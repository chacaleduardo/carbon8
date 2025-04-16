<?
require_once("../inc/php/functions.php");

$setor = $_GET['setor']; 
$setor = explode("-", $setor);
if(empty($setor)){
	die("Setor NAO ENVIADO");
}
    echo $sql= "SELECT sc.idsgcargo AS id, sc.cargo 
	LEFT JOIN sgcargo sc ON(sc.idsgcargo = o.idobjeto AND o.tipoobjeto = 'sgcargo')
	LEFT JOIN sgsetor ss ON(ss.idsgsetor = o.idobjetovinc AND o.tipoobjetovinc = 'sgsetor')
	WHERE o.idobjetovinc = ".$setor[0]." AND o.tipoobjetovinc = '".$setor[1]."' ORDER BY sc.cargo";
	
	$res = d::b()->query($sql) or die("Erro ao buscar Cargo: ".mysqli_error());
	
	echo "<option value='' selected></option>";
	while($r = mysqli_fetch_array($res)) {
            echo "<option value='".$r["id"]."'>".$r["cargo"]."</option>";
	}    
          
?>
