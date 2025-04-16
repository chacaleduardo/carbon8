<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/inclusaoresultado_controller.php");
require_once("../form/controllers/traresultados_controller.php");
require_once("../form/querys/resultado_query.php");



if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

//Parâmetros mandatórios para o carbon
$pagvaltabela = "resultado";
$pagvalcampos = array(
	"idresultado" => "pk"
);


$nomeModulo = $_GET['_modulo'];


//RECEBER O NOME E ROTULOS DO ALERTA SETADO NA PRODSERV
$alertaERotulosServico = InclusaoResultadoController::buscarNomeAlertaERotulosServico($_GET['idresultado']);
$nomeAlerta      			= $alertaERotulosServico["alertarotulo"];
$alertaRotuloMarcado   		= $alertaERotulosServico["alertarotuloy"];
$alertaRotuloDesmarcado   	= $alertaERotulosServico["alertarotulon"];
$idprodserv 	 			= $alertaERotulosServico["idprodserv"];

$CobrancaRes=InclusaoResultadoController::buscarCobrancaResultado($_GET['idresultado']);

//RECEBER OS SERVIÇOS VINCULADOS AO ALERTA SETADO NA PRODSERV
$servicosVinculados = json_encode(InclusaoResultadoController::buscarServicosVinculados($_GET['idresultado']));


$pagsql = ResultadoQuery::pagSql();
require_once("../inc/php/controlevariaveisgetpost.php");

if(!empty($_1_u_resultado_idresultado)){
	if($_GET['versao']){
		$jsonCongelado = InclusaoResultadoController::buscarJsonConfigJsonResultadoCongeladoVersaoAnterior($_1_u_resultado_idresultado,$_GET['versao']);
	}else{
		$jsonCongelado = InclusaoResultadoController::buscarJsonConfigJsonResultadoCongelado($_1_u_resultado_idresultado);
	}
	if(!empty($jsonCongelado)){
		$jsonC = unserialize(base64_decode($jsonCongelado));
		$_1_u_resultado_modelo = $jsonC['prodserv']['res']['modelo'];
		$_1_u_resultado_tipogmt = $jsonC['prodserv']['res']['tipogmt'];
		$_1_u_resultado_modo = $jsonC['prodserv']['res']['modo'];
	}
}

$dadosAmostra = InclusaoResultadoController::buscarDadosAmostraCabecalhoModuloResultados($_1_u_resultado_idamostra);
$jsonConfigJsonResultado = InclusaoResultadoController::buscarJsonConfigJsonResultado($_1_u_resultado_idresultado);
$prodservTipoOpcao = InclusaoResultadoController::buscarValorProdservTipoOpcao($_1_u_resultado_idtipoteste);
$somaOrificios = $_1_u_resultado_q1 + $_1_u_resultado_q2 + $_1_u_resultado_q3 + $_1_u_resultado_q4 + $_1_u_resultado_q5 + $_1_u_resultado_q6 + $_1_u_resultado_q7 + $_1_u_resultado_q8 + $_1_u_resultado_q9 + $_1_u_resultado_q10 + $_1_u_resultado_q11 + $_1_u_resultado_q12 + $_1_u_resultado_q13;

$_1_u_resultado_jsonresultado      	= $jsonConfigJsonResultado['jsonresultado'];
$_1_u_resultado_jsonconfig         	= $jsonConfigJsonResultado['jsonconfig'];

$unidadepadrao = getUnidadePadraoModulo($_GET["_modulo"], $_idempresa);
$moduloAmostra = getModuloAmostraPadrao($dadosAmostra["idunidade"]);
$rotuloFluxo = getStatusFluxo($pagvaltabela, 'idresultado', $_1_u_resultado_idresultado);



?>
<link href="/form/css/inclusaoresultado_css.css?version=1.3" rel="stylesheet">

<div class="col-md-12">
	<div class="panel panel-default">
		<div class="panel-body">
			<table id="amostra" style="width: 100%;">
			
				<tr>
				
					<td><strong>Registro:</strong></td>
					<td id="cabRegistro" cbidamostra="<?= $dadosAmostra["idamostra"] ?>">
						<label class='alert-warning'><?=(($dadosAmostra["idunidade"] == 1298) ? $dadosAmostra["idregistro"].'PET' : $dadosAmostra["idregistro"]) ?>
							<a title="Abrir Amostra" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=<?= $moduloAmostra ?>&idamostra=<?= $dadosAmostra["idamostra"] ?>&_idempresa=<?= $dadosAmostra['idempresa'] ?>" target="_blank"></a>
							<input type="hidden" id='idsecretaria' value="<?=$_1_u_resultado_idsecretaria?>">
							<input type="hidden" name="_1_<?=$_acao?>_resultado_quantidade" value="<?=$_1_u_resultado_quantidade?>">
						</label>
					
					</td>
					<td><strong>ID Teste:</strong></td>
					<td>
						<label class='alert-warning'>
							<?= $_1_u_resultado_idresultado ?>.<?= $_1_u_resultado_versao ?>
						</label>
					</td>
					<td style="width: 30px;">Cliente:</td>
					<td class="inputreadonly" nowrap style="padding:6px;"><?= $dadosAmostra["nome"] ?></td>
					<td><strong>Amostra:</strong></td>
					<td class="inputreadonly" style="padding:6px;"><?= $dadosAmostra["subtipoamostra"] ?></td>
					<?if (array_key_exists("vervalordolote", getModsUsr("MODULOS"))) {?>
					<td><strong>Custo:</strong> &nbsp;
						<i class="fa fa-money fa-1x verde  btn-lg pointer"  title="<?=number_format(tratanumero($_1_u_resultado_custo), 4, ',', '.')?>"></i>
					</td>
					<?}?>
					
					<?if(!empty($CobrancaRes)){?>
					<td><strong>Cobrança:</strong> &nbsp;
						<i class="fa fa-money fa-1x verde  btn-lg pointer"  title="	Nº Det.: <?=$CobrancaRes['idnotafiscal']?> -  <?=$CobrancaRes['status']?>"></i>
					</td>
					<?}?>
					<td>
						<span>
							<label title="<?= $rotuloFluxo['status'] ?>" class="alert-warning" id="statusButton"><?= mb_strtoupper($rotuloFluxo['rotulo'], 'UTF-8') ?></label>
						</span>
					</td>
					<td>
						<a title="Imprimir Cliente Amostra." class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/amostra.php?acao=i&idamostra=<?= $dadosAmostra['idamostra'] ?>&_idempresa=<?=$_1_u_resultado_idempresa?>')"></a>
					</td>
					<?
					$rowtipos = InclusaoResultadoController::buscarTiposDeEnvioDeEmailResultado($_1_u_resultado_idresultado);
					if(count($rowtipos) > 1){?>
						<td>
						<a class="pull-right" title="Ver emails enviados"  data-placement="left" data-toggle="popover" href="#modaltipos" data-trigger="click"><i class="fa fa-envelope-o cinza pointer"></i><i style="z-index: 2300;margin-left:-5px;margin-top:-7px;" class="fa fa-search cinza cinza pointer"></i></a>
							<!-- <i class="fa fa-envelope-o cinza pointer" title="Ver emails enviados" data-toggle="popover" href="#modaltipos" data-trigger="click"></i> -->
							<div id="modaltipos" style="display: none;" class="modal-popover">
								<table >
									<? foreach($rowtipos as $k => $row){
										switch($row['tipo']){
											case "EMAILOFICIAL": $tipo = 'Emails Oficiais'; break;
											case "EMAILOFICIALPOS": $tipo =  'Emails Oficiais Positivos'; break;
											default : $tipo = $row['tipo']; break;
										}
										?>
										<tr>
											<? $rowemail = InclusaoResultadoController::buscarUltimoMailfilaResultadoPorTpo($_1_u_resultado_idresultado,$row['tipo'])?>
											<td class="hoverazul pointer" onclick="janelamodal('?_modulo=envioemail&_acao=u&idmailfila=<?= $rowemail['idmailfila']?>')"><?=$tipo?></td>
										</tr>
									<?}?>
								</table>
							</div>
						</td>
					<?}else{
						$rowemail = InclusaoResultadoController::buscarUltimoMailfilaResultado($_1_u_resultado_idresultado,getidempresa('m.idempresa', 'envioemail'));
						if (!empty($rowemail)) { ?>
							<td>
								<a class="pull-right" title="Ver emails enviados" onclick="janelamodal('?_modulo=envioemail&_acao=u&idmailfila=<?= $rowemail['idmailfila'] ?>')"><i class="fa fa-envelope-o cinza pointer"></i><i style="z-index: 2300;margin-left:-5px;margin-top:-7px;" class="fa fa-search cinza cinza pointer"></i></a>
							</td>
						<? } 
					}
					?>
				</tr>
			</table>
		</div>
	</div>
</div>
<div class="col-md-2">
	<?	montarMenuLateralServicosAmostra();	?>
