<?
ini_set("display_errors", "1");
error_reporting(E_ALL);

if (defined('STDIN')) { //se estiver sendo executao em linhade comando
    include_once("/var/www/carbon8/inc/php/functions.php");
    include_once("/var/www/carbon8/inc/php/laudo.php");
} else { //se estiver sendo executado via requisicao htt
    include_once("../inc/php/functions.php");
    include_once("../inc/php/laudo.php");
}
$debug = false;
if (isset($_GET["_inspecionar_sql"])) {
    $debug = true;
}
//vamos pegar os idtiprodserv e seus valores paga gerar o débito
$sql = "SELECT
    idempresa,
    exercicio,
    idtipoprodserv,
    tipoprodserv,
    SUM(CASE WHEN mes = 1 THEN valor ELSE 0 END) AS jan,
    SUM(CASE WHEN mes = 2 THEN valor ELSE 0 END) AS fev,
    SUM(CASE WHEN mes = 3 THEN valor ELSE 0 END) AS mar,
    SUM(CASE WHEN mes = 4 THEN valor ELSE 0 END) AS abr,
    SUM(CASE WHEN mes = 5 THEN valor ELSE 0 END) AS mai,
    SUM(CASE WHEN mes = 6 THEN valor ELSE 0 END) AS jun,
    SUM(CASE WHEN mes = 7 THEN valor ELSE 0 END) AS jul,
    SUM(CASE WHEN mes = 8 THEN valor ELSE 0 END) AS ago,
    SUM(CASE WHEN mes = 9 THEN valor ELSE 0 END) AS `set`,
    SUM(CASE WHEN mes = 10 THEN valor ELSE 0 END) AS `out`,
    SUM(CASE WHEN mes = 11 THEN valor ELSE 0 END) AS nov,
    SUM(CASE WHEN mes = 12 THEN valor ELSE 0 END) AS dez,
    -- Soma total de todos os meses
    SUM(CASE WHEN mes IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12) THEN valor ELSE 0 END) AS total
FROM (
        SELECT pl.idplanejamentoprodserv, pl.idprodservformula, pl.idempresa, pl.planejado, pl.exercicio, pl.mes, pi.idprodserv, pi.valortotal, t.idtipoprodserv, t.tipoprodserv, sum(pi.valortotal * pl.planejado) * -1 as valor
        FROM
            planejamentoprodserv pl
            join prodserv ps on ps.idprodserv = pl.idprodserv and ps.tipo = 'PRODUTO'
            LEFT JOIN prodservformulaitem pi ON pi.idprodservformula = pl.idprodservformula AND pi.fabricado = 'N'
            LEFT JOIN prodserv pis ON pis.idprodserv = pi.idprodserv
            LEFT JOIN tipoprodserv t ON t.idtipoprodserv = pis.idtipoprodserv
            LEFT JOIN forecastvenda fc ON fc.idempresa = pl.idempresa AND fc.exercicio = pl.exercicio 
        WHERE
            pl.exercicio IN (SELECT exercicio FROM forecastvenda GROUP BY exercicio)
            AND fc.status <> 'CONCLUIDO'
        GROUP BY
            pl.idempresa, t.idtipoprodserv, pl.exercicio, pl.mes
        UNION
         SELECT pl.idplanejamentoprodserv, pl.idprodservformula, pl.idempresa, pl.planejado, pl.exercicio, pl.mes, pi.idprodserv, pi.valortotal, t.idtipoprodserv, t.tipoprodserv, sum(pi.valortotal * pl.planejado) * -1 as valor
            FROM
                planejamentoprodserv pl
                join prodserv ps on ps.idprodserv = pl.idprodserv and ps.tipo = 'SERVICO'
                join prodservformula pf on pf.idprodserv = ps.idprodserv and pf.status <> 'INATIVO'
                LEFT JOIN prodservformulaitem pi ON pi.idprodservformula = pf.idprodservformula
                AND pi.fabricado = 'N'
                LEFT JOIN prodserv pis ON pis.idprodserv = pi.idprodserv
                LEFT JOIN tipoprodserv t ON t.idtipoprodserv = pis.idtipoprodserv
                LEFT JOIN forecastvenda fc ON fc.idempresa = pl.idempresa
                AND fc.exercicio = pl.exercicio
               
            WHERE
                pl.exercicio IN (SELECT exercicio FROM forecastvenda GROUP BY exercicio)
                 AND fc.status <> 'CONCLUIDO'
                and pl.idempresa = 1
            GROUP BY
                pl.idempresa, t.idtipoprodserv, pl.exercicio, pl.mes
    ) AS subquery
