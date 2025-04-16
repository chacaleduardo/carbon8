<?
//Cada unidade deve ter seu módulo correspondente configurado
$idunidade = getUnidadePadraoModulo($_GET["_modulo"]);

if(empty($idunidade)){
	die("Presearch amostraaves: Erro ao recuperar ID da Unidade.\nCada unidade deve ter seu módulo correspondente configurado");
}

//Filtra pela Unidade correspondente ao módulo
$_SESSION["SEARCH"]["WHERE"][] = " idunidade = ".$idunidade;

?>