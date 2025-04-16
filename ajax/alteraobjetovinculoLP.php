<?
require_once("../inc/php/functions.php");

// CONTROLLERS

require_once(__DIR__."/../form/querys/_iquery.php");
require_once(__DIR__."/../form/controllers/_lp_controller.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}

$idlp = $_POST['idlp'];
$idobjetos = explode(',',$_POST['idobjeto']);
$tipoobjeto = $_POST['tipoobjeto'];
$tipoobjetovinc = $_POST['tipoobjetovinc'];
$tabela = $_POST['tabela'];

//deleta todos os vinculos existentes
_LpController::alterarObjetoVinculoPlantelObjeto('d',$tabela,$idlp,$idobjeto,$tipoobjeto,$_SESSION['SESSAO']['USUARIO'],Date('Y-m-d H:i:s'),$_SESSION['SESSAO']['USUARIO'],Date('Y-m-d H:i:s'));
foreach($idobjetos as $k => $idobjeto){
    _LpController::alterarObjetoVinculoPlantelObjeto('i',$tabela,$idlp,$idobjeto,$tipoobjeto,$_SESSION['SESSAO']['USUARIO'],Date('Y-m-d H:i:s'),$_SESSION['SESSAO']['USUARIO'],Date('Y-m-d H:i:s'));
}
