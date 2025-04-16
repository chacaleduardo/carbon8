<?

/*
 * Clientes do Contato
 */
if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==3){//usuariocliente
	
	$_SESSION["SEARCH"]["WHERE"][] = "  idunidade=9  -- GROUP BY idamostra";

}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==16  or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==9){
    $_SESSION["SEARCH"]["WHERE"][] = "  idunidade=9  -- GROUP BY idamostra ";
}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==4){

	$_SESSION["SEARCH"]["WHERE"][] = " idsecretaria in(".$_SESSION["SESSAO"]["STRCONTATOSECRETARIA"].") and idunidade=6  GROUP BY idamostra";
	//$_SESSION["SEARCH"]["WHERE"]["idpessoa"] = " idpessoa in(".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].") ";
        $_SESSION["SEARCH"]["WHERE"]["flgoficial"] = " flgoficial ='Y'";

}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==1 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==8){
	null;

}else{
	die("presearchexec__cliente_visualizarresultados.php[l:".__LINE__."]: idtipopessoa não previsto.");
}