</div>
<div class="col-md-10">
	<div class="panel panel-default">
		<div class="panel-body">
			<?


			if (!empty($_1_u_resultado_idresultado)) {


				/*****************************************/
				/*SE MODO FOR INDIVIDUAL [INICIO]
				/*****************************************/
				if ($_1_u_resultado_modo == "IND"   && $_1_u_resultado_modelo != 'DINAMICO') {

					$quantidadeResultadosIndividuais = InclusaoResultadoController::buscarNumeroDelinhasResultadoIndividual($_1_u_resultado_idresultado);

					if ($quantidadeResultadosIndividuais == 0 and $_1_u_resultado_quantidade > 0) { //inserir quando não tiver teste para aquele resultado

						//se for de um teste do bioterio verifica se existe numeração
						if (!empty($_1_u_resultado_idservicoensaio)) {

							$servicoEnsaio=InclusaoResultadoController::buscarIdentificacaoResultado($_1_u_resultado_idservicoensaio);
							$servicoEnsaioData = $servicoEnsaio['data'];
							$nurRowsServicoEnsaio = $servicoEnsaio['numRows'];

						} else {

							$servicoEnsaio=InclusaoResultadoController::buscarIdentificacaoResultado($_1_u_resultado_idamostra);
							$servicoEnsaioData = $servicoEnsaio['data'];
							$nurRowsServicoEnsaio = $servicoEnsaio['numRows'];

						}

						if ($nurRowsServicoEnsaio > 0) {

							foreach ($servicoEnsaioData as $key => $rowindx) {
								$statusInsertResultadoIndividual =  InclusaoResultadoController::inserirResultadoIndividual($_SESSION["SESSAO"]["IDEMPRESA"], $_1_u_resultado_idresultado, $rowindx['identificacao'], $_SESSION["SESSAO"]["USUARIO"]);
							}

						} else {

							for ($z = 1; $z <= $_1_u_resultado_quantidade; $z++) {
								$statusInsertResultadoIndividual = InclusaoResultadoController::inserirResultadoIndividual($_SESSION["SESSAO"]["IDEMPRESA"], $_1_u_resultado_idresultado,'', $_SESSION["SESSAO"]["USUARIO"]);
							}

						}
						
					} else if ($quantidadeResultadosIndividuais > $_1_u_resultado_quantidade) { // excluir quando a quantidade de testes for maior que a quantidade do resultado

						$dif = $quantidadeResultadosIndividuais - $_1_u_resultado_quantidade;

						for ($d = 1; $d <= $dif; $d++) {
							$statusDeletarResultadoIndividual = InclusaoResultadoController::deletarResultadoIndividual($_1_u_resultado_idresultado);
						}

					} else if ($quantidadeResultadosIndividuais < $_1_u_resultado_quantidade) { //inserir quando a quantidade de testes for inferior a quantidade do resultado

						$dif = $_1_u_resultado_quantidade - $quantidadeResultadosIndividuais;
						for ($d = 1; $d <= $dif; $d++) {
							$statusInsertResultadoIndividual = InclusaoResultadoController::inserirResultadoIndividual($_SESSION["SESSAO"]["IDEMPRESA"], $_1_u_resultado_idresultado,'', $_SESSION["SESSAO"]["USUARIO"]);
						}
					}
				}

				/*****************************************SE MODO FOR INDIVIDUAL [FIM]*****************************************/
				$planteisResultado = array_map(function($item) {
					return $item['idplantel'];
				}, InclusaoResultadoController::buscarPlantelPorIdResultado($_1_u_resultado_idresultado));

				$linKResultado = ($_1_u_resultado_modelo == "DINAMICOREFERENCIA" ? "/report/emissaoresultadodinamicoreferencia.php?idresultado=" : "/report/emissaoresultado.php?idresultado=").$_1_u_resultado_idresultado;

				if(!in_array($_1_u_resultado_modelo, ["DINAMICOREFERENCIA", "DROP", "SELETIVO"]) && in_array(34, $planteisResultado))
					$linKResultado = "/report/emissaoresultadopet.php?idresultado=$_1_u_resultado_idresultado";


				/**
				 * Data limite definida pelo William do Laudo para que carregue o layout de resultados antigos
				 */
				$dataCriacao = DateTime::createFromFormat('d/m/Y H:i:s', $_1_u_resultado_criadoem);
				$dataCriacaoFormatada = $dataCriacao->format('Y-m-d');
				if(date('Y-m-d', strtotime($dataCriacaoFormatada)) <= date('Y-m-d', strtotime('2024-10-01'))) {
					$linKResultado = "/report/emissaoresultado.php?idresultado=".$_1_u_resultado_idresultado;
				}
				?>

				<h4 class='nowrap'><span class="cinza">Resultado para </span>
					<span id="tipo-teste" class="negrito"><?= $_1_u_resultado_tipoteste ?></span>
					<span class="negrito">
						<a title="Abrir cadastro produto" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=prodserv&_acao=u&idprodserv=<?= $_1_u_resultado_idtipoteste ?>" target="_blank"></a>
						<a style="float:right" title="Visualizar Impressão" class="fa fa-print fade pointer hoverazul" href="<?= $linKResultado ?>&_idempresa=<?=$_1_u_resultado_idempresa?>" target="_blank"></a>
						<? if ($_1_u_resultado_jsonresultado == "" && $_1_u_resultado_modelo == "DINÂMICO") { ?>
							<i class="fa fa-exclamation-triangle fa-1x laranja pointer" title="Resultados ainda não foram salvos, favor salvar a página para que os dados apareçam no relatório."></i>
						<? } ?>
					</span>
				</h4>
				<hr>
				<input type="hidden" name="_1_u_resultado_idresultado" value="<?= $_1_u_resultado_idresultado ?>" id="idresultado">

				<?





				/*****************************************/
				/*SE MODO FOR SELETIVO AGRUPADO [INICIO]
				/*****************************************/
				if ($_1_u_resultado_modelo == "SELETIVO" && $_1_u_resultado_modo == "AGRUP") {
					//Se for tipo GMT
					$keyreadonly = "readonly"; //Esta variavel deixa as caixas de digitacao somente leitura. Para desabilitar, colocar valor ''
				?>
					<br>
					<div class="row row-eq-height">
						<div class="col-md-2">
							<div class="row" style="margin: 3px;border: 1px solid #ccc;font-size: 10px;  background-color: #ccc;">
								<div class="col-md-12">
									<font class="graybold bold">Selecionar Ação:</font>
								</div>
								<div class="col-md-12">
									<font class="graybold"><input type="radio" value="+" name="xoper" class="tablehidden" onclick="setoper('+');" checked> Adicionar</font>
								</div>
								<div class="col-md-12">
									<font class="graybold"><input type="radio" value="-" name="xoper" class="tablehidden" onclick="setoper('-');"> Subtrair</font>
								</div>
							</div>
						</div>
						<div class="col-md-5">
							<div id="tbOrificios">
								<div class="row">
									<? if (!empty($prodservTipoOpcao[1]) or $prodservTipoOpcao[1] === '0') { ?>
										<div class="col-sm-4">
											<div>
												<input type="text" name="_1_u_resultado_q1" value="<?= $_1_u_resultado_q1 ?>" size="3" id="k_1" <?= $keyreadonly ?>> x <span><?= $prodservTipoOpcao[1] ?></span>
											</div>
										</div>
									<? } ?>
									<? if (!empty($prodservTipoOpcao[6]) or $prodservTipoOpcao[6] === '0') { ?>
										<div class="col-sm-4">
											<div>
												<input type="text" name="_1_u_resultado_q6" value="<?= $_1_u_resultado_q6 ?>" size="3" id="k_6" <?= $keyreadonly ?>> x <span><?= $prodservTipoOpcao[6] ?></span>
											</div>
										</div>
									<? } ?>
									<? if (!empty($prodservTipoOpcao[11]) or $prodservTipoOpcao[11] === '0') { ?>
										<div class="col-sm-4">
											<div>
												<input type="text" name="_1_u_resultado_q11" value="<?= $_1_u_resultado_q11 ?>" size="3" id="k_11" <?= $keyreadonly ?>> x <span><?= $prodservTipoOpcao[11] ?></span>
											</div>
										</div>
									<? } ?>
								</div>


								<div class="row">
									<? if (!empty($prodservTipoOpcao[2]) or $prodservTipoOpcao[2] === '0') { ?>
										<div class="col-sm-4">
											<div>
												<input type="text" name="_1_u_resultado_q2" value="<?= $_1_u_resultado_q2 ?>" size="3" id="k_2" <?= $keyreadonly ?>> x <span><?= $prodservTipoOpcao[2] ?></span>
											</div>
										</div>
									<? } ?>
									<? if (!empty($prodservTipoOpcao[7]) or $prodservTipoOpcao[7] === '0') { ?>
										<div class="col-sm-4">
											<div>
												<input type="text" name="_1_u_resultado_q7" value="<?= $_1_u_resultado_q7 ?>" size="3" id="k_7" <?= $keyreadonly ?>> x <span><?= $prodservTipoOpcao[7] ?></span>
											</div>
										</div>
									<? } ?>
									<? if (!empty($prodservTipoOpcao[12]) or $prodservTipoOpcao[12] === '0') { ?>
										<div class="col-sm-4">
											<div>
												<input type="text" name="_1_u_resultado_q12" value="<?= $_1_u_resultado_q12 ?>" size="3" id="k_12" <?= $keyreadonly ?>> x <span><?= $prodservTipoOpcao[12] ?></span>
											</div>
										</div>
									<? } ?>
								</div>
								<div class="row">

									<? if (!empty($prodservTipoOpcao[3]) or $prodservTipoOpcao[3] === '0') { ?>
										<div class="col-sm-4">
											<div>
												<input type="text" name="_1_u_resultado_q3" value="<?= $_1_u_resultado_q3 ?>" size="3" id="k_3" <?= $keyreadonly ?>> x <span><?= $prodservTipoOpcao[3] ?></span>
											</div>
										</div>
									<? } ?>
									<? if (!empty($prodservTipoOpcao[8]) or $prodservTipoOpcao[8] === '0') { ?>
										<div class="col-sm-4">
											<div>
												<input type="text" name="_1_u_resultado_q8" value="<?= $_1_u_resultado_q8 ?>" size="3" id="k_8" <?= $keyreadonly ?>> x <span><?= $prodservTipoOpcao[8] ?></span>
											</div>
										</div>
									<? } ?>
									<? if (!empty($prodservTipoOpcao[13]) or $prodservTipoOpcao[13] === '0') { ?>
										<div class="col-sm-4">
											<div>
												<input type="text" name="_1_u_resultado_q13" value="<?= $_1_u_resultado_q13 ?>" size="3" id="k_13" <?= $keyreadonly ?>> x <span><?= $prodservTipoOpcao[13] ?></span>
											</div>
										</div>
									<? } ?>
								</div>
								<div class="row">

									<? if (!empty($prodservTipoOpcao[4]) or $prodservTipoOpcao[4] === '0') { ?>
										<div class="col-sm-4">
											<div>
												<input type="text" name="_1_u_resultado_q4" value="<?= $_1_u_resultado_q4 ?>" size="3" id="k_4" <?= $keyreadonly ?>> x <span><?= $prodservTipoOpcao[4] ?></span>
											</div>
										</div>
									<? } ?>
									<? if (!empty($prodservTipoOpcao[9]) or $prodservTipoOpcao[9] === '0') { ?>
										<div class="col-sm-4">
											<div>
												<input type="text" name="_1_u_resultado_q9" value="<?= $_1_u_resultado_q9 ?>" size="3" id="k_9" <?= $keyreadonly ?>> x <span><?= $prodservTipoOpcao[9] ?></span>
											</div>
										</div>
									<? } ?>
								</div>
								<div class="row">

									<? if (!empty($prodservTipoOpcao[5]) or $prodservTipoOpcao[5] === '0') { ?>
										<div class="col-sm-4">
											<div>
												<input type="text" name="_1_u_resultado_q5" value="<?= $_1_u_resultado_q5 ?>" size="3" id="k_5" <?= $keyreadonly ?>> x <span><?= $prodservTipoOpcao[5] ?></span>
											</div>
										</div>
									<? } ?>
									<? if (!empty($prodservTipoOpcao[10]) or $prodservTipoOpcao[10] === '0') { ?>
										<div class="col-sm-4">
											<div>
												<input type="text" name="_1_u_resultado_q10" value="<?= $_1_u_resultado_q10 ?>" size="3" id="k_10" <?= $keyreadonly ?>> x <span><?= $prodservTipoOpcao[10] ?></span>
											</div>
										</div>
									<? } ?>

									<div class="col-md-12">
										<div style="padding:8px !important; background: #faebcc">
											Total: <span style="font-size: 12pt; color: red;" id="somaorificios"><?= $somaOrificios ?></span>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-4">
										<div style="padding:8px !important; background-color: #709ABE; ">
											GTM: <span style="line-height: 20px;"><?= $_1_u_resultado_gmt ?></span>
										</div>
									</div>
									<div class="col-sm-4">
										<div style="padding:8px !important;background-color: #709ABE">
											IDT: <span style="line-height: 20px;"><?= $_1_u_resultado_idt ?></span>
										</div>
									</div>
									<div class="col-sm-4">
										<div style="padding:8px !important; background-color: #709ABE">
											CV: <span style="line-height: 20px;"><?= $_1_u_resultado_var ?></span>
										</div>
									</div>
								</div>
							</div>
						</div>


						<div class="col-sm-4">
							<div style="background-color: #ccc;padding: 4px;">
								<div class="row">
									<div class="col-sm-12">
										<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Qtd. Positivos:</span></div>
										<div class="col-sm-8"><input name="_1_u_resultado_positividade" id="comp_pos" type="number" size="2" value="<?= $_1_u_resultado_positividade ?>" style="float:left; width: 80px"></div>
									</div>
									<div class="col-sm-12">
										<div class="col-sm-4"><span class="col-md-12" style="float:left;line-height: 16px;text-align: left;"><?= $nomeAlerta; ?>:</span>
											<small class="col-md-12" style="font-size:8px;text-align:left;"><i>(marcado - <?= $alertaRotuloMarcado; ?>)</i></small>
											<small class="col-md-12" style="font-size:8px;text-align:left;"><i>(desmarcado - <?= $alertaRotuloDesmarcado; ?>)</i></small>
										</div>
										<div class="col-sm-8"><input type="checkbox" id="chAlerta" <?= $stralerta ?> onClick="verificarEIncluirServicosVinculados(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>

									</div>

									<div class="col-sm-12">
										<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;display: <?= $divdisplay ?>;">Tipo Alerta:</span></div>
										<div class="col-sm-8">
											<div id="dTipoAlerta" style=" display: <?= $divdisplay ?>;">

												<select name="_1_u_resultado_tipoalerta">
													<option value=""></option>
													<? 
														$arrProdservTipoAlerta = InclusaoResultadoController::buscarConfiguracaoAlerta($_1_u_resultado_idtipoteste);
														fillselect($arrProdservTipoAlerta, $_1_u_resultado_tipoalerta); 

													?>
												</select>
											</div>
										</div>
									</div>

									
									<?if($_1_u_resultado_idtipoteste == 15347){?>

										<div class="col-sm-12">
											<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;display: <?= $divdisplay ?>;">Tipo Agente:</span></div>
											<div class="col-sm-8">
												<div id="dTipoAgente" style=" display: <?= $divdisplay ?>;">
												
													<select  name="_1_u_resultado_tipoagente" vnulo>
														<option value=""></option>
														<? 
															$arrProdservTipoAgente = InclusaoResultadoController::buscarConfiguracaoAgente($_1_u_resultado_idtipoteste);
															fillselect($arrProdservTipoAgente, $_1_u_resultado_tipoagente);
														?>
													</select>
												</div>
											</div>
										</div>
										<?}?>
									<div class="col-sm-12" style="display:<?= $divdisplayins ?>">
										<div class="col-sm-8"><span style="float:left;line-height: 30px;text-align: left;">Mostra Insumo:</span></div>
										<div class="col-sm-4"><input type="checkbox" id="chMostraInsumo" <?= $strmostrainsumo ?> onClick="mostraInsumo(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>

									</div>
								</div>
							</div>
						</div>
					</div>

					<?
					/*****************************************SE MODO FOR SELETIVO AGRUPADO [FIM]*****************************************/







					/*****************************************/
					/*SE MODO FOR SELETIVO INDIVIDUAL [INICIO]
					/*****************************************/

				} elseif ($_1_u_resultado_modelo == "SELETIVO" and $_1_u_resultado_modo == "IND") { //Se for tipo GMT
					$keyreadonly = "readonly"; //Esta variavel deixa as caixas de digitacao somente leitura. Para desabilitar, colocar valor ''
					?>
					<br>
					<div align="center">
					<?


						$resultadoIndividual = InclusaoResultadoController::buscarResultadoIndividual($_1_u_resultado_idresultado);

						$x = 0;
						$i = 2;
						$tipoespecial = substr($_1_u_resultado_tipoespecial, 0, -4);
						$tab = 0;

						foreach ($resultadoIndividual as $key => $rowind) {
							$i = $i + 1;
							$tab = $tab + 2;

							if (($x % 7) == 0) {
						?>
								<div class="col-sm-4">
									<div class="interna3">
										<div class="row">
											<div class="col-sm-4 text-right">
												ID
											</div>

											<div class="col-sm-4">
												Orif.
											</div>
											<div class="col-sm-4">
												Valor
											</div>

										</div>
									</div>
								<?
							}
							$x = $x + 1;
								?>
								<div class="row divdescritivo">
									<div class="col-sm-12">
										<div class="interna2 ">
											<div class="col-sm-1">
												<span style="line-height: 30px;"><?= $x ?></span>
											</div>
											<div class="col-sm-4">
												<div class="interna">
													<input type="hidden" name="_<?= $i ?>_u_resultadoindividual_idresultadoindividual" value="<?= $rowind['idresultadoindividual'] ?>" size="3">
													<input tabindex="<?= $tab ?>" type="text" style="width:100% !important; font-size:11px" title="Identificação" placeholder="ID" name="_<?= $i ?>_u_resultadoindividual_identificacao" value="<?= $rowind['identificacao'] ?>" size="10">
												</div>
											</div>
											<!--JLAL - 20/10/01 /*Alteração do evento change para onkeyup, para atualizar o valor sem precisar clicar fora ou em outro campo*/-->
											<div class="col-sm-2">
												<div class="interna">
													<input style="width:100% !important; font-size:11px" tabindex="<?= $tab + 1 ?>" type="text" title="tecla" id="tecla<?= $i ?>" onkeyup="setresultadoind(<?= $i ?>);" name="_<?= $i ?>_u_resultadoindividual_valor" value="<?= $rowind['valor'] ?>" size="1">
													<input type="hidden" title="tecla" id="resultado<?= $i ?>" name="_<?= $i ?>_u_resultadoindividual_resultado" value="<?= $rowind['resultado'] ?>" size="1">
													<input type="hidden" name="tipoespecial" value="<?= $_1_u_resultado_tipoespecial ?>" size="1">
													<input type="hidden" name="tipoteste" value="<?= $tipoespecial ?>" size="1">
												</div>
											</div>
											<div class="col-sm-3">
												<div class="interna">
													<select class="seltit" id="rotulo<?= $i ?>" title="Resultado" name="resultado" vnulo disabled="disabled" style="background: #ddd;width:100% !important; font-size:11px">
														<option value=""></option>
														<? 
															$arrProdservTipoOpcaoResultado = InclusaoResultadoController::buscarValorProdservTipoOpcaoResultado($_1_u_resultado_idtipoteste);
															fillselect($arrProdservTipoOpcaoResultado, $rowind['resultado']	); 
														?>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
								<?
								if (($x % 7) == 0) {
								?>
								</div>
							<?
								}
							}
							if (($x % 7) != 0) {
							?>
						</div>
					</div>
							<?}?>


					<?
					$stralerta = "";
					if ($_1_u_resultado_alerta == "Y") {
						$stralerta = "checked";
						$divdisplay = "block";
					} else {
						$divdisplay = "none";
					}

					$strmostrainsumo = "";
					if ($_1_u_resultado_mostraformulains == "Y") {
						$strmostrainsumo = "checked";
						$divdisplayins = "block";
					} else {
						$divdisplayins = "none";
					}					
					?>

						<div class="col-sm-4">
							<? if ($_1_u_resultado_tipogmt != "N/A") { ?>
								<div class="interna3" style="background-color:#709ABE;height: 45px;">
									<div class="row">
										<div class="col-sm-12">
											<div style="padding-left: 6px;padding-right: 6px;">
												GMT <span style="float:right"><?= $_1_u_resultado_gmt ?></span>
											</div>
										</div>
									</div>
								</div>
							<? } ?>
							<div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">
								<div class="row">
									<div class="col-sm-12">
										<div style="padding-left: 6px;padding-right: 6px;line-height: 30px">
											Alerta <span style="float:right"><input type="checkbox" id="chAlerta" <?= $stralerta ?> onClick="verificarEIncluirServicosVinculados(<?= $_1_u_resultado_idresultado ?>);"></span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?
									/*****************************************SE MODO FOR SELETIVO INDIVIDUAL [FIM]*****************************************/







					/*****************************************/
					/*SE MODO FOR DINAMICO [INICIO]
					/*****************************************/
				} else if ($_1_u_resultado_modelo == "DINÂMICO") {
					if (!empty($_1_u_resultado_idtipoteste) and empty($_1_u_resultado_descritivo)) {

						$_1_u_resultado_descritivo = TraResultadosController::buscarTextoInclusaoDeResultado($_1_u_resultado_idtipoteste);

					}

					if (!empty($_1_u_resultado_idtipoteste)) {

						$jsonresultado = json_decode($_1_u_resultado_jsonresultado);
						$jsonconfig = InclusaoResultadoController::buscarJsonConfigServico($_1_u_resultado_idtipoteste);

						if (empty($_1_u_resultado_jsonconfig) or (count($jsonresultado->INDIVIDUAL) == 0)) {
							$_1_u_resultado_jsonconfig = $jsonconfig;
						}

						$input = ($_1_u_resultado_jsonconfig);
						$jsonconfig = json_decode($input);
						$dataagrup = '<div>';
						$qtdcampos = 1;

						/*JLAL - 20/10/01 
							Alterações: Validar identificadores cadastrados na amostra; Validar campo indentificador cadastrado na prodserv;
							Validar unidade especifica para buscar a configuração na prodserv; Validar campos tipo fixo para só aparecer quando não tiver identificador e nem campo identificador na prodserv;
							Função de aplicar para todos, apenas em campos tipo selecionavel e fixo quando tem campo identificador na prodeserv e identificador na amostra;
							Retrocompatibilidade com as funcionaldiades antigas, para que nenhum resultado perca a configuração;   
						*/
						//sql para trazer a unidade especifica cadastrada na amostra

						$identificacaoEPlantel = InclusaoResultadoController::buscarEspecieAmostra($_1_u_resultado_idamostra);
						$idplantel = $identificacaoEPlantel['idplantel'];
						$identificacao = $identificacaoEPlantel['identificacao'];
						$unidade = !empty($idplantel) ? $idplantel : 'todas';
						
						//Validação para verificar se é a prodserv antiga
						if ($jsonconfig->personalizados) {
							foreach ($jsonconfig->personalizados as $key) {
								if ($key->vinculo == 'INDIVIDUAL') {
									$headind .= '<div class="col-sm-1 text-center">';
									$headind .= $key->titulo;
									$headind .= '</div>';
									$qtdcampos++;
								}

								if ($key->vinculo == 'AGRUPADO') {
									$dataagrup .= '<div class="col-sm-4"><label style="color:#333 !important;font-weight:normal !important;">' . $key->titulo . '</label>';
									$dataagrup .= criarCamposJsonConfigAntigo($key);
									$dataagrup .= '</div>';
								} else {
									if ($key->tipo == 'textarea') {
										$n = 1;
									} else {
										$n = 1;
									}
									$dataind .= '<div class="col-sm-' . $n . '">';
									$dataind .= criarCamposJsonConfigAntigo($key);
									$dataind .= '</div>';
								}
							}
						} else {
							//Nova configuração implementada para atender a nova prodserv
							foreach ($jsonconfig->unidadeBloco as $unidadeNeg) {
								$unidadePadrao = $unidadeNeg->unidade == "" ? "todas" : $unidadeNeg->unidade;
								//Validação para verificar se existe uma configuração na prodserv que bate com o tipo de unidade de negocio,
								//se não existir ele pega a configuração com o tipo todas
								if ($unidadePadrao == $unidade) {
									$teste = 1;
									break;
								} else {
									$teste = 0;
								}
							}
							foreach ($jsonconfig->unidadeBloco as $unidadeNeg) {
								$unidadePadrao = $unidadeNeg->unidade == "" ? "todas" : $unidadeNeg->unidade;
								//Validação para verificar se existe uma configuração na prodserv que bate com o tipo de unidade de negocio,

								foreach ($unidadeNeg->personalizados as $key) {
									//Valida se existe campo identificador cadastrado na configuração da prodserv
									if ($unidadePadrao == $unidade && $teste == 1) {
										if ($key->tipo == 'identificador') {
											$tipoIdent = 1;
											break;
										} else {
											$tipoIdent = 0;
										}
										if ($key->tipo == 'selecionavel' || $key->tipo == 'fixo') {
											$inputRemover = 1;
										}
									} else if ($unidadePadrao == "todas" && $teste != 1) {
										if ($key->tipo == 'identificador') {
											$tipoIdent = 1;
											break;
										} else {
											$tipoIdent = 0;
										}
										if ($key->tipo == 'selecionavel' || $key->tipo == 'fixo') {
											$inputRemover = 1;
										}
									}
								}
							}
							foreach ($jsonconfig->unidadeBloco as $unidadeNeg) {
								$unidadePadrao = $unidadeNeg->unidade == "" ? "todas" : $unidadeNeg->unidade;

								//@521338 - ERRO ORDEM TABELA DINÂMICA SERVIÇOS
								//ordenando campos dinamicos conforme configuração da prodserv
								$camposOrdenados = [];
								foreach ($unidadeNeg->personalizados as $campos)
									$camposOrdenados[$campos->ordem] = $campos;
								ksort($camposOrdenados);

								if ($unidadePadrao == $unidade && $teste == 1) {
									foreach ($camposOrdenados as $key) {
										if ($unidadeNeg->index == $key->index) {
											if ($key->vinculo == 'INDIVIDUAL') {
												//Verifica se é campo do tipo selecionavel ou campo tipo fixo que tenha um campo identificador na config da prodserv
												//para poder implementar a funcionalidade de aplicar para todos
												if (($key->tipo == 'selecionavel') || ($key->tipo == "fixo" && !empty($identificacao &&  $tipoIdent == 1))) {
													if ($key->tipo == 'selecionavel') {
														$tipo = 1;
													} else if ($tipoIdent != 0 && !empty($identificacao) && $key->tipo == "fixo") {
														$tipo = 2;
													}
													$headind .= '<div class="col-sm-1 text-center">';
													$headind .= $key->titulo;
													$headind .= '<img src="./inc/img/iconApplyAll.png" width="16px" height="16px" title="Aplicar à todos" alt="Aplicar à todos" style="margin-left:5px" onclick="criarBotaoAbrirModalReplicarResultado(' . $tipo . ',' . $key->indice . ',' . $teste . ');">';
													$headind .= '<div class="configModal' . $key->indice . '"></div>';
													$headind .= '</div>';

													$qtdcampos++;
												} else {
													$headind .= '<div class="col-sm-1 text-center">';
													$headind .= $key->titulo;
													$headind .= '</div>';

													$qtdcampos++;
												}
											}
											if ($key->vinculo == 'AGRUPADO') {
												$dataagrup .= '<div class="col-sm-4"><label style="color:#333 !important;font-weight:normal !important;">' . $key->titulo . '</label>';
												$dataagrup .= criarCamposJsonConfig($key);
												$dataagrup .= '</div>';
											} else {
												if ($key->tipo == 'textarea') {
													$n = 1;
												} else {
													$n = 1;
												}
												$dataind .= '<div class="col-sm-' . $n . '">';
												$valorTeste = $key->vinculo;

												$valorOriginal = $jsonresultado->$valorTeste[$key->indice]->value;
												$dataind .= criarCamposJsonConfig($key, $valorOriginal);
												$dataind .= '</div>';
											}
										}
									}
								} else if ($unidadePadrao == "todas" && $teste != 1) {
									foreach ($camposOrdenados as $key) {
										if ($unidadeNeg->index == $key->index) {
											if ($key->vinculo == 'INDIVIDUAL') {
												if (($key->tipo == 'selecionavel') || ($key->tipo == "fixo" && !empty($identificacao &&  $tipoIdent == 1))) {
													if ($key->tipo == 'selecionavel') {
														$tipo = 1;
													} else if ($tipoIdent != 0 && !empty($identificacao) && $key->tipo == "fixo") {
														$tipo = 2;
													}
													$headind .= '<div class="col-sm-1 text-center">';
													$headind .= $key->titulo;
													$headind .= '<img src="./inc/img/iconApplyAll.png" width="16px" height="16px" title="Aplicar à todos" alt="Aplicar à todos" style="margin-left:5px" onclick="criarBotaoAbrirModalReplicarResultado(' . $tipo . ',' . $key->indice . ',' . $teste . ');">';
													$headind .= '<div class="configModal' . $key->indice . '"></div>';
													$headind .= '</div>';

													$qtdcampos++;
												} else {
													$headind .= '<div class="col-sm-1 text-center">';
													$headind .= $key->titulo;
													$headind .= '</div>';

													$qtdcampos++;
												}
											}
											if ($key->vinculo == 'AGRUPADO') {
												$dataagrup .= '<div class="col-sm-4"><label style="color:#333 !important;font-weight:normal !important;">' . $key->titulo . '</label>';
												$dataagrup .= criarCamposJsonConfig($key);
												$dataagrup .= '</div>';
											} else {
												if ($key->tipo == 'textarea') {
													$n = 1;
												} else {
													$n = 1;
												}
												$dataind .= '<div class="col-sm-' . $n . '">';
												$dataind .= criarCamposJsonConfig($key);
												$dataind .= '</div>';
											}
										}
									}
								}
							}
						}

						$dataagrup .= '</div>';
						$c = 1;
						$cabecalho .= ' <div class="row">
						<div class="col-sm-12">
							<div class="interna3" style="margin:0px;font-size:10px;">
								<div class="row">
								<div class="col-sm-1">         
									<img src="./inc/img/svg/checked.svg" style="display:none;margin-left:5px;" id="selectChecked" width="16px" height="16px" title="Selecionar todos os itens" alt="Selecionar todos os itens" style="margin-left:5px" onclick="uncheckedAll();">
									<img src="./inc/img/svg/unchecked.svg" style="margin-left:5px;"  id="unchecked" width="16px" height="16px" title="Selecionar todos os itens" alt="Selecionar todos os itens"  onclick="checkedAll();">
									<img src="./inc/img/removeAll.png" style="display:none;" id="removeChecked" width="16px" height="16px" title="Remover itens selecionados" alt="Remover itens selecionados" style="margin-left:5px" onclick="delSelecionados();">
									</div>
								
								' . $headind;
						$cabecalho .= '         </div>
								</div>
							</div>
						</div>';


						$fixo = 0;
						$fixoindice = '';
						$qtdfixo = 0;

						//Valida se é configuração da prodserv antiga, para trazer os campos certos
						if ($jsonconfig->personalizados) {
							foreach ($jsonconfig->personalizados as $key) {
								if ($key->tipo == 'fixo') {
									$fixoindice = $key->indice;
									foreach ($key->options as $k) {
										$qtdfixo++;
										$fixo = 1;
										$gridind .= '<div class="row  global" ><span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="acrescentarRowAoResultado(this,' . $c . ')" style="position:absolute;z-index:999;right:-15px;"></span>
										<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="removerRowDoResultado(this)" style="position:absolute;z-index:999;right:-40px;"></span>
													<div class="col-sm-12">
													<div class="interna2 " style="height:auto;">
														<div class="custom-control custom-checkbox">
															<input type="checkbox" class="custom-control-input remove" name="remove">
														</div>
													<div class="row">';
										$gridind .= '        <div class="col-sm-1 text-center">' . $c . '</div>';
										$gridind .= str_replace('data-indice=""', 'data-indice="' . $c . '"', str_replace('name="camp', 'name="' . $c . '_camp', $dataind));
										$gridind .= '     </div> </div>
											</div>
										</div>';
										$c++;
									}
								}
							}
						} else {
							//Traz os campos com a nova configuração buscada na prodsev
							foreach ($jsonconfig->unidadeBloco as $unidadeNeg) {
								$unidadePadrao = $unidadeNeg->unidade == "" ? "todas" : $unidadeNeg->unidade;
								if ($unidadePadrao == $unidade) {
									$teste = 1;
								} else {
									$teste = 0;
								}
								if ($unidadePadrao == $unidade && $teste == 1) {

									foreach ($unidadeNeg->personalizados as $key) {
										if ($unidadeNeg->index == $key->index && ($jsonresultado->QTDINDIVIDUAL == 0 || empty($jsonresultado))) {
											if ($key->tipo == 'fixo') {
												$fixoindice = $key->indice;
												foreach ($key->options as $k) {

													if ($tipoIdent == 0 || empty($identificacao)) {
														$qtdfixo++;

														$fixo = 1;
														$gridind .= '<div class="row  global">
														<span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="acrescentarRowAoResultado(this,' . $c . ')" style="position:absolute;z-index:999;right:-15px;"></span>
														<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="removerRowDoResultado(this)" style="position:absolute;z-index:999;right:-40px;"></span>
														<div class="col-sm-12">
														<div class="interna2" style="height:auto;">
														
															<div class="custom-control custom-checkbox">
																<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
															</div>
															<div class="row">';
														$gridind .= '     <div class="col-sm-1 text-center">' . $c . '</div>';
														$gridind .= str_replace('data-indice=""', 'data-indice="' . $c . '"', str_replace('name="camp', 'name="' . $c . '_camp', $dataind));
														$gridind .= '     </div> 
																</div>
															</div>
														</div>';
														$c++;
													}
												}
											}
										}
									}

								} else if ($unidadePadrao == "todas" && $teste != 1) {
									foreach ($unidadeNeg->personalizados as $key) {
										if ($unidadeNeg->index == $key->index && ($jsonresultado->QTDINDIVIDUAL == 0 || empty($jsonresultado))) {
											if ($key->tipo == 'fixo') {
												$fixoindice = $key->indice;
												foreach ($key->options as $k) {

													if ($tipoIdent == 0 || empty($identificacao)) {
														$qtdfixo++;
														$fixo = 1;
														$gridind .= '<div class="row  global">
														<span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="acrescentarRowAoResultado(this,' . $c . ')" style="position:absolute;z-index:999;right:-15px;"></span>
														<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="removerRowDoResultado(this)" style="position:absolute;z-index:999;right:-40px;"></span>
														<div class="col-sm-12">
														<div class="interna2" style="height:auto;">
															<div class="custom-control custom-checkbox">
																<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
															</div>
															<div class="row">
															';
														$gridind .= '     <div class="col-sm-1 text-center">' . $c . '</div>';
														$gridind .= str_replace('data-indice=""', 'data-indice="' . $c . '"', str_replace('name="camp', 'name="' . $c . '_camp', $dataind));
														$gridind .= '     </div> 
																</div>
															</div>
														</div>';
														$c++;
													}
												}
											}
										}
									}
								}
							}
						}

						$c = 1;
						//Traz a configuração gravada no jsonresultado se existir campo fixo
						if ($qtdfixo > 0 and count($jsonresultado->INDIVIDUAL) > 0) {
							$gridind = '';
							$vIndice = '';
							foreach ($jsonresultado->INDIVIDUAL as $k) {
								if ($vIndice != $k->indice) {
									$vIndice = $k->indice;
									$gridind .= '<div class="row  global" ><span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="acrescentarRowAoResultado(this,' . $vIndice . ')" style="position:absolute;z-index:999;right:-15px;"></span>
									<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="removerRowDoResultado(this)" style="position:absolute;z-index:999;right:-40px;"></span>
										<div class="col-sm-12">
										<div class="interna2 " style="height:auto;">
											<div class="custom-control custom-checkbox">
												<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
											</div>
												<div class="row">';
									$gridind .= '        <div class="col-sm-1 text-center">' . $vIndice . '</div>';
									$gridind .= str_replace('data-indice=""', 'data-indice="' . $vIndice . '"', str_replace('name="camp', 'name="' . $vIndice . '_camp', $dataind));
									$gridind .= '     </div> </div>
										</div>
									</div>';
									$c++;
								} else if ($c <= $jsonresultado->QTDINDIVIDUAL && empty($k->indice)) {
									$gridind .= '<div class="row  global" ><span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="acrescentarRowAoResultado(this,' . $c . ')" style="position:absolute;z-index:999;right:-15px;"></span>
									<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="removerRowDoResultado(this)" style="position:absolute;z-index:999;right:-40px;"></span>
										<div class="col-sm-12">
										<div class="interna2 " style="height:auto;">
											<div class="custom-control custom-checkbox">
												<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
											</div>
												<div class="row">';
									$gridind .= '        <div class="col-sm-1 text-center">' . $c . '</div>';
									$gridind .= str_replace('data-indice=""', 'data-indice="' . $c . '"', str_replace('name="camp', 'name="' . $c . '_camp', $dataind));
									$gridind .= '     </div> </div>
										</div>
									</div>';
									$c++;
								}
							}

							//Traz a config salva pelo usuário
						} else if (count($jsonresultado->INDIVIDUAL) > 0) {
							$gridind = '';
							$vIndice = '';
							foreach ($jsonresultado->INDIVIDUAL as $k) {
								if ($vIndice != $k->indice) {
									$vIndice = $k->indice;
									$onclick = ($_1_u_resultado_geraagente == 'Y') ? 'removerRowDoResultado(this,' . $vIndice . ')' : 'removerRowDoResultado(this)';

									$gridind .= '<div class="row  global" ><span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="acrescentarRowAoResultado(this,' . $vIndice . ')" style="position:absolute;z-index:999;right:-15px;"></span>
									<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="' . $onclick . '" style="position:absolute;z-index:999;right:-40px;"></span>
										<div class="col-sm-12">
										<div class="interna2 " style="height:auto;">
											<div class="custom-control custom-checkbox">
												<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
											</div>
												<div class="row">
												';
									$gridind .= '        <div class="col-sm-1 text-center">' . $vIndice . '</div>';
									$gridind .= str_replace('data-indice=""', 'data-indice="' . $vIndice . '"', str_replace('name="camp', 'name="' . $vIndice . '_camp', $dataind));
									$gridind .= '     </div> </div>
										</div>
									</div>';
									$c++;
								} else if ($c <= $jsonresultado->QTDINDIVIDUAL && empty($k->indice)) {
									$gridind .= '<div class="row  global" ><span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="acrescentarRowAoResultado(this,' . $c . ')" style="position:absolute;z-index:999;right:-15px;"></span>
									<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="removerRowDoResultado(this)" style="position:absolute;z-index:999;right:-40px;"></span>
										<div class="col-sm-12">
										<div class="interna2 " style="height:auto;">
											<div class="custom-control custom-checkbox">
												<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
											</div>
												<div class="row">';
									$gridind .= '        <div class="col-sm-1 text-center">' . $c . '</div>';
									$gridind .= str_replace('data-indice=""', 'data-indice="' . $c . '"', str_replace('name="camp', 'name="' . $c . '_camp', $dataind));
									$gridind .= '     </div> </div>
										</div>
									</div>';
									$c++;
								}
							}
						}

						//Valida se existe campo do tipo fixo, se não foi salvo ainda e se existe campo identificador ou identificações da amostra
						if ($fixo != 1 && (empty($identificacao) || $tipoIdent == 0) && $jsonresultado->QTDINDIVIDUAL == 0) {
							while ($c <= $_1_u_resultado_quantidade) {
								$gridind .= '<div class="row  global">
								<span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="acrescentarRowAoResultado(this,' . $c . ')" style="position:absolute;z-index:999;right:-15px;"></span>
								<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="removerRowDoResultado(this)" style="position:absolute;z-index:999;right:-40px;"></span>
								<div class="col-sm-12">
								<div class="interna2" style="height:auto;">
									<div class="custom-control custom-checkbox">
										<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
									</div>
									<div class="row">';
								$gridind .= '     <div class="col-sm-1 text-center">' . $c . '</div>';
								$gridind .= str_replace('data-indice=""', 'data-indice="' . $c . '"', str_replace('name="camp', 'name="' . $c . '_camp', $dataind));
								$gridind .= '     </div> 
										</div>
									</div>
								</div>';
								$c++;
							}

							//Valida se nao for do tipo fixo e se existir identificação e campo identificador e nao tiver sido salvo ainda
						} else if ($fixo != 1 && (!empty($identificacao) && $tipoIdent == 1) && $jsonresultado->QTDINDIVIDUAL == 0) {

							$arrIdentificacaoAmostra = InclusaoResultadoController::buscarIdentificacaoAmostra($_1_u_resultado_idamostra);
							foreach ($arrIdentificacaoAmostra as $key => $rowId) {
								$identificacao = $rowId['identificacao'];
								$gridind .= '<div class="row global">
								<span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="acrescentarRowAoResultado(this,' . $c . ')" style="position:absolute;z-index:999;right:-15px;"></span>
								<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="removerRowDoResultado(this)" style="position:absolute;z-index:999;right:-40px;"></span>
								<div class="col-sm-12">
								<div class="interna2" style="height:auto;">
									<div class="custom-control custom-checkbox">
										<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
									</div>
									<div class="row">';
								$gridind .= '     <div class="col-sm-1 text-center">' . $c . '</div>';
								$gridind .= str_replace('data-indice=""', 'data-indice="' . $c . '"', str_replace(
									'name="camp',
									'name="' . $c . '_camp',
									str_replace('value=""', 'value="' . $identificacao . '"', $dataind)
								));
								$gridind .= '     </div> 
										</div>
									</div>
								</div>';
								$c++;
							}
						}
					}

					echo $cabecalho . $gridind;
					//Valida para atualizar a quantidade de testes, caso chegue a zero ele pega da sessão o ultimo valor cadastrado para aquele tipo de resultado
					if ($_1_u_resultado_quantidade == 0) {
						$qtd = empty($_SESSION['auxqt']) ? 0 : $_SESSION['auxqt'];
						echo '<input type="hidden" id="qtTeste" value="' .$qtd. '" name="_1_u_resultado_quantidade">';
						unset($_SESSION['auxqt']);
					} else {
						if ($_SESSION['auxqt']) {
							echo '<input type="hidden" id="qtTeste" value="' . $_1_u_resultado_quantidade . '" name="_1_u_resultado_quantidade">';
						} else {
							$_SESSION['auxqt'] = $_1_u_resultado_quantidade;
							echo '<input type="hidden" id="qtTeste" value="' . $_1_u_resultado_quantidade . '" name="_1_u_resultado_quantidade">';
						}
					}

					$stralerta = "";
					if ($_1_u_resultado_alerta == "Y") {
						$stralerta = "checked";
						$divdisplay = "block";
					} else {
						$divdisplay = "none";
					}

					$strmostrainsumo = "";
					if ($_1_u_resultado_mostraformulains == "Y") {
						$strmostrainsumo = "checked";
						$divdisplayins = "block";
					} else {
						$divdisplayins = "none";
					}
						?>
						<div class="row">
							<div class="col-sm-9">
								<div style="background-color: #ccc;padding: 8px;">
									<div class="row">
										<div class="col-sm-12">

											<?
											echo $dataagrup;
											?>
											<input type="hidden" id="jsonresultado" name="_1_u_resultado_jsonresultado" value='<?= $_1_u_resultado_jsonresultado ?>'>
											<input type="hidden" id="jsonconfig" name="_1_u_resultado_jsonconfig" value='<?= $_1_u_resultado_jsonconfig ?>'>
										</div>

									</div>
								</div>
								<?

					//Valida se existe campo de calculo marcado na config da prodserv, se existe calculo marcado de GMT ou ART
					if ($_1_u_resultado_tipogmt != "N/A") {
						if ($_1_u_resultado_tipogmt == "GMT") {
							echo '<div class="row">
							<div class="col-sm-2">
							<div style="margin-left: 5px;padding:8px !important;background-color: #709ABE;">
								GMT: <span style="line-height: 10px;">' . $_1_u_resultado_gmt . '</span>
							</div>
							</div>
							</div>';
						}
						if ($_1_u_resultado_tipogmt == "ART") {
							echo '<div class="row">
							<div class="col-sm-2">
							<div style="margin-left: 5px;padding:8px !important;background-color: #709ABE; ">
								ART: <span style="line-height: 10px;">' . number_format(tratanumero($_1_u_resultado_gmt), 2, '.', '') . '</span>
							</div>
							</div>
							</div>';
						}
						if ($_1_u_resultado_tipogmt == "SOMA") {
							echo '<div class="row">
							<div class="col-sm-2">
							<div style="margin-left: 5px;padding:8px !important;background-color: #709ABE; ">
								SOMA: <span style="line-height: 10px;">' . number_format(tratanumero($_1_u_resultado_gmt), 2, '.', '') . '</span>
							</div>
							</div>
							</div>';
						}
						if ($_1_u_resultado_tipogmt == "PERC") {
							echo '<div class="row">
							<div class="col-sm-2">
							<div style="margin-left: 5px;padding:8px !important;background-color: #709ABE; ">
								PERC: <span style="line-height: 10px;">' . number_format(tratanumero($_1_u_resultado_gmt), 2, '.', '') . '%</span>
							</div>
							</div>
							</div>';
						}
					}
					?>
					</div>

					<div class="col-sm-3">
						<div style="background-color: #ccc;padding: 8px;">
							<div class="row">
								<div class="col-sm-12">
									<div class="col-sm-8"><span style="float:left;line-height: 30px;text-align: left;">Qtd. Positivos:</span></div>
									<div class="col-sm-4"><input name="_1_u_resultado_positividade" id="comp_pos" type="number" size="2" value="<?= $_1_u_resultado_positividade ?>" style="float:left; width: 40px"></div>
								</div>
								<div class="col-sm-12">
									<div class="col-sm-8"><span class="col-md-12" style="float:left;line-height: 16px;text-align: left;"><?= $nomeAlerta; ?>:</span>
										<small class="col-md-12" style="font-size:8px;text-align:left;"><i>(marcado - <?= $alertaRotuloMarcado; ?>)</i></small>
										<small class="col-md-12" style="font-size:8px;text-align:left;"><i>(desmarcado - <?= $alertaRotuloDesmarcado; ?>)</i></small>

									</div>
									<div class="col-sm-4"><input type="checkbox" id="chAlerta" <?= $stralerta ?> onClick="verificarEIncluirServicosVinculados(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>
								<?
								$stralerta = "";
								if ($_1_u_resultado_alerta == "Y") {
									$stralerta = "checked";
									$divdisplay = "block";
								} else {
									$divdisplay = "none";
								}
								?>
								<?
								$strmostrainsumo = "";
								if ($_1_u_resultado_mostraformulains == "Y") {
									$strmostrainsumo = "checked";
									$divdisplayins = "block";
								} else {
									$divdisplayins = "none";
								}
								?>
								</div>

								<div class="col-sm-12">
									<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;display: <?= $divdisplay ?>;">Tipo Alerta:</span></div>
									<div class="col-sm-8">
										<div id="dTipoAlerta" style=" display: <?= $divdisplay ?>;">
										
											<select name="_1_u_resultado_tipoalerta" >
												<option value=""></option>
												<? 
													$arrProdservTipoAlerta = InclusaoResultadoController::buscarConfiguracaoAlerta($_1_u_resultado_idtipoteste);
												 	fillselect($arrProdservTipoAlerta, $_1_u_resultado_tipoalerta); 

												?>

											</select>
											<input type="hidden" name="_1_<?= $_acao ?>_resultado_alerta" value="<?= $_1_u_resultado_alerta ?>">
										</div>
									</div>
								</div>

								<?if($_1_u_resultado_idtipoteste == 15347){?>

									<div class="col-sm-12">
										<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Tipo Agente:</span></div>
										<div class="col-sm-8">
											<div id="dTipoAgente">
											
												<select name="_1_u_resultado_tipoagente" vnulo>
													<option value=""></option>
													<? 
														$arrProdservTipoAgente = InclusaoResultadoController::buscarConfiguracaoAgente($_1_u_resultado_idtipoteste);
														fillselect($arrProdservTipoAgente, $_1_u_resultado_tipoagente);
													?>
												</select>
												<input type="hidden" name="_1_<?= $_acao ?>_resultado_alerta" value="<?= $_1_u_resultado_alerta ?>">
											</div>
										</div>
									</div>
									<?}?>
								<div class="col-sm-12" style="display:<?= $divdisplayins ?>">
									<div class="col-sm-8"><span style="float:left;line-height: 30px;text-align: left;">Mostra Insumo:</span></div>
									<div class="col-sm-4"><input type="checkbox" id="chMostraInsumo" <?= $strmostrainsumo ?> onClick="mostraInsumo(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>
								</div>
							</div>
						</div>


						<?
						if ($moduloAmostra == 'amostracqd' or $moduloAmostra == 'amostraprod') { //Controle de qualidade
						?>
						<div style="background-color: #709ABE;padding: 4px;margin-top: 4px;">
							<div class="row">
								<div class="col-sm-12">
									<div class="col-sm-12"><span style="float:left;line-height: 30px;text-align: left;">Resultado:</span></div>
									<div class="col-sm-12"><input class="size15" name="_1_u_resultado_resultadocertanalise" type="text" class="" value="<?= $_1_u_resultado_resultadocertanalise ?>"></div>
								</div>
								<div class="col-sm-12">
									<div class="col-sm-12"><span style="float:left;line-height: 30px;text-align: left;">Conclusão:</span></div>
									<div class="col-sm-12">
										<select name="_1_u_resultado_conformidade">
											<option value=""></option>
											<? fillselect(array(
												'CONFORME' => 'Conforme', 'NAO CONFORME' => 'Não Conforme', 'NAO SE APLICA' => 'Não se Aplica'
											), $_1_u_resultado_conformidade); ?>
										</select>
									</div>
								</div>
							</div>
						</div>
						<?}?>
					</div>
					</div>


					<style>
						.interna2 .col-sm-1,
						.interna3 .col-sm-1 {
							width: <?= (100 / $qtdcampos); ?>%;
						}
					</style>


					<?
					/*****************************************SE MODO FOR DINAMICO [FIM]*****************************************/





					
					/*****************************************/
					/*SE MODO FOR DESCRITIVO AGRUPADO [INICIO]
					/*****************************************/
				} elseif ($_1_u_resultado_modelo == "DESCRITIVO" and $_1_u_resultado_modo == "AGRUP") {

					if (!empty($_1_u_resultado_idtipoteste) and empty($_1_u_resultado_descritivo)) {

						$_1_u_resultado_descritivo = TraResultadosController::buscarTextoInclusaoDeResultado($_1_u_resultado_idtipoteste);

					}


					$stralerta = "";
					if ($_1_u_resultado_alerta == "Y") {
						$stralerta = "checked";
						$divdisplay = "block";
					} else {
						$divdisplay = "none";
					}

					$strmostrainsumo = "";
					if ($_1_u_resultado_mostraformulains == "Y") {
						$strmostrainsumo = "checked";
						$divdisplayins = "block";
					} else {
						$divdisplayins = "none";
					}

					?>

					<div align="center" style="padding: 0px 18px;">
						<div class="row">
							<div class="col-sm-9">
								<div style="background-color: #ccc;padding: 4px;">
									<div class="row">
										<div class="col-sm-12">
											<label id="lbaviso" class="idbox" style="display: none;"></label>
											<div id="diveditor" class="diveditor" onkeypress="pageStateChanged=true;" style="text-align: left"><?= $_1_u_resultado_descritivo ?></div>
											<textarea style="display: none; text-align: left" name="_1_u_resultado_descritivo"><?= $_1_u_resultado_descritivo ?></textarea>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-3">
								<div style="background-color: #ccc;padding: 4px;">
									<div class="row">
										<div class="col-sm-12">
											<div class="col-sm-8"><span style="float:left;line-height: 30px;text-align: left;">Qtd. Positivos:</span></div>
											<div class="col-sm-4"><input name="_1_u_resultado_positividade" id="comp_pos" type="number" size="2" value="<?= $_1_u_resultado_positividade ?>" style="float:left; width: 40px"></div>
										</div>


						<div class="col-sm-12">
							<div class="col-sm-8"><span class="col-md-12" style="float:left;line-height: 16px;text-align: left;"><?= $nomeAlerta; ?>:</span>
								<small class="col-md-12" style="font-size:8px;text-align:left;"><i>(marcado - <?= $alertaRotuloMarcado; ?>)</i></small>
								<small class="col-md-12" style="font-size:8px;text-align:left;"><i>(desmarcado - <?= $alertaRotuloDesmarcado; ?>)</i></small>
							</div>
							<div class="col-sm-4"><input type="checkbox" id="chAlerta" <?= $stralerta ?> onClick="verificarEIncluirServicosVinculados(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>
							<?
							$stralerta = "";
							if ($_1_u_resultado_alerta == "Y") {
								$stralerta = "checked";
								$divdisplay = "block";
							} else {
								$divdisplay = "none";
							}
							?>
							<?
							$strmostrainsumo = "";
							if ($_1_u_resultado_mostraformulains == "Y") {
								$strmostrainsumo = "checked";
								$divdisplayins = "block";
							} else {
								$divdisplayins = "none";
							}
							?>
						</div>

							<div class="col-sm-12">
								<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;display: <?= $divdisplay ?>;">Tipo Alerta:</span></div>
								<div class="col-sm-8">
									<div id="dTipoAlerta" style=" display: <?= $divdisplay ?>;">

										<select name="_1_u_resultado_tipoalerta" >
											<option value=""></option>
											<?
												$arrProdservTipoAlerta = InclusaoResultadoController::buscarConfiguracaoAlerta($_1_u_resultado_idtipoteste);
												fillselect($arrProdservTipoAlerta, $_1_u_resultado_tipoalerta); 
											?>
										</select>
									</div>
								</div>
							</div>

							<?if($_1_u_resultado_idtipoteste == 15347){?>

								<div class="col-sm-12">
									<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;display: <?= $divdisplay ?>;">Tipo Agente:</span></div>
									<div class="col-sm-8">
										<div id="dTipoAgente" style=" display: <?= $divdisplay ?>;">
										
											<select vnulo name="_1_u_resultado_tipoagente" vnulo>
												<option value=""></option>
												<? 
													$arrProdservTipoAgente = InclusaoResultadoController::buscarConfiguracaoAgente($_1_u_resultado_idtipoteste);
													fillselect($arrProdservTipoAgente, $_1_u_resultado_tipoagente);
												?>
											</select>
										</div>
									</div>
								</div>
								<?}?>
							<div class="col-sm-12" style="display:<?= $divdisplayins ?>">
								<div class="col-sm-8"><span style="float:left;line-height: 30px;text-align: left;">Mostra Insumo:</span></div>
								<div class="col-sm-4"><input type="checkbox" id="chMostraInsumo" <?= $strmostrainsumo ?> onClick="mostraInsumo(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>

							</div>
						</div>
					</div>
					<?
						if ($moduloAmostra == 'amostracqd' or $moduloAmostra == 'amostraprod') { //Controle de qualidade
					?>
					<div style="background-color: #709ABE;padding: 4px;margin-top: 4px;">
						<div class="row">
							<div class="col-sm-12">
								<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Resultado:</span></div>
								<div class="col-sm-8"><input class="size15" name="_1_u_resultado_resultadocertanalise" type="text" class="" value="<?= $_1_u_resultado_resultadocertanalise ?>"></div>
							</div>
							<div class="col-sm-12">
								<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Conclusão:</span></div>
								<div class="col-sm-8">
									<select name="_1_u_resultado_conformidade">
										<option value=""></option>
										<? fillselect(array(
											'CONFORME' => 'Conforme', 'NAO CONFORME' => 'Não Conforme', 'NAO SE APLICA' => 'Não se Aplica'
										), $_1_u_resultado_conformidade); ?>
									</select>
								</div>
							</div>
						</div>
					</div>
					<?
						}
					?>
							</div>
						</div>
					</div>
					<?

					/*****************************************SE MODO FOR DESCRITIVO AGRUPADO [FIM]*****************************************/




					/*****************************************/
					/*SE MODO FOR DESCRITIVO INDIVIDUAL [INICIO]
					/*****************************************/
				} else if (	$_1_u_resultado_modelo == "DESCRITIVO" and $_1_u_resultado_modo == "IND") { //Se for tipo GMT
					$keyreadonly = "readonly"; //Esta variavel deixa as caixas de digitacao somente leitura. Para desabilitar, colocar valor ''
					?>

					<div align="center">

					<?
					$resultadoIndividual = InclusaoResultadoController::buscarResultadoIndividual($_1_u_resultado_idresultado);

					$x = 0;
					$i = 2;
					$tipoespecial = substr($_1_u_resultado_tipoespecial, 0, -4);
					$tab = 0;

					
					foreach ($resultadoIndividual as $key => $rowind) {
						$i = $i + 1;
						$tab = $tab + 2;

						if (($x % 7) == 0) {

					?>

					<div class="col-sm-4" style="padding: 0px;">
						<div class="interna3">
							<div class="row">
								<div class="col-sm-4 text-right">
									ID
								</div>

								<div class="col-sm-7">
									<?= (($_1_u_resultado_tipogmt == "N/A") ? "RESULTADO" : "VALOR") ?>
								</div>

							</div>
						</div>
					<?
						}
						$x = $x + 1;
					?>
					<div class="row divdescritivo">
						<div class="col-sm-12">
							<div class="interna2 ">
								<div class="col-sm-1">
									<span style="line-height: 30px;"><?= $x ?></span>
								</div>
								<div class="col-sm-4">
									<div class="interna">
										<input type="hidden" name="_<?= $i ?>_u_resultadoindividual_idresultadoindividual" value="<?= $rowind['idresultadoindividual'] ?>" size="3">
										<input tabindex="<?= $tab ?>" style="width:100% !important; font-size:11px" type="text" title="Identificação" placeholder="ID" name="_<?= $i ?>_u_resultadoindividual_identificacao" value="<?= $rowind['identificacao'] ?>" size="10">
									</div>
								</div>

								<div class="col-sm-7">
									<div class="interna">
										<input type="hidden" name="tipoespecial" value="<?= $_1_u_resultado_tipoespecial ?>" size="1">
										<input style="width: 100% !important;font-size:11px" type="text" title="tecla" id="resultado<?= $i ?>" name="_<?= $i ?>_u_resultadoindividual_resultado" value="<?= $rowind['resultado'] ?>" size="20" <?= (($_1_u_resultado_tipogmt == "N/A") ? " class='text-left' " : "vdecimal") ?>>
									</div>
								</div>

							</div>
						</div>
					</div>
					<?
							if (($x % 7) == 0) {
					?>
					</div>
					<?
						}
					}
					if (($x % 7) != 0) {
								?>
						</div>
					</div>
					<?
					}

					$stralerta = "";
					if ($_1_u_resultado_alerta == "Y") {
						$stralerta = "checked";
						$divdisplay = "block";
					} else {
						$divdisplay = "none";
					}

					$strmostrainsumo = "";
					if ($_1_u_resultado_mostraformulains == "Y") {
						$strmostrainsumo = "checked";
						$divdisplayins = "block";
					} else {
						$divdisplayins = "none";
					}
					?>
					<div class="col-sm-4" style="padding:0px;">
						<? if ($_1_u_resultado_tipogmt != "N/A") { ?>
							<div class="interna3" style="background-color:#709ABE;height: 45px;">
								<div class="row">
									<div class="col-sm-12">
										<div style="padding-left: 6px;padding-right: 6px;text-align: left;line-height: 35px;">
											GMT <span style="float:right"><?= $_1_u_resultado_gmt ?></span>
										</div>
									</div>
								</div>
							</div>
						<? } ?>
						<div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">
							<div class="row">
								<div class="col-sm-12">
									<div style="padding-left: 6px;padding-right: 6px;text-align: left;line-height: 35px;">
										Alerta <span style="float:right"><input type="checkbox" id="chAlerta" <?= $stralerta ?> onClick="verificarEIncluirServicosVinculados(<?= $_1_u_resultado_idresultado ?>);"></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					</div>
					<?
					/*****************************************SE MODO FOR DESCRITIVO INDIVIDUAL [FIM]*****************************************/





					/*****************************************/
					/*SE MODO FOR DROP AGRUPADO [INICIO]
					/*****************************************/
				} elseif ($_1_u_resultado_modelo == "DROP" and $_1_u_resultado_modo == "AGRUP") {

					?>
					<div align="center" style="padding: 0px 16px;">
						<div class="row">
							<div class="col-sm-8">
								<div style="background-color: #ccc;padding: 4px;">
									<div class="row">
										<div class="col-sm-12">
											<div class="interna">

												<div class="row">
													<div class="col-sm-2">

														Resultado:


													</div>
													<div class="col-sm-10">
														<select class="seltit" id="rotulo<?= $i ?>" title="Resultado" name="_1_u_resultado_descritivo" style="width:94% !important">
															<option value=""></option>
															<? 
															$arrDescritivo = InclusaoResultadoController::buscarCampoDescritivo($_1_u_resultado_idtipoteste);
															fillselect($arrDescritivo, $_1_u_resultado_descritivo); 															
															?>
														</select>
													</div>
												</div>
											</div>
										</div>

									</div>
								</div>
							</div>
							<?
					$stralerta = "";
					if ($_1_u_resultado_alerta == "Y") {
						$stralerta = "checked";
						$divdisplay = "block";
					} else {
						$divdisplay = "none";
					}

					$strmostrainsumo = "";
					if ($_1_u_resultado_mostraformulains == "Y") {
						$strmostrainsumo = "checked";
						$divdisplayins = "block";
					} else {
						$divdisplayins = "none";
					}
					
					?>
					<div class="col-sm-4">
						<div style="background-color: #ccc;padding: 4px;">
							<div class="row">
								<div class="col-sm-12">
									<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Qtd. Positivos:</span></div>
									<div class="col-sm-8"><input name="_1_u_resultado_positividade" id="comp_pos" type="number" size="2" value="<?= $_1_u_resultado_positividade ?>" style="float:left; width: 80px"></div>
								</div>


								<div class="col-sm-12">
									<div class="col-sm-4"><span class="col-md-12" style="float:left;line-height: 16px;text-align: left;"><?= $nomeAlerta; ?>:</span>
										<small class="col-md-12" style="font-size:8px;text-align:left;"><i>(marcado - <?= $alertaRotuloMarcado; ?>)</i></small>
										<small class="col-md-12" style="font-size:8px;text-align:left;"><i>(desmarcado - <?= $alertaRotuloDesmarcado; ?>)</i></small>
									</div>
									<div class="col-sm-8"><input type="checkbox" id="chAlerta" <?= $stralerta ?> onClick="verificarEIncluirServicosVinculados(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>

								</div>

								<div class="col-sm-12">
									<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;display: <?= $divdisplay ?>;">Tipo Alerta:</span></div>
									<div class="col-sm-8">
										<div id="dTipoAlerta" style=" display: <?= $divdisplay ?>;">

									<select name="_1_u_resultado_tipoalerta" >
										<option value=""></option>
										<?
											$arrProdservTipoAlerta = InclusaoResultadoController::buscarConfiguracaoAlerta($_1_u_resultado_idtipoteste);
											fillselect($arrProdservTipoAlerta, $_1_u_resultado_tipoalerta); 									   
										?>
									</select>
								</div>
							</div>
						</div>

						<?if($_1_u_resultado_idtipoteste == 15347){?>

							<div class="col-sm-12">
								<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;display: <?= $divdisplay ?>;">Tipo Agente:</span></div>
								<div class="col-sm-8">
									<div id="dTipoAgente" style=" display: <?= $divdisplay ?>;">
									
										<select  name="_1_u_resultado_tipoagente" vnulo>
											<option value=""></option>
											<? 
												$arrProdservTipoAgente = InclusaoResultadoController::buscarConfiguracaoAgente($_1_u_resultado_idtipoteste);
												fillselect($arrProdservTipoAgente, $_1_u_resultado_tipoagente);
											?>
										</select>
									</div>
								</div>
							</div>
							<?}?>
						<div class="col-sm-12" style="display:<?= $divdisplayins ?>">
							<div class="col-sm-8"><span style="float:left;line-height: 30px;text-align: left;">Mostra Insumo:</span></div>
							<div class="col-sm-4"><input type="checkbox" id="chMostraInsumo" <?= $strmostrainsumo ?> onClick="mostraInsumo(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>

						</div>
					</div>
						</div>
					</div>
					<?
						if ($moduloAmostra == 'amostracqd' or $moduloAmostra == 'amostraprod') { //Controle de qualidade
					?>
						<div style="background-color: #709ABE;padding: 4px;margin-top: 4px;">
							<div class="row">
								<div class="col-sm-12">
									<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Resultado:</span></div>
									<div class="col-sm-8"><input class="size15" name="_1_u_resultado_resultadocertanalise" type="text" class="" value="<?= $_1_u_resultado_resultadocertanalise ?>"></div>
								</div>
								<div class="col-sm-12">
									<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Conclusão:</span></div>
									<div class="col-sm-8">
										<select name="_1_u_resultado_conformidade">
											<option value=""></option>
											<? fillselect(array(
												'CONFORME' => 'Conforme', 'NAO CONFORME' => 'Não Conforme', 'NAO SE APLICA' => 'Não se Aplica'
											), $_1_u_resultado_conformidade); ?>
										</select>
									</div>
								</div>
							</div>
						</div>
					<? } ?>
							</div>
						</div>
					<?
					/******************************************SE MODO FOR DROP AGRUPADO [FIM]*****************************************/




					/*****************************************/
					/*SE MODO FOR DROP INDIVIDUAL [INICIO]
					/*****************************************/
				} elseif ($_1_u_resultado_modelo == "DROP" and $_1_u_resultado_modo == "IND") { //Se for tipo GMT
					$keyreadonly = "readonly"; //Esta variavel deixa as caixas de digitacao somente leitura. Para desabilitar, colocar valor ''
					?>
						<br>
						<div align="center">
					<?

						$resultadoIndividual = InclusaoResultadoController::buscarResultadoIndividual($_1_u_resultado_idresultado);

						$x = 0;
						$i = 2;
						$tipoespecial = substr($_1_u_resultado_tipoespecial, 0, -4);
						$tab = 0;


						foreach ($resultadoIndividual as $key => $rowind) {

							$i = $i + 1;
							$tab = $tab + 2;

							if (($x % 7) == 0) { ?>
								<div class="col-sm-4">
									<div class="interna3">
										<div class="row">
											<div class="col-sm-4 text-right">
												ID
											</div>

											<div class="col-sm-7">
												<?= (($_1_u_resultado_tipogmt == "N/A") ? "RESULTADO" : "VALOR") ?>
											</div>

										</div>
									</div>
							<?
								}
								$x = $x + 1;
							?>

							<div class="row divdescritivo">
								<div class="col-sm-12">
									<div class="interna2 ">
										<div class="col-sm-1">
											<span style="line-height: 30px;"><?= $x ?></span>
										</div>
										<div class="col-sm-4">
											<div class="interna">
												<input type="hidden" name="_<?= $i ?>_u_resultadoindividual_idresultadoindividual" value="<?= $rowind['idresultadoindividual'] ?>" size="3">
												<input tabindex="<?= $tab ?>" style="width:100% !important; font-size:11px" type="text" title="Identificação" placeholder="ID" name="_<?= $i ?>_u_resultadoindividual_identificacao" value="<?= $rowind['identificacao'] ?>" size="10">
											</div>
										</div>

										<div class="col-sm-7">
											<div class="interna">
												<select class="seltit" id="rotulo<?= $i ?>" title="Resultado" name="_<?= $i ?>_u_resultadoindividual_resultado" style="width:100% !important">
													<option value=""></option>
													<? fillselect(
														"SELECT valor AS num, valor FROM prodservtipoopcao  where idprodserv = '" . $_1_u_resultado_idtipoteste . "' order by valor*1",
														$rowind['resultado']
													); ?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>

							<?if (($x % 7) == 0) {?>
								</div>
							<?
								}
						}

						if (($x % 7) != 0) { ?>
							</div>
							</div>
						<?}

					$stralerta = "";
					if ($_1_u_resultado_alerta == "Y") {
						$stralerta = "checked";
						$divdisplay = "block";
					} else {
						$divdisplay = "none";
					}

					$strmostrainsumo = "";
					if ($_1_u_resultado_mostraformulains == "Y") {
						$strmostrainsumo = "checked";
						$divdisplayins = "block";
					} else {
						$divdisplayins = "none";
					}
					?>


					<div class="col-sm-4">
						<? if ($_1_u_resultado_tipogmt != "N/A") { ?>
							<div class="interna3" style="background-color:#709ABE;height: 45px;">
								<div class="row">
									<div class="col-sm-12">
										<div style="padding-left: 6px;padding-right: 6px; text-align: left">
											GMT <span style="float:right"><?= $_1_u_resultado_gmt ?></span>
										</div>
									</div>
								</div>
							</div>
						<? } ?>
						<div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">
							<div class="row">
								<div class="col-sm-12">
									<div style="padding-left: 6px;padding-right: 6px;line-height: 30px; text-align: left">
										Alerta <span style="float:right"><input type="checkbox" id="chAlerta" <?= $stralerta ?> onClick="verificarEIncluirServicosVinculados(<?= $_1_u_resultado_idresultado ?>);"></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					</div>

					<?

					/*****************************************SE MODO FOR DROP INDIVIDUAL [FIM]*****************************************/





					/*****************************************/
					/*SE MODO FOR UPLOAD [INICIO]
					/*****************************************/
				} elseif ($_1_u_resultado_modelo == "UPLOAD") {

					$resultadosElisaArquivoUpload = InclusaoResultadoController::buscarResultadosDeArquivoUploadEliza($_1_u_resultado_idresultado);
					$iresult = count($resultadosElisaArquivoUpload);

					if ($iresult > 0) {?>

						<div class="row" style="background: #bbb;margin: 0;">
							<div class="col-xs-1 text-right">
								&nbsp;
							</div>
							<div class="col-xs-2 text-right">
								Wells
							</div>
							<div class="col-xs-1 text-right">
								O.D.
							</div>
							<div class="col-xs-2 text-right">
								I.E.
							</div>
							<div class="col-xs-1 text-right">
								S/P
							</div>
							<div class="col-xs-1 text-right">
								S/N
							</div>
							<div class="col-xs-1 text-right">
								Titer
							</div>
							<div class="col-xs-1 text-right">
								Group
							</div>
							<div class="col-xs-2 text-center">
								Result
							</div>
						</div>
						<div class="striped">

						
							<? foreach ($resultadosElisaArquivoUpload as $key => $row) {?>
								<div class="row" style="margin: 0;">
									<div class="col-xs-1 text-right">
										<?= $row['nome'] ?>
									</div>
									<div class="col-xs-2 text-right">
										<?= $row['well'] ?>
									</div>
									<div class="col-xs-1 text-right">
										<?= $row['OD'] ?>
									</div>
									<div class="col-xs-2 text-right">
										<?= $row['IE'] ?>
									</div>
									<div class="col-xs-1 text-right">
										<?= $row['SP'] ?>
									</div>
									<div class="col-xs-1 text-right">
										<?= $row['SN'] ?>
									</div>
									<div class="col-xs-1 text-right">
										<?= $row['titer'] ?>
									</div>
									<div class="col-xs-1 text-right">
										<?= $row['grupo'] ?>
									</div>
									<div class="col-xs-2 text-center">
										<?= $row['result'] ?>
									</div>
								</div>
							<? } ?>
						</div>

						<br>
						<?
					}


					$nomeArquivoElisaUpload = InclusaoResultadoController::buscarNomeArquivoElisaUpload($_1_u_resultado_idresultado);

					?>
					<div class="row" style="margin: 0;display: flex;align-items: center;">
						<div class="col-xs-12 col-sm-6 col-md-6" style="margin: 0;">
							<div class="row" style="margin: 0;">
								<div class="col-md-4">Selecione o kit:</div>
								<div class="col-md-8"><select id="tipokit" name="tipokit" onchange="settipokit(this,<?= $_1_u_resultado_idresultado ?>)">
										<? fillselect("select 'IDEXX-LAUDO','IDEXX-LAUDO' union select 'IDEXX','IDEXX' union select 'AFFINITECK','AFFINITECK' union select 'BIOCHEK','BIOCHEK'", $_1_u_resultado_tipokit); ?> </select>
								</div>
							</div>
							<div class="row" style="margin: 0;display: flex;align-items: center;">
								<div class="col-md-4">Nome Arquivo Padrão:</div>
								<div class="col-md-8">
									<span id="nome_arquivo_txt">
										<label class="alert-warning"><a class="copy" onclick="copyToClipboard(this)"><?= $nomeArquivoElisaUpload['nomearquivortf'] ?>.txt <i class="fa fa-clipboard"></i></a></label> (AFFINITECK / BIOCHEK)
									</span>
									<span id="nome_arquivo_rtf">
										<label class="alert-warning"><a class="copy" onclick="copyToClipboard(this)"><?= $nomeArquivoElisaUpload['nomearquivortf'] ?>.RTF <i class="fa fa-clipboard"></i></a></label> (IDEXX / IDEXX-LAUDO)
									</span>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-sm-6 col-md-6">
							<div class="cbupload" id="resultadoelisa" title="Clique ou arraste o arquivo Elisa para cá." style="width:100%;height:100%;">
								<i class="fa fa-cloud-upload fonte18"></i>
							</div>
						</div>
					</div>
					<?
				} else if($_1_u_resultado_modelo == "DINAMICOREFERENCIA") {
					function verificarRequisitosTeste($referencias) {
						global $dadosAmostra;
						$dadosReferencia = [];

						foreach($referencias as $referencia) {
							// Verificando sexo
							if($referencia->sexo != 'Não informado' && $referencia->sexo != $dadosAmostra['sexo']) continue;

							// Verificando idade
							if(!validarIntervalo(
								$dadosAmostra['idade'], 
								$dadosAmostra['tipoidade'], 
								$referencia->idade->min, 
								$referencia->idade->unidadeMin, 
								$referencia->idade->max, 
								$referencia->idade->unidadeMax)
							) continue;

							// Verificando especie
							if($referencia->especie != $dadosAmostra['idespeciefinalidade']) continue;

							array_push($dadosReferencia, $referencia);
						}

						return $dadosReferencia;
					}

					// Função para converter as unidades para dias
					function converterParaDias($valor, $unidade) {
						switch($unidade) {
							case 'Ano(s)':
								return $valor * 365;
							case 'Mês(es)':
								return $valor * 30;
							case 'Semana(s)':
								return $valor * 7;
							case 'Dias(s)':
								return $valor;
							default:
								return null;
						}
					}

					// Função para verificar se o valor está no intervalo
					function validarIntervalo($valor, $unidade, $idadeMin, $unidadeIdadeMin, $idadeMax, $unidadeIdadeMax) {
						$valorEmDias = converterParaDias($valor, $unidade);
						$valorMinDias = converterParaDias($idadeMin, $unidadeIdadeMin);
						$valorMaxDias = converterParaDias($idadeMax, $unidadeIdadeMax);

						return $valorEmDias >= $valorMinDias && $valorEmDias <= $valorMaxDias;
					}

					$jsonconfig = [];

					if (!empty($_1_u_resultado_idtipoteste)) {

						$jsonresultado = json_decode($_1_u_resultado_jsonresultado);
						$jsonconfig = InclusaoResultadoController::buscarJsonConfigServico($_1_u_resultado_idtipoteste);

						if (empty($_1_u_resultado_jsonconfig) or (count($jsonresultado->INDIVIDUAL) == 0)) {
							$_1_u_resultado_jsonconfig = $jsonconfig;
						}

						$jsonconfig = json_decode($_1_u_resultado_jsonconfig);
					}

					/**
					 * Montar toda a string HTML dentro do laço e dar um echo único ao final é geralmente mais 
					 * eficiente em PHP. Isso ocorre porque concatenar strings dentro do laço e fazer um único 
					 * echo reduz a quantidade de operações de I/O (entrada/saída) que o PHP precisa realizar.
					 */
					$resultadoHTML = "<input type='hidden' id='jsonresultado' name='_1_u_resultado_jsonresultado' value='$_1_u_resultado_jsonresultado'>
										<div id='grupo-exames' class='d-flex mx-auto col-xs-12 col-md-8 flex-wrap float-none'>";

					// Grupo de exames
					foreach($jsonconfig as $indiceGrupo => $grupoConfig) {
						$resultadoHTML .= '<div class="w-100 grupo-exames-item">';
						$resultadoHTML .= "<div class='row d-flex align-items-center'>
												<h4 class='font-bold text-uppercase mb-3 col-xs-6'>
													{$grupoConfig->nome}
												</h4>
												<h4 class='font-bold text-uppercase mb-3 col-xs-6 text-right'>Resultados</h4>
											</div>";

						foreach($grupoConfig->testes as $indiceTeste => $teste) {
							$referenciasValidas = verificarRequisitosTeste($teste->referencias);
							if($referenciasValidas) {
								if(!$jsonresultado || ($jsonresultado && !$jsonresultado->grupo)) {
									$jsonresultado = new stdClass();
									$testesStd = new stdClass();
									$testesStd->testes = [];

									$testeStd = new stdClass();

									$testeStd->resultadoum = '';
									$testeStd->resultadodois = '';

									$jsonresultado->grupo = [
										$indiceGrupo => $testesStd
									];

									$jsonresultado->grupo[$indiceGrupo]->testes[$indiceTeste] = $testeStd;
								}

								foreach($referenciasValidas as $indiceReferencia => $referencia) {
									// Verficar se a amostra em questão se encaixa para o teste
									$resultadoHTML .= "<div class='row d-flex align-items-center teste-item'>
															<div class='col-xs-6'>
																<h4 class='m-0'>{$teste->nome}</h4>
															</div>
															<br />
															<div class='col-xs-6 text-right d-flex flex-wrap'>
																<div class='col-xs-6 form-group'>
																	<div 
																		data-campo='resultadoum' 
																		data-indicegrupo='{$indiceGrupo}' 
																		data-indiceteste='{$indiceTeste}' 
																		type='text' 
																		class='form-control campo-resultado resultado-um'
																	>
																		{$jsonresultado->grupo[$indiceGrupo]->testes[$indiceTeste]->resultadoum}
																	</div>
																</div>
																<div class='col-xs-6 form-group'>
																	<div
																		data-campo='resultadodois' 
																		data-indicegrupo='{$indiceGrupo}' 
																		data-indiceteste='{$indiceTeste}' 
																		type='text' 
																		class='form-control campo-resultado resultado-dois'
																	>
																		{$jsonresultado->grupo[$indiceGrupo]->testes[$indiceTeste]->resultadodois}
																	</div>
																</div>
															</div>
														</div>";
								}
									
							}
						}

						$labelObs = $grupoConfig->obslabel ?? 'Observação';

						$campoDescricao = "<div class='row d-flex align-items-center'>
												<div class='col-xs-12 form-group'>
													<label>$labelObs</label>
													<div data-indicegrupo='$indiceGrupo' class='resultado-obs form-control' contenteditable='true'>
														{$jsonresultado->grupo[$indiceGrupo]->resultadoobs}
													</div>
												</div>
											</div>";

						$resultadoHTML .= "$campoDescricao
									</div>";
						$resultadoHTML .= '<hr class="w-100" />';
				 	}

					$resultadoHTML .= "</div>";

					echo $resultadoHTML;
				}
				
				/******************************************SE MODO FOR UPLOAD [FIM]*****************************************/


				

				if (($moduloAmostra == 'amostratra' or $moduloAmostra == 'amostraautogenas') and !empty($_1_u_resultado_idresultado)) { //diag autogenas
				?>

					<div class="col-sm-4">
						<div class="interna3" style="background-color:#ccc;height: 45px;">
							<div class="row">
								<div class="col-sm-12">
									<div style="padding-left: 6px;padding-right: 6px;">
										<?
										if ($_1_u_resultado_geraagente == "Y") {
										?>
											Agente:<i class="fa fa-plus-circle fa-1x verde btn-lg pointer" title="Criar um agente" onclick="inovolote(<?= $_1_u_resultado_idresultado ?>)"></i>
										<?
										} else {
										?>
											Agente:<i class="fa fa-exclamation-triangle vermelho fa-1x btn-lg pointer" title="Este teste não esta configurado para gerar sementes. Configurar a opção no cadastro de produtos e serviços."></i>
										<?


										}
										//buscar agentes gerados

										$dadosAgentesGerados = InclusaoResultadoController::buscarSementesGeradasResultado($_1_u_resultado_idresultado);
										$arrSementesGeradas = $dadosAgentesGerados->data;
										$numSementesGeradas = $dadosAgentesGerados->numRows();

										if ($numSementesGeradas > 0) {
										?>
											<div id="resultadoagente<?= $_1_u_resultado_idresultado ?>" style="display: none">
												<div id="cbModuloResultados" class="col-md-12 zeroauto panel panel-default">
													<table class="table table-hover table-striped table-condensed">
														<tr>
															<td>Lote</td>
															<td>Produto</td>
															<td>Criado por</td>
															<td>Criado em</td>
														</tr>
														<?
														$k = 0;
														$arrAgentes = array();
														foreach ($arrSementesGeradas as $key => $rl) {

															$arrAgentes[$k]["agente"] = $rl['partida'];
															$arrAgentes[$k]["idlote"] = $rl['idlote'];
															$arrAgentes[$k]["tipificacao"] = $rl['tipificacao'];
															$arrAgentes[$k]["exercicio"] = $rl['exercicio'];
															$k++;
															?>
															<tr onclick="janelamodal('?_modulo=semente&_acao=u&idlote=<?= $rl['idlote'] ?>');">
																<td><?= $rl['partida'] ?></td>
																<td><?= $rl['descr'] ?></td>
																<td><?= $rl['criadopor'] ?></td>
																<td><?= dmahms($rl['criadoem']) ?></td>
															</tr>
														<? } ?>
													</table>
												</div>
											</div>
											<i class="fa fa-cubes fa-1x azul btn-lg pointer" title="Agente(s) isolados" onclick="listalote(<?= $_1_u_resultado_idresultado ?>)"></i>
										<? } else {
											$arrAgentes = 0;
										}

										if (is_array($arrAgentes)) {
											$arrAgentes = (count($arrAgentes) == 0) ? json_encode([]) : json_encode($arrAgentes);
										} else {
											$arrAgentes = 0;
										}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>

				<?}?>


				<div class="panel-body">
					<br>
					<?


					$arrFormulas = InclusaoResultadoController::buscarInsumosFormulaResultado($_1_u_resultado_idtipoteste);

					// campo de Informações Adicionais.
					if (in_array($_1_u_resultado_modelo, ['DROP', 'UPLOAD', 'SELETIVO'])) { ?>

						<div class="row" style="margin: 0;">
							<div class="col-sm-12">
								<b>Informações Adicionais:</b>
								<textarea style="min-height: 150px;" name="_1_u_resultado_observacao"><?= $_1_u_resultado_observacao ?></textarea>
							</div>
						</div>

					<? }


					$l = $_1_u_resultado_idresultado;

					foreach ($arrFormulas as $key => $rowf1) {

						if ($_1_u_resultado_status == 'ASSINADO' or $_1_u_resultado_status == 'FECHADO' or $_1_u_resultado_status == 'CONFERIDO') {
							// if($key == 0){
								$insumosServico = InclusaoResultadoController::buscarInsumosServicoConcluido($_1_u_resultado_idresultado, $rowf1["idprodservformula"]);
							// }
						} else {
							$insumosServico = InclusaoResultadoController::buscarInsumosServicoEmAndamento($_1_u_resultado_idtipoteste, $rowf1["idprodservformula"], 'ATIVO', 'ATIVO');						
						}

						$nRowInsumoServio = count($insumosServico);

						if ($nRowInsumoServio > 0) {

							$nRegistrosResultadoProdservFormula = InclusaoResultadoController::verificarSeExisteRegistroNaTableaResultadoProdserFormula($_1_u_resultado_idresultado);

							if ($nRegistrosResultadoProdservFormula < 1 and ($_1_u_resultado_status != 'ASSINADO' or $_1_u_resultado_status != 'FECHADO' or $_1_u_resultado_status != 'CONFERIDO')) {

								$statusInsertProdservFormula = InclusaoResultadoController::insertResultadoProdservFormula(cb::idempresa(), $_1_u_resultado_idresultado, $rowf1["idprodservformula"], $_SESSION["SESSAO"]["USUARIO"]);

							}
							?>

							<div class="row">
								<div class="col-md-12">
									<div class="panel panel-default">
										<div class="panel-heading">Insumos do teste Fase: <?= $rowf1["ordem"] ?>
											<?

											$dadosProdservFormula = InclusaoResultadoController::buscarRegistroProdservFormulaServico($_1_u_resultado_idresultado, $rowf1["idprodservformula"]);
											
											if (count($dadosProdservFormula) > 0) {

											?>
												<input title="Retirar fase" checked="checked" type="checkbox" name="retirarfase" onclick="dfase(<?= $dadosProdservFormula['idresultadoprodservformula'] ?>);">
											<?
											} else {
											?>
												<input title="Inserir fase" type="checkbox" name="inserirfase" onclick="ifase(<?= $_1_u_resultado_idresultado ?>,<?= $rowf1['idprodservformula'] ?>);">
											<?
											}
											?>

										</div>

										<div class="panel-body">
											<?
											if (count($dadosProdservFormula) > 0) {
											?>
												<table class="table table-striped planilha">
													<tr>
														<? if ($_1_u_resultado_status != 'ASSINADO' and $_1_u_resultado_status != 'FECHADO' and $_1_u_resultado_status != 'CONFERIDO') { ?>
															<th>Utilizar</th>
														<? } ?>
														<th>Produto</th>

														<th>Lotes</th>
														<th>Utilizando</th>
														<? if ($_1_u_resultado_status != 'ASSINADO') { ?>
															<th>Restante</th>
															<th></th>
														<? } ?>
													</tr>
													<?

													foreach ($insumosServico as $key => $rowf) {

														if ($_1_u_resultado_status == 'ASSINADO' or $_1_u_resultado_status == 'FECHADO' or $_1_u_resultado_status == 'CANCELADO' or $_1_u_resultado_status == 'CONFERIDO') {

															$infoInsumoConsumido = InclusaoResultadoController::buscarLotesConsumoInsumoResultado($_1_u_resultado_idresultado, $unidadepadrao, $rowf["idprodserv"], $rowf1["idprodservformula"]);
															$nRowinsumoConsumido =count($infoInsumoConsumido);
															$qtdimput = $rowf['qtdi'] * $_1_u_resultado_quantidade;
															?>
															<tr>
																<td class='nowrap'><?= $rowf['descr'] ?> <a title="Abrir cadastro produto" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=prodserv&_acao=u&idprodserv=<?= $rowf['idprodserv'] ?>" target="_blank"></a></td>
																
																<? if ($nRowinsumoConsumido < 1) { ?>
																	<td>Não foi encontrado lote disponivel!!!</td>
																<? } else { ?>
																	
																	<td>
																		<?
																		$utilizando = 0;
																		foreach ($infoInsumoConsumido as $key => $rowca) {

																			$utilizando = $rowca['qtdd'] + $utilizando;
																			?>

																			<span class="label label-primary fonte10 itemestoque" qtddisp="<?= tratanumero($rowca['qtddisp']) ?>" qtddispexp="" idlote="<?= $rowca['idlote'] ?>" data-toggle="tooltip" title="" data-original-title="<?= $rowca['partida'] ?>">
																				<a class="branco hoverbranco" href="?_modulo=<?= $rowca['idobjeto'] ?>&_acao=u&idlote=<?= $rowca['idlote'] ?>" target="_blank"><?= $rowca['partida'] ?>/<?= $rowca['exercicio'] ?></a>
																				<span class="badge pointer screen" idlote="<?= $rowca['idlote'] ?>" onclick="janelamodal('?_modulo=<?= $rowca['idobjeto'] ?>&_acao=u&idlote=<?= $rowca['idlote'] ?>')"><?= tratanumero($rowca['qtddisp']) ?></span>
																				<? if ($rowca['status'] != 'ESGOTADO') { ?>
																					<a title="Esgotar Lote." class="fa fa-minus-circle pointer branco hoververmelho fa-1x" onclick="esgotarlote(<?= $rowca['idlotefracao'] ?>)"></a>
																				<? } ?>
																				<input type="text" name="<?= $act ?>qtdd" value="<?= $rowca['qtdd'] ?>" class="reset screen" cbqtddispexp="" style="width: 80px !important; background-color: white;" onkeyup="mostraConsumo(this)" readonly="readonly">
																			</span>
																		<? } ?>

																	</td>
																	<td align="right"><span class="badge"> <?= tratanumero($utilizando) ?></span></td>

																<? }       

														} else {

															$lotesDisponiveis = InclusaoResultadoController::buscarAtribuicoesDeLotesResultado($_1_u_resultado_idresultado, $unidadepadrao, $rowf["idprodserv"], $dadosProdservFormula['idprodservformula']);
															$nlotesDisponiveis = count($lotesDisponiveis);
															$qtdutilizar = $rowf['qtdi'] * $_1_u_resultado_quantidade;
															$qtdimput = $rowf['qtdi'] * $_1_u_resultado_quantidade;
															?>

															<tr class="trInsumo">
																<td align="right"><span class="badge sQtdpadrao"><?= tratanumero($qtdutilizar) ?></span></td>
																<td class='nowrap'><?= $rowf['descr'] ?> <a title="Abrir cadastro produto" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=prodserv&_acao=u&idprodserv=<?= $rowf['idprodserv'] ?>" target="_blank"></a> </td>
																<? if ($nlotesDisponiveis< 1) { ?>

																	<td>Não foi encontrado lote disponivel!!!</td>

																<? } else { ?>
																	<td id="insumos<?= $rowf["idprodserv"] ?>">
																		<?
																		$qtdusando = 0;
																		foreach ($lotesDisponiveis as $key => $rowc) {

																			$l = $l + 1;
																			$act = $l;
																			$novo = 'Y';
																			if (!empty($rowc['idlotecons']) && $rowc['idobjetoconsumoespec'] == $rowf1["idprodservformula"]) {
																				$act = '_cons' . $l . '_u_lotecons_';
																				$qtdimput = $qtdimput - $rowc['qtddisp'];
																				$novo = 'N';
																			} elseif (($rowc['qtddisp'] > 0 and $qtdimput > 0 and empty($rowc['qtdd']) || ($rowc['idobjetoconsumoespec'] != $rowf1["idprodservformula"])) && $qtdimput > $qtdusando) {
																				if ($rowc['qtddisp'] < $qtdimput) {
																					$rowc['qtdd'] = $rowc['qtddisp'];
																					$qtdimput = $qtdimput - $rowc['qtddisp'];
																					$act = '_cons' . $l . '_i_lotecons_';
																				} else {
																					$rowc['qtdd'] = $qtdimput;
																					$qtdimput = 0;
																					$act = '_cons' . $l . '_i_lotecons_';
																				}
																				$novo = 'N';
																			}
																			if ($rowc['qtddisp'] <= 0 and $rowc['qtdd'] <= 0) {
																				$readonlyzest = "readonly='readonly'";
																			} else {
																				$readonlyzest = "";
																			}
																			$qtdusando = $rowc['qtdd'] + $qtdusando;
																			?>
																			<span class="label label-primary fonte10 itemestoque" qtddisp="<?= tratanumero($rowc['qtddisp']) ?>" qtddispexp="" idlote="<?= $rowc['idlote'] ?>" data-toggle="tooltip" title="" data-original-title="<?= $rowc['partida'] ?>">
																				<a class="branco hoverbranco" href="?_modulo=<?= $rowc["idobjeto"] ?>&_acao=u&idlote=<?= $rowc['idlote'] ?>" target="_blank"><?= $rowc['partida'] ?>/<?= $rowc['exercicio'] ?></a>
																				<span class="badge pointer screen" idlote="<?= $rowc['idlote'] ?>" onclick="janelamodal('?_modulo=<?= $rowc['idobjeto'] ?>&_acao=u&idlote=<?= $rowc['idlote'] ?>')"><?= tratanumero($rowc['qtddisp']) ?></span>
																				<? if ($rowca['status'] != 'ESGOTADO') { ?>
																					<a title="Esgotar Lote." class="fa fa-minus-circle pointer branco hoververmelho fa-1x" onclick="esgotarlote(<?= $rowc['idlotefracao'] ?>)"></a>
																				<? } ?>
																				<? if($rowc['idunidade'] != 10 || ($rowc['idunidade'] == 10 && !in_array($_1_u_resultado_status, ['ABERTO', 'RECEBIDO']) && $_1_u_resultado_idempresa == 2)) { ?>
																					<input <?= $readonlyzest ?> type="text" name="<?= $act ?>qtdd" value="<?= $rowc['qtdd'] ?>" class="reset screen" cbqtddispexp="" style="width: 80px !important; background-color: white;" onkeyup="mostraConsumo(this)" <? if ($novo == 'Y') { ?> onchange="atualizainput(<?= $l ?>)" <? } ?>>
																				<?} else {?>
																					<label for="" class="text-white"><?= $rowc['qtdd'] ?></label>
																				<?}?>
																				<input type="hidden" <?= $rowc['idunidade'] != 10 || ($rowc['idunidade'] == 10 && !in_array($_1_u_resultado_status, ['ABERTO', 'RECEBIDO']) && $_1_u_resultado_idempresa == 2 ) ? "name='{$act}idlotecons'" : '' ?> value="<?=$rowc['idlotecons'] ?>">
																				<input type="hidden" <?= $rowc['idunidade'] != 10 || ($rowc['idunidade'] == 10 && !in_array($_1_u_resultado_status, ['ABERTO', 'RECEBIDO']) && $_1_u_resultado_idempresa == 2 ) ? "name='{$act}tipoobjeto'" : '' ?> value="resultado">
																				<input type="hidden" <?= $rowc['idunidade'] != 10 || ($rowc['idunidade'] == 10 && !in_array($_1_u_resultado_status, ['ABERTO', 'RECEBIDO']) && $_1_u_resultado_idempresa == 2 ) ? "name='{$act}idobjeto'" : '' ?> value="<?=$_1_u_resultado_idresultado ?>">
																				<input type="hidden" <?= $rowc['idunidade'] != 10 || ($rowc['idunidade'] == 10 && !in_array($_1_u_resultado_status, ['ABERTO', 'RECEBIDO']) && $_1_u_resultado_idempresa == 2 ) ? "name='{$act}idlote'" : '' ?> value="<?=$rowc['idlote'] ?>">
																				<input type="hidden" <?= $rowc['idunidade'] != 10 || ($rowc['idunidade'] == 10 && !in_array($_1_u_resultado_status, ['ABERTO', 'RECEBIDO']) && $_1_u_resultado_idempresa == 2 ) ? "name='{$act}idlotefracao'" : '' ?> value="<?=$rowc['idlotefracao'] ?>">
																				<input type="hidden" <?= $rowc['idunidade'] != 10 || ($rowc['idunidade'] == 10 && !in_array($_1_u_resultado_status, ['ABERTO', 'RECEBIDO']) && $_1_u_resultado_idempresa == 2 ) ? "name='{$act}tipoobjetoconsumoespec'" : '' ?> value="prodservformula">
																				<input type="hidden" <?= $rowc['idunidade'] != 10 || ($rowc['idunidade'] == 10 && !in_array($_1_u_resultado_status, ['ABERTO', 'RECEBIDO']) && $_1_u_resultado_idempresa == 2 ) ? "name='{$act}idobjetoconsumoespec'" : '' ?> value="<?=$rowf1["idprodservformula"];?>">
																			</span>
																			<?
																		} 
																		$restante = $qtdutilizar - $qtdusando;
																		if ($restante > 0) {
																			$fundo = "fundolaranja";
																		} else {
																			$fundo = "fundoverde";
																		}
																		?>
																	</td>
																	<td>
																		<span class="badge  sUtilizando <?= $fundo ?>"><?= $qtdusando ?></span>
																	</td>
																	<td>
																		<span class="badge sRestante <?= $fundo ?>"><?= $restante ?></span>
																	</td>
															<?  } 
															}  
															?>

															</tr>
														<?
													} 
													?>
												</table>
												<?
											} 
											?>
										</div>
									</div>
								</div>
							</div>
							<?
						} 
					} 
					?>
					<br>
					<div class="row">
						<div class="col-md-12">
							<div class="panel panel-default">
								<div class="panel-heading">Tags vinculadas</div>
								<div class="panel-body" style="padding-top: 10px !important;">
									<?
									$tagsVinculadas = InclusaoResultadoController::buscarTagsVinculadasAoTesteAgrupado($_1_u_resultado_idtipoteste,$_1_u_resultado_idresultado);
									foreach($tagsVinculadas as $k => $tag){?>
										<div class="panel panel-default">
											<div class="panel-heading">
												<?= $k ?>
											</div>
											<div class="panel-body" style="padding-top: 10px !important;">
												<table class="table table-striped planilha">
													<tr>
														<th style="width: 80%;">Tag</th>
														<th style="text-align: center;width: 20%;">
															<button class="btn btn-success btn-xs" onclick="vincularTagAoTeste(<?= $_1_u_resultado_idresultado ?>,this)">Vincular</button>
														</th>
													</tr>
													<?
													foreach($tag['tags'] as $t => $tagVinculada){
														?>
														<tr>
															<td style="width: 80%;">
																<?= $tagVinculada['descr'] ?>
															</td>
															<td align="center" style="width: 20%;">
																<?
																$checked = '';
																if(!empty($tagVinculada['idobjetovinculo']) || (empty($tagVinculada['idobjetovinculo']) && in_array($tagVinculada['idtag'], array_map(function($item) {return $item['idTag'];},$tag['tagsVinculadas'])))){
																	$checked = 'checked';
																}
																?>
																<input type="checkbox" idobjetovinculo="<?=$tagVinculada['idobjetovinculo']?>" idtag="<?=$tagVinculada['idtag']?>" <?= $checked ?> >
															</td>
														</tr>
														<?}?>
												</table>
											</div>
										</div>
									<?}?>
								</div>
							</div>
						</div>
					</div>
					<br>
					<input name="_1_u_resultado_status" type="hidden" style="width: 10px;" value="<?= $_1_u_resultado_status ?>">
					<input name="_statusant_" type="hidden" style="width: 10px;" value="<?= $_1_u_resultado_status ?>">

			<? } ?>
			
			<div id="mdvoltarversao" style="display: none;">
				<div class="row">
					<div class="col-md-3"></div>
					<div class="col-md-6" nowrap="">
						<label>Status:</label>
						<table style="width:100%;" id="fluxo">
							<tbody>                                                        
								<tr>
									<td>
										<input type="radio" status="ABERTO" idfluxostatus="871" name="idfluxostatushist">
									</td>
									<td>
										<label>Aberto </label>
									</td>
								</tr>
								<tr>
									<td>
										<input type="radio" status="PROCESSANDO" idfluxostatus="872" name="idfluxostatushist">
									</td>
									<td>
										<label>Processando</label>
									</td>
								</tr>
								<tr>
									<td>
										<input type="radio" status="AGUARDANDO" idfluxostatus="873" name="idfluxostatushist">
									</td>
									<td>
										<label>Aguardando</label>
									</td>
								</tr>
								<tr>
									<td>
										<input type="radio" status="FECHADO" idfluxostatus="874" name="idfluxostatushist">
									</td>
									<td>
										<label>Fechado</label>
									</td>
								</tr>
								<tr>
									<td>
										<input type="radio" status="CONFERIDO" idfluxostatus="875" name="idfluxostatushist">
									</td>
									<td>
										<label>Conferido</label>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="col-md-3"></div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<label>Motivo:</label>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<select id="_edicaomotivores_" title="" tabindex="-99">
							<option value="Correção">Correção</option>
						</select>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<label>Obs.:</label>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<input type="text" id="_edicaoobsres_" value="">
					</div>
				</div>
			<div class="pull-right" style="margin-top:15px; margin-bottom: 15px;">
				<button id="cbSalvar" type="button" class="btn btn-success btn-xs" onclick="voltaversaosave(<?= $_GET['idresultado'] ?>, <?= $_1_u_resultado_versao ?>)" title="Salvar">
					<i class="fa fa-circle"></i>Salvar
				</button>
			</div>
			
		</div>
