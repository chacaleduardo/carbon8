<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}

$pagvaltabela = "rep";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idrep" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from rep where idrep = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php")
?>
<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table style="width: 100%;">
					<tr>
						<td align="right">ID:</td>
						<td>
							<input name="_1_<?=$_acao?>_rep_idrep"  type="hidden" value="<?=$_1_u_rep_idrep?>">
							<span class="alert-warning"><?=$_1_u_rep_idrep?></span>
						</td>
						<td align="right">Alias:</td>
						<td>
							<input name="_1_<?=$_acao?>_rep_alias"  type="text" value="<?=$_1_u_rep_alias?>">
						</td>
						<td align="right"align="right">Tipo:</td>
						<td>
							<select name="_1_<?=$_acao?>_rep_tipo" vnulo>
								<option></option>
								<?
									fillselect("select 'SECUNDARIO','Secundário' union select 'MASTER','Master'",$_1_u_rep_tipo);
								?>
							</select>
						</td>
						<td align="right">Status:</td>
						<td>
							<select name="_1_<?=$_acao?>_rep_status">
								<option value="ATIVO">Ativo</option>
								<option value="INATIVO">Inativo</option>
							</select>
						</td>
					</tr>
				</table>
			</div>
			<div class="panel-body">
				<div class="col-sm-12">
					<div class="col-sm-5">
						<table>
							<tr>
								<td style="width:1%">IP:</td>
								<td>
									<input name="_1_<?=$_acao?>_rep_ip"  type="text" value="<?=$_1_u_rep_ip?>">
								</td>
							</tr>
							<tr>
								<td  style="width:1%">Modelo:</td>
								<td>
									<input name="_1_<?=$_acao?>_rep_modelo" type="text" value="<?=$_1_u_rep_modelo?>">
								</td>
							</tr>
						</table>
					</div>

					<div class="col-sm-7">
						<table style="width: 100%;">
							<tr>
								<td style="width: 1%;vertical-align:top;">Observação:</td>
								<td>
									<textarea name="_1_<?=$_acao?>_rep_obs" rows="4" style="width: 100%;"><?=$_1_u_rep_obs?></textarea>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?
if(!empty($_1_u_rep_idrep)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_rep_idrep; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "rep"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>