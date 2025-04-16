<?
require_once("../inc/php/functions.php");

if($_GET['estado']){
	$estado = $_GET['estado'];
	if(empty($estado)){
		die("Estado NAO ENVIADO");
	}else{
		switch($estado){
			case "#idcargo":
				$sql= "select idsgcargo as id, cargo  from sgcargo where status = 'ATIVO' order by cargo";
				$res = d::b()->query($sql) or die("Erro ao buscar Cargo: ".mysqli_error());
				
				echo "<option value='' selected></option>";
				while($r = mysqli_fetch_array($res)) {
					echo "<option value='".$r["id"]."'>".$r["cargo"]."</option>";
				}
				break;
			case "#idsetor":
				$sql= "select idsgsetor as id, setor  from sgsetor where status = 'ATIVO' order by setor";
				$res = d::b()->query($sql) or die("Erro ao buscar Setor: ".mysqli_error());
				
				echo "<option value='' selected></option>";
				while($r = mysqli_fetch_array($res)) {
					echo "<option value='".$r["id"]."'>".$r["setor"]."</option>";
				}
				break;
			case "#iddepartamento":
				$sql= "select idsgdepartamento as id, departamento  from sgdepartamento where status = 'ATIVO' order by departamento ";
				$res = d::b()->query($sql) or die("Erro ao buscar Departamento: ".mysqli_error());
				
				echo "<option value='' selected></option>";
				while($r = mysqli_fetch_array($res)) {
					echo "<option value='".$r["id"]."'>".$r["departamento"]."</option>";
				}
				break;
			case "#idarea":
				$sql= "select idsgarea as id, area  from sgarea where status = 'ATIVO' order by area ";
				$res = d::b()->query($sql) or die("Erro ao buscar Área: ".mysqli_error());
				
				echo "<option value='' selected></option>";
				while($r = mysqli_fetch_array($res)) {
					echo "<option value='".$r["id"]."'>".$r["area"]."</option>";
				}
				break;
			default:
				die("Reinicie a página");
		}
	}
}
?>