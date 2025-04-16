<?
//Valida para exclusão do XML - Lidiane (19/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=327401
$iu = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'] ? 'u' : 'i';
$idnf = $_POST["_retxml_u_nf_idnf"];
$xmlret = $_POST["_retxml_u_nf_xmlret"];
$envionfe = $_POST["_retxml_u_nf_envionfe"];
$prodservdescr = $_POST['prodservdescr'];
$moedaapp = $_POST['moedaapp'];
$valorapp = $_POST['valorapp'];
$dtemissaoapp = $_POST['dtemissaoapp'];

$_idempresa = isset($_GET["_idempresa"]) ? $_GET["_idempresa"] : $_SESSION["SESSAO"]["IDEMPRESA"];

if (!empty($idnf) && !empty($envionfe)) {
	NfEntradaController::atualizarNfXmlRetEnvioNfe(NULL, 'PENDENTE', $idnf);
	NfEntradaController::apagarNfItemXmlPorIdNf($idnf);
	NfEntradaController::atualizarNfXmlVinculo($idnf);
}

if (!empty($_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'])) {
	$idnf = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'];
	//Salva a data de dtemissao e Chave de Acesso da Nota Fiscal após subir o XML (LTM - 04-08-2020)
	$listarXmlret = NfEntradaController::buscarNfPorTipoNfEIdNf('T', $idnf);
	foreach ($listarXmlret as $xmlret) {
		if (!empty($xmlret["xmlret"])) {
			$xml = simplexml_load_string($xmlret["xmlret"]);
			$chCTe = $xml->protCTe->infProt->chCTe;

			if (empty($chCTe)) {
				$chave = $xml->protNFe->infProt->chNFe;
				$data = $xml->NFe->infNFe->ide->dhEmi;
			} else {
				$chave = $chCTe;
				$data = $xml->CTe->infCte->ide->dhEmi;
			}

			$data = str_replace("T", " ", substr($data, 0, 19));
			NfEntradaController::atualizarNfIdnfeDtemissaoPorIdnf($chave, $data, $idnf);
		}
	}
}

if (
	!empty($_SESSION['arrpostbuffer']['x9']['i']['contapagar']['tipo'])
	&& !empty($_SESSION['arrpostbuffer']['x9']['i']['contapagar']['datapagto'])
	&& $_SESSION['arrpostbuffer']['x9']['i']['contapagar']['tipo'] == 'D'
) {
	$datapagto = date('Y-m-d', strtotime(str_replace('/', '-', $_SESSION['arrpostbuffer']['x9']['i']['contapagar']['datapagto'])));
	$lastinsert = $_SESSION["arrscriptsql"]['x9']['contapagar']["insertid"];

	// GVT - 23/07/2021 - @471938 lançamentos com diferença de 28 dias gera assinatura p/ o Fábio
	// Não há a necessidade de verificar se já existe assinatura pendente, pois aqui sempre são criadas novas contas a pagar
	$d1 = strtotime(date("Y-m-d"));
	$d2 = strtotime($datapagto);
	$diff = ($d2 - $d1) / 60 / 60 / 24;
	if ($diff < 28) {
		$arrayInserCarimbo = [
			"idempresa" => $_idempresa,
			"idpessoa" => 798,
			"idobjeto" => $lastinsert,
			"tipoobjeto" => 'contapagar',
			"idobjetoext" => '903',
			"tipoobjetoext" => 'idfluxostatus',
			"status" => 'PENDENTE',
			"criadopor" => 'sislaudo',
			"criadoem" => date("Y-m-d H:i:s"),
			"alteradopor" => 'sislaudo',
			"alteradoem" => date("Y-m-d H:i:s")
		];
		$idnfitemNew = NfEntradaController::inserirCarimbo($arrayInserCarimbo);
	}
}

if (!empty($idnf)) {
	$rwNf = NfEntradaController::buscarNfPorIdnf($idnf);

	if ($rwNf['geracontapagar'] == 'Y') {
		$_POST["_1_u_nf_idformapagamento"] = $rwNf['idformapagamento'];
	}

	$_POST["_1_u_nf_idunidade"] = $rwNf['idunidade'];
	$_POST["_1_u_nf_parcelas"] = $rwNf['parcelas'];
	$_POST["_1_u_nf_total"] = $rwNf['total'];
	$_POST["_1_u_nf_tipocontapagar"] = $rwNf['tipocontapagar'];
	$_POST["_1_u_nf_geracontapagar"] = $rwNf['geracontapagar'];
	$_POST['_1_u_nf_comissao'] = $rwNf['comissao'];
	$_POST['_1_u_nf_idpessoafat'] = $rwNf['idpessoafat'];
	$_POST['_1_u_nf_idpessoa'] = $rwNf['idpessoa'];
	$_POST['_1_u_nf_entrega'] = $rwNf['entrega'];
	$_POST['_1_u_nf_idnfe'] = $rwNf['idnfe'];
	$_POST["_1_u_nf_tiponf"] = $rwNf['tiponf'];
	$_POST["_1_u_nf_dtemissao"] = $rwNf['dtemissao'];
	$_POST['_1_u_nf_status'] = $rwNf['status'];
}

if ($iu == 'i' && !empty($moedaapp) && !empty($valorapp)) {
	$idpessoa = $_SESSION['arrpostbuffer']['1']['i']['nf']['idpessoa'];
	$contaItemProdserv = CompraAppController::buscarTipoProdservPorApp($prodservdescr, 'idtipoprodserv');
	$newidnf = $_SESSION["arrscriptsql"]['1']['nf']["insertid"];
	$data = date("d-m-Y", strtotime($dtemissaoapp));

	$nome = empty(strtoupper(traduzid('pessoa', 'idpessoa', 'nomecurto', $_SESSION["SESSAO"]["IDPESSOA"])))
		? strtoupper(traduzid('pessoa', 'idpessoa', 'nome', $_SESSION["SESSAO"]["IDPESSOA"]))
		: strtoupper(traduzid('pessoa', 'idpessoa', 'nomecurto', $_SESSION["SESSAO"]["IDPESSOA"]));

	$arrayInsertNfItem = [
		"idnf" => $newidnf,
		"tiponf" => 'M',
		"qtd" => 1,
		"idtipoprodserv" => $contaItemProdserv['idtipoprodserv'],
		"idcontaitem" => $contaItemProdserv['idcontaitem'],
		"prodservdescr" => $contaItemProdserv['tipoprodserv'] . " - " . $nome . " - " . $data,
		"moeda" => $moedaapp,
		"vlritem" => tratadouble($valorapp),
		"basecalc" => tratadouble($valorapp),
		"total" => tratadouble($valorapp),
		"idempresa" => $_idempresa,
		"nfe" => 'Y',
		"usuario" => $_SESSION["SESSAO"]["USUARIO"]
	];
	$idnfitemNew = CompraAppController::inserirNfItemAPP($arrayInsertNfItem);

	$idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'INICIO');
	FluxoController::inserirFluxoStatusHist('nfentrada', $newidnf, $idfluxostatus, 'PENDENTE');
	$formaPagamento = CompraAppController::buscarFormaPagamentoPorIdPessoa($_SESSION["SESSAO"]["IDPESSOA"], $_idempresa);
	if (NfEntradaController::buscarNfItemIdPessoaNuloNfe($newidnf, 'Y') > 0) { //so vai gerar para itens com pessoas vinculadas
		cnf::geraRateio($newidnf);
		$arrInsrateio[1]['idunidade'] = $formaPagamento['idunidade'] . ",unidade";
		$arrInsrateio[1]['idpessoa'] = $idpessoa;
		$arrInsrateio[1]['valor'] = 100;
		CompraAppController::inserirRateioRateioItemRateioItemDest($idnfitemNew, $arrInsrateio, $_idempresa);
	}
}

