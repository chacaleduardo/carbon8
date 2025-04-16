<?
$idpessoaemail = $_SESSION['arrpostbuffer']['1']['i']['emailvirtualconf']['idpessoaemail'];


if(!empty($_SESSION['arrpostbuffer']['1']['i']['emailvirtualconf']['idpessoaemail'])){
    $idpessoaemail = $_SESSION['arrpostbuffer']['1']['i']['emailvirtualconf']['idpessoaemail'];
    $z='i';
}elseif($_SESSION['arrpostbuffer']['1']['u']['emailvirtualconf']['idpessoaemail']){
    $idpessoaemail = $_SESSION['arrpostbuffer']['1']['u']['emailvirtualconf']['idpessoaemail'];
    $z='u';
}


if(!empty($idpessoaemail)){
    $webmailemail =traduzid('pessoa','idpessoa','webmailemail',$idpessoaemail);
    $_SESSION['arrpostbuffer']['1'][$z]['emailvirtualconf']['email_original']=$webmailemail;
}
?>