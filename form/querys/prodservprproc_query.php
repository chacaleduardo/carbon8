<?
class ProdservPrProcQuery
{
	public static function buscarProcessosPorIdProdserv()
	{
		return "SELECT c.*, p.proc, p.idprproc
				  FROM prodservprproc c LEFT JOIN prproc p ON (p.idprproc = c.idprproc)
				 WHERE c.idprodserv = ?idprodserv?
			  ORDER BY c.status";
	}

	public static function atualizarVersaoProdservPrProc()
	{
		return "UPDATE prodservprproc SET versao = versao+1 WHERE idprodservprproc = ?idprodservprproc?";
	}
}
?>