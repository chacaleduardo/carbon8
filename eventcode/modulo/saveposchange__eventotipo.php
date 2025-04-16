<?

require_once(__DIR__."/../../form/controllers/eventotipo_controller.php");

$ideventotipo             = $_SESSION['arrpostbuffer']['1']['u']['eventotipo']['ideventotipo'];

//SABER QUAL TOKEN INICIAL E ÚLTIMA VERSÃO DO EVENTO TIPO
if (!empty($ideventotipo)){
	EventoTipoController::atualizarVersoesDeEventosQueEstejamNoStatusInicial($ideventotipo);
		
}	
?>