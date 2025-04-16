<?
class TagClassQuery
{
    public static function buscarTagClassPorIdTagClass(){
        return "SELECT * FROM tagclass WHERE 1 ?sqlidempresa? AND idtagclass = ?idtagclass?";
    }

    public static function buscarTagClass()
    {
        return "SELECT tc.*, if(tc.status = 'INATIVO', concat(e.sigla, ' - ',tc.tagclass,' (Inativo)'), concat(e.sigla, ' - ',tc.tagclass)) as tagclass
                FROM tagclass tc
                JOIN empresa e ON (e.idempresa = tc.idempresa)
                WHERE tc.status = 'ATIVO'
                UNION 
                SELECT tc.*, if(tc.status = 'INATIVO', concat(e.sigla, ' - ',tc.tagclass,' (Inativo)'), concat(e.sigla, ' - ',tc.tagclass)) as tagclass
                FROM tagclass tc 
                JOIN empresa e ON (e.idempresa = tc.idempresa)
                WHERE tc.idtagclass = '?idtagclass?'";
    }

    public static function buscarTodasTagClassAtivasPorEmpresa(){
        return "SELECT idtagclass,tagclass 
            FROM tagclass
            WHERE status='ATIVO' ?sqlidempresa?
            ORDER BY tagclass";
    }
}
?>