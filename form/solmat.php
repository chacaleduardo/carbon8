<?
require_once("../inc/php/validaacesso.php");
require_once("../model/prodserv.php");
require_once("../inc/php/permissao.php");
require_once("controllers/assinatura_controller.php");
if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

$pagvaltabela = "solmat";
$pagvalcampos = array(
	"idsolmat" => "pk"
);

$pagsql = "SELECT * from solmat where idsolmat = '#pkid'";

//controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
if (!empty($_GET['idsolmatcp'])) {
	$scp = "SELECT * from solmat where idsolmat = " . $_GET['idsolmatcp'];
	$recp = d::b()->query($scp) or die("idsolmatcp: Erro: " . mysqli_error(d::b()) . "\n" . $scp);
	if (mysqli_num_rows($recp) > 0) {
		$rcp = mysqli_fetch_assoc($recp);
		$_1_u_solmat_tipo = $rcp['tipo'];
		$_1_u_solmat_unidade = $rcp['unidade'];
		$_1_u_solmat_idunidade = $rcp['idunidade'];
	} else {
		die("Idsolmat não encontrado para duplicar");
	}
}

include_once("../inc/php/controlevariaveisgetpost.php");

//Chama a Classe prodserv
$prodservclass = new PRODSERV();
//Recuperar a unidade padrão conforme módulo pré-configurado
$_idempresa = !empty($_GET['_idempresa']) ? $_GET['_idempresa'] : $_SESSION['SESSAO']['IDEMPRESA'];
$unidadepadrao = getUnidadePadraoModulo($_GET["_modulo"], $_idempresa);
if ($_acao == 'i') {
	$_1_u_solmat_unidade = $unidadepadrao;
}

//LTM (28/05/2021) - Caso a amostra não tenha status, seta o primeiro quando for no insert o caso
if (empty($_1_u_solmat_status)) {
	$_1_u_solmat_status = 'ABERTO';
}

$statusr = array('CONCLUIDO', 'CANCELADO', 'EXECUCAO', 'SEPARADO', 'DIVERGENCIA');
if (in_array($_1_u_solmat_status, $statusr)) {
	$readonly2 = 'readonly';
}

$statusrd = array('SOLICITADO', 'EXECUCAO', 'CANCELADO', 'SEPARADO', 'DIVERGENCIA', 'CONCLUIDO');
if (in_array($_1_u_solmat_status, $statusrd)) {
	$readonly = 'readonly';
	$disable = 'disabled';
}

if ($_1_u_solmat_status == 'CONCLUIDO' || $_1_u_solmat_status == 'CANCELADO') {
	$disablebt = 'disabled';
}

//Recupera os contato as serem selecionados
if (!empty($_1_u_solmat_idsolmat) or !empty($_GET['idsolmatcp'])) {
	$jprodservtemp = SolmatController::buscarProdutosPorUnidadeMeios($_1_u_solmat_idsolmat, $_1_u_solmat_idunidade);
}

