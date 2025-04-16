<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

$_acao = $_GET['_acao'];

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}
/*
* $pagvaltabela: tablea principal a ser atualizada pelo formulario html
* $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
*                pk: indica parâmetros chave para o select inicial
*                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
*/
$pagvaltabela = "eventotipo";
$pagvalcampos = array(
	"ideventotipo" => "pk"
);

/*
* $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
*/
$pagsql = "SELECT * FROM $pagvaltabela WHERE ideventotipo = '#pkid'";
/*
* controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
*/
include_once("../inc/php/controlevariaveisgetpost.php");

// CONTROLLERS
require_once(__DIR__ . "/controllers/eventotipo_controller.php");

$idPessoa 			= $_SESSION["SESSAO"]["IDPESSOA"];
$ideventotipo       = (empty($_1_u_eventotipo_ideventotipo)) ? 'undefined' : $_1_u_eventotipo_ideventotipo;

$todosTipoPessoa = EventoTipoController::buscarTodosTipoPessoa();
$todosSgDocTipo	= EventoTipoController::buscarTodosSgDocTipo();
$todosTagtipo	= EventoTipoController::buscarTodosTagTipo();

$eventoSla = EventoTipoController::buscarEventoSlaPorIdEventoTipo($_1_u_eventotipo_ideventotipo, 'ATIVO');
$eventoRelacionamento = EventoTipoController::buscarEventoRelacionamentoPorIdEventoTipo($_1_u_eventotipo_ideventotipo);
$eventoTipoAdd = EventoTipoController::buscarEventoTipoAddPorIdEventoTipo($_1_u_eventotipo_ideventotipo);
$unidadeDisponiveisParaVinculo = EventoTipoController::buscarUnidadesDisponiveisParaVinculoPorIdEventoTipo($_1_u_eventotipo_ideventotipo);

$if = 100;

