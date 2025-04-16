<?
require_once("../inc/php/functions.php");
session_start();
$chave= $_GET["chave"];
//print_r($_SESSION);
$existe="";	
	if(empty($chave)){		
		die("ID da conta não informado");
	}else{		
		//verifica se o idventotipo existe no array SESSION
		$existe = array_search($chave,$_SESSION['tipoacao']);				
		//se existir ele retira e grava as novas configurações no banco				
		if($existe!=""){			
			unset($_SESSION['tipoacao'][$existe]); //apago
			
			foreach ($_SESSION['tipoacao'] as $i => $value) {	
				if(!empty($value)){			
					$strconf .="#".$value;		
				}				
			}		
			$sql="update pessoa set confcalendario='".$strconf."' where idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
			
			$res = d::b()->query($sql) or die($sql."Erro ao gravar a configuração do calendário".mysqli_error(d::b()));
			if(!$res){
				echo "ERRO AO ALTERAR A CONFIGURAÇÃO: ".mysqli_error(d::b());
			}else{
				 d::b()->query("COMMIT");
				 echo "2";
			}
			//se não existir ele inseri no array e grava as novas configurações no banco						
		}else{	
			array_push($_SESSION['tipoacao'],$chave);
			
			foreach ($_SESSION['tipoacao'] as $i => $value) {
				if(!empty($value)){			
					$strconf .="#".$value;	
				}
			}			
			$sql="update pessoa set confcalendario='".$strconf."' where idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
			
			$res = d::b()->query($sql) or die($sql."Erro ao gravar a configuração do calendário".mysqli_error(d::b()));
			if(!$res){
				echo "ERRO AO ALTERAR A CONFIGURAÇÃO: ".mysqli_error(d::b());
			}else{
				 d::b()->query("COMMIT");
				 echo "1";
			}				
		}			
	}
?>