<?
require_once("../../inc/php/functions.php");
require_once("../../form/controllers/nfentrada_controller.php");

echo("[".NfEntradaController::buscarFluxoComprasPrompt($_GET['_modulo'], $_GET["ocultar"])."]");
?>