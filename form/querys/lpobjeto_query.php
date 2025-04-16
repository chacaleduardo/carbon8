<?

class LpObjetoQuery
{
    public static function buscarLpsPorIdObjetoTipoObjetoEGetIdEmpresa()
    {
        return "SELECT lpo.idlpobjeto, lp.idlp, CONCAT(e.sigla,' - ',lp.descricao) as descricao, lpg.lpgrupopar as idlpgrupopai, lpg.idlpgrupo as idlpgrupofilho, lpg.descricao as descricaogrupofilho, lpgpai.descricao as descricaogrupopai
                FROM lpobjeto lpo
                JOIN "._DBCARBON."._lp lp ON(lp.idlp = lpo.idlp)
                LEFT JOIN empresa e on (e.idempresa = lp.idempresa)
                JOIN "._DBCARBON."._lpobjeto clpo ON(clpo.idlp = lp.idlp AND clpo.tipoobjeto = 'lpgrupo')
                JOIN "._DBCARBON."._lpgrupo lpg ON(lpg.idlpgrupo = clpo.idobjeto)
                JOIN "._DBCARBON."._lpgrupo lpgpai ON(lpgpai.idlpgrupo = lpg.lpgrupopar)
                WHERE lpo.idobjeto = ?idobjeto?
                AND lpo.tipoobjeto = '?tipoobjeto?'
                AND lp.status = 'ATIVO'
                AND lpg.status = 'ATIVO'
                ?getidempresa?
                GROUP BY lp.idlp
                ORDER BY e.sigla, lp.descricao ASC";
    }

    public static function deletarVinculoPorIdObjetoETipoObjeto()
    {
        return "DELETE FROM lpobjeto where idobjeto = ?idobjeto? and tipoobjeto = '?tipoobjeto?'";
    }

    public static function buscarLpsVinculadasPorIdDashCard()
    {
        return "SELECT 
                    l.idlp,
                    lo.idlpobjeto,
                    CONCAT(e.sigla,' - ',l.descricao) as descricao
                FROM "._DBCARBON."._lpobjeto lo
                JOIN "._DBCARBON."._lp l on lo.idlp = l.idlp 
                JOIN empresa e ON (l.idempresa = e.idempresa)
                WHERE lo.idobjeto = '?iddashcard?' 
                AND lo.tipoobjeto = 'dashboard' 
                ORDER BY l.descricao ASC";
    }
}

?>