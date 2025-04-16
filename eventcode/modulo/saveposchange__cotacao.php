<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
require_once("../api/nf/index.php");

cnf::$idempresa = cb::idempresa(); //isset($_GET["_idempresa"])?$_GET["_idempresa"]:$_SESSION["SESSAO"]["IDEMPRESA"];

$_idempresa =cb::idempresa(); //isset($_GET["_idempresa"])?$_GET["_idempresa"]:$_SESSION["SESSAO"]["IDEMPRESA"];
$_idcotacao = $_SESSION['arrpostbuffer']['1']['u']['cotacao']['idcotacao'];
$status = $_POST['_x_u_nf_status'];
$idnotafiscal = $_POST['_x_u_nf_idnf'];
$idformapagamento = $_POST['_x_u_nf_idformapagamento'];
$gerarpdf = $_POST['gerarpdf'];
$tipogerarpdf = $_POST['tipogerarpdf'];
$idempresa = cb::idempresa();

//gerar contapagar na nf
if(($status == 'APROVADO' or $status == 'PREVISAO') AND !empty($idnotafiscal) AND !empty($idformapagamento))
{
	//gera rateio quando tem pessoa no nfitem
	$qtdpes = CotacaoController::buscarRateioNfItem($idnotafiscal, 'nfitem');

	if($qtdpes > 0){//so vai gerar para itens com pessoas vinculadas
		cnf::geraRateioDanfe($idnotafiscal);
	}

    $qtParcelas = CotacaoController::buscarQuantidadeParcelasPorStatusTipoObjeto('nf', $idnotafiscal, 'QUITADO');
    $qtdlinhasbol = CotacaoController::buscarQuantidadeBoletosRemessaItem('nf', $idnotafiscal);
    if ($qtParcelas == 0 && $qtdlinhasbol == 0)
	{
		//deleta as parcelas existentes.
    	CotacaoController::apagarParcelasExistentes('nf', $idnotafiscal);
		$nf = CotacaoController::buscarInformacoesCotacao($idnotafiscal);
	
		//Insere novas parcelas
		$valorparcela = $nf['total']/$nf['parcelas'];	

		if(empty(trim($nf['diasentrada']))){
			$nf['diasentrada'] = '0';		
		}

		$difdias = 0;
		if(!empty($nf['emissao']))
		{
			for ($index = 1; $index <= $nf['parcelas']; $index++)
			{
				$strintervalo = 'DAY';
				
				if($index == 1)
				{
					$valintervalo = $nf['diasentrada'];
					$diareceb = $nf['diasentrada'] + $difdias;
					$vencimentocalc = "DATE(DATE_ADD('".$nf['emissao']."', INTERVAL ".$nf['diasentrada']." ".$strintervalo."))";
					$recebcalc = "DATE(DATE_ADD('".$nf['emissao']."', INTERVAL ".$diareceb." ".$strintervalo."))";
					
				}else{
					$valintervalo = $valintervalo + $nf['intervalo'];
					$diareceb = $valintervalo + $difdias;
					$vencimentocalc = "DATE(DATE_ADD('".$nf['emissao']."', INTERVAL ".$valintervalo." ".$strintervalo."))";
					$recebcalc = "DATE(DATE_ADD('".$nf['emissao']."', INTERVAL ".$diareceb." ".$strintervalo."))";
				}
							
				//BUSCAR CONFIGURAÇÕES DA FORMA DE PAGAMENTO
				$formapagamento = CotacaoController::buscarConfiguracoesFormaPagamento($idformapagamento);
				cnf::$idempresa = $formapagamento['idempresa'];
		
				if($formapagamento['agrupado'] == 'Y')
				{
					//se for agrupado
					if(!empty($nf['idcontaitem'])){
						CotacaoController::inserirValoresContaPagarItem($_idempresa,'PENDENTE', $nf['idpessoa'], $nf['idcontaitem'], $idnotafiscal, 'nf', 'D', 'S', $idformapagamento, $index, $nf['parcelas'], $recebcalc, $valorparcela, $_SESSION["SESSAO"]["USUARIO"]);
					}else{
						CotacaoController::inserirParcelaSemIdContaItem($_idempresa,'PENDENTE', $nf['idpessoa'], $idnotafiscal, 'nf', 'D', 'S', $idformapagamento, $index, $nf['parcelas'], $recebcalc, $valorparcela, $_SESSION["SESSAO"]["USUARIO"]);
					}
								
				}else{
					//LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
					$idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'PENDENTE');

					if(!empty($nf['idcontaitem'])){		
						$idcontapagar = CotacaoController::inserirContaPagarComIdContaItem($_idempresa, $nf['idcontaitem'] , 3, $nf['idpessoa'], 'nf', $idnotafiscal, $index, $nf['parcelas'] , $valorparcela, $vencimentocalc, $recebcalc, 'PENDENTE', $idfluxostatus, $idformapagamento, 'D', 'S', $nf['intervalo'], $_SESSION["SESSAO"]["USUARIO"]);
					}else{
						$idcontapagar = CotacaoController::inserirContaPagarSemIdContaItem($_idempresa, 3, $nf['idpessoa'], 'nf', $idnotafiscal, $index, $nf['parcelas'] , $valorparcela, $vencimentocalc, $recebcalc, 'PENDENTE', $idfluxostatus, $idformapagamento, 'D', 'S', $nf['intervalo'], $_SESSION["SESSAO"]["USUARIO"]);
					} 
				}

				//LTM - 31-03-2021: Insere o FluxoHist para ContaPagar
				if(!empty($idfluxostatus))
				{
					FluxoController::inserirFluxoStatusHist('contapagar', $idcontapagar, $idfluxostatus, 'PENDENTE');
				}
						
				if($formapagamento['agrupado'] == 'Y'){
					cnf::agrupaCP(); 
				}
			}
		}
    }

