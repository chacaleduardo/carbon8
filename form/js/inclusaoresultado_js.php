<script>
	//injeções de php
	const idempresa = <?= cb::idempresa() ?>;
	const servicosVinculados = <?= $servicosVinculados ?>;
	const jsonServicos = <?= jsonServicos() ?>;
	const idResultado = Number(<?= $_1_u_resultado_idresultado ?>);
	const vModelo = "<?= $_1_u_resultado_modelo ?>";
	const vModo = "<?= $_1_u_resultado_modo ?>";
	const qtx = parseInt(<?= $_1_u_resultado_quantidade ?>); //A soma dos orificios deve ser <= a este valor
	const jsonConfig = <?= !empty($_1_u_resultado_jsonconfig) ? $_1_u_resultado_jsonconfig : '{}' ?>;
	const qtdResultado = '<?= $qtdfixo ?>' == 0 ? '<?= $_1_u_resultado_quantidade ?>' : '<?= $qtdfixo ?>';
	const idplantel = '<?= $idplantel; ?>';
	const moduloAmostra = '<?= $moduloAmostra ?>';
	const objJsonReturn = <?= !empty($_1_u_resultado_jsonresultado) ? $_1_u_resultado_jsonresultado : '{}' ?>;
	const idfluxostatus = <?= FluxoController::getIdFluxoStatus($nomeModulo, 'ABERTO') ?>;
	const idAmostra = <?= $_1_u_resultado_idamostra ?>;
	const auxQt = "<?= $_SESSION['auxqt'] ?>";
	const jsonResultado = <?= json_encode($jsonresultado) ?>;
	const statusResultado = '<?= $_1_u_resultado_status ?>';

	var jArrAgentes = <?= count($arrAgentes) == 0 ? "new Array()" : $arrAgentes ?>;
	var vFixo = '<?= $fixo; ?>';
	var vFixoindice = '<?= $fixoindice; ?>';
	var vQtdfixo = '<?= $qtdfixo; ?>';


	//Variáveis de Uso Geral
	const vinculados = jQuery.map(servicosVinculados, function(o, id) {
		return {
			"idobjeto": o.idobjeto,
			"oficial": o.logoinmetro
		}
	});

	var pageStateChanged = false; //Teste se a pagina sofreu alteracoes
	var arrKeyConf = new Array();
	var arrKeyConf = new Array();
	var xoper = "+";


	$(function() {
		$('[data-toggle="popover"]').popover({
			html: true,
			content: function() {
				let ModalPopoverId = $(this).attr("href").replaceAll("#", "")
				return $("#" + ModalPopoverId).html();
			}
		});
	});

	let idTimeout = null;

	// Inicializa todos os elementos com a classe 'tinymce-editor'
	tinymce.init({
		selector: 'div.resultado-obs',
		language: 'pt_BR',
		inline: true /* não usar iframe */ ,
		toolbar: 'removeformat | fontsizeselect | bold | subscript superscript | bullist numlist | table',
		menubar: false,
		plugins: ['table'],
		fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt",
		removeformat: [{
				selector: 'b,strong,em,i,font,u,strike',
				remove: 'all',
				split: true,
				expand: false,
				block_expand: true,
				deep: true
			},
			{
				selector: 'span',
				attributes: ['style', 'class'],
				remove: 'empty',
				split: true,
				expand: false,
				deep: true
			},
			{
				selector: '*',
				attributes: ['style', 'class'],
				split: false,
				expand: false,
				deep: true
			}
		],
		setup: function (editor) {
			editor.on('keyup', function (e) {
				const indiceGrupo = $(editor.getElement()).data('indicegrupo');

				if(jsonResultado['grupo'][indiceGrupo]) jsonResultado['grupo'][indiceGrupo]['resultadoobs'] = editor.getContent();
				$('#jsonresultado').val(JSON.stringify(jsonResultado));
			});
		}
	});

	tinymce.init({
		selector: 'div.campo-resultado',
		language: 'pt_BR',
		inline: true /* não usar iframe */ ,
		toolbar: 'removeformat | fontsizeselect | bold | subscript superscript | bullist numlist',
		menubar: false,
		plugins: ['table'],
		fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt",
		removeformat: [{
				selector: 'b,strong,em,i,font,u,strike',
				remove: 'all',
				split: true,
				expand: false,
				block_expand: true,
				deep: true
			},
			{
				selector: 'span',
				attributes: ['style', 'class'],
				remove: 'empty',
				split: true,
				expand: false,
				deep: true
			},
			{
				selector: '*',
				attributes: ['style', 'class'],
				split: false,
				expand: false,
				deep: true
			}
		],
		setup: function (editor) {
			editor.on('keyup', function (e) {
				const elementoJQ = $(editor.getElement()),
					indiceGrupo = elementoJQ.data('indicegrupo'),
					campo = elementoJQ.data('campo'),
					indiceTeste = elementoJQ.data('indiceteste');

				if(!jsonResultado['grupo'][indiceGrupo]) jsonResultado['grupo'][indiceGrupo] = {
					testes: []
				};

				if(!jsonResultado['grupo'][indiceGrupo]['testes'][indiceTeste]) jsonResultado['grupo'][indiceGrupo]['testes'][indiceTeste] = {
					resultadoum: '',
					resultadodois: ''
				};

				jsonResultado['grupo'][indiceGrupo]['testes'][indiceTeste][campo] = editor.getContent();

				$('#jsonresultado').val(JSON.stringify(jsonResultado));
			});
		}
	});

	function botaoVoltarVersao() {
		CB.novoBotaoUsuario({
			id: "voltarVersaoResult",
			rotulo: "Voltar Versão",
			icone: "fa fa-undo",
			class: "btn btn-primary",
			onclick: function() {
				let versaoatual = $(":input[name=_1_" + CB.acao + "_resultado_conteudo]").val()
				var htmlmodal = $(`#mdvoltarversao`).html()

				CB.modal({
					titulo: "</strong>Voltar Versão do Resultado</strong>",
					corpo: [htmlmodal],
					classe: 'cinquenta',
				});
			}
		});
	}
	if ($("[name=_1_" + CB.acao + "_resultado_status]").val() == 'ASSINADO' && $(`#voltarVersaoResult`).length == 0) {
		botaoVoltarVersao();
	} else {
		$(`#voltarVersaoResult`).remove()
	}

	function voltaversaosave(idresultado, versaoanterior) {
		let desc = $('#cbModalCorpo').find('#_edicaoobsres_').val();
		let status = $('#cbModalCorpo').find('[name=idfluxostatushist]:checked').attr('status')
		let idfluxostatus = $('#cbModalCorpo').find('[name=idfluxostatushist]:checked').attr('idfluxostatus')
		let motivo = $('#cbModalCorpo').find('#_edicaomotivores_').val()

		if (!status && desc.length < 5) {
			alert('Preencha os campos corretamente e forneça uma descrição com no mínimo 10 caractares')
		} else {
			if (confirm('Tem certeza que deseja alterar a versão do Resultado? Essa ação é Irreversível')) {
				CB.post({
					objetos: {
						"_versionares_u_resultado_idresultado": idresultado,
						"_versionares_u_resultado_versaoatual": versaoanterior,
						"_versionares_u_resultado_status": status,
						"_versionares_u_resultado_idfluxostatus": idfluxostatus,
						"_versionares_u_resultado_desc": desc,
						"_versionares_u_resultado_motivo": motivo,
					},
					parcial: true
				})
			} else {
				$("[data-dismiss=modal]").click()
			}
		}

	}

	if (vModelo == "DESCRITIVO") {
		sSeletor = '#diveditor';
		oDescritivo = $("[name=_1_" + CB.acao + "_resultado_descritivo]");
		//Inicializa Editor
		if (tinyMCE.editors["diveditor"]) {
			tinyMCE.editors["diveditor"].remove();
		}
		tinyMCE.init({
			selector: sSeletor,
			language: 'pt_BR',
			inline: true /* não usar iframe */ ,
			toolbar: ' italic | subscript superscript | formatselect | removeformat | fontsizeselect | | bullist numlist | table |  alignleft aligncenter alignright alignjustify',
			menubar: false,
			plugins: ['table', 'autoresize', 'imagetools', 'contextmenu', 'advlist', 'template'],
			setup: function(editor) {
				editor.on('init', function(e) {
					this.setContent(oDescritivo.val());
				});
			},
			entity_encoding: 'raw'
		});

		//Antes de salvar atualiza o textarea
		CB.on('prePost', function() {
			if (tinyMCE.get('diveditor')) {
				oDescritivo.val(
					tinyMCE.get('diveditor').getContent().replace(/[a-z]/gi, function(char) {
						return char;
					})
				);
			}
		});

	} else if (vModelo == "DINÂMICO") {

		function criaModalBotaoAplicarATodos(tipo, indice, teste) {
			if (tipo == 1) {
				tipo = 'selecionavel';
			} else if (tipo == 2) {
				tipo = 'fixo';
			}

			let novoCampo = '<div class="modOption' + indice + '" data-campo="' + indice + '">\
				<div id="modalID' + indice + '" class="modal"  tabindex="-' + indice + '" role="dialog">\
				<div class="modal-dialog" role="document">\
					<div class="modal-content">\
						<div class="modal-header">\
							<h5 class="modal-title">Aplicar à todos</h5>\
						</div>\
						<div class="modal-body">\
							<p>Selecione abaixo a opção para que seja preenchida nos outros itens selecionáveis</p>\
							<div class="row">\
							<div class="col-lg">';

			if (typeof(jsonConfig) == 'object') {
				var stop = false;
				var unidade = idplantel ? idplantel : 'todas';

				jsonConfig.unidadeBloco.forEach(function(bloco) {

					if (bloco.unidade == unidade && teste == 1) {
						bloco.personalizados.forEach(function(key) {
							if (bloco.index == key.index) {
								if (key.tipo == tipo && key.indice == indice) {
									novoCampo += '<div class="input-group mb-3">\
									   <div class="input-group-prepend">\
										  <label class="input-group-text" for="select' + key.indice + '">Opções</label>\
									   </div>\
									   <select class="custom-select" id="select' + key.indice + '">\
										  <option selected value="vazio"></option>';
									key.options.forEach(function(option) {
										novoCampo += '<option value="' + option.indice + '">' + option.nome + '</option>\ ';
									});
									novoCampo += '</select>\
										</div>\
										</div>\
										</div>\
										</div>\
										<div class="modal-footer">\
											<span id="aplicarOpcao" class="btn btn-success" data-dismiss="modal" onclick="aplicarOpcaoSelecionadaModalAplicarATodos(' + key.indice + ');">Aplicar</span>\
											<button id="cancelarOption" type="button"\
												class="btn btn-danger"\
												data-dismiss="modal">Cancelar</button>\
										</div>\
										</div>\
										</div>\
										</div>\
									<\div>';
								}
							}
						});

					} else if (bloco.unidade == "todas" && teste != 1) {
						bloco.personalizados.forEach(function(key) {
							if (bloco.index == key.index) {
								if (key.tipo == tipo && key.indice == indice) {
									novoCampo += '<div class="input-group mb-3">\
									   <div class="input-group-prepend">\
										  <label class="input-group-text" for="select' + key.indice + '">Opções</label>\
									   </div>\
									   <select class="custom-select" id="select' + key.indice + '">\
										  <option selected value="vazio"></option>';
									key.options.forEach(function(option) {
										novoCampo += '<option value="' + option.indice + '">' + option.nome + '</option>\ ';
									});
									novoCampo += '</select>\
										</div>\
										</div>\
										</div>\
										</div>\
										<div class="modal-footer">\
											<span id="aplicarOpcao" class="btn btn-success" data-dismiss="modal" onclick="aplicarOpcaoSelecionadaModalAplicarATodos(' + key.indice + ');">Aplicar</span>\
											<button id="cancelarOption" type="button"\
											class="btn btn-danger"\
											data-dismiss="modal">Cancelar</button>\
										</div>\
										</div>\
										</div>\
										</div>\
									<\div>';
								}
							}
						});
					}
				});
			}
			return novoCampo;
		}

		/*JLAL - 20/10/01
	  	Criação da função vai replicar o valor selecionado para os outros campos select
   		*/
		function aplicarOpcaoSelecionadaModalAplicarATodos(index) {
			var valor = $('#select' + index).val();
			if (valor == "vazio") {
				$('.aplicar' + valor + index + ' ').not('[readonly] .aplicar' + valor + index + ' ').prop('selected', true);
			} else {
				// $('.aplicar' + valor + ' ').not('[readonly] .aplicar' + valor + ' ').prop('selected', true);
				$(`[indice-campo='${index}'] .aplicar${valor} `).not(`[readonly] .aplicar${valor}`).prop('selected', true);
			}
		}


		/*JLAL - 20/10/01
		Criação da função que cria o modal para que o usuário consiga escolher qual valor vai replicar para os outros campos select
	  	*/
		function criarBotaoAbrirModalReplicarResultado(tipo, indice, teste) {
			let novaConfig = criaModalBotaoAplicarATodos(tipo, indice, teste);
			$('.configModal' + indice).append($(novaConfig));

			$('#modalID' + indice).modal('show');
			$(".modal-backdrop").remove();

		}


		function acrescentarRowAoResultado(valor, indice) {

			console.log(valor);
			let ind = $('.interna2').length--;
			let nIndice = 0;
			$('[data-indice]').each(function(index) {

				let input = $(this);
				if (Number(input.attr('data-indice')) > nIndice) {

					nIndice = input.attr('data-indice');
				}
			});

			nIndice++;
			let grid = $(valor).closest(".row").html();
			grid = grid.split(">" + indice + "<").join(">" + nIndice + "<");
			grid = grid.split(indice + "_campo").join(nIndice + "_campo");
			grid = grid.split('data-indice="' + indice + '"').join('data-indice="' + nIndice + '"');

			$(valor).closest(".row").after(grid);

			/*JLAL - 20/10/01 
			Atualiza a quantidade de testes a cada acrescimo, além de salvar automatico para atualizar o indice, evitando que ocorra erro         
		 	*/
			document.getElementById('qtTeste').value = ind;

			CB.post();
		}


		function removerRowDoResultado(valor, indice = null) {
			console.log(valor);
			var ind = $('.interna2').length - 1;

			var resultado = $(`[data-titulo="RESULTADO"][data-indice="${indice}"]`).val();
			var semente = $(`[data-titulo="SEMENTE"][data-indice="${indice}"]`).val();

			if (indice && resultado == "POSITIVO" && semente != "") {
				if (confirm("Deseja realmente retirar essa semente?")) {
					var grid = $(valor).closest(".row").remove();
					ind--;
					var qt = document.getElementById('qtTeste').value = ind;
					/*JLAL - 20/10/01 
					Atualiza a quantidade de testes a cada decrescimo,caso a quatidade chegue a zero, altera o valor pegando o ultimo valor cadastrado para aquele tipo de resultado
					, além de salvar automatico para atualizar o indice, evitando que ocorra erro         
			   		*/
					if (qt == 0) {
						qt = document.getElementById('qtTeste').value = auxQt;
					}

					let lote = jArrAgentes.filter((o, i) => {
						return o.agente + "/" + o.exercicio == semente
					});

					if (lote.length > 0) {
						if ($('div[data-tipo="divhtml"]').attr('name')) {
							namedoobjeto = $('div[data-tipo="divhtml"]').attr('name');
							$('div[data-tipo="divhtml"]').each(function(index, element) {
								var nome = $(this).attr('name').replace('editor', '');
								$('textarea[name="' + nome + '"]').val(element.innerHTML);
							});
						}
						InputsToJsonField();
						CB.post({
							objetos: {
								"_x_u_lote_idlote": lote[0].idlote,
								"_x_u_lote_orgao": ""
							},
						});
					} else {
						CB.post();
					}
				}

			} else {
				var grid = $(valor).closest(".row").remove();
				ind--;
				var qt = document.getElementById('qtTeste').value = ind;
				/*JLAL - 20/10/01 
				   Atualiza a quantidade de testes a cada decrescimo,caso a quatidade chegue a zero, altera o valor pegando o ultimo valor cadastrado para aquele tipo de resultado
				   , além de salvar automatico para atualizar o indice, evitando que ocorra erro         
				*/
				if (qt == 0) {
					qt = document.getElementById('qtTeste').value = auxQt;
				}
				CB.post();
			}

		}


		function checkedAll() {
			$("input[name='remove']").each(function() {
				$(".remove").prop('checked', true);
			});
			$('#removeChecked').show();
			$('#selectChecked').show();
			$('.remove').show();
			$('#unchecked').hide();
		}

		function uncheckedAll() {
			$("input[name='remove']").each(function() {
				$(".remove").prop('checked', false);
			});
			$('#removeChecked').show();
			$('#selectChecked').hide();
			$('#unchecked').show();
		}

		function delSelecionados() {
			var verificaMsg = $("[data-titulo='SEMENTE']").filter((i, o) => {
				if (o.value &&
					$(`[data-titulo='RESULTADO'][data-indice="${$(o).attr('data-indice')}"]`).val() == "POSITIVO" &&
					$(`[data-titulo='TIPO DE AMOSTRA'][data-indice="${$(o).attr('data-indice')}"]`).val() != "" &&
					$(o).closest(".global").find("[name='remove']").is(":checked")) {
					return true;
				} else {
					return false;
				}
			})

			let Nverifica = true;
			if (verificaMsg.length > 0) {
				Nverifica = confirm("Existem sementes associadas ao teste, deseja realmente excluí-las?");
			}

			if (Nverifica) {

				var ind = $('.interna2').length - 1;
				$("input[name='remove']:checked").each(function(i, o) {

					var resultado = $(this).closest('.global').find(`[data-titulo="RESULTADO"]`).val()
					var semente = $(this).closest('.global').find(`[data-titulo="SEMENTE"]`).val()

					if (resultado == "POSITIVO" && semente != "") {
						if ((moduloAmostra == 'amostratra' || moduloAmostra == 'amostraautogenas') && idResultado != '') {
							let lote = jArrAgentes.filter((o, i) => {
								return o.agente + "/" + o.exercicio == semente
							});
							if (lote.length > 0) {
								$("#cbModuloForm").append(`
									<input type="hidden" name="_upd${i}_u_lote_idlote" value=${lote[0].idlote}>
									<input type="hidden" name="_upd${i}_u_lote_orgao" value="">
								`);
							}
						}
					}

					$(this).closest('.global').remove();
					ind--;
					var qt = document.getElementById('qtTeste').value = ind;
					if (qt == 0) {
						qt = document.getElementById('qtTeste').value = auxQt;

						CB.post();
					}
				});
			}
		}

		//FUNÇÃO CRIADA PARA CONVERTER TODOS OS INPUTS DINÂMICOS EM JSON E ARMAZENAR NA VARIÁVEL CB
		function InputsToJsonField() {
			//TIPO DINÂMICO INDIVIDUAL
			var arrObj = [];
			var individual = '';
			var qtdindividual = $('.interna2').length - 1;
			$('[data-vinculo="INDIVIDUAL"]').each(function(index) {
				var valor = "";
				var calculoperc = "";
				var input = $(this);
				//Validação para os campos tipo select para pdoer trazer o valor selecionado
				if ($(input).is("select")) {
					valor = input.val();
					if (input.children("option:selected").attr('calcula') == "Y") {
						calculoperc = "Y";
					} else {
						calculoperc = "N";
					}
				}
				if (input.attr('type') == 'checkbox') {
					valor = input.is(':checked');
				} else {
					valor = input.val().replace(/"/g, '&quot;');
				}
				//Alteração para gravar se o campo é de calculo ou não
				arrObj.push({
					type: input.attr("type"),
					indice: input.attr("data-indice"),
					titulo: input.attr("data-titulo"),
					calculo: input.attr("data-calculo"),
					name: input.attr("name"),
					value: valor,
					calculoop: calculoperc
				});
			});

			var jsonObj = new Object();
			jsonObj["INDIVIDUAL"] = arrObj;
			jsonObj["QTDINDIVIDUAL"] = qtdindividual;
			var arrObj = [];

			//TIPO DINÂMICO AGRUPADO
			$('[data-vinculo="AGRUPADO"]').each(

				function(index) {
					var input = $(this);
					var valor = "";
					if (input.attr('type') == 'checkbox') {
						valor = input.is(':checked');
					} else {
						valor = input.val().replace(/"/g, '&quot;');
					}

					arrObj.push({
						type: input.attr("type"),
						indice: input.attr("data-indice"),
						titulo: input.attr("data-titulo"),
						name: input.attr("name"),
						value: valor
					});

				}
			);

			jsonObj["AGRUPADO"] = arrObj;

			//JUNTAR AS ARRAYS E TRANSFORMAR EM JSON
			myJsonString = JSON.stringify(jsonObj);
			console.log(myJsonString);
			//ATRIBUIR AO CAMPO HIDDEN DO INPUT CB
			$("[name=_1_" + CB.acao + "_resultado_jsonresultado]").val(myJsonString);
		}


		//INCLUSÃO DINÂMICA DO TINYMCE NOS CAMPOS DINÂMICOS DO TIPO TEXTAREA
		sSeletor = '.diveditor';

		if (tinyMCE.editors["diveditor"]) {
			tinyMCE.editors["diveditor"].remove();
		}


		tinyMCE.init({
			selector: sSeletor,
			language: 'pt_BR',
			inline: true /* não usar iframe */ ,
			toolbar: 'bold | subscript superscript | bullist numlist | table',
			menubar: false,
			plugins: ['table', 'autoresize'],
			setup: function(editor) {
				editor.on('init', function(e) {
					$(":text [name='" + this.bodyElement.getAttribute('name') + "']").val(this.bodyElement.attributes.contenteditable.ownerElement.innerHTML);
				}).on('change', function(e) {
					$(":text [name='" + this.bodyElement.getAttribute('name') + "']").val(this.bodyElement.attributes.contenteditable.ownerElement.innerHTML);
				});
			},
			entity_encoding: 'raw'
		});

		//PREENCHIMENTO DE TODOS OS CAMPOS DINÂMICOS COM SEUS RESPECTIVOS VALORES
		//DE ACORDO COM AS INFORMAÇÕES ARMAZENADAS NO JSON
		if (objJsonReturn) {

			$.each(objJsonReturn, function(index, value) {

				$.each(value, function(i, v) {
					let name = v['name'];
					const campoJQ = $('input[name="' + name + '"]');
					//SE TIPO INPUT
					if ($('input').is('[name="' + name + '"]')) {
						//SE TIPO TEXT
						if (v['type'] == 'text') {
							campoJQ.attr('value', v['value']);
							if (campoJQ.next().hasClass('resultado')) campoJQ.next().html(v['value']);
							//SE TIPO CHECKBOX
						} else if (v['type'] == 'checkbox') {
							console.log(v['value']);
							if (v['value'] == true) {
								campoJQ.prop('checked', true);
							}
						} else {
							//SE TIPO OUTROS
							campoJQ.val(v['value']);
						}
						//SE TIPO SELECT
					} else if ($('select').is('[name="' + name + '"]')) {
						$('select[name="' + name + '" ] option[value="' + v['value'] + '"]').attr("selected", "selected");
						//SE TIPO TEXTAREA
					} else if ($('textarea').is('[name="' + name + '"]')) {
						//ATUALIZA TEXTAREA          
						$('textarea[name="' + name + '"]').html(v['value']);
						namediv = name.replace('campo', 'campoeditor');
						//ATUALIZA DIV TYNEMCE
						$('div[name="' + namediv + '"]').html(v['value']);
					}
					name = "";
				});
			});
		}
		//Antes de salvar atualiza o textarea
		CB.on('prePost', function() {
			if ($('div[data-tipo="divhtml"]').attr('name')) {
				namedoobjeto = $('div[data-tipo="divhtml"]').attr('name');
				$('div[data-tipo="divhtml"]').each(function(index, element) {
					var nome = $(this).attr('name').replace('editor', '');
					$('textarea[name="' + nome + '"]').val(element.innerHTML);
				});

			}
			InputsToJsonField();
		})

		var c = 1;

		if (vFixo == '1') {
			while (c <= vQtdfixo) {
				$('[name=' + c + '_campo_' + vFixoindice + '] option').eq(c).attr('selected', true);
				c = c + 1;
			}
		}

	} else if (vModelo == "SELETIVO" && vModo == "AGRUP") {
		document.onkeypress = capkey;
		arrKeyConf[48] = 1;
		arrKeyConf[49] = 2;
		arrKeyConf[50] = 3;
		arrKeyConf[51] = 4;
		arrKeyConf[52] = 5;
		arrKeyConf[53] = 6;
		arrKeyConf[54] = 7;
		arrKeyConf[55] = 8;
		arrKeyConf[56] = 9;
		arrKeyConf[57] = 10;
		arrKeyConf[47] = 11;
		arrKeyConf[42] = 12;
		arrKeyConf[45] = 13;

	} else if (vModelo == "UPLOAD") {
		$("#resultadoelisa").dropzone({
			idObjeto: idResultado,
			tipoObjeto: 'resultado',
			tipoArquivo: 'RESULTADOELISA',
			tipoKit: $("[name=tipokit]").val(),
			sending: function(file, xhr, formData) {
				//Ajusta parametros antes de enviar via post
				formData.append("tipokit", this.options.tipoKit);
			}
		});

	}


	function retsomax() {

		//soma as quantidades dos inputs (orificios)
		var total = 0;
		$("#tbOrificios input[id^=k_]").each(function() {
			total += Number($(this).val());
		});

		//Verifica se é maior que a quantidade de testes estipulada
		if ((total + 1) <= qtx) {
			$("#somaorificios").html(total + 1);
			return true;
		} else {
			return false;
		}
	}



	function setoper(inoper) {
		xoper = inoper;
		console.log("Operação de cálculo alterada para [" + inoper + "]");
	}




	function capkey(e) {

		teclaPressionada = retkey(e);

		iInput = arrKeyConf[teclaPressionada];

		if (iInput && (document.activeElement.name === undefined || document.activeElement.name == 'xoper')) {
			pageStateChanged = true;

			$objx = $("#k_" + iInput);

			if ($objx.length == 0) {
				console.log('Objeto [' + idobjx + '] não encontrado');
				return;
			}

			if (xoper == "+" && $("#qtdteste").is(":focus") == false) {
				if (retsomax()) { //Verifica se a quant maxima foi atingida
					$objx.val(parseInt($objx.val()) + 1);
					return false;
				} else {
					alertAtencao("A Quantidade total de [" + qtx + "] testes  foi atingida!", null, "3000");
					return false;
				}
			} else if (xoper == "-" && $("#qtdteste").is(":focus") == false) {
				if (parseInt($objx.val()) > 0) { //Verifica se a quant maxima foi atingida
					$objx.val(parseInt($objx.val()) - 1);
					$("#somaorificios").html(Number($("#somaorificios").html()) - 1);
					return false;
				} else {
					window.status = "Limite inferior [0] atingido...";
					return false;
				}
			} else if ($("#qtdteste").is(":focus") == false) {
				alert("Valor para Operação (+ ou -) não ajustado!\n Impossível calcular orifícios.");
			}
		}
	}


	function adicionarTestes() {

		let jPost = {};
		$.each($("#tbTestes .formTmp"), function(i, o) {
			let $o = $(o); //Transforma o elemento HTML em obj Jquery
			let aName = $o.attr("name").split("#"); //Transforma o name em um array

			if ($o.attr('cbvalue')) {
				jPost["_" + aName[0] + "_i_resultado_" + aName[1]] = $o.attr('cbvalue'); //Monta o obj key/value com os valores dos elementos
			} else {
				jPost["_" + aName[0] + "_i_resultado_" + aName[1]] = $o.val(); //Monta o obj key/value com os valores dos elementos
			}

			jPost["_" + aName[0] + "_i_resultado_idfluxostatus"] = idfluxostatus; //Monta o obj key/value com os valores dos elementos

			if (idempresa) {
				jPost["_" + aName[0] + "_i_resultado_idempresa"] = idempresa; //Monta o obj key/value com os valores dos elementos
			}
		});

		console.log(jPost);

		CB.post({
			objetos: jPost,
			parcial: true,
			msgSalvo: "Teste(s) adicionado(s)"
		});
	}


	function novoTeste() {

		oTbTestes = $("#tbTestes");
		iNovoTeste = (oTbTestes.find("input.idprodserv").length + 1);
		htmlTrModelo = $("#modeloNovoTeste").html();
		htmlTrModelo = htmlTrModelo.replace("#nameidamostra", "" + iNovoTeste + "#idamostra");
		htmlTrModelo = htmlTrModelo.replace("#namestatus", "" + iNovoTeste + "#status");
		htmlTrModelo = htmlTrModelo.replace("#nameidtipoteste", "" + iNovoTeste + "#idtipoteste");
		htmlTrModelo = htmlTrModelo.replace("#namequantidade", "" + iNovoTeste + "#quantidade");
		htmlTrModelo = htmlTrModelo.replace("#nameidsecretaria", "" + iNovoTeste + "#idsecretaria");
		htmlTrModelo = htmlTrModelo.replace(/#irow/g, iNovoTeste);

		novoTr = "<div>" + htmlTrModelo + "</div>";
		oTbTestes.append(novoTr);
		criaAutocompletesTestes();
		$("#novoTestesalvar").css("display", "inline-block");

		//Autocomplete de Servicos (testes)
		function criaAutocompletesTestes() {
			$("#tbTestes .idprodserv").autocomplete({
				source: jsonServicos,
				delay: 0
			});
		}

	}



	function verificarEIncluirServicosVinculados(inIdresultado, inChk) {

	let sTipoalerta = $("[name=_1_u_resultado_tipoalerta]").val();
	let sTipoagente = $("[name=_1_u_resultado_tipoagente]").val();

	if ($("#chAlerta").is(':checked')) {

		CB.post({
			"objetos": "_x_u_resultado_idresultado=" + inIdresultado + "&_x_u_resultado_alerta=Y&_x_u_resultado_tipoalerta=" + sTipoalerta + "&_x_u_resultado_alerta=Y&_x_u_resultado_tipoagente=" + sTipoagente + "&_x_u_resultado_positividade=" + $("[name='_1_u_resultado_positividade']").val(),
			parcial: true,
			posPost: () => {
				if (vinculados.length) {
					alert("" + vinculados.length + " serviço(s) vínculado(s) a esse resultado positivo serão incluídos.");
					array = [];
					vinculados.map((obj, i) => {
						i = i + 1;
						array["_" + i + "_i_resultado_idamostra"] = idAmostra;
						array["_" + i + "_i_resultado_idtipoteste"] = obj.idobjeto;
						array["_" + i + "_i_resultado_quantidade"] = $("[name='_1_u_resultado_positividade']").val() || 1;
						array["_" + i + "_i_resultado_positividade"] = $("[name='_1_u_resultado_positividade']").val() || 0;
						array["_" + i + "_i_resultado_idempresa"] = idempresa;
						array["_" + i + "_i_resultado_idfluxostatus"] = idfluxostatus;
						array["_" + i + "_i_resultado_status"] = 'ABERTO';
						array["_" + i + "_i_resultado_jsonresultado"] = '{}';
						if(obj.oficial == 'Y'){
							array["_" + i + "_i_resultado_idsecretaria"] = $("#idsecretaria").val() || 0;
						}

					});

					console.log(array);
					CB.post({
						"objetos": $.extend({}, array),
						parcial: true
					});
				}
			}
		});
	} else {
		CB.post({
			"objetos": "_x_u_resultado_idresultado=" + inIdresultado + "&_x_u_resultado_alerta=N&_x_u_resultado_tipoalerta=" + sTipoalerta + "&_x_u_resultado_alerta=N&_x_u_resultado_tipoagente=" + sTipoagente,
			parcial: true
		});
	}
	}


	function copyToClipboard(element) {
		var $temp = $("<input>");
		$("body").append($temp);
		$temp.val($(element).text()).select();
		document.execCommand("copy");
		$temp.remove();
		alertAzul('Copiado para a área de trabalho &nbsp&nbsp');
	}

	function change_arquivo() {
		if ($('#tipokit').val() == 'IDEXX-LAUDO' || $('#tipokit').val() == 'IDEXX') {
			$('#nome_arquivo_txt').hide();
			$('#nome_arquivo_rtf').show();
		} else {
			$('#nome_arquivo_txt').show();
			$('#nome_arquivo_rtf').hide();
		}
	}
	$('#tipokit').on('change', () => {
		change_arquivo();
	});

	change_arquivo();



	//Mostra Insumo no Resultado
	function mostraInsumo(inIdresultado, inChk) {

		if ($("#chMostraInsumo").is(':checked')) {
			CB.post({
				"objetos": "_x_u_resultado_idresultado=" + inIdresultado + "&_x_u_resultado_mostraformulains=Y",
				parcial: true
			});
		} else {
			CB.post({
				"objetos": "_x_u_resultado_idresultado=" + inIdresultado + "&_x_u_resultado_mostraformulains=N",
				parcial: true
			});
		}
	}




	//Conforme o tipo do teste prepara a tela para reagir a funções/comandos específicos
	CB.preLoadUrl = function() {
		//Como o carregamento é via ajax, os popups ficavam aparecendo após o load
		$(".webui-popover").remove();
	}

	$(".oTeste").webuiPopover({
		trigger: "hover",
		placement: "right",
		delay: {
			show: 300,
			hide: 0
		}
	});



	window.onbeforeunload = testPageState;

	function testPageState() {
		if ((typeof(pageStateChanged) != "undefined") && (pageStateChanged)) {
			mess = "***********************************************************\n\nAS INFORMAÇÕES NÃO FORAM SALVAS AINDA!\n DESEJA REALMENTE SAIR SEM SALVAR?\n\n***********************************************************";
			return mess;
		}
	}

	function vincularTagAoTeste(idresultado, vthis) {

		var cbpost = "";
		arraycheck = $(vthis).parent().parent().parent().find(":checkbox").each((i, o) => {
			if ($(o).prop("checked") == true && $(o).attr("idobjetovinculo") == "") {
				cbpost += `_x${i}_i_objetovinculo_idobjeto=${idresultado}
							&_x${i}_i_objetovinculo_tipoobjeto=resultado
							&_x${i}_i_objetovinculo_idobjetovinc=${$(o).attr("idtag")}
							&_x${i}_i_objetovinculo_tipoobjetovinc=tag&`;
			} else if ($(o).prop("checked") == false && $(o).attr("idobjetovinculo") != "") {
				cbpost += `_xd${i}_d_objetovinculo_idobjetovinculo=${$(o).attr("idobjetovinculo")}&`;
			} else {
				cbpost += "";
			}
		});

		if (cbpost != "") {
			CB.post({
				"objetos": cbpost,
				parcial: true
			});
		}
	}



	function inovolote(inidresultado) {

		var strCabecalho = "</strong>NOVO AGENTE <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='criaragente();'><i class='fa fa-circle'></i>Salvar</button></strong>";
		var htmloriginal = $("#novolote").html();
		var objfrm = $(htmloriginal);

		objfrm.find("#idlotelote").attr("name", "_x_i_lote_idlote");
		objfrm.find("#idprodservlote").attr("name", "_x_i_lote_idprodserv");

		objfrm.find("#exerciciolote").attr("name", "_x_i_lote_exercicio");
		objfrm.find("#statuslote").attr("name", "_x_i_lote_status");
		objfrm.find("#qtdprod").attr("name", "_x_i_lote_qtdprod");
		objfrm.find("#idunidadegplote").attr("name", "_x_i_lote_idunidade");

		let tipoAmostra = $(`[data-titulo="TIPO DE AMOSTRA"]`);
		if (tipoAmostra.length > 0) {
			var arrTipoAmostra = false;
			var $opt = "";
			tipoAmostra.each((i, o) => {
				if (o.value != "" && $(`[data-titulo="RESULTADO"][data-indice="${$(o).attr('data-indice')}"]`).val() == "POSITIVO" && $(`[data-titulo="SEMENTE"][data-indice="${$(o).attr('data-indice')}"]`).val() == "") {
					arrTipoAmostra = true;
					let indice = $(o).attr('data-indice')
					$opt += `<option indice=${indice} value="${o.value}">${o.value}</option>`;
				}
			});
			let content = `<select id="orgao" name="_x_i_lote_orgao"><option></option>${$opt}</select>`;

			if (arrTipoAmostra) {
				$(content).insertAfter(objfrm.find("#orgao"));
				objfrm.find("input#orgao").remove();
			} else {
				objfrm.find("#orgao").attr("name", "_x_i_lote_orgao");
			}

		} else {
			objfrm.find("#orgao").attr("name", "_x_i_lote_orgao");
		}

		objfrm.find("#idobjetolote").attr("name", "_x_i_lote_idobjetosolipor");
		objfrm.find("#idobjetolote").attr("value", inidresultado);

		objfrm.find("#tipoobjetolote").attr("name", "_x_i_lote_tipoobjetosolipor");

		CB.modal({
			titulo: strCabecalho,
			corpo: [objfrm],
		});

	}


	function listalote(inidresultado) {
		var strCabecalho = "</strong>AGENTE(S)</strong>";
		$("#cbModalTitulo").html((strCabecalho));

		var htmloriginal = $("#resultadoagente" + inidresultado).html();
		var objfrm = $(htmloriginal);
		$("#cbModalCorpo").html(objfrm.html());
		$('#cbModal').modal('show');

	}



	function criaragente(razaosocial) {

		$.ajax({
			type: "get",
			url: "ajax/empresa.php",
			dataType: "json",
			data: {
				"action": 'buscarRazaoSocial',
				"params": idempresa
			},
			success: res => {

				//LTM - 05-05-2021 - Retorna o idFluxoStatus Selecionado
				var idfluxostatus = getIdFluxoStatus('semente', 'ABERTO');
				var str = "_x_i_lote_idprodserv=" + $("[name=_x_i_lote_idprodserv]").val() +
					"&_x_i_lote_status=ABERTO" +
					"&_x_i_lote_fabricante=" + res.razaosocial +
					"&_x_i_lote_idempresa=" + idempresa +
					"&_x_i_lote_idfluxostatus=" + idfluxostatus +
					"&_x_i_lote_exercicio=" + $("[name=_x_i_lote_exercicio]").val() +
					"&_x_i_lote_idunidade=" + $("[name=_x_i_lote_idunidade]").val() +
					"&_x_i_lote_orgao=" + $("[name=_x_i_lote_orgao]").val() +
					"&_x_i_lote_qtdprod=" + $("[name=_x_i_lote_qtdprod]").val() +
					"&_x_i_lote_qtdpedida=" + $("[name=_x_i_lote_qtdprod]").val() +
					"&_x_i_lote_tipoobjetosolipor=" + $("[name=_x_i_lote_tipoobjetosolipor]").val() +
					"&_x_i_lote_idobjetosolipor=" + $("[name=_x_i_lote_idobjetosolipor]").val();

				var orgao = $("[name=_x_i_lote_orgao]");
				var indice = orgao.children(`[value="${orgao.val()}"]`).attr('indice');

				CB.post({
					objetos: str,
					parcial: true,
					posPost: function(resp, status, ajax) {
						if (status = "success") {

							$.post(`./ajax/resultados_ajax.php?_buscaragentes=Y&_idresultado=${idResultado}`, function(report) {
								jArrAgentes = report;
							});

							var a = jArrAgentes.filter((o, i) => {
								return o.idlote == ajax.getResponseHeader('x-cb-pkid')
							});


							$("#cbModalCorpo").html("");
							$('#cbModal').modal('hide');
							if (indice && a[0]) {
								$(`[data-titulo="SEMENTE"][data-indice="${indice}"]`).val(a[0].agente + "/" + a[0].exercicio);
								CB.post();
							}
						} else {
							alert(resp);
						}
					}
				});
				// criaragente
			}
		})


	}

	if ($("[name=_1_u_resultado_idresultado]").val()) {
		$("#arquivoresultado").dropzone({
			idObjeto: $("[name=_1_u_resultado_idresultado]").val(),
			tipoObjeto: 'resultado'
		});
	}



	function setresultadoind(vid) {

		var tecla = parseInt($('#tecla' + vid).val());
		var valor = tecla + 1;

		document.getElementById('resultado' + vid).value = valor;
		document.getElementById('rotulo' + vid).value = valor;
	}


	function atualizainput(inlinha) {

		$("[name=" + inlinha + "idlote]").attr('name', '_' + inlinha + '_i_lotecons_idlote');
		$("[name=" + inlinha + "idlotefracao]").attr('name', '_' + inlinha + '_i_lotecons_idlotefracao');
		$("[name=" + inlinha + "tipoobjeto]").attr('name', '_' + inlinha + '_i_lotecons_tipoobjeto');
		$("[name=" + inlinha + "idobjeto]").attr('name', '_' + inlinha + '_i_lotecons_idobjeto');
		$("[name=" + inlinha + "qtdd]").attr('name', '_' + inlinha + '_i_lotecons_qtdd');
		$("[name=" + inlinha + "tipoobjetoconsumoespec]").attr('name', '_' + inlinha + '_i_lotecons_tipoobjetoconsumoespec');
		$("[name=" + inlinha + "idobjetoconsumoespec]").attr('name', '_' + inlinha + '_i_lotecons_idobjetoconsumoespec');
	}


	function esgotarlote(inIdlotefracao) {

		if (confirm("Deseja realmente esgotar o lote?")) {
			CB.post({
				"objetos": "_x_u_lotefracao_idlotefracao=" + inIdlotefracao + "&_x_u_lotefracao_status=ESGOTADO&_x_u_lotefracao_qtd=0&&_x_u_lotefracao_qtd_exp=0",
				parcial: true
			});
		}
	}


	function mostraConsumo(inOConsumo) {

		$oc = $(inOConsumo);
		$tbInsumo = $oc.closest("table");
		somaUtilizacao = 0;
		$trInsumo = $oc.closest("tr.trInsumo");
		$sRestante = $trInsumo.find(".sRestante");
		$sQtdpadrao = $trInsumo.find(".sQtdpadrao");
		$oConsumos = $trInsumo.find("[name*=_qtdd]");
		$sUtilizando = $trInsumo.find(".sUtilizando");

		$.each($oConsumos, function(isc, osc) {

			var $o = $(osc);

			if ($o.val()) {

				if ($o.attr("cbqtddispexp") != "" && ($o.val().toLowerCase().indexOf("e") <= 0 && $o.val().toLowerCase().indexOf("d") <= 0)) {
					alertAtencao("Valor inválido. <br> Inserir e ou d.");
					return false;
				}

				valor = $o.val().replace(/,/g, '.');
				valor = normalizaQtd(valor);

				somaUtilizacao += valor;
			}

		});

		qtdPadrao = normalizaQtd($sQtdpadrao.html());

		if (somaUtilizacao >= qtdPadrao) {
			sclass = "fundoverde";
		} else {
			sclass = "fundolaranja";
		}

		if (somaUtilizacao > 0) {
			//Formata o badge de 'utilizando'
			$sUtilizando
				.html(somaUtilizacao)
				.removeClass("fundoverde")
				.removeClass("fundolaranja")
				.addClass(sclass)
				.attr("title", (somaUtilizacao / qtdPadrao) * 100 + "%");
		} else { //zero ou vazio
			//Formata o badge de 'utilizando'
			$sUtilizando
				.html(somaUtilizacao)
				.removeClass("fundoverde")
				.removeClass("fundolaranja")
				.attr("title", (somaUtilizacao / qtdPadrao) * 100 + "%");
		}

		$sRestante
			.html(qtdPadrao - somaUtilizacao)
			.removeClass("fundoverde")
			.removeClass("fundolaranja")
			.addClass(sclass);

	}



	function normalizaQtd(inValor) {

		var sVlr = "" + inValor;
		var $arrExp;
		var fVlr;

		if (sVlr.toLowerCase().indexOf("d") > -1) {
			$arrExp = sVlr.toLowerCase().split('d');
			fVlr = (parseFloat($arrExp[0]) * parseFloat($arrExp[1])).toFixed(2);
			fVlr = parseFloat(fVlr);
		} else if (sVlr.toLowerCase().indexOf("e") > -1) {
			$arrExp = sVlr.toLowerCase().split('e');
			fVlr = $arrExp[0] * Math.pow(10, $arrExp[1]);
		} else {
			fVlr = parseFloat(sVlr).toFixed(2);
		}

		return parseFloat(fVlr);
	}



	function settipokit(vthis, inidres) {
		CB.post({
			objetos: `_x_u_resultado_idresultado=` + inidres + `&_x_u_resultado_tipokit=` + $(vthis).val(),
			parcial: true
		});
	}


	function dfase(inidresultadoprodservformula) {
		CB.post({
			objetos: `_x_u_resultadoprodservformula_idresultadoprodservformula=` + inidresultadoprodservformula + `&_x_u_resultadoprodservformula_status=INATIVO`,
			parcial: true
		});
	}


	function ifase(inidresultado, inidprodservformula) {
		CB.post({
			objetos: `_x_i_resultadoprodservformula_idresultado=` + inidresultado + `&_x_i_resultadoprodservformula_idprodservformula=` + inidprodservformula,
			parcial: true
		});
	}

	if (jArrAgentes != 0) {
		if (jArrAgentes.length > 0) {

			$(`[data-titulo="SEMENTE"]`).each((i, o) => {
				let name = $(o).attr('name');
				let indice = $(o).attr('data-indice');
				let tipo = $(o).attr('data-tipo');
				let titulo = $(o).attr('data-titulo');
				let vinculo = $(o).attr('data-vinculo');
				let calculo = $(o).attr('data-calculo');
				let type = $(o).attr('type');
				let value = $(o).val();
				let selected;
				if (value && $(`[data-titulo="RESULTADO"][data-indice="${indice}"]`).val() == "POSITIVO") {
					$(`[data-titulo="RESULTADO"][data-indice="${indice}"]`).attr("readonly", "readonly")
				}
				if (value == "" && $(`[data-titulo="RESULTADO"][data-indice="${indice}"]`).val() == "POSITIVO" && $(`[data-titulo="TIPO DE AMOSTRA"][data-indice="${indice}"]`).val() != "") {
					$opt = `<option value = ""></option>`;
					var orgao = $(`[data-titulo="TIPO DE AMOSTRA"][data-indice="${indice}"]`).val()
					for (let a of jArrAgentes) {

						$opt += `<option tipificacao="${a.tipificacao}" idlote="${a.idlote}" value = "${a.agente}/${a.exercicio}">${a.agente}/${a.exercicio}</option>`;

					}

					let $oContent = $(`<select name = "${name}" data-indice = "${indice}" type = "${type}" data-tipo = "${tipo}" data-titulo = "${titulo}" data-vinculo = "${vinculo}" data-calculo = "${calculo}">
							${$opt}
						</select>`).on('change', function() {
						if (this.value != "") {
							let option = $(this).children(`[value="${this.value}"]`);
							if ($(`[data-titulo="TIPIFICAÇÃO"][data-indice="${indice}"]`)) {

								$(`[data-titulo="TIPIFICAÇÃO"][data-indice="${indice}"]`).val($(this).children(`[value="${this.value}"]`).attr('tipificacao'));

							}

							if ($('div[data-tipo="divhtml"]').attr('name')) {
								namedoobjeto = $('div[data-tipo="divhtml"]').attr('name');
								$('div[data-tipo="divhtml"]').each(function(index, element) {
									var nome = $(this).attr('name').replace('editor', '');
									$('textarea[name="' + nome + '"]').val(element.innerHTML);
								});
							}
							InputsToJsonField();
							CB.post({
								objetos: {
									"_x_u_lote_idlote": option.attr('idlote'),
									"_x_u_lote_orgao": orgao
								},
							});
						}
					});

					$(o).parent().append($oContent);
					$(o).remove();

				} else if (value != "" && $(`[data-titulo="RESULTADO"][data-indice="${indice}"]`).val() == "POSITIVO") {
					$(o).on('keyup', {
						lote: value
					}, function(e) {
						if (this.value == "") {
							if (confirm("Deseja realmente retirar essa semente?")) {
								let lote = jArrAgentes.filter((o, i) => {
									return o.agente + "/" + o.exercicio == e.data.lote
								});

								if (lote.length > 0) {
									if ($('div[data-tipo="divhtml"]').attr('name')) {
										namedoobjeto = $('div[data-tipo="divhtml"]').attr('name');
										$('div[data-tipo="divhtml"]').each(function(index, element) {
											var nome = $(this).attr('name').replace('editor', '');
											$('textarea[name="' + nome + '"]').val(element.innerHTML);
										});

									}
									if ($(`[data-titulo="TIPIFICAÇÃO"][data-indice="${indice}"]`)) {

										$(`[data-titulo="TIPIFICAÇÃO"][data-indice="${indice}"]`).val('');

									}
									InputsToJsonField();
									CB.post({
										objetos: {
											"_x_u_lote_idlote": lote[0].idlote,
											"_x_u_lote_orgao": ""
										},
									});
								}
							} else {
								this.value = e.data.lote;
							}
						}
					})
				}

			});
		}
	}

	if (Object.keys(objJsonReturn).length == 0) {
		$('.global').find('select').each(function(i, o) {
			indice = $(o).attr('indice-campo');
			$(o).find(`.aplicarvazio${indice}`).val('');
		});
	}

	<? if ($_acao != 'i') { ?>
		CB.on("posPost", function(data) {
			if (data.jqXHR.getResponseHeader('positivos')) {
				let numeroResultadosPositivos = JSON.parse(data.jqXHR.getResponseHeader('positivos'));
				console.log(numeroResultadosPositivos);

				if (numeroResultadosPositivos > 0) {
					alertAtencao(`Existem ${numeroResultadosPositivos} resultado(s) positivo(s). Favor marcar o Flag de Alerta`);
				}
			}
		});
	<? } ?>

	function validaCampo(element) {
		const elementJQ = $(element);
		const resultadoJQ = $(element).parent().parent().find('.resultado');
		const resultadoInput = resultadoJQ.prev();
		const camposConfig = jsonConfig.unidadeBloco ?? [];

		camposConfig.forEach(campos => {
			if (campos.index == elementJQ.data('index')) {
				campos.personalizados.forEach(personalizado => {
					if (personalizado.indice == elementJQ.data('indicevalidacao')) {
						const parametros = personalizado.options.find(item => item.nome == elementJQ.parent().parent().find(`[indice-campo="${personalizado.indice}"]`).val()).parametros;
						const resultado = parametros.find(param => {
							if (param.valorinicial && !param.valorfinal)
								return parseInt(elementJQ.val()) >= parseInt(param.valorinicial)

							return valorEstaEntre(elementJQ.val(), param.valorinicial, param.valorfinal)
						})?.resultado ?? 'Não encontrado';

						resultadoJQ.html(resultado)
						resultadoInput.val(resultado)
					}
				})
			}
		})
	}

	function valorEstaEntre(valor, valorInicial, valorFinal) {
		return valor >= Math.min(parseInt(valorInicial), parseInt(valorFinal)) && valor <= Math.max(parseInt(valorInicial), parseInt(valorFinal));
	}

	function validarCampoResultado(element = null) {
		const elements = element ? [element] : $('[data-titulo="RESULTADO"]').get();

		elements.forEach((element) => {
			const campoTipificacaoJQ = $(`[data-titulo="TIPIFICAÇÃO"][data-indice=${$(element).data('indice')}]`),
				campoSemente = $(`[data-titulo="SEMENTE"][data-indice=${$(element).data('indice')}]`);

			if($(element).val().toLowerCase() == 'negativo') {
				$(`[data-titulo="TIPIFICAÇÃO"][data-indice=${$(element).data('indice')}], [data-titulo="SEMENTE"][data-indice=${$(element).data('indice')}]`)
					.attr('disabled', true)
					.attr('title', 'Resultado ou negativo')
					.val('');

				verificaraHabilitarBotoesStatus('', true);
				campoSemente.removeClass('positivo-semente-sem-valor');
			} else if(statusResultado == 'AGUARDANDO' && !$(element).val()) {				
				$(`[data-titulo="TIPIFICAÇÃO"][data-indice=${$(element).data('indice')}], [data-titulo="SEMENTE"][data-indice=${$(element).data('indice')}]`)
					.attr('disabled', true)
					.attr('title', 'Resultado vazio')
					.val('');

				verificaraHabilitarBotoesStatus('', false, 'Campo resultado é obrigatório');
			} else if($(element).val().toLowerCase() == 'positivo') {
				// $(`[data-titulo="TIPIFICAÇÃO"][data-indice=${$(element).data('indice')}]`)
				// 	.attr('required', true)
				// 	.attr('vnulo', true);

				$(`[data-titulo="TIPIFICAÇÃO"][data-indice=${$(element).data('indice')}], [data-titulo="SEMENTE"][data-indice=${$(element).data('indice')}]`)
					.removeAttr('disabled')
					.removeAttr('title');

				// Desabilitando botoes de status
				if($(`[data-titulo="TIPIFICAÇÃO"]`).length) verificaraHabilitarBotoesStatus(campoTipificacaoJQ.val(), );

				if(!campoSemente.val()) campoSemente.addClass('positivo-semente-sem-valor');
				else campoSemente.removeClass('positivo-semente-sem-valor');
			} else {
				$(`[data-titulo="TIPIFICAÇÃO"][data-indice=${$(element).data('indice')}], [data-titulo="SEMENTE"][data-indice=${$(element).data('indice')}]`)
					.removeAttr('disabled')
					.removeAttr('title')
					.removeAttr('required', true)
					.removeAttr('vnulo', true);
			}
		});
	}

	function verificaraHabilitarBotoesStatus(val, ignoraValidacao = false, labelValidacao = 'Campo Tipificação é obrigatório') {
		$('#info-btn').remove();

		const btnFechar = $('#fluxostatus_889'),
			iconeInfo = $("<i id='info-btn' class='fa fa-info' title='"+labelValidacao+"'></i>");

		if(!val && !ignoraValidacao) {
			btnFechar.attr('disabled', true);

			btnFechar.parent().append(iconeInfo);
		}
		else {
			btnFechar.removeAttr('title');
			btnFechar.removeAttr('disabled');
			$("#info-btn").remove();
		};
	}

	if(idempresa == 2 && $("[data-titulo='TIPIFICAÇÃO'], [data-titulo='SEMENTE']").length) {
		validarCampoResultado();

		$('[data-titulo="RESULTADO"]').on('change', function() {
			
			if(($(this).data('valororiginal') == 'POSITIVO' && $(this).val() == 'NEGATIVO' || (!$(this).val())) && ($(`[data-titulo='TIPIFICAÇÃO'][data-indice=${$(this).data('indice')}]`).val()) || $(`[data-titulo='SEMENTE'][data-indice=${$(this).data('indice')}]`).val()) {
				$(this).val($(this).data('valororiginal'));
				return alertAtencao('"espécie/tipificação" e "Semente" estão preenchidas');
			}

			validarCampoResultado(this);
		});

		$('[data-titulo="TIPIFICAÇÃO"]').on('change', function() {
			verificaraHabilitarBotoesStatus($(this).val());
		});
	};

	if(idempresa == 2) {
		CB.on('prePost', function() {
			const tipoTeste = $('#tipo-teste').text();

			// if(statusResultado == 'AGUARDANDO' && (tipoTeste.toLocaleLowerCase().includes('antibiograma') || tipoTeste.toLocaleLowerCase().includes('tipificação'))) {
			// 	const resultadosJQ = $('[data-titulo="RESULTADO"]').get();
			// 	const resultadosVazioJQ = resultadosJQ.filter(item => !item.value);

			// 	$(resultadosJQ).removeClass('alertaCbvalidacao');
				
			// 	if(resultadosVazioJQ.length) {
			// 		$(resultadosVazioJQ).addClass('alertaCbvalidacao');
			// 		alertAtencao('Campo de resultado obrigatório');

			// 		return false;
			// 	}
			// }

			if($('.positivo-semente-sem-valor').length) {
				if(!confirm("Há resultados positivos sem sementes! Gostaria de prosseguir?")) return false;
			}

			if(statusResultado == 'AGUARDANDO' && $('[data-titulo="RESULTADO"]').get().some(item => item.value == 'POSITIVO') && !$('#chAlerta:checked').length) {
				alertAtencao("Há resultados positivos. É necessário marcar a flag 'Alerta'");

				return false;
			}
		});
	}
	

	// adicionar rodapé em todos os JS de forms p/ ser possível debuggar em produção
	//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
	// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||| 17/02/2021 PEDRO LIMA |||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
</script> 