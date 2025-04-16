<?
error_reporting(E_ALL);
require_once("../inc/php/validaacesso.php");
if(!empty($_GET["reportexport"])){
    session_cache_expire(1);
    session_cache_limiter("private");
    ob_start();//não envia nada para o browser antes do termino do processamento
}
//ini_set("display_errors","1");
//error_reporting(E_ALL);

################################################## Atribuindo o resultado do metodo GET

$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$idagencia 	= $_GET["idagencia"];
$tipo 	= $_GET["tipo"];

if(empty($_GET["_idempresa"])){
	$idempresa = $_SESSION['SESSAO']['IDEMPRESA'];
} else {
	$idempresa = $_GET["_idempresa"];
}
if($tipo=='C'){
    $strtipo=" and c.tipo='C' "; 
}else{
    $strtipo=" and c.tipo='D' and c.tipoobjeto ='nf' "; 
}


//$clausula .= " vencimento > '2009-01-01' and ";

if (!empty($vencimento_1) or !empty($vencimento_2)){
	$month = date("m",strtotime($vencimento_1));
	
	$dataini = validadate($vencimento_1);
	$datafim = validadate($vencimento_2);

	if ($dataini and $datafim){
		$clausulad .= "(n.dtemissao  BETWEEN '" . $dataini ." 00:00:00' and '" .$datafim ." 23:59:59')";
             //$clausulad .= " (c.datareceb  BETWEEN '" . $dataini ."' and '" .$datafim ."')";
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}else{
    die("E necessário informar o intervalo");
}
/*
 * colocar condição para executar select
 */
 if($_GET and !empty($clausulad)){


     
    $sql="SELECT  p.razaosocial,f.finalidadeprodserv,c.*,dma(c.datareceb) as dtreceb,dma(n.dtemissao) as dtemissao,CASE c.status    WHEN 'QUITADO' THEN 1
                WHEN 'PENDENTE' THEN 2 
                ELSE 3 END as ordem 
                FROM contapagar c join nf n on(n.idnf=c.idobjeto and n.status='CONCLUIDO' AND ".$clausulad."  )
                    	join pessoa p on(p.idpessoa = n.idpessoa )
                        left join finalidadeprodserv f on(f.idfinalidadeprodserv=n.idfinalidadeprodserv)
                where 
                 c.idagencia=".$idagencia."
                and c.idempresa = ".$idempresa."
                and c.tipo='D'
                ".$strtipo."
                and c.status != 'INATIVO'  order by  p.razaosocial,n.dtemissao asc,ordem asc,c.tipo asc,c.quitadoemseg";


    echo "<!--";
    echo $sql;
    echo "-->";
    if (!empty($sql)){

            $res =  d::b()->query($sql) or die("Falha ao pesquisar contas: " . mysqli_error() . "<p>SQL: $sql");
            $ires = mysqli_num_rows($res);



            $data=date("Y/m/d");
    }
}
?>
<html>
<head>
<title>Sislaudo - Emissões</title>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

</head>

<link href="../inc/css/rep.css" media="all" rel="stylesheet" type="text/css">
<style>
    
a.btbr20{
	display: none;
}

/* Botao branco fonte 8 */
a.btbr20:link{
	position: fixed;

	right: 15px;

    font-weight: bold;
    font-size:20px;
    color: silver;
    
	border: 1px solid #d7d7d7;
    cursor: pointer;

    padding-left: 5px;
    padding-right: 5px;
    padding-bottom: 1px;
    margin-left: 5px;
      
	background: #cccccc; /* para browsers sem suporte a CSS 3 */

	/* Gradiente */
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ececec', endColorstr='#dcdcdc'); /* IE */
	background: -webkit-gradient(linear, left top, right top, from(#ececec), to(#dcdcdc)); /* webkit */
	background: -moz-linear-gradient(top,  #ececec, #dcdcdc); /* FF */

    /* Arredondamento */
	-moz-border-radius: 8px;
 	-webkit-border-radius: 8px;
 	border-radius: 8px 8px 8px 8px;
 	
 	text-decoration: none;
}
a.btbr20:hover
{
    font-weight: bold;
    font-size:20px;
    color: silver;
    
	border: 1px solid #d7d7d7;
    cursor: pointer;

    padding-left: 5px;
    padding-right: 5px;
    padding-bottom: 1px;
    margin-left: 5px;

	background: #eaeaf4; /* para browsers sem suporte a CSS 3 */

	/* Gradiente */
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff0000', endColorstr='#c02900'); /* IE */
	background: -webkit-gradient(linear, left top, right top, from(#ff0000), to(#c02900)); /* webkit */
	background: -moz-linear-gradient(top, #ffffff, #e1e1e1); /* FF */

    /* Arredondamento */
	-moz-border-radius: 8px;
 	-webkit-border-radius: 8px;
 	border-radius: 8px 8px 8px 8px;
 	text-decoration: none;
} 
a.btbr20:visited {
	border: 1px solid silver;
	color:white;
	text-decoration: none;
}
</style>
<script language="javascript">

var reloadpage = true;//Utilizado para informar à req.xml para efetuar refresh APÓS a respota
var xmlonreadystate = "xmldocU=xmldoc.toUpperCase();if(xmldocU.indexOf('ERR')>0){alert(xmldoc);}";
$(document).ready(function(){
	$("#vencimento_1").mask("99/99/9999");
	$("#vencimento_2").mask("99/99/9999");
});

/*
 * Funcao para preencher automaticamente valores de campos "gemeos" ex: data_1 e data_2
 */
function fill_2(inobj){

	//Confirma se o objeto possui a identificacao correta (nomecampo_1) para gemeos
	if(inobj.id.indexOf("_2") > -1) {
		
		var strnome_1 = inobj.id.replace("_2","_1");
		var obj_1 = document.getElementById(strnome_1);

		if(inobj != null && inobj.value == ""){
			inobj.value = obj_1.value;
			inobj.select();
		}
		//if(inobj.value != "" and inobj.value != undefined){
			
		//}
	}

}


</script>
<style data-cke-temp="1" type="text/css" media="screen">

a.btbr20{
	display: block;
}

</style>
<body>

<?
if($_GET){
$agencia=traduzid("agencia","idagencia","agencia",$idagencia);
?>

<table class="tbrepheader">
	
	<tr>
		<td rowspan="3" style="width:200;">
                    <!--
                    <img src="../img/repheader.png">
                    -->
                </td>
		<td class="header">Relatório de Emissões - (<?=$agencia?>)</td>
		<td><a class="btbr20" href="<?=$_SERVER['REQUEST_URI']?>&reportexport=csv" target="_blank">Exportar .csv</a></td>
	</tr>
	<tr>
		<td class="subheader">(Período entre <?=$vencimento_1?> e <?=$vencimento_2?>)</td>
	</tr>	
</table>
<br>
<table class="normal" style="font-size: 10px;">
   
	<tr class="header">
            <td align="center">Emissão</td>
            <td align="center">Fornecedor</td>
            <td align="center">Finalidade</td>
            <td align="center">NF</td>            
            <td align="center">Vencimento</td> 
            <td align="center">Valor</td>            
	</tr>
         
	<?
        $total=0;
        $debitototal=0;
	$ip =9999;//variavel para o form
	$conteudoexport;// guarda o conteudo para exportar para csv
	$conteudoexport='"Emissoa";"Fornecedor";"Finalidade";"NF";"Vencimento";"Valor"';
	$conteudoexport.="\n";//QUEBRA DE LINHA NO CONTEUDO CSV
	while ($row = mysqli_fetch_array($res)){
		
                $debitototal=$debitototal+$row["valor"];

		$ip=$ip+1;
		$stredit = "janelamodal('contapagar.php?acao=u&idcontapagar=".$row["idcontapagar"]."',420,720);";
		//verificar se a conta possui lançamentos
		
		$favorecido="";
		$intemext="";
		$descext="";
                $descnf='';
		
	    if($row["tipoobjeto"] == "nf" and !empty($row["idobjeto"] )){
			
			$sqlf = "select n.idnf,ifnull(p.razaosocial, p.nome) as nome,n.tiponf,n.controle,n.nnfe from nf n,pessoa p where p.idpessoa = n.idpessoa and p.idempresa = ".$idempresa." and idnf =".$row["idobjeto"];
			
			$qrf =  d::b()->query($sqlf) or die("Erro ao buscar nome do cliente da nota:".mysqli_error());
			$qtdrowsf= mysqli_num_rows($qrf);
			$resf = mysqli_fetch_assoc($qrf);
					    if($resf["tiponf"]=="C"){
			$tiponf="ENTRADA";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }
		    if($resf["tiponf"]=="V"){
			$tiponf="SAÍDA";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];                        
		    }			
		    if($resf["tiponf"]=='S'){ 
			$tiponf="SERVIÇO";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }
		    if($resf["tiponf"]=='T'){ 
			$tiponf="CTE";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }
		    if($resf["tiponf"]=='E'){ 
			$tiponf="CONCESSIONÁRIA";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }
		    if($resf["tiponf"]=='M'){ 
			$tiponf="MANUAL/CUPOM";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
			}
			if($resf["tiponf"]=='B'){ 
			$tiponf="RECIBO";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
			}
		    if($resf["tiponf"]=='R'){ 
			$tiponf="RH";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }                    
		    if($resf["tiponf"]=='F'){ 
			$tiponf="FATURA";
			$descnf = $resf["nnfe"];
		    }	
                     if($resf["tiponf"]=='D'){ 
			$tiponf="";
			$descnf = $resf["nnfe"];
		    }	
			
                    $favorecido = $resf["nome"];
			
			
		}elseif($row["tipoobjeto"] == "notafiscal" and !empty($row["idobjeto"] )){
			
			$sqlf = "select ifnull( p.razaosocial,  p.nome) as nome,n.numerorps,n.nnfe,n.idnotafiscal from notafiscal n,pessoa p where p.idempresa = ".$idempresa." and p.idpessoa = n.idpessoa and idnotafiscal =".$row["idobjeto"];
			
			$qrf =  d::b()->query($sqlf) or die("Erro ao buscar nome do cliente da nota:".mysqli_error());
			$qtdrowsf= mysqli_num_rows($qrf);
			$resf = mysqli_fetch_assoc($qrf);
			$tiponf="Saída";	
			$favorecido = $resf["nome"];
			
			$intemext = "NFS-e - ".$resf["nnfe"];
			
		}elseif(empty($row["idobjeto"] ) and !empty($row["idpessoa"])){
			$favorecido =traduzid("pessoa","idpessoa","nome",$row["idpessoa"]);
			$intemext="";
		}else{
			$favorecido="";
			$intemext="";
		}
		
		if(!empty($row["idcontaitem"])){
			$sqlf2 = "select c.contaitem from contaitem c  where c.idcontaitem =".$row["idcontaitem"];			
			$qrf2 = d::b()->query($sqlf2) or die("Erro ao buscar descrição item da nota:".mysqli_error(d::b()));			
			$resf2 = mysqli_fetch_assoc($qrf2);
			$intemext = $resf2["contaitem"];
		}else{
		    $intemext="";
		}
		    
		if($row["idpessoa"] and empty($favorecido)){
		    $favorecido =traduzid("pessoa","idpessoa","razaosocial",$row["idpessoa"]);
			if(empty($favorecido)){
 				$favorecido =traduzid("pessoa","idpessoa","nome",$row["idpessoa"]);
			}
		}elseif($row["idcontadesc"]  and empty($favorecido) ){
		    $favorecido =traduzid("contadesc","idcontadesc","contadesc",$row["idcontadesc"]);
		}	
		
		if(empty ($dtpagmento)){
			$dtpagmento=$row["dtemissao"];
		}elseif($dtpagmento ==  $row["dtemissao"]){
			$quebralinha='N';
			
		}elseif($dtpagmento <> $row["dtemissao"]){
				$quebroulinha = 'S';

				$data= date('Y-m-d');				
				if($quebroulinha=='S' and (strtotime($data) < strtotime($dtpagmento) and  (strtotime($data) <> strtotime($dtpagmento)))){
					
							
							
			
				    $conteudoexport.='"";"";"";"";"";"'.number_format($total, 2, '.','').'";"";""';
				    $conteudoexport.="\r\n";//QUEBRA DE LINHA NO CONTEUDO CSV
?>
            <!--
					<tr >
						<td colspan="6"></td>
						<td align="right" class="header" ><?echo "Total:"?></td>
						<td align="right" class="header" ><?=number_format($total, 2, '.','');?></td>
					</tr>
            -->
<?		
                                    $total=0;
                                    
				}else{	
				    $conteudoexport.='"";"";"";"";"";"'.number_format($total, 2, '.','').'";"";""';
				    $conteudoexport.="\r\n";//QUEBRA DE LINHA NO CONTEUDO CSV
?>			
		 <!--
					<tr>
						<td colspan="6"></td>
						<td align="right" class="header" ><?echo "Total:"?></td>
						<td align="right" class="header" ><?=number_format($total, 2, '.','');?></td>
					</tr>
   -->
<?                                  $total=0;
                                    
				}
					$dtpagmento =  $row["dtemissao"];
		}
		
	
		
                if($row['tipoespecifico']=='AGRUPAMENTO'){
                    $bold='<b>';
                    $boldf='</b>';
                }else{
                    $bold='';
                    $boldf='';
                }
?>	
	
	<tr class="respreto">
            <td><?=$row["dtemissao"]?></td>
            <td><?=$favorecido?></td>
            <td><?=$row["finalidadeprodserv"]?></td>
            <td><?=$descnf?></td>                        
            <td><?=$row["dtreceb"]?></td>	
            <td><?=$bold?><?=$row["valor"]?><?=$boldf?></td>
	</tr>
<?	
	$conteudoexport.='"'.$row["dtemissao"].'";"'.$favorecido.'";"'.$row["finalidadeprodserv"].'";"'.$descnf.'";"'.$row["dtreceb"].'";"'. $row["valor"].'"';
	$conteudoexport.="\r\n";//QUEBRA DE LINHA NO CONTEUDO CSV
	
	
		
                
                if($row['tipoespecifico']=='AGRUPAMENTO' AND $row['tipoobjeto']=='nf' and !empty($row['idobjeto'])){
                    $sqlx=" select i.parcela,i.parcelas,ci.contaitem,p.nome,n.nnfe,i.valor,i.status,dma(n.dtemissao) as dtemissao,f.finalidadeprodserv
                            from contapagaritem i 
                            join nf n on(n.idnf=i.idobjetoorigem)
                            join contaitem ci on (ci.idcontaitem = n.idcontaitem)
                            join pessoa p on(n.idpessoa=p.idpessoa)
                            left join finalidadeprodserv f on(f.idfinalidadeprodserv=n.idfinalidadeprodserv)
                            where i.idcontapagar=".$row['idcontapagar'];
                    $resx = d::b()->query($sqlx) or die("Falha ao buscar items do agrupamento " . mysqli_error(d::b()) . "<p>SQL: $sqlx");
                    while($rowx=mysqli_fetch_assoc($resx)){
?>                   
                <tr class="respreto">
                    <td><?=$row["dtemissao"]?></td>
                    <td><?=$favorecido?></td>
                     <td><?=$rowx['finalidadeprodserv']?></td>
                    <td><?=$rowx['nnfe']?></td>                    
                    <td><?=$row["dtreceb"]?></td> 
                    <td><?=$rowx["valor"]?></td>
                </tr>
<?

        $conteudoexport.='"'.$row["dtemissao"].'";"'.$favorecido.'";"'.$rowx['finalidadeprodserv'].'";"'.$rowx['nnfe'].'";"'.$row["dtreceb"].'";"'.$rowx['valor'].'"';
	$conteudoexport.="\r\n";//QUEBRA DE LINHA NO CONTEUDO CSV
                    }//while($rowx=mysqli_fetch_assoc($resx)){
                    
                }//if($row['tipoespecifico']=='AGRUPAMENTO' AND $row['tipoobjeto']=='nf' and !empty($row['idobjeto'])){
	 $total=$total+$row["valor"];
	}//while ($row = mysqli_fetch_array($res)){
	
		
		
				
		if($quebroulinha=='S' and (strtotime($data) < strtotime($dtpagmento) and  (strtotime($data) <> strtotime($dtpagmento)))){
			
			
					$corprevsaldo = "#FFFF00";//amarelo
				
?> <!--
			<tr >
				<td colspan="6" ></td>
				<td align="right" class="header" ><?echo  "Total"?></td>
				<td align="right" class="header" ><?=number_format($total, 2, '.','');?></td>
			</tr>
   -->
<?			
			//$conteudoexport.='"'.$prevsaldototal.'";"';
			$conteudoexport.='"";"";"";"";"";"'.number_format($total, 2, '.','').'";"";""';
			$conteudoexport.="\r\n";//QUEBRA DE LINHA NO CONTEUDO CSV
	
			}else{
				if($saldototal >= 0){
					$corsaldo = "#c8d0ff";
				}else{
					$corsaldo = "#f0bfbf";
				}
?>		 <!--
			<tr>
				<td colspan="6" ></td>
				<td align="right" class="header" ><?echo "Total:"?></td>
				<td align="right" class="header" ><?=number_format($total, 2, '.','');?></td>
			</tr>
     -->
				
<?			//$conteudoexport.='"'.$saldototal.'";"';
			$conteudoexport.='"";"";"";"";"";"'.number_format($total, 2, '.','').'";"";""';
			$conteudoexport.="\r\n";//QUEBRA DE LINHA NO CONTEUDO CSV
			}
?>
			
<?		
	
	$cortrfim = "";
	if($somatotais >= 0){
		$cortrfim = "#c8d0ff";
	}else{
		$cortrfim= "#FF6347";
	}
	
?>
						
</table>
<p>&nbsp;</p>
<?/*
    $sqlf="SELECT  p.razaosocial,p.nome,sum(c.valor) as valor
                FROM contapagar c join nf n on(n.idnf=c.idobjeto and n.status='CONCLUIDO' AND ".$clausulad."   )
                    join pessoa p on(p.idpessoa = n.idpessoa )
                where 
                    c.idagencia=".$idagencia."
                and c.idempresa = ".$idempresa."
                ".$strtipo."
                and c.status = 'PENDENTE' group by p.idpessoa   order by  p.razaosocial";
      $resf = d::b()->query($sqlf) or die("Falha ao buscar soma dos valores por fornecedor" . mysqli_error(d::b()) . "<p>SQL: $sqlf");

?>
<div style=" page-break-after:always;"></div>
<table class="normal" style="font-size: 10px;">
	<tr class="header">
		<td>Descrição</td>
		<td>Valor</td>
		
	</tr>
<? 
    while($rowf=mysqli_fetch_assoc($resf)){
?>	
        <tr class="respreto">		
            <td align="left"><?=$rowf['nome']?></td>
            <td align="right"><?=$rowf['valor']?></td>		
	</tr>
<?
    }//while($rowf=mysqli_fetch_assoc($resf)){
?>
	<tr class="respreto">		
            <td align="left"><b>PENDENTE</b></td>
            <td align="right"><b><?=number_format($debitototal, 2, '.','');?></b></td>		
	</tr>
	<tr>
		<td colspan="30"><a style="font-size:8px">Verificar Falhas</a></td>
	</tr>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
<?
*/
}//if($_GET){
?>
</body>
</html>

<?

if(!empty($_GET["reportexport"])){
	ob_end_clean();//não envia nada para o browser antes do termino do processamento

	/* Gerar o nome do arquivo para exportar
	 * Substitui qualquer caractere estranho pelo sinal de '_'
	* Caracteres que NAO SERAO substituidos:
	*   - qualquer caractere de A a Z (maiusculos)
	*   - qualquer caracteres de a a z (minusculos)
	*   - qualquer caractere de 0 a 9
	*   - e pontos '.'
	*/

	//$infilename = ereg_replace("[^A-Za-z0-9s.]", "", "relfin");
	$infilename='relfin_'.$agencia.'_'.$month;
	//gera o csv
	header("Content-type: text/csv; charset=iso-8859-1");
	header("Content-Disposition: attachment; filename=".$infilename.".csv");
	header("Pragma: no-cache");
	header("Expires: 0");

	echo($conteudoexport);
 
}

?>