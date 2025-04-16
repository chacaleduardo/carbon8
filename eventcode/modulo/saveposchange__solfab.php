<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

$iu = $_SESSION['arrpostbuffer']['1']['u']['solfab']['idsolfab'] ? 'u' : 'i';

if($iu == 'i' && !empty($_SESSION["_pkid"]) && $_SESSION['arrpostbuffer']['1']['i']['solfab']['idlote'])
{
	$_idlote = $_SESSION['arrpostbuffer']['1']['i']['solfab']['idlote'];	
	$lidpessoa = traduzid("lote", "idlote", "idpessoa", $_idlote);
	$lidprodserv = traduzid("lote", "idlote", "idprodserv", $_idlote);
	$arrLoteSF = SolfabController::buscarLotesSolfab($lidpessoa, $lidprodserv);//esta também e chamada na SF	
	
	if(count($arrLoteSF["amostras"]) > 0)
	{
		foreach($arrLoteSF["amostras"] as $idamostra => $arrres)
		{
			//Listar somente outras amostras
			if($idamostra != $_1_u_amostra_idamostra)
			{
				foreach($arrres as $idresultado => $arrlote)
				{
					foreach($arrlote as $idlote => $lote)
					{
						//inserir os agente do cliente na SF
						$arraySolfabItem = [
							"idempresa" => cb::idempresa(),
							"idsolfab" => $_SESSION["_pkid"],
							"idobjeto" => $idlote,
							"tipoobjeto" => 'lote',
							"usuario" => $_SESSION["SESSAO"]["USUARIO"]
						];
						SolfabController::inserirSolfabItem($arraySolfabItem);
					}
				}
			}
		}
	}
}

$idsolfab = $_SESSION['arrpostbuffer']['1']['u']['solfab']['idsolfab'];
$status = $_SESSION['arrpostbuffer']['1']['u']['solfab']['status'];
$statusant = $_POST["solfab_status_anterior"];
if(!empty($idsolfab) && $status == 'APROVADO')
{
	SolfabController::atualizarAtualizarLotePorIdSolfab($idsolfab);

	$listarLote = SolfabController::buscarLoteSolfabItem($idsolfab);

	$idlotevacina= SolfabController::buscarLoteSolfab($idsolfab); 

	foreach($listarLote as $rw)
	{
		$rowFluxo = FluxoController::getFluxoStatusHist('semente', 'idlote', $rw["idlote"], 'APROVADO');
		FluxoController::alterarStatus('semente', 'idlote', $rw["idlote"], $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], null, 0, $rowFluxo['idfluxostatus'], $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);


		 

		// passar o custo do rateio da semente para o lote de vacina

		$infrateio= SolfabController::buscaInfRateioSemente($rw["idlote"]);

		if ($infrateio['idrateioitemdest']>0) {

			$insrateiocusto = new Insert();
			$insrateiocusto->setTable("rateiocusto");
			$insrateiocusto->idempresa=cb::idempresa();
			$insrateiocusto->tiporateio='semente';
			$insrateiocusto->valorun=$infrateio['custo'];
			$insrateiocusto->valor=$infrateio['custo'];
			$idrateiocusto=$insrateiocusto->save();		

			$ilotecusto= new Insert();
			$ilotecusto->setTable("lotecusto");
			$ilotecusto->idempresa=cb::idempresa();
			$ilotecusto->idrateiocusto=$idrateiocusto;
			$ilotecusto->idlote=$idlotevacina['idlote'];
			$ilotecusto->idobjeto=$infrateio['idunidade'];
			$ilotecusto->tipoobjeto='unidade';
			$ilotecusto->origem='semente';
			$ilotecusto->tipo='CD';
			$ilotecusto->valor=$infrateio['custo'];
			$idlotecusto=$ilotecusto->save();
		
			SolfabController::atualizaValorLote($idlotevacina['idlote'],$infrateio['vlrlote'],$infrateio['vlrlotetotal']);
			SolfabController::atualizaRateioitemdest($infrateio['idrateioitemdest'],$idrateiocusto);
		}

	}
	
	//Aprova as OPs ligadas a esta Solfab
	$listarFormalizacao = SolfabController::buscarFormalizacaoPorlote($idsolfab);
	foreach($listarFormalizacao as $rwSelect)
	{
		$moduloFormalizacao = getModuloPadrao('formalizacao', $rwSelect["idunidade"]);
		$rowFluxoFpr = FluxoController::getFluxoStatusHist($moduloFormalizacao, 'idformalizacao', $rwSelect["idformalizacao"], 'TRIAGEM', $rwSelect["idprproc"], 'INICIO');
		FluxoController::atualizaModuloTab($moduloFormalizacao, 'idformalizacao', $rwSelect["idformalizacao"], $rowFluxoFpr['statustipo'], $rowFluxoFpr['idfluxostatus']);
		FluxoController::inserirFluxoStatusHist($moduloFormalizacao, $rwSelect["idformalizacao"], $rowFluxoFpr['idfluxostatus'], 'ATIVO');
	}
}
?>