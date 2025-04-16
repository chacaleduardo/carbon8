<?
require_once("../inc/php/functions.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pk = $_GET['pk'];
	$modulo = $_GET['modulo'];
	$chave = 'lock:' . $modulo . ':' . $pk;
	
	$renova = renovaBloqueioTela($pk,$modulo);
	if ($renova){
		if ($renova)
		header('Content-Type: application/json');
			$retorno = array(
				"status" => true,
				"timeout" => re::dis()->ttl($chave),
				"message" => "Bloqueio renovado com sucesso."
			);
			echo json_encode($retorno);

	}else{
		header('Content-Type: application/json');
			$retorno = array(
				"status" => false,
				"timeout" => false,
				"message" => "Bloqueio não renovado."
			);
			echo json_encode($retorno);
	}
}