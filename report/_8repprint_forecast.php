<?
include_once("../inc/php/validaacesso.php");
// CONTROLLERS
require_once(__DIR__ . "/../form/controllers/menurelatorio_controller.php");
require_once(__DIR__ . "/../form/controllers/empresa_controller.php");

baseToGet($_GET["_filtros"]);

if (!empty($_GET["reportexport"])) {
	ob_start(); //não envia nada para o browser antes do termino do processamento
}

$verificarLp = MenuRelatorioController::verificarLpPorIdLpEIdRep(getModsUsr("LPS"), $_GET["_idrep"]);
if (!$verificarLp) {
	die('<div>Você não Possui permissão para acessar esse Relatório</div>');
}

$_idrep = $_GET["_idrep"];

if ($_GET["relatorio"]) {
	$_idrep = $_GET["relatorio"];
}

if (empty($_idrep)) {
	die("Relat&oacute;rio n&atilde;o informado!");
}

if ($_idrep == 21) {
	// d::b()->query("SET sql_mode = 'NO_UNSIGNED_SUBTRACTION';");
	MenuRelatorioController::alterarSQLMode('NO_UNSIGNED_SUBTRACTION');
}
//Recupera a definicao das colunas da view ou table default da pagina
// $arrRep=getConfRelatorio($_idrep);
$arrRep = MenuRelatorioController::buscarConfiguracaoRelatorioPorIdRep($_idrep);
//Facilita a utilização do array
$arrRep = $arrRep[$_idrep];

$_rep = $arrRep["rep"];
$_header = $arrRep["header"];
$_footer = $arrRep["footer"]; // Não usa
$_showfilters = $arrRep["showfilters"]; // Não usa
$_tab = $arrRep["tab"];
$_newgrouppagebreak = $arrRep["newgrouppagebreak"]; // Não usa
$_pbauto = $arrRep["pbauto"];
$_showtotalcounter = $arrRep["showtotalcounter"];
$_compl = $arrRep["compl"];
$_descr = $arrRep["descr"];
$_rodape = $arrRep["rodape"];
$_chavefts = $arrRep["chavefts"]; // Não usa
$_tabfull = $arrRep["tabfull"]; // Não usa
$valorPosFixado = $arrRep['valorposfixado'] ?? '';

$eixoX = "";
$eixoY = [];
$arrayGrafico = array();
$tipoGraphRelatorio = $arrRep["tipograph"];
?>
<html>

<head>
	<? require_once(__DIR__ . "/_8repprint_head.php") ?>

</head>

