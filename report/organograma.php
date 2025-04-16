<?
include_once("../inc/php/validaacesso.php");

// CONTROLLERS
require_once(__DIR__ . "/../form/controllers/organograma_controller.php");

$idEmpresa = cb::idempresa();

$empresas  = OrganogramaController::buscarEmpresasAtivas();
$conselhos = OrganogramaController::buscarSgConselhoPorIdEmpresa($idEmpresa);
$areas 	= OrganogramaController::buscarSgAreaPorIdEmpresa($idEmpresa);
$departamentos = OrganogramaController::buscarSgDepartamentoPorIdEmpresa($idEmpresa);
$setores = OrganogramaController::buscarSgSetorPorIdEmpresa($idEmpresa);
$pessoas = OrganogramaController::buscarPessoasAtivasComVinculoEmUmSetor();
$organograma = OrganogramaController::buscarPermissaoVisualizacaoOrganograma($_SESSION["SESSAO"]["IDPESSOA"]);

if ($organograma['visualizarorganograma'] == 'Y') {
	if (!empty($_GET)) {
		$conselho = "";
		$area = "";
		$departamento = "";
		$setor = "";
		$funcionario = "";
		$controlaMostragem = 0;
		$JsonAux = array();
		$semPai = false;

		$filtrado = false;
		/**
		 * TODO: Multi filtros
		 * Restringir -
		 * áreas ao selecionar um conselho,
		 * departamentos ao selecionar area,
		 * setores ao selecionar departamento
		 */
		$empresa = OrganogramaController::buscarEmpresaPorIdEmpresa($idEmpresa);

		// Empresa
		$JsonOrganograma = array([
			"id"	=> 1,
			"nome"	=> strtoupper($empresa['razaosocial']),
			"pai"	=> 0,
			"cor"	=> "orange",
			"responsavel" => " ",
			"tipo" => 'empresa'
		]);

		$nomeCEO = $empresa['ceo'] ? "CEO - " . strtoupper($empresa['ceo']) : 'Sem CEO';

		// Adicionar CEO
		$JsonOrganograma[] = [
			"id"	=> 2,
			"nome"	=> $nomeCEO,
			"pai"	=> 1,
			"cor"	=> "green",
			"responsavel" => " ",
			"tipo" => 'ceo'
		];

		// Filtrar por conselho
		if (!empty($_GET["conselho"]) && !$filtrado) {
			$conselho = "'{$_GET["conselho"]}'";

			$JsonOrganograma = OrganogramaController::buscarConselhosPorIdConselhoERetornarEmUmArray($JsonOrganograma, $conselho, cb::idempresa());
			$JsonOrganograma = OrganogramaController::buscarAreasPorIdConselhoERetornarEmUmArray($JsonOrganograma, $area, cb::idempresa());
			$JsonOrganograma = OrganogramaController::buscarDepartamentosPorIdConselhoERetornarEmUmArray($JsonOrganograma, $departamento, cb::idempresa(), true);
			$JsonOrganograma = OrganogramaController::buscarSetoresPorIdConselhoERetornarEmUmArray($JsonOrganograma, $setor, cb::idempresa());

			$filtrado  = true;
		}

		// Filtrar pela área
		if (!empty($_GET["area"]) && !$filtrado) {
			$area = "'{$_GET["area"]}'";
			$conselho = OrganogramaController::buscaConselhoPorArea($area, $idEmpresa);

			if ($conselho == "_vazio_" || $conselho == "_ghost_") {
				$semPai = true;
			}

			if (!$semPai) {
				$JsonOrganograma = OrganogramaController::buscarConselhosPorIdConselhoERetornarEmUmArray($JsonOrganograma, $conselho, cb::idempresa());
				$JsonOrganograma =  OrganogramaController::buscarAreasPorIdConselhoERetornarEmUmArray($JsonOrganograma, $area, cb::idempresa());
				$JsonOrganograma = OrganogramaController::buscarDepartamentosPorIdConselhoERetornarEmUmArray($JsonOrganograma, $departamento, cb::idempresa(), true);
				$JsonOrganograma = OrganogramaController::buscarSetoresPorIdConselhoERetornarEmUmArray($JsonOrganograma, $setor, cb::idempresa());

				$controlaMostragem = 1;
			}

			$filtrado  = true;
		}

		// Departamento
		if (!empty($_GET["departamento"]) && !$filtrado) {
			$departamento = "'{$_GET["departamento"]}'";
			$area = OrganogramaController::buscarAreaPorDepartamento($departamento, $idEmpresa);
			$conselho = OrganogramaController::buscaConselhoPorArea($area, $idEmpresa);

			if (($area == '_ghost_' || $area == '_vazio_') || (($conselho == '_ghost_' || $conselho == '_vazio_'))) {
				$semPai = true;
			}

			if (!$semPai) {
				$JsonOrganograma = OrganogramaController::buscarConselhosPorIdConselhoERetornarEmUmArray($JsonOrganograma, $conselho, cb::idempresa());
				$JsonOrganograma =  OrganogramaController::buscarAreasPorIdConselhoERetornarEmUmArray($JsonOrganograma, $area, cb::idempresa());
				$JsonOrganograma = OrganogramaController::buscarDepartamentosPorIdConselhoERetornarEmUmArray($JsonOrganograma, $departamento, cb::idempresa());
				$JsonOrganograma = OrganogramaController::buscarSetoresPorIdConselhoERetornarEmUmArray($JsonOrganograma, $setor, cb::idempresa());
				$controlaMostragem = 2;
			}

			$filtrado  = true;
		}

		if (!empty($_GET["setor"]) && !$filtrado) {

			$setor = "'{$_GET["setor"]}'";
			$departamento =  OrganogramaController::buscarDepartamentoPorSetor($setor, $idEmpresa);
			$area = OrganogramaController::buscarAreaPorDepartamento($departamento, $idEmpresa);
			$conselho = OrganogramaController::buscaConselhoPorArea($area, $idEmpresa);

			if ((($conselho == '_ghost_' || $conselho == '_vazio_') || ($area == '_ghost_' || $area == '_vazio_') || ($departamento == '_ghost_' || $departamento == '_vazio_'))) {
				$semPai = true;
			}

			if (!$semPai) {
				$JsonOrganograma = OrganogramaController::buscarConselhosPorIdConselhoERetornarEmUmArray($JsonOrganograma, $conselho, cb::idempresa());
				$JsonOrganograma = OrganogramaController::buscarAreasPorIdConselhoERetornarEmUmArray($JsonOrganograma, $area, cb::idempresa());
				$JsonOrganograma = OrganogramaController::buscarDepartamentosPorIdConselhoERetornarEmUmArray($JsonOrganograma, $departamento, cb::idempresa());
				$JsonOrganograma = OrganogramaController::buscarSetoresPorIdConselhoERetornarEmUmArray($JsonOrganograma, $setor, cb::idempresa());
				$controlaMostragem = 3;
			}

			$filtrado  = true;
		}

		if (!empty($_GET["funcionario"]) && $_GET["funcionario"] != "TODOS" && !$filtrado) {
			$funcionario = $_GET["funcionario"];
			$setor = OrganogramaController::buscarSetorPorFuncionario($funcionario);
			$departamento =  OrganogramaController::buscarDepartamentoPorSetor($setor, $idEmpresa);
			$area = OrganogramaController::buscarAreaPorDepartamento($departamento, $idEmpresa);
			$conselho = OrganogramaController::buscaConselhoPorArea($area, $idEmpresa);

			if ((($conselho == '_ghost_' || $conselho == '_vazio_') || ($area == '_ghost_' || $area == '_vazio_')
				|| ($departamento == '_ghost_' || $departamento == '_vazio_') || ($setor == '_ghost_' || $setor == '_vazio_'))) {
				$semPai = true;
			}

			if (!$semPai) {
				$JsonOrganograma = OrganogramaController::buscarConselhosPorIdConselhoERetornarEmUmArray($JsonOrganograma, $conselho, cb::idempresa());
				$JsonOrganograma = OrganogramaController::buscarAreasPorIdConselhoERetornarEmUmArray($JsonOrganograma, $area, cb::idempresa());
				$JsonOrganograma = OrganogramaController::buscarDepartamentosPorIdConselhoERetornarEmUmArray($JsonOrganograma, $departamento, cb::idempresa());
				$JsonOrganograma = OrganogramaController::buscarSetoresPorIdConselhoERetornarEmUmArray($JsonOrganograma, $setor, cb::idempresa());
				$controlaMostragem = 4;
			}

			$filtrado  = true;
		}

		if (!$filtrado && !$semPai) {
			$JsonOrganograma = OrganogramaController::buscarConselhosPorIdConselhoERetornarEmUmArray($JsonOrganograma, $conselho, cb::idempresa());
			$JsonOrganograma = OrganogramaController::buscarAreasPorIdConselhoERetornarEmUmArray($JsonOrganograma, $area, cb::idempresa());
			$JsonOrganograma = OrganogramaController::buscarDepartamentosPorIdConselhoERetornarEmUmArray($JsonOrganograma, $departamento, cb::idempresa());
			$JsonOrganograma = OrganogramaController::buscarSetoresPorIdConselhoERetornarEmUmArray($JsonOrganograma, $setor, cb::idempresa());
		}

		$objetosSemVinculo = false;

		foreach ($JsonOrganograma as $item) {
			if ($item['pai'] == -1) {
				$objetosSemVinculo = true;
				break;
			}
		}

		if (!$objetosSemVinculo) {
			$chaveDoObjetoSemVinc = key(array_filter($JsonOrganograma, function ($item) {
				return $item['id'] == -1;
			}));
			unset($JsonOrganograma[$chaveDoObjetoSemVinc]);
		}


		if (!($setor == "_vazio_" && $departamento == "_vazio_" && $area == "_vazio_") && (!$semPai)) {
			$JsonAux = OrganogramaController::buscarPessoasPorIdEmpresa($JsonAux, $idEmpresa);
			$JsonPessoaSetor = json_encode($JsonAux, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			$JsonOrg =  json_encode($JsonOrganograma, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		}

		if ($semPai) {
			$organograma = [
				"id" 	  		=> 2,
				"nome"	  		=> "REGISTRO SEM VÍNCULO",
				"idobjeto" 		=> 0,
				"pai"	  		=> 1,
				"cor"	  		=> "purple",
				"tipo"	 		=> "SEMVINCULO",
				"responsavel" 	=> "",
				"nresp"		  	=> 0
			];

			array_push($JsonOrganograma, $organograma);

			$JsonAux = '';
			$JsonPessoaSetor = json_encode($JsonAux, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			$JsonOrg =  json_encode($JsonOrganograma, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		}
	}
?>
	<html lang="pt-br">

	<head>
		<title>Organograma</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="../inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">

		<!-- Latest compiled and minified JavaScript -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
		<style>
			/***************************************************************************
		CSS Reset
	***************************************************************************/

			html,
			body,
			div,
			span,
			applet,
			object,
			iframe,
			h1,
			h2,
			h3,
			h4,
			h5,
			h6,
			p,
			blockquote,
			pre,
			a,
			abbr,
			acronym,
			address,
			big,
			cite,
			code,
			del,
			dfn,
			em,
			img,
			ins,
			kbd,
			q,
			s,
			samp,
			small,
			strike,
			strong,
			sub,
			sup,
			tt,
			var,
			b,
			u,
			i,
			center,
			dl,
			dt,
			dd,
			ol,
			ul,
			li,
			fieldset,
			form,
			label,
			legend,
			table,
			caption,
			tbody,
			tfoot,
			thead,
			tr,
			th,
			td,
			article,
			aside,
			canvas,
			details,
			embed,
			figure,
			figcaption,
			footer,
			header,
			hgroup,
			menu,
			nav,
			output,
			ruby,
			section,
			summary,
			time,
			mark,
			audio,
			video {
				margin: 0;
				padding: 0;
				border: 0;
				font-size: 100%;
				font: inherit;
				vertical-align: baseline;
			}

			#pesquisa {
				display: flex;
				justify-content: center;
				align-items: center;
				/* margin-top: -5px; */
				/* margin-left: 1%; */
				border-bottom: 1px solid black;
				border-right: 1px solid black;
				border-left: 1px solid black;
				background-color: #333;
				border-top-left-radius: 0;
				border-top-right-radius: 0;
				width: 3%;
				height: 30px;
			}

			#legenda {
				margin-left: 10px;
			}

			label {
				color: white;
				margin-bottom: 5px;
				font-size: 12pt;
			}

			article,
			aside,
			details,
			figcaption,
			figure,
			footer,
			header,
			hgroup,
			menu,
			nav,
			section {
				display: block;
			}

			ol,
			ul {
				list-style: none;
			}

			#rodape {
				background-color: #333;
			}

			/***************************************************************************
		Estrutura Básica
	***************************************************************************/

			.row {
				margin: 0;
				border-bottom: 1px solid black;
			}

			.func {
				background-color: #f6c23e;
				color: black;
				margin-top: 10px;
				border-radius: 5px;
				margin-left: 10px;
				margin-right: 10px;
				border: 1px solid black;
			}

			body {
				line-height: 1;
				font: 12px arial, verdana, tahoma, sans-serif;
				color: white;
				cursor: grab;
				background-color: #DCDCDC;
			}

			body:active {
				cursor: grabbing;
			}

			.main {
				margin: 0 auto;
				display: flex;
				padding-top: 100px;
				padding-bottom: 200px;
				width: 100%;
			}

			.organograma {
				margin: 0 auto;
				justify-content: center;
			}

			#target {
				width: 25px;
				height: 25px;
				background-color: mediumvioletred;
				border: 1px solid black;
				border-radius: 50%;
				display: flex;
				align-items: center;
				position: relative;
				bottom: 20;
				left: 120;
			}

			#text {
				width: 100%;
				text-align: center;
			}

			.has-people p {
				margin-top: -20;
			}

			/***************************************************************************
		Ramos
	***************************************************************************/

			.organograma .organograma-exemplo-base ul,
			.organograma .organograma-exemplo-base {
				margin: 0;
				padding-top: 40px;
				position: relative;
			}

			.organograma ul.hide {
				display: none;
			}

			/***************************************************************************
		Estrutura
	***************************************************************************/

			.organograma>.organograma-exemplo-base li {
				z-index: 1;
			}

			.organograma li {
				float: left;
				text-align: center;
				list-style-type: none;
				position: relative;
				padding: 40px 0 0;
			}

			/***************************************************************************
		Estrutura - Conectores
	***************************************************************************/

			.organograma li:before {
				content: '';
				position: absolute;
				top: 0;
				right: 50%;
				border-top: 1px solid black;
				/* Renderizado como zero no IE8 */
				width: 50%;
				/* Causador do bug da barra direita no IE8 */
				height: 40px;
			}

			.organograma li:after {
				content: '';
				position: absolute;
				top: 0;
				right: 50%;
				left: 50%;
				border-left: 1px solid black;
				border-top: 1px solid black;
				width: 50%;
				height: 40px;
			}

			/* Remoção dos conectores da esquerda-direita dos elementos sem nenhum irmão */

			.organograma .filho-unico:after {
				display: none;
			}

			.organograma .filho-unico:before {
				display: none;
			}

			/* Remoção de espaço do topo de filho único */

			.organograma .filho-unico {
				padding: 0;
			}

			/* Remoção do conector esquerdo do primeiro filho e conector direito do último */

			.organograma li:first-child:before {
				border: 0 none;
			}

			.organograma .ultimo-filho:after {
				border: 0 none;
			}

			p {
				cursor: pointer;
			}

			/* Retorno do conector vertical para os últimos nós. */

			.organograma .ultimo-filho:before {
				border-right: 1px solid black;
				border-radius: 0 5px 0 0;
			}

			.organograma li:first-child:after {
				border-radius: 5px 0 0 0;
			}

			/* Retorno do conector dos pais */

			.organograma ul ul:before {
				content: '';
				position: absolute;
				top: 0;
				border-left: 1px solid black;
				height: 40px;
			}

			#ul-0 {
				position: relative;
			}

			/* Demais estilos */

			.organograma .wrap-infos {
				border: 1px solid black;
				padding: 5px;
				display: inline-block;
				border-radius: 5px;
				cursor: pointer;
				margin: 0 10px;
			}

			.organograma #li--1 .AREA #target {
				left: 25px;
				margin-top: 5px;
			}

			.organograma .wrap-infos-padrao {
				min-height: 40px;
				width: 140px;
			}

			.organograma .hightlight {
				border: 1px solid black;
			}

			.organograma .organograma-exemplo-base>li:first-child {
				margin-bottom: 10px;
			}
		</style>
	</head>

	<body>
		<div class="collapse fixed-top" id="rodape">
			<form>
				<div class="row py-3">
					<!-- Empresas -->
					<div class="col-sm-2">
						<div class="form-group">
							<label for="sel1">Empresa:</label>
							<select class="form-control form-control-sm" id="sel1" name="empresa">
								<option></option>
								<? foreach ($empresas as $empresa) { ?>
									<option value="<?= $empresa['idempresa'] ?>"><?= $empresa["nomefantasia"] ?></option>
								<? } ?>
							</select>
						</div>
						<!-- FIM empresas -->
					</div>
					<!-- Conselho -->
					<div class="col-sm-2">
						<div class="form-group">
							<label for="sel1">Conselho:</label>
							<select class="form-control form-control-sm" id="sel1" name="conselho">
								<option></option>
								<? foreach ($conselhos as $conselho) { ?>
									<option><?= $conselho["conselho"] ?></option>
								<? } ?>
							</select>
						</div>
						<!-- FIM Conselho -->
					</div>
					<!-- Área -->
					<div class="col-sm-2">
						<div class="form-group">
							<label for="sel1">Área:</label>
							<select class="form-control form-control-sm" id="sel1" name="area">
								<option></option>
								<? foreach ($areas as $area) { ?>
									<option><?= $area["area"] ?></option>
								<? } ?>
							</select>
						</div>
						<!-- FIM Área -->
					</div>
					<!-- Departamento -->
					<div class="col-sm-2">
						<div class="form-group">
							<label for="sel1">Departamento:</label>
							<select class="form-control form-control-sm" id="sel1" name="departamento">
								<option></option>
								<? foreach ($departamentos as $departamento) { ?>
									<option><?= $departamento["departamento"] ?></option>
								<? } ?>
							</select>
						</div>
						<!-- FIM Departamento -->
					</div>
					<!-- Setor -->
					<div class="col-sm-2">
						<div class="form-group">
							<label for="sel1">Setor:</label>
							<select class="form-control form-control-sm" id="sel1" name="setor">
								<option></option>
								<? foreach ($setores as $setor) { ?>
									<option data-tokens="<?= $setor["setor"] ?>"><?= $setor["setor"] ?></option>
								<? } ?>
							</select>
						</div>
						<!-- FIM Setor -->
					</div>
					<!-- Pessoas -->
					<div class="col-sm-2">
						<div class="form-group">
							<label for="sel1">Pessoa:</label>
							<select class="form-control form-control-sm selectpicker" id="sel1" name="funcionario" data-live-search="true">
								<option>TODOS</option>
								<? foreach ($pessoas as $pessoa) { ?>
									<option><?= $pessoa["nome"] ?></option>
								<? } ?>
							</select>
						</div>
						<!-- FIM Pessoas -->
					</div>
					<div class="col-sm-2">
						<button type="submit" class="btn btn-primary" style="margin-top: 22.5;width: 100%;">Pesquisar</button>
					</div>
				</div>
			</form>
		</div>
		<a data-toggle="collapse" data-target="#rodape" class="btn btn-lg fixed-top aux1" id="pesquisa">
			<span id="icon" class="glyphicon glyphicon-chevron-down "></span>
		</a>
		<div class="main">
			<section id="organograma-exemplo" class="organograma">
				<ul id="ul-0" class="organograma-exemplo-base">
				</ul>
				<!-- Não deixar espaço vazio dentro -->
			</section>
		</div>
		<div class="container1 fixed-bottom">
			<table>
				<tr>
					<td>
						<div class="alert alert-light" data-toggle="collapse" data-target="#tabela" role="alert" id="legenda" style="font-size:13pt;cursor: pointer;">
							<div style="border-bottom: 1px solid gray;">
								<span>Legenda:</span>
								<span class="glyphicon glyphicon-list" style="margin-left:90px;"></span>
							</div>

							<table id="tabela" class="collapse" style="margin-top:20px;">
								<tr>
									<td style="margin:0;padding: 5px;">
										<div style="background-color: orange; width: 20px;height: 20px;"></div>
									</td>
									<td style="contain: content; color:gray;"><span>EMPRESA</span></td>
								</tr>
								<tr>
									<td style="margin:0;padding: 5px;">
										<div style="background-color: #666666; width: 20px;height: 20px;"></div>
									</td>
									<td style="contain: content; color:gray;"><span>CONSELHOS</span></td>
								</tr>
								<tr>
									<td style="margin:0;padding: 5px;">
										<div style="background-color: #d00038; width: 20px;height: 20px;"></div>
									</td>
									<td style="contain: content; color:gray;"><span>ÁREAS</span></td>
								</tr>
								<tr>
									<td style="margin:0;padding: 5px;">
										<div style="background-color: #4e73df; width: 20px;height: 20px;"></div>
									</td>
									<td style="contain: content; color:gray;"><span>DEPARTAMENTOS</span></td>
								</tr>
								<tr>
									<td style="margin:0;padding: 5px;">
										<div style="background-color: #96c965; width: 20px;height: 20px;"></div>
									</td>
									<td style="contain: content; color:gray;"><span>SETORES</span></td>
								</tr>
								<tr>
									<td style="margin:0;padding: 5px;">
										<div style="background-color: #f6c23e; width: 20px;height: 20px;"></div>
									</td>
									<td style="contain: content; color:gray;"><span>PESSOA</span></td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<? require_once(__DIR__ . "/../form/js/organograma_js.php");  ?>
	</body>

	</html>

<? } else {
	echo 'Acesso negado';
} ?>