<?
	  

	
echo $dashlogistica = "  
			
			
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
		'dashlogistica' as panel_id,
		'col-md-4' as panel_class_col,
		'LOGÍSTICA - ENVIOS' as panel_title,
		'dashlogisticaenviosemandamento' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat(
			'_modulo=pedidologistica&_pagina=0&_ordcol=idnf&_orddir=desc&_filtrosrapidos={%22status%22:%22ENVIADO%22}'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'em andamento' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'LOGÍSTICA - EM ANDAMENTO' as card_title_modal,
		'_modulo=pedidologistica&_acao=u' as card_url_modal
	from
		vwnfsaida
	where
		status = 'ENVIADO' 
		".getidempresa('idempresa','pedidologistica')."
		
	
UNION ALL


	SELECT
		'dashlogistica' as panel_id,
		'col-md-6' as panel_class_col,
		'LOGÍSTICA - ENVIOS' as panel_title,
		'dashlogisticaenviosematraso' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat(
			'_modulo=pedidologistica&_pagina=0&_ordcol=idnf&_orddir=desc&_filtrosrapidos={%22status%22:%22ENVIAR,ENVIADO%22}&idnf=[',
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
		'LOGÍSTICA - ENVIOS EM ATRASO' as card_title_modal,
		'_modulo=pedidologistica&_acao=u' as card_url_modal
	from
		vwnfsaida
	where
		status in ('ENVIADO', 'ENVIAR') 
		and DATE_ADD(DATE_FORMAT(envio, '%Y-%m-%d'), interval 3 day) < CURRENT_DATE
		".getidempresa('idempresa','pedidologistica')."
		
		";
	  		
	  
	
	
?>