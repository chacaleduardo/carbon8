<?
require_once("../inc/php/validaacesso.php");
require_once("controllers/rateioitemdest_controller.php");
require_once("controllers/empresa_controller.php");
require_once("../model/prodserv.php");
require_once("../model/nf.php");
require_once("../api/nf/index.php");




//Chama a Classe prodserv
$prodservclass = new PRODSERV();
$nfclass = new NF();

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$idtipoprodserv = $_GET["idtipoprodserv"];
$funcao = $_GET["funcao"];
$tipo = $_GET["tipo"];
$idrateioitemdest = $_GET['idrateioitemdest'];
$stidrateioitemdest = $_GET['stidrateioitemdest'];
$_idnf = $_GET['idnf'];
$_idnfitem = $_GET['idnfitem'];

if (empty($_GET["_idempresa"])) {
	$idempresa = cb::idempresa();
} else {
	$idempresa = $_GET["_idempresa"];
}

?>
<link href="../form/css/rteioitemdest_css.css?_<?=date("dmYhms")?>" rel="stylesheet">
<?
if ($tipo == 'rateio') 
{
	if (!empty($stidrateioitemdest)) {
		$clausula .= "  and dt.idrateioitemdest in(" . $stidrateioitemdest . ") ";
		$clausulac .= "  and dt.idrateioitemdest in(" . $stidrateioitemdest . ")";
		$clausulax .= "  and dt1.idrateioitemdest in(" . $stidrateioitemdest . ") ";
	} else {
		die("Não informado o destinos do rateio");
	}

	//consulta dos sem rateio para enviar para abrir no modal de edição
	$_nfSemRateio = RateioItemDestController::buscarNfSemRateio('nfitem', 'nfitem', 'unidade', "'T','S','M','E','R','D','B','C','O'", "'CANCELADO','REPROVADO'", $clausula);
	echo "<!-- ".$_nfSemRateio['sql']." -->";
	?>
	<div class="row" id="formulario">
		<div class="col-md-8">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading cabecalho" style="height: 32px;">
						ITENS DO RATEIO
						<div style="float: right;">
						<?if($funcao!="COBRAR"){?>
							<button id="cbrestaurar" type="button" class="btn btn-primary btn-xs" onclick="restaurartodosdest()" title="Limpar">
								<i class="fa fa-arrow-circle-left"></i>Limpar
							</button>	
						<?}?>				
						</div>
					</div>
					<div class="panel-body">
						<div><i>Selecione os iten(s) para edição do rateio.</i></div>
						<div>
							<input placeholder="Filtrar Itens do Rateio" class="size20" style="height: 22px;" type="text" id="inputFiltro2"> 
						</div>
						
						<div class="table table-striped planilha panel panel-default" style="width:100%;font-size:9px;">
							<div class="col-md-12 row rowcab panel-heading" style="margin:0px; font-size:9px;">
								<div class="col-md-6">
									<div class="col-md-1"><input type="checkbox" name="marcardesmarcar"  checked class="pointer" title="Marcar/Desmarcar todos" onclick="selecionar(this,'inputcheckbox')"></div>
									<div class="col-md-2">QTD</div>
									<div class="col-md-2">UN</div>
									<div class="col-md-7">ITEM</div>
								</div>
								<div class="col-md-1 text-al-r">VLR. R$</div>
								<div class="col-md-2 text-al-r">DESTINO</div>
								<div class="col-md-1 text-al-r">RATEIO %</div>
								<div class="col-md-1 text-al-r">DETALHES</div>
								<div class="col-md-1 text-al-r"></div>					

							</div>
							<?
							$i = 0;
							$total = 0;
							$semrateio='N';
							foreach($_nfSemRateio['dados'] as $_semRateio)
							{
								$i = $i + 1;
								$total = $total + $_semRateio['rateio'];

								if ($_semRateio['tipoobjeto'] == 'unidade') {
									$rateio = $_semRateio['empresa'];
									$rateiostr= $_semRateio['empresa'];
								} /*elseif ($_semRateio['tipoobjeto'] == 'sgdepartamento') {
									$rateio = RateioItemDestController::buscarDepartamentoSgDepartamentoPorIdSgDepartamento($_semRateio['idobjeto']);
								} elseif ($_semRateio['tipoobjeto'] == 'pessoa') {
									$rateio = RateioItemDestController::buscarPessoaPorIdPessoa($_semRateio['idobjeto']);
								} elseif ($_semRateio['tipoobjeto'] == 'empresa') {
									$rateio = RateioItemDestController::buscarEmpresaPorIdEmpresa($_semRateio['idobjeto']);
								}*/ else {
									$semrateio='Y';
									$rateio = "<font color='red'>Sem rateio</font>";
									$rateiostr="Sem rateio";
								}

								//Itens relacionados	
								?>
								<div class="col-md-12 row rowitem itemrateio" title="Rateio selecionado na tela Rateio Item" style="margin:0px;" data-text="<?=$_semRateio['descr']?> <?=$rateiostr?>">
									<div class="col-md-6 inputcheckbox">
										<div class="col-md-1">
										<?if(!empty($_semRateio['idrateioitemdestnf']) and $_semRateio['status'] =='COBRADO'){?>
										<i class="fa fa-money verde pointer" title="Rateio em Cobrança" onclick="editardestnf(<?=$_semRateio['idrateioitemdest']?>,'<?=$_semRateio['descr']?>','<?=$rateiostr?>')" ></i>										
										<div class="hide" id="destnf<?=$_semRateio['idrateioitemdest']?>">
											<table class="table table-striped planilha">
												<tr>
													<th>Cobrança %</th>
													<th>Valor R$</th>
													<th>Nome</th>
													<th>Status</th>
												</tr>
												<?
												$cobranca=RateioItemDestController::listarRateioitemdestnfPorIdrateioitemdest($_semRateio['idrateioitemdest']);
												$totalrt=0;
												$deletar='Y';
												foreach($cobranca as $linha) {
													$totalrt=$totalrt+$linha['valor'];
													$rotulo = getStatusFluxo('nf', 'idnf', $linha['idnf']);
													if( $linha['status'] != 'INICIO' and   $linha['status'] !='ABERTO' ){
														$deletar='N';
													}
												?>
												<tr>
													<td><?=number_format(tratanumero($linha['rateio']), 2, ',', '.');?></td>
													<td><?=number_format(tratanumero($linha['valor']), 2, ',', '.');?></td>
													<td><?=$linha['nome']?></td>
													<td>
														<a target="_blank" href="?_modulo=nfentrada&_acao=u&idnf=<?=$linha['idnf']?>"><?= mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?></a>
													</td>
												</tr>
												<?
												}
												?>
												<tr>
													<th>Total</th>
													<th><?=number_format(tratanumero($totalrt), 2, ',', '.'); ?></th>
													<th></th>
													<th style="text-align-last: center;" >
														<?if($deletar=='Y'){?>
															<i title="Excluir os Itens" class="fa fa-trash vermelho hoverpreto pointer" onclick="excluirdestnf('<?=$_semRateio['idrateioitemdestnf']?>')"></i>
														<?}?>
													</th>
												</tr>
											</table>
										
										</div>
										<?}elseif($_semRateio['status'] =='PENDENTE' OR empty($_semRateio['status']) ){?>
											<input type="checkbox" checked class="changeacao" acao="i" atname="checked[<?=$i ?>]" value="<?=$_semRateio['idrateioitemdest'] ?>" style="border:0px">
											<input class="rateioitem" name="_<?=$i ?>_u_rateioitemdest_idrateioitemdest" type="hidden" value="<?=$_semRateio['idrateioitemdest'] ?>">
											<input class="rateioitem" name="_<?=$i ?>_u_rateioitemdest_idrateioitem" type="hidden" value="<?=$_semRateio['idrateioitem'] ?>">
										<?
										}else{
										?>
										<span title="Por: <?=$_semRateio['alteradopor']?> Em:  <?=dmahms($_semRateio['alteradoem'])?>">
											<?=$_semRateio['status']?>
										</span>	
										<?
										}
										?>										
										</div>
										<div class="col-md-2"><?=$_semRateio['qtd'] ?></div>
										<div class="col-md-2"><?=$_semRateio['un'] ?></div>
										<div class="col-md-7"><?=$_semRateio['descr'] ?> </div>
									</div>
									<div class="col-md-1 " style="text-align: right;">

										<? if (!empty($_semRateio['idnf'])) { ?>
											<a class="hoverazul pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?=$_semRateio['idnf'] ?>')" title="Compra">
												<?=number_format(tratanumero($_semRateio['rateio']), 2, ',', '.'); ?>
											</a>
										<? } else { ?>
											<?=number_format(tratanumero($_semRateio['rateio']), 2, ',', '.'); ?>
										<? } ?>

									</div>
									<div class="col-md-2" >
										<?
										if (!empty($_semRateio['idpessoa'])) {
											echo traduzid("pessoa", "idpessoa", "nome", $_semRateio['idpessoa']);
										}
										
										echo $rateio;
										?>
									</div>
									<div class="col-md-1" style="text-align: right;">
										<?=$_semRateio['valor'] ?>%
									</div>
									<div class="col-md-1 nowrap" style="text-align: center;">
										<?
										if ($_semRateio['tipo'] == 'nfitem') { ?>											
											<a title="Compra" class="fa fa-search fa-1x hoverazul pointer" onclick="showhistoricoitem(<?=$_semRateio['idtipo'] ?>);"></a>
										<? } ?>
									</div>
									<div class="col-md-1 nowrap" style="text-align: right;">
										<? if ($_semRateio['valor'] < 100 and $_semRateio['valor'] !=0) { ?>
											<i class="fa fa-arrows-v fa-1x cinzaclaro pointer" title="Ocultar/Desocultar <?=$qtdx ?> iten(s) relacionado(s)" data-toggle="collapse" href="#col<?=$_semRateio['idrateioitem'] ?>" onclick="carregasub(<?=$_semRateio['idrateioitem'] ?>,<?=$_semRateio['idrateioitemdest'] ?>)"></i>
										<? } ?>
									</div>
								</div>
								<div class="collapse" id="col<?=$_semRateio['idrateioitem'] ?>"></div>
								<?
							}
							?>
							<div class="col-md-12 row rowitem"  style="margin:0px; font-size:9px;background:#ddd;font-weight:bold;">
								<div class="col-md-6">TOTAL:</div>
								<div class="col-md-1 " style="text-align: right;">
									<?=number_format(tratanumero($total), 2, ',', '.'); ?>
								</div>
								<div class="col-md-2" style="text-align: right;"></div>
								<div class="col-md-1" style="text-align: right;"></div>
								<div class="col-md-1" style="text-align: right;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
		    <? if($semrateio=='N' AND $funcao =="COBRAR") { ?>
			<div class="panel panel-default">
				<div class="panel-heading cabecalho" style="height: 32px;">
					COBRANÇA
					<div style="float: right;">
						<button id="cbalterar" type="button" class="btn btn-success btn-xs hidden" onclick="alterartodos('Y')" title="Salvar">
							<i class="fa fa-circle"></i>Salvar
						</button>
						<button id="cbalterar2" style="border-color:#5cb85c1f !important; background-color:#5cb85c1f !important;" type="button" class="btn btn-success btn-xs" onclick="alert('É necessário completar o valor de 100% para o rateio.')" title="Salvar">
							<i class="fa fa-circle"></i>Salvar
						</button>
					</div>
				</div>
				<div class="panel-body">
					<div class="col-md-12 row">
						<div><i>Insira o(s) percentual(ais) para o(s) destino(s) desejado(s) abaixo, mínimo 100%.</i></div>
						<br>
						<table class="table table-striped planilha" id="tbrateio" style="font-size:9px;text-transform:uppercase;">
							<thead>
								<tr class="rowcab">
							
									<td style="width:30%;">
									Total Divisão % <br/>
										<span id="totalvalor" style="color: red;"></span>								
									</td>
									<td style="width:70%">
									
										
									</td>
								</tr>
								<tr class="rowcab">
									<td colspan="2">
										<input placeholder="Filtrar" style="height: 22px; width: 90%;" type="text" id="inputFiltroempresa"> 
									</td>
								</tr>
							</thead>
							<tbody>
								<?
								
								$listarEmpresa = EmpresaController::listarEmpresasAtivasComcobranca($idempresa);
								$li = 10;
								?>
								<tr class="rowcab unidade" style="background:#ddd;">
									<th colspan="3" style="height: 40px;">
										<i class="fa fa-building" style="color:#291c1c;font-size:9px;"></i> Empresa(S)
									</th>
								</tr>
								<?
								foreach($listarEmpresa as  $empresa) 
								{
									$li = $li + 1;
								?>
									<tr class="empresa" style="width:100%;" data-text="<?=$empresa['empresa']?>">								
										<td style="width:20%">
											
										<? if(!empty($empresa['idpessoaform']) and $empresa['conf']>0){
										$fontc="";		
										$title="";
										?>
											<input type="text"  name="_<?=$li ?>#valor" class="valorrateio" style="width:60px; font-size:9px; border: 1px solid #cccccc !important;" vnumero="" onkeyup="calcular(this)">
										<?}else{ 
										 $fontc="red";	
										 $title="Configurar no cadastro desta empresa a pessoa empresa relacionada, fatura automática, Categoria e Subcategoria";
										?>
											<input title="<?=$title?>" type="text" disabled='disabled'  name="_<?=$li ?>#valor" class="valorrateio" style="width:60px; font-size:9px; border: 1px solid #cccccc !important;" vnumero="" onkeyup="calcular(this)">
										<?}?>
										</td>
										<td style="width:80%" title=<?=$title?>>
									
											<font color="<?=$fontc?>">									
											<?=$empresa['empresa'] ?> 
											</font>
											
										
											<input type="hidden" name="_<?=$li ?>#idempresa" class="idempresa" value="<?=$empresa['idempresa']?>,'empresa'">
											<span style="background: rgb(102, 102, 102);font-size: 9px;color: #fff;padding: 0px 6px;border-radius: 3px; display:none">
												Empresa
											</span>
										</td>
										<td class="nowrap">
										<?if(!empty($empresa['idnf'])){?>
												<a class="hoverazul pointer" onclick="janelamodal('?_modulo=rateioitemdestnf&_acao=u&idnf=<?=$empresa['idnf']?>')" title="Cobrança aberta">
													R$ <?=number_format($empresa['valor'], 2, '.', '');?>
												</a>
											<?}else{echo("R$ 0.00");}
											?>
										</td>
										
									</tr>

								<?
								}
								?>								
							</tbody>

						</table>
					</div>
				</div>
			</div>
			<? }else{ ?>
			<div class="panel panel-default">
				<div class="panel-heading cabecalho" style="height: 32px;">
					RATEIO INTERNO
					<div style="float: right;">
						<button id="cbalterar" type="button" class="btn btn-success btn-xs hidden" onclick="alterartodos('N')" title="Salvar">
							<i class="fa fa-circle"></i>Salvar
						</button>
						<button id="cbalterar2" style="border-color:#5cb85c1f !important; background-color:#5cb85c1f !important;" type="button" class="btn btn-success btn-xs" onclick="alert('É necessário completar o valor de 100% para o rateio.')" title="Salvar">
							<i class="fa fa-circle"></i>Salvar
						</button>
					</div>
				</div>
				<div class="panel-body">
					<div class="col-md-12 row">
						<div><i>Insira o(s) percentual(ais) para o(s) destino(s) desejado(s) abaixo, mínimo 100%.</i></div>
						<br>
						<table id="tbrateio" style="font-size:9px;text-transform:uppercase;">
							<thead>
								<tr class="rowcab">
							
									<td style="width:30%;">
										Rateio % <br/>
										<span id="totalvalor" style="color: red;"></span>								
									</td>
									<td style="width:70%">
										<i style="margin-top: 8px;" title="Ratear por numero de funcionários" class="fa fa-building pointer" onclick="calculafunc(this)"></i>
										<i title="Mostrar funcionários" class="fa fa-group pointer btn-lg" onclick="mostrarpessoa(this)"></i>
									</td>
								</tr>
								<tr class="rowcab">
									<td colspan="2">
										<input placeholder="Filtrar" style="height: 22px; width: 90%;" type="text" id="inputFiltro"> 
									</td>
								</tr>
							</thead>
							<tbody>
								<?
								$li = 10;
								?>
								<tr class="rowcab unidade">
									<th colspan="3" style="height: 40px;">
										<i class="fa fa-building" style="color:#291c1c;font-size:9px;"></i> UNIDADE(S)
									</th>
								</tr>
								<?
								$condicaoEmpresa = TRUE;
								$pessoaUnidade = RateioItemDestController::buscarPessoaPorIdUnidadeFuncionario($condicaoEmpresa);
								foreach ($pessoaUnidade AS $pessoaU) 
								{
									$li = $li + 1;
									$porc = ($pessoaU['funidade'] / $pessoaU['totalf']) * 100;
									?>
									<tr class="unidade" style="width:100%;" data-text="<?=$pessoaU['nome']?>">
										<td class="text-center">
											<input type="text" name="_<?=$li ?>#valor" class="valorrateio" style="width:60px; font-size:9px; border: 1px solid #cccccc !important; " vnumero="" onkeyup="calcular(this)">
											<input type="hidden" class="fracaofunc" quant='<?=$pessoaU['funidade'] ?>' value="<?=$porc ?>">
										</td>
										<td title="<?=$pessoaU['funidade'] . '/' . $pessoaU['totalf'] ?>">
											<? if ($pessoaU['funidade'] == 0) { ?>
												<font style="color: red;" title="NENHUM FUNCIONÁRIO NA UNIDADE"><?=$pessoaU['nome'] ?></font>
											<? } else { ?>
												<?=$pessoaU['nome'] ?>
											<? } ?>
											<input type="hidden" name="_<?=$li ?>#idunidade" class="idunidade" value="<?=$pessoaU['id'] ?>,<?=$pessoaU['tipo'] ?>">
										</td>
										<td></td>
									</tr>
									<?
								}

								?>
								<tr class="rowcab pessoa hide" style="background:#ddd;">
									<th colspan="3" style="height: 40px;">
										<i class="fa fa-building" style="color:#291c1c;font-size:9px;"></i> FUNCIONÁRIO(S)
									</th>
								</tr>
								<?
								$listarVw8Funcionario = RateioItemDestController::buscarvw8FuncionarioUnidadePorIdTipoPessoa();
								foreach($listarVw8Funcionario as $vw8Funcionario) 
								{
									$li = $li + 1;
									?>
									<tr class='pessoa hide'  data-text="<?=$vw8Funcionario['unidade']?>">
										<td><input type="text" name="_<?=$li?>#valor" class="valorrateio" style="width:60px; font-size:9px; border: 1px solid #cccccc !important; " vnumero="" onkeyup="calcular(this)"></td>
										<td>
											<?=$vw8Funcionario['nome'] ?>
											<input type="hidden" name="_<?=$li?>#idunidade" class="idunidade" value="<?=$vw8Funcionario['idunidade'] ?>,unidade">
											<input type="hidden" name="_<?=$li?>#idpessoa" class="idpessoa" value="<?=$vw8Funcionario['idpessoa'] ?>">
											<span style="background: rgb(102, 102, 102);font-size: 9px;color: #fff;padding: 0px 6px;border-radius: 3px;">
												<?=$vw8Funcionario['unidade'] ?>
											</span>
										</td>
										<td></td>
									</tr>
									<?
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div
			<? } ?>
		</div>
	</div>
<?
} else { //tipo = rateio

	$listarRateioNf = RateioItemDestController::buscarRateioItemNfItem($_idnf, $_idnfitem);
	?>
	<div class="row" id="formulario">
		<div class="col-md-8">
			<div class="panel panel-default">
				<div class="panel-heading cabecalho" style="height: 32px;">
					ITENS DO RATEIO
					<div style="float: right;">
					<?if($funcao!="COBRAR"){?>
						<button id="cbrestaurar" type="button" class="btn btn-primary btn-xs" onclick="restaurartodos()" title="Limpar">
						<i class="fa fa-arrow-circle-left"></i>Limpar
						</button>
					<?}?>				
					</div>
				</div>
				<div class="panel-body">
					<div><i>Selecione os iten(s) para edição do rateio.</i></div>
					<div>
						<input placeholder="Filtrar Itens do Rateio" class="size20" style="height: 22px;" type="text" id="inputFiltro2"> 
					</div>
					<div class="table table-striped planilha panel panel-default" style="width:100%;font-size:9px;">
						<div class="col-md-12 row rowcab panel-heading" style="margin:0px;font-size:9px;">
							<div class="col-md-6">
								<div class="col-md-1"><input type="checkbox" name="marcardesmarcar" checked class="pointer" title="Marcar/Desmarcar todos" onclick="selecionar(this,'inputcheckbox')"></div>
								<div class="col-md-2">QTD</div>
								<div class="col-md-2">UN</div>
								<div class="col-md-7">ITEM</div>
							</div>
							<div class="col-md-1" style="text-align: right;">VLR. R$</div>
							<div class="col-md-3" style="text-align: center;">DESTINO</div>
							<div class="col-md-1" style="text-align: right;">RATEIO %</div>
							<div class="col-md-1" style="text-align: center;">DETALHES</div>
						</div>
						<?
						$i = 0;
						$semrateio='N';
						foreach($listarRateioNf as $rateioNf) 
						{
							$rateiopendente = 'Y';
							if ($rateioNf['tipo'] == 'PRODUTO' && !empty($rateioNf['idunidadeest']) && !empty($rateioNf['idprodserv'])) {

								$sqlsol = cnf::buscaSqlsolcom($rateioNf['idnfitem']);
								$ressol = d::b()->query($sqlsol) or die("Falha ao buscar informações da solcom:  " . $sqlsol);
								$qtdsolcom = mysqli_num_rows($ressol);
								$qtdcom = 0;
								if ($qtdsolcom > 0) { // tem solicitação de compra

									while ($rowsol = mysqli_fetch_assoc($ressol)) {
										$qtdcom = $qtdcom + $rowsol['qtdcom'];
									}
								}
								if ($rateioNf['qtd'] <= $qtdcom) {
									$reteiopendente = 'N';
								} else {
									$consumodiasloterateio = $rateioNf['tempoconsrateio'];
									if (empty($consumodiasloterateio)) {
										$consumodiasloterateio = 30;
									}

									$sqlmeio = cnf::buscarSqlRateio($rateioNf['idprodserv'], $rateioNf['idunidadeest'], $consumodiasloterateio);
									$_reslotemeio = d::b()->query($sqlmeio) or die("Falha ao buscar rateio do produto: " . $sqlmeio);
									$numrowmeio = mysqli_num_rows($_reslotemeio);

									if ($numrowmeio > 0) {
										$rateiopendente = "N";
										$idprodservs .= $virg . $rateioNf['idprodserv'];
										$virg = ',';
									} else {
										$rateiopendente = 'Y';
									}
								}
							}

							$i = $i + 1;
							if (!empty($rateioNf['idrateioitemdest'])) {
								$acao = 'u';
							} else {
								$acao = 'i';
							}
							$total = $total + $rateioNf['rateio'];

							if ($rateioNf['tipoobjeto'] == 'unidade') {										
								$unidade = RateioItemDestController::buscarPorChavePrimariaUnidade($rateioNf['idobjeto']);
								$rateio = '<a target="_blank" href="?_modulo=unidade&_acao=u&idunidade='.$unidade["idunidade"].'&_idempresa='.$unidade["idempresa"].'">'.$unidade["unidade"].'</a>';
								$rateiostr=$unidade["unidade"];
							}/* elseif ($rateioNf['tipoobjeto'] == 'sgdepartamento') {
								$rateio = RateioItemDestController::buscarDepartamentoSgDepartamentoPorIdSgDepartamento($row['idobjeto']);
							} elseif ($rateioNf['tipoobjeto'] == 'pessoa') {
								$rateio = RateioItemDestController::buscarPessoaPorIdPessoa($row['idobjeto']);
							} elseif ($rateioNf['tipoobjeto'] == 'empresa') {
								$rateio = RateioItemDestController::buscarEmpresaPorIdEmpresa($rateioNf['idobjeto']);
							}*/ else {
								$semrateio='Y';
								$rateio = "<font color='red'>Sem Rateio</font>";
								$rateiostr="Sem Rateio";
							}


							?>
							<div class="col-md-12 row rowitem itemrateio" style="margin:0px;" style="width:100%;" data-text="<?=$rateioNf['descr']?> <?=$rateiostr?>">
								<div class="col-md-6 inputcheckbox">
									<div class="col-md-1"> 
										<?if(!empty($rateioNf['idrateioitemdestnf']) and $rateioNf['status'] =='COBRADO' ){?>
										<i class="fa fa-money verde pointer" title="Rateio em Cobrança" onclick="editardestnf(<?=$rateioNf['idrateioitemdest']?>,'<?=$rateioNf['descr']?>','<?=$rateiostr?>')" ></i>										
										<div class="hide" id="destnf<?=$rateioNf['idrateioitemdest']?>">
											<table class="table table-striped planilha">
												<tr>
													<th>Cobrança %</th>
													<th>Valor R$</th>
													<th>Nome</th>
													<th>Status</th>
												</tr>
												<?
												$cobranca=RateioItemDestController::listarRateioitemdestnfPorIdrateioitemdest($rateioNf['idrateioitemdest']);
												$totalrt=0;
												$deletar='Y';
												foreach($cobranca as $linha) {
													$totalrt=$totalrt+$linha['valor'];
													$rotulo = getStatusFluxo('nf', 'idnf', $linha['idnf']);
													if( $linha['status'] != 'INICIO' ){
														$deletar='N';
													}
												?>
												<tr>
													<td><?=number_format(tratanumero($linha['rateio']), 2, ',', '.');?></td>
													<td><?=number_format(tratanumero($linha['valor']), 2, ',', '.');?></td>
													<td><?=$linha['nome']?></td>
													<td>
														<a target="_blank" href="?_modulo=nfentrada&_acao=u&idnf=<?=$linha['idnf']?>"><?= mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?></a>
													</td>
												</tr>
												<?
												}
												?>
												<tr>
													<th>Total</th>
													<th><?=number_format(tratanumero($totalrt), 2, ',', '.'); ?></th>
													<th></th>
													<th style="text-align-last: center;" >
													<?if($deletar=='Y'){?>
														<i title="Excluir os Itens" class="fa fa-trash vermelho hoverpreto pointer" onclick="excluirdestnf('<?=$rateioNf['idrateioitemdestnf']?>')"></i>
													<?}?>
													</th>
												</tr>
											</table>
										</div>
										<?
										}elseif($rateioNf['status'] =='PENDENTE' OR empty($rateioNf['status'])){?>
											<input type="checkbox" checked class="changeacao" acao="<?=$acao ?>" atname="checked[<?=$i ?>]" value="<?=$rateioNf['idrateioitemdest'] ?>" style="border:0px">
										
											<input class="rateioitem" name="_<?=$i ?>_<?=$acao ?>_rateioitemdest_idrateioitemdest" type="hidden" value="<?=$rateioNf['idrateioitemdest'] ?>">
											<input class="rateioitem" name="_<?=$i ?>_<?=$acao ?>_rateioitemdest_idrateioitem" type="hidden" value="<?=$rateioNf['idrateioitem'] ?>">
										<? if ($acao == 'i') { ?>
											<input class="rateioitem" name="_<?=$i ?>_<?=$acao ?>_rateioitemdest_idobjeto" type="hidden" value="<?=$rateioNf['idnfitem'] ?>">
										<? } ?>	<?
										}else{
										?>
										<span title="Por: <?=$rateioNf['alteradopor']?> Em:  <?=dmahms($rateioNf['alteradoem'])?>">
											<?=$rateioNf['status']?>
										</span>	
										<?}?>
									</div>
									<div class="col-md-2"><?=$rateioNf['qtd'] ?></div>
									<div class="col-md-2"><?=$rateioNf['un'] ?> </div>
									<div class="col-md-7"><?=$rateioNf['descr'] ?></div>
								</div>
								<div class="col-md-1 " style="text-align: right;">

									<? if (!empty($rateioNf['idnf'])) { ?>
										<a class="hoverazul pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?=$rateioNf['idnf'] ?>')" title="Compra">
											<?=number_format(tratanumero($rateioNf['rateio']), 2, ',', '.'); ?>
										</a>
									<? } else { ?>
										<?=number_format(tratanumero($rateioNf['rateio']), 2, ',', '.'); ?>
									<? } ?>

								</div>
								<div class="col-md-3" style="text-align: center;">
									<?
									if (!empty($rateioNf['nome'])) {
										echo ($rateioNf['nome'] . '<br>');
									}
								
									echo $rateio;
									?>
								</div>
								<div class="col-md-1" style="text-align: right;">
									<?=$rateioNf['valorateio'] ?>%
								</div>
								<div  class="col-md-1" style="text-align: center;">
									<?if( !empty($rateioNf['idnfitem']) ) { ?>											
										<a title="Compra" class="fa fa-search fa-1x hoverazul pointer" onclick="showhistoricoitem(<?=$rateioNf['idnfitem'] ?>);"></a>
									<? } ?>
								</div>

							</div>
							<?
							$vtipo = $vtipo + $rateioNf['rateio'];
						}
						?>
						<div class="col-md-12 row rowitem"  style="margin:0px; font-size:9px;background:#ddd;font-weight:bold;">
							<div class="col-md-6">TOTAL:</div>
							<div class="col-md-1 " style="text-align: right;"><?=number_format(tratanumero($total), 2, ',', '.'); ?></div>
							<div class="col-md-3" style="text-align: right;"></div>
							<div class="col-md-1" style="text-align: right;"></div>
							<div class="col-md-1" style="text-align: right;"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
		    <? if ($semrateio=='N' AND $funcao=="COBRAR") { ?>
			<div class="panel panel-default">
				<div class="panel-heading cabecalho" style="height: 32px;">
					COBRANÇA
					<div style="float: right;">
						<button id="cbalterar" type="button" class="btn btn-success btn-xs hidden" onclick="alterartodos('Y')" title="Salvar">
							<i class="fa fa-circle"></i>Salvar
						</button>
						<button id="cbalterar2" style="border-color:#5cb85c63 !important; background-color:#5cb85c63 !important;" type="button" class="btn btn-success btn-xs" onclick="alert('É necessário completar o valor de 100% para o rateio.')" title="Salvar">
							<i class="fa fa-circle"></i>Salvar
						</button>
					</div>
				</div>
				<div class="panel-body">
					<div class="col-md-12">
						<div><i>Insira o(s) percentual(ais) para o(s) destino(s) desejado(s) abaixo, mínimo 100%.</i></div>
						<br>
						<table id="tbrateio" style="width:100%;">
							<thead>
								<tr class="rowcab">
																
									<td style="width:30%">
										Total Divisão: <span id="totalvalor" style="color: red;"></span>
									</td>	
									<td style="width:70%; text-align:right">									    
										
									</td>								
								</tr>
								<tr class="rowcab">
									<td colspan="2">
										<input placeholder="Filtrar" style="height: 22px; width: 90%;" type="text" id="inputFiltroempresa"> 
									</td>
								</tr>
							</thead>
						</table>
						<table class="table table-striped planilha" style="font-size:9px;text-transform:uppercase;">
							<tbody>
								<?
								
								$listarEmpresa = EmpresaController::listarEmpresasAtivasComcobranca($idempresa);
								$li = 10;
								?>
								<tr class="rowcab unidade" style="background:#ddd;">
									<th colspan="3" style="height: 40px;">
										<i class="fa fa-building" style="color:#291c1c;font-size:9px;"></i> Empresa(S)
									</th>
								</tr>
								<?
								foreach($listarEmpresa as  $empresa) 
								{
									$li = $li + 1;
								?>
									<tr class="empresa" style="width:100%;" data-text="<?=$empresa['empresa']?>">
										<td style="width:20%">
											
										<? if(!empty($empresa['idpessoaform']) and $empresa['conf']>0){
										$fontc="";		
										$title="";
										?>
											<input type="text"  name="_<?=$li ?>#valor" class="valorrateio" style="width:60px; font-size:9px; border: 1px solid #cccccc !important;" vnumero="" onkeyup="calcular(this)">
										<?}else{ 
										 $fontc="red";	
										 $title="Configurar no cadastro desta empresa a pessoa empresa relacionada, fatura automática, Categoria e Subcategoria";
										?>
											<input title="<?=$title?>" type="text" disabled='disabled'  name="_<?=$li ?>#valor" class="valorrateio" style="width:60px; font-size:9px; border: 1px solid #cccccc !important;" vnumero="" onkeyup="calcular(this)">
										<?}?>
										</td>
										<td style="width:80%" title=<?=$title?>>
											<font color="<?=$fontc?>">									
											<?=$empresa['empresa'] ?>
											</font>
										
											<input type="hidden" name="_<?=$li ?>#idempresa" class="idempresa" value="<?=$empresa['idempresa']?>,'empresa'">
											<span style="background: rgb(102, 102, 102);font-size: 9px;color: #fff;padding: 0px 6px;border-radius: 3px; display:none">
												Empresa
											</span>
										</td>
										<td class="nowrap">
										<?if(!empty($empresa['idnf'])){?>
												<a class="hoverazul pointer" onclick="janelamodal('?_modulo=rateioitemdestnf&_acao=u&idnf=<?=$empresa['idnf']?>')" title="Cobrança aberta">
													R$ <?=number_format($empresa['valor'], 2, '.', '');?>
												</a>
											<?}else{echo("R$ 0.00");}
											?>
										</td>	
									</tr>

								<?
								}
								?>								
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<? } ELSE { ?>
			<div class="panel panel-default">
				<div class="panel-heading cabecalho" style="height: 32px;">
					RATEIO INTERNO
					<div style="float: right;">
						<button id="cbalterar" type="button" class="btn btn-success btn-xs hidden" onclick="alterartodos('N')" title="Salvar">
							<i class="fa fa-circle"></i>Salvar
						</button>
						<button id="cbalterar2" style="border-color:#5cb85c63 !important; background-color:#5cb85c63 !important;" type="button" class="btn btn-success btn-xs" onclick="alert('É necessário completar o valor de 100% para o rateio.')" title="Salvar">
							<i class="fa fa-circle"></i>Salvar
						</button>
					</div>
				</div>
				<div class="panel-body">
					<div class="col-md-12">
						<div><i>Insira o percentual de rateio de acordo com a(s) unidade(s) totalizando 100%.</i></div>
						<br>
						<table id="tbrateio" style="width:100%;">
							<thead>
								<tr class="rowcab">
																
									<td style="width:30%">
										Total Rateio: <span id="totalvalor" style="color: red;"></span>
									</td>	
									<td style="width:70%; text-align:right" >
									    <button  title="Ratear por numero de funcionários" type="button" class="btn btn-primary btn-xs" onclick="calculafunc(this)" >
                                            <i title="Ratear por numero de funcionários" class="fa fa-money pointer" ></i> Ratear por nº de colaboradores
                                        </button>                                      
									</td>								
								</tr>
								<tr class="rowcab">
									<td colspan="2">
										<input placeholder="Filtrar" style="height: 22px; width: 90%;" type="text" id="inputFiltro"> 
                                        <button  title="Visualizar por colaborador" type="button" class="btn btn-primary btn-xs" onclick="mostrarpessoa(this)" >
                                            <i title="Visualizar por colaborador" class="fa fa-users group"></i>  
                                        </button>
									</td>
								</tr>
							</thead>
						</table>
						<table class="table table-striped planilha" style="font-size:9px;text-transform:uppercase;">
							<tbody>
								<?
								$condicaoEmpresa = TRUE;
								$listarPessoaUnidadePorIdunidade = RateioItemDestController::buscarPessoaPorIdUnidadeFuncionario($condicaoEmpresa);
								$li = 10;
								?>
								<tr class="rowcab unidade" style="background:#ddd;">
									<th colspan="3" style="height: 40px;">
										<i class="fa fa-building" style="color:#291c1c;font-size:9px;"></i> UNIDADE(S)
									</th>
								</tr>
								<?
								foreach($listarPessoaUnidadePorIdunidade as  $pessoaUnidadePorIdunidade) 
								{
									$li = $li + 1;
									$porc = ($pessoaUnidadePorIdunidade['funidade'] / $pessoaUnidadePorIdunidade['totalf']) * 100;
									?>
									<tr class="unidade" style="width:100%;" data-text="<?=$pessoaUnidadePorIdunidade['nome']?>">
										<td style="width:20%">
											<input type="text" name="_<?=$li ?>#valor" class="valorrateio" style="width:60px; font-size:9px; border: 1px solid #cccccc !important;" vnumero="" onkeyup="calcular(this)">
											<input type="hidden" class="fracaofunc" quant='<?=$pessoaUnidadePorIdunidade['funidade'] ?>' value="<?=$porc ?>">
										</td>
										<td style="width:80%">
											<? if ($pessoaUnidadePorIdunidade['funidade'] == 0) { ?>
												<font style="color: red;" title="NENHUM FUNCIONÁRIO NA UNIDADE"><?=$pessoaUnidadePorIdunidade['nome'] ?></font>
											<? } else { ?>
												<?=$pessoaUnidadePorIdunidade['nome'] ?>
											<? } ?>
											<input type="hidden" name="_<?=$li ?>#idunidade" class="idunidade" value="<?=$pessoaUnidadePorIdunidade['id'] ?>,<?=$pessoaUnidadePorIdunidade['tipo'] ?>">
											<span style="background: rgb(102, 102, 102);font-size: 9px;color: #fff;padding: 0px 6px;border-radius: 3px; display:none">
												Unidade
											</span>
										</td>
										
									</tr>

								<?
								}
								?>
								<tr class="rowcab pessoa hide" style="background:#ddd;">
									<th colspan="3" style="height: 40px;">
										<i class="fa fa-building" style="color:#291c1c;font-size:9px;"></i> FUNCIONÁRIO(S)
									</th>
								</tr>
								<?
								$listarFuncionarioUnidade = RateioItemDestController::buscarvw8FuncionarioUnidadePorIdTipoPessoa();
								foreach($listarFuncionarioUnidade as $funcionarioUnidade) 
								{
									$li = $li + 1;
									?>
									<tr class="pessoa hide" data-text="<?=$funcionarioUnidade['nomecurto']?>">
										<td><input type="text" name="_<?=$li ?>#valor" class="valorrateio" style="width:60px; font-size:9px; border: 1px solid #cccccc !important; " vnumero="" onkeyup="calcular(this)"></td>
										<td>
											<?=$funcionarioUnidade['nomecurto'] ?> 
											<input type="hidden" name="_<?=$li ?>#idunidade" class="idunidade" value="<?=$funcionarioUnidade['idunidade'] ?>,unidade">
											<input type="hidden" name="_<?=$li ?>#idpessoa" class="idpessoa" value="<?=$funcionarioUnidade['idpessoa'] ?>">
											<span style="background: rgb(102, 102, 102);font-size: 9px;color: #fff;padding: 0px 6px;border-radius: 3px;">
												<?=$funcionarioUnidade['unidade'] ?>
											</span>
										</td>
										<td></td>
									</tr>

								<?
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<? } ?>
		</div>
	</div>

	<?
	if (strpos($stidrateioitemdest, ',') === false) {
		$idrateioitemdest = $stidrateioitemdest;
	}
	if (!empty($idrateioitemdest)) { // trocar p/ cada tela a tabela e o id da tabela
		$_idModuloParaAssinatura = $idrateioitemdest; // trocar p/ cada tela o id da tabela
		require 'viewAssinaturas.php';
	}
	$tabaud = "rateioitemdest"; //pegar a tabela do criado/alterado em antigo
	$idRefDefaultDropzone = "anexos";
	require 'viewCriadoAlterado.php';
}

require_once('../form/js/rateioitemdest_js.php');
?>