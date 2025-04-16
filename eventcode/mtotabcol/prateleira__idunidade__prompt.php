<?include_once("../../inc/php/functions.php");
$sql="select idunidade,unidade from unidade where status='ATIVO'  ".getidempresa('idempresa','unidade')." order by  unidade asc;";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar unidade sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idunidade'].'":"'.$row['unidade'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>