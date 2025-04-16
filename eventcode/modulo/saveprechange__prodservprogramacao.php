<?

$idprodservformula = $_SESSION['arrpostbuffer']['x']['i']['prodservforn']['idprodservformula'];
if(!empty($idprodservformula)){
    $idprodserv= traduzid('prodservformula', 'idprodservformula', 'idprodserv', $idprodservformula);
    $_SESSION['arrpostbuffer']['x']['i']['prodservforn']['idprodserv']=$idprodserv;
}
