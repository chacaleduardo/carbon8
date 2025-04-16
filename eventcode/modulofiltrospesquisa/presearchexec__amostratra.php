<?
//Cada unidade deve ter seu módulo correspondente configurado
$idunidade = getUnidadePadraoMatriz($_GET["_modulo"],cb::idempresa(), cb::habilitarMatriz());

if(empty($idunidade)){
	die("Presearch amostraaves: Erro ao recuperar ID da Unidade.\nCada unidade deve ter seu módulo correspondente configurado");
}

$_SESSION["SEARCH"]["WHERE"]['status'] = " status != 'CANCELADO'"; 
//Filtra pela Unidade correspondente ao módulo
$_SESSION["SEARCH"]["WHERE"][] = " idunidade in (".$idunidade.")";

?>