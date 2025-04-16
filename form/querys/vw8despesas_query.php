<?
class Vw8DespesasQuery
{
    public static function buscarRateioPorCentroDeCusto()
    {
        return " SELECT 
        `a`.`tiponf` AS `tiponf`,
        `a`.`idcontaitem` AS `idcontaitem`,
        `a`.`contaitem` AS `contaitem`,
        `a`.`idtipoprodserv` AS `idtipoprodserv`,
        `a`.`tipoprodserv` AS `tipoprodserv`,
        `a`.`cor` AS `cor`,
        `a`.`previsao` AS `previsao`,
        `a`.`status` AS `status`,
        `a`.`tipo` AS `tipo`,
        `a`.`faturamento` AS `faturamento`,
        `a`.`ordem` AS `ordem`,
        `a`.`descricao` AS `descricao`,
        `a`.`idnf` AS `idnf`,
        `a`.`datareceb` AS `datareceb`,
        `a`.`idempresa` AS `idempresa`,
        `a`.`idagencia` AS `idagencia`,
        `a`.`idnfitem` AS `idnfitem`,
        `a`.`idcontapagar` AS `idcontapagar`,
        `a`.`qtd` AS `qtd`,
        `a`.`un` AS `un`,
        `a`.`total` AS `total`,
        `a`.`parcela` AS `parcela`,
        `a`.`parcelas` AS `parcelas`,
        `a`.`nnfe` AS `nnfe`,
        `a`.`vlritem` AS `vlritem`,
        ROUND(sum(IF((`rid`.`valor` IS NOT NULL),
                    (`a`.`total` * (`rid`.`valor` / 100)),
                    `a`.`total`)),
                2) AS `rateio`,
        `rid`.`valor` AS `vlrrateio`,
        IF((`rid`.`valor` IS NOT NULL),
            'Y',
            'N') AS `rateado`,
        `ri`.`idrateio` AS `idrateio`,
        `ri`.`idrateioitem` AS `idrateioitem`,
        `rid`.`idrateioitemdest` AS `idrateioitemdest`,
        `rid`.`tipoobjeto` AS `tipoobjeto`,
        `rid`.`idobjeto` AS `idobjeto`,
        `u`.`idunidade` AS `idunidade`,
        `u`.`unidade` AS `unidade`,
        IFNULL(`e`.`idempresa`, `a`.`idempresa`) AS `idempresarateio`,
        IFNULL(`e`.`sigla`,  `a`.`sigla`) AS `siglarateio`,
        IFNULL(`e`.`empresa`,`a`.`empresa`) AS `empresarateio`,
        IFNULL(`e`.`corsistema`,
                `a`.`corsistema`) AS `corsistema`,
                a.idunidade as idunidadenf,
        `tu`.`idcentrocusto` AS `idtipounidade`,
        `tu`.`centrocusto` AS `tipounidade`
        FROM
        ((((((SELECT 
            `n`.`tiponf` AS `tiponf`,
                `c`.`contaitem` AS `contaitem`,
                `c`.`idcontaitem` AS `idcontaitem`,
                `c`.`cor` AS `cor`,
                `c`.`somarelatorio` AS `somarelatorio`,
                `c`.`previsao` AS `previsao`,
                `cp`.`status` AS `status`,
                `cp`.`tipo` AS `tipo`,
                `c`.`faturamento` AS `faturamento`,
                `c`.`ordem` AS `ordem`,
                IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
                `n`.`idnf` AS `idnf`,
                `cp`.`datareceb` AS `datareceb`,
                `cp`.`idempresa` AS `idempresa`,
                `e`.`empresa` AS `empresa`,
                `e`.`corsistema` AS `corsistema`,
                `e`.`sigla` as `sigla`,
                `cp`.`idagencia` AS `idagencia`,
                `cp`.`idcontapagar` AS `idcontapagar`,
                `cp`.`parcela` AS `parcela`,
                `cp`.`parcelas` AS `parcelas`,
                `p`.`idtipoprodserv` AS `idtipoprodserv`,
                `i`.`idnfitem` AS `idnfitem`,
                `i`.`qtd` AS `qtd`,
                IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
                (((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - `n`.`frete`)) * `n`.`frete`)) / `n`.`total`) * `cp`.`valor`) * -(1)) AS `total`,
                `p`.`tipoprodserv` AS `tipoprodserv`,
                `n`.`nnfe` AS `nnfe`,
                `i`.`vlritem` AS `vlritem`,
                n.idunidade
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
                AND `cp`.`status` <> 'ABERTO'
            
                AND `cp`.`datareceb` BETWEEN ?data1? and ?data2?
        UNION ALL 
    
        SELECT 
            `n`.`tiponf` AS `tiponf`,
                `c`.`contaitem` AS `contaitem`,
                `c`.`idcontaitem` AS `idcontaitem`,
                `c`.`cor` AS `cor`,
                `c`.`somarelatorio` AS `somarelatorio`,
                `c`.`previsao` AS `previsao`,
                `cp`.`status` AS `status`,
                `cp`.`tipo` AS `tipo`,
                `c`.`faturamento` AS `faturamento`,
                `c`.`ordem` AS `ordem`,
                IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
                `n`.`idnf` AS `idnf`,
                `cp`.`datareceb` AS `datareceb`,
                `cp`.`idempresa` AS `idempresa`,
                `e`.`empresa` AS `empresa`,
                `e`.`corsistema` AS `corsistema`,
                `e`.`sigla` as `sigla`,
                `cp`.`idagencia` AS `idagencia`,
                `cp`.`idcontapagar` AS `idcontapagar`,
                `cp`.`parcela` AS `parcela`,
                `cp`.`parcelas` AS `parcelas`,
                `p`.`idtipoprodserv` AS `idtipoprodserv`,
                `i`.`idnfitem` AS `idnfitem`,
                `i`.`qtd` AS `qtd`,
                IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
                (((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - `n`.`frete`)) * `n`.`frete`)) / `n`.`total`) * sum(`ci`.`valor`)) * -(1)) AS `total`,
                `p`.`tipoprodserv` AS `tipoprodserv`,
                `n`.`nnfe` AS `nnfe`,
                `i`.`vlritem` AS `vlritem`,
                n.idunidade
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
                AND `cp`.`status` <> 'ABERTO'
    
                AND `cp`.`datareceb` BETWEEN ?data1? and ?data2?
                and `i`.`qtd` > 0
        group by cp.idcontapagar,i.idnfitem
        UNION ALL SELECT 
            `n`.`tiponf` AS `tiponf`,
                `c`.`contaitem` AS `contaitem`,
                `c`.`idcontaitem` AS `idcontaitem`,
                `c`.`cor` AS `cor`,
                `c`.`somarelatorio` AS `somarelatorio`,
                `c`.`previsao` AS `previsao`,
                `cp`.`status` AS `status`,
                `cp`.`tipo` AS `tipo`,
                `c`.`faturamento` AS `faturamento`,
                `c`.`ordem` AS `ordem`,
                IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
                `n`.`idnf` AS `idnf`,
                `cp`.`datareceb` AS `datareceb`,
                `cp`.`idempresa` AS `idempresa`,
                `e`.`empresa` AS `empresa`,
                `e`.`corsistema` AS `corsistema`,
                `e`.`sigla` as `sigla`,
                `cp`.`idagencia` AS `idagencia`,
                `cp`.`idcontapagar` AS `idcontapagar`,
                `cp`.`parcela` AS `parcela`,
                `cp`.`parcelas` AS `parcelas`,
                `p`.`idtipoprodserv` AS `idtipoprodserv`,
                `i`.`idnfitem` AS `idnfitem`,
                `i`.`qtd` AS `qtd`,
                IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
                ((((IFNULL(`i`.`total`, 0) * (`n`.`total` /  ifnull(n.subtotal,n.total))) / `n`.`total`) * `cp`.`valor`) * -(1)) AS `total`,
                `p`.`tipoprodserv` AS `tipoprodserv`,
                `n`.`nnfe` AS `nnfe`,
                `i`.`vlritem` AS `vlritem`,
                n.idunidade
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
                AND `cp`.`status` <> 'ABERTO'
    
                AND `cp`.`datareceb` BETWEEN ?data1? and ?data2?
                and `i`.`qtd` > 0
        UNION ALL 
        SELECT 
            `n`.`tiponf` AS `tiponf`,
                `c`.`contaitem` AS `contaitem`,
                `c`.`idcontaitem` AS `idcontaitem`,
                `c`.`cor` AS `cor`,
                `c`.`somarelatorio` AS `somarelatorio`,
                `c`.`previsao` AS `previsao`,
                `cp`.`status` AS `status`,
                `cp`.`tipo` AS `tipo`,
                `c`.`faturamento` AS `faturamento`,
                `c`.`ordem` AS `ordem`,
                IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
                `n`.`idnf` AS `idnf`,
                `cp`.`datareceb` AS `datareceb`,
                `cp`.`idempresa` AS `idempresa`,
                `e`.`empresa` AS `empresa`,
                `e`.`corsistema` AS `corsistema`,
                `e`.`sigla` as `sigla`, 
                `cp`.`idagencia` AS `idagencia`,
                `cp`.`idcontapagar` AS `idcontapagar`,
                `cp`.`parcela` AS `parcela`,
                `cp`.`parcelas` AS `parcelas`,
                `p`.`idtipoprodserv` AS `idtipoprodserv`,
                `i`.`idnfitem` AS `idnfitem`,
                `i`.`qtd` AS `qtd`,
                IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
                ((((IFNULL(`i`.`total`, 0) * (`n`.`total` /  ifnull(n.subtotal,n.total))) / `n`.`total`) * sum(`ci`.`valor`)) * -(1)) AS `total`,
                `p`.`tipoprodserv` AS `tipoprodserv`,
                `n`.`nnfe` AS `nnfe`,
                `i`.`vlritem` AS `vlritem`,
                n.idunidade
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
                AND `cp`.`status` <> 'ABERTO'
                AND `cp`.`datareceb` BETWEEN ?data1? and ?data2?
                and `i`.`qtd` > 0
            group by cp.idcontapagar,i.idnfitem
        ) `a`
        JOIN `rateioitem` `ri` ON (((`ri`.`idobjeto` = `a`.`idnfitem`)
            AND (`ri`.`tipoobjeto` = 'nfitem'))))
        JOIN `rateioitemdest` `rid` ON ((`rid`.`idrateioitem` = `ri`.`idrateioitem`)))
        JOIN `unidade` `u` ON (((`u`.`idunidade` = `rid`.`idobjeto`)
            AND (`rid`.`tipoobjeto` = 'unidade'))))
        JOIN `centrocusto` `tu` on `tu`.`idcentrocusto` = `u`.`idcentrocusto`
        JOIN `empresa` `e` ON ((`e`.`idempresa` = `u`.`idempresa`))))
        WHERE
        (`a`.`somarelatorio` = 'Y')  group by idrateioitemdest, idcontapagar";
    }

    public static function buscarRelatorioDespesasPorViewClausulaEClausulaUnidade()
    {
        return "SELECT  
                    tipo,idempresa,qtd,un,contaitem,idcontaitem,idtipo,idrateio,idrateioitem,idrateioitemdest,idnf,nnfe,idobjeto,tipoobjeto,
                    idtipoprodserv,tipoprodserv,descr,vlrlote,rateio,valor,empresa,dtemissao,corsistema,rateado, idempresarateio as idempresarateio,siglarateio, idagencia, datareceb,
                    idunidade as idobjetovinc,unidade as tipoobjetovinc,siglarateio as  sigla,
                    concat('<a href=\"?_modulo=',
                    CASE
                        WHEN idunidadenf in (254,301,355,363,370,413)  THEN 'comprasrh'
                        WHEN idunidadenf in (321,335,344,345,349)  THEN 'comprassocios'
                        WHEN idunidadenf in (290,312,350,351,378)  THEN 'nfcte'
                        WHEN idunidadenf in (313,333,334,364,367)  THEN 'nfentrada'
                        WHEN idunidadenf in (312,323,340,342,346)  THEN 'nfrdv'
                    ELSE 'nfentrada'  
                    END,'&_acao=u&_idempresa=',idempresa,'&idnf=',idnf,'\" target=\"_blank\">',if(nnfe='',idnf,nnfe),'</a>') as linknf,
                idunidade,unidade,empresarateio,idtipounidade,tipounidade
                FROM (
                    SELECT 
                        'nfitem' AS tipo,
                        v.idempresa,
                        round(qtd,2) as qtd,
                        un,
                        contaitem,
                        idcontaitem,
                        idnfitem AS idtipo,
                        idrateio,
                        idrateioitem,
                        idrateioitemdest,
                        idnf,
                        nnfe,
                        ifnull(v.idobjeto,v.idempresa) as idobjeto,
                        ifnull(v.tipoobjeto,'aratiar') as tipoobjeto,
                        idtipoprodserv,	
                        tipoprodserv,
                        descricao AS descr,
                        vlritem AS vlrlote,
                        rateio AS rateio,
                        concat('<a target=\"_blank\" href=\"?_modulo=rateioitemdest&_acao=u&tipo=rateio&stidrateioitemdest=',idrateioitemdest,'\">',round(vlrrateio,2),'%</a>') AS valor,
                        v.empresarateio AS empresa,
                        datareceb AS dtemissao,
                        v.corsistema,
                        rateado,
                        idempresarateio,
                        v.empresarateio as empresarateio,
                        siglarateio as siglarateio2,
                        
                        datareceb,
                        v.idagencia,
                        v.siglarateio,
                        v.idunidadenf,
                        v.idunidade,
                        v.unidade,
                        v.idtipounidade,
                        v.tipounidade
                        
                    FROM (?view?) v
                        where 1
                        ?clausula?
                        ?clausulaunidade?
                    ) as u
                order by idempresarateio, tipounidade, tipoobjetovinc, contaitem, tipoprodserv,empresa, descr,dtemissao";
    }

    public static function buscarDespesasPorViewClaususlaEIdRateioItemDest()
    {
        return "SELECT
                    rateio,
                    tipo,idempresa,qtd,un,contaitem,idcontaitem,idtipo,idrateio,idrateioitem,idrateioitemdest,idnf,nnfe,idobjeto,tipoobjeto,
                    idtipoprodserv,tipoprodserv,descr,vlrlote,valor,empresa,dtemissao,corsistema,rateado, idempresarateio as idempresarateio,siglarateio, idagencia, datareceb,
                    idobjetovinc,tipoobjetovinc, sigla,
                idunidade,unidade
                from (
                    SELECT 
                        'nfitem' AS tipo,
                        e.idempresa,
                        round(qtd,2) as qtd,
                        un,
                        contaitem,
                        idcontaitem,
                        idnfitem AS idtipo,
                        idrateio,
                        idrateioitem,
                        idrateioitemdest,
                        idnf,
                        nnfe,
                        ifnull(v.idobjeto,e.idempresa) as idobjeto,
                        ifnull(v.tipoobjeto,'aratiar') as tipoobjeto,
                        idtipoprodserv,	
                        tipoprodserv,
                        descricao AS descr,
                        vlritem AS vlrlote,
                        rateio AS rateio,
                        concat('<a target=\"_blank\" href=\"?_modulo=rateioitemdest&_acao=u&tipo=rateio&stidrateioitemdest=',idrateioitemdest,'\">',round(vlrrateio,2),'%</a>') AS valor,
                        empresarateio AS empresa,
                        datareceb AS dtemissao,
                        e.corsistema,
                        rateado,
                        idempresarateio,
                        siglarateio,
                        vwo.idobjeto as idobjetovinc,
                        vwo.tipoobjeto as tipoobjetovinc,
                        datareceb,
                        v.idagencia,
                        e.sigla,
                        v.idunidadenf,
                        v.idunidade,
                        unidade
                        
                FROM
                    (?view?) v
                JOIN
                    vw8organogramaunidade vwo on FIND_IN_SET(v.idunidade, vwo.idunidade)
                JOIN empresa e on e.idempresa = v.idempresa

                WHERE
                    rateado = 'Y'
                    ?clausula?
                    and not idrateioitemdest in (?idrateioitemdest?)
                group by idrateioitemdest, idcontapagar
                ) as u";
    }
}
?>