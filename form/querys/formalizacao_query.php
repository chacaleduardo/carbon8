<?
require_once(__DIR__ . "/_iquery.php");

class FormalizacaoQuery implements DefaultQuery
{

	public static $table = "formalizacao";
	public static $pk = "idformalizacao";

	public static function buscarPorChavePrimaria()
	{
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ['table' => self::$table, 'pk' => self::$pk]);
	}

	public static function buscarFormalizacoesPorSubTipo()
	{
		return "SELECT idformalizacao as idevento,
						'FORMALIZACAO' as tipo,
						idformalizacao as idobjeto,
						'loteativ' as objeto,
						pd.descr as evento, 
						DATE(producao) inicio,
						DATE_ADD(time(producao), INTERVAL 8 HOUR) as iniciohms,
						DATE(producao) fim,
						DATE_ADD(time(producao), INTERVAL 8 HOUR) as fimhms,
						'' as jsonconfig,
						fs.cor as cor
				FROM formalizacao f JOIN lote l ON l.idlote = f.idlote
				JOIN prproc p ON f.idprproc = p.idprproc
				JOIN prodserv pd ON pd.idprodserv = l.idprodserv
				JOIN formalizacaosubtipo fs ON fs.subtipo = p.subtipo
				JOIN fluxostatus fls ON(fls.idfluxostatus = p.idfluxostatus)
				JOIN " . _DBCARBON . "._status cs ON(cs.idstatus = fls.idstatus)
				WHERE p.subtipo IN (?subtipo?)
				AND DATE_FORMAT('?data?','%Y-%m') BETWEEN DATE_FORMAT(DATE_SUB(producao, INTERVAL 14 DAY),'%Y-%m') AND DATE_FORMAT(DATE_ADD(producao, INTERVAL 14 DAY),'%Y-%m')
				AND cs.statustipo != 'CANCELADO'";
	}

	public static function buscarLoteDaFormalizacao()
	{
		return "SELECT l.idlote,l.partida,l.exercicio,l.piloto, f.idformalizacao
				FROM loteativ a
					JOIN lote l ON l.idlote = a.idlote
					JOIN formalizacao f ON f.idlote = l.idlote
				where a.idloteativ= ?idloteativ?";
	}

	public static function buscarFormalizacaoPorIdLote()
	{
		return "SELECT idformalizacao, status
				  FROM formalizacao
				 WHERE idlote = ?idlote?";
	}

	public static function buscarFluxoFormalizacao()
	{
		return "SELECT * 
			FROM (
					SELECT e.etapa, 
						e.idetapa, 
						e.ordem AS 'ordemfs',
						s.rotulo AS 'rotulo',
						la.idfluxostatus,
						mh.status,   
						la.status AS 'statusloteativ',                 
						mh.idfluxostatushist,
						concat(ifnull(em.sigla,''),' - ',mh.criadopor) as criadopor,
						la.ord AS 'ordem',
						mh.criadoem
					FROM formalizacao f JOIN loteativ la ON la.idlote = f.idlote 
						JOIN fluxostatus fs ON fs.idfluxostatus = la.idfluxostatus
						JOIN " . _DBCARBON . "._status s ON s.idstatus = fs.idstatus
						LEFT JOIN fluxostatushist mh ON mh.idmodulo = '?idobjeto?' 
							AND mh.modulo = '?modulo?' 
							AND mh.idfluxostatus = la.idfluxostatus
						JOIN etapa e ON la.idetapa = e.idetapa 
						LEFT JOIN pessoa ps on(ps.usuario= mh.criadopor)
                		LEFT JOIN empresa em on(em.idempresa=ps.idempresa)
					WHERE idformalizacao = '?idobjeto?' 
					UNION 
					SELECT e.etapa, 
						e.idetapa, 
						e.ordem AS 'ordemfs',
						sa.rotulo AS 'rotulo',
						fs.idfluxostatus,
						mh.status, 
						'ATIVO' AS 'statusloteativ',                   
						mh.idfluxostatushist,
						concat(ifnull(em.sigla,''),' - ',mh.criadopor) as criadopor,
						if(sa.statustipo = 'ABERTO', -2, -1) AS 'ordem',
						mh.criadoem 
					FROM formalizacao f JOIN prproc p ON p.idprproc = f.idprproc
						JOIN fluxo fx ON fx.idobjeto = p.subtipo 
							AND fx.tipoobjeto = 'subtipo'
						JOIN " . _DBCARBON . "._status sa ON (sa.statustipo = 'ABERTO') 
							OR (sa.statustipo = 'TRIAGEM' 
								AND sa.tipobotao = 'INICIO')
						JOIN fluxostatus fs ON fs.idstatus = sa.idstatus 
							AND fs.idfluxo = fx.idfluxo
						LEFT JOIN fluxostatushist mh ON mh.idmodulo = '?idobjeto?' 
							AND mh.modulo = '?modulo?' 
							AND mh.idfluxostatus = fs.idfluxostatus
						JOIN etapa e ON fs.idetapa = e.idetapa 
						LEFT JOIN pessoa ps on(ps.usuario= mh.criadopor)
						LEFT JOIN empresa em on(em.idempresa=ps.idempresa)
					WHERE idformalizacao = '?idobjeto?'
						AND fs.idfluxostatus NOT IN (SELECT idfluxostatus 
							FROM loteativ lt 
							WHERE lt.idlote = f.idlote
						) AND etapa <= 2
			) AS u
			ORDER BY etapa ASC, status DESC";
	}

	public static function buscarStatusTipoFormalizacao()
	{
		return "SELECT s.statustipo, fs.idfluxostatus, s.tipobotao
			FROM formalizacao f 
				JOIN prproc p ON f.idprproc = p.idprproc
				JOIN fluxo fx ON fx.idobjeto = p.subtipo 
					AND fx.tipoobjeto = 'subtipo'
				JOIN fluxostatus fs ON fs.idfluxo = fx.idfluxo
				JOIN " . _DBCARBON . "._status s ON s.idstatus = fs.idstatus
			WHERE f.idformalizacao = '?idobjeto?'
				AND f.idfluxostatus = fs.idfluxostatus";
	}

	public static function buscarStatusAbertoInicioFormalizacao()
	{
		return "SELECT fs.idfluxostatus, s.statustipo
			FROM formalizacao f 
				JOIN prproc p ON f.idprproc = p.idprproc
				JOIN fluxo fx ON fx.modulo = 'formalizacao' 
					AND tipoobjeto = 'subtipo' 
					AND p.subtipo = fx.idobjeto 
				JOIN fluxostatus fs ON fs.idfluxo = fx.idfluxo
				JOIN " . _DBCARBON . "._status s ON s.idstatus = fs.idstatus 
					AND s.tipobotao = 'INICIO' 
					AND s.statustipo = 'ABERTO' 
			WHERE f.idformalizacao = '?idformalizacao?'";
	}

	public static function buscarStatusAbertoFormalizacao()
	{
		return "SELECT fs.idfluxostatus
			FROM formalizacao f 
				JOIN prproc p ON f.idprproc = p.idprproc
				JOIN fluxo fx ON fx.modulo = 'formalizacao' 
					AND tipoobjeto = 'subtipo' 
					AND p.subtipo = fx.idobjeto 
				JOIN fluxostatus fs ON fs.idfluxo = fx.idfluxo
				JOIN " . _DBCARBON . "._status s ON s.idstatus = fs.idstatus 
					AND s.statustipo = 'ABERTO' 
			WHERE f.idformalizacao = '?idobjeto?'";
	}

	public static function atualizarStatuseFluxoStatusFormalizacao()
	{
		return "UPDATE formalizacao f 
				JOIN lote l ON f.idlote = l.idlote
			SET l.idprodservformula = NULL, 
				l.idsolfab = NULL, 
				f.idfluxostatus = '?idfluxostatus?',
				f.alteradoem = sysdate(), 
				f.alteradopor = '?usuario?', 
				f.status = '?status?'
			WHERE f.idformalizacao = '?idformalizacao?'";
	}

	public static function buscarFluxoAtividadesFormalizacao()
	{
		return "SELECT * 
			FROM (
				SELECT fs.idfluxostatus,
					la.status,
					ft.idfluxostatus AS idfluxostatustab,
					st.statustipo AS statustipotab,
					e.etapa,
					la.idetapa
				FROM formalizacao f 
					JOIN loteativ la ON f.idlote = la.idlote
					JOIN etapa e ON la.idetapa = e.idetapa
					LEFT JOIN fluxostatus fs ON la.idfluxostatus = fs.idfluxostatus
					JOIN fluxostatus ft ON ft.idfluxostatus = f.idfluxostatus
					JOIN " . _DBCARBON . "._status st ON st.idstatus = ft.idstatus
					LEFT JOIN fluxostatushist fh ON fh.idfluxostatus = fs.idfluxostatus 
						AND fh.modulo = '?modulo?' 
						AND fh.idmodulo = '?idmodulo?' 
						AND fh.status <> 'INATIVO'
				WHERE f.idformalizacao = '?idformalizacao?'
				UNION 
				SELECT fs.idfluxostatus,
					fh.status,
					f.idfluxostatus AS idfluxostatustab,
					sa.statustipo AS statustipotab,
					e.etapa,
					e.idetapa
				FROM formalizacao f 
					JOIN prproc p ON p.idprproc = f.idprproc
					JOIN fluxo fx ON fx.idobjeto = p.subtipo AND fx.tipoobjeto = 'subtipo'
					JOIN " . _DBCARBON . "._status sa ON sa.statustipo = 'ABERTO'
					JOIN fluxostatus fs ON fs.idstatus = sa.idstatus 
						AND fs.idfluxo = fx.idfluxo
					LEFT JOIN fluxostatushist fh ON fh.idmodulo = '?idmodulo?' 
						AND fh.modulo = '?modulo?' 
						AND fh.idfluxostatus = fs.idfluxostatus 
						AND fh.status <> 'INATIVO'
					JOIN etapa e ON fs.idetapa = e.idetapa 
				WHERE idformalizacao = '?idformalizacao?'
				UNION 
				SELECT fs.idfluxostatus,
					fh.status,
					f.idfluxostatus AS idfluxostatustab,
					sa.statustipo AS statustipotab,
					e.etapa,
					e.idetapa
				FROM formalizacao f JOIN prproc p ON p.idprproc = f.idprproc
					JOIN fluxo fx ON fx.idobjeto = p.subtipo AND fx.tipoobjeto = 'subtipo'
					JOIN " . _DBCARBON . "._status sa ON sa.statustipo = 'TRIAGEM' AND sa.tipobotao = 'INICIO'
					JOIN fluxostatus fs ON fs.idstatus = sa.idstatus AND fs.idfluxo = fx.idfluxo
					LEFT JOIN fluxostatushist fh ON fh.idmodulo = '?idmodulo?' 
						AND fh.modulo = '?modulo?' 
						AND fh.idfluxostatus = fs.idfluxostatus 
						AND fh.status <> 'INATIVO'
					JOIN etapa e ON fs.idetapa = e.idetapa 
				WHERE idformalizacao = '?idformalizacao?' 
					AND etapa <= 2
			) AS f
			ORDER BY CONVERT(etapa, SIGNED), status";
	}

	public static function buscarFluxoRestauracao()
	{
		return "SELECT s.rotulo, 
				s.statustipo, 
				fs.idfluxostatus, 
				fs.ordem
			FROM formalizacao f 
				JOIN prproc p ON f.idprproc = p.idprproc
				JOIN fluxo fx ON fx.idobjeto = p.subtipo 
					AND fx.tipoobjeto = 'subtipo'
				JOIN fluxostatus fs ON fs.idfluxo = fx.idfluxo
				JOIN " . _DBCARBON . "._status s ON s.idstatus = fs.idstatus
			WHERE f.idformalizacao = '?idformalizacao?' 
				AND s.statustipo in ('ABERTO','CANCELADO')";
	}

	public static function buscarFormalizacaoParaBioensaio()
	{
		return "SELECT la.idloteativ,
						concat(l.partida,'/',l.exercicio) as partida
				from loteativ la,loteobj o,lote l
				where l.idlote = la.idlote
				and o.idloteativ = la.idloteativ 
				and o.idobjeto = 5 order by partida";
	}

	public static function buscarAtividadesLote()
	{
		return "SELECT l.idloteativ,
					   l.idprativ,
					   l.ativ,
					   IFNULL(NULLIF(pa.nomecurtoativ, ''), l.ativ) AS nomecurtoativ,
					   l.status,
					   l.execucao,
					   l.execucaofim,
					   l.duracao,
					   l.tempoestimado,
					   l.tempogastoobrigatorio,
					   l.ord,
					   l.impresso,
					   pa.travasala,
					   pa.idsgareasetor,
					   l.loteimpressao,
					   f.idformalizacao,
					   l.idfluxostatus,
					   l.bloquearstatus,
					   pa.cancelamento,
					   pa.naoconformidade
				  FROM loteativ l JOIN formalizacao f ON l.idlote = f.idlote
			 LEFT JOIN prativ pa ON pa.idprativ = l.idprativ
				 WHERE l.idlote = ?idlote? 
						?idloteativ?
			  ORDER BY l.loteimpressao, l.ord";
	}

	public static function buscarFluxoHistoricoIdFormalizacao()
	{
		return "SELECT fh.idfluxostatushist, fh.status
				  FROM formalizacao f JOIN loteativ la ON la.idlote = f.idlote 
			 LEFT JOIN fluxostatushist fh ON fh.idmodulo = ?idmodulo? and fh.modulo = '?modulo?' AND la.idfluxostatus = fh.idfluxostatus
				 WHERE la.idloteativ = ?idloteativ? 
				   AND fh.status <> 'INATIVO'
				   ?sqlStatus?";
	}

	public static function buscarSolfabFormalizacao()
	{
		return "SELECT s.status, f.idprproc, f.idunidade, f.status AS 'statusformalizacao'
				FROM solfab s JOIN lote l ON l.idsolfab = s.idsolfab 
				JOIN formalizacao f ON f.idlote = l.idlote
			   WHERE f.idformalizacao = ?idformalizacao?";
	}

	public static function buscarPrimeiroFluxoTriagem()
	{
		return "SELECT fs.idfluxostatus
				  FROM formalizacao f JOIN prproc p ON p.idprproc = f.idprproc
				  JOIN fluxo fx ON fx.idobjeto = p.subtipo AND fx.tipoobjeto = 'subtipo'
				  JOIN fluxostatus fs ON fs.idfluxo = fx.idfluxo
				  JOIN carbonnovo._status s ON s.idstatus = fs.idstatus AND s.statustipo = 'TRIAGEM' AND s.tipobotao = 'INICIO'
				 WHERE f.idformalizacao = ?idformalizacao?
				HAVING count(s.idstatus) > 1
			  ORDER BY fs.ordem
				 LIMIT 1;";
	}

	public static function buscarVacinasProgramadas()
	{
		return "SELECT idprodserv, partida, idpessoa, idlote, nome, descr, idformalizacao, envio, qtdpedida, formula, status
                FROM (SELECT p.idprodserv, l.partida, l.idpessoa, l.idlote, pf.nome, p.descr, fo.idformalizacao, fo.envio, qtdpedida,
                             IFNULL(CONCAT(pdf.rotulo, '-', IFNULL(pdf.dose, '--'), concat(' ', p.conteudo),' (', pdf.volumeformula, ' ', pdf.un,')'),'') AS formula,
                             CASE
                                WHEN FIND_IN_SET('PROCESSANDO', (SELECT GROUP_CONCAT(distinct(f2s.status)) AS status fROM formalizacao fs JOIN lote ls ON ls.idlote = fs.idlote
                                                                   JOIN solfab ss ON ss.idsolfab = ls.idsolfab
                                                                   JOIN solfabitem sis ON sis.idsolfab = ss.idsolfab
                                                                   JOIN lote l2s ON l2s.idlote = sis.idobjeto AND sis.tipoobjeto = 'lote'
                                                                   JOIN lotefracao lfs ON lfs.idlote = l2s.idlote
                                                                   JOIN lotecons cs ON cs.idlote = lfs.idlote AND cs.idlotefracao = lfs.idlotefracao AND (cs.qtdd > 0) AND (cs.tipoobjeto IS NULL OR cs.tipoobjeto = 'lote')
                                                                   JOIN formalizacao f2s ON f2s.idlote = cs.idobjeto AND cs.tipoobjeto = 'lote' WHERE fs.idformalizacao =  fo.idformalizacao)) THEN 'INSUFICIENTE' 
                                -- Compara a quantidade de produtos com a quantidade de lotes disponíveis dos lotes desses produtos. Caso os valores sejam iguais retorna 1 senão 0 que é insuficente
                                -- Nâo tem lotes (formalização para ser utilizada.)
                                WHEN ((SELECT CASE WHEN (SELECT COUNT(DISTINCT (lw.idprodserv)) AS contador_lote
                                                            FROM lote lw JOIN  lotefracao fw ON fw.idlote = lw.idlote
                                                            JOIN prodserv pw ON pw.idprodserv = lw.idprodserv
                                                            JOIN formalizacao fow ON fow.idlote = lw.idlote
                                                            WHERE lw.idprodserv IN (SELECT DISTINCT (lfi.idprodserv)  FROM loteformulains lfi JOIN prodserv pi ON pi.idprodserv = lfi.idprodserv WHERE lfi.idlote = l.idlote AND pi.especial = 'Y' AND pi.tipo = 'PRODUTO' AND pi.fabricado = 'Y')
                                                            AND fw.idunidade = 2
                                                            AND pw.especial = 'Y'
                                                            AND lw.idpessoa = l.idpessoa
                                                            AND ((lw.status IN ('ABERTO' , 'FORMALIZACAO', 'PROCESSANDO', 'QUARENTENA', 'APROVADO') AND fw.status = 'DISPONIVEL')
                                                                    OR EXISTS( SELECT 1 FROM lotecons con  WHERE con.idlote = lw.idlote AND con.idobjeto = l.idpessoa AND con.tipoobjeto = 'lote' AND qtdd > 0))
                                                            AND EXISTS(SELECT 1 FROM lotecons lcs JOIN lote lt ON lt.idlote = lcs.idlote JOIN vwsolfab ws ON ws.idloteitem = lcs.idlote
                                                                        WHERE lcs.idobjeto = lw.idlote AND tipoobjeto = 'lote' AND tipoobjetoconsumoespec = 'loteativespecial' AND ws.statuslotesolfabitem NOT IN ('CANCELADO') AND ws.idprodserv = lt.idprodserv
                                                                        AND ws.idsolfab = lw.idsolfab)) = (SELECT COUNT(DISTINCT (lfi.idprodserv)) AS contador_idprodserv FROM loteformulains lfi JOIN prodserv pi ON pi.idprodserv = lfi.idprodserv
																		  WHERE lfi.idlote = l.idlote AND pi.especial = 'Y' AND pi.tipo = 'PRODUTO' AND pi.fabricado = 'Y') THEN 1  ELSE 0 END AS total_lote)) = 0 THEN 'INSUFICIENTE' 
                                WHEN (SELECT SUM(fw.qtd) - (l.qtdajust * lfi.qtdi) AS totalconsumo  FROM lote lw JOIN  lotefracao fw ON fw.idlote = lw.idlote
										 JOIN loteformulains lfi ON lfi.idlote = l.idlote and lfi.idprodserv = l.idprodserv
									     JOIN prodserv pw ON pw.idprodserv = lw.idprodserv
									     JOIN formalizacao fow ON fow.idlote = lw.idlote
									    WHERE lw.idprodserv IN (SELECT DISTINCT (idprodserv) FROM loteformulains WHERE idlote = l.idlote)
										  AND fw.idunidade = 2
										  AND pw.especial = 'Y'
										  AND lw.idpessoa = l.idpessoa
										  AND ((lw.status IN ('ABERTO' , 'FORMALIZACAO', 'PROCESSANDO', 'QUARENTENA', 'APROVADO') AND fw.status = 'DISPONIVEL')  
													   OR EXISTS(SELECT 1 FROM lotecons con WHERE con.idlote = lw.idlote AND con.idobjeto = l.idpessoa AND con.tipoobjeto = 'lote' AND qtdd > 0))
										  AND EXISTS(SELECT 1 FROM lotecons lcs JOIN  lote lt ON lt.idlote = lcs.idlote JOIN vwsolfab ws ON ws.idloteitem = lcs.idlote
                                        WHERE lcs.idobjeto = lw.idlote AND tipoobjeto = 'lote' AND tipoobjetoconsumoespec = 'loteativespecial' 
                                          AND ws.statuslotesolfabitem NOT IN ('CANCELADO') AND ws.idprodserv = lt.idprodserv AND ws.idsolfab = lw.idsolfab)) < 0 THEN 'INSUFICIENTE' 
                                ELSE 'SUFICIENTE' 
                            END AS 'status'
                        FROM lote l JOIN formalizacao fo ON fo.idlote = l.idlote
                        JOIN prodserv p ON p.idprodserv = l.idprodserv AND p.venda = 'Y' AND p.tipo = 'PRODUTO' AND p.especial = 'Y'
                        JOIN prodservformula pdf ON pdf.idprodservformula = l.idprodservformula
                  LEFT JOIN pessoa pf ON pf.idpessoa = l.idpessoa
                        WHERE fo.idfluxostatus = 1283
						?idempresa?
						?clausulalote?
						?clausulad?
						?clausulapr?			
                    WHERE u.status = '?status?'
                 ORDER BY descr";
	}

	public static function buscarSementesVacina()
	{
		return "SELECT l.idlote,
						p.descr,
						case when l.vencimento < (DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 MONTH),'%Y-%m-%d')) then 'Y' else 'N' end as vencido,
						l.vencimento,
						l.partida,
						l.exercicio,
						l.status,
						fo.idformalizacao
				FROM lote l JOIN  lotefracao f ON f.idlote = l.idlote
				JOIN prodserv p ON p.idprodserv = l.idprodserv
				JOIN formalizacao fo ON fo.idlote = l.idlote
				WHERE l.idprodserv IN (SELECT DISTINCT (idprodserv) FROM loteformulains WHERE idlote = ?idlote?)
					AND f.idunidade = 2
					AND p.especial = 'Y'
					AND l.idpessoa = ?idpessoa?
					AND ((l.status IN ('ABERTO' , 'FORMALIZACAO', 'PROCESSANDO', 'QUARENTENA', 'APROVADO') AND f.status = 'DISPONIVEL')  
								OR EXISTS( SELECT 1 FROM lotecons con WHERE con.idlote = l.idlote AND con.idobjeto = ?idpessoa? AND con.tipoobjeto = 'lote' AND qtdd > 0))
					AND EXISTS(SELECT 1 FROM lotecons lcs JOIN  lote lt ON lt.idlote = lcs.idlote JOIN vwsolfab ws ON ws.idloteitem = lcs.idlote
									WHERE lcs.idobjeto = l.idlote AND tipoobjeto = 'lote' AND tipoobjetoconsumoespec = 'loteativespecial' 
										AND ws.statuslotesolfabitem NOT IN ('CANCELADO') AND ws.idprodserv = lt.idprodserv AND ws.idsolfab = l.idsolfab)
					?clausulalote?                 
					?clausulad?
				ORDER BY descr";
	}

	public static function buscarFormalizacaoPorlote()
	{
		return "SELECT f.idformalizacao, f.idprproc, f.idunidade
				  FROM formalizacao f JOIN lote l ON f.idlote = l.idlote
	   			 WHERE idsolfab = ?idsolfab? AND f.status = 'ABERTO';";
	}

	public static function atualizarResponsavel()
	{
		return "UPDATE formalizacao SET responsavel = NULL WHERE idformalizacao = ?idformalizacao?";
	}

	public static function atualizarLoteFormalizacao()
	{
		return "UPDATE formalizacao SET idlote = ?idlote? WHERE idformalizacao = ?idformalizacao?";
	}

	public static function atualizarPrProcFormalizacaoPorIdLote()
	{
		return "UPDATE formalizacao SET idprproc = ?idprproc? WHERE idlote = ?idlote?";
	}
	public static function atualizaLacreLote()
	{
		return "UPDATE
					loteformulains i
						JOIN
					lote l ON (l.idlote = i.idlote)
						JOIN
					prodservformulapref c ON (c.idpessoa = l.idpessoa
						AND c.idprodservformula = i.idprodservformula)
						JOIN
					prodserv p ON (c.idprodserv = p.idprodserv)
					SET
					i.idprodserv=p.idprodserv,
					i.codprodserv=p.codprodserv,
					i.descr=p.descr,
					i.descrcurta=p.descrcurta
				WHERE
					i.idlote = ?idlote?
						AND i.descr LIKE ('%SELO%')
						AND i.descr LIKE ('%ALUMINIO%')";
	}

	public static function buscarLoteIdPorFormalizacao()
	{
		return "SELECT 
						l.idunidade,
						l.idlote, 
						p.descr, 
						concat(l.partida, '/', l.exercicio) as partidainterna, 
						ifnull(f.rotulo, 'Sem fórmula') as formula, 
						IFNULL(f.qtdpadraof, 0) as qtdpadraof, 
						l.idempresa,
						l.observacao,
						l.qtdprod,
						lf.qtd_exp,
						lf.qtd,
						lf.idlotefracao,
						l.idprodserv,
						l.converteest,
						l.unpadrao,
						l.unlote
				FROM formalizacao op
				JOIN lote l ON l.idlote = op.idlote
				JOIN prodserv p ON p.idprodserv = l.idprodserv
				LEFT JOIN prodservformula f ON f.idprodservformula = l.idprodservformula
				LEFT JOIn lotefracao lf ON lf.idlote = l.idlote and lf.idunidade = ?idunidade?
				where op.idformalizacao = ?idformalizacao?";
	}

	public static function atualizarStatusFormalizacao()
	{
		return "UPDATE formalizacao SET status = '?status?', idfluxostatus = ?idfluxostatus? WHERE idformalizacao = '?idformalizacao?'";
	}
}
