<?php
require_once __DIR__."/_iquery.php";
class _LpQuery  implements DefaultQuery{
	public static $table = _DBCARBON.'_lp';
	public static $pk = 'idlp';

	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
			'table' => self::$table,
			'pk' => self::$pk
		]);
	}

	public static function buscarLpsDisponiveisParaVinculoPorIdSgDepartamento()
	{
		return "SELECT p.idlp, CONCAT(e.sigla,' - ',p.descricao) as descricao
				FROM " . _DBCARBON . "._lp p JOIN empresa e on (e.idempresa = p.idempresa)
				WHERE p.status='ATIVO'
				AND p.idempresa =?idempresa?
				AND p.idlp NOT IN (
					SELECT idlp 
					FROM lpobjeto po 
					WHERE po.idobjeto = '?idsgdepartamento?'
					AND po.tipoobjeto = 'sgdepartamento'
				) order by descricao";
	}

	public static function buscarLpsDisponiveisParaVinculoPorIdSgSetorEGetIdEmpresa()
	{
		return "SELECT 
					p.idlp, CONCAT(e.sigla,' - ',p.descricao) as descricao
				FROM "._DBCARBON."._lp p
				JOIN empresa e on (e.idempresa = p.idempresa)
				WHERE 1
				?getidempresa?
				AND p.status='ATIVO'
				AND p.idlp NOT IN (
					SELECT po.idlp
					FROM lpobjeto po 
					WHERE po.idobjeto = '?idsgsetor?'
					AND po.tipoobjeto = 'sgsetor'
				) order by descricao";
	}

	public static function buscarLpsDisponiveisParaVinculoPorIdObjetoTipoObjetoEGetIdEmpresa()
	{
		return "SELECT lp.*, CONCAT(e.sigla, ' - ', lp.descricao) as lp
				FROM carbonnovo._lp lp
				JOIN empresa e ON(lp.idempresa = e.idempresa)
				WHERE 1
				?getidempresa?
				AND lp.status='ATIVO'
				AND lp.idlp NOT IN (SELECT idlp FROM lpobjeto WHERE idobjeto = '?idobjeto?' AND tipoobjeto = '?tipoobjeto?')
				ORDER BY e.sigla, lp.descricao";
	}

	public static function buscarLPsPorIdLprupo(){
		return "SELECT 
					l.idlp,
					e.empresa,
					e.idempresa,
					l.status
				FROM carbonnovo._lpobjeto lo
					JOIN carbonnovo._lp l ON (lo.idlp = l.idlp)
					JOIN empresa e ON (l.idempresa = e.idempresa)
				WHERE lo.tipoobjeto = 'lpgrupo'
					AND lo.idobjeto = ?idlpgrupo?
					AND EXISTS (select 1 from objempresa oe where oe.idobjeto = ?idpessoa? and oe.objeto = 'pessoa' and oe.empresa = e.idempresa)
				ORDER BY l.status asc";
	}

	public static function jsonEmpresasNaoVinculadasALp(){
		return "SELECT 
		e.idempresa,
		CONCAT(e.sigla, ' - ', e.nomefantasia) AS empresa
	FROM
		empresa e
	WHERE
		e.status = 'ATIVO'
			AND e.idempresa != ?idempresa?
			AND NOT EXISTS( SELECT 
				1
			FROM
				carbonnovo._lpobjeto l
			WHERE
				l.tipoobjeto = 'empresa'
					AND l.idobjeto = e.idempresa
					AND l.idlp = ?idlp? )";
	}

	public static function listaSnippets(){
		return "SELECT s.idsnippet,
						s.snippet,
						s.cssicone,
						s.tipo,
						o.idlpobjeto,
						o.criadopor,
						o.criadoem
					FROM carbonnovo._snippet s
						LEFT JOIN carbonnovo._lpobjeto o ON (o.idlp=?idlp? AND o.tipoobjeto='_snippet' AND o.idobjeto=s.idsnippet)
					WHERE s.status='ATIVO' 
					?getidempresa?";
	}

	public static function buscarPessoasSetorDepartamentoArea(){
		return "SELECT IFNULL(p.nomecurto, p.nome) AS pessoa,
						a.?alias? AS nome,
						p.idpessoa,
						e.sigla
				FROM
					?table? a
						LEFT JOIN
					pessoaobjeto po ON (a.id?table? = po.idobjeto AND po.tipoobjeto = '?table?')
						LEFT JOIN
					pessoa p ON (po.idpessoa = p.idpessoa)
						LEFT JOIN
					empresa e on (e.idempresa = p.idempresa) 
				WHERE
					a.id?table? = ?id?";
	}

	public static function buscarPessoasSetorDepartamentoAreaComUnion(){
		return "SELECT IFNULL(p.nomecurto, p.nome) AS pessoa,
						p.idpessoa,
						a.?alias? AS nome,
						e.sigla
				FROM
					?table? a
						LEFT JOIN
					pessoaobjeto po ON (a.id?table? = po.idobjeto AND po.tipoobjeto = '?table?')
						LEFT JOIN
					pessoa p ON (po.idpessoa = p.idpessoa)
						LEFT JOIN
					empresa e on (e.idempresa = p.idempresa) 
				WHERE
					a.id?table? = ?id?
				UNION ALL
				SELECT IFNULL(p.nomecurto, p.nome) AS pessoa,
						p.idpessoa,
						a.?alias? AS nome,
						e.sigla
				FROM
					?table? a, pessoa p 
					LEFT JOIN empresa e on (e.idempresa = p.idempresa) 
				WHERE
					a.id?table? = ?id?
					AND p.status='ATIVO'
					AND p.idtipopessoa = a.idtipopessoa";
	}

	public static function buscarPessoasSetorDepartamentoAreaConselho(){
		return "SELECT 
					a.idpessoa AS idobjeto,
					CONCAT(e.sigla, ' - ', a.nomecurto) AS rot,
					'pessoa' AS tipoobj,
					1 AS ord
				FROM
					pessoa a
						JOIN
					empresa e ON (e.idempresa = a.idempresa)
				WHERE
					a.status = 'ATIVO'
						AND a.idtipopessoa = 1
						AND NOT EXISTS( SELECT 
							1
						FROM
							carbonnovo._lp l
								JOIN
							lpobjeto v ON (l.idlp = v.idlp)
						WHERE
							l.idlp = ?idlp?
								AND v.tipoobjeto = 'pessoa'
								AND a.idpessoa = v.idobjeto) 
				UNION SELECT 
					s.idsgsetor AS idobjeto,
					CONCAT(e.sigla, ' - ', s.setor) AS rot,
					'sgsetor' AS tipoobj,
					2 AS ord
				FROM
					sgsetor s
						JOIN
					empresa e ON (e.idempresa = s.idempresa)
				WHERE
					s.status = 'ATIVO'
						AND NOT EXISTS( SELECT 
							1
						FROM
							carbonnovo._lp l
								JOIN
							lpobjeto v ON (l.idlp = v.idlp)
						WHERE
							l.idlp = ?idlp?
								AND v.tipoobjeto = 'sgsetor'
								AND s.idsgsetor = v.idobjeto) 
				UNION SELECT 
					d.idsgdepartamento AS idobjeto,
					CONCAT(e.sigla, ' - ', d.departamento) AS rot,
					'sgdepartamento' AS tipoobj,
					3 AS ord
				FROM
					sgdepartamento d
						JOIN
					empresa e ON (e.idempresa = d.idempresa)
				WHERE
					d.status = 'ATIVO'
						AND NOT EXISTS( SELECT 
							1
						FROM
							carbonnovo._lp l
								JOIN
							lpobjeto v ON (l.idlp = v.idlp)
						WHERE
							l.idlp = ?idlp?
								AND v.tipoobjeto = 'sgdepartamento'
								AND d.idsgdepartamento = v.idobjeto) 
				UNION SELECT 
					a.idsgarea AS idobjeto,
					CONCAT(e.sigla, ' - ', a.area) AS rot,
					'sgarea' AS tipoobj,
					4 AS ord
				FROM
					sgarea a
						JOIN
					empresa e ON (e.idempresa = a.idempresa)
				WHERE
					a.status = 'ATIVO'
						AND NOT EXISTS( SELECT 
							1
						FROM
							carbonnovo._lp l
								JOIN
							lpobjeto v ON (l.idlp = v.idlp)
						WHERE
							l.idlp = ?idlp?
								AND v.tipoobjeto = 'sgarea'
								AND a.idsgarea = v.idobjeto)
				UNION SELECT 
					s.idsgconselho AS idobjeto,
					CONCAT(e.sigla, ' - ', s.conselho) AS rot,
					'sgconselho' AS tipoobj,
					5 AS ord
				FROM
					sgconselho s
						JOIN
					empresa e ON (e.idempresa = s.idempresa)
				WHERE
					s.status = 'ATIVO'
						AND NOT EXISTS( SELECT 
							1
						FROM
							carbonnovo._lp l
								JOIN
							lpobjeto v ON (l.idlp = v.idlp)
						WHERE
							l.idlp = ?idlp?
								AND v.tipoobjeto = 'sgconselho'
								AND s.idsgconselho = v.idobjeto)
				ORDER BY ord , rot ASC";
	}

	public static function buscarModulosPadrao(){
		return "SELECT
					m.*,
					'' AS idlpmodulo,
					'' AS ordlp,
					'w' AS permissao
				FROM carbonnovo._modulo m 
				WHERE tipo IN ('BTPR') AND status = 'ATIVO'
				ORDER BY  modulo";
	}

	public static function buscarModulosDisponiveis(){
		return "SELECT * FROM (
							SELECT 
								m.rotulomenu, m.tipo, m.cssicone, m.ord
							FROM carbonnovo._modulo m
								JOIN unidadeobjeto o
								JOIN unidade u
								WHERE m.modulopar =''
								AND m.status = 'ATIVO'
								AND m.tipo IN ('DROP','LINK','LINKHOME','MODVINC','BTINV','POPUP','SNIPPET')
								AND m.modulo = o.idobjeto 
								AND o.tipoobjeto='modulo'
								AND u.idunidade = o.idunidade
								?getidempresa?
								AND NOT EXISTS(
									SELECT 1 FROM carbonnovo._lpmodulo lm WHERE lm.modulo = m.modulo AND lm.idlp = '?idLp?'
								)
							union 
							SELECT m2.rotulomenu, m2.tipo, m2.cssicone, m2.ord FROM carbonnovo._modulo m2
								WHERE m2.modulopar ='' AND m2.tipo IN ('DROP','LINK','LINKHOME','MODVINC','BTINV','POPUP','SNIPPET') AND m2.status = 'ATIVO'
								AND NOT EXISTS(
									SELECT 1 FROM carbonnovo._lpmodulo lm 
									WHERE lm.modulo = m2.modulo 
									AND lm.idlp = '?idLp?'
								)
								AND NOT EXISTS(
									SELECT 1 FROM unidadeobjeto o 
									WHERE m2.modulo = o.idobjeto 
									AND o.tipoobjeto='modulo' 
									AND o.idunidade IS NOT NULL
								)
								AND  EXISTS (
									SELECT 1 FROM objempresa oe WHERE oe.idobjeto = m2.idmodulo AND oe.objeto='modulo' 
								)
							) aa ORDER BY ord, rotulomenu";
	}

	public static function buscarModulosSelecionados(){
		return "SELECT * FROM (
								SELECT
									m.idmodulo
									,m.modulo
									,m.cssicone
									,m.rotulomenu
									,m.tipo
									,m.status
									,m.botaoassinar
									,lm.permissao
									,lm.solassinatura
									,lm.idlpmodulo
									,IFNULL(m.ord,0) AS ord
									,e.sigla,
									case when m.modulopar like 'snippet%' then 1
									else 2 end as divisao
								FROM carbonnovo._modulo m 
								LEFT  JOIN carbonnovo._lpmodulo lm ON (lm.modulo = m.modulo AND lm.idlp = '?idlp?')
								LEFT JOIN carbonnovo._lp l ON l.idlp = lm.idlp
								LEFT JOIN empresa e ON e.idempresa = l.idempresa
								LEFT JOIN unidadeobjeto o ON m.modulo = o.idobjeto AND o.tipoobjeto='modulo'
								LEFT JOIN unidade u ON u.idunidade = o.idunidade
								JOIN objempresa ob ON ob.idobjeto = m.idmodulo AND ob.objeto = 'modulo' AND ob.empresa = ?idempresa?
								WHERE modulopar in ('snippetprincipal','snippetsecundario','snippetacao','')
									AND m.status = 'ATIVO'
										AND tipo IN ('DROP','LINK','LINKHOME','MODVINC','BTINV','POPUP','SNIPPET')
							UNION 
								SELECT
									m2.idmodulo
									,m2.modulo
									,m2.cssicone
									,m2.rotulomenu
									,m2.tipo
									,m2.status
									,m2.botaoassinar
									,lm2.permissao
									,lm2.solassinatura
									,lm2.idlpmodulo
									,IFNULL(m2.ord,0) AS ord
									,e.sigla
									,case when m2.modulopar like 'snippet%' then 1
									else 2 end as divisao
								FROM carbonnovo._modulo m2 
								LEFT JOIN carbonnovo._lpmodulo lm2 ON (lm2.modulo = m2.modulo AND lm2.idlp = '?idlp?')
								LEFT JOIN carbonnovo._lp l ON l.idlp = lm2.idlp
								LEFT JOIN empresa e ON e.idempresa = l.idempresa
								JOIN objempresa ob ON ob.idobjeto = m2.idmodulo AND ob.objeto = 'modulo' AND ob.empresa = ?idempresa?
								WHERE modulopar in ('snippetprincipal','snippetsecundario','snippetacao','') AND tipo IN ('DROP','LINK','LINKHOME','MODVINC','BTINV','POPUP','SNIPPET') AND m2.status = 'ATIVO'
								AND NOT EXISTS(
												SELECT 1 FROM unidadeobjeto o 
												WHERE m2.modulo = o.idobjeto 
												AND o.tipoobjeto='modulo' 
												AND o.idunidade IS NOT NULL
											)
						) aa
							ORDER BY aa.divisao,aa.ord,aa.tipo";
	}

	public static function buscarModulosSelecionados2(){
		// Consulta do Euzébio
		return "WITH RECURSIVE modulo_hierarquia AS (
				-- Base da recursão: seleciona os módulos principais
				SELECT
					m.idmodulo,
					m.modulo,
					m.cssicone,
					m.rotulomenu,
					m.tipo,
					CAST(NULL AS CHAR(255)) AS reptipo,
					m.status,
					lm.permissao,
					m.botaoassinar,
					lm.solassinatura,
					lm.idlpmodulo,
					IFNULL(m.ord, 0) AS ord,
					e.sigla,
					CASE 
						WHEN m.modulopar LIKE 'snippet%' THEN 'snippets'
						ELSE 'modulos'
					END AS divisao,
					CASE 
						WHEN m.modulopar IN ('snippetprincipal','snippetsecundario','snippetacao','') THEN ''
						ELSE m.modulopar
					END as modulopar,
					0 AS nivel, -- nível 0 para os módulos principais
					m.rotulomenu AS raiz_modulo, -- marca o módulo raiz (nível 0)
					concat(CASE WHEN m.modulopar LIKE 'snippet%' THEN 1 ELSE 2 END, '-',  IFNULL(m.ord, 0), '-', m.tipo, '-', m.rotulomenu ) AS chave_ordenacao,
					0 as idlprep,
                    'N' as 'flgunidade',
					'N' as 'flgidpessoa',
					'N' as 'flgcontaitem',
                    0 as 'btnrep',
					0 as 'btnreporg',
					0 as 'btnrepcti'
				FROM carbonnovo._modulo m
				JOIN objempresa ob ON ob.idobjeto = m.idmodulo 
								AND ob.objeto = 'modulo' 
								AND ob.empresa = ?idempresa?
				LEFT JOIN carbonnovo._lpmodulo lm ON (lm.modulo = m.modulo 
													AND lm.idlp = ?idlp?)
				LEFT JOIN carbonnovo._lp l ON l.idlp = lm.idlp
				LEFT JOIN empresa e ON e.idempresa = l.idempresa
				WHERE m.status = 'ATIVO'
				AND m.tipo IN ('DROP','LINK','LINKHOME','MODVINC','BTINV','POPUP','SNIPPET')
				AND m.modulopar IN ('snippetprincipal','snippetsecundario','snippetacao','')
				UNION ALL
				-- Recursão: busca os filhos e subfilhos dos módulos anteriores
				SELECT
					m.idmodulo,
					m.modulo,
					m.cssicone,
					m.rotulomenu,
					m.tipo,
					'',
					m.status,
					lm.permissao,
					m.botaoassinar,
					lm.solassinatura,
					lm.idlpmodulo,
					IFNULL(m.ord, 0) AS ord,
					e.sigla,
					  mh.divisao,
					m.modulopar,
					mh.nivel + 1 AS nivel, -- incrementa o nível da hierarquia
					mh.raiz_modulo, -- propaga o módulo raiz para manter a ligação hierárquica
					concat(mh.chave_ordenacao, '-', mh.nivel + 1, '-',  IFNULL(m.ord, 0), '-', m.tipo, '-', m.rotulomenu  ) AS chave_ordenacao,
					0 as idlprep,
                    'N' as 'flgunidade',
					'N' as 'flgidpessoa',
					'N' as 'flgcontaitem',
                    0 as 'btnrep',
					0 as 'btnreporg',
					0 as 'btnrepcti'
				FROM carbonnovo._modulo m
				JOIN objempresa ob ON ob.idobjeto = m.idmodulo 
								AND ob.objeto = 'modulo'
								AND ob.empresa = ?idempresa?
				LEFT JOIN carbonnovo._lpmodulo lm ON (lm.modulo = m.modulo 
													AND lm.idlp = ?idlp?)
				LEFT JOIN carbonnovo._lp l ON l.idlp = lm.idlp
				LEFT JOIN empresa e ON e.idempresa = l.idempresa
				JOIN modulo_hierarquia mh ON m.modulopar = mh.modulo
				WHERE m.status = 'ATIVO'
				AND m.tipo IN ('LINK','LINKHOME','MODVINC','BTINV','POPUP','SNIPPET')
				UNION ALL
				-- Novo nível de recursão: seleciona os filhos com base na coluna mr.modulo
				SELECT
					re.idrep AS idmodulo,
					re.idrep AS modulo,
					'' AS cssicone,
					re.rep AS rotulomenu,
					'REPTIPO' AS tipo,
					rp.reptipo ,
					'ATIVO' AS status,
					NULL AS permissao,
					null as botaoassinar,
					NULL AS solassinatura,
					NULL AS idlpmodulo,
					0 AS ord,
					NULL AS sigla,
					mh.divisao,
					mr.modulo AS modulopar,
					mh.nivel + 1 AS nivel,
					mh.raiz_modulo,
					CONCAT(mh.chave_ordenacao, '-', mh.nivel + 1, '-', IFNULL(re.idrep, 0), '-', 'REP', '-',re.rep) AS chave_ordenacao,
					r.idlprep,
					r.flgunidade,
					r.flgidpessoa,
					r.flgcontaitem,
                    rc.idrep as 'btnrep',
					rc1.idrep as 'btnreporg',
					rc2.idrep as 'btnrepcti'
				FROM carbonnovo._rep re
				JOIN carbonnovo._reptipo rp ON rp.idreptipo = re.idreptipo
				JOIN carbonnovo._modulorep mr ON mr.idrep = re.idrep
				LEFT JOIN carbonnovo._lprep r ON mr.idrep = r.idrep AND r.idlp = ?idlp?
				LEFT JOIN carbonnovo._repcol rc ON rc.idrep = re.idrep AND rc.col = 'idunidade'
				LEFT JOIN carbonnovo._repcol rc1 ON rc1.idrep = re.idrep AND rc1.col = 'idpessoa'
				LEFT JOIN carbonnovo._repcol rc2 ON rc2.idrep = re.idrep AND rc2.col = 'idcontaitem'
				JOIN modulo_hierarquia mh ON mr.modulo = mh.modulo
			)
			SELECT x.*
			FROM modulo_hierarquia x
			ORDER BY chave_ordenacao, tipo, ord;";
	}
	
	public static function buscarModulosFilhos(){
		return "SELECT * FROM (
					SELECT
						m.*,
						lm.idlpmodulo,
						lm.ord as ordlp,
						lm.permissao,
						lm.solassinatura,
						e.sigla
					FROM carbonnovo._modulo m 
						LEFT JOIN carbonnovo._lpmodulo lm ON (lm.modulo = m.modulo AND lm.idlp = '?idlp?')
						LEFT JOIN carbonnovo._lp l ON l.idlp = lm.idlp
						LEFT JOIN empresa e ON e.idempresa = l.idempresa
						JOIN unidadeobjeto o
						JOIN unidade u
					WHERE modulopar ='?inmod?' 
						AND tipo IN ('LINK','LINKHOME','MODVINC','BTINV','POPUP','SNIPPET')
						AND m.status = 'ATIVO'
						AND m.modulo = o.idobjeto 
						AND o.tipoobjeto='modulo'
						AND u.idunidade = o.idunidade
						?getidempresa?
						AND  EXISTS (
							SELECT 1 FROM objempresa oe WHERE oe.idobjeto = m.idmodulo AND oe.objeto='modulo' AND oe.empresa = ?idempresa? 

						)
						
					UNION
					SELECT
						m.*,
						lm.idlpmodulo,
						lm.ord as ordlp,
						lm.permissao,
						lm.solassinatura,
						e.sigla
						
					FROM carbonnovo._modulo m 
						LEFT JOIN carbonnovo._lpmodulo lm ON (lm.modulo = m.modulo AND lm.idlp = '?idlp?')
						LEFT JOIN carbonnovo._lp l ON l.idlp = lm.idlp
						LEFT JOIN empresa e ON e.idempresa = l.idempresa
					WHERE modulopar ='?inmod?' 
						AND tipo IN ('LINK','LINKHOME','MODVINC','BTINV','POPUP','SNIPPET')
						AND m.status = 'ATIVO'
						AND NOT EXISTS(
										SELECT 1 FROM unidadeobjeto o 
										WHERE m.modulo = o.idobjeto 
										AND o.tipoobjeto='modulo' 
										AND o.idunidade is not null
									)
						AND  EXISTS (
							SELECT 1 FROM objempresa oe WHERE oe.idobjeto = m.idmodulo AND oe.objeto='modulo' AND oe.empresa = ?idempresa? 
						)
				) aa
				ORDER BY aa.ord, aa.modulo";
	}
	public static function buscarModulosFilhos2(){
		return "SELECT * FROM (
					SELECT
						m.*,
						lm.idlpmodulo,
						lm.ord as ordlp,
						lm.permissao,
						lm.solassinatura,
						e.sigla
					FROM carbonnovo._modulo m 
						LEFT JOIN carbonnovo._lpmodulo lm ON (lm.modulo = m.modulo AND lm.idlp = '?idlp?')
						LEFT JOIN carbonnovo._lp l ON l.idlp = lm.idlp
						LEFT JOIN empresa e ON e.idempresa = l.idempresa
						JOIN unidadeobjeto o
						JOIN unidade u
					WHERE tipo IN ('LINK','LINKHOME','MODVINC','BTINV','POPUP','SNIPPET')
						AND m.status = 'ATIVO'
						AND m.modulo = o.idobjeto 
						AND o.tipoobjeto='modulo'
						AND u.idunidade = o.idunidade
						?getidempresa?
						AND  EXISTS (
							SELECT 1 FROM objempresa oe WHERE oe.idobjeto = m.idmodulo AND oe.objeto='modulo' AND oe.empresa = ?idempresa? 

						)
				UNION
					SELECT
						m.*,
						lm.idlpmodulo,
						lm.ord as ordlp,
						lm.permissao,
						lm.solassinatura,
						e.sigla
						
					FROM carbonnovo._modulo m 
						LEFT JOIN carbonnovo._lpmodulo lm ON (lm.modulo = m.modulo AND lm.idlp = '?idlp?')
						LEFT JOIN carbonnovo._lp l ON l.idlp = lm.idlp
						LEFT JOIN empresa e ON e.idempresa = l.idempresa
					WHERE
						tipo IN ('LINK','LINKHOME','MODVINC','BTINV','POPUP','SNIPPET')
						AND m.status = 'ATIVO'
						AND NOT EXISTS(
										SELECT 1 FROM unidadeobjeto o 
										WHERE m.modulo = o.idobjeto 
										AND o.tipoobjeto='modulo' 
										AND o.idunidade is not null
									)
						AND  EXISTS (
							SELECT 1 FROM objempresa oe WHERE oe.idobjeto = m.idmodulo AND oe.objeto='modulo' AND oe.empresa = ?idempresa? 
						)
				) aa
				ORDER BY aa.ord, aa.modulo";
	}


	public static function buscarModulosFilhosDosFilhos(){
		return "SELECT * FROM (
					SELECT
						m.*,
						lm.idlpmodulo,
						lm.ord as ordlp,
						lm.permissao,
						lm.solassinatura,
						e.sigla
					FROM carbonnovo._modulo m 
						LEFT JOIN carbonnovo._lpmodulo lm ON (lm.modulo = m.modulo AND lm.idlp = '?idlp?')
						LEFT JOIN carbonnovo._lp l ON l.idlp = lm.idlp
						LEFT JOIN empresa e ON e.idempresa = l.idempresa
					WHERE modulopar ='?inmod?'
						AND tipo IN ('BTINV')
						AND NOT EXISTS(
										SELECT 1 FROM unidadeobjeto o 
										WHERE m.modulo = o.idobjeto 
										AND o.tipoobjeto='modulo' 
										AND o.idunidade is not null
									)
						AND  EXISTS (
							SELECT 1 FROM objempresa oe WHERE oe.idobjeto = m.idmodulo AND oe.objeto='modulo' AND oe.empresa = ?idempresa? 
						)
				) aa        
					ORDER BY aa.ordlp, aa.modulo";
	}
	public static function buscarModulosFilhosDosFilhos2(){
		return "SELECT * FROM (
					SELECT
						m.*,
						lm.idlpmodulo,
						lm.ord as ordlp,
						lm.permissao,
						lm.solassinatura,
						e.sigla
					FROM carbonnovo._modulo m 
						LEFT JOIN carbonnovo._lpmodulo lm ON (lm.modulo = m.modulo AND lm.idlp = '?idlp?')
						LEFT JOIN carbonnovo._lp l ON l.idlp = lm.idlp
						LEFT JOIN empresa e ON e.idempresa = l.idempresa
					WHERE
						tipo IN ('BTINV')
						AND NOT EXISTS(
										SELECT 1 FROM unidadeobjeto o 
										WHERE m.modulo = o.idobjeto 
										AND o.tipoobjeto='modulo' 
										AND o.idunidade is not null
									)
						AND  EXISTS (
							SELECT 1 FROM objempresa oe WHERE oe.idobjeto = m.idmodulo AND oe.objeto='modulo' AND oe.empresa = ?idempresa? 
						)
				) aa        
					ORDER BY aa.ordlp, aa.modulo";
	}

	public static function buscarRepsDoModulo(){
		return "SELECT * FROM (
					SELECT rp.reptipo,
							rp.idreptipo,
							r.idlprep,
							r.flgunidade,
							r.flgidpessoa,
							r.flgcontaitem,
							re.rep,
							mr.modulo,
							re.idrep,
							rc.idrep as 'btnrep',
							rc1.idrep as 'btnreporg',
							rc2.idrep as 'btnrepcti'
					FROM
						carbonnovo._rep re 
						JOIN carbonnovo._reptipo rp on rp.idreptipo = re.idreptipo
						JOIN carbonnovo._modulorep mr ON mr.idrep = re.idrep
						JOIN carbonnovo._lpmodulo lm ON mr.modulo = lm.modulo
						JOIN carbonnovo._lp l ON l.idlp = lm.idlp AND l.idlp = ?idLp?
						LEFT JOIN carbonnovo._lprep r ON  l.idlp = r.idlp AND mr.idrep = r.idrep 
						LEFT JOIN carbonnovo._repcol rc ON rc.idrep = re.idrep AND rc.col = 'idunidade'
						LEFT JOIN carbonnovo._repcol rc1 ON rc1.idrep = re.idrep AND rc1.col = 'idpessoa'
						LEFT JOIN carbonnovo._repcol rc2 ON rc2.idrep = re.idrep AND rc2.col = 'idcontaitem'
					WHERE mr.modulo = '?inmod?'
					ORDER BY rp.idreptipo,re.rep
				) aa";
	}
	public static function buscarRepsDoModulo2(){
		return "
				   SELECT
							re.idrep
							,re.rep
							,rp.reptipo
							,rp.idreptipo
							,mr.modulo
							,r.idlprep
							,r.flgunidade
							,r.flgidpessoa
							,r.flgcontaitem
							,rc.idrep as 'btnrep'
							,rc1.idrep as 'btnreporg'
							,rc2.idrep as 'btnrepcti'
					FROM
						carbonnovo._rep re
						JOIN carbonnovo._reptipo rp on rp.idreptipo = re.idreptipo
						JOIN carbonnovo._modulorep mr ON mr.idrep = re.idrep
						LEFT JOIN carbonnovo._lprep r ON mr.idrep = r.idrep and r.idlp = ?idLp?
						LEFT JOIN carbonnovo._repcol rc ON rc.idrep = re.idrep AND rc.col = 'idunidade'
						LEFT JOIN carbonnovo._repcol rc1 ON rc1.idrep = re.idrep AND rc1.col = 'idpessoa'
						LEFT JOIN carbonnovo._repcol rc2 ON rc2.idrep = re.idrep AND rc2.col = 'idcontaitem'
					ORDER BY rp.idreptipo,re.rep";
	}

	public static function buscarLPEEmpresa(){
		return "SELECT 
					p.*, e.empresa, e.corsistema, e.habilitarmatriz
				FROM
					carbonnovo._lp p
						JOIN
					empresa e ON (e.idempresa = p.idempresa)
				WHERE
					p.idlp = ?idlp?
								AND EXISTS( SELECT 1
									FROM
										carbonnovo._lpobjeto o
									WHERE
										o.idobjeto = ?idlpgrupo?
											AND o.tipoobjeto = 'lpgrupo'
											AND o.idlp = p.idlp)";
	}

	public static function buscarLPEEmpresa2(){
		return "SELECT 
					lp.*, e.empresa, e.corsistema, e.sigla, e.habilitarmatriz
				FROM
					carbonnovo._lp lp
						JOIN
					empresa e ON e.idempresa = lp.idempresa
				WHERE
					lp.idlp = ?idlp?";
	}

	public static function buscarObjetosVinculadosALp(){
		return "SELECT 
					idlpobjeto,
					idobjeto,
					tipoobjeto,
					CASE
						WHEN tipoobjeto = 'pessoa' THEN 1
						ELSE 9
					END as ord
				FROM
					lpobjeto
				WHERE
					idlp = ?idlp? AND tipoobjeto != 'lpgrupo'
				ORDER BY ord ASC";
	}

	public static function jsonRepDisponiveis(){
		return "SELECT r.idrep,
						r.rep
				from carbonnovo._rep r
				where not exists(
					select 1 from carbonnovo._lprep lr where lr.idlp=?idlp? and lr.idrep=r.idrep
				)
				order by rep";
	}

	public static function buscarTipopessoaVinculadoALp(){
		return "SELECT ov.idobjetovinculo,
						a.idtipopessoa,
						a.tipopessoa
				FROM tipopessoa a
					LEFT JOIN objetovinculo ov on (ov.idobjetovinc = a.idtipopessoa AND ov.tipoobjetovinc = 'tipopessoa' AND ov.idobjeto =  ?idlp? AND ov.tipoobjeto = '_lp')
				where a.status='ATIVO'
				ORDER BY  a.tipopessoa";
	}

	public static function buscarAgencias(){
		return "SELECT 
					ov.idobjetovinculo,
					a.idagencia,
					a.agencia,
					e.sigla
				FROM agencia a
				JOIN empresa e ON a.idempresa = e.idempresa
				LEFT JOIN objetovinculo ov ON ov.idobjetovinc = a.idagencia AND ov.tipoobjetovinc = 'agencia' AND ov.idobjeto = ?idlp? AND ov.tipoobjeto = '_lp'
				WHERE a.status = 'ATIVO'
				?clausulamatriz?
				ORDER BY e.sigla, a.agencia";
	}

	public static function buscarIdempresasDaLp(){
		return "SELECT group_concat(idempresa) as idempresas
				from 
					(select idobjeto as idempresa
					from carbonnovo._lpobjeto
					where idlp=?idlp?
							and tipoobjeto='empresa'
					union
					SELECT idempresa
					from carbonnovo._lp
					where idlp=?idlp?) as u";
	}

	public static function buscarUnidades(){
		return "SELECT ov.idobjetovinculo,
						u.idunidade,
						u.unidade
				FROM unidade u
					LEFT JOIN objetovinculo ov ON (ov.idobjetovinc = u.idunidade AND ov.tipoobjetovinc = 'unidade' AND ov.idobjeto = ?idlp? AND ov.tipoobjeto = '_lp')
				WHERE u.status = 'ATIVO'
					and u.idempresa in (?idempresas?)
			ORDER BY u.unidade";
	}

	public static function buscarContaitem(){
		return "SELECT ov.idobjetovinculo,
						c.idcontaitem,
						c.contaitem,
						e.sigla
				FROM contaitem c
				JOIN empresa e ON c.idempresa = e.idempresa
				LEFT JOIN objetovinculo ov ON ov.idobjetovinc = c.idcontaitem AND ov.tipoobjetovinc = 'contaitem' AND ov.idobjeto = '?idlp?' AND ov.tipoobjeto = '_lp'
				WHERE c.status = 'ATIVO'
				?clausulamatriz?
				ORDER BY e.sigla, c.contaitem";
	}

	public static function buscarFormapagamento(){
		return "SELECT * FROM (
					SELECT 
						ov.idobjetovinculo,
						c.idformapagamento,
						c.descricao,
						e.sigla,
						c.idempresa
					FROM formapagamento c JOIN empresa e ON c.idempresa = e.idempresa
					LEFT JOIN objetovinculo ov ON ov.idobjetovinc = c.idformapagamento AND ov.tipoobjetovinc = 'formapagamento' AND ov.idobjeto = '?idlp?' AND ov.tipoobjeto = '_lp'
					WHERE c.status = 'ATIVO'
					AND c.idunidade IS NOT NULL 
					AND c.formapagamento = 'C.CREDITO'
					UNION 
					SELECT 
						ov.idobjetovinculo,
						c.idformapagamento,
						c.descricao,
						e.sigla,
						c.idempresa
					FROM formapagamento c
					JOIN empresa e ON c.idempresa = e.idempresa
					LEFT JOIN objetovinculo ov ON ov.idobjetovinc = c.idformapagamento AND ov.tipoobjetovinc = 'formapagamento' AND ov.idobjeto = '?idlp?' AND ov.tipoobjeto = '_lp'
					WHERE c.status = 'ATIVO'
					AND c.formapagamento <> 'C.CREDITO'
				) as f
				?clausulamatriz?
				ORDER BY f.sigla, f.descricao";
	}

	public static function buscarDashboards(){
		return "SELECT s.iddashcard,
						concat(IF(c.tipoobjeto = 'manual',
						e.sigla,
						concat('[FLUXO - ',s.modulo,']')),' - ',IFNULL(s.grupo_rotulo,''), ' -> ',IFNULL(panel_title,''), ' -> ', IFNULL(card_title,'')) as dashboard,
						idlpobjeto,
						s.criadopor,
						s.criadoem,
						p.iddashpanel,
						s.panel_title,
						c.cardtitle as card_title,
						c.cardtitlesub as card_title_sub,
						s.card_border_color,
						s.card_color,
						s.card_bg_class,
						s.iddashgrupo,
						s.grupo_rotulo,
						e.corsistema,
						p.iddashpanel
				from 
					dashboard s
				join dashcard c on 
					c.iddashcard = s.iddashcard and c.status = 'ATIVO'
				LEFT join empresa e on 
					e.idempresa = s.idempresa
				join dashpanel p on 
					p.iddashpanel = c.iddashpanel and p.status = 'ATIVO'
				join dashgrupo g on 
					g.iddashgrupo = p.iddashgrupo and g.status = 'ATIVO'
				join carbonnovo._lpobjeto o force index(lp_id) on o.idlp= ?idlp? 
					and o.tipoobjeto='dashboard' 
					and o.idobjeto=s.iddashcard
				where 
					s.status='ATIVO' and c.status = 'ATIVO'
					and exists (
									select 
										1 
									from 
										carbonnovo._modulo m 
									join 
										objempresa oe on oe.idobjeto = m.idmodulo and oe.objeto = 'modulo' 
									where 
										m.modulo = s.modulo and oe.empresa = s.idempresa
										and (oe.empresa = ?idempresa? or oe.empresa in (select lo.idobjeto from carbonnovo._lpobjeto lo where lo.idlp =  ?idlp? and lo.tipoobjeto = 'empresa' and oe.empresa = lo.idobjeto))
										)
				group by s.iddashcard
				order by s.iddashgrupo desc,
						c.ordem asc,
						panel_title asc,
						idlpobjeto desc,
						concat( 
								IF(c.tipoobjeto = 'manual',
										e.sigla,
										'[FLUXO]'),
								' - ',
								IFNULL(
										s.grupo_rotulo,
										''),
								' -> ',
								IFNULL(
										panel_title,
										''),
								' -> ',
								IFNULL(
										card_title,
										'')
							)";
	}

	public static function buscarDashboardsDisponiveis(){
		return "SELECT s.iddashcard,
						concat(IF(c.tipoobjeto = 'manual',
						e.sigla,
						concat('[FLUXO - ',s.modulo,']')),' - ',IFNULL(s.grupo_rotulo,''), ' -> ',IFNULL(panel_title,''), ' -> ', IFNULL(card_title,'')) as dashboard,
						s.criadopor,
						s.criadoem,
						p.iddashpanel,
						s.panel_title,
						case when c.tipoobjeto = 'etapa' then concat(m.rotulomenu,' Etapa ',s.card_title) else s.card_title end as card_title,
						s.card_title_sub,
						s.card_border_color,
						s.card_color,
						s.card_bg_class,
						s.iddashgrupo,
						s.grupo_rotulo,
						e.corsistema,
						e.sigla
				from 
					dashboard s
				join dashcard c on 
					c.iddashcard = s.iddashcard and c.status = 'ATIVO'
				LEFT join empresa e on 
					e.idempresa = s.idempresa
				join dashpanel p on 
					p.iddashpanel = c.iddashpanel and p.status = 'ATIVO'
				join dashgrupo g on 
					g.iddashgrupo = p.iddashgrupo and g.status = 'ATIVO'
				left join carbonnovo._modulo m on
					(m.modulo = s.modulo)
				where 
					s.status='ATIVO' and c.status = 'ATIVO'
					-- and exists (
					--                 select 
					--                     1 
					--                 from 
					--                     carbonnovo._modulo m 
					--                 join 
					--                     objempresa oe on oe.idobjeto = m.idmodulo and oe.objeto = 'modulo'
					--                 where 
					--                     m.modulo = s.modulo and oe.empresa = s.idempresa
					--                      and (oe.empresa = ?idempresa? or oe.empresa in (select lo.idobjeto from carbonnovo._lpobjeto lo where lo.idlp = ?idlp? and lo.tipoobjeto = 'empresa' and oe.empresa = lo.idobjeto))
					--                     )
					and exists (select 1 from carbonnovo._lpmodulo lm JOIN carbonnovo._modulo m ON m.modulo = lm.modulo where (lm.idlp = ?idlp? OR m.tipo = 'BTPR') AND s.modulo = lm.modulo AND lm.permissao in ('r', 'w'))
					and not exists (select 1 
					from carbonnovo._lpobjeto o force index(lp_id) where
						o.idlp=?idlp?
					and o.tipoobjeto='dashboard' 
					and o.idobjeto=s.iddashcard)
					and exists(
						select 1
						from carbonnovo._lpmodulo lpm
						join carbonnovo._modulo m on(m.modulo = lpm.modulo)
						where idlp = ?idlp?
						and m.modulo = g.modulo
					)
					order by g.ordem asc,s.iddashgrupo,p.ordem asc,p.iddashpanel,c.ordem asc,s.iddashcard,grupo_rotulo asc, p.iddashpanel asc,c.ordem asc,panel_title asc, concat(IF(c.tipoobjeto = 'manual',e.sigla,
					'[FLUXO]'),' - ',IFNULL(s.grupo_rotulo,''), ' -> ',IFNULL(panel_title,''), ' -> ', IFNULL(card_title,''));";
	}

	public static function criarLp(){
		return "INSERT INTO carbonnovo._lp (idempresa, grupo, fullaccess, descricao, status)
		VALUES (?idempresa?, '?grupo?', '?fullaccess?', '?descr?', 'ATIVO')";
	}

	public static function vincularLp(){
		return "INSERT INTO carbonnovo._lpobjeto
				(idlp,
				idobjeto,
				tipoobjeto,
				criadopor,
				criadoem,
				alteradopor,
				alteradoem)
				VALUES
				(?idlp?,
				?idlpgrupo?,
				'lpgrupo',
				'?usuario?',
				now(),
				'?usuario?',
				now());";
	}

	public static function buscarPessoasVinculadasEPessoasDoGrupoVinculado()
	{
		return "SELECT DISTINCT 
					l.idempresa
					, p.idpessoa
					, p.nomecurto
					, CONCAT(e.sigla, ' - ', l.descricao) as grupo
					, '' as descr
					, l.idlp as idobjetoext
					, '_lp' as tipoobjetoext
				FROM carbonnovo._lp l
				JOIN lpobjeto lo on lo.idlp = l.idlp  
				JOIN empresa e ON e.idempresa = l.idempresa  
				JOIN pessoaobjeto fas on fas.idobjeto=lo.idobjeto and fas.tipoobjeto = 'sgarea' and lo.tipoobjeto = 'sgarea'
				JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO' AND l.grupo = 'Y' AND NOT l.status = 'INATIVO' 														
				UNION														
				SELECT DISTINCT 
					l.idempresa
					, p.idpessoa
					, p.nomecurto
					, CONCAT(e.sigla, ' - ', l.descricao) as grupo
					, '' as descr
					, l.idlp as idobjetoext
					, '_lp' as tipoobjetoext
				FROM carbonnovo._lp l
				JOIN lpobjeto lo on lo.idlp = l.idlp    
				JOIN pessoaobjeto fas on fas.idobjeto=lo.idobjeto and fas.tipoobjeto = 'sgdepartamento' and lo.tipoobjeto = 'sgdepartamento'
				JOIN sgdepartamento sd ON fas.idobjeto = sd.idsgdepartamento AND NOT sd.status='INATIVO'
				JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO' AND l.grupo = 'Y' AND NOT l.status = 'INATIVO'
				JOIN empresa e ON e.idempresa = l.idempresa	
				UNION
				SELECT DISTINCT 
					l.idempresa
					, p.idpessoa
					, p.nomecurto
					, CONCAT(e.sigla, ' - ', l.descricao) as grupo
					, '' as descr
					, l.idlp as idobjetoext
					, '_lp' as tipoobjetoext
				FROM carbonnovo._lp l
				JOIN lpobjeto lo on lo.idlp = l.idlp    
				JOIN empresa e ON e.idempresa = l.idempresa
				JOIN pessoaobjeto fas on fas.idobjeto=lo.idobjeto and fas.tipoobjeto = 'sgsetor' and lo.tipoobjeto = 'sgsetor'
				JOIN sgsetor sg ON fas.idobjeto = sg.idsgsetor AND NOT sg.status='INATIVO'
				JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO' AND l.grupo = 'Y' AND NOT l.status = 'INATIVO'
				UNION
				SELECT DISTINCT 
					l.idempresa
					, p.idpessoa
					, p.nomecurto
					, CONCAT(e.sigla, ' - ', l.descricao) as grupo
					, '' as descr
					, l.idlp as idobjetoext
					, '_lp' as tipoobjetoext
				FROM  carbonnovo._lp l
				JOIN lpobjeto lo on lo.idlp = l.idlp 
				JOIN empresa e ON e.idempresa = l.idempresa
				JOIN objetovinculo o on o.idobjeto = lo.idobjeto and lo.tipoobjeto = 'sgsetor' and o.tipoobjeto = 'sgsetor' 
				JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgsetor' and lo.tipoobjeto = 'sgsetor'
				JOIN sgsetor sg ON fas.idobjeto = sg.idsgsetor AND NOT sg.status='INATIVO'
				JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO' AND l.grupo = 'Y' AND NOT l.status = 'INATIVO'
				UNION
				SELECT DISTINCT 
					l.idempresa
					, p.idpessoa
					, p.nomecurto
					, CONCAT(e.sigla, ' - ', l.descricao) as grupo
					, '' as descr
					, l.idlp as idobjetoext
					, '_lp' as tipoobjetoext
				FROM 
					carbonnovo._lp l
					join lpobjeto lo on lo.idlp = l.idlp    
					JOIN empresa e ON e.idempresa = l.idempresa													
					JOIN pessoa p on p.idpessoa = lo.idobjeto and lo.tipoobjeto = 'pessoa' AND NOT p.status='INATIVO' AND l.grupo = 'Y' AND NOT l.status = 'INATIVO'
				UNION
			SELECT DISTINCT 
					a.idempresa
					, p.idpessoa
					, p.nomecurto
					, CONCAT(e.sigla, ' - ', l.descricao) as grupo
					, a.descr as descr
					-- , if(s.idsgsetor, s.idsgsetor, if(l.idlp, l.idlp, a.idimgrupo))  as idobjetoext
					,l.idlp as idobjetoext
					-- , if(s.idsgsetor, 'sgsetor', if(l.idlp, '_lp', 'imgrupo'))   as tipoobjetoext  
					,'_lp' as tipoobjetoext  
					
				FROM imgrupo a
					JOIN carbonnovo._lp l on l.idlp = a.idobjetoext and a.tipoobjetoext in ('_lp') AND NOT l.status = 'INATIVO'
					JOiN objetovinculo o on o.idobjeto = a.idimgrupo and o.tipoobjeto = 'imgrupo'
					JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgsetor' and o.tipoobjetovinc = 'sgsetor'
					JOIN sgsetor sg ON fas.idobjeto = sg.idsgsetor AND NOT sg.status='INATIVO'
					JOIN empresa e ON e.idempresa = l.idempresa
					JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO' and a.tipoobjetoext = '_lp'";
	}

	public static function buscarLpDashPorIdLpEIdEmpresa()
	{
		return "SELECT idlp,jsondashboardconf 
				FROM "._DBCARBON."._lp 
				WHERE jsondashboardconf <> '' 
				AND idlp in (?idlp?) 
				AND idempresa = ?idempresa?";
	}

	public static function buscarLpsDisponiveisParaVinculoPorIdDashCard()
	{
		return "SELECT 
					l.idlp,
					CONCAT(e.sigla,' - ',l.descricao) as descricao
				FROM "._DBCARBON."._lp l 
				LEFT JOIN empresa e on (e.idempresa=l.idempresa)
				WHERE l.status='ATIVO'
				AND l.idlp NOT IN (
					SELECT lo.idlp 
					FROM "._DBCARBON."._lpobjeto lo 
					WHERE lo.idobjeto = '?iddashcard?' 
						AND lo.tipoobjeto = 'dashboard'
				)";
	}

	public static function buscarLpPorIdbi()
    {
        return "SELECT e.sigla, b.idbi, b.nome, b.reportid, lb.idlpbi, b.bipai
                FROM carbonnovo._bi b
                LEFT JOIN carbonnovo._lpbi lb ON lb.idbi = b.idbi AND lb.idlp = ?idlp?
				JOIN laudo.empresa e on e.idempresa = b.idempresa
				WHERE b.status = 'ATIVO'
				-- and e.idempresa in (?idempresa?)
				ORDER BY e.idempresa,bipai,ordem, nome";
    }

	public static function buscarLpPorDescricao(){
		return"select idlp
					from carbonnovo._lp
					where descricao like ('%?descricao?%')
					AND status = 'ATIVO'";
	}
}

?>
