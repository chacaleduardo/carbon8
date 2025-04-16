<?php

require_once(__DIR__."/../form/controllers/inclusaoresultado_controller.php");
require_once(__DIR__."/../inc/php/validaacesso.php");

if ($_GET['_buscaragentes'] == 'Y') {
    $_1_u_resultado_idresultado = $_GET['_idresultado'];
    $dadosAgentesGerados = InclusaoResultadoController::buscarSementesGeradasResultado($_1_u_resultado_idresultado);
    $arrSementesGeradas = $dadosAgentesGerados->data;
    $arrAgentes = array();

    foreach ($arrSementesGeradas as $key => $rl) {
        $arrAgentes[$k]["agente"] = $rl['partida'];
        $arrAgentes[$k]["idlote"] = $rl['idlote'];
        $arrAgentes[$k]["tipificacao"] = $rl['tipificacao'];
        $arrAgentes[$k]["exercicio"] = $rl['exercicio'];
        $k++;
    }

    $arrAgentes = (count($arrAgentes) == 0) ? json_encode([]) : json_encode($arrAgentes);

    return $arrAgentes;
}


if($_GET['status_resultado']) {
    $cancelandoResultados = '';
}