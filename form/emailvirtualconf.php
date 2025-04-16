<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "emailvirtualconf";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
	"idemailvirtualconf" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from emailvirtualconf where idemailvirtualconf = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

require_once(__DIR__ . "/controllers/emailvirtualconf_controller.php");

if ($_1_u_emailvirtualconf_idemailvirtualconf)
{
	$pessoas = EmailVirtualConfController::buscarPessoasPorIdEmailVirtualConfEShare($_1_u_emailvirtualconf_idemailvirtualconf, share::emailvirtualconfPorSessionIdempresa('p.idpessoa'));
	$gruposDisponiveisParaVinculo = EmailVirtualConfController::buscarGruposDisponiveisParaVinculoPorIdEmailVirtualConf($_1_u_emailvirtualconf_idemailvirtualconf);
	$buscarWebmailAssinaturaTemplateDisponiveisParaaVinculo = EmailVirtualConfController::buscarWebmailAssinaturaTemplateDisponiveisParaVinculoPorIdEmailVirtualConf($_1_u_emailvirtualconf_idemailvirtualconf);
	list('emaildestino' => $emailsDeDestino, 'idpessoas' => $arrIdPessoas) = EmailVirtualConfController::buscarEmailDestinoPorEmail($_1_u_emailvirtualconf_emails_destino, $_1_u_emailvirtualconf_idemailvirtualconf);

	$pessoasEmails = EmailVirtualConfController::buscarPessoasPorIdEmailVirtualConf($_1_u_emailvirtualconf_idemailvirtualconf);
	$gruposVinculados = EmailVirtualConfController::buscarGruposVinculadosPorIdEmailVirtualConf($_1_u_emailvirtualconf_idemailvirtualconf);
	$assinaturaEmailCampos = EmailVirtualConfController::buscarAssinaturaEmailCamposPorIdEmailVirtualConf($_1_u_emailvirtualconf_idemailvirtualconf);
	$webmailAssinatura = EmailVirtualConfController::buscarWebmailAssinaturaPorIdEmailVirtualConfEEmailOriginal($_1_u_emailvirtualconf_idemailvirtualconf, $_1_u_emailvirtualconf_email_original);
}

?>
<style>
	.titulo_email {
		position: relative;
		display: block;
		padding: 6px 9px;
		font-size: 11px;
		text-transform: uppercase;
	}

	.panel-body {
		padding-top: 0 !important;
	}

	.spanemail {
		color: #8d8d8d;
	}

	.table-stripped tbody tr:nth-child(odd) {
		background-color: #E8E8E8
	}

	.table-stripped tbody tr {
		background-color: #FFFFFF
	}
