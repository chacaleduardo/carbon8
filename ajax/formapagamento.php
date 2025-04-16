<?
require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}

// CONTROLLERS
require_once(__DIR__ . "/../form/controllers/formapagamento_controller.php");
require_once(__DIR__ . "/../form/controllers/contapagar_controller.php");

$action = $_GET['action'] ?? $_POST['action'];

if($action)
{
    $params = $_GET['params'] ?? $_POST['params'];

    if(!isset($params['typeParam']))
    {
        $params['typeParam'] = false;
    }

    if(is_array($params) && ($params['typeParam'] != 'array'))
    {
        return $action(implode(',', $params));
    }

    return $action($params);
}

function buscarFormaPagamentoAtivaPorIdEmpresaEFormaPagamento($dados) {
    list($idEmpresa, $formaPagamento) = $dados['param'];
    $formasPagamento = [];

    if($idEmpresa && $formaPagamento)
        $formasPagamento = FormaPagamentoController::buscarFormaPagamentoAtivaPorIdEmpresaEFormaPagamento($idEmpresa, $formaPagamento);

    echo json_encode($formasPagamento);
}

function buscarExtratoAppPorEmpresaFormaPagamentoEPeriodo($dados) {
    list($idEmpresa, $idFormaPagamento, $dataInicio, $dataFinal) = $dados['param'];

    $extrato = [];

    if($idEmpresa && $idFormaPagamento && $dataInicio && $dataFinal) {
        $extrato = ContaPagarController::buscarExtratoAppPorEmpresaFormaPagamentoEPeriodo($idFormaPagamento, $idEmpresa, $dataInicio, $dataFinal);
    }

    echo json_encode($extrato);
}