<?
require_once("../inc/php/validaacesso.php");

$user_name=$_GET['user_name'];

if(!empty($user_name)){
	$sql = "SELECT count(*) as quant
			FROM pessoa
			WHERE usuario = '" . trim($user_name) ."'";
	

	$res =  d::b()->query($sql) or die("Falha ao pesquisar exitencia de usuario : " . mysql_error() . "<p>SQL: $sql");

	$row = mysqli_fetch_assoc($res);
	
	if($row["quant"]>=1){
		die("Usuário já existente");
	}elseif($row["quant"]==0){
		die("OK");
	}else{
		die("ERRO");
	}
}else{
	die("Erro: Parametro user_name nao enviado corretamente!");
}

?>