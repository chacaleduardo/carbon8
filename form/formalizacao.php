<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
ini_set('memory_limit', -1);

require_once(__DIR__ . "/controllers/fluxo_controller.php");
require_once(__DIR__ . "/controllers/formalizacao_controller.php");

if (empty($_1_u_lote_tipo) && strpos($_GET['_modulo'], 'servico') == true) {
	$_1_u_lote_tipo = 'SERVICO';
} elseif (empty($_1_u_lote_tipo)) {
	$_1_u_lote_tipo = 'PRODUTO';
}

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parámetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagprefixo = '_1_u_';
$pagvaltabela = "formalizacao";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
	"idformalizacao" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT * FROM formalizacao WHERE idformalizacao = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require("../inc/php/controlevariaveisgetpost.php");

$pagprefixo = '_2_u_';
$pagvaltabela = "lote";
$pagvalcampos = array(
	"idlote" => "pk"
);

$_ignoraEmpresaControleVariavelGetPost = true;
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */

$pagsql = "SELECT * FROM lote WHERE idlote = " . $_1_u_formalizacao_idlote;
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require("../inc/php/controlevariaveisgetpost.php");

//Recuperar a unidade padrão conforme módulo pré-configurado
$idunidadepadrao = getUnidadePadraoModulo($_GET["_modulo"]);

if (!empty($_GET['idunidade'])) {
	$_2_u_lote_idunidade = $_GET['idunidade'];
}
if (!empty($_GET['idunidade'])) {
	$_1_u_formalizacao_idunidade = $_GET['idunidade'];
}

if (!empty($_GET['idpessoa']) and empty($_2_u_lote_idpessoa)) {
	$_2_u_lote_idpessoa = $_GET['idpessoa'];
}

if (!empty($_GET['idprodserv']) and empty($_2_u_lote_idprodserv)) {
	$_2_u_lote_idprodserv = $_GET['idprodserv'];
}

if (empty($_2_u_lote_idunidade)) {
	$_2_u_lote_idunidade = $idunidadepadrao;
}
if (empty($_1_u_formalizacao_idunidade)) {
	$_1_u_formalizacao_idunidade = $idunidadepadrao;
}

if (empty($_2_u_lote_exercicio)) {
	$_2_u_lote_exercicio = date("Y");
}
if (empty($_1_u_formalizacao_exercicio)) {
	$_1_u_formalizacao_exercicio = date("Y");
}

if (empty($_2_u_lote_status)) {
	$_2_u_lote_status = 'ABERTO';
}
if (empty($_1_u_formalizacao_status)) {
	$_1_u_formalizacao_status = 'ABERTO';
}

if (!empty($_2_u_lote_idlote)) {
	$prodForm = getObjeto("prodserv", $_2_u_lote_idprodserv, "idprodserv");
}

if (!empty($_2_u_lote_idprodservformula)) {
	$prodFormula = getObjeto("vwprodservformula", $_2_u_lote_idprodservformula, "idprodservformula");
}

if (!empty($_GET['_idempresa']) and empty($_2_u_lote_idempresa)) {
	$_2_u_lote_idempresa = $_GET['_idempresa'];
	$_1_u_formalizacao_idempresa = $_GET['_idempresa'];
}

//Recupera a listagem de clientes que será usado também na função buscarClientesSolicitacaoFabricacao, logo abaixo.
$arrCli = FormalizacaoController::buscarPessoaPorStatusIdTipoPessoaEIdEmpresa('ATIVO', 2);

$arrResponsavel = FormalizacaoController::buscarResponsavelFormalizacao(1, '', 'ATIVO');

//Recupera os produtos que o usuário pode selecionar em uma nova Formalização
$arrProd = FormalizacaoController::buscarProdutosFormalizacao($_1_u_lote_tipo);

//LTM (12-05-2021): Pega o modulo do lote para inserir o fluxostatushist
$modulolote = FluxoController::getDadosModuloPrincipal($_2_u_lote_idlote);

if (!empty($_2_u_lote_idlote)) {
	//busca lotes consumidos
	$arrLotecons = FormalizacaoController::buscarConsumoLoteProduto($_2_u_lote_idlote);

	if (!empty($_2_u_lote_idpessoa)) {
		$arrClientesSolFab = FormalizacaoController::buscarClientesSolicitacaoFabricacao($_2_u_lote_idprodservformula, $_2_u_lote_idpessoa, $_1_u_formalizacao_status, true);
	}
} else {
	$arrLotecons = array();
}

if (!empty($_2_u_lote_idlote) && !empty($_2_u_lote_idprodservformula)) {
	//se for status FORMALIZACAO o idlote na getArvoreInsumos vai vazio para poder selecionar os lotes
	if ($_1_u_formalizacao_status == "FORMALIZACAO" or $_1_u_formalizacao_status == "PROCESSANDO" or $_1_u_formalizacao_status == "TRIAGEM" or $_1_u_formalizacao_status == "AGUARDANDO") {
		$arrInsumos = getArvoreInsumos($_2_u_lote_idprodserv, true, null, null, $_2_u_lote_idprodservformula, $_2_u_lote_idlote, $_2_u_lote_idpessoa, $_1_u_formalizacao_status); //buscar os insumos do produto que não possuem và­nculo com Atividade
	} else { //se for outros status vai com idlote para trazer somente os lotes que foram utilizados
		$arrInsumos = getArvoreInsumos($_2_u_lote_idprodserv, true, null, null, $_2_u_lote_idprodservformula, $_2_u_lote_idlote, $_2_u_lote_idpessoa, $_1_u_formalizacao_status); //buscar os insumos do produto que não possuem và­nculo com Atividade
	}

	//buscar os insumos do produto estão vinculado à  Atividades
	$jAtividadeInsumos = FormalizacaoController::buscarInsumoFormula($_2_u_lote_idprodserv, false, $_2_u_lote_idlote);

	//Atividades configuradas
	$statusFormalizacao = array("FORMALIZACAO", "PROCESSANDO", "TRIAGEM", "AGUARDANDO");
	if (in_array($_1_u_formalizacao_status, $statusFormalizacao)) {
		//Seta o modudlo da formalizacao para pegar o hist ($pagvalmodulo)
		$arrLoteAtiv = FormalizacaoController::buscarLoteAtividade($_2_u_lote_idlote, null, $_GET["_modulo"], $_GET["idloteativ"]);
	} else {
		//Seta o modudlo da formalizacao para pegar o hist ($pagvalmodulo)
		$arrLoteAtiv = FormalizacaoController::buscarLoteAtividade($_2_u_lote_idlote, 'Y', $_GET["_modulo"], $_GET["idloteativ"]);
	}

	//Todos os Objetos utilizados/selecionados
	$arrLoteObj = FormalizacaoController::buscarObjetosLote($_2_u_lote_idlote);
	$arrsgareasetor = FormalizacaoController::buscarPessoaObjetoAreaSetor($_SESSION["SESSAO"]["IDPESSOA"], 'sgsetor');

	$prodsersAtivos = ProdServController::buscarProdservAtivasSemVenda(cb::idempresa(), true);
	$prodsersInsumosAtivos = ProdServController::buscarProdservInsumoAtivasSemVenda(cb::idempresa(), $_1_u_formalizacao_idunidade, true);
}

if (!empty($_2_u_lote_idprodserv) && !empty($_2_u_lote_idprodservformula)) {
	$arrInfRotulo = FormalizacaoController::listarEnderecoPessoaLote(6, $_2_u_lote_idlote);
}

