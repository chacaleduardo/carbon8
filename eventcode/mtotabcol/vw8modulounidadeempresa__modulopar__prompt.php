<?include_once("../../inc/php/functions.php");
$sql="select modulo as modulopar, rotulomenu from carbonnovo._modulo  where tipo = 'DROP' and status = 'ATIVO' order by rotulomenu";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar modulo tipo sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['modulopar'].'":"'.$row['rotulomenu'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>