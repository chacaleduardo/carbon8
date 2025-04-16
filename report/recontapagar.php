<?

require_once("../inc/php/functions.php");
if(!empty($_GET["reportexport"])){
	ob_start();//não envia nada para o browser antes do termino do processamento
}
require_once("../inc/php/validaacesso.php");
$_header="Contas a Pagar";
$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$valor_1		= trim($_GET["valor_1"]);
$valor_2		= trim($_GET["valor_2"]);
$itemconta 		= trim($_GET["itemconta"]);
$idcontadesc		= $_GET["idcontadesc"];
$drops			= false;
$controle		= $_GET["controle"];
$tipo			= $_GET["tipo"];
$statuspgto		= $_GET["statuspgto"];
$idagencia = $_GET["idagencia"];
$previsao = $_GET["previsao"];
//$contadesc = $_GET["contadesc"];
$obs= $_GET["obs"];
$visivel=$_GET["visivel"];
$nome=$_GET["nome"];
$idformapagamento=$_GET["idformapagamento"];
$idcartao=$_GET["idcartao"];

$buscaitens="Y";

//$clausula .= " vencimento > '2009-01-01' and ";
$sql=" select * from pessoa where flgsocio='Y' and idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
$res = d::b()->query($sql) or die("Erro ao buscar usuário: " . mysqli_error(d::b()));
$flgdiretor=mysqli_num_rows($res);
//print_r($_SESSION["post"]);
if(!empty($controle) and $controle!='undefined'){
	$clausulad .= " idcontapagar = " . $controle ." and ";
    $clausulai .=" pg.idcontapagar = " . $controle ." and ";
    

}
if($previsao=='N'){
    $clausulad.=" exists (select 1 from nf n where n.idnf = cp.idobjeto and n.status ='CONCLUIDO') AND 
                            cp.tipoobjeto ='nf' and " ;
}

if(!empty($itemconta) and $itemconta!='undefined'){
	$clausulad .=" exists (select 1 from  contaitem ci
							where  cp.idcontaitem = ci.idcontaitem
							and ci.idcontaitem = ".$itemconta." ) and ";
    $clausulai .=" exists (select 1 from  contaitem ci
							where  cp.idcontaitem = ci.idcontaitem
							and ci.idcontaitem = ".$itemconta." ) and ";
	$drops = true;
	
}
if($idformapagamento){
    $clausulad.=" cp.idformapagamento=".$idformapagamento." and ";
    if($previsao=='N'){
        $prev="  AND pg.tipoobjeto = 'nf' AND  exists (select 1 from nf n where n.idnf =pg.idobjeto and n.status ='CONCLUIDO')" ;
    }
    
    $joincp=" join  contapagar pg on(pg.idcontapagar=cp.idcontapagar ".$prev." and pg.idformapagamento=".$idformapagamento." )";
}else{
     if($previsao=='N'){
        $prev=" AND pg.tipoobjeto = 'nf' AND  exists (select 1 from nf n where n.idnf =pg.idobjeto and n.status ='CONCLUIDO') " ;
    }
    
      $joincp=" join  contapagar pg on( pg.idcontapagar=cp.idcontapagar ".$prev." )";
}

