<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/amostra_controller.php");
require_once("../form/controllers/pessoa_controller.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}
$idpessoa = $_GET["idpessoa"];
//Parâmetros mandatórios para o carbon
$pagvaltabela = "amostra";
$pagvalcampos = array(
	"idamostra" => "pk"
);
//Recuperar a unidade padrão conforme módulo pré-configurado
$unidadepadrao = getUnidadePadraoModulo($_GET["_modulo"]);

//Recuperar o modulo de resultados associado conforme a unidade
$modResultadosPadrao = getModuloResultadoPadrao($unidadepadrao);
$provisorio = $_GET['provisorio'];

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from amostra where idunidade=" . $unidadepadrao . " and  idamostra = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");

// if($_headers['meuheader']){
// 	$sql
// }

$dataAmostra = explode('/', $_1_u_amostra_dataamostra);
$dataAmostra = $dataAmostra[2].'-'.$dataAmostra[1].'-'.$dataAmostra[0];

//LTM (28/05/2021) - Caso a amostra não tenha status, seta o primeiro quando for no insert o caso
if(empty($_1_u_amostra_status) && strpos($_GET['_modulo'], 'provisorio') == false && $_GET['_modulo'] != 'amostratra'){
	$_1_u_amostra_status = 'ABERTO';
} elseif(empty($_1_u_amostra_status) && (strpos($_GET['_modulo'], 'provisorio') == true || $_GET['_modulo'] == 'amostratra')){
	$_1_u_amostra_status = 'PROVISORIO';
}

//Valida se é provisório para enviar para impressão
//Define a impressora se é para o Provisório ou Permanente - LTM (26-08-2020)
//LTM - 01102020 - 375156: Alterado para que TRA seja impresso no provisório
if($_1_u_amostra_status != 'PROVISORIO' && strpos($_GET['_modulo'], 'provisorio') == false){
	$impressora = 'IMP_DIAGNOSTICO';
} else {
	$impressora = 'IMP_DIAGNOSTICO_PROVISORIO';
}

//LTM - 03/092020 - 370913: Caso seja o módulo Provisório ficará com a cor azul para difenciar dos demais.
if($_acao == "i" && strpos($_GET['_modulo'], 'provisorio') == true){
?>
	<script>
		$("body").addClass("novoprovisorio");
	</script>
	<?
}

//Atualizado para direcionar para validar se é cqd ou não - Lidiane - 28-05-2020
if(strpos($_GET['_modulo'], 'cqd') == true){
	$modulo = 'nucleocqd';
} elseif(strpos($_GET['_modulo'], 'pesqdes') == true){
	$modulo = 'nucleopesqdes';
} else {
	$modulo = 'nucleo';
}

// Contatos vínculados a empresa (cliente) disponíveis para vínculo no responsável coleta.
$arrResponsavelColeta = [];
$responsavelColeta = [];

//JLAL - 12-08-20 **Verifica se tem resultado assinados, se tiver ele bloqueia todos os campos para alteração
//LTM - 16-09-2020 - 373286 - Alterado para que feche se tiver pelo menos um resultado assinado.
if($_1_u_amostra_idamostra){
	$arrResponsavelColeta = PessoaController::buscarContatoPessoa($_1_u_amostra_idpessoa);
	$responsavelColeta = PessoaController::buscarPessoa($_1_u_amostra_idresponsavel);

	$r = AmostraController::contarResultadosAssinados($_1_u_amostra_idamostra);
	if(!empty($_1_u_amostra_idamostra) and $_1_u_amostra_idunidade != 9 and $r >= 1  and $_1_u_amostra_status != "ABERTO"){
		$redonAssi = "readonly='readonly'";
		$disableDta = "disabled='disable'";
		$tredonAssi = "Amostra possui testes assinados.";
	} else {
		$redonAssi = "";
		$tredonAssi = "";
	}
}

if($r['assinado'] >= 1  || $_1_u_amostra_status != "ABERTO"){
	$disableHoraAmostra = "disabled='disable'";
}
if($_1_u_amostra_idamostra){
	$gAmostraDados = AmostraController::buscarTemperaturaAmostra($_1_u_amostra_idamostra, 'temperaturarecebimento');
}

if(empty($gAmostraDados['idamostra']) || $_acao == "i"){
	$rAcao = 'i';
} else {
	$rAcao = 'u';
}

function mostraUnidade(){
	global $_acao, $_1_u_amostra_idunidade, $unidadepadrao;

	if(!empty($_GET["idunidade"]) && $_acao == "i") $_1_u_amostra_idunidade = $_GET["idunidade"];

	if(!empty($_1_u_amostra_idunidade)){

		$unidade = traduzid("unidade", "idunidade", "unidade", $_1_u_amostra_idunidade);
		//Cria cor de back e foreground para a unidade
		$bg = str2Color($unidade);
		$fc = colorContrastYIQ($bg);
	?>
		<input name="_1_<?=$_acao?>_amostra_idunidade" type="hidden" value="<?=$_1_u_amostra_idunidade ?>">
		<span style="color:<?=$fc ?>;background-color:#<?=$bg ?>;" class="label label-default fonte10">
			<?
			echo strtoupper(substr($unidade, 0, 2));
			?>
		</span>
	<?
	} else {

	?>
		<select name="_1_<?=$_acao?>_amostra_idunidade" title="Unidade" tabindex="-99" style="background-color: transparent;" onchange="CB.setPrefUsuario('u',CB.modulo+'.unidadepreferencial',this.value)">
			<? fillselect(AmostraController::buscarUnidadesAtivas(cb::idempresa()), $unidadepadrao); ?></select>
		<?
	}
}

// Configuração de inputs visíveis conforme combinação de tipo e subtipo de amostra
function jsonInputsTipoamostra(){
	global $unidadepadrao, $jsonTelaamostraconf;

	$arrConf = getAmostraConfInputs($unidadepadrao);

	$json = new Services_JSON();
	$jsonTelaamostraconf = json_encode($arrConf["arrcoluna"]);
	return $json->encode($arrConf["arrtipo"]);
}

function jsonObgTipoamostra(){
	global $unidadepadrao, $jsonamostracampos, $jsonamostracamposobgtra;

	$arrConf = AmostraController::buscarAmostracamposPorIdunidade($unidadepadrao);
	$jsonamostracamposobgtra = json_encode($arrConf['config']);
	$jsonamostracampos = json_encode($arrConf['idamostracampos']);
	$json = new Services_JSON();
	return $json->encode($arrConf['colunas']);
}
/*
 * Tipos de amostra disponíveis
 */
function jsonTipoSubtipo(){
	global $unidadepadrao;
	return AmostraController::buscarSubtipoamostraPorIdunidade($unidadepadrao);
}

function jsonServicos(){
	global $unidadepadrao;
	return AmostraController::buscarServicosDaUnidade($unidadepadrao, $_GET['idamostra']);
}

function jsonEspecieFinalidade(){
	return AmostraController::buscarEspeciefinalidade(cb::idempresa());
}

function jsonClientes(){
	global $jsonDetClientes;
	$resc = AmostraController::buscarClientesAmostra(getidempresa('p.idempresa', 'pessoa'));
	$jsonDetClientes = json_encode($resc['arrtmpdet']);
	return json_encode($resc['arrtmp']);
}

$arrCores = AmostraController::$arrCores;
//die($arrCores[0]);

$iAgentes = 0;

function listaTestes(){
	global $_acao, $_1_u_amostra_idamostra, $_1_u_amostra_idpessoa, $arrCores, $modResultadosPadrao, $iAgentes, $unidadepadrao, $_vids, $_1_u_amostra_idunidade, $bloqueioCobranca;
	$duplica = $_GET["duplicaramostra"];
	if($_acao == "u" and !empty($_1_u_amostra_idamostra)){
		$rest = AmostraController::listarTestesDaAmostra($_1_u_amostra_idamostra);
		$_vids = '';
		$virgula = '';
		$i = 10;
		foreach($rest as $k => $r){
			$_vids .= $virgula . $r["idresultado"];
			$virgula = ',';
			$iAgentes += $r["iagentes"];

			$title = ($r["loteetiqueta"]) ? "Lote " . $r["loteetiqueta"] : "Alterar Lote de Impressão";
			$classDrag = ($r["status"] == "ABERTO" && $r["apagavel"] <= 0) ? "dragExcluir" : "";
			$disableteste = ($r["status"] == "ABERTO") ? "" : "readonly='readonly'";

			if($r["cobrar"] == 'Y'){
				//$cobranca='N';
				$checkedob = "checked";
			} else {
				//$cobranca='Y';
				$checkedob = "";
			}
			?>
			<tr class="<?=$classDrag ?>" idresultado="<?=$r["idresultado"]?>" style="border-left-width:2px;border-left-style:solid;border-left-color:<? switch ($r["status"]){
																																						case "ABERTO": ?>#c53632;<? break;
																																																case "PROCESSANDO": ?>#ffc107;<? break;
																																																										case "FECHADO": ?>#40708F;<? break;
																																																																			case "ASSINADO": ?>#3c763d;<? break;
																																																																												default: ?>#333;<? break;
																																																																																	} ?>;">
				<td>
					<a class="fa fa-print pointer cinzaclaro" style="color:<?=$arrCores[$r["loteetiqueta"]]?>;" title="<?=$title ?>" id="cbimp<?=$i ?>" onclick="alteraLoteEtiqueta(<?=$i ?>)"></a>
				</td><? if($_1_u_amostra_idunidade == 1){?>
					<td>
						<?
							//Alteração para alterar apenas a cobrança, para não salvar a amostra e retirar a assinatura.
							//Lidiane (21/05/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=321311
							if($r["cobrar"] == 'Y'){
								$cobrancaobrig = 'N';
								$checkedob = "checked";
							} else {
								$cobrancaobrig = 'Y';
								$checkedob = "";
							}
						?>
						<input type="checkbox" name="_<?=$i ?>_u_resultado_cobrar" atval="<?=$cobrancaobrig ?>" bloqueio="<?=isset($bloqueioCobranca) ? 'true' : 'false'; ?>" value="<?=$r["cobrar"]?>" <?=$checkedob ?> idresultado="<?=$r["idresultado"]?>" onclick="flgcobrar(this)" style="border:0px" title="Cobrar">

					</td>
				<? } ?>
				<td style="white-space: nowrap;">
					<? if($duplica == "Y"){?>
						<input type="hidden" name="_<?=$i ?>_u_resultado_idresultado" value="<?=$r["idresultado"]?>">
						<input type="hidden" name="_<?=$i ?>_u_resultado_loteetiqueta" value="<?=$r["loteetiqueta"]?>">
						<input type="hidden" name="_<?=$i ?>_u_resultado_ord" value="<?=$r["ord"]?>">
						<input type="text" name="_<?=$i ?>_u_resultado_idtipoteste" class="idprodserv" cbvalue="<?=$r["idprodserv"]?>" value="<?=$r["codprodserv"]?>" vnulo <?=$disableteste ?>>
					<? } else {?>
						<input type="hidden" name="_<?=$i ?>_u_resultado_idresultado" value="<?=$r["idresultado"]?>">
						<input type="hidden" name="_<?=$i ?>_u_resultado_loteetiqueta" value="<?=$r["loteetiqueta"]?>">
						<? if(!empty($_GET['_idempresa'])){
							$idempresaLink = '&_idempresa=' . $_GET['_idempresa'];
						} else {
							$idempresaLink = '';
						} ?>
						<a href="?_modulo=<?=$modResultadosPadrao?>&_acao=u&idresultado=<?=$r["idresultado"]?><?=$idempresaLink ?>" target="_blank" style="color:<? switch ($r["status"]){
																																									case "ABERTO": ?>#c53632;<? break;
																																																			case "PROCESSANDO": ?>#ffc107;<? break;
																																																													case "FECHADO": ?>#40708F;<? break;
																																																																						case "ASSINADO": ?>#3c763d;<? break;
																																																																															default: ?>#333;<? break;
																																																																																				} ?>"><?=$r["codprodserv"]?> <span style="font-size:9px;"><?=$r["versao"]?></span></a>
						<? //Alterado porque estava repetindo o valor de alterar a etiqueta "alteraLoteEtiqueta(11)" - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=312223 (Lidiane - 14/05/2020)
						?>
						<input type="hidden" name="#idtipoteste" class="idprodserv" cbvalue="<?=$r["idprodserv"]?>" value="<?=$r["codprodserv"]?>">

					<? } ?>
				</td>
				<td style="white-space: nowrap;">
					<input type="text" <? if($r["status"] == "ASSINADO"){
											echo "readonly";
										} ?> name="_<?=$i ?>_u_resultado_quantidade" value="<?=$r["quantidade"]?>" style="width:30px" placeholder="Quant." vnulo vnumero>
					<? if($r["status"] != "ASSINADO"){?>
						<button class="btn btn-success btn-xs btnqtdteste" onclick="alteraqtdteste(this,<?=$r['idresultado']?>)">Alterar</button>
					<? } ?>
					<?
					unset($_SESSION['auxqt']);

					?>
				</td>
				<?
				if($unidadepadrao == 1){ // Diagnóstico Autógenas 
				?>
					<td>
						<? if($r['logoinmetro'] == 'Y' || !empty($r["idsecretaria"])){?>
							<select name="_<?=$i ?>_u_resultado_idsecretaria" class="idsecretaria" style="font-size:10px;width:100%" placeholder="Secretaria" duplicado>
								<option value=""></option>
								<?

								fillselect(AmostraController::buscarSecretariaPessoa($_1_u_amostra_idpessoa), $r["idsecretaria"]);
								?>
							</select>
						<? } ?>
					</td>
					<?
					if(!empty($_1_u_amostra_idpessoa)){
						$qtdpf = AmostraController::buscarPedidopreferencia($_1_u_amostra_idpessoa);
						if($qtdpf > 0){

					?>
							<td>

								<input name="_<?=$i ?>_u_resultado_npedido" title="Obrigatório informar o pedido de compra." type="text" value="<?=$r["npedido"]?>" class="size6" placeholder="N. Pedido" vnulo>
							</td>
						<?

						} //if($qtdpf>0)
					} else { //if(!empty($_1_u_amostra_idpessoa))
						?>
						<td>

						</td>
					<?
					}
				} else { // Diagnóstico Autógenas 
					if($r["cobrancaobrig"] == 'Y'){
						$cobrancaobrig = 'N';
						$checkedob = "checked";
					} else {
						$cobrancaobrig = 'Y';
						$checkedob = "";
					}
					$qtdcob = AmostraController::verificarSeHaResultadosNaNotafiscalitem($r["idresultado"]);
					// $qtdcob=mysqli_num_rows($rescob);
					if($qtdcob > 0){
						$desabilitaob = " disabled='disabled' ";
					} else {
						$desabilitaob = '';
					}
					?>
					<td align="center">
						<input <?=$desabilitaob ?> type="checkbox" atval="<?=$cobrancaobrig ?>" <?=$checkedob ?> idresultado="<?=$r["idresultado"]?>" style="border:0px" onclick="flgcobrancaobrig(this)" title="Obrigatório cobrança.">
					</td>
				<?
				}
				?>

				<td>
					<?
					$hidemove = "";
					$qtdcob = AmostraController::verificarSeHaResultadosNaNotafiscalitem($r["idresultado"]);
					if($r["status"] !== "ABERTO" or $qtdcob > 0){
						$hidemove = "hidden";
					} ?>
					<i class="fa fa-arrows cinzaclaro hover move <?=$hidemove ?>" title="Excluir teste"></i>
				</td>
			</tr>
		<?
			$i++;
		}
	}
}

