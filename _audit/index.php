<?
require_once("../inc/php/validaacesso.php");

//ini_set("display_errors", 1);
//error_reporting(E_ALL);

session_cache_expire(1);
session_cache_limiter("private");

//Recupera Parametros gerais da Pagina
if(!$_SESSION["SESSAO"]["LOGADO"]){
	//Não logado: o silêncio é uma dádiva.
}else{

	//Recupera os modulos que o usuário tem acesso
	$mods = getModsUsr("MODULOS");

	print_r($mods);
/*
	//Transforma o array em json
	$json_filtros = json_encode($ARRMOD,JSON_PRETTY_PRINT);

	if(json_last_error()){
	    
	    echo("_modulofiltros: Erro ao montar Json: Código [".json_last_error()."] Erro: ".json_last_error_msg());
		if($_SESSION["SESSAO"]["USUARIO"]=="marcelo" OR $_SESSION["SESSAO"]["USUARIO"]=="hermesp"){
			echo "teste";
			print_r($ARRMOD);
		}
	    die;
	}else{
	    echo $json_filtros;
	}

	//print_r($ARRMOD);
*/
}


?>
