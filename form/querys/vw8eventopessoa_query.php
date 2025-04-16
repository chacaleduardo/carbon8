<?
class Vw8EventoPessoaQuery
{
    public static function buscarEventosAlerta()
    {
        return "SELECT 
                    concat('_modulo=evento&idevento=[',group_concat(idevento),']') as url,
                    count(1) as value,
                    'evento' as modulo,
                    'eventotipo' as  tipoobjeto,
                        ideventotipo as objeto
                FROM vw8eventopessoa 
                WHERE idpessoa = '?idpessoa?' 
                AND visualizado != 1 
                AND oculto = 0
                GROUP BY ideventotipo
                UNION ALL
                SELECT
                    concat('_modulo=evento&idevento=[',group_concat(idevento),']'),
                    count(1),
                    'evento' as modulo,
                    'fluxostatus' as  tipoobjeto,
                    idfluxostatus as objeto
                FROM vw8eventopessoa 
                WHERE idpessoa = '?idpessoa?' 
                AND visualizado != 1 
                AND oculto = 0
                GROUP BY idfluxostatus";
    }

    public static function buscarEventosCardFixo()
    {
        return "SELECT
                    ifnull(count(s.idevento),0) as card_value,
                    SUM(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(s.prazo,'%Y-%m-%d') and statustipoevento not in ('CONCLUIDO','CANCELADO'), 1, 0)) AS card_atraso_value,
                    concat('_modulo=evento&_pagina=0&_ordcol=prazo&_orddir=asc&idevento=[',group_concat(idevento separator ','),']')as card_url,
                    concat('_modulo=evento&_pagina=0&_ordcol=prazo&_orddir=asc&idevento=[',GROUP_CONCAT(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(s.prazo,'%Y-%m-%d') and statustipoevento not in ('CONCLUIDO','CANCELADO'), s.idevento, null)),']') AS card_atraso_url,
                    'evento' as modulo,
                    ifnull(m.rotulomenu,'') as card_title_modal,
                    '_modulo=evento&_acao=u' as card_url_modal,
                    g.ordem as 	grupo_ordem,
                    g.iddashgrupo as iddashgrupo,
                    e.idempresa,
                    d.card_color as card_color,
                    d.card_border_color as card_border_color,
                    p.ordem as 	panel_ordem,
                    eventotipo as card_ordem
                From vw8eventopessoa s
                join dashboard d on d.iddashcard = 2174
                join dashcard c on c.iddashcard = 2174
                join dashpanel p on p.iddashpanel = c.iddashpanel
                join dashgrupo g on g.iddashgrupo = p.iddashgrupo
                left join objetovinculo ov on ov.idobjeto = p.iddashpanel and ov.tipoobjeto = 'dashpanel'
                left join "._DBCARBON."._modulo m on m.idmodulo = ov.idobjetovinc and tipoobjetovinc = 'modulo'
                JOIN empresa e ON e.idempresa = '?idempresa?'
                where s.dashboard = 'Y'
                and s.idpessoa = '?idpessoa?'
                and s.oculto != 1
                and s.ideventotipo = '?iddashcard?'
                group by s.ideventotipo";
    }

    public static function buscarSuporteTecnologiaCardFixo()
    {
        return "SELECT
                    ifnull(count(s.idevento),0) as card_value,
                    SUM(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(s.prazo,'%Y-%m-%d') and statustipoevento not in ('CONCLUIDO','CANCELADO'), 1, 0)) AS card_atraso_value,
                    concat('_modulo=evento&_pagina=0&_ordcol=prazo&_orddir=asc&idevento=[',group_concat(idevento separator ','),']')as card_url,
                    concat('_modulo=evento&_pagina=0&_ordcol=prazo&_orddir=asc&idevento=[',GROUP_CONCAT(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(s.datafim,'%Y-%m-%d') and statustipoevento not in ('CONCLUIDO','CANCELADO'), s.idevento, null)),']') AS card_atraso_url,
                    'evento' as modulo,
                    ifnull(m.rotulomenu,'') as card_title_modal,
                    '_modulo=evento&_acao=u' as card_url_modal,
                    g.ordem as 	grupo_ordem,
                    g.iddashgrupo as iddashgrupo,
                    e.idempresa,
                    d.card_color as card_color,
                    d.card_border_color as card_border_color,
                    p.ordem as 	panel_ordem,
                    eventotipo as card_ordem
                From vw8eventopessoa s
                join dashboard d on d.iddashcard = 2174
                join dashcard c on c.iddashcard = 2174
                join dashpanel p on p.iddashpanel = c.iddashpanel
                join dashgrupo g on g.iddashgrupo = p.iddashgrupo
                left join objetovinculo ov on ov.idobjeto = p.iddashpanel and ov.tipoobjeto = 'dashpanel'
                left join "._DBCARBON."._modulo m on m.idmodulo = ov.idobjetovinc and tipoobjetovinc = 'modulo'
                JOIN empresa e ON e.idempresa = '?idempresa?'
                where s.dashboard = 'Y'
                and s.idpessoa = '?idpessoa?'
                and s.oculto != 1
                and s.ideventotipo = '?iddashcard?'
                group by s.ideventotipo";
    }

    public static function buscarEventoCardPorIdDashCard()
    {
        return "SELECT
                    concat('_modulo=evento&idevento=[',group_concat(idevento),']') as url,
                    count(1) as value
                FROM vw8eventopessoa 
                WHERE idpessoa = '?idpessoa?' 
                and visualizado != 1 
                and oculto = 0 
                and ideventotipo = ?iddashcard?
                group by ideventotipo
                union all
                select
                    concat('_modulo=evento&idevento=[',group_concat(idevento),']'),
                    count(1)
                From vw8eventopessoa 
                where idpessoa = '?idpessoa?' 
                and visualizado != 1 
                and oculto = 0 
                and idfluxostatus = ?iddashcard?
                group by idfluxostatus";
    }
}
