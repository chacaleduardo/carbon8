<?
require_once __DIR__."/../../inc/php/functions.php";
require_once __DIR__."/notificacao_controller.php";

$vjwt = validaTokenReduzido();

$jwt = $vjwt["token"];

if($vjwt["sucesso"] === true 
    and !empty($_GET["__cmd"])
    and !empty($jwt->idpessoa)){

    NotificacaoController::$jwt = $jwt;

    $__cmd = $_GET["__cmd"];

    switch($__cmd){
        case 'ultimasNotificacoes':
            $lNotif = NotificacaoController::ultimasNotificacoes($_GET["offset"], $_GET["filtros"]);
            break;
        case 'alterarStatusNotificacaoPorId':
            $lNotif = NotificacaoController::alterarStatusNotificacaoPorId($_POST["idnotificacao"], $_POST["status"]);
            break;
        case 'adicionarRestricaoNotificacaoUsuario':
            $lNotif = NotificacaoController::adicionarRestricaoNotificacaoUsuario($_POST["tipoRestricao"], $_POST["idObjeto"], $_POST["tipoObjeto"]);
            break;
        case 'removerRestricaoNotificacao':
            $lNotif = NotificacaoController::removerRestricaoNotificacao($_POST["idNotificacaoRestricao"]);
            break;
        default:
            $lNotif = array();
            break;
    }

    echo json_encode($lNotif);
}else{
    header("HTTP/1.1 401 Unauthorized");
}
?>