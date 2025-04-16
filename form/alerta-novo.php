<?
require_once "../inc/php/validaacesso.php";
require_once "../models/evento.php";

/*
 * Centralizar a consulta de Módulo
 * Evitar falhas em relação à  Módulos Vinculados
 * Complementar com as colunas necessárias diretamente na consulta
 */

/*if($_SESSION["SESSAO"]["IDPESSOA"]!=1098 and $_SESSION["SESSAO"]["IDPESSOA"]!=6494 and $_SESSION["SESSAO"]["IDPESSOA"]!=778){
Die('Evento em Manutenção... Previsão de Retorno 22-11-2019.');
}*/

/* TAREFAS EM ABERTO COM AS TAREFAS FINALIZADAS*/
if ($_GET['ordenacao']) {
    switch ($_GET['ordenacao']) {
        case 'status':
            $ord = ' es.rotulo asc, e.prazo asc';
            break;
        case 'statusd':
            $ord = 'es.rotulo desc, e.prazo asc';
            break;
        case 'tipo':
            $ord = 'e.ideventotipo, er.visualizado asc, e.prazo asc';
            break;
        case 'tipod':
            $ord = 'e.ideventotipo desc, er.visualizado asc, e.prazo asc';
            break;
        case 'evento':
            $ord = 'e.evento asc';
            break;
        case 'eventod':
            $ord = 'e.evento desc';
            break;
        case 'criadopor':
            $ord = 'e.criadopor asc';
            break;
        case 'criadopord':
            $ord = 'e.criadopor desc';
            break;
        case 'idevento':
            $ord = 'e.idevento asc';
            break;
        case 'ideventod':
            $ord = 'e.idevento desc';
            break;
        case 'data':
            $ord = ' e.fim, e.fimhms, e.prazo asc';
            break;
        case 'datad':
            $ord = ' e.fim desc, e.fimhms desc, e.prazo desc';
            break;
    }
} else {
    $_GET['ordenacao'] = '0';
	if ($_GET['vfilter'] == 'ocultos') {
		 $ord = "e.idevento desc";
	}else{
		 $ord = "er.visualizado asc, if(et.prazo = 'N', e.fim, e.prazo) asc, e.fimhms asc ";
	}
}

if ($_GET['vfilter'] == 'minhas') {
    $ocultosFilter = "AND e.idpessoa = " . $_SESSION["SESSAO"]["IDPESSOA"];
} else if ($_GET['vfilter'] == 'ocultos') {
    $ocultosFilter = 'AND er.oculto = 1';
} else {
    $ocultosFilter = 'AND er.oculto = 0';
}

//Seta a sessão do continuar o filtro do Tipo de Evento
// 17-02-2020 - Lidiane
$filtro = json_decode(userPref('s', 'evento.alertafiltrotipoevento'));

forEach($filtro AS $_filtro){
	$_filtro = $_filtro;
}

if($_filtro AND $_filtro != 'undefined' AND $_filtro != 'null'){
	$qtde = count(explode(',', $_filtro));
	$filterEventoTipo = ' AND et.ideventotipo IN ('.$_filtro.')';
}

$evento = new EVENTO();
$totalResultados = $evento->getQuantidadeEvento();
?>
<link href="./inc/css/alerta.css?_<?=date("dmYhms")?>" rel="stylesheet">
<div class="hidden toggle-content" id="example">
    <div class="row" style="width: 100%; margin: 0px;">
        <div class="col-md-12" style="padding:8px;border-bottom:1px solid #ccc;background:#fff;">
            <div class="row">
                <div class="col-md-8">
                    <div class="col-md-12 mcor" style="margin-bottom:4px;border-radius:15px;text-transform:uppercase;border:1px solid #28d6fb;color:#333;padding: 2px 6px;font-size:9px;word-break:normal;text-align:center;">
                        <span class="meventotipo"></span>
                    </div>
                    <div class="col-md-12 mcorstatus" style="border-radius:15px;text-transform:uppercase;color:#fff;padding: 2px 6px;font-size:9px;word-break:normal;text-align:center;">
                        <i class="fa fa-calendar" style="font-size: 12px; line-height: 9px;margin-right:4px;"></i><span class="mstatus"></span>
                    </div>
                </div>
                <div class="col-md-4">
                    <a class="close toggle fright" href="#example" id="fechar"></a>
                    <a class="mlink fright" href="#" onclick="javascript:modalEvento(this,'?_modulo=evento&_acao=u&idevento=<?=$r['idevento']?>')">
                        <i class="fa fa-window-maximize" style="font-size:28px;color:#ccc;"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-12 " style="padding:0px;">
            <div class="tab " style="<?if ($_SESSION[" SESSAO"]["IDPESSOA"] != '64942') {echo 'display:none';}?>">
                <button class="tablinks " onclick="openTab(event, 'conteudo')" style="width:50%">
                    <i class="fa fa-align-left" aria-hidden="true"></i>
                </button>
                <button class="tablinks active historico" onclick="openTab(event, 'historico')" style="width:50%;display:none;">
                    <i class="fa fa-comments-o " aria-hidden="true"></i>
                </button>
                <button class="tablinks" onclick="openTab(event, 'participantes')" style="width:50%">
                    <i class="fa fa-users" aria-hidden="true"></i> 
                </button>
            </div>
        </div>
        <div class="col-md-12 ">
            <label class="dd">
                <div class="dd-button mcorstatusresp">
                    <span class="mrotuloresp"></span>
                </div>
                <input type="checkbox" class="dd-input" id="test">
                <ul class="dd-menu" id="divbotoes">
                </ul>
            </label>
        </div>
        <div class="col-md-12" style="padding:0px;">
            <!-- Tab content -->
            <div id="conteudo" class="tabcontent" style="display: block;">
                <label style="cursor:pointer;text-align:right; font-size:11px;padding:0px 4px; margin:3px;float:left"
                    class="ideventos alert-warning"><span class="midevento"></span> </label><Br>
                <h5 style="margin-left:3px;"><b><span class="mevento"></span></b></h5>
                <div class="mdescricao" style="margin-left:3px;white-space: pre-wrap;">
                    <p></p>
                </div>
                <input name="_1_i_eventocom_idevento" id="idevento" type="hidden" value="" class="midevento"
                    readonly='readonly'>
                <div class="panel-body" style="max-height: 100px; min-height: 100px; height: 100px;">
                    <textarea class="caixa" name="_1_i_eventocom_descricao" name=""
                        style="width: 100%; height: 80px; resize: none;"></textarea>
                </div>
                <table class="table table-striped planilha" style="font-size: 11px; word-break: break-word;"
                    id="tblHistorico">
                    <tbody></tbody>
                </table>
                <br><br><br>
            </div>
            <div id="historico" class="tabcontent">
                <label style="cursor:pointer;text-align:right; font-size:11px;padding:0px 4px; margin:3px;float:left"
                    class="ideventos alert-warning"><span class="midevento"></span> </label> <Br>
                <h5><b><span class="mevento"></span></b></h5>
                <input name="_1_i_eventocom_idevento" id="idevento" type="hidden" value="" class="midevento"
                    readonly='readonly'>
                <div class="panel-body" style="max-height: 100px; min-height: 100px; height: 100px;">
                    <textarea class="caixa" name="_1_i_eventocom_descricao" name=""
                        style="width: 100%; height: 80px; resize: none;"></textarea>
                </div>
                <div class="panel-body">
                    <table class="table table-striped planilha" style="font-size: 10px; word-break: break-word;"
                        id="tblHistorico">
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div id="participantes" class="tabcontent">
                <div class="panel panel-default">
                    <div class="panel-heading">Participantes</div>
                    <div class="panel-body" id="localInfo1">
                        <?if (empty($_1_u_evento_idevento)) {echo '<p style="color:#aaa;"><i>Crie o evento para adicionar os participantes</i></p>';} else {?>
							<? //if($dono=='Y'){ ?>
								<table>
									<tr>
										<td id="tdfuncionario"><input id="pessoavinc" class="compacto" type="text" cbvalue placeholder="Selecione" <?if (empty($_1_u_evento_idevento)) { echo 'disabled="true"';}?>></td>
										<td id="tdsgsetor"><input id="sgsetorvinc" class="compacto" type="text" cbvalue placeholder="Selecione" <?if (empty($_1_u_evento_idevento)) { echo 'disabled="true"';}?>></td>
										<td class="nowrap" style="width: 110px">
											<div class="btn-group nowrap" role="group" aria-label="...">
												<button onclick="showfuncionarioAlerta()" type="button"
														class=" btn btn-default fa fa-user fa-1x hoverlaranja pointer floatright "
														title="Selecionar Funcionário"
														style="margin-right: 8px; border-radius: 4px;" 
														<?if(empty($_1_u_evento_idevento)) {echo 'disabled="true"';}?>>&nbsp;</button>
												<button onclick="showsgsetorAlerta()" type="button"
														class=" btn btn-default fa fa-users hoverlaranja pointer floatright selecionado"
														title="Selecionar Setor" style="margin-right: 8px; border-radius: 4px;" <?
														if (empty($_1_u_evento_idevento)) {echo 'disabled="true"';}?>>&nbsp;</button>
											</div>
										</td>
									</tr>
								</table>
							<?//}
						}?>
                        <div class="col-md-12">
                            <div class="panel panel-default" style="background:#fff;height: 100%;overflow: auto;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12" style="background:#f1f1f1;border-bottom:1px solid #ccc;display:none">
            <div class="col-md-12" style="font-size:11px;">
                <div class="col-md-3" style="font-size:11px;">
                    Criação:
                </div>
                <div class="col-md-9" style="font-size:11px;">
                    <span class="mcriadoempor"></span>
                </div>

            </div>

            <div class="col-md-12" style="font-size:11px;">
                <div class="col-md-3" style="font-size:11px;">
                    Alteração:
                </div>
                <div class="col-md-9" style="font-size:11px;">
                    <span class="malteradoempor"></span>
                </div>
            </div>
        </div>
        <p>
        </p>
    </div>