if ($_2_u_lote_idlote) {
	$listaHistoricoFab = LoteController::buscarHistoricoDeAlteração($_2_u_lote_idlote, 'lote', 'producao');
	$qtdhist = count($listaHistoricoFab);
	if ($qtdhist > 0) { ?>
		<div id="hist_producao" style="display: none">
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
					<? foreach ($listaHistoricoFab as $historico) { ?>
						<tr>
							<? if ($historico['campo'] == "idunidadeest" || $historico['campo'] == "idunidadealerta") { ?>
								<td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor_old']); ?></label></td>
								<td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor']); ?></label></td>
							<? } else { ?>
								<td><?= $historico['valor_old'] ?></td>
								<td><?= $historico['valor'] ?></td>
							<? } ?>

							<td><?
								echo $historico['justificativa'];
								?></td>
							<td><?= $historico['nomecurto'] ?></td>
							<td><?= dmahms($historico['criadoem']) ?></td>
						</tr>
					<?
					} ?>
				</tbody>
			</table>
		</div>
	<? }

	$listaHistoricoFab = LoteController::buscarHistoricoDeAlteração($_2_u_lote_idlote, 'lote', 'fabricacao');
	$qtdhist = count($listaHistoricoFab);
	if ($qtdhist > 0) { ?>
		<div id="hist_fabricacao" style="display: none">
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
					<? foreach ($listaHistoricoFab as $historico) { ?>
						<tr>
							<? if ($historico['campo'] == "idunidadeest" || $historico['campo'] == "idunidadealerta") { ?>
								<td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor_old']); ?></label></td>
								<td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor']); ?></label></td>
							<? } else { ?>
								<td><?= $historico['valor_old'] ?></td>
								<td><?= $historico['valor'] ?></td>
							<? } ?>

							<td><?
								echo $historico['justificativa'];
								?></td>
							<td><?= $historico['nomecurto'] ?></td>
							<td><?= dmahms($historico['criadoem']) ?></td>
						</tr>
					<?
					} ?>
				</tbody>
			</table>
		</div>
	<? }
	$listaHistoricoFab = LoteController::buscarHistoricoDeAlteração($_2_u_lote_idlote, 'lote', 'qtdpedida');
	$qtdhist = count($listaHistoricoFab);
	if ($qtdhist > 0) { ?>
		<div id="hist_qtdpedida" style="display: none">
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
					<? foreach ($listaHistoricoFab as $historico) { ?>
						<tr>
							<? if ($historico['campo'] == "idunidadeest" || $historico['campo'] == "idunidadealerta") { ?>
								<td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor_old']); ?></label></td>
								<td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor']); ?></label></td>
							<? } else { ?>
								<td><?= $historico['valor_old'] ?></td>
								<td><?= $historico['valor'] ?></td>
							<? } ?>

							<td><?
								echo $historico['justificativa'];
								?></td>
							<td><?= $historico['nomecurto'] ?></td>
							<td><?= dmahms($historico['criadoem']) ?></td>
						</tr>
					<?
					} ?>
				</tbody>
			</table>
		</div>
	<? }

	$listaHistoricoFab = LoteController::buscarHistoricoDeAlteração($_2_u_lote_idlote, 'lote', 'qtdajust');
	$qtdhist = count($listaHistoricoFab);
	if ($qtdhist > 0) { ?>
		<div id="hist_qtdajust" style="display: none">
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
					<? foreach ($listaHistoricoFab as $historico) { ?>
						<tr>
							<? if ($historico['campo'] == "idunidadeest" || $historico['campo'] == "idunidadealerta") { ?>
								<td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor_old']); ?></label></td>
								<td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor']); ?></label></td>
							<? } else { ?>
								<td><?= $historico['valor_old'] ?></td>
								<td><?= $historico['valor'] ?></td>
							<? } ?>

							<td><?
								echo $historico['justificativa'];
								?></td>
							<td><?= $historico['nomecurto'] ?></td>
							<td><?= dmahms($historico['criadoem']) ?></td>
						</tr>
					<?
					} ?>
				</tbody>
			</table>
		</div>
	<?
	}

	$listaHistoricoFab = LoteController::buscarHistoricoDeAlteração($_2_u_lote_idlote, 'lote', 'qtdprod');
	$qtdhist = count($listaHistoricoFab);
	if ($qtdhist > 0) { ?>
		<div id="hist_qtdprod" style="display: none">
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
					<? foreach ($listaHistoricoFab as $historico) { ?>
						<tr>
							<? if ($historico['campo'] == "idunidadeest" || $historico['campo'] == "idunidadealerta") { ?>
								<td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor_old']); ?></label></td>
								<td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $historico['valor']); ?></label></td>
							<? } else { ?>
								<td><?= $historico['valor_old'] ?></td>
								<td><?= $historico['valor'] ?></td>
							<? } ?>

							<td><?
								echo $historico['justificativa'];
								?></td>
							<td><?= $historico['nomecurto'] ?></td>
							<td><?= dmahms($historico['criadoem']) ?></td>
						</tr>
					<?
					} ?>
				</tbody>
			</table>
		</div>
<?
	}
} ?>

