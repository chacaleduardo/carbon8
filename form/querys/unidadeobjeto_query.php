<?
class UnidadeObjetoQuery
{
	public static function buscarUnidadePadraoModulo()
	{
		return "SELECT o.idunidade
				FROM unidadeobjeto o JOIN unidade u ON (u.idunidade = o.idunidade ?idempresa? AND u.status = 'ATIVO')
				WHERE o.idobjeto = '?modulo?' AND o.tipoobjeto = 'modulo'";
	}

	public static function deletaUnidadeObjetoPorChavePrimaria()
	{
		return "DELETE FROM unidadeobjeto WHERE idunidadeobjeto = ?idunidadeobjeto?";
	}

	public static function buscarUnidadesDisponiveisParaVinculoPorIdSgDepartamentoEIdEmpresa()
	{
		return "SELECT u.idempresa, u.idunidade, u.unidade
		FROM unidade u
		JOIN empresa e on(e.idempresa = u.idempresa)
		JOIN unidadeobjeto uo on(uo.idunidade = u.idunidade)
		WHERE uo.idunidade NOT IN(
			SELECT uo2.idunidade
			FROM unidadeobjeto uo2
			WHERE uo2.idobjeto = ?idsgdepartamento?
			AND uo2.tipoobjeto = 'sgdepartamento'
		)
		AND e.status = 'ATIVO'
		AND u.status = 'ATIVO'
		?idempresa?
		GROUP BY u.idunidade;";
	}

