<?
require_once(__DIR__ . "/_controller.php");

// QUERYS
require_once(__DIR__ . "/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/apontamentoobj_query.php");
require_once(__DIR__ . "/../querys/arquivo_query.php");
require_once(__DIR__ . "/../querys/carimbo_query.php");
require_once(__DIR__ . "/../querys/nfconfpagar_query.php");
require_once(__DIR__ . "/../querys/nfitemxml_query.php");
require_once(__DIR__ . "/../querys/fluxostatus_query.php");
require_once(__DIR__ . "/../querys/endereco_query.php");
require_once(__DIR__ . "/../querys/lote_query.php");
require_once(__DIR__ . "/../querys/_modulo_query.php");
require_once(__DIR__ . "/../querys/tipoprodserv_query.php");

//Controllers
require_once(__DIR__ . "/../controllers/conferenciaitem_controller.php");
require_once(__DIR__ . "/../controllers/contaitem_controller.php");
require_once(__DIR__ . "/../controllers/contapagar_controller.php");
require_once(__DIR__ . "/../controllers/cotacao_controller.php");
require_once(__DIR__ . "/../controllers/inclusaoresultado_controller.php");
require_once(__DIR__ . "/../controllers/finalidadeprodserv_controller.php");
require_once(__DIR__ . "/../controllers/formalizacao_controller.php");
require_once(__DIR__ . "/../controllers/formapagamento_controller.php");
require_once(__DIR__ . "/../controllers/lote_controller.php");
require_once(__DIR__ . "/../controllers/natop_controller.php");
require_once(__DIR__ . "/../controllers/nf_controller.php");
require_once(__DIR__ . "/../controllers/pedido_controller.php");
require_once(__DIR__ . "/../controllers/pessoa_controller.php");
require_once(__DIR__ . "/../controllers/prodserv_controller.php");
require_once(__DIR__ . "/../controllers/rateio_controller.php");
require_once(__DIR__ . "/../controllers/solcom_controller.php");
require_once(__DIR__ . "/../controllers/sped_controller.php");
require_once(__DIR__ . "/../controllers/tag_controller.php");
require_once(__DIR__ . "/../controllers/unidade_controller.php");

class NfEntradaController extends Controller
{
	// ----- FUNÇÕES -----	
	public static function buscarPessoaPorContato($idtipopessoa, $idcontato)
	{
		return PessoaController::buscarPessoaPorContato($idtipopessoa, $idcontato);
	}

	public static function buscarSpedC100($idnf, $status)
	{
		return SpedController::buscarSpedC100($idnf, $status);
	}

	public static function buscarSpedD100($idnf, $status)
	{
		return SpedController::buscarSpedD100($idnf, $status);
	}

	public static function buscarHistoricoSped($idobjeto, $tipoobjeto, $campo)
	{
		return SpedController::buscarHistoricoSped($idobjeto, $tipoobjeto, $campo);
	}

	public static function buscarNfPorRefnfeETipoNf($refnfe, $tiponf)
	{
		return NfController::buscarNfPorRefnfeETipoNf($refnfe, $tiponf);
	}

	public static function buscarXmlNfItem($idnf)
	{
		return NfController::buscarXmlNfItem($idnf);
	}

	public static function buscarSeExisteConversaoMoeda($idnf)
	{
		return NfController::buscarSeExisteConversaoMoeda($idnf);
	}

