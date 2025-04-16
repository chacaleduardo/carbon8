<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/plpositivo_query.php");
require_once(__DIR__."/../querys/amostra_query.php");


class LanagroController extends Controller{

    public static function inserirResultadoPositivo($clausula){
        $results = SQL::ini(PlpositivoQuery::inserirPlPositivo(), [
            "clausula" => $clausula
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }

        return true;
    }

    public static function montarConsultaParaRelatorio($clausula){
        $results = SQL::ini(AmostraQuery::buscarConsultaLanagro(), [
            "clausula" => $clausula
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        return $results->data;
    }

}?>