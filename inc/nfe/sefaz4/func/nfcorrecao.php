<?php
include_once("../../../php/functions.php");
ini_set("display_errors","1");
error_reporting(E_ALL);

////BIBLIOTECA PARA NFS4.0
//use NFePHP\NFe\Tools;
//use NFePHP\Common\Certificate;
//use NFePHP\Common\Soap\SoapCurl;
//require_once "../vendor/autoload.php";


use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
require_once "../vendor/autoload.php";
$idnotafiscal = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar

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

    // $tools = new Tools($configJson, Certificate::readPfx($certificadoDigital, '010787'));


$sql="select idnfe,protocolonfe,infcorrecao from nf where idnf=".$idnotafiscal;
$res=d::b()->query($sql) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
$row=mysqli_fetch_assoc($res);

//die($sql);

if(empty($row['idnfe']) or empty($row['protocolonfe'])){
	die("Não foi encontrado o protocolo da notafiscal para cancelar a NF");
}

//$nfe = new ToolsNFePHP;
//$chNFe = "31130323259427000104550010000005021110385551";
//$nProt = "135110002251645";
//LTM - 23-09-2020: Função para deixar apenas números quando validar os dados no sefazCCe
$chNFe = preg_replace("/[^0-9]/", "", $row['idnfe']);
$nProt=$row['protocolonfe'];

if(empty($row['infcorrecao'])){
	die ("Não informado o motivo da correção!!!");
}elseif(strlen($row['infcorrecao']) < 15){
    die ("A justificativa deve ter no mínimo 15 caracteres!!!");
}else{
    $xJust =utf8_encode($row['infcorrecao']);
    //$xJust =$row['infcorrecao'];
}

$tpAmb = '1';
$modSOAP = '2';

try {
    $tools = new NFePHP\NFe\Tools($configJson, NFePHP\Common\Certificate::readPfx($certificadoDigital, $_row["senha"]));



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

   // $certificate = Certificate::readPfx($content, 'senha');
    //$tools = new Tools($configJson, $certificate);
    $tools->model('55');

    $chave = $chNFe;
    //$xJust = 'Erro de digitação nos dados dos produtos';
    $nProt = $nProt;
    
    $sqle="select count(*)+1 as seq from nfcorrecao where idnf=".$idnotafiscal;
    $rese=d::b()->query($sqle) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sqle);
    $rowe=mysqli_fetch_assoc($rese);
    
    $nSeqEvento = $rowe["seq"];
   
    
   echo  $response = $tools->sefazCCe($chave, $xJust, $nSeqEvento);

    if($_row["contingencia"]=="Y"){
	    $tools->contingency->deactivate();
    }


    //você pode padronizar os dados de retorno atraves da classe abaixo
    //de forma a facilitar a extração dos dados do XML
    //NOTA: mas lembre-se que esse XML muitas vezes seré necessário, 
    //      quando houver a necessidade de protocolos
    $stdCl = new Standardize($response);
    //nesse caso $std irá conter uma representação em stdClass do XML
    $std = $stdCl->toStd();
      
    //verifique se o evento foi processado
    if ($std->cStat != 128) {
        echo("Falha ao enviar carta de correção.");
    } else {
        $cStat = $std->retEvento->infEvento->cStat;
       if ($cStat == '101' || $cStat == '155' || $cStat == '135') {

        $xml = Complements::toAuthorize($tools->lastRequest, $response);
             
	    $sql="INSERT INTO nfcorrecao
            (idempresa,idnf,xml,status,criadopor,criadoem,alteradopor,alteradoem)
            values
            (".$_idempresa.",".$idnotafiscal.",'".$xml."','CORRIGIDA','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

	    $retx = d::b()->query($sql) or die("Erro ao atualizar nf correcao sql:".$sql);
            
            $sql="update nf 
			    set infcorrecao = null
			    where idnf = ".$idnotafiscal;
	    $retx = d::b()->query($sql) or die("Erro ao atualizar nf sql:".$sql);

                echo("Correção realizada com sucesso!");

                
        } else {
            //houve alguma falha no evento 
            //TRATAR
	   
	    echo '<BR>Erro: ';
	    echo $cStat.'<BR>';
	    echo '<PRE>';
	    //echo htmlspecialchars($nfe->soapDebug);
	    echo '</PRE><BR>';
		}
    

    }    
} catch (\Exception $e) {
    echo $e->getMessage();
}



?>


