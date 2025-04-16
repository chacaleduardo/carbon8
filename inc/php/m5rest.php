<?
	header('Cache-Control: no-cache, must-revalidate');
	header('Content-Type: application/json');

	include_once("functions.php"); 

	$ip = $_SERVER["REMOTE_ADDR"];
	$mac = $_REQUEST["mac_address"];
	$tipoescolhido = $_REQUEST["tipoescolhido"];
	$sensorescolhido = $_REQUEST["sensorescolhido"];
	$idtag = $_REQUEST["idtag"];

	//Busca as configurações padrões do M5
	$sql = "SELECT 
				d.iddevice,
				d.ip_hostname,
				d.mac_address,
				d.ciclo,
				IF(d.calib_press = '', 0, d.calib_press) AS calib_press,
				d.calib_umid,
				d.iddeviceref,
				d.calib_temp,
				d.delay,
				d.versao,
				(SELECT 
						1
					FROM
						deviceobj dobj
					WHERE
						dobj.iddevice = d.iddevice
					LIMIT 1) AS temciclo,
				(SELECT 
						db.iddevicesensorbloco
					FROM
						devicesensorbloco db
							JOIN
						devicesensor ds ON db.iddevicesensor = ds.iddevicesensor
							JOIN
						devicesensortipo dt ON db.tipo = dt.tipo
					WHERE
						dt.tipo = 'p'
							AND ds.iddevice = d.iddeviceref
							AND d.iddevice != d.iddeviceref) AS iddevicesensorblocoref
			FROM
				device d
			WHERE
				d.mac_address = '".$_REQUEST['mac_address']."'";
	$res = d::b()->query($sql);
	$qtd=mysqli_num_rows($res);
    
	if($qtd>0){
	
		$_row = mysql_fetch_assoc($res);
		
		$leitura['dados']['iddevice'] = strval($_row['iddevice']);
		$leitura['dados']['ciclo'] = strval($_row['ciclo']);
		$leitura['dados']['calibpress'] = strval($_row['calib_press']);		
		$leitura['dados']['calibumid'] = strval($_row['calib_umid']);
		$leitura['dados']['calibtemp'] = strval($_row['calib_temp']);
		$leitura['dados']['delay'] = strval($_row['delay']);		
		$leitura['dados']['ip_hostname'] = strval($_row['ip_hostname']);
		$leitura['dados']['iddevicesensorblocoref'] = strval($_row['iddevicesensorblocoref']);
		$temciclo = strval($_row['temciclo']);

		$sqlb = "SELECT 
					d.pino, dt.tipo, d.nomesensor, db.iddevicesensorbloco, db.prioridade, db.offset, db.tipocalibracao
				FROM
					devicesensorbloco db
				JOIN
					devicesensor d ON d.iddevicesensor = db.iddevicesensor
				JOIN
					devicesensortipo dt ON db.tipo = dt.tipo
				WHERE
					d.iddevice = '".$_row['iddevice']."'
				AND db.status = 'ATIVO'
				ORDER BY db.tipo";
		
		$resb = d::b()->query($sqlb);

		//echo ($_row['versao']);

		if($_row['versao'] == "1.1.3" || $_row['versao'] == "1.1.2" || $_row['versao'] == "1.1.7" || $_row['versao'] == "1.0.2" || $_row['versao'] == "1.0.9"){
			$i = 0;
			while ($_rowb = mysql_fetch_assoc($resb)){
				$leitura['dados'][$i]['nomesensor'] = strval($_rowb['nomesensor']);
				$leitura['dados'][$i]['pino'] = strval($_rowb['pino']);
				$leitura['dados'][$i]['tipo'] = strval($_rowb['tipo']);
				$leitura['dados'][$i]['iddevicesensorbloco'] = strval($_rowb['iddevicesensorbloco']);
				$leitura['dados'][$i]['tipocalibracao'] = strval($_rowb['tipocalibracao']);
				$i++;
			}
		}else{
			$i = 1;
			while ($_rowb = mysql_fetch_assoc($resb)){
				if($nomeesensoranterior != $_rowb['nomesensor']){
					$i = 1;
				}
				$leitura['dados']['sensor'][$_rowb['nomesensor']] [$i]['pino'] = strval($_rowb['pino']);
				$leitura['dados']['sensor'][$_rowb['nomesensor']] [$i]['tipo'] = strval($_rowb['tipo']);
				$leitura['dados']['sensor'][$_rowb['nomesensor']] [$i]['offset'] = strval($_rowb['offset']);
				$leitura['dados']['sensor'][$_rowb['nomesensor']] [$i]['iddevicesensorbloco'] = strval($_rowb['iddevicesensorbloco']);
				$leitura['dados']['sensor'][$_rowb['nomesensor']] [$i]['prioridade'] = strval($_rowb['prioridade']);
				$leitura['dados']['sensor'][$_rowb['nomesensor']] [$i]['tipocalibracao'] = strval($_rowb['tipocalibracao']);
				$nomeesensoranterior = $_rowb['nomesensor'];
				$i++;
			}
		}

		//Atualiza o ip do M5 e o campo reiniciadoem no banco de dados
		$sql_update = "UPDATE device set ip_hostname = '".$_SERVER["REMOTE_ADDR"]."', reiniciadoem = NOW() where iddevice = ".$leitura['dados']['iddevice']."";
		if (d::b()->query($sql_update) === TRUE) {
			//maf150421: Gravar o ESTADO do m5
			re::dis()->hMSet(
				'_estado:'.$leitura['dados']['iddevice'].':device',
				[
					'_pkval' 		=> $_row['iddevice'],
					'status'        => 'ATIVO',
					'reiniciadoem' 	=> Date('Y-m-d H:i:s'),
					'registradoem' 	=> Date('Y-m-d H:i:s')
				]
			);
		}
		else {
			echo "Error: " . $sql_update;
		}

		//Se houver ciclos, buscar a configuração dinâmica de cada um
		if ($temciclo > 0){
			$sqlu="SELECT 
						dc.nomeciclo,
						dc.iddeviceciclo,
						dc.modelo,
						dca.iddevicecicloativ,
						dca.nomeativ,
						dca.tipo,
						dca.qtd,
						dca.min,
						dca.max,
						dca.var,
						dca.alertamin,
						dca.alertamax,
						dca.panicomin,
						dca.panicomax,
						dcac.pino,
						dcac.acao,
						dcac.estado
					FROM
						deviceciclo dc
							JOIN
						devicecicloativ dca ON dca.iddeviceciclo = dc.iddeviceciclo
							JOIN
						deviceobj do ON do.objeto = dc.iddeviceciclo
							AND do.tipoobjeto = 'deviceciclo'
							LEFT JOIN
						devicecicloativacao dcac ON dcac.iddevicecicloativ = dca.iddevicecicloativ
					WHERE
						do.iddevice = ".$leitura['dados']['iddevice']."
					ORDER BY dc.nomeciclo, dca.ordem, dcac.acao";

			$rese = d::b()->query($sqlu);
			
			$i = 0;
			$j = 0;

			while ($_row = mysql_fetch_assoc($rese)){

				if ($cicloanterior != $_row['nomeciclo']){
					$i++;
					$k = 0;
					$j = 0;
				}

				if ($atividadeanterior != $_row['nomeativ']){
					$k++;
					$j = 0;
				}

				if ($acaoanterior != $_row['acao']){
					$j = 0;
				}

				if($_row['tipo'] == "T"){
					$qtd = $_row['qtd'] * 60000;
				}else{
					$qtd = $_row['qtd'];
				}
				
				$leitura['ciclo'.$i]['nomeciclo'] = preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/"),explode(" ","a A e E i I o O u U n N c"), $_row['nomeciclo']);
				$leitura['ciclo'.$i]['iddeviceciclo'] = $_row['iddeviceciclo'];
				$leitura['ciclo'.$i]['modelo'] = $_row['modelo'];
				$leitura['ciclo'.$i]['var'] = $_row['var'];

				$leitura['ciclo'.$i]['atividade'.$k]['nomeativ'] = preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/"),explode(" ","a A e E i I o O u U n N c"), $_row['nomeativ']);
				$leitura['ciclo'.$i]['atividade'.$k]['iddevicecicloativ'] = strval($_row['iddevicecicloativ']);
				$leitura['ciclo'.$i]['atividade'.$k]['tipo'] = strval($_row['tipo']);
				$leitura['ciclo'.$i]['atividade'.$k]['qtd'] = strval($qtd);
				$leitura['ciclo'.$i]['atividade'.$k]['min'] = strval($_row['min']);
				$leitura['ciclo'.$i]['atividade'.$k]['max'] = strval($_row['max']);
				$leitura['ciclo'.$i]['atividade'.$k]['panicomin'] = strval($_row['panicomin']);
				$leitura['ciclo'.$i]['atividade'.$k]['panicomax'] = strval($_row['panicomax']);

				if ($_row['acao'] == 'min'){
					$leitura['ciclo'.$i]['atividade'.$k]['acaomin'][$j]['pino'] = $_row['pino'];
					$leitura['ciclo'.$i]['atividade'.$k]['acaomin'][$j]['estado'] = $_row['estado'];
				} else if ($_row['acao'] == 'max'){
					$leitura['ciclo'.$i]['atividade'.$k]['acaomax'][$j]['pino'] = $_row['pino'];
					$leitura['ciclo'.$i]['atividade'.$k]['acaomax'][$j]['estado'] = $_row['estado'];
				}
				$leitura['ciclo'.$i]['atividade'.$k]['alertamin'] = strval($_row['alertamin']);
				$leitura['ciclo'.$i]['atividade'.$k]['alertamax'] = strval($_row['alertamax']);

				$cicloanterior = $_row['nomeciclo'];
				$atividadeanterior = $_row['nomeativ'];
				$acaoanterior = $_row['acao'];

				$j++;
			}

		}

		echo json_encode($leitura, JSON_UNESCAPED_UNICODE);

	}else if($tipoescolhido != "" && $sensorescolhido != "" && $idtag != ""){

		$sqli = "INSERT INTO 
					`laudo`.`device` (`idempresa`, `idtag`, `mac_address`, `ip_hostname`, `status`, `delay`, `iddeviceref`, `modelo`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
					VALUES 
					(1, '".$idtag ."', '".$mac."', '".$ip ."', 'PENDENTE', '100', '240', 'CICLO', 'sislaudo', now(), 'sislaudo', now())";

		if(d::b()->query($sqli) === TRUE){
			$sqldev = "SELECT 
							d.iddevice
						FROM
							device d
						WHERE
							d.mac_address = '".$_REQUEST['mac_address']."'";

			$resdev = d::b()->query($sqldev);
			$_rowdev = mysql_fetch_assoc($resdev);

			insertciclos($_rowdev['iddevice'],$tipoescolhido);

			insertsensor($_rowdev['iddevice'],$sensorescolhido,$tipoescolhido);

			$updatestatus = "UPDATE tag 
								SET 
									status = 'ATIVO'
								WHERE
									idtag = ".$idtag."";
			
			d::b()->query($updatestatus);
						
		}else{
			die("erro ao inserir device: ".mysqli_error(d::b())."<br>".$sqli);
		}

	}else {
		$sqlcad = "SELECT 
						idtag, tag
					FROM
						tag t
					WHERE
						idtagtipo = 83
							AND fabricante = 'M5STACK'
							AND status = 'DISPONIVEL'
							AND NOT EXISTS( SELECT 
								1
							FROM
								device d
							WHERE
								d.idtag = t.idtag)";

		$rescad = d::b()->query($sqlcad);

		$leitura['CadastreM5'] = 1;

		$i = 1;

		while ($_rowcad = mysql_fetch_assoc($rescad)){
			$leitura['tag'.$i]['idtag'] = $_rowcad['idtag'];
			$leitura['tag'.$i]['tag'] = $_rowcad['tag'];
			$i ++;
		}

		echo json_encode($leitura, JSON_UNESCAPED_UNICODE);
	}

	function insertciclos($iddevice,$tipoescolhido){

		switch($tipoescolhido){

			case 1: // QUARTO ESTUFA / CAMARA FRIA
				$sqlciclos = "INSERT INTO 
								`laudo`.`deviceobj` (`idempresa`, `iddevice`, `objeto`, `tipoobjeto`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
								VALUES 
								(1, '".$iddevice."', 1, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$iddevice."', 16, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$iddevice."', 18, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$iddevice."', 12, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now())";
				$sqldevice = "UPDATE device set subtipo = 'CONTROLE', status = 'ATIVO' where  iddevice = ".$iddevice;
				break;
			case 2: // CAMARA FRIA
				$sqlciclos = "INSERT INTO 
								`laudo`.`deviceobj` (`idempresa`, `iddevice`, `objeto`, `tipoobjeto`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
								VALUES 
								(1, '".$iddevice."', 1, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$iddevice."', 16, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$iddevice."', 12, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now())";
				$sqldevice = "UPDATE device set subtipo = 'CONTROLE', status = 'ATIVO' where  iddevice = ".$iddevice;
				break;
			case 3: // QUARTO ESTUFA
				$sqlciclos = "INSERT INTO 
								`laudo`.`deviceobj` (`idempresa`, `iddevice`, `objeto`, `tipoobjeto`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
								VALUES 
								(1, '".$iddevice."', 1, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$iddevice."', 18, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now())";
				$sqldevice = "UPDATE device set subtipo = 'CONTROLE', status = 'ATIVO' where  iddevice = ".$iddevice;
								break;
			case 4: // FREEZER
				$sqlciclos = "INSERT INTO 
								`laudo`.`deviceobj` (`idempresa`, `iddevice`, `objeto`, `tipoobjeto`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
								VALUES 
								(1, '".$iddevice."', 15, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$iddevice."', 10', 'deviceciclo', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$iddevice."', 12, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now())";
				$sqldevice = "UPDATE device set subtipo = 'CONTROLE', status = 'ATIVO' where  iddevice = ".$iddevice;
				break;
			case 5: // MONITORAMENTO SALA
				$sqlciclos = "INSERT INTO 
								`laudo`.`deviceobj` (`idempresa`, `iddevice`, `objeto`, `tipoobjeto`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
								VALUES
								(1, '".$iddevice."', 22, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now())";
				$sqldevice = "UPDATE device set subtipo = 'DIFERENCIAL', status = 'ATIVO' where  iddevice = ".$iddevice;
				break;
			case 6: // AUTOCLAVE
				$sqlciclos = "INSERT INTO 
								`laudo`.`deviceobj` (`idempresa`, `iddevice`, `objeto`, `tipoobjeto`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
								VALUES 
								(1, '".$iddevice."', 2, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$iddevice."', 3, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$iddevice."', 4, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$iddevice."', 5, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$iddevice."', 6, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$iddevice."', 14, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$iddevice."', 22, 'deviceciclo', 'sislaudo', now(), 'sislaudo', now())";
				$sqldevice = "UPDATE device set subtipo = 'CONTROLE', status = 'ATIVO' where  iddevice = ".$iddevice;
								break;
		}
		d::b()->query($sqlciclos) or die("erro ao inserir ciclos: ".mysqli_error(d::b())."<br>".$sqlciclos);
		d::b()->query($sqldevice) or die("erro ao atualizar device subtipo: ".mysqli_error(d::b())."<br>".$sqlciclos);
	}

	function insertsensor($iddevice,$sensorescolhido,$tipoescolhido){

		switch($sensorescolhido){

			case 1:  // ENV
				$sqlsensor = "INSERT INTO 
								`laudo`.`devicesensor` (`idempresa`, `iddevice`, `pino`, `nomesensor`, `status`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
								VALUES 
								(1, '".$iddevice."', 'I2C', 'ENV', 'PENDENTE', 'sislaudo', now(), 'sislaudo', now())";
				break;
			case 2:  // ENV3
				$sqlsensor = "INSERT INTO 
								`laudo`.`devicesensor` (`idempresa`, `iddevice`, `pino`, `nomesensor`, `status`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
								VALUES 
								(1, '".$iddevice."', 'I2C', 'ENV3', 'PENDENTE', 'sislaudo', now(), 'sislaudo', now())";
				break;
			case 3:  // NTC
				$sqlsensor = "INSERT INTO 
								`laudo`.`devicesensor` (`idempresa`, `iddevice`, `pino`, `nomesensor`, `status`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
								VALUES 
								(1, '".$iddevice."', '35', 'NTC', 'PENDENTE', 'sislaudo', now(), 'sislaudo', now())";
				break;
			case 4: // TXPC
				$sqlsensor = "INSERT INTO 
								`laudo`.`devicesensor` (`idempresa`, `iddevice`, `pino`, `nomesensor`, `status`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
								VALUES 
								(1, '".$iddevice."', '35', 'TXPC', 'PENDENTE', 'sislaudo', now(), 'sislaudo', now())";
				break;
			case 5: // TPJ
				$sqlsensor = "INSERT INTO 
								`laudo`.`devicesensor` (`idempresa`, `iddevice`, `pino`, `nomesensor`, `status`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
								VALUES 
								(1, '".$iddevice."', 'SPI', 'TPJ', 'PENDENTE', 'sislaudo', now(), 'sislaudo', now())";
				break;
		}

		d::b()->query($sqlsensor) or die("erro ao inserir sensor: ".mysqli_error(d::b())."<br>".$sqlsensor);

		$sqlsen = "SELECT 
							ds.iddevicesensor
						FROM
							devicesensor ds
						WHERE
							ds.iddevice = '".$iddevice."'";

		$ressen = d::b()->query($sqlsen);
		$_rowsen = mysql_fetch_assoc($ressen);

		if(($sensorescolhido == 1 || $sensorescolhido == 2) && $tipoescolhido == 5){
			$sqlbloco = "INSERT INTO 
								`laudo`.`devicesensorbloco` (`idempresa`, `iddevicesensor`, `unidade`, `tipocalibracao`, `tipo`, `prioridade`, `status`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
								VALUES 
								(1, '".$_rowsen["iddevicesensor"]."', 'PA', 1, 'p', 2, 'ATIVO', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$_rowsen["iddevicesensor"]."', 'ºC', 1, 't', 2, 'ATIVO', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$_rowsen["iddevicesensor"]."', '%', 1, 'u', 2, 'ATIVO', 'sislaudo', now(), 'sislaudo', now()),
								(1, '".$_rowsen["iddevicesensor"]."', 'PA', 1, 'd', 1, 'ATIVO', 'sislaudo', now(), 'sislaudo', now())";
		}else if ($sensorescolhido == 1 || $sensorescolhido == 2 || $sensorescolhido == 3){
			$sqlbloco = "INSERT INTO 
								`laudo`.`devicesensorbloco` (`idempresa`, `iddevicesensor`, `unidade`, `tipocalibracao`, `tipo`, `prioridade`, `status`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
								VALUES 
								(1, '".$_rowsen["iddevicesensor"]."', 'ºC', 1, 't', 1, 'ATIVO', 'sislaudo', now(), 'sislaudo', now())";
		}else if($sensorescolhido == 4){
			$sqlbloco = "INSERT INTO 
								`laudo`.`devicesensorbloco` (`idempresa`, `iddevicesensor`, `unidade`, `tipocalibracao`, `tipo`, `prioridade`, `status`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
								VALUES 
								(1, '".$_rowsen["iddevicesensor"]."', 'BAR', 1, 'p', 1, 'ATIVO', 'sislaudo', now(), 'sislaudo', now())";
		}else if($sensorescolhido == 5){
			$sqlbloco = "INSERT INTO 
								`laudo`.`devicesensorbloco` (`idempresa`, `iddevicesensor`, `unidade`, `tipocalibracao`, `tipo`, `prioridade`, `status`, `criadopor`, `criadoem`,  `alteradopor`, `alteradoem`) 
								VALUES 
								(1, '".$_rowsen["iddevicesensor"]."', 'ºC', 1, 't', 1, 'ATIVO', 'sislaudo', now(), 'sislaudo', now())";
		}

		d::b()->query($sqlbloco) or die("erro ao inserir bloco do sensor: ".mysqli_error(d::b())."<br>".$sqlbloco);
	}
?>