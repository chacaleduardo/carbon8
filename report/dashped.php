<?


	
 $dashped = "  

	SELECT
		'dashresultados' as panel_id,
		'col-md-4' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosematraso' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat(
			'_modulo=',
			uo.idobjeto,
			'&_pagina=0&_ordcol=idresultado&_orddir=desc&_filtrosrapidos={%22status%22:%22ABERTO,PROCESSANDO%22,%22ematraso%22:%22Y%22}'
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
		'RESULTADOS - EM ATRASO' as card_title_modal,
		concat('_modulo=', uo.idobjeto, '&_acao=u') as card_url_modal
	from
		resultado r
		JOIN amostra l on l.idamostra = r.idamostra
		JOIN prodserv p on p.idprodserv = r.idtipoteste
		JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON (
			(
				(`uo`.`tipoobjeto` = 'moduloresultun')
				AND (`l`.`idunidade` = `uo`.`idunidade`)
			)
		)
	where
		r.status in ('PROCESSANDO', 'ABERTO')
		and DATE_ADD(
			DATE_FORMAT(r.criadoem, '%Y-%m-%d'),
			interval p.prazoexec day
		) < CURRENT_DATE 
		".$dashlotescond." 
		
		
		
	UNION ALL
	
	
	SELECT
		'dashresultados' as panel_id,
		'col-md-4' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosassinatura' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat(
			'form/assinarresultado.php?_idrep=2&_acao=u&registro_1=&registro_2=&dataregistro_1=&dataregistro_2=&cliente=&teste=&exercicio=&status=FECHADO&idunidade=',
			$ linkunidade,
			'&novajanela=Y'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'assinatura' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'RESULTADOS - ASSINATURA' as card_title_modal,
		'_modulo=conferencia&statusres=FECHADO' as card_url_modal
	from
		vwassinarresultado l
	where
		status = 'FECHADO'
		and (
			l.idsecretaria = ''
			or l.idsecretaria is null
		) 
		".$dashlotescond."
		";
	  		
	  
?>