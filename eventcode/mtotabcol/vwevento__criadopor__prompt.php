<?
require_once(__DIR__."/../../inc/php/functions.php");

// CONTROLLERS
require_once(__DIR__."/../../form/controllers/evento_controller.php");

echo json_encode(EventoController::buscarSolicitantesDeEventos(true));

?>