<?
require_once(__DIR__."/../../form/controllers/evento_controller.php");
require_once(__DIR__."/../../form/controllers/pessoa_controller.php");

$mensagem = empty($_POST["mensagem"]) 
    ? "Adicionou o arquivo ".$arq_nome 
    : $_POST["mensagem"] . " ". $arq_nome;

EventoController::inserirComentarioEvento($_idobjeto, $mensagem, $_SESSION["SESSAO"]["USUARIO"]);
?>