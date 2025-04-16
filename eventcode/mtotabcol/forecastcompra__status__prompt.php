<?
require_once("../../inc/php/functions.php");
$sql = "SELECT status FROM forecastcompra GROUP BY status";
$res = mysql_query($sql) or die(mysql_error() . " Erro ao buscar sql=" . $sql);
$virg = "";
$json = "";
$json .= "[";
while ($row = mysql_fetch_assoc($res)) {
	$json .= $virg . '{"' . $row['status'] . '":"' . $row['status'] . '"}';
	$virg = ",";
}
$json .= "]";
echo ($json);
