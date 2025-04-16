<?require_once("../../inc/php/functions.php");
if(logado()==true){
$sql="select idmotivo, motivo from motivo where status = 'ATIVO'  ".getidempresa('idempresa','motivo')." order by motivo";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar motivo sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idmotivo'].'":"'.$row['motivo'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
}
?>