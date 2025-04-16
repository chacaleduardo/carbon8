<?
require_once("../../inc/php/functions.php");


    if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["OBRIGATORIOCONTATO"]=='Y'){ $inrep='and idpessoa = '.$_SESSION["SESSAO"]["IDPESSOA"];}
$sql="select idpessoa,nome from pessoa where idempresa = ".cb::idempresa()." ".$inrep." and (idtipopessoa = 12 or (flagobrigatoriocontato='Y' and idtipopessoa = 1)) and status = 'ATIVO' order by nome;";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar agencia sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idpessoa'].'":"'.$row['nome'].'"}';
	$virg=",";
}
$json.="]";
echo($json);

?>