</div>
<?
echo '<script>';
echo 'var ordenacao = "' . $_GET[ordenacao] . '";';
echo '</script>';
?>
<link href="./inc/css/show-hide/show-hide.css?_<?=date("dmYhms")?>" rel="stylesheet">
<link href="./inc/css/fontawesome/font-awesome.min.css?_<?=date("dmYhms")?>" rel="stylesheet">
<script src="./inc/js/show-hide/show-hide.js?_<?=date("dmYhms")?>"></script>
<script src="./inc/js/models/evento.js?_<?=date("dmYhms")?>"/>

<div class="panel panel-default" id="divalerta" style="background:#fff;border:none;">
    <div class="panel-heading hidden">Tarefas</div>
    <div class="panel-body" style="padding: 8px 20px;">
        <div class="row" style="margin-bottom:16px">
            <div class="col-lg-12">
                <button class="btn btn-xs btn-primary" onclick="novaTarefa()">Novo Evento</button>
                <div class="btn-group fright" style="margin-right: 10px;" role="group">
                    <button type="button" class="btn btn-default btn-xs fonte08 selecionado" id="btn-2"
                        onclick="toggleFiltrarTarefas('todas')">Ativos
                    </button>
                    <button type="button" class="btn btn-default btn-xs fonte08" id="btn-1"
                        onclick="toggleFiltrarTarefas('minhas')" style="display:none">Meus Eventos
                    </button>
                    <button type="button" class="btn btn-default btn-xs fonte08" id="btn-3"
                        onclick="toggleFiltrarTarefas('ocultos')">Ocultos
                    </button>
                </div>
                <div class="btn-group fright" role="group">
                    <button type="button" class="btn btn-default btn-xs fonte08 selecionado hide" id="btn-3"
                        onclick="toggleMostrarTarefasFinalizadas(false)">Pendentes</button>
                    <button type="button" class="btn btn-default btn-xs fonte08 hide" id="btn-4"
                        onclick="toggleMostrarTarefasFinalizadas(true)">Todas</button>
                </div>
                <input type="text" placeholder="Localizar evento" id="searchInput"
                    style="font-size:8px; max-width:30%; min-width: 30%;border:1px solid #ccc !important" class="fright"
                    filtrarElementos="tbEvento">
            </div>
        </div>
        <div class="row hidden-xs hidden-sm hidden-md" style="margin:0px;padding:4px;background:#666;border-radius:4px;">
            <div class="col-lg-2" style="font-size: 11px;text-align:center;">
                <label style="" id="tipoevento">
					<a style="color:#fff;font-weight:normal;" class="link"
                        href="javascript:$(CB.oModuloHeader, CB.oModuloHeaderBg).addClass('hidden');CB.loadUrl({urldestino: 'form/alerta.php?vfilter=<?=$_GET['vfilter'];?>&ordenacao=<?if ($_GET['ordenacao'] == 'tipo') {echo 'tipod';} else {echo 'tipo';}?>'});">TIPO
						<?if ($_GET['ordenacao'] == 'tipo') {echo '<i class="fa fa-sort-down"></i>';} elseif ($_GET['ordenacao'] == 'tipod') {echo '<i class="fa fa-sort-up"></i>';}?></a><label>
						&nbsp;&nbsp;&nbsp;<a style="color:#fff;font-weight:normal;" href="#" onclick="carregaFiltroTipoEvento()" class="selectpicker valoresselect" multiple="multiple" data-live-search="true" id="carregaFiltroEvento" data-toggle="modal-popover" data-placement="bottom" role="button"><i class="fa fa-filter"></i>&nbsp;<span class="badge fundovermelho"><?=$qtde;?></span></a>
					<div id="popupBottom" class="popover"><div class="webui-arrow" style="left: 464px;"></div><div class="popover-content"></div></div>						
            </div>
            <div class="col-lg-4" style="font-size: 11px;text-align:center;">
                <label><a style="color:#fff;font-weight:normal;" class="link"
                        href="javascript:$(CB.oModuloHeader, CB.oModuloHeaderBg).addClass('hidden');CB.loadUrl({urldestino: 'form/alerta.php?vfilter=<?=$_GET['vfilter'];?>&ordenacao=<?if ($_GET['ordenacao'] == 'evento') {echo 'eventod';} else {echo 'evento';}?>'});">EVENTO
                        <?if ($_GET['ordenacao'] == 'evento') {echo '<i class="fa fa-sort-down"></i>';} elseif ($_GET['ordenacao'] == 'eventod') {echo '<i class="fa fa-sort-up"></i>';}?></a><label>
            </div>
            <div class="col-lg-6" style="text-align:center;font-size: 11px;">
                <div class="col-lg-5" style="font-size: 11px;margin: 0px; padding: 0px;">
                    <label><a style="color:#fff;font-weight:normal;" class="link"
                            href="javascript:$(CB.oModuloHeader, CB.oModuloHeaderBg).addClass('hidden');CB.loadUrl({urldestino: 'form/alerta.php?vfilter=<?=$_GET['vfilter'];?>&ordenacao=<?if ($_GET['ordenacao'] == 'status') {echo 'statusd';} else {echo 'status';}?>'});">STATUS
                            <?if ($_GET['ordenacao'] == 'status') {echo '<i class="fa fa-sort-down"></i>';} elseif ($_GET['ordenacao'] == 'statusd') {echo '<i class="fa fa-sort-up"></i>';}?></a><label>
                            
                </div>
                <div class="col-lg-3" style="font-size: 11px;text-align:left;">
                    <label><a style="color:#fff;font-weight:normal;" class="link"
                            href="javascript:$(CB.oModuloHeader, CB.oModuloHeaderBg).addClass('hidden');CB.loadUrl({urldestino: 'form/alerta.php?vfilter=<?=$_GET['vfilter'];?>&ordenacao=<?if ($_GET['ordenacao'] == 'criadopor') {echo 'criadopord';} else {echo 'criadopor';}?>'});">CRIADO POR
                            <?if ($_GET['ordenacao'] == 'criadopor') {echo '<i class="fa fa-sort-down"></i>';} elseif ($_GET['ordenacao'] == 'criadopord') {echo '<i class="fa fa-sort-up"></i>';}?></a><label>
                </div>
				<div class="col-lg-3" style="font-size: 11px;text-align:left;">
                    <label><a style="color:#fff;font-weight:normal;"
							href="javascript:$(CB.oModuloHeader, CB.oModuloHeaderBg).addClass('hidden');CB.loadUrl({urldestino: 'form/alerta.php?vfilter=<?=$_GET['vfilter'];?>&ordenacao=<?if ($_GET['ordenacao'] == 'data') {echo 'datad';} else {echo 'data';}?>'});">DATA/PRAZO
							<?if ($_GET['ordenacao'] == 'data') {echo '<i class="fa fa-sort-down"></i>';} elseif ($_GET['ordenacao'] == 'datad') {echo '<i class="fa fa-sort-up"></i>';}?></a><label>
                </div>
                <div class="col-lg-1" style=" text-align: right;font-size: 11px;">
                </div>
            </div>
        </div>
        <div class="eventos">
            <?
			//Função que retorna a quantidade de Eventos
			$countResultados = $evento->getListaEventoQtde($ocultosFilter, $filterEventoTipo, $ord);
			//Função que retorna os Eventos
			$dados = $evento->getListaEvento($ocultosFilter, $filterEventoTipo, $ord);
			while ($r = mysqli_fetch_assoc($dados)) 
			{  
				$resb = $evento->getBotoes($r['idevento']);
				while ($rowb = mysqli_fetch_assoc($resb)) 
				{
					$ideventoresp = $rowb['ideventoresp'];
					if (($rowb['botaocriador'] == 'Y' and $rowb['criadopor'] == $_SESSION["SESSAO"]["USUARIO"]) or ($rowb['botaoparticipante'] == 'Y' and $rowb['criadopor'] != $_SESSION["SESSAO"]["USUARIO"]) or ($rowb['botaocriador'] != 'Y' and $rowb['botaoparticipante'] != 'Y')) {
						$fluxo .= $sep . $rowb['botao'] . "*" . $rowb['cor'] . "*" . $rowb['ideventostatusf'] . "*" . $rowb['ideventoresp'] . "*" . $rowb['ocultar'] . "*" . $rowb['cortexto'];
						$sep = '|';
					}
				}

				if ($r["anonimo"] == 'Y') {
					$origem = '<i><b>ANÔNIMO</b></i>';
				} else {
					$origem = $r["nomecurto"];
				}

				$dataTarefa = "";

				if ($r["prazo"] != "0000-00-00 00:00:00") {
					//echo $r["configprazo"];
					if ($r["configprazo"] == "N") {
						$dataTarefa = substr(dmahms($r["inicio"] . ' ' . $r["iniciohms"]), 0, -3) . '<br>' . substr(dmahms($r["fim"] . ' ' . $r["fimhms"]), 0, -3);

						$current = strtotime(date("Y-m-d"));
						$date = strtotime($r["inicio"]);

						$datediff = $date - $current;
						$difference = floor($datediff / (60 * 60 * 24));
						if ($difference == 0) {
							$dataTarefa = 'HOJE ' . substr($r["iniciohms"], 0, -3);
							$coricone = '#0f8041;background:#0f8041;color:#fff;';
						} else if ($difference > 1) {
							$dataTarefa = substr(dmahms($r["inicio"] . ' ' . $r["iniciohms"]), 0, -3);
							$coricone = '#999;color:#999;';
						} else if ($difference > 0) {
							$dataTarefa = 'AMANHÃ ' . substr($r["iniciohms"], 0, -3);
							$coricone = '#999;color:#999;';
						} else if ($difference < -1) {
							$dataTarefa = substr(dmahms($r["inicio"] . ' ' . $r["iniciohms"]), 0, -3);
							$coricone = '#999;background:#999;color:#666;';
						} else {
							$dataTarefa = 'ONTEM ' . substr($r["iniciohms"], 0, -3);
							$coricone = '#999;background:#999;color:#666;';
						}

					} else {
						$dataTarefa = dma($r["prazo"]);
					}
				}

				$eventoTipo = $r["eventotipo"];
				$cor = $r["cor"];
				$eventoTitulo = $r["evento"];
				?> <a class="toggle" href="#example" id="abrir">
					<div class="row eventoRow<?=(!$r['visualizado']) ? ' naoVisualizado' : '';?>"
						style="color:#333 !important;padding:8px; position:relative; " fluxo="<?=$fluxo;?>"
						id="idevento_<?=$r["idevento"]?>" travasala="<?=$r["travasala"];?>" modulo="<?=$r["modulo"];?>"
						idmodulo="<?=$r["idmodulo"];?>" diainteiro="<?=$r["diainteiro"];?>"
						idequipamento="<?=$r["idequipamento"];?>" duracaohms="<?=$r["duracaohms"];?>"
						configprazo="<?=$r["configprazo"];?>" prazo="<?=$r["prazo"]?>" inicio="<?=$r["inicio"]?>"
						iniciodata="<?=$r["iniciodata"]?>" iniciohms="<?=$r["iniciohms"]?>" posicao="<?=$r["posicao"]?>"
						ideventoresp="<?=$ideventoresp?>" idevento="<?=$r["idevento"]?>" eventotipo="<?=$r["eventotipo"]?>"
						cor="<?=$r["cor"]?>" cortextostatus="<?=$r["cortextostatus"]?>" corstatus="<?=$r["corstatus"]?>"
						corstatusresp="<?=$r["cortextostatus"]?>" rotuloresp="<?=$r["rotuloresp"]?>"
						status="<?=$r["status"]?>" evento="<?=$r["evento"]?>" data-toggle="collapse" href="#<?=$r["ideventotipo"]?>"
						descricao="<?=str_replace('"', '*', htmlentities($r["descricao"]));?>" 
						criadoempor="<?=$r["criadoempor"];?>" alteradoempor="<?=$r["alteradoempor"];?>" 
                        datafim="<?=dma($r["fim"]);?>">

						<div class="col-lg-2 col-sm-3 col-xs-6 atalhoEvento" style="font-size: 12px; color: #333333;">
							<div class="col-lg-12 col-xs-12" style="font-size: 12px;text-align:center">
								<div
									style="border-radius:15px;border:1px solid <?=$cor?>;color:<?=$cor?>;padding: 2px 6px;font-size:9px;word-break:normal;text-align:center;text-transform:uppercase;">
									<?=$eventoTipo?>
									<br>
									<div style="text-align: center;font-size: 9px; padding: 0px 4px; color: #333; background-color: transparent; width: auto;"
										class="ideventos alert-warning"><?=$r["idevento"]?></div>
								</div>

							</div>
						</div>

						<div class="col-lg-4 col-sm-9 col-xs-12 atalhoHist"
							style="display: block; word-break: break-word;font-size: 12px;">
							<div class="col-lg-12 col-xs-12 descricao"
								style="min-height: 24px;border-bottom:1px solid #ddd;text-transform:uppercase;font-size:10px;margin:0px 12px;">
								<?=$eventoTitulo?></div>
						</div>
						<?
						if ($r['prazorestante'] == '0d 00h 00m 00s ') {
							$r['prazorestante'] = '<i>venc.</i>';
						} else {
							$r['prazorestante'] = explode(" ", $r['prazorestante']);
							if ($r['prazorestante'][0] != '0d') {
								if (strpos($r['prazorestante'][0], '-') !== false) {
									$r['prazorestante'] = '<i>venc.</i>';
								} else {
									$r['prazorestante'] = $r['prazorestante'][0];
								}
							} else if ($r['prazorestante'][1] != '00h') {
								if (strpos($r['prazorestante'][1], '-') !== false) {
									$r['prazorestante'] = '<i>venc.</i>';
								} else {
									$r['prazorestante'] = $r['prazorestante'][1];
								}
							} else if ($r['prazorestante'][2] != '00m') {
								if (strpos($r['prazorestante'][2], '-') !== false) {
									$r['prazorestante'] = '<i>venc.</i>';
								} else {
									$r['prazorestante'] = $r['prazorestante'][2];
								}
							} else if ($r['prazorestante'][3] != '00s') {
								if (strpos($r['prazorestante'][3], '-') !== false) {
									$r['prazorestante'] = '<i>venc.</i>';
								} else {
									$r['prazorestante'] = $r['prazorestante'][3];
								}
							}
						} ?>

						<div class="col-lg-6 col-sm-8 col-xs-12 atalhoPart" style="font-size: 12px;">
							<div class="col-lg-5 col-xs-12 " style="font-size: 12px;">
								<div class="hrefs"
									style="border-radius:15px;width:100%;text-transform:uppercase;background:<?=$r['corstatus']?>;color:<?=$r['cortextostatus']?>;padding: 2px 6px;font-size:9px;word-break:normal;text-align:center;">
									<i class="fa fa-calendar"
										style="font-size: 12px; line-height: 9px;margin-right:4px;"></i><?=$r['rotulo'];?>
								</div>
								<? /*<div class="col-lg-12 col-sm-12  col-xs-12 dataAlerta" style="font-size: 11px;margin:0;padding:0">
								<? if($r['sla']=='Y'){
									if ($r['posicao'] == 'FIM'){
										//echo 'Concluído';
									}else{
										echo '<!--'.$r['temporeal'].' '.$r['prazorestante'].'-->';
										if (strpos($r['prazorestante'],'-') !== false){
											echo '<div style="font-size: 11px;margin:5px 0 0 38%;padding:0">';
											echo '<i style="background:#ac202e;color:#fff;padding:3px;border-radius:8px;font-size:10px;">vencido</i>';  
											echo '</div>';
										}else{
											//echo $r['datasla'].'h<span style="font-size:9px"> (tempo restante)</span>';
											?>
											<div class="col-lg-12 col-sm-12 col-xs-12" style="margin:0;padding:0">
												<div class="col-lg-2 col-sm-2 col-xs-2">
													<p class="calendarioprazo 1" value="<?=$r['prazo'];?>" name="novoprazo" style="background-image:none;<?if ($r['prazorestante'] == '<i>venc.</i>') {echo 'background:#ac202e;color: #fff;font-size:10px;';} else {echo 'box-shadow: inset 0px 0px 0px 1px #999;font-weight:bold; box-sizing: border-box;font-size:10px; ';}?>  position: absolute; margin-bottom: 0px; padding: 0px;text-align:center;width:100%;font-style: italic;border-radius:8px;">
													<? if ($r['prazorestante'] == '<i>venc.</i>'){echo $r['prazorestante'];}else{echo $r['datasla'];}?></p>
												</div>
												<div class="col-lg-10 col-sm-10 col-xs-10">
													<div class="progress-wrap progress " data-progresspercent="<?if ($r['prazorestante'] == '<i>venc.</i>'){ echo '0';}else{ echo $r['percentual'];}?>" data-height="6px" data-width="100%" data-speed="500" data-color="2dd2bc" style="height: 6px; width: 100%;top:4px;">
														<div class="progress-bar progress" style="left:0;width: <?if ($r['prazorestante'] == '<i>venc.</i>'){ echo '0';}else{ echo $r['percentual'];}?>%;color:#333;padding: 0px 0px;">
														</div>
														<p class="calendarioprazo 2" value="<?=$r['prazo'];?>" name="novoprazo" style="background-image:none;<?if ($r['prazorestante'] == '<i>venc.</i>') {}?>  position: absolute; margin-bottom: 0px; padding: 3px;text-align:center;width:100%;font-style: italic;">
														<?=$r['prazorestante'];?></p>
													</div>
												</div>
											</div>
											<?
										}									
									}									 
								}else{
								?>
									<div class="col-lg-12 col-sm-12 col-xs-12 <?=$r["mostraprazo"];?>"
										style="margin:0;padding:0">
										<div class="col-lg-2 col-sm-2 col-xs-2">
											<p class="calendarioprazo" value="<?=$r['prazo'];?>" name="novoprazo"
												style="background-image:none;<?if ($r['prazorestante'] == '<i>venc.</i>') {echo 'background:#ac202e;color: #fff;font-size:10px;';} else {echo 'box-shadow: inset 0px 0px 0px 1px #999;font-weight:bold;box-sizing: border-box;font-size:10px; ';}?>  position: absolute; margin-bottom: 0px; padding: 0px;text-align:center;width:100%;font-style: italic;border-radius:8px;">
												<?=$r['prazorestante'];?></p>
										</div>
										<div class="col-lg-10 col-sm-10 col-xs-10">
											<div class="progress-wrap progress " data-progresspercent="<?=$r['slaprazo'];?>"
												data-height="6px" data-width="100%" data-speed="500" data-color="2dd2bc"
												style="height: 6px; width: 100%;top:4px;">
												<div class="progress-bar progress"
													style="left:0;width: <?=$r['slaprazo'];?>%;color:#333;padding: 0px 0px;">
												</div>
												<p class="calendarioprazo" value="<?=$r['prazo'];?>" name="novoprazo"
													style="background-image:none;<?if ($r['prazorestante'] == '<i>venc.</i>') {}?>  position: absolute; margin-bottom: 0px; padding: 3px;text-align:center;width:100%;font-style: italic;">
													<?=$r['prazorestante'];?></p>
											</div>
										</div>
									</div>

									<div class="<?=$r["mostradata"];?>"
										style="width:100%;border-radius:4px;<?=$st;?>;text-align:center">

										<div style="width:fit-content;background-image:none;box-shadow: inset 0px 0px 0px 1px <?=$coricone;?>;font-weight:bold;
													box-sizing: border-box;font-size:9px;margin: auto; padding: 1px 8px; border-radius: 8PX;font-style:italic;margin-top:5px;"
											class="calendariotime">
											<i class="fa fa-calendar"
												style="font-size: 14px; line-height: 11px; margin-right: 2px; padding: 2px; "></i>
											<?=$dataTarefa?></div>
									</div>
								<? } ?>

								</div> */?>
							</div>
							<div class="col-lg-3 col-xs-12 origem" style="font-size: 10px; color: #333;">
								<i class="fa fa-user" style="font-size: 12px; line-height: 9px;margin-right:4px;color:#999;"></i><?=$origem?>
							</div>
                            <?
                            if($r['prazo'] >= date('Y-m-d') OR $r["posicao"] == 'FIM'){ $colorprazo = '#666;'; }
                            else {$colorprazo = '#DC143C;'; }
                            ?>
							<div class="col-lg-3 col-xs-12 prazo" style="font-size: 10px; color: #FFFFFF;">
                                <div class="hrefs" style="border-radius:15px;width:100%;text-transform:uppercase;background:<?=$colorprazo?>; 2px 25px 0px 30px; font-size:10px;word-break:normal;text-align:center;">
									<? if($r['sla']=='Y' AND $r['posicao'] != 'FIM'){ ?>
                                        <p class="calendariotime" value="<?=$r['prazo'];?>" name="novoprazo">
                                            <?=dma($dataTarefa);?>
                                        </p>
                                    <? } else { ?>
                                        <p class="calendarioprazo" value="<?=$r['prazo'];?>" name="novoprazo">
                                            <?=dma($dataTarefa);?>
                                        </p>
                                    <? } ?>
                                </div>
							</div>
						</div>
						<div class="col-lg-1 col-xs-12">
							<? if (traduzid("evento", "idevento", "modulo", $r['idevento']) and traduzid('evento', 'idevento', 'modulo', $r['idevento']) != 'evento') {?>
							<div class="linkmodulo">
								<i style="float:right; font-size:22px; margin-right:4px;"
									class="fa fa-paperclip fa-2x pointer fade hoverazul" title="Link Modulo" <?
									$nomemodulo = (traduzid('evento', 'idevento', 'modulo', $r['idevento'])) ?
									$evento->RetornaChaveModuloAlerta(traduzid('evento', 'idevento', 'modulo', $r['idevento'])) : '';
									?>
									onclick="javascript:janelamodal('?_modulo=<?=traduzid('evento', 'idevento', 'modulo', $r['idevento']);?>&_acao=u&<?=$nomemodulo?>=<?=traduzid('evento', 'idevento', 'idmodulo', $r['idevento']);?>')">
								</i>
							</div>
							<?}?>
						</div>
					</div>
				</a>
			<?
			}
			?>
        </div>
        <div class="row">
            <div class="col-lg-5"></div>
            <div class="col-lg-2" style="float:right">
                <span id="exibidos"><?=$countResultados?></span> de
                <span id="totais"><?=$totalResultados?></span> resultados
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

