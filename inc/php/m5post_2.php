<?php

require_once("functions.php");

ini_set("display_errors",1);

error_reporting(E_ERROR);

$arqlog = '../../tmp/apache/m5.log';

$today = date("Y-m-d H:i:s");

$ip = $_SERVER["REMOTE_ADDR"];

//Verifica se leituras foram enviadas tanto pelo cartão quanto por um método de envio post/get
if ($_SERVER["REQUEST_METHOD"] == "POST" OR $_SERVER["REQUEST_METHOD"] == "GET") {
	$leitura = test_input($_REQUEST["leituras"]);
	$arquivo = test_input($_REQUEST["file"]);
	//$leitura = '{"id":"37","reg":"14012021150720","env":["Pino=I2C","t=27.62","u=51.89","p=90920.09"]}';
	if($leitura != ""){
		$result = trataleitura($leitura,$ip);
		echo $result;
	}
	else if($arquivo != ""){
		//$ip = '192.168.2.170';
		$url = "http://".$ip."/".$arquivo;
		$source = fopen("http://".$ip."/".$arquivo, 'r') or die("Não existe Leituras.txt no cartão micro SD!");
		while(!feof($source)) {
			$leitura = fgets($source);
			$result = trataleitura($leitura,$ip);
		//mostra linha a linha
		echo $leitura.'</br>';
		echo $result;
		}
		fclose($source);
	} 
} else {
    echo "No data POSTED with HTTP REQUEST.";
}

//Tratamento das Leituras para inserção no BD e no REDIS.
function trataleitura($leitura,$ip){
	$register = json_decode($leitura,true);
	$id = $register['id'];
	$dia = substr($register['reg'], 0, 2);
	$mes = substr($register['reg'], 2, 2);
	$ano = substr($register['reg'], 4, 4);
	$hora = substr($register['reg'], 8, 2);
	$min = substr($register['reg'], 10, 2);
	$seg = substr($register['reg'], 12, 2);
	$reg = "$ano-$mes-$dia $hora:$min:$seg";
	foreach ($register as $sensor => $valor) {
		if($sensor != 'id' && $sensor != 'reg'){
			foreach($valor as $valor2) {
				$tipo = explode('=',$valor2);
				if($tipo[0] == 'Pino'){
					$pino = $tipo[1];
				}else{
					$sql = "INSERT INTO devicesensorhist (idempresa,iddevice, ip, registradoem, sensor, pino, tipo, valor, criadopor, criadoem, alteradopor, alteradoem)
					VALUES ('1',".$id.",'".$ip."','".$reg."','".$sensor."','".$pino."','".$tipo[0]."','".$tipo[1]."','m5',NOW(),'m5',NOW())";
					if (d::b()->query($sql) === TRUE) {
						echo "New record created successfully";
						if($tipo[0] != 'p'){
							//maf: enviar para a fila de cache do redis
							$queue_cache = '{
								"1":{
									"_alteradopor":"M5",
									"_alteradoem": "'.date("Y-m-d H:i:s").'",
									"_mod":"device",
									"_acao":"u",
									"_tab":"device",
									"_cols":{
										"'.$tipo[0].'":"'.$tipo[1].'"
									},
									"_pk":"iddevice",
									"_pkval":"'.$id.'"
								}
							}';
							re::dis()->hMSet(
								'_queue_cache:'.rstr(8)
								, ["#data"=>$queue_cache]);
						}
					} 
					else {
						echo "Error: " . $sql . "<br>" . $conn->error;
					}
				}
			}
		}
	}
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}
?>