<?
$_ajax_d_fluxostatus_idfluxostatus = $_SESSION['arrpostbuffer']['ajax']['d']['fluxostatus']['idfluxostatus'];
$idfluxostatus                     = $_SESSION['arrpostbuffer']['x']['u']['fluxostatus']['idfluxostatus'];
$idstatus                          = $_SESSION['arrpostbuffer']['x']['u']['fluxostatus']['idstatus'];
$idstatusfluxo                     = $_POST['idfluxostatus'];
$ideventotipo                      = $_POST['ideventotipo'];
$idstatusatual                     = $_POST['idstatusatual'];
$idfluxo                           = $_POST['idfluxo'];
if (!empty($_ajax_d_fluxostatus_idfluxostatus)) {
	$sql = "SELECT idstatus 
            FROM fluxostatus
           WHERE idfluxostatus = " . $_ajax_d_fluxostatus_idfluxostatus;
	$res = d::b()->query($sql) or die("Erro ao buscar informções da configuração do status: " . mysqli_error(d::b()));
	$row = mysqli_fetch_assoc($res);
	if ($row['idstatus']) {
		$sql1 = "SELECT * FROM fluxostatus 
			    WHERE idfluxostatus = " . $_ajax_d_fluxostatus_idfluxostatus . " 
                  AND (FIND_IN_SET(" . $row['idstatus'] . ",fluxo) OR FIND_IN_SET(" . $row['idstatus'] . ", fluxoocultar));";
		$res1 = d::b()->query($sql1) or die("Erro ao buscar se status consta no fluxo: " . mysqli_error(d::b()));
		$qtd = mysqli_num_rows($res1);
		if ($qtd > 0) {
			die('É necessário retirar o status do fluxo antes de excluir o mesmo.');
		}
	}
}
if (!empty($_ajax_d_fluxostatus_idfluxostatus) || (!empty($idstatusfluxo) && !empty($idstatusatual) && !empty($idfluxostatus))) {
	$sql3 = "UPDATE evento AS e INNER JOIN fluxostatuspessoa AS er ON e.idevento = er.idmodulo AND er.modulo = 'evento' 
					JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.modulo = 'evento'
					JOIN fluxostatus mf ON ms.idfluxo = mf.idfluxo AND idfluxostatus = $idfluxostatus
				SET er.idfluxostatus = '" . $idstatusatual . "', er.alteradoem = now(), er.alteradopor = '" . $_SESSION["SESSAO"]["IDPESSOA"] . "',
					e.idstatus = '" . $idstatusatual . "', e.alteradoem = now(), e.alteradopor = '" . $_SESSION["SESSAO"]["IDPESSOA"] . "'
				WHERE er.idfluxostatus = " . $idstatusfluxo;
	$res3 = d::b()->query($sql3) or die("Erro ao fazer o UPDATE nos eventos: " . mysqli_error(d::b()));
}
/** Validação para Alteração do Status. Se o Status existir no fluxo do IdEventoTipo, aparecerá a mensagem que não pode alterar. 
 * A validação do $idfluxostatus não pode ser nulo e $idstatus ou statustipo que também não pode ser Nulo, para não dar conflito com os Fluxos.
 * Alteração Realizada em 14/01/2020 - Lidiane	
 */
if (!empty($idfluxostatus) && (!empty($idstatus))) {
	$sql = "SELECT fs.idfluxostatus, fs.idstatus, s.statustipo 
			FROM fluxostatus fs JOIN " . _DBCARBON . "._status s ON s.idstatus = fs.idstatus
			WHERE idfluxostatus = " . $idfluxostatus;
	$res = d::b()->query($sql) or die("Erro ao buscar informções da configuração do FLUXO: " . mysqli_error(d::b()));
	$row = mysqli_fetch_assoc($res);
	if (!empty($row['idstatus']) && $row['statustipo'] != 'INICIO' && (($row['statustipo'] != 'CANCELADO' OR $row['statustipo'] != 'FIM' OR $row['statustipo'] != 'CONCLUIDO')) && $row['statustipo'] != 'ASSINA' && $row['statustipo'] != 'REJEITA') {
		$sql1 = "SELECT * FROM fluxostatus 
				WHERE idfluxostatus = " . $row['idfluxostatus'] . " 
					AND (FIND_IN_SET(" . $row['idstatus'] . ",fluxo) OR FIND_IN_SET(" . $row['idstatus'] . ",fluxoocultar));";
		$res1 = d::b()->query($sql1) or die("Erro ao buscar se status consta no fluxo: " . mysqli_error(d::b()));
		$qtd = mysqli_num_rows($res1);
		if ($qtd > 0) {
			die('É necessário retirar o status do fluxo antes de Alterar o mesmo.');
		}
	}
}


