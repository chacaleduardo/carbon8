<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

$status = FluxoController::buscarStatusPorModulo('funcionario');

foreach($status as $row)
{
    $json.=$virg.'{"'.$row['statustipo'].'":"'.$row['rotulo'].'"}';
    $virg=",";
}
echo("[".$json."]");

?>