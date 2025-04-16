<?
class _RepQuery {
    public static function buscarRelatorioPorIdRep(){
        return "SELECT 
                rt.idreptipo,
                rt.reptipo,
                r.idrep,
                r.rep,
                r.url,
                r.tipograph,
                r.titlebutton
            FROM
                "._DBCARBON."._rep r
                    JOIN
                "._DBCARBON."._reptipo rt ON (rt.idreptipo = r.idreptipo)
            WHERE
                r.idreptipo <> ''
                    AND r.idrep = ?idrep?";
    }

    public static function buscarInfoRelatorioPorIdRep() {
        return "SELECT 
                    rt.idreptipo,
                    rt.reptipo,
                    r.idrep,
                    r.rep,
                    r.url,
                    r.tipograph,
                    r.titlebutton,
                    m.modulopar
                FROM "._DBCARBON."._rep r
                JOIN "._DBCARBON."._reptipo rt ON (rt.idreptipo = r.idreptipo)
                join "._DBCARBON."._modulorep mr on mr.idrep = r.idrep
                join "._DBCARBON."._modulo m on m.modulo = mr.modulo
                join objempresa oe on oe.idobjeto = m.idmodulo and oe.objeto = 'modulo'
                WHERE r.idreptipo <> ''
                AND r.idrep = ?idrep?
                AND m.modulo in ('?modulos?')
                -- AND oe.idempresa = ?idempresa?
                group by r.idrep;";
    }

    public static function buscarRelatoriosVinculadosEmLpPorModulo()
    {
        return "SELECT distinct
                    r.idrep,
                    r.rep,
                    r.idreptipo,
                    r.cssicone,
                    r.url,
                    r.showfilters,
                    r.header,
                    r.footer,
                    r.tab,
                    tc.code,
                    r.newgrouppagebreak,
                    r.pbauto,
                    r.showtotalcounter,
                    rc.col,
                    rc.psqkey,
                    rc.psqreq,
                    if(tc.rotcurto>'',tc.rotcurto,rc.col) as rotulo,
                    rc.idrepcol,
                    rc.idrep,
                    rc.visres,
                    rc.align,
                    rc.grp,
                    rc.ordseq,
                    rc.ordtype,
                    rc.tsum,
                    rc.acsum,
                    rc.acavg,
                    rc.tavg,
                    rc.mascara,
                    rc.hyperlink,
                    rc.entre,
                    rc.inseridomanualmente,
                    rc.calendario,
                    rc.entre,
                    rc.like,
                    rc.inval,
                    rc.in,
                    rc.findinset,
                    rc.json,
                    mr.ord,
                    tc.datatype,
                    r.compl,
                    rc.ordcol,
                    rc.eixograph,
                    r.tipograph,
                    r.descr,
                    m.tab as tabfull,
                    lr.flgunidade,
                    CASE WHEN m.tipo='MODVINC' THEN mv.chavefts ELSE m.chavefts END as chavefts,
                    rodape
                FROM "._DBCARBON."._rep r
                LEFT JOIN "._DBCARBON."._lprep lr on (lr.idrep = r.idrep and lr.flgunidade='Y')
                JOIN "._DBCARBON."._repcol rc on rc.idrep=r.idrep ?clausularep?
                JOIN "._DBCARBON."._modulorep mr ON mr.modulo='?modulo?' and mr.idrep=r.idrep
                LEFT JOIN "._DBCARBON."._mtotabcol tc on tc.tab = r.tab AND rc.col = tc.col 
                LEFT JOIN "._DBCARBON."._modulo m ON m.modulo= mr.modulo
                LEFT JOIN "._DBCARBON."._modulo mv ON (mv.modulo=m.modvinculado)
                order by ?ordem?";
    }

    public static function buscarRelatorioPorIdRepEColunaPrimaria()
    {
        return "SELECT
                    IF (
                        tc.rotcurto > '',
                        tc.rotcurto,
                        rc.col
                    ) AS rotulo,
                    rc.col,
                    rc.psqreq,
                    rc.calendario,
                    rc.entre,
                    rc.like,
                    rc.inval,
                    rc.in,
                    rc.findinset,
                    rc.psqkey,
                    tc.code,
                    rc.json,
                    tc.datatype,
                    rc.visres,
                    rc.align,
                    rc.hyperlink,
                    rc.grp,
                    rc.tsum,
                    rc.acsum,
                    rc.acavg,
                    rc.tavg,
                    rc.mascara,
                    rc.inseridomanualmente,
                    rc.ordcol,
                    rc.eixograph,
                    tc.prompt,
                    mrelac.tabde,
                    mrelac.colde,
                    mrelac.tabpara,
                    mrelac.colpara
                FROM "._DBCARBON."._rep r
                JOIN "._DBCARBON."._repcol rc ON (rc.idrep = r.idrep)
                LEFT JOIN "._DBCARBON."._mtotabcol tc ON tc.tab = r.tab AND rc.col = tc.col
                LEFT JOIN "._DBCARBON."._modulorelac mrelac ON(mrelac.colde = tc.col AND mrelac.modulo = '_rep')
                WHERE r.idrep = ?idrep? AND rc.psqkey = 'Y'
                ORDER BY tc.ordpos";
    }

