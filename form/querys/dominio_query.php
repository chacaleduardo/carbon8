<?

class DominioQuery
{
    public static function buscarDominioPorGetIdEmpresa()
    {
        return "SELECT * from dominio WHERE 1 ?getidempresa? AND status = 'ATIVO' ORDER BY dominio";
    }

    public static function buscarDominiosComExcesaoDeEmails() 
    {
        return "SELECT dominio 
                FROM dominio 
                WHERE 1 
                ?getidempresa?
                AND status='ATIVO'
                ?queryauxiliar?";
    }
}

?>