<?php

require_once("./functions.php");

ini_set("display_errors",1);

error_reporting(E_ERROR);

$arqlog = '../../tmp/apache/m5.log';

$today = date("Y-m-d H:i:s");

$ip = $_SERVER["REMOTE_ADDR"];

$dados = "leitura: ".test_input($_REQUEST["leituras"])." arquivo: ".test_input($_REQUEST["file"])." ciclo: ".test_input($_REQUEST["ciclo"]);

$grupo = rstr(8);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."', 'm5post', '".$ip ."', 'dados', '".$dados."', 'INICIO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

//d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

//Verifica se leituras foram enviadas tanto pelo cartão quanto por um método de envio post/get
if ($_SERVER["REQUEST_METHOD"] == "POST" OR $_SERVER["REQUEST_METHOD"] == "GET") {
	$leitura = test_input($_REQUEST["leituras"]);
	$arquivo = test_input($_REQUEST["file"]);
	$ciclo = test_input($_REQUEST["ciclo"]);
	$status = test_input($_REQUEST["status"]);
	//$leitura = '{"iddevice":"19","reg":"20210510103647","iddevicecicloativ":25,"acao":"max","prssi":54,"ENV":["Pino=I2C","iddevicesensorbloco=6","t=27.9"]}';
	if($leitura){
		$result = trataleitura($leitura,$ip);
		echo $result;
	}
	else if ($arquivo){
		//$ip = '192.168.2.170';
		$url = "http://".$ip."/".$arquivo;
		$source = fopen("http://".$ip."/".$arquivo, 'r');
		if($source){
			while(!feof($source)) {
				$leitura = fgets($source);
				$result = trataleitura($leitura,$ip);
			//mostra linha a linha
			echo $leitura.'</br>';
			echo $result;
			}
			fclose($source);
		}else {
			$dados = "Não existe Leituras.txt no cartão micro SD!";
		}
	} 
	else if ($ciclo){
		$register = explode("=",$ciclo);
		$sql = "update device set ciclo ='".$register[0]."', alteradoem = NOW(), alteradopor = 'm5' where ip_hostname = '".$ip."'";
		if (d::b()->query($sql) === TRUE) {
			echo "Update realizado!";
		}else {
			die($msgerro.": ". mysqli_error(d::b()));
		}
	}
	else if ($status){
		$id = explode("=",$status);
		re::dis()->hMSet(
			'_estado:'.$id[0].':device',
			[
				'_pkval' 		=> $id[0],
				'status'        => 'DESLIGADO',
				'desligadoem' 	=> Date('Y-m-d H:i:s')
			]
		);
	}
} else {
    echo "No data POSTED with HTTP REQUEST.";
}

