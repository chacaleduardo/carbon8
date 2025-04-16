<?

  $dashautogenas = "
	  

	SELECT
		'dashautogenas' as panel_id,
		'col-md-2' as panel_class_col,
		'TEA / TRA' as panel_title,
		'dashautogenastraaberta' as card_id,
		'col-md-12 col-sm-12 col-xs-12' as card_class_col,
		concat(
			'_modulo=',
			uo.idobjeto,
			'&_pagina=0&_ordcol=idamostra&_orddir=desc&_filtrosrapidos={%22statustra%22:%22ABERTO%22}&idamostra=[',
			group_concat(idamostra separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'TEA / TRA' as card_title,
		'em aberto' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'TEA / TRA - ABERTO' as card_title_modal,
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
		l.statustra in ('ABERTO') 
		".$dashlotescond."
		
	
UNION ALL


	SELECT
		'dashproducaoconcentrado' as panel_id,
		'col-md-6' as panel_class_col,
		'PRODUÇÃO DE CONCENTRADOS' as panel_title,
		'concentradosaproduzir' as card_id,
		'col-md-4 col-sm-4 col-xs-6' as card_class_col,
		'_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&_filtrosrapidos={%22status%22:%22TRIAGEM%22,%22idtipoprodserv%22,%2219%22,%22especial%22:%22N%22}' as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'a produzir' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PRODUÇÃO DE CONCENTRADOS - A PRODUZIR' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		idtipoprodserv = 19
		and especial = 'N'
		and idunidade = ".$linkunidade."
		and status = 'TRIAGEM' 
		".getidempresa('idempresa','formalizacao')."
		
		
UNION ALL


	SELECT
		'dashproducaoconcentrado' as panel_id,
		'col-md-6' as panel_class_col,
		'PRODUÇÃO DE CONCENTRADOS' as panel_title,
		'concentradosmespassado' as card_id,
		'col-md-4 col-sm-4 col-xs-6' as card_class_col,
		concat(
			'_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22,%22idtipoprodserv%22,%2219%22,%22especial%22:%22N%22}&_fds=',
			DATE_FORMAT(
				last_day(curdate() - interval 2 month) + interval 1 day,
				'%d/%m/%Y'
			),
			'-',
			DATE_FORMAT(
				last_day(curdate() - interval 1 month),
				'%d/%m/%Y'
			)
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'mês passado' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PRODUÇÃO DE CONCENTRADOS - MÊS PASSADO' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		idtipoprodserv = 19
		and especial = 'N'
		and fabricacao between last_day(curdate() - interval 2 month) + interval 1 day
		and last_day(curdate() - interval 1 month)
		and status in ('APROVADO', 'ESGOTADO') 
		".getidempresa('idempresa','formalizacao')."
		
		
UNION ALL


	SELECT
		'dashproducaoconcentrado' as panel_id,
		'col-md-6' as panel_class_col,
		'PRODUÇÃO DE CONCENTRADOS' as panel_title,
		'concentradosestemes' as card_id,
		'col-md-4 col-sm-4 col-xs-6' as card_class_col,
		concat(
			'_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22,%22idtipoprodserv%22:%2219%22,%22especial%22:%22N%22}&_fds=',
			DATE_FORMAT(
				last_day(curdate() - interval 1 month) + interval 1 day,
				'%d/%m/%Y'
			),
			'-',
			DATE_FORMAT(last_day(curdate()), '%d/%m/%Y')
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'este mês' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PRODUÇÃO DE CONCENTRADOS - ESTE MÊS' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		idtipoprodserv = 19
		and especial = 'N'
		and status in ('APROVADO', 'ESGOTADO')
		and fabricacao between last_day(curdate() - interval 1 month) + interval 1 day
		and last_day(curdate()) 
		".getidempresa('idempresa','formalizacao')."
		
	
UNION ALL


	SELECT
		'dashproducaovacina' as panel_id,
		'col-md-6' as panel_class_col,
		'VACINAS' as panel_title,
		'vacinasaproduzir' as card_id,
		'col-md-4 col-sm-4 col-xs-6' as card_class_col,
		'_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&_filtrosrapidos={%22status%22:%22TRIAGEM%22,%22especial%22:%22N%22}' as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'a produzir' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'VACINAS - A PRODUZIR' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		fabricado = 'Y'
		and venda = 'Y'
		and especial = 'N'
		and idunidade = ".$linkunidade."
		and status = 'TRIAGEM' 
		".getidempresa('idempresa','formalizacao')."
		
	
UNION ALL


	SELECT
		'dashproducaovacina' as panel_id,
		'col-md-6' as panel_class_col,
		'VACINAS' as panel_title,
		'vacinasmespassado' as card_id,
		'col-md-4 col-sm-4 col-xs-6' as card_class_col,
		concat(
			'_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22,%22especial%22:%22N%22}&_fds=',
			DATE_FORMAT(
				last_day(curdate() - interval 2 month) + interval 1 day,
				'%d/%m/%Y'
			),
			'-',
			DATE_FORMAT(
				last_day(curdate() - interval 1 month),
				'%d/%m/%Y'
			)
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'mês passado' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'VACINAS - MÊS PASSADO' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		fabricado = 'Y'
		and venda = 'Y'
		and especial = 'N'
		and fabricacao between last_day(curdate() - interval 2 month) + interval 1 day
		and last_day(curdate() - interval 1 month)
		and status in ('APROVADO', 'ESGOTADO') 
		".getidempresa('idempresa','formalizacao')."
		
		
UNION ALL


	SELECT
		'dashproducaovacina' as panel_id,
		'col-md-6' as panel_class_col,
		'VACINAS - PRODUZIDOS' as panel_title,
		'vacinasestemes' as card_id,
		'col-md-4 col-sm-4 col-xs-6' as card_class_col,
		concat(
			'_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22,%22especial%22:%22N%22}&_fds=',
			DATE_FORMAT(
				last_day(curdate() - interval 1 month) + interval 1 day,
				'%d/%m/%Y'
			),
			'-',
			DATE_FORMAT(last_day(curdate()), '%d/%m/%Y')
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'este mês' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'VACINAS - ESTE MÊS' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		fabricado = 'Y'
		and venda = 'Y'
		and especial = 'N'
		and status in ('APROVADO', 'ESGOTADO')
		and fabricacao between last_day(curdate() - interval 1 month) + interval 1 day
		and last_day(curdate()) 
		".getidempresa('idempresa','formalizacao')."	
	";
	
?>