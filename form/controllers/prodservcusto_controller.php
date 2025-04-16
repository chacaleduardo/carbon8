<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../../form/querys/prodservcusto_query.php");

//Controllers
require_once(__DIR__."/../controllers/_rep_controller.php");
require_once(__DIR__."/../controllers/prodserv_controller.php");

class ProdservCustoController extends Controller{
    // ----- FUNÇÕES -----

    public static function buscarCustosPorIdprodserv($idprodserv){
        $results = SQL::ini(ProdservCustoQuery::buscarCustosPorIdprodserv(), [
            "idprodserv" => $idprodserv,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }

    }

}