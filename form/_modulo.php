<?
require_once("../inc/php/validaacesso.php");
require_once("./controllers/_modulo_controller.php");
require_once("./controllers/lote_controller.php");

if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

if (empty($_acao)) {
	$_acao = $_GET["_acao"];
}

if ($_acao !== "i") {
	if ($_pkid && $_modulo == '_modulo') {
		$_GET["idmodulo"] = $_pkid;
	}
	//Centraliza a recuperação de informações de Módulo, para considerar diferenças de Módulos vinculados
	$aMod = retArrModuloConf($_GET["idmodulo"], true);

	//Cria automaticamente as variaveis conforme mtotabcol
	$_arrtabdef = retarraytabdef($pagvaltabela);
	//echo $pagvaltabela;
	//print_r($aMod);
	foreach ($aMod as $k => $v) {
		$fldvar = "_1_u__modulo_" . $k;
		$_fldtype = $_arrtabdef[$k]["type"];
		//executa pre tratamento para formatacao de informacoes
		$$fldvar = formatastringvisualizacao($v, $_fldtype);
	}

	/*
	 * Inicializa variáveis de controle para módulos vinculados, que herdam parà¢metros de outro módulo,
	 * para que seja possivel reaproveitar códigos de eventos change, etc.
	 */

	$configmoduloReal = _moduloController::verificaModVinc($_1_u__modulo_modulo);

	if ($_1_u__modulo_tipo == "MODVINC") {

		$redtab = _moduloController::buscarTabModVinc($_1_u__modulo_modulo);

		if (!empty($redtab)) {
			$tab = $redtab['tab'];
		}
		$_1_u__modulo_tab = $tab;
		$estado2 = "style='background-color:transparent !important;border:0px !important;pointer-events:none !important;'";
	}

	if ($_1_u__modulo_tipo == "MODVINC" and !$configmoduloReal) {


		$moduloReal = $_1_u__modulo_modvinculado;

		$estado = "style='background-color:transparent !important;border:0px !important;pointer-events:none !important;'";
		$colvinc = "col-md-6";
		$estado = "";
		$colvinc = "col-md-12";
	} else {
		$moduloReal = $_1_u__modulo_modulo;
		$estado = "";
		$colvinc = "col-md-12";
	}
}
/* *************************** Variável com os arquivos .PHP da pasta /form *************************** */
//lista os arquivos existentes na pasta /form do carbon, excluindo arquivos ocultos '.' e '..'
$strpathforms = _CARBON_ROOT . "form/";

$arrscan = preg_grep('/^([^.])/', scandir($strpathforms));

if (!$arrscan) {
	echo "Carbon root não encontrado: " . $strpathforms;
}

$arrformularios = array();
foreach ($arrscan as $itemscan) {
	$arrformularios["form/" . $itemscan] = "form/" . $itemscan;
	$arrscanSub = preg_grep('/^([^.])/', scandir($strpathforms . $itemscan));
	if ($arrscanSub) {
		foreach ($arrscanSub as $itemscanSub) {
			$arrformularios["form/" . $itemscan . "/" . $itemscanSub] = "form/" . $itemscan . "/" . $itemscanSub;
		}
	}
}

//lista os arquivos existentes na pasta /rep do carbon, excluindo arquivos ocultos '.' e '..'
$strpathrep = _CARBON_ROOT . "report/";
$arrscan = preg_grep('/^([^.])/', scandir($strpathrep));

foreach ($arrscan as $itemscan) {
	$arrformularios["report/" . $itemscan] = "report/" . $itemscan;
}

//lista os arquivos existentes na pasta /ajax do carbon, excluindo arquivos ocultos '.' e '..'
$strpathrep = _CARBON_ROOT . "ajax/";
$arrscan = preg_grep('/^([^.])/', scandir($strpathrep));

foreach ($arrscan as $itemscan) {
	$arrformularios["ajax/" . $itemscan] = "ajax/" . $itemscan;
}

/* *************************** Atualizar automaticamente a tabela _formobjetos conforme o formulário selecionado na drop *************************** */
//Làª o formulario salvo na drop para extrair objetos de banco de dados e arquivos ajax
$_arqdest = _CARBON_ROOT . $_1_u__modulo_urldestino;
$contarq = file_get_contents($_arqdest);
//echo "<!--".$contarq."-->";
//Conteúdo do arquivo
if (!$contarq and !empty($_1_u__modulo_urldestino)) {
	if ($_acao != "i") echo ('Arquivo inexistente: ' . $_arqdest);
} else {
	$arrpalavras = preg_split('/\s+/', $contarq, -1, PREG_SPLIT_NO_EMPTY);

	$arrFormObj = array();
	$arrArqAjax = array();
	$strBulkInsert;
	$virg = "";

	//print_r($arrpalavras);

	//Loop em cada palavra (linha) do arquivo:
	foreach ($arrpalavras as $palavra) {
		//1 - Extrai todas as referàªncias de tabelas do código por nomeclaturas válidas do carbon
		$arrInputName = explodeInputNameCarbon($palavra);
		$nomeTabela = $arrInputName[2];
		$chamadaCbpost = $arrInputName["cbpost"];
		if ($arrInputName) {
			if ($chamadaCbpost) {
				$arrFormObj[$nomeTabela]["tipoobjeto"] = "tabelacbpost";
			} else {
				$arrFormObj[$nomeTabela]["tipoobjeto"] = "tabela";
			}
		}

		//2 Extrai os arquivos ajax mencionados no código: string entre (quaisquer)AJAX[quaisquerpalavras].php(quaisquer)
		$pattern = "#ajax[^.*?]*.php#";
		$iajax = preg_match($pattern, $palavra, $matches);
		if ($iajax > 0) {
			//echo "\n".$palavra."\n";
			//Como a expressão acima retorna "AJAX/arquivo.php" ehe necessario extrair somente o nome do arquivo/;
			$strBName = pathinfo($matches[0], PATHINFO_BASENAME);
			$arrFormObj[$strBName]["tipoobjeto"] = "ajax";
		}
	}
	//print_r($arrFormObj);die;

	//Apaga registros antigos QUE NAO FORAM INSERIDOS PELO USUARIO
	mysql_query("delete from " . _DBCARBON . "._formobjetos where modulo = '" . $_1_u__modulo_modulo . "' and form = '" . $_1_u__modulo_urldestino . "' and inseridomanualmente = 'N'") or die("Erro ao apagar objetos relacionados: " . mysql_error() . "\Nsql: " . $strbulk);

	//Loop na lista de objetos recuperados
	if (sizeof($arrFormObj) > 0) {
		foreach ($arrFormObj as $tab => $opt) {
			$strBulkInsert .= $virg . "('" . $_1_u__modulo_modulo . "','" . $_1_u__modulo_urldestino . "','" . $opt["tipoobjeto"] . "','" . $tab . "','" . $_SESSION["SESSAO"]["USUARIO"] . "',now())";
			$virg = ",";
		}
		//Insere no banco
		$strbulk = "REPLACE into " . _DBCARBON . "._formobjetos (modulo, form,tipoobjeto,objeto,criadopor,criadoem) values " . $strBulkInsert;
		mysql_query($strbulk) or die("Erro ao efetuar bulk insert para objetos relacionados: " . mysql_error() . "\Nsql: " . $strbulk);

		$sqlarq = "SELECT 1 from " . _DBCARBON . "._formobjetos where modulo='" . $_1_u__modulo_modulo . "' and objeto='arquivo'";
		$res = d::b()->query($sqlarq) or die("Erro formobjetos:" . mysqli_error(d::b()) . "\n" . $sqlarq);
		if (mysqli_num_rows($res) == 0) {
			$strbulk1 = "INSERT ignore into " . _DBCARBON . "._formobjetos 
			(modulo, form,tipoobjeto,objeto,criadopor,criadoem,inseridomanualmente) values 
			('" . $_1_u__modulo_modulo . "','" . $_1_u__modulo_urldestino . "','tabelacbpost','arquivo','" . $_SESSION["SESSAO"]["USUARIO"] . "',now(),'Y')";
			mysql_query($strbulk1) or die("Erro ao efetuar bulk insert para objetos relacionados: " . mysql_error() . "\Nsql: " . $strbulk1);
		}
	}
}