//Alteração realizada a pedido do Hermes - 375922 - 05-10-2020
} elseif(($status == 'REPROVADO' or $status == 'INICIO' or $status == 'ENVIADO' or $status == 'RESPONDIDO' or  $status == 'CANCELADO') AND !empty($idnotafiscal) ){
	CotacaoController::apagarParcelasExistentes('nf', $idnotafiscal);
}

if($gerarpdf == TRUE and !empty($tipogerarpdf))
{
	congelaNfCotacao($idnotafiscal, $tipogerarpdf);
}

if((!empty($_POST['sel_picker_idcontaitem']) || !empty($_POST['name="sel_picker_idcontaitemtipoprodserv"'])) && !empty($_idcotacao))
{
	$arrayObjVincDb = array();
	$nfGrupoEs = array();
	$listarContaItem = CotacaoController::buscarObjetoVinculoPorTipoObjetoTipoObjetoVinc('cotacao', 'contaitem', $_idcotacao);
	$qtd = empty($listarContaItem) ? 0 : count($listarContaItem);
	$arrayObjvinc = explode(",", $_POST['sel_picker_idcontaitem']);
	if($qtd > 0 || count($arrayObjvinc) > 1)
	{
		//Retorna os valores cadastrados
		foreach($listarContaItem as $contaItem)
		{	
			array_push($arrayObjVincDb, $contaItem['idobjetovinc']);
		}

		//Valida o item novo para inserir
		foreach($arrayObjvinc AS $_idcontaitem)
		{
			if((!in_array($_idcontaitem, $arrayObjVincDb)))
			{
				CotacaoController::inserirObjetoVinculo($_idcotacao, 'cotacao', $_idcontaitem, 'contaitem', $_SESSION["SESSAO"]["USUARIO"]);
			}
		}

		//Valida para remover
		foreach($arrayObjVincDb AS $_idobjvincDb)
		{
			if((!in_array($_idobjvincDb, $arrayObjvinc)))
			{	
				//Valida se tem cotação vinculada ao Categoria
				$listarContaItensCotacao = CotacaoController::buscarGrupoESTipoObjetoSoliPor($_idcotacao, 'cotacao', $_idobjvincDb);
				$qtdNf = empty($listarContaItensCotacao) ? 0 : count($listarContaItensCotacao);
				if($qtdNf == 0)
				{
					CotacaoController::apagarObjetoVinculoIdObjetoIdObjetoVinc($_idcotacao, 'cotacao', $_idobjvincDb, 'contaitem');
				} else {
					$nremove = true;

					//Retorna os ids das NFs vinculadas ao Categoria
					foreach($listarContaItensCotacao as $cotacaoContaItem)
					{
						array_push($nfGrupoEs, $cotacaoContaItem['idnf']);
					}
				}
			}
		}

		if($nremove){
			die('Não é possível remover Categoria, pois está vinculada a uma cotação: '.json_encode($nfGrupoEs));
		}

	} else {
		CotacaoController::inserirObjetoVinculo($_idcotacao, 'cotacao', $_POST['sel_picker_idcontaitem'], 'contaitem', $_SESSION["SESSAO"]["USUARIO"]);
	}

	if(!empty($_POST['sel_picker_idcontaitemtipoprodserv']))
	{
		$arrayObjVincDbTipoItem = array();
		$nfTipoItem = array();
		$listarTipoItem = CotacaoController::buscarObjetoVinculoPorTipoObjetoTipoObjetoVinc('cotacao', 'contaitemtipoprodserv', $_idcotacao);
		$qtdContaItem = empty($listarTipoItem) ? 0 : count($listarTipoItem); 
		$arrayObjvincContaItem = explode(",", $_POST['sel_picker_idcontaitemtipoprodserv']);
		
		if($qtdContaItem > 0 || count($arrayObjvincContaItem) > 1)
		{
			//Retorna os valores cadastrados
			foreach($listarTipoItem as $tipoItem)
			{	
				array_push($arrayObjVincDbTipoItem, $tipoItem['idobjetovinc']);
			}

			//Valida o item novo para inserir
			foreach($arrayObjvincContaItem AS $_idTipoItem)
			{
				if((!in_array($_idTipoItem, $arrayObjVincDbTipoItem)))
				{
					CotacaoController::inserirObjetoVinculo($_idcotacao, 'cotacao', $_idTipoItem, 'contaitemtipoprodserv', $_SESSION["SESSAO"]["USUARIO"]);
				}
			}

			//Valida para remover
			foreach($arrayObjVincDbTipoItem AS $_idobjvincDbContaItem)
			{
				if(!in_array($_idobjvincDbContaItem, $arrayObjvincContaItem) && !empty($arrayObjvincContaItem))
				{	
					//Valida se tem cotação vinculada ao  Subcategoria
					$listarTipoItensCotacao = CotacaoController::buscarGrupoESTipoObjetoSoliPor($_idcotacao, 'cotacao', $_idobjvincDbContaItem);
					$qtdNf = empty($listarTipoItensCotacao) ? 0 : count($listarTipoItensCotacao);
					if($qtdNf == 0)
					{
						CotacaoController::apagarObjetoVinculoIdObjetoIdObjetoVinc($_idcotacao, 'cotacao', $_idobjvincDbContaItem, 'contaitemtipoprodserv');
					} else {
						$nremoveTipoItem = true;

						//Retorna os ids das NFs vinculadas ao Categoria
						foreach($listarTipoItensCotacao as $cotacaoTipoItem)
						{
							array_push($nfTipoItem, $cotacaoTipoItem['idnf']);
						}
					}
				}
			}

			if($nremoveTipoItem){
				die('Não é possível remover  Subcategoria, pois está vinculada a uma cotação: '.json_encode($nfTipoItem));
			}

		} else {
			CotacaoController::inserirObjetoVinculo($_idcotacao, 'cotacao', $_POST['sel_picker_idcontaitemtipoprodserv'], 'contaitemtipoprodserv', $_SESSION["SESSAO"]["USUARIO"]);
		}
	}
}

