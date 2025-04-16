<?
class VwFormalizacaoQuery
{
    public static function buscarDashboardAutogenas()
    {
        return "SELECT
                    'dashproducaoconcentrado' as panel_id,
                    'col-md-6' as panel_class_col,
                    'PRODUÇÃO DE CONCENTRADOS' as panel_title,
                    'concentradosaproduzir' as card_id,
                    'col-md-4 col-sm-4 col-xs-6' as card_class_col,
                    '_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&idtipoprodserv=19&especial=N&_filtrosrapidos={%22status%22:%22TRIAGEM%22}' as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    if (count(1) > 0,'danger','success') as card_color,
                    if (count(1) > 0,'danger','success') as card_border_color,
                    '' as card_bg_class,
                    'a produzir' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'PRODUÇÃO DE CONCENTRADOS - A PRODUZIR' as card_title_modal,
                    '_modulo=formalizacao&_acao=u' as card_url_modal
                FROM vwformalizacao
                WHERE idtipoprodserv = 19 
                AND especial = 'N' 
                AND idunidade = 2 
                AND status = 'TRIAGEM'
                ?getidempresa?
                UNION ALL
                SELECT
                    'dashproducaoconcentrado' as panel_id,
                    'col-md-6' as panel_class_col,
                    'PRODUÇÃO DE CONCENTRADOS' as panel_title,
                    'concentradosmespassado' as card_id,
                    'col-md-4 col-sm-4 col-xs-6' as card_class_col,
                    concat('_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&idtipoprodserv=19&especial=N&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22}&_fds=', DATE_FORMAT(last_day(curdate() - interval 2 month) + interval 1 day,'%d/%m/%Y'),'-',DATE_FORMAT(last_day(curdate() - interval 1 month),'%d/%m/%Y')) as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    'success' as card_color,
                    'success' as card_border_color,
                    '' as card_bg_class,
                    'mês passado' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'PRODUÇÃO DE CONCENTRADOS - MÊS PASSADO' as card_title_modal,
                    '_modulo=formalizacao&_acao=u' as card_url_modal
                FROM vwformalizacao
                WHERE idtipoprodserv = 19 
                AND especial = 'N' 
                AND fabricacao between last_day(curdate() - interval 2 month) + interval 1 day AND last_day(curdate() - interval 1 month)
                AND status IN ('APROVADO','ESGOTADO')
                ?getidempresa?
                UNION ALL
                SELECT
                    'dashproducaoconcentrado' as panel_id,
                    'col-md-6' as panel_class_col,
                    'PRODUÇÃO DE CONCENTRADOS' as panel_title,
                    'concentradosestemes' as card_id,
                    'col-md-4 col-sm-4 col-xs-6' as card_class_col,
                    concat('_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&idtipoprodserv=19&especial=N&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22}&_fds=', DATE_FORMAT(last_day(curdate() - interval 1 month) + interval 1 day,'%d/%m/%Y'),'-',DATE_FORMAT(last_day(curdate()),'%d/%m/%Y')) as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    'success' as card_color,
                    'success' as card_border_color,
                    '' as card_bg_class,
                    'este mês' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'PRODUÇÃO DE CONCENTRADOS - ESTE MÊS' as card_title_modal,
                    '_modulo=formalizacao&_acao=u' as card_url_modal
                FROM vwformalizacao
                WHERE idtipoprodserv = 19 
                AND especial = 'N' 
                AND status in ('APROVADO','ESGOTADO')
                AND fabricacao between last_day(curdate() - interval 1 month) + interval 1 day AND  last_day(curdate())
                ?getidempresa?
                UNION ALL
                SELECT
                    'dashproducaovacina' as panel_id,
                    'col-md-6' as panel_class_col,
                    'PRODUÇÃO DE VACINAS' as panel_title,
                    'vacinasaproduzir' as card_id,
                    'col-md-4 col-sm-4 col-xs-6' as card_class_col,
                    '_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&_fts=vacina&especial=N&_filtrosrapidos={%22status%22:%22TRIAGEM%22}' as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    if (count(1) > 0,'danger','success') as card_color,
                    if (count(1) > 0,'danger','success') as card_border_color,
                    '' as card_bg_class,
                    'a produzir' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'PRODUÇÃO DE VACINAS - A PRODUZIR' as card_title_modal,
                    '_modulo=formalizacao&_acao=u' as card_url_modal		
                FROM vwformalizacao
                WHERE descr like '%vacina%'
                AND especial = 'N'
                AND idunidade = 2
                AND status = 'TRIAGEM'
                ?getidempresa?
                UNION ALL
                SELECT
                    'dashproducaovacina' as panel_id,
                    'col-md-6' as panel_class_col,
                    'PRODUÇÃO DE VACINAS' as panel_title,
                    'vacinasmespassado' as card_id,
                    'col-md-4 col-sm-4 col-xs-6' as card_class_col,
                    concat('_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&_fts=vacina&especial=N&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22}&_fds=', DATE_FORMAT(last_day(curdate() - interval 2 month) + interval 1 day,'%d/%m/%Y'),'-',DATE_FORMAT(last_day(curdate() - interval 1 month),'%d/%m/%Y')) as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    'success' as card_color,
                    'success' as card_border_color,
                    '' as card_bg_class,
                    'mês passado' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'PRODUÇÃO DE VACINAS - MÊS PASSADO' as card_title_modal,
                    '_modulo=formalizacao&_acao=u' as card_url_modal
                FROM vwformalizacao
                WHERE descr like '%vacina%'
                AND especial = 'N' 
                AND fabricacao between last_day(curdate() - interval 2 month) + interval 1 day and last_day(curdate() - interval 1 month)
                AND status IN ('APROVADO','ESGOTADO')
                ?getidempresa?
                UNION ALL
                SELECT
                    'dashproducaovacina' as panel_id,
                    'col-md-6' as panel_class_col,
                    'PRODUÇÃO DE VACINAS' as panel_title,
                    'vacinasestemes' as card_id,
                    'col-md-4 col-sm-4 col-xs-6' as card_class_col,
                    concat('_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&_fts=vacina&especial=N&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22}&_fds=', DATE_FORMAT(last_day(curdate() - interval 1 month) + interval 1 day,'%d/%m/%Y'),'-',DATE_FORMAT(last_day(curdate()),'%d/%m/%Y')) as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    'success' as card_color,
                    'success' as card_border_color,
                    '' as card_bg_class,
                    'este mês' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'PRODUÇÃO DE VACINAS - ESTE MÊS' as card_title_modal,
                    '_modulo=formalizacao&_acao=u' as card_url_modal
                FROM vwformalizacao
                WHERE descr like '%vacina%' 
                AND especial = 'N' 
                AND status in ('APROVADO','ESGOTADO')
                AND fabricacao between last_day(curdate() - interval 1 month) + interval 1 day and  last_day(curdate())
                ?getidempresa?";
    }

