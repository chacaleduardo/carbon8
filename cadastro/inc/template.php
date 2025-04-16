<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>Pré Cadastro</title>

	<script src="https://cdn.tailwindcss.com"></script>
	<script src="/inc/js/jquery/jquery-1.11.2.min.js"></script>
	<script>
		tailwind.config = {
			theme: {
				extend: {
					screens: {
						"3xl": "1600px",
						"4xl": "2000px",
						xs: "375px",
						sm: "430px",
					}
				},
			},
		};
	</script>

	<link rel="stylesheet" type="text/css" href="/cadastro/css/cadastro.css">
	<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
	<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>

<body>
	<div id="app" class="w-lvw h-svh relative overflow-x-hidden overflow-y-auto login-bg flex flex-col justify-between">
		<? if(!$cadastroFinalizado) { ?>
			<form id="form-principal" action="" method="POST">
				<div class="relative">
					<div class="w-full h-32 flex flex-col-reverse md:flex-row justify-between items-center px-8 py-6 bg-cover" style="background-image: url(./img/banner.png);">
						<span class="text-white text-2xl md:text-4xl font-bold ps-0 lg:ps-48">Realizar pré-cadastro</span>
						<img class="h-16 md:h-20" src="./img/logo-laudo.png" alt="" />
					</div>
					<div class="w-full flex flex-col m-auto mt-16 gap-6 justify-center items-center p-6">
						<span class="w-full text-2xl md:text-4xl text-center font-medium text-[#40464F]">
							Crie seu pré-cadastro abaixo
						</span>

						<span class="w-11/12 text-base md:text-xl text-start">
							Para o cadastro em nosso sistema é necessário preencher o formulário
							abaixo com os respectivos dados cadastrais. Os Campos com * são de
							preenchimento obrigatório e essenciais para realizarmos o cadastro
						</span>
						<!-- Dados do cliente -->
						<div id="dados-cliente" class="w-full flex flex-wrap rounded-md border border-[#C0C0C0] m-6">
							<span class="w-full text-white py-2 bg-[#178B94] text-center rounded font-bold">Dados do cliente</span>
							<div class="w-full p-4 flex flex-col bg-[#F5F5F5] gap-2">
								<div class="w-full flex flex-col md:flex-row gap-2">
									<div class="w-full md:w-6/12">
										<span class="text-xs text-[#989898]">Tipo de cliente <span class="text-red-600">*</span></span>
										<select onchange="alteraCpfCnpj(this);" id="tipocliente" name="tipocliente" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]">
											<option value="cnpj" selected>Pessoa Jurídica</option>
											<option value="cpf">Pessoa Fisíca</option>
										</select>
									</div>
									<!-- CNPJ -->
									<div id="input-cnpj" class="w-full md:w-6/12">
										<span class="text-xs text-[#989898]">CNPJ <span class="text-red-600">*</span></span>
										<input name="cnpj" class="w-full cnpj required py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o CNPJ da empresa" />
									</div>
									<!-- CPF -->
									<div id="input-cpf" class="w-full md:w-6/12 hidden">
										<span class="text-xs text-[#989898]">CPF <span class="text-red-600">*</span></span>
										<input name="cpf" class="w-full cpf required py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o seu CPF" />
									</div>
								</div>
								<div id="pessoa-juridica"><? require_once('./form/pessoajuridica.php'); ?></div>
								<div id="pessoa-fisica" hidden><? require_once('./form/pessoafisica.php'); ?></div>
							</div>
						</div>
						<!-- Dados do endereco -->
						<div id="endereco" class="w-full flex-wrap flex-col m-auto gap-6 justify-center items-center hidden m-6">
							<? require_once('./form/endereco.php'); ?>
						</div>
						<!-- Acesso sislaudo -->
						<div id="acesso-sislaudo" class="w-full flex-wrap rounded-md border border-[#fafafa] hidden m-6">
							<? require_once('./form/usuario.php'); ?>
						</div>
					</div>
					<div class="w-full flex justify-between p-6">
						<button id="btn-cancelar" onclick="cancelar()" type="button" class="py-2 text-[#178B94] font-bold rounded">
							Cancelar
						</button>
						<button id="btn-voltar" onclick="voltar(event)" class="py-2 text-[#178B94] font-bold rounded hidden">
							Voltar
						</button>
						<button id="btn-proximo" onclick="avancar(event);" class="p-2 px-4 bg-[#178B94] font-bold rounded text-white">
							Próximo
						</button>
						<button id="btn-enviar-cadastro" onclick="enviarCadastro(event);" class="p-2 px-4 bg-[#178B94] font-bold rounded text-white hidden" type="submit">
							Enviar cadastro
						</button>
					</div>
				</div>
			</form>
		<?} else { ?>
			<div class="w-full flex-col py-4">
				<div class="flex flex-col items-center gap-10">
					<span class="text-base text-center font-bold md:text-4xl px-2">Seu pré-cadastro foi realizado com sucesso!</span>
					<div>
						<img class="md:h-80" src="/inc/img/contatomenurapido/sucesso.svg" alt="">
					</div>
					<div class="w-full flex flex-col gap-2 justify-center items-center md:text-xl font-light px-2 text-center md:px-0 md:text-start">
						<span>Você já pode acessar o nosso sistema!</span>
						<span>Verifique as funcionalidades liberadas no link abaixo.</span>
					</div>
					<a class="text-xl md:text-3xl font-bold text-[#176292] underline" href="/?_modulo=_login">Acessar sistema</a>
				</div>
			</div>
		<?}?>
		<? require_once('./inc/footer.php'); ?>
	</div>
