<?
class NfPendenciaQuery
{
	public static function buscarNfPendencia()
	{
		return "SELECT *
				  FROM nfpendencia q
				 WHERE q.idnf = ?idnf?
			  ORDER BY idnfpendencia";
	}
}
?>