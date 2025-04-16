<?php
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/assinatura_controller.php");
require_once("../form/controllers/fluxo_controller.php");
require_once("../form/controllers/prodserv_controller.php");
require_once("../form/controllers/planejamentoprodserv_controller.php");

if ($_POST) {
    require_once("../inc/php/cbpost.php");
}

$_acao = d::b()->real_escape_string($_GET['_acao']);
$_modulo = d::b()->real_escape_string($_GET['_modulo']);
$_idempresa = d::b()->real_escape_string($_GET['_idempresa']);

$idforecastcompra = d::b()->real_escape_string($_GET['idforecastcompra']);
$contaitem = d::b()->real_escape_string($_GET['contaitem']);

const MESES = ['JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'];
//$totalGeral = array_fill(1, 12, 0);

$pagvaltabela = "forecastcompra";
$pagvalcampos = ["idforecastcompra" => "pk"];

$pagsql = "select * from " . $pagvaltabela . " where id" . $pagvaltabela . " = #pkid";

include_once("../inc/php/controlevariaveisgetpost.php");

function criaInput($subcategoria, $acao, $month, $value, &$totallinha, &$total)
{
    $value = floatval($value) ?? 0;
    $monthIndex = array_search($month, MESES) + 1;
    $totallinha += $value;
    $total[$monthIndex] += $value;
    $inputName = "_{$subcategoria['idtipoprodserv']}_{$acao}_planejamentocompra_" . strtolower($month);
    //só se pode alterar o que não foi alterado pelo cron
    if ($subcategoria['altera'] == 'N') {
        return "<td class='text-right'>{$value}</td>";
    } else {
        return "<td><input readonly type='text' name='{$inputName}' value='{$value}' class='text-right'></td>";
    }
} ?>

<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="d-flex flex-between align-items-center p-2">
                <span class="title" style="font-size: large;">FORECAST DE COMPRAS<i class="btn fa fa-info-circle"
                        title='Condições: PRODUTO: Ativo (+) COMPRADO: Sim (+) CATEGORIA: Ativo'></i></span>
                <div>
                    <label class="alert-warning" title="<?= $_1_u_forecastcompra_exercicio ?>" id="statusButton"><?= $_1_u_forecastcompra_exercicio ?></label>
                    <td>Versão: </td>
                    <label class="alert-warning" id="statusButton"><?= $_GET['versao'] ?></label>
                </div>
            </div>
        </div>
    </div>
</div>

