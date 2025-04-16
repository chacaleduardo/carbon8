<?
class PrativOpcaoQuery
{
	public static function buscarOpcaoPorIdPrativopcao()
	{
		return "SELECT opcao, descr, ord, tipo, textoajuda, status, ord
				  FROM prativopcao 
				 WHERE idprativopcao = ?idprativopcao?
			  ORDER BY ord";
	}
}
?>