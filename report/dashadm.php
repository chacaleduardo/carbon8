<?

 $dashadm = "

	 
	SELECT
		'dashadm' as panel_id,
		'col-md-2' as panel_class_col,
		'ADMINISTRATIVO' as panel_title,
		'dashadmnfsafaturar' as card_id,
		'col-md-12 col-sm-12 col-xs-6' as card_class_col,
		concat(
			'_modulo=nfs&_pagina=0&_ordcol=idnf_orddir=desc&_filtrosrapidos={%22status%22:%22FECHADO%22}'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'nfs a faturar' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'ADM -NFS A FATURAR' as card_title_modal,
		'_modulo=nfs&_acao=u' as card_url_modal
	FROM
		vwnf
	where
		status = 'FECHADO'
	

UNION ALL
	

	SELECT
		'dashenvioemail' as panel_id,
		'col-md-10' as panel_class_col,
		'EMAILS NÃO ENVIADOS' as panel_title,
		'dashenvioemaildetalhamento' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
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
		'detalhamento' as card_title,
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
		AND m.tipoobjeto = 'detalhamento'
	
	
UNION ALL
		
		
	SELECT
		'dashenvioemail' as panel_id,
		'col-md-2' as panel_class_col,
		'EMAILS NÃO ENVIADOS' as panel_title,
		'dashenvioemailnfp' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
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
		'NF Produto' as card_title,
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
		AND m.tipoobjeto = 'nfp'
	
	
UNION ALL
	
	
	SELECT
		'dashenvioemail' as panel_id,
		'col-md-2' as panel_class_col,
		'EMAILS NÃO ENVIADOS' as panel_title,
		
		'dashenvioemailnfs' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col, 
		concat('_modulo=envioemail&_pagina=0&_ordcol=idmailfila&_orddir=desc&_filtrosrapidos={%22status%22:%22NAO%20ENVIADO%22}&idmailfila=[',group_concat(distinct m.idmailfila separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_color,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'NF Serviço' as card_title,
		'' as card_title_sub,
		count(distinct m.idmailfila) as card_value,
		'fa-print' as card_icon,
		'EMAILS NÃO ENVIADOS' as card_title_modal,
		'_modulo=envioemail&_acao=u' as card_url_modal
	from
		mailfila m
	LEFT JOIN
		mailfila m2 ON (m.idenvio = m2.idenvio
        AND m.idmailfila < m2.idmailfila)
	WHERE
	
        1 AND m.status IN ('NAO ENVIADO')
        AND m.remover = 'N'
        AND m.tipoobjeto = 'nfs'	


UNION ALL
	
	
SELECT
	'dashenvioemail' as panel_id,
	'col-md-2' as panel_class_col,
	'EMAILS NÃO ENVIADOS' as panel_title,
	'dashenvioemailop' as card_id,
	'col-md-2 col-sm-2 col-xs-6' as card_class_col,
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
	'Orç. Produto' as card_title,
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
	AND m.tipoobjeto = 'orcamentoprod'
	
	
UNION ALL
	
	
SELECT
	'dashenvioemail' as panel_id,
	'col-md-2' as panel_class_col,
	'EMAILS NÃO ENVIADOS' as panel_title,
	'dashenvioemailos' as card_id,
	'col-md-2 col-sm-2 col-xs-6' as card_class_col,
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
	'Orç. Serviço' as card_title,
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
	AND m.tipoobjeto = 'orcamentoserv'
	
	";
	

?>