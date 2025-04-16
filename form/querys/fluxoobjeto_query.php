<? 

class FluxoObjetoQuery
{
    public static function buscarPorIdobjetoETipoObjeto()
    {
        return "SELECT
                    vwf.idfluxo,
                    vwf.tipoobjeto,
                    fo.idfluxoobjeto
                FROM fluxoobjeto fo
                JOIN fluxo f ON(f.idfluxo = fo.idfluxo)
                JOIN vwfluxo vwf ON(vwf.idfluxo = f.idfluxo)
                WHERE fo.tipoobjeto = '?tipoobjeto?'
                AND fo.idobjeto = ?idobjeto?
            ORDER BY vwf.tipoobjeto";
    }

    public static function buscarPessoasFluxoParaAssinatura () {
        return "SELECT fo.idobjeto AS idpessoa, assina
            FROM fluxoobjeto fo
                JOIN fluxo f ON fo.idfluxo = f.idfluxo 
                    AND f.modulo = '?modulo?' 
                    AND f.status = 'ATIVO' 
                    AND fo.tipoobjeto = 'pessoa'
            WHERE FIND_IN_SET('?idstatusf?', fo.inidstatus) 
                AND fo.assina IN ('PARCIAL', 'TODOS', 'INDIVIDUAL')
            UNION
            SELECT idpessoa, assina
            FROM fluxoobjeto fo
                JOIN fluxo f ON fo.idfluxo = f.idfluxo 
                    AND f.modulo = '?modulo?'
                    AND f.status = 'ATIVO' 
                    AND fo.tipoobjeto = 'imgrupo'
                JOIN imgrupopessoa ip ON fo.idobjeto = ip.idimgrupo
            WHERE FIND_IN_SET('?idstatusf?', fo.inidstatus) 
                AND fo.assina IN ('PARCIAL', 'TODOS', 'INDIVIDUAL')";  
    }

    public static function buscarFluxoEvento()
    {
        return "SELECT vwf.idfluxo,
                       vwf.tipoobjeto
                  FROM fluxoobjeto fo JOIN fluxo f ON f.idfluxo = fo.idfluxo
                  JOIN vwfluxo vwf ON vwf.idfluxo = f.idfluxo 
                 WHERE f.idfluxo NOT IN (SELECT f2.idfluxo FROM fluxoobjeto fo2 JOIN fluxo f2 ON f2.idfluxo = fo2.idfluxo 
                                          WHERE fo2.tipoobjeto = '?tipoobjeto?' AND fo2.idobjeto = ?idobjeto? AND f2.tipoobjeto = f.tipoobjeto AND f2.idobjeto = f.idobjeto)
                   AND f.modulo = 'evento'
                   AND f.status = 'ATIVO'
              GROUP BY f.idfluxo;";
    }
}

?>