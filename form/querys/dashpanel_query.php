<?
class DashPanelQuery
{
    public static function buscarGruposDashPanelPorIdLpIdDashCardEIdEmpresa()
    {
        return "SELECT
                    c.cardurl,
                    c.cardtitle,
                    c.cardurlmodal,
                    c.modulo,
                    c.cardurl as cardatrasourl,
                    c.status as statuscard,
                    c.cardcolor,
                    c.cardtitlemodal,
                    c.tab,
                    c.code as code,
                    c.iddashcard,
                    g.ordem as grupo_ordem,
                    g.iddashgrupo,
                    c.idempresa,
                    p.ordem as panel_ordem,
                    c.ordem as card_ordem,
                    c.cardbordercolor,
                    c.tipocalculo,
                    c.colcalc,
                    c.mascararotulo as masc,
                    (if(c.tipoobjeto = 'fluxostatus', c.objeto, '')) as idfluxostatus
                from dashpanel as p
                join dashcard as c on (c.iddashpanel=p.iddashpanel) 
                join dashboard as d on (d.iddashcard=c.iddashcard)
                join dashgrupo as g on (g.iddashgrupo=p.iddashgrupo)
                JOIN "._DBCARBON."._lpobjeto lo on lo.tipoobjeto='dashboard' 
                                and lo.idobjeto=c.iddashcard
                                and lo.idlp in(?lps?)
                JOIN empresa e ON e.idempresa = p.idempresa				
                where p.status = 'ATIVO' and c.status = 'ATIVO' and g.status = 'ATIVO'
                and c.tipoobjeto = 'manual'  and c.idempresa in (?idempresa?)
                and c.iddashcard = ?iddashcard?";
    }
}

?>