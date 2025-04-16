<?
/*
 * SE for representante so listar
 * parcelas do mesmo
 */
if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15){
 /*   $_SESSION["SEARCH"]["WHERE"][]=" idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"];*/
    
}
$_SESSION["SEARCH"]["WHERE"][]=" idtipopessoa in (12,1) ";

?>
