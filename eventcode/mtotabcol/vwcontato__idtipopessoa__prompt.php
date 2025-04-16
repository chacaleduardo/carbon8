<?include_once("../../inc/functions.php");
$sql="select idtipopessoa,tipopessoa
from tipopessoa 
where contato is null
and status = 'ATIVO'";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar tipopessoa sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idtipopessoa'].'":"'.$row['tipopessoa'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>