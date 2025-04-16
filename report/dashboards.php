<?
session_start();
//print_r(getallheaders());die;
 
include_once("../mod/validaacesso.php");
include_once("../inc/php/functions.php");

 
 
 
 
 
	$ss = "SELECT d.iddashboard
			,s.dashboard
			,s.dashboard_title
			FROM laudo.dashboard s
				JOIN "._DBCARBON."._lpobjeto lo on lo.tipoobjeto='dashboard' 
					and lo.idobjeto=d.iddashboard
					and lo.idlp in(".getModsUsr("LPS").")
					where d.status = 'ATIVO'";

        $rs = d::b()->query($ss) or die("Erro ao recuperar snippets: ". mysqli_error(d::b()));

	while($r= mysqli_fetch_assoc($rs)){
		$arrret[$r["idsnippet"]]=$r;
	}
	
	
	
	//if ($_SESSION["SESSAO"]["IDPESSOA"]=="6494" || $_SESSION["SESSAO"]["IDPESSOA"]=="778" ) {  
if(array_key_exists("STRCONTATOCLIENTE", $_SESSION["SESSAO"]) ){
	$pessoas =  " and idpessoa in( ".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].",".$_SESSION["SESSAO"]["IDPESSOA"].") ";
}else{
	$pessoas =  '';
}
 

	

switch ($_GET['_modulo']) {
	case 'dashautogenas':	
		
			$dashlotescond = " and l.idunidade = 9";
			$linkunidade = 9;
	break;
	case 'dashproducao':		
			$dashlotescond = " and l.idunidade = 2 and l.especial = 'N'";	
			$linkunidade = 2;
			$especial = 'N';
			$titulo = '';

	break;
	case 'dashproducaoautogenas':		
			$dashlotescond = " and l.idunidade = 2 and l.especial = 'Y'";	
			$linkunidade = 2;
			$especial = 'Y';
			
			$titulo = 'AUTÓGENAS';
	break;
	case 'dashdiagnostico':
		
		$dashlotescond = " and l.idunidade = 1";
		$linkunidade = 1;
		
	break;
	case 'dashqualidade': 
$dashlotescond = " and l.idunidade = 3";
		$linkunidade = 3;
	break;

	case 'dashlogistica':
	
	break;
	case 'dashti':
	break;
	case 'dashsuprimentos':

	break;
	case 'dashcrm':
		
	break;
	case 'dashadm':
		
	break;
	case 'dashcq':
		$dashlotescond = " and l.idunidade = 10";
		$linkunidade = 10;
	break;
	case 'dashbioterio':
		$dashlotescond = " and l.idunidade = 10";
		$linkunidade = 10;
	break;
	case 'dashped':
	
		$dashlotescond = " and l.idunidade = 14";
		$linkunidade = 14;
		break;
	case 'dashalmox':
	
		$dashlotescond = " and l.idunidade = 8";
		$linkunidade = 8;
		break;
	default:
		$linkunidade = '0';
	
}	
	

  $dashautogenas = "
  
  
	SELECT
		'dashautogenas' as panel_id,
		'col-md-2' as panel_class_col,
		'TEA / TRA' as panel_title,
		'dashautogenastraaberta' as card_id,
		'col-md-12 col-sm-12 col-xs-12' as card_class_col,
		concat('_modulo=',uo.idobjeto,'&_pagina=0&_ordcol=idamostra&_orddir=desc&_filtrosrapidos={%22statustra%22:%22ABERTO%22}&idamostra=[',group_concat(idamostra separator ','),']')as card_url, 
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'TEA / TRA' as card_title,
		'em aberto' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'TEA / TRA - ABERTO' as card_title_modal,
		concat('_modulo=',uo.idobjeto,'&_acao=u') as card_url_modal
	from
	
		 amostra l
	
		
	 JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON (`uo`.`tipoobjeto` = 'modulo' AND `l`.`idunidade` = `uo`.`idunidade`)
	 JOIN carbonnovo._modulo m on m.modulo = uo.idobjeto and m.modulotipo = 'amostra'
	where
		l.statustra in ('ABERTO')  

		  ".$dashlotescond."
		  union all
  
  
  SELECT
		'dashproducaoconcentrado' as panel_id,
		'col-md-6' as panel_class_col,
		'PRODUÇÃO DE CONCENTRADOS' as panel_title,
		'concentradosaproduzir' as card_id,
		'col-md-4 col-sm-4 col-xs-6' as card_class_col,
		'_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&_filtrosrapidos={%22status%22:%22TRIAGEM%22,%22idtipoprodserv%22,%2219%22,%22especial%22:%22N%22}' as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'a produzir' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PRODUÇÃO DE CONCENTRADOS - A PRODUZIR' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		idtipoprodserv = 19  and
        especial = 'N' and
		idunidade = ".$linkunidade." and
        status = 'TRIAGEM'
		".getidempresa('idempresa','formalizacao')."	
	union all
	SELECT
		'dashproducaoconcentrado' as panel_id,
		'col-md-6' as panel_class_col,
		'PRODUÇÃO DE CONCENTRADOS' as panel_title,
		'concentradosmespassado' as card_id,
		'col-md-4 col-sm-4 col-xs-6' as card_class_col,
		concat('_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22,%22idtipoprodserv%22,%2219%22,%22especial%22:%22N%22}&_fds=', DATE_FORMAT(last_day(curdate() - interval 2 month) + interval 1 day,'%d/%m/%Y'),'-',DATE_FORMAT(last_day(curdate() - interval 1 month),'%d/%m/%Y')) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'mês passado' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PRODUÇÃO DE CONCENTRADOS - MÊS PASSADO' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		idtipoprodserv = 19  and
		especial = 'N' 
        and fabricacao between last_day(curdate() - interval 2 month) + interval 1 day and last_day(curdate() - interval 1 month)
        and status in ('APROVADO','ESGOTADO')
		".getidempresa('idempresa','formalizacao')."	
	union all
    SELECT
		'dashproducaoconcentrado' as panel_id,
		'col-md-6' as panel_class_col,
		'PRODUÇÃO DE CONCENTRADOS' as panel_title,
		'concentradosestemes' as card_id,
		'col-md-4 col-sm-4 col-xs-6' as card_class_col,
		concat('_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22,%22idtipoprodserv%22:%2219%22,%22especial%22:%22N%22}&_fds=', DATE_FORMAT(last_day(curdate() - interval 1 month) + interval 1 day,'%d/%m/%Y'),'-',DATE_FORMAT(last_day(curdate()),'%d/%m/%Y')) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'este mês' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PRODUÇÃO DE CONCENTRADOS - ESTE MÊS' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		idtipoprodserv = 19 and
		especial = 'N' 
		and status in ('APROVADO','ESGOTADO')
        and fabricacao between last_day(curdate() - interval 1 month) + interval 1 day and  last_day(curdate())
		".getidempresa('idempresa','formalizacao')."	
		
		UNION ALL
		
	SELECT
		'dashproducaovacina' as panel_id,
		'col-md-6' as panel_class_col,
		'VACINAS' as panel_title,
		'vacinasaproduzir' as card_id,
		'col-md-4 col-sm-4 col-xs-6' as card_class_col,
		'_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&_filtrosrapidos={%22status%22:%22TRIAGEM%22,%22especial%22:%22N%22}' as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'a produzir' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'VACINAS - A PRODUZIR' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal		
	from
		vwformalizacao
	where
		fabricado = 'Y' and venda = 'Y' and
        especial = 'N' and
		idunidade = ".$linkunidade." and
        status = 'TRIAGEM'
		".getidempresa('idempresa','formalizacao')."	
	union all
	SELECT
		'dashproducaovacina' as panel_id,
		'col-md-6' as panel_class_col,
		'VACINAS' as panel_title,
		'vacinasmespassado' as card_id,
		'col-md-4 col-sm-4 col-xs-6' as card_class_col,
		concat('_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22,%22especial%22:%22N%22}&_fds=', DATE_FORMAT(last_day(curdate() - interval 2 month) + interval 1 day,'%d/%m/%Y'),'-',DATE_FORMAT(last_day(curdate() - interval 1 month),'%d/%m/%Y')) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'mês passado' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'VACINAS - MÊS PASSADO' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		fabricado = 'Y' and venda = 'Y' and
		especial = 'N' 
        and fabricacao between last_day(curdate() - interval 2 month) + interval 1 day and last_day(curdate() - interval 1 month)
        and status in ('APROVADO','ESGOTADO')
		".getidempresa('idempresa','formalizacao')."	
	union all
    SELECT
		'dashproducaovacina' as panel_id,
		'col-md-6' as panel_class_col,
		'VACINAS - PRODUZIDOS' as panel_title,
		'vacinasestemes' as card_id,
		'col-md-4 col-sm-4 col-xs-6' as card_class_col,
		concat('_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22,%22especial%22:%22N%22}&_fds=', DATE_FORMAT(last_day(curdate() - interval 1 month) + interval 1 day,'%d/%m/%Y'),'-',DATE_FORMAT(last_day(curdate()),'%d/%m/%Y')) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'este mês' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'VACINAS - ESTE MÊS' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
	from
		vwformalizacao
	where
		fabricado = 'Y' and 
		venda = 'Y' and
		especial = 'N' 
		and status in ('APROVADO','ESGOTADO')
        and fabricacao between last_day(curdate() - interval 1 month) + interval 1 day and  last_day(curdate())
		".getidempresa('idempresa','formalizacao')."		";
		
		
	$dashproducao = "

		SELECT
		panel_id,
		panel_class_col,
		concat(	'".$titulo." ',panel_title) as panel_title,
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
			union all
    SELECT
		'dashproducaoconcentradosproduzir' as panel_id,
		'col-md-4' as panel_class_col,
		'".$titulo." CONCENTRADOS A PRODUZIR' as panel_title,
		'dashproducaoconcentradosproduzirtriagem' as card_id,
		'col-md-6 col-sm-6 col-xs-12' as card_class_col,
		concat('_modulo=formalizacao&_pagina=0&_ordcol=producao&_orddir=desc&_filtrosrapidos={%22status%22:%22FORMALIZACAO%22,%22especial%22:%22".$especial."%22}&idlote=[',group_concat(idlote separator ','),']') as card_url,
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
		idtipoprodserv = 19 and
		especial = '".$especial."' 
		and status IN ('FORMALIZACAO')
      
		".getidempresa('idempresa','formalizacao')."
		
	
	
	
	
	