//Làª o o arquivo js do formulario salvo na drop para extrair objetos de banco de dados e arquivos ajax
$patterns = array(
	'/(form\/)/',
	'/(report\/)/',
	'/(\.php)/',
);
$_arqdestjs =  _CARBON_ROOT . '/form/js/' . preg_replace($patterns, '', $_1_u__modulo_urldestino) . '_js.php';
$contarqjs = file_get_contents($_arqdestjs);
//Conteúdo do arquivo
if (!$contarqjs and !empty($_1_u__modulo_urldestino)) {
	//if($_acao!="i")echo('Arquivo inexistente: '.$_arqdestjs);
} else {
	$arrpalavras = preg_split('/\s+/', $contarqjs, -1, PREG_SPLIT_NO_EMPTY);

	$arrFormObj = array();
	$arrArqAjax = array();
	$strBulkInsert = "";
	$virg = "";

	//print_r($arrpalavras);

	//Loop em cada palavra (linha) do arquivo:
	foreach ($arrpalavras as $palavra) {
		//1 - Extrai todas as referàªncias de tabelas do código por nomeclaturas válidas do carbon
		$arrInputName = explodeInputNameCarbon($palavra);
		$nomeTabela = $arrInputName[2];
		$chamadaCbpost = $arrInputName["cbpost"];
		if ($arrInputName) {
			if ($chamadaCbpost) {
				$arrFormObj[$nomeTabela]["tipoobjeto"] = "tabelacbpost";
			} else {
				$arrFormObj[$nomeTabela]["tipoobjeto"] = "tabela";
			}
		}

		//2 Extrai os arquivos ajax mencionados no código: string entre (quaisquer)AJAX[quaisquerpalavras].php(quaisquer)
		$pattern = "#ajax[^.*?]*.php#";
		$iajax = preg_match($pattern, $palavra, $matches);
		if ($iajax > 0) {
			//echo "\n".$palavra."\n";
			//Como a expressão acima retorna "AJAX/arquivo.php" ehe necessario extrair somente o nome do arquivo/;
			$strBName = pathinfo($matches[0], PATHINFO_BASENAME);
			$arrFormObj[$strBName]["tipoobjeto"] = "ajax";
		}
	}
	//print_r($arrFormObj);die;

	//Apaga registros antigos QUE NAO FORAM INSERIDOS PELO USUARIO
	//mysql_query("delete from "._DBCARBON."._formobjetos where modulo = '".$_1_u__modulo_modulo."' and form = '".$_1_u__modulo_urldestino."' and inseridomanualmente = 'N'") or die("Erro ao apagar objetos relacionados: ".mysql_error()."\Nsql: ".$strbulk);

	//Loop na lista de objetos recuperados
	if (sizeof($arrFormObj) > 0) {
		foreach ($arrFormObj as $tab => $opt) {
			$strBulkInsert .= $virg . "('" . $_1_u__modulo_modulo . "','" . $_1_u__modulo_urldestino . "','" . $opt["tipoobjeto"] . "','" . $tab . "','" . $_SESSION["SESSAO"]["USUARIO"] . "',now())";
			$virg = ",";
		}
		//Insere no banco
		$strbulk = "REPLACE into " . _DBCARBON . "._formobjetos (modulo, form,tipoobjeto,objeto,criadopor,criadoem) values " . $strBulkInsert;
		mysql_query($strbulk) or die("Erro ao efetuar bulk insert para objetos relacionados: " . mysql_error() . "\Nsql: " . $strbulk);
	}
}

if ($_1_u__modulo_modulo) {
	/* *************************** Lista lps relacionadas ao modulo ******************************* */

	//$jLp=_moduloController::jsonLpsDisponiveis( $_1_u__modulo_modulo );

	/* *************************** Lista etiquetas relacionadas ao modulo ******************************* */

	// $jEtiqueta=_moduloController::jsonEtiquetasDisponiveis($_1_u__modulo_idmodulo);

	/* *************************** Lista impressoras relacionadas ao modulo ******************************* */

	$jImp = _moduloController::jsonImpressorasDisponiveis($_1_u__modulo_idmodulo);
}
/* *************************** Listar tabelas relacionadas ao formulario *************************** */
function listaTabelasRelacionadas()
{

	global $_1_u__modulo_modulo, $_1_u__modulo_urldestino, $estado, $estado2;

	/*
	 * Lista as tabelas relacionadas
	 */
	$rtabs = _moduloController::listaTabelasVinculadasAoForm($_1_u__modulo_modulo, $_1_u__modulo_urldestino);

	if (count($rtabs) > 0) { ?>
		<label>Permissões DB:</label>
		<br>
		<table class="planilha grade compacto">
			<tr>
				<td>Tipo</td>
				<td>Tabela</td>
				<td>Principal?</td>
				<td>Excluir</td>
			</tr>
			<?
			foreach ($rtabs as $k => $rtab) {
				if (!empty($rtab["idmodulorelac"])) {
					$bolModuloRelac = true;
				}
			}

			foreach ($rtabs as $k => $rtab) {
				//echo $br;
				switch ($rtab["tipoobjeto"]) {
					case "tabela":
						$ico = "table";
						break;
					case "tabelacbpost":
						$ico = "code";
						break;
					default:
						break;
				} ?>
				<tr>
					<td align="center">
						<i class="fa fa-<?= $ico ?> verdeclaro"></i>
					</td>
					<td>
						<a href="?_modulo=_mtotabcol&_acao=u&PK=<?= _DBAPP ?>.<?= $rtab["objeto"] ?>" target="_blank">
							<?= $rtab["objeto"] ?>
						</a>
					</td>
					<td align="center">
						<? if ($rtab["tipoobjeto"] == "tabela") {
							if (!empty($rtab["idmodulorelac"])) {
								$scheck = "checked";
								$sacao = "_ajax_d__modulorelac_idmodulorelac=" . $rtab["idmodulorelac"];
								$disabled = "";
							} else {
								$scheck = "";
								$sacao = "_ajax_i__modulorelac_modulo=" . $_1_u__modulo_modulo . "&_ajax_i__modulorelac_tabde=" . $rtab["objeto"];
								$disabled = $bolModuloRelac == true ? 'disabled="disabled"' : "";
							}
						?>
							<input type="checkbox" <?= $scheck ?> <?= $disabled ?> onclick="CB.post({objetos:'<?= $sacao ?>'})">
						<? } ?>
					</td>
					<td align="center">
						<? if ($rtab["inseridomanualmente"] == "Y") { ?>
							<span onclick="CB.post({objetos:'_ajax_d__formobjetos_idformobjetos=<?= $rtab["idformobjetos"] ?>'})" class="pointer" style="color:red;font-weight: bold;">x</span>
						<? } ?>
					</td>
				</tr>
			<? } ?>
		</table>
	<? }
}

/* *************************** Listar arquivos ajax relacionados ao formulario *************************** */
function listaArqAjax()
{

	global $_1_u__modulo_modulo, $_1_u__modulo_urldestino;

	/*
	 * Lista os arquivos AJAX relacionados
	*/

	$rajax = _moduloController::listaAjaxVinculadosAoForm($_1_u__modulo_modulo, $_1_u__modulo_urldestino);

	if (count($rajax) > 0) {
	?>
		<label>Permissões para arquivos Ajax:</label><br>
		<?
		$br = "";
		while ($ra = mysql_fetch_assoc($rajax)) {
			echo $br;
		?>
			<i class="fa fa-file-code-o verdeclaro"></i>&nbsp;<?= $ra["objeto"] ?>
<?
			$br = "<br>";
		}
	}
}

/* ********** Inicializa tabela _modulofiltros, para facilitar códigos de checkboxes ********** */
if ($_1_u__modulo_modulo && $_1_u__modulo_tab && ($_1_u__modulo_tipo != "MODVINC" || !$configmoduloReal)) {

	_moduloController::insertModuloFiltros($_1_u__modulo_modulo, $_1_u__modulo_tab);
}

/* *************************** Array com a configuração da pesquisa *************************** */

if ($_1_u__modulo_tab) {

	$rfiltros = _moduloController::montaArrayConfPesquisa($moduloReal);
}


/* *********************************** Tabelas dos databases configurados ****************************** */
function jsonTabelasCarbonApp()
{

	return _moduloController::jsonTabelasCarbonApp();
}

/* *********************************** Verificar funcionamento de FTS ****************************** */
function verificaColunasFtsDicionario()
{
	global $_1_u__modulo_tab;

	$resf = _moduloController::verificaColunasFtsDicionario($_1_u__modulo_tab);

	if ($resf == 0) {
		return "<i class='fa fa-warning vermelho fa-2x' title='Nenhuma coluna configurada para Full Text Search'></i>";
	} else {
		return "<i class='fa fa-check-circle azul fa-2x' title='" . $resf . " Colunas FTS configuradas corretamente'></i>";
	}
}