GROUP BY
    idempresa, idtipoprodserv, exercicio;";

if ($debug) {
    echo '<pre>';
    echo 'Query negativa: ' . $sql;
    echo '</pre>';
}
$res = d::b()->query($sql) or die("Erro ao buscar consumos" . mysqli_error(d::b()) . "sql=" . $sql);

while ($row = mysqli_fetch_assoc($res)) {
    // Verificar se já existe o registro
    $checkSql = "SELECT COUNT(*) as count FROM planejamentocompra 
                 WHERE idempresa = {$row['idempresa']} 
                 AND exercicio = {$row['exercicio']} 
                 AND idtipoprodserv = {$row['idtipoprodserv']}";

    $checkResult = d::b()->query($checkSql);
    $checkRow = mysqli_fetch_assoc($checkResult);

    if ($row['total'] != 0) {
        //vamos inserir somente o que tiver valor.
        if ($checkRow['count'] > 0) {
            // Registro já existe - Realizar UPDATE
            $update = "UPDATE planejamentocompra SET 
            jan = {$row['jan']}, 
            fev = {$row['fev']}, 
            mar = {$row['mar']}, 
            abr = {$row['abr']}, 
            mai = {$row['mai']}, 
            jun = {$row['jun']}, 
            jul = {$row['jul']}, 
            ago = {$row['ago']}, 
            `set` = {$row['set']}, 
            `out` = {$row['out']}, 
            nov = {$row['nov']}, 
            dez = {$row['dez']}, 
            altera = 'N',
            alteradoem = NOW(), 
            alteradopor = 'CRON'
        WHERE idempresa = {$row['idempresa']} 
        AND exercicio = {$row['exercicio']} 
        AND idtipoprodserv = {$row['idtipoprodserv']}";

            $resulta = d::b()->query($update) or die("Erro ao atualizar. sql: " . $update);
            if ($debug) {
                echo "Update: " . $update . "<br>";
            }
        } else {
            // Registro não existe - Realizar INSERT
            $insert = "INSERT INTO planejamentocompra (
                idempresa, 
                exercicio, 
                idtipoprodserv, 
                jan, fev, mar, abr, mai, jun, jul, ago, `set`, `out`, nov, dez, altera,
                criadoem, alteradoem, criadopor, alteradopor
            ) VALUES (
                {$row['idempresa']},
                {$row['exercicio']},
                {$row['idtipoprodserv']},
                {$row['jan']},
                {$row['fev']},
                {$row['mar']},
                {$row['abr']},
                {$row['mai']},
                {$row['jun']},
                {$row['jul']},
                {$row['ago']},
                {$row['set']},
                {$row['out']},
                {$row['nov']},
                {$row['dez']},
                'N',
                NOW(), NOW(), 'CRON', 'CRON'
            );";

            $resulta = d::b()->query($insert) or die("Erro ao inserir. sql: " . $insert);
            if ($debug) {
                echo "Insert: " . $insert . "<br>";
            }
        }
    }
}

