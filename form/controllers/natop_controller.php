<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/natop_query.php");


class NatopController extends Controller{

    public static function listarNatopPorEmpresa()
	{
		$results = SQL::ini(NatopQuery::listarNatopPorEmpresa(), [          
			"idempresa" => cb::idempresa(),			
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            return $results->data;
            
        }
	}

    public static function listarNatopCfop()
	{
        $results = SQL::ini(NatopQuery::listarNatopCfop())::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            return $results->data;            
        }
    }

    public static function buscarNatOpECfopPorOrigemEIdNatOp($origem, $idnatop)
	{
        $results = SQL::ini(NatopQuery::buscarNatOpECfopPorOrigemEIdNatOp(), [
            "origem" => $origem,
            "idnatop" => $idnatop
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            return $results->data[0];            
        }
    }
}
?>