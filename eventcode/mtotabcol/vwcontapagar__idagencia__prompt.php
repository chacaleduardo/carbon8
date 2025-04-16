<?
require_once("../../inc/php/functions.php");

$sql="select idagencia,agencia 
from agencia where 1 ".getidempresa('idempresa','agencia')."  and status='ATIVO' order by agencia";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar pessoa sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idagencia'].'":"'.$row['agencia'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>