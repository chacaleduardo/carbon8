<?include_once("../../inc/php/functions.php");
$sql = "SELECT idtipoprodserv, tipoprodserv FROM tipoprodserv t 
        WHERE t.status = 'ATIVO' ".getidempresa('idempresa','tipoprodserv')." ORDER BY tipoprodserv ";

$res = mysql_query($sql) or die(mysql_error()." Erro ao buscar tipoprodserv sql=".$sql);
$virg = "";
$json .= "[";
while($row = mysql_fetch_assoc($res)){
	$json .= $virg.'{"'.$row['idtipoprodserv'].'":"'.$row['tipoprodserv'].'"}';
	$virg = ",";
}
$json .= "]";
echo($json);
?>