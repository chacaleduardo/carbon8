<?include_once("../../inc/php/functions.php");
$sql = "SELECT idcontaitem, contaitem FROM contaitem t 
        WHERE t.status = 'ATIVO'  ".getidempresa('idempresa','contaitem')."  ORDER BY contaitem ";

$res = mysql_query($sql) or die(mysql_error()." Erro ao buscar tipoprodserv sql=".$sql);
$virg = "";
$json .= "[";
while($row = mysql_fetch_assoc($res)){
	$json .= $virg.'{"'.$row['idcontaitem'].'":"'.$row['contaitem'].'"}';
	$virg = ",";
}
$json .= "]";
echo($json);
?>
