<?php
include_once("../../../php/functions.php");
//require_once('../libs/NFe/ToolsNFePHP.class.php');
/*
 * Exemplo de solicitação da situação da NFe atraves do numero do 
 * recibo de uma nota enviada e recebida com sucesso pelo SEFAZ
 */

//BIBLIOTECA PARA NFS4.0
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\Common\Soap\SoapCurl;
require_once "../vendor/autoload.php";

/*
$qr = "SELECT 
ifnull(idempresafat,idempresa) as idempresa 
from nf where idnf = ".$_GET["idnotafiscal"];
$rs = d::b()->query($qr);
$rs = mysqli_fetch_assoc($rs);

//$_idempresa = isset($rs["idempresa"])?$rs["idempresa"]:$_GET["_idempresa"];
$_idempresa=$rs["idempresa"];
*/

$qr = "SELECT 
			case n.idempresa 
				when 8 then 1
				else n.idempresa
			end as idempresa,n.idempresafat,o.natoptipo
	from nf n 
	join natop o on(o.idnatop=n.idnatop)
	where n.idnf = ".$_GET["idnotafiscal"];
$rs = d::b()->query($qr);
$rs = mysqli_fetch_assoc($rs);

// _idempresa = isset($rs["idempresa"])?$rs["idempresa"]:$_GET["_idempresa"];
if(!empty($rs["idempresafat"]) and $rs["natoptipo"]!='transferencia'){
	$_idempresa=$rs["idempresafat"];
}else{
	$_idempresa=$rs["idempresa"];
}


//conectabanco();
$idnotafiscal = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar


// $sql="select * from "._DBAPP.".nf where envionfe='ENVIADO' and recibo is not null and idnf=".$idnotafiscal;
$sql="select * from "._DBAPP.".nf where recibo is not null and idnf=".$idnotafiscal;
$res=d::b()->query($sql) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
$row=mysqli_fetch_assoc($res);

if(empty($row['recibo'])){
	die("Não foi encontrado o recibo da notafiscal para consulta");
}

$_sql = "SELECT *,str_to_date(nfatualizacao,'%d/%m/%Y %h:%i:%s') as nfatt FROM empresa WHERE idempresa = ".$_idempresa;
$_res=d::b()->query($_sql) or die(mysqli_error(d::b())." erro ao buscar o empresa na tabela empresa ".$_sql);
$_row=mysqli_fetch_assoc($_res);

 $config = [
    "atualizacao" => $_row["nfatt"],
    "tpAmb" => 1, // Se deixar o tpAmb como 2 você emitirá a nota em ambiente de homologação(teste) e as notas fiscais aqui não tem valor fiscal
    "razaosocial" => $_row["nfrazaosocial"],
    "siglaUF" => $_row["nfsiglaUF"],
    "cnpj" => $_row["nfcnpj"],
    "schemes" => $_row["nfschemes"], 
    "versao" => $_row["nfversao"],
    "tokenIBPT" => $_row["nftokenIBPT"]
    ];
$configJson = json_encode($config);
$certificadoDigital = file_get_contents($_row["certificado"]);
$tools = new NFePHP\NFe\Tools($configJson, NFePHP\Common\Certificate::readPfx($certificadoDigital, $_row["senha"]));

  //contingencia
  if($_row["contingencia"]=="Y"){
	if(empty($_row["datacontingencia"])){
	   die('Preencher a data de inicio da contingencia no cadastro da empresa.'); 
	}
	//NOTA: esse json pode ser criado com Contingency::class
	//$timestamp = time();//1484747583
			$timestamp=strtotime($_row["datacontingencia"]);
	$contingency = '{
		"motive":"SEFAZ fora do AR",
		"timestamp":'.$timestamp.',
		"type":"SVCAN",
		"tpEmis":6
	}';
	//indica que estamos em modo de contingência, se nada for passado estaremos em modo normal
	$tools->contingency->activate("MG", "SEFAZ fora do AR", $tipo = '');
	$tools->contingency->load($contingency);
}

$resp = $tools->sefazConsultaRecibo($row['recibo'],1);

if($_row["contingencia"]=="Y"){
	$tools->contingency->deactivate();
}

