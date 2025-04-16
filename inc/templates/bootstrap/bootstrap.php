<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">
		<meta name="robots" content="noindex">
		<meta name="googlebot" content="noindex">
		<title id="cbTitle"><?=$_pagetitle?></title>

		<link rel="preconnect" href="https://aadcdn.msauth.net" crossorigin="">
		<meta http-equiv="x-dns-prefetch-control" content="on">
		<meta name="theme-color" content="#ff0000">
		<meta name="mobile-web-app-capable" content="yes">
		<link rel="dns-prefetch" href="//aadcdn.msauth.net">
		<link rel="dns-prefetch" href="//aadcdn.msftauth.net">
		<link rel="stylesheet" href="/inc/templates/bootstrap/css/index-bootstrap.css" />
		<link rel="manifest" href="manifest.php?idempresa=<?=cb::idempresa()?>">
		<?php
			$init->mergeCss(); 
			$init->empresaFavicon();
			$init->empresaCss();
		?>
		<!-- O Jquery e o outdatedbrowser devem estar fora do mergearquivos -->
		<script src="/inc/js/jquery/jquery-1.11.2.min.js"></script>
		<script src="/inc/templates/js/_stats.js"></script>
		<script src="/inc/templates/js/ieInvalido.js"></script>
		<script src="/inc/js/dropzone/dropzone.js"></script>
		
		
		<?php
		$init->mergeJs();
		
		if($_logado)
		{
			?>
			<!-- link href="./inc/js/diagrama/Treant.css?_<?#=date("dmYhms")?>" rel="stylesheet" -->

			<!-- Scripts Carbon -->
			<!-- script src="./inc/nodejs/notificacoes/notificacoes.php"></script -->
	
		

			<!-- MAF: o script de notificacoes já havia sido desativado: não reativar sem projeto -->
			<!-- script src="./notificacoes.php?_d<?#=date("dmYhms")?>"></script -->
			<!-- Forcar atualizacao dos scripts -->
			<!--<script src="./inc/js/functions.js?_d<?#=date("dmYhms")?>"></script>-->
			<link rel="stylesheet" href="/inc/css/dashboard.css" />

			<!-- script src="./inc/js/chat/chat.js"></script -->
			
			<script src="/inc/js/models/fluxo.js?version=2.2"></script>
			<script src="/inc/js/custom-filter-carbon/index.js"></script>
			<script src="/inc/js/models/share.js"></script>

			<?if($_SESSION["SESSAO"]["SUPERUSUARIO"] !== true AND $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1 AND FALSE){?>
				<script src="/inc/js/fetch-controller/fetch_controller.js"></script>
				<script src="/inc/js/notification-controller/index.js"></script>
			<?
			}
		}
