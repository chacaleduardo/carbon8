<?
require_once("../../inc/php/functions.php");
$sql="select idrheventofolha,titulo from rheventofolha where status ='ATIVO' order by titulo";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar rheventofolha sql=".$sql);
$virg="";
$json='';
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idrheventofolha'].'":"'.$row['titulo'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>