<?
require_once("../../inc/php/functions.php");
$sql = "SELECT exercicio FROM forecastvenda group by exercicio";
$res = mysql_query($sql) or die(mysql_error() . " Erro ao buscar prodserv sql=" . $sql);
$virg = "";
$json = "";
$json .= "[";
while ($row = mysql_fetch_assoc($res)) {
	$json .= $virg . '{"' . $row['exercicio'] . '":"' . $row['exercicio'] . '"}';
	$virg = ",";
}
$json .= "]";
echo ($json);