function search() {

    let string = $("#searchInput").val();

    fetch('form/_modulofiltrospesquisa.php?_modulo=vwfiltrareventoalerta&_pagina=1&_fts=' + string +
        '&_filtrosrapidos=\'{"idobjeto":"<?=$_SESSION["SESSAO"]["IDPESSOA"];?>","tipoobjeto":"pessoa"}\'&_registrosentre={}&_=1555094659333'
        ).then(function(response) {
        return response.json();
    }).then(function(data) {

        $(".eventos").empty();

        if (data &&
            data.numrows &&
            parseInt(data.numrows) > 0) {
            $("#exibir").hide();
            let eventos = [];

            for (var index in data.rows) {

                let event = data.rows[index];
                let evento = {};

                Object.keys(data.cols).forEach(function(i, o) {
                    evento[i] = data.rows[index].cols[o];
                });

                evento.idevento = event.parget.idevento;
                eventos.push(evento);
            }

            criaEventos(eventos);

        } else {
            $("#exibir").show();
        }

    }, (error) => {
        alert('Nenhum registro encontrado!');
    });
}

executar = true;
$(window).scroll(function(event) {
    var scroll = $(window).scrollTop();
    if (executar == true) {
        if ($(window).scrollTop() + $(window).innerHeight() >= ($(document).height() - 500)) {
            executar = false;
            //	console.log(executar);
            let offset = $(".eventoRow").length;
            let filter = $("#btn-2").hasClass('selecionado') ? 'todas' : $("#btn-1").hasClass('selecionado') ?
                'minhas' : 'ocultos';

            loadEventos(offset, filter);
            //CB.estilizarCalendarios();
            //configCalendario();
        }
    } else {
        if ($(window).scrollTop() + $(window).innerHeight() <= ($(document).height() - 500)) {
            executar = true;
            //console.log(executar);
        }
    }
});

