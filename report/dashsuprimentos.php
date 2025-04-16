<?

$dashsuprimentos = " 
	    insert into  dashboard (
			panel_id,
			panel_class_col,
			panel_title,
			card_id,
			card_class_col,
			card_url,
			card_notification_bg,
			card_notification,
			card_color,
			card_border_color,
			card_bg_class,
			card_title,
			card_title_sub,
			card_value,
			card_icon,
			card_title_modal,
			card_url_modal
		)
	(
	SELECT
		'dashsuprimentosprodalerta' as panel_id,
		'col-md-2' as panel_class_col,
		'PRODUTO(S) EM ALERTA' as panel_title,
		'dashsuprimentosprodalerta1' as card_id,
		'col-md-12 col-sm-12 col-xs-12' as card_class_col,
		concat('?_modulo=produtoemalerta&novajanela=Y') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'ABAIXO DO ESTOQUE' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PRODUTO(S) EM ALERTA' as card_title_modal,
		'' as card_url_modal
	from
		(
			SELECT
				1
			FROM
				(
					SELECT
						c.idcotacao AS idprodserv,
						'' AS codprodserv,
						prodservdescr AS descr,
						'' AS estmin,
						'' AS pedido,
						'' AS total,
						'' AS quar,
						'MANUAL' as entrada,
						c.status AS statusorc,
						n.status as status,
						n.idnf,
						c.idcotacao,
						(
							if(
								DATE_FORMAT(n.previsaoentrega, '%Y-%m-%d') >= DATE_FORMAT(now(), '%Y-%m-%d'),
								'O',
								'V'
							)
						) as atrasado,
						(
							if(
								DATE_FORMAT(c.prazo, '%Y-%m-%d') >= DATE_FORMAT(now(), '%Y-%m-%d'),
								'O',
								'V'
							)
						) as atrasadocot,
						'MANUAL' AS tipo,
						c.alteradoem AS ultimoconsumo
					FROM
						nfitem i FORCE INDEX(PRAZO)
						JOIN nf n ON i.idnf = n.idnf
						JOIN cotacao c ON n.idobjetosolipor = c.idcotacao
					WHERE
						idprodserv IS NULL
						AND n.status NOT IN ('CONCLUIDO', 'CANCELADO', 'REPROVADO')
						and c.idempresa = 1
						AND n.tipoobjetosolipor = 'cotacao'
					UNION
					SELECT
						u2.*,
						CASE
							WHEN u3.statusorc is null THEN 'PENDENTE'
							ELSE u3.statusorc
						END as statusorc,
						(
							select
								n.status
							from
								nfitem i,
								nf n,
								cotacao c
							where
								i.idprodserv = u2.idprodserv
								and i.idnf = n.idnf
								and i.nfe = 'Y'
								and n.idobjetosolipor = u3.idcotacao
								and n.tipoobjetosolipor = 'cotacao'
								and n.status in ('APROVADO', 'DIVERGENCIA')
							LIMIT
								1
						) as status,
						(
							select
								n.idnf
							from
								nfitem i,
								nf n,
								cotacao c
							where
								i.idprodserv = u2.idprodserv
								and i.idnf = n.idnf
								and i.nfe = 'Y'
								and n.idobjetosolipor = u3.idcotacao
								and n.tipoobjetosolipor = 'cotacao'
								and n.status in ('APROVADO', 'DIVERGENCIA')
							LIMIT
								1
						) as idnf,
						u3.idcotacao,
						(
							select
								if(
									DATE_FORMAT(n.previsaoentrega, '%Y-%m-%d') >= DATE_FORMAT(now(), '%Y-%m-%d'),
									'O',
									'V'
								)
							from
								nfitem i,
								nf n,
								cotacao c
							where
								i.idprodserv = u2.idprodserv
								and i.idnf = n.idnf
								and i.nfe = 'Y'
								and n.idobjetosolipor = u3.idcotacao
								and n.tipoobjetosolipor = 'cotacao'
								and n.status in ('APROVADO', 'DIVERGENCIA')
							LIMIT
								1
						) as atrasado,
						(
							if(
								DATE_FORMAT(u3.prazo, '%Y-%m-%d') >= DATE_FORMAT(now(), '%Y-%m-%d'),
								'O',
								'V'
							)
						) as atrasadocot,
						'NORMAL' AS tipo,
						(
							SELECT
								criadoem
							FROM
								prodcomprar
							WHERE
								status = 'ATIVO'
								AND idprodserv = u2.idprodserv
						) AS ultimoconsumo
					FROM
						(
							SELECT
								idprodserv,
								codprodserv,
								descr,
								estmin,
								pedido,
								SUM(total) AS total,
								SUM(quar) AS quar,
								entrada
							FROM
								(
									SELECT
										p.idprodserv,
										p.codprodserv,
										p.descr,
										p.estmin,
										p.pedido,
										IFNULL(f.qtd, 0) AS total,
										(
											SELECT
												IFNULL(SUM(q.qtdprod), 0)
											FROM
												lote q
											WHERE
												q.idprodserv = p.idprodserv
												AND q.status = 'QUARENTENA'
										) AS quar,
										'NORMAL' AS entrada
									FROM
										prodserv p
										join unidadeobjeto o on(
											o.idunidade = 8
											and o.idobjeto = p.idprodserv
											and o.tipoobjeto = 'prodserv'
										)
										LEFT JOIN lote l ON (
											l.idprodserv = p.idprodserv
											AND l.status IN ('APROVADO', 'QUARENTENA')
										)
										LEFT JOIN lotefracao f on(
											f.idlote = l.idlote
											AND f.idunidade = 8
											and f.status = 'DISPONIVEL'
										)
									WHERE
										p.tipo = 'PRODUTO'
										and p.idempresa = 1
										AND p.status = 'ATIVO'
										AND p.estmin IS NOT NULL
										AND p.estmin != 0.00
										AND p.comprado = 'Y'
								) AS u
							GROUP BY
								u.idprodserv
						) u2
						LEFT JOIN (
							SELECT
								MAX(c.prazo) AS prazo,
								c.status AS statusorc,
								i.idprodserv,
								c.idcotacao
							FROM
								cotacao c
								JOIN nf n ON n.idobjetosolipor = c.idcotacao
								JOIN nfitem i FORCE INDEX (PRAZO) ON n.tipoobjetosolipor = 'cotacao'
								AND i.idnf = n.idnf
								AND n.status NOT IN ('CONCLUIDO', 'CANCELADO', 'REPROVADO')
							GROUP BY
								i.idprodserv
						) u3 ON u3.idprodserv = u2.idprodserv
					WHERE
						u2.estmin >= u2.total
				) AS xx
			where
				xx.statusorc != 'CONCLUIDA'
				and (
					CASE
						WHEN xx.statusorc = 'PENDENTE' THEN 1
						WHEN xx.statusorc = 'ABERTA' THEN 1
						WHEN xx.statusorc = 'COMPRAR'
						and xx.atrasadocot = 'V' THEN 2
						WHEN xx.statusorc = 'COMPRAR'
						and xx.atrasadocot = 'O' THEN 3
						WHEN xx.statusorc = 'CONCLUIDA'
						AND xx.status = 'DIVERGENCIA'
						and xx.tipo = 'NORMAL'
						and xx.atrasado = 'V' THEN 4
						WHEN xx.statusorc = 'CONCLUIDA'
						AND xx.status = 'DIVERGENCIA'
						and xx.tipo = 'MANUAL'
						and xx.atrasado = 'V' THEN 5
						WHEN xx.statusorc = 'CONCLUIDA'
						AND xx.status = 'APROVADO'
						and xx.tipo = 'NORMAL'
						and xx.atrasado = 'V' THEN 4
						WHEN xx.statusorc = 'CONCLUIDA'
						AND xx.status = 'APROVADO'
						and xx.tipo = 'MANUAL'
						and xx.atrasado = 'V' THEN 5
						WHEN xx.statusorc = 'ANDAMENTO'
						and xx.atrasadocot = 'V' THEN 2
						WHEN xx.statusorc = 'ANDAMENTO'
						and xx.atrasadocot = 'O' THEN 3
						WHEN xx.statusorc = 'CONCLUIDA'
						and xx.atrasado = 'O' THEN 8
						ELSE 9
					END
				) in (1, 2, 4, 5, 6, 9)
			group by
				idprodserv,
				idcotacao
		) a
		
	
UNION ALL


	SELECT
		'dashsuprimentoscotacao' as panel_id,
		'col-md-4' as panel_class_col,
		'COTAÇÃO' as panel_title,
		'dashsuprimentoscotacaoenviado' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat(
			'_modulo=cotacao&_pagina=0&_ordcol=dmaemissao&_orddir=desc&_filtrosrapidos={%22cotacao%22:%22ENVIADO%22}&idcotacao=[',
			group_concat(idcotacao separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'ENVIADO' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'COTAÇÃO - ENVIADO' as card_title_modal,
		'_modulo=cotacao&_acao=u' as card_url_modal
	FROM
		vwcotacao
	WHERE
		cotacao = 'ENVIADO' 
		".getidempresa('idempresa','cotacao')."
		
		
UNION ALL


	SELECT
		'dashsuprimentoscotacao' as panel_id,
		'col-md-4' as panel_class_col,
		'COTAÇÃO' as panel_title,
		'dashsuprimentoscotacaorecebido' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat(
			'_modulo=cotacao&_pagina=0&_ordcol=dmaemissao&_orddir=desc&_filtrosrapidos={%22cotacao%22:%22RESPONDIDO%22}&idcotacao=[',
			group_concat(idcotacao separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'RESPONDIDO' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'COTAÇÃO - RESPONDIDO' as card_title_modal,
		'_modulo=cotacao&_acao=u' as card_url_modal
	FROM
		vwcotacao
	WHERE
		cotacao = 'RESPONDIDO' 
		".getidempresa('idempresa','cotacao')."
		
		
UNION ALL


	SELECT
		'dashcompras' as panel_id,
		'col-md-4' as panel_class_col,
		'COMPRAS' as panel_title,
		'dashcomprasaprovadas' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat(
			'_modulo=nfentrada&_pagina=0&_ordcol=idnf&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO%22}&_fds=',
			DATE_FORMAT(
				DATE_SUB(
					DATE_FORMAT(CURRENT_DATE, '%Y-%m-%d'),
					interval 1 year
				),
				'%d/%m/%Y'
			),
			'-',
			DATE_FORMAT(
				DATE_SUB(CURRENT_DATE, interval 1 day),
				'%d/%m/%Y'
			)
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'aprovado' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'COMPRAS - APROVADO' as card_title_modal,
		'_modulo=nfentrada&_acao=u' as card_url_modal
	FROM
		`nf` `n`
	WHERE
		n.status = 'APROVADO' 
		and n.tiponf IN ('C', 'T', 'E', 'S', 'M', 'F', 'B')
		and dtemissao between DATE_SUB(
			DATE_FORMAT(CURRENT_DATE, '%Y-%m-%d'),
			interval 1 year
		)
		and DATE_SUB(CURRENT_DATE, interval 1 day)
		".getidempresa('idempresa','nfentrada')."
		
		
UNION ALL


	SELECT
		'dashcompras' as panel_id,
		'col-md-4' as panel_class_col,
		'COMPRAS' as panel_title,
		'dashcomprasaprovadasematraso' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat(
			'_modulo=nfentrada&_pagina=0&_ordcol=previsaoentrega&_orddir=asc&_filtrosrapidos={%22status%22:%22APROVADO%22}&idnf=[',
			group_concat(idnf separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'em atraso' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'COMPRAS - APROVADAS EM ATRASO' as card_title_modal,
		'_modulo=nfentrada&_acao=u' as card_url_modal
	FROM
		`nf` `n`
	WHERE
		n.status = 'APROVADO' 
		and n.tiponf IN ('C', 'T', 'E', 'S', 'M', 'F', 'B')
		and previsaoentrega < CURRENT_DATE
		".getidempresa('idempresa','nfentrada')."
		
		
UNION ALL


	SELECT
		'dashenvioemail' as panel_id,
		'col-md-2' as panel_class_col,
		'EMAILS NÃO ENVIADOS' as panel_title,
		'dashenvioemailcotacao' as card_id,
		'col-md-12 col-sm-12 col-xs-12' as card_class_col,
		concat(
			'_modulo=envioemail&_pagina=0&_ordcol=idmailfila&_orddir=desc&_filtrosrapidos={%22status%22:%22NAO%20ENVIADO%22}&idmailfila=[',
			group_concat(distinct m.idmailfila separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (
			count(distinct m.idmailfila) > 0,
			'danger',
			'success'
		) as card_color,
		if (
			count(distinct m.idmailfila) > 0,
			'danger',
			'success'
		) as card_border_color,
		'' as card_bg_class,
		'Cotação' as card_title,
		'' as card_title_sub,
		count(distinct m.idmailfila) as card_value,
		'fa-print' as card_icon,
		'EMAILS NÃO ENVIADOS' as card_title_modal,
		'_modulo=envioemail&_acao=u' as card_url_modal
	from
		mailfila m
		LEFT JOIN mailfila m2 ON (
			m.idenvio = m2.idenvio
			AND m.idmailfila < m2.idmailfila
		)
	WHERE
		1
		AND m.status IN ('NAO ENVIADO')
		AND m.remover = 'N'
		AND m.tipoobjeto in ('cotacao', 'cotacaoaprovada')
		
		
UNION ALL


	select
		panel_id,
		panel_class_col,
		panel_title,
		card_id,
		card_class_col,
		card_url,
		card_notification_bg,
		card_notification,
		card_color,
		card_border_color,
		card_bg_class,
		card_title,
		card_title_sub,
		card_value,
		card_icon,
		card_title_modal,
		card_url_modal
	from
		(
			select
				'dashsuprimentoscontapagarccredito' as panel_id,
				'col-md-12' as panel_class_col,
				'FATURAS C.CRÉDITO - ABERTO' as panel_title,
				concat(
					'dashsuprimentoscontapagarccredito',
					replace(trim(lower(c.idcontapagar)), ' ', '')
				) as card_id,
				'col-md-2 col-sm-2 col-xs-6' as card_class_col,
				concat(
					'?_modulo=contapagar&_acao=u&idcontapagar=',
					c.idcontapagar,
					'&novajanela=Y'
				) as card_url,
				'fundovermelho' as card_notification_bg,
				'0' as card_notification,
				if (sum(cp.valor) >= c.valor, 'secundary', 'secundary ') as card_color,
				if (sum(cp.valor) >= c.valor, 'secundary', 'secundary ') as card_border_color,
				'' as card_bg_class,
				fp.descricao as card_title,
				concat(
					dma(c.datareceb),
					'</span><span card_titlesub_',
					if(c.valor - sum(cp.valor) >= 0, 'success', 'danger'),
					'>',
					if(c.valor - sum(cp.valor) >= 0, '-', '+'),
					' R$ ',
					format(ABS(c.valor - sum(cp.valor)), 2, 'de_DE')
				) as card_title_sub,
				concat('R$ ', format(sum(cp.valor), 2, 'de_DE')) as card_value,
				'fa-print' as card_icon,
				'' as card_title_modal,
				'_modulo=contapagar&_acao=u' as card_url_modal
			from
				contapagaritem cp
				join contapagar c on c.idcontapagar = cp.idcontapagar
				and c.status in ('ABERTO')
				join formapagamento fp on fp.idformapagamento = cp.idformapagamento
				join(
					select
						min(c.datareceb) as datareceb,
						c.idformapagamento
					from
						contapagar c
						join formapagamento f on f.idformapagamento = c.idformapagamento
					where
						f.formapagamento = 'C.CREDITO'
						and c.status in ('ABERTO') 
						".getidempresa('c.idempresa','contapagar')."
					group by
						f.idformapagamento
				) cpi on c.datareceb = cpi.datareceb
				and c.idformapagamento = cpi.idformapagamento
			group by
				c.idcontapagar
			order by
				c.datareceb,
				fp.descricao
		) a
		
		
UNION ALL


	select
		panel_id,
		panel_class_col,
		panel_title,
		card_id,
		card_class_col,
		card_url,
		card_notification_bg,
		card_notification,
		card_color,
		card_border_color,
		card_bg_class,
		card_title,
		card_title_sub,
		card_value,
		card_icon,
		card_title_modal,
		card_url_modal
	from
		(
			select
				'dashsuprimentoscontapagarccreditofechado' as panel_id,
				'col-md-12' as panel_class_col,
				'FATURAS C.CRÉDITO - FECHADO' as panel_title,
				concat(
					'dashsuprimentoscontapagarccreditofechado',
					replace(trim(lower(c.idcontapagar)), ' ', '')
				) as card_id,
				'col-md-2 col-sm-2 col-xs-6' as card_class_col,
				concat(
					'?_modulo=contapagar&_acao=u&idcontapagar=',
					c.idcontapagar,
					'&novajanela=Y'
				) as card_url,
				'fundovermelho' as card_notification_bg,
				'0' as card_notification,
				if (sum(cp.valor) >= c.valor, 'secundary', 'secundary ') as card_color,
				if (sum(cp.valor) >= c.valor, 'secundary', 'secundary ') as card_border_color,
				'' as card_bg_class,
				fp.descricao as card_title,
				concat(
					dma(c.datareceb),
					'</span><span card_titlesub_',
					if(c.valor - sum(cp.valor) >= 0, 'success', 'danger'),
					'>',
					if(c.valor - sum(cp.valor) >= 0, '-', '+'),
					' R$ ',
					format(ABS(c.valor - sum(cp.valor)), 2, 'de_DE')
				) as card_title_sub,
				concat('R$ ', format(sum(cp.valor), 2, 'de_DE')) as card_value,
				'fa-print' as card_icon,
				'' as card_title_modal,
				'_modulo=contapagar&_acao=u' as card_url_modal
			from
				contapagaritem cp
				join contapagar c on c.idcontapagar = cp.idcontapagar
				and c.status in ('FECHADO')
				join formapagamento fp on fp.idformapagamento = cp.idformapagamento
				join(
					select
						min(c.datareceb) as datareceb,
						c.idformapagamento
					from
						contapagar c
						join formapagamento f on f.idformapagamento = c.idformapagamento
					where
						f.formapagamento = 'C.CREDITO'
						and c.status in ('FECHADO') 
						".getidempresa('c.idempresa','contapagar')."
					group by
						f.idformapagamento
				) cpi on c.datareceb = cpi.datareceb
				and c.idformapagamento = cpi.idformapagamento
			group by
				c.idcontapagar
			order by
				c.datareceb,
				fp.descricao
		) a
		
		
		";
	  		
	
?>