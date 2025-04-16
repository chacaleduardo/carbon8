<?
require_once("../inc/php/validaacesso.php");
require_once("../model/evento.php");
require_once(__DIR__."/controllers/confereamostra_controller.php");
//Chama a Classe Evento
$eventoclass = new EVENTO();

//Recuperar o modulo de resultados associado conforme a unidade
$modResultadosPadrao = getModuloResultadoPadrao($unidadepadrao);
$acao =$_GET["acao"];
$unidadepadrao = getUnidadePadraoModulo($_GET["_modulo"]);
$exercicio	= $_GET["exercicio"];
$nome	= $_GET["cliente"];
$registro_1	= $_GET["registro_1"];
$registro_2	= $_GET["registro_2"];
$registrop_1	= $_GET["registrop_1"];
$registrop_2	= $_GET["registrop_2"];
$status	= $_GET["status"];
$idunidade =$_GET['unidade'];
$flgoficial	= $_GET["flgoficial"];

$controleass = $_SESSION[$struniqueid]["controleass"];

if($acao=="ini"){
	$controleass=1;//vai para o primeiro registro	
	// Executa a consulta completa somente 1 vez para recuperar a quantidade total de registros
	$booexeccount = true;
	//Atualiza o Uniqueid da pagina para guardar o ultima pagina utilizada
	//$_SESSION[$struniqueid]["uniqueid"] = $struniqueid;	
}else{
	if($acao=="prox"){
		$controleass=intval($controleass)+1;	
	}elseif($acao=="ant" and $controleass > 1){
		$controleass=intval($controleass)-1;
	}
}
//Apos o incremento da variavel, atribui para a ssesion o valor do proximo registro a ser chamado
$_SESSION[$struniqueid]["controleass"] = $controleass;


/*
 * TRATAMENTO E CONCATENACAO DO SQL PRINCIPAL
 */

	if($booexeccount==true){
		//echo($sqlcount.'<br>');
		$res = ConfereAmostraController::buscarAmostrasParaConferencia($registro_1,$registro_2,$registrop_1,$registrop_2,$nome,$idunidade,$flgoficial,$status,$exercicio);
		$qtdcount = $res->numRows();
		echo "<!-- ".$res->sql()." -->";
		if(empty($qtdcount)){
			echo '<br><br><br><div align="center">Não existem mais registros.</div> <br> <div align="center">ou <div><br> <div align="center">Não há nenhum registro para os parà¢metros informados!</div>';
			echo "<!-- ".$res->sql()." -->";
			die;
		}
		
		$arridresultado = array();
		$iarr = 0;
		//while para gravar todos os resultados para se poder navegar entre eles
		foreach($res ->data as $k => $rowqtd){
			$iarr++;
			$arridresultado[$iarr]=$rowqtd["idamostra"];			
			$booexeccount = false;
		}		
		//total de registros da consulta
		$_SESSION[$vargetsess]["qtdreg"] = $iarr;
		//grava todos dos os ids de resultados da consulta
		$_SESSION[$vargetsess]["arridres"] = $arridresultado;
	}
	$arridresultado=$_SESSION[$vargetsess]["arridres"];
	
	
	
	$qtdcount = $_SESSION[$vargetsess]["qtdreg"];	
	
if($qtdcount < $controleass){
	echo '<br><br><br><div align="center">Não existem mais registros.</div>';
	die;
}



if($qtdcount < $controleass){
	echo '<br><br><br><div align="center">Não existem mais registros.</div>';
	die;
}
function getResultadosConf($inIdamostra){
	
	$res = ConfereAmostraController::buscarConfResultados($inIdamostra);

	return $res;
}

 
$oAm=getAmostra($arridresultado[$controleass]);

$idsubtipoamostra=$oAm["idsubtipoamostra"];

$oRes=getResultadosConf($arridresultado[$controleass]);

