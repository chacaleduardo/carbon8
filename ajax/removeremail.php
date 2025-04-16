<?
require_once("../inc/php/functions.php");

if($_GET['remover'] == 'Y'){
	if(!empty($_GET['idmailfila']) and $_GET['idmailfila'] != 0){
		$idmailfila= $_GET['idmailfila'];
		$_sql= "UPDATE mailfila set remover='Y' where idmailfila=".$idmailfila;
		$_res = d::b()->query($_sql) or die($_sql." Erro ao atualizar campo de email removido: ".mysqli_error());
	}else{
		echo "Parâmetros GET inválidos";
	}
}         
?>