<? 
$sqlhist = "select * from objetojson where idobjeto=".$_GET['idforecastcompra']." and tipoobjeto='forecastcompra' and versaoobjeto=".$_GET['versao'];
$res = d::b()->query($sqlhist) or die("Erro ao recuperar json: ".mysqli_error(d::b()));
$row = mysqli_fetch_assoc($res);
$categorias = unserialize(base64_decode($row["jobjeto"]));
?>
<style>
    #cbModal.url .modal-body {
        line-height: initial;
        text-align: initial;
        padding: 0px;
        position: relative;
        height: 99.7%;
    }

    .modal-footer {
        display: none;
    }

    #cbModal.url .modal-body {
        line-height: initial;
        text-align: initial;
        padding: 0px;
        position: relative;
        height: 99.7%;
    }
    .scroll-offset {
        padding-top: 30px;
        /* Ajuste a altura conforme necessário */
        margin-top: -30px;
        /* Ajuste a altura conforme necessário */
    }
    td {
        vertical-align: middle !important;
    }

    td:has(input) {
        padding: 0px !important;
        border: 0px solid #cccccc;
        border-radius: 0px;
        background-color: #fff !important;
    }

    td input {
        width: 100%;
        border: 0px solid #cccccc !important;
        border-radius: 0px !important;
        text-align: center;
        min-height: 33px;
    }

    .row {
        margin-left: 0px !important;
        margin-right: 0px !important;
    }

    td.subgrupo {
        cursor: pointer;
        text-align: left;
        background-color: rgb(206 205 205 / 30%) !important;
    }

    td.subgrupo a {
        padding-top: 8px !important;
        padding-bottom: 8px !important;
    }

    .table {
        background-color: whitesmoke !important;
    }

    .table.collapse {
        border-style: hidden;
    }

    td.subgrupo:hover {
        background-color: rgb(205 205 205 / 50%);
    }

    #resultadoCount {
        font-size: 14px;
        display: flex;
        align-items: center;
        color: #333;
    }

    .naoplanejados {
        background-color: #f0bfbf !important;
    }

    .emplanejamento {
        background-color: rgb(238 161 5 / 40%) !important;
    }

    .planejado {
        background-color: rgb(105 183 105 / 40%) !important;
    }

    .panel-body {
        padding-top: 10px !important;
    }

    table>thead>tr.active>th {
        background-color: #f5f5f5;
        padding: 3px;
        vertical-align: sub;
    }

    .justify-content-between {
        justify-content: space-between;
    }

    a i.fa-arrows-v {
        margin-right: 0.5rem !important;
        font-size: medium;
        align-self: center;
        margin-bottom: 2px;
    }

    table {
        border: 1px solid #C0C0C0;
        color: #757575;
        background-color: #EEEEEE;
    }

    tr.active td {
        font-weight: bold;
    }

    .panel-heading.resultados {
        background-color: #989898;
        color: #FAFAFA
    }

    td.td-marcador {
        display: flex;
        align-items: center;
    }

    .no-borders-left {
        border-left: 1px solid #f5f5f500 !important;
        border-top: 1px solid #f5f5f500 !important;
    }

    .no-borders-bottom {
        border-bottom: 1px solid #f5f5f500 !important;
    }

    .marcador {
        background: red;
        width: 7px;
        height: 32px;
        align-content: center;
        left: 4px;
        position: absolute;
        border-radius: 4px 0 0 4px;
    }

    .bg-green {
        background-color: green !important;
    }
    .bg-silver {
        background-color: silver !important;
    }

    .bold-text * {
        font-weight: bold;
        color: #000000cc;
    }
