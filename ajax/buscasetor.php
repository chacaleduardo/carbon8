<?
require_once("../inc/php/functions.php");

if($_GET['departamento']){
	$departamento = $_GET['departamento'];
	if(empty($departamento)){
		die("Departamento NAO ENVIADO");
	}

		$sql= "SELECT idsgsetor as id, setor FROM sgsetor where idsgdepartamento = '".$departamento."' order by setor";
		$res = d::b()->query($sql) or die("Erro ao buscar Setor: ".mysqli_error());
			
		echo "<option value='' selected></option>";
		while($r = mysqli_fetch_array($res)) {
				echo "<option value='".$r["id"]."'>".$r["setor"]."</option>";
		}
}else{
	if($_GET['cargo']){
		$cargo = $_GET['cargo'];
		if(empty($cargo)){
			die("Setor NAO ENVIADO");
		}

			$sql= "SELECT c.idsgsetor as id, s.setor FROM sgcargo c, sgsetor s where c.idsgsetor = s.idsgsetor and c.idsgcargo = '".$cargo."'";
			$res = d::b()->query($sql) or die("Erro ao buscar Setor: ".mysqli_error());
				
			while($r = mysqli_fetch_array($res)) {
					echo "<option value='".$r["id"]."'>".$r["setor"]."</option>";
			}
	}
}

    
          
?>