function loadEventos(offset, filter) {
    vfiltro = filter;
    if (Number.isInteger(parseInt(vfiltro, 10))) {
        var str = '&videvento=' + vfiltro;
        $('#example').removeClass('is-visible');
    } else {
        var str = '';
    }

    var token = Cookies.get('jwt') || localStorage.getItem("jwt") || "";

    if (($("#exibidos").text() != $("#totais").text()) || $("#totais").text() == 0) {
        fetch('ajax/evento.php?vopcao=eventos&voffset=' + offset + '&vfilter=' + filter + '&vordenacao=' + ordenacao + '&vfilterEventoTipo=<?=$_filtro?>' +
            str, {
                headers: {
                    "Content-Type": "application/json",
                    "authorization": token
                }
            }).then(function(response) {
            return response.json();
        }).then(function(data) {
            if(data.error)
            {
                return alertAtencao(data.error);
            }

            if (data && data.length > 0) {
                //alert('criar');
                criaEventos(data);
            } else {
                //$("#exibidos").text("0");
                //$("#totais").text("0");
            }
        });
    }
}

function isDono() {
    if (idPessoa == $('#idpessoa').val()) {
        return true;
    }
    return false;
}

$(document).ready(function() {
    $("#cbContainer").css('display', 'block');
    $("#cbContainer").css('width', '65%');
    $("#cbContainer").css('margin-top', '5%');
});
//# sourceURL=alerta.php

