<?
$expires = 60*60*24*14;
header("Pragma: public");
header("Cache-Control: maxage=".$expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');

require_once("../functions.php");
require_once("../validaacesso.php");

$sql = "SELECT idtipoamostra, idsubtipoamostra, campo
	FROM amostracampos t 
	order by idtipoamostra, idsubtipoamostra, idamostracampos";

$res = mysql_query($sql) or die("Falha pesquisando configuracao de campos da tela da amostra : " . mysql_error() . "<p>SQL: $sql");

$total = mysql_num_rows($res);

$ctrlgrupo = ""; // controla quando sera iniciado um novo grupo
$ctrlkey = 0; // controla a chave ordenada para o array do javascript

while ($r = mysql_fetch_assoc($res)) {

	$id1 = $r["idtipoamostra"];
	$id2 = $r["idsubtipoamostra"];
	$strid = "x".$id1."x".$id2;	
	$cmp = $r["campo"];
	
	if($ctrlgrupo != $strid){
		$ctrlgrupo = $strid;
		$ctrlkey = 0;
		echo "\n\n".$strid." = new Array();";
	}

	echo "\n".$strid."[".$ctrlkey."] = '".$cmp."';";

	$ctrlkey++;
}

/*
exemplo de saida:

x2x2 = new Array();
x2x2[0] = 'nroamostra';
x2x2[1] = 'lote';
x2x2[2] = 'idade';
x2x2[3] = 'tipoaves';
x2x2[4] = 'setor';
x2x2[5] = 'galpao';
x2x2[6] = 'linha';
x2x2[7] = 'sexo';
x2x2[8] = 'datacoleta';
x2x2[9] = 'responsavel';
x2x2[10] = 'observacao';
x2x2[11] = 'observacaointerna';
x2x2[12] = 'granja';
x2x2[13] = 'idnucleo';
*/
?>