<?
require_once("../../inc/php/functions.php");
if(logado()==true){
$sql21="select idplantel, plantel from plantel where 1 ".getidempresa('idempresa','plantel')." order by plantel";
$res21=mysql_query($sql21) or die(mysql_error()." Erro ao buscar motivo sql=".$sql21);
$virg="";
$json21.="[";
while($row21=mysql_fetch_assoc($res21)){
	$json21.=$virg.'{"'.$row21['idplantel'].'":"'.$row21['plantel'].'"}';
	$virg=",";
}
$json21.="]";
echo($json21);
}
?>