<?
require_once("../inc/php/functions.php");

$idcampo = $_POST["idcampo"];
$campo = $_POST["campo"];
$tabela = $_POST["tabela"];

if(empty($idcampo) or empty($campo) or empty($tabela)){
	die("Erro enviado valor vazio para o update");
}else{
		$sqlmin="select ".$campo." as campo from ".$tabela." where id".$tabela." = ".$idcampo;
		$qrmin =  d::b()->query($sqlmin) or die("Erro busca na busca do  item:".mysql_error());
		
		
		$rowmin=mysqli_fetch_assoc($qrmin);	
		
		if($rowmin["campo"]=="Y"){
			$checked = "N";
		}elseif($rowmin["campo"]=="N"){
			$checked = "Y";
		}else{
			$checked = "Y";
		}

		$sql = "update ".$tabela." set ".$campo." = '".$checked."' where id".$tabela." =".$idcampo;
		
		$res =  d::b()->query($sql) or die("Error2 ao alterar item".$sql);
		//die($sql);
	
		echo $checked;	

}


?>