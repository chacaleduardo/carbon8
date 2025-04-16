<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}


/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetros chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "cbenef";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idcbenef" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from cbenef where idcbenef = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

require_once(__DIR__ . "/controllers/pedidoemlote_controller.php");
require_once(__DIR__ . "/controllers/cbenef_controller.php");

$cbenefItens = [];

if($_1_u_cbenef_idcbenef) $cbenefItens = CbenefConttrooler::buscarItensPorIdCbenef($_1_u_cbenef_idcbenef);
?>

<link rel="stylesheet" href="/form/css/cbenef_css.css" />
<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-default">
            <div class="panel-heading row d-flex align-items-center w-100">
                <? if ($_1_u_cbenef_idcbenef) { ?>
                    <div class="form-group col-xs-12 col-lg-1">
                        <label for="" class="text-white">ID</label>
                        <label class="d-flex align-items-center form-control alert-warning">
                            <?= $_1_u_cbenef_idcbenef ?>
                        </label>
                    </div>
                <? } ?>
                <div class="form-group col-xs-3 text-right ml-auto">
                    <label for="" class="text-white block">Status</label>
                    <select name="_1_<?= $_acao ?>_cbenef_status" class="form-control">
                        <?= fillselect(['ATIVO' => 'Ativo', 'INATIVO' => 'Inativo'], $_1_u_cbenef_status) ?>
                    </select>
                </div>
            </div>
            <div class="panel-body">
                <? if($_1_u_cbenef_idcbenef) { ?>
                    <input name="_1_<?= $_acao ?>_cbenef_idcbenef" value="<?= $_1_u_cbenef_idcbenef ?>" class="hidden" hidden>
                <? } ?>
                <div class="col-xs-12 col-md-8 d-flex flex-column">
                    <div class="form-group col-xs-4 px-0 block">
                        <label for="">UF </label>
                        <select name="_1_<?= $_acao ?>_cbenef_uf" id="" class="select-picker form-control" vnulo>
                            <option value="">Selecionar UF</option>
                            <? fillselect(PedidoEmLoteController::buscarUf(true), $_1_u_cbenef_uf) ?>
                        </select>
                    </div>
                    <? if ($_1_u_cbenef_idcbenef) { ?>
                        <div id="cbenef-container" class="w-100">
                            <h4>Itens</h4>
                            <hr />
                            <div id="cbenef-table" class="col-xs-12 col-md-8 px-0">
                                <!-- Cabecalho -->
                                <div class="pb-2 w-100">
                                    <div class="d-flex text-uppercase text-center">
                                        <h4 class="col-xs-3">CST</h4>
                                        <h4 class="col-xs-4">NCM</h4>
                                        <h4 class="col-xs-4">CBENEF</h4>
                                        <h4 class="col-xs-1"></h4>
                                    </div>
                                </div>
                                <!-- Corpo -->
                                <div id="cbenef-items" class="w-100">
                                    <? if(!$cbenefItens) { ?>
                                        <div id="sem-itens" class="col-xs-12 text-center">
                                            <h5>Nenhum item adicionado.</h5>
                                        </div>
                                    <? } else { ?>
                                        <? foreach($cbenefItens as $key => $item) { ?>
                                            <div class="d-flex form-group align-items-center">
                                                <input type="text" name="_c<?= $key ?>_<?= $_acao ?>_cbenefitem_idcbenef" class="form-control hidden" value="<?= $_1_u_cbenef_idcbenef ?>" hidden />
                                                <input type="text" name="_c<?= $key ?>_<?= $_acao ?>_cbenefitem_idcbenefitem" class="form-control hidden" value="<?= $item['idcbenefitem'] ?>" hidden />
                                                <div class="col-xs-3">
                                                    <input type="text" name="_c<?= $key ?>_<?= $_acao ?>_cbenefitem_cst" class="form-control" value="<?= $item['cst'] ?>" placeholder="Digite o CST" vnulo />
                                                </div>
                                                <div class="col-xs-4">
                                                    <input type="text" name="_c<?= $key ?>_<?= $_acao ?>_cbenefitem_ncm" class="form-control" value="<?= $item['ncm'] ?>" placeholder="Digite o NCM" vnulo />
                                                </div>
                                                <div class="col-xs-4">
                                                    <input type="text" name="_c<?= $key ?>_<?= $_acao ?>_cbenefitem_cbenef" class="form-control" value="<?= $item['cbenef'] ?>" placeholder="Digite o CBENEF" vnulo />
                                                </div>
                                                <div class="col-xs-1">
                                                    <i class="fa fa-trash vermelho pointer fa-2x btn-remove" title="Remover item" data-idcbenefitem="<?=$item['idcbenefitem'] ?>"></i>
                                                </div>
                                            </div>
                                        <? } ?>
                                    <? } ?>
                                </div>
                            </div>
                            <!-- Rodape -->
                            <div class="col-xs-12 col-md-8 px-0 text-end mt-3">
                                <button id="add-item" class="ml-auto btn btn-primary d-flex align-items-center" title="Adicionar item.">
                                    ADICIONAR <i class="ml-2 fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    <? } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<? require_once(__DIR__ . "/js/cbenef_js.php"); 
$tabaud = "cbenef"; //pegar a tabela do criado/alterado em antigo
$_disableDefaultDropzone = true;
require 'viewCriadoAlterado.php';
?>