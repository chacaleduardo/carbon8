<?
require_once("../inc/php/functions.php");

if(empty($_GET["idpessoa"])){
	die("Id do cliente (idpessoa) vazio.");
}

if(empty($_GET["coluna"])){
	die("Coluna desejada vazio");
}

$idpessoa = $_GET["idpessoa"];
$coluna = $_GET["coluna"];

$sqlc = "select distinct $coluna as coluna
			from amostra where 
			idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and idpessoa = ".$idpessoa."
				and ".$coluna." > ''
				and exercicio >= (".date("Y")." - 1)
			order by ".$coluna;

$res = d::b()->query($sqlc) or die("Erro ao recuperar itens históricos da amostra: ".mysqli_error(d::b()));

$arrTmp = array();

$i=0;
while($r = mysqli_fetch_assoc($res)) {
	$arrTmp[$i]["value"] = $r["coluna"];
	$arrTmp[$i]["label"] = $r["coluna"];
	$i++;
}

echo json_encode($arrTmp);
?>
