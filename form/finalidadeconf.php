<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 * pk: indica parâmetro chave para o select inicial
 * vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "finalidadeconf";
$pagvalcampos = array(
	"idfinalidadeconf" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from finalidadeconf where idfinalidadeconf = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">CFOP Entrada / Finalidade</div>
			<div class="panel-body">
				<table>
					<tr>
						<td></td>
						<td><input name="_1_<?= $_acao ?>_finalidadeconf_idfinalidadeconf" type="hidden" value="<?= $_1_u_finalidadeconf_idfinalidadeconf ?>" readonly='readonly'></td>
					</tr>
					<tr>
						<td align="right">CFOP</td>
						<td><input class="size8" size="8" name="_1_<?= $_acao ?>_finalidadeconf_cfopnf" type="text" value="<?= $_1_u_finalidadeconf_cfopnf ?>" vnulo></td>
					</tr>
					<tr>
						<td align="right">CFOP Entrada</td>
						<td><input class="size8" size="8" name="_1_<?= $_acao ?>_finalidadeconf_cfopentrada" type="text" value="<?= $_1_u_finalidadeconf_cfopentrada ?>" vnulo></td>
					</tr>
					<tr>
						<td align="right">Descr.</td>
						<td><textarea name="_1_<?= $_acao ?>_finalidadeconf_obs" style=" width: 433px; height: 35px;"><?= $_1_u_finalidadeconf_obs ?></textarea></td>
					</tr>
					<tr>
						<td align="right">Tipo Finalidade:</td>
						<td>
							<select class="size8" name="_1_<?= $_acao ?>_finalidadeconf_tipoconsumo">
								<? fillselect("select 'faticms','Industria' union select 'comercio','Comércio' union select 'consumo','Consumo' union select 'imobilizado','Imobilizado' union select 'outro','Outros'", $_1_u_finalidadeconf_tipoconsumo); ?>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right">Status:</td>
						<td>
							<select class="size8" name="_1_<?= $_acao ?>_finalidadeconf_status">
								<? fillselect("SELECT 'ATIVO', 'Ativo' UNION SELECT 'INATIVO', 'Inativo'", $_1_u_finalidadeconf_status); ?>
							</select>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<p>
<?
if (!empty($_1_u_finalidadeconf_idfinalidadeconf)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_finalidadeconf_idfinalidadeconf; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "finalidadeconf"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';

?>