//Tratamento das Leituras para inserção no BD e no REDIS.
function trataleitura($leitura,$ip){
	$register = json_decode($leitura,true);
	if($register['id']){
		$id = $register['id'];
		$dia = substr($register['reg'], 0, 2);
		$mes = substr($register['reg'], 2, 2);
		$ano = substr($register['reg'], 4, 4);
		$hora = substr($register['reg'], 8, 2);
		$min = substr($register['reg'], 10, 2);
		$seg = substr($register['reg'], 12, 2);
	}else{
		$id = $register['iddevice'];
		$ano = substr($register['reg'], 0, 4);
		$mes = substr($register['reg'], 4, 2);
		$dia = substr($register['reg'], 6, 2);
		$hora = substr($register['reg'], 8, 2);
		$min = substr($register['reg'], 10, 2);
		$seg = substr($register['reg'], 12, 2);
	}
	$idcicloativ = $register['iddevicecicloativ'];
	$acao = $register['acao'];
	$prssi = $register['prssi'];
	$chave = $register['chave'];
    $ssid = $register['ssid'];
	$reg = "$ano-$mes-$dia $hora:$min:$seg"; // Padrão YYY-MM-DD HH:mm:ss, no UTC America/Sao_Paulo
	$idbloco = "";
	$pino = "";
	foreach ($register as $sensor => $valor) {
		if($sensor != 'id' && $sensor != 'reg'){
			foreach($valor as $valor2) {
				$tipo = explode('=',$valor2);
				if($tipo[0] == 'Pino'){
					$pino = $tipo[1];
				}else if($tipo[0] == 'idbloco' || $tipo[0] == 'iddevicesensorbloco'){
					$idbloco = $tipo[1];
				}else{
					if(!empty($idbloco)){
						$sql = "INSERT INTO devicesensorhist (idempresa,iddevice, iddevicesensorbloco, iddevicecicloativ, acao, ip, registradoem, sensor, pino, tipo, valor, grupo, criadopor, criadoem, alteradopor, alteradoem)
						VALUES ('1',".$id.",'".$idbloco."','".$idcicloativ."','".$acao."','".$ip."','".$reg."','".$sensor."','".$pino."','".$tipo[0]."','".$tipo[1]."','".$chave."','m5',NOW(),'m5',NOW())";
						if (d::b()->query($sql) === TRUE) {
						    $sqlq = "select alertamin, alertamax, var as tipo, LAST_INSERT_ID() as iddevicesensorhist from devicecicloativ where iddevicecicloativ = '".$idcicloativ."'";
							    $res = d::b()->query($sqlq);
								while ($_row = mysql_fetch_assoc($res)){
								    if(($tipo[1] < $_row['alertamin'] || $tipo[1] > $_row['alertamax']) and $_row['tipo'] == $tipo[0] ){
									    $sqlq = "INSERT INTO devicesensorhistdesvio (idempresa,iddevice, iddevicesensorbloco, iddevicecicloativ, acao, ip, registradoem, sensor, pino, tipo, valor, grupo, alertamin, alertamax, iddevicesensorhist, status, criadopor, criadoem, alteradopor, alteradoem)
										    VALUES ('1',".$id.",'".$idbloco."','".$idcicloativ."','".$acao."','".$ip."','".$reg."','".$sensor."','".$pino."','".$tipo[0]."','".$tipo[1]."','".$chave."', '".$_row['alertamin']."', '".$_row['alertamax']."', '".$_row['iddevicesensorhist']."', 'PENDENTE', 'm5',NOW(),'m5',NOW())";
									    d::b()->query($sqlq);
								    }
								}
							
							
							echo "New record created successfully";
								//maf: enviar para a fila de cache do redis
								$_estado = [
									'_pkval' 	=> $idbloco,
									'pk'		=> 'iddevicesensorbloco',
									'_tab'		=> 'devicesensorbloco',
									'_mod'		=> 'devicesensor',
									'_cols'		=> [
										'tipo'				=> $tipo[0],
										'ultimaleitura' 	=> $tipo[1],
										'dataultimaleitura'	=> $reg,
									]
								];
	
								$_estado = json_encode($_estado);
	
								//maf150421: Gravar o ESTADO do sensor
								re::dis()->hMSet(
									'_estado:'.$idbloco.':devicesensorbloco',
									[
										'_pkval' 		=> $idbloco, // Chave para recuperação de confis, evitando parse do #data
										'registradoem' 	=> $reg,
										'#data'			=> $_estado,
										$tipo[0]		=> $tipo[1]
									]
								);
	
							echo $_estado;
						} 
						else {
							echo "Error: " . $sql;
						}

						$sqlalert = "SELECT 
										alertamin, alertamax, tipo
									FROM
										devicecicloativ
									WHERE
										iddevicecicloativ = '".$idcicloativ."'";

						$resalert = d::b()->query($sqlalert);
						$qtdalert = mysqli_num_rows($resalert);

						if($qtdalert>0){

							$_rowalert = mysql_fetch_assoc($resalert);

							if(strcasecmp($_rowalert['tipo'],$tipo[0]) == 0){
								if ($tipo[1] < $_rowalert['alertamin'] or $tipo[1] > $_rowalert['alertamax'] ){
									
									$sqlalert2 = "INSERT INTO devicesensorhistalert (idempresa, iddevice, iddevicesensorbloco, iddevicecicloativ, tipo, valor, grupo, registradoem, status, criadopor, criadoem, alteradopor, alteradoem) VALUES (1,".$id.",".$idbloco.",".$idcicloativ.",'".$tipo[0]."','".$tipo[1]."','".$chave."','".$reg."','PENDENTE','m5',NOW(),'m5',NOW())";
									
									if (d::b()->query($sqlalert2) !== TRUE){
										echo "Error: " . $sql;
									}
								}
							}
						}	

					}else{
						echo "idbloco está vazio";
					}
				}
			}
		}
	}
	//maf150421: Gravar o ESTADO do m5
	if(!empty($id)){
		re::dis()->hMSet(
			'_estado:'.$id.':device',
			[
				'_pkval' 	   => $id,
				'prssi' 	   => $prssi,
				'ssid' 	       => $ssid,
				'registradoem' => $reg
			]
		);
	}
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."', 'm5post', '".$ip ."', 'dados', '".$dados."', 'FIM', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

//d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);
?>