    public static function buscarDashboardProducao()
    {
        return "SELECT
                    'dashautogenasconcentrado' as panel_id,
                    'col-md-6' as panel_class_col,
                    'PRODUÇÃO DE CONCENTRADOS AUTÓGENAS' as panel_title,
                    'dashautogenasconcentradosaproduzir' as card_id,
                    'col-md-4 col-sm-4 col-xs-6' as card_class_col,
                    '_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&idtipoprodserv=19&especial=Y&_filtrosrapidos={%22status%22:%22TRIAGEM%22}' as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    if (count(1) > 0,'danger','success') as card_color,
                    if (count(1) > 0,'danger','success') as card_border_color,
                    '' as card_bg_class,
                    'a produzir' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'PRODUÇÃO DE CONCENTRADOS AUTÓGENAS - A PRODUZIR' as card_title_modal,
                    '_modulo=formalizacao&_acao=u' as card_url_modal
                FROM vwformalizacao
                WHERE idtipoprodserv = 19 
                AND especial = 'Y' 
                AND idunidade = 2 
                AND status = 'TRIAGEM'
                ?getidempresa?
                UNION ALL
                SELECT
                    'dashautogenasconcentrado' as panel_id,
                    'col-md-6' as panel_class_col,
                    'PRODUÇÃO DE CONCENTRADOS AUTÓGENAS' as panel_title,
                    'dashautogenasconcentradosmespassado' as card_id,
                    'col-md-4 col-sm-4 col-xs-6' as card_class_col,
                    concat('_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&idtipoprodserv=19&especial=Y&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22}&_fds=', DATE_FORMAT(last_day(curdate() - interval 2 month) + interval 1 day,'%d/%m/%Y'),'-',DATE_FORMAT(last_day(curdate() - interval 1 month),'%d/%m/%Y')) as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    'success' as card_color,
                    'success' as card_border_color,
                    '' as card_bg_class,
                    'mês passado' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'PRODUÇÃO DE CONCENTRADOS AUTÓGENAS - MÊS PASSADO' as card_title_modal,
                    '_modulo=formalizacao&_acao=u' as card_url_modal
                FROM vwformalizacao
                WHERE idtipoprodserv = 19
                AND especial = 'Y' 
                AND fabricacao between last_day(curdate() - interval 2 month) + interval 1 day AND last_day(curdate() - interval 1 month)
                AND status IN ('APROVADO','ESGOTADO')
                ?getidempresa?
                UNION ALL
                SELECT
                    'dashautogenasconcentrado' as panel_id,
                    'col-md-6' as panel_class_col,
                    'PRODUÇÃO DE CONCENTRADOS AUTÓGENAS' as panel_title,
                    'dashautogenasconcentradosestemes' as card_id,
                    'col-md-4 col-sm-4 col-xs-6' as card_class_col,
                    concat('_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&idtipoprodserv=19&especial=Y&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22}&_fds=', DATE_FORMAT(last_day(curdate() - interval 1 month) + interval 1 day,'%d/%m/%Y'),'-',DATE_FORMAT(last_day(curdate()),'%d/%m/%Y')) as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    'success' as card_color,
                    'success' as card_border_color,
                    '' as card_bg_class,
                    'este mês' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'PRODUÇÃO DE CONCENTRADOS AUTÓGENAS - ESTE MÊS' as card_title_modal,
                    '_modulo=formalizacao&_acao=u' as card_url_modal
                FROM vwformalizacao 
                WHERE idtipoprodserv = 19
                AND especial = 'Y' 
                AND status IN ('APROVADO','ESGOTADO')
                AND fabricacao between last_day(curdate() - interval 1 month) + interval 1 day AND  last_day(curdate())
                ?getidempresa?
                UNION ALL
                SELECT
                    'dashautogenasvacina' as panel_id,
                    'col-md-6' as panel_class_col,
                    'PRODUÇÃO DE VACINAS AUTÓGENAS' as panel_title,
                    'dashautogenasvacinasaproduzir' as card_id,
                    'col-md-4 col-sm-4 col-xs-6' as card_class_col,
                    '_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&_fts=vacina&especial=Y&_filtrosrapidos={%22status%22:%22TRIAGEM%22}' as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    if (count(1) > 0,'danger','success') as card_color,
                    if (count(1) > 0,'danger','success') as card_border_color,
                    '' as card_bg_class,
                    'a produzir' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'PRODUÇÃO DE VACINAS AUTÓGENAS - A PRODUZIR' as card_title_modal,
                    '_modulo=formalizacao&_acao=u' as card_url_modal
                FROM vwformalizacao
                WHERE descr like '%vacina%'
                AND especial = 'Y'
                AND idunidade = 2 
                AND status = 'TRIAGEM'
                ?getidempresa?
                UNION ALL
                SELECT
                    'dashautogenasvacina' as panel_id,
                    'col-md-6' as panel_class_col,
                    'PRODUÇÃO DE VACINAS AUTÓGENAS' as panel_title,
                    'dashautogenasvacinasmespassado' as card_id,
                    'col-md-4 col-sm-4 col-xs-6' as card_class_col,
                    concat('_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&_fts=vacina&especial=Y&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22}&_fds=', DATE_FORMAT(last_day(curdate() - interval 2 month) + interval 1 day,'%d/%m/%Y'),'-',DATE_FORMAT(last_day(curdate() - interval 1 month),'%d/%m/%Y')) as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    'success' as card_color,
                    'success' as card_border_color,
                    '' as card_bg_class,
                    'mês passado' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'PRODUÇÃO DE VACINAS AUTÓGENAS - MÊS PASSADO' as card_title_modal,
                    '_modulo=formalizacao&_acao=u' as card_url_modal
                FROM vwformalizacao
                WHERE descr like '%vacina%'
                AND especial = 'Y' 
                AND fabricacao between last_day(curdate() - interval 2 month) + interval 1 day AND last_day(curdate() - interval 1 month)
                AND status IN ('APROVADO','ESGOTADO')
                ?getidempresa?
                UNION ALL
                SELECT
                    'dashautogenasvacina' as panel_id,
                    'col-md-6' as panel_class_col,
                    'PRODUÇÃO DE VACINAS AUTÓGENAS' as panel_title,
                    'dashautogenasvacinasestemes' as card_id,
                    'col-md-4 col-sm-4 col-xs-6' as card_class_col,
                    concat('_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&_fts=vacina&especial=Y&_filtrosrapidos={%22status%22:%22APROVADO,ESGOTADO%22}&_fds=', DATE_FORMAT(last_day(curdate() - interval 1 month) + interval 1 day,'%d/%m/%Y'),'-',DATE_FORMAT(last_day(curdate()),'%d/%m/%Y')) as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    'success' as card_color,
                    'success' as card_border_color,
                    '' as card_bg_class,
                    'este mês' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'PRODUÇÃO DE VACINAS AUTÓGENAS - ESTE MÊS' as card_title_modal,
                    '_modulo=formalizacao&_acao=u' as card_url_modal
                FROM vwformalizacao
                WHERE descr like '%vacina%' 
                AND especial = 'Y' 
                AND status IN ('APROVADO','ESGOTADO')
                AND fabricacao between last_day(curdate() - interval 1 month) + interval 1 day AND  last_day(curdate())
                ?getidempresa?";
    }
}

?>