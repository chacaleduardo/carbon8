<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}

require_once(__DIR__ . "/controllers/pedidoemlote_controller.php");
require_once(__DIR__ . "/controllers/conciliacaofinanceira_controller.php");
require_once(__DIR__ . "/controllers/fluxo_controller.php");
require_once(__DIR__ . "/controllers/pessoa_controller.php");


/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetros chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "conciliacaofinanceira";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idconciliacaofinanceira" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from conciliacaofinanceira where idconciliacaofinanceira = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$permissaoEditar = strpos($_SESSION['SESSAO']['LPS'], '2696') !== false || PessoaController::verificarGestor($_SESSION['SESSAO']['IDPESSOA'], cb::idempresa());
$permissaoVisualizar = $_SESSION['SESSAO']['MODULOS'][$_GET['_modulo']]['permissao'] == 'r';
$visualizarMensagemMobile = $_SESSION['SESSAO']['MODULOS'][$_GET['_modulo']]['permissao'] == 'w' || $permissaoVisualizar;
$empresas = PedidoEmLoteController::buscarEmpresasDoFiltro($_SESSION['SESSAO']['IDPESSOA'], true);
$lancamentoArquivo = '';
$lancamentos = [];
$campoDesabilitado = '';
$financeiro = false;
$gestor = false;

if ($_1_u_conciliacaofinanceira_idconciliacaofinanceira) {
    $lancamentoArquivo = ConciliacaoFinanceiraController::buscarArquivoPorTipoObjetoEIdObjeto($_1_u_conciliacaofinanceira_idconciliacaofinanceira, 'conciliacaofinanceira');
    $lancamentos = ConciliacaoFinanceiraController::buscarLancamentosPorIdConciliacaoFinanceira($_1_u_conciliacaofinanceira_idconciliacaofinanceira, $_1_u_conciliacaofinanceira_idempresaconciliacaofinanceira);
    $idFluxoStatusAberto =  FluxoController::getIdFluxoStatus('nfentrada', 'INICIO');
    $rotuloStatus = FluxoController::buscarRotuloStatusFluxo('conciliacaofinanceira', 'idconciliacaofinanceira', $_1_u_conciliacaofinanceira_idconciliacaofinanceira);
    $financeiro = strpos($_SESSION['SESSAO']['LPS'], '2696') !== false;
    $gestor = PessoaController::verificarGestor($_SESSION['SESSAO']['IDPESSOA'], cb::idempresa());

    if ($rotuloStatus) $rotuloStatus = $rotuloStatus['rotulo'];

    if ($_1_u_conciliacaofinanceira_status == 'CONCILIADO') $campoDesabilitado = 'disabled';
}

