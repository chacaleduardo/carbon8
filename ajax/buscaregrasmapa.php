<?
require_once("../inc/php/functions.php");

if($_GET["tipo"] == 'regrasmapa' && $_GET["idespeciefinalidade"]){

$sqlfinalidade = "SELECT idespeciefinalidade, mortalidademapa,idademapa FROM especiefinalidade WHERE idespeciefinalidade = ".$_GET["idespeciefinalidade"];
$resfinalidade = d::b()->query($sqlfinalidade) or die("Erro ao recuperar Regras do Mapa: ".mysqli_error(d::b()));

$arrTmp = array();

while($r = mysqli_fetch_assoc($resfinalidade)) {
    $arrTmp[$r["idespeciefinalidade"]]["mortalidademapa"] = $r["mortalidademapa"];
    $arrTmp[$r["idespeciefinalidade"]]["idademapa"] = $r["idademapa"];
}

echo json_encode($arrTmp);


}?>