</style>
<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading resultados">
            <div class="d-flex flex-between align-items-center p-2">
                <span class="title" style="font-size: medium;">RESULTADOS - <?= $_1_u_forecastcompra_exercicio ?> </span>
            </div> 
        </div>

        <div class="panel-body">
            <div class="">
                <div class="col-md-12"></div>
                <div class="col-md-12">
                    <div class="table-responsive form-label">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="bold-text">

                                <input readonly class="size8" type="hidden" value="<?=$_1_u_forecastcompra_idempresa?>" name="_1_<?=$_acao ?>_forecastcompra_idempresa">
                                <input readonly class="size8" type="hidden" value="<?=$_1_u_forecastcompra_idforecastcompra?>" name="_1_<?=$_acao ?>_forecastcompra_idforecastcompra">
                                <input readonly class="size8" type="hidden" value="<?=$_1_u_forecastcompra_idforecastvenda?>" name="_1_<?=$_acao ?>_forecastcompra_idforecastvenda">
                                <input readonly class="size8" type="hidden" value="<?=$_1_u_forecastcompra_exercicio?>" name="_1_<?=$_acao ?>_forecastcompra_exercicio">
                                <input readonly class="size8" type="hidden" value="<?=$_1_u_forecastcompra_status?>" name="_1_<?=$_acao ?>_forecastcompra_status">
                                <input readonly class="size8" type="hidden" value="<?=$_1_u_forecastcompra_versao?>" name="_1_<?=$_acao ?>_forecastcompra_versao">  
                                <input readonly class="size8" type="hidden" value="<?=$contaitem?>" name="idcategoria">  
                                

                                    <th class="no-borders-left no-borders-bottom" style="width:25%;text-align: left;" colspan="2"></th>
                                    <th style="width:6%;text-align: center;">JANEIRO</th>
                                    <th style="width:6%;text-align: center;">FEVEREIRO</th>
                                    <th style="width:6%;text-align: center;">MARÇO</th>
                                    <th style="width:6%;text-align: center;">ABRIL</th>
                                    <th style="width:6%;text-align: center;">MAIO</th>
                                    <th style="width:6%;text-align: center;">JUNHO</th>
                                    <th style="width:6%;text-align: center;">JULHO</th>
                                    <th style="width:6%;text-align: center;">AGOSTO</th>
                                    <th style="width:6%;text-align: center;">SETEMBRO</th>
                                    <th style="width:6%;text-align: center;">OUTUBRO</th>
                                    <th style="width:6%;text-align: center;">NOVEMBRO</th>
                                    <th style="width:6%;text-align: center;">DEZEMBRO</th>
                                    <th style="width:6%;text-align: center;">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?
                                $linha = 0;
                                $nrnivel = 1;
                                foreach ($categorias as $categoria) {
                                    $total = array_fill(1, 12, 0);
                                ?>
                                    <tr id="totalgeraltopo"></tr>

                                    <? if ($nivel != $categoria['subcategorias'][0]['grupo'] . ' - ' . $categoria['subcategorias'][0]['contatipo']) { ?>
                                        <tr class="bold-text" style="background-color: #989898">
                                            <td colspan="14" ><?= $nrnivel . ' - ' . strtoupper($categoria['subcategorias'][0]['grupo']).' - ' . strtoupper($categoria['subcategorias'][0]['contatipo']) ?><td>
                                        </tr>
                                    <? $nrnivel++;
                                    }
                                    $nivel = $categoria['subcategorias'][0]['grupo'] . ' - ' . $categoria['subcategorias'][0]['contatipo'] ?>

                                    <tr id="titulo_<?= $categoria['idcontaitem'] ?>"
                                        class="bold-text" href="#contaitem_<?= $categoria['idcontaitem'] ?>"
                                        onclick="collapseCategorias(<?= $categoria['idcontaitem'] ?>)"
                                        data-toggle='collapse' data-target='#contaitem_<?= $categoria['idcontaitem'] ?>'
                                        aria-expanded='false' aria-controls='contaitem_<?= $categoria['idcontaitem'] ?>'>
                                        <th style="width:20%;text-align: left;"><?= $categoria['contaitem'] ?></th>
                                        <th style="width:5%;text-align: center;">AJUSTE (%)</th>
                                        <th style="width:6%;text-align: center;">JANEIRO</th>
                                        <th style="width:6%;text-align: center;">FEVEREIRO</th>
                                        <th style="width:6%;text-align: center;">MARÇO</th>
                                        <th style="width:6%;text-align: center;">ABRIL</th>
                                        <th style="width:6%;text-align: center;">MAIO</th>
                                        <th style="width:6%;text-align: center;">JUNHO</th>
                                        <th style="width:6%;text-align: center;">JULHO</th>
                                        <th style="width:6%;text-align: center;">AGOSTO</th>
                                        <th style="width:6%;text-align: center;">SETEMBRO</th>
                                        <th style="width:6%;text-align: center;">OUTUBRO</th>
                                        <th style="width:6%;text-align: center;">NOVEMBRO</th>
                                        <th style="width:6%;text-align: center;">DEZEMBRO</th>
                                        <th style="width:6%;text-align: center;">TOTAL</th>
                                    </tr>
                                    <? foreach ($categoria['subcategorias'] as $subcategoria) {
                                        $totallinha = 0; ?>
                                        <tr class="marcador_<?= $categoria['idcontaitem'] ?>">
                                            <td class="td-marcador">
                                                <div class="marcador" id="marcador_<?= $subcategoria['idtipoprodserv'] ?>"></div><span><?= $subcategoria['tipoprodserv'] ?></span>
                                            </td>
                                            <?
                                            $acao = $subcategoria['idplanejamentocompra'] ? 'u' : 'i';
                                            $inputName = "_{$subcategoria['idtipoprodserv']}_{$acao}_planejamentocompra_" . strtolower($month);
                                            if ($acao == 'u') : ?>
                                                <input readonly type="hidden" name="_<?= $subcategoria['idtipoprodserv'] ?>_<?= $acao ?>_planejamentocompra_idplanejamentocompra" value="<?= $subcategoria['idplanejamentocompra'] ?>">
                                            <?php endif; ?>
                                            <input readonly type="hidden" name="_<?= $subcategoria['idtipoprodserv'] ?>_<?= $acao ?>_planejamentocompra_idempresa" value="<?= $_GET["_idempresa"] ?>">
                                            <input readonly type="hidden" name="_<?= $subcategoria['idtipoprodserv'] ?>_<?= $acao ?>_planejamentocompra_exercicio" value="<?= $_1_u_forecastcompra_exercicio ?>">
                                            <input readonly type="hidden" name="_<?= $subcategoria['idtipoprodserv'] ?>_<?= $acao ?>_planejamentocompra_idtipoprodserv" value="<?= $subcategoria['idtipoprodserv'] ?>">
                                            <td><input readonly type="text" name="_<?= $subcategoria['idtipoprodserv'] ?>_<?= $acao ?>_planejamentocompra_ajuste" value="<?= $subcategoria['ajuste'] ?>" class="text-center"></td>
                                            <?php
                                            ($subcategoria['ajuste'] != null ? $completo = 2 : $completo = 0);

                                            foreach (MESES as $mes) {
                                                $monthLower = strtolower($mes);
                                                $completo += floatval($subcategoria[$monthLower]) ? 1 : 0;
                                                $totalGeral[$monthLower] += floatval($subcategoria[$monthLower]) ?? 0;
                                                echo criaInput($subcategoria, $acao, $mes, $subcategoria[$monthLower] ?? '', $totallinha, $total);
                                            }

                                            if ($completo == 14) {
                                                echo "<script>$('#marcador_{$subcategoria['idtipoprodserv']}')[0].classList.add('bg-green');</script>";
                                            } else if ($subcategoria['altera'] == 'N' && $completo < 14){ 
                                                echo "<script>$('#marcador_{$subcategoria['idtipoprodserv']}')[0].classList.add('bg-silver');</script>";
                                            } ?>
                                            <td class='text-right'><?= ($acao == 'u' ? number_format($totallinha, 2, ',', '.') : '0,00') ?>
                                        </tr>
                                    <? } ?>
                                    <tr id="total_<?= $categoria['idcontaitem'] ?>">
                                        <th style="" onclick="collapseCategorias(<?= $categoria['idcontaitem'] ?>)"><span class="titulo-categoria" style="text-align: center;">TOTAL</span><span class="titulo-categoria" style="display:none;color: black;text-align:left"><b><?= $categoria['contaitem'] ?></b></span></th>
                                        <th></th>
                                        <? foreach ($total as $key => $monthTotal) { ?>
                                            <th style="text-align: right;"><span class="total-mes-<?= $key ?>"> <?= number_format($monthTotal, 2, ',', '.') ?></span></th>
                                        <? } ?>
                                        <th style="text-align: right;"><span class="total-mes-total"> <?= number_format(array_sum($total), 2, ',', '.') ?></span></th>
                                    </tr>
                                <? } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<? //inserir o valor total por mês calculado 
