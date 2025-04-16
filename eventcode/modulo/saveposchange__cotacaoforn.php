<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

if($_SESSION['arrpostbuffer']['1']['u']['nf']['status'] == 'RESPONDIDO'){
    $idnf = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'];
    $idfluxostatus = $_SESSION['arrpostbuffer']['1']['u']['nf']['idfluxostatus'];
    FluxoController::inserirFluxoStatusHist('nfentrada', $idnf, $idfluxostatus, 'RESPONDIDO');

    //LTM (28-07-2021) - Congela as Informações da Nota Fiscal
    congelaNfCotacao($idnf, 'cotacaoforn'); 
}
?>