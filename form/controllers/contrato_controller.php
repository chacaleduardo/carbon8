<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/contrato_query.php");


class ContratoController extends Controller{

    public static function listarContratoPorPessoa($idpessoa)
	{
		$results = SQL::ini(ContratoQuery::listarContratoPorPessoa(), [          
			"idpessoa" => $idpessoa			
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            return $results->data[0];
            
        }
	}

    public static function buscarValorContatoProdutoFomulado($idpessoa,$idprodserv,$idprodservformula){

        $results = SQL::ini(ContratoQuery::buscarValorContatoProdutoFomulado(), [          
			"idpessoa" => $idpessoa,
            "idprodserv" => $idprodserv,
            "idprodservformula" => $idprodservformula	
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            return $results->data[0];
            
        }

    }

    public static function buscarDescontoContratoPorProduto($idpessoa,$idprodserv){

        $results = SQL::ini(ContratoQuery::buscarDescontoContratoPorProduto(), [          
			"idpessoa" => $idpessoa,
            "idprodserv" => $idprodserv	
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            return $results->data[0];
            
        }

    }


}
?>