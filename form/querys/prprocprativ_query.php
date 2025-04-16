<?
class PrProcPrativQuery
{
	public static function buscarOrdemPrProcPrativPorIdPrProc()
	{
		return "SELECT (IFNULL(MAX(pa.ord), 0) + 1) AS ordem
				  FROM prprocprativ pa
				 WHERE pa.idprproc = ?idprproc?";
	}

	public static function buscarPrProPrativComHoraEstimada()
	{
		return "SELECT CAST(SUM(pa.tempoestimado) as TIME) AS tempoestimado
				  FROM prprocprativ pa
				 WHERE pa.idprproc = ?idprproc?";
	}
}
?>