<?
require_once("../../inc/php/functions.php");
if(logado()){
    if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==16){ $inrep='and idcontaitem = 35';}
$sql = getContaItemSelect();
$res = mysql_query($sql) or die(mysql_error()." Erro ao buscar agencia sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idcontaitem'].'":"'.$row['contaitem'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
}
?>