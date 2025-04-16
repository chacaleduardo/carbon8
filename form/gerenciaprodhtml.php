<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/gerenciaprod_controller.php");
if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

$idpessoa = $_GET["idpessoa"];
$idprodserv = $_GET["idprodserv"];
$status = $_GET["status"];
$idplantel = $_GET['idplantel'];
$validacao = $_GET['validacao'];

$acao = $_GET["acao"];

if (!empty($idpessoa)) {
	$clausulalote = " AND l.idpessoa =".$idpessoa." ";
}

if (!empty($idprodserv)) {
	$clausulad = " AND p.idprodserv =  ".$idprodserv."";
}

if ($status == 'ATIVO') {
	$clausulals = " AND ls.status NOT IN ('CANCELADO')";
	$clausulastatusfracao = " AND fr.status ='DISPONIVEL'";
} else {
	$clausulals = " AND ls.status NOT IN ('CANCELADO') ";
}

if ($idplantel) {
	$strplantel = " AND f.idplantel=".$idplantel;
	$strinplantel = " AND EXISTS (SELECT 1 FROM prodservformula f 
								   WHERE f.idprodserv = p.idprodserv 
								     AND f.status ='ATIVO' 
									 AND f.idplantel = ".$idplantel.") ";
} else {
	$strplantel = '';
	$strinplantel = '';
}

if ($validacao == 'V') {
	$strvalidacao = " AND NOT EXISTS (SELECT 1 
										FROM prodservforn pf 
									   WHERE pf.idprodserv = l.idprodserv 
										 AND pf.idprodservformula = f.idprodservformula
										 AND pf.idpessoa = l.idpessoa 
										 AND pf.validadoem > DATE_SUB(now(), INTERVAL 12 MONTH))
					  AND NOT EXISTS (SELECT 1 
										   FROM prodservforn pf 
										  WHERE pf.idprodserv = l.idprodserv 
											AND pf.idprodservformula = f.idprodservformula
											AND pf.idpessoa = l.idpessoa 
											AND pf.valido = 'N')";
} elseif ($validacao == 'O') {
	$strvalidacao = " AND EXISTS (SELECT 1 
									FROM prodservforn pf 
								   WHERE pf.idprodserv = l.idprodserv 
									 AND pf.idprodservformula = f.idprodservformula
									 AND pf.idpessoa = l.idpessoa 
									 AND pf.validadoem > DATE_SUB(now(), INTERVAL 12 MONTH))
									 AND NOT EXISTS (SELECT 1 
													   FROM prodservforn pf 
													  WHERE pf.idprodserv = l.idprodserv 
														AND pf.idprodservformula = f.idprodservformula
														AND pf.idpessoa = l.idpessoa 
														AND pf.valido = 'N') ";

} elseif ($validacao == 'I'){
	$strvalidacao = " AND EXISTS (SELECT 1 FROM prodservforn pf 
								   WHERE pf.idprodserv = l.idprodserv 
									 AND pf.idprodservformula= f.idprodservformula
									 AND pf.idpessoa = l.idpessoa 
									 AND pf.valido = 'N')";
} else {
	$strvalidacao = " ";
}

//LTM - 25-09-2020: Inserido para listar as pessoas do chat quando clica no Botão Compartilhar
if ($_SESSION["SESSAO"]["IDPESSOA"]) {
	$idsnippet = GerenciaProdController::buscarSnippetPorNotificacaoEEmpresa();
}

$controleass = $_SESSION[$struniqueid]["controleass"];

if ($acao == "ini") {
	$controleass = 1; //vai para o primeiro registro	
	// Executa a consulta completa somente 1 vez para recuperar a quantidade total de registros
	$booexeccount = true;
	//Atualiza o Uniqueid da pagina para guardar o ultima pagina utilizada
} else {
	if ($acao == "prox") {
		$controleass = intval($controleass) + 1;
	} elseif ($acao == "ant" and $controleass > 1) {
		$controleass = intval($controleass) - 1;
	}
}
//Apos o incremento da variavel, atribui para a ssesion o valor do proximo registro a ser chamado
$_SESSION[$struniqueid]["controleass"] = $controleass;


