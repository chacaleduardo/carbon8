<?include_once("../../inc/php/functions.php");
$sql="select distinct(modvinculado) as modvinculado from `carbonnovo`.`_modulo` where modvinculado <> '' and status = 'ATIVO'";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar plantel sql=".$sql);
$virg="";
$json="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['modvinculado'].'":"'.$row['modvinculado'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>