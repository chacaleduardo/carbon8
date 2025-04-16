<?php
require_once("../../../../inc/php/validaacesso.php");

require_once("../../../../api/nf/index.php");
require_once("../../../../form/controllers/pedido_controller.php");


//ini_set("display_errors","1");

//error_reporting(E_ALL);


use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use NFePHP\NFe\Common\FakePretty;


require_once "../vendor/autoload.php";




$qr = "SELECT 
			 n.idempresa		
	from nf n 
	where n.idnf = ".$_GET["idnotafiscal"];
$rs = d::b()->query($qr);
$rs = mysqli_fetch_assoc($rs);


$_idempresa=$rs["idempresa"];


$idnotafiscal = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar

$_sql = "SELECT *,str_to_date(nfatualizacao,'%d/%m/%Y %h:%i:%s') as nfatt FROM empresa WHERE idempresa = ".$_idempresa;
$_res=d::b()->query($_sql) or die(mysqli_error(d::b())." erro ao buscar o empresa na tabela empresa ".$_sql);
$_row=mysqli_fetch_assoc($_res);

if(empty($_row["certificado"]) or empty($_row["senha"])){
    die("Alerta: Favor vericar o cadastro do certificado digital da empresa no sistema.");
}


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


$sql="select idnfe from nf where idnf=".$idnotafiscal;
$res=d::b()->query($sql) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
$row=mysqli_fetch_assoc($res);

if(empty($row['idnfe']) ){
	die("Não foi encontrado o protocolo da notafiscal");
}

//$nfe = new ToolsNFePHP;
//$chNFe = "31130323259427000104550010000005021110385551";
//$nProt = "135110002251645";
$chNFe=$row['idnfe'];

$tpAmb = '1';
$modSOAP = '2';

try {
	
		$tools = new NFePHP\NFe\Tools($configJson, NFePHP\Common\Certificate::readPfx($certificadoDigital, $_row["senha"]));
		// $certificate = Certificate::readPfx($content, 'senha');
		//$tools = new Tools($configJson, $certificate);
		$tools->model('55');

		$chave = $chNFe;
		$justificativa = null;
        $tipo = $tools::EVT_CIENCIA;
        $xml = $tools->sefazManifesta($chave, $tipo, $justificativa);


        $st = new NFePHP\NFe\Common\Standardize();
        $std = $st->toStd($xml);
        
        
        //print_r($std);
        if ($std->cStat != 128) {
            //erro registrar e voltar
            echo("[$std->cStat] $std->xMotivo");
        

/*
        $stdCl = new Standardize($xml);
		//nesse caso $std irá conter uma representação em stdClass do XML
		$std = $stdCl->toStd();
        if ($std->cStat != 128) {
            echo("Falha ao declarar ciência da operação.");
        }*/
        }else{

            $vcStat=$std->retEvento->infEvento->cStat;
            if($vcStat== 596){
                $xMotivo=$std->retEvento->infEvento->xMotivo;
                //[596]Rejeicao: Evento apresentado apos o prazo permitido para o evento: [10 dias]

                $sql="select * from nfentradaxml where chave = '".$chNFe."'";
                $resn= d::b()->query($sql);
                $qtdn=mysqli_num_rows($resn);
                if($qtdn>0){
                    $rown=mysqli_fetch_assoc($resn);
                    if(empty($rown['idnf'])){

                        $sqlu="update nfentradaxml set idnf=".$idnotafiscal." where idnfentradaxml=".$rown['idnfentradaxml'];
                        $res=d::b()->query($sqlu) or die("Erro ao atualizar  nfentradaxml sql=".$sqlu);
                
                        $sqlu="update nf n join nfentradaxml x on(x.idnf=n.idnf) set n.xmlret=x.xml,envionfe = 'CONCLUIDA' where n.idnf=".$idnotafiscal." and x.idnfentradaxml=".$rown['idnfentradaxml'];
                        $res=d::b()->query($sqlu) or die("Erro ao atualizar  nota sql=".$sqlu);

                        echo("XML baixado SUCESSO!!!");


                    }else{
                        echo("Nota ja esta vinculada no sistema".$rown['idnf']);
                    }

                }else{
                    echo("[1]-Permitido para o evento: [10 dias] , Após é necessário baixar o XML diretamento no site do Sefaz.");
                }
                

                

               
            }else{
                 //nao juntar os updates
                $sql1="update nf
                set envionfe='MANIFESTADA'
                where idnf = ".$idnotafiscal;
                $retx1 = mysql_query($sql1) or die("Erro ao atualizar nf sql:".$sql1);
                
                $sql="update nf 
                set xml = '".$xml."'
                where idnf = ".$idnotafiscal;
                $retx = mysql_query($sql);
                
                //header('Content-type: text/xml; charset=UTF-8');
                // echo($xml);
                echo("Ciência da operação declarada com SUCESSO!!!");

            }
            	
           

        }
	    

} catch (\Exception $e) {
    echo $e->getMessage();
}
?>