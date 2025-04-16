<?
require_once("../inc/php/validaacesso.php");
if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "device";
$pagvalcampos = array(
	"iddevice" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from device where iddevice = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

// CONTROLLERS
require_once(__DIR__ . "/controllers/tag_controller.php");
require_once(__DIR__ . "/controllers/device_controller.php");

$log = 'Y';
$checkedob = "";

if ($_1_u_device_log == 'Y') {
	$log = 'N';
	$checkedob = "checked";
}

$elementosHTML = [];

if ($_1_u_device_url) {
	$elementosHTML['device_url'] = "<a href='$_1_u_device_url' target='_blank'>
										<img style='width:24px' src='../inc/img/d1.png' alt='Monitoramento' srcset='' title='Monitoramento' />
									</a>";
}

if ($_1_u_device_urlplanta) {
	$elementosHTML['device_urlplanta'] = "<a href='$_1_u_device_urlplanta' target='_blank'>
												<img style='width:24px' src='../inc/img/d2.png' alt='Planta' srcset='' title='Planta'>
											</a>";
}

if ($_1_u_device_urlplanta2) {
	$elementosHTML['device_urlplanta2'] = "<a href='$_1_u_device_urlplanta2' target='_blank'>
												<img style='width:24px' src='../inc/img/d3.png' alt='Monitoramento Planta' srcset='Monitoramento Planta' title='Monitoramento Planta'>
											</a>";
}

if ($_1_u_device_idtag) {
	$elementosHTML['link_tag'] = "<a title='Abrir Tag' class='fa fa-bars fade pointer hoverazul' href='?_acao=u&_modulo=tag&idtag=$_1_u_device_idtag' target='_blank'></a>";
}

if ($_1_u_device_iddevice) {
	$elementosHTML['campos']['log'] = "<td align='right'>Log:</td>
										<td>
											<input name='_1_{$_acao}_device_log' type='checkbox' atval='$log' value='$_1_u_device_log' $checkedob iddevice='$_1_u_device_iddevice' onclick='flglog(this)'>
										</td>";
}

$tabaud = "device";

?>
<style>
	i.fa {
		display: inline-block;
		border-radius: 60px;
		box-shadow: 0px 0px 2px #888;
		padding: 0.5em 0.6em;
		margin-right: 4px;
	}

	@media print {
		.hideshowtable {
			display: block !important;
		}

		#cbContainer {
			display: none !important;
		}

		#cbModuloForm,
		.modal-header {
			display: none !important;
		}

		.modal-content {
			border: none;
		}

		.modal-body {
			overflow: visible !important;
		}

		body {
			font-family: "Helvetica Neue", Helvetica, Arial, sans-serif !important;
		}

		table {
			break-inside: auto;
		}

		table,
		tr,
		td {
			border: 2px solid #fff;
		}

		thead {
			font-weight: bold;
		}

		.footer,
		.header {
			position: fixed;
			top: 0;
			left: 0;
			height: 40px;
			width: 100%;
			background-color: white;
			margin-left: 25px;
		}

		.footer {
			top: auto;
			bottom: 0;
		}

		@page {
			margin: 1cm;
			counter-increment: page;
			counter-reset: page 1;
		}
	}