$i = 99;?>
<link rel="stylesheet" href="../form/css/solmat_css.css?_<?= date("dmYhms") ?>" />
<div class="row">
	<div class="panel panel-default w-100">
		<div class="panel-heading">
			<div class="sigla-empresa"></div>
			<div class="row w-100 d-flex flex-wrap flex-between align-items-center">
				<!-- ID -->
				<? if (!empty($_1_u_solmat_idsolmat)) { ?>
					<div class="col-xs-6 col-md-1 form-group">
						<input name="_1_<?= $_acao ?>_solmat_idsolmat" id="idsolmat" type="hidden" value="<?= $_1_u_solmat_idsolmat ?>" readonly='readonly'>
						<input name="_1_<?= $_acao ?>_solmat_status" id="status" type="hidden" value="<?= $_1_u_solmat_status ?>">
						<label for="" class="text-white">ID</label>
						<label class="d-flex align-items-center alert-warning form-control"><?= $_1_u_solmat_idsolmat ?></label>
					</div>
				<? } ?>
				<!-- Tipo -->
				<div class="col-xs-6 col-md-1 form-group">
					<label for="" class="text-white d-block">
						Tipo
					</label>
					<select name="_1_<?= $_acao ?>_solmat_tipo" vnulo <?= $readonly ?> class="form-control">
						<option value=""></option>
						<? if ($_GET['_modulo'] == 'solmat') {
							fillselect(SolmatController::$tipoSolmat, $_1_u_solmat_tipo);
							//indica o tipo de unidade no campo origem
							$idtipounidade = 3;
						} elseif ($_GET['_modulo'] == 'soltag') {
							fillselect(SolmatController::$tipoSoltag, $_1_u_solmat_tipo);
						} elseif ($_GET['_modulo'] == 'solmatmeios') {
							fillselect(SolmatController::$tipoSolmatMeios, $_1_u_solmat_tipo);
							//indica o tipo de unidade no campo origem
							$idtipounidade = 8;
						} else {
							fillselect(SolmatController::$tipoSoltag, $_1_u_solmat_tipo);
						}
						?>
					</select>
				</div>
				<!-- Origem -->
				<div class="col-xs-6 col-md-2 form-group">
					<label for="" class="text-white">
						Origem
					</label>
					<? if (!empty($_1_u_solmat_idsolmat)) {
						$rotun = traduzid('unidade', 'idunidade', 'unidade', $_1_u_solmat_unidade); ?>
						<label class="d-flex align-items-center alert-warning form-control"><?= $rotun ?></label>
						<input type="hidden" name="_1_<?= $_acao ?>_solmat_unidade" value="<?= $_1_u_solmat_unidade ?>" readonly>
					<? } else { ?>
						<select name="_1_<?= $_acao ?>_solmat_unidade" vnulo class="form-control">
							<option value=""></option>
							<? fillselect("SELECT idunidade, unidade from unidade where idtipounidade = {$idtipounidade} and status = 'ATIVO' and idempresa in (SELECT DISTINCT (u.idempresa) FROM vw8PessoaUnidade w inner join unidade u on u.idunidade = w.idunidade WHERE w.idpessoa = {$_SESSION["SESSAO"]["IDPESSOA"]}) and idempresa = {$_GET['_idempresa']}"); ?>
						</select>
					<? } ?>
				</div>
				<!-- Destino -->
				<div class="col-xs-6 col-md-2 form-group">
					<label for="" class="text-white">Destino</label>
					<?
					if (empty($_1_u_solmat_idsolmat)) {
					?>
						<select name="_1_<?= $_acao ?>_solmat_idunidade" <?= $readonly2 ?> vnulo <?= $_acao == "u" ? "onchange='mudarunidade(this)'" : "" ?> class="form-control">
							<? fillselect("SELECT v.idunidade, v.unidade, u.idempresa FROM vw8PessoaUnidade v INNER JOIN unidade u on u.idunidade = v.idunidade  WHERE v.idpessoa = {$_SESSION["SESSAO"]["IDPESSOA"]} and u.idempresa = {$_GET['_idempresa']}"); ?>
						</select>
					<? } else { ?>
						<? if (empty($_1_u_solmat_idunidade)) {
							$rotun = traduzid('unidade', 'idunidade', 'unidade', traduzid("pessoa", 'usuario', 'idunidade', $_1_u_solmat_criadopor));
						} else {
							$rotun = traduzid('unidade', 'idunidade', 'unidade', $_1_u_solmat_idunidade);
						} ?>
						<label class="d-flex align-items-center alert-warning form-control"><?= $rotun ?></label>
						<input type="hidden" name="_1_<?= $_acao ?>_solmat_idunidade" value="<?= $_1_u_solmat_idunidade ?>" readonly>
					<? } ?>
				</div>
				<!-- Local de Entrega -->
				<? if ($_acao == "u") { ?>
					<div class="col-xs-6 col-md-2 form-group">
						<label for="" class="text-white">Local de Entrega</label>
						<select name="_1_<?= $_acao ?>_solmat_idtag" <?= $_1_u_solmat_idtag ?> style="width: 149px;" <?= $readonly ?> class="form-control">
							<option value=""></option>
							<? fillselect(SolmatController::listarFillSelectTagsPorIdTagClassIdTagTipoEIdUnidade(2, "145, 297", $_1_u_solmat_idunidade), $_1_u_solmat_idtag); ?>
						</select>
					</div>
				<? } ?>
				<? if ($_acao == "u") { ?>
					<!-- QR CODE -->
					<div class="col-xs-6 col-md-1">
						<button id="gerar-qrcode" class="btn btn-info">
							QR Code
						</button>
					</div>
					<div class="col-xs-6 col-md-1">
						<button onclick="consomePendentes(<?= $_1_u_solmat_idsolmat ?>)" class="btn btn-danger">
							Consumir Pendentes
						</button>
					</div>
					<div class="d-flex flex-wrap align-items-center justify-end col-xs-6 col-md-2 px-0">
						<!-- Impressao -->
						<div class="dropdown" id="cbDaterangeCol">
							<a href="#" class="btn btn-link dropdown-toggle cinza pointer hoverazul" type="button" id="dropdownMenuDateCol" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
								<span class="fa fa-lg fa-print">
							</a>
							<ul class="dropdown-menu" aria-labelledby="dropdownprint">
								<li class="dropdown-item" onclick="showModal()"><i title="Etiqueta da solicitação" class="fa fa-print pull-right fa-lg cinza pointer hoverazul"></i> Zebra</li>
								<li class="dropdown-item" onclick="janelamodal('report/relsolmat.php?idsolmat=<?= $_1_u_solmat_idsolmat ?>')"><i title="Imprimir A4" class="fa fa-print pull-right fa-lg cinza pointer hoverazul"></i> A4</li>
							</ul>
						</div>
						<!-- Status -->
						<div class="col-xs-6 form-group">
							<label for="" class="text-white">
								Status
							</label>
							<? $rotulo = getStatusFluxo('solmat', 'idsolmat', $_1_u_solmat_idsolmat) ?>
							<label class="alert-warning d-flex align-items-center form-control" title="<?= $_1_u_solmat_status ?>" id="statusButton"><?= mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?> </label>
						</div>
					</div>
				<? } ?>
			</div>
		</div>

		<div class="row" style="margin-bottom: 30px;">
			<!-- faz itens -->
			<? if (!empty($_1_u_solmat_idsolmat) or !empty($_GET['idsolmatcp'])) {
				if (empty($_1_u_solmat_idsolmat)) {
					$_1_u_solmat_idsolmat = $_GET['idsolmatcp'];
				}

				$listarItensSolmat = SolmatController::buscarProdServESolMatItemPorIdSolMat($_1_u_solmat_idsolmat);
				$qtdItensSolmat = count($listarItensSolmat);

				//busca os itens sem cadastro
				$listarItensSolmatSemProdserv = SolmatController::buscarSolMatItemSemCadastroPorIdSolMat($_1_u_solmat_idsolmat);
				$qtdItensSolmatSemProdserv = count($listarItensSolmatSemProdserv);
				$qtdrows = $qtdItensSolmat + $qtdItensSolmatSemProdserv;
				if (!empty($unidadepadrao)) {
					$tipounidadepadrao = traduzid('unidade', 'idunidade', 'idtipounidade', $unidadepadrao);
				}

				//busca a unidade
				$listarUnidades = SolmatController::buscarUnidadesPorModuloTipoGetIdEmpresaEIdTipoUnidade('lote', getidempresa("ui.idempresa", $_GET['_modulo']), $tipounidadepadrao);
				$qtdUnidades = count($listarUnidades);
			?>
				<div class="col-md-12">
					<div class="panel panel-default">
						<div class="panel-heading bg-none">
							Item(ns) Cadastrado(s)
							<i class="fa fa-arrows-v fa-1x cinzaclaro pointer" id="esconderMostrarTodos" state="<?= $userPref['collapse']['solcomitemtotal'] ?>" title="Esconder Todos" onclick="esconderMostrarTodos('expandir')" style="float: right; padding: 2px 10px 0 10px;"></i>
						</div>
						<div class="panel-body bg-none">
							<?
							$_1_u_solmatitem_qtdc = $rowqr['qtdc'];
							$_1_u_solmatitem_idtag = $rowqr['idtag'];
							$data = explode(" ", $_1_u_solmat_criadoem);
							$dataCriadoEm = explode("/", $data[0]);
							$planejamentoProdserv = SolmatController::buscarPlanejamentoPorIdProdservMesExercioUnidade($_1_u_solmat_idunidade, $dataCriadoEm[2], $dataCriadoEm[1], $_1_u_solmat_idsolmat);
							listaitem($listarItensSolmat, 1, $planejamentoProdserv, $dataCriadoEm);
							?>
							<? if ($_acao == "u" && $_1_u_solmat_status == 'ABERTO') { ?>
								<div class="panel panel-default">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-4">
												Descrição
											</div>
										</div>
									</div>
									<div class="panel-body bg-white px-0" style="padding-top: .5rem !important;padding-bottom: 0;">
										<div class="row" style="height:40px;">
											<div class="col-xs-4">
												<input style="border: 1px solid silver;" type="text" id="insidprodserv" placeholder="Selecione o produto" cbvalue="produtos" <?= $readonly ?> <?= $disable ?>>
											</div>
										</div>
									</div>
								</div>
							<? } ?>
						</div>
					</div>
				</div>
			<?
			}

			//----------------------------------- Comentário ---------------------------------------
			if ($_acao == 'u') {
				$listarComentarios = SolmatController::buscarComentariosPorIdSolMat($_1_u_solmat_idsolmat);
			?>
				<div hidden>
					<div id="comentarioopoup">
						<table width="100%">
							<tbody>
								<tr>
									<td>Comentário:</td>
								</tr>
								<tr>
									<td>
										<input type="hidden" id="_99_i_modulocom_idmodulo" name="_99_i_modulocom_idmodulo" value="<?= $_1_u_solmat_idsolmat ?>" />
										<input type="hidden" id="_99_i_modulocom_modulo" name="_99_i_modulocom_modulo" value="solmat" />
										<textarea rows="3" onkeyup="atualizaCampo(this)" style="width: 100%; height: 80px;" name="_99_i_modulocom_descricao" id="_99_i_modulocom_descricao"></textarea>
									</td>
								</tr>
								<tr>
									<td>
										<table class="table table-striped planilha display">
											<tbody>
												<?
												if (count($listarComentarios) > 0) {
													foreach ($listarComentarios as $comentario) {?>
														<tr>
															<td style="line-height: 14px; padding: 8px; color:#666;">
																<div>
																	<div style="margin-left: 1px; word-break: break-word;line-height: 14px; padding: 8px; font-size: 11px;color:#666;">
																		<?= dmahms($comentario['criadoem']) ?> - <?= $comentario['criadopor'] ?>: <?= nl2br($comentario['descricao']) ?>
																	</div>
																</div>
															</td>
														</tr>
												<?	}
												}?>
											</tbody>
										</table>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			<?
			}
			//------------------------------------ Comentário ---------------------------------------
			if (($_acao == "u" or !empty($_GET['idsolmatcp'])) && $_GET['_modulo'] !== 'solmat') {
			?>
				<div class="col-md-12">
					<div class="panel panel-default">
						<div class="panel-heading">Item(ns) Não Cadastrado(s)</div>
						<div class="panel-body">
							<table class="table table-striped planilha display">
								<tbody>
									<tr class="cabitem" style="height:40px;">
										<th>Qtd</th>
										<th>Descrição</th>
										<th>Observação</th>
									</tr>
									<? listaitem($listarItensSolmatSemProdserv, 10000);
									if ($_acao == "u" && $_1_u_solmat_status == "ABERTO") { ?>
										<tr style="height:40px">
											<td style="max-width: 32px;">
												<input style=" border: 1px solid silver;" name="_10#quantidade" title="Qtd" placeholder="Qtd" type="number" class="size6" <?= $readonly ?>>
											</td>
											<td>
												<input type="text" style=" border: 1px solid silver;width: 100%;" name="_10#prodservdescr" class="idprodserv" placeholder="Informe o produto" <?= $readonly ?>>
											</td>
											<td>
												<input style="border: 1px solid silver;width: 100%;" name="_10#obs" title="obs" placeholder="Observação:" type="text" <?= $readonly ?>>
											</td>
											<td></td>
										</tr>
									<? } ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			<? } ?>
		</div>
	</div>
