<?
class VwNfQuery
{
    public static function buscarDashboardAdministrativo()
    {
        return "SELECT
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
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'ADM -NFS A FATURAR' as card_title_modal,
                    '_modulo=nfs&_acao=u' as card_url_modal
                FROM vwnf
                WHERE status = 'FECHADO' ";
    }
}


?>