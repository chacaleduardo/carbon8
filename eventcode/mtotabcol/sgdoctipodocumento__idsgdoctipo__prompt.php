<?require_once("../../inc/php/functions.php");
if(logado()==true){
$sql="SELECT idsgdoctipo,rotulo FROM sgdoctipo 
	where 1 ".getidempresa('idempresa','sgdoctipo')." and status= 'ATIVO' order by rotulo";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar sgdoctipo sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idsgdoctipo'].'":"'.$row['rotulo'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
}
?>
