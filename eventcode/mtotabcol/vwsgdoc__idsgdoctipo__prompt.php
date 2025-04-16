<?require_once("../../inc/php/functions.php");
if(logado()==true){
$sql="select idsgdoctipo,rotulo 
    from sgdoctipo 
    WHERE status = 'ATIVO'
    ORDER by rotulo";

	
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar sgdoctipo sql=".$sql);
$virg="";
$json="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idsgdoctipo'].'":"'.$row['rotulo'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
}
?>