</body>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script type="text/Javascript">
	const formHTML = $('#form-principal').get(0);

	const dadosCliente = {},
        etapas = [
            'dados-cliente',
            'endereco',
            'acesso-sislaudo'
        ];

    const modalFinalizarHTML = `<div class="w-full flex-col py-4">
                                    <div class="flex flex-col items-center gap-10">
                                        <span class="text-base text-center font-bold md:text-4xl px-2">O pedido de envio de amostra <br> foi realizado com sucesso!</span>
                                        <div>
                                            <img class="md:h-80" src="/inc/img/contatomenurapido/sucesso.svg" alt="">
                                        </div>
                                        <div class="w-full flex flex-col gap-2 justify-center items-center md:text-xl font-light px-2 text-center md:px-0 md:text-start">
                                            <span>Assim que o pedido for aprovado, entraremos em contato!</span>
                                        </div>
                                        <a class="text-xl md:text-3xl font-bold text-[#176292] underline" href="/?_modulo=contatomenurapido">Voltar</a>
                                    </div>
                                </div>`;

    let etapaAtual = 0;


	// Remover erro ao digitar no campo
    $('.required').on('blur', function() {
        const elementJQ = $(this);
        if (elementJQ.val() && elementJQ.hasClass('error')) elementJQ.removeClass('error');
    });

	$(".password-icon").on("click", function () {
		const passwordField = $(`#${$(this).data('id')}`);
		const passwordFieldType = passwordField.attr("type");
		const passwordIcon = $(this);

		if (passwordFieldType === "password") {
			passwordField.attr("type", "text");
			passwordIcon.html(`<svg fill="#5E5E5E" width="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L525.6 386.7c39.6-40.6 66.4-86.1 79.9-118.4c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C465.5 68.8 400.8 32 320 32c-68.2 0-125 26.3-169.3 60.8L38.8 5.1zM223.1 149.5C248.6 126.2 282.7 112 320 112c79.5 0 144 64.5 144 144c0 24.9-6.3 48.3-17.4 68.7L408 294.5c8.4-19.3 10.6-41.4 4.8-63.3c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3c0 10.2-2.4 19.8-6.6 28.3l-90.3-70.8zM373 389.9c-16.4 6.5-34.3 10.1-53 10.1c-79.5 0-144-64.5-144-144c0-6.9 .5-13.6 1.4-20.2L83.1 161.5C60.3 191.2 44 220.8 34.5 243.7c-3.3 7.9-3.3 16.7 0 24.6c14.9 35.7 46.2 87.7 93 131.1C174.5 443.2 239.2 480 320 480c47.8 0 89.9-12.9 126.2-32.5L373 389.9z"/></svg>`)
		} else {
			passwordField.attr("type", "password");
			passwordIcon.html(`<svg fill="#5E5E5E" width="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
									<path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z" />
								</svg>`)
		}
	});

    function avancar(e) {
        e.preventDefault();
        if (!validarCamposObrigatorios()) return alertAtencao('Preencha os campos obrigatórios.');

        alterarEtapa(etapaAtual + 1);

        atualizarBotaoVoltar();
        atualizarBotaoAvancar();

    }

    function voltar(e) {
        e.preventDefault();
        alterarEtapa(etapaAtual - 1);
        atualizarBotaoVoltar();
        atualizarBotaoAvancar();
    }

	function cancelar() {
		if(confirm('Deseja cancelar o pré-cadastro?')) window.location.href = '/';
	}

	function alertAtencao(text) {
		Toastify({	
			text,
			className: "alert",
			duration: 2000
		}).showToast();
	}

	function enviarCadastro(e) {
		e.preventDefault();
		
		// Validar campos 
		if(!validarSenhas()) return;
		// Verificar se é pessoa fisica ou juridica selecionada e remover da DOM 
		if(!verificarCpfCnpj()) return;

		formHTML.submit();
	}

	function verificarCpfCnpj() {
		if($('#input-cnpj .cnpj').val()) {
			return $('#pessoa-fisica').remove();
		} else if($('#input-cpf .cpf').val()) {
			return $('#pessoa-juridica').remove();
		}else {
			alertAtencao('Tipo pessoa não selecionado.');
			return false;
		}
	}

	function validarSenhas() {
		const senha = $('#input-password').val(),
				confirmSenha = $('#input-confirm-password').val();

		if(senha != confirmSenha) return alertAtencao('As senhas não coincidem.');


		return true;
	}

	function alteraCpfCnpj(element) {
		const elementJQ = $(element);

		if(elementJQ.val() == 'cpf') {
			$('#input-cnpj .cnpj').val('');

			$('#pessoa-fisica')
				.removeClass("hidden")
				.addClass('block');

			$('#input-cpf')
				.removeClass("hidden")
				.addClass('block');

			$('#pessoa-juridica')
				.addClass("hidden")
				.removeClass('block');

			$('#input-cnpj')
				.addClass("hidden")
				.removeClass('block');
		} else {
			$('#input-cpf .cpf').val('');

			$('#pessoa-juridica')
				.removeClass("hidden")
				.addClass('block');

			$('#input-cnpj')
				.removeClass("hidden")
				.addClass('block');

			$('#pessoa-fisica')
				.addClass("hidden")
				.removeClass('block');

			$('#input-cpf')
				.addClass("hidden")
				.removeClass('block');
		}
	}

	// Validar campos obrigatórios
    function validarCamposObrigatorios() {
        const camposObrigatoriosJQ = $('.required').filter(function() {
            let elementJQ = $(this);

            if (elementJQ.attr('type') == 'radio') {
                return $(this).is(':visible') && !$(`[name="${elementJQ.attr('name')}"]`).get().some(item => item.checked);
            }

            return $(this).is(':visible') && !$(this).val()
        });

        camposObrigatoriosJQ.addClass('error');

        return !camposObrigatoriosJQ.length;
    }

	function alterarEtapa(novaEtapa) {
        const etapaJQ = $(`#${etapas[novaEtapa]}`);

        if (etapaJQ.length) {
            const etapaAnteriorJQ = $(`#${etapas[etapaAtual]}`);

            etapaAnteriorJQ
                .removeClass('flex')
                .addClass('hidden');

            etapaJQ
                .addClass('flex')
                .removeClass('hidden');
        }

        etapaAtual = novaEtapa;
    }

	function atualizarBotaoVoltar() {
        if (!etapaAtual) {
            $('#btn-cancelar')
                .removeClass('hidden')
                .addClass('block');

            $('#btn-voltar')
                .removeClass('block')
                .addClass('hidden');
        } else {
            $('#btn-cancelar')
                .addClass('hidden')
                .removeClass('block');

            $('#btn-voltar')
                .addClass('block')
                .removeClass('hidden');
        }
    }

    function atualizarBotaoAvancar() {
        if (etapaAtual === 2) {
            $('#btn-enviar-cadastro')
                .removeClass('hidden')
                .addClass('block');

            $('#btn-proximo')
                .addClass('hidden')
                .removeClass('block');
        } else {
            $('#btn-proximo')
                .removeClass('hidden')
                .addClass('block');

            $('#btn-enviar-cadastro')
                .removeClass('block')
                .addClass('hidden');
        }
    }

	$('.cpf').mask('000.000.000-00', {reverse: true});
	$('.cnpj').mask('00.000.000/0000-00', {reverse: true});
	$('.telefone').mask('(00) 0000-00009');
	$('.telefone-sem-ddd').mask('0000-00009');
	$('.ddd').mask('000');
	// Ajuste para celulares com 9 dígitos
	$('.telefone').blur(function() {
		var telefone = $(this).val().replace(/\D/g, '');
		if (telefone.length > 10) {
			$(this).mask('(00) 00000-0000');
		} else {
			$(this).mask('(00) 0000-0000');
		}
	});
	$('.uf').mask('AA');
	$('.cep').mask('00000-000');
</script>

</html>