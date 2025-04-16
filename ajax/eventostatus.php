<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/laudo.php");
require_once("../model/evento.php");

//Chama a Classe Evento
$eventoclass = new EVENTO();

$_x_u_fluxostatuspessoa_idfluxostatuspessoa = $_GET['inidfluxostatuspessoa'];
$_x_u_fluxostatuspessoa_idfluxostatus = $_GET['inideventostatus'];
$inocultar = $_GET['inocultar'];
$historico = $_GET['historico'];
$voltarStatus = $_GET['voltarStatus'];
                                 
$eventoclass->atualizaEventoStatus($_x_u_fluxostatuspessoa_idfluxostatuspessoa, $_x_u_fluxostatuspessoa_idfluxostatus, $inocultar, 'false', $voltarStatus);
?>