if(!empty($obs) and $obs!='undefined'){
	$clausulad .=" cp.obs like ('%".$obs."%') and ";
    $buscaitens="N";
}
/*
if(!empty($contadesc) and $contadesc!='undefined'){
	$clausulad .=" cd.contadesc like ('%".$contadesc."%') and ";
	$drops = true;
}
*/
if (!empty($vencimento_1) and !empty($vencimento_2) and $vencimento_1!='undefined' and $vencimento_2!='undefined'){
	$dataini = validadate($vencimento_1);
	$datafim = validadate($vencimento_2);

	if ($dataini and $datafim){
		
		$clausulad .= " (datareceb  BETWEEN '" . $dataini ."' and '" .$datafim ."')"." and ";
                $clausulai .= " (pg.datareceb  BETWEEN '" . $dataini ."' and '" .$datafim ."')"." and ";
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}

if (!empty($valor_1) and !empty($valor_2)  and $valor_1!='undefined' and $valor_2!='undefined'){
	if (is_numeric($valor_1) and is_numeric($valor_2)){
		
		$clausulad .= " (cp.valor BETWEEN " . $valor_1 ." and " .$valor_2 .")  and ";
        $clausulai .= " (cp.valor BETWEEN " . $valor_1 ." and " .$valor_2 .")  and ";
	}else{
		die ("Os valores de contas informados [".$valor_1."] e [".$valor_2."] s&atilde;o inv&aacute;lidos!");
	}
}




    

if (!empty($statuspgto) and $statuspgto!='undefined'){
	
	$clausulad .= " cp.status = '" . $statuspgto ."' and";
    $clausulai .= " cp.status = '" . $statuspgto ."' and";

}

if($nome){
    $cpessoa =" join pessoa p ";
    $clausulad .= " (p.idpessoa = cp.idpessoa  and p.nome like('%".$nome."%')) and";
    $clausulai .= " (p.idpessoa = cp.idpessoa  and p.nome like('%".$nome."%')) and";
}else{    
    $cpessoa =" left join pessoa p on(p.idpessoa = cp.idpessoa ) ";    
}



if(!$clausulad == ''){//aqui estava assim
	$clausulad = " where " . substr($clausulad,1,strlen($clausulad) - 5);
   
}

//verificar se e usuario com modulo master restaurar ativo
 $sqlm=" select if('restaurar' in (".getModsUsr("SQLWHEREMOD")."),'Y','N') as master";
 $resm = d::b()->query($sqlm) or die("Falha ao pesquisar SQLWHEREMOD usuario master : " . mysqli_error(d::b()) . "<p>SQL: $sqlm");
 $rowm=mysqli_fetch_assoc($resm);
//die($sqlm);
     
if($_GET and !empty($clausulad)){
if($flgdiretor<1){   
if(array_key_exists("quitarcredito", getModsUsr("MODULOS"))){
    $clausulad .=" and cp.tipo in ('C') and cp.tipoobjeto  in('nf','notafiscal')  "; 
    $joincontaitem="";
   // $buscaitens="N";
    $clausulad .="      and exists (select 1 from contapagaritem i where i.idcontapagar=cp.idcontapagar and i.tipoobjetoorigem in ('nf','notafiscal') )
                            ";
     $clausulai.= "  cp.visivel='S' and cp.tipo in ('C') and ";
    
}elseif(array_key_exists("quitardebito", getModsUsr("MODULOS"))){
    $clausulad .=" and  cp.tipo in ('D')   "; 
    $joincontaitem=" join nf n on (  cp.idobjeto = n.idnf and (cp.tipoobjeto like ('nf%') or cp.tipoobjeto='gnre') and n.tiponf not in('R','D'))  ";
        
    $clausulad .=" and (exists (select 1 from contapagaritem i,nf n where i.idcontapagar=cp.idcontapagar and i.tipoobjetoorigem ='nf' and i.idobjetoorigem = n.idnf and n.tiponf not in('D','R') ) 
                        or 
                        exists (select 1 from contapagaritem i where i.idcontapagar=cp.idcontapagar and i.tipoobjetoorigem ='contapagar' )
                        )   ";
     $clausulai.= "  cp.visivel='S' and cp.tipo in ('D') and";
            
}
}
    if(!empty($tipo) and $tipo!='undefined' and $tipo!='T'){
	if($tipo == 'C'){
            $clausulad .= " and cp.tipo = 'C'  ";
            $clausulai .= "  cp.tipo = 'C' and ";
           //$buscaitens="N";
	}elseif($tipo == 'D'){
            $clausulad .= " and cp.tipo = 'D'  ";
            $clausulai .= "  cp.tipo = 'D' and";
	}
    }   

     
if($flgdiretor<1){
  $clausulad .= " and cp.visivel = 'S' ";         
}elseif(!empty($visivel) and ($visivel!='undefined') and $rowm['master']=="Y"){
	$clausulad .= " and cp.visivel = '".$visivel."' ";
       
    }elseif(!empty($visivel) and ($visivel!='undefined') and $rowm['master']!="Y"){
	$clausulad .= " and cp.visivel = 'S' ";
       
    }elseif($rowm['master']!="Y"){
	$clausulad .= " and cp.visivel = 'S' ";       
    }
  
	
		if(!empty($idagencia)){
			$andagencia = " and cp.idagencia = ".$idagencia." ";
			
		}else{
			$andagencia = " ";
		}
        
        if($buscaitens=='Y'){
            $sqlit=" union all
                        SELECT cp.tipoobjetoorigem as tipoobjeto ,cp.idcontapagar as id,cp.idobjetoorigem as idobjeto,'' as obs,cp.valor,pg.datareceb as datareceb,dma(pg.datareceb) as dmadatareceb,cp.status,pg.tipo as tipo, '' as idcontadesc ,cp.idcontaitem,'' as parcelas,'' as parcela,a.agencia,'CONTA ITEM' as tipoespecifico,p.nome,cp.idpessoa,f.descricao,pg.ndocumento
                        FROM agencia a,contapagaritem cp ".$joincp." left join formapagamento f on(cp.idformapagamento=f.idformapagamento)				
                         ".$cpessoa."
                        where " .$clausulai." 
			
                        a.idagencia = cp.idagencia 
                        ".getidempresa('cp.idempresa','contas')."
                         ".$andagencia."";
        }else{
            $sqlit="";
        }
        
				$sql = "select * from (
                                        SELECT cp.tipoobjeto ,cp.idcontapagar as id,cp.idobjeto,cp.obs,cp.valor,datareceb,dma(datareceb) as dmadatareceb,cp.status,cp.tipo as tipo, cp.idcontadesc ,cp.idcontaitem,cp.parcelas,cp.parcela,a.agencia,cp.tipoespecifico,p.nome,cp.idpessoa,f.descricao,cp.ndocumento
                                            FROM agencia a,contapagar cp ".$joincontaitem."
                                            left join contadesc cd on ( cp.idcontadesc = cd.idcontadesc)
                                            left join formapagamento f on ( cp.idformapagamento = f.idformapagamento)
                                             ".$cpessoa."
                                             " . $clausulad ."
                                            and a.idagencia = cp.idagencia 
                                            and cp.tipoespecifico='NORMAL'
                                            ".getidempresa('cp.idempresa','contapagar')."
                                            ".$andagencia." 
                                            ".$sqlit."
                                         
                                        ) as u
                       order by u.datareceb asc,u.status desc,u.id asc ";
     
                    $sqlg = "select 
                                    tipoobjeto,
                                    id,
                                   idobjeto,
                                   obs,
                                   sum(valor) as valor,
                                   datareceb,
                                   dmadatareceb,
                                   status,
                                    tipo,
                                   idcontadesc,
                                   idcontaitem,
                                   parcelas,
                                   parcela,
                                   agencia,
                                   tipoespecifico,
                                   nome,
                                   idpessoa,
                                   descricao,
                                   ndocumento
                            from (
                                        SELECT cp.tipoobjeto ,cp.idcontapagar as id,cp.idobjeto,cp.obs,cp.valor,datareceb,dma(datareceb) as dmadatareceb,cp.status,cp.tipo as tipo, cp.idcontadesc ,cp.idcontaitem,cp.parcelas,cp.parcela,a.agencia,cp.tipoespecifico,p.nome,cp.idpessoa,f.descricao,cp.ndocumento
                                            FROM agencia a,contapagar cp ".$joincontaitem."
                                            left join contadesc cd on ( cp.idcontadesc = cd.idcontadesc)
                                            left join formapagamento f on ( cp.idformapagamento = f.idformapagamento)
                                             ".$cpessoa."
                                             " . $clausulad ."
                                            and a.idagencia = cp.idagencia 
                                            and cp.tipoespecifico='NORMAL'
                                            ".getidempresa('cp.idempresa','contapagar')."
                                            ".$andagencia." 
                                            ".$sqlit."
                                         
                                        ) as u group by u.idpessoa
                       order by u.datareceb asc, u.nome asc,u.status desc,u.id asc ";


}

