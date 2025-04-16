<?
require_once("../inc/php/functions.php");

cbSetPostHeader('1','html');


//Pegar estado do Device
$arrm5[$_REQUEST['iddevice']] = re::dis()->hGetAll('_estado:'.$_REQUEST['iddevice'].':device');

$d1 = new DateTime();
$d2 = new DateTime($arrm5[$_REQUEST['iddevice']]['reiniciadoem']);

$interval = $d1->diff($d2);
$diffInMinutes = $interval->i; //23
if($diffInMinutes >= 1 and $diffInMinutes < 9){
    echo "OK";
}else if($diffInMinutes >= 10){
    echo "tempo excedido";
}else{
    echo "NOK";
}

?>