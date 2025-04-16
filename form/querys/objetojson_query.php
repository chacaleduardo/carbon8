<?
class ObjetoJsonQuery
{
	public static function buscarVersaoObjetoPorTipoObjeto()
    {
		return "SELECT versaoobjeto, alteradopor, alteradoem FROM objetojson WHERE idobjeto = ?idobjeto? AND tipoobjeto = '?tipoobjeto?' ORDER BY versaoobjeto DESC";
	}

	public static function buscarVersaoAtualPorTipoObjeto()
    {
		return "SELECT max(versaoobjeto) as versaoobjeto FROM objetojson WHERE idobjeto = ?idobjeto? AND tipoobjeto = '?tipoobjeto?'";
	}

	public static function buscarObjetoPorTipoObjeto()
    {
		return "SELECT * FROM objetojson WHERE idobjeto = ?idobjeto? AND tipoobjeto = '?tipoobjeto?' ORDER BY versaoobjeto DESC";
	}

	public static function buscarVersaoObjetoPorTipoObjetoEVersao()
    {
		return "SELECT * 
				  FROM objetojson 
				 WHERE idobjeto = ?idobjeto? 
				   AND tipoobjeto = '?tipoobjeto?' 
				   AND versaoobjeto = ?versaoobjeto?";
	}

	public static function atualizarJobjetoObjetoJsonPorIdobjetojson()
    {
		return "UPDATE objetojson SET jobjeto = '?jobjeto?' WHERE idobjetojson = ?idobjetojson?";
	}	

	public static function inserirObjetoJson()
    {
		return "INSERT INTO objetojson (idempresa,
										idobjeto,
										tipoobjeto,
										jobjeto,
										versaoobjeto,
										criadopor,
										criadoem,
										alteradopor,
										alteradoem)
								VALUES (?idempresa?,
										?idobjeto?,
										'?tipoobjeto?',
										'?jobjeto?',
										?versaoobjeto?,
										'?criadopor?',
										now(),
										'?alteradopor?',
										now())";
	}
}
?>