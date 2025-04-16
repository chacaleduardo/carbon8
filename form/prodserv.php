<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/prodserv_controller.php");
require_once("../form/controllers/amostra_controller.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

$idsolcomitem = $_GET['idsolcomitem'];
if (!empty($_GET['nomeprod'])) {
	$_1_u_prodserv_descr = $_GET['nomeprod'];
}

/*
 * Este procedimento leva mais de 10 segundos para ser finalizado. Portanto será instanciado somente caso esta página seja chamada com parâmetros adequados
 */
if ($_GET["_atualizaarvoreinsumos"] == "Y") {
	//MAF: removido para background completamente
	//	armazenaConfiguracaoArvoreInsumos();
	die;
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "prodserv";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
	"idprodserv" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT p.*, u.flgpotencia
			FROM prodserv p LEFT JOIN unidadevolume u ON u.un = p.un
		   WHERE p.idprodserv = #pkid";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

require_once(__DIR__."/controllers/calculoestoque_controller.php");
require_once(__DIR__."/controllers/prativ_controller.php");
require_once(__DIR__."/controllers/prodserv_controller.php");
require_once(__DIR__."/controllers/nf_controller.php");

$i = 2;

if ($_1_u_prodserv_jsonconfig == '') $_1_u_prodserv_jsonconfig = '{}';

$atividadesRelacionadas = [];
$atividadesDisponiveisParaVinculo = [];
$listarDocsParaVinculo = [];
$jsonConfig = [];
$especieFinalidade = [];

if (!empty($_1_u_prodserv_idprodserv) and $_1_u_prodserv_tipo == 'SERVICO') {
	$nfItemServico = NfController::buscarDadosNfitemServico($_1_u_prodserv_idprodserv, $_1_u_prodserv_consumodiasgraf);
	$atividadesRelacionadas = ProdServController::buscarPrAtivPorIdObjetoTipoObjetoETipo($_1_u_prodserv_idprodserv, 'prodserv', 'SERVICO');

	$atividadesDisponiveisParaVinculo = PrativController::buscarAtividadesDisponivesParaVinculoEmServico($_1_u_prodserv_idprodserv, 'prodserv', 'SERVICO', cb::idempresa(), true);
}
if (!empty($row)) {
	$listarDocsParaVinculo = ProdServController::listarDocsParaVinculo();
	$listatagtipo = ProdServController::listarTagTipo()['array'];

	if ($_1_u_prodserv_jsonconfig && $_1_u_prodserv_jsonconfig != '{}') $jsonConfig = html_entity_decode($_1_u_prodserv_jsonconfig);

	if ($_1_u_prodserv_tipo == 'SERVICO') $especieFinalidade = AmostraController::buscarEspeciefinalidade(cb::idempresa());
}


$consome = ProdServController::verificaConsomeNaTransferencia($row);

$listaHistoricoDescr = ProdServController::buscarHistoricoDeAlteração($_1_u_prodserv_idprodserv, 'prodserv', 'descr');
$qtdhist = count($listaHistoricoDescr);

$listaHistoricoVlrVenda = [];

if ($_1_u_prodserv_idprodserv) $listaHistoricoVlrVenda = LoteController::buscarHistoricoDeAlteração($_1_u_prodserv_idprodserv, 'prodserv', 'vlrvenda');

if ($qtdhist > 0) { ?>
	<div id="hist_descr" style="display: none">
		<table class="table table-hover">
			<thead>
				<tr>
					<th scope="col">De</th>
					<th scope="col">Para</th>
					<th scope="col">Justificativa</th>
					<th scope="col">Por</th>
					<th scope="col">Em</th>
				</tr>
			</thead>
			<tbody>
				<? foreach ($listaHistoricoDescr as $historico) { ?>
					<tr>
						<? if ($historico['campo'] == "idunidadeest" || $historico['campo'] == "idunidadealerta") { ?>
							<td style="padding: 3px !important;"><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor_old']); ?></label></td>
							<td style="padding: 3px !important;"><label class=" alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor']); ?></label></td>
						<? } else { ?>
							<td style="padding: 3px !important;"><?=$historico['valor_old'] ?></td>
							<td style="padding: 3px	!important;"><?=$historico['valor'] ?></td>
						<? } ?>

						<td style="padding: 3px !important;"><? echo $historico['justificativa']; ?></td>
						<td style="padding: 3px !important;"><?=$historico['nomecurto'] ?></td>
						<td style="padding: 3px !important;"><?= dmahms($historico['criadoem']) ?></td>
					</tr>
				<?
				} ?>
			</tbody>
		</table>
	</div>
<? }

$listaHistoricoDescr = ProdServController::buscarHistoricoDeAlteração($_1_u_prodserv_idprodserv, 'prodserv', 'descrcurta');
$qtdhist = count($listaHistoricoDescr);
if ($qtdhist > 0) { ?>
	<div id=" hist_descrcurta" style="display: none">
		<table class="table table-hover">
			<thead>
				<tr>
					<th scope="col">De</th>
					<th scope="col">Para</th>
					<th scope="col">Justificativa</th>
					<th scope="col">Por</th>
					<th scope="col">Em</th>
				</tr>
			</thead>
			<tbody>
				<? foreach ($listaHistoricoDescr as $historico) { ?>
					<tr>
						<? if ($historico['campo'] == "idunidadeest" || $historico['campo'] == "idunidadealerta") { ?>
							<td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor_old']); ?></label></td>
							<td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor']); ?></label></td>
						<? } else { ?>
							<td><?=$historico['valor_old'] ?></td>
							<td><?=$historico['valor'] ?></td>
						<? } ?>

						<td><?
							echo $historico['justificativa'];
							?></td>
						<td><?=$historico['nomecurto'] ?></td>
						<td><?= dmahms($historico['criadoem']) ?></td>
					</tr>
				<?
				} ?>
			</tbody>
		</table>
	</div>
<? }

?>
<link href="../form/css/prodserv_css.css?_<?= date("dmYhms") ?>" rel="stylesheet">
<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table style="width:100%">
					<tr>
						<td align="right" style="width:5%;"><strong>Id:</strong></td>
						<td align="left" style="width:5%;"><strong><label class="alert-warning"><?=$_1_u_prodserv_idprodserv; ?></label></strong></td>
						<td align="right" style="width:5%;"><strong>Sigla:</strong></td>
						<td>
							<?
							if (empty($_1_u_prodserv_codprodserv)) {
							?>
								<input class="size10" name="_1_<?=$_acao?>_prodserv_codprodserv" type="text" value="<?=$_1_u_prodserv_codprodserv ?>" vnulo>
							<?
							} else {
							?>
								<label class="alert-warning"><?=$_1_u_prodserv_codprodserv ?></label>
							<?
							}
							?>
						</td>
						<input name="_1_<?=$_acao?>_prodserv_idprodserv" id="idprodserv" size="10" type="hidden" value="<?=$_1_u_prodserv_idprodserv ?>" vnulo>
						<? if (!empty($idsolcomitem)) { ?>
							<input name="idsolcomitem" id="idsolcomitem" type="hidden" value="<?=$idsolcomitem ?>">
						<? } ?>

						<?
						if (!empty($_1_u_prodserv_idprodserv) && !empty($_1_u_prodserv_descr)) {
							$disableddescr = 'disabled="disabled"';
							$backgrounddescr = 'background-color:#E0E0E0;';
						} ?>

						</td>
						<td align="right" colspan="2">Descrição:</td>
						<td><input name="_1_<?=$_acao?>_prodserv_descr" size="75" style="<?=$backgrounddescr ?>" type="text" value="<?= htmlspecialchars($_1_u_prodserv_descr) ?>" vnulo <?=$disableddescr ?>>
						</td>
						<? if (!empty($_1_u_prodserv_idprodserv)) { ?>
							<td>

								<i class="fa btn-sm fa-pencil preto pointer align-left" onclick="alteravalordescr('descr','<?= htmlspecialchars($_1_u_prodserv_descr) ?>','modulohistorico',<?=$_1_u_prodserv_idprodserv ?>,'Descrição:')"></i>
								<img src="/form/img/icon-hist.svg" class="pointer" alt="Icone histórico" onclick="modalhist('hist_descr')" />
							</td>
						<? } ?>
						<? // GVT - 13/04/2020 - A pedido de Daniel Rossi, retirar todos os lápis de edição na prodserv 
						?>
						<td align="right" colspan="3">Status:</td>
						<td>
							<select name="_1_<?=$_acao?>_prodserv_status">
								<? fillselect(ProdServController::$status, $_1_u_prodserv_status); ?>
							</select>
						</td>

					</tr>
					<tr>
						<td align="right" style="width:5%;"><strong>Tipo:</strong></td>
						<td align="left" colspan="2" style="width:5%;">
							<strong><label class="alert-warning"><?=$_1_u_prodserv_tipo; ?></label></strong>
							<?
							if (empty($_1_u_prodserv_tipo)) {
							?>
								<select name="_1_<?=$_acao?>_prodserv_tipo" id="tipo" vnulo>
									<option></option>
									<? fillselect(ProdServController::$tipoProdserv, $_1_u_prodserv_tipo); ?>
								</select>
							<?
							}
							?>
						</td>
						<?
						$disableddescricao = '';
						$backgrounddescricao = '';

						if (!empty($_1_u_prodserv_descrcurta)) {
							$disableddescrcurta = 'disabled="disabled"';
							$styledescrcurta = 'background-color:#E0E0E0;';
						}
						if ($_1_u_prodserv_tipo == 'PRODUTO') { ?>
							<td align="right" colspan="3">Descrição Curta (NFe):</td>
						<? } else { ?>
							<td align="right" colspan="3">Descrição Curta (NFs):</td>
						<? } ?>
						<td><input name="_1_<?=$_acao?>_prodserv_descrcurta" title="O máximo de caracteres para este campo é 120" maxlength="120" style="<?=$styledescrcurta ?>" size="75" type="text" <?=$disableddescrcurta ?>value="<?=$_1_u_prodserv_descrcurta ?>"></td>
						<td>
							<? if (!empty($_1_u_prodserv_idprodserv)) { ?>
								<i class="fa btn-sm fa-pencil preto pointer align-left" onclick="alteravalordescr('descrcurta','<?=$_1_u_prodserv_descrcurta ?>','modulohistorico',<?=$_1_u_prodserv_idprodserv ?>,'Descrição Curta:')"></i>
								<img src="/form/img/icon-hist.svg" class="pointer" alt="Icone histórico" onclick="modalhist('hist_descrcurta')" />
							<? } ?>
						</td>
						<td align="right" colspan="3"> Ordenação: </td>
						<td>
							<input name="_1_<?=$_acao?>_prodserv_ordenacao" class="size5" type="text" value="<?=$_1_u_prodserv_ordenacao ?>">
						</td>
					</tr>
				</table>
			</div>
			<div class="panel-body">
				<?
				$listarEmpresa = ProdServController::listarEmpresaVinculadaObjetoEmpresa($_1_u_prodserv_idprodserv, 'prodserv');
				$hiddenempresa = '';
				if (count($listarEmpresa) > 0) {
					$hiddenempresa = 'hidden';
				}

				if ($_1_u_prodserv_idprodserv) {
					?>
					<div class='row <?=$hiddenempresa ?>'>
						<div class="col-md-3">
							<table>
								<tr>
									<td>Empresa:</td>
									<td>
										<select name="prodserv_objempresa" onchange="inserirObjempresa(this)">
											<option value=""> Selecione para relacionar</option>
											<? fillselect(ProdServController::buscarEmpresaQueNaoExisteNaObjetoEmpresa('prodserv', $_1_u_prodserv_idprodserv)); ?>
										</select>
									</td>
								</tr>
							</table>
						</div>
					</div>
					<?
					foreach ($listarEmpresa as $_empresa) {
						if (!empty($_empresa['idobjempresa'])) {
							?>
							<hr <?=$hiddenempresa ?>>
							<span style="font-size: 10px;  font-weight: bold;  color: gray;">
								UNIDADES <?=$_empresa['empresa'] ?>
							</span>
							<div class="row">
								<?
								$listarUnidadesEmpresa = ProdServController::buscarUnidadesDisponiveisPorUnidadeObjeto($_1_u_prodserv_idprodserv, 'prodserv', "  AND u.requisicao = 'Y' AND u.idempresa = ".$_empresa['idempresa']);
								foreach ($listarUnidadesEmpresa as $unidadeEmpresa) {
									?>
									<div class="col-md-3" style="font-size:11px">
										<?
										if (!empty($unidadeEmpresa['idunidadeobjeto'])) {
											$spam = "font-weight: bolder;";
										?>
											<i class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="retirarUnidade(<?=$unidadeEmpresa['idunidadeobjeto'] ?>);" alt="Retirar Unidade"></i>
										<? } else {
											$spam = "font-stretch: normal;";
										?>
											<i class="fa fa-square-o fa-1x btn-lg pointer" onclick="inserirUnidade(<?=$unidadeEmpresa['idunidade'] ?>);" alt="Inserir Unidade"></i>
										<? }
										?>
										<span style="<?=$spam ?>">
											<?=$unidadeEmpresa['unidade'] ?>
										</span>
									</div>
									<?
								}
								?>
							</div>
							<?
						}
					}
					?>
					<hr>
					<span style="font-size: 10px;  font-weight: bold;  color: gray;">UNIDADES DE NEGÓCIO</span>
					<div class="row">
						<?
						$listarPlantel = ProdServController::listarPlantelPorIdobjetoTipoobjetoProdservAtiva($_1_u_prodserv_idprodserv, 'prodserv');
						foreach ($listarPlantel as $_plantel) {
							?>
							<div class="col-md-3" style="font-size:11px">
								<?
								if (!empty($_plantel['idplantelobjeto'])) {
								?>
									<i class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="retirarUnidadeNegocio(<?=$_plantel['idplantelobjeto'] ?>);" alt="Retirar Unidade de negócio"></i>
								<? } else { ?>
									<i class="fa fa-square-o fa-1x btn-lg pointer" onclick="inseirUnidadeNegocio(<?=$_plantel['idplantel'] ?>);" alt="Inserir Unidade de negócio"></i>
								<? }
								echo ($_plantel['plantel']);
								?>
							</div>
							<?
						}
						?>
					</div>
				<? } ?>
			</div>
		</div>
	</div>
</div>

<? if (!empty($_1_u_prodserv_idprodserv) && ($_1_u_prodserv_tipo == 'PRODUTO' || $_1_u_prodserv_comprado == 'Y' || $_1_u_prodserv_tipo == 'SERVICO')) {
	/**********************************************************************************************************************
	 *												|		CHECK BOX			|											*
	 *												|	(COMPRADO,FORMULADO)	|											*
	 *												V							V											*
	 ***********************************************************************************************************************/ ?>
	<div class="row">
		<div class="col-sm-6">
			<? if (!empty($_1_u_prodserv_idprodserv) and $_1_u_prodserv_tipo == 'SERVICO') { ?>
				<div class="panel panel-default">
					<div class="panel-heading" style="padding:1px 10px;">Perfil
						<?
						$ListarHistoricoModal = ProdServController::buscarHistoricoAlteracao($_1_u_prodserv_idprodserv, ['comissionado', 'comprado', 'venda']);
						$qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
						if ($qtdvh > 0) {
							?>
							<i title="Histórico de alteração" class="fa btn-lg fa-info-circle preto pointer hoverazul btnhistperfil" data-placement="right"></i>
							<div class="webui-popover-content top" id="webuiPopover1" data-placement="right">
								<br />
								<table class="table table-striped planilha">
									<?
									if ($qtdvh > 0) {	?>
										<thead>
											<tr>
												<th scope="col">Alterado para</th>
												<th scope="col">Por</th>
												<th scope="col">Em</th>
											</tr>
										</thead>
										<tbody>
											<?
											foreach ($ListarHistoricoModal as $historicoModal) {
											?>
												<tr>
													<td><?= strtoupper($historicoModal['campo']) ?></td>
													<td><?=$historicoModal['nomecurto'] ?></td>
													<td><?= dmahms($historicoModal['criadoem']) ?></td>
												</tr>
											<?
											}
											?>
										</tbody>
										<?
									}
									?>
								</table>
							</div>
							<?
						} else {
							echo '&nbsp;';
						}
						?>
					</div>

					<div class="panel-body">
						<div class="row mt-2">

							<!-- Comprado -->
							<div class="col-sm-4 text-left">
								<td>
									<? if ($_1_u_prodserv_comprado == "Y") { ?>
										<span style="font-weight: bolder;">
										<i class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="altcomprado('N');" alt="Alterar para Não"></i>
									<? } else { ?>
										<span>
										<i class="fa fa-square-o fa-1x btn-lg pointer" onclick="altcomprado('Y');" alt="Alterar para Sim"></i>
									<? } ?>
									Comprado
									</span>
								</td>
							</div>

							<!-- Venda -->
							<div class="col-sm-3 text-left">
								<td>
									<? if ($_1_u_prodserv_venda == "Y") { ?>
										<span style="font-weight: bolder;">
										<i class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="altvenda('N');" alt="Alterar para Não"></i>
									<? } else { ?>
										<span>
											<i class="fa fa-square-o fa-1x btn-lg pointer" onclick="altvenda('Y');" alt="Alterar para Sim"></i>
									<? } ?>
									Prestado
									</span>
								</td>
							</div>

							<!-- Comissionado -->
							<div class="col-sm-3 text-left">
								<td>
									<? if ($_1_u_prodserv_venda == 'Y') {
										$disabledcomissionado = 'disabled="disabled"';
									} ?>
									<? if ($_1_u_prodserv_comissionado == "Y") { ?>
										<span style="font-weight: bolder;">
										<i class="fa fa-check-square-o fa-1x btn-lg pointer" <?=$disabledcomissionado ?> onclick="altcomissionado('N');" alt="Alterar para Não"></i>
									<? } else { ?>
										<span>
										<i class="fa fa-square-o fa-1x btn-lg pointer" <?=$disabledcomissionado ?> onclick="altcomissionado('Y');" alt="Alterar para Sim"></i>
									<? } ?>
									Comissionado
									</span>
								</td>
							</div>
						</div>
					</div>
				</div>
			<? }

			if ($_1_u_prodserv_tipo == 'PRODUTO') { ?>
				<div class="panel panel-default">
					<div class="panel-heading" style="padding:1px 10px;">Perfil
						<? $ListarHistoricoModal = ProdServController::buscarHistoricoAlteracao($_1_u_prodserv_idprodserv, ['comissionado', 'comprado', 'fabricado']);
						$qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
						if ($qtdvh > 0) {
						?>
							<i title="Histórico de alteração" class="fa btn-lg fa-info-circle preto pointer hoverazul btnhistperfil" data-placement="right"></i>
							<div class="webui-popover-content top" id="webuiPopover1" data-placement="right">
								<br />
								<table class="table table-striped planilha">
									<?
									if ($qtdvh > 0) {
										?>
										<thead>
											<tr>
												<th scope="col">Alterado para</th>
												<th scope="col">Por</th>
												<th scope="col">Em</th>
											</tr>
										</thead>
										<tbody>
											<?
											foreach ($ListarHistoricoModal as $historicoModal) {
											?>
												<tr>
													<td><?= strtoupper($historicoModal['campo']) ?></td>
													<td><?=$historicoModal['nomecurto'] ?></td>
													<td><?= dmahms($historicoModal['criadoem']) ?></td>
												</tr>
											<?
											}
											?>
										</tbody>
										<?
									}
									?>
								</table>
							</div>
							<?
						} else {
							echo '&nbsp;';
						}
						?>
					</div>
					<div class="panel-body">
						<div class="row mt-2">
							<!-- Comprado -->
							<div class="col-sm-4 text-left">
								<label class="radio-inline" title="Flegar o perfil Comprado">
									<input required type="radio" name="configuracao_perfil1" id="configuracao_perfil1" value="comprado" onchange="alterarPerfilProduto(this)" <?= ($_1_u_prodserv_comprado == "Y") ? 'checked' : ''; ?>>
									<span style="font-size:11px;">Comprado</span>
								</label>
							</div>

							<!-- Formulado -->
							<div class="col-sm-4 text-left">
								<label class="radio-inline" title="Flegar o perfil Formulado">
									<input required type="radio" name="configuracao_perfil1" id="configuracao_perfil2" value="fabricado" onchange="alterarPerfilProduto(this)" <?= ($_1_u_prodserv_fabricado == "Y") ? 'checked' : ''; ?>>
									<span style="font-size:11px;">Formulado</span>
								</label>
							</div>
						</div>
						<hr>

						<div class="row" style="align-items: center;">
							<!-- NFe -->
							<div class="col-sm-6 text-left">
								<div class="col-sm-2 text-right">NFe</div>
								<div class="col-sm-10">									
									<? $disabledNfe = ($_acao == 'u' && !empty($_1_u_prodserv_nfe)) ? 'disabled="disabled"' : ''; ?>
									<select name="_1_<?= $_acao ?>_prodserv_nfe" class="size6 prodserv_nfe" <?= $disabledNfe ?> vnulo>
										<? fillselect(ProdServController::$CondicaoSimNaoVazio, $_1_u_prodserv_nfe); ?>
									</select>
									<i class="fa fa-pencil btn-lg pointer" title="Editar NFe" onclick="alterarValor('nfe', '<?= dma($_1_u_prodserv_nfe) ?>', 'modulohistorico', <?= $_1_u_prodserv_idprodserv ?>, 'NFe')"></i>
								</div>
							</div>

							<!-- Venda -->
							<div class="col-sm-6 text-left">
								<div class="col-sm-2 text-right">Venda</div>
								<div class="col-sm-10">									
									<? $disabledVenda = ($_acao == 'u' && !empty($_1_u_prodserv_venda)) ? 'disabled="disabled"' : ''; ?>
									<select name="_1_<?= $_acao ?>_prodserv_venda" class="size6 prodserv_venda" <?= $disabledVenda ?> vnulo>
										<? fillselect(ProdServController::$CondicaoSimNaoVazio, $_1_u_prodserv_venda); ?>
									</select>
									<i class="fa fa-pencil btn-lg pointer" title="Editar Venda" onclick="alterarValor('venda', '<?= dma($_1_u_prodserv_nfe) ?>', 'modulohistorico', <?= $_1_u_prodserv_idprodserv ?>, 'Venda')"></i>
								</div>
							</div>

							<!-- Certificado -->
							<div class="col-sm-6 text-left">
								<div class="col-sm-2 text-right">Certificado</div>
								<div class="col-sm-10">									
									<? $disabledCertificado = ($_acao == 'u' && !empty($_1_u_prodserv_certificado)) ? 'disabled="disabled"' : ''; ?>
									<select name="_1_<?= $_acao ?>_prodserv_certificado" class="size6 prodserv_certificado" style="height: 25px; margin-left: 5px;" <?= $disabledVenda ?> vnulo>
										<? fillselect(ProdServController::$CondicaoSimNaoVazio, $_1_u_prodserv_certificado); ?>
									</select>
									<i class="fa fa-pencil btn-lg pointer" title="Editar Certificado" onclick="alterarValor('certificado', '<?= dma($_1_u_prodserv_nfe) ?>', 'modulohistorico', <?= $_1_u_prodserv_idprodserv ?>, 'Certificado')"></i>															
								</div>
							</div>
							
							<div class="col-sm-6 text-left">
								<td>
									<?
									$titlecomissionado = "Alterar para Sim";
									if($_1_u_prodserv_venda == 'N'){$disabledcomissionado = 'disabled="disabled"'; $titlecomissionado="Habilitar VENDA SIM para marcar essa opção";}?>
									<? if ($_1_u_prodserv_comissionado == "Y") { ?>
										<span style="font-weight: bolder; padding: 5px 8px;">
										<i class="fa fa-check-square-o fa-1x btn-lg pointer p-0" title="Alterar comissionado para não"  onclick="altcomissionado('N');" alt="Alterar para Não"></i>
									<? } else { ?>
										<span style="padding: 5px 8px;">
										<i class="fa fa-square-o fa-1x btn-lg input pointer p-0" title="<?=$titlecomissionado?>" onclick="altcomissionado('Y', '<?= $_1_u_prodserv_venda?>');" alt="Alterar para Sim" <?= $disabledcomissionado?>></i>
									<? } ?>								
									Comissionado
									</span>
								</td>
							</div>

							<div id="modulohistorico" style="display: none">
								<table class="table table-hover">
									<tr>
										<td>#namerotulo:</td>
										<td>
											<input name="#name_idobjeto" value="<?= $_1_u_prodserv_idprodserv ?>" type="hidden">
											<input name="#name_auditcampo" value="" type="hidden">
											<input name="#name_tipoobjeto" value="prodserv" type="hidden">
											<input name="#name_valorold" value="#valor_campo_old" type="hidden">
											<select name="#name_campo" vnulo class="size8">
												<? fillselect(ProdServController::$CondicaoSimNaoVazio); ?>
											</select>
										</td>
									</tr>
									<tr>
										<td>Justificativa:</td>
										<td>
											<select name="#name_justificativa" vnulo class="size50">
												<? fillselect(ProdServController::$_justificativa); ?>
											</select>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>

				<div class="panel panel-default my-4">
					<div class="panel-heading" style="padding:1px 10px;">Tipo do produto
						<?
						$ListarHistoricoModal = ProdServController::buscarHistoricoAlteracao($_1_u_prodserv_idprodserv, ['material', 'insumo', 'imobilizado', 'produtoacabado']);
						$qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
						if ($qtdvh > 0) {
						?>
							<i title="Histórico de alteração" class="fa btn-lg fa-info-circle preto pointer hoverazul historicoUn"></i>
							<div class="webui-popover-content top" id="webuiPopover1">
								<br />
								<table class="table table-striped planilha">
									<?
									if ($qtdvh > 0) {
										?>
										<thead>
											<tr>
												<th scope="col">Alterado para</th>
												<th scope="col">Por</th>
												<th scope="col">Em</th>
											</tr>
										</thead>
										<tbody>
											<?
											foreach ($ListarHistoricoModal as $historicoModal) {
												?>
												<tr>
													<td><?= strtoupper($historicoModal['campo']) ?></td>
													<td><?=$historicoModal['nomecurto'] ?></td>
													<td><?= dmahms($historicoModal['criadoem']) ?></td>
												</tr>
												<?
											}
											?>
										</tbody>
										<?
									}
									?>
								</table>
							</div>
							<?
						} else {
							echo '&nbsp;';
						}
						?>
					</div>
					<div class="panel-body" style="text-align: center;font-size:11px; padding-top:15px;">
						<div class="col-sm-11 mt-2">
							<div class="row">
								<script>
									oldVar = '<?= ProdServController::configuracaoTipoProduto(['material' => $row['material'], 'insumo' => $row['insumo'], 'imobilizado' => $row['imobilizado']]); ?>'
								</script>
								<!-- Insumo -->
								<div class="col-sm-6 text-left">
									<label class="radio-inline" title="Não consome na transferência">
										<input required type="radio" name="configuracao_produto" id="conficuracao_produto2"
											value="insumo" onchange="altConfiguracaoProduto(this, oldVar)" <?= ($_1_u_prodserv_insumo == "Y") ? 'checked' : ''; ?>>
										<span style="font-size:11px;">Insumo</span>
									</label>
								</div>

								<!-- Material -->
								<div class="col-sm-6 text-left">
									<label class="radio-inline" title="Consome na transferência">
										<input required type="radio" name="configuracao_produto" id="conficuracao_produto1"
											value="material" onchange="altConfiguracaoProduto(this, oldVar)" <?= ($_1_u_prodserv_material == "Y") ? 'checked' : ''; ?>>
										<span style="font-size:11px;">Material</span>
									</label>
								</div>

								<!-- Imobilizado -->
								<div class="col-sm-6 text-left">
									<label class="radio-inline" title="Não consome na transferência">
										<input required type="radio" name="configuracao_produto" id="conficuracao_produto3" value="imobilizado" onchange="altConfiguracaoProduto(this, oldVar)" <?= ($_1_u_prodserv_imobilizado == "Y") ? 'checked' : ''; ?>>
										<span style="font-size:11px;">Imobilizado</span>
									</label>
								</div>

								<!-- Produto acabado -->
								<div class="col-sm-6 text-left">
									<label class="radio-inline" title="Não consome na transferência">
										<input required type="radio" name="configuracao_produto" id="conficuracao_produto4" value="produtoacabado" onchange="altConfiguracaoProduto(this, oldVar)" <?= ($_1_u_prodserv_produtoacabado == "Y") ? 'checked' : ''; ?>>
										<span style="font-size:11px;">Produto Acabado</span>
									</label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?
				/**********************************************************************************************************************
				 *												|										|								*
				 *												|	Informações Sobre o Produto			|								*
				 *												V										V								*
				 ***********************************************************************************************************************/ ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<? if ($_1_u_prodserv_tipo == 'PRODUTO') { ?>Informações Sobre o Produto<? } else { ?>Informações Sobre o Serviço<? } ?>
					</div>
					<div id="prodInfo" class="panel-body">
						<table>
							<tr>
								<td align="left" style="width: 12%;">Qtd Padrão:</td>
								<td>
									<input class="size5" type="text" name="_1_<?=$_acao?>_prodserv_qtdpadrao" value="<?= recuperaExpoente($_1_u_prodserv_qtdpadrao, $_1_u_prodserv_qtdpadrao_exp) ?>">
								</td>
								<?
								if (empty($_1_u_prodserv_un)) {
									?>
									<td align="right">Unidade Padrão:</td>
									<td>
										<select class="size15" <?=$desabledun ?> id="unpadrao" name="_1_<?=$_acao?>_prodserv_un" vnulo>
											<option value=""></option>
											<? fillselect(ProdServController::buscarUnidadeVolume(), $_1_u_prodserv_un); ?>
										</select>
									<?
								} else {
									?>
									<td align="right">Unidade Padrão:</td>
									<td>
										<select class="size15" disabled='disabled' id="unpadrao" name="_1_<?=$_acao?>_prodserv_un" vnulo>
											<option value=""></option>
											<? fillselect(ProdServController::buscarUnidadeVolume(), $_1_u_prodserv_un); ?>
										</select>
										<?
										$rowqtdlote = ProdServController::buscarQuantidadeLotePorProduto($_1_u_prodserv_idprodserv);
										if ($rowqtdlote['qtd'] == '0') {
											?>
											<i class="fa fa-pencil btn-lg pointer" title='Editar Unidade' onclick="alteravalor('un','<?=$_1_u_prodserv_un ?>','modulohistorico',<?=$_1_u_prodserv_idprodserv ?>,'Unidade Padrão:')"></i>
										<?
										} else {
											?>
											<i class="fa fa-pencil btn-lg pointer" title='Editar Unidade' onclick="alert('Este produto já possui lote não é possível editar a unidade.')"></i>
										<?
										}
										
										$ListarHistoricoModal = ProdServController::buscarHistoricoAlteracao($_1_u_prodserv_idprodserv, ['un']);
										$qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
										if ($qtdvh > 0) {
											?>
											<img src="/form/img/icon-hist.svg" class="pointer" alt="Icone histórico" title="Histórico do Envio" data-target="webuiPopover0" onclick="modalhist('hist_unidadepadrao')" />
											<div class="webui-popover-content" id="hist_unidadepadrao">
												<br />
												<table class="table table-striped planilha">
													<?
													if ($qtdvh > 0) {
													?>
														<thead>
															<tr>
																<th scope="col">De</th>
																<th scope="col">Para</th>
																<th scope="col">Justificativa</th>
																<th scope="col">Por</th>
																<th scope="col">Em</th>
															</tr>
														</thead>
														<tbody>
															<?
															foreach ($ListarHistoricoModal as $historicoModal) {
																?>
																<tr>
																	<td><?=$historicoModal['valor_old'] ?></td>
																	<td><?=$historicoModal['valor'] ?></td>
																	<td>
																		<?
																		if ($historicoModal['justificativa'] == 'ERRO NO CADASTRO') echo 'Erro no cadastro';
																		else echo $historicoModal['justificativa'];
																		?>
																	</td>
																	<td><?=$historicoModal['nomecurto'] ?></td>
																	<td><?= dmahms($historicoModal['criadoem']) ?></td>
																</tr>
																<?
															}
															?>
														</tbody>
														<?
													}
													?>
												</table>
											</div>
											<?
										} else {
											echo '&nbsp;';
										}
										?>
					</div>
				<? } ?>
				</div>
				</td>
				</tr>
				<tr>
					<td align="left">Volume Padrão:</td>
					<td>
						<input name="_1_<?=$_acao?>_prodserv_volumeprod" value="<?=$_1_u_prodserv_volumeprod ?>" class="form-inline size5">
					</td>

					<td align="right">Unidade Volume Padrão:</td>
					<td>
						<select name="_1_<?=$_acao?>_prodserv_unvolume" class="size15">
							<option value=""></option>
							<? fillselect(ProdServController::buscarUnidadeVolume(), $_1_u_prodserv_unvolume); ?>
						</select>
					</td>
					<td></td>
				</tr>
				<tr>
					<td align="left">Validade(M*):</td>
					<td>
						<input class="size5" name="_1_<?=$_acao?>_prodserv_validade" type="text" value="<?=$_1_u_prodserv_validade ?>">
					</td>
					<td align="right">Cobrar Validade:</td>
					<td>
						<select name="_1_<?=$_acao?>_prodserv_validadeforn" class="size15">
							<? fillselect(ProdServController::$CondicaoSimNaoVazio, $_1_u_prodserv_validadeforn); ?>
						</select>
					</td>
					<td></td>
				</tr>
				<tr>
					<td align="left">Licença:</td>
					<td>
						<? if ($_1_u_prodserv_licenca == "Y") { ?>
							<i class="fa fa-check-square-o fa-1x btn-lg pointer" alt="Alterar para Não" onclick="altlicenca('N');"></i>
						<? } else { ?>
							<i class="fa fa-square-o fa-1x btn-lg pointer" alt="Alterar para Sim" onclick="altlicenca('Y');"></i>
						<? } ?>
					</td>
					<td align="right">Consome na transferência:</td>
					<td>
						<? if ($_1_u_prodserv_material == 'Y' && $_1_u_prodserv_consometransf == 'Y') { ?>
							<i class="fa fa-check-square-o fa-1x btn-lg pointer paddingRightZero" alt="Alterar para Não" onclick="altctransf('N');"></i>
						<? } elseif ($_1_u_prodserv_material == 'Y') { ?>
							<i class="fa fa-square-o fa-1x btn-lg pointer paddingRightZero" alt="Alterar para Sim" onclick="altctransf('Y');"></i>
						<? } else { ?>
							<i class="fa fa-square-o fa-1x btn-lg pointer disabled readonly" style="cursor: no-drop;"></i>
						<? } ?>
					</td>
				</tr>
				<tr>
					<td align="left">EPI:</td>
					<td>
						<? if ($_1_u_prodserv_epi == "Y") { ?>
							<i class="fa fa-check-square-o fa-1x btn-lg pointer" alt="Alterar para Não" onclick="altEpi('N');"></i>
						<? } else { ?>
							<i class="fa fa-square-o fa-1x btn-lg pointer" alt="Alterar para Sim" onclick="altEpi('Y');"></i>
						<? } ?>
					</td>

					<td align="right">Retornar estoque Lote Reprovado:</td>
					<td>
						<? if ($_1_u_prodserv_retornarest == "Y") { ?>
							<i class="fa fa-check-square-o fa-1x btn-lg pointer" alt="Alterar para Não" onclick="altEst('N');"></i>
						<? } else { ?>
							<i class="fa fa-square-o fa-1x btn-lg pointer" alt="Alterar para Sim" onclick="altEst('Y');"></i>
						<? } ?>
					</td>

				</tr>
				<? if ($_1_u_prodserv_tipo == 'PRODUTO') { ?>
					<tr>
						<td align="left">Gera Lote Automático:</td>
						<td>
							<? if ($_1_u_prodserv_geraloteautomatico == "Y") { ?>
								<i class="fa fa-check-square-o fa-1x btn-lg pointer" alt="Alterar para Não" onclick="altLoteAut('N');"></i>
							<? } else { ?>
								<i class="fa fa-square-o fa-1x btn-lg pointer" alt="Alterar para Sim" onclick="altLoteAut('Y');"></i>
							<? } ?>
						</td>
					</tr>
				<? } ?>
				<tr hidden>
					<td align="right">Alerta Venc.(D*)</td>
					<td>
						<input name="_1_<?=$_acao?>_prodserv_alertavenc" type="text" value="<?=$_1_u_prodserv_alertavenc ?>">
					</td>
					<td></td>
				</tr>
				</table>
				<hr>
				<table style="width: 100%;">
					<tr>
						<td style="width: 15%;">Categoria:</td>
						<td>
							<?
							$contaItem = ProdServController::buscarContaItemProdservContaItem($_1_u_prodserv_idprodserv);
							$qtdcontaitem = count($contaItem);
							if ($qtdcontaitem < 1) {
							?>
								<input value="<?=$contaItem['idcontaitem'] ?>" type="hidden" id="grupoesant">
								<input value="<?=$contaItem['idprodservcontaitem'] ?>" type="hidden" id="idprodservcontaitem">
								<input name="_grupoes_" cbvalue="<?=$contaItem['idcontaitem'] ?>" value="<?=$contaItem['contaitem'] ?>" type="text" id="grupoes">
							<? } else {
								echo $contaItem['contaitem'];
								?>
								<input name="_grupoes_" cbvalue="<?=$contaItem['idcontaitem'] ?>" value="<?=$contaItem['contaitem'] ?>" type="hidden" id="grupoes">
								<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" style="float:right" title="Excluir Vinculo" onclick="excluiritem('prodservcontaitem', <?=$contaItem['idprodservcontaitem'] ?>)"></i>
							<? }
							?>
						</td>
					</tr>
					<tr>
						<td style="width: 15%;">
							Subcategoria:
						</td>
						<td>
							<?
							if ($contaItem['idcontaitem']) {
								$sqlit = ProdServController::listarContaItemTipoProdservTipoProdServ($contaItem['idcontaitem']);
							} else {
								$sqlit = '';
							}
							?>
							<input type="hidden" id="_idtipoprodserv_" value="<?=$_1_u_prodserv_idtipoprodserv ?>">
							<select id="autoidtipoitem" onchange="alteratipo(this)" name="_1_<?=$_acao?>_prodserv_idtipoprodserv">
								<option></option>
								<? fillselect($sqlit, $_1_u_prodserv_idtipoprodserv) ?>
							</select>
						</td>
					</tr>
					<!-- Tipo Agente: -->
					<? if ($_1_u_prodserv_fabricado == "Y" && $_1_u_prodserv_idempresa == 2) { ?>
						<tr>
							<td style="width: 15%;">
								Tipo Agente:
							</td>
							<td>
								<select name="_1_<?=$_acao?>_prodserv_tipoagente">
									<? fillselect(ProdServController::$tipoAgente, $_1_u_prodserv_tipoagente) ?>
								</select>
							</td>
						</tr>
					<? }
					$disabledindicacao = 'disabled="disabled"';
					$titleidicacao = 'Para selecionar uma opção, o campo acima Autógena tem que estar habilitado';
					if ($_1_u_prodserv_especial == 'Y') {
						$disabledindicacao = '';
						$titleidicacao = '';
						?>
						<tr>
							<td align="left" style="width: 15%;">Autógena:</td>
							<td>
								<i class="fa fa-star fa-1x laranja  btn-lg pointer" onclick="alerarEspecial('N');" title="Alterar Autógena para Não"></i>
								<label class="autogenalabel alert-warning">AUTÓGENA</label>
					<? } else {
							$hidenautogena = 'hidden';
							?>
						<tr>
							<td align="left" style="width: 15%;">Autógena:</td>
							<td>
								<i class="fa fa-star fa-1x cinzaclaro btn-lg pointer" onclick="alerarEspecial('Y');" title="Alterar Autógena para Sim"></i>
							</td>
						</tr>
					<? } ?>
					</td>
					</tr>
					<tr>
						<td align="left" style="width:15%;">Indicação de Uso:</td>
						<td>
							<select name="_1_<?=$_acao?>_prodserv_idindicacaouso" <?=$disabledindicacao ?> title="<?=$titleidicacao ?>">
								<option></option>
								<? fillselect(ProdServController::buscarInidicacaoUso(), $_1_u_prodserv_idindicacaouso); ?>
							</select>
						</td>
					</tr>
				</table>
		</div>
	</div>
<? } ?>
</div>
<div class="col-sm-6">
	<?
	/**********************************************************************************************************************
	 *												|							|											*
	 *												|	ESTOQUE ALMOXARIFADO	|											*
	 *												V							V											*
	 ***********************************************************************************************************************/

	if (!empty($_1_u_prodserv_idprodserv) && $_1_u_prodserv_tipo == 'PRODUTO') {
		?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<a class="calculoestoquedomodal point">Gerenciamento de Estoque</a>
			</div>
		</div>
		<?
		if (array_key_exists("fichatecnicaprodserv", getModsUsr("MODULOS"))) {
			/**********************************************************************************************************************
			 *												|							|											*
			 *												|	Ficha de Estoque		|											*
			 *												V							V											*
			 ***********************************************************************************************************************/

			?>
			<div class="panel panel-default my-4">
				<div class="panel-heading">
					<a class="fichaestoquemodal point">Ficha Kardex</a>
				</div>
			</div>
		<?
		}
	}

	/*DADOS FINANCEIROS CALCULOESTOQUE

			/**********************************************************************************************************************
			*												|							|											*
			*												|  Planejamento de Consumo	|											*
			*												V							V											*
			***********************************************************************************************************************/

	if ($_1_u_prodserv_comprado == "Y") { // mostra forcast somente para produtos de venda
		?>
		<div class="panel panel-default my-4">
			<div class="panel-heading"><a id="modalplanejamento" class="point">Planejamento de Consumo</a></div>
		</div>
	<?
	}

	/**********************************************************************************************************************
	 *												|							|											*
	 *												|		FORNECEDOR			|											*
	 *												V							V											*
	 ***********************************************************************************************************************/

	if ($_1_u_prodserv_comprado == "Y" or $_1_u_prodserv_fabricado == "Y") {
	?>
		<div class="panel panel-default my-4">
			<div class="panel-heading"><a id="modalfornecedor" class="point">Fornecedor</a></div>
		</div>
	<?
	}

	/**********************************************************************************************************************
	 *												|							|											*
	 *												|		Dados Financeiros	|											*
	 *												V							V											*
	 ***********************************************************************************************************************/

	if ($_1_u_prodserv_venda == "Y" or $_1_u_prodserv_comprado == "Y") {
	?>
		<div class="panel panel-default my-4">
			<div class="panel-heading"><a id="modalfinanceiro" class="point">Dados Financeiros</a></div>
			<div id="financInfo" style="display: none;">
				<div class="panel panel-default">
					<div class="panel-body">
						<table>
							<input name="_1p_<?=$_acao?>_prodserv_idprodserv" type="hidden" value="<?=$_1_u_prodserv_idprodserv ?>">
							<? if ($_1_u_prodserv_comissionado == 'Y' and $_1_u_prodserv_fabricado == 'N') { ?>
								<tr>
									<td align="right">Comissão:</td>
									<td>
										<input name="_1p_<?=$_acao?>_prodserv_comissao" id="comissao" type="text" value="<?=$_1_u_prodserv_comissao ?>" vdecimal>
									</td>
								</tr>
							<? } ?>
							<tr>
								<? if ($_1_u_prodserv_fabricado == 'N') { ?>

									<td align="right">Vlr. Venda:</td>
									<td><input name="_1p_<?=$_acao?>_prodserv_vlrvenda" id="vlrvenda" type="text" value="<?=$_1_u_prodserv_vlrvenda ?>" vdecimal></td>

								<? } ?>
								<td align="right" class="nowrap">Desoneração:</td>
								<td>
									<select name="_1p_<?=$_acao?>_prodserv_desoneracao">
										<option value=""></option>
										<? fillselect(ProdServController::$CondicaoYesNo, $_1_u_prodserv_desoneracao); ?>
									</select>
								</td>
							</tr>
							<tr>
								<td align="right">Finalidade</td>
								<td>
									<select name="_1p_<?=$_acao?>_prodserv_finalidade">
										<option value=""></option>
										<? fillselect(ProdServController::$finalidade, $_1_u_prodserv_finalidade); ?>
									</select>
								</td>
								<td align="right" class="nowrap">Isento Mesma UF:</td>
								<td>
									<select name="_1p_<?=$_acao?>_prodserv_isentomesmauf">
										<option value=""></option>
										<? fillselect(ProdServController::$CondicaoYesNo, $_1_u_prodserv_isentomesmauf); ?>
									</select>
								</td>
							</tr>
							<tr>
								<td align="right">Vlr. Compra:</td>
								<td>
									<input name="_1p_<?=$_acao?>_prodserv_vlrcompra" size="10" type="text" value="<?=$_1_u_prodserv_vlrcompra ?>" vdecimal>
								</td>
								<td align="right">Origem:</td>
								<td>
									<select name="_1p_<?=$_acao?>_prodserv_origem">
										<? fillselect(ProdServController::$origem, $_1_u_prodserv_origem); ?>
									</select>
								</td>
							</tr>
							<tr>
								<td align="right">Vlr. Un. Comercial</td>
								<td>
									<input name="_1p_<?=$_acao?>_prodserv_uncom" size="10" type="text" value="<?=$_1_u_prodserv_uncom ?>" vdecimal>
								</td>
								<td align="right">CST:</td>
								<td align="left">
									<select name="_1p_<?=$_acao?>_prodserv_cst">
										<option></option>
										<? fillselect(ProdServController::$cst, $_1_u_prodserv_cst); ?>
									</select>
								</td>
							</tr>
							<tr>
								<td align="right">BC.%:</td>
								<td><input name="_1p_<?=$_acao?>_prodserv_reducaobc" type="text" size="3" value="<?=$_1_u_prodserv_reducaobc ?>"></td>
								<td align="right">Mod-BC: </td>
								<td align="left">
									<select name="_1p_<?=$_acao?>_prodserv_modbc">
										<? fillselect(ProdServController::$modbc, $_1_u_prodserv_modbc); ?>
									</select>
								</td>

							</tr>
							<tr>
								<td align="right">PIS%:</td>
								<td><input name="_1p_<?=$_acao?>_prodserv_pis" type="text" size="4" value="<?=$_1_u_prodserv_pis ?>"></td>
								<td align="right">CST PIS:</td>
								<td>
									<select name="_1p_<?=$_acao?>_prodserv_piscst">
										<? fillselect(ProdServController::$pisConfins, $_1_u_prodserv_piscst); ?>
									</select>
								</td>
							</tr>
							<tr>
								<td align="right">Cofins%:</td>
								<td><input name="_1p_<?=$_acao?>_prodserv_cofins" type="text" size="4" value="<?=$_1_u_prodserv_cofins ?>"></td>
								<td align="right">CST COFINS:</td>
								<td>
									<select name="_1p_<?=$_acao?>_prodserv_confinscst">
										<? fillselect(ProdServController::$pisConfins, $_1_u_prodserv_confinscst); ?>
									</select>
								</td>
							</tr>
							<tr>
								<td align="right">IPI%:</td>
								<td><input name="_1p_<?=$_acao?>_prodserv_ipi" type="text" size="4" value="<?=$_1_u_prodserv_ipi ?>"></td>
								<td align="right">CST IPI:</td>
								<td>
									<select name="_1p_<?=$_acao?>_prodserv_ipint">
										<? fillselect(ProdServController::$ipi, $_1_u_prodserv_ipint); ?>
									</select>
								</td>
							</tr>
							<tr>
								<td align="right">NCM:</td>
								<td><input name="_1p_<?=$_acao?>_prodserv_ncm" type="text" size="10" value="<?=$_1_u_prodserv_ncm ?>"></td>
								<td align="right">ISS:</td>
								<td><input name="_1p_<?=$_acao?>_prodserv_iss" type="text" size="4" value="<?=$_1_u_prodserv_iss ?>"></td>
							</tr>
							<tr>
								<td align="right">CEST:</td>
								<td><input name="_1p_<?=$_acao?>_prodserv_cest" type="text" size="10" value="<?=$_1_u_prodserv_cest ?>"></td>
							</tr>
						</table>

						<?
						if ($_1_u_prodserv_fabricado == 'Y') {
							$arrFormulas = ProdServController::listarProdservFormulaPlantel($_1_u_prodserv_idprodserv);
							$result = count($arrFormulas);
							if ($result > 0) {
								?>
								<hr>
								<table class="table-striped planilha" style="width: 100%;">
									<tr>
										<th>Fórmula</th>
										<th>Valor de Venda (R$)</th>
										<th>Comissão (%)</th>
									</tr>
									<?
									$if = 0;
									$ifi = 0;
									foreach ($arrFormulas as $_idprodservformula => $form) {
										$if++;
										if ($form["status"] == "ATIVO") {
											?>
											<tr>
												<td><? echo ($form['rotulo'].' - '.$form['dose'].' Doses '.$form['volumeformula'].' '.$form['un']); ?>
													<input type="hidden" name="_psf<?=$if ?>_u_prodservformula_idprodservformula" value="<?=$_idprodservformula ?>">
												</td>
												<td><input type="text" name="_psf<?=$if ?>_u_prodservformula_vlrvenda" class="form-control size7" value="<?=$form["vlrvenda"] ?>" onchange="CB.post()"></td>
												<td><input type="text" name="_psf<?=$if ?>_u_prodservformula_comissao" class="form-control size7" value="<?=$form["comissao"] ?>" onchange="CB.post()"></td>
											</tr>
											<?
										}
									} //foreach ($arrFormulas as $idprodservformula => $form){
									?>
								</table>
							<?
							}
						} //if($_1_u_prodserv_fabricado=='Y'){
						?>
					
					<hr>
					</div>
				</div>
			</div>
		</div>
	<?
	} //	if($_1_u_prodserv_venda=="Y")
	/**********************************************************************************************************************
	 *												|							|											*
	 *												|	Fórmulas e Processos	|											*
	 *												V							V											*
	 ***********************************************************************************************************************/
	if ($_1_u_prodserv_fabricado == "Y") { ?>
		<div class="panel panel-default my-4">
			<div class="panel-heading">
				<a class="formuladomodal point">Fórmulas e Processos</a>
				<a href="report/prodtree.php?idprodserv=<?=$_1_u_prodserv_idprodserv ?>" class="floatright" target="_blank" title="Navegar pela árvore de insumos"><i class="fa fa-sitemap"></i></a>
			</div>
		</div>
	<? }

	/**********************************************************************************************************************
	 *												|							|											*
	 *												|	Certificado de Análise	|											*
	 *												V							V											*
	 ***********************************************************************************************************************/
	if (($_1_u_prodserv_venda == "Y" or $_1_u_prodserv_comprado == "Y" or $_1_u_prodserv_fabricado == "Y") and ($_1_u_prodserv_tipo == 'PRODUTO')) { ?>
		<div class="panel panel-default my-4">
			<div class="panel-heading"><a id="modalcertificado" class="point">Certificado de Análise</a></div>
		</div>
		<div class="panel panel-default" hidden>
			<div id="certanalise" class="panel-body">
				<table>
					<tr>
						<td align="right">Certificado de Análise:</td>
						<td>
							<select name="_1_<?=$_acao?>_prodserv_tipocertanalise">
								<? fillselect(ProdServController::$tipocertanalise, $_1_u_prodserv_tipocertanalise); ?>
							</select>
						</td>
						<td align="right">Requer Assinatura?</td>
						<td>
							<select name="_1_<?=$_acao?>_prodserv_assinatura">
								<? fillselect(ProdServController::$CondicaoSimNao, $_1_u_prodserv_assinatura); ?>
							</select>
						</td>
						<td align="right">Imprime Imagens do Ensaio?</td>
						<td>
							<select name="_1_<?=$_acao?>_prodserv_imagemcert">
								<? fillselect(array_reverse(ProdServController::$CondicaoYesNo), $_1_u_prodserv_imagemcert); ?>
							</select>
						</td>
					</tr>
				</table>
				<?
				$listarAnaliseQst = ProdServController::buscarAnaliseQst($_1_u_prodserv_idprodserv);
				$qtdrows1 = $listarAnaliseQst['qtdLinhas'];
				?>
				<table class="table table-striped planilha">
					<tr>
						<th style="width:5%;">#</th>
						<th style="width:40%;">Teste</th>
						<th>Especificações</th>
						<th></th>
						<th></th>
					</tr>
					<?
					foreach ($listarAnaliseQst['dados'] as $_analise) {
						$i++;
						?>
						<tr>
							<td style="vertical-align: top;"><input name="_<?=$i ?>_u_analiseqst_ordem" size="1" type="text" value="<?=$_analise["ordem"] ?>"></td>
							<td title="<?=$_analise['qst'] ?>">
								<input name="_<?=$i ?>_u_analiseqst_qst" size="50" type="text" value="<?=$_analise["qst"] ?>"><br>
								<?
								$listarAnaliseTeste = ProdServController::buscarAnaliseTestePorIdAnaliseQst($_analise['idanaliseqst']);
								$qtdt = $listarAnaliseTeste['qtdLinhas'];
								if ($qtdt > 0) {
									$c = 0;
									foreach ($listarAnaliseTeste['dados'] as $analiseTeste) {
										?>
										<div id="analiseteste<?=$analiseTeste["idanaliseteste"] ?>">
											<input name="_x<?=$i.$c ?>_u_analiseteste_idanaliseteste" size="6" type="hidden" value="<?=$analiseTeste["idanaliseteste"] ?>">
											<select style="width: 85%;;margin-left: 2%;background-color: #faebcc;" onchange="updanaliset(this,<?=$analiseTeste['idanaliseteste'] ?>)" name="_x<?=$i.$c ?>_u_analiseteste_idprodserv">
												<option value=""></option>
												<? fillselect(ProdServController::buscarIdTipoTestePorTipoEIdTipoUnidade(7, 'SERVICO'), $analiseTeste['idprodserv']); ?>
											</select>
											<i align="right" title='Excluir analise' class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="delanaliseteste(<?=$analiseTeste['idanaliseteste'] ?>)" alt="Excluir!"></i>
										</div>
										<?
										$c++;
									}
								} ?>
								<div align="Center">
									<? if ($_1_u_prodserv_fabricado == "Y") { ?>
										<i style="align-self: center;" class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" onclick="insanaliseteste(<?=$_analise['idanaliseqst'] ?>)" alt="Adicionar!"></i>
									<? } ?>
								</div>
							</td>
							<td align="center" style="vertical-align: top;">
								<input name="_<?=$i ?>_u_analiseqst_idanaliseqst" size="6" type="hidden" value="<?=$_analise["idanaliseqst"] ?>">
								<input name="_<?=$i ?>_u_analiseqst_especificacao" size="30" type="text" value="<?=$_analise["especificacao"] ?>">
							</td>
							<td align="center" title='Excluir tipo teste' style="vertical-align: top;">
								<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="inativaobjeto(<?=$_analise["idanaliseqst"] ?>,'analiseqst')" alt="Excluir!"></i>
							</td>
						</tr>
					<?
					}
					?>
					<tr>
						<td colspan="5">
							<i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" onclick="novoobjeto('analiseqst')" alt="Inserir teste !"></i>
						</td>
					</tr>
				</table>

				<table>
					<tr> Informações do Produto</tr>
					<tr>
						<td>
							<textarea name="_1_<?=$_acao?>_prodserv_infprod" cols="95" rows="4"><?=$_1_u_prodserv_infprod ?></textarea>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<?
		/**********************************************************************************************************************
		 *												|								|										*
		 *												|			Regulatório			|										*
		 *												V								V										*
		 ***********************************************************************************************************************/ ?>
		<div class="panel panel-default my-4">
			<div class="panel-heading"><a id="modalregulatorio" class="point">Regulatório</a></div>
			<div class="panel-body" id="certInfo" hidden>
				<table class="tabelaoptions">
					<tr>
						<td align="right">Descrição:</td>
						<td>
							<input name="_1_<?=$_acao?>_prodserv_descrgenerica" type="text" value="<?=$_1_u_prodserv_descrgenerica ?>">
						</td>
						<td align="right">Armazenamento</td>
						<td>
							<select name="_1_<?=$_acao?>_prodserv_armazanagem">
								<? fillselect(ProdServController::$armazanagem, $_1_u_prodserv_armazanagem); ?>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right"> Modo Partida:</td>
						<td>
							<select name="_1_<?=$_acao?>_prodserv_modopart">
								<option value=""></option>
								<? fillselect(ProdServController::$modopart, $_1_u_prodserv_modopart); ?>
							</select>
						</td>
						<?
						//Mostrar somente na unidade de produção
						if (ProdServController::verificarSeExisteTipoUnidadeProducao($_1_u_prodserv_idprodserv) > 0) { ?>
							<td align="right">Forma Farmacêutica:</td>
							<td>
								<select name="_1_<?=$_acao?>_prodserv_formafarm">
									<option></option>
									<? fillselect(ProdServController::$formafarm, $_1_u_prodserv_formafarm); ?>
								</select>
							</td>
						<? } ?>
					</tr>
				</table>
			</div>
		</div>
		<?
		/**********************************************************************************************************************
		 *												|								|										*
		 *												|    Arquivo de Orçamento		|										*
		 *												V								V										*
		 ***********************************************************************************************************************/
		?>
		<div class="panel panel-default my-4">
			<div class="panel-heading" data-toggle="collapse" href="#ArqCotacao">Arquivo de Orçamento</div>
			<div class="panel-body" id="ArqCotacao">
				<div id="imgcotacao" class="dz-clickable dropz" title="Clique ou arraste arquivos para cá" style="width:100%;height:100%;">
					<i class="fa fa-cloud-upload fonte18"></i>
				</div>
			</div>
		</div>
		<?
		/**********************************************************************************************************************
		 *												|								|										*
		 *												|			Observação			|										*
		 *												V								V										*
		 ***********************************************************************************************************************/
		?>
		<div class="panel panel-default my-4">
			<div class="panel-heading"><a id="modalobservacao" class="point" data-toggle="collapse" href="#obs">Observação</a></div>
			<div id="obs" class="panel-body">
				<table>
					<td><textarea name="_1_<?=$_acao?>_prodserv_obs" cols="300" rows="14"><?=$_1_u_prodserv_obs ?></textarea></td>
				</table>
			</div>
		</div>
		<?
		/**********************************************************************************************************************
		 *												|    Produto(s) e Serviço(s)   	|										*
		 *												| 		  Vinculado(s)			|										*
		 *												V								V										*
		 ***********************************************************************************************************************/
		if (!empty($_1_u_prodserv_idprodserv)) {
			$listarFormulas = ProdServController::listarFormulas($_1_u_prodserv_idprodserv);
			$qtdrowsf1 = count($listarFormulas);
			if ($qtdrowsf1 > 0) {
		?>
				<div class="panel panel-default my-4">
					<div class="panel-heading"><a id="modalprodservvinc" class="point">Produto(s) e Serviço(s) Vinculado(s)</a></div>
					<div class="panel-body" id="prodservvincInfo" hidden>
						<div>
							<span>Pesquisar Fórmulas:</span><input type="text" id="inputSearchProdservFormula">
							<hr>
							<table class="table table-striped planilha" id="tableformulas">
								<tr>
									<th>Produto</th>
									<th>Quantidade</th>
									<th>F.P.</th>
									<th></th>
									<th></th>
								</tr>
								<?
								$ifi = 99;
								foreach ($listarFormulas as $_formulas) {
									$ifi = $ifi + 1;
								?>
									<tr>
										<td class='col-md-10'><?=$_formulas["sigla"] ?> - <?=$_formulas['descr'] ?> <?=$_formulas['rotulo'] ?></td>
										<td class='col-md-2'>
											<input type="hidden" name="prodservformulains_idprodservformulains" value="<?=$_formulas["idprodservformulains"] ?>">
											<? $expoenteQtdi = recuperaExpoente($_formulas["qtdi"], $_formulas["qtdi_exp"]) ?>
											<input type="text" name="<?=$_formulas['idprodservformulains'] ?>_prodservformulains_qtdi" id="<?=$_formulas['idprodservformulains'] ?>_prodservformulains_qtdi" value="<?=$expoenteQtdi ?>" onchange="alteraFormulaIns(`<?=$_formulas['idprodservformulains'] ?>`, `qtdi`, this)" class="size7">
											<?=$_1_u_prodserv_un ?>
										</td>
										<td class='col-md-2'>
											<? $expoenteQtdpd = recuperaExpoente($_formulas["qtdpd"], $_formulas["qtdpd_exp"]) ?>
											<input type="text" name="<?=$_formulas['idprodservformulains'] ?>_prodservformulains_qtdpd" id="<?=$_formulas['idprodservformulains'] ?>_prodservformulains_qtdpd" value="<?=$expoenteQtdpd ?>" onchange="alteraFormulaIns(`<?=$_formulas['idprodservformulains'] ?>`, `qtdpd`, this)" class="size7">
										</td>
										<td class='col-md-2'>
											<a class="fa fa-bars pointer hoverazul" title="Produto/Serviço" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$_formulas['idprodserv'] ?>')"></a>
										</td>
										<td>
											<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer" onclick="excluirinsumo(<?=$_formulas["idprodservformulains"] ?>)" title="Excluir"></i>
										</td>
									</tr>
								<?
								}
								?>
							</table>
						</div>
					</div>
				</div>
				<?
			}
		}
	}
	?>
</div>
</div>
<?
}
if (!empty($_1_u_prodserv_idprodserv) and $_1_u_prodserv_tipo == 'SERVICO') //if($_1_u_prodserv_idprodserv){
{
	?>
	<div class="row">
		<div class="col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading">Informações Sobre o Serviço</div>
				<div class="panel-body">
					<table style="width: 90%">
						<tr>
							<td align="right">Categoria:</td>

							<td>
								<?
								$contaItem = ProdServController::buscarContaItemProdservContaItem($_1_u_prodserv_idprodserv);
								$qtdcontaitem = count($contaItem);
								if ($qtdcontaitem < 1) {
									?>
									<input value="<?=$contaItem['idcontaitem'] ?>" type="hidden" id="grupoesant">
									<input value="<?=$contaItem['idprodservcontaitem'] ?>" type="hidden" id="idprodservcontaitem">
									<input name="_grupoes_" cbvalue="<?=$contaItem['idcontaitem'] ?>" value="<?=$contaItem['contaitem'] ?>" type="text" id="grupoes">
								<? } else {
									echo $contaItem['contaitem'];
									?>
									<input name="_grupoes_" cbvalue="<?=$contaItem['idcontaitem'] ?>" value="<?=$contaItem['contaitem'] ?>" type="hidden" id="grupoes">
									<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" style="float:right" title="Excluir Vinculo" onclick="excluiritem('prodservcontaitem', <?=$contaItem['idprodservcontaitem'] ?>)"></i>
									<?
								}
								?>
							</td>
							<td align="right" style="display:none">Mostra Interpretação:</td>
							<td style="display:none">
								<select class="size15" name="_1_<?=$_acao?>_prodserv_geralegenda">
									<option value=""></option>
									<? fillselect(ProdServController::$CondicaoYesNo, $_1_u_prodserv_geralegenda); ?>
								</select>
							</td>
							<td align="right">Oficial:</td>
							<td>
								<select class="size5" name="_1_<?=$_acao?>_prodserv_logoinmetro">
									<option value=""></option>
									<? fillselect(ProdServController::$CondicaoYesNo, $_1_u_prodserv_logoinmetro); ?>
								</select>
							</td>
							<td align="right">Notificação Obr.:</td>
							<td>
								<select class="size5" name="_1_<?=$_acao?>_prodserv_notoficial">
									<? fillselect(ProdServController::$CondicaoYesNo, $_1_u_prodserv_notoficial); ?>
								</select>
							</td>
						</tr>
						<tr>
							<td align="right"> Subcategoria:</td>
							<td>
								<?
								if ($contaItem['idcontaitem']) {
									$sqlit = ProdServController::listarContaItemTipoProdservTipoProdServ($contaItem['idcontaitem']);
								} else {
									$sqlit = '';
								}
								?>
								<input type="hidden" id="_idtipoprodserv_" value="<?=$_1_u_prodserv_idtipoprodserv ?>">
								<select id="autoidtipoitem" class="size20" onchange="alteratipo(this)" name="_1_<?=$_acao?>_prodserv_idtipoprodserv">
									<option></option>
									<? fillselect($sqlit, $_1_u_prodserv_idtipoprodserv) ?>
								</select>
							</td>
							<td align="right">Prazo:</td>
							<td><input class="size5" placeholder="Dias" name="_1_<?=$_acao?>_prodserv_prazoexec" type="text" value="<?=$_1_u_prodserv_prazoexec ?>"></td>
							<td align="right">Conferência Amostra:</td>
							<td>
								<select class="size5" name="_1_<?=$_acao?>_prodserv_conferencia">
									<? fillselect(ProdServController::$CondicaoYesNo, $_1_u_prodserv_conferencia); ?>
								</select>
							</td>
						</tr>
						<tr>
							<td align="right">Vlr. Venda:</td>
							<td>
								<div class="d-flex align-items-center">
									<input class="size5" name="_1_<?=$_acao?>_prodserv_vlrvenda" id="vlrvenda" type="text" value="<?=$_1_u_prodserv_vlrvenda ?>" vdecimal disabled>
									<i class="fa fa-pencil pointer mx-3" onclick="alterarValorCampo('vlrvenda','<?=$_1_u_prodserv_vlrvenda ?>','modulohistorico',<?=$_1_u_prodserv_idprodserv ?>,'Valor venda:', true)"></i>
									<img src="/form/img/icon-hist.svg" class="pointer timeout-hist" alt="" />
								</div>
							</td>
							<td align="right">Portaria:</td>
							<td>
								<select class="size5" name="_1_<?=$_acao?>_prodserv_idportaria">
									<option value=""></option>
									<? fillselect(ProdServController::listarPortaria(), $_1_u_prodserv_idportaria); ?>
								</select>
							</td>
							<td align="right">Conferência Resultado:</td>
							<td>
								<select class="size5" name="_1_<?=$_acao?>_prodserv_conferenciares">
									<? fillselect(ProdServController::$CondicaoYesNo, $_1_u_prodserv_conferenciares); ?>
								</select>
							</td>
						</tr>
						<tr>
							<? if ($_1_u_prodserv_especial == 'Y') { ?>
								<td align="right" style="width: 15%;">Autógena:</td>
								<td>
									<i class="fa fa-star fa-1x laranja  btn-lg pointer" onclick="alerarEspecial('N');" title="Alterar Autógena para Não"></i>
									<label class="autogenalabel alert-warning"> AUTÓGENA</label>
							<? } else {
								$hidenautogena = 'hidden';
								?>
								<td align="right" style="width: 15%;">Autógena:</td>
								<td>
									<i class="fa fa-star fa-1x cinzaclaro btn-lg pointer" onclick="alerarEspecial('Y');" title="Alterar Autógena para Sim"></i>
								</td>							
							<? } ?>
							<td align="right">Laboratório:</td>
							<td>
								<select class="size20" name="_1_<?=$_acao?>_prodserv_laboratorio">
									<? fillselect(ProdServController::$laboratorio, $_1_u_prodserv_laboratorio); ?>
								</select>
							</td>
							<td align="right">Tipo de Teste:</td>
							<td>
								<select class="size20" name="_1_<?=$_acao?>_prodserv_tipoteste">
									<? fillselect(ProdServController::$tipoteste, $_1_u_prodserv_tipoteste); ?>
								</select>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<div class="col-xs-12">
			<div class="panel panel-default">
				<div class="panel-heading d-flex" style="align-items: center;">
					<span for="" style="margin-right: .5rem;">Compras nos últimos</span>
					<select name="_1_<?=$_acao?>_prodserv_consumodiasgraf" onchange="CB.post()">
						<? fillselect(CalculoEstoqueController::$_consumodiasgraf, $_1_u_prodserv_consumodiasgraf); ?>
					</select>
				</div>
				<div class="panel-body">
					<? if (!count($nfItemServico)) { ?>
						<h5>Nenhum registro encontrado neste periodo.</h5>
					<? } else { ?>
						<div class="panel panel-default">
							<div class="panel-heading">
								<span>Última(s) Compra(s)</span>
							</div>
							<div class="panel-body">
								<table class="table table-striped planilha">
									<thead>
										<tr>
											<th style="text-align: center;">NF</th>
											<th style="text-align: center;">Emissão</th>
											<th style="text-align: center;">Fornecedor</th>
											<th style="text-align: center;">Qtd</th>
											<th style="text-align: center;">Valor UN</th>
											<th style="text-align: center;">UN</th>
											<th style="text-align: center;" class="size15">Status</th>
										</tr>
									</thead>
									<tbody>
										<?
										foreach ($nfItemServico as $nf) {
											if ($nf['idnf'] != $idnf) $quantidadesomada = 0;

											if ($nf['idnfitem'] != $idnfitem) {
												$valconv = (($nf['valconv'] > 0) ? $nf['valconv'] : 1);
												$quantidadesomada += ($nf['qtd'] * $valconv);
											}

											if ($nf['idnf'] != $idnf) {
												$rotulo = getStatusFluxo('nf', 'idnf', $nf['idnf'])
												?>
												<tr <?=$fluxostatus != $nf['status'] && $nf['status'] == 'CONCLUIDO' && $key > 0 ? "style='border-top: 4px solid #1a111133'" : '' ?>>
													<td align="center" style="width: 10%;">
														<? if (empty($nf["nnfe"])) {
															if ($nf['status'] == 'APROVADO') { ?>
																<span class="azulclaro pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?=$nf['idnf'] ?>');"><?=$nf["idnf"]; ?></span>
															<? } else { ?>
																<span class="azulclaro pointer" onclick="janelamodal('?_modulo=cotacao&_acao=u&idcotacao=<?=$nf['idobjetosolipor'] ?>');"><?=$nf["idnf"]; ?></span>
															<? }
														} else { ?><span class=" azulclaro pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?=$nf['idnf'] ?>');"><?=$nf["nnfe"]; ?></span>
														<? } ?>
													</td>
													<td align="center" style="width: 15%;"><?=$nf["dtemissao"] ? dma($nf["dtemissao"]) : '-'; ?></td>
													<td align="center" style="width: 30%;"><?=$nf["nome"] ?></td>
													<td align="center" style="width: 10%;"><?= number_format(tratanumero($quantidadesomada), 2, ',', '.') ?></td>
													<td align="center" style="width: 10%;"><?= number_format(tratanumero($nf['valoritem2']), 4, ',', '.') ?></td>
													<td align="center" style="width: 10%;"><label class="alert-warning"><?=$nf["unpadrao"] ?></label></td>
													<td align="center" style="width: 15%;"><?= mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?>
												</tr>
												<? 
											}
										}
										$fluxostatus = $_nfItemLote['status'];
										$idnf = $_nfItemLote['idnf'];
										$idnfitem = $_nfItemLote['idnfitem'];
										?>
									</tbody>
								</table>
							</div>
						</div>
					<? } ?>
					<? if (count($nfItemServico)) { ?>
						<div class="panel panel-default">
							<div class="panel-heading">
								<a id="modalobservacao" class="point" data-toggle="collapse" href="#grafcompras">
									Histórico de Compra nos Últimos <?=$_1_u_prodserv_consumodiasgraf ?> Dias - Valor Unitário
								</a>
							</div>
							<div class="panel-body" id="grafcompras">
								<div id="chartdivcompras" style="text-align: -webkit-center; width:100%;height:400px; background-color: white;"></div>
							</div>
						</div>
						<?
						$_listarFormuladosSemFormula = NfController::buscarServicosFormuladosSemFormula($_1_u_prodserv_idprodserv, $_1_u_prodserv_consumodiasgraf);

						echo ("<!-- valores grafico compras ".$_listarFormuladosSemFormula['sql']."-->");
						$ai = 0;
						$arrgrafc = array_map(function ($item) {
							return [
								'data' => $item['dmadtemissao'],
								'valor' => $item['valoritem2']
							];
						}, $_listarFormuladosSemFormula['dados']);
					}
					?>
				</div>
			</div>
		</div>
		<div class="col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading" href="#body_configresult" data-toggle="collapse">Configurações do Resultado</div>
				<div class="panel-body" id="body_configresult">
					<div class="row">
						<div class="panel-body">
							<div class="row">
								<div class="col-sm-1 text-right">
									<label>Modelo:</label>
								</div>
								<div class="col-sm-3">
									<select id="input-modelo" name="_1_<?=$_acao?>_prodserv_modelo">
										<? fillselect(ProdServController::$modelo, $_1_u_prodserv_modelo) ?>
									</select>
								</div>
								<div class="col-sm-1 text-right">
									<label>Modo:</label>
								</div>
								<div class="col-sm-3">
									<select name="_1_<?=$_acao?>_prodserv_modo">
										<? fillselect(ProdServController::$modo, $_1_u_prodserv_modo) ?>
									</select>
								</div>
								<div class="col-sm-1 text-right">
									<label>Cálculo:</label>
								</div>
								<div class="col-sm-3">
									<select name="_1_<?=$_acao?>_prodserv_tipogmt" class="size10">
										<? fillselect(ProdServController::$tipogmt, $_1_u_prodserv_tipogmt) ?>
									</select>
								</div>

							</div>
							<div class="row">
								<div class="col-sm-1 text-right">
									<label>Comparativo de Lotes:</label>
								</div>
								<div class="col-sm-3">
									<select name="_1_<?=$_acao?>_prodserv_comparativodelotes">
										<? fillselect(ProdServController::$CondicaoYesNo, $_1_u_prodserv_comparativodelotes) ?>
									</select>
								</div>
								<div class="col-sm-1 text-right">
									<label>Gera Agente:</label>
								</div>
								<div class="col-sm-3">
									<select name="_1_<?=$_acao?>_prodserv_geraagente">
										<option value=""></option>
										<? fillselect(ProdServController::$CondicaoYesNo, $_1_u_prodserv_geraagente); ?>
									</select>
								</div>
								<div class="col-sm-1 text-right">
									<label>Exibir ao Cliente:</label>
								</div>
								<div class="col-sm-3">
									<select class="size10" name="_1_<?=$_acao?>_prodserv_visualizacliente">
										<option value=""></option>
										<? fillselect(ProdServController::$CondicaoYesNo, $_1_u_prodserv_visualizacliente); ?>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-1 text-right">
									<label>Nome do Alerta:</label>
								</div>
								<div class="col-sm-3">
									<input placeholder="Nome do Alerta" name="_1_<?=$_acao?>_prodserv_alertarotulo" type="text" value="<?=$_1_u_prodserv_alertarotulo ?>">
								</div>
								<div class="col-sm-1 text-right">
									<label>Rótulo Alerta Marcado:</label>
								</div>
								<div class="col-sm-3">
									<input placeholder="Rótulo Alerta Marcado" name="_1_<?=$_acao?>_prodserv_alertarotuloy" type="text" value="<?=$_1_u_prodserv_alertarotuloy ?>">
								</div>
								<div class="col-sm-1 text-right">
									<label>Rótulo Alerta Desmarcado:</label>
								</div>
								<div class="col-sm-3">
									<input placeholder="Rótulo Alerta Desmarcado" name="_1_<?=$_acao?>_prodserv_alertarotulon" class='size10' type="text" value="<?=$_1_u_prodserv_alertarotulon ?>">
								</div>
							</div>
							<div class="row">
								<? if (array_key_exists("prodservmaster", getModsUsr("MODULOS")) == 1) { ?>
									<div class="col-sm-1 text-right">
										<label>Permite Formatação:</label>
									</div>
									<div class="col-sm-3">
										<select class="size10" name="_1_<?=$_acao?>_prodserv_permiteformatacao">
											<? fillselect(ProdServController::$CondicaoYesNo, $_1_u_prodserv_permiteformatacao); ?>
										</select>
									</div>
								<? } ?>
								<div class="col-sm-1 text-right">
									<label>Documento Regulador:</label>
								</div>
								<div class="col-sm-3">
									<input id="idsgdoc" cbvalue="<?=$_1_u_prodserv_idsgdoc ?>" name="_1_<?=$_acao?>_prodserv_idsgdoc" value="<?=$listarDocsParaVinculo['formatado'][$_1_u_prodserv_idsgdoc] ?>">
								</div>
								<div class="col-sm-1 text-right">
									<label>Envia Emails Oficiais:</label>
								</div>
								<div class="col-sm-3">
									<select class="size10" name="_1_<?=$_acao?>_prodserv_enviaemailoficial">
										<option value=""></option>
										<? fillselect(ProdServController::$CondicaoYesNo, $_1_u_prodserv_enviaemailoficial); ?>
									</select>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-sm-1 text-right">
									<label>Opções:</label>
								</div>
								<div class="col-sm-2">
									<input type="text" name="opcao" id="opcao" value="" placeholder="Digite para adicionar">
								</div>
								<div class="col-sm-9 ">
									<?
									$listarOpcoesTipoServico = ProdServController::listarProdservTipoOpcaoPorIdprodserv($_1_u_prodserv_idprodserv);
									foreach ($listarOpcoesTipoServico as $_opcoesTipoServico) {
										$title = "Vinculado por: ".$_opcoesTipoServico["criadopor"]." - ".dmahms($r["criadoem"], true);
									?>
										<a title="<?=$title ?>" href="javascript:void(0)">
											<div class="opcoes"><?=$_opcoesTipoServico["valor"] ?>
												<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" style="float:right" title="Desvincular" idprodservtipoopcao="<?=$_opcoesTipoServico["idprodservtipoopcao"] ?>" onclick="desvincularOpcao(this)"></i>
											</div>
										</a>
									<?
									}
									?>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-1 text-right">
									<label>Tipo Alerta:</label>
								</div>
								<div class="col-sm-2 ">
									<input Title="Tipo Alerta" type="text" name="prodservtipoalerta" id="prodservtipoalerta" value="" placeholder="Digite para adicionar">
								</div>
								<div class="col-sm-9 " id="tipoalerta">
									<?
									$listarAlerta = ProdServController::listarConfiguracaoAlerta($_1_u_prodserv_idprodserv);
									if ($listarAlerta['qtdLinhas'] > 0) {
										foreach ($listarAlerta['dados'] as $alerta) {
											$title = "Vinculado por: ".$alerta["criadopor"]." - ".dmahms($alerta["criadoem"], true);
											?>
											<a title="<?=$title ?>" href="javascript:void(0)">
												<div class="opcoes"><?=$alerta["tipoalerta"] ?>
													<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" style="float:right" title="Excluir" idprodservtipoalerta="<?=$row["idprodservtipoalerta"] ?>" onclick="desvincularTipoalerta(this)"></i>
												</div>
											</a>
											<?
										}
									}
									?>
								</div>
							</div>
							<hr>
							<? if ($_1_u_prodserv_idprodserv == 15347) { ?>
								<div class="row">
									<div class="col-sm-1 text-right">
										<label>Agente:</label>
									</div>
									<div class="col-sm-2 ">
										<input Title="Tipo Agente" type="text" name="prodservtipoagente" id="prodservtipoagente" value="" placeholder="Digite para adicionar">
									</div>
									<div class="col-sm-9 " id="tipoagente">
										<?
										$listarAgente = ProdServController::listarConfiguracaoAgente($_1_u_prodserv_idprodserv);
										if ($listarAgente['qtdLinhas'] > 0) {
											foreach ($listarAgente['dados'] as $agente) {
												$title = "Vinculado por: ".$agente["criadopor"]." - ".dmahms($agente["criadoem"], true);
												?>
												<a title="<?=$title ?>" href="javascript:void(0)">
													<div class="opcoes"><?=$agente["tipoagente"] ?>
														<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" style="float:right" title="Excluir" idprodservtipoagente="<?=$agente["idprodservtipoagente"] ?>" onclick="desvincularTipoagente(this)"></i>
													</div>
												</a>
												<?
											}
										}
										?>
									</div>
								</div>
							<?
							}
							?>
						</div>
						<!--JLAL - 20/10/01 
							Remoção do campo dinamico que estava fixo, alterado para chamar a função que cria o bloco dinamico.
							Alteração permite que sejam criadas varias config de acordo com a unidade especifica desejada
							-->
						<div class="alinharBotao">
							<button class="btn btn-primary btn-xs" id="addConfig" data-tipocriacao="default"><i class="fa fa-plus"></i>Nova configuração</button>
							<input name="_1_<?=$_acao?>_prodserv_jsonconfig" type="hidden" id="jsonconfig" value='<?=$_1_u_prodserv_jsonconfig ?>'>
						</div>
						<hr>
						<div class="novoCampoConfig"></div>
						<div class="col-sm-12">
							<div class="row">
								<label> Gráficos:
									<i class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer" onclick="novografico()" title="Relacionar processo"></i>
								</label>
								<div class="col-sm-12 text-left">

									<?
									$listarProdservEspecie = ProdServController::listarProdservTipoOpcaoEspecie($_1_u_prodserv_idprodserv);
									$i = 0;
									foreach ($listarProdservEspecie['dados'] as $especie) {
										if ($i > 0 and $idadeinicio != $especie["idadeinicio"]) {
											echo '<div class="col-md-12"></div>';
										}
										$idadeinicio = $especie["idadeinicio"];
										$i = $i + 1;
										?>
										<div class="col-sm-2 text-left" style="margin-bottom: 12px;">
											<input name="_pe<?=$i ?>_u_prodservtipoopcaoespecie_idprodservtipoopcaoespecie" type="hidden" size="4" value="<?=$especie["idprodservtipoopcaoespecie"] ?>">
											<div class="input-group input-group-sm">
												<span class="input-group-addon pointer" style="cursor: initial;"><i class="fa fa-expand" style="color:#aaa; "></i></span>
												<select name="_pe<?=$i ?>_u_prodservtipoopcaoespecie_idespeciefinalidade" class="form-control especieFinalidade">
													<option value="" disabled selected hidden>Espécie...</option>
													<? fillselect(ProdServController::listarEspecieFinalidadePlantelOrdenadoPorPlantel($_1_u_prodserv_idempresa), $especie['idespeciefinalidade']); ?>
												</select>
												<? if (isset($especie['valorinicio'])) {
													$valini = $especie['valorinicio'];
												}
												if (isset($especie['valorfim'])) {
													$valfim = $especie['valorfim'];
												} ?>
												<input name="_pe<?=$i ?>_u_prodservtipoopcaoespecie_valorinicio" type="number" value="<?=$valini ?>" placeholder="- Entre -" class="form-control">
												<input name="_pe<?=$i ?>_u_prodservtipoopcaoespecie_valorfim" type="number" value="<?=$valfim ?>" placeholder="- E -" class="form-control">
												<input name="_pe<?=$i ?>_u_prodservtipoopcaoespecie_idadeinicio" type="text" size="4" value="<?=$especie["idadeinicio"] ?>">

												<span class="fa errspan">entre</span><span class="fa fa-calendar errspan2"></span>
												<input name="_pe<?=$i ?>_u_prodservtipoopcaoespecie_idadefim" type="text" size="4" value="<?=$especie["idadefim"] ?>">

												<span class="fa errspan">e</span><span class="fa fa-calendar errspan2"></span>
												<textarea name="_pe<?=$i ?>_u_prodservtipoopcaoespecie_msg" placeholder="Mensagem" style="height:80px"><?=$especie["msg"] ?></textarea>
												<?
												$selectedAzul = "";
												$selecteVermelho = "";
												$selectedVerde = "";
												$selectedAmarelo = "";
												switch ($especie["cor"]) {
													case 'azul':
														$selectedAzul = 'selected';
														break;
													case 'vermelho':
														$selecteVermelho = 'selected';
														break;
													case 'verde':
														$selectedVerde = 'selected';
														break;
													case 'amarelo':
														$selectedAmarelo = 'selected';
														break;
												}
												?>
												<select name="_pe<?=$i ?>_u_prodservtipoopcaoespecie_cor" class="<?=$especie["cor"]; ?>g" onchange="javascript:this.style.backgroundColor = '#fff'">
													<option value="">- Cor do Gráfico -</option>
													<option value="azul" class="azulg" <?=$selectedAzul ?>>azul</option>
													<option value="vermelho" class="vermelhog" <?=$selecteVermelho ?>>vermelho</option>
													<option value="verde" class="verdeg" <?=$selectedVerde ?>>verde</option>
													<option value="amarelo" class="amarelog" <?=$selectedAmarelo ?>>amarelo</option>
												</select>

												<span class="input-group-addon pointer hoververmelho" title="Excluir"><i class="fa fa-trash" onclick="excluirgrafico(<?=$especie['idprodservtipoopcaoespecie'] ?>)"></i></span>
											</div>
										</div>
										<?
									}
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading" href="#body_relatorios" data-toggle="collapse">Relatório(s)</div>
				<div class="panel-body" id="body_relatorios">
					<?
					$listarTipoRelatorio = ProdServController::listarTipoRelatorioPorIdProdserv($_1_u_prodserv_idprodserv);
					?>
					<table style="width: 100%">
						<tr>
							<? $rel = 0;
							foreach ($listarTipoRelatorio as $tipoRelatorio) {
								$rel = $rel + 1;
								if ($rel == 5) {
									$rel = 0;
							?>
						</tr>
						<tr>
						<?
								}
						?>
						<td>
							<?
								if (!empty($tipoRelatorio['idprodservtiporelatorio'])) {
							?>
								<i class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="removerRelatorio(<?=$tipoRelatorio['idprodservtiporelatorio'] ?>);" alt="Retirar relatório"></i>
							<? } else { ?>
								<i class="fa fa-square-o fa-1x btn-lg pointer" onclick="inserirRelatorio(<?=$tipoRelatorio['idtiporelatorio'] ?>);" alt="Inserir relatório"></i>
							<? }
								echo ($tipoRelatorio['tiporelatorio']);
							?>
						</td>
							<?
							}
							?>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<div class="col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading" href="#body_textoinclusao" data-toggle="collapse">Texto Inclusão Resultado</div>
				<div class="panel-body" id="body_textoinclusao">
					<table style="width: 100%">
						<tr>
							<td>
								<label id="lbaviso" class="idbox" style="display: none;"></label>
								<div id="diveditor" class="diveditor" onkeypress="pageStateChanged=true;" style="width: 800px;height: 200px; margin: auto"><?=$_1_u_prodserv_textoinclusaores ?></div>
								<textarea style="display: none;" name="_1_u_prodserv_textoinclusaores"><?=$_1_u_prodserv_textoinclusaores ?></textarea>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<div class="col-sm-12">
			<div class="col-sm-6" style="padding-left:0px;padding-right:10px;">
				<div class="panel panel-default">
					<div class="panel-heading bold" href="#body_textopadrao" data-toggle="collapse">Texto Padrão</div>
					<div class="panel-body" id="body_textopadrao">
						<table>
							<tr>
								<td align="right">Título:</td>
								<td><input name="_1_<?=$_acao?>_prodserv_titulotextopadrao" size="80" type="text" value="<?=$_1_u_prodserv_titulotextopadrao ?>"></td>
							</tr>
						</table>
						<table style="width: 100%">
							<tr>
								<td>
									<label id="lbaviso" class="idbox" style="display: none;"></label>

									<div id="diveditor2" class="diveditor2" onkeypress="pageStateChanged=true;" style="width: 100%;height: 200px;"><?=$_1_u_prodserv_textopadrao ?></div>

									<textarea style="display: none;" name="_1_u_prodserv_textopadrao"><?=$_1_u_prodserv_textopadrao ?></textarea>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>

			<div class="col-sm-6" style="padding-left:10px;padding-right:0px;">
				<div class="panel panel-default">
					<div class="panel-heading bold" href="#body_interpretrel" data-toggle="collapse">Interpretações Relacionadas</div>
					<div class="panel-body" id="body_interpretrel">
						<table style="width: 100%">
							<tr>
								<td><input id="idtipoteste" type="text" cbvalue placeholder="Selecione"></td>
							</tr>
						</table>
						<table class='table-hover' style="width: 100%">
							<tbody>
								<?
								$litarInterpretacoes = ProdServController::listarInterpretacoesRelacionadasServicoSelecionadas($_1_u_prodserv_idprodserv);
								foreach ($litarInterpretacoes as $_interpretacoes) {
									?>
									<tr>
										<td>
											<a title="Editar Teste" target="_blank" href="?_modulo=interpretacao&_acao=u&idinterpretacao=" <?=$_interpretacoes['idinterpretacao'] ?>"><?=$_interpretacoes["titulo"] ?></a>
										</td>
										<td align="center">
											<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="retirarTeste(<?=$_interpretacoes['idintertipoteste'] ?>)" title="Excluir!"></i>
										</td>
									</tr>
									<?
								}
								?>
							</tbody>
						</table>
						<table style="width: 100%">
							<tr>
								<td>
									<div id="diveditor3" class="diveditor3" onkeypress="pageStateChanged=true;" style="width: 100%;height: 200px;">
										<?=$_1_u_prodserv_textointerpretacao ?>
									</div>
									<textarea style="display: none;" name="_1_u_prodserv_textointerpretacao"><?=$_1_u_prodserv_textointerpretacao ?></textarea>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-12">
			<? // Atividades vinculadas
			if ($_1_u_prodserv_tipo === 'SERVICO') { ?>
				<div class="panel panel-default w-100">
					<div class="panel-heading">
						<a class="ativmodal point">Atividades vinculadas</a>
					</div>
					<div class="panel-body" id="atividades-vinculadas" hidden>
						<div class="row">
							<div class="col-xs-12 form-group">
								<label for="">Vincular atividade</label>
								<input id="input-vinc-atividade" type="text" class="form-control" />
							</div>
						</div>
						<? if (count($atividadesRelacionadas)) { ?>
							<table class="table table-stripped">
								<thead>
									<th>Ativ. Op</th>
									<th>Descrição</th>
									<th></th>
									<th></th>
								</thead>
								<tbody>
									<? foreach ($atividadesRelacionadas as $atividade) { ?>
										<tr>
											<td><?=$atividade["ativ"] ?></td>
											<td><?=$atividade["descr"] ?></td>
											<td>
												<a href="?_modulo=prativ&_acao=u&idprativ=<?=$atividade["idprativ"] ?>" class="fa fa-bars pointer hoverpreto btn-lg" target="_blank"></a>
											</td>
											<td>
												<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg text-danger pointer' onclick='desvincularAtividade(<?=$atividade["idprativobj"] ?>)' title='Excluir!'></i>
											</td>
										</tr>
									<? } ?>
								</tbody>
							</table>
						<? } else { ?>
							<h5>Nenhuma atividade vinculada</h5>
						<? } ?>
					</div>
				</div>
			<? } ?>
		</div>
	</div>
<?
}

if (!empty($_1_u_prodserv_idprodserv)) { ?>
	<div class="col-sm-12">
		<?if ($_1_u_prodserv_tipo == 'SERVICO') { ?>
			<div class="col-sm-6" style="padding-left:0px;padding-right:10px;">
				<div class="panel panel-default">
					<div class="panel-heading"><a class="formuladomodal point">Fórmulas e Processos</a></div>
				</div>
			</div>
		<?}?>
		<div class="col-sm-6" style="padding-left:0px;padding-right:10px;">
			<? if ($_1_u_prodserv_especial == 'Y') {
				/************************************************************************************************************************
				 *												|								|										*
				 *												|			Modal				|										*
				 *												|  		vinculobjeto			|										*
				 *												V								V										*
				 ***********************************************************************************************************************/
				?>
				<div class="panel panel-default">
					<div class="panel-heading"><a id="modalProdutosVinculados" class="point">Produtos Vinculados(s)</a></div>
					<div class="panel-body" id="prodservvincobj" hidden>
						<table class="table table-striped planilha">
							<tr>
								<th colspan="3">Produto(s)</th>
								<th></th>
								<th></th>
							</tr>
							<tr>
								<td colspan="3">
									<input id="idobjetovinc">
								</td>
							</tr>
							<? $ifi = 99;
							$listarProdutosVinculados = ProdServController::buscarProdservObjetoVinculoPorIdobjetoTipoobjetoTipo($_1_u_prodserv_idprodserv, 'prodserv', 'PRODUTO');
							foreach ($listarProdutosVinculados as $produtosVinculados) {
								$ifi = $ifi + 1;
							?>
								<tr>
									<td class='col-md-10'><?=$produtosVinculados['descr'] ?></td>
									<td class='col-md-2'>
										<a class="fa fa-bars pointer hoverazul" title="Produto/Serviço" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$produtosVinculados['idprodserv'] ?>')"></a>
									</td>
									<td>
										<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer" onclick="excluirvinc(<?=$produtosVinculados['idobjetovinculo'] ?>)" title="Excluir"></i>
									</td>
								</tr>
							<? } ?>
						</table>
					</div>
				</div>
			<?
			}
			?>
			<div class="panel panel-default">
				<div class="panel-heading"><a id="modalServicosVinculados" class="point">Serviços Vinculados(s)</a></div>
				<div class="panel-body" id="prodservvincobj2" hidden>
					<table class="table table-striped planilha">
						<tr>
							<th colspan="4">Serviço(s)</th>
						</tr>
						<tr>
							<td colspan="4">
								<input id="idobjetovinc2">
							</td>
						</tr>
						<?
						$ifi = 99;
						$listarProdservVinculo = ProdServController::buscarProdservVinculoServico($_1_u_prodserv_idprodserv);
						foreach ($listarProdservVinculo as $prodservVinculo) {
							$ifi = $ifi + 1;
							$acaovinculo = $prodservVinculo['idprodservvinculo'] ? 'u' : 'i';
							?>
							<tr style="height:35px;">
								<td class='col-md-8'>
									<?=$prodservVinculo['descr'] ?>
								</td>
								<td class='col-md-1'>
									<a class="fa fa-bars pointer hoverazul" title="Produto/Serviço" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$prodservVinculo['idprodserv'] ?>')"></a>
								</td>
								<td class='col-md-2'>
									<p>Condição/Alerta: <input type="checkbox" onclick="alterarAlerta(this, <?=$prodservVinculo['idprodservvinculo'] ?>,<?=$prodservVinculo['idobjeto'] ?>)" <?=$prodservVinculo['alerta'] == 'Y' ? 'checked' : '' ?>></p>
								</td>
								<td class='col-md-1 text-center'>
									<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer" onclick="excluirServicoVinculado(<?=$prodservVinculo['idprodservvinculo'] ?>)" title="Excluir"></i>
								</td>
							</tr>
						<? } ?>
					</table>
				</div>
			</div>
		</div>

		<div class="col-md-6" style="padding-left:0px;padding-right:10px;">
			<div class="panel panel-default">
				<div class="panel-heading"><a id="modalTagsVinculadas" class="point">Tags Vinculada(s)</a></div>
				<div class="panel-body" id="prodservvincobj3" hidden>
					<div class="w-100" style="min-height: 300px;">
						<table class="table table-striped planilha">
							<tr>
								<th colspan="2">Sala(s)</th>
								<th></th>
								<th colspan="2">Tipo Tag(s)</th>
								<th>Tag(s)</th>
								<th colspan="2"></th>
							</tr>
							<tr>
								<td colspan="2">
									<input id="idobjetovinc31">
								</td>
								<td></td>
								<td colspan="2"></td>
								<td></td>
								<td colspan="2"></td>
							</tr>
							<?
							$ifi = 1111;
							$listarProdservVinculo = ProdServController::buscarTagSalaVinculo($_1_u_prodserv_idprodserv);
							foreach ($listarProdservVinculo as $prodservVinculo) {
								$ifi = $ifi + 1;
								$acaovinculo = $prodservVinculo['idprodservvinculo'] ? 'u' : 'i';
								?>
								<tr style="height:35px;" id="linha-<?=$prodservVinculo['idtag'] ?>">
									<td>
										<?=$prodservVinculo['descricao'] ?>
									</td>
									<td colspan="2" align="left">
										<a class="fa fa-bars pointer hoverazul" title="Tag" onclick="janelamodal('?_modulo=tag&_acao=u&idtag=<?=$prodservVinculo['idtag'] ?>')"></a>
									</td>
									<td>
										<?
										$vinculo = $prodservVinculo['idobjetovinculo'] ? $prodservVinculo['idobjetovinculo'] : 'null';
										$tipoTagsDisponiveisParaVinculo = TagController::buscarTipoPorIdTagSala($prodservVinculo['idobjeto'], $_1_u_prodserv_idempresa, true);
										?>
										<select name="" id="select-tagtipo-<?=$prodservVinculo['idprodservvinculo'] ?>" class="selectpicker select-tipo-tag auto-tipotag" idprodservvinculo="<?=$prodservVinculo['idprodservvinculo'] ?>" idtagsala="<?=$prodservVinculo['idtag'] ?>" idobjetovinculo="<?=$prodservVinculo['idobjetovinculo'] ?>">
											<option value=""></option>
											<?= fillselect($tipoTagsDisponiveisParaVinculo, $prodservVinculo['idobjetovinc']) ?>
										</select>
									</td>
									<? $tags = $prodservVinculo['idobjetovinc'] ? TagController::buscarTagsFormatadasPorIdTagTipo($prodservVinculo['idobjetovinc'], $_1_u_prodserv_idempresa) : []; ?>
									<td class='text-center'>
										<? if (!$tags) { ?>
											<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer" onclick="excluirTagsVinculado(<?=$prodservVinculo['idprodservvinculo'] ?>,<?=$vinculo ?>)" title="Excluir"></i>
										<? } ?>
									</td>
									<td class="select-col">
										<?
										if ($tags) {
											$tagsVinculadas = ProdServController::buscarVinculosTipoTagPorIdProdserv($_1_u_prodserv_idprodserv, $prodservVinculo['idobjetovinc'], implode(',', array_map(function ($item) {
												return $item['idtag'];
											}, $tags)));

											if ($tagsVinculadas)
												$tagsVinculadas = array_map(function ($item) {
													return $item['idTag'];
												}, $tagsVinculadas);
										?>
											<div class="w-100 flex">
												<select class="selectpicker tag-select" multiple data-live-search="true">
													<? foreach ($tags as $tag) { ?>
														<option value="<?=$tag['idtag'] ?>" <?= ($tagsVinculadas && in_array($tag['idtag'], $tagsVinculadas)) ? 'selected' : ''; ?>><?=$tag['sigla'] ?> - <?=$tag['descricao'] ?></option>
													<? } ?>
												</select>
												<button class="btn btn-warning pointer hide atualizar-tag" onclick="atualizaObjetoVinculo(this);">
													<i class="fa fa-warning"></i>
													Salvar alterações
												</button>
											</div>
										<? } ?>
									</td>
									<td class='text-center'>
										<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer remover <?= !$tags ? 'hide' : '' ?>" onclick="excluirTagsVinculado(<?=$prodservVinculo['idprodservvinculo'] ?>,<?=$vinculo ?>, this);" title="Excluir"></i>
									</td>
								</tr>
							<? } ?>
						</table>
					</div>
				</div>
			</div>
			<?if (!empty($_1_u_prodserv_idprodserv)) {
				$listarProcessosVinculados = ProdServController::listarProcessosVinculados($_1_u_prodserv_idprodserv);
				if (!empty($listarProcessosVinculados) and $_1_u_prodserv_tipo != 'PRODUTO') { ?>
						<div class="panel panel-default">
							<div class="panel-heading">Processo</div>
							<div class="panel-body">
								<tbody>
									<table id="tbItens" class="table table-striped planilha display ">
										<?
										foreach ($listarProcessosVinculados as $_processos) {
											?>
											<tr>
												<td style="line-height: 14px; padding: 8px; color:#666;">
													<div>
														<div style="margin-left: 1px;">
															<?=$_processos['proc'] ?>
														</div>
													</div>
												</td>
												<td>
													<div>
														<a class="fa fa-bars pointer hoverazul" title="ID: <?=$_processos['idprproc'] ?>" onclick="janelamodal('?_modulo=prproc&_acao=u&idprproc=<?=$_processos['idprproc'] ?>')"></a>
													</div>
												</td>
											</tr>
											<?
										}
										?>
									</table>
								</tbody>
							</div>
						</div>
					</div>
				<?}

				$certificadoProcessos = ProdServController::listarCertificadosProcesso($_1_u_prodserv_idprodserv);
				if (!empty($certificadoProcessos)) {
					?>
							<div class="panel panel-default">
								<div class="panel-heading" href="#body_certproc" data-toggle="collapse">Certificados - Processos</div>
								<div class="panel-body" id="body_certproc">
									<tbody>
										<table id="tbItens" class="table table-striped planilha display ">
											<? foreach ($certificadoProcessos as $_certProc) {
											?>
												<tr>
													<td style="line-height: 14px; padding: 8px; color:#666;">
														<div>
															<div style="margin-left: 1px;">
																<?=$_certProc['proc'] ?> - <?=$_certProc['idprproc'] ?>
															</div>
														</div>
													</td>
													<td>
														<div>
															<a class="fa fa-bars pointer hoverazul " title="ID: <?=$_certProc['idprproc'] ?>" onclick="janelamodal('?_modulo=prproc&_acao=u&idprproc=<?=$_certProc['idprproc'] ?>')"></a>
														</div>
													</td>
												</tr>
											<?
											} ?>
										</table>
									</tbody>
								</div>
							</div>
						</div>
					<?
				}
			}?>
		</div>
	</div>
<?}

if (!empty($_1_u_prodserv_idprodserv)) {
	$analiseBioterio = ProdServController::listarAnaliseBioterio($_1_u_prodserv_idprodserv);
	if (!empty($analiseBioterio)) {
	?>
		<div class="col-md-6 alinhamentoEsquerda">
			<div class="panel panel-default">
				<div class="panel-heading">Análise - Biotério</div>
				<div class="panel-body">
					<tbody>
						<table id="tbItens" class="table table-striped planilha display">
							<? foreach ($analiseBioterio as $_bioterio) { ?>
								<tr>
									<td style="line-height: 14px; padding: 8px; color:#666;">
										<div>
											<div style="margin-left: 1px;">
												<?=$_bioterio['tipoanalise'] ?> - <?=$_bioterio['idobjeto'] ?>
											</div>
										</div>
									</td>
									<td>
										<div>
											<a class="fa fa-bars pointer hoverazul " title="ID: <?=$_bioterio['idobjeto'] ?>" onclick="janelamodal('?_modulo=bioterioanalise&_acao=u&idbioterioanalise=<?=$_bioterio['idobjeto'] ?>')"></a>
										</div>
									</td>
								</tr>
							<?
							}
							?>
						</table>
					</tbody>
				</div>
			</div>
		</div>
		<?
	}
}

if (!empty($_1_u_prodserv_idprodserv)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_prodserv_idprodserv; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "prodserv"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
require_once('../form/js/prodserv_js.php');
?>