$idnfFl = $_POST['_fl_u_nf_idnf'];
if (!empty($idnfFl) && $_POST['_atualizar_fluxo'] == 'Y') {
	$contaItemProdserv = CompraAppController::buscarTipoProdservPorApp($prodservdescr, 'idtipoprodserv');
	$rowFluxo = FluxoController::getFluxoStatusHist('nfentrada', 'idnf', $idnfFl, $_POST['_status_fluxo']);
	$retorno = FluxoController::alterarStatus('nfentrada', 'idnf', $idnfFl, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], null, 0, $rowFluxo['idfluxostatus'], $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);

	if ($retorno['resposta'] == 0) {
		echo $retorno['formato'];
		die();
	}

	if ($rowFluxo['statustipo'] == 'CONCLUIDO') {
		//Inserir Conta Pagar
		$formaPamagento = CompraAppController::buscarFormaPagamentoPorIdPessoa($_SESSION["SESSAO"]["IDPESSOA"], $_idempresa);
		//LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
		$idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'PENDENTE');

		$prazo = explode(" ", $dtemissaoapp);
		$formato = 'd/m/Y';
		$data = !empty($prazo[0]) ? DateTime::createFromFormat($formato, $prazo[0]) : "";
		$prazoEmissao = !empty($prazo[0]) ? $data->format('Y-m-d') : "";
		$dtEmissao = date('Y-m-d', strtotime("+28 DAY", strtotime($prazoEmissao)));

		$arrinsnfcp[1]['idnf'] = $idnfFl;
		$arrinsnfcp[1]['parcela'] = 1;
		$arrinsnfcp[1]['idformapagamento'] = $formaPamagento['idformapagamento'];
		$arrinsnfcp[1]['proporcao'] = 100;
		$arrinsnfcp[1]['datareceb'] = $dtEmissao;
		cnf::inseredb($arrinsnfcp, 'nfconfpagar');

		$arrayInsertContaPagarItem = [
			"idempresa" => $_idempresa,
			"status" => "PENDENTE",
			"idpessoa" => $contaItemProdserv['idpessoa'],
			"idcontapagar" => $contapagar["idcontapagar"],
			"idcontaitem" => 'NULL',
			"idobjetoorigem" => $idnfFl,
			"tipoobjetoorigem" => 'nf',
			"tipo" => "D",
			"visivel" => 'S',
			"idformapagamento" => $formaPamagento['idformapagamento'],
			"parcela" => 1,
			"parcelas" => 1,
			"datapagto" => "'$dtEmissao'",
			"valor" => tratadouble($valorapp),
			"usuario" => $_SESSION["SESSAO"]["USUARIO"]
		];
		NfEntradaController::inserirValoresContaPagarItem($arrayInsertContaPagarItem);
		NfEntradaController::atualizarContaPagarItemSemRateio($_idempresa);
	}
}


