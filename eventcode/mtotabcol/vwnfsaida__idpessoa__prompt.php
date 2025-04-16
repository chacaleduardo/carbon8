<?include_once("../../inc/php/functions.php");
$sql="select idpessoa,nome 
from pessoa 
where idtipopessoa = 2
 ".getidempresa('idempresa','pessoa')." order by nome";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar prodserv sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
$json.=$virg.'{"'.$row['idpessoa'].'":"'.retira_acentos(str_replace('/t',' ',$row['nome'])).'"}';
$virg=",";
}
$json.="]";
echo($json);
?>