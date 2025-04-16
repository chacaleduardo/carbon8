<?require_once("../../inc/php/functions.php");
if(logado()==true){
$sql="SELECT DISTINCT tipolog as tipolog FROM log WHERE tipolog <> ''
ORDER BY tipolog asc";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar tipologs de log sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['tipolog'].'":"'.$row['tipolog'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
}
?>