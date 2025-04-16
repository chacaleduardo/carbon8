<?
require_once("/var/www/carbon8/inc/php/functions.php");

$sqlemailnenviado = "select distinct(idobjeto) as idobjeto,idempresa,destinatario 
	from mailfila 
	where
		tipoobjeto = 'comunicacaoext' and (criadoem BETWEEN '2020-08-07 00:00' AND '2020-08-10 23:59:59') limit 1";
$resemailnenviado = d::b()->query($sqlemailnenviado) or die("Consulta dos emails oficiais para reenvio. SQL = ".$sqlemailnenviado);
$num = mysqli_num_rows($resemailnenviado);
$arrtmp=array();
$i=0;
while($row = mysqli_fetch_assoc($resemailnenviado)){
	$arrtmp[$i]["idobjeto"]=$row["idobjeto"];
	$arrtmp[$i]["idempresa"]=$row["idempresa"];
	$arrtmp[$i]["destinatario"]=$row["destinatario"];
	$i++;
}


$jwt_ = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzaXNsYXVkbyIsImlkbHAiOiI5IiwiaWR0aXBvcGVzc29hIjoiMSIsImlkcGVzc29hIjoiMjI2NiIsInVzdWFyaW8iOiJqb3Nlc291c2EiLCJpZGVtcHJlc2EiOiIxIn0.csC1v_813FLCMtGJKv8iypyYfP_aKzbdMoKovK35vmU";

//print_r($arrtmp);
//die();
if($arrtmp != 0){
	echo "Enviando ".$num." emails...<br><br>";
	foreach ($arrtmp as $name => $value) {
		// Monta parâmetros que ser?o enviados por GET
		$content = http_build_query(array(
			'idobjeto' => $value["idobjeto"],
			'idempresa' => $value["idempresa"],
			'destinatario' => $value["destinatario"],
			'reenvio' => 'Y'
		));
		
		// Cria a requisiç?o com o método, conteúdo e seta um header com o JWT que será recuperado na validaacesso.php
		$context = stream_context_create(array(
			'http' => array(
			'method' => 'GET',
			'header'  => 'jwt: '.$jwt_,
			'content' => $content,
			),
		));
		
		// Alterar para enviar um teste de requisiç?o, caso positivo, será inserido na tabela LOG
		//$result = file_get_contents('https://sislaudo.laudolab.com.br/ajax/testerequisicao.php?'.$content, null, $context);
		
		echo "Enviando resultados_".$value["idobjeto"]." para ".$value["destinatario"]."...<br>";
		// Envia a requisiç?o para o arquivo de envio de email oficial com os parâmetros necessários
		$result = file_get_contents('https://sislaudo.laudolab.com.br/tmp/reenvioemailoficialreq.php?'.$content, null, $context);
		print_r($result);
		echo "Enviado!!!<br><br>";
	}
}else{
	echo "N?o há resultados para serem enviados !!!";
	die();
}
?>