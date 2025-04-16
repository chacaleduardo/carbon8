<?
$iu = $_SESSION['arrpostbuffer']['1']['u']['prproc']['idprproc'] ? 'u' : 'i';
$tipo = $_SESSION['arrpostbuffer']['1']['i']['prproc']['tipo'];
$proc = $_SESSION['arrpostbuffer']['1']['i']['prproc']['proc'];

if($iu == 'i' && $tipo == 'SERVICO')
{    
    $rowx = PrProcController::buscarUnidadePorIdtipoIdempresa(12, 1);  
    $idprproc = $_SESSION["_pkid"];
    
    $insprodserv = new Insert();
    $insprodserv->setTable("prodserv");
    $insprodserv->descr = $proc;
    $insprodserv->tipo = 'SERVICO';
    $insprodserv->idunidadeest = $rowx['idunidade'];
    $insprodserv->status = 'ATIVO';
    $idprodserv = $insprodserv->save();  
    
    $insunidadeobjeto = new Insert();
    $insunidadeobjeto->setTable("unidadeobjeto");
    $insunidadeobjeto->idunidade = $rowx['idunidade'];
    $insunidadeobjeto->idobjeto = $idprodserv;
    $insunidadeobjeto->tipoobjeto = 'prodserv';
    $idunidadeobjeto = $insunidadeobjeto->save();
    
    $insprodservprproc = new Insert();
    $insprodservprproc->setTable("prodservprproc");
    $insprodservprproc->idprodserv = $idprodserv;
    $insprodservprproc->idprproc = $idprproc;
    $insprodservprproc->status = 'ATIVO';
    $idprodservprproc=$insprodservprproc->save();
    
    $insprodservf = new Insert();
    $insprodservf->setTable("prodservformula");
    $insprodservf->idprodserv = $idprodserv;
    $insprodservf->status = 'ATIVO';
    $idprodservf = $insprodservf->save();
}

if ($_POST['_1_u_prproc_status'] == 'REVISAO' || $_POST['_1_u_prproc_status'] == 'APROVADO' || $_POST['_1_i_prproc_status'] == 'REVISAO')
{
    if($_POST['_1_i_prproc_status'] == 'REVISAO'){
        $idprproc = $_SESSION['_pkid'];
    } else {
        $idprproc = $_SESSION["arrpostbuffer"]["1"]['u']["prproc"]["idprproc"];    
    }

    $aProc = array();

    if($_POST['_statusant_'] != 'APROVADO' && !empty($_POST['_1_i_prproc_idprproc'])){
        $controleVersao = $_POST['_1_u_prproc_versao'];
    } else if(empty($_POST['_1_i_prproc_idprproc']) && empty($_POST['_1_u_prproc_versao'])){
        $controleVersao = 0;
    } else if($_POST['_statusant_'] != 'APROVADO'){
        $controleVersao = $_POST['_1_u_prproc_versao'];
    } else {
        $controleVersao = $_POST['_1_u_prproc_versao']+1;
    }

    $processos = PrProcController::buscarSqlProcessos($idprproc);
    $aProc["prproc"]["sql"] = $processos;
	$aProc["prproc"]["res"] = sql2array($processos, true);

	//prprocprativ
    $sqlAtividade = PrProcController::buscarSqlAtividadePorIdProProc($idprproc);
	$aProc["prprocprativ"]["sql"] = $sqlAtividade;
	$aProc["prprocprativ"]["res"] = sql2array($sqlAtividade, true, array(), true);

    if($_POST['_1_u_prproc_status'] == 'REVISAO' || $_POST['_1_i_prproc_status'] == 'REVISAO')
    {
        $_listarObjetoJson = PrProcController::buscarVersaoObjetoPorTipoObjetoEVersao($idprproc, 'prproc', $controleVersao);
        if($_listarObjetoJson['qtdLinhas'] < 1)
        {
            $res_result = PrProcController::buscarProcessos($_POST["_1_u_prproc_idprproc"]);    
            $aProc["prprocprativ"]["rev"] = ['revisadopor' => $res_result['alteradopor'],'revisadoem' => $res_result['alteradoem'], 'naomostrar' => 'Y'];
            
            $arrayObjetoJson = [
                "idempresa" => $_SESSION['SESSAO']['IDEMPRESA'],
                "idobjeto" => $idprproc,
                "tipoobjeto" => 'prproc',
                "jobjeto" => base64_encode(serialize($aProc)),
                "versaoobjeto" => $controleVersao,
                "criadopor" => $_SESSION['SESSAO']['USUARIO'],
                "criadoem" => 'now()',
                "alteradopor" => $_SESSION["SESSAO"]["USUARIO"],
                "alteradoem" => 'now()'
            ];
            PrProcController::inserirObjetoJson($arrayObjetoJson);
        }

    } else if ($_POST['_1_u_prproc_status']  == 'APROVADO'){

        $_listarObjetoJson = PrProcController::buscarVersaoObjetoPorTipoObjetoEVersao($idprproc, 'prproc', $_POST['_1_u_prproc_versao']);
        $_listarObjetoJson = $_listarObjetoJson['dados'];
        $ru = unserialize(base64_decode($_listarObjetoJson["jobjeto"]));

        $aProc["prprocprativ"]["ref"] = ['revisadopor'=>nl2br($ru['prprocprativ']['rev']['revisadopor']),'revisadoem'=>nl2br($ru['prprocprativ']['rev']['revisadoem'])];
        PrProcController::atualizarJobjetoObjetoJsonPorIdobjetojson(base64_encode(serialize($aProc)), $_listarObjetoJson['idobjetojson']);
    }

    if ($_POST['_statusant_'] != 'APROVADO' AND $_SESSION["arrpostbuffer"]["1"]['u']["prproc"]["status"] == 'APROVADO')
    {
        $arrayAuditoria = [
			"idempresa" => $_SESSION['SESSAO']['IDEMPRESA'],
			"linha" => 1,
			"acao" => 'i',
			"objeto" => 'objetojson',
			"idobjeto" => $idprproc,
			"coluna" => 'jobjeto',
			"valor" => base64_encode(serialize($aProc)),
			"criadopor" => $_SESSION["SESSAO"]["USUARIO"],
			"tela" => $_SERVER["HTTP_REFERER"]
		];
		PrProcController::inserirAuditoria($arrayAuditoria);
    }
}
