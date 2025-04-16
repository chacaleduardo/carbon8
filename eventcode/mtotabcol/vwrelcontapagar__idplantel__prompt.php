<?include_once("../../inc/php/functions.php");
$sql="select idplantel,plantel from plantel where status='ATIVO' and idempresa=".cb::idempresa()." order by  plantel asc";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar plantel sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idplantel'].'":"'.$row['plantel'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>