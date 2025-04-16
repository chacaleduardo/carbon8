<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once('../../../bootstrap.php');
include_once("../../../../../php/functions.php");

$idnf = $_GET["idnf"];
$idnfentradaxml=$_GET["idnfentradaxml"];

if(empty($idnf) and empty($idnfentradaxml)) die("Parâmetro Vazio ou Inválido");

if(!empty($idnfentradaxml)){
	$sql="select idempresa,xml as xmlret,'T' as tiponf from nfentradaxml x where   x.idnfentradaxml=".$idnfentradaxml;
}else{
    $sql="select idempresa,xmlret,tiponf from nf where idnf=".$idnf;
}
$res=d::b()->query($sql) or die("erro ao buscar xml. Sql: ".$sql);

if(mysql_num_rows($res) < 1) die("NF não encontrada");

$row=mysqli_fetch_assoc($res);

if($row["tiponf"] != 'T') die("Essa nota não é um CTe");
if(empty($row["xmlret"])) die("XML do CTe está vazio");

use NFePHP\DA\CTe\Dacte;

$logo = "";

$sqlimagemdanfe="select caminho from empresaimagem where idempresa = ".$row["idempresa"]." and tipoimagem = 'IMAGEMEMPRESADANFE'";
$resimagemdanfe=d::b()->query($sqlimagemdanfe) or die("Erro ao buscar figura da danfe da empresa sql=".$sqlimagemdanfe);
$rowimagemdanfe= mysqli_fetch_assoc($resimagemdanfe);
if(!empty($rowimagemdanfe["caminho"])){
    $rowimagemdanfe["caminho"] = str_replace("../", "", $rowimagemdanfe["caminho"]);
    $logo = _CARBON_ROOT.$rowimagemdanfe["caminho"];
}


try {
    //instanciação da classe (OBRIGATÓRIO)
    $da = new Dacte($row["xmlret"]);
    
    //Métodos públicos (TODOS OPCIONAIS)
    $da->debugMode(true);
    //$da->printParameters('P', 'A4', 2, 2);
    $da->setDefaultFont('times');
    $da->logoParameters($logo, 'C', false);
    $da->setDefaultDecimalPlaces(2);
    //$da->depecNumber('12345678');
    
    
    //Renderização do PDF  (OBRIGATÓRIO)
    $pdf = $da->render();
    header('Content-Type: application/pdf');
    echo $pdf;
} catch (InvalidArgumentException $e) {
    echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
}  