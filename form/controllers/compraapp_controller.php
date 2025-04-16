<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");

//Controllers
require_once(__DIR__."/../controllers/nf_controller.php");
require_once(__DIR__."/../controllers/nfentrada_controller.php");
require_once(__DIR__."/../controllers/formapagamento_controller.php");
require_once(__DIR__."/../controllers/prodserv_controller.php");
require_once(__DIR__."/../controllers/tag_controller.php");
require_once(__DIR__."/../controllers/rateio_controller.php");

class CompraAppController extends Controller
{	
	public static function buscarTipoProdservPorAppFillSelect()
	{
		return ProdServController::buscarTipoProdservPorAppFillSelect();
	}

	public static function buscarTipoProdservPorApp($id, $condicao)
	{
		return ProdServController::buscarTipoProdservPorApp($id, $condicao);
	}

	public static function buscarFormaPagamentoPorIdPessoa($idpessoa, $_idempresa)
	{
		return FormaPagamentoController::buscarFormaPagamentoPorIdPessoa($idpessoa, $_idempresa);
	}

	public static function buscarIdunidadePorTipoUnidade($idpessoa, $_idempresa)
	{
		return NfEntradaController::buscarIdunidadePorTipoUnidade($idpessoa, $_idempresa);
	}

	public static function inserirNfItemAPP($arrayInsertNfItem)
	{
		return NfController::inserirNfItemAPP($arrayInsertNfItem);
	}

	public static function inserirRateioRateioItemRateioItemDest($idnfitem, $arrInsrateio, $_idempresa)
	{
		return RateioController::inserirRateioRateioItemRateioItemDest($idnfitem, $arrInsrateio, $_idempresa);
	}

	public static function buscarArquivoPorTipoObjetoEIdObjeto($tipoobjeto, $idobjeto)
	{
		$results = SQL::ini(ArquivoQuery::buscarArquivoPorTipoObjetoEIdObjeto(), [
			'tipoobjeto' => $tipoobjeto,
			'idobjeto' => $idobjeto
		])::exec();

		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->numRows();            
        }
	}

	public static function buscarTagPorIdTagClassVeiculos($idtagclass)
	{
		return TagController::buscarTagPorIdTagClass($idtagclass);
	}

	public static function buscarNfItemAcao($idtagclass)
	{
		return NfController::buscarNfItemAcao($idtagclass);
	}	

	public static function inserirNfItemAcao($arrayInsertNfItemAcao)
	{
		return NfController::inserirNfItemAcao($arrayInsertNfItemAcao);
	}

	public static function buscarUltimoValor($idobjeto, $tipoobjeto)
	{
		return NfController::buscarUltimoValor($idobjeto, $tipoobjeto);
	}

	// ----- Variáveis de apoio -----
	public static $_moeda = array('' => '',
								  'BRL' => 'BRL',
								  'USD' => 'USD',
								  'EUR' => 'EUR');
	// ----- Variáveis de apoio -----
}

?>