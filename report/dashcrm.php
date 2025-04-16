<?


		
$dashcrm = "

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
		'dashautogenas' as panel_id,
		'col-md-4' as panel_class_col,
		'TEA / TRA' as panel_title,
		'dashautogenastraenviado' as card_id,
		'col-md-6 col-sm-6 col-xs-12' as card_class_col,
		concat(
			'_modulo=',
			uo.idobjeto,
			'&_pagina=0&_ordcol=idamostra&_orddir=desc&_filtrosrapidos={%22statustra%22:%22ENVIADO%22}&idamostra=[',
			group_concat(idamostra separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'ENVIADO' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'TEA / TRA - ENVIADO' as card_title_modal,
		concat('_modulo=', uo.idobjeto, '&_acao=u') as card_url_modal
	from
		amostra l
		JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON (
			`uo`.`tipoobjeto` = 'modulo'
			AND `l`.`idunidade` = `uo`.`idunidade`
		)
		JOIN carbonnovo._modulo m on m.modulo = uo.idobjeto
		and m.modulotipo = 'amostra'
	where
		l.statustra in ('ENVIADO')
		and l.idunidade = 9
		".str_replace('idpessoa','l.idpessoa',$pessoas)."


UNION ALL
		  

	SELECT
		'dashautogenas' as panel_id,
		'col-md-2' as panel_class_col,
		'TEA / TRA' as panel_title,
		'dashautogenastradevolvido' as card_id,
		'col-md-6 col-sm-6 col-xs-12' as card_class_col,
		concat(
			'_modulo=',
			uo.idobjeto,
			'&_pagina=0&_ordcol=idamostra&_orddir=desc&_filtrosrapidos={%22statustra%22:%22DEVOLVIDO%22}&idamostra=[',
			group_concat(idamostra separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'DEVOLVIDO' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'TEA / TRA - DEVOLVIDO' as card_title_modal,
		concat('_modulo=', uo.idobjeto, '&_acao=u') as card_url_modal
	from
		amostra l
		JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON (
			`uo`.`tipoobjeto` = 'modulo'
			AND `l`.`idunidade` = `uo`.`idunidade`
		)
		JOIN carbonnovo._modulo m on m.modulo = uo.idobjeto
		and m.modulotipo = 'amostra'
	where
		l.statustra in ('DEVOLVIDO')
		and l.idunidade = 9
		".str_replace('idpessoa','l.idpessoa',$pessoas)."

		  
UNION ALL


	SELECT
		'dashcrmpessoa' as panel_id,
		'col-md-4' as panel_class_col,
		'EMPRESAS' as panel_title,
		'dashcrmpessoacontato' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat(
			'_modulo=pessoa&_pagina=0&_ordcol=idpessoa&_orddir=desc&_filtrosrapidos={%22status%22:%22ATIVO%22,%22idtipopessoa%22:%222%22}&idpessoa=[',
			group_concat(DISTINCT idpessoa separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'SEM REPRESENTAÇÃO' as card_title,
		'' as card_title_sub,
		count(DISTINCT idpessoa) as card_value,
		'fa-print' as card_icon,
		'EMPRESAS - SEM REPRESENTAÇÃO' as card_title_modal,
		'_modulo=pessoa&_acao=u' as card_url_modal
	FROM
		pessoa p
	where
		not exists (
			select
				1
			from
				pessoacontato pc
			where
				pc.idpessoa = p.idpessoa
		)
		and p.idtipopessoa = 2
		and p.status = 'ATIVO'
		and p.vendadireta = 'N'
		".$pessoas."
		
		
UNION ALL


	SELECT
		'dashcrmpessoa' as panel_id,
		'col-md-4' as panel_class_col,
		'EMPRESAS' as panel_title,
		'dashcrmpessoapendente' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat(
			'_modulo=pessoa&_pagina=0&_ordcol=idpessoa&_orddir=desc&_filtrosrapidos={%22status%22:%22PENDENTE%22,%22idtipopessoa%22:%222%22}'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'PENDENTE' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'EMPRESAS - PENDENTE' as card_title_modal,
		'_modulo=pessoa&_acao=u' as card_url_modal
	FROM
		pessoa p
	where
		p.idtipopessoa = 2
		and p.status = 'PENDENTE'
	".$pessoas."


UNION ALL 


	SELECT
		'dashcrm' as panel_id,
		'col-md-4' as panel_class_col,
		'CRM' as panel_title,
		'dashcrmsementesvencidas' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat(
			'_modulo=semente&_pagina=0&_ordcol=idlote&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO%22}&idlote=[',
			group_concat(distinct idlote separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'sementes vencidas' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'CRM - SEMENTES VENCIDAS' as card_title_modal,
		'_modulo=semente&_acao=u' as card_url_modal
	FROM
		vwlote a
	where
		exists(
			select
				1
			from
				prodserv p
				join unidadeobjeto u on(
					u.idunidade = 9
					and u.idobjeto = p.idprodserv
					and u.tipoobjeto = 'prodserv'
				)
			where
				p.idprodserv = a.idprodserv
				and p.tipo = 'PRODUTO'
				and p.status = 'ATIVO'
				and p.especial = 'Y'
				and p.idtipoprodserv = 3
		)
		and DATE_FORMAT(CURRENT_DATE, '%Y-%m-%d') >= a.vencimento
		and a.status = 'APROVADO'
		and a.statusfr = 'DISPONÍVEL'
		and a.qtddisp > 0
		  ".$pessoas."
			
		
UNION ALL 


	SELECT
		'dashcrm' AS panel_id,
		'col-md-4' AS panel_class_col,
		'CRM' AS panel_title,
		'dashcrmsementesavencer' AS card_id,
		'col-md-6 col-sm-6 col-xs-6' AS card_class_col,
		concat(
			'_modulo=semente&_pagina=0&_ordcol=idlote&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO%22}&idlote=[',
			group_concat(DISTINCT idlote separator ','),
			']'
		) AS card_url,
		'fundovermelho' AS card_notification_bg,
		'0' AS card_notification,
		IF (
			count(1) > 0,
			'warning',
			'success'
		) AS card_color,
		IF (
			count(1) > 0,
			'warning',
			'success'
		) AS card_border_color,
		'' AS card_bg_class,
		'sementes a vencer' AS card_title,
		'próximos 90 dias' AS card_title_sub,
		count(1) AS card_value,
		'fa-print' AS card_icon,
		'CRM - SEMENTES A VENCER' AS card_title_modal,
		'_modulo=semente&_acao=u' AS card_url_modal
	FROM
		vwlote a
	WHERE
		exists (
			SELECT
				1
			FROM
				prodserv p
				JOIN unidadeobjeto u on(
					u.idunidade = 9
					AND u.idobjeto = p.idprodserv
					AND u.tipoobjeto = 'prodserv'
				)
			WHERE
				p.idprodserv = a.idprodserv
				AND p.tipo = 'PRODUTO'
				AND p.status = 'ATIVO'
				AND p.especial = 'Y'
				AND p.idtipoprodserv = 3
		)
		AND a.vencimento BETWEEN DATE_FORMAT(CURRENT_DATE, '%Y-%m-%d')
		AND DATE_ADD(
			DATE_FORMAT(CURRENT_DATE, '%Y-%m-%d'),
			interval 90 DAY
		)
		AND a.status = 'APROVADO'
		 ".$pessoas."	

		
UNION ALL


	SELECT
		'dashenvioemail' AS panel_id,
		'col-md-2' AS panel_class_col,
		'EMAILS NÃO ENVIADOS' AS panel_title,
		'dashenvioemailsenha' AS card_id,
		'col-md-12 col-sm-12 col-xs-6' AS card_class_col,
		CONCAT(
			'_modulo=envioemail&_pagina=0&_ordcol=idmailfila&_orddir=desc&_filtrosrapidos={%22status%22:%22NAO%20ENVIADO%22}&idmailfila=[',
			GROUP_CONCAT(DISTINCT m.idmailfila SEPARATOR ','),
			']'
		) AS card_url,
		'fundovermelho' AS card_notification_bg,
		'0' AS card_notification,
		IF(
			COUNT(DISTINCT m.idmailfila) > 0,
			'danger',
			'success'
		) AS card_color,
		IF(
			COUNT(DISTINCT m.idmailfila) > 0,
			'danger',
			'success'
		) AS card_border_color,
		'' AS card_bg_class,
		'Recuperação Senha' AS card_title,
		'' AS card_title_sub,
		COUNT(DISTINCT m.idmailfila) AS card_value,
		'fa-print' AS card_icon,
		'EMAILS NÃO ENVIADOS' AS card_title_modal,
		'_modulo=envioemail&_acao=u' AS card_url_modal
	FROM
		mailfila m
		LEFT JOIN mailfila m2 ON (
			m.idenvio = m2.idenvio
			AND m.idmailfila < m2.idmailfila
		)
	WHERE
		1
		AND m.status IN ('NAO ENVIADO')
		AND m.remover = 'N'
		AND m.tipoobjeto = 'recuperasenha' 
	 ";
			


?>