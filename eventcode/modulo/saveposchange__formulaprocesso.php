<?

$idprodserformula = $_SESSION['arrpostbuffer']['psf1']['u']['prodservformula']['idprodservformula'];
if (!empty($idprodserformula)) 
{
	$idprodserv = traduzid('prodservformula', 'idprodservformula', 'idprodserv', $idprodserformula);
	$_listarFormula =  FormulaProcessoController::buscarProdServFormulaPorIdProdServEStatus($idprodserv, 'ATIVO');
	$sqlupd = "";

	foreach($_listarFormula as $formula) 
	{
		$valoritem = 0;
		$vlr = cprod::buscavalorprodformula($formula["idprodservformula"], 1, 'N');
		if ($vlr > 0) {
			$vlr = number_format(tratanumero($vlr), 2, '.', ',');
			FormulaProcessoController::atualizarCustoArvoreProdservFormula($vlr, $formula["idprodservformula"]);
		} else {
			FormulaProcessoController::atualizarArvoreProdservFormula($formula["idprodservformula"]);
		}


		$sqld="delete from prodservformulaitem where idprodservformula=".$formula["idprodservformula"];
		d::b()->query($sqld);
	  
		$valoritem=0;
		$vlr=prodformulaitem($formula["idprodservformula"],$formula["idprodservformula"],1,'N');
	   // $prodservclass->valoritem=0;


	}
}

if (!empty($_SESSION['arrpostbuffer']['duplicar']['u']['prodservformula']['idprodservformula'])) 
{
	// duplicar formula
	$idprodservformula = $_SESSION['arrpostbuffer']['duplicar']['u']['prodservformula']['idprodservformula'];
	$lastinsert = FormulaProcessoController::inserirProdservFormulaComSelect($idprodservformula);

	// duplicar insumos da formula
    $_listarFormulaIns = FormulaProcessoController::buscarDadosProdservFormulaInsPorIdProdservFormula($idprodservformula, 'ATIVO');
	foreach($_listarFormulaIns as $insumo) 
	{	
		$arrIsumos = [
			"idempresa" => $insumo['idempresa'],
			"idprodservformula" => $lastinsert,
			"idprodserv" => $insumo['idprodserv'],
			"qtdi" => 0,
			"chkvolume" => $insumo['chkvolume'],
			"listares" => $insumo['listares'],
			"ord" => $insumo['ord'],
			"status" => 'ATIVO',
			"criadopor" => $_SESSION['SESSAO']['USUARIO'],
			"alteradopor" => $_SESSION['SESSAO']['USUARIO'],
		];		
		FormulaProcessoController::inserirProservFormulaIns($arrIsumos);
	}

	$_listarFilhosInsumos = FormulaProcessoController::buscarFilhosProdservFormulaInsPorIdProdservFormula($lastinsert, $idprodservformula);
	foreach($_listarFilhosInsumos as $filhoInsumos) 
	{
		FormulaProcessoController::inserirInsumo($filhoInsumos['idnovo'], $idprodservformula, $filhoInsumos['idantigo']);
	}
}