?>
<div class="">
    <div class="table-responsive form-label d-flex align-items-center mb-3">
        <table class="table table-bordered m-0">
            <tbody>
                <tr id="totalgeral">
                    <td class="no-borders-left" style="width:25%;" colspan="2"> <? echo count($categorias) ?> categorias encontradas</td>
                    <?php foreach ($totalGeral as $monthTotal) { ?>
                        <td style="width:6%;text-align:center;"><b><?= number_format($monthTotal, 2, ',', '.') ?></b></td>
                    <?php } ?>
                    <td style="width:6%;text-align:center;"><b><?= number_format(array_sum($totalGeral), 2, ',', '.') ?></b></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>

document.addEventListener("DOMContentLoaded", function() {
    // Move o conteúdo de #totalgeral para #totalgeraltopo
    var totalGeral = document.getElementById("totalgeral");
    var totalGeralTopo = document.getElementById("totalgeraltopo");

    if (totalGeral && totalGeralTopo) {
        totalGeralTopo.innerHTML = totalGeral.innerHTML;

        // Limpa o conteúdo original em #totalgeral (opcional)
        totalGeral.innerHTML = "";
    }

    // Itera sobre os elementos cujo id começa com "total_"
    var elements = document.querySelectorAll("[id^=\"total_\"]");
    elements.forEach(function(element) {
        var idParts = element.id.split("_");
        if (idParts.length > 1) {
            collapseCategorias(idParts[1]);
        }
    });
});
</script>