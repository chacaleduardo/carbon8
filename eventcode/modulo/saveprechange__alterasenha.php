<?
/* MAF190824: Se houverem varios servidores de apliacacao, o servidor de autenticacao deve estar centralizado. Comentado até que um modo de alteracao de senha descentralizado via http seja criado
if(gethostname()!=="sislaudo"){
	//Maf130519: Alterar a senha no servidor da intranet. A partir deste ponto todos os comandos serão executados no servidor remoto
	d::b("db.laudolab.com.br","3306","_usr_","_pwd_","laudo");

	//Confirma se o db foi alterado
	$resh=d::b()->query("select @@hostname as 'hostname'");
	$rh = mysqli_fetch_assoc($resh);

	if($rh["hostname"]!=="sislaudo"){
		die("Servidor de autenticação indisponível.\nTente novamente mais tarde.");
	}
}*/

cbSetPostHeader(0,"erro");

if($_POST["senhanova"]!=$_POST["senhanova2"]){
	die("As senhas informadas são diferentes, ou os nomes dos inputs foram alterados na página.");
}

if(empty($_POST["senhanova"])){
	die("Senha nova inválida!");
}

if(strlen($_POST["senhanova"]) < 8 
    or !preg_match("/[a-z]+/i", $_POST["senhanova"]) 
    or !preg_match("/[0-9]+/",  $_POST["senhanova"])){
    die("A senha informada deve ter no mínimo 8 caracteres,\ne conter Letras [a-z] e Números [0-9]");
}

//verifica se o usuario informou a senha atual corretamente
$rusr = getUsr($_SESSION["SESSAO"]["USUARIO"]);
$senha=verificasenha($rusr, $_SESSION['arrpostbuffer'][1]['u']['pessoa']['senha']);

if($senha==false){
	die("Senha atual incorreta!");
}else{

	$_SESSION['arrpostbuffer'][1]['u']['pessoa']['idpessoa'] = $_SESSION["SESSAO"]["IDPESSOA"];
	$_SESSION['arrpostbuffer'][1]['u']['pessoa']['senha'] = senha_hash($_POST["senhanova"]);
    $_SESSION['arrpostbuffer'][1]['u']['pessoa']['tipoauth'] = "bsp";

	unset($_SESSION["SESSAO"]["FORCAALTERACAOSENHA"]);

	$_SESSION["SESSAO"]["SENHAALTERADACOMSUCESSO"] = 'Y';
}

?>
