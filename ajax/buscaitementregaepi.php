<?

/**
 * Select para mostar o campo solmatitem na tela de solicitação de equipamento
 * 
 */
require_once("../inc/php/functions.php");

if ($_GET['view']) {
    $solmat = $_GET['idsolmat'];
    $sql = "SELECT e.*
                FROM  entregaepiitens e 
                INNER join solmatitem s on s.idsolmat = e.idsolmat and e.idprodserv = s.idprodserv
            WHERE
                e.idsolmat in (" . $solmat . ") 
                and e.identregaepi = " . $_GET['identregaepi'] . "
                and e.qtd > 0 ";
    $res = d::b()->query($sql) or die("Erro ao buscar Solicitação de material");
} else {
    $solmat = implode(",", $_REQUEST['idsolmat']);
    $sql = "SELECT t.*
            FROM (
            SELECT
                i.idsolmat, i.idprodserv, i.qtdc - CASE
                    WHEN (
                        SELECT sum(qtd)
                        FROM entregaepiitens epi
                        WHERE
                            epi.idsolmat = i.idsolmat
                            AND epi.idprodserv = i.idprodserv
                    ) > 0 THEN (
                        SELECT sum(qtd)
                        FROM entregaepiitens epi
                        WHERE
                            epi.idsolmat = i.idsolmat
                            AND epi.idprodserv = i.idprodserv
                    )
                    ELSE 0
                END AS qtdc, i.un, i.descr, CONCAT(l.partida, '/', l.exercicio) AS partidaexercicio, l.certificadoepi, f.idlotefracao, l.idlote
            FROM
                solmatitem i
                JOIN lotecons c ON (
                    c.tipoobjetoconsumoespec = 'solmatitem'
                    AND c.idobjetoconsumoespec = i.idsolmatitem
                    AND c.tipoobjeto = 'lotefracao'
                )
                JOIN lote l ON (l.idlote = c.idlote)
                JOIN lotefracao f ON (f.idlotefracao = c.idobjeto)
            WHERE
                i.idsolmat IN (" . $solmat . ")
                AND l.certificadoepi > 0) as t 
            where qtdc > 0;";
    $res = d::b()->query($sql) or die("Erro ao buscar Solicitação de material");
}


?>
<table <?= $_GET['view'] ? '' : 'style="display:none"' ?>>
    <tr>
        <td>
            <label class="form-label" type="hidden">Id Solicitação de Equipamento (Solmat)</label>
            <input class="col-xs-4" name="_1_u_entregaepi_idsolmat" value="<?= $solmat ?>" readonly='readonly'>
        </td>
    </tr>
</table>

<table id="tbItens" class="table table-striped" style="width: 100%">
    <thead>
        <th><!-- <input type="checkbox"> --></th>
        <th>Solicitação(Solmat)</th>
        <th>Estoque</th>
        <th class="col-xs-1">Qtd</th>
        <th>Unidade</th>
        <th>Descrição</th>
        <th>Lote</th>
        <th>certificado</th>
        </tr>
    </thead>
    <tbody>
        <? $i = 1;
        if ($_GET['view']) {
            while ($r = mysqli_fetch_array($res)) { ?>
                <tr>
                    <td></td>
                    <td><?= $r['idsolmat']; ?></td>
                    <td><?= $r['qtdc']; ?></td>
                    <td><?= $r['qtd']; ?></td>
                    <td><?= $r['un']; ?></td>
                    <td><?= $r['descitem']; ?></td>
                    <td><?= $r['partidaexercicio']; ?></td>
                    <td><?= $r['certificadoepi']; ?></td>
                </tr>
            <? $i++;
            }
        } else {
            while ($r = mysqli_fetch_array($res)) { ?>
                <tr>
                    <td><input type="checkbox"></td>
                    <td><?= $r['idsolmat']; ?><input value="<?= $r['idsolmat'] ?>" name="_<?= $i ?>_i_entregaepiitens_idsolmat" type="hidden"> </td>
                    <td><?= $r['qtdc']; ?><input value="<?= $r['qtdc'] ?>" name="_<?= $i ?>_i_entregaepiitens_qtdc" type="hidden"> </td>
                    <td><input value="" id="numero" oninput="validateInput()" type="number" max="<?= $r['qtdc'] ?>" name="_<?= $i ?>_i_entregaepiitens_qtd"> </td>
                    <td><?= $r['un']; ?><input value="<?= $r['un'] ?>" name="_<?= $i ?>_i_entregaepiitens_un" type="hidden"></td>
                    <td><?= $r['descr']; ?><input value="<?= $r['descr'] ?>" name="_<?= $i ?>_i_entregaepiitens_descitem" type="hidden"></td>
                    <td><?= $r['partidaexercicio']; ?><input value="<?= $r['partidaexercicio'] ?>" name="_<?= $i ?>_i_entregaepiitens_partidaexercicio" type="hidden"></td>
                    <td><?= $r['certificadoepi']; ?><input value="<?= $r['certificadoepi'] ?>" name="_<?= $i ?>_i_entregaepiitens_certificadoepi" type="hidden"></td>
                    <input value="<?= $_GET['identregaepi'] ?>" name="_<?= $i ?>_i_entregaepiitens_identregaepi" type="hidden">
                    <input value="<?= $r['idprodserv'] ?>" name="_<?= $i ?>_i_entregaepiitens_idprodserv" type="hidden">
                    <input value="<?= $r['idlote'] ?>" name="_<?= $i ?>_i_entregaepiitens_idlote" type="hidden">
                    <input value="<?= $r['idlotefracao'] ?>" name="_<?= $i ?>_i_entregaepiitens_idlotefracao" type="hidden">
                </tr>
        <? $i++;
            }
        } ?>
    </tbody>
</table>