<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/prativ_controller.php");

if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

//Parametros mandatarios para o carbon
$pagvaltabela = "prativ";
$pagvalcampos = array(
	"idprativ" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT * FROM prativ WHERE idprativ = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");
// Configuração de inputs visà­veis conforme combinação de tipo e subtipo de amostra

$arrOpcoesInputManual = ["" => array("icone" => "fa fa-eye-slash cinzaclaro"), "check" => array("icone" => "fa fa-check-square-o verde"), "linha" => array("icone" => "fa fa-window-minimize verde"), "text" => array("icone" => "fa fa-comment verde")];

if ($_1_u_prativ_idprativ) 
{
	if ($_1_u_prativ_status == 'APROVADO') {
		$readonly = "readonly";
		$disasbled = "disabled";
	} else {
		$readonly = "";
		$disasbled = "";
	}
}
?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table>
					<tr>
						<td><label class="text-white">Atividade</label></td>
						<td>
							<input type="hidden" idatividade="<?=$_1_u_prativ_idprativ ?>" name="_1_<?=$_acao ?>_prativ_ord" value="<?=$_1_u_prativ_ord ?>">
							<input type="hidden" name="_1_<?=$_acao ?>_prativ_idprativ" value="<?=$_1_u_prativ_idprativ ?>">
							<input type="hidden" name="_1_<?=$_acao ?>_prativ_ord" value="1">
							<input type="text" <?=$readonly ?> class="inlineblocktop inputatividade" size="80" name="_1_<?=$_acao ?>_prativ_ativ" value="<?=$_1_u_prativ_ativ ?>">
						</td>
						<? if ($_acao == 'i') { ?>
							<td>Status:</td>
							<td>
								<input class="size8" type="hidden" name="_1_<?=$_acao ?>_prativ_status" value="REVISAO">
								<label class="alert-warning">REVISAO</label>
							</td>
						<? } else { ?>
							<td>Status:</td>
							<!-- @512299 - ERRO FLUXO PRPROC E PRATIV -- O rótulo foi alterado para exibir para o usuário a mesma informação contida no fluxo -->
							<td>
								<span>
									<input type="hidden" class="size8" name="_1_<?=$_acao ?>_prativ_status" value="<?=$_1_u_prativ_status ?>">
									<input type="hidden" name="_statusant_" value="<?=$_1_u_prativ_status ?>">
									<? $rotulo = getStatusFluxo($pagvaltabela, 'idprativ', $_1_u_prativ_idprativ) ?>
									<label class="alert-warning" title="<?=$_1_u_prativ_status ?>" id="statusButton"><?=mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?> </label>
								</span>
							</td>
						<? } ?>
						<td>Versão:</td>
						<? if ($_acao == 'i') 
						{
							?><td>0.0</td><?
						} else {
							?>
							<td><?=$_1_u_prativ_versao ?>.0</td>
							<input name="_1_<?=$_acao ?>_prativ_versao" value="<?=$_1_u_prativ_versao ?>" type="hidden">
							<?
						} ?>
					</tr>
					<?
					if ($_1_u_prativ_idprativ) 
					{
						?>
						<tr>
							<td>Nome Curto Atividade</td>
							<td>
								<input <?=$readonly ?> type="text" class="inlineblocktop inputatividade" size="80" name="_1_<?=$_acao ?>_prativ_nomecurtoativ" value="<?=$_1_u_prativ_nomecurtoativ ?>">
							</td>
							<td>
								<label class="text-white">Tipo tag (Sala):</label>
							</td>
							<td>
								<?
								$_listarSala = PrativController::buscarPrativObjPorTipoEIdPrativ(2, 'tagtipo', $_1_u_prativ_idprativ);
								$sala = $_listarSala['dados'][0];
								if($_listarSala['qtdLinhas'] > 0) {
									$iu = 'u';
								} else {
									$iu = 'i';
								}
								$i = 1;
								$i = $i + 1;
								?>
								<input type="hidden" name="_sala_<?=$iu ?>_prativobj_idprativobj" value="<?=$sala["idprativobj"] ?>">
								<input type="hidden" name="_sala_<?=$iu ?>_prativobj_tipoobjeto" value="tagtipo">
								<input type="hidden" name="_sala_<?=$iu ?>_prativobj_idprativ" value="<?=$_1_u_prativ_idprativ ?>">
								<select <?=$readonly ?> name="_sala_<?=$iu ?>_prativobj_idobjeto">
									<option></option>
									<? fillselect(PrativController::listarFillSelectTagPorIdTagClass(2), $sala["idobjeto"]); ?>
								</select>
							</td>
							<td>Reserva:</td>
							<td>
								<select <?=$readonly ?> class="size8" name="_1_<?=$_acao ?>_prativ_travasala">
									<? fillselect(PrativController::$travasala, $_1_u_prativ_travasala); ?>
								</select>
							</td>
							<?if($_1_u_prativ_idprativ) {?>
								<td>Logistica:</td>
								<td>
									<input id="tipo-logistica" type="checkbox" <?= $_1_u_prativ_logistica == 'Y' ? 'checked' : '' ?> >
								</td>
							<?}?>
						</tr>
					<?
					} //if($_1_u_prativ_idprativ){
					?>
				</table>
			</div>
			<div class="panel-body" id="atividadeinfo<?=$_1_u_prativ_idprativ ?>" idprativ=<?=$_1_u_prativ_idprativ ?>>
				<? if ($_1_u_prativ_idprativ) 
				{ 
					?>
					<table>
						<tr>
							<?
							$_listarPrativOpcao = PrativController::buscarPrativOpcaoPorTipo('prativopcao', $_1_u_prativ_idprativ, "'camposconclusao'");
							foreach($_listarPrativOpcao as $prativOpcao) 
							{
								?>
								<td>
									<?
									if ($_1_u_prativ_status == 'APROVADO') 
									{
										if (!empty($prativOpcao["idprativobj"])) 
										{
											?>
											<i style="padding-right: 0px;" class="fa fa-check-square-o fa-1x btn-lg pointer"></i>
										<? } else { ?>
											<i style="padding-right: 0px;" class="fa fa-square-o fa-1x btn-lg pointer"></i>
										<? }
										echo ($prativOpcao["descr"]);
									} else {
										if (!empty($prativOpcao["idprativobj"])) {
										?>
											<i style="padding-right: 0px;" class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="excluiitem(<?=$prativOpcao['idprativobj'] ?>)" alt="Alterar para Não"></i>
										<? } else { ?>
											<i style="padding-right: 0px;" class="fa fa-square-o fa-1x btn-lg pointer" onclick="inserircontr(<?=$_1_u_prativ_idprativ ?>,<?=$prativOpcao['idprativopcao'] ?>,'prativopcao')" alt="Alterar para Sim"></i>
									<? }
										echo ($prativOpcao["descr"]);
									} ?>
								</td>
								<?
							}

							$_listarBioterio = PrativController::buscarPrativOpcaoPorTipo('prativopcao', $_1_u_prativ_idprativ, "'bioterio'");
							foreach($_listarBioterio as $bioterio) 
							{
								?>
								<td>
									<?
									if ($_1_u_prativ_status == 'APROVADO') 
									{
										if (!empty($bioterio["idprativobj"])) {
										?>
											<i style="padding-right: 0px;" class="fa fa-check-square-o fa-1x btn-lg pointer"></i>
										<? } else { ?>
											<i style="padding-right: 0px;" class="fa fa-square-o fa-1x btn-lg pointer"></i>
										<? }
										echo ($bioterio["descr"]);
									} else {
										if (!empty($bioterio["idprativobj"])) {
										?>
											<i style="padding-right: 0px;" class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="excluiitem(<?=$bioterio['idprativobj'] ?>)" alt="Alterar para Não"></i>
										<? } else { ?>
											<i style="padding-right: 0px;" class="fa fa-square-o fa-1x btn-lg pointer" onclick="inserircontr(<?=$_1_u_prativ_idprativ ?>,<?=$bioterio['idprativopcao'] ?>,'prativopcao')" alt="Alterar para Sim"></i>
										<? 
										}
										echo ($bioterio["descr"]);
									} ?>
								</td>
							<?
							}
							?>
						</tr>
						<tr>
							<td>
								<div class="d-flex align-items-center">
									<input id="habilitar-nao-conformidade" name="_1_<?=$_acao ?>_prativ_naoconformidade" value="Y" class="mt-0" type="checkbox" <?= $_1_u_prativ_naoconformidade == 'Y' ? 'checked' : '' ?> />
									<label for="habilitar-nao-conformidade" class="ml-2">Permite Não Conformidade</label>
								</div>
							</td>
							<td>
								<div class="d-flex align-items-center">
									<input id="habilitar-cancelamento" name="_1_<?=$_acao ?>_prativ_cancelamento" value="Y" class="mt-0" type="checkbox" <?= $_1_u_prativ_cancelamento == 'Y' ? 'checked' : '' ?> />
									<label for="habilitar-cancelamento" class="ml-2">Permite Cancelamento</label>
								</div>
							</td>
						</tr>
					</table>
					<hr>
					<div class="row">
						<div class="col-md-3">
							<div class="panel panel-default">
								<div class="panel-heading">Tipo tag (Equipamento)</div>
								<div class="panel-body">
									<table class="table table-striped planilha sortable" style="width: 100%;">
										<?
										$_listarTipoTag = PrativController::buscarPrativObjPorTipoEIdPrativ(1, 'tagtipo', $_1_u_prativ_idprativ);
										$i++;
										if($_listarTipoTag['qtdLinhas'] > 0) 
										{
											foreach($_listarTipoTag['dados'] as $tipoTag) 
											{
												$i++;
												$ico = empty($arrOpcoesInputManual[$tipoTag["inputmanual"]]["icone"]) ? "fa fa-eye-slash cinzaclaro" : $arrOpcoesInputManual[$tipoTag["inputmanual"]]["icone"];
												?>
												<tr>
													<td>
														<? if ($_1_u_prativ_status != 'APROVADO') { ?>
															<i class="hoververde pointer <?=$ico ?>" title="Mostrar quadro para check manual ou espaço para escrita manual" idprativobj="<?=$tipoTag["idprativobj"] ?>" inputmanual="<?=$tipoTag["inputmanual"] ?>" onclick="alteraInputmanual(this)"></i>
														<? } else { ?>
															<i class="hoververde pointer <?=$ico ?>" title="Mostrar quadro para check manual ou espaço para escrita manual" idprativobj="<?=$tipoTag["idprativobj"] ?>" inputmanual="<?=$tipoTag["inputmanual"] ?>"></i>
														<? } ?>
														<input type="hidden" name="_<?=$i ?>_<?=$_acao ?>_prativobj_ord" value="<?=$tipoTag["ord"] ?>">
														<input type="hidden" name="_<?=$i ?>_<?=$_acao ?>_prativobj_idprativobj" value="<?=$tipoTag["idprativobj"] ?>">
														<?=$tipoTag['tagtipo'] ?>
														<? if ($_1_u_prativ_status != 'APROVADO') { ?>
															<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluiitem(<?=$tipoTag['idprativobj'] ?>)" alt="Excluir item!"></i>
														<? } ?>
													</td>
												</tr>
												<?
											} 
										} //if($qtdrowitem>0){	
										?>
										<tr>
											<td>
												<input type="text" id="prativ_equipamento" placeholder="Selecione um equipamento" <?=$disasbled ?>>
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
												<select <?=$readonly ?> class="size20" name="_1_<?=$_acao ?>_prativ_idsubtipoamostra">
													<option value="">Tipo da Amostra</option>
													<? fillselect(PrativController::buscarSubtipoamostraEmpresaPorIdEmpresa(), $_1_u_prativ_idsubtipoamostra); ?>
												</select>
											</td>
										</tr>
									</table>
								</div>
								<div class="panel-body">
									<table class="table table-striped planilha sortable" style="width: 100%; ">
										<?
										$_listarObjetosServico = PrativController::buscarPrativObjEEmpresaPorTipoEIdPrativ('prodserv', 'SERVICO', $_1_u_prativ_idprativ);
										$i++;
										if($_listarObjetosServico['qtdLinhas'] > 0) 
										{
											foreach($_listarObjetosServico['dados'] as $objetosServico) 
											{
												$i++;
												?>
												<tr>
													<td>
														<input type="hidden" name="_<?=$i ?>_<?=$_acao ?>_prativobj_ord" value="<?=$objetosServico["ord"] ?>">
														<input type="hidden" name="_<?=$i ?>_<?=$_acao ?>_prativobj_idprativobj" value="<?=$objetosServico["idprativobj"] ?>">
														<div class="row">
															<? $classmd = ($_1_u_prativ_status != 'APROVADO') ? 'col-md-11' : 'col-md-12'; ?>
															<div class="<?=$classmd ?>">
																<label class="idbox"><?=$objetosServico['descr'] ?> <a class="fa fa-bars pointer fade" target="_blanck" href="?_modulo=prodserv&_acao=u&idprodserv=<?=$objetosServico["idobjeto"] ?>"></a></label>
															</div>
															<div class="col-md-1" style="left: -11%;">
																<? if ($_1_u_prativ_status != 'APROVADO') { ?>
																	<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluiitem(<?=$objetosServico['idprativobj'] ?>)" alt="Excluir item!"></i>
																<? } ?>
															</div>
														</div>
													</td>
												</tr>
												<?
											}
										} //if($qtdrowitemteste>0){	
										?>
										<tr>
											<td>
												<input type="text" id="prativ_prodserv" placeholder="Selecione um teste" <?=$disasbled ?>>
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
									<table id="informacao-especifica" class="table table-striped planilha sortable enable-sortable" style="width: 100%;">
										<?
										$_listarAtividades = PrativController::buscarATividadesPorIdPrativETipoObjeto('ctrlproc', $_1_u_prativ_idprativ);
										$qtdrowitem = mysqli_num_rows($resitem);
										$i++;
										if($_listarAtividades['qtdLinhas'] > 0) 
										{
											foreach($_listarAtividades['dados'] as $atividade) 
											{
												$i++;
												$ico = empty($arrOpcoesInputManual[$atividade["inputmanual"]]["icone"]) ? "fa fa-eye-slash cinzaclaro" : $arrOpcoesInputManual[$atividade["inputmanual"]]["icone"];
												?>
												<tr>
													<td class="d-flex">
														<? if ($_1_u_prativ_status != 'APROVADO') { ?>
															<i class="hoververde pointer <?=$ico ?>" title="Mostrar quadro para check manual ou espaço para escrita manual" idprativobj="<?=$atividade["idprativobj"] ?>" inputmanual="<?=$atividade["inputmanual"] ?>" onclick="alteraInputmanual(this)"></i>
														<? } else { ?>
															<i class="hoververde pointer <?=$ico ?>" title="Mostrar quadro para check manual ou espaço para escrita manual" idprativobj="<?=$atividade["idprativobj"] ?>" inputmanual="<?=$atividade["inputmanual"] ?>"></i>
														<? } ?></i>
														<input type="hidden" name="_<?=$i ?>_<?=$_acao ?>_prativobj_ord" value="<?=$atividade["ord"] ?>">
														<input type="hidden" name="_<?=$i ?>_<?=$_acao ?>_prativobj_idprativobj" value="<?=$atividade["idprativobj"] ?>">
														<? if ($_1_u_prativ_status != 'APROVADO') { ?>
															<!-- Descricao -->
															<textarea id="desc-input-<?=$i ?>" type="text" name="_<?=$i ?>_<?=$_acao ?>_prativobj_descr" disabled data-id="<?=$atividade["idprativobj"] ?>" rows="1"><?=$atividade['descr'] ?></textarea>
															<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable pl-4" onclick="excluiitem(<?=$atividade['idprativobj'] ?>)" alt="Excluir item!" title="Excluir item!"></i>
															<i class="fa fa-arrows cinzaclaro hover move px-2 py-3" title="Mover item"></i>
															<i class="fa fa-edit cinzaclaro hovercinza pointer edit-info-espec" data-inputid="desc-input-<?=$i ?>" title="Editar item"></i>
														<? } else { ?>
															<?=$atividade['descr'] ?>
														<? } ?>
													</td>
												</tr>
												<?
											}
										} //if($qtdrowitem>0){	
										?>
										<tr>
											<td>
												<input <?=$readonly ?> class="ctrlproc" name="prativobj1" type="text" idprativ="<?=$_1_u_prativ_idprativ ?>" onchange="geraprativobj(this)">
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
									<table class="table table-striped planilha sortable enable-sortable" style="width: 100%; ">
										<?
										$_listarMateriais = PrativController::buscarATividadesPorIdPrativETipoObjeto('materiais', $_1_u_prativ_idprativ);
										$qtdrowitem = mysqli_num_rows($resitem);
										$i++;
										if ($_listarMateriais['qtdLinhas'] > 0) 
										{
											foreach($_listarMateriais['dados'] as  $materiais) 
											{
												$i++;
												$ico = empty($arrOpcoesInputManual[$materiais["inputmanual"]]["icone"]) ? "fa fa-eye-slash cinzaclaro" : $arrOpcoesInputManual[$materiais["inputmanual"]]["icone"];
												?>
												<tr>
													<td class="d-flex">
														<? if ($_1_u_prativ_status != 'APROVADO') { ?>
															<i class="hoververde pointer <?=$ico ?>" title="Mostrar quadro para check manual ou espaço para escrita manual" idprativobj="<?=$materiais["idprativobj"] ?>" inputmanual="<?=$materiais["inputmanual"] ?>" onclick="alteraInputmanual(this)"></i>
														<? } else { ?>
															<i class="hoververde pointer <?=$ico ?>" title="Mostrar quadro para check manual ou espaço para escrita manual" idprativobj="<?=$materiais["idprativobj"] ?>" inputmanual="<?=$materiais["inputmanual"] ?>"></i>
														<? } ?>
														<input type="hidden" name="_<?=$i ?>_<?=$_acao ?>_prativobj_ord" value="<?=$materiais["ord"] ?>">
														<input type="hidden" name="_<?=$i ?>_<?=$_acao ?>_prativobj_idprativobj" value="<?=$materiais["idprativobj"] ?>">
														<? if ($_1_u_prativ_status != 'APROVADO') { ?>
															<!-- Descricao -->
															<textarea id="desc-input-<?=$i ?>" type="text" name="_<?=$i ?>_<?=$_acao ?>_prativobj_descr" disabled data-id="<?=$materiais["idprativobj"] ?>" rows="1"><?=$materiais['descr'] ?></textarea>
															<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable pl-4" onclick="excluiitem(<?=$materiais['idprativobj'] ?>)" alt="Excluir item!" title="Excluir item!"></i>
															<i class="fa fa-arrows cinzaclaro hover move px-2 py-3" title="Mover item"></i>
															<i class="fa fa-edit cinzaclaro hovercinza pointer edit-info-espec" data-inputid="desc-input-<?=$i ?>" title="Editar item"></i>
														<? } else { ?>
															<?=$materiais['descr'] ?>
														<? } ?>
													</td>
												</tr>
												<?
											} 
										} //if($qtdrowitem>0){	
										?>
										<tr>
											<td>
												<input <?=$readonly ?> class="materiais" name="prativobj1" type="text" idprativ="<?=$_1_u_prativ_idprativ ?>" onchange="geraprativobjm(this)">
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
					<?
				} //if($_1_u_prativ_idprativ){
				?>
			</div>
		</div>
	</div>
</div>
<?
if (!empty($_1_u_prativ_idprativ)) 
{
	$_listarProcessosAtividades = PrativController::buscarProcessosLigadosAtividade($_1_u_prativ_idprativ);
	if ($_listarProcessosAtividades['qtdLinhas'] > 0) 
	{
		?>
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">Processos</div>
					<div class="panel-body">
						<table class="planilha grade compacto">
							<tr align="Center">
								<th>Processos</th>
								<th colspan="2">Status</th>
							</tr>
							<?
							foreach($_listarProcessosAtividades['dados'] as $processos) 
							{
								?>
								<tr class="res">
									<td nowrap><?=$processos["proc"] ?></td>
									<td nowrap><?=$processos["status"] ?></td>
									<td nowrap><a class="fa fa-bars pointer hoverazul " title="Processo" onclick="janelamodal('?_modulo=prproc&_acao=u&idprproc=<?=$processos['idprproc']?>')"></a></td>
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
							<th><b>Revisado por</b></th>
							<th><b>Revisado em</b></th>
						</tr>
						<? if ($_1_u_prativ_status != 'APROVADO') { ?>
							<tr>
								<td style="width: 30%;">Versão: <?=$_1_u_prativ_versao ?>.0</td>
								<td style="width: 70%;"><TEXTAREA name="_1_<?=$_acao ?>_prativ_descr" vnulo><?=$_1_u_prativ_descr ?></TEXTAREA></td>
							</tr>
						<? } ?>
						<?
						$_listarHistorico = PrProcController::buscarObjetoPorTipoObjeto($_1_u_prativ_idprativ, 'prativ');
						foreach($_listarHistorico as $historico) 
						{
							$rc = unserialize(base64_decode($historico["jobjeto"]));
							?>
							<tr class="res">
								<td nowrap><a href="report/prativ.php?idprativ=<?=$historico['idobjeto'] ?>&versao=<?=$historico['versaoobjeto']?>" target="_blank">Versão: <?=$historico['versaoobjeto']?>.0</a></td>
								<td style="line-height: 1.5;"><?=nl2br($rc['prativ']['res']['descr']) ?></td>
								<td style="line-height: 1.5;"><?= nl2br($rc['prativ']['res']['alteradopor']) ?></td>
								<td style="line-height: 1.5;"><?= dmahms(nl2br($rc['prativ']['res']['alteradoem'])) ?></td>
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
} //if(!empty($_1_u_prativ_idprativ)){			

if (!empty($_1_u_prativ_idprativ)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_prativ_idprativ; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "prativ"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
require '../form/js/prativ_js.php';
?>
