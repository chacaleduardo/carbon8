<?
require_once(__DIR__."/_iquery.php");

class DashCardQuery implements DefaultQuery
{
    public static $table = 'dashcard';
    public static $pk = 'iddashcard';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarDashCardPorIdDashCard()
    {
        return "SELECT * FROM dashcard WHERE iddashcard IN (?iddashcard?)";
    }

    public static function buscarDashboardFixoPorIdDashCard()
    {
        return "SELECT
                    g.iddashgrupo, 
                    d.iddashcard,
                    p.iddashpanel,
                    p.paneltitle,
                    g.rotulo,
                    d.ordem as dashcardorder, 
                    p.ordem as panelorder, 
                    g.ordem as grouporder,
                    d.cardordenacao, 
                    d.cardsentido, 
                    d.cardurl, 
                    d.code,
                    d.cardcolor,
                    d.cardbordercolor,
                    d.cardtitle,
                    d.cardtitlesub,
                    d.cardtitlemodal,
                    d.cardurlmodal,
                    d.tab,
                    d.modulo
                FROM dashcard d
                JOIN dashpanel p on p.iddashpanel = d.iddashpanel
                JOIN dashgrupo g on g.iddashgrupo = p.iddashgrupo
                WHERE d.iddashcard = ?iddashcard?";
    }

    public static function buscarDashCardFixo()
    {
        return "SELECT
                '?iddashgrupo?' as iddashgrupo,
                '?rotulo?' as grupo_rotulo,
                '?iddashcard?' as iddashcard,
                'N' as iddashcardreal,
                '?iddashpanel?' as panel_id,
                '' as panel_class_col,
                '?paneltitle?' as panel_title,
                '' as card_id,
                'col-md-12 col-sm-12 col-xs-12' as card_class_col,
                ?cardurl? as card_url,
                '' AS card_atraso_url,
                '' as card_url_tipo,
                '' as card_url_js,
                'alteradoem' as ordenacao,
                'asc' as sentido,
                '' as card_notification_bg,
                'N' as card_notification,
                ?cardcolor? as card_color,
                ?cardbordercolor? as card_border_color,
                '' as card_bg_class,
                '?cardtitle?' as card_title,
                '?cardtitlesub?' as card_title_sub,
                count(1) as card_value,
                '' AS card_atraso_value,
                '' as card_icon,
                '' as card_row,
                '?cardtitlemodal?' as card_title_modal,
                '?cardurlmodal?' as card_url_modal,
                '?dashcardorder?' as card_ordem,
                '?panelorder?' as 	panel_ordem,
                '?grouporder?' as 	grupo_ordem,
                '?code?' as 	code,
                '?tab?' as 	tab,
                '?modulo?' as modulo,
                '' as col,
                '' as tipoobjeto,
                '' as objeto,
                CONCAT('?_modulo=', '?modulo?','&_acao=i') AS panel_insert,
                CONCAT('?_modulo=', '?modulo?') AS panel_pesquisa,
                e.idempresa,
                e.sigla,
                e.corsistema,
                (if(c.tipoobjeto = 'fluxostatus', c.objeto, '')) as idfluxostatus
            FROM ?tab? s
            JOIN dashcard c ON c.iddashcard = ?iddashcard?
            JOIN empresa e ON e.idempresa = '?idempresa?'
            WHERE 1
            ?clausula?
            ?group?";
    }

    public static function buscarDashCardPorTipoObjetoEStatus()
    {
        return "SELECT iddashcard FROM dashcard WHERE tipoobjeto = '?tipoobjeto?' AND status = '?status?'";
    }

