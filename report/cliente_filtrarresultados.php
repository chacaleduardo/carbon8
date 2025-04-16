<?

//maf020719: Redirecionar o usuario conforme a unidade relacionada ao resultado
if($_GET["idunidade"]=="6" or $_GET["idunidade"]=="9"){
	header("Location: /report/traresultados.php?".$_SERVER["QUERY_STRING"]);
}else{
	header("Location: /report/emissaoresultado.php?".$_SERVER["QUERY_STRING"]);
}

?>