function verificaColunaChaveFts()
{
	global $_1_u__modulo_chavefts;

	if (empty($_1_u__modulo_chavefts)) {
		return "<i class='fa fa-key vermelho fa-2x' title='Nenhuma coluna configurada como Chave para Full Text Search\nConfigure abaixo.'></i>";
	} else {
		return "<i class='fa fa-key azul fa-2x' title='Coluna chave para FTS configurada corretamente'></i>";
	}
}

function verificaColunasInexistentes()
{
	global $_1_u__modulo_tab;

	$resf = _moduloController::contarColunasInexistentesNoDB($_1_u__modulo_tab);

	if ($resf > 0) {
		return "<i class='fa fa-table vermelho fa-2x blink' title='Existem colunas inexistentes em [" . ".$_1_u__modulo_tab." . "]'></i>";
	} else {
		return "";
	}
}


/*
 * Procedimento para reiniciar o full text search para uma tabela especà­fica
 * Este procedimento caso seja ativado leva muito tempo para ser finalizado.
 * Portanto será instanciado somente caso esta página seja chamada com parà¢metros adequados
 */
if ($_GET["_atualizaftstabela"] == "Y") {
	atualizaFtsTabela();
	die;
}

function atualizaFtsTabela()
{
	global $_1_u__modulo_tab;
	//Para resetar o FTS para a tabela, deve-se passar FALSE no primeiro parà¢metro, e informar o nome da tabela no terceiro
	$sfts = "CALL " . _DBCARBON . "._ftsByHostnameAtualizarDb(false,'" . _DBAPP . "','" . $_1_u__modulo_tab . "', '" . rand(9999, 9999999) . "')";
	$resfts = d::b()->query($sfts) or die("Erro ao resetar FTS para a tabela: " . mysqli_error(d::b()));
}

$listaHistoricoFab = [];

if($_1_u__modulo_idmodulo) $listaHistoricoFab = LoteController::buscarHistoricoDeAlteração($_1_u__modulo_idmodulo, '_modulo', 'timeout');

?>
<style>
	.bootstrap-select:not([class*="col-"]):not([class*="form-control"]):not(.input-group-btn) {
		width: 166px;
	}

	#svganexo .dz-details,
	#svganexo.carregado::before {
		display: none !important;
	}

	#svganexo.carregado {
		display: flex;
		flex-direction: row;
		align-items: center;
	}

	#icone-modulo {
		display: flex;
		align-items: center;
	}

	#icone-modulo>* {
		padding: 0px 5px;
	}

	.panel-heading[data-toggle][href], [data-toggle][href]{
		color: #337ab7;
		text-decoration: none;
		cursor: ns-resize;
	}
	.panel-heading[data-toggle][href]:hover, [data-toggle][href]:hover{
		color: #23527c;
		text-decoration: underline;
	}
</style>

