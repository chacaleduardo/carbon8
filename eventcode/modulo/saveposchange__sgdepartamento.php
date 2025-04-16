<?
// CONTROLLERS
require_once(__DIR__."/../../form/controllers/organograma_controller.php");
require_once(__DIR__."/../../form/controllers/sgdepartamento_controller.php");



$iu = $_SESSION['arrpostbuffer']['1']['i']['sgdepartamento']['idsgdepartamento'] ? 'i' : 'u';
if($iu == 'u' and $_SESSION['arrpostbuffer']['1']['u']['sgdepartamento']['status'] == 'INATIVO')
{
	OrganogramaController::removerVinculosPorIdObjetoETipoObjeto($_SESSION['arrpostbuffer']['1']['u']['sgdepartamento']['idsgdepartamento'], 'sgdepartamento');
}




$tipoobjetovinc= $_SESSION['arrpostbuffer']['x']['i']['objetovinculo']['tipoobjetovinc'];
$idsgsetor= $_SESSION['arrpostbuffer']['x']['i']['objetovinculo']['idobjetovinc'];
$idsgdepartamento= $_SESSION['arrpostbuffer']['x']['i']['objetovinculo']['idobjeto'];

if($tipoobjetovinc== 'sgsetor' and !empty($idsgsetor) and !empty($idsgdepartamento)){


		
	$arrun=SgDepartamentoController::buscarUnidadeVinculadaIdSgDepartamentoSetor($idsgsetor);

	foreach ($arrun as $row) {
		
		$arrobj=SgDepartamentoController::buscarUnidadeVinculadaIdSgDepartamento($row['idunidade'],$idsgdepartamento);
		foreach ($arrobj as $rid) {			
			$resp=SgDepartamentoController::deletaUnidadeobjeto($rid['idunidadeobjeto']);
		}

	}
}
?>