<link href="../form/css/formalizacao_css.css?_<?= date("dmYhms") ?>" rel="stylesheet">
<div id="infadic" style="display: none">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<table>
						<tr>
							<td align="right">Descr.:</td>
							<td colspan="5">
								<input type="hidden" name="idlotelote" id="idlotelote" value="<?= $_2_u_lote_idlote ?>" vnulo>
								<textarea name="infadictext" id="infadictext" style="width: 422px; height: 194px; margin: 0px;"><?= $_2_u_lote_infadic ?></textarea>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row screen">
	<div class="col-md-12 loteativ" idloteativ="000000">
		<div class="panel panel-default" style="font-size: 11px;">
			<div class="panel-heading">
				<?
				$_listarModulos = FormalizacaoController::buscarModulosPorModuloEIdLp('ao', getModsUsr("LPS"));
				if ($_listarModulos['qtdLinhas'] > 0) {
					$click = "onclick='infadic(this)'";
				}
				?>
				<div class="row d-flex d-md-block flex-wrap align-items-end px-2 px-md-0 py-2">
					<?
					if ($_1_u_formalizacao_idformalizacao) {
					?>
						<div class="d-flex flex-column col-xs-1">
							<label>Formalização:</label>
							<div class="d-flex align-items-center form-valor">
								<label class="alert-warning"><?= $_1_u_formalizacao_idformalizacao ?></label>
							</div>
						</div>
					<?
					}
					?>
					<!-- Lote / Protocolo -->
					<div class="d-flex flex-column col-xs-2">
						<?
						if ($prodForm['tipo'] != 'SERVICO') {
						?>
							<label><strong><a href="?_modulo=ao&_acao=u&idlote=<?= $_2_u_lote_idlote ?>" target="_blank" style="color: inherit;">Lote:</a></strong></label>
							<div class="d-flex align-items-center form-valor">
								<?
								if ($_2_u_lote_partida) {
									$rowo = FormalizacaoController::buscarUnidadeObjetoPorModuloTipoEIdUnidade('modulo', 'lote', $_2_u_lote_idunidade);
									if (empty($rowo["idobjeto"])) {
										$modulo_lote = "loteproducao";
									} else {
										$modulo_lote = $rowo["idobjeto"];
									}
								?>
									<label class="alert-warning">
										<a href="javascript:janelamodal('?_modulo=<?= $modulo_lote ?>&_acao=u&idlote=<?= $_2_u_lote_idlote ?>')" title="Lote">
											<?= $_2_u_lote_partida ?>/<?= $_2_u_lote_exercicio ?>
										</a>
									</label>
									<input name="_2_<?= $_acao ?>_lote_partida" type="hidden" value="<?= $_2_u_lote_partida ?>" vnulo>
									<i title="Etiqueta da partida" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="showEtiquetas(1,<?= $_2_u_lote_idlote ?>)"></i>
								<?
								} else {
								?>
									<input type="hidden" name="_1_<?= $_acao ?>_formalizacao_status" id="formalizacao" value="<?= $_1_u_formalizacao_status ?>" vnulo>
									<label class="alert-warning"><?= $_2_u_lote_idlote ?></label>
								<? } ?>
							</div>
						<? } elseif ($prodForm['tipo'] = 'SERVICO') {
							$proc = traduzid('prproc', 'idprproc', 'proc', $_2_u_lote_idprproc);
						?>
							<label>Protocolo:</label>
							<div class="d-flex align-items-center form-valor">
								<input class="form-control" type="hidden" name="_2_<?= $_acao ?>_lote_idprodserv" value="<?= $_2_u_lote_idprodserv ?>">
								<?= $proc ?>
							</div>
						<? } ?>
						<input type="hidden" name="_2_<?= $_acao ?>_lote_idlote" id="idlote" value="<?= $_2_u_lote_idlote ?>" vnulo>
						<input type="hidden" name="_2_<?= $_acao ?>_lote_idunidade" id="idlote" value="<?= $_2_u_lote_idunidade ?>" vnulo>
						<input type="hidden" name="_2_<?= $_acao ?>_lote_exercicio" value="<?= $_2_u_lote_exercicio ?>">
						<input type="hidden" name="_1_<?= $_acao ?>_formalizacao_idformalizacao" id="idformalizacao" value="<?= $_1_u_formalizacao_idformalizacao ?>" vnulo>
						<input type="hidden" name="_1_<?= $_acao ?>_formalizacao_idunidade" id="idformalizacao" value="<?= $_1_u_formalizacao_idunidade ?>" vnulo>
						<input type="hidden" name="_1_<?= $_acao ?>_formalizacao_exercicio" value="<?= $_1_u_formalizacao_exercicio ?>" vnulo>
					</div>

					<!-- Prioridade -->
					<div class="d-flex flex-column col-xs-2">
						<label>Prioridade:</label>
						<div class="d-flex align-items-center form-valor">
							<select name="_2_<?= $_acao ?>_lote_prioridade" style="width: 100% !important">
								<? fillselect(FormalizacaoController::$prioridade, $_2_u_lote_prioridade); ?>
							</select>
						</div>
					</div>

					<!-- Responsavel pela Formalização -->
					<?
					if ($_1_u_formalizacao_status != "ABERTO") {
						$rowfluxo = FormalizacaoController::buscarPrimeiroFluxoTriagem($_1_u_formalizacao_idformalizacao);
						$arrayStatusResponsavel = array("TRIAGEM", "AGUARDANDO", "FORMALIZACAO", "PROCESSANDO", "QUARENTENA", "LIBERADO");
						if (!empty($_2_u_lote_idlote) && empty($_1_u_formalizacao_responsavel) && in_array($_1_u_formalizacao_status, $arrayStatusResponsavel) && $rowfluxo['idfluxostatus'] != $_1_u_formalizacao_idfluxostatus) {
					?>
							<div class="d-flex flex-column col-xs-2">
								<label>Resp. Formalização:</label>
								<div class="d-flex align-items-center form-valor">
									<input type="text" name="_1_<?= $_acao ?>_formalizacao_responsavel" vnulo cbvalue="<?= $_1_u_formalizacao_responsavel ?>" value="<?= $arrResponsavel[$_1_u_formalizacao_responsavel]["nome"] ?>" vnulo>
								</div>
							</div>
						<?
						} elseif ($rowfluxo['idfluxostatus'] != $_1_u_formalizacao_idfluxostatus) {
						?>
							<div class="d-flex flex-column col-xs-2">
								<label>Resp. Formalização:</label>
								<div class="d-flex align-items-center form-valor">
									<label class="alert-warning" id="statusButton">
										<?= traduzid('pessoa', 'idpessoa', 'nomecurto', $_1_u_formalizacao_responsavel); ?>
									</label>
								</div>
							</div>
						<?
						}
					}

					if (!empty($_2_u_lote_idlote)) {
						//Como está no status de Triagem o campo ficou obrigatório. Por isso será feita uma validação se LoteAtiv está preenchida
						$qtdLoteativ = FormalizacaoController::buscarQtdLoteAtivPorIdLote($_2_u_lote_idlote);
						if (($_1_u_formalizacao_status != "TRIAGEM" && $_1_u_formalizacao_status != "ABERTO") || ($qtdLoteativ == 0 && $_1_u_formalizacao_status == "TRIAGEM")) {
							$desablepr = "disabled='disabled'";
							$botaoeditar = "hide";
						}
						?>

						<!-- Início da Produção -->
						<div class="d-flex flex-column col-xs-2">
							<label>Início <? if ($prodForm['tipo'] != 'SERVICO') { ?> Produção <? } ?>:</label>
							<div class="d-flex align-items-center form-valor">
								<input size="6" type="hidden" name="_2_<?= $_acao ?>_lote_inicioprod" value="<?= $_2_u_lote_inicioprod ?>">
								<input size="6" type="hidden" name="lote_producaoold" value="<?= $_2_u_lote_producao ?>">

								<? if ($_2_u_lote_producao) { ?>
									<input <?= $desablepr ?> style=" background-color:#E0E0E0;" class="calendario size9" id="dataproducao" type="text" name="_2_<?= $_acao ?>_lote_producao" value="<?= $_2_u_lote_producao ?>" vnulo autocomplete="off" disabled='disabled'>
								<? } else { ?>
									<input <?= $desablepr ?> class="calendario size9" id="dataproducao" type="text" name="_2_<?= $_acao ?>_lote_producao" value="<?= $_2_u_lote_producao ?>" vnulo autocomplete="off">
								<? } ?>

								<? if ($_2_u_lote_idlote) { ?>
									<i class="fa btn-sm fa-pencil preto pointer <?= $botaoeditar ?>" onclick="alteravalor('producao','<?= $_2_u_lote_producao ?>','modulohistorico',<?= $_2_u_lote_idlote ?>,'Início da Produção:',true)"></i>
									<i class="fa fa-info-circle preto pointer tip " onclick="modalhist('hist_producao')"></i>
								<? } ?>
							</div>
						</div>
					<?
					}
					?>

					<!-- Previsão de Envio -->
					<div class="d-flex flex-column col-xs-1">
						<label>Previsão Envio:</label>
						<div class="d-flex align-items-center form-valor">
							<?
							if (!empty($_1_u_formalizacao_envio)) {
							?>
								<font color="red"><?= dma($_1_u_formalizacao_envio) ?></font>
								<?
							} elseif (!empty($_2_u_lote_idlote)) {
								$_listarEnvioLote = FormalizacaoController::buscarEnvioLoteReservaPorIdLote($_2_u_lote_idlote, 'nfitem');
								$qtden = count($_listarEnvioLote);
								if ($qtden > 0) {
								?>
									<font color="red"><?= dma($_listarEnvioLote['envio']) ?></font>
							<?
								}
							}
							?>
						</div>
					</div>

					<!-- Status -->
					<div class="d-flex flex-column col-xs-1">
						<label>Status:</label>
						<div class="d-flex align-items-center form-valor">
							<?
							if ($_2_u_lote_idlote and (($prodForm["especial"] == "Y" and !empty($_2_u_lote_idsolfab)) or ($prodForm["especial"] == "N"))) {
								if ($prodForm["tipo"] == "SERVICO") {
									$arraSlote = array('TRIAGEM' => 'Aberto', 'PROCESSANDO' => 'Processando', 'QUARENTENA' => 'Emissão', 'APROVADO' => 'Concluido', 'CANCELADO' => 'Cancelado');
								} else {
									$arrayStatus2 = array("ABERTO", "FORMALIZACAO", "TRIAGEM", "PROCESSANDO", "AGUARDANDO");
									if (in_array($_1_u_formalizacao_status, $arrayStatus2)) {
										if ($_2_u_lote_statusao == "PENDENTE") {
											// status AGUARDANDO somente para produto especial
											if ($prodForm["especial"] == "Y") {
												if ($_1_u_formalizacao_status == "ABERTO") {
													$arraSlote = array('ABERTO' => 'Aberto', 'AGUARDANDO' => 'Aguardando Autorização', 'CANCELADO' => 'Cancelado');
												} elseif ($_1_u_formalizacao_status == "AGUARDANDO") {
													$arraSlote = array('ABERTO' => 'Aberto', 'AGUARDANDO' => 'Aguardando Autorização', 'TRIAGEM' => 'Triagem', 'CANCELADO' => 'Cancelado');
												} else {
													$arraSlote = array('ABERTO' => 'Aberto', 'AGUARDANDO' => 'Aguardando Autorização', 'TRIAGEM' => 'Triagem', 'FORMALIZACAO' => 'Formalização', 'CANCELADO' => 'Cancelado');
												}
											} else {
												if ($_1_u_formalizacao_status == "ABERTO") {
													$arraSlote = array('ABERTO' => 'Aberto', 'TRIAGEM' => 'Triagem', 'CANCELADO' => 'Cancelado');
												} else {
													$arraSlote = array('ABERTO' => 'Aberto', 'TRIAGEM' => 'Triagem', 'FORMALIZACAO' => 'Formalização', 'CANCELADO' => 'Cancelado');
												}
											}
										} else {
											// status AGUARDANDO somente para produto especial
											if ($prodForm["especial"] == "Y") {
												if ($_1_u_formalizacao_status == "ABERTO") {
													$arraSlote = array('ABERTO' => 'Aberto', 'AGUARDANDO' => 'Aguardando Autorização', 'CANCELADO' => 'Cancelado');
												} elseif ($_1_u_formalizacao_status == "AGUARDANDO") {
													$arraSlote = array('ABERTO' => 'Aberto', 'AGUARDANDO' => 'Aguardando Autorização', 'TRIAGEM' => 'Triagem', 'CANCELADO' => 'Cancelado');
												} else {
													$arraSlote = array('ABERTO' => 'Aberto', 'AGUARDANDO' => 'Aguardando Autorização', 'TRIAGEM' => 'Triagem', 'FORMALIZACAO' => 'Formalização', 'PROCESSANDO' => 'Processando', 'CANCELADO' => 'Cancelado');
												}
											} else {
												if ($_1_u_formalizacao_status == "ABERTO") {
													$arraSlote = array('ABERTO' => 'Aberto', 'TRIAGEM' => 'Triagem', 'CANCELADO' => 'Cancelado');
												} else {
													$arraSlote = array('ABERTO' => 'Aberto', 'TRIAGEM' => 'Triagem', 'FORMALIZACAO' => 'Formalização', 'PROCESSANDO' => 'Processando', 'CANCELADO' => 'Cancelado');
												}
											}
										}
									} else {
										$arraSlote = array('QUARENTENA' => 'Quarentena', 'APROVADO' => 'Aprovado', 'REPROVADO' => 'Reprovado', 'CANCELADO' => 'Cancelado');
									}
								}
							?>
								<input type="hidden" name="oldstatus" value="<?= $_1_u_formalizacao_status ?>">
								<input type="hidden" name="_1_<?= $_acao ?>_formalizacao_status" id="status-formalizacao" value="<?= $_1_u_formalizacao_status ?>" vnulo>
								<? $rotulo = getStatusFluxo('formalizacao', 'idformalizacao', $_1_u_formalizacao_idformalizacao) ?>
								<label class="alert-warning" title="<?= $_1_u_formalizacao_status ?>" id="statusButton">
									<?= mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?>
								</label>
								<?
							} else {
								if ($_2_u_lote_idlote) {
								?>
									<label class="alert-warning"><?= $_1_u_formalizacao_status ?></label>
							<?
								}
							}
							?>
						</div>
					</div>
					<div class="d-flex flex-container col-xs-1">
						<? if (array_key_exists("lancarinsumos", getModsUsr("MODULOS"))) { ?>
							<div class="lancar-perda d-flex flex-column col-xs-6">
								<i class="fa fa-cubes fa-2x fade pointer hoverazul  btn-lg pointer" onclick="lancarInsumo()" title="Lançar Insumos"></i>
							</div>
						<? } ?>
						<?
						$arrayStatusIN = array("APROVADO", "CANCELADO");
						if (!in_array($_1_u_formalizacao_status, $arrayStatusIN)) {
						?>
							<div class="lancar-perda d-flex flex-column col-xs-6">
								<i class="fa fa-arrow-circle-o-down fa-2x fade pointer hoverazul  btn-lg pointer" onclick="lancarPerda()" title="Lançar perda"></i>
							</div>
						<?
						}
						?>
						<div class="imprimirFormalizacao d-flex flex-column col-xs-6">
							<i class="fa fa-print fa-2x  fade pointer hoverazul  btn-lg pointer" onclick="imprimir()" data-test="download" title="Imprimir Formalização"></i>
						</div>
					</div>
				</div>
				<?
				if ((!empty($_2_u_lote_idlote) && $prodForm['tipo'] != 'SERVICO' && $_GET['_acao'] == 'u') || $_GET['_acao'] == 'i') {
				?>
					<div class="row d-flex d-md-block flex-wrap align-items-end px-2 px-md-0 py-2">
						<!-- Produto -->
						<div class="d-flex flex-column col-xs-6">
							<label>Produto:</label>
							<?
							if (empty($_2_u_lote_idlote)) {
							?>
								<div class="d-flex align-items-center form-valor">
									<? $vidprodservformulains = (empty($_2_u_lote_idprodservformulains) && $_2_u_lote_idprodservformulains !== "0") ? "0" : $_2_u_lote_idprodservformulains; ?>
									<input type="hidden" name="_2_<?= $_acao ?>_lote_idprodservformulains" value="<?= $vidprodservformulains ?>">
									<input class="form-control" type="text" name="_2_<?= $_acao ?>_lote_idprodserv" id="_lote_idprodserv" vnulo cbvalue="<?= $_2_u_lote_idprodserv ?>" value="<?= $arrProd[$_2_u_lote_idprodserv]["descr"] ?>" style="width: 52em;">
									<?
									if (empty($_2_u_lote_idlote)) {
									?>
										<span class="input-group-addon pointer hoverazul" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?= $_2_u_lote_idprodserv ?>')" title="Editar Produto #<?= $_2_u_lote_idprodserv ?>">
											<i class="fa fa-bars pointer"></i>
										</span>
									<?
									}
									?>
								</div>
							<?
							} else {
							?>
								<div class="d-flex align-items-center form-valor">
									<? $vidprodservformulains = (empty($_2_u_lote_idprodservformulains) && $_2_u_lote_idprodservformulains !== "0") ? "0" : $_2_u_lote_idprodservformulains; ?>
									<input type="hidden" name="_2_<?= $_acao ?>_lote_idprodservformulains" value="<?= $vidprodservformulains ?>">
									<label class="preto" style="max-width: 96%;"><?= $prodForm["descr"] ?></label>
									<input class="form-control" type="hidden" name="_2_<?= $_acao ?>_lote_idprodserv" value="<?= $_2_u_lote_idprodserv ?>">
									<span class="pointer hoverazul pdLeftOito" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?= $_2_u_lote_idprodserv ?>')" title="Editar Produto #<?= $_2_u_lote_idprodserv ?>">
										<i class="fa fa-bars pointer"></i>
									</span>
								</div>
							<?
							}
							?>
						</div>
						<?
						if ($prodForm['tipo'] != 'SERVICO') {
						?>
							<!-- Qtd. Sol.: -->
							<div class="d-flex flex-column col-xs-2">
								<label>Qtd. Sol:</label>
								<div class="d-flex align-items-center form-valor">
									<?
									$arrayStatus = array("PROCESSANDO", "APROVADO", "REPROVADO", "ESGOTADO", "CANCELADO");
									if (in_array($_1_u_formalizacao_status, $arrayStatus)) {
										$peddisabled = "disabled='disabled'";
									}
									$valorQtdSol = recuperaExpoente(tratanumero($_2_u_lote_qtdpedida), $_2_u_lote_qtdpedida_exp);
									$valorQtdSol = $valorQtdSol == "NULL" ? "" : $valorQtdSol;
									?>

									<? if ($valorQtdSol) { ?>
										<input style=" background-color:#E0E0E0;" class="size9" type="text" name="_2_<?= $_acao ?>_lote_qtdpedida" value="<?= $valorQtdSol ?>" vnulo autocomplete="off" readonly='readonly'>
									<? } else { ?>
										<input <?= $peddisabled ?> name="_2_<?= $_acao ?>_lote_qtdpedida" size="4" type="text" value="<?= $valorQtdSol; ?>" vnulo>
									<? } ?>
									<? if ($_2_u_lote_idlote) { ?>
										<i class="fa btn-sm fa-pencil preto pointer " onclick="alteravalor('qtdpedida','<?= $valorQtdSol ?>','modulohistorico',<?= $_2_u_lote_idlote ?>,'Qtd. Sol:',false)"></i>
										<i class="fa fa-info-circle preto pointer tip " onclick="modalhist('hist_qtdpedida')"></i>
									<? } ?>

								</div>
							</div>
						<?
						}
						?>
						<!-- Formula -->
						<div class="d-flex flex-column col-xs-4">
							<label>Fórmula:</label>
							<div class="d-flex align-items-center form-valor div-formula">
								<?
								$arrayStatusTipo = array('FORMALIZACAO', 'TRIAGEM', 'AGUARDANDO', 'ABERTO', 'AUTORIZADO');
								if (!empty($_2_u_lote_idprodservformula)) {
									//pegar da informação congelada no lote
									if (!empty($_2_u_lote_rotuloform)) {
										$rowfo['rotulo'] = $_2_u_lote_rotuloform;
									} else {
										//monta a infomação da formula do produto
										$rowfo = FormalizacaoController::buscarRotuloFormulaPorId($_2_u_lote_idprodservformula);
									}
									echo $rowfo['rotulo'];
								?>
									<i id="modalloteformulains" class="fa fa-edit azul pointer pdLeftOito" title="Editar Formula deste lote"></i>
								<?
								} elseif (in_array($_1_u_formalizacao_status, $arrayStatusTipo) && empty($_2_u_lote_idprodservformula)) {
								?>
									<select name="lote_idprodservformula" id="lote_idprodservformula" style="width: 38% !important;" onchange="buscarSolfab(this)">
										<option></option>
										<!--mostra a formula do produto-->
										<? fillselect(FormalizacaoController::buscarFormulaAtivaPorProdserv($_2_u_lote_idprodserv)); ?>
									</select>
								<?
								}
								?>
							</div>
						</div>
					</div>
				<?
				}

				$displayNone = $_GET['_acao'] == 'i' ? 'display:none !important;' : '';
				if (($prodForm["especial"] == "Y" && $_GET['_acao'] == 'u') || $_GET['_acao'] == 'i') {
					$solfab = empty($_2_u_lote_idsolfab) ? "" : $_2_u_lote_idsolfab . "-" . $arrClientesSolFab[$_2_u_lote_idpessoa][$_2_u_lote_idsolfab]["codprodserv"] . "/ " . $arrClientesSolFab[$_2_u_lote_idpessoa][$_2_u_lote_idsolfab]["exercicio"];
				?>
					<div class="row d-flex d-md-block flex-wrap align-items-end px-2 px-md-0 py-2">
						<!-- Cliente -->
						<div class="d-flex flex-column col-xs-6 div_cliente" style="<?= $displayNone ?>">
							<label>Cliente:</label>
							<div class="d-flex align-items-center form-valor">
								<?
								if ($_1_u_formalizacao_status == 'AGUARDANDO' || $_1_u_formalizacao_status == 'ABERTO'  || empty($_2_u_lote_idpessoa)) {
								?>
									<input type="text" name="_2_<?= $_acao ?>_lote_idpessoa" id="_lote_idpessoa" vnulo cbvalue="<?= $_2_u_lote_idpessoa ?>" value="<?= $arrCli[$_2_u_lote_idpessoa]["nome"] ?>" vnulo style="width: 52em;">
								<? } else { ?>
									<label class="alert-warning"><?= $arrCli[$_2_u_lote_idpessoa]["nome"] ?></label>
									<input type="hidden" name="_2_<?= $_acao ?>_lote_idpessoa" id="_lote_idpessoa" vnulo cbvalue="<?= $_2_u_lote_idpessoa ?>">
								<? } ?>
							</div>
						</div>
						<!-- S. Fabricação -->
						<div class="d-flex flex-column col-xs-3 div_sol_fab" style="<?= $displayNone ?>">
							<label>Solicitação de Vacinas Autógenas:</label>
							<div class="d-flex align-items-center form-valor">
								<?
								if ($_1_u_formalizacao_status == 'AGUARDANDO' || $_1_u_formalizacao_status == 'ABERTO' || empty($_2_u_lote_idsolfab)) {
								?>
									<input class="form-control" type="text" name="_2_<?= $_acao ?>_lote_idsolfab" id="_lote_idsolfab" cbvalue="<?= $_2_u_lote_idsolfab ?>" value="<?= $solfab ?>" style="width:300px;" vnulo autocomplete="off">
								<?
								} elseif (!empty($_2_u_lote_idsolfab)) {
									$solfab = FormalizacaoController::buscarSolfabJoinLotePorIdSolfab($_2_u_lote_idsolfab);
								?>
									<label class="alert-warning"><? echo ($solfab["idsolfab"] . "-" . $solfab["partida"] . "/" . $solfab["exercicio"]); ?></label>
								<?
								}
								?>
								<span class="input-group-addon pointer hoverazul flex-center" onclick="janelamodal('?_modulo=solfab&_acao=u&idsolfab=<?= $_2_u_lote_idsolfab ?>')" title="Editar Solicitação de Vacinas Autógenas #<?= $_2_u_lote_idsolfab ?>">
									<i class="fa fa-bars pointer"></i>
								</span>
								<span class="input-group-addon pointer hoververde flex-center" onclick="geraSolfab()" title="Nova Solicitação de Vacinas Autógenas">
									<i class="fa fa-plus-circle pointer"></i>
								</span>
							</div>
						</div>
						<?

						if (!empty($_2_u_lote_idsolfab)) {
							$rodtaprov = FormalizacaoController::buscarDataAprovacaoSolfab($_2_u_lote_idsolfab);
							if (!empty($rodtaprov["dataaprovacao"])) {
						?>
								<!-- Data Aprovação -->
								<div class="d-flex flex-column col-xs-3">
									<label>Data Aprovação:</label>
									<div class="d-flex align-items-center form-valor">
										<?= dma($rodtaprov["dataaprovacao"]); ?>
									</div>
								</div>
						<?
							}
						}
						?>
					</div>
				<?
				}

				$unidadeVolume = traduzid("unidadevolume", "un", "descr", $prodForm["un"]);
				?>

				<div class="row d-flex d-md-block flex-wrap align-items-end px-2 px-md-0">
					<!-- Qtd. Ajust -->
					<div class="d-flex flex-column col-xs-2">
						<label>Qtd. Ajust:</label>
						<div class="d-flex align-items-center form-valor">
							<?
							$valorQtdajust = recuperaExpoente(tratanumero($_2_u_lote_qtdajust), $_2_u_lote_qtdajust_exp);
							$valorQtdajust = $valorQtdajust == "NULL" ? "" : $valorQtdajust;
							?>
							<? if ($valorQtdajust) { ?>
								<input style=" background-color:#E0E0E0;" class="size10 mgRightOito" type="text" name="_2_<?= $_acao ?>_lote_qtdajust" value="<?= $valorQtdajust ?>" vnulo autocomplete="off" readonly='readonly'>
							<? } else { ?>
								<input <?= $peddisabled ?> name="_2_<?= $_acao ?>_lote_qtdajust" class="size10 mgRightOito" type="text" value="<?= $valorQtdajust ?>" vnulo>
							<? } ?>
							<?= $unidadeVolume ?>
							<? if ($_2_u_lote_idlote) { ?>
								<i class="fa btn-sm fa-pencil preto pointer " onclick="alteravalor('qtdajust','<?= $valorQtdajust ?>','modulohistorico',<?= $_2_u_lote_idlote ?>,'Qtd. Ajust:',false)"></i>
								<i class="fa fa-info-circle preto pointer tip " onclick="modalhist('hist_qtdajust')"></i>
							<? } ?>


						</div>
					</div>

					<!-- Qtd. Prod -->
					<div class="d-flex flex-column col-xs-2">
						<label>Qtd. Prod:</label>
						<div class="d-flex align-items-center form-valor">
							<?
							$arrayStatusAjust = array("ABERTO", "AGUARDANDO", "FORMALIZACAO", "TRIAGEM");
							if (!empty($_1_u_formalizacao_status) or !in_array($_1_u_formalizacao_status, $arrayStatusAjust)) {
								$_2_u_lote_qtdprod = (empty($_2_u_lote_qtdprod) or $_2_u_lote_qtdprod == 0) ? $_2_u_lote_qtdpedida : $_2_u_lote_qtdprod;
								$_2_u_lote_qtdprod_exp = (empty($_2_u_lote_qtdprod_exp) or $_2_u_lote_qtdprod_exp == 0) ? $_2_u_lote_qtdpedida_exp : $_2_u_lote_qtdprod_exp;
								$strnul = "vnulo";
								$valprod = recuperaExpoente($_2_u_lote_qtdprod, $_2_u_lote_qtdprod_exp);
								$valprod = $valprod;
							}
							?>
							<? if ($valprod) { ?>
								<input style=" background-color:#E0E0E0;" class="size10 mgRightOito" type="text" name="_2_<?= $_acao ?>_lote_qtdprod" value="<?= $valprod ?>" <?= $strnul ?> autocomplete="off" readonly='readonly'>
							<? } else { ?>
								<input name="_2_<?= $_acao ?>_lote_qtdprod" class="size10 mgRightOito" type="text" value="<?= $valprod ?>" <?= $strnul ?>>
							<? } ?>
							<?= $unidadeVolume ?>
							<? if ($_2_u_lote_idlote) { ?>
								<i class="fa btn-sm fa-pencil preto pointer " onclick="alteravalor('qtdprod','<?= $valprod ?>','modulohistorico',<?= $_2_u_lote_idlote ?>,'Qtd. Prod:',false)"></i>
								<i class="fa fa-info-circle preto pointer tip " onclick="modalhist('hist_qtdprod')"></i>
							<? } ?>

						</div>
					</div>

					<?
					//Calcular o volume da formula
					if (!empty($_2_u_lote_idprodservformula)) {
						$rowvol = FormalizacaoController::buscarVolumeEQtdProdservFormula($_2_u_lote_idprodservformula);
						$volumeform = (empty($rowvol['volumeform']) or $rowvol['volumeform'] == 0) ? 0 : $rowvol['volumeform'];
					?>
						<!-- Volume Prod -->
						<div class="d-flex flex-column col-xs-1">
							<label>Volume Prod:</label>
							<div class="d-flex align-items-center form-valor">
								<?
								if (strpos(strtolower($rowvol['qtdpadrao_exp']), "d")) {
									$arrExp = explode('d', strtolower($rowvol['qtdpadrao_exp']));
									$vqtdpadrao = $arrExp[0];
								} elseif (strpos(strtolower($rowvol['qtdpadrao_exp']), "e")) {
									$arrExp = explode('e', strtolower($rowvol['qtdpadrao_exp']));
									$vqtdpadrao =  $arrExp[0];
								} else {
									$vqtdpadrao = (empty($rowvol['qtdpadrao']) or $rowvol['qtdpadrao'] == 0) ? 1 : $rowvol['qtdpadrao'];
								}

								if (strpos(strtolower($_2_u_lote_qtdajust_exp), "d")) {
									$arrExp = explode('d', strtolower($_2_u_lote_qtdajust_exp));
									$volumeprod = $arrExp[0];
								} elseif (strpos(strtolower($_2_u_lote_qtdajust_exp), "e")) {
									$arrExp = explode('e', strtolower($_2_u_lote_qtdajust_exp));
									$volumeprod =  $arrExp[0];
								} else {
									$volumeprod = tratanumero($_2_u_lote_qtdajust);
								}
								$strcalc = "[(" . $volumeprod . "*" . $volumeform . ")/" . $vqtdpadrao . "]";
								echo '<!-- ' . $strcalc . '-->';
								$volumeprod = ($volumeprod * $volumeform) / $vqtdpadrao;

								?>
								<input name="_2_<?= $_acao ?>_lote_volumeprod" size="4" type="hidden" value="<?= $volumeprod ?>">
								<label class="alert-warning" title="<?= $strcalc ?>"><?= round($volumeprod, 4) ?></label>
								&nbsp;&nbsp;<?= $prodFormula['unformula'] ?>
							</div>
						</div>

						<!-- Volume Cons -->
						<div class="d-flex flex-column col-xs-1">
							<label>Volume Cons:</label>
							<div class="d-flex align-items-center form-valor">
								<label class="alert-warning" title="<?= $arrLotecons['strcalc'] ?>"><?= round($arrLotecons['volumeconsf'], 4) ?></label>
								&nbsp;&nbsp;<?= $prodFormula['unformula'] ?>
							</div>
						</div>

						<?
						if ($_2_u_lote_idlote  && $_1_u_formalizacao_status != "ABERTO" && $_1_u_formalizacao_status != "AGUARDANDO") {
						?>
							<!-- Fabricação -->
							<div class="d-flex flex-column col-xs-2">
								<label>Fabricação:</label>
								<div class="d-flex align-items-center form-valor">
									<input type="hidden" name="fabricacao_old" value="<?= $_2_u_lote_fabricacao ?>">

									<? if ($_2_u_lote_fabricacao) { ?>
										<input style=" background-color:#E0E0E0;" class="calendario size9" type="text" name="_2_<?= $_acao ?>_lote_fabricacao" value="<?= $_2_u_lote_fabricacao ?>" vnulo autocomplete="off" disabled='disabled'>
										<input type="hidden" name="_2_<?= $_acao ?>_lote_fabricacao" value="<?= $_2_u_lote_fabricacao ?>" />
									<? } else { ?>
										<input class="calendario size10 fabricacao" size="6" type="text" name="_2_<?= $_acao ?>_lote_fabricacao" value="<?= $_2_u_lote_fabricacao ?>">
									<? } ?>


									<? if ($_2_u_lote_idlote) { ?>
										<i class="fa btn-sm fa-pencil preto pointer " onclick="alteravalor('fabricacao','<?= $_2_u_lote_fabricacao ?>','modulohistorico',<?= $_2_u_lote_idlote ?>,'Fabricação:',true)"></i>
										<i class="fa fa-info-circle preto pointer tip " onclick="modalhist('hist_fabricacao')"></i>
									<? } ?>
								</div>
							</div>

							<!-- Vencimento -->
							<div class="d-flex flex-column col-xs-2">
								<label>Vencimento:</label>
								<div class="d-flex align-items-center form-valor">
									<?
									$arrayStatusValidade = array('APROVADO', 'CANCELADO', 'ESGOTADO', 'REPROVADO', 'RETIDO');
									if ($prodForm['validade']) {
										if ($_2_u_lote_fabricacao) {
											$fabricacao = explode("/", $_2_u_lote_fabricacao);
											$dia = ($fabricacao[0] == 31) ? 30 : $fabricacao[0];
											$fabricacao = $fabricacao[2] . "-" . $fabricacao[1] . "-" . $dia;
											$_2_u_lote_vencimento = date('d/m/Y', strtotime("+" . $prodForm['validade'] . " MONTH", strtotime($fabricacao)));
										} else {
											$_2_u_lote_vencimento = "";
										}
									?>
										<label class="alert-warning vencimento" title="<?= $_1_u_formalizacao_status ?>" id="statusButton">
											<?= $_2_u_lote_vencimento ?>
										</label>
										<input type="hidden" name="validade" value="<?= $prodForm['validade'] ?>">
										<?
										if (!in_array($_1_u_formalizacao_status, $arrayStatusValidade)) {
										?>
											<input class="vencimento" type="hidden" name="_2_<?= $_acao ?>_lote_vencimento" value="<?= $_2_u_lote_vencimento ?>">
										<?
										}
									} else {
										?>
										<input class="calendario" size="6" type="text" name="_2_<?= $_acao ?>_lote_vencimento" value="<?= $_2_u_lote_vencimento ?>">
									<? } ?>
								</div>
							</div>
							<div class="d-flex flex-column col-xs-2">
								<label style="color: red !important;">Validade:</label>
								<label class="alert-warning" style="color: red !important;"><?= $prodForm['validade'] ?> Meses</label>
								<?
								if (!in_array($_1_u_formalizacao_status, $arrayStatusValidade)) {
								?>
									<inpu type="hidden" name="_1_<?= $_acao ?>_formalizacao_vencimento" value="<?= $prodForm['validade'] ?>">
									<?
								}
									?>
							</div>
					<?
						}
					}
					?>
				</div>
				<?
				if (($prodForm['tipo'] != 'SERVICO' && $_GET['_acao'] == 'u') || $_GET['_acao'] == 'i') {
				?>
					<!-- Observação -->
					<div class="row d-flex d-md-block flex-wrap align-items-end px-2 px-md-0 py-2 div_observacao" style="<?= $displayNone ?>">
						<div class="d-flex flex-column col-xs-12">
							<label>Observação:</label>
							<div class="d-flex align-items-center form-valor">
								<textarea cols="120" rows="4" name="_2_<?= $_acao ?>_lote_observacao"><?= $_2_u_lote_observacao ?></textarea>
							</div>
						</div>
					</div>
				<?
				}
				?>
			</div>
		</div>
	</div>