if(!empty($_POST['idsolcomitem']) && $_acao = 'u' && !empty($_SESSION['arrpostbuffer']['si']['u']['cotacao']['idcotacao']))
{
	$idsolcomitem = explode(",", $_POST['idsolcomitem']);
	$idcotacao = $_SESSION['arrpostbuffer']['si']['u']['cotacao']['idcotacao'];

	foreach($idsolcomitem as $_idsolcomitem)
	{
		if(!empty($_idsolcomitem))
		{
			$rowSolcomItem = CotacaoController::buscarSolcomItemPorIdSolcomItem($_idsolcomitem);
			$idprodserv = $rowSolcomItem["idprodserv"];
			$qtd = $rowSolcomItem["qtdc"];

			$forn = $_POST['itemalerta_forn_'.$idprodserv];
			if(!empty($forn))
			{
				$cond_where = ' AND p.idprodservforn IN ('.$forn.')';
			}

			$fornecedores = CotacaoController::buscarFornecedoresPertencentesCotacao($idcotacao, 'cotacao', $cond_where, $idprodserv);
			$qtdBuscaFornecedor = count($fornecedores);
			if($qtdBuscaFornecedor > 0)
			{
				$idpessoa = 0;
				foreach($fornecedores as $rowBuscaFornecedor)
				{	
					if($idpessoa == $rowBuscaFornecedor['idpessoa'] && !empty($newidnf)){
						$rowBuscaFornecedor["idnf"] = $newidnf;
					}else{
						$idpessoa = $rowBuscaFornecedor['idpessoa'];
					}

					//LTM 27-10-2020 - Alterado para não fazer calculo quando for produto. Na divisão estava retornando "INF"
					if($rowBuscaFornecedor['converteest'] == 'Y' && $rowBuscaFornecedor['tipo'] == 'PRODUTO'){
						$un = $rowBuscaFornecedor['unforn'];
						if(empty($rowBuscaFornecedor['valconv'])){
							die("[Erro] Verifique o valor de conversão do produto ".$rowBuscaFornecedor['descr']."<br><a href='?_modulo=prodserv&_acao=u&idprodserv=".$idprodserv."' target='_blank'>Ajustar</a>");
						}
						$_qtd = $qtd/$rowBuscaFornecedor['valconv'];
					}else{
						$un = $rowBuscaFornecedor['un'];
						$_qtd = $qtd;
					}

					//Caso o idnf do sql seja igual ao add do Solcom e a nf não for vazia, atualizao o valor do Item
					if(!empty($rowBuscaFornecedor["idnf"]) && $idprodserv == $rowBuscaFornecedor["idprodservnfitem"])
					{
						CotacaoController::atualizarQtdNfItem($_qtd, $rowBuscaFornecedor['idnfitem']);

					} else {
						//Caso a NF seja vazia, será criada uma nova e juntamente com o item
						if(empty($rowBuscaFornecedor["idnf"]))
						{
							$tiponf = $_SESSION['arrpostbuffer']['si']['u']['cotacao']['tiponf'];
							$idtipounidade = CotacaoController::buscarIdTipoUnidade($tiponf);
							$rwUnid = CotacaoController::buscarIdunidadePorTipoUnidade($idtipounidade, $_idempresa);

							$idfluxostatusNf = FluxoController::getIdFluxoStatus('nfentrada', 'INICIO');

							if($tiponf != 'V'){
								$idunidade = $rwUnid["idunidade"]; 
							} else {
								$idunidade = 'NULL';
							}

							$newidnf = CotacaoController::inserirNf($rowBuscaFornecedor['idpessoa'], $idempresa, $idcotacao, $idfluxostatusNf, $rwUnid["idunidade"], 'cotacao', 'INICIO', '0', $tiponf, $_SESSION["SESSAO"]["USUARIO"]);
							$arrayLog = [
								"idempresa" => cb::idempresa(),
								"sessao" => '',
								"tipoobjeto" => 'nf',
								"idobjeto" => $newidnf,
								"tipolog" => 'unidadeNf',
								"log" => $idunidade,
								"status" => 'ERRO',
								"info" => 'Inserção de Itens da Solcom',
								"criadoem" => SYSDATE(),
								"data" => SYSDATE(),
								"usuario" => $_SESSION["SESSAO"]["USUARIO"]
							];
							CotacaoController::inserirLog($arrayLog);

							if($newidnf)
							{
								//LTM - 31-03-2021: Insere o fluxo
								FluxoController::inserirFluxoStatusHist('nfentrada', $newidnf, $idfluxostatusNf, 'PENDENTE');   
							}
						} else {
							//Se não cria apenas o item com o ID da NF do fornecedor correspondente.
							$newidnf = $rowBuscaFornecedor["idnf"];
						}
						
						$idtipoprodserv = traduzid('prodserv', 'idprodserv', 'idtipoprodserv', $idprodserv);
						$idtipoprodserv = isset($idtipoprodserv) ? $idtipoprodserv : NULL;
						
						CotacaoController::inserirNfItem($newidnf, 'C', $rowBuscaFornecedor['idprodservforn'], $idempresa, $un, $_qtd, 'Y', $idprodserv, $idtipoprodserv, $rowBuscaFornecedor["idgrupoes"], $_SESSION["SESSAO"]["USUARIO"]);
					}

					CotacaoController::atualizarStatusIdcotacaoSolcomItem($idcotacao, 'ASSOCIADO', $_idsolcomitem, $_SESSION["SESSAO"]["USUARIO"]);  			
				}
			}
		}  
	}                         
}

