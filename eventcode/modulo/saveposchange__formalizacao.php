<?
//LTM - 05/05/2021 - Valida se todos status da etapa estão cocluidas, caso estejam, será inserido os status da próxima etapa
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

$iu = $_SESSION['arrpostbuffer']['1']['u']['formalizacao']['idformalizacao'] ? 'u' : 'i';
$statusOP = $_SESSION['arrpostbuffer']['1']['u']['formalizacao']['status'];

//LTM (19/05/2021): Cancelar o Lote junto com o OP
if($iu == "u" && $statusOP == "CANCELADO")
{
	//mcc 12/08/2020 - Excluir os testes vinculados à atividades deste lote quando cancelado, conforme evento 365212
	FormalizacaoController::excluirResultadosVinculadosFormalizacao($_SESSION["arrpostbuffer"]["2"]["u"]["lote"]["idlote"]);
	$re = d::b()->query($s) or die("Erro ao alterar status do lote : ". mysql_error() . "<p>SQL: ".$s);  
	
	//LTM (02/06/2021): Caso a OP seja cancelada o status do lote tb será
	$idlote = $_SESSION['arrpostbuffer']['2']['u']['lote']['idlote'];
	$modulo = FluxoController::getDadosModuloPrincipal($idlote);
	$rowFluxo = FluxoController::getFluxoStatusHist($modulo, 'idlote', $idlote, 'CANCELADO');
	FluxoController::alterarStatus($modulo, 'idlote', $idlote, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], null, 0, $rowFluxo['idfluxostatus'], $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);	
}

//LTM (25/03/2021): Devido ter criado uma tabela formalização é necessário, pegar o último ID do lote para atualizar a tabela formalização
$idloteLastIns = $_SESSION['arrscriptsql']['2']['lote']['insertid'];
if($iu == "i" && !empty($idloteLastIns))
{	
	FormalizacaoController::atualizarLoteFormalizacao($idloteLastIns, $_SESSION["_pkid"]);		

	//LTM (02/06/2021): Altera o Status do lote para Aberto (Início), pois foi criado neste momento. Insere o FluxostatusHist
	// Define status ABERTO p/ o lote que está sendo criado.
	$modulo = FluxoController::getDadosModuloPrincipal($idloteLastIns);
	$rowFluxo = FluxoController::getFluxoStatusHist($modulo, 'idlote', $idloteLastIns, 'ABERTO');
	FluxoController::alterarStatus($modulo, 'idlote', $idloteLastIns, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], null, 0, $rowFluxo['idfluxostatus'], $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);	
}

if(($_SESSION['arrpostbuffer']['lote']['u']['lote']['idlote'] && $_SESSION['arrpostbuffer']['lote']['u']['lote']['idprodservformula'])
		|| ($_POST['novomodelo'] == 'Y' && $_POST['lote_u_lote_idprodservformula']))
{
	$sessionLote = empty($_SESSION['arrpostbuffer']['lote']['u']['lote']['idlote']) ? $_POST['idlotelote'] : $_SESSION['arrpostbuffer']['lote']['u']['lote']['idlote'];
	$idlote = empty($sessionLote) ? $idloteLastIns : $sessionLote;
	$sessionProservFormula = $_SESSION['arrpostbuffer']['lote']['u']['lote']['idprodservformula'];
	$idprodservformula = empty($sessionProservFormula) ? $_POST['lote_u_lote_idprodservformula'] : $sessionProservFormula;

	if($_POST['novomodelo'] == 'Y')
	{
		FormalizacaoController::atualizarPprodservFormulaLote($idlote, $idprodservformula);
	}

	$qtd = FormalizacaoController::buscarQtdLoteAtivPorIdLote($idlote);
	if($qtd < 1 && !empty($idprodservformula)){
		$ret = FormalizacaoController::gerarAtividadeLote($idlote, $idprodservformula);
		if($ret != "OK"){
			die($ret);
		}else{
			$ret2 = FormalizacaoController::atualizaLacreLote($idlote);
			if($ret2 != "OK"){
				die("Falha ao atualizar lacre:".$ret2);
			}
		}
	}

}elseif($iu == 'u' && $_SESSION['arrpostbuffer']['2']['u']['lote']['idprodserv'] && $_SESSION['arrpostbuffer']['2']['u']['lote']['idlote']){

	$idlote = $_SESSION['arrpostbuffer']['2']['u']['lote']['idlote'];
	$atividadeLote = FormalizacaoController::buscarLoteAtivPorIdLote($idlote);
	$qtd = $atividadeLote['qtdLinhas'];
	
	$prodservFormula = FormalizacaoController::buscarIdProdservFormulaPorIdLote($idlote);
	
	if($qtd < 1 && !empty($prodservFormula['idprodservformula']))
	{
		$ret = FormalizacaoController::gerarAtividadeLote($idlote, $prodservFormula['idprodservformula']);
		if($ret!="OK")
		{
			die($ret);
		}else{
			$ret2 = FormalizacaoController::atualizaLacreLote($idlote);
			if($ret2 != "OK"){
				die("Falha ao atualizar lacre:".$ret2);
			}
		}
	}
}

