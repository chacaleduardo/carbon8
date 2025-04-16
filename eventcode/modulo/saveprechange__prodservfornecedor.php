<?
$converteest = $_SESSION['arrpostbuffer']['x']['u']['prodservforn']['converteest'] ;



if($converteest=='N'){
    $_SESSION['arrpostbuffer']['x']['u']['prodservforn']['unforn']='';
    $_SESSION['arrpostbuffer']['x']['u']['prodservforn']['valconv']='';
}

$idprodservori = $_SESSION['arrpostbuffer']['x']['u']['prodservforn']['idprodservori'] ;


if(!empty($idprodservori)){
    $codforn =  traduzid('prodserv', 'idprodserv', 'descr', $idprodservori);
    $_SESSION['arrpostbuffer']['x']['u']['prodservforn']['codforn']= $codforn;

}
//print_r($_SESSION['arrpostbuffer']); 
// die;

?>
