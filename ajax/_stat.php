<?
require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    header("HTTP/1.1 401 Unauthorized");
    die;
}

foreach($_POST as $n=>$v) {
        re::dis()->incr($v);
        echo "\n".$v;
}
?>