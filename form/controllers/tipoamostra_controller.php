<?
require_once(__DIR__."/../../inc/php/functions.php");

require_once(__DIR__."/_controller.php");
require_once(__DIR__ . "/../querys/unidade_query.php");
require_once(__DIR__ . "/../querys/amostracampos_query.php");
require_once(__DIR__ . "/../querys/telaamostracampos_query.php");


class TipoAmostraController extends Controller{

    public static function buscarUnidadesPorTipoAmostra($tipoobjeto,$idobjeto,$idempresa){
         $results = SQL::ini(UnidadeQuery::buscarUnidadesPorTipoObjeto(),[
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto,
            "idempresa" => $idempresa,
            ])::exec();

         if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return [];
         }else{
            return $results->data;
         }
    }

    public static function buscarAmotraCamposPorTipoAmostra($idempresa,$idsubtipoamostra){
         $results = SQL::ini(AmostraCamposQuery::buscarPorIdEIdempresa(),[
            "idempresa" => $idempresa,
            "idsubtipoamostra" => $idsubtipoamostra,
            ])::exec();

         if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return [];
         }else{
            return $results->data;
         }
    }

    public static function buscarTodosOsCamposDeUmaEmpresa($idempresa){
         $results = SQL::ini(TelaAmostraCamposQuery::buscarTodosOsCamposDeUmaEmpresa(),[
            "idempresa" => $idempresa,
            ])::exec();

         if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return [];
         }else{
            return $results->data;
         }
    }
}
?>