</div>

<?
if (!empty($_2_u_lote_idprodservformula) && !empty($_2_u_lote_idpessoa) && !empty($_2_u_lote_idsolfab) && ($_1_u_formalizacao_status == "ABERTO" || $_1_u_formalizacao_status == 'AGUARDANDO' || $_1_u_formalizacao_status == 'TRIAGEM')) {
	$resf = FormalizacaoController::buscarLotePorProdservFormula($_2_u_lote_idprodservformula, $_2_u_lote_idpessoa, $_2_u_lote_idsolfab);
	$qtdsf = count($resf);
	if ($qtdsf > 0) {
?>
		<div class="row screen">
			<div class="col-md-12">
				<div class="panel panel-default ">
					<div class="panel-heading">Sementes que não estão na Solicitação de Vacinas Autógenas</div>
					<div class="panel-body alert-info">
						<? foreach ($resf as $rowf) { ?>
							<span>
								<a href="javascript:janelamodal('?_modulo=semente&_acao=u&idlote=<?= $rowf["idlote"] ?>')" title="Lote">
									<?= $rowf["partida"] ?>/<?= $rowf["exercicio"] ?>&nbsp;&nbsp;
								</a>
							</span>
						<? } ?>
					</div>
				</div>
			</div>
		</div>
<?
	} //if($qtdsf>0){
}
?>