<div class="col-md-5">	
	<div class="panel panel-default">
		<div class="panel-heading" href="#body_modulos" data-toggle="collapse">
			<?
			if (!empty($_1_u__modulo_modulo)) {
			?>
				Módulo: <label class='alert-warning'><?= $_1_u__modulo_modulo ?></label>

				<input type="hidden" name="_1_<?= $_acao ?>__modulo_idmodulo" value="<?= $_1_u__modulo_idmodulo ?>">
				<input type="hidden" name="_1_<?= $_acao ?>__modulo_modulo" vnulo valfa value="<?= $_1_u__modulo_modulo ?>">
			<?
			} else {
			?>
				<input type="text" name="_1_<?= $_acao ?>__modulo_modulo" vnulo valfa value="<?= $_1_u__modulo_modulo ?>" maxlength="45">
			<?
			}
			?>
		</div>
		<div class="panel-body" id="body_modulos">
			<table width="100%">
				<tr>
					<td>Rótulo:</td>
					<td>
						<input type="text" name="_1_<?= $_acao ?>__modulo_rotulomenu" vnulo="" value="<?= $_1_u__modulo_rotulomenu ?>" maxlength="45">
					</td>

				</tr>
				<tr>
					<td>Descrição:</td>
					<td>
						<input type="text" name="_1_<?= $_acao ?>__modulo_descricao" value="<?= $_1_u__modulo_descricao ?>">
					</td>

				</tr>
				<tr>
					<td>Ícone:</td>
					<td id="icone-modulo" class="nowrap">
						<input type="text" name="_1_<?= $_acao ?>__modulo_cssicone" value="<?= $_1_u__modulo_cssicone ?>" maxlength="45">
						<i id="seletoricones" class="<?= ($_1_u__modulo_cssicone) ? $_1_u__modulo_cssicone : "fa fa-smile-o" ?> fa-2x fade pointer"></i>
						<span>ou</span>
						<i class="fa fa-cloud-upload dz-clickable pointer azul" id="svganexo" title="Clique para adicionar um SVG"></i>
					</td>
				</tr>
				<tr>
					<td>Ordenação:</td>
					<td>
						<input type="text" class="size5" name="_1_<?= $_acao ?>__modulo_ord" value="<?= $_1_u__modulo_ord ?>">
					</td>
				</tr>
				<tr>
					<td>Time Out (Minutos:Segundos):</td>
					<td>
						<div class="d-flex align-items-center gap-3">
							<input type="text" class="size5" name="_1_<?= $_acao ?>__modulo_timeout" value="<?= $_1_u__modulo_timeout?>" placeholder="00:00" disabled>
							<i class="fa fa-pencil pointer mx-3" onclick="alteravalor('timeout','<?= $_1_u__modulo_timeout ?>','modulohistorico',<?= $_1_u__modulo_idmodulo ?>,'Timeout:',true)"></i>
							<img src="/form/img/icon-hist.svg" class="pointer timeout-hist" alt="" />
						</div>
					</td>
				</tr>
				<tr>
					<td class="col-md-2"><br>
						<hr><br>
					</td>
					<td><br>
						<hr><br>
					</td>
				</tr>
				<tr>
					<td>Disponível p/ App:</td>
					<?
					$checked = $_1_u__modulo_disponivelapp == "Y" ? "checked" : "";
					?>
					<td><input type="checkbox" <?= $checked ?> onclick="toggleDisponivelApp(this)"></td>

				</tr>
				<? if ($_1_u__modulo_disponivelapp == "Y") {
					modulosMobile();
				?>
					<tr>
						<td>Cor Botão App:</td>
						<td><input type="color" name="_1_<?= $_acao ?>__modulo_btncolorapp" value="<?= $_1_u__modulo_btncolorapp ?>"></td>
					</tr>
				<? } ?>
				<tr>
					<td>Status:</td>
					<td>
						<?
						$unidades = _moduloController::buscarUnidadesTabelaModulo($_1_u__modulo_modulo);
						$bloqueiaStatus = '';
						if($unidades){
							if(_moduloController::buscarUnidadesTabelas($unidades['tabde'], $unidades['idunidade']) > 0){
								$bloqueiaStatus = 'disabled';
							}
						}
						?>
						<select name="_1_<?=$_acao?>__modulo_status" class="size7" <?=$bloqueiaStatus?>>                                   
							<? fillselect(_moduloController::$ArrayStatus, $_1_u__modulo_status); ?>
						</select>
						<? if($unidades) { ?>
							<p><small>Não é possivel inativar o módulo, pois existem registros nas unidades do módulo. </small></p>
						<? } ?>

					</td>
				</tr>
				<tr>
					<td style="vertical-align:top">Módulo Tipo:</td>
					<td>
						<select name="_1_<?= $_acao ?>__modulo_modulotipo" class="size7">
							<?
							fillselect(_moduloController::buscarModuloTipo(), $_1_u__modulo_modulotipo);
							?>
						</select>
						<p><small>Usado para "agrupar" os módulos, forçando a usarem o mesmo link. </small></p>
					</td>
				</tr>
				<tr>
					<td style="vertical-align:top">Botão Assinar:</td>
					<td>
						<select name="_1_<?= $_acao ?>__modulo_botaoassinar" class="size7">
							<?
							fillselect(_moduloController::$ArrayYN, $_1_u__modulo_botaoassinar);
							?>
						</select>
						<p><small>Usado para mostrar o botão de assinatura no módulo. </small></p>
					</td>
				</tr>
				<tr>
					<td style="vertical-align: top; margin-top: 4px; padding-top: 8px;">Empresa:</td>
					<td>
						<select name="empresa" onchange="inserirempresa(this);">
							<option value=""></option>
							<?
							fillselect(_moduloController::buscarEmpresasParaVincularAoModulo($_1_u__modulo_idmodulo));
							?>
						</select>
					</td>
				</tr>

				<? if (!empty($_1_u__modulo_modulo)) { ?>
					<tr>
						<td>Unidade:</td>
						<td>
							<select name="unidade" onchange="inserirunidade(this);">
								<option value=""></option>
								<?

								fillselect(_moduloController::buscarUnidadesParaVincularAoModulo($_1_u__modulo_idmodulo, $_1_u__modulo_modulo));
								?>
							</select>
						</td>
						<? ?>
					</tr>
				<? } ?>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2">
						<table style="width: 100%" class="table table-striped planilha">
							<th style="text-align: center" colspan="3">Empresa</th>
							<th style="text-align: center" colspan="3">Unidade</th>
							<?
							$rese = _moduloController::buscarEmpresasVinculadasAoModulo($_1_u__modulo_idmodulo);
							$qtde = count($rese);
							if ($qtde > 0) {
								foreach ($rese as $k => $rowe) {
							?>
									<tr>
										<td>
											<?= $rowe["empresa"] ?>
										</td>
										<td>
											<a title="Empresa" class="fa fa-bars fade pointer hoverazul" href="?_modulo=empresa&_acao=u&idempresa=<?= $rowe["idempresa"] ?>" target="_blank"></a>
										</td>
										<td>
											<span onclick="deleteEmpresa(<?= $rowe['idobjempresa'] ?>)" class="pointer">
												<i class="fa fa-trash vermelho hoverpreto pointer"></i>
											</span>
										</td>
										<?
										$resu = _moduloController::buscarUnidadesVinculadasAoModulo($_1_u__modulo_modulo, $rowe["idempresa"]);
										$qtdu = count($resu);
										if ($qtdu > 0) {
											foreach ($resu as $k1 => $rowu) {
										?>
												<td>
													<?= $rowu["unidade"] ?>
												</td>
												<td>
													<input type="hidden" name="unidade_<?= $rowe["idobjempresa"] ?>" id="unidade_<?= $rowe["idobjempresa"] ?>" value="<?= $rowu["idunidadeobjeto"] ?>">
													<a title="Unidade" class="fa fa-bars fade pointer hoverazul" href="?_modulo=unidade&_acao=u&idunidade=<?= $rowu["idunidade"] ?>" target="_blank"></a>
												</td>
												<td>
													<span onclick="CB.post({objetos:'_ajax_d_unidadeobjeto_idunidadeobjeto=<?= $rowu['idunidadeobjeto'] ?>'})" class="pointer">
														<i class="fa fa-trash vermelho hoverpreto pointer"></i>
													</span>
												</td>
										<? }
										} else {
											echo "<td colspan='3'>-</td>";
										}
										?>
									</tr>
							<? }
							}
							?>
						</table>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading" href="#body_share" data-toggle="collapse">
			Share
		</div>
		<div class="panel-body" id="body_share" >
			<?
			$res = _moduloController::buscarRegrasShareModuloFiltrosPesquisa($_1_u__modulo_modulo);
			if (empty($res)) { ?>
				<div class="col-md-12">Nenhuma regra share aplicada!</div>
				<div class="col-md-12"><i class="fa fa-plus-circle verde pointer fa-lg" onclick="criaShare()"></i>&nbsp;&nbsp;Nova regra</div>
			<? } else { ?>
				<table class="table table-striped planilha">
					<tr>
						<th class="center">
							Empresa de acesso
						</th>
						<th class="center">
							Empresas visíveis
						</th>
						<th colspan="2" class="center">
							Unidades visíveis
						</th>
					</tr>
					<? foreach ($res as $k => $row) {
						$jclauswhere = json_decode($row['jclauswhere'], true);
					?>
						<tr>
							<td class="center">
								<select group="<?= $k ?>" campo="ovalue" idshare="<?= $row['idshare'] ?>" id="jclauswhere_ovalue">
									<? fillselect(_moduloController::buscarEmpresasVinculadasAoModulo($_1_u__modulo_idmodulo, true), $row['ovalue']) ?>
								</select>
							</td>
							<td class="center">
								<select campo="idempresa" id="<?= $k ?>_jclauswhere_idempresa" group="<?= $k ?>" idshare="<?= $row['idshare'] ?>" class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
									<?
									$resjidempresa = _moduloController::buscarEmpresasVinculadasAoModulo($_1_u__modulo_idmodulo);
									$selected = '';
									foreach ($resjidempresa as $k1 => $rowempresa) {
										$selected = (in_array($rowempresa['idempresa'], explode(",", $jclauswhere['idempresa'])) != false) ? 'selected' : '';
										echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowempresa['empresa']) . '" value="' . $rowempresa['idempresa'] . '" >' . $rowempresa['empresa'] . '</option>';
									}
									?>
								</select>
							</td>
							<td class="center">
								<select campo="idunidade" id="<?= $k ?>_jclauswhere_idunidade" group="<?= $k ?>" idshare="<?= $row['idshare'] ?>" class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
									<?
									$resjidunidade = _moduloController::buscarUnidadesDisponiveisParaShare($_1_u__modulo_idmodulo);
									$selected = '';
									foreach ($resjidunidade as $k1 => $rowunidade) {
										$selected = (in_array($rowunidade['idunidade'], explode(",", $jclauswhere['idunidade'])) != false) ? 'selected' : '';
										echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowunidade['unidade']) . '" value="' . $rowunidade['idunidade'] . '" >' . $rowunidade['unidade'] . '</option>';
									}
									?>
								</select>
								<input name="_share<?= $k ?>_u_share_idshare" type="hidden" value="<?= $row['idshare'] ?>">
								<input name="_share<?= $k ?>_u_share_ovalue" type="hidden" value="<?= $row['ovalue'] ?>">
								<textarea name="_share<?= $k ?>_u_share_jclauswhere" class="hidden"><?= $row['jclauswhere'] ?></textarea>
							</td>
							<td>
								<i onclick="CB.post({objetos:'_ajax_d_share_idshare=<?= $row['idshare'] ?>'})" class="fa fa-trash vermelho hoverpreto pointer"></i>
							</td>
						</tr>
					<?
					} ?>
					<tr>
						<td colspan="4"><i class="fa fa-plus-circle verde pointer fa-lg" onclick="criaShare()"></i>&nbsp;&nbsp;Nova regra</td>
					</tr>
				</table>
			<? } ?>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading" href="#body_comportamentoaparencia" data-toggle="collapse">Comportamento/Aparência</div>
		<div class="panel-body" id="body_comportamentoaparencia">
			<div class="<?= $colvinc ?>">
				Tipo:
				<select name="_1_<?= $_acao ?>__modulo_tipo" vnulo>
					<?
					fillselect(_moduloController::$ArrayTipos, $_1_u__modulo_tipo);
					?>
				</select>
			</div>
			<?
			if ($_1_u__modulo_tipo == "MODVINC" and !empty($_1_u__modulo_modulo)) {
			?>
				<div class="<?= $colvinc ?>">
					<i class="fa fa-link laranja"></i>&nbsp;Módulo Vinculado:
					<select name="_1_<?= $_acao ?>__modulo_modvinculado" vnulo>
						<?
						fillselect(_moduloController::adicionarModVinculados(), $_1_u__modulo_modvinculado);
						?>
					</select>
				</div>
			<?
			}
			?>
			<div class="col-md-6">
				<?
				if (!in_array($_1_u__modulo_tipo, ['DROP']) and !empty($_1_u__modulo_modulo)) {
					if ($_1_u__modulo_tipo == "BTINV" or $_1_u__modulo_tipo == "POPUP") {
						$sqlssup = _moduloController::buscarModpar("tipo in ('LINK','DROP','SNIPPET','LINKHOME','BTPR') order by modulo");
					} else {
						$sqlssup = _moduloController::buscarModpar("tipo in ('DROP','BTPR') and modulo !='" . $_1_u__modulo_modulo . "'");
					}
				?>
					Menu Superior:
					<select name="_1_<?= $_acao ?>__modulo_modulopar" class="selectpicker form-control" data-live-search="true">
						<option selected="selected"></option>
						<?
						fillselect($sqlssup, $_1_u__modulo_modulopar);
						?>
					</select>
				<?
				}
				?>
			</div>

			<?
			if ($_1_u__modulo_tipo != "DROP" and !empty($_1_u__modulo_modulo)) {
			?>
				<div class="col-md-6">
					On Ready?
					<select name="_1_<?= $_acao ?>__modulo_ready" <?= $estado ?>>
						<option value=""></option>
						<?
						fillselect(_moduloController::$ArrayOnReady, $_1_u__modulo_ready);
						?>
					</select>
				</div>
				<?
				if ($_1_u__modulo_ready == "FILTROS" || $_1_u__modulo_tipo == "MODVINC") {
				?>
					<div class="col-md-6">
						Rótulo Filtros:
						<input type="text" name="_1_<?= $_acao ?>__modulo_titulofiltros" value="<?= $_1_u__modulo_titulofiltros ?>" maxlength="45" size="40" placeholder="[Titulo]">
					</div>
				<?
				}
			}

			if ($_1_u__modulo_btsalvar == "N") {
				?>
				<div class="col-md-6">
					Header?
					<select name="_1_<?= $_acao ?>__modulo_cbheader" vnulo <?= $estado ?>>
						<? fillselect(_moduloController::$ArrayYN, $_1_u__modulo_cbheader); ?>
					</select>
				</div>
			<?
			}
			?>
			<div class="col-md-6">
				Botão Salvar?
				<select name="_1_<?= $_acao ?>__modulo_btsalvar" vnulo <?= $estado ?>>
					<?
					fillselect(_moduloController::$ArrayYN, $_1_u__modulo_btsalvar);
					?>
				</select>
			</div>
			<?

			if ($_1_u__modulo_ready == "FILTROS" and $_1_u__modulo_tipo != "DROP"  and !empty($_1_u__modulo_modulo)) {
			?>
				<div class="col-md-6">
					Botão Novo?
					<select name="_1_<?= $_acao ?>__modulo_btnovo" vnulo <?= $estado ?>>
						<?
						fillselect(_moduloController::$ArrayYN, $_1_u__modulo_btnovo);
						?>
					</select>
				</div>
				<div class="col-md-6">
					Botão Imprimir?
					<select name="_1_<?= $_acao ?>__modulo_btimprimir" vnulo <?= $estado ?>>
						<?
						fillselect(_moduloController::$ArrayYN, $_1_u__modulo_btimprimir);
						?>
					</select>
				</div>
				<div class="col-md-6">
					Confirmar Impressão?
					<select name="_1_<?= $_acao ?>__modulo_btimprimirconf" vnulo <?= $estado ?>>
						<?
						fillselect(_moduloController::$ArrayYN, $_1_u__modulo_btimprimirconf);
						?>
					</select>
				</div>
				<div class="col-md-6">
					Pesquisar sem parâmetros?
					<select name="_1_<?= $_acao ?>__modulo_psqfull" vnulo <?= $estado ?>>
						<?
						fillselect(_moduloController::$ArrayYN, $_1_u__modulo_psqfull);
						?>
					</select>
				</div>
			<?
			}
			if (($_1_u__modulo_ready == "FILTROS" and $_1_u__modulo_tipo != "DROP"  and !empty($_1_u__modulo_modulo)) || ($_1_u__modulo_ready == 'URL')) {
			?>
				<div class="col-md-6">
					Abrir em Nova Janela?
					<select name="_1_<?= $_acao ?>__modulo_novajanela" vnulo <?= $estado ?>>
						<option></option>
						<?
						fillselect(_moduloController::$ArrayNovaJanela, $_1_u__modulo_novajanela);
						?>
					</select>
				</div>
				<div class="col-md-6">
					Salvamentos (ajax) paralelos?
					<select name="_1_<?= $_acao ?>__modulo_ajaxparalelo" vnulo <?= $estado ?>>
						<?
						fillselect(_moduloController::$ArrayYN, $_1_u__modulo_ajaxparalelo);
						?>
					</select>
				</div>

			<?
			}
			?><div class="col-md-6">
				Divisor?
				<select name="_1_<?= $_acao ?>__modulo_divisor" vnulo>
					<?
					fillselect(_moduloController::$ArrayYN, $_1_u__modulo_divisor);
					?>
				</select>
			</div>
			<div class="col-md-6">
				Módulo Principal?
				<select name="_1_<?= $_acao ?>__modulo_moduloinicial" vnulo>
					<?
					fillselect(_moduloController::$ArrayYN, $_1_u__modulo_moduloinicial);
					?>
				</select>
			</div>

		</div><!-- panel body -->
	</div><!-- panel default -->

	<div class="panel panel-default">
		<div class="panel-heading" href="#body_formulariorelacionado" data-toggle="collapse">Formulário Relacionado</div>
		<div class="panel-body" id="body_formulariorelacionado">
			Url destino:
			<select name="_1_<?= $_acao ?>__modulo_urldestino" <?= $estado ?> class="selectpicker form-control" data-live-search="true">
				<option></option>
				<?
				fillselect($arrformularios, $_1_u__modulo_urldestino);
				?>
			</select>
			<hr>
			Url print
			<select name="_1_<?= $_acao ?>__modulo_urlprint" <?= $estado ?>>
				<option></option>
				<?
				fillselect($arrformularios, $_1_u__modulo_urlprint);
				?>
			</select>
			<hr>
			<?
			listaTabelasRelacionadas();
			?>
			<hr>
			<?
			listaArqAjax();
			?>
			<hr>
			<i class="fa fa-plus-circle verde pointer fa-lg" onclick="$('novoformobj').removeClass('hidden').find(':input, select').removeAttr('disabled').find(':input:text, select').removeAttr('disabled')"></i>
			<label></label>
			<novoformobj class="hidden">
				<input type="hidden" name="_10_i__formobjetos_modulo" value="<?= $_1_u__modulo_modulo ?>" disabled>
				<input type="hidden" name="_10_i__formobjetos_form" value="<?= $_1_u__modulo_urldestino ?>" disabled>
				<input type="hidden" name="_10_i__formobjetos_inseridomanualmente" value="Y" disabled>
				<select name="_10_i__formobjetos_tipoobjeto" style="width: auto;" disabled>
					<? fillselect(_moduloController::$ArrayNovoFormObj) ?>
				</select>
				<input type="text" name="_10_i__formobjetos_objeto" style="width: auto;" disabled>
			</novoformobj>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading" href="#body_informacoesresusuario" data-toggle="collapse">Informações para restauração do usuário</div>
		<div class="panel-body" id="body_informacoesresusuario">
			<table>
				<tr>
					<td>Tabela:</td>
					<td><input type="text" name="_1_<?= $_acao ?>__modulo_tabrest" value="<?= $_1_u__modulo_tabrest ?>"></td>
				</tr>
				<tr>
					<td>Status:</td>
					<td>
						<textarea name="_1_<?= $_acao ?>__modulo_statusrest" rows="1"><?= $_1_u__modulo_statusrest ?></textarea>
					</td>
				</tr>
				<? if (!empty($_1_u__modulo_tabrest)) { ?>
					<tr>
						<td colspan="2">
							Motivos para restauração:
						</td>
					</tr>
					<?
					$sqlm = 'SELECT * from fluxostatushistmotivo where modulo="' . $_1_u__modulo_modulo . '"';
					$resm = d::b()->query($sqlm) or die("A Consulta de motivos falhou: " . mysqli_error(d::b()) . "<p>SQL: $sqlm");
					$resm = _moduloController::buscarMotivosDeRestauracao($_1_u__modulo_modulo);
					foreach ($resm as $k2 => $rowsm) { ?>
						<tr>
							<td>
								<?= $rowsm['motivo'] ?>
							</td>
							<td>
								<i onclick="deletamotivo(<?= $rowsm['idfluxostatushistmotivo'] ?>)" id="esconde" class="fa fa-trash cinza hoververmelho pointer fa-lg"></i>
							</td>
						</tr>
					<? } ?>
					<tr>
						<td>
							<i onclick="motivo(this)" id="esconde" class="fa fa-plus-circle verde pointer fa-lg"></i>
						</td>
						<td>
							<textarea id="_motivo_" style="display: none;"></textarea>
						</td>
					</tr>
				<? } ?>
			</table>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading" href="#body_supTI" data-toggle="collapse">Últimos Suporte TI</div>
		<div class="panel-body" id="body_supTI">
			<table class="table table-striped planilha">
				<tr>
					<th>ID</th>
					<th>Evento</th>
					<th>Tipo</th>
					<th>Prazo</th>
					<th>Status</th>
				</tr>
				<?
				$resevento = _moduloController::buscarEventosPorTipoEClassificacao($_1_u__modulo_modulo, 21); // 21 idtipoevento suporte TI 
				foreach ($resevento as $k => $rowevento) { ?>
					<tr>
						<td>
							<a class="background-color: #FFEFD1; pointer hoverazul" title="Evento" onclick="janelamodal('?_modulo=evento&_acao=u&idevento=<?= $rowevento["idevento"] ?>')"><?= $rowevento["idevento"] ?></a>
						</td>
						<td><?= $rowevento["evento"] ?></td>
						<td><?= $rowevento["eventotipo"] ?></td>
						<td><?= dma($rowevento["prazo"]) ?></td>
						<td><?= $rowevento["status"] ?></td>
					</tr>
				<? } ?>
			</table>
		</div>
	</div>
