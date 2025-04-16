<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/tag_query.php");
require_once(__DIR__."/../querys/_modulo_query.php");
require_once(__DIR__."/bioensaio_controller.php");
require_once(__DIR__."/../querys/servicoensaio_query.php");


class RebioensaioController extends Controller{
    public static function buscarTipoDeSalasDeBioensaioPorUnidade($idunidade){
        $results = SQL::ini(TagQuery::buscarTipoDeSalasDeBioensaioPorUnidade(),[
            'idunidadepadrao' => $idunidade,
            'getidempresa' => getidempresa('t.idempresa',$_GET['_modulo'])
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarSalasDeBioensaioPorUnidade($idunidade){
        $results = SQL::ini(TagQuery::buscarSalasDeBioensaioPorUnidade(),[
            'idunidadepadrao' => $idunidade,
            'idempresa' => cb::idempresa()
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarServicosPendentesPorUnidade($idunidade){
        $results = SQL::ini(ServicoEnsaioQuery::buscarServicosPendentesPorUnidade(),[
            'idunidadepadrao' => $idunidade,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarFilhosDeUmaSala($idunidade,$idtagpai){
        $results = SQL::ini(TagQuery::buscarSalasDeUmaSala(),[
            'idunidadepadrao' => $idunidade,
            'idtagpai' => $idtagpai,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarEstudosDeUmaGaiola($idtag){
        $results = SQL::ini(BioensaioQuery::buscarEstudosDeUmaGaiola(),[
            'idtag' => $idtag,
            ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }

    public static function buscarModuloPadrao($modulo,$idunidade){
       return BioensaioController::buscarModuloPorUnidade($idunidade,$modulo);
    }
}