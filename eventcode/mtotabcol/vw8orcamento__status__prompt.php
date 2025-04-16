<?
require_once("../../inc/php/functions.php");

$sql = "select distinct(status) from orcamento;";

$res=mysql_query($sql) or die("Orçamento - Erro ao recuperar status: ".mysql_error());
$virg="";
$json = "";
while($row=mysql_fetch_assoc($res)){
    $json.=$virg.'{"'.$row['status'].'":"'.$row['status'].'"}';
    $virg=",";
}
echo("[".$json."]");
?>