function atualizaComentario(idevento) {

    $("#tblHistorico tbody").html("");
    $.ajax({
        type: "ajax",
        dataType: 'json',
        url: "ajax/eventoresp3.php?vopcao=load&videvento=" + idevento,
        success: function(data) {
            if(data.error)
            {
                return alertAtencao(data.error);
            }

            var peopleHTML = '';
            if (data === null) {} else {
                for (i = 0; i < data.length; i++) {
                    const HOUR = 1000 * 60 * 60;
                    const anHourAgo = moment(data[i]["criadoem"]).add(68, 'minutes');
                    if ('<?=$_SESSION["SESSAO"]["NOMECURTO"];?>' == data[i]["nomecurto"] && moment(Date
                        .now()) < anHourAgo) {
                        var dl =
                            '<i style="display:none" class=\"fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable\" onclick=\"excluiComentario(' +
                            data[i]['ideventocom'] + ')\" title="Excluir!"></i>';
                    } else {
                        var dl = '';
                    }
                    peopleHTML += "<tr ideventocom=" + data[i]['ideventocom'] + ">" +
                        "<td align='left' style='min-width:100px;vertical-align:top;font-size:9px;padding:4px;'>" +
                        data[i]["nomecurto"] + " <Br>" + moment(data[i]["criadoem"], 'YYYY-MM-DD HH:mm')
                        .format('DD/MM/YY HH:mm') + " </td><td>" + data[i]["descricao"] + "</td>" +
                        "<td style='w'> " + dl + "</td>" +
                        "</tr>";
                }
                $("#tblHistorico tbody").html(peopleHTML);
            }
        },
        error: function(objxml) {
            document.body.style.cursor = "default";
            alert('Erro: ' + objxml.status);
        }
    });
}

