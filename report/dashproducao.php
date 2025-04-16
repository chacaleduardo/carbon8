<?

		$especial = 'Y';
		$linkunidade = 2;
$dashproducao = "

	SELECT
		panel_id,
		panel_class_col,
		concat('".$titulo." ', panel_title) as panel_title,
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
		card_value as card_value,
		card_icon,
		card_title_modal,
		card_url_modal
	from
		dashboard
	WHERE
		panel_id = 'dashproducaoconcentradosproduzir'
	UNION ALL
	SELECT
		'dashproducaoconcentradosproduzir' as panel_id,
		'col-md-4' as panel_class_col,
		'".$titulo." CONCENTRADOS A PRODUZIR' as panel_title,
		'dashproducaoconcentradosproduzirtriagem' as card_id,
		'col-md-6 col-sm-6 col-xs-12' as card_class_col,
		concat(
			'_modulo=formalizacao&_pagina=0&_ordcol=producao&_orddir=desc&_filtrosrapidos={%22status%22:%22FORMALIZACAO%22,%22especial%22:%22".$especial."%22}&idlote=[',
			group_concat(idlote separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'info' as card_color,
		'info' as card_border_color,
		'' as card_bg_class,
		'FORMALIZAÇÃO' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'CONCENTRADOS ".$titulo." - FORMALIZAÇÃO' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		idtipoprodserv = 19
		and especial = '".$especial."'
		and status IN ('FORMALIZACAO') 
		".getidempresa('idempresa','formalizacao')."
		
		
UNION ALL


	SELECT
		'dashproducaoconcentradosproduzir1' as panel_id,
		'col-md-6' as panel_class_col,
		'".$titulo." CONCENTRADOS A PRODUZIR' as panel_title,
		concat('dashautogenastriagem', t.idtag) as card_id,
		'col-md-4 col-sm-4 col-xs-4' as card_class_col,
		concat(
			'_modulo=formalizacao&_pagina=0&_ordcol=producao&_orddir=desc&_filtrosrapidos={%22status%22:%22PROCESSANDO%22,%22especial%22:%22".$especial."%22}&idlote=[',
			group_concat(l.idlote separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'info' as card_color,
		'info' as card_border_color,
		'' as card_bg_class,
		concat('', t.descricao, '') as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'CONCENTRADOS ".$titulo." - PROCESSANDO' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao l
		join loteativ a on a.idlote = l.idlote
		join loteobj o on o.idloteativ = a.idloteativ
		and o.tipoobjeto = 'tag'
		join tag t on t.idtag = o.idobjeto
	where
		a.idloteativ in (
			select
				*
			from
				(
					select
						idloteativ
					from
						loteativ la
					where
						la.idlote = l.idlote
						and status = 'PENDENTE'
					order by
						ord
					limit
						1
				) b
		)
		and not a.idloteativ is null
		and idtipoprodserv = 19
		and especial = '".$especial."'
		and l.status IN ('PROCESSANDO')
	group by
		t.idtag
		
		
UNION ALL


	SELECT
		'dashproducaoconcentradosproduzir2' as panel_id,
		'col-md-2' as panel_class_col,
		'".$titulo." CONCENTRADOS A PRODUZIR' as panel_title,
		'dashproducaoconcentradosproduzirquarentena' as card_id,
		'col-md-12 col-sm-12 col-xs-6' as card_class_col,
		concat(
			'_modulo=formalizacao&_pagina=0&_ordcol=producao&_orddir=desc&_filtrosrapidos={%22status%22:%22QUARENTENA%22,%22especial%22:%22".$especial."%22}&idlote=[',
			group_concat(idlote separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'info', 'success') as card_color,
		if (count(1) > 0, 'info', 'success') as card_border_color,
		'' as card_bg_class,
		'QUARENTENA' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'CONCENTRADOS ".$titulo." - QUARENTENA' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		idtipoprodserv = 19
		and especial = '".$especial."'
		AND status = 'QUARENTENA' 
		".getidempresa('idempresa','formalizacao')."
		
		
UNION ALL


	SELECT
		'dashproducaoprodutosproduzir' as panel_id,
		'col-md-4' as panel_class_col,
		'".$titulo." PRODUTOS A PRODUZIR - ORGANIZACIONAL' as panel_title,
		'dashautogenasvacinasaproduzirA' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat(
			'_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:%22TRIAGEM%22,%22especial%22:%22".$especial."%22}&idlote=[',
			group_concat(idlote separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'TRIAGEM' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PRODUTOS ".$titulo." - TRIAGEM' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		fabricado = 'Y'
		and venda = 'Y'
		and especial = '".$especial."'
		and idunidade = ".$linkunidade."
		and status = 'TRIAGEM' 
		".getidempresa('idempresa','formalizacao')."
		
		
UNION ALL


	SELECT
		'dashproducaoprodutosproduzir' as panel_id,
		'col-md-4' as panel_class_col,
		'".$titulo." PRODUTOS A PRODUZIR' as panel_title,
		'dashautogenastriagemV1' as card_id,
		'col-md-6 col-sm-6 col-xs-12' as card_class_col,
		concat(
			'_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:%22FORMALIZACAO%22,%22especial%22:%22".$especial."%22}&idlote=[',
			group_concat(idlote separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'info' as card_color,
		'info' as card_border_color,
		'' as card_bg_class,
		'FORMALIZAÇÃO' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PRODUTOS ".$titulo." - FORMALIZAÇÃO' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		fabricado = 'Y'
		and venda = 'Y'
		and especial = '".$especial."'
		and status IN ('FORMALIZACAO') 
		".getidempresa('idempresa','formalizacao')."
		
		
UNION ALL


	SELECT
		'dashproducaoprodutosproduzir1' as panel_id,
		'col-md-6' as panel_class_col,
		'".$titulo." PRODUTOS A PRODUZIR' as panel_title,
		concat('dashautogenastriagem', t.idtag) as card_id,
		'col-md-4 col-sm-4 col-xs-4' as card_class_col,
		concat(
			'_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:%22PROCESSANDO%22,%22especial%22:%22".$especial."%22}&idlote=[',
			group_concat(l.idlote separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'info' as card_color,
		'info' as card_border_color,
		'' as card_bg_class,
		concat('', t.descricao, '') as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PRODUTOS ".$titulo." - PROCESSANDO' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao l
		join loteativ a on a.idlote = l.idlote
		join loteobj o on o.idloteativ = a.idloteativ
		and o.tipoobjeto = 'tag'
		join tag t on t.idtag = o.idobjeto
	where
		a.idloteativ in (
			select
				*
			from
				(
					select
						idloteativ
					from
						loteativ la
					where
						la.idlote = l.idlote
						and status = 'PENDENTE'
					order by
						ord
					limit
						1
				) b
		)
		and not a.idloteativ is null
		and fabricado = 'Y'
		and venda = 'Y'
		and especial = '".$especial."'
		and l.status IN ('PROCESSANDO')
	group by
		t.idtag
		
		
UNION ALL


	SELECT
		'dashproducaoprodutosproduzir2' as panel_id,
		'col-md-2' as panel_class_col,
		'".$titulo." PRODUTOS A PRODUZIR' as panel_title,
		'dashautogenasconcentradoQUARENTENAv' as card_id,
		'col-md-12 col-sm-12 col-xs-6' as card_class_col,
		concat(
			'_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:%22QUARENTENA%22,%22especial%22:%22".$especial."%22}&idlote=[',
			group_concat(idlote separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'info', 'success') as card_color,
		if (count(1) > 0, 'info', 'success') as card_border_color,
		'' as card_bg_class,
		'QUARENTENA' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PRODUTOS ".$titulo." - QUARENTENA' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		fabricado = 'Y'
		and venda = 'Y'
		and especial = '".$especial."'
		AND idunidade = ".$linkunidade."
		and status = 'QUARENTENA' 
		".getidempresa('idempresa','formalizacao')."
		
		
UNION ALL


	SELECT
		'dashproducaodados' as panel_id,
		'col-md-12' as panel_class_col,
		'".$titulo." PRODUZIDOS' as panel_title,
		'dashproducaodadosconcentradosproduzidospassado' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat(
			'_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22,%22especial%22:%22".$especial."%22}&idlote=[',
			group_concat(idlote separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'concentrados' as card_title,
		'mês passado' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'CONCENTRADOS ".$titulo." - MÊS PASSADO' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		idtipoprodserv = 19
		and especial = '".$especial."'
		and fabricacao between last_day(curdate() - interval 2 month) + interval 1 day
		and last_day(curdate() - interval 1 month)
		and status IN ('APROVADO', 'ESGOTADO') 
		".getidempresa('idempresa','formalizacao')."
		
		
UNION ALL


	SELECT
		'dashproducaodados' as panel_id,
		'col-md-12' as panel_class_col,
		'PRODUZIDOS ".$titulo."' as panel_title,
		'dashproducaodadosconcentradosproduzidosmes' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat(
			'_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:% ,ESGOTADO%22,%22idtipoprodserv%22:%2219%22,%22especial%22:%22".$especial."%22}&idlote=[',
			group_concat(idlote separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'concentrados' as card_title,
		'este mês' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'CONCENTRADOS ".$titulo." - ESTE MÊS' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		idtipoprodserv = 19
		and especial = '".$especial."'
		and status IN ('APROVADO', 'ESGOTADO')
		and fabricacao between last_day(curdate() - interval 1 month) + interval 1 day
		and last_day(curdate()) 
		".getidempresa('idempresa','formalizacao')."
		
		
UNION ALL


	SELECT
		'dashproducaodados' as panel_id,
		'col-md-12' as panel_class_col,
		'PRODUZIDOS ".$titulo." - PRODUZIDOS' as panel_title,
		'dashproducaodadosinsumosproduzidosmes' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat(
			'_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22,%22especial%22:%22".$especial."%22}&idlote=[',
			group_concat(idlote separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'produtos' as card_title,
		'mês passado' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'VACINAS ".$titulo." - MÊS PASSADO' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		fabricado = 'Y'
		and venda = 'Y'
		and especial = '".$especial."'
		and fabricacao between last_day(curdate() - interval 2 month) + interval 1 day
		and last_day(curdate() - interval 1 month)
		and status IN ('APROVADO', 'ESGOTADO') 
		".getidempresa('idempresa','formalizacao')."
		
		
UNION ALL


	SELECT
		'dashproducaodados' as panel_id,
		'col-md-12' as panel_class_col,
		'PRODUZIDOS ".$titulo." - PRODUZIDOS' as panel_title,
		'dashproducaodadosinsumosproduzidospassado' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat(
			'_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22,%22especial%22:%22".$especial."%22}&idlote=[',
			group_concat(idlote separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'produtos' as card_title,
		'este mês' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'VACINAS ".$titulo." - ESTE MÊS' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		fabricado = 'Y'
		and venda = 'Y'
		and especial = '".$especial."'
		and status IN ('APROVADO', 'ESGOTADO')
		and fabricacao between last_day(curdate() - interval 1 month) + interval 1 day
		and last_day(curdate()) 
		".getidempresa('idempresa','formalizacao')."
		
		";
	  		
	
	  
?>