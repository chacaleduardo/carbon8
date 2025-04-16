<?
require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();

if ($jwt["sucesso"] !== true) {
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}

// CONTROLLERS
require_once("../form/controllers/_rep_controller.php");

$action = $_GET['action'] ?? $_POST['action'];
$idEmpresa = $_GET['_idempresa'];

if ($action) {
    $params = $_GET['params'] ?? $_POST['params'];

    if (!isset($params['typeParam'])) {
        $params['typeParam'] = false;
    }

    if (is_array($params) && ($params['typeParam'] != 'array')) {
        return $action(implode(',', $params));
    }

    return $action($params);
}

function buscarInfoRelatorioPorIdRep($idrep) {
    if(!$idrep) {
        echo json_encode([
            'error' => 'idrep não informado'
        ]);

        return false;
    }
    
    $rep = _RepController::buscarInfoRelatorioPorIdRep($idrep, getModsUsr('MODULOS'));

    if(!$rep) {
        echo json_encode([
            'error' => "Relatório $idrep não encontrado."
        ]);

        return false;
    }

    echo json_encode($rep);
}