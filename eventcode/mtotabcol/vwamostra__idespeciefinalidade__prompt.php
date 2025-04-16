<?include_once("../../inc/php/functions.php");
$sql="select idespeciefinalidade,especietipofinalidade from vwespeciefinalidade where 1 ".getidempresa('idempresa','vwespeciefinalidade')." order by especietipofinalidade;";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar especie/finalidade sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idespeciefinalidade'].'":"'.$row['especietipofinalidade'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>