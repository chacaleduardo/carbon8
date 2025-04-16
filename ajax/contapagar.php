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
require_once(__DIR__ . "/../form/controllers/formapagamento_controller.php");
require_once(__DIR__ . "/../form/controllers/contapagar_controller.php");

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

function buscarExtratoAppPorContapagarIdFormaPagamentoEEmpresa($param)
{
    list($idContapagar, $idFormaPagamento, $idEmpresa) = $param['param'];

    if(!$idContapagar) {
        echo 'NFe não informada';
        return false;
    }

    if(!$idFormaPagamento) {
        echo 'Forma de pagamento não informada';
        return false;
    }

    if(!$idEmpresa) {
        echo 'Empresa não informada';
        return false;
    };

    $extrato = ContaPagarController::buscarExtratoAppPorContapagarIdFormaPagamentoEEmpresa($idContapagar, $idFormaPagamento, $idEmpresa);

    echo json_encode($extrato);
}

function buscarFaturasPorIdFormapagamento($dados) {
    list($idFormaPagamento, $idEmpresa, $idContapagar) = $dados['param'];

    if(!$idFormaPagamento) {
        echo json_encode([
            'error' => 'Forma de pagamento não informada'
        ]);

        return;
    }

    if(!$idEmpresa) {
        echo json_encode([
            'error' => 'Empresa não informada'
        ]);

        return;
    }

    $faturas = ContaPagarController::buscarFaturasPorIdFormapagamento($idFormaPagamento, $idEmpresa, $idContapagar);

    echo json_encode($faturas);
}
