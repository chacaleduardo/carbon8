<?
//Cada unidade deve ter seu módulo correspondente configurado
$idunidade = getUnidadePadraoMatriz($_GET["_modulo"],cb::idempresa(), cb::habilitarMatriz());

if(empty($idunidade)){
	die("Presearch amostraaves: Erro ao recuperar ID da Unidade.\nCada unidade deve ter seu módulo correspondente configurado");
}

if(strpos($_GET['_modulo'], 'prov') == true){
		$_SESSION["SEARCH"]["WHERE"]['status'] = " status = 'PROVISORIO'";		
				 		
} elseif($_GET['_modulo'] == 'amostratra'){
	//LTM - 02-08-2021: Alterado para não mostrar os lotes cancelados
	$_SESSION["SEARCH"]["WHERE"]['status'] = " status != 'CANCELADO'";
}else{
	//LTM - 22-09-2020: Alterado para não mostrar os lotes cancelados
	$_SESSION["SEARCH"]["WHERE"]['status'] = " status != 'PROVISORIO' and status != 'CANCELADO'";					 
} 
//Filtra pela Unidade correspondente ao módulo
$_SESSION["SEARCH"]["WHERE"][] = " idunidade in (".$idunidade.")";

?>