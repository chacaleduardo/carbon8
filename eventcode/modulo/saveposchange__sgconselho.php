<?
// CONTROLLERS
require_once(__DIR__."/../../form/controllers/organograma_controller.php");

$iu = $_SESSION['arrpostbuffer']['1']['i']['sgconselho']['idsgconselho'] ? 'i' : 'u';
if($iu == 'u' and $_SESSION['arrpostbuffer']['1']['u']['sgconselho']['status'] == 'INATIVO'){

	OrganogramaController::removerVinculosPorIdObjetoETipoObjeto($_SESSION['arrpostbuffer']['1']['u']['sgconselho']['idsgconselho'], 'sgconselho');
}
?>