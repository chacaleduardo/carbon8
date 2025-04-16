<?
require_once("../inc/php/validaacesso.php");
require_once("../model/prodserv.php");
require_once("../inc/php/permissao.php");
if ($_POST){
	require_once("../inc/php/cbpost.php");
}

$pagvaltabela = "solmat";
$pagvalcampos = array(
	"idsolmat" => "pk"
);

$pagsql = "SELECT * from solmat where idsolmat = '#pkid'";

include_once("../inc/php/controlevariaveisgetpost.php");
// CONTROLLERS
require_once('./controllers/lote_controller.php');
require_once('./controllers/solmat_controller.php');
require_once('./controllers/tag_controller.php');

$prodServESolmatItem = SolmatController::buscarProdServESolMatItemPorIdSolMat($_1_u_solmat_idsolmat);
$data = explode(" ", $_1_u_solmat_criadoem);
$dataCriadoEm = explode("/", $data[0]);
$planejamentoProdserv = SolmatController::buscarPlanejamentoPorIdProdservMesExercioUnidade($_1_u_solmat_idunidade, $dataCriadoEm[2], $dataCriadoEm[1], $_1_u_solmat_idsolmat);
// listaitem($listarItensSolmat, 1, $planejamentoProdserv, $dataCriadoEm); 
$unidadepadrao = getUnidadePadraoModulo($_GET["_modulo"], cb::idempresa());
if (!empty($unidadepadrao)) 
	$tipounidadepadrao = traduzid('unidade', 'idunidade', 'idtipounidade', $unidadepadrao);

$arrOrdenado = [];
$arrDados = [];

foreach($prodServESolmatItem as $item)
{
	$lotes = SolmatController::buscarLocalizacaoLoteSolmatItem($item['idsolmatitem'], $item['idprodservformula'], $_1_u_solmat_status, $item['idprodserv'], $item['idsolmatitem'], 'solmatitem', $tipounidadepadrao);

	foreach($lotes as $key => $lote)
	{
		$loteConsumo = SolmatController::buscarConsumoLotePorTipoObjetoConsumoEspec($lote["idlote"], $lote["idlotefracao"], $item['idsolmatitem'], 'solmatitem');

		if(!$loteConsumo) continue;

		$lote['idlotecons'] = $loteConsumo['id'];
		$lote['statuslotecons'] = $loteConsumo['status'];
		$lote['qtddconsulmo'] = $loteConsumo['qtdd'];

		$arrOrdenado[$lote['descricao'] ?? "_{$item['idprodserv']}"][TagController::$alfabeto[$lote['coluna']] ?? "_{$item['idprodserv']}"][$lote['linha']][$lote['caixa']][$lote['idlotefracao']]['produtos'] = [
																									'qtdc' => $item['qtdc'],
																									'un' => $item['un'],
																									'descr' => $item['descr']
																								];

		$arrOrdenado[$lote['descricao'] ?? "_{$item['idprodserv']}"][TagController::$alfabeto[$lote['coluna']] ?? "_{$item['idprodserv']}"][$lote['linha']][$lote['caixa']][$lote['idlotefracao']]['produtos']['lotes'][] = $lote;

		if(isset($arrOrdenado[$lote['descricao']]))
			ksort($arrOrdenado[$lote['descricao']]);

		// $prodServ = SolmatController::buscarIdProdservFormulaPorIdProdserv($item['idprodserv']);
	}
}

ksort($arrOrdenado);

