<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/controleteste_query.php");
require_once(__DIR__."/../querys/controletitulo_query.php");


class ControleController extends Controller{

    public static function buscarMediasControle($idcontrole){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ControleTesteQuery::buscarMediaControles(),['idcontrole' => $idcontrole])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarControles($idcontrole,$idtipo){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ControleTesteQuery::buscarControlesTeste(),[
            'idcontrole' => $idcontrole,
            "idtipo" => $idtipo
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarTitulosPorControles($idcontrole){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ControleTituloQuery::buscarTitulosPorTeste(),['idcontroleteste' => $idcontrole])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

}?>