<?
class SpedD100Query
{
	public static function buscarSpedD100()
	{
		return "SELECT *
				  FROM spedd100
				 WHERE idnf = ?idnf?
				   AND status IN (?status?)";
	}
}
?>