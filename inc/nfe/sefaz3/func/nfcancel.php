<?php
include_once("../../../php/functions.php");
ini_set("display_errors","1");
error_reporting(E_ALL);
/*require_once('../libs/ToolsNFePHP.class.php');
header('Content-type: text/html; charset=UTF-8');
$nfe = new ToolsNFePHP;

$id = "35110358716523000119550000000103241701643172";
$protId = "135110002251645";
$xJust = "Teste de cancelamento da versao 2.0, release 2a.2.31";
$modSOAP = '1';

$resp = $nfe->cancelNF($id, $protId, $xJust, $modSOAP);

echo print_r($resp);
echo '<BR>';
echo $nfe->errMsg.'<BR>';
echo '<PRE>';
echo htmlspecialchars($nfe->soapDebug);
echo '</PRE><BR>';
*/
?>
<?php
/**
 * testaCancelaEvent
 * 
 * Rotina de teste de cancelamento por evento
 * 
 * Corrija os dados para o cancelamento antes de testar
 */
require_once('../libs/NFe/ToolsNFePHP.class.php');
//conectabanco();
$idnotafiscal = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar

$sql="select SUBSTRING(idnfe,4) as idnfe,protocolonfe,infcpl from nf where idnf=".$idnotafiscal;
$res=d::b()->query($sql) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
$row=mysqli_fetch_assoc($res);

if(empty($row['idnfe']) or empty($row['protocolonfe'])){
	die("Não foi encontrado o protocolo da notafiscal para cancelar a NF");
}

$nfe = new ToolsNFePHP;
//$chNFe = "31130323259427000104550010000005021110385551";
//$nProt = "135110002251645";
$chNFe=$row['idnfe'];
$nProt=$row['protocolonfe'];
if(empty($row['infcpl'])){
	$xJust = "A NF foi cancelada pois foi emitida com informações incorretas";
}else{
	$xJust =$row['infcpl'];
}

$tpAmb = '1';
$modSOAP = '2';

if ($resp = $nfe->cancelEvent($chNFe,$nProt,$xJust,$tpAmb,$modSOAP)){
    header('Content-type: text/xml; charset=UTF-8');
    
    	$sql="update nf 
   			set envionfe='CANCELADA',status='CANCELADO'   			
   			where idnf = ".$idnotafiscal;
	$retx = d::b()->query($sql) or die("Erro ao atualizar nf sql:".$sql);
	
	$sql="delete l.* 
        from nfitem i,lotecons l 
        where l.idobjeto = i.idnfitem 
        and l.tipoobjeto='nfitem' 
        and i.idnf=".$idnotafiscal;
	$retx = d::b()->query($sql) or die("Erro ao atualizar nf sql:".$sql);
	
	echo $resp;
    
} else {
    header('Content-type: text/html; charset=UTF-8');
    echo '<BR>';
    echo $nfe->errMsg.'<BR>';
    echo '<PRE>';
    echo htmlspecialchars($nfe->soapDebug);
    echo '</PRE><BR>';
}    
?>

