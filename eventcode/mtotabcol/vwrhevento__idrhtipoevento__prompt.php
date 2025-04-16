<?include_once("../../inc/php/functions.php");
$sql="select idrhtipoevento,evento 
from rhtipoevento where 1 ".getidempresa('idempresa','rhtipoevento')."  and status='ATIVO' order by evento";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar pessoa sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idrhtipoevento'].'":"'.$row['evento'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>