/* 			if($_SESSION["SESSAO"]["IDPESSOA"])
			{
				$sqlAlerta="SELECT idsnippet FROM "._DBCARBON."._snippet WHERE notificacao = 'Y' " .getidempresa('idempresa','_snippet');
				$resAlerta=d::b()->query($sqlAlerta) or die("Erro ao Buscar _snippet: Erro: ".mysqli_error(d::b())."\n".$sqla);
				$rowAlerta=mysqli_fetch_assoc($resAlerta);
				$idsnippet = $rowAlerta['idsnippet'];
			} */
		?>
	<script>

		var gIdpessoa = "<?=$_SESSION["SESSAO"]["IDPESSOA"]?>";
		var gIdtipopessoa = "<?=$_SESSION["SESSAO"]["IDTIPOPESSOA"]?>";
		var gIdarquivoavatar = "<?=$_SESSION["SESSAO"]["IDARQUIVOAVATAR"]?>";
		var gPermissaochat = "N";//"<?=$_SESSION["SESSAO"]["PERMISSAOCHAT"]?>";
		var gSomNovaMensagem;
		var gChatMaxHistorico="<?=_CHAT_MAX_HISTORICO?>";
		var gVersaoSistema="<?=$versaoSistema?>";
		var gDbHost="<?=$_SERVER['SERVER_ADDR']?>";
		var gDbVersao="<?=d::b()->server_version?>";
		var gAppHost="<?=php_uname('n')?>";
		var gAppVersao="<?=phpversion()?>";
		var gCbCanal="<? echo empty($_headers["cb-canal"]) ? $_GET['cb-canal'] : $_headers["cb-canal"]?>";
		var gMatrizPermissoes = "<?=$_SESSION["SESSAO"]["MATRIZPERMISSOES"]?>";
		var gHabilitarMatriz = "<?=cb::habilitarMatriz();?>";
		var gIdEmpresa = "<?=cb::idempresa();?>";
		var gIdEmpresaModulo;
		//@487013 - MULTI EMPRESA
		CB.logado = <?=var_export($_logado)?>;
		CB.autoLoadUrl = "<?=$_autoloadurl?>";

		if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)){
			//mobile
			var isMobile = true;
		}else{
			//desktop
			var isMobile = false;
		}

		$(document).ready(function() {
		//Inicializa arquivos de audio
		gSomNovaMensagem = $("#cbSomNovaMensagem")[0];
		
		//Inicializa o framework
		CB.init();

		CB.modulo="<?=$_modulo?>";
		CB.inicializaModulo({
			posInit: function()
			{
				<?/* if($_SESSION["SESSAO"]["SUPERUSUARIO"] != true){?>
					montarChat=(CB.oBody.attr("menu")!=="N" && CB.oBody.attr("menu")!=="form");

					if (CB.v2 && CB.logado && gPermissaochat == "Y" && montarChat===true) {

						chat = new Chat();
						chat.init({
							notificacoes: "#cbNotificacoes", 
							badge: "#cbNotificacoes #cbBadge", 
							iBadge: "#cbNotificacoes #cbIBadge", 
							badgeTarefa: "#cbSnippet<?=$idsnippet?>", 
							iBadgeTarefa: "#cbIBadgeSnippet<?=$idsnippet?>", 
							listaNotificacoes: "#cbNotificacoes #cbListaNotificacoes", 
							listaNotificacoesHeader: "#cbNotificacoes #cbListaNotificacoesHeader", 
							listaNotificacoesFooter: "#cbNotificacoes #cbListaNotificacoesFooter", 
							chatContainer: "#cbChatContainer"
						});
						//Aguarda 5 segundos para inicializar o chat
						setTimeout('refresh();', 5000)
						//Ajusta o refresh para repetição infinita
						setInterval(function() {
							refresh("refresh");
						}, chat.intervaloRefresh);
					}
				<?} */?>

				if(parseInt(gIdtipopessoa) == 1)
					eventoListenerFiltrarModulos();
			}
		});

		<?php
			//Valida se está configurada os Fluxo no fluxo
			if(!empty($thread_id))
			{
				echo "console.log('MySQL Tread Id: ".$thread_id."')";
			}
		?>

		});
		//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
		//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>
	</script>

<?php
	if(!empty($_SESSION["SESSAO"]["CUSTOMCSS"])){
		echo "<!-- CSS Customizado para a LP --><style>{$_SESSION["SESSAO"]["CUSTOMCSS"]}</style>";
	}

	//Ajusta logo para usuários logados
	$cssLogado = var_export($_logado, true);

	//Verifica utilização de recurso "Super Usuário"
	if($_SESSION["SESSAO"]["SUPERUSUARIO"]===true){
		$cssSuperUsuario = "superusuario";
	}

	//Verifica uso de webview
	if($_headers["cb-canal"]=="webview"){
		$cssWebview="webview";
		$cssPlat=$_headers["cb-plat"];
	}

	$classeCbContainer = 'container-fluid';
	$classeMobile = "";

	if (
		($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1) &&
		(!$_GET['_modulo'] || ($_GET['_modulo'] != 'menulateralapp' && $_GET['_modulo'] != 'modalsnippetacaoapp'))
	)
	{
		$classeMobile = "mobile";
	}
?>

