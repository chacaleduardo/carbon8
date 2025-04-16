<?
require_once(__DIR__ . "/../../form/controllers/formapagamento_controller.php");
require_once(__DIR__ . "/../../form/controllers/sgarea_controller.php");
$permissaoVisualizar = ($_SESSION['SESSAO']['MODULOS']['conciliacaofinanceira']['permissao'] == 'r') 
                        || ($_SESSION['SESSAO']['MODULOS']['conciliacaofinanceiracartoes']['permissao'] == 'w' && 
                            $_SESSION['SESSAO']['MODULOS']['conciliacaofinanceira']['permissao'] != 'w');

if ($permissaoVisualizar) {
    $idPessoaEquipeArr = array_map(function ($item) {
        return $item['idpessoa'];
    }, SgareaController::buscarPessoasPorIdResponsavel($_SESSION['SESSAO']['IDPESSOA']));

    array_push($idPessoaEquipeArr, $_SESSION['SESSAO']['IDPESSOA']);
    $idPessoaEquipe = implode(',', $idPessoaEquipeArr);

    $formaPagamentoArr = array_map(function ($item) {
        return $item['idformapagamento'];
    }, FormaPagamentoController::buscarFormasPagamentoPorIdPessoa(($idPessoaEquipe ? $idPessoaEquipe : $_SESSION['SESSAO']['IDPESSOA']), cb::idempresa()));

    $formaPagamento = implode(',', $formaPagamentoArr);

    if (!$formaPagamento) {
        header('X-CB-RESPOSTA: 0');
        header('X-CB-FORMATO: alert');

        die('Nenhuma forma de pagamento configurada!');
    };

    $_SESSION["SEARCH"]["WHERE"][] = " idformapagamento in({$formaPagamento})";
}