</tr>
</table>
</div>
</div>
</div>

	<?
	if (!empty($_1_u_resultado_idresultado)) { // trocar p/ cada tela a tabela e o id da tabela
		$_idModuloParaAssinatura = $_1_u_resultado_idresultado; // trocar p/ cada tela o id da tabela
		require 'viewAssinaturas.php';
	}

	$tabaud = "resultado"; //pegar a tabela do criado/alterado em antigo
	$idRefDefaultDropzone = "arquivoresultado";
	require 'viewCriadoAlterado.php';
	?>
	

<div id="novolote" style="display: none">
	<div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<table>
							<tr>
								<td align="right"><strong>Agente:</strong></td>
								<td>
									<?
										$arrObjetoVinculo = InclusaoResultadoController::buscarObjetoVinculoProdservServico($_1_u_resultado_idtipoteste);
									?>
									<select class="size30" id="idprodservlote" name="">
										<option></option>
										<? fillselect($arrObjetoVinculo); ?>
									</select>
									<input id="idlotelote" name="" type="hidden" value="">
									<input id="statuslote" name="" type="hidden" value="ABERTO">
									<input id="idunidadegplote" name="" type="hidden" value="<?= empty(getUnidadePadraoModulo($_GET['modulo'],cb::idempresa()))? 2 :getUnidadePadraoModulo($_GET['modulo'],cb::idempresa()) ?>">
									<input id="exerciciolote" name="" type="hidden" value="<?= date("Y") ?>">
									<input id="tipoobjetolote" name="" type="hidden" value="resultado">
									<input id="idobjetolote" name="" type="hidden" value="">
								</td>
								<td>Qtd:</td>
								<td>
									<input class="size5" id="qtdprod" name="" type="number" value="5" vnulo>
								</td>
							</tr>
							<tr>
								<td align="right"><strong>Orgão:</strong></td>
								<td>
									<input id="orgao" name="" type="text" value="">
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
//FUNÇÕES