</head>
<body class="<?=$classeMobile." ".$_autoloadclasses." ".$_acaoclasses." ".$cssSuperUsuario." ".$classSomenteLeitura." ".$cssWebview." ".$cssPlat?>" menu="<?=$_GET["_menu"]?>" modo="<?=$_GET["_modo"]?>">
	<audio id="cbSomNovaMensagem">
		<source src="sound/novaMensagem2.mp3"></source>
		<source src="sound/novaMensagem2.ogg"></source>
	</audio>

	<link href="<?= "/form/css/index_css.css?_".date('dmYhms') ?>" rel="stylesheet" />

	<style>
		._btnAssinatura, button.btn.btn-default.fa.fa-edit.hoverlaranja.pointer {
			display: <?=(getModsUsr("MODULOS")["_btnAssinatura"]["permissao"])?'block !important':'none !important';?>;
		}
	</style>
	<?
		/* pelo tipo de usuario, devinir o navbar e css */
		if($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1)
		{
			echo '<link href="./inc/templates/css/tipopessoa-sislaudo.css" rel="stylesheet" />';
			if(!isset($_GET['_menu']) || $_GET['_menu'] == 'Y') { 
				require_once(_CARBON_ROOT."/form/components/_modal_snippet_acao.php");
				echo '<nav id="cbMenuSuperior" class="navbar navbar-light py-0"></nav>';
			}
		} 
		else 
		{
			echo '<link href="./inc/templates/css/tipopessoa-cliente.css" rel="stylesheet" />';
			echo '<nav id="cbMenuSuperior" class="navbar navbar-default navbar-inverse navbar-static-top" role="navigation"></nav>';
		}
	?>
	<div class="row w-100 d-flex flex-nowrap m-0 relative">
		<div class="overlay"></div>
		<?
			if($_logado) {
				echo $_empresa_layout['css'];
				if($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1) {
					$classeCbContainer = '';
					if(!isset($_GET['_menu']) || $_GET['_menu'] == 'Y')
					{
						require_once(_CARBON_ROOT."/form/components/_sidebar.php");
					}
				}
			} 
		?>
		<div class="col-xs-12 p-0">
			<div id="cbContainer" class="<?= $classeCbContainer ?> ml-auto mw-100">
				<div class="row">
					<div id="cbModuloHeaderBg" class="hidden"></div>
					<div id="cbModuloHeader" class="col-md-12 hidden">
						<i id="cbModuloIcone" class=""></i>
						<span id="cbModuloBreadcrumb" href="">
							<span id="cbModuloBreadcrumbRotulo" class="h5" data-toggle="dropdown"></span>
							<ul id="cbModuloBreadcrumbOpcoes" class="dropdown-menu"></ul>
						</span>
						<button id="cbRep" type="button" class="btn btn-primary btn-xs disabled" onclick="CB.rep()" title="Extrair Relatórios" style="display:none">
							<i class="fa fa-bar-chart"></i>
						</button>
						<?
						if(getModsUsr("MODULOS")["restaurar"]["permissao"]=='w'){
						?>
						<button id="cbRestaurar" type="button" class="btn btn-primary btn-xs hidden" onclick="CB.restaurar()" title="Restaurar">
							<i class="fa fa-arrow-circle-left"></i>Restaurar
						</button>
						<?
						}
						if(cb::habilitarMatriz() == 'Y'){?>
							<button id="cbNovo" type="button" class="btn btn-primary btn-xs disabled" onclick="CB.novo()" title="Novo item">
								<i class="fa fa-plus"></i>Novo
							</button>
						<?}else{?>
							<button id="cbNovo" type="button" class="btn btn-primary btn-xs disabled" onclick="CB.novo(<?=cb::idempresa()?>)" title="Novo item">
								<i class="fa fa-plus"></i>Novo
							</button>
						<?}?>
						<button id="cbSalvar" type="button" class="btn btn-success btn-xs disabled" onclick="CB.post()" title="Salvar">
							<i class="fa fa-circle"></i>Salvar
						</button>
						<?
						//Após carregar totalmente a página, libera o ícone de Compartilhar, pois não estava carregando os contatos (Lidiane - 26-02-2020)
						if($_SESSION["SESSAO"]["SUPERUSUARIO"] != true){
						?>
						<i id="cbCompartilharItem" class="hide fa fa-comment-o fa-2x fade pointer hoverlaranja compartilhar hidden" title="Compartilhar este item" onclick="compartilharItem()"></i>
						<i id="cbCompartilharAlerta" class="fa fa-share-alt fa-2x fade pointer hoverlaranja compartilhar hidden" title="Criar Evento" onclick="CB.compartilharAlerta('carregaTipos')"></i>
						<?}?>
					</div>
				</div>
				<!-- separar modulos -->
				<div class="row" style="margin-right: 0px;margin-left: 0px;">
					<div id="cbModuloPesquisa" class="col-md-12 zeroauto hidden">
						
						<div id="cbFiltroRapido" class="btn-group hidden" role="group"></div>
						
						<div id="cbBarraPesquisa" class="input-group">
							<input id="cbTextoPesquisa" type="text" class="form-control" placeholder="Pesquisar..." value="">
							<span id="cbDaterange" cbdata="<? if ($_GET['_modulo'] == 'cliente_filtrarresultados'){ echo get_current_month_range();} ?>" class="input-group-addon pointer cinzaclaro hoverpreto">
								<i class="fa fa-calendar"></i>
								<span id="cbDaterangeTexto"><? if ($_GET['_modulo'] == 'cliente_filtrarresultados'){ echo get_current_month_range(1);} ?></span>
								<span id="cbCloseDaterange" class="fa fa-close hide"></span>
							</span>
							<div class="pointer dropdown" cbdatacol="" id="cbDaterangeCol">
								<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuDateCol" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<span class="fa fa-filter"></span><span id="cbDaterangeColText"></span>
								</button>
								<ul class="dropdown-menu" aria-labelledby="dropdownMenuDateCol"></ul>
							</div>
							<div class="input-group-btn">
								<button class="btn btn-default hidden" data-toggle="dropdown">
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu dropdown-menu-right" role="menu">
									<li><div>
										<form class="form-horizontal" role="form"></form>
									</div></li>
								</ul>
								
								<button id="cbPesquisar" class="btn btn-default btn-primary" onclick="CB.pesquisar({resetVarPesquisa:true})">
									<span class="fa fa-search"></span>
								</button>
								<button id="cbLimparFiltros" class="btn btn-outline-warning" onclick="CB.limparFiltros({resetVarPesquisa:true})">
									<span class="fa fa-close"></span> Limpar
								</button>
								<? 

								//Mostra a Impressora de Relatórios somente para os clientes
								$modimp = array("contatomenurapido","dashboardcliente","cliente_filtrarresultados","_login"); 
								if($_logado && in_array($_GET["_modulo"],$modimp)){
									$_mod = empty($_GET["_modulo"]) ? modInicial() : $_GET["_modulo"];
									
									$arrRel = getRelatorios($_mod);
									
									$arrModuloc = retArrModuloConf($_mod);
									if ($arrModuloc['btimprimirconf'] == 'Y' || $arrRel){?>
									
										<span class="dropdown" style=" margin-left:12px">
											<button class="btn btn-info dropdown-toggle  btn-primary" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
												<span class="fa fa-print"></span>
												<span class="caret"></span>
											</button>
											<ul class="dropdown-menu" aria-labelledby="dropdownMenu1" style="color:#898989; font-size:11px;text-transform:uppercase;text-transform: uppercase;
												margin-top: 17px; left: -120px;">
												<!--  <li style="border-bottom:1px solid #ccc; padding: 2px 0px; "><a href="#" data-value="action" style="color:#898989 !important;" onclick="CB.prepararRelatorio({resetVarPesquisa:true});">IMPRIMIR</a></li> -->
												<? if ($arrModuloc['btimprimirconf'] == 'Y'){ ?>
													<li style="border-bottom:1px solid #ccc; padding: 2px 0px; "><a href="#" data-value="action" style="color:#898989 !important;" onclick="CB.imprimirResultados();">IMPRIMIR</a></li>
												
												<?}
												foreach($arrRel as $number => $number_array){	
													$link = 'CB.prepararRelatorio({resetVarPesquisa:true}, \''.strip_tags($number_array['url']).'?_modulo='.$_modulo.'&_idrep='.$number.'\')';
													echo '<li style="padding: 2px 0px;"><a href="javascript:void(0)" onclick="'.$link.'"  data-value="another action" style="color:#898989 !important;">'.$number_array['rep'].'</a></li>';
												}?>						
											</ul>
										</span>
									<?}
								}?>
							</div>
						</div>
						<i id="cbIconePesquisando" class="fa fa-spinner fa-pulse azul hidden"></i>
					</div>
					<div id="cbModuloResultados" class="col-md-12 panel panel-default hidden"></div>
					<div id="cbModuloForm" class="hidden"></div>
				</div>
			</div>
		</div>
	</div>

	<?php require_once(_CARBON_ROOT."/form/components/_cbmodal.php"); ?>

	<div id="cbPanelLegenda" class="panel panel-default fixedBottomRight shadowRight hidden" style="margin-right: 15px;" data-toggle="collapse" data-target="#cbPanelLegendaBody">
		<div class="panel-heading">
			<label>Legenda</label>
			<i id="cbLegendaFechar" title="Fechar legenda" class="fa fa-chevron-down pull-right" onclick="CB.setPrefUsuario('u',CB.modulo+'.legenda','N');"></i>
			<i id="cbLegendaAbrir" title="Mostrar legenda" class="fa fa-chevron-up pull-right" onclick="CB.setPrefUsuario('d',CB.modulo+'.legenda');"></i>
		</div>
		<div id="cbPanelLegendaBody" class="panel-body collapse in">
			
		</div>
	</div>

	<div id="cbPanelBotaoFlutuante" class="panel panel-default fixedBottomRight shadowRight hidden" style="margin-right: 15px; z-index: 901; margin-bottom: 10px; height: 80%; width: 33%; overflow-y: scroll; overflow-x: hidden;">
		<div class="panel-heading">
			<div id="descricaobotao">
				<i id="cbPanelBotaoFlutuanteFechar" data-toggle="collapse" href="#cbPanelBotaoFlutuanteBody" title="Fechar" class="fa fa-chevron-down pull-right" onclick="CB.setPrefUsuario('u',CB.modulo+'.botaoflutuante','N'); alteraAltura('N');"></i>
				<i id="cbPanelBotaoFlutuanteAbrir" data-toggle="collapse" href="#cbPanelBotaoFlutuanteBody" title="Mostrar" class="fa fa-chevron-up pull-right" onclick="CB.setPrefUsuario('u',CB.modulo+'.botaoflutuante','Y'); alteraAltura('Y');"></i>
			</div>
		</div>
		<div id="cbPanelBotaoFlutuanteBody" class="panel-body collapse in">
			
		</div>
	</div>

	<div id="cbDropzone" class="hidden">
		<div class="dz-preview dz-file-preview ">
		<div class="dz-details ">
			<div class=" col-sm-6">
				<div class="dz-filename " ><span data-dz-name></span></div>
				<div class="dz-size display: table-cell" data-dz-size></div>
			</div>
			<div  style="margin-bottom: 1em; display: table-cell; text-transform: lowercase;"class="col-sm-3"  data-dz-criadopor></div>
			<div  style="margin-bottom: 1em; display: table-cell;"  data-dz-data></div>
			<img data-dz-thumbnail />
		</div>
		<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
		<div class="dz-success-mark hidden"><i class="fa fa-check"></i></div>
		<div class="dz-error-mark hidden"><i class="fa fa-remove"></i></div>
		<div class="dz-error-message hidden"><span data-dz-errormessage></span></div>
		</div>
	</div>

	<div id="cbCarregando" class="hideprint"></div>

	<?php
		$init->printFooter();

		if($_logado) require_once(_CARBON_ROOT."/form/js/index_js.php");

		if (verificaXDebug()) {
			echo '
				<div id="cbFooterDebug" title="Modo debug para desenvolvimento">
				<i class="fa fa-bug"></i>
				<!--<label>Modo Debug</label>-->
			</div>
			';
		}
	
	if($_logado && array_key_exists("edify", getModsUsr("MODULOS"))){ // and array_key_exists("edify", getModsUsr("MODULOS")?>
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link href="/inc/templates/edify/style.css" rel="stylesheet" type="text/css"/>
		<script type="module" src="/inc/templates/edify/dist/bundle.js?version=1.0" ></script>
	<? } ?>
</body>
</html>