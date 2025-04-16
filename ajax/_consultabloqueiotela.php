<?
require_once("../inc/php/functions.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$pk = $_GET['pk'];
	$modulo = $_GET['modulo'];
	$chave = 'lock:' . $modulo . ':' . $pk;
	$idPessoa = $_SESSION["SESSAO"]["IDPESSOA"];
 
	$timeout = retArrModuloConf($modulo )["timeout"];
	if ($timeout && $timeout != '' && $timeout != '00:00') {
		$verificaChaveExiste = consultaBloqueioRedis($chave);

		if ($verificaChaveExiste) {
			$resultadoJson = json_decode($verificaChaveExiste);

			if ($resultadoJson->idpessoa != $idPessoa) {
				// Tem bloqueio e não é desse usuário.
				http_response_code(400); // Status 400 
				echo 'O usuário '. $resultadoJson->nome . ' está trabalhando neste ID: '.$pk .'. Não foi possível salvar os dados.' ;
				exit;
			}
		}
	}

	// Se não houver bloqueio ou for do próprio usuário
	header('Content-Type: application/json');
	echo json_encode(['status' => 'ok']);
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $pk = $_GET['pk'];
    $modulo = $_GET['modulo'];
    $chave = 'lock:' . $modulo . ':' . $pk;
    $idPessoa = $_SESSION["SESSAO"]["IDPESSOA"];

    if ($pk && $modulo && $idPessoa) {
        $verificaChaveExiste = consultaBloqueioRedis($chave);

        if ($verificaChaveExiste) {
            $resultadoJson = json_decode($verificaChaveExiste);

            header('Content-Type: application/json');
            if ($resultadoJson->idpessoa != $idPessoa) {
                $retorno = array(
                    "status" => true,
                    "me" => false,
                    "timeout" => re::dis()->ttl($chave),
                    "nome" => $resultadoJson->nome,
                    "idpessoa" => $resultadoJson->idpessoa
                );
            } else {
                $retorno = array(
                    "status" => true,
                    "me" => true,
                    "timeout" => re::dis()->ttl($chave),
                    "nome" => $resultadoJson->nome,
                    "idpessoa" => $resultadoJson->idpessoa
                );
            }
            echo json_encode($retorno);
        } else {
            header('Content-Type: application/json');
            $retorno = array(
                "status" => false,
                "me" => false,
                "timeout" => false,
                "nome" => false,
                "idpessoa" => false
            );
            echo json_encode($retorno);
        }
    } 
	
}
