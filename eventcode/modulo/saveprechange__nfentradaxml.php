<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
require_once("../api/nf/index.php");
require_once(__DIR__."/../../form/controllers/nfentradaxml_controller.php");
 

//print_r($_POST);die;
$histobs = $_SESSION['arrpostbuffer']['1']['u']['nfentradaxml']['obs'];
$status = $_SESSION['arrpostbuffer']['1']['u']['nfentradaxml']['status'];
$idnfentradaxml = $_SESSION['arrpostbuffer']['1']['u']['nfentradaxml']['idnfentradaxml'];
if (!empty($histobs)){
    $ultimaobs = NfentradaxmlController::BuscarObsNfentradaxml($idnfentradaxml);
    $usuario = $_SESSION["SESSAO"]["USUARIO"];
    $data = date('d/m/Y H:i:s');  
    if(!empty($ultimaobs)){
        $_SESSION['arrpostbuffer']['1']['u']['nfentradaxml']['obs']= $ultimaobs . '<br>' . '| Alterado por: ' . $usuario. ' ' . '| Data: ' . $data . ' ' . '| Observação: '. $histobs . ' ' . '| Status selecionado:' . $status;
    }else{ 
        $_SESSION['arrpostbuffer']['1']['u']['nfentradaxml']['obs']= '| Alterado por: ' . $usuario. ' ' . '| Data: ' . $data.' ' .'| Observação: '. $histobs . ' ' . '| Status selecionado:' . $status; 
    }
}
?>