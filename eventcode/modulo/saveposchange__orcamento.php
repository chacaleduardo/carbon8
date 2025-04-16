<?
//i ou u
$iu = $_SESSION['arrpostbuffer']['1']['u']['orcamento']['idorcamento'] ? 'u' : 'i';

if($iu == "i"){
	
	///gerar o numero de controle do orçamento
	$idor = $_SESSION["_pkid"];
	
	if(!empty($idor)){
	$ncontrole =str_pad($idor,5, "0", STR_PAD_LEFT);
	
		$sql = "update orcamento 
					set controle = '".$ncontrole."' 
					where idorcamento = ".$idor;
			d::b()->query($sql) or die("Erro ao inserir numero de controle \nSQL:".$sql."\nErro:".mysql_error());
	}			
	
}

?>