</div>

<?
$lAss = AssinaturaController::buscarAssinatura($_1_u_solmat_idsolmat, 'solmat', 'base64');
$qtdAss = count($lAss);
if ($qtdAss < 1) {
	$cenviar = "";
	$climpar = "hide";
} else {
	$cenviar = "hide";
	$climpar = "";
} ?>

<div class="row <?= ($_acao == "i" ? "hide" : "") ?>">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">Rubrica de Recebimento</div>
			<div class="panel-body">
				<div class="row">

					<div class="col-md-6 <?= $cenviar ?>">
						<canvas id="canvas" width="500" height="200" style="border:1px solid #00000047; background-color: white;"></canvas><br>
						<div style="margin:20px 0">
							<button id="saveButton" class="btn btn-primary btn-xs" type="submit" style="margin-right: 20px;">
								<i class="fa fa-check"></i>
								Enviar
							</button>
							<button id="clearButton" class="btn btn-xs " type="button" style="background:#da8610; color:#ffffff;">
								<i class="fa fa-eraser"></i>
								Limpar
							</button>

						</div>
					</div>

					<div class="col-md-6 <?= $climpar ?>">
						<div class="row">
							<div class="col-md-12" style="padding-left: 50px;">
								<img id="imgassinatura" style="margin: auto;background-color: hsl(0, 0%, 90%); width: 250px;" src="<?= $lAss['assinatura'] ?>" value="<?= $lAss['idassinatura'] ?>">
							</div>
						</div>
						<div class="row">
							<div class="col-md-12 bold" style="padding-left: 50px;">
								Assinado em:<?= dmahms($lAss['criadoem']) ?>
							</div>
						</div>

						<div class="row">
							<div class="col-md-12 bold" style="padding-left: 50px;">
								<br>
								<button <?= $disablebt ?> id="trashButton" class="btn btn-danger btn-xs " type="button">
									<i class="fa fa-trash-o "></i>
									Retirar
								</button>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>


