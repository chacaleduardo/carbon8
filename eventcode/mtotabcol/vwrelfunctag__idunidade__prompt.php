<?require_once("../../inc/php/functions.php");
if(logado()==true){
$sql="SELECT idunidade,unidade
FROM unidade
where status = 'ATIVO'
 ".getidempresa('idempresa','unidade')." ORDER BY unidade";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar pessoa sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idunidade'].'":"'.$row['unidade'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
}
?>