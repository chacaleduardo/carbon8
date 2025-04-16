<?
require_once("../inc/php/functions.php");
cbSetPostHeader("0","alert");

if((!empty($_GET["idnucleo"]) or $_GET["idnucleo"]===0) or !empty($_GET["idpessoa"])){
	

	if((!empty($_GET["idnucleo"]) or $_GET["idnucleo"]===0)){
		$clausnucleo = " and idnucleo = ".$_GET["idnucleo"];
	} 
	if(!empty($_GET["idpessoa"])){
		$clauscliente = " and idcliente = ".$_GET["idpessoa"]; 
	}
	
	if(!$_SESSION["SESSAO"]["SUPERUSUARIO"]){
		//maf: o IDPESSOA que vem via get, vem a partir do campo IDCLIENTE que esta na tabela dashboard.
		$sqldecr = "delete from dashboardnucleopessoa
		where idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"]." 
			".$clauscliente."
			".$clausnucleo."
			and alerta <> 1";

		//echo $sqldecr;die;
		$resupd = mysql_query($sqldecr);
		
		if($resupd){
			cbSetPostHeader("1","alert");
			die("Notificações alteradas com sucesso");
		}else{
			cbSetPostHeader("0","alert");
		 	die("Erro ao resetar dashboard:\n<Br>Sql: ".$sqldecr);
		}
	}else{
		die("Super usuário configurado para não realizar alterações no Dashboard.");
	}
	
}else{

	die("Id do Núcleo ou Id do Cliente não enviado!");
}

?>
