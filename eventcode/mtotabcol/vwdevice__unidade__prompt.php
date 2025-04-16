<?include_once("../../inc/php/functions.php");
if(logado()==true){
$sql="SELECT u.idunidade,u.unidade
    from 
    unidade u
    where 
    exists (select 1 from vwdevice d where d.idunidade = u.idunidade)
    ORDER by unidade";

	
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar sgdoctipo sql=".$sql);
$virg="";
$json="[";
while($row1=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row1['idunidade'].'":"'.$row1['unidade'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
}
?>