<?
require_once(__DIR__ . "/_controller.php");

// QUERYS
require_once(__DIR__ . "/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/log_query.php");
require_once(__DIR__ . "/../querys/nf_query.php");
require_once(__DIR__ . "/../querys/empresaemails_query.php");
require_once(__DIR__ . "/../querys/modulohistorico_query.php");
require_once(__DIR__ . "/../querys/cotacao_query.php");
require_once(__DIR__ . "/../querys/arquivo_query.php");
require_once(__DIR__ . "/../querys/unidadevolume_query.php");
require_once(__DIR__ . "/../querys/objetovinculo_query.php");
require_once(__DIR__ . "/../querys/_auditoria_query.php");
require_once(__DIR__ . "/../querys/nfconfpagar_query.php");

//Controllers
require_once(__DIR__ . "/../controllers/formapagamento_controller.php");
require_once(__DIR__ . "/../controllers/pessoa_controller.php");
require_once(__DIR__ . "/../controllers/contaitem_controller.php");
require_once(__DIR__ . "/../controllers/fluxo_controller.php");
require_once(__DIR__ . "/../controllers/solcom_controller.php");
require_once(__DIR__ . "/../controllers/nf_controller.php");
require_once(__DIR__ . "/../controllers/prodserv_controller.php");
require_once(__DIR__ . "/../controllers/tipoprodserv_controller.php");
require_once(__DIR__ . "/../controllers/envioemail_controller.php");
require_once(__DIR__ . "/../controllers/finalidadeprodserv_controller.php");
require_once(__DIR__ . "/../controllers/endereco_controller.php");
require_once(__DIR__ . "/../controllers/empresa_controller.php");
require_once(__DIR__ . "/../controllers/unidade_controller.php");
require_once(__DIR__ . "/../controllers/contapagar_controller.php");

