<?include_once("../../inc/php/functions.php");
$sql="select distinct modulotipo,modulotipo from carbonnovo._modulo where not modulotipo is null and not modulotipo = '' order by modulotipo";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar modulo tipo sql=".$sql);
$virg="";
$json="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['modulotipo'].'":"'.$row['modulotipo'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>