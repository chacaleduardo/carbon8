<?

$dashti = "
	SELECT
		'dashti' as panel_id,
		'col-md-8' as panel_class_col,
		'EVENTOS - TI' as panel_title,
		'dashtilocalS' as card_id,
		'col-md-3 col-sm-4 col-xs-6' as card_class_col,
		concat(
			'_modulo=evento&_pagina=0&_ordcol=prazoamd&_orddir=asc&_filtrosrapidos={%22ideventotipo%22:%2221%22}&idevento=[',
			group_concat(idevento separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'SUPORTE TI' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'SUPORTE TI' as card_title_modal,
		'_modulo=evento&_acao=u' as card_url_modal
	from
		evento `e`
		JOIN "._DBCARBON."._status `est` ON ((`est`.`idstatus` = `e`.`idstatus`))
	where
		`e`.`idstatus` in (7, 29, 38, 2, 3)
		and e.ideventotipo in (21)
		
		
UNION ALL
	
	
	SELECT
		'dashti' as panel_id,
		'col-md-2' as panel_class_col,
		'EVENTOS - TI' as panel_title,
		'dashticorrecao' as card_id,
		'col-md-3 col-sm-4 col-xs-6' as card_class_col,
		concat(
			'_modulo=evento&_pagina=0&_ordcol=prazoamd&_orddir=asc&_filtrosrapidos={%22ideventotipo%22:%2228,53%22}&idevento=[',
			group_concat(idevento separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'CORRETIVAS' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'SUPORTE TI - CORREÇÃO' as card_title_modal,
		'_modulo=evento&_acao=u' as card_url_modal
	from
		evento `e`
		JOIN "._DBCARBON."._status `est` ON ((`est`.`idstatus` = `e`.`idstatus`))
	where
		`e`.`idstatus` in (3, 2, 8, 38, 29, 7)
		and e.ideventotipo in (28, 53)
		
		
UNION ALL


	SELECT
		'dashti' as panel_id,
		'col-md-2' as panel_class_col,
		'EVENTOS - TI' as panel_title,
		'dashtiprojetos' as card_id,
		'col-md-3 col-sm-4 col-xs-6' as card_class_col,
		concat(
			'_modulo=evento&_pagina=0&_ordcol=prazoamd&_orddir=asc&_filtrosrapidos={%22ideventotipo%22:%2240%22}&idevento=[',
			group_concat(idevento separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'PROJETOS' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'SUPORTE TI - PROJETOS' as card_title_modal,
		'_modulo=evento&_acao=u' as card_url_modal
	from
		evento `e`
		JOIN "._DBCARBON."._status `est` ON ((`est`.`idstatus` = `e`.`idstatus`))
	where
		not `e`.`idstatus` in (4, 6, 46)
		and e.ideventotipo in (40)
		
		
UNION ALL


	SELECT
		'dashti' as panel_id,
		'col-md-8' as panel_class_col,
		'EVENTOS - TI' as panel_title,
		'dashtiematraso' as card_id,
		'col-md-3 col-sm-3 col-xs-6' as card_class_col,
		concat(
			'_modulo=evento&_pagina=0&_ordcol=prazoamd&_orddir=asc&_filtrosrapidos={%22ideventotipo%22:%2240,21,28%22}&idevento=[',
			group_concat(idevento separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'EM ATRASO' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'SUPORTE TI - PROJETOS' as card_title_modal,
		'_modulo=evento&_acao=u' as card_url_modal
	from
		evento `e`
		JOIN "._DBCARBON."._status `est` ON ((`est`.`idstatus` = `e`.`idstatus`))
	where
		(
			`e`.`idstatus` in (7, 29, 38, 2, 3)
			and e.ideventotipo in (21)
			and DATE_FORMAT(prazo, '%Y-%m-%d') < CURRENT_DATE
		)
		or (
			`e`.`idstatus` in (7, 38, 2, 3)
			and e.ideventotipo in (28)
			and DATE_FORMAT(prazo, '%Y-%m-%d') < CURRENT_DATE
		)
		or (
			`e`.`idstatus` in (29, 2, 7, 32, 45, 55, 3)
			and e.ideventotipo in (28)
			and DATE_FORMAT(prazo, '%Y-%m-%d') < CURRENT_DATE
		)
		
		
UNION ALL


	SELECT
		'dashenvioemail' as panel_id,
		'col-md-4' as panel_class_col,
		'EMAILS NÃO ENVIADOS' as panel_title,
		'dashenvioemailtodos' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
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
		'pendente' as card_title,
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
		
		
	UNION ALL
	
	
	select
		*
	from
		(
			select
				CONCAT('dashtilocal') as panel_id,
				'col-md-12' as panel_class_col,
				'SUPORTE TI' as panel_title,
				concat('dashtilocal', FLOOR((RAND() * 1000))) as card_id,
				'col-md-2 col-sm-2 col-xs-6' as card_class_col,
				concat(
					'_modulo=evento&_pagina=0&_ordcol=prazoamd&_orddir=asc&idevento=[',
					group_concat(idevento separator ','),
					']'
				) as card_url,
				'fundovermelho' as card_notification_bg,
				'0' as card_notification,
				'secundary' as card_color,
				'secundary' as card_border_color,
				'' as card_bg_class,
				if (
					length(classificacao) > 0,
					classificacao,
					'- SEM CLASSIFICAÇÃO -'
				) as card_title,
				'' as card_title_sub,
				count(1) as card_value,
				'fa-print' as card_icon,
				concat('SUPORTE - ', upper(classificacao)) as card_title_modal,
				'_modulo=evento&_acao=u' as card_url_modal
			from
				(
					SELECT
						idevento,
						servico as classificacao
					FROM
						evento e
						JOIN "._DBCARBON."._status `est` ON ((`est`.`idstatus` = `e`.`idstatus`))
					where
						`e`.`idstatus` in (7, 29, 38, 2, 3)
						and e.ideventotipo in (21)
				) a
			group by
				classificacao
			order by
				classificacao
		) a
		
		
	UNION ALL
	
	
	select
		*
	from
		(
			select
				CONCAT('dashticorretivas') as panel_id,
				'col-md-12' as panel_class_col,
				'CORRETIVAS' as panel_title,
				concat('dashticorretivas', FLOOR((RAND() * 1000))) as card_id,
				'col-md-2 col-sm-2 col-xs-6' as card_class_col,
				concat(
					'_modulo=evento&_pagina=0&_ordcol=prazoamd&_orddir=asc&idevento=[',
					group_concat(idevento separator ','),
					']'
				) as card_url,
				'fundovermelho' as card_notification_bg,
				'0' as card_notification,
				'secundary' as card_color,
				'secundary' as card_border_color,
				'' as card_bg_class,
				if (
					length(classificacao) > 0,
					classificacao,
					'- SEM CLASSIFICAÇÃO -'
				) as card_title,
				'' as card_title_sub,
				count(1) as card_value,
				'fa-print' as card_icon,
				concat('CORRETIVAS - ', upper(classificacao)) as card_title_modal,
				'_modulo=evento&_acao=u' as card_url_modal
			from
				(
					SELECT
						idevento,
						classificacao
					FROM
						evento e
						JOIN "._DBCARBON."._status `est` ON ((`est`.`idstatus` = `e`.`idstatus`))
					where
						`e`.`idstatus` in (3, 2, 8, 38, 29, 7)
						and e.ideventotipo in (28, 53)
				) a
			group by
				classificacao
			order by
				classificacao
		) a
		
		
UNION ALL


	select
		*
	from
		(
			select
				CONCAT('dashtitags') as panel_id,
				'col-md-12' as panel_class_col,
				'TAGS' as panel_title,
				concat('dashtitags', FLOOR((RAND() * 1000))) as card_id,
				'col-md-2 col-sm-2 col-xs-6' as card_class_col,
				concat(
					'_modulo=tag&_pagina=0&_ordcol=tag&_orddir=desc&idtag=[',
					group_concat(idtag separator ','),
					']'
				) as card_url,
				'fundovermelho' as card_notification_bg,
				'0' as card_notification,
				if (count(1) > 0, 'secundary', 'danger') as card_color,
				if (count(1) > 0, 'secundary', 'danger') as card_border_color,
				'' as card_bg_class,
				if (
					length(tagtipo) > 0,
					tagtipo,
					'- SEM CLASSIFICAÇÃO -'
				) as card_title,
				status as card_title_sub,
				count(1) as card_value,
				'fa-print' as card_icon,
				concat('TAGS - ', upper(tagtipo)) as card_title_modal,
				'_modulo=tag&_acao=u' as card_url_modal
			from
				(
					SELECT
						t.idtag,
						tt.tagtipo,
						t.status
					FROM
						tag t
						join tagtipo tt on tt.idtagtipo = t.idtagtipo
					where
						tt.idtagtipo in (22, 24, 260, 204)
						and t.status in ('DISPONÍVEL', 'MANUTENÇÃO', 'BACKUP', 'ESTOQUE')
				) a
			group by
				tagtipo,
				status
			order by
				tagtipo
		) a

	
		"; 
	
?>