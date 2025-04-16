<?
// CONTROLLERS
require_once(__DIR__."/../../form/controllers/sgsetor_controller.php");
require_once(__DIR__."/../../form/controllers/organograma_controller.php");

$iu = $_SESSION['arrpostbuffer']['1']['i']['sgsetor']['idsgsetor'] ? 'i' : 'u';

if($iu == 'u' and $_SESSION['arrpostbuffer']['1']['u']['sgsetor']['status'] == 'INATIVO')
{
	OrganogramaController::removerVinculosPorIdObjetoETipoObjeto($_SESSION['arrpostbuffer']['1']['u']['sgsetor']['idsgsetor'], 'sgsetor');
}

$regra=$_SESSION['arrpostbuffer']['x']['u']['imregra']['idimregra'];  
$status=$_SESSION['arrpostbuffer']['x']['u']['imregra']['status'];  

SgsetorController::atualizarGrupos($status, $regra);
						
// $msg=file_get_contents('https://sislaudo.laudolab.com.br/im/bim.php?call=atualiza') ;

