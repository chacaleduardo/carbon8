<?
require_once("/var/www/carbon8/inc/php/functions.php");

/**
 * Parameteros get aceitos
 * @reset 
 *  codigo produto - reprocessa o produto
 *  all - reprocessa todos
 * @produto - id do produtos ser reprocessado
 * @debug - ativa o debug para visualizar os sqls gerados
 * 
 * exemplo de chamada em produção https://sislaudo.laudolab.com.br/cron/gera_tabela_custo.php?produto=all&debug=true&reset=true
 */

//valcular tempo de processamento
$startTime = microtime(true);

//vamos colocar a opção para reprocessar somente 1 produto ou vários
if (isset($_GET['reset']) && isset($_GET['produto'])) {
    if ($_GET['produto'] == 'all') {
        echo "Reset table";
        $truncate = "TRUNCATE TABLE custo";
        d::b()->query($truncate) or die("Erro ao resetar tabela:" . $truncate);
    } else {
        echo "Reprocessando o produto " . $_GET['produto'];
        $sqldel = "DELETE FROM custo WHERE idprodserv = " . $_GET['produto'];
        d::b()->query($sqldel) or die("Erro ao recalcular produto:" . $sqldel);
        $produto = $_GET['produto'];
    }
}

//primeiro pegamos todos os produtos que precisam processar
$sqlprodutos = "SELECT idprodserv 
                    FROM prodserv 
                WHERE tipo = 'PRODUTO' 
                    AND status = 'ATIVO' 
                    AND comprado = 'Y' 
                    AND idempresa = 2 " . (isset($produto) ? "AND idprodserv = {$produto};" : ";");

$produtos = d::b()->query($sqlprodutos) or die("Erro ao buscar PRODUTOS sql:" . $sqlprodutos);

$produtos->num_rows;