<script src="./inc/js/qr-scanner/qr-scanner.legacy.min.js" type="text/javascript"></script>
<script src="./inc/js/qr-scanner/qr-scanner-controller.js" type="text/javascript"></script>
<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>

<?
function listaitem($listarItensSolmatSemProdserv, $con, $planejamentoProdserv = false, $dataCriadoEm = false)
{
	global $readonly, $_1_u_solmat_status, $_acao, $_1_u_solmat_tipo, $_1_u_solmatitem_idprodservformula, $tipounidadepadrao, $_1_u_solmat_idunidade, $_1_u_solmat_unidade;
	$x = $con;
	foreach ($listarItensSolmatSemProdserv as $rowqr) {
		if (!empty($_GET['idsolmatcp']) && $_acao == "i") {
			$pd = "duplicar_" . $x;
			if (!empty($rowqr['idprodserv'])) {
				$excluir = "$('#_item_" . $x . "').remove();";
			} else {
				$excluir = "$('#_iten_" . $x . "').remove();";
			}
		} else {
			$pd = "_x" . $x . "_u_solmatitem";
			$excluir = "excluir(" . $rowqr['idsolmatitem'] . ")";
		}
		?>
		<div class="panel panel-default">
			<div class="panel-heading px-0">
				<div class="row d-flex">
					<?
					if ($_GET['_modulo'] == 'solmat' && $planejamentoProdserv['qtdLinhas'] > 0) {
					?>
						<div class="col-xs-1 cp_plan" title="Consumo Planejado">Cons. Plan.</div>
						<div class="col-xs-1 cp_disp" title="Consumo Disponível"> Lim. Credito </div>
						<div class="col-xs-1 cp_disp" title="Consumo Disponível"> Disponível </div>
					<? } ?>
					<? if ($_GET['_modulo'] != 'soltag') { ?>
						<div class="col-xs-1 cp_qtd" title="Quantidade"> Qtd </div>
					<? } ?>
					<div class="col-xs-1 cp_un" title="Unidade"> Un </div>
					<? $class = $planejamentoProdserv['qtdLinhas'] > 0 ? 'col-xs-4' : 'col-xs-6'; ?>
					<div class="<?= $class ?> cp_descricao" title="Descrição"> Descrição </div>
					<div class="col-xs-3"> </div>
					<? if ($_GET['_modulo'] == 'soltag') { ?>
						<div class="col-xs-3 cp_descricao" title="Local Equipamento"> Local Equipamento </div>
					<? } ?>
					<? if ($_1_u_solmat_tipo != "EQUIPAMENTOS" && $_GET['_modulo'] == 'solmatmeios') { ?>
						<div class="col-xs-3 cp_formproc" title="Fórmulas/Processos"> Fórmulas/Processos </div>
					<? } ?>
					<div class="col-xs-3" title="Observação"> Observação </div>
					<div class="col-xs-1"></div>
				</div>
			</div>
			<div class="panel-body bg-white px-0 item" style="padding-top: .5rem !important;padding-bottom: 0;">
				<?
				$listarLotes = SolmatController::buscarLoteSolmatItem($rowqr['idprodservformula'], $_1_u_solmat_status, $rowqr['idprodserv'], $rowqr['idsolmatitem'], 'solmatitem', $_1_u_solmat_unidade);
				//$fundoVerde = (count($listarLotes) ? "fundoVerde" : '');
				?>
				<div class="row d-flex align-items-center soll " id="<?= $rowqr['idsolmatitem'] ?>" style="height:40px;">
					<input name="<?= $pd ?>_idsolmatitem" type="hidden" value="<?= $rowqr['idsolmatitem'] ?>">
					<? if ($_GET['_modulo'] == 'solmat' && $planejamentoProdserv['qtdLinhas'] > 0) {
					?>
						<!--Cons. Plan.-->
						<div class="col-xs-1">
							<?
							$planejamentoProdservDados = $planejamentoProdserv['dados'][$rowqr['idprodserv']];
							echo $planejamentoProdservDados['planejado'] ? $planejamentoProdservDados['planejado'] : '-';
							?>
						</div>

						<!--Disponível-->
						<div class="col-xs-1">
							<?
							$somaConsumoMes = SolmatController::buscarConsumoLoteMes($dataCriadoEm[2], $dataCriadoEm[1], $_1_u_solmat_idunidade, $rowqr['idprodserv']);
							$totalDisponivel =  $planejamentoProdservDados['planejado'] - $somaConsumoMes['totalconsumomes'];
							$adicional = ($planejamentoProdservDados['planejado'] * $planejamentoProdservDados['adicional']) / 100;
							$totalDisponivelComAdicional = $adicional + $planejamentoProdservDados['planejado'] - $somaConsumoMes['totalconsumomes'];
							echo $adicional ? $adicional : '-';

							$itemEstoque = SolmatController::buscarSolmatitemEstoque($rowqr['idsolmatitem']);

							?>
						</div>

						<div class="col-xs-1">
							<?
							echo $planejamentoProdservDados['planejado'] ? (($totalDisponivel > 0) ? $totalDisponivel : 0) : '-';
							?>
							<input id="<?= $pd ?>_disponivel" type="hidden" value="<?= $totalDisponivelComAdicional ?>">
						</div>
					<? } ?>
					<!-- Quantidade -->
					<div class="col-xs-1">
						<?
						$disable = ($totalDisponivelComAdicional <= 0 && $planejamentoProdservDados['planejado'] > 0) ? 'readonly="readonly"' : ''; ?>
						<input estoque="<?= $itemEstoque['qtd'] ?>" style="padding-left:12px;" autocomplete="off" class='size6' name="<?= $pd ?>_qtdc" type="number" data-accept-dot="1" <?= $disable ?> value="<?= $rowqr['qtdc'] ?>" <?= $readonly ?> <? if ($planejamentoProdserv['qtdLinhas'] > 0 and $planejamentoProdservDados['planejado'] > 0) { ?> onchange="verificadisp(this,<?= $rowqr['idsolmatitem'] ?>,'<?= $pd ?>_disponivel')" <? } else { ?>onchange="verificaestoque(this,<?= $rowqr['idsolmatitem'] ?>)" <? } ?>>
						<? if (!empty($rowqr['justificativa'])) {
							if ($rowqr['status'] == 'APROVADO') {
								$cordep = 'verde';
							} else {
								$cordep = 'vermelho';
							}


						?>
							<i id="fig_<?= $pd ?>_disponivel" class='fa fa-exclamation-triangle <?= $cordep ?> pointer' title='Solicitação maior que o planejado' onclick="planejamento(<?= $rowqr['idsolmatitem'] ?>,'Justificativa do consumo excedente.','N',<?= $rowqr['qtdc'] ?>)"></i>
						<? } ?>
						<div id="planejamento<?= $rowqr['idsolmatitem'] ?>" style="display: none">
							<table class="table table-hover">

								<thead>
									<tr>
										<th scope="col">Justificativa</th>
										<? if (!empty($rowqr['justificativa'])) { ?>
											<th>Status</th>
											<th><? if ($rowqr['status'] == 'APROVADO' and !empty($rowqr['aprovadopor'])) {
													echo ("Aprovado Por");
												} ?></th>
										<? } ?>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<input id="<?= $rowqr['idsolmatitem'] ?>_idsolmatitem" name="" type="hidden" value="<?= $rowqr['idsolmatitem'] ?>">
											<input id="<?= $rowqr['idsolmatitem'] ?>_qtdc" name="" type="hidden" value="<?= $rowqr['qtdc'] ?>">
											<input id="<?= $rowqr['idsolmatitem'] ?>_justificativa" name="" value="<?= $rowqr['justificativa'] ?>" placeholder="Justifique o motivo para ultrapassar o limite de planejamento do item">
										</td>
										<? if (!empty($rowqr['justificativa'])) { ?>
											<td>
												<?

												if ($rowqr['status'] == 'PENDENTE') {

													if (!array_key_exists("aprovasolmat", getModsUsr("MODULOS"))) {
														$disablednf = "readonly='readonly'";
														$msg = "Liberar permissão na LP ao modulo: Aprova consumos da Solicitação de Materiais.";
													} else {
														$disablednf = "";
														$msg = "Aprovar consumo";
													}

												?>
													<button title="<?= $msg ?>" <?= $disablednf ?> type='button' style='' class='btn btn-success btn-xs' onclick="aprovarconsumo(<?= $rowqr['idsolmatitem'] ?>,'<?= $_SESSION['SESSAO']['USUARIO'] ?>');"><i class='fa fa-check'></i>APROVAR</button>
												<? } else {
													echo ($rowqr['status']);
												} ?>
											</td>
											<td><? if ($rowqr['status'] == 'APROVADO' and !empty($rowqr['aprovadopor'])) {
													echo ($rowqr['aprovadopor']);
												} ?></td>
										<? } ?>
									</tr>
								</tbody>

							</table>
						</div>



					</div>

					<!-- Unidade -->
					<? if (!empty($rowqr['idprodserv'])) { ?>
						<div class="col-xs-1">
							<input name="<?= $pd ?>_un" type="hidden" value="<?= $rowqr['un'] ?>">
							<?= $rowqr['un'] ?>
						</div>
					<? } else { ?>
						<div class="col-xs-1"></div>
					<? } ?>
					<!-- Descricao -->
					<div class="prodserv-descr <?= $class ?>">
						<?
						if ($rowqr['idprodserv']) {
							$rowprof = SolmatController::buscarIdProdservFormulaPorIdProdserv($rowqr['idprodserv']);
						?>
							<input name="<?= $pd ?>_idprodserv" type="hidden" value="<?= $rowqr['idprodserv'] ?>">
							<input name="<?= $pd ?>_descr" type="hidden" value="<?= $rowqr['descr'] ?>">
							<a class="pointer" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?= $rowqr['idprodserv'] ?>')" title="<?= $rowqr['descr'] ?>"><span class="bold"><?= $rowqr['descr'] ?></span></a>
							<?
							if (!empty($rowqr['idprodserv']) && $_1_u_solmat_status != 'ABERTO') { ?>
								<a class="pointer fa fa-calculator hoverazul btn-lg pointer" onclick="calculoestoquedomodal(<?= $rowqr['idprodserv'] ?>, <?= $rowprof['idprodservformula'] ?>)"></a>
						<? }
						} else {
							echo $rowqr['descr'];
						} ?>
					</div>
					<div class="col-xs-3">
						<?
						// Novo campo inserido para trazer fórmulas/processos de acordo com a descricao do produto selecionado - ID460480 - ALBT 27/04/2021
						if (!empty($rowqr['idprodserv'])) {
							$fabricado = traduzid('prodserv', 'idprodserv', 'fabricado', $rowqr['idprodserv']);
							if ($fabricado == 'Y') {
						?>

								<input name="_pf_<?= $_acao ?>_solmatitem_idsolmatitem" id="idsolmatitem" type="hidden" style="width: 0%; text-align: center;" readonly='readonly' value="<?= $rowqr['idsolmatitem'] ?>">
								<select name="_pf_<?= $_acao ?>_solmatitem_idprodservformula" value="<?= $_1_u_solmatitem_idprodservformula ?>" onchange="formula(this,<?= $x ?>)" <?= $readonly ?> <? if ($readonly) { ?>style="width: 149px; background-color: #f5f5f5;" <? } else { ?> style="width: 149px;" <? } ?>>
									<option value="">Local de utilização do equipamento</option>
									<? fillselect(SolmatController::buscarProdServFormulaPorIdProdServEStatus($rowqr['idprodserv']), $rowqr['idprodservformula']); ?>
								</select>

							<? } elseif ($_GET['_modulo'] == 'soltag' || ($_1_u_solmat_tipo != "EQUIPAMENTOS" && $_GET['_modulo'] == 'solmatmeios')) { ?>
								-
						<? }
						} ?>
					</div>
					<!-- Observacao -->
					<div class="col-xs-3">
						<input name="<?= $pd ?>_obs" <?= $readonly ?> value="<?= $rowqr['obs'] ?>" placeholder="Observação:">
					</div>
					<!-- Exluir / Expandir -->
					<? if ($_1_u_solmat_status == 'ABERTO' || !empty($_GET['idsolmatcp'])) { ?>
						<div class="col-xs-1">
							<a class="btn btn-link"><i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable w" onclick="<?= $excluir ?>" alt="Excluir!"></i></a>
						</div>
					<? } else { ?>
						<div class="col-xs-1">
							<a class="btn btn-link expandir" title="Expandir" data-toggle="collapse" idnfitem="<?= $rowqr['idsolmatitem'] ?>" href="#solmatitem<?= $rowqr['idsolmatitem'] ?>">
								<i class="fa fa-arrows-v fa-1x cinzaclaro hoververmelho pointer ui-droppable w" alt="Expandir"></i></a>
						</div>
					<? } ?>
				</div>

				<?
				// lista intens da solmatitem
				if (!empty($rowqr['idprodserv'])) {
					$status = array('EXECUCAO', 'PROCESSANDO', 'CONCLUIDO', 'CANCELADO', 'SEPARADO', 'DIVERGENCIA');
					if (in_array($_1_u_solmat_status, $status) /* and ($rowqr['status']!='PENDENTE')*/) {
						?>
						<tr>
							<td><? listaitem1($listarLotes, $rowqr['idsolmatitem'], $rowqr['qtdc']); ?></td>
						</tr>
						<?
					}
				}
				$x++;
				?>
			</div>
		</div>
		<?
	}
}