//BLOCO DE CRIAÇÃO E ATUALIZAÇÃO DE DASHCARD E DASHPANEL PARA O DASHBOARD.
//VERIFICA SE JÁ FOI CRIADO UM DASHPANEL PARA O FLUXO
if ($_POST["_x_i_fluxostatus_idfluxo"]) {
	$sql = "SELECT iddashpanel FROM dashpanel WHERE idfluxo = '" . $_POST["_x_i_fluxostatus_idfluxo"] . "'";
	$res = d::b()->query($sql) or die("Falha ao pesquisar dashpanel: " . mysqli_error() . "<p>SQL: $sql");
	while ($row = mysqli_fetch_assoc($res)) {
		$iddashpanel = $row['iddashpanel'];
	}
//BUSCO DADOS QUE SERÃO USADOS PARA PREENCHER O NOME DO DASHPANEL
	$sql = "
		
		select m.rotulomenu, f.modulo, m.tab, f.tipoobjeto, idobjeto, dropsql
		from fluxo f 
		 JOIN carbonnovo._modulo m on m.modulo = f.modulo
		 LEFT JOIN  carbonnovo._mtotabcol mto on m.tab = mto.tab and mto.col = f.tipoobjeto

		 where f.idfluxo =  " . $_POST['_x_i_fluxostatus_idfluxo'] . ";
		
		";
		$res = d::b()->query($sql) or die("Falha ao pesquisar etapa dashcard: " . mysql_error() . "<p>SQL: $sql");
		while ($row = mysqli_fetch_assoc($res)) {
			$rotulomenu = $row['rotulomenu'];
			$modulo     = $row['modulo'];
			$tab        = $row['tab'];
			$tipoobjeto = $row['tipoobjeto'];
			$idobjeto   = $row['idobjeto'];
			$dropsql    = $row['dropsql'];
		}
		$sqlc = $dropsql;
		$resc = d::b()->query($sqlc) or die("Falha ao pesquisar dropsql do status: " . mysql_error() . "<p>SQL: $sqlc");
		while ($row = mysqli_fetch_array($resc)) {
			if ($idobjeto == $row[$tipoobjeto]) {
				$name = $row[1];
			}
		}

//CADASTRO O DASHPANEL CASO NÃO EXISTA, OU ATUALIZO OS DADOS DO MESMO
	if (empty($iddashpanel)) {
		
		$sqlu = "INSERT INTO dashpanel (iddashpanel, paneltitle, idfluxo, idempresa, status, criadopor, criadoem, alteradopor, alteradoem)
					VALUES (null, '" . $rotulomenu . " - " . $name . "', " . $_POST['_x_i_fluxostatus_idfluxo'] . ", '" . $_SESSION["SESSAO"]["IDEMPRESA"] . "','ATIVO','" . $_SESSION["SESSAO"]["USUARIO"] . "',now(),'" . $_SESSION["SESSAO"]["USUARIO"] . "',now());";
		$resu = d::b()->query($sqlu) or die("saveposchange: falha ao criar dashpanel:" . mysqli_error(d::b()) . "");
		$iddashpanel = d::b()->insert_id;
	}else{
		$sqlu = "UPDATE dashpanel SET paneltitle = '" . $rotulomenu . " - " . $name . "' WHERE iddashpanel = ".$iddashpanel.";";
					
		$resu = d::b()->query($sqlu) or die("saveposchange: falha ao criar dashpanel:" . mysqli_error(d::b()) . "");
		//die($sql);
	}

	//INSERE UM DASHCARD
	$sql = "INSERT INTO dashcard  (cardtitle, idempresa, objeto, tipoobjeto, iddashpanel, modulo, tab, calculo, tipocalculo, 
								   colcalc, cardtitlemodal, cardurl, cardurlmodal, criadopor, criadoem, alteradopor, 
								   alteradoem, cardcolor, cardbordercolor, status)
			SELECT concat(m.rotulomenu, ' - ', ifnull(NULL,s.rotulo)), f.idempresa, fs.idfluxostatus, 'fluxostatus' , " . $iddashpanel . ", f.modulo, m.tab, 'Y', 'COUNT', 
					'1', m.rotulomenu, CONCAT(\"concat('_modulo=\",f.modulo,\"&\",col,\"=[',group_concat(\",col,\"),']')\"), concat('_modulo=',f.modulo,'&_acao=u'), '" . $_SESSION["SESSAO"]["USUARIO"] . "', now(), '" . $_SESSION["SESSAO"]["USUARIO"] . "', 
					now(), 'if (count(1) > 0,\'danger\',\'success\')',	'if (count(1) > 0,\'danger\',\'success\')', 'ATIVO'
			 FROM fluxostatus fs JOIN fluxo f on f.idfluxo = fs.idfluxo
		LEFT JOIN " . _DBCARBON . "._status s on s.idstatus = fs.idstatus
			 JOIN " . _DBCARBON . "._modulo m on m.modulo = f.modulo
			 JOIN " . _DBCARBON . "._mtotabcol mto on m.tab = mto.tab and primkey = 'y'
		WHERE fs.idfluxostatus = " . $_SESSION["_pkid"] . ";";
	$resu = d::b()->query($sql) or die("saveposchange: falha ao criar dashcard:" . mysqli_error(d::b()) . "");
	//die($sql);
} elseif ($_POST["_x_d_fluxostatus_idfluxostatus"]) {
	//REMOVE UM DASHCARD
	$sql = "DELETE FROM dashcard WHERE objeto = " . $_POST["_x_d_fluxostatus_idfluxostatus"] . " and tipoobjeto = 'fluxostatus'";
	$resu = d::b()->query($sql) or die("saveposchange: falha ao excluir dashcard:" . mysqli_error(d::b()) . "");
} elseif ($_POST['_ajax_u_fluxostatus_idetapa']) {
	//CRIAR / ATUALIZAR O DASHCARD DA ETAPA
	$sql = "SELECT iddashpanel from dashcard  WHERE objeto = '" . $_POST["_ajax_u_fluxostatus_idfluxostatus"] . "' and tipoobjeto = 'fluxostatus'";
	$res = d::b()->query($sql) or die("Falha ao pesquisar dashcard: " . mysql_error() . "<p>SQL: $sql");
	while ($row = mysqli_fetch_assoc($res)) {
		$iddashpanel = $row['iddashpanel'];
	}
	//VERIFICA SE JÁ EXISTE UM DASHCARD
	$sql = "SELECT iddashcard from dashcard where objeto = '" . $_POST["_ajax_u_fluxostatus_idetapa"] . "' and tipoobjeto = 'etapa'";
	$res = d::b()->query($sql) or die("Falha ao pesquisar etapa dashcard: " . mysql_error() . "<p>SQL: $sql");
	while ($row = mysqli_fetch_assoc($res)) {
		$iddashcard = $row['iddashcard'];
	}

	//BUSCA DADOS PARA ATUALIZAÇÃO
	 $sql = "
		
		select m.rotulomenu, e.modulo, m.tab, e.tipoobjeto, idobjeto, dropsql, e.etapa
		FROM etapa e
		 JOIN carbonnovo._modulo m on m.modulo = e.modulo
		 LEFT JOIN  carbonnovo._mtotabcol mto on m.tab = mto.tab and mto.col = e.tipoobjeto
		 where
		 e.idetapa=" . $_POST['_ajax_u_fluxostatus_idetapa'] . ";
		
		";
	$res = d::b()->query($sql) or die("Falha ao pesquisar rotulo da etapa dashcard: " . mysql_error() . "<p>SQL: $sql");
	while ($row = mysqli_fetch_assoc($res)) {
		$rotulomenu = $row['rotulomenu'];
		$modulo     = $row['modulo'];
		$tab        = $row['tab'];
		$tipoobjeto = $row['tipoobjeto'];
		$idobjeto   = $row['idobjeto'];
		$dropsql    = $row['dropsql'];
		$etapa      = $row['etapa'];
	}
	$sql = $dropsql;
	$res = d::b()->query($sql) or die("Falha ao pesquisar dropsql da etapa: " . mysql_error() . "<p>SQL: $sql");
	while ($row = mysqli_fetch_array($res)) {
		$row[$tipoobjeto];
		if ($idobjeto == $row[$tipoobjeto]) {
			$name = $row[1];
		}
	}

	//SE NÃO EXISTIR, CRIA PARA ETAPA, CASO CONTRÁRIO ATUALIZA
	if (empty($iddashcard)) {
		$sqle = "INSERT INTO dashcard 
							(cardtitle, idempresa, objeto, tipoobjeto, iddashpanel, modulo, tab, calculo, tipocalculo, 
							colcalc, cardtitlemodal, cardurl, cardurlmodal, criadopor, criadoem, alteradopor, alteradoem,
							cardcolor, cardbordercolor, status) 	
					 SELECT '" . $etapa . "', e.idempresa, " . $_POST['_ajax_u_fluxostatus_idetapa'] . ", 'etapa', '" . $iddashpanel . "', m.modulo, m.tab, 'Y', 'COUNT', 
					 		'1', m.rotulomenu,
							 CONCAT(\"concat('_modulo=\",e.modulo,\"&\",col,\"=[',group_concat(\",col,\"),']')\"),						 
							 CONCAT('_modulo=',m.modulo,'&_acao=u'), '" . $_SESSION["SESSAO"]["USUARIO"] . "', now(), '" . $_SESSION["SESSAO"]["USUARIO"] . "', now(),
							'if (count(1) > 0,\'danger\',\'success\')',	'if (count(1) > 0,\'danger\',\'success\')', 'ATIVO'
						FROM etapa e
						JOIN " . _DBCARBON . "._modulo m on m.modulo = e.modulo
						JOIN " . _DBCARBON . "._mtotabcol mto on m.tab = mto.tab and primkey = 'y'
					   WHERE e.idetapa = " . $_POST['_ajax_u_fluxostatus_idetapa'] . ";";
		$rese = d::b()->query($sqle) or die("saveposchange: falha ao criar etapa dashcard:" . mysql_error(d::b()) . "<p>SQL: $sqle");
	} else {
		$sqle = "UPDATE dashcard SET objeto = " . $_POST['_ajax_u_fluxostatus_idetapa'] . ", tipoobjeto = 'etapa', cardtitle = '". $etapa . "' WHERE iddashcard = " . $iddashcard;
		$rese = d::b()->query($sqle) or die("Falha ao atualizar etapa dashcard: " .  mysql_error(d::b())  . "<p>SQL: $sqle");
	}
} elseif ($_POST['_x_u_fluxostatus_idstatus']) {
	//ATUALIZAR DADOS DO DASHCARD DO STATUS
	$sql = "
		
		SELECT 
			m.rotulomenu
			,f.modulo
			,m.tab
			,f.tipoobjeto
			,idobjeto
			,dropsql
			,s.rotulo 
		FROM 
			fluxostatus fs
		JOIN 
			fluxo f on f.idfluxo = fs.idfluxo
		JOIN 
			carbonnovo._modulo m on m.modulo = f.modulo
		LEFT JOIN  
			carbonnovo._mtotabcol mto on m.tab = mto.tab and mto.col = f.tipoobjeto
		LEFT JOIN 
			carbonnovo._status s on s.idstatus = fs.idstatus
		WHERE 
			fs.idfluxostatus = ".$_POST['_x_u_fluxostatus_idfluxostatus'].";
	
	";
	$res = d::b()->query($sql) or die("Falha ao pesquisar rotulo do status dashcard: " . mysql_error() . "<p>SQL: $sql");
	while ($row = mysqli_fetch_assoc($res)) {
		$rotulomenu = $row['rotulomenu'];
		$modulo     = $row['modulo'];
		$tab        = $row['tab'];
		$tipoobjeto = $row['tipoobjeto'];
		$idobjeto   = $row['idobjeto'];
		$dropsql    = $row['dropsql'];
		$rotulo     = $row['rotulo'];
	}
	$sqlc = $dropsql;
	$resc = d::b()->query($sqlc) or die("Falha ao pesquisar dropsql do status: " . mysql_error() . "<p>SQL: $sqlc");
	while ($row = mysqli_fetch_array($resc)) {
		if ($idobjeto == $row[$tipoobjeto]) {
			$name = $row[1];
		}
	}

	//ATUALIZAR DADOS DO DASHCARD
	$sqla = "UPDATE dashcard d 
			  JOIN fluxostatus fs on fs.idfluxostatus = d.objeto and d.tipoobjeto = 'fluxostatus' 
			  JOIN " . _DBCARBON . "._status s ON s.idstatus = fs.idstatus
			   SET d.cardtitle = '" . $rotulo . "'
			 WHERE d.objeto = " . $_POST['_x_u_fluxostatus_idfluxostatus'] . ";";
	$res = d::b()->query($sqla) or die("**Falha ao atualizar dashcard: " . mysql_error() . "<p>SQL: $sqla");
}
//FIM DO BLOCO