function jsonServicos()
{
	global $unidadepadrao;

	$servicosDaUnidade = InclusaoResultadoController::buscarServicosDaUnidade($unidadepadrao);

	$arrtmp = array();
	$i = 0;

	foreach ($servicosDaUnidade as $key => $r) {
		$arrtmp[$i]["value"] = $r["idprodserv"];
		$arrtmp[$i]["label"] = $r["descr"];
		$i++;
	}

	 return json_encode($arrtmp);
}


function criarCamposJsonConfigAntigo($campo)
{
	$fields = "";

	switch ($campo->tipo) {
		case 'numerico':
			$fields .= '<input name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '" type="number">';
			break;
		case 'input':
			$fields .= '<input name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '" type="text">';
			break;
		case 'data':
			$fields .= '<input name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '" type="date">';
			break;
		case 'hora':
			$fields .= '<input name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '" type="time">';
			break;
		case 'selecionavel':
			$compl = '';
			foreach ($campo->options as $k) {
				$compl .= '<option value="' . $k->nome . '">' . $k->nome . '</option>';
			}
			$fields .= '<select name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"><option value=""></option>' . $compl . '</select>';
			break;
		case 'fixo':
			$compl = '';
			foreach ($campo->options as $k) {
				$compl .= '<option value="' . $k->nome . '">' . $k->nome . '</option>';
			}
			$fields .= '<select name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"><option value=""></option>' . $compl . '</select>';
			break;
		case 'checkbox':
			$fields .= '<input name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '" type="checkbox" >';
			break;
		case 'textarea':
			$fields .= '<textarea name="campo_' . $campo->indice . '" data-indice="" data-tipo="editorhtml" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '" class="hide"></textarea>
						<div name="campoeditor_' . $campo->indice . '" data-tipo="divhtml" class="diveditor" onkeypress="pageStateChanged=true;" style="text-align: left;width:auto; height:66px; margin-right: 8px; border: none;"></div>';
			break;
	}

	return ($fields);
}


