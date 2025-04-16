<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/planejamentoprodserv_controller.php");


if ($_POST) {
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "prodserv";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idprodserv" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT * FROM prodserv  WHERE idprodserv = #pkid";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$vari = 1;
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <table>
                    <tr>
                        <td align="right"><strong>Sigla:</strong></td>
                        <td nowrap>
                            <label class="alert-warning"><?= $_1_u_prodserv_codprodserv ?></label>
                        </td>
                        <td align="right"><strong>Descrição:</strong></td>
                        <td nowrap>
                            <label class="alert-warning"><?= $_1_u_prodserv_descr ?>
                                <a title="Abrir Prodserv" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=prodserv&idprodserv=<?= $_1_u_prodserv_idprodserv ?>" target="_blank" style="margin: 0 4px;"></a>
                            </label>
                        </td>
                        <? if (!empty($_1_u_prodserv_un)) { ?>
                            <td align="right"><strong>Unidade Padrão:</strong></td>
                            <td nowrap>
                                <label class="alert-warning">
                                    <?= traduzid('unidadevolume', 'un', 'descr', $_1_u_prodserv_un) ?>
                                </label>
                            </td>
                        <? } ?>
                    </tr>
                </table>
            </div>
            <div class="panel-body">
            <?$meses = array(1 => 'JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ');
                if(PlanejamentoProdServController::verificaProdservComFormulaEPlanejamentoSem($_1_u_prodserv_idprodserv)){?>
                <div class="panel panel-default">
                    <div class="panel-heading pointer" data-toggle="collapse" href="#planejamentoformula<?=$_1_u_prodserv_idprodserv?>">Fórmula - Geral</div>
                    <div class="panel-body" id="planejamentoformula<?=$_1_u_prodserv_idprodserv?>">
                        <?$listarUnidadesEmpresa = PlanejamentoProdServController::buscarUnidadesPorUnidadeObjeto($_1_u_prodserv_idprodserv, 'prodserv', "  AND u.requisicao = 'Y' and u.idtipounidade !=3 AND u.idempresa = " . $_1_u_prodserv_idempresa);
                        foreach ($listarUnidadesEmpresa as $unidadeEmpresa) { ?>
                            <div class="col-sm-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading " style="display: inline-flex; align-items: center; width: 100%; justify-content: space-between;">
                                        <div>
                                            <?= $unidadeEmpresa['unidade'] ?>
                                        </div>
                                    </div>
                                    <div class="panel-body" style="padding-top: 2px !important;">
                                        <?

                                        $lExercicio = PlanejamentoProdServController::buscarPlanejamentoprodservExercicio($_1_u_prodserv_idprodserv, $unidadeEmpresa['idunidade'], null);
                                        sort($lExercicio);
                                        foreach ($lExercicio as $exercicio) {

                                            $lMes = PlanejamentoProdServController::buscarPlanejamentoprodservMes($_1_u_prodserv_idprodserv, $unidadeEmpresa['idunidade'], $exercicio['exercicio'], null);
                                        ?>

                                            <table style="width: 100%;">
                                                <tr>
                                                    <td style="width: 7%;"></td>
                                                    <td style="width: 7%;"><b>MÊS</b></td>
                                                    <?
                                                    $m = 0;
                                                    foreach ($lMes as $mes) {
                                                        $m++;
                                                        $vari++;
                                                    ?>
                                                        <td style="width: 7%;" class="nowrap">
                                                            <div class="col-md-4" style="padding-left: 2px;">
                                                                <?= $meses[$m] ?>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <? if (!empty($mes['planejado'])) { ?>
                                                                    <i class="fa fa-pencil pointer" title='Editar Planejamento' onclick="alteravalor('planejado','<?= $mes['planejado'] ?>','modulohistorico',<?= $mes['idplanejamentoprodserv'] ?>,'Planejamento:')"></i>
                                                                <? } ?>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <?
                                                                $ListarHistoricoModal = PlanejamentoProdServController::buscarHistoricoModuloAlteracao($mes['idplanejamentoprodserv'], 'planejamentoprodserv', 'planejado');
                                                                $qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
                                                                if ($qtdvh > 0) {
                                                                ?>

                                                                    <div class="historicoEnvio" idplanejamentoprodserv="<?= $mes['idplanejamentoprodserv'] ?>">
                                                                        <i title="Histórico do Planejamento" class="fa  fa-info-circle preto pointer hoverazul tip" data-target="webuiPopover0"></i>
                                                                    </div>
                                                                    <div class="webui-popover-content">
                                                                        <br />
                                                                        <table class="table table-striped planilha">
                                                                            <?
                                                                            if ($qtdvh > 0) {
                                                                            ?>
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th scope="col">De</th>
                                                                                        <th scope="col">Para</th>
                                                                                        <th scope="col">Justificativa</th>
                                                                                        <th scope="col">Por</th>
                                                                                        <th scope="col">Em</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?
                                                                                    foreach ($ListarHistoricoModal as $historicoModal) {
                                                                                    ?>
                                                                                        <tr>
                                                                                            <td><?= $historicoModal['valor_old'] ?></td>
                                                                                            <td><?= $historicoModal['valor'] ?></td>
                                                                                            <td>
                                                                                                <? echo $historicoModal['justificativa']; ?>
                                                                                            </td>
                                                                                            <td><?= $historicoModal['nomecurto'] ?></td>
                                                                                            <td><?= dmahms($historicoModal['criadoem']) ?></td>
                                                                                        </tr>
                                                                                    <?
                                                                                    }
                                                                                    ?>
                                                                                </tbody>
                                                                            <?
                                                                            }
                                                                            ?>
                                                                        </table>
                                                                    </div>
                                                                <?
                                                                }
                                                                ?>
                                                            </div>
                                                        </td>
                                                    <?
                                                    }
                                                    reset($lMes);
                                                    ?>
                                                </tr>
                                                <tr>
                                                    <td style="width: 7%;">
                                                        <label class="alert-warning"><b><?= $exercicio['exercicio'] ?></b></label>
                                                    </td>
                                                    <td style="width: 7%;"><b>Planejamento</b></td>
                                                    <?

                                                    foreach ($lMes as $mes) {
                                                        $vari++;
                                                    ?>
                                                        <td style="width: 7%;" class="nowrap">
                                                            <input name="_<?= $vari . $_1_u_prodserv_idprodserv ?>_u_planejamentoprodserv_idplanejamentoprodserv" class="hide" type="text" value="<?= $mes['idplanejamentoprodserv'] ?>">
                                                            <input name="_<?= $vari . $_1_u_prodserv_idprodserv ?>_u_planejamentoprodserv_idprodservformula" class="hide" type="text" value="<?= $mes['idprodservformula'] ?>">
                                                            <input name="_<?= $vari . $_1_u_prodserv_idprodserv ?>_u_planejamentoprodserv_planejado" <? if (!empty($mes['planejado'])) { ?> readonly="readonly" style="background-color: #ece5e5 !important;" <? } ?> class="size6" type="text" value="<?= $mes['planejado'] ?>">

                                                        </td>
                                                    <?
                                                    }
                                                    reset($lMes);
                                                    ?>
                                                </tr>

                                                <tr>
                                                    <td style="width: 7%;" class="nowrap"></td>
                                                    <td style="width: 7%;" class="nowrap"><b>Adicional %</b></td>
                                                    <?
                                                    $ad = 0;
                                                    foreach ($lMes as $mes) {
                                                        $ad++;
                                                        $vari++;
                                                    ?>
                                                        <td style="width: 7%;" class="nowrap">
                                                            <input name="_<?= $vari . $_1_u_prodserv_idprodserv?>_u_planejamentoprodserv_idplanejamentoprodserv" class="hide" type="text" value="<?= $mes['idplanejamentoprodserv'] ?>">
                                                            <? if ($ad == 1) {
                                                                $_idplanejamentoprodserv = $mes['idplanejamentoprodserv'];
                                                                $_adicional = $mes['adicional'];
                                                            ?>
                                                                <input name="_<?= $vari . $_1_u_prodserv_idprodserv?>_u_planejamentoprodserv_adicional" <? if (!empty($mes['adicional'])) { ?> readonly="readonly" style="background-color: #ece5e5 !important;" <? } ?> class="size6  adicional<?= $_1_u_prodserv_idprodserv . $unidadeEmpresa['idunidade'] . $exercicio['exercicio'] ?>" type="text" value="<?= $mes['adicional'] ?>" onchange="atualizaad(this,'adicional<?= $_1_u_prodserv_idprodserv . $unidadeEmpresa['idunidade'] . $exercicio['exercicio'] ?>')">
                                                            <? } elseif ($ad == 2) { ?>
                                                                <div class="col-md-4">
                                                                    <? if (!empty($_adicional)) { ?>
                                                                        <i class="fa fa-pencil pointer" title='Editar Adicional' onclick="alteravalor('adicional','<?= $_adicional ?>','modulohistorico',<?= $_idplanejamentoprodserv ?>,'Adicional:')"></i>
                                                                    <? } ?>
                                                                    <input name="_<?= $vari . $_1_u_prodserv_idprodserv?>_u_planejamentoprodserv_adicional" class="hide  adicional<?= $_1_u_prodserv_idprodserv . $unidadeEmpresa['idunidade'] . $exercicio['exercicio'] ?>" type="text" value="<?= $mes['adicional'] ?>" onchange="atualizaad(this,'adicional<?= $_1_u_prodserv_idprodserv . $unidadeEmpresa['idunidade'] . $exercicio['exercicio'] ?>')">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <?
                                                                    $ListarHistoricoModal = PlanejamentoProdServController::buscarHistoricoModuloAlteracao($_idplanejamentoprodserv, 'planejamentoprodserv', 'adicional');
                                                                    $qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
                                                                    if ($qtdvh > 0) {
                                                                    ?>

                                                                        <div class="historicoEnvio" idplanejamentoprodserv_adicional="<?= $_idplanejamentoprodserv ?>">
                                                                            <i title="Histórico do Adicional" class="fa fa-info-circle preto pointer hoverazul tip" data-target="webuiPopover0"></i>
                                                                        </div>
                                                                        <div class="webui-popover-content">
                                                                            <br />
                                                                            <table class="table table-striped planilha">
                                                                                <?
                                                                                if ($qtdvh > 0) {
                                                                                ?>
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th scope="col">De</th>
                                                                                            <th scope="col">Para</th>
                                                                                            <th scope="col">Justificativa</th>
                                                                                            <th scope="col">Por</th>
                                                                                            <th scope="col">Em</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <?
                                                                                        foreach ($ListarHistoricoModal as $historicoModal) {
                                                                                        ?>
                                                                                            <tr>
                                                                                                <td style="width: 7%;"><?= $historicoModal['valor_old'] ?></td>
                                                                                                <td style="width: 7%;"><?= $historicoModal['valor'] ?></td>
                                                                                                <td style="width: 7%;">
                                                                                                    <? echo $historicoModal['justificativa']; ?>
                                                                                                </td>
                                                                                                <td style="width: 7%;"><?= $historicoModal['nomecurto'] ?></td>
                                                                                                <td style="width: 7%;"><?= dmahms($historicoModal['criadoem']) ?></td>
                                                                                            </tr>
                                                                                        <?
                                                                                        }
                                                                                        ?>
                                                                                    </tbody>
                                                                                <?
                                                                                }
                                                                                ?>
                                                                            </table>
                                                                        </div>
                                                                    <?
                                                                    }
                                                                    ?>
                                                                </div>
                                                            <? } else { ?>
                                                                <input name="_<?= $vari . $_1_u_prodserv_idprodserv?>_u_planejamentoprodserv_adicional" class="hide  adicional<?= $_1_u_prodserv_idprodserv . $unidadeEmpresa['idunidade'] . $exercicio['exercicio'] ?>" type="text" value="<?= $mes['adicional'] ?>" onchange="atualizaad(this,'adicional<?= $_1_u_prodserv_idprodserv . $unidadeEmpresa['idunidade'] . $exercicio['exercicio'] ?>')">
                                                            <? } ?>
                                                        </td>
                                                    <?
                                                    }
                                                    reset($lMes);
                                                    ?>



                                                </tr>
                                                <tr>
                                                    <td style="width: 7%;"></td>
                                                    <td style="width: 7%;"></td>
                                                    <td style="width: 7%;" colspan="11"></td>
                                                    <td style="width: 7%;">

                                                    </td>
                                                </tr>
                                            </table>
                                            <hr>
                                        <?
                                        } //foreach ($lExercicio as $exercicio)
                                        ?>
                                        <div>
                                            <p style="color: red;font-size: medium;">Planejamento sem fórmula, favor selecionar fórmula para vínculo!</p>
                                            <select  class="size7" vnulo onchange="vinculaPlanementoAFormula(this)">
                                                <option  >Seleionar fórmula para vinculo</option>
                                                <? fillselect(ProdServController::toFillSelect(ProdServController::buscarIdProdservFormulaPorIdProdservArray($_1_u_prodserv_idprodserv))); ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?
                        }?>
                    </div>
                </div>
            <?}?>
                <?
                
                $formulas = PlanejamentoProdServController::buscarFormulasProdserv($_1_u_prodserv_idprodserv);
                foreach($formulas as $formula){?>
                    <div class="panel panel-default">
                        <div class="panel-heading pointer" data-toggle="collapse" href="#planejamentoformula<?=$_1_u_prodserv_idprodserv.$formula['idprodservformula']?>">Fórmula - <?=$formula['rotulo']?></div>
                        <div class="panel-body" id="planejamentoformula<?=$_1_u_prodserv_idprodserv.$formula['idprodservformula']?>">
                            <?$listarUnidadesEmpresa = PlanejamentoProdServController::buscarUnidadesPorUnidadeObjeto($_1_u_prodserv_idprodserv, 'prodserv', "  AND u.requisicao = 'Y' and u.idtipounidade !=3 AND u.idempresa = " . $_1_u_prodserv_idempresa);
                            foreach ($listarUnidadesEmpresa as $unidadeEmpresa) { ?>
                                <div class="col-sm-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading " style="display: inline-flex; align-items: center; width: 100%; justify-content: space-between;">
                                            <div>
                                                <?= $unidadeEmpresa['unidade'] ?>
                                            </div>
                                        </div>
                                        <div class="panel-body" style="padding-top: 2px !important;">
                                            <?
            
                                            $lExercicio = PlanejamentoProdServController::buscarPlanejamentoprodservExercicio($_1_u_prodserv_idprodserv, $unidadeEmpresa['idunidade'], $formula['idprodservformula']);
                                            sort($lExercicio);
                                            foreach ($lExercicio as $exercicio) {
            
                                                $lMes = PlanejamentoProdServController::buscarPlanejamentoprodservMes($_1_u_prodserv_idprodserv, $unidadeEmpresa['idunidade'], $exercicio['exercicio'], $formula['idprodservformula']);
                                            ?>
            
                                                <table style="width: 100%;">
                                                    <tr>
                                                        <td style="width: 7%;"></td>
                                                        <td style="width: 7%;"><b>MÊS</b></td>
                                                        <?
                                                        $m = 0;
                                                        foreach ($lMes as $mes) {
                                                            $m++;
                                                            $vari++;
                                                        ?>
                                                            <td style="width: 7%;" class="nowrap">
                                                                <div class="col-md-4" style="padding-left: 2px;">
                                                                    <?= $meses[$m] ?>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <? if (!empty($mes['planejado'])) { ?>
                                                                        <i class="fa fa-pencil pointer" title='Editar Planejamento' onclick="alteravalor('planejado','<?= $mes['planejado'] ?>','modulohistorico',<?= $mes['idplanejamentoprodserv'] ?>,'Planejamento:')"></i>
                                                                    <? } ?>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <?
                                                                    $ListarHistoricoModal = PlanejamentoProdServController::buscarHistoricoModuloAlteracao($mes['idplanejamentoprodserv'], 'planejamentoprodserv', 'planejado');
                                                                    $qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
                                                                    if ($qtdvh > 0) {
                                                                    ?>
            
                                                                        <div class="historicoEnvio" idplanejamentoprodserv="<?= $mes['idplanejamentoprodserv'] ?>">
                                                                            <i title="Histórico do Planejamento" class="fa  fa-info-circle preto pointer hoverazul tip" data-target="webuiPopover0"></i>
                                                                        </div>
                                                                        <div class="webui-popover-content">
                                                                            <br />
                                                                            <table class="table table-striped planilha">
                                                                                <?
                                                                                if ($qtdvh > 0) {
                                                                                ?>
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th scope="col">De</th>
                                                                                            <th scope="col">Para</th>
                                                                                            <th scope="col">Justificativa</th>
                                                                                            <th scope="col">Por</th>
                                                                                            <th scope="col">Em</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <?
                                                                                        foreach ($ListarHistoricoModal as $historicoModal) {
                                                                                        ?>
                                                                                            <tr>
                                                                                                <td><?= $historicoModal['valor_old'] ?></td>
                                                                                                <td><?= $historicoModal['valor'] ?></td>
                                                                                                <td>
                                                                                                    <? echo $historicoModal['justificativa']; ?>
                                                                                                </td>
                                                                                                <td><?= $historicoModal['nomecurto'] ?></td>
                                                                                                <td><?= dmahms($historicoModal['criadoem']) ?></td>
                                                                                            </tr>
                                                                                        <?
                                                                                        }
                                                                                        ?>
                                                                                    </tbody>
                                                                                <?
                                                                                }
                                                                                ?>
                                                                            </table>
                                                                        </div>
                                                                    <?
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </td>
                                                        <?
                                                        }
                                                        reset($lMes);
                                                        ?>
                                                    </tr>
                                                    <tr>
                                                        <td style="width: 7%;">
                                                            <label class="alert-warning"><b><?= $exercicio['exercicio'] ?></b></label>
                                                        </td>
                                                        <td style="width: 7%;"><b>Planejamento</b></td>
                                                        <?
            
                                                        foreach ($lMes as $mes) {
                                                            $vari++;
                                                        ?>
                                                            <td style="width: 7%;" class="nowrap">
                                                                <input name="_<?= $vari . $_1_u_prodserv_idprodserv . $formula['idprodservformula'] ?>_u_planejamentoprodserv_idplanejamentoprodserv" class="hide" type="text" value="<?= $mes['idplanejamentoprodserv'] ?>">
                                                                <input name="_<?= $vari . $_1_u_prodserv_idprodserv . $formula['idprodservformula'] ?>_u_planejamentoprodserv_idprodservformula" class="hide" type="text" value="<?= $formula['idprodservformula'] ?>">
                                                                <input name="_<?= $vari . $_1_u_prodserv_idprodserv . $formula['idprodservformula'] ?>_u_planejamentoprodserv_planejado" <? if (!empty($mes['planejado'])) { ?> readonly="readonly" style="background-color: #ece5e5 !important;" <? } ?> class="size6" type="text" value="<?= $mes['planejado'] ?>">
            
                                                            </td>
                                                        <?
                                                        }
                                                        reset($lMes);
                                                        ?>
                                                    </tr>
            
                                                    <tr>
                                                        <td style="width: 7%;" class="nowrap"></td>
                                                        <td style="width: 7%;" class="nowrap"><b>Adicional %</b></td>
                                                        <?
                                                        $ad = 0;
                                                        foreach ($lMes as $mes) {
                                                            $ad++;
                                                            $vari++;
                                                        ?>
                                                            <td style="width: 7%;" class="nowrap">
                                                                <input name="_<?= $vari . $_1_u_prodserv_idprodserv . $formula['idprodservformula'] ?>_u_planejamentoprodserv_idplanejamentoprodserv" class="hide" type="text" value="<?= $mes['idplanejamentoprodserv'] ?>">
                                                                <? if ($ad == 1) {
                                                                    $_idplanejamentoprodserv = $mes['idplanejamentoprodserv'];
                                                                    $_adicional = $mes['adicional'];
                                                                ?>
                                                                    <input name="_<?= $vari . $_1_u_prodserv_idprodserv . $formula['idprodservformula'] ?>_u_planejamentoprodserv_adicional" <? if (!empty($mes['adicional'])) { ?> readonly="readonly" style="background-color: #ece5e5 !important;" <? } ?> class="size6  adicional<?= $_1_u_prodserv_idprodserv . $unidadeEmpresa['idunidade'] . $exercicio['exercicio'] ?>" type="text" value="<?= $mes['adicional'] ?>" onchange="atualizaad(this,'adicional<?= $_1_u_prodserv_idprodserv . $unidadeEmpresa['idunidade'] . $exercicio['exercicio'] ?>')">
                                                                <? } elseif ($ad == 2) { ?>
                                                                    <div class="col-md-4">
                                                                        <? if (!empty($_adicional)) { ?>
                                                                            <i class="fa fa-pencil pointer" title='Editar Adicional' onclick="alteravalor('adicional','<?= $_adicional ?>','modulohistorico',<?= $_idplanejamentoprodserv ?>,'Adicional:')"></i>
                                                                        <? } ?>
                                                                        <input name="_<?= $vari . $_1_u_prodserv_idprodserv . $formula['idprodservformula'] ?>_u_planejamentoprodserv_adicional" class="hide  adicional<?= $_1_u_prodserv_idprodserv . $unidadeEmpresa['idunidade'] . $exercicio['exercicio'] ?>" type="text" value="<?= $mes['adicional'] ?>" onchange="atualizaad(this,'adicional<?= $_1_u_prodserv_idprodserv . $unidadeEmpresa['idunidade'] . $exercicio['exercicio'] ?>')">
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <?
                                                                        $ListarHistoricoModal = PlanejamentoProdServController::buscarHistoricoModuloAlteracao($_idplanejamentoprodserv, 'planejamentoprodserv', 'adicional');
                                                                        $qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
                                                                        if ($qtdvh > 0) {
                                                                        ?>
            
                                                                            <div class="historicoEnvio" idplanejamentoprodserv_adicional="<?= $_idplanejamentoprodserv ?>">
                                                                                <i title="Histórico do Adicional" class="fa fa-info-circle preto pointer hoverazul tip" data-target="webuiPopover0"></i>
                                                                            </div>
                                                                            <div class="webui-popover-content">
                                                                                <br />
                                                                                <table class="table table-striped planilha">
                                                                                    <?
                                                                                    if ($qtdvh > 0) {
                                                                                    ?>
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th scope="col">De</th>
                                                                                                <th scope="col">Para</th>
                                                                                                <th scope="col">Justificativa</th>
                                                                                                <th scope="col">Por</th>
                                                                                                <th scope="col">Em</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            <?
                                                                                            foreach ($ListarHistoricoModal as $historicoModal) {
                                                                                            ?>
                                                                                                <tr>
                                                                                                    <td style="width: 7%;"><?= $historicoModal['valor_old'] ?></td>
                                                                                                    <td style="width: 7%;"><?= $historicoModal['valor'] ?></td>
                                                                                                    <td style="width: 7%;">
                                                                                                        <? echo $historicoModal['justificativa']; ?>
                                                                                                    </td>
                                                                                                    <td style="width: 7%;"><?= $historicoModal['nomecurto'] ?></td>
                                                                                                    <td style="width: 7%;"><?= dmahms($historicoModal['criadoem']) ?></td>
                                                                                                </tr>
                                                                                            <?
                                                                                            }
                                                                                            ?>
                                                                                        </tbody>
                                                                                    <?
                                                                                    }
                                                                                    ?>
                                                                                </table>
                                                                            </div>
                                                                        <?
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                <? } else { ?>
                                                                    <input name="_<?= $vari . $_1_u_prodserv_idprodserv . $formula['idprodservformula'] ?>_u_planejamentoprodserv_adicional" class="hide  adicional<?= $_1_u_prodserv_idprodserv . $unidadeEmpresa['idunidade'] . $exercicio['exercicio'] ?>" type="text" value="<?= $mes['adicional'] ?>" onchange="atualizaad(this,'adicional<?= $_1_u_prodserv_idprodserv . $unidadeEmpresa['idunidade'] . $exercicio['exercicio'] ?>')">
                                                                <? } ?>
                                                            </td>
                                                        <?
                                                        }
                                                        reset($lMes);
                                                        ?>
            
            
            
                                                    </tr>
                                                    <tr>
                                                        <td style="width: 7%;"></td>
                                                        <td style="width: 7%;"></td>
                                                        <td style="width: 7%;" colspan="11"></td>
                                                        <td style="width: 7%;">
            
                                                        </td>
                                                    </tr>
                                                </table>
                                                <hr>
                                            <?
                                            } //foreach ($lExercicio as $exercicio)
                                            ?>
                                            <div>
                                                <i id="mais_<?=$idprodserv.$formula['idprodservformula'].$unidadeEmpresa['idunidade']?>" class="fa fa-plus-circle fa-2x verde btn-lg pointer" onclick="novoPlanejamento(<?=$idprodserv.$formula['idprodservformula'].$unidadeEmpresa['idunidade']?>)" title="Novo Planejamento"></i>
                                                <select id="select_<?=$idprodserv.$formula['idprodservformula'].$unidadeEmpresa['idunidade']?>" class="size7 hide" idprodservformula="<?= $formula['idprodservformula'] ?>" onchange="inserirPlanejamento(this,<?= $_1_u_prodserv_idprodserv ?>,<?= $unidadeEmpresa['idunidade'] ?>)">
                                                    <option value=""> Selecione para adicionar</option>
                                                    <? fillselect(PlanejamentoProdServController::buscarExercicioPorId($_1_u_prodserv_idprodserv, $unidadeEmpresa['idunidade'],$formula['idprodservformula'])); ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?
                            }?>
                        </div>
                    </div>
                <?}?>
            </div>
        </div>
    </div>
</div>
<? require_once('../form/js/planejamentoprodserv_js.php'); ?>