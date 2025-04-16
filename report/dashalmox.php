<?

$dashalmox = "

	SELECT
		'dashprodserv' as panel_id,
		'col-md-2' as panel_class_col,
		'PRODUTOS' as panel_title,
		'dashprodservminimo' as card_id,
		'col-md-12 col-sm-12 col-xs-12' as card_class_col,
		concat(
			'_modulo=prodserv&_pagina=0&_ordcol=descr&_orddir=asc&_filtrosrapidos={%22tipo%22:%22PRODUTO%22,%22status%22:%22ATIVO%22}&idprodserv=[',
			group_concat(distinct idprodserv separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (
			count(distinct idprodserv) > 0,
			'danger',
			'success'
		) as card_color,
		if (
			count(distinct idprodserv) > 0,
			'danger',
			'success'
		) as card_border_color,
		'' as card_bg_class,
		'min. zerado' as card_title,
		'' as card_title_sub,
		count(distinct idprodserv) as card_value,
		'fa-print' as card_icon,
		'PRODSERV - MÍNIMO 0' as card_title_modal,
		'_modulo=prodserv&_acao=u' as card_url_modal
	from
		prodserv p
		join unidadeobjeto o on o.tipoobjeto = 'prodserv'
		and o.idobjeto = p.idprodserv
		and o.idunidade = 9
	where
		tipo = 'PRODUTO'
		and status = 'ATIVO'
		and not `p`.`estmin` > 0
		
";  


?>