</style>
<div class="row ">
	<div class="col-md-12">
		<div class="panel panel-default">

			<?
			// -------------------------------------------------------------------------------------------------------------------------------- //

			//														EMAIL ORIGINAL																//

			// -------------------------------------------------------------------------------------------------------------------------------- //
			?>
			<div class="panel-heading">
				<table>
					<tr>
						<!-- Id -->
						<td>
							<input name="_1_<?= $_acao ?>_emailvirtualconf_idemailvirtualconf" type="hidden" value="<?= $_1_u_emailvirtualconf_idemailvirtualconf ?>">
						</td>
						<!-- Email Original -->
						<td>Email Original:</td>
						<td>
							<input name="_1_<?= $_acao ?>_emailvirtualconf_email_original" type="text" size="60" value="<?= $_1_u_emailvirtualconf_email_original ?>" placeholder="Exemplo: origem@laudolab.com.br">
						</td>
						<!-- Titulo Assinatura -->
						<td>Titulo Assinatura:</td>
						<td>
							<input name="_1_<?= $_acao ?>_emailvirtualconf_titulo" type="text" size="60" value="<?= $_1_u_emailvirtualconf_titulo ?>" placeholder="Exemplo: DEPARTAMENTO ADMINISTRATIVO">
						</td>
						<!-- Tipo de Envio -->
						<td>Tipo de Envio:</td>
						<td>
							<select id="tipoenvio" class="form-control" name="_1_<?= $_acao ?>_emailvirtualconf_tipoenvio">
								<? fillselect(EmailVirtualConfController::$tipoDeEnvio, $_1_u_emailvirtualconf_tipoenvio); ?>
							</select>
						</td>
						<!-- Status -->
						<td>Status:</td>
						<td>
							<select id="status" class="form-control" name="_1_<?= $_acao ?>_emailvirtualconf_status" value="<?= $_1_u_emailvirtualconf_status ?>">
								<? fillselect(EmailVirtualConfController::$status, $_1_u_emailvirtualconf_status); ?>
							</select>
						</td>
					</tr>
				</table>
			</div>
			<?
			// -------------------------------------------------------------------------------------------------------------------------------- //

			//														EMAILS DE DESTINO															//

			// -------------------------------------------------------------------------------------------------------------------------------- //
			?>
			<div class="panel-body">
				<div class="col-sm-5">
					<? if (!empty($_1_u_emailvirtualconf_idemailvirtualconf)) { ?>
						<label style="margin-bottom: 10px;">Email(s) de Destino:</label>
						<table class="table table-stripped table-hover">
							<tr class="header">
								<th>#</th>
								<th>Nome</th>
								<th>Email</th>
							</tr>
							<?
							// Loop para construção da tabela de emails de destino
							foreach ($emailsDeDestino as $key => $email) { ?>
								<tr style="color:<?= $email["cor"] ?>">
									<td><?= $key + 1; ?></td>
									<td><?= $email["nomecurto"] ?></td>
									<td><?= $email["emaildestino"] ?></td>
								</tr>
							<? } ?>
						</table>
						<label style="margin-bottom: 10px;">Legenda:</label>
						<table>
							<tr>
								<td>
									<div style="background-color: red;width: 15px;height: 15px;border-radius: 20%;border: 1px solid darkgray;"></div>
								</td>
								<td>Inserido Manualmente</td>
							</tr>
							<tr>
								<td>
									<div style="background-color: blue;width: 15px;height: 15px;border-radius: 20%;border: 1px solid darkgray;"></div>
								</td>
								<td>Inserido por Grupo</td>
							</tr>
						</table>
					<? } ?>
				</div>
				<div class="col-sm-1">
				</div>
				<?
				// -------------------------------------------------------------------------------------------------------------------------------- //

				//														EMAILS VINCULADOS															//
				//													 (inserido manualmente)															//

				// -------------------------------------------------------------------------------------------------------------------------------- //
				?>
				<div class="col-sm-6">
					<div class="row">
						<div class="col-sm-12">
							<?
							// GVT - 19/05/2020 - Bloco lista e associa a pessoa ao email virtual
							if (!empty($_1_u_emailvirtualconf_idemailvirtualconf)) { ?>
								<label style="margin-bottom: 10px;">Emails Adicionais Vinculados:</label>
								<table class="table table-hover">
									<? if (count($pessoasEmails)) { ?>
										<tr class="header">
											<th>#</th>
											<th>Funcionário</th>
											<th>Email</th>
											<th>Retirar</th>
										</tr>
										<?
										$troca = "S";
										$qtd = 0;
										foreach ($pessoasEmails as $pessoa) {
											$qtd = $qtd + 1;
											//mudar a cor da linha
											if ($troca == "S") {
												$cortr = "#FFFFFF";
												$troca = "N";
											} else {
												$cortr = "#E8E8E8";
												$troca = "S";
											} ?>

											<tr class="respreto" style=" background-color:<?= $cortr ?>">
												<td><?= $qtd ?></td>
												<td><a class="text-uppercase" href="?_modulo=confacessocolaborador&_acao=u&_idempresa=8&idpessoa=<?= $pessoa['idpessoa'] ?>" target="_blank"><?= $pessoa['nomecurto'] ?></a></td>
												<td><?= $pessoa['webmailemail'] ?></td>
												<td align="center">
													<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" onclick="retirar('<?= $pessoa['idemailvirtualconfpessoa'] ?>');" title="Retirar pessoa"></i>
												</td>
											</tr>
									<? }
									} ?>
									<tr>
										<td colspan="4"><input type="text" name="emailvirtualconfpessoa" cbvalue="emailvirtualconfpessoa" value="" style="width: 100%;"></td>
									</tr>
								</table>
							<? } ?>
						</div>
					</div>
					<?
					// -------------------------------------------------------------------------------------------------------------------------------- //

					//														GRUPOS VINCULADOS															//

					// -------------------------------------------------------------------------------------------------------------------------------- //
					?>
					<div class="row">
						<div class="col-sm-12">
							<?
							// GVT - 19/05/2020 - Bloco lista e associa um grupo ao email virtual
							if (!empty($_1_u_emailvirtualconf_idemailvirtualconf)) { ?>
								<label style="margin-bottom: 10px;">Grupos Vinculados:</label>
								<table class="table table-hover">
									<? if (count($gruposVinculados)) { ?>
										<tr class="header">
											<th>#</th>
											<th>Grupo</th>
											<th>Retirar</th>
										</tr>
										<?
										$troca1 = "S";
										$qtd1 = 0;
										foreach ($gruposVinculados as $grupo) {
											$qtd1 = $qtd1 + 1;
											//mudar a cor da linha
											if ($troca1 == "S") {
												$cortr1 = "#FFFFFF";
												$troca1 = "N";
											} else {
												$cortr1 = "#E8E8E8";
												$troca1 = "S";
											} ?>
											<tr class="respreto" style=" background-color:<?= $cortr1 ?>">
												<td><?= $qtd1 ?></td>
												<td><a target='_blank' href='?_modulo=imgrupo&_acao=u&idimgrupo=<?= $grupo['idimgrupo'] ?>'><?= $grupo['grupo'] ?></a></td>
												<td align="center" style="width: 1%;">
													<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" onclick="retirargrupo('<?= $grupo['idemailvirtualconfimgrupo'] ?>');" title="Retirar pessoa"></i>
												</td>
											</tr>
									<? }
									} ?>
									<tr>
										<td colspan="4"><input type="text" name="emailvirtualconfimgrupo" cbvalue="emailvirtualconfimgrupo" value="" style="width: 100%;"></td>
									</tr>
								</table>
							<? } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<? if (!empty($_1_u_emailvirtualconf_idemailvirtualconf) and !empty($_1_u_emailvirtualconf_email_original)) { ?>
	<div class="row">
		<div class="col-md-8">
			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#identidadewebm">Assinaturas de E-mail</div>
				<div class="panel-body" id="identidadewebm">
					<ul class="nav">
						<li class="panel" style="background:#e6e6e6;border: 1px solid #ddd;">
							<div class="titulo_email pointer">
								<a>Conteúdo Assinatura de E-mail</a>
							</div>
							<?
							if (count($assinaturaEmailCampos) == 1) { ?>
								<div style="padding: 5px;background:whitesmoke;">
									<table class="table">
										<tr>
											<td>
												Nome Assinatura:
												<input type="hidden" name="_ass1_u_assinaturaemailcampos_idassinaturaemailcampos" value="<?= $assinaturaEmailCampos[0]["idassinaturaemailcampos"] ?>">
												<input type="text" name="_ass1_u_assinaturaemailcampos_nome" value="<?= $assinaturaEmailCampos[0]["nome"] ?>">
											</td>
											<td colspan="2">
												Cargo:
												<input type="text" name="_ass1_u_assinaturaemailcampos_cargo" value="<?= $assinaturaEmailCampos[0]["cargo"] ?>">
											</td>
										</tr>
										<tr>
											<td>
												Telefone:
												<input type="text" name="_ass1_u_assinaturaemailcampos_telefone" value="<?= $assinaturaEmailCampos[0]["telefone"] ?>">
											</td>
											<td>
												Ramal:
												<input type="text" name="_ass1_u_assinaturaemailcampos_ramal" value="<?= $assinaturaEmailCampos[0]["ramal"] ?>">
											</td>
											<td>
												Celular:
												<input type="text" name="_ass1_u_assinaturaemailcampos_celular" value="<?= $assinaturaEmailCampos[0]["celular"] ?>">
											</td>
										</tr>
									</table>
								</div>
							<? } else if (count($assinaturaEmailCampos) > 1) { ?>
								<div style="padding: 5px;background:whitesmoke;">
									Mais de uma configuração de campos de assinatura para o grupo de email
								</div>
							<? } else { ?>
								<div style="padding: 5px;background:whitesmoke;">
									<i class="fa fa-plus-circle verde hovercinza btn-lg pointer" onclick="criarAssinaturaCampos()"></i>
								</div>
							<? } ?>
						</li>
					</ul>
					<? if (count($assinaturaEmailCampos) == 1) { ?>
						<hr>
						<ul class="nav">
							<li class="panel" style="background:#e6e6e6;border: 1px solid #ddd;">
								<div class="titulo_email pointer" data-toggle="collapse" href="#assinaturaprincipal">
									<a>Assinaturas de E-mail para <?= $_1_u_emailvirtualconf_email_original ?></a>
								</div>
								<div id="assinaturaprincipal" class="collapse">
									<div style="padding: 15px;background:whitesmoke;">
										<input id="outrasassinaturas" class="compacto" type="text" cbvalue placeholder="Selecione">
									</div>
									<?
									foreach ($webmailAssinatura as $assinatura) { ?>
										<div id="templates_email">
											<div class="titulo_email" style="padding: 6px 9px;">
												<?= $assinatura['descricao'] ?><i class="fa fa-trash cinzaclaro hoververmelho pointer fright" onclick="deletaidentidade([<?= $assinatura['removeids'] ?>])"></i>
											</div>
											<div style="padding: 20px;background:whitesmoke;">
												<?= $assinatura["htmlassinatura"] ?>
											</div>
										</div>
									<? } ?>
								</div>
							</li>
						</ul>
					<? } ?>
				</div>
			</div>
		</div>
	</div>
<? } ?>

<?
// -------------------------------------------------------------------------------------------------------------------------------- //

//																RODAPÉ																//

// -------------------------------------------------------------------------------------------------------------------------------- //
?>
<?
if (!empty($_1_u_emailvirtualconf_idemailvirtualconf)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_emailvirtualconf_idemailvirtualconf; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}

$tabaud = "emailvirtualconf"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';

require(__DIR__."/js/emailvirtualconf_js.php");

?>