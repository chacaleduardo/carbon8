<?include_once("../../inc/php/functions.php");
$sql="select finalidade from vwespeciefinalidade where 1 ".getidempresa('idempresa',$_GET["modulo"])." order by especietipofinalidade;";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar especie/finalidade sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['finalidade'].'":"'.$row['finalidade'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>