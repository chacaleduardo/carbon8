<?
if (!empty($_GET["reportexport"])) {
    ob_start(); //não envia nada para o browser antes do termino do processamento
}

require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/estoqueformulados_controller.php");

if($_POST){
    require_once("../inc/php/cbpost.php");
}

$_modulo = $_GET["_modulo"];
$vendas = "";

$idUnidadePadrao =  getUnidadePadraoMatriz($_modulo, cb::idempresa(), cb::habilitarMatriz());

if ($_modulo == 'estoqueformuladosvenda') {
	$instrvenda = " AND p.venda='Y' ";
	$strvenda = "VENDA ";
} else if ($_modulo == 'estoqueformulados') {
	$instrvenda = " AND p.venda='N' AND p.especial = 'N'";
	$strvenda = "NÃO VENDA ";
} else {
	$instrvenda = '';
	$strvenda = "";
}

if ($_modulo == 'lotealertapesqdes') {
	$novoorcamentolink = "./?_modulo=lotepesqdess&_acao=i";
	$titulo = "Novo Lote";
	$titulomodulo = "Lote";
} elseif ($_modulo == 'lotesformuladosmeio') {
	$novoorcamentolink = "./?_modulo=formalizacaomeios&_acao=i";
	$titulo = "Nova Formalização";
	$titulomodulo = "Formalização";
}

$linklote = EstoqueFormuladosController::buscarModuloTipoLoteViculadoAUnidade($idUnidadePadrao);
$produtosEmAlerta = EstoqueFormuladosController::buscarProdutosFormuladosEmAlertaDeProducao($instrvenda, $idUnidadePadrao, false, $_modulo);
$arrayProdutosEmAlerta = $produtosEmAlerta['data'];
$sqlProdutosEmAlerta = $produtosEmAlerta['sql'];
$qtdProdutosEmAlerta = $produtosEmAlerta['numRows'];

echo '<!--'.$sqlProdutosEmAlerta.'-->';

