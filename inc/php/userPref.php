<?
require_once("functions.php");

if(	(empty($_POST["_acao"]) or empty($_POST["_path"])) 
	or ($_POST["_acao"]=="u" and empty($_POST["_valor"]))
	){
	die("Parâmetros não enviados corretamente");
}else{

	if(logado()){
		if($_POST["_acao"]=="s"){
			echo userPref('s', $_POST["_path"]);
		}else{
			echo userPref($_POST["_acao"], $_POST["_path"], $_POST["_valor"]);
		}
	}
}
?>
