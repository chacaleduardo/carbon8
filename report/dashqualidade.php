<?

$dashqualidade = " 

	 
	SELECT
		'dashautogenas' as panel_id,
		'col-md-2' as panel_class_col,
		'TEA / TRA' as panel_title,
		'dashautogenastraenviado' as card_id,
		'col-md-12 col-sm-12 col-xs-12' as card_class_col,
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


UNION ALL
			  
			  
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
	  ".str_replace('idpessoa','l.idpessoa',$pessoas)."
	  
	  
UNION ALL
			
		
	SELECT
		'dashresultados' as panel_id,
		'col-md-4' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosconferencia' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat(
			'?_modulo=',
			uo.idobjeto,
			'&_acao=u&registro_1=&registro_2=&dataregistro_1=&dataregistro_2=&cliente=&teste=&exercicio=&statusres=FECHADO&novajanela=Y'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'conferência' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'RESULTADOS - CONFERÊNCIA' as card_title_modal,
		'?_modulo=conferencia&statusres=FECHADO' as card_url_modal
	from
		vwassinarresultado l
		JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON (
			(
				(`uo`.`tipoobjeto` = 'moduloconferenciaun')
				AND (`l`.`idunidade` = `uo`.`idunidade`)
			)
		)
	where
		l.conferenciares = 'Y'
		and not exists (
			select
				1
			from
				_auditoria aud
			where
				valor = 'CONFERIDO'
				and idauditoria = (
					select
						max(idauditoria) as idauditoria
					from
						_auditoria au
					where
						au.objeto = 'resultado'
						and idobjeto = l.idresultado
						and coluna = 'status'
				)
		)
		and status = 'FECHADO'
		".$dashlotescond."	
		  
		  
UNION ALL

		
	SELECT
		'dashqualidade' as panel_id,
		'col-md-2' as panel_class_col,
		'DOCUMENTOS' as panel_title,
		'dashqualidadedocumentosnaoassinados' as card_id,
		'col-md-12 col-sm-12 col-xs-6' as card_class_col,
		concat(
			'_modulo=documento&_pagina=0&_ordcol=idsgdoc&_orddir=desc&_filtrosrapidos={%22assinaturadoc%22:%22PENDENTE%22,%22status%22:%22APROVADO%22}'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'não assinados' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'DOCUMENTOS - ASSINATURA PENDENTE' as card_title_modal,
		'_modulo=documento&_acao=u' as card_url_modal
	from
		vwsgdoc
	where
		assinaturadoc = 'PENDENTE'
		and status = 'APROVADO'
		".getidempresa('idempresa','sgdoc')."	";
	  		
	
		
		
?>