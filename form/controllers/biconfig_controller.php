<?php
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/bi_query.php");

class BigConfigController extends Controller {

    public static function buscarEmpresasVinculas($idbi){
        $results = SQL::ini(BiQuery::buscarEmpresasVinculas(),[
            'idbi' => $idbi
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return parent::toFillSelect($results->data);
        }
    }

    public static function buscarEmpresaBi($idbi)
	{
		$results = SQL::ini(BiQuery::buscarEmpresaBi(), [
            "idbi" => $idbi
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}
}

?>