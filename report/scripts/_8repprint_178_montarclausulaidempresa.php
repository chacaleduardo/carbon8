<?
if($_iclausulas > 0){
    $_sqlresultado = getDbTabela($_tab).".". $_tab ." ".$_sqlwhere;

    if(in_array('idempresa', array_keys($_GET)))
    {
        $wIdempresa = false;
    } else {
        if(empty($_GET['idempresa']) && cb::habilitarMatriz() == 'N')
        {
            $wIdempresa = ($idEmpresas == 0) ? cb::idempresa() : $idEmpresas;
    
            $_sqlresultado .=  $_and." idempresa in (".$wIdempresa.")";
            $_and = " and ";
        } elseif(empty($_GET['idempresa']) && cb::habilitarMatriz() != 'N')
        {
            $idEmpresas = EmpresaController::buscarIdEmpresasVinculadasPorIdObjetoEObjeto($_SESSION["SESSAO"]["IDPESSOA"], 'pessoa');
    
            $wIdempresa = ($idEmpresas == 0) ? cb::idempresa() : $idEmpresas;
    
            $_sqlresultado .= $_and." idempresa in (".$wIdempresa.")";
            $_and = " and ";
        }
    }
}else{
    $_sqlresultado = getDbTabela($_tab).".". $_tab ." ".$_sqlwhere;
    if(empty($_GET['idempresa']) && cb::habilitarMatriz() == 'N')
    {
        $wIdempresa = ($idEmpresas == 0) ? cb::idempresa() : $idEmpresas;

        $_sqlresultado .= $_and." idempresa in (".$wIdempresa.")";
        $_and = " and ";
    }elseif(empty($_GET['idempresa']) && cb::habilitarMatriz() != 'N'){
        $idEmpresas = EmpresaController::buscarIdEmpresasVinculadasPorIdObjetoEObjeto($_SESSION["SESSAO"]["IDPESSOA"], 'pessoa');

        $wIdempresa = ($idEmpresas == 0) ? cb::idempresa() : $idEmpresas;

        $_sqlresultado .=  $_and." idempresa in (".$wIdempresa.")";
        $_and = " and ";
    }
}
?>