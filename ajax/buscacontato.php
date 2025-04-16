<?
require_once("../inc/php/functions.php");

$idpessoa= $_GET['idpessoa']; 

if(empty($idpessoa)){
	die("CLIENTE OU FORNECEDOR NAO ENVIADO");
}

    $sql= "select 
			c.idcontato
			,nome			
			from pessoa p
			,pessoacontato c
			where p.status='ATIVO'
			and  p.idpessoa = c.idcontato
			and p.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
			and c.idpessoa = ".$idpessoa." order by nome";
	$res = d::b()->query($sql) or die("Erro ao buscar endereco: ".mysqli_error());
	
		
	echo "<option value='' selected></option>";
	while($r = mysqli_fetch_array($res)) {
		
		echo "<option value='".$r["idcontato"]."'>".$r["nome"]."</option>";        
   
	}    
          
?>


