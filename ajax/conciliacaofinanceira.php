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
require_once(__DIR__ . "/../form/controllers/conciliacaofinanceira_controller.php");

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

function removerLancamentosPorIdConciliacaoFinanceira($idConciliacaoFinanceira)
{
    if (!$idConciliacaoFinanceira) {
        echo json_encode(['error' => true, 'mensagem' => 'ID da conciliação financeira não informado']);
        return;
    }

    $removendoConciliacoes = ConciliacaoFinanceiraController::removerLancamentosPorIdConciliacaoFinanceira($idConciliacaoFinanceira);

    if (!$removendoConciliacoes)
        echo json_encode(['error' => true, 'mensagem' => 'Erro ao remover lançamentos']);

    echo json_encode(['error' => false, 'mensage' => 'Removido']);
}

function adicionarLancamentoPorIdContaPagarItem($params)
{
    list($idContaPagarItem, $idContapagar) = explode(',', $params);

    if (!$idContaPagarItem) {
        echo json_encode(['error' => 'Id contapagaritem não informado!']);
        return;
    }

    if (!$idContapagar) {
        echo json_encode(['error' => 'Id contapagar não informado!']);
        return;
    }

    $conciliacaoFinanceira = ConciliacaoFinanceiraController::buscarConciliacaoFinananceiraPorIdContaPagar($idContapagar);

    if (!$conciliacaoFinanceira) {
        echo json_encode(['error' => 'Está fatura não possui Conciliação financeira!']);
        return;
    }

    if($conciliacaoFinanceira['status'] == 'CONCILIADO') {
        echo json_encode(['error' => 'Status da conciliação já fechado!']);
        return;
    }

    $contaPagarItem = ConciliacaoFinanceiraController::buscarContapagarItemPorIdContaPagarItem($idContaPagarItem);

    ConciliacaoFinanceiraController::adicionarLancamentoPeloComprasApp(
        $conciliacaoFinanceira['idconciliacaofinanceira'],
        $idContapagar,
        $contaPagarItem['idcontapagaritem'],
        $contaPagarItem['dtemissao'],
        $contaPagarItem['nome'],
        $contaPagarItem['valor'],
        $contaPagarItem['status'],
        $contaPagarItem['idempresa']
    );

    echo json_encode(['success' => 'Conciliacao criada']);
}
