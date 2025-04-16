<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/plantel_query.php");
require_once(__DIR__."/../querys/plantelobjeto_query.php");

class PlantelController extends Controller
{
	// ----- FUNÇÕES -----
	public static function listarPlantelPorIdobjetoTipoobjetoProdservAtiva($idobjeto, $tipoobjeto)
	{
		$results = SQL::ini(PlantelQuery::listarPlantelPorIdobjetoTipoobjetoProdservAtiva(), [          
			"idobjeto" => $idobjeto,
			"tipoobjeto" => $tipoobjeto,
			"getidempresa" => getidempresa("u.idempresa", "plantel")
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            return $results->data;            
        }
	}

	public static function buscarPlantelPorIdObjetoETipoObjeto($idobjeto, $tipoobjeto)
	{
		$results = SQL::ini(PlantelQuery::buscarPlantelPorIdObjetoETipoObjeto(), [          
			"idobjeto" => $idobjeto,
			"tipoobjeto" => $tipoobjeto
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            return $results->data;            
        }
	}

	public static function buscarPlantelPorIdUnidadeEIdEmpresa($idUnidade, $idEmpresa)
	{
		$planteis = SQL::ini(PlantelQuery::buscarPlantelPorIdUnidadeEIdEmpresa(), [
			'idunidade' => $idUnidade,
			'idempresa' => $idEmpresa
		])::exec();

		if($planteis->error()){
            parent::error(__CLASS__, __FUNCTION__, $planteis->errorMessage());
            return array();
			
        }
		return $planteis->data;            
	}

	public static function buscarPlanteisDisponiveisParaVinculoEmUnidades($idEmpresa)
	{
		$planteis = SQL::ini(PlantelQuery::buscarPlanteisDisponiveisParaVinculoEmUnidades(), [
			'idempresa' => $idEmpresa
		])::exec();

		if($planteis->error()){
            parent::error(__CLASS__, __FUNCTION__, $planteis->errorMessage());
            return array();
			
        }
		return $planteis->data;    
	}

	public static function buscarPlantelObjeto($tipoobjeto, $idobjeto)
	{
		$planteis = SQL::ini(PlantelObjetoQuery::buscarPlantelObjeto(), [
			'tipoobjeto' => $tipoobjeto,
			'idobjeto' => $idobjeto
		])::exec();

		if($planteis->error()){
            parent::error(__CLASS__, __FUNCTION__, $planteis->errorMessage());
            return [];			
        } else {
			$dados['sql'] = $planteis->sql();
            $dados['data'] = $planteis->data[0];
            $dados['numLinhas'] = $planteis->numRows();
            return $dados;
		}		
	}

	public static function buscarPlanteisProdServ($idEmpresa) {
		$planteis = SQL::ini(PlantelQuery::buscarPlanteisProdServ(), [
			'idempresa' => $idEmpresa
		])::exec();

		if($planteis->error()){
            parent::error(__CLASS__, __FUNCTION__, $planteis->errorMessage());
            return array();
			
        }
		return $planteis->data; 
	}
	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE -----

	//----- AUTOCOMPLETE -----
}
?>