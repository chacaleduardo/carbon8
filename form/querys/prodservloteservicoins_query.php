<?
class ProdservLoteServicoInsQuery
{
	public static function buscarProdservLotesServicoIns(){
        return "SELECT * 
                  FROM prodservloteservicoins i LEFT JOIN prodserv p ON (i.idprodserv = p.idprodserv)
                 WHERE i.idprodservloteservico = ?idprodservloteservico?
                   AND i.status = 'ATIVO'
              ORDER BY ord ASC";
    }
}

?>