?>
<style>
	@media print {
		* {
			-webkit-transition: none !important;
			transition: none !important;
		}
	}
	[class*='wdDescr'] {
		width: 25%;
	}
	[class*='wdStatus'] {
		width: 20%;
	}

	.center {
		text-align: center;
	}

	.end {
		text-align: end;
	}

	.valign {
		vertical-align: middle !important;
	}

	.mt30 {
		margin-top: 30px;
	}

	.fs15 {
		font-size: 15px;
	}

	.twrap {
		flex-wrap: nowrap;
	}

	.titulodoc {
		height: inherit;
		line-height: inherit;
		display: table-cell;
		text-align: center;
		font-size: 0.3cm;
		font-weight: bold;
	}

	.row {
		display: table;
		table-layout: fixed;
		width: 99%;
		margin: 0mm 0mm;
	}
	
	.f-nav{ z-index: 9999; position: fixed; left: 0; top: 0; background-color: white;}
	.nav-container{
		display: block; width: 100%; height: 50px; background: yellow;
	}

	.tituloEmpresa
	{
		background-color: #ffffff !important;
		text-align: center;
		font-weight: bold;
		text-transform: uppercase;
		font-size: 15px;
	}
	.spanAlinha{
		display:  inline-block; 
		margin-right: 1rem; 
		font-size: 13px;
		padding: 0.3rem 1rem;
	}
	.spanAlinha.verdeqtd{background-color: #98FB98;}
	.spanAlinha.vermelhoqtd{background-color: #FDD0C7;}
</style>

<table class="table table-striped ">
	<thead>
		<th class="fs15">
			<strong> PRODUTOS <?=$strvenda ?>COM ESTOQUE ABAIXO DO IDEAL</strong>
			<div class="w-100 row m-0 d-flex flex-wrap">
				<span style="display: inline-block; margin-right: 1rem; font-size: 13px; padding-top: 3px;">QTD: <?=$qtdProdutosEmAlerta ?></span>
				<strong class="spanAlinha verdeqtd"></strong>
				<strong class="spanAlinha vermelhoqtd"></strong>
			</div>
		</th>
		<th><input id="filtroAlertaEstoque" type="text" placeholder="Pesquisar"></th>
		<th><a class="btn btn-success no-print" style="width: 130px;" href="<?= $_SERVER['REQUEST_URI'] ?>&reportexport=csv" target="_blank">Exportar .csv</a></th>
	</thead>
</table>
<table id="restbl" class="table table-striped fixarthtable">
	<thead>
		<th class="wdDescr valign col-md-3">
			Descrição
			<br>
			&nbsp;<i class='fa fa-arrow-down pointer' title='Ordenar Crescente' style='font-size: 0.8em; opacity: 0;' attr='desc'></i>
			&nbsp;<i class='fa fa-arrow-up pointer' title='Ordenar Decrescente' style='font-size: 0.8em; opacity: 0;' attr='asc'></i>
		</th>
		<th class="center valign col-md-1">
			Estoque
			<br>
			&nbsp;<i class='fa fa-arrow-down pointer' title='Ordenar Crescente' style='font-size: 0.8em; opacity: 0;' attr='desc'></i>
			&nbsp;<i class='fa fa-arrow-up pointer' title='Ordenar Decrescente' style='font-size: 0.8em; opacity: 0;' attr='asc'></i>
		</th>
		<th class="center valign col-md-1" title='Estoque em Falta é o Estoque Mínimo menos o Estoque'>
			Estoque em Falta
			<br>
			&nbsp;<i class='fa fa-arrow-down pointer' title='Ordenar Crescente' style='font-size: 0.8em; opacity: 0;' attr='desc'></i>
			&nbsp;<i class='fa fa-arrow-up pointer' title='Ordenar Decrescente' style='font-size: 0.8em; opacity: 0;' attr='asc'></i>
		</th>
		<? if ($_modulo == 'estoqueformuladosvenda') { ?>
			<th class="center valign col-md-1" title='Estoque em Falta é o Estoque Mínimo menos o Estoque'>
				Estoque Com Reserva
				<br>
				&nbsp;<i class='fa fa-arrow-down pointer' title='Ordenar Crescente' style='font-size: 0.8em; opacity: 0;' attr='desc'></i>
				&nbsp;<i class='fa fa-arrow-up pointer' title='Ordenar Decrescente' style='font-size: 0.8em; opacity: 0;' attr='asc'></i>
			</th>
		<? } ?>
		<th class="center valig col-md-1">
			Est. Futuro
			<br>
			&nbsp;<i class='fa fa-arrow-down pointer' title='Ordenar Crescente' style='font-size: 0.8em; opacity: 0;' attr='desc'></i>
			&nbsp;<i class='fa fa-arrow-up pointer' title='Ordenar Decrescente' style='font-size: 0.8em; opacity: 0;' attr='asc'></i>
		</th>
		<th class="center valig col-md-1" title='Est. Futuro em Falta é o Estoque Futuro menos o Estoque Mínimo'>
			Est. Fut. em Falta
			<br>
			&nbsp;<i class='fa fa-arrow-down pointer' title='Ordenar Crescente' style='font-size: 0.8em; opacity: 0;' attr='desc'></i>
			&nbsp;<i class='fa fa-arrow-up pointer' title='Ordenar Decrescente' style='font-size: 0.8em; opacity: 0;' attr='asc'></i>
		</th>
		<th class="center valign col-md-1">
			Estoque Mínimo
			<br>
			&nbsp;<i class='fa fa-arrow-down pointer' title='Ordenar Crescente' style='font-size: 0.8em; opacity: 0;' attr='desc'></i>
			&nbsp;<i class='fa fa-arrow-up pointer' title='Ordenar Decrescente' style='font-size: 0.8em; opacity: 0;' attr='asc'></i>
		</th>
		<th class="wdStatus center valign col-md-1">
			Status Lote / Status OP
		</th>
	</thead>
	<tbody class="restblbody">
		<?
		$conteudoexport;
		$conteudoexport = '"Descrição";"Estoque";"Estoque em Falta";"Estoque Com Reserva";"Est. Futuro";"Est. Fut. em Falta";"Estoque Mínimo"';
		$conteudoexport .= "\n";
		foreach ($arrayProdutosEmAlerta as $key => $row) 
		{
			if ($_modulo != 'lotesformuladosmeio' && $_modulo != 'lotealertapesqdes') 
			{
				$novoorcamentolink = "./?_modulo=".$row['modulo']."&_acao=i";
				$titulo = "Nova Formalização";
				$titulomodulo = "Formalização";
			} 

			$lotesFormalizacaoEmQuarententa = EstoqueFormuladosController::buscarLoteEmQuarentenaProdutoEmALerta($idUnidadePadrao, $row["idprodserv"]);
			$lotesFormalizacaoEmAndamento = EstoqueFormuladosController::buscarLotesDeFormalizacaoEmandamento($idUnidadePadrao, $row["idprodservformula"], $row["idprodserv"]);
			$dadosConsumoFormula =  EstoqueFormuladosController::buscarDadosConsumoFormula($row['idprodservformula']);
			$diasConsumoLote =  EstoqueFormuladosController::buscarPrimeiroDiaConsumoLote($row['idprodserv'], $row["idunidadeest"], $row['idprodservformula']);

			$estmin = floatval(str_replace(",", ".", $dadosConsumoFormula['estmin']));
			$valor1 = (round(floatval($dadosConsumoFormula['mediadiaria']) * floatval($dadosConsumoFormula['tempocompra']), 2));
			$valor2 = (round((floatval($estmin) - floatval($dadosConsumoFormula['vqtdest'])), 2));
			$sugcp = ($valor1 + $valor2);
			$sugestao2 =  $sugcp - $row["y"];
			$pendprod = 'N';
			$strqua = "";

			$estoque = empty($row["estmin_exp"]) ? number_format(tratanumero($row["total"]), 2, ',', '.') : recuperaExpoente(tratanumero($row["total"]), $row["estmin_exp"]);
			$estoqueMinAutom = empty($row["estmin_exp"]) ? number_format(tratanumero($row["estminautomatico"]), 2, ',', '.') : recuperaExpoente(tratanumero($row["estminautomatico"]), $row["estmin_exp"]);
			$estoqueMin = empty($row["estmin_exp"]) ? number_format(tratanumero($row["estmin"]), 2, ',', '.') : recuperaExpoente(tratanumero($row["estmin"]), $row["estmin_exp"]);
			$qtdSolicitada = empty($row["estmin_exp"]) ? number_format(tratanumero($row["y"]), 2, ',', '.') : recuperaExpoente(tratanumero($row["y"]), $row["estmin_exp"]);

			if (empty($row['idloteprod']) and (($row['total'] + $row['quar'] <= $row['estmin']))) {
				//$cortr = '#FDD0C7'; //vermelho

			} elseif ($row['total'] + $row["y"] < $row['estmin'] and !empty($row['idloteprod'])) {
				//$cortr = '#FDD0C7'; //vermelho
				$pendprod = 'Y';
			} else {
				if ($row['total'] + $row['quar'] >= $row['estmin']) {
					$strqua = "QUARENTENA";
				}
				//$cortr = '#9DDBFF'; //azul    
			}
			 
			$estoqueFuturo = $row["total"] + $row["y"] - $row["qtdlotereserva"]; 
			$estoqueFuturofalta = $estoqueFuturo - $row["estmin"]; 
			$estoquefalta = $row["total"]-$row["estmin"];

			$estoqueSemReserva =  $row["total"] - $row["qtdlotereserva"]; 	
			$estoqueFuturoSemReserva =	$estoqueFuturo - $row["qtdlotereserva"]; 		

			if($estoqueFuturofalta < 1){
				$cortr = '#FDD0C7'; //vermelho
				$pendprod = 'Y';
				$faltaFuturo = $faltaFuturo + 1;
			}else{
				$cortr = '#98FB98'; //verde
				$falta = $falta + 1;
			}
			
			if($row['idempresa'] != $idempresaAnt && count(explode(",", $idUnidadePadrao)) > 1)
			{
				?>
				<tr class="tituloEmpresa">
					<td colspan="9"><?=traduzid('empresa', 'idempresa', 'nomefantasia', $row['idempresa'], false);?></td>
				</tr>
				<?
				$idempresaAnt = $row['idempresa'];
			}
			?>

			<tr style="background-color: <?=$cortr?>;">
				<!-- Descrição -->
				<td class="valign  col-md-3" filterValue="<?=$row["descr"] ?>">
					<a title="Cadastro do produto" target="_blank" href="./?_modulo=prodserv&_acao=u&idprodserv=<?=$row['idprodserv'] ?>"><?=$row['descr'] ?>-<?=$row['rotulo'] ?></a>
				</td>

				<!-- Estoque -->
				<td class="center valign col-md-1" filterValue="<?=$estoque?>">
					<?=$estoque?>
				</td>	
				
				<!-- Estoque em Falta -->
				<? $estoqueFaltaTd = empty($row["estmin_exp"]) ? number_format(tratanumero($estoquefalta), 2, ',', '.') : recuperaExpoente(tratanumero($estoquefalta), $row["estmin_exp"]); ?>
				<td class="center valign col-md-1" filterValue="<?=$estoquefalta ?>">
					<?=$estoqueFaltaTd?>
				</td>	

				<!-- Estoque Com Reserva -->
				<? 
				$estoqueComReserva = empty($row["estmin_exp"]) ? number_format(tratanumero($estoqueSemReserva), 2, ',', '.') : recuperaExpoente(tratanumero($estoquefalta), $row["estmin_exp"]);
				if ($_modulo == 'estoqueformuladosvenda') { ?>
					<td class="center valign col-md-1" filterValue="<?=$estoqueComReserva ?>">
						<?=$estoqueComReserva?>
					</td>
				<? } ?>
				
				<!-- Est. Futuro -->
				<? $estoqueFuturoTD = empty($row["estmin_exp"]) ? number_format(tratanumero($estoqueFuturo), 2, ',', '.') : recuperaExpoente(tratanumero($estoqueFuturo), $row["estmin_exp"]); ?>
				<td class="center valign col-md-1" filterValue="<?=$estoqueFuturo?>">
					<?=$estoqueFuturoTD?>
				</td>	
				
				<!-- Est. Futuro em Falta -->
				<? $estoqueFuturofaltaTd =  empty($row["estmin_exp"]) ? number_format(tratanumero($estoqueFuturofalta), 2, ',', '.') : recuperaExpoente(tratanumero($estoqueFuturofalta), $row["estmin_exp"]); ?>
				<td class="center valign col-md-1" filterValue="<?=$estoqueFuturofalta?>">
					<?=$estoqueFuturofaltaTd?>
				</td>
				
				<!-- Estoque Mínimo -->
				<td class="center valign col-md-1" filterValue="<?=$estoqueMin?>">
					<?=$estoqueMin?>
					<i class="fa fa-pencil btn-lg pointer" style="padding-left: 10px;" title="Editar Estoque Mínimo" onclick="alterarValorCampos('estmin', '<?=$estoqueMin?>', 'prodservformula', <?=$row['idprodservformula'] ?>,  'Estoque Mínimo')"></i>
				</td>

				<!-- Status Lote / Status OP -->
				<td class="center valign col-md-2" filterValue="">
					<? if (!empty($row["idloteprod"])) {
						foreach ($lotesFormalizacaoEmAndamento as $key => $rowqua1) {
							if ($_GET['_modulo'] == "lotealertapesqdes") { 
									if(strpos($linklote,'lote') !== false){
										$idmod = 'idlote';
									}else{
										$idmod = 'idformalizacao';
									}
								?>
								<span>
									<a class="twrap" title="<? $titulomodulo ?>" target="_blank" href="./?_modulo=<?=$linklote ?>&_acao=u&<?=$idmod?>=<?=$rowqua1["idlote"] ?>">
										<?=$rowqua1["status"] ?> / <?=$rowqua1["rotulo"] ?> - <?=$rowqua1['partida'] ?>
									</a><br>
								</span>

							<? } else { 
									if(strpos($row['modulo'],'lote') !== false){
										$idmod = 'idlote';
									}else{
										$idmod = 'idformalizacao';
									}
							?>
								<span>
									<a class="twrap" target="_blank" href="./?_modulo=<?=$row['modulo']?>&_acao=u&<?=$idmod?>=<?=$rowqua1["idformalizacao"] ?>&_idempresa=<?=$row['idempresa'] ?>">
										<?=$rowqua1["status"] ?> / <?=$rowqua1["rotulo"] ?> - <?=$rowqua1['partida']  ?> 
									</a><br>
								</span>
							<? }
						}

						if ($pendprod == 'Y') { ?>
								<br>
								<a title="<?=$titulo ?>" target="_blank" class="fa fa-plus-circle fa-1x verde pointer" href="<?=$novoorcamentolink ?>&_idempresa=<?=$row['idempresa'] ?>&idprodserv=<?=$row['idprodserv'] ?>&_idprodservformula=<?=$row['idprodservformula'] ?>"></a>
							<? }
					} else if ($strqua == "QUARENTENA") {

						echo "<!-- Entrei Quarentena -->";
						foreach ($lotesFormalizacaoEmQuarententa as $key => $rowqua) { ?>
								<a title="<?=$rowqua['partida'] ?>/<?=$rowqua['exercicio'] ?>" target="_blank" href="./?_modulo=<?=$linklote ?>&_acao=u&idlote=<?=$rowqua['idlote'] ?>">QUARENTENA</a>
							<? }
					} else { ?>
							<a title="<?=$titulo ?>" target="_blank" class="fa fa-plus-circle fa-1x verde pointer" href="<?=$novoorcamentolink ?>&_idempresa=<?=$row['idempresa'] ?>&idprodserv=<?=$row['idprodserv'] ?>&_idprodservformula=<?=$row['idprodservformula'] ?>"></a>
						<? } ?>
				</td>
			</tr>
			<? 
			
			$conteudoexport .= '"'.$row['descr'].' - '.$row['rotulo'].'";"'.$estoque.'";"'.$estoqueFaltaTd.'";"'.$estoqueComReserva.'";"'.$estoqueFuturoTD.'";"'.$estoqueFuturofaltaTd.'";"'.$estoqueMin.'"';
			$conteudoexport .= "\r\n";
		} 
		?>
	</tbody>
</table>
<div id="alterarValor" style="display: none">
	<table class="table table-hover">
		<tr>
			<td>#namerotulo:</td>
			<td>
				<input name="#name_idtabela" value="#valor_idtabela" class="size7" type="hidden">
				<input  id="campoinput" name="#name_campo_input" value="#valor_campo" class="size7" type="text">
				<? $sqlca = EstoqueFormuladosController::tempoReposicao(); ?>
				<select id="camposelect" class="size10" name="#name_campo_select">
					<? fillselect($sqlca); ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Justificativa:</td>
			<td>
				<select name="#name_justificativa" onchange="alteraoutros(this,'<?= $tabela ?>')" vnulo class="size50">
					<? fillselect(EstoqueFormuladosController::$statusEstoque); ?>
				</select>
			</td>
		</tr>
	</table>
</div>

<? 
if (!empty($_GET["reportexport"])) {
    ob_end_clean(); //não envia nada para o browser antes do termino do processamento

    /* Gerar o nome do arquivo para exportar
	 * Substitui qualquer caractere estranho pelo sinal de '_'
	 * Caracteres que NAO SERAO substituidos:
	 *   - qualquer caractere de A a Z (maiusculos)
	 *   - qualquer caracteres de a a z (minusculos)
	 *   - qualquer caractere de 0 a 9
	 *   - e pontos '.'
	 */
    $infilename = 'produtos_alerta';
    $infilename = preg_replace("/[^A-Za-z0-9s.]/", "", $infilename);
    //gera o csv

	header('Content-Encoding: UTF-8');
    header('Content-Type: text/csv; charset=utf-8' );
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header("Content-Disposition: attachment; filename=".$infilename.".csv");
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo "\xEF\xBB\xBF";
	
	echo $conteudoexport;
	exit();
}
require_once("../form/js/estoqueformuladosvenda_js.php"); 
?>