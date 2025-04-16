<?php //Hermes 05/04/2013
require_once("../inc/php/validaacesso.php");
//ini_set("display_errors","1");
//error_reporting(E_ALL);
$idremessa = $_GET["idremessa"]; 

if(empty($idremessa)){
 die("É necessário informar o ID da Remessa.");
}

$sql="select texto,dataenvio from remessa where idremessa=".$idremessa;
$res=mysql_query($sql) or die(mysql_error()." erro ao buscar o texto da remessa ".$sql);
$row=mysql_fetch_assoc($res);

if(empty($row['texto'])){
	die("Năo encontrado o texto da remessa");
}


ob_end_clean();//năo envia nada para o browser antes do termino do processamento

/* Gerar o nome do arquivo para exportar
 * Substitui qualquer caractere estranho pelo sinal de '_'
 * Caracteres que NAO SERAO substituidos:
 *   - qualquer caractere de A a Z (maiusculos)
 *   - qualquer caracteres de a a z (minusculos)
 *   - qualquer caractere de 0 a 9
 *   - e pontos '.'
 */ 

//gera o csv
header("Content-type: text/xml; charset=UTF-8");
header("Content-Disposition: attachment; filename=re_".$idremessa.".txt");
header("Pragma: no-cache");
header("Expires: 0");

echo($row['texto']);
