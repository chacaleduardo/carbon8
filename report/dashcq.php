<?

$dashcq=" 
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
		'dashresultados' as panel_id,
		'col-md-4' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosematraso' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
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
		JOIN vwtipoteste t ON r.idtipoteste = t.idtipoteste
	where
		r.status in ('PROCESSANDO', 'ABERTO')
		and DATE_ADD(
			DATE_FORMAT(r.criadoem, '%Y-%m-%d'),
			interval p.prazoexec day
		) < CURRENT_DATE 
		".$dashlotescond." 
		".str_replace('idpessoa','l.idpessoa',$pessoas)." "	;
		
			

?>