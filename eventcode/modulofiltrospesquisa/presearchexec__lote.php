<?
//Cada unidade deve ter seu módulo correspondente configurado
$idunidade = getUnidadePadraoModulo($_GET["_modulo"]);

if(!empty($idunidade)){

//Filtra pela Unidade correspondente ao módulo
$_SESSION["SEARCH"]["WHERE"][] = " idunidade = ".$idunidade;

}
?>