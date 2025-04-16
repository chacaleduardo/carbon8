<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/especiefinalidade_query.php");

class EspecieFinalidadeController extends Controller
{
	public static function listarEspecieFinalidadePlantelOrdenadoPorPlantel($idempresa)
    {
        $results = SQL::ini(EspecieFinalidadeQuery::listarEspecieFinalidadePlantelOrdenadoPorPlantel(), [
            "idempresa" => $idempresa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }
    public static function buscarEspecieAmostra($idamostra)
    {
        $results = SQL::ini(EspecieFinalidadeQuery::buscarEspecieAmostra(), [
            "idamostra" => $idamostra
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }
    
}
?>