if (!empty($_SESSION['arrpostbuffer']['xxx']['u']['prodservformula']['idprodservformula']) && !empty($_SESSION['arrpostbuffer']['xxx']['u']['prodservformula']['editar']) && !empty($_POST['_idprodserv_'])) 
{
	$idprodserformula = $_SESSION['arrpostbuffer']['xxx']['u']['prodservformula']['idprodservformula'];
	$versaoform = $_POST['_versao_'];
	if (isset($_POST['_versao_'])) 
	{
		// FORMULA/INSUMOS
		$idprodserv = $_POST['_idprodserv_'];
		$aForm = array();
		$sqlProdServ = FormulaProcessoController::retornarSqlProdserv($idprodserv);
		$aForm['prodserv']['sql'] = $sqlProdServ;
		$aForm['prodserv']['res'] = sql2array($sqlProdServ, true);

		$arrayforms = FormulaProcessoController::listarProdservFormulaPlantel($idprodserv);
		$aForm['prodservformula']['res'] = $arrayforms[$idprodserformula];

		$arrayObjetoJson = [
			"idempresa" => $_SESSION['SESSAO']['IDEMPRESA'],
			"idobjeto" => $idprodserformula,
			"tipoobjeto" => 'prodservformula',
			"jobjeto" => base64_encode(serialize($aForm)),
			"versaoobjeto" => $versaoform,
			"criadopor" => $_SESSION['SESSAO']['USUARIO'],
			"alteradopor" => $_SESSION['SESSAO']['USUARIO'],
		];
		FormulaProcessoController::inserirObjetoJson($arrayObjetoJson);

		$arrayAuditoria = [
			"idempresa" => $_SESSION['SESSAO']['IDEMPRESA'],
			"linha" => 1,
			"acao" => 'i',
			"objeto" => 'objetojson',
			"idobjeto" => $idprodserformula,
			"coluna" => 'jobjeto',
			"valor" => base64_encode(serialize($aForm)),
			"criadopor" => $_SESSION["SESSAO"]["USUARIO"],
			"tela" => $_SERVER["HTTP_REFERER"]
		];
		FormulaProcessoController::inserirAuditoria($arrayAuditoria);

		//PROCESSO/INSUMOS
		$aPROCIN = array();
		$sqlProcessos = FormulaProcessoController::retornarProcessosPorIdProdserv($idprodserv);
		$aPROCIN['prodservprproc']['sql'] = $sqlProcessos;
		$aPROCIN['prodservprproc']['res'] = sql2array($sqlProcessos, true, array(), true);

		foreach($aPROCIN['prodservprproc']['res'] as $key => $value) 
		{
			$sqlPrativ = FormulaProcessoController::buscarSqlProcessosPorIdProdservPrProc($value['idprodservprproc']);
			$aPROCIN['prodservprproc']['res']['idprodservprproc'][$value['idprodservprproc']]['sql'] = $sqlPrativ;
			$aPROCIN['prodservprproc']['res']['idprodservprproc'][$value['idprodservprproc']]['res'] = sql2array($sqlPrativ, true, array(), true);
			foreach ($aPROCIN['prodservprproc']['res']['idprodservprproc'][$value['idprodservprproc']]['res'] as $k => $val) 
			{
				$insumos = FormulaProcessoController::buscarInsumosEFormulasProdsev($val['idprativ']);
				if($insumos['qtdLinhas'] > 0) 
				{
					$aPROCIN['prodservprproc']['res']['idprodservprproc'][$value['idprodservprproc']]['res']['prativ'][$val['idprativ']]['sql'] =  $insumos['sql'];
					$aPROCIN['prodservprproc']['res']['idprodservprproc'][$value['idprodservprproc']]['res']['prativ'][$val['idprativ']]['res'] = sql2array($insumos['sql'], true, array(), true);
				}
			}

			$arrayObjetoJson = [
				"idempresa" => $_SESSION['SESSAO']['IDEMPRESA'],
				"idobjeto" => $value['idprodservprproc'],
				"tipoobjeto" => 'prodservprproc',
				"jobjeto" => base64_encode(serialize($aPROCIN)),
				"versaoobjeto" => $value['versao'],
				"criadopor" => $_SESSION['SESSAO']['USUARIO'],
				"alteradopor" => $_SESSION['SESSAO']['USUARIO'],
			];
			FormulaProcessoController::inserirObjetoJson($arrayObjetoJson);

			$arrayAuditoria = [
				"idempresa" => $_SESSION['SESSAO']['IDEMPRESA'],
				"linha" => 1,
				"acao" => 'i',
				"objeto" => 'objetojson',
				"idobjeto" => $value['idprodservprproc'] ,
				"coluna" => 'jobjeto',
				"valor" => base64_encode(serialize($aPROCIN)),
				"criadopor" => $_SESSION["SESSAO"]["USUARIO"],
				"tela" => $_SERVER["HTTP_REFERER"]
			];
			FormulaProcessoController::inserirAuditoria($arrayAuditoria);

			FormulaProcessoController::atualizarVersaoProdservPrProc($value['idprodservprproc']);
		}
		//PROCESSO/INSUMOS
	}
}



