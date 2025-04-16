<?

if (!empty($_POST["_lp_idempresa_"])
    and !empty($_POST['_new_i__lpgrupo_lpgrupopar']) 
    and !empty($_POST['_new_i__lpgrupo_descricao']) 
    and !empty($_SESSION["arrscriptsql"]["new"]["_lpgrupo"]["insertid"])  
) {

    $lastInsertid = _LpController::CriarLp($_POST["_lp_idempresa_"],'N','N',$_POST['_new_i__lpgrupo_descricao']);

    _LpController::vincularLp($lastInsertid,$_SESSION["arrscriptsql"]["new"]["_lpgrupo"]["insertid"],$_SESSION['SESSAO']['USUARIO']);
}

if (!empty($_POST["_lpgrupo_idlpgrupo_"])
    and !empty($_POST['_new_i__lp_idempresa']) 
    and !empty($_POST['_new_i__lp_descricao']) 
    and !empty($_SESSION["arrscriptsql"]["new"]["_lp"]["insertid"])  
) {
    
    _LpController::vincularLp($_SESSION["arrscriptsql"]["new"]["_lp"]["insertid"],$_POST["_lpgrupo_idlpgrupo_"],$_SESSION['SESSAO']['USUARIO']);
}


if (!empty($_POST['copia_lp_empresa']) and !empty($_POST['copia_lp_descr'])
and !empty($_POST['lp_copiar_id']) and !empty($_POST['copia_lp_rot']) and $_POST['acao'] == 'copiaLP') {
    
    $sigla=traduzid('empresa','idempresa','sigla',$_POST['copia_lp_empresa'],false);
    $inslp = new Insert();
    $inslp->setTable("carbonnovo._lp");
    $inslp->idempresa=$_POST['copia_lp_empresa'];
    $inslp->descricao=$_POST['copia_lp_descr']." ".$sigla." - ".$_POST['copia_lp_rot'];
    $idnewlp=$inslp->save();

    $s1="SELECT * from lpobjeto where idlp=".$_POST['lp_copiar_id']." and tipoobjeto!='lpgrupo'";
    $rs1 = d::b()->query($s1) or die("Erro ao buscar lpobjeto: ". mysqli_error(d::b()));

    while($row1 = mysqli_fetch_assoc($rs1)){
        $inslpobj = new Insert();
        $inslpobj->setTable("carbonnovo._lpobjeto");
        $inslpobj->idobjeto=$row1['idobjeto'];
        $inslpobj->tipoobjeto=$row1['tipoobjeto'];
        $inslpobj->idlp=$idnewlp;
        $idnewlpobj=$inslpobj->save();
    }
   

    $inslpobjvinc = new Insert();
    $inslpobjvinc->setTable("carbonnovo._lpobjeto");
    $inslpobjvinc->idobjeto=$_POST['idlpgrupo'];
    $inslpobjvinc->tipoobjeto="lpgrupo";
    $inslpobjvinc->idlp=$idnewlp;
    $idnewlpobjvinc=$inslpobjvinc->save();


}

if (!empty($_POST['_ajax_i__lpmodulo_permissao'])) {
    _LpController::alterarPermissaoModVinculados( "i", $_POST['_ajax_i__lpmodulo_idlp'], $_POST['_ajax_i__lpmodulo_modulo']);
} else if (!empty($_POST['_ajax_u__lpmodulo_permissao'])) {
    _LpController::alterarPermissaoModVinculados( "u", $_POST['_ajax_u__lpmodulo_idlp'], $_POST['_ajax_u__lpmodulo_modulo']);
} else if(!empty($_POST['_ajax_d__lpmodulo_permissao'])) {
    _LpController::alterarPermissaoModVinculados( "d", $_POST['_ajax_d__lpmodulo_idlp'], $_POST['_ajax_d__lpmodulo_modulo']);
}
?>