<?
require_once("../inc/php/functions.php");
require_once("../inc/php/laudo.php");
require_once("../inc/php/permissao.php");

// QUERYS
require_once(__DIR__ . "/../form/querys/_iquery.php");
require_once(__DIR__ . "/../form/querys/eventotipo_query.php");
require_once(__DIR__ . "/../form/querys/pessoa_query.php");
require_once(__DIR__ . "/../form/querys/imgrupo_query.php");
require_once(__DIR__ . "/../form/querys/_modulo_query.php");

// CONTROLLERS
require_once(__DIR__ . "/../form/controllers/eventotipo_controller.php");

$jwt = validaTokenReduzido();

if ($jwt["sucesso"] !== true) {
	echo JSON_ENCODE([
		'error' => "Erro: Não autorizado."
	]);
	die;
}

$opcao              = filter_input(INPUT_GET, "vopcao");
$ideventotipo		= filter_input(INPUT_GET, "videventotipo");
$vobjeto			= filter_input(INPUT_GET, "vobjeto");
$vidobjeto			= filter_input(INPUT_GET, "vidobjeto");

global $JSON;

if (empty($opcao)) {
	die('Opção não foi enviada!');
} else {

	if ($opcao == 'carregaparticipantes') {
		if (empty($ideventotipo)) {
			die('Variável POST não enviada corretamente!');
		} else {

			$funcionarios = SQL::ini(EventoTipoQuery::buscarEventoTipoPorIdEventoTipoEIdEmpresa(), [
				'ideventotipo' => $ideventotipo,
				'idempresa' => cb::idempresa()
			])::exec();


			$funcionarios = $funcionarios->data[0];
			$funcionarios["jsonconfig"];
			$func = json_decode($funcionarios["jsonconfig"]);

			$resultsetor = array();
			$resultpessoa = array();
			$i = 0;
			$j = 0;
			foreach ($func as $key => $object) {
				foreach ($object as $k => $v) {
					//  print_r($v); 
					if ($v->tipo == 'imgrupo') {
						$resultsetor[$i++] = $v->value;
					}
					if ($v->tipo == 'pessoa') {
						$resultpessoa[$j++] = $v->value;
					}
				}
			}

			if ($resultsetor) {
				$resultsetor = implode(",", $resultsetor);
			} else {
				$resultsetor = "''";
			}

			if ($resultpessoa) {
				$resultpessoa = implode(",", $resultpessoa);
			} else {
				$resultpessoa = "''";
			}

			$pessoas = SQL::ini(PessoaQuery::buscarPessoaPorIdPessoaEGetIdEmpresa(), [
				'idpessoa' => $resultpessoa,
				'getidempresa' => getidempresa('p.idempresa', 'pessoa')
			])::exec();

			$grupos = SQL::ini(ImGrupoQuery::buscarGruposPorIdImGrupoEGetIdEmpresa(), [
				'idimgrupo' => $resultsetor,
				'getidempresa' => getidempresa('i.idempresa', 'pessoa')
			])::exec();

			$result = array();
			$i = 0;

			foreach ($pessoas->data as $pessoa) {

				$result[$i]["value"] 				= $pessoa["idpessoa"];
				$result[$i]["label"] 				= $pessoa["nomecurto"];
				$result[$i]["tipo"] 				= "pessoa";
				$result[$i]["idobjetoext"] 			= $pessoa["idpessoa"];
				$result[$i]["tipoobjetoext"] 		= "";
				$result[$i]["inseridomanualmente"] 	= "S";
				$result[$i]["status"] 				= "info";

				$i++;
			}

			foreach ($grupos as $grupo) {

				$result[$i]["value"] 	= $grupo["idimgrupo"];
				$result[$i]["label"] 	= $grupo["grupo"];
				$result[$i]["tipo"] 	= "imgrupo";
				$result[$i]["status"] 	= "info";

				$i++;
			}

			echo json_encode($result, JSON_UNESCAPED_UNICODE);
		}
	} elseif ($opcao == 'carregaTipos') {

		if (!empty($vobjeto)) {
			$chavePrimaria = SQL::ini(_ModuloQuery::buscarModuloComChavePrimariaPorModulo(), [
				'modulo' => $vobjeto
			])::exec();

			$url = explode("&", $vidobjeto);
			foreach ($url as $_url) {
				$id = explode("=", $_url);
				if ($id[0] == $chavePrimaria->data[0]['chavefts']) {
					$idmodulo = $id[1];
				}
			}
		}

		$eventoTipos = EventoTipoController::carregarEventoTipoPorTipoEIdPessoa('dashboard', $_SESSION["SESSAO"]["IDPESSOA"]);

		$result = array();
		$i = 0;

		foreach ($eventoTipos as $tipo) {
			$result[$i]["value"]	= $tipo["id"];
			$result[$i]["label"]	= $tipo["tipo"];
			$result[$i]["objeto"]	= $vobjeto;
			$result[$i]["idobjeto"]	= $idmodulo;
			$result[$i]["idempresa"]	= $tipo["idempresa"];

			$i++;
		}

		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	} elseif ($opcao == 'carregaTiposAlerta') {
		//so aparece quem e dashboard e que para quem e criador do tipo de eventos
		$eventoTipos = EventoTipoController::carregarEventoTipoPorTipoEIdPessoa('dashboard', $_SESSION["SESSAO"]["IDPESSOA"]);
		$result = array();
		$i = 0;

		foreach ($eventoTipos as $tipo) {

			$result[$i]["value"]			= $tipo["id"];
			$result[$i]["label"]			= $tipo["tipo"];
			$result[$i]["eventotitle"]		= $tipo["eventotitle"];
			$result[$i]["idempresa"]		= $tipo["idempresa"];
			$i++;
		}


		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	}
}