function atualizaParticipantes(idevento, modulo, idmodulo) {

    $("#localInfo1").html("");
    $.ajax({
        type: "ajax",
        dataType: 'html',
        url: "ajax/eventoresp3.php?vopcao=loadParticipantes&videvento=" + idevento + "&vmodulo=" + modulo +
            "&vidmodulo=" + idmodulo,
        success: function(data) {
            if(data.error)
            {
                return alertAtencao(data.error);
            }

            var peopleHTML = '';
            if (data === null) {} else {
                peopleHTML = data;
                $("#localInfo1").html(peopleHTML);
            }
        },
        error: function(objxml) {
            document.body.style.cursor = "default";
            alert('Erro: ' + objxml.status);
        }
    });
}


function atualizaLido(ideventoresp) {

    $.ajax({
        type: "get",
        url: "ajax/eventoresp3.php?vopcao=lido&videventoresp=" + ideventoresp,
        success: function(data) {
            if(data.error)
            {
                return alertAtencao(data.error);
            }
        },
        error: function(objxml) {
            document.body.style.cursor = "default";
            alert('Erro: ' + objxml.status);
        }
    });
}

function atualizaStatus(status, ideventoresp) {

    $.ajax({
        type: "get",
        url: "ajax/eventoresp3.php?vopcao=change&videventoresp=" + ideventoresp + "&vstatus=" + status,
        success: function(data) {
            if(data.error) return alertAtencao(data.error);

            var peopleHTML = '';
            alertAzul("Salvo", "", 1000);
            CB.loadUrl({
                urldestino: 'form/alerta.php'
            });
        },
        error: function(objxml) {
            document.body.style.cursor = "default";
            alert('Erro: ' + objxml.status);
        }
    });
}

function createButton(context, fluxo, ideventoresp, idevento) {

    if (fluxo.length > 0) {
        $('#divbotoes').show();
        botoes = fluxo.split("|");
        if (botoes.length > 0) {
            for (var i = 0; i < botoes.length; i++) {
                var botoesconfig = botoes[i].split("*");
                if (botoesconfig[2].length > 0) {
                    var li = document.createElement("li");
                    li.setAttribute('style', 'background:' + botoesconfig[1] + ';height:24px;width:100%;color:' +
                        botoesconfig[5]);

                    li.innerHTML = '<a style="color:' + botoesconfig[5] + ';" onclick="AlteraStatusEventoResp(' +
                        botoesconfig[3] + ',' + botoesconfig[2] + ',\'' + botoesconfig[4] +
                        '\')"><i class="fa fa-refresh"></i>&nbsp;&nbsp;' + botoesconfig[0] + '</a>';
                    context.append(li);
                }
            }

        }
    } else {
        $('#divbotoes').hide();
    }
}



