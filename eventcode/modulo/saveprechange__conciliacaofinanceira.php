<?
// Controllers
require_once(__DIR__ . "/../../form/controllers/conciliacaofinanceira_controller.php");

$dadosLancamentoJSON = $_POST['dados_lancamento'];
$idConciliacaoFinanceira = $_GET['idconciliacaofinanceira'];
$idLancamentosRemovidos = $_POST['dados_lancamento_remover'];
$idEmpresa = $_POST['_1_u_conciliacaofinanceira_idempresa'];
$idContapagar = $_POST['_1_i_conciliacaofinanceira_idcontapagar'] ?? $_POST['_1_u_conciliacaofinanceira_idcontapagar'];

$envioDeMensagem = $_POST['_1_u_conciliacaofinanceiraitem_mensagem'];
$idRemoverLancamentoItem = $_POST['_1_d_conciliacaofinanceiraitem_idconciliacaofinanceiraitem'];

if (!$envioDeMensagem && !$idRemoverLancamentoItem) {
    if (!$idContapagar) die('NFe não informada!');
    if (ConciliacaoFinanceiraController::verificarSeExisteConciliacaoPorIdContaItem($idContapagar, $idConciliacaoFinanceira)) die('Conciliação já registrada nesta NFe!');

    if ($idConciliacaoFinanceira && $dadosLancamentoJSON) {
        $dadosLancamento = json_decode($dadosLancamentoJSON);

        if ($idLancamentosRemovidos) {
            $idLancamentosRemovidosStr = implode(',', $idLancamentosRemovidos);

            if ($idLancamentosRemovidosStr)
                ConciliacaoFinanceiraController::removerLancamentos($idLancamentosRemovidosStr);
        }

        if (count(get_object_vars($dadosLancamento)))
            ConciliacaoFinanceiraController::inserirLancamentos($idConciliacaoFinanceira, $dadosLancamento, $idEmpresa);
    }
}
