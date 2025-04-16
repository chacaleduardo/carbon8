<?include_once("../../inc/php/functions.php");
$sql="SELECT idagencia,agencia 
		from agencia where idagencia in (".getModsUsr("AGENCIAS").") 
		and status='ATIVO' order by agencia";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar pessoa sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idagencia'].'":"'.$row['agencia'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>