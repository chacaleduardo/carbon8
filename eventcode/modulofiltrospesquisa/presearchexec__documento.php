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

$sqllp="SELECT count(*) as editadoc FROM carbonnovo._lpmodulo
            where modulo='documento'  
            and permissao='w' and idlp in (".getModsUsr("LPS").")";
$reslp= mysql_query($sqllp);
$rowp= mysql_fetch_assoc($reslp);
 
if($rowp['editadoc']==0){
     $_SESSION["SEARCH"]["WHERE"][]="( 
		exists (select 1 from  carrimbo car where car.tipoobjeto = 'documento' and car.idobjeto= a.idsgdoc and car.idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"].")
		or ( a.idpessoaresp=".$_SESSION["SESSAO"]["IDPESSOA"].")
		or 
		exists (select 1 from vinculos v,pessoaobjeto t          
				where v.idobjetode=a.idsgdoc 
				and v.tipoobjetode='sgdoc'
				and v.idobjetopara=t.idobjeto
				and t.idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"]."
				and v.tipoobjetopara='sgsetor'
				and t.tipoobjeto = 'sgsetor'
				)
		)";
}