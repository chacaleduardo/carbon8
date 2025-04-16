<?
class ProdservLoteServicoQuery
{
	public static function buscarProdservLotesServico(){
        return "SELECT * 
                  FROM prodservloteservico 
                 WHERE idprodserv = ?idprodserv? 
              ORDER BY CASE WHEN status = 'INATIVO' THEN 0
					        ELSE 1 END DESC, idprodservloteservico ASC";
    }

    public static function buscarValorServico(){
        return "SELECT 
                    n.dtemissao,n.idnf,n.tiponf,
                    (i.total / i.qtd) AS vlrun,
                    n.status,
                    i.total,
                    i.qtd
                FROM
                    nfitem i
                        JOIN
                    nf n ON (n.idnf = i.idnf
                        AND n.status != 'CANCELADO' and n.tiponf!='V')
                WHERE
                    i.idprodserv = ?idprodserv?
                ORDER BY dtemissao DESC limit 1";

    }

    public static function buscarValorServicoVenda(){
        return "SELECT 
                    ifnull(vlrvenda,0) as vlrvenda
                FROM
                    prodserv p
                WHERE
                    p.idprodserv = ?idprodserv? ";
    }
}

?>