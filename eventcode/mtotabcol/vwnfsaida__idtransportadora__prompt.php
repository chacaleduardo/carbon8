<?include_once("../../inc/php/functions.php");
$sql="SELECT idpessoa,nome 
from pessoa 
where idtipopessoa = 11
-- ".getidempresa('idempresa','pessoa')."
and status = 'ATIVO'
 order by nome";
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