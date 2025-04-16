<?
class ProdservQuery implements DefaultQuery
{
    public static $table = 'prodserv';
    public static $pk = 'idprodserv';

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function listarProdservsNaoVinculadasAoTagTipo()
    {
        return "SELECT idprodserv, CONCAT(e.sigla, ' - ', descr) AS descr 
            FROM prodserv p 
                JOIN empresa e ON e.idempresa = p.idempresa 
            WHERE 
                EXISTS (
                    SELECT 1 
                    FROM prodservcontaitem c 
                        JOIN contaitem ci ON ci.idcontaitem = c.idcontaitem 
                    WHERE c.idprodserv = p.idprodserv 
                        AND ci.idcontaitem IN(485, 39, 54, 467)
                        AND c.status = 'ATIVO'
                    )
                AND p.status = 'ATIVO' 
                AND p.tipo = 'PRODUTO' 
                AND (p.idtagtipo IS NULL OR p.idtagtipo = '')
            ORDER BY e.sigla, p.descr";
    }

    public static function buscarProdservVinculadasAoTagTipo()
    {
        return "SELECT idprodserv, CONCAT(e.sigla, ' - ', descr) AS descr 
            FROM prodserv p 
                JOIN empresa e ON e.idempresa = p.idempresa 
            WHERE p.idtagtipo = ?idtagtipo? 
                AND p.status = 'ATIVO' 
            ORDER BY p.descr";
    }

    public static function buscarTextoInclusaoDeResultado()
    {
        return "SELECT 
                    textoinclusaores
                FROM
                    prodserv
                WHERE
                    idprodserv = ?idtipoteste?";
    }

    public static function buscarFornecedorProdserv()
    {
        return "SELECT p.idprodserv,
                    CASE WHEN f.codforn='' THEN  p.descr
                            WHEN  f.codforn is null THEN p.descr
                            ELSE f.codforn END AS descr
                    ,f.idpessoa,f.idprodservforn,
                    CASE WHEN f.unforn='' THEN  p.un
                            WHEN  f.unforn is null THEN p.un
                            ELSE f.unforn END AS unforn
                FROM prodservforn f JOIN prodserv p ON p.idprodserv = f.idprodserv
                WHERE p.status = 'ATIVO'
                AND f.status = 'ATIVO'                                                          
                AND f.idpessoa IN (?idpessoas?) 
                AND p.idempresa = ?idempresa?
                ORDER BY descr";
    }


    public static function buscarNomeAlertaERotulosServico()
    {
        return "SELECT 
                    p.alertarotulo, p.alertarotuloy, p.alertarotulon
                FROM
                    resultado r
                        JOIN
                    prodserv p ON r.idtipoteste = p.idprodserv
                WHERE
                    idresultado =  ?idresultado?";
    }

    public static function buscarServicosVinculados()
    {
        return "SELECT 
                    p.idprodserv as idobjeto, p.logoinmetro
                FROM
                    resultado r
                        JOIN
                    prodservvinculo pv ON pv.idprodserv = r.idtipoteste
                        JOIN
                    prodserv p ON p.idprodserv = pv.idobjeto
                WHERE
                    tipoobjeto = 'prodserv'
                        AND pv.alerta = 'Y'
                        AND idresultado = ?idresultado?";
    }

    public static function buscarJsonConfigServico()
    {
        return "SELECT 
            jsonconfig
        FROM
            prodserv
        WHERE
            idprodserv = ?idtipoteste?";
    }


    public static function buscarServicosDaUnidade()
    {
        return "SELECT 
            idprodserv, TRIM(CONCAT(codprodserv, ' - ', descr)) AS descr
        FROM
            prodserv p
                JOIN
            unidadeobjeto u ON (u.idunidade = ?unidadepadrao?
                AND u.idobjeto = p.idprodserv
                AND u.tipoobjeto = 'prodserv')
        WHERE
            p.status = 'ATIVO'
                AND p.tipo = 'SERVICO'
        ORDER BY TRIM(CONCAT(codprodserv, ' - ', descr))";
    }

    public static function buscarProdutoSaida()
    {

        return "SELECT 
                    p.idprodserv,
                    CONCAT(IFNULL(CONCAT(e.sigla, ' - '), ''),
                            p.descr) AS descr
                FROM
                    prodserv p
                        LEFT JOIN
                    empresa e ON (e.idempresa = p.idempresa)
                WHERE (p.nfe = 'Y' OR p.certificado = 'Y')
                    AND p.status = 'ATIVO'
                    AND p.tipo = 'PRODUTO'
                    AND p.idtipoprodserv IS NOT NULL
                    AND p.idtipoprodserv != ''
                    AND( p.produtoacabado = 'Y' OR p.insumo = 'Y' OR p.imobilizado = 'Y' OR p.material = 'Y')
                    AND EXISTS (SELECT 1 FROM prodservcontaitem i WHERE i.idprodserv = p.idprodserv)
                    ?prodservPorSessionIdempresa?
                ORDER BY descr";
    }

    public static function buscarProdutoSaidaMateriais()
    {

        return "SELECT p.idprodserv,
                       CONCAT(IFNULL(CONCAT(e.sigla, ' - '), ''), p.descr) AS descr
                FROM
                    prodserv p
                        LEFT JOIN
                    empresa e ON (e.idempresa = p.idempresa)
                WHERE
                    p.nfe = 'Y'
                        AND p.status = 'ATIVO'
                        AND p.tipo = 'PRODUTO'
                        AND p.idtipoprodserv IS NOT NULL
                        AND p.idtipoprodserv != ''
                        AND( p.produtoacabado = 'Y' OR p.insumo = 'Y' OR p.imobilizado = 'Y')
                        AND EXISTS(SELECT 1 FROM prodservcontaitem i WHERE i.idprodserv = p.idprodserv)
                        ?prodservPorSessionIdempresa?
                        ?limitProdutos?
                UNION
                SELECT p.idprodserv,
                       CONCAT(IFNULL(CONCAT(e.sigla, ' - '), ''), p.descr) AS descr
                FROM prodserv p LEFT JOIN empresa e ON (e.idempresa = p.idempresa)
                WHERE p.nfe = 'Y'
                  AND p.status = 'ATIVO'
                  AND p.tipo = 'PRODUTO'
                  AND p.idtipoprodserv IS NOT NULL
                  AND p.idtipoprodserv != ''
                  AND p.material = 'Y'
                  AND EXISTS(SELECT 1 FROM prodservcontaitem i WHERE i.idprodserv=p.idprodserv)
                  ?prodservPorSessionIdempresa?
                ORDER BY descr";
    }

    public static function buscarProdutoServicoEspecialVinculadoProdservPrProc()
    {
        return "SELECT idprodserv, descr
                  FROM prodserv p
                 WHERE p.fabricado = 'Y' 
                   AND p.venda = 'N'
                   ?idempresa?
                   AND p.especial = 'Y'
                   AND EXISTS(SELECT 1 FROM prodservprproc o WHERE o.idprodserv = p.idprodserv)
                   AND p.status = 'ATIVO'
              ORDER BY p.descr";
    }

    public static function buscarProdutosProgramadosGerenciamentoConcentrados()
    {
        return "SELECT idprodservvacina,
                       vacina,
                       idpessoa,
                       SUM(qtd) AS qtd,
                       nome,
                       plantel,
                       rotulo,
                       dose,
                       idprodserv,
                       descr,
                       qtdi,
                       qtdi_exp,
                       qtdpadrao,
                       idempresa,
                       idsolfab
                  FROM (SELECT pf.idprodserv AS idprodservvacina,
                               v.descr AS vacina,
                               pf.idpessoa,
                               pf.qtd AS qtd,
                               p.nome,
                               pa.plantel,
                               CONCAT(f.rotulo, '-', IFNULL(f.dose, '--'), ' Doses ', ' (', f.volumeformula, ' ', f.un, ')') AS rotulo,
                               f.dose,
                               fi.idprodserv,
                               ps.descr,
                               fi.qtdi,
                               fi.qtdi_exp,
                               IFNULL(f.qtdpadraof, 1) AS qtdpadrao,
                               p.idempresa,
                               (select max(idsolfab) from lote l where l.idprodserv=pf.idprodserv and l.idpessoa=pf.idpessoa) as idsolfab
                          FROM prodservforn pf JOIN prodserv v ON (v.idprodserv = pf.idprodserv)
                          JOIN pessoa p ON (pf.idpessoa = p.idpessoa AND p.status = 'ATIVO')
                     LEFT JOIN plantelobjeto po ON (po.idobjeto = p.idpessoa AND po.tipoobjeto = 'pessoa')
                     LEFT JOIN plantel pa ON (pa.idplantel = po.idplantel)
                          JOIN prodservformula f ON (pf.idprodservformula = f.idprodservformula)
                          JOIN prodservformulains fi ON (fi.idprodservformula = f.idprodservformula)
                          JOIN prodserv ps ON (ps.idprodserv = fi.idprodserv AND ps.especial = 'Y')
                         WHERE pf.status = 'ATIVO'
                           AND fi.status = 'ATIVO'
                           AND pf.valido = 'Y'
                           AND pf.qtd > 0
                           ?idempresa?
                           AND v.tipo = 'PRODUTO'
                           AND v.venda = 'Y'
                           AND v.especial = 'Y'
                           ?clausulalote?
                           ?clausulad?) AS u
                GROUP BY u.idprodserv, u.idpessoa
                ORDER BY u.plantel, u.descr";
    }

