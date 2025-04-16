<?
$idloteformulains = $_SESSION['arrpostbuffer']['1']['u']['loteformulains']['idloteformulains'] ;
$idprodserv = $_SESSION['arrpostbuffer']['1']['u']['loteformulains']['idprodserv'] ;


if(!empty($idloteformulains) and !empty($idprodserv)){

$s="select idprodserv,codprodserv,descr,ifnull(descrcurta,descr) as descrcurta,descrgenerica  
             from prodserv where idprodserv=".$idprodserv;
$res = d::b()->query($s) or die("A Consulta do produto falhou :".mysql_error()."<br>Sql:".$s); 
$qtd=mysqli_num_rows($res);
if($qtd<1){
    die('configurações do produto não encontrada');
}
$row=mysqli_fetch_assoc($res);

$_SESSION['arrpostbuffer']['1']['u']['loteformulains']['codprodserv']=$row['codprodserv'];
$_SESSION['arrpostbuffer']['1']['u']['loteformulains']['descr']=$row['descr'];
$_SESSION['arrpostbuffer']['1']['u']['loteformulains']['descrcurta']=$row['descrcurta'];
$_SESSION['arrpostbuffer']['1']['u']['loteformulains']['descrgenerica']=$row['descrgenerica'];
}
?>