$statusFormalizacao = $_SESSION["arrpostbuffer"]["1"]["u"]["formalizacao"]["status"];
if($statusFormalizacao == 'TRIAGEM' || $statusFormalizacao == 'FORMALIZACAO')
{
	$idlote = $_SESSION['arrpostbuffer']['2']['u']['lote']['idlote'];
	//reservar salas
	$listarSalasReserva = FormalizacaoController::buscarSalasParaReserva($idlote);
		
	FormalizacaoController::apagarSalasReserva($idlote);
		
	foreach($listarSalasReserva as $reserva)
	{
		if(!empty($reserva['idtagreserva']))
		{
			$arrayAtualizaReserva = [
				"inicio" => $reserva['execucao'],
				"fim" => $reserva['execucaofim'],
				"trava" => $reserva['travasala'],
				"alteradopor" => $_SESSION["SESSAO"]["USUARIO"],
				"idtagreserva" => $reserva['idtagreserva']
			];
			FormalizacaoController::atualizarReservaSalaLoteFormalizacao($arrayAtualizaReserva);
				
		}else{
			$arrayInserirReserva = [
				"idtag" => $reserva['idtag'],
				"idobjeto" => $reserva["idloteativ"],
				"objeto" => 'loteativ',
				"inicio" => $reserva['execucao'],
				"fim" => $reserva["execucaofim"],
				"trava" => $reserva['travasala'],
				"status" => 'ATIVO',
				"criadopor" => $_SESSION["SESSAO"]["USUARIO"],
				"criadoem" => 'now()',
				"alteradopor" => $_SESSION["SESSAO"]["USUARIO"],
				"alteradoem" => 'now()'
			];
			$retorno = FormalizacaoController::inserirReservaSalaLoteFormalizacao($arrayInserirReserva);	
			if($retorno)
			{
				echo $retorno;
			}	
		}
	}
}
        
if($_POST['oldstatus'] == 'TRIAGEM' && ($_POST['lote_producaoold'] != $_SESSION["arrpostbuffer"]["2"]["u"]["lote"]["producao"]))
{
	$idlote = $_SESSION["arrpostbuffer"]["2"]["u"]["lote"]["idlote"];
	$dataprod = validadatetime($_SESSION["arrpostbuffer"]["2"]["u"]["lote"]["producao"]);
   
    if(!empty($dataprod))
	{
		FormalizacaoController::atualizarDataExecucaoAtividade($idlote, $dataprod);
		if(!empty($_POST['lote_producaoold']))
		{	
			FormalizacaoController::apagarAtividadeESalasReserva($idlote);
		}		
    }
}

$status = $_SESSION['arrpostbuffer']['1'][$iu]['formalizacao']['status'];
$idloteU = $_SESSION['arrpostbuffer']['2'][$iu]['lote']['idlote'];
$idformalizacao = empty($_SESSION['arrpostbuffer']['1'][$iu]['formalizacao']['idformalizacao']) ? $_SESSION['_pkid'] : $_SESSION['arrpostbuffer']['1'][$iu]['formalizacao']['idformalizacao'];

// atualiza o status do lote conforme o status configurado no processo da ultima atividade concluida  --gabrielsaimo-- 01/02/2021
if(!empty($idloteU) && $iu == 'u')
{
    $processo = FormalizacaoController::buscarStatusPaiProcessoPorIdLote($idloteU);
	$processoDados = $processo['dados'];
    if($processo['qtdLinhas'] > 0)
	{
		if(!empty($processoDados['statuspai']))
		{ 
			$statusArrayConcluido = array('QUARENTENA', 'PROCESSANDO', 'ESGOTADO','APROVADO', 'REPROVADO');
			$statusArrayPendente = array('FORMALIZACAO', 'TRIAGEM', 'AGUARDANDO','ABERTO');
			if(in_array($processoDados['statuspai'], $statusArrayConcluido)){
				$_situacao='CONCLUIDO';
			}elseif(in_array($processoDados['statuspai'], $statusArrayPendente)){
				$_situacao='PENDENTE';
			}

			if($processoDados['statuspai'] == 'PROCESSANDO')
			{
				FormalizacaoController::gerarAmostrasRelacionadasAoLote($idloteU);
			}
			
			//Validação para não alterar o status do lote se este já tiver mudado de status
			$idloteu = $_SESSION['arrpostbuffer']['2']['u']['lote']['idlote'];			

			$arrsq = $_SESSION["arrscriptsql"];
			reset($arrsq);
			//Insere os novos Fluxos
			foreach($arrsq as $linha => $arrlinha)
			{
				foreach ($arrlinha as $tab => $arrsql)
				{
					$statusAtiv = $_SESSION["arrpostbuffer"][$linha]['u']["loteativ"]["status"];
					$idloteativ = $_SESSION["arrpostbuffer"][$linha]['u']["loteativ"]["idloteativ"];
					$bloquearstatus = $_SESSION["arrpostbuffer"][$linha]['u']["loteativ"]["bloquearstatus"];

					if($tab == "loteativ" && $statusAtiv == "CONCLUIDO" && $bloquearstatus != 'Y')
					{
						FluxoController::alterarStatusFormalizacao($_REQUEST['_modulo'], $idformalizacao, $idloteativ);
					}
				}
			}
		}
    }// if($qtdres>0){
}//if($status=='PROCESSANDO' AND !empty($idloteU) AND $iu=='u' ){

