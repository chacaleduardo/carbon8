<?
if (cb::habilitarMatriz() == "Y") {
    //Cada unidade deve ter seu módulo correspondente configurado em todas as empresas
    $idunidade = getUnidadePadraoModuloMultiempresa($_GET["_modulo"],null,true);

    if(!empty($idunidade)){

    //Filtra pela Unidade correspondente ao módulo
    $_SESSION["SEARCH"]["WHERE"][] = " idunidade in (".$idunidade.")";
    }
}else {
    //Cada unidade deve ter seu módulo correspondente configurado
    $idunidade = getUnidadePadraoModulo($_GET["_modulo"]);

    if(!empty($idunidade)){

    //Filtra pela Unidade correspondente ao módulo
    $_SESSION["SEARCH"]["WHERE"][] = " idunidade = ".$idunidade;

    }
}


//die($_GET['_modulo']);
if($_GET['_modulo']=='produdosvenda'){
    //filtra somente transporte
    $_SESSION["SEARCH"]["WHERE"][]=" exists(select 1 from prodserv p where p.idprodserv=a.idprodserv and p.venda='Y' ) ";
    
    
}//if($_GET['_modulo']=='nfcte'){
	
	
if($_GET['_modulo']=='semente'){
    //filtra somente semente
    $_SESSION["SEARCH"]["WHERE"][]=" exists(select 1 from prodserv p join unidadeobjeto u on(
                                                u.idobjeto = p.idprodserv and u.tipoobjeto = 'prodserv') 
                                                join unidade un on(un.idunidade=u.idunidade and un.idtipounidade = 2)
                                            join tipoprodserv  t on(t.idtipoprodserv=p.idtipoprodserv and t.idtipoprodserv in (1171, 3))
                                            where p.idprodserv=a.idprodserv and p.tipo = 'PRODUTO' and p.status = 'ATIVO' and p.especial='Y') ";
    
    
}//if($_GET['_modulo']=='nfcte'){

if(!empty($_SESSION["SESSAO"]["INIDPLANTEL"])){
     $_SESSION["SEARCH"]["WHERE"]["idpessoa"] = " idplantel in( ".$_SESSION["SESSAO"]["INIDPLANTEL"].") ";
}
//print_r($_SESSION["SEARCH"]["WHERE"]);
//die();

?>