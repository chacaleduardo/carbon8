<?
class VwNfSaidaQuery
{
    public static function buscarDashboardLogistica()
    {
        return "SELECT
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
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'LOGÍSTICA - EM ANDAMENTO' as card_title_modal,
                    '_modulo=pedidologistica&_acao=u' as card_url_modal
                FROM vwnfsaida 
                WHERE status = 'DESPACHADO'
                ?getidempresa?
                UNION ALL
                SELECT
                    'dashlogistica' as panel_id,
                    'col-md-6' as panel_class_col,
                    'LOGÍSTICA - ENVIOS' as panel_title,
                    'dashlogisticaenviosematraso' as card_id,
                    'col-md-6 col-sm-6 col-xs-6' as card_class_col,
                    concat('_modulo=pedidologistica&_pagina=0&_ordcol=idnf&_orddir=desc&_filtrosrapidos={%22status%22:%22ENVIAR%22}&_fds=', DATE_FORMAT((curdate() - interval 1 month),'%d/%m/%Y'),'-',DATE_FORMAT((curdate() + interval 3 day),'%d/%m/%Y')) as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    if (count(1) > 0,'danger','success') as card_color,
                    if (count(1) > 0,'danger','success') as card_border_color,
                    '' as card_bg_class,
                    'em atraso' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'LOGÍSTICA - ENVIOS EM ATRASO' as card_title_modal,
                    '_modulo=pedidologistica&_acao=u' as card_url_modal
                FROM vwnfsaida
                WHERE status = 'DESPACHAR'
                AND DATE_ADD(envio, interval 3 day) < CURRENT_DATE
                ?getidempresa?";
    }
}

?>