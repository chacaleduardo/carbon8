<?
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/permissao.php");

// CONTROLLERS
require_once(__DIR__."/controllers/evento_controller.php");
require_once(__DIR__."/controllers/eventotipo_controller.php");
require_once(__DIR__."/controllers/pessoa_controller.php");
require_once(__DIR__."/controllers/sgarea_controller.php");
require_once(__DIR__."/controllers/sgdocumento_controller.php");
require_once(__DIR__."/controllers/sgdepartamento_controller.php");
require_once(__DIR__."/controllers/sgsetor_controller.php");
require_once(__DIR__."/controllers/tag_controller.php");
require_once(__DIR__."/controllers/prodserv_controller.php");
require_once(__DIR__."/controllers/imgrupo_controller.php");

$_acao = $_GET['_acao'];
$idevento = $_GET['idevento'];

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

/*
* $pagvaltabela: tablea principal a ser atualizada pelo formulario html
* $pagvalcampos: Informar os Parâmetros GET que devem ser validados para compor o select principal
*                pk: indica Parâmetro chave para o select inicial
*                vnulo: indica Parâmetros secundários que devem somente ser validados se nulo ou não
*/
$pagvaltabela = "evento";
$pagvalcampos = array(
	"idevento" => "pk"
);

/*
* $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
*/
$pagsql = EventoController::buscarVariaveisDoEvento();

$_ignoraEmpresaControleVariavelGetPost = true;
/*
* controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET
	e preenchimento das variáveis que vieram por POST
*/
include_once("../inc/php/controlevariaveisgetpost.php");

if(empty($_1_u_evento_idpessoa)){
	$_1_u_evento_idpessoa=$_SESSION["SESSAO"]["IDPESSOA"];
}

$idEventoTipo = filter_input(INPUT_GET, 'eventotipo');
$calendario = filter_input(INPUT_GET, 'calendario');
$idmodulo   = filter_input(INPUT_GET, 'idmodulo');
$modulo     = filter_input(INPUT_GET, 'modulo');
$inicio     = filter_input(INPUT_GET, 'inicio');
$fim        = filter_input(INPUT_GET, 'fim');

$subevento  = false;
$dataclick  = str_replace("/", "-", filter_input(INPUT_GET, 'dataclick'));
$horainicio = EventoController::arredondarMinutoParaCima(new DateTime());
$horafim    = EventoController::arredondarMinutoParaCima(new DateTime());
$horafim->modify('+1 hour');
$newdate    = date_create()->format('d/m/Y');

$nomemodulo = '';
$linksVinculados = [];
$pessoas = [];
$tags = [];
$sgDoc = [];
$areas = [];
$departamentos = [];
$setores = [];

if($_1_u_evento_modulo)
{
	$nomemodulo = EventoController::retornaChaveModuloEvento($_1_u_evento_modulo);
} else
{
	if($modulo)
	{
		$nomemodulo = EventoController::retornaChaveModuloEvento($modulo);
	}
}

// Rounded from 37 minutes to 40
//  2018-06-27 20:40:00
if (empty($_1_u_evento_inicio) and empty($_1_u_evento_ideventotipo)) {
	$_1_u_evento_ideventotipo   = (empty($idEventoTipo)) ? '' : $idEventoTipo;
	$calendario                 = (empty($calendario)) ? '' : $calendario;
	$_1_u_evento_repetirate     = '';
	$_1_u_evento_fimsemana      = 'N';
	$_1_u_evento_periodicidade  = '';
	$_1_u_evento_idsgdoc        = '';
}

$idevento	= (empty($_1_u_evento_idevento)) ? 'undefined' : $_1_u_evento_idevento;
$evento		= traduzid("eventotipo", "ideventotipo", "eventotipo", $_1_u_evento_ideventotipo);

$eventoTipo = [];

if($_1_u_evento_ideventotipo)
{
	$eventoTipo = EventoTipoController::buscarPorChavePrimaria($_1_u_evento_ideventotipo);
	$anonimo = $eventoTipo['anonimo'];
	$fluxounico = $eventoTipo['fluxounico'];

	$jDocvinc = [];

	if($eventoTipo['vinculadoc']=='Y')
		$jDocvinc = EventoController::buscarDocsDisponiveisParaVinculo($_1_u_evento_idevento, cb::idempresa(), true);
}//if($_1_u_evento_ideventotipo){

if(!empty($_1_u_evento_idevento)) 
{
	$tokeninicial = EventoController::buscarTokenInicialDoEvento($_1_u_evento_idevento);
	EventoController::atualizarStatusParaLidoPorIdEventoEIdPessoa($_1_u_evento_idevento, $_SESSION["SESSAO"]["IDPESSOA"]);
	$grupoDeFuncionariosDisponiveisParaVinculo = PessoaController::buscarGruposDePessoasDisponiveisParaVinculoNoEvento($_1_u_evento_idevento, "evento", $_SESSION["SESSAO"]["IDPESSOA"], share::pessoaseventoPorSessionIdempresa('a.idpessoa'), share::gruposeventoPorSessionIdempresa('g.idimgrupo'));
	if($eventoTipo['link']=='Y')
		$linksVinculados = EventoController::buscarLinksVinculados($_1_u_evento_idevento);

}
if(empty($grupoDeFuncionariosDisponiveisParaVinculo)){
	$grupoDeFuncionariosDisponiveisParaVinculo = array();
}
// PENDENTE
$Jtime = $JSON->encode($arrTime);
// FIM PENDENTE

$arrDuracaoTempo = EventoController::buscarDuracao();

$motivos = null;

if (!empty($_1_u_evento_idevento))
{
	$motivos = EventoController::buscarMotivos(true);
	$arrayOcultar = [];

	if(!empty($_1_u_evento_fluxoocultar))
	{
		$arrayOcultar = explode(",", $_1_u_evento_fluxoocultar);
	}
}

$dono='N';

if($_SESSION["SESSAO"]["USUARIO"]==$_1_u_evento_criadopor)
{
	$dono='Y';
}

/**
* Valido se o usuário Logado tem acesso ao formulário Evento.
* 16/01/2020 - Lidiane
*/
$nmodid = '';
if ($_1_u_evento_modulo == 'eventoobj')
{
	$statusEvento = EventoController::buscarStatusDoUsuarioNoEventoPorIdPessoaEIdModulo($_SESSION["SESSAO"]["IDPESSOA"], $_1_u_evento_idmodulo);

	foreach($statusEvento as $status)
	{
		$nmod = 'evento';
		$nmodid = $status['idevento'];
	}
}

$permissoesDoEvento = EventoController::buscarPermissoesPorIdEvento($_1_u_evento_idevento);

//Valida se a pessoa está no evento pai, embora não esteja no filho, aparecerá nele.
//sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=321527 (Lidiane - 21-05-2020)
$permissoesDoEventoPai = EventoController::buscarPermissoesPorIdEvento($_1_u_evento_ideventopai);

// buscar se o evento esta esta aberto para o usuario no fluxo
$permissoesDoEventoAberto = EventoController::buscarPermissoesAbertoPorIdEvento($_1_u_evento_idevento);

//Validação para o Evento em anexo, que pode ser visto apenas dentro do Iframe.
//sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=321527 (Lidiane - 21-05-2020)
$visualizarpaiprincipal = $_GET['anexo'];
if(		($visualizarpaiprincipal == 'Y' && $privado == 'N') 
	|| 	(count($permissoesDoEvento) > 0 && $_1_u_evento_privado == 'N' && count($permissoesDoEventoPai) >= 0) || 
		(count($permissoesDoEvento) > 0 && $_1_u_evento_privado == 'Y') ||
		(count($permissoesDoEventoAberto) > 0 && $_1_u_evento_privado == 'N' )
){
	$visualizarevento = 'Y';
} else {
	$visualizarevento = 'N';
}

$possuiAcesso = !(empty($nmodid) && $_1_u_evento_idevento <> "" && $visualizarevento == 'N' &&  !(array_key_exists("eventomaster", getModsUsr("MODULOS"))));

if(!$possuiAcesso)
{
	echo "<br /> Você não possui acesso a este evento.";
	die;
}

$nmodid = '';

if ($_1_u_evento_prazo != "0000-00-00 00:00:00") 
{
	if ($_1_u_evento_configprazo == "N") 
	{
		$dataTarefa = substr(dmahms($_1_u_evento_inicio . ' ' . $_1_u_evento_iniciohms), 0, -3) . '<br>' . substr(dmahms($_1_u_evento_fim . ' ' . $_1_u_evento_fimhms), 0, -3);

		$current = strtotime(date("Y-m-d"));
		$date = strtotime($_1_u_evento_inicio);

		$datediff = $date - $current;
		$difference = floor($datediff / (60 * 60 * 24));
		if ($difference == 0) {
			$dataTarefa = 'HOJE ' . substr($_1_u_evento_iniciohms, 0, -3);
			$coricone = '#0f8041;background:#0f8041;color:#fff;';
		} else if ($difference > 1) {
			$dataTarefa = substr(dmahms($_1_u_evento_inicio . ' ' . $_1_u_evento_iniciohms), 0, -3);
			$coricone = '#999;color:#999;';
		} else if ($difference > 0) {
			$dataTarefa = 'AMANHÃ ' . substr($_1_u_evento_iniciohms, 0, -3);
			$coricone = '#999;color:#999;';
		} else if ($difference < -1) {
			$dataTarefa = substr(dmahms($_1_u_evento_inicio . ' ' . $_1_u_evento_iniciohms), 0, -3);
			$coricone = '#999;background:#999;color:#666;';
		} else {
			$dataTarefa = 'ONTEM ' . substr($_1_u_evento_iniciohms, 0, -3);
			$coricone = '#999;background:#999;color:#666;';
		}
	} else {
		$dataTarefa = dma($_1_u_evento_prazo);
	}
}

if ($_1_u_evento_prazorestante == '0d 00h 00m 00s ') {
	$_1_u_evento_prazorestante = '<i>venc.</i>';
} else {
	$_1_u_evento_prazorestante = explode(" ", $_1_u_evento_prazorestante);
	if ($_1_u_evento_prazorestante[0] != '0d') {
		if (strpos($_1_u_evento_prazorestante[0], '-') !== false) {
			$_1_u_evento_prazorestante = '<i>venc.</i>';
		} else {
			$_1_u_evento_prazorestante = $_1_u_evento_prazorestante[0];
		}
	} else if ($_1_u_evento_prazorestante[1] != '00h') {
		if (strpos($_1_u_evento_prazorestante[1], '-') !== false) {
			$_1_u_evento_prazorestante = '<i>venc.</i>';
		} else {
			$_1_u_evento_prazorestante = $_1_u_evento_prazorestante[1];
		}
	} else if ($_1_u_evento_prazorestante[2] != '00m') {
		if (strpos($_1_u_evento_prazorestante[2], '-') !== false) {
			$_1_u_evento_prazorestante = '<i>venc.</i>';
		} else {
			$_1_u_evento_prazorestante = $_1_u_evento_prazorestante[2];
		}
	} else if ($_1_u_evento_prazorestante[3] != '00s') {
		if (strpos($_1_u_evento_prazorestante[3], '-') !== false) {
			$_1_u_evento_prazorestante = '<i>venc.</i>';
		} else {
			$_1_u_evento_prazorestante = $_1_u_evento_prazorestante[3];
		}
	}
}
//Fim Barra de Status do Prazo

$camposObrigatorios = [];

if(property_exists(json_decode($eventoTipo['jsonconfig']), 'campos_obrigatorios'))
{
	foreach(json_decode($eventoTipo['jsonconfig'])->campos_obrigatorios as $key => $item)
	{
		if($item)
		{
			array_push($camposObrigatorios, $key);
		}
	}
}

$camposVisiveis =  EventoController::buscarCamposVisiveisPorIdEventoTipo($_1_u_evento_ideventotipo);
$temCampoInicio = (($eventoTipo && $eventoTipo['prazo'] == 'N') && (count(array_filter($camposVisiveis, function($element){return $element['col'] == 'inicio';})) !== 0));

$jtagp = []; 
$jPessoap = [];
$jSgdocp = [];
$areas = [];
$departamentos = [];
$setores = [];
?>
	
	<link href="./inc/css/alerta.css?_<?=date("dmYhms")?>" rel="stylesheet">
	<link href="/form/css/evento_css.css?_<?=date("dmYhms")?>" rel="stylesheet">

	<style>
		.miniBarProgress {
			height: 100%;
			position: absolute;
			top: 0rem;
			left: 0rem;
		}
		.miniBar {
			height: 0.5rem;
			border: 1px solid #8a898a;
			position: relative;
			width: -webkit-calc(100% - 2rem);
			width: -moz-calc(100% - 2rem);
			width: calc(100% - 2rem);
			margin-right: 0.5rem;
			display: table;
			text-align: center!important;
			color: #fff!important;
		}

	</style>

	<? 
	//Validação para não aparecer as tabelas enquanto for insert.
	if($_acao == 'i'){ ?>
		<style>
			div.row {     
			  display:none;
			}
		</style>
	<? } ?>