//Verificação de quais inputs serão mostrados no TRA
//$arrConfInputs = getAmostraConfInputs(1);
//echo "SUBTIPO: ".$idsubtipoamostra;
//print_r($arrConfInputs);
//Mostrar ou esconder divs conforme configuração
function hide($inCol){
	global $idsubtipoamostra;
	$row = ConfereAmostraController::buscarCamposVisiveis(1,$idsubtipoamostra,$inCol);
	if(!empty($row)){
		$arrConf['colunas'][$row["idunidade"]][$row["idsubtipoamostra"]][$row["campo"]]=$row["campo"];
	}
	if($arrConf["colunas"]["1"][$idsubtipoamostra][$inCol]){
		return "";
	}else{
		return "hidden";
	}
}


//@todo: realizar a verificação através de Classes
function validaDataExame($ascriadoem,$dataamostra){
	if($ascriadoem<$dataamostra){
		die("<h1>Erro: Data Final Exame [".$ascriadoem."] < Data Início Exame [".$dataamostra."]</h1>");
	}
}

function buscacarrimbo($inidamostra){
    return ConfereAmostraController::buscarConferenciaAmostra($inidamostra);
}
	
?>

<div style="display:table-cell; width: 100%; height: 100%;">
	<pagina class="ordContainer">
		<header c>
			<?
			// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
			$figurarelatorio = ConfereAmostraController::buscarFigRelatorio($_SESSION["SESSAO"]["IDEMPRESA"]);
		?>
		
			<div class="row">
				<div class="col-md-3">
					<div class="logosup  "><img src="<?= $figurarelatorio ?>"></div>
				</div>
				<div class="col-md-6">
					<div class="titulodoc">
						CONFERÊNCIA DE AMOSTRA
					</div>
				</div>
			</div>

		</header>
		<div class="row">
			<div class="col-md-8">
				<div class="panel panel-default">
					<div class="panel-heading" style="background-color: #e6e6e6;">
						<table id="amostra" style="width: 100%;">
							<tr>
								<td>
									<b>REGISTRO</b> : <a title="" href="javascript:janelamodal('../?_modulo=amostraaves&_acao=u&idamostra=<?= $oAm["idamostra"] ?>')"><label style="padding: 4px; background:#fff;" ><?= $oAm["idregistro"] ?>/<label style="padding: 4px; background:#fff;" ><?= $oAm["exercicio"] ?></a>
					 </div>
									<td><b>REGISTRO PROVISÓRIO: </b>  <label style="padding: 4px; background:#fff;" ><?= $oAm["idregistroprovisorio"] ?></td>
								</td>
								<td>
									<b>DATA </b> : <?= dmahms($oAm["dataamostrah"], true) ?>
								</td>
								<td>
									<b>AMOSTRA</b> : <label style="padding: 4px; background:#fff;" ><?= $oAm["subtipoamostra"] ?>
								</td>
							</tr>
						</table>
				</div>
				<!-- <div class="row"  ">
					<div class="col-md-3">
					<b>DADOS DO CLIENTE</b>	
					</div>
				
				</div> -->
				<div class="panel-body" style="background-color: #f5f5f5;font-size:8px; ">


					<div class="row " style="margin-top: 20px;margin-bottom: 10px;">
						<div class="col-md-2 text-right" style="padding: 4px; padding-right: 6px;">
							CLIENTE:
						</div>
						<div class="col-md-10 " style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["nome"] ?></label>
						</div>
					</div>
					<div class="row hidden" style="margin-top: 20px;margin-bottom: 10px;">
						<div class="col-md-2 text-right" style="padding: 4px; padding-right: 6px;">
							R SOCIAL:
						</div>
						<div class="col-md-10 " style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["razaosocial"] ?></label>
						</div>
					</div>
					<div class="row hidden" style="margin-top: 20px;margin-bottom: 10px;">
						<div class="col-md-2 text-right" style="padding: 4px; padding-right: 6px;">
							ENDEREÇO:
						</div>
						<div class="col-md-10 " style="padding-left: 1px;">
						<?
									if(empty($oAm["enderecosacado"])){
						?>
										<div class="alert alert-warning">
										<span class="notProducao"><i class="fa fa-exclamation-triangle"></i>&nbsp;Favor preencher o endereço da propriedade no cadastro do cliente!</span>
										</div>		    
						<?
									}else{
										echo($oAm["enderecosacado"]);
									}
						?>
						</div>
					</div>


					


					<div class="col-md-12 " style="border-bottom: solid 1px; border-color:#dad2d2; padding-left: 12px; margin-top:8px; margin-bottom:12px"> </div>
					<div class="row">
						<div class="col-md-2 text-right" style="padding: 4px; padding: 2px; padding-right: 6px; ">
							AMOSTRA:
						</div>
						<div class="col-md-3 <?= hide("nroamostra") ?>" style="padding-left: 1px;  ">
						<label style="padding: 4px; background:#fff;" ><?=$oAm["subtipoamostra"]?></label>
						</div>
					</div>
					
					<div class="row">
						<div class="col-md-2 text-right <?= hide("nroamostra") ?> " style="padding: 4px; padding-right: 6px; ">
							QTD.:
						</div>
						<div class="col-md-10 my-3 <?= hide("nroamostra") ?>" style="padding-left: 1px;  ">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["nroamostra"] ?></label>
							<? if(count($oAm['identificadores'])) {?>
								<div class="w-100 d-flex flex-wrap row mt-3">
									<?foreach($oAm['identificadores'] as $indice => $item) {?>
										<div class="col-xs-2 px-0 mr-2 mb-2" style="padding: 0 !important;">
											<label for="" style="padding: 4px; background:#fff;">
												<?= $indice + 1?> - <?= $item['identificacao'] ? $item['identificacao'] : 'Não identificado' ?>
											</label>
										</div>
									<?}?>
								</div>
							<?}?>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("descricao") ?>" style="padding-right: 6p;">
							DESCRIÇÃO:
						</div>
						<div class="col-md-3 <?= hide("descricao") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["descricao"] ?></label>
						</div>
					</div>
					
					<div class="row">
						<div class="col-md-2 text-right  <?= hide("idnucleo") ?> " style="padding: 4px; padding-right: 6px;">
							NÚCLEO:
						</div>
						<div class="col-md-3  <?= hide("idnucleo") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["nucleo"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right  <?= hide("idnucleo") ?> " style="padding: 4px; padding-right: 6px;">
							NÚCLEO AMOSTRA:
						</div>
						<div class="col-md-3  <?= hide("nucleoamostra") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["nucleoamostra"] ?></label>
						</div>
					</div>

					<div class="row">
						<div class="col-md-2 text-right  <?= hide("idade") ?> " style="padding: 4px; padding-right: 6px;">
							IDADE:
						</div>
						<div class="col-md-3  <?= hide("idade") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["idade"] ?> <?= $oAm["tipoidade"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right  <?= hide("lote") ?> " style="padding: 4px; padding-right: 6px;">
							LOTE:
						</div>
						<div class="col-md-3  <?= hide("lote") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["lote"] ?> </label>
						</div>
						<div class="col-md-2 text-right <?= hide("idespeciefinalidade") ?>" style="padding: 4px; padding-right: 6px;">
							ESPÉCIE/FINALIDADE:
						</div>
						<div class="col-md-3 <?= hide("idespeciefinalidade") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["especietipofinalidade"] ?></label>
						</div>



					</div>
					<div class="row">
						<div class="col-md-2 text-right  <?= hide("pedido") ?>   " style="padding-left: 0px;padding-right: 2px;">
							Nº CLIFOR/PEDIDO:
						</div>
						<div class="col-md-3 <?= hide("pedido") ?> " style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["pedido"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right  <?= hide("granja") ?>" style="padding: 4px; padding-right: 6px;">
							GRANJA:
						</div>
						<div class="col-md-3  <?= hide("granja") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["granja"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("estexterno") ?>" style="padding: 4px; padding-right: 6px;">
							REG.EXTERNO:
						</div>
						<div class="col-md-3 <?= hide("estexterno") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["estexterno"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right  <?= hide("unidadeepidemiologica") ?>" style="padding: 4px; padding-right: 6px;">
							UN. EPIDEMIOLÓGICA:
						</div>
						<div class="col-md-3 <?= hide("unidadeepidemiologica") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["unidadeepidemiologica"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("cpfcnpjprod") ?>" style="padding: 4px; padding-right: 6px;">
							CNPJ/CPF (PROPRIETÁRIO):
						</div>
						<div class="col-md-3 <?= hide("cpfcnpjprod") ?>" style="padding-left: 1px; ">
						<label style="padding: 4px; background:#fff;" ><?= formatarCPF_CNPJ($oAm["cpfcnpjprod"], true) ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("uf") ?> " style="padding: 4px; padding-right: 6px;">
							UF:
						</div>
						<div class="col-md-3 <?= hide("uf") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["uf"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("cidade") ?>" style="padding: 4px; padding-right: 6px;">
							CIDADE:
						</div>
						<div class="col-md-3 <?= hide("cidade") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["cidade"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("numeroanimais") ?> " style="padding: 4px; padding-right: 6px;">
							Nº ANIMAIS:
						</div>
						<div class="col-md-3 <?= hide("numeroanimais") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["numeroanimais"] ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("galpao") ?>" style="padding: 4px; padding-right: 6px;">
							GALPÃO/AVIÁRIO:
						</div>
						<div class="col-md-3 <?= hide("galpao") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["galpao"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("alojamento") ?>" style="padding: 4px; padding-right: 6px;">
							DATA ALOJAMENTO:
						</div>
						<div class="col-md-3 <?= hide("alojamento") ?> " style="padding-left: 1px;">
						<label style="padding: 4px; background:#fff;" ><?= dma($oAm["alojamento"]) ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("numgalpoes") ?>" style="padding: 4px; padding-right: 6px;">
							Nº GALPÕES:
						</div>
						<div class="col-md-3 <?= hide("numgalpoes") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["numgalpoes"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right  <?= hide("linha") ?> " style="padding: 4px; padding-right: 6px;">
							LINHA:
						</div>
						<div class="col-md-3 <?= hide("linha") ?> " style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["linha"] ?>
						</div>
						<div class="col-md-2 text-right <?= hide("regoficial") ?> " style="padding: 4px; padding-right: 6px;">
							Nº REG. OF.:
						</div>
						<div class="col-md-3 <?= hide("regoficial") ?> " style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["regoficial"] ?></label>
						</div>
					</div>
					<div class="row">

						<div class="col-md-2 text-right <?= hide("nsvo") ?>  " style="padding: 4px; padding-right: 6px;">
							Nº SVO:
						</div>
						<div class="col-md-3 <?= hide("nsvo") ?> " style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["nsvo"] ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("rejeitada") ?>" style="padding: 4px; padding-right: 6px; height: 55px;">
							AMOSTRAS REJEITADAS / DESCARTADAS:
						</div>
						<div class="col-md-3  <?= hide("rejeitada") ?>" style="padding-left: 1px; padding-top: 35px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["rejeitada"] ?></label>
						</div>
					</div>
					<div class="col-md-12 " style="border-bottom: solid 1px; border-color:#dad2d2; padding-left: 12px; margin-top:8px; margin-bottom:12px"> </div>
					<div class="row">
						<div class="col-md-2 text-right  <?= hide("sinaisclinicosinicio") ?>" style="padding: 4px; padding-right: 6px;padding-left: 0px;">
							INÍCIO SINAIS CLÍNICOS:
						</div>
						<div class="col-md-3  <?= hide("sinaisclinicosinicio") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["sinaisclinicosinicio"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("sinaisclinicos") ?> " style="padding: 4px; padding-right: 6px;">
							SINAIS CLÍNICOS:
						</div>
						<div class="col-md-3  <?= hide("sinaisclinicos") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["sinaisclinicos"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right  <?= hide("achadosnecropsia") ?>" style="padding: 4px; padding-right: 6px; padding-left: 0px;">
							ACHADOS NECRÓPSIA:
						</div>
						<div class="col-md-3 <?= hide("achadosnecropsia") ?> " style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["achadosnecropsia"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right  <?= hide("suspclinicas") ?>" style="padding: 4px; padding-right: 6px;">
							SUSPEITAS CLÍNICAS:
						</div>
						<div class="col-md-3  <?= hide("suspclinicas") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["suspclinicas"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right  <?= hide("histproblema") ?>" style="padding: 4px; padding-right: 6px;">
							HISTÓRICO DO PROBLEMA:
						</div>
						<div class="col-md-3  <?= hide("histproblema") ?>" style="padding-left: 1px;; ">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["histproblema"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right  <?= hide("morbidade") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;;">
							MORBIDADE/Nº ANIMAIS:
						</div>
						<div class="col-md-3  <?= hide("morbidade") ?>" style="padding-left: 1px;;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["morbidade"] ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("letalidade") ?>" style="padding: 4px; padding-right: 6px; padding-left: 0px;">
							LETALIDADE/Nº ANIMAIS:
						</div>
						<div class="col-md-3  <?= hide("letalidade") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["letalidade"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right  <?= hide("mortalidade") ?> " style="padding: 4px; padding-right: 6px;">
							MORTALIDADE/Nº ANIMAIS:
						</div>
						<div class="col-md-3  <?= hide("mortalidade") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["mortalidade"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right  <?= hide("usomedicamentos") ?> " style="padding: 4px; padding-right: 6px;">
							USO DE MEDICAMENTOS:
						</div>
						<div class="col-md-3 <?= hide("usomedicamentos") ?>" style="padding-left: 1px; ">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["usomedicamentos"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("usovacinas") ?> " style="padding: 4px; padding-right: 6px;">
							USO DE VACINAS:
						</div>
						<div class="col-md-3 <?= hide("usovacinas") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["usovacinas"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("sexo") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
							SEXO:
						</div>
						<div class="col-md-3 <?= hide("sexo") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["sexo"] ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("datacoleta") ?>" style="padding: 4px; padding-right: 6px;">
							DATA COLETA:
						</div>
						<div class="col-md-3 <?= hide("datacoleta") ?>" style="padding-left: 1px;">
						<label style="padding: 4px; background:#fff;" ><?= dma($oAm["datacoleta"]) ?></label>
						</div>
					</div>

					<div class="row">
						<div class="col-md-2 text-right <?= hide("clienteterceiro") ?>" style="padding: 4px; padding-right: 6px;">
							CLIENTE 3º:
						</div>
						<div class="col-md-3 <?= hide("clienteterceiro") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["clienteterceiro"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("datachegada") ?> " style="padding: 4px; padding-right: 6px;">
							DATA CHEGADA:
						</div>
						<div class="col-md-3 <?= hide("datachegada") ?>" style="padding-left: 1px;">
						<label style="padding: 4px; background:#fff;" ><?= dma($oAm["datachegada"]) ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("formaarmazen") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
							FORMA ARMAZENAMENTO:
						</div>
						<div class="col-md-3 <?= hide("formaarmazen") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["formaarmazen"] ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("meiotransp") ?>" style="padding: 4px; padding-right: 6px;">
							MEIO DE TRANSP. AMOSTRAS:
						</div>
						<div class="col-md-3 <?= hide("meiotransp") ?>" style="padding-left: 1px; ">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["meiotransp"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("condconservacao") ?>" style="padding: 4px; padding-right: 6px;">
							CONDIÇÕES DE CONSERVAÇÃO:
						</div>
						<div class="col-md-3 <?= hide("condconservacao") ?>" style="padding-left: 1px; ">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["condconservacao"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("nucleoorigem") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
							NÚCLEO ORIGEM:
						</div>
						<div class="col-md-3 <?= hide("nucleoorigem") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["nucleoorigem"] ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("tipo") ?>" style="padding: 4px; padding-right: 6px;">
							TIPO:
						</div>
						<div class="col-md-3 <?= hide("tipo") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["tipo"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("especificacao") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
							ESPECIFICAÇÕES:
						</div>
						<div class="col-md-3 <?= hide("especificacao") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["especificacao"] ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("fornecedor") ?>" style="padding: 4px; padding-right: 6px;">
							FORNECEDOR:
						</div>
						<div class="col-md-3 <?= hide("fornecedor") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["fornecedor"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("partida") ?>" style="padding: 4px; padding-right: 6px;">
							PARTIDA:
						</div>
						<div class="col-md-3 <?= hide("partida") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["partida"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("datafabricacao") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
							DATA FABRICAÇÃO:
						</div>
						<div class="col-md-3 <?= hide("datafabricacao") ?> " style="padding-left: 1px;">
						<label style="padding: 4px; background:#fff;" ><?= dma($oAm["datafabricacao"]) ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("identificacaochip") ?>" style="padding: 4px; padding-right: 6px;">
							CHIP/IDENTIF.:
						</div>
						<div class="col-md-3 <?= hide("identificacaochip") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["identificacaochip"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("diluicoes") ?>" style="padding: 4px; padding-right: 6px;">
							DILUIÇÕES:
						</div>
						<div class="col-md-3 <?= hide("diluicoes") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["diluicoes"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right  <?= hide("nroplacas") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
							Nº PLACAS:
						</div>
						<div class="col-md-3  <?= hide("nroplacas") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["nroplacas"] ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("nrodoses") ?>" style="padding: 4px; padding-right: 6px;">
							Nº DOSES:
						</div>
						<div class="col-md-3 <?= hide("nrodoses") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["nrodoses"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("semana") ?>" style="padding: 4px; padding-right: 6px;">
							SEMANA:
						</div>
						<div class="col-md-3 <?= hide("semana") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["semana"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("notafiscal") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
							NOTA FISCAL:
						</div>
						<div class="col-md-3 <?= hide("notafiscal") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["notafiscal"] ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("vencimento") ?>" style="padding: 4px; padding-right: 6px;">
							VENCIMENTO:
						</div>
						<div class="col-md-3 <?= hide("vencimento") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["vencimento"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("fabricante") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
							FABRICANTE:
						</div>
						<div class="col-md-3 <?= hide("fabricante") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["fabricante"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("sexadores") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
							SEXADORES:
						</div>
						<div class="col-md-3 <?= hide("sexadores") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["sexadores"] ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("localexp") ?>" style="padding: 4px; padding-right: 6px;">
							LOCAL ESPECÍFICO:
						</div>
						<div class="col-md-3 <?= hide("localexp") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["localexp"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("localcoleta") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
							LOCAL COLETA:
						</div>
						<div class="col-md-3 <?= hide("localcoleta") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["localcoleta"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("responsavel") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
							RESPONSÁVEL COLETA:
						</div>
						<div class="col-md-10 <?= hide("responsavel") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["responsavel"] ?></label>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 text-right <?= hide("responsavelcolcrmv") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
							RESPONSÁVEL COLETA CRMV:
						</div>
						<div class="col-md-3 <?= hide("responsavelcolcrmv") ?>" style="padding-left: 1px; ">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["responsavelcolcrmv"] ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("responsavelcolcont") ?>" style="padding: 4px; padding-right: 6px;  ">
							RESPONSÁVEL COLETA CONTATO:
						</div>
						<div class="col-md-3 <?= hide("responsavelcolcont") ?>" style="padding-left: 1px; ">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["responsavelcolcont"] ?></label>
						</div>
					</div>
 
					<div class="row">
						<div class="col-md-2 text-right <?= hide("responsavelof") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
							RESPONSÁVEL OFICIAL:
						</div>
						<div class="col-md-2 <?= hide("responsavelof") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["responsavelof"] ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("responsavelofcrmv") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
							CRMV:
						</div>
						<div class="col-md-2 <?= hide("responsavelofcrmv") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["responsavelofcrmv"] ?></label>
						</div>
						<div class="col-md-2 text-right <?= hide("responsaveloftel") ?>" style="padding: 4px; padding-right: 6px;">
							TEL:
						</div>
						<div class="col-md-2 <?= hide("responsaveloftel") ?>" style="padding-left: 1px;">
							<label style="padding: 4px; background:#fff;" ><?= $oAm["responsaveloftel"] ?></label>
						</div>
					</div>

					<div class="row">
						<div class="row">
							<div class="col-md-2 text-right <?= hide("lacre") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
								LACRE:
							</div>
							<div class="col-md-3 <?= hide("lacre") ?>" style="padding-left: 1px;">
								<label style="padding: 4px; background:#fff;" ><?= $oAm["lacre"] ?></label>
							</div>
							<div class="col-md-2 text-right <?= hide("tc") ?>" style="padding: 4px; padding-right: 6px;">
								TC:
							</div>
							<div class="col-md-3 <?= hide("tc") ?>" style="padding-left: 1px;">
								<label style="padding: 4px; background:#fff;" ><?= $oAm["tc"] ?></label>
							</div>
						</div>
						<div class="row">
							<div class="col-md-2 text-right <?= hide("observacao") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
								OBSERVAÇÃO:
							</div>
							<div class="col-md-3 <?= hide("observacao") ?>" style="padding-left: 1px;">
								<label style="padding: 4px; background:#fff;" ><?= nl2br($oAm["observacao"]) ?></label>
							</div>
						</div>
						<div class="row">
							<div class="col-md-2 text-right <?= hide("observacaointerna") ?>" style="padding: 4px; padding-right: 6px;padding-left: 1px;">
								OBSERVAÇÃO INTERNA:
							</div>
							<div class="col-md-3 <?= hide("observacaointerna") ?>" style="padding-left: 1px;">
								<label style="padding: 4px; background:#fff;" ><?= nl2br($oAm["observacaointerna"]) ?></label>
							</div>
						</div>
						<? //consulta para trazer a preferencia, de acordo com a pessoa cadastrada na amostra. 
							$respref = ConfereAmostraController::buscarAmostraEPreferencia($oAm['idamostra']);
							foreach($respref as $k => $rowp){
								$preferencia = $rowp['observacaore'];
									if ($preferencia != ''){  // verificacao para mostrar apenas se o campo for diferente de vazio, ou seja, se existir informação. 
								?>
									<div class="row">
										<div class="col-md-2 text-right  "  style="padding: 4px; padding-right: 6px;padding-left: 1px;">
											PREFERÊNCIA:
										</div>
										<div class="col-md-3   " style="padding-left: 1px;">
											<label style="padding: 4px; background:#fff;" ><?= $preferencia?></label>
										</div>
									</div>
								<?	}
								}
							?>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading" style="background-color: #e6e6e6;">
					<b>TESTES</b>
				</div>
				<div class="panel-body" style="background-color: #f5f5f5;font-size:8px;">
					<table id="amostra" style="width: 100%;">
						<thead>
							<tr>
								
								<th style="width: 20%;">TESTE</th>
								<th style="width: 20%;">QTD.</th>
								<th style="width: 40%">OFICIAL</th>
								<th style="width: 20%">N. Pedido</th>
							</tr>
						</thead>	
							
						<? $i=0;
							foreach ($oRes as $k => $v){
								 $idresultado = $k;  
							?>
							<tr style="height: 30px;">
								
								<td>
									<a href="javascript:janelamodal('../?_modulo=resultaves&_acao=u&idresultado=<?= $idresultado ?>')" style="color:<?= $v["status"] ?> ">
										<span style="font-size:10px;"> <?=$v["codprodserv"]?></span>
									</a>
								</td>
									
								<td><?= $v["quantidade"] ?></td>
								<td>
									<a href="javascript:janelamodal('../?_modulo=resultaves&_acao=u&idresultado=<?= $idresultado ?>')" style="color:<?= $v["status"] ?> ">
										<?=$v["secretaria"];?>
									</a>
								</td>
								<td><?= $v['npedido'] ?></td>
							</tr>
							<?}?>
					</table>
				</div>
			</div>

			<div class="panel panel-default divHistorico" >
						<div class="panel-heading" style="height:34px">
							<div class="row">
								<div class="col-md-12" style="font-size: 14px; margin-left: -15px;"> <strong>OBSERVAÇÃO CONFERÊNCIA</strong>
								<button id="cbSalvar" type="button" class="btn btn-success btn-xs pull-right" style=" margin-top: -4px; margin-right: -27px;" onclick="salvarComentario('i',<?=$oAm['idamostra']?>,'descricao')" title="Salvar">
									<i class="fa fa-circle"></i>Salvar
								</button>
								</div>                          
							
							</div>
						</div>
						<div class="panel-body" style="max-height: 100px; min-height: 100px; height: 100px;">
							<input id="modulocom_idempresa" type="hidden" value="1">
							<input id="modulocom_idmodulo" type="hidden" value="<?= $oAm["idamostra"] ?>">
							<input id="modulocom_modulo" type="hidden" value="amostraaves">
							<textarea id="modulocom_descricao"style="width: 100%; height: 80px; resize: none;"></textarea>
							<input id="modulocom_status" type="hidden" value="ATIVO">
						</div>
						<div class="panel-body">
							<table class="table table-striped planilha" style="font-size: 10px; word-break: break-word;" id="tblComentarios">
								<?
								$resc = ConfereAmostraController::buscarComentariosCoferenciaDeAmostra($oAm["idamostra"]);
								foreach($resc as $k => $rowc){
								
									?>
									<tr id="tr_<?=$rowc['idmodulocom']?>">   
										<td  id="td_<?=$rowc['idmodulocom']?>" style="line-height: 14px; padding: 8px; font-size: 11px;color:#666; width: 90%;"><?=dmahms($rowc['alteradoem'])?> - <?=$rowc['alteradopor']?>: <?=nl2br($rowc['descricao'])?></td>
										
											<?if($_SESSION["SESSAO"]["USUARIO"]==$rowc['criadopor']){ ?>
												<td><i class="btn fa fa-pencil fa-1x " onclick="salvarComentario('u',<?=$rowc['idmodulocom']?>,'<?=$rowc['descricao']?>')" title='Editar!' style="padding: 0;color: blue;"></i></td>
												<td><i class="btn fa fa-trash fa-1x " onclick="salvarComentario('d',<?=$rowc['idmodulocom']?>,'<?=$rowc['descricao']?>')" title='Excluir!' style="padding: 0;color: red;"></i></td>
											<?}?>
										
									</tr>
									<?
								} ?>
							</table>
						</div>
					</div>	



		</div>
</div>
</div>
</div>




<?
$qtdca=buscacarrimbo($arridresultado[$controleass]);
if($qtdca>0){
	$clsfooter="clsFootera";
}else{
	$clsfooter="clsFooterf";
}

?>


<div id="Footer" class="<?= $clsfooter ?>">
	<table width="100%">
		<tr>
			<td style="font-size:12px;padding-left:15px;width:150px;">
				<?echo"Resultado ".$controleass." de ".$qtdcount;?>
			</td>
			<td align="center">
				<table align="center">
					<tr>
						<?
						$stralerta = "";
						if($row["alerta"]=="Y"){
							$stralerta = "checked";
						}
						if(empty($row["idsecretaria"])){
							$varoficial = "N";
						}elseif(!empty($row["idsecretaria"])){
							$varoficial = "Y";
						}
?>
						<td>
							&nbsp;&nbsp;&nbsp;
							<input type="button" tabindex="2" value="Conferir" id="btassina" class="btassina" onfocus="this.className='btassinafoco';" onblur="this.className='btassina';" onClick="carrimbo(<?= $arridresultado[$controleass] ?>,'inserir','conferencia');">
							<input type="button" tabindex="3" value="Retirar" id="btretira" class="btretira" onfocus="this.className='btretirafoco';" onblur="this.className='btretira';" onClick="carrimbo(<?= $arridresultado[$controleass] ?>,'retirar','conferencia');">
							&nbsp;&nbsp;&nbsp;

						</td>
					</tr>
				</table>
			</td>
			<td style="width:150px;"></td>
		</tr>
	</table>
</div>
<hr>
<br>

</pagina>
