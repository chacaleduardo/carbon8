<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parámetro chave para o select inicial
 *                vnulo: indica parámetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "formularotulo";
$pagvalcampos = array(
	"idformularotulo" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from formularotulo where idformularotulo = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

// Controllers
require_once(__DIR__."/controllers/formularotulo_controller.php");
require_once(__DIR__."/controllers/prodservformularotulo_controller.php");
require_once(__DIR__."/controllers/frasco_controller.php");
require_once(__DIR__."/controllers/fluxo_controller.php");

$idprodservformula= $_1_u_formularotulo_idprodservformula ?? $_GET["idprodservformula"];

$rotulos = ProdservFormulaRotuloController::buscarFormulaRotulo();
$frascos = FrascoController::buscarFrascos(true);
$rotulosToFillSelect = [];
foreach($rotulos as $rotulo) $rotulosToFillSelect[$rotulo['idprodservformularotulo']] = $rotulo['titulo'] ?? 'Sem titulo';
$formulaRotulo = [];
$isEdit = $_1_u_formularotulo_status === 'REVISAO' || $_acao === 'i';
$botaoStatusAtual = FormulaRotuloController::getLabelStatusButton($_1_u_formularotulo_status);
$versaoAtual = FormulaRotuloController::buscarVersaoAtual((int)$_1_u_formularotulo_idformularotulo);
$versoesFormulaRotulo = FormulaProcessoController::buscarObjetoPorTipoObjeto($_1_u_formularotulo_idformularotulo, 'formularotulo');

if($idprodservformula) $formulaRotulo = FormulaRotuloController::buscarProdServEFormulaRotuloPorIdProdServFormula($idprodservformula);

?>
<style>
    .row{margin: 1rem 0 !important;}
    p, strong{font-size: 1.2rem;}
    textarea{
        max-width: 100%;
        width: 100%;
        min-height: 150px;
    }
    .input-group{padding: 0;}
</style>
<div class="row ">
    <div class="col-md-12">
        <div class="panel panel-default" >
            <div class="panel-heading">
                <span class="mr-2">Configurações do Rótulo</span>
                <span class="d-inline-block alert-warning p-1 rounded text-uppercase">
                    <?= getStatusFluxo('formularotulo', 'idformularotulo', $_1_u_formularotulo_idformularotulo)['rotulo'] ?>
                </span>
            <? if($_1_u_formularotulo_idprodservformula) { ?>
                <a title="Imprimir" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('form/rotulovac.php?acao=u&idprodservformula=<?=$_1_u_formularotulo_idprodservformula?>')"></a>
            <?}?>
            </div>
            <div class="panel-body pt-2">	
                <div class="row">
                    <input name="_1_<?=$_acao?>_formularotulo_idformularotulo" id="idformularotulo" type="hidden" value="<?= $_1_u_formularotulo_idformularotulo ?>" />
                    <input name="_1_<?=$_acao?>_formularotulo_status" type="hidden" value="<?= $_1_u_formularotulo_status ?>" />
                    <input name="_1_<?=$_acao?>_formularotulo_idempresa" type="hidden" value="<?= $_1_u_formularotulo_idempresa ?>" />
                    <input name="idfluxostatus" type="hidden" value="<?= $_1_u_formularotulo_idfluxostatus ?>" />
                    <!-- idprodservformula -->
                    <input name="_1_<?=$_acao?>_formularotulo_idprodservformula" id="idprodservformula" type="hidden" readonly value="<?= $idprodservformula?>">
                    <!-- Frasco -->
                    <div class="col-xs-12 col-lg-6 form-group d-flex">
                        <div class="col-xs-12 col-lg-2">
                            <strong>Frasco:</strong>
                        </div>
                        <div class="col-xs-12 col-lg-8 px-0 input-group">
                            <input name="frasco" type="hidden" value="<?= $frascos[$_1_u_formularotulo_idfrasco] ?? '' ?>" />
                            <? if($isEdit) { ?>
                                <select id="input-frasco" name="_1_<?=$_acao?>_formularotulo_idfrasco" class="form-control selectpicker" data-live-search="true" vnulo>
                                    <option value="">Selecionar</option>
                                    <?fillselect($frascos, $_1_u_formularotulo_idfrasco);?>
                                </select>
                            <? }else { ?>
                                <input name="_1_<?=$_acao?>_formularotulo_idfrasco" type="hidden" value="<?= $_1_u_formularotulo_idfrasco ?>" />
                                <span><?= $frascos[$_1_u_formularotulo_idfrasco] ?? '' ?></span>
                            <?}?>
                        </div>
                    </div>
                    <!-- Rotulos -->
                    <div class="col-xs-12 col-lg-6 form-group d-flex <?= !$isEdit ? 'align-items-center' : '' ?>">
                        <div class="col-xs-12 col-lg-2">
                            <strong>Rotulos:</strong>
                        </div>
                        <input name="rotulo" type="hidden" value="<?= addslashes($rotulosToFillSelect[$_1_u_formularotulo_idprodservformularotulo]) ?>" />
                        <? if($isEdit) { ?> 
                            <div class="col-xs-12 col-lg-8 px-0 input-group">
                                <select id="input-rotulo" name="_1_<?=$_acao?>_formularotulo_idprodservformularotulo" class="form-control selectpicker" data-live-search="true" vnulo <?= $_acao == 'i' ? 'onchange="CB.post();"' : '' ?>>
                                    <option value="">Selecionar</option>
                                    <?fillselect($rotulosToFillSelect, $_1_u_formularotulo_idprodservformularotulo);?>
                                </select>
                            </div>
                        <? }else { ?>
                            <p class="mb-0"><?= $rotulosToFillSelect[$_1_u_formularotulo_idprodservformularotulo] ?></p> 
                            <input name="_1_<?=$_acao?>_formularotulo_idprodservformularotulo" type="hidden" value="<?= $_1_u_formularotulo_idprodservformularotulo ?>" />
                        <? }?>
                        <? if ($_1_u_formularotulo_idprodservformularotulo && in_array($_SESSION['SESSAO']['MODULOS']['prodservformularotulo']['permissao'], ['r', 'w'])) {?>
                            <div class="col-xs-1 px-0">
                                <? if($isEdit) { ?> 
                                    <a href="?_modulo=prodservformularotulo&_acao=u&idprodservformularotulo=<?=$_1_u_formularotulo_idprodservformularotulo?>" target="_blank" class="pointer hoverazul d-block d-flex align-items-center justify-content-center btn-primary h-100" title="Padrão Formulas e Rótulos">
                                        <i class="fa fa-bars"></i>
                                    </a>
                                <? }else { ?>
                                    <a href="?_modulo=prodservformularotulo&_acao=u&idprodservformularotulo=<?=$_1_u_formularotulo_idprodservformularotulo?>" target="_blank" class="pointer hoverazul d-block d-flex align-items-center justify-content-center h-100" title="Padrão Formulas e Rótulos">
                                        <i class="fa fa-bars"></i>
                                    </a>
                                <?}?>
                            </div>                                
                        <?}?>
                    </div>
                </div>
                <? if($formulaRotulo) {  ?>
                    <div class="row">
                        <div id="example" class="is-visible"></div>
                        <div class="col-xs-12 col-lg-6">
                            <!-- Indicações -->
                            <div class="row">
                                <div class="col-xs-12 col-lg-2"><strong>Indicações:</strong></div>
                                <div class="col-xs-12 col-lg-8">
                                    <?if($isEdit) { ?>
                                        <textarea id="indicacao" name="_1_<?=$_acao?>_formularotulo_indicacao" class="form-control alter"><?= $_1_u_formularotulo_indicacao ? $_1_u_formularotulo_indicacao : $formulaRotulo['indicacao'] ?></textarea>
                                    <?} else { ?>
                                        <p><?= $_1_u_formularotulo_indicacao ? $_1_u_formularotulo_indicacao : $formulaRotulo['indicacao'] ?></p>
                                        <textarea class="hidden" name="_1_<?=$_acao?>_formularotulo_indicacao" type="hidden"><?= $_1_u_formularotulo_indicacao ? $_1_u_formularotulo_indicacao : $formulaRotulo['indicacao'] ?></textarea>
                                    <? } ?>
                                </div>
                            </div>
                            <!-- Fórmula -->
                            <div class="row">
                                <div class="col-xs-12 col-lg-2"><strong>Fórmula:</strong></div>
                                <div class="col-xs-12 col-lg-8">
                                    <?if($isEdit) { ?>
                                        <textarea id="formula" name="_1_<?=$_acao?>_formularotulo_formula" class="form-control alter"><?=$_1_u_formularotulo_formula ? $_1_u_formularotulo_formula : $formulaRotulo['formula']?></textarea>
                                    <?} else { ?>
                                       <p><?= $_1_u_formularotulo_formula ? $_1_u_formularotulo_formula : $formulaRotulo['formula']?></p> 
                                       <textarea class="hidden" name="_1_<?=$_acao?>_formularotulo_formula" type="hidden"><?= $_1_u_formularotulo_formula ? $_1_u_formularotulo_formula : $formulaRotulo['formula'] ?></textarea>
                                    <? } ?>
                                </div>
                            </div>
                            <!-- Modo de usar -->
                            <div class="row">
                                <div class="col-xs-12 col-lg-2"><strong>Modo de usar:</strong></div>
                                <div class="col-xs-12 col-lg-8">
                                    <p id="modousar-elemento"><?=nl2br($formulaRotulo['modousar'])?></p>
                                    <input id="modousar" name="modousar" class="hidden" type="hidden" value="<?=nl2br($formulaRotulo['modousar'])?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-lg-6">
                            <!-- Cepas -->
                            <div class="row">
                                <div class="col-xs-12 col-lg-2">
                                    <strong>Cepas:</strong>
                                </div>
                                <div class="col-xs-12 col-lg-8">
                                <?if($isEdit) { ?>
                                    <textarea name="_1_<?=$_acao?>_formularotulo_cepas" id="cepas" id="" class="form-control alter"><?= $_1_u_formularotulo_cepas ? $_1_u_formularotulo_cepas : $formulaRotulo['cepas'] ?></textarea>
                                <?} else { ?>
                                    <p>
                                        <?=  $_1_u_formularotulo_cepas ? $_1_u_formularotulo_cepas : $formulaRotulo['cepas'] ?>
                                    </p>
                                    <textarea class="hidden" name="_1_<?=$_acao?>_formularotulo_cepas" type="hidden"><?= $_1_u_formularotulo_cepas ? $_1_u_formularotulo_cepas : $formulaRotulo['cepas'] ?></textarea>
                                <?}?>
                                </div>
                            </div>
                            <!-- Descrição -->
                            <div class="row">
                                <div class="col-xs-12 col-lg-2"><strong>Descrição:</strong></div>
                                <div class="col-xs-12 col-lg-8">
                                    <p id="descricao-elemento"><?=$formulaRotulo['descricao']?></p>
                                    <input id="descricao" name="descricao" class="hidden" type="hidden" value="<?=$formulaRotulo['descricao']?>">
                                </div>
                            </div>
                            <!-- Conteúdo -->
                            <div class="row">
                                <div class="col-xs-12 col-lg-2"><strong>Conteúdo:</strong></div>
                                <div class="col-xs-12 col-lg-8">
                                    <p id="conteudo-elemento"><?=$formulaRotulo['conteudo']?></p>
                                    <input id="conteudo" name="conteudo" class="hidden" type="hidden" value="<?=$formulaRotulo['conteudo']?>">
                                </div>
                            </div>
                            <!-- Programa de utilização -->
                            <div class="row">
                                <div class="col-xs-12 col-lg-2"><strong>Programa de utilização:</strong></div>
                                <div class="col-xs-12 col-lg-8">
                                    <p id="programa-elemento"><?=nl2br($formulaRotulo['programa'])?></p>
                                    <input id="programa" name="programa" class="hidden" type="hidden" value="<?=nl2br($formulaRotulo['programa'])?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <? if(count($versoesFormulaRotulo)) { ?>
                        <div class="row">
                            <div class="col-xs-12 col-lg-6 panel-default">
                                <div class="panel-heading">
                                    <a data-toggle="collapse" href="#versoes" class="w-100 d-block py-2">Versões</a>
                                </div>
                                <div id="versoes" class="panel-body collapse">
                                    <table class="table table-striped planilha">
                                        <thead>
                                            <tr>
                                                <th>Versão</th>
                                                <th>Alterado Por</th>
                                                <th>Alterado Em</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <? foreach($versoesFormulaRotulo as $key => $hist) {
                                                ?>
                                                <tr>
                                                    <td><?= "{$hist['versaoobjeto']}.0" ?></td>
                                                    <td><?= $hist['alteradopor'] ?></td>
                                                    <td><?= dma($hist['alteradoem']) ?></td>
                                                    <td>
                                                        <i class="fa fa-bars pointer hoverazul" onclick="detalhesVersao(<?= $key ?>)"></i>
                                                    </td>
                                                </tr>
                                            <? }?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <? } ?>
                <?}?>
            </div>
        </div>
        <?
            $tabaud = "formularotulo";
            $_disableDefaultDropzone = true;
            require 'viewCriadoAlterado.php';
            require(__DIR__."/js/formularotulo_js.php");
        ?>
    </div> 
</div>