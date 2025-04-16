<?
require_once("../../inc/php/functions.php");

$_modulo = $_GET['_modulo'];

$sql = "SELECT f.subtipo, f.descricao FROM formalizacaosubtipo f WHERE status = 'ATIVO'";

$res=mysql_query($sql) or die("Lote - Erro ao recuperar subtipo: ".mysql_error());

$virg="";
$json = "";
while($rowdrop = mysql_fetch_assoc($res)){
    $json.=$virg.'{"'.$rowdrop['subtipo'].'":"'.$rowdrop['descricao'].'"}';
    $virg=",";
}
echo("[".$json."]");
?>  