if(!empty($_POST['idprodservalerta']) && $_acao = 'u' && !empty($_SESSION['arrpostbuffer']['si']['u']['cotacao']['idcotacao']))
{
	$idprodservarray = explode(",", $_POST['idprodservalerta']);
	$idcotacao = $_SESSION['arrpostbuffer']['si']['u']['cotacao']['idcotacao'];

	foreach($idprodservarray as $idprodserv)
	{
		if(!empty($idprodserv))
		{
			$qtd = $_POST['idprodservalertaqtd'.$idprodserv];
			$forn = $_POST['itemalerta_forn_'.$idprodserv];
			if(!empty($forn))
			{
				$cond_where = ' AND p.idprodservforn IN ('.$forn.')';
			}
								 
			$fornecedores = CotacaoController::buscarFornecedoresPertencentesCotacao($idcotacao, 'cotacao', $cond_where, $idprodserv);
			$qtdBuscaFornecedor = count($fornecedores);
			if($qtdBuscaFornecedor > 0)
			{
				$idpessoa = 0;
				foreach($fornecedores as $rowBuscaFornecedor)
				{	
					if($idpessoa == $rowBuscaFornecedor['idpessoa'] && !empty($newidnf)){
						$rowBuscaFornecedor["idnf"] = $newidnf;
					}else{
						$idpessoa = $rowBuscaFornecedor['idpessoa'];
					}
					
					//LTM 27-10-2020 - Alterado para não fazer calculo quando for produto. Na divisão estava retornando "INF"
					if($rowBuscaFornecedor['converteest'] == 'Y' && $rowBuscaFornecedor['tipo'] == 'PRODUTO'){
						$un = $rowBuscaFornecedor['unforn'];
						if(empty($rowBuscaFornecedor['valconv'])){
							die("[Erro] Verifique o valor de conversão do produto ".$rowBuscaFornecedor['descr']."<br><a href='?_modulo=prodserv&_acao=u&idprodserv=".$idprodserv."' target='_blank'>Ajustar</a>");
						}
						$_qtd = $qtd/$rowBuscaFornecedor['valconv'];
					}else{
						$un = $rowBuscaFornecedor['un'];
						$_qtd = $qtd;
					}

					//Caso a NF seja vazia, será criada uma nova e juntamente com o item
					if(empty($rowBuscaFornecedor["idnf"]))
					{
						$tiponf = $_SESSION['arrpostbuffer']['si']['u']['cotacao']['tiponf'];
						$idtipounidade = CotacaoController::buscarIdTipoUnidade($tiponf);
						$rwUnid = CotacaoController::buscarIdunidadePorTipoUnidade($idtipounidade, $_idempresa);

						$idfluxostatusNf = FluxoController::getIdFluxoStatus('nfentrada', 'INICIO');

						if($tiponf != 'V'){
							$idunidade = $rwUnid["idunidade"]; 
						} else {
							$idunidade = 'NULL';
						}

						$newidnf = CotacaoController::inserirNf($rowBuscaFornecedor['idpessoa'], $idempresa, $idcotacao, $idfluxostatusNf, $rwUnid["idunidade"], 'cotacao', 'INICIO', '0', $tiponf, $_SESSION["SESSAO"]["USUARIO"]);
						$arrayLog = [
							"idempresa" => cb::idempresa(),
							"sessao" => '',
							"tipoobjeto" => 'nf',
							"idobjeto" => $newidnf,
							"tipolog" => 'unidadeNf',
							"log" => $idunidade,
							"status" => 'ERRO',
							"info" => 'Inserção de Alerta',
							"criadoem" => SYSDATE(),
							"data" => SYSDATE(),
							"usuario" => $_SESSION["SESSAO"]["USUARIO"]
						];
						CotacaoController::inserirLog($arrayLog);

						if($newidnf)
						{
							//LTM - 31-03-2021: Insere o fluxo
							FluxoController::inserirFluxoStatusHist('nfentrada', $newidnf, $idfluxostatus, 'PENDENTE'); 
						}

					} else {
						//Se não cria apenas o item com o ID da NF do fornecedor correspondente.
						$newidnf = $rowBuscaFornecedor["idnf"];
					}
					
					$idtipoprodserv = traduzid('prodserv', 'idprodserv', 'idtipoprodserv', $idprodserv);
					$idtipoprodserv = isset($idtipoprodserv) ? $idtipoprodserv : NULL;

					CotacaoController::inserirNfItem($newidnf, 'C', $rowBuscaFornecedor['idprodservforn'], $idempresa, $un, $_qtd, 'Y', $idprodserv, $idtipoprodserv, $rowBuscaFornecedor["idgrupoes"], $_SESSION["SESSAO"]["USUARIO"]);
				}
			}
		}  
	}                         
}
?>
