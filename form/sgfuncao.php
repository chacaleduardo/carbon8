<?
require_once("../inc/php/functions.php");
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
$pagvaltabela = "sgfuncao";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
	"idsgfuncao" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from sgfuncao where idsgfuncao = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
require_once(__DIR__ . "/controllers/sgfuncao_controller.php");

?>
<input name="_1_<?= $_acao ?>_sgfuncao_idsgfuncao" type="hidden" value="<?= $_1_u_sgfuncao_idsgfuncao ?>" readonly='readonly'>
<div class="col-md-12">
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="row">
				<div class="col-md-1" style="text-align: right">
					<label class="text-white" style="position: relative; top:4px;">Tí­tulo</label>
				</div>
				<div class="col-md-8">
					<input name="_1_<?= $_acao ?>_sgfuncao_funcao" type="text" value="<?= $_1_u_sgfuncao_funcao ?>">
					</td>
				</div>
				<div class="col-md-1" style="text-align: right">
					<label class="text-white" style="position: relative; top:4px;">Status</label>
				</div>
				<div class="col-md-2">
					<select name="_1_<?= $_acao ?>_sgfuncao_status">
						<? fillselect(SgfuncaoController::$status, $_1_u_sgfuncao_status); ?>
					</select>
				</div>
			</div>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-md-1" style="text-align: right">
					Descrição
				</div>
				<div class="col-md-8">
					<textarea style="margin: 0px; width: 100%; height: 111px;" name="_1_<?= $_acao ?>_sgfuncao_observacao" cols="45" rows="3"><?= $_1_u_sgfuncao_observacao ?></textarea>
					</td>
				</div>
			</div>
		</div>
	</div>
</div>

<?
if (!empty($_1_u_sgfuncao_idsgfuncao)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_sgfuncao_idsgfuncao; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "sgfuncao"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';

require_once(__DIR__."/js/sgfuncao_js.php");
?>