class CotacaoController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarValorTotalCotacao($idcotacao)
	{
		$results = SQL::ini(NfQuery::buscarValorTotalCotacao(), [
			'idcotacao' => $idcotacao
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return (count($results->data) > 0) ? $results->data[0]['total'] : "";
		}
	}

	public static function buscarQuantidadeTipoEnvioEmpesaEmails($idempresa)
	{
		$results = SQL::ini(EmpresaEmailsQuery::buscarQuantidadeTipoEnvioEmpesaEmails(), [
			'idempresa' => $idempresa
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			foreach ($results->data as $_results) {
				$qtdempresaemail[$_results['tipoenvio']] = $_results['qtd'];
			}

			return $qtdempresaemail;
		}
	}

	public static function buscarHistoricoAlteracaoPrazoCotacao($idcotacao, $campo)
	{
		$results = SQL::ini(ModuloHistoricoQuery::buscarHistoricoAlteracao(), [
			"idobjeto" => $idcotacao,
			"tipoobjeto" => "cotacao",
			"campo" => " AND h.campo = '$campo'"
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return (count($results->data) > 0) ? $results->data : "";
		}
	}

	public static function buscarPreferenciaPessoa($caminho, $idpessoa)
	{
		return PessoaController::buscarPreferenciaPessoa($caminho, $idpessoa);
	}

	public static function buscarGrupoES($idobjeto, $tipoobjeto)
	{
		return ContaItemController::buscarGrupoES($idobjeto, $tipoobjeto);
	}

	public static function buscarRotuloStatusFluxo($tabela, $primarykey, $idobjeto)
	{
		return FluxoController::buscarRotuloStatusFluxo($tabela, $primarykey, $idobjeto);
	}

	public static function listarSolicitacaoCompraVincultadaCotacao($idobjeto, $modulo, $idempresa)
	{
		return SolcomController::listarSolicitacaoCompraVincultadaCotacao($idobjeto, $modulo, $idempresa);
	}

	public static function buscarNfPorTipoObjetoSoliPor($_idcotacao, $idempresa, $cancelados = false)
	{
		$nfsCotacao = NfController::buscarNfPorTipoObjetoSoliPor($_idcotacao, 'cotacao', $cancelados);

		$arrNf = [];
		$arrIdNf = [];
		$arrIdPessoa = [];
		foreach ($nfsCotacao as $_nf) {
			array_push($arrIdNf, $_nf['idnf']);
			array_push($arrIdPessoa, $_nf['idpessoa']);
		}

		$_idnfs = implode(",", $arrIdNf);
		$_idpessoas = implode(",", $arrIdPessoa);

		$arrNf['nf'] = $nfsCotacao;
		$arrNf['nfitens'] = empty($_idnfs) ? [] : self::buscarNtItens($_idnfs, $_idcotacao, $idempresa);
		$arrNf['resultadoavaliacaofornecedor'] = empty($_idpessoas) ? [] : self::buscarResultadoAvaliacaoFornecedor($_idpessoas);
		$arrNf['mailfila'] = empty($_idnfs) ? [] : self::buscarMailFila($_idnfs);
		$arrNf['anexocotacao'] = empty($_idnfs) ? [] : self::buscarAnexoCotacao($_idnfs);
		$arrNf['empresaemailobjeto'] = empty($_idnfs) ? [] : self::buscarEmpresaEmailObjeto($_idnfs);
		$arrNf['fillSelectFinalidadeProdserv'] = empty($_idpessoas) ? [] : self::buscarFillSelectFinalidadeProdserv($_idpessoas, $idempresa);
		$arrNf['fillSelectNovoItem'] = empty($_idpessoas) && empty($_idnfs) ? [] : self::buscarFillSelectNovoItem($_idpessoas, $_idnfs, $idempresa);

		return $arrNf;
	}

	public static function buscarNtItens($_idnfs, $_idcotacao, $idempresa)
	{
		$nfsItens = NfController::buscarNtItens($_idnfs);

		$arrIdProdServ = [];
		$arrNfItem = [];
		$arrIdContaItem = [];

		foreach ($nfsItens as $_itens) {
			if (!empty($_itens['idprodserv'])) {
				array_push($arrIdProdServ, $_itens['idprodserv']);
			}
			if (!empty($_itens['idcontaitem'])) {
				array_push($arrIdContaItem, $_itens['idcontaitem']);
			}

			foreach ($_itens as $_nfitemcol => $_nfitemvalorcol) {
				$arrNfItem[$_itens['idnf']][$_itens['idnfitem']][$_nfitemcol] = $_nfitemvalorcol;
			}

			$arrayMigrarCotacao[$_itens['idnf']]['idcontaitem'][] = $_itens['idcontaitem'];
			$arrayMigrarCotacao[$_itens['idnf']]['idtipoprodserv'][] = $_itens['idtipoprodserv'];
		}

		$_idprodservs = implode(",", $arrIdProdServ);
		$_idcontaitens = implode(",", $arrIdContaItem);

		if (!empty($_idprodservs)) {
			$arrNfItem['semelhantes'] = self::buscarItensSemelhantes($_idprodservs, $_idcotacao);
			$arrNfItem['fillSelectContaItemProdserv'] = self::listarFillSelecContaItemProdserv($_idprodservs);
			$arrNfItem['fillSelectTipoProdservProdserv'] = self::listarFillSelectTipoProdservProdserv($_idprodservs);
			$arrNfItem['itenssolcom'] = self::buscarItensSolcom($_idprodservs, $_idcotacao);
		}

		if (!empty($_idcontaitens)) {
			$arrNfItem['fillSelectTipoProdservIdContaItem'] = self::listarFillSelectTipoProdservIdContaItem($_idcontaitens);
		}

		if (!empty($arrayMigrarCotacao)) {
			$arrNfItem['migrarCotacao'] = self::listarCotacaoParaMigrar($arrayMigrarCotacao, $_idcotacao, $idempresa);
		}

		if (!empty($_idcontaitens)) {
			$arrNfItem['traduzirContaItem'] = self::buscarTraduzirContaItem($_idcontaitens);
		}

		return $arrNfItem;
	}

	public static function buscarItensSemelhantes($_idprodservs, $_idcotacao)
	{
		$resultsItensSemelhantes = SQL::ini(CotacaoQuery::buscarItensSemelhantes(), [
			"idprodservs" => $_idprodservs,
			"idcotacao" => $_idcotacao
		])::exec();

		if ($resultsItensSemelhantes->error()) {
			parent::error(__CLASS__, __FUNCTION__, $resultsItensSemelhantes->errorMessage());
			return "";
		} else {
			$i = 0;
			foreach ($resultsItensSemelhantes->data as $_dadosItensSemelhantes) {
				$arrSemelhantes[$_dadosItensSemelhantes['idprodserv']][$i]['idnf'] = $_dadosItensSemelhantes['idnf'];
				$arrSemelhantes[$_dadosItensSemelhantes['idprodserv']][$i]['idnfitem'] = $_dadosItensSemelhantes["idnfitem"];
				$arrSemelhantes[$_dadosItensSemelhantes['idprodserv']][$i]['vlritem'] = number_format(tratanumero($_dadosItensSemelhantes['vlritem']), 2, ',', '.');
				$arrSemelhantes[$_dadosItensSemelhantes['idprodserv']][$i]['nome'] = $_dadosItensSemelhantes['nome'];
				$arrSemelhantes[$_dadosItensSemelhantes['idprodserv']][$i]['rotulo'] = $_dadosItensSemelhantes['rotulo'];
				$arrSemelhantes[$_dadosItensSemelhantes['idprodserv']][$i]['nfe'] = $_dadosItensSemelhantes['nfe'];
				$i++;
			}

			return $arrSemelhantes;
		}
	}

	public static function listarFillSelecContaItemProdserv($_idprodservs)
	{
		$arrContaItem = [];
		$status = " AND c.status = 'ATIVO'";
		$resultsContaItemProdserv = ContaItemController::buscarContaItemProdservContaItem($_idprodservs, $status);
		foreach ($resultsContaItemProdserv as $_dadosContaItemProdserv) {
			$arrContaItem[$_dadosContaItemProdserv['idprodserv']][$_dadosContaItemProdserv['idcontaitem']] = $_dadosContaItemProdserv['contaitem'];
		}

		return $arrContaItem;
	}

	public static function listarFillSelectTipoProdservProdserv($_idprodservs)
	{
		$arrTipoProdserv = [];
		$resultsProdservTipoProdserv = ProdServController::listarProdservTipoProdServ($_idprodservs);
		foreach ($resultsProdservTipoProdserv as $_dadosProdservTipoProdserv) {
			$arrTipoProdserv[$_dadosProdservTipoProdserv['idprodserv']][$_dadosProdservTipoProdserv['idtipoprodserv']] = $_dadosProdservTipoProdserv['tipoprodserv'];
		}

		return $arrTipoProdserv;
	}

	public static function listarFillSelectTipoProdservIdContaItem($_idcontaitens)
	{
		$arrTipoProdserv = [];
		$resultsTipoProdserv = TipoProdServController::listarContaItemTipoProdservTipoProdServ($_idcontaitens);
		foreach ($resultsTipoProdserv as $_dadosTipoProdserv) {
			$arrTipoProdserv[$_dadosTipoProdserv['idcontaitem']][$_dadosTipoProdserv['idtipoprodserv']] = $_dadosTipoProdserv['tipoprodserv'];
		}

		return $arrTipoProdserv;
	}

	public static function listarProdservTipoProdServPorEmpresa($idempresa)
	{
		$arrProdservTipoProdServPorEmpresa = [];
		$resultsProdservTipoProdServPorEmpresa = TipoProdServController::listarProdservTipoProdServPorEmpresa($idempresa);
		foreach ($resultsProdservTipoProdServPorEmpresa as $_dadosProdservTipoProdServPorEmpresa) {
			$arrProdservTipoProdServPorEmpresa[$_dadosProdservTipoProdServPorEmpresa['idtipoprodserv']] = $_dadosProdservTipoProdServPorEmpresa['tipoprodserv'];
		}

		return $arrProdservTipoProdServPorEmpresa;
	}

	public static function buscarItensSolcom($_idprodservs, $_idcotacao)
	{
		$arrSolcomItens = [];
		$resultsSolcomItens = SolcomController::buscarItensSolcom($_idprodservs, $_idcotacao);
		foreach ($resultsSolcomItens as $_dadosSolcomItens) {
			$arrSolcomItens[$_dadosSolcomItens['idprodserv']][$_dadosSolcomItens['idsolcom']] = $_dadosSolcomItens['idsolcom'];
			$arrSolcomItens[$_dadosSolcomItens['idprodserv']]['urgencia'][] = $_dadosSolcomItens['urgencia'];
		}

		return $arrSolcomItens;
	}

	public static function buscarTraduzirContaItem($_idcontaitens)
	{
		$arrContaItem = [];
		$resultsTipoProdserv = ContaItemController::buscarContaItem($_idcontaitens);
		foreach ($resultsTipoProdserv as $_dadosTipoProdserv) {
			$arrContaItem[$_dadosTipoProdserv['idcontaitem']] = $_dadosTipoProdserv['contaitem'];
		}

		return $arrContaItem;
	}

	public static function buscarResultadoAvaliacaoFornecedor($_idpessoas)
	{
		$arrAvalicaoFornecedor = [];
		$resultsAvalicacoFornecedor = PessoaController::buscarResultadoAvaliacaoFornecedor($_idpessoas);
		foreach ($resultsAvalicacoFornecedor as $_dadosAvalicacoFornecedor) {
			$arrAvalicaoFornecedor[$_dadosAvalicacoFornecedor['idpessoa']]['resultado'] = $_dadosAvalicacoFornecedor['resultado'];
		}

		return $arrAvalicaoFornecedor;
	}

	public static function buscarMailFila($_idnfs)
	{
		$arrMailFila = [];
		$resultsMailFila = EnvioEmailController::buscarMailFila($_idnfs);
		foreach ($resultsMailFila as $_dadosMailFila) {
			$arrMailFila[$_dadosMailFila['idsubtipoobjeto']]['idmailfila'] = $_dadosMailFila['idmailfila'];
		}

		return $arrMailFila;
	}

	public static function buscarDominio($_idnfs)
	{
		return EnvioEmailController::buscarDominio($_idnfs);
	}

	public static function buscarEmpresaEmailObjeto($_idnfs)
	{
		$arrEmpresaEmailObjeto = [];
		$resultsEmpresaEmailObjeto = EnvioEmailController::buscarEmpresaEmailObjeto($_idnfs);
		foreach ($resultsEmpresaEmailObjeto as $_dadosEmpresaEmailObjeto) {
			$arrEmpresaEmailObjeto[$_dadosEmpresaEmailObjeto['idobjeto']][$_dadosEmpresaEmailObjeto['tipoenvio']] = $_dadosEmpresaEmailObjeto['tipoenvio'];
		}

		return $arrEmpresaEmailObjeto;
	}

	public static function buscarAnexoCotacao($_idnfs)
	{
		$resultsAnexoCotacao = SQL::ini(ArquivoQuery::buscarArquivoPorTipoArquivoTipoobjetoIdobjeto(), [
			"tipoarquivo" => "ANEXO",
			"tipoobjeto" => "cotacaoforn",
			"idobjetos" => $_idnfs
		])::exec();

		if ($resultsAnexoCotacao->error()) {
			parent::error(__CLASS__, __FUNCTION__, $resultsAnexoCotacao->errorMessage());
			return [];
		} else {
			$arrAnexos = [];
			foreach ($resultsAnexoCotacao->data as $_dadosAnexoCotacao) {
				$arrAnexos[$_dadosAnexoCotacao['idobjeto']][$_dadosAnexoCotacao['idarquivo']]['nome'] = $_dadosAnexoCotacao['nome'];
				$arrAnexos[$_dadosAnexoCotacao['idobjeto']][$_dadosAnexoCotacao['idarquivo']]['caminho'] = $_dadosAnexoCotacao['caminho'];
			}
			return $arrAnexos;
		}
	}

	public static function buscarFillSelectFinalidadeProdserv($_idpessoas, $idempresa)
	{
		$finalidadeProdserv = FinalidadeProdservController::buscarFinalidadeProdservPorIdPessoa($_idpessoas, $idempresa);
		$arrFinalidadePessoa = [];
		foreach ($finalidadeProdserv as $_dadosFinalidadeProdserv) {
			$arrFinalidadePessoa[$_dadosFinalidadeProdserv['idpessoa']][$_dadosFinalidadeProdserv['idfinalidadeprodserv']] = $_dadosFinalidadeProdserv['finalidadeprodserv'];
		}

		return $arrFinalidadePessoa;
	}

	public static function buscarFillSelectNovoItem($_idpessoas, $_idnfs, $idempresa)
	{
		$fornecedorProdserv = ProdServController::buscarFornecedorProdserv($_idpessoas, $_idnfs, $idempresa);

		return $fornecedorProdserv;
	}

	public static function buscarCtePorIdNfe($idnfe, $idnf)
	{
		$cte = NfController::buscarCtePorIdNfe($idnfe, $idnf);
		return $cte;
	}

	public static function buscarCte($idnf)
	{
		$cte = NfController::buscarCte($idnf);
		return $cte;
	}

	public static function listarSugestaoTodos($idcontaitem, $where, $idcotacao, $idempresa)
	{
		$results = SQL::ini(CotacaoQuery::listarSugestaoTodos(), [
			"idtipoprodserv" => $idcontaitem,
			"where" => $where,
			"idobjeto" => $idcotacao,
			"idempresa" => $idempresa
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->data;
		}
	}

	public static function buscarProdservPelaNf($idcotacao)
	{
		$prodserv = NfController::buscarProdservPelaNf($idcotacao);
		return $prodserv;
	}

	public static function buscarItensNfPorIdProdserv($idcotacao, $idprodserv)
	{
		return NfController::buscarItensNfPorIdProdserv($idcotacao, $idprodserv);
	}

	public static function buscarSolicitacaoComprasAssociadoCotacao($idcotacao)
	{
		return NfController::buscarSolicitacaoComprasAssociadoCotacao($idcotacao);
	}

	public static function buscarEmpresaPorIdEmpresa($idempresa)
	{
		return EmpresaController::buscarEmpresaPorIdEmpresa($idempresa);
	}

	public static function buscarIdNfPorTipoObjetoStatusIdpessoa($idobjetosolipor, $tipoobjetosolipor, $status, $idpessoa)
	{
		return NfController::buscarIdNfPorTipoObjetoStatusIdpessoa($idobjetosolipor, $tipoobjetosolipor, $status, $idpessoa);
	}

	public static function buscarIdunidadePorTipoUnidade($idtipounidade, $idempresa)
	{
		return UnidadeController::buscarIdunidadePorTipoUnidade($idtipounidade, $idempresa);
	}

	public static function buscarFornecedoresPertencentesCotacao($idobjetosolipor, $tipoobjetosolipor, $cond_where, $idprodserv)
	{
		return NfController::buscarFornecedoresPertencentesCotacao($idobjetosolipor, $tipoobjetosolipor, $cond_where, $idprodserv);
	}

	public static function buscarNfPorIdnf($idnf)
	{
		return NfController::buscarNfPorIdnf($idnf);
	}

	public static function buscarIdTipoUnidade($tiponf)
	{
		if (!empty($tiponf) && $tiponf != 'V') {
			switch ($tiponf) {
				case 'R':
					$idtipounidade = 14;
					break;
				case 'F':
				case 'T':
					$idtipounidade = 21;
					break;
				case 'D':
					$idtipounidade = 22;
					break;
				default:
					$idtipounidade = 19;
			}
		} else {
			$idtipounidade = 19;
		}

		return $idtipounidade;
	}

	public static function buscarFornecedoresPertencentesIdnf($idnf, $idprodserv)
	{
		return NfController::buscarFornecedoresPertencentesIdnf($idnf, $idprodserv);
	}

	public static function buscarDadosNfItemPorIdNfItem($idnfitem)
	{
		return NfController::buscarDadosNfItemPorIdNfItem($idnfitem);
	}

	public static function buscarDadosNfPorIdNfItem($idnfitem)
	{
		return NfController::buscarDadosNfPorIdNfItem($idnfitem);
	}

	public static function buscarNfPorIdNfDeslocamento($idobjetosolipor, $tipoobjetosolipor, $idpessoa, $sinal, $idnf)
	{
		return NfController::buscarNfPorIdNfDeslocamento($idobjetosolipor, $tipoobjetosolipor, $idpessoa, $sinal, $idnf);
	}

	public static function buscarIdNfConfPagar($idnf)
	{
		$results = SQL::ini(NfConfPagar::buscarIdNfConfPagar(), [
			"idnf" => $idnf
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->data;
		}
	}

	public static function buscarQuantidadeItensSolcomPorIdSolcolmItem($idsolcomitem)
	{
		return SolcomController::buscarQuantidadeItensSolcomPorIdSolcolmItem($idsolcomitem);
	}

	public static function buscarNfPessoaPorIdNf($idnf)
	{
		return NfController::buscarNfPessoaPorIdNf($idnf);
	}

	public static function atualizarDataVisualizacaoFornecedor($idnf)
	{
		$results = SQL::ini(NfQuery::atualizarDataVisualizacaoFornecedor(), [
			"idnf" => $idnf
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}

	public static function atualizarNfParaCanceladoComStatusDiferenteConcluido($idfluxostatus, $idobjetosolipor, $tipoobjetosolipor)
	{
		return NfController::atualizarNfParaCanceladoComStatusDiferenteConcluido($idfluxostatus, $idobjetosolipor, $tipoobjetosolipor);
	}

	public static function inserirNf($idpessoa, $idempresa, $idobjetosolipor, $idfluxostatus, $idunidade, $tipoobjetosolipor, $status, $tpnf, $tiponf, $usuario)
	{
		return NfController::inserirNf($idpessoa, $idempresa, $idobjetosolipor, $idfluxostatus, $idunidade, $tipoobjetosolipor, $status, $tpnf, $tiponf, $usuario);
	}

	public static function inserirNfDuplicada($arrayInsertNf)
	{
		return NfController::inserirNfDuplicada($arrayInsertNf);
	}

	public static function inserirNfTransportadora($idpessoa, $idempresa, $idobjetosolipor, $idfluxostatus, $idunidade, $tipoobjetosolipor, $status, $tiponf, $usuario, $previsaoentrega, $idformapagamento, $subtotal, $total, $parcelas, $dtemissao)
	{
		return NfController::inserirNfTransportadora($idpessoa, $idempresa, $idobjetosolipor, $idfluxostatus, $idunidade, $tipoobjetosolipor, $status, $tiponf, $usuario, $previsaoentrega, $idformapagamento, $subtotal, $total, $parcelas, $dtemissao);
	}

	public static function buscarSolcomQuantidadeItensSolcomCotacao($idsolcomitem, $idprodserv, $idcotacao)
	{
		return SolcomController::buscarSolcomQuantidadeItensSolcomCotacao($idsolcomitem, $idprodserv, $idcotacao);
	}

	public static function buscarProdutoNfPorIdNf($idnf)
	{
		return NfController::buscarProdutoNfPorIdNf($idnf);
	}

	public static function atualizarStatusIdcotacaoSolcomItem($idcotacao, $status, $idsolcomitem, $usuario)
	{
		return SolcomController::atualizarStatusIdcotacaoSolcomItem($idcotacao, $status, $idsolcomitem, $usuario);
	}

	public static function atualizarSolcomItensAssociados($novo_idcotacao, $idcotacao, $idprodserv, $usuario)
	{
		return SolcomController::atualizarSolcomItensAssociados($novo_idcotacao, $idcotacao, $idprodserv, $usuario);
	}

	public static function buscarRateioNfItem($idnotafiscal, $tipoobjeto)
	{
		return NfController::buscarRateioNfItem($idnotafiscal, $tipoobjeto);
	}

	public static function atualizarProporcaoNfConfPagar($idnf)
	{
		$results = SQL::ini(NfConfPagar::atualizarProporcaoNfConfPagar(), [
			"idnf" => $idnf
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}

	public static function inserirIdNfContaPagar($idnf, $idempresa, $usuario, $parc = 1)
	{
		$results = SQL::ini(NfConfPagar::inserirIdNfContaPagar(), [
			"idnf" => $idnf,
			"parcela" => $parc,
			"idempresa" => $idempresa,
			"usuario" => $usuario
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}

	public static function inserirIdNfContaPagarDataReceb($idnf, $idempresa, $usuario, $dtemissao, $parc = null)
	{
		$results = SQL::ini(NfConfPagar::inserirIdNfContaPagarDataReceb(), [
			"idnf" => $idnf,
			"datareceb" => $dtemissao,
			"parcela" => $parc,
			"idempresa" => $idempresa,
			"usuario" => $usuario
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}

	public static function inserirLog($arrayLog)
	{
		$results = SQL::ini(LogQuery::inserirLog(), $arrayLog)::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}

	public static function buscarInformacoesCotacao($idnf)
	{
		$results = SQL::ini(NfQuery::buscarInformacoesCotacao(), [
			"idnf" => $idnf
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->data[0];
		}
	}

	public static function apagarNfConfPagar($idnfconfpagar)
	{
		$results = SQL::ini(NfConfPagar::apagarNfConfPagar(), [
			"idnfconfpagar" => $idnfconfpagar
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}

	public static function apagarNfConfPagarPorIdnf($idnf)
	{
		$results = SQL::ini(NfConfPagar::apagarNfConfPagarPorIdnf(), [
			"idnf" => $idnf
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}

	public static function aturalizarMailFilaPorSubTipoIdSubTipoObjeto($ididobjeto, $idsubtipoobjeto, $subtipoobjeto, $tipoobjeto)
	{
		$results = SQL::ini(MailFilaQuery::aturalizarMailFilaPorSubTipoIdSubTipoObjeto(), [
			"idobjeto" => $ididobjeto,
			"idsubtipoobjeto" => $idsubtipoobjeto,
			"subtipoobjeto" => $subtipoobjeto,
			"tipoobjeto" => $tipoobjeto
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->data;
		}
	}

	public static function buscarConfiguracoesFormaPagamento($idnotafiscal)
	{
		return FormaPagamentoController::buscarConfiguracoesFormaPagamento($idnotafiscal);
	}

	public static function inserirValoresContaPagarItem($idempresa, $status, $idpessoa, $idcontaitem, $idobjetoorigem, $tipoobjetoorigem, $tipo, $visivel, $idformapagamento, $parcela, $parcelas, $datapagto, $valor, $usuario)
	{
		return ContaPagarController::inserirValoresContaPagarItem($idempresa, $status, $idpessoa, $idcontaitem, $idobjetoorigem, $tipoobjetoorigem, $tipo, $visivel, $idformapagamento, $parcela, $parcelas, $datapagto, $valor, $usuario);
	}

	public static function inserirParcelaSemIdContaItem($idempresa, $status, $idpessoa, $idobjetoorigem, $tipoobjetoorigem, $tipo, $visivel, $idformapagamento, $parcela, $parcelas, $datapagto, $valor, $usuario)
	{
		return ContaPagarController::inserirParcelaSemIdContaItem($idempresa, $status, $idpessoa, $idobjetoorigem, $tipoobjetoorigem, $tipo, $visivel, $idformapagamento, $parcela, $parcelas, $datapagto, $valor, $usuario);
	}

	public static function inserirContaPagarComIdContaItem($idempresa, $idcontaitem, $idagencia, $idpessoa, $tipoobjeto, $idobjeto, $parcela, $parcelas, $valor, $datapagto, $datareceb, $status, $idfluxostatus, $idformapagamento, $tipo, $visivel, $intervalo, $usuario)
	{
		return ContaPagarController::inserirContaPagarComIdContaItem($idempresa, $idcontaitem, $idagencia, $idpessoa, $tipoobjeto, $idobjeto, $parcela, $parcelas, $valor, $datapagto, $datareceb, $status, $idfluxostatus, $idformapagamento, $tipo, $visivel, $intervalo, $usuario);
	}

	public static function inserirContaPagarSemIdContaItem($idempresa, $idagencia, $idpessoa, $tipoobjeto, $idobjeto, $parcela, $parcelas, $valor, $datapagto, $datareceb, $status, $idfluxostatus, $idformapagamento, $tipo, $visivel, $intervalo, $usuario)
	{
		return ContaPagarController::inserirContaPagarSemIdContaItem($idempresa, $idagencia, $idpessoa, $tipoobjeto, $idobjeto, $parcela, $parcelas, $valor, $datapagto, $datareceb, $status, $idfluxostatus, $idformapagamento, $tipo, $visivel, $intervalo, $usuario);
	}

	public static function apagarParcelasExistentes($tipoobjeto, $idobjeto)
	{
		return ContaPagarController::apagarParcelasExistentes($tipoobjeto, $idobjeto);
	}

	public static function buscarQuantidadeParcelasPorStatusTipoObjeto($tipoobjeto, $idobjeto, $status)
	{
		return ContaPagarController::buscarQuantidadeParcelasPorStatusTipoObjeto($tipoobjeto, $idobjeto, $status);
	}

	public static function buscarQuantidadeBoletosRemessaItem($tipoobjeto, $idobjeto)
	{
		return ContaPagarController::buscarQuantidadeBoletosRemessaItem($tipoobjeto, $idobjeto);
	}

	public static function buscarObjetoVinculoPorTipoObjetoTipoObjetoVinc($tipoobjeto, $tipoobjetovinc, $idcotacao)
	{
		$resultsIdobjetoVinc = SQL::ini(ObjetoVinculoQuery::buscarObjetoVinculoPorTipoObjetoTipoObjetoVinc(), [
			"tipoobjeto" => $tipoobjeto,
			"tipoobjetovinc" => $tipoobjetovinc,
			"idobjeto" => $idcotacao
		])::exec();

		if ($resultsIdobjetoVinc->error()) {
			parent::error(__CLASS__, __FUNCTION__, $resultsIdobjetoVinc->errorMessage());
			return [];
		} else {
			return $resultsIdobjetoVinc->data;
		}
	}

	public static function inserirObjetoVinculo($idobjeto, $tipoobjeto, $idobjetovinc, $tipoobjetovinc, $usuario)
	{
		$inserindoObjetoVinculo = SQL::ini(ObjetoVinculoQuery::inserirObjetoVinculo(), [
			'idobjeto' => $idobjeto,
			'tipoobjeto' => $tipoobjeto,
			'idobjetovinc' => $idobjetovinc,
			'tipoobjetovinc' => $tipoobjetovinc,
			'criadopor' => $usuario,
			'criadoem' => SYSDATE(),
			'alteradopor' => $usuario,
			'alteradoem' => SYSDATE(),
		])::exec();

		if ($inserindoObjetoVinculo->error()) {
			parent::error(__CLASS__, __FUNCTION__, $inserindoObjetoVinculo->errorMessage());
		}
	}

	public static function buscarGrupoESTipoObjetoSoliPor($idobjetosolipor, $tipoobjetosolipor, $idcontaitem)
	{
		return NfController::buscarGrupoESTipoObjetoSoliPor($idobjetosolipor, $tipoobjetosolipor, $idcontaitem);
	}

	public static function apagarObjetoVinculoIdObjetoIdObjetoVinc($idobjeto, $tipoobjeto, $idobjetovinc, $tipoobjetovinc)
	{
		$inserindoObjetoVinculo = SQL::ini(ObjetoVinculoQuery::apagarObjetoVinculoIdObjetoIdObjetoVinc(), [
			'idobjeto' => $idobjeto,
			'tipoobjeto' => $tipoobjeto,
			'idobjetovinc' => $idobjetovinc,
			'tipoobjetovinc' => $tipoobjetovinc
		])::exec();

		if ($inserindoObjetoVinculo->error()) {
			parent::error(__CLASS__, __FUNCTION__, $inserindoObjetoVinculo->errorMessage());
		}
	}

	public static function buscarSolcomItemPorIdSolcomItem($idsolcomitem)
	{
		return SolcomController::buscarSolcomItemPorIdSolcomItem($idsolcomitem);
	}

	public static function atualizarQtdNfItem($qtd, $idnfitem)
	{
		return NfController::atualizarQtdNfItem($qtd, $idnfitem);
	}

	public static function inserirNfItem($idnf, $tiponf, $idprodservforn, $idempresa, $un, $qtd, $nfe, $idprodserv, $idtipoprodserv, $idcontaitem, $usuario)
	{
		return NfController::inserirNfItem($idnf, $tiponf, $idprodservforn, $idempresa, $un, $qtd, $nfe, $idprodserv, $idtipoprodserv, $idcontaitem, $usuario);
	}

	public static function buscarItensPorIdNf($qtd, $idnf)
	{
		return NfController::buscarItensPorIdNf($qtd, $idnf);
	}

	public static function buscarProdutoPorNfItem($idnf)
	{
		$prodserv = SQL::ini(NfItemQuery::buscarProdutoPorNfItem(), [
			"idnf" => $idnf
		])::exec();

		if ($prodserv->error()) {
			parent::error(__CLASS__, __FUNCTION__, $prodserv->errorMessage());
			return [];
		} else {
			return $prodserv->data;
		}
	}

	public static function buscarConversaoFornecedorPorCnpj($cnpf, $idprodserv, $unforn)
	{
		$prodserv = SQL::ini(ProdservFornQuery::buscarConversaoFornecedorPorCnpj(), [
			"cnpf" => $cnpf,
			"idprodserv" => $idprodserv,
			"unforn" => $unforn,
		])::exec();

		if ($prodserv->error()) {
			parent::error(__CLASS__, __FUNCTION__, $prodserv->errorMessage());
			return [];
		} else {
			return $prodserv->data;
		}
	}

	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE -----
	public static function listarFormaPagamentoAtivo()
	{
		$orderBy = " ORDER BY descricao ASC";
		$formaPagamento = FormaPagamentoController::listarFormaPagamentoAtivo($orderBy);
		foreach ($formaPagamento as $_formaPagamento) {
			$listarFormaPagamento[$_formaPagamento['idformapagamento']]['descricao'] = $_formaPagamento['descricao'];
		}

		return $listarFormaPagamento;
	}

	public static function listarFormaPagamentoAtivoDistinct()
	{
		$formaPagamento = FormaPagamentoController::listarFormaPagamentoAtivoDistinct();
		foreach ($formaPagamento as $_formaPagamento) {
			$listarFormaPagamento[$_formaPagamento['idformapagamento']]['descricao'] = $_formaPagamento['descricao'];
		}

		return $listarFormaPagamento;
	}

	public static function listarPessoaPorIdTipoPessoa($idtipopessoa)
	{
		$pessoaPorTipo =  PessoaController::listarPessoaPorIdTipoPessoa($idtipopessoa);
		foreach ($pessoaPorTipo as $_pessoaPorTipo) {
			$listarFormaPagamento[$_pessoaPorTipo['idpessoa']]['nome'] = $_pessoaPorTipo['nome'];
			$listarFormaPagamento[$_pessoaPorTipo['idpessoa']]['nomecurto'] = $_pessoaPorTipo['nomecurto'];
		}
		return $listarFormaPagamento;
	}

	public static function listarFornecedorPessoaPorIdTipoPessoa($idtipopessoa)
	{
		$arrTransportadora = [];
		$pessoaPorTipo =  PessoaController::listarPessoaPorIdTipoPessoa($idtipopessoa);
		$i = 0;
		foreach ($pessoaPorTipo as $_pessoaPorTipo) {
			$arrTransportadora[$_pessoaPorTipo['idpessoa']] = $_pessoaPorTipo['nome'];
			$i++;
		}
		return $arrTransportadora;
	}

	public static function listarUnidadeVolume()
	{
		$resultsUnidadeVolume = SQL::ini(UnidadeVolumeQuery::buscarUnidadeVolume())::exec();

		if ($resultsUnidadeVolume->error()) {
			parent::error(__CLASS__, __FUNCTION__, $resultsUnidadeVolume->errorMessage());
			return [];
		} else {
			$arrUnidadeVolume = [];
			foreach ($resultsUnidadeVolume->data as $_dadosUnidadeVolume) {
				$arrUnidadeVolume[$_dadosUnidadeVolume['un']] = $_dadosUnidadeVolume['un'];
			}

			return $arrUnidadeVolume;
		}
	}

	public static function buscarIdobjetoVincConcat($idcotacao)
	{
		$resultsIdobjetoVincConcat = SQL::ini(ObjetoVinculoQuery::buscarIdobjetoVincConcat(), [
			"tipoobjeto" => 'cotacao',
			"tipoobjetovinc" => 'contaitemtipoprodserv',
			"idobjeto" => $idcotacao
		])::exec();

		if ($resultsIdobjetoVincConcat->error()) {
			parent::error(__CLASS__, __FUNCTION__, $resultsIdobjetoVincConcat->errorMessage());
			return "";
		} else {
			foreach ($resultsIdobjetoVincConcat->data as $_dadosIdobjetoVincConcat) {
				return $_dadosIdobjetoVincConcat['idobjetovinc'];
			}
		}
	}

	public static function listarCotacaoParaMigrar($arrIdtipoProdserv, $idcotacao, $idempresa)
	{
		$arrCotacaoParaMigrar = [];

		foreach ($arrIdtipoProdserv as $key => $idtipoProdserv) {
			$idContaItem = array_unique($idtipoProdserv['idcontaitem']);
			$idTipoProdserv = array_unique($idtipoProdserv['idtipoprodserv']);

			$inIdContaItem = [];
			foreach ($idContaItem as $_idContaItem) {
				array_push($inIdContaItem, $_idContaItem);
			}

			$inIdtipoProdserv = [];
			foreach ($idTipoProdserv as $_idTipoProdserv) {
				array_push($inIdtipoProdserv, $_idTipoProdserv);
			}

			if (array_search(false, $inIdContaItem, false) === false && array_search(false, $inIdtipoProdserv, false) === false) {
				$resultsAlterarCotacao = SQL::ini(CotacaoQuery::listarCotacao(), [
					"idcontaitens" => implode(",", $inIdContaItem),
					"idobjeto" => $idcotacao,
					"idempresa" => $idempresa,
					"idTipoItens" => implode(",", $inIdtipoProdserv)
				])::exec();

				if ($resultsAlterarCotacao->error()) {
					parent::error(__CLASS__, __FUNCTION__, $resultsAlterarCotacao->errorMessage());
				} else {
					foreach ($resultsAlterarCotacao->data as $_dadosCotacaoParaAlterar) {
						$concatcontaitemtipoprodserv = array_unique(explode(",", $_dadosCotacaoParaAlterar['concatcontaitemtipoprodserv']));
						$countInterTipoProdserv = count(array_intersect($concatcontaitemtipoprodserv, $inIdtipoProdserv));
						$countTipoProdserv = count($inIdtipoProdserv);

						$concatcontaitem = array_unique(explode(",", $_dadosCotacaoParaAlterar['concatcontaitem']));
						$countInterContaItem = count(array_intersect($concatcontaitem, $idContaItem));
						$countContaItem = count($inIdContaItem);

						if ($countTipoProdserv ==  $countInterTipoProdserv && $countContaItem == $countInterContaItem) {
							$arrCotacaoParaMigrar[$key][$_dadosCotacaoParaAlterar['idcotacao']] = $_dadosCotacaoParaAlterar['titulo'];
						}
					}
				}
			}
		}

		return $arrCotacaoParaMigrar;
	}

	public static function listarContaItemAtivoShare()
	{
		$resultsContaItemAtivoShare = ContaItemController::buscarContaItemAtivoShare();

		$arrCotacaoParaAlterar = [];
		foreach ($resultsContaItemAtivoShare as $_dadosContaItemAtivoShare) {
			$arrCotacaoParaAlterar[$_dadosContaItemAtivoShare['idcontaitem']] = $_dadosContaItemAtivoShare['contaitem'];
		}

		return $arrCotacaoParaAlterar;
	}

	public static function buscarDadosFornecedorNf($idnf)
	{
		$results = SQL::ini(NfQuery::buscarDadosFornecedorNf(), [
			"idnf" => $idnf
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->data[0];
		}
	}

	public static function buscarQuantidadeAuditoriaPorObjetoColunaStatus($idnf)
	{
		$results = SQL::ini(AuditoriaQuery::buscarQuantidadeAuditoriaPorObjetoColunaStatus(), [
			"objeto" => 'nf',
			"idobjeto" => $idnf,
			"idempresa" => getidempresa('idempresa', '_auditoria'),
			"coluna" => 'status',
			"valor" => 'RESPONDIDO'
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->data[0];
		}
	}

	public static function buscarEnderecoPessoa($idpessoa)
	{
		return EnderecoController::buscarEnderecoPessoa($idpessoa);
	}

	public static function buscarItensNfPorIdNf($idnf)
	{
		return NfController::buscarItensNfPorIdNf($idnf);
	}

	public static function buscarAnexosPorTipoObjetoIdObjeto($tipoObjeto, $idObjeto)
	{
		$result = SQL::ini(ArquivoQuery::buscarAnexosPorTipoObjetoIdObjeto(), [
			'tipoobjeto' => $tipoObjeto,
			'idobjeto' => $idObjeto
		])::exec();

		if ($result->error()) {
			parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
			return "";
		} else {
			return $result->data;
		}
	}

	public static function buscarCotacaoDisponivelPorGrupoEsTipoItem($idprodserv)
	{
		$result = SQL::ini(CotacaoQuery::buscarCotacaoDisponivelPorGrupoEsTipoItem(), [
			'idprodserv' => $idprodserv
		])::exec();

		if ($result->error()) {
			parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
			return "";
		} else {
			return $result->data;
		}
	}

	public static function listarNfitemsxmlPorIdprodserv($idprodserv)
	{
		$results = SQL::ini(CotacaoQuery::listarNfitemsxmlPorIdprodserv(), [
			"idprodserv" => $idprodserv
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->data;
		}
	}

	//----- AUTOCOMPLETE -----

	// ----- Variáveis de apoio -----
	public static $justificativaAlteraPrazo =  array(
		'' => '',
		'NOVACOTACAO' => 'Inserida Nova Cotação',
		'ATRASORESPOSTAFORNECEDOR' => 'Fornecedor Demorou Responder',
		'ATRASO' => 'Atraso na Entrega',
		'PEDIDOFORNECEDOR' => 'A Pedido do Fornecedor',
		'OUTROS' => 'Outros'
	);

	public static $exibirVisualizacao = array('1' => 'Por Produto', '2' => 'Por Fornecedor');

	public static $statusCotacao = array(
		'INICIO' => 'Aberto',
		'ENVIADO' => 'Enviado',
		'RESPONDIDO' => 'Respondido',
		'AUTORIZADA' => 'Em Aprovação',
		'AUTORIZADO' => 'Autorização Diretoria',
		'PREVISAO' => 'Programado',
		'APROVADO' => 'Aprovado',
		'REPROVADO' => 'Reprovado',
		'CANCELADO' => 'Cancelado'
	);

	public static $tipoNf = array(
		'C' => 'Danfe',
		'S' => 'Serviço',
		'M' => 'Guia/Cupom',
		'B' => 'Recibo'
	);

	public static $tipoFrete = array(
		'0' => 'CIF',
		'1' => 'FOB',
		'2' => 'TER',
		'3' => 'TP REM',
		'4' => 'TP DEST',
		'9' => 'SEM FRETE'
	);

	public static $tituloFrete = "&#13;0=Contratação do Frete por Conta do Remetente (CIF);&#13;1=Contratação do Frete por Conta do Destinatário (FOB);&#13;2=Contratação do Frete por Conta de Terceiros (TER);&#13;3=Transporte Próprio por conta do Remetente(TP REM);&#13;4=Transporte Próprio por conta do Destinatário (TP DEST);&#13;9=Sem Ocorrência de Transporte (SEM FRETE);";

	// ----- Variáveis de apoio -----
}
