<?
require_once("/var/www/carbon8/inc/php/functions.php");

/**
 * Parameteros get aceitos
 * @reset 
 *  codigo produto - reprocessa o produto
 *  all - reprocessa todos
 * @debug - ativa o debug para visualizar os sqls gerados
 * 
 * exemplo de chamada em produção https://sislaudo.laudolab.com.br/cron/gera_tabela_forecast.php?debug=true&reset=true
 */

//valcular tempo de processamento
$startTime = microtime(true);

//vamos colocar a opção para reprocessar somente 1 produto ou vários
if (isset($_GET['reset'])) {
    echo "Reset table";
    $truncate = "TRUNCATE TABLE forecast";
    d::b()->query($truncate) or die("Erro ao resetar tabela:" . $truncate);
}


//primeiro pegamos todos os produtos que precisam processar
$sqlprodutos = "SELECT
    t.tipoprodserv,
    p.idempresa,
    p.idprodserv,
    p.descr,
    p.insumo,
    (SELECT IF(idlote IS NULL, vlritem2, vlritem)
     FROM (
         SELECT 
             CONCAT(n1.idnf, '#', ROUND(IFNULL(l1.vlrlote, 0), 2)) AS vlritem,
             CONCAT(n1.idnf, '#', ROUND(IFNULL((i1.total / (i1.qtd * IF(IFNULL(f.valconv, 1) = 0.00, 1, IFNULL(f.valconv, 1)))), 0), 2)) AS vlritem2,
             n1.dtemissao,
             l1.idlote,
             n1.idnf
         FROM 
             nf n1 
         JOIN 
             nfitem i1 ON n1.idnf = i1.idnf AND i1.nfe = 'Y' AND i1.qtd > 0
         LEFT JOIN 
             lote l1 ON l1.idnfitem = i1.idnfitem AND l1.qtdprod > 0
         LEFT JOIN 
             lotecons lc ON lc.idobjetoconsumoespec = i1.idnfitem AND lc.tipoobjetoconsumoespec = 'nfitem' AND lc.qtdc > 0
         LEFT JOIN 
             lote l2 ON l2.idlote = lc.idlote 
         LEFT JOIN 
             prodservforn f ON f.idprodservforn = i1.idprodservforn
         WHERE 
             n1.tiponf IN ('C') 
             AND i1.idprodserv = p.idprodserv
         ORDER BY 
             n1.dtemissao DESC 
         LIMIT 1
     ) AS uc) AS ultimacompra
FROM 
    prodserv p
INNER JOIN 
    tipoprodserv t ON t.idtipoprodserv = p.idtipoprodserv
WHERE
     p.insumo = 'Y'
    AND p.status = 'ATIVO'
/*     AND p.idprodserv = 221 */
ORDER BY t.tipoprodserv ASC;";

$produtos = d::b()->query($sqlprodutos) or die("Erro ao buscar PRODUTOS sql:" . $sqlprodutos);

$produtos->num_rows;
echo '<pre>';
while ($produto = mysqli_fetch_assoc($produtos)) {
    $idempresa = $produto['idempresa'];
    $subcategoria = $produto['tipoprodserv'];
    $insumo = $produto['descr'];
    $exercicio = 2024; //ano vai ser fixo por enqto

    for ($i = 1; $i <= 12; $i++) { //vamos fazer para todos os meses

        $mes = $i;

        //vamos pegar os dados previstos
        $sqlprevisto = "SELECT
                            SUM((((pl.planejado / f.qtdpadraof) * i.qtd))) AS qtdplanejado,
                            SUM((((pl.planejado / f.qtdpadraof) * i.valortotal))) AS vlrplanejado,
                            i.un
                        FROM
                            prodserv ins
                            JOIN prodservformulaitem i ON (i.idprodserv = ins.idprodserv)
                            JOIN prodservformula f ON (f.idprodservformula = i.idprodservformula AND f.status != 'INATIVO')
                            JOIN prodserv v ON (v.idprodserv = f.idprodserv AND v.status != 'INATIVO' AND v.venda = 'Y')
                            JOIN planejamentoprodserv pl ON (pl.idprodserv = v.idprodserv AND pl.idprodservformula = f.idprodservformula AND pl.exercicio = $exercicio AND pl.planejado > 0)
                        WHERE
                            ins.idprodserv = {$produto['idprodserv']}
                            AND pl.mes = $i 
                        GROUP BY
                            ins.idprodserv,
                            pl.mes,
                            pl.exercicio;";
        
        $previsto = d::b()->query($sqlprevisto)  or die("Erro buscar dados previstos:" . $sqlprevisto);
        $dadosprevisto = mysqli_fetch_assoc($previsto);

        $qtdprevisto = ($dadosprevisto['qtdplanejado'] > 0 ? $dadosprevisto['qtdplanejado'] : 0);
        $vlrprevisto = ($dadosprevisto['vlrplanejado'] > 0 ? $dadosprevisto['vlrplanejado'] : 0);
        $unprevisto = ($dadosprevisto['un'] ? $dadosprevisto['un'] : '');

        //vamos pegar os dados executado
        $sqlproduzido = "SELECT SUM(c.qtdd * IFNULL(l.vlrlote, 0)) AS valor,
                                SUM(c.qtdd) AS qtd,
                                p.un
                            FROM lote l
                                JOIN lotecons c ON (
                                    c.idlote = l.idlote
                                    AND c.qtdd > 0
                                    AND c.status = 'ABERTO'
                                    AND c.tipoobjeto != 'lotefracao'
                                )
                                JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                            WHERE
                                l.idprodserv = {$produto['idprodserv']}
                                AND c.criadoem like '" . $exercicio . "-" . sprintf("%02d", date($i)) . "%';";

        $produzido = d::b()->query($sqlproduzido)  or die("Erro buscar produtos:" . $sqlproduzido);
        $dadosproduzido = mysqli_fetch_assoc($produzido);

        $qtdexecutado = ($dadosproduzido['qtd'] > 0 ? $dadosproduzido['qtd'] : 0);
        $vlrexecutado = ($dadosproduzido['valor'] > 0 ? $dadosproduzido['valor'] : 0);
        $unexecutado =  ($dadosproduzido['un'] ? $dadosproduzido['un'] : '');

        //vamos inserir os dados no banco
        $sqlinsert = "INSERT INTO
                        forecast (
                            idempresa, 
                            subcategoria, 
                            insumo, 
                            exercicio, 
                            mes, 
                            qtdprevisto, 
                            vlrprevisto, 
                            unprevisto, 
                            qtdexecutado, 
                            vlrexecutado, 
                            unexecutado
                        )
                        VALUE
                        (
                            $idempresa, 
                            '$subcategoria', 
                            '$insumo', 
                            $exercicio, 
                            $mes, 
                            $qtdprevisto, 
                            $vlrprevisto, 
                            '$unprevisto', 
                            $qtdexecutado, 
                            $vlrexecutado, 
                            '$unexecutado'
                        );";
        d::b()->query($sqlinsert) or die("Erro ao inserir custo sql: " . $sqlinsert);
        if (isset($_GET['debug'])) {
            echo $sqlinsert;
        }
    }
}
$endTime = microtime(true);

// Calcula o tempo de execução
$executionTime = $endTime - $startTime;

if (isset($_GET['debug'])) {
    echo "Tempo de execução: " . number_format($executionTime, 6) . " minutos.";
}
