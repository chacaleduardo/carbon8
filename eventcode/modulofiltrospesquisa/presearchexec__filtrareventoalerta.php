<?


    //$_SESSION["SEARCH"]["WHERE"][] = " idobjeto = '".$_SESSION["SESSAO"]["IDTIPOPESSOA"]."' ";


	$_SESSION["SEARCH"]["WHERE"][]=" idobjeto = '".$_SESSION["SESSAO"]["IDPESSOA"]."' ";
    $_SESSION["SEARCH"]["WHERE"][]=" tipoobjeto = 'pessoa' ";

    if(isset($_GET["_oculto_"])){
        $_SESSION["SEARCH"]["WHERE"][]=" oculto = '".$_GET["_oculto_"]."' ";
    }