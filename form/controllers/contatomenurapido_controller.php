<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/pessoacontato_query.php");

class ContatoMenuRapidoController extends Controller{

    // ----- FUNÇÕES -----
    public static function buscarNumeroResultadosPorUnidadeCliente ( $idpessoa ){
        $results = SQL::ini(PessoaContatoQuery::buscarNumeroAmostraPorUnidadeCliente(), [
            'idpessoa' => $idpessoa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return (count($results->data) > 0) ? $results->data[0] : "";
        }
    }


}
?>