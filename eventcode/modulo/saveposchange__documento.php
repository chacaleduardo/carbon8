<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");


if ($this->BUFFER['1']['i']['sgdoc']['idsgdoctipo'] or $this->BUFFER['x']['i']['sgdoc']['idsgdoctipo'] or (!empty($this->BUFFER['xxxx']['u']['sgdoc']['idsgdoc']) && !empty($this->BUFFER['xxxx']['u']['sgdoc']['idsgdoctipodocumento']) && $_POST["_qst_"])) {

	$_POST['_NEW_DOC']=(!empty($this->scriptsSql['x']['sgdoc']['insertid']))?$this->scriptsSql['x']['sgdoc']['insertid']:$this->scriptsSql['1']['sgdoc']['insertid'];

	if($this->BUFFER['1']['i']['sgdoc']['idsgdoctipo'] or $this->BUFFER['x']['i']['sgdoc']['idsgdoctipo']){
		$_CMD = new cmd();

		$res = $_CMD->save([
			"_doc_i_fluxostatuspessoa_idpessoa" => $_SESSION['SESSAO']['IDPESSOA'],
			"_doc_i_fluxostatuspessoa_idmodulo" => $_POST['_NEW_DOC'],
			"_doc_i_fluxostatuspessoa_modulo" => 'documento',
			"_doc_i_fluxostatuspessoa_idobjeto" => $_SESSION['SESSAO']['IDPESSOA'],
			"_doc_i_fluxostatuspessoa_tipoobjeto" => 'pessoa',
			"_doc_i_fluxostatuspessoa_inseridomanualmente" => 'N',
			"_doc_i_fluxostatuspessoa_assinar" => 'X',
			"_doc_i_fluxostatuspessoa_editar" => 'Y',
		]);
		if(!$res){
			die($_CMD->erro);
		}
		if(getUnidadePadraoModulo($_GET['_modulo'],cb::idempresa())){
			$_CMD_UN = new cmd();

			$res_un = $_CMD_UN->save([
				"_uni_u_sgdoc_idunidade" => getUnidadePadraoModulo($_GET['_modulo'],cb::idempresa()),
				"_uni_u_sgdoc_idsgdoc" => $_POST['_NEW_DOC'],
			]);
			if(!$res_un){
				die($_CMD_UN->erro);
			}
		}
	}

	if ((!empty($this->BUFFER['xxxx']['u']['sgdoc']['idsgdoc']) && !empty($this->BUFFER['xxxx']['u']['sgdoc']['idsgdoctipodocumento']) && $_POST["_qst_"]) or ($this->BUFFER['x']['i']['sgdoc']['idsgdoctipodocumento'] && $_POST["_qst_"] == 'Y')) {

		$idsgdoc = (!empty($_POST['_NEW_DOC']))?$_POST['_NEW_DOC']:$this->BUFFER['xxxx']['u']['sgdoc']['idsgdoc'];

		$_tipo= (empty($this->BUFFER['xxxx']['u']['sgdoc']['idsgdoctipodocumento']))?$this->BUFFER['x']['i']['sgdoc']['idsgdoctipodocumento']:$this->BUFFER['xxxx']['u']['sgdoc']['idsgdoctipodocumento'];


		$sql = "SELECT * from sgdocpagtemplate where idsgdoctipodocumento=".$_tipo." order by pagina asc";
		$r = d::b()->query($sql) or die('Erro ao buscar template. ->'.mysqli_error(d::b()). "<p>SQL: $sql");
		if (mysqli_num_rows($r) > 0) {
			$arr = array();
			$_cmd_insert= new cmd();
			$i=0;
			while ($row = mysqli_fetch_assoc($r)) {
			    $n = str_pad($i, 2, '0', STR_PAD_LEFT);
				$arr["_inspag".$n."_i_sgdocpag_idsgdoc"] = $idsgdoc;
				$arr["_inspag".$n."_i_sgdocpag_pagina"] = $row['pagina'];
				$arr["_inspag".$n."_i_sgdocpag_conteudo"] = $row['conteudo'];
				$arr["_inspag".$n."_i_sgdocpag_conteudotxt"] = $row['conteudotxt'];
				$arr["_inspag".$n."_i_sgdocpag_marcadores"] = $row['marcadores'];
				$arr["_inspag".$n."_i_sgdocpag_resposta"] = $row['resposta'];
				$arr["_inspag".$n."_i_sgdocpag_resposta2"] = $row['resposta2'];
				$arr["_inspag".$n."_i_sgdocpag_resposta3"] = $row['resposta3'];
				$arr["_inspag".$n."_i_sgdocpag_resultado"] = $row['resultado'];
				$arr["_inspag".$n."_i_sgdocpag_conclusao"] = $row['conclusao'];
				$arr["_inspag".$n."_i_sgdocpag_descricao"] = $row['descricao'];
				$arr["_inspag".$n."_i_sgdocpag_descricao2"] = $row['descricao2'];
				$arr["_inspag".$n."_i_sgdocpag_descricao3"] = $row['descricao3'];
				$arr["_inspag".$n."_i_sgdocpag_nota"] = $row['nota'];
				$arr["_inspag".$n."_i_sgdocpag_paginaold"] = $row['paginaold'];
				$arr["_inspag".$n."_i_sgdocpag_classificacao"] = $row['classificacao'];
				$arr["_inspag".$n."_i_sgdocpag_observacao"] = $row['observacao'];
				$i++;
				$res = $_cmd_insert->save($arr);
				unset($arr);
				$arr = array();
			}
			//$res = $_cmd_insert->save($arr);
			if(!$res){
				cbSetPostHeader("0","erro");
				var_dump($arr);
				die($_cmd_insert->erro);
			}
			unset($arr);
			
		}
		
	}
	if($_GET['idsgdoccp']){
		$sql = 'SELECT * from sgdocpag where idsgdoc = '.$_GET['idsgdoccp'];
		$r = d::b()->query($sql) or die('Erro ao buscar template. ->'.mysqli_error(d::b()). "<p>SQL: $sql");
		$i = 0;
		$_cmd_insert= new cmd();
		$idsgdoc = (!empty($_POST['_NEW_DOC']))?$_POST['_NEW_DOC']:$this->BUFFER['xxxx']['u']['sgdoc']['idsgdoc'];
		while($row = mysqli_fetch_assoc($r)){
			$n = str_pad($i, 2, '0', STR_PAD_LEFT);
			$arr["_inspag".$n."_i_sgdocpag_idsgdoc"] = $idsgdoc;
			$arr["_inspag".$n."_i_sgdocpag_pagina"] = $row['pagina'];
			$arr["_inspag".$n."_i_sgdocpag_conteudo"] = $row['conteudo'];
			$arr["_inspag".$n."_i_sgdocpag_conteudotxt"] = $row['conteudotxt'];
			$arr["_inspag".$n."_i_sgdocpag_marcadores"] = $row['marcadores'];
			$arr["_inspag".$n."_i_sgdocpag_resposta"] = $row['resposta'];
			$arr["_inspag".$n."_i_sgdocpag_resposta2"] = $row['resposta2'];
			$arr["_inspag".$n."_i_sgdocpag_resposta3"] = $row['resposta3'];
			$arr["_inspag".$n."_i_sgdocpag_resultado"] = $row['resultado'];
			$arr["_inspag".$n."_i_sgdocpag_conclusao"] = $row['conclusao'];
			$arr["_inspag".$n."_i_sgdocpag_descricao"] = $row['descricao'];
			$arr["_inspag".$n."_i_sgdocpag_descricao2"] = $row['descricao2'];
			$arr["_inspag".$n."_i_sgdocpag_descricao3"] = $row['descricao3'];
			$arr["_inspag".$n."_i_sgdocpag_nota"] = $row['nota'];
			$arr["_inspag".$n."_i_sgdocpag_paginaold"] = $row['paginaold'];
			$arr["_inspag".$n."_i_sgdocpag_classificacao"] = $row['classificacao'];
			$arr["_inspag".$n."_i_sgdocpag_observacao"] = $row['observacao'];
			$i++;
		}
		$res = $_cmd_insert->save($arr);
		if(!$res){
			cbSetPostHeader("0","erro");
			var_dump($arr);
			die($_cmd_insert->erro);
		}
	}
}