<table class="table100">
	<!-- 
		Esta table permite a utilização dos recursos THEAD e TFOOT, que fazem com que o 
		cabeçalho e o rodapé se repitam em todas as páginas
		O chrome ainda tem um Bug relacionado ao footer, que ainda não foi resolvido
	-->
	<thead>
		<tr>
			<th class="print tituloPagina">
				<? if (!empty($_2_u_lote_idempresa)) { ?>
					<div class="logoSup">
						<?
						$figrel = FormalizacaoController::buscarCaminhoImagemTipoHeaderProduto($_2_u_lote_idempresa);
						$figrel["caminho"] = str_replace("../", "", $figrel["caminho"]);
						?>
						<img src="<?= $figrel["caminho"] ?>">
					</div>
				<? } ?>
				<div class="tituloDoc" style="font-size: 0.4cm;">
					<? if ($_2_u_lote_tipo == 'SERVICO') { ?>PROTOCOLO<? } else { ?>ORDEM DE PRODUÇÃO<? } ?>
					<?= $_1_u_formalizacao_idformalizacao ?>

					<div class="cinza quebralinha" style="font-size: 0.4cm;">
						<?= $prodForm["descr"] ?> <? if ($_1_u_formalizacao_idempresa == 15) {
														echo $_2_u_lote_idprodserv;
													} ?>
					</div>
				</div>
				<div class="tituloAdicional"></div>
			</th>
		</tr>
		<?
		if ($_2_u_lote_tipoobjetoprodpara == 'bioensaio') {
			$roqb = FormalizacaoController::buscarSolfabPorIdLote($_2_u_lote_idlote);
		?>
			<th class="print cabecalhoPagina">
				<hr>
				<table>
					<tr>
						<td>Empresa:</td>
						<td colspan="2"><?= $roqb["nome"] ?></td>
						<td>N&ordm; Registro:</td>
						<td colspan="2"><?= $roqb["idregistro"] ?> / <?= $roqb["exercicio"] ?></td>
					</tr>
					<tr>
						<td>Estudo:</td>
						<td colspan="5"><?= $roqb["bioensaio"] ?></td>
					</tr>
					<tr>
						<td colspan="6">
							<hr>
						</td>
					</tr>
					<tr>
						<td>Tipo:</td>
						<td><?= $roqb["especie"] ?>-<?= $roqb["finalidade"] ?></td>
						<td>Nº <?= $roqb["especie"] ?>:</td>
						<td><?= $roqb["qtd"] ?></td>
						<td>Cor da Anilha:</td>
						<td><?= $roqb["coranilha"] ?></td>
					</tr>
					<tr>
						<td>Nascimento:</td>
						<td><?= dma($roqb["nascimento"]) ?></td>
						<td>Alojamento:</td>
						<td><?= dma($roqb["alojamento"]) ?></td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td colspan="6">
							<hr>
						</td>
					</tr>
					<tr>
						<td>Produto:</td>
						<td><?= $roqb["produto"] ?></td>
						<td>Partida:</td>
						<td><?= $roqb["partida"] ?></td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td>Vol. Aplicado:</td>
						<td><?= $roqb["volume"] ?></td>
						<td>Nº Doses:</td>
						<td><?= $roqb["doses"] ?></td>
						<td>Via:</<td>
						<td><?= $roqb["via"] ?></td>
					</tr>
					<tr>
						<td colspan="6">
							<hr>
						</td>
					</tr>
				</table>
			</th>
			<br>
		<?
		} else {
		?>
			<tr>
				<th class="print cabecalhoPagina">
					<hr>
					<table>
						<tr>
							<td>Código Produto:</td>
							<td>
								<?
								if ($_2_u_lote_piloto == 'Y') {
									echo ("PP ");
								}
								echo $_2_u_lote_spartida;
								?>
							</td>
							<td>Forma Farmacêutica:</td>
							<td><?= $arrProd[$_2_u_lote_idprodserv]["formafarm"] ?></td>
						</tr>
						<tr>
							<td>Partida:</td>
							<td>
								<? //se for partida pilote tem PP na frente
								if ($_2_u_lote_piloto == 'Y') {
									echo ("PP");
								}
								?>
								<?= str_pad($_2_u_lote_npartida, 3, "0", STR_PAD_LEFT); ?> / <?= substr($_2_u_lote_exercicio, 2, 2) ?>
							</td>
							<td>Qtd. Pedida:</td>
							<td><?= recuperaExpoente(tratanumero($_2_u_lote_qtdpedida), $_2_u_lote_qtdpedida_exp) ?> <?= traduzid("unidadevolume", "un", "descr", $prodForm["un"]) ?>.</span>
								<? if ($_2_u_lote_idprodservformula > 0) { ?>
									<span>
										<span class="rot">Formula:</span>
										<?
										$row = FormalizacaoController::buscarRotuloFormulaPorId($_2_u_lote_idprodservformula);
										echo $row['rotulo'];
										?>
									</span>
								<? } ?>
							</td>
						</tr>
						<tr>
							<td>Volume Produzido:</td>
							<td>
								<? $procsubtipo = traduzid('prproc', 'idprproc', 'subtipo', $_1_u_formalizacao_idprproc);
								if (in_array($procsubtipo, ['VACINA', 'VACINACOMERCIALINATIVA', 'VACINACOMERCIALVIVA', 'VACINAAUTOGENA'])) {
									echo $_2_u_lote_volumeprod . " " . strtoupper($rowvol['un']);
								}
								?>
							</td>
							<?
							//Conforme orientação de Maxsuel
							if ($_1_u_formalizacao_status == "CANCELADO" or $_1_u_formalizacao_status == "REPROVADO") { ?>
								<td>Status:</td>
								<td><?= $_1_u_formalizacao_status; ?></td>
							<? } ?>

						</tr>
					</table>
					<hr>
				</th>
			</tr>
		<? } //if($_2_u_lote_tipoobjetoprodpara=='bioensaio'){
		?>
	</thead>
	<tbody>
		<? if ($_2_u_lote_tipoobjetoprodpara != 'bioensaio') { ?>
			<tr>
				<th class="print cabecalhoPagina">
					<table>
						<tr>
							<td>Data Fabricação:</td>
							<td><? //=$_2_u_lote_fabricacao
								?></td>
							<td>Data Vencimento:</td>
							<td><? //=$_2_u_lote_vencimento
								?></td>
						</tr>
						<tr>
							<td>Qtd. Produzida:</td>
							<td></td>
						</tr>
					</table>
					<hr>
				</th>
			</tr>
		<? } ?>
		<tr>
			<td>
				<div class="row">
					<div id="alertaStatus" class="alert alert-warning <?= $_1_u_formalizacao_status ?> print">
						<span class="notProducao"><i class="fa fa-exclamation-triangle"></i>&nbsp;Formalização não está em produção: Impressão não permitida!</span>
					</div>
					<div id="corpoFormalizacao" class="col-md-12 <?= $_1_u_formalizacao_status ?>"></div>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<div class="print row mt-3">
					<div id="qrcode-formalizacao" class="col-xs-12"></div>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<?
