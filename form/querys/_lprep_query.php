<?
class _LpRepQuery{
    public static function buscarRepsMenuRelatorio(){
        return " SELECT 
                *
            FROM
                (SELECT 
                    rt.idreptipo,
                        TRIM(rt.reptipo) AS reptipo,
                        r.idrep,
                        r.rep,
                        r.url,
                        r.tipograph,
                        r.titlebutton,
                        lr.flgunidade,
                        m2.modulo AS modulopesq,
                        m2.idmodulo AS idmodulopesq,
                        m2.rotulomenu AS rotulomenupesq,
                        m2.ord
                FROM
                    "._DBCARBON."._modulorep mr
                JOIN "._DBCARBON."._modulo m ON mr.modulo = m.modulo
                JOIN "._DBCARBON."._modulo m2 ON (find_in_set(m2.modulo,m.modulopar))
                JOIN "._DBCARBON."._lprep lr ON lr.idrep = mr.idrep
                JOIN "._DBCARBON."._rep r ON (r.idrep = lr.idrep
                    AND r.idrep = mr.idrep)
                JOIN "._DBCARBON."._reptipo rt ON (rt.idreptipo = r.idreptipo)
                WHERE
                    r.mostrarmenurelatorio IN (?mostrarmenurelatorio?)
                        -- AND m.modulopar IN (?modulosPai?)
                        AND m.modulo IN (?modulosFilhos?)
                        AND lr.idlp IN (?lps?)
                        AND r.status = 'ATIVO'
                        AND EXISTS( SELECT 
                            1
                        FROM
                            objempresa oe
                        WHERE
                            oe.objeto = 'modulo'
                                AND oe.idobjeto = m.idmodulo
                                AND oe.empresa = ?idempresa?)
                GROUP BY m2.idmodulo, r.idrep UNION ALL SELECT 
                    rt.idreptipo,
                        TRIM(rt.reptipo) AS reptipo,
                        r.idrep,
                        r.rep,
                        r.url,
                        r.tipograph,
                        r.titlebutton,
                        lr.flgunidade,
                        m.modulo AS modulopesq,
                        m.idmodulo AS idmodulopesq,
                        m.rotulomenu AS rotulomenupesq,
                        m.ord * 1000
                FROM
                    "._DBCARBON."._modulo m
                JOIN "._DBCARBON."._modulorep mr ON (m.modulo = mr.modulo)
                JOIN "._DBCARBON."._lprep lr ON lr.idrep = mr.idrep
                JOIN "._DBCARBON."._rep r ON (r.idrep = lr.idrep
                    AND r.idrep = mr.idrep)
                JOIN "._DBCARBON."._reptipo rt ON (rt.idreptipo = r.idreptipo)
                WHERE
                    r.mostrarmenurelatorio IN (?mostrarmenurelatorio?)
                        AND (m.modulo IN (?modulosFilhosVinculados?) OR m.tipo = 'BTPR')
                        AND lr.idlp IN (?lps?)
                        AND r.status = 'ATIVO'
                        AND EXISTS( SELECT 
                            1
                        FROM
                            objempresa oe
                        WHERE
                            oe.objeto = 'modulo'
                                AND oe.idobjeto = m.idmodulo
                                AND oe.empresa = ?idempresa?)
                GROUP BY m.idmodulo, r.idrep) AS u
                ?clausula?
            ORDER BY u.ord, u.reptipo, u.rep";
    }

    public static function buscarLpRepPorIdRepEIdLps()
    {
        return "SELECT * from "._DBCARBON."._lprep where idrep = ?idrep? and idlp in(?idlp?) order by flgunidade desc";
    }

    public static function verificarLpPorIdLpEIdRep()
    {
        return "SELECT 1 from "._DBCARBON."._lprep where idlp in (?idlp?) and idrep = ?idrep?";
    }
}
?>