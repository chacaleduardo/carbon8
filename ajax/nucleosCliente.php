<?
require_once("../inc/php/functions.php");

if(empty($_GET["idpessoa"])){
	die("Id do Cliente não enviado via GET");
}
if(empty($_GET["idunidade"])){
	die("Id da Unidade não enviado via GET");
}

$sqlc = "SELECT n.idnucleo
			, n.nucleo
			, n.rotulonucleotipo
			, n.regoficial
			, n.granja
			, n.lote
			, n.idespeciefinalidade
			, n.finalidade
			, n.alojamento
			, n.nsvo
			, especietipofinalidade as especiefinalidade
			,n.cpfcnpj
			,n.uf
			,n.cidade
		FROM nucleo n
			LEFT JOIN vwespeciefinalidade e on e.idespeciefinalidade = n.idespeciefinalidade
		WHERE n.situacao = 'ATIVO'
			and n.idpessoa = ".$_GET["idpessoa"]." 
			and n.idempresa=".cb::idempresa()."
			and n.idunidade=".$_GET["idunidade"]."
		ORDER BY n.nucleo";

$res = d::b()->query($sqlc) or die("Erro ao recuperar nucleo: ".mysqli_error(d::b()));

$arrTmp = array();

$arrColunas = mysqli_fetch_fields($res);

while($r = mysqli_fetch_assoc($res)) {
	//para cada coluna resultante do select cria-se um item no array
	foreach ($arrColunas as $col) {
		$arrTmp[$r["idnucleo"]][$col->name] = $r[$col->name];
	}
}

echo json_encode($arrTmp);

?>