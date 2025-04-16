<?
	// QUERYS
	require_once(__DIR__."/../../form/querys/_iquery.php");
	require_once(__DIR__."/../../form/querys/imgrupo_query.php");
	require_once(__DIR__ . "/../../form/querys/log_query.php");

	// CONTROLLERS
	require_once(__DIR__."/../../form/controllers/imgrupo_controller.php");

	ImGrupoController::atualizarGruposSetores();

	$idpessoaemail=$_SESSION['arrpostbuffer']['1']['u']['imgrupo']['idpessoaemail'];

	$idimgrupo=$_SESSION['arrpostbuffer']['x']['i']['imgrupopessoa']['idimgrupo'];

	if(!empty($idimgrupo)){
		
		$imGrupo = SQL::ini(ImGrupoQuery::buscarPorChavePrimaria(), [
			'pkval' => $idimgrupo
		])::exec();

		if($imGrupo->data[0]['idpessoaemail']>0)
		{
			$idpessoaemail = $imGrupo->data[0]['idpessoaemail'];
		}
	}
 ?> 