function listaitem1($listarLotes, $idsolmatitem, $qtdc)
{
	global $i, $_1_u_solmat_status, $readonly, $readonly3, $prodservclass, $lote, $rowws, $_1_u_solmat_unidade, $tipounidadepadrao;


	if ($_1_u_solmat_status != 'ABERTO') {
		//bloqueio por 
	?>
		<div class="transition overflow-hidden collapse-in" id="solmatitem<?= $idsolmatitem ?>">
			<table class="table table-striped planilha">
				<tbody>
					<tr class="cabitem" style="height:40px;">
						<th class="cp_qtd" title="Quantidade"> Qtd</th>
						<th class="cp_un" title="Unidade">Un</th>
						<th class="cp_descricao">Lote</th>
						<th class="cp_formproc"></th>
						<th>Vencimento</th>
						<th></th>
						<th></th>
					</tr>
					<?
					if (count($listarLotes) > 0 && $_1_u_solmat_status != 'ABERTO') {
						//bloqueio por 
						$vencimento = "9999-99-99";
						foreach ($listarLotes as $lote) {
							$loteConsumo = SolmatController::buscarConsumoLotePorTipoObjetoConsumoEspec($lote["idlote"], $lote["idlotefracao"], $idsolmatitem, 'solmatitem');
							$corFundo = '';

							if ($loteConsumo['status'] == 'ABERTO')
								$corFundo = 'fundoVerde';
							else if ($loteConsumo['status'] == 'PENDENTE')
								$corFundo = 'fundoAmarelo';

							$i = $i + 1;
							$rowws = SolmatController::buscarLocalizacaoLotePorIdLote($lote['idlotefracao'], 'tagdim');
					?>
							<!-- $loteConsumo['qtdd'] ? 'fundoverde': '' -->
							<tr class="solmatitem<?= $idsolmatitem ?> <?= $corFundo ?>" <? !empty($loteConsumo['qtdd']) ? $readonly3 = 'readonly' : $readonly3 = '' ?>>

								<? if (!empty($loteConsumo['id'])) { ?>
									<td style="padding-left: 12px;" class='size6' <? if ($_1_u_solmat_status == 'CONCLUIDO') { ?><?= $readonly ?><? } ?>>
										<?= $loteConsumo['qtdd'] ?>
										<input type="hidden" class="mw-input" value="<?= $loteConsumo['qtdd'] ?>">
									</td>
								<? } else { ?>
									<td>
										<input class="mw-input lotecons_qtdd" autocomplete="off" type="text" name="lotecons_qtdd" data-vencimento="<?= $lote['vencimento'] ? $lote['vencimento'] : '' ?>" data-accept-dot="1" <?= $readonly3 ?> value="<?= number_format(tratanumero($lote['qtdd']), 2, ',', '.') ?>" onchange="gerarfracao(this,<?= $idsolmatitem ?>,<?= $lote['idlote'] ?>,<?= $lote['idlotefracao'] ?>,<?= $lote['qtd'] ?>,<?= $qtdc ?>)">
										<? $vencimento = $lote['vencimento']; ?>
									</td>
								<?
								}
								?>
								<td class="cp_un"> <?= number_format(tratanumero($lote['qtd']), 2, ',', '.') ?> <?= $lote['un'] ?></td>
								<td>
									<div class="d-flex flex-wrap justify-content-between">
										<? //busca o modulo
										//$listarModulos = SolmatController::buscarModulosComUnidadesVinculadasPorGetIdEmpresa($tipounidadepadrao, getidempresa("u.idempresa", $_GET['_modulo'])); 
										?>
										<a title="Lote" class="pointer" onclick="janelamodal('?_modulo=<?= $lote['idobjeto'] ?>&_acao=u&idlote=<?= $lote['idlote'] ?>')">
											<?= $lote['partida'] ?>/<?= $lote['exercicio'] ?>
										</a>
									</div>

								</td>
								<td></td>
								<td class="vencimento"><?= $lote['vencimento'] ? $lote['vencimento'] : '' ?></td>
								<td colspan="3" align="right">
									<a class="btn btn-link" title="Histórico" onClick="consumo(<?= $lote['idlote'] ?>);"><i class="fa fa-search azul pointer hoverazul"></i></a>
									<div id="consumo_<?= $lote['idlote'] ?>" style="display: none;">
										<input type="hidden" idlote="<?= $lote['idlote'] ?>">
										<?= $prodservclass->historicolotecons($lote['idlote'], $_1_u_solmat_unidade); ?>
									</div>
								</td>
							</tr>
						<? }
					} elseif (count($listarLotes) == 0 && $_1_u_solmat_status != 'CONCLUIDO') { ?>
						<tr>
							<? if (!empty($lote['idlotecons'])) { ?>
								<td class="<?= $idsolmatitem ?>" style="padding-left: 15px;"> <input class='size6'></td>
							<? } ?>
							<td></td>
							<td colspan="7" style="height: 40px;">
								<label class="alert-warning">Nenhum lote disponível</label>
							</td>
						</tr>
					<? } else { ?>
						<tr>
							<? if (!empty($lote['idlotecons'])) { ?>
								<td class="<?= $idsolmatitem ?>"> <input class='size6'></td>
							<? } else { ?>
								<td>-</td>
							<? } ?>

							<td colspan="8">
								<label for="" class="alert-warning">
									Nada foi consumido
								</label>
							</td>
						</tr>
					<?
					}
					?>
				</tbody>
			</table>
		</div>
<?
	}
}

$tabaud = "solmat";
require 'viewCriadoAlterado.php';
require_once(__DIR__ . "/../form/js/solmat_js.php");
?>