<script src=".\inc\js\jquery\vanilla-masker.js"></script>

	<div class='row d-flex flex-wrap w-100'>
		<div class="col-xs-12 col-md-6">
			<div class="panel panel-default" >
				<div class="panel-heading">                    
					<div class="row d-flex align-items-center">
						<!-- Sigla -->
						<div class="col-xs-1 sigla-empresa"></div>
						<!-- Id -->
						<div id="idEventoTitulo" class="col-xs-2 nowrap">
							<?if($_1_u_evento_idevento){?>
								<span>
									<strong>
										ID:<label class="alert-warning" ondblclick="copiaLink()"><?=$_1_u_evento_idevento?></label>
									</strong>                              
								</span>
							<?}?>
						</div>
						<!-- Evento -->
						<div class="col-xs-4">
							<span id="tipoEventoSpan" class="text-uppercase"><?=$evento?></span>
						</div>
						<!-- Rotulo -->
						<div class="col-xs-5 d-flex align-items-center justify-end">
							<?if(!empty($_1_u_evento_idevento))
							{?>
								<span>                               
									<label class="alert-warning" id="statusButton" ondblclick="copiaGit()">
										<span style="text-transform:uppercase;font-size: 11px;"><?=$_1_u_evento_rotulo?></span>
									</label>
								</span>
								<a title="Imprimir Evento." class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/relevento.php?_acao=u&idevento=<?=$_1_u_evento_idevento?>')"></a>
							<?}?>
						</div>
					</div>
				</div>       
				<div class="panel-body"> 						
					<div class="row d-flex flex-wrap">
						<? //Atendimento Telefonico - Lidiane (08-04-2020) ?>						
						<? if($eventoTipo['atendimentotelefonico']=='Y')
						{ ?>
							<div class="w-100 form-group">
								<label>Forma Atendimento</label>
								<select class="form-control" name="_1_u_evento_formaatendimento">
									<option></option>
									<? fillselect(EventoController::$formasAtendmento, $_1_u_evento_formaatendimento); ?>
								</select>
							</div>
						<? } 

						if($eventoTipo['prazoprevisto'] == 'Y') {
							?>
							<div class="row w-100 d-flex flex-wrap align-items-center" id="rowRepetition">
								<div class="col-xs-4 form-group px-2">
									<label class="w-100">Prazo Solicitante</label>
									<? $bloqueioSolicitante = ($_SESSION["SESSAO"]["IDPESSOA"] == $_1_u_evento_idpessoa) ? '' : 'disabled'; ?>
									<input class="calendario form-control form-control prazosolicitante" <?=$bloqueioSolicitante?> name="_1_u_evento_prazosolicitante" type="text" value="<?=substr($_1_u_evento_prazosolicitante, 0, 10)?>">
								</div>
								<div class="col-xs-4 form-group px-2">
									<label class="w-100">Prazo Responsável</label>
									<?
									$arrayPermissaoPessoa = [EventoController::buscarPessoaDepartamento($_1_u_evento_idpessoaev), $_1_u_evento_idpessoaev]; 
									$bloqueioResponsavel = (in_array($_SESSION["SESSAO"]["IDPESSOA"], $arrayPermissaoPessoa)) ? '' : 'disabled'; 
									?>
									<input class="calendario form-control form-control prazoresponsavel" <?=$bloqueioResponsavel?> name="_1_u_evento_prazoresponsavel" type="text" value="<?=substr($_1_u_evento_prazoresponsavel, 0, 10)?>">
								</div>
								<div class="col-xs-4 form-group px-2">
									<label class="w-100">Prazo Acordado</label>
									<input class="calendario form-control form-control prazoacordado" disabled type="text" value="<?=substr($_1_u_evento_prazoacordado, 0, 10)?>">
									<input name="_1_u_evento_prazoacordado" type="hidden" value="<?=dma($_1_u_evento_prazoacordado)?>">
								</div>
							</div>
							<?
						}

						//HTML Livre
						if(!empty($eventoTipo['html']))
						{
						    echo "<div class='w-100'>".$eventoTipo['html']."</div>";
						} ?>
						<input name="_1_<?= $_acao ?>_evento_idpessoa" id="idpessoa" type="hidden" value="<?= $_1_u_evento_idpessoa ?>" readonly='readonly'>
						<input name="fluxounico" id="fluxounico" type="hidden" value="<?= $fluxounico ?>" readonly='readonly'>
						<input name="_1_<?=$_acao?>_evento_idevento" type="hidden" value="<?=$_1_u_evento_idevento?>">
						<?
						if(!empty($_1_u_evento_ideventotipo))
						{
							listaCampos($camposVisiveis);

						}// if(!empty($_1_u_evento_ideventotipo)){
						if(empty($_1_u_evento_ideventotipo)){
							?>
							<tr>
								<td  align="right">Tipo Evento:</td>

								<td >
									<select style="min-width: 500px;" class="form-control"
										value="<?= $_1_u_evento_ideventotipo ?>" 
										name="_1_<?= $_acao ?>_evento_ideventotipo" 
										vnulo <?= ($_acao=='u') ? 'readonly="readonly"' : '';?>>

										<option selected="selected" disabled="disabled" value="">Selecione o tipo do evento</option>
										<? fillselect(EventoTipoController::buscarTiposPorIdEmpresa($_SESSION["SESSAO"]["IDEMPRESA"], true), $_1_u_evento_ideventotipo); ?>
									</select>
								</td>
							</tr>
							<?
						} else {?>
							<tr>
								<td> <input name="_1_<?= $_acao ?>_evento_ideventotipo" id="idpessoa" type="hidden" value="<?= $_1_u_evento_ideventotipo ?>" class="form-control" readonly='readonly'></td>
							</tr>
							<?
						}
						if($eventoTipo['evcor']=='Y'){
							?>
							<div class="form-group w-100">
								<label>Cor</label>
								<?if(!empty($_1_u_evento_ideventotipo)){
									if(empty($_1_u_evento_cor)){
										$_1_u_evento_cor=$eventoTipo['cor'];
									}
								}
								?>
								<input class="form-control" name="_1_<?= $_acao ?>_evento_cor" onchange="atualizaCor()" id="color" type="color" aria-label="..." value="<?=$_1_u_evento_cor?>">
							</div>
							<?
						}
						if($eventoTipo['vinculadoc']=='Y'){
							?>
							<div class="form-group w-100">
								<label>Vincular documentos</label>
								<?if(!empty($_1_u_evento_ideventotipo)){
									if(empty($_1_u_evento_cor)){
										$_1_u_evento_cor=$eventoTipo['cor'];
									}
								}
								?>
								<input id="docvinc" class="compacto" type="text" cbvalue placeholder="Selecione">
								<?= listarDocumentosVinculados($_1_u_evento_idevento) ?>
							</div>
							<?
						}

						if($eventoTipo['rnc']=='Y' and !empty($_1_u_evento_idevento)){
							?>                                 
							<div class="form-group w-100 d-flex flex-wrap">
								<label class="w-100">RNC</label>
								<?
								//Alteração realizada, pois havia dois campos com idsgdoc - Lidiane (28-04-2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=314921
								if(empty($_1_u_evento_idsgdocrnc)){
									?>
									<div class="col-xs-10 float-none pl-0">
										<input type="text" id="motivornc" value="" vnulo="" class="ui-autocomplete-input form-control" autocomplete="off">
									</div>
									<i id="criarrnc" class="fa fa-plus-circle verde btn-lg pointer" onclick="fnovornc(<?=$_1_u_evento_idevento?>);" title="Criar novo RNC"></i>
									<?
								}else{
									?>
									<a title="Documento RNC" href="javascript:janelamodal('?_modulo=documento&_acao=u&idsgdoc=<?=$_1_u_evento_idsgdocrnc?>')">
											<?=traduzid("sgdoc","idsgdoc","titulo",$_1_u_evento_idsgdocrnc);?>
									</a>
									<a title="Documento RNC" href="javascript:janelamodal('?_modulo=documento&_acao=u&idsgdoc=<?=$_1_u_evento_idsgdocrnc?>')">
										<?= $_1_u_evento_idsgdocrnc?>
									</a>
									<?
								}//if($_1_u_evento_idsgdoc){
								?>                                                                          
							</div>
						<?
						}//if( !empty($row['rnc'])){
						
						if(!empty($_1_u_evento_ideventotipo))
						{
							$eventosSla = EventoTipoController::buscarEventoSlaPorIdEventoTipo($_1_u_evento_ideventotipo);
							if(count($eventosSla))
							{	
								?>
								<tr>                    
									<td align="right">Serviço:</td>
									<td>
										<select class="form-control" name="_1_<?= $_acao ?>_evento_servico">
											<option></option>
											<? fillselect(EventoTipoController::buscarEventoSlaPorIdEventoTipo($_1_u_evento_ideventotipo, '', true, 'servico'), $_1_u_evento_servico); ?>
										</select>                    
									</td>
								</tr>
								<tr>
									<td align="right">Prioridade:</td>
									<td>
										<select class="form-control" name="_1_<?= $_acao ?>_evento_prioridade">
											<option></option>
											<? fillselect(EventoTipoController::buscarEventoSlaPorIdEventoTipo($_1_u_evento_ideventotipo, '', true, 'prioridade'), $_1_u_evento_prioridade); ?>
										</select>                   
									</td>
								</tr>
				
								<?
							}//if($qtdsla>0){	
						}//if(!empty($_1_u_evento_ideventotipo)){
						?>
						<div id="divmodulo" class="row w-100 form-group px-3">
							<label class="w-100">Anexo</label>
							<? 
							if ($_1_u_evento_modulo == 'eventoobj')
							{
								$eventoFilho = EventoController::buscarEventoFilhoPorIdModulo($_1_u_evento_idmodulo);
								$nmod = 'evento';
								$eventosFilho = $eventoFilho['idevento'];
							}
								
							?>
							<input placeholder="Link do Módulo" name="_1_<?= $_acao ?>_evento_modulo" id="inputmodulo" type="text" value="<?=traduzid("evento","idevento","modulo",$_1_u_evento_idevento); ?>" class="form-control">
							<?
							$search = 'report';
							if (preg_match("/\b{$search}\b/i", traduzid("evento","idevento","modulo",$_1_u_evento_idevento))) { ?>
								<input placeholder="Link do Id do Módulo" name="_1_<?= $_acao ?>_evento_idmodulo" id="inputidmodulo" type="text" value="0" class="form-control">
								<a id="modulo" title="Módulo" onclick="window.open(this.href); return false;" href="<?=traduzid("evento","idevento","modulo",$_1_u_evento_idevento); ?>" target="_blank">
									<?=traduzid("evento","idevento","modulo",$_1_u_evento_idevento);?>
								</a>
							<? } else { ?>
								<input placeholder="Link do Id do Módulo" name="_1_<?= $_acao ?>_evento_idmodulo" id="inputidmodulo" type="text" value="<?= traduzid("evento","idevento","idmodulo",$_1_u_evento_idevento); ?>" class="form-control">								
								<a id="modulo" title="Módulo" href="javascript:janelamodal('?_modulo=<?=traduzid("evento","idevento","modulo",$_1_u_evento_idevento);?>&_acao=u&<?=$nomemodulo?>=<?=traduzid("evento","idevento","idmodulo",$_1_u_evento_idevento);?>')">
									<?=traduzid("evento","idevento","modulo",$_1_u_evento_idevento);?>
								</a>
								<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable " style="margin-left: 15px; margin-top:-3px"  onclick="exmodulo(<?=$_1_u_evento_idevento?>)" alt="Excluir"></i>
							<? } ?>
						</div>
						<? //Anexar o Link dos Módulos - Lidiane (21-02-2020) ?>
						<? if($eventoTipo['anexolink']=='Y') { ?>
							<div class="col-xs-10 form-group px-2" id="divanexolink">
								<label>Anexo</label>
								<input placeholder="Cole aqui o Link da Tela do Sislaudo caso necessário" name="link" id="inputmodulo" type="text" value="" class="form-control" <?= in_array('anexarlink', $camposObrigatorios) ? 'vnulo' : '' ?>>
							</div>
						<? } 
						
						if($eventoTipo['link'] == 'Y') { ?>
							<div class="col-xs-10 p-0">
								<div class="panel panel-default">
									<div class="panel-heading" data-toggle="collapse" href="#eventoslink">
										Links vinculados
									</div>
									<div class="panel-body" id="eventoslink">
										<div class="d-flex align-items-center w-100">
											<div class="col-xs-4 form-group">
												<label>Título</label>
												<input placeholder="Título do link" id="input_titulo_link" type="text" value="" class="form-control" >
											</div>
											<div class="col-xs-9 form-group px-2" id="divlink">
												<label>Link</label>
												<input placeholder="Inserir link" id="inputlinkmultiplo" type="text" value="" class="form-control" >
											</div>
											<div class="col-xs-1">
												<i class="fa fa-plus btn btn-primary pointer" onclick="adicionarLink()"></i>
											</div>
										</div>
										<table class="w-100 table table-stripped">
											<tbody>
												<? foreach($linksVinculados as $link) { ?>
													<tr>
														<td>
															<h5><?= $link['titulo'] ?></h5>
															<a href="<?= $link['link'] ?>" target="_blank"><?= $link['link'] ?></a>
														</td>
														<td class="text-end">
															<i class="fa fa-trash hoververmelho pointer" onclick="removerLink(<?= $link['ideventolink'] ?>)"></i>
														</td>
													</tr>
												<? } ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						<? } ?>
					</div>	 
					<?
					// if($eventoTipo['prazo']=='N'){
									 
					if(empty($_1_u_evento_repetirate) || empty($_1_u_evento_periodicidade) || empty($_1_u_evento_fimsemana)){
						$divrepetir='none';
					}else{
						$divrepetir='flex';
					}
					if($_1_u_evento_repetirevento == 'Y'){
						$checkrep="checked='checked'";
						$repetirCheck = 'true';
					}else{
						$checkrep="";
						$repetirCheck = 'false';
					}
					
					if($eventoTipo['repetirevento'] == 'Y') { ?>
						<hr>
						<div class="row w-100 d-flex flex-wrap align-items-center" id="rowRepetition">
							<div class="col-md-2 pointer nowrap">
								<div id="repetirlink">
									<label for="repetircheckbox">Repetir Evento</label>
								</div>
							</div>
							<div class="col-md-2" style="padding-left: 4%;">
								<input id="repetircheckbox" name="repetircheckbox" type="checkbox" <?=$checkrep?> >
							</div>

							<div class="col-md-2"></div>
							<div class="col-md-2 pointer">
								<!--  <a id="diainteirolink" name="" type="text" value="">Dia Inteiro</a> -->
							</div>
							<div class="col-md-2">
								<!--  <input id="diainteirocheckbox" type="checkbox"> -->
							</div>
						</div>
					<? } ?>
					<div id="divrepetir" class="col-xs-12 flex-wrap align-items-center" style="display: <?=$divrepetir?>">
						<div class="col-xs-4">
							<label>Periodicidade:</label>
							<select class="form-control" name="_1_<?= $_acao ?>_evento_periodicidade">
								<? fillselect(EventoController::$peridiocidade, $_1_u_evento_periodicidade); ?>
							</select>
						</div>
						<div class="col-xs-4">
							<label>Data Até:</label>
							<input name="_1_<?= $_acao ?>_evento_repetirate" class="calendario form-control" type="text" autocomplete="off" value="<?=$_1_u_evento_repetirate ?>">
						</div>
						<div class="col-xs-4">
							<label>Fim de Semana:</label>
							<select class="form-control" name="_1_<?= $_acao ?>_evento_fimsemana">
								<? fillselect(EventoController::$fimDeSemana, $_1_u_evento_fimsemana); ?>
							</select>
						</div>
					</div>
				</div>                 
			</div>
		</div>
		<div class="col-xs-12 col-md-6">
			<?
			if(!empty($_1_u_evento_idevento))
			{				

				if($eventoTipo['prevhoras']=='Y'){
					$tap=0;
					if(empty($_1_u_evento_prevhorasdec)){$_1_u_evento_prevhorasdec=0;}
					$arrapontamento = EventoController::buscarEventoApontamento($_1_u_evento_idevento);

					$totaldecimal=0;
					$estouro=0;
					foreach($arrapontamento as $ap){	
						$totaldecimal = $totaldecimal+$ap['valordecimal'];
					}
					$total = EventoController::buscarValorDecimal($idevento);//Pega o valor decimal convertido a partir de evento_horasexec
					if($total<=tratanumero($_1_u_evento_prevhorasdec)){
						$realizado=($total/tratanumero($_1_u_evento_prevhorasdec))*100;	
						$falta = 100 - $realizado;
						$horasfalta=$total-tratanumero($_1_u_evento_prevhorasdec);
					}else{ 
						$estouro="100";
						$realizado=(tratanumero($_1_u_evento_prevhorasdec)/$total)*100;
						$falta = 100 - $realizado;
						$horasfalta=tratanumero($_1_u_evento_prevhorasdec)-$total;
					}
					
					reset($arrapontamento);					
				?>			
					
				<div class="panel panel-default" >
					<div class="panel-heading" style="height:34px">
						<div class="row">
							<div class="col-xs-12 col-md-10 text-uppercase">PREVISÃO DE HORAS</div>                          
							<div class="col-xs-12 col-md-2"></div>
						</div>
					</div>						
					<div class="panel-body">			
						<table  class="table table-striped planilha"  style="font-size: 10px; word-break: break-word;">	 
							<tbody>	
								<tr style="font-weight: bold;">
									<td colspan='2' >
										<div class="row">
											<div class=" col-md-2 nowrap" style="padding-top: 10px;"></div>   
											<div class=" col-md-8 ">
												<div style="width: 150px;display: flex;align-content: center;flex-direction: column;border-radius: 6px;border: 1px solid silver;">
													<div style="display: flex;flex-direction: row;background-color: #e0e0e0;">
														<label style="display: flex;justify-content: center;width: 100%;">PREVISÃO</label>												
													</div>
													<div style="display: flex;flex-direction: row;background-color: #e0e0e0;">
														<label style="display: flex;justify-content: right;width: 45%;">Horas</label>
														<label style="display: flex;justify-content: center;width: 10%;">&nbsp;:&nbsp;</label>
														<label style="display: flex;justify-content: left;width: 45%;">Minutos</label>
													</div>
													<input id="evento_prevhoras" name="evento_prevhoras" type="text" valoratual="<?=$_1_u_evento_prevhoras?>"  value="<?=substr($_1_u_evento_prevhoras, 0, -3);?>" oninput="setMaskPattern(this)" onblur="atualizaprev(this)" style="border: none; outline: none;  text-align: center;align-self: center;display: flex;letter-spacing: 3px;" class="size10">
												</div>
				
												</div>                          
											<div class=" col-md-2 nowrap" style="padding-top: 10px;"></div>								
										</div>					
									</td>
									<td colspan='2'>	
										<div style="width: 150px;display: flex;align-content: center;flex-direction: column;border-radius: 6px;border: 1px solid silver;">
											<div style="display: flex;flex-direction: row;background-color: #e0e0e0;">
												<label style="display: flex;justify-content: center;width: 100%;">REGISTRADO</label>												
											</div>
											<div style="display: flex;flex-direction: row;background-color: #e0e0e0;">
												<label style="display: flex;justify-content: right;width: 45%;">Horas</label>
												<label style="display: flex;justify-content: center;width: 10%;">&nbsp;:&nbsp;</label>
												<label style="display: flex;justify-content: left;width: 45%;">Minutos</label>
											</div>

											<input readonly name="evento_horasexec" type="text"   value="<?=substr($_1_u_evento_horasexec, 0, -3);?>" style="text-align: center; border: none; outline: none;  align-self: center;display: flex;letter-spacing: 3px;" class="size10">
										</div>
									</td>
								</tr>
								<tr>
									<td colspan="4">
										<? 
										if( !empty($_1_u_evento_prevhorasdec) && $estouro == 0){
											?>
											<div class="miniBar" style="height: 12px; width: 100%;">
												<div class="miniBarProgress nowrap" style="left: 0%; width: <?=$realizado?>%; background-color: green;"><?=convertHoras(abs($total))?></div>
												<div class="miniBarProgress nowrap" style="left: <?=$realizado?>%; width: <?=$falta?>%; background-color: grey;"><?=convertHoras(abs($horasfalta))?></div>
											</div>
											<? 
										} elseif(!empty($_1_u_evento_prevhorasdec) and $estouro > 0){
											?>		
											<div class="miniBar" style="height: 12px;">
												<div class="miniBarProgress nowrap" style="left: 0%; width: <?=$realizado?>%; background-color: green;"><?=convertHoras(tratanumero($_1_u_evento_prevhorasdec))?></div>
												<div class="miniBarProgress nowrap" style="left: <?=$realizado?>%; width: <?=$falta?>%; background-color: red;"><?=convertHoras(abs($horasfalta))?></div>
											</div>
										<?
										}
										?>
									</td>
								</tr>
								<tr style="font-weight: bold;">
									<td colspan="4">
										<div class="row">
											<? 
											$colRel = ($_1_u_evento_relacionamento == 'Y') ? '5' : '8';
											$relacionamentos = EventoController::buscarRelacionamento($_1_u_evento_ideventotipo);
											?>
											<!-- TEMPO GASTO -->
											<div class="col-md-2 form-group">												
												<label class="d-block">
													TEMPO GASTO:
												</label>										
												<input title="Tempo gasto" name="evento_acomphoras" type="text" placeholder="00:00"  oninput="setMaskPattern(this)" value="" id="acomphoras" style="background-color: white; letter-spacing: 3px;" class="form-control text-center" />
											</div>

											<? if($_1_u_evento_relacionamento == 'Y') { ?>
												<!-- RELACIONAMENTO -->
												<div class="col-md-3 form-group">												
													<label class="d-block">
														RELACIONAMENTO:
													</label>										
													<select name="evento_relacionamento" id="evento_relacionamento" placeholder="Selecione" class="form-control" style="background-color: white;">
														<option value=""></option>
														<?
														foreach($relacionamentos as $_relacionamento){
															echo '<option value="'.$_relacionamento['ideventorelacionamento'].'" >'.$_relacionamento['descricao'].'</option>';
														}
														?>
													</select>
												</div>
											<? } ?>

											<!-- OBSERVAÇÃO -->
											<div class="col-md-<?=$colRel?> form-group">												
												<label class="d-block">
													OBSERVAÇÃO:
												</label>										
												<textarea title="Descrição do que foi realizado" class="caixa form-control mw-100 default" name="evento_descr" id="evento_descr" style="height: 32px;min-width: 100%;min-height: 32px;"></textarea>
											</div>

											<!-- REGISTRO -->
											<div class="col-md-2 form-group">												
												<button title="Registrar tempo e descrição" class="btn btn-success btn-xs" style="padding: 5px 10px 5px 10px !important; margin-top: 18%;" onclick="eventoapontamento()">Registrar</button>	
											</div>
										</div>																		
									</td>													
								</tr>
								<?								
								foreach($arrapontamento as $ap){
									?>
									<tr>
										<td  class="tblComentariosItem"><?=$ap['criadoem']?> - <?=$ap['nome']?>:</td> 
										<td  class="tblComentariosItem"><?=$ap['descr']?></td> 
										<? if($_1_u_evento_relacionamento == 'Y') { ?>
											<td  class="tblComentariosItem"><?=$ap['descricao']?></td> 
										<? } ?>
										<td class="tblComentariosItem nowrap">	<?=substr( $ap['valor'], 0, -3)?> Hora(s)
									
											<?									
											if($_SESSION["SESSAO"]["USUARIO"]==$ap['criadopor'] ){ ?>
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												<i class="fa fa-trash fa-1x cinzaclaro hoververmelho  pointer ui-droppable " onclick="CB.post({objetos:'_ev_u_eventoapontamento_ideventoapontamento=<?=$ap['ideventoapontamento']?>&_ev_u_eventoapontamento_status=INATIVO'})" title='Excluir!'></i>
											<?}?>
										</td>
									</tr>
									<?
								}							
								?>						
							</tbody>								
						</table>
					</div>
				</div>				
				<?
				}	

				//Anexar o Link dos Módulos - Lidiane (21-02-2020)
				if($eventoTipo['comentario']=='Y') 
				{  
					?>
					<div class="panel panel-default divHistorico" >
						<div class="panel-heading" style="height:34px">
							<div class="row">
								<div class="col-xs-12 col-md-10 text-uppercase">Comentários</div>                          
								<div class="col-xs-12 col-md-2">
									<button id="adicionar"
										  type="button"
										 style="margin-top: -2px; margin-right: 10px; display:none"
										 class="btn btn-success btn-xs fright"
										 title="Adicionar">
										<i class="fa fa-check"></i>Salvar
									</button>
								</div>
							</div>
						</div>
						<div class="panel-body" style="max-height: 100px; min-height: 100px; height: 100px;">
							<input name="_100_i_modulocom_idmodulocom" type="hidden" value="">  
							<input name="_100_i_modulocom_idempresa" type="hidden" value="1">
							<input name="_100_i_modulocom_idmodulo" type="hidden" value="<?=$_1_u_evento_idevento?>">
							<input name="_100_i_modulocom_modulo" type="hidden" value="evento">
							<textarea class="caixa mw-100"  name="_100_i_modulocom_descricao" id="obs" name="" style="width: 100%; height: 80px; resize: none;"></textarea>
							<input name="_100_i_modulocom_status" type="hidden" value="ATIVO">
						</div>
						<div class="panel-body">
							<table class="table table-striped planilha" style="font-size: 10px; word-break: break-word;" id="tblComentarios">
								<tbody>
								<?
									$comentarios = EventoController::buscarListaDeComentariosDoEvento($_1_u_evento_idevento);

									// $resc = $eventoclass->getListaComentariosEvento($_1_u_evento_idevento);
									foreach($comentarios as $comentario)
									{
										if ($comentario["anonimo"] == 'Y' && $comentario["dono"] == 'Y')
										{
											$comentario["nomecurto"] = '<i><b>ANÔNIMO</b></i>';
										}?>

										<tr>   
											<td class="tblComentariosItem"><?=dmahms($comentario['criadoem'])?> - <b><?=$comentario['nomecurto']?></b>: <?=nl2br($comentario['descricao'])?></td>
											<td>
												<?if($_SESSION["SESSAO"]["USUARIO"]==$comentario['criadopor'] and empty($comentario['idstatus'])){ ?>
													<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable hidden" onclick="CB.post({objetos:'_ajax_u_modulocom_idmodulocom=<?=$comentario['idmodulocom']?>&_ajax_u_modulocom_status=INATIVO',parcial:true})" title='Excluir!'></i>
												<?}?>
											</td>
										</tr>
										<?
									} ?>
								</tbody>
							</table>
						</div>
					</div>
				<? }				

				if($eventoTipo['participantes'] == 'Y'){ ?>
					<div class="panel panel-default">
						<div class="panel-heading text-uppercase" >Participantes </div>
						<div class="panel-body"  id="localInfo1"> 
							<? if (empty($_1_u_evento_idevento)){ echo '<p style="color:#aaa;"><i>Crie o evento para adicionar os participantes</i></p>';}else{?>						  
								<table>
									<tr>
										<td>
											<!-- <input id="funcsetvinc" class="compacto autocomplete" type="text" cbvalue placeholder="Selecione" <? if (empty($_1_u_evento_idevento)){ echo 'disabled="true"';}?>> -->
											<select id="funcsetvinc" class="compacto selectpicker" cbvalue placeholder="Selecione"  multiple="multiple" data-actions-box="true" data-live-search="true" <? if (empty($_1_u_evento_idevento) || $_1_u_evento_idfluxostatus == 1){ echo 'disabled="true"';}?>>
												
												<?foreach($grupoDeFuncionariosDisponiveisParaVinculo as $k => $v){
													echo '<option data-tokens="' . retira_acentos($v['labelnome']) . '" value="' . $v['pessoa'] . '_' . $v['labelnome'] . '_' .$v['tipo']. '" >' . $v['nome'] . '</option>';
												}?>
											</select>
											<button style="position: relative;bottom: -9px;" class="btn btn-xs btn-success" <? if ($_1_u_evento_idfluxostatus != 1){ ?>onclick="adicionaParticipantes()" <? } ?>>Adicionar Selecionados</button>
										</td>
									</tr>
								</table>
							<? } ?>
							<div class="col-xs-12 px-0">
								<div class="panel panel-default" style="background:#fff;height: 100%;overflow: auto;">
									<?= listarPessoasDoEvento(); ?>
								</div>
							</div>
						</div>
					</div>
				<? }
			}//if(!empty($_1_u_evento_idevento)){
			?>
		</div>   
	</div>
	<?
	if(empty($_1_u_evento_idevento))
	{
		$tags=[]; 
		$pessoas=[];
		$documentos=[];
	}//if(!empty($_1_u_evento_ideventotipo)  ){
	
	if(!empty($_1_u_evento_idevento))
	{
		// PENDENTE
		$eventoTipoBloco = EventoController::buscarEventoTipoBlocoPorIdEventoTipo($_1_u_evento_ideventotipo);
		// FIM PENDENTE

		$eventosAdd = EventoController::buscarEventoAddPorIdEvento($_1_u_evento_idevento);
		$i = 1;
		?>
		<div class="ordenarBloco">
			<div class="row ordenarBlocoItem">
				<?
				foreach($eventosAdd as $evento)
				{
					$camposVisiveisEventoAdd = EventoController::buscarCamposVisiveisPorIdEventoTipoAdd($evento['idobjeto']);
					
					if(!empty($evento['idobjeto']) OR ($evento['tipoobjeto'] == 'minievento' && $evento['tipoobjeto'] != 'crisolmat'))
					{
						?>
						<div class="col-xs-12">
							<div class="panel panel-default">
								<div class="panel-heading">
									<?
									$j = $j + 1;
									if(empty($evento['titulo'])){
										$dtit="";
										$class = '';
									}else{
										$dtit="readonly='readonly'";
										$class = 'desabilitado';
									}
									?>
									<div class="row d-flex flex-wrap align-items-center">
										<div class="col-xs-1">
											<i class="fa fa-arrows cinzaclaro hover move" title="Ordenar" style="padding-right: 10px;"></i>
											<input name="_ad<?=$j?>_u_eventoadd_ideventoadd" type="hidden" value="<?=$evento['ideventoadd']?>">
											<input name="_ad<?=$j?>_u_eventoadd_ord" type="hidden" value="<?=$evento["ord"]?>">
										</div>
										<div class="col-xs-8 col-md-10">
											<input <?=$dtit?> name="_ad<?=$j?>_u_eventoadd_titulo" class="size40 <?=$class?>" type="text" style="width:100% !important" value="<?=$evento['titulo']?>">
										</div>
										<div class="col-xs-3 col-md-1 d-flex align-items-center">
											<a class="fa fa-pencil hoverazul btn-lg pointer" onclick="editarpedido('_ad<?=$j?>_u_eventoadd_titulo');" title="Alterar Nome do Título."></a>
											<a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="deletaeventoadd(<?=$evento['ideventoadd']?>)" title="Excluir"></a>
										</div>
									</div>
								</div>
								<?
								//Verifica se existe o ideventotipoadd e retorna do os Objetos inseridos no EventipoTipoAdd
								if(!empty($evento['idobjeto']) && $evento['tipoobjeto'] && $evento['tipoobjeto'] != 'minievento' && $evento['tipoobjeto'] != 'criasolmat'){

									$eventoTipoAdd = EventoController::buscarEventoTipoAddPorChavePrimaria($evento['idobjeto']);
									?>
									<div  class="panel-body">
										<div class="row col-xs-12 form-group">
											<label>Observação</label>
											<textarea row="3" name="_ad<?=$j?>_u_eventoadd_observacao"  class="textdin form-control mw-100 w-100" ><?=$evento['observacao']?></textarea>
										</div>
										<div class="row w-100"> 
											<?            		
											if(!empty($eventoTipoAdd['tipopessoaobj']) && $evento['tipoobjeto'] == 'pessoa')
											{
												$pessoasEventoAdd = PessoaController::buscarPessoasPorIdTipoPessoa($eventoTipoAdd['tipopessoaobj'], false, false, null, 'nome', true);
												?>
												<div class="col-xs-12 col-md-4 form-group">
													<label>Adicionar Pessoas</label>
													<table class="w-100">
														<td><input ideventoadd="<?=$evento['ideventoadd']?>" id="pessoaeventoobjadd<?=$evento['ideventoadd']?>" class="compacto" type="text" cbvalue placeholder="Selecione" ></td>
													</table>
												</div>
												<?
											}elseif(empty($pessoasEventoAdd)){
												$pessoasEventoAdd=[];
											}
											
											if(!empty($eventoTipoAdd['tagtipoobj']) && $evento['tipoobjeto'] == 'tag')
											{
												$tags = TagController::buscarTagsQueNaoEstejamLocadasPorIdTagTipo($eventoTipoAdd['tagtipoobj'], $evento['idevento'], $evento['ideventoadd'],  true);
												?>
												<div class="col-md-4" >
													<div class="col-md-12">
														<label>Adicionar Tags</label>
													</div>
													<div class="col-xs-12">
														<table class="w-100">
															<td ><input ideventoadd="<?=$evento['ideventoadd']?>" id="tageventoobjadd<?=$evento['ideventoadd']?>" class="compacto" type="text" cbvalue placeholder="Selecione" ></td>
														</table>
													</div>
												</div>
												<?
											}elseif(empty($tags)){//if( !empty($row['tagtipoobj'])){
												$tags = []; 
											}
											if(!empty($eventoTipoAdd['prodservtipoobj']) && $evento['tipoobjeto'] == 'prodserv')
											{
												$prodServ = ProdServController::buscarProdServPorTipoObjEGetIdEmpresa($eventoTipoAdd['prodservtipoobj'], getidempresa('p.idempresa', 'prodserv'), true);
												?>
												<div class="col-md-4" >
													<div class="col-md-12">
														<label>Adicionar Produtos/Serviços</label>
													</div>
													<div class="col-md-12">
														<table>
															<td ><input ideventoadd="<?=$evento['ideventoadd']?>" id="prodserveventoobjadd<?=$evento['ideventoadd']?>" class="compacto" type="text" cbvalue placeholder="Selecione" ></td>
														</table>
													</div>
												</div>
												<?
											}elseif(empty($prodServ)){//if( !empty($row['tagtipoobj'])){
												$prodServ=[]; 
											}
											
											if( !empty($eventoTipoAdd['sgdoctipoobj']) && $evento['tipoobjeto'] == 'sgdoc')
											{
												$documentos = SgdocumentoController::buscarSgdocPorIdSgdocTipoEGetIdEmpresa($eventoTipoAdd['sgdoctipoobj'], getidempresa('p.idempresa','sgdoc'), false, true);
												?>
												<div class="col-md-4" >
													<div class="col-md-12">
														<label>Adicionar Documentos</label>
													</div>
													<div class="col-md-12">
														<table>
															<td ><input ideventoadd="<?=$evento['ideventoadd']?>" id="sgdoceventoobjadd<?=$evento['ideventoadd']?>" class="compacto" type="text" cbvalue placeholder="Selecione" ></td>
														</table>
													</div>
												</div>
												<?
											} elseif(empty($documentos)) {//if( !empty($row['tagtipoobj'])){
												$documentos=[]; 
											}	
											
											if( !empty($eventoTipoAdd['tipocamposobj']) && $evento['tipoobjeto'] == 'tipocampos')
											{												
												?>
												<div class="col-md-4" >
													<div class="col-md-12">
														<label>Adicionar Campos</label>
														<i class="fa fa-plus-circle fa-2x verde btn-lg pointer" onclick="addCampos(<?=$evento['ideventoadd']?>)" title="Adicionar Campos"></i>
													</div>
												</div>
												<?
											} 
											?>
											<div id="inputs" class="row" style="margin-left: 0px;"></div>
										</div>
									</div>
								<? } ?>
								<div id="vinculos-eventoadd-<?= $evento['ideventoadd'] ?>" class="panel-body overflow-x-auto">
									<!-- Cria solmat -->
									<? if($evento['tipoobjeto'] == 'criasolmat') { ?>	
										<div class="row w-100"> 									
											<div class="col-md-4" >
												<div class="col-md-12">
													<label>Adicionar Solmat</label>
												</div>
												<div class="col-md-12">
													<table>
														<td><button class="btn btn-xs btn-primary" onclick="criaSolmat(<?= $evento['ideventoadd'] ?>);">Nova Sol. de materiais</button></td>
													</table>
												</div>
											</div>
										</div>
									<?}?>
									<? if($evento['tipoobjeto'] == 'minievento')
									{
										$tagsTipoObjeto = $evento['tipoobjeto']; ?>	
										<div class="row w-100">
											<div class="col-xs-12">
											<label>Observação</label>
												<textarea row="3" name="_ad<?=$j?>_u_eventoadd_observacao" class="textdin form-control mw-100 w-100" ><?=$evento['observacao']?></textarea>
											</div>
										</div>
										<div class="row w-100"> 									
											<div class="col-md-4" >
												<div class="col-md-12">
													<label>Adicionar Eventos</label>
												</div>
												<div class="col-md-12">
													<table>
														<td><button class="btn btn-xs btn-primary" onclick="novaTarefa(true, <?=$evento['ideventoadd']?>)">Novo Evento</button></td>
													</table>
												</div>
											</div>
										</div>
									<?
									}else{//if( !empty($row['tagtipoobj'])){
										$jMiniEvento="null"; 
									}
										
									if($evento['tipoobjeto'] != 'criasolmat' && $evento['tipoobjeto'] != 'minievento' && $evento['tipoobjeto'] != 'tipocampos') {
										$tagsTipoObjeto = TagController::buscarTipoObjetoDasTagsPorIdEventoAddEIdEvento($evento['ideventoadd'], $_1_u_evento_idevento);
										?>
										<table class="table table-striped tbTags">
											<thead>
												<tr>
													<th></th>
													<th style="vertical-align: middle;">Descrição</th>
													<? if (count($tagsTipoObjeto) > 0)
													{ ?>											
														<th>
															<table>
																<tr>
																	<td colspan="2">Valores Referência</td>
																</tr>
																<tr>
																	<td>Min</td>
																	<td>Máx</td>
																</tr>
															</table>
														</th>
													<?
													}
													foreach ($camposVisiveisEventoAdd as $ord => $value) 
													{ ?>                        
														<th style="vertical-align: middle;"><?=strip_tags($value['rotulo'])?></th>
													<? } ?>                  
													<th></th>  		
													<th></th>  	
												</tr>
											</thead>
											<tbody>
												<?
												$listaDeTags = TagController::buscarListaDeTagsPorIdEventoEIdEventoTipoAdd($_1_u_evento_idevento, $evento['ideventoadd']);

												foreach($listaDeTags as $tag)
												{
													$i=$i+1;
													reset($camposVisiveisEventoAdd);

													?>
													<tr data-ideventoobj="<?= $tag['ideventoobj'] ?>">
														<td><i class="fa fa-arrows cinzaclaro hover move" title="Ordenar"></i></td>
														<td class="d-flex align-items-center">
															<select name="_<?=$i?>_u_eventoobj_idobjeto" vnulo <? if ($tag["objeto"]=='tag'){ echo 'readonly="readonly"'; }?> class="form-control col-xs-10">
																<option  value="">-</option>
																<? if ($tag["objeto"]=='sgdoc')
																{
																	$mlink = 'documento';
																	fillselect(SgdocumentoController::buscarSgdocPorIdSgdocTipoEGetIdEmpresa($eventoTipoAdd['sgdoctipoobj'], getidempresa('p.idempresa','sgdoc'), true), $tag['idobjeto']); 
																}else if ($tag["objeto"]=='tag')
																{
																	$mlink = 'tag';
																	//Retirado a Valiação do Status Ativo, devido já ter sido utilizado antes - Lidiane (12-02-2020)
																	fillselect(TagTipoController::buscarTagsPorTagTipo($eventoTipoAdd['tagtipoobj'], true, 'descr', 'tag'), $tag['idobjeto']);
																}else if ($tag["objeto"]=='pessoa'){
																	$mlink = 'pessoa';
																	fillselect(PessoaController::buscarPessoasPorIdTipoPessoa($eventoTipoAdd['tipopessoaobj'], false, true), $tag['idobjeto']); 
																}else if ($tag["objeto"]=='prodserv'){
																	$mlink = 'prodserv';

																	$tipoobj = '';

																	if (!empty($eventoTipoAdd['prodservtipoobj'])) {
																		$tipoobj = explode(',',$eventoTipoAdd['prodservtipoobj']);
																		
																	}
																	
																	fillselect(ProdServController::buscarProdServPorTipoObjEGetIdEmpresa($tipoobj, getidempresa('p.idempresa', 'pessoa'), false, true), $tag['idobjeto']); 
																}
																?>
															</select>
															<input name="_<?=$i?>_u_eventoobj_ideventoobj" type="hidden" value="<?=$tag['ideventoobj']?>">
															<a class="fa fa-bars fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="janelamodal('?_modulo=<?=$mlink;?>&_acao=u&id<?=$tag['objeto'];?>=<?=$tag['idobjeto']?>');" ></a>
														</td>
														<? if (count($tagsTipoObjeto) > 0){ ?>
														<td>
															<table style="width:100%;">
																<tr>
																	<td><?=$tagsTipoObjeto[$tag["idobjeto"]]['padraotempmin']?></td>
																	<td><?=$tagsTipoObjeto[$tag["idobjeto"]]['padraotempmax']?></td>
																</tr>
															</table>
														</td>
														<?
														}

														foreach($camposVisiveisEventoAdd as $ord => $value){
														?>                        
															<td>
																<?if($value['datatype']=='varchar' and $value['prompt']=='select'){?>
																	<select name="_<?=$i?>_u_eventoobj_<?=$value['col']?>" class="form-control">
																		<?
																			$code =  $value['code'];

																			if(strpos($value['code'], '?') !== false)
																				$code = str_replace('?', $tag['idobjeto'], $value['code']);
																		?>
																		<?fillselect($code, $tag[$value['col']]);?>		
																	</select>
																	
																<?}elseif($value['datatype']=='longtext'){?>
																	<textarea name="_<?=$i?>_u_eventoobj_<?=$value['col']?>" class="textdin mw-100 min-h-auto form-control" ><?=$tag[$value['col']]?></textarea>
																<?}else{
																	if($value['datatype']=='date'){
																		$class='calendario size8';
																		$tag[$value['col']] = dma($tag[$value['col']]);
																	}elseif($value['datatype']=='datetime'){
																		$class='calendariodatahora size8';
																		$tag[$value['col']] = dmahms($tag[$value['col']]);
																	}else{
																		$class='';
																	}
																	?>
																	<input class="<?=$class?>" name="_<?=$i?>_u_eventoobj_<?=$value['col']?>" type="text" value="<?=$tag[$value['col']]?>" class="form-control">
																<?}?>
															</td>
														<?
														}
														?>                   
														<td>                            
															<a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="retiraObj(<?=$tag['ideventoobj']?>)" title="Excluir"></a>
														</td>
														<td>
															<?
															$eventoCor = EventoController::buscarCorPorIdEventoEIdEventoObj($_1_u_evento_idevento, $tag['ideventoobj']);
															foreach($eventoCor as $cor)
															{
																$status = $cor['status'];
																$idevento = $cor['idevento'];
						
																if (!empty($idevento)){
																	?>		 
																	<a class="" onclick="janelamodal('?_modulo=evento&_acao=u&idevento=<?=$idevento?>');" title="<?=$idevento;?>"><span title="<?=$cor['rotulo'];?>" class="circle button-<?=$cor['cor'];?>" style="background:<?=$cor['cor'];?>; border:none;margin:5px;"></span></a>
																	<?				
																}else{
																	?>
																	<a class="fa fa-plus fa-1x cinzaclaro hoververde btn-sm pointer ui-droppable" onclick="novaTarefa(false,undefined,'eventoobj',<?=$tag['ideventoobj'];?>)" title="Nova Tarefa"></a>
																	<?
																}
															}
															?>
														</td>
													</tr>
												<?}?>
											</tbody>
										</table>
										<? 
									} else {
										if($evento['tipoobjeto'] == 'criasolmat')  {
											$solmatVinculada = EventoController::buscarSolmatVinculada($_1_u_evento_idevento, $evento['ideventoadd']);

											foreach($solmatVinculada as $solmat) { ?>
												<a target="_blank" href="?_modulo=solmat&_acao=u&idsolmat=<?= $solmat['idsolmat'] ?>&_idempresa=<?= $solmat['idempresa'] ?>">
													<div class="row"
														style="color:#333 !important;padding:8px; position:relative;">
														<div class="col-lg-2 col-sm-3 col-xs-6 atalhoEvento" style="font-size: 12px; color: #333333;">
															<div class="col-lg-12 col-xs-12" style="font-size: 12px;text-align:center">
																<div
																	style="border-radius:15px;border:1px solid <?=$cor?>;color:<?=$cor?>;padding: 2px 6px;font-size:9px;word-break:normal;text-align:center;text-transform:uppercase;">
																	Solicitação
																</div>

															</div>
														</div>

														<div class="col-lg-4 col-sm-9 col-xs-12 atalhoHist"
															style="display: block; word-break: break-word;font-size: 12px;">
															<div class="col-lg-12 col-xs-12 descricao"
																style="display:flex; flex-direction:column;min-height: 24px;border-bottom:1px solid #ddd;text-transform:uppercase;font-size:10px;margin:0px 12px;">
																<div>
																	<strong>Unidade origem:</strong> <span><?= $solmat['unidadeOrigem'] ?></span>
																</div>
																<div>
																	<strong>Unidade destino:</strong> <span><?= $solmat['unidadeDestino'] ?></span>
																</div>
															</div>
														</div>
														<div class="col-lg-5 col-sm-8 col-xs-12 atalhoPart" style="font-size: 12px;">
															<div class="col-lg-6 col-xs-12 " style="font-size: 12px;">
																<div
																	style="display:flex; flex-direction:column;border-radius:15px;width:100%;text-transform:uppercase;background:<?=$solmat['corstatus']?>;color:<?=$solmat['cortextostatus']?>;padding: 2px 6px;font-size:9px;word-break:normal;text-align:center;">
																	<strong>Status</strong>
																	<span><?=$solmat['status'];?></span>
																</div>								
															</div>
															<div class="col-lg-3 col-xs-12 origem" style="display:flex; flex-direction:column;font-size: 10px; color: #333;">
																<strong>
																	CRIADO POR
																</strong>
																<span><?=$solmat['nomecurto'] ?? 'Usuário desconhecido'?></span>
															</div>
														</div>
													</div>
												</a>
											<? }
										}

										//Carrega os MiniEventos de acordo com cada TipoAdd
										if(count(EventoController::buscarCamposObjPorIdEventoEIdEventoAdd($_1_u_evento_idevento, $evento['ideventoadd'], 'evento')))
										{?>
											<script>
												//Carrega o evento setado no Minievento
												<? if($evento['tipoobjeto'] == 'minievento') { ?>
													$(document).ready(function() {
														toggleFiltrarTarefas(<?=$_1_u_evento_idevento?>, true, <?=$evento['ideventoadd']?>);
													})
												<? } ?>
											</script>
											<div class="eventos_<?=$evento['ideventoadd']?>"></div>
										<? 
										}

										$tipoCamposEventoAdd = EventoController::buscarCamposObjPorIdEventoEIdEventoAdd($_1_u_evento_idevento, $evento['ideventoadd'], 'tipocampos');
										if(count($tipoCamposEventoAdd) > 0){
											foreach($tipoCamposEventoAdd as $_campos){
												$tipocampos = EventoController::buscarCamposVisiveisEventoTipoAdd($_1_u_evento_ideventotipo);
												?>
												<div class="d-flex flex-wrap">
													<?
													listaCampos($tipocampos, 'eventoAdd', $_campos['ideventoobj'], $_campos['jsonconfigcampos']);
													?>
												</div>
												<hr class="border-1 my-5" style="border-color: #bbb;"/>
												<?
											}											
										}
									}?>
								</div>
							</div>
						</div>
					<? 
					}
					if (!empty($_1_u_evento_ideventopai)) 
					{
						$subevento = true;
					}
					?>
					<script>
						jTag=<?= json_encode($tags ?? []) ?>;
						jPrd=<?= json_encode($prodServ ?? []) ?>;
						jPessoa=<?= json_encode($pessoasEventoAdd ?? []) ?>;
						jSgdoc=<?= json_encode($documentos ?? []) ?>;

						$("#sgdoceventoobjadd<?=$evento['ideventoadd']?>").autocomplete({
							source: jSgdoc
							,delay: 0
							,create: function(){
								$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
									lbItem = item.label;			
									return $('<li>')
										.append('<a>' + lbItem + '</a>')
										.appendTo(ul);
								};
							}
							,select: function(event, ui){
								CB.post({
									objetos: {
										"_x_i_eventoobj_idevento":$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
										,"_x_i_eventoobj_idobjeto": ui.item.value
										,"_x_i_eventoobj_objeto": 'sgdoc'
										,"_x_i_eventoobj_ideventoadd":$(this).attr("ideventoadd")
									}
									,parcial: true
								});
							}
						});
						
						$("#pessoaeventoobjadd<?=$evento['ideventoadd']?>").autocomplete({
							source: jPessoa
							,delay: 0
							,create: function(){
								$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
									lbItem = item.label;			
									return $('<li>')
										.append('<a>' + lbItem + '</a>')
										.appendTo(ul);
								};
							}
							,select: function(event, ui){
								CB.post({
									objetos: {
										"_x_i_eventoobj_idevento":$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
										,"_x_i_eventoobj_idobjeto": ui.item.value
										,"_x_i_eventoobj_objeto": 'pessoa'
										,"_x_i_eventoobj_ideventoadd":$(this).attr("ideventoadd")
									}
								});
							}
						});

						$("#tageventoobjadd<?=$evento['ideventoadd']?>").autocomplete({
							source: jTag
							,delay: 0
							,create: function(){
								$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
									lbItem = item.label;	
									lbSigla = item.sigla;		
									return $('<li>')
										.append('<a>'+ lbSigla + '-'+ lbItem + '</a>')
										.appendTo(ul);
								};
							}
							,select: function(event, ui){     
								ideventoaddTag = $(this).attr("ideventoadd");
								CB.post({
									objetos: {
										"_x_i_eventoobj_idevento":$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
										,"_x_i_eventoobj_idobjeto": ui.item.value
										,"_x_i_eventoobj_objeto": 'tag'	
										,"_x_i_eventoobj_ideventoadd":ideventoaddTag
									}
								});
							}
						});
						$("#prodserveventoobjadd<?=$evento['ideventoadd']?>").autocomplete({
							source: jPrd
							,delay: 0
							,create: function(){
								$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
									lbItem = item.label;		
									return $('<li>')
										.append('<a>'+ lbItem + '</a>')
										.appendTo(ul);
								};
							}
							,select: function(event, ui){     
								ideventoaddTag = $(this).attr("ideventoadd");
								CB.post({
									objetos: {
										"_x_i_eventoobj_idevento":$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
										,"_x_i_eventoobj_idobjeto": ui.item.value
										,"_x_i_eventoobj_objeto": 'prodserv'	
										,"_x_i_eventoobj_ideventoadd":ideventoaddTag
									}
								});
							}
						});
					</script>
					<?
				}//while($rowad=mysqli_fetch_assoc($resad)){
				?>
			</div>
		</div>
		
		<?
		if(count($eventoTipoBloco)){
		?>
		<div class='row'>
				<div class="col-md-12">
					<div class="panel panel-default">      
						<div class="panel-body">             
							<div class="agrupamento novo">
								<label for="" class="text-uppercase">Adicionar:</label>
								<?
								foreach($eventoTipoBloco as $item)
								{?>
									<button id="novobloco" class="btn btn-success" onclick="NovoBloco(<?=$_1_u_evento_idevento?>,<?=$item['ideventotipoadd']?>,'<?=$item['titulo']?>','<?=$item['tipoobjeto']?>');"><i class="fa fa-plus"></i> <?=$item['titulo']?></button>
								<?}?>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?
		}
		
		$eventosFilhos = EventoController::buscarEventosFilhosPorIdEvento($_1_u_evento_idevento);
		if(count($eventosFilhos))
		{
			$travasala= traduzid('eventotipo', 'ideventotipo', 'travasala', $_1_u_evento_ideventotipo);
			
			?> 
			<div class="row">  
				<div class="col-md-12">
					<div class="col-md-12">
						<div class="panel panel-default">  
							<div class="panel-heading">Eventos Filhos</div>
							<div class="panel-body overflow-x-auto">
								<table class="table table-striped planilha">
									<tr>
										<th>ID</th>
										<th>Evento</th>
										<th>Inicio</th>
										<th>Fim</th>
										<th>Prazo</th>
										<th>Status</th>
										<?if($travasala=="Y"){?>
										<th>Tag</th>
										<?}?>
									</tr>
									<?
									foreach($eventosFilhos as $evento)
									{//abreevento
										?>
										<tr>
											<td >
												<button type="button" class="btn btn-link btn-xs " onclick="janelamodal('?_modulo=evento&_acao=u&idevento=<?=$evento['idevento']?>');"> <?=$evento['idevento']?></button>                       
											</td>
											<td><?=  array_filter($camposVisiveis, function($item) {return $item['col'] == 'evento';}) ? EventoController::transformarSelectEmArray(array_filter($camposVisiveis, function($item) {return $item['col'] == 'evento';})[0]['code'])[$evento['evento']] : $evento['evento']?></td>
											<td><?=dma($evento['inicio'])?> <?=$evento['iniciohms']?></td>
											<td><?=dma($evento['fim'])?> <?=$evento['fimhms']?></td>
											<td><?=dma($evento['prazo'])?></td>
											<td><?=$evento['status']?></td>
											<?if($travasala=="Y"){?>
											<td><?=$evento['tag']?>-<?=$evento['descricao']?></td>
											<?}?>
										</tr>   
										<?
									}
									?>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?
		}//if($qtdfi>0){
			
		if(!empty($_1_u_evento_ideventopai))
		{
			$equipamentos = EventoController::buscarEquipamentosPorIdEvento($_1_u_evento_ideventopai);
			if(count($equipamentos))
			{
				$travasala= traduzid('eventotipo', 'ideventotipo', 'travasala', $_1_u_evento_ideventotipo);

				?> 
				<div class="row">  
					<div class="col-md-12">
					<div class="col-md-12">
						<div class="panel panel-default">  
						<div class="panel-heading text-uppercase">Evento Pai</div>
						<div class="panel-body overflow-x-auto">
							<table class="table table-striped planilha">
								<tr>
									<th>ID</th>
									<th>Evento</th>
									<th>Inicio</th>
									<th>Fim</th>
									<th>Prazo</th>
									<?if($travasala=="Y"){?>
										<th>Tag</th>
									<?}?>
								</tr>
								<?
								foreach($equipamentos as $equipamento){//abreevento
								?>
								<tr>
									<td >
										<button type="button" class="btn btn-link btn-xs " 
												onclick="janelamodal('?_modulo=evento&_acao=u&idevento=<?=$equipamento['idevento']?>');"
										> <?= $equipamento['idevento'] ?></button>                       
									</td>
									<td><?=dma($equipamento['inicio'])?> <?=$equipamento['iniciohms']?></td>
									<td><?=dma($equipamento['fim'])?> <?=$equipamento['fimhms']?></td>
									<td><?=dma($equipamento['prazo'])?></td>
									<?if($travasala=="Y"){?>
									<td><?=$equipamento['tag']?>-<?=$equipamento['descricao']?></td>
									<?}?>
								</tr>   
								<?
								}
								?>
							</table>
						</div>
						</div>
					</div>
					</div>
				</div>
				<?
			}//if($qtdfp>0){
			
			
		}//if(!empty($_1_u_evento_ideventopai)){

	}//if(!empty($_1_u_evento_ideventotipo)){

	if ($anonimo == 'N'){
		if(!empty($_1_u_evento_idevento)){// trocar p/ cada tela a tabela e o id da tabela
			$_idModuloParaAssinatura = $_1_u_evento_idevento; // trocar p/ cada tela o id da tabela
			require 'viewAssinaturas.php';
		}
	}

	if ($anonimo == 'N'){
		$tabaud = "evento"; //pegar a tabela do criado/alterado em antigo
		if($eventoTipo['upload'] != "Y" and $eventoTipo['upload'] != null){
			$_disableDefaultDropzone = true;
		}
		$idRefDefaultDropzone = "mainDropzone";
		require 'viewCriadoAlterado.php';
	}
	
	/* Alteração realizada para carregar o evento, página ou documento detnro de um iframe, facilitando a visualização.
	No caso do Evento valida se o valor do idevento é igual ao modulo para que não apareça o mesmo evento repetido na página (19-01-2020 - Lidiane) */
	?>
	
	<? if($_1_u_evento_idsgdoc || $_1_u_evento_idsgdocrnc){ 
		if($_1_u_evento_idsgdoc){
			$linkIframe = "?_modulo=documento&_acao=u&idsgdoc=".$_1_u_evento_idsgdoc."&_menu=N";
		} else{ 
			$linkIframe = "?_modulo=documento&_acao=u&idsgdoc=".$_1_u_evento_idsgdocrnc."&_menu=N";
		}
		?>
		<div id="iframeModulo" style="height: 800px; padding-top: 4%;"></div>
		<?
	} elseif($_1_u_evento_idevento && $_1_u_evento_idmodulo && $_1_u_evento_idevento != $_1_u_evento_idmodulo) { 
		if ($_1_u_evento_modulo == 'eventoobj')
		{
			$eventoObj = EventoController::buscarEventoObjPorChavePrimaria($_1_u_evento_idmodulo);

			foreach($eventoObj as $evento)
			{
				$nmod = 'evento';
				$nmodid = $evento['idevento'];
			}
			//Todos os eventos dentro do IFRAME aparecerão para as pessoas que estão no principal, exceto quando for privado
			//sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=321527 (Lidiane - 21-05-2020)

			$linkIframe = "?_modulo=<?=$nmod;?>&_acao=u&idevento=".$nmodid."&anexo=Y&_menu=N";
			
		}else{
			$linkIframe = "?_modulo=".traduzid("evento","idevento","modulo",$_1_u_evento_idevento)."&anexo=Y&_acao=u&".$nomemodulo."=".traduzid("evento","idevento","idmodulo",$_1_u_evento_idevento)."&_menu=N";
		}

		?>
		<div id="iframeModulo" style="height: 800px; padding-top: 4%;"></div>
		<?
	} 

