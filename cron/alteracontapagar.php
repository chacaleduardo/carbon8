<?
ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
    include_once("/var/www/carbon8/inc/php/functions.php");
}else{//se estiver seno executao via requisicao http
    include_once("../inc/php/functions.php");
}
/*
		$sql=" update contapagar 
		set status = 'QUITADO'
		where progpagamento = 'S' 
		and datareceb = curdate() 
		and status = 'PENDENTE'";
		
		$qr = d::b()->query($sql) or die("Erro ao alterar status da conta".mysqli_error());
	*/

?>