$(document).ready(function() {
    $('.atalhoPart').click(function(e) {
        //abrirAtalho($(this).parent(), 'participantes');
		 modalEvento($(this).parent(), '?_modulo=evento&_acao=u&idevento=' + $(this).parent().attr("idevento"));
    });

    $('.atalhoHist').click(function(e) {
        //abrirAtalho($(this).parent(), 'conteudo');
		 modalEvento($(this).parent(), '?_modulo=evento&_acao=u&idevento=' + $(this).parent().attr("idevento"));
    });

    $('.atalhoEvento').click(function(e) {
        modalEvento($(this).parent(), '?_modulo=evento&_acao=u&idevento=' + $(this).parent().attr(
            "idevento"));
        $('#example').removeClass('is-visible');
    });

});

function abrirAtalho(thisObj, aba) {
    $(".eventoRow").css("background-color", "");
    thisObj.css("background-color", "rgba(0,0,0,0.1)");
    if (thisObj.attr("posicao") == 'INICIO') {
        var fluxo = thisObj.attr("fluxo");
        if (fluxo.length > 0) {
            botoes = fluxo.split("|");
            if (botoes.length > 0) {
                for (var i = 0; i < botoes.length; i++) {
                    var botoesconfig = botoes[i].split("*");
                    if (botoesconfig[2].length > 0) {
                        AlteraStatusEventoResp(botoesconfig[3], botoesconfig[2], botoesconfig[4]);
                        thisObj.attr("posicao", "");
                    }
                }
            }
        }
    }
    $('.dd-input').prop("checked", false);
    $('.caixa').val('');
    $('#divbotoes').html("");
    $('#divbotoes').css("display", "block");
    $(".midevento").text(thisObj.attr("idevento"));
    $(".midevento").val(thisObj.attr("idevento"));
    $(".meventotipo").text(thisObj.attr("eventotipo"));
    $(".meventotipo").css({
        "color": thisObj.attr("cor")
    });
    $(".mrotuloresp").text(thisObj.attr("rotuloresp"));
    $(".mcor").css({
        "border-color": thisObj.attr("cor")
    });
    $(".mcorstatus").css({
        "background": thisObj.attr("corstatus")
    });
    $(".mcorstatus").css({
        "color": thisObj.attr("cortextostatus")
    });
    $(".mcorstatusresp").css({
        "background": "#ccc"
    });
    $(".mstatus").text(thisObj.attr("status"));
    $(".mstatusresp").text(thisObj.attr("statusresp"));
    $(".mevento").css('text-transform','uppercase');
    $(".mevento").text(thisObj.attr("evento"));
    $(".mdescricao").html(thisObj.attr("descricao").replace(/<br>/gi,"\n"));
    $(".mcriadoempor").text(thisObj.attr("criadoempor"));
    $(".malteradoempor").text(thisObj.attr("alteradoempor"));
    $(".mlink").attr("onclick", "javascript:modalEvento(this,'?_modulo=evento&_acao=u&idevento=" + thisObj.attr(
        "idevento") + "')");
    $("#tblHistorico tbody").html("");

    createButton($('#divbotoes'), thisObj.attr("fluxo"), thisObj.attr("ideventoresp"), thisObj.attr("idevento"));
    atualizaLido(thisObj.attr("ideventoresp"));
    thisObj.removeClass('naoVisualizado');
    atualizaComentario(thisObj.attr("idevento"));
    atualizaParticipantes(thisObj.attr("idevento"), thisObj.attr("modulo"), thisObj.attr("idmodulo"));
    openTab(event, aba);
}

$(document).ready(function() {
    $('.historico').click(function(e) {
        $("#tblHistorico tbody").html("");
    });
});

var executa_apenas_uma_vez = true;
$(document).keydown(function() {
    if (event.ctrlKey == true && $("#example").hasClass("is-visible") == true && (event.key == 's' || event
            .key == 'S') && !(event.which == 19) && $("[name=_1_i_eventocom_descricao]").val() != '') {
        executa_apenas_uma_vez = false;
        // prevent default event on newer browsers
        if (event.preventDefault) {
            event.preventDefault()
        }
        $.ajax({
            type: "post",
            url: "ajax/eventoresp3.php?vopcao=add&videvento=" + $("[name=_1_i_eventocom_idevento]")
            .val() + "&vobs=" + $("[name=_1_i_eventocom_descricao]").val(),
            success: function(data) {
                if(data.error) return alertAtencao(data.error);

                $("[name=_1_i_eventocom_descricao]").val("");
                alertAzul("Comentário inserido", "", 1000);
                atualizaComentario($("[name=_1_i_eventocom_idevento]").val());
            }
        });
        executa_apenas_uma_vez = true;
        event.stop();
        return false;
    }

});

function AlteraStatusEventoResp(inideventoresp, inideventostatus, inocultar) {

    $.ajax({
        type: "get",
        url: "ajax/eventostatus.php?inideventoresp=" + inideventoresp + "&inideventostatus=" +
            inideventostatus + "&inocultar=" + inocultar,
        success: function(data) {
            if (data == 'ERROASSTODOS') {
                alert(
                'Conforme cadastro do evento e necessária assinatura de todas as pessoas envolvidas.');
                return;
            }
            if (data == 'ERROASSPARCIAL') {
                alert('Conforme cadastro do evento e necessário assinar documento em anexo');
                return;
            }
            if (data == 'ASSPENDENTE') {
                alert('Assinatura do anexo pendente');
                return;
            }
            var obj = JSON.parse(data);
            var novofluxo = obj.fluxo;
            var sep = "";
            var fluxo = "";
            for (var k in novofluxo) {
                fluxo += sep + novofluxo[k].botao + "*" + novofluxo[k].cor + "*" + novofluxo[k]
                    .ideventostatus + "*" + novofluxo[k].ideventoresp + "*" + novofluxo[k].ocultar + "*" +
                    novofluxo[k].cortexto;
                sep = "|";
            }

            now = new Date;
            var colorprazo;
            var classe;
            if(obj.prazo >= now || obj.posicao == 'FIM'){
                colorprazo = '#666;';
            } else {
                colorprazo = '#DC143C;';
            }
            if(obj.sla == 'Y' && obj.posicao != 'FIM'){
                classe = 'calendarioprazo';
            } else {
                classe = 'calendariotime';
            }
console.log(colorprazo);
console.log(classe);
console.log(obj.sla);
console.log(obj.posicao);
console.log(obj.prazo);
            $('#idevento_' + obj.status.idevento).attr("corstatus", obj.status.corstatus);
            $('#idevento_' + obj.status.idevento).attr("cortextostatus", obj.status.cortextostatus);
            $('#idevento_' + obj.status.idevento).attr("corstatusresp", obj.statusresp.corstatus);
            $('#idevento_' + obj.status.idevento).attr("rotuloresp", obj.statusresp.rotulo);
            $('#idevento_' + obj.status.idevento).attr("status", obj.status.rotulo);
            $('#idevento_' + obj.status.idevento).attr("fluxo", fluxo);
            //$('#idevento_' + obj.status.idevento).find(".hrefs").html(
            //    '<i class="fa fa-calendar" style="font-size: 12px; line-height: 9px;margin-right:4px;"></i>' +
            //    obj.status.rotulo);
            //$('#idevento_' + obj.status.idevento).find(".hrefs").css({
            //    "background": obj.status.corstatus
            //});
            //$('#idevento_' + obj.status.idevento).find(".hrefs").css({
            //    "color": obj.status.cortextostatus
            //});
            //$('#idevento_' + obj.status.idevento + ' .atalhoPart').click();
            //if (obj.statusresp.oculto == '1') {
            //    $('#idevento_' + obj.status.idevento).hide();
            //}
        }
    });
}

