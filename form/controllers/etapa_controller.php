<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/etapa_query.php");

//Controllers

class EtapaController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarEtapaPorTipoObjeto($modulo, $tipoobjeto, $idobjeto)
	{
		$results = SQL::ini(EtapaQuery::buscarEtapaPorTipoObjeto(), [
            "modulo" => $modulo,
			"tipoobjeto" => $tipoobjeto,
			"idobjeto" => $idobjeto
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return parent::toFillSelect($results->data);;
        }
	}
	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE ----
	//----- AUTOCOMPLETE ----

	// ----- Variáveis de apoio -----
	// ----- Variáveis de apoio -----
}
?>