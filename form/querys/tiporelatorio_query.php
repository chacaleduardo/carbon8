<?
class TipoRelatorioQuery
{
	public static function listarTipoRelatorioPorIdProdserv()
	{
		return "SELECT t.idtiporelatorio,
					   CONCAT(e.sigla, ' - ', t.tiporelatorio) AS tiporelatorio,
					   pt.idprodservtiporelatorio
				  FROM tiporelatorio t JOIN empresa e ON e.idempresa = t.idempresa
			 LEFT JOIN prodservtiporelatorio pt ON (pt.idtiporelatorio = t.idtiporelatorio AND pt.idprodserv = ?idprodserv?)
			  ORDER BY pt.idprodservtiporelatorio desc,tiporelatorio asc";
	}
}
?>