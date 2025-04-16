<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/tipoprodserv_query.php");

require_once(__DIR__."/../controllers/prodserv_controller.php");

class TipoProdServController extends Controller
{
    // ----- FUNÇÕES -----
	public static function listarProdservTipoProdServPorEmpresa($idempresa)
	{
		$results = SQL::ini(TipoProdServQuery::listarProdservTipoProdServPorEmpresa(),[
            "idempresa" => $idempresa
        ])::exec();
        
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}

	public static function listarContaItemTipoProdservTipoProdServ($idcontaitens)
	{
		$results = SQL::ini(TipoProdServQuery::listarContaItemTipoProdservTipoProdServ(), [
            "idcontaitens" => $idcontaitens
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}
    public static function listarTipoProdservTipoProdServ($iempresa)
	{
		$results = SQL::ini(TipoProdServQuery::listarTipoProdservTipoProdServ(), [
            "idempresa" => $iempresa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}

	public static function buscarContaItem($idcotacao, $idgrupoes)
	{
		$results = SQL::ini(TipoProdServQuery::buscarContaItem(), [
            "tipoobjeto" => 'cotacao',
			"idobjeto" => $idcotacao,
            "idcontaitens" => $idgrupoes
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}

    public static function buscarEmpresa($idempresa){
        $results = SQL::ini(TipoProdServQuery::buscarEmpresa(), [       
            "idempresa" => $idempresa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarExercicioPorIdprev($idtipoprodserv, $idempresa)
    {
     

        $results = SQL::ini(TipoProdServQuery::buscarExercicioPorIdprev(), [
            "idtipoprodserv" => $idtipoprodserv,
            "idempresa" => $idempresa
         
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
			$arrval = [];
			foreach($results->data as $_valor)
			{	
				$arrval[$_valor['exercicio']] = $_valor['exercicio'];	
			}

            return $arrval;
        }	
    }

    

    public static function buscarPrevisaoTipoprodservExercicio($idtipoprodserv,$idempresa){
        $results = SQL::ini(TipoProdServQuery::buscarPrevisaoTipoprodservExercicio(), [       
            "idtipoprodserv" => $idtipoprodserv,
            "idempresa" => $idempresa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }
  
    public static function buscarPrevisaoTipoprodservMes($idtipoprodserv,$idempresa,$exercicio){
        $results = SQL::ini(TipoProdServQuery::buscarPrevisaoTipoprodservMes(), [       
            "idtipoprodserv" => $idtipoprodserv,
            "idempresa" => $idempresa,
            "exercicio"=>$exercicio
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

    public static function buscarHistoricoModuloAlteracao($idobjeto,$tabela,$campo)
	{
		$results = SQL::ini(ModuloHistoricoQuery::buscarHistoricoAlteracao(), [
            "idobjeto" => $idobjeto,
			"tipoobjeto" => $tabela,
			"campo" => " AND h.campo = '$campo'"
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return (count($results->data) > 0) ? $results->data : "";
        }
	}

    public static $_justificativa = array('' => '',
    'DEMANDA DA PRODUÇÃO' => 'Demanda da produção',
    'DEMANDA DO COMERCIAL' => 'Demanda do comercial',
    'OUTROS' => 'Outros');

    // ----- FUNÇÕES -----

    public static function buscarValorTotalPrevisao($idtipoprodserv, $exercicio){
        $results = SQL::ini(TipoProdServQuery::buscarValorTotalPrevisao(), [       
            "idtipoprodserv" => $idtipoprodserv,
            "exercicio" => $exercicio
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        return $results->data[0];
    }
}
?>