<?
require_once(__DIR__."/../inc/php/functions.php");
require_once(__DIR__."/../form/controllers/nfentrada_controller.php");

$idpessoa = $_POST['idpessoa'];

$idNf = $_GET['idNf'];
$idNfBusca = $_GET['idNfBusca'];
$idEmpresa = $_GET['idEmpresa'];

if(!empty($_POST["comandoExecutar"] || $_GET["comandoExecutar"]))
{
	$_comandoExecutar = $_POST["comandoExecutar"] ?? $_GET["comandoExecutar"];

    switch($_comandoExecutar){
        case 'buscarDadosPessoa':
            $retAjax = NfEntradaAjax::buscarPessoa($idpessoa);
        case 'buscarComprasDisponiveisParaVinculo':
            $retAjax = NfEntradaAjax::buscarComprasDisponiveisParaVinculo($idNfBusca, $idNf, $idEmpresa);
        break;
    }

    echo json_encode($retAjax);
} else {
    cbSetPostHeader("0", "Não foi enviado nenhum Comando a ser executado");
}

Class NfEntradaAjax
{
	public static function buscarPessoa($idpessoa)
	{
		return NfEntradaController::buscarPessoa($idpessoa); 
	}

	public static function buscarComprasDisponiveisParaVinculo($idNfBusca, $idNf, $idEmpresa)
	{
		return NfEntradaController::buscarComprasDisponiveisParaVinculo($idNfBusca, $idNf, $idEmpresa); 
	}
}
?>