$idPessoa = $_SESSION["SESSAO"]["IDPESSOA"];
$nomePessoa = $_SESSION["SESSAO"]["NOMECURTO"];

$funcionariosDisponiveisParaVinculo = PessoaController::buscarPessoasDisponiveisParaVincularNoEvento($_1_u_evento_idevento, $_SESSION["SESSAO"]["IDPESSOA"], getidempresa('a.idempresa','evento'), true);
$gruposDisponiveisParaVinculo = ImGrupoController::buscarGruposDisponiveisParaVinculoNoEvento($_1_u_evento_idevento, getidempresa('idempresa','evento'), true);

function listarDocumentosVinculados($idEvento){
	$documentos = EventoController::buscarDocumentosVinculadosPorIdEvento($idEvento);

	if(!$documentos) return;

		echo"<table class='mt-3'>
			<thead>
				<th>Documento</th>
				<th></th>
			</thead>
			<tbody>";
			foreach($documentos as $documento)
				echo "<tr>
						<td>
							<a target='_blank' href='?_modulo=documento&_acao=u&idsgdoc={$documento['idsgdoc']}'>{$documento['titulo']}</a>
						</td>
						<td><i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularDocumento( {$documento['idobjetovinculo']})' title='Excluir!'></i></td>
					</tr>";
			echo "</tbody>
			</table>";
}
	
