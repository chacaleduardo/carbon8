<?
class UnidadeVolumeQuery
{
	public static function buscarUnidadeVolume()
	{
		return "SELECT un, descr FROM unidadevolume WHERE status = 'A' ORDER BY descr";
	}
}
?>