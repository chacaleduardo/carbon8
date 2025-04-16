<?php
error_reporting(E_ALL);

include_once("../../php/functions.php");
require_once('../vendor/nfephp-org/sped-mdfe/bootstrap.php');

use NFePHP\Common\Certificate;
use NFePHP\MDFe\Common\Standardize;
use NFePHP\MDFe\Tools;

$idmdfe= $_GET["idmdfe"]; //Id da nota fiscal que vem da acao de enviar


// $sql="select * from "._DBAPP.".nf where envionfe='ENVIADO' and recibo is not null and idnf=".$idnotafiscal;
$sql="select SUBSTRING(chave,5) as nchave, protocolo as nprotocolo,recibo,idempresa from "._DBAPP.".mdfe where protocolo is not null and idmdfe=".$idmdfe;
$res=d::b()->query($sql) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
$row=mysqli_fetch_assoc($res);

/*
if(empty($row['recibo'])){
	die("Não foi encontrado o recibo do MDFe para consulta");
}

*/

$_sql = "SELECT *,str_to_date(nfatualizacao,'%d/%m/%Y %h:%i:%s') as nfatt FROM empresa WHERE idempresa = ".$row['idempresa'];
$_res=d::b()->query($_sql) or die(mysqli_error(d::b())." erro ao buscar o empresa na tabela empresa ".$_sql);
$_row=mysqli_fetch_assoc($_res);

$config = [
    "atualizacao" => date('Y-m-d H:i:s'),
    "tpAmb" => 1,
    "razaosocial" => $_row["nfrazaosocial"],
    "cnpj" => $_row["nfcnpj"],
    "ie" => '',
    "siglaUF" => $_row["nfsiglaUF"],
    "versao" => '3.00'
];


//$certificadoDigital = file_get_contents('../../nfe/sefaz4/certs/1001845428certificadolaudolaboratorio10fb4b9_56044.08fb4b9_56044.21fb4b9_56044.pfx');
$arquivo=str_replace("../","../../nfe/sefaz4/",$_row["certificado"]);

$certificadoDigital = file_get_contents($arquivo);

try {
    $certificate = Certificate::readPfx($certificadoDigital, $_row["senha"]);

    $tools = new Tools(json_encode($config), $certificate);

    $chave = $row['nchave'];
    $nProt = $row['nprotocolo'];
    $cUF = $_row['cuf'];
    $cMun = $_row['cmun'];
  //  $dtEnc = 'Y-m-d'; // Opcional, caso nao seja preenchido pegara HOJE
    $resp = $tools->sefazEncerra($chave, $nProt, $cUF, $cMun, $dtEnc);

    $st = new Standardize();
    $std = $st->toStd($resp);

    $cStat=$std->infEvento->cStat;
    $xMotivo=$std->infEvento->xMotivo;
    $nProt=$std->infEvento->nProt;

    if ($cStat == '135') {
	    $statusmdfe='CONCLUIDO';
	}else{
        $statusmdfe='AUTORIZADO';
    }

    echo($xMotivo." ".$nProt);
	
    $sql="update "._DBAPP.".mdfe 
    set statusmdfe='".$statusmdfe."',mensagem='".$xMotivo." ".$nProt."'				
    where  idmdfe = ".$idmdfe;
    $retx = d::b()->query($sql) or die("Erro ao atualizar mdfe sql:".$sql);
/*
    echo '<pre>';
    print_r($std);
    echo "</pre>";
    */
} catch (Exception $e) {
    echo $e->getMessage();
}