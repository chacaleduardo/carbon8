<?
require_once(__DIR__."/../inc/php/functions.php");

// CONTROLLERS
require_once(__DIR__."/../form/controllers/dashboardsnippet_controller.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    header("HTTP/1.1 401 Unauthorized");
    die("Token inválido");
}

if(empty($_POST['dashcards'])){
    header("HTTP/1.1 400 Bad Request");
    die("Parâmetro [dashcard] inválido");
}

// $qr = "SELECT * FROM dashcard WHERE iddashcard IN (".$_POST['dashcards'].")";
// $rs = d::b()->query($qr);

$dashCards = DashboardSnippetController::buscarDashCardPorIdDashCard($_POST['dashcards']);

if(!$dashCards){
    header("HTTP/1.1 400 Bad Request");
    die("Falha ao consultar dashcards");
}

// $arrResponse = array();

// while( $rw = mysqli_fetch_assoc($rs) ){
//     $arrResponse[$rw['iddashcard']] = $rw['periodoetl'];
// }

echo json_encode($dashCards);
?>