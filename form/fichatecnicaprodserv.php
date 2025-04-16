<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/prodserv_controller.php");

if ($_POST) {
    require_once("../inc/php/cbpost.php");
    $data_inicial = $_POST['datainicial'];
    $data_final = $_POST['datafinal'];
    $_1_u_prodserv_idprodserv = $_POST['_1_u_prodserv_idprodserv'];
}

?>

<style>
    .tad {
        text-align: center;
    }

    .tac {
        text-align: center;
        border-left: 1px solid #000;
    }
</style>

<div id="fichaestoque">
    <div style="padding-bottom: 15px;">
        <input name="data_inicial" id="data_inicial" class="calendario form-control size10" type="text" style="margin-right: 15px;" value="<?= $data_inicial ?>" vnulo autocomplete="off">
        <input name="data_final" id="data_final" class="calendario form-control size10" type="text" style="margin-right: 15px;" value="<?= $data_final ?>" vnulo autocomplete="off">
        <button class="btn btn-primary btn-xs habilitar-campo" style="padding: 5px 20px 5px 20px;" onclick="buscarKdardex()"><i class="fa fa-search" style="padding-right: 5px;"></i>Filtrar</button>
    </div>
    <? if (!empty($data_inicial) && !empty($data_final)) { ?>
        <a href="/cron/gera_tabela_custo.php?produto=<?= $_1_u_prodserv_idprodserv ?>&reset=true">Clique para reprocessar o produto</a>
        <div class="panel panel-default">
            <div class="panel-body">
                <table class="table table-striped planilha">
                    <tr>
                        <th colspan="2"></th>
                        <th colspan="2" class="tac">Entrada</th>
                        <th colspan="2" class="tac">Saída</th>
                        <th colspan="3" class="tac">Estoque</th>
                    </tr>
                    <tr>
                        <th class="size15">Data</th>
                        <th class="size20">Operação</th>
                        <th class="size10 tac">Qt. Entrada</th>
                        <th class="size10 tad">Vl. Entrada (R$)</th>
                        <th class="size10 tac">Qt. Saída</th>
                        <th class="size10 tad">Vl. Saída (R$)</th>
                        <th class="size10 tac">Qt. Saldo</th>
                        <th class="size10 tad">Vl. Un. Saldo (R$)</th>
                        <th class="size10 tad">Vl. Saldo (R$)</th>
                    </tr>
                    <?
                    $custo = ProdServController::pegaCustoPeriodo($_1_u_prodserv_idprodserv, $data_inicial, $data_final);
                    foreach ($custo as $produto) {
                    ?>
                        <tr>
                            <td><?= implode("/", array_reverse(explode("-", explode(" ", $produto['datacusto'])[0]))) ?></td>
                            <? if ($produto['operacao'] == 'entrada') { ?>
                                <td>Recebimento idNF <a href="?_modulo=nfentrada&_acao=u&idnf=<?= $produto['numerodoc'] ?>" target="_blank"><?= $produto['numerodoc'] ?></a></td>
                                <td class="tac"><?= number_format(tratanumero($produto['qtd']), 0, ',', '.') ?></td>
                                <td class="tad"><?= number_format(tratanumero($produto['custoentrada']), 4, ',', '.') ?></td>
                                <td class="tac"></td>
                                <td class="tad"></td>
                            <? } else { ?>
                                <td>Baixa idSolmat <a href="?_modulo=solmat&_acao=u&idsolmat=<?= $produto['numerodoc'] ?>" target="_blank"><?= $produto['numerodoc'] ?></a></td>
                                <td class="tac"></td>
                                <td class="tad"></td>
                                <td class="tac"><?= number_format(tratanumero($produto['qtd']), 0, ',', '.') ?></td>
                                <td class="tad"><?= number_format(tratanumero($produto['custo']), 4, ',', '.') ?></td>
                            <? } ?>
                            <td class="tac"><?= number_format(tratanumero($produto['estoque']), 0, ',', '.') ?></td>
                            <td class="tad"><?= number_format(tratanumero($produto['custo']), 4, ',', '.') ?></td>
                            <td class="tad"><?= number_format(tratanumero($produto['custo'] * $produto['estoque']), 4, ',', '.') ?></td>
                        </tr>
                    <? } ?>
                </table>
            </div>
        </div>
    <? } ?>
</div>
<? require_once('../form/js/fichatecnicaprodserv_js.php'); ?>