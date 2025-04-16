<?
class Vw8AssinaturasPendentes
{
    public static function buscarCardsAssinaturaPendente()
    {
        return "SELECT
                    ifnull(count(s.idcarrimbo),0) as card_value,
                    SUM(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(DATE_ADD(s.alteradoem,INTERVAL 3 DAY),'%Y-%m-%d') and s.status not in ('ASSINADO','REJEITADO'), 1, 0)) AS card_atraso_value,
                    concat('_modulo=assinaturapendente&_pagina=0&_ordcol=alteradoem&_orddir=asc&idcarrimbo=[',group_concat(idcarrimbo separator ','),']')as card_url,
                    concat('_modulo=assinaturapendente&_pagina=0&_ordcol=alteradoem&_orddir=asc&idcarrimbo=[',GROUP_CONCAT(IF(DATE_FORMAT(NOW(), '%Y-%m-%d') > DATE_FORMAT(DATE_ADD(s.alteradoem,INTERVAL 3 DAY),'%Y-%m-%d') and s.status not in ('ASSINADO','REJEITADO'), s.idcarrimbo, null)),']') AS card_atraso_url,
                    'assinaturapendente' as modulo,
                    ifnull(m.rotulomenu,'') as card_title_modal,
                    '_modulo=assinaturapendente&_acao=u' as card_url_modal,
                    g.ordem as 	grupo_ordem,
                    g.iddashgrupo as iddashgrupo,
                    e.idempresa,
                    d.card_color as card_color,
                    d.card_border_color as card_border_color,
                    p.ordem as 	panel_ordem,
                    s.rotulomenu as card_ordem
                From vw8assinaturaspendentes s
                join dashboard d on d.iddashcard = 2252
                join dashcard c on c.iddashcard = 2252
                join dashpanel p on p.iddashpanel = c.iddashpanel
                join dashgrupo g on g.iddashgrupo = p.iddashgrupo
                left join objetovinculo ov on ov.idobjeto = p.iddashpanel and ov.tipoobjeto = 'dashpanel'
                left join carbonnovo._modulo m on m.idmodulo = ov.idobjetovinc and tipoobjetovinc = 'modulo'
                JOIN empresa e ON e.idempresa = '?idempresa?'
                where s.status = 'PENDENTE'	
                and s.idpessoa = '?idpessoa?'
                --  and s.tipoobjeto = '".$_REQUEST["iddashcard"]."'
                group by s.tipoobjeto";
    }
}

?>