if (!empty($_POST['_vinculo_'])) {
	$_CMD2 = new cmd();
	$res1 = $_CMD2->save([
		'_xv_i_sgdocvinc_idsgdoc' => $_POST['_vinculo_'],
		'_xv_i_sgdocvinc_iddocvinc' => $_POST['_NEW_DOC'],
		'_xv_i_sgdocvinc_idempresa' => cb::idempresa(),
		'_xv_i_sgdocvinc_versao' => $_POST['_versao_'],
	]);
	if(!$res1){
		die($_CMD2->erro);
	}
}
	

if(!empty($_POST["_copiardoc_"])){
	$idsgdoc 	= $_POST["_copiardoc_"];
	
	$sql="select d.*,count(dd.idsgdoc) as qtdcp 
				from sgdoc d left join sgdoc dd on(dd.idsgdoccopia = d.idsgdoc and d.versao =dd.versao)
				where d.idsgdoc= ".$idsgdoc." group by d.idsgdoc";
	$res= d::b()->query($sql) or die("Erro ao buscar tipo do documento: ".mysqli_error(d::b())." SQL=".$sql);
	$row= mysqli_fetch_assoc($res);

	$_idregistro=$row['idregistro'];
	$versao=$row['versao'];
	$revisao=$row['versao'];
	$copia=$row['qtdcp']+1;
	/*
		* Insere linha
	*/

	$sqli = "SELECT 
				idsgdoc as idsgdoccopia,
				idunidade,
				titulo,
				idsgdoctipo,					
				tipoacesso,
				conteudo				
			from sgdoc 
			where idsgdoc = ".$idsgdoc;

	$resi= d::b()->query($sqli) or die("Erro ao copiar documento: ".mysqli_error(d::b())." SQL=".$sqli);


	if(mysqli_num_rows($resi) > 0){

		//LTM - 28-04-2021: Retorna o Idfluxo Amostra
		$ri = mysqli_fetch_assoc($resi);
		$idfluxostatus = FluxoController::getIdFluxoStatus('documento', 'AGUARDANDO', $ri["idsgdoccopia"]);

		$_CMD = new cmd();
		$res = $_CMD->save([
			"_doc_i_sgdoc_idsgdoccopia" => $ri["idsgdoccopia"],
			"_doc_i_sgdoc_idregistro" => $_idregistro,
			"_doc_i_sgdoc_idunidade" => $ri["idunidade"],
			"_doc_i_sgdoc_titulo" => $ri["titulo"],
			"_doc_i_sgdoc_idpessoa" => $_SESSION["SESSAO"]["IDPESSOA"],
			"_doc_i_sgdoc_idsgdoctipo" => $ri["idsgdoctipo"],
			"_doc_i_sgdoc_versao" => $versao,
			"_doc_i_sgdoc_revisao" => $revisao,
			"_doc_i_sgdoc_copia" => $copia,
			"_doc_i_sgdoc_status" => 'AGUARDANDO',
			"_doc_i_sgdoc_idfluxostatus" => $idfluxostatus,
			"_doc_i_sgdoc_tipoacesso" => $ri["tipoacesso"],
			"_doc_i_sgdoc_conteudo" => $ri["conteudo"],
		]);

		if(!$res){
			die($_CMD->erro);
		}

		$nidsgdoc= $_CMD->insertid();

		//LTM - 28-04-2021: Insere FluxoHist Amostra        
		FluxoController::inserirFluxoStatusHist('documento', $nidsgdoc, $idfluxostatus, 'PENDENTE');
			
		/*
			* COPIAR AS PAGINAS DO DOCUMENTO PARA O DOCUMENTO ATUAL
		*/
		
		$sqlinsereitens = "SELECT `pagina` as pagina,
			`conteudo` as conteudo								
			FROM sgdocpag where idsgdoc = ".$idsgdoc;
		$resa = d::b()->query($sqlinsereitens) or die("A insercão das paginas do documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $sqlinsereitens");								

		if(mysqli_num_rows($resa) > 0){
			$arr = array();
			$i=0;

			// Acumula todos em um array e executar todos de uma vez
			while($r = mysqli_fetch_assoc($resa)){
				$arr["_docp".$i."_i_sgdocpag_idsgdoc"] = $nidsgdoc;
				$arr["_docp".$i."_i_sgdocpag_pagina"] = $r["pagina"];
				$arr["_docp".$i."_i_sgdocpag_conteudo"] = $r["conteudo"];
				$i++;
			}

			$res = $_CMD->save($arr);

			if(!$res){
				die($_CMD->erro);
			}
		}

		$_CMD = new cmd();

	$res = $_CMD->save([
		"_doc_i_fluxostatuspessoa_idpessoa" => $_SESSION['SESSAO']['IDPESSOA'],
		"_doc_i_fluxostatuspessoa_idmodulo" =>  $nidsgdoc,
		"_doc_i_fluxostatuspessoa_modulo" => 'documento',
		"_doc_i_fluxostatuspessoa_idobjeto" => $_SESSION['SESSAO']['IDPESSOA'],
		"_doc_i_fluxostatuspessoa_tipoobjeto" => 'pessoa',
		"_doc_i_fluxostatuspessoa_inseridomanualmente" => 'N',
		"_doc_i_fluxostatuspessoa_assinar" => 'Y',
	]);
	if(!$res){
		die($_CMD->erro);
	}
	$this->BUFFER["x"]["i"]["fluxostatuspessoa"]["idpessoa"] = $_SESSION["SESSAO"]["IDPESSOA"];
	$this->BUFFER["x"]["i"]["fluxostatuspessoa"]["idobjeto"] = 10363;
	$this->BUFFER["x"]["i"]["fluxostatuspessoa"]["tipoobjeto"] = 'imgrupo';
	$this->BUFFER["x"]["i"]["fluxostatuspessoa"]["idmodulo"] =  $nidsgdoc;
	$this->BUFFER["x"]["i"]["fluxostatuspessoa"]["modulo"] = 'documento';
	$this->BUFFER["x"]["i"]["fluxostatuspessoa"]["inseridomanualmente"] = 'N';
	$this->BUFFER["x"]["i"]["fluxostatuspessoa"]["assinar"] = 'N';



	$sqlinsereitens = "SELECT gp.idpessoa, g.idimgrupo, 'pessoa' as tipoobjeto from imgrupopessoa gp join imgrupo g on (g.idimgrupo = gp.idimgrupo) where g.idimgrupo = ".$this->BUFFER["x"]["i"]["fluxostatuspessoa"]["idobjeto"];
	$resa = d::b()->query($sqlinsereitens) or die("A insercão das paginas do documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $sqlinsereitens");								

	if(mysqli_num_rows($resa) > 0){
		$arr = array();
		$i=0;
		$_CMD1 = new cmd();

		
		// Acumula todos em um array e executar todos de uma vez
		while($r = mysqli_fetch_assoc($resa)){

			$sqve="select idobjeto from fluxostatuspessoa where modulo = 'documento' and idmodulo = ".$this->BUFFER["x"]["i"]["fluxostatuspessoa"]["idmodulo"]."  and idobjeto=".$r['idpessoa'];
			$rv = d::b()->query($sqve) or die("A insercão das paginas do documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $sqve");								
			if(mysqli_num_rows($rv) < 1){							
				$arr["_xf".$i."_i_fluxostatuspessoa_idmodulo"] = $this->BUFFER["x"]["i"]["fluxostatuspessoa"]["idmodulo"];
				$arr["_xf".$i."_i_fluxostatuspessoa_modulo"] = $this->BUFFER["x"]["i"]["fluxostatuspessoa"]["modulo"];
				$arr["_xf".$i."_i_fluxostatuspessoa_idpessoa"] = $_SESSION["SESSAO"]["IDPESSOA"];
				$arr["_xf".$i."_i_fluxostatuspessoa_idobjeto"] = $r["idpessoa"];
				$arr["_xf".$i."_i_fluxostatuspessoa_idimgrupo"] = $r["idimgrupo"];
				$arr["_xf".$i."_i_fluxostatuspessoa_tipoobjeto"] = $r['tipoobjeto'];
				$arr["_xf".$i."_i_fluxostatuspessoa_inseridomanualmente"] = "N";
				$arr["_xf".$i."_i_fluxostatuspessoa_assinar"] = "Y";
				/*
				$arr["_xc".$i."_i_carrimbo_idpessoa"] = $r['idpessoa'];
				$arr["_xc".$i."_i_carrimbo_tipoobjeto"] = $this->BUFFER["x"]["i"]["fluxostatuspessoa"]["modulo"];
				$arr["_xc".$i."_i_carrimbo_idobjeto"] = $this->BUFFER["x"]["i"]["fluxostatuspessoa"]["idmodulo"];
				$arr["_xc".$i."_i_carrimbo_versao"] = $versao["versao"];
				*/
				$i++;
			}
		}

		$res = $_CMD1->save($arr);
		if(!$res){
			unset($this->BUFFER["x"]["i"]["fluxostatuspessoa"]);
			die($_CMD1->erro);
		}
		//unset($this->BUFFER["x"]["i"]["fluxostatuspessoa"]);
	}

		header("lastinsertid: ".$nidsgdoc);
	}
}
$statusant=$_POST['statusant'];
if(($this->BUFFER["1"]["u"]["sgdoc"]["status"] != $statusant) or ($this->BUFFER["1"]["i"]["sgdoc"]["status"] != $statusant) or ($this->BUFFER["1"]["i"]["sgdoc"]["status"] != $statusant)){
	if (empty($this->BUFFER["1"]["u"]["sgdoc"]["idsgdoc"])) {
		$idsgdoc = $this->scriptsSql['1']['sgdoc']['insertid'];
		$idfluxostatus = FluxoController::getIdFluxoStatus('documento', $this->BUFFER["1"]["i"]["sgdoc"]["status"], $idsgdoc) || "''";
	}else {
		$idsgdoc =$this->BUFFER["1"]["u"]["sgdoc"]["idsgdoc"];
		$idfluxostatus = FluxoController::getIdFluxoStatus('documento', $this->BUFFER["1"]["u"]["sgdoc"]["status"], $idsgdoc) || "''";
	}

	$sqlFuncionarios = "SELECT mfo.idobjeto,
							   mfo.tipoobjeto,
								mfo.inidstatus 
						   FROM fluxo ms JOIN fluxoobjeto mfo ON mfo.idfluxo = ms.idfluxo
						   		AND mfo.idobjeto NOT IN (SELECT idobjeto FROM fluxostatuspessoa WHERE idmodulo = ".$idsgdoc." AND modulo = 'documento')
						  WHERE tipo = 'PARTICIPANTE' 
								AND ms.idobjeto = '".$this->BUFFER["1"]["u"]["sgdoc"]["idsgdoctipo"]."' AND ms.modulo = 'documento' AND ms.status = 'ATIVO'
								AND mfo.inidstatus = ".$idfluxostatus."
								".getidempresa('ms.idempresa', 'fluxo');
	
	$resf = d::b()->query($sqlFuncionarios) or die("A insercão das pessoas pelo fluxo documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $sqlFuncionarios");
	if(mysqli_num_rows($resf) > 0){
		while ($rwf = mysqli_fetch_assoc($resf)) {
			if ($rwf['tipoobjeto'] == 'pessoa') {
				$_CMD = new cmd();

				$res = $_CMD->save([
					"_doc_i_fluxostatuspessoa_idpessoa" => $_SESSION['SESSAO']['IDPESSOA'],
					"_doc_i_fluxostatuspessoa_idmodulo" => $idsgdoc,
					"_doc_i_fluxostatuspessoa_modulo" => 'documento',
					"_doc_i_fluxostatuspessoa_idobjeto" => $rwf['idobjeto'],
					"_doc_i_fluxostatuspessoa_tipoobjeto" => 'pessoa',
					"_doc_i_fluxostatuspessoa_inseridomanualmente" => 'N',
					"_doc_i_fluxostatuspessoa_assinar" => 'Y',
				]);
				
				if(!$res){
					die($_CMD->erro);
				}
			}else {
				$sqlinsereitens = "SELECT gp.idpessoa, g.idimgrupo, 'pessoa' as tipoobjeto from imgrupopessoa gp join imgrupo g on (g.idimgrupo = gp.idimgrupo) where g.idimgrupo = ".$rwf['idobjeto'];
				$resa = d::b()->query($sqlinsereitens) or die("A insercão das paginas do documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $sqlinsereitens");								
			
				if(mysqli_num_rows($resa) > 0){
					$arr = array();
					$i=0;
					$_CMD = new cmd();
			
					
					// Acumula todos em um array e executar todos de uma vez
					while($r = mysqli_fetch_assoc($resa)){
			
						$sqve="select idobjeto from fluxostatuspessoa where modulo = 'documento' and idmodulo = ".$idsgdoc."  and idobjeto=".$r['idpessoa'];
						$rv = d::b()->query($sqve) or die("A insercão das paginas do documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $sqve");								
						if(mysqli_num_rows($rv) < 1){							
							$arr["_xf".$i."_i_fluxostatuspessoa_idmodulo"] = $idsgdoc;
							$arr["_xf".$i."_i_fluxostatuspessoa_modulo"] = 'documento';
							$arr["_xf".$i."_i_fluxostatuspessoa_idpessoa"] = $_SESSION["SESSAO"]["IDPESSOA"];
							$arr["_xf".$i."_i_fluxostatuspessoa_idobjeto"] = $r["idpessoa"];
							$arr["_xf".$i."_i_fluxostatuspessoa_idimgrupo"] = $r["idimgrupo"];
							$arr["_xf".$i."_i_fluxostatuspessoa_tipoobjeto"] = $r['tipoobjeto'];
							$arr["_xf".$i."_i_fluxostatuspessoa_inseridomanualmente"] = "N";
							$arr["_xf".$i."_i_fluxostatuspessoa_assinar"] = "Y";
							$i++;
						}
					}
					if(!empty($arr)){
						$res = $_CMD->save($arr);
						if(!$res){
						unset($this->BUFFER["x"]["i"]["fluxostatuspessoa"]);
						die($_CMD->erro);
						}
					}
					
					//unset($this->BUFFER["x"]["i"]["fluxostatuspessoa"]);
				}
			}
		}

	}
	
}