	public static function buscarUnidadeDoIdSgDepartamentoEIdEmpresa()
	{
		return "SELECT qry_distinct.idunidade, qry_distinct.unidade, qry_distinct.idobjeto, qry_distinct.tipoobjeto, qry_distinct.idunidadeobjeto, qry_distinct.idempresa
				FROM (
					SELECT u.idunidade, u.unidade, unidadeobjeto.idobjeto, 'sgdepartamento' as tipoobjeto,unidadeobjeto.idunidadeobjeto, u.idempresa
					FROM unidadeobjeto  
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)  
					JOIN sgdepartamento ON(sgdepartamento.idsgdepartamento = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto='sgdepartamento')   
					WHERE unidadeobjeto.idobjeto = ?idsgdepartamento?
					AND unidadeobjeto.tipoobjeto = 'sgdepartamento'  
					AND unidadeobjeto.padrao ='Y'   
					AND u.status = 'ATIVO'
					?idempresa?
				)  as qry_distinct
				JOIN empresa e ON(e.idempresa = qry_distinct.idempresa)
				GROUP BY qry_distinct.idunidade
				ORDER BY e.sigla, qry_distinct.unidade";
	}

	public static function buscarUnidadeEnviocusto(){
			return "SELECT u.idunidade, u.unidade, ur.idobjeto, 'unidade' as tipoobjeto,ur.idunidaderateio, u.idempresa,ur.rateio
					FROM unidaderateio  ur
					JOIN unidade u ON(u.idunidade = ur.idobjeto)  
					WHERE  ur.idunidade = ?idunidade?
					AND ur.tipoobjeto = 'unidadepadrao'  
					AND ur.padrao ='Y'   
					AND u.status = 'ATIVO'";
	}

	public static function buscarUnidadeRecebecusto(){
		return "SELECT u.idunidade, u.unidade, ur.idobjeto, 'unidade' as tipoobjeto,ur.idunidaderateio, u.idempresa,ur.rateio
				FROM unidaderateio  ur
				JOIN unidade u ON(u.idunidade = ur.idunidade)  
				WHERE ur.idobjeto = ?idunidade?
				AND ur.tipoobjeto = 'unidadepadrao'  
				AND ur.padrao ='Y'   
				AND u.status = 'ATIVO'";
	}


	

	public static function buscarUnidadeVinculadaIdSgDepartamentoEIdEmpresa()
	{
		return "SELECT qry_distinct.idunidade, qry_distinct.unidade, qry_distinct.idobjeto, qry_distinct.tipoobjeto, qry_distinct.idunidadeobjeto, qry_distinct.idempresa
				FROM (

					SELECT u.idunidade, u.unidade, unidadeobjeto.idobjeto, 'sgdepartamento' as tipoobjeto,unidadeobjeto.idunidadeobjeto, sgdepartamento.idempresa
					FROM unidadeobjeto  
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)  
					JOIN sgdepartamento ON(sgdepartamento.idsgdepartamento = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto='sgdepartamento')   
					WHERE unidadeobjeto.idobjeto = ?idsgdepartamento?
					AND unidadeobjeto.tipoobjeto = 'sgdepartamento'  
					AND unidadeobjeto.padrao ='N'   
					AND u.status = 'ATIVO'
					?idempresa?

					union 

					SELECT u.idunidade, u.unidade, unidadeobjeto.idobjeto, 'sgdepartamento' as tipoobjeto ,unidadeobjeto.idunidadeobjeto, s.idempresa 
					FROM unidadeobjeto  
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)  
					JOIN sgsetor s ON(s.idsgsetor = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto = 'sgsetor')  
					JOIN objetovinculo ov ON(ov.idobjetovinc = s.idsgsetor and ov.tipoobjetovinc = 'sgsetor' and ov.tipoobjeto = 'sgdepartamento')   
					WHERE  ov.idobjeto = ?idsgdepartamento?
					AND u.status = 'ATIVO'
					?idempresa?
				)  as qry_distinct
				JOIN empresa e ON(e.idempresa = qry_distinct.idempresa)
				GROUP BY qry_distinct.idunidade
				ORDER BY e.sigla, qry_distinct.unidade";
	}

	public static function buscarUnidadesPorIdSgDepartamentoEIdEmpresa()
	{
		return "SELECT qry_distinct.idunidade, qry_distinct.unidade, qry_distinct.idobjeto, qry_distinct.tipoobjeto, qry_distinct.idunidadeobjeto, qry_distinct.idempresa
				FROM (
					SELECT u.idunidade, u.unidade, unidadeobjeto.idobjeto, 'sgdepartamento' as tipoobjeto,unidadeobjeto.idunidadeobjeto, sgdepartamento.idempresa
					FROM unidadeobjeto  
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)  
					JOIN sgdepartamento ON(sgdepartamento.idsgdepartamento = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto='sgdepartamento')   
					WHERE unidadeobjeto.idobjeto = ?idsgdepartamento?
					AND unidadeobjeto.tipoobjeto = 'sgdepartamento'     
					AND u.status = 'ATIVO'
					?idempresa?
					UNION 
					SELECT u.idunidade, u.unidade, unidadeobjeto.idobjeto, 'sgdepartamento',unidadeobjeto.idunidadeobjeto, s.idempresa 
					FROM unidadeobjeto  
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)  
					JOIN sgsetor s ON(s.idsgsetor = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto = 'sgsetor')  
					JOIN objetovinculo ov ON(ov.idobjetovinc = s.idsgsetor and ov.tipoobjetovinc = 'sgsetor' and ov.tipoobjeto = 'sgdepartamento')   
					WHERE  ov.idobjeto = ?idsgdepartamento?
					AND u.status = 'ATIVO'
					?idempresa?
				)  as qry_distinct
				JOIN empresa e ON(e.idempresa = qry_distinct.idempresa)
				GROUP BY qry_distinct.idunidade
				ORDER BY e.sigla, qry_distinct.unidade";
	}

	public static function buscarUnidadesPorIdSgsetorEIdEmpresa()
	{
		return "SELECT qry_distinct.idunidade, qry_distinct.unidade, qry_distinct.idobjeto, qry_distinct.tipoobjeto, qry_distinct.idunidadeobjeto, qry_distinct.idempresa
				FROM (
					SELECT u.idunidade, u.unidade, unidadeobjeto.idobjeto, 'sgsetor' as tipoobjeto,unidadeobjeto.idunidadeobjeto, e.sigla, sgsetor.idempresa
					FROM unidadeobjeto  
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)  
					JOIN sgsetor ON(sgsetor.idsgsetor = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto='sgsetor')   
					JOIN empresa e ON(e.idempresa = u.idempresa)
					WHERE sgsetor.status = 'ATIVO'
					AND unidadeobjeto.idobjeto = ?idsgsetor?
					AND unidadeobjeto.tipoobjeto = 'sgsetor'
					and unidadeobjeto.padrao='?padrao?'
					AND u.status = 'ATIVO'
					?idempresa?
				) as qry_distinct        
				GROUP BY qry_distinct.idunidade
				ORDER BY qry_distinct.sigla, qry_distinct.unidade";
	}

	

	public static function buscarUnidadesPorIdsgareaEIdEmpresa()
	{
		return "SELECT qry_distinct.idunidade, qry_distinct.unidade, qry_distinct.idobjeto, qry_distinct.tipoobjeto, qry_distinct.idunidadeobjeto, qry_distinct.idempresa
				FROM (
					SELECT u.idunidade, u.unidade, unidadeobjeto.idobjeto, 'sgarea' as tipoobjeto,unidadeobjeto.idunidadeobjeto, sgarea.idempresa 
					FROM unidadeobjeto  
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)   
					JOIN sgarea ON(sgarea.idsgarea = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto='sgarea')
					WHERE unidadeobjeto.idobjeto = ?idsgarea?
					AND unidadeobjeto.tipoobjeto = 'sgarea'
					AND u.status = 'ATIVO'
					?idempresa?
					UNION
					SELECT u.idunidade, u.unidade, uo.idobjeto, uo.tipoobjeto, uo.idunidadeobjeto, u.idempresa
					FROM (
						SELECT u.idunidade, u.unidade, unidadeobjeto.idobjeto, unidadeobjeto.tipoobjeto, unidadeobjeto.idunidadeobjeto, sgdep.idempresa
						FROM unidadeobjeto  
						JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)  
						JOIN sgdepartamento sgdep ON(sgdep.idsgdepartamento = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto = 'sgdepartamento')  
						JOIN objetovinculo ov ON(ov.idobjetovinc = sgdep.idsgdepartamento AND ov.tipoobjetovinc = 'sgdepartamento' AND ov.tipoobjeto = 'sgarea')   
						WHERE ov.idobjeto = ?idsgarea?
						?idempresa?
					) as qry  
					JOIN objetovinculo ov ON(ov.idobjeto = qry.idobjeto and ov.tipoobjeto = 'sgdepartamento' and ov.tipoobjetovinc = 'sgsetor')  
					JOIN unidadeobjeto uo ON(uo.idobjeto = ov.idobjetovinc and uo.tipoobjeto = 'sgsetor')  
					JOIN unidade u ON(u.idunidade = uo.idunidade)   
					WHERE 1
					AND u.status = 'ATIVO'
					?idempresa?
					UNION
					SELECT u.idunidade, u.unidade, unidadeobjeto.idobjeto, unidadeobjeto.tipoobjeto, unidadeobjeto.idunidadeobjeto, sgdep.idempresa 
					FROM unidadeobjeto  
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)  
					JOIN sgdepartamento sgdep ON(sgdep.idsgdepartamento = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto = 'sgdepartamento')  
					JOIN objetovinculo ov ON(ov.tipoobjeto = 'sgarea' AND ov.idobjetovinc = sgdep.idsgdepartamento AND ov.tipoobjetovinc = 'sgdepartamento')   
					WHERE ov.idobjeto = ?idsgarea?
					AND sgdep.status = 'ATIVO' 
					AND u.status = 'ATIVO'     
					?idempresa?
					GROUP BY unidadeobjeto.idunidade
				) as qry_distinct
				GROUP BY qry_distinct.idunidade;";
	}


	public static function buscarUnidadesPorIdsgareaEIdEmpresaPadrao()
	{
		return "SELECT qry_distinct.idunidade, qry_distinct.unidade, qry_distinct.idobjeto, qry_distinct.tipoobjeto, qry_distinct.idunidadeobjeto, qry_distinct.idempresa
				FROM (
					SELECT u.idunidade, u.unidade, unidadeobjeto.idobjeto, 'sgarea' as tipoobjeto,unidadeobjeto.idunidadeobjeto, u.idempresa 
					FROM unidadeobjeto  
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)   
					JOIN sgarea ON(sgarea.idsgarea = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto='sgarea')
					WHERE unidadeobjeto.idobjeto = ?idsgarea?
					AND unidadeobjeto.tipoobjeto = 'sgarea'
					AND u.status = 'ATIVO'
					and unidadeobjeto.padrao='Y'
					?idempresa?
					GROUP BY idunidade
				) as qry_distinct
				GROUP BY qry_distinct.idunidade;";
	}

	public static function buscarUnidadesPorIdsgconselhoEIdempresaPadrao()
	{
		return "SELECT qry_distinct.idunidade, qry_distinct.unidade, qry_distinct.idobjeto, qry_distinct.tipoobjeto, qry_distinct.idunidadeobjeto, qry_distinct.idempresa
				FROM (
					SELECT u.idunidade, u.unidade, unidadeobjeto.idobjeto, 'sgconselho' as tipoobjeto,unidadeobjeto.idunidadeobjeto, u.idempresa 
					FROM unidadeobjeto  
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)   
					JOIN sgconselho ON(sgconselho.idsgconselho = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto='sgconselho')
					WHERE unidadeobjeto.idobjeto = ?idsgconselho?
					AND unidadeobjeto.tipoobjeto = 'sgconselho'
					AND u.status = 'ATIVO'
					and unidadeobjeto.padrao='Y'
					?idempresa?
					GROUP BY idunidade
				) as qry_distinct
				GROUP BY qry_distinct.idunidade;";
	}

	public static function buscarUnidadesPorIdSgconselhoEIdEmpresa()
	{
		return "SELECT qry_distinct.idunidade, qry_distinct.unidade, qry_distinct.idobjeto, qry_distinct.tipoobjeto, qry_distinct.idunidadeobjeto, qry_distinct.idempresa
				FROM (
					-- CONSELHO
					SELECT u.idunidade, u.unidade, unidadeobjeto.idobjeto, 'sgconselho' as tipoobjeto,unidadeobjeto.idunidadeobjeto, u.idempresa
					FROM unidadeobjeto  
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)   
					JOIN sgconselho ON(sgconselho.idsgconselho = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto='sgconselho')
					WHERE unidadeobjeto.idobjeto = ?idobjeto?
					AND unidadeobjeto.tipoobjeto = '?tipoobjeto?'
				    AND unidadeobjeto.padrao='N'
					AND u.status = 'ATIVO'					
					?idempresa?
					UNION
					-- AREAS
					SELECT u.idunidade, u.unidade, unidadeobjeto.idobjeto, unidadeobjeto.tipoobjeto, unidadeobjeto.idunidadeobjeto, u.idempresa 
					FROM unidadeobjeto  
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)  
					JOIN sgarea a ON(a.idsgarea = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto = 'sgarea')  
					JOIN objetovinculo ov ON(ov.tipoobjeto = 'sgconselho' and ov.tipoobjetovinc = 'sgarea' AND ov.idobjetovinc = a.idsgarea)   
					WHERE ov.idobjeto = ?idobjeto? 
					AND a.status = 'ATIVO' 
					AND u.status = 'ATIVO'
					?idempresa?
					UNION
					-- DEPARTAMENTOS
					SELECT u.idunidade, u.unidade, a.idsgarea as idobjeto, unidadeobjeto.tipoobjeto, unidadeobjeto.idunidadeobjeto, u.idempresa 
					FROM objetovinculo ov
					JOIN sgarea a ON(ov.tipoobjeto = 'sgconselho' and ov.tipoobjetovinc = 'sgarea' AND ov.idobjetovinc = a.idsgarea)
					JOIN objetovinculo ov2 ON(ov.idobjetovinc = ov2.idobjeto and ov2.tipoobjeto = 'sgarea' AND ov2.tipoobjetovinc = 'sgdepartamento')
					LEFT JOIN unidadeobjeto ON(ov2.idobjetovinc = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto = 'sgdepartamento')  
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)  
					WHERE ov.idobjeto = ?idobjeto?
					AND a.status = 'ATIVO' 
					AND u.status = 'ATIVO'
					?idempresa?
					UNION
					-- SETORES
					SELECT  u.idunidade, u.unidade, unidadeobjeto.idobjeto, unidadeobjeto.tipoobjeto, unidadeobjeto.idunidadeobjeto, u.idempresa 
					FROM (
						SELECT u.idunidade, u.unidade, a.idsgarea as idobjeto, unidadeobjeto.tipoobjeto, unidadeobjeto.idunidadeobjeto, u.idempresa
						FROM sgarea a
						LEFT JOIN unidadeobjeto ON(a.idsgarea = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto = 'sgarea')
						LEFT JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)
						LEFT JOIN objetovinculo ov ON(ov.idobjetovinc = a.idsgarea AND ov.tipoobjetovinc = 'sgarea' AND ov.tipoobjeto = 'sgconselho')   
						WHERE ov.idobjeto = ?idobjeto?
						AND a.status = 'ATIVO' 
					) as qry  
					JOIN objetovinculo ov ON(ov.idobjeto = qry.idobjeto and ov.tipoobjeto = 'sgarea' and ov.tipoobjetovinc = 'sgdepartamento')  
					JOIN sgdepartamento sgdep ON(ov.idobjetovinc = sgdep.idsgdepartamento and ov.tipoobjetovinc = 'sgdepartamento')
					JOIN objetovinculo ov2 ON(ov2.idobjeto = ov.idobjetovinc AND ov2.tipoobjetovinc = 'sgsetor' AND ov2.tipoobjeto = 'sgdepartamento')  
					JOIN sgsetor s ON(s.idsgsetor = ov2.idobjetovinc)  
					JOIN unidadeobjeto ON(unidadeobjeto.idobjeto = s.idsgsetor and unidadeobjeto.tipoobjeto = 'sgsetor')
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)
					WHERE s.status = 'ATIVO'
					AND sgdep.status = 'ATIVO'
					AND u.status = 'ATIVO'
					?idempresa?
				) as qry_distinct
				GROUP BY qry_distinct.idunidade";
	}

	public static function buscarConselhosAreasDepsSetoresDisponiveisParaVinculoPorIdUnidade()
	{
		return "SELECT
					qry.id AS id,
					concat(e.sigla,' - ',qry.label) AS label,
					qry.idempresa,
					qry.tipo
				FROM (
					SELECT
						'sgconselho' as tipo,
						c.idsgconselho AS id,
						c.idempresa,
						c.conselho as label
					FROM sgconselho c
					WHERE c.status = 'ATIVO'
					UNION
					SELECT
						'sgarea' as tipo,
						a.idsgarea AS id,
						a.idempresa,
						a.area as label
					FROM sgarea a
					WHERE a.status = 'ATIVO'
					UNION
					SELECT
						'sgdepartamento' as tipo,
						sgdep.idsgdepartamento AS id,
						sgdep.idempresa,
						sgdep.departamento as label
					FROM sgdepartamento sgdep
					WHERE sgdep.status = 'ATIVO'
					UNION
					SELECT 
						'sgsetor' as tipo,
						s.idsgsetor AS id,
						s.idempresa,
						s.setor as label
					FROM sgsetor s
					WHERE s.status = 'ATIVO'
					UNION
					SELECT
						'pessoas' AS tipo,
						p.idpessoa AS id,
						p.idempresa,
						p.nome AS label
					FROM pessoa p
					WHERE p.status = 'ATIVO'
					AND idtipopessoa = 1
				) qry
				JOIN empresa e on (e.idempresa = qry.idempresa)
				AND NOT EXISTS(
					SELECT 1
					FROM unidadeobjeto
					WHERE idunidade = ?idunidade?
					AND tipoobjeto in('sgsetor', 'sgdepartamento', 'sgarea', 'sgconselho', 'pessoas')
					AND qry.idempresa = idempresa
					AND idobjeto = qry.id 
					AND tipoobjeto = qry.tipo
				)
				?where?
				?getidempresa?
				UNION
				SELECT 
					s.idsgsetor AS id,
					concat(e.sigla, ' - ', s.setor) as label,
					s.idempresa,
					'sgsetor' as tipo
				FROM unidade u
				JOIN unidadeobjeto uo on (uo.idunidade = u.idunidade AND uo.tipoobjeto = 'sgsetor' AND u.idunidade = ?idunidade?)	
				JOIN objetovinculo ov ON(ov.idobjetovinc = uo.idobjeto AND ov.tipoobjetovinc = 'sgsetor' AND ov.tipoobjeto = 'sgdepartamento')
				JOIN objetovinculo ov2 ON(ov.idobjeto = ov2.idobjeto AND ov2.tipoobjeto = 'sgdepartamento')
				JOIN sgsetor s on s.idsgsetor = ov2.idobjetovinc and ov2.tipoobjetovinc = 'sgsetor' AND s.status = 'ATIVO'
				JOIN pessoa p ON(p.idpessoa = uo.idobjeto AND uo.tipoobjeto = 'pessoas' AND p.idtipopessoa = 1)
				JOIN empresa e ON(e.idempresa = s.idempresa)
				WHERE 1
				AND NOT EXISTS(
					SELECT 1
					FROM unidadeobjeto
					where idobjeto = s.idsgsetor
					and tipoobjeto = 'sgsetor'
					and idunidade = ?idunidade?
				)
				?wheresetor?
				?getidempresa?";
	}

	public static function buscarGrupoUnidadePorTipoObjeto()
	{
		return "SELECT GROUP_CONCAT(DISTINCT(idunidade)) AS idunidade 
				  FROM unidadeobjeto WHERE idobjeto = ?idobjeto? AND tipoobjeto = '?tipoobjeto?'";
	}

	public static function buscarUnidadeObjetoLoteModuloPorIdnfItem()
	{
		return "SELECT l.idlote,
					   l.partida,
					   l.exercicio,
					   o.idobjeto,
					   l.qtdprod,
					   l.unlote,
					   l.status
				  FROM lote l JOIN unidadeobjeto o ON (o.tipoobjeto = 'modulo' AND o.idunidade = l.idunidade)
				  JOIN "._DBCARBON."._modulo m ON (m.modulo = o.idobjeto AND m.ready = 'FILTROS' AND m.modulotipo = 'lote' AND m.status = 'ATIVO')
				  ?condicaoWhere?";
	}

	public static function buscarUnidadeObjetoLoteModuloPorIdnf()
	{
		return "SELECT l.idlote,
					   l.partida,
					   l.exercicio,
					   o.idobjeto,
					   l.qtdprod,
					   l.unlote,
					   l.status,
					   l.fabricante,
					   DMA(l.vencimento) as vencimento,
					   ni.idnfitem,
					   ni.idnf,
					   s.rotulo
				  FROM lote l JOIN unidadeobjeto o ON (o.tipoobjeto = 'modulo' AND o.idunidade = l.idunidade)
				  JOIN fluxostatus fs ON fs.idfluxostatus = l.idfluxostatus
				  JOIN "._DBCARBON."._status s ON s.idstatus = fs.idstatus
				  JOIN nfitem ni ON l.idnfitem = ni.idnfitem
				  JOIN "._DBCARBON."._modulo m ON (m.modulo = o.idobjeto AND m.ready = 'FILTROS' AND m.modulotipo = 'lote' AND m.status = 'ATIVO')
				  ?condicaoWhere?";
	}

	public static function buscarUnidadeObjetoLoteModuloPorIdnfIdLote()
	{
		return "SELECT l.idlote,
					   l.partida,
					   l.exercicio,
					   o.idobjeto,
					   l.qtdprod,
					   l.unlote,
					   l.status,
					   l.fabricante,
					   DMA(l.vencimento) as vencimento,
					   ni.idnfitem,
					   ni.idnf,
					   s.rotulo                       
				  FROM lote l JOIN unidadeobjeto o ON (o.tipoobjeto = 'modulo' AND o.idunidade = l.idunidade)
                  JOIN lotecons lc ON lc.idlote = l.idlote
                  JOIN nfitem ni ON ni.idnfitem = lc.idobjeto AND lc.tipoobjeto = 'nfitem' 
				  JOIN fluxostatus fs ON fs.idfluxostatus = l.idfluxostatus
				  JOIN "._DBCARBON."._status s ON s.idstatus = fs.idstatus				  
				  JOIN "._DBCARBON."._modulo m ON (m.modulo = o.idobjeto AND m.ready = 'FILTROS' AND m.modulotipo = 'lote' AND m.status = 'ATIVO')
				  ?condicaoWhere?";
	}

	public static function buscarUnidadeObjetoPorTipoObjetoEIdUnidade()
	{
		return "SELECT o.idobjeto
				  FROM unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE)
				  JOIN "._DBCARBON."._modulo m ON (m.modulo = o.idobjeto AND m.modulotipo = '?modulotipo?')
				 WHERE o.tipoobjeto = '?tipoobjeto?'
				   AND m.status = 'ATIVO'
				   AND o.idunidade = ?idunidade?";
	}

	public static function buscarUnidadePorIdObjetoETipoObjeto()
	{
		return "SELECT u.*
				FROM unidadeobjeto uo
				JOIN unidade u ON(u.idunidade = uo.idunidade and u.status = 'ATIVO')
				WHERE uo.idobjeto = ?idobjeto?
				AND uo.tipoobjeto = '?tipoobjeto?'
				AND uo.padrao='Y'
				?getidempresa?
				ORDER BY u.idempresa ASC";
	}

	public static function verificarSeExisteVinculoComUnidadePorIdObjetoETipoObjeto()
	{
		return "SELECT 1
				FROM unidadeobjeto uo
				JOIN unidade u on(u.idunidade = uo.idunidade)
				WHERE uo.idobjeto = ?idobjeto?
				AND uo.tipoobjeto = '?tipoobjeto?'
				AND uo.padrao='Y'
				AND u.status = 'ATIVO'";
	}

	public static function buscarIdFluxoStatusPorUnidade() {
		return "SELECT idfluxostatus
			FROM unidadeobjeto uo 
				JOIN "._DBCARBON."._modulo m ON m.modulo = uo.idobjeto 
					AND m.modulotipo = '?modulotipo?'
				JOIN fluxo f ON f.modulo = uo.idobjeto 
					AND f.status = 'ATIVO'
				JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo
				JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus 
					AND s.statustipo = '?statustipo?' 
			WHERE idunidade = '?idunidade?' 
				AND uo.tipoobjeto = 'modulo';";
	}

	public static function buscarUnidadeObjeto() 
	{
		return "SELECT p.*
				  FROM unidadeobjeto p JOIN unidade u ON (u.idunidade = p.idunidade AND u.idempresa = ?idempresa?)
				 WHERE p.idobjeto = ?idobjeto?
				   AND p.tipoobjeto = '?tipoobjeto?'";
	}

	public static function buscarUnidadeObjetoPorModuloTipoEIdUnidade() 
	{
		return "SELECT idobjeto
				  FROM unidadeobjeto o JOIN "._DBCARBON."._modulo m ON m.modulo = o.idobjeto 
				   AND o.tipoobjeto = '?tipoobjeto?'
				   AND m.modulotipo = '?modulotipo?'
				   AND o.idunidade = ?idunidade?";
	}

	public static function buscarUnidadeObjetoPorModuloTipoEIdUnidadeEReady() 
	{
		return "SELECT o.idobjeto
				  FROM unidadeobjeto o JOIN "._DBCARBON."._modulo m ON (m.modulo = o.idobjeto AND m.status = 'ATIVO' AND m.ready = 'FILTROS' AND m.modulotipo = '?modulotipo?')
				 WHERE o.tipoobjeto = '?tipoobjeto?'
			 	   AND o.idunidade = ?idunidade?";
	}


	public static function buscarUnidadeVinculadaIdSgDepartamentoSetor()
	{
		return "SELECT u.idunidade, u.unidade
					FROM unidadeobjeto  
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)  
					JOIN sgsetor s ON(s.idsgsetor = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto = 'sgsetor')
					WHERE s.idsgsetor =?idsgsetor?				
					AND u.status = 'ATIVO'";
	}

	public static function buscarUnidadeVinculadaIdSgDepartamento(){
		return "SELECT unidadeobjeto.idunidadeobjeto
					FROM unidadeobjeto  
					JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)  
					JOIN sgdepartamento ON(sgdepartamento.idsgdepartamento = unidadeobjeto.idobjeto AND unidadeobjeto.tipoobjeto='sgdepartamento')   
					WHERE unidadeobjeto.idobjeto = ?idsgdepartamento?
					AND unidadeobjeto.tipoobjeto = 'sgdepartamento'  
					AND unidadeobjeto.padrao ='N'   
					AND u.idunidade=?idunidade?
					AND u.status = 'ATIVO'";
	}


	public static function deletaUnidadeobjeto(){
	
        return "DELETE
                FROM unidadeobjeto
                WHERE idunidadeobjeto = ?idunidadeobjeto?";
    
	}

	public static function buscarUnidadesTabelaModulo(){
		return "SELECT GROUP_CONCAT(uo.idunidade) as idunidade, mr.tabde
				  FROM unidadeobjeto uo JOIN carbonnovo._modulorelac mr ON mr.modulo = uo.idobjeto
				 WHERE uo.idobjeto = '?modulo?'
				   AND uo.tipoobjeto = 'modulo'";
	}

}
?>