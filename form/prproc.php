<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/prproc_controller.php");

if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

//Parà¢metros mandatários para o carbon
$pagvaltabela = "prproc";
$pagvalcampos = array(
	"idprproc" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT * FROM prproc WHERE idprproc = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");

$arrOpcoesInputManual = ["" => array("icone" => "fa fa-eye-slash cinzaclaro"), "check" => array("icone" => "fa fa-check-square-o verde"), "linha" => array("icone" => "fa fa-window-minimize verde"), "text" => array("icone" => "fa fa-comment verde")];

if (!empty($_1_u_prproc_idprproc)) 
{
	if ($_1_u_prproc_status == 'APROVADO') {
		$readonlyp = 'readonly';
	} else {
		$readonlyp = '';
	}
}
?>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table>
					<tr>
						<td>Processo:</td>
						<td>
							<input type="hidden" name="_1_<?=$_acao ?>_prproc_idprproc" value="<?=$_1_u_prproc_idprproc ?>">
							<input <?=$readonlyp ?> type="text" size="80" name="_1_<?=$_acao ?>_prproc_proc" value="<?=$_1_u_prproc_proc ?>">
						</td>
						<td>Tipo:</td>
						<td>
							<select <?=$readonlyp ?> name="_1_<?=$_acao ?>_prproc_tipo" vnulo>
								<option value=""></option>
								<? fillselect(PrProcController::tipoProdserv(), $_1_u_prproc_tipo); ?>
							</select>
						</td>
						<td>Sub Tipo:</td>
						<td>
							<select <?=$readonlyp ?> name="_1_<?=$_acao ?>_prproc_subtipo" vnulo>
								<option value=""></option>
								<? fillselect(PrProcController::buscarFillSelectSubtipoFormalizacao(), $_1_u_prproc_subtipo); ?>
							</select>
						</td>
						<? if ($_acao == 'i') { ?>
							<td>Status:</td>
							<td>
								<input class="size8" type="hidden" name="_1_<?=$_acao ?>_prproc_status" value="REVISAO">
								<label class="alert-warning">REVISAO</label>
							</td>
						<? } else { ?>
							<td>Status:</td>
							<!-- @512299 - ERRO FLUXO PRPROC E PRATIV -- O rótulo foi alterado para exibir para o usuário a mesma informação contida no fluxo -->
							<td>
								<span>
									<input type="hidden" class="size8" name="_1_<?=$_acao ?>_prproc_status" value="<?=$_1_u_prproc_status ?>">
									<input type="hidden" name="_statusant_" value="<?=$_1_u_prproc_status ?>">
									<? $rotulo = getStatusFluxo($pagvaltabela, 'idprproc', $_1_u_prproc_idprproc) ?>
									<label class="alert-warning" title="<?=$_1_u_prproc_status ?>" id="statusButton"><?= mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?> </label>
								</span>
							</td>
						<? } ?>
						<td></td>
						<td>Versão:</td>
							<? if ($_acao == 'i') {
								?><td>0.0</td><?
							} else {
								?><td>
								<?=$_1_u_prproc_versao ?>.0
								<input name="_1_<?=$_acao ?>_prproc_versao" value="<?=$_1_u_prproc_versao ?>" type="hidden">
								</td><?
							} ?>
						</td>
						<td></td>
						<td>
							Tempo total estimado:
						</td>
						<td>
							<?
							if($_1_u_prproc_idprproc){
								$vreadonly = PrProcController::buscarPrProPrativComHoraEstimada($_1_u_prproc_idprproc);
								
								if($vreadonly == false || $vreadonly == '00:00:00'){
									$readonlyTime = '';
								}else{
									$readonlyTime = 'readonly';
									// $_1_u_prproc_tempoestimado = $vreadonly;
								}
							}
							?>
							<input id="tempofinal" type="text" <?=$readonlyTime?> name="_1_<?=$_acao ?>_prproc_tempoestimado" placeholder="00:00" oninput="setMaskPattern(this)" style="background-color:white; text-align: center; align-self: center;display: flex;letter-spacing: 3px;"  value="<?=substr($_1_u_prproc_tempoestimado, 0, -3);?>" class="size8">
						</td>
						<?
						if($_1_u_prproc_idprproc){?>
							<td>
								<label>Tempo Gasto Obrigatório</label>
							</td>
							<td>
								<input type="checkbox" <?=$readonlyp?> idprproc="<?=$_1_u_prproc_idprproc?>" name="_1_<?=$_acao ?>_prproc_tempogastoobrigatorio" value="<?=$_1_u_prproc_tempogastoobrigatorio?>" <?=$_1_u_prproc_tempogastoobrigatorio == 'Y' ? 'checked' : ''; ?> onclick="alterarTempoGastoObrigatorio(this)">
							</td>
						<?}?>
					</tr>
				</table>
			</div>
			<div class="panel-body">
				<div class="divbody enable-sortable">
					<?
					if (!empty($_1_u_prproc_idprproc)) 
					{
						$_listarProcesso = PrProcController::buscarProcessosPorTipoEIdEmpresa($_1_u_prproc_idprproc);
						$i = 1;
						foreach($_listarProcesso as $processo) 
						{
							$i++;
							$cor = "";
							$readonly = 'readonly';

							if ($readonlyp == 'readonly') {
								$readonlypr = 'readonly';
								$disabledpr = 'disabled';
							} else {
								$readonlypr = '';
								$disabledpr = '';
							}

							if ($processo['status'] == 'INATIVO') {
								$cor = "rgba(208,0,56,0.5)";
							}
							if ($processo['status'] == 'APROVADO') {
								$cor = "rgba(150, 201, 101,0.5)";
							}
							?>
							<div class="row">
								<div class="col-md-12">
									<div class="panel panel-default" style="margin-top: 0px !important">
										<div class="panel-heading" style="background-color: <?=$cor?>;">
											<table>
												<tr>
													<td>
														<? if ($_1_u_prproc_status != 'APROVADO') { ?>
															<i class="fa fa-arrows cinzaclaro hover move" title="Ordenar atividade #<?=$processo["idprprocprativ"]?>"></i>
														<? } ?>
														<input type="hidden" idatividade="<?=$processo["idprprocprativ"]?>" name="_<?=$i?>_<?=$_acao ?>_prprocprativ_ord" value="<?=$processo["ordem"]?>">
														<input type="hidden" name="_<?=$i?>_<?=$_acao ?>_prprocprativ_idprprocprativ" value="<?=$processo["idprprocprativ"]?>">
														<?
														$ipr = $i;
														$i++; // aumentar o i para idprativ
														$bi = $i;
														?>
													</td>
													<td>
														<button 
															type="button" 
															class="btn btn-link" 
															data-toggle="popover"
															title="Alterar cor"
															idprprocprativ="<?=$processo["idprprocprativ"]?>" 
															loteimpressao="<?=$processo["loteimpressao"]?>"
															title="Alterar Lote de Impressão: #<?=$processo["loteimpressao"]?>"
															style="color:<?= PrProcController::$arrCores[$processo["loteimpressao"]]?>;"
														>
															<i class="fa fa-print pointer"></i>
														</button>
													</td>
													<td title="<?=$processo["ativ"]?>">
														<label>
															<a href="javascript:janelamodal('?_modulo=prativ&_acao=u&idprativ=<?=$processo["idprativ"]?>')" title="Editar Atividade">
																Atividade
															</a>
														</label>
														<input type="hidden" name="_<?=$i?>_<?=$_acao ?>_prativ_idprativ" value="<?=$processo["idprativ"]?>">
														<input type="text" <?=$readonly ?> class="inlineblocktop inputatividade" size="80" name="_<?=$i?>_<?=$_acao ?>_prativ_ativ" value="<?=$processo["ativ"]?>" disabled>
													</td>
													<td style="padding-bottom: 10px; padding-left: 1%;">
														<label>Status OP:</label>
														<input type="hidden" name="_<?=$i?>_<?=$_acao ?>_prativ_statuspai" value="<?=$processo["statuspai"]?>" id="<?=$processo["idfluxostatuspp"]?>">
														<select <?=$readonlypr?> class="fluxo-select" name="_<?=$ipr ?>_<?=$_acao ?>_prprocprativ_idfluxostatus" style="width: 95%">
															<option value=""></option>
															<? fillselect(PrProcController::buscarFillSelectFluxoPorModuloETipoObjeto('formalizacao', 'subtipo', $_1_u_prproc_subtipo), $processo["idfluxostatuspp"]); ?>
														</select>
													</td>
													<td style="width: 8%; padding-bottom: 10px; padding-right: 1%;">
														<label>Bloquear Status:</label>
														<?
														$value = $processo["bloquearstatus"] == 'Y' ? 'N' : 'Y';
														?>
														<input <?=$disabledpr?> type="checkbox" bloquearstatus="<?=$processo["bloquearstatus"]?>" <?=$processo["bloquearstatus"] == 'Y' ? 'checked' : ''; ?> onclick="alterarBloqueioStatus('<?=$processo['idprprocprativ']?>', this)">
													</td>
													<td>
														<label>Etapa:</label>
														<select <?=$readonlypr?> class="size5" name="_<?=$ipr ?>_<?=$_acao ?>_prprocprativ_idetapa">
															<option value=""></option>
															<? fillselect(PrProcController::buscarEtapaPorTipoObjeto('formalizacao', 'subtipo', $_1_u_prproc_subtipo), $processo["idetapa"]); ?>
														</select>
													</td>
													<td>
														<label>Prazo D:</label>
														<input type="text" <?=$readonlypr?> class="size5" name="_<?=$ipr ?>_<?=$_acao ?>_prprocprativ_prazod" value="<?=$processo["prazod"]?>">
													</td>
													<td>
														<label>Sala</label>
														<?
														$ressala = PrProcController::buscarTagClassPorTipoObjetoEIdPrativ(2, 'tagtipo', $processo['idprativ']);
														$sala = $ressala['dados'][0];
														if ($ressala['qtdLinhas'] > 0) {
															$iu = 'u';
														} else {
															$iu = 'i';
														}
														$y = $i;
														$i = $i + 1;
														?>
														<input type="hidden" name="_<?=$i?>_<?=$iu ?>_prativobj_idprativobj" value="<?=$sala["idprativobj"]?>">
														<input type="hidden" name="_<?=$i?>_<?=$iu ?>_prativobj_tipoobjeto" value="tagtipo">
														<input type="hidden" name="_<?=$i?>_<?=$iu ?>_prativobj_idprativ" value="<?=$processo["idprativ"]?>">
														<select class="size20" <?=$readonly ?> name="_<?=$i?>_<?=$iu ?>_prativobj_idobjeto" disabled>
															<option></option>
															<? fillselect(PrProcController::listarFillSelectTagPorIdTagClass(2), $sala["idobjeto"]); ?>
														</select>
													</td>
													<?
													$_listarPrativOpcaoBioterio = PrProcController::buscarPrativOpcaoPorTipo('prativopcao', $processo['idprativ'], 'bioterio');
													foreach($_listarPrativOpcaoBioterio as $prativOpcaoBioterio) 
													{
														?>
														<td><label><? echo ($prativOpcaoBioterio["descr"]); ?></label><br>
															<?
															if (!empty($prativOpcaoBioterio["idprativobj"])) {
															?>
																<i style="padding-right: 0px;pointer-events: none;" class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="excluiitem(<?=$prativOpcaoBioterio['idprativobj']?>)" alt="Alterar para Não"></i>
															<? } else { ?>
																<i style="padding-right: 0px;pointer-events: none;" class="fa fa-square-o fa-1x btn-lg pointer" onclick="inserircontr(<?=$processo['idprativ']?>, <?=$prativOpcaoBioterio['idprativopcao']?>, 'prativopcao')" alt="Alterar para Sim"></i>
															<? }
															?>
														</td>
														<?
													}
													?>
													<td class="nowrap"><label>Dia</label><br>
														<input <?=$readonlypr ?> type="text" class="size3" name="prprocprativ_dia" onchange="atualizardia(this,<?=$processo['idprprocprativ']?>);" value="<?=$processo['dia']?>" vnulo>
													</td>
													<td class="nowrap">
														<label>Tempo Estimado:</label><br>
														<input <?=$readonlypr ?> class="tempoestimado" type="text" name="_<?=$ipr ?>_<?=$_acao ?>_prprocprativ_tempoestimado" placeholder="00:00" oninput="setMaskPattern(this)" style="background-color:white; text-align: center; align-self: center;display: flex;letter-spacing: 3px;"  value="<?=substr($processo["tempoestimado"], 0, -3);?>">
													</td>
													<? if (empty($readonlypr)) { ?>
														<td>
															<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluiatividade(<?=$processo['idprprocprativ']?>)" title="Excluir Atividade #<?=$processo["idprativ"]?>"></i>
														</td>
														<td>
															<i class="fa fa-arrows-v cinzaclaro pointer" title="Configurações da Atividade" data-toggle="collapse" href="#atividadeinfo<?=$processo["idprativ"]?>"></i>
														</td>
													<? } else { ?>
														<td></td>
														<td></td>
													<? } ?>
												</tr>
											</table>
										</div>
										<div class="panel-body <?=$processo["collapse"]?>" id="atividadeinfo<?=$processo["idprativ"]?>" idprativ=<?=$processo["idprativ"]?>>
											<table>
												<tr>
													<?
													$_listarPrativOpcaoConclusao = PrProcController::buscarPrativOpcaoPorTipo('prativopcao', $processo['idprativ'], 'camposconclusao');
													foreach($_listarPrativOpcaoConclusao as $opcaoConclusao) 
													{
														?>
														<td>
															<?
															if (!empty($opcaoConclusao["idprativobj"])) {
															?>
																<i style="padding-right: 0px;pointer-events: none;" class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="excluiitem(<?=$opcaoConclusao['idprativobj']?>)" alt="Alterar para Não"></i>
															<? } else { ?>
																<i style="padding-right: 0px;pointer-events: none;" class="fa fa-square-o fa-1x btn-lg pointer" onclick="inserircontr(<?=$processo['idprativ']?>,<?=$opcaoConclusao['idprativopcao']?>,'prativopcao')" alt="Alterar para Sim"></i>
															<? }
															echo ($opcaoConclusao["descr"]);
															?>
														</td>
														<?
													}
													?>
												</tr>
											</table>
											<hr>
											<div class="row">
												<div class="col-md-3">
													<div class="panel panel-default">
														<div class="panel-heading">Equipamentos</div>
														<div class="panel-body">
															<table class="table table-striped planilha sortable" style="width: 100%; ">
																<?
																$_listarEquipapmento = PrProcController::buscarTagClassPorTipoObjetoEIdPrativ(1, 'tagtipo', $processo['idprativ']);
																$equipamento = $_listarEquipapmento['dados'];
																$i++;
																if ($_listarEquipapmento['qtdLinhas'] > 0) 
																{
																	foreach($equipamento as $item) 
																	{
																		$i++;
																		$ico = empty($arrOpcoesInputManual[$item["inputmanual"]]["icone"]) ? "fa fa-eye-slash cinzaclaro" : $arrOpcoesInputManual[$item["inputmanual"]]["icone"];
																		?>
																		<tr>
																			<td>
																				<i class="hoververde pointer <?=$ico ?>" style="pointer-events: none;" title="Mostrar quadro para check manual ou espaço para escrita manual" idprativobj="<?=$item["idprativobj"]?>" inputmanual="<?=$item["inputmanual"]?>" onclick="alteraInputmanual(this)"></i>
																				<input type="hidden" name="_<?=$i?>_<?=$_acao ?>_prativobj_ord" value="<?=$item["ord"]?>">
																				<input type="hidden" name="_<?=$i?>_<?=$_acao ?>_prativobj_idprativobj" value="<?=$item["idprativobj"]?>">
																				<?=$item['tagtipo']?>
																				<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" style="pointer-events: none;" onclick="excluiitem(<?=$item["idprativobj"]?>)" alt="Excluir item!"></i>
																			</td>
																		</tr>
																		<?
																	} //while($item1=mysqli_fetch_assoc($resitem)){
																} //if($qtdrowitem>0){	
																?>
																<tr>
																	<td>
																		<select <?=$readonly ?> name="prativobj<?=$i?>" onchange="novoitem(<?=$processo['idprativ']?>,'tagtipo','prativobj',<?=$i?>);" disabled>
																			<option></option>
																			<? fillselect(PrProcController::listarFillSelectTagPorIdTagClassEStatus(1)); ?>
																		</select>
																	</td>
																</tr>
															</table>
														</div>
													</div>
												</div>
												<div class="col-md-3">
													<div class="panel panel-default">
														<div class="panel-heading">
															<table>
																<tr>
																	<td>Testes</td>
																	<td>
																		<select disabled class="size15" name="_<?=$bi ?>_<?=$_acao ?>_prativ_idsubtipoamostra">
																			<option value="">Tipo da Amostra</option>
																			<? fillselect(PrProcController::listarFillSelectSubtipoamostraPorIdEmpresa(), $processo['idsubtipoamostra']); ?>
																		</select>
																	</td>
																</tr>
															</table>
														</div>
														<div class="panel-body">
															<table class="table table-striped planilha sortable" style="width: 100%; ">
																<?
																$resitem = PrProcController::buscarPrativObjPorTipoObjeto('prodserv', $processo['idprativ'], 'SERVICO');
																$i++;
																$qtdrowitemteste = $resitem['qtdLinhas'];
																if ($qtdrowitemteste > 0) 
																{
																	foreach($resitem['dados'] as $prativObj) 
																	{
																		$i++;
																		?>
																		<tr>
																			<td>
																				<input type="hidden" name="_<?=$i?>_<?=$_acao ?>_prativobj_ord" value="<?=$prativObj["ord"]?>">
																				<input type="hidden" name="_<?=$i?>_<?=$_acao ?>_prativobj_idprativobj" value="<?=$prativObj["idprativobj"]?>">
																				<?=$prativObj['descr']?>
																				<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" style="pointer-events: none;" onclick="excluiitem(<?=$prativObj['idprativobj']?>)" alt="Excluir item!"></i>
																			</td>
																		</tr>
																		<?
																	} //while($item1=mysqli_fetch_assoc($resitem)){
																} //if($qtdrowitemteste>0){	
																?>
																<tr>
																	<td>
																		<select <?=$readonly ?> name="prativobj<?=$i?>" onchange="novoitem(<?=$processo['idprativ']?>,'prodserv','prativobj',<?=$i?>);" disabled>
																			<option></option>
																			<? fillselect(PrProcController::listarFillSelectProdutoPorTipoEAtivo('SERVICO')); ?>
																		</select>
																	</td>
																</tr>
															</table>
														</div>
													</div>
												</div>
												<div class="col-md-3">
													<div class="panel panel-default">
														<div class="panel-heading">Informações específicas</div>
														<div class="panel-body">
															<table class="table table-striped planilha sortable" style="width: 100%;">
																<?
																$_listarAtividadesPrativ = PrProcController::buscarATividadesPorIdPrativETipoObjeto('ctrlproc', $processo['idprativ']);
																$qtdrowitem = $_listarAtividadesPrativ['qtdLinhas'];
																$i++;
																if ($qtdrowitem > 0) 
																{
																	foreach($_listarAtividadesPrativ['dados'] as $ativPrativ) 
																	{
																		$i++;
																		$ico = empty($arrOpcoesInputManual[$ativPrativ["inputmanual"]]["icone"]) ? "fa fa-eye-slash cinzaclaro" : $arrOpcoesInputManual[$ativPrativ["inputmanual"]]["icone"];
																		?>
																		<tr>
																			<td>
																				<i class="hoververde pointer <?=$ico ?>" style="pointer-events: none;" title="Mostrar quadro para check manual ou espaço para escrita manual" idprativobj="<?=$ativPrativ["idprativobj"]?>" inputmanual="<?=$ativPrativ["inputmanual"]?>" onclick="alteraInputmanual(this)"></i>
																				<input type="hidden" name="_<?=$i?>_<?=$_acao ?>_prativobj_ord" value="<?=$ativPrativ["ord"]?>">
																				<input type="hidden" name="_<?=$i?>_<?=$_acao ?>_prativobj_idprativobj" value="<?=$ativPrativ["idprativobj"]?>">
																				<?=$ativPrativ['descr']?>
																				<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" style="pointer-events: none;" onclick="excluiitem(<?=$ativPrativ['idprativobj']?>)" alt="Excluir item!"></i>
																			</td>
																		</tr>
																	<?
																	} //while($item1=mysqli_fetch_assoc($resitem)){
																} //if($qtdrowitem>0){	
																?>
																<tr>
																	<td>
																		<input class="ctrlproc" <?=$readonly ?> name="prativobj<?=$i?>" type="text" idprativ="<?=$processo['idprativ']?>" disabled>
																	</td>
																</tr>
															</table>
														</div>
													</div>
												</div>

												<div class="col-md-3">
													<div class="panel panel-default">
														<div class="panel-heading">Materiais e Utensílios</div>
														<div class="panel-body">
															<table class="table table-striped planilha sortable" style="width: 100%; ">
																<?
																$_listarMateriais = PrProcController::buscarATividadesPorIdPrativETipoObjeto('materiais', $processo['idprativ']);
																$qtdrowitem = $_listarMateriais['qtdLinhas'];
																$i++;
																if ($qtdrowitem > 0) 
																{
																	foreach($_listarMateriais['dados'] as $materiais) 
																	{
																		$i++;
																		$ico = empty($arrOpcoesInputManual[$materiais["inputmanual"]]["icone"]) ? "fa fa-eye-slash cinzaclaro" : $arrOpcoesInputManual[$materiais["inputmanual"]]["icone"];
																		?>
																		<tr>
																			<td>
																				<i class="hoververde pointer <?=$ico ?>" style="pointer-events: none;" title="Mostrar quadro para check manual ou espaço para escrita manual" idprativobj="<?=$materiais["idprativobj"]?>" inputmanual="<?=$materiais["inputmanual"]?>" onclick="alteraInputmanual(this)"></i>
																				<input type="hidden" name="_<?=$i?>_<?=$_acao ?>_prativobj_ord" value="<?=$materiais["ord"]?>">
																				<input type="hidden" name="_<?=$i?>_<?=$_acao ?>_prativobj_idprativobj" value="<?=$materiais["idprativobj"]?>">
																				<?=$materiais['descr']?>
																				<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" style="pointer-events: none;" onclick="excluiitem(<?=$materiais['idprativobj']?>)" alt="Excluir item!"></i>
																			</td>
																		</tr>
																	<?
																	} //while($item1=mysqli_fetch_assoc($resitem)){
																} //if($qtdrowitem>0){	
																?>
																<tr>
																	<td>
																		<input class="materiais" <?=$readonly ?> name="prativobj<?=$i?>" type="text" idprativ="<?=$processo['idprativ']?>" disabled>
																	</td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?
						} //while ($processo = mysqli_fetch_assoc($res)){

						if ($_1_u_prproc_status != 'APROVADO') 
						{
							$rowmo = PrProcController::buscarOrdemPrProcPrativPorIdPrProc($_1_u_prproc_idprproc);
							?>
							<div class="row">
								<div class="col-md-6">
									<div class="panel panel-default">
										<div class=" panel-body">
											<table>
												<tr>
													<td>Atividade:</td>
													<td>
														<input ordem="<?=$rowmo['ordem']?>" name="prproc_idprativ" id="prproc_idprativ" ordem="<?=$rowmo['ordem']?>" cbvalue="" value="" style="width: 40em;">
													</td>
												</tr>
											</table>
										</div>
									</div>
								</div>
							</div>
							<? 	
						}
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<?
if (!empty($_1_u_prproc_idprproc)) 
{
	$listarProcesso = PrProcController::buscarProdservPrprocPorIdPrProc($_1_u_prproc_idprproc);
	if($listarProcesso['qtdLinhas'] > 0) 
	{
		?>
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">Produtos</div>
					<div class="panel-body">
						<table class="planilha grade compacto">
							<tr>
								<th>Produto</th>
								<th></th>
							</tr>
							<?
							foreach($listarProcesso['dados'] as $processo) 
							{
								?>
								<tr class="res">
									<td style="line-height:normal;"><?=$processo["descr"]?></td>
									<td style="line-height:normal;"><a class="fa fa-bars pointer hoverazul " title="Produto" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$processo['idprodserv']?>')"></a></td>
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
	?>
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">Histórico</div>
				<div class="panel-body">
					<table style="width: 100%;" class="planilha grade compacto">
						<tr>
							<th><b>Versões</b></th>
							<th><b>Descrição</b></th>
							<th><b>Revisado Por</b></th>
							<th><b>Revisado Em</b></th>
							<th><b>Aprovado Por</b></th>
							<th><b>Aprovado Em</b></th>
						</tr>
						<? if ($_1_u_prproc_status != 'APROVADO') { ?>
							<tr>
								<td style="width: 30%;">Versão: <?=$_1_u_prproc_versao ?>.0</td>
								<td colspan="5" style="width: 70%;"><TEXTAREA name="_1_<?=$_acao ?>_prproc_descr" vnulo><?=$_1_u_prproc_descr ?></TEXTAREA></td>
							</tr>
						<? } 

						$_listarHistorico = PrProcController::buscarObjetoPorTipoObjeto($_1_u_prproc_idprproc, 'prproc');
						foreach ($_listarHistorico as $historico) 
						{
							$rc = unserialize(base64_decode($historico["jobjeto"]));
							if (($rc['prprocprativ']['rev']['naomostrar'] == 'Y') || ($historico['versaoobjeto'] == $_1_u_prproc_versao && ($_1_u_prproc_status == 'REVISAO' || $_1_u_prproc_status == 'AGUARDANDO'))) {
								continue;
							}
							?>
							<tr class="res">
								<td nowrap><a href="report/prproc.php?idprproc=<?=$historico['idobjeto']?>&versao=<?=$historico['versaoobjeto']?>" target="_blank">Versão: <?=$historico['versaoobjeto']?>.0</a></td>
								<td style="line-height: 1.5; width: 50%;"><?= nl2br($rc['prproc']['res']['descr']) ?></td>
								<td style="line-height: 1.5;"><?= nl2br($rc['prprocprativ']['ref']['revisadopor']) ?></td>
								<td style="line-height: 1.5;"><?= dmahms(nl2br($rc['prprocprativ']['ref']['revisadoem'])) ?></td>
								<td style="line-height: 1.5;"><?= nl2br($rc['prproc']['res']['alteradopor']) ?></td>
								<td style="line-height: 1.5;"><?= dmahms(nl2br($rc['prproc']['res']['alteradoem'])) ?></td>
							</tr>
							<?
						}
						?>
					</table>
				</div>
			</div>
		</div>
	</div>
	<script src=".\inc\js\jquery\vanilla-masker.js"></script>
<? 
} 

require_once('../form/js/prproc_js.php'); 
$tabaud = "prproc"; //pegar a tabela do criado/alterado em antigo
require '../form/viewCriadoAlterado.php';
?>