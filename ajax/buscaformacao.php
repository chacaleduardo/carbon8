<?
require_once("../inc/php/functions.php");

$cargo= $_GET['cargo']; 

if(empty($cargo)){
	die("Cargo NAO ENVIADO");
}

    $sql= "SELECT obs FROM sgcargo where idsgcargo = ".$cargo."";
	$res = d::b()->query($sql) or die("Erro ao buscar Cargo: ".mysqli_error());

	while($r = mysqli_fetch_array($res)) {
            echo $r["obs"];
	} 
          
?>