    public static function buscarProdutosPedidosGerenciamentoConcentrados()
    {
        return "SELECT idprodservvacina,
                       vacina,
                       idpessoa,
                       SUM(qtd) AS qtd,
                       nome,
                       plantel,
                       rotulo,
                       dose,
                       idprodserv,
                       descr,
                       qtdi,
                       qtdi_exp,
                       qtdpadrao,
                       envio,
                       idnf,
                       idformalizacao,
                       idempresa,
                       idsolfab
                  FROM (SELECT v.idprodserv AS idprodservvacina,
                               v.descr AS vacina,
                               l.idpessoa,
                               IF(l.qtdajust < 1, l.qtdpedida, l.qtdajust) AS qtd,
                               pf.nome,
                               pa.plantel,
                               CONCAT(f.rotulo, '-', IFNULL(f.dose, '--'), ' Doses ', ' (', f.volumeformula, ' ', f.un, ')') AS rotulo,
                               f.dose,
                               fi.idprodserv,
                               ps.descr,
                               fi.qtdi,
                               fi.qtdi_exp,
                               IFNULL(f.qtdpadraof, 1) AS qtdpadrao,
                               IFNULL(fo.envio, n.envio) AS envio, 
                               n.idnf,
                               fo.idformalizacao,
                               l.idempresa,
                               l.idsolfab
                          FROM lote l JOIN formalizacao fo ON fo.idlote = l.idlote
                          JOIN prodserv v ON (v.idprodserv = l.idprodserv AND v.venda = 'Y' AND v.tipo = 'PRODUTO' AND v.especial = 'Y')
                          JOIN unidade u ON (l.idunidade = u.idunidade AND u.producao = 'Y')
                          JOIN pessoa pf ON (pf.idpessoa = l.idpessoa)
                     LEFT JOIN plantelobjeto po ON (po.idobjeto = pf.idpessoa AND po.tipoobjeto = 'pessoa')
                     LEFT JOIN plantel pa ON (pa.idplantel = po.idplantel)
                          JOIN prodservformula f ON (f.idprodservformula = l.idprodservformula)
                          JOIN prodservformulains fi ON (fi.idprodservformula = f.idprodservformula)
                          JOIN prodserv ps ON (ps.idprodserv = fi.idprodserv AND ps.especial = 'Y')
                          JOIN fluxostatus fx ON (fx.idfluxostatus = fo.idfluxostatus)
                     LEFT JOIN (SELECT lc.idlote AS idlote, lc.idobjeto AS idobjeto 
                                  FROM (lotereserva lc JOIN (SELECT c1.idlotereserva FROM lotereserva c1 JOIN nfitem ni ON ni.idnfitem = c1.idobjeto WHERE ((c1.qtd > 0) AND (c1.tipoobjeto = 'nfitem'))
                              GROUP BY c1.idlote) c ON ((lc.idlotereserva = c.idlotereserva)))) c ON ((c.idlote = l.idlote))
                             LEFT JOIN nfitem i ON ((i.idnfitem = c.idobjeto))
                             LEFT JOIN nf n ON ((i.idnf = n.idnf))
                         WHERE fx.idstatus = 169 
                           ?dataenvioCondicao?
                           ?idempresa?
                           AND fi.status = 'ATIVO' 
                           AND l.tipoobjetoprodpara = 'nfitem'
                           ?clausulalote?
                           ?clausulad?) AS u
               GROUP BY u.idprodserv, u.idpessoa
               ORDER BY ?orderBy?";
    }

    public static function buscarProdutosProgramadosPedidosGerenciamentoConcentrados()
    {
        return "SELECT idprodservvacina,
                       vacina,
                       idpessoa,
                       SUM(qtd) AS qtd,
                       nome,
                       plantel,
                       rotulo,
                       dose,
                       idprodserv,
                       descr,
                       qtdi,
                       qtdi_exp,
                       qtdpadrao,
                       envio,
                       idnf,
                       idformalizacao,
                       idempresa,
                       idsolfab
                  FROM (SELECT v.idprodserv AS idprodservvacina,
                           v.descr AS vacina,
                           l.idpessoa,
                           IF(l.qtdajust < 1, l.qtdpedida, l.qtdajust) AS qtd,
                           pf.nome,
                           pa.plantel,
                           CONCAT(f.rotulo, '-', IFNULL(f.dose, '--'), ' Doses ', ' (', f.volumeformula, ' ', f.un, ')') AS rotulo,
                           f.dose,
                           fi.idprodserv,
                           ps.descr,
                           fi.qtdi,
                           fi.qtdi_exp,
                           IFNULL(f.qtdpadraof, 1) AS qtdpadrao,
                           IFNULL(fo.envio, n.envio) AS envio, 
                           n.idnf,
						   fo.idformalizacao,
                           fo.idempresa,
                           l.idsolfab
                      FROM lote l JOIN formalizacao fo ON fo.idlote = l.idlote
                      JOIN prodserv v ON (v.idprodserv = l.idprodserv AND v.venda = 'Y' AND v.tipo = 'PRODUTO' AND v.especial = 'Y')
                      JOIN unidade u ON (l.idunidade = u.idunidade AND u.producao = 'Y')
                      JOIN pessoa pf ON (pf.idpessoa = l.idpessoa)
                 LEFT JOIN plantelobjeto po ON (po.idobjeto = pf.idpessoa AND po.tipoobjeto = 'pessoa')
                 LEFT JOIN plantel pa ON (pa.idplantel = po.idplantel)
                      JOIN prodservformula f ON (f.idprodservformula = l.idprodservformula)
                      JOIN prodservformulains fi ON (fi.idprodservformula = f.idprodservformula)
                      JOIN prodserv ps ON (ps.idprodserv = fi.idprodserv AND ps.especial = 'Y')
                      JOIN fluxostatus fx ON (fx.idfluxostatus = fo.idfluxostatus)
                LEFT JOIN (SELECT lc.idlote AS idlote, lc.idobjeto AS idobjeto 
                             FROM (lotereserva lc JOIN (SELECT MIN(c1.idlotereserva) AS idlotereserva FROM lotereserva c1 WHERE ((c1.qtd > 0) AND (c1.tipoobjeto = 'nfitem'))
                         GROUP BY c1.idlote) c ON ((lc.idlotereserva = c.idlotereserva)))) c ON ((c.idlote = l.idlote))
                             LEFT JOIN nfitem i ON ((i.idnfitem = c.idobjeto))
                             LEFT JOIN nf n ON ((i.idnf = n.idnf))
                     WHERE fx.idstatus = 169
                       AND fi.status = 'ATIVO'
                       AND l.tipoobjetoprodpara = 'nfitem'
                       and (n.envio  is null  or n.envio <='?dataenvio?')
                       ?idempresa2?
                       ?clausulalote?
                       ?clausulad?
            UNION
                SELECT pf.idprodserv AS idprodservvacina,
                               v.descr AS vacina,
                               pf.idpessoa,
                               CASE WHEN (SELECT SUM(IF(l.qtdajust < 1, l.qtdpedida, l.qtdajust)) 
                                            FROM lote l JOIN formalizacao f1 ON (f1.idlote = l.idlote)
                                            JOIN fluxostatus fx ON (fx.idfluxostatus = f1.idfluxostatus)
                                            left join lotereserva c on(c.idlote = l.idlote AND c.tipoobjeto = 'nfitem' and c.qtd > 0  and c.status != 'CONCLUIDO') 
                                            left JOIN nfitem ni ON (ni.idnfitem = c.idobjeto )
                                            LEFT JOIN nf n ON (ni.idnf = n.idnf)
                                           WHERE fx.idstatus IN (114, 169) AND l.idprodserv = pf.idprodserv 
                                           and (n.envio  is null  or n.envio <='?dataenvio?')
                                             AND l.idprodservformula = pf.idprodservformula
                                             AND l.idpessoa = pf.idpessoa) > pf.qtd
                                    THEN (SELECT SUM(IF(l.qtdajust < 1, l.qtdpedida, l.qtdajust))
                                            FROM lote l JOIN formalizacao f1 ON (f1.idlote = l.idlote)
                                            JOIN fluxostatus fx ON (fx.idfluxostatus = f1.idfluxostatus)
                                                left join lotereserva c on(c.idlote = l.idlote AND c.tipoobjeto = 'nfitem' and c.qtd > 0  and c.status != 'CONCLUIDO') 
                                                left JOIN nfitem ni ON (ni.idnfitem = c.idobjeto )
                                                LEFT JOIN nf n ON (ni.idnf = n.idnf)
                                           WHERE fx.idstatus IN (114, 169)
                                           and (n.envio  is null  or n.envio <='?dataenvio?')
                                             AND l.idprodserv = pf.idprodserv
                                             AND l.idprodservformula = pf.idprodservformula
                                             AND l.idpessoa = pf.idpessoa)
                                    ELSE pf.qtd
                                    END AS qtd,
                               p.nome,
                               pa.plantel,
                               CONCAT(f.rotulo, '-', IFNULL(f.dose, '--'), ' Doses ', ' (', f.volumeformula, ' ', f.un, ')') AS rotulo,
                               f.dose,
                               fi.idprodserv,
                               ps.descr,
                               fi.qtdi,
                               fi.qtdi_exp,
                               IFNULL(f.qtdpadraof, 1) AS qtdpadrao,
                               '' as envio,
                               '' as idnf,
                               '' as idformalizacao,
                               p.idempresa,
                               (select max(idsolfab) from lote l where l.idprodserv=pf.idprodserv and l.idpessoa=pf.idpessoa) as idsolfab
                          FROM prodservforn pf JOIN prodserv v ON (v.idprodserv = pf.idprodserv)
                          JOIN pessoa p ON (pf.idpessoa = p.idpessoa AND p.status = 'ATIVO')
                     LEFT JOIN plantelobjeto po ON (po.idobjeto = p.idpessoa AND po.tipoobjeto = 'pessoa')
                     LEFT JOIN plantel pa ON (pa.idplantel = po.idplantel)
                          JOIN prodservformula f ON (pf.idprodservformula = f.idprodservformula)
                          JOIN prodservformulains fi ON (fi.idprodservformula = f.idprodservformula)
                          JOIN prodserv ps ON (ps.idprodserv = fi.idprodserv AND ps.especial = 'Y')
                         WHERE pf.status = 'ATIVO'
                           AND fi.status = 'ATIVO'
                           AND pf.valido = 'Y'
                           AND pf.qtd > 0
                           ?idempresa1?
                           AND v.tipo = 'PRODUTO'
                           AND v.venda = 'Y'
                           AND v.especial = 'Y' 
                           ?clausulalote?
                           ?clausulad?) AS u
        GROUP BY u.idprodserv, u.idpessoa
        ORDER BY u.plantel, u.descr";
    }

