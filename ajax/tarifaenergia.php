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
require_once("../form/controllers/tarifaenergia_controller.php");

$action = $_GET['action'] ?? $_POST['action'];

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


function VerificaStatusTipoCobranca($idtarifapadrao)
{
    if(!$idtarifapadrao){

        echo json_encode(['error' => 'Idtarifa não está sendo passado']);
        return false;
    }

    $tarifaativa= PrecoEnergiaController::BuscarTarifaAtivo($idtarifapadrao);
    
    if($tarifaativa['qtdLinhas'] > 0){

        echo json_encode(['error' => 'Já existe uma cobrança ativa. É necessário inativar a cobrança ativa para prosseguir.']);
        return true;
    }

    echo true;
}

