<?
include_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();
/*
if($jwt["sucesso"] !== true){
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}
*/
$ip = $_REQUEST['ip'];
$status = $_REQUEST['status'];
$iddeviceciclo = $_REQUEST['iddeviceciclo'];
$statuslog = $_REQUEST['statuslog'];


// Cria a requisição com o método, conteúdo e seta um header com o JWT que serão recuperado na validaacesso.php
$context = stream_context_create(array(
	'http' => array(
	'method' => 'GET',
	'header'  => 'Content-type: application/x-www-form-urlencoded\r\n',
	'content' => $status,
	),
));

if($statuslog != ""){
	echo $result = file_get_contents('http://'.$ip.'/'.$status.'?statuslog='.$statuslog, null, $context);
}else{
	echo $result = file_get_contents('http://'.$ip.'/'.$status.'?iddeviceciclo='.$iddeviceciclo, null, $context);
}
		
?>
