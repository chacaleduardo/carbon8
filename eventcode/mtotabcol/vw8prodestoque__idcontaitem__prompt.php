<?include_once("../../inc/php/functions.php");
$sql="SELECT idcontaitem, contaitem 
		FROM contaitem t 
        WHERE t.status='ATIVO' 
		 AND  t.idempresa = ".cb::idempresa()."
		 ORDER BY contaitem ";

$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar contaitem sql=".$sql);
$virg="";
$json="";
$json="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idcontaitem'].'":"'.$row['contaitem'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>