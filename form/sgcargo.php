<?
require_once("../inc/php/validaacesso.php");

if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "sgcargo";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
	"idsgcargo" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT sc.*, 
				  CASE WHEN sc.tipoobjeto = 'sgarea' THEN sa.area
				       WHEN sc.tipoobjeto = 'sgdepartamento' THEN sd.departamento
					   WHEN sc.tipoobjeto = 'sgsetor' THEN ss.setor END AS nome
			 FROM " . _DBAPP . ".sgcargo sc  
		LEFT JOIN " . _DBAPP . ".sgarea sa ON sc.idobjeto = sa.idsgarea AND sc.tipoobjeto = 'sgarea'
		LEFT JOIN " . _DBAPP . ".sgdepartamento sd ON sc.idobjeto = sd.idsgdepartamento AND sc.tipoobjeto = 'sgdepartamento'
		LEFT JOIN " . _DBAPP . ".sgsetor ss ON sc.idobjeto = ss.idsgsetor AND sc.tipoobjeto = 'sgsetor'
		    WHERE sc.idsgcargo = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");

require_once(__DIR__ . "/controllers/sgcargo_controller.php");
require_once(__DIR__ . "/controllers/sgfuncao_controller.php");
require_once(__DIR__ . "/controllers/pessoa_controller.php");

if ($_1_u_sgcargo_idsgcargo) {
	$funcoesDisponiveisParaVinculo = SgfuncaoController::buscarFuncoesDisponiveisParaVinculoPorIdSgcargo($_1_u_sgcargo_idsgcargo);
	$pessoasDisponiveisParaVinculo = PessoaController::buscarPessoasDisponiveisParaVinculoPorIdSgcargoEGetIdEmpresa($_1_u_sgcargo_idsgcargo, getidempresa('a.idempresa', 'funcionario'));
	$areasDepsSetoresDisponiveisParaVinculo = SgCargoController::buscarAreasDepsSetoresDisponiveisParaVinculoPorGetIdEmpresa($_1_u_sgcargo_idsgcargo);
}



function listarSetoresDepartamentosAreasVinculadas()
{
	global $_1_u_sgcargo_idsgcargo;

	
	$setores = SgCargoController::listarSetoresDepartamentosAreasVinculadas($_1_u_sgcargo_idsgcargo);

	if (!count($setores)) {
		return false;
	}

	echo "<table class='table-hover mt-3 w-100'>
			<tbody>";

	foreach ($setores as $setor) {
		$title = "Vinculado por: " . $setor["criadopor"] . " - " . dmahms($setor["criadoem"], true);

		$cor = 'cinzaclaro hoververmelho';
		echo "<tr id=" . $setor["idtipoobjetovinc "] . "><td title='" . $title . "'>" . $setor["resultado"] ."</td><td><i class='fa fa-trash $cor' title='" . $setor["i"] . "' idobjetovinc='" . $setor["idobjetovinc"] . "' status='" . $setor["status"] . "' onclick='desvincularSetor(" . $setor['idobjetovinculo'] . ")' title='Excluir!'></i></td></tr>";
	}
	echo "</tbody></table>";
}

function listarSgfuncoes()
{
	global $_1_u_sgcargo_idsgcargo;

	$funcoes = SgCargoController::buscarFuncoesPorIdSgcargo($_1_u_sgcargo_idsgcargo);

	if (!count($funcoes)) {
		return false;
	}

	echo "<table class='table-hover mt-3 w-100'>
			<tbody>";

	foreach ($funcoes as $funcao) {
		$title = "Vinculado por: " . $funcao["criadopor"] . " - " . dmahms($funcao["criadoem"], true);
		//if ($funcao["status"] == 'ATIVO'){ $cor = 'verde hoververde'; }else{ $cor = 'vermelho hoververmelho';}
		$cor = 'cinzaclaro hoververmelho';
		echo "<tr id=" . $funcao["idsgcargofuncao"] . "><td title='" . $title . "'>" . $funcao["funcao"] . "</td><td><i class='fa fa-trash $cor' title='" . $funcao["i"] . "' idsgcargofuncao='" . $funcao["idsgcargofuncao"] . "' status='" . $funcao["status"] . "' onclick='AlteraStatus(this)'></i></td></tr>";
	}
	echo "</tbody></table>";
}

function listarFucionario()
{
	global $_1_u_sgcargo_idsgcargo;

	$pessoas = PessoaController::buscarPessoasPorIdSgcargo($_1_u_sgcargo_idsgcargo);

	foreach ($pessoas as $pessoa) {
		echo "<tr><td><a title='Editar Funcionario' target='_blank' href='?_modulo=funcionario&_acao=u&idpessoa=" . $pessoa["idpessoa"] . "'>" . $pessoa["nome"] . "</a></td>
                <td align='center'>	
                    <i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='inativaobjeto(" . $pessoa['idpessoa'] . ")' title='Excluir!'></i>
                </td>
                </tr>";
	}
}

