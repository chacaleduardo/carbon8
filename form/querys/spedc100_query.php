<?
class Spedc100Query
{
	public static function buscarSpedC100()
	{
		return "SELECT *
				  FROM spedc100
				 WHERE idnf = ?idnf?
				   AND status IN (?status?)";
	}
}
?>