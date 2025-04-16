<?
//Necessario incluir manualmente na tela de login
require_once("../inc/php/functions.php");
//print_r($_POST);
//print_r($_SESSION);die;

if (
	(
		$_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1
		or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 15
		or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 16
		or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 113
	)
	and !empty($_POST["idempresa"])
	and !empty($_SESSION["SESSAO"]["USUARIO"])
	and empty($_POST["usuario"])
) {
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

prompt::get('laudo_popup_login')::tipo('server')::executa();

?>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Login</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
	let idTimeout;

	idTimeout = setInterval(() => {
		if(typeof tailwind != 'undefined') {
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

			clearInterval(idTimeout);
		}
	}, 400);
	
</script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<link rel="stylesheet" href="/inc/css/index.css" />

<div class="w-lvw h-svh relative overflow-x-hidden overflow-y-auto login-bg flex flex-col justify-between">
	<div class="w-lvw h-svh bg-[#178b94ab] flex flex-col items-center justify-center px-4 gap-12">

		<span class="text-white font-bold text-[1.20rem] sm:text-2xl md:text-4xl xl:text-6xl">
			Bem-vindo ao Laudo Laboratório!
		</span>

		<form id="recovery-password" action="#" class="w-full bg-[#EEEEEE] flex flex-col rounded-xl justify-around p-4 md:w-[450px] gap-6" style="display: none;">
			<div class="flex gap-4 text-xl font-bold">
				<span class="p-1 primary-bg"></span>
				Recuperar senha
			</div>
			<span>Você receberá um e-mail para redefinição de senha</span>
			<div class="w-full flex rounded bg-white relative items-center">
				<img class="absolute left-4" src="../inc/img/login/svg/user-icon.svg" alt="" />
				<input class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#178b94] pl-10" type="text" placeholder="Usuário" id="usuarioemail" required />
			</div>

			<div class="flex flex-col gap-2">
				<button class="w-full primary-bg font-bold text-white border-none rounded py-2 text-sm shadow-md" onclick="event.preventDefault();recuperarSenha()">
					ENVIAR
				</button>
				<a href="#">
					<button class="w-full bg-[#FFFFFF] font-bold primary-text border-none rounded py-2 text-sm shadow-md" type="button" onclick="$('#login').toggle();$('#recovery-password').toggle()">
						VOLTAR
					</button>
				</a>
			</div>
		</form>

		<form id="login" action="#" class="w-full bg-[#EEEEEE] flex flex-col rounded-xl justify-around p-4 md:w-[450px] gap-6">
			<div class="flex gap-4 text-xl font-bold">
				<span class="p-1 primary-bg"></span>
				Área de acesso ao sistema
			</div>
			<div class="w-full flex rounded bg-white relative items-center">
				<img class="absolute left-4" src="../inc/img/login/svg/user-icon.svg" alt="" />
				<input class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#178b94] pl-10" type="text" placeholder="Usuário" id="usuario" name="usuario" autofocus vnulo required />
			</div>
			<div class="w-full flex rounded bg-white relative items-center">
				<img class="absolute left-4" src="../inc/img/login/svg/pasword-icon.svg" alt="" />
				<input type="password" placeholder="Senha" class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#178b94] pl-10" id="input-password" name="senha" required vnulo />
				<img id="password-icon" class="password-icon absolute right-4 h-5 cursor-pointer hidden" src="../inc/img/login/svg/eye-off.svg" alt="" />
				<img id="password-icon-off" class="password-icon absolute right-4 h-5 cursor-pointer" src="../inc/img/login/svg/eye.svg" alt="" />
			</div>
			<div class="w-full flex justify-between">
				<a href="/cadastro">Cadastre-se</a>
				<a href="#" onclick="$('#login').toggle();$('#recovery-password').toggle()" class="self-end">Esqueci minha senha</a>
			</div>

			<button class="w-full primary-bg font-bold text-white border-none rounded py-2 text-sm shadow-md" onclick="event.preventDefault();CB.login()">
				ENTRAR
			</button>
		</form>

	</div>
	<div class="w-lvw">
		<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 primary-bg text-[#FAFAFA] font-light text-xs py-6 xl:text-base w-lvw py-6 ">
			<div class="p-4 flex flex-col justify-center items-center">
				<span>LAUDO LABORATÓRIO AVÍCOLA</span>
				<span>UBERLÂNDIA LTDA</span>
			</div>
			<div class="p-4 flex flex-col justify-center items-center">
				<span>Rod. BR 365, km 615 - SN B. Alvorada </span>
				<span>Uberlândia/MG - CEP 38.407-180 </span>
				<span>Telefone: (34) 3222-5700</span>
			</div>
			<div class="p-4 flex flex-col justify-center items-center">
				<span>www.laudolab.com.br</span>
				<span>resultados@laudolab.com.br</span>
			</div>
		</div>
		<div class="w-full py-2 bg-[#757F95] text-center text-[#FAFAFA] text-xs md:text-base">
			©Copyright - Biofy Technologies | Todos os Direitos Reservados 2025
		</div>
	</div>
</div>

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

	alertVars = (data, icon, {
		background,
		color
	}) => {
		$('.info').hide();
		return {
			text: data.replace(/(<([^>]+)>)/gi, ""),
			className: icon,
			duration: 3000,
			position: "center",
			gravity: "top",
			stopOnFocus: true,
			close: false,
			style: {
				background: background,
				color: color,
				'border-radius': '10px'
			},
			oldestFirst: false
		}
	}

	alertErro = (data) => {
		Toastify(alertVars(data, 'info', {
			background: 'white',
			color: 'black'
		})).showToast();
	}
	alertAzul = (data) => {
		Toastify(alertVars(data, 'info', {
			background: 'blue',
			color: 'white'
		})).showToast();
	}
	alertAtencao = (data) => {
		Toastify(alertVars(data, 'info', {
			background: 'white',
			color: 'black'
		})).showToast();
	}
	alertAguarde = (data) => {
		Toastify(alertVars('AGUARDE', 'info', {
			background: 'white',
			color: 'grey'
		})).showToast();
	}

	$(".password-icon").on("click", function() {
		const passwordField = $("#input-password");
		const passwordFieldType = passwordField.attr("type");
		const passwordIcon = $(this.querySelector('#password-icon'));

		if (passwordFieldType === "password") {
			passwordField.attr("type", "text");
			$('#password-icon-off').hide();
			$('#password-icon').show();
		} else {
			passwordField.attr("type", "password");
			$('#password-icon-off').show();
			$('#password-icon').hide();
		}
	});
</script>