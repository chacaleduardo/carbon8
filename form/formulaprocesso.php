<?
require_once("../inc/php/validaacesso.php");
require_once("../api/prodserv/index.php");
require_once(__DIR__ . "/controllers/fluxo_controller.php");
require_once(__DIR__ . "/controllers/formulaprocesso_controller.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

/*
 * Este procedimento leva mais de 10 segundos para ser finalizado. Portanto será instanciado somente caso esta página seja chamada com parâmetros adequados
 */
if ($_GET["_atualizaarvoreinsumos"] == "Y") {
	//armazenaConfiguracaoArvoreInsumos();
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
$pagsql = "SELECT p.*, u.flgpotencia FROM prodserv p LEFT JOIN unidadevolume u on u.un = p.un WHERE p.idprodserv = #pkid ";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$arrCadInsumos = FormulaProcessoController::buscarProdservPorTipoEStatusEIdEmpresa('ATIVO', 'PRODUTO');
$arrCadInsumosServico = FormulaProcessoController::buscarProdservPorTipoEStatusEIdEmpresa('ATIVO', 'SERVICO');

if (!empty($_1_u_prodserv_idprodserv) and (($_1_u_prodserv_tipo == 'SERVICO') or ($_1_u_prodserv_fabricado == "Y") or ($_1_u_prodserv_processado == "Y"))) 
{
    /*************************************************************************************************************************************
	 *															FÓRMULAS																 *
	 *																																	 *
	 ************************************************************************************************************************************/ ?>

	<link href="../form/css/formulaprocesso_css.css?_<?=date("dmYhms")?>" rel="stylesheet">
	<div id="contentmodal" class="row">
		<div class="col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#formulascollapse">Fórmulas -
					<? if (!empty($_1_u_prodserv_descrcurta)) {
						echo ($_1_u_prodserv_descrcurta);
					} else {
						echo ($_1_u_prodserv_descr);
					} ?>
				</div>
				<div id="formulascollapse" class="panel-body">
					<?
					$arrFormulas = FormulaProcessoController::listarProdservFormulaPlantel($_1_u_prodserv_idprodserv);
					$if = 0;
					$ifi = 0;
					$inativo = 0;
					$passou = 0;
					
					foreach ($arrFormulas as $idprodservformula => $form) 
					{
						$if++;
						if ($form['editar'] == 'Y' and ($form['status'] == "INICIO" || $form['status'] == "REVISAO" || $form['status'] == "AGUARDANDO")) {
							$disabled = '';
						} else {
							$disabled = 'disabled';
						}
						$ordem = $form["ordem"];

						if ($form["status"] == 'INATIVO' && $inativo == 0 && $passou == 0) 
						{
							$inativo = 1;
							$passou = 1;
							?>
							<div class="panel panel-default">
								<div class="panel-heading cabecalho pointer">
									<table class="pointer">
										<tr>
											<td class="col-md-11">Fórmulas Inativas
											<td>
											<td class="col-md-1"><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Mostrar Fórmulas Inativas" data-toggle="collapse" href="#formulainfo"></i></td>
										</tr>
									</table>
								</div>
								<div class="panel-body collapse" id="formulainfo" style="opacity: 70%;">
								<?
						}
						?>
						
						<div class="agrupamento" style="border-color:<?=$form["cor"]?>;">
							<div class="panel-default">
								<div align='CENTER' class="col-md-12 panel-heading">
									<div class="col-md-2">
										ID: <?=$idprodservformula ?>
									</div>
									<div class="col-md-3">
										<?
										$_listarBotao = FluxoController::getLayoutBotao($pagvalmodulo, $form['idprodservformula'], 'idprodservformula');
										foreach($_listarBotao as $botao) 
										{
											$checkededit = ($form['editar'] == 'Y') ? 'Aprovar' : 'Revisar';
											$classedit = ($form['editar'] == 'Y') ? 'btn btn-success btn-xs' : 'btn btn-primary btn-xs';
											$iconeditar = ($form['editar'] == 'Y') ? 'fa fa-check' : 'fa fa-refresh';
											if ($botao['statustipo'] == 'INICIO' || $botao['statustipo'] == 'REVISAO') {
												$editar = 'N';
											} else {
												$editar = 'Y';
											}
											?>
											<button assina="false" value="<?=$editar ?>" token="<?=$botao['statustipo']?>" type="button" style="margin-top: -2px; display: block;background:<?=$botao['cor']?>;color:<?=$botao['cortexto']?>" class="btn btn-xs fright botaofluxo" onclick="trocaedicao(this, <?=$if ?>, '<?=$form['versao']?>', '<?=$botao['statustipo']?>', <?=$botao['idfluxostatusf']?>)">
												<i class="<?=$iconeditar ?>"></i><?=$botao['botao']?>
											</button>
											<?
										}
										?>
									</div>
									<div class="col-md-4">
										<? $rotulo = getStatusFluxo('prodservformula', 'idprodservformula', $form['idprodservformula']) ?>
										<label class="alert-warning" title="<?=$form['status']?>" id="statusButton"><?=mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?> </label>
									</div>
									<div align='right' class="col-md-3">
										Versão : <?=$form["versao"]?>.0
									</div>
								</div>
							</div>
							<span class="input-group-addon" title="Editar">
								<?
								if ($_1_u_prodserv_tipo == "PRODUTO") 
								{
									if (empty($disabled)) 
									{
										?>
										<i class="fa fa-eraser pointer hoverazul pd-right-10" onclick="resetplantel(<?=$idprodservformula ?>)" title="Limpar espécie"></i>
										<?
									}
									$_listarPLantel = FormulaProcessoController::buscarPlantelPorIdObjetoETipoObjeto($_1_u_prodserv_idprodserv, 'prodserv');
									foreach($_listarPLantel as $plantel) 
									{
										if ($form["idplantel"] == $plantel["idplantel"]) {
											$checked = 'checked';
										} else {
											$checked = '';
										}
										?>
										<span class="mg-right-10">
											<input type="radio" <?=$disabled ?> name="optionespeciel<?=$if ?>" id="optionespeciel" value="<?=$plantel["idplantel"]?>" onclick="especieform(<?=$idprodservformula ?>,<?=$plantel['idplantel']?>);" <?=$checked ?>>
											<?=$plantel["plantel"]?>
										</span>										
										<?
									}
									?>
									<ul class="c">
										<?
										$valoritem = 0;
										$excel = '';
										$qtdfr = cprod::buscavalorprodformula($idprodservformula, 1, 'N');
										$qtdserv = cprod::buscavalorprodservservico($_1_u_prodserv_idprodserv, 'N');

										if(!empty($qtdserv)){
											$qtdfr= ( tratanumero($qtdserv) + tratanumero($qtdfr));
										}
										
										?>
									</ul>
									<? if (array_key_exists("vervalordolote", getModsUsr("MODULOS"))) { ?>
										<i class="fa fa-money hoverazul btn-lg pointer" title="<? echo number_format(tratanumero($qtdfr), 2, ',', '.'); ?>" onclick="mostraval(<?=$idprodservformula ?>)"></i>
									<? } ?>
									<? if (array_key_exists("vervalordolote2", getModsUsr("MODULOS"))) { ?>
										<i class="fa fa-money hoverazul btn-lg pointer" style="color:rgb(162, 47, 57) !important;" title="Relatório Hubio" onclick="mostraval2(<?=$idprodservformula ?>)"></i>
									<? } ?>
									<div>
										<?
										$rotulo = FormulaProcessoController::buscarFormulaRotuloPorIdProdservFormula($idprodservformula);
										if (!$rotulo) 
										{
											?>
											<button id="" type="button" style="margin-top:7px" class="btn btn-success btn-xs pull-left" onclick="janelamodal('?_modulo=formularotulo&_acao=i&idprodservformula=<?=$idprodservformula ?>');">
												<i class="fa fa-plus"></i>Rótulo
											</button>
											<?
										} else {
											?>
											<button id="" type="button" style="margin-top:7px" class="btn btn-primary btn-xs pull-left" onclick="janelamodal('?_modulo=formularotulo&_acao=u&idformularotulo=<?=$rotulo['idformularotulo']?>');">
												<i class="fa fa-bars "></i>Rótulo
											</button>
											<?
										}
										?>
										<button id="" type="button" class="btn btn-primary btn-xs pull-center" style="margin-top:7px" title="Lotes produzidos com a fórmula" onclick="janelamodal('report/_8repprint126.php?_modulo=loteproducao&_idrep=166&idprodservformula=<?=$idprodservformula ?>');">
											<i class="fa fa-bars pointer "></i>Lotes
										</button>
										<? if ($form["status"] == "INATIVO") { ?>
											<button id="" type="button" class="btn btn-warning btn-xs pull-right" style="margin-top:7px;margin-left: 10%;" title="Ativar fórmula" onclick="ativar('<?=$idprodservformula ?>');">
												<i class="fa fa-clone pointer "></i>Ativar
											</button>
										<? } ?>
										<button id="" type="button" class="btn btn-primary btn-xs pull-right" style="margin-top:7px" title="Duplicar fórmula e insumos" onclick="duplicar('<?=$idprodservformula ?>');">
											<i class="fa fa-clone pointer "></i>Duplicar
										</button>
									</div>
									<?
								} else {
									?>
									Fase <input <?=$disabled ?> type="text" name="_psf<?=$if ?>_u_prodservformula_ordem" style="width:30px;" class="form-control" value="<?=$form["ordem"]?>" placeholder="Fase" title="Fase" vdecimal onchange="CB.post()">
									<ul class="c">
										<?
										$valoritem = 0;
										$excel = '';
										$qtdfr = cprod::buscavalorprodformula($idprodservformula, 1, 'N');

										$qtdserv = cprod::buscavalorprodservservico($_1_u_prodserv_idprodserv, 'N');

										if(!empty($qtdserv)){
											$qtdfr= ( tratanumero($qtdserv) + tratanumero($qtdfr)) ;
										}
										?>
									</ul>
									<? if (array_key_exists("vervalordolote", getModsUsr("MODULOS"))) { ?>
										<i class="fa fa-money hoverazul btn-lg pointer" title="<? echo number_format(tratanumero($qtdfr), 2, ',', '.'); ?>" onclick="mostraval(<?=$idprodservformula ?>)"></i>
									<? } 
								}
								?>
							</span>
							<hr>
							<div>
								<input type="hidden" name="_psf<?=$if ?>_u_prodservformula_idprodservformula" value="<?=$idprodservformula ?>">
								<input type="hidden" name="_psf<?=$if ?>_u_prodservformula_cor" class="form-control" value="<?=$form["cor"]?>">
								<? if ($_1_u_prodserv_tipo == "PRODUTO") { ?>
									<table class="table table-striped planilha">
										<tr>
											<th>Qtd</th>
											<th>Descrição</th>
											<th><?=$_1_u_prodserv_conteudo ?></th>
											<th>Volume</th>
											<th>Un Vol</th>
											<th></th>
										</tr>
										<tr>
											<td class="nowrap">
												<input <?=$disabled ?> type="text" name="_psf<?=$if ?>_u_prodservformula_qtdpadraof" class="form-control size7 pd-right-10" value="<?=recuperaExpoente($form["qtdpadraof"], $form["qtdpadraof_exp"]) ?>" placeholder="Qtd" title="Qtd Padrão" vnulo>
												<?=$_1_u_prodserv_un ?>
											</td>
											<td>
												<input <?=$disabled ?> type="text" name="_psf<?=$if ?>_u_prodservformula_rotulo" class="form-control" value="<?=$form["rotulo"]?>" placeholder="Rótulo" title="Rótulo">
											</td>
											<td>
												<input <?=$disabled ?> type="text" name="_psf<?=$if ?>_u_prodservformula_dose" class="form-control size3" value="<?=$form["dose"]?>" placeholder="<?=$_1_u_prodserv_conteudo ?>" title="<?=$_1_u_prodserv_conteudo ?>">
											</td>
											<td>
												<input <?=$disabled ?> type="text" name="_psf<?=$if ?>_u_prodservformula_volumeformula" class="form-control size5" value="<?=$form["volumeformula"]?>" placeholder="Volume" title="Volume">
											</td>
											<td>
												<input <?=$disabled ?> type="text" name="_psf<?=$if ?>_u_prodservformula_un" class="form-control size3" value="<?=$form["un"]?>" placeholder="Unidade" title="Unidade">
											</td>
											<td>
												<? if ($form['editar'] == 'Y') { ?>
													<div class="input-group input-group-sm">
														<span class="input-group-addon pointer hoverazul dropdown-toggle" data-toggle="dropdown" title="Alterar cor" style="color:<?=$form["cor"]?>">
															<i class="fa fa-paint-brush colorpicker"></i>
														</span>
														<ul class="dropdown-menu">
															<li style="display:inline-block;">
																<div class="colorpalette"></div>
															</li>
														</ul>
														<span class="input-group-addon pointer hoververmelho" title="Inativar" onclick="inativarformulacao(<?=$idprodservformula ?>)"><i class="fa fa-trash"></i></span>
													</div>
												<? } ?>
											</td>
										</tr>
									</table>
								<? } else { ?>
									<? if ($form['editar'] == 'Y') { ?>
										<div class="input-group input-group-sm">
											<span class="input-group-addon pointer hoverazul dropdown-toggle" data-toggle="dropdown" title="Alterar cor" style="color:<?=$form["cor"]?>">
												<i class="fa fa-paint-brush colorpicker"></i>
											</span>
											<ul class="dropdown-menu">
												<li style="display:inline-block;">
													<div class="colorpalette"></div>
												</li>
											</ul>
											<span class="input-group-addon pointer hoververmelho" title="Excluir" onclick="inativarformulacao(<?=$idprodservformula ?>)"><i class="fa fa-trash"></i></span>
										</div>
									<? } ?>
								<? } ?>
							</div>

							<?
							$idtable = ($disabled == 'disabled') ? '' : 'tbinsumos';
							?>
							<table id="<?=$idtable ?>" class="table table-striped planilha">
								<tr>
									<th></th>
									<th>#</th>
									<th style="width: 15%;">Qtd</th>
									<th>Un.</th>
									<th>Insumo</th>
									<? if ($_1_u_prodserv_tipo != 'SERVICO') { ?>
										<th style="width: 15%;">F.P.</th>
									<? } ?>
									<? if ($_1_u_prodserv_tipo == 'SERVICO') { ?>
										<th>Resultado</th>
									<? } ?>
									<th></th>
									<th></th>
								</tr>
								<?
								$countItemFormula = 1;
								foreach ($form["prodservformulains"] as $idprodservformulains => $formins) 
								{
									$ifi++;
									$stackoverflow = ($formins["idprodserv"] == $_1_u_prodserv_idprodserv) ? "" : "hidden"; //Caso o filho seja igual o pai, entrará no modo recursivo infinito até causar stackoverflow
									$insprodserv = getObjeto("prodserv", $formins["idprodserv"], "idprodserv")
									?>
									<tr class="dragInsumo" prod_prodservformula="<?=$idprodservformula ?>" idprodservformulains="<?=$formins['idprodservformulains']?>">
										<td title="Ordenar insumos/Relacionar com Atividades">
											<? if ($form['editar'] == "Y") { ?>
												<i class="fa fa-arrows cinzaclaro hover move"></i>
											<? } ?>
										</td>
										<td class="fonte08"><b><?=$countItemFormula++?></b></td>
										<td>
											<input <?=$disabled ?> type="text" name="_ifi<?=$ifi ?>_u_prodservformulains_qtdi" value="<?=recuperaExpoente($formins["qtdi"], $formins["qtdi_exp"]) ?>" <? if ($_1_u_prodserv_tipo == 'PRODUTO') { ?> vnulo<? } ?> class="fonte11">
										</td>
										<td>
											<?
											echo ($arrCadInsumos[$formins["idprodserv"]]["un"]);
											?>
										</td>
										<td>
											<input type="hidden" name="_ifi<?=$ifi ?>_u_prodservformulains_idprodservformulains" value="<?=$formins["idprodservformulains"]?>">
											<input type="hidden" name="_ifi<?=$ifi ?>_u_prodservformulains_ord" value="<?=$formins["ord"]?>">
											<input <?=$disabled ?> type="text" name="_ifi<?=$ifi ?>_u_prodservformulains_idprodserv" title="<?=$arrCadInsumos[$formins["idprodserv"]]["codprodserv"] . " #" . $formins["idprodserv"]?>" cbvalue="<?=$formins["idprodserv"]?>" class="fonte08 prodservformulains_idprodserv">
										</td>

										<? if ($_1_u_prodserv_tipo != 'SERVICO') { ?>
											<td>
												<input <?=$disabled ?> type="text" name="_ifi<?=$ifi ?>_u_prodservformulains_qtdpd" value="<?=recuperaExpoente($formins["qtdpd"], $formins["qtdpd_exp"]) ?>" class="fonte08">
											</td>
										<? } ?>

										<? if ($_1_u_prodserv_tipo == 'SERVICO') {
											if ($formins["listares"] == 'Y') {
												$cor = 'verdeclaro hoververde';
												$lita = 'Y';
											} else {
												$cor = 'laranja hoververmelho ';
												$lita = 'N';
											}
											?>
											<td>
												<a class=" fa fa-eye btn-lg pointer <?=$cor ?> " title='Aparece no resultado.' listares="<?=$lita ?>" idprodservformulains="<?=$formins["idprodservformulains"]?>" <? if ($disabled == '') {echo 'onclick="Alteralistares(this)"';} ?>></a>
											</td>
										<? } ?>
										<td>
											<i class="fa fa-bars fa-1x pointer cinzaclaro hoverazul" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$formins["idprodserv"]?>')" title="Editar insumo #<?=$formins["idprodserv"]?>"></i>
										</td>
										<td>
											<? if ($disabled == '') { ?>
												<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer" onclick="inativarinsumo(<?=$idprodservformulains ?>)" title="Excluir prodservformulains #<?=$idprodservformulains ?>"></i>
											<? }
											$stackoverflowAvoNeto = ($insumosAvoNeto == true && in_array($formins["idprodserv"], $arrayProduto)) ? "" : "hidden";
											?>
											<i class="fa fa-2x fa-ban vermelho blink <?=$stackoverflow ?>" title="Erro: Insumo não pode estar relacionado com ele mesmo"></i>
											<i class="fa fa-2x fa-ban vermelho blink hidden insumoinativo" title="Erro: Insumo Inativo"></i>
											<i class="fa fa-2x fa-ban vermelho blink <?=$stackoverflowAvoNeto?> insumorelprimeironivel" title="Erro: Insumo não pode estar relacionado com insumo de primeiro nível"></i>
											
										</td>
									</tr>
									<?
								}
								
								if ($form['editar'] == 'Y') { ?>
									<tr>
										<td colspan="1"></td>
										<td colspan="99">
											<i class="fa fa-plus-circle fa-2x  cinzaclaro hoververde pointer" onclick="novoinsumo(<?=$idprodservformula ?>,<?=$ifi + 1; ?>)" title="Inserir insumo"></i>
										</td>
									</tr>
								<? } ?>
							</table>
							<?
							$_litarResversao = FormulaProcessoController::buscarVersaoObjetoPorTipoObjeto($idprodservformula, 'prodservformula');
							if (count($_litarResversao) > 0) { ?>
								<div class="panel-default">
									<div data-toggle="collapse" href="#versaocollapse<?=$idprodservformula ?>" class="panel-heading">Versões</div>
									<div class="panel-body collapse" id="versaocollapse<?=$idprodservformula ?>">
										<table class="table table-striped planilha">
											<tr>
												<th>Versão</th>
												<th>Alterado Por</th>
												<th>Alterado Em</th>
												<th></th>
											</tr>
											<?
											foreach($_litarResversao as $reversao) { ?>
												<tr>
													<td><?='Versão '.$reversao['versaoobjeto'].'.0' ?></td>
													<td><?=$reversao['alteradopor']?></td>
													<td><?=dma($reversao['alteradoem'])?></td>
													<td>
														<i class="fa fa-bars pointer hoverazul" onclick="janelamodal('report/relversaoprodservform.php?idprodservformula=<?=$idprodservformula ?>&versao=<?=$reversao['versaoobjeto']?>')"></i>
													</td>
												</tr>
											<? } ?>
										</table>
									</div>
								</div>
							<? } ?>
						</div>

						<?
						if ($_1_u_prodserv_tipo == "PRODUTO" or $_1_u_prodserv_tipo == "SERVICO") {							
							?>
							<div id="htmlmodal_<?=$idprodservformula; ?>" style="display: none">
								<div id="valaorform<?=$idprodservformula; ?>" style="display: none">
									<div class="row">
										<div class="col-md-12">
											<div class="panel panel-default">
												<div class="panel-heading">
													<?=recuperaExpoente($form["qtdpadraof"], $form["qtdpadraof_exp"]) ?> <?=$_1_u_prodserv_un ?>
													<? if (!empty($_1_u_prodserv_descrcurta)) {
														echo ($_1_u_prodserv_descrcurta);
													} else {
														echo ($_1_u_prodserv_descr);
													} ?>
												</div>
												<div class="panel-body">
													<div class="col-md-12" style="font-weight: bold; font-size: 11px; border-bottom: 1px solid #ddd; border-top: 1px solid #ddd; background-color: #f0f0f0;">
														<div class="col-md-1">#</div>
														<div class="col-md-2">
															<span>
																<i class="fa" style="padding: 5px 12px;"></i>
															</span>
															Qtd
														</div>	
														<div class="col-md-1">Un</div>
														<div class="col-md-5">Descrição</div>	
														<div class="col-md-1" style="text-align: right;">Valor Un</div>
														<div class="col-md-2" style="text-align: right;">Total</div>										
													</div>
													<?
													$excel = '';
													cprod::buscavalorprodformula($idprodservformula, 1, 'Y');
													$valoritem = 0;
													$qtdf = cprod::buscavalorprodformula($idprodservformula, 1, 'N');
													?>
													<div class="col-md-12" style="font-weight: bold; font-size: 11px; border-bottom: 1px solid #ddd; border-top: 1px solid #ddd; background-color: #f0f0f0;">
														<div class="col-md-10" style="text-align: right;">
															Insumos R$ 
														</div>
														<div class="col-md-2">
															<span style="float:right" title="R$: ${valLote}">
																<? echo number_format(tratanumero($qtdf), 4, ',', '.'); ?>
															</span>
														</div>
													</div>
													<?
													$valoritem = 0;
													$excel .= 
													'<tr>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
													</tr>';   
													cprod::buscavalorprodservservico($_1_u_prodserv_idprodserv,'Y');								
													$qtdserv = cprod::buscavalorprodservservico($_1_u_prodserv_idprodserv, 'N');

													if(!empty($qtdserv)){
														$qtdtotal= ( tratanumero($qtdserv) + tratanumero($qtdf)) ;
													?>
													<div class="col-md-12" style="font-weight: bold; font-size: 11px; border-bottom: 1px solid #ddd; border-top: 1px solid #ddd; background-color: #f0f0f0;">
														<div class="col-md-10" style="text-align: right;">
															Serviços R$ 
														</div>
														<div class="col-md-2">
															<span style="float:right" title="R$: ${valLote}">
																<? echo number_format(tratanumero($qtdserv), 4, ',', '.'); ?>
															</span>
														</div>
													</div>
													<div class="col-md-12" style="font-weight: bold; font-size: 11px; border-bottom: 1px solid #ddd; border-top: 1px solid #ddd; background-color: #f0f0f0;">
														<div class="col-md-10" style="text-align: right;">
															TOTAL R$ 
														</div>
														<div class="col-md-2">
															<span style="float:right" title="R$: ${valLote}">
																<? echo number_format(tratanumero($qtdtotal), 4, ',', '.'); ?>
															</span>
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
						<?
						}

						?>
						<?
						if ($_1_u_prodserv_tipo == "PRODUTO" or $_1_u_prodserv_tipo == "SERVICO") {							
						?>
							
							<div id="htmlmodal_<?=$idprodservformula; ?>_duplicados" style="display: none">
								<div id="valaorformclone<?=$idprodservformula; ?>_duplicados" style="display: none">
									<div class="row">
										<div class="col-md-12">
											<div class="panel panel-default">
												<div class="panel-heading">
													<?=recuperaExpoente($form["qtdpadraof"], $form["qtdpadraof_exp"]) ?> <?=$_1_u_prodserv_un ?>
													<? if (!empty($_1_u_prodserv_descrcurta)) {
														echo ($_1_u_prodserv_descrcurta);
													} else {
														echo ($_1_u_prodserv_descr);
													} ?>
												</div>
												<div class="panel-body">
													<div class="col-md-12" style="font-weight: bold; font-size: 11px; border-bottom: 1px solid #ddd; border-top: 1px solid #ddd; background-color: #f0f0f0;">
														<div class="col-md-1">#</div>
														<div class="col-md-1">
															<span>
																<i class="fa" style="padding: 5px 12px;"></i>
															</span>
															Qtd
														</div>	
														<div class="col-md-1">Un</div>	
														<div class="col-md-4">Descrição</div>	
														<div class="col-md-1" style="text-align: right;">Empresa</div>
														<div class="col-md-1" style="text-align: right;">Prodserv</div>
														<div class="col-md-1" style="text-align: right;">Valor Un</div>														
														<div class="col-md-1" style="text-align: right;">Total</div>										
													</div>
													<?
													$excelClone = '';
													cprod::buscavalorprodformula2($idprodservformula, 1, 'Y');
													$valoritem = 0;
													$qtdf = cprod::buscavalorprodformula2($idprodservformula, 1, 'N');
													?>
													<div class="col-md-12" style="font-weight: bold; font-size: 11px; border-bottom: 1px solid #ddd; border-top: 1px solid #ddd; background-color: #f0f0f0;">
														<div class="col-md-10" style="text-align: right;">
															Insumos R$ 
														</div>
														<div class="col-md-2">
															<span style="float:right" title="R$: ${valLote}">
																<? echo number_format(tratanumero($qtdf), 4, ',', '.'); ?>
															</span>
														</div>
													</div>
													<?
													$valoritem = 0;
													$excelClone .= '<tr>
													<td> </td>
													<td> </td>
													<td> </td>
													<td> </td>
													<td> </td>
													<td> </td>
													<td> </td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td> </td>
													<td> </td>
													<td> </td>
													<td> </td>
												</tr>';   
													cprod::buscavalorprodservservico2($_1_u_prodserv_idprodserv,'Y');
														
													$qtdserv = cprod::buscavalorprodservservico2($_1_u_prodserv_idprodserv, 'N');

													if(!empty($qtdserv)){
														$qtdtotal= ( tratanumero($qtdserv) + tratanumero($qtdf)) ;
													?>
													<div class="col-md-12" style="font-weight: bold; font-size: 11px; border-bottom: 1px solid #ddd; border-top: 1px solid #ddd; background-color: #f0f0f0;">
														<div class="col-md-10" style="text-align: right;">
															Serviços R$ 
														</div>
														<div class="col-md-2">
															<span style="float:right" title="R$: ${valLote}">
																<? echo number_format(tratanumero($qtdserv), 4, ',', '.'); ?>
															</span>
														</div>
													</div>
													<div class="col-md-12" style="font-weight: bold; font-size: 11px; border-bottom: 1px solid #ddd; border-top: 1px solid #ddd; background-color: #f0f0f0;">
														<div class="col-md-10" style="text-align: right;">
															TOTAL R$ 
														</div>
														<div class="col-md-2">
															<span style="float:right" title="R$: ${valLote}">
																<? echo number_format(tratanumero($qtdtotal), 4, ',', '.'); ?>
															</span>
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
						<?
						}

						?>
						<table id="_tableresult<?=$idprodservformula; ?>" class="hidden">
							<tr>
								<td>#</td>
								<td>ID Prodserv</td>
								<td>QTD</td>
								<td>Un</td>
								<td>Descrição</td>
								<td>Empresa</td>
								<td>Prodserv</td>
								<td>Valor Un</td>
								<td>Total</td>
							</tr>
							<?=$excel;?>
						</table>

						<table id="_tableresultClone<?=$idprodservformula; ?>" class="hidden">
							<tr>
								<td>#</td>
								<td>ID Prodserv</td>
								<td>QTD</td>
								<td>Un</td>
								<td>Descrição</td>
								<!-- <td>Empresa</td>
								<td>Prodserv</td> -->
								<td>Categoria</td>
								<td>Subcategoria</td>
								<td>Fornecedor</td>
								<td>Cidade do Fornecedor</td>
								<td>Estado (UF) do Fornecedor</td>
								<td>Data da última compra</td>
								<td>NFe</td>
								<td>Chave NFe</td>
								<td>Valor Un</td>
								<td>Total</td>
							</tr>
							<?=$excelClone;?>
						</table>
						<?

					}
					if ($inativo == 1) {
							?>
							</div>
						</div>
						<?
					}
					$ordem++;
					?>
					<div class="agrupamento novo">
						<i class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer" onclick="novaformulacao(<?=$ordem ?>,<?=$_1_u_prodserv_idprodserv ?>,<?=$_1_u_prodserv_idunidadeest ?>)" title="Nova Formulação"></i>
					</div>

				</div>
			</div>
			<? if (!empty($idprodservformula)) 
			{
				$_listarHistoricoInsumos =  FormulaProcessoController::buscarInsumosServicoEmAndamento($_1_u_prodserv_idprodserv, $idprodservformula, 'ATIVO', 'INATIVO');
				$qtdins = count($_listarHistoricoInsumos);
				if ($qtdins > 0) 
				{ ?>
					<div class="row">
						<div class="col-sm-12" style="padding-left: 17px !important; padding-right: 17px !important;">
							<div class="panel panel-default">
								<div class="panel-heading">Histórico de insumos inativados</div>
								<div class="panel-body">
									<table class="table table-striped planilha">
										<tr>
											<th>Insumo</th>
											<th>Status</th>
											<th>Ultima alteração</th>
											<th>Alterado por</th>
										</tr>
										<? foreach($_listarHistoricoInsumos as $historico) { ?>
											<tr>
												<td><?=$historico['descr']?> <a title="Abrir cadastro produto" class="fa fa-bars fade pointer hoverazul" href="?_modulo=prodserv&_acao=u&idprodserv=<?=$_1_u_resultado_idtipoteste ?>" target="_blank"></a></td>
												<td><?=$historico['status']?></td>
												<td><?=dmahms($historico['alteradoem']) ?> </td>
												<td> <?=$historico['alteradopor']?></td>
											</tr>
										<? } ?>
									</table>
								</div>
							</div>
						</div>
					</div>
					<? 
				}
			} ?>

		</div>

		<? /***********************************************************************************************************************************
		 *															PROCESSOS																 *
		 ************************************************************************************************************************************/ ?>
		<div class="col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#processocollapse">Processo</div>
				<div id="processocollapse" class="panel-body">
					<?
					$_listarProcessos = FormulaProcessoController::buscarProcessosPorIdProdserv($_1_u_prodserv_idprodserv);
					foreach($_listarProcessos as $processos) 
					{
						$i = $i + 1;
						?>
						<table style="width: 100%;border: 1px dashed;border-color: #959795;">
							<tr>
								<td>
									<input name="_<?=$i ?>_u_prodservprproc_idprodservprproc" type="hidden" size="4" value="<?=$processos["idprodservprproc"]?>">
									<div class="input-group input-group-sm">
										<select name="_<?=$i ?>_u_prodservprproc_idprproc" class="form-control">
											<option value=""></option>
											<? fillselect(FormulaProcessoController::buscarFillSelectProcessosPorTipoEIdEmpresa($_1_u_prodserv_tipo, cb::idempresa()), $processos["idprproc"]); ?>
										</select>
										<? if (!empty($processos["idprproc"])) { ?>
											<span class="input-group-addon pointer hoverazul" title="Editar">
												<i class="fa fa-bars" onclick="janelamodal('?_modulo=prproc&_acao=u&idprproc=<?=$processos['idprproc']?>')"></i>
											</span>
										<? } else { ?>
											<span class="input-group-addon pointer hoverazul" title="Inserir">
												<i class="fa fa-plus-circle" onclick="janelamodal('?_modulo=prproc&_acao=i')"></i>
											</span>
										<? } ?>
										<span class="input-group-addon pointer hoververmelho" title="Excluir">
											<i class="fa fa-trash" onclick="excluiratividade(<?=$processos["idprodservprproc"]?>)"></i>
										</span>
									</div>
									<br>
									<b style="color: #666;">&nbsp; Versão Fórmula/Processo: <?=$processos['versao']?>.0</b>
									<br>
									<ul class="ulatividades">
										<?
										if (!empty($processos['idprproc'])) 
										{
											$_listarAtividades = FormulaProcessoController::buscarProcessosPorTipoEIdEmpresa($processos['idprproc']);
											foreach($_listarAtividades as $atividade) 
											{
											?>
												<li class="liatividade soltavel" idprodservprproc="<?=$processos['idprodservprproc']?>" idprativ="<?=$atividade['idprativ']?>">
													<div>
														<h5 class="bold cinza" title="<?=$atividade['idprativ']?>"><?=$atividade['ativ']?></h5>
													</div>
													<ul>
														<?
														$_listarInsumosAtividades = FormulaProcessoController::buscarInsumosAtividadeNaoAtivos($atividade['idprativ'], $processos['idprodservprproc']);
														foreach ($_listarInsumosAtividades as $insumosAtividades) 
														{
															$i = $i + 1;
															$stackoverflow = ($insumosAtividades["idprodserv"] == $_1_u_prodserv_idprodserv) ? "" : "hidden"; //Caso o filho seja igual o pai, entrará no modo recursivo infinito até causar stackoverflow
															$stackoverflowAvoNeto = ($insumosAvoNeto == true && !in_array($insumosAtividades["idprodserv"], $arrayProduto)) ? "" : "hidden";
															?>
															<li class="liinsumo">
																<small><i class="fa fa-1x fa-circle cinzaclaro" style="color:<?=$insumosAtividades["cor"]?>;"></i></small>

																<?=$insumosAtividades['descr']?>
																<input type="hidden" name="_<?=$i ?>_u_procprativinsumo_idprocprativinsumo" proc_idprodservformula="<?=$insumosAtividades["idprodservformula"]?>" size="4" value="<?=$insumosAtividades['idprocprativinsumo']?>">
																<span class="label label-default"><?=recuperaExpoente($insumosAtividades["qtdi"], $insumosAtividades["qtdi_exp"]) ?></span>
																<?
																if ($insumosAtividades['editar'] == "Y") { ?>
																	<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluirprocprativinsumo(<?=$insumosAtividades['idprocprativinsumo']?>)" alt="Excluir"></i>
																<? } ?>
																<i class="fa fa-2x fa-ban vermelho blink <?=$stackoverflow ?>" title="Erro: Insumo não pode estar relacionado com ele mesmo"></i>													
															</li>
														<?
														}
														?>
													</ul>
												</li>
												<?
											} 
										} 
										?>
									</ul>
									<?
									$_litarResversao = FormulaProcessoController::buscarVersaoObjetoPorTipoObjeto($processos['idprodservprproc'], 'prodservprproc');
									if (count($_litarResversao) > 0) { ?>
										<div class="panel-default">
											<div data-toggle="collapse" href="#versaocollapsep<?=$processos['idprodservprproc']?>" class="panel-heading">Versões</div>
											<div class="panel-body collapse" id="versaocollapsep<?=$processos['idprodservprproc']?>">
												<table class="table table-striped planilha">
													<tr>
														<th>Versão</th>
														<th>Alterado Por</th>
														<th>Alterado Em</th>
														<th></th>
													</tr>
													<?
													foreach($_litarResversao as $resversao) { ?>
														<tr>
															<td><?='Versão '.$resversao['versaoobjeto'].'.0' ?></td>
															<td><?=$resversao['alteradopor']?></td>
															<td><?=dma($resversao['alteradoem'])?></td>
															<td>
																<i class="fa fa-bars pointer hoverazul" onclick="janelamodal('report/prodservprproc.php?idprodservprproc=<?=$processos['idprodservprproc']?>&versao=<?=$resversao['versaoobjeto']?>')"></i>
															</td>
														</tr>
													<? } ?>
												</table>
											</div>
										</div>

									<? } ?>
								</td>
							</tr>
						</table>
						<br>
					<?
					}
					?>

					<i class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer" onclick="novoatividade(<?=$_1_u_prodserv_idprodserv ?>)" title="Relacionar processo"></i>
				</div>
			</div>
		</div>

		<? /***********************************************************************************************************************************
		 *													    Lotes Vinculados Serviço													 *
		 ************************************************************************************************************************************/ ?>
		<div class="col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#lotesvinculadosservicocollapse">Serviço(s) Vinculado(s) -
					<? if (!empty($_1_u_prodserv_descrcurta)) {
						echo ($_1_u_prodserv_descrcurta);
					} else {
						echo ($_1_u_prodserv_descr);
					} ?>
				</div>
				<div id="lotesvinculadosservicocollapse" class="panel-body">
					<?
					$arrProdservServico = FormulaProcessoController::listarProdservLoteServicoPlantel($_1_u_prodserv_idprodserv);
					$if = 0;
					$ifi = 0;
					$inativoLoteServico = 0;
					$passouLoteServico = 0;
					foreach ($arrProdservServico as $idprodservloteservico => $formLoteServico) 
					{
						$if++;
						if ($formLoteServico['editar'] == 'Y' && ($formLoteServico['status'] == "INICIO" || $formLoteServico['status'] == "REVISAO" || $formLoteServico['status'] == "AGUARDANDO")) {
							$disabled = '';
						} else {
							$disabled = 'disabled';
						}
						$ordem = $formLoteServico["ordem"];

						if ($formLoteServico["status"] == 'INATIVO' && $inativoLoteServico == 0 && $passouLoteServico == 0) 
						{
							$inativoLoteServico = 1;
							$passouLoteServico = 1;
							?>
							<div class="panel panel-default">
								<div class="panel-heading cabecalho pointer">
									<table class="pointer">
										<tr>
											<td class="col-md-11">Serviço(s) Vinculado(s) Inativo(s)
											<td>
											<td class="col-md-1"><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Mostrar Serviço(s) Vinculado(s) Inativo(s)" data-toggle="collapse" href="#formulainfoservico"></i></td>
										</tr>
									</table>
								</div>
								<div class="panel-body collapse" id="formulainfoservico" style="opacity: 70%;">
								<?
						}
						?>
						
						<div class="agrupamento" style="border-color:<?=$formLoteServico["cor"]?>;">
							<div class="panel-default">
								<div align='CENTER' class="col-md-12 panel-heading">
									<div class="col-md-4">
										<?
										$_listarBotao = FluxoController::getLayoutBotao('loteservico', $formLoteServico['idprodservloteservico'], 'idprodservloteservico');
										foreach($_listarBotao as $botao) 
										{
											$checkededit = ($formLoteServico['editar'] == 'Y') ? 'Aprovar' : 'Revisar';
											$classedit = ($formLoteServico['editar'] == 'Y') ? 'btn btn-success btn-xs' : 'btn btn-primary btn-xs';
											$iconeditar = ($formLoteServico['editar'] == 'Y') ? 'fa fa-check' : 'fa fa-refresh';
											if ($botao['statustipo'] == 'INICIO' || $botao['statustipo'] == 'REVISAO') {
												$editar = 'N';
											} else {
												$editar = 'Y';
											}
											$versao = (empty($formLoteServico['versao'])) ? 0 : $formLoteServico['versao'];
											?>
											<button assina="false" value="<?=$editar ?>" token="<?=$botao['statustipo']?>" type="button" style="margin-top: -2px; display: block;background:<?=$botao['cor']?>;color:<?=$botao['cortexto']?>" class="btn btn-xs fright botaofluxo" onclick="trocaedicaoLoteServico(this, <?=$if ?>, '<?=$formLoteServico['versao']?>', '<?=$botao['statustipo']?>', <?=$botao['idfluxostatusf']?>)">
												<i class="<?=$iconeditar ?>"></i><?=$botao['botao']?>
											</button>
											<?
										}
										?>
									</div>
									<div class="col-md-5">
										<? $rotulo = getStatusFluxo('prodservloteservico', 'idprodservloteservico', $formLoteServico['idprodservloteservico']) ?>
										<label class="alert-warning" title="<?=$formLoteServico['status']?>" id="statusButton"><?=mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?> </label>
									</div>
									<div align='right' class="col-md-3">
										Versão : <?=$formLoteServico["versao"]?>.0
									</div>
								</div>
							</div>
							<br />
							<hr>
							<div>
								<input type="hidden" name="_pls<?=$if ?>_u_prodservloteservico_idprodservloteservico" value="<?=$idprodservloteservico ?>">
								<input type="hidden" name="_pls<?=$if ?>_u_prodservloteservico_cor" class="form-control" value="<?=$formLoteServico["cor"]?>">
								<? if ($_1_u_prodserv_tipo == "PRODUTO") { ?>
									<table class="table table-striped planilha">
										<tr>
											<th>Tipo Amostra</th>
											<th></th>
										</tr>
										<tr>
											<td class="nowrap">
												<select name="_pls<?=$if?>_u_prodservloteservico_idsubtipoamostra" class="form-control">
													<option value=""></option>
													<? fillselect(FormulaProcessoController::listarFillSelectSubtipoamostraPorIdEmpresa(), $formLoteServico["idsubtipoamostra"]); ?>
												</select>
											</td>											
											<td>
												<? if ($formLoteServico['editar'] == 'Y') { ?>
													<div class="input-group input-group-sm">
														<ul class="dropdown-menu">
															<li style="display:inline-block;">
																<div class="colorpalette"></div>
															</li>
														</ul>
														<span class="input-group-addon pointer hoververmelho" title="Inativar" onclick="inativarFormulacaoLoteServico(<?=$idprodservloteservico ?>)"><i class="fa fa-trash"></i></span>
													</div>
												<? } ?>
											</td>
										</tr>
									</table>
								<? } else { ?>
									<? if ($formLoteServico['editar'] == 'Y') { ?>
										<div class="input-group input-group-sm">
											<span class="input-group-addon pointer hoverazul dropdown-toggle" data-toggle="dropdown" title="Alterar cor" style="color:<?=$formLoteServico["cor"]?>">
												<i class="fa fa-paint-brush colorpicker"></i>
											</span>
											<ul class="dropdown-menu">
												<li style="display:inline-block;">
													<div class="colorpalette"></div>
												</li>
											</ul>
											<span class="input-group-addon pointer hoververmelho" title="Excluir" onclick="inativarFormulacaoLoteServico(<?=$idprodservloteservico ?>)"><i class="fa fa-trash"></i></span>
										</div>
									<? } ?>
								<? } ?>
							</div>

							<?
							$idtable = ($disabled == 'disabled') ? '' : 'tbinsumos';
							?>
							<table id="<?=$idtable ?>" class="table table-striped planilha">
								<tr>
									<th style="padding-left: 10px;">#</th>
									<th style="width: 15%;">Qtd</th>
									<th>Serviço</th>									
									<th></th>
									<th>R$ Un</th>
									<th></th>
								</tr>
								<?
								$countItemFormula = 1;
								$valor_serv=0;
								foreach ($formLoteServico["prodservloteservico"] as $idprodservloteservicoins => $formLoteServicoins) 
								{
									$servico_valor = FormulaProcessoController::buscarValorServico($formLoteServicoins["idprodserv"]);
									if(empty($servico_valor['vlrun'])){
										$vlrvenda=FormulaProcessoController::buscarValorServicoVenda($formLoteServicoins["idprodserv"]);	
										$servico_valor['vlrun']=$vlrvenda['vlrvenda'];
									}
									$valor_serv=$valor_serv+($servico_valor['vlrun']*$formLoteServicoins["qtdi"]);
									$ifi++;
									$stackoverflow = ($formLoteServicoins["idprodserv"] == $_1_u_prodserv_idprodserv) ? "" : "hidden"; //Caso o filho seja igual o pai, entrará no modo recursivo infinito até causar stackoverflow
									$insprodserv = getObjeto("prodserv", $formLoteServicoins["idprodserv"], "idprodserv")
									?>
									<tr class="dragInsumo" prod_prodservformula="<?=$idprodservloteservico ?>" idprodservloteservicoins="<?=$formLoteServicoins['idprodservloteservicoins']?>">
										<td class="fonte08" style="padding-left: 10px;"><b><?=$countItemFormula++?></b></td>
										<td>
											<input <?=$disabled ?> type="text" name="_plsi<?=$ifi ?>_u_prodservloteservicoins_qtdi" value="<?=recuperaExpoente($formLoteServicoins["qtdi"], $formLoteServicoins["qtdi_exp"]) ?>" <? if ($_1_u_prodserv_tipo == 'PRODUTO') { ?> vnulo<? } ?> class="fonte11">
										</td>
										<td>
											<input type="hidden" name="_plsi<?=$ifi ?>_u_prodservloteservicoins_idprodservloteservicoins" value="<?=$formLoteServicoins["idprodservloteservicoins"]?>">
											<input type="hidden" name="_plsi<?=$ifi ?>_u_prodservloteservicoins_ord" value="<?=$formLoteServicoins["ord"]?>">
											<input <?=$disabled ?> type="text" name="_plsi<?=$ifi ?>_u_prodservloteservicoins_idprodserv" title="<?=$arrCadInsumos[$formLoteServicoins["idprodserv"]]["codprodserv"] . " #" . $formLoteServicoins["idprodserv"]?>" cbvalue="<?=$formLoteServicoins["idprodserv"]?>" class="fonte08 prodservloteservicoins_idprodserv">
										</td>										
										<td>
											<i class="fa fa-bars fa-1x pointer cinzaclaro hoverazul" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$formLoteServicoins["idprodserv"]?>')" title="Editar insumo #<?=$formLoteServicoins["idprodserv"]?>"></i>
										</td>
										<td>
											<?if($servico_valor['tiponf']=='R'){ $modulo='comprasrh';}else{ $modulo='nfentrada';}?>
											<a class="pointer" onclick="janelamodal('?_modulo=<?=$modulo?>&_acao=u&idnf=<?=$servico_valor['idnf']?>')" title="Compra">
												<?=number_format(tratanumero($servico_valor['vlrun']), 2, ',', '.');?>
											</a>
										</td>
										<td>
											<? if ($disabled == '') { ?>
												<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer" onclick="inativarinsumoLoteServico(<?=$idprodservloteservicoins ?>)" title="Excluir prodservloteservicoins #<?=$idprodservloteservicoins ?>"></i>
											<? } 
											$stackoverflowAvoNeto = ($insumosAvoNeto == true && !in_array($formLoteServicoins["idprodserv"], $arrayProduto)) ? "" : "hidden";
											?>
											<i class="fa fa-2x fa-ban vermelho blink <?=$stackoverflow ?>" title="Erro: Insumo não pode estar relacionado com ele mesmo"></i>
											<i class="fa fa-2x fa-ban vermelho blink hidden insumoinativo" title="Erro: Insumo Inativo"></i>
											<i class="fa fa-2x fa-ban vermelho blink <?=$stackoverflowAvoNeto?> insumorelprimeironivel" title="Erro: Insumo não pode estar relacionado com insumo de primeiro nível"></i>
										</td>
									</tr>
									<?
								}
								?>
								<tr>
										
										<td colspan="4"><b>Total</b></td>
										<td><b><?=number_format(tratanumero($valor_serv), 2, ',', '.');?></b></td>
										<td >
											
										</td>
									</tr>
								<?
								if ($formLoteServico['editar'] == 'Y') { ?>
									<tr>
										<td colspan="1"></td>
										<td colspan="1"></td>
									
										<td colspan="99">
											<i class="fa fa-plus-circle fa-2x  cinzaclaro hoververde pointer" onclick="novoInsumoLoteServico(<?=$idprodservloteservico ?>,<?=$ifi + 1; ?>)" title="Inserir insumo"></i>
										</td>
									</tr>
								<? } ?>
							</table>
							<?
							$_litarResversao = FormulaProcessoController::buscarVersaoObjetoPorTipoObjeto($idprodservloteservico, 'prodservloteservico');
							if (count($_litarResversao) > 0) { ?>
								<div class="panel-default">
									<div data-toggle="collapse" href="#versaocollapse<?=$idprodservloteservico ?>" class="panel-heading">Versões</div>
									<div class="panel-body collapse" id="versaocollapse<?=$idprodservloteservico ?>">
										<table class="table table-striped planilha">
											<tr>
												<th>Versão</th>
												<th>Alterado Por</th>
												<th>Alterado Em</th>
												<th></th>
											</tr>
											<?
											foreach($_litarResversao as $reversao) { ?>
												<tr>
													<td><?='Versão '.$reversao['versaoobjeto'].'.0' ?></td>
													<td><?=$reversao['alteradopor']?></td>
													<td><?=dma($reversao['alteradoem'])?></td>
													<td>
														<i class="fa fa-bars pointer hoverazul" onclick="janelamodal('report/relversaoprodservform.php?idprodservformula=<?=$idprodservloteservico ?>&versao=<?=$reversao['versaoobjeto']?>')"></i>
													</td>
												</tr>
											<? } ?>
										</table>
									</div>
								</div>
							<? } ?>
						</div>

						<?
						if ($_1_u_prodserv_tipo == "PRODUTO" or $_1_u_prodserv_tipo == "SERVICO") {
						?>
							<div id="htmlmodal_<?=$idprodservloteservico; ?>" style="display: none">
								<div id="valaorform<?=$idprodservloteservico; ?>" style="display: none">
									<div class="row">
										<div class="col-md-12">
											<div class="panel panel-default">
												<div class="panel-heading">
													<?=recuperaExpoente($formLoteServico["qtdpadraof"], $formLoteServico["qtdpadraof_exp"]) ?> <?=$_1_u_prodserv_un ?>
													<? if (!empty($_1_u_prodserv_descrcurta)) {
														echo ($_1_u_prodserv_descrcurta);
													} else {
														echo ($_1_u_prodserv_descr);
													} ?>
												</div>
												<div class="panel-body">
													<div class="col-md-12" style="font-weight: bold; font-size: 11px; border-bottom: 1px solid #ddd; border-top: 1px solid #ddd; background-color: #f0f0f0;">
														<div class="col-md-1">#</div>
														<div class="col-md-2">
															<span>
																<i class="fa" style="padding: 5px 12px;"></i>
															</span>
															Qtd
														</div>	
														<div class="col-md-1">Un</div>	
														<div class="col-md-5">Descrição</div>	
														<div class="col-md-1" style="text-align: right;">Valor Un</div>
														<div class="col-md-2" style="text-align: right;">Total</div>										
													</div>
													<?
													$excel = '';
													cprod::buscavalorprodformula($idprodservloteservico, 1, 'Y');
													$valoritem = 0;
													$qtdf = cprod::buscavalorprodformula($idprodservloteservico, 1, 'N');
													?>
													<div class="col-md-12" style="font-weight: bold; font-size: 11px; border-bottom: 1px solid #ddd; border-top: 1px solid #ddd; background-color: #f0f0f0;">
														<div class="col-md-10" style="text-align: right;">
															Insumos R$ 
														</div>
														<div class="col-md-2">
															<span style="float:right" title="R$: ${valLote}">
																<? echo number_format(tratanumero($qtdf), 2, ',', '.'); ?>
															</span>
														</div>
													</div>
													<?
													$valoritem = 0;
													cprod::buscavalorprodservservico($_1_u_prodserv_idprodserv,'Y');
													$valoritem = 0;
													$qtdserv = cprod::buscavalorprodservservico($_1_u_prodserv_idprodserv, 'N');

													if(!empty($qtdserv)){
														$qtdtotal= ( tratanumero($qtdserv) + tratanumero($qtdf)) ;
													?>
													<div class="col-md-12" style="font-weight: bold; font-size: 11px; border-bottom: 1px solid #ddd; border-top: 1px solid #ddd; background-color: #f0f0f0;">
														<div class="col-md-10" style="text-align: right;">
															Serviços R$ 
														</div>
														<div class="col-md-2">
															<span style="float:right" title="R$: ${valLote}">
																<? echo number_format(tratanumero($qtdserv), 2, ',', '.'); ?>
															</span>
														</div>
													</div>
													<div class="col-md-12" style="font-weight: bold; font-size: 11px; border-bottom: 1px solid #ddd; border-top: 1px solid #ddd; background-color: #f0f0f0;">
														<div class="col-md-10" style="text-align: right;">
															TOTAL R$ 
														</div>
														<div class="col-md-2">
															<span style="float:right" title="R$: ${valLote}">
																<? echo number_format(tratanumero($qtdtotal), 2, ',', '.'); ?>
															</span>
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
						<?
						}
					}
					if ($inativoLoteServico == 1) {
							?>
							</div>
						</div>
						<?
					}
					$ordem++;
					?>
					<div class="agrupamento novo">
						<i class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer" onclick="novaFormulacaoLoteServico(<?=$ordem ?>,<?=$_1_u_prodserv_idprodserv ?>,<?=$_1_u_prodserv_idunidadeest ?>)" title="Nova Formulação"></i>
					</div>

				</div>
			</div>
		</div>
	</div>
<?
}

require_once('../form/js/formulaprocesso_js.php'); 
?>