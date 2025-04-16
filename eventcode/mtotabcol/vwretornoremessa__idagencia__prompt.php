<?include_once("../../inc/php/functions.php");
$sql="select a.idagencia, a.agencia 
from agencia a where 1 ".idempresaFiltros("a")."  and a.status='ATIVO' order by a.agencia";

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