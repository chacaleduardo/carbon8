<?
require_once("../inc/php/validaacesso.php");

$idsolcomitem = $_POST['idsolcomitem'];

$sqlAlocado = "SELECT m.descricao, m.criadoem, m.idmodulocom, si.dataprevisao
				 FROM modulocom m JOIN solcomitem si ON m.idmodulo = si.idsolcomitem
				WHERE idmodulo = '$idsolcomitem' AND modulo = 'solcomitem'
				ORDER BY criadoem";
$resAlocado = d::b()->query($sqlAlocado) or die("A consulta do alocado falhou : ".mysql_error(d::b())."<br>Sql:".$sqlAlocado);
$numr2 = mysqli_num_rows($resAlocado);
if($numr2 == 0){
	return "";    
} else {
	while ($r = mysqli_fetch_assoc($resAlocado)) 
	{
		$arrtmp[$r["idmodulocom"]]["descricao"] = $r["descricao"];
		$arrtmp[$r["idmodulocom"]]["criadoem"] = dma($r["criadoem"]);
		$arrtmp[$r["idmodulocom"]]["dataprevisao"] = dma($r["dataprevisao"]);
	}
	
	$json = json_encode($arrtmp);
	echo($json);
} 
?>