function excluiComentario(ideventocom) {
    $.ajax({
        type: "get",
        url: "ajax/eventoresp3.php?vopcao=excluicomentario&videventocom=" + ideventocom,
        success: function(data) {
            if(data.error) return alertAtencao(data.error);

            alertAtencao('Comentário Removido');
            $('tr[ideventocom^="' + ideventocom + '"]').hide()
            //	$( this ).closest("tr").hide();
            //atualizaComentario($('.midevento').val());
        },
        error: function(objxml) {
            document.body.style.cursor = "default";
            alert('Erro: ' + objxml.status);
        }

    });
}

//Função para filtrar o evento. Caso tenha a sessão setada irá aparecer no filtro e continuará io filtro nos parâmetros da listagem do Evento
// 17-02-2020 - Lidiane
function carregaFiltroTipoEvento()
{
	var ideventotipovalue = $("[name=ideventotipo]").val();
	console.log('ideventotipovalue');
	console.log(ideventotipovalue);
	if(ideventotipovalue != undefined){
		//Seta as preferencias do USUARIO
		CB.setPrefUsuario('u', 'evento.alertafiltrotipoevento', ideventotipovalue);
		CB.loadUrl({urldestino: 'form/alerta.php'});		
	} 
	
	if(ideventotipovalue === null){
		CB.setPrefUsuario('u', 'evento.alertafiltrotipoevento', null);
		CB.loadUrl({urldestino: 'form/alerta.php'});	
	}
	
	if($("#ideventotipo").hasClass('visivel')){
		$("#carregaFiltroEvento").hide();
		$("#ideventotipo").removeClass('visivel');		
	}else{
		$.ajax({
			type: "post",
			url: "ajax/alerta.php?vopcao=menu&vfiltro="+ideventotipovalue,
			success: function(data) 
			{	
				var menu = '<a style="line-height: 12px; font-size: 12px; float: right; color: gray; padding-right: 8px;" href="javascript:limpaFiltroEvento();">Limpar filtro<i class="fa fa-close"></i></a><br />';
				menu += '<select name="ideventotipo"  id="ideventotipo"  class="selectpicker valoresselect visivel" multiple="multiple" data-live-search="true">';
				console.log(data);
				var obj = JSON.parse(data);
				
				for(i in obj) {	
					var item = obj[i];	
					var filtro = '<?=$_filtro?>';
					console.log(filtro);
					if(filtro)
					{			
						ideventotipovalueSelected = filtro.indexOf(item.ideventotipo);
						var selecionado;
						if(ideventotipovalueSelected != -1)
						{
							selecionado = 'selected="selected"';
						} else {
							selecionado = '';
						}
					}		
					if(selecionado == undefined){
						selecionado = '';
					}
					menu += '<option data-tokens="'+item.eventotipo+'" value="'+item.ideventotipo+'" '+selecionado+'>'+item.eventotipo+'</option>';
				}
				menu += '</select>';
				menu += '<button id="cbNovo" type="button" class="btn btn-primary btn-xs" onclick="carregaFiltroTipoEvento()" style="float: right; margin-top: 5px;">Ok</button>';					
				$('.popover-content').html(menu);		
				$('.popover').css('display','block');
				$('.popover').css('top','25px');
				$('.popover').css('left','50px');
				$('.popover').css('max-width','300px');
				$('.popover').css('width','290px');
				$('.selectpicker').selectpicker('render');
				CB.post();
			}
		});
		$("#carregaFiltroEvento").show();
		
	}	
}

function limpaFiltroEvento(){
	CB.setPrefUsuario('u', 'evento.alertafiltrotipoevento', null);
	CB.loadUrl({urldestino: 'form/alerta.php'});	
}

$(document).ready(function () {
    $(".popover > a").click(function (e) {
        $(this).closest('.popupBottom').css('display','block');
        e.stopPropagation();
    });
    $(document).on('click', function (e) {    
        if (!$(e.target).closest('.hover').length) $('.popover').css('display','none');
    });
});

//Função para filtrar o evento. Caso tenha a sessão setada irá aparecer no filtro e continuará io filtro nos parâmetros da listagem do Evento
// 17-02-2020 - Lidiane

//configCalendario();
</script>
<style>
fieldset.scheduler-border {
    border: 1px solid #eee !important;
    padding: 8px;
    margin: 0 0 1.5em 0 !important;
    -webkit-box-shadow: 0px 0px 0px 0px #000;
    box-shadow: 0px 0px 0px 0px #000;
}

legend.scheduler-border {
    font-size: 11px !important;
    font-weight: bold !important;
    text-align: left !important;
    text-transform: uppercase;
}

legend {
    border-bottom: none;
    margin-bottom: 0px !important;

}

#mceu_4 {
    top: 24px !important;
}

.multiselects {
    width: 100% !important;
}

.messageboxok {
    width: 150px;
    border: 1px solid #349534;
    background: #C9FFCA;
    padding: 3px;
    font-weight: bold;
    color: #008000;
}

.messageboxerror {
    width: 150px;
    border: 1px solid #CC0000;
    background: #F7CBCA;
    padding: 3px;
    font-weight: bold;
    color: #CC0000;
}

.table-hover div {
    padding: 2px 0px !important;
}

#localInfo1 {
    background: #fff;
}
.mrotuloresp{
	-webkit-touch-callout: none; /* iOS Safari */
    -webkit-user-select: none; /* Safari */
     -khtml-user-select: none; /* Konqueror HTML */
       -moz-user-select: none; /* Old versions of Firefox */
        -ms-user-select: none; /* Internet Explorer/Edge */
            user-select: none; /* Non-prefixed version, currently
                                  supported by Chrome, Opera and Firefox */
}

.popover .webui-arrow{
	position: absolute;
    display: block;
    width: 0;
    height: 0;
    border-color: transparent;
    border-style: solid;
}
</style>