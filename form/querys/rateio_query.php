<?

class RateioQuery
{
    public static function inserir()
    {
        return "INSERT INTO rateio (idempresa, idobjeto, tipoobjeto, criadopor, criadoem, alteradopor, alteradoem) 
                            VALUES (?idempresa?, ?idobjeto?, '?tipoobjeto?', '?criadopor?', ?criadoem?, '?alteradopor?', ?alteradoem?)";
    }

    public static function buscarNfitemRateio()
    {
        return "SELECT i.idnf,
                       i.idnfitem,
                       IFNULL(p.descr, i.prodservdescr) AS descr,
                       i.total AS rateio,
                       r.idrateio,
                       ri.idrateioitem,
                       IFNULL(rd.valor, 100) AS valorateio
                  FROM nfitem i LEFT JOIN rateio r ON (r.idobjeto = i.idnf AND r.tipoobjeto = 'nf')
             LEFT JOIN prodserv p ON (p.idprodserv = i.idprodserv)
             LEFT JOIN rateioitem ri ON (r.idrateio = ri.idrateio AND ri.idobjeto = i.idnfitem AND ri.tipoobjeto = 'nfitem')
             LEFT JOIN rateioitemdest rd ON (rd.idrateioitem = ri.idrateioitem)
                 WHERE i.idnfitem = ?idnfitem?";
    }
}

?>