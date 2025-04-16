<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/imgrupo_query.php");
require_once(__DIR__."/../querys/_modulo_query.php");
require_once(__DIR__."/../querys/eventotipo_query.php");
require_once(__DIR__."/../querys/immsgconfdest_query.php");
require_once(__DIR__."/../querys/immsgconfplataforma_query.php");


// CONTROLLERS
require_once(__DIR__."/_controller.php");

class NotificacaoConfController extends Controller{

    public static function buscarModulosParaVincular(){

        $results = SQL::ini(_ModuloQuery::buscarModuloETabComPK(), [])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        $arrret=array();
        foreach($results->data as $k => $r){
            //monta 2 estruturas json para finalidades (loops) diferentes
            $arrret[$r["modulo"]]["idmodulo"]=$r["idmodulo"];
            $arrret[$r["modulo"]]["modulo"]=$r["modulo"];
            $arrret[$r["modulo"]]["tab"]=$r["tab"];
            $arrret[$r["modulo"]]["rotulomenu"]=$r["rotulomenu"];
        }
        return $arrret;
    }

    public static function buscarModulosDestParaVincular(){
        $results = SQL::ini(_ModuloQuery::buscarModuloETab(), [])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        $arrret=array();
        foreach($results->data as $k => $r){
            //monta 2 estruturas json para finalidades (loops) diferentes
            $arrret[$r["modulo"]]["modulo"]=$r["modulo"];
            $arrret[$r["modulo"]]["idmodulo"]=$r["idmodulo"];
            $arrret[$r["modulo"]]["tab"]=$r["tab"];
            $arrret[$r["modulo"]]["rotulomenu"]=$r["rotulomenu"];
        }
        return $arrret;
    }
}