if ($_GET && (!empty($idpessoa) || !empty($idprodserv) || !empty($idplantel) || $validacao == 'V' || $validacao == 'O')) 
{
	if($booexeccount == true) 
	{
		$listarProdutosComFormula = GerenciaProdController::buscarProdutoComFormula($invalidacao, $strinplantel, $clausulad, $strvalidacao, $clausulalote);
		echo "<!-- buscarProdutoComFormula: <br> ".$listarProdutosComFormula['sql']." -->";
		$arridresultado = array();
		$iarr = 0;
		//Grava todos os resultados para se poder navegar entre eles
		foreach ($listarProdutosComFormula['dados'] as $produtoFormula) 
		{
			$iarr++;
			$arridresultado[$iarr] = $produtoFormula["idprodserv"];
			$booexeccount = false;
		}
		//total de registros da consulta
		$_SESSION[$vargetsess]["qtdreg"] = $iarr;
		//grava todos dos os ids de resultados da consulta
		$_SESSION[$vargetsess]["arridres"] = $arridresultado;
	} //if($booexeccount==true){
		
	$arridresultado = $_SESSION[$vargetsess]["arridres"];

	$qtdcount = $_SESSION[$vargetsess]["qtdreg"];
	if ($qtdcount < $controleass) {
		echo '<br><br><br><div align="center">Não existem mais produtos.</div>';
		die;
	}

	$listarProdutos = GerenciaProdController::buscarProdutoPorIdProdserv($arridresultado[$controleass]);
	foreach($listarProdutos as $produtoDados) 
	{
		$produto = $produtoDados["descr"];
		?>
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="row">
							<div class="col-md-8">Produto <?=$controleass ?> de <?=$qtdcount ?> - <span id="cbResultadosInfo" numrows="<?=$qtdrows ?>"><?=$produto ?></span></div>
							<div class="col-md-1 nowrap">
								&nbsp;&nbsp;&nbsp;&nbsp;
								<i id="cbCompartilharItem" class="fa fa-comment-o fa-2x fade pointer hoverlaranja compartilhar cbCompartilharItem" title="Compartilhar este item" onclick="compartilharItem()" style="display:none;"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?
		$listarFormulaFornecedor = GerenciaProdController::buscarFormulaPorFornecedor($clausulalote, $strplantel, $strvalidacao, $produtoDados['idprodserv']);
		$qtdprodform = $listarFormulaFornecedor['qtdLinhas'];
		echo "<!-- buscarFormulaPorFornecedor: ".$listarFormulaFornecedor['sql']." -->";

		if ($qtdprodform > 0) 
		{
			foreach ($listarFormulaFornecedor['dados'] AS $formulaFornecedor) 
			{
				if (!empty($formulaFornecedor['idprodservforn'])) 
				{
					$strurl = "_acao=u&idprodservforn=".$formulaFornecedor['idprodservforn'];
					if ($formulaFornecedor['validade'] == 'V' || empty($formulaFornecedor['validadoem'])) 
					{
						$cor = 'vermelho';
						$texto = "<font color='red'>VALIDAÇÃO PENDENTE</font>";

						$funcaovalida = "validaproduto(".$formulaFornecedor['idprodservforn'].", '".$_SESSION["SESSAO"]["USUARIO"]."','".date("d/m/Y")."')";
						if (!empty($formulaFornecedor['validadopor'])) {
							$rotulovalida = " por: ".$formulaFornecedor['validadopor']." em: ".dma($formulaFornecedor['validadoem']);
						} else {
							$rotulovalida = "";
						}
					} else {
						$cor = 'verde';
						$texto = "<font color='green'>VALIDADO</font>";
						$funcaovalida = "retiravalidaproduto(".$formulaFornecedor['idprodservforn'].")";
						$rotulovalida = " por: ".$formulaFornecedor['validadopor']." em: ".dma($formulaFornecedor['validadoem']);
					}
				} else {
					$strurl = "_acao=i&idprodserv=".$produtoDados['idprodserv']."&idpessoa=".$formulaFornecedor['idpessoa']."&idprodservformula=".$formulaFornecedor['idprodservformula'];
					$cor = 'vermelho';
					$texto = "<font color='red'>VALIDAÇÃO PENDENTE</font>";
					$rotulovalida = " ";
					$funcaovalida = "geravalidaproduto(".$produtoDados['idprodserv'].",".$formulaFornecedor['idpessoa'].",".$formulaFornecedor['idprodservformula'].",'".$_SESSION["SESSAO"]["USUARIO"]."','".date("d/m/Y")."')";
				}

				$listarFormulaEAmostra = GerenciaProdController::buscarFormulaEAmostraPorIdProdserv($clausulals, $clausulastatusfracao, $formulaFornecedor['idpessoa'], $formulaFornecedor['idprodserv'], $strplantel);
				echo "<!-- buscarFormulaPorFornecedor: ".$listarFormulaEAmostra['sql']." -->";
				$qtdx = $listarFormulaEAmostra['qtdLinhas'];
				?>
				<div class="row">
					<div class="col-md-12">
						<div class="panel panel-default">
							<div class="panel-heading ">
								<div class="col-md-12">
									<i onclick="<?=$funcaovalida ?>" title="Validar Produto." class="fa fa-check pointer <?=$cor?>">
									&nbsp;<label class="alert-warning"><?=$texto ?></label>&nbsp;</i><?=$rotulovalida ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<? 
									if (!empty($formulaFornecedor['idprodservforn'])) 
									{
										if ($formulaFornecedor['valido'] == 'Y') { ?>
											<i class="fa fa-check-circle-o  fa-1x verde hoververde btn-lg pointer ui-droppable" vstatus="N" onclick="inativaprodserv(<?=$formulaFornecedor['idprodservforn'] ?>,this)" title="Ativo - Clique para Inativar">ATIVO</i>
										<? } else { ?>
											<i class="fa fa-check-circle-o  fa-1x vermelho hoververmelho btn-lg pointer ui-droppable" vstatus="Y" onclick="inativaprodserv(<?=$formulaFornecedor['idprodservforn'] ?>,this)" title="Inativo - Clique para Ativar">INATIVO</i>
										<?
										}
									}
									?>
								</div>
								<div class="col-md-12">
									<strong><a href="?_modulo=prodservforn&<?=$strurl?>" target="_blank" style="color: inherit;"> - <?=$formulaFornecedor['nome']?> <br> - <?=$produto?> <br> - <?=$formulaFornecedor['rotulo']?> </a></strong>
									<?
									if ($formulaFornecedor['idprodservforn']) {
										$fatqtd = "geraprodservforn(this,'u',".$produtoDados['idprodserv'].",".$formulaFornecedor['idpessoa'].",".$formulaFornecedor['idprodservformula']."," .$formula['idprodservforn'].")";
									} else {
										$fatqtd = "geraprodservforn(this,'i',".$produtoDados['idprodserv'].",".$formulaFornecedor['idpessoa'].",".$formulaFornecedor['idprodservformula'].",0)";
									}
									?>
									<input value="<?=$formulaFornecedor['qtd'] ?>" onchange="<?=$fatqtd ?>" class="size7" type="text">
								</div>
							</div>
							<div class="panel-body">
								<?
								if($qtdx > 0) 
								{
									?>
									<table class="table table-striped planilha">
										<tr>
											<td>
												<div class="row">
													<?
													$idprodserv = '';
													foreach($listarFormulaEAmostra['dados'] as $formulaAmostra) 
													{
														$idprodservsemente = $formulaAmostra['idprodservsemente'];
														if ($formulaAmostra['situacao'] == 'APROVADO') {
															$botao = 'btn-success';
														} elseif ($formulaAmostra['situacao'] == 'REPROVADO') {
															$botao = 'btn-danger';
														} else {
															$botao = 'btn-warning';
														}
														if ($formulaAmostra['idprodserv'] != $idprodserv) 
														{
															if (!empty($idprodserv)) 
															{
																$abriuprod = 'N';
																if ($divaberto == 'Y') {
																	$divaberto = 'N';
																	echo ("</div>");
																}

																//Listar os concentrados
																listaconcentrados($formulaFornecedor['idpessoa'], $idprodservsementeant, $produtoDados['idprodserv'], $formulaFornecedor['idprodservformula'], $strplantel, $formulaFornecedor['qtd']);
															} //if(!empty($idprodserv)){
															?>

															</div>
															</div>
															<div class="col-md-6">
																<div class="panel panel-default">
																	<div class="panel-heading"><?=$formulaAmostra['descr'] ?></div>
																		<?
																		$idprodserv = $formulaAmostra['idprodserv'];
																		$idprodservsementeant = $formulaAmostra['idprodservsemente'];
																		$idpool = "";
																		$existepoll = '';
																		$abriuprod = 'Y';
														} //if($formulaAmostra['idprodserv']!=$idprodserv){

														if ($formulaAmostra['idpool'] > 0) 
														{
															$tempool = 'Y';
															$classdrag = "soltavel";
															$existepoll = 'Y';
														} else {
															$tempool = 'N';
															$classdrag = "dragInsumo";
														} //if($formulaAmostra['idpool']>0){

														if (!empty($formulaAmostra['idpool'])) 
														{
															if (!empty($idpool) && $idpool != $formulaAmostra['idpool']) 
															{
																$divaberto = 'Y';
																?>
																</div>
																<div style="border: 1px silver dotted; margin: 4px; background-color: #ffffffd6;">
																<?
															} elseif (empty($idpool)) {
																$divaberto = 'Y';
																?>
																<div style="border: 1px silver dotted; margin: 4px; background-color: #ffffffd6;">
																<?
															}
															$idpool = $formulaAmostra['idpool'];
														} elseif (!empty($idpool) and empty($formulaAmostra['idpool'])) {
															$idpool = '';
															$divaberto = 'N';
															?>
															</div>
															<?
														} //if(!empty($formulaAmostra['idpool'])){
														if (empty($formulaAmostra['orgao'])) {
															$formulaAmostra['orgao'] = $formulaAmostra['observacao'];
														}
														?>
														<div class="panel-body" <? if ($tempool == 'N') { ?> style="border: 1px silver dotted; margin: 4px; background-color: #ffffffd6;" <? } ?>>
															<table class='tblotes'>
																<tr class="soltavel dragInsumo" idloteins="<?=$formulaAmostra['idlote'] ?>" idpool="<?=$formulaAmostra['idpool'] ?>">
																	<td>
																		<a class="fa fa-bars hoverazul pointer fade" href="?_modulo=semente&_acao=u&idlote=<?=$formulaAmostra['idlote'] ?>" target="_blank" title="Lote"></a>
																	</td>
																	<td>
																		<button title="<?=$formulaAmostra['subtipoamostra'] ?> <?=$formulaAmostra['tipificacao'] ?> <?=$formulaAmostra['orgao'] ?>" status="<?=$formulaAmostra['status'] ?>" id="<?=$formulaAmostra['idlote'] ?>" situacao="<?=$formulaAmostra['situacao'] ?>" type="button" class="btn <?=$botao ?> btn-xs" onclick="alterast(this, <?=$formulaAmostra['idlote'] ?>)">
																			<? if ($formulaAmostra['vencido'] == 'Y') { ?><i class="fa fa-exclamation-triangle vermelho fa-1x pointer" title="Vencida"></i><? } ?>
																			<?=$formulaAmostra['partida'] ?>/<?=$formulaAmostra['exercicio'] ?> <?=$formulaAmostra['status'] ?> - <?= dma($formulaAmostra['vencimento']) ?>
																		</button>
																	</td>
																	<? if ($tempool == 'N') { ?>
																		<td title="Arrastar para um Poll.">
																			<i class="fa fa-arrows hoverazul cinzaclaro hover move"></i>
																		</td>
																	<? } elseif ($tempool == 'Y') { ?>
																		<td>
																			<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer" onclick="inativapool(<?=$formulaAmostra['idlotepool'] ?>)" title="Retirar do Pool "></i>
																		</td>
																	<? } ?>
																</tr>
															</table>
														</div>
														<?
													} //($formulaAmostra= mysqli_fetch_assoc($resx)){
													//Listar os concentrados da ultima semente do loop

													if (!empty($idprodservsemente)) 
													{
														//Listar os concentrados
														listaconcentrados($formulaFornecedor['idpessoa'], $idprodservsemente, $produtoDados['idprodserv'], $formulaFornecedor['idprodservformula'], $strplantel, $formulaFornecedor['qtd']);
													} //if(!empty($idprodservsemente)){	

													if ($divaberto == 'Y') 
													{
														$divaberto = 'N';
														echo ("</div>");
													}
													if ($abriuprod == 'Y') 
													{
														echo ("</div></div>");
														$abriuprod = 'N';
													}

													?>
												</div>
												</div>
												</div>
											</td>
										</tr>
									</table>
									<?
								} else {
									echo ('Não Possui semente.');
								}
								?>
							</div>
						</div>
					</div>
				</div>
				<?
			} //while($r= mysqli_fetch_assoc($resv)){
		} else {
			echo ("<br>Este produto não possui formulação com as caracteristicas da pesquisa.</br>");
		}
	} // while($row=mysql_fetch_assoc($res)){ 
	?>
	</div>
	</div>
	</div>
	</div>
	<?
} //if($_GET and !empty($clausulad)){

