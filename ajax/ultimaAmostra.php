<?
require_once("../inc/php/functions.php");

//dados da amostra
$sqla = "select *
	from amostra where idamostra = ".$_GET["idamostra"];

$res = d::b()->query($sqla);

//dados dos testes
$sqlt = "select idresultado, idtipoteste, quantidade, idsecretaria
			, (select codprodserv from prodserv p where p.tipo = 'SERVICO' and p.idprodserv = r.idtipoteste) as codprodserv
		from resultado r where idamostra = ".$_GET["idamostra"];

$rest = d::b()->query($sqlt);

$arrUA=array();
//Dados da amostra
$r = mysqli_fetch_assoc($res);
//print_r($r);

while(list($k, $v) = each($r)){
	$arrUA["amostra"][$k] = $v;
}

//Loop Testes
while($rt = mysqli_fetch_assoc($rest)){
	//print_r($rt);
	while(list($k, $v) = each($rt)){
		//echo $virg.'"'.$k.'":"'.$v.'"';
		$arrUA["testes"][$rt["idresultado"]][$k] = $v;
	}
}

//print_r($arrUA);//die();
echo(json_encode($arrUA));
?>