function mountarSelecaoDeCamposHTML($tipoEventoTipo)
{
	global 	$_1_u_eventotipo_ideventotipo, $_1_u_eventotipo_tagtipoobj, $todosTipoPessoa,
		$_1_u_eventotipo_sgdoctipoobj, $todosSgDocTipo, $todosTagtipo, $_1_u_eventotipo_tipopessoaobj,
		$_1_u_eventotipo_prazo, $_1_u_eventotipo_setor_idempresa, $_1_u_eventotipo_departamento_idempresa,
		$_1_u_eventotipo_area_idempresa, $if, $eventoTipoAdd;

	if (!$_1_u_eventotipo_ideventotipo) {
		return false;
	}

	EventoTipoController::inserirNovosCamposPorIdEventoTipo($_1_u_eventotipo_ideventotipo);

	$campos  = EventoTipoController::buscarCamposDisponiveisPorIdEventoTipo($_1_u_eventotipo_ideventotipo);
	$tipoTagAtual 	 = explode(',', $_1_u_eventotipo_tagtipoobj);
	$tipoPessoaAtual = explode(',', $_1_u_eventotipo_tipopessoaobj);
	$tipoSgDocAtual = explode(',', $_1_u_eventotipo_sgdoctipoobj);

	$empresasComAreaDepOuSetor = [];
	$setoresIdEmpresa = explode(',', $_1_u_eventotipo_setor_idempresa);
	$departamentosIdEmpresa = explode(',', $_1_u_eventotipo_departamento_idempresa);
	$areasIdEmpresa = explode(',', $_1_u_eventotipo_area_idempresa);

	$ordem = 1;

	echo '<div class="row d-flex flex-wrap panel-status m-0">';

	foreach ($campos as $campo) {
		$empresasComAreaDepOuSetor = [];
		$checked = "";
		$readonlyEventoAdd = "";
		$var = 'Y';

		echo '<div class="col-sm-3 input-block relative">';

		$if = $if + 1;

		$nameObrigatorio = "name='_{$if}_u_eventotipocampos_obrigatorio'";

		if ($campo['visivel'] == 'Y' && $tipoEventoTipo == 'eventotipocampos') {
			$checked = "checked='checked'";
			$var = 'N';
		} elseif ($campo['eventotipoadd'] == 'Y' && $tipoEventoTipo == 'eventotipocampos') {
			$readonlyEventoAdd = "disabled";
			$nameObrigatorio = '';
		}

		if ($campo['eventotipoadd'] == 'Y' && $tipoEventoTipo == 'eventotipoadd') {
			$checked = "checked='checked'";
			$var = 'N';
		} elseif ($campo['visivel'] == 'Y' && $tipoEventoTipo == 'eventotipoadd') {
			$readonlyEventoAdd = "disabled";
			$nameObrigatorio = '';
		}

		// Tootip para definir largura
		echo "<div class='width-tooltip flex-wrap w-100 absolute hide' data-idinput='larguracoluna-$if' data-ideventotipocampos={$campo['ideventotipocampos']}>
				<div class='w-100 layout-content p-3 bg-gray-10'>
					<div class='d-flex flex-wrap w-100 mb-2 layout-row'>
						<div class='pointer col-xs-2 px-0 layout-item " . ($campo['larguracoluna'] == 2 ? 'active' : '') . "' data-value='2'>
							<div class='w-100 py-2 text-center bg-gray-40 text-gray-80'>
								<span class='bold'>2/12</span>
							</div>
						</div>
						<div class='pointer col-xs-10 pr-0 layout-item " . ($campo['larguracoluna'] == 10 ? 'active' : '') . "' data-value='10'>
							<div class='w-full py-2 text-center bg-gray-40 text-gray-80'>
								<span class='bold'>10/12</span>
							</div>
						</div>
					</div>
					<div class='d-flex flex-wrap w-100 mb-2 layout-row'>
						<div class='pointer col-xs-4 px-0 layout-item " . ($campo['larguracoluna'] == 4 ? 'active' : '') . "' data-value='4'>
							<div class='w-100 py-2 text-center bg-gray-40 text-gray-80'>
								<span class='bold'>4/12</span>
							</div>
						</div>
						<div class='pointer col-xs-8 pr-0 layout-item " . ($campo['larguracoluna'] == 8 ? 'active' : '') . "' data-value='8'>
							<div class='w-100 py-2 text-center bg-gray-40 text-gray-80'>
								<span class='bold'>8/12</span>
							</div>
						</div>
					</div>
					<div class='d-flex flex-wrap w-100 mb-2 layout-row'>
						<div class='pointer col-xs-6 px-0 layout-item " . ($campo['larguracoluna'] == 6 ? 'active' : '') . "' data-value='6'>
							<div class='w-100 py-2 text-center bg-gray-40 text-gray-80'>
								<span class='bold'>6/12</span>
							</div>
						</div>
						<div class='pointer col-xs-6 pr-0 layout-item' data-value='6'>
							<div class='w-100 py-2 text-center bg-gray-40 text-gray-80'>
								<span class='bold'>6/12</span>
							</div>
						</div>
					</div>
					<div class='d-flex flex-wrap w-100 layout-row'>
						<div class='pointer col-xs-12 p-0 layout-item " . (($campo['larguracoluna'] == 12 || !$campo['larguracoluna']) ? 'active' : '') . "' data-value='12'>
							<div class='w-100 py-2 text-center bg-gray-40 text-gray-80'>
								<span class='bold'>12/12</span>
							</div>
						</div>
					</div>
				</div>
				<div class='absolute tooltip-arrow'></div>
			</div>";

		echo '<div class="input-check col-sm-2">';

		if ($campo['col'] == 'inicio' || $campo['col'] == 'prazo') {
			$checked = "";

			if ($campo['col'] == 'prazo' && $_1_u_eventotipo_prazo == "Y") {
				$checked = "checked='checked'";
			}

			if ($campo['col'] != 'prazo' && $_1_u_eventotipo_prazo == "N") {
				$checked = "checked='checked'";
			}


			echo "<input type='radio' name='dataprazo' $checked $readonlyEventoAdd onclick='toggledataprazo(`{$campo['col']}`, this)' />";
		} else {
			echo "<input title='Visível' id='arquivo' $readonlyEventoAdd type='checkbox' aria-label='...' onchange='eventotipocampos(this, {$campo['ideventotipocampos']}, `$var`, $ordem, `$tipoEventoTipo`, `{$eventoTipoAdd[0]['tipocamposobj']}`, `{$eventoTipoAdd[0]['ideventotipoadd']}`)' $checked /> ";
		}

		echo "</div>";

		$colRotulo = 'col-sm-4';
		$colOrdem = 'col-sm-2';
		$colCampoObrigatorio = 'col-sm-1';

		if (
			(
				$campo['col'] == 'idsgsetor' || $campo['col'] == 'idsgdoc'
				|| $campo['col'] == 'idsgdepartamento' || $campo['col'] == 'idpessoaev'
				|| $campo['col'] == 'idequipamento'
			) && ($campo['visivel'] == 'Y' || $campo['eventotipoadd'] == 'Y')
		) {
			$colRotulo = 'col-sm-3';
		}


		if ($campo['col'] != 'prazo' || $campo['col'] != 'inicio') {
			echo "	<div class='$colRotulo'>
						<input title='{$campo['col']}' style='background: #eee;' $readonlyEventoAdd name='_{$if}_u_eventotipocampos_rotulo' type='text' class='form-control' aria-label='...' value='" . (trim($campo['rotulo']) == '' ? $campo['col'] : $campo['rotulo']) . "' />
					</div>
					<div class='$colOrdem'>
						<input title='Ordem' name='_{$if}_u_eventotipocampos_ord' $readonlyEventoAdd class='rounded-0' type='text' value='{$campo['ord']}' placeholder='ord' />
					</div>
					<input id='campo-{$campo['ideventotipocampos']}' $readonlyEventoAdd name='_{$if}_u_eventotipocampos_ideventotipocampos' type='hidden' value='{$campo['ideventotipocampos']}' />
					<input id='campo-code-{$campo['ideventotipocampos']}' $readonlyEventoAdd name='_{$if}_u_eventotipocampos_code' type='hidden' value='" . htmlspecialchars($campo['code'], ENT_QUOTES) . "' />
					<input id='campo-codevinculo-{$campo['ideventotipocampos']}' $readonlyEventoAdd name='_{$if}_u_eventotipocampos_codevinculo' type='hidden' value='" . htmlspecialchars($campo['codevinculo'], ENT_QUOTES) . "' />
					<input id='campo-codedeletado-{$campo['ideventotipocampos']}' $readonlyEventoAdd name='_{$if}_u_eventotipocampos_codedeletado' type='hidden' value='" . htmlspecialchars($campo['codedeletado'], ENT_QUOTES) . "' />";

		}

		if (($campo['col'] == 'idsgsetor' && ($campo['visivel'] == 'Y' || $campo['eventotipoadd'] == 'Y')) ||
			($campo['col'] == 'idsgdepartamento' && ($campo['visivel'] == 'Y' || $campo['eventotipoadd'] == 'Y')) ||
			($campo['col'] == 'idsgarea' && ($campo['visivel'] == 'Y' || $campo['eventotipoadd'] == 'Y'))
		) {
			$empresasComAreaDepOuSetor = EventoTipoController::buscarEmpresasVinculadasAUmaAreaDepartamentoOuSetor(substr($campo['col'], 2));
		}

		if ($campo['col'] == 'idsgsetor' and ($campo['visivel'] == 'Y' || $campo['eventotipoadd'] == 'Y')) {
			echo "<select class='selectpicker'  multiple='multiple' data-live-search='true' $readonlyEventoAdd title='Empresa' onchange='atualizaValorSetor(this, $_1_u_eventotipo_ideventotipo);'>";

			foreach ($empresasComAreaDepOuSetor as $empresa) {
				$selected = '';

				if (in_array($empresa['idempresa'], $setoresIdEmpresa)) {
					$selected = 'selected';
				}

				echo "<option data-tokens='" . retira_acentos($empresa['empresa']) . "' value='{$empresa['idempresa']}' $selected>{$empresa['empresa']}</option>";
			}

			echo "</select>";
		}

		if ($campo['col'] == 'idsgdepartamento' and ($campo['visivel'] == 'Y' || $campo['eventotipoadd'] == 'Y')) {
			echo "<select class='selectpicker' multiple='multiple' data-live-search='true' $readonlyEventoAdd title='Empresas' onchange='atualizaValorDepartamento(this, $_1_u_eventotipo_ideventotipo);'>";

			foreach ($empresasComAreaDepOuSetor as $empresa) {
				$selected = '';

				if (in_array($empresa['idempresa'], $departamentosIdEmpresa)) {
					$selected = 'selected';
				}

				echo "<option data-tokens='" . retira_acentos($empresa['empresa']) . "' value='{$empresa['idempresa']}' $selected>{$empresa['empresa']}</option>";
			}

			echo "</select>";
		}

		if ($campo['col'] == 'idpessoaev' && ($campo['visivel'] == 'Y' || $campo['eventotipoadd'] == 'Y')) {
			echo "<select class='selectpicker' multiple='multiple' data-live-search='true' $readonlyEventoAdd title='Tipo Pessoa' onchange='atualizavalorpessoa(this, $_1_u_eventotipo_ideventotipo);'>";

			foreach ($todosTipoPessoa as $tipo) {
				$selected = '';

				if (in_array($tipo['idtipopessoa'], $tipoPessoaAtual)) {
					$selected = 'selected';
				}

				echo "<option data-tokens='" . retira_acentos($tipo['tipopessoa']) . "' value='{$tipo['idtipopessoa']}' $selected>{$tipo['tipopessoa']}</option>";
			}

			echo '</select>';
		}

		if ($campo['col'] == 'idequipamento' && ($campo['visivel'] == 'Y' || $campo['eventotipoadd'] == 'Y')) {
			echo "<select class='selectpicker' multiple='multiple' data-live-search='true' $readonlyEventoAdd title='Tipo Equipamento' onchange='atualizavalor(this, $_1_u_eventotipo_ideventotipo);'>";

			foreach ($todosTagtipo as $tipo) {
				$selected = '';

				if (in_array($tipo['idtagtipo'], $tipoTagAtual)) {
					$selected = 'selected';
				}

				echo "<option data-tokens='" . retira_acentos($tipo['tagtipo']) . "' value='{$tipo['idtagtipo']}' $selected>{$tipo['tagtipo']}</option>";
			}

			echo "</select>";
		}

		if ($campo['col'] == 'idsgdoc' and ($campo['visivel'] == 'Y' || $campo['eventotipoadd'] == 'Y')) {
			echo "<select class='selectpicker'  multiple='multiple' data-live-search='true' $readonlyEventoAdd title='Tipo Documento' onchange='atualizavalordoc(this, $_1_u_eventotipo_ideventotipo);'>";

			foreach ($todosSgDocTipo as $tipo) {
				$selected = '';

				if (in_array($tipo['idsgdoctipo'], $tipoSgDocAtual)) {
					$selected = 'selected';
				}

				echo "<option data-tokens='" . retira_acentos($tipo['rotulo']) . "' value='" . $tipo['idsgdoctipo'] . "' $selected>{$tipo['rotulo']}</option>";
			}

			echo "</select> ";
		}

		if ($campo['col'] == 'idsgarea' && ($campo['visivel'] == 'Y' || $campo['eventotipoadd'] == 'Y')) {
			echo "<select class='selectpicker' multiple='multiple' data-live-search='true' $readonlyEventoAdd title='Empresas' onchange='atualizaValorArea(this, $_1_u_eventotipo_ideventotipo);'>";

			foreach ($empresasComAreaDepOuSetor as $empresa) {
				$selected = '';

				if (in_array($empresa['idempresa'], $areasIdEmpresa)) {
					$selected = 'selected';
				}

				echo "<option data-tokens='" . retira_acentos($empresa['empresa']) . "' value='{$empresa['idempresa']}' $selected>{$empresa['empresa']}</option>";
			}

			echo "</select>";
		}

		// Alterar layout
		echo "	<div class='required-input $colCampoObrigatorio hoverazul'>
					<label class='transition px-3 text-xs d-flex align-items-center justify-content-center pointer h-100 w-100 larguracoluna-item' for='larguracoluna-{$if}' title='Layout de campos'>
						<i class='fa fa-th-large'></i>
					</label>
				</div>";

		// Tornar obrigatorio
		echo "	<div class='required-input $colCampoObrigatorio hoverazul'>
					<input id='obrigatorio-{$if}' $readonlyEventoAdd $nameObrigatorio class='hidden' type='checkbox' value='1' " . ($campo['obrigatorio'] ? 'checked' : '') . " />
					<label class='transition px-3 text-xs d-flex align-items-center justify-content-center pointer h-100 w-100' for='obrigatorio-{$if}' title='" . ($campo['obrigatorio'] ? 'Tornar opcional.' : 'Tornar obrigatório.') . "'>
						<i class='fa fa-asterisk'></i>
					</label>
				</div>";

		if (($campo['col'] != 'idsgsetor' && $campo['col'] != 'idsgdoc' && $campo['col'] != 'idsgdepartamento' && $campo['col'] != 'idpessoaev'
			&& $campo['col'] != 'idequipamento' && $campo['col'] != 'idsgdoc' && $campo['col'] != 'url') && !$campo['colunacode']) {

			$camposOption = EventoTipoController::buscarEventoTipoCamposPorIdEventoTipoOpcao($_1_u_eventotipo_ideventotipo);
			$camposOptionSelect = "<select class='col-xs-8' name='campooption' $readonlyEventoAdd ideventotipocampos='{$campo['ideventotipocampos']}' title='Campos' onchange='atualizaOptionVinculo(this);'><option></option>";

			foreach ($camposOption as $option) {
				$selectedOption = $campo['ideventotipocamposvinculo'] == $option['ideventotipocampos'] ? 'selected' : '';

				if ($option['ideventotipocampos'] != $campo['ideventotipocampos']) {
					$camposOptionSelect .= "<option value='{$option['ideventotipocampos']}' $selectedOption>{$option['rotulo']}</option>";
				}
			}

			$camposOptionSelect .= "</select>";

			// Escolher Tipo do campo
			echo "	<div class='choose-input-type-btn pointer col-sm-1' title='Adicionar opções para {$campo['col']}' data-title='{$campo['col']}' data-ideventocampo='{$campo['ideventotipocampos']}'>
					<span>
						<i class='fa fa-navicon'></i>
					</span>
					</div>";

			echo  "	<div id='choose-input-type' class='hidden'>
						<i id='close-options' class='fa fa-times-circle pointer text-danger' title='Salvar e fechar modal de opções'></i>
						<div class='d-flex flex-between align-items-center w-100 flex-wrap'>		
							<div class='w-100 d-flex align-items-center flex-between'>
								<h4 class='font-default'>
									{$campo['col']}
								</h4>				
								<i class='fa fa-plus-circle text-success pointer mr-2 add-option' title='Adicionar opção'></i>
							</div>
							<div class='w-100 d-flex align-items-center flex-between mb-3'>
								Vincular Campo: {$camposOptionSelect}
							</div>
						</div>
					<div class='d-flex flex-wrap w-100 options'>";

			$opcoesHTML = null;

			if ($campo['code']) {
				$opcoes = EventoTipoController::buscarCodigoPorConsulta($campo['code']);

				if ($campo['ideventotipocamposvinculo']) {
					$codeVinculo = EventoTipoController::buscarCodeIdEventoTipoCampos($campo['ideventotipocamposvinculo']);
					$opcoesVinculo = EventoTipoController::buscarCodigoPorConsulta($codeVinculo['code']);
					if ($campo['codevinculo']) {
						$opcoesVinculados = EventoTipoController::buscarCodigoPorConsulta($campo['codevinculo']);
						$arrayVinculado = array_column($opcoesVinculados, 'id');
					}
				}

				foreach ($opcoes as $opcao) {
					$divVinculo = "";
					if (empty($campo['ideventotipocamposvinculo'])) {
						$classe = 'col-sm-10';
					} else {
						$classe = 'col-sm-5';
						foreach ($opcoesVinculo as $opcaoVinculo) {
							$selectedOptionVinculo = in_array($opcao['id'] . "-" . $opcaoVinculo['id'], $arrayVinculado) ? 'selected' : '';
							$divVinculo .= "<option value='{$opcaoVinculo['id']}' {$selectedOptionVinculo}>{$opcaoVinculo['value']}</option>";
						}

						$divVinculo = "<div class='col-sm-5 px-0'>
											<select class='w-100 campovinculo' $readonlyEventoAdd data-live-search='true' campo='{$opcao['id']}'  onchange='atualizarValoresDasOpcoesDoCampoAtivoVinculo();'>
												<option></option>
												{$divVinculo}
											</select>
										</div>";
					}

					$opcoesHTML .= "<div class='w-100 d-flex flex-wrap'>
										<div class='{$classe} pl-0'>
											<input id='{$opcao['id']}' type='text' class='form-control mb-2 option mb-2' value='{$opcao['value']}' placeholder='{$opcao['value']}' />
										</div>
										{$divVinculo}
										<div class='col-sm-2 d-flex align-items-center'>
											<i class='fa fa-trash cinza pointer remove-option' title='Remover opção'></i>
										</div>
									</div>";
				}
			}

			echo 	$opcoesHTML;

			echo 	"</div>
			</div>";
		}

		if ($campo['col'] == 'url') {
			echo "<div class='remove-url hoverazul'>
						<label class='transition px-3 text-xs d-flex align-items-center justify-content-center pointer h-100 w-100' title='Remover url'>
							<i class='fa fa-trash'></i>
						</label>
					</div>";
		}

		echo "</div>";
	} //foreach($roec=mysqli_fetch_assoc($rec)){

	$camposPrazoEData = EventoTipoController::buscarCamposPrazoEDataPorIdEventoTipo($_1_u_eventotipo_ideventotipo);

	foreach ($camposPrazoEData as $campoPrazoData) {
		$if = $if + 1;
		$ifprazo = $if;
		$i = 0;
		$readonlyEventoAdd = "";
		$checked = "";

		$nameObrigatorio = "name='_{$if}_u_eventotipocampos_obrigatorio'";

		if ($campoPrazoData['col'] == 'prazo' && $_1_u_eventotipo_prazo == "Y" && $tipoEventoTipo == 'eventotipocampos') {
			$checked = "checked='checked'";
		} elseif ($campo['eventotipoadd'] == 'Y' && $tipoEventoTipo == 'eventotipocampos') {
			$readonlyEventoAdd = "disabled";
			$nameObrigatorio = '';
		}

		if ($campo['eventotipoadd'] == 'Y' && $tipoEventoTipo == 'eventotipoadd') {
			$checked = "checked='checked'";
		} elseif ($campoPrazoData['col'] == 'prazo' && $_1_u_eventotipo_prazo == "Y" && $tipoEventoTipo == 'eventotipoadd') {
			$readonlyEventoAdd = "disabled";
			$nameObrigatorio = '';
		}

		echo "<div class='input-group nowrap px-3 relative' style='margin-top: 5px;'>
				<span class='input-group-addon'>";

		echo "	<input type='radio' class='size1' name='dataprazo' $checked  $readonlyEventoAdd onclick='toggledataprazo(`{$campoPrazoData["col"]}`,this)' />
				<input title='{$campoPrazoData['col']}' style='width:50px !important;background: #eee;' $readonlyEventoAdd name='_{$if}_u_eventotipocampos_rotulo' type='text' class='form-control size8' aria-label='...' value=" . (trim($campoPrazoData['rotulo']) == '' ? $campoPrazoData['col'] : $campoPrazoData['rotulo']) . ">
				<input name='_{$ifprazo}_u_eventotipocampos_ideventotipocampos' $readonlyEventoAdd class='size2' type='hidden' value='{$campoPrazoData['ideventotipocampos']}'>
			</span>
			<span class='input-group-addon'>";

		$if = $if + 1;
		$ifinicio = $if;

		$checked = "";

		if ($campoPrazoData['col2'] != 'prazo' && $_1_u_eventotipo_prazo == "N") {
			$checked = "checked='checked'";
		}

		echo "		<input type='radio' name='dataprazo' $checked $readonlyEventoAdd onclick='toggledataprazo(`{$campoPrazoData["col2"]}`, this)'>
					<input title='{$campoPrazoData['col2']}' style='background: #eee;width:50px !important;' $readonlyEventoAdd  name='_{$if}_u_eventotipocampos_rotulo' type='text' class='form-control size8' aria-label='...' value=" . (trim($campoPrazoData['rotulo2']) == '' ? $campoPrazoData['col2'] : $campoPrazoData['rotulo2']) . ">
					<input name='_{$ifinicio}_u_eventotipocampos_ideventotipocampos' $readonlyEventoAdd class='size2' type='hidden' value='{$campoPrazoData['ideventotipocampos2']}' />
				</span>
				<div class='d-flex flex-column col-sm-2 px-0'>";

		if ($_1_u_eventotipo_prazo == "Y") {
			echo "	<input title='Ordem Prazo' $readonlyEventoAdd name='_{$ifprazo}_u_eventotipocampos_ord' type='text' value='{$campoPrazoData['ord']}' placeholder='ord'>
					<input title='Ordem Inicio' $readonlyEventoAdd class='hidden' name='_{$ifinicio}_u_eventotipocampos_ord' value='' placeholder='ord' />";
		} else {
			echo "	<input title='Ordem Inicio' $readonlyEventoAdd name='_{$ifinicio}_u_eventotipocampos_ord' type='text' value='{$campoPrazoData['ord2']}' placeholder='ord' />
					<input title='Ordem Prazo' $readonlyEventoAdd class='hidden' name='_{$ifprazo}_u_eventotipocampos_ord' value='' placeholder='ord' />";
		}

		// Layout
		echo "	<div class='required-input $colCampoObrigatorio hoverazul  w-100'>
					<label class='transition text-xs d-flex align-items-center justify-content-center pointer h-100 w-100 larguracoluna-item' for='larguracoluna-{$if}' title='Layout de campos'>
						<i class='fa fa-th-large'></i>
					</label>
				</div>";

		// Obrigatorio
		echo "	<input id='obrigatorio-{$if}' $readonlyEventoAdd $nameObrigatorio class='hidden' type='checkbox' value='1' " . ($campoPrazoData['obrigatorio'] ? 'checked' : '') . " />
				<label class='input-group-addon pointer hoverazul transition px-0 size3 text-base px-1' for='obrigatorio-{$if}' title='" . ($campoPrazoData['obrigatorio'] ? 'Tornar opcional.' : 'Tornar obrigatório.') . "'>
					<i class='fa fa-asterisk'></i>
				</label>
			</div>
			<div class='width-tooltip flex-wrap w-100 absolute hide' data-idinput='larguracoluna-$if' data-ideventotipocampos={$campoPrazoData['ideventotipocampos']}>
				<div class='w-100 layout-content p-3 bg-gray-10'>
					<div class='d-flex flex-wrap w-100 mb-2 layout-row'>
						<div class='pointer col-xs-2 px-0 layout-item " . ($campoPrazoData['larguracoluna'] == 2 ? 'active' : '') . "' data-value='2'>
							<div class='w-100 py-2 text-center bg-gray-40 text-gray-80'>
								<span class='bold'>2/12</span>
							</div>
						</div>
						<div class='pointer col-xs-10 pr-0 layout-item " . ($campoPrazoData['larguracoluna'] == 10 ? 'active' : '') . "' data-value='10'>
							<div class='w-full py-2 text-center bg-gray-40 text-gray-80'>
								<span class='bold'>10/12</span>
							</div>
						</div>
					</div>
					<div class='d-flex flex-wrap w-100 mb-2 layout-row'>
						<div class='pointer col-xs-4 px-0 layout-item " . ($campoPrazoData['larguracoluna'] == 4 ? 'active' : '') . "' data-value='4'>
							<div class='w-100 py-2 text-center bg-gray-40 text-gray-80'>
								<span class='bold'>4/12</span>
							</div>
						</div>
						<div class='pointer col-xs-8 pr-0 layout-item " . ($campoPrazoData['larguracoluna'] == 8 ? 'active' : '') . "' data-value='8'>
							<div class='w-100 py-2 text-center bg-gray-40 text-gray-80'>
								<span class='bold'>8/12</span>
							</div>
						</div>
					</div>
					<div class='d-flex flex-wrap w-100 mb-2 layout-row'>
						<div class='pointer col-xs-6 px-0 layout-item " . ($campoPrazoData['larguracoluna'] == 6 ? 'active' : '') . "' data-value='6'>
							<div class='w-100 py-2 text-center bg-gray-40 text-gray-80'>
								<span class='bold'>6/12</span>
							</div>
						</div>
						<div class='pointer col-xs-6 pr-0 layout-item' data-value='6'>
							<div class='w-100 py-2 text-center bg-gray-40 text-gray-80'>
								<span class='bold'>6/12</span>
							</div>
						</div>
					</div>
					<div class='d-flex flex-wrap w-100 layout-row'>
						<div class='pointer col-xs-12 p-0 layout-item " . (($campoPrazoData['larguracoluna'] == 12 || !$campoPrazoData['larguracoluna']) ? 'active' : '') . "' data-value='12'>
							<div class='w-100 py-2 text-center bg-gray-40 text-gray-80'>
								<span class='bold'>12/12</span>
							</div>
						</div>
					</div>
				</div>
				<div class='absolute tooltip-arrow'></div>
			</div>
		</div> ";

		$i++;
	}

	echo '</div>';
}

