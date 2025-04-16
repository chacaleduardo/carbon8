<?
class _ModuloRepQuery
{
    public static function buscarRelatoriosPorModuloELp()
    {
        return "SELECT mr.idrep,r.rep,mr.modulo,r.url,r.tipograph,r.titlebutton,ifnull(m1.idmodulo,m.idmodulo) as idmodpai
                FROM "._DBCARBON."._modulorep mr 
                JOIN "._DBCARBON."._rep r ON (mr.idrep = r.idrep)
                JOIN "._DBCARBON."._modulo m ON (mr.modulo = m.modulo)
                LEFT JOIN "._DBCARBON."._modulo m1 ON (find_in_set(m1.modulo, m.modulopar))
                WHERE mr.modulo IN (?modulos?) 
                AND EXISTS(select 1 from "._DBCARBON."._lprep ep where  ep.idlp in (?lps?) and ep.idrep=r.idrep)
                AND r.tab <> ''
                ?clausuladashboard?
                AND r.status = 'ATIVO'
                GROUP BY mr.idrep";
    }
}

?>