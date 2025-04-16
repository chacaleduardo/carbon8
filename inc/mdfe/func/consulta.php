<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

include_once("../../php/functions.php");
require_once('../vendor/nfephp-org/sped-mdfe/bootstrap.php');

use NFePHP\Common\Certificate;
use NFePHP\MDFe\Common\Standardize;
use NFePHP\MDFe\Tools;

//conectabanco();
$idmdfe= $_GET["idmdfe"]; //Id da nota fiscal que vem da acao de enviar


// $sql="select * from "._DBAPP.".nf where envionfe='ENVIADO' and recibo is not null and idnf=".$idnotafiscal;
$sql="select * from "._DBAPP.".mdfe where recibo is not null and idmdfe=".$idmdfe;
$res=d::b()->query($sql) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
$row=mysqli_fetch_assoc($res);

if(empty($row['recibo'])){
	die("Não foi encontrado o recibo do MDFe para consulta");
}

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

    $arquivo=str_replace("../","../../nfe/sefaz4/",$_row["certificado"]);
    
    $certificadoDigital = file_get_contents($arquivo);

    //$certificadoDigital = file_get_contents('../../nfe/sefaz4/certs/1001845428certificadolaudolaboratorio10fb4b9_56044.08fb4b9_56044.21fb4b9_56044.pfx');
   //$certificadoDigital = file_get_contents("../".$_row["certificado"]);
try {
    $certificate = Certificate::readPfx($certificadoDigital, $_row["senha"]);

    $tools = new Tools(json_encode($config), $certificate);

    $recibo = $row['recibo'];
    $resp = $tools->sefazConsultaRecibo($recibo);

    $st = new Standardize();
    $std = $st->toStd($resp);

    echo($resp );
/*
    echo '<pre>';
    print_r($std);
    echo "</pre>";
*/
    $versaoaplic=$std->verAplic;
    $tpAmb=$std->tpAmb;
    $recibo=$std->nRec;
    $chMDFe=$std->protMDFe->infProt->chMDFe;
    $digVal=$std->protMDFe->infProt->digVal;
    $cStat=$std->protMDFe->infProt->cStat;
    $xMotivo=$std->protMDFe->infProt->xMotivo;
    $idProt=$std->protMDFe->infProt->attributes->Id;
    $nProt=$std->protMDFe->infProt->nProt;
    $dhRecbto=$std->protMDFe->infProt->dhRecbto;

    if ($cStat != '100') {
	    $statusmdfe='ERRO';
	}else{
        $statusmdfe='AUTORIZADO';
    }

    $_SESSION["xmlprot"] ="<protMDFe xmlns=\"http://www.portalfiscal.inf.br/mdfe\" versao=\"3.00\"><infProt Id=\"".$idProt."\"><tpAmb>".$tpAmb."</tpAmb><verAplic>".$versaoaplic."</verAplic><chMDFe>".$chMDFe."</chMDFe><dhRecbto>".$dhRecbto."</dhRecbto><nProt>".$nProt."</nProt><digVal>".$digVal."</digVal><cStat>".$cStat."</cStat><xMotivo>".$xMotivo."</xMotivo></infProt></protMDFe>";

	
    $sql="update "._DBAPP.".mdfe 
    set xmlret= concat('<?xml version=\"1.0\" encoding=\"UTF-8\"?><mdfeProc xmlns=\"http://www.portalfiscal.inf.br/mdfe\" versao=\"3.00\">',xml,'".$_SESSION["xmlprot"]."','</mdfeProc>'),
    statusmdfe='".$statusmdfe."',mensagem='".$xMotivo."',protocolo='".$nProt."'				
    where  idmdfe = ".$idmdfe;
    $retx = d::b()->query($sql) or die("Erro ao atualizar mdfe sql:".$sql);

    
    echo($xMotivo);
  
} catch (Exception $e) {
    echo $e->getMessage();
}