while ($produto = mysqli_fetch_assoc($produtos)) {

    //vamos validar qual a ultima entrada na tabela de custos
    $sql_data_corte = "select datacusto, estoque, custo from custo where idprodserv = {$produto['idprodserv']} ORDER BY datacusto desc limit 1;";

    $res_data_corte = d::b()->query($sql_data_corte) or die("Erro ao validar ultima entrada PRODUTOS sql:" . $sql_data_corte);

    if ($res_data_corte->num_rows > 0) {
        $data_corte = mysqli_fetch_row($res_data_corte);
    }

    //vamos pegar todas as entradas e saídas
    $sql_dados = "SELECT t.produto, t.data, t.qtd, t.valor, t.entrada as tipo, t.numerodoc, t.idlote
                    FROM (
                            (
                                SELECT ni.idprodserv produto, n.prazo data, CONVERT(ni.qtd, DECIMAL) qtd, ROUND(
                                        IF(
                                            valconvori > 0, (
                                                (
                                                    ni.total + IFNULL(ni.valipi, 0) + IFNULL(ni.vst, 0) - IFNULL(nx.valicms, 0)
                                                ) / IF(
                                                    l.valconvori > 0, l.valconvori * ni.qtd, ni.qtd
                                                )
                                            ) + IFNULL(
                                                (
                                                    (
                                                        (
                                                            (
                                                                (
                                                                    ni.total + IFNULL(ni.valipi, 0) + IFNULL(ni.vst, 0) - IFNULL(nx.valicms, 0)
                                                                ) * 100
                                                            ) / n.total
                                                        ) / 100
                                                    ) * ni2.vlritem / IF(
                                                        l.valconvori > 0, l.valconvori * ni.qtd, ni.qtd
                                                    )
                                                ), 0
                                            ), (
                                                (
                                                    ni.total + IFNULL(ni.valipi, 0) + IFNULL(ni.vst, 0) - IFNULL(nx.valicms, 0)
                                                ) / IF(
                                                    l.valconvori > 0, l.valconvori * ni.qtd, ni.qtd
                                                )
                                            ) + IFNULL(
                                                (
                                                    (
                                                        (
                                                            (
                                                                (
                                                                    ni.total + IFNULL(ni.valipi, 0) + IFNULL(ni.vst, 0) - IFNULL(nx.valicms, 0)
                                                                ) * 100
                                                            ) / n.total
                                                        ) / 100
                                                    ) * ni2.vlritem / IF(
                                                        l.valconvori > 0, l.valconvori * ni.qtd, ni.qtd
                                                    )
                                                ), 0
                                            )
                                        ), 2
                                    ) as valor, \"entrada\",
                                    n.idnf as numerodoc,
                                    l.idlote
                                FROM
                                    nf n
                                    JOIN nfitem ni ON n.idnf = ni.idnf
                                    AND ni.nfe = 'Y'
                                    AND ni.qtd > 0
                                    LEFT JOIN nf n2 ON n2.idobjetosolipor = n.idnf
                                    AND n2.tipoobjetosolipor = 'nf'
                                    AND n2.tiponf = 'T'
                                    AND n2.status <> 'CANCELADO'
                                    LEFT JOIN nfitem ni2 ON n2.idnf = ni2.idnf
                                    LEFT JOIN lote l ON l.idnfitem = ni.idnfitem
                                    LEFT JOIN nfitemxml nx ON nx.idnf = n.idnf
                                    AND nx.idprodserv = ni.idprodserv
                                    AND nx.status = 'Y'
                                    LEFT JOIN prodservforn pf ON pf.idprodservforn = ni.idprodservforn
                                WHERE
                                    n.tiponf NOT IN('R', 'D', 'T', 'E', 'V')
                                    AND ni.idprodserv = {$produto['idprodserv']}
                                    AND n.status IN (
                                        'APROVADO', 'DIVERGENCIA', 'CONCLUIDO'
                                    )
                                GROUP BY
                                    n.idnf
                            )
                            UNION
                            (
                                SELECT l.idprodserv produto, c.criadoem data,
                                        IF(l.valconvori > 0, c.qtdd / l.valconvori, c.qtdd) as qtd,
                                        0.00, \"saida\", si.idsolmat numerodoc, l.idlote
                                FROM
                                    lotefracao lf
                                    JOIN lote l ON (lf.idlote = l.idlote)
                                    JOIN lotecons c ON (
                                        lf.idlote = c.idlote
                                        AND (
                                            c.qtdd > 0
                                            OR c.qtdc > 0
                                        )
                                        AND c.idlotefracao = lf.idlotefracao
                                    )
                                    LEFT JOIN solmatitem si ON si.idsolmatitem = c.idobjetoconsumoespec
                                    AND c.tipoobjetoconsumoespec = 'solmatitem'
                                WHERE
                                    l.idprodserv = {$produto['idprodserv']}
                                    AND l.status NOT IN('CANCELADO', 'CANCELADA')
                                    AND idsolmat is not null
                                ORDER BY c.criadoem
                            )
                            UNION
                            (
                                
                                SELECT
                                    ni.idprodserv produto, n.criadoem data,
                                    CONVERT(ni.qtd, DECIMAL) as qtd,
                                    ROUND(
                                        IF(
                                            valconvori > 0, (
                                                (
                                                    ni.total + IFNULL(ni.valipi, 0) + IFNULL(ni.vst, 0) - IFNULL(nx.valicms, 0)
                                                ) / IF(
                                                    l.valconvori > 0, l.valconvori * ni.qtd, ni.qtd
                                                )
                                            ) + IFNULL(
                                                (
                                                    (
                                                        (
                                                            (
                                                                (
                                                                    ni.total + IFNULL(ni.valipi, 0) + IFNULL(ni.vst, 0) - IFNULL(nx.valicms, 0)
                                                                ) * 100
                                                            ) / n.total
                                                        ) / 100
                                                    ) * ni2.vlritem / IF(
                                                        l.valconvori > 0, l.valconvori * ni.qtd, ni.qtd
                                                    )
                                                ), 0
                                            ), (
                                                (
                                                    ni.total + IFNULL(ni.valipi, 0) + IFNULL(ni.vst, 0) - IFNULL(nx.valicms, 0)
                                                ) / IF(
                                                    l.valconvori > 0, l.valconvori * ni.qtd, ni.qtd
                                                )
                                            ) + IFNULL(
                                                (
                                                    (
                                                        (
                                                            (
                                                                (
                                                                    ni.total + IFNULL(ni.valipi, 0) + IFNULL(ni.vst, 0) - IFNULL(nx.valicms, 0)
                                                                ) * 100
                                                            ) / n.total
                                                        ) / 100
                                                    ) * ni2.vlritem / IF(
                                                        l.valconvori > 0, l.valconvori * ni.qtd, ni.qtd
                                                    )
                                                ), 0
                                            )
                                        ), 2
                                    ) as valor, \"saida\", n.idnf as numerodoc,
                                    l.idlote
                                FROM
                                    nf n
                                    JOIN nfitem ni ON n.idnf = ni.idnf
                                    AND ni.nfe = 'Y'
                                    AND ni.qtd > 0
                                    LEFT JOIN nf n2 ON n2.idobjetosolipor = n.idnf
                                    AND n2.tipoobjetosolipor = 'nf'
                                    AND n2.tiponf = 'T'
                                    AND n2.status <> 'CANCELADO'
                                    LEFT JOIN nfitem ni2 ON n2.idnf = ni2.idnf
                                    LEFT JOIN lote l ON l.idnfitem = ni.idnfitem
                                    LEFT JOIN nfitemxml nx ON nx.idnf = n.idnf
                                    AND nx.idprodserv = ni.idprodserv
                                    AND nx.status = 'Y'
                                    LEFT JOIN prodservforn pf ON pf.idprodservforn = ni.idprodservforn
                                WHERE
                                    n.tiponf NOT IN('R', 'D', 'T', 'E', 'V')
                                    AND ni.idprodserv = {$produto['idprodserv']}
                                    AND n.status IN (
                                        'APROVADO', 'DIVERGENCIA', 'CONCLUIDO'
                                    ) AND (l.idlote is null or l.idlote = '')
                                GROUP BY
                                    n.idnf
                            )
                        ) AS t
                        WHERE t.valor IS NOT NULL
                      " . (isset($data_corte) ? ' AND data > "' . $data_corte['0'] . '"' : "") . " 
                    ORDER BY data, t.entrada";
    if (isset($_GET['debug'])) {
        echo "<pre>";
        var_dump($sql_dados);
        echo "</pre>";
    }

    $fila = d::b()->query($sql_dados) or die("Erro ao buscar dados do PRODUTOS sql:" . $sql_dados);

    if ($fila->num_rows < 1) {
        continue;
    }

    $estoque = 0;
    $customedio = 0;

    //caso tenha uma nova entrada ou saída pegamos o ultimos valores para continuar os calculos
    if (isset($data_corte)) {
        $estoque = $data_corte['1'];
        $customedio = $data_corte['2'];
    }

    //vamos preencher a tabela
    while ($row = mysqli_fetch_assoc($fila)) {
        //debug para usar em futuros questionamentos sobre os dados do produto.
        if (isset($_GET['debug']) && (isset($_GET['produto']))) {
            echo '<pre>';
            var_dump($row);
            var_dump($customedio);
            echo '</pre>';
        }

        //se não tem número de documento ou valor não conto
        if ($row['numerodoc'] < 1) {
            continue;
        }

        if ($row['tipo'] == 'entrada') {
            if ($estoque == 0) {
                //valor primeira entrada e para produtos zerados o custo médio é o da entrada
                $estoque = $row['qtd'];
                $customedio = $row['valor'];
            } else if ($estoque < 0) {
                //custo para negativos mantém o custo médio e altera o estoque.
                $estoque = $estoque + $row['qtd'];
            } else {
                // Cálculo do custo médio ponderado
                $custototal_estoque = $estoque * $customedio;
                $custototal_entrada = $row['qtd'] * $row['valor'];
                $quantidade_total = $estoque + $row['qtd'];
                $customedio = ($custototal_estoque + $custototal_entrada) / $quantidade_total;

                // Atualizando o estoque com a nova entrada
                $estoque += $row['qtd'];
            }
        } else if ($row['tipo'] == 'saida') {
            if ($estoque >= $row['qtd']) {
                // Reduzir o estoque pela quantidade saída
                $estoque -= $row['qtd'];
            } else {
                // validando se o estoque vai ficar certo , vai entrar estoque negativo vou apenas salvar
                echo "Erro: Quantidade de saída {$row['qtd']} maior que o estoque {$estoque} disponível.<br>";
                // die(); não vamos parar vai deixar negativo.
                $estoque -= $row['qtd'];
            }
        }

        $sql_insert = "INSERT INTO 
                            custo (
                                idempresa, 
                                idprodserv,
                                operacao,
                                numerodoc, 
                                datacusto,
                                qtd,
                                estoque, 
                                custo,
                                custoentrada,
                                criadoem, 
                                criadopor, 
                                alteradoem, 
                                alteradopor
                            )
                            VALUES
                            (
                                2, 
                                {$row['produto']},
                                '{$row['tipo']}',
                                {$row['numerodoc']},
                                '{$row['data']}',
                                {$row['qtd']}, 
                                $estoque, 
                                $customedio,
                                {$row['valor']}, 
                                NOW(),
                                'cron',
                                NOW(),
                                'cron'
                            );";

        //salvando no banco de dados.
        d::b()->query($sql_insert) or die("Erro ao inserir custo sql: " . $sql_insert);
    }
}
$endTime = microtime(true);

// Calcula o tempo de execução
$executionTime = $endTime - $startTime;

if (isset($_GET['debug'])) {
    echo "Tempo de execução: " . number_format($executionTime, 6) . " minutos.";
}
