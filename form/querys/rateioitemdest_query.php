<?
class RateioItemDestQuery
{
    public static function inserir()
    {
        return "INSERT INTO rateioitemdest (
                idempresa, idrateioitem, idobjeto, tipoobjeto, 
                valor, idpessoa, idobjetoinicio, tipoobjetoinicio, 
                valorinicio, criadopor, criadoem, alteradopor, alteradoem
            ) VALUES (
                ?idempresa?, ?idrateioitem?, ?idobjeto?, '?tipoobjeto?', 
                ?valor?, ?idpessoa?, ?idobjetoinicio?, '?tipoobjetoinicio?', 
                ?valorinicio?, '?criadopor?', ?criadoem?, '?alteradopor?', ?alteradoem?
            )";
    }

    public static function atualizarUnidadeNoRateioItemDest()
    {
        return "UPDATE rateioitemdest
                SET idobjeto = ?novoidunidade?, alteradopor = 'sislaudo', alteradoem = now()
                WHERE idobjeto = ?idunidade?
                AND tipoobjeto = 'unidade'";
    }

    public static function buscarNfSemRateio()
    {
        return  "SELECT '?tipo?' AS tipo,
                        i.qtd,
                        IFNULL(i.un, pr.un) AS un,
                        i.idnfitem AS idtipo,
                        r.idrateio,
                        r.idrateioitem,
                        dt.idrateioitemdest,
                        n.idnf,
                        n.nnfe,
                        dt.idobjeto,
                        dt.tipoobjeto,
                        dt.status,
                        i.idtipoprodserv,
                        tp.tipoprodserv,
                        IFNULL(pr.descr, i.prodservdescr) AS descr,
                        ROUND(((i.total + IFNULL(i.valipi, 0) + IFNULL(n.frete, 0)) * (IFNULL(dt.valor, 100) / 100)) , 2) AS rateio,
                        IFNULL(dt.valor, 0) AS valor,
                        e.unidade AS empresa,
                        GROUP_CONCAT(rdn.idrateioitemdestnf,'@',dt.idrateioitemdest) as idrateioitemdestnf,
                        n.dtemissao,
                        dt.idobjeto,
                        dt.alteradopor,
                        dt.alteradoem
                   FROM nf n JOIN nfitem i ON (i.idnf = n.idnf)
                   JOIN rateioitem r ON (r.tipoobjeto = '?rtipoobjeto?' AND r.idobjeto = i.idnfitem)
                   JOIN rateioitemdest dt ON (dt.idrateioitem = r.idrateioitem )
                  left JOIN unidade e ON (e.idunidade = dt.idobjeto AND dt.tipoobjeto = '?dttipoobjeto?') 
              LEFT JOIN prodserv pr ON (pr.idprodserv = i.idprodserv)
              LEFT JOIN tipoprodserv tp ON (tp.idtipoprodserv = i.idtipoprodserv)
              LEFT JOIN rateioitemdestnf rdn ON(dt.idrateioitemdest=rdn.idrateioitemdest)
                  WHERE n.tiponf IN (?tiponf?)
                    AND n.status NOT IN (?status?) 
                    -- AND dt.valor > 0
                    ?clausula?
               GROUP BY i.idnfitem, dt.idrateioitemdest
               ORDER BY  descr, tipoobjeto, empresa, idobjeto, tipoprodserv";
    }

    public static function buscarRateioItemNfItem()
    {
        return "SELECT p.idunidadeest,
                       p.idprodserv,
                       p.tempoconsrateio,
                       p.tipo,
                       i.idnfitem,
                       i.idpessoa as idpessoaitem,
                       ps.idpessoa,
                       ps.nome,
                       i.qtd,
                       IFNULL(i.un, p.un) AS un,
                       IFNULL(p.descr, i.prodservdescr) AS descr,
                       ROUND(((i.total + IFNULL(i.valipi, 0) + IFNULL(n.frete, 0)) * (IFNULL(rd.valor, 100) / 100)), 2) AS rateio,
                       ri.idrateio,
                       ri.idrateioitem,
                       IFNULL(rd.valor, 0) AS valorateio,
                       GROUP_CONCAT(rdn.idrateioitemdestnf,'@',rd.idrateioitemdest) as idrateioitemdestnf,
                       rd.*
                  FROM nfitem i JOIN nf n ON (n.idnf = i.idnf)
             LEFT JOIN prodserv p ON (p.idprodserv = i.idprodserv)
             LEFT JOIN rateioitem ri ON (ri.idobjeto = i.idnfitem AND ri.tipoobjeto = 'nfitem')
             LEFT JOIN rateioitemdest rd ON (rd.idrateioitem = ri.idrateioitem)
             LEFT JOIN rateioitemdestnf rdn ON(rd.idrateioitemdest=rdn.idrateioitemdest)
                  JOIN contaitem c ON (c.idcontaitem = i.idcontaitem AND c.somarelatorio = 'Y')
             LEFT JOIN pessoa ps ON (ps.idpessoa = rd.idpessoa)
                 WHERE -- i.idpessoa IS NULL AND
                  i.qtd > 0
                   AND i.nfe = 'Y'
                   ?clausula?
              GROUP BY i.idnfitem , rd.idrateioitemdest  
              order by ri.idrateio, descr";
    }

    public static function inserirRateioItemDest()
    {
        return "INSERT INTO rateioitemdest (idempresa, 
                                            idrateioitem, 
                                            idobjeto, 
                                            tipoobjeto, 
                                            valor, 
                                            idpessoa, 
                                            criadopor, 
                                            criadoem, 
                                            alteradopor, 
                                            alteradoem) 
                                    VALUES (?idempresa?, 
                                            ?idrateioitem?, 
                                            ?idobjeto?, 
                                            '?tipoobjeto?', 
                                            '?valor?', 
                                            ?idpessoa?, 
                                            '?usuario?', 
                                            NOW(), 
                                            '?usuario?', 
                                            NOW())";
    }

    public static function buscarvalorRateioitemdest(){
        return "SELECT 
                    idobjeto, tipoobjeto, rateio, valor,idempresa
                FROM
                    (SELECT 
                        idobjeto AS idobjeto,
                            tipoobjeto AS tipoobjeto,
                            rateio AS rateio,
                            vlrrateio AS valor,
                            idempresa
                    FROM
                        (SELECT 
                        ROUND(SUM(IF((`rid`.`valor` IS NOT NULL), (`a`.`total` * (`rid`.`valor` / 100)), `a`.`total`)), 2) AS `rateio`,
                            `rid`.`valor` AS `vlrrateio`,
                            `rid`.`tipoobjeto` AS `tipoobjeto`,
                            `rid`.`idobjeto` AS idobjeto,
                            a.idempresa,
                            rid.idrateioitemdest
                    FROM
                        ((((((SELECT 
                        (((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - `n`.`frete`)) * `n`.`frete`)) ) )) AS `total`,
                            i.idnfitem,
                            n.idempresa
                    FROM
                        ((((((`nf` `n`
                    JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
                        AND (`i`.`nfe` = 'Y'))))
                    JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
                    JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)))
                    JOIN `contapagar` `cp` ON (((`cp`.`idobjeto` = `n`.`idnf`)
                        AND (`cp`.`tipoobjeto` = 'nf'))))
                    LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
                    JOIN `empresa` `e` ON ((`e`.`idempresa` = `cp`.`idempresa`)))
                    WHERE
                        ((`cp`.`tipoespecifico` <> 'AGRUPAMENTO')
                            AND (`cp`.`status` <> 'INATIVO')
                            AND (`cp`.`tipo` = 'D')
                            AND (`cp`.`valor` > 0)
                            AND (`n`.`tiponf` NOT IN ('S' , 'R')))
                            AND i.idnfitem = ?idnfitem?  group by i.idnfitem
                            UNION  
                    SELECT 
                        (((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - `n`.`frete`)) * `n`.`frete`)) ) )) AS `total`,
                            i.idnfitem,
                            n.idempresa
                    FROM
                        (((((((`contapagar` `cp`
                    JOIN `contapagaritem` `ci` ON (((`cp`.`idcontapagar` = `ci`.`idcontapagar`)
                        AND (`ci`.`tipoobjetoorigem` = 'nf'))))
                    JOIN `nf` `n` ON ((`ci`.`idobjetoorigem` = `n`.`idnf`)))
                    JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
                        AND (`i`.`nfe` = 'Y'))))
                    JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
                    JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)))
                    LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
                    JOIN `empresa` `e` ON ((`e`.`idempresa` = `cp`.`idempresa`)))
                    WHERE
                        ((`cp`.`tipoespecifico` = 'AGRUPAMENTO')
                            AND (`cp`.`status` <> 'INATIVO')
                            AND (`ci`.`status` <> 'INATIVO')
                            AND (`cp`.`tipo` = 'D')
                            AND (`cp`.`valor` > 0)
                            AND (`n`.`tiponf` NOT IN ('S' , 'R')))
                            AND i.idnfitem = ?idnfitem? group by i.idnfitem
                    UNION  
                    SELECT 
                        ((((IFNULL(`i`.`total`, 0) * (`n`.`total` / IFNULL(n.subtotal, n.total))) ))) AS `total`,
                            i.idnfitem,
                            n.idempresa
                    FROM
                        ((((((`nf` `n`
                    JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
                        AND (`i`.`nfe` = 'Y'))))
                    JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
                    JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)))
                    JOIN `contapagar` `cp` ON (((`cp`.`idobjeto` = `n`.`idnf`)
                        AND (`cp`.`tipoobjeto` = 'nf'))))
                    LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
                    JOIN `empresa` `e` ON ((`e`.`idempresa` = `cp`.`idempresa`)))
                    WHERE
                        ((`cp`.`tipoespecifico` <> 'AGRUPAMENTO')
                            AND (`cp`.`status` <> 'INATIVO')
                            AND (`cp`.`tipo` = 'D')
                            AND (`cp`.`valor` > 0)
                            AND (`n`.`tiponf` IN ('S' , 'R')))
                            AND i.idnfitem = ?idnfitem?  group by i.idnfitem
                    UNION  
                    SELECT 
                        ((((IFNULL(`i`.`total`, 0) * (`n`.`total` / IFNULL(n.subtotal, n.total)))) ) ) AS `total`,
                            i.idnfitem,
                            n.idempresa
                    FROM
                        (((((((`contapagar` `cp`
                    JOIN `contapagaritem` `ci` ON (((`cp`.`idcontapagar` = `ci`.`idcontapagar`)
                        AND (`ci`.`tipoobjetoorigem` = 'nf'))))
                    JOIN `nf` `n` ON ((`ci`.`idobjetoorigem` = `n`.`idnf`)))
                    JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
                        AND (`i`.`nfe` = 'Y'))))
                    JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
                    JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)))
                    LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
                    JOIN `empresa` `e` ON ((`e`.`idempresa` = `cp`.`idempresa`)))
                    WHERE
                        ((`cp`.`tipoespecifico` = 'AGRUPAMENTO')
                            AND (`cp`.`status` <> 'INATIVO')
                            AND (`ci`.`status` <> 'INATIVO')
                            AND (`cp`.`tipo` = 'D')
                            AND (`cp`.`valor` > 0)
                            AND (`n`.`tiponf` IN ('S' , 'R')))
                            AND i.idnfitem = ?idnfitem? group by i.idnfitem
                    ) `a`
                    LEFT JOIN `rateioitem` `ri` ON (((`ri`.`idobjeto` = `a`.`idnfitem`)
                        AND (`ri`.`tipoobjeto` = 'nfitem'))))
                    LEFT JOIN `rateioitemdest` `rid` ON ((`rid`.`idrateioitem` = `ri`.`idrateioitem`)))
                    LEFT JOIN `unidade` `u` ON (((`u`.`idunidade` = `rid`.`idobjeto`)
                        AND (`rid`.`tipoobjeto` = 'unidade'))))
                    LEFT JOIN `empresa` `e` ON ((`e`.`idempresa` = `u`.`idempresa`))))
                    WHERE
                        1
                    GROUP BY idrateioitemdest) v
                    WHERE
                        idrateioitemdest = ?idrateioitemdest?) AS u";
    }

    public static function listarRateioitemdestnfPorIdrateioitemdest(){
        return "SELECT 
                    dn.idrateioitemdestnf,
                    dn.valor,
                    dn.rateio,
                    dn.criadopor,
                    dn.criadoem,
                    n.idnf,
                    n.status,
                    p.idpessoa,
                    p.nome
                FROM
                    rateioitemdestnf dn
                        JOIN
                    nf n ON (n.idnf = dn.idnf)
                        JOIN
                    pessoa p ON (p.idpessoa = n.idpessoa)
                WHERE
                    dn.idrateioitemdest =?idrateioitemdest? ";
    }
    public static function listarRateioitemdestnfPorIdnf(){
        return "SELECT 
                    dn.idrateioitemdestnf,
                    dn.idrateioitemdest,
                    dn.valor,
                    dn.rateio,
                    i.idnf,
                    CONCAT(IFNULL(CONCAT(e.sigla, ' - '), ''),
                            p.descr) AS descr,
                    i.prodservdescr,
                    u.idunidade,
                    u.unidade
                
                FROM
                    rateioitemdestnf dn
                        JOIN
                    rateioitemdest d ON (d.idrateioitemdest = dn.idrateioitemdest)
                        JOIN
                    rateioitem r ON (r.idrateioitem = d.idrateioitem
                        AND r.tipoobjeto = 'nfitem')
                        JOIN
                    nfitem i ON (i.idnfitem = r.idobjeto)
                        LEFT JOIN
                    prodserv p ON (p.idprodserv = i.idprodserv)
                        LEFT JOIN
                    empresa e ON (e.idempresa = p.idempresa)
                        LEFT JOIN
                    unidade u on(u.idunidade = d.idobjeto)
                WHERE
                    dn.idnf = ?idnf? group by  dn.idrateioitemdestnf";
    }

    public static function verificaritemIdnf(){
        return "SELECT 
                    *
                FROM
                   nfitem
                WHERE
                    idnf = ?idnf? ";
    }

    public static function listarRateioitemdestnfPorIdnfAgrupadoUnidade(){
        return "SELECT 
                     SUM(dn.valor) AS valor, u.idunidade, u.unidade
                FROM
                    rateioitemdestnf dn
                        JOIN
                    rateioitemdest d ON (d.idrateioitemdest = dn.idrateioitemdest)
                        JOIN
                    rateioitem r ON (r.idrateioitem = d.idrateioitem
                        AND r.tipoobjeto = 'nfitem')
                        JOIN
                    nfitem i ON (i.idnfitem = r.idobjeto)
                        LEFT JOIN
                    prodserv p ON (p.idprodserv = i.idprodserv)
                        LEFT JOIN
                    empresa e ON (e.idempresa = p.idempresa)
                        LEFT JOIN
                    unidade u on(u.idunidade = d.idobjeto)
                WHERE
                    dn.idnf = ?idnf?  GROUP BY  u.idunidade";
    }
    public static function listarRateioitemdestnfPorIdnfAgrupado(){
        return "SELECT 
                     SUM(dn.valor) AS valor
                FROM
                    rateioitemdestnf dn
                        JOIN
                    rateioitemdest d ON (d.idrateioitemdest = dn.idrateioitemdest)
                        JOIN
                    rateioitem r ON (r.idrateioitem = d.idrateioitem
                        AND r.tipoobjeto = 'nfitem')
                        JOIN
                    nfitem i ON (i.idnfitem = r.idobjeto)
                        LEFT JOIN
                    prodserv p ON (p.idprodserv = i.idprodserv)
                        LEFT JOIN
                    empresa e ON (e.idempresa = p.idempresa)
                        LEFT JOIN
                    unidade u on(u.idunidade = d.idobjeto)
                WHERE
                    dn.idnf = ?idnf? ";
    }

    public static function buscarConfEmpresaCobranca(){
        return "SELECT 
                    idempresa,
                    idformapagamento,
                    idcontaitem,
                    idtipoprodserv,
                    idempresad,
                    idformapagamentod,
                    idcontaitemd,
                    idtipoprodservd
                FROM
                    empresacobranca
                WHERE
                    idempresa = ?idempresa? AND idempresad = ?idempresad?";


    }

    public static function buscarRateioCusto()
    {
        return  "SELECT 'nfitem' AS tipo,
                        i.qtd,
                        IFNULL(i.un, pr.un) AS un,
                        i.idnfitem AS idtipo,
                        r.idrateio,
                        r.idrateioitem,
                        dt.idrateioitemdest,
                        n.idnf,
                        n.nnfe,
                        dt.idobjeto,
                        dt.tipoobjeto,
                        dt.status,
                        i.idtipoprodserv,
                        tp.tipoprodserv,
                        IFNULL(pr.descr, i.prodservdescr) AS descr,
                        ROUND(((i.total + IFNULL(i.valipi, 0) + IFNULL(n.frete, 0)) * (IFNULL(dt.valor, 100) / 100)) , 2) AS rateio,
                        IFNULL(dt.valor, 0) AS valor,
                        e.unidade AS empresa,
                        GROUP_CONCAT(rdn.idrateioitemdestnf,'@',dt.idrateioitemdest) as idrateioitemdestnf,
                        n.dtemissao,
                        dt.idobjeto,
                        dt.alteradopor,
                        dt.alteradoem
                   FROM nf n JOIN nfitem i ON (i.idnf = n.idnf)
                   JOIN rateioitem r ON (r.tipoobjeto = 'nfitem' AND r.idobjeto = i.idnfitem)
                   JOIN rateioitemdest dt ON (dt.idrateioitem = r.idrateioitem )
                  left JOIN unidade e ON (e.idunidade = dt.idobjeto AND dt.tipoobjeto = 'unidade') 
              LEFT JOIN prodserv pr ON (pr.idprodserv = i.idprodserv)
              LEFT JOIN tipoprodserv tp ON (tp.idtipoprodserv = i.idtipoprodserv)
              LEFT JOIN rateioitemdestnf rdn ON(dt.idrateioitemdest=rdn.idrateioitemdest)
                  WHERE 
                     n.status NOT IN ('CANCELADO','REPROVADO') 
                     ?clausula?
               GROUP BY i.idnfitem, dt.idrateioitemdest
              union all
            SELECT 'resultado' AS tipo,
                        rs.quantidade as qtd,
                        'Teste' AS un,
                        rs.idresultado AS idtipo,
                        r.idrateio,
                        r.idrateioitem,
                        dt.idrateioitemdest,
                        rs.idresultado as idnf,
                        concat(a.idregistro,'/',a.exercicio) AS nnfe,
                        dt.idobjeto,
                        dt.tipoobjeto,
                        dt.status,
                        pr.idtipoprodserv,
                        tp.tipoprodserv,
                        pr.descr AS descr,
                        rs.custo AS rateio,
                        IFNULL(dt.valor, 0) AS valor,
                        e.unidade AS empresa,
                        GROUP_CONCAT(rdn.idrateioitemdestnf,'@',dt.idrateioitemdest) as idrateioitemdestnf,
                       rs.dataconclusao AS `dtemissao`,
                        dt.idobjeto,
                        dt.alteradopor,
                        dt.alteradoem
                   FROM resultado rs JOIN amostra a ON (a.idamostra = rs.idamostra)
                   JOIN rateioitem r ON (r.tipoobjeto = 'resultado' AND r.idobjeto = rs.idresultado)
                   JOIN rateioitemdest dt ON (dt.idrateioitem = r.idrateioitem )
                  left JOIN unidade e ON (e.idunidade = dt.idobjeto AND dt.tipoobjeto = 'unidade') 
              LEFT JOIN prodserv pr ON (pr.idprodserv = rs.idtipoteste)
              LEFT JOIN tipoprodserv tp ON (tp.idtipoprodserv = pr.idtipoprodserv)
              LEFT JOIN rateioitemdestnf rdn ON(dt.idrateioitemdest=rdn.idrateioitemdest)                  
              WHERE 
                     rs.status NOT IN ('CANCELADO') 
                   ?clausula?
               GROUP BY rs.idresultado, dt.idrateioitemdest
               ORDER BY  descr, tipoobjeto, empresa, tipoprodserv;";
    }
}

?>