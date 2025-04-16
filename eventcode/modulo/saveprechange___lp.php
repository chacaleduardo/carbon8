<?

if($_POST['tipobjeto_inativar_'] && $_POST['idobjeto_inativar_'] && $_POST['status_inativar_']){
    if($_POST['tipobjeto_inativar_'] == "grupo"){
        $reslp = _LpController::buscarGruposPorLpgrupopar($_POST['idobjeto_inativar_']);
        $i=0;
        $i2=0;
        foreach($reslp as $k => $r){
            $_SESSION['arrpostbuffer']["upd".$i]['u']['_lpgrupo']['idlpgrupo'] =  $r['idlpgrupo'];
            $_SESSION['arrpostbuffer']["upd".$i]['u']['_lpgrupo']['status']    =  $_POST['status_inativar_'];

            $rs = _LpController::buscarLPsPorIdLprupo($r["idlpgrupo"],$_SESSION['SESSAO']['IDPESSOA']);
            
            foreach($rs as $k1 => $r2){
                $_SESSION['arrpostbuffer']["updl1".$i2]['u']['_lp']['idlp']   =  $r2['idlp'];
                $_SESSION['arrpostbuffer']["updl1".$i2]['u']['_lp']['status'] =  $_POST['status_inativar_'];
                $i2++;
            }
            $i++;
        }
    }
    if($_POST['tipobjeto_inativar_'] == "painel"){
        $rs = _LpController::buscarLPsPorIdLprupo($_POST['idobjeto_inativar_'],$_SESSION['SESSAO']['IDPESSOA']);
        $i2=0;
        
        $_SESSION['arrpostbuffer']["upd".$i2]['u']['_lpgrupo']['idlpgrupo'] =  $_POST['idobjeto_inativar_'];
        $_SESSION['arrpostbuffer']["upd".$i2]['u']['_lpgrupo']['status']    =  $_POST['status_inativar_'];

        foreach($rs as $k2 => $r2){
            $_SESSION['arrpostbuffer']["updl2".$i2]['u']['_lp']['idlp']   =  $r2['idlp'];
            $_SESSION['arrpostbuffer']["updl2".$i2]['u']['_lp']['status'] =  $_POST['status_inativar_'];
            $i2++;
        }
    }
}
if($_POST["_x_i__lpobjeto_tipoobjeto"]){

    $res1 = _LpController::buscarIdlpobjetoPorIdobjetoTipoobjetoIdlp($_POST["_x_i__lpobjeto_idlp"],$_POST["_x_i__lpobjeto_idobjeto"],$_POST["_x_i__lpobjeto_tipoobjeto"]);
    $qtd=count($res1);
		if($qtd>0){
            $i = 200;
            foreach ($res1 as $k => $row ){

              
                $_SESSION['arrpostbuffer'][$i]['d']['_lpobjeto']['idlpobjeto'] =  $row['idlpobjeto'];
                $i++;
            }
            unset($_SESSION['arrpostbuffer']['x']['i']['_lpobjeto']['tipoobjeto']);
            unset($_SESSION['arrpostbuffer']['x']['i']['_lpobjeto']['idobjeto']);
            unset($_SESSION['arrpostbuffer']['x']['i']['_lpobjeto']['idlp']);
           

		} 
}


if($_POST["_x_i_objetovinculo_idobjeto"]){
    $res2 = _LpController::buscarObjetovinculoEObjetosVinculados($_POST["_x_i_objetovinculo_idobjeto"],$_POST["_x_i_objetovinculo_tipoobjeto"],$_POST["_x_i_objetovinculo_idobjetovinc"],$_POST["_x_i_objetovinculo_tipoobjetovinc"]);
    $qtd2=count($res2);
		if($qtd2>0){
            $i = 200;
            foreach ($res2 as $k => $row2){

              
                $_SESSION['arrpostbuffer'][$i]['d']['objetovinculo']['idobjetovinculo'] =  $row2['idobjetovinculo'];
                $i++;
            }
            unset($_SESSION['arrpostbuffer']['x']['i']['idobjetovinculo']['tipoobjeto']);
            unset($_SESSION['arrpostbuffer']['x']['i']['idobjetovinculo']['idobjeto']);
            unset($_SESSION['arrpostbuffer']['x']['i']['idobjetovinculo']['tipoobjetovinc']);
            unset($_SESSION['arrpostbuffer']['x']['i']['idobjetovinculo']['idobjetovinc']);
           

		} 
}


?>