<?

$dashlotes = "

	
SELECT
		'dashlote' as panel_id,
		'col-md-6' as panel_class_col,
		'".$titulo." LOTES' as panel_title,
		'dashloteexcesso' as card_id,
		'col-md-4 col-sm-4 col-xs-6' as card_class_col, 
		if (vlr is null,'',
		
		concat('_modulo=',modulolink,'&_pagina=0&_ordcol=idprodserv&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO%22}&idlote=[',vids,']')
		)
			
		 as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'lotes em excesso' as card_title,
		'' as card_title_sub,
		if (vlr is null,0,vlr) as card_value,
		'fa-print' as card_icon, 
		'PRODSERV - LOTE EM EXCESSO' as card_title_modal,
		concat('_modulo=',modulolink,'&_acao=u') as card_url_modal
        
   FROM
     ( select group_concat(vids separator ',') as vids, sum(qtd) as vlr, modulolink from
    
    (select group_concat(distinct l.idlote separator ',') as vids, count(distinct l.idlote) as qtd,  uo.idobjeto AS modulolink From
 
      vwlote l
	   
    JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON (((`uo`.`idempresa` = `l`.`idempresa`)
        AND (`uo`.`tipoobjeto` = 'moduloloteun')
        AND (`l`.`idunidade` = `uo`.`idunidade`)))
		
	
    WHERE
	l.especial = 'N' and
        l.status = 'APROVADO' and l.statusfr = 'DISPONÍVEL' AND l.qtddisp >= 0
            AND NOT l.idprodserv = 0
            ".$dashlotescond."  
 
     GROUP BY l.idprodserv
 HAVING COUNT(*) > 2 )a ) a
 
 
 union all
 SELECT
		'dashlote' as panel_id,
		'col-md-4' as panel_class_col,
		'".$titulo." LOTES' as panel_title,
		'dashlotevencido' as card_id,
		'col-md-4 col-sm-4 col-xs-6' as card_class_col, 
		if (count(distinct l.idlote) is null,'',
		
		concat('_modulo=',uo.idobjeto,'&_pagina=0&_ordcol=vencimento&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO%22}&idlote=[',group_concat(distinct l.idlote separator ','),']')
		)
			
		 as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(distinct l.idlote) > 0,'danger','success') as card_color,
		if (count(distinct l.idlote) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'lotes vencidos' as card_title,
		'' as card_title_sub,
		if ( count(distinct l.idlote) is null,0, count(distinct l.idlote)) as card_value,
		'fa-print' as card_icon, 
		'PRODSERV - LOTE EM EXCESSO' as card_title_modal,
		concat('_modulo=',uo.idobjeto,'&_acao=u') as card_url_modal
      
   FROM
       vwlote l
	   
    JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON (((`uo`.`idempresa` = `l`.`idempresa`)
        AND (`uo`.`tipoobjeto` = 'moduloloteun')
        AND (`l`.`idunidade` = `uo`.`idunidade`)))
		
	
    WHERE
	l.especial = 'N' and l.tipo = 'PRODUTO' and
       l.status = 'APROVADO' and l.statusfr = 'DISPONÍVEL' AND l.qtddisp >= 0
		 and  DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d') > l.vencimento
            AND NOT l.idprodserv = 0
			".$dashlotescond." 
			

 
 union all
 SELECT
		'dashlote' as panel_id,
		'col-md-4' as panel_class_col,
		'".$titulo." LOTES' as panel_title,
		'dashlotevencido' as card_id,
		'col-md-4 col-sm-4 col-xs-6' as card_class_col, 
		if (count(distinct l.idlote) is null,'',
		
		concat('_modulo=',uo.idobjeto,'&_pagina=0&_ordcol=vencimento&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO%22}&idlote=[',group_concat(distinct l.idlote separator ','),']')
		)
			
		 as card_url, 
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(distinct l.idlote) > 0,'danger','success') as card_color,
		if (count(distinct l.idlote) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'lotes à vencer' as card_title,
		'Próximos 30 dias' as card_title_sub,
		if ( count(distinct l.idlote) is null,0, count(distinct l.idlote)) as card_value,
		'fa-print' as card_icon, 
		'PRODSERV - LOTE EM EXCESSO' as card_title_modal,
		concat('_modulo=',uo.idobjeto,'&_acao=u') as card_url_modal
      
   FROM
      vwlote l
	   
    JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON (((`uo`.`idempresa` = `l`.`idempresa`)
        AND (`uo`.`tipoobjeto` = 'moduloloteun')
        AND (`l`.`idunidade` = `uo`.`idunidade`)))
		
	
    WHERE
	l.especial = 'N' and l.tipo = 'PRODUTO' and
        l.status = 'APROVADO' and l.statusfr = 'DISPONÍVEL' AND l.qtddisp >= 0
		 and  l.vencimento between DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d') and DATE_ADD(DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d'), interval 30 day) 
            AND NOT l.idprodserv = 0
			".$dashlotescond." 
			
		"; 
		

	
	
?>