<body>

	<?
	if (!empty($_GET)) {

		$_sqlwhere = " where ";
		$_and = "";
		$_iclausulas = 0;

		require_once(__DIR__ . "/scripts/_8repprint_montarclausulawhere.php");

		$_sqldata = '';
		require_once(__DIR__ . "/scripts/_8repprint_montaclausuladata.php");

		// Definir Preferencias do usuario
		require_once(__DIR__ . "/scripts/_8repprint_ajustaprefusuario.php");

		//Abre variavel de sessao para que ela possa ser acessada pelo modulo de interceptacao de eventos
		//Isto permitira saber se existe clausula where ou nao
		$_SESSION["SEARCH"]["CLAUSULAS"] = (string)$_iclausulas;

		require_once(__DIR__ . "/scripts/_8repprint_178_montarclausulaidempresa.php");

		if (trim($_compl) != '') {
			$_sqlresultado .= ' ' . trim($_compl);
		}

		// RETRINGIR CONSULTA A UNIDADE MARCADA NA LP-------------------------------------------------------------
		$lps = getModsUsr("LPS");
		// $sqlFlgUnidade="Select flgunidade from "._DBCARBON."._lprep where idrep=".$_idrep." and idlp in(".$lps.") order by flgunidade desc";
		// $rrep = d::b()->query($sqlFlgUnidade) or die("Erro ao verificar unidade no relatorio: ".mysql_error(d::b()));
		$lpRep = MenuRelatorioController::buscarLpRepPorIdRepEIdLps($_idrep, $lps);

		require_once(__DIR__ . "/scripts/_8repprint_restringirconsultaaunidademarcadanalp.php");

		// RETRINGIR CONSULTA A HIERARQUIA ORGANOGRAMA QUANDO MARCADO NA LPREP-------------------------------------------------------------
		require_once(__DIR__ . "/scripts/_8repprint_restringirconsultaaoorganogramapelalp.php");

		//--------------------- Validação para filtro com Plantel - LTM (28-07-2020 - 363014) ----------------
		$arrFiltros = retarraytabdef($_tab);
		require_once(__DIR__ . "/scripts/_8repprint_validafiltroplantel.php");

		$strselectfields = "";
		$strord = "";
		$strvirg = "";

		//Concatenar campos para o select
		require_once(__DIR__ . "/scripts/_8repprint_concatenarcamposvisiveis.php");

		//Concatenar clausulas para Order By
		require_once(__DIR__ . "/scripts/_8repprint_concatenarclausulaorderby.php");
		$strvirg = "";

		//Concatenar clausulas para GROUP BY
		require_once(__DIR__ . "/scripts/_8repprint_ordenarpelocampoordseq.php");

		// RETRINGIR CONSULTA A HIERARQUIA ORGANOGRAMA QUANDO MARCADO NA LPREP-------------------------------------------------------------
		$sqlflgidpessoa = "Select flgidpessoa, flgcontaitem from " . _DBCARBON . "._lprep where idrep=" . $_idrep . " and idlp in(" . $lps . ") and flgcontaitem = 'Y'  order by flgidpessoa desc";

		function gerarCorHex()
		{
			// Gera três valores aleatórios para cada componente de cor (R, G, B)
			$red = rand(0, 255);
			$green = rand(0, 255);
			$blue = rand(0, 255);

			// Converte os valores para hexadecimal e os combina
			$corHex = sprintf("#%02x%02x%02x", $red, $green, $blue);

			return $corHex;
		}

		$sqlsub = "SELECT subcategoria, sum(qtdprevisto), sum(qtdexecutado), sum(vlrprevisto), sum(vlrexecutado)
					FROM forecast
					GROUP BY
						subcategoria;";

		$ressub = mysql_query($sqlsub) or die(mysql_error() . " Erro ao buscar sub sql=" . $sqlsub); ?>

		<div class="normal">
			<div id="accordion">
				<?
				while ($row = mysql_fetch_assoc($ressub)) {?>
				<div class="card table-striped" style="border-collapse: collapse; border-left: 3px solid <?= gerarCorHex() ?>;">
					<div class="card-header" id="headingOne" style="background-color: #bbbbbb;">
						<div class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
							<div style="float:left; margin:0 100px 0 5px; width: 400px;" onclick="insertposition()"><?=$row['subcategoria']?></div>
							<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Qtd Previsto"><?=$row['qtdprevisto']?></div>
							<div style="float:left; margin-left:5px" class="somatorio_valor" title="Qtd Executado"><?=$row['qtdexecutado']?></div>
							<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Vlr Previsto"><?=$row['vlrprevisto']?></div>
							<div style="float:left; margin-left:5px" class="somatorio_valor" title="Vlr Executado"><?=$row['vlrexecutado']?></div>
						</div>
					</div>
					<div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion" style="margin: 0px 10px 0px 0px;">
						<table style="color:#000;">
							<thead>
								<tr>
									<th style="text-align: center; border: 1px solid;" colspan="4">Janeiro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Fevereiro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Março</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Abril</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Maio</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Junho</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Jullho</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Agosto</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Setembro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Outubro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Novembro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Dezembro</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<? for ($i = 1; $i <= 12; $i++) {
										echo '<td>Qtd planejado</td>';
										echo '<td>Qtd Executado</td>';
										echo '<td>Vlr Planejado</td>';
										echo '<td>Vlr Planejado</td>';
									} ?>
								</tr>
								<tr>
									<? for ($i = 1; $i <= 12; $i++) {
										echo '<td>' . rand(3, 10) . '</td>';
										echo '<td>' . rand(3, 10) . '</td>';
										echo '<td> R$' . rand(3, 10) . '</td>';
										echo '<td> R$' . rand(3, 10) . '</td>';
									} ?>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<?}?>

				<div class="card table-striped" style="border-collapse: collapse; border-left: 3px solid <?= gerarCorHex() ?>;">
					<div class="card-header" id="headingOne" style="background-color: #bbbbbb;">
						<div class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
							<div style="float:left; margin:0 100px 0 5px; width: 400px;" onclick="insertposition()">MATERIA-PRIMA</div>
							<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Qtd Previsto">50</div>
							<div style="float:left; margin-left:5px" class="somatorio_valor" title="Qtd Executado">90</div>
							<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Vlr Previsto">R$ 500,00</div>
							<div style="float:left; margin-left:5px" class="somatorio_valor" title="Vlr Executado">R$ 900,00</div>
						</div>
					</div>
					<div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion" style="margin: 0px 10px 0px 0px;">
						<table style="color:#000;">
							<thead>
								<tr>
									<th style="text-align: center; border: 1px solid;" colspan="4">Janeiro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Fevereiro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Março</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Abril</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Maio</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Junho</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Jullho</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Agosto</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Setembro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Outubro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Novembro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Dezembro</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<? for ($i = 1; $i <= 12; $i++) {
										echo '<td>Qtd planejado</td>';
										echo '<td>Qtd Executado</td>';
										echo '<td>Vlr Planejado</td>';
										echo '<td>Vlr Planejado</td>';
									} ?>
								</tr>
								<tr>
									<? for ($i = 1; $i <= 12; $i++) {
										echo '<td>' . rand(3, 10) . '</td>';
										echo '<td>' . rand(3, 10) . '</td>';
										echo '<td> R$' . rand(3, 10) . '</td>';
										echo '<td> R$' . rand(3, 10) . '</td>';
									} ?>
								</tr>
							</tbody>
						</table>
						<div class="card-body" style="margin: 10px 0px 0px 5px; border-collapse: collapse; border-left: 3px solid #999999;">
							<div class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwoOne" aria-expanded="false" aria-controls="collapseTwoOne" style="background-color:#f0f0f0; width: 100%">
								<div style="float:left; margin:0 100px 0 5px; width: 400px;">PH- ADITIVO ÁCIDO PARA REDUÇÃO DO PH (2L)</div>
								<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Qtd Previsto">50</div>
								<div style="float:left; margin-left:5px" class="somatorio_valor" title="Qtd Executado">90</div>
								<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Vlr Previsto">R$ 500,00</div>
								<div style="float:left; margin-left:5px" class="somatorio_valor" title="Vlr Executado">R$ 900,00</div>
							</div>
							<div id="collapseTwoOne" class="collapse tabelainsumo" aria-labelledby="headingTwoOne" data-parent="#accordion" style="margin: 0px 10px 0px 0px;">
								<table style="margin-left: 10px; color:#000;">
									<thead style="text-align: center;">
										<tr>
											<th style="text-align: center; border: 1px solid;" colspan="4">Janeiro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Fevereiro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Março</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Abril</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Maio</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Junho</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Jullho</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Agosto</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Setembro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Outubro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Novembro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Dezembro</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<? for ($i = 1; $i <= 12; $i++) {
												echo '<td>Qtd planejado</td>';
												echo '<td>Qtd Executado</td>';
												echo '<td>Vlr Planejado</td>';
												echo '<td>Vlr Planejado</td>';
											} ?>
										</tr>
										<tr>
											<? for ($i = 1; $i <= 12; $i++) {
												echo '<td>' . rand(3, 10) . '</td>';
												echo '<td>' . rand(3, 10) . '</td>';
												echo '<td> R$' . rand(3, 10) . '</td>';
												echo '<td> R$' . rand(3, 10) . '</td>';
											} ?>
										</tr>
									</tbody>
								</table>
							</div>
						</div>

						<div class="card-body" style="margin: 10px 0px 0px 5px; border-collapse: collapse; border-left: 3px solid #999999;">
							<button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwoTwo" aria-expanded="false" aria-controls="collapseTwoTwo" style="background-color:#f0f0f0; width: 100%">
								<div style="float:left; margin:0 100px 0 5px ;width: 400px;">SOLUÇÃO DE ÁCIDO FOSFÓRICO HB- (5L) - (APRESENTAÇÃO 2022)</div>
								<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Qtd Previsto">50</div>
								<div style="float:left; margin-left:5px" class="somatorio_valor" title="Qtd Executado">90</div>
								<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Vlr Previsto">R$ 500,00</div>
								<div style="float:left; margin-left:5px" class="somatorio_valor" title="Vlr Executado">R$ 900,00</div>
							</button>
							<div id="collapseTwoTwo" class="collapse" aria-labelledby="headingTwoOne" data-parent="#accordion" style="margin: 0px 10px 0px 0px;">
								<table style="margin-left: 10px; color:#000;">
									<thead style="text-align: center;">
										<tr>
											<th style="text-align: center; border: 1px solid;" colspan="4">Janeiro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Fevereiro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Março</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Abril</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Maio</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Junho</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Jullho</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Agosto</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Setembro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Outubro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Novembro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Dezembro</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<? for ($i = 1; $i <= 12; $i++) {
												echo '<td>Qtd planejado</td>';
												echo '<td>Qtd Executado</td>';
												echo '<td>Vlr Planejado</td>';
												echo '<td>Vlr Planejado</td>';
											} ?>
										</tr>
										<tr>
											<? for ($i = 1; $i <= 12; $i++) {
												echo '<td>' . rand(3, 10) . '</td>';
												echo '<td>' . rand(3, 10) . '</td>';
												echo '<td>R$' . rand(3, 10) . '</td>';
												echo '<td>R$' . rand(3, 10) . '</td>';
											} ?>
										</tr>
									</tbody>
								</table>
							</div>
						</div>

						<div class="card-body" style="margin: 10px 0px 0px 5px; border-collapse: collapse; border-left: 3px solid #999999;">
							<button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwothree" aria-expanded="false" aria-controls="collapseTwothree" style="background-color:#f0f0f0; width: 100%">
								<div style="float:left; margin:0 100px 0 5px ;width: 400px;">MONTANIDE ISA 71 VG FILTRADO ESTÉRIL</div>
								<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Qtd Previsto">50</div>
								<div style="float:left; margin-left:5px" class="somatorio_valor" title="Qtd Executado">90</div>
								<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Vlr Previsto">R$ 500,00</div>
								<div style="float:left; margin-left:5px" class="somatorio_valor" title="Vlr Executado">R$ 900,00</div>
							</button>
							<div id="collapseTwothree" class="collapse" aria-labelledby="headingTwoOne" data-parent="#accordion" style="margin: 0px 10px 0px 0px;">
								<table style="margin-left: 10px; color:#000;">
									<thead style="text-align: center;">
										<tr>
											<th style="text-align: center; border: 1px solid;" colspan="4">Janeiro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Fevereiro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Março</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Abril</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Maio</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Junho</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Jullho</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Agosto</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Setembro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Outubro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Novembro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Dezembro</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<? for ($i = 1; $i <= 12; $i++) {
												echo '<td>Qtd planejado</td>';
												echo '<td>Qtd Executado</td>';
												echo '<td>Vlr Planejado</td>';
												echo '<td>Vlr Planejado</td>';
											} ?>
										</tr>
										<tr>
											<? for ($i = 1; $i <= 12; $i++) {
												echo '<td>' . rand(3, 10) . '</td>';
												echo '<td>' . rand(3, 10) . '</td>';
												echo '<td>R$' . rand(3, 10) . '</td>';
												echo '<td>R$' . rand(3, 10) . '</td>';
											} ?>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<div style="padding: 5px; background:#fff;"></div>
				<div class="card table-striped" style="border-collapse: collapse; border-left: 3px solid <?= gerarCorHex() ?>;">
					<div class="card-header" id="heading1" style="background-color: #bbbbbb;">
						<div class="btn btn-link" data-toggle="collapse" data-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
							<div style="float:left; margin:0 100px 0 5px; width: 400px;" onclick="insertposition()">ADJUVANTE</div>
							<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Qtd Previsto">50</div>
							<div style="float:left; margin-left:5px" class="somatorio_valor" title="Qtd Executado">90</div>
							<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Vlr Previsto">R$ 500,00</div>
							<div style="float:left; margin-left:5px" class="somatorio_valor" title="Vlr Executado">R$ 900,00</div>
						</div>
					</div>
					<div id="collapse1" class="collapse" aria-labelledby="heading1" data-parent="#accordion" style="margin: 0px 10px 0px 0px;">
						<table style="color:#000;">
							<thead>
								<tr>
									<th style="text-align: center; border: 1px solid;" colspan="4">Janeiro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Fevereiro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Março</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Abril</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Maio</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Junho</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Jullho</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Agosto</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Setembro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Outubro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Novembro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Dezembro</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<? for ($i = 1; $i <= 12; $i++) {
										echo '<td>Qtd planejado</td>';
										echo '<td>Qtd Executado</td>';
										echo '<td>Vlr Planejado</td>';
										echo '<td>Vlr Planejado</td>';
									} ?>
								</tr>
								<tr>
									<? for ($i = 1; $i <= 12; $i++) {
										echo '<td>' . rand(3, 10) . '</td>';
										echo '<td>' . rand(3, 10) . '</td>';
										echo '<td> R$' . rand(3, 10) . '</td>';
										echo '<td> R$' . rand(3, 10) . '</td>';
									} ?>
								</tr>
							</tbody>
						</table>
						<div class="card-body" style="margin: 10px 0px 0px 5px; border-collapse: collapse; border-left: 3px solid #999999;">
							<div class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse11" aria-expanded="false" aria-controls="collapse11" style="background-color:#f0f0f0; width: 100%">
								<div style="float:left; margin:0 100px 0 5px; width: 400px;">PH- ADITIVO ÁCIDO PARA REDUÇÃO DO PH (2L)</div>
								<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Qtd Previsto">50</div>
								<div style="float:left; margin-left:5px" class="somatorio_valor" title="Qtd Executado">90</div>
								<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Vlr Previsto">R$ 500,00</div>
								<div style="float:left; margin-left:5px" class="somatorio_valor" title="Vlr Executado">R$ 900,00</div>
							</div>
							<div id="collapse11" class="collapse tabelainsumo" aria-labelledby="heading11" data-parent="#accordion" style="margin: 0px 10px 0px 0px;">
								<table style="margin-left: 10px; color:#000;">
									<thead style="text-align: center;">
										<tr>
											<th style="text-align: center; border: 1px solid;" colspan="4">Janeiro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Fevereiro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Março</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Abril</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Maio</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Junho</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Jullho</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Agosto</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Setembro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Outubro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Novembro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Dezembro</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<? for ($i = 1; $i <= 12; $i++) {
												echo '<td>Qtd planejado</td>';
												echo '<td>Qtd Executado</td>';
												echo '<td>Vlr Planejado</td>';
												echo '<td>Vlr Planejado</td>';
											} ?>
										</tr>
										<tr>
											<? for ($i = 1; $i <= 12; $i++) {
												echo '<td>' . rand(3, 10) . '</td>';
												echo '<td>' . rand(3, 10) . '</td>';
												echo '<td> R$' . rand(3, 10) . '</td>';
												echo '<td> R$' . rand(3, 10) . '</td>';
											} ?>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<div style="padding: 5px; background:#fff;"></div>
				<div class="card table-striped" style="border-collapse: collapse; border-left: 3px solid <?= gerarCorHex() ?>;">
					<div class="card-header" id="heading2" style="background-color: #bbbbbb;">
						<div class="btn btn-link" data-toggle="collapse" data-target="#collapse2" aria-expanded="true" aria-controls="collapse2">
							<div style="float:left; margin:0 100px 0 5px; width: 400px;" onclick="insertposition()">ALIMENTAÇÃO</div>
							<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Qtd Previsto">50</div>
							<div style="float:left; margin-left:5px" class="somatorio_valor" title="Qtd Executado">90</div>
							<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Vlr Previsto">R$ 500,00</div>
							<div style="float:left; margin-left:5px" class="somatorio_valor" title="Vlr Executado">R$ 900,00</div>
						</div>
					</div>
					<div id="collapse2" class="collapse" aria-labelledby="heading2" data-parent="#accordion" style="margin: 0px 10px 0px 0px;">
						<table style="color:#000;">
							<thead>
								<tr>
									<th style="text-align: center; border: 1px solid;" colspan="4">Janeiro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Fevereiro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Março</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Abril</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Maio</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Junho</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Jullho</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Agosto</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Setembro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Outubro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Novembro</th>
									<th style="text-align: center; border: 1px solid;" colspan="4">Dezembro</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<? for ($i = 1; $i <= 12; $i++) {
										echo '<td>Qtd planejado</td>';
										echo '<td>Qtd Executado</td>';
										echo '<td>Vlr Planejado</td>';
										echo '<td>Vlr Planejado</td>';
									} ?>
								</tr>
								<tr>
									<? for ($i = 1; $i <= 12; $i++) {
										echo '<td>' . rand(3, 10) . '</td>';
										echo '<td>' . rand(3, 10) . '</td>';
										echo '<td> R$' . rand(3, 10) . '</td>';
										echo '<td> R$' . rand(3, 10) . '</td>';
									} ?>
								</tr>
							</tbody>
						</table>
						<div class="card-body" style="margin: 10px 0px 0px 5px; border-collapse: collapse; border-left: 3px solid #999999;">
							<div class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse21" aria-expanded="false" aria-controls="collapse21" style="background-color:#f0f0f0; width: 100%">
								<div style="float:left; margin:0 100px 0 5px; width: 400px;">PH- ADITIVO ÁCIDO PARA REDUÇÃO DO PH (2L)</div>
								<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Qtd Previsto">50</div>
								<div style="float:left; margin-left:5px" class="somatorio_valor" title="Qtd Executado">90</div>
								<div style="float:left; margin-left:5px" class="somatorio_previsao" title="Vlr Previsto">R$ 500,00</div>
								<div style="float:left; margin-left:5px" class="somatorio_valor" title="Vlr Executado">R$ 900,00</div>
							</div>
							<div id="collapse21" class="collapse tabelainsumo" aria-labelledby="heading21" data-parent="#accordion" style="margin: 0px 10px 0px 0px;">
								<table style="margin-left: 10px; color:#000;">
									<thead style="text-align: center;">
										<tr>
											<th style="text-align: center; border: 1px solid;" colspan="4">Janeiro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Fevereiro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Março</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Abril</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Maio</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Junho</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Jullho</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Agosto</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Setembro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Outubro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Novembro</th>
											<th style="text-align: center; border: 1px solid;" colspan="4">Dezembro</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<? for ($i = 1; $i <= 12; $i++) {
												echo '<td>Qtd planejado</td>';
												echo '<td>Qtd Executado</td>';
												echo '<td>Vlr Planejado</td>';
												echo '<td>Vlr Planejado</td>';
											} ?>
										</tr>
										<tr>
											<? for ($i = 1; $i <= 12; $i++) {
												echo '<td>' . rand(3, 10) . '</td>';
												echo '<td>' . rand(3, 10) . '</td>';
												echo '<td> R$' . rand(3, 10) . '</td>';
												echo '<td> R$' . rand(3, 10) . '</td>';
											} ?>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<? } ?>
	<script class="normal">
		function insertposition() {
			$('#accordion').css({
				'position': 'absolute'
			});
		}
	</script>

</body>

</html>