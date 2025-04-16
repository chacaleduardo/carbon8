<?
require_once("../inc/php/functions.php");

$idempresa= $_GET['idempresa']; 

if(empty($idempresa)){
	die("CLIENTE OU FORNECEDOR NAO ENVIADO");
}

    $sql= "select 
                p.idpessoa,
                if(p.cpfcnpj !='',concat(p.nome,' - ',p.cpfcnpj),p.nome) as nome
                    from pessoa p
                    where p.idtipopessoa in (5) 
                        and p.status = 'ATIVO'
			and p.idempresa = ".$idempresa."
			 order by nome";
    
    //die($sql);
	$res = d::b()->query($sql) or die("Erro ao buscar fornecedores: ".mysqli_error());
	
		
	echo "<option value='' selected></option>";
	while($r = mysqli_fetch_array($res)) {
		
		echo "<option value='".$r["idpessoa"]."'>".$r["nome"]."</option>";        
   
	}    
          
?>


