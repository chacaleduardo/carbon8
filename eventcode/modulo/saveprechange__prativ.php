<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

$iu = $_SESSION['arrpostbuffer']['sala']['u']['prativobj']['tipoobjeto'] ? 'u' : 'i';
$idprativobj = $_SESSION["arrpostbuffer"]["sala"][$iu]["prativobj"]["idprativobj"];
$idobjeto = $_SESSION["arrpostbuffer"]["sala"][$iu]["prativobj"]["idobjeto"];
$tipoobjeto = $_SESSION["arrpostbuffer"]["sala"][$iu]["prativobj"]["tipoobjeto"];

if($iu == 'i' && empty($idobjeto)){
	unset($_SESSION['arrpostbuffer']['sala']);
}

if($iu == 'u' && empty($idobjeto) && !empty($idprativobj) && !empty($tipoobjeto))
{
	PrativController::apagarPrativObj($idprativobj);
	unset($_SESSION['arrpostbuffer']['sala']);
}

if (($_POST['_statusant_'] != 'REVISAO' && $_SESSION["arrpostbuffer"]["1"]['u']["prativ"]["status"] == 'REVISAO') || ($_SESSION["arrpostbuffer"]["1"]['u']["prativ"]["status"] == 'INATIVO')) 
{
	$idprativ = $_SESSION["arrpostbuffer"]["1"]['u']["prativ"]["idprativ"];
	$_listarProcessos = PrativController::buscarProcessosLigadosAtividade($idprativ);
	if ($_listarProcessos['qtdLinhas'] > 0) 
	{
		$i = 0;

		foreach($_listarProcessos['dados'] as $processos)
		{
			$aProc = array();
			
			$_1_u_prativ_versao=$_POST['_1_u_prativ_versao']+1;
			if(empty($_1_u_prativ_versao)){$_1_u_prativ_versao=1;}
			$idprproc = $processos['idprproc'];
			$_SESSION["arrpostbuffer"]["xx".$i.""]['u']["prproc"]["idprproc"] = $idprproc;
			$_SESSION["arrpostbuffer"]["xx".$i.""]['u']["prproc"]["versao"] = $processos["versao"] + 1;
			$_SESSION["arrpostbuffer"]["xx".$i.""]['u']["prproc"]["status"] = 'REVISAO';
			$_SESSION["arrpostbuffer"]["xx".$i.""]['u']["prproc"]["descr"] = 'Alteração na atividade: '.$_POST['_1_u_prativ_ativ'].' (Nova versão '.$_1_u_prativ_versao.'.0)';

			$rowFluxo = FluxoController::getFluxoStatusHist('prproc', 'idprproc', $idprproc, 'REVISAO');
			FluxoController::alterarStatus('prproc', 'idprproc', $idprproc, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], null, 0, '', $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);

			$processos = PrativController::buscarSqlProcessos($idprproc);
			$aProc["prproc"]["sql"] = $processos;
			$aProc["prproc"]["res"] = sql2array($processos,true);

			//prprocprativ
			$sqlPrprocprativ = PrativController::buscarSqlAtividadePorIdProProc($idprproc);
			$aProc["prprocprativ"]["sql"] = $sqlPrprocprativ;
			$aProc["prprocprativ"]["res"] = sql2array($sqlPrprocprativ,true,array(),true);

			$arrayObjetoJson = [
                "idempresa" => cb::idempresa(),
                "idobjeto" => $idprproc,
                "tipoobjeto" => 'prproc',
                "jobjeto" => base64_encode(serialize($aProc)),
                "versaoobjeto" => intval($aProc["prproc"]["res"] ["versao"]) + 1,
                "criadopor" => $_SESSION['SESSAO']['USUARIO'],
                "criadoem" => 'now()',
                "alteradopor" => $_SESSION["SESSAO"]["USUARIO"],
                "alteradoem" => 'now()'
            ];
            PrProcController::inserirObjetoJson($arrayObjetoJson);

			$arrayAuditoria = [
				"idempresa" => cb::idempresa(),
				"linha" => 1,
				"acao" => 'i',
				"objeto" => 'objetojson',
				"idobjeto" => $idprproc,
				"coluna" => 'jobjeto',
				"valor" => base64_encode(serialize($aProc)),
				"criadopor" => $_SESSION["SESSAO"]["USUARIO"],
				"tela" => $_SERVER["HTTP_REFERER"]
			];
			FormulaProcessoController::inserirAuditoria($arrayAuditoria);

			$aProc = null;
			$i++;
		}
	}
}

if ($_POST['_statusant_'] != 'REVISAO' and $_SESSION["arrpostbuffer"]["1"]['u']["prativ"]["status"] == 'REVISAO') 
{
    $_SESSION["arrpostbuffer"]["1"]['u']["prativ"]["versao"] = $_SESSION["arrpostbuffer"]["1"]['u']["prativ"]["versao"] + 1;
    $_SESSION["arrpostbuffer"]["1"]['u']["prativ"]["descr"] = NULL;
}

retarraytabdef('prproc');