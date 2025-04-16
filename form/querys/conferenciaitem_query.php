<?
class ConferenciaItemQuery
{
	public static function buscarConferenciaItem()
	{
		return "SELECT qst
		 		  FROM conferenciaitem c
				 WHERE c.status = 'ATIVO'
				   ?getidempresa?
				   AND c.?tiponf? = 'Y'";
	}
}
?>