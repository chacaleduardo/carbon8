<?
class PrativQuery
{
	public static function buscarAtividadesDisponivesParaVinculoEmServico()
	{
		return "SELECT a.idprativ, a.ativ, a.descr
		from prativ a
		where a.idempresa = ?idempresa?
		and a.status != 'INATIVO'
		and not exists(
			select 1
			from prativobj po
			join prodserv s on s.idprodserv = po.idobjeto
			where po.idobjeto = ?idobjeto?
			and po.tipoobjeto = '?tipoobjeto?'
			and s.tipo = '?tipo?'
			and po.idprativ = a.idprativ
		)";
	}

	public static function buscarProcessosPorTipoEIdEmpresa()
	{
		return "SELECT pa.idprprocprativ,
					   pa.idfluxostatus AS idfluxostatuspp,
					   pa.loteimpressao,
					   pa.prazod,
					   pa.dia,
					   pa.ord AS ordem,
					   pa.idetapa,
					   pa.ord,
					   pa.tempoestimado,
					   pa.bloquearstatus,
					   a.*
				  FROM prprocprativ pa JOIN prativ a ON (a.idprativ = pa.idprativ)
				 WHERE pa.idprproc = ?idprproc?
			  ORDER BY pa.ord, pa.loteimpressao";
	}

	public static function buscarProcessosPorIdProdservPrProc()
	{
		return "SELECT *
				  FROM prativ pa JOIN prprocprativ pp ON (pp.idprativ = pa.idprativ)
				  JOIN prodservprproc pr ON (pp.idprproc = pr.idprproc)
				 WHERE pr.idprodservprproc = ?idprodservprproc?
			  ORDER BY pp.ord;";
	}

	public static function buscarAtividadePorIdempresaEAtividadeNaoNulo()
	{
		return "SELECT idprativ, ativ
				  FROM prativ a
				 WHERE a.status = 'APROVADO' 
				   AND a.ativ != ''
				   ?getidempresa?
			  ORDER BY a.ativ";
	}

	public static function buscarAtividadePorIdProProc()
	{
		return "SELECT pa.idprprocprativ,
					   pa.loteimpressao,
					   pa.dia,
					   pa.ord AS ordem,
					   pa.idetapa,
					   pa.idfluxostatus,
					   a.*
				  FROM prprocprativ pa JOIN prativ a ON (a.idprativ = pa.idprativ)
				 WHERE pa.idprproc = ?idprproc?
			  ORDER BY pa.loteimpressao, ordem";
	}

	public static function listarAtividadePorTamanhoAtivMaiorDois()
	{
		return "SELECT DISTINCT TRIM(ativ) AS ativ
				  FROM prativ
				 WHERE ativ > '' AND LENGTH(ativ) > 2
				 ?getidempresa?
			  ORDER BY TRIM(ativ)";
	}
}
?>