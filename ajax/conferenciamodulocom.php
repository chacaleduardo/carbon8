<?
include("../inc/php/validaacesso.php");
require_once(__DIR__."/../form/controllers/confereamostra_controller.php");

$idempresa= $_POST['modulocom_idempresa'];
$idmodulocom= $_POST['modulocom_idmodulocom'];
$idmodulo= $_POST['modulocom_idmodulo'];
$modulo= $_POST['modulocom_modulo'];
$descricao= $_POST['modulocom_descricao'];
$status= $_POST['modulocom_status'];
$usuario= $_SESSION['SESSAO']['USUARIO'];
$acao= $_POST['modulocom_acao'];

if($acao == "i" && $descricao!=""){
    echo (ConfereAmostraController::insereComentarioConferencia($idmodulo,$descricao,$idempresa,$modulo,$usuario));
} else if($acao == "d"){
    ConfereAmostraController::desativarComentario($idmodulocom);
    echo json_encode([]);
} else if($acao == "u"){
    echo (ConfereAmostraController::atualizarComentario($idmodulocom,$descricao,$usuario));
}else{
    echo json_encode([]);
}
?>