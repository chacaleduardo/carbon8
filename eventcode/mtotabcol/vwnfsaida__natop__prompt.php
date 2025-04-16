<?
require_once("../../inc/php/functions.php");

$sql="select natop 
from natop where status = 'ATIVO' -- and idnatop in (21,20,25,15,28,9,16,19,8,26,23,13,24,27,1)
order by natop asc";
//die($sql);
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar natureza da operação sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
$json.=$virg.'{"'.$row['natop'].'":"'.$row['natop'].'"}';
$virg=",";
}
$json.="]";
echo($json);
?>