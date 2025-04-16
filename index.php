<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	//Evitar cache temporariamente: atinge somente a index
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	//Evitar indexação pelo google
	header("X-Robots-Tag: noindex");

	class Init
	{
		public $_modulo; /* modulo */
		public $_autoloadurl = "N"; /*  */
		public $_autoloadclasses;
		public $_acaoclasses;
		public $_logado; /*  */
		public $_empresa_layout; /* css, favicon, footer customizado */
		public $is_external; /* se a chamada é sislaudo ou resultados */

		public function __construct()
		{
			require_once "inc/php/functions.php";
			validatoken();
			$this->_modulo = $_GET['_modulo'];
			$this->_logado = logado();
			$this->isExternal();
		}
		
		/* Chekin de login, logout, verificação de token jwt */
		public function checkLogin()
		{
			if($_SESSION["SESSAO"]["LOGADO"] && !getModsUsr("LPS"))
			{
				session_unset();
				unset($_SESSION);
				session_destroy();
			}

			if (_SITEOUT===true)
			{
				header("Location: out/");
			} 
			elseif($_GET["_acao"]=="logout")
			{
				//Limpa sessão
				session_start();
				session_unset();

				unset($_SESSION);
				session_destroy();

				//Maf: limpa a session, para evitar que requisicoes ajax reloguem o usuario via XHR
				setcookie(session_name(),'',0,'/');

				$_SESSION["SESSAO"]["LOGADO"] = false;
				$_SESSION["SESSAO"]["MSGLOGIN"] = "At&eacute; logo!";
			}
		}

		/* Arquivos de inicialização por dominio  */
		public function checkArqInit()
		{
			//Executar inicializacao controlada por dominio. Isto permite interromper o processamento neste ponto
			//@todo: realizar verificacao em outra fonte, para reduzir IO no disco
			$arq_init = _CARBON_ROOT."eventcode/dominio/".str_replace(".","_",$_SERVER["HTTP_HOST"])."__init.php";
			if(file_exists($arq_init))
			{
				include_once($arq_init);
			}
		}

		/* Verifica a url se o acesso é de resultados ou syislaudo */
		public function isExternal()
		{
			if($_SERVER['SERVER_NAME']==RESULTADOSURL)
			{
				return true;
			}
			return false;
		}

		/* Verifica se o modo do módulo é FORM ou URL(?) */
		public function modo()
		{
			//Verifica se entrara em modo de FORM, em caso de envio direto de acao e ids via GET
			if(($_GET["_acao"] == "u" || $_GET["_acao"] == "i") && $this->_modulo!="")
			{
				if (sizeof($_GET) <= 2 && $_GET["_acao"] == "u") {//modulo e acao enviados. falta ID
					die("Erro: Somente os parametros _modulo e _acao foram enviados. Nenhum parâmetro de ID encontrado.");
				}
				else
				{
					//Coloca o body em modo autoload. Ajustando a classe diretamente no body, evitará flickering por conta da animação em paralelo ao carregamento
					if ($this->_modulo != "_login" || $this->_modulo != "_cadastroext")
					{
						$this->_autoloadurl = "Y";
						$this->_autoloadclasses = "minimizado autoloadurl";
					}
				}
				
				if ($_GET["_acao"] == "i")
				{
					$this->_acaoclasses="novo";
				}
			}
		}
		
		/* Merge de folhas de estilo*/
		public function mergeCss($comprimeIncludes = false)
		{
			if(!$this->is_external)
			{
				echo mergeArquivos(
					$comprimeIncludes,
					"css",
					array(
						_CARBON_ROOT."/inc/css/bootstrap/css/bootstrap.min.css"
						,_CARBON_ROOT."/inc/css/fonts/laudo/laudofonts.css"
						,_CARBON_ROOT."/inc/css/fontawesome/font-awesome.min.css"
						,_CARBON_ROOT."/inc/snippetIcons/style.css"
						,_CARBON_ROOT."/inc/js/daterangepicker/daterangepicker.css"
						,_CARBON_ROOT."/inc/js/select2/select2.min.css"
						,_CARBON_ROOT."/inc/js/notifications/smart.css?version=1.0"
						,_CARBON_ROOT."/inc/css/bootstrap-toggle/bootstrap-toggle.css"
						,_CARBON_ROOT."/inc/js/webuipopover/jquery.webui-popover.css"
						,_CARBON_ROOT."/inc/js/bootstrap-select/bootstrap-select.css"
						,_CARBON_ROOT."/inc/js/jquery/jquery-ui.min.css"
						,_CARBON_ROOT."/inc/lbox/css/lightbox.css"
						//,_CARBON_ROOT."/inc/js/diagrama/Treant.css"
						,_CARBON_ROOT."/inc/js/colorpalette/css/bootstrap-colorpalette.css"
						// ,_CARBON_ROOT."/inc/fullcalendar/fullcalendar.min.css"
						,_CARBON_ROOT."/inc/css/carbon.css?version=2.7"
						,_CARBON_ROOT."/inc/css/toast.css"
						,_CARBON_ROOT."/inc/css/sislaudo.css"
						,_CARBON_ROOT."/inc/css/toastify.min.css"
						,_CARBON_ROOT."/inc/css/chat.css"
						// ,_CARBON_ROOT."/inc/fullcalendar/fullcalendar.print.min.css"
						,_CARBON_ROOT."/inc/css/mobile.css" //Sempre por último
						,_CARBON_ROOT."/inc/css/print.css?version=1.1" //Sempre por último
						/*,_CARBON_ROOT."/inc/js/jquery-filer/css/jquery.filer.css"*/
						,_CARBON_ROOT."/inc/css/models/fluxo.css"
						,(($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1) ? _CARBON_ROOT."/inc/css/core.min.css?version=4.0" : '')
						//,(!in_array($_SESSION['SESSAO']['IDTIPOPESSOA'], [6494, 111565, 107524, 1098, 98070, 778, 799, 8211, 111319, 798, 114994, 112378, 115227, 107822, 115410, 115414, 1944, 115748, 115703, 115819, 115412]) ? _CARBON_ROOT."/inc/css/modal_snippet_acao.min.css?version=1.4" : '')
					)
				);
			}
		}

		/* Merge de funções javascript */
		public function mergeJs($comprimeIncludes = false)
		{
			echo mergeArquivos(
				$comprimeIncludes,
				"js",
				array(
					_CARBON_ROOT."/inc/js/jquery/jquery-ui.js"
					,_CARBON_ROOT."/inc/js/jquery/jquery.autosize-min.js"
					,_CARBON_ROOT."/inc/css/bootstrap/js/bootstrap.min.js"
					,_CARBON_ROOT."/inc/js/moment/moment.min.js"
					,_CARBON_ROOT."/inc/js/htmlentities/he.js"
					,_CARBON_ROOT."/inc/js/daterangepicker/daterangepicker.js"
					,_CARBON_ROOT."/inc/js/notifications/smart.js"
					,_CARBON_ROOT."/inc/js/webuipopover/jquery.webui-popover.js"
					,_CARBON_ROOT."/inc/js/accent-fold.js"
					,_CARBON_ROOT."/inc/js/bootstrap-select/bootstrap-select.js"
					,_CARBON_ROOT."/inc/js/bootstrap-select/i18n/defaults-pt_BR.js"
					,_CARBON_ROOT."/inc/js/tinymce/tinymce.min.js"
					,_CARBON_ROOT."/inc/lbox/js/lightbox.js"
					,_CARBON_ROOT."/inc/js/diagrama/vendor/raphael.js"
					,_CARBON_ROOT."/inc/js/colorpalette/js/bootstrap-colorpalette.js"
					,_CARBON_ROOT."/inc/js/cookie/js.cookie.js"
					,_CARBON_ROOT."/inc/js/ping/ping.js"
					,_CARBON_ROOT."/inc/js/autosize/autosize.min.js"
					,_CARBON_ROOT."/inc/js/functions.js?version=4.1"
					,_CARBON_ROOT."/inc/js/carbon.js?version=1.7"
					,_CARBON_ROOT."/inc/js/bowser/es5.js"
					,_CARBON_ROOT."/inc/tmp/feriado.js"
					,_CARBON_ROOT."/inc/tmp/calendarioferiado.js"
					,_CARBON_ROOT."/inc/js/models/evento.js?version=1"
					,_CARBON_ROOT."/inc/js/toastify.min.js"
				)
			);
		}

		/* Impressão de footer para tela de login */
		public function printFooter()
		{
			if(!$this->_logado)
			{
				if(!$this->_empresa_layout['footer'])
				{
					echo '<div id="cbFooterLogin">';
						echo '<label>LAUDO LABORATÓRIO AVÍCOLA UBERLÂNDIA LTDA</label>';
						echo '<label>Rod. BR 365, km 615 - SNº</label>';
						echo '<label>B. Alvorada - Uberlândia/MG - CEP 38.407-180</label>';
						echo '<label>Tels.: (34) 3222 5700</label>';
						echo '<label>www.laudolab.com.br - resultados@laudolab.com.br</label>';
					echo '</div>';
				}
				else
				{
					echo $this->_empresa_layout['footer']['footer'];
				}
			}
		}

		/* Configuração de favicon da empresa (salvo em db)*/
		public function empresaFavicon()
		{
			if($this->_empresa_layout['favicon'])
			{
				echo '<link rel="shortcut icon" type="image/ico" href="./inc/img/favicon.ico"/>';
			}
			else
			{
				echo '<link rel="shortcut icon" type="image/ico" href="'.$this->_empresa_layout['favicon'].'"/>';
			}
		}

		/* Configuração de favicon da empresa (salvo em db)*/
		public function empresaCss()
		{
			if($this->_empresa_layout['css'])
			{
				echo $this->_empresa_layout['css'];
			}
		}

		/* Configuração de css de template da empresa (salvo em db) */
		public function empresaLayout()
		{
			$sql="SELECT footer,favicon,css from empresalayout where hostname='".$_SERVER['SERVER_NAME']."'";
			$this->_empresa_layout = d::b()->query($sql)->fetch_assoc();
		}

		public function linkStylesheet(string $link){
			echo '<link rel="stylesheet" href="'.$link.'" />';
		}

		/* Iniciar classe, rodar funções necessárias */
		public function run()
		{
			
			$this->checkLogin();
			$this->checkArqInit();
			$this->modo();
			$this->empresaLayout();
			$versaoSistema = $_SESSION["SESSAO"]["VERSAOSISTEMA"] ? "Versão sistema: ".$_SESSION["SESSAO"]["VERSAOSISTEMA"] : "";
			$classSomenteLeitura = getModsUsr("MODULOS")[$_GET["_modulo"]]["permissao"] == "r" ? "somenteleitura" : "";
			$this->_modulo = (empty($_GET["_modulo"]) && logado() == false) ? "_login" : $_GET["_modulo"];

			return [
				$this->_modulo,
				$this->isExternal(),
				$versaoSistema,
				$classSomenteLeitura,
				$this->_autoloadurl,
				$this->_autoloadclasses,
				$this->_acaoclasses,
				$this->_logado
			];
		}
	}

	$init = new Init();

	[
		$_modulo,
		$is_external,
		$versaoSistema,
		$classSomenteLeitura,
		$_autoloadurl,
		$_autoloadclasses,
		$_acaoclasses,
		$_logado,
		$_empresa_layout
	] = $init->run();

	try
	{
		// A condição abaixo deve ser temporária e de uso exclusivo para avaliação da nova tela de cliente em produção
		// (array_key_exists("novatelapet", getModsUsr("MODULOS")) && $_GET['telapet']=='Y')
		if(($is_external && $_modulo=='_login') || $_SESSION['SESSAO']['PESSOAPLANTEL'] == 34)
		{
			require_once './inc/templates/tailwind/tailwind.php';
		}
		else
		{
 			require_once './inc/templates/bootstrap/bootstrap.php';
		}
	} catch (\Throwable $th) {
		throw $th;
	}

