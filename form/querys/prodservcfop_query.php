<?
class ProdservCfopQuery
{
	public static function listarProdservCfop()
    {
		return "SELECT p.idprodservcfop, 
					   p.idcfop, 
					   p.origem
				  FROM prodservcfop p
				 WHERE p.idprodserv = ?idprodserv?
			  ORDER BY p.idprodservcfop";
	}	
}
?>