function criarCamposJsonConfig($campo, $valor = '')
{
	$fields = "";
	if ($campo->calculo ==  "SIM") {
		$calculo = 'data-calculo="SIM"';
	} else {
		$calculo = 'data-calculo="NAO"';
	}
	switch ($campo->tipo) {
		case 'numerico':
			$fields .= '<input data-valororiginal="'.$valor.'" name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  type="number" ' . $calculo . '>';
			break;
		case 'identificador':
			$fields .= '<input data-valororiginal="'.$valor.'" name="campo_' . $campo->indice . '" data-indice="" value="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  type="text" ' . $calculo . '>';
			break;
		case 'input':
			$fields .= '<input data-valororiginal="'.$valor.'" name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  type="text" ' . $calculo . '>';
			break;
		case 'data':
			$fields .= '<input data-valororiginal="'.$valor.'" name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  type="date" ' . $calculo . '>';
			break;
		case 'hora':
			$fields .= '<input data-valororiginal="'.$valor.'" name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  type="time" ' . $calculo . '>';
			break;
		case 'selecionavel':
			$compl = '';
			foreach ($campo->options as $k) {
				$compl .= '<option calcula="' . $k->calculo . '" value="' . $k->nome . '" class="aplicar' . $k->indice . '">' . $k->nome . '</option>';
			}
			$fields .= '<select data-valororiginal="'.$valor.'" name="campo_' . $campo->indice . '" indice-campo="'.$campo->indice.'" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '" ' . $calculo . ' ><option value="" class="aplicarvazio' . $campo->indice . '"></option>' . $compl . '</select>';
			break;
		case 'fixo':
			$compl = '';
			foreach ($campo->options as $k) {
				$compl .= '<option value="' . $k->nome . '" class="aplicar' . $k->indice . '">' . $k->nome . '</option>';
			}
			$fields .= '<select data-valororiginal="'.$valor.'" name="campo_' . $campo->indice . '" indice-campo="'.$campo->indice.'" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  ' . $calculo . ' ><option value="" class="aplicarvazio' . $campo->indice . '"></option>' . $compl . '</select>';
			break;
		case 'checkbox':
			$fields .= '<input data-valororiginal="'.$valor.'" name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  type="checkbox" ' . $calculo . ' >';
			break;
		case 'textarea':
			$fields .= '<textarea data-valororiginal="'.$valor.'" name="campo_' . $campo->indice . '" data-indice="" data-tipo="editorhtml" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  class="hide" ' . $calculo . ' ></textarea>
		 <div name="campoeditor_' . $campo->indice . '" data-tipo="divhtml" class="diveditor" onkeypress="pageStateChanged=true;" style="text-align: left;width:auto; height:66px; margin-right: 8px; border: none;"></div>';
			break;
	}

	return ($fields);
}



