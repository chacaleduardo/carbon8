<?
require_once(__DIR__."/../../inc/php/functions.php");

require_once(__DIR__."/_controller.php");
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/fluxo_query.php");
require_once(__DIR__."/../querys/resultado_query.php");



class ResultLoteController extends Controller{

    public static function buscarTipoBotao($modulo,$status){
        $results = SQL::ini(FluxoQuery::buscarTipoBotaoPorModuloeStatus(),[
            "modulo" => $modulo,
            "status" => $status,
            ])::exec();

         if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return [];
         }else{
            return $results->data[0];
         }
    }

    public static function executaProcessaLote($idini,$idfim,$idtipoteste,$status,$idfluxostatus,$tipobotao,$descritivo,$usuario){
        $results = SQL::ini(ResultadoQuery::executaProcessaLote(),[
            "idini" => $idini,
            "idfim" => $idfim,
            "idtipoteste" => $idtipoteste,
            "status" => $status,
            "idfluxostatus" => $idfluxostatus,
            "tipobotao" => $tipobotao,
            "descritivo" => $descritivo,
            "usuario" => $usuario,
            ])::exec();

         if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return [];
         }else{
            return $results->data[0];
         }
    }

    public static function buscarLotesParaConsumo($qtdun,$idtipoteste,$exercicio,$ordem,$idini,$idfim,$idlote,$getidempresa){
        $results = SQL::ini(ResultadoQuery::buscarLotesParaConsumo(),[
            "qtdun" => $qtdun,
            "idtipoteste" => $idtipoteste,
            "exercicio" => $exercicio,
            "ordem" => $ordem,
            "idini" => $idini,
            "idfim" => $idfim,
            "idlote" => $idlote,
            "getidempresa" => $getidempresa
        ])::exec();

         if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return [];
         }else{
            return $results->data;
         }
    }

    public static function buscarResultadosParaVinculoTag($idtipoteste,$exercicio,$idini,$idfim,$getidempresa,$idtag){
        $results = SQL::ini(ResultadoQuery::buscarResultadosParaVinculoTag(),[
            "idtipoteste" => $idtipoteste,
            "exercicio" => $exercicio,
            "idini" => $idini,
            "idfim" => $idfim,
            "getidempresa" => $getidempresa,
            "idtag" => $idtag
        ])::exec();

         if($results->error()){
             parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
             return [];
         }else{
            return $results->data;
         }
    }
}?>