<?

$idusuario= $_SESSION["SESSAO"]["IDPESSOA"];//$perfilpag_idpessoa;

$sql="select * from "._DBCARBON."._lpmodulo where modulo ='aprovaponto' and idlp in(".getModsUsr("LPS").")";
$res=d::b()->query($sql) or die("erro ao buscar supervisor sql=".$sql);
$qtdsup= mysqli_num_rows($res);

if($qtdsup<1){
  
    $_SESSION["SEARCH"]["WHERE"]["idpessoa"]=" idpessoa = '".$idusuario."'";
    
}

//print_r( $SESSION["SEARCH"]["WHERE"]); die;