//listar concentrados
function listaconcentrados($idpessoa, $idprodservsementeant, $idprodserv, $idprodservformula, $strplantel, $qtdprod = 0)
{
	//Listar os concentrados
	$listarProdservFormAmostra = GerenciaProdController::buscarProdservFormulaEAmostraPorIdProdserv($idpessoa, $idprodservsementeant, $idprodserv, $idprodservformula, $strplantel);
	echo "<!-- buscarFormulaPorFornecedor: ".$listarProdservFormAmostra['sql']." -->";
	$qtdcon = $listarProdservFormAmostra['qtdLinhas'];
	if ($qtdcon > 0) 
	{
		?>
		<table class="table table-striped planilha">
			<?
			$lin = 0;
			foreach($listarProdservFormAmostra['dados'] as $prodservFormAmostra) 
			{
				if (strpos(strtolower(recuperaExpoente(tratanumero($prodservFormAmostra['qtdi']), $prodservFormAmostra['qtdi_exp'])), "d")) 
				{
					$arrExp = explode('d', strtolower(recuperaExpoente(tratanumero($prodservFormAmostra['qtdi']), $prodservFormAmostra['qtdi_exp'])));
					$vqtdpadrao = $arrExp[0];
					$varde = 'd';

					$v1 = (floatval($qtdprod) * floatval($vqtdpadrao)) / floatval($prodservFormAmostra['qtdpadrao']);
					$v2 = $v1 * $arrExp[1];
					if (strpos(strtolower(recuperaExpoente(tratanumero($prodservFormAmostra['qtddisp']), $prodservFormAmostra['qtddisp_exp'])), "d")) 
					{
						$arrExplt = explode('d', strtolower(recuperaExpoente(tratanumero($prodservFormAmostra['qtddisp']), $prodservFormAmostra['qtddisp_exp'])));
						$preciso = round($v2 / $arrExplt[1], 2);
						$rotpreciso = $preciso.'d'.$arrExplt[1];
						$tenho = $arrExplt[0];
					} else {
						$preciso = $v2;
						$rotpreciso = $v2;
						$tenho = $prodservFormAmostra['qtddisp'];
					}
				} elseif (strpos(strtolower(recuperaExpoente(tratanumero($prodservFormAmostra['qtddisp']), $prodservFormAmostra['qtddisp_exp'])), "e")) {
					$arrExp = explode('e', strtolower(recuperaExpoente(tratanumero($prodservFormAmostra['qtddisp']), $prodservFormAmostra['qtddisp_exp'])));
					$vqtdpadrao =  $arrExp[0];
					$varde = 'e';
				} else {
					$vqtdpadrao = (empty($prodservFormAmostra['qtdi']) or $prodservFormAmostra['qtdi'] == 0) ? 1 : $prodservFormAmostra['qtdi'];
					$varde = '';
					$preciso = (floatval($qtdprod) * floatval($vqtdpadrao)) / floatval($prodservFormAmostra['qtdpadrao']);
					$rotpreciso = $preciso;
					if (strpos(strtolower(recuperaExpoente(tratanumero($prodservFormAmostra['qtddisp']), $prodservFormAmostra['qtddisp_exp'])), "d")) {
						$arrExplt = explode('d', strtolower(recuperaExpoente(tratanumero($prodservFormAmostra['qtddisp']), $prodservFormAmostra['qtddisp_exp'])));
						$tenho = $arrExplt[0] / $arrExplt[1];
					} else {
						$tenho = $prodservFormAmostra['qtddisp'];
					}
				}
				if ($tenho < $preciso) {
					$btn = 'danger';
				} else {
					$btn = 'success';
				}

				if ($lin == 0) 
				{
					?>
					<tr>
						<th>Concentrado</th>
					</tr>
					<?
				}
				?>
				<tr>
					<td title="<?= dma($prodservFormAmostra['vencimento']) ?>	<?=$prodservFormAmostra['status'] ?>">
						<span class="label label-<?=$btn ?> fonte10 itemestoque  especial especialvisivel">
							<a href="?_modulo=formalizacao&_acao=u&idlote=<?=$prodservFormAmostra['idlote'] ?>" target="_blank" style="color: inherit;">
								<?=$prodservFormAmostra['partida'] ?>/<?=$prodservFormAmostra['exercicio'] ?>
							</a>
							<?= recuperaExpoente(tratanumero($prodservFormAmostra['qtddisp']), $prodservFormAmostra['qtddisp_exp']) ?>
							<div class="insumosEspeciais">
								<i class="fa fa-star amarelo bold"></i>
								<?=$prodservFormAmostra['sementes'] ?>
							</div>
							<?
							if ($qtdprod > 0) {
								echo ("Usar: ".$rotpreciso);
							}
							?>
						</span>
					</td>
				</tr>
			<?
			}
			?>
		</table>
		<?
	} //if($qtdcon>0){

} //function listaconcentrados(){

require_once('../form/js/gerenciaprodhtml_js.php');
?>