</style>
<div class="col-md-12">
	<div class="panel panel-default">
		<div class="panel-heading">
			<table>
				<tr>
					<td>
						<input name="_1_<?= $_acao ?>_device_iddevice" type="hidden" value="<?= $_1_u_device_iddevice ?>" readonly='readonly'>
					</td>

					<td>Tag</td>
					<td>
						<select name="_1_<?= $_acao ?>_device_idtag" class="select-picker" value="<?= $_1_u_device_idtag ?>" data-live-search="true">
							<option value=""></option>
							<? fillselect(TagController::buscarTagPorIdTagClassEIdTagTipo(1, 83), $_1_u_device_idtag); ?>
						</select>
						<?= $elementosHTML['link_tag'] ?>
					</td>
					<td> Status</td>
					<td>
						<select name="_1_<?= $_acao ?>_device_status">
							<? fillselect(DeviceController::$status, $_1_u_device_status); ?>
						</select>
					</td>
					<td> Subtipo</td>
					<td>
						<select name="_1_<?= $_acao ?>_device_subtipo">
							<? fillselect(DeviceController::$subTipo, $_1_u_device_subtipo); ?>
						</select>
					</td>
					<?= $elementosHTML['campos']['log'] ?>
					<td>&nbsp;
					</td>
					<td align='right'>Ordem:</td>
					<td>
						<input name="_1_<?= $_acao ?>_device_ordem" type='number' value='<?= $_1_u_device_ordem ?>'>
					</td>
					<td class="hide">Supervisório:</td>
					<td class="hide">
						<?= $elementosHTML['device_url'] ?>
					</td>
					<td class="hide">
						<?= $elementosHTML['device_urlplanta'] ?>
					</td>
					<td class="hide">
						<?= $elementosHTML['device_urlplanta2'] ?>
					</td>
				</tr>
			</table>
		</div>
		<? if ($_1_u_device_iddevice) { ?>
			<div class="panel-body">
				<div class="row">

					<div class="col-md-6">
						<table style="width:100%">

							<tr>
								<td style="width:200px">Mac Address</td>
								<td>
									<input name="_1_<?= $_acao ?>_device_mac_address" type="text" value="<?= $_1_u_device_mac_address ?>">
								</td>
							</tr>
							<tr>

								<td>Localização</td>
								<td>
									<input name="_1_<?= $_acao ?>_device_descricao" type="text" value="<?= $_1_u_device_descricao ?>">
								</td>
							</tr>
							<tr>
								<td>Ip</td>
								<td>
									<input name="_1_<?= $_acao ?>_device_ip_hostname" type="text" size="4" value="<?= $_1_u_device_ip_hostname ?>">
								</td>
							</tr>
							<tr>
								<td class="hide">Tipo</td>
								<td class="hide">
									<select name="_1_<?= $_acao ?>_device_tipo">
										<? fillselect(DeviceController::$tipo, $_1_u_device_tipo); ?>
									</select>

								</td>
							</tr>
							<tr class="hide">
								<td>Ambiente</td>
								<td>
									<select name="_1_<?= $_acao ?>_device_ambiente">
										<? fillselect(DeviceController::$ambiente, $_1_u_device_ambiente); ?>
									</select>
								</td>
							</tr>
						</table>
					</div>
					<div class="col-md-3"></div>
					<div class="col-md-3 divacao">
						<table style="width:100%; border:1px solid #ddd;background:#eee">
							<tr style="background:#ddd;">
								<td colspan="2">
									Ações
								</td>
							</tr>
							<tr>
								<td><i class="fa fa-refresh acaom5" id="reiniciar" data-acaom5="reiniciar" data-conclusao="M5 Reiniciado"></i>Reiniciar</td>
								<td><i class="fa fa-power-off acaom5" data-acaom5="desligar" data-conclusao="M5 Desligado"></i> Desligar</td>
							</tr>

							<tr>
								<td><i class="fa fa-repeat acaom5" data-acaom5="trocaciclo" data-conclusao="Ciclo Alterado"></i> Alterar Ciclo</td>
								<td><i class="fa fa-ban acaom5" data-acaom5="cancelaciclo" data-conclusao="Ciclo Cancelado"></i> Cancelar Ciclo</td>
							</tr>
							<tr>
							<!--	<td><a href="http://<?= $_1_u_device_ip_hostname ?>/log" style="color:inherit" target="_blank"><i class="fa fa-file-text-o"></i></a> Log</td> -->
								<td><i class="fa fa-refresh acaom5" id="statuslog" data-acaom5="statuslog" data-conclusao="M5 statuslog"></i>Reiniciar</td>
								<td><i class="fa fa-trash acaom5" data-acaom5="apagalog" data-conclusao="Log excluído"></i> Apagar Log</td>
							</tr>

							<tr>
								<td><i class="fa fa-line-chart acaom5" data-acaom5="leituras" data-conclusao="desligado"></i> Leituras</td>
								<td><i class="fa fa-upload dz-clickable pointer azul " style="display: none;" id="certanexo" title="Clique para atualizar a versão!"></i> Atualizar Versão</td>
							</tr>

						</table>
					</div>
				</div>

			</div>
		<? } ?>
	</div>
