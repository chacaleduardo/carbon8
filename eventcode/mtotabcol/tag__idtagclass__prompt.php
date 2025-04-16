<?require_once("../../inc/php/functions.php");
if(logado()==true){
$sql="SELECT idtagclass,tagclass
FROM tagclass
where status = 'ATIVO'
1 ".getidempresa('idempresa','tagclass')." ORDER BY tagclass";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar pessoa sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idtagclass'].'":"'.$row['tagclass'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
}
?>