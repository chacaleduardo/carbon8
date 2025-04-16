<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__ . "/../form/controllers/prodserv_controller.php");

$opcao			= filter_input(INPUT_GET, "vopcao") ?? $_POST['vopcao'];
$idprodserv		= filter_input(INPUT_GET, "vidprod") ?? $_POST['vidprod'];

if (empty($opcao)) {
	die("Opção: Variável POST não enviada corretamente!");
} else {
	//======= LTM (06/08/2020) ======
	if ($opcao == "atualizaLoteTipo") {
		//Apaga as prodserv que estão na tabela LoteTipoProdServ para atualizar as novas informações
		$sql = "DELETE FROM lotetipoprodserv WHERE idprodserv = " . $idprodserv . getidempresa('idempresa', 'lotetipoprodserv');
		$res = d::b()->query($sql) or die("Erro ao deletar prodserv: " . mysqli_error(d::b()));

		//Busca os dados da Tabela ProdServ para validar quais estão ativos e validar na Tabela lotetipo, retornando o Id do campo a ser inserido ou removido na LoteTipoProdServ 
		$sql = "SELECT comissionado, fabricado, material, comprado, imobilizado, venda, especial FROM prodserv WHERE idprodserv = " . $idprodserv;
		$res = d::b()->query($sql) or die("Erro ao carregar prodserv: " . mysqli_error(d::b()));
		while ($r = mysqli_fetch_assoc($res)) {
			//Insere id do Valor Comissionado
			if ($r['comissionado'] == 'Y') {
				$idlotetipo = buscaIdLoteTipo('COMISSIONADO', $r['especial']);
				insertLoteTipoProdServ($idprodserv, $idlotetipo);
			}
			//ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.	
			//Insere id do Valor Imobilizado
			if ($r['imobilizado'] == 'Y') {
				$idlotetipo = buscaIdLoteTipo('IMOBILIZADO', $r['especial']);
				insertLoteTipoProdServ($idprodserv, $idlotetipo);
			}
			//Insere o Id do Valor Fabricado (Formulado)
			if ($r['fabricado'] == 'Y') {
				$idlotetipo = buscaIdLoteTipo('FORMULADO', $r['especial']);
				insertLoteTipoProdServ($idprodserv, $idlotetipo);
			}

			//Insere o Id do Valor Material
			if ($r['material'] == 'Y') {
				$idlotetipo = buscaIdLoteTipo('MATERIAL', $r['especial']);
				insertLoteTipoProdServ($idprodserv, $idlotetipo);
			}

			//Insere o Id do Valor Comprado
			if ($r['comprado'] == 'Y') {
				$idlotetipo = buscaIdLoteTipo('COMPRADO', $r['especial']);
				insertLoteTipoProdServ($idprodserv, $idlotetipo);
			}

			//Insere o Id do Valor Venda
			if ($r['venda'] == 'Y') {
				$idlotetipo = buscaIdLoteTipo('VENDA', $r['especial']);
				insertLoteTipoProdServ($idprodserv, $idlotetipo);
			}
		}
	}

	if ($opcao == "atualizarVinculoComTagTipo") {
		$idobjetoArr = $_POST['idobjeto'];
		$idobjetovinc = $_POST['idobjetovinc'];
		$idProdserv = $_POST['idProdserv'];

		if ($idProdserv && $idobjetoArr && $idobjetovinc)
			atualizarObjetoVinculo($idProdserv, $idobjetoArr, $idobjetovinc);
	}

	if ($opcao == "excluirVinculoComTagTipo") {
		$idobjetovinc = $_POST['idobjetovinc'];
		$idProdserv = $_POST['idProdserv'];

		if ($idProdserv && $idobjetovinc)
			excluirVinculoComTagTipo($idProdserv, $idobjetovinc);
	}
}

function buscaIdLoteTipo($campo, $especial)
{
	$sql = "SELECT idlotetipo FROM lotetipo WHERE lotetipo = '" . $campo . "' AND especial = '" . $especial . "' AND status = 'ATIVO'" . getidempresa('idempresa', 'lotetipo') . "";
	$res = d::b()->query($sql) or die("Erro ao carregar lotetipo: " . mysqli_error(d::b()));
	while ($r = mysqli_fetch_assoc($res)) {
		$idlotetipo = $r['idlotetipo'];
	}

	return $idlotetipo;
}

function insertLoteTipoProdServ($idprodserv, $idlotetipo)
{
	$sql = "INSERT INTO lotetipoprodserv (idempresa, idlotetipo, idprodserv) VALUES (" . $_SESSION['SESSAO']['IDEMPRESA'] . ", '" . $idlotetipo . "', '" . $idprodserv . "');";
	$res = d::b()->query($sql) or die("Erro ao inserir lotetipoprodserv: " . mysqli_error(d::b()));
}

function atualizarObjetoVinculo($idProdserv, $idobjetoArr, $idobjetovinc)
{
	$errors = [];

	// Remover vinculos 
	$removendoVinculos = ProdServController::removerVinculosNoTagTipo($idProdserv, $idobjetovinc);

	if (!$removendoVinculos) {
		array_push($errors, [
			'label' => "Erro ao remover vínculos"
		]);

		echo json_encode($errors);
		return;
	}

	// Inserir novos vinculos
	foreach ($idobjetoArr as $idTag) {
		$inserindoVinculo = ProdServController::inserirVinculosNoTagTipo($idobjetovinc, $idTag);

		if (!$inserindoVinculo)
			array_push($errors, [
				'id' => $idTag,
				'label' => "Erro para o vínculo da tag $idTag"
			]);
	}

	echo json_encode($errors);
}

function excluirVinculoComTagTipo($idProdserv, $idobjetovinc)
{
	$errors = [];

	// Remover vinculos 
	$removendoVinculos = ProdServController::removerVinculosNoTagTipo($idProdserv, $idobjetovinc);

	if (!$removendoVinculos) {
		array_push($errors, [
			'label' => "Erro ao remover vínculos"
		]);

		echo json_encode($errors);
		return;
	}

	echo json_encode($errors);
}
