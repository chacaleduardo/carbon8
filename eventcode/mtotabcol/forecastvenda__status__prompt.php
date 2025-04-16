<?
require_once("../../inc/php/functions.php");
$sql = "SELECT status FROM forecastvenda GROUP BY status";
$res = mysql_query($sql) or die(mysql_error() . " Erro ao buscar prodserv sql=" . $sql);
$virg = "";
$json = "";
$json .= "[";
while ($row = mysql_fetch_assoc($res)) {
	$json .= $virg . '{"' . $row['status'] . '":"' . $row['status'] . '"}';
	$virg = ",";
}
$json .= "]";
echo ($json);