<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/pessoa_query.php");
require_once(__DIR__."/../querys/confcontapagar_query.php");
require_once(__DIR__."/../querys/formapagamento_query.php");
require_once(__DIR__."/../querys/rhtipoevento_query.php");
//Controllers
require_once(__DIR__."/../controllers/prodserv_controller.php");
require_once(__DIR__."/../controllers/empresa_controller.php");
require_once(__DIR__."/../controllers/pedido_controller.php");
require_once(__DIR__."/../controllers/contaitem_controller.php");

class ConfContapagarController extends Controller
{
    public static $tipoConfcontapagar =  array('TAXA BOLETO'=>'Taxa de Boleto' 
            ,'COMISSAO'=>'Comissão' 
            , 'CONSIGNADO'=>'Consignado'
            ,'COFINS'=>'COFINS'
            ,'COFINSCRED'=>'COFINS-Crédito'
            , 'CSRF'=>'CSRF' 
            , 'CTE-ENVIO'=>'CTE-ENVIO'   
            , 'CTE-SUPRIMENTOS'=>'CTE-SUPRIMENTOS'                                                
            , 'FERIAS'=>'Férias'   
            , 'FGTS'=>'FGTS'
            , 'FGTSMA'=>'FGTS -  Menor Aprendiz'                         
            , 'GNRE'=>'GNRE' 
            , 'ICMS'=>'ICMS' 
            , 'IRRF'=>'IRRF' 
            , 'IRRF-FOLHA'=>'IRRF - Folha' 
            , 'INSS'=>'INSS'                                             
            , 'ISS'=>'ISS - Retido'  
            , 'ISSRECOLHER'=>'ISS - A Recolher'                                         
            , 'PIS'=>'PIS'  
            , 'PISCRED'=>'PIS-Crédito'    
            , 'IPI'=>'IPI'                                           
            , 'SALARIO'=>'Salário' 
            , '13SALARIO'=>'13º Salário' 
            , 'PJ'=>' PJ' 
            , 'VALE'=>'VALE');

    public static $status = array('ATIVO' => 'Ativo',
            'INATIVO' => 'Inativo');	

    public static $tiponf = array('T'=>'CTE' 
    , 'C'=>'Danfe' 
    , 'S'=>'Serviço'
    , 'M'=>'Guia/Cupom'
    , 'R'=>'RH');

    public static $tpnf = array('E'=>'Entrada' 
    , 'S'=>'Saída');

    public static $statusnf = array('PREVISAO'=>'Previsão' 
    , 'CONCLUIDO'=>'Concluido');

    public static $_tiponf = array( 'T' => 'CTe'
    , 'E'=> 'Concessionária'
    , 'C' => 'Danfe-Compra'
    , 'V' => 'Danfe-Venda'
    , 'M' => 'Guia/Cupom'
    , 'B' => 'Recibo'
    , 'R'=>'RH'
    , 'S'=>'Serviço'
    , 'D' => 'Sócios' 
    , 'O' => 'Outros'
    );

    public static $tipo = array( 'C' => 'Crédito','D' => 'Debito');


    public static function regimetrib(){

        return EmpresaController::$regimetrib;
    }

    public static  $tipoconsumo = array( 'consumo'=>'Consumo', 'faticms'=>'Industria', 'imobilizado'=>'Imobilizado', 'comercio'=>'Comercio', 'outro'=>'Outros');


    public static function listarPessoaPorIdtipopessoaIdempresa($idtipopessoa){

        $pessoasPorSessionIdempresa=share::pessoasPorSessionIdempresa("idpessoa");

        $results = SQL::ini(PessoaQuery::listarPessoaPorIdtipopessoaIdempresa(), [          
            "pessoasPorSessionIdempresa"=> $pessoasPorSessionIdempresa ,
            "idtipopessoa" =>$idtipopessoa                
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            foreach($results->data as $_valor)
            {	
                $lista[$_valor['idpessoa']] = $_valor['nome'];                
            }
            return $lista;
        }
    }

    public static function listarFormaPagamentoPorEmpresa(){

       
        $results = SQL::ini(FormaPagamentoQuery::listarFormaPagamentoPorEmpresa(), [          
                "idempresa" => getidempresa('idempresa', 'formapagamento')   
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            foreach($results->data as $_valor)
            {	
                $lista[$_valor['idformapagamento']] = $_valor['descricao'];                
            }
            return $lista;
        }
    }

    public static function buscarConfcontapagaritemPorIdconfcontapagar($idconfcontapagar){
        $results = SQL::ini(ConfcontapagarQuery::buscarConfcontapagaritemPorIdconfcontapagar(), [          
            "idconfcontapagar"=>$idconfcontapagar             
        ])::exec();

         if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
          return $results->data;
        }
    }

    public static function listarTipoProdservConfpagar($idcontaitem = NULL, $idprodserv = NULL)
	{
		if($idcontaitem) {
			$listarProdserv = PedidoController::buscarContaItemTipoProdservTipoProdServ($idcontaitem);
		} elseif($idprodserv) {
			$prodserv = ProdServController::listarProdservTipoProdServ($idprodserv);

			foreach($prodserv as $_valor)
            {	
                $listarProdserv[$_valor['idtipoprodserv']] = $_valor['tipoprodserv'];                
            }
		} else {
			$listarProdserv = '';
		}

		return $listarProdserv;
	}

    public static function buscarContaItemAtivoShare($tiponf = NULL)
	{	
		$arrContaItem = [];
		$listarContaItem = ContaItemController::buscarContaItemAtivoShare($tiponf);
		foreach($listarContaItem as $contaItem)
		{
			$arrContaItem[$contaItem['idcontaitem']] = $contaItem['contaitem'];
		}
		return $arrContaItem;
	}

    public static function buscarContaItemAtivoShareEmpresaDest($idempresa,$tiponf = NULL)
	{	
		$arrContaItem = [];
		$listarContaItem = ContaItemController::buscarContaItemPorIdempresa($tiponf,$idempresa);
		foreach($listarContaItem as $contaItem)
		{
			$arrContaItem[$contaItem['idcontaitem']] = $contaItem['contaitem'];
		}
		return $arrContaItem;
	}

    public static function listarTipoEventoRH()
	{
        $arrEvento = [];
        $intensEvento = SQL::ini(RhtipoeventoQuery::buscarEventosRh())::exec();

		if($intensEvento->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $intensEvento->errorMessage());
			return "";
		}

        foreach($intensEvento->data as $_valor)
        {	
            $arrEvento[$_valor['idrhtipoevento']] = $_valor['evento'];                
        }

		return $arrEvento;
	}

}

?>