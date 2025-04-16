<?
set_time_limit(0);
ini_set('memory_limit', '-1');

require_once("../php/functions.php");
require_once("../php/laudo.php");

$conn = new mysqli(_DBSERVER, _DBUSER, _DBPASS, _DBAPP);

if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
} 
/*
$sql="SELECT fluxo, idstatus, idfluxostatus, idfluxo, fluxoocultar FROM fluxostatus";
$res = $conn->query($sql) or die("Erro ao buscar fluxo: " .mysqli_error($sql) . "<p>SQL:".$sql);
while($row = mysqli_fetch_assoc($res))
{
	$fluxo = $row['fluxo'];
	$idstatus = $row['idstatus'];
	$idfluxostatus = $row['idfluxostatus'];
	$idfluxo = $row['idfluxo'];
	$fluxoocultar = $row['fluxoocultar'];
	
	$novoArrayFluxo = "";
	$arrayFluxo = explode(",", $fluxo);
	foreach($arrayFluxo AS $_arrayFluxo)
	{
		$sql1 = "SELECT idfluxostatus FROM fluxostatus WHERE idfluxo = {$idfluxo} AND idstatus = '{$_arrayFluxo}';";
		var_dump($sql1);
		$res1 = $conn->query($sql1) or die("Erro ao buscar _arrayFluxo : " .mysqli_error($sql) . "<p>SQL:".$sql);
		$row1 = mysqli_fetch_assoc($res1);
		if(!empty($row1['idfluxostatus'])){
			$novoArrayFluxo .= $row1['idfluxostatus'].",";
		}
	}
	
	$novoArrayFluxoOcultar = "";
	$arrayFluxoocultar = explode(",", $fluxoocultar);
	foreach($arrayFluxoocultar AS $_arrayFluxoocultar)
	{
		$sql1 = "SELECT idfluxostatus FROM fluxostatus WHERE idfluxo = {$idfluxo} AND idstatus = '{$_arrayFluxoocultar}';";
		var_dump($sql1);
		$res1 = $conn->query($sql1) or die("Erro ao buscar _arrayFluxoocultar: " .mysqli_error($sql) . "<p>SQL:".$sql);
		$row1 = mysqli_fetch_assoc($res1);
		if(!empty($row1['idfluxostatus'])){
			$novoArrayFluxoOcultar .= $row1['idfluxostatus'].",";
		}	
	}
	
	$sql="UPDATE fluxostatus 
				 SET fluxo = '".substr($novoArrayFluxo, 0, -1)."',
					 fluxoocultar = '".substr($novoArrayFluxoOcultar, 0, -1)."'
                WHERE idfluxostatus = ".$idfluxostatus;
	var_dump($sql);
	$conn->query($sql) or die("Erro ao buscar sql : " .mysqli_error($sql) . "<p>SQL:".$sql);
}

$sqlIdFluxo = "SELECT fluxo, idstatus, idfluxostatus, idfluxo, fluxoocultar FROM evento";
$res = $conn->query($sql) or die("Erro ao buscar fluxo: " .mysqli_error($sql) . "<p>SQL:".$sql);
*/

$sql = "SELECT idresultado FROM resultadoassinatura GROUP BY idresultado HAVING count(idresultado) > 1 ;";
$res = $conn->query($sql) or die("Erro ao buscar fluxo: " .mysql_error($sql) . "<p>SQL:".$sql);
while($row = mysqli_fetch_assoc($res))
{	
	$sqlJson = "SELECT jresultado FROM resultadojson WHERE idresultado = ".$row['idresultado'].";";
	$resJson = $conn->query($sqlJson) or die("Erro ao buscar fluxo: " .mysql_error($sqlJson) . "<p>SQL:".$sqlJson);
	$fetJson = mysqli_fetch_assoc($resJson);
	$rowJson = unserialize(base64_decode($fetJson["jresultado"]));
	$arrassinat = $rowJson["resultadoassinatura"]["res"][1];
	d::b()->query("INSERT INTO log (idempresa, tipoobjeto, idobjeto, tipolog, status, log, criadoem, data) 
							 VALUES ('1', 'cron', 'resultadoassinatura', 'removerassinatura', 'BUSCA', '".json_encode($arrassinat)."', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))") or die("Erro #1 ao inserir log: ".mysqli_error(d::b())."<br>".$sqll);	
	
	$sqlDel = "DELETE FROM resultadoassinatura WHERE idresultado = '".$arrassinat['idresultado']."' and idpessoa <> '".$arrassinat['idpessoa']."';";
	$conn->query($sqlDel) or die("Erro ao buscar fluxo: " .mysql_error($sqlDel) . "<p>SQL:".$sqlDel);
	d::b()->query("INSERT INTO log (idempresa, tipoobjeto, idobjeto, tipolog, status, log, criadoem, data) 
							 VALUES ('1', 'cron', 'resultadoassinatura', 'removerassinatura', 'DELETE', '".addslashes($sqlDel)."', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))") or die("Erro #1 ao inserir log: ".mysqli_error(d::b())."<br>".$sqll);	
}
?>