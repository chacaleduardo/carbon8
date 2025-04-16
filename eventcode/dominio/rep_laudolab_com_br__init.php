<?
if(logado() and $_GET["_modulo"]!=='baterponto' and $_GET["_locked"]!=="Y"){
	header('Location: '.$_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].'/?_modulo=baterponto&_locked=Y');
	die;
}
