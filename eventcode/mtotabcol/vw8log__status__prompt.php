<?require_once("../../inc/php/functions.php");
if(logado()==true){
$sql="SELECT DISTINCT status as status FROM log WHERE status <> ''
ORDER BY status asc";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar tipologs de log sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['status'].'":"'.$row['status'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
}
?>