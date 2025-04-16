<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/modulohistorico_query.php");
require_once(__DIR__."/../querys/spedc100_query.php");
require_once(__DIR__."/../querys/spedd100_query.php");

class SpedController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarSpedC100($idnf, $status)
	{
		$results = SQL::ini(Spedc100Query::buscarSpedc100(), [
			"idnf" => $idnf,
			"status" => $status
        ])::exec();
		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
	}

	public static function buscarSpedD100($idnf, $status)
	{
		$results = SQL::ini(SpedD100Query::buscarSpedD100(), [
			"idnf" => $idnf,
			"status" => $status
        ])::exec();
		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
	}

	public static function buscarHistoricoSped($idobjeto, $tipoobjeto, $campo)
	{
		$results = SQL::ini(ModuloHistoricoQuery::buscarHistoricoSped(), [
			"idobjeto" => $idobjeto,
			"tipoobjeto" => $tipoobjeto,
			"campo" => $campo
        ])::exec();
		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
	}
	// ----- FUNÇÕES -----
}

?>