function montarUnidadeHTML()
{
	global $_1_u_eventotipo_ideventotipo;

	$unidades = EventoTipoController::buscarUnidadePorIdEventoTipo($_1_u_eventotipo_ideventotipo);

	$title = 'Editar Unidade';
	$trStart = '<tr>';
	$trEnd   = '</tr>';

	foreach ($unidades as $unidade) {
		if (isset($unidade['error'])) {
			echo "  <tr>
						<td class='py-3'>{$unidade['error']}</td>
					</tr>";

			break;
		}

		$trContent = "  <td class='py-3' colspan='2'><a title='" . $title . "' target='_blank' href='?_modulo=unidade&_acao=u&idunidade=" . $unidade["idunidade"] . "'>" . $unidade["unidade"] . "</a></td>
						<td align='center'>
							<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularUnidade(" . $unidade['idunidadeobjeto'] . ")' title='Excluir!'></i>
						</td>";

		$tr = " $trStart
					$trContent
				$trEnd";

		echo $tr;
	}
}

?>
<link rel="stylesheet" href="/form/css/eventotipo_css.css?version=1.3" />
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="row">
					<div class="col-md-10">Tipo Evento</div>
				</div>
			</div>
			<div class="panel-body">
				<div class="d-flex flex-wrap px-0 w-100">
					<!-- Id -->
					<input name="_1_<?= $_acao ?>_<?= $pagvaltabela ?>_ideventotipo" id="ideventotipo" type="hidden" value="<?= $_1_u_eventotipo_ideventotipo ?>" readonly='readonly'>
					<!-- Jconf -->
					<textarea name="_1_<?= $_acao ?>_<?= $pagvaltabela ?>_jconfig" class="hidden"><?= $_1_u_eventotipo_jconfig ?></textarea>
					<!-- JsonConfig -->
					<textarea name="_1_<?= $_acao ?>_<?= $pagvaltabela ?>_jsonconfig" class="hidden"><?= $_1_u_eventotipo_jsonconfig ?></textarea>
					<div class="col-xs-12 col-md-6 d-flex flex-wrap">
						<!-- Tipo evento -->
						<div class="col-xs-12 form-group">
							<label class="head mb-3">Tipo Evento:</label>
							<input class="form-control" placeholder="Nome do Tipo do Evento" name="_1_<?= $_acao ?>_<?= $pagvaltabela ?>_eventotipo" id="eventotipo" type="text" value="<?= $_1_u_eventotipo_eventotipo ?>" vnulo>
						</div>
						<!-- Explicacao -->
						<div class="col-xs-12 form-group">
							<label class="head mb-3">Explicação (title):</label>
							<input class="form-control" placeholder="Título do Botão" name="_1_<?= $_acao ?>_<?= $pagvaltabela ?>_eventotitle" id="eventotitle" type="text" value="<?= $_1_u_eventotipo_eventotitle ?>" vnulo>
						</div>
					</div>
					<div class="col-xs-12 col-md-6 d-flex flex-wrap">
						<!-- Cor -->
						<div class="col-xs-12 col-md-4 form-group">
							<label class="head mb-3">Cor:</label>
							<input class="form-control" name="_1_<?= $_acao ?>_<?= $pagvaltabela ?>_cor" onchange="atualizaCor()" id="color" type="color" aria-label="..." value="<?= ($_acao == 'u') ? $_1_u_eventotipo_cor : '#A9A9A9' ?>">
						</div>

						<!-- Fluxo Único -->
						<div class="col-xs-12 col-md-2 form-group">
							<label class="head mb-3">Fluxo Único:</label> <br />
							<? $fluxounico = ($_1_u_eventotipo_fluxounico == 'Y') ? 'N' : 'Y'; ?>
							<? $checkedFluxoUnico = ($_1_u_eventotipo_fluxounico == 'Y') ? 'checked' : ''; ?>
							<input title="Fluxo Único" id="fluxounico" type="checkbox" aria-label="..." <?=$checkedFluxoUnico?> onchange="atcampo(<?=$_1_u_eventotipo_ideventotipo?>, 'fluxounico', '<?=$fluxounico?>')">
						</div>

						<!-- Status -->
						<div class="col-xs-12 col-md-4 form-group">
							<label class="head mb-3">Status:</label>
							<select name="_1_<?= $_acao ?>_<?= $pagvaltabela ?>_status" class="form-control">
								<? fillselect("SELECT 'ATIVO','Ativo' union select 'INATIVO','Inativo'", $_1_u_eventotipo_status); ?>
							</select>
						</div>
						<!-- Html livre -->
						<div class="col-xs-12 form-group">
							<label class="head mb-3">Html livre:</label>
							<textarea name="_1_<?= $_acao ?>_<?= $pagvaltabela ?>_html" rows="4" class="form-control"><?= $_1_u_eventotipo_html ?></textarea>
						</div>
					</div>
				</div>
				<hr>
				<div class="row"></div>
				<hr>
				<?
				if (!empty($_1_u_eventotipo_ideventotipo)) {
				?>
					<? //Evento Tipo Campos 
					?>
					<?= mountarSelecaoDeCamposHTML('eventotipocampos') ?>
					<div class="row">
						<div class="col-lg-12">
							<? //Evento Campos 
							?>
							<div class=" panel-status">
								<table>
									<tr>
										<!-- Upload -->
										<td>
											<? if ($_1_u_eventotipo_upload == 'Y') {
												$checkedupload = "checked='checked'";
											} else {
												$checkedupload = '';
											} ?>
											<div class="input-group " style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="arquivo" type="checkbox" aria-label="..." onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'upload')" <?= $checkedupload ?> data-idcampoobrigatorio="obrigatorio-upload">
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Upload de Arquivos" readonly="">
												<!-- Tornar obrigatorio -->
												<input id="obrigatorio-upload" class="hidden" type="checkbox" value="1" onclick="setObrigatorio(this, 'upload')" />
												<label class="input-group-addon pointer hoverazul transition px-3 text-xs" for="obrigatorio-upload" title="">
													<i class="fa fa-asterisk"></i>
												</label>
											</div>
										</td>
										<!-- Vincular documentos -->
										<td>
											<? if ($_1_u_eventotipo_vinculadoc == 'Y') {
												$checkedvinculadoc = "checked='checked'";
											} else {
												$checkedvinculadoc = '';
											} ?>
											<div class="input-group " style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="arquivo" type="checkbox" aria-label="..." onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'vinculadoc')" <?= $checkedvinculadoc ?> data-idcampoobrigatorio="obrigatorio-vinculadoc">
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Vincular documentos" readonly="">
												<!-- Tornar obrigatorio -->
												<!-- <input id="obrigatorio-vinculadoc" class="hidden" type="checkbox" value="1" onclick="setObrigatorio(this, 'vinculadoc')" />
												<label class="input-group-addon pointer hoverazul transition px-3 text-xs" for="obrigatorio-vinculadoc" title="">
													<i class="fa fa-asterisk"></i>
												</label> -->
											</div>
										</td>
										<td>
											<? if ($_1_u_eventotipo_calendario == 'Y') {
												$checkedcalendario = "checked='checked'";
											} else {
												$checkedcalendario = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="calendario" type="checkbox" aria-label="..." onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'calendario')" <?= $checkedcalendario ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Exibir no calendario" readonly="">
											</div>
										</td>
										<td>
											<? if ($_1_u_eventotipo_travasala == 'Y') {
												$checkedtravasala = "checked='checked'";
											} else {
												$checkedtravasala = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="travasala" type="checkbox" aria-label="..." onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'travasala')" <?= $checkedtravasala ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Trava Tag" readonly="">
											</div>
										</td>
										<td>
											<? if ($_1_u_eventotipo_alertar == 'Y') {
												$checkedalertar = "checked='checked'";
											} else {
												$checkedalertar = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Alertar" id="alerta" type="checkbox" aria-label="..." onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'alertar')" <?= $checkedalertar ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Alertar" readonly="">
											</div>
										</td>
										<td>
											<? if ($_1_u_eventotipo_sla == 'Y') {
												$checkedsla = "checked='checked'";
											} else {
												$checkedsla = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Sla" id="sla" type="checkbox" aria-label="..." onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'sla')" <?= $checkedsla ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Sla" readonly="">
											</div>
										</td>
										<td>
											<? if ($_1_u_eventotipo_dashboard == 'Y') {
												$checkeddashboard = "checked='checked'";
											} else {
												$checkeddashboard = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="dashboard" id="alerta" type="checkbox" aria-label="..." onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'dashboard')" <?= $checkeddashboard ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Dashboard" readonly="">
											</div>
										</td>
									</tr>
									<tr>
										<td>
											<? if ($_1_u_eventotipo_anonimo == 'Y') {
												$checkedanonimo = "checked='checked'";
											} else {
												$checkedanonimo = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="anônimo" id="alerta" type="checkbox" aria-label="..." onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'anonimo')" <?= $checkedanonimo ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Anônimo" readonly="">
											</div>
										</td>
										<td>
											<? if ($_1_u_eventotipo_evcor == 'Y') {
												$checkedevcor = "checked='checked'";
											} else {
												$checkedevcor = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="rnc" type="checkbox" aria-label="..." value="" onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'evcor')" <?= $checkedevcor ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Cor" readonly="">
											</div>
										</td>
										<td>
											<? if ($_1_u_eventotipo_rnc == 'Y') {
												$checkedrnc = "checked='checked'";
											} else {
												$checkedrnc = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="rnc" type="checkbox" aria-label="..." value="" onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'rnc')" <?= $checkedrnc ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="RNC" readonly="">
											</div>
										</td>
										<td>
											<? //Setar se aparece ou não no evento (Adicionar link dos Eventos) (Lidiane - 21-02-2020) 
											?>
											<? if ($_1_u_eventotipo_anexolink == 'Y') {
												$checkedanexolink = "checked='checked'";
											} else {
												$checkedanexolink = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="anexolink" type="checkbox" aria-label="..." value="" onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'anexolink')" <?= $checkedanexolink ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Anexar Link" readonly="">
												<!-- Tornar obrigatorio -->
												<input id="obrigatorio-anexarlink" class="hidden" type="checkbox" value="1" onclick="setObrigatorio(this, 'anexarlink')" />
												<label class="input-group-addon pointer hoverazul transition px-3 text-xs" for="obrigatorio-anexarlink" title="">
													<i class="fa fa-asterisk"></i>
												</label>
											</div>
										</td>
										<td>
											<? //Setar se aparece ou não no evento (Permitir Comentário) (Lidiane - 26-02-2020) 
											?>
											<? if ($_1_u_eventotipo_comentario == 'Y') {
												$checkedanexocomentario = "checked='checked'";
											} else {
												$checkedanexocomentario = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="comentario" type="checkbox" aria-label="..." value="" onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'comentario')" <?= $checkedanexocomentario ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Permitir Comentário" readonly="">
											</div>
										</td>
										<td>
											<? //Setar se aparece ou não no evento (Incluir Participantes) (Lidiane - 26-02-2020) 
											?>
											<? if ($_1_u_eventotipo_participantes == 'Y') {
												$checkedanexoparticipantes = "checked='checked'";
											} else {
												$checkedanexoparticipantes = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="participantes" type="checkbox" aria-label="..." value="" onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'participantes')" <?= $checkedanexoparticipantes ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Incluir Participantes" readonly="">
											</div>
										</td>
										<td>
											<? //Setar se aparece ou não no evento (Incluir Participantes) (Lidiane - 26-02-2020) 
											?>
											<? $checkedPrazoPrevisto = ($_1_u_eventotipo_prazoprevisto == 'Y') ? "checked='checked'" : ""; ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="prazoprevisto" type="checkbox" aria-label="..." value="" onchange="eventotipochecked(this, <?=$_1_u_eventotipo_ideventotipo?>, 'prazoprevisto')" <?=$checkedPrazoPrevisto?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Prazo Previsto" readonly="">
											</div>
										</td>
									</tr>
									<tr>
										<td>
											<? //Setar se aparece ou não no evento (Repetir Evento) (Lidiane - 26-02-2020) 
											?>
											<? if ($_1_u_eventotipo_repetirevento == 'Y') {
												$checkedanexorepetirevento = "checked='checked'";
											} else {
												$checkedanexorepetirevento = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="repetirevento" type="checkbox" aria-label="..." value="" onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'repetirevento')" <?= $checkedanexorepetirevento ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Repetir Evento" readonly="">
											</div>
										</td>
										<td>
											<? //Setar se o evento será Privado - Alterado de eventopai para privado (Lidiane - 21-05-2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=321527
											?>
											<? if ($_1_u_eventotipo_privado == 'Y') {
												$checkedprivado = "checked='checked'";
											} else {
												$checkedprivado = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="privado" type="checkbox" aria-label="..." value="" onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'privado')" <?= $checkedprivado ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Privado" readonly="">
											</div>
										</td>
										<td>
											<? //Setar o atendimento_telefonico aparecerá no Evento (Lidiane - 08-04-2020) 
											?>
											<? if ($_1_u_eventotipo_atendimentotelefonico == 'Y') {
												$checkedvisualizaratendimentotelefonico = "checked='checked'";
											} else {
												$checkedvisualizaratendimentotelefonico = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="atendimentotelefonico" type="checkbox" aria-label="..." value="" onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'atendimentotelefonico')" <?= $checkedvisualizaratendimentotelefonico ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Forma Atendimento" readonly="">
											</div>
										</td>
										<td>
											<? //Setar o atendimento_telefonico aparecerá no Evento (Lidiane - 08-04-2020) 
											?>
											<? if ($_1_u_eventotipo_prevhoras == 'Y') {
												$checkedprevhoras = "checked='checked'";
												$displayRelacionamento = '';
											} else {
												$checkedprevhoras = '';
												$displayRelacionamento = 'display:none;';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="prevhoras" type="checkbox" aria-label="..." value="" onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'prevhoras')" <?= $checkedprevhoras ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Previsão de Horas" readonly="">
											</div>
										</td>
										<td>
											<? //Adicionado o Prazo que será setado para o Prazo final em dias (LTM - 17-07-2020)
											?>
											<? if ($_1_u_eventotipo_horasprazo == 'Y') {
												$checkedhorasprazo = "checked='checked'";
											} else {
												$checkedhorasprazo = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="horasprazo" type="checkbox" aria-label="..." value="" onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'horasprazo')" <?= $checkedhorasprazo ?>>
												</span>
												<input style="background: #eee;" type="text" disabled class="form-control size7" aria-label="..." value="Horas Prazo">
												<input type="text" id="quantidadehorasprazo" name="_1_u_eventotipo_quantidadehorasprazo" class="form-control size4" aria-label="..." title="Horas para Início do Evento" value="<?= $_1_u_eventotipo_quantidadehorasprazo ?>">
											</div>
										</td>
										<td>
											<? //Setar o atendimento_telefonico aparecerá no Evento (Lidiane - 08-04-2020) 
											?>
											<? if ($_1_u_eventotipo_link == 'Y') {
												$checkedvisualizarlink = "checked='checked'";
											} else {
												$checkedvisualizarlink = '';
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="link" type="checkbox" aria-label="..." value="" onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'link')" <?= $checkedvisualizarlink ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Links" readonly="">
											</div>
										</td>
										<td>
											<?
											$checkedvisualizardestaque = '';
											if ($_1_u_eventotipo_destaque == 'Y') {
												$checkedvisualizardestaque = "checked='checked'";
											} ?>
											<div class="input-group" style="margin-top: 5px;">
												<span class="input-group-addon">
													<input title="Exibir" id="destaque" type="checkbox" aria-label="..." value="" onchange="eventotipochecked(this,<?= $_1_u_eventotipo_ideventotipo ?>,'destaque')" <?= $checkedvisualizardestaque ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Destaque" readonly="">
											</div>
										</td>	
										<td></td>
									</tr>
									<tr>
										<td>
											<? if ($_1_u_eventotipo_relacionamento == 'Y') {
												$checkedrelacionamento = "checked='checked'";
											} else {
												$checkedrelacionamento = '';
											} ?>
											<div class="input-group div-relacionamento" style="margin-top: 5px; <?=$displayRelacionamento?>" >
												<span class="input-group-addon">
													<input title="RELACIONAMENTO" id="centrotrabalho" type="checkbox" aria-label="..." onchange="eventotipochecked(this, <?=$_1_u_eventotipo_ideventotipo ?>, 'relacionamento')" <?=$checkedrelacionamento ?>>
												</span>
												<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Relacionamento" readonly="">
												<!-- Tornar obrigatorio -->
												<input id="obrigatorio-relacionamento" class="hidden" type="checkbox" value="1" onclick="setObrigatorio(this, 'relacionamento')" />
												<label class="input-group-addon pointer hoverazul transition px-3 text-xs" for="obrigatorio-relacionamento" title="">
													<i class="fa fa-asterisk"></i>
												</label>
											</div>
										</td>
									</tr>
								</table>
							</div>
						</div>

						<? //SLA
						if ($_1_u_eventotipo_sla == 'Y' || $_1_u_eventotipo_relacionamento == 'Y') {
							?>
							<div class="col-lg-14">
								<div class="panel-status">									
									<div class="panel-body">										
										<div class="panel-heading texto_configuracao" style="margin-top: 12%;">Configuração SLA</div>
										<? if (count($eventoSla)) { ?>
											<table class="table table-striped planilha">
												<tr>
													<th>Serviço</th>
													<th>Prioridade</th>
													<th>Tempo</th>
													<th></th>
												</tr>
												<?
												foreach ($eventoSla as $evento) {
													$ifi = $ifi + 1;
													?>
													<tr>
														<td>
															<input type="hidden" name="_sla<?= $ifi ?>_u_eventosla_ideventosla" value="<?= $evento["ideventosla"] ?>">
															<input type="text" name="_sla<?= $ifi ?>_u_eventosla_servico" value="<?= $evento["servico"] ?>">
														</td>
														<td>
															<input type="text" name="_sla<?= $ifi ?>_u_eventosla_prioridade" value="<?= $evento["prioridade"] ?>">
														</td>
														<td>
															<input type="text" name="_sla<?= $ifi ?>_u_eventosla_sla" class="sla" value="<?= $evento["sla"] ?>" min="0" max="100">
														</td>
														<td>
															<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="CB.post({objetos:'_ajax_d_eventosla_ideventosla=<?= $evento['ideventosla'] ?>',parcial:true})" title="Excluir!"></i>
														</td>
													</tr>
												<? } ?>
											</table>
										<? } ?>
										<button id="novoStatus" class="btn btn-success" onclick="CB.post({objetos:'_ajax_i_eventosla_ideventotipo=<?= $_1_u_eventotipo_ideventotipo ?>',parcial:true})"><i class="fa fa-plus"></i></button>
									</div>

									<? //Relacionamento
									if ($_1_u_eventotipo_relacionamento == 'Y') {
										?>
										<div class="panel-body">
											<div class="panel-heading texto_configuracao" style="margin-top: 1%;">Configuração Relacionamento</div>
											<? if (count($eventoRelacionamento)) { ?>
												<table class="table table-striped planilha">
													<tr>
														<th>Descrição</th>
														<th>Status</th>
														<th></th>
													</tr>
													<?
													foreach ($eventoRelacionamento as $relacionamento) {
														$ifi = $ifi + 1;
														?>
														<tr>
															<td>
																<input type="hidden" class="relacionamentoclass" name="_res<?=$ifi ?>_u_eventorelacionamento_ideventorelacionamento" value="<?=$relacionamento["ideventorelacionamento"] ?>">
																<input type="hidden" class="relacionamentoclass" name="_res<?=$ifi ?>_u_eventorelacionamento_ideventotipo" value="<?=$_1_u_eventotipo_ideventotipo ?>">
																<input type="text" class="relacionamentoclass" name="_res<?=$ifi ?>_u_eventorelacionamento_descricao" value="<?=$relacionamento["descricao"] ?>">
															</td>
															<td>
																<select class="relacionamentoclass" name="_res<?=$ifi ?>_u_eventorelacionamento_status">
																	<option></option>
																	<option value='A' <? echo ($relacionamento["status"] == 'A') ? 'selected' : '' ?>>Ativo</option>
																	<option value='I' <? echo ($relacionamento["status"] == 'I') ? 'selected' : '' ?>>Inativo</option>
																</select>
															</td>
															<td>
																<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="CB.post({objetos:'_ajax_d_eventorelacionamento_ideventorelacionamento=<?= $relacionamento['ideventorelacionamento'] ?>', parcial:true})" title="Excluir!"></i>
															</td>
														</tr>
													<? } ?>
												</table>
											<? } ?>
											<button id="novoStatus" class="btn btn-success" onclick="addRelacionamento(<?=$_1_u_eventotipo_ideventotipo ?>)"><i class="fa fa-plus"></i></button>
										</div>
									<? } ?>
								</div>
							</div>
						<? } ?>						
					</div>
				<?
				} //if(!empty($_1_u_eventotipo_ideventotipo)){
				?>
			</div>
		</div>
	</div>
</div>
<?
if ($_1_u_eventotipo_ideventotipo) {
	foreach ($eventoTipoAdd as $eventoTipo) {
		//Se o campo MiniEvento estiver marcado desabilita os outros - LTM (30/06/2020)
		if (in_array('Y', [$eventoTipo['minievento'], $eventoTipo['tipocampos'], $eventoTipo['criasolmat']])) {
			$checkedminievento = $eventoTipo['minievento'] == 'Y' ? "checked='checked'" : "";
			$checkedEventoTipoCampos = $eventoTipo['tipocampos'] == 'Y' ? "checked='checked'" : "";
			$tipoCamposDisplay = $eventoTipo['tipocampos'] == 'Y' ? "" : 'style="display:none"';
			$planilhagrade = 'style="display:none"';
			$disabled = 'disabled';
		} else {
			$checkedminievento = '';
			$checkedEventoTipoCampos = '';
			$tipoCamposDisplay = 'style="display:none"';
			$planilhagrade = '';
			$disabled = '';
		}
		$i = $i + 1;
?>
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="row">
							<div class="col-md-1">Título:</div>
							<div class="col-md-10">
								<input name="_ad<?= $i ?>_u_eventotipoadd_ideventotipoadd" type="hidden" value="<?= $eventoTipo['ideventotipoadd'] ?>" readonly='readonly'>
								<input class='size40' style="width:100% !important" name="_ad<?= $i ?>_u_eventotipoadd_titulo" type="text" value="<?= $eventoTipo['titulo'] ?>">
							</div>
							<div class="col-md-1">
								<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="CB.post({objetos:'_ajax_u_eventotipoadd_ideventotipoadd=<?= $eventoTipo['ideventotipoadd'] ?>&_ajax_u_eventotipoadd_status=INATIVO',parcial:true})" title="Excluir!"></i>
							</div>
						</div>
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-12">
								<label>Observação:</label>
								<textarea name="_ad<?= $i ?>_u_eventotipoadd_observacao" rows="3"><?= $eventoTipo["observacao"] ?></textarea>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-2">
								<? if ($eventoTipo['tag'] == 'Y') {
									$checkedtag = "checked='checked'";
									$disptagtipoobj = 'block';
								} else {
									$checkedtag = '';
									$disptagtipoobj = 'none';
								} ?>
								<div class="input-group">
									<span class="input-group-addon">
										<input id="equipamentocheck" class="equipamento<?= $eventoTipo['ideventotipoadd'] ?>" type="checkbox" aria-label="..." onchange="equipamentocheckedad(this,<?= $eventoTipo['ideventotipoadd'] ?>)" <?= $checkedtag ?> <?= $disabled ?>>
									</span>
									<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Equipamento" readonly="" style="width:100%;padding:0px;">
								</div>
								<div id="tagtipoobjad<?= $eventoTipo['ideventotipoadd'] ?>" style="display:<?= $disptagtipoobj ?>; ">
									<select class="selectpicker selecttipo" multiple="multiple" data-live-search="true" title="Tipo Equipamento" onchange="atualizavalorad(this,<?= $eventoTipo['ideventotipoadd'] ?>);">
										<?
										$arrvalor = explode(',', $eventoTipo['tagtipoobj']);

										foreach ($todosTagtipo as $tagTipo) {
											$selected = '';

											if (in_array($tagTipo['idtagtipo'], $arrvalor)) {
												$selected = 'selected';
											}

											echo "<option data-tokens='" . retira_acentos($tagTipo['tagtipo']) . "' value='{$tagTipo['idtagtipo']}' $selected>{$tagTipo['tagtipo']}</option>";
										} ?>
									</select>
								</div>
							</div>

							<div class="col-lg-2">
								<? if ($eventoTipo['sgdoc'] == 'Y') {
									$checkedsgdoc = "checked='checked'";
									$dispsgdoctipoobj = 'block';
								} else {
									$checkedsgdoc = '';
									$dispsgdoctipoobj = 'none';
								} ?>
								<div class="input-group">
									<span class="input-group-addon">
										<input id="documentocheck" class="documento<?= $eventoTipo['ideventotipoadd'] ?>" type="checkbox" aria-label="..." onchange="documentocheckedad(this,<?= $eventoTipo['ideventotipoadd'] ?>)" <?= $checkedsgdoc ?> <?= $disabled ?>>
									</span>
									<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Documento" readonly="" style="width:100%;padding:0px;">
								</div>

								<div id="sgdoctipoobjad<?= $eventoTipo['ideventotipoadd'] ?>" style="display:<?= $dispsgdoctipoobj ?>">
									<select class="selectpicker selecttipo" multiple="multiple" data-live-search="true" title="Tipo Documento" onchange="atualizavalordocad(this,<?= $eventoTipo['ideventotipoadd'] ?>);">
										<?
										$arrvalor = explode(',', $eventoTipo['sgdoctipoobj']);
										foreach ($todosSgDocTipo as $sgDocTipo) {
											$selected = '';

											if (in_array($sgDocTipo['idsgdoctipo'], $arrvalor)) {
												$selected = 'selected';
											}

											echo "<option data-tokens='" . retira_acentos($sgDocTipo['rotulo']) . "' value='{$sgDocTipo['idsgdoctipo']}' $selected>{$sgDocTipo['rotulo']}</option>";
										}
										?>
									</select>
								</div>
							</div>

							<div class="col-lg-2">
								<? if ($eventoTipo['pessoa'] == 'Y') {
									$checkedpessoa = "checked='checked'";
									$disptipopessoaobj = 'block';
								} else {
									$checkedpessoa = '';
									$disptipopessoaobj = 'none';
								} ?>
								<div class="input-group">
									<span class="input-group-addon">
										<input id="pessoacheck" class="pessoa<?= $eventoTipo['ideventotipoadd'] ?>" type="checkbox" aria-label="..." onchange="pessoacheckedad(this,<?= $eventoTipo['ideventotipoadd'] ?>)" <?= $checkedpessoa ?> <?= $disabled ?>>
									</span>
									<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Pessoa" readonly="" style="width:100%;padding:0px;">
								</div>
								<div id='tipopessoaobjad<?= $eventoTipo['ideventotipoadd'] ?>' style="display:<?= $disptipopessoaobj ?>">
									<select class="selectpicker selecttipo" multiple="multiple" data-live-search="true" title="Tipo Pessoa" onchange="atualizavalorpessoaad(this,<?= $eventoTipo['ideventotipoadd'] ?>);">
										<?
										$arrvalor = explode(',', $eventoTipo['tipopessoaobj']);

										foreach ($todosTipoPessoa as $tipoPessoa) {
											$selected = '';

											if (in_array($tipoPessoa['idtipopessoa'], $arrvalor)) {
												$selected = 'selected';
											}

											echo "<option data-tokens='" . retira_acentos($tipoPessoa['tipopessoa']) . "' value='{$tipoPessoa['idtipopessoa']}' $selected>{$tipoPessoa['tipopessoa']}</option>";
										}
										?>
									</select>
								</div>
							</div>

							<div class="col-lg-2">
								<? if ($eventoTipo['prodserv'] == 'Y') {
									$checkedprodserv = "checked='checked'";
									$dispprodservtipoobj = 'block';
								} else {
									$checkedprodserv = '';
									$dispprodservtipoobj = 'none';
								} ?>
								<div class="input-group">
									<span class="input-group-addon">
										<input id="prodservcheck" class="prodserv<?= $eventoTipo['ideventotipoadd'] ?>" type="checkbox" aria-label="..." onchange="prodservcheckedad(this,<?= $eventoTipo['ideventotipoadd'] ?>)" <?= $checkedprodserv ?> <?= $disabled ?>>
									</span>
									<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Produto" readonly="" style="width:100%;padding:0px;">
								</div>

								<div id="prodservtipoobjad<?= $eventoTipo['ideventotipoadd'] ?>" style="display:<?= $dispprodservtipoobj ?>">
									<select class="selectpicker selecttipo" multiple="multiple" data-live-search="true" title="Tipo Produto" onchange="atualizavalorprodservad(this,<?= $eventoTipo['ideventotipoadd'] ?>);">
										<?
										$arrvalor = explode(',', $eventoTipo['prodservtipoobj']);

										$sgDocTipoCampo = [
											['descr' => 'comissionado'],
											['descr' => 'comprado'],
											['descr' => 'fabricado'],
											['descr' => 'material'],
											['descr' => 'venda']
										];

										$resm =  d::b()->query($sqlm)  or die("Erro sgdoctipo campo 	Prompt Drop sql:" . $sqlm);
										foreach ($sgDocTipoCampo as $campo) {
											$selected = '';

											if (in_array($campo['descr'], $arrvalor)) {
												$selected = 'selected';
											}

											echo "<option data-tokens='" . retira_acentos($campo['descr']) . "' value='{$campo['descr']}' $selected>{$campo['descr']}</option>";
										}
										?>
									</select>
								</div>
							</div>

							<? //Acrescentado para Eventos que precisam de outro evento que será listado dentro da propria pagina (LTM - 08/07/2020) 
							?>
							<div class="col-lg-2">
								<div class="input-group">
									<span class="input-group-addon">
										<input id="minieventocheck" class="minievento<?= $eventoTipo['ideventotipoadd'] ?>" type="checkbox" aria-label="..." onchange="minieventocheckedad(this,<?= $eventoTipo['ideventotipoadd'] ?>)" <?= $checkedminievento ?>>
									</span>
									<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Evento" readonly="" style="width:100%;padding:0px;">
								</div>
							</div>

							<div class="col-lg-2">
								<div class="input-group">
									<span class="input-group-addon">
										<input id="eventotipocamposcheck" class="eventotipocampos<?= $eventoTipo['ideventotipoadd'] ?>" type="checkbox" aria-label="..." onchange="eventoTipoCamposCheckedad(this,<?= $eventoTipo['ideventotipoadd'] ?>)" <?= $checkedEventoTipoCampos ?>>
									</span>
									<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Tipo Campos" readonly="" style="width:100%;padding:0px;">
								</div>
							</div>
							<div class="col-lg-2">
								<?
								$checkedsolmat = '';
								$dispsolmat = 'none';

								if ($eventoTipo['criasolmat'] == 'Y') {
									$checkedsolmat = "checked='checked'";
									$dispsolmat = 'block';
								} ?>
								<div class="input-group">
									<span class="input-group-addon">
										<input id="solmatcheck" class="solmatcheck<?= $eventoTipo['ideventotipoadd'] ?>" type="checkbox" aria-label="..." onchange="geraSolmatCheck(this,<?= $eventoTipo['ideventotipoadd'] ?>)" <?= $checkedsolmat ?>>
									</span>
									<input style="background: #eee;" type="text" class="form-control" aria-label="..." value="Gerar solmat" readonly="" style="width:100%;padding:0px;">
								</div>
							</div>

						</div>
					</div>
					<div class="row" style="margin-left:0">
						<div class="col-lg-8">
							<?
							$inserindoCamposEventoTipoAdd = EventoTipoController::inserirNovoCampoEventoTipoAddPorIdEventoTipoAdd($eventoTipo['ideventotipoadd']);
							$camposEventoTipoAdd = EventoTipoController::buscarEventoTipoCamposPorIdEventoTipoAdd($eventoTipo['ideventotipoadd']);
							?>
							<table class="planilha grade compacto" id="planilhagrade<?= $eventoTipo['ideventotipoadd'] ?>" <?= $planilhagrade ?> <?= $estado ?>>
								<tr>
									<th class="size25">Coluna</th>
									<th class="size25">Rótulo</th>
									<th class="size5"><i class="fa fa-eye" title="Coluna visível no evento"></th>
									<th class="size30">Rótulo Evento</th>
									<th class="size10">Prompt</th>
									<th class="size40">Code</th>
									<th class="size10">Ordem</th>
								</tr>
								<?
								foreach ($camposEventoTipoAdd as $camposTipoAdd) {
									$if++;
									$estado = ($camposTipoAdd['visivel'] == 'Y') ? "checked='checked'" : "";
									$validanulo = ($camposTipoAdd['visivel'] == 'Y') ? "vnulo" : "";
								?>
									<tr>
										<td><?= $camposTipoAdd["col"] ?></td>
										<td><?= $camposTipoAdd["rotpsq"] ?></td>

										<td>
											<input type="checkbox" onclick="toggle(<?= $camposTipoAdd['ideventotipocampos'] ?>,this)" <?= $estado ?>>
										</td>
										<td>
											<input name="_<?= $if ?>_u_eventotipocampos_ideventotipocampos" type="hidden" value="<?= $camposTipoAdd["ideventotipocampos"] ?>" readonly='readonly'>
											<input name="_<?= $if ?>_u_eventotipocampos_rotulo" type="text" value="<?= $camposTipoAdd["rotulo"] ?>">

										</td>
										<td>
											<select name="_<?= $if ?>_u_eventotipocampos_prompt">
												<option value=''></option>
												<? fillselect([
													'select' => 'select'
												], $camposTipoAdd["prompt"]); ?>
											</select>
										</td>
										<td>
											<textarea name="_<?= $if ?>_u_eventotipocampos_code" rows="1"><?= $camposTipoAdd["code"] ?></textarea>
										</td>
										<td>
											<input name="_<?= $if ?>_u_eventotipocampos_ord" class="size10" type="text" value="<?= $camposTipoAdd["ord"] ?>" <?= $validanulo ?>>
										</td>
									</tr>
								<? } ?>
							</table>
						</div>

						<div <?= $tipoCamposDisplay ?>>
							<?= mountarSelecaoDeCamposHTML('eventotipoadd') ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?
	} //while($eventoTipo=mysqli_fetch_assoc($radd)){
	?>

	<? if ($_1_u_eventotipo_ideventotipo) { ?>
		<div class="d-flex flex-wrap row">
			<div class="col-xs-12 col-md-5">
				<!-- Inserir unidades -->
				<div class="panel panel-default">
					<div class="panel-heading">Unidades:</div>
					<div class="panel-body">
						<table>
							<tr>
								<td><input id="unidades" cbvalue placeholder="Selecionar unidade"></td>
							</tr>
						</table>
						<table class='table-hover'>
							<tbody>
								<?= montarUnidadeHTML() ?>
							</tbody>
						</table>
						<hr>
					</div>
				</div>
			</div>
		</div>
	<? } ?>

	<div class='row'>
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-body">
					<div class="agrupamento novo">
						Adicional:
						<button id="novoStatus" class="btn btn-success" onclick="novoadd(<?= $_1_u_eventotipo_ideventotipo ?>);"><i class="fa fa-plus"></i></button>
					</div>
				</div>
			</div>
		</div>
	</div>
<?
}
?>

<?
if (!empty($_1_u_eventotipo_ideventotipo)) {	// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_eventotipo_ideventotipo; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}

$tabaud = "eventotipo"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';

require_once(__DIR__ . "/js/eventotipo_js.php");
?>