?>
<style>
	.diveditor {
		border: 1px solid gray;
		background-color: white;
		color: black;
		font-family: Arial, Verdana, sans-serif;
		font-size: 10pt;
		font-weight: normal;
		width: 800px;
		height: 260px;
		word-wrap: break-word;
		overflow: auto;
		padding: 5px;
	}

	.desabilitado {
		background-color: #ece5e5 !important;
	}

	.row
	{
		margin-left: 0 !important;
		margin-right: 0 !important;
	}
</style>
<div class="">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table style="width:100%">
					<tr>
						<td align="right"></td>
						<td>
							<input name="_1_<?= $_acao ?>_sgcargo_idsgcargo" type="hidden" value="<?= $_1_u_sgcargo_idsgcargo ?>" readonly='readonly'>
						</td>
						<td align="right">Tipo:</td>
						<td>
							<select name="_1_<?= $_acao ?>_sgcargo_tipo">
								<? fillselect(SgCargoController::$tipo, $_1_u_sgcargo_tipo); ?>
							</select>
						</td>
						<td align="right">Cargo:</td>
						<td>
							<input class="size35" name="_1_<?= $_acao ?>_sgcargo_cargo" value="<?= $_1_u_sgcargo_cargo ?>" vnulo>
							<input class="size35" name="_1_<?= $_acao ?>_sgcargo_descr" type="hidden" value="<?= $_1_u_sgcargo_descr ?>">
						</td>
						<td align="right">Nível:</td>
						<td>
							<select name="_1_<?= $_acao ?>_sgcargo_nivel">
								<? fillselect(SgCargoController::$nivel, $_1_u_sgcargo_nivel); ?>
							</select>
						</td>
						<? if (!empty($_1_u_sgcargo_idsgcargo)) { ?>
							<td align="right">Salário Base R$:</td>
							<td class="tdbr" align="left"> <input type="text" name="_1_<?= $_acao ?>_sgcargo_salario" value="<?= $_1_u_sgcargo_salario ?>" style="width:70px">
							</td>

						<? } ?>
						<td align="right">Status:</td>
						<td>
							<select name="_1_<?= $_acao ?>_sgcargo_status">
								<? fillselect(SgCargoController::$status, $_1_u_sgcargo_status); ?>
							</select>
						</td>
					</tr>
				</table>
			</div>
			<div class="panel-body">
				<div class="col-md-12">
					<div class="row">
						
							<div class="col-md-6">
								<div class="row mb-4">
									<div class="col-md-8">
										<div class="row">
											<div class="col-md-12">
												Setor: 
											</div>
											<div class="col-md-12">
												<input id="sgsetorvinc" class="w-100" type="text" cbvalue placeholder="Selecione" value="<?= $_1_u_sgcargo_nome ?>">
											</div>

											<div class="col-md-12">
												<?= listarSetoresDepartamentosAreasVinculadas() ?>
											</div>

										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-8">
										<div class="row">
											<div class="col-md-12">
												Funções:
											</div>
											<div class="col-md-12">
												<input id="sgareavinc" class="compacto" type="text" cbvalue placeholder="Selecione">
												<?= listarSgfuncoes() ?>
											</div>
										</div>
									</div>
								</div>
							</div>
					
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-12">
									Formação:
								</div>
								<div class="col-md-12">
									<label id="lbaviso" class="idbox" style="display: none;"></label>
									<div id="diveditor" class="diveditor" onkeypress="pageStateChanged=true;"><?= $_1_u_sgcargo_obs ?></div>
									<textarea style="display: none;" name="_1_<?= $_acao ?>_sgcargo_obs"><?= $_1_u_sgcargo_obs ?></textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<? if (!empty($_1_u_sgcargo_idsgcargo)) { ?>
	<div class="">
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">Pessoas do Cargo</div>
				<div class="panel-body">
					<table>
						<tr>
							<td><input id="sgpessoavinc" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
						</tr>
					</table>
					<table class='table-hover'>
						<tbody>
							<?= listarFucionario() ?>
						</tbody>
					</table>
					<hr>
				</div>
			</div>
		</div>
	</div>
<? } ?>
<?
	if (!empty($_1_u_sgcargo_idsgcargo)) { // trocar p/ cada tela a tabela e o id da tabela
		$_idModuloParaAssinatura = $_1_u_sgcargo_idsgcargo; // trocar p/ cada tela o id da tabela
		require 'viewAssinaturas.php';
	}

	$tabaud = "sgcargo"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';

	require_once(__DIR__."/js/sgcargo_js.php");
?>