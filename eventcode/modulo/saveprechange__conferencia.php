<? 
$arrpostbuffer = $_SESSION["arrpostbuffer"];
//print_r($arrpostbuffer);
//$status=$_POST['status'];
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
require_once("../model/evento.php");

$qtdreg= count($arrpostbuffer);
if($qtdreg > 0){			
    foreach ($arrpostbuffer as $key => $value) {
        $idobjeto=$_SESSION['arrpostbuffer'][$key]['i']['carrimbo']['idobjeto'];
    
        //Atualiza o Status do Resultado
		$rowFluxo = ConferirResultadoController::buscarFluxoParaResultados($idobjeto);
		 
		FluxoController::alterarStatus($rowFluxo['modulo'], 'idresultado', $idobjeto, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], '', 'Y', '', $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);		
    }

}else{
	die("Nenhum registro selecionado para CONFERENCIA.");
}
