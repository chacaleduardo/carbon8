<?
// CONTROLLERS
require_once(__DIR__."/../../form/controllers/sgarea_controller.php");
require_once(__DIR__."/../../form/controllers/organograma_controller.php");

$iu = $_SESSION['arrpostbuffer']['1']['i']['sgarea']['idsgarea'] ? 'i' : 'u';
if($iu == 'u' and $_SESSION['arrpostbuffer']['1']['u']['sgarea']['status'] == 'INATIVO'){

	OrganogramaController::removerVinculosPorIdObjetoETipoObjeto($_SESSION['arrpostbuffer']['1']['u']['sgarea']['idsgarea'], 'sgarea');
}

if($_SESSION['arrpostbuffer']['1']['i']['sgarea']['idsgconselho'] || $_SESSION['arrpostbuffer']['1']['u']['sgarea']['idsgconselho'])
{
	$idSgarea = $_SESSION["_pkid"];
	$idSgconselho = $_SESSION['arrpostbuffer']['1']['i']['sgarea']['idsgconselho'] ?? $_SESSION['arrpostbuffer']['1']['u']['sgarea']['idsgconselho'];

	$objetoVinculo = SgareaController::atualizarVinculoComSgconselhoPorIdSgareaEIdSgconselho($idSgarea, $idSgconselho);
}

?>