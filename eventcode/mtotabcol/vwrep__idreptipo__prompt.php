<?include_once("../../inc/php/functions.php");
$sql="select idreptipo,reptipo from carbonnovo._reptipo where status='ATIVO'  order by reptipo asc";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar reptipo sql=".$sql);
$virg="";
$json="[";
while($row1=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row1['idreptipo'].'":"'.$row1['reptipo'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>