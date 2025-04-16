<?include_once("../../inc/php/functions.php");
$sql="select idempresa,empresa from empresa where status='ATIVO' order by  empresa asc";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar plantel sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idempresa'].'":"'.$row['empresa'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>