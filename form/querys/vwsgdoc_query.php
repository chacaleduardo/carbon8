<?
class VwSgDocQuery
{
    public static function buscarDashboardQualidade()
    {
        return "SELECT
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
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'DOCUMENTOS - ASSINATURA PENDENTE' as card_title_modal,
                    '_modulo=documento&_acao=u' as card_url_modal
                FROM vwsgdoc
                WHERE assinaturadoc = 'PENDENTE' 
                AND status = 'APROVADO'
                ?getidempresa?";
    }
}

?>