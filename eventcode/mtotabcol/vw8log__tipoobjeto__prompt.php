<?require_once("../../inc/php/functions.php");
if(logado()==true){
$sql="SELECT DISTINCT tipoobjeto as tipoobjeto FROM log WHERE tipoobjeto <> ''
ORDER BY tipoobjeto asc";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar tipologs de log sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['tipoobjeto'].'":"'.$row['tipoobjeto'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
}
?>