if (!empty($_2_u_lote_idlote)) {
	$_listarAssinatura = FormalizacaoController::buscarAssinaturaPessoa("'ATIVO','PENDENTE'", 'formalizacao', $_2_u_lote_idlote);
	$existe = $_listarAssinatura['qtdLinhas'];
	if ($existe > 0) {
?>
		<div class="row screen">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">Assinaturas</div>
					<div class="panel-body">
						<table class="planilha grade compacto">
							<tr>
								<th>Funcionários</th>
								<th>Data Assinatura</th>
								<th>Status</th>
							</tr>
							<?
							foreach ($_listarAssinatura as $assinaturas) {
							?>
								<tr class="res">
									<td nowrap><?= $assinaturas["nome"] ?></td>
									<td nowrap><?= $assinaturas["dataassinatura"] ?></td>
									<td nowrap><?= $assinaturas["status"] ?></td>
								</tr>
							<?
							}
							?>
						</table>
					</div>
				</div>
			</div>
		</div>
<?
	} //if($existe>0){ 
} //if(!empty($_2_u_atendimento_idatendimento)){

?>
<script src=".\inc\js\jquery\vanilla-masker.js"></script>
<div class="screen loteativ" idloteativ="000001">
	<?
	if (!empty($_1_u_formalizacao_idlote)) { // trocar p/ cada tela a tabela e o id da tabela
		$_idModuloParaAssinatura = $_1_u_formalizacao_idlote; // trocar p/ cada tela o id da tabela
		require '../form/viewAssinaturas.php';
	}
	$tabaud = "formalizacao"; //pegar a tabela do criado/alterado em antigo
	require '../form/viewCriadoAlterado.php';
	?>
</div>
<? require_once(__DIR__ . "/js/formalizacao_js.php"); ?>