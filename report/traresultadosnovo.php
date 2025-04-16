<?
ini_set('memory_limit', '-1');
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/traresultados_controller.php");
require_once("../form/controllers/reportelisa_controller.php");


$idamostratra      = $_GET["idamostratra"];
$_vids            = $_GET['_vids'];

if (empty($idamostratra)) {
	$idamostratra = $_GET["idamostra"];
}
if (empty($idamostratra)) {
	die("Amostra não enviada");
}
$idunidade = $_GET["unidadepadrao"];
if (empty($idunidade)) {
	$idunidade = $_GET["idunidade"];
}

$dadosAmostra = TraResultadosController::buscarDadosAmostra($idamostratra);
$dadosResultadosAmostra = TraResultadosController::buscarDadosResultadosAmostra($idamostratra, $dadosAmostra['dataamostra']);
$possuiAntibiograma = array_search('Y', array_column($dadosResultadosAmostra, 'relatorioantibiograma'));

if ($dadosAmostra["status"] == "ABERTO" or $dadosAmostra["status"] == "ENVIADO") {
	$titulo = "Termo de Envio de Amostra";
	$sub = "TEA";
} else if ($dadosAmostra["status"] == "DEVOLVIDO" or $dadosAmostra["status"] == "ASSINADO") {
	$titulo = "Termo de Recepção de Amostra";
	$sub = "TRA";
} else {
	$titulo = "";
}

?>
<html>

<head>
	<link href="../inc/css/emissaoresultadopdf.css" rel="stylesheet" type="text/css" />
	<link href="../inc/css/mtorep.css" media="all" rel="stylesheet" type="text/css">

	<style type="text/css" media="print">
		@media print {
			body {
				-webkit-print-color-adjust: exact;
			}

			a[href]:after {
				content: none !important;
			}

			.no-print,
			.no-print * {
				display: none !important;
			}
		}

		@page {
			size: A4;
			margin-left: 0px;
			margin-right: 0px;
			margin-top: 0px;
			margin-bottom: 0px;
			margin: 0;
			-webkit-print-color-adjust: exact;
		}
	</style>

	<style>
		#inter span {
			font-size: 8px !important;
			font-weight: normal;
		}

		#inter u {
			font-size: 8px !important;
			font-weight: normal;
		}

		.tabtitulos td {
			height: 10px;
		}

		.MsoTableGrid td {
			border-bottom: 1px solid silver !important;
			border-top: 1px solid silver !important;
			border-left: 1px solid silver !important;
			border-right: 1px solid silver !important;
		}

		strong {
			font-weight: normal !important;
		}

		span {
			font-size: 6px !important;
		}

		.relisa {
			width: 10.1% !important;
			display: inline-block;
		}

		.MsoTableGrid {
			width: 100%;
		}

		.MsoTableGrid {
			border-color: #eee !important;
		}

		.trelisa {
			height: 10px;
		}

		.trpos {

			background-color: #FFC0C0;
		}

		table {
			padding: 0px;
			margin: 0px;
			border-spacing: 0px;
			font-size: 7px;
			font-family: Verdana, Geneva, sans-serif !important;
			font-weight: normal !important;
			color: #333 !important;
			page-break-inside: auto;
			width: 100%;
			text-transform: uppercase;
		}

		tr {
			page-break-inside: avoid;
			page-break-after: auto;
		}

		thead {
			display: table-header-group
		}

		tfoot {
			display: table-footer-group
		}


		.tdrot {
			width: 105px !important;
			font-size: 7px !important;
			color: #333 !important;
			height: 12px;
			text-align: left !important;
			background-color: #fff;
			font-family: Verdana, Geneva, sans-serif !important;

		}

		.tdval {
			font-size: 8px !important;
			font-family: Verdana, Geneva, sans-serif !important;
			text-transform: uppercase;
			color: #333 !important;
			background-color: #fff;
		}

		.tablegenda tr td {
			padding-left: 4px !important;
		}

		.resdesc table td {
			padding-left: 4px !important;
			padding: 1px !important;
			height: 10px !important;
			border: 1px solid #e1e1e1;
		}

		.resdesc table tr {
			height: 10px !important;

		}

		.resdesc table {
			border: none !important;
		}

		.resdesc2 p,
		.resdesc2 strong,
		.resdesc2 div {
			font-size: 7px !important;
			line-height: 8px !important;
		}

		.resdesc2 span {
			font-size: 7px !important;
			line-height: 8px !important;
		}

		.resdesc2 td {
			border: none !important;
		}

		#resm span {
			font-size: 8px !important;
		}


		.MsoTableGrid td {
			border-bottom: 1px solid silver !important;
			border-top: 1px solid silver !important;
			border-left: 1px solid silver !important;
			border-right: 1px solid silver !important;
		}

		strong {
			font-weight: normal !important;
		}

		span {
			font-size: 7px !important;
		}

		.relisa {
			width: 12% !important;
			display: inline-block;
		}

		.MsoTableGrid {
			width: 100%;
		}


		.MsoTableGrid {
			border-color: #fff !important;
		}

		.trelisa {
			height: 12px;
		}

		.trpos {
			background-color: #FFC0C0;
		}

		@font-face {
			font-family: 'Roboto';
			src: url("../inc/css/fonts/Roboto-Regular.woff2?v=2.137") format("woff2"), url("../inc/css/fonts/Roboto-Regular.woff?v=2.137") format("woff");
			font-weight: 400;
			font-style: normal;
		}

		@font-face {
			font-family: 'Roboto';
			src: url("../inc/css/fonts/Roboto-Regular.woff2?v=2.137") format("woff2"), url("../inc/css/fonts/Roboto-Regular.woff2?v=2.137") format("woff");
			font-weight: normal;
			font-style: normal;
		}

		/* BEGIN Bold */
		@font-face {
			font-family: 'Roboto';
			src: url("../inc/css/fonts/Roboto-Bold.woff2?v=2.137") format("woff2"), url("../inc/css/fonts/Roboto-Bold.woff?v=2.137") format("woff");
			font-weight: 700;
			font-style: normal;
		}

		@font-face {
			font-family: 'Roboto';
			src: url("../inc/css/fonts/Roboto-Bold.woff2?v=2.137") format("woff2"), url("../inc/css/fonts/Roboto-Bold.woff?v=2.137") format("woff");
			font-weight: bold;
			font-style: normal;
		}

		/* END Bold */


		.tdval {
			font-size: 7px !important;
			font-family: 'Roboto' !important;
			text-transform: uppercase;
			color: #333 !important;
			background-color: #fff;
		}

		.tdtit {
			width: 80px !important;
			font-size: 7px !important;
			color: #333 !important;
			height: 14px;
			text-align: left !important;
			background-color: #fff;
			font-family: 'Roboto' !important;
			font-weight: bold;
		}

		.mostraresultado .tdval .grval,
		.mostraresultado .tbgr .grval,
		.mostraresultado .tbgr {
			border: none !important;
		}

		.quebrapagina {
			page-break-before: always;
		}
	</style>


	<title>Resumo Diagnóstico</title>
