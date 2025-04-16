<?
require_once("../inc/php/validaacesso.php");
require_once(__DIR__."/controllers/solfab_controller.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "solfab";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
	"idsolfab" => "pk"
);
$idlote = $_GET["idlote"];
$idunidade = getUnidadePadraoModulo($_GET["_modulo"]);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT * FROM solfab WHERE idsolfab = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

//Fornecer um checklist para orientação ao usuário
$iAgentesIsolados;
$iAgenteInclusos;

//Recupera o cliente po parâmetro GET em caso de insert
$_1_u_solfab_idpessoa = ($_acao == "i") ? $_GET["idpessoa"] : $_1_u_solfab_idpessoa;
if (!$_1_u_solfab_idpessoa) die("Idpessoa não informado");

$arrCliente = getObjeto("pessoa", $_1_u_solfab_idpessoa);

if ($_1_u_solfab_idlote) {
	$lidprodserv = traduzid("lote", "idlote", "idprodserv", $_1_u_solfab_idlote);
	$listarPessoasAdjacentes = SolfabController::buscarPessoasLigadasSolfabAdjacente($_1_u_solfab_idsolfab);
	
	$idpessoa_sf = $_1_u_solfab_idpessoa;
	if ($listarPessoasAdjacentes['data']['idadjacente']> 0) {
		if (!empty($listarPessoasAdjacentes['data']['idadjacente'])) {
			$idpessoa_sf .= ','.$listarPessoasAdjacentes['data']['idadjacente'];
		}
	}

	$arrLoteSF = array();
	if ($_1_u_solfab_status == "ABERTO") {
		$arrLoteSF = SolfabController::buscarLotesSolfab($idpessoa_sf, $lidprodserv);
	} else {
		$arrLoteSF = SolfabController::buscarLotesSolfab($idpessoa_sf, $lidprodserv, $_1_u_solfab_idsolfab);
	}
	$JarrLoteSF = $JSON->encode($arrLoteSF);
}

$arrSFAssociado = [];
$arrSFAssociado = getObjeto("solfab", $_1_u_solfab_idsolfab, "idsolfab");

if (!empty($_1_u_solfab_idpessoa)) 
{
	$arrAdj = SolfabController::buscarRepresentantes($_1_u_solfab_idpessoa);
	$jAdj = $JSON->encode($arrAdj);
}
if ($idlote) {
	$_1_u_solfab_idlote = $idlote;
}

?>
<style>
	.papel-cinza {
		background: #f3f3f3;
		margin-bottom: 20px;
		border-radius: 2px;
		display: inline-block;
		position: relative;
		padding: 0px 10px 10px 10px;
		box-shadow: 0 3px 6px rgb(0 0 0 / 16%), 0 3px 6px rgb(0 0 0 / 23%);
		width: 100%;
		min-width: 100%;
	}

	.dataCalendario {
		width: 90px !important;
	}

	.pd-right{padding-right: 20px;}
	.pd-left-top
	{
		padding-left: 15px;
		padding-top: 4px;
	}

	.text-white {font-size: 11px !important;}

	.pd-left-5{padding-left: 5px;}
