<?

require_once("saveprechange__pedido.php");
if($_SESSION['arrpostbuffer']['1']['u']['nf']['status'] == 'CONCLUIDO'){
    $idnf = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'];
    $idfluxostatus = FluxoController::getIdFluxoStatus('pedido', 'CONCLUIDO');
    FluxoController::inserirFluxoStatusHist('pedido', $idnf, $idfluxostatus, 'PENDENTE');
    $_SESSION['arrpostbuffer']['1']['u']['nf']['idfluxostatus'] = $idfluxostatus;
    $_SESSION['arrpostbuffer']['1']['u']['nf']['status'] = 'CONCLUIDO';
    $_SESSION['arrpostbuffer']['1']['u']['nf']['emailrepresentante'] = "Y";
}

?>