//Atualiza o valor do Lote do Item
foreach ($_POST as $k => $v) {
	$indice = explode('_', $k);
	if (!empty($indice[1]) && $indice[4] == 'idnfitem') {
		$i = $indice[1];
		if (
			$_POST["_" . $i . "_qtd_old"] <> $_POST["_" . $i . "_u_nfitem_qtd"] || $_POST["_" . $i . "_vlritem_old"] <> $_POST["_" . $i . "_u_nfitem_vlritem"]
			|| $_POST["_" . $i . "_des_old"] <> $_POST["_" . $i . "_u_nfitem_des"] || $_POST["_" . $i . "_impostoimportacao_old"] <> $_POST["_" . $i . "_u_nfitem_impostoimportacao"]
			|| $_POST["_" . $i . "_valipi_old"] <> $_POST["_" . $i . "_u_nfitem_valipi"] || $_POST["_" . $i . "_pis_old"] <> $_POST["_" . $i . "_u_nfitem_pis"]
			|| $_POST["_" . $i . "_cofins_old"] <> $_POST["_" . $i . "_u_nfitem_cofins"]
		) {

			$_dadosLote = NfEntradaController::buscarLoteNfItemPorIndNf(0, $_POST['_1_u_nf_idnf'], $_POST["_" . $i . "_u_nfitem_idprodserv"]);
			foreach ($_dadosLote as $_lote) {
				$impostoImportacao = NfController::buscarValorImpostoTotalItem($_lote['idprodserv'], 'nf', $_POST['_1_u_nf_idnf']);
				if ($impostoImportacao['internacional'] == 'Y') {
					$novoValorLote = $impostoImportacao['vlritem'] + $impostoImportacao['valorcomimpostoitem'] + $impostoImportacao['valorcomimposto'];
				} else {
					$novoValorLote = NfController::buscarValorItem($_POST['_1_u_nf_idnf'], $_lote['idprodserv']);
				}

				NfEntradaController::atualizarValorLote($novoValorLote, $_lote['idlote']);
			}
		}

		if ($_POST["_" . $i . "_qtd_old"] <> $_POST["_" . $i . "_u_nfitem_qtd"]) {
			NfEntradaController::atualizarQtdLote($_POST["_" . $i . "_u_nfitem_idnfitem"], $_POST["_" . $i . "_u_nfitem_qtd"]);
		}

		$validarValoresNulos = [0, 0.00, '0', '0.00'];
		if (in_array($_POST["_" . $i . "_u_nfitem_qtd"], $validarValoresNulos) && $_POST["_" . $i . "_qtd_old"] <> $_POST["_" . $i . "_u_nfitem_qtd"]) {
			$lotes = NfEntradaController::buscarLotePorIdNfitem($_POST["_" . $i . "_u_nfitem_idnfitem"]);
			foreach ($lotes as $_lote) {
				$rowFluxo = FluxoController::getFluxoStatusHist('lotealmoxarifado', 'idlote', $_lote['idlote'], 'CANCELADO');
				FluxoController::alterarStatus('lotealmoxarifado', 'idlote', $_lote['idlote'], $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], null, 0, $rowFluxo['idfluxostatus'], $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);
			}
		}
	}
}