    public static function buscarConcenteradosProgramado()
    {
        return "SELECT u.idprodservvacina,
                       u.idprodservformula,
                       u.vacina,
                       u.descrcurta,
                       u.idpessoa,
                       u.nome,
                       u.rotulo,
                       u.dose,
                       u.idprodserv,
                       u.descr,
                       u.qtdi,
                       u.qtdi_exp,
                       u.qtdpadrao,
                       u.padraoconcentrado,
                       u.qtdpadrao_exp,
                       ROUND(u.qtd, 4) AS qtd,
                       'PROGRAMACAO' AS tipopendencia
                  FROM (SELECT pf.idprodserv AS idprodservvacina,
                               f.idprodservformula,
                               v.descr AS vacina,
                               v.descrcurta,
                               pf.idpessoa,
                               pf.qtd,
                               p.nome,
                               CONCAT(f.rotulo, '-', IFNULL(f.dose, '--'), ' Doses ', ' (', f.volumeformula, ' ', f.un, ')') AS rotulo,
                               f.dose,
                               fi.idprodserv,
                               ps.descr,
                               fi.qtdi,
                               fi.qtdi_exp,
                               IFNULL(f.qtdpadraof, 1) AS qtdpadrao,
                               fc.qtdpadraof AS padraoconcentrado,
                               fc.qtdpadraof_exp AS qtdpadrao_exp
                          FROM prodservforn pf JOIN prodserv v ON (v.idprodserv = pf.idprodserv)
                          JOIN pessoa p ON (pf.idpessoa = p.idpessoa AND p.status = 'ATIVO')
                          JOIN prodservformula f ON (pf.idprodservformula = f.idprodservformula AND f.status = 'ATIVO')
                          JOIN prodservformulains fi ON (fi.idprodservformula = f.idprodservformula)
                          JOIN prodserv ps ON (ps.idprodserv = fi.idprodserv AND ps.especial = 'Y')
                          JOIN prodservformula fc ON (fc.idprodserv = ps.idprodserv AND fc.status = 'ATIVO')
                         WHERE pf.status = 'ATIVO' 
                           AND pf.qtd > 0
                           AND pf.valido = 'Y'
                           AND fi.status = 'ATIVO'
                           AND v.tipo = 'PRODUTO'
                           AND v.venda = 'Y'
                           AND v.especial = 'Y'
                           ?idempresa?
                           AND pf.idpessoa = ?idpessoa?
                           AND fi.idprodserv = ?idprodserv?
                           ?clausulalote?
                           ?clausulad?) AS u
                GROUP BY u.idprodservvacina , u.qtdi , u.qtdi_exp
                ORDER BY descr";
    }

    public static function buscarConcenteradosPedido()
    {
        return "SELECT u.idprodservvacina,
                       u.idprodservformula,
                       u.vacina,
                       u.descrcurta,
                       u.idpessoa,
                       u.nome,
                       u.rotulo,
                       u.dose,
                       u.idprodserv,
                       u.descr,
                       u.qtdi,
                       u.qtdi_exp,
                       u.qtdpadrao,
                       u.padraoconcentrado,
                       u.qtdpadrao_exp,
                       u.qtd,
                      'PEDIDO' AS tipopendencia,
                      idformalizacao,
                      envio,
                      idnf
                 FROM (SELECT p.idprodserv AS idprodservvacina,
                              f.idprodservformula,
                              p.descr AS vacina,
                              p.descrcurta,
                              l.idpessoa,
                              (SELECT SUM(IF(l.qtdajust < 1, l.qtdpedida, l.qtdajust))
                                 FROM lote l JOIN formalizacao f ON (f.idlote = l.idlote)
                                 JOIN fluxostatus fx ON (fx.idfluxostatus = f.idfluxostatus)
                                 WHERE fx.idstatus IN (114 , 169)
                                   AND l.idprodserv = p.idprodserv
                                   AND l.idprodservformula = f.idprodservformula
                                   AND l.idpessoa = l.idpessoa
                                   AND f.idformalizacao = fo.idformalizacao) AS qtd,
                              pf.nome,
                              CONCAT(f.rotulo, '-', IFNULL(f.dose, '--'), ' Doses ', ' (', f.volumeformula, ' ', f.un, ')') AS rotulo,
                              f.dose,
                              fi.idprodserv,
                              ps.descr,
                              fi.qtdi,
                              fi.qtdi_exp,
                              IFNULL(f.qtdpadraof, 1) AS qtdpadrao,
                              psif.qtdpadraof AS padraoconcentrado,
                              psif.qtdpadraof_exp AS qtdpadrao_exp,
                              fo.idformalizacao,
                              IFNULL(fo.envio, n.envio) AS envio, 
                              n.idnf
                         FROM lote l JOIN formalizacao fo ON fo.idlote = l.idlote
                         JOIN prodserv p ON (p.idprodserv = l.idprodserv AND p.venda = 'Y' AND p.tipo = 'PRODUTO' AND p.especial = 'Y')
                         JOIN unidade u ON (l.idunidade = u.idunidade AND u.producao = 'Y')
                         JOIN pessoa pf ON (pf.idpessoa = l.idpessoa)
                         JOIN prodservformula f ON (f.idprodservformula = l.idprodservformula AND f.status = 'ATIVO')
                         JOIN prodservformulains fi ON (fi.idprodservformula = f.idprodservformula)
                         JOIN prodserv ps ON (ps.idprodserv = fi.idprodserv AND ps.especial = 'Y')
                         JOIN prodservformula psif ON (ps.idprodserv = psif.idprodserv AND psif.status = 'ATIVO')
                         JOIN fluxostatus fx ON (fx.idfluxostatus = fo.idfluxostatus)
                    LEFT JOIN (SELECT lc.idlote AS idlote, lc.idobjeto AS idobjeto 
                                  FROM (lotereserva lc JOIN (SELECT c1.idlotereserva FROM lotereserva c1 JOIN nfitem ni ON ni.idnfitem = c1.idobjeto AND c1.tipoobjeto = 'nfitem' WHERE ((c1.qtd > 0))
                              GROUP BY c1.idlote) c ON ((lc.idlotereserva = c.idlotereserva)))) c ON ((c.idlote = l.idlote))
                    LEFT JOIN nfitem i ON ((i.idnfitem = c.idobjeto))
                             LEFT JOIN nf n ON ((i.idnf = n.idnf))
                        WHERE fx.idstatus = 169
                          ?dataenvioCondicao?
                          AND pf.idpessoa = ?idpessoa?
                          AND fi.idprodserv = ?idprodserv?
                          AND fi.status = 'ATIVO'
                          ?idempresa?
                          ?clausulalote?
                          ?clausulad?) AS u
                GROUP BY u.idprodservvacina, u.qtdi, u.qtdi_exp, u.idformalizacao
                ORDER BY descr";
    }

