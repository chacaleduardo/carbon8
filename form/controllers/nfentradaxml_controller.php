<?
require_once(__DIR__."/_controller.php");


require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/nfentradaxml_query.php");




class NfentradaxmlController extends Controller{

    public static function BuscarObsNfentradaxml($idnfentradaxml)
    {
        $results = SQL::ini(NfentradaxmlQuery::buscarObsporIdnfentradaxml(), [
            'idnfentradaxml' => $idnfentradaxml
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
           return $results->data[0]['obs'];
        }
    }
}

?>

