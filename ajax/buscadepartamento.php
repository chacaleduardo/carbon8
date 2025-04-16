<?
require_once("../inc/php/functions.php");


if($_GET['area']){
	$area= $_GET['area']; 
	if(empty($area)){
		die("Área NAO ENVIADA");
	}
    $sql= "SELECT idsgdepartamento as id, departamento as dept FROM sgdepartamento where idsgarea = '".$area."' order by departamento";
	$res = d::b()->query($sql) or die("Erro ao buscar Área: ".mysqli_error());
        
	echo "<option value='' selected></option>";
	while($r = mysqli_fetch_array($res)) {
            echo "<option value='".$r["id"]."'>".$r["dept"]."</option>";
	} 
}else{
	if($_GET['setor']){
		$setor= $_GET['setor'];
		if(empty($setor)){
			die("Setor NAO ENVIADO");
		}
			$sql= "SELECT c.idsgdepartamento as id, s.departamento FROM sgsetor c, sgdepartamento s where c.idsgdepartamento = s.idsgdepartamento and c.idsgsetor = '".$setor."'";
			$res = d::b()->query($sql) or die("Erro ao buscar Setor: ".mysqli_error());
			
			
			while($r = mysqli_fetch_array($res)) {
					echo "<option value='".$r["id"]."'>".$r["departamento"]."</option>";
			}
	}
}
   
          
?>
