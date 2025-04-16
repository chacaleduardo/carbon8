<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/assinatura_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");


class AssinaturaController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarAssinatura($idobjeto, $tipoobjeto,$tipo)
    {
		$results = SQL::ini(AssinaturaQuery::buscarAssinatura(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" =>$tipoobjeto,
            "tipo" => $tipo
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

	
	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE ----	
	//----- AUTOCOMPLETE ----	

	// ----- Variáveis de apoio -----
	// ----- Variáveis de apoio -----
}
?>