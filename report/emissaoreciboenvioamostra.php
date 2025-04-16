<?
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . "/../inc/php/validaacesso.php";
require_once __DIR__ . "/../form/controllers/enviopedidoamostra_controller.php";
require_once __DIR__ . "/../form/querys/amostra_query.php";

$pedido = EnvioPedidoAmostraController::buscarInformacoesPedidoAmostra($_GET['protocolo']);
$pedido['jsonpedido'] = json_decode($pedido['jsonpedido'], true);

function getAmostraCadastrada($idamostra){
	$result = SQL::ini(AmostraQuery::buscarPorChavePrimaria(),[
		'pkval' => $idamostra
	])::exec();
	
	if($result->error() || $result->numRows() == 0) return false;
	
	return $result->data[0];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Emissão resultado</title>
	<link href="/var/www/carbon8/inc/css/bootstrap/css/bootstrap.css" media="all" rel="stylesheet" type="text/css" />

	<style>
		* {
			margin: 0;
			padding: 0;

		}

		/* Estilos base para impressão em A4 */
		@page {
			size: A4;
			margin: 0mm;
		}
		
		body {
			background-color: #fafafa;
			font-family: Helvetica, Arial,sans-serif;
			line-height: 1.5;
			font-weight: normal;
		}
		h1, h2,h3, h4, h5, h6{
			font-family: Helvetica, Arial,sans-serif
		}
		/* Ajustes de tamanhos de fontes e pesos */
		h1 {
			font-size: 24pt;
			font-weight: 300;
			margin-bottom: 12pt;
		}

		h2 {
			font-size: 20pt;
			font-weight: 400;
			margin-bottom: 10pt;
		}

		h3 {
			font-size: 18pt;
			font-weight: 500;
			margin-bottom: 8pt;
		}

		h4 {
			font-size: 16pt;
			font-weight: 500;
			margin-bottom: 6pt;
		}

		h5 {
			font-size: 14pt;
			font-weight: 500;
			margin-bottom: 4pt;
		}

		h6 {
			font-size: 12pt;
			font-weight: 600;
			margin-bottom: 4pt;
		}

		/* Texto normal */
		p {
			font-size: 12pt;
			margin-bottom: 8pt;
			font-weight: 400;
		}

		small {
			font-size: 10pt;
		}

		/* Remover elementos não imprimíveis */
		@media print {
			.no-print {
				display: none;
			}
		}

		body {
			font-size: 12px;
		}

		.container {
			width: 100%;
			padding:20px;
			margin-right: auto;
			margin-left: auto;
		}

		.logolaudo {
			text-align: center;
		}

		.logolaudo img {
			width: 80%;
		}

		.text-white {
			color: white !important;
		}
		table{
			text-transform: uppercase;
		}
		table {
			table-layout: fixed;
			width: 100%;
		}

		td {
			word-wrap: break-word;
			word-break: break-all; /* ou try word-break: break-word; */
		}
		table.dados-cliente {
			width: 100%;
		}

		table.dados-cliente tr td:first-child {
			width: 25%;
		}
		table.dados-cliente tr td:last-child {
			width: 75%;
		}
		.dados-cliente2 tr td:first-child {
			width: 35% !important;
		}
	

		.header-envio {
			width: 100%;
			background-image: url(https://resultados-acpt.biofy.tech/form/img/bg-resultado-inata.png);
			background-repeat: no-repeat;
			padding: 10px;
			font-weight: bold;
		}

		.borda {
			padding: 10px;
			border: 1px solid #000800;
			border-radius: 4px;
		}
		.qrcode{
			margin:auto;
			width: 100%;
			text-align: center;
			padding-top: 30px;
		}
	</style>
</head>

<body class="container">
	<div class="header-envio">
		<div class="row">
			<div class="col col-sm-6">
				<div class="logolaudo"><img src="https://resultados-acpt.biofy.tech/form/img/logolaudoenviapedido.png" alt=""></div>
			</div>
			<div class="col col-6 text-white text-bold">
				<div>
					<span>
						Laudo Laboratório Avícola Uberlândia Ltda <br>
						CNPJ: 23.259.427/0001-04 <br>
						Rod. BR 365 - Morumbi, Uberlândia -MG
					</span>
				</div>
				<div>
					<span>(34) 3222-5700</span>
				</div>
				<div>
					<span>sac@laudolab.com.br</span>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col col-sm-12">
			<h3 class="text-center">RECIBO - FORMULÁRIO DE ENVIO DE AMOSTRAS</h3>
		</div>
	</div>
	<div class="borda">
		<div class="row">
			<div class="col col-sm-7">
				<table class="dados-cliente">
					<!-- Dados Exame -->
					<tr>
						<td style="width: 25%;"><strong>Protocolo:</strong></td>
						<td style="width: 75%;"><span><?= $pedido['jsonpedido']['dadosCliente']['protocolo'] ?><span></td>
					</tr>

					<tr>
						<td><strong>Tutor:</strong></td>
						<td><span><?= $pedido['jsonpedido']['dadosCliente']['tutor'] ?? '-' ?></span></td>
					</tr>

					<tr>
						<td><strong>Paciente:</strong></td>
						<td><span><?= $pedido['jsonpedido']['dadosCliente']['paciente'] ?? '-' ?></span></td>
					</tr>

					<tr>
						<td><strong>Espécie:</strong></td>
						<td><span><?= traduzid("vwespeciefinalidade", "idespeciefinalidade", "especietipofinalidade", $pedido['jsonpedido']['dadosCliente']['especie']); ?></span></td>
					</tr>

					<tr>
						<td><strong>Sexo:</strong></td>
						<td><span><?= $pedido['jsonpedido']['dadosCliente']['sexagem'] ?></span></td>
					</tr>

					<tr>
						<td><strong>Idade:</strong></td>
						<td><span><?= "{$pedido['jsonpedido']['dadosCliente']['idade']} {$pedido['jsonpedido']['dadosCliente']['periodo']}" ?></span></td>
					</tr>
				</table>
			</div>
			<div class="col col-sm-5">
				<table class="dados-cliente dados-cliente2">
					<tr>
						<td><strong>Data de envio:</strong></td>
						<td><span><?= formatadatadbweb($pedido['criadoem']) ?></span></td>
					</tr>
					<tr>
						<td><strong>Médico vet:</strong></td>
						<td><span><?= $pedido['jsonpedido']['dadosCliente']['veterinario'] ?></span></td>
					</tr>
					<tr>
						<td><strong>Urgente</strong>
						<td><span><?= $pedido['jsonpedido']['dadosCliente']['urgente_animal'] ? 'Sim' : 'Não'; ?></span></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<!-- Amostras enviadas -->
	<div class="row">
		<div class="col col-sm-12">
			<h3 class="text-center">AMOSTRAS ENVIADAS</h3>
		</div>
	</div>
	<? foreach ($pedido['jsonpedido']['amostras'] as $amostra){
			$amostraCadastrada = getAmostraCadastrada($amostra['idamostra']);
		?>
		<!-- amostra -->
		 <div class="borda" style="margin-bottom: 10px;">
			<div class="row">
				<div class="col col-sm-12">
					<table class="table" style="width: 100%;">
						<thead>
							<tr>
								<th>Amostra:</th>
								<th><span style="color:lightslategrey;"><?= $amostra['idamostra'] ?></span></th>
								<th>Registro:</th>
								<th><span style="color:lightslategrey;"><?= $amostraCadastrada['idregistro'] ?></span></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="width: 20%;"><strong>Tipo da amostra:</strong></td>
								<td style="width: 40%;"><?= $amostra['subtipoamostra']['text'] ?></td>
								<td style="width: 20%;"><strong>Data de coleta:</strong></td>
								<td style="width: 20%;"><?= !empty($amostra['dataColeta']) && $amostra['dataColeta']!=""?$amostra['dataColeta']:"Não informado" ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="row">
				<div class="col col-sm-12">
					<table class="table" style="width: 100%;">
						<thead>
							<tr>
								<th style="width: 80%;">Exames solicitados</th>
								<th style="width: 20%;">Urgente</th>
							</tr>
						</thead>
						<? foreach ($amostra['exames'] as $key => $exame) { ?>
							<tr>
								<td style="width: 80%;">
									<?= $exame["exame"] ?>
								</td>
								<td style="width: 20%;"><?= $exame["urgente"]?"SIM":"NÃO" ?></td>
							</tr>
						<? } ?>
					</table>
				</div>
			</div>
		</div>
	<? } ?>

	<div class="qrcode">
		<div style="color:lightslategrey;;min-width:200px;font-size:10px"><?= $pedido['jsonpedido']['dadosCliente']['protocolo']?></div>
		<div style="color:lightslategrey;min-width:200px;font-size:10px">Data de impressão: <?= date("d-m-Y H:i:s");?></div>
		<img  style="margin-top:10px"  src="<?= getQrcode()?>" alt="">
	</div>

</body>

</html>

<?

use Com\Tecnick\Barcode\Barcode;

function getQrcode($path=null){
	
	if(!$path)
	{
		$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	}
	
	$host  = $_SERVER['HTTP_HOST'];
	$url = "https://" . $host . "/" . $path;
	
	try {
		require_once '/var/www/carbon8/inc/cte/vendor/autoload.php';
		$barcode = new Barcode();

		$bobj = $barcode->getBarcodeObj(
			'QRCODE,M',
			$url,
			-2,
			-2,
			'black',
			array(-2, -2, -2, -2)
		)->setBackgroundColor('white');
		$qrcode = $bobj->getPngData();
	} catch (\Throwable $th) {
		throw $th;
	}
	
	// prepare a base64 encoded "data url"
	return 'data://text/plain;base64,' . base64_encode($qrcode);
}

$html = ob_get_contents();
//limpar o codigo html
//$html = preg_replace('/>\s+</', "><", $html);

ob_end_clean();

//echo($html);die;

// Incluímos a biblioteca DOMPDF
require_once("../inc/php/composer/vendor/autoload.php");
require_once("../inc/php/composer/vendor/dompdf/dompdf/src/Dompdf.php");

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', TRUE);

// Instanciamos a classe
$dompdf = new Dompdf($options);
//$dompdf->set_base_path("/var/www/carbon8/inc/css/bootstrap/css/");
//$html=preg_match("//u", $html)?utf8_decode($html):$html;
$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

// Passamos o conteúdo que será convertido para PDF
$dompdf->load_html($html);

// Definimos o tamanho do papel e
// sua orientação (retrato ou paisagem)
//$dompdf->set_paper(array(0, 0, 690, 841.89),'portrait');
$dompdf->set_paper('A4', 'portrait');


// O arquivo é convertido
$dompdf->render();

// e exibido para o usuário
$dompdf->stream("Pedido" . $pedido['jsonpedido']['dadosCliente']['protocolo'] . ".pdf", ['Attachment' => false]);
?>