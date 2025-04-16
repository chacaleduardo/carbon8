<?

$id = $_POST['idsolcomitem'];
if(!empty($_POST['idsolcomitem']))
{
    ProdServController::atualizarIdProdservPorIdSolcomItem($_SESSION['_pkid'], $_POST['idsolcomitem']);
}

$_idtipoprodserv = $_SESSION['arrpostbuffer']['1']['u']['prodserv']['idtipoprodserv'];
$_idprodserv = $_SESSION['arrpostbuffer']['1']['u']['prodserv']['idprodserv'];
if($_POST['_idtipoprodserv_'] != $_idtipoprodserv && !empty($_idtipoprodserv) && !empty($_idprodserv))
{
    ProdServController::atualizarIdTipoProdservPorIdProdserv($_idprodserv);
    ProdServController::atualizarProdservContaItemPorIdContaItem($_idprodserv);
    ProdServController::atualizarIdContaItemPorIdProdserv($_idprodserv);
} 
?>