    public static function buscarConcenteradosProgramadoPedido()
    {
        return "SELECT idprodservvacina,
                       idprodservformula,
                       vacina,
                       descrcurta,
                       idpessoa,
                       nome,
                       rotulo,
                       dose,
                       idprodserv,
                       descr,
                       qtdi,
                       qtdi_exp,
                       qtdpadrao,
                       padraoconcentrado,
                       qtdpadrao_exp,
                       CASE WHEN (SELECT SUM(IF(l.qtdajust < 1, l.qtdpedida,  l.qtdajust))
                                    FROM lote l JOIN formalizacao f ON (f.idlote = l.idlote)
                                    JOIN fluxostatus fx ON (fx.idfluxostatus = f.idfluxostatus)
                                   WHERE fx.idstatus IN (114, 169)
                                   AND l.idprodserv = u.idprodservvacina
                                   AND l.idprodservformula = u.idprodservformula
                                   AND l.idpessoa = u.idpessoa) > ROUND(qtd, 4)
                            THEN (SELECT SUM(IF(l.qtdajust < 1, l.qtdpedida, l.qtdajust))
                                    FROM lote l JOIN formalizacao f ON (f.idlote = l.idlote)
                                    JOIN fluxostatus fx ON (fx.idfluxostatus = f.idfluxostatus)
                                   WHERE fx.idstatus IN (114 , 169)
                                     AND l.idprodserv = u.idprodservvacina
                                     AND l.idprodservformula = u.idprodservformula
                                     AND l.idpessoa = u.idpessoa)
                            ELSE ROUND(u.qtd, 4)
                             END AS qtd,
                       CASE WHEN (SELECT SUM(IF(l.qtdajust < 1, l.qtdpedida, l.qtdajust))
                                    FROM lote l JOIN formalizacao f ON (f.idlote = l.idlote)
                                    JOIN fluxostatus fx ON (fx.idfluxostatus = f.idfluxostatus)
                                   WHERE fx.idstatus IN (114, 169)
                                     AND l.idprodserv = u.idprodservvacina
                                     AND l.idprodservformula = u.idprodservformula
                                     AND l.idpessoa = u.idpessoa) > ROUND(u.qtd, 4)
                            THEN 'PEDIDO'
                            ELSE 'PROGRAMACAO'
                             END AS tipopendencia
                FROM (SELECT pf.idprodserv AS idprodservvacina, 
                             f.idprodservformula,
                             v.descr AS vacina,
                             v.descrcurta,
                             pf.idpessoa,
                             pf.qtd,
                             p.nome,
                             CONCAT(f.rotulo, '-', IFNULL(f.dose, '--'), ' Doses ', ' (', f.volumeformula, ' ', f.un, ')') AS rotulo,
                             f.dose,
                             fi.idprodserv,
                             ps.descr,
                             fi.qtdi,
                             fi.qtdi_exp,
                             IFNULL(f.qtdpadraof, 1) AS qtdpadrao,
                             fc.qtdpadraof AS padraoconcentrado,
                             fc.qtdpadraof_exp AS qtdpadrao_exp
                        FROM prodservforn pf JOIN prodserv v ON (v.idprodserv = pf.idprodserv)
                        JOIN pessoa p ON (pf.idpessoa = p.idpessoa AND p.status = 'ATIVO')
                        JOIN prodservformula f ON (pf.idprodservformula = f.idprodservformula AND f.status = 'ATIVO')
                        JOIN prodservformulains fi ON (fi.idprodservformula = f.idprodservformula)
                        JOIN prodserv ps ON (ps.idprodserv = fi.idprodserv AND ps.especial = 'Y')
                        JOIN prodservformula fc ON (fc.idprodserv = ps.idprodserv AND fc.status = 'ATIVO')
                       WHERE pf.status = 'ATIVO' AND pf.qtd > 0
                         AND pf.valido = 'Y'
                         AND fi.status = 'ATIVO'
                         AND v.tipo = 'PRODUTO'
                         AND v.venda = 'Y'
                         AND v.especial = 'Y'
                         ?idempresa?
                         AND pf.idpessoa = ?idpessoa?
                         AND fi.idprodserv = ?idprodserv?
                         ?clausulalote?
                         ?clausulad? 
            UNION 
                SELECT p.idprodserv AS idprodservvacina,
                       f.idprodservformula,
                       p.descr AS vacina,
                       p.descrcurta,
                       l.idpessoa,
                       0 AS qtd,
                       pf.nome,
                       CONCAT(f.rotulo, '-', IFNULL(f.dose, '--'), ' Doses ', ' (', f.volumeformula, ' ', f.un, ')') AS rotulo,
                       f.dose,
                       fi.idprodserv,
                       psi.descr,
                       fi.qtdi,
                       fi.qtdi_exp,
                       IFNULL(f.qtdpadraof, 1) AS qtdpadrao,
                       psif.qtdpadraof AS padraoconcentrado,
                       psif.qtdpadraof_exp AS qtdpadrao_exp
                  FROM lote l JOIN formalizacao fo ON fo.idlote = l.idlote
                  JOIN prodserv p ON (p.idprodserv = l.idprodserv AND p.venda = 'Y' AND p.tipo = 'PRODUTO' AND p.especial = 'Y')
                  JOIN unidade u ON (l.idunidade = u.idunidade AND u.producao = 'Y')
                  JOIN pessoa pf ON (pf.idpessoa = l.idpessoa)
                  JOIN prodservformula f ON (f.idprodservformula = l.idprodservformula AND f.status = 'ATIVO')
                  JOIN prodservformulains fi ON (fi.idprodservformula = f.idprodservformula)
                  JOIN prodserv psi ON (psi.idprodserv = fi.idprodserv AND psi.especial = 'Y')
                  JOIN prodservformula psif ON (psi.idprodserv = psif.idprodserv AND psif.status = 'ATIVO')
                  JOIN fluxostatus fx ON (fx.idfluxostatus = fo.idfluxostatus)
                  LEFT JOIN (SELECT lc.idlote AS idlote, lc.idobjeto AS idobjeto 
                                  FROM (lotereserva lc JOIN (SELECT c1.idlotereserva FROM lotereserva c1 JOIN nfitem ni ON ni.idnfitem = c1.idobjeto AND c1.tipoobjeto = 'nfitem' WHERE ((c1.qtd > 0))
                              GROUP BY c1.idlote) c ON ((lc.idlotereserva = c.idlotereserva)))) c ON ((c.idlote = l.idlote))
                    LEFT JOIN nfitem i ON ((i.idnfitem = c.idobjeto))
                             LEFT JOIN nf n ON ((i.idnf = n.idnf))
                 WHERE fx.idstatus IN  (169)
                 and (n.envio  is null  or n.envio <='?dataenvio?')
                   AND pf.idpessoa = ?idpessoa?
                   AND fi.idprodserv = ?idprodserv?
                   AND fi.status = 'ATIVO'
                   ?idempresa2?
                   ?clausulalote?
                   ?clausulad?) AS u
          GROUP BY u.idprodservvacina , u.qtdi , u.qtdi_exp
          ORDER BY descr";
    }

    public static function buscarValorProdutoFormulado()
    {
        return "SELECT 
                        IFNULL(vlrvenda, '0.00') AS vlrvenda
                    FROM
                        prodservformula
                    WHERE
                        idprodservformula =?idprodservformula?";
    }

    public static function buscarProdServPorCondicao()
    {
        return "SELECT p.idprodserv, p.descr 
                FROM prodserv p  
                WHERE 1
                ?getidempresa?
                ?condicao?
                ?orderby?";
    }

    public static function buscarProdservPorIdresultado()
    {
        return "SELECT p.jsonconfig,
                        pl.idplantel,
                        a.idade,
                        p.tipogmt,
                        p.modelo,
                        p.modo,
                        (select g.gmt from gmt g where t.tipoespecial = g.tipogmt AND a.idade = g.idade) as gmt,
                        p.idprodserv,
                        t.tipoespecial 
                FROM amostra a
                    join resultado r on r.idamostra = a.idamostra 
                    join vwtipoteste t on r.idtipoteste = t.idtipoteste 
                    join especiefinalidade esp on esp.idespeciefinalidade=a.idespeciefinalidade
                    join plantel pl on pl.idplantel=esp.idplantel
                    join prodserv p on p.idprodserv = r.idtipoteste
                WHERE r.idresultado = ?idresultado?";
    }

    public static function buscarValidadeProdserv()
    {
        return "SELECT 
                    DATE_FORMAT(DATE_ADD(now(), INTERVAL if(validade<1,15,validade) month), '%d/%m/%Y') as vencimento 
                FROM prodserv
                WHERE  idprodserv= ?idprodserv?";
    }

    public static function buscarProservVendaMaterial()
    {
        return "SELECT 
                    idprodserv,
                    CASE
                        WHEN descrcurta = '' OR descrcurta = NULL THEN descr
                        ELSE descrcurta
                    END AS descrcurta
                FROM
                    prodserv
                WHERE
                    (venda = 'Y' OR material = 'Y')
                        AND status = 'ATIVO'
                        AND tipo = 'PRODUTO'
                        ?getidempresa?
                ORDER BY descrcurta";
    }

    public static function buscarProdutoOuServicoComprado()
    {
        return "SELECT p.idprodserv, CONCAT(e.sigla,' - ', p.descr) AS descr, p.un, p.codprodserv
				  FROM prodserv p JOIN empresa e on e.idempresa = p.idempresa
				 WHERE p.status = 'ATIVO' 
				   AND ((p.comprado = 'Y' AND p.tipo = 'SERVICO') OR (p.tipo = 'PRODUTO'))
                   ?solcomUnidadeCbUserIdempresa?
                   ?nothere?
             GROUP BY p.idprodserv  
             ORDER BY p.descr";
    }

    public static function buscarProdutoOuServicoFabricado()
    {
        return "SELECT p.idprodserv, CONCAT(e.sigla,' - ', p.descr) AS descr, p.un, p.codprodserv,f.idprodservformula,
                        CONCAT(f.rotulo,
                                ' ',
                                IFNULL(f.dose, ' '),
                                ' ',
                                p.conteudo,
                                ' ',
                                ' (',
                                f.volumeformula,
                                ' ',
                                f.un,
                                ')') AS rotulo
				  FROM prodserv p JOIN empresa e on e.idempresa = p.idempresa
                  JOIN prodservformula f on(f.idprodserv=p.idprodserv and f.status !='INATIVO')
				 WHERE p.status = 'ATIVO' 
				   AND p.fabricado = 'Y' AND p.tipo = 'PRODUTO'
                   ?solcomUnidadeCbUserIdempresa?
                   ?nothere?            
             ORDER BY p.descr";
    }


    public static function listarProdutosVinculados()
    {
        return "SELECT p.idprodserv, p.descr
                  FROM prodserv p JOIN unidadeobjeto u ON (u.idunidade = 9 AND u.idobjeto = p.idprodserv AND u.tipoobjeto = 'prodserv')
                 WHERE p.tipo = '?tipo?'
                   AND p.idprodserv NOT IN (SELECT idobjetovinc 
                                              FROM objetovinculo o
                                             WHERE o.idobjeto = '?idprodserv?'
                                               AND o.tipoobjeto = 'prodserv')
                   AND p.idprodserv NOT IN (SELECT idobjeto 
                                              FROM objetovinculo o
                                             WHERE o.idobjetovinc = '?idprodserv?'
                                               AND o.tipoobjetovinc = 'prodserv')
                   AND p.status = 'ATIVO'
                   AND p.especial = 'Y'
              ORDER BY p.descr";
    }

    public static function listarServicosVinculados()
    {
        return "SELECT p.idprodserv, p.descr
                  FROM prodserv p JOIN unidadeobjeto u ON (u.idunidade in (9,1) AND u.idobjeto = p.idprodserv AND u.tipoobjeto = 'prodserv')
                 WHERE p.tipo = '?tipo?'
                   AND p.status = 'ATIVO'
              ORDER BY p.descr";
    }

    public static function listarTagSalaVinculados()
    {
        return "SELECT t.idtag, t.descricao
                  FROM tag t 
                 WHERE t.status = 'ATIVO'
                    and t.idempresa in (?idempresa?)
                    AND t.idtagclass = 2
                  /*  AND t.idtag NOT IN (SELECT idobjeto 
                                          FROM prodservvinculo o
                                         WHERE o.idprodserv = ?idprodserv?
                                           AND o.tipoobjeto = 'tagsala') */
              ORDER BY t.descricao asc";
    }

    public static function listarAnaliseBioterio()
    {
        return "SELECT b.idservicobioterioconf, 
                       bi.tipoanalise,
                       s.idobjeto,
                       s.tipoobjeto 
                  FROM bioterioanaliseteste b JOIN servicobioterioconf s ON b.idservicobioterioconf = s.idservicobioterioconf
                  JOIN bioterioanalise bi ON bi.idbioterioanalise = s.idobjeto
                 WHERE idprodserv = ?idprodserv? 
              GROUP BY s.idobjeto, s.tipoobjeto";
    }