</head>

<body>

	<table style="margin:auto; ">
		<thead>
			<tr>
				<td>
					<div>
						<table class="cabtxt" style="width:100%;position:relative; z-index:2; background-color:#fff; margin-bottom:0px;">
							<tr>
								<td style="background:url('../inc/img/cabecalho-relatorio-de-ensaio-LAUDO-INATA.jpg'); width:573px; height:90px;
                                 background-position: left; background-size: cover; border: 1px solid #fff; border-radius: 7px 0px 0px 0px; border-right:none;background-repeat:no-repeat;		">
									&nbsp;
								</td>
								<td style="border: 1px solid #fff; border-radius: 0px 7px 0px 0px;  border-left:none; text-align:right"></td>
							</tr>

						</table>
					</div>
				</td>
			</tr>
		</thead>
		<!-- Controle Impressao -->
		<tbody>
			<tr>
				<td style="width: 100%">
					<table style="width: 100%">
						<tr>
							<td>
								<table class="tsep" style="width:100%; margin-top:6px;">
									<tr>
										<td style="text-align:center; font-size:13px;">RESUMO <?= $titulo; ?> - LDA</td>
									</tr>

									<tr>
										<td>
											<table class="tsep" style="width:100%;">
												<tr>
													<td>
														<table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">

															<tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase;	height:20px;">
																<td colspan="6" style="font-size:11px;">DADOS DO CLIENTE
																</td>
															</tr>
															<tr>
																<td class="tdrot grrot" width="5%">Cliente:</td>
																<td class="tdval grval" colspan="2" width="45%"><?= $dadosAmostra["razaosocial"] ?></td>
																<td class="tdrot grrot" width="15%">DATA COLETA:</td>
																<td class="tdval grval" colspan="2" width="35%"><?= dma($dadosAmostra["datacoleta"]) ?></td>
															</tr>
															<tr>
																<td class="tdrot grrot">Endereço:</td>
																<td class="tdval grval" colspan="5"> <?= $dadosAmostra["enderecosacado"] ?> </td>
															</tr>
														</table>
													</td>
												</tr>

											</table>
										</td>
									</tr>
									<tr>
										<td>
											<table class="tsep" style="width:100%;">
												<tr>
													<td>
														<table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
															<tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase;	height:20px;">
																<td colspan="6" style="font-size:11px;">DADOS DA AMOSTRA</td>
															</tr>
															<tr>
																<td style="width:12% !important;" class="tdrot grrot">Nº de Registro:</td>
																<td style="width:38% !important;" class="tdval grval"><?= $dadosAmostra['idregistro'] ?></td>
																<td class="tdrot grrot">Linha:</td>
																<td class="tdval grval"><?= $dadosAmostra["linha"] ?></td>
															</tr>
															<tr>
																<td style="width:12% !important;" class="tdrot grrot">Data Registro:</td>
																<td style="width:38% !important;" class="tdval grval"><?= dma($dadosAmostra['dataamostra']) ?></td>
																<td class="tdrot grrot">RESPONS. COLETA:</td>
																<td class="tdval grval"><?= $dadosAmostra["responsavel"] ?></td>
															</tr>
															<tr>
																<td class="tdrot grrot">Nº de Amostras:</td>
																<td class="tdval grval"><?= $dadosAmostra["nroamostra"] ?></td>
																<td class="tdrot grrot">Lote:</td>
																<td class="tdval grval"><?= $dadosAmostra["lote"] ?></td>
															</tr>
															<tr>
																<td class="tdrot grrot">Descrição:</td>
																<td class="tdval grval"><?= $dadosAmostra["descricao"] ?></td>
																<td class="tdrot grrot">Idade:</td>
																<td class="tdval grval"><?= $dadosAmostra["idade"] . " " . $dadosAmostra["tipoidade"] ?></td>
															</tr>
															<? if ($dadosAmostra["regoficial"]) { ?>
																<tr>
																	<td class="tdrot grrot">Nº Registro oficial:</td>
																	<td class="tdval grval"><?= $dadosAmostra["regoficial"] ?></td>
																</tr>
															<? } ?>
															<tr>
																<td class="tdrot grrot">Observações da Coleta:</td>
																<td class="tdval grval" colspan="6"><?= nl2br($dadosAmostra["observacao"]) ?></td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td>
											<table class="tsep" style="width:100%;">
												<tr>
													<td>
														<table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
															<tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase;	height:20px;">
																<td colspan="6" style="font-size:11px;">DADOS EPIDEMIOLÓGICOS</td>
															</tr>

															<tr>
																<td class="tdrot grrot">Início sinais clínicos:</td>
																<td class="tdval grval" colspan="3"><?= $dadosAmostra["sinaisclinicosinicio"] ?></td>
																<td class="tdrot grrot">Suspeitas clínicas:</td>
																<td class="tdval grval" colspan="3"><?= nl2br($dadosAmostra["suspclinicas"]) ?></td>
															</tr>
															<tr>
																<td class="tdrot grrot">Sinais clínicos:</td>
																<td class="tdval grval" colspan="6"><?= nl2br($dadosAmostra["sinaisclinicos"]) ?></td>
															</tr>
															<tr>
																<td class="tdrot grrot">Achados necrópsia:</td>
																<td class="tdval grval" colspan="6"><?= nl2br($dadosAmostra["achadosnecropsia"]) ?></td>
															</tr>
															<tr>
																<td class="tdrot grrot">Histórico problema:</td>
																<td class="tdval grval" colspan="6"><?= nl2br($dadosAmostra["histproblema"]) ?></td>
															</tr>
															<tr>
																<td class="tdrot grrot">Morbidade<span class="fonte8"> (N&ordm; animais)</span>:</td>
																<td class="tdval grval" colspan="2"><?= $dadosAmostra["morbidade"] ?></td>
																<td class="tdrot grrot">Letalidade<span class="fonte8"> (N&ordm; animais)</span>:</td>
																<td class="tdval grval"><?= $dadosAmostra["letalidade"] ?></td>
																<td class="tdrot grrot">Mortalidade<span class="fonte8"> (N&ordm; animais)</span>:</td>
																<td class="tdval grval"><?= $dadosAmostra["mortalidade"] ?></div>
															</tr>
															<tr>
																<td class="tdrot grrot">Uso medicamentos:</td>
																<td class="tdval grval" colspan="3"><?= $dadosAmostra["usomedicamentos"] ?></td>
																<td class="tdrot grrot">Uso de vacinas:</td>
																<td class="tdval grval" colspan="3"><?= $dadosAmostra["usovacinas"] ?></td>
															</tr>
															<? if ($dadosAmostra["formaarmazen"] or $dadosAmostra["meiotransp"]) {
															?>
																<tr>
																	<td class="tdrot grrot">Forma de armaz.:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["formaarmazen"] ?></td>
																	<td class="tdrot grrot">Meio de transp.:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["meiotransp"] ?></td>
																</tr>
															<?
															}
															if ($dadosAmostra["condconservacao"]) {
															?>
																<tr>
																	<td class="tdrot grrot">Cond. conservação:</td>
																	<td class="tdval grval" colspan="6"><?= nl2br($dadosAmostra["condconservacao"]) ?></td>
																</tr>
															<?
															}
															if ($dadosAmostra["sexo"] or $dadosAmostra["clienteterceiro"]) {
															?>
																<tr>
																	<td class="tdrot grrot">Sexo:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["sexo"] ?></td>
																	<td class="tdrot grrot">Cliente 3&ordm;:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["clienteterceiro"] ?></td>
																</tr>
															<?
															}
															if ($dadosAmostra["nucleoorigem"] or $dadosAmostra["tipo"]) {
															?>
																<tr>
																	<td class="tdrot grrot">Núcleo origem:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["nucleoorigem"] ?></td>
																	<td class="tdrot grrot">Tipo:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["tipo"] ?></td>
																</tr>
															<?
															}
															if ($dadosAmostra["especificacao"] or $dadosAmostra["partida"]) {
															?>
																<tr>
																	<td class="tdrot grrot">Especificações:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["especificacao"] ?></td>
																	<td class="tdrot grrot">Partida:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["partida"] ?></td>
																</tr>
															<?
															}
															if ($dadosAmostra["datafabricacao"] or $dadosAmostra["identificacaochip"]) {
															?>
																<tr>
																	<td class="tdrot grrot">Data fabricação:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["datafabricacao"] ?></td>
																	<td class="tdrot grrot">Chip/Identif.:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["identificacaochip"] ?></td>
																</tr>
															<?
															}
															if ($dadosAmostra["nroplacas"] or $dadosAmostra["nrodoses"]) {
															?>
																<tr>
																	<td class="tdrot grrot">Nº Placas:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["nroplacas"] ?></td>
																	<td class="tdrot grrot">Nº Doses:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["nrodoses"] ?></td>

																</tr>
															<?
															}
															if ($dadosAmostra["notafiscal"] or $dadosAmostra["vencimento"]) {
															?>
																<tr>
																	<td class="tdrot grrot">Nota Fiscal:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["notafiscal"] ?></td>
																	<td class="tdrot grrot">Vencimento:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["vencimento"] ?></td>
																</tr>
															<?
															}
															if ($dadosAmostra["sexadores"] or $dadosAmostra["localexp"]) {
															?>
																<tr>
																	<td class="tdrot grrot">Sexadores:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["sexadores"] ?></td>
																	<td class="tdrot grrot">Local específico:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["localexp"] ?></td>
																</tr>
															<?
															}
															if ($dadosAmostra["lacre"] or $dadosAmostra["tc"]) {
															?>
																<tr>
																	<td class="tdrot grrot">Lacre:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["lacre"] ?></td>
																	<td class="tdrot grrot">Termo de coleta:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["tc"] ?></td>
																</tr>
															<?
															}
															if ($dadosAmostra["fabricante"] or $dadosAmostra["semana"]) {
															?>
																<tr>
																	<td class="tdrot grrot">Fabricante:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["fabricante"] ?></td>
																	<td class="tdrot grrot">Semana:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["semana"] ?></td>
																</tr>
															<?
															}
															if ($dadosAmostra["diluicoes"] or $dadosAmostra["fornecedor"]) {
															?>
																<tr>
																	<td class="tdrot grrot">Diluições:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["diluicoes"] ?></td>
																	<td class="tdrot grrot">Fornecedor:</td>
																	<td class="tdval grval" colspan="3"><?= $dadosAmostra["fornecedor"] ?></td>
																</tr>
															<?
															}
															?>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td>
											<table class="tsep" style="width:100%;">
												<tr>
													<td>
														<table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
															<tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase; height:20px;">
																<td colspan="6" style="font-size:11px;">RESULTADO DO(S) EXAME(S) </td>
															</tr>


															<? foreach ($dadosResultadosAmostra as $key => $rowa) {
																if ($rowa['relatorioantibiograma'] == 'N') {

																	if ($rowa['jresultado']) {
																		$row = TraResultadosController::criarArrayDadosJsonAmostra($rowa['jsonResultado']); ?>


																		<tr>
																			<td class="" colspan="6">
																				<table class="tsep" style="width:100%; margin-top:0px;">
																					<!-- Cabecalho Superior -->
																					<tr>
																						<td style="width:100%">
																							<table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
																								<tr style="background-color:#f7f7f7; font-size:11x; text-transform:uppercase;	height:20px;">
																									<td idresultado="<?= $row["idresultado"] ?>" style="font-size:11px;width: 90%;"><b><?= $rowa["descr"] ?></b> <span style="font-size:6px !important;">(<?= $row["quantidade"]; ?> teste(s) realizado(s))</span> </td>
																									<td style="width: 10%;"><a class="no-print" style=" FONT-SIZE: 8PX;  TEXT-DECORATION: NONE;" title="LDA Detalhado" href="emissaoresultado.php?idresultado=<?= $rowa["idresultado"] ?>">Ver Mais</a></td>
																								</tr>
																							</table>
																							<table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
																								<tr>
																									<td class="tdval grval">
																										<br>
																										<table style="width:100%; text-transform:none;">

																											<?
																											if ($row['modelo'] == "UPLOAD") { //Se for elisa, quebrar a tabela em partes iguais para nao gerar paginas 'soltas' na impressao
																												reportElisaController::montarReportElisa($row["idresultado"], $row["idnucleo"], $row["idpessoa"], $row["idtipoteste"], $row["tipoidade"], $row["idespeciefinalidade"], $mostraass, 1, $row["textointerpretacao"], $row["textopadrao"]);
																											} else {
																											?>
																												<tr>
																													<? if ($row['modelo'] == 'DINÂMICO' && empty($urlimg)) {
																														echo '<td style="vertical-align:top;width:100%;">';
																													} else if (empty($urlimg)) { ?>
																														<td style="vertical-align:top;width:100%">
																														<? 	} else { ?>
																														<td style="vertical-align:top;width:64%">
																														<? 	} ?>

																														<fieldset class="fset" style="border:none;">
																															<div class="resdesc" id="resm" style="vertical-align:top">


																																<?

																																//PARA MODELO DESCRITIVO
																																if (empty($row["descritivo"]) and $row['resultadostatus'] == 'ABERTO' and !empty($row['idtipoteste'])) {
																																	$row["descritivo"] = TraResultadosController::buscarTextoInclusaoDeResultado($row['idtipoteste']);
																																}

																																$row["descritivo"] = str_replace("&nbsp;", "", $row["descritivo"]);
																																$row["descritivo"] = preg_replace('/<P[^>]*>\s*?<\/P[^>]*>/', '', $row["descritivo"]);
																																$row["descritivo"] = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $row["descritivo"]);
																																$row["descritivo"] = preg_replace('/(<[^>]+) align=".*?"/i', '$1', $row["descritivo"]);
																																echo $row["descritivo"];



																																if ($row['modelo'] == 'DINÂMICO') {
																																	$arrResultadosJson = json_decode($row["jsonresultado"], true);
																																	$arrResultadosJson[0]->name;
																																	$z = 0;
																																	$vindice = '';
																																	$x = 0;
																																	$validaId = 0;
																																	foreach ($arrResultadosJson as $key => $val) {
																																		if ($key == 'INDIVIDUAL') {
																																			foreach ($val as $k => $v) {
																																				if ($v['titulo'] == "ID") {
																																					if ($v['value'] == "") {
																																						$validaId = 0;
																																					} else {
																																						$validaId = 1;
																																						break;
																																					}
																																				}
																																			}
																																		}
																																	}

																																	foreach ($arrResultadosJson as $key1 => $value1) {
																																		if ($key1 == 'INDIVIDUAL') {
																																			foreach ($value1 as $k => $v) {
																																				$group = explode('_', $v['name']);
																																				$h = $group[2];
																																				$group = $group[0];
																																				if ($v['titulo'] == "ID" && $validaId == 0) {
																																				} else {
																																					if (empty($campocalc) && $v['calculoop'] == "Y") {
																																						$campocalc = $v['value'];
																																					}
																																					$dinamicoindividual['header'][$h] = $v['titulo'];
																																				}


																																				switch ($v['type']) {
																																					case 'date':
																																						$dinamicoindividual[$group][$h] = dma($v['value']);
																																						break;
																																					case 'checkbox':
																																						if ($v['value'] == 1) {
																																							$dinamicoindividual[$group][$h] = 'Sim';
																																						} else {
																																							$dinamicoindividual[$group][$h] = 'Não';
																																						}

																																						break;
																																					default:

																																						$dinamicoindividual[$group][$h] = $v['value'];
																																						break;
																																				}
																																			}
																																			$z++;
																																		} else {
																																			foreach ($value1 as $k => $v) {
																																				switch ($v['type']) {
																																					case 'date':
																																						$dinamicoagrupado[$x]['value'] = dma($v['value']);
																																						break;
																																					case 'checkbox':
																																						if ($v['value'] == 1) {
																																							$dinamicoagrupado[$x]['value'] = 'Sim';
																																						} else {
																																							$dinamicoagrupado[$x]['value'] = 'Não';
																																						}
																																						break;
																																					default:
																																						$dinamicoagrupado[$x]['value'] = $v['value'];
																																						break;
																																				}

																																				$dinamicoagrupado[$x]['header'] = $v['titulo'];
																																				$x++;
																																			}
																																		}
																																	}

																																?>
																																	<? if (!empty($dinamicoindividual['header'])) { ?>
																																		<table style="width:100%; background-color:#f7f7f7; height:14px; vertical-align:middle;text-transform:none;" class="trelisa ">
																																			<thead>
																																				<tr>
																																					<?
																																					$z = 0;
																																					$cab = [];

																																					foreach ($dinamicoindividual['header'] as $key1 => $value1) {
																																						if ($value1 == "ID" && $validaId == 0) {
																																					?>

																																						<?
																																						} else {
																																						?>
																																							<td style="  flex-grow: 1; font-weight:bold;"><?= $value1; ?></td>
																																					<?
																																						}

																																						$cab[$z] = $key1;
																																						$z++;
																																					}
																																					unset($dinamicoindividual['header']);

																																					?>
																																				</tr>
																																			</thead>
																																			<tbody>
																																				<?
																																				$z = 0;
																																				foreach ($dinamicoindividual as $key1 => $value1) { ?>
																																					<tr>
																																						<? $r = 0;
																																						while ($r < count($cab)) {
																																							if ($dinamicoindividual['header'] == 'ID' && $validaId != 1) {
																																						?>

																																							<?
																																							} else {
																																							?>
																																								<td style="  flex-grow: 1;"><?= $value1[$cab[$r]]; ?></td>
																																						<?
																																							}

																																							$r++;
																																						}
																																						?>

																																					</tr>
																																				<? $z++;
																																				}

																																				?>
																																			</tbody>
																																		</table>
																																	<? } ?>
																																<?
																																	$z = 0;
																																	if (count($dinamicoagrupado) > 0) {
																																		$tabela .= '<br><table style="width:100%;">';
																																	}
																																	foreach ($dinamicoagrupado as $key1 => $value1) {
																																		$tabela .= '<tr><td style="width:74px;white-space:nowrap;"><b>' . $value1['header'] . ':</b></td><td>' . $value1['value'] . '</td></tr>';
																																	}
																																	foreach ($arrResultadosJson as $key1 => $value1) {
																																		foreach ($value1 as $k => $v) {

																																			if ($v['calculo'] == "SIM") {

																																				if ($tipogmt == "GMT") {
																																					$tabela .= "<tr><td class='text-center' style='flex-grow: 1;'><b>MÉDIA GEOMÉTRICA DOS TÍTULOS:</b></td> <td>" . $row["gmt"] . "</td></tr>";
																																					break;
																																				} else if ($tipogmt == "ART") {
																																					$tabela .= "<tr><td class='text-center' style='flex-grow: 1;'><b>MÉDIA ARITMÉTICA:</b></td> <td>" . $row["gmt"] . "</td></tr>";
																																					break;
																																				} else if ($tipogmt == "PERC") {
																																					$tabela .= "<tr><td class='text-center' style='flex-grow: 1;'><b>PERCENTUAL " . $campocalc . ":</b></td> <td>" . $row["gmt"] . "%</td></tr>";
																																					break;
																																				}
																																			}
																																		}
																																	}
																																	if (count($dinamicoagrupado) > 0) {
																																		$tabela .= '</table>';
																																	}
																																	if ((empty($dinamicoindividual) and !empty($dinamicoagrupado)) or (!empty($dinamicoagrupado))) {
																																		echo $tabela;
																																	}
																																	//echo $tabela;
																																	unset($dinamicoindividual);
																																	unset($dinamicoagrupado);
																																} elseif ($modo == 'IND') {
																																	if ($prodservcongelada == true) {
																																		//mcc - 28/11/2018 - pegar a configuração da prodserv congelada 
																																		$c = 1;

																																		foreach ($row["prodservtipoopcao"] as $i => $linhad) {

																																			if ($linhad['valor'] == '0.0') {
																																				$linhad['valor'] = 0;
																																			}
																																			$strind[$c] = $linhad['valor'];
																																			$c++;
																																		}
																																	} else {

																																		$prodservTipoOpcaoValor = TraResultadosController::buscarValorProdservTipoOpcao($row['idtipoteste']);

																																		foreach ($prodservTipoOpcaoValor as $key => $rowi) {

																																			if ($rowi['valor'] == '0.0') {
																																				$rowi['valor'] = 0;
																																			}

																																			$strind[$y] = $rowi['valor'];
																																			$y++;
																																		}
																																	}

																																	$identificacaoResultado = TraResultadosController::buscarIdentificacaoResultado($row['idresultado']);

																																	$y = 1;
																																	$total = sizeof($resind);

																																	foreach ($identificacaoResultado as $key => $rowind) {
																																		if ($y > ($total / 2)) {
																																			echo '</ul>';
																																			$y = 1;
																																		}
																																		if ($y == 1) {
																																			echo '<ul style="width:40%;vertical-align:top; margin-bottom:0px; padding-left:12px; float:left; margin-top:0px;">';
																																		}

																																		if (!empty($rowind['identificacao'])) {

																																			if ($tipogmt == 'GMT') {
																																				echo "<li>Amostra " . $rowind['identificacao'] . " apresentou título " . $strind[$rowind['resultado']] . "</li>";
																																			} elseif ($tipogmt == 'ART') {
																																				echo "<li>Amostra " . $rowind['identificacao'] . " pesou " . $rowind['resultado'] . " (GR)</li>";
																																			} else {
																																				$arrrotulo[$y]     = $strind[$rowind['resultado']];
																																				echo "<li>Amostra " . $rowind['identificacao'] . " apresentou resultado " . $arrrotulo[$y] . "</li>";
																																				$y++;
																																			}
																																		} else {
																																			echo "<li>Amostra " . $rowind['identificacao'] . " apresentou resultado " . $rowind['resultado'] . "</li>";
																																		}
																																		$y++;
																																	}
																																	if ($tipogmt == "GMT") {
																																		echo "<li>Média Geométrica dos títulos: " . $row["gmt"] . "</li>";
																																	} else if ($tipogmt == "ART") {
																																		echo "<li>Média Aritmética: " . $row["gmt"] . "</li>";
																																	}

																																	if ($y > 0) {
																																		echo '</ul>';
																																	}
																																} else if ($modo == 'AGRUP') {

																																	for ($i = 1; $i <= 13; $i++) { //roda nos 13 orificios

																																		//se o oficio foi marcado alguma vez
																																		if ($row["q" . $i] > 0) {


																																			if ($row["q" . $i] > 1) {
																																				echo "<li>" . $row["q" . $i] . " Amostras apresentaram título " . $rot[$i];
																																			} else {
																																				echo "<li>" . $row["q" . $i] . " Amostra apresentou título " . $rot[$i];
																																			}


																																			$y++;
																																		}
																																	}
																																	if ($tipogmt == "GMT") {
																																		echo "<li>Média Geométrica dos títulos: " . $row["gmt"] . "</li>";
																																	} else if ($tipogmt == "ART") {
																																		echo "<li>Média Aritmética: " . $row["gmt"] . "</li>";
																																	}
																																}

																																?>
																																<?

																																if (($z % 50) == 0 and $z > 0) {
																																	echo '</ul><div style="page-break-before: always; "></div><ul style="width:100%; margin-top:0px; margin-bottom:0px;padding-left:12px;">';
																																?>
																																<?
																																}

																																?>

																																<?
																																if (!empty($frasepronta) and !empty($rot["msgmin"]) and $row["geralegenda"] == 'Y') {
																																?>
																																	<li>

																																		<?= ($frasepronta) ?>
																																	</li>
																																<?
																																}
																																//INCLUIDO A FRASE PARA AVES QUE ESTÃO COM TITULO ACIMA DO MAXIMO
																																if (!empty($frasepronta2) and !empty($rot["msgmax"]) and $row["geralegenda"] == 'Y') {
																																?>
																																	?>
																																	?>
																																	<li>
																																		<?= ($frasepronta2) ?>
																																	</li>
																																<?
																																}

																																?>
																																</ul>

																															</div>
																														</fieldset>
																													<? } ?>
																										</table>
																									</td>
																								</tr>
																							</table>
																						</td>
																					</tr>
																				</table>
																			</td>
																		</tr>
																	<? } else { ?>
																		<tr>
																			<td colspan="6" class="">
																				<table class="tsep" style="width:100%; margin-top:0px;">
																					<!-- Cabecalho Superior -->
																					<tr>
																						<td style="width:100%">
																							<table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
																								<tr style="background-color:#f7f7f7; font-size:11x; text-transform:uppercase;	height:20px;">
																									<td idresultado="<?= $row["idresultado"] ?>" style="font-size:11px;width: 90%;"><b><?= $rowa["descr"] ?></b></td>
																									<td style="width: 10%;"><b style=" FONT-SIZE: 8PX;  TEXT-DECORATION: NONE;">EM ANÁLISE</b></td>
																								</tr>
																							</table>
																						</td>
																					</tr>
																				</table>
																			</td>
																		</tr>
																	<? } ?>
															<?
																}
															} ?>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>

							</td>
						</tr>

						<?

						if ($possuiAntibiograma !== false) { ?>
							<tr>
								<td>
									<div class="quebrapagina"></div>
									<table class="tsep" style="width:100%;">
										<tr>
											<td>
												<table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
													<tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase; height:20px;">
														<td colspan="6" style="font-size:11px;">ANTIBIOGRAMAS</td>
													</tr>


													<? foreach ($dadosResultadosAmostra as $key => $rowa) {
														if ($rowa['relatorioantibiograma'] == 'Y') {

															if ($rowa['jresultado']) {
																$row = TraResultadosController::criarArrayDadosJsonAmostra($rowa['jsonResultado']);
																$arrResultadosJson = json_decode($row["jsonresultado"], true);
																$tipoDeAmostra = $arrResultadosJson['AGRUPADO']['0']['value'];
																$agente = $arrResultadosJson['AGRUPADO']['1']['value'];
													?>


																<tr>
																	<td class="" colspan="6">
																		<table class="tsep" style="width:100%; margin-top:0px;">
																			<!-- Cabecalho Superior -->
																			<tr>
																				<td style="width:100%">
																					<table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
																						<tr style="background-color:#f7f7f7; font-size:11x; text-transform:uppercase;	height:20px;">
																							<td idresultado="<?= $row["idresultado"] ?>" style="font-size:11px;width: 90%;"><b><?= $rowa["descr"] . ' - ' . $agente . ' (' . $tipoDeAmostra . ')' ?></b> </td>
																							<td style="width: 10%;"><a class="no-print" style=" FONT-SIZE: 8PX;  TEXT-DECORATION: NONE;" title="LDA Detalhado" href="emissaoresultado.php?idresultado=<?= $rowa["idresultado"] ?>">Ver Mais</a></td>
																						</tr>

																					</table>
																					<table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
																						<tr>
																							<td class="tdval grval">
																								<br>
																								<table style="width:100%; text-transform:none;">
																									<tr>
																										<? if ($row['modelo'] == 'DINÂMICO' && empty($urlimg)) {
																											echo '<td style="vertical-align:top;width:100%;">';
																										} else if (empty($urlimg)) { ?>
																											<td style="vertical-align:top;width:100%">
																											<? } else { ?>
																											<td style="vertical-align:top;width:64%">
																											<? } ?>

																											<fieldset class="fset" style="border:none;">
																												<div class="resdesc" id="resm" style="vertical-align:top">

																													<?

																													if ($row['modelo'] == 'DINÂMICO') {

																														$arrResultadosJson[0]->name;
																														$z = 0;
																														$vindice = '';
																														$x = 0;
																														$validaId = 0;

																														foreach ($arrResultadosJson as $key => $val) {
																															if ($key == 'INDIVIDUAL') {
																																foreach ($val as $k => $v) {
																																	if ($v['titulo'] == "ID") {

																																		if ($v['value'] == "") {
																																			$validaId = 0;
																																		} else {
																																			$validaId = 1;
																																			break;
																																		}
																																	}
																																}
																															}
																														}

																														foreach ($arrResultadosJson as $key1 => $value1) {

																															if ($key1 == 'INDIVIDUAL') {

																																foreach ($value1 as $k => $v) {
																																	$group = explode('_', $v['name']);
																																	$h = $group[2];
																																	$group = $group[0];
																																	if ($v['titulo'] == "ID" && $validaId == 0) {
																																	} else {
																																		if (empty($campocalc) && $v['calculoop'] == "Y") {
																																			$campocalc = $v['value'];
																																		}
																																		$dinamicoindividual['header'][$h] = $v['titulo'];
																																	}


																																	switch ($v['type']) {
																																		case 'date':
																																			$dinamicoindividual[$group][$h] = dma($v['value']);
																																			break;
																																		case 'checkbox':
																																			if ($v['value'] == 1) {
																																				$dinamicoindividual[$group][$h] = 'Sim';
																																			} else {
																																				$dinamicoindividual[$group][$h] = 'Não';
																																			}

																																			break;
																																		default:


																																			$dinamicoindividual[$group][$h] = $v['value'];
																																			break;
																																	}
																																}
																																$z++;
																															} else {
																																foreach ($value1 as $k => $v) {
																																	switch ($v['type']) {
																																		case 'date':
																																			$dinamicoagrupado[$x]['value'] = dma($v['value']);
																																			break;
																																		case 'checkbox':
																																			if ($v['value'] == 1) {
																																				$dinamicoagrupado[$x]['value'] = 'Sim';
																																			} else {
																																				$dinamicoagrupado[$x]['value'] = 'Não';
																																			}
																																			break;
																																		default:
																																			$dinamicoagrupado[$x]['value'] = $v['value'];
																																			break;
																																	}

																																	$dinamicoagrupado[$x]['header'] = $v['titulo'];
																																	$x++;
																																}
																															}
																														}

																													?>

																														<table style="width:100%; background-color:#f7f7f7; height:14px; vertical-align:middle;text-transform:none;" class="trelisa ">
																															<thead>
																																<tr>
																																	<?
																																	$z = 0;
																																	$cab = [];


																																	foreach ($dinamicoindividual['header'] as $key1 => $value1) {
																																		if ($value1 == "ID" && $validaId == 0) {
																																	?>

																																		<?
																																		} else {
																																		?>
																																			<td style="  flex-grow: 1; font-weight:bold;"><?= $value1; ?></td>
																																	<?
																																		}

																																		$cab[$z] = $key1;
																																		$z++;
																																	}
																																	unset($dinamicoindividual['header']);

																																	?>
																																</tr>
																															</thead>
																															<tbody>
																																<?
																																$z = 0;
																																foreach ($dinamicoindividual as $key1 => $value1) { ?>
																																	<tr>
																																		<? $r = 0;
																																		while ($r < count($cab)) {
																																			if ($dinamicoindividual['header'] == 'ID' && $validaId != 1) {
																																		?>

																																			<?
																																			} else {
																																			?>
																																				<td style="  flex-grow: 1;"><?= $value1[$cab[$r]]; ?></td>
																																		<?
																																			}


																																			$r++;
																																		}
																																		?>

																																	</tr>
																																<? $z++;
																																}

																																?>
																															</tbody>
																														</table>
																													<?
																														$z = 0;
																														if (count($dinamicoagrupado) > 0) {
																															$tabela .= '<br><table style="width:100%;">';
																														}
																														foreach ($dinamicoagrupado as $key1 => $value1) {
																															$tabela .= '<tr><td style="width:74px;white-space:nowrap;"><b>' . $value1['header'] . ':</b></td><td>' . $value1['value'] . '</td></tr>';
																														}
																														foreach ($arrResultadosJson as $key1 => $value1) {
																															foreach ($value1 as $k => $v) {

																																if ($v['calculo'] == "SIM") {

																																	if ($tipogmt == "GMT") {
																																		$tabela .= "<tr><td class='text-center' style='flex-grow: 1;'><b>MÉDIA GEOMÉTRICA DOS TÍTULOS:</b></td> <td>" . $row["gmt"] . "</td></tr>";
																																		break;
																																	} else if ($tipogmt == "ART") {
																																		$tabela .= "<tr><td class='text-center' style='flex-grow: 1;'><b>MÉDIA ARITMÉTICA:</b></td> <td>" . $row["gmt"] . "</td></tr>";
																																		break;
																																	} else if ($tipogmt == "PERC") {
																																		$tabela .= "<tr><td class='text-center' style='flex-grow: 1;'><b>PERCENTUAL " . $campocalc . ":</b></td> <td>" . $row["gmt"] . "%</td></tr>";
																																		break;
																																	}
																																}
																															}
																														}
																														if (count($dinamicoagrupado) > 0) {
																															$tabela .= '</table>';
																														}

																														//echo $tabela;
																														unset($dinamicoindividual);
																														unset($dinamicoagrupado);
																													}

																													?>
																													<?

																													if (($z % 50) == 0 and $z > 0) {
																														echo '</ul><div style="page-break-before: always; "></div><ul style="width:100%; margin-top:0px; margin-bottom:0px;padding-left:12px;">';
																													?>
																													<?
																													}

																													?>

																													<?
																													if (!empty($frasepronta) and !empty($rot["msgmin"]) and $row["geralegenda"] == 'Y') {
																													?>
																														<li>

																															<?= ($frasepronta) ?>
																														</li>
																													<?
																													}
																													//INCLUIDO A FRASE PARA AVES QUE ESTÃO COM TITULO ACIMA DO MAXIMO
																													if (!empty($frasepronta2) and !empty($rot["msgmax"]) and $row["geralegenda"] == 'Y') { ?>
																														<li>
																															<?= ($frasepronta2) ?>
																														</li>
																													<?
																													}

																													?>
																													</ul>

																												</div>
																											</fieldset>
																								</table>

																							</td>
																						</tr>
																					</table>
																				</td>
																			</tr>
																		</table>
																	</td>
																</tr>
															<? } else { ?>
																<tr>
																	<td colspan="6" class="">
																		<table class="tsep" style="width:100%; margin-top:0px;">
																			<!-- Cabecalho Superior -->
																			<tr>
																				<td style="width:100%">
																					<table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
																						<tr style="background-color:#f7f7f7; font-size:11x; text-transform:uppercase;	height:20px;">
																							<td idresultado="<?= $row["idresultado"] ?>" style="font-size:11px;width: 90%;"><b><?= $rowa["descr"] ?></b></td>
																							<td style="width: 10%;"><b style=" FONT-SIZE: 8PX;  TEXT-DECORATION: NONE;">EM ANÁLISE</b></td>
																						</tr>
																					</table>
																				</td>
																			</tr>
																		</table>
																	</td>
																</tr>
															<? } ?>
														<? } ?>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
					</table>
				</td>
			</tr>
	<? }
												} ?>
	</table>
	</td>
	</tr>
	</tbody>
	</table>
	<hr>
	<?
	if ($dadosAmostra["status"] == "ASSINADO") {

		$resass = TraResultadosController::buscarAssinaturaTraResultado($dadosAmostra["idamostra"]);

		foreach ($resass  as $key => $rowass) {
			$nomresp = "";
			$crmvresp = "";
			if ($rowass["idpessoa"] != 797 or $rowass["idpessoa"] != 782) {
				$rowass["idpessoa"] = 797;
			}
			$respidpessoa = $rowass["idpessoa"];
			//troca dados do responsavel via hardcode
			switch ($rowass["idpessoa"]) {
				case 782: //edison
					$nomresp = "Edison Rossi";
					$crmvresp = "MG N&ordm; 1626";;
					break;
				case 797: //marcio
					$nomresp = "Marcio Danilo Botrel Coutinho";
					$crmvresp = "MG N&ordm; 1454";;
				default:
					null;
					break;
			}

			if ($dadosAmostra["dataamostrah"] >= '2021-05-18 00:00:01' and ($_SESSION["SESSAO"]["IDEMPRESA"] == 1 || $_SESSION["SESSAO"]["IDEMPRESA"] == 2)) {
				$nomresp = "José Renato de O. Branco";
				$crmvresp = "MG N&ordm; 19770";
				$respidpessoa = 5655;
			}
		}
	?>
		<div class="row">
			<div class="col 15 rot">Técnico Resp.:</div>
			<div class="col 25"> <?= $nomresp ?></div>
			<div class="col 10 rot">CRMV:</div>
			<div class="col "><?= $crmvresp ?></div>
			<div class="col 15 rot">Assinatura.:</div>
			<div class="col "><img style="position: relative; top: 13px;" src="../inc/img/sig<?= strtolower(trim($respidpessoa)) ?>.gif"></div>
		</div>
	<? } ?>
</body>

</html>