<?
require_once("../form/controllers/estoqueformulados_controller.php");

if($_GET['_modulo']=='formalizacaoproc'){
    $_SESSION["SEARCH"]["WHERE"]['tipo']=" a.tipo='SERVICO'";
}else{
    $_SESSION["SEARCH"]["WHERE"]['tipo']=" a.tipo='PRODUTO'";
}

if($_GET["_idempresa"] == 12)
{
    $idunidadepadrao =  EstoqueFormuladosController::buscarRegrasShareModuloFiltrosPesquisa($_GET["_modulo"]);           
} else {
    $idunidadepadrao = getUnidadePadraoModulo($_GET["_modulo"]);
}

if(!empty($idunidadepadrao))
{
    $_SESSION["SEARCH"]["WHERE"]['idunidade'] = " idunidade IN (".$idunidadepadrao.")";
}

/*
if($_GET['_modulo']=='formalizacaoped'){
    $_SESSION["SEARCH"]["WHERE"]['idtipounidade'] = " idtipounidade = 13";
}else{
   $_SESSION["SEARCH"]["WHERE"]['idtipounidade'] = " idtipounidade = 5";
}


if(!empty($idunidade)){

//Filtra pela Unidade correspondente ao módulo
    
$_SESSION["SEARCH"]["WHERE"][] = " idunidade = ".$idunidade;

}
 *
 */
?>