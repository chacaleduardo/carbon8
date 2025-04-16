<?
$arrpostbuffer = $_SESSION['arrpostbuffer'];
$exercicio = $_POST["exercicio"];
$idini = $_POST["idini"];
$idfim = $_POST["idfim"];
$idtipoteste = $_POST["idtipoteste"];

if(empty($_SESSION['arrpostbuffer']['x']['u']['lote']['idlote'])){// se não for esgotar o lote

    if(empty($exercicio)){
        die("Favor informar o exercicio.");
    }elseif(empty($idtipoteste)){
        die("Favor informar o tipo do teste.");
    }elseif(empty($idini)){
        die("Favor informar o inicio do registro desejado.");
    }elseif(empty($idfim)){
        die("Favor informar o fim do registro desejado.");
    }

    //print_r($_POST); die();
    // print_r($arrpostbuffer); die();
    $arrlotes=array();//array para guardar os lotes
    $i=0;
    foreach ($arrpostbuffer as $key => $value) {
        if($_SESSION['arrpostbuffer'][$key]['i']['lotecons']['qtdd']>0){
            $idprodserv=$_SESSION['arrpostbuffer'][$key]['i']['lotecons']['idprodserv'];
            $arrlotes[$idprodserv][$key]['qtdd']=$_SESSION['arrpostbuffer'][$key]['i']['lotecons']['qtdd'];
            $arrlotes[$idprodserv][$key]['qtdteste']=$_SESSION['arrpostbuffer'][$key]['i']['lotecons']['qtdteste'];
            $arrlotes[$idprodserv][$key]['idlote']= $_SESSION['arrpostbuffer'][$key]['i']['lotecons']['idlote'];
            $arrlotes[$idprodserv][$key]['idlotefracao']= $_SESSION['arrpostbuffer'][$key]['i']['lotecons']['idlotefracao'];
            $arrlotes[$idprodserv][$key]['ordem']= $_SESSION['arrpostbuffer'][$key]['i']['lotecons']['ordem'];
            $i=$i+1;
        }
    }
    unset($_SESSION['arrpostbuffer']);
    $l=1;
    foreach ($arrlotes as $list => $arrl) { 
        foreach ($arrl as $key1 => $value) { 
            //echo($value['idlote']."=".$value['qtdd']."<br>"); 
            $qtdun=$value['qtdd']/$value['qtdteste'];
            $res = ResultLoteController::buscarLotesParaConsumo($qtdun,$idtipoteste,$exercicio,$value['ordem'],$idini,$idfim,$value['idlote'],getidempresa('a.idempresa','amostraaves'));
            foreach($res as $k => $row){
                $_SESSION['arrpostbuffer'][$l]['i']['lotecons']['qtdd']=$row['qtdimput'];
                $_SESSION['arrpostbuffer'][$l]['i']['lotecons']['idlote']=$row['idlote'];
                $_SESSION['arrpostbuffer'][$l]['i']['lotecons']['idlotefracao']=$value['idlotefracao'];
                $_SESSION['arrpostbuffer'][$l]['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"];
                $_SESSION['arrpostbuffer'][$l]['i']['lotecons']['tipoobjeto']='resultado';
                $_SESSION['arrpostbuffer'][$l]['i']['lotecons']['idobjeto']=$row['idresultado'];
                $_SESSION['arrpostbuffer'][$l]['i']['lotecons']['criadoem']=date("d/m/Y H:i:s");
                $_SESSION['arrpostbuffer'][$l]['i']['lotecons']['criadopor']=$_SESSION["SESSAO"]["USUARIO"];
                $_SESSION['arrpostbuffer'][$l]['i']['lotecons']['alteradoem']=date("d/m/Y H:i:s");
                $_SESSION['arrpostbuffer'][$l]['i']['lotecons']['alteradopor']=$_SESSION["SESSAO"]["USUARIO"];

                $l=$l+1;
            }
        }

    }
}//if(empty($_SESSION['arrpostbuffer']['x']['u']['lote']['idlote']))
foreach ($arrpostbuffer as $key => $value) {
    if($arrpostbuffer[$key]['i']['objetovinculo']['idobjetovinc']){
        $idtag = $arrpostbuffer[$key]['i']['objetovinculo']['idobjetovinc'];
        $res = ResultLoteController::buscarResultadosParaVinculoTag($idtipoteste,$exercicio,$idini,$idfim,getidempresa('a.idempresa','amostraaves'),$idtag);
        foreach($res as $k => $row){
            $_SESSION['arrpostbuffer'][$key]['i']['objetovinculo']['idobjetovinc']=$idtag;
            $_SESSION['arrpostbuffer'][$key]['i']['objetovinculo']['tipoobjetovinc']='tag';
            $_SESSION['arrpostbuffer'][$key]['i']['objetovinculo']['idobjeto']=$row['idresultado'];
            $_SESSION['arrpostbuffer'][$key]['i']['objetovinculo']['tipoobjeto']='resultado';
        }
    }
}
    
//print_r($_SESSION['arrpostbuffer']);
//die();