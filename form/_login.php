<?
require_once("../inc/php/functions.php");

if ($_SERVER['SERVER_NAME'] == RESULTADOSURL) {
	require_once('./_loginlaudo.php');
	die();
} else {
	//Necessario incluir manualmente na tela de login
	require_once(__DIR__ . "/controllers/bannerlogin_controller.php");

	//print_r($_POST);die;

	if (($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1 or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 16 or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 113) and !empty($_POST["idempresa"]) and !empty($_SESSION["SESSAO"]["USUARIO"]) and empty($_POST["usuario"])) {
		getEmpresaPessoa();
		// verifica se a empresa destino esta configurado para o usuário
		if (array_key_exists($_POST["idempresa"],  $_SESSION["SESSAO"]["STRIDEMPRESA"])) {
			echo '{"success":true}';
			die;
		} else {
			$arrResponse['success'] = false;
			$arrResponse['infos']['idempresa'] = $_POST["idempresa"];
			$arrResponse['infos']['acessos'] = $_SESSION["SESSAO"]["STRIDEMPRESA"];
			echo json_encode($arrResponse);
			die;
		}
	} else {

		if (!empty($_POST) and (empty($_POST["usuario"]) or empty($_POST["senha"])) and !verificaSuperUsuario($_POST["usuario"])) {
			cbSetPostHeader("0", "alert");
			die("Usuário ou Senha não informados corretamente!");
		} elseif ($_POST["usuario"]) {
			/*
		* Efetua procedimento de Login
		*/
			logincarbon($_POST["usuario"], $_POST["senha"]);
		}
	}
	if ($_SESSION["SESSAO"]["MSGLOGIN"]) {
		$_msglogin = "<div class='alert alert-warning' role='alert'>" . $_SESSION["SESSAO"]["MSGLOGIN"] . "</div>";
		session_destroy();
	}

	$banners = BannerLoginController::buscarBanners(date('Y-m-d'));
	$col = count($banners['desktop']) ? 'col-xs-12 col-lg-6' : 'col-xs-12';
	$colForm = count($banners['desktop']) ? 'col-xs-12 col-md-9 col-lg-8' : 'col-xs-12 col-sm-6 col-md-7 col-lg-4';

?>
	<style>
		@font-face {
			font-family: 'Roboto';
			src: url(/inc/fonts//roboto/Roboto-Light.ttf);
			font-weight: 300;
		}

		@font-face {
			font-family: 'Roboto';
			src: url(/inc/fonts//roboto/Roboto-Regular.ttf);
			font-weight: 400;
		}

		@font-face {
			font-family: 'Roboto';
			src: url(/inc/fonts//roboto/Roboto-Bold.ttf);
			font-weight: 600;
		}

		:root {
			--swiper-navigation-color: #ffff;
		}

		body {
			font-family: Roboto !important;
		}

		#cbMenuSuperior {
			display: none !important;
		}

		.esqueci {
			position: absolute;
			right: 0px;
			cursor: pointer;
			margin-top: 4px;
			opacity: 0.7;
		}

		.esqueci:hover {
			opacity: 1;
		}

		.aviso {
			margin: 20px 0px;
			width: 100%;
			-webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
			box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
		}

		.flex-col-reverse {
			flex-direction: column-reverse;
		}

		.main-block {
			height: 95vh;
		}

		.block-login,
		.block-img {
			background-size: 100% 100%;
			background-repeat: no-repeat;
		}

		.block-login {
			background-color: none;
		}

		.block-img {
			/* background-image: url(form/img/bg2-login.jpg); */
			background-position: center;
		}

		.block-swiper-login-mobile,
		.block-img {
			height: 224px !important;
		}

		.swiper {
			height: auto !important;
		}

		h1 {
			font-size: 55px !important;
		}

		h2 {
			font-size: 26px !important;
		}

		h5 {
			font-size: 18px !important;
		}

		.row {
			margin: 0 !important;
		}

		#cbContainer {
			width: 100% !important;
			margin: 0 !important;
			padding: 0 !important;
		}

		.font-bold {
			font-weight: 800;
		}

		.text-black {
			color: #212121 !important;
		}

		.btn-primary {
			background: #176292 !important;
			box-shadow: 0 3px 3px rgba(0, 0, 0, .25);
			border-radius: 3px !important;
		}

		.col-xs-1,
		.col-xs-10,
		.col-xs-11,
		.col-xs-12,
		.col-xs-2,
		.col-xs-3,
		.col-xs-4,
		.col-xs-5,
		.col-xs-6,
		.col-xs-7,
		.col-xs-8,
		.col-xs-9 {
			float: none !important;
		}

		.block-swiper-login {
			display: none !important;
		}
		@media (min-width: 768px) {
			.block-swiper-login-mobile, .block-img {
				height: 324px !important;
			}
		}

		@media(min-width: 1024px) {
			.block-swiper-login-mobile, .block-img {
				height: 350px !important;
			}
		}

		@media (min-width: 1280px) {
			.swiper {
				height: 100% !important;
			}

			.lg\:flex-row {
				flex-direction: row;
			}

			.block-login {
				background-image: url(form/img/bg-login.jpeg);
			}

			.block-img {
				height: auto !important;
			}

			.form-control {
				height: 45px !important;
			}

			.btn-primary,
			.form-control {
				font-size: 1.5rem;
			}

			.block-swiper-login {
				display: block !important;
			}

			.block-swiper-login-mobile {
				display: none !important;
			}
		}

		/* @media (min-width: 1100px) {
			.block-img {
				background-size: cover;
			}
		} */

		.swiper {
			width: 100%;
			height: 100%;
		}

		.swiper-slide {
			text-align: center;
			font-size: 18px;
			display: flex;
			justify-content: center;
			align-items: center;
		}
	</style>
	<script>
		(function() {
			Cookies.remove('jwt');
			Cookies.remove('PHPSESSID');
			localStorage.removeItem('jwt');
		})();

		function mostraRecuperaSenha() {

			var strCaixaTexto = "Informe abaixo seu usuário ou o email cadastrado no sistema:" +
				"<div class='form-inline'>" +
				"  <div class='form-group'>" +
				"    <input type='text' class='form-control' id='usuarioemail' placeholder='Usuário/Email'>" +
				"  </div>" +
				"  <button class='btn btn-default btn-danger' onclick='recuperarSenha()'>Enviar</button>" +
				"</div>";

			$("#cbModal").modal();
			$("#cbModal #cbModalCorpo").html(strCaixaTexto);
			$("#cbModal #cbModalTitulo").html("Esqueceu sua senha?");
		}

		function recuperarSenha() {

			strUsuarioEmail = $("#usuarioemail").val();
			if (strUsuarioEmail == undefined || strUsuarioEmail == "") {
				alertAtencao("Informe corretamente um usuário ou email válido!");
			} else {
				//Realiza a chamada da pagina para recuperação de senha
				$.ajax({
					type: 'get',
					cache: false,
					url: 'ajax/recuperarSenhaEmail.php',
					data: "passo=1&usuarioemail=" + strUsuarioEmail + "&modulo=recuperasenha&idobjeto=0",
					dataType: "text",
					beforeSend: function() {},
					success: function(data, textStatus, jqXHR) {
						if (jqXHR.getResponseHeader("X-CB-RESPOSTA") == "1") {
							alertAzul(data);
						} else {
							alertErro(data);
						}
					}
				});
			}
		}
	</script>
	<!-- Link Swiper's CSS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
	<?
	prompt::get('laudo_popup_login')::tipo('server')::executa();
	?>
	<div class="w-100 d-flex flex-col-reverse lg:flex-row main-block p-0">
		<div class="<?= $col ?> px-0 block-login h-100 d-flex align-items-center justify-content-center">
			<div class="<?= $colForm ?> d-flex flex-col align-items-center">
				<h1 class="mb-4 font-bold text-center w-100">Bem-vindo!</h1>
				<h5>Digite seus dados para acessar o sistema</h5>
				<div class="panel panel-default col-xs-12 p-0 bg-white mt-2">
					<div class="panel-heading text-center py-3">
						<h2 class="m-0 font-bold text-black">Área de acesso ao sistema</h2>
					</div>
					<div class="center-block panel-body col-xs-10 col-lg-7" style="padding-top: 1rem !important; padding-bottom: 1.5rem;">
						<?= $_msglogin ?>
						<br />
						<input type="text" name="usuario" class="form-control mb-3" placeholder="Usu&aacute;rio" autofocus vnulo><br>
						<div id="password-show" class="input-group">
							<input id="input-password" type="password" name="senha" class="form-control " placeholder="Senha" vnulo>
							<span class="input-group-addon pointer" id="password-icon"><i class="fa fa-eye"></i></span>
						</div>
						<div class="checkbox">
							<label style="display: none;">
								<input type="checkbox"> Permanecer logado
							</label>
						</div>
						<button class="btn btn-primary btn-block font-bold text-uppercase" onclick="CB.login()"><small>Entrar</small></button>
					</div>
					<a class='esqueci text-lg font-bold' href="javascript:mostraRecuperaSenha()">Esqueci minha senha</a>
				</div>
			</div>
		</div>
		<!-- Swiper -->
		<? if (count($banners['desktop'])) { ?>
			<div class="col-xs-12 col-md-6 p-0 block-swiper-login">
				<div class="swiper swiper-login">
					<div class="swiper-wrapper">
						<? foreach ($banners['desktop'] as $banner) { ?>
							<div class="swiper-slide block-img" style="background-image: url(<?= $banner['caminho'] ?>);" title="<?= $banner['titulo'] ?>"></div>
						<? } ?>
					</div>
					<div class="swiper-button-next"></div>
					<div class="swiper-button-prev"></div>
				</div>
			</div>
		<? } ?>
		<? if (count($banners['mobile'])) { ?>
			<div class="col-xs-12 col-lg-6 p-0 block-swiper-login-mobile">
				<div class="swiper swiper-login-mobile">
					<div class="swiper-wrapper">
						<? foreach ($banners['mobile'] as $banner) { ?>
							<div class="swiper-slide block-img" style="background-image: url(<?= $banner['caminho'] ?>);" title="<?= $banner['titulo'] ?>"></div>
						<? } ?>
					</div>
					<div class="swiper-button-next"></div>
					<div class="swiper-button-prev"></div>
				</div>
			</div>
		<? } ?>
		<?
		//recupera o parametro configurado pelo modulo "configuracoesgerais"
		$sqltelaini = "select * from " . _DBCARBON . "._paraplweb where parametro = 'avisoTelaInicial' and status = 'A'";
		$restini = mysql_query($sqltelaini) or die("<!-- erro ao recuperar parametros: " . mysql_error() . " -->");
		$rtini = mysql_fetch_assoc($restini);
		$msgFormatada = str_replace(array("\r", "\n"), "<br>", $rtini["valor"]);
		$msgFormatada = str_replace("<br><br>", "<br>", $msgFormatada);
		if ($rtini) {
		?>
			<script>
				function alertaLogin(inMsg) {

					inMsg = inMsg || "";

					var htmlAlerta = "<div class='row'><div class='col-md-4 offset-md-4' style='float: none; margin: 0 auto;'><div class='alert alert-warning aviso' role='alert'> \
		" + inMsg + " \
		</div></div></div>";

					CB.oModuloForm.before($(htmlAlerta));
				}
				var msg = "<?= $msgFormatada ?>";
				alertaLogin(msg);
			</script>
		<?
		}
		?>
		<script>
			$("#password-icon").on("click", function() {
				const passwordField = $("#input-password");
				const passwordFieldType = passwordField.attr("type");
				const passwordIcon = $(this.querySelector('#password-icon'));

				if (passwordFieldType === "password") {
					passwordField.attr("type", "text");
					passwordIcon.html('<i class="fa fa-eye-slash"></i>')
				} else {
					passwordField.attr("type", "password");
					passwordIcon.html('<i class="fa fa-eye"></i>')
				}
			});
		</script>
		<?
		//recupera o parametro configurado pelo modulo "configuracoesgerais"
		$sqltelaini = "select * from " . _DBCARBON . "._paraplweb where parametro = 'avisoTelaInicial' and status = 'A'";
		$restini = mysql_query($sqltelaini) or die("<!-- erro ao recuperar parametros: " . mysql_error() . " -->");
		$rtini = mysql_fetch_assoc($restini);
		$msgFormatada = str_replace(array("\r", "\n"), "<br>", $rtini["valor"]);
		$msgFormatada = str_replace("<br><br>", "<br>", $msgFormatada);
		//$msgFormatada = str_replace("\"","\\\"",$msgFormatada);

		$msgFormatada = "
			<table>
			<tr><td><i class='fa fa-shield fa-2x' style='margin-right: 10px;''></i></td>
			<td>
			<strong>Importante:</strong>
			<br>Caro cliente, para sua segurança, melhoramos nosso sistema de Login.
			<br>Portanto, antes de efetuar acesso ao Sistema, solicitamos que utilize o 
			<br><strong>procedimento de Recuperação de senha: </strong><a href='javascript:mostraRecuperaSenha()' style='cursor:pointer;'>Esqueci minha senha!</a>
			<br><br>Para mais informações, entre em contato conosco:
			<br>(34) 3222-5700 / resultados@laudolab.com.br
			<td>
			</tr>
			</table>";
		if ($msgFormatada and false) {
		?>
			<div class="row" id="alertalogin" class='hidden'>
				<div class="col-md-4 offset-md-4" style="float: none; margin: 0 auto;">
					<div class='alert alert-warning aviso' role='alert'>
						<?
						echo $msgFormatada;
						?>
					</div>
				</div>
			</div>
			<script>
				//Como o carbon recupera somente o conteúdo do formulário em questão, este script é executado após o carregamento, atribuindo o conteúdo ao form principal
				CB.oModuloForm.before($("#alertalogin").removeClass("hidden"));
			</script>
	<?
		}
	}
	?>
	<!-- Swiper JS -->
	<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
	<script>
		let idTimeout = 0;
		idTimeout = setTimeout(() => {
			if (typeof Swiper != 'undefined') {
				const swiperLogin = new Swiper(".swiper-login", {
					loop: true,
					autoplay: {
						delay: 3500,
						disableOnInteraction: false,
					},
					navigation: {
						nextEl: ".swiper-button-next",
						prevEl: ".swiper-button-prev",
					},
				});

				const swiperLoginMobile = new Swiper(".swiper-login-mobile", {
					loop: true,
					autoplay: {
						delay: 3500,
						disableOnInteraction: false,
					},
					navigation: {
						nextEl: ".swiper-button-next",
						prevEl: ".swiper-button-prev",
					},
				});

				clearTimeout(idTimeout);
			}
		}, 300);
	</script>