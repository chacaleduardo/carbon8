<?
class LoteconsQuery
{
	public static function inserir()
	{
		return "INSERT INTO lotecons(
				idempresa, idtransacao, idlote, tipoobjeto,
				idobjeto, tipoobjetoconsumoespec, idobjetoconsumoespec,
				idlotefracao, qtdd, qtdd_exp, qtdc, qtdc_exp, qtdsaldo,
				qtdsaldo_exp, qtdsol, qtdsol_exp, obs, status, criadoem,
				criadopor, alteradoem, alteradopor
			)
			VALUES(
				?idempresa?, ?idtransacao?, ?idlote?, '?tipoobjeto?',
				?idobjeto?, '?tipoobjetoconsumoespec?', ?idobjetoconsumoespec?,
				?idlotefracao?, ?qtdd?, '?qtdd_exp?', ?qtdc?, '?qtdc_exp?', ?qtdsaldo?,
				'?qtdsaldo_exp?', ?qtdsol?, '?qtdsol_exp?', '?obs?', '?status?', ?criadoem?,
				'?criadopor?', ?alteradoem?, '?alteradopor?'
			)";}

	public static function inserirLoteCons()
	{
		return "INSERT INTO lotecons(idlote,
									 idlotefracao,
									 idempresa,
									 idobjeto,
									 tipoobjeto,
									 obs,
									 idtransacao, 
									 idobjetoconsumoespec,
									 tipoobjetoconsumoespec,
									 status,
									 qtdd,
									 qtdc,
									 criadoem,
									 criadopor, 
									 alteradoem, 
									 alteradopor)
							  VALUES(?idlote?, 
							  		 ?idlotefracao?, 
									 ?idempresa?,
									 ?idobjeto?, 
									 '?tipoobjeto?',
									 '?obs?',
									 '?idtransacao?', 
									 ?idobjetoconsumoespec?,
									 '?tipoobjetoconsumoespec?', 
									 '?status?', 
									 '?qtdd?', 
									 '0', 
									 NOW(),
									 '?usuario?', 
									 NOW(), 
									 '?usuario?')";
	}

	public static function buscarConsumoMaior60Dias()
    {
		return "SELECT COUNT(1) 
				  FROM lotecons lc JOIN lote l ON (lc.idlote = l.idlote 
				   AND l.idprodserv = ?idprodserv? 
				   AND lc.criadoem > DATE_ADD(UTC_DATE(), INTERVAL - 60 DAY))";
	}
	public static function deletaLoteconsPorId()
	{
		return "DELETE FROM lotecons 
		WHERE
			(qtdd = 0 OR qtdd IS NULL)
			AND idlotecons = ?idlotecons?";
	}

	public static function buscarConsumoLotecons()
    {
		return "SELECT 
					c.*
				FROM
					lotecons c
				WHERE
					c.idobjeto = ?idobjeto?
						AND c.tipoobjeto = '?tipoobjeto?'";
	}

	public static function buscarConsumoLoteconsPorIdLoteEIdLoteFracao()
    {
		return "SELECT idlotecons AS id, 
					   qtdsol, 
					   qtdc, 
					   'lotecons' AS tipo
				  FROM lotecons c
				 WHERE c.idobjeto = ?idobjeto?
				   AND c.tipoobjeto = '?tipoobjeto?'
				   AND idlotefracao = ?idlotefracao?
				   AND idlote = ?idlote?
				   ?condicionalStatus?";
	}

	public static function buscarConsumoLoteconsBioensaio()
    {
		return "SELECT * from lotecons  where idlote = ?idlote? and tipoobjeto = 'bioensaio' and idobjeto=?idobjeto?";
	}

	public static function buscarConsumoLoteLoteconsLoteFracao()
    {
		return "SELECT c.idlotecons,
					   c.qtdd,
					   c.qtdd_exp,
					   c.qtdc,
					   c.qtdc_exp,
					   c.idlotefracao,
					   l.idlote,
					   l.partida,
					   l.exercicio,
					   p.descr
				  FROM lotecons c JOIN lotefracao f ON f.idlotefracao = c.idlotefracao
				  JOIN lote l ON f.idlote = l.idlote
				  JOIN prodserv p ON p.idprodserv = l.idprodserv
				 WHERE c.idobjeto = ?idobjeto?
				   AND c.tipoobjeto = '?tipoobjeto?'
			  ORDER BY p.descr";
	}

	public static function buscarConsumoProduto()
	{
		return "SELECT c.idlotecons,
					   p.idprodserv,
					   CONCAT(l.partida, '/', l.exercicio) AS partida,
					   c.qtdd,
					   c.qtdd_exp,
					   pf.volumeformula,
					   p.volumeprod,
					   IFNULL(pf.qtdpadraof, p.qtdpadrao) AS qtdpadrao,
					   IFNULL(pf.qtdpadraof_exp, p.qtdpadrao_exp) AS qtdpadrao_exp,
					   l.idprodservformula,
					   p.retornarest
				  FROM lote l JOIN lotecons c ON (c.idlote = l.idlote AND c.tipoobjeto = '?tipoobjeto?' AND c.idobjeto = ?idobjeto?)
				  JOIN prodserv p ON p.idprodserv = l.idprodserv
			 LEFT JOIN prodservformula pf ON pf.idprodservformula = l.idprodservformula
				 WHERE c.qtdd <> 0
				   AND c.qtdd <> ''
				   AND c.qtdd IS NOT NULL
				   ?strped?
			  ORDER BY idprodserv";
	}

	public static function buscarConsumoProdutoRetornarEstoque()
	{
		return "SELECT c.idlotecons
					   
				  FROM lote l JOIN lotecons c ON (c.idlote = l.idlote AND c.tipoobjeto = '?tipoobjeto?' AND c.idobjeto = ?idobjeto?)
				  JOIN prodserv p ON p.idprodserv = l.idprodserv
				 WHERE c.qtdd <> 0
				   AND c.qtdd <> ''
				   AND c.qtdd IS NOT NULL
				   AND p.retornarest = 'Y'";
	}

	public static function buscarConsumoLotePorTipoObjetoConsumoEspec()
	{
		return "SELECT idlotecons AS id, qtdd, qtdc, 'lotecons' AS tipo, status
				  FROM lotecons
				 WHERE idlote = ?idlote?
				   AND idlotefracao = ?idlotefracao?
				   AND idobjetoconsumoespec = ?idobjetoconsumoespec?
				   AND tipoobjetoconsumoespec = '?tipoobjetoconsumoespec?'
				   AND status IN ('ABERTO', 'PENDENTE')";
	}

	public static function buscarConsumoEUnidade()
	{
		return "SELECT c.idlotecons,
					   c.tipoobjetoconsumoespec,
					   c.tipoobjeto,
					   c.qtdsol,
					   c.qtdsol_exp,
					   c.qtdd,
					   c.qtdd_exp,
					   c.qtdc,
					   c.qtdc_exp,
					   c.obs,
					   c.criadoem,
					   c.criadopor,
					   a.partida,
					   IFNULL(a.idloteorigem, a.idlote) AS idlote,
					   a.exercicio,
					   o.idobjeto,
					   u.unidade AS destino,
					   u.consomeun AS consumetransfdestino,
					   p.consometransf AS consometransf,
					   uori.unidade AS origem,
					   uori.idunidade,
					   c.idtransacao,
					   c.status,
					   c.idobjetoconsumoespec
				  FROM lotecons c JOIN lotefracao f ON c.idlote = f.idlote
			 LEFT JOIN lote a ON (a.idlote = c.idobjeto AND c.tipoobjeto = 'lote')
			 LEFT JOIN prodserv p ON (p.idprodserv = a.idprodserv)
			 LEFT JOIN unidadeobjeto o ON (o.tipoobjeto = 'modulo' AND o.idunidade = a.idunidade AND o.idobjeto LIKE 'lote%' AND o.idobjeto NOT LIKE 'lotesformulados%')
			 LEFT JOIN "._DBCARBON."._modulo m ON (m.modulo = o.idobjeto AND m.ready = 'FILTROS' AND m.modulotipo = 'lote' AND m.status = 'ATIVO')
				  JOIN unidade u ON (u.idunidade = a.idunidade )
				  JOIN unidade uori ON (uori.idunidade = f.idunidade )
   			     WHERE c.idlotefracao = f.idlotefracao
			 	   AND f.idlote = ?idlote?
				   AND (c.qtdd > 0 OR qtdsol > 0)
				   AND (c.tipoobjeto IS NULL OR c.tipoobjeto = 'lote') 
				   ?whereCondicao?			   
			  GROUP BY idlotecons 
			UNION 
				SELECT c.idlotecons,
			  		   c.tipoobjetoconsumoespec,
					   c.tipoobjeto,
					   c.qtdsol,
					   c.qtdsol_exp,
					   qtdd AS qtdd,
					   qtdd_exp AS qtdd_exp,
					   c.qtdc AS qtdc,
					   c.qtdc_exp AS qtdc_exp,
					   c.obs,
					   c.criadoem,
					   c.criadopor,
					   a.partida,
					   a.idlote,
					   a.exercicio,
					   o.idobjeto,
					   u.unidade AS destino,
					   u.consomeun AS consumetransfdestino,
					   p.consometransf AS consometransf,
					   uori.unidade AS origem,
					   uori.idunidade,
					   c.idtransacao,
					   c.status,
					   c.idobjetoconsumoespec
				  FROM lotecons c JOIN lotefracao f ON c.idlotefracao = f.idlotefracao
				  JOIN lotefracao lf ON (lf.idlotefracao = c.idobjeto AND c.tipoobjeto = 'lotefracao')
	  	     LEFT JOIN lote a ON (a.idlote = lf.idlote)
			 LEFT JOIN prodserv p ON (p.idprodserv = a.idprodserv)
			 LEFT JOIN unidadeobjeto o ON (o.tipoobjeto = 'modulo' AND o.idunidade = lf.idunidade AND o.idobjeto LIKE 'lote%' AND o.idobjeto NOT LIKE 'lotesformulados%')
			 LEFT JOIN "._DBCARBON."._modulo m ON (m.modulo = o.idobjeto AND m.ready = 'FILTROS' AND m.modulotipo = 'lote' AND m.status = 'ATIVO')
				  JOIN unidade u ON (u.idunidade = lf.idunidade )
				  JOIN unidade uori ON (uori.idunidade = f.idunidade )
  			     WHERE f.idlote = ?idlote? AND (c.qtdc > 0) 
				   AND c.tipoobjeto = 'lotefracao'
				   ?whereCondicao?
			  GROUP BY idlotecons
		  	UNION 
				SELECT c.idlotecons,
					   c.tipoobjetoconsumoespec,
					   c.tipoobjeto,
					   c.qtdsol,
					   c.qtdsol_exp,
					   c.qtdd,
					   c.qtdd_exp,
					   c.qtdc AS qtdc,
					   c.qtdc_exp AS qtdc_exp,
					   c.obs,
					   c.criadoem,
					   c.criadopor,
					   a.partida,
					   a.idlote,
					   a.exercicio,
					   o.idobjeto,
					   u.unidade AS destino,
					   u.consomeun AS consumetransfdestino,
					   p.consometransf AS consometransf,
					   uori.unidade AS origem,
					   uori.idunidade,
					   c.idtransacao,
					   c.status,
					   c.idobjetoconsumoespec
				  FROM lotecons c JOIN lotefracao f 
				  JOIN lotefracao lf ON (lf.idlotefracao = c.idobjeto AND c.tipoobjeto = 'lotefracao')
	   	  	 LEFT JOIN lote a ON (a.idlote = lf.idlote)
			 LEFT JOIN prodserv p ON (p.idprodserv = a.idprodserv)
			 LEFT JOIN unidadeobjeto o ON (o.tipoobjeto = 'modulo' AND o.idunidade = lf.idunidade AND o.idobjeto LIKE 'lote%' AND o.idobjeto NOT LIKE 'lotesformulados%')
			 LEFT JOIN "._DBCARBON."._modulo m ON (m.modulo = o.idobjeto AND m.ready = 'FILTROS' AND m.modulotipo = 'lote' AND m.status = 'ATIVO')
				  JOIN unidade u ON (u.idunidade = lf.idunidade )
				  JOIN unidade uori ON (uori.idunidade = f.idunidade)
  				 WHERE c.idlotefracao = f.idlotefracao
				   AND f.idlote = ?idlote?
				   AND (c.qtdd > 0)
				   AND c.tipoobjeto = 'lotefracao'
				   ?whereCondicao?
			  GROUP BY idlotecons 
		UNION 
			SELECT c.idlotecons,
			  	   c.tipoobjetoconsumoespec,
				   'adicao',
                   c.qtdsol,
				   c.qtdsol_exp,
				   c.qtdd,
				   c.qtdd_exp,
				   c.qtdc,
				   c.qtdc_exp,
				   c.obs,
				   c.criadoem,
				   c.criadopor,
				   '' AS partida,
				   '' AS idlote,
				   '' AS exercicio,
				   '' AS idobjeto,
				   CASE
				   		WHEN c.qtdd > 0 THEN 'Retirada'
						ELSE 'Adição'
					END AS destino,
					'' AS consumetransfdestino,
					'' AS consometransf,
					uori.unidade AS origem,
					uori.idunidade,
					c.idtransacao,
					c.status,
					c.idobjetoconsumoespec	
			   FROM lotecons c JOIN lotefracao f ON c.idlotefracao = f.idlotefracao
			   JOIN unidade uori ON (uori.idunidade = f.idunidade )
  		      WHERE f.idlote = ?idlote?
			    AND (c.qtdd > 0 OR qtdc > 0)
				AND (c.tipoobjeto IS NULL or c.tipoobjeto = '')
				AND c.idobjeto IS NULL
				?whereCondicao?
 		   ORDER BY criadoem ASC";
	}

	public static function buscarGrupoLoteConsPorIdTransacao()
	{
		return "SELECT GROUP_CONCAT(c.idlotecons) AS ids, f.idlotefracao
				  FROM lotecons c LEFT JOIN lotefracao f ON (c.idtransacao = f.idtransacao)
				 WHERE c.idtransacao = ?idtransacao?";
	}

	public static function buscarGrupoLoteConsPorIdTransacaoParaExclusao()
	{
		return "SELECT GROUP_CONCAT(c.idlotecons) AS ids, f.idlotefracao
				  FROM lotecons c
				  LEFT JOIN lotefracao f ON (c.idtransacao = f.idtransacao)
				 WHERE c.idtransacao = ?idtransacao?
				 and not exists (select 1 from lotecons c1 where c1.idlotefracao = f.idlotefracao and c1.qtdd > 0 and c1.status = 'ABERTO')";
	}

	public static function buscarConsumoPorLoteFracaoETipoObjeto()
	{
		return "SELECT c.idlotecons,
					   c.qtdd,
					   c.qtdd_exp,
					   c.qtdc,
					   c.qtdc_exp,
					   c.obs,
					   c.criadoem,
					   c.criadopor,
					   c.idobjeto,
					   c.tipoobjeto,
					   c.idempresa,
					   u.unidade,
					   f.idlote
				  FROM lotecons c JOIN lotefracao f ON c.idlote = f.idlote
			 LEFT JOIN unidadeobjeto o ON (o.tipoobjeto = 'modulo' AND o.idunidade = f.idunidade)
			 	  JOIN "._DBCARBON."._modulo m ON (m.modulo = o.idobjeto AND m.ready = 'FILTROS' AND m.modulotipo = 'lote')
				  JOIN unidade u ON (u.idunidade = f.idunidade  AND u.idtipounidade != 5)
  			     WHERE c.idlotefracao = f.idlotefracao
			  	   AND (c.qtdd > 0 OR qtdc > 0)
				   AND c.tipoobjeto IN ('nfitem', 'resultado')
				   ?whereCondicao?
				   AND f.idlote = ?idlote?
			  ORDER BY c.criadoem ASC";
	}

	public static function buscarConsumoLoteMes()
	{
		return "SELECT SUM(totalconsumomes) AS totalconsumomes 
				  FROM (SELECT SUM(si.qtdc) AS totalconsumomes 
						  FROM solmatitem si JOIN solmat s ON s.idsolmat = si.idsolmat
						 WHERE s.criadoem BETWEEN '?ano?-?mes?-01 00:00:00' AND '?ano?-?mes?-31 23:59:59'
						   AND s.status IN ('ABERTO', 'SOLICITADO')
						   AND si.idprodserv = ?idprodserv?
						   AND NOT EXISTS (SELECT 1 FROM lotecons lcn WHERE lcn.idobjetoconsumoespec = si.idsolmatitem AND lcn.tipoobjetoconsumoespec = 'solmatitem')
					UNION
						SELECT SUM(c.qtdd) AS totalconsumomes 
						  FROM prodserv p JOIN lote l ON (l.idprodserv = p.idprodserv)
						  JOIN lotefracao f ON (f.idlote = l.idlote AND f.idunidade = p.idunidadeest)
						  JOIN lotecons c ON (c.idlotefracao = f.idlotefracao AND c.qtdd > 0 AND c.status = 'ABERTO' AND c.tipoobjeto = 'lotefracao')
						  JOIN lotefracao fd ON (fd.idlotefracao = c.idobjeto AND fd.idunidade = ?idunidade?)
						 WHERE p.idprodserv = ?idprodserv?
						   AND c.criadoem BETWEEN '?ano?-?mes?-01 00:00:00' AND '?ano?-?mes?-31 23:59:59') AS somaconsumo";
	}

	public static function apagarLoteConsRestauracaoPorIdLote()
	{
		return "DELETE c.* FROM loteativ a JOIN lotecons c ON (c.idobjetoconsumoespec = a.idloteativ AND c.tipoobjetoconsumoespec = 'loteativ') 
				 WHERE a.idlote = ?idlote?";
	}

	public static function atualizarLoteConsRestauracaoPorIdLote()
	{
		return "UPDATE loteativ a JOIN lotecons c ON (c.idobjetoconsumoespec = a.idloteativ AND c.tipoobjetoconsumoespec = 'loteativ') 
				   SET c.status = 'INATIVO'
				 WHERE a.idlote = ?idlote?";
	}

	public static function buscarConsumoPendenteDaSolmat()
	{
		return "SELECT lc.*,lf.idunidade,lf.idlotefracao as idlotefracaofn
				FROM solmatitem si JOIN lotecons lc ON (lc.idobjetoconsumoespec = si.idsolmatitem
						AND lc.tipoobjetoconsumoespec = 'solmatitem'
						AND lc.status = 'PENDENTE'
						AND lc.qtdd > 0)
						JOIN lotefracao lf ON (lf.idlotefracao = lc.idobjeto and lc.tipoobjeto = 'lotefracao')
				WHERE
					si.idsolmat = ?idsolmat?";
	}

	public static function atualizarStatusLoteCons()
	{
		return "UPDATE lotecons
				SET status = '?status?',
				alteradoem = NOW(),
				alteradopor = '?alteradopor?'
				WHERE idlotecons = ?idlotecons?";
	}


	public static function buscarUltimoConsumoPorIdlote() {
		return "SELECT co.idlotecons, co.status
				from lotefracao lfo
				join lotecons co on co.idlotefracao and lfo.idlotefracao and co.idlote = lfo.idlote 
				join lotefracao lf on lf.idlotefracao = co.idobjeto and co.tipoobjeto = 'lotefracao'
				join lotecons c on c.idlotefracao = lf.idlotefracao  and c.idlote = lf.idlote 
				where lfo.idlote = ?idlote?
				and lfo.idunidade = 2
				and co.qtdd > 0
				and co.status  != 'INATIVO'
				group by co.idlotecons
				order by co.idlotecons desc";
	}

	public static function inativarLoteCons() {
		return "UPDATE lotecons SET status = 'INATIVO' WHERE idlotecons = ?idlotecons?";
	}

	public static function consumirFracao() {
		return "INSERT INTO lotecons (idempresa, idtransacao, idlotefracao, idlote, idobjeto, tipoobjeto, qtdd, obs, criadoem, criadopor, alteradoem, alteradopor)
				VALUES(?idempresa?, ?idtransacao?, ?idlotefracao?, ?idlote?, ?idobjeto?, '?tipoobjeto?', ?qtdd?, '?obs?', NOW(), '?usuario?', NOW(), '?usuario?')";
	}
	public static function excluirLoteconsRestaurarOP()
	{
		return "DELETE FROM lotecons WHERE idobjeto = '?idlote?' AND tipoobjeto = '?tipoobjeto?'";
		
	}

	public static function atualizarLoteConsPorIdTransacaoCredito()
    {
        return "UPDATE lotecons SET status = '?status?', qtdc = '?qtdc?' 
                 WHERE qtdc = 0 AND qtdd = 0
				   AND idtransacao = '?idtransacao?'";
    }

	public static function atualizarLoteConsPorIdTransacaoDebito()
    {
        return "UPDATE lotecons SET status = '?status?', qtdd = '?qtdd?' 
                 WHERE qtdd > 0
				   AND idtransacao = '?idtransacao?'";
    }

}
?>