	public static function buscarSeExisteConversaoMoedaInternacional($idnf)
	{
		$results = SQL::ini(NfItemQuery::buscarSeExisteConversaoMoedaInternacional(), [
			"idnf" => $idnf
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->numRows();
		}
	}

	public static function buscarItensCategoriaESubCategoriaNula($idnf)
	{
		$results = SQL::ini(NfItemQuery::buscarItensCategoriaESubCategoriaNula(), [
			"idnf" => $idnf
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->numRows();
		}
	}

	public static function listarItensCadastrados($idnf, $idobjetosolipor)
	{
		return NfController::listarItensCadastrados($idnf, $idobjetosolipor);
	}

	public static function listarItensSemCadastro($idnf)
	{
		return NfController::listarItensSemCadastro($idnf);
	}

	public static function buscarRateioNfItemProdserv($idnf)
	{
		return NfController::buscarRateioNfItemProdserv($idnf);
	}

	public static function buscarNfitemContaItem($idnf)
	{
		$nfContaItem = NfController::buscarNfitemContaItem($idnf);
		return $nfContaItem['qtdLinhas'];
	}

	public static function buscarNfitemContaItemRateio($idnf, $idprodserv = NULL)
	{
		return NfController::buscarNfitemContaItemRateio($idnf, $idprodserv = NULL);
	}

	public static function buscarNfContaItemRateio($idnf)
	{
		return NfController::buscarNfContaItemRateio($idnf);
	}

	public static function buscarNfItemSolcom($idnf)
	{
		return NfController::buscarNfItemSolcom($idnf);
	}

	public static function buscarRateio($idprodserv, $idunidade, $consumodiaslote)
	{
		return LoteController::buscarRateio($idprodserv, $idunidade, $consumodiaslote);
	}

	public static function buscarTituloFrete()
	{
		return CotacaoController::$tituloFrete;
	}

	public static function buscarTipoFrete()
	{
		return CotacaoController::$tipoFrete;
	}

	public static function listarUnidadeVolume()
	{
		return CotacaoController::listarUnidadeVolume();
	}

	public static function buscarDadosNfPorIdNfItem($idnfitem)
	{
		return CotacaoController::buscarDadosNfPorIdNfItem($idnfitem);
	}

	public static function buscarNfitemPorIdobjetoTipoobjetoEIdNfOrigem($idobjetoitem, $tipoobjetoitem, $idnforigem)
	{
		return NFController::buscarNfitemPorIdobjetoTipoobjetoEIdNfOrigem($idobjetoitem, $tipoobjetoitem, $idnforigem);
	}

	public static function buscarNfitemPorIdobjetoTipoobjetoEIdNfOrigemNew($idnf, $tipoobjetoitem)
	{
		return NFController::buscarNfitemPorIdobjetoTipoobjetoEIdNfOrigemNew($idnf, $tipoobjetoitem);
	}

	public static function buscarContaItemAtivoShare($tiponf = NULL)
	{
		$arrContaItem = [];
		$listarContaItem = ContaItemController::buscarContaItemAtivoShare($tiponf);
		foreach ($listarContaItem as $contaItem) {
			$arrContaItem[$contaItem['idcontaitem']] = $contaItem['contaitem'];
		}
		return $arrContaItem;
	}

	public static function buscarContaItemProdservContaItem($idprodservs, $status, $tipo, $idNf = false)
	{
		$arrContaItem = [];
		$listarContaItem = ContaItemController::buscarContaItemProdservContaItem($idprodservs, $status, $tipo, $idNf);
		foreach ($listarContaItem as $contaItem) {
			$arrContaItem[$contaItem['idcontaitem']] = $contaItem['contaitem'];
		}
		return $arrContaItem;
	}

	public static function buscarContaItemProdservContaItemDados($idprodserv)
	{
		$results = SQL::ini(ContaItemQuery::buscarContaItemProdservContaItem(), [
			"idprodservs" => $idprodserv,			
			"somarRelatorio" => "",
			"union" => ""
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data[0];
		}
	}

	public static function atualizarQtdLote($idnfitem, $qtd)
	{
		$qtdLote = SQL::ini(LoteQuery::buscarQtdLote(), [
			'idnfitem' => $idnfitem
		])::exec();
		
		$qtd = str_replace(['.', ','], ['', '.'], $qtd);
		if($qtdLote->numRows() == 1){ 
			$resultsLote = SQL::ini(LoteQuery::atualizarQtdLote(), [
				"qtdpedida" => $qtd,
				"qtdprod" => $qtd,
				"idnfitem" => $idnfitem
			])::exec();

			if ($resultsLote->error()) {
				parent::error(__CLASS__, __FUNCTION__, $resultsLote->errorMessage());
				return false;
			} 

			$resultsLoteFracao = SQL::ini(LoteFracaoQuery::atualizarQtdLoteFracao(), [
				"qtd" => $qtd,
				"qtdini" => $qtd,
				"idnfitem" => $idnfitem
			])::exec();

			if ($resultsLoteFracao->error()) {
				parent::error(__CLASS__, __FUNCTION__, $resultsLoteFracao->errorMessage());
				return false;
			} 
		}
	}

	public static function buscarLotePorIdNfitem($arrayInsertNfItem)
	{
		return LoteController::buscarLotePorIdNfitem($arrayInsertNfItem);
	}



	public static function buscarContaItemProdservContaItemPorNf($idNf, $tiponf = NULL)
	{
		$listarContaItem = ContaItemController::buscarContaItemProdservContaItemPorNf($idNf, $tiponf);
		
		return $listarContaItem;
	}

	public static function listarTipoProdservCompra($status, $idcontaitem, $idprodserv = NULL)
	{
		if ($status == 'CONCLUIDO') {
			if ($idcontaitem) {
				$listarProdserv = PedidoController::buscarContaItemTipoProdservTipoProdServ($idcontaitem);
			} else {
				$listarProdserv = PedidoController::buscarProdservTipoProdServ();
			}
		} elseif ($idcontaitem) {
			$listarProdserv = PedidoController::buscarContaItemTipoProdservTipoProdServ($idcontaitem);
		} elseif ($idprodserv) {
			$prodserv = ProdServController::listarProdservTipoProdServ($idprodserv);

			foreach ($prodserv as $_valor) {
				$listarProdserv[$_valor['idtipoprodserv']] = $_valor['tipoprodserv'];
			}
		} else {
			$listarProdserv = '';
		}

		return $listarProdserv;
	}

	public static function listarTipoProdservCompraNf($status, $idcontaitem, $idprodserv = NULL)
	{
		if ($status == 'CONCLUIDO') {
			if ($idcontaitem) {
				$listarProdserv = PedidoController::buscarContaItemTipoProdservTipoProdServ($idcontaitem);
			} else {
				$listarProdserv = PedidoController::buscarProdservTipoProdServ();
			}
		} elseif ($idcontaitem) {
			$listarProdserv = PedidoController::buscarContaItemTipoProdservTipoProdServ($idcontaitem);
		} elseif ($idprodserv) {
			$prodserv = ProdServController::listarProdservTipoProdServ($idprodserv);

			foreach ($prodserv as $_valor) {
				$listarProdserv[$_valor['idtipoprodserv']] = $_valor['tipoprodserv'];
			}
		} else {
			$listarProdserv = '';
		}

		return $listarProdserv;
	}

	public static function buscarUnidadeObjetoLoteModuloPorIdnfItem($idnfitem, $idlote)
	{
		return UnidadeController::buscarUnidadeObjetoLoteModuloPorIdnfItem($idnfitem, $idlote);
	}

	public static function buscarUnidadeObjetoLoteModuloPorIdnf($idnf, $idlote)
	{
		return UnidadeController::buscarUnidadeObjetoLoteModuloPorIdnf($idnf, $idlote);
	}

	public static function buscarTagEmpresa($idobjetoorigem, $tipoobjetoorigem)
	{
		return TagController::buscarTagEmpresa($idobjetoorigem, $tipoobjetoorigem);
	}

	public static function buscarTagEmpresaPorIdNf($_idnf)
	{
		return TagController::buscarTagEmpresaPorIdNf($_idnf);
	}

	public static function buscarLoteNfItem($idnfitem)
	{
		return LoteController::buscarLoteNfItem($idnfitem);
	}

	public static function buscarConsumoLoteconsPorIdLoteEIdLoteFracao($idobjeto, $tipoobjeto, $idlotefracao, $idlote)
	{
		return LoteController::buscarConsumoLoteconsPorIdLoteEIdLoteFracao($idobjeto, $tipoobjeto, $idlotefracao, $idlote);
	}

	public static function buscarFormalizacaoPorIdLote($idlote)
	{
		return FormalizacaoController::buscarFormalizacaoPorIdLote($idlote);
	}

	public static function buscarLotePorIdLote($idlote)
	{
		return LoteController::buscarLotePorIdLote($idlote);
	}

	public static function buscarLoteFracaoPorIdloteEIdUnidade($idlote, $idunidade)
	{
		return LoteController::buscarLoteFracaoPorIdloteEIdUnidade($idlote, $idunidade);
	}

	public static function buscarUnidadeObjetoPorTipoObjetoEIdUnidade($idunidade, $tipoobjeto, $modulotipo)
	{
		return UnidadeController::buscarUnidadeObjetoPorTipoObjetoEIdUnidade($idunidade, $tipoobjeto, $modulotipo);
	}

	public static function buscarCtePorIdNfe($idnfe, $idobjetosolipor)
	{
		return NfController::buscarCtePorIdNfe($idnfe, $idobjetosolipor);
	}

	public static function buscarCte($idobjetosolipor)
	{
		return NfController::buscarCte($idobjetosolipor);
	}

	public static function buscarNfItemPorNfe($idnf, $nfe)
	{
		return NfController::buscarNfItemPorNfe($idnf, $nfe);
	}

	public static function buscarNfItemContaPagar($idobjetoitem, $tipoobjetoitem)
	{
		return NfController::buscarNfItemContaPagar($idobjetoitem, $tipoobjetoitem);
	}

	public static function buscarConfiguracoesFormaPagamento($idformapagamento)
	{
		return FormaPagamentoController::buscarConfiguracoesFormaPagamento($idformapagamento);
	}

	public static function buscarIdNfConfPagar($idnf)
	{
		return CotacaoController::buscarIdNfConfPagar($idnf);
	}

	public static function buscarNfEFluxoStatusPorTipoObjetoSoliPor($idobjetosolipor, $tipoobjetosolipor)
	{
		return NfController::buscarNfEFluxoStatusPorTipoObjetoSoliPor($idobjetosolipor, $tipoobjetosolipor);
	}

	public static function buscarNfporServDesc($prodservdescr)
	{
		return NfController::buscarNfporServDesc($prodservdescr);
	}

	public static function buscarNfPessoaPorIdNf($idnf)
	{
		return NfController::buscarNfPessoaPorIdNf($idnf);
	}

	public static function buscarNfConferenciaItem($idnf)
	{
		return ConferenciaItemController::buscarNfConferenciaItem($idnf);
	}

	public static function buscarConferenciaItem($tiponf)
	{
		return ConferenciaItemController::buscarConferenciaItem($tiponf);
	}

	public static function inserirNfConferenciaItem($idempresa, $idnf, $tiponf)
	{
		return ConferenciaItemController::inserirNfConferenciaItem($idempresa, $idnf, $tiponf);
	}

	public static function buscarHistoricoAlteracao($idobjeto, $campo)
	{
		$results = SQL::ini(ModuloHistoricoQuery::buscarHistoricoAlteracao(), [
			"idobjeto" => $idobjeto,
			"tipoobjeto" => "nf",
			"campo" => " AND h.campo = '$campo'"
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return (count($results->data) > 0) ? $results->data : "";
		}
	}

	public static function buscarNfPendencia($idnf)
	{
		return NFController::buscarNfPendencia($idnf);
	}

	public static function buscarPessoa($idpessoa)
	{
		return PessoaController::buscarPessoa($idpessoa);
	}

	public static function buscarTransportadorPorIdpessoa($idpessoa, $idempresa)
	{
		return PessoaController::buscarTransportadorPorIdpessoa($idpessoa, $idempresa);
	}

	public static function buscarNfPessoaPorIdNfe($idnfe)
	{
		return NfController::buscarNfPessoaPorIdNfe($idnfe);
	}

	public static function buscarApontamentoObj($idobjeto, $tipoobjeto)
	{
		$results = SQL::ini(ApontamentoObjQuery::buscarApontamentoObj(), [
			"idobjeto" => $idobjeto,
			"tipoobjeto" => $tipoobjeto
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->data;
		}
	}

	public static function buscarContaPagarFormaPagamentoPorIdObejtoOrigem($idobjetoorigem, $tipoobjetoorigem, $tipoobjeto, $idobjeto)
	{
		return ContaPagarController::buscarContaPagarFormaPagamentoPorIdObejtoOrigem($idobjetoorigem, $tipoobjetoorigem, $tipoobjeto, $idobjeto);
	}

	public static function buscarFaturaPorId($idcontapagar)
	{
		return ContapagarController::buscarFaturaPorId($idcontapagar);
	}

	public static function buscarContaPagarItem($idcontapagaritem)
	{
		return ContapagarController::buscarContaPagarItem($idcontapagaritem);
	}

	public static function buscarInfFormapagamentoPorIdObjetoOrigemEIdObjeto($idobjetoorigem, $tipoobjetoorigem, $idobjeto, $tipoobjeto)
	{
		return FormaPagamentoController::buscarInfFormapagamentoPorIdObjetoOrigemEIdObjeto($idobjetoorigem, $tipoobjetoorigem, $idobjeto, $tipoobjeto);
	}

	public static function buscarAssinaturaPessoa($status, $tipoobjeto, $idobjeto)
	{
		$results = SQL::ini(CarimboQuery::buscarAssinaturaPessoa(), [
			"status" => $status,
			"tipoobjeto" => $tipoobjeto,
			"idobjeto" => $idobjeto
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			$dados['dados'] = $results->data;
			$dados['qtdLinhas'] = $results->numRows();
			return $dados;
		}
	}

	public static function listarItensNfParaDuplicar($idnf)
	{
		return NFController::listarItensNfParaDuplicar($idnf);
	}

	public static function buscarIdunidadePorTipoUnidade($idtipounidade, $idempresa)
	{
		return UnidadeController::buscarIdunidadePorTipoUnidade($idtipounidade, $idempresa);
	}
	public static function buscarIdunidadePorTipoUnidadeDescricao($idtipounidade, $idempresa, $unidade)
	{
		return UnidadeController::buscarIdunidadePorTipoUnidadeDescricao($idtipounidade, $idempresa, $unidade);
	}

	public static function buscarDadosNfItemPorIdNfItem($idnfitem)
	{
		return NFController::buscarDadosNfItemPorIdNfItem($idnfitem);
	}

	public static function buscarNfPorIdNfENfe($idnfitem)
	{
		return NFController::buscarNfPorIdNfENfe($idnfitem);
	}

	public static function atualizarNfTotalSubtotal($total, $subtotal, $idnf)
	{
		return NFController::atualizarNfTotalSubtotal($total, $subtotal, $idnf);
	}

	public static function buscarInfFormapagamentoPorId($idformapagamento)
	{
		return FormaPagamentoController::buscarInfFormapagamentoPorId($idformapagamento);
	}

	public static function buscarQuantidadeParcelasPorStatusTipoObjeto($tipoobjeto, $idobjeto, $status)
	{
		return ContaPagarController::buscarQuantidadeParcelasPorStatusTipoObjeto($tipoobjeto, $idobjeto, $status);
	}

	public static function buscarQuantidadeBoletosRemessaItem($tipoobjeto, $idobjeto)
	{
		return ContaPagarController::buscarQuantidadeBoletosRemessaItem($tipoobjeto, $idobjeto);
	}

	public static function apagarParcelasExistentes($tipoobjeto, $idobjeto)
	{
		ContaPagarController::apagarParcelasExistentes($tipoobjeto, $idobjeto);
	}

	public static function buscarPrevisaoEntregaPorIdNf($idnf)
	{
		return NfController::buscarPrevisaoEntregaPorIdNf($idnf);
	}

	public static function buscarFornecedorPorNnfe($idpessoa, $nnfe, $idnf)
	{
		return NfController::buscarFornecedorPorNnfe($idpessoa, $nnfe, $idnf);
	}

	public static function buscarRateioNfItem($idnf, $tipoobjeto)
	{
		return NfController::buscarRateioNfItem($idnf, $tipoobjeto);
	}

	public static function buscarNfItemIdPessoaNuloNfe($idnf, $nfe)
	{
		return NfController::buscarNfItemIdPessoaNuloNfe($idnf, $nfe);
	}

	public static function buscarValorNfitemXmlNfItem($idnf)
	{
		return NfController::buscarValorNfitemXmlNfItem($idnf);
	}

	public static function atualizarNcmProdServ($idnf)
	{
		return ProdservController::atualizarNcmProdServ($idnf);
	}

	public static function buscarNfItemXml($idnfitemxml)
	{
		return NfController::buscarNfItemXml($idnfitemxml);
	}

	public static function buscarNFProdservForn($idprodserv, $idnf)
	{
		return NfController::buscarNFProdservForn($idprodserv, $idnf);
	}

	public static function buscarNfPorId($idnf)
	{
		return NfController::buscarNfPorId($idnf);
	}

	public static function buscarNfitemPorIdnf($idnf)
	{
		return NfController::buscarNfitemPorIdnf($idnf);
	}

	public static function buscarItensPorIdNf($idnf, $dtemissao)
	{
		return NfController::buscarItensPorIdNf($idnf, $dtemissao);
	}

	public static function buscarEnderecoPessoaNf($idnf)
	{
		return NfController::buscarEnderecoPessoaNf($idnf);
	}

	public static function buscarNatOpECfopPorOrigemEIdNatOp($origem, $idnatop)
	{
		return NatopController::buscarNatOpECfopPorOrigemEIdNatOp($origem, $idnatop);
	}

	public static function buscarNfItemXmlNfItem($idnfitemxml, $idnf)
	{
		return NfController::buscarNfItemXmlNfItem($idnfitemxml, $idnf);
	}

	public static function atualizarProporcaoNfConfPagar($idnf)
	{
		return CotacaoController::atualizarProporcaoNfConfPagar($idnf);
	}

	public static function apagarNfConfPagar($idnf)
	{
		return CotacaoController::apagarNfConfPagar($idnf);
	}

	public static function buscarNfconfpagar($idnf)
	{
		$results = SQL::ini(NfConfPagar::buscarNfconfpagar(), [
			"idnf" => $idnf
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			$dados['dados'] = $results->data;
			$dados['qtdLinhas'] = $results->numRows();
			return $dados;
		}
	}

	public static function buscarNfconfpagarOrdenadoPorOrdemDescrescente($idnf)
	{
		$results = SQL::ini(NfConfPagar::buscarNfconfpagarOrdenadoPorOrdemDescrescente(), [
			"idnf" => $idnf
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			$dados['dados'] = $results->data;
			$dados['qtdLinhas'] = $results->numRows();
			return $dados;
		}
	}

	public static function atualizarDatarecebNfConfPagar($idnf)
	{
		$results = SQL::ini(NfConfPagar::atualizarDatarecebNfConfPagar(), [
			"idnf" => $idnf
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}

	public static function buscarNfRateioPorTipoObjetoRateioEIdObjetoRateio($tipoobjetorateio, $idobjetorateio)
	{
		return RateioController::buscarNfRateioPorTipoObjetoRateioEIdObjetoRateio($tipoobjetorateio, $idobjetorateio);
	}

	public static function atualizarValorNfRateio($tipoobjetorateio, $idobjetorateio, $valor)
	{
		return RateioController::atualizarValorNfRateio($tipoobjetorateio, $idobjetorateio, $valor);
	}

	public static function atualizarIdNfItemXmlNfItem($idprodserv, $idnfitemxml)
	{
		return NfController::atualizarIdNfItemXmlNfItem($idprodserv, $idnfitemxml);
	}

	public static function atualizarTransportadoraNf($idtransportadora, $idnf)
	{
		return NfController::atualizarTransportadoraNf($idtransportadora, $idnf);
	}

	public static function buscarLoteNfItemPorIndNf($frete, $idnf, $idprodserv = null)
	{
		return NfController::buscarLoteNfItemPorIndNf($frete, $idnf, $idprodserv = null);
	}

	public static function buscarProdservfornPorId($idprodservforn)
	{
		return ProdservController::buscarProdservfornPorId($idprodservforn);
	}

	public static function buscarProdservfornPorIdprodservIdnf($idprodserv, $idnf)
	{
		return ProdservController::buscarProdservfornPorIdprodservIdnf($idprodserv, $idnf);
	}

	public static function buscarLotePorIdprodservIdunidade($idunidade, $idprodserv)
	{
		return LoteController::buscarLotePorIdprodservIdunidade($idunidade, $idprodserv);
	}

	public static function vw8FuncionarioUnidadePorIdPessoaIdEmpresa($idpessoa, $idempresa)
	{
		return UnidadeController::vw8FuncionarioUnidadePorIdPessoaIdEmpresa($idpessoa, $idempresa);
	}

	public static function atualizarNfXmlRetEnvioNfe($xmlret, $envionfe, $idnf)
	{
		return NfController::atualizarNfXmlRetEnvioNfe($xmlret, $envionfe, $idnf);
	}

	public static function apagarNfItemXmlPorIdNf($idnf)
	{
		return NfController::apagarNfItemXmlPorIdNf($idnf);
	}
	public static function atualizarNfXmlVinculo($idnf)
	{
		return NfController::atualizarNfXmlVinculo($idnf);
	}

	public static function buscarNfPorTipoNfEIdNf($tiponf, $idnf)
	{
		return NfController::buscarNfPorTipoNfEIdNf($tiponf, $idnf);
	}

	public static function atualizarNfIdnfeDtemissaoPorIdnf($idnfe, $dtemissao, $idnf)
	{
		return NfController::atualizarNfIdnfeDtemissaoPorIdnf($idnfe, $dtemissao, $idnf);
	}

	public static function buscarNfitemPorIdnfitem($idnfitem)
	{
		return NfController::buscarNfitemPorIdnfitem($idnfitem);
	}

	public static function buscarFluxoComprasPrompt($modulo, $ocultar)
	{
		if ($modulo == 'comprasrh' ||  $modulo == 'comprasrhrestrito') {
			$modulo = 'comprasrh';
			$statusWhere = "";
		} elseif ($modulo == 'comprasapp') {
			$modulo = 'nfentrada';
			$statusWhere = " AND s.statustipo IN('INICIO', 'EMANALISE', 'CONCLUIDO', 'CANCELADO')";
		} else {
			$modulo = 'nfentrada';
			$statusWhere = "";
		}

		if (!empty($ocultar)) {
			$ocultarWhere = " AND fs.ocultar IN ('" . implode("','", explode(',', $ocultar)) . "')";
		} else {
			$ocultarWhere = "";
		}

		$listarFluxo = SQL::ini(FluxoStatusQuery::buscarFluxoComprasPrompt(), [
			"modulo" => $modulo,
			"ocultarWhere" => $ocultarWhere,
			"statusWhere" => $statusWhere
		])::exec();

		if ($listarFluxo->error()) {
			parent::error(__CLASS__, __FUNCTION__, $listarFluxo->errorMessage());
			return [];
		} else {
			$virg = "";
			$json = "";
			foreach ($listarFluxo->data as $fluxo) {
				$json .= $virg . '{"' . $fluxo['idfluxostatus'] . '":"' . $fluxo['rotulo'] . '"}';
				$virg = ",";
			}

			return $json;
		}
	}

	public static function buscarFracaoPorLoteEUnidade($idlote, $idunidade)
	{
		return LoteController::buscarFracaoPorLoteEUnidade($idlote, $idunidade);
	}

	public static function buscarFracaoPorIdLoteFracao($idlotefracao)
	{
		return LoteController::buscarFracaoPorIdLoteFracao($idlotefracao);
	}

	public static function buscarConsumoEUnidade($idlote, $whereCondicao)
	{
		return LoteController::buscarConsumoEUnidade($idlote, $whereCondicao);
	}

	public static function buscarUnidadeObjetoPorModuloTipoEIdUnidadeEReady($modulotipo, $tipoobjeto, $idunidade)
	{
		return UnidadeController::buscarUnidadeObjetoPorModuloTipoEIdUnidadeEReady($modulotipo, $tipoobjeto, $idunidade);
	}

	public static function buscarSolMatItemPorIdSomatItem($idsolmatitem)
	{
		return SolmatController::buscarSolMatItemPorIdSomatItem($idsolmatitem);
	}

	public static function verficarSePodeExcluirConsumo($idtransacao)
	{
		return LoteController::verficarSePodeExcluirConsumo($idtransacao);
	}

	public static function buscarGrupoLoteConsPorIdTransacao($idtransacao)
	{
		return LoteController::buscarGrupoLoteConsPorIdTransacao($idtransacao);
	}

	public static function buscarConsumoPorLoteFracaoETipoObjeto($idlote, $whereCondicao)
	{
		return LoteController::buscarConsumoPorLoteFracaoETipoObjeto($idlote, $whereCondicao);
	}

	public static function buscarNnfePorIdNfItem($idnfitem)
	{
		return NfController::buscarNnfePorIdNfItem($idnfitem);
	}

	public static function buscarAmostraPorIdResultado($idresultado)
	{
		return InclusaoResultadoController::buscarAmostraPorIdResultado($idresultado);
	}

	public static function buscarReservaLotePorNfEUnidade($tipoobjeto, $idlote)
	{
		return LoteController::buscarReservaLotePorNfEUnidade($tipoobjeto, $idlote);
	}

	public static function buscarSomasLoteFracao($idlote, $idunidade)
	{
		return LoteController::buscarSomasLoteFracao($idlote, $idunidade);
	}

	public static function buscarArquivoPorTipoObjetoEIdObjeto($tipoobjeto, $idobjeto)
	{
		$results = SQL::ini(ArquivoQuery::buscarArquivoPorTipoObjetoEIdObjeto(), [
			'tipoobjeto' => $tipoobjeto,
			'idobjeto' => $idobjeto
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->numRows();
		}
	}

	public static function inserirContaPagarComIdContaItem($arrayInsertContaPagar)
	{
		return ContaPagarController::inserirContaPagarComIdContaItemArray($arrayInsertContaPagar);
	}

	public static function inserirValoresContaPagarItem($arrayInsertContaPagar)
	{
		return ContaPagarController::inserirValoresContaPagarItemArray($arrayInsertContaPagar);
	}

	public static function atualizarContaPagarItemSemRateio($idempresa)
	{
		$results = SQL::ini(ContaPagarItemQuery::buscarContaPagarItemAbertas(), [
			'idempresa' => $idempresa
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			foreach ($results->data as $linhas) {
				$tipoespecifico = $linhas['tipoespecifico'];

				if ($linhas['agrupnota'] == 'Y') {
					$qtd1 = 0;
				} elseif ($linhas['agruppessoa'] == 'Y') {
					//alterado a buscar para pegar a primeira em aberto apartir da data de vencimento datapagto 11-09-2020 hermesp
					$resultsContaPagar = SQL::ini(ContaPagarQuery::buscarIdContaPagarPorIdFormapagamentoIdPessoaDataReceb(), [
						"AndIdpessoa" => "AND idpessoa = " . $linhas['idpessoa'],
						"idformapagamento" => $linhas['idformapagamento'],
						"idagencia" => $linhas['idagencia'],
						"idempresa" => $idempresa,
						"tipoespecifico" => $tipoespecifico,
						"datareceb" => $linhas['datavencimento']
					])::exec();
				} else {
					$resultsContaPagar = SQL::ini(ContaPagarQuery::buscarIdContaPagarPorIdFormapagamentoIdPessoaDataReceb(), [
						"AndIdpessoa" => '',
						"idformapagamento" => $linhas['idformapagamento'],
						"idagencia" => $linhas['idagencia'],
						"idempresa" => $idempresa,
						"tipoespecifico" => $tipoespecifico,
						"datareceb" => $linhas['datapagto']
					])::exec();
				}

				if ($resultsContaPagar->numRows() > 0) {
					$linhaContaPagar = $resultsContaPagar->data[0];
					ContaPagarController::atualizarIdContaPagarPorIdContaPagarItem($linhaContaPagar['idcontapagar'], $linhas['idcontapagaritem']);

					if (($linhas["formapagamento"] != 'C.CREDITO') && ($linhas["formapagamento"] != 'BOLETO' || $linhas['agruppessoa'] != 'Y')) { //Não atualiza valores de cartao de credito                  
						$rowContaPagar = ContaPagarController::buscarValorTotalContaPagarItem($linhaContaPagar['idcontapagar']);
						ContaPagarController::atualizarValorContaPagar($rowContaPagar[0], $linhaContaPagar['idcontapagar']);
					}
				} else {
					/* 
                    * Fatura cartão: ao lançar um item de conta, 
                    * verificar se ha  uma fatura "pendente e/ou quitado"
                    * no mes do lançamento. Caso haja, jogar para o proximo mes.                     * 
                    */
					if ($linhas['agrupnota'] == 'Y') {
						$datavencimento = $linhas['datapagto'];
					} else {
						$datavencimento = $linhas['datavencimento'];
					}

					$inscontapagar = new Insert();
					$inscontapagar->setTable("contapagar");
					$inscontapagar->idagencia = $linhas['idagencia'];
					$inscontapagar->criadopor = $linhas['criadopor'];
					$inscontapagar->alteradopor = $linhas['criadopor'];
					$inscontapagar->idempresa = $idempresa;

					if ($linhas['agruppessoa'] == 'Y') {
						$inscontapagar->idpessoa = $linhas['idpessoa'];
						$inscontapagar->status = 'ABERTO';

						//LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
						$idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'ABERTO');
						$inscontapagar->idfluxostatus = $idfluxostatus;

						$inscontapagar->parcela = 1;
						$inscontapagar->parcelas = 1;
						if (!empty($linhas['idcontaitem'])) {
							$inscontapagar->idcontaitem = $linhas['idcontaitem'];
						}
					} elseif ($linhas['agrupnota'] == 'Y') {
						$inscontapagar->idpessoa = $linhas['idpessoa'];
						$inscontapagar->tipoobjeto = $linhas['tipoobjetoorigem'];
						$inscontapagar->idobjeto = $linhas['idobjetoorigem'];
						$inscontapagar->parcela = $linhas['parcela'];
						$inscontapagar->parcelas = $linhas['parcelas'];
						$inscontapagar->valor = $linhas['valor'];
						$inscontapagar->status = $linhas['status'];

						//LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
						$idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', $linhas['status']);
						$inscontapagar->idfluxostatus = $idfluxostatus;

						if (!empty($linhas['idcontaitem'])) {
							$inscontapagar->idcontaitem = $linhas['idcontaitem'];
						}
					} else {
						$inscontapagar->idcontaitem = 46;
						$inscontapagar->status = 'ABERTO';

						//LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
						$idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'ABERTO');
						$inscontapagar->idfluxostatus = $idfluxostatus;

						$inscontapagar->parcela = 1;
						$inscontapagar->parcelas = 1;
					}

					if ($tipoespecifico == 'IMPOSTO') {
						$inscontapagar->idpessoa = $linhas['idpessoa'];
					}
					$inscontapagar->idformapagamento = $linhas['idformapagamento'];
					if (!empty($linhas['previsao']) && $linhas['agrupnota'] != 'Y') {
						$inscontapagar->valor = $linhas['previsao'];
					}

					$inscontapagar->tipo = $linhas['tipo'];
					$inscontapagar->visivel = $linhas['visivel'];
					$inscontapagar->obs = $linhas['obs'];
					$inscontapagar->tipoespecifico = $tipoespecifico;
					$inscontapagar->datapagto = $datavencimento;
					$inscontapagar->datareceb = $datavencimento;

					$idcontapagar = $inscontapagar->save();

					// GVT - 23/07/2021 - @471938 lançamentos com diferença de 28 dias gera assinatura p/ o Fábio
					// Não há a necessidade de verificar se já existe assinatura pendente, pois aqui sempre são criadas novas contas a pagar
					$d1 = strtotime(date("Y-m-d"));
					$d2 = strtotime($datavencimento);
					$diff = ($d2 - $d1) / 60 / 60 / 24;
					if ($diff < 28 && $linhas['agrupnota'] == 'Y' && $linhas['tipo'] == 'D') {
						$arrayCarimbo = [
							"idempresa" => $idempresa,
							"idpessoa" => 798,
							"idobjeto" => $idcontapagar,
							"tipoobjeto" => 'contapagar',
							"status" => 'PENDENTE',
							"usuario" => $_SESSION["SESSAO"]["USUARIO"]
						];
						self::InserirAssinaturaResultado($arrayCarimbo);
					}

					ContaPagarController::atualizarIdContaPagarPorIdContaPagarItem($idcontapagar, $linhas['idcontapagaritem'], $idempresa);

					if (($linhas["formapagamento"] != 'C.CREDITO') && (($linhas["formapagamento"] != 'BOLETO' || $linhas['agruppessoa'] != 'Y') || ($linhas['agrupnota'] == 'Y'))) { //Não atualiza valores de cartao de credito   
						$rowContaPagar = ContaPagarController::buscarValorTotalContaPagarItem($idcontapagar);
						ContaPagarController::atualizarValorContaPagar($rowContaPagar[0], $idcontapagar);
					} // if($row["formapagamento"]!='C.CREDITO'){

					//LTM - 31-03-2021: Retorna o Idfluxo Hist
					if (!empty($idfluxostatus)) {
						FluxoController::inserirFluxoStatusHist('contapagar', $idcontapagar, $idfluxostatus, 'PENDENTE');
					}
				}
			}
		}
	}

	public static function InserirAssinaturaResultado($arrayCarimbo)
	{
		$results = SQL::ini(CarimboQuery::InserirAssinaturaResultado(), $arrayCarimbo)::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return false;
		} else {
			return true;
		}
	}

	public static function buscarIdParcelaNfConfPagarPorIdNf($idnf, $parcela)
	{
		$results = SQL::ini(NfConfPagar::buscarIdParcelaNfConfPagarPorIdNf(), [
			"idnf" => $idnf,
			"parcela" => $parcela
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->data[0];
		}
	}

	public static function inserirIdNfContaPagarDataRecebProporcao($arrayNfConfPagar)
	{
		$results = SQL::ini(NfConfPagar::inserirIdNfContaPagarDataRecebProporcao(), $arrayNfConfPagar)::exec();
		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->data;
		}
	}

	public static function buscarItensSolcom($_idprodservs, $_idcotacao)
	{
		return SolcomController::buscarItensSolcom($_idprodservs, $_idcotacao);
	}

	public static function buscarItensSolcomPorNf($_idnf)
	{
		$results = SQL::ini(SolcomItemQuery::buscarItensSolcomPorNf(), [
            'idnf' => $_idnf
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
			$arrNf = [];
            foreach($results->data as $_nf)
            {
                $arrNf[$_nf['idprodserv']][$_nf['idsolcom']]['idprodserv'] = $_nf['idprodserv'];
                $arrNf[$_nf['idprodserv']][$_nf['idsolcom']]['idsolcom'] = $_nf['idsolcom'];
            }

			$dados['dados'] = $arrNf;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
	}

	public static function buscarTagClass($idtagclass)
	{
		return TagController::buscarTagClass($idtagclass);
	}

	public static function buscarTagsDisponiveisParaVinculo($idEmpresa)
	{
		return TagController::buscarTagsDisponiveisParaVinculo($idEmpresa);
	}

	public static function buscarTagPorIdNfItem($idnfitem)
	{
		$tags = SQL::ini(NfItemQuery::buscarTagPorIdNfItem(), [
			'idnfitem' => $idnfitem
		])::exec();

		if ($tags->error()) {
			parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
			return [];
		}

		return $tags->data[0];
	}

	public static function buscarItensTagPorIdNf($_idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarItensTagPorIdNf(), [
            'idnf' => $_idnf
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
			$arrNf = [];
            foreach($results->data as $_nf)
            {
                $arrNf[$_nf['idnfitem']][$_nf['idnf']]['idnfitemacao'] = $_nf['idnfitemacao'];
                $arrNf[$_nf['idnfitem']][$_nf['idnf']]['idobjeto'] = $_nf['idobjeto'];
                $arrNf[$_nf['idnfitem']][$_nf['idnf']]['idobjetoext'] = $_nf['idobjetoext'];
                $arrNf[$_nf['idnfitem']][$_nf['idnf']]['categoria'] = $_nf['categoria'];
                $arrNf[$_nf['idnfitem']][$_nf['idnf']]['kmrodados'] = $_nf['kmrodados'];
            }

			$dados['dados'] = $arrNf;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

	public static function buscarFreteInternacional($idnf)
	{
		$internacional = SQL::ini(NfQuery::buscarFreteInternacional(), [
			'idnf' => $idnf
		])::exec();

		if ($internacional->error()) {
			parent::error(__CLASS__, __FUNCTION__, $internacional->errorMessage());
			return [];
		}

		return $internacional->data[0];
	}

	public static function atualizarValorLote($vlrlote, $idlote)
	{
		return LoteController::atualizarValorLote($vlrlote, $idlote);
	}

	public static function buscarNfPorIdObjetoItem($_idnf)
	{
		$results = SQL::ini(NfItemQuery::buscarNfPorIdObjetoItem(), [
            "idnf" => $_idnf
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
			$arrNf = [];
            foreach($results->data as $_nf)
            {
                $arrNf[$_nf['idnfitem']]['idnf'] = $_nf['idnf'];
            }

            return $arrNf;
        }
	}

	public static function buscarLotePorIdNf($idnf)
	{
		$results = SQL::ini(NfQuery::buscarLotePorIdNf(), [
			"idnf" => $idnf
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
        } else {
            return $results->data;
            
        }
	}

	public static function buscarItemValorNulo($idnf)
	{
		$results = SQL::ini(NfItemQuery::buscarItemValorNulo(), [
			"idnf" => $idnf
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->numRows();
		}
	}


	public static function inserirNfItemAPP($arrayInsertNfItem)
	{
		return NfController::inserirNfItemAPP($arrayInsertNfItem);
	}

	public static function buscarSubCategoriaPorNf($_idnf)
    {
        $results = SQL::ini(TipoProdServQuery::buscarSubCategoriaPorNf(), [
            'idnf' => $_idnf
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
			$arrNf = [];
            foreach($results->data as $_nf)
            {
                $arrNf[$_nf['idnfitem']][$_nf['idtipoprodserv']]['idtipoprodserv'] = $_nf['idtipoprodserv'];
                $arrNf[$_nf['idnfitem']][$_nf['idtipoprodserv']]['tipoprodserv'] = $_nf['tipoprodserv'];
            }

            return $arrNf;
        }
    }
	
	public static function buscarAnexo($_idnf, $tipoNf, $tipoobjeto)
	{
		$resultsAnexoCotacao = SQL::ini(ArquivoQuery::buscarArquivoPorTipoArquivoTipoobjetoIdobjetoEvariosTipoObjeto(), [
            "tipoarquivo" => $tipoobjeto,
			"tipoobjeto" => $tipoNf,
			"idobjetos" => $_idnf
        ])::exec();

        if($resultsAnexoCotacao->error()){
            parent::error(__CLASS__, __FUNCTION__, $resultsAnexoCotacao->errorMessage());
            return [];
        }else{
			$arrAnexos = [];
			foreach($resultsAnexoCotacao->data as $_dadosAnexoCotacao)
			{
				$arrAnexos[$_dadosAnexoCotacao['tipoobjeto']][$_dadosAnexoCotacao['idarquivo']]['nome'] = $_dadosAnexoCotacao['nome'];
				$arrAnexos[$_dadosAnexoCotacao['tipoobjeto']][$_dadosAnexoCotacao['idarquivo']]['caminho'] = $_dadosAnexoCotacao['caminho'];
			}
            return $arrAnexos;
        }		
	}

	public static function buscarQtdNfItemXml($_idnf)
	{
		$resultsAnexoCotacao = SQL::ini(NfItemXmlQuery::buscarQtdNfItemXml(), [
			"idnf" => $_idnf
        ])::exec();

        if($resultsAnexoCotacao->error()){
            parent::error(__CLASS__, __FUNCTION__, $resultsAnexoCotacao->errorMessage());
            return [];
        }else{
            return $resultsAnexoCotacao->data[0];
        }		
	}

	public static function buscarXMLParaEnvioDeEmail($idnf){

        $results = SQL::ini(NfQuery::buscarXMLParaEnvioDeEmail(), [
            'idnf' => $idnf,
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
            
        }
    }

	public static function inserirArquivo($arrayArquivo){

        $results = SQL::ini(ArquivoQuery::inserirArquivo(), $arrayArquivo)::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
            
        }
    }

	public static function buscarModuloTipoLoteViculadoAUnidade($idUnidadePadrao)
    {
        $results = SQL::ini(_ModuloQuery::buscarModuloTipoLoteViculadoAUnidade(), [
            'idUnidadePadrao' => $idUnidadePadrao
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0]['modulo'];
        }
    }

	public static function atualizarStatuseFluxoStatusPorLote($status, $idfluxostatus, $idlote)
	{
		$results = SQL::ini(LoteQuery::atualizarStatuseFluxoStatusPorLote(), [
			"status" => $status,
			"idfluxostatus" => $idfluxostatus,
			"idlote" => $idlote
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}

	// ----- FUNÇÕES -----

	// ----- AUTOCOMPLETE -----
	public static function listarFormaPagamentoAtivo()
	{
		$listarFormaPagamento = [];
		$orderBy = " ORDER BY ord, descricao DESC";
		$formaPagamento = FormaPagamentoController::listarFormaPagamentoAtivo($orderBy);
		foreach ($formaPagamento as $_formaPagamento) {
			$listarFormaPagamento[$_formaPagamento["idformapagamento"]]["descricao"] = $_formaPagamento["descricao"];
		}

		return $listarFormaPagamento;
	}

	public static function listarFormaPagamentoAtivoPorLP($modulo = null,$idempresa = null)
	{
		$listarFormaPagamento = [];
		$orderBy = " ORDER BY ord, descricao DESC";
		if(!empty($idempresa)){
			$andRestricao = " AND f.idempresa = ".$idempresa." ";
		}else{
			$andRestricao = ($modulo == 'nfentrada') ? " AND f.idempresa = ".cb::idempresa() : "";
		}
		
		$formaPagamento = FormaPagamentoController::listarFormaPagamentoAtivoPorLP($orderBy, $andRestricao);
		foreach ($formaPagamento as $_formaPagamento) {
			$listarFormaPagamento[$_formaPagamento["idformapagamento"]]["descricao"] = $_formaPagamento["descricao"];
		}

		return $listarFormaPagamento;
	}

	public static function listarEnderecosParaEntregaPessoa($idpessoa)
	{
		$results = SQL::ini(EnderecoQuery::listarEnderecoPessoaPorTipo(), [
			"idpessoa" => $idpessoa,
			"idtipoendereco" => "2,8",
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			$contadorFat = 0;
			$contadorLr = 0;
			$dadoRt = array();

			foreach ($results->data as $k => $v) {
				if ($v['idtipoendereco'] == 2) {
					$contadorFat++;
				} else if ($v['idtipoendereco'] == 8) {
					$contadorLr++;
				}
			}
			if ($contadorLr == 1) {
				foreach ($results->data as $k => $v) {
					if ($v['idtipoendereco'] == 8) {
						$dadoRt = $v;
					}
				}
				return $dadoRt;
			}
			if ($contadorFat == 1) {
				foreach ($results->data as $k => $v) {
					if ($v['idtipoendereco'] == 2) {
						$dadoRt = $v;
					}
				}
				return $dadoRt;
			}

			if (($contadorFat > 1 || $contadorLr > 1) || ($contadorFat == 0 && $contadorLr == 0)) {
				return $dadoRt;
			}
		}
	}


	public static function buscarContatoPessoa($idpessoa)
	{
		$results = SQL::ini(PessoaQuery::buscarContatoTelefonePessoa(), [
			"idpessoa" => $idpessoa,
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			$contato = "";
			if ($results->data[0]['dddfixo'] and $results->data[0]['telfixo']) {
				$contato .= '(' . $results->data[0]['dddfixo'] . ')';
			}
			if ($results->data[0]['telfixo']) {
				$contato .= $results->data[0]['telfixo'];
			}
			if (!empty($contato) and !empty($results->data[0]['telcel'])) {
				$contato .= '/';
			}
			if ($results->data[0]['dddcel'] and $results->data[0]['telcel']) {
				$contato .= '(' . $results->data[0]['dddcel'] . ')';
			}
			if ($results->data[0]['telcel']) {
				$contato .= $results->data[0]['telcel'];
			}
			return $contato;
		}
	}

	public static function buscarClientesCompras($modulo)
	{
		$arrListarClientes = [];
		if ($modulo == 'comprasrh') {
			$listarClientes = PessoaController::buscarClientesPorIdTipoPessoa('1,2,5,7,9,12,116');
		} elseif ($modulo == 'comprassocios') {
			$listarClientes = PessoaController::buscarClientesPorIdTipoPessoa('1,2,5,6,7,9,11,12,116');
		} else {
			$listarClientes = PessoaController::buscarTodosClientesETipoFuncionario();
		}

		foreach ($listarClientes as $_clientes) {
			$arrListarClientes[$_clientes["idpessoa"]]["nome"] = $_clientes["nome"];
			$arrListarClientes[$_clientes["idpessoa"]]["tipo"] = $_clientes["tipo"];
			$arrListarClientes[$_clientes["idpessoa"]]["razaosocial"] = $_clientes["razaosocial"];
			$arrListarClientes[$_clientes["idpessoa"]]["cpfcnpj"] = $_clientes["cpfcnpj"];
			$arrListarClientes[$_clientes["idpessoa"]]["regimetrib"] = $_clientes["regimetrib"];
			$arrListarClientes[$_clientes["idpessoa"]]["email"] = $_clientes["email"];
		}

		return $arrListarClientes;
	}

	public static function buscarProdutoServicoComprado()
	{
		$i = 0;
		$arrProdutos = [];
		$listarProdutos = ProdservController::buscarProdutoServicoComprado();
		foreach ($listarProdutos as $_produtos) {
			$arrProdutos[$i]["value"] = $_produtos["idprodserv"];
			$arrProdutos[$i]["label"] = $_produtos["descr"];
			$i++;
		}

		return $arrProdutos;
	}

	public static function buscarSePessoaESocio($idpessoa)
	{
		return PessoaController::buscarSePessoaESocio($idpessoa);
	}

	public static function buscarProdutoServico($tiponf)
	{
		if ($tiponf == 'S') {
			$idtipoprodserv = '';
			$strtipo = " AND p.tipo='SERVICO' ";
		} elseif ($tiponf == 'C') {
			$idtipoprodserv = " AND p.idtipoprodserv <> ''";
			$strtipo = "  AND p.tipo = 'PRODUTO' ";
		} else {
			$idtipoprodserv = " AND p.idtipoprodserv <> ''";
			$strtipo = " AND p.tipo in ('SERVICO','PRODUTO') ";
		}

		$i = 0;
		$arrProdutos = [];
		$listarProdutoServico = ProdservController::buscarProdutoServicoCompradoPorIdTipoProdserv($strtipo, $idtipoprodserv);
		foreach ($listarProdutoServico as $_produtos) {
			$arrProdutos[$i]["value"] = $_produtos["idprodserv"];
			$arrProdutos[$i]["label"] = $_produtos["descr"];
			$i++;
		}

		return $arrProdutos;
	}

	public static function buscarFinalidadeProdserv()
	{
		$arrFinalidade = [];
		$listarFinalidade = FinalidadeProdservController::buscarFinalidadeProdserv();
		foreach ($listarFinalidade as $_finalidade) {
			$arrFinalidade[$_finalidade["idfinalidadeprodserv"]] = $_finalidade["finalidadeprodserv"];
		}

		return $arrFinalidade;
	}

	public static function buscarProdutoItemProdservQueNaoExisteXml($idnf, $idnfitemxml, $idprodserv, $consumo, $descr = null, $valoritem = null)
	{
		$arrNf = [];
		$count = 0;
		$listarNf =  NfController::buscarProdutoItemProdservQueNaoExisteXml($idnf, $idnfitemxml, $idprodserv, $consumo);
		foreach ($listarNf as $_nf) {
			if(similar_text($descr, $_nf["descr"], $perc) > '60%' || $valoritem == $_nf['vlritem']) {
				$arrNf[$_nf["idprodserv"]] = $_nf["codprodserv"];
				$count++;
			}
		}

		if($count == 0){
			foreach ($listarNf as $_nf) {
				$arrNf[$_nf["idprodserv"]] = $_nf["codprodserv"];
			}	
		}

		return $arrNf;
	}

	public static function listarFuncionarioPessoaPorIdtipoPessoa($idtipopessoa, $tipo, $status)
	{
		$arrPessoa = [];
		$listarPessoa =  PessoaController::listarFuncionarioPessoaPorIdtipoPessoa($idtipopessoa, $tipo, $status);
		foreach ($listarPessoa as $_pessoa) {
			if ($tipo == 'funcionarioCb') {
				$arrPessoa[$_pessoa["idpessoa"]] = $_pessoa["nomecurto"];
			} elseif ($tipo == 'pessoasPorSession') {
				$arrPessoa[$_pessoa["idpessoa"]] = $_pessoa["nome"];
			}
		}

		return $arrPessoa;
	}

	public static function buscarIdNfeNfItemPorObsNotNULLEIdNf($idnf)
	{
		return NfController::buscarIdNfeNfItemPorObsNotNULLEIdNf($idnf);
	}

	public static function listarFormapagamentoAgrupadoPorEmpresa()
	{
		return FormaPagamentoController::listarFormapagamentoAgrupadoPorEmpresa();
	}

	public static function buscarNfContaPagar($idcontapagar, $modulo, $idmodulo)
	{
		return ContaPagarController::buscarNfContaPagar($idcontapagar, $modulo, $idmodulo);
	}

	public static function buscarFormaPagamentoPorStatusEIdEmpresa($status)
	{
		$arrFormaPagamento = [];
		$listarFormaPagamento =  FormaPagamentoController::buscarFormaPagamentoPorStatusEIdEmpresa($status);
		foreach ($listarFormaPagamento['dados'] as $_formaPagamento) {
			$arrFormaPagamento[$_formaPagamento["idformapagamento"]] = $_formaPagamento["descricao"];
		}

		return $arrFormaPagamento;
	}

	public static function buscarContaItemPorIdprodserv($idprodserv)
	{
		$results = SQL::ini(ContaItemQuery::buscarContaItemPorIdprodserv(), [
			"idprodserv" => $idprodserv
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		} else {
			return $results->data[0];
		}
	}

	public static function buscarIdProdservFornPorIdprodservIdForn($idprodserv, $idpessoa)
	{
		return ProdservController::buscarIdProdservFornPorIdprodservIdForn($idprodserv, $idpessoa);
	}

	public static function buscarIdProdservPorIdpessoaIdCodForn($cprodforn, $idpessoa, $idempresa)
	{
		return ProdservController::buscarIdProdservPorIdpessoaIdCodForn($cprodforn, $idpessoa, $idempresa);
	}


	public static function inserirCarimbo($arrayInsertNfItem)
	{
		$results = SQL::ini(CarimboQuery::inserir(), $arrayInsertNfItem)::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		} else {
			return $results->lastInsertId();
		}
	}

	public static function buscarNfPorIdnf($idnf)
	{
		return NfController::buscarNfPorIdnf($idnf);
	}

	public static function buscaNfitemFaturar($idnf)
	{

		$results = SQL::ini(NfitemQuery::buscaNfitemFaturar(), [
			"idnf" => $idnf
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->data;
		}
	}

	public static function buscarFretePorChave($chaveFrete)
	{
		if (!isset(self::$fretes[$chaveFrete]))
			return [];

		return self::$fretes[$chaveFrete];
	}

	public static function buscarValorItensFaturamento($idobjetoorigem, $tipoobjetoorigem, $tipoobjeto, $idobjeto) {
		$results = SQL::ini(ContaPagarQuery::buscarValorItensFaturamento(), [
            "idobjetoorigem" => $idobjetoorigem,
            "tipoobjetoorigem" => $tipoobjetoorigem,
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
		
		return $results->data[0];
	}

	public static function buscarCTEDisponiveisParaVinculo($idNf, $idEmpresa) {
		$fretes = SQL::ini(NfQuery::buscarCTEDisponiveisParaVinculo(), [
			'idnf' => $idNf,
			'idempresa' => $idEmpresa
		])::exec();

		if ($fretes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $fretes->errorMessage());
            return [];
        }
		
		return $fretes->data;
	}
	
	public static function buscarComprasDisponiveisParaVinculo($idNfBusca, $idNf, $idEmpresa) {
		$compras = SQL::ini(NfQuery::buscarComprasDisponiveisParaVinculo(), [
			'idnf' => $idNf,
			'idempresa' => $idEmpresa,
			'idnfbusca' => $idNfBusca
		])::exec();

		if ($compras->error()) {
            parent::error(__CLASS__, __FUNCTION__, $compras->errorMessage());
            return [];
        }
		
		return $compras->data;
	}

	// ----- AUTOCOMPLETE -----

	// ----- Variáveis de apoio -----
	public static $_nfentrada = array(
		'R' => 'RH',
		'D' => 'Sócios',
		'C' => 'Danfe',
		'S' => 'Serviço',
		'T' => 'CTe',
		'E' => 'Concessionária',
		'M' => 'Guia/Cupom',
		'B' => 'Recibo',
		'F' => 'Captação',
		'O' => 'Outros'
		
	);

	public static $_rh = array('R' => 'RH');

	public static $_socios = array('D' => 'Sócios');

	public static $_tipoNfF = array(
		'C' => 'Danfe',
		'S' => 'Serviço',
		'T' => 'CTe',
		'E' => 'Concessionária',
		'M' => 'Guia/Cupom',
		'B' => 'Recibo',
		'F' => 'Captação'
	);

	public static $_outros = array(
		'C' => 'Danfe',
		'S' => 'Serviço',
		'T' => 'CTe',
		'E' => 'Concessionária',
		'M' => 'Guia/Cupom',
		'B' => 'Recibo',
		'O' => 'Outros'
	);

	public static $_cobranca = array(
		'S' => 'Serviço',
		'M' => 'Guia/Cupom',
		'B' => 'Recibo',
		'O' => 'Outros'
	);

	public static $_conssecionaria = array(
		'ENERGIA' => 'Energia',
		'TELECOMUNICACAO' => 'Telecomunicação',
		'AGUA' => 'Água'
	);

	public static $_simNao = array(
		'Y' => 'Sim',
		'N' => 'Não'
	);

	public static $_simNaoNenhum = array(
		'S' => 'Sim',
		'N' => 'Não',
		'NA' => 'N/A'
	);

	public static $_debitoCredito = array(
		'D' => 'Débito',
		'C' => 'Crédito'
	);

	public static $_periodo = array(
		'D' => 'Dias',
		'M' => 'Mês',
		'Y' => 'Ano'
	);

	public static $_modFrete = array(
		'0' => '0 - Por conta do Emitente',
		'1' => '1 - Por conta do Destinatário/Remetente',
		'2' => '2 - Por conta de Terceiro',
		'3' => '9 - Sem Frete'
	);

	public static $_tipofrete = array(
		'SUPRIMENTOS' => 'Suprimentos',
		'RECEBIMENTO' => 'Recebimento de Materiais',
		'ENVIO' => 'Envio de Produtos (Vacinas / Antígenos / Material de Coleta / Material para Análise)'
	);

	public static $_justificativa = array(
		'' => '',
		'ATRASO' => 'Atraso na Entrega',
		'PEDIDO FORNECEDOR' => 'A Pedido do Fornecedor',
		'OUTROS' => 'Outros'
	);

	public static $_manutencao = array(
		'' => '',
		'ACESSORIOS' => 'ACESSÓRIOS',
		'AR CONDICIONADO' => 'AR CONDICIONADO',
		'BATERIAS' => 'BATERIAS',
		'CAMBIOTRANSMISSAO' => 'CÂMBIO E TRANSMISSÃO',
		'CHAVE' => 'CHAVE',
		'COMBUSTIVEL' => 'COMBUSTÍVEL',
		'CORREIAS' => 'CORREIAS',
		'ELETRICA' => 'ELÉTRICA',
		'EMBREAGEM' => 'EMBREAGEM',
		'ESCAPE' => 'ESCAPE',
		'FREIOS' => 'FREIOS',
		'FUNILARIA' => 'FUNILARIA',
		'HIGIENIZACAO' => 'HIGIENIZAÇÃO',
		'LUZES' => 'LUZES',
		'MANUTENCAOPREVENTIVA' => 'MANUTENÇÃO PREVENTIVA',
		'PNEUS' => 'PNEUS',
		'REFRIGERACAO' => 'REFRIGERAÇÃO',
		'RODA' => 'RODA',
		'SISTEMAELETRICO' => 'SISTEMA ELÉTRICO',
		'SUSPENSAO' => 'SUSPENSÃO',
		'VIDROS' => 'VIDROS',
		'OUTROS' => 'OUTROS'
	);

	public static $_justificativaPrazo = array(
		'' => '',
		'ATRASO' => 'Atraso na Entrega',
		'NOTAERRADA' => 'Nota com Problema',
		'OUTROS' => 'Outros'
	);

	
    public static function buscarNfDanfe($idnf)
	{
      
        $results = SQL::ini(NfQuery::buscarNfDanfe(), [         
            "idnf" => $idnf           
        ])::exec();

		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

	public static $fretes = [
		'honimp' => [
			'label' => 'Honorário de Importação',
			'idprodserv' => [
				'1' => 19334,
				'2' => 18935,
				'4' => 19571,
				'15' => 39041,
				'19' => 39320
			]
		],
		'impnac' => [
			'label' => 'Frete Importação Nacional',
			'idprodserv' => [
				'1' => 31133,
				'2' => 27631,
				'4' => 25583,
				'15' => 39048,
				'19' => 40177
			]
		],
		'impint' => [
			'label' => 'Frete Importação Internacional',
			'idprodserv' => [
				'1' => 19338,
				'2' => 27630,
				'4' => 19616,
				'15' => 39049,
				'19' => 39797 
			]
		],
		'aerop' => [
			'label' => 'Armazenagem Aeroportuária',
			'idprodserv' => [
				'1' => 19363,
				'2' => 27634,
				'4' => 19614,
				'15' => 39037,
				'19' => 39319
			]
		],
		'icms' => [
			'label' => 'Impostos de importação',
			'idprodserv' => [
				'1' => 19442,
				'2' => 27635,
				'4' => 19615,
				'15' => 39047,
				'8' => 19441,
				'19' => 39321
			]
		],
		'siscomex' => [
			'label' => 'Siscomex',
			'idprodserv' => []
		],
	];
	// ----- Variáveis de apoio -----
}
