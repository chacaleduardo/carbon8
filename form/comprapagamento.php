<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/nfentrada_controller.php");
require_once(__DIR__ . "/controllers/fluxo_controller.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}

/**********************************************************************************************************************
 *												|							|										  *
 *												|	  Modal Pagamento   	|										  *
 *												V							V										  *
 ***********************************************************************************************************************/

$pagvaltabela = "nf";
$pagvalmodulo = $_GET['_modulo'];
$vtotal = $_GET['vlrtotal'];

$pagvalcampos = array(
    "idnf" => "pk"
);
$pagsql = "SELECT * FROM nf WHERE idnf = '#pkid'";

include_once("../inc/php/controlevariaveisgetpost.php");

$arrayFormaPagamento = NfEntradaController::listarFormaPagamentoAtivoPorLP();

if (empty($_1_u_nf_intervalo)) {
    $_1_u_nf_intervalo = 28;
}

?>

<link href="../form/css/nfentrada_css.css?_<?= date("dmYhms") ?>" rel="stylesheet">

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading cabecalho" style="font-size:12px">
                <div class="row">
                    <div class="col-sm-1 sigla-empresa"></div>
                    <div class="alinhar-texto-cabecalho">
                        <strong>Pagamento</strong>
                    </div>
                </div>
            </div>
            <?
            if (!empty($_1_u_nf_vnf)) {
                $vsubtotal = $_1_u_nf_vnf;
            } elseif (!empty($valorx)) {
                $valorx = $valorx - $desconto;
                $vsubtotal = $valorx;
            }

            if (!empty($fretex)) {
                $_1_u_nf_frete = $fretex;
            }

            if ($_1_u_nf_tiponf == "S" or $_1_u_nf_tiponf == 'R' or $_1_u_nf_tiponf == 'D') {
                $imp_serv = (tratanumero($_1_u_nf_pis) + tratanumero($_1_u_nf_cofins) + tratanumero($_1_u_nf_csll) + tratanumero($_1_u_nf_inss) + tratanumero($_1_u_nf_ir) + tratanumero($_1_u_nf_issret));
                $imp_serv = ($imp_serv < 0) ? 0 : $imp_serv;
                $vtotal = $vtotal - $imp_serv;
            }
            ?>
            <!-- input name="_1_<?= $_acao ?>_nf_subtotal" id="vlrsubtotal" size="8" type="hidden" value="<?= number_format(tratanumero($vsubtotal), 2, ',', '.') ?>" <?= $readonly ?> <?= $vreadonly ?> vdecimal -->
            <input <?= $readonly ?> <?= $vreadonly ?> name="_1_<?= $_acao ?>_nf_total" id="vlrtotal" class="size6" type="hidden" value="<?= number_format(tratanumero($vtotal), 2, ',', '.') ?>" vdecimal>
            <input name="_1_<?= $_acao ?>_nf_idnf" id="idnf" type="hidden" value="<?= $_1_u_nf_idnf ?>" readonly='readonly'>
            <div class="panel-body">
                <? if ($_1_u_nf_tiponf == "E") { ?>
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="form-group">
                                <div class="col-sm-1">
                                    <?
                                    if ($_1_u_nf_sped == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    }
                                    ?>
                                    <input title="sped" type="checkbox" <?= $checked ?> name="namesped" onclick="altcheck('nf','sped',<?= $_1_u_nf_idnf ?>,'<?= $vchecked ?>')">
                                </div>
                                <div class="col-sm-10 top-align-sete">
                                    Sped?
                                </div>
                            </div>
                        </div>
                    </div>
                <? } ?>

                <div class="col-md-12">
                    <div class="form-group">
                        <label>Emissão:</label>
                        <div class="input-group col-md-12">
                            <? echo ($_1_u_nf_dtemissao); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Data Entrada:</label>
                        <div class="input-group col-md-12">
                            <input type="hidden" id="statusnota" value="<?= $_1_u_nf_status ?>">
                            <input name="_1_<?= $_acao ?>_nf_prazo" autocomplete="off" class="calendario size8" <? if ($_1_u_nf_status == 'CONCLUIDO' or $qtdc100 > 0) { ?> disabled="disabled" <? } ?> id="fdata1" type="text" value="<?= $_1_u_nf_prazo ?>">
                            <? if ($_1_u_nf_status == 'CONCLUIDO'  or $qtdc100 > 0) { ?>
                                <input name="_1_<?= $_acao ?>_nf_prazo" type="hidden" value="<?= $_1_u_nf_prazo ?>">
                            <? } ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group col-md-2">
                        <label>Gera Parcela:</label>
                        <div class="input-group col-md-12">
                            <select class="size6" name="_1_<?= $_acao ?>_nf_geracontapagar" vnulo <? if ($_1_u_nf_status == 'CONCLUIDO' or $_1_u_nf_status == 'CANCELADO') { ?>readonly='readonly' <? } ?>>
                                <? fillselect(NfEntradaController::$_simNao, $_1_u_nf_geracontapagar); ?>
                            </select>
                        </div>
                    </div>
                    <? if ($_1_u_nf_geracontapagar == "Y") { ?>
                        <div class="form-group col-md-2">
                            <label>Tipo:</label>
                            <div class="input-group col-md-12">
                                <select class="size8" name="_1_<?= $_acao ?>_nf_tipocontapagar" id="tipo">
                                    <? fillselect(NfEntradaController::$_debitoCredito, $_1_u_nf_tipocontapagar); ?>
                                </select>
                            </div>
                        </div>
                    <? }

                    if ($_1_u_nf_geracontapagar == "Y") {
                    ?>
                        <div class="form-group col-md-2">
                            <label>Pagamento:</label>
                            <div class="input-group col-md-12">
                                <? if (empty($_1_u_nf_idformapagamento)) { ?>
                                    <input name="_nf_idformapagamentoant" type="hidden" value="<?= $_1_u_nf_idformapagamento ?>">
                                    <input id="forma_pag" cbvalue='<?= $_1_u_nf_idformapagamento ?>' name="_1_<?= $_acao ?>_nf_idformapagamento" vnulo value="<?= $arrayFormaPagamento[$_1_u_nf_idformapagamento]['descricao'] ?>">
                                <? } else { ?>
                                    <label class="alert-warning"><?= traduzid('formapagamento', 'idformapagamento', 'descricao', $_1_u_nf_idformapagamento) ?></label>
                                    <i onclick="mostraInputFormapagamento(this)" class="fa fa-pencil azul"></i>
                                    <input name="_nf_idformapagamentoant" type="hidden" value="<?= $_1_u_nf_idformapagamento ?>">
                                    <input style="display: none;" disabled cbvalue='<?= $_1_u_nf_idformapagamento ?>' id="forma_pag" name="_1_<?= $_acao ?>_nf_idformapagamento" vnulo>
                                <? } ?>
                            </div>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Parcelas:</label>
                            <div class="input-group col-md-12">
                                <?
                                for ($selparcelas = 1; $selparcelas <= 120; $selparcelas++) {
                                    if ($selparcelas == 1) {
                                        $select = "select " . $selparcelas . ",'" . $selparcelas . "x' ";
                                    } else {
                                        $select .= "union select " . $selparcelas . ",'" . $selparcelas . "x' ";
                                    }
                                }
                                ?>
                                <select class="size6" name="_1_<?= $_acao ?>_nf_parcelas" id$_1_u_nf_dtemissao="parcelas" onchange="atualizaparc(this)">
                                    <? fillselect($select, $_1_u_nf_parcelas); ?>
                                </select>
                            </div>
                        </div>
                        <? if (!empty($_1_u_nf_idformapagamento)) {
                            $rowdias = NfEntradaController::buscarConfiguracoesFormaPagamento($_1_u_nf_idformapagamento);
                            if (empty($_1_u_nf_diasentrada) and $_1_u_nf_diasentrada != 0) {
                                $_1_u_nf_diasentrada = $rowdias['campo'];
                                if (empty($_1_u_nf_diasentrada)) {
                                    $_1_u_nf_diasentrada = 0;
                                }
                            }
                        ?>
                            <div class="form-group col-md-2">
                                <label>1º Vencimento:</label>
                                <div class="input-group col-md-12">
                                    <input class="size3" name="_1_<?= $_acao ?>_nf_diasentrada" type="text" value="<?= $_1_u_nf_diasentrada ?>" onchange="atualizadiasentrada(this)">&nbsp
                                    <select class="size6" name="_1_<?= $_acao ?>_nf_tipointervalo">
                                        <? fillselect(NfEntradaController::$_periodo, $_1_u_nf_tipointervalo); ?>
                                    </select>
                                </div>
                            </div>
                            <? if ($_1_u_nf_parcelas > 1) {
                                $strdivtab = "style='display:block;'";
                            } else {
                                $strdivtab = "style='display:none;'";
                            } ?>
                            <div class="form-group col-md-2">
                                <label>Intervalo:</label>
                                <div class="input-group col-md-12">
                                    <input class="size3" name="_1_<?= $_acao ?>_nf_intervalo" type="text" value="<?= $_1_u_nf_intervalo ?>" onchange="atualizaintervalo(this)">
                                </div>
                            </div>
                    <?
                        }
                    }
                    ?>
                </div>

                <?
                if ($_1_u_nf_geracontapagar == "Y") {
                ?>
                    <div class="col-md-12">
                        <div class="form-group col-md-12">
                            <div class="col-md-2">
                                <label>Editar Proporção:</label>
                            </div>
                            <div class="col-md-10">
                                <div class="input-group col-md-12">
                                    <? if ($_1_u_nf_proporcional == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    } ?>
                                    <input title="Editar proporções" type="checkbox" <?= $checked ?> name="nameproporcional" onclick="altcheck('nf','proporcional',<?= $_1_u_nf_idnf ?>,'<?= $vchecked ?>')">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <?
                        // Calcula a data daqui 3 dias
                        if (empty($_1_u_nf_diasentrada)) {
                            $_1_u_nf_diasentrada = 0;
                        }

                        $q = 10;
                        if (!empty($_1_u_nf_idnf)) {
                            $rescx = NfEntradaController::buscarIdNfConfPagar($_1_u_nf_idnf);
                            $qtdpx = count($rescx);
                            $v = 0;
                            $tproporcao = 0;
                            $count = 1;
                            foreach ($rescx as $rowcx) {
                                $q++;
                                $i++;
                                if ($_1_u_nf_tipointervalo == "M") {
                                    $strintervalo = 'MONTH';
                                } elseif ($_1_u_nf_tipointervalo == "Y") {
                                    $strintervalo = 'YEAR';
                                } else {
                                    $strintervalo = 'days';
                                }
                                $q++;
                                if ($v == 0) {
                                    $dias = $_1_u_nf_diasentrada;
                                } else {

                                    $dias = $_1_u_nf_diasentrada + ($_1_u_nf_intervalo * $v);
                                }
                                $pvdate = $_1_u_nf_dtemissao;
                                $pvdate = str_replace('/', '-', $pvdate);
                                $timestamp = strtotime(date('Y-m-d', strtotime($pvdate)) . "+" . $dias . " " . $strintervalo . "");

                                //verificar se a data e sabado ou domingo
                                /*
                                $sqldia = "SELECT DAYOFWEEK('" . date('Y-m-d', $timestamp) . "') as diasemana;";
                                $resdia = d::b()->query($sqldia) or die("Erro ao buscar dia da semana");
                                $rowdia = mysqli_fetch_assoc($resdia);

                                if ($rowdia['diasemana'] == 1) { //Se for domingo aumenta 1 dia
                                    $timestamp = strtotime(date('Y-m-d', $timestamp) . "+1 days");
                                } elseif ($rowdia['diasemana'] == 7) { //Se for sabado aumenta 2 dias
                                    $timestamp = strtotime(date('Y-m-d', $timestamp) . "+2 days");
                                }
                                */

                                $eFeriado = 1;

                                while ($eFeriado >= 1) {

                                    /*
                                    $sqldia = " SELECT verificaFeriadoFds('" . date('Y-m-d', $timestamp) . "' ) as eFeriado;";
                                    $resdia = d::b()->query($sqldia) or die("Erro ao buscar dia da semana");
                                    */
                                    $rowdia =  NFController::verificaFeriadoFds(date('Y-m-d', $timestamp));

                                    if ($rowdia['eFeriado'] == 1) {
                                        $timestamp = strtotime(date('Y-m-d', $timestamp) . "+1 days");
                                        $eFeriado = 1;
                                    } else {
                                        $eFeriado = 0;
                                    }
                                }

                                if (empty($rowcx['dmadatareceb'])) {
                                    $rowcx['dmadatareceb'] = date('d/m/Y', $timestamp);
                                }

                                $proporcao = 100 / $_1_u_nf_parcelas;
                                if (empty($rowcx['proporcao'])) {
                                    $rowcx['proporcao'] = $proporcao;
                                }
                                $tproporcao = $tproporcao + $rowcx['proporcao'];
                                // Exibe o resultado
                        ?>
                                <div class="col-md-4">
                                    <div class="col-md-12">
                                        <div class="col-md-5">
                                            <font color="red"><? echo (($v + 1) . "º"); ?>:</font>
                                            <input style="width: 100px;" name="_<?= $q ?>_u_nfconfpagar_idnfconfpagar" type="hidden" value="<?= $rowcx['idnfconfpagar'] ?>">
                                            <input class="size7 calendario dataconfdate" id="dataconf<?= $rowcx['idnfconfpagar'] ?>" idnfconfpagar="<?= $rowcx['idnfconfpagar'] ?>" name="_<?= $q ?>_u_nfconfpagar_datareceb" type="text" value="<?= $rowcx['dmadatareceb'] ?>">
                                            <?
                                            if ($rowcx['dmadatareceb'] != date('d/m/Y', $timestamp)) {
                                            ?>
                                                &nbsp;<?= date('d/m/Y ', $timestamp) ?>&nbsp;<i class="fa fa-exclamation-triangle laranja" title="Valor sugerido pelo Sistema"></i>
                                            <?
                                            }
                                            ?>
                                        </div>
                                        <? if ($_1_u_nf_proporcional == 'Y') { ?>
                                            <div class="col-md-2 alinhar-texto-cabecalho">
                                                Proporção:
                                            </div>
                                            <div class="col-md-2">
                                                <input class="size4" name="_<?= $q ?>_u_nfconfpagar_proporcao" type="text" value="<?= round($rowcx['proporcao'], 2) ?>" onchange="atualizaproporcao(this,<?= $rowcx['idnfconfpagar'] ?>)">
                                            </div>
                                        <? } ?>
                                        <div class="col-md-1">
                                            <? if (empty($rowcx['obs'])) { ?>
                                                <i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde bnt-lg-sete pointer" onclick="nfconfpagar(<?= $rowcx['idnfconfpagar'] ?>,<?= $q ?>)" title="Inserir observação."></i>
                                            <? } else { ?>
                                                <div class="observacao">
                                                    <i data-target="webuiPopover0" class="fa fa-info-circle fa-1x azul pointer hoverpreto bnt-lg-sete tip" onclick="nfconfpagar(<?= $rowcx['idnfconfpagar'] ?>,<?= $q ?>)"></i>
                                                </div>
                                                <div class="webui-popover-content">
                                                    <b>Obs:</b> <?= $rowcx['obs'] ?> <br />
                                                    <b>Alterado em:</b> <?= dmahms($rowcx['alteradoem']) ?><br />
                                                    <b>Alterado por:</b> <?= $rowcx['alteradopor'] ?><br />
                                                </div>
                                            <? } ?>
                                        </div>
                                        <div class="col-md-2 alinhar-texto-cabecalho">
                                            <font color="red"><?= round($tproporcao, 2) ?></font>
                                        </div>

                                        <div id="<?= $q ?>_editarnfconfpagar" class="hide">
                                            <table>
                                                <tr>
                                                    <td>
                                                        <textarea name="<?= $q ?>_nfconfpagar_obs" id="<?= $q ?>_nfconfpagar_obs" style="width: 570px; height: 41px; margin: 0px;"><?= $rowcx['obs'] ?></textarea>
                                                        <input id="<?= $q ?>_nfconfpagar_idnfconfpagar" name="<?= $q ?>_nfconfpagar_idnfconfpagar" type="hidden" value="<?= $rowcx['idnfconfpagar'] ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="panel panel-default">
                                                                    <div class="panel-body">
                                                                        <div class="row col-md-12">
                                                                            <div class="col-md-2" style="text-align:right">Alterado Por:</div>
                                                                            <div class="col-md-4" style="text-align:left"><?= $rowcx['alteradopor'] ?></div>
                                                                            <div class="col-md-2" style="text-align:right">Alterado Em:</div>
                                                                            <div class="col-md-4" style="text-align:left"><?= dmahms($rowcx['alteradoem']); ?></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                        <?
                                $v++;
                                if ($count % 3 == 0) {
                                    echo '</div><div class="col-md-12">';
                                }
                                $count++;
                            } //for ($v = 0; $v < $_1_u_nf_parcelas; $v++) {                            
                        }
                        ?>
                    </div>
                <?
                } //if($_1_u_nf_geracontapagar=="Y"){
                ?>
            </div>
        </div>
    </div>
</div>

<? if (!empty($_1_u_nf_idnf) and $_1_u_nf_tipoorc != "COBRANCA") { ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading" data-toggle="collapse" href="#Multiplicar">Multiplicar</div>
                <div class="panel-body">
                    <div id="Multiplicar" class="collapse">
                        <div class="col-md-12">
                            <div class="col-md-12">
                                <div class="form-group col-md-2">
                                    <label>Qtd. Vezes:</label>
                                    <div class="input-group col-md-12">
                                        <select name="qtdvezes" id="qtdvezes" class="size6">
                                            <?
                                            for ($selIntervalo = 1; $selIntervalo <= 120; $selIntervalo++) {
                                                if ($selIntervalo == 1) {
                                                    $selectIntervalo = "select " . $selIntervalo . ",'" . $selIntervalo . "x' ";
                                                } else {
                                                    $selectIntervalo .= "union select " . $selIntervalo . ",'" . $selIntervalo . "x' ";
                                                }
                                            }
                                            fillselect($selectIntervalo, $_1_u_nf_parcelas);
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Intervalo de:</label>
                                    <div class="input-group col-md-12">
                                        <input name="intervalo" type="text" class="size5" value="">&nbsp
                                        <select name="tipointervalo" class="size5">
                                            <?
                                            fillselect(NfEntradaController::$_periodo);
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                    <div class="input-group col-md-12">
                                        <button id="cbSalvar" type="button" class="btn btn-danger btn-xs" onclick="multiplicarnf(<?= $_1_u_nf_idnf ?>)" title="Multiplicar">
                                            <i class="fa fa-circle"></i>Multiplicar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?
                            $listarNfFluxo = NfEntradaController::buscarNfEFluxoStatusPorTipoObjetoSoliPor($_1_u_nf_idnf, 'nf', $_1_u_nf_tiponf);
                            if ($listarNfFluxo['qtdLinhas'] > 0) {
                            ?>
                                <table class="table table-striped planilha" style="margin-top:10px;">
                                    <tr>
                                        <th>Cópia(s)</th>
                                        <th>Nº NF</th>
                                        <th>Emissão</th>
                                        <th>Status</th>
                                    </tr>
                                    <? $z = 0;
                                    foreach ($listarNfFluxo['dados'] as $_nfFluxo) {
                                        if ($_nfFluxo["tiponf"] == 'C') {
                                            $vtiponf = "Compra";
                                            $link = "nfentrada";
                                        }
                                        if ($_nfFluxo["tiponf"] == 'O') {
                                            $vtiponf = "Compra";
                                            $link = "nfentrada";
                                        }
                                        if ($_nfFluxo["tiponf"] == 'S') {
                                            $vtiponf = "Servi&ccedil;o";
                                            $link = "nfentrada";
                                        }
                                        if ($_nfFluxo["tiponf"] == 'T') {
                                            $vtiponf = "Cte";
                                            $link = "nfentrada";
                                        }
                                        if ($_nfFluxo["tiponf"] == 'E') {
                                            $vtiponf = "Consession&aacute;ria";
                                            $link = "nfentrada";
                                        }
                                        if ($_nfFluxo["tiponf"] == 'M') {
                                            $vtiponf = "Manual/Cupom";
                                            $link = "nfentrada";
                                        }
                                        if ($_nfFluxo["tiponf"] == 'B') {
                                            $vtiponf = "Recibo";
                                            $link = "nfentrada";
                                        }
                                        if ($_nfFluxo["tiponf"] == 'R') {
                                            $vtiponf = "PJ";
                                            $link = "comprasrh";
                                        }
                                        if ($_nfFluxo["tiponf"] == 'F') {
                                            $vtiponf = "Fatura";
                                            $link = "nfentrada";
                                            $tipo = 'F';
                                        }
                                        if ($_nfFluxo["tiponf"] == 'D') {
                                            $vtiponf = "Sócios";
                                            $link = "comprassocios";
                                            $tipo = 'D';
                                        }

                                        $z = $z + 1;
                                    ?>
                                        <tr>
                                            <td>
                                                <a class="hoverazul pointer" title="Compra" onclick="janelamodal('?_modulo=<?= $link ?>&_acao=u&idnf=<?= $_nfFluxo['idnf'] ?>&_idempresa=<?= $_nfFluxo['idempresa'] ?>')"><?= $_nfFluxo["idnf"] ?></a>
                                            </td>
                                            <td>
                                                <input name="_l<?= $z ?>_u_nf_idnf" type="hidden" value="<?= $_nfFluxo["idnf"] ?>">
                                                <input name="_l<?= $z ?>_u_nf_nnfe" type="text" value="<?= $_nfFluxo["nnfe"] ?>">
                                            </td>
                                            <td>
                                                <input name="_l<?= $z ?>_u_nf_dtemissao" type="text" class="calendario" value="<?= dma($_nfFluxo["dtemissao"]) ?>">
                                            </td>
                                            <td>
                                                <?= $_nfFluxo["status"] ?>
                                            </td>
                                        </tr>
                                    <?
                                    }
                                    ?>
                                </table>
                            <?
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?
}
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" href="#Transferir">Transferir</div>
            <div class="panel-body">
                <? $sqlt = "SELECT idnf FROM nf WHERE idobjetosolipor = " . $_1_u_nf_idnf . " AND tipoobjetosolipor = 'nfentradatransferencia'";
                $rest = d::b()->query($sqlt) or die("Falha ao consultar tarifa: " . mysqli_error(d::b()) . "<p>SQL: $sqlt");
                $qtdt = mysqli_num_rows($rest);
                $rowr = mysqli_fetch_assoc($rest); ?>
                <? if ($qtdt > 0) { ?>
                    <button class="btn btn-default btn-xs" id="transferir" onclick="transferido('')">
                        <i class="fa fa-circle"></i> Transferido
                    </button>
                <? } else { ?>
                    <button class="btn btn-warning btn-xs" onclick="transferir()">
                        <i class="fa fa-circle"></i> Transferir
                    </button>
                <? } ?>
            </div>
        </div>
    </div>
</div>

<script>
    function transferir() {
        $.ajax({
            type: "POST",
            url: "ajax/htmltransferirconta.php?valortotal=" + $('#vlrtotal').val(),
            data: {
                valortotal: $('#vlrtotal').val(),
                dtremessa: '',
            },

            success: function(data) {
                CB.modal({
                    titulo: "</strong>Transferência <button type='button' class='btn btn-warning btn-xs' onclick='transferirconta();'><i class='fa fa-circle'></i>Confirmar Transferência</button></strong>",
                    corpo: data,
                    classe: 'sessenta',
                    aoAbrir: function(vthis) {
                        $(".calendario").daterangepicker({
                            "singleDatePicker": true,
                            "showDropdowns": true,
                            "linkedCalendars": false,
                            "opens": "left",
                            "locale": {
                                format: 'DD/MM/YYYY'
                            }
                        }).on('apply.daterangepicker', function(ev, picker) {
                            console.log(picker.startDate.format('DD/MM/YYYY hh:mm:ss'));
                            $(this).val(picker.startDate.format('DD/MM/YYYY hh:mm:ss'));
                        });
                    }
                });
            },
        })
    }

    function transferirconta() {
        var dados = $('#dadostranferencia').find(':input').serialize();
        dados += "&_transf_u_nf_idnf=" + $("#idnf").val();
        dados += "&_transf_u_nf_status=" + $("#statusnota").val();
        dados += "&_orig_i_nf_idpessoa=" + $("[name=_orig_i_nf_idpessoa]").attr('cbvalue');
        dados += "&_dest_i_nf_idpessoa=" + $("[name=_dest_i_nf_idpessoa]").attr('cbvalue');

        CB.post({
            objetos: dados,
            parcial: true,
            posPost: function(resp, status, ajax) {

                $.ajax({
                    type: "post",
                    url: "ajax/htmlresultadotransferencia.php",
                    data: {
                        idretornoremessa: $("#idnf").val(),
                        local: 'nfentradatransferencia',
                    },

                    success: function(data) {
                        CB.modal({
                            titulo: "</strong>Resultado transferência</strong>",
                            corpo: data,
                            classe: 'sessenta',
                        });
                    },
                })
            }
        })
    }

    function transferido() {
        $.ajax({
            type: "post",
            url: "ajax/htmlresultadotransferencia.php",
            data: {
                idretornoremessa: $("#idnf").val(),
                local: 'nfentradatransferencia',
            },
            success: function(data) {
                CB.modal({
                    titulo: "</strong>Resultado transferência</strong>",
                    corpo: data,
                    classe: 'sessenta',
                });
            },
        })
    }
</script>

<? require_once('../form/js/comprapagamento_js.php'); ?>