header('Content-type: text/html; charset=UTF-8');
if ($resp){
   $aResp = $resp;
   print($aResp);
    if(!empty($resp)){
	$doc = DOMDocument::loadXML($aResp);
	$cab = $doc->getElementsByTagName("nfeResultMsg")->item(0);
	$cab2 = $cab->getElementsByTagName("retConsReciNFe")->item(0); 
	$cab3 = $cab2->getElementsByTagName("protNFe")->item(0); 
	if($cab3 != null){	
			
		$cab4 = $cab3->getElementsByTagName("infProt")->item(0);
		$nProt = $cab4->getElementsByTagName("nProt")->item(0);
		$protocolonfe =($nProt->textContent); //->length;
		$xMotivo = $cab4->getElementsByTagName("xMotivo")->item(0);
		$xMens =($xMotivo->textContent); //->length;
		$xmlret=$aResp;

		

		$chNFe = $cab4->getElementsByTagName("chNFe")->item(0);
		$xchNFe =($chNFe->textContent); //->length; 		
		$dhRecbto = $cab4->getElementsByTagName("dhRecbto")->item(0);
		$xdhRecbto =($dhRecbto->textContent); //->length; 		
		$digVal = $cab4->getElementsByTagName("digVal")->item(0);
		$xdigVal =($digVal->textContent); //->length; 		
		$verAplic = $cab4->getElementsByTagName("verAplic")->item(0);
		$xverAplic =($verAplic->textContent); //->length;
		$xmlprot="<protNFe versao=\"4.00\"><infProt><tpAmb>1</tpAmb><verAplic>".$xverAplic."</verAplic><chNFe>".$xchNFe."</chNFe><dhRecbto>".$xdhRecbto."00</dhRecbto><nProt>".$protocolonfe."</nProt><digVal>".$xdigVal."</digVal><cStat>100</cStat><xMotivo>Autorizado o uso da NF-e</xMotivo></infProt></protNFe>";

		if(!empty($xmlret) and !empty($protocolonfe)){

			echo($xMens." Protocolo (".$protocolonfe.")");

			/*$xmlret = str_replace('<?xml version="1.0" encoding="UTF-8"?>','',$xmlret);*/
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

			$xmlprot="<protNFe versao=\"4.00\"><infProt><tpAmb>1</tpAmb><verAplic>".$xverAplic."</verAplic><chNFe>".$xchNFe."</chNFe><dhRecbto>".$xdhRecbto."</dhRecbto><nProt>".$protocolonfe."</nProt><digVal>".$xdigVal."</digVal><cStat>100</cStat><xMotivo>Autorizado o uso da NF-e</xMotivo></infProt></protNFe>";

			//Retirar o cabeçalho soap e montar um novo xml 05/05/2015 hermesp
			$chNFe = $cab4->getElementsByTagName("chNFe")->item(0);
			$xchNFe =($chNFe->textContent); //->length;
			$dhRecbto = $cab4->getElementsByTagName("dhRecbto")->item(0);
			$xdhRecbto =($dhRecbto->textContent); //->length;
			$digVal = $cab4->getElementsByTagName("digVal")->item(0);
			$xdigVal =($digVal->textContent); //->length;
			$verAplic = $cab4->getElementsByTagName("verAplic")->item(0);
			$xverAplic =($verAplic->textContent); //->length;

			$xmlprot="<protNFe versao=\"4.00\"><infProt><tpAmb>1</tpAmb><verAplic>".$xverAplic."</verAplic><chNFe>".$xchNFe."</chNFe><dhRecbto>".$xdhRecbto."</dhRecbto><nProt>".$protocolonfe."</nProt><digVal>".$xdigVal."</digVal><cStat>100</cStat><xMotivo>Autorizado o uso da NF-e</xMotivo></infProt></protNFe>";


			// GERAR SEQUENCIA DE  BOLETO


			$sqllnf = "select controle from nf where idnf = ".$idnotafiscal;
			$resnf = d::b()->query($sqllnf) or die("Erro pesquisando nossonumero na NF:\nSQL:".$sqllnf."\nErro:".mysqli_error(d::b()));
			$rnf = mysqli_fetch_assoc($resnf);


			if(empty($rnf["controle"])){
				
				//if(_NFSECHOLOG)//echo "\n".date("H:i:s")." - geranumerorps:gerando nova rps";

				### Tenta incrementar e recuperar o numerorps
				d::b()->query("LOCK TABLES sequence WRITE;");
				d::b()->query("update sequence set chave1 = (chave1 + 1) where sequence = 'nossonumero'");

				$sqlns = "SELECT chave1 FROM sequence where sequence = 'nossonumero';";

				$resns = d::b()->query($sqlns);

				if(!$resns){
					d::b()->query("UNLOCK TABLES;");
					echo "1-Falha Pesquisando Sequence [nossonumero] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
					die();
				}

				$rowns = mysqli_fetch_array($resns);

				### Caso nao retorne nenhuma linha ou retorn valor vazio
				if(empty($rowns["chave1"])){
					if(!$rowns){
						d::b()->query("UNLOCK TABLES;");
						echo "2-Falha Pesquisando Sequence [nossonumero] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
						die();
					}
				}

				d::b()->query("UNLOCK TABLES;");        

				$sqlnf = "update nf set controle = ".$rowns["chave1"]." where idnf = ".$idnotafiscal;
				d::b()->query($sqlnf) or die("Erro atribuindo nossonumero:\nSQL:".$sqlnf."\nErro:".mysqli_error(d::b()));

			}

			// FIM GERAR SEQUENCIA DE BOLETO




			$sql="update "._DBAPP.".nf 
					set protocolonfe='".$protocolonfe."',
					xmlret= concat('<?xml version=\"1.0\" encoding=\"UTF-8\"?><nfeProc versao=\"4.00\" xmlns=\"http://www.portalfiscal.inf.br/nfe\">',xml,'".$xmlprot."','</nfeProc>'),
					envionfe='CONCLUIDA',
					obsint = infcpl,
					infcpl = null		
					where idnf = ".$idnotafiscal;
			$retx = d::b()->query($sql) or die("Erro ao atualizar nf sql:".$sql);
			
			}else{
					echo($xMens);//E uma falha descrição do erro
			}
		}else{
			die($aResp);	
		}
    }else{
	echo("Não ouve resposta!!!");
    }
} else {
    //não houve retorno mostrar erro de comunicação
    echo "Houve erro !! $nfe->errMsg";
}    
?>