function logImpressao(){
	global $_acao, $_1_u_amostra_idamostra;

	if($_1_u_amostra_idamostra){
		$resimp = AmostraController::buscarLogsDeImpetiqueta($_1_u_amostra_idamostra);

		foreach($resimp as $k => $rowimp){
		?>
			<tr class="respreto">
				<td><?=$rowimp['codprodserv']?></td>
				<td><?=$rowimp['criadopor']?></td>
				<td><?=dmahms($rowimp['criadoem'], true) ?></td>
			</tr>
		<?
		}
	}
}

function logTestesCancelados(){
	global $_acao, $_1_u_amostra_idamostra;

	if($_1_u_amostra_idamostra){
		$resimp = AmostraController::buscarLogsTestesCancelados($_1_u_amostra_idamostra);

		foreach($resimp as $k => $rowimp){
		?>
			<tr class="respreto">
				<td><?=$rowimp['codprodserv']?></td>
				<td><?=$rowimp['alteradopor']?></td>
				<td><?=dmahms($rowimp['alteradoem'], true) ?></td>
			</tr>
		<?
		}
	}
}


function logReabertura(){
	global $_acao, $_1_u_amostra_idamostra;

	if($_1_u_amostra_idamostra){

		$resimp = AmostraController::buscarLogDeReabertura($_1_u_amostra_idamostra, $_GET['_modulo']);
		echo '  <div class="webui-popover-content">';
		foreach($resimp as $k => $rowimp){?>

			<div class="row" style="margin: 8px;background: #efefef;border-radius: 4px;">
				<div class="col-md-12" style="font-size:11px;"><b style="text-transform:uppercase;"><?=$rowimp['motivo']?></b>
					<p style="font-size:11px;"><?=$rowimp['motivoobs']?></p>
					<p class="text-right" style="white-space: nowrap;font-size:10px;"><?=$rowimp['criadopor']?> - <?=$rowimp['criadoem']?></p>
				</div>
			</div>
	<? }
		echo '</div>';
	}
}



$arrUltimaAmostra = array();
function dadosUltimaAmostra(){
	global $unidadepadrao;

	$arrA = AmostraController::buscarUltimaAmostra($unidadepadrao);

	return $arrA;
}
$arrUltimaAmostra = dadosUltimaAmostra();

//Coloca data atual em nova amostra e verifica se é inferior à  data atual
$dtUltimaAmostra = date("d/m/Y");
$_1_u_amostra_dataamostra = ($_acao == "i") ? $dtUltimaAmostra : $_1_u_amostra_dataamostra;
//Compara as 2 datas
$dtUltima = new DateTime($arrUltimaAmostra["dataamostra"]);
$dtAtual = new DateTime("now");
$interval = date_diff($dtAtual, $dtUltima);
$intervaloDias = $interval->days;

if($intervaloDias > 0 and $_acao == "i"){
	$stlAlertaDataAnterior = "weight: bold; color: #d9534f;";
	$titleAlertaDataAnterior = "Data da última amostra é inferior à  data de hoje.";
}

//Recuperar hora do DB para permitir calcular o tempo gasto para registro de cada amostra
function getDatahoraDb(){
	$r = AmostraController::buscarHoraDB();
	return $r["dmahms"];
}


//print_r($arrLoteTra);die;

