<?
if ($_POST['_statusant_'] != 'APROVADO' && $_SESSION["arrpostbuffer"]["1"]['u']["prativ"]["status"] == 'APROVADO')
{
    $idprativ = $_SESSION["arrpostbuffer"]["1"]['u']["prativ"]["idprativ"];
    $aPrativ = array();

    $subtipoamostra = PrativController::buscarSqlSubtipoamostraPorIdPrativ($idprativ);
    $aPrativ["prativ"]["sql"] = $subtipoamostra;
	$aPrativ["prativ"]["res"] = sql2array($subtipoamostra, true);

    $sqlPrativobj = PrativController::buscarSqlObjetoPorTipoObjetoEIdPrativ(2, 'tagtipo', $idprativ);
    $aPrativ["prativsala"]["sql"] = $sqlPrativobj;
    $aPrativ["prativsala"]["res"] = sql2array($sqlPrativobj, true);

	//prativobjcampos
    $sqlPrativobjcampos = PrativController::buscarSqlPrativOpcaoPorTipo('prativopcao', $idprativ, "'camposconclusao', 'bioterio'");
	$aPrativ["prativobjcampos"]["sql"] = $sqlPrativobjcampos;
	$aPrativ["prativobjcampos"]["res"] = sql2array($sqlPrativobjcampos,true,array(),true);

    $sqlPrativobj = PrativController::buscarSqlPrativObjPorTipoEIdPrativ(1, 'tagtipo', $idprativ);;
    $aPrativ["prativobjtagtipo"]["sql"] =$sqlPrativobj;
    $aPrativ["prativobjtagtipo"]["res"] = sql2array($sqlPrativobj, true, array(), true);

    $sqlProdserv = PrativController::buscarSqlPrativObjPorTipoObjeto('prodserv', $idprativ, 'SERVICO');
    $aPrativ["prativobjteste"]["sql"] = $sqlProdserv;
	$aPrativ["prativobjteste"]["res"] = sql2array($sqlProdserv,true,array(),true);

    $sqlCtrlproc = PrativController::buscarSqlATividadesPorIdPrativETipoObjeto('ctrlproc', $idprativ);
    $aPrativ["prativobjctrl"]["sql"] = $sqlCtrlproc;
    $aPrativ["prativobjctrl"]["res"] = sql2array($sqlCtrlproc, true, array(), true);

    $sqlMateriais = PrativController::buscarSqlATividadesPorIdPrativETipoObjeto('materiais', $idprativ);
    $aPrativ["prativobjmat"]["sql"] = $sqlMateriais;
    $aPrativ["prativobjmat"]["res"] = sql2array($sqlMateriais, true, array(), true);

    $arrayObjetoJson = [
        "idempresa" => cb::idempresa(),
        "idobjeto" => $idprativ,
        "tipoobjeto" => 'prativ',
        "jobjeto" => base64_encode(serialize($aPrativ)),
        "versaoobjeto" => $_SESSION["arrpostbuffer"]["1"]['u']["prativ"]["versao"],
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
        "idobjeto" => $idprativ,
        "coluna" => 'jobjeto',
        "valor" => base64_encode(serialize($aPrativ)),
        "criadopor" => $_SESSION["SESSAO"]["USUARIO"],
        "tela" => $_SERVER["HTTP_REFERER"]
    ];
    FormulaProcessoController::inserirAuditoria($arrayAuditoria);
}
?>