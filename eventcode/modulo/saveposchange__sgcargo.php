<?
// CONTROLLERS
require_once(__DIR__."/../../form/controllers/sgcargo_controller.php");

$iu = $_SESSION['arrpostbuffer']['1']['u']['sgcargo']['idsgcargo'] ? 'u' : 'i';

if($iu == 'u')
{
    $idsgcargo = $_SESSION["arrpostbuffer"]["1"]["u"]["sgcargo"]["idsgcargo"];
    
	SgCargoController::atualizarPessoaFuncaoInseridasPorIdSgCargo($idsgcargo); 
    
	SgCargoController::atualizarPessoaFuncaoRemovidasPorIdSgCargo($idsgcargo);
    
	SgCargoController::atualizarPessoaFuncaoDePessoasSemCargo();
}