?>
<link rel="stylesheet" href="/form/css/conciliacaofinanceira_css.css?version=1.5" />
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading row">
                <h2 class="col-xs-12 font-bold">Conciliação financeira</h2>
            </div>
            <div class="panel-body">
                <div class="row col-xs-12 d-flex flex-wrap align-items-center">
                    <div class="col-xs-12 col-md-8 col-lg-9 col-xl-10">
                        <div class="row d-flex flex-wrap">
                            <!-- ID empresa -->
                            <input class="hide" name="_1_<?= $_acao ?>_conciliacaofinanceira_idempresa" type="number" value="<?= cb::idempresa() ?>" hidden />
                            <!-- Status -->
                            <? if ($_1_u_conciliacaofinanceira_status) { ?>
                                <input id="input-status" class="hide" name="_1_<?= $_acao ?>_conciliacaofinanceira_status" type="text" value="<?= $_1_u_conciliacaofinanceira_status ?>" hidden />
                            <? } ?>
                            <? if (!$_1_u_conciliacaofinanceira_idconciliacaofinanceira && (cb::idempresa() == 2 && $_GET['_modulo'] == 'conciliacaofinanceiracartoes')) { ?>
                                <input id="input-status" class="hide" name="_1_<?= $_acao ?>_conciliacaofinanceira_status" type="text" value="INICIO" hidden />
                            <? } ?>
                            <!-- ID conciliacao -->
                            <? if ($_1_u_conciliacaofinanceira_idconciliacaofinanceira) { ?>
                                <input class="hide" name="_1_<?= $_acao ?>_conciliacaofinanceira_idconciliacaofinanceira" type="number" value="<?= $_1_u_conciliacaofinanceira_idconciliacaofinanceira ?>" hidden />
                            <? } ?>
                            <!-- Dados dos lancamentos em JSON -->
                            <input name="dados_lancamento" id="dados-lancamentos-json" class="hide" type="text" value="" hidden>
                            <!-- Lancamentos removidos -->
                            <input name="dados_lancamento_remover[]" id="dados-lancamentos-remover" class="hide" type="text" value="" hidden>
                            <!-- Empresa -->
                            <div class="form-group col-xs-12 col-lg-3">
                                <label for="">Empresa</label>
                                <select name="_1_<?= $_acao ?>_conciliacaofinanceira_idempresaconciliacaofinanceira" id="input-empresa" class="form-control" <?= $campoDesabilitado ?>>
                                    <option value="">Selecionar empresa</option>
                                    <?= fillselect($empresas, $_1_u_conciliacaofinanceira_idempresaconciliacaofinanceira); ?>
                                </select>
                            </div>
                            <!-- Cartão de crédito -->
                            <div class="form-group col-xs-12 col-lg-3">
                                <label for="">Cartão de crédito</label>
                                <select name="_1_<?= $_acao ?>_conciliacaofinanceira_idformapagamento" id="cartao-credito" class="form-control input" disabled data-live-search="true"></select>
                            </div>
                            <!-- NFe -->
                            <div class="form-group col-xs-12 col-lg-3">
                                <label for="" class="w-100">NFe</label>
                                <div class="w-100 d-flex flex-wrap align-items-center">
                                    <div class="col-xs-10 col-md-10 col-xl-11 pl-0 py-0">
                                        <select id="input-nfe" name="_1_<?= $_acao ?>_conciliacaofinanceira_idcontapagar" class="form-control" type="number" value="<?= $_1_u_conciliacaofinanceira_idcontapagar ?>" vnulo <?= $campoDesabilitado ?>></select>
                                    </div>
                                    <? if ($_1_u_conciliacaofinanceira_idcontapagar) { ?>
                                        <a href="?_modulo=contapagar&_acao=u&idcontapagar=<?= $_1_u_conciliacaofinanceira_idcontapagar ?>" target="_blank" rel="noopener noreferrer" title="Acessar Fatura">
                                            <i class="fa fa-navicon fa-2x"></i>
                                        </a>
                                    <? } ?>
                                </div>
                            </div>
                            <? if ($_1_u_conciliacaofinanceira_status) { ?>
                                <div class="form-group col-xs-12 col-lg-3">
                                    <label for="">Status</label>
                                    <label id="conciliacao-status" class="d-flex align-items-center form-control alert-warning">
                                        <?= $rotuloStatus ?? $_1_u_conciliacaofinanceira_status ?>
                                    </label>
                                </div>
                            <? } ?>
                        </div>
                    </div>
                    <? if ($financeiro) { ?>
                        <div class="col-xs-12 col-md-4 col-lg-3 col-xl-2">
                            <div class="row d-flex align-items-center justify-content-end" style="gap: 2rem;">
                                <? if (!$lancamentoArquivo['idarquivo']) { ?>
                                    <label for="" id="input-extrato" class="btn btn-primary rounded text-light" <?= !$_1_u_conciliacaofinanceira_idconciliacaofinanceira ? 'disabled' : '' ?>>
                                        Importar Extrato
                                    </label>
                                <? } else { ?>
                                    <a href="/<?= $lancamentoArquivo['caminho'] ?>" target="_blank" class="me-2">
                                        <i class="fa fa-file-text-o fa-2x" title="Extrato da fatura"></i>
                                    </a>
                                    <? if ($_1_u_conciliacaofinanceira_status == 'AGUARDANDO') { ?>
                                        <label for="" id="remover-arquivo-extrato" class="btn btn-danger rounded text-light" <?= $_1_u_conciliacaofinanceira_status == 'CONCILIADO' ? 'disabled' : '' ?>>
                                            Remover Extrato
                                        </label>
                                    <? } ?>
                                <? } ?>
                                <button id="<?= $_1_u_conciliacaofinanceira_idconciliacaofinanceira ? 'btn-pesquisar' : 'input-desabilitado-2' ?>" class="btn btn-primary rounded text-light" disabled>
                                    <i class="fa fa-search text-white m-0"></i>
                                </button>
                            </div>
                        </div>
                    <? } ?>
                </div>
            </div>
        </div>
    </div>
    <? if ($_1_u_conciliacaofinanceira_idconciliacaofinanceira) { ?>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading d-flex flex-between">
                    <h3 class="font-bold">Lançamento de Fatura</h3>
                    <h3 class="font-bold text-xs-end">Lançamento do Sistema</h3>
                </div>
                <div id="tabela-lancamentos" class="panel-body">
                    <!-- Header -->
                    <div id="header" class="d-flex flex-between font-bold mb-2">
                        <div class="border d-flex flex-between col-xs-12 col-lg-5 cabecalho-fatura">
                            <span class="col-xs-4">Data de emissão</span>
                            <span class="col-xs-3 col-lg-4">Nome</span>
                            <span class="col-xs-2">Valor</span>
                            <span class="col-xs-3 col-lg-2">Porcentagem</span>
                        </div>
                        <!-- Check -->
                        <span style="width: 13px;"></span>
                        <div class="col-xs-2 text-center acoes">
                            <span class="border px-3">Ações</span>
                        </div>
                        <!-- Check -->
                        <span style="width: 13px;"></span>
                        <div class="border d-flex flex-between col-xs-5 cabecalho-sistema">
                            <span class="col-xs-4">Data de emissão</span>
                            <span class="col-xs-4">Nome</span>
                            <span class="col-xs-2">Valor</span>
                            <span class="col-xs-2 text-center">Status</span>
                        </div>
                    </div>
                    <div id="itens-lancamento" 
                        class="w-100 <?= $_1_u_conciliacaofinanceira_status ?? '' ?> <?= ((!in_array($_1_u_conciliacaofinanceira_status, ['EMCONCILIACAO', 'AGUARDANDO', 'CORRIGIDO'])) || $_1_u_conciliacaofinanceira_status == 'CONCILIADO') ? 'desabilitado' : '' ?> <?= ($financeiro || ($gestor && in_array($_1_u_conciliacaofinanceira_status, ['APROVACAOSOLICITADA','CORRIGIDO'])))  ? '' : 'leitura' ?>"></div>
                    <div id="total-lancamentos" class="w-100 d-flex flex-between align-items-center py-3"> 
                        <div class="col-xs-5 text-center px-0">
                            <h2 id="total-lancamentos-fatura" class="font-bold m-0" data-total="0">R$ 0,00</h2>
                        </div>
                        <div class="col-xs-1 text-center">
                            <h2 class="font-bold m-0">Total</h2>
                        </div>
                        <div class="col-xs-5 text-center px-0">
                            <h2 id="total-lancamentos-sistema" class="font-bold m-0" data-total="0">R$ 0,00</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <? if (
            (
                $financeiro &&
                in_array($_1_u_conciliacaofinanceira_status, ['EMCONCILIACAO', 'APROVACAOSOLICITADA','CORRIGIDO', 'AGUARDANDO'])
            ) || (
                $gestor && 
                in_array($_1_u_conciliacaofinanceira_status, ['APROVACAOSOLICITADA','CORRIGIDO'])
            )
        ) { ?>
            <div id="painel-conciliacao" class="py-1">
                <div class="row d-flex flex-between align-items-center  <?= (in_array($_1_u_conciliacaofinanceira_status, ['EMCONCILIACAO', 'AGUARDANDO'])) ? '' : 'py-2' ?>">
                    <!-- Legenda -->
                    <div id="legenda" class="d-flex align-items-center pointer">
                        <i class="fa fa-question-circle fa-2x text-black mr-2"></i>
                        <h1 class="m-0">Ajuda</h1>
                    </div>
                    <!-- Info Fatura -->
                    <div class="col-4 d-flex flex-col align-items-center">
                        <h3 class="m-0 <?= (in_array($_1_u_conciliacaofinanceira_status, ['EMCONCILIACAO', 'AGUARDANDO'])) ? '' : 'hidden' ?>">Itens Selecionados da fatura <strong>(<strong id="itens-selecionados-fatura" data-itensselecionadosfatura="0">0</strong>)</strong></h3>
                        <h3 class="m-0 <?= (in_array($_1_u_conciliacaofinanceira_status, ['EMCONCILIACAO', 'AGUARDANDO'])) ? '' : 'hidden' ?>">Total <strong id="valor-total-fatura" data-valortotalfatura="0">R$ 0,0</strong></h3>
                    </div>
                    <!-- Info Sistema -->
                    <div class="col-4 d-flex flex-col align-items-center">
                        <h3 class="m-0 <?= (in_array($_1_u_conciliacaofinanceira_status, ['EMCONCILIACAO', 'AGUARDANDO'])) ? '' : 'hidden' ?>">Itens Selecionados do sistema <strong>(<strong id="itens-selecionados-sistema" data-itensselecionadossistema="0">0</strong>)</strong></h3>
                        <h3 class="m-0 <?= (in_array($_1_u_conciliacaofinanceira_status, ['EMCONCILIACAO', 'AGUARDANDO'])) ? '' : 'hidden' ?>">Total <strong id="valor-total-sistema" data-valortotalsistema="0">R$ 0,0</strong></h3>
                    </div>
                    <div class="flex">
                        <button id="btn-enviar-mensagem-em-massa" class="btn btn-orange text-white d-flex align-items-center relative" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 13 13" fill="#FFFFFFF" width="12px" height="12px" class="pointer mr-2">
                                <g clip-path="url(#clip0_801_5591)">
                                    <path d="M12.1067 0.914745C12.134 0.846597 12.1406 0.771942 12.1259 0.700035C12.1111 0.628128 12.0756 0.562131 12.0237 0.510226C11.9718 0.458321 11.9058 0.422791 11.8339 0.40804C11.762 0.39329 11.6873 0.399967 11.6192 0.427245L0.708934 4.7915C0.612852 4.82996 0.529242 4.89418 0.467308 4.9771C0.405374 5.06001 0.367516 5.15841 0.357902 5.26146C0.348287 5.3645 0.367288 5.4682 0.412814 5.56115C0.45834 5.65409 0.528625 5.73267 0.615934 5.78825L4.36218 8.17175L5.51043 9.97624C5.56467 10.0581 5.64885 10.1155 5.74491 10.1359C5.84096 10.1564 5.94121 10.1384 6.02411 10.0857C6.10702 10.0331 6.16596 9.95001 6.18827 9.85437C6.21058 9.75872 6.19448 9.65814 6.14343 9.57425L5.11143 7.95275L10.7319 2.33225L9.31068 5.88575C9.29116 5.93167 9.28099 5.98103 9.28079 6.03093C9.28058 6.08083 9.29034 6.13027 9.30948 6.17635C9.32863 6.22243 9.35678 6.26423 9.39228 6.2993C9.42779 6.33436 9.46994 6.36199 9.51625 6.38055C9.56257 6.39912 9.61213 6.40826 9.66203 6.40743C9.71192 6.4066 9.76115 6.39582 9.80682 6.37572C9.8525 6.35562 9.8937 6.32661 9.92802 6.29038C9.96234 6.25415 9.98908 6.21144 10.0067 6.16474L12.1067 0.914745ZM10.2017 1.802L4.58118 7.4225L1.32693 5.35175L10.2017 1.802Z" fill="#FFFFFF" />
                                    <path d="M9.50879 12.4001C10.205 12.4001 10.8727 12.1236 11.3649 11.6313C11.8572 11.139 12.1338 10.4713 12.1338 9.77515C12.1338 9.07895 11.8572 8.41127 11.3649 7.91899C10.8727 7.42671 10.205 7.15015 9.50879 7.15015C8.8126 7.15015 8.14492 7.42671 7.65263 7.91899C7.16035 8.41127 6.88379 9.07895 6.88379 9.77515C6.88379 10.4713 7.16035 11.139 7.65263 11.6313C8.14492 12.1236 8.8126 12.4001 9.50879 12.4001ZM9.88379 8.65015V9.77515C9.88379 9.8746 9.84428 9.96999 9.77395 10.0403C9.70363 10.1106 9.60825 10.1501 9.50879 10.1501C9.40933 10.1501 9.31395 10.1106 9.24362 10.0403C9.1733 9.96999 9.13379 9.8746 9.13379 9.77515V8.65015C9.13379 8.55069 9.1733 8.45531 9.24362 8.38498C9.31395 8.31466 9.40933 8.27515 9.50879 8.27515C9.60825 8.27515 9.70363 8.31466 9.77395 8.38498C9.84428 8.45531 9.88379 8.55069 9.88379 8.65015ZM9.88379 10.9001C9.88379 10.9996 9.84428 11.095 9.77395 11.1653C9.70363 11.2356 9.60825 11.2751 9.50879 11.2751C9.40933 11.2751 9.31395 11.2356 9.24362 11.1653C9.1733 11.095 9.13379 10.9996 9.13379 10.9001C9.13379 10.8007 9.1733 10.7053 9.24362 10.635C9.31395 10.5647 9.40933 10.5251 9.50879 10.5251C9.60825 10.5251 9.70363 10.5647 9.77395 10.635C9.84428 10.7053 9.88379 10.8007 9.88379 10.9001Z" fill="#FFFFFF" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_801_5591">
                                        <rect width="12" height="12" fill="white" transform="translate(0.133789 0.400146)" />
                                    </clipPath>
                                </defs>
                            </svg>
                            <span class="badge"></span>
                            Enviar mensagem
                        </button>
                        <? if($financeiro) { ?>
                            <? if (in_array($_1_u_conciliacaofinanceira_status, ['EMCONCILIACAO', 'AGUARDANDO'])) { ?>
                                <button id="btn-conciliar" class="btn btn-success text-white d-flex align-items-center" disabled>
                                    <i class="fa fa-circle mr-2"></i>
                                    Conciliar (SHIFT + C)
                                </button>
                            <? } ?>
                            <button id="btn-conciliar-todos" class="btn text-white d-flex align-items-center text-white me-2" style="background-color: #5BC0DE;">
                                <i class="fa fa-circle mr-2"></i>
                                Aprovar Todos
                            </button>
                        <?}?>
                    </div>
                </div>
            </div>
        <? } ?>
    <? } ?>
</div>
<?
if (!empty($_1_u_comunicacaoext_idcomunicacaoext)) { // trocar p/ cada tela a tabela e o id da tabela
    $_idModuloParaAssinatura = $_1_u_comunicacaoext_idcomunicacaoext; // trocar p/ cada tela o id da tabela
    require 'viewAssinaturas.php';
}
$tabaud = "conciliacaofinanceira"; //pegar a tabela do criado/alterado em antigo
$_disableDefaultDropzone = true;
require 'viewCriadoAlterado.php';
?>
<?
require_once __DIR__ . '/js/conciliacaofinanceiracartoes_js.php';
?>