<?
require_once("../inc/php/functions.php");

$uf= $_GET['uf']; 

if(empty($uf)){
	die("UF (Estado) NAO ENVIADO");
}

    $sql= "SELECT cidade,cidade FROM nfscidadesiaf where uf = '".$uf."' order by cidade";
	$res = d::b()->query($sql) or die("Erro ao buscar Cidade: ".mysqli_error());
	
	echo "<option value='' selected></option>";
	while($r = mysqli_fetch_array($res)) {
		
		echo "<option value='".$r["cidade"]."'>".$r["cidade"]."</option>";        
   
	}    
          
?>