?>
<link rel="stylesheet" href="/form/css/solmatconferencia_css.css?version=1.2">
<link rel="stylesheet" href="./inc/js/qr-scanner/qr-scanner.css?v=2" />
<div class="container">
	<div class="row w-100 m-0">
		<? foreach($arrOrdenado as $descricao => $localizacao){ ?>
			<div class="col-xs-12 col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4><?= strpos($descricao, '_') === false ? $descricao : 'Sem localização' ?></h4>
					</div>
					<div class="panel-body">
						<div class="row m-0">
							<? foreach($localizacao as $colunaLabel => $colunas) { ?>
								<div class="col-xs-12 col-md-6">
									<div class="w-100 bg-gray mb-2">
										<h5 class="letra"><?= strpos($colunaLabel, '_') === false ? $colunaLabel : '-' ?></h5>
									</div>
									<div class="col-xs-12 mb-4 px-0">
										<div class="row m-0">
											<h5>
												<strong>Itens</strong>
											</h5>
										<? foreach($colunas as $linhas) {?>
											<? foreach($linhas as $caixas) {?>
												<? foreach($caixas as $idlote) {?>
													<? foreach($idlote as $produto) {?>
														<? if(!count($produto['lotes'])) continue;?>
														<div class="produto w-100 d-flex flex-wrap align-items-center justify-content-between mb-3">
															<div class="produto-header w-100 d-flex align-items-center justify-content-between mb-3">
																<div class="px-2 py-1">
																	<span><?= $produto['qtdc'] ?></span>
																</div>
																<div class="px-2 py-1">
																	<span><?= $produto['un'] ?></span>
																</div>
																<div class="col-xs-8 px-0">
																	<span class="text-center">
																	<?= $produto['descr'] ?>
																	</span>
																</div>
															</div>
															<ul class="lotes w-100">
																<li><p>Lotes</p></li>
																<? $nenhumLoteAtivo = true;
																	foreach($produto['lotes'] as $lote) { ?>
																	<? if(!$lote['statuslotecons']) continue;
																		$nenhumLoteAtivo = false; 
																		$atributos = "data-idlote='{$lote['idlote']}' data-idunidade='{$_1_u_solmat_idunidade}' data-idlotefracao='{$lote['idlotefracao']}' data-idlotecons='{$lote['idlotecons']}'";
																		$coluna = 'col-xs-2 text-center';

																		if($lote['statuslotecons'] == 'ABERTO')
																		{
																			$atributos = '';
																			$coluna = 'col-xs-2';
																		}
																	?>
																	<li class="mb-3 d-flex justify-content-end">
																		<?if($lote['linha']){?>
																			<span class="badge mr-2"><small>Linha</small> <?= $lote['linha']?></span>
																		<?}?>
																		<?if($lote['caixa']){?>
																			<span class="badge"><small>Caixa</small> <?= $lote['caixa']?></span>
																		<?}?>
																	</li>
																	<li class="d-flex flex-wrap d-flex flex-wrap justify-content-between lote-item mb-2 <?= !$atributos ? 'lotecons-aberto' : 'lotecons-pendente' ?>">
																		<div class="<?= $coluna ?> px-0">
																			<span><?= number_format(tratanumero($lote['qtdd']), 2, ',', '.') ?></span>
																		</div>
																		<div class="<?= $coluna ?> px-0">
																			<span><?=$lote['un'] ?></span>
																		</div>
																		<div class="col-xs-6 px-0 d-flex flex-wrap align-items-center justify-content-between">
																			<span class="text-center"><?=$lote['partida'] ?>/<?=$lote['exercicio'] ?></span>
																		</div>
																		<? if($atributos) { ?>
																			<button class="btn abrir-camera btn-primary" <?= $atributos ?>>
																				<i class="fa fa-camera m-0"></i>
																			</button>
																		<?}?>
																	</li>
																<?}
																if($nenhumLoteAtivo) { ?>
																	<li>Nenhum consumo de lote ativo.</li>
																<?}?>
															</ul>
														</div>
													<?}?>
												<?}?>
											<?}?>
										<?}?>
										</div>
									</div>									
								</div>
							<? } ?>	
						</div>
					</div>
				</div>
			</div>
		<?}?>
	</div>
</div>
<? require_once('./js/solmatconferencia_js.php') ?>