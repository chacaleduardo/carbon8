<?
if (defined('STDIN')){
	require_once("/var/www/carbon8/inc/php/validaacesso.php");
}else{
	require_once("../inc/php/validaacesso.php");
}

if(!empty($_GET["idnucleo"])){
	$_sql="INSERT INTO log(tipoobjeto,log) VALUES ('teste_req','OK')";
	$_res = d::b()->query($_sql) or die($_sql);
}else{
	$_sql="INSERT INTO log(tipoobjeto,log) VALUES ('teste_req','NOT OK')";
	$_res = d::b()->query($_sql) or die($_sql);
}
?>