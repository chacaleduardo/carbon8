<?
include_once("../../form/controllers/solmat_controller.php");

if ($_GET['_modulo'] == 'solmat') {
    $tipo = SolmatController::$tipoSolmat;
} elseif($_GET['_modulo'] == 'soltag') {
    $tipo = SolmatController::$tipoSoltag;
} elseif($_GET['_modulo'] == 'solmatmeios') {
    $tipo = SolmatController::$tipoSolmatMeios;
} else{
    $tipo = SolmatController::$tipoSolmat;
}

echo "[".json_encode($tipo)."]";
?>