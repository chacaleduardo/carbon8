<?
require_once("../inc/php/functions.php");

$idcontroleimpressao= $_GET["idcontroleimpressao"];



	
	if(empty($idcontroleimpressao)){
		
		die("ID do controle de impressão não informado");
	}else{

			$sql1 = "update  controleimpressaoitem 
			set status = 'INATIVO' 
			where idcontroleimpressao=".$idcontroleimpressao."
			 and status = 'ATIVO'";
			
			//echo($sql1);
			
			$res1 = d::b()->query($sql1) or die("Erro ao alterar o status da impressões do controle item".$sql1);
			
			$sql2 = "update  controleimpressao
			set status = 'INATIVO' 
			where idcontroleimpressao=".$idcontroleimpressao."
			 and status = 'ATIVO'";
			
			//echo($sql1);
			
			$res2 = d::b()->query($sql2) or die("Erro ao alterar o status da impressões do controle".$sql1);
			
			echo "OK";
	}
?>