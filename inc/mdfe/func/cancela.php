<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

include_once("../../php/functions.php");
require_once('../vendor/nfephp-org/sped-mdfe/bootstrap.php');

use NFePHP\Common\Certificate;
use NFePHP\MDFe\Common\Standardize;
use NFePHP\MDFe\Tools;

$idmdfe= $_GET["idmdfe"]; //Id da nota fiscal que vem da acao de enviar


// $sql="select * from "._DBAPP.".nf where envionfe='ENVIADO' and recibo is not null and idnf=".$idnotafiscal;
$sql="select SUBSTRING(chave,5) as nchave, protocolo as nprotocolo,recibo,justificativa,idempresa from "._DBAPP.".mdfe where protocolo is not null and idmdfe=".$idmdfe;
$res=d::b()->query($sql) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
$row=mysqli_fetch_assoc($res);

if(empty($row['recibo'])){
	die("Não foi encontrado o recibo do MDFe para consulta");
}

if($row['justificativa']){
    $justificativa=$row['justificativa'];
}else{
    $justificativa="Cancelado por motivos internos";
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

//$certificadoDigital = file_get_contents('../../nfe/sefaz4/certs/1001845428certificadolaudolaboratorio10fb4b9_56044.08fb4b9_56044.21fb4b9_56044.pfx');

$arquivo=str_replace("../","../../nfe/sefaz4/",$_row["certificado"]);

$certificadoDigital = file_get_contents($arquivo);


try {
    $certificate = Certificate::readPfx($certificadoDigital, $_row["senha"]);

    $tools = new Tools(json_encode($config), $certificate);

    $chave = $row['nchave'];
    $xJust = $justificativa;
    $nProt = $row['nprotocolo'];
 //   echo($chave);
  //  echo('\n'.$xJust);
   // echo('\n'.$nProt);
    $resp = $tools->sefazCancela($chave, $xJust, $nProt);

    $st = new Standardize();
    $std = $st->toStd($resp);
/*
    echo '<pre>';
    print_r($std);
    echo "</pre>";
*/
    $cStat=$std->infEvento->cStat;
    $xMotivo=$std->infEvento->xMotivo;
    $nProt=$std->infEvento->nProt;

    if ($cStat == '135') {
	    $statusmdfe='CANCELADO';
	}else{
        $statusmdfe='CONCLUIDO';
    }

    echo($xMotivo." ".$nProt);
	
    $sql="update "._DBAPP.".mdfe 
    set statusmdfe='".$statusmdfe."',mensagem='".$xMotivo." ".$nProt."'				
    where  idmdfe = ".$idmdfe;
    $retx = d::b()->query($sql) or die("Erro ao atualizar mdfe sql:".$sql);


    /*
Object
(
    [attributes] => stdClass Object
        (
            [versao] => 3.00
        )

    [infEvento] => stdClass Object
        (
            [attributes] => stdClass Object
                (
                    [Id] => ID931210000004952
                )

            [tpAmb] => 2
            [verAplic] => RS20191021102629
            [cOrgao] => 31
            [cStat] => 135
            [xMotivo] => Evento registrado e vinculado ao MDF-e
            [chMDFe] => 31210323259427000104580260000000061503431826
            [tpEvento] => 110111
            [xEvento] => Cancelamento
            [nSeqEvento] => 1
            [dhRegEvento] => 2021-03-10T17:29:10-03:00
            [nProt] => 931210000004952
        )

)
    */
} catch (Exception $e) {
    echo $e->getMessage();
}