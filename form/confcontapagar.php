<!-- Ciar Paramêtros para vincular Produtos e Serviços. Segunda Div lista os produtos. Criado em 09/01/2020 Lidiane -->
<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/permissao.php");
require_once("../form/controllers/confcontapagar_controller.php");
require_once("../form/controllers/prodserv_controller.php");

if ($_POST) {
    require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "confcontapagar";
$pagvalcampos = array(
    "idconfcontapagar" => "pk"
);

/*
* $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
*/
$pagsql = "select * from confcontapagar where idconfcontapagar = '#pkid'";

/*
* controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
*/
include_once("../inc/php/controlevariaveisgetpost.php");

?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <table>
                    <tr>
                        <td>
                            <input name="_1_<?= $_acao ?>_confcontapagar_idconfcontapagar" type="hidden" value="<?= $_1_u_confcontapagar_idconfcontapagar ?>" readonly='readonly'>
                        </td>
                        <td>Descrição:</td>
                        <td>
                            <input name="_1_<?= $_acao ?>_confcontapagar_configuracao" type="text" value="<?= $_1_u_confcontapagar_configuracao ?>" class="size30">
                        </td>
                        <td>Tipo:</td>
                        <td>
                            <select name="_1_<?= $_acao ?>_confcontapagar_tipo">
                                <option value=""></option>
                                <? fillselect(ConfContapagarController::$tipoConfcontapagar, $_1_u_confcontapagar_tipo); ?>
                            </select>
                        </td>

                        <td>Tipo EVento RH:</td>
                        <td>
                            <select name="_1_<?= $_acao ?>_confcontapagar_idrhtipoevento" vnulo>
                                <option value=""></option>
                                <? fillselect(ConfContapagarController::listarTipoEventoRH(), $_1_u_confcontapagar_idrhtipoevento); ?>
                            </select>
                        </td>

                        <td>Status:</td>
                        <td>
                            <select name="_1_<?= $_acao ?>_confcontapagar_status">
                                <? fillselect(ConfContapagarController::$status, $_1_u_confcontapagar_status); ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading">NF Automática</div>
                            <div class="panel-body">
                                <table>
                                    <tr>
                                        <td style="text-align: right;">Tipo:</td>
                                        <td colspan="4">
                                            <select name="_1_<?= $_acao ?>_confcontapagar_tiponf">
                                                <option value=""></option>
                                                <? fillselect(ConfContapagarController::$tiponf, $_1_u_confcontapagar_tiponf); ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: right;">Status:</td>
                                        <td colspan="5">
                                            <select name="_1_<?= $_acao ?>_confcontapagar_statusnf" vnulo>
                                                <option value=""></option>
                                                <? fillselect(ConfContapagarController::$statusnf, $_1_u_confcontapagar_statusnf); ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <?
                                    if ($_1_u_confcontapagar_tipo != 'TAXA BOLETO') {
                                    ?>
                                        <tr>
                                            <td style="text-align: right;">Pessoa:</td>
                                            <td colspan="5">
                                                <select name="_1_<?= $_acao ?>_confcontapagar_idpessoa">
                                                    <option value=""></option>
                                                    <? fillselect(ConfContapagarController::listarPessoaPorIdtipopessoaIdempresa('7,5,11'), $_1_u_confcontapagar_idpessoa); ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: right;">Fatura Automática:</td>
                                            <td colspan="5">
                                                <select name="_1_<?= $_acao ?>_confcontapagar_idformapagamento">
                                                    <option value=""></option>
                                                    <? fillselect(ConfContapagarController::listarFormaPagamentoPorEmpresa(), $_1_u_confcontapagar_idformapagamento); ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?
                                    }
                                    ?>
                                    <tr>
                                        <td>Categoria</td>
                                        <td colspan="5">
                                            <select id="idcontaitem" onchange="preencheti()" name="_1_<?= $_acao ?>_confcontapagar_idcontaitem">
                                                <option value=""></option>
                                                <? fillselect(ConfContapagarController::buscarContaItemAtivoShare(), $_1_u_confcontapagar_idcontaitem); ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td> Subcategoria:</td>
                                        <td colspan="5">
                                            <select id="idtipoprodserv" name="_1_<?= $_acao ?>_confcontapagar_idtipoprodserv">
                                                <option value=""></option>
                                                <? fillselect(ConfContapagarController::listarTipoProdservConfpagar($_1_u_confcontapagar_idcontaitem), $_1_u_confcontapagar_idtipoprodserv); ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <?

                                    if (!empty($_1_u_confcontapagar_idconfcontapagar) and $_1_u_confcontapagar_tipo != 'TAXA BOLETO') {
                                    ?>

                                        <tr>
                                            <td style="text-align: right;">Emisssão:</td>
                                            <td colspan="2">
                                                <? if ($_1_u_confcontapagar_vigente == 'Y') {
                                                    $checked = 'checked';
                                                    $vchecked = 'N';
                                                } else {
                                                    $checked = '';
                                                    $vchecked = 'Y';
                                                }
                                                ?>
                                                <input style="margin-right: 6px;" title="Mês Vigente." type="radio" <?= $checked ?> name="namemesv" onclick="altcheckV(<?= $_1_u_confcontapagar_idconfcontapagar ?>,'<?= $vchecked ?>')">
                                                Mês Vigente.
                                            </td>
                                            <td colspan="2">
                                                <? if ($_1_u_confcontapagar_sequente == 'Y') {
                                                    $checked = 'checked';
                                                    $vchecked = 'N';
                                                } else {
                                                    $checked = '';
                                                    $vchecked = 'Y';
                                                }
                                                ?>
                                                <input style="margin-right: 6px;" title="Mês Sequente." type="radio" <?= $checked ?> name="namemess" onclick="altcheckS(<?= $_1_u_confcontapagar_idconfcontapagar ?>,'<?= $vchecked ?>')">
                                                Mês Sequente.
                                            </td>                                           
                                        </tr>
                                        <tr>
                                            <td align="right">Dia Vencimento:</td>
                                            <td>
                                                <input name="_1_<?= $_acao ?>_confcontapagar_diavenc" type="text" value="<?= $_1_u_confcontapagar_diavenc ?>" class="size3">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: right;">Agrupamento:</td>
                                            <td>
                                                <? if ($_1_u_confcontapagar_agruppessoa == 'Y') {
                                                    $checked = 'checked';
                                                    $vchecked = 'N';
                                                } else {
                                                    $checked = '';
                                                    $vchecked = 'Y';
                                                }
                                                ?>
                                                <input style="margin-right: 6px;" title="Agrupa por pessoa." type="radio" <?= $checked ?> name="nameagrupp" onclick="altcheckAP(<?= $_1_u_confcontapagar_idconfcontapagar ?>,'<?= $vchecked ?>')">
                                                Agrupado por Pessoa
                                            </td>
                                            <td>
                                                <? if ($_1_u_confcontapagar_agrupnota == 'Y') {
                                                    $checked = 'checked';
                                                    $vchecked = 'N';
                                                } else {
                                                    $checked = '';
                                                    $vchecked = 'Y';
                                                }
                                                ?>
                                                <input style="margin-right: 6px;" title="Agrupa por nota." type="radio" <?= $checked ?> name="nameagrupn" onclick="altcheckAN(<?= $_1_u_confcontapagar_idconfcontapagar ?>,'<?= $vchecked ?>')">
                                                Agrupado por Nota
                                            </td>
                                        </tr>
                                </table>
                            <? } ?>

                            </table>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-8">
                        <?
                        if (
                            $_1_u_confcontapagar_tipo == 'COFINS' or $_1_u_confcontapagar_tipo == 'GNRE'  or $_1_u_confcontapagar_tipo == 'ICMS'  or $_1_u_confcontapagar_tipo == 'CSRF'
                            or $_1_u_confcontapagar_tipo == 'INSS'  or $_1_u_confcontapagar_tipo == 'IRRF'  or $_1_u_confcontapagar_tipo == 'PIS'  or $_1_u_confcontapagar_tipo == 'IPI'
                        ) { ?>
                            <?
                            $i = 99;
                            $resc = ConfContapagarController::buscarConfcontapagaritemPorIdconfcontapagar($_1_u_confcontapagar_idconfcontapagar);
                            foreach ($resc as $rowc) {
                                $i++;
                            ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <div class="col-md-10">
                                                Regra Item Automático
                                            </div>
                                            <div class="col-md-2" align="right">
                                                <i class="fa fa-trash fa-2x cinzaclaro hoververmelho pointer ui-droppable" onclick="excluir('confcontapagaritem',<?= $rowc["idconfcontapagaritem"] ?>)" title="Excluir !"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row d-flex align-items-center">
                                            <div class="col-md-12">
                                                <b>Condição:</b>
                                            </div>
                                        </div>

                                        <div class="row d-flex align-items-center">

                                            <div class="col-md-2" style="text-align: right;">Origem Tipo:</div>
                                            <div class="col-md-2">
                                                <input name="_<?= $i ?>_u_confcontapagaritem_idconfcontapagaritem" type="hidden" value="<?= $rowc['idconfcontapagaritem'] ?>">
                                                <select class="size10" name="_<?= $i ?>_u_confcontapagaritem_tiponf" onchange="mostrarfin(<?= $i ?>,this)">
                                                    <option value=""></option>
                                                    <? fillselect(ConfContapagarController::$_tiponf, $rowc['tiponf']); ?>
                                                </select>
                                            </div>
                                            <div class="col-md-1" style="text-align: right;">Regime Tributário:</div>
                                            <div class="col-md-4">
                                                <select class="size25" name="_<?= $i ?>_u_confcontapagaritem_regimetrib">
                                                    <option value=""></option>
                                                    <? fillselect(ConfContapagarController::regimetrib(), $rowc['regimetrib']); ?>
                                                </select>
                                            </div>
                                            <?
                                            if ($rowc['tiponf'] != 'C') {
                                                $hide = "hide";
                                            } else {
                                                $hide = "";
                                            }
                                            ?>
                                            <div class="col-md-1 <?= $hide ?>" id="rotf<?= $i ?>" style="text-align: right;">Finalidade:</div>
                                            <div class="col-md-2 <?= $hide ?>" id="valf<?= $i ?>">
                                                <select class="size10" name="_<?= $i ?>_u_confcontapagaritem_tipoconsumo" vnulo>
                                                    <option value=""></option>
                                                    <? fillselect(ConfContapagarController::$tipoconsumo, $rowc['tipoconsumo']); ?>
                                                </select>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row d-flex align-items-center">
                                            <div class="col-md-2" style="text-align: right;">Item Automático:</div>
                                            <div class="col-md-2">
                                                <select class="size7" name="_<?= $i ?>_u_confcontapagaritem_tipo">
                                                    <option value=""></option>
                                                    <? fillselect(ConfContapagarController::$tipo, $rowc['tipo']); ?>
                                                </select>
                                            </div>
                                            <div class="col-md-1" style="text-align: right;">Categoria:</div>
                                            <div class="col-md-4">
                                                <select class="size25" id="idcontaitem<?= $i ?>" onchange="preenchetitem(<?= $i ?>)" name="_<?= $i ?>_u_confcontapagaritem_idcontaitem">
                                                    <option value=""></option>
                                                    <? fillselect(ConfContapagarController::buscarContaItemAtivoShare(), $rowc['idcontaitem']); ?>
                                                </select>
                                            </div>
                                            <div class="col-md-1" style="text-align: right;">Tipo:</div>
                                            <div class="col-md-2">
                                                <select id="idtipoprodserv<?= $i ?>" name="_<?= $i ?>_u_confcontapagaritem_idtipoprodserv">
                                                    <option value=""></option>
                                                    <? fillselect(ConfContapagarController::listarTipoProdservConfpagar($rowc['idcontaitem']), $rowc['idtipoprodserv']); ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?
                            }
                            ?>
                            <div class="panel panel-default">
                                <div class="panel-heading"> Regra Item Automático</div>
                                <div class="panel-body">
                                    <table>
                                        <tr>
                                            <td colspan="10">
                                                <i class="fa fa-plus-circle fa-2x  cinzaclaro hoververde btn-lg pointer" onclick="novoobjeto('confcontapagaritem',<?= $_1_u_confcontapagar_idconfcontapagar ?>)" title="Inserir nova configuração!"></i>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        <? } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?
require_once('../form/js/confcontapagar_js.php');

$tabaud = "confcontapagar";
require_once('../form/viewEvento.php');
require_once('../form/viewCriadoAlteradoNew.php');
require_once('../form/js/folhapagamento_js.php');
?>
?>