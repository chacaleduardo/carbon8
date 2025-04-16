<?require_once("../../inc/php/functions.php");
if(logado()==true){
$sql="select idassunto,assunto 
from assunto where status = 'ATIVO'  ".getidempresa('idempresa','assunto')." order by assunto ";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar motivo sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idassunto'].'":"'.$row['assunto'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
}
?>

