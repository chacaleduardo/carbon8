<?
class PrProcQuery
{
	public static function buscarProcessosPorTipoEIdEmpresa()
	{
		return "SELECT a.idprproc, a.proc
				  FROM prproc a
				 WHERE a.status = 'APROVADO'
				   AND a.tipo = '?tipo?'
				   AND a.idempresa = ?idempresa?
			  ORDER BY a.proc;";
	}

	public static function buscarProdservPrProcPorIdProdserv()
	{
		return "SELECT c.*, p.proc, p.idprproc
				  FROM prodservprproc c LEFT JOIN prproc p ON (p.idprproc = c.idprproc)
				 WHERE c.idprodserv = ?idprodserv?
			  ORDER BY c.status";
	}

	public static function buscarProcessos()
	{
		return "SELECT * FROM prproc WHERE idprproc = ?idprproc?";
	}

	public static function buscarProcessosLigadosAtividade()
	{
		return "SELECT a.*
				  FROM prprocprativ pa JOIN prproc a ON (a.idprproc = pa.idprproc)
				 WHERE pa.idprativ = '?idprativ?'
			  ORDER BY a.proc";
	}

	public static function buscarAtividadesGrupo()
	{
		return "SELECT p.idprproc,
					   p.proc,
					   p.tipo,
					   a.idprativ,
					   a.ativ,
					   pa.dia,
					   a.statuspai,
					   pa.idetapa,
					   pa.bloquearstatus,
					   a.nomecurtoativ,
					   pa.idprprocprativ,
					   pa.tempoestimado,
					   p.tempogastoobrigatorio,
					   IFNULL(pa.loteimpressao, 0) loteimpressao,
					   IFNULL(pa.ord, 0) ordativ,
					   pa.idfluxostatus
				  FROM prproc p JOIN prprocprativ pa ON (p.idprproc = pa.idprproc)
				  JOIN prativ a ON (a.idprativ = pa.idprativ)
				 WHERE p.idprproc = ?idprproc?
			  ORDER BY pa.ord";
	}
}
?>