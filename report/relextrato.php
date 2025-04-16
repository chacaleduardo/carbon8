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

if(empty($_GET["_idempresa"])){
	$idempresa = $_SESSION['SESSAO']['IDEMPRESA'];
} else {
	$idempresa = $_GET["_idempresa"];
}

$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$idagencia 	= $_GET["idagencia"];


//$clausula .= " vencimento > '2009-01-01' and ";

if (!empty($vencimento_1) or !empty($vencimento_2)){
	$month = date("m",strtotime($vencimento_1));
	
	$dataini = validadate($vencimento_1);
	$datafim = validadate($vencimento_2);

	if ($dataini and $datafim){
		$clausulad .= "(datareceb  BETWEEN '" . $dataini ."' and '" .$datafim ."')";
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}
/*
 * colocar condição para executar select
 */
 if($_GET and !empty($clausulad)){
$sqlant = "select * from contapagar where saldo is not null and status = 'QUITADO' and idagencia=".$idagencia." and idempresa = ".$idempresa." order by quitadoemseg desc limit 1;";


$sql = "SELECT *,dma(datareceb) as dtreceb,CASE status    WHEN 'QUITADO' THEN 1
            WHEN 'PENDENTE' THEN 2 
            ELSE 3 END as ordem FROM contapagar where ". $clausulad." and idagencia=".$idagencia." and idempresa = ".$idempresa." and status NOT IN('INATIVO','DEVOLVIDO','CANCELADO') order by datareceb asc,ordem asc,tipo asc,quitadoemseg";

echo "<!--";
echo $sqlant;
echo "-->";
echo "<!--";
echo $sql;
echo "-->";
if (!empty($sql)){

	$res =  d::b()->query($sql) or die("Falha ao pesquisar contas: " . mysqli_error() . "<p>SQL: $sql");
	$ires = mysqli_num_rows($res);

	$resant =  d::b()->query($sqlant) or die("Falha ao pesquisar saldo anterior: " . mysqli_error() . "<p>SQL: $sqlant");


	$somatotais = 0;
	$vlrcredito = 0;
	$vlrdebito = 0;
	$qtdcred = 0;
	$qtddeb = 0;
	$parc='';
	$vlrpendcredito = 0;
	$vlrpenddebito = 0;
	$qtdpendcred = 0;
	$qtdpenddeb = 0;
	$prevsaldototal=0;
	$saldofim=0;
	$data=date("Y/m/d");
}
}
?>
<html>
<head>
<title>Sislaudo - Contas a Receber</title>

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
		<td class="header">Relatório Extrato Bancário - (<?=$agencia?>)</td>
		<td><a class="btbr20" href="<?=$_SERVER['REQUEST_URI']?>&reportexport=csv" target="_blank">Exportar .csv</a></td>
	</tr>
	<tr>
		<td class="subheader">(Período entre <?=$vencimento_1?> e <?=$vencimento_2?>)</td>
	</tr>	
</table>
<br>
<table class="normal" style="font-size: 10px;">
	<tr class="header">
		<td align="center">Pagamento</td>
		<td align="center">Tipo Doc</td>
		<td align="center">Nº Doc</td>
		<td align="center">Emissão</td>
		<td align="center">Parcela</td>
		<td align="center">Fornecedor/Cliente</td>
		<td align="center">Forma de Pagamento</td>
		<td align="center">Valor</td>
		<td align="center">Tipo</td>			
		<td align="center">Status</td>	
	</tr>
	<?

	$rowant = mysqli_fetch_array($resant);
	
	if(!empty($rowant["saldo"])){
		$saldototal = $rowant["saldo"];
		
		if($saldototal >= 0){
			$corsaldo = "#c8d0ff";
		}else{
			$corsaldo = "#f0bfbf";
		}
		
	}else{
		$saldototal = 0;
	}	

	$ip =9999;//variavel para o form
	$conteudoexport;// guarda o conteudo para exportar para csv
	$conteudoexport='"Pagamento";"Tipo Doc";"Nº Doc";"Emissão";"Parcela";"Fornecedor/Cliente";"Forma Pagamento";"Valor";"Tipo";"Status"';
	$conteudoexport.="\n";//QUEBRA DE LINHA NO CONTEUDO CSV
	while ($row = mysqli_fetch_array($res)){
		$emissao='';
		$formapagamento=traduzid('formapagamento','idformapagamento','descricao',$row['idformapagamento']);		
		
		$sqls = "select * from contapagar 
		where saldo is not null 
		and status = 'QUITADO' 
		and idagencia=".$idagencia." 
		and datareceb = '".$row["datareceb"]."'
		and idempresa = ".$idempresa." order by quitadoemseg desc limit 1;";
		$ress =  d::b()->query($sqls) or die("Falha ao pesquisar saldo anterior 2: " . mysqli_error() . "<p>SQL: $sqls");
		$rows=mysqli_fetch_assoc($ress);
		
		$ip=$ip+1;
		$stredit = "janelamodal('contapagar.php?acao=u&idcontapagar=".$row["idcontapagar"]."',420,720);";
		//verificar se a conta possui lançamentos
		
		$favorecido="";
		$intemext="";
		$descext="";
		$descnf='';
		$obs='';
	    if($row["tipoobjeto"] == "nf" and !empty($row["idobjeto"] )){
			
			$sqlf = "select n.idnf,ifnull(p.razaosocial,p.nome) as nome,n.tiponf,n.controle,ifnull(n.nnfe,n.idnf) as nnfe,c.contaitem,n.dtemissao
						from nf n join pessoa p on( p.idpessoa = n.idpessoa )
						left join nfitem i on(i.idnf=n.idnf)
						left join contaitem c on(c.idcontaitem = i.idcontaitem)
						where n.idnf=".$row["idobjeto"]." limit 1";
			
			$qrf =  d::b()->query($sqlf) or die("Erro ao buscar nome do cliente da nota:".mysqli_error());
			$qtdrowsf= mysqli_num_rows($qrf);
			$resf = mysqli_fetch_assoc($qrf);
			$obs=$resf['contaitem'];
			$emissao=dma($resf['dtemissao']);

            if($resf["tiponf"]=="C"){
				$tiponf="DANFE";
				$descnf = $resf["nnfe"];
		    }
			if($resf["tiponf"]=="O"){
				$tiponf="Outros";
				$descnf = $resf["nnfe"];
		    }
		    if($resf["tiponf"]=="V"){
				$tiponf="SAIDA";
				$descnf = $resf["nnfe"];   
				$obs= traduzid('contaitem', 'idcontaitem', 'contaitem', 23);
		    }			
		    if($resf["tiponf"]=='S'){ 
				$tiponf="SERVICO";
				$descnf = $resf["nnfe"];
				$obs= traduzid('contaitem', 'idcontaitem', 'contaitem', 23);
		    }
		    if($resf["tiponf"]=='T'){ 
				$tiponf="CTE";
				$descnf = $resf["nnfe"];
		    }
		    if($resf["tiponf"]=='E'){ 
				$tiponf="CONCESSIONÁRIA";
				$descnf = $resf["nnfe"];
		    }
		    if($resf["tiponf"]=='M'){ 
				$tiponf="GUIA/CUPOM";
				$descnf = $resf["nnfe"];
			}
			if($resf["tiponf"]=='B'){ 
				$tiponf="RECIBO";
				$descnf = $resf["nnfe"];
		    }
		    if($resf["tiponf"]=='R'){ 
				$tiponf="PJ";
				$descnf = $resf["nnfe"];
		    }                    
		    if($resf["tiponf"]=='F'){ 
				$tiponf="FATURA";
				$descnf = $resf["nnfe"];
		    }	
			
			if(empty($resf['nnfe'])){
				$descnf=$resf['idnf'];
			}
			
			$favorecido = $resf["nome"];
			
			
		}elseif($row["tipoobjeto"] == "notafiscal" and !empty($row["idobjeto"] )){
			
			$sqlf = "select ifnull(p.razaosocial,p.nome) as nome,n.numerorps,n.nnfe,n.idnotafiscal,n.emissao from notafiscal n,pessoa p where p.idempresa = ".$idempresa." and p.idpessoa = n.idpessoa and idnotafiscal =".$row["idobjeto"];
			
			$qrf =  d::b()->query($sqlf) or die("Erro ao buscar nome do cliente da nota:".mysqli_error());
			$qtdrowsf= mysqli_num_rows($qrf);
			$resf = mysqli_fetch_assoc($qrf);
			$tiponf="NFS-e";	
			$favorecido = $resf["nome"];
			$descnf = $resf["nnfe"];
			$intemext = "NFS-e - ".$resf["nnfe"];
			$obs= traduzid('contaitem', 'idcontaitem', 'contaitem', 23);
			$emissao=dma($resf['emissao']);
			
		}elseif(empty($row["idobjeto"] ) and !empty($row["idpessoa"])){
			
			$favorecido =traduzid("pessoa","idpessoa","razaosocial",$row["idpessoa"]);
			if(empty($favorecido)){
 				$favorecido =traduzid("pessoa","idpessoa","nome",$row["idpessoa"]);
			}
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
			$dtpagmento=$row["datareceb"];
		}elseif($dtpagmento ==  $row["datareceb"]){
			$quebralinha='N';
			
		}elseif($dtpagmento <> $row["datareceb"]){
				$quebroulinha = 'S';

				$data= date('Y-m-d');				
				if($quebroulinha=='S' and (strtotime($data) < strtotime($dtpagmento) and  (strtotime($data) <> strtotime($dtpagmento)))){
					
					$prevsaldototal=0;
					$prevsaldototal =  $saldototal + $vlrpendcredito;
					$prevsaldototal =  $prevsaldototal - $vlrpenddebito;
							
					if($prevsaldototal >= 0){
						$corprevsaldo = "#98FB98";//verde
					}else{
						$corprevsaldo = "#FFFF00";//amarelo
					}
					$saldofim =	$prevsaldototal;
			
				    $conteudoexport.='"";"";"";"";"";"";"";"'.number_format($prevsaldototal, 2, '.','').'";"";""';
				    $conteudoexport.="\r\n";//QUEBRA DE LINHA NO CONTEUDO CSV
?>
					<tr >
						<td colspan="6"></td>
						<td align="right" class="header" ><?echo "Prev. Saldo:"?></td>
						<td align="right" class="header" ><?=number_format($prevsaldototal, 2, '.','');?></td>
					</tr>
<?		
				}else{	
				    $conteudoexport.='"";"";"";"";"";"";"";"'.number_format($saldototal, 2, '.','').'";"";""';
				    $conteudoexport.="\r\n";//QUEBRA DE LINHA NO CONTEUDO CSV
?>			
		
					<tr>
						<td colspan="6"></td>
						<td align="right" class="header" ><?echo "Saldo:"?></td>
						<td align="right" class="header" ><?=number_format($saldototal, 2, '.','');?></td>
					</tr>
<?		
				}
					$dtpagmento =  $row["datareceb"];
		}
		
	
		if($row["tipo"]=="C"){
			if($row["status"]=="PENDENTE"){	
				$vlrpendcredito = $vlrpendcredito + $row["valor"];
				$cortr = "#98FB98";//verde
				if(strtotime($data)== strtotime($row["datareceb"])){
					$mostraquitar="S";
					$newsaldo=$row["valor"]+$rowant["saldo"];
				}else{
					$mostraquitar="N";
				}
				
			}else{
				$vlrcredito = $vlrcredito + $row["valor"];
				$cortr = "#c8d0ff";//azul
				$mostraquitar="N";
			}
		}elseif($row["tipo"]=="D"){
			if($row["status"]=="PENDENTE"){	
				$vlrpenddebito = $vlrpenddebito + $row["valor"];
				$cortr = "#FFFF00";//amarelo
				
				if(strtotime($data)== strtotime($row["datareceb"])){
					$mostraquitar="S";
					$newsaldo=$row["valor"] - $rowant["saldo"];
				}else{
					$mostraquitar="N";
				}
			}else{
				$vlrdebito = $vlrdebito + $row["valor"];
				$cortr = "#f0bfbf";//red + fraco
				$mostraquitar="N";
			}			
		}
                if($row['tipoespecifico']!='AGRUPAMENTO' or $row["tipo"]=="C"){
                 
?>	
	
                        <tr class="respreto">
							<td><?=$row["dtreceb"]?></td>								
							<td><?=$tiponf?></td>
							<td><?=$descnf?></td>
							<td><?=$emissao?></td>
							<td><?echo($row["parcela"]." de ".$row["parcelas"]);?></td>	
							<td><?=$favorecido?></td>		
							<td><?=$formapagamento?></td>
							<td><?=$row["valor"]?></td>
							<td align="center" style="background-color: <?=$cortr?>;"><?=$row["tipo"]?></td>			
							<td align='center'><?=$row["status"]?></td>
                        </tr>
<?	
 
                    $conteudoexport.='"'.$row["dtreceb"].'";"'.$tiponf.'";"'.$descnf.'";"'.$emissao.'";"'.$row["parcela"].'de'.$row["parcelas"].'";"'.$favorecido.'";"'.$formapagamento.'";"'. $row["valor"].'";"'.$row["tipo"].'";"'.$row["status"].'"';	
	
                    $conteudoexport.="\r\n";//QUEBRA DE LINHA NO CONTEUDO CSV
                }else{
               
                    $sqlx=" select i.parcela,i.parcelas,ci.contaitem,ifnull(p.razaosocial,p.nome) as nome,n.nnfe,i.valor,i.status,i.tipo,n.idnf,n.tiponf,i.tipoobjetoorigem,n.dtemissao
                            from contapagaritem i left join nf n on(n.idnf=i.idobjetoorigem)
                            left join nfitem ni on(ni.idnf=n.idnf)
                            left join contaitem ci on (ci.idcontaitem = ni.idcontaitem)
                            join pessoa p on(i.idpessoa=p.idpessoa)
                            where i.idcontapagar=".$row['idcontapagar']." group by i.idcontapagaritem";
                    $resx = d::b()->query($sqlx) or die("Falha ao buscar items do agrupamento " . mysqli_error(d::b()) . "<p>SQL: $sqlx");
                    while($rowx=mysqli_fetch_assoc($resx)){
                        if(empty($obs)){
                          	$obs=$rowx['contaitem'];
						}
						if(empty($rowx['nnfe'])){
							$rowx['nnfe']=$rowx['idnf'];
						}
						$emissao=dma($rowx['dtemissao']);

						if($rowx["tiponf"]=="C"){
							$tiponf="DANFE";						
						}
						if($rowx["tiponf"]=="V"){
							$tiponf="SAIDA";							
						}			
						if($rowx["tiponf"]=='S'){ 
							$tiponf="SERVICO";														
						}
						if($rowx["tiponf"]=='T'){ 
							$tiponf="CTE";							
						}
						if($rowx["tiponf"]=='E'){ 
							$tiponf="CONCESSIONÁRIA";						
						}
						if($rowx["tiponf"]=='M'){ 
							$tiponf="GUIA/CUPOM";							
						}
						if($rowx["tiponf"]=='B'){ 
							$tiponf="RECIBO";						
						}
						if($rowx["tiponf"]=='O'){ 
							$tiponf="Outros";						
						}
						if($rowx["tiponf"]=='R'){ 
							$tiponf="PJ";							
						}                    
						if($rowx["tiponf"]=='F'){ 
							$tiponf="FATURA";							
						}		
?>                   
                <tr class="respreto">
					<td><?=$row["dtreceb"]?></td>	
					<td><?=$tiponf?></td>
					<td><?=$rowx['nnfe']?></td>
					<td><?=$emissao?></td>						
					<td><?echo($rowx["parcela"]." de ".$rowx["parcelas"]);?></td>	
					<td><?=$rowx['nome']?></td>
					<td><?=$formapagamento?></td>    
					<td><?=$rowx["valor"]?></td>                                     
					<td align="center" style="background-color:<?=$cortr?>;"><?=$rowx["tipo"]?></td>			
					<td align='center'><?=$rowx["status"]?></td>
                </tr>
<?
						$conteudoexport.='"'.$row["dtreceb"].'";"'.$tiponf.'";"'.$rowx['nnfe'].'";"'.$emissao.'";"'.$rowx["parcela"].'de'.$rowx["parcelas"].'";"'.$rowx['nome'].'";"'.$formapagamento.'";"'. $rowx["valor"].'";"'.$rowx["tipo"].'";"'.$rowx["status"].'"';
						$conteudoexport.="\r\n";//QUEBRA DE LINHA NO CONTEUDO CSV
                    }//while($rowx=mysqli_fetch_assoc($resx)){
                    
                }//if($row['tipoespecifico']=='AGRUPAMENTO' AND $row['tipoobjeto']=='nf' and !empty($row['idobjeto'])){
	 

                
		if($rows["status"]=="QUITADO" AND !empty($rows["saldo"])){
			//ja esta quitado 
			$saldototal= $rows["saldo"];	
			if($saldototal >= 0){
				$corsaldo = "#c8d0ff";
			}else{
				$corsaldo = "#f0bfbf";
			}
		}
	}//while ($row = mysqli_fetch_array($res)){
	
	$saldofim = $saldototal;
	$somatotais = $vlrcredito - $vlrdebito;//a soma do total
	$fimpendcredito = $vlrpendcredito + $vlrcredito;
	$fimpenddebito = $vlrpenddebito + $vlrdebito;
	
		
				
		if($quebroulinha=='S' and (strtotime($data) < strtotime($dtpagmento) and  (strtotime($data) <> strtotime($dtpagmento)))){
			
					$prevsaldototal=0;
					$prevsaldototal =  $saldototal + $vlrpendcredito;
					$prevsaldototal =  $prevsaldototal - $vlrpenddebito;
					
				if($prevsaldototal >= 0){
					$corprevsaldo = "#98FB98";//verde
				}else{
					$corprevsaldo = "#FFFF00";//amarelo
				}
				$saldofim =	$prevsaldototal;
?>
			<tr >
				<td colspan="6" ></td>
				<td align="right" class="header" ><?echo "Prev. Saldo:"?></td>
				<td align="right" class="header" ><?=number_format($prevsaldototal, 2, '.','');?></td>
			</tr>
<?			
			//$conteudoexport.='"'.$prevsaldototal.'";"';
			$conteudoexport.='"";"";"";"";"";"";"";"'.number_format($prevsaldototal, 2, '.','').'";"";""';
			$conteudoexport.="\r\n";//QUEBRA DE LINHA NO CONTEUDO CSV
	
			}else{
				if($saldototal >= 0){
					$corsaldo = "#c8d0ff";
				}else{
					$corsaldo = "#f0bfbf";
				}
?>		
			<tr>
				<td colspan="6" ></td>
				<td align="right" class="header" ><?echo "Saldo:"?></td>
				<td align="right" class="header" ><?=number_format($saldototal, 2, '.','');?></td>
			</tr>		
				
<?			//$conteudoexport.='"'.$saldototal.'";"';
			$conteudoexport.='"";"";"";"";"";"";"";"'.number_format($saldototal, 2, '.','').'";"";""';
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
<table class="normal" style="font-size: 10px;">
	<tr class="header">
		<td>Descrição</td>
		<td>Valor</td>
		<td>Descrição</td>
		<td>Valor</td>
		<td>Descrição</td>
		<td>Valor</td>
	</tr>
	<tr class="respreto">
		<td align="left" >CRÉDITO</td>
		<td align="right" ><?=number_format($vlrcredito, 2, '.','');?></td>
		<td align="left" >DÉBITO</td>
		<td align="right" ><?=number_format($vlrdebito, 2, '.','');?></td>
		<td align="left" >SOMA VALORES</td>
		<td align="right" ><?=number_format($somatotais, 2, '.','');?></td>
	</tr>
	<tr class="respreto">
		<td align="left">PEND. CRÉDITO</td>
		<td align="right"><?=number_format($vlrpendcredito, 2, '.','');?></td>
		<td align="left">PEND. DÉBITO</td>
		<td align="right"><?=number_format($vlrpenddebito, 2, '.','');?></td>
		<td align="left">PREV. SALDO</td>
		<td align="right"><?=number_format($saldofim, 2, '.','');?></td>
	</tr>
	<tr>
		<td colspan="30"><a style="font-size:8px">Verificar Falhas</a></td>
	</tr>
</table>
<?
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
	header("Content-type: text/csv; charset=utf-8");
	header("Content-Disposition: attachment; filename=".$infilename.".csv");
	header("Pragma: no-cache");
	header("Expires: 0");

	echo utf8_decode($conteudoexport);
 
}

?>