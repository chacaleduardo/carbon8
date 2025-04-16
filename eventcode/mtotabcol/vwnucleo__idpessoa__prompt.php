<?include_once("../../inc/functions.php");
$sql="select idpessoa, nome 
from pessoa 
where idtipopessoa = 2
and status = 'ATIVO'
 ".getidempresa('idempresa','pessoa')."
order by nome";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar pessoa sql=".$sql);
$virg="";
$json.="{";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'"'.$row['idpessoa'].'":"'.$row['nome'].'"';
	$virg=",";
}
$json.="}";
echo($json);
?>