function listarPessoasDoEvento()
{
	global $_1_u_evento_idevento, $_1_u_evento_idfluxostatus, 
			$_1_u_evento_idpessoa, $_1_u_evento_modulo, $_1_u_evento_idmodulo, 
			$modelo, $fluxounico, $_1_u_evento_status;

	$pessoas = EventoController::buscarTodasPessoasDoEventoPorIdEvento($_1_u_evento_idevento);

	$statusDoModuloAtual = getModuloTab($_1_u_evento_modulo);

	echo "<div class='table-hover table table-striped planilha'>";

	// Verifica se a pessoa pode remover quaquer grupo ou pessoa
	// getModsUsr("MODULOS"): Pega todos os modulos vinculados às minhas LP's
	$eventoMaster = array_key_exists('eventomaster', getModsUsr("MODULOS"));
	$grupo = '';
	$opacity = '';

	foreach($pessoas as $pessoa)
	{
		$cor = $pessoa['respcor'];
		$respstatus = $pessoa['respstatus'];
										
		if ($pessoa["idpessoa"] == $_SESSION["SESSAO"]["IDPESSOA"])
		{
			echo '<input id="statusresp" type="hidden" value="'.$pessoa["resptoken"].'" readonly="readonly">';
		}
					
		$pad = '';
		$op ='';
		
		if( $pessoa['oculto'] == '1')
		{
			$op = 'opacity:0.5;';
		}
		
		if ($grupo != $pessoa["grupo"])
		{
			if ($grupo != '')
			{
				echo '</div></fieldset>';
			}

			$grupo = $pessoa["grupo"];
			if(($_SESSION["SESSAO"]["IDPESSOA"] != $_1_u_evento_idpessoa) && (!$eventoMaster))
			{
				echo "<div style='padding:0px 6px;'><fieldset class='scheduler-border'><legend class='scheduler-border'>".$grupo." <i class=\"fa fa-ban fa-1x cinzaclaro hovercinza btn-lg pointer ".$cor." ui-droppable\" title='Excluir!'></i><a class='fa fa-bars pointer hoverazul' title='Grupo' onclick=\"janelamodal('?_modulo=imgrupo&_acao=u&idimgrupo=".$pessoa["idimgrupo"]."')\"></a></legend>";
			}else
			{
				if ($modelo == 'xs')
				{
					echo "<div style='padding:0px 6px;'><fieldset class='scheduler-border'><legend class='scheduler-border'>".$grupo." <a class='fa fa-bars pointer hoverazul' title='Grupo' onclick=\"janelamodal('?_modulo=imgrupo&_acao=u&idimgrupo=".$pessoa["idimgrupo"]."')\"></a></legend>";
				} else
				{
					echo "<div style='padding:0px 6px;'><fieldset class='scheduler-border'><legend class='scheduler-border'>".$grupo." <i class=\"fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable\" onclick=\"retirasgsetor(".$pessoa['idfluxostatuspessoagrupo'].",'".$grupo."')\" title='Excluir!'></i><a class='fa fa-bars pointer hoverazul' title='Grupo' onclick=\"janelamodal('?_modulo=imgrupo&_acao=u&idimgrupo=".$pessoa["idimgrupo"]."')\"></a></legend>";
				}
			}
		}	

		if($pessoa['idtipopessoa']==1){
			$mod='funcionario';
		}else{
			$mod='pessoa';
		}

		if(($pessoa['idpessoa'] == $_1_u_evento_idpessoa || !$eventoMaster) && ($pessoa['inseridomanualmente']=='N' or $_SESSION["SESSAO"]["IDPESSOA"] != $_1_u_evento_idpessoa) || $grupo){
			$botao="<i class='fa fa-ban fa-1x cinzaclaro hovercinza btn-lg pointer #f5f5f5 ui-droppable px-0' title='Sem permissão para exclusão!'></i>";
		}else{
			$botao="<i class='fa fa-trash fa-1x cinzaclaro hoververmelho  pointer ui-droppable' status='".$pessoa["status"]."' idfluxostatuspessoa='".$pessoa["idfluxostatuspessoa"]."' onclick='retirapessoa(".$pessoa["idfluxostatuspessoa"].",\"".$pessoa["nomecurto"]."\")'></i>";
		}
		$title="Vinculado por: ".$pessoa["criadopor"]." - ".dmahms($pessoa["criadoem"],true);
		if ($pessoa["setor"]){
			$cl = "&nbsp<span style='background: rgb(102, 102, 102);font-size: 10px;color: #fff;padding: 0px 6px;border-radius: 3px;'>".$pessoa["setor"]."</span>";
		}else{
			$cl = '';
		}	
		
		if ($pessoa['oculto']== '1'){
			$vs = "<i class='fa fa-eye-slash' style='font-size: 14px;color:silver'></i>&nbsp";
		}elseif ($pessoa["visualizado"] == '1'){
			$vs = "<i class='fa fa-check' style='font-size: 14px;color:#4FC3F7'></i>&nbsp";
		}else{
			$vs = "<i class='fa fa-check' style='font-size: 14px;color:#fff'></i>&nbsp";
		}
		
		if ($pessoa['aprova'] == 1){
			$va = "<i class='fa fa-edit' style='font-size: 14px;color:#fff'></i>&nbsp";
		}else{
			$va = "<i class='fa fa-edit' style='font-size: 14px;color:#fff'></i>&nbsp";	
		}
		
		
		if ($pessoa["anonimo"] == 'Y' && $pessoa["dono"] == 'Y'){
			$pessoa["nomecurto"] = '<i><b>ANÔNIMO</b></i>';
			$cl = '';
		}

		$onclickCarregar = "";

		if($fluxounico != 'Y') {
			$onclickCarregar = "onclick='carregaFiltroTipoEvento(".$pessoa['idfluxostatuspessoa'].", ".$pessoa['idpessoa'].", ".$_1_u_evento_idevento.");'";
		}

		if($fluxounico == 'Y' && $pessoa['oculto'] == 1 && !in_array($pessoa['statustipo'], ['CONCLUIDO', 'CANCELADO'])
			&& ($pessoa['idpessoa'] == $_1_u_evento_idpessoa || $eventoMaster)){
			$onclickCarregar = "onclick='carregaFiltroTipoEventoUnico(".$pessoa['idfluxostatuspessoa'].",".$pessoa['idpessoa'].", ".$_1_u_evento_idfluxostatus.", `".$_1_u_evento_status."`, `".$pessoa['nomecurto']."`);'";
		}

		if ($modelo == 'xs'){
			$md1 = '12';
			$md2 = '12';

			echo "<div id=".$pessoa["idfluxostatuspessoa"]." class='".$opacity." col-md-12' style='".$pad."".$op."''>
					<div class='col-xs-".$md1."' style='line-height: 14px; padding: 8px; font-size: 10px;'>
						<span data-toggle='collapse' href='#".$pessoa['idpessoa']."' title=".$respstatus." $onclickCarregar class='circle button-".$cor."' style='background:".$cor."; border:none;'></span>&nbsp".$vs."".$pessoa["nomecurto"]." ".$cl."</div>
						<div class='col-md-".$md2."'><div style='float:right;font-size:9px;cursor:default;' class='btn btn-xs btn-".$clbt."'><i class='fa fa-check'></i>&nbsp;Assinatura</button></div>
					</div>
				</div>
				<div id='collapse-".$pessoa['idpessoa']."'></div>";
		}else{
			$md1 = '11';
			$md2 = '3';
			$md3 = '1';
			echo "<div id='".$pessoa["idfluxostatuspessoa"]."' class='".$opacity."d-flex align-items-center col-xs-12' style='".$pad."".$op."''>
					<div class='col-xs-10 col-md-".$md1."' style='line-height: 14px; padding: 8px; font-size: 10px;'>
						<span data-toggle='collapse' href='#".$pessoa['idpessoa']."' title='".$respstatus."' $onclickCarregar class='circle button-".$cor."' style='background:".$cor."; border:none;'></span>&nbsp".$vs."".$pessoa["nomecurto"]." ".$cl."
					</div>
					<div class='col-xs-2 col-md-".$md3."'>".$botao."</div> 
				</div>
				<div id='collapse-".$pessoa['idpessoa']."'></div>";
		}
	}
	if ($grupo != ''){
		echo '</div></fieldset>';
	}
	echo "</div>";
}
	