function montarMenuLateralServicosAmostra()
{

	global $_1_u_resultado_idamostra, $_1_u_resultado_idresultado, $arrSecretaria, $unidadepadrao;

	$listaDeServicos = InclusaoResultadoController::buscarServicosDaAmostra($_1_u_resultado_idamostra);

	echo '<div id="tbTestes">';

	foreach ($listaDeServicos as $key => $rowListaDeServicos) {
		$secretaria = $rowListaDeServicos["idpessoa"];		
		//BUSCAR SECRETARIAS
		$arrSecretaria = InclusaoResultadoController::buscarSecretariaResultado(getidempresa('pp.idempresa', 'pessoa'),$secretaria);

		$oficial = empty($rowListaDeServicos["secretaria"]) ? "naooficial" : "oficial";
		$testeativo = ($_1_u_resultado_idresultado == $rowListaDeServicos["idresultado"]) ? "ativo shadowRightGray" : "inativo";
		?>
		<div class="oTeste <?= $testeativo ?>" cbstatus="<?= $rowListaDeServicos["status"] ?>" onclick="CB.go(`idresultado=<?= $rowListaDeServicos['idresultado'] ?>`)">
			<table>
				<tr>
					<td class="sigla"><?= $rowListaDeServicos["sigla"] ?></td>
					<td class="quant"><span><?= $rowListaDeServicos["quant"] ?></span></td>
				</tr>
				<tr class="testerotulo">
					<td class="tipoteste"><?= $rowListaDeServicos["tipoteste"] . $rowListaDeServicos["rotulo"] ?></td>
					<td><span class="<?= $oficial ?>"><i class="fa fa-user-secret"></i></span></td>
				</tr>
			</table>
		</div>
		<div class="webui-popover-content">
			<table>
				<tr>
					<td>Teste:</td>
					<td class="nowrap"><?= $rowListaDeServicos["tipoteste"] ?></td>
				</tr>
				<tr>
					<td>Quant.:</td>
					<td><?= $rowListaDeServicos["quant"] ?></td>
				</tr>
				<?
				if ($rowListaDeServicos["secretaria"]) {
				?>
					<tr>
						<td class="nowrap"><i class="fa fa-user-secret"></i>&nbsp;Oficial:</td>
						<td class="nowrap"><?= $rowListaDeServicos["secretaria"] ?></td>
					</tr>
				<?
				}
				?>
			</table>
			<?
			
			$infoFluxoResultado = InclusaoResultadoController::buscarInfoFluxoResultado($_GET["_modulo"], $rowListaDeServicos["idresultado"])['data'];
			$numRowsinfoFluxoResultado = InclusaoResultadoController::buscarInfoFluxoResultado($_GET["_modulo"], $rowListaDeServicos["idresultado"])['numRows'];

			if ($numRowsinfoFluxoResultado > 0) {
			?>
				<hr>
				<table style="font-size: 10px">
					<tr>
						<td>STATUS</td>
						<td>ALTERADO POR</td>
						<td>ALTERADO EM</td>
					</tr>
					<?
					foreach ($infoFluxoResultado as $key => $rowInfoFluxoResultado) {
						if (($rowInfoFluxoResultado['valor'] == 'ASSINADO' and $rowInfoFluxoResultado['assinateste'] == 'Y') or $rowInfoFluxoResultado['valor'] != 'ASSINADO') {
					?>
							<tr>
								<td>
									<?= $rowInfoFluxoResultado['valor'] ?></td>
								<td>
									<?= $rowInfoFluxoResultado['criadopor'] ?>
								</td>
								<td>
									<?= dmahms($rowInfoFluxoResultado['criadoem']) ?>
								</td>
							</tr>
					<?
						}
					}
					?>
				</table>
			<?}?>
		</div>
	<?
	}
	echo '</div>';
	?>
	<i class="fa fa-plus-circle fa-2x verde pointer" onclick="novoTeste()" alt="Inserir novo teste"></i>
	<button id="novoTestesalvar" type="button" class="btn btn-danger btn-xs" onclick="adicionarTestes();" title="Adicionar Teste(s)" style="display:none">
		<i class="fa fa-circle"></i>Adicionar Teste(s)
	</button>
	<div id="modeloNovoTeste" class="hidden">
		<div class="oTeste novo">
			<table>
				<tr>
					<td class="tipoteste" colspan="2"><input class="formTmp" type="hidden" name="#nameidamostra" value="<?= $_1_u_resultado_idamostra ?>">
						<input class="formTmp" type="hidden" name="#namestatus" value="ABERTO">
						<input class="formTmp idprodserv" type="text" name="#nameidtipoteste" cbvalue placeholder="INFORME O TESTE" vnulo style="font-size:10px">
					</td>
				</tr>
				<tr class="testerotulo">
					<td class="quant"><span><input id="qtdteste" type="text" class="formTmp" name="#namequantidade" style="width:50px;font-size:10px" placeholder="QTD." vnulo vnumero></span></td>
					<?if($unidadepadrao!=9){?>
						<td>
							<select class="formTmp" name="#nameidsecretaria" style="font-size:10px;" placeholder="Secretaria">
								<option value="0"></option>
								<? fillselect($arrSecretaria, $rowListaDeServicos["idsecretaria"]);?>
							</select>
						</td>
					<?}?>
				</tr>
			</table>
		</div>
	</div>
<?}?>

<? require_once("../form/js/inclusaoresultado_js.php"); ?>