    public static function buscarDashboardCardPanelGrupo()
    {
        return "SELECT DISTINCT * 
                FROM (
                    SELECT
                        g.iddashgrupo as iddashgrupo,
                        g.rotulo as grupo_rotulo,
                        s.ideventotipo as iddashcard,
                        2174 as iddashcardreal,
                        p.iddashpanel as panel_id,
                        '' as panel_class_col,
                        paneltitle as panel_title,
                        s.ideventotipo as card_id,
                        'col-md-12 col-sm-12 col-xs-12' as card_class_col,
                        concat('_modulo=evento&_pagina=0&_ordcol=prazo&_orddir=asc&idevento=[',group_concat(idevento separator ','),']')as card_url,
                        concat('_modulo=evento&_pagina=0&_ordcol=prazo&_orddir=asc&idevento=[',GROUP_CONCAT(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(s.prazo,'%Y-%m-%d') and statustipoevento not in ('CONCLUIDO','CANCELADO'), s.idevento, null)),']') AS card_atraso_url,
                        '' as card_url_tipo,
                        '' as card_url_js,
                        'prazo' as ordenacao,
                        'asc' as sentido,
                        '' as card_notification_bg,
                        'N' as card_notification,
                        d.card_color as card_color,
                        d.card_border_color as card_border_color,
                        '' as card_bg_class,
                        eventotipo as card_title,
                        '' as card_title_sub,
                        count(s.idevento) as card_value,
                        SUM(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(s.prazo,'%Y-%m-%d') and statustipoevento not in ('CONCLUIDO','CANCELADO'), 1, 0)) AS card_atraso_value,
                        '' as card_icon,
                        '' as card_row,
                        'Eventos' as card_title_modal,
                        '_modulo=evento&_acao=u' as card_url_modal,
                        eventotipo as card_ordem,
                        p.ordem as 	panel_ordem,
                        g.ordem as 	grupo_ordem,
                        '' as 	code,
                        '' as 	tab,
                        'evento' as modulo,
                        'idevento' as 	col,
                        'eventotipo' as tipoobjeto,
                        s.ideventotipo as objeto,
                        CONCAT('?_modulo=', m.modulo,'&_acao=i') AS panel_insert,
                        CONCAT('?_modulo=', m.modulo) AS panel_pesquisa,
                        e.idempresa,
                        e.sigla,
                        e.corsistema,
                        (if(c.tipoobjeto = 'fluxostatus', c.objeto, '')) as idfluxostatus
                    FROM vw8eventopessoa s
                        join dashcard c on c.iddashcard = 2174
                        join dashboard d on c.iddashcard = d.iddashcard
                        join dashpanel p on p.iddashpanel = c.iddashpanel
                        join dashgrupo g on g.iddashgrupo = p.iddashgrupo
                        left join objetovinculo ov on ov.idobjeto = p.iddashpanel and ov.tipoobjeto = 'dashpanel'
                        left join "._DBCARBON."._modulo m on m.idmodulo = ov.idobjetovinc and tipoobjetovinc = 'modulo'
                        JOIN empresa e ON e.idempresa = '?idempresa?'
                    WHERE s.dashboard = 'Y'
                    AND s.idpessoa = '?idpessoa?'
                    AND s.oculto != 1
                    AND s.ideventotipo != 39
                    GROUP BY s.ideventotipo
                    UNION ALL
                    SELECT
                        g.iddashgrupo as iddashgrupo,
                        g.rotulo as grupo_rotulo,
                        s.tipoobjeto as iddashcard,
                        2252 as iddashcardreal,
                        p.iddashpanel as panel_id,
                        '' as panel_class_col,
                        paneltitle as panel_title,
                        s.tipoobjeto as card_id,
                        'col-md-12 col-sm-12 col-xs-12' as card_class_col,
                        concat('_modulo=assinaturapendente&_pagina=0&_ordcol=alteradoem&_orddir=asc&idcarrimbo=[',group_concat(idcarrimbo separator ','),']')as card_url,
                        concat('_modulo=assinaturapendente&_pagina=0&_ordcol=alteradoem&_orddir=asc&idcarrimbo=[',GROUP_CONCAT(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(DATE_ADD(s.alteradoem,INTERVAL 3 DAY),'%Y-%m-%d') and s.status not in ('ASSINADO','REJEITADO'), s.idcarrimbo, null)),']') AS card_atraso_url,
                        '' as card_url_tipo,
                        '' as card_url_js,
                        'alteradoem' as ordenacao,
                        'asc' as sentido,
                        '' as card_notification_bg,
                        'N' as card_notification,
                        'primary' as card_color,
                        'primary' as card_border_color,
                        '' as card_bg_class,
                        s.rotulomenu as card_title,
                        '' as card_title_sub,
                        count(s.idcarrimbo) as card_value,
                        SUM(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(DATE_ADD(s.alteradoem,INTERVAL 3 DAY),'%Y-%m-%d') and s.status not in ('ASSINADO','REJEITADO'), 1, 0)) AS card_atraso_value,
                        '' as card_icon,
                        '' as card_row,
                        m.rotulomenu as card_title_modal,
                        '_modulo=assinaturapendente&_acao=u' as card_url_modal,
                        s.rotulomenu as card_ordem,
                        p.ordem as 	panel_ordem,
                        g.ordem as 	grupo_ordem,
                        '' as 	code,
                        '' as 	tab,
                        'assinaturapendente' as modulo,
                        'idcarrimbo' as 	col,
                        'eventotipo' as tipoobjeto,
                        s.tipoobjeto as objeto,
                        CONCAT('?_modulo=', m.modulo,'&_acao=i') AS panel_insert,
                        CONCAT('?_modulo=', m.modulo) AS panel_pesquisa,
                        e.idempresa,
                        e.sigla,
                        e.corsistema,
                        (if(c.tipoobjeto = 'fluxostatus', c.objeto, '')) as idfluxostatus
                    FROM vw8assinaturaspendentes s
                    join sgdoc on(sgdoc.idsgdoc = s.idobjeto and s.tipoobjeto = 'documento')
                    join dashcard c on c.iddashcard = 2252
                    join dashpanel p on p.iddashpanel = c.iddashpanel
                    join dashgrupo g on g.iddashgrupo = p.iddashgrupo
                    left join objetovinculo ov on ov.idobjeto = p.iddashpanel and ov.tipoobjeto = 'dashpanel'
                    left join carbonnovo._modulo m on m.idmodulo = ov.idobjetovinc and tipoobjetovinc = 'modulo'
                    JOIN empresa e ON e.idempresa = '?idempresa?'
                    where s.status = 'PENDENTE'	
                    and s.idpessoa = '?idpessoa?'
                    and sgdoc.status != 'REVISAO'
                    group by s.tipoobjeto
                    UNION ALL
                    select
                        g.iddashgrupo as iddashgrupo,
                        g.rotulo as grupo_rotulo,
                        s.tipoobjeto as iddashcard,
                        2252 as iddashcardreal,
                        p.iddashpanel as panel_id,
                        '' as panel_class_col,
                        paneltitle as panel_title,
                        s.tipoobjeto as card_id,
                        'col-md-12 col-sm-12 col-xs-12' as card_class_col,
                        concat('_modulo=assinaturapendente&_pagina=0&_ordcol=alteradoem&_orddir=asc&idcarrimbo=[',group_concat(idcarrimbo separator ','),']')as card_url,
                        concat('_modulo=assinaturapendente&_pagina=0&_ordcol=alteradoem&_orddir=asc&idcarrimbo=[',GROUP_CONCAT(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(DATE_ADD(s.alteradoem,INTERVAL 3 DAY),'%Y-%m-%d') and s.status not in ('ASSINADO','REJEITADO'), s.idcarrimbo, null)),']') AS card_atraso_url,
                        '' as card_url_tipo,
                        '' as card_url_js,
                        'alteradoem' as ordenacao,
                        'asc' as sentido,
                        '' as card_notification_bg,
                        'N' as card_notification,
                        'primary' as card_color,
                        'primary' as card_border_color,
                        '' as card_bg_class,
                        s.rotulomenu as card_title,
                        '' as card_title_sub,
                        count(s.idcarrimbo) as card_value,
                        SUM(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(DATE_ADD(s.alteradoem,INTERVAL 3 DAY),'%Y-%m-%d') and s.status not in ('ASSINADO','REJEITADO'), 1, 0)) AS card_atraso_value,
                        '' as card_icon,
                        '' as card_row,
                        m.rotulomenu as card_title_modal,
                        '_modulo=assinaturapendente&_acao=u' as card_url_modal,
                        s.rotulomenu as card_ordem,
                        p.ordem as 	panel_ordem,
                        g.ordem as 	grupo_ordem,
                        '' as 	code,
                        '' as 	tab,
                        'assinaturapendente' as modulo,
                        'idcarrimbo' as 	col,
                        'eventotipo' as tipoobjeto,
                        s.tipoobjeto as objeto,
                        CONCAT('?_modulo=', m.modulo,'&_acao=i') AS panel_insert,
                        CONCAT('?_modulo=', m.modulo) AS panel_pesquisa,
                        e.idempresa,
                        e.sigla,
                        e.corsistema,
                        (if(c.tipoobjeto = 'fluxostatus', c.objeto, '')) as idfluxostatus
                    From vw8assinaturaspendentes s
                        join lote l on(l.idlote = s.idobjeto and s.tipoobjeto like '%lote%')
                        join dashcard c on c.iddashcard = 2252
                        join dashpanel p on p.iddashpanel = c.iddashpanel
                        join dashgrupo g on g.iddashgrupo = p.iddashgrupo
                        left join objetovinculo ov on ov.idobjeto = p.iddashpanel and ov.tipoobjeto = 'dashpanel'
                        left join carbonnovo._modulo m on m.idmodulo = ov.idobjetovinc and tipoobjetovinc = 'modulo'
                        JOIN empresa e ON e.idempresa = '?idempresa?'
                    where s.status = 'PENDENTE'	
                    and s.idpessoa = '?idpessoa?'
                    and l.status != 'CANCELADO'
                    group by s.tipoobjeto
                    UNION ALL
                    ".self::buscarDashboardSolicitacoes()."
                    ?querydashfixo?
                ) a
                where 
                    exists (select 
                            1
                        from 
                            "._DBCARBON."._modulo m 
                        join 
                            objempresa oe on oe.idobjeto = m.idmodulo and oe.objeto = 'modulo' and oe.empresa = ".cb::idempresa()."
                        where 
                            m.modulo = a.modulo
                        )
                    and card_value > 0
                order by
                    grupo_ordem+0, grupo_ordem,
                    iddashgrupo+0, iddashgrupo,
                    idempresa+0, idempresa,
                    panel_ordem+0, panel_ordem,
                    card_ordem+0, card_ordem;";
    }

    public static function buscarInformacaoDashCardPorTabelaEIdDashCard()
    {
        return "SELECT  
                    c.iddashcard as iddashcard, 
                    concat('_modulo=?modulo?','&','?coluna?=[',GROUP_CONCAT(distinct e.?coluna?),']') as card_url,
                    COUNT(distinct e.?coluna?) as card_value,
                    'primary' as card_color,
                    'primary' as card_border_color,
                    c.modulo as modulo,
                    c.cardtitlemodal as card_title_modal,
                    c.cardurlmodal as card_url_modal
                FROM ?tabela? e
                JOIN dashcard c
                WHERE  c.status = 'ATIVO'
                AND c.iddashcard = ?iddashcard?
                ?clausula?";
    }

    public static function buscarCardComModuloVinculadoPorIdEmpresa()
    {
        return "SELECT distinct * 
                FROM (
                    ?querydinamicacard?
                ) a
                where exists (
                    select 1
                    from  "._DBCARBON."._modulo m 
                    join objempresa oe on oe.idobjeto = m.idmodulo and oe.objeto = 'modulo' and oe.empresa = ?idempresa?
                    where m.modulo = a.modulo
                )
                order by
                grupo_ordem+0, grupo_ordem,
                iddashgrupo+0, iddashgrupo,
                idempresa+0, idempresa,
                panel_ordem+0, panel_ordem,
                card_ordem+0, card_ordem;";
    }

    public static function buscarDashboardSolicitacoes()
    {
        return "SELECT
                    27 as iddashgrupo,
                    'MEU GERENCIAMENTO' as grupo_rotulo,
                    3991 as iddashcard,
                    3991 as iddashcardreal,
                    1249 as panel_id,
                    '' as panel_class_col,
                    'SOLICITAÇÕES' as panel_title,
                    3991 as card_id,
                    'col-md-12 col-sm-12 col-xs-12' as card_class_col,
                    concat('_modulo=comprasrhrestrito&idnf=[',group_concat(s.idnf),']') as card_url,
                    '' AS card_atraso_url,
                    '' as card_url_tipo,
                    '' as card_url_js,
                    'alteradoem' as ordenacao,
                    'asc' as sentido,
                    '' as card_notification_bg,
                    'N' as card_notification,
                    'primary' as card_color,
                    'primary' as card_border_color,
                    '' as card_bg_class,
                    'NF Aprovada' as card_title,
                    'Etapa 3' as card_title_sub,
                    count(s.idnf) as card_value,
                    '' AS card_atraso_value,
                    '' as card_icon,
                    '' as card_row,
                    '' as card_title_modal,
                    '_modulo=comprasrhrestrito&_acao=u' as card_url_modal,
                    'NF Aprovada' as card_ordem,
                    '01' as panel_ordem,
                    '03' as grupo_ordem,
                    '' as 	code,
                    '' as 	tab,
                    'comprasrhrestrito' as modulo,
                    '' as 	col,
                    '' as tipoobjeto,
                    '' as objeto,
                    '' AS panel_insert,
                    '' AS panel_pesquisa,
                    e.idempresa,
                    e.sigla,
                    e.corsistema,
                    s.idfluxostatus as idfluxostatus
                From (
                    SELECT 
                        `n`.`idnf` AS `idnf`, `c`.`idcontato` AS `idcontato`, n.status, fs.idfluxostatus
                    FROM `nf` `n`
                    JOIN fluxostatus fs ON(fs.idfluxostatus = n.idfluxostatus)
                    JOIN "._DBCARBON."._status cs ON(cs.idstatus = fs.idstatus)
                    JOIN `pessoa` `p` ON (`p`.`idpessoa` = `n`.`idpessoa` AND `p`.`idtipopessoa` IN (5 , 12) and n.idempresa = p.idempresa)                                
                    JOIN `pessoacontato` `c` ON (`n`.`idpessoa` = `c`.`idpessoa`)
                    WHERE ((`n`.`tiponf` = 'R'))
                    and (`cs`.`statustipo` in ('APROVADO'))
                    and c.idcontato = '?idpessoa?'
                    GROUP BY `n`.`idnf`
                ) s
                JOIN empresa e ON e.idempresa = '?idempresa?'";
    }

    public static function buscarDashCardPorTabela()
    {
        return "SELECT tab as tab FROM dashcard WHERE tab ='?tabela?'";
    }
}

?>