</div>
<? if ($_1_u_device_iddevice) { ?>
	<div class="col-md-3">
		<div id="divconfiguracoes" class="panel panel-default">
			<div class="panel-heading">Configurações</div>
			<div class="panel-body">
				<table style="width:100%">
					<tr>
						<td>Delay</td>
						<td>
							<input name="_1_<?= $_acao ?>_device_delay" type="text" size="4" value="<?= $_1_u_device_delay ?>">
						</td>
					</tr>
					<tr>
						<td>Modelo</td>
						<td>
							<select name="_1_<?= $_acao ?>_device_modelo">
								<option value=""></option>
								<? fillselect(DeviceController::$modelo, $_1_u_device_modelo); ?>
							</select>
						</td>
					</tr>
					<tr>
						<td> Versão</td>
						<td>
							<input readonly="readonly" name="_1_<?= $_acao ?>_device_versao" type="text" value="<?= $_1_u_device_versao ?>" style="background-color:#eee">
						<td>
							<i class="fa fa-upload dz-clickable pointer azul" style="display: none;" id="certanexo" title="Clique para atualizar a versão!"></i>
						</td>
						</td>
					</tr>
					<tr>
						<td style="width: 35%;">Tag Diferencial</td>
						<td>
							<select name="_1_<?= $_acao ?>_device_iddeviceref">
								<? fillselect(DeviceController::buscarTodosDevices(), $_1_u_device_iddeviceref); ?>
							</select>
							<a title="Abrir M5" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=device&iddevice=<?= $_1_u_device_iddeviceref ?>" target="_blank"></a>
						</td>

					</tr>
				</table>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div id="divciclosesensores" class="panel panel-default">
			<div class="panel-heading">Ciclos e Sensores <a class="fa fa-search azul pointer hoverazul" title="Histórico" href="<?= $_1_u_device_url2; ?>" target="_blank"></a></div>
			<div class="panel-body">
				<table style="width:100%">
					<tr>
						<td>Sensores</td>
						<td>
							<select onchange="inserirSensor(this);">
								<option value=""></option>
								<? fillselect(DeviceController::buscarDeviceSensor()); ?>
							</select>
							<? foreach (DeviceController::buscarDeviceSensorPorIdDevice($_1_u_device_iddevice) as $deviceSensor) { ?>
						<td style="float: left;border: 1px solid #ccc; padding: 2px 4px;margin:2px;text-transform:uppercase;font-size:10px;">
							<?= $deviceSensor["nomesensor"] ?>
							<a title="Abrir Ciclo" class="fa fa-bars fade pointer hoverazul" href="?_modulo=devicesensor&_acao=u&iddevicesensor=<?= $deviceSensor["iddevicesensor"] ?>" target="_blank"></a>
							<span onclick="removerSensor(<?= $deviceSensor['iddevicesensor'] ?>)" class="pointer" style="color:red;font-weight: bold;">x</span>
						</td>
					<? } ?>
					</td>
					</tr>
					<tr>
						<td colspan="6">
							<hr>
						</td>
					</tr>
					<tr>
						<td>Ciclos</td>
						<td>
							<select name="idm5" onchange="inserirCiclo(this);" id="_m5">
								<option value=""></option>
								<? fillselect(DeviceController::buscarCiclosPorIdDevice($_1_u_device_iddevice)); ?>
							</select>
							<? foreach (DeviceController::buscarCiclosDeDeviceObjetoPorIdDevice($_1_u_device_iddevice) as $device) {
								$ciclo .= $device['iddeviceciclo'] . " para ciclo " . $device['nomeciclo'] . '\n'; ?>
						<td style="float: left;border: 1px solid #ccc; padding: 2px 4px;margin:2px;text-transform:uppercase;font-size:10px;">
							<?= $device["nomeciclo"] ?>
							<a title="Abrir Ciclo" class="fa fa-bars fade pointer hoverazul" href="?_modulo=deviceciclo&_acao=u&iddeviceciclo=<?= $device["iddeviceciclo"] ?>" target="_blank"></a>
							<span onclick="removerCiclo(<?= $device['iddeviceobj'] ?>)" class="pointer" style="color:red;font-weight: bold;">x</span>
						</td>
					<? } ?>
					</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div id="divoffsets" class="panel panel-default">
			<div class="panel-heading">Offsets</div>
			<div class="panel-body">
				<table style="width:100%">
					<tr>
						<td style="width:140px">Temperatura:</td>
						<td>
							<input name="_1_<?= $_acao ?>_device_calib_temp" type="text" size="4" value="<?= $_1_u_device_calib_temp ?>">
						</td>
					</tr>
					<tr>
						<td>Umidade:</td>
						<td>
							<input name="_1_<?= $_acao ?>_device_calib_umid" type="text" size="4" value="<?= $_1_u_device_calib_umid ?>">
						</td>
					</tr>
					<tr>
						<td>Pressão:</td>
						<td>
							<input name="_1_<?= $_acao ?>_device_calib_press" type="text" size="4" value="<?= $_1_u_device_calib_press ?>">
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
<? } ?>
<div class="hide">
	<div class="panel panel-default">
		<div class="panel-heading">Links</div>
		<div class="panel-body">
			<table style="width:100%">
				<tr>
					<td style="width:140px">Grafana M5</td>
					<td>
						<input name="_1_<?= $_acao ?>_device_url" type="text" size="4" value="<?= $_1_u_device_url ?>">
					</td>
				</tr>
				<tr>
					<td>Grafana Planta</td>
					<td>
						<input name="_1_<?= $_acao ?>_device_urlplanta2" type="text" size="4" value="<?= $_1_u_device_urlplanta2 ?>">
					</td>
				</tr>
				<tr>
					<td>Grafana Histórico</td>
					<td>
						<input name="_1_<?= $_acao ?>_device_urlplanta" type="text" size="4" value="<?= $_1_u_device_urlplanta ?>">
					</td>
				</tr>
				<tr>
					<td>Supervisório</td>
					<td>
						<input name="_1_<?= $_acao ?>_device_url2" type="text" size="4" value="<?= $_1_u_device_url2 ?>">
					</td>
				</tr>

			</table>
		</div>
	</div>
</div>
</div>
<? if ($_1_u_device_iddevice) { ?>
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-body">
				<div class="row col-md-12">
					<div class="col-md-1 nowrap">Criado Por:</div>
					<div class="col-md-5"><?= ${"_1_u_" . $tabaud . "_criadopor"} ?></div>
					<div class="col-md-1 nowrap">Criado Em:</div>
					<div class="col-md-5"><?= ${"_1_u_" . $tabaud . "_criadoem"} ?></div>
				</div>
				<div class="row col-md-12">
					<div class="col-md-1 nowrap">Alterado Por:</div>
					<div class="col-md-5"><?= ${"_1_u_" . $tabaud . "_alteradopor"} ?></div>
					<div class="col-md-1 nowrap">Alterado Em:</div>
					<div class="col-md-5"><?= ${"_1_u_" . $tabaud . "_alteradoem"} ?></div>
				</div>
			</div>
		</div>
	</div>
<? } ?>

<? require_once(__DIR__ . "/../form/js/device_js.php") ?>