    public static function listarProcessosVinculados()
    {
        return "SELECT p.idprodserv,
                       p.descr,
                       c.idprproc,
                       r.idprproc,
                       r.proc
                  FROM prodserv p JOIN prodservprproc c ON p.idprodserv = c.idprodserv AND p.status = 'ATIVO'
                  JOIN prproc r ON c.idprproc = r.idprproc
                 WHERE p.idprodserv  = ?idprodserv? 
              ORDER BY p.descr";
    }

    public static function listarCertificadosProcesso()
    {
        return "SELECT pr.idprodservprproc,
                       p.idprproc,
                       p.proc 
                  FROM prodservprproc pr JOIN prprocprativ pa ON pr.idprproc = pa.idprproc
                  JOIN prproc p ON p.idprproc = pa.idprproc
                  JOIN prativ a ON a.idprativ = pa.idprativ
                  JOIN prativobj pb ON pb.idprativ = a.idprativ 
                 WHERE pr.idprodserv = ?idprodserv? 
              GROUP BY p.idprproc";
    }

    public static function verificarSeExisteTipoUnidadeProducao()
    {
        return "SELECT 1
                  FROM unidade u JOIN unidadeobjeto p ON (u.idunidade = p.idunidade AND p.idobjeto = ?idobjeto? AND p.tipoobjeto = 'prodserv')
                 WHERE u.status = 'ATIVO'
                   AND u.idtipounidade = 5
              ORDER BY u.ord";
    }

    public static function buscarServicosDaUnidadeEEmpresa()
    {
        return "SELECT idprodserv,concat(codprodserv,' - ',descr) as descr, logoinmetro
                from prodserv p 
                join unidadeobjeto u on( u.idunidade = ?idunidade? and u.idobjeto = p.idprodserv and u.tipoobjeto = 'prodserv')
                join unidade un on(un.idunidade = u.idunidade)
                join empresa e on(e.idempresa = un.idempresa)
                where  p.status = 'ATIVO'
                and p.tipo='SERVICO' 
                ?str?
                order by p.descr";
    }

    public static function buscarCfopPorIdprodserv()
    {
        return "SELECT 
                        c.cfop, c.cfop
                    FROM
                        prodservcfop p,
                        cfop c
                    WHERE
                        c.idcfop = p.idcfop
                            AND c.cfop = '?cfop?'
                            AND p.idprodserv = ?idprodserv?
                            AND p.origem = '?origem?'";
    }

    public static function buscarInfoProdserv()
    {
        return "SELECT * FROM prodserv WHERE idprodserv = ?idprodserv?";
    }

    public static function buscarAnaliseQst()
    {
        return "SELECT f.idanaliseqst,
                       p.descr,
                       f.qst,
                       f.especificacao,
                       f.esperado,
                       f.idtipoteste,
                       f.ordem
                  FROM analiseqst f LEFT JOIN prodserv p ON (p.idprodserv = f.idtipoteste)
                 WHERE f.status = 'ATIVO'
                   AND f.idprodserv = ?idprodserv?
                   AND f.idpessoa IS NULL
              ORDER BY f.ordem , p.descr";
    }

    public static function buscarIdTipoTestePorTipoEIdTipoUnidade()
    {
        return "SELECT p.idprodserv AS idtipoteste,
                       CONCAT(p.descr, ' - ', p.idprodserv) AS descr
                  FROM prodserv p JOIN unidadeobjeto u ON p.idprodserv = u.idobjeto
                  JOIN unidade un ON (un.idunidade = u.idunidade AND un.idtipounidade = ?idtipounidade?)
                 WHERE p.tipo = '?tipo?'
                   AND p.status = 'ATIVO'
                   AND u.tipoobjeto = 'prodserv'
                   ?getidempresa?
              ORDER BY p.descr";
    }

    public static function buscarProdservVinculoServico()
    {
        return "SELECT pva.idprodservvinculo,
                       IF(p.tipo = 'SERVICO', p.descr, IF(p.descrcurta = '' OR p.descrcurta IS NULL, p.descr, p.descrcurta)) AS descr,
                       p.idprodserv,
                       pva.alerta
                  FROM prodservvinculo pva JOIN prodserv p ON pva.idobjeto = p.idprodserv AND pva.tipoobjeto = 'prodserv'
                 WHERE pva.idprodserv = ?idprodserv?                   
                   AND p.tipo = 'SERVICO'
              ORDER BY descr";
    }

    public static function buscarTagSalaETagTipoVinculo()
    {
        return "SELECT p.*, t.descricao, t.idtag, (o.idobjetovinc) as idobjetovinc, (o.idobjetovinculo) as idobjetovinculo
                FROM prodservvinculo p
                JOIN tag t ON t.idtag = p.idobjeto
                LEFT JOIN objetovinculo o on o.idobjeto = p.idprodservvinculo and o.tipoobjeto = 'prodservvinculo' and o.tipoobjetovinc = 'tagtipo'
                WHERE p.idprodserv = ?idprodserv?
                    AND p.tipoobjeto = 'tagsala'
                order by t.descricao";
    }

    public static function buscarTagSalaETagTipoVinculoAgrupado()
    {
        return "SELECT p.*, t.descricao, t.idtag, group_concat(o.idobjetovinc) as idobjetovinc, group_concat(o.idobjetovinculo) as idobjetovinculo
                FROM prodservvinculo p
                JOIN tag t ON t.idtag = p.idobjeto
                join objetovinculo o on o.idobjeto = p.idprodservvinculo and o.tipoobjeto = 'prodservvinculo' and o.tipoobjetovinc = 'tagtipo'
                WHERE p.idprodserv = ?idprodserv?
                    AND p.tipoobjeto = 'tagsala'
                GROUP BY t.idtag";
    }

    public static function buscarCalculoEstoqueProdservComFormula()
    {
        return "SELECT f.idprodservformula AS id,
                       CONCAT(f.rotulo, '-', IFNULL(f.dose, '--'), ' Doses ', ' (', f.volumeformula, ' ', f.un, ')') AS rotulo,
                       IFNULL(SUM(lf.qtd), 0) AS vqtdest,
                       f.qtdpadraof_exp AS qtdpadrao_exp,
                       f.*
                  FROM prodserv p JOIN prodservformula f ON (f.idprodserv = p.idprodserv AND f.status != 'INATIVO')
             LEFT JOIN lote l ON (l.idprodserv = p.idprodserv AND l.idprodservformula = f.idprodservformula AND l.status IN ('APROVADO')) AND l.piloto = 'N'
             LEFT JOIN lotefracao lf ON (lf.idlote = l.idlote AND lf.idunidade = f.idunidadeest AND lf.status = 'DISPONIVEL')
                 WHERE p.fabricado = 'Y' AND p.tipo = 'PRODUTO'
                   AND f.idprodservformula = ?idprodservformula?
              GROUP BY f.idprodservformula";
    }

    public static function buscarCalculoEstoqueProdserv()
    {
        return "SELECT p.idprodserv AS id, 
                       SUM(f.qtd) AS vqtdest, 
                       p.*
                  FROM prodserv p LEFT JOIN lote l ON (l.idprodserv = p.idprodserv AND l.status IN ('APROVADO', 'EMANALISE', 'QUARENTENA') AND l.idprodservformula IS NULL)
             LEFT JOIN lotefracao f ON (f.idlote = l.idlote AND f.status = 'DISPONIVEL' AND f.idunidade = p.idunidadeest)
                 WHERE p.idprodserv = ?idprodserv?";
    }

    public static function buscarRotuloProdservComRorulo()
    {
        return "SELECT idprodservformula AS id,
                       CONCAT(f.rotulo, ' ', IFNULL(f.dose, ' '), ' ', p.conteudo, ' ', ' (', f.volumeformula, ' ', f.un, ')') AS rotulo
                  FROM prodservformula f JOIN prodserv p ON (p.idprodserv = f.idprodserv)
                 WHERE f.status != 'INATIVO'
                   AND f.idprodserv = ?idprodserv?
              ORDER BY f.idprodservformula DESC";
    }

    public static function buscarRotuloProdserv()
    {
        return "SELECT idprodserv AS id, descr AS rotulo
                  FROM prodserv
                 WHERE idprodserv = ?idprodserv?";
    }

