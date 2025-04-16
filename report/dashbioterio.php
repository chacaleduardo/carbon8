<?


  $dashbioterio = "
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
		'dashprodserv' as panel_id,
		'col-md-2' as panel_class_col,
		'SERVIÇOS' as panel_title,
		'dashprodservminimo' as card_id,
		'col-md-12 col-sm-12 col-xs-12' as card_class_col,
		concat('?_modulo=rebioensaio&novajanela=Y') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'em atraso' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PRODSERV - MÍNIMO 0' as card_title_modal,
		'_modulo=rebioensaio' as card_url_modal
	from
		servicoensaio l
		join servicobioterio sb
		join analise a
		join nucleo n
		join bioensaio b
		LEFT JOIN (localensaio le) ON (
			b.idbioensaio = le.idbioensaio
			AND le.idlocal > 3
		)
		LEFT JOIN (local lo) ON (lo.idlocal = le.idlocal)
	where
		b.idbioensaio = a.idobjeto
		and b.idespeciefinalidade != 18
		and b.idnucleo = n.idnucleo
		and sb.idservicobioterio = l.idservicobioterio
		and a.objeto = 'bioensaio'
		and a.idanalise = l.idobjeto
		and l.tipoobjeto = 'analise'
		AND l.data BETWEEN CURDATE() - INTERVAL 100 DAY
		AND CURDATE() - INTERVAL 1 DAY
		AND l.status = 'PENDENTE'
		
		
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
			";
			


?>