<?
require_once("../inc/php/functions.php");
require_once("../inc/php/laudo.php");

$vjwt = validaTokenReduzido();

$jwt = $vjwt["token"];

if ($vjwt["sucesso"] === false) {
    die("");
}
cb::idempresa();
require_once(__DIR__ . "/../form/controllers/_lp_controller.php");


$_idempresa = $_GET['_idempresa'];

$re = _LpController::buscarSiglaECorsistemaDaEmpresa($_idempresa);

$_sigla = $re['sigla'];
$_corsistema = $re['corsistema'];


if (hexdec(substr($_corsistema, 0, 2)) + hexdec(substr($_corsistema, 2, 2)) + hexdec(substr($_corsistema, 4, 2)) > 381) {
    $_cortexto = "#333";
} else {
    $_cortexto = "#fff"; //dark color
}



if ($_POST['idlpgrupo'] && $_POST['idlp']) {


    function buscarPessoasSetorDepartamentoArea($id, $tp)
    {

        return _LpController::buscarPessoasSetorDepartamentoArea($tp, $id);
    }

    function retArrModPadrao()
    {

        return _LpController::buscarModulosPadrao();
    }

    function retArrModDisponiveis($inIdLp)
    {
        return _LpController::buscarModulosDisponiveis(getidempresa('u.idempresa', 'unidade'), $inIdLp);
    }

    function retArrModSelecionados($inIdLp)
    {
        global $_sigla;
        global $_idempresa;
        return _LpController::buscarModulosSelecionados($_idempresa, $inIdLp, $_sigla);
    }

    function retArrModFilho($inIdLp, $inmod, $inidempLP)
    {

        global $_sigla;

        return _LpController::buscarModulosFilhos($inidempLP, $inIdLp, getidempresa('u.idempresa', 'unidade'), $inmod, $_sigla);
    }

    function retArrModFilho2($inIdLp, $inmod, $inidempLP)
    {
        global $_sigla;

        if ($modulovinc = getModReal($inmod)) {
            $inmod = $modulovinc;
        }

        return _LpController::buscarModulosFilhosDosFilhos($inidempLP, $inIdLp, $inmod, $_sigla);
    }


    function retArrModRep($inIdLp, $inmod)
    {

        return _LpController::buscarRepsDoModulo($inIdLp, $inmod);
    }


    $rsemp = _LpController::buscarLpEEmpresa($_POST["idlp"], $_POST['idlpgrupo']);
    foreach ($rsemp as $k => $remp) { ?>
        <div class="row d-flex flex-wrap">
            <div class="panel-default col-md-8 py-0 pl-0 pr-4">
                <div class="panel-heading bg-gray text-uppercase">
                    Configurações
                </div>
                <div class="panel-body bg-graylight">
                    <div class="row">
                        <div class="col-md-12" >
                             <button style="float:right" onclick="showModalSincronizarLp(<?=$remp['idlp']?>)" class="btn btn-success btn-sm"><i class="fa fa-upload"></i>Importar Configurações</button>
                        </div>
                        <div class="col-md-4">
                            <table>
                                <tr>
                                    <td>
                                        Gera grupos:
                                    </td>
                                    <td>
                                        <select onchange="alteraStatusGrupo(this,<?= $remp['idlp'] ?>)">
                                            <? fillselect(_LpController::$ArrayYN, $remp['grupo']) ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Status:
                                    </td>
                                    <td>
                                        <select onchange="alteraStatusLP(this,<?= $remp['idlp'] ?>)">
                                            <? fillselect(_LpController::$ArrayStatus, $remp['status']) ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Tipo Pessoa:
                                    </td>
                                    <td>
                                        <select name="_<?= $remp['idlp'] ?>_u__lp_idtipopessoa">
                                            <option></option>
                                            <? fillselect(TipoPessoaQuery::buscarTodosTipoPessoa(), $remp['idtipopessoa']) ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Empresas:
                                    </td>
                                    <td>
                                        <select id="lp_<?= $remp['idlp'] ?>_empresas_selectpicker" idlp='<?= $remp['idlp'] ?>' cbpost='empresa' class="selectpicker" multiple="multiple">
                                            <?
                                            $resjidempresa = _LpController::buscarLpobjetoPorEmpresa($remp['idlp'], $remp['idempresa']);
                                            $selected = '';
                                            foreach ($resjidempresa as $k1 => $rowempresa) {
                                                $selected = (($rowempresa['idlpobjeto'])) ? 'selected' : '';
                                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowempresa['empresa']) . '" value="' . $rowempresa['idempresa'] . '" >' . $rowempresa['empresa'] . '</option>';
                                            } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-2"></div>
                        <div class="col-md-6">
                            <table>
                                <tr>
                                    <td nowrap>Un. Negócio Obrigatório </td>
                                    <td>
                                        <? if ($remp['flagobrigatoriofiltro'] == 'Y') { ?>
                                            <i style="padding-right: 0px;" class="fa fa-check-square-o fa-1x btn-lg pointer fleft" onclick="alttipocontato('flagobrigatoriofiltro','N',<?= $remp['idlp'] ?>);" Title="Se marcado este campo as pesquisas só listaram informações dos tipos de pessoa marcados abaixo."></i>
                                        <? } else { ?>
                                            <i style="padding-right: 0px;" class="fa fa-square-o fa-1x btn-lg pointer fleft" onclick="alttipocontato('flagobrigatoriofiltro','Y',<?= $remp['idlp'] ?>);" Title="Se marcado este campo as pesquisas só listaram informações dos tipos de pessoa marcados abaixo."></i>
                                        <? } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Un Negócio(s):</td>
                                    <td>
                                        <select id="lp_<?= $remp['idlp'] ?>_empresas_selectpicker" idlp='<?= $remp['idlp'] ?>' cbpost='plantelobjeto' class="selectpicker" multiple="multiple">
                                            <? $resu = _LpController::buscarPlanteis($remp['idlp'], getidempresa('u.idempresa', 'plantel'));
                                            foreach ($resu as $k => $rowu) {
                                                $selected = (($rowu['idplantelobjeto'])) ? 'selected' : '';
                                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowu['plantel']) . '" value="' . $rowu['idplantel'] . '" >' . $rowu['plantel'] . '</option>';
                                            } ?>
                                        </select>

                                    </td>
                                </tr>
                                <? if ($remp['flagobrigatoriofiltro'] == "Y") { ?>
                                    <tr>
                                        <td>
                                            Contato Obrigatório
                                        </td>
                                        <td>
                                            <? if ($remp['flagobrigatoriocontato'] == "Y") { ?>
                                                <i style="padding-right: 0px;" class="fa fa-check-square-o fa-1x  pointer" onclick="alttipocontato('flagobrigatoriocontato','N',<?= $remp['idlp'] ?>);" Title="Se marcado este campo as pesquisas só listarão os contatos ligados ao pessoa.">&nbsp;&nbsp;Obrigatório Contato</i>
                                            <? } else { ?>
                                                <i style="padding-right: 0px;" class="fa fa-square-o fa-1x pointer" onclick="alttipocontato('flagobrigatoriocontato','Y',<?= $remp['idlp'] ?>);" Title="Se marcado este campo as pesquisas só listarão os contatos ligados ao pessoa.">&nbsp;&nbsp;Obrigatório Contato</i>
                                            <? } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Tipo Contato
                                        </td>
                                        <td>
                                            <select id="lp_<?= $remp['idlp'] ?>_empresas_selectpicker" cbpost='tipopessoa' idlp='<?= $remp['idlp'] ?>' class="selectpicker" multiple="multiple">
                                                <? $resAg = _LpController::buscarTipopessoaVinculadoALp($remp['idlp']);
                                                foreach ($resAg as $k => $rowAg) {
                                                    $selected = (($rowAg['idobjetovinculo'])) ? 'selected' : '';
                                                    echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowAg['tipopessoa']) . '" value="' . $rowAg['idtipopessoa'] . '" >' . $rowAg['tipopessoa'] . '</option>';
                                                } ?>
                                            </select>
                                        </td>
                                    </tr>
                                <? } ?>
                                <tr>
                                    <td>
                                        Agência
                                    </td>
                                    <td>
                                        <select id="lp_<?= $remp['idlp'] ?>_empresas_selectpicker" cbpost='agencia' idlp='<?= $remp['idlp'] ?>' class="selectpicker" multiple="multiple">
                                            <? $resAg = _LpController::buscarAgencias($remp['idlp'], $_idempresa, $remp['habilitarmatriz']);
                                            foreach ($resAg as $k => $rowAg) {
                                                $selected = (($rowAg['idobjetovinculo'])) ? 'selected' : '';
                                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowAg['agencia']) . '" value="' . $rowAg['idagencia'] . '" >' . $rowAg['sigla'] . ' - ' . $rowAg['agencia'] . '</option>';
                                            } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Categoria
                                    </td>
                                    <td>
                                        <select id="lp_<?= $remp['idlp'] ?>_empresas_selectpicker" cbpost='contaitem' idlp='<?= $remp['idlp'] ?>' class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                                            <? $resCi = _LpController::buscarContaitem($remp['idlp'], $_idempresa, $remp['habilitarmatriz']);
                                            foreach ($resCi as $k => $rowCi) {
                                                $selected = (($rowCi['idobjetovinculo'])) ? 'selected' : '';
                                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowCi['contaitem']) . '" value="' . $rowCi['idcontaitem'] . '" >' . $rowCi['sigla'] . ' - ' . $rowCi['contaitem'] . '</option>';
                                            } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Forma de Pagamento
                                    </td>
                                    <td>
                                        <select id="lp_<?= $remp['idlp'] ?>_empresas_selectpicker" cbpost='formapagamento' idlp='<?= $remp['idlp'] ?>' class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                                            <? $resCi = _LpController::buscarFormaPagamento($remp['idlp'], $_idempresa);
                                            foreach ($resCi as $k => $rowCi) {
                                                $selected = (($rowCi['idobjetovinculo'])) ? 'selected' : '';
                                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowCi['descricao']) . '" value="' . $rowCi['idformapagamento'] . '" >' . $rowCi['sigla'] . ' - ' . $rowCi['descricao'] . '</option>';
                                            } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <div class="hidden" id="lps_adicionar_<?= $remp['idlp'] ?>">
                                        <div class="plantel hidden">

                                        </div>
                                        <div class="tipopessoa hidden">

                                        </div>
                                        <div class="agencia hidden">

                                        </div>
                                        <div class="contaitem hidden">

                                        </div>
                                        <div class="empresa hidden">

                                        </div>
                                    </div>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-12">
                            <table class="w-100">
                                <tr>
                                    <td>
                                        Observação:
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <textarea rows="8" name="_<?= $remp['idlp'] ?>_u__lp_obs"><?= nl2br($remp['obs']) ?></textarea>
                                        <input type="hidden" name="_<?= $remp['idlp'] ?>_u__lp_idlp" value="<?= $remp['idlp'] ?>">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 p-0">
                <div class="panel-default">
                    <div class="panel-heading text-uppercase" data-toggle="collapse" href="#participantes_lp">Participantes</div>
                    <div class="panel-body bg-graylight" id="participantes_lp">
                        <div class="col-md-12">
                            <input class="compacto funcsetdeptvinc" type="text" placeholder="Selecione">
                        </div>
                        <div class="col-md-12">
                            <div class="panel panel-default" style="background:#fff;height: 100%;overflow: auto;font-size:11px;">
                                <?
                                $re = _LpController::buscarPessoasPorTipoPessoa($remp['idtipopessoa']);
                                if (count($re) > 0) { ?>
                                    <div class="col-md-12">
                                        <?= traduzid('tipopessoa', 'idtipopessoa', 'tipopessoa', $remp['idtipopessoa'], false) ?>
                                    </div>
                                    <? foreach ($re as $k => $rws) { ?>
                                        <div class="col-md-12">
                                            <div class="col-md-11">
                                                <?= empty($rws['nomecurto']) ? $rws['nome'] : $rws['nomecurto'] ?>
                                            </div>
                                            <div class="col-md-1"></div>
                                        </div>
                                    <? }
                                }
                                $rs = _LpController::buscarObjetosVinculadosALp($_POST["idlp"]);
                                foreach ($rs as $k => $rw) {
                                    if ($rw['tipoobjeto'] == 'pessoa') { ?>
                                        <div class="col-md-12">
                                            <div class="col-md-11">
                                                <?=traduzid('empresa', 'idempresa', 'sigla', traduzid('pessoa', 'idpessoa', 'idempresa', $rw['idobjeto'])). ' - '. traduzid('pessoa', 'idpessoa', 'IFNULL(nomecurto,nome)', $rw['idobjeto']) ?>
                                            </div>
                                            <div class="col-md-1">
                                                <i class="fa fa-trash hoververmelho pointer" style="float:right" onclick="desvincularpessoaSetDeptArea(<?= $rw['idlpobjeto'] ?>)"></i>
                                            </div>
                                        </div>

                                        <?
                                    } else {
                                        $pessoas = buscarPessoasSetorDepartamentoArea($rw['idobjeto'], $rw['tipoobjeto']);
                                        if ($pessoas and count($pessoas) > 0) { ?>
                                            <div class="col-md-12">
                                                <fieldset class="scheduler-border">
                                                    <legend class="scheduler-border">
                                                        <?= $pessoas['nome'] ?>
                                                        <i class="fa fa-trash hoververmelho pointer" style="float:right" onclick="desvincularpessoaSetDeptArea(<?= $rw['idlpobjeto'] ?>)"></i>
                                                    </legend>
                                                    <?
                                                    foreach ($pessoas['pessoas'] as $k => $v) { ?>
                                                        <div class="col-md-12"><?= $v['pessoa'] ?></div>
                                                    <? } ?>
                                                </fieldset>
                                            </div>
                                <? }
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading pointer" onclick="abreModalMod(<?= $remp['idlp'] ?>,<?= $_POST['idlpgrupo'] ?>,<?= $_idempresa ?>)">Módulos Selecionados</div>
                </div><!-- Modulos -->
            </div>
        </div>

        <div class="">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading col-md-12 pointer" onclick="abreModalDash(<?= $remp['idlp'] ?>,<?= $_POST['idlpgrupo'] ?>,<?= $_idempresa ?>)">Dashboards</div>
                </div>
            </div>
        </div>
<!-- INÍCIO - DashBoards -->
<!-- FIM - DashBoards -->
<? require_once(__DIR__ . "/../form/js/getLp_js.php") ?>
<? } ?>
<? } ?>