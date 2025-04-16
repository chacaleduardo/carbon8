<?

/*
 * Clientes do Contato
 */
if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==3){//usuariocliente
	
	// $_SESSION["SEARCH"]["WHERE"]["flgoficial"] = " flgoficial ='N'";
	//$_SESSION["SEARCH"]["WHERE"]["flgoficial"] = " (case when flgoficial = 'Y' and alerta = 'Y' and (idespeciefinalidade not in (49,55)) then false else true end) AND statusamostra NOT IN ('PROVISORIO')";

	$_SESSION["SEARCH"]["WHERE"]["flgoficial"] = " statusamostra NOT IN ('PROVISORIO')  
		and not exists( select 1 from vwtipificacaosalm tp
		where tp.idamostra =a.idamostra
		and tp.idsecretaria is not null and   tp.idsecretaria !=''
		and tp.idprodserv = 640 
		and tp.tipoespecie in('Avós','Bisavós','Matrizes','Controlado','SPF')
		and tp.resultado not like('%POSITIVO PARA SALMONELLA SPP%'))";
	
}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==16 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==9){
    $_SESSION["SEARCH"]["WHERE"][] = "  (idunidade=6 or idunidade=9) ";
}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==4){

	$_SESSION["SEARCH"]["WHERE"][] = " idsecretaria in(".$_SESSION["SESSAO"]["STRCONTATOSECRETARIA"].")";
	//$_SESSION["SEARCH"]["WHERE"]["idpessoa"] = " idpessoa in(".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].")";
        $_SESSION["SEARCH"]["WHERE"]["flgoficial"] = " flgoficial ='Y'";

}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==1 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==8){
	null;

}else{
	die("presearchexec__cliente_visualizarresultados.php[l:".__LINE__."]: idtipopessoa não previsto.");
}

