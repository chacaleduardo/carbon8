<?
require_once("cmd.php");
ob_start();

//Inicializa resposta para a requisição ajax/jQuery
cbSetPostHeader("0","erro");

//Caso seja feito acesso direto ao arquivo de post, negar requisicao
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
	header("HTTP/1.0 404 Not Found");
	die();
}

//maf191103: SEGURANCA: verifica se o modulo esta marcado para Alteração ou Inserção na LP relacionada ao usuário
if(empty($_GET["_modulo"])){
	die("Erro: Módulo não informado");
}else{
	if($_SESSION["SESSAO"]["FULLACCESS"]!="Y" and (getModsUsr("MODULOS")[$_GET["_modulo"]]["permissao"]!='i' and getModsUsr("MODULOS")[$_GET["_modulo"]]["permissao"]!='w')){
		die("Sem permissão de escrita ao Módulo [".$_GET["_modulo"]."].\nVerificar configurações da LP [".$_SESSION["SESSAO"]["IDLP"]."].\nPermissão atual: [".getModsUsr("MODULOS")[$_GET["_modulo"]]["permissao"]."]");
	}
}

//maf0820: para retrocompatibilidade, reseta qualquer buffer de session anterior, para evitar erro em eventos
unset($_SESSION["arrpostbuffer"]);//Será removido na versão 10 do carbon
unset($_SESSION["insertid"]);//Será removido na versão 10 do carbon
unset($_SESSION["_pkid"]);//Será removido na versão 10 do carbon
session_start();//Será removido na versão 10 do carbon

$__CMD = new cmd();
$res=$__CMD->save($_POST);

if(!$res){
	//algum erro ocorreu
	echo($__CMD->erro);
	die;
}else{
	//Ajusta os cabeçalhos de resposta
	cbSetPostHeader("1","html");

	//maf210820: Exigir envio da acao original, o que vai acionar a expansão de variáveis automaticamente
	if($_GET["_acao"]=="i"){
		$_acao = "u";
		$_pkid = $__CMD->getPkid();
		//$_SESSION["insertid"]=$__CMD->getPkid();//Será removido na versão 10 do carbon
		//$_SESSION["_pkid"]=$__CMD->getPkid();//Será removido na versão 10 do carbon
	}
	
	//Envia Header de resposta para que _acao nao permaneca como 'i'
	if($__CMD->getPkid()!==""){
		header('X-CB-PKFLD: '.$__CMD->getPkfld());
		header('X-CB-PKID: '.$__CMD->getPkid());
	}
	
	//maf071213: armazenar os variaveis de insert em caso de Token, para evitar updates em registros que não foram inseridos na sessão atual
	if($_SESSION["SESSAO"]["TOKEN"]==true){
		//$_SESSION["SESSAO"]["TOKENPKFLD"]=;
		//$_SESSION["SESSAO"]["TOKENPKFLD"]=;
		//unset($_SESSION["headergetretorno"]);
	}
	
	//maf310513: Caso seja enviado o header de controle, interromper o processamento para nao recarregar a pagina
	if($_SERVER["HTTP_X_CB_REFRESH"]=="N"){
		die;
	}

	if($_GET["_refresh"]=="false"){
		header('X-CB-FORMATO: none');
		header('CB-REFRESH: false');
		die;
	}
}
?>