</style>
<div class="row">
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="w-100 d-flex flex-wrap flex-between align-items-center">
				<!-- ID -->
				<div class="col-xs-1 col-md-1 form-group"> 
					<label for="" class="text-white">ID:</label> <br />
					<label class="alert-warning"><?=$_1_u_solfab_idsolfab ?></label>
					<input type="hidden" name="_1_<?=$_acao ?>_solfab_idpessoa" value="<?=$_1_u_solfab_idpessoa ?>">
					<input name="_1_<?=$_acao ?>_solfab_idsolfab" id="idsolfab" type="hidden" value="<?=$_1_u_solfab_idsolfab ?>" vnulo>
					<input name="_1_<?=$_acao ?>_solfab_idunidade" id="idsolfab" type="hidden" value="<?=$idunidade ?>" vnulo>
				</div>

				<!-- Lote -->
				<div class="col-xs-2 col-md-2 form-group">
					<label for="" class="text-white">Lote:</label><br />
					<? if (empty($_1_u_solfab_idlote)) { ?>
						<select name="_1_<?=$_acao ?>_solfab_idlote" vnulo>
							<option value=''></option>
							<? fillselect(SolfabController::buscarLotesComPessoasEPartidasNaoNulos(), $_1_u_solfab_idlote); ?>
						</select>
					<? } else {
						$linhaFor = SolfabController::buscarFormalizacaoPorIdLote($_1_u_solfab_idlote);
						?>
						<label class="alert-warning">
							<?= traduzid("lote", "idlote", "concat(partida,'/',exercicio)", $_1_u_solfab_idlote) ?>
							<a target="_blank" class="fa fa-bars pointer fade pd-left-5" href="?_modulo=formalizacao&_acao=u&idformalizacao=<?=$linhaFor['idformalizacao'] ?>" ></a>
						</label>
						<input name="_1_<?=$_acao ?>_solfab_idlote" type="hidden" value="<?=$_1_u_solfab_idlote ?>" vnulo>
					<?
					}
					?>
				</div>

				<!-- Nome Cliente -->
				<?
				if (!empty($_1_u_solfab_idlote)  and !empty($_1_u_solfab_idsolfab)) {
					?>
					<div class="col-xs-3 col-md-3 form-group">
						<label for="" class="text-white">Nome Cliente:</label><br />
						<label class="alert-warning">
							<?=$arrCliente['nome'] ?>
							<a target="_blank" class="fa fa-bars pointer fade pd-left-5" href="?_modulo=pessoa&_acao=u&idpessoa=<?=$arrCliente['idpessoa'] ?>" ></a>
						</label>						
					</div>
					<?
				}
				?>

				<!-- Data -->
				<div class="col-xs-1 col-md-1 form-group"> 
					<label for="" class="text-white">Data:</label> <br />
					<input autocomplete="off" type="text" name="_1_<?=$_acao ?>_solfab_data" title="Data" class="calendario dataCalendario" value="<?=$_1_u_solfab_data ?>">
				</div>

				<!-- Nº SEI -->
				<div class="col-xs-1 col-md-1 form-group"> 
					<label for="" class="text-white">Nº SEI:</label> <br />
					<input class="size7" name="_1_<?=$_acao ?>_solfab_nsei" type="text" value="<?=$_1_u_solfab_nsei ?>">
				</div>

				<!-- Status -->
				<div class="col-xs-2 col-md-2 form-group"> 
					<label for="" class="text-white">Status:</label> <br />
					<? if ($_acao == 'u') {
						$_1_u_solfab_status = $_1_u_solfab_status;
					} else {
						$_1_u_solfab_status = 'ABERTO';
					} ?>
					<input type="hidden" name="solfab_status_anterior" value="<?=$_1_u_solfab_status ?>">
					<input type="hidden" name="_1_<?=$_acao ?>_solfab_status" value="<?=$_1_u_solfab_status ?>">
					<span>
						<label class="alert-warning" id="statusButton">
							<? $rotulo = getStatusFluxo('solfab', 'idsolfab', $_1_u_solfab_idsolfab) ?>
							<label title="<?=$_1_u_solfab_status ?>" id="statusButton"><?= mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?> </label>
						</label>
					</span>
				</div>

				<!-- Data Aprovação -->
				<div class="col-xs-1 col-md-1 form-group"> 
					<label for="" class="text-white">Data Aprovação:</label> <br />
					<? if ($_1_u_solfab_status == 'APROVADO') { ?>
						<label class="alert-warning preto"><?=$_1_u_solfab_dataaprovacao ?></label>
					<? } else { ?>
						<input autocomplete="off" class="calendario" id="dataaprovacao" size="6" type="text" name="_1_<?=$_acao ?>_solfab_dataaprovacao" value="<?=$_1_u_solfab_dataaprovacao ?>">
					<? }  ?>
				</div>

				<!-- Impressão -->
				<div class="col-xs-1 col-md-1 form-group"> 
					<i title="Imprimir" class="fa fa-print pull-right fa-lg cinza hoverazul pd-right" onclick="janelamodal('report/solfabnova.php?idsolfab=<?=$_1_u_solfab_idsolfab ?>')"></i>
					<!--i title="Imprimir PDF" class="fa fa-file-pdf-o fa-lg vermelho pointer pd-left-top" onclick="janelamodal('report/solfabnova.php?idsolfab=<?//=$_1_u_solfab_idsolfab ?>&geraarquivo=Y')"></i></td-->
				</div>
			</div>
		</div>
		<?
		if (!empty($_1_u_solfab_idlote)  and !empty($_1_u_solfab_idsolfab)) 
		{
			?>
			<div class="panel-body">
				<?
				//Forçar o salvamento
				if (!empty($_1_u_solfab_idsolfab)) 
				{
					?>
					<div class="col-md-7">
						<?					 
						if ($_1_u_solfab_status == "ABERTO") {
						?>
							<div class="row">
								<div class="col-md-12">Propriedade Alvo:
									<input placeholder="Selecione para adicionar" type="text" name="pessoaadjacente" cbvalue="pessoaadjacente" value="" style="width: 30em;">
								</div>
							</div>
						<?
						}

						$listarPessoasSolfab = SolfabController::buscarNomePessoasLigadasSolfabAdjacente($_1_u_solfab_idsolfab);
						$a = 0;
						foreach ($listarPessoasSolfab as $pessoas) 
						{
							$a = $a + 1;
							?>
							<div class="row">
								<div class="col-md-12">Propriedade Alvo <?=$a ?>: <b><?=$pessoas['nome'] ?></b> &nbsp; &nbsp; &nbsp;<i class='fa fa-trash  cinzaclaro hoververmelho' onclick=excluiadj(<?=$pessoas['idsolfabadj'] ?>)></i></div>
							</div>
							<?
						}
						?>
						<div class="row">
							<div class="col-md-12">
								<hr>
							</div>
						</div>
						<div>
							<div class="col-md-12">Descritivo:</div>
						</div>

						<?
						if (empty($_1_u_solfab_descr)) {
							$_1_u_solfab_descr = "Ao &#13;Ministério da Agricultura, Pecuária e Abastecimento&#13;Superintendência Federal de Agricultura, Pecuária e Abastecimento em Minas Gerais - SFA/MG&#13;Serviço de Fiscalização de Insumos e Saúde Animal - SISA/DDA/SFA-MG&#13;Unidade Técnica Regional de Uberlândia-MG - UTRA/UBERLÂNDIA/SFA-MG";
						}
						?>
						<div class="row">
							<div class="col-md-12">
								<textarea name="_1_<?=$_acao ?>_solfab_descr" cols="120" rows="5" style="line-height: 14px;"><?=$_1_u_solfab_descr ?></textarea>
							</div>
						</div>

						<div class="">
							<div class="col-md-12">Espécie e nº de animais suscetíveis na propriedade:</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<input type="text" name="_1_<?=$_acao ?>_solfab_animsuscep" value="<?=$_1_u_solfab_animsuscep ?>">
							</div>
						</div>

						<div class="">
							<div class="col-md-12">Identificação e endereço das propriedades adjacentes:</div>
						</div>
						<?
						if (empty($_1_u_solfab_propad)) {
							$_1_u_solfab_propad = "Não se aplica.";
						}
						?>
						<div class="row">
							<div class="col-md-12">
								<textarea cols="120" name="_1_<?=$_acao ?>_solfab_propad" rows="5" style="line-height: 14px;"><?=$_1_u_solfab_propad ?></textarea>
							</div>
						</div>

						<div class="col-md-12">Espécie e nº de animais susceptíveis nas propriedades adjacentes:</div>
						<?
						if (empty($_1_u_solfab_animsuscepad)) {
							$_1_u_solfab_animsuscepad = "Não se aplica.";
						}
						?>
						<div class="row">
							<div class="col-md-12">
								<textarea cols="120" name="_1_<?=$_acao ?>_solfab_animsuscepad" rows="5" style="line-height: 14px;"><?=$_1_u_solfab_animsuscepad ?></textarea>
							</div>
						</div>
						<div class="col-md-12">Nº de doses por partida:</div>
						<div class="row">
							<div class="col-md-12">
								<input type="text" name="_1_<?=$_acao ?>_solfab_ndosespart" value="<?=$_1_u_solfab_ndosespart ?>">
							</div>
						</div>

						<div class="col-md-12">Nº de doses por propriedade:</div>
						<div class="row">
							<div class="col-md-12">
								<textarea cols="120" name="_1_<?=$_acao ?>_solfab_ndoses" rows="5" style="line-height: 14px;"><?=$_1_u_solfab_ndoses ?></textarea>
							</div>
						</div>
						<div class="col-md-12">Observação:</div>
						<div class="row">
							<div class="col-md-12">
								<textarea cols="120" name="_1_<?=$_acao ?>_solfab_observacao" rows="5" style="line-height: 14px;"><?=$_1_u_solfab_observacao ?></textarea>
							</div>
						</div>
						<div class="col-md-12">Observação(Interna):</div>
						<div class="row">
							<div class="col-md-12">
								<textarea cols="120" name="_1_<?=$_acao ?>_solfab_observacaoint" rows="5" style="line-height: 14px;"><?=$_1_u_solfab_observacaoint ?></textarea>
							</div>
						</div>
					</div>
					<div class="col-md-5">
						<table style="width: 100%;">
							<tr>
								<td colspan="2">
									<div class="papel hover ui-droppable" id="formSF" style="width: 100%;">
										<h5 class="cinzaclaro" style="white-space: nowrap">Nome Comercial da Vacina:</h5>
										<?
										$ListarDescr = SolfabController::BuscarDescrProdservVacina($_1_u_solfab_idlote);
										$fabricado = $ListarDescr['fabricado'];
										if(count($ListarDescr) > 0 && ($fabricado = 'Y'))
										{?>
											<table class="table table-striped">
											<tr>
												<th>Descrição:</th>
											</tr>
											<?
											foreach($ListarDescr as $decr)
											$descr_consulta = $decr['descrcurta'];
											$idprodserv_consulta = $decr['idprodserv'];
											{?>
												<tr>
													<td>
														<div class="input-group input-group-sm">
															<div class="input-group input-group-sm">
																<label class="alert-warning"><?=$descr_consulta ?></label>
																<span class="input-group-addon pointer hoverazul" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$idprodserv_consulta ?>')" title="Ver Produto">
																	<i class="fa fa-bars pointer"></i>
																</span>
															</div>
														</div>
													</td>
												</tr>
											<?
											}
											?>
											</table>
										<? } else {
											echo '<hr>Não possui vacina vinculada.<br /><br />';
										}
										?>
									</div>									
									<br>
								</td>
							</tr>
							<tr>
								<td>
									<?
									if ($_1_u_solfab_status == 'ABERTO') {
										listaLotesSF();
									}
									?>
								</td>
							</tr>
							<tr>
								<td style="width:100%;min-width:100%;" class="inlineblocktop">
									<?
									desenhaSF($_1_u_solfab_idsolfab);
									?>
								</td>
							</tr>
							<tr>
								<td style="width:100%;min-width:100%;" class="inlineblocktop">
									<?
									arquivostra($_1_u_solfab_idsolfab);
									?>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<div class="papel hover ui-droppable" id="formSF" style="width: 100%;">
										<h5 class="cinzaclaro" style="white-space: nowrap">Lotes da solicitação:</h5>
										<?
										$listarLotesSolfab = SolfabController::buscarLotesDeFormalizacaoPorIdSolfab($_1_u_solfab_idsolfab);
										if(count($listarLotesSolfab) > 0)
										{
											?>
											<table class="table table-striped">
												<tr>
													<th>Pedido - Status</th>
													<th>Formalização - Status</th>
												</tr>
												<?
												foreach ($listarLotesSolfab as $linha) 
												{
													$idlote_consulta = $linha['idlote'];
													$idnf_consulta = $linha['idnf'];
													$statusnf_consulta = $linha['rotulo'];
													$partida_consulta = $linha['partida']."/".$linha['exercicio'];
													$status_consulta = $linha['status'];
													$idformalizacao = traduzid('formalizacao', 'idlote', 'idformalizacao', $idlote_consulta);
													?>
													<tr>
														<td>
															<div class="input-group input-group-sm">
																<div class="input-group input-group-sm">
																	<label class="alert-warning"><?=$idnf_consulta ?> - <?=$statusnf_consulta ?></label>
																	<span class="input-group-addon pointer hoverazul" onclick="janelamodal('?_modulo=pedido&_acao=u&idnf=<?=$idnf_consulta ?>')" title="Ver Pedido">
																		<i class="fa fa-bars pointer"></i>
																	</span>
																</div>
															</div>
														</td>
														<td class="">
															<div class="input-group input-group-sm">
																<div class="input-group input-group-sm">
																	<label class="alert-warning"><?=$partida_consulta ?></a> - <?=$status_consulta ?></label>
																	<span class="input-group-addon pointer hoverazul" onclick="janelamodal('?_modulo=formalizacao&_acao=u&idformalizacao=<?=$idformalizacao ?>')" title="Ver OP">
																		<i class="fa fa-bars pointer"></i>
																	</span>
																</div>
															</div>
														</td>
													</tr>
												<? } ?>
											</table>
										<? } else {
											echo 'Não possui Lotes.<br /><br />';
										}
										?>
									</div>									
									<br>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<div class="cbupload" title="Clique ou arraste arquivos para cá" style="width:100%;height:100%;">
										<i class="fa fa-cloud-upload fonte18"></i>
									</div>
								</td>
							</tr>
						</table>
					</div>
					<?
				}
				?>
			</div>
		<? } ?>
	</div>
</div>

<?
if (!empty($_1_u_solfab_idsolfab)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_solfab_idsolfab; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "solfab"; //pegar a tabela do criado/alterado em antigo
$_disableDefaultDropzone = true;
require 'viewCriadoAlterado.php';

require_once('../form/js/solfab_js.php');
?>