//Inserir Fluxo Atividades Concluídas pelo FinalizaOP
$arrsq = $_SESSION["arrscriptsql"];
foreach($arrsq as $linha => $arrlinha)
{
	foreach ($arrlinha as $tab => $arrsql)
	{
		$statusAtiv = $_SESSION["arrpostbuffer"][$linha]['u']["loteativ"]["status"];
		$idloteativ = $_SESSION["arrpostbuffer"][$linha]['u']["loteativ"]["idloteativ"];
		$bloquearstatus = $_SESSION["arrpostbuffer"][$linha]['u']["loteativ"]["bloquearstatus"];

		if($tab == "loteativ" && $statusAtiv == "CONCLUIDO" && substr($linha, 0, 8) == 'statusla')
		{
			$fluxoAtiv = FormalizacaoController::buscarFluxoStatusLoteAtiv($idloteativ);
			$fluxoCount = FluxoController::buscarFluxoStatusHistPorModuloEFluxoStatus($_REQUEST['_modulo'], $idformalizacao, $fluxoAtiv['idfluxostatus']);
			if(count($fluxoCount) == 0){				
				FluxoController::inserirFluxoStatusHist($_REQUEST['_modulo'], $idformalizacao, $fluxoAtiv['idfluxostatus'], 'ATIVO');
			}
		}
	}
}

//Verificar se o input de controle [gerartestes=Y] foi enviado
if($iu == "u" && $_POST["gerartestes"] == "Y" && $_SESSION['arrpostbuffer']['2']['u']['lote']['status'] == "PROCESSANDO")
{
	FormalizacaoController::gerarAmostrasRelacionadasAoLote($_SESSION['arrpostbuffer']['2']['u']['lote']['idlote']);
}

//LTM (12-05-2021): Insere na tabela Formalizacao e hist do lote e da formalizacao
if(!empty($_SESSION['arrpostbuffer']['x']['i']['lote']['idprodservformulains']) && !empty($_SESSION['arrpostbuffer']['x']['i']['lote']['idobjetoprodpara']))
{
	$modulolote = FluxoController::getDadosModuloPrincipal($_SESSION["_pkid"]);
	//Insere a Hist da Formalização
	FluxoController::inserirFluxoStatusHist($modulolote, $_SESSION["_pkid"], $_SESSION['arrpostbuffer']['x']['i']['lote']['idfluxostatus'], 'FORMALIZACAO');
}

if(!empty($_SESSION['arrpostbuffer']['f']['i']['formalizacao']['idlote']) && !empty($_SESSION['arrpostbuffer']['f']['i']['formalizacao']['idprproc']))
{
	//Insere a Hist da Formalização
	FluxoController::inserirFluxoStatusHist('formalizacao', $_SESSION["_pkid"], $_SESSION['arrpostbuffer']['f']['i']['formalizacao']['idfluxostatus'], 'ABERTO');
}

if(!empty($idformalizacao))
{
	//LTM (14/07/2021): Altera para Autorizado caso a Formalização esteja no Status Aberto e a Solfab esteja aprovado e tenha o idsolfab
	$rowSolFab = FormalizacaoController::buscarSolfabFormalizacao($idformalizacao);
	if($rowSolFab['statusformalizacao']  == 'ABERTO' && $rowSolFab['status'] == 'APROVADO')
	{
		$moduloFormalizacao = getModuloPadrao('formalizacao', $rowSolFab['idunidade']);
		$rowFluxoFpr = FluxoController::getFluxoStatusHist($moduloFormalizacao, 'idformalizacao', $idformalizacao, 'TRIAGEM', $rowSolFab['idprproc'], 'INICIO');
		FluxoController::atualizaModuloTab($moduloFormalizacao, 'idformalizacao', $idformalizacao, 'TRIAGEM', $rowFluxoFpr['idfluxostatus']);
		FluxoController::inserirFluxoStatusHist($moduloFormalizacao, $idformalizacao, $rowFluxoFpr['idfluxostatus'], 'ATIVO');
	}
}

unset($fluxo);
?>