    public static function buscarMediaDiaria()
    {
        return "SELECT c.idlotefracao, c.qtdd, c.qtdc
                  FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                  JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd > 0 OR c.qtdc > 0) AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')
                 WHERE lf.idunidade = ?idunidade?
                   ?in_str?
                   AND l.idprodserv = ?idprodserv?
                   AND l.status NOT IN ('CANCELADO' , 'CANCELADA')
                   AND c.criadoem > DATE_SUB(NOW(),  INTERVAL ?consumodias? DAY)";
    }

    public static function buscarProdutoServicoComprado()
    {
        return "SELECT p.idprodserv, p.descr
                  FROM prodserv p
                 WHERE p.comprado = 'Y'
                   AND p.status = 'ATIVO'
                   ?prodservPorSessionIdempresa?
              ORDER BY p.descr";
    }

    public static function buscarProdutoServicoCompradoPorIdTipoProdserv()
    {
        return "SELECT idprodserv,
                       CONCAT(IFNULL(CONCAT(e.sigla, ' - '), ''), p.descr) AS descr
                  FROM prodserv p LEFT JOIN empresa e ON (e.idempresa = p.idempresa)
                 WHERE p.comprado = 'Y'
                   ?strtipo?
                   ?idempresa?
                   AND p.status = 'ATIVO'
                   ?idtipoprodserv?
              ORDER BY descr";
    }

    public static function buscarProdServPorClausulasProdServTipoUnidadeTagEIdEmpresa()
    {
        return "SELECT 
                    p.idprodserv, CONCAT(e.sigla,' - ',p.descr) AS descr, ifnull(p.un, '') as un ,p.codprodserv
                FROM prodserv p 
                JOIN unidadeobjeto u ON(u.idobjeto = p.idprodserv AND u.tipoobjeto = 'prodserv' ?clausulaprodserv?)
                JOIN empresa e ON e.idempresa = p.idempresa
                JOIN unidade un ON un.idempresa = e.idempresa 
                WHERE un.idtipounidade IN(3) AND p.status='ATIVO' AND p.idempresa IN(?idempresa?, 8)
                ?clausulatipounidade?
                ?clausulatag?
                GROuP BY p.idprodserv
                ORDER BY p.descr;";
    }

    public static function buscarProdutoParaEstudo()
    {
        return "SELECT l.idlote,
                        concat(e.sigla,' - ',concat(l.partida,'/',l.exercicio,' - ',ifnull(p.descrcurta,p.descr))) as descr
                FROM lote l 
                    JOIN lotefracao f on(f.idlote=l.idlote and f.qtd>0 and f.status='DISPONIVEL' and f.idunidade = ?idunidade?)
                    JOIN prodserv p on(p.idprodserv=l.idprodserv and (p.fabricado='Y' or p.venda='Y') and p.status='ATIVO' and p.tipo='PRODUTO')
                    LEFT JOIN empresa e on(e.idempresa = p.idempresa)
                WHERE l.status in ('QUARENTENA', 'APROVADO','LIBERADO','RETIDO')
                ?getidempresa?
                order by descr";
    }

    public static function buscarServicosParaEnsaio()
    {
        return "SELECT idprodserv as idtipoteste,
                        codprodserv 
                from prodserv t
                    join unidadeobjeto p 
                where t.status = 'ATIVO' 
                    and t.tipo='SERVICO' 
                    and p.idunidade in (?idunidade?)
                    and p.tipoobjeto= 'prodserv'
                    and p.idobjeto=t.idprodserv
                    ?getidempresa?
                order by codprodserv";
    }

    public static function atualizarNcmProdServ()
    {
        return "UPDATE nfitemxml nix JOIN prodserv p ON (p.idprodserv = nix.idprodserv AND (p.ncm IS NULL OR p.ncm = '')) 
                   SET p.ncm = nix.ncm
                 WHERE nix.idnf = ?idnf?
                   AND nix.status = 'Y'
                   AND nix.ncm IS NOT NULL";
    }

    public static function buscarProdutoParaFicharep()
    {
        return "SELECT p.idprodserv,p.descr 
                from prodserv p
                    join  unidadeobjeto u on( u.idunidade = ?idunidadepadrao? and u.idobjeto = p.idprodserv and u.tipoobjeto = 'prodserv')
                where p.tipo = 'PRODUTO'
                    and exists (select 1 from plantelobjeto po where po.tipoobjeto = 'prodserv' and po.idobjeto = p.idprodserv and po.idplantel= ?idplantel?)
                    and p.status = 'ATIVO' 
                order by p.descr";
    }

    public static function buscarNaoFormuladosPorIdProdserv()
    {
        return "SELECT p.idprodserv,
                       p.idempresa,
                       p.estoqueseguranca,
                       p.temporeposicao,
                       p.tempocompra,
                       p.idunidadeest,
                       p.valconv,
                       p.consumodias,
                       p.estmin,
                       IFNULL(SUM(f.qtd), 0) AS qtdest
                  FROM prodserv p LEFT JOIN lote l ON (l.idprodserv = p.idprodserv AND l.status IN ('APROVADO', 'QUARENTENA'))
             LEFT JOIN lotefracao f ON (f.idlote = l.idlote AND f.status = 'DISPONIVEL' AND f.idunidade = p.idunidadeest)
                 WHERE p.status = 'ATIVO' 
                   AND p.comprado = 'Y'
                   AND p.tipo = '?tipo?'
                   AND p.idprodserv = ?idprodserv?
              GROUP BY p.idprodserv";
    }

    public static function atualizarValoresCalculoEstoqueProdserv()
    {
        return "UPDATE prodserv 
                   SET qtdest = '?qtdest?',
                       destoque = ?diasestoque?,
                       mediadiaria = '?mediadiaria?',
                       estminautomatico = '?minimoauto?',
                       pedidoautomatico = '?pedidoauto?',
                       pedido_automatico = '?pedido_auto?',
                       sugestaocompra2 = '?sugestaoCompra2?',
                       ultimoorcamento = '?ultimoorcamento?',
                       alteradopor = '?usuario?',
                       alteradoem = ?alteradoem?
                 WHERE idprodserv = ?idprodserv?";
    }

    public static function buscarFormuladosPorIdProdservFormula()
    {
        return "SELECT f.idprodservformula,
                       f.idprodserv,
                       f.idempresa,
                       f.estoqueseguranca,
                       f.temporeposicao,
                       f.tempocompra,
                       f.idunidadeest,
                       p.valconv,
                       f.consumodias,
                       f.estmin,
                       IFNULL(SUM(lf.qtd), 0) AS qtdest
                  FROM prodserv p JOIN prodservformula f ON (f.idprodserv = p.idprodserv AND f.status != 'INATIVO' AND f.idunidadeest IS NOT NULL)
             LEFT JOIN lote l ON (l.idprodserv = p.idprodserv AND l.idprodservformula = f.idprodservformula AND l.status IN ('APROVADO', 'LIBERADO'))
             LEFT JOIN lotefracao lf ON (lf.idlote = l.idlote AND lf.idunidade = f.idunidadeest AND lf.status = 'DISPONIVEL')
                 WHERE p.status = 'ATIVO' AND p.fabricado = 'Y' 
                   AND p.tipo = 'PRODUTO' 
                   AND f.idprodservformula = ?idprodservformula?
              GROUP BY f.idprodservformula";
    }

    public static function buscarProdservPorVendaMaterialIdTipoProdserv()
    {
        return "SELECT p.idprodserv,
                       CONCAT(IFNULL(CONCAT(e.sigla, ' - '), ''), p.descr) AS descr,
                       p.idempresa
                  FROM prodserv p LEFT JOIN empresa e ON (e.idempresa = p.idempresa)
                 WHERE ((p.venda = 'Y' OR p.material = 'Y') OR p.idtipoprodserv IN (?idtipoprodserv?))
                   AND p.status = 'ATIVO' 
                   AND p.tipo = 'PRODUTO'
              ORDER BY descr";
    }

    public static function atualizarProdservContaItemPorIdContaItem()
    {
        return "UPDATE prodserv p JOIN prodservcontaitem pc ON (pc.idprodserv = p.idprodserv AND pc.status = 'ATIVO')
                  JOIN contaitemtipoprodserv ct ON (ct.idtipoprodserv = p.idtipoprodserv) 
                   SET pc.idcontaitem = ct.idcontaitem
                 WHERE p.status = 'INATIVO' 
                   AND ct.idcontaitem != pc.idcontaitem
                   AND p.idprodserv = ?idprodserv?";
    }

    public static function buscarProdservPorTipoEStatusEIdEmpresa()
    {
        return "SELECT p.idprodserv,
                       CONCAT(p.descr) AS descr,
                       p.especial,
                       p.codprodserv,
                       p.un,
                       p.unconv
                  FROM prodserv p JOIN empresa e ON (e.idempresa = p.idempresa)
                 WHERE p.status = '?status?' 
                   AND p.tipo = '?tipo?'
                   ?andIdempresa?
              ORDER BY descr";
    }

    public static function buscarProdutoPorTipoEAtivo()
    {
        return "SELECT idprodserv, descr
                  FROM prodserv
                 WHERE tipo = '?tipo?' AND status = 'ATIVO'
              ORDER BY descr";
    }

    public static function buscarProdservPrprocPorIdPrProc()
    {
        return "SELECT p.idprodserv, p.descr
                  FROM prodservprproc c JOIN prodserv p ON (p.idprodserv = c.idprodserv AND p.status = 'ATIVO')
                 WHERE c.idprproc = ?idprproc?
              ORDER BY p.descr";
    }

    public static function listarProdservPorTipoEIdEmpresa()
    {
        return "SELECT idprodserv, descr
                  FROM prodserv
                 WHERE tipo = '?tipo?' 
                 ?getidempresa?
                 AND status = 'ATIVO'
            ORDER BY descr";
    }

    public static function buscarProdutosFormalizacao()
    {
        return "SELECT p.idprodserv,
                       p.descr,
                       p.codprodserv,
                       p.qtdpadrao,
                       p.qtdpadrao_exp,
                       p.formafarm
                  FROM prodserv p
                 WHERE p.tipo = '?tipo?' ?tipoNegocio?
                 ?getidempresa?
                   AND EXISTS (SELECT 1 FROM prodservprproc ppp WHERE ppp.idprodserv = p.idprodserv)
                   AND p.status = 'ATIVO'
              ORDER BY p.descr";
    }

    public static function buscarProdutoPorIdProdserv()
    {
        return "SELECT p.idprodserv, p.codprodserv, p.tipo, p.descr, 'prodserv' AS grupo, especial
                  FROM prodserv p
                 WHERE p.idprodserv = ?idprodserv?";
    }

    public static function buscarprodServPorIdUnidadeEIdEmpresa()
    {
        return "SELECT p.idprodserv, uo.idunidade, e.sigla, uo.idunidadeobjeto, p.descr, uo.idempresa
                FROM prodserv p
                JOIN empresa e ON(e.idempresa = p.idempresa)
                JOIN unidadeobjeto uo on(uo.idobjeto = p.idprodserv and uo.tipoobjeto = 'prodserv')
                WHERE uo.idunidade = ?idunidade?
                AND p.idempresa = ?idempresa?
                GROUP BY p.idprodserv
                ORDER BY p.descr";
    }

    public static function buscarProdServDisponivelParaVinculoEmUnidades()
    {
        return "SELECT ps.idprodserv, ps.descr, e.sigla
                FROM prodserv ps
                JOIN empresa e ON(e.idempresa = ps.idempresa)
                WHERE NOT EXISTS(
                    SELECT 1
                    FROM unidadeobjeto
                    WHERE idobjeto = ps.idprodserv
                    AND tipoobjeto = 'prodserv'
                    AND idunidade = ?idunidade?
                )
                AND ps.status = 'ATIVO'
                AND e.idempresa = ?idempresa?
                ORDER BY ps.descr";
    }

    public static function buscarprodServPorIdUnidadeEIdEmpresaAgrupadoPorDescricao()
    {
        return "SELECT p.idprodserv,
                       CONCAT(e.sigla,' - ',p.descr) as descr, 
                       p.un,
                       p.codprodserv
                  FROM prodserv p JOIN unidadeobjeto uo ON(uo.idobjeto = p.idprodserv AND uo.tipoobjeto = 'prodserv')
                  JOIN empresa e ON e.idempresa = p.idempresa                
                 WHERE uo.idunidade = ?idunidade?
                   AND p.status='ATIVO'
                   ?solmatPorSessionIdempresa?                   
                ORDER BY p.descr";
    }

    public static function buscarProdservVinculadoAoLote()
    {
        return "SELECT p.idprodserv, p.descr
                  FROM prodserv p
                 WHERE p.status = 'ATIVO'
                   AND p.tipo = 'PRODUTO'
                   AND p.venda = 'Y'
                   AND EXISTS(SELECT 1 FROM lote l  WHERE l.idprodserv = p.idprodserv)
                   AND p.especial = 'Y'
                   ?getidempresa?
              ORDER BY p.descr";
    }

    public static function buscarProdutoComFormula()
    {
        return "SELECT p.idprodserv, p.codprodserv, p.descr
                  FROM prodserv p JOIN lote l ON p.idprodserv = l.idprodserv
                  JOIN prodservformula f ON f.idprodservformula = l.idprodservformula
                 WHERE p.status = 'ATIVO'
                  AND p.tipo = 'PRODUTO'
                  AND p.venda = 'Y'                  
                  AND p.especial = 'Y'
                  AND f.status = 'ATIVO'
                  AND l.idpessoa IS NOT NULL
                  ?invalidacao?
                  ?strinplantel?
                  ?clausulad?
                  ?strvalidacao?
                  ?clausulalote?
             GROUP BY p.idprodserv
             ORDER BY p.descr";
    }

    public static function buscarProdutoPorDescr()
    {
        return "SELECT idprodserv, descr
                from prodserv
                where descr like '%?descr?%'";
    }

    public static function buscarAgentes()
    {
        return "SELECT ps.idprodserv, ps.descr
                from prodserv ps
                join tipoprodserv tp on tp.idtipoprodserv = ps.idtipoprodserv and tp.tipoprodserv = 'CONCENTRADOS'
                where ps.especial = 'Y'";
    }

    public static function buscarProdservAtivasSemVenda()
    {
        return "SELECT ps.idprodserv, ps.descr
                from prodserv ps
                where ps.idempresa = ?idempresa?
                and ps.status = 'ATIVO'
                AND ps.venda = 'N'";
    }

    public static function buscarProdservInsumoAtivasSemVenda()
    {
        return "SELECT ps.idprodserv, ps.descr
                from prodserv ps
                join prodservcontaitem pci on pci.idprodserv = ps.idprodserv
                join contaitem ci on ci.idcontaitem = pci.idcontaitem
                where ps.idempresa = ?idempresa?
                and ps.status = 'ATIVO'
                AND ps.venda = 'N'
                AND pci.idcontaitem in (507, 530)
                AND exists (
                    select 1
                    from lote l
                    join lotefracao lf on lf.idlote = l.idlote and lf.status = 'DISPONIVEL'
                    where l.idprodserv = ps.idprodserv
                    and l.status in('APROVADO', 'QUARENTENA')
                    and lf.idunidade = ?idunidade?
                )
                AND exists (
                    SELECT 1
                    from unidadeobjeto
                    where idobjeto = ps.idprodserv
                    and tipoobjeto = 'prodserv'
                    and idunidade = ?idunidade?
                )
                group by ps.idprodserv;";
    }

    public static function buscarEntradaProdserv()
    {
        return "SELECT GROUP_CONCAT(l.idlote) as idlote,
                        n.idnf,
                        n.nnfe,
                        ROUND(IF(valconvori > 0, 
                                ((ni.total + IFNULL(ni.valipi, 0) + IFNULL(ni.vst, 0) -  IFNULL(nx.valicms, 0)) / IF(l.valconvori > 0, l.valconvori * ni.qtd, ni.qtd)) 
                                    + IFNULL((((((ni.total + IFNULL(ni.valipi, 0) + IFNULL(ni.vst, 0) - IFNULL(nx.valicms, 0)) * 100) / n.total) /100) * ni2.vlritem / IF(l.valconvori > 0, l.valconvori * ni.qtd, ni.qtd)), 0), 
                                ((ni.total + IFNULL(ni.valipi, 0) + IFNULL(ni.vst, 0) -  IFNULL(nx.valicms, 0)) / IF(l.valconvori > 0, l.valconvori * ni.qtd, ni.qtd)) 
                                    + IFNULL((((((ni.total + IFNULL(ni.valipi, 0) + IFNULL(ni.vst, 0) - IFNULL(nx.valicms, 0)) * 100) / n.total) /100) * ni2.vlritem / IF(l.valconvori > 0, l.valconvori * ni.qtd, ni.qtd)), 0)), 2) as vlritem,
						IF(valconvori > 0, l.valconvori * ni.qtd, ni.qtd) as qtd,
                        n.dtemissao
                FROM nf n JOIN nfitem ni ON n.idnf = ni.idnf AND ni.nfe = 'Y' AND ni.qtd > 0
                LEFT JOIN nf n2 ON n2.idobjetosolipor = n.idnf AND n2.tipoobjetosolipor = 'nf' AND n2.tiponf = 'T' AND n2.status <> 'CANCELADO'
                LEFT JOIN nfitem ni2 ON n2.idnf = ni2.idnf  
                JOIN lote l ON l.idnfitem = ni.idnfitem
                JOIN nfitemxml nx ON nx.idnf = n.idnf AND nx.idprodserv = ni.idprodserv AND nx.status = 'Y'
           LEFT JOIN prodservforn pf ON pf.idprodservforn = ni.idprodservforn
                WHERE n.tiponf NOT IN ('R', 'D', 'T', 'E', 'V') 
                    AND ni.idprodserv = ?idprodserv?
                    AND n.dtemissao BETWEEN '?datainicial?' AND '?datafinal?'
                    AND n.status IN ('APROVADO', 'DIVERGENCIA', 'CONCLUIDO')
                GROUP BY n.idnf";
    }

    public static function buscarLotesVencidosOuProximos()
    {
        return "SELECT 
                    f.idlotefracao,
                    l.idprodserv, 
                    e.sigla, 
                    l.idlote, 
                    p.descr, 
                    l.partida as partidainterna, 
                    l.exercicio, 
                    l.vencimento, 
                    RECUPERAEXPOENTE(`f`.`qtd`, `f`.`qtd_exp`) AS `estoque`, 
                    l.partidaext as partida,
                    pe.nome as cliente
                FROM lote l
                JOIN pessoa pe on pe.idpessoa = l.idpessoa
                JOIN lotefracao f on f.idlote = l.idlote AND f.idempresa = l.idempresa
                JOIN prodserv p on p.idprodserv = l.idprodserv
                LEFT JOIN empresa e ON e.idempresa = l.idempresa
                WHERE (
                    DATE_FORMAT(CURDATE(), '%Y-%m-%d') >= `l`.`vencimento`
                    ?intervaloSQL?
                )
                AND f.status = 'DISPONIVEL'
                AND l.idempresa = ?idempresa?
                ?clausulaunidade?
                ORDER BY vencimento DESC";
    }

    public static function pegaCustoPeriodo()
    {
        return "SELECT p.descr, c.*
                FROM custo c
                    INNER JOIN prodserv p ON (p.idprodserv = c.idprodserv)
                WHERE
                    c.idprodserv = ?idprodserv?
                    AND datacusto BETWEEN '?datainicial?' AND '?datafinal?';";
    }

    public static function buscarQtdValorLoteProdserv()
    {
        return "SELECT ifnull(sum(f.qtd),0) as qtdest,
                        l.vlrlote,
                        f.criadoem                
			       FROM prodserv p 
			       JOIN lote l ON(l.idprodserv = p.idprodserv AND l.status IN ('APROVADO','QUARENTENA')) AND p.idprodserv = ?idprodserv?
			       JOIN lotefracao f ON(f.idlote = l.idlote AND f.status = 'DISPONIVEL' AND  f.idunidade = p.idunidadeest)
			      WHERE p.comprado = 'Y' OR p.fabricado = 'Y'
			        AND p.tipo = 'PRODUTO' 			        
                    AND f.alteradoem BETWEEN '?dataInicial?' AND '?dataFinal?'
			   GROUP BY p.idprodserv;";
    }

    public static function listaProdservTranferencia()
    {
        return "SELECT p.idprodserv, p.descr
                FROM
                    prodserv p
                    JOIN prodservcontaitem t on t.idprodserv = p.idprodserv
                    JOIN contaitem c on c.idcontaitem = t.idcontaitem
                    AND c.somarelatorio = 'N'
                WHERE
                    p.status = 'ATIVO'
                    AND p.tipo = 'SERVICO'
                    AND p.idempresa = ?idempresa?
                    ORDER BY p.descr desc;";
    }

    public static function buscarExamesPorIdUnidade()
    {
        return "SELECT
                    ps.idprodserv,
                    cp.idcontrato,
                    ps.descr,
                    IF(ISNULL(pe.status) OR pe.status != 'ATIVO', '-', IFNULL(ps.vlrvenda, '-')) as valor,
                    IF(
                        ISNULL(pe.status) OR pe.status != 'ATIVO',
                        '-',
                        IFNULL(
                            IF(
                                d.tipodesconto = 'P', 
                                ps.vlrvenda - ((d.desconto / 100) * ps.vlrvenda), 
								IFNULL(d.desconto, 0)
                            ), 
                        '-')
                    ) as valorFinal,
                     IF(
                        ISNULL(pe.status) OR pe.status != 'ATIVO',
                        '-',
                        IFNULL(
                            IF(
                                d.tipodesconto = 'P', 
                               ((d.desconto / 100) * ps.vlrvenda), 
								 (ps.vlrvenda - IFNULL(d.desconto, 0))
                            ), 
                        '-')
                    ) as desconto,
                    d.tipodesconto,
                    pe.status
                FROM pessoacontato pc 
				join pessoa pe on pe.idpessoa = pc.idpessoa and pe.status = 'ATIVO' -- empresa                
                join  prodserv ps on(ps.venda = 'Y' and ps.tipo = 'SERVICO'  and ps.status = 'ATIVO' AND ps.idtipoprodserv in(359, 358))			
				left join contratopessoa cp on cp.idpessoa = pe.idpessoa 
                left join desconto d on ps.idprodserv = d.idtipoteste and cp.idcontrato=d.idcontrato             
                join unidadeobjeto uo on uo.idobjeto = ps.idprodserv and uo.tipoobjeto = 'prodserv'  
				left join portaria p on(ps.idportaria=p.idportaria)                
                WHERE uo.idunidade = ?idunidade?
                and pc.idcontato = ?idpessoa?
                group by ps.idprodserv
                order by 
                    CASE WHEN pe.status = 'ATIVO' THEN 1 ELSE 2 END, 
                    valorFinal DESC,
                    ps.descr;";
    }
    public static function buscarProdservServicosExames()
    {
        return "SELECT p.idprodserv, p.descr
                from laudo.prodserv p
                where p.tipo = 'SERVICO'
                and p.status = 'ATIVO'
                and p.venda = 'Y'
                and p.idtipoprodserv in (359, 358)
                ";
    }

    public static function buscarProdutosVendaPorEmpresa()
    {
        $sql = "SELECT t.tipoprodserv, p.idprodserv, p.descr, p.un, pf.idprodservformula, pf.rotulo
                FROM
                    laudo.prodserv p
                    INNER JOIN tipoprodserv t ON t.idtipoprodserv = p.idtipoprodserv
                    LEFT JOIN prodservformula pf ON pf.idprodserv = p.idprodserv AND pf.status = 'ATIVO'
                WHERE
                    p.status = 'ATIVO'
                    AND p.venda = 'Y'
                    AND p.produtoacabado = 'Y'
                    AND p.tipo = 'PRODUTO'
                    AND p.idempresa = ?idempresa?
                    ?tipoprodserv?
                    ?subcategoria?
                ORDER BY p.descr";
        return $sql;
    }

    public static function buscaDadosProdutoForecast()
    {
        return "
                SELECT * FROM (
                    SELECT
                            a.idempresa, c.contaitem, a.idprodserv, a.descr, a.un, a.descrcurta, a.rotulo, a.idprodservformula, pl.plantel, t.tipoprodserv, t.idtipoprodserv, SUM(
                            CASE
                                WHEN ps.planejado IS NULL THEN 0
                                ELSE 1
                            END
                        ) AS planejado, JSON_OBJECT(
                            'meses', JSON_ARRAYAGG(
                                JSON_OBJECT(
                                    'idplanejamentoprodserv', ps.idplanejamentoprodserv, 'planejado', ps.planejado, 'vlrticketmedio', ps.vlrticketmedio, 'adicional', ps.adicional, 'mes', a.mes, 'acao', CASE
                                        WHEN ps.idplanejamentoprodserv IS NULL THEN 'i'
                                        ELSE 'u'
                                    END
                                )
                            )
                        ) AS produto_json
                    FROM (
                            WITH
                                meses AS (
                                    SELECT 12 AS mes
                                    UNION ALL
                                    SELECT 11
                                    UNION ALL
                                    SELECT 10
                                    UNION ALL
                                    SELECT 9
                                    UNION ALL
                                    SELECT 8
                                    UNION ALL
                                    SELECT 7
                                    UNION ALL
                                    SELECT 6
                                    UNION ALL
                                    SELECT 5
                                    UNION ALL
                                    SELECT 4
                                    UNION ALL
                                    SELECT 3
                                    UNION ALL
                                    SELECT 2
                                    UNION ALL
                                    SELECT 1
                                )
                            SELECT p.idempresa, p.descr, p.un, p.descrcurta, p.idprodserv, p.idtipoprodserv, pf.idprodservformula, CONCAT(
                                    IFNULL(pf.rotulo, ''), IFNULL(concat('- d ', pf.dose), ''), IFNULL(
                                        concat(
                                            ' -', pf.volumeformula, ' ', pf.un
                                        ), ''
                                    )
                                ) as rotulo, pf.idplantel, m.mes, p.status, p.venda, p.produtoacabado, p.tipo
                            FROM
                                prodserv p
                                LEFT JOIN prodservformula pf on pf.idprodserv = p.idprodserv AND pf.status not in('INATIVO')
                                CROSS JOIN meses m
                    ) AS a
                LEFT JOIN planejamentoprodserv ps ON ps.idprodserv = a.idprodserv
                AND a.idprodservformula=ps.idprodservformula AND ps.mes = a.mes AND ps.exercicio = ?exercicio?
                LEFT JOIN plantel pl ON pl.idplantel = a.idplantel
                INNER JOIN tipoprodserv t ON t.idtipoprodserv = a.idtipoprodserv
                LEFT JOIN prodservcontaitem ci on ci.idprodserv = a.idprodserv
                LEFT JOIN contaitem c on c.idcontaitem = ci.idcontaitem
                WHERE
                    a.status = 'ATIVO'
                    AND a.venda = 'Y'
                     AND a.produtoacabado = 'Y' AND a.tipo='PRODUTO' 
                    AND a.idempresa = ?idempresa?
                    ?tipoprodserv?
                    ?subcategoria?
                GROUP BY a.idprodserv, a.idprodservformula
                ORDER BY t.tipoprodserv, a.idprodserv, a.idprodservformula, a.mes
            ) as a2
            where 1 ?planejado?
        ";
    }
    public static function buscaDadosProdutoForecastLaudo()
    {
        return "
                 SELECT * FROM (
                    SELECT
                        a.idempresa, a.idprodserv, a.descr, a.un, a.descrcurta, a.rotulo, a.idprodservformula, pl.plantel, t.tipoprodserv, t.idtipoprodserv, SUM(
                            CASE
                                WHEN ps.planejado IS NULL THEN 0
                                ELSE 1
                            END
                        ) AS planejado, JSON_OBJECT(
                            'meses', JSON_ARRAYAGG(
                                JSON_OBJECT(
                                    'idplanejamentoprodserv', ps.idplanejamentoprodserv, 'planejado', ps.planejado, 'vlrticketmedio', ps.vlrticketmedio, 'adicional', ps.adicional, 'mes', a.mes, 'acao', CASE
                                        WHEN ps.idplanejamentoprodserv IS NULL THEN 'i'
                                        ELSE 'u'
                                    END
                                )
                            )
                        ) AS produto_json
                    FROM (
                            WITH
                                meses AS (
                                    SELECT 12 AS mes
                                    UNION ALL
                                    SELECT 11
                                    UNION ALL
                                    SELECT 10
                                    UNION ALL
                                    SELECT 9
                                    UNION ALL
                                    SELECT 8
                                    UNION ALL
                                    SELECT 7
                                    UNION ALL
                                    SELECT 6
                                    UNION ALL
                                    SELECT 5
                                    UNION ALL
                                    SELECT 4
                                    UNION ALL
                                    SELECT 3
                                    UNION ALL
                                    SELECT 2
                                    UNION ALL
                                    SELECT 1
                                )
                            SELECT p.idempresa, p.descr, p.un, p.descrcurta, p.idprodserv, p.idtipoprodserv, '' as idprodservformula, '' as rotulo,
                                '' as idplantel, m.mes, p.status, p.venda, p.produtoacabado, p.tipo
                            FROM
                                prodserv p
                                CROSS JOIN meses m
                    ) AS a
                LEFT JOIN planejamentoprodserv ps ON ps.idprodserv = a.idprodserv AND ps.mes = a.mes AND ps.exercicio = ?exercicio?
                LEFT JOIN plantel pl ON pl.idplantel = a.idplantel
                INNER JOIN tipoprodserv t ON t.idtipoprodserv = a.idtipoprodserv
                WHERE
                    a.status = 'ATIVO'
                    AND a.venda = 'Y'
                    AND a.tipo = 'SERVICO'
                    AND a.idempresa = ?idempresa?
                    ?tipoprodserv?
                    ?subcategoria?
                GROUP BY a.idprodserv
                ORDER BY t.tipoprodserv, a.idprodserv, a.mes
            ) as a2
            where 1 ?planejado?
        ";
    }

    public static function buscaForecastCriado()
    {
        return "SELECT exercicio from forecastvenda where idempresa = ?idempresa?;";
    }

    public static function BuscaForecastComprasLigadosForecastVenda()
    {
        return "SELECT * FROM forecastcompra WHERE idforecastvenda = ?idforecastvenda?";
    }

    public static function buscarSubcategoriaPorProdserv()
    {
        return "SELECT la.idloteativ
                    FROM formalizacao f
                            JOIN lote l ON (f.idlote = l.idlote)
                            JOIN prodserv p ON (p.idprodserv = l.idprodserv AND p.fabricado = 'Y')
                            JOIN tipoprodserv tp ON (tp.idtipoprodserv = p.idtipoprodserv)
                            JOIN loteativ la ON (la.idlote = l.idlote)
                            JOIN objetovinculo ov ON (ov.idobjetovinc = la.idloteativ AND tipoobjetovinc = 'loteativ')
                            LEFT JOIN resultado r ON (r.idresultado = ov.idobjeto AND tipoobjeto = 'resultado')
                            LEFT JOIN amostra a ON (a.idamostra = r.idamostra)
                    WHERE idformalizacao = ?idformalizacao?
                    AND f.idempresa = ?idempresa?
                    AND tp.tipoprodserv IN ('MEIOS FORMULADOS', 'CONCENTRADOS', 'MATERIAL LABORATÓRIO E PRODUÇÃO');";
    }

    public static function verifcarProdutoCliente()
    {
        return "SELECT GROUP_CONCAT(DISTINCT pf.idprodserv) AS idprodserv 
                  FROM prodservformulapref pfp JOIN prodservformula pf ON pf.idprodservformula = pfp.idprodservformula
                 WHERE pfp.idpessoa = ?idpessoa?";
    }
}
