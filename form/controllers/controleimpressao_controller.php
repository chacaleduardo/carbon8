<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/notafiscal_query.php");
require_once(__DIR__."/../querys/controleimpressaoitem_query.php");


class ControleImpressaoController extends Controller{

    public static function buscarNotaFiscalPorNumeroRPS($numerorps){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(NotaFiscalQuery::buscarNotaFiscalPorNumeroRPS(),['numerorps' => $numerorps])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data[0];
        }
    }

    public static function buscarItensPorControleDeImpressao($idcontroleimpressao){
        //Verifica se os resultados de uma amostra possem lotetiqueta
        $results = SQL::ini(ControleImpressaoItemQuery::buscarItensPorControleDeImpressao(),['idcontroleimpressao' => $idcontroleimpressao])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
    }
}?>