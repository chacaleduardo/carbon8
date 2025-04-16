<?
require_once("../inc/php/functions.php");

// QUERYS
require_once(__DIR__."/../form/querys/_iquery.php");
require_once(__DIR__."/../form/querys/webmailassinaturaobjeto_query.php");

// CONTROLLERS
require_once(__DIR__."/../form/controllers/log_controller.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}

if(!empty($_POST['id']) and !empty($_POST['idwebmailassinaturaobjetos']) and !empty($_POST['tipoobjeto'])){
    $inserindoTemplate = SQL::ini(WebmailAssinaturaObjeto::inserirTemplate(), [
        'idobjeto' => $_POST['id'],
        'tipoobjeto' => $_POST['tipoobjeto'],
        'idwebmailassinaturaobjetos' => $_POST['idwebmailassinaturaobjetos']
    ])::exec();

    if($inserindoTemplate->error())
    {
        $dadosLog = [
            'idempresa' => $_SESSION["SESSAO"]["IDEMPRESA"],
            'sessao' => session_id(),
            'tipoobjeto' => $_POST['tipoobjeto'],
            'idobjeto' => $_POST['id'],
            'tipolog' => 'webmailassinaturaobjeto',
            'log' => 'Erro:' . $inserindoTemplate->sql(),
            'status' => '',
            'info' => $inserindoTemplate->errorMessage(),
            'criadoem' => "NOW()",
            'data' => "NOW()"
        ];
        
        $inserirLog = LogController::inserir($dadosLog);

        cbSetPostHeader('0', 'erro');

        echo addslashes($dadosLog['log']);
        die;
    }

    cbSetPostHeader('1', 'html');
    die;
}else{
    cbSetPostHeader('0', 'erro');
    echo "Os campos id ou idwebmailassinaturaobjetos ou tipoobjeto não podem ser vazios";
    die;
}
?>