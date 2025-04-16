<?
require_once("../inc/php/functions.php");

$departamento= $_GET['departamento']; 

if(empty($departamento)){
	die("Departamento NAO ENVIADO");
}
    $sql= "SELECT c.idsgarea as id, s.area FROM sgdepartamento c, sgarea s where c.idsgarea = s.idsgarea and c.idsgdepartamento = '".$departamento."'";
			$res = d::b()->query($sql) or die("Erro ao buscar Setor: ".mysqli_error());
		
			
		while($r = mysqli_fetch_array($res)) {
			echo "<option value='".$r["id"]."'>".$r["area"]."</option>";
		}
   
?>