$arrTRAAssociado = getObjeto("amostra", $_1_u_amostra_idamostra, "idamostra");
function linksolfb(){
	global $_1_u_amostra_idamostra;
	$ress = AmostraController::buscarSolfab($_1_u_amostra_idamostra);
	?>
	<div class="papel hover" id="formTra">
		<h5 class="cinzaclaro" style="white-space: nowrap">Solfab vinculadas:</h5>
		<hr>
		<? foreach($ress as $k => $rows){?>
			<h5>
				<a href="?_modulo=solfab&_acao=u&idsolfab=<?=$rows['idsolfab']?>" target="_blank">
					<?=traduzid("lote", "idlote", "concat(partida,'/',exercicio)", $rows['idlote']) ?>
				</a>
			</h5>
	<? }
	} ?>
	<br />
	</div>
	<?


	function desenhaTRA(){
		global $arrTRAAssociado;

		$resa = AmostraController::buscarInformacoesTRAAssocioado($arrTRAAssociado['idamostra']);

	?>
		<div class="papel hover" id="formTra">
			<h5 class="cinzaclaro" style="white-space: nowrap">Agentes isolados:</h5>
			<hr>
			<?
			foreach($resa as $k => $rowa){
				if(($rowa['status'] == "APROVADO" or $rowa['status'] == "PENDENTE") and ($rowa['statusfr'] != 'ESGOTADO')){
				?>
					<h5><a href="?_modulo=<?=$rowa['idobjeto']?>&_acao=u&idlote=<?=$rowa["idlote"]?>" target="_blank" style="color:#32c53d" title="Status: <?=$rowa['status']?>"><?=$rowa['partida'] . "/" . $rowa['exercicio']?></a></h5>
				<?
				} elseif($rowa['statusfr'] != 'ESGOTADO' and $rowa['status'] != "CANCELADO"){
				?>
					<h5><a href="?_modulo=<?=$rowa['idobjeto']?>&_acao=u&idlote=<?=$rowa["idlote"]?>" target="_blank" title="Status: <?=$rowa['status']?>"><?=$rowa['partida'] . "/" . $rowa['exercicio']?></a></h5>
				<?
				} elseif($rowa['status'] == "CANCELADO"){
				?>
					<h5><a href="?_modulo=<?=$rowa['idobjeto']?>&_acao=u&idlote=<?=$rowa["idlote"]?>" target="_blank" style="color:#c53632" title="Status: <?=$rowa['status']?>"><?=$rowa['partida'] . "/" . $rowa['exercicio']?></a></h5>
				<?
				} else {
				?>
					<h5><a href="?_modulo=<?=$rowa['idobjeto']?>&_acao=u&idlote=<?=$rowa["idlote"]?>" target="_blank" style="color:#c53632;" title="Status: <?=$rowa['statusfr']?>"><?=$rowa['partida'] . "/" . $rowa['exercicio']?></a></h5>
			<?
				}
			}
			?> <br />
		</div>
	<?

	} //function desenhatra

	function buscaamostras(){
		global $_1_u_amostra_idamostra, $_1_u_amostra_idpessoa;

		$res = AmostraController::buscarAmostraComSubtipo($_1_u_amostra_idamostra); ?>
		<div class="papel hover inlineblocktop" id="novoTra">
			<h5 class="cinza" style="white-space: nowrap">Amostras</h5>
			<? foreach($res as $k => $row){?>
				<h5 style=" margin:  0px;"><a href="?_modulo=amostraautogenas&_acao=u&idamostra=<?=$row["idamostra"]?>" target="_blank" title="<?=$row["subtipoamostra"]?>"><?=$row['idregistro'] . "/" . $row['exercicio']?>-<?=$row["subtipoamostra"]?></a> <i class="fa fa-trash cinzaclaro hoververmelho btn-lg pointer" onclick="rettraamostra(<?=$row["idamostra"]?>)" title="Retirar"></i></h5>
				<? $resre = AmostraController::buscarTRAAmostra($row["idamostra"]);
				foreach($resre as $k => $rowre){
					if($rowre['status'] == 'ASSINADO'){
						$cor = '#39bb3c;'; //assinado
					} elseif($rowre['status'] == 'FECHADO'){
						$cor = '#40708F;'; //fechado
					} else {
						$cor = '#c53632;'; //aberto
					} ?>
					<h5 style="font-size: 8px; margin:  0px;"><a style="color:<?=$cor ?>" href="?_modulo=resultsuinos&_acao=u&idresultado=<?=$rowre["idresultado"]?>" target="_blank" title="resultado"><?=$rowre['codprodserv']?></a></h5>
			<? }
			} ?>
			<br />&nbsp;
		</div>
	<?

	} //function buscaamostras(){

	?>
	<div class="col-md-7">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table id="amostra" style="width: 100%;">
					<tr>
						<td>
							<strong>
								<? if($unidadepadrao != 9){?>
									Registro
								<? } elseif(($_1_u_amostra_status == 'PROVISORIO' && $_1_u_amostra_idunidade == 9) || ($provisorio == 'Y')){
									echo "TEA";
								} else {
									echo "TRA";
								} ?>

								<? if((($_1_u_amostra_status == 'PROVISORIO' || $provisorio == 'Y') && $_1_u_amostra_idunidade == 9) or ($_GET['_modulo'] == 'amostraavesprovisorio' && strpos($_GET['_modulo'], 'provisorio') == true)){
									echo (" Provisório");
									if(empty($_1_u_amostra_status)){
										$_1_u_amostra_status = 'PROVISORIO';
									}
								} ?>:
							</strong>
						</td>
						<td id="cabRegistro">
							<?
							if($provisorio != 'Y'){
								if((!empty($_1_u_amostra_idregistro) && empty($_1_u_amostra_idregistroprovisorio) && strpos($_GET['_modulo'], 'provisorio') == true)
									|| (!empty($_1_u_amostra_idregistro) && strpos($_GET['_modulo'], 'provisorio') != true)
								){
									if($_1_u_amostra_idunidade == 1298)
										echo "<label class='alert-warning'>" . $_1_u_amostra_idregistro . "PET</label>";
									else
										echo "<label class='alert-warning'>" . $_1_u_amostra_idregistro . "</label>";
									
								} elseif(!empty($_1_u_amostra_idregistroprovisorio) && strpos($_GET['_modulo'], 'provisorio') == true){
									if($_1_u_amostra_idunidade == 1298)
										echo "<label class='alert-warning'>" . $_1_u_amostra_idregistroprovisorio . "PET</label>";
									else
										echo "<label class='alert-warning'>" . $_1_u_amostra_idregistroprovisorio . "</label>";
								} else {
									?>
									<input name="_1_<?=$_acao?>_amostra_inicioedicao" type="hidden" value="<?=getDatahoraDb() ?>">
									Nova Amostra
									<?
								}

								if(!empty($_1_u_amostra_idregistroprovisorio && strpos($_GET['_modulo'], 'provisorio') != true)){
									if($_1_u_amostra_idunidade == 1298)
										echo " </td><td width='70'> Reg. Prov.: </td><td width='65'><label class='alert-warning'>P-" . $_1_u_amostra_idregistroprovisorio . "PET</label>";
									else
										echo " </td><td width='70'> Reg. Prov.: </td><td width='65'><label class='alert-warning'>P-" . $_1_u_amostra_idregistroprovisorio . "</label>";
								}
							} else {
								if($_1_u_amostra_idunidade == 1298)
									 echo "<label class='alert-warning'>" . $_1_u_amostra_idregistroprovisorio . "</label>";
								else
									 echo "<label class='alert-warning'>" . $_1_u_amostra_idregistroprovisorio . "</label>";
							}

							logReabertura();
							?>
						</td>
						<td style="width: 30px;">Data/Hora:</td>
						<td style="width: 100px;">
							<input <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_idamostra" type="hidden" value="<?=$_1_u_amostra_idamostra ?>">
							<input <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_exercicio" type="hidden" value="<?=$_1_u_amostra_exercicio?>">
							<input <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_status" type="hidden" value="<?=$_1_u_amostra_status ?>">
							<input <?=$disableDta ?> name="_1_<?=$_acao?>_amostra_dataamostra" type="text" class="calendario" value="<?=$_1_u_amostra_dataamostra ?>" autocomplete="off" vnulo style="height: 18px;<?=$stlAlertaDataAnterior ?>" title="<?=$titleAlertaDataAnterior ?>">

							<? if($_acao == "u" && $_1_u_amostra_idunidade == 9 &&  $_1_u_amostra_status != "PROVISORIO"){?>
								<input <?=$disableHoraAmostra ?> name="_1_<?=$_acao?>_amostra_horaamostra" type="time" value="<?=$_1_u_amostra_horaamostra ?>" autocomplete="off" vnulo style="height: 18px;">
							<? } ?>

						</td>
						<td style="width: 30px;">Amostra:</td>
						<td>

							<div class="input-group input-group-sm">
								<input <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_idsubtipoamostra" type="text" cbvalue="<?=$_1_u_amostra_idsubtipoamostra ?>" value="<?=traduzid("subtipoamostra", "idsubtipoamostra", "subtipoamostra", $_1_u_amostra_idsubtipoamostra) ?>" vnulo>
								<span class="input-group-addon" title="Editar Campos Visíveis" id="editarCamposVisiveis"><i class="fa fa-eye pointer"></i></span>
							</div>
						</td>
						<td>
							<span>
								<? $rotulo = getStatusFluxo($pagvaltabela, 'idamostra', $_1_u_amostra_idamostra) ?>
								<label class="alert-warning" id="statusButton" title="<?=$_1_u_amostra_status ?>" id="statusButton"><?=mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?> </label>
							</span>
						</td>
						<? if($unidadepadrao == 9 and !empty($_1_u_amostra_idamostra) and strpos($_GET['_modulo'], 'provisorio') == false){?>
							<!--td >
							<label>Status:</label>
						</td-->
							<td>
								<input type="hidden" name="_2_u_amostra_idamostra" value="<?=$arrTRAAssociado["idamostra"]?>">
							</td>
							<td>
								<a title="Imprimir Amostra" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="showModal()"></a>
							</td>
						<? } ?>
						<? //LTM - 01-10-2020: Alterado para que as impressoras e status fique somente em um lugar
						?>
						<td style="width: 100px;display:none;"><? mostraUnidade(); ?></td>
						<? if($_1_u_amostra_status == 'PROVISORIO' and !empty($_1_u_amostra_idamostra)){?>
							<td>
								<a title="Imprimir TEA" class="fa fa-print pull-right fa-lg cinza hoverazul" onclick="janelamodal('report/tra.php?idamostra=<?=$_1_u_amostra_idamostra ?>&unidadepadrao=9&provisorio=Y&_idempresa=<?=$_1_u_amostra_idempresa?>')"></a>
							</td>
						<? } ?>
						<? if($_1_u_amostra_status != 'PROVISORIO' and !empty($_1_u_amostra_idamostra) and $unidadepadrao != 9){?>
							<td>
								<a title="Imprimir Amostra" class="fa fa-print pull-right fa-lg cinza hoverazul" onclick="janelamodal('report/impamostra.php?idamostra=<?=$_1_u_amostra_idamostra ?>&_idempresa=<?=$_1_u_amostra_idempresa?>')"></a>
							</td>
						<? } ?>
					</tr>
					<?
					$rowLoteAtiv = AmostraController::buscarLoteAtiv($_1_u_amostra_idamostra);
					if(!empty($rowLoteAtiv)){
						$_part = AmostraController::buscarLoteDaFormalizacao($rowLoteAtiv['idobjetovinc']);
						if(!empty($_part)){

							$lotepartida = $_part['partida'] . '/' . $_part['exercicio'];
							?>
							<tr>
								<td>Produto:</td>
								<td>
									<a class="pointer hoverazul" title="Formalização" onclick="janelamodal('?_modulo=formalizacao&_acao=u&idformalizacao=<?=$_part['idformalizacao']?>')"><?=$_part['piloto'] == 'Y' ? 'PP' : '' ?> <?=$_part['partida']?>/<?=$_part['exercicio']?> </a>
								</td>
							</tr>
							<?
						} else {
							$lotepartida = '';
						}
					} else {
						$lotepartida = '';
					}
					?>
				</table>
			</div>
			<div class="panel-body">
				<style>
					.rowDin div[class*="col-"] {
						padding: 3px 15px;
						white-space: nowrap;
					}

					.rowDin div[id*=lb_] {
						font-size: 12px;
					}

					.rowDin input,
					.rowDin select {
						height: 22px !important;
					}

					select.idsecretaria {
						width: 96px;
					}

					.ui-menu .ui-menu-item {
						font-size: 10px;
					}

					.idprodserv {
						font-size: 9px !important;
					}
				</style>

				<div class="row">
					<div class="col-md-3 text-right">Cliente:</div>
					<div class="col-md-6">
						<?
						if(!empty($_1_u_amostra_idamostra) and $_acao == "u"){
							$qr = AmostraController::verificarSeHaResultadosNaNotafiscalitemPorIdamostra($_1_u_amostra_idamostra);
							if($qr > 0){
								$redon = "readonly='readonly'";
								$tredon = "Amostra possui teste em cobrança.";
								//@520296 - ACESSO AO PAINEL COBRANÇAS 
								//bloquear a função de desflegar a cobrança, quando a amostra já estiver com cobrança em andamento. Lucas Melo
								$bloqueioCobranca = true;
							} else {
								$redon = isset($redonAssi) ? $redonAssi : '';
								$tredon = isset($tredonAssi) ? $tredonAssi : '';
							}
						}
						?>
						<input <?=$redon ?> title="<?=$tredon ?>" name="_1_<?=$_acao?>_amostra_idpessoa" type="text" cbvalue="<?=$_1_u_amostra_idpessoa ?>" value="<?=traduzid("pessoa", "idpessoa", "if(cpfcnpj !='',concat(nome,' - ',cpfcnpj),nome)", $_1_u_amostra_idpessoa) ?>" vnulo>
					</div>
					<div class="col-md-3">
						<table>
							<tr>
								<td style="width: 3em;">
									<a href="javascript:toggleOficial();" id="aToggleOficial">
										<input name="_1_<?=$_acao?>_amostra_idsecretaria" type="hidden" value="<?=$_1_u_amostra_idsecretaria ?>">
										<i class="fa fa-shield fa-2x" style="position:absolute;"></i>
										&nbsp;
									</a>
								</td>
								<td style="white-space: nowrap;">
									<span id="lbcnpj" style="vertical-align: middle;"></span>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_nroamostra">Qtd.</div>

					<div class="col-md-3" id="col_nroamostra" colspan="10">
						<div class="input-group input-group-sm">
							<input <?=$redonAssi ?> placeholder="Não Informado" type="text" name="_1_<?=$_acao?>_amostra_nroamostra" id="nroamostra" title="Qtd." value="<?=$_1_u_amostra_nroamostra ?>" size="10" autocomplete="off" vnulo>
							<? if(!empty($_1_u_amostra_nroamostra) and !empty($_1_u_amostra_idamostra)){
								$resind = AmostraController::buscarIdentificadoresDaAmostra($_1_u_amostra_idamostra);
								$qtdind = count($resind);
								if($qtdind < 1 && (($_acao == 'u' && $_GET['_modulo'] != 'amostratra') || ($_acao == 'u' && $_1_u_amostra_status != 'PROVISORIO' && $_GET['_modulo'] == 'amostratra'))){
									?>
									<span class="input-group-addon indicador" title="Limpar informações de Núcleo">
										<i class="fa fa-plus-circle fa-1x verde pointer" onclick="novoIdentificador(<?=$_1_u_amostra_idamostra ?>)" title="Gerar identificadores"></i>
									</span>
									<?
								} elseif(($_acao == 'u' && $_GET['_modulo'] != 'amostratra') || ($_acao == 'u' && $_1_u_amostra_status != 'PROVISORIO' && $_GET['_modulo'] == 'amostratra')) {
									?>
									<span class="input-group-addon indicador" title="Limpar informações de Núcleo">
										<i class="fa fa-arrows-v pointer hoverazul" data-toggle="collapse" title="Editar identificadores" href="#identificadores"></i>
									</span>
									<span class="input-group-addon indicador" title="Editar Núcleo">
										<i class="fa fa-plus-circle pointer " onclick="Novaidentificacao(<?=$_1_u_amostra_idamostra ?>)" title="Inserir mais uma identificação!"></i>
									</span>
									<?
								}
								?>
							<? } ?>
						</div>
					</div>
				</div>

				<div id="identificadores" class="collapse">
					<div class="row">
						<div class="col-md-12">
							<div class="panel panel-default">
								<div class="panel-heading">
									<table>
										<tr>
											<?
											$b = 999;
											$lin = 0;
											$linha = 0;
											foreach($resind as $k => $rowiy){
												$b = $b + 1;
												$lin = $lin + 1;
												$linha = $linha + 1;
												if($lin == 4){
													echo ("</tr><tr>");
													$lin = 1;
												}
												?>

												<td class="nowrap"><?=$linha ?>-
													<input name="_<?=$b ?>_<?=$_acao?>_identificador_ididentificador" type="hidden" style="width: 80px;" value="<?=$rowiy['ididentificador']?>">
													<input name="_<?=$b ?>_<?=$_acao?>_identificador_identificacao" class="size10" type="text" value="<?=$rowiy['identificacao']?>">
													&nbsp;&nbsp;
													<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluiridentificacao(this,<?=$rowiy['ididentificador']?>,<?=$b ?>)" alt="Excluir identificação!"></i>&nbsp;&nbsp;&nbsp;&nbsp;
												</td>
											<? } ?>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_descricao">Descri&ccedil;&atilde;o:</div>
					<div class="col-md-9" id="col_descricao">
						<?/*retirado o $redon do campo descrição à pedido de Marcelo Cunha --- 03/03/2021 Pedro L.*/ ?>
						<input type="text" name="_1_<?=$_acao?>_amostra_descricao" title="Descrição" value="<?=$_1_u_amostra_descricao?>" autocomplete="off">
					</div>
				</div>

				<hr>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_idnucleo">Núcleo:</div>
					<div class="col-md-9" id="col_idnucleo">
						<select <?=$redon ?> style="text-align-last:right;" name="_1_<?=$_acao?>_amostra_rotulonucleotipo" class="col-md-3" id="rotulonucleotipo">
							<? fillselect(array("NUCLEO" => "Núcleo", "INTEGRACAO" => "Integração", "PRODUTO" => "Produto", "PROP" => "Propriedade", "FASE" => "Fase", "DESCRICAO" => "Descrição"), $_1_u_amostra_rotulonucleotipo); ?>
						</select>
						<div class="input-group input-group-sm ">
							<input <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_idnucleo" title="Núcleo" type="text" cbvalue="<?=$_1_u_amostra_idnucleo?>" value="<?=traduzid("nucleo", "idnucleo", "nucleo", $_1_u_amostra_idnucleo, false) ?>" class="ui-autocomplete-input " autocomplete="off">
							<span class="input-group-addon" id="limparnucleo" title="Limpar informações de Núcleo"><i class="fa fa-eraser pointer" onclick="resetNucleo()"></i></span>
							<span class="input-group-addon" id="editarnucleo" title="Editar Núcleo"><i class="fa fa-pencil pointer" onclick="alterarNucleo()"></i></span>
						</div>
					</div>
					<div class="col-md-3 text-right" id="lb_nucleoamostra">Núcleo Amostra:</div>
					<div class="col-md-9" id="col_nucleoamostra">
						<div class="input-group input-group-sm ">
							<input title="Núcleo Amostra" readonly name="_1_<?=$_acao?>_amostra_nucleoamostra" id="nucleoamostra" value="<?=$_1_u_amostra_nucleoamostra ?>">
							<span class="input-group-addon" title="Editar Descrição do Núcleo"><i class="fa fa-pencil pointer" onclick="EditarNucleoAmostra()"></i></span>
						</div>
					</div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_idade">Idade:</div>
					<div class="col-md-3" id="col_idade">
						<input <?=$redonAssi ?> vnulo placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_idade" id="idade" title="Idade" value="<?=$_1_u_amostra_idade ?>" style="width:49%;display: inline-block;">
						<select <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_tipoidade" id="tipoidade" style="width:49%;display: inline-block;">
							<option></option>
							<? fillselect(array('Dia(s)' => 'Dia(s)', 'G' => 'Dias de Gestação', 'Semana(s)' => 'Sem(s)', 'Mês(es)' => 'Mês(es)', 'Ano(s)' => 'Ano(s)', 'ª Progênie' => 'ª Progênie'), $_1_u_amostra_tipoidade); ?>
						</select>
					</div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_lote">Lote:</div>
					<? if(!empty($_1_u_amostra_lote) and $_1_u_amostra_lote != '/'){
						$valorlote = $_1_u_amostra_lote;
					} else {
						$valorlote = $lotepartida;
					} ?>
					<div class="col-md-3" id="col_lote"><input <?=$redonAssi ?> placeholder="Não Informado" type="text" name="_1_<?=$_acao?>_amostra_lote" title="Lote" id="lote" value="<?=$valorlote ?>" size="25" autocomplete="off"></div>

					<div class="col-md-3 text-right" id="lb_idespeciefinalidade">
						Espécie/finalidade:
					</div>
					<div class="col-md-3" id="col_idespeciefinalidade">
						<input <?=$redonAssi ?> type="text" name="_1_<?=$_acao?>_amostra_idespeciefinalidade" vnuo title="Tipo de Espécie/Finalidade" cbvalue="<?=$_1_u_amostra_idespeciefinalidade ?>" value="<?=traduzid("vwespeciefinalidade", "idespeciefinalidade", "especietipofinalidade", $_1_u_amostra_idespeciefinalidade) ?>" vnulo>
					</div>

				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_tutor">Tutor:</div>
					<div class="col-md-3" id="col_tutor"><input <?=$redonAssi ?> placeholder="Não Informado" type="text" name="_1_<?=$_acao?>_amostra_tutor" title="Tutor" id="tutor" value="<?=$_1_u_amostra_tutor ?>" size="25" autocomplete="off"></div>

					<div class="col-md-3 text-right" id="lb_paciente">
						Paciente:
					</div>
					<div class="col-md-3" id="col_paciente">
						<input <?=$redonAssi ?> placeholder="Não Informado" type="text" name="_1_<?=$_acao?>_amostra_paciente" title="Paciente" id="paciente" value="<?=$_1_u_amostra_paciente ?>" size="25" autocomplete="off">
					</div>

				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_pedido">N&ordm; Clifor/Pedido:</div>
					<div class="col-md-9" id="col_pedido">
						<input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_pedido" id="pedido" title="N&ordm; Clifor/Pedido" value="<?=$_1_u_amostra_pedido?>">
					</div>
					<div class="col-md-3 text-right" id="lb_granja">Granja:</div>
					<div class="col-md-9" id="col_granja"><input <?=$redonAssi ?> placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_granja" id="granja" title="Granja" value="<?=$_1_u_amostra_granja ?>" size="20"></div>
					<div class="col-md-3 text-right" id="lb_estexterno">Reg. Externo:</div>
					<div class="col-md-3" id="col_estexterno">
						<input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_estexterno" id="estexterno" title="Registro Externo" value="<?=$_1_u_amostra_estexterno?>">
					</div>
				</div>

				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_unidadeepidemiologica">Unidade Epidemiológica:</div>
					<div class="col-md-3" id="col_unidadeepidemiologica"><input <?=$redonAssi ?> vnulo placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_unidadeepidemiologica" id="unidadeepidemiologica" title="Unidade Epidemiológica" value="<?=$_1_u_amostra_unidadeepidemiologica ?>" size="20"></div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_cpfcnpjprod">CNPJ/CPF (Proprietário):</div>
					<div class="col-md-3" id="col_cpfcnpjprod">
						<input <?=$redonAssi ?> placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_cpfcnpjprod" id="cpfcnpjprod" title="CPF/CNPJ" value="<?=$_1_u_amostra_cpfcnpjprod ?>">
					</div>
					<div class="col-md-3 text-right" id="lb_cidade">UF:</div>
					<div class="col-md-1" id="col_cidade">
						<select <?=$redonAssi ?> class="size5" name="_1_<?=$_acao?>_amostra_uf" id="uf" title="uf">
							<option value=""></option>
							<? fillselect(array('AC' => 'AC', 'AL' => 'AL', 'AM' => 'AM', 'AP' => 'AP', 'BA' => 'BA', 'CE' => 'CE', 'DF' => 'DF', 'ES' => 'ES', 'GO' => 'GO', 'MA' => 'MA', 'MG' => 'MG', 'MS' => 'MS', 'MT' => 'MT', 'PA' => 'PA', 'PB' => 'PB', 'PE' => 'PE', 'PI' => 'PI', 'PR' => 'PR', 'RJ' => 'RJ', 'RN' => 'RN', 'RO' => 'RO', 'RR' => 'RR', 'RS' => 'RS', 'SC' => 'SC', 'SE' => 'SE', 'SP' => 'SP', 'TO' => 'TO', 'EX' => 'EX'), $_1_u_amostra_uf); ?>
						</select>
					</div>
					<div class="col-md-3 text-right" id="lb_cidade">Cidade:</div>
					<div class="col-md-5" id="col_cidade">
						<input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_cidade" id="cidade" title="cidade" value="<?=$_1_u_amostra_cidade ?>">
						<!--
						<select name="_1_<?=$_acao?>_amostra_cidade" id="cidade" title="Cidade" >
						<option value="">Não informado</option>
						<? fillselect(AmostraController::buscarCidadesNfscidadesiaf(), $_1_u_amostra_cidade); ?>
						</select>	
						-->
					</div>
				</div>

				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_numeroanimais">N&ordm; de animais:</div>
					<div class="col-md-3" id="col_numeroanimais">
						<input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_numeroanimais" title="N&ordm; de Animais" id="numeroanimais" value="<?=$_1_u_amostra_numeroanimais ?>">
					</div>
					<div class="col-md-3 text-right" id="lb_galpao">Galp&atilde;o/Aviário:</div>
					<div class="col-md-3" id="col_galpao">
						<input <?=$redonAssi ?> placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_galpao" title="Galpão" id="galpao" value="<?=$_1_u_amostra_galpao?>">
					</div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_alojamento">Data do Alojamento:</div>
					<div class="col-md-3" id="col_alojamento">
						<input <?=$disableDta ?> class="calendario" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_alojamento" title="Data do Alojamento" id="alojamento" value="<?=$_1_u_amostra_alojamento?>">
					</div>
					<div class="col-md-3 text-right" id="lb_numgalpoes">Nº Galp&otilde;es</div>
					<div class="col-md-3" id="col_numgalpoes">
						<input <?=$redonAssi ?> placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_numgalpoes" title="Numero de Galpões" id="numgalpoes" value="<?=$_1_u_amostra_numgalpoes ?>">
					</div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_linha">Linha:</div>
					<div class="col-md-3" id="col_linha">
						<input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_linha" id="linha" title="Linha" value="<?=$_1_u_amostra_linha ?>">
					</div>
					<div class="col-md-3 text-right" id="lb_regoficial">Nº Reg. Of.:</div>
					<div class="col-md-3" id="col_regoficial">
						<input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_regoficial" id="regoficial" vnulo title="Número do Registro Oficial" value="<?=$_1_u_amostra_regoficial ?>">
					</div>


				</div>

				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_nsvo">Nº SVO:</div>
					<div class="col-md-3" id="col_nsvo">
						<input <?=$redonAssi ?> placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_nsvo" id="nsvo" title="Número SVO." value="<?=$_1_u_amostra_nsvo?>" style="font-size: 12px;" vnulo>
					</div>
					<div class="col-md-3 text-right" id="lb_rejeitada" style="white-space: normal;">Amostras Rejeitadas / Descartadas:</div>
					<div class="col-md-3 text-right" id="col_rejeitada">
						<input <?=$redonAssi ?> placeholder="0" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_rejeitada" id="rejeitada" title="Rejeitadas" value="<?=$_1_u_amostra_rejeitada ?>" style="font-size: 12px;">
					</div>
				</div>
				<hr>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_sinaisclinicosinicio">Início sinais clínicos:</div>
					<div class="col-md-9" id="col_sinaisclinicosinicio"><input <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_sinaisclinicosinicio" type="text" title="Início Sinais Clínicos" value="<?=$_1_u_amostra_sinaisclinicosinicio?>"></div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_sinaisclinicos">Sinais Clínicos:</div>
					<div class="col-md-9" id="col_sinaisclinicos"><textarea <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_sinaisclinicos" cols="61" rows="2" title="Sinais Clínicos"><?=$_1_u_amostra_sinaisclinicos ?></textarea></div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_achadosnecropsia">Achados de Necrópsia:</div>
					<div class="col-md-9" id="col_achadosnecropsia"><textarea <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_achadosnecropsia" cols="61" rows="3" title="Achados de Necrópsia"><?=$_1_u_amostra_achadosnecropsia ?></textarea></div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_suspclinicas">Suspeitas Clínicas:</div>
					<div class="col-md-9" id="col_suspclinicas"><textarea <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_suspclinicas" cols="61" rows="2" title="Suspeitas Clínicas"><?=$_1_u_amostra_suspclinicas ?></textarea></div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_histproblema">Histórico do Problema:</div>
					<div class="col-md-9" id="col_histproblema"><textarea <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_histproblema" cols="61" rows="3" title="Histórico do Problema"><?=$_1_u_amostra_histproblema ?></textarea></div>
				</div>


				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_morbidade">Morbidade/N&ordm; animais:</div>
					<div class="col-md-3 text-right" colspan="5" id="col_morbidade">
						<input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_morbidade" id="morbidade" title="Morbidade" value="<?=$_1_u_amostra_morbidade ?>">
					</div>
					<div class="col-md-3 text-right" id="lb_letalidade">Letalidade/N&ordm; animais:</div>
					<div class="col-md-3 text-right" id="col_letalidade">
						<input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_letalidade" id="letalidade" title="Letalidade" value="<?=$_1_u_amostra_letalidade ?>">
					</div>
					<div class="col-md-3 text-right" id="lb_mortalidade">Mortalidade/N&ordm; animais:</div>
					<div class="col-md-3 text-right" id="col_mortalidade">
						<input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_mortalidade" id="mortalidade" title="Mortalidade" value="<?=$_1_u_amostra_mortalidade ?>">
					</div>
				</div>


				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_usomedicamentos">Uso de Medicamentos:</div>
					<div class="col-md-9" id="col_usomedicamentos"><input <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_usomedicamentos" type="text" title="Uso de Medicamentos" value="<?=$_1_u_amostra_usomedicamentos ?>"></div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_usovacinas">Uso de Vacinas:</div>
					<div class="col-md-9" id="col_usovacinas"><input <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_usovacinas" type="text" title="Uso de Vacinas" value="<?=$_1_u_amostra_usovacinas ?>"></div>
				</div>
				<div class="row rowDin hidden">

					<div class="col-md-3 text-right" id="lb_datacoleta">Data Coleta:</div>
					<div class="col-md-3" id="col_datacoleta">
						<input <?=$disableDta ?> placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_datacoleta" id="datacoleta" title="Data Coleta" class="calendario" value="<?=$_1_u_amostra_datacoleta ?>">
					</div>
					<div class="col-md-3 text-right" id="lb_horacoleta">Hora Coleta:</div>
					<div class="col-md-3" id="col_horacoleta">
						<input placeholder="Não Informado" autocomplete="off" type="time" name="_1_<?=$_acao?>_amostra_horacoleta" id="horacoleta" title="Hora Coleta" value="<?=$_1_u_amostra_horacoleta ?>">
					</div>

					<div class="col-md-3 text-right" id="lb_sexo">Sexo:</div>
					<div class="col-md-3" id="col_sexo" colspan="10">
						<select <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_sexo" id="sexo" title="Sexo">
							<option value=""></option>
							<? fillselect(array('Macho' => 'Macho', 'Fêmea' => 'Fêmea', 'Macho/Fêmea' => 'Macho/Fêmea'), $_1_u_amostra_sexo); ?>
						</select>
					</div>
					<div class="col-md-3 text-right" id="lb_clienteterceiro">Cliente 3&ordm;:</div>
					<div class="col-md-3" id="col_clienteterceiro" colspan="10"><input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_clienteterceiro" id="clienteterceiro" title="Cliente Terceiro" value="<?=$_1_u_amostra_clienteterceiro?>"></div>

				</div>
				<? //Insere a Data de Chegada dos Materiais (LTM - 21/08/2020 - 368742)
				?>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_datachegada">Data chegada Material:</div>
					<div class="col-md-3" id="col_datachegada">
						<input <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_datachegada" type="text" id="dtchegada" title="Data chegada Material" class="calendario" value="<?=$_1_u_amostra_datachegada ?>" vnulo>
					</div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_formaarmazen">Forma de <br>Armazenamento:</div>
					<div class="col-md-3" id="col_formaarmazen">
						<?
						if($unidadepadrao == 9){?>
							<select <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_formaarmazen" id="clienteterceiro" title="Forma de Armazenamento" value="<?=$_1_u_amostra_formaarmazen ?>">
								<option value=""></option>
								<? fillselect('SELECT "Resfriado" as value, "Resfriado" UNION SELECT "Congelado" as value, "Congelado"', $_1_u_amostra_formaarmazen) ?>
							</select>
						<? } else {?>
							<input <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_formaarmazen" id="clienteterceiro" title="Forma de Armazenamento" value="<?=$_1_u_amostra_formaarmazen ?>">
						<? } ?>
					</div>
					<div class="col-md-3 text-right" id="lb_meiotransp">Meio de Transporte<br> Amostras:</div>
					<div class="col-md-3" id="col_meiotransp">
						<? if($unidadepadrao == 9){?>
							<select <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_meiotransp" id="clienteterceiro" title="Meio de Transporte" value="<?=$_1_u_amostra_meiotransp ?>">
								<option value=""></option>
								<? fillselect('SELECT "Caixa de isopor com gelo" as id, "Caixa de isopor com gelo"', $_1_u_amostra_meiotransp) ?>
							</select>
						<? } else {?>
							<input <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_meiotransp" id="clienteterceiro" title="Meio de Transporte" value="<?=$_1_u_amostra_meiotransp ?>">
						<? } ?>
					</div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_condconservacao">Condições de <br>Conservação:</div>
					<div class="col-md-9" id="col_condconservacao"><textarea <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_condconservacao" title="Condições de conservação" cols="61" rows="3"><?=$_1_u_amostra_condconservacao?></textarea></div>
				</div>

				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_valorobjeto">Temperatura de<br> recebimento do Material(°C):</div>
					<div class="col-md-3" id="col_valorobjeto" colspan="10">
						<input vnulo <?=$redonAssi ?> disabled onkeyup="setNameDadosAmostra('<?=$rAcao?>',this)" name="_d1_<?=$rAcao?>_dadosamostra_valorobjeto" placeholder="Não Informado" autocomplete="off" type="text" id="col_valorobjeto" title="Temperatura de recebimento do material" value="<?=$gAmostraDados['valorobjeto']?>">
						<? if($_acao == 'u'){?>
							<input type="hidden" id='damostraobj' value="temperaturarecebimento">
							<input type="hidden" id='didamostra' value="<?=$_1_u_amostra_idamostra ?>">
							<input type="hidden" id='iddadosamostra' value="<?=$gAmostraDados['iddadosamostra']?>">
						<? } ?>
					</div>
				</div>

				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_nucleoorigem">N&uacute;cleo Origem:</div>
					<div class="col-md-3" id="col_nucleoorigem" colspan="10"><input <?=$redonAssi ?> placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_nucleoorigem" id="nucleoorigem" title="Núcleo de Origem" value="<?=$_1_u_amostra_nucleoorigem ?>"></div>
					<div class="col-md-3 text-right" id="lb_tipomicroorganismo">Tipo de microorganismo:</div>
					<div class="col-md-3" id="col_tipomicroorganismo" colspan="10"><input <?=$redonAssi ?> placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_tipomicroorganismo" id="tipomicroorganismo" title="Tipo de microorganismo" value="<?=$_1_u_amostra_tipomicroorganismo?>"></div>
					<div class="col-md-3 text-right" id="lb_tempofermentacao">Tempo de fermentação:</div>
					<div class="col-md-3" id="col_tempofermentacao" colspan="10"><input <?=$redonAssi ?> placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_tempofermentacao" id="tempofermentacao" title="Tempo de fermentação" value="<?=$_1_u_amostra_tempofermentacao?>"></div>
					<div class="col-md-3 text-right" id="lb_modeloreator">Modelo reator:</div>
					<div class="col-md-3" id="col_modeloreator" colspan="10"><input <?=$redonAssi ?> placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_modeloreator" id="modeloreator" title="Modelo reator" value="<?=$_1_u_amostra_modeloreator ?>"></div>

					<div class="col-md-3 text-right" id="lb_tipo">Tipo:</div>
					<div class="col-md-3" id="col_tipo" colspan="5"><input <?=$redonAssi ?> autocomplete="off" type="text" title="Tipo" name="_1_<?=$_acao?>_amostra_tipo" id="tipo" value="<?=$_1_u_amostra_tipo?>"></div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_especificacao">Especifica&ccedil;&otilde;es:</div>
					<div class="col-md-3" id="col_especificacao"><input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_especificacao" id="especificacao" title="Especificação" value="<?=$_1_u_amostra_especificacao?>"></div>

					<div class="col-md-3 text-right" id="lb_fornecedor">Fornecedor:</div>
					<div class="col-md-3" id="col_fornecedor"><input <?=$redonAssi ?> autocomplete="off" type="text" title="Fornecedor" name="_1_<?=$_acao?>_amostra_fornecedor" id="fornecedor" value="<?=$_1_u_amostra_fornecedor ?>"></div>

					<div class="col-md-3 text-right" id="lb_partida">Partida:</div>
					<div class="col-md-3" id="col_partida"><input <?=$redonAssi ?> autocomplete="off" type="text" title="Partida" name="_1_<?=$_acao?>_amostra_partida" id="partida" value="<?=$_1_u_amostra_partida ?>"></div>

				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_datafabricacao">Data Fabrica&ccedil;&atilde;o:</div>
					<div class="col-md-3" id="col_datafabricacao" colspan="3"><input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_datafabricacao" id="datafabricacao" title="Data de Fabricação" value="<?=$_1_u_amostra_datafabricacao?>"></div>

					<div class="col-md-3 text-right" id="lb_identificacaochip">Chip/Identif.:</div>
					<div class="col-md-3" id="col_identificacaochip"><input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_identificacaochip" id="identificacaochip" title="Identificação/Chip" value="<?=$_1_u_amostra_identificacaochip ?>"></div>

					<div class="col-md-3 text-right" id="lb_diluicoes">Dilui&ccedil;&otilde;es:</div>
					<div class="col-md-3" colspan="3" id="col_diluicoes"><input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_diluicoes" id="diluicoes" title="Diluições" value="<?=$_1_u_amostra_diluicoes ?>"></div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_nroplacas">N&ordm; Placas:</div>
					<div class="col-md-3" id="col_nroplacas"><input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_nroplacas" id="nroplacas" title="Número de Placas" value="<?=$_1_u_amostra_nroplacas ?>"></div>

					<div class="col-md-3 text-right" id="lb_nrodoses">N&ordm; Doses</div>
					<div class="col-md-3" id="col_nrodoses" colspan="5"><input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_nrodoses" id="nrodoses" title="Número de Doses" value="<?=$_1_u_amostra_nrodoses ?>"></div>

					<div class="col-md-3 text-right" id="lb_semana">Semana:</div>
					<div class="col-md-3" id="col_semana" colspan="5"><input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_semana" id="semana" title="Semana" value="<?=$_1_u_amostra_semana ?>"></div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_notafiscal">Nota Fiscal:</div>
					<div class="col-md-3" id="col_notafiscal" colspan="5"><input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_notafiscal" id="notafiscal" title="Nota Fiscal" value="<?=$_1_u_amostra_notafiscal ?>"></div>

					<div class="col-md-3 text-right" id="lb_vencimento">Vencimento:</div>
					<div class="col-md-3" id="col_vencimento" colspan="5"><input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_vencimento" id="vencimento" title="Vencimento" value="<?=$_1_u_amostra_vencimento?>"></div>

					<div class="col-md-3 text-right" id="lb_fabricante">Fabricante</div>
					<div class="col-md-3" id="col_fabricante" colspan="5"><input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_fabricante" id="fabricante" title="Fabricante" value="<?=$_1_u_amostra_fabricante ?>"></div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_sexadores">Sexadores:</div>
					<div class="col-md-3" id="col_sexadores" colspan="5"><input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_sexadores" id="sexadores" title="Sexadores" value="<?=$_1_u_amostra_sexadores ?>"></div>

					<div class="col-md-3 text-right" id="lb_localexp">Local Espec&iacute;fico:</div>
					<div class="col-md-3" colspan="5" id="col_localexp"><input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_localexp" id="localexp" title="Local Específico" value="<?=$_1_u_amostra_localexp ?>"></div>

				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_localcoleta">Local Coleta:</div>
					<div class="col-md-9" colspan="5" id="col_localcoleta"><input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_localcoleta" id="localcoleta" title="Local Coleta" value="<?=$_1_u_amostra_localcoleta ?>"></div>
				</div>
				<div class="row rowDin hidden">
					<? if((empty($_1_u_amostra_responsavel) || !empty($_1_u_amostra_idpessoaresponsavel)) && ($_GET['_modulo'] == 'amostratraprovisorio' || $_GET['_modulo'] == 'amostratra')){?>
						<div class="col-md-3 text-right" id="lb_idpessoaresponsavel">Respons&aacute;vel Coleta:</div>
						<div class="col-md-9" colspan="5" id="col_idpessoaresponsavel">
							<div class="input-group input-group-sm">
								<input <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_idpessoaresponsavel" title="Responsável" type="text" cbvalue="<?=$_1_u_amostra_idpessoaresponsavel ?>" value="<?=traduzid("pessoa", "idpessoa", "nome", $_1_u_amostra_idpessoaresponsavel, false) ?>" class="ui-autocomplete-input" autocomplete="off">
								<span class="input-group-addon" id="limparresponsavel" title="Limpar informações do Responsável Coleta"><i class="fa fa-eraser pointer" onclick="resetResponsavel()"></i></span>
								<span class="input-group-addon" id="editarressponsavel" title="Editar Responsável Coleta"><i class="fa fa-pencil pointer" onclick="alterarResponsavel()"></i></span>
							</div>
						</div>
					<? } else {?>
						<div class="col-md-3 text-right" id="lb_responsavel">Respons&aacute;vel Coleta:</div>
						<div class="col-md-9 d-flex align-items-center" colspan="5" id="col_responsavel">
							<?if($_1_u_amostra_responsavel) { ?>
								<input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_responsavel" id="responsavel" title="Responsável Coleta" value="<?=$_1_u_amostra_responsavel ?>" class="col-xs-9">
							<?} else { ?>
								<select <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_idresponsavel" title="Responsável Coleta" class="col-xs-7 selectpicker px-0" data-live-search="true">
									<?= fillselect($arrResponsavelColeta, $_1_u_amostra_idresponsavel) ?>
								</select>
							 <? } ?>
							 <div class="col-xs-5 pr-0">
								<input type="text" class="form-control cpf" title="CPF" name="_1_<?=$_acao?>_amostra_cpf"  value="<?=$_1_u_amostra_cpf ? $_1_u_amostra_cpf : $responsavelColeta['cpfcnpj'] ?>" placeholder="CPF do responsável pela coleta" />
							 </div>
						</div>
					<? } ?>
					<? if(((empty($_1_u_amostra_responsavelcolcrmv) && empty($_1_u_amostra_responsavel)) || !empty($_1_u_amostra_idpessoaresponsavelcrmv)) && ($_GET['_modulo'] == 'amostratraprovisorio' || $_GET['_modulo'] == 'amostratra')){?>
						<div class="col-md-3 text-right" id="lb_idpessoaresponsavelcrmv">Respons&aacute;vel Coleta Crmv:</div>
						<div class="col-md-3 text-right" id="col_idpessoaresponsavelcrmv">
							<div class="input-group input-group-sm">
								<input <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_idpessoaresponsavelcrmv" id="idpessoaresponsavelcrmv" title="CRMV" type="text" cbvalue="<?=$_1_u_amostra_idpessoaresponsavelcrmv ?>" value="<?=traduzid("pessoacrmv", "idpessoacrmv", "crmv", $_1_u_amostra_idpessoaresponsavelcrmv, false) ?> - <?=traduzid("pessoacrmv", "idpessoacrmv", "uf", $_1_u_amostra_idpessoaresponsavelcrmv, false) ?>" class="ui-autocomplete-input" autocomplete="off">
							</div>
						</div>
					<? } else {?>
						<div class="col-md-3 text-right" id="lb_responsavelcolcrmv">Respons&aacute;vel Coleta Crmv:</div>
						<div class="col-md-3 text-right" id="col_responsavelcolcrmv">
							<input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_responsavelcolcrmv" id="responsavelcolcrmv" title="CRMV" value="<?=$_1_u_amostra_responsavelcolcrmv ?>">
						</div>
					<? } ?>
					<div class="col-md-3 text-right" id="lb_responsavelcolcont">Respons&aacute;vel Coleta Contato:</div>
					<div class="col-md-3 text-right" id="col_responsavelcolcont">
						<input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_responsavelcolcont" id="responsavelcolcont" title="Contato" value="<?=$_1_u_amostra_responsavelcolcont ?>">
					</div>
				</div>
				<div class="row rowDin hidden">
					<? if((empty($_1_u_amostra_responsavelof) || !empty($_1_u_amostra_idpessoaresponsavelof)) && ($_GET['_modulo'] == 'amostratraprovisorio' || $_GET['_modulo'] == 'amostratra')){
					?>
						<div class="col-md-3 text-right" id="lb_idpessoaresponsavelof">Respons&aacute;vel Oficial:</div>
						<div class="col-md-9" colspan="5" id="col_idpessoaresponsavelof">
							<div class="input-group input-group-sm">
								<input name="_1_<?=$_acao?>_amostra_idpessoaresponsavelof" class="ui-autocomplete-input" type="text" title="Responsável Oficial" cbvalue="<?=$_1_u_amostra_idpessoaresponsavelof ?>" placeholder="Selecione" value="<?=traduzid("pessoa", "idpessoa", "nome", $_1_u_amostra_idpessoaresponsavelof) ?>">
								<span class="input-group-addon" id="limparresponsavelof" title="Limpar informações do Responsaável Oficial"><i class="fa fa-eraser pointer" onclick="resetResponsavelof()"></i></span>
								<span class="input-group-addon" id="editarressponsavelof" title="Editar Responsaável Oficial"><i class="fa fa-pencil pointer" onclick="alterarResponsavelof()"></i></span>
							</div>
						</div>
					<? } else {?>
						<div class="col-md-3 text-right" id="lb_responsavelof">Respons&aacute;vel Oficial:</div>
						<div class="col-md-9" colspan="5" id="col_responsavelof">
							<input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_responsavelof" id="responsavelof" title="Responsável Oficial" value="<?=$_1_u_amostra_responsavelof ?>">
						</div>
					<? } ?>
					<? if((empty($_1_u_amostra_responsavelofcrmv) || !empty($_1_u_amostra_idpessoaresponsavelcrmvof)) && ($_GET['_modulo'] == 'amostratraprovisorio' || $_GET['_modulo'] == 'amostratra')){?>
						<div class="col-md-3 text-right" id="lb_idpessoaresponsavelcrmvof">Respons&aacute;vel Oficial Crmv:</div>
						<div class="col-md-3 text-right" id="col_idpessoaresponsavelcrmvof">
							<div class="input-group input-group-sm">
								<input <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_idpessoaresponsavelcrmvof" id="idpessoaresponsavelcrmvof" title="CRMV" type="text" cbvalue="<?=$_1_u_amostra_idpessoaresponsavelcrmvof ?>" value="<?=traduzid("pessoacrmv", "idpessoacrmv", "crmv", $_1_u_amostra_idpessoaresponsavelcrmvof, false) ?> - <?=traduzid("pessoacrmv", "idpessoacrmv", "uf", $_1_u_amostra_idpessoaresponsavelcrmvof, false) ?>" class="ui-autocomplete-input" autocomplete="off">
							</div>
						</div>
					<? } else {?>
						<div class="col-md-3 text-right" id="lb_responsavelofcrmv">Respons&aacute;vel Oficial Crmv:</div>
						<div class="col-md-3 text-right" id="col_responsavelofcrmv">
							<input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_responsavelofcrmv" id="responsavelofcrmv" title="CRMV" value="<?=$_1_u_amostra_responsavelofcrmv ?>">
						</div>
					<? } ?>
					<div class="col-md-3 text-right" id="lb_responsaveloftel">Tel:</div>
					<div class="col-md-3 text-right" id="col_responsaveloftel">
						<input <?=$redonAssi ?> autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_responsaveloftel" id="responsaveloftel" title="Telefone" value="<?=$_1_u_amostra_responsaveloftel ?>">
					</div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_lacre">Lacre:</div>
					<div class="col-md-3" id="col_lacre"><input <?=$redonAssi ?> vnulo placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_lacre" id="lacre" title="Lacre" value="<?=$_1_u_amostra_lacre ?>"></div>

					<div class="col-md-3 text-right" id="lb_tc">TC:</div>
					<div class="col-md-3" id="col_tc"><input <?=$redonAssi ?> vnulo placeholder="Não Informado" autocomplete="off" type="text" name="_1_<?=$_acao?>_amostra_tc" id="tc" title="T.C." value="<?=$_1_u_amostra_tc ?>"></div>

				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_observacao">Observa&ccedil;&atilde;o:</div>
					<div class="col-md-9" id="col_observacao" colspan="5"><textarea <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_observacao" id="observacao" title="Observação" cols="61" rows="3"><?=$_1_u_amostra_observacao?></textarea></div>
				</div>
				<div class="row rowDin hidden">
					<div class="col-md-3 text-right" id="lb_observacaointerna">Observa&ccedil;&atilde;o
						interna:<br>
						(N&atilde;o ser&aacute; impressa)</div>
					<div class="col-md-9" id="col_observacaointerna" colspan="5"><textarea <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_observacaointerna" id="observacaointerna" title="Observação Interna" cols="61" rows="3"><?=$_1_u_amostra_observacaointerna ?></textarea></div>
				</div>
				<? //Campos adicionados em 30-03-2020 - Lidiane - Aparecerá somente para TRA
				//474152 20-07-2021 motrar tanto no tea como no tra
				if(($unidadepadrao == 9 /*&& strpos($_GET['_modulo'], 'provisorio') == true*/) or !empty($_1_u_amostra_sorologico) or !empty($_1_u_amostra_isolamento) or !empty($_1_u_amostra_pcr)){?>
					<div class="row rowDin hidden">
						<div class="col-md-3 text-right" id="lb_sorologico">Sorológico:</div>
						<div class="col-md-9" id="col_sorologico" colspan="5">
							<textarea <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_sorologico" id="sorologico" title="Sorológico" cols="61" rows="2"><?=$_1_u_amostra_sorologico?></textarea>
						</div>
					</div>
					<div class="row rowDin hidden">
						<div class="col-md-3 text-right" id="lb_isolamento">Isolamento:</div>
						<div class="col-md-9" id="col_isolamento" colspan="5">
							<textarea <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_isolamento" id="isolamento" title="Isolamento" cols="61" rows="2"><?=$_1_u_amostra_isolamento?></textarea>
						</div>
					</div>
					<div class="row rowDin hidden">
						<div class="col-md-3 text-right" id="lb_pcr">PCR:</div>
						<div class="col-md-9" id="col_pcr" colspan="5">
							<textarea <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_pcr" id="pcr" title="PCR" cols="61" rows="2"><?=$_1_u_amostra_pcr ?></textarea>
						</div>
					</div>
					<div class="row rowDin hidden">
						<div class="col-md-3 text-right" id="lb_histopatologico">Histopatológico:</div>
						<div class="col-md-9" id="col_histopatologico" colspan="5">
							<textarea <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_histopatologico" id="histopatologico" title="Histopatológico" cols="61" rows="2"><?=$_1_u_amostra_histopatologico?></textarea>
						</div>
					</div>
					<div class="row rowDin hidden">
						<div class="col-md-3 text-right" id="lb_parasitologico">Parasitológico:</div>
						<div class="col-md-9" id="col_parasitologico" colspan="5">
							<textarea <?=$redonAssi ?> name="_1_<?=$_acao?>_amostra_parasitologico" id="parasitologico" title="Parasitológico" cols="61" rows="2"><?=$_1_u_amostra_parasitologico?></textarea>
						</div>
					</div>
				<? } ?>
			</div><!-- panel body -->
		</div>
	</div>
	<style>
		.list-group-item .list-group-item-text {
			display: none;
		}

		.rowDin .btn.dropdown-toggle {
			height: 22px;
			display: flex;
			align-items: center;
			font-size: 1rem;
		}
	</style>
	<div class="col-md-5">
		<?

		//Valida se está ou não no Provisorio. Caso esteja estes campos não aparecerão (Lidiane - 30-03-2020) 
		//se for unidade diferenete de TRA pode lançar testes

		if(($_GET['_modulo'] == 'amostratra' && $_1_u_amostra_status <> 'PROVISORIO' && $provisorio <> 'Y') || $_GET['_modulo'] != 'amostratra'){
		?>
			<div id="observacaore"></div>
			<i class=""></i>
			<div class="panel panel-default">
				<div class="panel-heading">Testes <label>(<span id="testesquant">0</span> itens)</label>
					<a title="Imprimir Teste(s) Amostra." class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/amostra.php?acao=i&idamostra=<?=$_1_u_amostra_idamostra ?>&_idempresa=<?=$_1_u_amostra_idempresa?>')"></a>
				</div>
				<div class="panel-body">
					<span id="infoSecretaria" class="cinza"></span>
					<table id="tbTestes" class="table table-striped planilha">
						<thead>
							<tr>
								<th style="width:10%"></th>
								<? if($_1_u_amostra_idunidade == 1){?>
									<th>R$</th>
								<? } ?>
								<th style="width:20%">Teste</th>
								<th style="width:10%">Qtd.</th>
								<? if($unidadepadrao == 1){?>
									<th style="width:50%;text-align:center" id="oficialInsert">Oficial</th>
								<? } else {?>
									<th style="width:50%;text-align:center">Cobrar</th>
								<? } ?>
								<th style="width:10%"></th>
							</tr>
						</thead>
						<tbody>
							<? listaTestes() ?>
						</tbody>
					</table>
					<table class="hidden">
						<tr id="modeloNovoTeste">
							<td>
								<a class="fa fa-print pointer cinzaclaro" style="color:silver;" title="Alterar Lote de Impressão" id="cbimp#irow" onclick="alteraLoteEtiqueta(#irow)"></a>
							</td>
							<td style="width:60%">
								<input type="hidden" name="#nameidresultado">
								<input type="hidden" name="#nameloteetiqueta" value="">
								<? ////Inserir Y se caso for Novo Teste - Lidiane (26/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=328394 - Lidiane (26/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=328394 
								?>
								<input type="hidden" name="#namecobrar" value="Y">
								<input type="hidden" name="#nameord" value="">
								<input type="text" name="#nameidtipoteste" class="idprodserv" cbvalue placeholder="Informe o Teste" vnulo>
							</td>
							<td><input type="text" name="#namequantidade" style="width:30px" placeholder="Qtd." vnulo vnumero></td>
							<td>
								<select name="#nameidsecretaria" style="font-size:10px;" placeholder="Secretaria" id="oficialInsert">
									<option value=""></option>
									<?
									fillselect(AmostraController::buscarSecretariaPessoa($_1_u_amostra_idpessoa), $r["idsecretaria"]);
									?>
								</select>
							</td>
							<td>
								<?
								if(!empty($_1_u_amostra_idpessoa)){
									$qtdpf = AmostraController::buscarPedidopreferencia($_1_u_amostra_idpessoa);
									if($qtdpf > 0){
										$typepf = 'text';
										$disable = '';
									} else {
										$typepf = 'hidden';
										$typepf = 'hidden';
										$disable = "disabled='disabled'";
									}
								} else {
									$typepf = 'hidden';
									$disable = "disabled='disabled'";
								}
								?>
								<input class="namenpedido size6" placeholder="N. Pedido" type="<?=$typepf ?>" name="#namenpedido" value="" <?=$disable ?> vnulo>
								<i class="fa fa-arrows cinzaclaro hover move"></i>
							</td>
						</tr>
					</table>
					<div>
						<i class="fa fa-plus-circle fa-2x verde btn-lg pointer" onclick="novoTeste()" alt="Inserir novo teste"></i>

						<? if(!empty($_1_u_amostra_idamostra)){?>
							<a class="fa fa-print fa-2x cinzaclaro btn-lg pointer hidden" onclick="imprimeTestes(<?=$_1_u_amostra_idamostra ?>, '<?=$impressora ?>');" alt="Imprimir Etiquetas"></a>
							<a class="fa fa-print fa-2x cinzaclaro btn-lg pointer" onclick="showModalEtiqueta(<?=$_1_u_amostra_idamostra ?>);" alt="Imprimir Etiquetas"></a>
						<? } ?>
						<i class="fa fa-trash fa-2x cinzaclaro hoververmelho btn-lg pointer" id="excluirTeste" alt="Arraste o teste até aqui para excluir"></i>
					</div>
				</div>
			</div>
		<?
		}

		//Valida se está ou não no Provisorio. Caso esteja estes campos não aparecerão (Lidiane - 30-03-2020) 
		if($unidadepadrao == 9 and !empty($_1_u_amostra_idamostra)){ // Diagnóstico Autógenas 
			// print_r($arrTRAAssociado);
		?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<table style="width: 100%; min-width:100%;">
						<tr>
							<td><label>TEA:</label></td>
							<td>
								<?=$_1_u_amostra_idregistroprovisorio?>
								<a class="fa fa-bars pointer hoverazul" title="TEA" onclick="janelamodal('?_modulo=amostratra&_acao=u&idamostra=<?=$_1_u_amostra_idamostra ?>&provisorio=Y')" style="cursor: pointer;border: 1px dotted #e4dada;border-radius: 80px;"></a>
							</td>
							<td>
								<?
								//LTM (05-08-2021) - O valor do tipoobjeto ficará fixo para diferenciar o TEA do TRA
								$row = AmostraController::buscarAssinaturaTEA($_1_u_amostra_idamostra, $_GET['_modulo']);
								if(empty($row)){
									$assinatura = 'SEMASSINATURA';
								} else {
									if(!empty($row['idarquivo']) && $row['status'] == 'ASSINADO'){
										$assinatura = 'ASSINADO';
									} elseif($row['status'] == 'INATIVO'){
										$assinatura = 'SEMASSINATURA';
									} else {
										$assinatura = $row['status'];
									}
								}

								?>
								<select readonly>
									<? fillselect(array("ASSINADO" => "Assinado", "PENDENTE" => "Pendente", "REJEITADO" => "Rejeitado", "SEMASSINATURA" => "Sem Assinatura"), $assinatura) ?>
								</select>
								<? if($row['status'] == 'PENDENTE' && $qtdpf > 0 && $_SESSION["SESSAO"]["IDPESSOA"] == $row['idpessoa'] && empty($row['idarquivo'])){?>
									<button id="cbSalvar" type="button" class="btn btn-success btn-xs assinarTEA" onclick="assinarTEA()" title="Assinar TEA">
										<i class="fa fa-circle"></i>Assinar TEA
									</button>
									<script>
										function assinarTEA(){
											dataassinatura = (moment('<?=date_format(date_create($row['criadoem']), 'd-m-Y') ?>').format("DD/MM/YYYY") || "");
											dataamostra = ($("[name=_1_" + CB.acao + "_amostra_dataamostra]").val() || "");
											datacoleta = ($("[name=_1_" + CB.acao + "_amostra_datacoleta]").val() || "");
											difColeta = moment(dataamostra, "DD/MM/YYYY").diff(moment(datacoleta, "DD/MM/YYYY"), "days");
											if(difColeta >= 0 && difColeta < 8){
												ST.assinar(<?=$row['idcarrimbo']?>, datacoleta);
											} else {
												alertAtencao("O intervalo entre a Data de Entrada e Data de Coleta deve ser, no máximo, de 7 dias.");
												$("[name=_1_" + CB.acao + "_amostra_dataamostra]").css("border", "1px solid #a94442");
												$("[name=_1_" + CB.acao + "_amostra_dataamostra]").css("background", "mistyrose");
												$("[name=_1_" + CB.acao + "_amostra_datacoleta]").css("border", "1px solid #a94442");
												$("[name=_1_" + CB.acao + "_amostra_datacoleta]").css("background", "mistyrose");
											}
										}
									</script>
								<? } ?>
							</td>
							<td>
								<a title="Imprimir TEA" class="fa fa-print pull-right fa-lg pointer cinza hoverazul" onclick="janelamodal('report/tra.php?idamostra=<?=$_1_u_amostra_idamostra ?>&unidadepadrao=9&provisorio=Y&_idempresa=<?=$_1_u_amostra_idempresa?>')" style="cursor: pointer;border: 1px dotted #e4dada;border-radius: 80px;"></a>
							</td>
						</tr>
						<tr>
							<td>
								Email:
							</td>
							<td colspan="4">
								<input style="font-size: 10px; height: 20px; width: 180px; " type="hidden" name="_2_u_amostra_idamostra" value="<?=$_1_u_amostra_idamostra ?>">
								<input style="font-size: 10px; height: 20px; width: 180px; " type="text" name="_2_u_amostra_email" value="<?=$arrTRAAssociado["email"]?>">
								<?
								if($arrTRAAssociado["emailtea"] == "O"){
									$cbotao = "verde";
								} elseif($arrTRAAssociado["emailtea"] == "E"){
									$cbotao = "vermelho";
								} else {
									$cbotao = "cinzaclaro";
								}
								?>
								<i class="fa fa-envelope <?=$cbotao?> hoververde btn-lg pointer" onclick="statusenviaemailtea(<?=$arrTRAAssociado["idamostra"]?>)" title="<?=$arrTRAAssociado["logemail"]?>"></i>
							</td>
						</tr>
					</table>

				</div>

				<div class="panel-body">
					<table style="width: 100%;">
						<tr>
							<td style="width:100%;min-width:100%;" class="inlineblocktop">
								<? if(!empty($_1_u_amostra_idamostra)){
									$res = AmostraController::buscarTRAVinculado($_1_u_amostra_idamostra);
									$qtd = count($res);
									if($qtd > 0){
										buscaamostras();
									}

									desenhaTRA();
									linksolfb();
								} ?>
							</td>
						</tr>
						<tr>
							<td style="width:100%;height:100%;">
								<div class="cbupload" id="anexo" title="Clique ou arraste arquivos para cá" style="width:100%;height:100%;">
									<i class="fa fa-cloud-upload fonte18"></i>
								</div>
							</td>
						</tr>
						<? if(!empty($_1_u_amostra_idamostra)){?>
							<tr>
								<? $res = AmostraController::buscarArquivosAmostra($_1_u_amostra_idamostra);
								$numarq = count($res);
								if($numarq > 0){?>
									<td colspan="4">
										<ul class="listaitens">
											<li class="cab">Arquivos Anexos (<?=$numarq ?>)</li>
											<? foreach($res as $k => $row){?>
												<li><a title="Abrir arquivo" target="_blank" href="../upload/<?=$row["nome"]?>"><?=$row["nome"]?></a></li>
											<? } ?>
										</ul>
									</td>
								<? } ?>
							</tr>
						<? } //if(!empty($_1_u_amostra_idamostra))
						?>
					</table>
				</div>
			</div>
		<? } ?>

		<div class="panel panel-default" id="panelEtiquetas">
			<div class="panel-heading">Testes Cancelados</div>
			<table class="table">
				<tr class="header">
					<td>Teste</td>
					<td>Cancelado por</td>
					<td>Cancelado em</td>
				</tr>
				<?logTestesCancelados(); ?>
			</table>
		</div>
		<div class="panel panel-default" id="panelEtiquetas">
			<div class="panel-heading">Log de Impressão de Etiquetas</div>
			<table class="table">
				<tr class="header">
					<td>Teste</td>
					<td>Criado por</td>
					<td>Criado em</td>
				</tr>
				<?logImpressao(); ?>
			</table>
		</div>

		<? if($_GET['_acao'] == "u"){
			$resultcar = AmostraController::buscarConferenciaAmostra($_GET['idamostra']);

			if($resultcar['status'] == 'CONFERIDO'){
			?>
				<div class="panel panel-default divHistorico">
					<div class="panel-heading">
						<strong>Observações de Conferência</strong>
					</div>
					<div class="panel-body" style="padding-top: 0px !important;">
						<table class="table table-striped planilha" style="font-size: 10px; word-break: break-word;" id="tblComentarios">
							<?
							$resc = AmostraController::buscarComentariosCoferenciaDeAmostra($_GET['idamostra']);
							foreach($resc as $k => $rowc){?>
								<tr id="tr_<?=$rowc['idmodulocom']?>">
									<td id="td_<?=$rowc['idmodulocom']?>" style="line-height: 14px; padding: 8px; font-size: 11px;color:#666; width: 90%;"><?=dmahms($rowc['alteradoem']) ?> - <?=$rowc['alteradopor']?>: <?=nl2br($rowc['descricao']) ?></td>
								</tr>
							<? } ?>
						</table>
					</div>
				</div>
		<? }
		} ?>
	</div>
	<?

	if(cb::idempresa() == 1 && AmostraController::contarResultadosAssinados($_1_u_amostra_idamostra) >= 1){
		require '../inc/php/readonly.php';
	}
	if(!empty($_1_u_amostra_idamostra)){// trocar p/ cada tela a tabela e o id da tabela
		$_idModuloParaAssinatura = $_1_u_amostra_idamostra; // trocar p/ cada tela o id da tabela
		require 'viewAssinaturas.php';
	}
	$tabaud = "amostra"; //pegar a tabela do criado/alterado em antigo
	$idRefDefaultDropzone = "anexos";
	require 'viewCriadoAlterado.php';

	if(empty($jsonTelaamostraconf)){
		$jsonTelaamostraconf = "null";
	}
	require_once(__DIR__."/js/amostra_js.php")
	?>