<?
require_once(__DIR__ . "/../../form/controllers/conciliacaofinanceira_controller.php");
require_once(__DIR__ . "/../../form/controllers/nf_controller.php");

require('saveposchange__nfentrada.php');

$idNf = $_GET['idnf'];

if ($idNf && $rowFluxo['statustipo'] == 'CONCLUIDO') {
    $lancamentoConciliacaoFinanceira = ConciliacaoFinanceiraController::buscarLancamentoPorIdNf($idNf);

    if ($lancamentoConciliacaoFinanceira) {
        if($lancamentoConciliacaoFinanceira['status'] != 'CONCILIADO')
            ConciliacaoFinanceiraController::limparMensagemLancamento($lancamentoConciliacaoFinanceira['idconciliacaofinanceiraitem']);
    } else {
        $contaPagarItem = NfController::buscarContaPagarItemPorIdNf($idNf);

        if ($contaPagarItem && $contaPagarItem['idconciliacaofinanceira'] && $contaPagarItem['statusconciliacao'] != 'CONCILIADO') {
            ConciliacaoFinanceiraController::adicionarLancamentoPeloComprasApp(
                $contaPagarItem['idconciliacaofinanceira'],
                $contaPagarItem['idcontapagar'] ?? 'null',
                $contaPagarItem['idcontapagaritem'],
                $contaPagarItem['dtemissao'],
                $contaPagarItem['nome'],
                $contaPagarItem['valor'],
                $contaPagarItem['status'],
                $contaPagarItem['idempresa']
            );
        }
    }
}
