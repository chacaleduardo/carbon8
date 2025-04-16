<?require_once("../../inc/php/functions.php");
if(logado()==true){
$sql="SELECT DISTINCT enviadode
FROM mailfila
ORDER BY enviadode asc";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar enviadode da tabela mailfila sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['enviadode'].'":"'.$row['enviadode'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
}
?>