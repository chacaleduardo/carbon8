<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");
require_once(__DIR__."/formalizacao_controller.php");

class AoController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarCalculoVolumeConsumo($inidlote, $incpde = '')
    {
        return FormalizacaoController::buscarConsumoLoteProduto($inidlote,$incpde);
    }
	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE ----	
	//----- AUTOCOMPLETE ----	

	// ----- Variáveis de apoio -----
	// ----- Variáveis de apoio -----
}
?>