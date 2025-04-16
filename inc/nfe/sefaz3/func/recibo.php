<?php
include_once("../../../php/functions.php");
/*
 * Exemplo de solicitação da situação da NFe atraves do numero do 
 * recibo de uma nota enviada e recebida com sucesso pelo SEFAZ
 */
require_once('../libs/NFe/ToolsNFePHP.class.php');
//conectabanco();
$idnotafiscal = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar

// $sql="select * from "._DBAPP.".nf where envionfe='ENVIADO' and recibo is not null and idnf=".$idnotafiscal;
$sql="select * from "._DBAPP.".nf where recibo is not null and idnf=".$idnotafiscal;
$res=d::b()->query($sql) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
$row=mysqli_fetch_assoc($res);

if(empty($row['recibo'])){
	die("Não foi encontrado o recibo da notafiscal para consulta");
}

$nfe = new ToolsNFePHP;
$modSOAP = '2'; //usando cURL 
$recibo = $row['recibo']; //310000739838621 este e o numero do seu recibo mude antes de executar este script
$chave = '';
$tpAmb = '1'; //homologação

header('Content-type: text/html; charset=UTF-8');
if ($aResp = $nfe->getProtocol($recibo, $chave, $tpAmb, $modSOAP)){
    //houve retorno mostrar dados
  // print_r($aResp);
  // echo($aResp['aProt'][0]['xMotivo']);
 	if(!empty($aResp)){
 		$doc = DOMDocument::loadXML($aResp);
 		$cab = $doc->getElementsByTagName("nfeRetAutorizacaoLoteResult")->item(0);
 		$cab2 = $cab->getElementsByTagName("retConsReciNFe")->item(0);
 		$cab3 = $cab2->getElementsByTagName("protNFe")->item(0); 		
 		$cab4 = $cab3->getElementsByTagName("infProt")->item(0);
 		$nProt = $cab4->getElementsByTagName("nProt")->item(0);
 		$protocolonfe =($nProt->textContent); //->length;
 		
 		$xMotivo = $cab4->getElementsByTagName("xMotivo")->item(0);
 		$xMens =($xMotivo->textContent); //->length;
 		   
 		$xmlret=$aResp;
 		
 		echo($xMens." Protocolo (".$protocolonfe.")");
 		
 		$chNFe = $cab4->getElementsByTagName("chNFe")->item(0);
 		$xchNFe =($chNFe->textContent); //->length; 		
 		$dhRecbto = $cab4->getElementsByTagName("dhRecbto")->item(0);
 		$xdhRecbto =($dhRecbto->textContent); //->length; 		
 		$digVal = $cab4->getElementsByTagName("digVal")->item(0);
 		$xdigVal =($digVal->textContent); //->length; 		
 		$verAplic = $cab4->getElementsByTagName("verAplic")->item(0);
 		$xverAplic =($verAplic->textContent); //->length;
 			
 		$xmlprot="<protNFe versao=\"3.10\"><infProt><tpAmb>1</tpAmb><verAplic>".$xverAplic."</verAplic><chNFe>".$xchNFe."</chNFe><dhRecbto>".$xdhRecbto."00</dhRecbto><nProt>".$protocolonfe."</nProt><digVal>".$xdigVal."</digVal><cStat>100</cStat><xMotivo>Autorizado o uso da NF-e</xMotivo></infProt></protNFe>";

	   
	   if(!empty($xmlret) and !empty($protocolonfe)){
	   	/*$xmlret = str_replace('<?xml version="1.0" encoding="UTF-8"?>','',$xmlret);*/
	   	
	   	//Retirar o cabeçalho soap e montar um novo xml 05/05/2015 hermesp
	   	$chNFe = $cab4->getElementsByTagName("chNFe")->item(0);
	   	$xchNFe =($chNFe->textContent); //->length;
	   	$dhRecbto = $cab4->getElementsByTagName("dhRecbto")->item(0);
	   	$xdhRecbto =($dhRecbto->textContent); //->length;
	   	$digVal = $cab4->getElementsByTagName("digVal")->item(0);
	   	$xdigVal =($digVal->textContent); //->length;
	   	$verAplic = $cab4->getElementsByTagName("verAplic")->item(0);
	   	$xverAplic =($verAplic->textContent); //->length;
	   	
	   	$xmlprot="<protNFe versao=\"3.10\"><infProt><tpAmb>1</tpAmb><verAplic>".$xverAplic."</verAplic><chNFe>".$xchNFe."</chNFe><dhRecbto>".$xdhRecbto."</dhRecbto><nProt>".$protocolonfe."</nProt><digVal>".$xdigVal."</digVal><cStat>100</cStat><xMotivo>Autorizado o uso da NF-e</xMotivo></infProt></protNFe>";
	   	 
	   	
	   	$sql="update "._DBAPP.".nf 
	   			set protocolonfe='".$protocolonfe."',
	   			xmlret= concat('<?xml version=\"1.0\" encoding=\"UTF-8\"?><nfeProc versao=\"3.10\" xmlns=\"http://www.portalfiscal.inf.br/nfe\">',xml,'".$xmlprot."','</nfeProc>'),
	   			envionfe='CONCLUIDA',
	   			obsint = infcpl,
	   			infcpl = null		
	   			where idnf = ".$idnotafiscal;
		$retx = d::b()->query($sql) or die("Erro ao atualizar nf sql:".$sql);
		
	   }
	}else{
		echo("Não ouve resposta!!!");
	}
   
} else {
    //não houve retorno mostrar erro de comunicação
    echo "Houve erro !! $nfe->errMsg";
}
//31130223259427000104550010000003211918352224   
   
?>