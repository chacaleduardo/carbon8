<?require_once("../../inc/php/functions.php");
if(logado()==true){
$sql="SELECT idtagtipo,tagtipo
FROM tagtipo
where status = 'ATIVO'
 ".getidempresa('idempresa','tagtipo')." ORDER BY tagtipo";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar pessoa sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idtagtipo'].'":"'.$row['tagtipo'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
}
?>