if (
	$_GET['_acao'] == 'u' && (($_POST['_1_u_nf_freteimpnacional'] <> $_POST['_1_impostoimportacao_old'] && $_POST['_1_u_nf_freteimpnacional'] != '0,0000')
		|| ($_POST['_1_u_nf_freteimpnacional'] <> $_POST['_1_freteimpnacional_old']) && $_POST['_1_u_nf_freteimpnacional'] != '0,0000')
	|| ($_POST['_1_u_nf_freteimpinternacional'] <> $_POST['_1_freteimpinternacional_old']  && $_POST['_1_u_nf_freteimpnacional'] != '0,0000')
	|| ($_POST['_1_u_nf_aeroportuaria'] <> $_POST['_1_aeroportuaria_old'] && $_POST['_1_u_nf_freteimpnacional'] != '0,0000')
	|| ($_POST['_1_u_nf_honorarioimportacao'] <> $_POST['_1_honorarioimportacao_old']  && $_POST['_1_u_nf_freteimpnacional'] != '0,0000')
	|| ($_POST['_1_u_nf_icms'] <> $_POST['_1_icms_old']  && $_POST['_1_u_nf_freteimpnacional'] != '0,0000')
	|| ($_POST['_1_u_nf_siscomex'] <> $_POST['_1_siscomex_old'] && $_POST['_1_u_nf_freteimpnacional'] != '0,0000')
) {

	$_dadosLote = NfEntradaController::buscarLoteNfItemPorIndNf(0, $_POST['_1_u_nf_idnf']);
	foreach ($_dadosLote as $_lote) {
		$impostoImportacao = NfController::buscarValorImpostoTotalItem($_lote['idprodserv'], 'nf', $_POST['_1_u_nf_idnf']);
		if ($impostoImportacao['internacional'] == 'Y') {
			$novoValorLote = $impostoImportacao['vlritem'] + $impostoImportacao['valorcomimpostoitem'] + $impostoImportacao['valorcomimposto'];
		} else {
			$novoValorLote = NfController::buscarValorItem($_POST['_1_u_nf_idnf'], $_lote['idprodserv']);
		}

		NfEntradaController::atualizarValorLote($novoValorLote, $_lote['idlote']);
	}
}

$idnf = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'];
$arquivo = NfEntradaController::buscarAnexo($idnf, "'nf'", 'XMLNFE');
if (!empty($_SESSION['arrpostbuffer']['1']['u']['nf']['idnfe']) && count($arquivo) == 0) {
	$dadosXml = NfEntradaController::buscarXMLParaEnvioDeEmail($idnf);

	if ($dadosXml['xmlret']) {

		$_xml = str_replace('&', '&amp;', $dadosXml['xmlret']);

		$xml = new SimpleXMLElement($_xml);
		$nomeArquivo = $idnf . $dadosXml['nnfe'] . '.xml';
		$caminhoArquivo = '../upload/' . $nomeArquivo;
		$xml->asXML($caminhoArquivo);
		if (file_exists($caminhoArquivo)) {
			// Pegar o tamanho do arquivo
			$tamanhoArquivo = filesize($caminhoArquivo);
			$tamanhoArquivoKb = $tamanhoArquivo / 0124;

			// Inserir o nome e tamanho do arquivo em outra tabela no banco de dados
			$arrayArquivo = [
				"idempresa" => cb::idempresa(),
				"tipoarquivo" => 'XMLNFE',
				"nomeoriginal" => $nomeArquivo,
				"nome" => $nomeArquivo,
				"caminho" => $caminhoArquivo,
				"tamanho" => number_format($tamanhoArquivoKb, 2),
				"tamanhobytes" => $tamanhoArquivo,
				"imagempadrao" => 'N',
				"idpessoa" => $_SESSION["SESSAO"]["IDPESSOA"],
				"idobjeto" => $idnf,
				"tipoobjeto" => 'nf'
			];
			NfEntradaController::inserirArquivo($arrayArquivo);
		}
	}
}

include_once("saveposchange__rateioitemdest.php");
include_once("saveposchange__pedido.php");
