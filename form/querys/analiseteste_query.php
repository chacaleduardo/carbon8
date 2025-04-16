<?
class AnaliseTesteQuery
{
	public static function buscarAnaliseTestePorIdAnaliseQst()
	{
		return "SELECT idanaliseteste, idprodserv FROM analiseteste WHERE idanaliseqst = ?idanaliseqst?";
	}
}
?>