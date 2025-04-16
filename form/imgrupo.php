<?
require_once("../inc/php/validaacesso.php");

if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "imgrupo";
$pagvalcampos = array(
	"idimgrupo" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from "._DBAPP.".imgrupo where idimgrupo = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");

require_once(__DIR__."/controllers/imgrupo_controller.php");

if ($_1_u_imgrupo_idimgrupo) {
	$emaisVirtuaisDisponiveisParaVinculo = ImGrupoController::buscarEmailsVirtuaisDisponiveisParaVinculoPorIdImGrupo($_1_u_imgrupo_idimgrupo);
	$pessoasDisponiveisParaVinculo = ImGrupoController::buscarPessoasDisponiveisParaVinculoPorIdImGrupoEIdEmpresa($_1_u_imgrupo_idimgrupo, cb::idempresa());
	$gruposDeAreasDepsSetoresDisponiveisParaVinculo = ImGrupoController::buscarGruposDeAreasDepsSetoresDisponiveisParaVinculoPorIdImGrupo($_1_u_imgrupo_idimgrupo);
	$fluxosLiberadosCriarEvento = ImGrupoController::buscarFluxoEvento($_1_u_imgrupo_idimgrupo, 'imgrupo');

	if ($_1_u_imgrupo_idobjetoext) {
		$idobjetoext = $_1_u_imgrupo_idobjetoext;

		if ($_1_u_imgrupo_tipoobjetoext == '_lp') {
			$idobjetoext = ImGrupoController::buscarLpGrupoPorIdLp($_1_u_imgrupo_idobjetoext)[0]['lpgrupopar'];
		}
	}
}

function listarFluxos()
{
	global $_1_u_imgrupo_idimgrupo;

	$fluxos = ImGrupoController::buscarFluxosVinculadosPorIdImGrupo($_1_u_imgrupo_idimgrupo);

	foreach ($fluxos as $fluxo) {
		echo "	<tr>
					<td>
						<a href='?_modulo=fluxo&_acao=u&idfluxo={$fluxo['idfluxo']}' target='_blank'>
							".($fluxo['tipoobjeto'] ? $fluxo['tipoobjeto'] : '[VALOR NÃO DEFINIDO]')."
						</a>						
					</td>
					<td align='center'>
						<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='excluirFluxo(".$fluxo['idfluxoobjeto'].")' title='Excluir!'></i>
					</td>
				</tr>
		";
	}
}

function listarFucionario()
{
	global $_1_u_imgrupo_idimgrupo;

	$pessoas = ImGrupoController::buscarPessoasVinculadasPorIdImGrupo($_1_u_imgrupo_idimgrupo);

	if (!count($pessoas)) {
		return false;
	}

	echo "	<tr>
				<td>Nome</td>
				<td align='center'>	
					Açao
				</td>
			</tr>";

	foreach ($pessoas as $pessoa) {
		$modulo = "pessoa";

		if ($pessoa["idtipopessoa"] == 1) {
			$modulo = "funcionario";
		}

		$vermelho = '';
		$excluir = '';

		if ($pessoa["inseridomanualmente"] == 'Y') {
			$vermelho = "vermelho";
			$pessoa["inseridomanualmente"] = 'Sim';
			$excluir = 'Y';
		} else {
			$pessoa["inseridomanualmente"] = 'Nao';
		}

		echo "<tr><td><a  class='".$vermelho."' title='Editar Funcionario' target='_blank' href='?_modulo=".$modulo."&_acao=u&idpessoa=".$pessoa["idpessoa"]."'>".$pessoa["nome"]."</a></td>
              ";
		if ($excluir == 'Y') {
			echo " <td align='center'>	
                    <i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='inativaobjeto(".$pessoa['idimgrupopessoa'].")' title='Excluir!'></i>
                </td>";
		} else {
			echo " <td align='center'>	
                    <i class='fa fa-ban fa-1x cinzaclaro  btn-lg ui-droppable'></i>
                </td>";
		}
		echo "   </tr>";
	}
}

function listarGruposDeAreasDepsSetores()
{
	global $_1_u_imgrupo_idimgrupo;

	$grupos = ImGrupoController::buscarGruposDeAreasDepsSetoresPorIdImGrupo($_1_u_imgrupo_idimgrupo);

	foreach ($grupos as $grupo) {
		if ($grupo["tipo"] == 'sgsetor') {
			$link = "?_modulo=sgsetor&_acao=u&idsgsetor=".$grupo["id"];
			$title = "Editar Setor";
		} elseif ($grupo["tipo"] == 'sgarea') {
			$link = "?_modulo=sgarea&_acao=u&idsgarea=".$grupo["id"];
			$title = "Editar Área";
		} elseif ($grupo["tipo"] == 'sgdepartamento') {
			$link = "?_modulo=sgdepartamento&_acao=u&idsgdepartamento=".$grupo["id"];
			$title = "Editar Departamento";
		}


		echo "<tr><td><a title='".$title."' target='_blank' href='".$link."'>".$grupo["name"]."</a></td>
				<td align='center' width='70px'>	
					<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='inativavinculo(".$grupo['idobjetovinculo'].")' title='Excluir!'></i>
				</td>
			</tr>";
	}
}

// GVT - 19/05/2020 - Função retorna lista de emails virtuais associados ao grupo.
function listarEmailVinculados()
{
	global $_1_u_imgrupo_idimgrupo;

	$emails = ImGrupoController::buscarEmailsVirtuaisPorIdImGrupo($_1_u_imgrupo_idimgrupo);

	echo "<table class='table-hover'><tbody>";
	foreach ($emails as $email) {
		$title = "Vinculado por: ".$email["criadopor"]." - ".dmahms($email["criadoem"], true);
		echo "<tr><td><a title='".$title."' target='_blank' href='?_modulo=emailvirtualconf&_acao=u&idemailvirtualconf=".$email["idemailvirtualconf"]."'>".$email["email_original"]."</a></td><td><i class='fa fa-trash vermelho fade hoververmelho' title='Desvincular'  onclick='desvincularEmail(".$email["idemailvirtualconfimgrupo"].")'></i></td></tr>";
	}
	echo "</tbody></table>";
}

?>
<div class="container-fluid">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table>
					<tr>
						<td align="right"></td>
						<td>
							<input name="_1_<?=$_acao?>_imgrupo_idimgrupo" type="hidden" value="<?=$_1_u_imgrupo_idimgrupo?>" readonly='readonly'>
						</td>

						<td align="right">Grupo:</td>
						<td>
							<input class="size40" name="_1_<?=$_acao?>_imgrupo_grupo" type="text" value="<?=$_1_u_imgrupo_grupo?>" vnulo>
						</td>
						<td align="right">Status:</td>
						<td>
							<select name="_1_<?=$_acao?>_imgrupo_status">
								<? fillselect(ImGrupoController::$status, $_1_u_imgrupo_status);?>
							</select>
						</td>
					</tr>
					</tr>
				</table>
			</div>
			<div class="panel-body">
				<div class="col-sm-7">
					<table style="width:100%;">
						<tr>
							<td>Descrição:</td>
						</tr>
						<tr>
							<td>
								<textarea style="margin: 0px; width: 100%;" name="_1_<?=$_acao?>_imgrupo_descr" cols="45" rows="10"><?=$_1_u_imgrupo_descr?></textarea>
							</td>
						</tr>
					</table>
				</div>
				<div class="col-sm-5">
					<div class="row">
						<table style="width:100%;">
							<tr>
								<td>Tipo Objeto:
									<? if (!empty($_1_u_imgrupo_idobjetoext)) {
									?>
										<a href="?_modulo=<?=$_1_u_imgrupo_tipoobjetoext?>&_acao=u&id<?=$_1_u_imgrupo_tipoobjetoext == '_lp' ? 'lpgrupo' : $_1_u_imgrupo_tipoobjetoext?>=<?=$idobjetoext?>" target="_blank">
											[<font color="red"><?=$idobjetoext?></font>]
										</a>
									<? }?>
								</td>
								<td>Inserido Manualmente:</td>
								<td>Grupo de Liderança</td>
							</tr>
							<tr>
								<td>
									<select name="_1_<?=$_acao?>_imgrupo_tipoobjetoext">
										<? //Lucas Melo - 14/03/2022 - inclusão de sgdepartamento
										fillselect(ImGrupoController::$tiposObjeto, $_1_u_imgrupo_tipoobjetoext);?>
									</select>
								</td>
								<td>
									<select name="_1_<?=$_acao?>_imgrupo_inseridomanualmente">
										<? fillselect(ImGrupoController::$simNao, $_1_u_imgrupo_inseridomanualmente);?>
									</select>
								</td>
								<td>
									<select name="_1_<?=$_acao?>_imgrupo_grupolideranca">
										<? fillselect(ImGrupoController::$simNao, $_1_u_imgrupo_grupolideranca);?>
									</select>
								</td>
							</tr>
						</table>
					</div>
					<?
					// ------------------------------------------------------------------------------------------------------------------------------------ //
					//															EMAILS VIRTUAIS																//
					// ------------------------------------------------------------------------------------------------------------------------------------ //

					// GVT - 19/05/2020 - bloco lista e chama função que vincula emailvirtualconf com o grupo.
					?>
					<? if ($_1_u_imgrupo_idimgrupo) {?>
						<div class="row" style="margin-top: 10px;">
							<table style="width:100%;">
								<tr>
									<td>Email(s) Virtual(is):</td>
								</tr>
								<tr id="emailsvirtuais">
									<td><input id="emailvinc" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
									<td style="width: 1%;"><a class="fa fa-plus-circle fa-x verde btn-lg pointer" onclick="novoemail()" title="Novo Email"></a></td>
								</tr>
							</table>
							<?=listarEmailVinculados()?>
						</div>
					<? }?>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
<? if (!empty($_1_u_imgrupo_idimgrupo)) {?>
	<div class="container-fluid">
		<!-- Fluxos vinculados -->
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">Fluxos vinculados</div>
				<div class="panel-body">
					<table>
						<tr>
							<td>
								<input id="fluxovinculados" class="compacto ui-autocomplete-input" type="text" cbvalue="" placeholder="Selecione" autocomplete="off">
							</td>
						</tr>
					</table>
					<table class='table-hover w-100'>
						<tbody>
							<?=listarFluxos()?>
						</tbody>
					</table>
					<hr>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">Pessoas do grupo</div>
				<div class="panel-body">
					<table>
						<tr>
							<td><input id="imgrupovinc" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
						</tr>
					</table>
					<table class='table-hover w-100'>
						<tbody>
							<?=listarFucionario()?>
						</tbody>
					</table>
					<hr>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">Áreas, Departamentos e Setores Vinculados (Grupos)</div>
				<div class="panel-body">
					<table>
						<tr>
							<td><input id="objetovinculo" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
						</tr>
					</table>
					<table class='table-hover w-100'>
						<tbody>
							<?=listarGruposDeAreasDepsSetores()?>
						</tbody>
					</table>
					<hr>
				</div>
			</div>
		</div>
	</div>
<? }?>
<?
if (!empty($_1_u_imgrupo_idimgrupo)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_imgrupo_idimgrupo; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}

$tabaud = "imgrupo"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';

require_once(__DIR__."/js/imgrupo_js.php");
?>