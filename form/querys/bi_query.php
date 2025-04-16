<?
class BiQuery
{
    public static function buscarEmpresasVinculas()
    {
        return "SELECT idempresa,
                       IF((empresa IS NULL OR empresa = ''), IF((nomefantasia IS NULL OR nomefantasia = ''), razaosocial, nomefantasia), empresa) AS empresa
                  FROM empresa e
                 WHERE status = 'ATIVO'  
                   AND NOT EXISTS (SELECT 1 FROM _linkportalbi o WHERE o.empresa = e.idempresa AND o.idbi = ?idbi?)
                ORDER BY empresa";
    }

    public static function buscarEmpresaBi()
    {
        return "SELECT * 
                  FROM _linkportalbi l JOIN empresa e ON e.idempresa = l.empresa
                 WHERE l.idbi = ?idbi? ORDER BY e.empresa";
    }
}

?>
