<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/cotacao_controller.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 * pk: indica parâmetro chave para o select inicial
 * vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "cotacao";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idcotacao"  =>  "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT * FROM cotacao WHERE idcotacao = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$_modulo = $_GET["_modulo"];

$titulo = "Novo Orçamento de Compra";
$titulomodulo = "Orçamento de Compra";

//Retorna Formas de Pagamento
$jPag = CotacaoController::listarFormaPagamentoAtivo();

/**
 * Retorna as pessoas:
 * Tipo 1: Funcionários - NomeCurto
 * Tipo 5: Fornecedor - Nome
 */
$jFunc = CotacaoController::listarPessoaPorIdTipoPessoa(1);
$jForn = CotacaoController::listarPessoaPorIdTipoPessoa(5);

//sql que mostra o valor total do orçamento  
if (!empty($_1_u_cotacao_idcotacao)) {
    $valorTotalCotacao = CotacaoController::buscarValorTotalCotacao($_1_u_cotacao_idcotacao);
    $qtdempresaemail = CotacaoController::buscarQuantidadeTipoEnvioEmpesaEmails($_1_u_cotacao_idempresa);
}
$vreadonly = $_1_u_cotacao_status == 'CONCLUIDO' ? "readonly='readonly'" : '';
?>
<link href="../form/css/cotacao_css.css?_<?= date("dmYhms") ?>" rel="stylesheet" />
<link href="../form/css/skeleton.css" rel="stylesheet" />
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-12 d-flex" style="gap: 20px;">
                        <style>
                            .form-groupt-orcamento {
                                flex: 1;
                                max-width: 3%;
                            }

                            .form-groupt-data {
                                flex: 1;
                                max-width: 6%;
                            }

                            .form-groupt-titulo {
                                flex: 1;
                                max-width: 40%;
                            }

                            .form-groupt {
                                flex: 1;
                                min-width: 150px;
                                max-width: 200px;
                            }

                            .form-groupt-total {
                                flex: 1;
                                min-width: 100px;
                                max-width: 150px;
                            }

                            .form-controlt {
                                width: 100% !important;
                                padding: 5px;
                            }

                            .readonly-fieldt {
                                background-color: #f2f2f2;
                            }

                            .field-icont {
                                cursor: pointer;
                                margin-left: 5px;
                            }

                            .d-flext {
                                flex-wrap: nowrap !important;
                                overflow-x: auto;
                            }

                            .titulo-body {
                                padding-left: 13px;
                            }
                        </style>

                        <!-- Orçamento -->
                        <div class="form-groupt-orcamento">
                            <label class="text-white mb-2">Orçamento</label>
                            <?php if (!empty($_1_u_cotacao_idcotacao)) : ?>
                                <label class="alert-warning d-flex align-items-center form-control">
                                    <?= $_1_u_cotacao_idcotacao ?>
                                    <input id="idcotacao" name="_1_<?= $_acao ?>_cotacao_idcotacao" type="hidden" value="<?= $_1_u_cotacao_idcotacao ?>">
                                </label>
                            <?php endif; ?>
                        </div>

                        <!-- Título -->
                        <div class="form-groupt-titulo">
                            <label class="text-white mb-2">Título</label>
                            <input name="_1_<?= $_acao ?>_cotacao_titulo"
                                class="form-control"
                                type="text"
                                value="<?= strtoupper($_1_u_cotacao_titulo) ?>"
                                <?= $vreadonly ?>
                                vnulo>
                        </div>

                        <!-- Prazo Fornecedor -->
                        <div class="form-groupt-data">
                            <?php if (empty($_1_u_cotacao_prazo)) : ?>
                                <label class="text-white mb-2">Prazo Fornecedor</label>
                                <input name="_1_<?= $_acao ?>_cotacao_prazo"
                                    id="_1_<?= $_acao ?>_cotacao_prazo"
                                    class="form-control calendario"
                                    type="text"
                                    value="<?= $_1_u_cotacao_prazo ?>"
                                    vnulo>
                            <?php else : ?>
                                <label class="text-white mb-2">Prazo Fornecedor <i class="fa fa-pencil field-icon"
                                        title="Editar Prazo"
                                        onclick="alterarValor('prazo','<?= dma($_1_u_cotacao_prazo) ?>', 'modulohistorico', <?= $_1_u_cotacao_idcotacao ?>, 'Prazo')"></i></label>
                                <div class="d-flext align-items-center">
                                    <input name="_1_<?= $_acao ?>_cotacao_prazo"
                                        id="_1_<?= $_acao ?>_cotacao_prazo"
                                        class="form-control readonly-field"
                                        readonly
                                        type="text"
                                        <?= $vnulo ?>
                                        value="<?= dma($_1_u_cotacao_prazo) ?>">
                                </div>
                            <?php endif; ?>

                            <input name="cotacao_prazo_old" type="hidden" value="<?= $_1_u_cotacao_prazo ?>">
                            <input name="_1_<?= $_acao ?>_cotacao_status"
                                type="hidden"
                                value="<?= $_acao == 'u' ? $_1_u_cotacao_status : 'INICIO' ?>">

                            <?php if (!empty($_1_u_cotacao_idcotacao)) :
                                $historicoPrazo = CotacaoController::buscarHistoricoAlteracaoPrazoCotacao($_1_u_cotacao_idcotacao, 'prazo');
                                if ($historicoPrazo) : ?>
                                    <i title="Histórico do Prazo"
                                        class="fa fa-info-circle field-icon hoverazul tip"
                                        onclick="historico('prazo', <?= $_1_u_cotacao_idcotacao ?>, 'de Prazo');"></i>
                                    <div id="prazo<?= $_1_u_cotacao_idcotacao ?>" style="display: none">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>De</th>
                                                    <th>Para</th>
                                                    <th>Justificativa</th>
                                                    <th>Por</th>
                                                    <th>Em</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($historicoPrazo as $dados) : ?>
                                                    <tr>
                                                        <td><?= $dados['valor_old'] ?></td>
                                                        <td><?= $dados['valor'] ?></td>
                                                        <td>
                                                            <?php
                                                            switch ($dados['justificativa']) {
                                                                case 'ATRASO':
                                                                    echo 'Atraso na Entrega';
                                                                    break;
                                                                case 'PEDIDOFORNECEDOR':
                                                                    echo 'A Pedido do Fornecedor';
                                                                    break;
                                                                case 'NOVACOTACAO':
                                                                    echo 'Inserida Nova Cotação';
                                                                    break;
                                                                case 'ATRASORESPOSTAFORNECEDOR':
                                                                    echo 'Fornecedor Demorou Responder';
                                                                    break;
                                                                default:
                                                                    echo $dados['justificativa'];
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?= $dados['nomecurto'] ?></td>
                                                        <td><?= dmahms($dados['criadoem']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Prazo Orçamento -->
                        <div class="form-groupt-data">
                            <?php if (empty($_1_u_cotacao_prazointerno)) : ?>
                                <label class="text-white mb-2">Prazo Orçamento</label>
                                <input name="_1_<?= $_acao ?>_cotacao_prazointerno"
                                    id="_1_<?= $_acao ?>_cotacao_prazointerno"
                                    class="form-control calendario"
                                    type="text"
                                    value="<?= $_1_u_cotacao_prazointerno ?>"
                                    vnulo>
                            <?php else : ?>
                                <label class="text-white mb-2">Prazo Orçamento <i class="fa fa-pencil field-icon"
                                        title="Editar Prazo Orçamento"
                                        onclick="alterarValor('prazointerno','<?= dma($_1_u_cotacao_prazointerno) ?>', 'modulohistorico', <?= $_1_u_cotacao_idcotacao ?>, 'Prazo Orçamento')"></i></label>
                                <div class="d-flext align-items-center">
                                    <input name="_1_<?= $_acao ?>_cotacao_prazointerno"
                                        id="_1_<?= $_acao ?>_cotacao_prazointerno"
                                        class="form-control readonly-field"
                                        readonly
                                        type="text"
                                        <?= $vnulo ?>
                                        value="<?= dma($_1_u_cotacao_prazointerno) ?>">
                                </div>
                            <?php endif; ?>

                            <input name="cotacao_prazointerno_old" type="hidden" value="<?= $_1_u_cotacao_prazointerno ?>">

                            <?php if (!empty($_1_u_cotacao_idcotacao)) :
                                $historicoPrazoInterno = CotacaoController::buscarHistoricoAlteracaoPrazoCotacao($_1_u_cotacao_idcotacao, 'prazointerno');
                                if ($historicoPrazoInterno) : ?>
                                    <i title="Histórico do Prazo Orçamento"
                                        class="fa fa-info-circle field-icon hoverazul tip"
                                        onclick="historico('prazointerno', <?= $_1_u_cotacao_idcotacao ?>, 'de Prazo Orçamento');"></i>
                                    <div id="prazo<?= $_1_u_cotacao_idcotacao ?>" style="display: none">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>De</th>
                                                    <th>Para</th>
                                                    <th>Justificativa</th>
                                                    <th>Por</th>
                                                    <th>Em</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($historicoPrazoInterno as $dados) : ?>
                                                    <tr>
                                                        <td><?= $dados['valor_old'] ?></td>
                                                        <td><?= $dados['valor'] ?></td>
                                                        <td>
                                                            <?php
                                                            switch ($dados['justificativa']) {
                                                                case 'ATRASO':
                                                                    echo 'Atraso na Entrega';
                                                                    break;
                                                                case 'PEDIDOFORNECEDOR':
                                                                    echo 'A Pedido do Fornecedor';
                                                                    break;
                                                                case 'NOVACOTACAO':
                                                                    echo 'Inserida Nova Cotação';
                                                                    break;
                                                                case 'ATRASORESPOSTAFORNECEDOR':
                                                                    echo 'Fornecedor Demorou Responder';
                                                                    break;
                                                                default:
                                                                    echo $dados['justificativa'];
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?= $dados['nomecurto'] ?></td>
                                                        <td><?= dmahms($dados['criadoem']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Referência -->
                        <div class="form-groupt-data">
                            <label class="text-white mb-2">Referência (Mês/Ano)</label>
                            <input name="_1_<?= $_acao ?>_cotacao_referencia"
                                id="_1_<?= $_acao ?>_cotacao_referencia"
                                onkeyup="mascararData(this)"
                                class="form-control"
                                type="text"
                                value="<?= $_1_u_cotacao_referencia ?>"
                                <?= $vreadonly ?>
                                placeholder="mm/yyyy">
                        </div>

                        <!-- Módulo Histórico -->
                        <div id="modulohistorico<?= $_1_u_cotacao_idcotacao ?>" style="display: none">
                            <table class="table table-hover">
                                <tr>
                                    <td>#namerotulo:</td>
                                    <td>
                                        <input name="#name_idobjeto" value="<?= $_1_u_cotacao_idcotacao ?>" type="hidden">
                                        <input name="#name_auditcampo" value="prazo" type="hidden">
                                        <input name="#name_tipoobjeto" value="cotacao" type="hidden">
                                        <input name="#name_valorold" value="#valor_campo_old" type="hidden">
                                        <input name="#name_campo" value="#valor_campo calendario" class="form-control <?= $calendario ?>" type="text">
                                    </td>
                                </tr>
                                <tr>
                                    <td>Justificativa:</td>
                                    <td>
                                        <select name="#name_justificativa" vnulo class="form-control">
                                            <?php fillselect(CotacaoController::$justificativaAlteraPrazo); ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>


                        <!-- Responsável -->
                        <div class="form-groupt">
                            <label class="text-white mb-2">Responsável</label>
                            <input id="idresponsavel"
                                class="form-control"
                                type="text"
                                name="_1_<?= $_acao ?>_cotacao_idresponsavel"
                                cbvalue="<?= $_1_u_cotacao_idresponsavel ?>"
                                value="<?= $jFunc[$_1_u_cotacao_idresponsavel]["nomecurto"] ?>"
                                <?= $vreadonly ?>
                                vnulo>
                        </div>

                        <!-- Exibir -->
                        <?php if (!empty($_1_u_cotacao_idcotacao)) : ?>
                            <div class="form-groupt">
                                <label class="text-white mb-2">Exibir</label>
                                <select name="Lista"
                                    id="Lista"
                                    onChange="visualizacao(this);"
                                    class="form-controlt">
                                    <?php
                                    $visualizacao = CotacaoController::buscarPreferenciaPessoa($_GET["_modulo"] . ".visualizacao", $_SESSION["SESSAO"]["IDPESSOA"]);
                                    fillselect(CotacaoController::$exibirVisualizacao, $visualizacao); ?>
                                    <?= $vreadonly ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <!-- Total -->
                        <?php if (!empty($valorTotalCotacao)) : ?>
                            <div class="form-groupt-total">
                                <label class="text-white mb-2">Total</label>
                                <div class="form-control readonly-field" style="text-align: right;">
                                    R$ <?= number_format(tratanumero($valorTotalCotacao), 2, ',', '.') ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Importação -->
                        <div class="form-groupt-orcamento">
                            <label class="text-white mb-2">Importação</label>
                            <div>
                                <input type="checkbox"
                                    onclick="alterarImportacao(this, <?= $_1_u_cotacao_idcotacao ?>)"
                                    <?= $_1_u_cotacao_importacao == 'S' ? 'checked' : '' ?>>
                            </div>
                        </div>

                        <!-- Status -->
                        <?php if (!empty($valorTotalCotacao)) : ?>
                            <div class="form-groupt">
                                <label class="text-white mb-2">Status</label>
                                <?php $rotulo = CotacaoController::buscarRotuloStatusFluxo($pagvaltabela, 'idcotacao', $_1_u_cotacao_idcotacao); ?>
                                <label class="alert-warning form-control" style="font-size: 15px;"
                                    id="statusButton"
                                    title="<?= $_1_u_cotacao_status ?>">
                                    <?= $rotulo['rotulo'] ?>
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <div class="row d-flex align-items-center">
                    <div class="col-xs-10">
                        <div class="w-100 d-flex">
                            <?php
                            if (!empty($_1_u_cotacao_idcotacao)) {
                            ?>
                                <div class="form-group col-md-6">
                                    <label class="titulo-body" id="titlecategoria">Categoria<i class="fa btn-sm fa-info-circle cinza pointer hoverazul <?= in_array($_1_u_cotacao_status, ['CONCLUIDO', 'CANCELADO']) ? '' : 'hidden' ?>" onclick="modalcategoria()"></i></label>
                                    <select id="picker_grupoes" class="selectpicker valoresselect col-md-12" data-actions-box="true" multiple="multiple" data-live-search="true" vnulo <?= in_array($_1_u_cotacao_status, ['CONCLUIDO', 'CANCELADO']) ? 'disabled="disabled"' : '' ?>>
                                        <?
                                        $resContaItem = CotacaoController::buscarGrupoES($_1_u_cotacao_idcotacao, $_GET["_modulo"]);
                                        $stringpicker = '<ul>';
                                        foreach ($resContaItem as $dadosContaItem) {
                                            if (!empty($dadosContaItem['idobjetovinc'])) {
                                                $selected = 'selected';
                                                $valuepicker .= $dadosContaItem['idcontaitem'] . ',';
                                                $stringpicker .= '<li>' . $dadosContaItem['contaitem'] . '</li>';
                                                $selecionadoContaItem = TRUE;
                                            } else {
                                                $selected = '';
                                                if ($selecionadoContaItem == FALSE) {
                                                    $selecionadoContaItem = FALSE;
                                                }
                                            }
                                            echo '<option data-tokens="' . retira_acentos($dadosContaItem['contaitem']) . '" value="' . $dadosContaItem['idcontaitem'] . '" ' . $selected . ' >' . $dadosContaItem['contaitem'] . '</option>';
                                        }
                                        $stringpicker .= '</ul>';
                                        echo '<option data-tokens="' . retira_acentos($dadosContaItem['contaitem']) . '" value="' . $dadosContaItem['idcontaitem'] . '" ' . $selected . ' >' . $dadosContaItem['contaitem'] . '</option>';
                                        ?>
                                    </select>
                                    <? $idcontaitemSelected = substr($valuepicker, 0, -1); ?>
                                    <div class="stringcategoria hidden"> <?= $stringpicker ?></div>
                                    <input type="hidden" name="sel_picker_idcontaitem" id="sel_picker_idcontaitem" value="<?= $idcontaitemSelected ?>">
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="titulo-body">Subcategoria<i class="fa btn-sm fa-info-circle cinza pointer hoverazul <?= in_array($_1_u_cotacao_status, ['CONCLUIDO', 'CANCELADO']) ? '' : 'hidden' ?>" onclick="modalsubcategoria()"></i></label>
                                    <select id="picker_contaitemprodserv" class="selectpicker valoresselect col-md-12" data-actions-box="true" multiple="multiple" data-live-search="true" vnulo <?= in_array($_1_u_cotacao_status, ['CONCLUIDO', 'CANCELADO']) ? 'disabled="disabled"' : '' ?>>
                                    </select>
                                    <input type="hidden" name="sel_picker_idcontaitemtipoprodserv" id="sel_picker_idcontaitemtipoprodserv">
                                    <span class="altertaSalvar hidden"><a title="Salvar para gravar as informações selecionadas" style="text-align: end;" class="fa fa-exclamation-triangle fa-1x laranja pointer"></a></span>
                                </div>
                                <div class="stgringsubcategoria hidden"></div>
                            <? } ?>
                        </div>
                    </div>
                    <? if ($_acao == 'u' && $idcontaitemSelected) { ?>
                        <div class="col-xs-2 text-right">
                            <a id="link-forecast" class="btn btn-primary">FORECAST DE COMPRAS 2025</a>
                        </div>
                    <? } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<? if ($_acao == 'u' && $idcontaitemSelected) { ?>
    <div class="row container-itens-forecast">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading pl-4 d-flex flex-between" data-toggle="collapse" href="#forecast-compra"><span>FORECAST COMPRAS vs ORÇAMENTO</span><i class="fa fa-chevron-down fa-2x"></i></div>
                <div id="forecast-compra" class="panel-body">
                    <div class="w-100 d-flex font-bold text-lg">
                        <div style="width: 20%"></div>
                        <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>Forecast Mês Atual</span></div>
                        <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>Valor Utilizado</span></div>
                        <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>Saldo Atual</span></div>
                        <div class="text-center p-1 d-flex justify-content-center align-items-center" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)">
                            <span class="mr-3">Acumulado Total </span>
                            <i id="btn-forecast" class="fa fa-info-circle pointer" style="color: rgba(51, 122, 183, 1);" onclick="abrirModalForecast()"></i>
                        </div>
                    </div>
                    <div id="corpo-forecast-itens" class="w-100 text-uppercase font-bold" style="color: rgba(152, 152, 152, 1);">
                        <div class="w-100 d-flex text-lg">
                            <div class="p-1 px-4" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)">
                                <div class="py-3 col-xs-7">
                                    <? include(__DIR__ . "/components/_skeleton.php") ?>
                                </div>
                            </div>
                            <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)">
                                <div class="py-3 col-xs-7 float-none text-center mx-auto">
                                    <? include(__DIR__ . "/components/_skeleton.php") ?>
                                </div>
                            </div>
                            <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>
                                    <div class="py-3 col-xs-7 float-none text-center mx-auto">
                                        <? include(__DIR__ . "/components/_skeleton.php") ?>
                                    </div>
                                </span></div>
                            <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)">
                                <div class="py-3 col-xs-7 float-none text-center mx-auto">
                                    <? include(__DIR__ . "/components/_skeleton.php") ?>
                                </div>
                            </div>
                            <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)">
                                <div class="py-3 col-xs-7 float-none text-center mx-auto">
                                    <? include(__DIR__ . "/components/_skeleton.php") ?>
                                </div>
                            </div>
                        </div>
                        <div class="w-100 d-flex text-lg">
                            <div class="p-1 px-4" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)">
                                <div class="py-3 col-xs-7">
                                    <? include(__DIR__ . "/components/_skeleton.php") ?>
                                </div>
                            </div>
                            <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)">
                                <div class="py-3 col-xs-7 float-none text-center mx-auto">
                                    <? include(__DIR__ . "/components/_skeleton.php") ?>
                                </div>
                            </div>
                            <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>
                                    <div class="py-3 col-xs-7 float-none text-center mx-auto">
                                        <? include(__DIR__ . "/components/_skeleton.php") ?>
                                    </div>
                                </span></div>
                            <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)">
                                <div class="py-3 col-xs-7 float-none text-center mx-auto">
                                    <? include(__DIR__ . "/components/_skeleton.php") ?>
                                </div>
                            </div>
                            <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)">
                                <div class="py-3 col-xs-7 float-none text-center mx-auto">
                                    <? include(__DIR__ . "/components/_skeleton.php") ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
