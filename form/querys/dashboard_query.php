<?
class DashboardQuery
{
	public static function atualizarDashboardGerencimentoConcentrados()
    {
        return "UPDATE dashboard 
				   SET card_value = '?haproduzir?', 
				   	   card_color = if(?haproduzir? > 0, 'danger', 'success'), 
					   card_border_color = if(?haproduzir? > 0, 'danger', 'success')
            	 WHERE iddashcard = '1370'";
    }

	public static function buscarDashboardPorIdDashCardEIdEmpresa()
	{
		return "SELECT 
					d.card_value as card_value, 
					d.card_atraso_value as card_atraso_value,
					d.modulo as modulo,
					d.card_title_modal as card_title_modal,
					d.card_url_modal as card_url_modal, 
					d.card_url as card_url, 
					d.card_atraso_url as card_atraso_url,
					d.card_url_tipo as card_url_tipo, 
					d.card_url_js as card_url_js, 
					d.card_color as card_color, 
					d.card_border_color as card_border_color,
					c.cardtitle as card_title, 
					c.cardtitlesub as card_title_sub,
					c.status as statuscard,
					c.modulofiltros as modulofiltros, 
					c.mascararotulo as masc,
					(if(c.tipoobjeto = 'fluxostatus', c.objeto, '')) as idfluxostatus
				FROM dashboard d
				LEFT JOIN dashcard c ON d.iddashcard = c.iddashcard
				WHERE d.idempresa in (?idempresa?) 
				AND d.iddashcard = ?iddashcard?";
	}
}
?>