    public static function alterarSQLMode()
    {
        return "SET sql_mode = '?mode?'";
    }

    public static function buscarConfiguracaoRelatorioPorIdRep()
    {
        return "SELECT distinct
                    r.idrep,
                    r.rep,
                    r.valorposfixado,
                    r.idreptipo,
                    r.cssicone,
                    r.url,
                    r.showfilters,
                    r.header,
                    r.footer,
                    r.tab,
                    tc.code,
                    r.newgrouppagebreak,
                    r.pbauto,
                    r.showtotalcounter,
                    rc.col,
                    rc.psqkey,
                    rc.psqreq,
                    if(tc.rotcurto>'',tc.rotcurto,rc.col) as rotulo,
                    rc.idrepcol,
                    rc.visres,
                    rc.align,
                    rc.grp,
                    rc.ordseq,
                    rc.ordtype,
                    rc.tsum,
                    rc.acsum,
                    rc.acavg,
                    rc.tavg,
                    rc.mascara,
                    rc.hyperlink,
                    rc.entre,
                    rc.inseridomanualmente,
                    rc.calendario,
                    rc.like,
                    rc.inval,
                    rc.in,
                    rc.findinset,
                    rc.json,
                    tc.datatype,
                    r.compl,
                    rc.ordcol,
                    rc.eixograph,
                    r.tipograph,
                    r.descr,
                    r.rodape
                FROM
                "._DBCARBON."._rep r
                JOIN "._DBCARBON."._repcol rc ON (rc.idrep = r.idrep AND r.idrep = ?idrep?)
                LEFT JOIN "._DBCARBON."._mtotabcol tc ON (tc.tab = r.tab AND rc.col = tc.col)
                ORDER BY rc.ordcol";
    }

    public static function buscarRelatorioDinamico()
    {
        return "?colunas? from ?query?";
    }

    public static function buscarVisualizacaograf()
    {
        return "?query?";
    }
    public static function buscarRelatorioFluxoDeCaixaPorClausula()
    {
        return "SELECT 
                    sigla,
                    agencia,
                    datareceb,
                    credito,
                    debito,
                    credito - debito as dif,
                    cast(@saldoanterior:=IF(@saldoanterior = 0,
                            (saldo),
                            if(saldo = 0,
                                @saldoanterior + (credito - debito),
                                saldo))
                        AS DECIMAL (10 , 2 )) AS saldoprev,
                    saldo,
                    idempresa,
                    idagencia
                FROM
                    (SELECT 
                            e.idempresa,
                            x.idagencia,
                            e.sigla,
                            a.agencia,
                            tipo,
                            SUM(credito) AS credito,
                            SUM(debito) AS debito,
                            GROUP_CONCAT(aaa) AS aaa,
                            SUM(saldo) AS saldo,
                            datareceb,
                            SUM(debito) - SUM(credito) AS conta
                    FROM
                        (SELECT 
                        cp.idempresa,
                            cp.idagencia,
                            'D' AS tipo,
                            0 AS credito,
                            SUM(cp.valor) AS debito,
                            0 AS aaa,
                            0 AS saldo,
                            cp.datareceb
                    FROM
                        vwrelcontapagarmin cp
                
                    GROUP BY cp.datareceb , `cp`.`idagencia` UNION ALL 
                    SELECT 
                        cp.idempresa,
                            cp.idagencia,
                            'D' AS tipo,
                            SUM(cp.valor) AS credito,
                            0 AS debito,
                            0 AS aaa,
                            0 AS saldo,
                            cp.datareceb
                    FROM
                        vwrelcontarecebermin cp
                
                    GROUP BY cp.datareceb , `cp`.`idagencia` UNION ALL SELECT 
                        c.idempresa,
                            c.idagencia,
                            'S' AS tipo,
                            0,
                            0,
                            '',
                            saldo,
                            c.datareceb
                    FROM
                        (SELECT 
                        datareceb,
                            idempresa,
                            MAX(quitadoemseg) AS quitadoemseg,
                            idagencia
                    FROM
                        contapagar cp
                    WHERE
                        saldo IS NOT NULL AND status = 'QUITADO'
                    
                    GROUP BY idagencia , idempresa , datareceb) temp
                    LEFT JOIN contapagar c ON c.idagencia = temp.idagencia
                        AND c.idempresa = temp.idempresa
                        AND c.datareceb = temp.datareceb
                        AND c.quitadoemseg = temp.quitadoemseg
                    ORDER BY datareceb) x
                    join agencia a on a.idagencia = x.idagencia
                    join empresa e on e.idempresa = x.idempresa
                    GROUP BY e.idempresa , idagencia , datareceb
                    ORDER BY e.idempresa , idagencia , datareceb , tipo) hh
                    
                    
                        CROSS JOIN
                    (SELECT @saldoanterior:=0) v
                    WHERE 1
                    ?clausula?";
    }

    public static function buscarColunasDoRelatorioPorIdRep()
    {
        return "SELECT rc.col
                FROM carbonnovo._rep r 
                JOIN carbonnovo._repcol rc on (rc.idrep = r.idrep)
                WHERE r.idrep = ?idrep?";
    }
}
?>