function listaCampos($camposVisiveis, $tipo = NULL, $ideventoobj = NULL, $jsonconfigcampos = NULL){	

	global $_1_u_evento_idevento, $_acao, $_1_u_evento_ideventotipo, $eventoTipo, $_1_u_evento_idpessoaev, $_1_u_evento_idsgdoc, $_1_u_evento_idsgarea, $_1_u_evento_idsgdepartamento,
			$_1_u_evento_idsgsetor, $_1_u_evento_idequipamento, $verificasala, $_1_u_evento_inicio, $arrTime, $_1_u_evento_prazo, $_1_u_evento_mostradata, $coricone, $dataTarefa,
			$arrDuracaoTempo, $st, $_1_u_evento_diainteiro, $_1_u_evento_iniciohms, $_1_u_evento_duracaohms, $_1_u_evento_posicao, $_1_u_evento_datasla, $_1_u_evento_repetirevento, $_1_u_evento_sla, $idevento, $textalign,
			$_1_u_evento_textocurto5, $_1_u_evento_textocurto6, $_1_u_evento_evento, $_1_u_evento_descricao, $_1_u_evento_textocurto1, $_1_u_evento_textocurto2, 
			$_1_u_evento_textocurto3, $_1_u_evento_textocurto4, $_1_u_evento_classificacao, $_1_u_evento_complemento, $_1_u_evento_datafim, $_1_u_evento_datainicio, 
			$_1_u_evento_idempresa, $_1_u_evento_motivo, $_1_u_evento_nomecompleto, $_1_u_evento_url, $pessoas, $sgDoc, $areas, $departamentos, $setores, $tags,
			$_1_u_evento_horainicio, $_1_u_evento_horafim,$_1_u_evento_textocurto7, $_1_u_evento_textocurto8, $_1_u_evento_textocurto9, $_1_u_evento_textocurto10, $_1_u_evento_textocurto11,
			 $_1_u_evento_textocurto12, $_1_u_evento_textocurto13, $_1_u_evento_textocurto14 ,$_1_u_evento_textocurto15;

	foreach ($camposVisiveis as $ord => $value) {
		$obrigatorio = $value['obrigatorio'] ? 'vnulo' : '';

		$colsp = "col-xs-{$value['larguracoluna']}";

		$fvar = "_1_u_evento_".$value['col'];
		$fvar = $$fvar;
		$nameCamposUI = "";

		$jsonconfigcamposArray = json_decode($jsonconfigcampos, true);
		foreach($jsonconfigcamposArray as $key => $res){
			if($key == $value['col']){
				$fvar = $res;

				switch($value['col']){
					case 'idpessoaev':
						$_1_u_evento_idpessoaev = $res;
					case 'idequipamento': 
						$_1_u_evento_idequipamento = $res;
					case 'idsgdoc': 
						$_1_u_evento_idsgdoc = $res;
					case 'idsgarea': 
						$_1_u_evento_idsgarea = $res;
					case 'idsgdepartamento': 
						$_1_u_evento_idsgdepartamento = $res;
					case 'idsgsetor': 
						$_1_u_evento_idsgsetor = $res;
					break;
				}
			}
		}

		if($tipo == 'eventoAdd'){
			$nameCampos = 'eventoAdd_'.$value['col'].'_'.$ideventoobj;
			$nameCamposUI = 'eventoAdd_'.$value['col'].'_'.$ideventoobj;			
			$nameIdPessoaev = 'eventoAdd_idpessoaev_'.$ideventoobj;
			$nameIdSgDoc = 'eventoAdd_idsgdoc_'.$ideventoobj;
			$nameIdEquipamento = 'eventoAdd_idequipamento_'.$ideventoobj;
			$nameIdSgArea = 'eventoAdd_idsgarea_'.$ideventoobj;
			$nameIdSgDepartamento = 'eventoAdd_idsgdepartamento_'.$ideventoobj;
			$nameIdSgSetor = 'eventoAdd_idsgsetor_'.$ideventoobj;
			$classCampos = 'eventoAdd';
		} else {
			$nameCampos = "_1_u_evento_".$value['col'];
			$nameCamposUI = "_1_".$_acao."_evento_".$value['col'];
			$classCampos = "";
			$nameIdPessoaev = '_1_'.$_acao.'_evento_idpessoaev';
			$nameIdSgDoc = '_1_'.$_acao.'_evento_idsgdoc';
			$nameIdEquipamento = '_1_'.$_acao.'_evento_idequipamento';
			$nameIdSgArea = '_1_'.$_acao.'_evento_idsgarea';
			$nameIdSgDepartamento = '_1_'.$_acao.'_evento_idsgdepartamento';
			$nameIdSgSetor = '_1_'.$_acao.'_evento_idsgsetor';
		}	

		if($value['col'] != 'idequipamento' or $_acao != 'i') {
			?>
			<div class="<?=$colsp ?> form-group px-2">
				<label class="w-100"><?=$value['rotulo'] ?? $value['col'] ?></label>
				<!-- Campos que possuem code -->
				<? if((!empty($value['tablecode']) || !empty($value['code'])) && $value['col'] != 'checklist') {
					$col = EventoTipoController::buscarColIdEventoTipoCampos($_1_u_evento_ideventotipo, $value['ideventotipocampos']);
					?>
					<input type="hidden" value="<?=$fvar ?>" class="<?=$value['col'] ?>_old">
					<? //para o evento notifica GQ(173) blocar o campo que tem o prazo de resposta já preenchido ?>
					<select name="<?=$nameCampos?>" class="form-control <?=$value['col'] ?> <?=$classCampos?> <?=$value['col'] ?>-<?=$ideventoobj?>" campo="<?=$value['col'] ?>" ideventoobj="<?=$ideventoobj?>" <?=$obrigatorio ?> onchange="mostrarSomenteVinculados('<?=$col ?>', '<?=$value['col'] ?>', this, <?=$ideventoobj?>)" <?=(($_1_u_evento_ideventotipo =='173' && $_1_u_evento_textocurto4 > 0 &&  $nameCampos == '_1_u_evento_textocurto4' ) ? 'readonly': '') ?> >
						<option value=""></option>
						<?
						$arrayVinculado = "";
						if($_1_u_evento_idevento) {
							$fluxoStatusHist = EventoController::buscarFluxoStatusHistPorIdEvento($_1_u_evento_idevento);
						}

						$code = ($value['tablecode']) ? $value['tablecode'] : $value['code'];
						$codedeletado = ($value['codedeletado']) ? $value['codedeletado'] : false;

						preg_match_all('/[$]([\w]+)+/', $code, $grupos);

						foreach ($grupos as $grupo) {
							foreach ($grupo as $key => $encontrado)
								$code = str_replace($encontrado, ${$grupos[1][$key]}, $code);

							break;
						}

						if(!empty($value['code']) || $value['code'] != "") {
							$opcoes = EventoTipoController::buscarCodigoPorConsulta($value['code']);

						}
						if(!empty($value['codevinculo']) || $value['codevinculo'] != "" || $value['codevinculo'] != null) {
							$opcoesVinculados = EventoTipoController::buscarCodigoPorConsulta($value['codevinculo']);
							$arrayVinculado = array_column($opcoesVinculados, 'id');
						}
						if($value['ideventotipocamposvinculo']) {
							$colCampoVinculado = EventoTipoController::buscarCodeIdEventoTipoCampos($value['ideventotipocamposvinculo']);
							$valorCampo = "_1_u_evento_".$colCampoVinculado['col'];
							$valorCampoPai = $$valorCampo;
						}
						$i = false;// para imprimir so uma vez
						if($arrayVinculado) {
							foreach ($opcoes as $_opcao) {
								foreach ($arrayVinculado as $valor) {
									$valorId = explode("-", $valor);

									if($valorId[0] == $_opcao['id']) {
										$campo = explode("-", $valor);
										$esconderNaoVinculados = (strpos($valor, $valorCampoPai) === false) ? 'display: none;' : '';
										if($fvar == $_opcao['id']) {
											$i = true;
											echo "<option value='{$_opcao['id']}' selected campo='{$campo[1]}' style='{$esconderNaoVinculados}'>{$_opcao['value']}</option>\n";
										} else {
											echo "<option value='{$_opcao['id']}' campo='{$campo[1]}' style='{$esconderNaoVinculados}'>{$_opcao['value']}</option>\n";
										}
									}
								}
							}

							//mostrar o deletado caso seja ele o selecionado no vinculado
							if($i == false){
								//pegar o valor para imprimir no banco	
								$sqlval = 'select * from ('. $codedeletado .') as a(a1, a2) where a1 =  "'.$fvar.'";';
								$ressql = d::b()->query($sqlval);
								if (mysqli_num_rows($ressql) > 0){
									$valdeletado = mysqli_fetch_array($ressql);
									echo "<option value='{$valdeletado[0]}' selected campo='{$valdeletado[0]}' >{$valdeletado[1]}</option>\n";
								}
							}
						} else {

							if($fvar) {
																
								if($codedeletado){
									//mostrar o deletado qdo ele for o que está no evento
									fillselectdeletado($codedeletado, $fvar);
									fillselect($code, $fvar, true);
								}else{
									//para os deletados antes da alteração vai manter o erro.
									fillselect($code, $fvar);
								}
								} else {
								fillselect($code);
							}
						}
						?>
					</select>

				<? } else if($value['datatype'] == 'longtext') { ?>

					<textarea class="form-control mw-100" name="<?=$nameCamposUI?>" <?=$obrigatorio ?>><?= strip_tags($fvar) ?></textarea>

				<? } else if($value['col'] == 'url' && $fvar) { ?>
					<div class="w-100 d-flex flex-wrap align-items-center">
						<a href="<?=$fvar ?>" target="_blank">
							<?=$fvar ?>
						</a>
						<i class='fa fa-trash text-danger ml-2 remove-url pointer'></i>
					</div>
					<? } else if(
					$value['col'] == 'idpessoaev' || $value['col'] == 'idequipamento' ||
					$value['col'] == 'idsgdoc' || $value['col'] == 'idsgsetor' ||
					$value['col'] == 'idsgdepartamento' || $value['col'] == 'idsgarea'
				) {
					if((!empty($eventoTipo['tipopessoaobj']) or !empty($_1_u_evento_idpessoaev)) and $value['col'] == 'idpessoaev') {
						$pessoas = PessoaController::buscarPessoasPorIdTipoPessoa($eventoTipo['tipopessoaobj'], false, false, false, 'nome', true);
						?>
						<input id="pessoaeventoobj" style="width: 97%;" name="<?=$nameIdPessoaev ?>" class="ui-autocomplete-input form-control pessoaeventoobj" type="text" cbvalue="<?=$_1_u_evento_idpessoaev ?>" placeholder="Selecione" value="<?= traduzid("pessoa", "idpessoa", "nome", $_1_u_evento_idpessoaev) ?>" <?=$obrigatorio ?>>

						<? //Criado para direcionar para o Cliente (Lidiane - 15-05-2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=313649 
						if($_1_u_evento_idpessoaev) { ?>
							&nbsp; <a class="fa fa-bars pointer hoverazul" title="Cadastro de  Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$_1_u_evento_idpessoaev ?>')"></a>
						<? } 
					} else if((!empty($eventoTipo['tagtipoobj']) or !empty($_1_u_evento_idequipamento)) and $value['col'] == 'idequipamento') {
						$tags = TagController::buscarTagsQueNaoEstejamLocadasPorIdTagTipo($eventoTipo['tagtipoobj'], false, false, true);

						if($eventoTipo['travasala'] == 'Y') { ?>
							<input name="travasala" type="hidden" value="<?=$eventoTipo['travasala'] ?>" class="form-control" <?=$obrigatorio ?>>
						<? } ?>

						<input id="tageventoobj" travasala="<?=$eventoTipo['travasala'] ?>" name="<?=$nameIdEquipamento?>" class="ui-autocomplete-input form-control tageventoobj" type="text" cbvalue="<?=$_1_u_evento_idequipamento ?>" placeholder="Selecione" value="<?= traduzid("tag", "idtag", "concat(tag,'-',descricao)", $_1_u_evento_idequipamento) ?>" <?=$verificasala ?> <?=$obrigatorio ?>>
						<div id="reservasala"></div>
					<?
					} elseif ((!empty($eventoTipo['sgdoctipoobj']) or !empty($_1_u_evento_idsgdoc)) and $value['col'] == 'idsgdoc') {
						$sgDoc = SgdocumentoController::buscarSgdocPorIdSgdocTipoEGetIdEmpresa($eventoTipo['sgdoctipoobj'], getidempresa('p.idempresa', 'sgdoc'), false, true);
						?>
						<input id="sgdoceventoobj" name="<?=$nameIdSgDoc?>" class="ui-autocomplete-input form-control sgdoceventoobj" type="text" cbvalue="<?=$_1_u_evento_idsgdoc ?>" placeholder="Selecione" value="<?= traduzid("sgdoc", "idsgdoc", "concat(idregistro,'-',titulo)", $_1_u_evento_idsgdoc) ?>" <?=$obrigatorio ?>>
						<br />
						<? //Alteração realizada, pois havia dois campos com idsgdoc - Lidiane (28-04-2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=314921 
						?>
						Link do Documento:
						<a title="Documento" href="javascript:janelamodal('?_modulo=documento&_acao=u&idsgdoc=<?=$_1_u_evento_idsgdoc ?>')">
							<?= traduzid("sgdoc", "idsgdoc", "titulo", $_1_u_evento_idsgdoc); ?> <?=$_1_u_evento_idsgdoc ?>
						</a>
					<?
					} // Pegar areas por empresa
					elseif ((!empty($eventoTipo['area_idempresa']) and $value['col'] == 'idsgarea') or (!empty($_1_u_evento_idsgarea) and $value['col'] == 'idsgarea')) {
						if(!empty($_1_u_evento_idsgarea)) {
							$areas = [SgareaController::buscarPorChavePrimaria($_1_u_evento_idsgarea, 'ATIVO', true)];
						} else {
							$areas = SgareaController::buscarAreasPorIdEmpresa($eventoTipo['area_idempresa'], true);
						}

						$idempresaArea = traduzid("sgarea", "idsgarea", "idempresa", $_1_u_evento_idsgarea);
						?>
						<input id="sgarea-idempresa" name="<?=$nameIdSgArea?>" class="ui-autocomplete-input form-control sgarea-idempresa" type="text" cbvalue="<?=$_1_u_evento_idsgarea ?>" placeholder="Selecione" value="<?= traduzid("empresa", "idempresa", "sigla", $idempresaArea) ?> - <?= traduzid("sgarea", "idsgarea", "area", $idempresaArea) ?>" <?=$obrigatorio ?>>
						<?
					} // Pegar departamentos por empresa
					elseif ((!empty($eventoTipo['departamento_idempresa']) and $value['col'] == 'idsgdepartamento') or (!empty($_1_u_evento_idsgdepartamento) and $value['col'] == 'idsgdepartamento')) {

						if(!empty($_1_u_evento_idsgdepartamento)) {
							$departamentos = [SgDepartamentoController::buscarPorChavePrimaria($_1_u_evento_idsgdepartamento, 'ATIVO', true)];
						} else {
							$departamentos = SgDepartamentoController::buscarDepartamentosPorIdEmpresa($eventoTipo['departamento_idempresa'], true);
						}

						$idempresaDepto = traduzid("sgdepartamento", "idsgdepartamento", "idempresa", $_1_u_evento_idsgdepartamento);
						$valueSgDepartamento = !(empty($_1_u_evento_idsgdepartamento)) ? traduzid("empresa", "idempresa", "sigla", $idempresaDepto)." - ".traduzid("sgdepartamento", "idsgdepartamento", "departamento", $_1_u_evento_idsgdepartamento) : "";
						?>
						<input id="sgdepartamentoobj" name="<?=$nameIdSgDepartamento?>" class="ui-autocomplete-input form-control sgdepartamentoobj" type="text" cbvalue="<?=$_1_u_evento_idsgdepartamento?>" placeholder="Selecione" value="<?=$valueSgDepartamento?>" <?=$obrigatorio ?>>
					<?
					} // Pegar setores por empresa
					elseif ((!empty($eventoTipo['setor_idempresa']) and $value['col'] == 'idsgsetor') or (!empty($_1_u_evento_idsgsetor) and $value['col'] == 'idsgsetor')) {
						if(!empty($_1_u_evento_idsgsetor)) {
							$setores = SgSetorController::buscarPorChavePrimaria($_1_u_evento_idsgsetor, 'ATIVO', true);
						} else {
							$setores = SgSetorController::buscarSetoresPorIdEmpresa($eventoTipo['setor_idempresa'], true);
						}

						$idempresaSetor = traduzid("sgsetor", "idsgsetor", "idempresa", $_1_u_evento_idsgsetor);
					?>
						<input id="sgsetorobj" name="<?=$nameIdSgSetor?>" class="ui-autocomplete-input form-control sgsetorobj" type="text" cbvalue="<?=$_1_u_evento_idsgsetor ?>" placeholder="Selecione" value="<?= traduzid("empresa", "idempresa", "sigla", $idempresaSetor) ?> <?= traduzid("sgsetor", "idsgsetor", "setor", $_1_u_evento_idsgsetor) ?>" <?=$obrigatorio ?>>
					<?
					}
				} elseif ($value['col'] == 'inicio') {
					if($eventoTipo['prazo'] == 'N') {
					?>
						<input id="data-inicio" name="_1_<?=$_acao ?>_evento_inicio" class="form-control hidden" type="text" autocomplete="off" value="<?=$_1_u_evento_inicio ? $_1_u_evento_inicio : date('d/m/Y') ?>" <?=$obrigatorio ?>>
						<?
						if(empty($_1_u_evento_iniciohms)) {
							$ehora = date("H");
							$eminuto = date("i");
							if($eminuto > 1 and $eminuto < 16) {
								$smin = '15';
							} elseif ($eminuto < 31) {
								$smin = '30';
							} elseif ($eminuto < 46) {
								$smin = '45';
							} else {
								$ehora = str_pad((intval($ehora) + 1), 2, '0', STR_PAD_LEFT);

								if($ehora > 24)
									$ehora = '00';

								$smin = '00';
							}
							$_1_u_evento_iniciohms = $ehora.":".$smin.":00";
						}

						$p_str_iniciohms = substr($_1_u_evento_iniciohms, 0, -3);
						foreach ($arrTime as $value) {
							if($p_str_iniciohms == $value['value']) {
								$str_iniciohms = $value['label'];
							}
						}

						if(!empty($_1_u_evento_inicio)) {
							//Alterado o campo status para mostrar a barra de progresso de status do prazo - Lidiane (13-02-2020)
							//Início
							?>
							<div class="w-100 flex-wrap">
								<div class="w-100 d-flex flex-wrap align-items-center justify-content-center justify-content-md-start">
									<?
									if(empty($_1_u_evento_duracaohms)) {
										$_1_u_evento_duracaohms = '00:15:00';
									}
									
									$p_str_dur = substr($_1_u_evento_duracaohms, 0, -3);
									foreach ($arrDuracaoTempo as $value) {
										if($p_str_dur == $value['value']) {
											$str_dur = $value['label'];
										}
									}
									?>
									<div class="<?=$_1_u_evento_mostradata; ?> d-inline me-3 col-xs-12 col-md-auto" style="border-radius:4px;<?=$st; ?>;text-align:center">
										<div style="background-image:none;box-shadow: inset 0px 0px 0px 1px <?=$coricone; ?>;font-weight:bold; box-sizing: border-box;font-size:9px;margin: auto; padding: 1px 8px; border-radius: 8PX;font-style:italic;margin-top:5px;" class="calendariotimeevento col-xs-12">
											<i class="fa fa-calendar" style="font-size: 14px; line-height: 11px; margin-right: 2px; padding: 2px; "></i>
											<?= explode(' ', $dataTarefa)[0] ?>
										</div>
									</div>
									<div class="relative d-inline"> 
										<input name="_1_<?=$_acao ?>_evento_iniciohms" type="search" id="iniciohms" class="input-time-default" value="<?=$_1_u_evento_iniciohms ? date('H:i', strtotime($_1_u_evento_iniciohms)) : $_1_u_evento_iniciohms ?>" placeholder="HH:MM" autocomplete="off" <?=$obrigatorio ?> />
										<ul id="lista-horario-inicio" class="w-100 lista-select" data-target="iniciohms"></ul>
									</div>
									<span class="mx-3">Até</span>
									<div class="relative d-flex align-items-center">
										<input id="duracaohms" name="_1_<?=$_acao ?>_evento_duracaohms" type="search" placeholder="HH:MM" <?= (!$_1_u_evento_duracaohms || $_1_u_evento_diainteiro == 'Y') ? 'disabled' : '' ?> autocomplete="off" class="input-time-default" cbvalue=<?=$_1_u_evento_duracaohms ?>>
										<ul id="lista-horario-duracao" class="w-100 lista-select" data-target="duracaohms"></ul>
										<? if($_1_u_evento_diainteiro == 'Y') {
											$diainteiro = "checked='checked'";
										} else {
											$diainteiro = '';
										} ?>
										<label class="mx-3">Até o fim do dia</label>
										<input id="diainteiro" class="m-0" name="_1_<?=$_acao ?>_evento_diainteiro" type="checkbox" <?=$diainteiro ?> onclick="desabilitaDuracao()">
									</div>
								</div>
							</div>
						<?
						}
					}
				} elseif ($value['col'] == 'prazo') {

					if($_1_u_evento_prazo == '' && $eventoTipo['sla'] == 'N') {
						?>
						<input name="_1_<?=$_acao ?>_evento_<?=$value['col'] ?>" type="text" class="calendario form-control" <?=$obrigatorio ?> autocomplete="off">
						<?
					} else {
						date_default_timezone_set('America/Sao_Paulo');
						$prazo = explode("/", $_1_u_evento_prazo);
						$prazo = $prazo[2].'-'.$prazo[1].'-'.$prazo[0];

						if((!empty($_1_u_evento_prazo) && strtotime($prazo) >= strtotime(date('Y-m-d')))
							or (!empty($_1_u_evento_dataslaprazo) && strtotime($prazo) >= strtotime(date('Y-m-d H:m:s')) && $eventoTipo['sla'] == 'Y')
							or (!empty($_1_u_evento_inicio) && strtotime($_1_u_evento_inicio) >= strtotime(date('Y-m-d H:m:s')))
							or ($_1_u_evento_posicao == 'CANCELADO' or $_1_u_evento_posicao == 'FIM' or $_1_u_evento_posicao == 'CONCLUIDO')
						) {
							$colorprazo = '#666;';
						} else {
							$colorprazo = '#DC143C;';
						}

						if($eventoTipo['sla'] == 'Y') {
							if($_1_u_evento_posicao == 'CANCELADO' or $_1_u_evento_posicao == 'FIM' or $_1_u_evento_posicao == 'CONCLUIDO') {
								echo 'Concluído';
							} else {
								if(strpos($_1_u_evento_datasla, '-') !== false) {
									echo '<i style="background:#ac202e;color:#fff;padding:3px;border-radius:8px;font-size:10px;">Vencido</i>';
								} else {
									?>
									<div class="col-md-3 col-xs-12 prazo" style="font-size: 10px; color: #FFFFFF;">
										<div class="hrefs" style="color: #FFFFFF;border-radius:15px;width:100%;text-transform:uppercase;background:<?=$colorprazo ?>; 2px 25px 0px 30px; font-size:10px;word-break:normal;text-align:center;">
											<div class="<?=$_1_u_evento_mostradata; ?>">
												<p class="sla" value="<?=$_1_u_evento_prazo; ?>" name="novoprazo" style="margin:0; !important">
													<?= dma($_1_u_evento_datasla); ?>
												</p>
											</div>
										</div>
									</div>
								<?
								}
							}
						} else {
							if($eventoTipo['prazo'] == 'Y') {
								if($_1_u_evento_repetirevento == 'Y') {
									$disabledPrazo = "disabled='disabled'";
								} else {
									$disabledPrazo = "";
								}
								if($_1_u_evento_repetirevento == 'Y') {
								?>
									<div class="col-sm-4 col-xs-12 text-center" style="<?=$st; ?>;">
										<div class="w-100" style="background-image:none;box-shadow: inset 0px 0px 0px 1px <?=$coricone; ?>;font-weight:bold;font-size:9px; padding: 1px 8px; border-radius: 8PX;font-style:italic;margin-top:5px;" class="calendariotime">
											<i class="fa fa-calendar" style="font-size: 14px; line-height: 11px; margin-right: 2px; padding: 2px; "></i>
											<?=$dataTarefa ?>
										</div>
									</div>
									<input class="calendario calendarioprazo form-control" name="_1_<?=$_acao ?>_evento_prazo" type="hidden" autocomplete="off" value="<?=$_1_u_evento_prazo ?>" <?=$obrigatorio ?>>
								<?
								} else {
								?>
									<input class="calendario form-control" name="_1_<?=$_acao ?>_evento_prazo" type="hidden" autocomplete="off" value="<?=$_1_u_evento_prazo ?>" <?=$obrigatorio ?> <?=$disabledPrazo ?>>
									<? //Alterado o campo status para mostrar a barra de progresso de status do prazo - Lidiane (13-02-2020) - Início 
									?>
									<div class="col-lg-12 col-xs-12 dataAlerta" style="font-size: 11px;margin:0;padding:0">
										<div class="col-sm-3 col-xs-12 prazo" style="font-size: 10px; color: #FFFFFF;">
											<div class="hrefs" style="border-radius:15px;width:100%;text-transform:uppercase;background:<?=$colorprazo ?>; 2px 25px 0px 30px; font-size:10px;word-break:normal;text-align:<?=$textalign ?>;">
												<? if($_1_u_evento_sla == 'Y' and ($_1_u_evento_posicao != 'CANCELADO' or $_1_u_evento_posicao != 'FIM' or $_1_u_evento_posicao != 'CONCLUIDO')) { ?>
													<p class="calendariotime" value="<?=$_1_u_evento_prazo; ?>" name="novoprazo" style="text-align: center;">
														<?= dma($dataTarefa); ?>
													</p>
												<? } else { ?>
													<p class="calendarioprazo" value="<?=$_1_u_evento_prazo; ?>" data-idevento="<?=$_1_u_evento_idevento ?>" name="novoprazo" style="text-align: center;">
														<?= dma($dataTarefa); ?>
													</p>
												<? } ?>
											</div>
										</div>
									</div>
									<!-- Fim -->
								<?
								}
							}
						}
					}
				} elseif ($value['datatype'] == 'time') {
					?>
					<input class="form-control" name="_1_<?=$_acao ?>_evento_<?=$value['col'] ?>" type="time" value="<?=$fvar ?>">
					<?
				} elseif($value['col'] == 'checklist') {
					$checklist = EventoController::buscarChecklistPorIdEvento($idevento);

					if(!count($checklist)) { ?>
						<span id="insert-checklist" class="btn btn-primary btn-xs">
							<i class="fa fa-plus"></i>
						</span>
					<? } else { ?>
						<!-- CSS -->
						<link rel="stylesheet" href="./../form/css/checklist.min.css?version=1.5" />
						<? foreach ($checklist as $checklistValue) {

							$checklistItems = EventoController::buscarCheckListItemPorIdEventoCheckList($checklistValue['ideventochecklist']);
							if(!count($checklistItems) && $value['code'] && $_1_u_evento_idevento) {
								$rescheck = d::b()->query($value['code']);
								$field = mysqli_fetch_fields($rescheck);
								while ($rowcheck = mysqli_fetch_assoc($rescheck)) {
									$dados = array();
									$dados['ideventochecklist'] = $checklistValue['ideventochecklist'];
									$dados['idempresa'] = cb::idempresa();
									$dados['idevento'] = $_1_u_evento_idevento;
									$dados['titulo'] = $rowcheck[$field[1]->name];
									$dados['checked'] = 'N';
									EventoController::inserirEventoChecklistItem($dados);
								}
								$checklistItems = EventoController::buscarCheckListItemPorIdEventoCheckList($checklistValue['ideventochecklist']);
							}
							?>
							<div class="checklist" data-ideventochecklist=<?=$checklistValue['ideventochecklist'] ?> data-idempresa="<?=$checklistValue['idempresa'] ?>" data-obrigatorio="<?=$obrigatorio ? 'true' : 'false' ?>">
								<div class="checklist-progress">
									<span>0%</span>
									<div class="checklist-progress-bar"></div>
								</div>
								<div class="checklist-group">
									<? foreach ($checklistItems as $checklistItem) { ?>
										<div class="checklist-item checklist-item-<?=$checklistItem['ideventochecklistitem'] ?>">
											<div>
												<input id="item-<?=$checklistItem['ideventochecklistitem'] ?>" type="checkbox" type="checkbox" value="Y" <?=$checklistItem['checked'] == 'Y' ? 'checked' : '' ?> />
												<label for="item-<?=$checklistItem['ideventochecklistitem'] ?>">
													<i class="fa fa-check text-gray-10"></i>
												</label>
											</div>
											<div class="checklist-item-text">
												<span><?=$checklistItem['titulo'] ?></span>
											</div>
											<div class="checklist-item-edit">
												<textarea name="" id=""><?=$checklistItem['titulo'] ?></textarea>
												<button class="mt-2 btn btn-success btn-xs checklist-save">Salvar</button>
												<button class="mt-2 btn btn-secondary btn-xs checklist-cancel-edit">Cancelar</button>
											</div>
											<i class="fa fa-trash checklist-remove" data-idchecklistitem="<?=$checklistItem['ideventochecklistitem'] ?>"></i>
										</div>
									<? } ?>
								</div>
								<div class="checklist-input">
									<textarea type="text" placeholder="Adicionar um item" class="default"></textarea>
									<button class="btn btn-primary btn-xs habilitar-campo">
										Adicionar item
									</button>
								</div>
								<div class="checklist-controls">
									<button class="btn btn-primary btn-xs adicionar ml-2">Adicionar</button>
									<button class="btn btn-secondary btn-xs text-gray-100 cancelar">Cancelar</button>
								</div>
							</div>
						<? } 
					} 
				} else {
					if($value['datatype'] == 'date') {
						$class = 'calendario form-control';
					} elseif ($value['datatype'] == 'datetime') {
						$class = 'calendariodatahora form-control';
						//Alterção realizada para aumentar o tamanho dos novos inputs textocurto (03-02-2020 - Lidiane)
					} elseif ($value['col'] == 'evento' || $value['col'] == 'textocurto1' || $value['col'] == 'textocurto2' || $value['col'] == 'textocurto3' || $value['col'] == 'textocurto4' || $value['col'] == 'textocurto5' || $value['col'] == 'textocurto6'
					|| $value['col'] == 'textocurto7' || $value['col'] == 'textocurto8' || $value['col'] == 'textocurto9' || $value['col'] == 'textocurto10' || $value['col'] == 'textocurto11' || $value['col'] == 'textocurto12' || $value['col'] == 'textocurto13'
					|| $value['col'] == 'textocurto14' || $value['col'] == 'textocurto15') {
						$class = '';
					} else {
						$class = '';
					}
					?>
					<input class="<?=$class ?> form-control" rotulo="<?=$value['rotulo'] ?? $value['col'] ?>" name="_1_<?=$_acao ?>_evento_<?=$value['col'] ?>" type="text" value="<?=$fvar ?>" <?=$obrigatorio ?>>
					<?
				}
				?>
			</div>
			<?
		} // if($value['col']!='idequipamento' or $_acao!='i')
	} //foreach($camposVisiveis as $ord => $value)
}

require_once(__DIR__."/js/evento_js.php");
?>
