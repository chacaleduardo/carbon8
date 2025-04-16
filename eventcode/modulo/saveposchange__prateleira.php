<?
/*
 * gerar os campos da prateleira
 * 
 */

$iu = $_SESSION['arrpostbuffer']['1']['u']['prateleira']['idempresa'] ? 'u' : 'i';

//se for insert
if($iu == "i"){
	
	$vlinha = $_SESSION['arrpostbuffer']['1']['i']['prateleira']['linha'];
	$vcoluna = $_SESSION['arrpostbuffer']['1']['i']['prateleira']['coluna'];
	$idprateleira =$_SESSION["_pkid"];
	//$idprateleira = $_SESSION['arrpostbuffer']['1']['i']['prateleira']['idprateleira'];
	
	d::b()->query("START TRANSACTION") or die("geraprateleira: Falha 1 ao abrir transacao: ".mysqli_error());
	
	$coluna=1;
	
		for($coluna==1; $coluna <=$vcoluna ; $coluna++){
			$linha=1; 
			for($linha==1; $linha <=$vlinha; $linha++){
				
				$sqlprateleira = " INSERT INTO `prateleiradim`(idempresa,idprateleira,coluna,linha) 
				values(".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idprateleira.",".$coluna.",".$linha.")";
	  			$resprateleira = d::b()->query($sqlprateleira);
	
	  			if(!$resprateleira){
					d::b()->query("ROLLBACK;");
					die("1-Falha ao gerar dimensoes da prateleira: " . mysqli_error() . "<p>SQL: ".$sqlprateleira);
				}else{
					d::b()->query("COMMIT") or die("Erro");	
				}				
			}			
		}	
}
/*
elseif($iu == "u"){
	
	$vlinha = $_SESSION['arrpostbuffer']['1']['u']['prateleira']['linha'];
	$vcoluna = $_SESSION['arrpostbuffer']['1']['u']['prateleira']['coluna'];
	$idprateleira = $_SESSION['arrpostbuffer']['1']['u']['prateleira']['idprateleira'];
	
	
	d::b()->query("START TRANSACTION") or die("geraprateleira: Falha 1.1 ao abrir transacao: ".mysqli_error());
	
	$coluna=1;
	
		$sql = "delete from prateleiradim
			where idprateleira = " .$idprateleira; 
		$res = d::b()->query($sql) or die("ERRO ao excluir dimensÃÂµes anterioes: ".mysqli_error()."
 SQL: ".$sql);

	
		for($coluna==1; $coluna <=$vcoluna ; $coluna++){
			$linha=1; 
			for($linha==1; $linha <=$vlinha; $linha++){
				
				$sqlprateleira = " INSERT INTO `prateleiradim`(idempresa,idprateleira,coluna,linha) 
				values(".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idprateleira.",".$coluna.",".$linha.")";
	  			$resprateleira = d::b()->query($sqlprateleira);
	
	  			if(!$resprateleira){
					d::b()->query("ROLLBACK;");
					die("2-Falha ao gerar dimensÃÂµes da prateleira: " . mysqli_error() . "<p>SQL: ".$sqlprateleira);
				}				
			}			
		}
	
}
?>
