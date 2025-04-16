<?
class NfRateioQuery
{
	public static function buscarNfRateioPorTipoObjetoRateioEIdObjetoRateio()
    {
		return "SELECT * FROM nfrateio WHERE tipoobjetorateio = '?tipoobjetorateio?' AND idobjetorateio = ?idobjetorateio?";
	}

	public static function atualizarValorNfRateio()
    {
		return "UPDATE nfrateio SET valor = '?valor?' WHERE tipoobjetorateio = '?tipoobjetorateio?' AND idobjetorateio = ?idobjetorateio?";
	}
}
?>