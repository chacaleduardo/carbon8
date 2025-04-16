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
	<title id="cbTitle"><?= $_pagetitle ?></title>
	<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

	<?
	echo mergeArquivos(
		$comprimeIncludes,
		"css",
		array(
			_CARBON_ROOT . "/inc/css/fonts/laudo/laudofonts.css",
			_CARBON_ROOT . "/inc/css/fontawesome/font-awesome.min.css",
			_CARBON_ROOT . "/inc/snippetIcons/style.css",
			_CARBON_ROOT . "/inc/js/daterangepicker/daterangepicker.css",
			_CARBON_ROOT . "/inc/js/select2/select2.min.css",
			_CARBON_ROOT . "/inc/js/notifications/smart.css?version=1.0",
			_CARBON_ROOT . "/inc/lbox/css/lightbox.css",
			_CARBON_ROOT . "/inc/css/mobile.css",
			_CARBON_ROOT . "/inc/css/print.css?version=1.1"
		)
	);
	?>

	<!-- O Jquery e o outdatedbrowser devem estar fora do mergearquivos -->
	<script src="/inc/js/jquery/jquery-1.11.2.min.js"></script>
	<script src="./inc/templates/js/_stats.js"></script>
	<script src="./inc/templates/js/ieInvalido.js"></script>
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link rel="stylesheet" href="/inc/templates/tailwind/css/index-tailwind.css?version=1.0" />
	<link rel="manifest" href="manifest.php?idempresa=<?=cb::idempresa()?>">
	<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
	<script src="./inc/js/dropzone/dropzone.js"></script>
	
	<?
	$init->mergeJs();

	if ($_logado) { ?>
		<!-- link href="./inc/js/diagrama/Treant.css?_<? #= date("dmYhms") 
														?>" rel="stylesheet" -->

		<!-- Scripts Carbon -->
		<!-- script src="./inc/nodejs/notificacoes/notificacoes.php"></script -->

		

		<!-- MAF: o script de notificacoes já havia sido desativado: não reativar sem projeto -->
		<!-- script src="./notificacoes.php?_d<? #= date("dmYhms") 
												?>"></script -->
		<!-- Forcar atualizacao do s scripts -->
		<!--<script src="./inc/js/functions.js?_d<? #= date("dmYhms") 
													?>"></script>-->
		<link rel="stylesheet" href="./inc/css/dashboard.css" />

		<!-- script src="./inc/js/chat/chat.js"></script -->

		<script src="./inc/js/models/fluxo.js?version=2.0"></script>
		<script src="./inc/js/custom-filter-carbon/index.js"></script>
		<script src="./inc/js/models/share.js"></script>
		<? if ($_SESSION["SESSAO"]["SUPERUSUARIO"] !== true and $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1) { ?>
			<script src="./inc/js/fetch-controller/fetch_controller.js"></script>
			<script src="./inc/js/notification-controller/index.js"></script>
		<? } ?>
	<? } ?>
	<?
	if ($_SESSION["SESSAO"]["IDPESSOA"]) {
		$sqlAlerta = "SELECT idsnippet FROM " . _DBCARBON . "._snippet WHERE notificacao = 'Y' " . getidempresa('idempresa', '_snippet');
		$resAlerta = d::b()->query($sqlAlerta) or die("Erro ao Buscar _snippet: Erro: " . mysqli_error(d::b()) . "\n" . $sqla);
		$rowAlerta = mysqli_fetch_assoc($resAlerta);
		$idsnippet = $rowAlerta['idsnippet'];
	}
	?>

	<script>
		var gIdpessoa = "<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>";
		var gIdtipopessoa = "<?= $_SESSION["SESSAO"]["IDTIPOPESSOA"] ?>";
		var gIdarquivoavatar = "<?= $_SESSION["SESSAO"]["IDARQUIVOAVATAR"] ?>";
		var gPermissaochat = "N"; //"<?= $_SESSION["SESSAO"]["PERMISSAOCHAT"] ?>";
		var gSomNovaMensagem;
		var gChatMaxHistorico = "<?= _CHAT_MAX_HISTORICO ?>";
		var gVersaoSistema = "<?= $_SESSION["SESSAO"]["VERSAOSISTEMA"] ? $_SESSION["SESSAO"]["VERSAOSISTEMA"] : "Não informado" ?>";
		var gDbHost = "<?= $_SERVER['SERVER_ADDR'] ?>";
		var gDbVersao = "<?= d::b()->server_version ?>";
		var gAppHost = "<?= php_uname('n') ?>";
		var gAppVersao = "<?= phpversion() ?>";
		var gCbCanal = "<? echo empty($_headers["cb-canal"]) ? $_GET['cb-canal'] : $_headers["cb-canal"] ?>";
		var gMatrizPermissoes = "<?= $_SESSION["SESSAO"]["MATRIZPERMISSOES"] ?>";
		var gHabilitarMatriz = "<?= cb::habilitarMatriz(); ?>";
		var gIdEmpresa = "<?= cb::idempresa(); ?>";
		var gIdEmpresaModulo;
		//@487013 - MULTI EMPRESA
		CB.logado = <?= var_export($_logado) ?>;
		CB.autoLoadUrl = "<?= $_autoloadurl ?>";

		if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
			//mobile
			var isMobile = true;
		} else {
			//desktop
			var isMobile = false;
		}

		$(document).ready(function() {
			//Inicializa arquivos de audio
			gSomNovaMensagem = $("#cbSomNovaMensagem")[0];

			//Inicializa o framework
			CB.init();

			CB.modulo = "<?= $_modulo ?>";
			CB.inicializaModulo({
				posInit: function() {
					<? if ($_SESSION["SESSAO"]["SUPERUSUARIO"] != true) { ?>
						montarChat = (CB.oBody.attr("menu") !== "N" && CB.oBody.attr("menu") !== "form");

						if (CB.v2 && CB.logado && gPermissaochat == "Y" && montarChat === true) {

							chat = new Chat();
							chat.init({
								notificacoes: "#cbNotificacoes",
								badge: "#cbNotificacoes #cbBadge",
								iBadge: "#cbNotificacoes #cbIBadge",
								badgeTarefa: "#cbSnippet<?= $idsnippet ?>",
								iBadgeTarefa: "#cbIBadgeSnippet<?= $idsnippet ?>",
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
					<? } ?>

					if (parseInt(gIdtipopessoa) == 1)
						eventoListenerFiltrarModulos();
				}
			});

			<? //Valida se está configurada os Fluxo no fluxo
			if (!empty($thread_id)) {
				echo "console.log('MySQL Tread Id: " . $thread_id . "')";
			}
			?>

		});

		if (ieInvalido()) {
			alert("Você está utilizando uma versão inválida do Internet Explorer.\n\nRecomendação: Utilize o Firefox ou Google Chrome!");
		}

		//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
		//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>

		//script imagem modal
	</script>

	<?
	if (!empty($_SESSION["SESSAO"]["CUSTOMCSS"])) {
		echo '<!-- CSS Customizado para a LP -->
			<style>
				{$_SESSION["SESSAO"]["CUSTOMCSS"]}
			</style>';
	}

	//Ajusta logo para usuários logados
	$cssLogado = var_export($_logado, true);

	//Verifica utilização de recurso "Super Usuário"
	if ($_SESSION["SESSAO"]["SUPERUSUARIO"] === true) {
		$cssSuperUsuario = "superusuario";
	}

	//Verifica uso de webview
	if ($_headers["cb-canal"] == "webview") {
		$cssWebview = "webview";
		$cssPlat = $_headers["cb-plat"];
	}

	$classeCbContainer = 'container-fluid';
	$classeMobile = "";

	if (
		($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1) &&
		(!$_GET['_modulo'] || ($_GET['_modulo'] != 'menulateralapp' && $_GET['_modulo'] != 'modalsnippetacaoapp'))
	) {
		$classeMobile = "mobile";
	}
	?>
	<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="roboto-regular tailwind flex flex-col justify-start w-full <?= $classeMobile . " " . $_autoloadclasses . " " . $_acaoclasses . " " . $cssSuperUsuario . " " . $classSomenteLeitura . " " . $cssWebview . " " . $cssPlat ?>" menu="<?= $_GET["_menu"] ?>" modo="<?= $_GET["_modo"] ?>">
	<audio id="cbSomNovaMensagem">
		<source src="sound/novaMensagem2.mp3">
		</source>
		<source src="sound/novaMensagem2.ogg">
		</source>
	</audio>

	<? #= $_empresa_layout['css']; 
	?>

	<style>
		._btnAssinatura {
			display: <? if (getModsUsr("MODULOS")["_btnAssinatura"]["permissao"]) {
							echo 'block !important';
						} else {
							echo 'none !important';
						} ?>;
		}

		button.btn.btn-default.fa.fa-edit.hoverlaranja.pointer {
			display: <? if (getModsUsr("MODULOS")["_btnAssinatura"]["permissao"]) {
							echo 'block !important';
						} else {
							echo 'none !important';
						} ?>;
		}
	</style>

	<? if ($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1) {
		if (!isset($_GET['_menu']) || $_GET['_menu'] == 'Y') {
			require_once(_CARBON_ROOT . "/form/components/_modal_snippet_acao.php"); ?>
			<nav id="cbMenuSuperior" class="navbar navbar-light py-0"></nav>
		<? } ?>
	<? } else { ?>
		<!--<nav id="cbMenuSuperior" class="navbar navbar-default navbar-inverse navbar-static-top" role="navigation"></nav>-->
		<nav id="cbMenuSuperior-tailwind" class="shadow-md shadow-gray-500 hidden" role="navigation"></nav>
	<? } ?>
	<? if ($_logado) { ?>
		<? if ($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1) {
			$classeCbContainer = '';
			if (!isset($_GET['_menu']) || $_GET['_menu'] == 'Y') {
				require_once(_CARBON_ROOT . "/form/components/_sidebar.php");
			}
		} ?>
	<? } ?>
	<div id="cbContainer" class="">
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
				if (getModsUsr("MODULOS")["restaurar"]["permissao"] == 'w') {
				?>
					<button id="cbRestaurar" type="button" class="btn btn-primary btn-xs hidden" onclick="CB.restaurar()" title="Restaurar">
						<i class="fa fa-arrow-circle-left"></i>Restaurar
					</button>
				<?
				}
				if (cb::habilitarMatriz() == 'Y') { ?>
					<button id="cbNovo" type="button" class="btn btn-primary btn-xs disabled" onclick="CB.novo()" title="Novo item">
						<i class="fa fa-plus"></i>Novo
					</button>
				<? } else { ?>
					<button id="cbNovo" type="button" class="btn btn-primary btn-xs disabled" onclick="CB.novo(<?= cb::idempresa() ?>)" title="Novo item">
						<i class="fa fa-plus"></i>Novo
					</button>
				<? } ?>
				<button id="cbSalvar" type="button" class="btn btn-success btn-xs disabled" onclick="CB.post()" title="Salvar">
					<i class="fa fa-circle"></i>Salvar
				</button>
				<?
				//Após carregar totalmente a página, libera o ícone de Compartilhar, pois não estava carregando os contatos (Lidiane - 26-02-2020)
				if ($_SESSION["SESSAO"]["SUPERUSUARIO"] != true) {
				?>
					<i id="cbCompartilharItem" class="hide fa fa-comment-o fa-2x fade pointer hoverlaranja compartilhar hidden" title="Compartilhar este item" onclick="compartilharItem()"></i>
					<i id="cbCompartilharAlerta" class="fa fa-share-alt fa-2x fade pointer hoverlaranja compartilhar hidden" title="Criar Evento" onclick="CB.compartilharAlerta('carregaTipos')"></i>
				<? } ?>
			</div>
		</div>
		<div class="row">
			<div id="cbModuloPesquisa" class="zeroauto hidden">

				<div id="cbFiltroRapido" class="btn-group hidden" role="group"></div>

				<div id="cbBarraPesquisa" class="input-group">
					<input id="cbTextoPesquisa" type="text" class="form-control" placeholder="Pesquisar..." value="">
					<span id="cbDaterange" cbdata="<? if ($_GET['_modulo'] == 'cliente_filtrarresultados') {
														echo get_current_month_range();
													} ?>" class="input-group-addon pointer cinzaclaro hoverpreto">
						<i class="fa fa-calendar"></i>
						<span id="cbDaterangeTexto"><? if ($_GET['_modulo'] == 'cliente_filtrarresultados') {
														echo get_current_month_range(1);
													} ?></span>
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
							<li>
								<div>
									<form class="form-horizontal" role="form"></form>
								</div>
							</li>
						</ul>

						<button id="cbPesquisar" class="btn btn-default btn-primary" onclick="CB.pesquisar({resetVarPesquisa:true})">
							<span class="fa fa-search"></span>
						</button>
						<button id="cbLimparFiltros" class="btn btn-outline-warning" onclick="CB.limparFiltros({resetVarPesquisa:true})">
							<span class="fa fa-close"></span> Limpar
						</button>
						<?

						//Mostra a Impressora de Relatórios somente para os clientes
						$modimp = array("contatomenurapido", "dashboardcliente", "cliente_filtrarresultados", "_login");
						if ($_logado && in_array($_GET["_modulo"], $modimp)) {
							$_mod = empty($_GET["_modulo"]) ? modInicial() : $_GET["_modulo"];

							$arrRel = getRelatorios($_mod);

							$arrModuloc = retArrModuloConf($_mod);
							if ($arrModuloc['btimprimirconf'] == 'Y' || $arrRel) { ?>

								<span class="dropdown" style=" margin-left:12px">
									<button class="btn btn-info dropdown-toggle  btn-primary" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
										<span class="fa fa-print"></span>
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu" aria-labelledby="dropdownMenu1" style="color:#898989; font-size:11px;text-transform:uppercase;text-transform: uppercase;
													margin-top: 17px; left: -120px;">
										<!--  <li style="border-bottom:1px solid #ccc; padding: 2px 0px; "><a href="#" data-value="action" style="color:#898989 !important;" onclick="CB.prepararRelatorio({resetVarPesquisa:true});">IMPRIMIR</a></li> -->
										<? if ($arrModuloc['btimprimirconf'] == 'Y') { ?>
											<li style="border-bottom:1px solid #ccc; padding: 2px 0px; "><a href="#" data-value="action" style="color:#898989 !important;" onclick="CB.imprimirResultados();">IMPRIMIR</a></li>

										<? }
										foreach ($arrRel as $number => $number_array) {
											$link = 'CB.prepararRelatorio({resetVarPesquisa:true}, \'' . strip_tags($number_array['url']) . '?_modulo=' . $_modulo . '&_idrep=' . $number . '\')';
											echo '<li style="padding: 2px 0px;"><a href="javascript:void(0)" onclick="' . $link . '"  data-value="another action" style="color:#898989 !important;">' . $number_array['rep'] . '</a></li>';
										} ?>
									</ul>
								</span>
						<? }
						} ?>
					</div>
				</div>
				<i id="cbIconePesquisando" class="fa fa-spinner fa-pulse azul hidden"></i>
			</div>
			<div id="cbModuloResultados" class="col-md-12 panel panel-default hidden"></div>
			<div id="cbModuloForm" class="hidden"></div>
		</div>
	</div>

	</div>

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
		<div id="cbPanelBotaoFlutuanteBody" class="panel-body collapse in"></div>
	</div>

	<div id="cbDropzone" class="hidden">
		<div class="dz-preview dz-file-preview ">
			<div class="dz-details ">
				<div class=" col-sm-6">
					<div class="dz-filename "><span data-dz-name></span></div>
					<div class="dz-size display: table-cell" data-dz-size></div>
				</div>
				<div style="margin-bottom: 1em; display: table-cell; text-transform: lowercase;" class="col-sm-3" data-dz-criadopor></div>
				<div style="margin-bottom: 1em; display: table-cell;" data-dz-data></div>
				<img data-dz-thumbnail />
			</div>
			<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
			<div class="dz-success-mark hidden"><i class="fa fa-check"></i></div>
			<div class="dz-error-mark hidden"><i class="fa fa-remove"></i></div>
			<div class="dz-error-message hidden"><span data-dz-errormessage></span></div>
		</div>
	</div>

	<div id="cbCarregando" class="hideprint"></div>

	<? $init->printFooter(); ?>

	<?
	if ($_logado) {
		echo '
				<link href="/inc/templates/edify/style.css" rel="stylesheet" type="text/css"></script>
				<script type="module" src="/inc/templates/edify/dist/bundle.js"></script>
				';
	}
	?>


	<script>
		innerMenu = () => {
			return `
				<div class="inner-menu relative flex h-full items-center justify-between">
					<div class="inner-menu-left relative flex h-full items-center justify-between"></div>
					<div class="inner-menu-center relative flex h-full items-center justify-between"></div>
					<div class="inner-menu-right relative flex h-full items-center justify-end"></div>
				</div>
			`;
		}

		snipetLogo = (logo) => {
			return `
				<div class="h-full d-flex flex items-center justify-center"><!--flex flex-1 items-center justify-center sm:items-stretch sm:justify-start-->
					<div class="h-full flex justify-center">
						<img class="p-3" src="/inc/img/laudo_2c6c0.png">
					</div>
				</div>
			`;
		}

		snipetDropdownUser = (snippet) => {
			return `
				<div class="snippet-dropdown relative inline-block text-left text-white" style="${snippet.style}">
					
					<button id="${snippet.id}" style="${snippet.style}" type="button"
						class="snippet align-center flex flex-row w-full h-full px-3 py-2 text-sm font-semibold text-white" aria-expanded="true" aria-haspopup="true">
						<span class="w-full mt-1 text-white">${snippet.texto}</span>
						<span class="material-icons text-white">arrow_drop_down</span>
					</button>

					<div id="dropmenu_${snippet.id}" class="relative ml-3 drop-menu hidden">
						<div class="absolute right-0 z-10 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="${snippet.id}" tabindex="-1">
							${snippet.menu.map((snipet, index)=>{return snipetDropdownUserItem(snipet, index);}).join('')}
						</div>
					</div>
				</div>
			`;
		}

		snipetDropdownUserItem = (item, key) => {
			return `
				<a href="${item.href?item.href:'#'}" 
				target="${item.target?item.target:''}" 
				onclick="${item.onclick?item.onclick:''}"  
				class="block px-4 py-2 text-sm text-gray-700" 
				role="menuitem" tabindex="-1" 
				id="user-menu-item-${key}">
					<i class="${item.icone}"></i> <span>${item.texto}</span>
				</a>
			`;
		}

		moduleLabel = () => {
			return `
				<h2 class="inner-menu-title">${CB.jsonModulo.rotulomenu}</h2>
			`;
		}

		montaJQmenuSuperior = (menu) => {
			if (menu == []) return;

			cbMenuSuperior = document.getElementById('cbMenuSuperior-tailwind');
			cbMenuSuperior.classList.remove('hidden');
			cbMenuSuperior.insertAdjacentHTML('beforeend', innerMenu());
			if (menu.alterar_empresa._url) {
				//console.log('montando menu superior');
				cbMenuSuperior.querySelector('.inner-menu .inner-menu-right').insertAdjacentHTML('beforeend', snipetLogo(menu.alterar_empresa._url));
				cbMenuSuperior.querySelector('.inner-menu .inner-menu-right').insertAdjacentHTML('beforeend', snipetDropdownUser(menu.snippets.dropdownUser1));
				cbMenuSuperior.querySelector('.inner-menu .inner-menu-left').insertAdjacentHTML('beforeend', moduleLabel());

				document.getElementById('dropdownUser1').closest('.snippet-dropdown').addEventListener('mouseover', (e) => {
					//console.log('mouseover');
					if (document.getElementById('dropmenu_dropdownUser1').classList.contains('hidden')) {
						document.getElementById('dropmenu_dropdownUser1').classList.remove('hidden');
					}
				});
				document.getElementById('dropdownUser1').closest('.snippet-dropdown').addEventListener('mouseleave', () => {
					//console.log('leave');
					document.getElementById('dropmenu_dropdownUser1').classList.add('hidden');
				});
			}
		}

		montaMenuCarbon = (lateral = true, superior = true, snippetAcao = true) => {
			if (getUrlParameter('_modulo') != "alterasenha") {

				let JQmenuSuperior = $("#cbMenuSuperior-tailwind"),
					JQmenuLateral = $('#sidebar'),
					JQmodalSnippetAcao = $('#modal-snippet'),
					JQcontainer = $('#cbContainer');
				JQcontainer.addClass('overflow-y-scroll mt-2');

				if (!lateral && !superior && !snippetAcao) {
					JQcontainer.addClass('pt-0 menu-lateral-hidden');
					return true;
				}

				//if(!JQmenuSuperior.get().length) JQcontainer.addClass('pt-0');
				//if(!JQmenuLateral.get().length) JQcontainer.addClass('menu-lateral-hidden w-100');

				if (JQmenuSuperior.hasClass('loading') || JQmenuSuperior.hasClass('carregado') ||
					JQmenuLateral.hasClass('loading') || JQmenuLateral.hasClass('carregado') ||
					JQmodalSnippetAcao.hasClass('loading') || JQmodalSnippetAcao.hasClass('carregado')
				) {
					return true;
				}

				if (lateral) JQmenuSuperior.addClass('loading');
				if (superior) JQmenuLateral.addClass('loading');
				if (snippetAcao) JQmodalSnippetAcao.addClass('loading');

				//if(!localStorage.menu)
				$.ajax({
					url: "ajax/_montamenu_tailwind.php",
					type: 'get',
					async: false,
					success: data => {
						let menu = {};

						try {
							menu = JSON.parse(data);
						} catch (e) {
							alertAtencao(e.message);
							alertErro(data);
							console.log(e.message);
						}

						if (menu.superior) {
							JQmenuSuperior.removeClass('loading').addClass('carregado');
							montaJQmenuSuperior(menu.superior);
						} else {
							JQcontainer.addClass('pt-0');
						}

						if (lateral) {
							if (menu.lateral) {
								JQmenuLateral
									.removeClass('loading')
									.addClass('carregado')
									.html(menu.lateral);
							}
						} else {
							JQcontainer.addClass('menu-lateral-hidden w-100');
						}

						if (snippetAcao) {
							if (menu.modalSnippetAcao) {
								JQmodalSnippetAcao
									.removeClass('loading')
									.addClass('carregado')
									.find('.modal-snippet-action-body')
									.html(menu.modalSnippetAcao);
							}
						} else {
							JQmodalSnippetAcao.addClass('hidden');
						}

						if (typeof NV === 'undefined') {
							return false;
						}

						// NV.init();
					},
					error: err => {
						console.log(err.message);
					}
				});
			}
		}

		sobreOSistema = () => {
			title = `<span class="flex"><i class="material-icons mr-3">info</i>Sobre o sistema:</span>`;
			body = `
				<p class="mb-5">
				Versão do sistema: ${gVersaoSistema} &nbsp;&nbsp;
				<a href='?_modulo=controleversao' target='_blank' class="text-blue-800 text-sm">Alterar vers&atilde;o</a>
				</p>
				<hr>
				<table class="mt-5 text-sm">
					<tr>
						<td class=""text-gray-600">Db host:</td>
						<td class=""pl-2 text-gray-600">${gVersaoSistema}/${gDbVersao}</td></tr>
					<tr>
						<td class=""text-gray-600">App host:</td>
						<td class=""pl-2 text-gray-600">${gAppHost}/${gAppVersao}</td>
					</tr>
				</table>
			`;
			modal(title, body);
		}

		modal = (title, body) => {
			modalTemplate = `
			<div class="modal relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
				<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
				<div class="fixed inset-0 z-10 w-screen overflow-y-auto">
					<div class="modal-overlay flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
						<div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg md:max-w-4xl">
							<div class="bg-white">
								<div class="flex flex-col">
									<div class="text-left bg-gray-200 p-3">
										<h3 class="text-base font-semibold leading-6 text-gray-900 flex
											flex-row justify-between align-middle" id="modal-title">
												${title}
											<i class="close material-icons cursor-pointer">close</i>
										</h3>
									</div>
									
									<div class="p-7">
										<p class="text-sm text-gray-500">${body}</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>`;
			document.querySelector('body').insertAdjacentHTML('beforeend', modalTemplate);
			document.querySelector('.modal').addEventListener('click', (e) => {
				if (e.target.classList.contains('modal-overlay'))
					e.target.closest('.modal').remove();
			});
			document.querySelector('.modal .close').addEventListener('click', (e) => {
				e.target.closest('.modal').remove();
			});
		}
		//# sourceURL=montaMenu
	</script>

	<script type="text/javascript">
		let delayHover;

		CB.on('posLoadUrl', function() {

			if (CB.logado) {
				let idempresa = getUrlParameter("_idempresa") || "<?= $_SESSION["SESSAO"]["IDEMPRESA"] ?>";
				if ($("#cbMenuSuperior").attr('idempresa') != idempresa) {
					montaMenuCarbon();
				}

				if (CB.jsonModulo.jsonpreferencias.botaoflutuante == 'N') {
					$("#cbPanelBotaoFlutuante").css("height", "auto");
					$("#cbPanelBotaoFlutuante").css("overflow-y", "hidden");
				}

				if ((parseInt(gIdtipopessoa) != 1)) {
					montaMenuCarbon(false, true, false);
				} else {
					montaMenuCarbon();
				}
			}
		});
		CB.on('posFecharForm', function() {

			if (CB.logado) {
				let _idempresa = sessionStorage.getItem('_idempresa');
				if (_idempresa) {
					window.history.pushState(null, window.document.title, "?_modulo=" + CB.modulo + "&_idempresa=" + _idempresa);
				}
				let idempresa = getUrlParameter("_idempresa") || "<?= $_SESSION["SESSAO"]["IDEMPRESA"] ?>";
				if ($("#cbMenuSuperior").attr('idempresa') != idempresa) {
					montaMenuCarbon();
				}
			}

		});

		function multiEmpresaModal(empobj) { //@487013 - MULTI EMPRESA

			var div = '';
			empobj.forEach((e, o) => {
				div += '<div class="col-md-3" style="text-align:center;"><img src="' + e.iconemodal + '" onclick="CB.novo(' + e.idempresa + ')" style="height:96px;width:96px;"></div>'
			});
			$("#cbModalTitulo").html("EMPRESA DESTINO");
			$("#cbModalCorpo").html('<div class="col-md-12">' + div + '</div>');
			$("#cbModal").modal("show");
		}

		function alteraAltura(mostrar) {
			if (mostrar == 'Y') {
				$("#cbPanelBotaoFlutuante").css("height", "80%").css("width", "33%").css("overflow-y", "scroll");
				$("#cbPanelBotaoFlutuanteFechar").show();
				$("#cbPanelBotaoFlutuanteAbrir").hide();
				$('#cbSalvarComentario').show();
			} else {
				$("#cbPanelBotaoFlutuante").css("height", "auto").css("width", "auto").css("overflow-y", "hidden");
				$("#cbPanelBotaoFlutuanteFechar").hide();
				$("#cbPanelBotaoFlutuanteAbrir").show();
				$('#cbSalvarComentario').hide();
			}
		}

		tailwind.config = {
			theme: {
				extend: {
					screens: {
						"3xl": "1600px",
						"4xl": "2000px",
						xs: "375px",
						sm: "430px",
					},
				},
			},
		};
		//# sourceURL=footer_rodape
	</script>
</body>

</html>