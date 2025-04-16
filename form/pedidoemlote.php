<?
require_once("../inc/php/validaacesso.php");
require_once(__DIR__ . "/controllers/resultlote_controller.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

require_once(__DIR__ . "/controllers/pedidoemlote_controller.php");


$idempresa = $_GET["idempresa"];
$envioinicio = $_GET["envioinicio"];
$enviofim = $_GET["enviofim"];
$uf = $_GET["uf"];
$idfluxostatus = $_GET["idfluxostatus"];
$idtransportadora = $_GET["idtransportadora"];

echo "<!-- ";
print_r($_GET);
echo " -->";
?>


<style>
	.diveditor {
		border: 1px solid gray;
		background-color: white;
		color: black;
		font-family: Arial, Verdana, sans-serif;
		font-size: 10pt;
		font-weight: normal;
		width: 695px;
		height: 98%;
		word-wrap: break-word;
		overflow: auto;
		padding: 5px;
	}

	.itemestoque {
		width: auto;
		display: inline-block;
		text-align: right;
	}
</style>


<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">Alteração de Pedidos em Lote</div>
			<div class="panel-body">
				<table>

					<tr>
						<td align="right">Empresa:</td>
						<td>
							<select name="idempresa" id="idempresa" class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
								<?
								$resjidempresa = PedidoEmLoteController::buscarEmpresasDoFiltro($_SESSION['SESSAO']['IDPESSOA']);
								foreach ($resjidempresa as $k1 => $rowempresa) {
									$selected = (in_array($rowempresa['idempresa'],explode(",",$idempresa)) != false)?'selected':'';
									echo '<option '.$selected.' data-tokens="' . retira_acentos($rowempresa['empresa']) . '" value="' . $rowempresa['idempresa'] . '" >' . $rowempresa['empresa'] . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right">Envio entre:&nbsp;</td>
						<td class="nowrap">
							<input name="envioinicio" type="text"  value="<?=$envioinicio?>"class="calendario" autocomplete="off">
							<span> e </span><input name="enviofim" value="<?=$enviofim?>" type="text" class="calendario" autocomplete="off">
						</td>
					</tr>
					<tr>
						<td align="right">UF:</td>
						<td>
							<select name="uf" id="uf" class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
								<?
								$resUF = PedidoEmLoteController::buscarUf();
								foreach ($resUF as $k1 => $rowUf) {
									$selected = (in_array($rowUf['uf'],explode(",",$uf)) != false)?'selected':'';
									echo '<option '.$selected.' data-tokens="' . retira_acentos($rowUf['uf']) . '" value="' . $rowUf['uf'] . '" >' . $rowUf['uf'] . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right">Status:</td>
						<td>
							<select name="idfluxostatus" id="idfluxostatus" class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
								<?
								$resstatus = PedidoEmLoteController::buscarFiltrosStatus('pedido');

								foreach ($resstatus as $k1 => $rowstatus) {
									$selected = (in_array($rowstatus['idfluxostatus'],explode(",",$idfluxostatus)) != false)?'selected':'';
									echo '<option '.$selected.' data-tokens="' . retira_acentos($rowstatus['rotulo']) . '" value="' . $rowstatus['idfluxostatus'] . '" >' . $rowstatus['rotulo'] . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right">Transportadora:</td>
						<td>
							<select name="idtransportadora" id="idtransportadora" class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
								<?
								$restransp = PedidoEmLoteController::buscarTransportadoras();

								foreach ($restransp as $k1 => $rowtransp) {
									$selected = (in_array($rowtransp['idpessoa'],explode(",",$idtransportadora)) != false)?'selected':'';
									echo '<option '.$selected.' data-tokens="' . retira_acentos($rowtransp['nome']) . '" value="' . $rowtransp['idpessoa'] . '" >' . $rowtransp['nome'] . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
				</table>
				<div class="row">
					<div class="col-md-11"></div>
					<div class="col-md-1">
						<button id="cbPesquisarx" class="btn btn-primary btn-xs" onclick="pesquisar()">
							<i class="fa fa-search"></i>&nbsp;Pesquisar
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?if ($envioinicio && $enviofim) {?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">Alterar</div>
			<div class="panel-body">
				<table>
					<tr id="trstatus" class="">
						<td align="right">Status:</td>
						<td>
							<select name="statusnovo" style="width: 250px;" id="status" vnulo>
							<option value=""></option>
							<?
								$resstatus = PedidoEmLoteController::toFillSelect(PedidoEmLoteController::buscarFiltrosStatus('pedido'));
								fillselect ($resstatus)
							?>
							</select>
						</td>
					</tr>
					<tr >
						<td align="right">Transportadora:</td>
						<td>
							<select name="transportadora" id="transportadora">
								<option value=""></option>
								<?
								$resstatus = PedidoEmLoteController::toFillSelect(PedidoEmLoteController::buscarTransportadoras());
								fillselect ($resstatus);
								?>
							</select>
						</td>
					</tr>
					<tr >
						<td align="right"></td>
						<td>
							<button class="btn btn-xs btn-success" onclick="alteraTodos()">  <i class="fa fa-circle"></i> Alterar Todos</button>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<?
}
if ($envioinicio && $enviofim) {
	$and = '';
	if ($idempresa) {
		$claus = $and . "idempresa in ($idempresa)";
		$and = ' and ';
	}
	if ($envioinicio && $enviofim) {
		$envioinicio = implode('-', array_reverse(explode('/', $envioinicio)));
		$enviofim = implode('-', array_reverse(explode('/', $enviofim)));
		$claus = $claus . $and . "(envio between '$envioinicio' and '$enviofim')";
		$and = ' and ';
	}
	if ($idfluxostatus) {
		$claus = $claus . $and . "idfluxostatus in ($idfluxostatus)";
		$and = ' and ';
	}
	if ($uf) {
		$uf = explode(',', $uf);
		$uftratado = array();
		foreach ($uf as $estado) {
			array_push($uftratado, "'" . $estado . "'");
		}
		$uf = implode(",", $uftratado);
		$claus = $claus . $and . "uf in ($uf)";
		$and = ' and ';
	}
	if ($idtransportadora) {
		$claus = $claus . $and . "idtransportadora in ($idtransportadora)";
		$and = ' and ';
	}
	$sql = "SELECT idnf,
						nnfe,
						envio,
						nitemped,
						cliente,
						sigla,
						qvol,
						pesob,
						enderecototal,
						cidade,
						uf,
						transportadora,
						total,
						status,
						idfluxostatus,
						idempresa
				FROM laudo.vw8relped
				WHERE
					$claus  order by uf asc, cidade asc, transportadora asc, envio asc";
	echo "<!--".$sql."-->";
	$rep = PedidoEmLoteController::executaConsulta($sql);

	if ($rep !== false) { ?>
		<div class="col-md-12 row" id="conteudo_relatorio" style="margin-top: 20px;">
			<div class="col-md-6" style="text-align: left; font-size: 9px;" class="n_linhas">
				<span id="nlinha"><?= $rep->numRows() ?> Registros encontrados</span>
			</div>
			<div class="col-md-6" style="text-align: right;" class="n_linhas">
				<span id="nlinha">
					Marcar Todos: <input class="form-group" onclick="marcarTodos(this)" type="checkbox" id="marcartodos">
				</span>
			</div>
			<div class="loader hide col-md-12" style="margin-left: auto;margin-right: auto"></div>
			<table class="col-md-12">
				<?
				$uf = '';
				foreach ($rep->data as $k => $row) {
					$cabeça = '<tr style="height: 30px; border: 1px; background-color: rgb(239, 235, 235); font-size: 11px; font-weight: bold;" class="header">
						<td class="header" style="border: 1px solid rgb(192, 192, 192); background-color: rgb(239, 235, 235); height: 30px; font-size: 11px; font-weight: bold;">Nº Pedido</td>
						<td class="header" style="border: 1px solid rgb(192, 192, 192); background-color: rgb(239, 235, 235); height: 30px; font-size: 11px; font-weight: bold;">Nº NFe</td>
						<td class="header" style="border: 1px solid rgb(192, 192, 192); background-color: rgb(239, 235, 235); height: 30px; font-size: 11px; font-weight: bold;">Envio</td>
						<td class="header" style="border: 1px solid rgb(192, 192, 192); background-color: rgb(239, 235, 235); height: 30px; font-size: 11px; font-weight: bold;">O.C. Cliente</td>
						<td class="header" style="border: 1px solid rgb(192, 192, 192); background-color: rgb(239, 235, 235); height: 30px; font-size: 11px; font-weight: bold;">Cliente</td>
						<td class="header" style="border: 1px solid rgb(192, 192, 192); background-color: rgb(239, 235, 235); height: 30px; font-size: 11px; font-weight: bold;">Empresa</td>
						<td class="header" style="border: 1px solid rgb(192, 192, 192); background-color: rgb(239, 235, 235); height: 30px; font-size: 11px; font-weight: bold;">Qtd. Vol.</td>
						<td class="header" style="border: 1px solid rgb(192, 192, 192); background-color: rgb(239, 235, 235); height: 30px; font-size: 11px; font-weight: bold;">Endereço</td>
						<td class="header" style="border: 1px solid rgb(192, 192, 192); background-color: rgb(239, 235, 235); height: 30px; font-size: 11px; font-weight: bold;">Cidade</td>
						<td class="header" style="border: 1px solid rgb(192, 192, 192); background-color: rgb(239, 235, 235); height: 30px; font-size: 11px; font-weight: bold;">UF</td>
						<td class="header" style="border: 1px solid rgb(192, 192, 192); background-color: rgb(239, 235, 235); height: 30px; font-size: 11px; font-weight: bold;">Transportadora</td>
						<td class="header" style="border: 1px solid rgb(192, 192, 192); background-color: rgb(239, 235, 235); height: 30px; font-size: 11px; font-weight: bold;">Total</td>
						<td class="header" style="border: 1px solid rgb(192, 192, 192); background-color: rgb(239, 235, 235); height: 30px; font-size: 11px; font-weight: bold;">Status</td>
						<td class="header" style="border: 1px solid rgb(192, 192, 192); background-color: rgb(239, 235, 235); height: 30px; font-size: 11px; font-weight: bold;"><span style="display: flex;align-items: center;justify-content: space-between;">Marcar este Estado: <input class="form-group" onclick="marcarTodosEstado(this)" estado="'.$row['uf'].'" type="checkbox"></span></td>
					</tr>';
					if (empty($uf)) {
						$uf = $row['uf'];
						echo $cabeça;
					}
					if ($uf != $row['uf']) {
						echo $linhasoma =  '<tr><td colspan="15"><table style="width: 100%"><tr style="background-color:#eee; height: 20px; font-size:10px"><td width="33%" ><b>QTD. PEDIDOS.: </b> ' . $nped . ' </td><td width="33%"  align="center" ><b> SOMA QTD. VOL: </b> ' . $sqtdvol . '    </td><td width="33%" align="right">    <b>SOMA PESO BRUTO:</b>    ' . $spbruto . ' </td> </tr>';
						echo $mostrar = '<tr style="background-color:#eee; height: 20px; font-size:10px">  <td >    <b>UF:</b>    ' . $estado . '  - ' . $uf . ' <td colspan="2" align="right"><b>R$  ' . number_format($vtotal, 2, ',', '.') . '</b></td></tr>';
						echo $linhadiv = '<tr style="background-color:#eee; height: 20px;"> </tr></table></td><td></tr>'.$cabeça;
						$vtotal = 0;
						$sqtdvol = 0;
						$spbruto = 0;
						$idnf = $row['idnf'];
						$uf = $row['uf'];
						$nped = 0;
					} else {
						$linhasoma = '';
						$mostrar = '';
						$linhadiv = '';
					}
					$vtotal	=	$vtotal + $row['total'];
					$sqtdvol = $sqtdvol + $row['qvol'];
					$spbruto = $spbruto + $row['pesob'];
					$_ilinha++;
					$nped++;
					$_i = 0;

					if ($row['uf'] == 'AL') {
						$estado = "ALAGOAS";
					} elseif ($row['uf'] == 'BA') {
						$estado = "BAHIA";
					} elseif ($row['uf'] == 'DF') {
						$estado = "DISTRITO FEDERAL";
					} elseif ($row['uf'] == 'ES') {
						$estado = "ESPÍRITO SANTO";
					} elseif ($row['uf'] == 'EX') {
						$estado = "EXTERIOR";
					} elseif ($row['uf'] == 'GO') {
						$estado = "GOIÁS";
					} elseif ($row['uf'] == 'MG') {
						$estado = "MINAS GERAIS";
					} elseif ($row['uf'] == 'MS') {
						$estado = "MATO GROSSO DO SUL ";
					} elseif ($row['uf'] == 'MT') {
						$estado = "MATO GROSSO";
					} elseif ($row['uf'] == 'PE') {
						$estado = "PERNAMBUCO";
					} elseif ($row['uf'] == 'PR') {
						$estado = "PARANÁ";
					} elseif ($row['uf'] == 'RN') {
						$estado = "RIO GRANDE DO NORTE";
					} elseif ($row['uf'] == 'RJ') {
						$estado = "RIO DE JANEIRO";
					} elseif ($row['uf'] == 'RS') {
						$estado = "RIO GRANDE DO SUL";
					} elseif ($row['uf'] == 'SC') {
						$estado = "SANTA CATARINA";
					} elseif ($row['uf'] == 'SP') {
						$estado = "SÃO PAULO";
					} elseif ($row['uf'] == 'RO') {
						$estado = "RONDÔNIA";
					} elseif ($row['uf'] == 'AC') {
						$estado = "ACRE";
					} elseif ($row['uf'] == 'RR') {
						$estado = "RORAIMA";
					} elseif ($row['uf'] == 'PA') {
						$estado = "PARÁ";
					} elseif ($row['uf'] == 'AP') {
						$estado = "AMAPÁ";
					} elseif ($row['uf'] == 'TO') {
						$estado = "TOCANTINS";
					} elseif ($row['uf'] == 'MA') {
						$estado = "MARANHÃO";
					} elseif ($row['uf'] == 'PI') {
						$estado = "PIAUÍ";
					} elseif ($row['uf'] == 'CE') {
						$estado = "CEARÁ";
					} elseif ($row['uf'] == 'SE') {
						$estado = "SERGIPE";
					} elseif ($row['uf'] == 'AM') {
						$estado = "AMAZONAS";
					} elseif ($row['uf'] == 'PB') {
						$estado = "PARAÍBA";
					}?>
					<tr style="height:22px;" class="res">
						<td style="border: 1px solid rgb(192, 192, 192);">
							<a target="_blank" href="/?_modulo=pedidologistica&amp;_acao=u&amp;idnf=<?=$row['idnf']?>"><?=$row['idnf']?></a>
							<input idnf="<?=$row['idnf']?>" type="hidden" value="<?=$row['idnf']?>">
						</td>
						<td style="border: 1px solid rgb(192, 192, 192);"><?=$row['nnfe']?></td>
						<td style="border: 1px solid rgb(192, 192, 192);"><?=dma($row['envio'])?></td>
						<td style="border: 1px solid rgb(192, 192, 192);"><?=$row['nnfe']?></td>
						<td style="border: 1px solid rgb(192, 192, 192);"><?=$row['cliente']?></td>
						<td style="border: 1px solid rgb(192, 192, 192);"><?=$row['sigla']?></td>
						<td style="border: 1px solid rgb(192, 192, 192);"><?=$row['qvol']?></td>
						<td style="border: 1px solid rgb(192, 192, 192);"><?=$row['enderecotoal']?></td>
						<td style="border: 1px solid rgb(192, 192, 192);"><?=$row['cidade']?></td>
						<td style="border: 1px solid rgb(192, 192, 192);"><?=$row['uf']?></td>
						<td style="border: 1px solid rgb(192, 192, 192);"><?=$row['transportadora']?></td>
						<td align="right" style="border: 1px solid rgb(192, 192, 192);"><?=$row['total']?></td>
						<td style="border: 1px solid rgb(192, 192, 192);">
						<?=$row['status']?>
						<input idstatusant="<?=$row['idfluxostatus']?>" type="hidden" value="<?=$row['idfluxostatus']?>">
						<input statusant="<?=$row['status']?>" type="hidden" value="<?=$row['status']?>">
						</td>
						<td align="right" style="border: 1px solid rgb(192, 192, 192);"><input type="checkbox" idnfgo="<?=$row['idnf']?>" estado='<?=$row['uf']?>' class="checkbox_alterar"></td>
					</tr>
				<?}
				// if(){

				// }
				echo $linhasoma =  '<tr><td colspan="15"><table style="width: 100%"><tr style="background-color:#eee; height: 20px; font-size:10px"><td width="33%" ><b>QTD. PEDIDOS.: </b> ' . $nped . ' </td><td width="33%"  align="center" ><b> SOMA QTD. VOL: </b> ' . $sqtdvol . '    </td><td width="33%" align="right">    <b>SOMA PESO BRUTO:</b>    ' . $spbruto . ' </td> </tr>';
				echo $mostrar = '<tr style="background-color:#eee; height: 20px; font-size:10px">  <td >    <b>UF:</b>    ' . $estado . '  - ' . $uf . ' <td colspan="2" align="right"><b>R$  ' . number_format($vtotal, 2, ',', '.') . '</b></td></tr>';
				echo $linhadiv = '<tr style="background-color:#eee; height: 20px;"> </tr></table></td></tr>';
				?>
			</table>
		</div>
<? }
}

?>

<script>
	//comentario
	$('.selectpicker').selectpicker('render');


	function marcarTodos(vthis){
		if ($(vthis).prop('checked')) {
			$('[estado]').prop('checked',true)
		} else {
			$('[estado]').prop('checked',false)
			
		}
	}
	function marcarTodosEstado(vthis){
		if ($(vthis).prop('checked')) {
			$(`[estado="${$(vthis).attr('estado')}"]`).prop('checked',true)
		} else {
			$(`[estado="${$(vthis).attr('estado')}"]`).prop('checked',false)
			
		}
	}

	function pesquisar(){

		var idempresa = $("[name=idempresa]").val()  || "";
		var envioinicio = $("[name=envioinicio]").val()  || "";
		var enviofim = $("[name=enviofim]").val()  || "";
		var uf = $("[name=uf]").val()  || "";
		var status = $("[name=idfluxostatus]").val() || "";
		var idtranportadora = $("[name=idtransportadora]").val() || "";
		var str = "idempresa=" + idempresa + "&envioinicio=" + envioinicio + "&enviofim=" + enviofim + "&uf=" + uf + "&idfluxostatus=" + status +"&idtransportadora="+idtranportadora;

		CB.go(str);
	}

	function alteraTodos(){
		var obj = []
		$('[idnf]').each((i,e) =>{
			if($(`[idnfgo="${$(e).val()}"]`).prop('checked')){
				obj.push({
					"idnf":$(e).val(),
					"statusant":$($('[statusant]')[i]).val(),
					"idstatusant":$($('[idstatusant]')[i]).val(),
					"statusnovo":$('[name=statusnovo]').val(),
					"transportadora":$('[name=transportadora]').val(),
				})
			}
		})
		$.ajax({
                type: "post",
                url: "ajax/atualizacaopedidoemlote.php",
				data: {data: obj},
                success: function(data) { //alert(data);
                    data.trim();
					try {
						data = JSON.parse(data)
						if(data.length){
							for (index in data) {
								alertAtencao(`Nota ${index}: ${data[index]}`);
							}
						}else{
							alertSalvo("Pedidos alterados!");
							window.location.reload();
						}
					} catch (err) {
						console.log(err)
					}
                },
                error: function(objxmlreq) {
                    alert('Erro:<br>' + objxmlreq.status);
                }
            }) //$.ajax
	}


	//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>