</div>
<div class="col-md-4">
	<div class="panel panel-default">
		<div class="panel-heading" href="#body_telapesquisa" data-toggle="collapse">Tela de Pesquisa</div>
		<div class="panel-body" id="body_telapesquisa">
			<table>
				<tr>
					<td>Tabela:</td>
					<td>
						<div class="input-group input-group-sm">
							<input type="text" name="_1_<?= $_acao ?>__modulo_tab" value="<?= $_1_u__modulo_tab ?>" <?= $estado ?> <?= $estado2 ?>>
							<span class="input-group-addon" title="Editar Tabela"><i class="fa fa-pencil" onclick="janelamodal('?_modulo=_mtotabcol&_acao=u&PK=<?= _DBAPP . "." . $_1_u__modulo_tab ?>')" <?= $estado ?>></i></span>

						</div>
						<?
						if (count($rfiltros) == 0 and $_1_u__modulo_tab) echo ("<label class='alert-danger'>Erro: Tabela " . $_1_u__modulo_tab . " não existente no DB</label>");
						?>
					</td>
					<td>
						<?= verificaColunaChaveFts() ?>
						<?= verificaColunasFtsDicionario() ?>
						<?= verificaColunasInexistentes() ?>
					</td>
				</tr>
				<tr>
					<td>Ordenável:</td>
					<td>
						<?
						$checked = $_1_u__modulo_ordenavel == "Y" ? "checked" : "";
						?>
						<input type="checkbox" <?= $checked ?> onclick="toggleOrdenavel(this)">
					</td>
					<td></td>
				</tr>
			</table>
			<hr />
			<table class="planilha grade compacto" <?= $estado ?>>
				<tr>
					<th>Colunas:</th>
					<th><i class="fa fa-bolt cinza"></i></th>
					<th><i class="fa fa-key vermelho" title="Coluna Chave para FTS"></th>
					<th><i class="fa fa-filter" title="Habilitar Filtro Rápido. Obrigatório existir um prompt informado no Dicionário de dados."></th>
					<th><i class="fa fa-calendar" title="Habilitar Filtro por Data"></th>
					<th><i class="fa fa-eye" title="Coluna visível nos resultados"></th>
					<th><i class="fa fa-mobile " title="Coluna visível nos resultados no app"></th>
					<th>Par Get</th>
					<th><i class="fa fa-caret-right" title="Intervalo de valores (between)"></i><i class="fa fa-caret-left" title="Intervalo de valores (between)"></i></th>
					<th class="center"><i class="fa fa-align-left" title="Alinhamento"></i></th>
					<th class="center"><i class="fa fa-gears" title="Formatação"></i></th>
					<th><i class="fa fa-sort-numeric-asc " title="Ordenação"></i></th>
				</tr>
				<?
				$if = 100;
				foreach ($rfiltros as $k => $rwf) {
					$if++;
					$ftschecked = ($_1_u__modulo_chavefts == $rwf["col"]) ? "checked='checked'" : "";

					$icoperf = "";
					switch (true) {
						case ($rwf["datatype"] == "date" or $rwf["datatype"] == "datetime" or $rwf["datatype"] == "timestamp") and $rwf["filtrodata"] !== "Y" and ($rwf["perfindice"] == "" or empty($rwf["perfindice"])):
							$icoperf = "<i class='fa fa-ban laranja' title='Coluna sem índice!'></i>";
							break;
						case $rwf["filtrodata"] == "Y" and ($rwf["perfindice"] == "" or empty($rwf["perfindice"])):
							$icoperf = "<i class='fa fa-ban vermelhoescuro blink' title='Coluna de data marcada para utilização, mas ela não possui índice, " . $rwf["filtrodata"] . ", " . $rwf["perfindice"] . ", " . $rwf["perfindice"] . "!'></i>";
							break;
						case ($rwf["datatype"] == "date" or $rwf["datatype"] == "datetime" or $rwf["datatype"] == "timestamp") and (int)$rwf["perfindice"] >= 5 and $rwf["perfindice"] !== "":
							$icoperf = "<i class='fa fa-bolt verde' title='Índice com boa performance'></i>";
							break;
						case ($rwf["datatype"] == "date" or $rwf["datatype"] == "datetime" or $rwf["datatype"] == "timestamp") and $rwf["perfindice"] !== "" and (int)$rwf["perfindice"] < 5:
							$icoperf = "<i class='fa fa-bolt vermelhoescuro blink' title='Índice Ruim'></i>";
							break;
						default:
							$icoperf = "";
							break;
					}


				?>
					<tr>
						<td title="<?= $rwf["col"] ?>"><?= $rwf["rotcurto"] ?></td>
						<td><?= $icoperf ?></td>
						<td>
							<input type="radio" name="chavefts" <?= $ftschecked ?> onclick="togglechavefts('<?= $rwf["col"] ?>',this)" <?= $estado ?>>
						</td>
						<td>
							<?
							if (!empty($rwf["prompt"])) {
							?>
								<input type="checkbox" onclick="toggle('<?=$rwf['idmodulofiltros'] ?>','promptativo', this)" <?= ($rwf["promptativo"] == "Y") ? "checked" : ""; ?> <?= $estado ?>>
							<?
							}
							?>
						</td>
						<td>
							<?
							if ($rwf["datatype"] == "date" or $rwf["datatype"] == "datetime" or $rwf["datatype"] == "timestamp") {
							?>
								<input type="checkbox" onclick="toggle('<?=$rwf['idmodulofiltros'] ?>','filtrodata',this)" col="<?=$rwf["col"] ?>" <?= ($rwf["filtrodata"] == "Y") ? "checked" : ""; ?> <?= $estado ?>>
							<?
							}
							?>
						</td>
						<td>
							<input type="checkbox" onclick="toggle('<?=$rwf['idmodulofiltros'] ?>','visres',this)" col="<?=$rwf["col"] ?>" <?= ($rwf["visres"] == "Y") ? "checked" : ""; ?> <?= $estado ?>>
						</td>
						<td>
							<input type="checkbox" onclick="toggle('<?=$rwf['idmodulofiltros'] ?>','visresapp',this)" col="<?=$rwf["col"] ?>" <?= ($rwf["visresapp"] == "Y") ? "checked" : ""; ?> <?= $estado ?>>
						</td>
						<td>
							<input type="checkbox" onclick="toggle('<?=$rwf['idmodulofiltros'] ?>','parget',this)" col="<?=$rwf["col"] ?>" <?= ($rwf["parget"] == "Y") ? "checked" : ""; ?> <?= $estado ?>>
						</td>
						<td>
							<input type="checkbox" onclick="toggle('<?=$rwf['idmodulofiltros'] ?>','entre',this)" col="<?=$rwf["col"] ?>" <?= ($rwf["entre"] == "Y") ? "checked" : ""; ?> <?= $estado ?>>
						</td>
						<td>
							<select onchange="toggleValor(<?= $rwf['idmodulofiltros'] ?>,'align',this)" style="width:38px;">
								<? fillselect(_moduloController::$ArrayLCR, $rwf["align"]); ?>
							</select>
						</td>
						<td>
							<select onchange="toggleValor(<?= $rwf['idmodulofiltros'] ?>,'masc',this)" style="width:38px;">
								<option></option>
								<? fillselect(_moduloController::$ArrayMascara, $rwf["masc"]); ?>
							</select>
						</td>
						<td>
							<input type="text" onchange="toggleValor(<?= $rwf['idmodulofiltros'] ?>,'ord',this)" value="<?= $rwf["ord"]; ?>" style="font-size:10px;width: 18px;">
						</td>
					</tr>
				<?
				}
				?>
			</table>
		</div>
	</div><!-- panel-default -->
	<?
	$sqlv = "SELECT modulo,
							modulopar,
							rotulomenu,
							status
                    FROM " . _DBCARBON . "._modulo 
                    WHERE modvinculado = '" . $_1_u__modulo_modulo . "'
					ORDER BY status, modulopar, modulo";
	$rrev = d::b()->query($sqlv) or die("Erro ao recuperar modulos vinculados: " . mysqli_error(d::b()));
	$rrev = _moduloController::buscarModVinculados($_1_u__modulo_modulo);
	$qtdrv = count($rrev);
	if ($qtdrv > 0) {
	?>
		<div class="panel panel-default">
			<div class="panel-heading" href="#body_modulosvinculados" data-toggle="collapse">Modulos Vinculados</div>
			<div class="panel-body" id="body_modulosvinculados">
				<table class="table table-striped planilha">
					<? foreach ($rrev as $k => $rowev) { ?>
						<tr>
							<td>
								<a href="?_modulo=_modulo&_acao=u&modulo=<?= $rowev["modulo"] ?>" target="_blank"><?= $rowev["modulo"] ?> </a>
							</td>
							<td>
								<a href="?_modulo=_modulo&_acao=u&modulo=<?= $rowev["modulo"] ?>" target="_blank"><?= $rowev["rotulomenu"] ?> [<?= $rowev["modulopar"] ?>] </a>
							</td>
							<td>
								<a href="?_modulo=_modulo&_acao=u&modulo=<?= $rowev["modulo"] ?>" target="_blank"><?= $rowev["status"] ?> </a>
							</td>
						</tr>
					<? } ?>
				</table>
			</div>
		</div>
	<?
	}
	?>