if ($_POST['statusant'] != 'APROVADO' and $this->BUFFER["1"]["u"]["sgdoc"]["status"] == 'APROVADO') {

	$sqlversao = 'update sgdoc set versao = versao + 1, revisao = 0 where idsgdoc='.$this->BUFFER["1"]["u"]["sgdoc"]["idsgdoc"];
	$rv = d::b()->query($sqlversao) or die("Falha ao alterar versão: " .mysqli_error(d::b()) . "<p>SQL: $sqlversao");

	$sqlupd = 'update sgdocupd set status = "OBSOLETO" where status="APROVADO" and idsgdoc='.$this->BUFFER["1"]["u"]["sgdoc"]["idsgdoc"];
	$rupd = d::b()->query($sqlupd) or die("Falha sgdocupd status: " .mysqli_error(d::b()) . "<p>SQL: $sqlupd");



	$sqlsgupd = "INSERT INTO `sgdocupd` (`idsgdoc`,idempresa,`titulo`, idregistro,`idsgdoctipo`,idsgdoctipodocumento,idpessoa,responsavel, `versao`,`revisao`, `status`
		, `conteudo`,`acompversao`,inicio,fim,nota,resultado,observacao,`criadopor`, `criadoem`, `alteradopor`, `alteradoem`)     
	(SELECT `idsgdoc`,idempresa,`titulo`,idregistro, `idsgdoctipo`,idsgdoctipodocumento,idpessoa,responsavel, versao,revisao, 'APROVADO'
		, `conteudo`,acompversao,inicio,fim,nota,resultado,observacao,`criadopor`, `criadoem`, `alteradopor`, `alteradoem`
	FROM sgdoc
	WHERE idsgdoc =".$this->BUFFER['1']['u']['sgdoc']['idsgdoc']." )";
	$rsgupd = d::b()->query($sqlsgupd) or die("Falha sgdocupd all: " .mysqli_error(d::b()) . "<p>SQL: $sqlsgupd");
	
		$sqlflag = 'select flquestionario from sgdoctipodocumento where idsgdoctipodocumento='.$this->BUFFER['1']['u']['sgdoc']['idsgdoctipodocumento'];
		$resfl = d::b()->query($sqlflag) or die("Falha recuperar flquestionario: " .mysqli_error(d::b()) . "<p>SQL: $sqlflag");
	$rowfl = mysqli_fetch_assoc($resfl);
		if($rowfl['flquestionario']=='Y') {
		
			$sqlsgupdpag = "INSERT INTO `laudo`.`sgdocpagupd`
				(`idsgdoc`,versao,`idempresa`,`pagina`,`conteudo`,  `resposta` , `resultado`, `classificacao`, `observacao` , `conclusao`  , `descricao` , `nota` ,`criadopor`,`criadoem`,`alteradopor`,`alteradoem`)
				(select `idsgdoc`,".($this->BUFFER['1']['u']['sgdoc']['versao']).",`idempresa`,`pagina`,`conteudo`,`resposta` , `resultado`, `classificacao`, `observacao` , `conclusao`  , `descricao` , `nota` ,`criadopor`,`criadoem`,`alteradopor`,`alteradoem`
					from  sgdocpag where idsgdoc=".$this->BUFFER['1']['u']['sgdoc']['idsgdoc'].")";
			$rsgupdpag = d::b()->query($sqlsgupdpag) or die("Falha sgdocpagupd all: " .mysqli_error(d::b()) . "<p>SQL: $sqlsgupdpag");		
				
			$sqlsgupdpagcampos = "INSERT INTO  sgdoctipodocumentocamposupd
				(idempresa,idsgdoctipodocumento,idsgdoc,versao,tabela,col,visivel,ord,criadopor,criadoem,alteradopor,alteradoem)
				(select idempresa,idsgdoctipodocumento,".$this->BUFFER['1']['u']['sgdoc']['idsgdoc'].",".($this->BUFFER['1']['u']['sgdoc']['versao']).",tabela,col,visivel,ord,criadopor,criadoem,alteradopor,alteradoem 
					from sgdoctipodocumentocampos 
					where idsgdoctipodocumento=".$this->BUFFER['1']['u']['sgdoc']['idsgdoctipodocumento'].")";
			$rsgupdpagcampos = d::b()->query($sqlsgupdpagcampos) or die("Falha sgdoccamposupd all: " .mysqli_error(d::b()) . "<p>SQL: $sqlsgupdpagcampos");		

		}


		$sql = "SELECT * from arquivo where idobjeto = ".$this->BUFFER['1']['u']['sgdoc']['idsgdoc']." and tipoobjeto='sgdoc'";
		$r = d::b()->query($sql) or die('Erro ao buscar anexos. ->'.mysqli_error(d::b()). "<p>SQL: $sql");
		while($row = mysqli_fetch_assoc($r)){

			$sql = "INSERT INTO sgdocanexo (idsgdoc, versao, tipoarquivo, caminho, nome, tamanho, idpessoa, criadoem) 
			VALUES (".$this->BUFFER['1']['u']['sgdoc']['idsgdoc'].",".(intval($this->BUFFER['1']['u']['sgdoc']['versao'])+1) .", '".$row['tipoarquivo']."', '".$row['caminho']."', '".$row['nome']."', '".$row['tamanho']."','".$row['idpessoa']."', now())";
			$res = d::b()->query($sql) or die('Erro ao inserir anexos. ->'.mysqli_error(d::b()). "<p>SQL: $sql");
		}


}
if ($_POST['statusant'] != 'OBSOLETO' and $this->BUFFER["1"]["u"]["sgdoc"]["status"] == 'OBSOLETO' and !empty($this->BUFFER["1"]["u"]["sgdoc"]["idsgdoc"])) {
	$upd = 'UPDATE carrimbo set status="INATIVO", alteradopor="'.$_SESSION["SESSAO"]["USUARIO"].'", alteradoem=now() where tipoobjeto="documento" and status="PENDENTE" and idobjeto='.$this->BUFFER["1"]["u"]["sgdoc"]["idsgdoc"].'';
	$rs = d::b()->query($upd) or die("Falha ao inativar assinaturas pendentes: " .mysqli_error(d::b()) . "<p>SQL: $upd");	
}
?>