<? } ?>

<?
// Converte as strings para objetos DateTime
$dateTime1 = DateTime::createFromFormat('d/m/Y H:i:s', $_1_u_cotacao_criadoem);
$dateTime2 = DateTime::createFromFormat('d/m/Y', "01/03/2025"); //data de corte para a validação

if (!empty($_1_u_cotacao_idcotacao) && !empty($_1_u_cotacao_referencia) || ($dateTime1 < $dateTime2)) {
    if ($visualizacao == 2) {
        // POR fornecedor
        if ($_acao == 'u') { ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default panelAbas" id="mainPanel">
                        <ul class="nav nav-tabs" id="Tab_lp" role="tablist">
                            <li role="presentation panel-heading" class="tabs-container li_cotacao" value="cotacao_solcom">
                                <a href="#cotacao_solcom" class="<?= $row['status'] == 'ATIVO' ? '' : 'cinzaclaro' ?>" tab="<?= $row['idlpgrupo'] ?>" role="tab" data-toggle="tab">
                                    Solicitação de Compras
                                </a>
                                <span class="bg-secundary badge badgesolcom pointer" titulo="Solicitação de Compras"></span>
                            </li>
                            <li role="presentation panel-heading" class="tabs-container li_cotacao" value="cotacao_sugestao">
                                <a href="#cotacao_sugestao" class="<?= $row['status'] == 'ATIVO' ? '' : 'cinzaclaro' ?>" tab="<?= $row['idlpgrupo'] ?>" role="tab" data-toggle="tab">
                                    Sugestão de Compras
                                </a>
                                <span class="bg-secundary badge badgeorc pointer" titulo="Sugestão de Compras"></span>
                            </li>
                            <li role="presentation panel-heading" class="tabs-container li_cotacao" value="cotacao_todos">
                                <a href="#cotacao_todos" class="<?= $row['status'] == 'ATIVO' ? '' : 'cinzaclaro' ?>" tab="<?= $row['idlpgrupo'] ?>" role="tab" data-toggle="tab">
                                    Todos
                                </a>
                                <span class="bg-secundary badge badgetodos pointer" titulo="Todos"></span>
                            </li>
                        </ul>
                        <div class="row">
                            <div class="col-md-4" style="margin-top: -3%; float: right; margin-right: 0.5%; margin-bottom: -7%;">
                                <input style="margin-right:10px;" type="text" class="form-control tipotext" autocomplete="off" id="inputFiltro" placeholder="Filtrar Dados">
                            </div>
                        </div>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade" role="tab" id="cotacao_solcom">
                                <?
                                $listarSolcom = CotacaoController::listarSolicitacaoCompraVincultadaCotacao($_1_u_cotacao_idcotacao, 'cotacao', $_1_u_cotacao_idempresa);
                                if (count($listarSolcom) > 0) {
                                ?>
                                    <div class="panel-body">
                                        <div class="panel panel-default " style="margin-top: -3px !important;">
                                            <div class="panel-heading nowrap hgt_trintacinco">
                                                <span class="qtdProdSolicitacao"></span> produto(s) encontrado(s)
                                                <i class="fa fa-arrows-v fa-2x cinzaclaro pointer" data-toggle="collapse" href="#cotacao_todos_itens_solicitacao_compras" title="Esconder/Mostrar Todos Itens" style="float: right; padding: 0 10px 0 10px;"></i>
                                            </div>
                                            <div class="cotacao_todos_itens" id="cotacao_todos_itens_solicitacao_compras">
                                                <table class="table table-striped planilha cotacaoabas" style="width: 100%;">
                                                    <thead>
                                                        <tr>
                                                            <td colspan="12">
                                                                <button id="Adicionar" type="button" class="btn btn-xs" style="background-color: #A9A9A9; color: #FFFFFF" onclick="addSolcom('true')" title="Adicionar Todos">
                                                                    <i class="fa fa-plus"></i> Adicionar Selecionados
                                                                </button>
                                                                <button id="Remover" type="button" class="btn btn-danger btn-xs" onclick="cancelarItemSolcom('true')" title="Remover Todos">
                                                                    <i class="fa fa-trash"></i> Remover Selecionados
                                                                </button>
                                                            </td>
                                                            <!-- <td><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" id="esconderMostrarTodosFornecedores" title="Esconder/Mostrar Todos Fornecedores" onclick="esconderMostrarTodosFornecedores('prodservsolcom')" style="float: right; padding: 6px 10px 0 10px;"></i></td> -->
                                                        </tr>
                                                        <tr>
                                                            <th class="wdt_dois">
                                                                <input name="marcartodos" title="Marcar/Desmarcar Todos" type="checkbox" onclick="checkallSolcom(this)">
                                                            </th>
                                                            <th class="wdt_cinco">Qtd</th>
                                                            <th class="wdt_dois">Un</th>
                                                            <th class="wdt_oito">Sigla</th>
                                                            <th class="wdt_vintecinco" colspan="2">Descrição</th>
                                                            <th class="wdt_doze">Tipo</th>
                                                            <th class="wdt_oito">Observação</th>
                                                            <th class="wdt_oito">Solicitado Em</th>
                                                            <th class="wdt_oito">Solicitado Por</th>
                                                            <th class="wdt_sete">Data Previsão</th>
                                                            <th class="wdt_sete">Solicitação</th>
                                                            <th class="wdt_sete">Ação</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?
                                                        $qtdProdSolcom = 0;
                                                        $totalItens = 0;
                                                        foreach ($listarSolcom as $_listarLinhasSolcom) {
                                                            $totalItens++;
                                                            if ((($idprodservOld != $_listarLinhasSolcom["idprodserv"])
                                                                    || ($idprodservOld == $_listarLinhasSolcom["idprodserv"] && $idsolcomOld != $_listarLinhasSolcom["idsolcom"]))
                                                                && $qtdProdSolcom > 0
                                                            ) {
                                                        ?>
                                                </table>
                                                </td>
                                                </tr>
                                            <?
                                                            }

                                                            if (
                                                                $idprodservOld != $_listarLinhasSolcom["idprodserv"]
                                                                || ($idprodservOld == $_listarLinhasSolcom["idprodserv"] && $idsolcomOld != $_listarLinhasSolcom["idsolcom"])
                                                            ) {
                                                                $qtdProdSolcom++;
                                            ?>
                                                <tr <? if ($_listarLinhasSolcom['urgencia'] == 'Y') { ?>style="background-color: mistyrose;" <? } ?>>
                                                    <td>
                                                        <input title="Solcom" type="checkbox" class="itemsolcom itemTodosProduto<?= $_listarLinhasSolcom['idprodserv'] ?>_<?= $_listarLinhasSolcom['idsolcom'] ?>" id="itemsolcom" idprodserv="<?= $_listarLinhasSolcom['idprodserv'] ?>" idsolcom="<?= $_listarLinhasSolcom['idsolcom'] ?>" idsolcomitem="<?= $_listarLinhasSolcom['idsolcomitem'] ?>">
                                                    </td>
                                                    <td><?= $_listarLinhasSolcom['qtdc'] ?></td>
                                                    <td><?= $_listarLinhasSolcom['un'] ?></td>
                                                    <td>
                                                        <?= $_listarLinhasSolcom['codprodserv'] ?>
                                                        <a title="Produto" class="fa fa-bars preto pointer modalProdServ" idprodserv="<?= $_listarLinhasSolcom['idprodserv'] ?>" modulo="prodserv"></a>
                                                    </td>
                                                    <td>
                                                        <?= mb_strtoupper($_listarLinhasSolcom['descr'], 'UTF-8') ?>
                                                        <a title="Produto" class="fa fa-bars preto pointer modalProdServ" idprodserv="<?= $_listarLinhasSolcom['idprodserv'] ?>" modulo="calculosestoque"></a>
                                                    </td>
                                                    <td>
                                                        <? if ($_listarLinhasSolcom['qtdfornecedor'] == 0) { ?>
                                                            <a title="Não possui Fornecedor" style="text-align: end;" class="fa fa-exclamation-triangle fa-1x laranja btn-lg pointer" style="font-size: medium;"></a>
                                                        <? } ?>
                                                    </td>
                                                    <td><?= $_listarLinhasSolcom['contaitem'] ?></td>
                                                    <td>
                                                        <? if (empty($_listarLinhasSolcom['obs'])) {
                                                                    $corurgente = 'cinza';
                                                                } else {
                                                                    $corurgente = 'azul';
                                                                } ?>
                                                        <i title="Observação" idsolcomitem="<?= $_listarLinhasSolcom['idsolcomitem'] ?>" class="fa btn-sm fa-info-circle <?= $corurgente ?> pointer hoverazul tip modalObsInternaClique"></i>
                                                        <div class="panel panel-default" hidden>
                                                            <div class="modalobsinterna<?= $_listarLinhasSolcom['idsolcomitem'] ?>" class="panel-body">
                                                                <div style="word-break: break-word;">
                                                                    <?= $_listarLinhasSolcom['obs'] ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?= dma($_listarLinhasSolcom['criadoem']) ?></td>
                                                    <td><?= $_listarLinhasSolcom['nomecurto'] ?></td>
                                                    <td>
                                                        <? if ($_listarLinhasSolcom['dataprevisao']) {
                                                                    echo dma($_listarLinhasSolcom['dataprevisao']);
                                                                } else {
                                                                    echo '-';
                                                                }

                                                        ?>
                                                    </td>
                                                    <td align="center">
                                                        <label class="alert-warning">
                                                            <?= $_listarLinhasSolcom['siglaidsolcom'] ?>
                                                            <a title="Cotação" class="fa fa-bars preto pointer hoverazul" href="?_modulo=solcom&_acao=u&idsolcom=<?= $_listarLinhasSolcom['idsolcom'] ?>" target="_blank"></a>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <a class="fa fa-plus-square fa-x verde btn-lg pointer" onclick="addSolcom('false', '<?= $_listarLinhasSolcom['idsolcomitem'] ?>')" title="Adicionar Solicitação Compras"></a>
                                                        <a class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable excluir" onclick="cancelarItemSolcom('false', '<?= $_listarLinhasSolcom['idsolcomitem'] ?>')" title="Cancelar Item"></a>
                                                        <i class="fa fa-arrows-v cinzaclaro colpaddingsolcom pointer cotacao_todos_fornecedores" title="Produto" data-toggle="collapse" idprodserv="<?= $_listarLinhasSolcom['idprodserv'] ?>" idsolcom="<?= $_listarLinhasSolcom['idsolcom'] ?>" href="#prodservsolcom<?= $_listarLinhasSolcom['idprodserv'] ?>_<?= $_listarLinhasSolcom['idsolcomitem'] ?>"></i>
                                                    </td>
                                                </tr>
                                                <tr class="prodservsolcom<?= $_listarLinhasSolcom['idprodserv'] ?>_<?= $_listarLinhasSolcom['idsolcomitem'] ?> collapse" style="height:40px;" id="prodservsolcom<?= $_listarLinhasSolcom['idprodserv'] ?>_<?= $_listarLinhasSolcom['idsolcomitem'] ?>" data-text="<?= $_listarLinhasSolcom['descr'] ?>">
                                                    <td colspan="15">
                                                        <table class="table table-striped planilha" style="width: 100%;">
                                                            <tr data-text="'.$row['descr'].'">
                                                                <input name="itemalerta_forn_<?= $_listarLinhasSolcom['idprodserv'] ?>" id="itemalerta_forn_<?= $_listarLinhasSolcom["idprodserv"] ?>" type="hidden" value="">
                                                                <td style="width: 2%;"></td>
                                                                <td style="width: 3%;"></td>
                                                                <td style="width: 36%;">
                                                                    Nome
                                                                    <a title="Produto" class="fa fa-bars preto pointer modalProdServ" idprodserv="<?= $_listarLinhasSolcom['idprodserv'] ?>" modulo="prodservfornecedor"></a>
                                                                </td>
                                                                <td style="width: 30%;">Descrição</td>
                                                                <td style="width: 10%; text-align: center;">Unidade Compra</td>
                                                                <td style="width: 7%; text-align: right;">Conversão</td>
                                                                <td style="width: 10%; text-align: center;">Unidade Padrão</td>
                                                            </tr>
                                                        <?
                                                            }
                                                        ?>
                                                        <tr>
                                                            <td></td>
                                                            <td><input type="checkbox" name="fornecedor" idprodservforn="<?= $_listarLinhasSolcom["idprodservforn"] ?>" class="checkTodosProduto<?= $_listarLinhasSolcom["idprodserv"] ?>" onclick="selecionaFornecedor(<?= $_listarLinhasSolcom['idprodserv'] ?>, 'cotacao_solcom', <?= $_listarLinhasSolcom['idsolcom'] ?>);"></td>
                                                            <td><?= strtoupper($_listarLinhasSolcom['nome']) ?></td>
                                                            <td><?= strtoupper($_listarLinhasSolcom['codforn']) ?></td>
                                                            <td align="center"><?= strtoupper($_listarLinhasSolcom['unidadedescr']) ?></td>
                                                            <td align="right"><?= $_listarLinhasSolcom['valconv'] ?></td>
                                                            <td align="center"><?= strtoupper($_listarLinhasSolcom['unidadeprod']) ?></td>
                                                        </tr>
                                                    <?
                                                            $idprodservOld = $_listarLinhasSolcom["idprodserv"];
                                                            $idsolcomOld = $_listarLinhasSolcom["idsolcom"];
                                                        }

                                                        if (count($listarSolcom) == $totalItens) {
                                                    ?>
                                                        </table>
                                                    </td>
                                                </tr>
                                            <?
                                                        }
                                            ?>
                                            </tbody>
                                            </table>
                                            </div>
                                        </div>
                                    </div>
                                <? } elseif (($selecionadoContaItem) || (count($listarSolcom) == 0 && $selecionadoContaItem == TRUE)) { ?>
                                    <div class="col-md-6" style="float: none;">
                                        <div class="alert alert-warning" role="alert">Não existe Solicitação de Compras para a Categoria e Subcategoria selecionadas.</div>
                                    </div>
                                <? } ?>
                            </div>

                            <div role="tabpanel" class="tab-pane fade" role="tab" id="cotacao_sugestao">
                                <div class="panel-body mostraProdutosSugestao">
                                    <div class="panel panel-default" style="margin-top: -5px !important;">
                                        <div class="panel-heading nowrap hgt_trintacinco">
                                            <span class="qtdProdSugestao"></span> produto(s) encontrado(s)
                                            <i class="fa fa-arrows-v fa-2x cinzaclaro pointer" data-toggle="collapse" href="#cotacao_todos_itens_sugestao_compras" title="Esconder/Mostrar Todos Itens" style="float: right; padding: 0 10px 0 10px;"></i>
                                        </div>
                                        <div class="cotacao_todos_itens" id="cotacao_todos_itens_sugestao_compras">
                                            <table class="table table-striped planilha cotacaoabas fixarthtable table_sugestao_compras" style="width: 100%;">
                                                <thead>
                                                    <tr style="background-color: whitesmoke;">
                                                        <td colspan="11">
                                                            <button id="Adicionar" type="button" class="btn btn-xs" style="background-color: #A9A9A9; color: #FFFFFF;" onclick="addProdutoAlerta('true', 'cotacao_sugestao')" title="Adicionar Todos">
                                                                <i class="fa fa-plus"></i> Adicionar Selecionados
                                                            </button>
                                                        </td>
                                                        <td><i class="fa fa-arrows-v fa-2x azul pointer" id="esconderMostrarTodosFornecedores" title="Esconder/Mostrar Todos Fornecedores" onclick="esconderMostrarTodosFornecedores('prodservprodalerta')" style="float: right; padding: 6px 10px 0 10px;"></i></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="sol_un">
                                                            <input name="marcartodos" title="Marcar/Desmarcar Todos" type="checkbox" onclick="marcarTodosProdutoAlerta(this)">
                                                        </th>
                                                        <th class="wdt_cinco qtd" title="Quantidade">Qtd</th>
                                                        <th class="wdt_cinco" title="Unidade" class="unidade">Un</th>
                                                        <th class="wdt_oito">Sigla</th>
                                                        <th class="wdt_trintaoito" class="descricao">Descrição</th>
                                                        <th class="wdt_quatro_right">Estoque</th>
                                                        <th class="wdt_oito_right">Sug. Compra</th>
                                                        <th class="wdt_seis_right">Est. Mín.</th>
                                                        <th class="wdt_oito_right">Est. Mín. Aut.</th>
                                                        <th class="wdt_oito_right">Dias Estoque</th>
                                                        <th class="wdt_quatorze">Orçamento</th>
                                                        <th class="wdt_um" style="width: 1%;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane fade" role="tab" id="cotacao_todos">
                                <div class="panel-body">
                                    <div class="panel panel-default" style="margin-top: -5px !important;">
                                        <div class="panel-heading">Pesquisar Todos Produtos</div>
                                        <table class="table table-striped planilha cotacaoabas" style="width: 100%;">
                                            <tr>
                                                <td align="right" class="col-md-1">
                                                    Pesquisa:
                                                </td>
                                                <td class="col-md-12">
                                                    <div class="col-md-6">
                                                        <div class="input-group" style="width: 40%; padding: 10px 0 10px 0;">
                                                            <input placeholder="Pesquise um item" class="form-control" name="descri" id="descri" size="15" type="text" value="" style="width: 40em;">
                                                            <span class="input-group-addon" style="background: #337ab7; padding: 0px;">
                                                                <i id="pesquisaritem" style="padding: 6px 12px;" class="fa fa-search pointer branco fa-blink pesquisaritem" title="Pesquisa itens que apenas possuem estoque mínimo da mesma  Subcategoria" onclick="getDadosATualizaInfoAbas('cotacao_todos')"></i>
                                                            </span>
                                                            <div id="circularProgressPesquisa" class="circularProgressPesquisa" style="display: none;"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mostraOcultos" hidden>
                                                        <button class="btn btn-secondary" title="Ocultar Estoque Mínimo Menor que Zero" style="margin-left: -40px; border: 1px solid #ccc; border-radius: 4px; margin-top: 10px;" cboculto="N" onclick="mostrarOcultarProdutoEstoqueMinimo(this)">
                                                            <span>Est. Mín.</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                        <div class="panel-body mostraProdutosTodos" style="display: none;">
                                            <div class="panel panel-default" style="margin-top: -3px !important;">
                                                <div class="panel-heading nowrap" style="height: 35px;">
                                                    <span class="qtdProdTodos" qtd=""></span> produto(s) encontrado(s)
                                                    <i class="fa fa-arrows-v fa-2x cinzaclaro pointer" data-toggle="collapse" href="#cotacao_todos_itens_todos_produtos" title="Esconder/Mostrar Todos Itens" style="float: right; padding: 0 10px 0 10px;"></i>
                                                    <i class="fa fa-arrows-v fa-1x cinzaclaro pointer" id="esconderMostrarTodosFornecedores" title="Esconder Todos" onclick="esconderMostrarTodosFornecedores('prodservtodos')" style="float: right; padding: 0 10px 0 10px;"></i>
                                                </div>
                                                <div class="cotacao_todos_itens_todos_produtos" id="cotacao_todos_itens_todos_produtos">
                                                    <table class="table table-striped planilha cotacaoabas fixarthtable table_cotacao_todos" style="width: 100%;">
                                                        <thead>
                                                            <tr style="background-color: whitesmoke;" style="top: 0">
                                                                <td colspan="15">
                                                                    <button id="Adicionar" type="button" class="btn btn-xs" style="background-color: #A9A9A9; color: #FFFFFF;" onclick="addProdutoAlerta('true', 'cotacao_todos')" title="Adicionar Todos">
                                                                        <i class="fa fa-plus"></i>Adicionar Selecionados
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            <tr style="background-color: #f0f0f0" class="linha-fixa">
                                                                <th class="wdt_um">
                                                                    <input name="marcartodos" title="Marcar/Desmarcar Todos" type="checkbox" onclick="marcarTodosProdutoAlerta(this)">
                                                                </th>
                                                                <th class="wdt_cinco qtd" title="Quantidade">Qtd</th>
                                                                <th class="wdt_cinco" title="Unidade" class="unidade">Un</th>
                                                                <th class="wdt_oito">Sigla</th>
                                                                <th class="wdt_trintaoito" class="descricao">Descrição</th>
                                                                <th class="wdt_quatro_right">Estoque</th>
                                                                <th class="wdt_oito_right">Sug. Compra</th>
                                                                <th class="wdt_seis_right">Est. Mín.</th>
                                                                <th class="wdt_oito_right">Est. Mín. Aut.</th>
                                                                <th class="wdt_oito_right">Dias Estoque</th>
                                                                <th class="wdt_quatorze">Orçamento</th>
                                                                <th class="wdt_um" style="width: 1%;"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="circularProgressIndicator" style="display: none;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?
        }

        //------- Serão reduzidas a quantidade de Vezes das Consultas ---------------------------------------
        //------- Os dados serão inseridos em array para depois montar o HTML    
        $infoNf = CotacaoController::buscarNfPorTipoObjetoSoliPor($_1_u_cotacao_idcotacao, $_1_u_cotacao_idempresa);

        //Os fillselect estão neste lugar para que rodem apenas uma vez ao carregar a página, pois existem Orçamentos com várias cotações.
        $fillSelectUnidadeVolume = CotacaoController::listarUnidadeVolume();
        $fillSelectContaItem = CotacaoController::listarContaItemAtivoShare();
        $fillSelectTipoProdserv = CotacaoController::listarProdservTipoProdServPorEmpresa($_1_u_cotacao_idempresa);
        $fillSelectTransportadora = CotacaoController::listarFornecedorPessoaPorIdTipoPessoa(11);
        $dominio = CotacaoController::buscarDominio($_1_u_cotacao_idempresa);
        $controleCabecarioReprovadosCancelados = TRUE; //CRIAÇAO DO HEADER DO COLAPSE DE CANCELADOS E REPROVADOS
        $controleCabecarioConcluido = TRUE; //CRIAÇAO DO HEADER DO COLAPSE DE CONCLUIDOS
        $controleCabecarioAprovados = TRUE; //CRIAÇAO DO HEADER DO COLAPSE DE APROVADOS
        $controleCabecarioAndamento = TRUE; //CRIAÇAO DO HEADER DO COLAPSE DE APROVADOS


        $countConcluido = 0;
        $countAprovado = 0;
        $countAndamento = 0;

        $i = 1;
        $l = 0;
        $m = 0;

        foreach ($infoNf['nf'] as $_nf) {
            $i = $i + 1;
            $l = $l + 1;

            if ($_nf['status'] == 'APROVADO' || $_nf['status'] == 'PREVISAO' || $_nf['status'] == 'RESPONDIDO' || $_nf['status'] == 'AUTORIZADO' || $_nf['status'] == 'AUTORIZADA') {
                $vnulo = "vnulo";
            } else {
                $vnulo = "";
            }

            if ($_nf['status'] == "INICIO" || $_nf['status'] == "RESPONDIDO" || $_nf['status'] == "AUTORIZADO" || $_nf['status'] == "ENVIADO" || $_nf['status'] == 'AUTORIZADA') {
                $nfreadonly = "";
                $nfdesabled = "";
            } else {
                $nfreadonly = "readonly='readonly'";
                $nfdesabled = "disabled='disabled'";
            }

            if ($_nf['pedidoentrega'] == 'atrasado') {
                $statusent = 'ABERTO';
            } else {
                $statusent = $_nf['status'];
            }


            //sempre mostra a aba de andamento
            if ($controleCabecarioAndamento) {
                $controleCabecarioAndamento = FALSE; ?>
                <div class="row">
                    <div class=" col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading" style="background:#EFE885; height: 33px;">
                                <div style="color: #000000; font-size:medium;" class="emandamento"></div>
                                <div class="col-xs-2" style="text-align:right;"><b><i class="fa fa-arrows-v fa-2x azul pointer expandirtodos" title="Expandir todos" style="font-size: medium; text-align: right;" onclick="abrirtudo(this,'andamento')"> </i></b></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?
            }

            if (!in_array($_nf['status'], array('APROVADO', 'CONCLUIDO', 'CANCELADO', 'REPROVADO'))) {
                $countAndamento++;
                $aba = "andamento";
            }

            if (in_array($_nf['status'], array('APROVADO', 'DIVERGENCIA', 'CONFERIDO', 'INICIO RECEBIMENTO'))) {
                $countAprovado++;
                $aba = "aprovado";
                if ($controleCabecarioAprovados) {
                    $controleCabecarioAprovados = FALSE;
                ?>
                    <div class="row">
                        <div class=" col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading" style="background:#4B91DE; height: 33px;">
                                    <div class="col-xs-10 emaprovado" style="color: #FFFFFF; font-size:medium;" onclick="mostracotacao('APROVADO')"></div>
                                    <div class="col-xs-2" style="text-align:right"><i class="fa fa-arrows-v fa-2x azul pointer expandirtodos" title="Expandir todos" style="font-size: medium; text-align: right; color: #FFFFFF;" onclick="abrirtudo(this,'aprovado')"> </i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?
                }
            }

            if (in_array($_nf['status'], array('CONCLUIDO'))) {
                $countConcluido++;
                $aba = "concluido";
                if ($controleCabecarioConcluido) {
                    $controleCabecarioConcluido = FALSE; ?>
                    <div class="row">
                        <div class=" col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading" style="background:#22B14C; height: 33px;">
                                    <div class="col-xs-10 emconcluido" style="color: #FFFFFF; font-size:medium;" onclick="mostracotacao('CONCLUIDO')"></div>
                                    <div class="col-xs-2" style="text-align:right;"><i class="fa fa-arrows-v fa-2x azul pointer expandirtodos" title="Expandir todos" style="font-size: medium; color: #FFFFFF;" onclick="abrirtudo(this,'concluido')"> </i></div>
                                </div>
                            </div>
                        </div>
                    </div>
            <?
                }
            }


            if (in_array($_nf['status'], array('CANCELADO', 'REPROVADO'))) {
                $countCancelado++;
                continue;
            }

            $descrItensNf = "";
            $taxaConversao = FALSE;
            foreach ($infoNf['nfitens'][$_nf['idnf']] as $_itens) {
                if (empty($_itens["codforn"])) {
                    $descrProduto = $_itens["descr"] . " - " . $_itens["codprodserv"];
                } else {
                    $descrProduto = $_itens["codforn"];
                }
                $descrItensNf .= " " . $descrProduto;

                if (in_array($_itens["moedaext"], array('USD', 'EUR')) && !empty($_itens["vlritemext"])) {
                    $taxaConversao = TRUE;
                } elseif ($taxaConversao == FALSE) {
                    $taxaConversao = FALSE;
                }
            }
            ?>
            <div class="row cotacao<?= $_nf['status'] ?>" style="display: block;">
                <div class="col-md-12 cotacaoabas cotacaoItensNota">
                    <div class="panel panel-default" <?= $stylePanelDefault ?> id="nftable<?= $_nf['idnf'] ?>" data-text="<?= $descrItensNf ?>">
                        <div class="panel-heading <?= $statusent ?>" <?= $stylePanelHeading ?> id="divcor<?= $_nf['idnf'] ?>" data-text="<?= $descrItensNf ?>">
                            <!-- Linha de conteúdo -->
                            <div style="display: flex; flex-wrap: wrap; align-items: flex-start; gap: 1px;" data-text="<?= $descrItensNf ?>">

                                <!-- Primeira coluna (Chevron) -->
                                <div style="flex: 0 0 1%; min-width: 20px;">
                                    <span style="display: block; height: 20px;"></span>
                                    <i class="fa fa-chevron-down azul pointer"
                                        style="padding-right: 10px;"
                                        onclick="mostraitenscot(this,'cotacao<?= $_nf['idnf'] ?>')"></i>
                                </div>

                                <!-- Segunda coluna (ID/Duplicar) -->
                                <div style="flex: 0 0 1%; min-width: 45px;">
                                    <span style="display: block; height: 20px;"></span>
                                    <input type="hidden"
                                        id="cotacao<?= $_nf['idnf'] ?>iddiv"
                                        class="cotacao<?= $_nf['idnf'] ?>iddiv"
                                        value="<?= $statusent ?>">
                                    <label class="idbox" style="padding: 5px; display: inline-block;"><?= $l ?></label>-
                                    <a class="fa fa-clone pointer azul hoverpreto"
                                        style="font-size: medium;"
                                        title="Duplicar Cotação"
                                        onclick="duplicarcompra(<?= $_nf['idnf'] ?>)"></a>
                                </div>

                                <!-- Migrar Cotação -->
                                <div style="flex: 0 0 1%; min-width: 25px;">
                                    <? if (count($infoNf['nfitens']['migrarCotacao'][$_nf['idnf']]) > 0) { ?>
                                        <span style="display: block; height: 20px;"></span>
                                        <a id="altorcamento"
                                            class="fa fa-unlink fa-2x pointer modalMigrarCotacaoClique azul hoverpreto"
                                            idnf="<?= $_nf['idnf'] ?>"
                                            title="Migrar Cotação"></a>
                                        <div class="panel panel-default" hidden>
                                            <div class="modalmigrarcotacao<?= $_nf['idnf'] ?>">
                                                Selecionar Cotação:
                                                <select id="val_idobjetosolipor<?= $_nf['idnf'] ?>" class="size25" onchange="alterarCotacao(<?= $_nf['idnf'] ?>, this)">
                                                    <option value=""></option>
                                                    <? fillselect($infoNf['nfitens']['migrarCotacao'][$_nf['idnf']]); ?>
                                                </select>
                                            </div>
                                        </div>
                                    <? } else { ?>
                                        <span style="display: block; height: 20px;"></span>
                                        <i class="fa fa-unlink fa-2x cinzaclaro">
                                        </i>
                                    <? } ?>
                                </div>

                                <!-- Cotação -->
                                <div style="flex: 0 0 4%; min-width: 60px;">
                                    <span style="display: block; height: 20px;">Cotação:</span>
                                    <label class="idbox" style="padding: 5px; display: inline-block;">
                                        <a title="Cotação Fornecedor"
                                            href="?_modulo=cotacaoforn&_acao=u&idnf=<?= $_nf['idnf'] ?>"
                                            target="_blank"><?= $_nf['idnf'] ?>
                                        </a>
                                    </label>
                                    <? if ($_nf['idnforigem']) { ?>
                                        <i class="fa fa-info-circle azul pointer hoverpreto tip">
                                            <span class="infoNfDuplicada">
                                                <p>Cotação duplicada REF: <b><?= $_nf['idnforigem'] ?></b></p>
                                                <p>Criado em: <b><?= dmahms($_nf['criadoem']) ?></b></p>
                                                <p>Criado por: <b><?= $_nf['criadopor'] ?></b></p>
                                            </span>
                                        </i>
                                    <? } ?>
                                </div>

                                <!-- Fornecedor -->
                                <div style="flex: 0 0 25%; min-width: 300px;">
                                    <span style="display: block; height: 20px;">Fornecedor:</span>
                                    <label class="idbox" style="padding: 5px; width: 100%; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <a title="Fornecedor"
                                            href="?_modulo=pessoa&_acao=u&idpessoa=<?= $_nf['idpessoa'] ?>"
                                            target="_blank"><?= $_nf['nome'] ?>
                                        </a>
                                    </label>
                                </div>
                                <div style="flex: 0 0 2%; min-width: 5px; text-align: center;">
                                    <?
                                    $resultado = $infoNf['resultadoavaliacaofornecedor'][$_nf['idpessoa']]['resultado']; ?>
                                    <span style="display: block; height: 20px;"></span>
                                    <? if (COUNT($resultado) > 0) {
                                        if ($row["resultado"] == 'REPROVADO') { ?>
                                            <a class="pull-left"
                                                title="Fornecedor <?= $resultado ?>">
                                                <i class="fa fa-exclamation-triangle fa-1x vermelho btn-lg pointer"></i>
                                            </a>
                                        <? }
                                    } else { ?>
                                        <a class="pull-left"
                                            title="Avaliação do fornecedor PENDENTE">
                                            <i class="fa fa-exclamation-triangle fa-1x laranja btn-lg pointer"></i>
                                        </a>
                                    <? } ?>
                                </div>

                                <!-- Tipo NF -->
                                <div style="flex: 0 0 8%; min-width: 100px;">
                                    <span style="display: block; height: 20px;">Tipo NF:</span>
                                    <?
                                    if (empty($rownf['tiponf'])) {
                                        $_tiponf = 'C';
                                    } else {
                                        $_tiponf = $rownf['tiponf'];
                                    }
                                    ?>
                                    <select id="tiponf"
                                        name="_<?= $i ?>_<?= $_acao ?>_nf_tiponf"
                                        vnulo
                                        style="width: 100%; max-width: 100px;"
                                        <?= $_nf['status'] == "CONCLUIDO" ? 'disabled="disabled"' : '' ?>>
                                        <? fillselect(CotacaoController::$tipoNf, $_tiponf); ?>
                                    </select>
                                </div>

                                <!-- Finalidade -->
                                <div style="flex: 0 0 15%; min-width: 80px;">
                                    <span style="display: block; height: 20px;">Finalidade:</span>
                                    <?
                                    if (count($infoNf['fillSelectFinalidadeProdserv'][$_nf['idpessoa']]) == 0) {
                                        $option = "<option value=''>ALERTA: Configurar Finalidade no Fornecedor</option>";
                                    } elseif (count($infoNf['fillSelectFinalidadeProdserv'][$_nf['idpessoa']]) > 1) {
                                        $option = "<option value=''></option>";
                                    } else {
                                        $option = "";
                                    }

                                    if (count($infoNf['fillSelectFinalidadeProdserv'][$_nf['idpessoa']]) == 1) {
                                        $idfinalidadeprodserv = array_keys($infoNf['fillSelectFinalidadeProdserv'][$_nf['idpessoa']]);
                                        $idfinalidadeprodserv = $idfinalidadeprodserv[0];
                                    } elseif (!empty($_nf['idfinalidadeprodserv'])) {
                                        $idfinalidadeprodserv = $_nf['idfinalidadeprodserv'];
                                    } else {
                                        $idfinalidadeprodserv = "";
                                    }
                                    ?>
                                    <select id="idfinalidadeprodserv"
                                        name="_<?= $i ?>_<?= $_acao ?>_nf_idfinalidadeprodserv"
                                        class="fillCheck"
                                        <? if ($_nf['status'] != "CANCELADO") { ?> vnulo <? } ?>
                                        <? if ($_nf['status'] == "CONCLUIDO" or $_nf['status'] == "INICIO RECEBIMENTO" or $_nf['status'] == "DIVERGENCIA" or $_nf['status'] == "CORRIGIDO" or $_nf['status'] == "CONFERIDO" or $_nf['status'] == "APROVADO") { ?>disabled='disabled' <? } ?>
                                        style="width: 95%;">
                                        <?= $option ?>
                                        <? fillselect($infoNf['fillSelectFinalidadeProdserv'][$_nf['idpessoa']], $idfinalidadeprodserv); ?>
                                    </select>
                                    <input type="hidden" name="cnpj_forn" value="<?= $_nf['cpfcnpj'] ?>">
                                </div>

                                <!-- Emissão NF -->
                                <div style="flex: 0 0 5%; min-width: 100px;">
                                    <span style="display: block; height: 20px;">Emissão NF:</span>
                                    <input name="_<?= $i ?>_<?= $_acao ?>_nf_dtemissao"
                                        class="calendario size10 dtemissao<?= $_nf['idnf'] ?> alterarDtEmissao"
                                        idnf="<?= $_nf['idnf'] ?>"
                                        id="fdata<?= $i ?>"
                                        type="text"
                                        value="<?= dma($_nf["dtemissao"]) ?>"
                                        <?= $nfdesabled ?>
                                        style="width: 100%; max-width: 100px;">
                                </div>


                                <!-- NF -->
                                <div style="flex: 0 0 2%; min-width: 20px; text-align: center;">
                                    <span style="display: block; height: 20px;"></span>
                                    <? if ($_nf['status'] != "INICIO" && $_nf['status'] != "ABERTO" && $_nf['status'] != "RESPONDIDO" && $_nf['status'] != "AUTORIZADO" && $_nf['status'] != 'AUTORIZADA' && $_nf['status'] != "REPROVADO" && $_nf['status'] != "CANCELADO") {
                                        $modulo = 'nfentrada'; ?>
                                        <a class="fa fa-file fa-2x pointer azul hoverpreto"
                                            title="NF"
                                            onclick="janelamodal('?_modulo=<?= $modulo ?>&_acao=u&idnf=<?= $_nf['idnf'] ?>')"></a>
                                    <? } else { ?>
                                        <i class="fa fa-file fa-2x cinzaclaro"
                                            title="NF"></i>
                                    <? } ?>
                                </div>

                                <?
                                //usado para enviar o e-mail
                                if ($_nf['status'] == "DIVERGENCIA" || $_nf['status'] == "APROVADO" || $_nf['status'] == "PREVISAO" || $_nf['status'] == "REPROVADO" || $_nf['status'] == "CANCELADO") {
                                    $tipoemail = 'emailaprovacao';
                                    $tipoenvio = 'COTACAOAPROVADA';
                                    $rotemail = 'Aprovação';
                                    if ($_nf["emailaprovacao"] == 'Y') {
                                        $classtdemail = "amarelo";
                                        $varalt = 'N';
                                    } elseif ($_nf["emailaprovacao"] == 'O' || $_nf["emailaprovacao"] == 'R') {
                                        $classtdemail = "verde";
                                        $varalt = 'N';
                                    } elseif ($_nf["emailaprovacao"] == 'E') {
                                        $classtdemail = "vermelho";
                                        $varalt = 'Y';
                                    } else {
                                        $classtdemail = "cinza";
                                        $varalt = 'Y';
                                    }
                                } else {
                                    $tipoemail = 'envioemailorc';
                                    $tipoenvio = 'COTACAO';
                                    $rotemail = 'Cotação';
                                    if ($_nf["envioemailorc"] == 'Y') {
                                        $classtdemail = "amarelo";
                                        $varalt = 'N';
                                    } elseif ($_nf["envioemailorc"] == 'O' or $_nf["envioemailorc"] == 'R') {
                                        $classtdemail = "verde";
                                        $varalt = 'N';
                                    } elseif ($_nf["envioemailorc"] == 'E') {
                                        $classtdemail = "vermelho";
                                        $varalt = 'Y';
                                    } else {
                                        $classtdemail = "cinza";
                                        $varalt = 'Y';
                                    }
                                }

                                if ($qtdempresaemail[$tipoenvio] == 1) {
                                    $nemails = 1;
                                } else {
                                    if ($qtdempresaemail[$tipoenvio] > 1) {
                                        $nemails = 2;
                                    } else {
                                        $nemails = 0;
                                    }
                                }

                                if (count($infoNf['empresaemailobjeto'][$_nf['idnf']][$tipoenvio]) < 1) {
                                    $setemail = 1;
                                } else {
                                    $setemail = 0;
                                }

                                if ($nemails == 1) { ?>
                                    <input id="emailunico" type="hidden" value="<?= $dominio["idemailvirtualconf"] ?>">
                                    <input id="idempresaemail" type="hidden" value="<?= $dominio["idempresa"] ?>">
                                <? }

                                $formatadata = explode('/', $_1_u_cotacao_prazo);
                                $date = $formatadata[2] . '-' . $formatadata[1] . '-' . $formatadata[0] . ' 23:59:59';
                                $timeStampPrazo = strtotime($date);
                                $timeStampNow = strtotime(date('Y-m-d'));

                                if ($timeStampPrazo < $timeStampNow) {
                                    $fdel = "alert('O prazo para envio do e-mail venceu no dia " . $_1_u_cotacao_prazo . ".')";
                                } else {
                                    $fdel = "altflagemail(" . $_nf["idnf"] . ",'nf','" . $tipoemail . "','" . $varalt . "'," . $nemails . ");";
                                }
                                ?>

                                <!-- Email -->
                                <div style="flex: 0 0 2%; min-width: 20px; text-align: center;">
                                    <span style="display: block; height: 20px;"></span>
                                    <input id="setemail" type="hidden" value="<?= $setemail ?>">
                                    <i class="fa fa-envelope fa-2x pointer <?= $classtdemail ?> hoverazul"
                                        title="Enviar email <?= $rotemail ?>"
                                        onclick="<?= $fdel ?>"></i>
                                </div>

                                <!-- Emails enviados -->
                                <div style="flex: 0 0 2%; min-width: 20px; text-align: center;">
                                    <span style="display: block; height: 20px;"></span>
                                    <? $idmailfila = $infoNf['mailfila'][$_nf['idnf']]['idmailfila'];
                                    if (count($idmailfila) > 0) { ?>
                                        <a title="Ver emails enviados"
                                            onclick="janelamodal('?_modulo=envioemail&_acao=u&idmailfila=<?= $idmailfila ?>')">
                                            <i class="fa fa-envelope-o fa-2x cinza pointer"></i><i style="z-index: 2300;margin-left:-5px;margin-top:-7px;" class="fa fa-search cinza"></i>
                                        </a>
                                    <? } else { ?>
                                        <i title="Sem emails enviado" class="fa fa-envelope-o fa-2x cinzaclaro"></i><i style="z-index: 2300;margin-left:-5px;margin-top:-7px;" class="fa fa-search cinzaclaro"></i>

                                    <? } ?>

                                </div>
                                <!-- Propostas anexas -->
                                <div style="flex: 0 0 2%; min-width: 20px; text-align: center;">
                                    <span style="display: block; height: 20px;"></span>
                                    <i
                                        <? $nomeAnexoProposta = $infoNf['anexocotacao'][$_nf['idnf']];
                                        if (count($nomeAnexoProposta) > 0) {
                                            $arrprop .= $arrvirg . $_nf["idnf"];
                                            $arrvirg = ","; ?>
                                        id="propostaanexa_<?= $_nf['idnf'] ?>"
                                        title="Propostas Anexas"
                                        class="fa fa-paperclip fa-2x pointer azul hoverpreto"></i>
                                    <div class="webui-popover-content" id="content_<?= $_nf['idnf'] ?>">
                                        <table>
                                            <?
                                            foreach ($nomeAnexoProposta  as $_nomeAnexoProposta) {
                                            ?>
                                                <tr>
                                                    <td>
                                                        <a class="pointer" onclick="janelamodal('upload/<?= $_nomeAnexoProposta['caminho'] ?>');"><?= $_nomeAnexoProposta['nome'] ?></a>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                        </table>
                                    </div>
                                <? } else { ?>
                                    <i class="fa fa-paperclip fa-2x cinzaclaro"
                                        title="Sem propostas Anexas"></i>
                                <? } ?>
                                </div>
                                <!-- Status -->
                                <div style="flex: 0 0 10%; min-width: 100px;">
                                    <span style="display: block; height: 20px;">Status:</span>
                                    <input name="_<?= $i ?>_<?= $_acao ?>_nf_idnf"
                                        type="hidden"
                                        value="<?= $_nf['idnf'] ?>">
                                    <? if ($_nf['status'] == "INICIO" || $_nf['status'] == "RESPONDIDO" || $_nf['status'] == "AUTORIZADO" || $_nf['status'] == 'AUTORIZADA' || $_nf['status'] == "ENVIADO") { ?>
                                        <input type="hidden"
                                            id="nfstatus<?= $_nf['idnf'] ?>"
                                            value="<?= $_nf['status'] ?>">
                                        <select class="size10 nfstatus<?= $_nf['idnf'] ?>"
                                            id="nfstatus"
                                            onchange="validarCamposPreenchidos(this, <?= $_nf['idnf'] ?>, '<?= $_nf['status'] ?>', '<?= $_itens['idtipoprodserv'] ?>', '<?= $_itens['qtd'] * $_itens['vlritem'] ?>');"
                                            style="width: 100%; max-width: 120px;">
                                            <? fillselect(CotacaoController::$statusCotacao, $_nf['status']); ?>
                                        </select>
                                    <? } else { ?>
                                        <label class="idbox" style="padding: 5px; display: inline-block; width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <input type="hidden" id="nfstatus" value="<?= $_nf['status'] ?>">
                                            <?= $_nf['status'] ?>
                                        </label>
                                    <? } ?>
                                </div>
                                <!-- Botão Salvar -->
                                <div style="flex: 0 0 10%; min-width: 100px; text-align: center;">
                                    <span style="display: block; height: 20px;"></span>
                                    <button type="button"
                                        class="btn btn-success btn-xs"
                                        onclick="salvarNf(<?= $_nf['idnf'] ?>,<?= $i ?>)"
                                        title="Salvar Este"
                                        style="font-size: medium;">
                                        <i class="fa fa-circle"></i> Salvar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body cotacao<?= $aba ?>" id="cotacao<?= $_nf['idnf'] ?>" style="display: none;">
                            <div class=" row">
                                <div class="col-md-7">
                                    <table>
                                        <tr>
                                            <td align="left">Nº Orçamento:</td>
                                            <td align="left">Vendedor(a):</td>
                                            <td align="left">Telefone:</td>
                                            <? if (!empty($_nf['observacaore'])) { ?>
                                                <td align="left">Observação Fronecedor:</td>
                                            <? } ?>
                                        </tr>
                                        <tr>
                                            <?
                                            if ($_nf['status'] == 'ENVIADO') {
                                                $infoStatus = "disabled title='Não é possivel editar enquanto o status for ENVIADO'";
                                            } else {
                                                $infoStatus = "";
                                            } ?>
                                            <!-- orçamento -->
                                            <td>
                                                <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> class="size25" name="_<?= $i ?>_<?= $_acao ?>_nf_pedidoext" type="text" value="<?= $_nf["pedidoext"] ?>" <?= $nfdesabled ?>>
                                            </td>

                                            <!-- vendedor -->
                                            <td>
                                                <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> class="size25" name="_<?= $i ?>_<?= $_acao ?>_nf_aoscuidados" type="text" value="<?= $_nf["aoscuidados"] ?>" <?= $nfdesabled ?>>
                                            </td>

                                            <!-- telefone -->
                                            <td>
                                                <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> class="size15" name="_<?= $i ?>_<?= $_acao ?>_nf_telefone" type="text" value="<?= $_nf["telefone"] ?>" <?= $nfdesabled ?>>
                                            </td>

                                            <!--obervaçao fornecedor -->
                                            <td>
                                                <? if (!empty($_nf['observacaore'])) { ?>
                                                    <textarea class="alert-warning size40" disabled> <?= $_nf['observacaore'] ?> </textarea>
                                                <? } ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?
                            if ($_nf['marcartodosnfitem'] == 'Y') {
                                $checked = 'checked';
                            } else {
                                $checked = '';
                            }
                            ?>
                            <table class="table table-striped planilha">
                                <thead>
                                    <tr>
                                        <th>NF</th>
                                        <th><input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> name="marcartodos" title="Marcar/Desmarcar Todos" type="checkbox" id="<?= $_nf['idnf'] ?>" <?= $checked ?> onclick="checkall(<?= $_nf['idnf'] ?>,this)" <?= $nfdesabled ?>></th>
                                        <th>Qtd Sol</th>
                                        <th style="width: 65px;">Sug Com</th>
                                        <th>Un</th>
                                        <th>Descrição</th>
                                        <th class="nowrap">Categoria</th>
                                        <? if ($taxaConversao == TRUE) { ?>
                                            <th class="nowrap" title="Taxa de Conversão">Tx Cv</th>
                                        <? } ?>
                                        <th style="text-align: -webkit-center;">Valor Un</th>
                                        <th>Desc Un</th>
                                        <th>ICMS ST</th>
                                        <th>IPI %</th>
                                        <th>Total</th>
                                        <th>Validade</th>
                                        <th>Prev Entrega</th>
                                        <th>Obs</th>
                                        <th colspan="4"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?
                                    $total = 0;
                                    $desconto = 0;
                                    $totalsemdesc = 0;
                                    $moeda = "";
                                    $iobs = $i;
                                    $first = '';
                                    $infe = 0;
                                    $qtdItens = 1;
                                    $qtdnfitem = COUNT($infoNf['nfitens'][$_nf['idnf']]);
                                    $itemfor = 0;
                                    foreach ($infoNf['nfitens'][$_nf['idnf']] as $_itens) {
                                        $i = $i + 1;
                                        $itemfor++;
                                        if (!empty($first) and !empty($_itens['idprodserv']) and empty($second)) {
                                            $second = 1; ?>
                                            <tr>
                                                <td colspan="20">
                                                    <hr>
                                                </td>
                                            </tr>
                                        <?
                                        }

                                        if (empty($_itens['idprodserv'])) {
                                            $first = 1;
                                        }

                                        if (in_array('Y', $infoNf['nfitens']['itenssolcom'][$_itens['idprodserv']]['urgencia'])) {
                                            $corSolcom  = "style='background-color: mistyrose'";
                                        } else {
                                            $corSolcom = "";
                                        }
                                        ?>
                                        <tr <?= $corSolcom ?>>
                                            <?
                                            //Esconde os itens que não estão checados                                
                                            if (in_array($_nf['status'], array('APROVADO', 'PREVISAO', 'DIVERGENCIA', 'CONCLUIDO', 'CONFERIDO')) && $_itens['nfe'] == 'N') {
                                                if ($infe == 0) {
                                            ?>
                                        <tr>
                                            <td colspan="20" style="height:40px;" data-toggle="collapse" href="#itemnf<?= $_itens['idnf'] ?>" aria-expanded="false" class="collapsed">
                                                Itens Não Selecionados
                                                <i class="fa fa-arrows-v cinzaclaro pointer cotacao_todos_item" title="Produto"></i>
                                            </td>
                                        </tr>
                                        <tr class="collapse" id="itemnf<?= $_itens['idnf'] ?>">
                                            <td colspan="20">
                                                <table style="width: 100%;">
                                            <?
                                                }
                                                $infe++;
                                            }
                                            ?>
                                            <td><?= $itemfor ?></td>
                                            <td>
                                                <?
                                                if ($_itens["nfe"] == 'Y') {
                                                    $checked = 'checked';
                                                    $vchecked = 'N';
                                                    if ($_itens['moeda'] == "BRL") {
                                                        $totalsemdesc += $_itens['total'] + $_itens['valipi'] + ($_itens['des'] * $_itens['qtd']);
                                                        $total = $total + $_itens['total'] + $_itens['valipi'];
                                                        $desconto += $_itens['des'] * $_itens['qtd'];
                                                        $moeda = $_itens['moeda'];
                                                    } else {
                                                        $total = $total + $_itens['totalext'];
                                                        $moeda = $_itens['moeda'];
                                                    }
                                                } else {
                                                    $checked = '';
                                                    $vchecked = 'Y';
                                                    if ($_itens['moeda'] == "BRL") {

                                                        $moeda = $_itens['moeda'];
                                                    } else {

                                                        $moeda = $_itens['moeda'];
                                                    }
                                                }
                                                ?>
                                                <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> title="Nfe" type="checkbox" <?= $nfdesabled ?> <?= $checked ?> name="namenfec" class="<?= $_nf['idnf'] ?>" id="<?= $_itens["idnfitem"] ?>" onclick="alterarCamposNf(<?= $_itens['idnfitem'] ?>, 'nfitem', 'nfe', '<?= $vchecked ?>',this, <?= $_nf['idnf'] ?>)">
                                            </td>
                                            <!-- Qtd Sol -->
                                            <td>
                                                <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> name="_<?= $i ?>_<?= $_acao ?>_nfitem_idnfitem" type="hidden" value="<?= $_itens['idnfitem'] ?>">
                                                <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> name="_<?= $i ?>_<?= $_acao ?>_nfitem_tiponf" type="hidden" value="C">
                                                <input style="text-align: right; background: #fff;" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> class="size5" name="_<?= $i ?>_<?= $_acao ?>_nfitem_qtdsol" onchange="atualizarQtd(this, <?= $_itens['idnfitem'] ?>)" id="qtdsol<?= $_itens['idnfitem'] ?>" type="text" value="<?= $_itens['qtdsol'] ?>">
                                            </td>

                                            <!-- Sug Com -->
                                            <td align="right">
                                                <?
                                                if ($_itens['converteest'] == "Y") {
                                                    $sugestaocompra2 = $_itens['sugestaocompra2'] / $_itens['valconv'];
                                                } else {
                                                    $sugestaocompra2 = $_itens['sugestaocompra2'];
                                                }
                                                ?>
                                                <label class="idbox" style="padding: 5px;"><?= number_format(tratanumero($sugestaocompra2), 2, ',', '.') ?></label>
                                            </td>

                                            <!-- Un -->
                                            <td>
                                                <?
                                                if (empty($_itens["unidade"])) {
                                                    if (empty($_itens['idprodserv']) && $_nf['status'] == 'INICIO') {
                                                ?>
                                                        <select setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> name="_<?= $i ?>_<?= $_acao ?>_nfitem_un">
                                                            <option value=""></option>
                                                            <? fillselect($fillSelectUnidadeVolume, $_itens["unidade"]); ?>
                                                        </select>
                                                <?
                                                    } else {
                                                        $unidade = $_itens["unidade"];
                                                    }
                                                } else {
                                                    $unidade = $_itens["unidade"];
                                                }
                                                ?>
                                                <a class="cinza hoverazul pointer modalProdServ" idprodserv="<?= $_itens['idprodserv'] ?>" modulo="prodservfornecedor">
                                                    <?= $unidade ?>
                                                </a>
                                            </td>

                                            <!-- Descrição -->
                                            <td>
                                                <? if (!empty($_itens['idprodserv'])) {
                                                    if (empty($_itens["codforn"])) {
                                                        $descrProduto = $_itens["descr"];
                                                    } else {
                                                        $descrProduto = $_itens["codforn"];
                                                    }
                                                ?>
                                                    <a class="hoverazul pointer modalProdServ" title="ID: <?= $_itens['idprodserv'] ?>-<?= $_itens['tipoprodserv'] ?>" idprodserv="<?= $_itens['idprodserv'] ?>" modulo="calculosestoque">
                                                        <?= $descrProduto ?>
                                                    </a>
                                                <? } else {
                                                    $descrProduto = $_itens["prodservdescr"];
                                                ?>
                                                    <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> class="size20" name="_<?= $i ?>_<?= $_acao ?>_nfitem_prodservdescr" type="text" value="<?= $_itens['prodservdescr'] ?>" onchange="salvarDescr(this,<?= $_itens['idnfitem'] ?>)">
                                                    <?
                                                }
                                                //Mostra o Vínculo com Solcom
                                                if (!empty($infoNf['nfitens']['itenssolcom'][$_itens['idprodserv']])) {
                                                    foreach ($infoNf['nfitens']['itenssolcom'][$_itens['idprodserv']] as $chave => $_idsolcom) {
                                                        if ($chave != 'urgencia') {
                                                    ?>
                                                            <label class="idbox" style="margin-right: 5px; padding: 5px;">
                                                                <a title="Solicitação Compras" class="fade pointer hoverazul solcomvalida" idprodserv="<?= $_itens['idprodserv'] ?>" idnf="<?= $_itens['idnf'] ?>" idnfitem="<?= $_itens['idnfitem'] ?>" descr="<?= $descrProduto ?>" idsolcom="<?= $_idsolcom ?>" nfe="<?= $_itens["nfe"] ?>" href="?_modulo=solcom&_acao=u&idsolcom=<?= $_idsolcom ?>" target="_blank">
                                                                    <?= $_idsolcom ?>
                                                                </a>
                                                            </label>
                                                <?
                                                        }
                                                    }
                                                }
                                                ?>
                                            </td>

                                            <!-- Categoria -->
                                            <td align="center">
                                                <?
                                                if (!empty($_itens['idcontaitem'])) {
                                                    $idcontaitem = $_itens['idcontaitem'];
                                                } elseif (!empty($_itens['idprodserv'])) {

                                                    if (count($infoNf['nfitens']['fillSelectContaItemProdserv'][$_itens['idprodserv']]) == 1) {
                                                        foreach ($infoNf['nfitens']['fillSelectContaItemProdserv'][$_itens['idprodserv']] as $key => $_idcontaitem) {
                                                            $idcontaitem = $key;
                                                        }
                                                    }
                                                } else {
                                                    $idcontaitem = "";
                                                }
                                                ?>
                                                <div id="tb_<?= $_itens["idnfitem"] ?>" class="grupo_es_oculto" style="display: none;">
                                                    <table style="width: 100%">
                                                        <tr style="padding: 15px;">
                                                            <th>Categoria</th>
                                                            <th></th>
                                                            <th>Tipo</th>
                                                        </tr>
                                                        <tr>
                                                            <td style="width: 45%;" class="cp_grupoes">
                                                                <? if (empty($_itens['idprodserv'])) {
                                                                ?>
                                                                    <input type="hidden" nomodal id="iidcontaitem<?= $_itens["idnfitem"] ?>" name="_<?= $i ?>_u_nfitem_idcontaitem" value="<?= $_itens['idcontaitem'] ?>" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?>>
                                                                    <select id="idcontaitem<?= $_itens["idnfitem"] ?>" name="" class='size25' setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?> <?= $vnulo ?> onchange="alterarContaItem(this, <?= $_itens['idnfitem'] ?>)">
                                                                        <option value=""></option>
                                                                        <? fillselect($fillSelectContaItem, $idcontaitem) ?>
                                                                    </select>
                                                                <?
                                                                } elseif (!empty($_itens['idcontaitem'])) {
                                                                    echo $infoNf['nfitens']['traduzirContaItem'][$_itens['idcontaitem']];
                                                                ?>
                                                                    <input type="hidden" nomodal id="iidcontaitem<?= $_itens["idnfitem"] ?>" name="_<?= $i ?>_u_nfitem_idcontaitem" value="<?= $idcontaitem ?>" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?>>
                                                                <?
                                                                } else {
                                                                ?>
                                                                    <input type="hidden" nomodal id="iidcontaitem<?= $_itens["idnfitem"] ?>" name="_<?= $i ?>_u_nfitem_idcontaitem" value="<?= $_itens['idcontaitem'] ?>" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?>>
                                                                    <select id="idcontaitem<?= $_itens["idnfitem"] ?>" name="" class="size20" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?> <?= $vnulo ?>>
                                                                        <option value=""></option>
                                                                        <? fillselect($infoNf['nfitens']['fillSelectContaItemProdserv'][$_itens['idprodserv']], $idcontaitem); ?>
                                                                    </select>
                                                                <?
                                                                }
                                                                ?>
                                                            </td>
                                                            <td style="width: 10%;"></td>
                                                            <td style="width: 45%;" id="td<?= $row["idnfitem"] ?>" class="cp_tipo 1">
                                                                <?
                                                                if (!empty($_itens['idprodserv']) && !empty($_itens['idtipoprodserv'])) {
                                                                ?>
                                                                    <input type="hidden" nomodal name="_<?= $i ?>_u_nfitem_idtipoprodserv" value="<?= $_itens['idtipoprodserv'] ?>" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?>>
                                                                <?
                                                                    echo $_itens['tipoprodserv'];
                                                                } else {
                                                                    if ($_nf['status'] == 'CONCLUIDO') {
                                                                        $arrIdTipoProdserv = $fillSelectTipoProdserv;
                                                                    } elseif ($_itens['idcontaitem']) {
                                                                        $arrIdTipoProdserv = $infoNf['nfitens']['fillSelectTipoProdservIdContaItem'][$_itens['idcontaitem']];
                                                                    } else {
                                                                        if (!empty($_itens['idprodserv'])) {
                                                                            $arrIdTipoProdserv = $infoNf['nfitens']['fillSelectContaItemProdserv'][$_itens['idprodserv']];
                                                                        } else {
                                                                            $arrIdTipoProdserv = array('' => '');
                                                                        }
                                                                    }
                                                                ?>
                                                                    <input type="hidden" nomodal id="iidtipoprodserv<?= $row["idnfitem"] ?>" name="_<?= $i ?>_u_nfitem_idtipoprodserv" value="<?= $_itens['idtipoprodserv'] ?>" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?>>
                                                                    <select id="idtipoprodserv<?= $_itens["idnfitem"] ?>" name="" style="width: 100%" vnulo setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?>>
                                                                        <option value=""></option>
                                                                        <? fillselect($arrIdTipoProdserv, $_itens['idtipoprodserv']); ?>
                                                                    </select>
                                                                <?
                                                                } ?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>

                                                <? if (empty($_itens['idcontaitem'])) { ?>
                                                    <i class="btn fa fa-info-circle laranja" title="Categoria e/ou Subcategoria não Atribuídas" id="btn_<?= $_itens["idnfitem"] ?>" onclick="mostrarModalGrupoES(<?= $_itens['idnfitem'] ?>,'<?= addslashes($descrProduto) ?>',<?= $i ?>)"></i>
                                                <? } else { ?>
                                                    <i class="btn fa fa-info-circle" id="btn_<?= $_itens["idnfitem"] ?>" onclick="mostrarModalGrupoES(<?= $_itens['idnfitem'] ?>,'<?= addslashes($descrProduto) ?>',<?= $i ?>)"></i>
                                                <? } ?>
                                            </td>

                                            <!-- Tx Cv -->
                                            <?
                                            if (!empty($_itens['moedaext']) && $_itens['moedaext'] != "BRL") {
                                            ?>
                                                <td class="nowrap">
                                                    <label class="alert-warning">
                                                        <? echo ($_itens['moedaext']); ?>
                                                    </label>
                                                    <?
                                                    if ($_itens['moeda'] == "BRL") {
                                                    ?>
                                                        <input setdisable<?= $_itens['idnf'] ?> <?= $infoStatus ?> vnulo <?= $nfreadonly ?> style="width: 60px;" title="Câmbio BRL" placeholder="Câmbio" name="_<?= $i ?>_<?= $_acao ?>_nfitem_convmoeda" type="text" value="<?= $_itens['convmoeda'] ?>" onkeyup="setvalnfitem(this, <?= $_itens['idnfitem'] ?>, <?= $_itens['vlritemext'] ?>)">
                                                    <?
                                                    }
                                                    ?>
                                                </td>
                                            <?
                                            } elseif ($taxaConversao == TRUE) { ?>
                                                <td></td> <?
                                                        } ?>

                                            <!-- Valor Un -->
                                            <td class="nowrap">
                                                <?
                                                if ($_itens['vlritemext'] > 1) {
                                                    if ($_itens['vlritem'] < 1 || empty($_itens['convmoeda'])) {
                                                        $cbt = "btn-danger";
                                                        $_vlritem = 0;
                                                    } else {
                                                        $cbt = "btn-primary ";
                                                        $_vlritem = $_itens['vlritem'];
                                                    }
                                                } else {
                                                    $cbt = "btn-success";
                                                    $_vlritem = $_itens['vlritem'];
                                                }
                                                ?>
                                                <button setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?> title="Moeda" moeda="<?= $_itens['moeda'] ?>" type="button" class="btn <?= $cbt ?>  btn-xs pointer" onclick="alterarMoeda(this,<?= $_itens['idnfitem'] ?>,'<?= $_itens['moedaext'] ?>')">
                                                    <?= $_itens['moeda'] ?>
                                                </button>
                                                <? if ($_itens['moeda'] == "BRL") { ?>
                                                    <input style="text-align: right; width: 80px; background: #fff" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> onchange="alterarCamposNf(<?= $_itens['idnfitem'] ?>,'nfitem','vlritem','<?= $vchecked ?>',this, '<?= $_nf['idnf'] ?>')" id="nfitem<?= $_itens['idnfitem'] ?>" name="_<?= $i ?>_<?= $_acao ?>_nfitem_vlritem" type="text" value="<?= $_vlritem ?>">
                                                <?
                                                } else {
                                                ?>
                                                    <input style="text-align: right;" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> name="_<?= $i ?>_<?= $_acao ?>_nfitem_moedaext" type="hidden" value="<?= $_itens['moeda'] ?>">
                                                    <input style="text-align: right; width: 80px; background: #fff" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> name="_<?= $i ?>_<?= $_acao ?>_nfitem_vlritemext" type="text" value="<?= $_itens['vlritemext'] ?>">
                                                <? } ?>
                                            </td>

                                            <!-- Desc Un -->
                                            <td><input style="text-align: right; background: #fff" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> class="size6" name="_<?= $i ?>_<?= $_acao ?>_nfitem_des" id="des<?= $_itens['idnfitem'] ?>" onchange="alterarCamposNf(<?= $_itens['idnfitem'] ?>, 'nfitem', 'des', '<?= $vchecked ?>', this, '<?= $_nf['idnf'] ?>')" type="text" value="<?= $_itens['des'] ?>"></td>

                                            <!-- ICMS ST -->
                                            <td><input style="text-align: right; background: #fff" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> class="size5" name="_<?= $i ?>_<?= $_acao ?>_nfitem_vst" onchange="alterarCamposNf(<?= $_itens['idnfitem'] ?>, 'nfitem', 'vst', '<?= $vchecked ?>', this, '<?= $_nf['idnf'] ?>')" type="text" value="<?= $_itens['vst'] ?>"></td>

                                            <!-- IPI % -->
                                            <td><input style="text-align: right; background: #fff" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> class="size5" id="ipi<?= $_itens['idnfitem'] ?>" name="_<?= $i ?>_<?= $_acao ?>_nfitem_aliqipi" onchange="alterarCamposNf(<?= $_itens['idnfitem'] ?>, 'nfitem', 'aliqipi', '<?= $vchecked ?>', this, '<?= $_nf['idnf'] ?>')" type="text" value="<?= $_itens['aliqipi'] ?>" title="<?= number_format(tratanumero($_itens['valipi']), 2, ',', '.') ?>"></td>

                                            <!-- Total -->
                                            <td align="right">
                                                <span id="totalext<?= $_itens['idnfitem'] ?>">
                                                    <?
                                                    if ($_itens['moeda'] == "BRL") {
                                                        echo number_format(tratanumero($_itens['total'] + $_itens['valipi']), 2, ',', '.');
                                                    } else {
                                                        echo $_itens['totalext'] + $_itens['valipi'];
                                                    }
                                                    ?>
                                                </span>
                                            </td>

                                            <!-- Validade -->
                                            <td><input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> class="calendario size7" style="width: 100px; background: #fff" name="_<?= $i ?>_<?= $_acao ?>_nfitem_validade" _idnfitem="<?= $_itens["idnfitem"] ?>" _idnf="<?= $_nf["idnf"] ?>" type="text" value="<?= dma($_itens['validade']) ?>"></td>

                                            <!-- Prev Entrega -->
                                            <td>
                                                <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> id="nfitem_previsaoentrega<?= $_itens["idnfitem"] ?>" <?= $nfdesabled ?> name="_<?= $i ?>_<?= $_acao ?>_nfitem_previsaoentrega" _idnfitem="<?= $_itens["idnfitem"] ?>" _idnf="<?= $_nf["idnf"] ?>" class="calendario size7" style="width: 100px; background: #fff" type="text" value="<?= dma($_itens['previsaoentrega']) ?>">
                                            </td>

                                            <!-- Obs -->
                                            <td title="<?= $_itens['obs'] ?>">
                                                <?
                                                if ($_itens['obs']) {
                                                    $corIObsv = "azul";
                                                } else {
                                                    $corIObsv = "cinza";
                                                }
                                                ?>

                                                <i title="Observação" class="fa btn-sm fa-info-circle <?= $corIObsv ?> pointer hoverazul tip modalObservacaoClique" idnfitem="<?= $_itens['idnfitem'] ?>"></i>

                                                <div class="panel panel-default" hidden>
                                                    <div id="modalObservacao<?= $_itens['idnfitem'] ?>" class="panel-body">
                                                        <div class="row" style="width: 100%;">
                                                            <div class="col-md-2 head" style="color:#333; text-align: right;">Observação:</div>
                                                            <div class="col-md-10">
                                                                <textarea setdisable<?= $_nf['idnf'] ?> onkeyup="atualizarCampo(this, '<?= $_itens['idnfitem'] ?>')" <?= $infoStatus ?> <?= $nfreadonly ?> style="height: 80px;" name="_<?= $i ?>_u_nfitem_obs" id="_<?= $_itens['idnfitem'] ?>_nfitem_obs" type="text"><?= $_itens['obs'] ?></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td title="Compra Atual">
                                                <?
                                                if ($_itens["vlr"] > 0) { ?>
                                                    <a class="pointer hoverazul" title="Compra Atual" style="float: right;"><?= number_format(tratanumero($_itens['vlr']), 2, ',', '.') ?> </a>
                                                <? }
                                                ?>
                                            </td>
                                            <td title="Última Compra" align="right">
                                                <?
                                                if (!empty($_itens['ultimacompra'])) {
                                                    $dadosUltimaCompra = explode('#', $_itens['ultimacompra']);
                                                ?>
                                                    <a class="pointer hoverazul" title="Última Compra" style="color:green;" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?= $dadosUltimaCompra[0] ?>')"><?= number_format(tratanumero($dadosUltimaCompra[1]), 2, ',', '.') ?> </a>
                                                <? }


                                                ?>
                                            </td>
                                            <td>
                                                <div class="row nowrap">
                                                    <div class="col-md-6">
                                                        <?
                                                        if (!empty($_itens['idprodserv'])) { ?>
                                                            <div class="historicocompras" idnfitem="<?= $_itens["idnfitem"] ?>" idprodserv="<?= $_itens['idprodserv'] ?>" id="historicocompras<?= $_itens["idnfitem"] ?>">
                                                                <a class="fa fa-1x fa-info-circle btn-lg  azul pointer hoverazul" title="Histórico de compras em todas as empresas" data-target="webuiPopover0"></a>
                                                            </div>
                                                            <div class="webui-popover-content" id="target-<?= $_itens["idnfitem"] ?>"></div>
                                                        <?
                                                        } //if(!empty($_itens['idprodserv'])){
                                                        ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <?
                                                        if (!empty($_itens['idprodserv'])) {
                                                        ?>
                                                            <div class="itensSemelhantes" idnfitem="<?= $_itens["idnfitem"] ?>" id="semelhantes<?= $_itens["idnfitem"] ?>">
                                                                <a class="fa fa-1x fa-search btn-lg azul pointer hoverazul" title="Ver Cotações" data-target="webuiPopover0"></a>
                                                            </div>
                                                            <div class="webui-popover-content">
                                                                <br />
                                                                <table class="table table-striped planilha">
                                                                    <tr>
                                                                        <th></th>
                                                                        <th>Cotação</th>
                                                                        <th>Valor Item</th>
                                                                        <th>Fornecedor</th>
                                                                        <th>Status</th>
                                                                    </tr>
                                                                    <?
                                                                    foreach ($infoNf['nfitens']['semelhantes'][$_itens['idprodserv']] as $_semelhantes) {
                                                                        if ($_semelhantes["nfe"] == 'Y') {
                                                                            $checked = 'checked';
                                                                            $vchecked = 'N';
                                                                        } else {
                                                                            $checked = '';
                                                                            $vchecked = 'Y';
                                                                        }
                                                                    ?>
                                                                        <tr>
                                                                            <td><input setdisable<?= $_semelhantes['idnf'] ?> title="Nfe" type="checkbox" <?= $checked ?> class="<?= $_semelhantes['idnf'] ?> inputsemelhante" id="<?= $_semelhantes["idnfitem"] ?>" onclick="alterarCamposNf(<?= $_semelhantes['idnfitem'] ?>, 'nfitem', 'nfe', '<?= $vchecked ?>', this, <?= $_nf['idnf'] ?>)"></td>
                                                                            <td><a href="#nftable<?= $_semelhantes["idnf"] ?>"><?= $_semelhantes['idnf'] ?></a></td>
                                                                            <td><b>R$ <?= number_format(tratanumero($_semelhantes['vlritem']), 2, ',', '.') ?></b></td>
                                                                            <td><?= $_semelhantes['nome'] ?></td>
                                                                            <td><?= strtoupper($_semelhantes['rotulo']) ?></td>
                                                                        </tr>
                                                                    <?
                                                                    }
                                                                    ?>
                                                                </table>
                                                            </div>


                                                        <? } ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <? if (empty($nfreadonly)) { ?>
                                                    <a class="fa fa-download verde pointer btn-lg hoverazul" id="btdelocaitem<?= $_itens["idnfitem"] ?>" title="Deslocar item" onclick="deslocar(<?= $_itens['idnfitem'] ?>,this)"></a>
                                                <? } ?>
                                            </td>
                                            <td>
                                                <? if (empty($nfreadonly) && empty($_itens['idlote'])) { ?>
                                                    <a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluirItem(<?= $_itens['idnfitem'] ?>)" title="Excluir Item!"></a>
                                                <? } ?>
                                            </td>
                                        </tr>
                                        <?
                                        if ($qtdnfitem == $qtdItens && in_array($_nf['status'], array('APROVADO', 'PREVISAO', 'DIVERGENCIA', 'CONCLUIDO', 'CONFERIDO')) && $_itens['nfe'] == 'N') {
                                        ?>
                                            </tr>
                                            </td>
                            </table>
                    <? }
                                        $qtdItens++;
                                    }
                    ?>
                    <tr>
                        <td colspan="19">
                            <table class="adicionarNovoItem<?= $_nf['idnf'] ?>">
                                <tr class="trNovoItem"></tr>
                            </table>
                        </td>
                    </tr>
                    <?
                    if (empty($nfreadonly)) {
                    ?>
                        <tr class="hidden" id="modeloNovoItem<?= $_nf['idnf'] ?>">
                            <td></td>
                            <td colspan="2">
                                <input type="text" size="60" class="ui-autocomplete-input autocompletenovoitem" idnf="<?= $_nf['idnf'] ?>" idpessoa="<?= $_nf['idpessoa'] ?>" placeholder="**Novo item**" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?>>
                            </td>
                            <td colspan="13"></td>
                        </tr>

                        <tr class="esconderdiv">
                            <td colspan="8">
                                <i id="novoitem" class="fa fa-plus-circle fa-1x verde btn-lg pointer" onclick="inserirNovoItem(<?= $_nf['idnf'] ?>)" title="Inserir novo Item"></i>
                            </td>
                            <td colspan="8"></td>
                        </tr>
                    <? } ?>
                    <tr class="esconderdiv">
                        <?
                        if ($taxaConversao == TRUE) {
                            $colspanmodfrete = 9;
                            $colspanfrete = 2;
                        } else {
                            $colspanmodfrete = 8;
                            $colspanfrete = 2;
                        }
                        ?>

                        <td colspan="<?= $colspanmodfrete ?>"></td>
                        <td title="<?= CotacaoController::$tituloFrete ?>" align="right" colspan="<?= $colspanfrete ?>">
                            Frete:
                            <select setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> class="size6" name="_<?= $iobs ?>_<?= $_acao ?>_nf_modfrete" <?= $nfdesabled ?>>
                                <? fillselect(CotacaoController::$tipoFrete, $_nf['modfrete']); ?>
                            </select>

                            <?
                            if ($_nf['modfrete'] == '1') {
                                if (!empty($_nf['idnfe'])) {
                                    $cte = CotacaoController::buscarCtePorIdNfe($_nf['idnfe'], $_nf['idnf']);
                                } else {
                                    $cte = CotacaoController::buscarCte($_nf['idnf']);
                                }

                                if (count($cte) > 0) {
                            ?>
                                    <a title="CTE" class="fa fa-bars preto pointer hoverazul" href="?_modulo=nfcte&_acao=u&idnf=<?= $cte[0]['idnf'] ?>" target="_blank"></a>
                                <?
                                } elseif ($_nf['status'] == "APROVADO" || $_nf['status'] == "PREVISAO") {
                                ?>
                                    <i class="fa fa-plus-circle fa-1x verde  pointer" onclick="inserirNovoCte(<?= $_nf['idnf'] ?>)" title="Gerar Programação de CTe "></i>
                            <?
                                }
                            }
                            ?>
                        </td>
                        <?
                        if (empty($_nf['frete'])) {
                            $frete = 0.00;
                        } else {
                            $frete = $_nf['frete'];
                        }
                        ?>
                        <td>
                            <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> style="text-align-last: end;" name="_<?= $iobs ?>_<?= $_acao ?>_nf_frete" size="8" value="<?= number_format(tratanumero($frete), 2, ',', '.'); ?>" vdecimal onchange="atualizarFrete(this,<?= $_nf['idnf'] ?>)">
                        </td>
                        <td colspan="7"></td>
                    </tr>
                    <tr class="esconderdiv" style="background-color: #FFFFFF;">
                        <? if ($taxaConversao == TRUE) { ?>
                            <td align="right" colspan="11">Subtotal: <b><?= $moeda ?> </b></td>
                        <? } else { ?>
                            <td align="right" colspan="10">Subtotal: <b><?= $moeda ?> </b></td>
                        <? } ?>
                        <td align="right">
                            <b id="totalsemdesc<?= $_itens['idnf'] ?>">
                                <?= number_format(tratanumero($totalsemdesc), 2, ',', '.'); ?>
                            </b>
                        </td>
                        <td colspan="10"></td>
                    </tr>
                    <tr class="esconderdiv" style="background-color: #FFFFFF;">
                        <? if ($taxaConversao == TRUE) { ?>
                            <td align="right" colspan="11">Desconto: <b><?= $moeda ?> </b></td>
                        <? } else { ?>
                            <td align="right" colspan="10">Desconto: <b><?= $moeda ?> </b></td>
                        <? } ?>
                        <td align="right">
                            <b id="desconto<?= $_itens['idnf'] ?>">
                                <?= number_format(tratanumero($desconto), 2, ',', '.'); ?>
                            </b>
                        </td>
                        <td colspan="10"></td>
                    </tr>

                    <tr class="esconderdiv" style="background-color: #FFFFFF;">
                        <? if ($taxaConversao == TRUE) { ?>
                            <td align="right" colspan="11">Total: <b><?= $moeda ?> </b></td>
                        <? } else { ?>
                            <td align="right" colspan="10">Total: <b><?= $moeda ?> </b></td>
                        <? } ?>
                        <td align="right">
                            <b id="totalcomdesc<?= $_itens['idnf'] ?>">
                                <? $vtotal =  $total + tratanumero($_nf['frete']); ?>
                                <?= number_format(tratanumero($vtotal), 2, ',', '.'); ?>
                            </b>
                            <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> id="totalNf<?= $_nf['idnf'] ?>" name="_<?= $iobs ?>_<?= $_acao ?>_nf_total" type="hidden" value="<?= $vtotal ?>" vdecimal>
                        </td>
                        <td colspan="10"></td>
                    </tr>
                    </tbody>
                    </table>
                    <div class="row esconderdiv">
                        <div class="col-md-5">
                            <div class="">
                                <div class="" href="#transporte<?= $iobs ?>"></div>
                                <div>
                                    <table id="Pagamento<?= $iobs ?>">
                                        <? if ($_nf['formapgto']) { ?>
                                            <tr>
                                                <td class="nowrap">Pag. Fornecedor:<span style="color: red;"><?= $_nf['formapgto'] ?></span></td>
                                            </tr>
                                        <? } ?>
                                        <? if ($_nf['parcelas'] > 1) {
                                            $strdivtab = "style='display:block;'";
                                        } else {
                                            $strdivtab = "style='display:none;'";
                                        } ?>
                                        <tr>
                                            <td>Pagamento:</td>
                                            <td>1º Venc. em dias:</td>
                                            <td>Parcelas:</td>
                                            <td>
                                                <div class="divtab intervaloClass<?= $_nf['idnf'] ?>" <?= $strdivtab ?> id="divtab1">Intervalo Par. em dias:</div>
                                            </td>
                                            <td>Observação Cotação:</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> cbvalue="<?= $_nf['idformapagamento'] ?>" class="size30 forma_pagamento" id="formapagamento<?= $_nf['idnf'] ?>" name="_<?= $iobs ?>_<?= $_acao ?>_nf_idformapagamento" <?= $nfdesabled ?> value="<?= $jPag[$_nf['idformapagamento']]['descricao'] ?>">
                                            </td>

                                            <td><input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> class="size10 diasentrada<?= $_nf['idnf'] ?>" name="_<?= $iobs ?>_<?= $_acao ?>_nf_diasentrada" size="2" type="number" value="<?= $_nf['diasentrada'] ?>" vdecimal <?= $nfreadonly ?> onchange="atualizarParcelas(this, <?= $_nf['idnf'] ?>)"></td>

                                            <td>
                                                <select setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> class="size10 parcelas<?= $_nf['idnf'] ?>" id="parcelas" name="_<?= $iobs ?>_<?= $_acao ?>_nf_parcelas" <?= $nfdesabled ?> onchange="atualizarParcelas(this, <?= $_nf['idnf'] ?>)">
                                                    <option value=""></option>
                                                    <?
                                                    for ($isel = 1; $isel <= 60; $isel++) {
                                                        if ($isel == 1) {
                                                            $arrayParcelas[$isel] = $isel . "x";
                                                        } else {
                                                            $arrayParcelas[$isel] = $isel . "x";
                                                        }
                                                    }
                                                    fillselect($arrayParcelas, $_nf['parcelas']);
                                                    ?>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="divtab nowrap intervaloClass<?= $_nf['idnf'] ?>" <?= $strdivtab ?> id="divtab2">
                                                    <input class="intervaloant<?= $_nf['idnf'] ?>" type="hidden" value="<?= $_nf['intervalo'] ?>">
                                                    <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?> class="size10 intervalo<?= $_nf['idnf'] ?>" name="_<?= $iobs ?>_<?= $_acao ?>_nf_intervalo" type="text" value="<?= $_nf['intervalo'] ?>" vdecimal onchange="atualizarParcelas(this, <?= $_nf['idnf'] ?>)">
                                                </div>
                                            </td>
                                            <td><textarea class="size50" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?> name="_<?= $iobs ?>_<?= $_acao ?>_nf_obs"><?= $_nf['obs'] ?></textarea></td>
                                        </tr>
                                        <tr id="transporte<?= $iobs ?>">
                                            <td colspan="4">Transportadora:</td>
                                            <td>Observação Interna:</td>
                                        </tr>
                                        <tr>
                                            <td class="nowrap" colspan="4">
                                                <select setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> class="size55" name="_<?= $iobs ?>_<?= $_acao ?>_nf_idtransportadora" <?= $nfdesabled ?>>
                                                    <option value=""></option>
                                                    <? fillselect($fillSelectTransportadora, $_nf['idtransportadora']); ?>
                                                </select>
                                                <? if (!empty($_nf['idtransportadora'])) { ?>
                                                    <a title="Transportadora" class="fa fa-bars preto pointer hoverazul" href="?_modulo=pessoa&_acao=u&idpessoa=<?= $_nf['idtransportadora'] ?>" target="_blank"></a>
                                                <? } ?>
                                            </td>
                                            <td><textarea class="size50" name="_<?= $iobs ?>_<?= $_acao ?>_nf_obsinterna"><?= $_nf['obsinterna'] ?></textarea></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="compra<?= $_nf['idnf'] ?>" style="display: none">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <table>
                                    <tr id="editforn">
                                        <td align="right">Fornecedor:</td>
                                        <td title="Pesquisar e inserir item exclusivo por fornecedor.">
                                            <div class="input-group">
                                                <input id="_f_nome<?= $_nf['idnf'] ?>" type="text" value="<?= $_nf['nome'] ?>" cbvalue="<?= $_nf['idpessoa'] ?>" name="x_f_nome" class="size12" disabled style="background-color: #e6e6e6; width:180px; ">
                                                <a id="editar_fornecedor" class="fa fa-pencil hoverazul btn-lg pointer" onclick="editarFornecedor();" title="Editar fornecedor"></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?
                                    $b = 0;
                                    foreach ($infoNf['nfitens'][$_nf['idnf']] as $_duplicarCompra) {
                                        $b = $b + 1;
                                    ?>
                                        <tr id="tr<?= $_duplicarCompra['idnfitem'] ?>">
                                            <td><?= $_duplicarCompra['qtdsol'] ?>
                                                <input id="quantidade<?= $_nf['idnf'] ?>" name="_<?= $b ?>__quantidade" type="hidden" style="width: 80px;" value="<?= $_duplicarCompra['qtdsol'] ?>">
                                                <input id="idnfitem<?= $_nf['idnf'] ?>" name="_<?= $b ?>__idnfitem" type="hidden" value="<?= $_duplicarCompra['idnfitem'] ?>">
                                            </td>
                                            <td colspan="2">
                                                <? if (!empty($rowiy['prodservdescr'])) { ?>
                                                    <?= $_duplicarCompra['prodservdescr'] ?>
                                                <? } else { ?>
                                                    <?= $_duplicarCompra['descr'] ?>
                                                <? } ?>
                                            </td>
                                            <td><i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluirItemDuplicarCompra(this)" alt="Excluir item!"></i></td>
                                        </tr>
                                    <? } ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?
        }

        if ($_acao == 'u') {
            //cancelador reprovados
            if ($controleCabecarioReprovadosCancelados) {
                $controleCabecarioReprovadosCancelados = FALSE; ?>
                <div class="row">
                    <div class=" col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading" style="background:#808080; height: 33px;">
                                <div class="col-xs-10 canceladoreprovado" style="color: #FFFFFF; font-size:medium;" onclick="mostrainfo('cotacao')">
                                    <div><i class="fa fa-chevron-down branco pointer" title="Expandir" id="expandCancelado"> </i> Reprovados e Cancelados (<?= $countCancelado++ ?>)</div>
                                </div>
                                <div class="col-xs-2" style="text-align:right;"><i class="fa fa-arrows-v fa-2x azul pointer expandirtodos" title="Expandir todos" style="font-size: medium; text-align: right; color: #FFFFFF;" onclick="abrirtudo(this,'cancelado')"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="retornacancelados"></div>
            <?
            }
            ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="height: 30px;">
                            Dados adicionais
                        </div>
                        <div class="panel-body">
                            <div class="col-md-6">
                                <span class="pointer azul" title="Visualizar Origem Solicitação de Orçamento" onclick="mostrainfo('orcamento')"> Origem Solicitação de Orçamento</span>
                            </div>
                            <div id="canceladosreprovados">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 px-0">
                    <div class="panel panel-default">
                        <div class="panel-heading" data-toggle="collapse" href="#gpArqAnexos">Arquivos Anexos</div>
                        <div class="panel-body collapse" id="gpArqAnexos" style="padding-top: 8px !important;">
                            <div class="cbupload" id="" title="Clique ou arraste arquivos para cá" style="width:100%;">
                                <i class="fa fa-cloud-upload fonte18"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="display: none">
                <div class="resandamento">
                    <div class="col-md-10">Em andamento (<?= $countAndamento++; ?>)</div>
                </div>
                <div class="resaprovado">
                    <div><i class="fa fa-chevron-up branco pointer" title="Expandir" id="expandAPROVADO"> </i> Aprovado (<?= $countAprovado++; ?>)</div>
                </div>
                <div class="resconcluido">
                    <div><i class="fa fa-chevron-down branco pointer" title="Expandir" id="expandCONCLUIDO"> </i> Concluido (<?= $countConcluido++; ?>)</div>
                </div>
            </div>
            <div class="xg-button-container">
                <button class="xg-custom-button" id="meuBotao">
                    Legenda
                    <div class="xg-tooltip">
                        <div class="legend-grid">
                            <div class="legend-section">
                                <h4>Em andamento</h4>
                                <div class="legend-item"><span class="xg-aberto"></span> Aberto</div>
                                <div class="legend-item"><span class="xg-enviado"></span> Enviado</div>
                                <div class="legend-item"><span class="xg-respondido"></span> Respondido</div>
                                <div class="legend-item"><span class="xg-autorizacao"></span> Autorização Diretoria, Em Aprovação</div>
                                <div class="legend-item"><span class="xg-programado"></span> Programado, Previsão</div>
                            </div>
                            <div class="legend-section">
                                <h4>Aprovados</h4>
                                <div class="legend-item"><span class="xg-aprovado"></span> Aprovado</div>
                                <div class="legend-item"><span class="xg-aprovado-entrega"></span> Aprovado (Entrega está atrasada)</div>
                                <div class="legend-item"><span class="xg-divergencia"></span> Divergência</div>
                            </div>
                            <div class="legend-section">
                                <h4>Concluídos</h4>
                                <div class="legend-item"><span class="xg-concluido"></span> Concluído</div>
                                <div class="legend-item"><span class="xg-conferido"></span> Conferido, Revisado, Início de Recebimento</div>
                            </div>
                            <div class="legend-section">
                                <h4>Reprovados e Cancelados</h4>
                                <div class="legend-item"><span class="xg-reprovado-cancelado"></span> Reprovado, Cancelado</div>
                            </div>
                        </div>
                    </div>
                </button>
            </div>
            <?
        }
    } else { // visualizacao por produto

        $prodservNf = CotacaoController::buscarProdservPelaNf($_1_u_cotacao_idcotacao);
        $qtdNf = count($prodservNf);
        $i = 1;
        $l = 0;

        if ($qtdNf > 0) {
            foreach ($prodservNf as $_nf) {
                $i = $i++;
                $l = $l++;

                $itensNf = CotacaoController::buscarItensNfPorIdProdserv($_1_u_cotacao_idcotacao, $_nf['idprodserv']);
            ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <?= traduzid("prodserv", "idprodserv", "codprodserv", $_nf['idprodserv']) ?>-<?= traduzid("prodserv", "idprodserv", "descr", $_nf['idprodserv']) ?>
                            </div>
                            <div class="panel-body">
                                <table class="table table-striped planilha">
                                    <tr>
                                        <th style="text-align: center;width: 2%;">NF.</th>
                                        <th style="text-align: center;width: 5%;">Cotação</th>
                                        <th style="text-align: center;width: 33%;">Fornecedor</th>
                                        <th style="text-align: center;width: 5%;">Qtd</th>
                                        <th style="text-align: center;width: 10%;">Valor Un.</th>
                                        <th style="text-align: center;width: 10%;">Total</th>
                                        <th style="text-align: center;width: 5%;">Previsão Entrega</th>
                                        <th style="text-align: center;width: 15%;">Obs.</th>
                                        <th style="text-align: center;">Status</th>
                                    </tr>
                                    <?
                                    $vtotalp = 0;
                                    foreach ($itensNf as $_itens) {
                                        if ($_itens['status'] == 'ENVIADO' || $_itens['status'] == 'APROVADO' || $_itens['status'] == 'PREVISAO') {
                                            $stcolor = 'background-color:#9DDBFF !important;';
                                        } else if ($_itens['status'] == 'RESPONDIDO') {
                                            $stcolor = 'background-color:#f6c23e !important;';
                                        } else if ($_itens['status'] == 'AUTORIZADO' || $_itens['status'] == 'AUTORIZADA' || $_itens['status'] == 'ABERTO') {
                                            $stcolor = 'background-color:mistyrose !important;';
                                        } else if ($_itens['status'] == 'CONCLUIDO') {
                                            $stcolor = 'background-color:#9dffb2 !important';
                                        } else {
                                            $stcolor = 'background-color:#e6e6e6 !important;';
                                        }
                                        $i = $i++;
                                        $vtotalp = $vtotalp + (!empty($_itens['total']) ? $_itens['total'] : $_itens['totalext']); ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <? if ($_itens['status'] != 'AUTORIZADO' && $_itens['status'] != 'AUTORIZADA' && $_itens['status'] != 'ABERTO' && $_itens['status'] != 'RESPONDIDO') {
                                                    $disabled = 'disabled';
                                                } else {
                                                    $disabled = '';
                                                }
                                                if ($_itens["nfe"] == 'Y') {
                                                    $checked = 'checked';
                                                    $vchecked = 'N';
                                                    $total = $total + $_itens['total'];
                                                } else {
                                                    $checked = '';
                                                    $vchecked = 'Y';
                                                } ?>
                                                <input <?= $disabled ?> title="Nfe" type="checkbox" <?= $nfdesabled ?> <?= $checked ?> name="namenfec" id="<?= $_itens["idnfitem"] ?>" onclick="alterarCamposNf(<?= $_itens['idnfitem'] ?>, 'nfitem', 'nfe', '<?= $vchecked ?>', this, <?= $_nf['idnf'] ?>)">

                                            </td>
                                            <td style="text-align: center;">
                                                <?= $_itens['idnf'] ?>
                                            </td>
                                            <td>
                                                <input name="_<?= $i ?>_<?= $_acao ?>_nfitem_idnfitem" type="hidden" value="<?= $_itens['idnfitem'] ?>">
                                                <input name="_<?= $i ?>_<?= $_acao ?>_nfitem_tiponf" type="hidden" value="C">
                                                <?= $_itens['nome'] ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <?= number_format(tratanumero($_itens["qtd"]), 2, ',', '.'); ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <?
                                                if ($_itens['vlritemext'] > 1) {
                                                    $_vlritem = $_itens['vlritemext'];
                                                } else {
                                                    $cbt = "btn-success";
                                                    $_vlritem = $_itens['vlritem'];
                                                }
                                                echo number_format(tratanumero($_vlritem), 2, ',', '.');
                                                ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <? if ($_itens['totalext'] > 1) {
                                                    $totalitem = $_itens['totalext'];
                                                } else {
                                                    $totalitem = $_itens['total'];
                                                } ?>
                                                <?= number_format(tratanumero($totalitem), 2, ',', '.'); ?></td>
                                            <td style="text-align: center;">
                                                <?= dma($_itens['previsaoentrega']) ?>
                                            </td>
                                            <td style="text-align: center;word-break: break-all;"><?= $_itens['obs'] ?></td>
                                            <td style="text-align: center;">
                                                <label style="color: black;<?= $stcolor ?>;border-color: black;font-size: 11px;padding: 3px 3px;border-radius: 3px;-webkit-box-shadow: 2px 2px 1px rgb(0 0 0 / 5%);box-shadow: 2px 2px 1px rgb(0 0 0 / 5%);">
                                                    <? if ($_itens['status'] == 'AUTORIZADO') {
                                                        $_itens['status'] = 'AUTORIZAÇÃO DIRETORIA';
                                                    } elseif ($_itens['status'] == 'AUTORIZADA') {
                                                        $_itens['status'] = 'EM APROVAÇÃO';
                                                    } elseif ($_itens['status'] == 'INICIO') {
                                                        $_itens['status'] = 'ABERTO';
                                                    } ?>
                                                    <?= $_itens['status'] ?>
                                                </label>
                                            </td>
                                        </tr>
                                    <? } //while($_itens=mysqli_fetch_assoc($resi)){
                                    ?>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td style="text-align: center;">Total:</td>
                                        <td style="text-align: center;"><b><?= number_format(tratanumero($vtotalp), 2, ',', '.'); ?></b></td>
                                        <td colspan="3"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
<?
            } //while($_nf=mysqli_fetch_assoc($resnf)){ 
        } //}else{// visualizacao por produto
    }
}

if (!empty($_1_u_cotacao_idcotacao)) { // trocar p/ cada tela a tabela e o id da tabela
    $_idModuloParaAssinatura = $_1_u_cotacao_idcotacao; // trocar p/ cada tela o id da tabela
    require '../form/viewAssinaturas.php';
}
$_disableDefaultDropzone = true; // desabilitar o dropzone padrão
$tabaud = "cotacao"; //pegar a tabela do criado/alterado em antigo
require '../form/viewCriadoAlterado.php';

require_once('../form/js/cotacao_js.php');
?>