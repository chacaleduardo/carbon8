<?
/*
 * Status Visualização
 */
if(!empty($_GET["statusvisualizacao"])){ 
	
	//Resultados Visualizados/Não
	if($_GET["statusvisualizacao"]=="N"){
		$_SESSION["SEARCH"]["WHERE"][]="nvisualizado=1";
		
	}elseif($_GET["statusvisualizacao"]=="Y"){
		$_SESSION["SEARCH"]["WHERE"][]="(nvisualizado is null or nvisualizado=0)";

	}elseif($_GET["statusvisualizacao"]=="A"){
		$_SESSION["SEARCH"]["WHERE"][]="status='ABERTO'";
	}

	//Neste ponto, o parâmetro GET já faz parte da cláusula where. Retirar para não ser processado.
	unset($_SESSION["SEARCH"]["WHERE"]["statusvisualizacao"]);
}

/*
 * Clientes do Contato
 */
if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==3){//usuariocliente
	
	$_SESSION["SEARCH"]["WHERE"][] = " idpessoa in (".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].")";

}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==16 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==9){
    $_SESSION["SEARCH"]["WHERE"][] = " idpessoa in (".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].") and idunidade=6 ";
}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==4){

	$_SESSION["SEARCH"]["WHERE"][] = " idsecretaria in(".$_SESSION["SESSAO"]["STRCONTATOSECRETARIA"].")";
	$_SESSION["SEARCH"]["WHERE"][] = " idpessoa in(".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].")";

}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==1 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==8){
	null;

}else{
	die("presearchexec__cliente_visualizarresultados.php[l:".__LINE__."]: idtipopessoa não previsto.");
}

?>