//vamos gerar os de crédito
$sql2 = "SELECT 
            idempresa,
            exercicio,
            idtipoprodserv,
            SUM(CASE WHEN mes = 1 THEN valor ELSE 0 END) AS jan,
            SUM(CASE WHEN mes = 2 THEN valor ELSE 0 END) AS fev,
            SUM(CASE WHEN mes = 3 THEN valor ELSE 0 END) AS mar,
            SUM(CASE WHEN mes = 4 THEN valor ELSE 0 END) AS abr,
            SUM(CASE WHEN mes = 5 THEN valor ELSE 0 END) AS mai,
            SUM(CASE WHEN mes = 6 THEN valor ELSE 0 END) AS jun,
            SUM(CASE WHEN mes = 7 THEN valor ELSE 0 END) AS jul,
            SUM(CASE WHEN mes = 8 THEN valor ELSE 0 END) AS ago,
            SUM(CASE WHEN mes = 9 THEN valor ELSE 0 END) AS `set`,
            SUM(CASE WHEN mes = 10 THEN valor ELSE 0 END) AS `out`,
            SUM(CASE WHEN mes = 11 THEN valor ELSE 0 END) AS nov,
            SUM(CASE WHEN mes = 12 THEN valor ELSE 0 END) AS dez,
             -- Soma total de todos os meses
            SUM(CASE WHEN mes IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12) THEN valor ELSE 0 END) AS total
        FROM (
        (SELECT p.idempresa, ps.idtipoprodserv, p.exercicio, p.mes, sum(p.planejado * CASE WHEN p.vlrticketmedio > 0 THEN  p.vlrticketmedio WHEN f.vlrvenda > 0  THEN f.vlrvenda ELSE 0 END) as valor
        FROM planejamentoprodserv p
            JOIN prodservformula f on(f.idprodservformula =  p.idprodservformula) 
            JOIN prodserv ps on(ps.idprodserv= f.idprodserv)  
            JOIN forecastvenda fc ON fc.idempresa = p.idempresa 
                    AND fc.exercicio = p.exercicio 
                    AND fc.status <> 'CONCLUIDO'  
        WHERE p.planejado > 0 
        GROUP BY  p.idempresa, ps.idtipoprodserv , p.exercicio, p.mes
        ORDER BY  p.idempresa, ps.idtipoprodserv , p.exercicio, p.mes)
        UNION
        (SELECT p.idempresa, ps.idtipoprodserv, p.exercicio, p.mes, sum(p.planejado * CASE WHEN p.vlrticketmedio > 0 THEN  p.vlrticketmedio WHEN ps.vlrvenda > 0  THEN ps.vlrvenda ELSE 0 END) as planejado
        FROM planejamentoprodserv p
            join prodserv ps on(ps.idprodserv= p.idprodserv) 
            JOIN forecastvenda fc ON fc.idempresa = p.idempresa 
                    AND fc.exercicio = p.exercicio 
                    AND fc.status <> 'CONCLUIDO'  
        WHERE p.idprodservformula is null
        AND p.planejado > 0 
        GROUP BY p.idempresa, ps.idtipoprodserv , p.exercicio, p.mes
        ORDER BY p.idempresa, ps.idtipoprodserv , p.exercicio, p.mes)
        ) AS subquery
        GROUP BY idempresa, idtipoprodserv, exercicio";

$res2 = d::b()->query($sql2) or die("Erro ao buscar consumos" . mysqli_error(d::b()) . "sql=" . $sql2);
if ($debug) {
    echo '<pre>';
    echo 'query positiva: ' . $sql;
    echo '</pre>';
}
while ($row = mysqli_fetch_assoc($res2)) {
    // Verificar se já existe o registro
    $checkSql = "SELECT COUNT(*) as count FROM planejamentocompra 
                 WHERE idempresa = {$row['idempresa']} 
                 AND exercicio = {$row['exercicio']} 
                 AND idtipoprodserv = {$row['idtipoprodserv']}";

    $checkResult = d::b()->query($checkSql);
    $checkRow = mysqli_fetch_assoc($checkResult);

    if ($row['total'] != 0) {
        if ($checkRow['count'] > 0) {
            // Registro já existe - Realizar UPDATE
            $update = "UPDATE planejamentocompra SET 
            jan = {$row['jan']}, 
            fev = {$row['fev']}, 
            mar = {$row['mar']}, 
            abr = {$row['abr']}, 
            mai = {$row['mai']}, 
            jun = {$row['jun']}, 
            jul = {$row['jul']}, 
            ago = {$row['ago']}, 
            `set` = {$row['set']}, 
            `out` = {$row['out']}, 
            nov = {$row['nov']}, 
            dez = {$row['dez']}, 
            altera = 'N',
            alteradoem = NOW(), 
            alteradopor = 'CRON'
        WHERE idempresa = {$row['idempresa']} 
        AND exercicio = {$row['exercicio']} 
        AND idtipoprodserv = {$row['idtipoprodserv']}";

            $resulta = d::b()->query($update) or die("Erro ao atualizar. sql: " . $update);
            if ($debug) {
                echo "Update credito: " . $update . "<br>";
            }
        } else {
            // Registro não existe - Realizar INSERT
            $insert = "INSERT INTO planejamentocompra (
                idempresa, 
                exercicio, 
                idtipoprodserv, 
                jan, fev, mar, abr, mai, jun, jul, ago, `set`, `out`, nov, dez, altera,
                criadoem, alteradoem, criadopor, alteradopor
            ) VALUES (
                {$row['idempresa']},
                {$row['exercicio']},
                {$row['idtipoprodserv']},
                {$row['jan']},
                {$row['fev']},
                {$row['mar']},
                {$row['abr']},
                {$row['mai']},
                {$row['jun']},
                {$row['jul']},
                {$row['ago']},
                {$row['set']},
                {$row['out']},
                {$row['nov']},
                {$row['dez']},
                'N',
                NOW(), NOW(), 'CRON', 'CRON'
            );";

            $resulta = d::b()->query($insert) or die("Erro ao inserir. sql: " . $insert);
            if ($debug) {
                echo "Insert credito: " . $insert . "<br>";
            }
        }
    }
}
