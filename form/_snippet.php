<?
require_once("../inc/php/validaacesso.php");

$snpt = getSnippets()[$_GET["idsnippet"]];

if($snpt["tipo"]=="PHP" and strlen(trim($snpt["code"]))>0){
	//Cria arquivo em memà³ria ram com o cà³digo php a ser executado, para evitar uso de eval e também evitar a necessidade de geração de arquivos externos na pasta "eventcode"
	$fp = fopen("php://temp/", 'r+');
	fputs($fp, $snpt["code"]);
	rewind($fp);
	ob_start();	//Não gerar saà­da para o browser
	require 'data://text/plain;base64,'. base64_encode(stream_get_contents($fp));
	$resultado= ob_get_clean();//Limpa a saà­da antes que seja enviada para o browser
	echo $resultado;
}

?>