<?
require_once("../inc/php/functions.php");
require_once(__DIR__."/../form/controllers/tipoprodserv_controller.php");

$idcontaitem = $_GET['idcontaitem']; 

if(empty($idcontaitem))
{
	die("Categoria NAO ENVIADA");
}

$listarTipoContaItem = TipoProdServController::listarContaItemTipoProdservTipoProdServ($idcontaitem);
    
// SEMPRE ENVIAR UM OPTION VAZIO
echo "<option value='' selected></option>";

foreach($listarTipoContaItem as $_tipoContaItem) 
{
    echo "<option value='".$_tipoContaItem["idtipoprodserv"]."'>".$_tipoContaItem["tipoprodserv"]."</option>";        
}
?>