?>

<html>
<head>
<title><?=$_header?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />
<style type="text/css">
   table { page-break-inside:auto }
    tr    { page-break-inside:avoid; page-break-after:auto }
    thead { display:table-header-group }
    tfoot { display:table-footer-group }
</style>
</head>
<body>
<?
$_nomeimpressao = "[".md5(date('dmYHis'))."] gerada em [".date(" d/m/Y H:i:s")."]";


$sqlfig="select logosis from empresa where idempresa =".cb::idempresa();
$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
$figrel=mysqli_fetch_assoc($resfig);

//$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
//$figurarelatorio = "../inc/img/repheader.png";
$figurarelatorio = $figrel["logosis"];
if (!empty($sql)){
    $res = d::b()->query($sql) or die("Falha ao pesquisar NF : " . mysqli_error(d::b()) . "<p>SQL: $sql");
    echo "<!--".$sql."-->";
    $ires = mysqli_num_rows($res);
    $somatotais = 0;
    $vlrcredito = 0;
    $vlrdebito = 0;
    $qtdcred = 0;
    $qtddeb = 0;
    $parc='';
//echo($sql);
?>
<table class="tbrepheader">
<tr>
    <td rowspan="3" style="width:50px;"><img style="width:100%;" src="<?=$figurarelatorio?>"></td>
    <td class="header">Contas a Pagar</td>
</tr>
</table>
<br>
<fieldset class="fldsheader">
  <legend>Início da Impressão <?=$_nomeimpressao?></legend>
</fieldset>
<table class="normal">
    <tr class='header'>
        <td>Emissão</td>   
        <td>Nota</td>             
        <td>Pessoa</td>
        <td>Valor</td>
        <td>Vencimento</td>   
        <td>Agência</td>
    </tr>
    <tr class='res'>
<?
$conteudoexport;// guarda o conteudo para exportar para csv
$conteudoexport='"EMISSÃO";"NOTA";"PESSOA";"VALOR";"VENCIMENTO";"AGÊNCIA"';
$conteudoexport.="\n";//QUEBRA DE LINHA NO CONTEUDO CSV

    while($row=mysqli_fetch_assoc($res)){
    $descnf="";
    $cortr = "";
    $pessoa='';
               
        if($row["tipoobjeto"] == "nf" and !empty($row["idobjeto"] )){
                    
                   
		    $sqlf = "select n.idnf,p.nome,n.tiponf,n.controle,n.nnfe,n.dtemissao as emissao from nf n,pessoa p where p.idpessoa = n.idpessoa  and idnf =".$row["idobjeto"];
		    $qrf = d::b()->query($sqlf) or die("Erro ao buscar nome do cliente da nota:".mysqli_error(d::b()));
		    $qtdrowsf= mysqli_num_rows($qrf);
		    $resf = mysqli_fetch_assoc($qrf);
		    if($resf["tiponf"]=="C"){
			$tiponf="Entrada";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }
		    if($resf["tiponf"]=="V"){
			$tiponf="Saída";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }			
		    if($resf["tiponf"]=='S'){ 
			$tiponf="Serviço";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }
		    if($resf["tiponf"]=='T'){ 
			$tiponf="Cte";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }
		    if($resf["tiponf"]=='E'){ 
			$tiponf="Consessionária";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }
		    if($resf["tiponf"]=='M'){ 
			$tiponf="Manual/Cupom";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
			}
			if($resf["tiponf"]=='B'){ 
			$tiponf="Recibo";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
			}
		    if($resf["tiponf"]=='F'){ 
			$tiponf="Fatura";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }	
                    if($resf["tiponf"]=='R'){ 
			$tiponf="PJ";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }	
		    $pessoa = $resf["nome"];
                    $emissao=$resf['emissao'];
			
		}elseif($row["tipoobjeto"] == "notafiscal" and !empty($row["idobjeto"] )){
			
		    $sqlf = "select p.nome,n.numerorps,n.nnfe,n.idnotafiscal,n.emissao from notafiscal n,pessoa p where  p.idpessoa = n.idpessoa and idnotafiscal =".$row["idobjeto"];
		    $qrf = d::b()->query($sqlf) or die("Erro ao buscar nome do cliente da nota:".mysqli_error(d::b()));
		    $qtdrowsf= mysqli_num_rows($qrf);
		    $resf = mysqli_fetch_assoc($qrf);
		    $tiponf="Saída";	
		    $pessoa = $resf["nome"];
		    $descnf = "NFS-e - ".$resf["nnfe"];
                    $emissao=$resf['emissao'];
			
		}else{	
		   if(!empty($row["idformapagamento"])){
			$sqlff = "select c.descricao from formapagamento c  where c.idformapagamento =".$row["idformapagamento"];			
			$qrff = d::b()->query($sqlff) or die("Erro ao buscar descrição da formapagamento:".mysqli_error(d::b()));			
			$resff = mysqli_fetch_assoc($qrff);			
                        $descnf=$row["ndocumento"];
                        $pessoa= $resff["descricao"];
                        $janelanf = "janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=".$row["idcontapagar"]."');";
                         $emissao='';
                    }else{
                        $descnf=$row["ndocumento"];
                        $pessoa="";
                        $janelanf ="";
                        $emissao='';
                    }	    
		  
		}
		?>
        <?			
		if(!empty($row["idcontaitem"])){
			$sqlf2 = "select c.contaitem from contaitem c  where c.idcontaitem =".$row["idcontaitem"];			
			$qrf2 = d::b()->query($sqlf2) or die("Erro ao buscar descrição item da nota:".mysqli_error(d::b()));			
			$resf2 = mysqli_fetch_assoc($qrf2);
			$intemext = $resf2["contaitem"];
		}else{
		    $intemext="";
		}
		    
		
		if($row["idpessoa"]){
		    $pessoa =traduzid("pessoa","idpessoa","nome",$row["idpessoa"]);
		}elseif($row["idcontadesc"]){
		    $pessoa =traduzid("contadesc","idcontadesc","contadesc",$row["idcontadesc"]);
		}	
		?>
	    <tr class="respreto">
			
		<td align="center" class="nowrap">
            <?=dma($emissao)?>
		</td> 
		<td nowrap align=""><?=$descnf?></td>
        <td nowrap ><?=$pessoa?></td>
        <td nowrap align="right">
            <?=number_format(tratanumero($row["valor"]), 2, ',', '.');?>
        </td>
        <td nowrap><?=$row["dmadatareceb"] ?></td>
        <td nowrap align="center"><?=$row["agencia"]?></td>
	</tr>
    <?
    $conteudoexport.='"'.dma($emissao).'";"'.$descnf.'";"'.$pessoa.'";"R$'.number_format(tratanumero($row["valor"]), 2, ',', '.').'";"'.$row["dmadatareceb"].'";"'.$row["agencia"].'";';	
    $conteudoexport.="\r\n";//QUEBRA DE LINHA NO CONTEUDO CSV
    ?>
 <?}//while($rowp=mysqli_fetch_assoc($resp)){				
	?>	
	
    </tr>
</table>
<?}?>

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
    
	$infilename = empty($_header)?$_rep:$_header;
	$infilename = preg_replace("/[^A-Za-z0-9s.]/", "", $infilename);
	//gera o csv
	header("Content-type: text/csv; charset=utf-8");
	header("Content-Disposition: attachment; filename=".$infilename.".csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	echo utf8_decode($conteudoexport);
	
}
?>