<?

class ColaboradorHistoricoQuery
{
    public static function buscarHistoricoPorIdObjetoTipoObjeto()
    {
        return "SELECT p.nomecurto, c.acao, t.?tipoobjeto?, c.alteradopor, c.alteradoem 
                FROM laudo.colaboradorhistorico as c
                JOIN pessoa as p on(p.idpessoa=c.idpessoa)
                JOIN sg?tipoobjeto? as t on(t.idsg?tipoobjeto? = c.objeto)
                WHERE c.tipoobjeto = 'sg?tipoobjeto?'
                AND t.idsg?tipoobjeto? = ?idobjeto?";
    }

    public static function buscarHistoricoPorIdSgsetor()
    {
        return "SELECT p.nomecurto,c.acao,sgs.setor,c.alteradopor,c.alteradoem 
                FROM colaboradorhistorico as c
                JOIN pessoa AS p ON (p.idpessoa=c.idpessoa)
                JOIN sgsetor AS sgs ON (sgs.idsgsetor=c.objeto)
                WHERE c.tipoobjeto='sgsetor'
                AND sgs.idsgsetor=?idsgsetor?";
    }
}

?>