function prodformulaitem($inidprodservformulapai,$inidprodservformula, $percentagem, $detalhado, $lvl = 0, $linha = 0, $principal = 0, $nivel = 0, $lvl_old = 0)
{
	global $excel;

	if ($lvl > 0) {
		$m = $lvl * 15;
		$margin = "margin-left:".$m."px;";
	} else {
		$margin = "";
	}
	global $valoritem;
	if($lvl == 0){
		$valoritem = 0;
	}
	
	$sql = "SELECT * FROM (SELECT p.idempresa,
								  i.idprodservformulains,
								  i.qtdi,
								  i.qtdi_exp,
								  i.idprodserv,
								  p.fabricado,
								  p.descr,
								  CONCAT(fi.rotulo, ' ', IFNULL(fi.dose, ' '), ' ', p.conteudo, ' ', ' (', fi.volumeformula, ' ', fi.un, ')') AS rotulo,
								  p.un,
								  fi.idprodservformula,
								  IFNULL(((i.qtdi / fi.qtdpadraof) * '$percentagem'), 1) AS perc
							 FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
							 JOIN prodserv p ON (p.idprodserv = i.idprodserv)
							 JOIN prodservformula fi ON (fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv AND (fi.idplantel = f.idplantel))
							WHERE f.idprodservformula = '$inidprodservformula' 
					 UNION SELECT p.idempresa,
								  i.idprodservformulains,
								  i.qtdi,
								  i.qtdi_exp,
								  i.idprodserv,
								  p.fabricado,
								  p.descr,
								  CONCAT(fi.rotulo, ' ', IFNULL(fi.dose, ' '), ' ', p.conteudo, ' ', ' (', fi.volumeformula, ' ', fi.un, ')') AS rotulo,
								  p.un,
								  fi.idprodservformula,
								  IFNULL(((i.qtdi / fi.qtdpadraof) * '$percentagem'), 1) AS perc
							 FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
							 JOIN prodserv p ON (p.idprodserv = i.idprodserv) 
							 JOIN prodservformula fi ON (fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv  AND (fi.idplantel IS NULL OR fi.idplantel = ''))
							WHERE f.idprodservformula = '$inidprodservformula'
							  AND NOT EXISTS(SELECT 1 FROM prodservformula fi2 WHERE fi2.status = 'ATIVO' AND fi2.idprodserv = i.idprodserv AND (fi2.idplantel IS NOT NULL)) 
					 UNION SELECT p.idempresa,
								  i.idprodservformulains,
								  i.qtdi,
								  i.qtdi_exp,
								  i.idprodserv,
								  p.fabricado,
								  p.descr,
								  '' AS rotulo,
								  p.un,
								  NULL,
								  IFNULL(((i.qtdi / 1) * '$percentagem'), 1) AS perc
							 FROM prodservformula f JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND i.qtdi > 0 AND i.status = 'ATIVO')
							 JOIN prodserv p ON (p.idprodserv = i.idprodserv)
							WHERE f.idprodservformula = '$inidprodservformula' AND NOT EXISTS(SELECT 1 FROM prodservformula fi WHERE fi.status = 'ATIVO' AND fi.idprodserv = i.idprodserv)) AS u
						 GROUP BY idprodservformulains
						 ORDER BY fabricado";

	$res = d::b()->query($sql);
	
	while ($row = mysqli_fetch_assoc($res)) {
		$linha = $linha + 1;            

		// Concatena os contadores dos níveis para formar $nivel
		if($lvl == 0){
			cb::$session["nivel_old"] = $nivel;
			$nivel = $nivel + 1;
			$negritoInicial = '<b>';
			$negritoFinal = '</b>';
		} else {
			$arrayNivel = explode('.', $nivel);
			$contador = count($arrayNivel);
			if($lvl_old <> $lvl){
				$nivel = $nivel.'.1';
			} else {
				$arrayNivel[$contador - 1]++;
				$nivel = implode('.', $arrayNivel);
			}
		}

		if ($row['fabricado'] == 'Y' and !empty($row['idprodservformula'])) {

		  $valorQtd = tratanumero($row['qtdi'] * $percentagem);
							 

		  $sqli="INSERT INTO prodservformulaitem 
		  (idempresa,idprodservformula,idprodserv,descr,nivel,qtd,qtd_exp,un,fabricado,valorun,valortotal )
		  VALUES
		  (".$row['idempresa'].",".$inidprodservformulapai.",".$row['idprodserv'].",'".$row['descr'] ."','".$lvl ."','".$valorQtd."','".$row['qtdi_exp']."','".$row['un']."','Y',0,0);";
		  //echo($sqli);
		  $resf=d::b()->query($sqli);

			

		  $lvl_old = $lvl;
		  prodformulaitem($inidprodservformulapai,$row['idprodservformula'], $row['perc'], $detalhado, $lvl + 1, $linha, 1, $nivel, $lvl_old);
		 
		} elseif ($row['fabricado'] == 'N') {
		  $valor = buscavaloritem($row['idprodserv'], $row['qtdi']);

		  $valorun = buscavalorloteprod($row['idprodserv'],1);
		  $valor = $valor * $percentagem;
		   
		  $valorQtd = tratanumero($row['qtdi'] * $percentagem);

		  $sqli="INSERT INTO prodservformulaitem 
		  (idempresa,idprodservformula,idprodserv,descr,nivel,qtd,qtd_exp,un,fabricado,valorun,valortotal)
		  VALUES
		  (".$row['idempresa'].",".$inidprodservformulapai.",".$row['idprodserv'].",'".$row['descr'] ."','".$lvl ."','".$valorQtd."','".$row['qtdi_exp']."','".$row['un']."','N','". $valorun ."','". $valor ."');";
		 // echo($sqli);
		  $resf=d::b()->query($sqli);

		  $lvl_old = $lvl;
		}
	} //while($row=mysqli_fetch_assoc($res)){

	return  number_format(tratanumero($valoritem), 4, ',', '.');
} //function buscarvalorform($inidprodservformula,$inidplantel){


	function buscavaloritem($inidprodserv, $qtdi)
    {

        $sql = "select ifnull(l.vlrlote,0) as  valoritem,l.idlote 
        from lote l 
        where l.idprodserv = ".$inidprodserv." and vlrlote > 0  order by idlote desc limit 1";
        $res = d::b()->query($sql);
        $row = mysqli_fetch_assoc($res);
        $valor = round(($qtdi * $row['valoritem']), 4);
        return $valor;
    }

    function  buscavalorloteprod($inidprodserv,$qtdi=1)
    {

        $sql = "select ifnull(l.vlrlote,0) as  valoritem,l.idlote 
        from lote l 
        where l.idprodserv = ".$inidprodserv."  and vlrlote > 0   order by idlote desc limit 1";
        $res = d::b()->query($sql);
        $row = mysqli_fetch_assoc($res);
        $vlri=$row['valoritem']*$qtdi;
        $valor = round(($vlri), 4);
        return $valor;
    }