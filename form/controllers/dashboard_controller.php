<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/dashboard_query.php");

class DashboardController extends Controller
{
	// ----- FUNÇÕES -----
	public static function atualizarDashboardGerencimentoConcentrados($haproduzir)
	{
		$results = SQL::ini(DashboardQuery::atualizarDashboardGerencimentoConcentrados(), [
			"haproduzir" => $haproduzir
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
	}
	// ----- FUNÇÕES -----
}
?>