UNION ALL
	
	 SELECT
		'dashproducaoconcentradosproduzir1' as panel_id,
		'col-md-6' as panel_class_col,
		'".$titulo." CONCENTRADOS A PRODUZIR' as panel_title,
		concat('dashautogenastriagem',t.idtag) as card_id,
		'col-md-4 col-sm-4 col-xs-4' as card_class_col,
		concat('_modulo=formalizacao&_pagina=0&_ordcol=producao&_orddir=desc&_filtrosrapidos={%22status%22:%22PROCESSANDO%22,%22especial%22:%22".$especial."%22}&idlote=[',group_concat(l.idlote separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'info' as card_color,
		'info' as card_border_color,
		'' as card_bg_class,
		concat('',t.descricao,'') as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'CONCENTRADOS ".$titulo." - PROCESSANDO' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
        
	from
		vwformalizacao l
join loteativ a on a.idlote = l.idlote
 join loteobj o on o.idloteativ = a.idloteativ and o.tipoobjeto = 'tag' 
         join tag t on t.idtag = o.idobjeto
        where a.idloteativ in (
        select * from (select idloteativ from loteativ la where la.idlote = l.idlote and status = 'PENDENTE' order by ord limit 1)b)
       and not a.idloteativ is null
       
	and
		idtipoprodserv = 19 and
		especial = '".$especial."' 
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
		concat('_modulo=formalizacao&_pagina=0&_ordcol=producao&_orddir=desc&_filtrosrapidos={%22status%22:%22QUARENTENA%22,%22especial%22:%22".$especial."%22}&idlote=[',group_concat(idlote separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'info','success') as card_color,
		if (count(1) > 0,'info','success') as card_border_color,
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
		idtipoprodserv = 19 and
		especial = '".$especial."'  AND
        status = 'QUARENTENA'
		".getidempresa('idempresa','formalizacao')."	
	
		
		UNION ALL
		
	SELECT
		'dashproducaoprodutosproduzir' as panel_id,
		'col-md-4' as panel_class_col,
		'".$titulo." PRODUTOS A PRODUZIR - ORGANIZACIONAL' as panel_title,
		'dashautogenasvacinasaproduzirA' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat('_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:%22TRIAGEM%22,%22especial%22:%22".$especial."%22}&idlote=[',group_concat(idlote separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
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
		fabricado = 'Y' and venda = 'Y' and
        especial = '".$especial."' and 
		idunidade = ".$linkunidade." and
        status = 'TRIAGEM'
		".getidempresa('idempresa','formalizacao')."	
		
	
		
union all
    SELECT
		'dashproducaoprodutosproduzir' as panel_id,
		'col-md-4' as panel_class_col,
		'".$titulo." PRODUTOS A PRODUZIR' as panel_title,
		'dashautogenastriagemV1' as card_id,
		'col-md-6 col-sm-6 col-xs-12' as card_class_col,
		concat('_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:%22FORMALIZACAO%22,%22especial%22:%22".$especial."%22}&idlote=[',group_concat(idlote separator ','),']') as card_url,
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
		fabricado = 'Y' and venda = 'Y' and
		especial = '".$especial."'  
		and status IN ('FORMALIZACAO')
      
		".getidempresa('idempresa','formalizacao')."
		
	
	
		
UNION ALL
	
	 SELECT
		'dashproducaoprodutosproduzir1' as panel_id,
		'col-md-6' as panel_class_col,
		'".$titulo." PRODUTOS A PRODUZIR' as panel_title,
		concat('dashautogenastriagem',t.idtag) as card_id,
		'col-md-4 col-sm-4 col-xs-4' as card_class_col,
		concat('_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:%22PROCESSANDO%22,%22especial%22:%22".$especial."%22}&idlote=[',group_concat(l.idlote separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'info' as card_color,
		'info' as card_border_color,
		'' as card_bg_class,
		concat('',t.descricao,'') as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PRODUTOS ".$titulo." - PROCESSANDO' as card_title_modal,
		'_modulo=formalizacao&_acao=u' as card_url_modal
        
	from
		vwformalizacao l
join loteativ a on a.idlote = l.idlote
 join loteobj o on o.idloteativ = a.idloteativ and o.tipoobjeto = 'tag' 
         join tag t on t.idtag = o.idobjeto
        where a.idloteativ in (
        select * from (select idloteativ from loteativ la where la.idlote = l.idlote and status = 'PENDENTE' order by ord limit 1)b)
       and not a.idloteativ is null
       
	and
		fabricado = 'Y' and venda = 'Y' and
		especial = '".$especial."' 
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
		concat('_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:%22QUARENTENA%22,%22especial%22:%22".$especial."%22}&idlote=[',group_concat(idlote separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'info','success') as card_color,
		if (count(1) > 0,'info','success') as card_border_color,
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
		fabricado = 'Y' and venda = 'Y' and
		especial = '".$especial."'  AND
		idunidade = ".$linkunidade." and
        status = 'QUARENTENA'
		".getidempresa('idempresa','formalizacao')."		
		
		
union all


	SELECT
		'dashproducaodados' as panel_id,
		'col-md-12' as panel_class_col,
		'".$titulo." PRODUZIDOS' as panel_title,
		'dashproducaodadosconcentradosproduzidospassado' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat('_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22,%22especial%22:%22".$especial."%22}&idlote=[',group_concat(idlote separator ','),']') as card_url,
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
		idtipoprodserv = 19 and
		especial = '".$especial."'  
        and fabricacao between last_day(curdate() - interval 2 month) + interval 1 day and last_day(curdate() - interval 1 month)
        and status IN ('APROVADO','ESGOTADO')
		".getidempresa('idempresa','formalizacao')."	
		
	union all
    SELECT
		'dashproducaodados' as panel_id,
		'col-md-12' as panel_class_col,
		'PRODUZIDOS ".$titulo."' as panel_title,
		'dashproducaodadosconcentradosproduzidosmes' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat('_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:% ,ESGOTADO%22,%22idtipoprodserv%22:%2219%22,%22especial%22:%22".$especial."%22}&idlote=[',group_concat(idlote separator ','),']') as card_url,
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
		idtipoprodserv = 19 and
		especial = '".$especial."' 
		and status IN ('APROVADO','ESGOTADO')
        and fabricacao between last_day(curdate() - interval 1 month) + interval 1 day and  last_day(curdate())
		".getidempresa('idempresa','formalizacao')."	

union all
		
		
	SELECT
		'dashproducaodados' as panel_id, 
		'col-md-12' as panel_class_col,
		'PRODUZIDOS ".$titulo." - PRODUZIDOS' as panel_title,
		'dashproducaodadosinsumosproduzidosmes' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat('_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22,%22especial%22:%22".$especial."%22}&idlote=[',group_concat(idlote separator ','),']') as card_url,
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
		fabricado = 'Y' and venda = 'Y' and
		especial = '".$especial."' 
        and fabricacao between last_day(curdate() - interval 2 month) + interval 1 day and last_day(curdate() - interval 1 month)
        and status IN ('APROVADO','ESGOTADO')
		".getidempresa('idempresa','formalizacao')."	
		
	union all
    SELECT
		'dashproducaodados' as panel_id,
		'col-md-12' as panel_class_col,
		'PRODUZIDOS ".$titulo." - PRODUZIDOS' as panel_title,
		'dashproducaodadosinsumosproduzidospassado' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat('_modulo=formalizacao&_pagina=0&_ordcol=envio&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22,%22especial%22:%22".$especial."%22}&idlote=[',group_concat(idlote separator ','),']') as card_url,
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
		fabricado = 'Y' and venda = 'Y' and
		especial = '".$especial."' 
		and status IN ('APROVADO','ESGOTADO')
        and fabricacao between last_day(curdate() - interval 1 month) + interval 1 day and  last_day(curdate())
		".getidempresa('idempresa','formalizacao')."
";
	
	
	
	$dashdiagnostico = "
	 
	
		
	
	SELECT
		'dashamostra' as panel_id,
		'col-md-4' as panel_class_col,
		'AMOSTRAS' as panel_title,
		'dashamostraprovisoria' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat('_modulo=',uo.idobjeto,'&_pagina=0&_ordcol=idamostra&_orddir=desc&_fds=', DATE_FORMAT((curdate() - interval 1 year),'%d/%m/%Y'),'-',DATE_FORMAT(curdate(),'%d/%m/%Y'))as card_url, 
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
		concat('_modulo=',uo.idobjeto,'&_acao=u') as card_url_modal
	from
	
		 amostra l
	
		
	 JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON ((
         (`uo`.`tipoobjeto` = 'moduloamostraavesprovisorioun')
        AND (`l`.`idunidade` = `uo`.`idunidade`)))
	where
		l.status in ('PROVISORIO')
       

		  ".$dashlotescond."
		  
		  
		  union all
	
	
	SELECT
		'dashamostra' as panel_id,
		'col-md-4' as panel_class_col,
		'AMOSTRAS' as panel_title,
		'dashamostraconferencia' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat('form/confereamostra.php')as card_url, 
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'conferência' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'AMOSTRAS - CONFERÊNCIA' as card_title_modal,
		'_modulo=conferenciaamostra&_acao=u' as card_url_modal
		from amostra l
		join pessoa p on l.idpessoa = p.idpessoa
		 JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON ((
         (`uo`.`tipoobjeto` = 'moduloamostraun')
        AND (`l`.`idunidade` = `uo`.`idunidade`)))
			    where
                           
			   
			     exists (select 1 from resultado r,prodserv pp where r.idamostra = l.idamostra and r.idtipoteste=pp.idprodserv and r.status not in ('ASSINADO','OFFLINE') and pp.conferencia='Y' 
				)
			    and not exists (select 1 from carrimbo c where c.idobjeto = l.idamostra and c.tipoobjeto='amostra' and c.status='CONFERIDO')


       

		  ".$dashlotescond."
		  union all
		  
		  SELECT
		'dashresultados' as panel_id,
		'col-md-8' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosematraso' as card_id,
		'col-md-3 col-sm-3 col-xs-6' as card_class_col,
		concat('_modulo=',uo.idobjeto,'&_pagina=0&_ordcol=idresultado&_orddir=desc&_filtrosrapidos={%22status%22:%22ABERTO,PROCESSANDO%22,%22ematraso%22:%22Y%22}&idresultado=[',group_concat(idresultado separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'em atraso' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'RESULTADOS - EM ATRASO' as card_title_modal,
		concat('_modulo=',uo.idobjeto,'&_acao=u') as card_url_modal
	from
		resultado r
		JOIN amostra l on l.idamostra = r.idamostra
	JOIN
		prodserv p on p.idprodserv = r.idtipoteste
	 JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON ((
         (`uo`.`tipoobjeto` = 'moduloresultun')
        AND (`l`.`idunidade` = `uo`.`idunidade`)))
	where
		p.`tipo` = 'SERVICO' and
		r.status in ('PROCESSANDO','ABERTO')
        and DATE_ADD(DATE_FORMAT(r.criadoem,'%Y-%m-%d'), interval p.prazoexec day) < CURRENT_DATE

		  ".$dashlotescond."
			  ".str_replace('idpessoa','l.idpessoa',$pessoas)."

	  union all
	
	
	SELECT
		'dashresultados' as panel_id,
		'col-md-8' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosconferencia' as card_id,
		'col-md-3 col-sm-3 col-xs-6' as card_class_col,
		concat('?_modulo=conferencia&_acao=u&registro_1=&registro_2=&dataregistro_1=&dataregistro_2=&cliente=&teste=&exercicio=&statusres=FECHADO&novajanela=Y') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'conferência' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'RESULTADOS - CONFERÊNCIA' as card_title_modal,
		'?_modulo=conferencia&statusres=FECHADO' as card_url_modal
		from vwassinarresultado l 
			where
			 l.conferenciares='Y'
            and  not exists (select 1 from _auditoria aud where valor = 'CONFERIDO' and idauditoria = (select max(idauditoria) as idauditoria from _auditoria au where au.objeto = 'resultado' and idobjeto = l.idresultado and coluna = 'status'))
			and status = 'FECHADO' 
 
       

		  ".$dashlotescond."	
		  
		  union all
		  
		  	SELECT
		'dashresultados' as panel_id,
		'col-md-8' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosassinatura' as card_id,
		'col-md-3 col-sm-3 col-xs-6' as card_class_col,
		concat('form/assinarresultado.php?_idrep=2&_acao=u&registro_1=&registro_2=&dataregistro_1=&dataregistro_2=&cliente=&teste=&exercicio=&status=FECHADO&idunidade=',$linkunidade,'&novajanela=Y') as card_url,
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
		from vwassinarresultado l 
			where
		status = 'FECHADO'
 and  (l.idsecretaria = '' or l.idsecretaria is null)
		  ".$dashlotescond."	
		  
		  
		  	  union all
		  
		  	SELECT
		'dashresultados' as panel_id,
		'col-md-8' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosassinatura' as card_id,
		'col-md-3 col-sm-3 col-xs-6' as card_class_col,
		concat('form/assinarresultado.php?_idrep=2&_acao=u&registro_1=&registro_2=&dataregistro_1=&dataregistro_2=&cliente=&teste=&exercicio=&status=FECHADO&oficial=S&idunidade=',$linkunidade,'&novajanela=Y') as card_url,
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
		from vwassinarresultado l 
			where
		status = 'FECHADO'
		and l.idsecretaria != '' 
		
		  ".$dashlotescond."	

	
union all
	
	SELECT
		'dashenvioemail' as panel_id,
		'col-md-2' as panel_class_col,
		'EMAILS NÃO ENVIADOS' as panel_title,
		'dashenvioemailoficial' as card_id,
		'col-md-12 col-sm-12 col-xs-6' as card_class_col, 
		concat('_modulo=envioemail&_pagina=0&_ordcol=idmailfila&_orddir=desc&_filtrosrapidos={%22status%22:%22NAO%20ENVIADO%22}&idmailfila=[',group_concat(distinct m.idmailfila separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_color,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'Oficiais' as card_title,
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
		-- m2.idmailfila IS NULL
        1 AND m.status IN ('NAO ENVIADO')
        AND m.remover = 'N'
        AND m.tipoobjeto = 'comunicacaoext'	
	
	
	";
	
	
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



	$dashqualidade = " 
	

	SELECT
		'dashautogenas' as panel_id,
		'col-md-2' as panel_class_col,
		'TEA / TRA' as panel_title,
		'dashautogenastraenviado' as card_id,
		'col-md-12 col-sm-12 col-xs-12' as card_class_col,
		concat('_modulo=',uo.idobjeto,'&_pagina=0&_ordcol=idamostra&_orddir=desc&_filtrosrapidos={%22statustra%22:%22ENVIADO%22}&idamostra=[',group_concat(idamostra separator ','),']')as card_url, 
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
		concat('_modulo=',uo.idobjeto,'&_acao=u') as card_url_modal
	from
	
		 amostra l
	
		
	 JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON (`uo`.`tipoobjeto` = 'modulo' AND `l`.`idunidade` = `uo`.`idunidade`)
	 JOIN carbonnovo._modulo m on m.modulo = uo.idobjeto and m.modulotipo = 'amostra'
	where
		l.statustra in ('ENVIADO')  

		 and l.idunidade = 9
		  union all
		  
		  SELECT
		'dashresultados' as panel_id,
		'col-md-4' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosematraso' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat('_modulo=',uo.idobjeto,'&_pagina=0&_ordcol=idresultado&_orddir=desc&_filtrosrapidos={%22status%22:%22ABERTO,PROCESSANDO%22,%22ematraso%22:%22Y%22}') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'em atraso' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'RESULTADOS - EM ATRASO' as card_title_modal,
		concat('_modulo=',uo.idobjeto,'&_acao=u') as card_url_modal
	from
		resultado r
		JOIN amostra l on l.idamostra = r.idamostra
	JOIN
		prodserv p on p.idprodserv = r.idtipoteste
	 JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON ((
         (`uo`.`tipoobjeto` = 'moduloresultun')
        AND (`l`.`idunidade` = `uo`.`idunidade`)))
	where
		r.status in ('PROCESSANDO','ABERTO')
        and DATE_ADD(DATE_FORMAT(r.criadoem,'%Y-%m-%d'), interval p.prazoexec day) < CURRENT_DATE

		  ".$dashlotescond."
				  ".str_replace('idpessoa','l.idpessoa',$pessoas)."
 union all
			
	SELECT
		'dashresultados' as panel_id,
		'col-md-4' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosconferencia' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat('?_modulo=',uo.idobjeto,'&_acao=u&registro_1=&registro_2=&dataregistro_1=&dataregistro_2=&cliente=&teste=&exercicio=&statusres=FECHADO&novajanela=Y') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'conferência' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'RESULTADOS - CONFERÊNCIA' as card_title_modal,
		'?_modulo=conferencia&statusres=FECHADO' as card_url_modal
		from vwassinarresultado l 
		JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON ((
         (`uo`.`tipoobjeto` = 'moduloconferenciaun')
        AND (`l`.`idunidade` = `uo`.`idunidade`)))
			where
			 l.conferenciares='Y'
            and  not exists (select 1 from _auditoria aud where valor = 'CONFERIDO' and idauditoria = (select max(idauditoria) as idauditoria from _auditoria au where au.objeto = 'resultado' and idobjeto = l.idresultado and coluna = 'status'))
			and status = 'FECHADO' 
 
       

		  ".$dashlotescond."	
		  
		  
		  union all
	SELECT
		'dashqualidade' as panel_id,
		'col-md-2' as panel_class_col,
		'DOCUMENTOS' as panel_title,
		'dashqualidadedocumentosnaoassinados' as card_id,
		'col-md-12 col-sm-12 col-xs-6' as card_class_col,
		concat('_modulo=documento&_pagina=0&_ordcol=idsgdoc&_orddir=desc&_filtrosrapidos={%22assinaturadoc%22:%22PENDENTE%22,%22status%22:%22APROVADO%22}') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
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
		assinaturadoc = 'PENDENTE' and status = 'APROVADO'
		".getidempresa('idempresa','sgdoc')."	";
		
		
	$dashcq="  SELECT
		'dashresultados' as panel_id,
		'col-md-4' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosematraso' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat('_modulo=',uo.idobjeto,'&_pagina=0&_ordcol=idresultado&_orddir=desc&_filtrosrapidos={%22status%22:%22ABERTO,PROCESSANDO%22,%22ematraso%22:%22Y%22}&idresultado=[',group_concat(idresultado separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'em atraso' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'RESULTADOS - EM ATRASO' as card_title_modal,
		concat('_modulo=',uo.idobjeto,'&_acao=u') as card_url_modal
	from
		resultado r
		JOIN amostra l on l.idamostra = r.idamostra
	JOIN
		prodserv p on p.idprodserv = r.idtipoteste
	 JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON ((
         (`uo`.`tipoobjeto` = 'moduloresultun')
        AND (`l`.`idunidade` = `uo`.`idunidade`)))
		JOIN vwtipoteste t ON r.idtipoteste = t.idtipoteste
	where
		r.status in ('PROCESSANDO','ABERTO')
        and DATE_ADD(DATE_FORMAT(r.criadoem,'%Y-%m-%d'), interval p.prazoexec day) < CURRENT_DATE

		  ".$dashlotescond."
				  ".str_replace('idpessoa','l.idpessoa',$pessoas)."
 "	;
		
	$dashlogistica = "  SELECT
		'dashlogistica' as panel_id,
		'col-md-4' as panel_class_col,
		'LOGÍSTICA - ENVIOS' as panel_title,
		'dashlogisticaenviosemandamento' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat('_modulo=pedidologistica&_pagina=0&_ordcol=idnf&_orddir=desc&_filtrosrapidos={%22status%22:%22ENVIADO%22}') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
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
		 
		 status = 'ENVIADO'   ".getidempresa('idempresa','pedidologistica')."  
		
	
union all 
  SELECT
		'dashlogistica' as panel_id,
		'col-md-6' as panel_class_col,
		'LOGÍSTICA - ENVIOS' as panel_title,
		'dashlogisticaenviosematraso' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat('_modulo=pedidologistica&_pagina=0&_ordcol=idnf&_orddir=desc&_filtrosrapidos={%22status%22:%22ENVIAR,ENVIADO%22}&idnf=[',group_concat(idnf separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
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
		status in ('ENVIADO', 'ENVIAR') ".getidempresa('idempresa','pedidologistica')."	
		
	and DATE_ADD(DATE_FORMAT(envio,'%Y-%m-%d'), interval 3 day) < CURRENT_DATE ";
		
		
$dashti = "
SELECT
		'dashti' as panel_id,
		'col-md-8' as panel_class_col,
		'EVENTOS - TI' as panel_title,
		'dashtilocalS' as card_id,
		'col-md-3 col-sm-4 col-xs-6' as card_class_col, 
		concat('_modulo=evento&_pagina=0&_ordcol=prazoamd&_orddir=asc&_filtrosrapidos={%22ideventotipo%22:%2221%22}&idevento=[',group_concat(idevento separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'SUPORTE TI' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'SUPORTE TI' as card_title_modal,
		'_modulo=evento&_acao=u' as card_url_modal
	from
		evento `e`
		   JOIN "._DBCARBON."._status `est` ON ((`est`.`idstatus` = `e`.`idstatus`))
	where
	`e`.`idstatus`    in (7,29,38,2,3) and e.ideventotipo in ( 21)
union all
SELECT
		'dashti' as panel_id,
		'col-md-2' as panel_class_col,
		'EVENTOS - TI' as panel_title,
		'dashticorrecao' as card_id,
		'col-md-3 col-sm-4 col-xs-6' as card_class_col, 
		concat('_modulo=evento&_pagina=0&_ordcol=prazoamd&_orddir=asc&_filtrosrapidos={%22ideventotipo%22:%2228,53%22}&idevento=[',group_concat(idevento separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'CORRETIVAS' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'SUPORTE TI - CORREÇÃO' as card_title_modal,
		'_modulo=evento&_acao=u' as card_url_modal
	from
		evento `e`
		   JOIN "._DBCARBON."._status `est` ON ((`est`.`idstatus` = `e`.`idstatus`))
	where
	`e`.`idstatus`  in (3,2,8,38,29,7) and e.ideventotipo in ( 28,53)
	
	union all
	SELECT
		'dashti' as panel_id,
		'col-md-2' as panel_class_col,
		'EVENTOS - TI' as panel_title,
		'dashtiprojetos' as card_id,
		'col-md-3 col-sm-4 col-xs-6' as card_class_col, 
		concat('_modulo=evento&_pagina=0&_ordcol=prazoamd&_orddir=asc&_filtrosrapidos={%22ideventotipo%22:%2240%22}&idevento=[',group_concat(idevento separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'PROJETOS' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'SUPORTE TI - PROJETOS' as card_title_modal,
		'_modulo=evento&_acao=u' as card_url_modal
	from
		evento `e`
		   JOIN "._DBCARBON."._status `est` ON ((`est`.`idstatus` = `e`.`idstatus`))
	where
	not `e`.`idstatus`   in (4,6,46) and e.ideventotipo in ( 40) 
	
	
	union all
	SELECT
		'dashti' as panel_id,
		'col-md-8' as panel_class_col,
		'EVENTOS - TI' as panel_title,
		'dashtiematraso' as card_id,
		'col-md-3 col-sm-3 col-xs-6' as card_class_col, 
		concat('_modulo=evento&_pagina=0&_ordcol=prazoamd&_orddir=asc&_filtrosrapidos={%22ideventotipo%22:%2240,21,28%22}&idevento=[',group_concat(idevento separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'EM ATRASO' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'SUPORTE TI - PROJETOS' as card_title_modal,
		'_modulo=evento&_acao=u' as card_url_modal
	from
		evento `e`
		   JOIN "._DBCARBON."._status `est` ON ((`est`.`idstatus` = `e`.`idstatus`))
	where
	(`e`.`idstatus`   in (7,29,38,2,3) and e.ideventotipo in (21) 
	and DATE_FORMAT(prazo,'%Y-%m-%d') < CURRENT_DATE
	)
	or
	(`e`.`idstatus`   in (7,38,2,3) and e.ideventotipo in (28) 
	and DATE_FORMAT(prazo,'%Y-%m-%d') < CURRENT_DATE
	)
	or
	(`e`.`idstatus`   in (29,2,7,32,45,55,3) and e.ideventotipo in (28) 
	and DATE_FORMAT(prazo,'%Y-%m-%d') < CURRENT_DATE
	)
	
	
	UNION ALL
	SELECT
		'dashenvioemail' as panel_id,
		'col-md-4' as panel_class_col,
		'EMAILS NÃO ENVIADOS' as panel_title,
		
		'dashenvioemailtodos' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col, 
		concat('_modulo=envioemail&_pagina=0&_ordcol=idmailfila&_orddir=desc&_filtrosrapidos={%22status%22:%22NAO%20ENVIADO%22}&idmailfila=[',group_concat(distinct m.idmailfila separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_color,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'pendente' as card_title,
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
		-- m2.idmailfila IS NULL
        1 AND m.status IN ('NAO ENVIADO')
        AND m.remover = 'N'
	union all
	
	select * from (
 select 
		CONCAT('dashtilocal') as panel_id,
		'col-md-12' as panel_class_col,
		'SUPORTE TI' as panel_title,
		concat('dashtilocal', FLOOR((RAND() * 1000))) as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat('_modulo=evento&_pagina=0&_ordcol=prazoamd&_orddir=asc&idevento=[',group_concat(idevento separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'secundary' as card_color,
		'secundary' as card_border_color,
		'' as card_bg_class,
		if (length(classificacao) > 0, classificacao, '- SEM CLASSIFICAÇÃO -') as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		concat('SUPORTE - ',upper(classificacao)) as card_title_modal,
		'_modulo=evento&_acao=u' as card_url_modal
 from (

SELECT 
    idevento, servico as classificacao  
    
FROM
   evento e
     JOIN "._DBCARBON."._status `est` ON ((`est`.`idstatus` = `e`.`idstatus`))
	where
	`e`.`idstatus`  in (7,29,38,2,3) and e.ideventotipo in ( 21) 


) a  group by classificacao order by classificacao ) a

union all
	select * from (
 select 
		CONCAT('dashticorretivas') as panel_id,
		'col-md-12' as panel_class_col,
		'CORRETIVAS' as panel_title,
		concat('dashticorretivas', FLOOR((RAND() * 1000))) as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat('_modulo=evento&_pagina=0&_ordcol=prazoamd&_orddir=asc&idevento=[',group_concat(idevento separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'secundary' as card_color,
		'secundary' as card_border_color,
		'' as card_bg_class,
		if (length(classificacao) > 0, classificacao, '- SEM CLASSIFICAÇÃO -') as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		concat('CORRETIVAS - ',upper(classificacao)) as card_title_modal,
		'_modulo=evento&_acao=u' as card_url_modal
 from (

SELECT 
    idevento, classificacao
    
FROM
   evento e
     JOIN "._DBCARBON."._status `est` ON ((`est`.`idstatus` = `e`.`idstatus`))
	where
	`e`.`idstatus`  in (3,2,8,38,29,7) and e.ideventotipo in ( 28,53) 


) a  group by classificacao order by classificacao ) a
		
		
union all
	select * from (
 select 
		CONCAT('dashtitags') as panel_id,
		'col-md-12' as panel_class_col,
		'TAGS' as panel_title,
		concat('dashtitags', FLOOR((RAND() * 1000))) as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat('_modulo=tag&_pagina=0&_ordcol=tag&_orddir=desc&idtag=[',group_concat(idtag separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'secundary','danger') as card_color,
		if (count(1) > 0,'secundary','danger') as card_border_color,
		'' as card_bg_class,
		if (length(tagtipo) > 0, tagtipo, '- SEM CLASSIFICAÇÃO -') as card_title,
		status as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		concat('TAGS - ',upper(tagtipo)) as card_title_modal,
		'_modulo=tag&_acao=u' as card_url_modal
 from (

SELECT 
   t.idtag, tt.tagtipo, t.status
    
FROM
   tag t
  join tagtipo tt on tt.idtagtipo = t.idtagtipo 
  where tt.idtagtipo in ( 22,24,260,204) and t.status in ('DISPONÍVEL','MANUTENÇÃO','BACKUP', 'ESTOQUE')


) a  group by tagtipo , status order by tagtipo ) a


	
		";
	
$dashsuprimentos = " 






SELECT
		'dashsuprimentosprodalerta' as panel_id,
		'col-md-2' as panel_class_col,
		'PRODUTO(S) EM ALERTA' as panel_title,
		'dashsuprimentosprodalerta1' as card_id,
		'col-md-12 col-sm-12 col-xs-12' as card_class_col,  
		concat('?_modulo=produtoemalerta&novajanela=Y') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'ABAIXO DO ESTOQUE' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PRODUTO(S) EM ALERTA' as card_title_modal,
		'' as card_url_modal

		from (SELECT 
1
        FROM (
	SELECT c.idcotacao AS idprodserv,
				'' AS codprodserv,
				prodservdescr AS descr,
				'' AS estmin,
				'' AS pedido,
				'' AS total,
				'' AS quar,
				'MANUAL' as entrada,
				c.status AS statusorc,
                n.status as status,
                n.idnf,
                c.idcotacao,
               (if(DATE_FORMAT(n.previsaoentrega,'%Y-%m-%d')>=DATE_FORMAT(now(),'%Y-%m-%d'),'O','V'))  as atrasado,
               (if(DATE_FORMAT(c.prazo,'%Y-%m-%d')>=DATE_FORMAT(now(),'%Y-%m-%d'),'O','V'))  as atrasadocot,
				'MANUAL' AS tipo,
				c.alteradoem AS ultimoconsumo
			FROM nfitem i FORCE INDEX(PRAZO) JOIN nf n ON i.idnf = n.idnf JOIN cotacao c ON n.idobjetosolipor = c.idcotacao
			WHERE idprodserv IS NULL AND n.status NOT IN ('CONCLUIDO', 'CANCELADO', 'REPROVADO')
			 and c.idempresa=1 
			AND n.tipoobjetosolipor = 'cotacao' 
		UNION 
		SELECT 
			u2.*,
            CASE
				WHEN u3.statusorc is null THEN 'PENDENTE'
				ELSE u3.statusorc
			END as statusorc,  
            (select n.status
				from nfitem i, nf n,cotacao c
				where i.idprodserv =u2.idprodserv
				and i.idnf = n.idnf
				and i.nfe='Y'
				and n.idobjetosolipor = u3.idcotacao 
				and n.tipoobjetosolipor = 'cotacao' 
				and n.status in ('APROVADO','DIVERGENCIA') LIMIT 1 ) as status, 
                (select n.idnf
				from nfitem i, nf n,cotacao c
				where i.idprodserv =u2.idprodserv
				and i.idnf = n.idnf
				and i.nfe='Y'
				and n.idobjetosolipor = u3.idcotacao 
				and n.tipoobjetosolipor = 'cotacao' 
				and n.status in ('APROVADO','DIVERGENCIA') LIMIT 1 ) as idnf,
                		u3.idcotacao,
				(select if(DATE_FORMAT(n.previsaoentrega,'%Y-%m-%d')>=DATE_FORMAT(now(),'%Y-%m-%d'),'O','V')  
				from nfitem i, nf n,cotacao c
				where i.idprodserv =u2.idprodserv
				and i.idnf = n.idnf
				and i.nfe='Y'
				and n.idobjetosolipor = u3.idcotacao 
				and n.tipoobjetosolipor = 'cotacao' 
				and n.status in ('APROVADO','DIVERGENCIA') LIMIT 1 ) as atrasado,
            (if(DATE_FORMAT(u3.prazo,'%Y-%m-%d')>=DATE_FORMAT(now(),'%Y-%m-%d'),'O','V'))  as atrasadocot,
			'NORMAL' AS tipo,
			(SELECT 
					criadoem
				FROM
					prodcomprar
				WHERE
					status = 'ATIVO'
						AND idprodserv = u2.idprodserv) AS ultimoconsumo
		FROM
			(SELECT 
				idprodserv,
					codprodserv,
					descr,
					estmin,
					pedido,
					SUM(total) AS total,
					SUM(quar) AS quar,				
					entrada
			FROM
				(SELECT 
				p.idprodserv,
					p.codprodserv,
					p.descr,
					p.estmin,
					p.pedido,
					IFNULL(f.qtd, 0) AS total,
					(SELECT 
							IFNULL(SUM(q.qtdprod), 0)
						FROM
							lote q
						WHERE
							q.idprodserv = p.idprodserv
								AND q.status = 'QUARENTENA') AS quar,					
					'NORMAL' AS entrada
			FROM
				prodserv p  
                                 join unidadeobjeto o on( o.idunidade =8 and o.idobjeto = p.idprodserv and o.tipoobjeto = 'prodserv')                                     
			LEFT JOIN lote l ON (l.idprodserv = p.idprodserv 
                        AND l.status IN ('APROVADO' , 'QUARENTENA'))
                        LEFT JOIN lotefracao f on(f.idlote=l.idlote AND f.idunidade = 8 and f.status='DISPONIVEL'
                            )
			WHERE p.tipo = 'PRODUTO'
				 and p.idempresa=1 
					AND p.status = 'ATIVO'
					AND p.estmin IS NOT NULL
					AND p.estmin != 0.00
					-- AND p.estideal != 0.00
					AND p.comprado = 'Y') AS u
			GROUP BY u.idprodserv) u2
				LEFT JOIN
			(SELECT 
				MAX(c.prazo) AS prazo, c.status AS statusorc, i.idprodserv,c.idcotacao
			FROM
				cotacao c
			JOIN nf n -- FORCE INDEX (NOVOPRAZO) 
			ON n.idobjetosolipor = c.idcotacao
			JOIN nfitem i FORCE INDEX (PRAZO) ON n.tipoobjetosolipor = 'cotacao'
				AND i.idnf = n.idnf
				AND n.status NOT IN ('CONCLUIDO', 'CANCELADO', 'REPROVADO')
			GROUP BY i.idprodserv) u3 ON u3.idprodserv = u2.idprodserv
		WHERE
			u2.estmin >= u2.total
            ) AS xx  where  xx.statusorc !='CONCLUIDA'
            and (
             CASE
            WHEN  xx.statusorc='PENDENTE' THEN 1
            WHEN  xx.statusorc ='ABERTA' THEN 1
            WHEN  xx.statusorc ='COMPRAR' and xx.atrasadocot ='V' THEN 2
            WHEN  xx.statusorc ='COMPRAR' and xx.atrasadocot ='O' THEN 3
            WHEN  xx.statusorc = 'CONCLUIDA' AND xx.status ='DIVERGENCIA' and xx.tipo ='NORMAL' and xx.atrasado ='V' THEN 4
            WHEN  xx.statusorc = 'CONCLUIDA' AND xx.status ='DIVERGENCIA' and xx.tipo ='MANUAL' and xx.atrasado ='V' THEN 5 
            WHEN  xx.statusorc = 'CONCLUIDA' AND xx.status ='APROVADO' and xx.tipo ='NORMAL' and xx.atrasado ='V' THEN 4
            WHEN   xx.statusorc = 'CONCLUIDA' AND xx.status ='APROVADO' and xx.tipo ='MANUAL' and xx.atrasado ='V' THEN 5
            WHEN  xx.statusorc = 'ANDAMENTO' and xx.atrasadocot ='V' THEN 2
            WHEN  xx.statusorc = 'ANDAMENTO' and xx.atrasadocot ='O' THEN 3
            WHEN  xx.statusorc ='CONCLUIDA' and xx.atrasado ='O' THEN 8
        ELSE 9
	END) in (1,2,4,5,6,9)
            group by idprodserv,idcotacao  )a


union all








SELECT
		'dashsuprimentoscotacao' as panel_id,
		'col-md-4' as panel_class_col,
		'COTAÇÃO' as panel_title,
		'dashsuprimentoscotacaoenviado' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,  
		concat('_modulo=cotacao&_pagina=0&_ordcol=dmaemissao&_orddir=desc&idcotacao=[',group_concat(distinct idcotacao separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(distinct idcotacao) > 0,'danger','success') as card_color,
		if (count(distinct 1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'ENVIADO' as card_title,
		'' as card_title_sub,
		count(distinct idcotacao) as card_value,
		'fa-print' as card_icon,
		'COTAÇÃO - ENVIADO' as card_title_modal,
		'_modulo=cotacao&_acao=u' as card_url_modal
	FROM
		cotacao c
   LEFT JOIN `nf` `n` ON (`n`.`idobjetosolipor` = `c`.`idcotacao`)
            AND (`n`.`tipoobjetosolipor` = 'cotacao')
	WHERE
    `n`.`status` = 'ENVIADO'
    ".getidempresa('c.idempresa','cotacao')." 
		
union all


SELECT
		'dashsuprimentoscotacao' as panel_id,
		'col-md-4' as panel_class_col,
		'COTAÇÃO' as panel_title,
		'dashsuprimentoscotacaorecebido' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,  
		concat('_modulo=cotacao&_pagina=0&_ordcol=dmaemissao&_orddir=desc&idcotacao=[',group_concat(distinct idcotacao separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(distinct idcotacao) > 0,'danger','success') as card_color,
		if (count(distinct 1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'RESPONDIDO' as card_title,
		'' as card_title_sub,
		count(distinct idcotacao) as card_value,
		'fa-print' as card_icon,
		'COTAÇÃO - RESPONDIDO' as card_title_modal,
		'_modulo=cotacao&_acao=u' as card_url_modal
	FROM
		cotacao c
   LEFT JOIN `nf` `n` ON (`n`.`idobjetosolipor` = `c`.`idcotacao`)
            AND (`n`.`tipoobjetosolipor` = 'cotacao')
	WHERE
    `n`.`status` = 'RESPONDIDO'
    ".getidempresa('c.idempresa','cotacao')." 
	
union all




SELECT
		'dashcompras' as panel_id,
		'col-md-4' as panel_class_col,
		'COMPRAS' as panel_title,
		'dashcomprasaprovadas' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,  
		concat('_modulo=nfentrada&_pagina=0&_ordcol=idnf&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO%22}&_fds=', DATE_FORMAT(DATE_SUB(DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d'), interval 1 year),'%d/%m/%Y'),'-',DATE_FORMAT(DATE_SUB(CURRENT_DATE, interval 1 day),'%d/%m/%Y')) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'aprovado' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'COMPRAS - APROVADO' as card_title_modal,
		'_modulo=nfentrada&_acao=u' as card_url_modal
	 FROM
       `nf` `n`
    WHERE
	n.status = 'APROVADO'
    ".getidempresa('idempresa','nfentrada')." and n.tiponf IN ('C' , 'T', 'E', 'S', 'M', 'F', 'B')

	 and dtemissao between DATE_SUB(DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d'), interval 1 year) and DATE_SUB(CURRENT_DATE, interval 1 day) 
        
		 
		
union all 
  SELECT
		'dashcompras' as panel_id,
		'col-md-4' as panel_class_col,
		'COMPRAS' as panel_title,
		'dashcomprasaprovadasematraso' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col, 
		concat('_modulo=nfentrada&_pagina=0&_ordcol=previsaoentrega&_orddir=asc&_filtrosrapidos={%22status%22:%22APROVADO%22}&idnf=[',group_concat(idnf separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'em atraso' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'COMPRAS - APROVADAS EM ATRASO' as card_title_modal,
		'_modulo=nfentrada&_acao=u' as card_url_modal
    FROM
       `nf` `n`
    WHERE
	n.status = 'APROVADO'
		  ".getidempresa('idempresa','nfentrada')." and n.tiponf IN ('C' , 'T', 'E', 'S', 'M', 'F', 'B') 
    --     		and  DATE_ADD(DATE_FORMAT(dtemissao,'%Y-%m-%d'), interval 7 day) < CURRENT_DATE
				and previsaoentrega < CURRENT_DATE


union all
	
	SELECT
		'dashenvioemail' as panel_id,
		'col-md-2' as panel_class_col,
		'EMAILS NÃO ENVIADOS' as panel_title,
		
		'dashenvioemailcotacao' as card_id,
		'col-md-12 col-sm-12 col-xs-12' as card_class_col, 
		concat('_modulo=envioemail&_pagina=0&_ordcol=idmailfila&_orddir=desc&_filtrosrapidos={%22status%22:%22NAO%20ENVIADO%22}&idmailfila=[',group_concat(distinct m.idmailfila separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_color,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'Cotação' as card_title,
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
		-- m2.idmailfila IS NULL
        1 AND m.status IN ('NAO ENVIADO')
        AND m.remover = 'N'
        AND m.tipoobjeto in  ('cotacao', 'cotacaoaprovada')
		
union all
	
	
	select
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
	from	
		(
select

		'dashsuprimentoscontapagarccredito' as panel_id,
		'col-md-12' as panel_class_col,
		'FATURAS C.CRÉDITO - ABERTO' as panel_title,
		concat('dashsuprimentoscontapagarccredito',replace(trim(lower(c.idcontapagar)),' ','')) as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat('?_modulo=contapagar&_acao=u&idcontapagar=',c.idcontapagar,'&novajanela=Y') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (sum(cp.valor) >=c.valor,'secundary','secundary ') as card_color,
		if (sum(cp.valor) >=c.valor,'secundary','secundary ') as card_border_color,
		'' as card_bg_class,
		fp.descricao as card_title,
		concat(dma(c.datareceb), '</span><span card_titlesub_', if(c.valor - sum(cp.valor)>=0,'success','danger')  ,'>',if(c.valor - sum(cp.valor)>=0,'-','+'),' R$ ',         format(ABS(c.valor - sum(cp.valor)),2,'de_DE'))  as card_title_sub,
		concat('R$ ',format(sum(cp.valor),2,'de_DE'))  as card_value,
		'fa-print' as card_icon,
		'' as card_title_modal,
		'_modulo=contapagar&_acao=u' as card_url_modal
        from
contapagaritem cp
join contapagar c on c.idcontapagar = cp.idcontapagar and c.status in ('ABERTO')
join formapagamento fp on fp.idformapagamento = cp.idformapagamento
join(
select min(c.datareceb) as datareceb,
c.idformapagamento
from contapagar c 
join formapagamento f on f.idformapagamento = c.idformapagamento
where f.formapagamento = 'C.CREDITO' and c.status in ('ABERTO')
 ".getidempresa('c.idempresa','contapagar')." 
group by
f.idformapagamento) cpi on c.datareceb = cpi.datareceb and c.idformapagamento = cpi.idformapagamento

group by
c.idcontapagar
order by
c.datareceb, fp.descricao
) a
union all

	select
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
	from	
		(
select

		'dashsuprimentoscontapagarccreditofechado' as panel_id,
		'col-md-12' as panel_class_col,
		'FATURAS C.CRÉDITO - FECHADO' as panel_title,
		concat('dashsuprimentoscontapagarccreditofechado',replace(trim(lower(c.idcontapagar)),' ','')) as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat('?_modulo=contapagar&_acao=u&idcontapagar=',c.idcontapagar,'&novajanela=Y') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (sum(cp.valor) >=c.valor,'secundary','secundary ') as card_color,
		if (sum(cp.valor) >=c.valor,'secundary','secundary ') as card_border_color,
		'' as card_bg_class,
		fp.descricao as card_title,
		concat(dma(c.datareceb), '</span><span card_titlesub_', if(c.valor - sum(cp.valor)>=0,'success','danger')  ,'>',if(c.valor - sum(cp.valor)>=0,'-','+'),' R$ ',         format(ABS(c.valor - sum(cp.valor)),2,'de_DE'))  as card_title_sub,
		concat('R$ ',format(sum(cp.valor),2,'de_DE'))  as card_value,
		'fa-print' as card_icon,
		'' as card_title_modal,
		'_modulo=contapagar&_acao=u' as card_url_modal
        from
contapagaritem cp
join contapagar c on c.idcontapagar = cp.idcontapagar and c.status in ('FECHADO')
join formapagamento fp on fp.idformapagamento = cp.idformapagamento
join(
select min(c.datareceb) as datareceb,
c.idformapagamento
from contapagar c 
join formapagamento f on f.idformapagamento = c.idformapagamento
where f.formapagamento = 'C.CREDITO' and c.status in ('FECHADO')
 ".getidempresa('c.idempresa','contapagar')." 
group by
f.idformapagamento) cpi on c.datareceb = cpi.datareceb and c.idformapagamento = cpi.idformapagamento

group by
c.idcontapagar
order by
c.datareceb, fp.descricao
) a


			";
				
$dashcrm = "

  
	SELECT
		'dashautogenas' as panel_id,
		'col-md-4' as panel_class_col,
		'TEA / TRA' as panel_title,
		'dashautogenastraenviado' as card_id,
		'col-md-6 col-sm-6 col-xs-12' as card_class_col,
		concat('_modulo=',uo.idobjeto,'&_pagina=0&_ordcol=idamostra&_orddir=desc&_filtrosrapidos={%22statustra%22:%22ENVIADO%22}&idamostra=[',group_concat(idamostra separator ','),']')as card_url, 
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
		concat('_modulo=',uo.idobjeto,'&_acao=u') as card_url_modal
	from
	
		 amostra l
	
		
	 JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON (`uo`.`tipoobjeto` = 'modulo' AND `l`.`idunidade` = `uo`.`idunidade`)
	 JOIN carbonnovo._modulo m on m.modulo = uo.idobjeto and m.modulotipo = 'amostra'
	where
		l.statustra in ('ENVIADO')  
 ".str_replace('idpessoa','l.idpessoa',$pessoas)."
		 and l.idunidade = 9
		  union all
		  
		  
		  
	SELECT
		'dashautogenas' as panel_id,
		'col-md-2' as panel_class_col,
		'TEA / TRA' as panel_title,
		'dashautogenastradevolvido' as card_id,
		'col-md-6 col-sm-6 col-xs-12' as card_class_col,
		concat('_modulo=',uo.idobjeto,'&_pagina=0&_ordcol=idamostra&_orddir=desc&_filtrosrapidos={%22statustra%22:%22DEVOLVIDO%22}&idamostra=[',group_concat(idamostra separator ','),']')as card_url, 
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'DEVOLVIDO' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'TEA / TRA - DEVOLVIDO' as card_title_modal,
		concat('_modulo=',uo.idobjeto,'&_acao=u') as card_url_modal
	from
	
		 amostra l
	
		
	 JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON (`uo`.`tipoobjeto` = 'modulo' AND `l`.`idunidade` = `uo`.`idunidade`)
	 JOIN carbonnovo._modulo m on m.modulo = uo.idobjeto and m.modulotipo = 'amostra'
	where
		l.statustra in ('DEVOLVIDO')  
 ".str_replace('idpessoa','l.idpessoa',$pessoas)."
		 and l.idunidade = 9
		  union all
SELECT 
		'dashcrmpessoa' as panel_id,
		'col-md-4' as panel_class_col,
		'EMPRESAS' as panel_title,
		'dashcrmpessoacontato' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col, 
		concat('_modulo=pessoa&_pagina=0&_ordcol=idpessoa&_orddir=desc&_filtrosrapidos={%22status%22:%22ATIVO%22,%22idtipopessoa%22:%222%22}&idpessoa=[',group_concat( DISTINCT idpessoa separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'SEM REPRESENTAÇÃO' as card_title,
		'' as card_title_sub,
		count(DISTINCT idpessoa) as card_value,
		'fa-print' as card_icon,
		'EMPRESAS - SEM REPRESENTAÇÃO' as card_title_modal,
		'_modulo=pessoa&_acao=u' as card_url_modal
    FROM
pessoa p 
where not exists (select 1 from pessoacontato pc where pc.idpessoa = p.idpessoa)
and p.idtipopessoa = 2 and p.status = 'ATIVO' and p.vendadireta = 'N'
".$pessoas."
union all


SELECT
		'dashcrmpessoa' as panel_id,
		'col-md-4' as panel_class_col,
		'EMPRESAS' as panel_title,
		'dashcrmpessoapendente' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col, 
		concat('_modulo=pessoa&_pagina=0&_ordcol=idpessoa&_orddir=desc&_filtrosrapidos={%22status%22:%22PENDENTE%22,%22idtipopessoa%22:%222%22}') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'PENDENTE' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'EMPRESAS - PENDENTE' as card_title_modal,
		'_modulo=pessoa&_acao=u' as card_url_modal
    FROM
pessoa p 
where p.idtipopessoa = 2 and p.status = 'PENDENTE'
".$pessoas."
union all 

SELECT
		'dashcrm' as panel_id,
		'col-md-4' as panel_class_col,
		'CRM' as panel_title,
		'dashcrmsementesvencidas' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col, 
		concat('_modulo=semente&_pagina=0&_ordcol=idlote&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO%22}&idlote=[',group_concat(distinct idlote separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'sementes vencidas' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'CRM - SEMENTES VENCIDAS' as card_title_modal,
		'_modulo=semente&_acao=u' as card_url_modal
    FROM
       vwlote a

where
exists(select 1 
	from prodserv p join  unidadeobjeto u on( u.idunidade = 9 and u.idobjeto = p.idprodserv and u.tipoobjeto = 'prodserv')
	 where p.idprodserv=a.idprodserv
	 and p.tipo = 'PRODUTO'
	 and p.status = 'ATIVO' 
	 and p.especial='Y' 
	 and p.idtipoprodserv = 3)
	 and  DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d') >= a.vencimento and a.status = 'APROVADO' and a.statusfr = 'DISPONÍVEL' and a.qtddisp > 0
	  ".$pessoas."
      	
union all 
  SELECT
		'dashcrm' as panel_id,
		'col-md-4' as panel_class_col,
		'CRM' as panel_title,
		'dashcrmsementesavencer' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col, 
		concat('_modulo=semente&_pagina=0&_ordcol=idlote&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO%22}&idlote=[',group_concat(distinct idlote separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'warning','success') as card_color,
		if (count(1) > 0,'warning','success') as card_border_color,
		'' as card_bg_class,
		'sementes a vencer' as card_title,
		'próximos 90 dias' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'CRM - SEMENTES A VENCER' as card_title_modal,
		'_modulo=semente&_acao=u' as card_url_modal
    FROM
       vwlote a

where
exists(select 1 
	from prodserv p join  unidadeobjeto u on( u.idunidade = 9 and u.idobjeto = p.idprodserv and u.tipoobjeto = 'prodserv')
	 where p.idprodserv=a.idprodserv
	 and p.tipo = 'PRODUTO'
	 and p.status = 'ATIVO' 
	 and p.especial='Y' 
	 and p.idtipoprodserv = 3)
	 and  a.vencimento between DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d') and DATE_ADD(DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d'), interval 90 day) and a.status = 'APROVADO'
	 ".$pessoas."	

	
union all
	
	SELECT
		'dashenvioemail' as panel_id,
		'col-md-2' as panel_class_col,
		'EMAILS NÃO ENVIADOS' as panel_title,
		
		'dashenvioemailsenha' as card_id,
		'col-md-12 col-sm-12 col-xs-6' as card_class_col, 
		concat('_modulo=envioemail&_pagina=0&_ordcol=idmailfila&_orddir=desc&_filtrosrapidos={%22status%22:%22NAO%20ENVIADO%22}&idmailfila=[',group_concat(distinct m.idmailfila separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_color,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'Recuperação Senha' as card_title,
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
		-- m2.idmailfila IS NULL
        1 AND m.status IN ('NAO ENVIADO')
        AND m.remover = 'N'
        AND m.tipoobjeto = 'recuperasenha'	 
	 ";
	 
	 
	 
 $dashadm = "SELECT
		'dashadm' as panel_id,
		'col-md-2' as panel_class_col,
		'ADMINISTRATIVO' as panel_title,
		'dashadmnfsafaturar' as card_id,
		'col-md-12 col-sm-12 col-xs-6' as card_class_col, 
		concat('_modulo=nfs&_pagina=0&_ordcol=idnf_orddir=desc&_filtrosrapidos={%22status%22:%22FECHADO%22}') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
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

union all
	
	SELECT
		'dashenvioemail' as panel_id,
		'col-md-10' as panel_class_col,
		'EMAILS NÃO ENVIADOS' as panel_title,
		
		'dashenvioemaildetalhamento' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col, 
		concat('_modulo=envioemail&_pagina=0&_ordcol=idmailfila&_orddir=desc&_filtrosrapidos={%22status%22:%22NAO%20ENVIADO%22}&idmailfila=[',group_concat(distinct m.idmailfila separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_color,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'detalhamento' as card_title,
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
		-- m2.idmailfila IS NULL
       1  AND m.status IN ('NAO ENVIADO')
        AND m.remover = 'N'
        AND m.tipoobjeto = 'detalhamento'	
union all
	
	SELECT
		'dashenvioemail' as panel_id,
		'col-md-2' as panel_class_col,
		'EMAILS NÃO ENVIADOS' as panel_title,
		
		'dashenvioemailnfp' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col, 
		concat('_modulo=envioemail&_pagina=0&_ordcol=idmailfila&_orddir=desc&_filtrosrapidos={%22status%22:%22NAO%20ENVIADO%22}&idmailfila=[',group_concat(distinct m.idmailfila separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_color,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'NF Produto' as card_title,
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
		-- m2.idmailfila IS NULL
        1 AND m.status IN ('NAO ENVIADO')
        AND m.remover = 'N'
        AND m.tipoobjeto = 'nfp'	
union all
	
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
		-- m2.idmailfila IS NULL
        1 AND m.status IN ('NAO ENVIADO')
        AND m.remover = 'N'
        AND m.tipoobjeto = 'nfs'	

union all
	
	SELECT
		'dashenvioemail' as panel_id,
		'col-md-2' as panel_class_col,
		'EMAILS NÃO ENVIADOS' as panel_title,
		
		'dashenvioemailop' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col, 
		concat('_modulo=envioemail&_pagina=0&_ordcol=idmailfila&_orddir=desc&_filtrosrapidos={%22status%22:%22NAO%20ENVIADO%22}&idmailfila=[',group_concat(distinct m.idmailfila separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_color,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'Orç. Produto' as card_title,
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
		-- m2.idmailfila IS NULL
        1 AND m.status IN ('NAO ENVIADO')
        AND m.remover = 'N'
        AND m.tipoobjeto = 'orcamentoprod'	
	
	
union all
	
	SELECT
		'dashenvioemail' as panel_id,
		'col-md-2' as panel_class_col,
		'EMAILS NÃO ENVIADOS' as panel_title,
		
		'dashenvioemailos' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col, 
		concat('_modulo=envioemail&_pagina=0&_ordcol=idmailfila&_orddir=desc&_filtrosrapidos={%22status%22:%22NAO%20ENVIADO%22}&idmailfila=[',group_concat(distinct m.idmailfila separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_color,
		if (count(distinct m.idmailfila) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'Orç. Serviço' as card_title,
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
		-- m2.idmailfila IS NULL
        1 AND m.status IN ('NAO ENVIADO')
        AND m.remover = 'N'
        AND m.tipoobjeto = 'orcamentoserv'	
	
	";

	$dashped = "  
		  SELECT
		'dashresultados' as panel_id,
		'col-md-4' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosematraso' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat('_modulo=',uo.idobjeto,'&_pagina=0&_ordcol=idresultado&_orddir=desc&_filtrosrapidos={%22status%22:%22ABERTO,PROCESSANDO%22,%22ematraso%22:%22Y%22}') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'em atraso' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'RESULTADOS - EM ATRASO' as card_title_modal,
		concat('_modulo=',uo.idobjeto,'&_acao=u') as card_url_modal
	from
		resultado r
		JOIN amostra l on l.idamostra = r.idamostra
	JOIN
		prodserv p on p.idprodserv = r.idtipoteste
	 JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON ((
         (`uo`.`tipoobjeto` = 'moduloresultun')
        AND (`l`.`idunidade` = `uo`.`idunidade`)))
	where
		r.status in ('PROCESSANDO','ABERTO')
        and DATE_ADD(DATE_FORMAT(r.criadoem,'%Y-%m-%d'), interval p.prazoexec day) < CURRENT_DATE

		  ".$dashlotescond."
		  ".str_replace('idpessoa','l.idpessoa',$pessoas)."

 union all
		  
		  	SELECT
		'dashresultados' as panel_id,
		'col-md-4' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosassinatura' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat('form/assinarresultado.php?_idrep=2&_acao=u&registro_1=&registro_2=&dataregistro_1=&dataregistro_2=&cliente=&teste=&exercicio=&status=FECHADO&idunidade=',$linkunidade,'&novajanela=Y') as card_url,
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
		from vwassinarresultado l 
			where
		status = 'FECHADO'
 and  (l.idsecretaria = '' or l.idsecretaria is null)
		  ".$dashlotescond."	

";		
 
$dashalmox = "		  
		  	SELECT
		'dashprodserv' as panel_id,
		'col-md-2' as panel_class_col,
		'PRODUTOS' as panel_title,
		'dashprodservminimo' as card_id,
		'col-md-12 col-sm-12 col-xs-12' as card_class_col,
		concat('_modulo=prodserv&_pagina=0&_ordcol=descr&_orddir=asc&_filtrosrapidos={%22tipo%22:%22PRODUTO%22,%22status%22:%22ATIVO%22}&idprodserv=[',group_concat(distinct idprodserv separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(distinct idprodserv) > 0,'danger','success') as card_color,
		if (count(distinct idprodserv) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'min. zerado' as card_title,
		'' as card_title_sub,
		count(distinct idprodserv) as card_value,
		'fa-print' as card_icon,
		'PRODSERV - MÍNIMO 0' as card_title_modal,
		'_modulo=prodserv&_acao=u' as card_url_modal
		from prodserv   p
		join unidadeobjeto o on o.tipoobjeto = 'prodserv' and o.idobjeto = p.idprodserv and o.idunidade = 9
		where tipo = 'PRODUTO' and status = 'ATIVO' and not `p`.`estmin` > 0
		  
";  

$dashbioterio = "
SELECT
		'dashprodserv' as panel_id,
		'col-md-2' as panel_class_col,
		'SERVIÇOS' as panel_title,
		'dashprodservminimo' as card_id,
		'col-md-12 col-sm-12 col-xs-12' as card_class_col,
		concat('?_modulo=rebioensaio&novajanela=Y') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'em atraso' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PRODSERV - MÍNIMO 0' as card_title_modal,
		'_modulo=rebioensaio' as card_url_modal
	
   from servicoensaio l join servicobioterio sb join analise a join nucleo n join bioensaio b
                LEFT JOIN (localensaio le) ON (b.idbioensaio = le.idbioensaio AND le.idlocal > 3)
                LEFT JOIN (local lo) ON (lo.idlocal = le.idlocal)
        where b.idbioensaio=a.idobjeto
        and b.idespeciefinalidade != 18
        and b.idnucleo = n.idnucleo
        and sb.idservicobioterio = l.idservicobioterio
        and a.objeto ='bioensaio'
        and a.idanalise = l.idobjeto
        and l.tipoobjeto = 'analise'
	AND l.data BETWEEN CURDATE() - INTERVAL 100 DAY AND CURDATE() - INTERVAL 1 DAY
	AND l.status = 'PENDENTE'
      	
		
 union all
			
	SELECT
		'dashresultados' as panel_id,
		'col-md-4' as panel_class_col,
		'RESULTADOS' as panel_title,
		'dashresultadosconferencia' as card_id,
		'col-md-6 col-sm-6 col-xs-6' as card_class_col,
		concat('?_modulo=',uo.idobjeto,'&_acao=u&registro_1=&registro_2=&dataregistro_1=&dataregistro_2=&cliente=&teste=&exercicio=&statusres=FECHADO&novajanela=Y') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'conferência' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'RESULTADOS - CONFERÊNCIA' as card_title_modal,
		'?_modulo=conferencia&statusres=FECHADO' as card_url_modal
		from vwassinarresultado l 
		JOIN `unidadeobjeto` `uo` FORCE INDEX (TIPOOBJETOUNIDADE) ON ((
         (`uo`.`tipoobjeto` = 'moduloconferenciaun')
        AND (`l`.`idunidade` = `uo`.`idunidade`)))
			where
			 l.conferenciares='Y'
            and  not exists (select 1 from _auditoria aud where valor = 'CONFERIDO' and idauditoria = (select max(idauditoria) as idauditoria from _auditoria au where au.objeto = 'resultado' and idobjeto = l.idresultado and coluna = 'status'))
			and status = 'FECHADO' 
		 ".$dashlotescond."	
		";


$dashrh = "


select 
'dashrh' as panel_id,
		'col-md-12' as panel_class_col,
		'FUNCIONÁRIOS / PONTO' as panel_title,
		'dashrhponto' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat('?_modulo=eventoponto&_acao=u&dataevento_1=',DATE_FORMAT(CURRENT_DATE - if((6+weekday(CURRENT_DATE))%7 > 4, (6+weekday(CURRENT_DATE))%7-3, 1),'%d/%m/%Y'),'&dataevento_2=',DATE_FORMAT(CURRENT_DATE - if((6+weekday(@dat))%7 > 4, (6+weekday(@dat))%7-3, 1),'%d/%m/%Y'),'&idpessoa=',group_concat(idpessoa separator ','),'&idsgsetor=null&novajanela=Y')  as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'irregular' as card_title,
		DATE_FORMAT(CURRENT_DATE - if((6+weekday(CURRENT_DATE))%7 > 4, (6+weekday(CURRENT_DATE))%7-3, 1),'%d/%m/%Y')  as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PONTOS - TOTAL' as card_title_modal,
		'_modulo=funcionario&_acao=u' as card_url_modal
		
		

 from(
SELECT
idpessoa
                FROM rhevento e LEFT JOIN rhtipoevento t ON(t.idrhtipoevento = e.idrhtipoevento)
			   WHERE e.idrhtipoevento = 6
			
			 	 -- AND e.idpessoa = 6494
				 and DATE_FORMAT(e.dataevento,'%Y-%m-%d') = DATE_FORMAT(CURRENT_DATE - if((6+weekday(CURRENT_DATE))%7 > 4, (6+weekday(CURRENT_DATE))%7-3, 1),'%Y-%m-%d') 
			 	 AND e.status='PENDENTE'
                 group by idpessoa
				having sum(e.valor) <> 0
              ORDER BY e.dataevento desc,e.hora) a
              
UNION ALL
			  
			  
			  
			  
SELECT
		'dashrhfuncionariosdepartamento' as panel_id,
		'col-md-12' as panel_class_col,
		'FUNCIONÁRIOS / DEPARTAMENTO' as panel_title,
		'dashprodservminimo' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat('_modulo=funcionario&_pagina=0&_ordcol=nome&_orddir=asc&_filtrosrapidos={%22status%22:%22ATIVO%22}') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'total' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'FUNCIONÁRIOS - TOTAL' as card_title_modal,
		'_modulo=funcionario&_acao=u' as card_url_modal
	
   from vwcadfuncionario a where status = 'ATIVO' and
 exists (select 1 from objempresa o where o.idobjeto = a.idpessoa and o.objeto='pessoa'
 ".getidempresa('o.idempresa','funcionario')." ) 
 
 union all
select * from (
 select 
		CONCAT('dashrhfuncionariosdepartamento',a.idsgarea) as panel_id,
		'col-md-12' as panel_class_col,
		concat(area,' / FUNCIONÁRIOS') as panel_title,
		concat('dashrhfuncionarios',replace(trim(lower(idsgdepartamento)),' ','')) as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat('_modulo=funcionario&_pagina=0&_ordcol=nome&_orddir=asc&idpessoa=[',group_concat(idpessoa separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'secundary' as card_color,
		'secundary' as card_border_color,
		'' as card_bg_class,
		REPLACE(departamento,'Departamento','') as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		concat('FUNCIONÁRIOS - ',upper(departamento)) as card_title_modal,
		'_modulo=funcionario&_acao=u' as card_url_modal
 from (

SELECT 
    d.idsgdepartamento, d.departamento, p.idpessoa, a.idsgarea, a.area
FROM
    sgdepartamento d
join
	sgarea a on a.idsgarea = d.idsgarea and a.status = 'ATIVO'
JOIN
	pessoaobjeto po on po.idobjeto = d.idsgdepartamento and po.tipoobjeto = 'sgdepartamento'
JOIN
	pessoa p on p.idpessoa = po.idpessoa and p.status = 'ATIVO'
WHERE
    d.status = 'ATIVO'  
	and exists (select 1 from objempresa o where o.idobjeto = p.idpessoa and o.objeto='pessoa'  ".getidempresa('p.idempresa','funcionario').") 
union 

SELECT 
   d.idsgdepartamento, d.departamento, p.idpessoa, a.idsgarea, a.area
FROM
    sgdepartamento d
	join
	sgarea a on a.idsgarea = d.idsgarea and a.status = 'ATIVO'
    JOIN
	sgsetor s on s.idsgdepartamento = d.idsgdepartamento  and s.status = 'ATIVO'

JOIN
	pessoaobjeto po on po.idobjeto = s.idsgsetor and po.tipoobjeto = 'sgsetor'
JOIN
	pessoa p on p.idpessoa = po.idpessoa and p.status = 'ATIVO'
WHERE
    d.status = 'ATIVO' 
	and exists (select 1 from objempresa o where o.idobjeto = p.idpessoa and o.objeto='pessoa'  ".getidempresa('p.idempresa','funcionario').")  
) a  group by idsgdepartamento order by area,REPLACE(departamento,'Departamento','') ) a
		
	union all

SELECT
		'dashrhfuncionariosincompletos' as panel_id,
		'col-md-12' as panel_class_col,
		'FUNCIONÁRIOS SEM ALOCAÇÃO' as panel_title,
		'dashrhfuncionariosincompletoslista' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat('_modulo=funcionario&_pagina=0&_ordcol=nome&_orddir=asc&idpessoa=[',group_concat(idpessoa separator ','),']') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'total' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'FUNCIONÁRIOS - SEM ALOCAÇÃO' as card_title_modal,
		'_modulo=funcionario&_acao=u' as card_url_modal
	
   from 
   pessoa p where
   
   p.idtipopessoa = 1 and p.status = 'ATIVO'
   and exists (select 1 from objempresa o where o.idobjeto = p.idpessoa and o.objeto='pessoa'  ".getidempresa('p.idempresa','funcionario').") 
and not exists (select 1 from pessoaobjeto pop where pop.idpessoa = p.idpessoa and pop.tipoobjeto = 'sgsetor')
and not exists (select 1 from pessoaobjeto pop where pop.idpessoa = p.idpessoa and pop.tipoobjeto = 'sgdepartamento')
and not exists (select 1 from pessoaobjeto pop where pop.idpessoa = p.idpessoa and pop.tipoobjeto = 'sgarea')


 ".getidempresa('p.idempresa','funcionario')."

 ";
	  
 $dashtmp = "SELECT
		'dashtmp' as panel_id,
		'col-md-2' as panel_class_col,
		'TMP' as panel_title,
		'dashtmpcard' as card_id,
		'col-md-12 col-sm-12 col-xs-6' as card_class_col, 
		concat('') as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0,'danger','success') as card_color,
		if (count(1) > 0,'danger','success') as card_border_color,
		'' as card_bg_class,
		'nfs a faturar' as card_title,
		'' as card_title_sub,
		'' as card_value,
		'fa-print' as card_icon,
		'ADM -NFS A FATURAR' as card_title_modal,
		'' as card_url_modal";

switch ($_GET['_modulo']) {
	case 'dashautogenas':
		
		$sqldash = $dashdiagnostico." union all ".$dashautogenas." union all ".$dashlotes;
		
	break;
	case 'dashproducao':
		
		$sqldash = $dashproducao."  union all ".$dashlotes;
		
	break;
		case 'dashproducaoautogenas':
		
		$sqldash = $dashproducao."  union all ".$dashlotes;
		
	break;
	case 'dashrh':
		$sqldash = $dashrh;
	break;

	case 'dashbioterio':
		$sqldash = $dashbioterio;
	break;
	case 'dashcq':
		$sqldash = $dashcq." union all ".$dashlotes;
	break;
	case 'dashped':
		$sqldash = $dashped." union all ".$dashlotes;
	break;
	case 'dashalmox':
		$sqldash = $dashalmox." union all ".$dashlotes;
	break;
	case 'dashdiagnostico':
		$sqldash = $dashdiagnostico." union all ".$dashlotes; 
	//	$sqldash = $dashdiagnostico.$dashtmp." union all ".$dashlotes;
	break;
	case 'dashadm':
		$sqldash = $dashadm;
	break;
	case 'dashcrm':
		$sqldash = 	$dashcrm;
	break;
	case 'dashsuprimentos':
		$sqldash = 	$dashsuprimentos;
	break;
	case 'dashti':
		$sqldash = 	$dashti;
	break;
	case 'dashlogistica':
		$sqldash = 	$dashlogistica;
	break;
	case 'dashqualidade':
		$sqldash = 	$dashqualidade;
	break;
	
	default:
	//$sqldash = $dashdiagnostico." union all ".$dashautogenas." UNION ALL ".$dashbioterio." UNION ALL ".$dashproducao." UNION ALL ".$dashcq." UNION ALL ".$dashqualidade." UNION ALL ".$dashrh." UNION ALL ".$dashlogistica." UNION ALL ".$dashti."  UNION ALL ".$dashped." UNION ALL ".$dashalmox." UNION ALL ".$dashsuprimentos." UNION ALL ".$dashcrm." UNION ALL ".$dashadm." UNION ALL ".$dashlotes;
	break;
	}	
// echo '<pre>'.$sqldash.'</pre>'; 

	 	 	 
	//$dashautogenas $dashproducao $dashlogistica $sadhti $dashsuprimentos $dashcrm $dashadm

	$resfig = d::b()->query($sqldash) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	$i = -1;
	$j = 0;
	
	while ($_row = mysql_fetch_assoc($resfig)){
		
		if ($panel_id != $_row['panel_id']){
			$panel_id = $_row['panel_id'];
			$i++;
			
			$json[$i]['panel_id']							= $_row['panel_id'];
			$json[$i]['panel_class_col']					= $_row['panel_class_col'];
			$json[$i]['panel_title']						= $_row['panel_title'];
			$j = 0;
		}
		
		$json[$i]['cards'][$j]['card_id']					= $_row['card_id'];
		$json[$i]['cards'][$j]['card_class_col']			= $_row['card_class_col'];
		$json[$i]['cards'][$j]['card_url']					= $_row['card_url'];
		$json[$i]['cards'][$j]['card_notification_bg']		= $_row['card_notification_bg'];
		$json[$i]['cards'][$j]['card_notification']			= $_row['card_notification'];
		$json[$i]['cards'][$j]['card_border_color']			= $_row['card_border_color'];
		$json[$i]['cards'][$j]['card_color']				= $_row['card_color'];
		$json[$i]['cards'][$j]['card_bg_class']				= $_row['card_bg_class'];
		$json[$i]['cards'][$j]['card_title']				= $_row['card_title'];
		$json[$i]['cards'][$j]['card_title_sub']			= $_row['card_title_sub'];
		$json[$i]['cards'][$j]['card_title_modal']			= $_row['card_title_modal'];
		$json[$i]['cards'][$j]['card_url_modal']			= $_row['card_url_modal'];
		$json[$i]['cards'][$j]['card_value']				= $_row['card_value'];
		$json[$i]['cards'][$j]['card_icon']					= $_row['card_icon'];
		$j++;
		
	}
	
	//print_r($json);
	console.log(json); 
	
	$json = json_encode($json);
	//echo $json;
?>



function montapanel(json, callback){
	var i=0;
	 var panel = '';
	 var passou=false;
	 console.log(json);
	 json.forEach(function(item, index) {
		 i++;
		 console.log('criar '+item.panel_id);
		 if($('#' + item.panel_id).length == 0){
			  console.log('passou '+item.panel_id);
			 passou = true;
		   panel = 
		  `	<!-- LOOP DOS PAINÉIS --> 
				
					<div class="${item.panel_class_col}">
						<div class="panel panel-primary"  style="border-color:#F3F3F3">
							<div class="panel-body">
								<h3 class="text-on-pannel text-primary" id="${item.panel_title}"><strong class="text-uppercase"> ${item.panel_title}</strong></h3>
								<div id="${item.panel_id}">
								</div>
							</div>
						</div>
					</div>
			
			<!-- FIM LOOP DOS PAINÉIS -->`;
			
			$('#cbModuloForm').append(panel);
			
			
		}
	 
	 });
	 
	 /* if(passou){
		$('#cbModuloForm').html(panel);
	  } */
	 callback(json);
	 
}  
	 
montaCards=function(json){

	var card = '';
	var hide='';
	json.forEach(function(item, index) {

	console.log('Painel ' + item.panel_id + ' '+ $('#' + item.panel_id).length);
	if($('#' + item.panel_id).length > 0){

		item["cards"].forEach(function(i, x) {
				//console.log(i.card_id);
			console.log('Card '+$('#' + i.card_id).length);
			if($('#' + i.card_id).length == 0){   
				
				card = `
					<!-- LOOP DOS BLOCOS -->
					<div id="${i.card_id}">
						<div class="${i.card_class_col} mb-4 pointer hovercinzaclaro" onclick="popLink('${i.card_url}','${i.card_title_modal}','${i.card_border_color}','${i.card_url_modal}')" >
							<span id="cbIBadgeSnippet2" class="${i.card_notification_bg} badge badgedash hide" style="" ibadge="${i.card_notification}">${i.card_notification}</span>
							<div class="card border-left-${i.card_border_color} shadow h-100 py-2 bg-${i.card_bg_class}"style="border-radius:8px;">
								<div class="card-body">
									<div class="row no-gutters align-items-center">
										<div class="col-md-12">
											<div class="text-xs negrito text-uppercase mb-1" style="color:#888;text-align:left;padding:0px 8px">${i.card_title}</div>
											
										</div>
										
									</div>
									<div class="row">
										<div class="col-md-12">
										<div class="h6 mb-0 font-weight_bold text_gray-800 titulo-${i.card_color}" style="text-align:center;font-weight:bolder;"><span id='card_value'>${i.card_value}</span></div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
										<div  style="text-align:left;font-weight:bolder;"><span id='card_title_sub' class="bg-${i.card_border_color}" card_titlesub>${i.card_title_sub}</span></div>
										</div>
									</div>
								</div>
							</div>
						</div> 
					</div> 
						
					<!-- FIM LOOP DOS BLOCOS --> `;
				
					$('#' + item.panel_id).append(card);
					$('#' + item.panel_id).find( "#cbIBadgeSnippet2" ).hide();
				
			}else{
				console.log('existe'); 
				$('#' + item.panel_id).find( "#cbIBadgeSnippet2" ).removeClass();
				$('#' + item.panel_id).find( "#cbIBadgeSnippet2" ).addClass(' badge badgedash aaaa');
				$('#' + item.panel_id).find( "#cbIBadgeSnippet2" ).addClass(i["card_notification_bg"]);
				$('#' + item.panel_id).find( "#cbIBadgeSnippet2" ).html(i["card_notification"]);
  
			 
			}
					
		});
		
		/* if (card != ''){
		
		$('#' + item.panel_id).append(card);
		card = '';
		} */
	}
	
	
	}); 
	 //return (card);
} 

	
	

//Desenhar os elementos HTML la tela  
montaHTML=function(){

	var json2 =  [{
		"panel_id":"lotemalertavendas",
 		"panel_class_col": "col-md-12",
 		"panel_title": "LOTE EM ALERTA VENDAS",
 		"cards": [{
			"card_id":"quantidadedeamostras",
 			"card_class_col": "col-md-2 col-sm-4 col-xs-6",
 			"card_url": "report/relevento.php?_acao=u&amp;idevento=315419",
 			"card_notification_bg": "fundovermelho",
 			"card_notification": "66",
 			"card_color": "",
 			"card_border_color": "danger",
 			"card_bg_class": "danger",
 			"card_title": "Quantidade de Amostras",
 			"card_value": "129",
 			"card_icon": "fa-print"
 		}]
 	}
 ];
 
 
 
 var json = <?=$json;?>;

	montapanel(json, function(resultado){
		montaCards(resultado);
	});
	
	 
}


function popLink(url,title,color,urlmodal){
//	alert(url);
		vGet = "_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&&_fts=form&especial=Y";
	

	var strCabecalho = "</strong><label class='fonte08'><span class='titulo-"+color+"'>"+title+"</span></label></strong>";

	//Altera o cabeçalho da janela modal
	$("#cbModalTitulo")
				.html(strCabecalho)
				.append("&nbsp;&nbsp;<label id='resultadosEncontrados' class='fonte08'></label>")
				.append("<i class='fa fa-print floatright' id='btPrintNucleo' title='Impressão' onclick=\"printNucleo(2)\"></i>")
				.append("<i class='fa fa-eye floatright' title='Marcar como visualizados' onclick=\"resetNotificacoesPorNucleo(2)\"></i>")
	;

	console.log('teste'+url);  
	if (url != '' && url != 'null'){
		console.log('teste'+url);
	if (url.search("php")>=0 || url.search("novajanela")>=0){
		link= './'+url;
		janelamodal(url);
	}else{
		link='form/_modulofiltrospesquisa.php'
	
	//Realiza a chamada da pagina de pesquisa manualmente
	$.ajax({
		context: this,
		type: 'get', 
		cache: false,
		url: link,
		data: url,
		dataType: "json",
		beforeSend: function(){
			alertAguarde();
		},
		success: function(data, status, jqXHR){

			//Json contem resultados encontrados?
			if(!$.isEmptyObject(data)){
				//Nos casos onde existia um número muito grande linhas, o browser estava apresentando lentidão. Caso o número de linhas seja > configuracao do Mà³dulo, direcionar para tela de search
				if(parseInt(data.numrows)>parseInt(CB.jsonModulo.limite)||data.numrows>2000){
					alertAtencao("Mais de "+CB.jsonModulo.limite+" resultados foram encontrados!\n<a href='?" + vGetAutofiltro+"' target='_blank' style='color:#00009a;'><i class='fa fa-filter'></i> Clique aqui para filtrar os resultados encontrados.</a>");
					janelamodal("?" + vGetAutofiltro);
				}else{
					$("#cbModal").addClass("noventa").modal();
					var tblRes = CB.montaTableResultados(data, function(obj, event){

						oTr = $(obj);
						oTr.css("backgroundColor","transparent");
						
							//janelamodal("'"+urlmodal+"'" + oTr.attr("goParam"));
					
						janelamodal('?'+urlmodal+'&' + oTr.attr("goParam"));
						
					});
					$("#cbModal #cbModalCorpo").html(tblRes);

					if(data.numrows){
						$("#resultadosEncontrados").html("("+data.numrows+" resultados encontrados)").attr("cbnumrows",data.numrows);
					}
				}
			}else{
				
					alert("Nenhum resultado encontrado.");
				
			}
		},
		complete: function(){
			CB.aguarde(false);
			if(CB.limparResultados==true){
				CB.resetDadosPesquisa();
			}
		}
	});
	}
	}
}

$('#cbModal').one('hide.bs.modal', function(){
	CB.inicializaModulo();
});
	<? // } ?>