<?

	
 $dashdiagnostico = "

	SELECT
		'dashamostra' as panel_id,
		'col-md-4' as panel_class_col,
		'AMOSTRAS' as panel_title,
		'dashamostraprovisoria' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat(
			'_modulo=',
			uo.idobjeto,
			'&_pagina=0&_ordcol=idamostra&_orddir=desc&_fds=',
			DATE_FORMAT((curdate() - interval 1 year), '%d/%m/%Y'),
			'-',
			DATE_FORMAT(curdate(), '%d/%m/%Y')
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'provisórias' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'AMOSTRAS - PROVISÓRIAS' as card_title_modal,
		concat('_modulo=', uo.idobjeto, '&_acao=u') as card_url_modal
	from
		amostra l
		JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON (
			(
				(
					`uo`.`tipoobjeto` = 'moduloamostraavesprovisorioun'
				)
				AND (`l`.`idunidade` = `uo`.`idunidade`)
			)
		)
	where
		l.status in ('PROVISORIO') 
		".$dashlotescond."
		
		
UNION ALL


	SELECT
		'dashamostra' as panel_id,
		'col-md-4' as panel_class_col,
		'AMOSTRAS' as panel_title,
		'dashamostraconferencia' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat('form/confereamostra.php') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'conferência' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'AMOSTRAS - CONFERÊNCIA' as card_title_modal,
		'_modulo=conferenciaamostra&_acao=u' as card_url_modal
	from
		amostra l
		join pessoa p on l.idpessoa = p.idpessoa
		JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON (
			(
				(`uo`.`tipoobjeto` = 'moduloamostraun')
				AND (`l`.`idunidade` = `uo`.`idunidade`)
			)
		)
	where
		exists (
			select
				1
			from
				resultado r,
				prodserv pp
			where
				r.idamostra = l.idamostra
				and r.idtipoteste = pp.idprodserv
				and r.status not in ('ASSINADO', 'OFFLINE')
				and pp.conferencia = 'Y'
		)
		and not exists (
			select
				1
			from
				carrimbo c
			where
				c.idobjeto = l.idamostra
				and c.tipoobjeto = 'amostra'
				and c.status = 'CONFERIDO'
		) 
		".$dashlotescond."
		
		
UNION ALL


	SELECT
		'dashresultados' as panel_id,
		'col-md-8' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosematraso' as card_id,
		'col-md-3 col-sm-3 col-xs-6' as card_class_col,
		concat(
			'_modulo=',
			uo.idobjeto,
			'&_pagina=0&_ordcol=idresultado&_orddir=desc&_filtrosrapidos={%22status%22:%22ABERTO,PROCESSANDO%22,%22ematraso%22:%22Y%22}&idresultado=[',
			group_concat(idresultado separator ','),
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
		p.`tipo` = 'SERVICO'
		and r.status in ('PROCESSANDO', 'ABERTO')
		and DATE_ADD(
			DATE_FORMAT(r.criadoem, '%Y-%m-%d'),
			interval p.prazoexec day
		) < CURRENT_DATE 
		".$dashlotescond." 
		".str_replace('idpessoa','l.idpessoa',$pessoas)."
		
	
UNION ALL


	SELECT
		'dashresultados' as panel_id,
		'col-md-8' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosconferencia' as card_id,
		'col-md-3 col-sm-3 col-xs-6' as card_class_col,
		concat(
			'?_modulo=conferencia&_acao=u&registro_1=&registro_2=&dataregistro_1=&dataregistro_2=&cliente=&teste=&exercicio=&statusres=FECHADO&novajanela=Y'
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
		'dashresultados' as panel_id,
		'col-md-8' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosassinatura' as card_id,
		'col-md-3 col-sm-3 col-xs-6' as card_class_col,
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
		'?_modulo=conferencia&statusres=FECHADO' as card_url_modal
	from
		vwassinarresultado l
	where
		status = 'FECHADO'
		and (
			l.idsecretaria = ''
			or l.idsecretaria is null
		) 
		".$dashlotescond."
		
		
UNION ALL


	SELECT
		'dashresultados' as panel_id,
		'col-md-8' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosassinatura' as card_id,
		'col-md-3 col-sm-3 col-xs-6' as card_class_col,
		concat(
			'form/assinarresultado.php?_idrep=2&_acao=u&registro_1=&registro_2=&dataregistro_1=&dataregistro_2=&cliente=&teste=&exercicio=&status=FECHADO&oficial=S&idunidade=',
			$ linkunidade,
			'&novajanela=Y'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'danger' as card_color,
		'danger' as card_border_color,
		'' as card_bg_class,
		'assinatura oficial' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'RESULTADOS OFICIAL - ASSINATURA' as card_title_modal,
		'?_modulo=conferencia&statusres=FECHADO' as card_url_modal
	from
		vwassinarresultado l
	where
		status = 'FECHADO'
		and l.idsecretaria != '' 
		".$dashlotescond."
		
	
UNION ALL


	SELECT
		'dashenvioemail' as panel_id,
		'col-md-2' as panel_class_col,
		'EMAILS NÃO ENVIADOS' as panel_title,
		'dashenvioemailoficial' as card_id,
		'col-md-12 col-sm-12 col-xs-6' as card_class_col,
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
		'Oficiais' as card_title,
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
		AND m.tipoobjeto = 'comunicacaoext'
		";
	  		
	  
?>