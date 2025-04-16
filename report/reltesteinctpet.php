<meta charset="UTF-8" />
<link href="<?= $_GET['gerapdf']=='Y'?'/var/www/carbon8':''; ?>/inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />
<style type="text/css" media="print">
	@media print {
		body {
			-webkit-print-color-adjust: exact;
		}

		a[href]:after {
			content: none !important;
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

	table span:not(.resdesc span) {
		font-size: 6px !important;
	}

	.relisa {
		width: 10.1% !important;
		display: inline-block;
	}

	.MsoTableGrid {
		width: 100%;
	}

	table {
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
		/* border:1px solid #e1e1e1; */
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

	
</style>
<style type="text/css">
	table {
		page-break-inside: auto;
		width: 100%
	}

	tr {
		/* page-break-inside: avoid; */
		page-break-after: auto;
	}
	
	tr.cabecalho_superior {
		page-break-after: avoid !important;
	}

	thead {
		display: table-header-group
	}

	tfoot {
		display: table-footer-group
	}

	@media print {
		.no-print,
		.no-print * {
			display: none !important;
		}

	}
</style>
<?
//maf: classes que variam em caso de geração de PDF devem ser colocadas aqui
if ($_GET["gerapdf"] == "Y") {
?>
	<style>
		.screen {
			display: none !important;
		}
	</style>
<?
}

use Com\Tecnick\Barcode\Barcode;

include_once("../inc/php/jpgraph/grafelisa.php");
include_once("../inc/php/jpgraph/grafelisagmt.php");

function teaser($html)
{
	$html = str_replace('&nbsp;', ' ', $html);
	do {
		$tmp = $html;
		$html = preg_replace(
			'#<([^ >]+)[^>]*>[[:space:]]*</\1>#',
			'',
			$html
		);
	} while ($html !== $tmp);

	return $html;
}
/*
 *maf09082011: escreve no cabecalho as informacoes de controle da impressao
 */
function imppaginisup()
{
	//invoca para dentro deste contexto desta funcao as variaveis ja existentes fora dela. Isto permite que os valores da query do relatorio sejam utilizados aqui
	global $ipage;
	global $pb;
	global $codepress;
	global $irestotal;
	global $row;
	global $rowp;
	global $mostracabecalho;

	$ipage++;
	$paginaatual = $ipage;

	//MAF: Mostrar texto com versoes da Aplicacao e Banco de dados, para auditorias externas e LGPD
	$versionamento = empty($row["versionamento"]["versaosoft"]) ? "" : "Software:".$row["versionamento"]["versaosoft"];
	$versionamento .= empty($row["versionamento"]["versaodb"]) ? "" : "/Database:".$row["versionamento"]["versaosoft"];
	$versionamento = $versionamento == "" ? "" : " Versões[".$versionamento."]; ";

	/*
	* maf060211: mostrar cabecalhos a usuarios que nao sao do tipo funcionario 
	*  
	*/

	if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] != 1 or ($rowp["visualizares"] == 'Y' and $mostracabecalho != "N")) {

		if ($_SERVER["SCRIPT_NAME"] == "/report/enviaemailoficial_emissaogerapdf.php" || $_SERVER["SCRIPT_NAME"] == "/tmp/reenvioemailoficialreq.php" || $_SERVER["SCRIPT_NAME"] == "/report/enviaemailresultado_contatoempresa.php") {
			$displaynone = "display:none;";
		}
		?>
		<table style="<?= $pb ?>;width:700px !important; margin:auto;  ">
			<thead>
				<tr style="position:relative;">
					<td>
						<div class="screen" style="position:fixed; right:0px; top:4px;z-index:9999999;<?= $displaynone ?>">
							<a href="#" class="no-print" onclick="window.print();return false;">
								<div onmouseover="this.style.opacity=1;" onmouseout="this.style.opacity=0.6;" style="opacity:0.6;float:left;   top: 10%;  width: 8em;
								margin-top: -2.5em;
								background: #666;
								height: 32px;
								font-size: 12px !important;
								text-align: center;
								line-height: 20px;    margin: 0px 2px;
								color: #fff;"><img src="../inc/img/print_white_192x192.png" style="height:20px;position:relative;top:5px;<?= $displaynone ?>">Imprimir</div>
							</a>
							<?
							$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
							$full_url = $protocol."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

							?>
							<a href="<?= $full_url ?>&csv=1" class="no-print">
								<div onmouseover="this.style.opacity=1;" onmouseout="this.style.opacity=0.6;" style="opacity:0.6;float:left;top: 10%;  width: 12em;
								margin-top: -2.5em;
								background: #666;
								height: 32px;
								font-size: 12px !important;
								text-align: center;
								line-height: 20px; margin: 0px 2px;
								color: #fff;<?= $displaynone ?>"><img src="../inc/img/csv-icon.png" style="height:20px;position:relative;top:5px;<?= $displaynone ?>">DOWNLOAD CSV</div>
							</a>
							<a href="<?= $full_url ?>&gerapdf=Y" style="display:none" class="no-print">
								<div onmouseover="this.style.opacity=1;" onmouseout="this.style.opacity=0.6;" style="opacity:0.6;float:left;    top: 10%;  width: 12em;
								margin-top: -2.5em;								
								background: #666;
								height: 32px;
								font-size: 12px !important;
								text-align: center;
								line-height: 20px; margin: 0px 2px;
								color: #fff;<?= $displaynone ?>"><img src="../inc/img/pdf-icon.png" style="height:20px;position:relative;top:5px;<?= $displaynone ?>">DOWNLOAD PDF</div>
							</a>
						</div>
						<?
						//maf050719: a url original sera transformada em base64 para permitir embutir a emissaoresultado na geracao de PDF
						//$imgurl="../inc/img/cabecalho-relatorio-de-ensaio.png";
						if ($row["idempresa"] != 1 || ($row["dataamostra"] >= '2021-05-18 00:00:01' and  ($row["idempresa"] == 1 || $row["idempresa"] == 2) and $row["idunidade"] == 9)) {

							if ($row["dataamostra"] >= '2021-05-18 00:00:01' and  ($row["idempresa"] == 1 || $row["idempresa"] == 2) and $row["idunidade"] == 9) {
								$sqlimagem = "SELECT caminho FROM empresaimagem WHERE tipoimagem = 'HEADERSERVICO' and idempresa = 2";
							} else {
								$sqlimagem = "SELECT caminho FROM empresaimagem WHERE tipoimagem = 'HEADERSERVICO' and idempresa = ".$row["idempresa"];
							}
							$resimagem = mysql_query($sqlimagem) or die("Erro ao buscar imagem do relatório: ".$sqlimagem);
							$rowimagem = mysql_fetch_assoc($resimagem);
							//$rowimagem["caminho"] =  $rowimagem["caminho"];
							if (defined('STDIN')) {
								$rowimagem["caminho"] = str_replace("../", "/var/www/carbon8/", $rowimagem["caminho"]);
								$imgurl = $rowimagem["caminho"];
							} else {
								$imgurl = $rowimagem["caminho"];
							}
						} else {
							if (defined('STDIN')) {
								$imgurl = "/var/www/carbon8/inc/img/cabecalho-relatorio-de-ensaio.png";
							} else {
								$imgurl = "../inc/img/cabecalho-relatorio-de-ensaio.png";
							}
						}
						//$imgurl="../inc/img/cabecalho-relatorio-de-ensaio.png";
						//Transformacao em base64: o arquivo fica maior, mas permite a geração de PDF pelo dompdf
						if (
							$_SERVER["SCRIPT_NAME"] == "/report/enviaemailoficial_emissaogerapdf.php"
							or $_SERVER["SCRIPT_NAME"] == "/tmp/reenvioemailoficialreq.php"
							or $_SERVER["SCRIPT_NAME"] == "/report/enviaemailresultado_contatoempresa.php"
						) {
							$imgurl = "data:image/png;base64,".base64_encode(file_get_contents($imgurl));
						}
						?>
						<div>
							<table class="cabtxt" style="width:100%;position:relative; z-index:2; background-color:#fff; margin-bottom:0px; background-position: left; background-size: cover; border: 1px solid #eee; border-radius: 7px 0px 0px 0px; border-right:none;background-repeat:no-repeat;	">

								<tr>
									<td style="width:500px; height:127px;	">
										<img src="<?= $imgurl ?>" alt="Logo">
									</td>
									<td style="border: none; border-radius: 0px 7px 0px 0px;  border-left:none; text-align:right;vertical-align:bottom">
										<?
										if ($row["logoinmetro"] == 'Y' and !empty($row['idsecretaria'])) {
										?>
											<img src="../inc/img/selo-inmetron.png" border="0" style="height: 116px">
										<?
										}
										?>
									</td>
									<!-- <td style="width:171px;"  align="right">Rod. BR 365, KM 615 - B. Conj Alvorada<br>CEP 38407-180 - Uberl&acirc;ndia - MG<br>laudolab@laudolab.com.br<br>www.laudolab.com.br<br>Fone/Fax: (34) 3222-5700</td>-->
								</tr>

							</table>
						</div>
					</td>
				</tr>
			</thead>

		<?
		$pb = "";
	} else {
		?>
			<table style="<?= $pb ?>;width:700px !important; margin:auto;  ">
				<thead>
					<tr style="position:relative;">
						<td>
							<a href="#" class="no-print" onclick="window.print();return false;">
								<div>Imprimir</div>
							</a>
							<div>
								<table class="cabtxt" style="width:100%;position:relative; z-index:2; background-color:#fff; margin-bottom:0px;">
									<tr>
										<td style="width:498px; height:141px; ">
											&nbsp;
										</td>
										<td style="border: none; border-radius: 0px 7px 0px 0px;  border-left:none; text-align:left; ">
											<?
											if ($row["logoinmetro"] == 'Y') {
											?>
												<img src="../inc/img/selo-inmetron.png" border="0" style="height: 116px">
											<?
											}
											?>
										</td>
										<!-- <td style="width:171px;"  align="right">Rod. BR 365, KM 615 - B. Conj Alvorada<br>CEP 38407-180 - Uberl&acirc;ndia - MG<br>laudolab@laudolab.com.br<br>www.laudolab.com.br<br>Fone/Fax: (34) 3222-5700</td>-->
									</tr>

								</table>
							</div>
						</td>
					</tr>
				<? }
				?>
				<tr>
					<td>
						<div class="" style="width:700px; position:relative; z-index:111111; margin:auto; left:0; right:0; margin: 0 auto; text-align: right; padding-bottom:10px;">
							<div style="width: 700px;position:absolute; top:-3px; border-bottom:none;border-top: 1px solid gray; border-top-style: dotted;margin: auto; text-transform:uppercase; ">
								Imp.: <?= $codepress ?>; <?= $versionamento ?> Início pg. p/ [<?= $row["sigla"] ?>] reg.: [<?= $row["idregistro"] ?>];
							</div>
						</div><!-- Controle Impressao -->
					</td>
				</tr>
				</thead>
			<?
			$pb = "page-break-before: always;";
		}
function imppaginisup1()
{
	//invoca para dentro deste contexto desta funcao as variaveis ja existentes fora dela. Isto permite que os valores da query do relatorio sejam utilizados aqui
	global $ipage;
	global $pb;
	global $codepress;
	global $irestotal;
	global $row;
	global $rowp;
	global $mostracabecalho;

	$ipage++;
	$paginaatual = $ipage;

	//MAF: Mostrar texto com versoes da Aplicacao e Banco de dados, para auditorias externas e LGPD
	$versionamento = empty($row["versionamento"]["versaosoft"]) ? "" : "Software:".$row["versionamento"]["versaosoft"];
	$versionamento .= empty($row["versionamento"]["versaodb"]) ? "" : "/Database:".$row["versionamento"]["versaosoft"];
	$versionamento = $versionamento == "" ? "" : " Versões[".$versionamento."]; ";

	/*
	* maf060211: mostrar cabecalhos a usuarios que nao sao do tipo funcionario 
	*  
	*/

	if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] != 1 or ($rowp["visualizares"] == 'Y' and $mostracabecalho != "N")) {

		if ($_SERVER["SCRIPT_NAME"] == "/report/enviaemailoficial_emissaogerapdf.php" || $_SERVER["SCRIPT_NAME"] == "/tmp/reenvioemailoficialreq.php" || $_SERVER["SCRIPT_NAME"] == "/report/enviaemailresultado_contatoempresa.php") {
			$displaynone = "display:none;";
		}
		?>
		<table style="<?= $pb ?>">
			<thead>
				<tr style="position:relative;">
					<td>
						<div class="screen" style="position:fixed; right:0px; top:4px;z-index:9999999;<?= $displaynone ?>">
							<a href="#" class="no-print" onclick="window.print();return false;">
								<div onmouseover="this.style.opacity=1;" onmouseout="this.style.opacity=0.6;" style="opacity:0.6;float:left;   top: 10%;  width: 8em;
								margin-top: -2.5em;
								background: #666;
								height: 32px;
								font-size: 12px !important;
								text-align: center;
								line-height: 20px;    margin: 0px 2px;
								color: #fff;"><img src="../inc/img/print_white_192x192.png" style="height:20px;position:relative;top:5px;<?= $displaynone ?>">Imprimir</div>
							</a>
							<?
							$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
							$full_url = $protocol."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

							?>
							<a href="<?= $full_url ?>&csv=1" class="no-print">
								<div onmouseover="this.style.opacity=1;" onmouseout="this.style.opacity=0.6;" style="opacity:0.6;float:left;top: 10%;  width: 12em;
								margin-top: -2.5em;
								background: #666;
								height: 32px;
								font-size: 12px !important;
								text-align: center;
								line-height: 20px; margin: 0px 2px;
								color: #fff;<?= $displaynone ?>"><img src="../inc/img/csv-icon.png" style="height:20px;position:relative;top:5px;<?= $displaynone ?>">DOWNLOAD CSV</div>
							</a>
							<a href="<?= $full_url ?>&gerapdf=Y" style="display:none" class="no-print">
								<div onmouseover="this.style.opacity=1;" onmouseout="this.style.opacity=0.6;" style="opacity:0.6;float:left;    top: 10%;  width: 12em;
								margin-top: -2.5em;								
								background: #666;
								height: 32px;
								font-size: 12px !important;
								text-align: center;
								line-height: 20px; margin: 0px 2px;
								color: #fff;<?= $displaynone ?>"><img src="../inc/img/pdf-icon.png" style="height:20px;position:relative;top:5px;<?= $displaynone ?>">DOWNLOAD PDF</div>
							</a>
						</div>
						<?
						//maf050719: a url original sera transformada em base64 para permitir embutir a emissaoresultado na geracao de PDF
						//$imgurl="../inc/img/cabecalho-relatorio-de-ensaio.png";
						if ($row["idempresa"] != 1 || ($row["dataamostra"] >= '2021-05-18 00:00:01' and  ($row["idempresa"] == 1 || $row["idempresa"] == 2) and $row["idunidade"] == 9)) {

							if ($row["dataamostra"] >= '2021-05-18 00:00:01' and  ($row["idempresa"] == 1 || $row["idempresa"] == 2) and $row["idunidade"] == 9) {
								$sqlimagem = "SELECT caminho FROM empresaimagem WHERE tipoimagem = 'HEADERSERVICO' and idempresa = 2";
							} else {
								$sqlimagem = "SELECT caminho FROM empresaimagem WHERE tipoimagem = 'HEADERSERVICO' and idempresa = ".$row["idempresa"];
							}
							$resimagem = mysql_query($sqlimagem) or die("Erro ao buscar imagem do relatório: ".$sqlimagem);
							$rowimagem = mysql_fetch_assoc($resimagem);
							//$rowimagem["caminho"] =  $rowimagem["caminho"];
							if (defined('STDIN')) {
								$rowimagem["caminho"] = str_replace("../", "/var/www/carbon8/", $rowimagem["caminho"]);
								$imgurl = $rowimagem["caminho"];
							} else {
								$imgurl = $rowimagem["caminho"];
							}
						} else {
							if (defined('STDIN')) {
								$imgurl = "/var/www/carbon8/inc/img/cabecalho-relatorio-de-ensaio.png";
							} else {
								$imgurl = "../inc/img/cabecalho-relatorio-de-ensaio.png";
							}
						}
						//$imgurl="../inc/img/cabecalho-relatorio-de-ensaio.png";
						//Transformacao em base64: o arquivo fica maior, mas permite a geração de PDF pelo dompdf
						if (
							$_SERVER["SCRIPT_NAME"] == "/report/enviaemailoficial_emissaogerapdf.php"
							or $_SERVER["SCRIPT_NAME"] == "/tmp/reenvioemailoficialreq.php"
							or $_SERVER["SCRIPT_NAME"] == "/report/enviaemailresultado_contatoempresa.php"
						) {
							$imgurl = "data:image/png;base64,".base64_encode(file_get_contents($imgurl));
						}
						?>
						<div>
							<table class="cabtxt" style="width:100%;position:relative; z-index:2; background-color:#fff; margin-bottom:0px; background-position: left; background-size: cover; border: 1px solid #eee; border-radius: 7px 0px 0px 0px; border-right:none;background-repeat:no-repeat;	">

								<tr>
									<td style="border: none; border-radius: 0px 7px 0px 0px;  border-left:none; text-align:right;vertical-align:bottom">
										<?
										if ($row["logoinmetro"] == 'Y' and !empty($row['idsecretaria'])) {
										?>
											<img src="../inc/img/selo-inmetron.png" border="0" style="height: 116px">
										<?
										}
										?>
									</td>
									<!-- <td style="width:171px;"  align="right">Rod. BR 365, KM 615 - B. Conj Alvorada<br>CEP 38407-180 - Uberl&acirc;ndia - MG<br>laudolab@laudolab.com.br<br>www.laudolab.com.br<br>Fone/Fax: (34) 3222-5700</td>-->
								</tr>

							</table>
						</div>
					</td>
				</tr>
			</thead>								
		<?
		$pb = "";
	} else {
		?>
			<table style="<?= $pb ?>">
				<thead>
					
				<? }
				?>
				
				
				</thead>
			<?
			$pb = "page-break-before: always;";
		}


		/*
		*maf09082011: finaliza no rodape o controle da impressao
		*/
		function imppagrodape($inisubpage)
		{
			//invoca para dentro deste contexto desta funcao as variaveis ja existentes fora dela. Isto permite que os valores da query do relatorio sejam utilizados aqui
			global $codepress;
			global $ipage;
			global $irestotal;
			global $row;

			$paginaatual = $ipage;
			//

			?>
				<!-- INI: Rodape -->
				<tfoot>
					<tr>
						<td>
							<table style="width: 100%; top:-10px; position:relative;">
								<tr>
									<td>
										<table class="tsep" style="width:100%;">
											<tr>
												<td>
													<table class="tsep" style="width:100%;">
														<tr>
															<td style="width:100%">
																<table style="width:100%">
																	<tr>
																		<td>
																			<div class="nimptbot" style="width:100%; text-align:center;font-size:6px;">Imp: <?= $codepress ?>; Fim pg. p/ [<?= $row["sigla"] ?>] reg.: [<?= $row["idregistro"] ?>];</div>
																			<?

																			if ($row["dataamostra"] >= '2021-05-18 00:00:01' and  ($_SESSION["SESSAO"]["IDEMPRESA"] == 1 || $_SESSION["SESSAO"]["IDEMPRESA"] == 2) and $row["idunidade"] == 9) {

																				$sqlft = "SELECT razaosocial, cnpj, inscestadual, xlgr, nro, xbairro, cep, xmun, uf, DDDPrestador, TelefonePrestador, emailres FROM empresa WHERE idempresa = 2";
																			} else {

																				$sqlft = "SELECT razaosocial, cnpj, inscestadual, xlgr, nro, xbairro, cep, xmun, uf, DDDPrestador, TelefonePrestador, emailres FROM empresa WHERE idempresa = ".$row["idempresa"];
																			}
																			$resft = mysql_query($sqlft) or die("Erro ao buscar imagem do relatório: ".$sqlft);
																			$rowft = mysql_fetch_assoc($resft);
																			$razaosocial 		=  $rowft["razaosocial"];
																			$cnpj 				=  $rowft["cnpj"];
																			$inscestadual 		=  $rowft["inscestadual"];
																			$xlgr 				=  $rowft["xlgr"];
																			$nro 				=  $rowft["nro"];
																			$xbairro 			=  $rowft["xbairro"];
																			$cep 			=  $rowft["cep"];
																			$xmun 				=  $rowft["xmun"];
																			$uf 				=  $rowft["uf"];
																			$DDDPrestador 		=  $rowft["DDDPrestador"];
																			$TelefonePrestador 	=  $rowft["TelefonePrestador"];
																			$emailres 			=  $rowft["emailres"];

																			$cnpj_cpf = preg_replace("/\D/", '', $cnpj);

																			//if (strlen($cnpj_cpf) === 11) {
																			// $cnpj_cpf = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
																			//} 
																			$cnpj = substr($cnpj, 0, 2).'.'.substr($cnpj, 2, 3) .
																				'.'.substr($cnpj, 5, 3).'/' .
																				substr($cnpj, 8, 4).'-'.substr($cnpj, 12, 2);


																			?>
																			<div style="width:100%; text-align:center; background:#f7f7f7; padding-top:7px; padding-bottom:7px; line-height:12px;font-size:6px">
																				<? echo ('<span style="display:block; height: 10px; line-height: 10px;font-size:6px !important;">Local de realização das análises:</span>
																				<span style="display:block; height: 10px; line-height: 10px;font-size:6px !important;margin-bottom:4px">'.$razaosocial.'. CNPJ: '.$cnpj.' - I.E.: '.$inscestadual.'.</span>
																				<span style="display:block; height: 10px; line-height: 10px;font-size:6px !important;">'.$xlgr.', '.$nro.'°. '.$xbairro.'. CEP: '.$cep.' - '.$xmun.'-'.$uf.'. ('.$DDDPrestador.') '.$TelefonePrestador.' - '.$emailres.'</span>'); ?>

																			</div>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</tfoot>
			</table>
			<!-- FIM: Rodape -->
			<?
		}
		function imppagrodape1($inisubpage)
		{
			//invoca para dentro deste contexto desta funcao as variaveis ja existentes fora dela. Isto permite que os valores da query do relatorio sejam utilizados aqui
			global $codepress;
			global $ipage;
			global $irestotal;
			global $row;

			$paginaatual = $ipage;
			//

			?>
				<!-- INI: Rodape -->
				<tfoot>
					<tr>
						<td>
							<table style="width: 100%; top:-10px; position:relative;">
								<tr>
									<td>
										<table class="tsep" style="width:100%;">
											<tr>
												<td>
													<table class="tsep" style="width:100%;">
														<tr>
															<td style="width:100%">
																<table style="width:100%">
																	<tr>
																		<td>
																			<div class="nimptbot" style="width:100%; text-align:center;font-size:6px;">Imp: <?= $codepress ?>; Fim pg. p/ [<?= $row["sigla"] ?>] reg.: [<?= $row["idregistro"] ?>];</div>
																			<?

																			if ($row["dataamostra"] >= '2021-05-18 00:00:01' and  ($_SESSION["SESSAO"]["IDEMPRESA"] == 1 || $_SESSION["SESSAO"]["IDEMPRESA"] == 2) and $row["idunidade"] == 9) {

																				$sqlft = "SELECT razaosocial, cnpj, inscestadual, xlgr, nro, xbairro, cep, xmun, uf, DDDPrestador, TelefonePrestador, emailres FROM empresa WHERE idempresa = 2";
																			} else {

																				$sqlft = "SELECT razaosocial, cnpj, inscestadual, xlgr, nro, xbairro, cep, xmun, uf, DDDPrestador, TelefonePrestador, emailres FROM empresa WHERE idempresa = ".$row["idempresa"];
																			}
																			$resft = mysql_query($sqlft) or die("Erro ao buscar imagem do relatório: ".$sqlft);
																			$rowft = mysql_fetch_assoc($resft);
																			$razaosocial 		=  $rowft["razaosocial"];
																			$cnpj 				=  $rowft["cnpj"];
																			$inscestadual 		=  $rowft["inscestadual"];
																			$xlgr 				=  $rowft["xlgr"];
																			$nro 				=  $rowft["nro"];
																			$xbairro 			=  $rowft["xbairro"];
																			$cep 			=  $rowft["cep"];
																			$xmun 				=  $rowft["xmun"];
																			$uf 				=  $rowft["uf"];
																			$DDDPrestador 		=  $rowft["DDDPrestador"];
																			$TelefonePrestador 	=  $rowft["TelefonePrestador"];
																			$emailres 			=  $rowft["emailres"];

																			$cnpj_cpf = preg_replace("/\D/", '', $cnpj);

																			//if (strlen($cnpj_cpf) === 11) {
																			// $cnpj_cpf = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
																			//} 
																			$cnpj = substr($cnpj, 0, 2).'.'.substr($cnpj, 2, 3) .
																				'.'.substr($cnpj, 5, 3).'/' .
																				substr($cnpj, 8, 4).'-'.substr($cnpj, 12, 2);


																			?>
																			<div style="width:100%; text-align:center; background:#f7f7f7; padding-top:7px; padding-bottom:7px; line-height:12px;font-size:6px">
																				<? echo ('<span style="display:block; height: 10px; line-height: 10px;font-size:6px !important;">Local de realização das análises:</span>
																				<span style="display:block; height: 10px; line-height: 10px;font-size:6px !important;margin-bottom:4px">'.$razaosocial.'. CNPJ: '.$cnpj.' - I.E.: '.$inscestadual.'.</span>
																				<span style="display:block; height: 10px; line-height: 10px;font-size:6px !important;">'.$xlgr.', '.$nro.'°. '.$xbairro.'. CEP: '.$cep.' - '.$xmun.'-'.$uf.'. ('.$DDDPrestador.') '.$TelefonePrestador.' - '.$emailres.'</span>'); ?>

																			</div>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</tfoot>
			</table>
			<!-- FIM: Rodape -->
			<?
		}

		function cabecalhores()
		{

			//invoca para dentro deste contexto desta funcao as variaveis ja existentes fora dela. Isto permite que os valores da query do relatorio sejam utilizados aqui
			global $row, $rowbio, $rowend, $arrAmostraCampos;

			//verificar se usuario pode assinar o teste;
			$sqlass = "select assinateste from pessoa where assinateste = 'Y' and idpessoa =".$_SESSION["SESSAO"]["IDPESSOA"];
			$resass = mysql_query($sqlass) or die("Erro ao verificar se usuario assina testes: ".mysql_error());
			$qtdrowd = mysql_num_rows($resass);

			$campo = array();
			foreach ($arrAmostraCampos as $amostracampos) {
				$campo[$amostracampos['campo']] = $amostracampos;
			}

			$varobs = nl2br($row["observacao"]);
		}
		function cabecalhores1()
		{

			//invoca para dentro deste contexto desta funcao as variaveis ja existentes fora dela. Isto permite que os valores da query do relatorio sejam utilizados aqui
			global $row, $rowbio, $rowend, $arrAmostraCampos, $infoResultado;

			//verificar se usuario pode assinar o teste;
			$sqlass = "select assinateste from pessoa where assinateste = 'Y' and idpessoa =".$_SESSION["SESSAO"]["IDPESSOA"];
			$resass = mysql_query($sqlass) or die("Erro ao verificar se usuario assina testes: ".mysql_error());
			$qtdrowd = mysql_num_rows($resass);

			$campo = array();
			foreach ($arrAmostraCampos as $amostracampos) {
				$campo[$amostracampos['campo']] = $amostracampos;
			}

			$varobs = nl2br($row["observacao"]);

			?>
				<!-- Dados Exame -->
				<div class="border-black border-2 flex rounded px-4 py-2">
					<div class="w-4/12 flex flex-col">
						<div class="flex w-full">
							<strong class="w-6/12">Reg. Amostra: </strong>
							<span><?= $infoResultado['idamostra'] ?></span>
						</div>
						<div class="flex w-full">
							<strong class="w-6/12">Tutor: </strong>
							<span><?= $infoResultado['tutor'] ?? '-' ?></span>
						</div>
						<div class="flex w-full">
							<strong class="w-6/12">Paciente: </strong>
							<span><?= $infoResultado['paciente'] ?? '-' ?></span>
						</div>
						<div class="flex w-full">
							<strong class="w-6/12">Espécie: </strong>
							<span><?= $infoResultado['especie'] ?></span>
						</div>
						<div class="flex w-full justify-between">
							<strong>Subtipo:</strong>
							<span class="w-6/12"><?= $row["subtipoamostra"] ?? '-' ?></span>
						</div>
						<div class="flex w-full">
							<strong class="w-6/12">Sexo: </strong>
							<span><?= $infoResultado['sexo'] ?></span>
						</div>
						<div class="flex w-full">
							<strong class="w-6/12">Idade: </strong>
							<span><?= "{$infoResultado['idade']} - {$infoResultado['tipoidade']}" ?></span>
						</div>
						<div class="flex w-full">
							<strong class="w-6/12">Observação Interna: </strong>
							<span><?= $row['observacaointerna'] ?></span>
						</div>
					</div>
					<div class="ms-auto w-4/12 flex flex-col">
						<!-- <div class="flex w-full justify-between">
							<strong>Data da Solicitação:</strong>
							<span class="w-7/12"><?= $infoResultado['criadoem'] ?></span>
						</div> -->
						<div class="flex w-full justify-between">
							<strong>Data Coleta:</strong>
							<span class="w-7/12"><?= $row["datacoleta"] ?? 'Não Informado' ?></span>
						</div>
						<div class="flex w-full justify-between">
							<strong>Data Registro:</strong>
							<span class="w-7/12"><?= $row["dataamostraformatada"] ?? 'Não Informado' ?></span>
						</div>
						<div class="flex w-full justify-between">
							<strong>Médico Vet:</strong>
						<span class="w-7/12"><?= $infoResultado['responsavel'] ?? '-' ?></span>
						</div>
						<div class="flex w-full justify-between">
							<strong>CRMV:</strong>
							<span class="w-7/12"><?= $infoResultado['crmv'] ?? '-' ?></span>
						</div>
					</div>
				</div>
				<? if($varobs) { ?>
					<div class="w-full mt-5">
						<span>
							<strong class="font-bold text-sm">Observações: </strong> <?= $varobs ?>
						</span>
					</div>
			<? }
		}

			function assinaturarodape($inidresultado)
			{
				global $arrassinat;
				$qtdrowss = count($arrassinat);
				//se não foi assinado nao imprime assinatura
				if ($qtdrowss > 0) {
				?>
					<tr>
						<td style="width: 100%">
							<div style="width:700px;">
								<table style="width: 100%; top:-7px; position:relative;" id="assinatura">
									<tr>
										<td>
											<table class="tsep" style="width:100%;">
												<tr>
													<td>
														<table class="tsep" style="width:100%;">
															<tr>
																<td class="td" style="width:100%">
																	<table style="width:100%">
																		<tr>
																			<td class="tdval grval" style="background-color:#fff;">


																				<?
																				echo "<div align='center' style='margin:0px;padding:0px;border:none; width:100%'> \n";

																				echo "<table class='tabass' style='width:100%'> \n";
																				echo "<tr> \n";
																				foreach ($arrassinat as $i => $rowass) {

																					$nomresp = "";
																					$crmvresp = "";

																					//troca dados do responsavel via hardcode
																					switch ($rowass["idpessoa"]) {
																						case 782: //edison
																							$nomresp = "EDISON ROSSI";
																							$crmvresp = "CRMV - MG N&ordm; 1626";;
																							break;
																						case 1484: //edison
																							$nomresp = "EDISON ROSSI";
																							$crmvresp = "CRMV - MG N&ordm; 1626";;
																							break;
																						case 797: //marcio
																							$nomresp = "MARCIO BOTREL";
																							$crmvresp = "CRMV - MG N&ordm; 1454";;
																							break;
																						case 1483: //marcio
																							$nomresp = "MARCIO BOTREL";
																							$crmvresp = "CRMV - MG N&ordm; 1454";;
																							break;
																						case 5655: //marcio
																							$nomresp = "JOSÉ RENATO DE O. BRANCO";
																							$crmvresp = "CRMV - MG N&ordm; 19770";;
																							break;
																						case 1098: //marcio
																							$nomresp = "HERMES PEDRO";
																							$crmvresp = "CRMV - MG N&ordm; 20412";;
																							break;
																						default:
																							null;
																							break;
																					}

																					if ($nomresp) {

																						$arrAss = assinaturaDigitalA1($rowass["idresultado"].$rowass["criadoem"].$rowass["idpessoa"], $inUsuarioSislaudo);

																						echo "<td align='center'>";
																						echo "<img src='../inc/img/sig".strtolower(trim($rowass["idpessoa"])).".gif' height='48px'> \n";
																						echo "<p><label class='lbresp' style='font-weight:bold'>".($nomresp)."</label></p>";
																						echo "<p><label class='lb6'>Respons&aacute;vel T&eacute;cnico: ".($crmvresp)."</label> </p>";
																						echo "<p> </p>";
																						echo "<p><label class='lb6'>Assinatura Digital: ".$arrAss["assinatura"]."</label> | <label class='lb6'>Data Assinatura:".$rowass["criadoem"]."</label> </p>";
																						//	echo "<p><label class='lb6'><img width='6px;' src='../inc/img/secure15.png'> Autorização Certificado Digital Serpro: ".$arrAss["autorizacaoserpro"]."</label> \n";
																						echo "</td>";
																					}
																				}

																				echo "</tr> \n";
																				echo "</table> \n";

																				echo "</div> \n";
																				?>


																			</td>
																		</tr>
																	</table>
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
			</tbody>


		<?
				}
			}

			function assinaturarodape1($inidresultado)
			{
				global $arrassinat;
				$qtdrowss = count($arrassinat);
				//se não foi assinado nao imprime assinatura
				if ($qtdrowss > 0) {
				?>
					<tr >
					<td style="width: 100%" >
							<div style="width:500px;margin:auto;z-index: 99999;position: relative;">
								<div  class='barcode' align="center" idresultado="<?=$inidresultado?>"  alt="" title="Resultado <?=$inidresultado?>" >
									<img src="<?php
										$host  = $_SERVER['HTTP_HOST'];
										$path   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
										$url = "https://" . $host . "/" . $path;
										require_once('/var/www/carbon8/inc/cte/vendor/autoload.php');
										$barcode = new Barcode();
										try {
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
										echo 'data://text/plain;base64,' . base64_encode($qrcode);
									?>" alt="">
								</div>
								
							</div>
						</td>
					</tr>
					<tr>
						<td style="width: 100%;">
							<div style="width:700px;margin:auto; ">
								<table style="width: 100%; top:-7px; position:relative;" id="assinatura">
									<tr>
										<td>
											<table class="tsep" style="width:100%;">
												<tr>
													<td>
														<table class="tsep" style="width:100%;">
															<tr>
																<td class="td" style="width:100%">
																	<table style="width:100%">
																		<tr>
																			<td class="tdval grval" style="background-color:#fff;">


																				<?
																				echo "<div align='center' style='margin:0px;padding:0px;border:none; width:100%'> \n";

																				echo "<table class='tabass' style='width:100%'> \n";
																				echo "<tr> \n";
																				foreach ($arrassinat as $i => $rowass) {

																					$nomresp = "";
																					$crmvresp = "";

																					//troca dados do responsavel via hardcode
																					switch ($rowass["idpessoa"]) {
																						case 782: //edison
																							$nomresp = "EDISON ROSSI";
																							$crmvresp = "CRMV - MG N&ordm; 1626";;
																							break;
																						case 1484: //edison
																							$nomresp = "EDISON ROSSI";
																							$crmvresp = "CRMV - MG N&ordm; 1626";;
																							break;
																						case 797: //marcio
																							$nomresp = "MARCIO BOTREL";
																							$crmvresp = "CRMV - MG N&ordm; 1454";;
																							break;
																						case 1483: //marcio
																							$nomresp = "MARCIO BOTREL";
																							$crmvresp = "CRMV - MG N&ordm; 1454";;
																							break;
																						case 5655: //marcio
																							$nomresp = "JOSÉ RENATO DE O. BRANCO";
																							$crmvresp = "CRMV - MG N&ordm; 19770";;
																							break;
																						case 1098: //marcio
																							$nomresp = "HERMES PEDRO";
																							$crmvresp = "CRMV - MG N&ordm; 20412";;
																							break; 
																						case 118063: 
																							$nomresp = "MARIANA SOARES";
																							$crmvresp = "CRMV - MG N&ordm; 21638";;
																							break; 
																						default:
																							null;
																							break;
																					}

																					if ($nomresp) {

																						$arrAss = assinaturaDigitalA1($rowass["idresultado"].$rowass["criadoem"].$rowass["idpessoa"], $inUsuarioSislaudo);

																						echo "<td align='center'>";
																						echo "<img src='../inc/img/sig".strtolower(trim($rowass["idpessoa"])).".gif' height='48px'> \n";
																						echo "<p><label class='lbresp' style='font-weight:bold'>".($nomresp)."</label></p>";
																						echo "<p><label class='lb6'>Respons&aacute;vel T&eacute;cnico: ".($crmvresp)."</label> </p>";
																						echo "<p> </p>";
																						echo "<p><label class='lb6'>Assinatura Digital: ".$arrAss["assinatura"]."</label> | <label class='lb6'>Data Assinatura:".$rowass["criadoem"]."</label> </p>";
																						echo "<p><label class='lb6'>Data de Emissão do relatório:".$rowass["criadoem"]."</p>";
																						//	echo "<p><label class='lb6'><img width='6px;' src='../inc/img/secure15.png'> Autorização Certificado Digital Serpro: ".$arrAss["autorizacaoserpro"]."</label> \n";
																						echo "</td>";
																					}
																				}

																				echo "</tr> \n";
																				echo "</table> \n";

																				echo "</div> \n";
																				?>


																			</td>
																		</tr>
																	</table>
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
			</tbody>


		<?
				}
			}

			/*
 * Monta relatorio descritivo, geado a partir do RTE
 */
			function remove_empty_tags_recursive($str, $repto = NULL)
			{
				//** Return if string not given or empty.
				if (
					!is_string($str)
					|| trim($str) == ''
				)
					return $str;

				//** Recursive empty HTML tags.
				return preg_replace(

					//** Pattern written by Junaid Atari.
					'/<([^<\/>]*)>([\s]*?|(?R))<\/\1>/imsU',

					//** Replace with nothing if string empty.
					!is_string($repto) ? '' : $repto,

					//** Source string
					$str
				);
			}

			function relresultado($mostraass, $ocultar)
			{


				//invoca para dentro deste contexto desta funcao as variaveis ja existentes fora dela. Isto permite que os valores da query do relatorio sejam utilizados aqui
				global $echosql, $row, $rtitulos, $arrgrafgmt, $modelo, $modo, $grafico, $tipogmt, $arrprodservtipoopcao, $arrprodservtipoopcaoespecie, $arrlotecons, $templateinterpretacao;
				//MCC 29/08/2019 descomentei o trecho abaixo pois estava gerando lixo nas partidas, atribuindo as mesmas a resultados de forma errada.
				global $ins_nomepartida, $ins_fabricante, $ins_partidaext, $ins_fabricacao, $ins_vencimento;

				//$echosql = true;


				$prodservcongelada = true;

				if ($prodservcongelada == true) {

					foreach ($arrlotecons as $i => $linhai) {

						$ins_nomepartida[$i]			= $linhai['descr'];
						$ins_fabricante[$i]				= $linhai['fabricante'];
						$ins_partidaext[$i]				= $linhai['partidaext'];
						$ins_fabricacao[$i]				= $linhai['fabricacao'];
						$ins_vencimento[$i]				= $linhai['vencimento'];
					}
				} else {
					$sqli = "
			select 
				c.qtdd, 
				c.qtdd_exp,
				pl.descr,
				l.spartida,
				l.partidaext,
				DATE_FORMAT(l.vencimento, '%d/%m/%Y') as vencimento,
				DATE_FORMAT(l.fabricacao, '%d/%m/%Y') as fabricacao,
				l.fabricante
			FROM lotecons c
			JOIN lote l ON c.idlote=l.idlote
			JOIN prodservformulains i ON i.idprodserv=l.idprodserv
			JOIN prodservformula p ON p.idprodservformula = i.idprodservformula
			JOIN prodserv pl ON pl.idprodserv = l.idprodserv
			WHERE 
			c.tipoobjeto ='resultado' 
			and c.idobjeto ='".$row['idresultado']."'
			and p.idprodserv = '".$row['idtipoteste']."'
			and c.qtdd>0
		   	and i.listares='Y';";
					$resi = mysql_query($sqli) or die("Erro ao buscar as pesagens para listagem sql 1".$sqlind);
					$x = 1;
					while ($linhai = mysql_fetch_assoc($resi)) {
						$ins_nomepartida[$x]			= $linhai['descr'];
						$ins_fabricante[$x]				= $linhai['fabricante'];
						$ins_partidaext[$x]				= $linhai['partidaext'];
						$ins_fabricacao[$x]				= $linhai['fabricacao'];
						$ins_vencimento[$x]				= $linhai['vencimento'];
						$x++;
					}
				}
				//echo count($arrprodservtipoopcaoespecie);
				//mcc - 28/11/2018 - pegar a configuração da prodserv congelada 
				if ($prodservcongelada == true) {
					$modelo 				= $row['modelo'];
					$modo 					= $row['modo'];
					$tipogmt 				= $row['tipogmt'];
					$comparativodelotes 	= $row['comparativodelotes'];
				} else {
					//mcc - 28/11/2018 - pegar a configuração direto da prodserv
					$sqlc =
						"SELECT
		modelo,
		modo,
		tipogmt,
		comparativodelotes
	FROM
		prodserv
	WHERE
		idprodserv = '".$row['idtipoteste']."';";

					$resc = mysql_query($sqlc) or die("Erro ao buscar as pesagens para listagem sql 1".$sqlind);
					$y = 0;
					while ($linha = mysql_fetch_assoc($resc)) {
						$modelo 				= $linha['modelo'];
						$modo 					= $linha['modo'];
						$tipogmt 				= $linha['tipogmt'];
						$comparativodelotes 	= $linha['comparativodelotes'];
					}
				}

				//Recupera os rotulos de orificios para os valores GMT
				$rot = array();

				$rot = $rtitulos;

				if (!empty($row['idespeciefinalidade'])) {
					unset($cor);
					unset($msg);
					unset($valorinicio);
					unset($valorfim);
					if ($prodservcongelada == true) {
						//mcc - 28/11/2018 - pegar a configuração da prodserv congelada 
						$c = 0;
						foreach ($arrprodservtipoopcaoespecie as $i => $linhad) {

							$valorinicio[$c] 					= $linhad['valorinicio'];
							$valorfim[$c] 						= $linhad['valorfim'];
							$cor[$c] 							= $linhad['cor'];
							$msg[$c] 							= $linhad['msg'];
							switch ($cor[$c]) {
								case 'azul':
									$cor[$c] = '#00ffff';
									break;
								case 'amarelo':
									$cor[$c] = '#ffff00';
									break;
								case 'vermelho':
									$cor[$c] = '#ff0000';
									break;
								case 'verde':
									$cor[$c] = '#00ff00';
									break;
							}

							$c++;
						}
					} else {
						//mcc - 28/11/2018 - pegar a configuração direto da prodserv
						$sqld = "
		SELECT
			idespeciefinalidade,
			valorinicio,
			valorfim,
			cor,
			ptoe.valorinicio,
			ptoe.valorfim,
			msg
		FROM
			prodservtipoopcaoespecie ptoe
		WHERE
			ptoe.idprodserv = '".$row['idtipoteste']."' AND
			status = 'ATIVO' AND
			idadeinicio <= '".$row['idade']."' AND
			idadefim >= '".$row['idade']."' AND
			idespeciefinalidade = '".$row['idespeciefinalidade']."'
			order by
			valorinicio;";

						$resd = mysql_query($sqld) or die("Erro ao montar configuração de gráfico ".$sqlind);

						$c = 0;
						while ($linhad = mysql_fetch_assoc($resd)) {
							$valorinicio[$c] 		= $linhad['valorinicio'];
							$valorfim[$c] 		= $linhad['valorfim'];
							$cor[$c] 							= $linhad['cor'];
							$msg[$c] 							= $linhad['msg'];
							switch ($cor[$c]) {
								case 'azul':
									$cor[$c] = '#00ffff';
									break;
								case 'amarelo':
									$cor[$c] = '#ffff00';
									break;
								case 'vermelho':
									$cor[$c] = '#ff0000';
									break;
								case 'verde':
									$cor[$c] = '#00ff00';
									break;
							}

							$c++;
						}
					}

					//Verifica se e 'Semanas', nao gerar grafico GMT
					$boolsem = strpos(strtoupper($row["tipoidade"]), "SEM");
					if ($boolsem === false) { //Atenção para utilização do '===': $boolsem retorna como encontrado na posição 0 a string procurada. 0=False, portanto, com === se força a tratar somente booleanos
						$boolsem = false;
					} else {
						$boolsem = true;
					}

					if ($echosql) {
						echo "<!-- Rótulos Orifícios: \n";
						print_r($rot);
						echo "\n -->";
					}

					if (empty($rot)) {
						echo "<!-- Erro recuperando Titulos dos orificios GMT. O Array veio vazio. Provavelmente os Titulos nao estao cadastrados -->";
					}

					$arrrotulo   = array(); //guarda os valores do orificio
					$arrorificio = array(); //guarda os orifio
					$arrfrasecab = array();	//guarda a frase padrao
					$arrcolorbar = array(); //guarda a cor do grafio	

					$y = 0;

					//die($tipogmt.' '.$modo);
					if ($tipogmt == "GMT") {

						if ($modelo == "DINÂMICO") {
							$sqldina = "SELECT count(*) as qtdorificio,r.* from vw8resultadocampocalculo r where r.idresultado = ".$row['idresultado']."".getidempresa('r.idempresa', 'pessoa')." group by r.indice";
							$rew = mysql_query($sqldina) or die("Erro ao buscar resultados no tipo dinamico sql ".$sqldina);
							$y = 0;
							$qtdorificio = 0;
							while ($rowdina = mysql_fetch_assoc($rew)) {
								$arrorificio[$y] = $rowdina['qtdorificio']; //guarda quantas aves no array
								$qtdorificio 	 = $qtdorificio + $rowdina['qtdorificio']; //soma a quantidade de aves no orifcio
								$arrrotulo[$y] 	 = $rowdina['valor']; //guarda o valor do titulo
								$y++;
							}

							if ($rew->num_rows > 0) {
								$urlimg = graftitulo($arrrotulo, $arrorificio, $arrcolorbar);
							}
						} else {
							if ($modo == "AGRUP") {

								for ($i = 1; $i <= 13; $i++) { //roda nos 13 orificios

									//se o oficio foi marcado alguma vez
									if ($row["q".$i] > 0) {
										$arrorificio[$y] = $row["q".$i]; //guarda quantas aves no array
										$qtdorificio = $qtdorificio + $row["q".$i]; //soma a quantidade de aves no orifcio
										$arrrotulo[$y] = $rot[$i]; //guarda o valor do titulo 

										if ($row["q".$i] > 1) {
											$arrfrasecab[$y] = $row["q".$i]." Amostras apresentaram título ".$rot[$i];
										} else {
											$arrfrasecab[$y] = $row["q".$i]." Amostra apresentou título ".$rot[$i];
										}

										$c = 0;

										while ($c < count($valorinicio)) {

											if ($rot[$i] >= $valorinicio[$c] and $rot[$i] <= $valorfim[$c]) {

												$qtd[$c] = $qtd[$c] + $row["q".$i];
												$arrcolorbar[$y] = $cor[$c];
											}

											$c++;
										}
										$y++;
									}
								}

								$c = 0;
								while ($c < count($valorinicio)) {
									$perc[$c] = round((($qtd[$c] * 100) / $qtdorificio), 2);
									$c++;
								}

								$urlimg = graftitulo($arrrotulo, $arrorificio, $arrcolorbar);
							} elseif ($modo == "IND") {

								if ($prodservcongelada == true) {
									//mcc - 28/11/2018 - pegar a configuração da prodserv congelada 
									$c = 1;

									foreach ($arrprodservtipoopcao as $i => $linhad) {

										if ($linhad['valor'] == '0.0') {
											$linhad['valor'] = 0;
										}
										$strind[$c] = $linhad['valor'];
										$c++;
									}
								} else {
									$sqli = "SELECT
										valor
									FROM
										prodservtipoopcao
									WHERE
										idprodserv = '".$row['idtipoteste']."'
									ORDER BY
										valor*1, valor";
									$resi = mysql_query($sqli) or die("Erro ao buscar orifícios".$sqlind);
									$y = 1;
									while ($rowi = mysql_fetch_assoc($resi)) {
										if ($rowi['valor'] == '0.0') {
											$rowi['valor'] = 0;
										}

										$strind[$y] = $rowi['valor'];
										$y++;
									}
								}
							}

							// o resultado individual não possui quantidade de orificios predefinida por este motivo  e gerada uma nova tabela resultadoindividual

							$sqlind = "select 
								count(*) as qtdorificio,
								-- r.identificacao,
								r.resultado
							from 
								resultadoindividual r
							where 
								r.resultado is not null
								and r.idresultado = '".$row['idresultado']." '
							group by 
								resultado 
							order by 
								r.resultado*1, r.resultado";

							$resind = mysql_query($sqlind) or die("Erro ao buscar resultados dos orificios do teste do bioensaio sql".$sqlind);
							$y = 0;
							while ($rowind = mysql_fetch_assoc($resind)) {
								$arrorificio[$y] = $rowind['qtdorificio']; //guarda quantas aves no array
								$qtdorificio 	 = $qtdorificio + $rowind['qtdorificio']; //soma a quantidade de aves no orifcio
								$arrrotulo[$y] 	 = $strind[$rowind['resultado']]; //guarda o valor do titulo

								$c = 0;

								while ($c < count($valorinicio)) {
									if ($arrrotulo[$y] >= $valorinicio[$c] and $arrrotulo[$y] <= $valorfim[$c]) {
										$arrcolorbar[$y] = $cor[$c];
									}
									$c++;
								}
								$y++;
							}


							$urlimg = graftitulo($arrrotulo, $arrorificio, $arrcolorbar);
						}

						$arrrgmt            = array();
						$arridade           = array();

						$x = 0;

						if ($comparativodelotes) {
							foreach ($arrgrafgmt as $i => $idadegmt) {
								//maf040511: solicitação de Daniel: retirar a condicao de 'gtm > 0' porque os registros 5977 e 5984 de 2011 não estavam mostrando os resultados  de GMTs zerados
								//if($arrgrafgmt["gmt"] > 0){
								$arrrgmt[$x] = $idadegmt["gmt"];
								$arridade[$x] = $idadegmt["idade"];
								$x++;
								//}	
							}
							//se o array não estiver vazio e se for semanas mostra o grafico		
							if (!empty($arrrgmt) and !empty($arridade) and $boolsem == true) {
								$urlimggmt = grafgmt($arrrgmt, $arridade, $maiorgmt);
							}
						}
					} else if ($tipogmt == "ART") {

						if ($modelo == "DINÂMICO") {
							$sqldina = "SELECT count(*) as qtdorificio,r.* from vw8resultadocampocalculo r where r.idresultado = ".$row['idresultado']."".getidempresa('r.idempresa', 'pessoa')." group by r.indice";
							$rew = mysql_query($sqldina) or die("Erro ao buscar resultados no tipo dinamico sql ".$sqldina);
							$y = 0;
							$qtdorificio = 0;
							while ($rowdina = mysql_fetch_assoc($rew)) {
								$arrorificio[$y] = $rowdina['qtdorificio']; //guarda quantas aves no array
								$qtdorificio 	 = $qtdorificio + $rowdina['qtdorificio']; //soma a quantidade de aves no orifcio
								$arrrotulo[$y] 	 = $rowdina['valor']; //guarda o valor do titulo
								$y++;
							}
							$urlimg = graftitulo($arrrotulo, $arrorificio, $arrcolorbar);
						} else {

							$sqlind = "select 
									count(*) as qtdorificio,
								-- r.identificacao,
									r.resultado
									from 
											resultadoindividual r
									where 
											r.resultado is not null
											and r.idresultado = '".$row['idresultado']." '
									group by 
											resultado 
									order by 
											r.resultado";

							$resind = mysql_query($sqlind) or die("Erro ao buscar resultados dos orificios do teste do bioensaio sql".$sqlind);
							$y = 0;
							while ($rowind = mysql_fetch_assoc($resind)) {
								$arrorificio[$y] = $rowind['qtdorificio']; //guarda quantas aves no array
								$qtdorificio 	 = $qtdorificio + $rowind['qtdorificio']; //soma a quantidade de aves no orifcio
								$arrrotulo[$y] 	 = $rowind['resultado']; //guarda o valor do titulo

								$c = 0;

								while ($c < count($valorinicio)) {
									if ($arrrotulo[$y] >= $valorinicio[$c] and $arrrotulo[$y] <= $valorfim[$c]) {
										$arrcolorbar[$y] = $cor[$c];
									}
									$c++;
								}
								$y++;
							}
							$urlimg = graftitulo($arrrotulo, $arrorificio, $arrcolorbar);
						}
					}
				}




		?>
		<tr>
			<td style="width: 100%">
				<div style="width:700px;">
					<table style="width: 100%;position:relative;">
						<? $sql = "SELECT * FROM resultado where idresultado=".$row["idresultado"];
						$resp = mysql_query($sql);
						$linhap = mysql_fetch_assoc($resp);

						if (!empty($linhap["observacao"])) { ?>
							<tr>
								<td style="width: 100%">
									<div style="width:700px;">
										<table style="width: 100%;position:relative;">
											<td style="width:100%">
												<table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
													<tr style="background-color:#f7f7f7; font-size:11x; text-transform:uppercase;	height:20px;">
														<td idresultado="<?= $row["idresultado"] ?>" style="font-size:11px;width:50%">
															Informações Adicionais
														</td>
														<? if ($row["versao"] < 1) {
															$versao = 1;
														} else {
															$versao = $row["versao"];
														} ?>
													</tr>
													<tr>
														<td style="vertical-align:top;width:64%">
															<fieldset class="fset" style="border:none;">
																<?= $linhap["observacao"] ?>
															</fieldset>
														</td>
													</tr>
												</table>
											</td>
										</table>
									</div>
								</td>
							</tr>
						<? } ?>

						<tr>
							<td style="width: 100%">
								<table class="tsep" style="width:100%;">
									<tr>
										<td>
											<table class="tsep" style="width:100%; margin-top:0px;"><!-- Cabecalho Superior -->
												<tr>
													<td style="width:100%">
														<table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
															<tr class="cabecalho_superior" style="background-color:#f7f7f7; font-size:11x; text-transform:uppercase; height:20px;">
																<td idresultado="<?= $row["idresultado"] ?>" style="font-size:11px;width:50%">Resultado <span style="font-size:6px !important;">(<?= $row["quantidadeteste"]; ?> teste(s) realizado(s))</span> </td>
																<? if ($row["versao"] < 1) {
																	$versao = 1;
																} else {
																	$versao = $row["versao"];
																} ?>
																<td style="vertical-align:top;text-align:right !important; width:50%; font-size:8px; line-height: 16px; padding-top: 1px" align="right">
																	<?
																	if ($versao > 1) {
																		$verant = $versao - 1;
																		echo ("<span style='font-size:6px !important;'>Este relatório substitui o de n° ".$row["idresultado"].".".$verant."</span>");
																	}
																	?>
																	ID Teste:<font style='font-weight:bold'><?= $row["idresultado"] ?>
																		<? if ($versao > 0) { 
																			?>.<? echo $versao;
																		} ?></font>
																</td>
															</tr>
										
															<tr>
																<td class="tdval grval" colspan="2">
																	<br>
																	<table style="width:100%; text-transform:none;">

																		<? if ($modelo == "UPLOAD") { //Se for elisa, quebrar a tabela em partes iguais para nao gerar paginas 'soltas' na impressao
																			relelisa($row["idresultado"], $row["idnucleo"], $row["idpessoa"], $row["idtipoteste"], $row["tipoidade"], $row["idespeciefinalidade"], $mostraass, 1, $row["textointerpretacao"], $row["textopadrao"]);
																		} else {
																		?>
																			<tr>
																				<? if ($modelo == 'DINÂMICO' && empty($urlimg)) {
																					echo '<td style="vertical-align:top;width:100%;" class="py-3">';
																				} else if (empty($urlimg)) { ?>
																					<td style="vertical-align:top;width:100%">
																					<? } else { ?>
																					<td style="vertical-align:top;width:64%">
																					<? } ?>

																					<fieldset class="fset" style="border:none;">
																					<?if($modelo == 'DESCRITIVO' && $modo == 'AGRUP'){?>
																						<div class="resdesc" id="resm" style="vertical-align:top"  tabela="resultado" idpk="<?=$row['idresultado']?>" campo="descritivo">
																					<?}else{?>
																						<div class="resdesc" id="resm" style="vertical-align:top">
																					<?}?>
																							<?

																							$sqla = "select caminho from arquivo where idobjeto = '".$row['idresultado']."' and tipoobjeto = 'resultado'";
																							$resa = mysql_query($sqla);
																							$qtd = mysql_num_rows($resa);

																							if($qtd > 0){
																								echo '<div>';
																								while($rowa = mysql_fetch_assoc($resa)){
																									$row["caminho"] = $rowa['caminho'];

																									if (file_exists($row["caminho"])) {
																										echo '<a href="'.$row["caminho"].'" target="_blank"><img src="../inc/img/pdf-icon2.png"></a> &nbsp; &nbsp;';
																									}
																								}
																								echo '</div>';
																							}

																						
																							//hermes: Na tela de assinatura, colocar o texto de inclusao resultado no teste caso ele esteja aberto e com o descritivo vazio
																							if (empty($row["descritivo"]) and $row['status'] == 'ABERTO' and !empty($row['idtipoteste'])) {
																								$sqlt = "select textoinclusaores from prodserv where idprodserv =".$row['idtipoteste'];
																								$rest = mysql_query($sqlt);
																								$rowt = mysql_fetch_assoc($rest);

																								$row["descritivo"] = $rowt['textoinclusaores'];
																							}
																							$row["descritivo"] = str_replace("&nbsp;", "", $row["descritivo"]);
																							$row["descritivo"] = preg_replace('/<P[^>]*>\s*?<\/P[^>]*>/', '', $row["descritivo"]);
																							// $row["descritivo"] = preg_replace('/(<[^>]+) style=".*?"/i', '$1',  $row["descritivo"]);
																							
																							//regex removido pois estava removendo o resultado inteiro
																							// $row["descritivo"] = preg_replace('/(<[^>]+) align=".*?"/i', '$1',  $row["descritivo"]);
																							//Escreve diretamente na tela o resultado descritivo gerado pelo RTE
																							echo (($row["descritivo"]));

																							if ($modelo == 'DINÂMICO') {
																								//echo $row["jsonresultado"];
																								$phpArray = json_decode($row["jsonresultado"], true);
																								//	print_r($phpArray);

																								$phpArray[0]->name;
																								$z = 0;
																								$vindice = '';
																								$x = 0;
																								$validaId = 0;
																								foreach ($phpArray as $key => $val) {
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

																								foreach ($phpArray as $key1 => $value1) {
																									
																									if ($key1 == 'INDIVIDUAL') {
																										//	print_r($value1);
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

																											$countTd = 0;
																											foreach ($dinamicoindividual['header'] as $key1 => $value1) {
																												if ($value1 == "ID" && $validaId == 0) {
																												} else {
																													$countTd++;
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
																									$tabela .= '<tr><td style="width:74px;white-space:nowrap;"><b>'.$value1['header'].':</b></td><td>'.$value1['value'].'</td></tr>';
																								}
																								foreach ($phpArray as $key1 => $value1) {
																									foreach ($value1 as $k => $v) {

																										if ($v['calculo'] == "SIM") {

																											if ($tipogmt == "GMT") {
																												$tabela .= "<tr><td class='text-center' style='flex-grow: 1;'><b>MÉDIA GEOMÉTRICA DOS TÍTULOS:</b></td> <td>".$row["gmt"]."</td></tr>";
																												break;
																											} else if ($tipogmt == "ART") {
																												$tabela .= "<tr><td class='text-center' style='flex-grow: 1;'><b>MÉDIA ARITMÉTICA:</b></td> <td>".$row["gmt"]."</td></tr>";
																												break;
																											} else if ($tipogmt == "PERC") {
																												$porcTd = $countTd == 3 ? '45' : '60';
																												$tabela .= "<tr><td class='text-center'><fieldset class='fset' style='border:none;'><div class='resdesc'><table style='background: #f7f7f7; height: 40px; padding: 6px;'><tbody><tr><td style='width: $porcTd%; padding: 5px !important;'><b style='font-size: 10px;'>PERCENTUAL ".$campocalc.":</b></td><td><b style='font-size: 10px;'>".$row["gmt"]."%</b></td></tr></tbody></table></div></fieldset></td></tr>";
																												break;
																											}
																										}
																									}
																								}
																								if (count($dinamicoagrupado) > 0) {
																									$tabela .= '</table>';
																								}

																								echo $tabela;
																								unset($dinamicoindividual);
																								unset($dinamicoagrupado);
																							} elseif ($modo == 'IND') {
																								if ($prodservcongelada == true) {
																									//mcc - 28/11/2018 - pegar a configuração da prodserv congelada 
																									$c = 1;

																									foreach ($arrprodservtipoopcao as $i => $linhad) {

																										if ($linhad['valor'] == '0.0') {
																											$linhad['valor'] = 0;
																										}
																										$strind[$c] = $linhad['valor'];
																										$c++;
																									}
																								} else {
																									$sqli = "SELECT
																											valor
																										FROM
																											prodservtipoopcao
																										WHERE
																											idprodserv = '".$row['idtipoteste']."'
																										ORDER BY
																											valor*1, valor";
																									$resi = mysql_query($sqli) or die("Erro ao buscar orifícios".$sqlind);
																									$y = 1;
																									while ($rowi = mysql_fetch_assoc($resi)) {
																										if ($rowi['valor'] == '0.0') {
																											$rowi['valor'] = 0;
																										}

																										$strind[$y] = $rowi['valor'];
																										$y++;
																									}
																								}
																								$sqlind = "select ri.identificacao,
																												ri.resultado
																											from 
																												resultadoindividual ri
																											join
																												resultado r on r.idresultado = ri.idresultado
																											where 
																												ri.idresultado = ".$row['idresultado']." 
																											order 
																												by ri.idresultadoindividual";

																								$resind = mysql_query($sqlind) or die("Erro ao buscar identificação e resultados dos orificios do teste do bioensaio sql".$sqlind);
																								$y = 1;

																								$total = mysql_num_rows($resind);
																								while ($rowind = mysql_fetch_assoc($resind)) {
																									if ($y > ($total / 2)) {
																										echo '</ul>';
																										$y = 1;
																									}
																									if ($y == 1) {
																										echo '<ul style="width:40%;vertical-align:top; margin-bottom:0px; padding-left:12px; float:left; margin-top:0px;">';
																									}

																									if (!empty($rowind['identificacao'])) {

																										if ($tipogmt == 'GMT') {
																											echo "<li>Amostra ".$rowind['identificacao']." apresentou título ".$strind[$rowind['resultado']]."</li>";
																										} elseif ($tipogmt == 'ART') {
																											echo "<li>Amostra ".$rowind['identificacao']." pesou ".$rowind['resultado']." (GR)</li>";
																										} else {
																											$arrrotulo[$y] 	 = $strind[$rowind['resultado']];
																											echo "<li>Amostra ".$rowind['identificacao']." apresentou resultado ".$arrrotulo[$y]."</li>";
																											$y++;
																										}
																									} else {
																										echo "<li>Amostra ".$rowind['identificacao']." apresentou resultado ".$rowind['resultado']."</li>";
																									}
																									$y++;
																								}
																								if ($tipogmt == "GMT") {
																									echo "<li>Média Geométrica dos títulos: ".$row["gmt"]."</li>";
																								} else if ($tipogmt == "ART") {
																									echo "<li>Média Aritmética: ".$row["gmt"]."</li>";
																								}

																								if ($y > 0) {
																									echo '</ul>';
																								}
																							} else if ($modo == 'AGRUP') {

																								for ($i = 1; $i <= 13; $i++) { //roda nos 13 orificios

																									//se o oficio foi marcado alguma vez
																									if ($row["q".$i] > 0) {


																										if ($row["q".$i] > 1) {
																											echo "<li>".$row["q".$i]." Amostras apresentaram título ".$rot[$i];
																										} else {
																											echo "<li>".$row["q".$i]." Amostra apresentou título ".$rot[$i];
																										}


																										$y++;
																									}
																								}
																								if ($tipogmt == "GMT") {
																									echo "<li>Média Geométrica dos títulos: ".$row["gmt"]."</li>";
																								} else if ($tipogmt == "ART") {
																									echo "<li>Média Aritmética: ".$row["gmt"]."</li>";
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
																								<li>
																									<?= ($frasepronta2) ?>
																								</li>
																							<?
																							}

																							?>
																							</ul>

																						</div>
																					</fieldset>

																					<?
																					if (!empty($row["textointerpretacao"]) or trim($row["textointerpretacao"]) != "" or !empty($row["interfrase"]) or trim($row["interfrase"]) != "") {
																					?>

																						<BR>
																						<fieldset class="fset">
																							<legend>
																								<font class="ftitulo" style="text-transform:uppercase;">&nbsp;Interpretação *&nbsp;</font>
																							</legend>

																							<? echo $templateinterpretacao; ?>
																							<div class="resdesc" id="inter" style="font-size:8px !important ;">

																								<div id="fraseedicao" class="divfrase" style="width:100%"><?= ($row["interfrase"]) ?><Br><?= ($row["textointerpretacao"]) ?>
																									<input id='idfrasedit' type="hidden" value="<?= strip_tags($row['interfrase'], "<em>", "<sup>", "<sub>"); ?>">
																								</div>



																								<? if (!empty($row["idade"]) and !empty($row["tipoidade"])) { ?>

																									<table class="tablegenda" style="width:100%;text-transform:none">
																										<tr>
																											<td>* Para inserção da interpretação não foram considerados registros posteriores a <?= ($row["idade"]) ?> <?= ($row["tipoidade"]) ?></td>
																										</tr>
																									</table>
																								<?
																								}

																								?>
																							</div>
																						</fieldset>
																					<?
																					} elseif ($mostraass == false) {
																					?>

																						<BR>
																						<fieldset class="fset">
																							<legend>
																								<font class="ftitulo" style="text-transform:uppercase;">&nbsp;Interpretação *&nbsp;</font>
																							</legend>

																							<? echo $templateinterpretacao; ?>
																							<div class="resdesc">
																								<div id="fraseedicao" class="divfrase" style="width:100%"><textarea rows='5' cols='40' id='idfrasedit' tabindex="1"><?= ($row["interfrase"]) ?></textarea>
																									<br>
																									<?= ($row["textointerpretacao"]); ?>
																								</div>

																								<? if (!empty($row["idade"]) and !empty($row["tipoidade"])) { ?>
																									<table class="tablegenda" style="width:100%;text-transform:none">
																										<tr>
																											<td>* Para inserção da interpretação não foram considerados registros posteriores a <?= ($row["idade"]) ?> <?= ($row["tipoidade"]) ?></td>
																										</tr>
																									</table>
																								<?
																								}

																								?>
																							</div>
																						</fieldset>
																					<?
																					}
				
																					//MCC 19/11/2019 - Comentado o trecho sobre a condição que obrigava o campo textopadrao ser diferente de vazio.
																					//if(trim($row["textopadrao"])!=="" and $ocultar != 0) 
																					if ($ocultar != 0) { ?>

																						<BR>
																						<fieldset class="fset">
																							<legend>
																								<font class="ftitulo" style="text-transform:uppercase;">&nbsp;Considerações *&nbsp;</font>
																							</legend>

																							<div class="resdesc resdesc2">
																								<?
																								$x = 1;


																								if (count($ins_partidaext) > 0) {
																									echo '<table><tr><td>';


																									while ($x <= count($ins_partidaext)) {
																										if ($x % 2 == 0) {
																											$bor = 'border-right:1px dashed #eee;';
																										} else {
																											$bor = '';
																										}

																										if ($x > 1) {
																											$bot = 'border-top:1px dashed #eee;';
																										} else {
																											$bot = '';
																										}

																										echo '<ul style="padding-left:4px !important; '.$bot.' '.$bor.'  padding:4px;  float:left;list-style: none; width:47%; min-width:30px;vertical-align:top; margin-bottom:0px; padding-left:0px; float:left; margin-top:0px;font-size:6px !important;">';
																										echo '<li>PARTIDA DE '.$ins_nomepartida[$x].'</li>';
																										echo '<li>FABRICANTE: '.$ins_fabricante[$x].'</li>';
																										echo '<li>PARTIDA: '.$ins_partidaext[$x].'</li>';
																										echo '<li>FABRICAÇÃO: '.$ins_fabricacao[$x].'</li>';
																										echo '<li>VENCIMENTO: '.$ins_vencimento[$x].'</li>';
																										echo '</ul>';

																										$x++;
																									}


																									if ($x % 2 != 0) {
																										if ($x % 2 == 0) {
																											$bor = 'border-right:1px dashed #eee;';
																										} else {
																											$bor = '';
																										}

																										if ($x > 1) {
																											$bot = 'border-top:1px dashed #eee;';
																										} else {
																											$bot = '';
																										}

																										echo '<ul style="padding-left:4px !important; '.$bot.' '.$bor.'  padding:4px;  float:left;list-style: none; width:47%; min-width:30px;vertical-align:top; margin-bottom:0px; padding-left:0px; float:left; margin-top:0px;font-size:6px !important;">';
																										echo '<li></li>';

																										echo '</ul>';
																									}
																									echo '</td></tr></table>';
																								}
																								?>
																								<br>
																								<?= preg_replace('/<(\w+) [^>]+>/', '<$1>', $row["textopadrao"]);
																								?>
																							</div>
																						</fieldset>
																					<?    }
																					?>

																					</td>
																					<?
																					if ((!empty($urlimg) or !empty($urlimggmt)) and $row["idade"] != '') {
																					?>
																						<? if ($modelo == 'DINÂMICO') {
																							echo '<td style="vertical-align:top;width:1%">';
																						} else { ?>
																							<td style="width:36%;vertical-align: top;">
																							<? } ?>

																							<?
																							if (!empty($urlimg)) {
																							?>
																								<fieldset class="fset">
																									<legend>
																										<font class="ftitulo" style="text-transform:uppercase;">&nbsp;gráfico *&nbsp;</font>
																									</legend>
																									<img src="<?= $urlimg ?>" border="0" alt="Gráfico GMT" style="height: 120px;">
																									<? $c = 0;
																									while ($c < count($cor)) {
																										echo '<div style="font-size:7px !important; float:left; width:100%"><div style="width:8px; height:8px;float:left;background-color:'.$cor[$c].'">&nbsp;</div>&nbsp;'.$perc[$c].'% - '.$msg[$c].' (entre '.$valorinicio[$c].' e '.$valorfim[$c].')</div>';
																										$c++;
																									}
																									?>

																								</fieldset>
																							<? } ?>
																							<br>
																							<?
																							if (!empty($urlimggmt) and $comparativodelotes == 'Y') {
																							?>
																								<fieldset class="fset">
																									<legend>
																										<font class="ftitulo" style="text-transform:uppercase;">&nbsp;Histórico *&nbsp;</font>
																									</legend>

																									<img src="<?= $urlimggmt ?>" border="0" alt="Gráfico GMT" style="height: 120px;">



																								</fieldset>
																							<? } ?>
																							</td>
																						<? }
																						?>
																			</tr>
																		<? } ?>
																		<tr>
																			<td colspan="2" style="text-align:center">
																				<span class="block w-full" style="font-size:6px; text-align:center; text-transform:UPPERCASE">Os resultados deste relatório se restringem às amostras ensaiadas. Esse relatório só pode ser reproduzido em sua totalidade. <br>Data da realização do ensaio: <?= $row["dataconclusao"]; ?>
																					<br />
																				</span>
																				<?
																				if (!(empty($row["idsecretaria"]))) {
																					echo 'Conforme legislação vigente, se obrigado por lei, o laboratório poderá disponibilizar este resultado  em domínio público sem autorização prévia do cliente.';
																				}
																				?>
																			</td>
																		</tr>
																	</table>
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
		<?
		if ($mostraass == true and $ocultar != 0) {

			assinaturarodape($row["idresultado"]);
			//Apos assinatura colocar quebra de linha para o rodape nao misturar com a assinatura
		}
	}
	function relresultado1($mostraass, $ocultar)
	{
		//invoca para dentro deste contexto desta funcao as variaveis ja existentes fora dela. Isto permite que os valores da query do relatorio sejam utilizados aqui
		global $echosql, $row, $resultado, $rtitulos, $arrgrafgmt, $modelo, $modo, $grafico, $tipogmt, $arrprodservtipoopcao, $arrprodservtipoopcaoespecie, $arrlotecons, $templateinterpretacao;
		//MCC 29/08/2019 descomentei o trecho abaixo pois estava gerando lixo nas partidas, atribuindo as mesmas a resultados de forma errada.
		global $ins_nomepartida, $ins_fabricante, $ins_partidaext, $ins_fabricacao, $ins_vencimento;
		global $infoResultado;

		//$echosql = true;


		$prodservcongelada = true;

		if ($prodservcongelada == true) {

			foreach ($arrlotecons as $i => $linhai) {

				$ins_nomepartida[$i]			= $linhai['descr'];
				$ins_fabricante[$i]				= $linhai['fabricante'];
				$ins_partidaext[$i]				= $linhai['partidaext'];
				$ins_fabricacao[$i]				= $linhai['fabricacao'];
				$ins_vencimento[$i]				= $linhai['vencimento'];
			}
		} else {
			$sqli = "
	select 
		c.qtdd, 
		c.qtdd_exp,
		pl.descr,
		l.spartida,
		l.partidaext,
		DATE_FORMAT(l.vencimento, '%d/%m/%Y') as vencimento,
		DATE_FORMAT(l.fabricacao, '%d/%m/%Y') as fabricacao,
		l.fabricante
	FROM lotecons c
	JOIN lote l ON c.idlote=l.idlote
	JOIN prodservformulains i ON i.idprodserv=l.idprodserv
	JOIN prodservformula p ON p.idprodservformula = i.idprodservformula
	JOIN prodserv pl ON pl.idprodserv = l.idprodserv
	WHERE 
	c.tipoobjeto ='resultado' 
	and c.idobjeto ='".$row['idresultado']."'
	and p.idprodserv = '".$row['idtipoteste']."'
	and c.qtdd>0
	and i.listares='Y';";
			$resi = mysql_query($sqli) or die("Erro ao buscar as pesagens para listagem sql 1".$sqlind);
			$x = 1;
			while ($linhai = mysql_fetch_assoc($resi)) {
				$ins_nomepartida[$x]			= $linhai['descr'];
				$ins_fabricante[$x]				= $linhai['fabricante'];
				$ins_partidaext[$x]				= $linhai['partidaext'];
				$ins_fabricacao[$x]				= $linhai['fabricacao'];
				$ins_vencimento[$x]				= $linhai['vencimento'];
				$x++;
			}
		}
		//echo count($arrprodservtipoopcaoespecie);
		//mcc - 28/11/2018 - pegar a configuração da prodserv congelada 
		if ($prodservcongelada == true) {
			$modelo 				= $row['modelo'];
			$modo 					= $row['modo'];
			$tipogmt 				= $row['tipogmt'];
			$comparativodelotes 	= $row['comparativodelotes'];
		} else {
			//mcc - 28/11/2018 - pegar a configuração direto da prodserv
			$sqlc =
				"SELECT
modelo,
modo,
tipogmt,
comparativodelotes
FROM
prodserv
WHERE
idprodserv = '".$row['idtipoteste']."';";

			$resc = mysql_query($sqlc) or die("Erro ao buscar as pesagens para listagem sql 1".$sqlind);
			$y = 0;
			while ($linha = mysql_fetch_assoc($resc)) {
				$modelo 				= $linha['modelo'];
				$modo 					= $linha['modo'];
				$tipogmt 				= $linha['tipogmt'];
				$comparativodelotes 	= $linha['comparativodelotes'];
			}
		}

		//Recupera os rotulos de orificios para os valores GMT
		$rot = array();

		$rot = $rtitulos;

		if (!empty($row['idespeciefinalidade'])) {
			unset($cor);
			unset($msg);
			unset($valorinicio);
			unset($valorfim);
			if ($prodservcongelada == true) {
				//mcc - 28/11/2018 - pegar a configuração da prodserv congelada 
				$c = 0;
				foreach ($arrprodservtipoopcaoespecie as $i => $linhad) {

					$valorinicio[$c] 					= $linhad['valorinicio'];
					$valorfim[$c] 						= $linhad['valorfim'];
					$cor[$c] 							= $linhad['cor'];
					$msg[$c] 							= $linhad['msg'];
					switch ($cor[$c]) {
						case 'azul':
							$cor[$c] = '#00ffff';
							break;
						case 'amarelo':
							$cor[$c] = '#ffff00';
							break;
						case 'vermelho':
							$cor[$c] = '#ff0000';
							break;
						case 'verde':
							$cor[$c] = '#00ff00';
							break;
					}

					$c++;
				}
			} else {
				//mcc - 28/11/2018 - pegar a configuração direto da prodserv
				$sqld = "
SELECT
	idespeciefinalidade,
	valorinicio,
	valorfim,
	cor,
	ptoe.valorinicio,
	ptoe.valorfim,
	msg
FROM
	prodservtipoopcaoespecie ptoe
WHERE
	ptoe.idprodserv = '".$row['idtipoteste']."' AND
	status = 'ATIVO' AND
	idadeinicio <= '".$row['idade']."' AND
	idadefim >= '".$row['idade']."' AND
	idespeciefinalidade = '".$row['idespeciefinalidade']."'
	order by
	valorinicio;";

				$resd = mysql_query($sqld) or die("Erro ao montar configuração de gráfico ".$sqlind);

				$c = 0;
				while ($linhad = mysql_fetch_assoc($resd)) {
					$valorinicio[$c] 		= $linhad['valorinicio'];
					$valorfim[$c] 		= $linhad['valorfim'];
					$cor[$c] 							= $linhad['cor'];
					$msg[$c] 							= $linhad['msg'];
					switch ($cor[$c]) {
						case 'azul':
							$cor[$c] = '#00ffff';
							break;
						case 'amarelo':
							$cor[$c] = '#ffff00';
							break;
						case 'vermelho':
							$cor[$c] = '#ff0000';
							break;
						case 'verde':
							$cor[$c] = '#00ff00';
							break;
					}

					$c++;
				}
			}

			//Verifica se e 'Semanas', nao gerar grafico GMT
			$boolsem = strpos(strtoupper($row["tipoidade"]), "SEM");
			if ($boolsem === false) { //Atenção para utilização do '===': $boolsem retorna como encontrado na posição 0 a string procurada. 0=False, portanto, com === se força a tratar somente booleanos
				$boolsem = false;
			} else {
				$boolsem = true;
			}

			if ($echosql) {
				echo "<!-- Rótulos Orifícios: \n";
				print_r($rot);
				echo "\n -->";
			}

			if (empty($rot)) {
				echo "<!-- Erro recuperando Titulos dos orificios GMT. O Array veio vazio. Provavelmente os Titulos nao estao cadastrados -->";
			}

			$arrrotulo   = array(); //guarda os valores do orificio
			$arrorificio = array(); //guarda os orifio
			$arrfrasecab = array();	//guarda a frase padrao
			$arrcolorbar = array(); //guarda a cor do grafio	

			$y = 0;

			//die($tipogmt.' '.$modo);
			if ($tipogmt == "GMT") {

				if ($modelo == "DINÂMICO") {
					$sqldina = "SELECT count(*) as qtdorificio,r.* from vw8resultadocampocalculo r where r.idresultado = ".$row['idresultado']."".getidempresa('r.idempresa', 'pessoa')." group by r.indice";
					$rew = mysql_query($sqldina) or die("Erro ao buscar resultados no tipo dinamico sql ".$sqldina);
					$y = 0;
					$qtdorificio = 0;
					while ($rowdina = mysql_fetch_assoc($rew)) {
						$arrorificio[$y] = $rowdina['qtdorificio']; //guarda quantas aves no array
						$qtdorificio 	 = $qtdorificio + $rowdina['qtdorificio']; //soma a quantidade de aves no orifcio
						$arrrotulo[$y] 	 = $rowdina['valor']; //guarda o valor do titulo
						$y++;
					}

					if ($rew->num_rows > 0) {
						$urlimg = graftitulo($arrrotulo, $arrorificio, $arrcolorbar);
					}
				} else {
					if ($modo == "AGRUP") {

						for ($i = 1; $i <= 13; $i++) { //roda nos 13 orificios

							//se o oficio foi marcado alguma vez
							if ($row["q".$i] > 0) {
								$arrorificio[$y] = $row["q".$i]; //guarda quantas aves no array
								$qtdorificio = $qtdorificio + $row["q".$i]; //soma a quantidade de aves no orifcio
								$arrrotulo[$y] = $rot[$i]; //guarda o valor do titulo 

								if ($row["q".$i] > 1) {
									$arrfrasecab[$y] = $row["q".$i]." Amostras apresentaram título ".$rot[$i];
								} else {
									$arrfrasecab[$y] = $row["q".$i]." Amostra apresentou título ".$rot[$i];
								}

								$c = 0;

								while ($c < count($valorinicio)) {

									if ($rot[$i] >= $valorinicio[$c] and $rot[$i] <= $valorfim[$c]) {

										$qtd[$c] = $qtd[$c] + $row["q".$i];
										$arrcolorbar[$y] = $cor[$c];
									}

									$c++;
								}
								$y++;
							}
						}

						$c = 0;
						while ($c < count($valorinicio)) {
							$perc[$c] = round((($qtd[$c] * 100) / $qtdorificio), 2);
							$c++;
						}

						$urlimg = graftitulo($arrrotulo, $arrorificio, $arrcolorbar);
					} elseif ($modo == "IND") {

						if ($prodservcongelada == true) {
							//mcc - 28/11/2018 - pegar a configuração da prodserv congelada 
							$c = 1;

							foreach ($arrprodservtipoopcao as $i => $linhad) {

								if ($linhad['valor'] == '0.0') {
									$linhad['valor'] = 0;
								}
								$strind[$c] = $linhad['valor'];
								$c++;
							}
						} else {
							$sqli = "SELECT
								valor
							FROM
								prodservtipoopcao
							WHERE
								idprodserv = '".$row['idtipoteste']."'
							ORDER BY
								valor*1, valor";
							$resi = mysql_query($sqli) or die("Erro ao buscar orifícios".$sqlind);
							$y = 1;
							while ($rowi = mysql_fetch_assoc($resi)) {
								if ($rowi['valor'] == '0.0') {
									$rowi['valor'] = 0;
								}

								$strind[$y] = $rowi['valor'];
								$y++;
							}
						}
					}

					// o resultado individual não possui quantidade de orificios predefinida por este motivo  e gerada uma nova tabela resultadoindividual

					$sqlind = "select 
						count(*) as qtdorificio,
						-- r.identificacao,
						r.resultado
					from 
						resultadoindividual r
					where 
						r.resultado is not null
						and r.idresultado = '".$row['idresultado']." '
					group by 
						resultado 
					order by 
						r.resultado*1, r.resultado";

					$resind = mysql_query($sqlind) or die("Erro ao buscar resultados dos orificios do teste do bioensaio sql".$sqlind);
					$y = 0;
					while ($rowind = mysql_fetch_assoc($resind)) {
						$arrorificio[$y] = $rowind['qtdorificio']; //guarda quantas aves no array
						$qtdorificio 	 = $qtdorificio + $rowind['qtdorificio']; //soma a quantidade de aves no orifcio
						$arrrotulo[$y] 	 = $strind[$rowind['resultado']]; //guarda o valor do titulo

						$c = 0;

						while ($c < count($valorinicio)) {
							if ($arrrotulo[$y] >= $valorinicio[$c] and $arrrotulo[$y] <= $valorfim[$c]) {
								$arrcolorbar[$y] = $cor[$c];
							}
							$c++;
						}
						$y++;
					}


					$urlimg = graftitulo($arrrotulo, $arrorificio, $arrcolorbar);
				}

				$arrrgmt            = array();
				$arridade           = array();

				$x = 0;

				if ($comparativodelotes) {
					foreach ($arrgrafgmt as $i => $idadegmt) {
						//maf040511: solicitação de Daniel: retirar a condicao de 'gtm > 0' porque os registros 5977 e 5984 de 2011 não estavam mostrando os resultados  de GMTs zerados
						//if($arrgrafgmt["gmt"] > 0){
						$arrrgmt[$x] = $idadegmt["gmt"];
						$arridade[$x] = $idadegmt["idade"];
						$x++;
						//}	
					}
					//se o array não estiver vazio e se for semanas mostra o grafico		
					if (!empty($arrrgmt) and !empty($arridade) and $boolsem == true) {
						$urlimggmt = grafgmt($arrrgmt, $arridade, $maiorgmt);
					}
				}
			} else if ($tipogmt == "ART") {

				if ($modelo == "DINÂMICO") {
					$sqldina = "SELECT count(*) as qtdorificio,r.* from vw8resultadocampocalculo r where r.idresultado = ".$row['idresultado']."".getidempresa('r.idempresa', 'pessoa')." group by r.indice";
					$rew = mysql_query($sqldina) or die("Erro ao buscar resultados no tipo dinamico sql ".$sqldina);
					$y = 0;
					$qtdorificio = 0;
					while ($rowdina = mysql_fetch_assoc($rew)) {
						$arrorificio[$y] = $rowdina['qtdorificio']; //guarda quantas aves no array
						$qtdorificio 	 = $qtdorificio + $rowdina['qtdorificio']; //soma a quantidade de aves no orifcio
						$arrrotulo[$y] 	 = $rowdina['valor']; //guarda o valor do titulo
						$y++;
					}
					$urlimg = graftitulo($arrrotulo, $arrorificio, $arrcolorbar);
				} else {

					$sqlind = "select 
							count(*) as qtdorificio,
						-- r.identificacao,
							r.resultado
							from 
									resultadoindividual r
							where 
									r.resultado is not null
									and r.idresultado = '".$row['idresultado']." '
							group by 
									resultado 
							order by 
									r.resultado";

					$resind = mysql_query($sqlind) or die("Erro ao buscar resultados dos orificios do teste do bioensaio sql".$sqlind);
					$y = 0;
					while ($rowind = mysql_fetch_assoc($resind)) {
						$arrorificio[$y] = $rowind['qtdorificio']; //guarda quantas aves no array
						$qtdorificio 	 = $qtdorificio + $rowind['qtdorificio']; //soma a quantidade de aves no orifcio
						$arrrotulo[$y] 	 = $rowind['resultado']; //guarda o valor do titulo

						$c = 0;

						while ($c < count($valorinicio)) {
							if ($arrrotulo[$y] >= $valorinicio[$c] and $arrrotulo[$y] <= $valorfim[$c]) {
								$arrcolorbar[$y] = $cor[$c];
							}
							$c++;
						}
						$y++;
					}
					$urlimg = graftitulo($arrrotulo, $arrorificio, $arrcolorbar);
				}
			}
		}




?>
<div class="w-full flex mt-3 flex-wrap">
	<div class="w-full text-end">
		<? if ($row["versao"] < 1) {
			$versao = 1;
		} else {
			$versao = $row["versao"];
		}
		if ($versao > 1) {
			$verant = $versao - 1;
			echo ("<span style='font-size:6px !important;'>Este relatório substitui o de n° ".$row["idresultado"].".".$verant."<br></span>");
		}
		?>
		ID Teste: <strong class="font-bold"><?= $row["idresultado"] ?></strong>
	</div>
	<div class="w-full flex flex-col items-center mb-5">
		<h3 class="uppercase text-xl"><?=  $infoResultado['descr'] ?></h3>
		<h4 class="uppercase text-lg">(<?= $row["quantidadeteste"]; ?> teste(s) realizado(s))</h4>
	</div>
	<div class="w-6/12 flex gap-5">
		<span><b>Data de início:</b> <?=$row["dataamostraformatada"]?></span>
		<span><b>Data de término:</b> <?= $row["dataconclusao"]; ?></span>
	</div>
	<table class="w-full my-3 py-2 border-y-2">
		<? if ($modelo == "UPLOAD") {
			//Se for elisa, quebrar a tabela em partes iguais para nao gerar paginas 'soltas' na impressao
			if($resultado['criadoem'] > date('2023-11-09 14:30:00')){
				relelisa1($row["idresultado"], $row["idnucleo"], $row["idpessoa"], $row["idtipoteste"], $row["tipoidade"], $row["idespeciefinalidade"], $mostraass, 1, $row["textointerpretacao"], $row["textopadrao"]);
			}else{
				relelisa($row["idresultado"], $row["idnucleo"], $row["idpessoa"], $row["idtipoteste"], $row["tipoidade"], $row["idespeciefinalidade"], $mostraass, 1, $row["textointerpretacao"], $row["textopadrao"]);
			}
		} else {
		?>
			<tr>
				<? if ($modelo == 'DINÂMICO' && empty($urlimg)) {
					echo '<td style="vertical-align:top;width:100%;" class="py-3">';
				} else if (empty($urlimg)) { ?>
					<td style="vertical-align:top;width:100%">
					<? } else { ?>
					<td style="vertical-align:top;width:64%">
					<? } ?>

					<fieldset class="fset" style="border:none;">
					<?if($modelo == 'DESCRITIVO' && $modo == 'AGRUP'){?>
						<div class="resdesc" id="resm" style="vertical-align:top"  tabela="resultado" idpk="<?=$row['idresultado']?>" campo="descritivo">
					<?}else{?>
						<div class="resdesc" id="resm" style="vertical-align:top">
					<?}?>
							<?

							$sqla = "select caminho from arquivo where idobjeto = '".$row['idresultado']."' and tipoobjeto = 'resultado'";
							$resa = mysql_query($sqla);
							$rowa = mysql_fetch_assoc($resa);

							$row["caminho"] = $rowa['caminho'];

							if (file_exists($row["caminho"])) {
								echo '<a href="'.$row["caminho"].'" target="_blank"><img src="../inc/img/pdf-icon2.png"  style="position: absolute;right: 8px;top: 28px;"></a>';
							}

							//hermes: Na tela de assinatura, colocar o texto de inclusao resultado no teste caso ele esteja aberto e com o descritivo vazio
							if (empty($row["descritivo"]) and $row['status'] == 'ABERTO' and !empty($row['idtipoteste'])) {
								$sqlt = "select textoinclusaores from prodserv where idprodserv =".$row['idtipoteste'];
								$rest = mysql_query($sqlt);
								$rowt = mysql_fetch_assoc($rest);

								$row["descritivo"] = $rowt['textoinclusaores'];
							}
							$row["descritivo"] = str_replace("&nbsp;", "", $row["descritivo"]);
							$row["descritivo"] = preg_replace('/<P[^>]*>\s*?<\/P[^>]*>/', '', $row["descritivo"]);
							// $row["descritivo"] = preg_replace('/(<[^>]+) style=".*?"/i', '$1',  $row["descritivo"]);
							$row["descritivo"] = preg_replace('/(<[^>]+) align=".*?"/i', '$1',  $row["descritivo"]);
							//Escreve diretamente na tela o resultado descritivo gerado pelo RTE
							echo (($row["descritivo"]));

							if ($modelo == 'DINÂMICO') {
								//echo $row["jsonresultado"];
								$phpArray = json_decode($row["jsonresultado"], true);
								//	print_r($phpArray);

								$phpArray[0]->name;
								$z = 0;
								$vindice = '';
								$x = 0;
								$validaId = 0;
								foreach ($phpArray as $key => $val) {
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

								foreach ($phpArray as $key1 => $value1) {
									
									if ($key1 == 'INDIVIDUAL') {
										//	print_r($value1);
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

											$countTd = 0;
											foreach ($dinamicoindividual['header'] as $key1 => $value1) {
												if ($value1 == "ID" && $validaId == 0) {
												} else {
													$countTd++;
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
									$tabela .= '<tr><td style="width:74px;white-space:nowrap;"><b>'.$value1['header'].':</b></td><td>'.$value1['value'].'</td></tr>';
								}
								foreach ($phpArray as $key1 => $value1) {
									foreach ($value1 as $k => $v) {

										if ($v['calculo'] == "SIM") {

											if ($tipogmt == "GMT") {
												$tabela .= "<tr><td class='text-center' style='flex-grow: 1;'><b>MÉDIA GEOMÉTRICA DOS TÍTULOS:</b></td> <td>".$row["gmt"]."</td></tr>";
												break;
											} else if ($tipogmt == "ART") {
												$tabela .= "<tr><td class='text-center' style='flex-grow: 1;'><b>MÉDIA ARITMÉTICA:</b></td> <td>".$row["gmt"]."</td></tr>";
												break;
											} else if ($tipogmt == "PERC") {
												$porcTd = $countTd == 3 ? '45' : '60';
												$tabela .= "<tr><td class='text-center'><fieldset class='fset' style='border:none;'><div class='resdesc'><table style='background: #f7f7f7; height: 40px; padding: 6px;'><tbody><tr><td style='width: $porcTd%; padding: 5px !important;'><b style='font-size: 10px;'>PERCENTUAL ".$campocalc.":</b></td><td><b style='font-size: 10px;'>".$row["gmt"]."%</b></td></tr></tbody></table></div></fieldset></td></tr>";
												break;
											}
										}
									}
								}
								if (count($dinamicoagrupado) > 0) {
									$tabela .= '</table>';
								}

								echo $tabela;
								unset($dinamicoindividual);
								unset($dinamicoagrupado);
							} elseif ($modo == 'IND') {
								if ($prodservcongelada == true) {
									//mcc - 28/11/2018 - pegar a configuração da prodserv congelada 
									$c = 1;

									foreach ($arrprodservtipoopcao as $i => $linhad) {

										if ($linhad['valor'] == '0.0') {
											$linhad['valor'] = 0;
										}
										$strind[$c] = $linhad['valor'];
										$c++;
									}
								} else {
									$sqli = "SELECT
											valor
										FROM
											prodservtipoopcao
										WHERE
											idprodserv = '".$row['idtipoteste']."'
										ORDER BY
											valor*1, valor";
									$resi = mysql_query($sqli) or die("Erro ao buscar orifícios".$sqlind);
									$y = 1;
									while ($rowi = mysql_fetch_assoc($resi)) {
										if ($rowi['valor'] == '0.0') {
											$rowi['valor'] = 0;
										}

										$strind[$y] = $rowi['valor'];
										$y++;
									}
								}
								$sqlind = "select ri.identificacao,
												ri.resultado
											from 
												resultadoindividual ri
											join
												resultado r on r.idresultado = ri.idresultado
											where 
												ri.idresultado = ".$row['idresultado']." 
											order 
												by ri.idresultadoindividual";

								$resind = mysql_query($sqlind) or die("Erro ao buscar identificação e resultados dos orificios do teste do bioensaio sql".$sqlind);
								$y = 1;

								$total = mysql_num_rows($resind);
								while ($rowind = mysql_fetch_assoc($resind)) {
									if ($y > ($total / 2)) {
										echo '</ul>';
										$y = 1;
									}
									if ($y == 1) {
										echo '<ul style="width:40%;vertical-align:top; margin-bottom:0px; padding-left:12px; float:left; margin-top:0px;">';
									}

									if (!empty($rowind['identificacao'])) {

										if ($tipogmt == 'GMT') {
											echo "<li>Amostra ".$rowind['identificacao']." apresentou título ".$strind[$rowind['resultado']]."</li>";
										} elseif ($tipogmt == 'ART') {
											echo "<li>Amostra ".$rowind['identificacao']." pesou ".$rowind['resultado']." (GR)</li>";
										} else {
											$arrrotulo[$y] 	 = $strind[$rowind['resultado']];
											echo "<li>Amostra ".$rowind['identificacao']." apresentou resultado ".$arrrotulo[$y]."</li>";
											$y++;
										}
									} else {
										echo "<li>Amostra ".$rowind['identificacao']." apresentou resultado ".$rowind['resultado']."</li>";
									}
									$y++;
								}
								if ($tipogmt == "GMT") {
									echo "<li>Média Geométrica dos títulos: ".$row["gmt"]."</li>";
								} else if ($tipogmt == "ART") {
									echo "<li>Média Aritmética: ".$row["gmt"]."</li>";
								}

								if ($y > 0) {
									echo '</ul>';
								}
							} else if ($modo == 'AGRUP') {

								for ($i = 1; $i <= 13; $i++) { //roda nos 13 orificios

									//se o oficio foi marcado alguma vez
									if ($row["q".$i] > 0) {


										if ($row["q".$i] > 1) {
											echo "<li>".$row["q".$i]." Amostras apresentaram título ".$rot[$i];
										} else {
											echo "<li>".$row["q".$i]." Amostra apresentou título ".$rot[$i];
										}


										$y++;
									}
								}
								if ($tipogmt == "GMT") {
									echo "<li>Média Geométrica dos títulos: ".$row["gmt"]."</li>";
								} else if ($tipogmt == "ART") {
									echo "<li>Média Aritmética: ".$row["gmt"]."</li>";
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
								<li>
									<?= ($frasepronta2) ?>
								</li>
							<?
							}

							?>
							</ul>

						</div>
					</fieldset>

					<?
					if (!empty($row["textointerpretacao"]) or trim($row["textointerpretacao"]) != "" or !empty($row["interfrase"]) or trim($row["interfrase"]) != "") {
					?>

						<BR>
						<fieldset class="fset">
							<legend>
								<font class="ftitulo" style="text-transform:uppercase;">&nbsp;Interpretação *&nbsp;</font>
							</legend>

							<? echo $templateinterpretacao; ?>
							<div class="resdesc" id="inter" style="font-size:8px !important ;">

								<div id="fraseedicao" class="divfrase" style="width:100%"><?= ($row["interfrase"]) ?><Br><?= ($row["textointerpretacao"]) ?>
									<input id='idfrasedit' type="hidden" value="<?= strip_tags($row['interfrase'], "<em>", "<sup>", "<sub>"); ?>">
								</div>



								<? if (!empty($row["idade"]) and !empty($row["tipoidade"])) { ?>

									<table class="tablegenda" style="width:100%;text-transform:none">
										<tr>
											<td>* Para inserção da interpretação não foram considerados registros posteriores a <?= ($row["idade"]) ?> <?= ($row["tipoidade"]) ?></td>
										</tr>
									</table>
								<?
								}

								?>
							</div>
						</fieldset>
					<?
					} elseif ($mostraass == false) {
					?>

						<BR>
						<fieldset class="fset">
							<legend>
								<font class="ftitulo" style="text-transform:uppercase;">&nbsp;Interpretação *&nbsp;</font>
							</legend>

							<? echo $templateinterpretacao; ?>
							<div class="resdesc">
								<div id="fraseedicao" class="divfrase" style="width:100%"><textarea rows='5' cols='40' id='idfrasedit' tabindex="1"><?= ($row["interfrase"]) ?></textarea>
									<br>
									<?= ($row["textointerpretacao"]); ?>
								</div>

								<? if (!empty($row["idade"]) and !empty($row["tipoidade"])) { ?>
									<table class="tablegenda" style="width:100%;text-transform:none">
										<tr>
											<td>* Para inserção da interpretação não foram considerados registros posteriores a <?= ($row["idade"]) ?> <?= ($row["tipoidade"]) ?></td>
										</tr>
									</table>
								<?
								}

								?>
							</div>
						</fieldset>
					<?
					}

					//MCC 19/11/2019 - Comentado o trecho sobre a condição que obrigava o campo textopadrao ser diferente de vazio.
					//if(trim($row["textopadrao"])!=="" and $ocultar != 0) 
					if ($ocultar != 0) { ?>

						<BR>
						<fieldset class="fset">
							<legend>
								<font class="ftitulo" style="text-transform:uppercase;">&nbsp;Considerações *&nbsp;</font>
							</legend>

							<div class="resdesc resdesc2">
								<?
								$x = 1;


								if (count($ins_partidaext) > 0) {
									echo '<table><tr><td>';


									while ($x <= count($ins_partidaext)) {
										if ($x % 2 == 0) {
											$bor = 'border-right:1px dashed #eee;';
										} else {
											$bor = '';
										}

										if ($x > 1) {
											$bot = 'border-top:1px dashed #eee;';
										} else {
											$bot = '';
										}

										echo '<ul style="padding-left:4px !important; '.$bot.' '.$bor.'  padding:4px;  float:left;list-style: none; width:47%; min-width:30px;vertical-align:top; margin-bottom:0px; padding-left:0px; float:left; margin-top:0px;font-size:6px !important;">';
										echo '<li>PARTIDA DE '.$ins_nomepartida[$x].'</li>';
										echo '<li>FABRICANTE: '.$ins_fabricante[$x].'</li>';
										echo '<li>PARTIDA: '.$ins_partidaext[$x].'</li>';
										echo '<li>FABRICAÇÃO: '.$ins_fabricacao[$x].'</li>';
										echo '<li>VENCIMENTO: '.$ins_vencimento[$x].'</li>';
										echo '</ul>';

										$x++;
									}


									if ($x % 2 != 0) {
										if ($x % 2 == 0) {
											$bor = 'border-right:1px dashed #eee;';
										} else {
											$bor = '';
										}

										if ($x > 1) {
											$bot = 'border-top:1px dashed #eee;';
										} else {
											$bot = '';
										}

										echo '<ul style="padding-left:4px !important; '.$bot.' '.$bor.'  padding:4px;  float:left;list-style: none; width:47%; min-width:30px;vertical-align:top; margin-bottom:0px; padding-left:0px; float:left; margin-top:0px;font-size:6px !important;">';
										echo '<li></li>';

										echo '</ul>';
									}
									echo '</td></tr></table>';
								}
								?>
								<br>
								<?= preg_replace('/<(\w+) [^>]+>/', '<$1>', $row["textopadrao"]);
								?>
							</div>
						</fieldset>
					<?    }
					?>

					</td>
					<?
					if ((!empty($urlimg) or !empty($urlimggmt)) and $row["idade"] != '') {
					?>
						<? if ($modelo == 'DINÂMICO') {
							echo '<td style="vertical-align:top;width:1%">';
						} else { ?>
							<td style="width:36%;vertical-align: top;">
							<? } ?>

							<?
							if (!empty($urlimg)) {
							?>
								<fieldset class="fset">
									<legend>
										<font class="ftitulo" style="text-transform:uppercase;">&nbsp;gráfico *&nbsp;</font>
									</legend>
									<img src="<?= $urlimg ?>" border="0" alt="Gráfico GMT" style="height: 120px;">
									<? $c = 0;
									while ($c < count($cor)) {
										echo '<div style="font-size:7px !important; float:left; width:100%"><div style="width:8px; height:8px;float:left;background-color:'.$cor[$c].'">&nbsp;</div>&nbsp;'.$perc[$c].'% - '.$msg[$c].' (entre '.$valorinicio[$c].' e '.$valorfim[$c].')</div>';
										$c++;
									}
									?>

								</fieldset>
							<? } ?>
							<br>
							<?
							if (!empty($urlimggmt) and $comparativodelotes == 'Y') {
							?>
								<fieldset class="fset">
									<legend>
										<font class="ftitulo" style="text-transform:uppercase;">&nbsp;Histórico *&nbsp;</font>
									</legend>

									<img src="<?= $urlimggmt ?>" border="0" alt="Gráfico GMT" style="height: 120px;">



								</fieldset>
							<? } ?>
							</td>
						<? }
						?>
			</tr>
		<? } ?>
		<tr>
			<td colspan="3" style="text-align:center">
				<span class="block w-full" style="font-size:6px; text-align:center; text-transform:UPPERCASE">
					Os resultados deste relatório se restringem às amostras ensaiadas. Esse relatório só pode ser reproduzido em sua totalidade. <br></span>
				<?
				if (!(empty($row["idsecretaria"]))) {
					echo 'Conforme legislação vigente, se obrigado por lei, o laboratório poderá disponibilizar este resultado  em domínio público sem autorização prévia do cliente.';
				}
				?>
			</td>
		</tr>
	</table>
	<table class="w-full">
		<? $sql = "SELECT * FROM resultado where idresultado=".$row["idresultado"];
		$resp = mysql_query($sql);
		$linhap = mysql_fetch_assoc($resp);

		if (!empty($linhap["observacao"])) { ?>
			<tr>
				<td style="width: 100%">
					<div style="width:700px;">
						<table style="width: 100%;position:relative;">
							<td style="width:100%">
								<table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
									<tr style="background-color:#f7f7f7; font-size:11x; text-transform:uppercase;	height:20px;">
										<td idresultado="<?= $row["idresultado"] ?>" style="font-size:11px;width:50%">
											Informações Adicionais
										</td>
										<? if ($row["versao"] < 1) {
											$versao = 1;
										} else {
											$versao = $row["versao"];
										} ?>
									</tr>
									<tr>
										<td style="vertical-align:top;width:64%">
											<fieldset class="fset" style="border:none;">
												<?= $linhap["observacao"] ?>
											</fieldset>
										</td>
									</tr>
								</table>
							</td>
						</table>
					</div>
				</td>
			</tr>
		<? } ?>

		<tr>
			<td style="width: 100%">
				<table class="tsep" style="width:100%;">
					<tr>
						<td>
							<table class="tsep" style="width:100%; margin-top:0px;"><!-- Cabecalho Superior -->
								<tr>
									<td>
										<fieldset class="fset">
											<legend>
												<font class="ftitulo" style="text-transform:uppercase;">&nbsp;Controle de Versionamento *&nbsp;</font>
											</legend>

											<div class="resdesc resdesc2">
												<?
												$sqlrest = "SELECT fso.versaoorigem,fso.versao,s.rotulo, fso.motivo, fso.idfluxostatushistobs, fso.motivoobs, fso.criadoem, p.nome
															FROM fluxostatushistobs fso 
																JOIN fluxostatus fs ON fs.idfluxostatus = fso.idfluxostatus
																JOIN "._DBCARBON."._status s ON s.idstatus = fs.idstatus
																JOIN pessoa p ON p.usuario = fso.criadopor
															WHERE idmodulo = '".$row['idresultado']."'
																AND modulo like 'result%'
																AND fso.versaoorigem is not null";
												$resrev = d::b()->query($sqlrest);
												$qtd = mysqli_num_rows($resrev);
												if($qtd == 0 and $versao == 1){?>
													<div class="row"  style="padding-top:5px">
														Ver. 1.0: Nenhuma revisão aplicada neste relatório de ensaio.
													</div>
												<?}
												if($qtd > 0){
												while($rowrev=mysql_fetch_assoc($resrev)){?>
													<div class="row"  style="padding-top:5px">
														Ver. <?=$rowrev['versao']?>.0:
														<?if(($versao+1) == $rowrev['versao']){?>
														<span class="resdesc" tabela="fluxostatushistobs" idpk="<?=$rowrev['idfluxostatushistobs']?>" campo="motivoobs">
															<?=$rowrev['motivoobs']?>
														</span>
														<?}else{?>
															<?=$rowrev['motivoobs']?>
														<?}?>
													</div>	
												<?}
												}?>
											</div>
										</fieldset>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>


<tr>
	<td style="width: 100%">
		<div style="width:700px;">
			
		</div>
	</td>
</tr>
<?
if ($mostraass == true and $ocultar != 0) {

	assinaturarodape1($row["idresultado"]);
	//Apos assinatura colocar quebra de linha para o rodape nao misturar com a assinatura
}
}



/*
* Monta o grafico de titulos
* MAF270821: Esta funcao deve ser removida deste ponto, e ser alocada em arquivo externo
*/
function graftitulo($arrrotulo, $arrorificio, $arrcolorbar)
{
	include_once("../inc/php/jpgraph/jpgraph.php");
	include_once("../inc/php/jpgraph/jpgraph_bar.php");


	//maf: verifica se  no minimo 1 orificio foi preenchido. caso contrario emite msg erro. Isto ocorre por exemplo em casos de amostras contaminadas onde nao foi possivel efetuar o teste
	if (empty($arrorificio)) {
		echo "<font color='red'><li>Nenhum T&iacute;tulo foi informado</li></font>";
		return "";
	}

	// Create the graph. These two calls are always required
	$graph = new Graph(350, 185);
	$graph->SetScale("textlin");
	$graph->SetMarginColor('white');
	$graph->SetFrame(true, 'silver', 1);
	$graph->xaxis->title->Set("Títulos");
	$graph->yaxis->title->Set("N. Amostras");
	$graph->yaxis->scale->SetGrace(10);
	$graph->yaxis->SetFont(FF_ARIAL, FS_NORMAL, 7); //comentar para funcionar no local
	$graph->yaxis->SetLabelMargin(2);
	$graph->yaxis->SetTitleMargin(20);
	// Adjust the margin a bit to make more room for titles
	$graph->img->SetMargin(35, 10, 5, 25);

	// Create a bar pot
	$bplot = new BarPlot($arrorificio);
	$bplot->SetFillColor($arrcolorbar);

	$bplot->value->Show();
	$bplot->value->SetFont(FF_VERDANA, FS_NORMAL, 7); //comentar para funcionar no local
	$bplot->value->SetFormat('%d');
	$bplot->value->SetAngle(0);
	$bplot->SetWeight(0); //sem borda
	$bplot->value->SetColor("black", "darkred"); // Black color for positive values and darkred for negative values
	$bplot->SetColor("black");

	$graph->Add($bplot);


	$graph->xaxis->SetTickLabels($arrrotulo);

	// Setup the titles
	$graph->title->Set("Resultado Atual");
	// $graph->xaxis->title->Set("X-fghjkl");
	// $graph->yaxis->title->Set("Y-title");

	//$graph->title->SetFont(FF_VERDANA,FS_BOLD,8);
	$graph->title->SetColor('darkgray');
	//$graph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
	$graph->yaxis->title->SetColor('darkgray');
	$graph->yaxis->HideLine(true);
	$graph->yaxis->HideTicks(true);

	$graph->xaxis->title->SetFont(FF_VERDANA, FS_NORMAL, 8); //comentar para funcionar no local
	$graph->xaxis->title->SetColor('darkgray');
	$graph->xaxis->HideTicks(true);
	$graph->xaxis->HideLine(true);

	$urlimg = "../tmp/graph/". session_id()."_".md5(uniqid(time())).".png";

	$graph->Stroke($urlimg);

	return $urlimg;
}

/*
* Grafico GMT
*/
function grafgmt($arrrgmt, $arridade)
{
//  maf191023: caminho errado estava causando erro 500
//  include_once("../jpgraph/jpgraph.php");
//	include_once("../jpgraph/jpgraph_bar.php");
    include_once("../inc/php/jpgraph/jpgraph.php");
    include_once("../inc/php/jpgraph/jpgraph_bar.php");
	// Create the graph. These two calls are always required
	$graph = new Graph(350, 185);
	$graph->SetScale("textlin");
	$graph->SetMarginColor('white');
	$graph->SetFrame(true, 'silver', 1);
	$graph->xaxis->title->Set("Semanas");
	$graph->yaxis->title->Set("Título(GMT)");
	$graph->yaxis->SetTitleMargin(30);
	$graph->yaxis->scale->SetGrace(10);
	$graph->yaxis->SetFont(FF_VERDANA, FS_NORMAL, 7);
	$graph->xaxis->SetFont(FF_VERDANA, FS_NORMAL, 7);
	$graph->yaxis->SetLabelMargin(3);
	// Adjust the margin a bit to make more room for titles
	$graph->img->SetMargin(50, 10, 5, 25);

	// Create a bar pot
	$bplot = new BarPlot($arrrgmt);
	$bplot->SetFillColor('#ffff00');

	$bplot->value->Show();
	$bplot->SetWeight(0); //sem borda
	// Must use TTF fonts if we want text at an arbitrary angle
	$bplot->value->SetFont(FF_VERDANA, FS_NORMAL, 7);
	$bplot->value->SetFormat('%d');
	$bplot->value->SetAngle(0);
	// Black color for positive values and darkred for negative values
	$bplot->value->SetColor("black", "darkred");

	$graph->Add($bplot);
	$graph->xaxis->SetTickLabels($arridade);

	// Setup the titles
	$graph->title->Set("Histórico");

	$graph->title->SetFont(FF_VERDANA, FS_BOLD, 8);
	$graph->title->SetColor('darkgray');
	$graph->yaxis->title->SetFont(FF_VERDANA, FS_NORMAL, 8);
	$graph->yaxis->title->SetColor('darkgray');
	$graph->yaxis->HideLine(true);
	$graph->yaxis->HideTicks(true);

	$graph->xaxis->title->SetFont(FF_VERDANA, FS_NORMAL, 8);
	$graph->xaxis->title->SetColor('darkgray');
	$graph->xaxis->HideLine(true);
	$graph->xaxis->HideTicks(true);

	$urlimg = "../tmp/graph/". session_id()."_".md5(uniqid(time())).".png";

	$graph->Stroke($urlimg);

	return $urlimg;
}

/*
 * Grafico HISTóRICO
 */
function grafhistorico($arrtit, $arrsem, $arrpadrao)
{
	include_once("../jpgraph/jpgraph.php");
	include_once("../jpgraph/jpgraph_line.php");
	include_once("../jpgraph/jpgraph_bar.php");

	//print_r($arrpadrao);
	$data1y = $arrtit[1];
	$data2y = $arrtit[2];
	$data3y = $arrtit[3];
	$data4y = $arrtit[4];
	$data5y = $arrtit[4];
	$data6y = $arrtit[6];
	$data7y = $arrtit[7];
	$data8y = $arrtit[8];
	$data9y = $arrtit[9];
	$data10y = $arrtit[10];
	$data11y = $arrtit[11];
	$data12y = $arrtit[12];
	$data13y = $arrtit[13];
	$data14y = $arrtit[14];
	$data15y = $arrtit[15];
	$data16y = $arrtit[16];
	$data17y = $arrtit[17];
	$data18y = $arrtit[18];
	$data19y = $arrtit[19];
	$data20y = $arrtit[20];
	$data21y = $arrtit[21];
	$data22y = $arrtit[22];
	$data23y = $arrtit[23];
	$data24y = $arrtit[24];
	$data25y = $arrtit[25];
	$data26y = $arrtit[26];
	$data27y = $arrtit[27];
	$data28y = $arrtit[28];
	//line1
	$ydata = $arrpadrao;

	// Create the graph. These two calls are always required
	$graph = new Graph(1400, 620);
	$graph->SetScale("textlin");
	$graph->SetMarginColor('white');
	$graph->SetFrame(true, 'silver', 1);
	$graph->xaxis->title->Set("Semanas");
	$graph->yaxis->title->Set("Título");
	$graph->yaxis->SetTitleMargin(30);
	$graph->yaxis->scale->SetGrace(10);
	$graph->yaxis->SetFont(FF_ARIAL, FS_NORMAL, 7); //comentar para funcionar no local
	$graph->yaxis->SetLabelMargin(3);
	// Adjust the margin a bit to make more room for titles
	$graph->img->SetMargin(50, 10, 5, 25);

	$graph->ygrid->SetFill(false);
	$graph->xaxis->SetTickLabels($arrsem);
	$graph->yaxis->HideLine(false);
	$graph->yaxis->HideTicks(false, false);

	if (!empty($arrpadrao)) {
		// Create the linear plot 
		$lineplot = new LinePlot($ydata);
		$lineplot->SetColor("red");
		$lineplot->SetWeight(3);
		$lineplot->SetBarCenter();

		$lineplot->SetCSIMTargets($targ, $alt);
	}

	// Create the bar plots and colors
	$b1plot = new BarPlot($data1y);
	$b1plot->SetFillColor('#ffff00'); //$b1plot->SetColor('#ffff00');
	$b2plot = new BarPlot($data2y);
	$b2plot->SetFillColor('#ffff00'); //$b2plot->SetColor('#ffff00');
	$b3plot = new BarPlot($data3y);
	$b3plot->SetFillColor('#ffff00'); //$b3plot->SetColor('#ffff00');
	$b4plot = new BarPlot($data4y);
	$b4plot->SetFillColor('#ffff00'); //$b4plot->SetColor('#ffff00');
	$b5plot = new BarPlot($data5y);
	$b5plot->SetFillColor('#ffff00'); //$b5plot->SetColor('#ffff00');
	$b6plot = new BarPlot($data6y);
	$b6plot->SetFillColor('#ffff00'); //$b6plot->SetColor('#ffff00');
	$b7plot = new BarPlot($data7y);
	$b7plot->SetFillColor('#ffff00'); //$b7plot->SetColor('#ffff00');
	$b8plot = new BarPlot($data8y);
	$b8plot->SetFillColor('#ffff00'); //$b8plot->SetColor('#ffff00');
	$b9plot = new BarPlot($data9y);
	$b9plot->SetFillColor('#ffff00'); //$b9plot->SetColor('#ffff00');
	$b10plot = new BarPlot($data10y);
	$b10plot->SetFillColor('#ffff00'); //$b10plot->SetColor('#ffff00');
	$b11plot = new BarPlot($data11y);
	$b11plot->SetFillColor('#ffff00'); //$b11plot->SetColor('#ffff00');
	$b12plot = new BarPlot($data12y);
	$b12plot->SetFillColor('#ffff00'); //$b12plot->SetColor('#ffff00');
	$b13plot = new BarPlot($data13y);
	$b13plot->SetFillColor('#ffff00'); //$b13plot->SetColor('#ffff00');
	$b14plot = new BarPlot($data14y);
	$b14plot->SetFillColor('#ffff00'); //$b14plot->SetColor('#ffff00');
	$b15plot = new BarPlot($data15y);
	$b15plot->SetFillColor('#ffff00'); //$b15plot->SetColor('#ffff00');
	$b16plot = new BarPlot($data16y);
	$b16plot->SetFillColor('#ffff00'); //$b16plot->SetColor('#ffff00');
	$b17plot = new BarPlot($data17y);
	$b17plot->SetFillColor('#ffff00'); //$b17plot->SetColor('#ffff00');
	$b18plot = new BarPlot($data18y);
	$b18plot->SetFillColor('#ffff00'); //$b18plot->SetColor('#ffff00');
	$b19plot = new BarPlot($data19y);
	$b19plot->SetFillColor('#ffff00'); //$b19plot->SetColor('#ffff00');
	$b20plot = new BarPlot($data20y);
	$b20plot->SetFillColor('#ffff00'); //$b20plot->SetColor('#ffff00');
	$b21plot = new BarPlot($data21y);
	$b21plot->SetFillColor('#ffff00'); //$b21plot->SetColor('#ffff00');
	$b22plot = new BarPlot($data22y);
	$b22plot->SetFillColor('#ffff00'); //$b22plot->SetColor('#ffff00');
	$b23plot = new BarPlot($data23y);
	$b23plot->SetFillColor('#ffff00'); //$b23plot->SetColor('#ffff00');
	$b24plot = new BarPlot($data24y);
	$b24plot->SetFillColor('#ffff00'); //$b24plot->SetColor('#ffff00');
	$b25plot = new BarPlot($data25y);
	$b25plot->SetFillColor('#ffff00'); //$b25plot->SetColor('#ffff00');
	$b26plot = new BarPlot($data26y);
	$b26plot->SetFillColor('#ffff00'); //$b26plot->SetColor('#ffff00');
	$b27plot = new BarPlot($data27y);
	$b27plot->SetFillColor('#ffff00'); //$b27plot->SetColor('#ffff00');
	$b28plot = new BarPlot($data28y);
	$b28plot->SetFillColor('#ffff00'); //$b28plot->SetColor('#ffff00');


	// Create the grouped bar plot
	$gbplot = new GroupBarPlot(array($b1plot, $b2plot, $b3plot, $b4plot, $b5plot, $b6plot, $b7plot, $b8plot, $b9plot, $b10plot, $b11plot, $b12plot, $b13plot, $b14plot, $b15plot, $b16plot, $b17plot, $b18plot, $b19plot, $b20plot, $b21plot, $b22plot, $b23plot, $b24plot, $b25plot, $b26plot, $b27plot, $b28plot));
	$gbplot->SetWidth(0.9);
	// ...and add it to the graPH
	$graph->Add($gbplot);
	//line
	if (!empty($arrpadrao)) {
		$graph->Add($lineplot);
	}
	$graph->title->Set("Histórico");
	$graph->title->SetFont(FF_FONT1, FS_BOLD);
	$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
	$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

	$urlimg = "../tmp/jpgraph/". session_id()."_".md5(uniqid(time())).".png";

	$graph->Stroke($urlimg);

	return $urlimg;
}


function relespecialanterior()
{
	null;
}

/*
 * gera a tabela e graficos para o elisa
 */
function relelisa1($idresultado, $idnucleo, $idpessoa, $idtipoteste, $tipoidade, $idespeciefinalidade, $mostraass, $ocultar, $textointerpretacao, $textopadrao)
{


	//Invoca variáveis do escopo superior
	global $irestotal;
	global $boopb;
	global $arrelisa;
	$boopb = false;

	//Quantidade de linhas do Elisa por pagina
	$qtlinhaselisa = 89;
	$quebratab = 0;
	$paginaquebra = 1;
	$iresultv = count($arrelisa);

	//echo $strsqlv; die("[".$iresultv."]");

	if ($iresultv > 0) {
		$arrelisav = array();
		$in = 0;
		foreach ($arrelisa as $i => $rowv) {

			//se for resultado da tabela de dados, armazenar em um array com um nivel a mais
			$in++;
			if ($rowv["local"] == "C") {

				//Se o numero de linhas alcancar o limite, aumenta o grupo e reseta o numero de linhas atual
				if ($quebratab == $qtlinhaselisa) {
					$arrelisav[$rowv["local"]][$paginaquebra][$in++]['footer'] = $paginaquebra+1;
					$paginaquebra++;
					$quebratab = 0;
				}
				//Somente incrementa o numero de linhas atual
				if ($quebratab < $qtlinhaselisa) {
					$quebratab++;
				}

				$arrelisav[$rowv["local"]][$paginaquebra][$in] = $rowv;
			} else {
				$arrelisav[$rowv["local"]][$in] = $rowv;
			}
		}
	} else {
		echo ("\nTeste de Elisa sem dados: [".$idresultado."]\n");
	}
	$arrelisav["C"][$paginaquebra+1][$in++]['footer'] = $paginaquebra+1;

	$irestotal = count($arrelisav["C"]);


	
	while (list($key, $tabelisa) = each($arrelisav["C"])) {
		if(!$boopb){
			grafelisa1($tabelisa,$arrelisav["R"],$idtipoteste,$comparativodelotes);
			resumoelisa1($textopadrao,$idtipoteste,$arrelisav["R"],$mostraass,$ocultar,$textointerpretacao);
		}
		relelisacorpo1($idresultado, $idnucleo, $idpessoa, $idtipoteste, $tipoidade, $idespeciefinalidade, $mostraass, $tabelisa, $arrelisav["R"], $textopadrao, $ocultar, $textointerpretacao);

		$boopb = true; //Indica inicio da quebra de paginas na segunda folha
	}
}

/*
 * gera a tabela e graficos para o elisa
 */
function grafelisa1($arrtabelisa,$arrtabresumo,$idtipoteste,$comparativodelotes){
	global $arrelisa, $arrelisagr1, $arrelisagr2, $templatecsv;
	global $ins_nomepartida, $ins_fabricante, $ins_partidaext, $ins_fabricacao, $ins_vencimento;
	global $csvgmt;
	$templateelisa = '';
			$iresult = count($arrelisa);
			if ($iresult > 0) {
				$tabelisa = array();
				// print_r($row );
				foreach ($arrelisa as $i => $row) {
					$tabelisa[$row["local"]][$row["nome"]] = $row;
				}
				$tabelisa["C"] = $arrtabelisa;
				$tabelisa["R"] = $arrtabresumo;
				$arrgraf1 = array();
				$linha = array();
				//MCC 11/05/2020
				
				$arrayTipoTesteElistaResult = array(1556, 670, 590, 6248, 11741, 4160, 8710, 38971);

				if ($idtipoteste == 3512) {
					// print_r($arrelisa);
					foreach ($arrelisa as $i => $row) {
						if (is_numeric($row['nome']) and $row['SP'] != '') {
							$number = str_replace(',', '.', $row['SP']);
							$arredondado = floor($number * 100) / 100;
							// echo $number.'*'.round($number,2).'*'.(floor($number * 100) / 100).'<br />';
							if ($arredondado >= 0.00 and $arredondado <= 0.09) {
								$linha[0]++;
							}
							if ($arredondado > 0.09 and $arredondado <= 0.19) {
								$linha[1]++;
							}
							if ($arredondado > 0.19 and $arredondado <= 0.29) {
								$linha[2]++;
							}
							if ($arredondado > 0.29 and $arredondado <= 0.39) {
								$linha[3]++;
							}
							if ($arredondado > 0.39 and $arredondado <= 0.49) {
								$linha[4]++;
							}
							if ($arredondado > 0.49 and $arredondado <= 0.59) {
								$linha[5]++;
							}
							if ($arredondado > 0.59 and $arredondado <= 0.69) {
								$linha[6]++;
							}
							if ($arredondado > 0.69 and $arredondado <= 0.79) {
								$linha[7]++;
							}
							if ($arredondado > 0.79 and $arredondado <= 0.89) {
								$linha[8]++;
							}
							if ($arredondado > 0.89 and $arredondado <= 0.99) {
								$linha[9]++;
							}
							if ($arredondado > 0.99 and $arredondado <= 1.09) {
								$linha[10]++;
							}
							if ($arredondado > 1.09 and $arredondado <= 1.19) {
								$linha[11]++;
							}
							if ($arredondado > 1.19 and $arredondado <= 1.29) {
								$linha[12]++;
							}
							if ($arredondado > 1.29 and $arredondado <= 1.39) {
								$linha[13]++;
							}
							if ($arredondado > 1.39 and $arredondado <= 1.49) {
								$linha[14]++;
							}
							if ($arredondado > 1.49 and $arredondado <= 1.59) {
								$linha[15]++;
							}
							if ($arredondado > 1.59 and $arredondado <= 1000) {
								$linha[16]++;
							}
						}
						//	echo round('0.699',2).'<br />';
						//	echo $number =  round(str_replace(',','.',$row['SP']),2).'<br />';
					}
					for ($c = 0; $c <= 16; $c++) {
						// echo $c.'-// echo $c.'-'.$linha[$c].'<br />' ;
						$arrgraf1[$c] = $linha[$c];
					}
				} elseif (in_array($idtipoteste, $arrayTipoTesteElistaResult)) {
					foreach ($arrelisa as $i => $row) {
						if ($row['result'] == 'Pos!') {
							$linha[0]++;
						} elseif ($row['result'] == 'Neg') {
							$linha[1]++;
						}
					}
					for ($c = 0; $c <= 1; $c++) {
						// echo $c.'-'.$linha[$c].'<br />' ;
						$arrgraf1[$c] = $linha[$c];
					}
				} elseif ($idtipoteste == 3484) {
					foreach ($arrelisa as $i => $row) {
						if ($row['result'] == 'Pos!') {
							$linha[0]++;
						} elseif ($row['result'] == 'Neg') {
							$linha[1]++;
						} elseif ($row['result'] == 'Sus*') {
							$linha[2]++;
						}
					}
					for ($c = 0; $c <= 2; $c++) {
						// echo $c.'-'.$linha[$c].'<br />' ;
						$arrgraf1[$c] = $linha[$c];
					}
				} else {
					foreach ($arrelisagr1 as $i => $row) {
						if ($row["grupo"] == '0') {
							$arrgraf1[(int)$row["grupo"]] = $row["quant"];
						} else {
							$arrgraf1[$row["grupo"]] = $row["quant"];
						}
					}
				}
				// print_r($arrgraf1);
				// #######################################################Dados para o segundo gráfico
				if (!empty($idnucleo) and !empty($idpessoa) and !empty($idtipoteste)) {
					$arrgraf2 = array();
					foreach ($arrelisagr2 as $i => $row) {
						$arrgraf2[$row["idade"]] = $row["gmt"] + 1;
					}
				}

				$arrayElisaResult = array(1556, 670, 590, 6248, 11741, 4160, 38971,8710);
				if ($idtipoteste == 3512) {
					$urlimg = geragrafelisaSP($arrgraf1);
				} elseif ($idtipoteste == 636 or $idtipoteste == 1455) {
					$urlimg = geragrafelisa4($arrgraf1);
				} elseif (in_array($idtipoteste, $arrayElisaResult)) {
					$urlimg = geragrafelisaRESULT($arrgraf1);
				} elseif ($idtipoteste == 3484) {
					$urlimg = geragrafelisaRESULTSUS($arrgraf1);
				} else {
					$urlimg = geragrafelisa1($arrgraf1);
				}
				$urlimg2 = geragrafelisagmt($arrgraf2);
				if (!empty($urlimg) or !empty($urlimg2)) {
					$templateelisa .= '
						<td align="" colspan="3" style="width:36%;  vertical-align:top">';
					if (!empty($urlimg)) {
						$templateelisa .= '
							<fieldset class="fset">
								<legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Gráfico *&nbsp;</font></legend> 
								<div class="resdesc" style="text-align:center;">
									<img src="'.$urlimg.'" style="padding-bottom:5px; height: 120px;width: 75%"  >
								</div>
							</fieldset>	';
					}
					if ($comparativodelotes == 'Y') {
						$templateelisa .= '
							<br />
							<fieldset class="fset">
								<legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Histórico *&nbsp;</font></legend> 
								<div class="resdesc" style="text-align:center;">';
						if (!empty($urlimg2)) {
							$templateelisa .= '
									<img src="'.$urlimg2.'" style="padding-bottom:5px;height: 120px;">';
						}
						$templateelisa .= '
								</div>
							</fieldset>	';
					}
					$templateelisa .= '
							</td>';
			}
		}
	
	echo $templateelisa;
}

function resumoelisa1($intextopadrao = false,$idtipoteste,$arrtabresumo,$mostraass,$ocultar,$textointerpretacao){
	global $arrelisa,$templatecsv,$irestotal;
	global $ins_nomepartida, $ins_fabricante, $ins_partidaext, $ins_fabricacao, $ins_vencimento;
	$iresult = count($arrelisa);
		if ($iresult > 0) {
			$tabelisa = array();
			// print_r($row );
			foreach ($arrelisa as $i => $row) {
				$tabelisa[$row["local"]][$row["nome"]] = $row;
			}
			$tabelisa["R"] = $arrtabresumo;
		}
	$templateelisa = '';
	$templateelisa .= '
									<br />
									<table style="width:100%; padding:0px;margin:auto;" class="tabelisa" >
										<tr class="hdr">
											<td colspan="3" class="tdrot grrot uppercase" style="text-align:center !important" >Resumo</td>
										</tr>
										<tr class="hdr">
											<td></td>
											<td align="center" class="tdrot grrot" style="text-align:center !important;">';
			if ($idtipoteste == 81) {
				$templateelisa .= "S/N";
			} else {
				$templateelisa .= "S/P";
			}
			$templateelisa .= "						</tr>";
			while (list($chave, $vlr) = each($tabelisa["R"])) {
				if (strtoupper($vlr["result"]) == "POS!") {
					$corback = "trpos";
				} else {
					$corback = "trnormal";
				}
				if ($vlr['nome'] == 'GMN') {
					$csvgmt = $vlr['titer'];
				}
				$templateelisa .= '
										<tr class="'.$corback.'">
											<td align="center" class="tdval grval">'.($vlr['nome']).'</td>
											<td align="center" class="tdval grval">'.($vlr['SP']).'</td>
											<td align="center" class="tdval grval">'.($vlr['titer']).'</td>
										</tr>';
				$templatecsv .=	'"Nome: '.$vlr['nome'].' " "S/P: '.$vlr['SP'].'" "TITER: '.$vlr['titer'];
			}
			$templateelisa .= '
									</table>
								</div>
							</fieldset>';
			if (!empty($textointerpretacao) and $textointerpretacao != " ") {
				$templateelisa .= '
							<br />
								<fieldset class="fset">
								<legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Interpretação *&nbsp;</font></legend>
								<div class="resdesc">
									<div id="fraseedicao" class="divfrase" style="width:100%">'.$textointerpretacao.'
										<input id="idfrasedit" type="hidden" value="'.$textointerpretacao.'">
									</div>';
				if (!empty($row["idade"]) and !empty($row["tipoidade"])) {
					$templateelisa .= '
									<table class="tablegenda"style="width:100%;text-transform:none">
										<tr>
											<td>* Para inserção da interpretação não foram considerados registros posteriores a '.($row["idade"]).' '.($row["tipoidade"]).'</td>
										</tr>
									</table>';
				}
				$templateelisa .= '
								</div>
							</fieldset>';
			} elseif ($mostraass == false) {
				$templateelisa .= '

							<br /> 											 
							<fieldset class="fset">
								<legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Interpretação *&nbsp;</font></legend>
								<div class="resdesc">
									<div id="fraseedicao" class="divfrase" style="width:100%"><textarea  rows="5" cols="40" id="idfrasedit" tabindex="1">'.($textointerpretacao).'</textarea></div>
									<table class="tablegenda" style="width:100%;text-transform:none">
										<tr>
											<td>* Para inserção da interpretação não foram considerados registros posteriores a '.($row["idade"]).' '.($row["tipoidade"]).'</td>
										</tr>
									</table>
								</div>
							</fieldset>';
			}
			//MCC 19/11/2019 - Comentado o trecho sobre a condição que obrigava o campo textopadrao ser diferente de vazio.
			//if (trim($intextopadrao) !== "" and $ocultar != 0) {
			if ($ocultar != 0) {
				$templateelisa .= '  	<br />
							<fieldset class="fset" style="text-align:left">
								<legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Considerações *&nbsp;</font></legend>';


				$x = 1;
				//echo 'mcc.'.count($ins_nomepartida);

				if (count($ins_partidaext) > 0) {
					$templateelisa .= '<table><tr><td>';


					while ($x <= count($ins_partidaext)) {
						if ($x % 2 == 0) {
							$bor = 'border-right:1px dashed #eee;';
						} else {
							$bor = '';
						}

						if ($x > 1) {
							$bot = 'border-top:1px dashed #eee;';
						} else {
							$bot = '';
						}

						$templateelisa .= '<ul style="padding-left:4px !important; '.$bot.' '.$bor.'  padding:4px;  float:left;list-style: none; width:47%; min-width:30px;vertical-align:top; margin-bottom:0px; padding-left:0px; float:left; margin-top:0px;font-size:6px !important;">';
						$templateelisa .= '<li>PARTIDA DE '.$ins_nomepartida[$x].'</li>';
						$templateelisa .= '<li>FABRICANTE: '.$ins_fabricante[$x].'</li>';
						$templateelisa .= '<li>PARTIDA: '.$ins_partidaext[$x].'</li>';
						$templateelisa .= '<li>FABRICAÇÃO: '.$ins_fabricacao[$x].'</li>';
						$templateelisa .= '<li>VENCIMENTO: '.$ins_vencimento[$x].'</li>';
						$templateelisa .= '</ul>';

						$x++;
					}

					if ($x % 2 != 0) {
						if ($x % 2 == 0) {
							$bor = 'border-right:1px dashed #eee;';
						} else {
							$bor = '';
						}

						if ($x > 1) {
							$bot = 'border-top:1px dashed #eee;';
						} else {
							$bot = '';
						}

						$templateelisa .= '<ul style="padding-left:4px !important; '.$bot.' '.$bor.'  padding:4px;  float:left;list-style: none; width:47%; min-width:30px;vertical-align:top; margin-bottom:0px; padding-left:0px; float:left; margin-top:0px;font-size:6px !important;">';
						$templateelisa .= '<li></li>';

						$templateelisa .= '</ul>';
					}
					$templateelisa .= '</td></tr></table>';
				}

				$templateelisa .= '
								<div class="resdesc">'.preg_replace('/<(\w+) [^>]+>/', '<$1>', $intextopadrao).'</div>
							</fieldset>';
			}
			$templateelisa .= '<tr><td colspan="4"><div class="resdesc" style="text-align:center;"><div class=""><br>Página 1 de '.$irestotal.'</div></div></td></tr>';
	echo $templateelisa;
}

function relelisacorpo1($idresultado, $idnucleo, $idpessoa, $idtipoteste, $tipoidade, $idespeciefinalidade, $mostraass, $arrtabelisa, $arrtabresumo, $intextopadrao = false, $ocultar, $textointerpretacao)
{
	global $arrelisa, $arrelisagr1, $arrelisagr2, $templatecsv,$irestotal;
	global $csvgmt;
	// verifica se trata-se de uma amostra de DIAS. Caso positivo, nao mostrar segundo grafico. A pedido de Andre 271009.
	$booldia = strpos(strtoupper($tipoidade), "DIA");
	if ($booldia === false) { //Atenção para utilização do '===': $boolsem retorna como encontrado na posição 0 a string procurada. 0=False, portanto, com === se força a tratar somente booleanos
		$booldia = false;
	} else {
		$booldia = true;
	}
	if (empty($idresultado) or empty($idpessoa) or empty($idtipoteste)) {
		echo "--> Parâmetros para gr&aacute;fico Elisa est&atilde;o incompletos (Page Source). <br />A amostra n&atilde;o possui informa&ccedil;&atilde;o de [Cliente] ou [Teste]";
		echo "<!-- ";
		print_r(func_get_args());
		echo " -->";
	} else {
		// ######################################################Dados para a tabela
		$iresult = count($arrelisa);
		if ($iresult > 0) {
			$tabelisa = array();
			// print_r($row );
			foreach ($arrelisa as $i => $row) {
				$tabelisa[$row["local"]][$row["nome"]] = $row;
			}
			$tabelisa["C"] = $arrtabelisa;
			$tabelisa["R"] = $arrtabresumo;
			$arrgraf1 = array();
			$linha = array();
			//MCC 11/05/2020
			//A pedido do José Branco, foi removido o idtipoteste 3305 da condição abaixo (Deixou de apresentar S/P no gráfico para apresentar por Grupo (ELISA)
			//if ($idtipoteste == 3305 or $idtipoteste == 3512 or $idtipoteste == 4160) {

			$arrayTipoTesteElistaResult = array(1556, 670, 590, 6248, 11741, 4160, 8710, 38971);

			if ($idtipoteste == 3512) {
				// print_r($arrelisa);
				foreach ($arrelisa as $i => $row) {
					if (is_numeric($row['nome']) and $row['SP'] != '') {
						$number = str_replace(',', '.', $row['SP']);
						$arredondado = floor($number * 100) / 100;
						// echo $number.'*'.round($number,2).'*'.(floor($number * 100) / 100).'<br />';
						if ($arredondado >= 0.00 and $arredondado <= 0.09) {
							$linha[0]++;
						}
						if ($arredondado > 0.09 and $arredondado <= 0.19) {
							$linha[1]++;
						}
						if ($arredondado > 0.19 and $arredondado <= 0.29) {
							$linha[2]++;
						}
						if ($arredondado > 0.29 and $arredondado <= 0.39) {
							$linha[3]++;
						}
						if ($arredondado > 0.39 and $arredondado <= 0.49) {
							$linha[4]++;
						}
						if ($arredondado > 0.49 and $arredondado <= 0.59) {
							$linha[5]++;
						}
						if ($arredondado > 0.59 and $arredondado <= 0.69) {
							$linha[6]++;
						}
						if ($arredondado > 0.69 and $arredondado <= 0.79) {
							$linha[7]++;
						}
						if ($arredondado > 0.79 and $arredondado <= 0.89) {
							$linha[8]++;
						}
						if ($arredondado > 0.89 and $arredondado <= 0.99) {
							$linha[9]++;
						}
						if ($arredondado > 0.99 and $arredondado <= 1.09) {
							$linha[10]++;
						}
						if ($arredondado > 1.09 and $arredondado <= 1.19) {
							$linha[11]++;
						}
						if ($arredondado > 1.19 and $arredondado <= 1.29) {
							$linha[12]++;
						}
						if ($arredondado > 1.29 and $arredondado <= 1.39) {
							$linha[13]++;
						}
						if ($arredondado > 1.39 and $arredondado <= 1.49) {
							$linha[14]++;
						}
						if ($arredondado > 1.49 and $arredondado <= 1.59) {
							$linha[15]++;
						}
						if ($arredondado > 1.59 and $arredondado <= 1000) {
							$linha[16]++;
						}
					}
					//	echo round('0.699',2).'<br />';
					//	echo $number =  round(str_replace(',','.',$row['SP']),2).'<br />';
				}
				for ($c = 0; $c <= 16; $c++) {
					// echo $c.'-// echo $c.'-'.$linha[$c].'<br />' ;
					$arrgraf1[$c] = $linha[$c];
				}
			} elseif (in_array($idtipoteste, $arrayTipoTesteElistaResult)) {
				foreach ($arrelisa as $i => $row) {
					if ($row['result'] == 'Pos!') {
						$linha[0]++;
					} elseif ($row['result'] == 'Neg') {
						$linha[1]++;
					}
				}
				for ($c = 0; $c <= 1; $c++) {
					// echo $c.'-'.$linha[$c].'<br />' ;
					$arrgraf1[$c] = $linha[$c];
				}
			} elseif ($idtipoteste == 3484) {
				foreach ($arrelisa as $i => $row) {
					if ($row['result'] == 'Pos!') {
						$linha[0]++;
					} elseif ($row['result'] == 'Neg') {
						$linha[1]++;
					} elseif ($row['result'] == 'Sus*') {
						$linha[2]++;
					}
				}
				for ($c = 0; $c <= 2; $c++) {
					// echo $c.'-'.$linha[$c].'<br />' ;
					$arrgraf1[$c] = $linha[$c];
				}
			} else {
				foreach ($arrelisagr1 as $i => $row) {
					if ($row["grupo"] == '0') {
						$arrgraf1[(int)$row["grupo"]] = $row["quant"];
					} else {
						$arrgraf1[$row["grupo"]] = $row["quant"];
					}
				}
			}
			// print_r($arrgraf1);
			// #######################################################Dados para o segundo gráfico
			if (!empty($idnucleo) and !empty($idpessoa) and !empty($idtipoteste)) {
				$arrgraf2 = array();
				foreach ($arrelisagr2 as $i => $row) {
					$arrgraf2[$row["idade"]] = $row["gmt"] + 1;
				}
			}
			// print_r($tabelisa["C"]); echo "<br />"	;
			// echo count($tabelisa["C"]);die;
			$templateelisa = '	<tr class="trquebra" style="page-break-inside: avoid">
						<td style="vertical-align: top; width:64%" colspan="4">
							<fieldset class="fset" style="border:none;">
								<div class="resdesc" style="text-align:center;">
									<div style="width:100%; background-color:#f7f7f7; height:14px; vertical-align:middle" class="trelisa '.$corback.'">
										<div class="relisa">&nbsp;</div>
										<div class="relisa">Well</div>
										<div class="relisa">O.D.</div>
										<div class="relisa">I.E.</div>
										<div class="relisa">S/P</div>
										<div class="relisa">S/N</div>
										<div class="relisa">Titer</div>
										<div class="relisa">Group</div>
										<div class="relisa">Result</div>
									</div>';
			$templateauxelisa="";
			$templateauxelisafooter="";
			while (list($chave, $vlr) = each($tabelisa["C"])) {
				if (strtoupper($vlr["result"]) == "POS!") {
					$corback = "trpos";
				} else {
					$corback = "trnormal";
				}
				// maf110314: A pedido de Andre, SPs com zero devem ser mostrados
				$vc1 = (!empty($vlr['SP']) or $vlr['SP'] == 0) ? 1 : 0;
				$vc2 = (!empty($vlr['SN'])) ? 1 : 0;
				$vc3 = (!empty($vlr['titer'])) ? 1 : 0;
				$vc4 = (!empty($vlr['grupo'])) ? 1 : 0;
				$vc5 = (!empty($vlr['result'])) ? 1 : 0;
				$vcr = $vc1 + $vc2 + $vc3 + $vc4 + $vc5; //quantidade de colunas preenchidas. Isto evita mostrar lixo de RTF
				if (($vcr >= 2 and $vlr['nome'] != "Well" and $vlr['well'] != "O.D.") or (strtoupper($vlr['nome']) == "NEG" or strtoupper($vlr['nome']) == "POS")) { //Nao mostrar lixo
					$templateauxelisa .= '
									<div style="width:100%;" class="trelisa '.$corback.'"> 
										<div class="relisa">'.$vlr['nome'].'</div>  
										<div class="relisa">'.$vlr['well'].'</div>
										<div class="relisa">'.$vlr['OD'].'</div>
										<div class="relisa">'.$vlr['IE'].'</div>
										<div class="relisa">';
					if ($vlr['local'] == 'C' and (empty($vlr['SP']) and $vlr['SP'] != 0)) {
						$templateauxelisa .= '-';
						$_sp = '-';
					} else {
						$templateauxelisa .= $vlr['SP'];
						$_sp = $vlr['SP'];
					}
					$templateauxelisa .= '				</div><div class="relisa">';
					if ($vlr['local'] == 'C' and empty($vlr['SN'])) {
						$templateauxelisa .= '-';
						$_SN = '-';
					} else {
						$templateauxelisa .= $vlr['SN'];
						$_SN = $vlr['SN'];
					}
					$templateauxelisa .= '				</div><div class="relisa">';
					if ($vlr['local'] == 'C' and empty($vlr['titer'])) {
						$templateauxelisa .= '-';
						$_titer = '-';
					} else {
						$templateauxelisa .= $vlr['titer'];
						$_titer = $vlr['titer'];
					}
					$templateauxelisa .= '				</div><div class="relisa">';
					if ($vlr['local'] == 'C' and !strlen($vlr['grupo'])) {
						$templateauxelisa .= '-';
						$_grupo = '-';
					} else {
						$templateauxelisa .= $vlr['grupo'];
						$_grupo = $vlr['grupo'];
					}
					$templateauxelisa .= '				</div><div class="relisa">';
					if ($vlr['local'] == 'C' and empty($vlr['result'])) {
						$templateauxelisa .= '-';
						$_result = '-';
					} else {
						$templateauxelisa .= $vlr['result'];
						$_result = $vlr['result'];
					}
					$templateauxelisa .= '				</div>
									</div>';
					$templatecsv .= '"Nome: '.$vlr['nome'].' " "Well: '.$vlr['well'].'" "O.D.: '.$vlr['OD'].' " "S/P: '.$_sp.' " "S/N: '.$_sn.'"  "TITER: '.$_titer.'"\n "GROUP: '.$_grupo.'" "RESULT: '.$_result.'", ';
				}
				if(($vlr['footer'])){
					$templateauxelisafooter .= '<div class="trelisa"><div class="trelisa trnormal"><br>Página '.$vlr['footer'].' de '.$irestotal.'</div></div>';

				}
			}
			if(!empty($templateauxelisa)){
				$templateelisa .=$templateauxelisa;
				$templateelisa .= $templateauxelisafooter;
				$templateelisa .= '</td>';
			}else{
				$templateelisa ="<tr>";
				$templateelisa .='<td style="vertical-align: top; width:64%" colspan="4">';
				$templateelisa .='<div class="resdesc" style="text-align:center;">';
				$templateelisa .= $templateauxelisafooter;
				$templateelisa .="<br>";
				$templateelisa .="<br>";
				$templateelisa .="</div>";
				$templateelisa .="</td>";
				$templateelisa .="</tr>";
			}
			//MCC 11/05/2020
			//A pedido do José Branco, foi removido o idtipoteste 3305 da condição abaixo (Deixou de apresentar S/P no gráfico para apresentar por Grupo (ELISA)
			//if ($idtipoteste == 3305 or $idtipoteste == 3512 or $idtipoteste == 4160) 
			// if ($idtipoteste == 3512) {
			// 	$urlimg = geragrafelisaSP($arrgraf1);
			// } elseif ($idtipoteste == 636 or $idtipoteste == 1455) {
			// 	$urlimg = geragrafelisa4($arrgraf1);
			// } elseif ($idtipoteste == 1556 or $idtipoteste == 670 or $idtipoteste == 590 or $idtipoteste == 6248  or  $idtipoteste == 11741 or $idtipoteste == 4160) {
			// 	$urlimg = geragrafelisaRESULT($arrgraf1);
			// } elseif ($idtipoteste == 3484) {
			// 	$urlimg = geragrafelisaRESULTSUS($arrgraf1);
			// } else {
			// 	$urlimg = geragrafelisa($arrgraf1);
			// }
			// $urlimg2 = geragrafelisagmt($arrgraf2);
			// if (!empty($urlimg) or !empty($urlimg2)) {
			// 	$templateelisa .= '
			// 		<td style="width:36%;  vertical-align:top">';
			// 	if (!empty($urlimg)) {
			// 		$templateelisa .= '
			// 			<fieldset class="fset">
			// 				<legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Gráfico *&nbsp;</font></legend> 
			// 				<div class="resdesc" style="text-align:center;">
			// 					<img src="'.$urlimg.'" style="padding-bottom:5px; height: 120px;"  >
			// 				</div>
			// 			</fieldset>	';
			// 	}
			// 	if ($comparativodelotes == 'Y') {
			// 		$templateelisa .= '
			// 			<br />
			// 			<fieldset class="fset">
			// 				<legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Histórico *&nbsp;</font></legend> 
			// 				<div class="resdesc" style="text-align:center;">';
			// 		if (!empty($urlimg2)) {
			// 			$templateelisa .= '
			// 					<img src="'.$urlimg2.'" style="padding-bottom:5px;height: 120px;">';
			// 		}
			// 		$templateelisa .= '
			// 				</div>
			// 			</fieldset>	';
			// 	}
			// 	$templateelisa .= '
			// 			</td>';
			// }
			// $templateelisa .= '
			// 		</tr>';
		}
	}
	if (empty($_REQUEST['csv'])) {
		echo $templateelisa;
	}
}
function relelisa($idresultado, $idnucleo, $idpessoa, $idtipoteste, $tipoidade, $idespeciefinalidade, $mostraass, $ocultar, $textointerpretacao, $textopadrao)
{
	//Invoca variáveis do escopo superior
	global $irestotal;
	global $boopb;
	global $arrelisa;





	//Quantidade de linhas do Elisa por pagina
	$qtlinhaselisa = 35;
	$quebratab = 0;
	$paginaquebra = 1;
	$iresultv = count($arrelisa);

	//echo $strsqlv; die("[".$iresultv."]");

	if ($iresultv > 0) {
		$arrelisav = array();
		$in = 0;
		foreach ($arrelisa as $i => $rowv) {

			//se for resultado da tabela de dados, armazenar em um array com um nivel a mais
			$in++;
			if ($rowv["local"] == "C") {

				//Se o numero de linhas alcancar o limite, aumenta o grupo e reseta o numero de linhas atual
				if ($quebratab == $qtlinhaselisa) {

					$paginaquebra++;
					$quebratab = 0;
				}
				//Somente incrementa o numero de linhas atual
				if ($quebratab < $qtlinhaselisa) {
					$quebratab++;
				}

				$arrelisav[$rowv["local"]][$paginaquebra][$in] = $rowv;
			} else {
				$arrelisav[$rowv["local"]][$in] = $rowv;
			}
		}
	} else {

			
		$sqla = "select caminho from arquivo where idobjeto = '".$idresultado."' and tipoobjeto = 'resultado'  and nome like ('%.pdf')";
		$resa = mysql_query($sqla);

		$qtdarq=mysql_num_rows($resa);
		if($qtdarq>0){
		?>
			
		<?
			echo '<div>';
			while($rowa = mysql_fetch_assoc($resa)){
				$row["caminho"] = $rowa['caminho'];
		
				if (file_exists($row["caminho"])) {
					echo '<a href="'.$row["caminho"].'" target="_blank"><img src="../inc/img/pdf-icon2.png" ></a> &nbsp;&nbsp; &nbsp; ';
				}
			}
			echo '</div>';
		?>
			
		<?
		}else{
			echo ("\nTeste de Elisa sem dados: [".$idresultado."]\n");
		}
	
	}


	$irestotal = count($arrelisav["C"]);

	while (list($key, $tabelisa) = each($arrelisav["C"])) {

		relelisacorpo($idresultado, $idnucleo, $idpessoa, $idtipoteste, $tipoidade, $idespeciefinalidade, $mostraass, $tabelisa, $arrelisav["R"], $textopadrao, $ocultar, $textointerpretacao);

		$boopb = true; //Indica inicio da quebra de paginas na segunda folha
	}
}

/*
 * gera a tabela e graficos para o elisa
 */

function relelisacorpo($idresultado, $idnucleo, $idpessoa, $idtipoteste, $tipoidade, $idespeciefinalidade, $mostraass, $arrtabelisa, $arrtabresumo, $intextopadrao = false, $ocultar, $textointerpretacao)
{
	global $arrelisa, $arrelisagr1, $arrelisagr2, $templatecsv;
	global $ins_nomepartida, $ins_fabricante, $ins_partidaext, $ins_fabricacao, $ins_vencimento;
	global $csvgmt;


	// verifica se trata-se de uma amostra de DIAS. Caso positivo, nao mostrar segundo grafico. A pedido de Andre 271009.
	$booldia = strpos(strtoupper($tipoidade), "DIA");
	if ($booldia === false) { //Atenção para utilização do '===': $boolsem retorna como encontrado na posição 0 a string procurada. 0=False, portanto, com === se força a tratar somente booleanos
		$booldia = false;
	} else {
		$booldia = true;
	}
	if (empty($idresultado) or empty($idpessoa) or empty($idtipoteste)) {
		echo "--> Parâmetros para gr&aacute;fico Elisa est&atilde;o incompletos (Page Source). <br />A amostra n&atilde;o possui informa&ccedil;&atilde;o de [Cliente] ou [Teste]";
		echo "<!-- ";
		print_r(func_get_args());
		echo " -->";
	} else {
		// ######################################################Dados para a tabela
		$iresult = count($arrelisa);
		if ($iresult > 0) {
			$tabelisa = array();
			// print_r($row );
			foreach ($arrelisa as $i => $row) {
				$tabelisa[$row["local"]][$row["nome"]] = $row;
			}
			$tabelisa["C"] = $arrtabelisa;
			$tabelisa["R"] = $arrtabresumo;
			$arrgraf1 = array();
			$linha = array();
			//MCC 11/05/2020
			//A pedido do José Branco, foi removido o idtipoteste 3305 da condição abaixo (Deixou de apresentar S/P no gráfico para apresentar por Grupo (ELISA)
			//if ($idtipoteste == 3305 or $idtipoteste == 3512 or $idtipoteste == 4160) {

			$arrayTipoTesteElistaResult = array(1556, 670, 590, 6248, 11741, 4160, 8710, 38971);

			if ($idtipoteste == 3512) {
				// print_r($arrelisa);
				foreach ($arrelisa as $i => $row) {
					if (is_numeric($row['nome']) and $row['SP'] != '') {
						$number = str_replace(',', '.', $row['SP']);
						$arredondado = floor($number * 100) / 100;
						// echo $number.'*'.round($number,2).'*'.(floor($number * 100) / 100).'<br />';
						if ($arredondado >= 0.00 and $arredondado <= 0.09) {
							$linha[0]++;
						}
						if ($arredondado > 0.09 and $arredondado <= 0.19) {
							$linha[1]++;
						}
						if ($arredondado > 0.19 and $arredondado <= 0.29) {
							$linha[2]++;
						}
						if ($arredondado > 0.29 and $arredondado <= 0.39) {
							$linha[3]++;
						}
						if ($arredondado > 0.39 and $arredondado <= 0.49) {
							$linha[4]++;
						}
						if ($arredondado > 0.49 and $arredondado <= 0.59) {
							$linha[5]++;
						}
						if ($arredondado > 0.59 and $arredondado <= 0.69) {
							$linha[6]++;
						}
						if ($arredondado > 0.69 and $arredondado <= 0.79) {
							$linha[7]++;
						}
						if ($arredondado > 0.79 and $arredondado <= 0.89) {
							$linha[8]++;
						}
						if ($arredondado > 0.89 and $arredondado <= 0.99) {
							$linha[9]++;
						}
						if ($arredondado > 0.99 and $arredondado <= 1.09) {
							$linha[10]++;
						}
						if ($arredondado > 1.09 and $arredondado <= 1.19) {
							$linha[11]++;
						}
						if ($arredondado > 1.19 and $arredondado <= 1.29) {
							$linha[12]++;
						}
						if ($arredondado > 1.29 and $arredondado <= 1.39) {
							$linha[13]++;
						}
						if ($arredondado > 1.39 and $arredondado <= 1.49) {
							$linha[14]++;
						}
						if ($arredondado > 1.49 and $arredondado <= 1.59) {
							$linha[15]++;
						}
						if ($arredondado > 1.59 and $arredondado <= 1000) {
							$linha[16]++;
						}
					}
					//	echo round('0.699',2).'<br />';
					//	echo $number =  round(str_replace(',','.',$row['SP']),2).'<br />';
				}
				for ($c = 0; $c <= 16; $c++) {
					// echo $c.'-// echo $c.'-'.$linha[$c].'<br />' ;
					$arrgraf1[$c] = $linha[$c];
				}
			} elseif (in_array($idtipoteste, $arrayTipoTesteElistaResult)) {
				foreach ($arrelisa as $i => $row) {
					if ($row['result'] == 'Pos!') {
						$linha[0]++;
					} elseif ($row['result'] == 'Neg') {
						$linha[1]++;
					}
				}
				for ($c = 0; $c <= 1; $c++) {
					// echo $c.'-'.$linha[$c].'<br />' ;
					$arrgraf1[$c] = $linha[$c];
				}
			} elseif ($idtipoteste == 3484) {
				foreach ($arrelisa as $i => $row) {
					if ($row['result'] == 'Pos!') {
						$linha[0]++;
					} elseif ($row['result'] == 'Neg') {
						$linha[1]++;
					} elseif ($row['result'] == 'Sus*') {
						$linha[2]++;
					}
				}
				for ($c = 0; $c <= 2; $c++) {
					// echo $c.'-'.$linha[$c].'<br />' ;
					$arrgraf1[$c] = $linha[$c];
				}
			} else {
				foreach ($arrelisagr1 as $i => $row) {
					if ($row["grupo"] == '0') {
						$arrgraf1[(int)$row["grupo"]] = $row["quant"];
					} else {
						$arrgraf1[$row["grupo"]] = $row["quant"];
					}
				}
			}
			// print_r($arrgraf1);
			// #######################################################Dados para o segundo gráfico
			if (!empty($idnucleo) and !empty($idpessoa) and !empty($idtipoteste)) {
				$arrgraf2 = array();
				foreach ($arrelisagr2 as $i => $row) {
					$arrgraf2[$row["idade"]] = $row["gmt"] + 1;
				}
			}
			// print_r($tabelisa["C"]); echo "<br />"	;
			// echo count($tabelisa["C"]);die;
			$templateelisa = '	<tr>
						<td style="vertical-align: top; width:64%">
							<fieldset class="fset" style="border:none;">
								<div class="resdesc" style="text-align:center;">
									<div style="width:100%; background-color:#f7f7f7; height:14px; vertical-align:middle" class="trelisa '.$corback.'">
										<div class="relisa">&nbsp;</div>
										<div class="relisa">Well</div>
										<div class="relisa">O.D.</div>
										<div class="relisa">I.E.</div>
										<div class="relisa">S/P</div>
										<div class="relisa">S/N</div>
										<div class="relisa">Titer</div>
										<div class="relisa">Group</div>
										<div class="relisa">Result</div>
									</div>';
									
			$arrayTipoTesteElistaResult = array(1556, 670, 590, 6248, 11741, 4160, 8710, 38971);


			while (list($chave, $vlr) = each($tabelisa["C"])) {
				if (strtoupper($vlr["result"]) == "POS!") {
					$corback = "trpos";
				} else {
					$corback = "trnormal";
				}
				// maf110314: A pedido de Andre, SPs com zero devem ser mostrados
				$vc1 = (!empty($vlr['SP']) or $vlr['SP'] == 0) ? 1 : 0;
				$vc2 = (!empty($vlr['SN'])) ? 1 : 0;
				$vc3 = (!empty($vlr['titer'])) ? 1 : 0;
				$vc4 = (!empty($vlr['grupo'])) ? 1 : 0;
				$vc5 = (!empty($vlr['result'])) ? 1 : 0;
				$vcr = $vc1 + $vc2 + $vc3 + $vc4 + $vc5; //quantidade de colunas preenchidas. Isto evita mostrar lixo de RTF
				if (($vcr >= 2 and $vlr['nome'] != "Well" and $vlr['well'] != "O.D.") or (strtoupper($vlr['nome']) == "NEG" or strtoupper($vlr['nome']) == "POS")) { //Nao mostrar lixo
					$templateelisa .= '
									<div style="width:100%;" class="trelisa '.$corback.'"> 
										<div class="relisa">'.$vlr['nome'].'</div>  
										<div class="relisa">'.$vlr['well'].'</div>
										<div class="relisa">'.$vlr['OD'].'</div>
										<div class="relisa">'.$vlr['IE'].'</div>
										<div class="relisa">';
					if ($vlr['local'] == 'C' and (empty($vlr['SP']) and $vlr['SP'] != 0)) {
						$templateelisa .= '-';
						$_sp = '-';
					} else {
						$templateelisa .= $vlr['SP'];
						$_sp = $vlr['SP'];
					}
					$templateelisa .= '				</div><div class="relisa">';
					if ($vlr['local'] == 'C' and empty($vlr['SN'])) {
						$templateelisa .= '-';
						$_SN = '-';
					} else {
						$templateelisa .= $vlr['SN'];
						$_SN = $vlr['SN'];
					}
					$templateelisa .= '				</div><div class="relisa">';
					if ($vlr['local'] == 'C' and empty($vlr['titer'])) {
						$templateelisa .= '-';
						$_titer = '-';
					} else {
						$templateelisa .= $vlr['titer'];
						$_titer = $vlr['titer'];
					}
					$templateelisa .= '				</div><div class="relisa">';
					if ($vlr['local'] == 'C' and !strlen($vlr['grupo'])) {
						$templateelisa .= '-';
						$_grupo = '-';
					} else {
						$templateelisa .= $vlr['grupo'];
						$_grupo = $vlr['grupo'];
					}
					$templateelisa .= '				</div><div class="relisa">';
					if ($vlr['local'] == 'C' and empty($vlr['result'])) {
						$templateelisa .= '-';
						$_result = '-';
					} else {
						$templateelisa .= $vlr['result'];
						$_result = $vlr['result'];
					}
					$templateelisa .= '				</div>
									</div>';
					$templatecsv .= '"Nome: '.$vlr['nome'].' " "Well: '.$vlr['well'].'" "O.D.: '.$vlr['OD'].' " "S/P: '.$_sp.' " "S/N: '.$_sn.'"  "TITER: '.$_titer.'"\n "GROUP: '.$_grupo.'" "RESULT: '.$_result.'", ';
				}
			}
			$templateelisa .= '
									<br />
									<table style="width:100%; padding:0px;margin:auto;" class="tabelisa" >
										<tr class="hdr">
											<td colspan="3" class="tdrot grrot uppercase" style="text-align:center !important" >Resumo</td>
										</tr>
										<tr class="hdr">
											<td></td>
											<td align="center" class="tdrot grrot" style="text-align:center !important;">';
			if ($idtipoteste == 81) {
				$templateelisa .= "S/N";
			} else {
				$templateelisa .= "S/P";
			}
			$templateelisa .= "						</tr>";
			while (list($chave, $vlr) = each($tabelisa["R"])) {
				if (strtoupper($vlr["result"]) == "POS!") {
					$corback = "trpos";
				} else {
					$corback = "trnormal";
				}
				if ($vlr['nome'] == 'GMN') {
					$csvgmt = $vlr['titer'];
				}
				$templateelisa .= '
										<tr class="'.$corback.'">
											<td align="center" class="tdval grval">'.($vlr['nome']).'</td>
											<td align="center" class="tdval grval">'.($vlr['SP']).'</td>
											<td align="center" class="tdval grval">'.($vlr['titer']).'</td>
										</tr>';
				$templatecsv .=	'"Nome: '.$vlr['nome'].' " "S/P: '.$vlr['SP'].'" "TITER: '.$vlr['titer'];
			}
			$templateelisa .= '
									</table>
								</div>
							</fieldset>';
			if (!empty($textointerpretacao) and $textointerpretacao != " ") {
				$templateelisa .= '
							<br />
								<fieldset class="fset">
								<legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Interpretação *&nbsp;</font></legend>
								<div class="resdesc">
									<div id="fraseedicao" class="divfrase" style="width:100%">'.$textointerpretacao.'
										<input id="idfrasedit" type="hidden" value="'.$textointerpretacao.'">
									</div>';
				if (!empty($row["idade"]) and !empty($row["tipoidade"])) {
					$templateelisa .= '
									<table class="tablegenda"style="width:100%;text-transform:none">
										<tr>
											<td>* Para inserção da interpretação não foram considerados registros posteriores a '.($row["idade"]).' '.($row["tipoidade"]).'</td>
										</tr>
									</table>';
				}
				$templateelisa .= '
								</div>
							</fieldset>';
			} elseif ($mostraass == false) {
				$templateelisa .= '

							<br /> 											 
							<fieldset class="fset">
								<legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Interpretação *&nbsp;</font></legend>
								<div class="resdesc">
									<div id="fraseedicao" class="divfrase" style="width:100%"><textarea  rows="5" cols="40" id="idfrasedit" tabindex="1">'.($textointerpretacao).'</textarea></div>
									<table class="tablegenda" style="width:100%;text-transform:none">
										<tr>
											<td>* Para inserção da interpretação não foram considerados registros posteriores a '.($row["idade"]).' '.($row["tipoidade"]).'</td>
										</tr>
									</table>
								</div>
							</fieldset>';
			}
			//MCC 19/11/2019 - Comentado o trecho sobre a condição que obrigava o campo textopadrao ser diferente de vazio.
			//if (trim($intextopadrao) !== "" and $ocultar != 0) {
			if ($ocultar != 0) {
				$templateelisa .= '  	<br />
							<fieldset class="fset" style="text-align:left">
								<legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Considerações *&nbsp;</font></legend>';


				$x = 1;
				//echo 'mcc.'.count($ins_nomepartida);

				if (count($ins_partidaext) > 0) {
					$templateelisa .= '<table><tr><td>';


					while ($x <= count($ins_partidaext)) {
						if ($x % 2 == 0) {
							$bor = 'border-right:1px dashed #eee;';
						} else {
							$bor = '';
						}

						if ($x > 1) {
							$bot = 'border-top:1px dashed #eee;';
						} else {
							$bot = '';
						}

						$templateelisa .= '<ul style="padding-left:4px !important; '.$bot.' '.$bor.'  padding:4px;  float:left;list-style: none; width:47%; min-width:30px;vertical-align:top; margin-bottom:0px; padding-left:0px; float:left; margin-top:0px;font-size:6px !important;">';
						$templateelisa .= '<li>PARTIDA DE '.$ins_nomepartida[$x].'</li>';
						$templateelisa .= '<li>FABRICANTE: '.$ins_fabricante[$x].'</li>';
						$templateelisa .= '<li>PARTIDA: '.$ins_partidaext[$x].'</li>';
						$templateelisa .= '<li>FABRICAÇÃO: '.$ins_fabricacao[$x].'</li>';
						$templateelisa .= '<li>VENCIMENTO: '.$ins_vencimento[$x].'</li>';
						$templateelisa .= '</ul>';

						$x++;
					}

					if ($x % 2 != 0) {
						if ($x % 2 == 0) {
							$bor = 'border-right:1px dashed #eee;';
						} else {
							$bor = '';
						}

						if ($x > 1) {
							$bot = 'border-top:1px dashed #eee;';
						} else {
							$bot = '';
						}

						$templateelisa .= '<ul style="padding-left:4px !important; '.$bot.' '.$bor.'  padding:4px;  float:left;list-style: none; width:47%; min-width:30px;vertical-align:top; margin-bottom:0px; padding-left:0px; float:left; margin-top:0px;font-size:6px !important;">';
						$templateelisa .= '<li></li>';

						$templateelisa .= '</ul>';
					}
					$templateelisa .= '</td></tr></table>';
				}

				$templateelisa .= '
								<div class="resdesc">'.preg_replace('/<(\w+) [^>]+>/', '<$1>', $intextopadrao).'</div>
							</fieldset>';
			}
			$templateelisa .= '	</td>';
			//MCC 11/05/2020
			//A pedido do José Branco, foi removido o idtipoteste 3305 da condição abaixo (Deixou de apresentar S/P no gráfico para apresentar por Grupo (ELISA)
			//if ($idtipoteste == 3305 or $idtipoteste == 3512 or $idtipoteste == 4160) {
			if ($idtipoteste == 3512) {
				$urlimg = geragrafelisaSP($arrgraf1);
			} elseif ($idtipoteste == 636 or $idtipoteste == 1455) {
				$urlimg = geragrafelisa4($arrgraf1);
			} elseif (in_array($idtipoteste, $arrayTipoTesteElistaResult)) {
				$urlimg = geragrafelisaRESULT($arrgraf1);
			} elseif ($idtipoteste == 3484) {
				$urlimg = geragrafelisaRESULTSUS($arrgraf1);
			} else {
				$urlimg = geragrafelisa($arrgraf1);
			}
			$urlimg2 = geragrafelisagmt($arrgraf2);
			if (!empty($urlimg) or !empty($urlimg2)) {
				$templateelisa .= '
					<td style="width:36%;  vertical-align:top">';
				if (!empty($urlimg)) {
					$templateelisa .= '
						<fieldset class="fset">
							<legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Gráfico *&nbsp;</font></legend> 
							<div class="resdesc" style="text-align:center;">
								<img src="'.$urlimg.'" style="padding-bottom:5px; height: 120px;"  >
							</div>
						</fieldset>	';
				}
				if ($comparativodelotes == 'Y') {
					$templateelisa .= '
						<br />
						<fieldset class="fset">
							<legend><font class="ftitulo" style="text-transform:uppercase;">&nbsp;Histórico *&nbsp;</font></legend> 
							<div class="resdesc" style="text-align:center;">';
					if (!empty($urlimg2)) {
						$templateelisa .= '
								<img src="'.$urlimg2.'" style="padding-bottom:5px;height: 120px;">';
					}
					$templateelisa .= '
							</div>
						</fieldset>	';
				}
				$templateelisa .= '
						</td>';
			}
			$templateelisa .= '
					</tr>';
		}
	}
	if (empty($_REQUEST['csv'])) {
		echo $templateelisa;
	}
}
//controlar impressão por NF
function controleimpressao($innumerorps, $inoficial)
{



	$vqtd = 0;

	$sqlqtd = "select idcontroleimpressao,via from controleimpressao where numerorps = '".$innumerorps."' and oficial= '".$inoficial."'";
	$resqtd = mysql_query($sqlqtd) or die("A consulta da quantidade de vias falhou (1): ".mysql_error()."<p>SQL: $sqlqtd");
	$vqtd = mysql_num_rows($resqtd);

	if ($vqtd == 0) {

		//inicializa o controle de impressao. como existe CHAVE UNICA composta, somente o erro 1062 sera ignorado
		$sqli = "insert into controleimpressao (idempresa,numerorps,oficial,status,via,criadopor,criadoem) 
			values (
				".$_SESSION["SESSAO"]["IDEMPRESA"]."
				,".$innumerorps."
				,'".$inoficial."','ATIVO',1,'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

		mysql_query($sqli) or die("Error ao inserir na controleimpressao [".mysql_error()."] sql = ".$sqli);

		$idcontroleimpressao = mysql_insert_id();
	} else {

		$row = mysql_fetch_assoc($resqtd);
		$idcontroleimpressao = $row["idcontroleimpressao"];
		$via = $row["via"] + 1;
		$sqlu = "update controleimpressao set via=".$via.",status='ATIVO' where numerorps =".$innumerorps." and oficial= '".$inoficial."'";

		$res = mysql_query($sqlu) or die("Error2 ao alterar via".$sqlu);
	}
}
//controlar impressão por resultado executado pela impressão por NF
function controleimpressaoitem($innumerorps, $inoficial, $inidresultado)
{



	$vqtd = 0;

	$sqlqtd = "select idcontroleimpressao,via from controleimpressao where numerorps = '".$innumerorps."' and oficial= '".$inoficial."'";
	$resqtd = mysql_query($sqlqtd) or die("A consulta da quantidade de vias falhou (2) : ".mysql_error()."<p>SQL: $sqlqtd");
	$vqtd = mysql_num_rows($resqtd);
	$row = mysql_fetch_assoc($resqtd);

	if ($vqtd == 0) {

		echo ("erro ao buscar versão da impressão controleimpressaoitem".$sqlqtd);
		die;
	}

	//insere o resultado para o controle
	$sqlit = "insert into controleimpressaoitem (idempresa,idcontroleimpressao,idresultado,status,via,oficial,criadopor,criadoem) 
	values (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$row['idcontroleimpressao'].",".$inidresultado.",'ATIVO',".$row['via'].",'".$inoficial."','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
	mysql_query($sqlit) or die("Error ao inserir na controleimpressaoitem [".mysql_error()."] sql = ".$sqlit);
}

//controlar impressão executado pela impressão oficial
function controleimpressaooficial($inidregistro, $inexercicio)
{
	$vqtd = 0;

	$sqlqtd = "select idcontroleimpressao,via from controleimpressao where idregistro =".$inidregistro." and exercicio =".$inexercicio." and oficial= 'S'";
	$resqtd = mysql_query($sqlqtd) or die("controleimpressaooficial: A consulta da quantidade de vias oficial falhou : ".mysql_error()."<p>SQL: $sqlqtd");
	$vqtd = mysql_num_rows($resqtd);

	if ($vqtd == 0) {

		//inicializa o controle de impressao. como existe CHAVE UNICA composta, somente o erro 1062 sera ignorado
		$sqli = "insert into controleimpressao (idempresa,idregistro,exercicio,oficial,status,via,criadopor,criadoem)
			values (
				".$_SESSION["SESSAO"]["IDEMPRESA"]."
				,".$inidregistro."
				,".$inexercicio."
				,'S','ATIVO',1,'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

		mysql_query($sqli) or die("controleimpressaooficial: Error ao inserir na controleimpressao [".mysql_error()."] sql = ".$sqli);

		$idcontroleimpressao = mysql_insert_id();
	} else {

		$row = mysql_fetch_assoc($resqtd);
		$idcontroleimpressao = $row["idcontroleimpressao"];
		$via = $row["via"] + 1;
		$sqlu = "update controleimpressao set via=".$via.",status='ATIVO' where idregistro =".$inidregistro." and oficial= 'S'";

		$res = mysql_query($sqlu) or die("controleimpressaooficial: Erro2 ao alterar via".$sqlu);
	}
}
//controlar impressão por resultado executado pela impressão dos oficiais
function controleimpressaoitemoficial($inidregistro, $inidresultado, $inexercicio)
{
	$vqtd = 0;

	$sqlqtd = "select idcontroleimpressao,via from controleimpressao where idregistro =".$inidregistro."  and exercicio =".$inexercicio." and oficial= 'S'";
	$resqtd = mysql_query($sqlqtd) or die("controleimpressaoitemoficial: A consulta da quantidade de vias falhou : ".mysql_error()."<p>SQL: $sqlqtd");
	$vqtd = mysql_num_rows($resqtd);
	$row = mysql_fetch_assoc($resqtd);

	if ($vqtd == 0) {

		echo ("erro ao buscar versão da impressão controleimpressaoitemoficial ");
		die;
	}

	//insere o resultado para o controle
	$sqlit = "insert into controleimpressaoitem (idempresa,idcontroleimpressao,idresultado,status,via,oficial,criadopor,criadoem)
	values (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$row['idcontroleimpressao'].",".$inidresultado.",'ATIVO',".$row['via'].",'S','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
	mysql_query($sqlit) or die("controleimpressaoitemoficial: Error ao inserir na controleimpressaoitem [".mysql_error()."] sql = ".$sqlit);
}
	?>