</div>
<? if ($_acao !== "i") { ?>
	<div class="col-md-3">
		<div class="panel panel-default">
			<div class="panel-heading" href="#body_relatorios" data-toggle="collapse">Relatórios</div>
			<div class="panel-body" id="body_relatorios">
				<?

				$rrep = _moduloController::buscarRelatoriosVinculados($_1_u__modulo_modulo);

				?>
				<table id="tbrelatorios" class="table table-striped planilha">
					<?
					foreach ($rrep as $i => $arrrep) { ?>
						<tr class="dragRep" idmodulorep="<?= $arrrep["idmodulorep"] ?>">
							<td title="Ordenar Relatórios">
								<i class="fa fa-arrows cinzaclaro hover move"></i>
							</td>
							<td>
								<?= $arrrep["rep"] ?>
							</td>
							<td>
								<a class="fa fa-pencil cinzaclaro hoverazul pointer" href="?_modulo=_rep&_acao=u&idrep=<?= $arrrep["idrep"] ?>" target="_blank"></a>
							</td>
							<td>
								<i class="fa fa-times vermelho fade" title="Excluir" onclick="CB.post({objetos:'_del_d__modulorep_idmodulorep=<?= $arrrep["idmodulorep"] ?>',parcial:true})"></i>
							</td>
						</tr>
					<?
					}
					?>

				</table>
				<input id="associarRelatorio" type="text" title="Associar Relatório" cbvalue="" value="" autocomplete="off">
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading" href="#body_dash" data-toggle="collapse">Dashboards</div>
			<div class="panel-body" id="body_dash">
				<? $rrep = _moduloController::buscarDashboardsDoModulo($_1_u__modulo_modulo); ?>
				<table id="tbrelatorios" class="table table-striped planilha">
					<tr>
						<td>E</td
							<td>Rótulo</td
							<td>Sub Rótulo</td>
						<td>Tipo</td>
						<td>Cron</td>
						<td>MFP</td>
						<td></td>
					</tr>
					<? foreach ($rrep as $i => $arrrep) { ?>
						<tr>
							<td>
								<?= !empty($arrrep["cardtitlemodal"]) ? $arrrep["cardtitlemodal"] . " => " . $arrrep["sigla"] : "" . $arrrep["sigla"] ?>
							</td>
							<td>
								<?= !empty($arrrep["cardtitlemodal"]) ? $arrrep["cardtitlemodal"] . " => " . $arrrep["cardtitle"] : "" . $arrrep["cardtitle"] ?>
							</td>
							<td>
								<?= !empty($arrrep["cardtitlemodal"]) ? $arrrep["cardtitlemodal"] . " => " . $arrrep["cardtitlesub"] : "" . $arrrep["cardtitlesub"] ?>
							</td>
							<td>
								<?= !empty($arrrep["cardtitlemodal"]) ? $arrrep["cardtitlemodal"] . " => " . $arrrep["tipoobjeto"] : "" . $arrrep["tipoobjeto"] ?>
							</td>
							<td>
								<?= !empty($arrrep["cardtitlemodal"]) ? $arrrep["cardtitlemodal"] . " => " . $arrrep["cron"] : "" . $arrrep["cron"] ?>
							</td>
							<td>
								<?= !empty($arrrep["cardtitlemodal"]) ? $arrrep["cardtitlemodal"] . " => " . $arrrep["modulofiltros"] : "" . $arrrep["modulofiltros"] ?>
							</td>
							<td>
								<a class="fa fa-pencil cinzaclaro hoverazul pointer" href="?_modulo=dashcard&_acao=u&iddashcard=<?= $arrrep["iddashcard"] ?>" target="_blank"></a>
							</td>
						</tr>
					<? } ?>
				</table>
			</div>
		</div>
		<? if ($_1_u__modulo_idmodulo) { ?>
			<div class="panel panel-default">
				<div class="panel-heading" href="#body_etiqueta" data-toggle="collapse">
					Etiquetas
				</div>
				<div class="panel-body" id="body_etiqueta">
					<div class="col-md-12">
						<div class="row">
							Vincular Etiqueta:
							<input type="text" id="busca_etiquetas">
						</div>
						<?
						$reszpl = _moduloController::buscarEtiquetasVinculadasAoModulo($_1_u__modulo_idmodulo);
						if (count($reszpl) > 0) { ?>
							<hr>
							<div class="row">
								<table style="width: 100%;">
									<tr>
										<td>Nome Etiqueta</td>
										<td>Grupo</td>
										<td></td>
										<td></td>
									</tr>
									<?
									$k1 = 1000;
									foreach ($reszpl as $k => $rowe) { ?>
										<tr>
											<td>
												<?= $rowe['rotuloetiqueta'] ?>
											</td>
											<td style="width:20%">
												<input type="hidden" name="_<?= $k1 ?>_u_etiquetaobjeto_idetiquetaobjeto" value="<?= $rowe['idetiquetaobjeto'] ?>">
												<input type="number" name="_<?= $k1 ?>_u_etiquetaobjeto_grupo" value="<?= $rowe['grupo'] ?>" min="1">
											</td>
											<td style="text-align:center;">
												<a href="?_modulo=etiquetazpl&_acao=u&idetiqueta=<?= $rowe['idetiqueta'] ?>" target="_blank" class="fa fa-pencil cinzaclaro hoverazul pointer"></a>
											</td>
											<td style="text-align:center;">
												<i onclick="desvinculaetiqueta(<?= $rowe['idetiquetaobjeto'] ?>)" class="fa fa-times vermelho fade"></i>
											</td>
										</tr>
									<?
										$k1++;
									} ?>
								</table>
							</div>
						<? } ?>
					</div>

				</div>
			</div>
		<? } ?>
		<? if ($_1_u__modulo_idmodulo) { ?>
			<div class="panel panel-default">
				<div class="panel-heading" href="#body_imp" data-toggle="collapse">
					Impressoras
				</div>
				<div class="panel-body" id="body_imp">
					<div class="col-md-12">
						<div class="row">
							Vincular Impressora:
							<input type="text" id="busca_impressora">
						</div>
						<?
						$reszpl = _moduloController::buscarImpressorasVinculadasAoModulo($_1_u__modulo_idmodulo);
						if (count($reszpl) > 0) { ?>
							<hr>
							<div class="row">
								<table style="width: 100%;">
									<tr>
										<td>Impressoras</td>
										<td></td>
										<td></td>
									</tr>
									<?
									$k1 = 1100;
									foreach ($reszpl as $k => $rowe) { ?>
										<tr>
											<td>
												<?= $rowe['nome'] ?>
											</td>
											<td style="width:20%">
											</td>
											<td style="text-align:center;">
												<i onclick="desvinculaimp(<?= $rowe['idobjetovinculo'] ?>)" class="fa fa-times vermelho fade"></i>
											</td>
										</tr>
									<?
										$k++;
									} ?>
								</table>
							</div>
						<? } ?>
					</div>

				</div>
			</div>
		<? } ?>
		<?
		$rs = _moduloController::buscarLpsVinculadasAoModulo($_1_u__modulo_modulo, $_SESSION['SESSAO']['IDPESSOA']); ?>
		<script>
			$('.lps-empresa').on('click', (el, index) => {
				console.log(el, index);
				var idempresa = $(el.target).data('idempresa');
				$(".lps-empresa-modulo[data-idempresa=" + idempresa + "]").prop('checked', $(el.target).prop('checked'));
			});
			$('.lps-empresa-modulo, .lps-empresa').on('change', () => {
				var lps = $('.lps-empresa-modulo:checked');
				if (lps.length > 0) {
					$('#remove-todas-lps')[0].classList.remove('hidden');
					$('#write-todas-lps')[0].classList.remove('hidden');
					$('#read-todas-lps')[0].classList.remove('hidden');

				} else {
					$('#remove-todas-lps')[0].classList.remove('hidden');
					$('#write-todas-lps')[0].classList.remove('hidden');
					$('#read-todas-lps')[0].classList.remove('hidden');
				}
			});
			
			removerVariasLps = () => {

				var lps = $('.lps-empresa-modulo:checked');
				var ids = [];

				lps.each((i, el) => {
					var idlpmodulo = $(el).data('idlpmodulo');
					ids.push('_del' + idlpmodulo + '_d__lpmodulo_idlpmodulo=' + idlpmodulo);
				});

				console.log(ids.join('&'));

				CB.post({
					objetos: ids.join('&')
				});
			}

			permissaoVariasLps = (modulo, tipo) => {
				
				var lps = $('.lps-empresa-modulo:checked'); 
				var ids = [];
				var linha = 22;

				lps.each((i, el) => {
					var idlpmodulo = $(el).data('idlpmodulo');  
					var idlp = $(el).data('idlp');  
					var idempresa = $(el).data('idempresa');
					linha ++;
					

					ids.push(
						"_"+linha+"_u__lpmodulo_idlp=" + idlp, 
						"_"+linha+"_u__lpmodulo_modulo=" + modulo, 
						"_"+linha+"_u__lpmodulo_permissao=" + tipo, 
						"_"+linha+"_u__lpmodulo_idlpmodulo=" + idlpmodulo
					);
				});

				console.log(ids.join('&')); 

				CB.post({
					objetos: ids.join('&'), parcial: true 
				});
			};


		</script>
		<div class="panel panel-default">
			<div class="panel-heading d-flex flex-between">
				<span href="#lpsdomodulo" data-toggle="collapse">LP's Relacionadas ao Módulo</span>
				<div class="block-inline d-flex">
					<span style="margin-left: 10px;"><i id="remove-todas-lps" class="fa fa-trash vermelho hoverpreto pointer hidden" onclick="removerVariasLps()"></i></span>
					<span style="margin-left: 10px;"><i id="read-todas-lps" class="fa fa-lock azul  hoverpreto pointer hidden" onclick="permissaoVariasLps('<?= $_1_u__modulo_modulo ?>', 'r')"></i></span>
					<span style="margin-left: 10px;"><i id="write-todas-lps" class="fa fa-pencil vermelho  hoverpreto pointer hidden" onclick="permissaoVariasLps('<?= $_1_u__modulo_modulo ?>', 'w')"></i></span>
				</div>
			</div> 
			<div class="panel-body collpase" id="lpsdomodulo">
				<table class="table table-striped planilha">
					<?
					$empresa = "";
					foreach ($rs as $k => $rw) {
						if ($rw['permissao'] == 'w') {
							$permissao = 'r';
							$icon = 'fa fa-pencil vermelho '; 
						} else {
							$permissao = 'w';
							$icon = 'fa fa-lock azul ';
						}
						if ($empresa != $rw['empresa']) {
							$empresa = $rw['empresa']; ?>
							<tr style="background-color: #cccccc;">
								<td colspan="3" style="font-weight: bold; text-align:center;text-indent: 15px;"><?= $rw['empresa'] ?></td>
								<td><input class="lps-empresa" type="checkbox" data-idlp="<?= $rw['idlp'] ?>" data-idempresa="<?= $rw['idempresa'] ?>"></td>
							</tr>
						<? } ?>
						<tr>
							<td class="hoverazul">
								<a target="_blank" href="?_modulo=_lp&_acao=u&idlp=<?= $rw['idlp'] ?>"><?= $rw['descricao'] ?></a>
							</td>
							<td>
								<? if (array_key_exists("modulomaster", getModsUsr("MODULOS"))) { ?>
									<i class="<?= $icon ?> hoverpreto pointer" onclick="permissao(<?= $rw['idlp'] ?>,'<?= $rw['modulo'] ?>','<?= $permissao ?>',<?= $rw['idempresa'] ?>,<?= $_SESSION['SESSAO']['IDEMPRESA'] ?>,<?= $rw['idlpmodulo'] ?>)"></i>
								<? } ?>
							</td>
							<td>
								<? if (array_key_exists("modulomaster", getModsUsr("MODULOS"))) { ?>
									<i class="fa fa-trash vermelho hoverpreto pointer" onclick="romoverlp(<?= $rw['idlp'] ?>,'<?= $rw['modulo'] ?>',<?= $rw['idlpmodulo'] ?>)"></i>
								<? } ?>
							</td>

							<td class="hoverazul">
								<input class="lps-empresa-modulo" type="checkbox" data-idlpmodulo="<?= $rw['idlpmodulo']; ?>" data-idlp="<?= $rw['idlp'] ?>" data-idempresa="<?= $rw['idempresa'] ?>" data-modulo="<?= $rw['modulo'] ?>" data-idlpmodulo="<?= $rw['idlpmodulo'] ?>">
							</td>
						</tr>
					<? } ?>
				</table>
				<table style="width: 100%;">
					<tr>
						<td>
							<? if (array_key_exists("modulomaster", getModsUsr("MODULOS"))) { ?>
								<input id='selectlps' cbvalue="<?= $_1_u__modulo_idmodulo ?>" style="display:none">
								<i class="fa fa-plus-circle verde pointer fa-lg" id="mais"></i>
							<? } ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?

		if (!empty($_1_u__modulo_tab)) {

			$re = _moduloController::consultaEventoDBCarbon();

			if ($re["Status"] == "ENABLED" and $re["Type"] == "RECURRING") {
				$ftsico = "fa-check-circle-o verde";
				$ftstitle = "evFTS Habilitado e recorrente no seguinte intervalo:\n" . $re["Interval value"] . " " . $re["Interval field"];
			} else {
				$ftsico = "fa-times-circle vermelho fa-2x blink";
				$ftstitle = "O evFTS está com status [" . $re["Status"] . "]. Verificar o problema.";
			}

			$reshn = _moduloController::buscarHostsParaFts($_1_u__modulo_modulo);

		?>


				

			<div class="panel panel-default">
				<div class="panel-heading">
					<span href="#ftsstatus" data-toggle="collapse">FTS Status:</span>
					<div class="panel-body" id="ftsstatus">
						<table>
							<tr style="border-bottom: 1px solid white;">

								<td><i class="fa <?= $ftsico ?>" title="<?= $ftstitle ?>"></i></td>
							</tr>
							<?
							foreach ($reshn as $k => $rhn) {
								$corhn = $rhn["checked"] == "checked" ? "verde" : "";
								$descr = !empty($rhn["descr"]) ? "(" . $rhn["descr"] . ")" : "";
							?>
								<tr>
									<td class="<?= $corhn ?>"><?= $rhn["hostname"] ?> <?= $descr ?>:</td>
									<td><input type="checkbox" <?= $rhn["checked"] ?> onchange="habilitaModuloHostname(this,'<?= $_1_u__modulo_modulo ?>','<?= $rhn["hostname"] ?>','<?= $rhn["idftsmodulo"] ?>')"></td>
								</tr>
							<?
							}
							?>
							<tr style="border-top: 1px solid white;">
								<td colspan="99">
									<a class="btn btn-xs" href="javascript:resetarFTS()">Re-iniciar Full Text Search</a>
								</td>
							</tr>
							<?$resl = _moduloController::buscarUltimosLogsDeFts($_1_u__modulo_tab);?>

							<tr>
								<th>Id Exec.</th>
								<th>Status</th>
								<!--<th>Log</th>-->
								<th>Data Exec.</th>
							</tr>
							<?
							foreach ($resl as $k => $rl) {
								$classlog = $rl["status"] == "FULL" ? "blink vermelho bold" : "";
							?>
								<tr class="<?= $classlog ?>" title="<?= $rl["log"] ?>">
									<td style="width: 80px;text-overflow: ellipsis;height: 10px;white-space: nowrap;max-width: 30px;overflow: hidden;"><?= $rl["idexec"] ?></td>
									<td class="nowrap"><?= $rl["status"] ?></td>
									<!--<td><?= $rl["log"] ?></td>-->
									<td class="nowrap"><?= dmahms($rl["criadoem"], true) ?></td>
								</tr>
								<?}?>
						</table>
					</div>
				</div>
			</div>
			<!-- <div class="panel panel-default">
			<div class="panel-heading">
				Documentos do Módulo
			</div>
			<div class="panel-body">
			 
			</div>
		</div> -->

		<?
		}
		?>
	</div>
	<div class="col-md-12">
	<?
}
$tabaud = "_modulo"; //pegar a tabela do criado/alterado em antigo
$idRefDefaultDropzone = "anexos";
require 'viewCriadoAlterado.php';
?>
</div>
<? require_once(__DIR__ . "/js/_modulo_js.php"); ?>