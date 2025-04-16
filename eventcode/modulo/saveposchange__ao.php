<?
$idprodservformula=$_SESSION['arrpostbuffer']['x']['i']['lote']['idprodservformula'];
$partida=$_SESSION['arrpostbuffer']['x']['i']['lote']['partida'];
$exercicio=$_SESSION['arrpostbuffer']['x']['i']['lote']['exercicio'];
$idlote=$_SESSION["_pkid"];
if( !empty($idprodservformula)  and !empty($idlote)){
    $ret=geraatividadelote($idlote,$idprodservformula);
    if($ret!="OK"){
        die($ret);
    }

    geraAmostrasRelacionadasAoLoteao($idlote,$partida,$exercicio);
}

if($_SESSION["SESSAO"]["USUARIO"]=="marcelo"){
	print_r($_SESSION['arrpostbuffer']);
	die("Die.");
}
?>
