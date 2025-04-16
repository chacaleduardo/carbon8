<?
class NfConferenciaItemQuery
{
	public static function buscarNfConferenciaItem()
	{
		return "SELECT *
				  FROM nfconferenciaitem q
				 WHERE q.idnf = ?idnf?";
	}

	public static function inserirNfConferenciaItem()
	{
		return "INSERT INTO nfconferenciaitem (idempresa, idnf, qst)
                        (SELECT ?idempresa?, ?idnf?, c.qst FROM conferenciaitem c WHERE c.status = 'ATIVO' AND c.?tiponf? = 'Y');";
	}
}
?>