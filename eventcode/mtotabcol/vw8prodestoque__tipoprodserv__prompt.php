<?include_once("../../inc/php/functions.php");
$sql="select idtipoprodserv,tipoprodserv from tipoprodserv t 
        where t.status='ATIVO' ".getidempresa('idempresa','tipoprodserv')."  order by tipoprodserv ";

$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar tipoprodserv sql=".$sql);
$virg="";
$json="";
$json="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idtipoprodserv'].'":"'.$row['tipoprodserv'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>