<?
require_once("../inc/php/functions.php");
require_once("../form/controllers/endereco_controller.php");

$uf= $_GET['uf']; 

if(empty($uf)){
	die("UF (Estado) NAO ENVIADO");
}


   
		echo '<option value=""></option>';
		echo fillselect(EnderecoController::buscarCodcidadeCidade($uf));
          
?>


