<?php
include_once("../../../functions.php");


$idnotafiscal = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar

$sql="select idnfe from nf where  idnf=".$idnotafiscal;
$res=mysql_query($sql) or die(mysql_error()." erro ao buscar o numero do recibo ".$sql);
$qtd=mysql_num_rows($res);
if($qtd==0){
 die("Verificar dados da NF, possivel ainda n�o foi Manifestada.");	
}
$row=mysql_fetch_assoc($res);

if(empty($row['idnfe']) ){
	die("N�o foi encontrada a chave da notafiscal");
}

require_once('../libs/NFe/ToolsNFePHP.class.php');



$nfe = new ToolsNFePHP;

$modSOAP = '2'; //usando cURL

$tpAmb = '1';//usando produ��o

$chNFe = $row['idnfe']; // chave nfe

$AN = true; // buscar no ambiente nacional




if (!$xml = $nfe->getNFe($AN, $chNFe, $tpAmb, $modSOAP)){
    header('Content-type: text/html; charset=UTF-8');
    echo "Houve erro !! $nfe->errMsg";
    //echo '<br><br><PRE>';
   // echo htmlspecialchars($nfe->soapDebug);
    //echo '</PRE><BR>';
} else {
	$doc = DOMDocument::loadXML($xml);

	$nfeProc 	= $doc->getElementsByTagName("nfeProc")->item(0);	
	$NFe 		= $nfeProc->getElementsByTagName("NFe")->item(0); 	      		
	$infNFe 	= $NFe->getElementsByTagName("infNFe")->item(0);
	$ide 		= $infNFe->getElementsByTagName("ide")->item(0);
	$dhEmi 		= $ide->getElementsByTagName("dhEmi")->item(0);

	$timestamp = strtotime($dhEmi->textContent);
	$newDate = date("Y-m-d H:i:s", $timestamp );

	$xml = str_replace("'", "",$xml);

	$sql="update nf 
	set envionfe='CONCLUIDA', dtemissao = '".$newDate."',xmlret = '".$xml."'
	where idnf = ".$idnotafiscal;
	$retx = mysql_query($sql) or die("Erro ao atualizar nf sql:".$sql);
	
  //  header('Content-type: text/xml; charset=UTF-8');
    echo("XML baixado com sucesso.");
    //print_r($xml);
    //echo '<BR><BR><BR><BR><BR>';
    //print_r($resp);
}


/*

$xml = $nfe->getNFe($AN, $chNFe, $tpAmb, $modSOAP);
//echo print_r($xml);
$ERRO=$nfe->errMsg;

if(empty($ERRO)){

	$sql="update "._DBAPPORIGEM.".nf 
	set envionfe='CONCLUIDA',xmlret = '".$xml."'
	where idnf = ".$idnotafiscal;
	$retx = mysql_query($sql) or die("Erro ao atualizar nf sql:".$sql);
}else{
	echo($ERRO);
}
//echo '<BR>';



Echo("PROCESSADO...");

//echo htmlspecialchars($nfe->soapDebug);



?>
<?php
include_once("../../../php/functions.php");


//conectabanco();
$idnotafiscal = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar

$sql="select idnfe from "._DBAPP.".nf where  idnf=".$idnotafiscal;
$res=d::b()->query($sql) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
$qtd=mysqli_num_rows($res);
if($qtd==0){
 die("Verificar dados da NF, possivel ainda n�o foi Manifestada.");	
}
$row=mysqli_fetch_assoc($res);

if(empty($row['idnfe']) ){
	die("N�o foi encontrada a chave da notafiscal");
}

require_once('../libs/NFe/ToolsNFePHP.class.php');



$nfe = new ToolsNFePHP;

$modSOAP = '2'; //usando cURL

$tpAmb = '1';//usando produ��o

$chNFe = $row['idnfe']; // chave nfe

$AN = true; // buscar no ambiente nacional




if (!$xml = $nfe->getNFe($AN, $chNFe, $tpAmb, $modSOAP)){
    header('Content-type: text/html; charset=UTF-8');
    echo "Houve erro !! $nfe->errMsg";
    //echo '<br><br><PRE>';
   // echo htmlspecialchars($nfe->soapDebug);
    //echo '</PRE><BR>';
} else {
	$xml = str_replace("'", "",$xml);
	$sql="update "._DBAPP.".nf 
	set envionfe='CONCLUIDA',xmlret = '".$xml."'
	where idnf = ".$idnotafiscal;
	$retx = d::b()->query($sql) or die("Erro ao atualizar nf sql:".$sql);
	
  //  header('Content-type: text/xml; charset=UTF-8');
    echo("XML baixado com sucesso.");
    //print_r($xml);
    //echo '<BR><BR><BR><BR><BR>';
    //print_r($resp);
}


/*

$xml = $nfe->getNFe($AN, $chNFe, $tpAmb, $modSOAP);
//echo print_r($xml);
$ERRO=$nfe->errMsg;

if(empty($ERRO)){

	$sql="update "._DBAPP.".nf 
	set envionfe='CONCLUIDA',xmlret = '".$xml."'
	where idnf = ".$idnotafiscal;
	$retx = d::b()->query($sql) or die("Erro ao atualizar nf sql:".$sql);
}else{
	echo($ERRO);
}
//echo '<BR>';



Echo("PROCESSADO...");

//echo htmlspecialchars($nfe->soapDebug);



?>
