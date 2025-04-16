<?
require_once("../inc/php/validaacesso.php");
require_once("../model/prodserv.php");
require_once("../form/controllers/calculoestoque_controller.php");
require_once("../form/controllers/planejamentoprodserv_controller.php");

//Chama a Classe prodserv
$prodservclass = new PRODSERV();

if ($_POST) {
	include_once("../inc/php/cbpost.php");
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
$pagsql = "SELECT p.*, u.flgpotencia FROM prodserv p LEFT JOIN unidadevolume u ON u.un = p.un WHERE p.idprodserv = #pkid";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$idprodservformula = $_GET['idprodservformula'];
if ($_1_u_prodserv_fabricado == 'Y' && empty($idprodservformula)) {
	//@529271 - ERRO GERENCIAMENTO DE ESTOQUE
	$idprodservformula = CalculoEstoqueController::buscarIdprodservFormula($_1_u_prodserv_idprodserv);
}
?>
<script src="./inc/js/amcharts/amcharts.js"></script>
<script src="./inc/js/amcharts/serial.js"></script>
<link href="../form/css/calculoestoque_css.css?_<?= date("dmYhms") ?>" rel="stylesheet">
<?
$tabela = 'prodserv';
if (!empty($idprodservformula)) {
	$tabela = 'prodservformula';
	$comformula = 'Y';
	$calculoEstoque = CalculoEstoqueController::buscarCalculoEstoqueProdservComFormula($idprodservformula)['dados'];
	$_qtdpa = CalculoEstoqueController::buscarQtdpaComFormula($idprodservformula, $_1_u_prodserv_idprodserv)['dados'];
} else {
	$tabela = 'prodserv';
	$comformula = 'N';
	$calculoEstoque = CalculoEstoqueController::buscarCalculoEstoqueProdserv($_1_u_prodserv_idprodserv)['dados'];
	$_qtdpa = CalculoEstoqueController::buscarQtdpa($_1_u_prodserv_idprodserv)['dados'];
}
?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table>
					<tr>
						<td>ID:</td>
						<td>
							<label class="alert-warning"><?= $_1_u_prodserv_idprodserv ?></label>
						</td>
						<td align="right"><strong>Sigla:</strong></td>
						<td nowrap>
							<label class="alert-warning"><?= $_1_u_prodserv_codprodserv ?></label>
						</td>
						<td align="right"><strong>Descrição:</strong></td>
						<td nowrap>
							<label class="alert-warning"><?= $_1_u_prodserv_descr ?>
								<a title="Abrir Prodserv" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=prodserv&idprodserv=<?= $_1_u_prodserv_idprodserv ?>" target="_blank" style="margin: 0 4px;"></a>
							</label>
						</td>
					</tr>
				</table>
			</div>
			<?
			if (!empty($idprodservformula)) {
			?>
				<ul class="nav nav-tabs panel" id="Tab_lp" role="tablist">
					<?
					if (!empty($idprodservformula)) {
						$lsitarRotulo = CalculoEstoqueController::buscarRotuloProdservComRorulo($_1_u_prodserv_idprodserv);
						$formuladotab = "Y";
					} else {
						$lsitarRotulo = CalculoEstoqueController::buscarRotuloProdserv($_1_u_prodserv_idprodserv);
						$formuladotab = "N";
					}

					$arrLpGrupo = array();
					foreach ($lsitarRotulo as $rotulo) {
						if (!empty($idprodservformula)) {
							if ($rotulo['id'] == $idprodservformula) {
								$classac = "class='active'";
							} else {
								$classac = "";
							}
						} else {
							$classac = "class='active'";
						}
					?>
						<li id="idunidade_navtab_<?= $rotulo['id'] ?>" role="presentation panel-heading" <? if (!empty($idprodservformula)) { ?> onclick="carregarCalculo(<?= $rotulo['id'] ?>,<?= $_1_u_prodserv_idprodserv ?>,'<?= $formuladotab ?>')" <? } ?> <?= $classac ?>>
							<a href="#idprodservformula_<?= $rotulo['id'] ?>" idprodserv="<?= $_1_u_prodserv_idprodserv ?>" tab="<?= $rotulo['id'] ?>" role="tab" data-toggle="tab">
								<?= $rotulo['rotulo'] ?>
							</a>
						</li>
					<? } ?>
				</ul>
			<? } ?>
			<div class="panel-body" id="corpocal" style="background-color:white; padding-top: 5px !important;">
				<div class="row">
					<div class="col-sm-12">
						<div class="panel panel-default">
							<div class="panel-heading " style="display: inline-flex; align-items: center; width: 100%; justify-content: space-between;">
								<div>
									<?
									if ($tabela == 'prodservformula') {
									?> Informações Gerais - <?= $calculoEstoque['rotulo'] ?><?
																						} else {
																							echo "Informações Gerais"; /*$_1_u_prodserv_descr*/
																						} ?>

										<input name="_1_<?= $_acao ?>_<?= $tabela ?>_id<?= $tabela ?>" id="id<?= $tabela ?>" type="hidden" value="<?= $calculoEstoque['id'] ?>" vnulo>
										<input type="hidden" id="<?= $tabela ?>_qtdest" value="<?= $calculoEstoque['vqtdest'] ?>">
										<input type="hidden" id="<?= $tabela ?>_qtdpa" value="<?= $_qtdpa['qtdpa'] ?>">
								</div>
							</div>
							<div class="panel-body" style="padding-top: 2px !important;">
								<? if (is_null($idprodservformula) && $_1_u_prodserv_fabricado == "Y") {
								?>
									<div class="row">
										<div class="col-md-12">
											<div class="alert alert-warning" role="alert">O item está configurado como formulado, mas não possuí uma fórmula cadastrada.</div>
										</div>
									</div>
								<? } else { ?>
									<div class="col-sm-6">
										<?
										$mediadiaria = CalculoEstoqueController::buscarMediaDiaria($calculoEstoque['idprodserv'], $calculoEstoque['idunidadeest'], $idprodservformula, 60);
										$pedidoauto = $mediadiaria * $calculoEstoque['temporeposicao'];
										$strcal = " (media diaria x tempo compra) + (estoque minimo - estoque)";
										$estmin = floatval(str_replace(",", ".", $calculoEstoque['estmin']));
										$valor1 = (round(floatval($mediadiaria) * floatval($calculoEstoque['tempocompra']), 2));
										$valor2 = (round((floatval($estmin) - floatval($calculoEstoque['vqtdest'])), 2));
										$numv = ($valor1 + $valor2);
										$strsugcp = "(" . $mediadiaria . " * " . $calculoEstoque['tempocompra'] . " ) + (" . $estmin . " - " . $calculoEstoque['vqtdest'] . ")";
										$sugcp = tratanumerovisualizacao($numv);
										?>
										<div class="col-sm-12">
											<div class="panel panel-default">
												<div class="panel-heading" style="height:36px;display: inline-flex; align-items: center; width: 100%; justify-content: space-between;">
													Configurações do Estoque
												</div>
												<div class="panel-body" style="padding-top: 2px !important;">
													<table style="width:100%">
														<?
														$idobjeto = ($idprodservformula > 0 ? $idprodservformula : $_1_u_prodserv_idprodserv);
														?>
														<tr>
															<!-- Estoque -->
															<td style="text-align: right;">Estoque:</td>
															<td class="size10">
																<label class="alert-warning ">
																	<?
																	if (!empty($calculoEstoque["vqtdest"])) {
																		if (
																			strpos(strtolower($calculoEstoque['qtdpadrao_exp']), "d")
																			or strpos(strtolower($calculoEstoque['qtdpadrao_exp']), "e")
																		) {
																			echo recuperaExpoente(tratanumero($calculoEstoque["vqtdest"]), $calculoEstoque['qtdpadrao_exp']);
																		} else {
																			echo number_format(tratanumero($calculoEstoque["vqtdest"]), 2, ',', '.');
																		}
																	} else {
																		echo number_format(tratanumero(0), 2, ',', '.');
																	}
																	?>
																	<?= $_1_u_prodserv_un; ?>
																</label>
															</td>

															<!-- Estoque Mínimo: -->
															<td style="text-align: right;" class="td-label-inline">
																<span title="Quantidade mínima para alerta comprar ou produzir">Estoque Mínimo:</span>
																<img src="/form/img/icon-hist.svg" class="ml-2 pointer timeout-hist" alt="Icone histórico" title="Histórico do Estoque Mínimo" onClick="Hestmim('estmin',<?= $idobjeto ?>,'de Alteração de Estoque Mínimo');" />
															</td>
															<td class="idestmin">
																<input name="_estmin_anterior_" class="size7" type="hidden" value="<?= $calculoEstoque['estmin'] ?>">
																<?
																if (empty($calculoEstoque["estmin_exp"])) {
																?>
																	<input type="text" readonly='readonly' style="background-color: #f2f2f2;" name="" class="size8" value="<?= number_format(tratanumero($calculoEstoque['estmin']), 2, ',', '.') ?>">
																<?
																} else {
																?>
																	<input type="text" readonly='readonly' style="background-color: #f2f2f2;" name="" class="size8" value="<?= recuperaExpoente($calculoEstoque['estmin'], $calculoEstoque["estmin_exp"]) ?>">
																<?
																}

																if (empty($calculoEstoque["estmin_exp"])) {
																	$estminautomatico = number_format(tratanumero($calculoEstoque['estminautomatico']), 2, ',', '.');
																} else {
																	$estminautomatico = recuperaExpoente($calculoEstoque['estminautomatico'], $calculoEstoque["estmin_exp"]);
																}
																?>
																<input id="<?= $tabela ?>_estminautomatico" type="hidden" value="<?= $calculoEstoque['estminautomatico'] ?>" disabled />

																<i class="fa fa-pencil btn-lg pointer" title='Editar Estoque Mínimo' onclick="alteravalor('estmin','<?= recuperaExpoente($calculoEstoque['estmin'], $calculoEstoque['estmin_exp']) ?>','<?= $tabela ?>',<?= $idobjeto ?>,'Estoque Mínimo')"></i>
																&nbsp;&nbsp;<label class="alert-warning"><span title="Mínimo Automático = Média Diária * Tempo de Reposição * Tempo de Segurança"><?= $estminautomatico; ?></span></label>
															</td>
														</tr>
														<tr>
															<!-- Dias em Estoque -->
															<td style="text-align: right;">Dias em Estoque:</td>
															<td>
																<?
																if (!empty($calculoEstoque['idunidadeest']) and $calculoEstoque['vqtdest'] > 0) {
																	$mediadiaria = CalculoEstoqueController::buscarMediaDiaria($_1_u_prodserv_idprodserv, $calculoEstoque['idunidadeest'], $calculoEstoque['idprodservformula'], $calculoEstoque['consumodias']);
																	echo "<!-- estoque: " . $calculoEstoque['vqtdest'] . " -->";
																	echo "<!-- mediadiaria: " . $mediadiaria . " -->";
																	if ($mediadiaria > 0 and $calculoEstoque['vqtdest'] > 0) {
																		echo (number_format(tratanumero($calculoEstoque['vqtdest'] / $mediadiaria), 2, ',', '.'));
																	} else {
																		echo '0,00';
																	}
																}
																?>
															</td>
															<!-- Tempo de compra -->
															<td style="text-align: right;" class="td-label-inline">
																<span title="Frequência da compra em dias">Tempo de Compra:</span>
																<img src="/form/img/icon-hist.svg" class="ml-2 pointer timeout-hist" alt="Icone histórico" onClick="Hestmim('tempocompra',<?= $idobjeto ?>,'de Alteração de Tempo de Compra');" title="Histórico do Tempo de Compra" />
															</td>
															<td>
																<input type="text" readonly='readonly' style="background-color: #f2f2f2;" name="" class="size8" value="<?= number_format(tratanumero($calculoEstoque['tempocompra']), 2, ',', '.') ?>">
																<i class="fa fa-pencil btn-lg pointer" onclick="alteravalor('tempocompra','<?= $calculoEstoque['tempocompra'] ?>','<?= $tabela ?>',<?= $idobjeto ?>,'Tempo de Compra')" title='Editar Tempo de Compra'></i>
															</td>
														</tr>

														<tr>
															<!-- Média Diária -->
															<td style="text-align: right;" title="Consumo últimos <?= $calculoEstoque['consumodias'] ?> dias/<?= $calculoEstoque['consumodias'] ?>">Média Diária (60):</td>
															<td>
																<?
																if (empty($calculoEstoque["estmin_exp"])) {
																	echo number_format(tratanumero($mediadiaria), 2, ',', '.');
																} else {
																	echo recuperaExpoente($mediadiaria, $calculoEstoque["estmin_exp"]);
																}
																?>
																<input class="size15" type="hidden" disabled style="background-color: #E6E6E6;" value="<?= $mediadiaria ?>">
															</td>
															<!-- Tempo de Reposição -->
															<td style="text-align: right;" title="Prazo de negociação + Entrega do produto" class="td-label-inline">
																<span>Tempo de Reposição</span>
																<img src="/form/img/icon-hist.svg" class="ml-2 pointer timeout-hist" alt="Icone histórico" title="Históricode de Tempo de Reposição" onClick="Hestmim('temporeposicao',<?= $idobjeto ?>,'de Alteração de Tempo de Reposição');" />
															</td>
															<td>
																<input type="text" readonly='readonly' style="background-color: #f2f2f2;" name="" class="size8" value="<?= number_format(tratanumero($calculoEstoque['temporeposicao']), 2, ',', '.') ?>">
																<i class="fa fa-pencil btn-lg pointer" onclick="alteravalor('temporeposicao','<?= $calculoEstoque['temporeposicao'] ?>','<?= $tabela ?>',<?= $idobjeto ?>,'Tempo de Reposição')" title='Editar Tempo de Reposição'></i>
															</td>
														</tr>

														<tr>
															<!-- Sugestão Compra -->
															<td title="<?= $strcal ?>" style="text-align: right;">Sugestão de Compra:</td>
															<td title="<?= $strsugcp ?>">
																<span id="sugestaocompra">
																	<?
																	if (empty($calculoEstoque["estmin_exp"])) {
																		echo number_format(tratanumero($sugcp), 2, ',', '.');;
																	} else {
																		echo recuperaExpoente($sugcp, $calculoEstoque["estmin_exp"]);
																	} ?>
																</span>
															</td>

															<!-- Tempo de Segurança: -->
															<td style="text-align: right;" class="td-label-inline">
																<span title="Prazo de segurança">Tempo de Segurança:</span>
																<img src="/form/img/icon-hist.svg" class="ml-2 pointer timeout-hist" alt="Icone histórico" title="Histórico de Tempo de Segurança" onClick="Hestmim('estoqueseguranca',<?= $idobjeto ?>,'de Alteração de Tempo de Segurança');" />
															</td>
															<td>
																<input type="text" readonly='readonly' style="background-color: #f2f2f2;" name="" class="size8" value="<?= number_format(tratanumero($calculoEstoque['estoqueseguranca']), 2, ',', '.') ?>">
																<i class="fa fa-pencil btn-lg pointer" onclick="alteravalor('estoqueseguranca','<?= $calculoEstoque['estoqueseguranca'] ?>','<?= $tabela ?>',<?= $idobjeto ?>,'Sugestão Compra')" title='Editar Sugestão Compra'></i>
															</td>
														</tr>

														<!-- Estocado em: -->
														<tr>
															<!-- Sugestão Compra -->
															<td title="<?= $strcal . '- Qtd pedida' ?>" style="text-align: right;">Sugestão de Compra 2:</td>
															<td title="<?= $strsugcp . '-' . $_qtdpa['qtdpa'] ?>">
																<span id="sugestaocompra">
																	<?
																	if (empty($calculoEstoque["estmin_exp"])) {
																		echo number_format(tratanumero($numv - $_qtdpa['qtdpa']), 2, ',', '.');
																	} else {
																		echo recuperaExpoente($numv - $_qtdpa['qtdpa'], $calculoEstoque["estmin_exp"]);
																	} ?>
																</span>
															</td>
															<td style="text-align: right;" title="Tempo em dias de Consumo para Rateio" class="td-label-inline">
																<span>Tempo de Rateio</span>
																<img src="/form/img/icon-hist.svg" class="ml-2 pointer timeout-hist" alt="Icone histórico" title="Tempo em dias de Consumo para Rateio" onClick="Hestmim('tempoconsrateio',<?= $idobjeto ?>,'de Alteração de Tempo de Consumo para Rateio');" />
															</td>
															<td>
																<input type="text" readonly='readonly' style="background-color: #f2f2f2;" name="" class="size5" value="<?= number_format(tratanumero($calculoEstoque['tempoconsrateio']), 2, ',', '.') ?>">
																<i class="fa fa-pencil btn-lg pointer" onclick="alteravalor('tempoconsrateio','<?= $calculoEstoque['tempoconsrateio'] ?>','<?= $tabela ?>',<?= $idobjeto ?>,'Tempo de Consumo para Rateio')" title='Editar Tempo de Consumo para Rateio'></i>
															</td>
														</tr>

														<tr>
															<? if ($_1_u_prodserv_prioridadecompra == 'ALTA') {

																$agora = new DateTime(); // Pega o momento atual
																$criadoem = explode(" ", $agora->format('Y-m-d H:i'));
																$dataCriadoEm = explode("-", $criadoem[0]);
																$planejamentoProdserv = PlanejamentoProdServController::buscarPlanejamentoPorIdProdservMesExercio($_1_u_prodserv_idprodserv, $dataCriadoEm[0], $dataCriadoEm[1]);
																$totalplan = 0;
																$html = '';
																$qtdplan = count($planejamentoProdserv);
																foreach ($planejamentoProdserv as $_pl) {
																	$totalplan = $totalplan + $_pl['planejado'];
																	$html = $html . "
																	<tr>
																		<td>" . $_pl['unidade'] . "</td>
																		<td>" . number_format(tratanumero($_pl['planejado']), 2, ',', '.') . "</td>
																		<td>" . number_format(tratanumero($_pl['adicional']), 2, ',', '.') . "</td>	
																		<td>" . number_format(tratanumero($_pl['valor']), 2, ',', '.') . "</td>																		
																	</tr>
																	";
																}

															?>
																<td style="text-align: right;" title="Quantidades inseridas no planejamento de consumo." class="td-label-inline">
																	<span>Planejamento de Consumo:</span>
																	<i title="Quantidades inseridas no planejamento de consumo." class="fa  btn-sm fa-info-circle preto pointer hoverazul tip" onClick="planejamento('Planejamento(s) de Consumo do Produto');"></i>

																	<div id="planejamento" style="display: none">
																		<table class="table table-hover">
																			<?

																			if ($qtdplan > 0) {
																			?>
																				<thead>
																					<tr>
																						<th scope="col">Unidade</th>
																						<th scope="col">Planejado</th>
																						<th scope="col">Adicional</th>
																						<th scope="col">Planejado + Adicional</th>
																					</tr>
																				</thead>
																				<tbody>
																					<?
																					echo ($html);
																					?>
																				</tbody>
																			<?
																			}
																			?>
																		</table>
																	</div>


																</td>
																<td>
																	<input type="text" readonly='readonly' style="background-color: #f2f2f2;" name="" class="size8" value="<?= number_format(tratanumero($totalplan), 2, ',', '.') ?>">
																</td>
															<? } else { ?>
																<td></td>
																<td></td>
															<? } ?>
															<td style="text-align: right;" class="td-label-inline">
																<span>Estocado em:</span>
																<img src="/form/img/icon-hist.svg" class="ml-2 pointer timeout-hist" alt="Icone histórico" title="Histórico de Alteração da unidade" onClick="Hestmim('idunidadeest',<?= $idobjeto ?>,'de Alteração de Unidade');" />
															</td>
															<td class="nowrap">
																<? $fillIdUnidadeEst =  CalculoEstoqueController::buscarUnidadesPorTipoObjetoModulo($_1_u_prodserv_idprodserv, getidempresa("u.idempresa", 'unidade')); ?>
																<select name="_1_<?= $_acao ?>_<?= $tabela ?>_idunidadeest" vnulo id='desunidadeest1' class='desunidadeest size15' disabled>
																	<option value=""></option>
																	<? fillselect($fillIdUnidadeEst, $calculoEstoque['idunidadeest']); ?>
																</select>
																<i class="fa fa-pencil btn-lg pointer" onclick="alteravalor('idunidadeest', '<?= $calculoEstoque['idunidadeest'] ?>', '<?= $tabela ?>', <?= $idobjeto ?>, 'Unidade')" title='Editar unidade de estoque'></i>
															</td>
														</tr>

														<!-- Alerta -->
														<tr>
															<td></td>
															<td></td>
															<td style="text-align: right;" class="td-label-inline">
																<span>Alerta:</span>
																<img src="/form/img/icon-hist.svg" class="ml-2 pointer timeout-hist" alt="Icone histórico" title="Histórico de unidade de alerta" onClick="Hestmim('idunidadealerta',<?= $idobjeto ?>,'de Alteração de Unidade Alerta');" />
															</td>
															<td class="nowrap">
																<? $fillIdUnidadeAlerta =  CalculoEstoqueController::buscarUnidadesPorTipoObjeto($_1_u_prodserv_idprodserv, 'prodserv', getidempresa('u.idempresa', 'unidade')); ?>
																<select name="_1_<?= $_acao ?>_<?= $tabela ?>_idunidadealerta" class='desunidadeest size15' id='idunidadealerta1' disabled>
																	<option value=""></option>
																	<? fillselect($fillIdUnidadeAlerta, $calculoEstoque['idunidadealerta']); ?>
																</select>
																<i class="fa fa-pencil btn-lg pointer" onclick="alteravalor('idunidadealerta','<?= $calculoEstoque['idunidadealerta'] ?>','<?= $tabela ?>',<?= $idobjeto ?>,'Unidade Alerta')" title='Editar unidade de alerta'></i>
															</td>
														</tr>

													</table>
												</div>
											</div>

											<div class="panel panel-default">
												<div class="panel-heading"><a id="modalfornecedor" class="point">Fornecedor</a></div>
											</div>
										</div>
									</div>
									<? ///historico alteracao
									$arrayhist = array('estmin', 'temporeposicao', 'estoqueseguranca', 'consumodias', 'tempocompra', 'idunidadeest', 'idunidadealerta', 'tempoconsrateio');
									foreach ($arrayhist as $vh => $vcampo) {
										if ($vcampo == 'temporeposicao') {
											$sqlca = CalculoEstoqueController::$_tempoReposicao;
										} elseif ($vcampo == 'estoqueseguranca') {
											$sqlca = CalculoEstoqueController::$_estoqueseguranca;
										} elseif ($vcampo == 'consumodias') {
											$sqlca = CalculoEstoqueController::$_consumodias;
										} elseif ($vcampo == 'tempocompra') {
											$sqlca = CalculoEstoqueController::$_tempocompra;
										} elseif ($vcampo == 'idunidadeest') {
											$sqlca = $fillIdUnidadeEst;
										} elseif ($vcampo == 'idunidadealerta') {
											$sqlca = $fillIdUnidadeAlerta;
										} else {
											$sqlca = "";
										}
									?>
										<div id="<?= $vcampo ?><?= $idobjeto ?>" style="display: none">
											<table class="table table-hover">
												<?
												if ($idprodservformula != "") {
													$listarProdservhistorico = CalculoEstoqueController::buscarHistoricoProdservPessoaPorIdProdservFormula($idprodservformula, $vcampo);
													$qtProdservHist = count($listarProdservhistorico);
												} else {
													$listarProdservhistorico = CalculoEstoqueController::buscarHistoricoProdservPessoaPorIdProdserv($idprodserv, $vcampo);
													$qtProdservHist = count($listarProdservhistorico);
												}

												if ($qtProdservHist > 0) {
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
														foreach ($listarProdservhistorico as $prodservhistorico) {
														?>
															<tr>
																<? if ($prodservhistorico['campo'] == "idunidadeest" || $prodservhistorico['campo'] == "idunidadealerta") { ?>
																	<td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $prodservhistorico['valor_old']); ?></label></td>
																	<td><label class="alert-warning"><?= traduzid("unidade", "idunidade", "unidade", $prodservhistorico['valor']); ?></label></td>
																<? } else { ?>
																	<td><?= $prodservhistorico['valor_old'] ?></td>
																	<td><?= $prodservhistorico['valor'] ?></td>
																<? } ?>

																<td><?
																	if ($prodservhistorico['justificativa'] == 'CONSUMO AUTOMATICO') echo 'Alteração baseada no cálculo de consumo do sistema';
																	elseif ($prodservhistorico['justificativa'] == 'PROJECAO DE CONSUMO') echo 'Alteração baseada na previsão de aumento/diminuição informada via evento';
																	else echo $prodservhistorico['justificativa'];
																	?></td>
																<td><?= $prodservhistorico['nomecurto'] ?></td>
																<td><?= dmahms($prodservhistorico['criadoem']) ?></td>
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
										<div id="alt<?= $vcampo ?><?= $idobjeto ?>" style="display: none">
											<table class="table table-hover">
												<tr>
													<td>#namerotulo:</td>
													<td>
														<? if (empty($sqlca)) { ?>
															<input name="#name_campo" value="#valor_campo" class="size7" type="text">
														<? } else { ?>
															<select id="ndroptipo" class="size10" name="#name_campo">
																<? fillselect($sqlca); ?>
															</select>
														<? } ?>
													</td>
												</tr>

												<tr>
													<td>Justificativa:</td>
													<td>
														<select name="#name_justificativa" onchange="alteraoutros(this,'<?= $tabela ?>')" vnulo class="size50">
															<? fillselect(CalculoEstoqueController::$_justificativa); ?>
														</select>
													</td>
												</tr>
											</table>
										</div>
									<?
									} //foreach ($arrayhist as $vh => $vcampo) {
									//fim historico
									?>
									<div class="col-sm-6">
										<div class="col-sm-12">
											<div class="panel panel-default">
												<div class="panel-heading" style="display: inline-flex; align-items: center; width: 100%; justify-content: space-between;">
													<span>
														Consumo(s) nos últimos
														<select name="_1_<?= $_acao ?>_<?= $tabela ?>_consumodiaslote" onchange="CB.post()">
															<? fillselect(CalculoEstoqueController::$_consumodias, $calculoEstoque['consumodiaslote']); ?>
														</select>
													</span>
													<span style="float: right;">
														<? if ($comformula == 'N') { ?>
															<a id="lotetodosmodal" validprodserv='<?= $_1_u_prodserv_idprodserv ?>' class="fa fa-cubes hoverazul  pointer" title="Mostrar todos os lotes do produto"></a>
														<? } else { ?>
															<a id="lotetodosforumulamodal" validprodserv='<?= $_1_u_prodserv_idprodserv ?>' validprodservformula='<?= $calculoEstoque['idprodservformula'] ?>' class="fa fa-cubes hoverazul btn-lg pointer" title="Mostrar todos os lotes do produto"></a>
														<? } ?>
													</span>
												</div>
												<div class="panel-body">
													<div class="panel panel-default">
														<div class="panel-heading" data-toggle="collapse" href="#consumolote" style="display: inline-flex; align-items: center; width: 100%; justify-content: space-between;">Consumo(s) do lote</div>
														<div class="panel-body" id="consumolote">
															<table class="table table-striped planilha">
																<?
																$listarLotex = CalculoEstoqueController::buscarLoteConsComSolmatItemPorIdUnidade($_1_u_prodserv_idprodserv, $calculoEstoque['idunidadeest'], $idprodservformula, $calculoEstoque['consumodiaslote']);
																$numrow2 = count($listarLotex);
																$consumounidade = array();
																$arrlotesrateio = array();
																$totalqtddrateio = 0;
																$linhar = 0;
																if ($numrow2 > 0) {
																	$totalqtdd = 0;
																?>
																	<tr>
																		<th>Em</th>
																		<th>Partida</th>
																		<th>Crédito</th>
																		<th>Débito</th>
																		<th>Un</th>
																		<th>Destino</th>
																		<th>Por</th>
																		<th>Obs.</th>
																	</tr>
																	<?
																	foreach ($listarLotex as $lotex) {
																		$linhar = $linhar + 1;
																		$chavek = $lotex['idlotecons'] + $linhar;
																		if ($lotex['status'] == 'INATIVO') {
																			$color = 'background-color: #d6d6d6; opacity: 0.5;';
																			$title = 'INATIVO';
																		} else if ($lotex['status'] == 'DEVOLUCAO') {
																			$color = 'color: red;';
																			$title = 'DEVOLUÇÃO';
																		} else {
																			$color = '';
																			$title = '';
																		}

																		$aqtdd = $lotex["qtdd"];
																		$aqtdc = $lotex["qtdc"];
																		if ($lotex["tipoobjeto"] == 'lote' && !empty($lotex["idobjeto"])) {
																			$rowlote = CalculoEstoqueController::buscarUnidadeLotePorIdLote($lotex["idobjeto"]);
																			$destino = $rowlote["unidade"];
																			$idunidaderat = $rowlote['idunidade'];
																			$link = "?_modulo=" . $rowlote["modulo"] . "&_acao=u&idlote=" . $rowlote["idloteorigem"];
																		} elseif ($lotex["tipoobjeto"] == 'lotefracao' && !empty($lotex["idobjeto"])) {

																			$rowlote = CalculoEstoqueController::buscarUnidadeLotePorIdLoteFracao($lotex["idobjeto"]);
																			$destino = $rowlote["unidade"];
																			$idunidaderat = $rowlote['idunidade'];
																			$link = "?_modulo=" . $rowlote["modulo"] . "&_acao=u&idlote=" . $lotex["idlote"];
																		} elseif ($lotex['tipoobjeto'] == 'nfitem' and !empty($lotex["idobjeto"])) {

																			$rowlote = CalculoEstoqueController::buscarNfePorIdNfItem($lotex['idobjeto']);
																			$destino = "NF=" . $rowlote['idnf'];
																			$idunidaderat = $rowlote['idunidade'];
																			$link = "?_modulo=pedido&_acao=u&idnf=" . $rowlote['idnf'];
																		} elseif ($lotex['tipoobjeto'] == 'resultado' and !empty($lotex["idobjeto"])) {

																			$rowlote = CalculoEstoqueController::buscarAmostraPorIdResultado($lotex['idobjeto'])['dados'];
																			$modResultadosPadrao = getModuloResultadoPadrao($rowlote['idunidade']);
																			$destino = $rowlote['descr'];
																			$idunidaderat = $rowlote['idunidade'];
																			$link = "?_modulo=" . $modResultadosPadrao . "&_acao=u&idresultado=" . $rowlote['idobjeto'];
																		} elseif (empty($lotex["idobjeto"])) {

																			$rowlote = CalculoEstoqueController::buscarUnidadeModuloPorTipoObjetoParaLote($calculoEstoque['idunidadeest']);

																			if ($lotex['qtdd'] > 0) {
																				$destino = 'Retirada';
																			} else {
																				$destino = 'Adição';
																			}

																			$idunidaderat = $calculoEstoque['idunidadeest'];
																			$link = "?_modulo=" . $rowlote["modulo"] . "&_acao=u&idlote=" . $lotex["idlote"];
																		}
																		if ($lotex['status'] != 'INATIVO' and $lotex['status'] != 'DEVOLUCAO') {
																			$totalqtdd += $aqtdd;
																			$totalqtdc += $aqtdc;
																		}

																		echo "<!-- " . $link . " -->";
																		$output = parse_url($link);
																		parse_str($output['query'], $vquery);

																	?>
																		<tr style="<?= $color ?>" title="<?= $title ?>">
																			<td><?= dma($lotex["criadoem"]) ?></td>
																			<td>
																				<?
																				if (!empty($vquery['_modulo'])) {
																				?>
																					<a onclick="janelamodal('<?= $link ?>')"><?= $lotex["partida"] ?>/<?= $lotex["exercicio"] ?></a>
																				<?
																					$modulolink = "<a onclick=\"janelamodal('" . $link . "')\">" . $lotex["partida"] . "/" . $lotex["exercicio"] . "</a>";
																				} else {
																					echo ($lotex["partida"] . "/" . $lotex["exercicio"]);
																					$modulolink = $lotex["partida"] . "/" . $lotex["exercicio"];
																				}
																				?>
																			</td>
																			<td style="text-align: right;">
																				<?
																				if ($aqtdc > 0) {
																					if (empty($lotex["qtdprod_exp"])) {
																						echo number_format(tratanumero($aqtdc), 2, ',', '.');
																					} else {
																						echo recuperaExpoente(tratanumero($aqtdc), $lotex["qtdprod_exp"]);
																					}
																				} else {
																					echo ("---");
																				} ?>
																			</td>
																			<td style="text-align: right;">
																				<?
																				if ($aqtdd > 0) {
																					if (empty($lotex["qtdprod_exp"])) {
																						echo number_format(tratanumero($aqtdd), 2, ',', '.');
																					} else {
																						echo recuperaExpoente(tratanumero($aqtdd), $lotex["qtdprod_exp"]);
																					}
																				} else {
																					echo ("---");
																				}
																				?>
																			</td>
																			<td><?= $lotex['unpadrao'] ?></td>
																			<td>
																				<?
																				if (!empty($destino)) {
																					echo $destino;
																				} else {
																					echo '-';
																				}
																				?></td>
																			<td><?= dma($lotex["criadopor"]) ?></td>
																			<td>
																				<?
																				$linkobs = $lotex["idsolmat"] ? "<a  onclick=\"janelamodal('?_modulo=solmat&_acao=u&idsolmat=" . $lotex["idsolmat"] . "')\" title='Solicitação de material'>" . $lotex["obs"] . "</a>" : $lotex["obs"];
																				echo ($linkobs);
																				?>
																			</td>
																		</tr>
																	<?
																	}

																	$totalsoma = $totalqtdd;
																	?>
																	<tr>
																		<td title="Total débito - Total Crédito">Total:</td>
																		<td></td>
																		<td title="Crédito" style="color:black; font-weight:bold">
																			<? if ($totalqtdc > 0) {
																				if (empty($calculoEstoque["qtdpadrao_exp"])) {
																					echo number_format(tratanumero($totalqtdc), 2, ',', '.');
																				} else {
																					echo recuperaExpoente(tratanumero($totalqtdc), $calculoEstoque["qtdpadrao_exp"]);
																				}
																			} else {
																				echo ("---");
																			} ?>
																		</td>
																		<td title="Débito" style="color:black; font-weight:bold">
																			<? if ($totalqtdd > 0) {
																				if (empty($calculoEstoque["qtdpadrao_exp"])) {
																					echo number_format(tratanumero($totalqtdd), 2, ',', '.');
																				} else {
																					echo recuperaExpoente(tratanumero($totalqtdd), $calculoEstoque["qtdpadrao_exp"]);
																				}
																			} else {
																				echo ("---");
																			} ?>
																		</td>
																		<td><?= $_1_u_prodserv_un ?></td>
																		<td></td>
																		<td></td>
																		<td></td>
																	</tr>
																	<tr>
																		<td colspan="7"><span title="Conforme selecionado acima (<?= $calculoEstoque['consumodiaslote'] ?>)">Média diaria em <?= $calculoEstoque['consumodiaslote'] ?> dias: <?= number_format(tratanumero($totalsoma / $calculoEstoque['consumodiaslote']), 2, ',', '.') ?> <?= $_1_u_prodserv_un ?></span></td>
																	</tr>
																<?
																} else { ?>
																	<tr>
																		<td>
																			Não houve consumo nos últimos <span style="color:black; font-weight:bold"><?= $calculoEstoque['consumodiaslote'] ?></span> dias
																		</td>
																	</tr>
																<? } ?>
															</table>
														</div>
													</div>
													<?
													if ($_1_u_prodserv_comprado == 'Y') {
														$_reslotemeio = CalculoEstoqueController::buscarLoteMeios($_1_u_prodserv_idprodserv, $calculoEstoque['idunidadeest'], $calculoEstoque['tempoconsrateio']);
														$numrowmeio = count($_reslotemeio);

														if ($numrowmeio > 0 or count($arrlotesrateio) > 0) {
													?>
															<div class="panel panel-default">
																<div class="panel-heading" data-toggle="collapse" href="#rateio" style="display: inline-flex; align-items: center; width: 100%; justify-content: space-between;">Rateio</div>
																<div class="panel-body" id="rateio">
																	<table class="table table-striped planilha">
																		<tr>
																			<th>Em</th>
																			<th>Partida</th>
																			<th>Crédito</th>
																			<th>Débito</th>
																			<th>Un</th>
																			<th>Destino</th>
																			<th>Por</th>
																			<th>Obs.</th>
																		</tr>
																		<?
																		foreach ($_reslotemeio as $lotemeio) {
																			if ($lotemeio['qtdd'] > 0) {
																				$totalqtddrateio = $totalqtddrateio + $lotemeio['qtdd'];
																				$consumounidade[$lotemeio['idunidade']] = $consumounidade[$lotemeio['idunidade']] + $lotemeio['qtdd'];
																				$consumofim = number_format(tratanumero($lotemeio['qtdd']), 2, ',', '.');
																				$retornofim = '--';
																			} else {
																				$totalqtddrateio = $totalqtddrateio - $lotemeio['qtdc'];
																				$consumounidade[$lotemeio['idunidade']] = $consumounidade[$lotemeio['idunidade']] - $lotemeio['qtdc'];
																				$retornofim = number_format(tratanumero($lotemeio['qtdc']), 2, ',', '.');
																				$consumofim = '---';
																			}

																			$linhar = $linhar + 1;
																			$chavek = $lotemeio['idlotecons'] + $linhar;
																			$rlink = CalculoEstoqueController::buscarUnidadeObjetoPorTipoObjetoEIdUnidade($lotemeio['idunidade'], 'modulo', 'lote');

																			if (!empty($rlink['idobjeto'])) {
																				$modulolink = "<a onclick=\"janelamodal('?_modulo=" . $rlink['idobjeto'] . "&_acao=u&idlote=" . $lotemeio['idlote'] . "');\" style='cursor: pointer;' color='blue'>" . $lotemeio['partida'] . "/" . $lotemeio['exercicio'] . "</a>";
																			} else {
																				$modulolink = $lotemeio['partida'] . "/" . $lotemeio['exercicio'];
																			}
																			$linkobs = $lotemeio["idsolmat"] ? "<a  onclick=\"janelamodal('?_modulo=solmat&_acao=u&idsolmat=" . $lotemeio["idsolmat"] . "')\" title='Solicitação de material'>" . $lotemeio["obs"] . "</a>" : $lotemeio["obs"];
																			$arrlotesrateio[$chavek] = "<tr>
																											<td>" . dma($lotemeio['criadoem']) . "</td>
																											<td>" . $modulolink . "</td>
																											<td style='text-align: right;'>" . $retornofim . "</td>
																											<td style='text-align: right;'>" . $consumofim . "</td>
																											<td>" . $lotemeio['unpadrao'] . "</td>
																											<td>" . $lotemeio['unidade'] . "</td>		
																											<td>" . dma($lotemeio['criadopor']) . "</td>
																											<td>" . $linkobs . "</td></tr>";
																		}
																		ksort($arrlotesrateio);

																		foreach ($arrlotesrateio as $idlotecons => $valor) {
																			echo ($valor);
																		}
																		?>
																		<tr>
																			<td colspan="7"><b>Total:</b></td>
																			<td style="text-align: right;"><b><? echo number_format(tratanumero($totalqtddrateio), 2, ',', '.'); ?> <?= $_1_u_prodserv_un ?></b></td>
																		</tr>
																	</table>
																</div>
															</div>
														<?
														} //if ($numrowmeio > 0) {

														if (count($consumounidade) > 0) {
														?>
															<table class="table table-striped planilha">
																<tr>
																	<th>Unidade</th>
																	<th style="text-align: right;">Quantidade</th>
																	<th style="text-align: right;">%</th>
																</tr>
																<?
																foreach ($consumounidade as $idunidade => $valor) {
																	$unidade = traduzid("unidade", "idunidade", "unidade", $idunidade);
																	$perc = ($valor / ($totalqtddrateio)) * 100;
																?>
																	<tr>
																		<th><?= $unidade ?></th>
																		<th style="text-align: right;"><? echo number_format(tratanumero($valor), 2, ',', '.'); ?>&nbsp;&nbsp;<?= $_1_u_prodserv_un ?></th>
																		<th style="text-align: right;"><? echo number_format(tratanumero($perc), 2, ',', '.'); ?></th>
																	</tr>
																<?
																}
																?>
															</table>
													<?
														}
													} //if ($_1_u_prodserv_comprado == 'Y') {
													?>
												</div>
											</div>
										</div>
									</div>
								<? } ?>
							</div>
						</div>
					</div>
				</div>
				<!-- compras-->
				<? if ($_1_u_prodserv_comprado == 'Y') {
				?>
					<div class="row">
						<div class="col-md-12">
							<div class="panel panel-default">
								<div class="panel-heading" style="display: inline-flex; align-items: center; width: 100%; justify-content: space-between;">
									<span>Compras nos últimos
										<select name="_1_<?= $_acao ?>_<?= $tabela ?>_consumodiasgraf" onchange="CB.post()">
											<? fillselect(CalculoEstoqueController::$_consumodiasgraf, $calculoEstoque['consumodiasgraf']); ?>
										</select></span>
								</div>
								<div class="panel-body" style="padding-top: 2px !important;">
									<?
									$_listarNfItemLote = CalculoEstoqueController::buscarDadosNfitemLote($_1_u_prodserv_idprodserv, $calculoEstoque['consumodiasgraf']);
									if ($_listarNfItemLote['qtdLinhas'] > 0) {
									?>
										<div class="panel panel-default">
											<div class="panel-heading">
												Última(s) Compra(s) em
											</div>
											<div class="panel-body" style="padding: 0 !important;">
												<table class="table table-striped planilha" style="width: 100%;">
													<tr>
														<th style="text-align: center;">NF</th>
														<th style="text-align: center;">Emissão</th>
														<th style="text-align: center;">Fornecedor</th>
														<th style="text-align: center;">Qtd</th>
														<th style="text-align: center;">Valor UN</th>
														<th style="text-align: center;">UN</th>
														<th style="text-align: center;" class="size15">Status</th>
													</tr>

													<?
													$fluxostatus = '';
													$nfitem = 0;

													foreach ($_listarNfItemLote['dados'] as $_nfItemLote) {
														if ($_nfItemLote['idnf'] != $idnf) {
															$quantidadesomada = 0;
														}

														if ($_nfItemLote['idnfitem'] != $idnfitem) {
															$valconv = (($_nfItemLote['valconv'] > 0) ? $_nfItemLote['valconv'] : 1);
															$quantidadesomada += ($_nfItemLote['qtd'] * $valconv);
														}

														if ($_nfItemLote['idnf'] != $idnf) {
															$impostoImportacao = NfController::buscarValorImpostoTotalItem($_1_u_prodserv_idprodserv, 'nf', $_nfItemLote['idnf']);
															if (empty($_nfItemLote['idlote']) && $impostoImportacao['internacional'] != 'Y') {
																$valoritem = $_nfItemLote['valoritem2'];
															} elseif ($impostoImportacao['internacional'] == 'Y') {
																$valoritem = round(($impostoImportacao['vlritem'] + $impostoImportacao['valorcomimpostoitem'] + $impostoImportacao['valorcomimposto']), 4);
															} else {
																$valoritem = $_nfItemLote['valoritem'];
															}
													?>
															<? $rotulo = getStatusFluxo('nf', 'idnf', $_nfItemLote['idnf']) ?>
															<tr <? if ($fluxostatus != $_nfItemLote['status'] && $_nfItemLote['status'] == 'CONCLUIDO' && $key > 0) echo 'style="border-top: 4px solid #1a111133"';
																else echo ''; ?>>
																<td align="center" style="width: 10%;">
																	<? if (empty($_nfItemLote["nnfe"])) {
																		if ($_nfItemLote['status'] == 'APROVADO') { ?>
																			<span class="azulclaro pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?= $_nfItemLote['idnf'] ?>');"><?= $_nfItemLote["idnf"]; ?></span>
																		<? } else { ?>
																			<span class="azulclaro pointer" onclick="janelamodal('?_modulo=cotacao&_acao=u&idcotacao=<?= $_nfItemLote['idobjetosolipor'] ?>');"><?= $_nfItemLote["idnf"]; ?></span>
																		<? }
																	} else { ?><span class=" azulclaro pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?= $_nfItemLote['idnf'] ?>');"><?= $_nfItemLote["nnfe"]; ?></span>
																	<? } ?>
																</td>
																<td align="center" style="width: 15%;"><?= $_nfItemLote["dtemissao"] ? dma($_nfItemLote["dtemissao"]) : '-'; ?></td>
																<td align="center" style="width: 30%;"><?= $_nfItemLote["nome"] ?></td>
																<td align="center" style="width: 10%;"><?= number_format(tratanumero($quantidadesomada), 2, ',', '.') ?></td>
																<td align="center" style="width: 10%;"><?= number_format(tratanumero($valoritem), 4, ',', '.') ?></td>
																<td align="center" style="width: 10%;"><label class="alert-warning"><?= $_nfItemLote["unpadrao"] ?></label></td>
																<td align="center" style="width: 15%;"><?= mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?>
															</tr>
													<?
														}
														$fluxostatus = $_nfItemLote['status'];
														$idnf = $_nfItemLote['idnf'];
														$idnfitem = $_nfItemLote['idnfitem'];
													}
													?>
												</table>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<a id="modalobservacao" class="point" data-toggle="collapse" href="#grafcompras">
													Histórico de Compra nos Últimos <?= $calculoEstoque['consumodiasgraf'] ?> Dias - Valor Unitário
												</a>
											</div>
											<div class="panel-body" id="grafcompras" style="padding: 0 !important;">
												<div id="chartdivcompras" style="text-align: -webkit-center; width:100%;height:400px; background-color: white;"></div>
											</div>
										</div>
									<? } //if($qtdcp>0 or $qtdcp2>0) 

									$_listarFormuladosSemFormula = CalculoEstoqueController::buscarFormuladosSemFormula($_1_u_prodserv_idprodserv, $calculoEstoque['consumodiasgraf']);

									echo ("<!-- valores grafico compras " . $_listarFormuladosSemFormula['sql'] . "-->");
									$ai = 0;
									$arrgrafc = array();
									foreach ($_listarFormuladosSemFormula['dados'] as $formuladosSemFormula) {
										if (empty($formuladosSemFormula['idlote'])) {
											$valoritem = $formuladosSemFormula['valoritem2'];
										} else {
											$valoritem = $formuladosSemFormula['valoritem'];
										}

										$arrgrafc[$ai]['data'] = $formuladosSemFormula['dmadtemissao'];
										$arrgrafc[$ai]['valor'] = $valoritem;
										$ai++;
									}
									?>
								</div>
							</div>
						</div>
					</div>
				<?
				} //if($_1_u_prodserv_comprado=='Y'){
				?>

				<!-- ESTOQUE -->
				<div class="row">
					<div class="col-md-12">
						<?/*ESTOQUE*/ ?>
						<?
						if ($_1_u_prodserv_tipo == 'PRODUTO') {
							$_listarProdservFormula = CalculoEstoqueController::buscarProdServFormulaPorIdProdServEStatus($_1_u_prodserv_idprodserv, 'ATIVO');
							$temformula = count($_listarProdservFormula);

							if ($temformula > 0 and $_1_u_prodserv_fabricado == 'Y') { //se não tiver formula  
								if ($_1_u_prodserv_comprado == 'Y') {
									$strlt = '';
								}
								$strlt = " AND l.idprodservformula=" . $idprodservformula;
							} else { //produtos formulados
								$strlt = '';
							}

							$_listarUnidadeLoteFracao = CalculoEstoqueController::buscarUnidadeLoteFracaoPorIdProdserv($_1_u_prodserv_idprodserv, $strlt);
						?>
							<div class="panel panel-default">
								<div class="panel-heading">Estoque</div>
								<? echo ("<!--" . $_listarUnidadeLoteFracao['sql'] . "-->"); ?>
								<ul class="nav nav-tabs panel" role="tablist">
									<?
									$arrLpGrupo = array();
									$linhaun = 0;
									foreach ($_listarUnidadeLoteFracao['dados'] as $unidadeLoteFracao) {
										$arrUN[$unidadeLoteFracao['idunidade']] = $unidadeLoteFracao['idunidade'];
										$linhaun = $linhaun + 1;
										if ($linhaun == 1) {
											$classun = "active";
										} else {
											$classun = "";
										}
									?>
										<li id="idunidade_navtab_<?= $unidadeLoteFracao['idunidade'] ?>" role="presentation panel-heading" onclick="verloteunidade(this,<?= $unidadeLoteFracao['idunidade'] ?>)" class="<?= $classun ?>">
											<a href="#idunidade_<?= $unidadeLoteFracao['idunidade'] ?>" tab="<?= $unidadeLoteFracao['idunidade'] ?>" role="tab" data-toggle="tab">
												<?= $unidadeLoteFracao["unidade"] ?>
												<label class="alert-warning">
													<?
													if (strpos(strtolower($calculoEstoque["qtdpadrao_exp"]), "d") or strpos(strtolower($calculoEstoque["qtdpadrao_exp"]), "e")) {
														echo recuperaExpoente(tratanumero($unidadeLoteFracao["qtdporlote"]), $calculoEstoque["qtdpadrao_exp"]);
													} else {
														echo number_format(tratanumero($unidadeLoteFracao["qtdporlote"]), 2, ',', '.');
													}
													?>
													<?= $_1_u_prodserv_un ?>
												</label>
											</a>
										</li>
									<? } ?>
								</ul>
								<div class="panel-body" style="background-color:white; padding-top:0 !important;">
									<div class="row">
										<div class="col-md-12">
											<?
											if ($_listarUnidadeLoteFracao['qtdLinhas'] < 1) {
											?>
												<div class='alert alert-warning' role='alert'>Este produto não possui estoque e não teve lotes disponiveis!</div>
											<?
											} else {
											?>
												<div class="panel panel-default">
													<div class="panel-heading">
														<a id="modalobservacao" class="point" data-toggle="collapse" href="#grafestoque<?= $_1_u_prodserv_idprodserv ?><?= $idprodservformula ?>">
															Análise de Estoque nos Últimos <?= $calculoEstoque['consumodiasgraf'] ?> Dia(s)
														</a>
													</div>
													<div id="grafestoque<?= $_1_u_prodserv_idprodserv ?><?= $idprodservformula ?>" class="panel-body" style="padding: 0 !important;">
														<div id="chartdiv" style="text-align: -webkit-center; width:100%;height:400px; background-color: white;"></div>
													</div>
												</div>
											<?
											}
											$linhaun = 0;
											$arrgraf = array();
											$onLoadGraph = "";

											foreach ($arrUN as $v_idunidade => $rowy) {
												$linhaun = $linhaun + 1;
												if ($linhaun == 1) {
													$classun = "";
												} else {
													$classun = "hidden";
												}

												if ($onLoadGraph == "") {
													$onLoadGraph = $v_idunidade;
												}
											?>
												<div class="panel panel-default loteunidade <?= $classun ?>" class="collapse" id="unidade<?= $v_idunidade ?>" style="border: 0;border-radius:0;">
													<div class="panel-heading">
														Estoque - <? echo traduzid("unidade", "idunidade", "unidade", $v_idunidade); ?>
													</div>
													<div class="panel-body" style="padding: 0 !important;">
														<?
														$_listarLoteProdservUnidade = CalculoEstoqueController::buscarLoteELoteFracaoPorIdProdservEIdUnidade($_1_u_prodserv_idprodserv, $v_idunidade, $_1_u_prodserv_conteudo, $strlt);
														if ($_listarLoteProdservUnidade['qtdLinhas'] > 0) {
														?>
															<table class="table table-striped planilha">
																<tr>
																	<th style="text-align: center;">Lote</th>
																	<? if ($_1_u_prodserv_fabricado == 'Y') { ?>
																		<th style="text-align: center;">Formulação</th>
																	<? } ?>
																	<th style="text-align: center;">Disponível</th>
																	<th style="text-align: center;">Vencimento</th>
																	<th style="text-align: center;">Status</th>
																	<th style="text-align: center;">Hist</th>
																</tr>
																<?
																foreach ($_listarLoteProdservUnidade['dados'] as $loteProdservUnidade) {
																	if ($loteProdservUnidade['idunidadegp'] != 4 and $loteProdservUnidade['idunidadegp'] != 5) { // não aparece retem e cq
																		if (empty($loteProdservUnidade['idobjeto'])) {
																			$link = 'lotealmoxarifado';
																		} else {
																			$link = $loteProdservUnidade['idobjeto'];
																		}
																?>
																		<tr>
																			<td align="center" onclick="janelamodal('?_modulo=<?= $link ?>&_acao=u&idlote=<?= $loteProdservUnidade['idlote'] ?>&_idempresa=<?= $loteProdservUnidade['idempresaf'] ?>');" style="cursor: pointer;">
																				<a style="color:blue;"><?= $loteProdservUnidade["partida"] ?>/<?= $loteProdservUnidade["loteexercicio"] ?></a>
																			</td>
																			<? if ($_1_u_prodserv_fabricado == 'Y') { ?>
																				<td align="center"><?= $loteProdservUnidade["formula"] ?></td>
																			<? } ?>
																			<td align="center" class=" pointer hoververmelho ">

																				<?
																				$unestoque = $prodservclass->getUnEstoque($_1_u_prodserv_idprodserv, $loteProdservUnidade['idunidadepadrao'], $loteProdservUnidade['converteest'], $loteProdservUnidade['unpadrao'], $loteProdservUnidade['unlote']);
																				if (
																					strpos(strtolower($loteProdservUnidade["qtd_exp"]), "d")
																					or strpos(strtolower($loteProdservUnidade["qtd_exp"]), "e")
																				) {
																					$stvalor = recuperaExpoente(tratanumero($loteProdservUnidade["qtd"]), $loteProdservUnidade["qtd_exp"]) . ' - ' . $unestoque;
																				} else {
																					$qtdfr = $prodservclass->getEstoqueLote($loteProdservUnidade['idlotefracao']);
																					$stvalor = number_format(tratanumero($qtdfr), 2, ',', '.') . ' - ' . $unestoque;
																				}
																				?>

																				<button type="button" <? if ($loteProdservUnidade['vunpadrao'] == 'Y' and $loteProdservUnidade['convestoque'] == 'N' and $loteProdservUnidade['converteest'] == 'Y') { ?>style='font-size: 5px;' <? } else { ?>style='border: none; background-color: #faebcc;' <? } ?> class="btn btn-default btn-xs  " <? if ($loteProdservUnidade['converteest'] == 'Y' and $loteProdservUnidade['convestoque'] == 'N') { ?> onclick="alteravunpadrao(<?= $loteProdservUnidade['idlote'] ?>,'N')" <? } ?> title="<?= $stvalor ?>">
																					<?= $stvalor ?>
																				</button>
																				<? if ($loteProdservUnidade['converteest'] == 'Y' and $loteProdservUnidade['convestoque'] == 'N') {
																					$qtdfrR = $prodservclass->getEstoqueLoteReal($loteProdservUnidade['idlotefracao']);
																				?>
																					<button <? if ($loteProdservUnidade['vunpadrao'] == 'N') { ?>style='font-size: 5px;' <? } else { ?>style='border: none; background-color: #faebcc;' <? } ?> type="button" class="btn btn-default btn-xs " onclick="alteravunpadrao(<?= $loteProdservUnidade['idlote'] ?>,'Y')" title="<?= $qtdfrR ?>">
																						<?= $qtdfrR ?>
																					</button>
																				<? } ?>
																			</td>
																			<td align="center"><?= $loteProdservUnidade["dmavenci"] ?></td>
																			<td align="center"><?= $loteProdservUnidade["rotulo"] ?></td>
																			<td align="center">
																				<?	// GVT - 06/07/2020 - Histórico do lote igual de lote.php 
																				// ----------------------------------------------------------------------------------- //
																				?>
																				<div id="consumolote_<?= $loteProdservUnidade['idlotefracao'] ?>" style="display: none">
																					<?= $prodservclass->historicolotecons($loteProdservUnidade['idlote'], $loteProdservUnidade['idunidadepadrao']); ?>
																				</div>
																				<? // ----------------------------------------------------------------------------------- //
																				?>
																				<a title="Histórico" class="fa fa-search fa-1x cinzaclaro hoverazul btn-lg pointer" onclick="showhistoricolote(<?= $loteProdservUnidade['idlotefracao'] ?>);"></a>
																			</td>
																		</tr>
																<?
																	}
																}
																?>
															</table>
														<?
														}

														$_listarGrafico = CalculoEstoqueController::buscarGrafico($_1_u_prodserv_idprodserv, $idprodservformula, $v_idunidade, $calculoEstoque['consumodiasgraf'], $strlt);
														echo ("<!-- valores grafico " . $_listarGrafico['sql'] . "-->");
														$ai = 0;
														foreach ($_listarGrafico['dados'] as $grafico) {
															$arrgraf[$v_idunidade][$ai]['data'] = $grafico['datagraf'];
															$arrgraf[$v_idunidade][$ai]['credito'] = $grafico['qtdc'];
															$arrgraf[$v_idunidade][$ai]['debito'] = $grafico['qtdd'];
															$arrgraf[$v_idunidade][$ai]['estoque'] = $grafico['estoque'];
															$ai++;
														}
														?>
													</div>
												</div>
											<? } ?>
										</div>
									</div>
								</div>
							</div>
						<? } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?
if (!empty($_1_u_prodserv_idprodserv)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_prodserv_idprodserv; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "prodserv"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
require_once('../form/js/calculoestoque_js.php');
?>