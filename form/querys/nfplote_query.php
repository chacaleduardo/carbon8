<?
class NfPLoteQuery
{
	public static function buscarNfpLote()
	{
		return "SELECT *
				  FROM nfplote
				 WHERE idnf = ?idnf?
			  ORDER BY recibo";
	}
}
?>
