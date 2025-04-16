<script src="./inc/js/amcharts/amcharts.js"></script>
<script src="./inc/js/amcharts/serial.js"></script>
<script>
	//------- Injeção PHP no Jquery -------
	const historicoCampoVlrVenda = <?= json_encode($listaHistoricoVlrVenda) ?>;
	var idprodserv = '<?= $_1_u_prodserv_idprodserv ?>';
	var idpessoa = '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>';
	var cbIdEmpresa = '<?= cb::idempresa(); ?>';
	var prodservTipo = '<?= $_1_u_prodserv_tipo ?>';
	var statusProdsev = '<?= $_1_u_prodserv_status ?>';
	var arraygrafc = <?= json_encode($arrgrafc) ?> || [];
	var atividadesDisponiveisParaVinculo = <?= json_encode($atividadesDisponiveisParaVinculo) ?>;
	var PlanteisLigados = <?= ProdServController::toJson(PlantelController::listarPlantelPorIdobjetoTipoobjetoProdservAtiva($_1_u_prodserv_idprodserv, 'prodserv')) ?>;
	<?
	if ($_1_u_prodserv_idprodserv) {
		$listarProdutosVinculados = json_encode(ProdServController::listarProdutosVinculados('PRODUTO', $_1_u_prodserv_idprodserv));
		$listarServicosVinculados = json_encode(ProdServController::listarServicosVinculados('SERVICO', $_1_u_prodserv_idprodserv));
		$listarTagSalaVinculados = json_encode(ProdServController::listarTagSalaVinculados(cb::idempresa(), $_1_u_prodserv_idprodserv));
		$listarTiposTag = json_encode(ProdServController::listarTagTipo()['json']);
		$listarInterpretacoesRelacionadasServico = json_encode(ProdServController::listarInterpretacoesRelacionadasServico($_1_u_prodserv_idprodserv));
	} else {
		$listarProdutosVinculados = 0;
		$listarTagSalaVinculados = 0;
		$listarServicosVinculados = 0;
		$listarTiposTag = 0;
		$listarInterpretacoesRelacionadasServico = 0;
		$listarDocsParaVinculo['json'] = 0;
	}
	?>
	var produtosVinculados = <?= $listarProdutosVinculados ?> || 0;
	var servicosVinculados = <?= $listarServicosVinculados ?> || 0;
	var tagSalaVinculados = <?= $listarTagSalaVinculados ?> || 0;
	var tagTipoVinculados = <?= $listarTiposTag ?> || 0;
	var listarDocsParaVinculo = <?= $listarDocsParaVinculo['json'] ?? 0 ?> || 0;
	var interpretacao = <?= $listarInterpretacoesRelacionadasServico ?> || 0;
	var contaItem = <?= json_encode(ProdServController::listarContaItemAtivoShare()) ?> || 0;
	var count = 0;
	var countAdd = 0;
	var acao = "<?= $_acao ?>";
	var unidadeNeg = [];
	var unidadeNegocio = {
		unidadeBloco: []
	};
	var countId = 0;
	var valCal;
	var indiceCampoPersonalizadoAtivo;
	var indiceOptionAtiva;
	var indexCampoAtivo;

	const confCamposReferencia = [];

	const tagsVinculadas = <?= json_encode(ProdServController::buscarTagSalaVinculo($_1_u_prodserv_idprodserv)) ?>;
	//------- Injeção PHP no Jquery -------

	// Remover css do dashboard para evitar conflitos
	$(`link[href="./inc/css/dashboard.css"]`).remove();

	//------- Funções JS -------
	//Transforma o visual da tela em caso de status INATIVO
	CB.oModuloForm.attr("status", statusProdsev);
	arrCoresLote = $.extend({}, ["silver", "#cc0000", "#0000cc", "#00cc00", "#990000", "#ff6600", "#fcd202", "#b0de09", "#0d8ecf", "#cd0d74"]);

	$("#modalProdutosVinculados").click(function() {
		var strCabecalho = "</strong>Vinculo(s)</strong>";
		var data = $("#prodservvincobj")[0].innerHTML;
		var jHTML = $("#prodservvincobj").html()
		var objHTML = $(jHTML)
		objHTML.find('#idobjetovinc').attr('id', '_prodvinc');
		$('#cbModal').removeClass('url');
		$('#cbModal').removeClass('setenta');
		$('#cbModal').removeClass('noventa');
		$('#cbModal').addClass('sessenta');
		$('#cbModal').addClass('fade');
		$('#cbModal').attr("modal", "modalobjvinc");
		$("#cbModalTitulo").html((strCabecalho));
		$("#cbModalCorpo").html(objHTML);
		$('#cbModal').modal('show');
		autocompleteProdutosVinculados();
	});

	$('#cbModalCorpo').off('change', 'select.select-tipo-tag')

	$('#cbModalCorpo').on('change', 'select.select-tipo-tag', function() {
		console.log('funcai chamada');
		vinculaTipoTagNaTagSala($(this).attr("idprodservvinculo"), this, $(this).attr("idobjetovinculo"));
	});


	function desvincularAtividade(idprativobj) {
		if (!confirm('Deseja remover o vinculo com esta atividade?')) return false

		CB.post({
			objetos: {
				"_x_d_prativobj_idprativobj": idprativobj,
			},
			parcial: true,
			posPost: function() {
				if ($("#cbModal").is(':visible')) {
					$(".ativmodal").click();
				}
			}
		});
	}

	if (idprodserv) {
		if (produtosVinculados != 0) {
			function autocompleteProdutosVinculados() {
				$("#_prodvinc").autocomplete({
					source: produtosVinculados,
					delay: 0,
					create: function() {
						$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
							return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
						};
					},
					select: function(event, ui) {
						CB.post({
							objetos: {
								"_add_i_objetovinculo_idobjeto": idprodserv,
								"_add_i_objetovinculo_tipoobjeto": 'prodserv',
								"_add_i_objetovinculo_idobjetovinc": ui.item.value,
								"_add_i_objetovinculo_tipoobjetovinc": 'prodserv'
							},
							parcial: true,
							posPost: function() {
								if ($("#cbModal").is(':visible')) {
									$("#modalProdutosVinculados").click();
								}
							}
						});
					}
				});
			}
		}
	}

	var selectMontado = false;

	$("#modalTagsVinculadas").click(function() {
		var strCabecalho = "<strong>Vinculo(s)</strong>";
		var data = $("#prodservvincobj3")[0].innerHTML;
		var jHTML = $("#prodservvincobj3").html()
		var objHTML = $(jHTML)
		objHTML.find('#idobjetovinc31').attr('id', '_TagSalaVinc');
		$('#cbModal').removeClass('url');
		$('#cbModal').removeClass('setenta');
		$('#cbModal').removeClass('noventa');
		$('#cbModal').addClass('setenta');
		$('#cbModal').addClass('fade');
		$('#cbModal').attr("modal", "modalTagsVinculadas");
		$("#cbModalTitulo").html((strCabecalho));
		$("#cbModalCorpo").html(objHTML);
		$('#cbModal').modal('show');
		// $('.selectpicker').selectpicker('refresh');
		autocompleteTagSalaVinculados();
		// autocompleteTagTipoVinculados();

		var selectTags = $('#cbModalCorpo .selectpicker');

		if (selectMontado)
			selectTags.selectpicker('refresh');
		else
			selectTags.selectpicker();

		selectTags.on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue) {
			const element = $(e.target);

			element.parent().parent().find('.atualizar-tag').removeClass('hide');
		});

		if (!selectMontado) selectMontado = true;
	});

	$("#modalServicosVinculados").click(function() {
		var strCabecalho = "<strong>Vinculo(s)</strong>";
		var data = $("#prodservvincobj2")[0].innerHTML;
		var jHTML = $("#prodservvincobj2").html()
		var objHTML = $(jHTML)
		objHTML.find('#idobjetovinc2').attr('id', '_servVinc');
		$('#cbModal').removeClass('url');
		$('#cbModal').removeClass('setenta');
		$('#cbModal').removeClass('noventa');
		$('#cbModal').addClass('sessenta');
		$('#cbModal').addClass('fade');
		$('#cbModal').attr("modal", "modalServicosVinculados");
		$("#cbModalTitulo").html((strCabecalho));
		$("#cbModalCorpo").html(objHTML);
		$('#cbModal').modal('show');
		autocompleteServicosVinculados();
	});

	$(".ativmodal").click(function() {
		var strCabecalho = "<strong>Atividade(s) vinculada(s)</strong>";
		var data = $("#atividades-vinculadas")[0].innerHTML;
		var jHTML = $("#atividades-vinculadas").html()
		var objHTML = $(jHTML)
		objHTML.find('#input-vinc-atividade').attr('id', '_ativ-vinc');
		$('#cbModal').removeClass('url');
		$('#cbModal').removeClass('setenta');
		$('#cbModal').removeClass('noventa');
		$('#cbModal').addClass('sessenta');
		$('#cbModal').addClass('fade');
		$('#cbModal').attr("modal", "atividades-vinculadas");
		$("#cbModalTitulo").html((strCabecalho));
		$("#cbModalCorpo").html(objHTML);
		$('#cbModal').modal('show');
		autocompleteAtividadesVinculadas();
	});

	function autocompleteAtividadesVinculadas() {
		$("#_ativ-vinc").autocomplete({
			source: atividadesDisponiveisParaVinculo,
			delay: 0,
			create: function() {
				$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
					return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
				};
			},
			select: function(event, ui) {
				CB.post({
					objetos: {
						"_x_i_prativobj_idprativ": ui.item.value,
						"_x_i_prativobj_idobjeto": idprodserv,
						"_x_i_prativobj_tipoobjeto": 'prodserv',
						"_x_i_prativobj_idempresa": cbIdEmpresa

					},
					parcial: true,
					posPost: function() {
						if ($("#cbModal").is(':visible')) {
							$(".ativmodal").click();
						}
					}
				});
			}
		});
	}

	if (idprodserv) {
		console.log(arraygrafc)
		if (arraygrafc.length) $(document).ready(() => gerargraficocompra())

		function gerargraficocompra() {
			$('#chartdivcompras').html('');

			var chart = AmCharts.makeChart("chartdivcompras", {
				"type": "serial",
				"theme": "none",
				"legend": {
					"useGraphSettings": true
				},
				"dataProvider": arraygrafc,


				"valueAxes": [{
					"id": "v1",
					"axisColor": "#FF6600",

					"axisAlpha": 1,
					"position": "left"
				}],
				"graphs": [{
					"valueAxis": "v1",
					"lineColor": "#FF6600",
					"bullet": "round",
					"bulletBorderThickness": 1,
					"hideBulletsCount": 30,
					"title": "Valor",
					"valueField": "valor",
					"fillAlphas": 0
				}],
				"numberFormatter": {
					"precision": 2,
					"decimalSeparator": ",",
					"thousandsSeparator": "."
				},
				"dataDateFormat": "DD/MM/YYYY HH:NN:SS",
				"chartScrollbar": {},
				"chartCursor": {
					"cursorPosition": "mouse"
				},
				"categoryField": "data",
				"categoryAxis": {
					"parseDates": false,
					"axisColor": "#DADADA",
					"minorGridEnabled": true,
					"labelRotation": 30
				},
				"export": {
					"enabled": true,
					"position": "bottom-right"
				}
			});
		}

		if (servicosVinculados != 0) {
			function autocompleteServicosVinculados() {
				$("#_servVinc").autocomplete({
					source: servicosVinculados,
					delay: 0,
					create: function() {
						$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
							return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
						};
					},
					select: function(event, ui) {
						CB.post({
							objetos: {
								"_add_i_prodservvinculo_idprodserv": idprodserv,
								"_add_i_prodservvinculo_idobjeto": ui.item.value,
								"_add_i_prodservvinculo_tipoobjeto": 'prodserv',
								"_add_i_prodservvinculo_idempresa": cbIdEmpresa

							},
							parcial: true,
							posPost: function() {
								if ($("#cbModal").is(':visible')) {
									$("#modalServicosVinculados").click();
								}
							}
						});
					}
				});
			}
		}

		function autocompleteTagSalaVinculados() {


			$("#_TagSalaVinc").autocomplete({
				source: tagSalaVinculados,
				delay: 0,
				create: function() {
					$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
						return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
					};
				},
				select: function(event, ui) {
					CB.post({
						objetos: {
							"_add_i_prodservvinculo_idprodserv": idprodserv,
							"_add_i_prodservvinculo_idobjeto": ui.item.value,
							"_add_i_prodservvinculo_tipoobjeto": 'tagsala',
							"_add_i_prodservvinculo_idempresa": cbIdEmpresa

						},
						parcial: true,
						posPost: function() {
							if ($("#cbModal").is(':visible')) {
								$("#modalTagsVinculadas").click();
							}
						}
					});
				}
			});
		}

		function autocompleteTagTipoVinculados() {
			$(".auto-tipotag").autocomplete({
				source: tagTipoVinculados,
				delay: 0,
				create: function() {
					$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
						return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
					};
				},
				select: function(event, ui) {
					vinculaTipoTagNaTagSala($(this).attr("idprodservvinculo"), this, $(this).attr("idobjetovinculo"));
				}
			});
		}

		if (contaItem != 0) {
			$("#grupoes").autocomplete({
				source: contaItem,
				delay: 0,
				create: function() {
					$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
						return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
					};
				},
				select: function(event, ui) {

					if ($("#grupoesant").val()) {
						var obj = {};

						if ($("#idprodservcontaitem").val() != '' && $("#idprodservcontaitem").val() != undefined) {
							if (confirm("Alterar a Categoria apagará a Subcategoria, deseja realmente alterar?")) {
								obj["_x_u_prodserv_idprodserv"] = $("[name='_1_u_prodserv_idprodserv']").val();
								obj["_x_u_prodserv_idtipoprodserv"] = '';
								obj['_x_i_prodservcontaitem_idprodserv'] = $("[name=_1_u_prodserv_idprodserv]").val();
								obj["_x_i_prodservcontaitem_idcontaitem"] = ui.item.value;
								$("#autoidtipoitem").html("<option value=''>Procurando....</option>");
								CB.post({
									objetos: obj,
									parcial: true,
									posPost: function() {
										$("#autoidtipoitem").html("<option value=''>Procurando....</option>");
										preencherTipoContaItemConformeContaItem()
									}
								});
							}
						}
					} else {
						CB.post({
							objetos: {
								"_x_i_prodservcontaitem_idprodserv": $("[name=_1_u_prodserv_idprodserv]").val(),
								"_x_i_prodservcontaitem_idcontaitem": ui.item.value
							},
							parcial: true,
							posPost: function() {
								preencherTipoContaItemConformeContaItem()
							}
						});
					}
				}
			});
		}

		$("#idsgdoc").autocomplete({
			source: listarDocsParaVinculo,
			delay: 0,
			create: function() {
				$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
					return $('<li>').append("<a>" + item.titulo + "</a>").appendTo(ul);
				};
			},
			select: function(event, ui) {
				CB.post({
					objetos: {
						"_1_u_prodserv_idprodserv": idprodserv,
						"_1_u_prodserv_idsgdoc": ui.item.idsgdoc,

					},
					parcial: true,
				});
			}
		});
	}

	$("#idtipoteste").autocomplete({
		source: interpretacao,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				return $('<li>')
					.append('<a>' + item.label + '</a>')
					.appendTo(ul);
			};
		},
		select: function(event, ui) {
			CB.post({
				objetos: {
					"_x_i_intertipoteste_idtipoteste": $(":input[name=_1_" + CB.acao + "_prodserv_idprodserv]").val(),
					"_x_i_intertipoteste_idinterpretacao": ui.item.value
				},
				parcial: true
			});
		}
	});


	let grupoConfiguracaoReferencia = <?= $jsonConfig ? $jsonConfig : json_encode($jsonConfig ?? []) ?>,
		referencias = [];

	if (prodservTipo == "SERVICO") {
		const especiesFinalidade = <?= $especieFinalidade ?>;

		if (Array.isArray(grupoConfiguracaoReferencia) && grupoConfiguracaoReferencia.length) carregarGruposReferencias();

		// Limpa as configurações ao ser alterado
		$('#input-modelo').on('change', function() {
			if (!confirm('Esta ação irá removar as configurações atuais. Deseja continuar?')) return;

			$("#jsonconfig").val('{}');
			$(".grupos-resultado").html('');
			$(".configuracao-grupo-resultado").html('');
			$(".novoCampoConfig").html('');

			grupoConfiguracaoReferencia = [];
		});

		//Evento que cria o bloco dinamico e cria a configuração do jsonconfig
		$("#addConfig").click(function() {
			if ($('#input-modelo').val() == 'DINAMICOREFERENCIA') {
				grupoConfiguracaoReferencia = Array.from(grupoConfiguracaoReferencia);
				atualizarGrupoConfiguracao();

				return $('#addConfig').data('tipocriacao', 'dinamico_referencia');
			}
			if (Array.isArray(grupoConfiguracaoReferencia))
				grupoConfiguracaoReferencia = Object.fromEntries(grupoConfiguracaoReferencia);

			let unidade = {
				index: countAdd,
				unidade: "todas",
				personalizados: []
			};
			unidadeNegocio.unidadeBloco = unidadeNeg;
			unidadeNeg.push(unidade);
			let novaconfig = criaConfig(countAdd);
			$(".novoCampoConfig").append($(novaconfig));

			countAdd++;
		});

		// $("#cancelarOption").click(function() {
		// 	let indiceCampo = localStorage.getItem('lastIndiceCampo');
		// 	unidadeNeg[parseInt(indiceCampo)].options = optionsBackup;
		// 	$('#modalID').modal('hide');
		// });

		// $("#salvarOption").click(function() {
		// 	unidadeNegocio.unidadeBloco = unidadeNeg;
		// 	$("#jsonconfig").val("" + JSON.strgrupoConfiguracaoReferencia
		$('#cbModal').off('click');

		$('.novoCampoConfig').on('click', '.btn-adicionar-linha-resultado', adicionarLinhaResultado);
		$('.novoCampoConfig').on('click', '.btn-configurar-referencia', abrirModalConfiguracao);
		// Alterar valor dos campos no objeto de grupos de teste
		$('.novoCampoConfig').on('keydown', '.campo-dinamico', atualizarValorCampo);
		// $('.novoCampoConfig').on('blur', '.campo-dinamico-teste', atualizarValorCampoTeste);
		$('#cbModal').on('click', '#btn-adicionar-referencia', adicionarReferencia);
		$('#cbModal').on('click', '.btn-remover-referencia', removerReferencia);
		// Remover grupos
		$('.novoCampoConfig').on('click', '.btn-remover-grupo', removerGrupoConfig);

		function removerGrupoConfig(e) {
			if (!confirm('Deseja remover este grupo de testes?')) return false;

			const elementoJQ = $(e.currentTarget),
				idGrupo = elementoJQ.data('idgrupo');

			if (!idGrupo) return alertAtencao('Id do grupo não encontrado!');

			removerGrupoConfigPorIdGrupo(idGrupo);

			$(`#grupo-pai-${idGrupo}`).remove();
		}

		function removerGrupoConfigPorIdGrupo(idGrupo) {
			return grupoConfiguracaoReferencia = grupoConfiguracaoReferencia.filter(item => item.id != idGrupo);
		}

		function carregarGruposReferencias() {
			grupoConfiguracaoReferencia.forEach((grupo, indice) => {
				let grupoCamposDinamicosHTML = adicionarCampoDinamicoReferencia(grupo, indice);
				$('.novoCampoConfig').append(grupoCamposDinamicosHTML);
			});
		}

		function atualizarCampoJsonReferencias() {
			$('#input-json-referencias').val(JSON.stringfy(grupoConfiguracaoReferencia));
		}

		let idTimeout = null;

		function atualizarValorCampo(e) {
			let atualizacaoValor = false;

			if (idTimeout) clearTimeout(idTimeout);

			idTimeout = setTimeout(() => {
				const elementoJQ = $(e.target),
					nomePropriedade = elementoJQ.data('name'),
					indice = elementoJQ.data('indice'),
					idGrupo = elementoJQ.data('idgrupo');

				if (isNaN(parseInt(indice))) return alertAtencao('Indice não encontrado para este campo.');
				if (!nomePropriedade) return alertAtencao('Nome do campo não informado para alteração.');

				if (idGrupo) {
					indiceGrupo = buscarIndiceGrupoPorId(idGrupo);

					if (indiceGrupo === -1) {
						alertAtencao('Grupo dos testes atuais não encontrado para atualização.');
						return false;
					}

					grupoConfiguracaoReferencia[indiceGrupo]['testes'][indice][nomePropriedade] = elementoJQ.val();
				} else
					grupoConfiguracaoReferencia[indice][nomePropriedade] = elementoJQ.val();

				console.log(grupoConfiguracaoReferencia);

				return true;
			}, 300);

			// return atualizacaoValor;
		}

		function atualizarGrupoConfiguracao() {
			const grupo = {
				id: gerarId(),
				nome: '',
				testes: [{
					id: gerarId(),
					nome: '',
					resultadoum: '',
					resultadodois: '',
					referencias: []
				}]
			}

			grupoConfiguracaoReferencia.push(grupo);

			const indice = grupoConfiguracaoReferencia.length - 1;

			grupoCamposDinamicosHTML = adicionarCampoDinamicoReferencia(grupo, indice);

			$('.novoCampoConfig').append(grupoCamposDinamicosHTML);
		}

		function adicionarLinhaResultado(e) {
			const idGrupo = $(e.currentTarget).data('idgrupo'),
				indiceGrupo = buscarIndiceGrupoPorId(idGrupo);

			if (indiceGrupo === -1) return alertAtencao('Id do grupo não encontrado.');

			const teste = {
				id: gerarId(),
				nome: '',
				resultadoum: '',
				resultadodois: '',
			};

			grupoConfiguracaoReferencia[indiceGrupo]['testes'].push(teste);

			const indiceTeste = grupoConfiguracaoReferencia[indiceGrupo]['testes'].length - 1;

			$(e.target)
				.closest('.configuracao-grupo-resultado')
				.find('.grupos-resultado')
				.append(montarLinhaGrupoResultadoHTML(teste, idGrupo, indiceTeste));
		}

		function removerReferencia(e) {
			const idReferencia = $(e.target).data('id');

			if (!idReferencia) return alertAtenca('ID da referência não encontrado.');

			referencias = referencias.filter(item => item.id !== idReferencia);

			atualizarModalReferencias();
		}

		function adicionarCampoDinamicoReferencia(grupoDeTestes = {}, indice) {
			const unidadesRadio = montarRadioUnidade(indice, grupoDeTestes.unidade);

			let linhasResultadoHTML = '';

			if (grupoDeTestes.testes.length)
				linhasResultadoHTML = grupoDeTestes.testes.map((item, indiceTeste) => montarLinhaGrupoResultadoHTML(item, grupoDeTestes.id, indiceTeste));

			// Montar html do bloco de campos de referencia		
			const grupoResultado = `<div id="grupo-pai-${grupoDeTestes.id}" class="row relative">
										<button class="btn btn-danger btn-remover-grupo" data-idgrupo="${grupoDeTestes.id}">
											<i class="fa fa-trash text-white m-0"></i>
										</button>
										<div class="col-xs-12 text-center">
											<!-- Unidade -->
											${unidadesRadio}
										</div>
										<!-- Nome do grupo -->
										<div class="col-xs-12 col-md-3 form-group mb-3">
											<label for="">Nome do grupo</label>
											<input 	
												id="nome-grupo-${indice}" 
												value="${grupoDeTestes.nome}"
												type="text" 
												class="input-control campo-dinamico" 
												placeholder="Digite o nome do grupo" 
												data-indice=${indice} 
												data-name='nome' 
											/>
										</div>
										<!-- Grupo de resultados -->
										<div class="col-xs-12 d-flex mb-3 form-group align-items-center">
											<div class="d-flex justify-content-center align-items-center icon-plus mr-5 btn-adicionar-linha-resultado pointer" data-idgrupo="${grupoDeTestes.id}">
												<i class="fa fa-plus"></i>
											</div>
											<span>Grupo de resultados</span>
										</div>
										<div id="grupo-${grupoDeTestes.id}" class="row grupos-resultado">
											<!-- Linha -->
											${linhasResultadoHTML}
										</div>
										<div class="col-xs-3 form-group">
											<label for="">Label do campo de observação</label>
											<input 
												id="obs-grupo-${indice}" 
												type="text" 
												class="form-control campo-dinamico" 
												placeholder="Label do campo de observação" 
												data-indice=${indice} 
												data-name="obslabel"
												value="${grupoDeTestes.obslabel ?? ''}"
											/>
										</div>
									</div>`;

			return `<div class="p-4">
						<div class="panel panel-default">
							<div class="panel-body configuracao-grupo-resultado">
								${grupoResultado}
							</div>
						</div>
					</div>`;
		}

		function montarLinhaGrupoResultadoHTML(teste, idGrupo, indice = 0) {
			return `<div class="col-xs-12 col-md-7 d-flex flex-wrap mb-3 align-items-center">
						<div class="col-xs-4 form-group">
							<!-- Nome do grupo -->
							<label for="">Nome do teste</label>
							<input 
								type="text" 
								class="input-control campo-dinamico" 
								placeholder="Informe o nome do teste" 
								value="${teste.nome ?? ''}"
								data-idgrupo="${idGrupo}"
								data-indice="${indice}"
								data-name="nome" 
							/>
						</div>
						<div class="col-xs-4 form-group d-flex align-items-center flex-wrap">
							<label for="" class="w-100">Resultado</label>
							<div class="d-flex">
								<div class="d-flex pr-2">
									<div class="pr-1">
										<input 
											type="text" 
											class="input-control campo-dinamico" 
											placeholder="Valor" 
											value="${teste.resultadoum ?? ''}"
											data-idgrupo="${idGrupo}"
											data-indice="${indice}" 
											data-name="resultadoum"
										/>
									</div>
									<div class="pl-1">
										<input 
											type="text" 
											class="input-control campo-dinamico" 
											placeholder="Valor" 
											value="${teste.resultadodois ?? ''}"
											data-idgrupo="${idGrupo}"
											data-indice="${indice}" 
											data-name="resultadodois"
										/>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-center icon-info">
									<i class="fa fa-info"></i>
								</div>
							</div>
						</div>
						<div class="col-xs-3 d-flex flex-between">
							<button 
								data-idgrupo="${idGrupo}" 
								data-indice="${indice}"  
								class="btn btn-primary btn-configurar-referencia"
							>
								Configurar referência
							</button>
							<button class="btn btn-danger" onclick="removerTestePorIdEIdGrupo('${teste.id}', '${idGrupo}')">
								<i class="fa fa-minus"></i>
							</button>
						</div>
					</div>`;
		}

		function removerTestePorIdEIdGrupo(idTeste, idGrupo) {
			if (!confirm('Deseja remover este teste?')) return false;

			const indiceGrupo = buscarIndiceGrupoPorId(idGrupo);
			grupoConfiguracaoReferencia[indiceGrupo]['testes'] = grupoConfiguracaoReferencia[indiceGrupo]['testes'].filter(item => item.id != idTeste);

			atualizarListaTestes(idGrupo, indiceGrupo);
		}

		function atualizarListaTestes(idGrupo, indiceGrupo) {
			const testesHTML = grupoConfiguracaoReferencia[indiceGrupo]['testes'].map((item, indice) => montarLinhaGrupoResultadoHTML(item, idGrupo, indice));

			$(`#grupo-${idGrupo}`).html(testesHTML);
		}

		function buscarIndiceTestePorIdEIdGrupo(idTeste, idGrupo) {
			const indiceGrupo = buscarIndiceGrupoPorId(idGrupo);

			return grupoConfiguracaoReferencia[indiceGrupo]['testes'].findeIndex(item => item.id === idTeste);
		}

		function montarRadioUnidade(indiceGrupo, idUnidade) {
			return PlanteisLigados
				.filter(item => item.idplantelobjeto)
				.map((e, i) => (
					`<div class="form-check form-check-inline alinhamentoUnidadeNegocio">
							<input 
								id="plantel-${indiceGrupo}-${i}" 
								class="form-check-input campo-dinamico" 
								type="radio" 
								name="unidade_${indiceGrupo}" 
								value="${e.idplantel}" 
								data-name="unidade"
								data-indice=${indiceGrupo}
								${e.idplantel == idUnidade ? 'checked' : ''}
							>
							<label class="form-check-label" for="plantel-${indiceGrupo}-${i}">${e.plantel}</label>
						</div>`
				)).join(' ');
		}

		function abrirModalConfiguracao(e) {
			referencias = [];

			const idGrupo = $(e.target).data('idgrupo'),
				indiceTeste = $(e.target).data('indice');

			referencias = buscarReferenciasPorIdGrupoEIndiceTeste(idGrupo, indiceTeste);

			const formularioReferencia = montarFormularioReferenciaHTML(idGrupo, indiceTeste, referencias);

			CB.modal({
				titulo: 'Cadastro de referência',
				corpo: formularioReferencia,
				aoAbrir: () => {
					CB.oModal.find('#especie').selectpicker();
				}
			});
		}

		function buscarReferenciasPorIdGrupoEIndiceTeste(idGrupo, indiceTeste) {
			const indiceGrupo = buscarIndiceGrupoPorId(idGrupo);

			return grupoConfiguracaoReferencia[indiceGrupo]['testes'][indiceTeste]['referencias'] ?? [];
		}

		function montarFormularioReferenciaHTML(idGrupo, indiceTeste, referenciasParam = []) {
			let referencias = montarLinhaReferenciaHTML();

			if (referenciasParam.length)
				referencias = referenciasParam.map(item => montarLinhaReferenciaHTML(item)).join(' ');

			const grupoTestes = `<div class="panel panel-default testes">
									<div class="panel-heading row d-flex flex-betweem">
										<div class="col-xs-2 text-center">
											<h4 class="font-bold">Espécie</h4>	
										</div>
										<div class="col-xs-2 text-center">
											<h4 class="font-bold">Sexo</h4>	
										</div>
										<div class="col-xs-2 text-center">
											<h4 class="font-bold">Idade</h4>	
										</div>
										<div class="col-xs-2 text-center">
											<h4 class="font-bold">Referência</h4>	
										</div>
										<div class="col-xs-2 ml-auto text-right">
											<h4 class="font-bold">Ações</h4>	
										</div>
									</div>
									<div id="grupo-testes" class="panel-body">${referencias}</div>
								</div>`;

			return `<div class="row d-flex flex-wrap">
						<h4 class="col-xs-12 mb-3">Informe os parâmetros de classificação de resultados.</h4>
						<!-- Espécie -->
						<div class="col-xs-12 col-md-6 form-group">
							<label for="" class="block">Espécie</label>
							<select name="" id="especie" class="form-conrol w-100" data-live-search="true">
								<option value="">Selecione a espécie</option>
								${montarOptionsEspecie()}
							</select>
						</div>
						<!-- Sexo -->
						<div class="col-xs-12 col-md-6 form-group">
							<label for="">Sexo</label>
							<select name="" id="sexo" class="form-conrol">
								<option value="">Informe o sexo</option>
								<option value="Macho">Macho</option>
								<option value="Fêmea">Fêmea</option>
								<option value="Macho/Fêmea">Não informado</option>
							</select>
						</div>
						<!-- Idade mínima -->
						<div class="col-xs-12 col-md-6 form-group d-flex flex-wrap">
							<label for="" class="w-100">Idade mínima</label>
							<div class="d-flex w-100 row">
								<div class="col-xs-3 pl-0">
									<input name="" id="idade-min" class="form-control" placeholder="Mínima" type="number" />
								</div>
								<div class="col-xs-9 pr-0">
									<select name="" id="idade-min-unidade" class="form-control">
										<option value="">Unidade</option>
										<option value="Dia(s)">Dia(s)</option>
										<option value="Semana(s)">Semana(s)</option>
										<option value="Mês(es)">Mês(es)</option>
										<option value="Ano(s)">Ano(s)</option>
									</select>
								</div>
							</div>
						</div>
						<!-- Idade máxima -->
						<div class="col-xs-12 col-md-6 form-group d-flex flex-wrap">
							<label for="" class="w-100">Idade máxima</label>
							<div class="d-flex w-100 row">
								<div class="col-xs-3 pl-0">
									<input name="" id="idade-max" class="form-control" placeholder="Máxima" type="number" />
								</div>
								<div class="col-xs-9 pr-0">
									<select name="" id="idade-max-unidade" class="form-control">
										<option value="">Unidade</option>
										<option value="Dia(s)">Dia(s)</option>
										<option value="Semana(s)">Semana(s)</option>
										<option value="Mês(es)">Mês(es)</option>
										<option value="Ano(s)">Ano(s)</option>
									</select>
								</div>
							</div>
						</div>
						<!-- Valor de Referência -->
						<div class="col-xs-12 col-md-6 form-group d-flex flex-wrap">
							<label for="">Valor de Referência</label>
							<div class="d-flex w-100 row flex-between">
								<div class="col-xs-6 pl-0">
									<input id="referencia-min" name="" class="col-xs-11 col-md-5 form-conrol" placeholder="Insira o valor mínimo" />
								</div>
								<div class="col-xs-6 pr-0">
									<input name="" id="referencia-max" class="col-xs-11 col-md-5 form-conrol" placeholder="Insira o valor máximo" />
								</div>
							</div>
						</div>
						<div class="col-xs-12 d-flex justify-content-end">
							<button id="btn-adicionar-referencia" class="btn btn-secondary">
								Adicionar
							</button>
						</div>
						<div class="col-xs-12">${grupoTestes}</div>
						<div class="col-xs-12 d-flex flex-between mt-4">
							<button class="btn text-secondary" onclick="CB.oModal.modal('hide');">
								Cancelar
							</button>
							<button 
								id="btn-salvar-referencia" 
								class="btn btn-success" 
								onclick="salvarReferencias('${idGrupo}', ${indiceTeste})"
							>
								Salvar
							</button>
						</div>
					</div>`;
		}

		function montarOptionsEspecie() {
			let optionHTML = "";

			for (id in especiesFinalidade) {
				optionHTML += `<option value="${id}" class="d-flex">
									<span class="text-secondary">${especiesFinalidade[id].especie}</span>
									<span> - ${especiesFinalidade[id].finalidade}</span>
								</option>`;
			}

			return optionHTML;
		}

		function montarLinhaReferenciaHTML(referencia = {}) {
			if (!Object.keys(referencia).length)
				return `<div id="sem-dados" class="col-xs-12 text-center">
							<h3>Nenhuma referência adicionada.</h3>
						</div>`;

			const especie = buscarEspeciePorId(referencia.especie),
				especieHTML = especie ? `<span>${especie.especie} - ${especie.finalidade}</span>` : "<span>Não encontrada</span>"

			return `<div class="d-flex flex-between w-100 referencia-item">
						<div class="col-xs-2">
							<div class="bg-white px-4 py-2 text-center">
								${especieHTML}
							</div>
						</div>
						<div class="col-xs-2">
							<div class="bg-white px-4 py-2 text-center">
								<span>${referencia.sexo}</span>
							</div>
						</div>
						<div class="col-xs-2">
							<div class="bg-white px-4 py-2 text-center">
								<span>${referencia.idade.min} ${referencia.idade.unidadeMin ?? ''} - ${referencia.idade.max} ${referencia.idade.unidadeMax ?? ''}</span>
							</div>
						</div>
						<div class="col-xs-2">
							<div class="bg-white px-4 py-2 text-center">
								<span>${referencia.valorReferencia.min} - ${referencia.valorReferencia.max}</span>
							</div>
						</div>
						<div class="col-xs-2 ml-auto">
							<div class="px-4 py-2 text-end ">
								<i class="fa fa-trash pointer btn-remover-referencia" data-id="${referencia.id}"></i>
							</div>
						</div>
					</div>`;
		}

		function buscarEspeciePorId(id) {
			return especiesFinalidade[id] ?? false;
		}

		function adicionarReferencia() {
			limparValidacaoCamposReferencia();
			if (!validarCamposReferencia()) return alertAtencao('Preencha todos os campos.');

			const referencia = {
				id: gerarId(),
				especie: $('#especie').val(),
				sexo: $('#sexo').val(),
				idade: {
					unidadeMax: $('#idade-max-unidade').val(),
					unidadeMin: $('#idade-min-unidade').val(),
					max: $('#idade-max').val(),
					min: $('#idade-min').val()
				},
				valorReferencia: {
					max: $('#referencia-max').val(),
					min: $('#referencia-min').val()
				}
			};

			referencias.push(referencia);

			limparCamposModalReferencia();
			atualizarModalReferencias();
		}

		function validarCamposReferencia() {
			let success = true;

			CB.oModal.find('input[id], select').each((index, item) => {
				if (!$(item).val()) {
					$(item).addClass('has-error');
					success = false;

					return;
				}
			});

			return success;
		}

		function salvarReferencias(idGrupo, indiceTeste) {
			const indiceGrupoConfig = buscarIndiceGrupoPorId(idGrupo);

			if (indiceGrupoConfig === -1) return alertAtencao('Id do grupo não encontrado.');

			grupoConfiguracaoReferencia[indiceGrupoConfig]['testes'][indiceTeste]['referencias'] = referencias;

			referencias = [];
			CB.oModal.modal('hide');
		}

		function buscarIndiceGrupoPorId(idGrupo) {
			return grupoConfiguracaoReferencia.findIndex(grupo => grupo.id === idGrupo);
		}

		function limparValidacaoCamposReferencia() {
			$('.has-error').removeClass('has-error');
		}

		function limparCamposModalReferencia() {
			$('#especie').val('');
			$('#sexo').val('');
			$('#idade-max').val('');
			$('#idade-min').val('');
			$('#referencia-max').val('');
			$('#referencia-min').val('');
		}

		function atualizarModalReferencias() {
			let referenciaHTML = montarLinhaReferenciaHTML();

			if (referencias.length) referenciaHTML = referencias.map(item => montarLinhaReferenciaHTML(item)).join(' ');

			$('#grupo-testes').html(referenciaHTML);
		}

		function gerarId() {
			return '_' + Math.random().toString(36).substr(2, 9);
		}
	}

	$("#imgcotacao").dropzone({
		url: "form/_arquivo.php",
		idObjeto: idprodserv,
		tipoObjeto: 'arqCotacao',
		caminho: "upload/anexo_orcamento/",
		idPessoaLogada: idpessoa
	});

	if ($("[name=_1_u_prodserv_idprodserv]").val()) {
		$(".cbupload").dropzone({
			idObjeto: $("[name=_1_u_prodserv_idprodserv]").val(),
			tipoObjeto: 'prodserv',
			idPessoaLogada: '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>'
		});
	}

	$(".formuladomodal").click(function() {
		var idprodserv = $("[name=_1_u_prodserv_idprodserv]").val();
		CB.modal({
			url: "?_modulo=formulaprocesso&_acao=u&idprodserv=" + idprodserv,
			header: "Fórmulas e Processos"
		});
	});

	$(".calculoestoquedomodal").click(function() {
		var tabela = $(this).attr('tabela');
		var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';
		var idprodserv = $("[name=_1_u_prodserv_idprodserv]").val();
		CB.modal({
			url: "?_modulo=calculosestoque&_acao=u&idprodserv=" + idprodserv + idempresa,
			header: "Cálculos Estoque"
		});
	});

	$("#modalcertificado").click(function() {
		var strCabecalho = "</strong>Certificado de Análise</strong>";
		var data = $("#certanalise")[0].innerHTML;
		$('#cbModal').removeClass('url');
		$('#cbModal').removeClass('cinquenta');
		$('#cbModal').removeClass('setenta');
		$('#cbModal').addClass('noventa');
		$('#cbModal').addClass('titulo');
		$('#cbModal').addClass('fade');
		$('#cbModal').attr("modal", "modalcertificado");
		$("#cbModalTitulo").html((strCabecalho));
		$("#cbModalCorpo").html(data);
		$('#cbModal').modal('show');
	});

	$("#modalregulatorio").click(function() {
		var strCabecalho = "</strong>Regulatório</strong>";
		var data = $("#certInfo")[0].innerHTML;
		$('#cbModal').removeClass('url');
		$('#cbModal').removeClass('setenta');
		$('#cbModal').removeClass('noventa');
		$('#cbModal').addClass('cinquenta');
		$('#cbModal').addClass('titulo');
		$('#cbModal').addClass('fade');
		$('#cbModal').attr("modal", "modalregulatorio");
		$("#cbModalTitulo").html((strCabecalho));
		$("#cbModalCorpo").html(data);
		$('#cbModal').modal('show');
	});

	$("#opcao").on("change", function() {
		CB.post({
			objetos: {
				"_x_i_prodservtipoopcao_idempresa": '<?= cb::idempresa(); ?>',
				"_x_i_prodservtipoopcao_idprodserv": $("[name=_1_u_prodserv_idprodserv]").val(),
				"_x_i_prodservtipoopcao_valor": String($("#opcao").val())

			},
			parcial: true
		});
	});

	$("#prodservtipoalerta").on("change", function() {
		CB.post({
			objetos: {
				"_x_i_prodservtipoalerta_idempresa": '<?= cb::idempresa(); ?>',
				"_x_i_prodservtipoalerta_idprodserv": $("[name=_1_u_prodserv_idprodserv]").val(),
				"_x_i_prodservtipoalerta_tipoalerta": String($("#prodservtipoalerta").val())

			},
			parcial: true
		});
	});

	$("#prodservtipoagente").on("change", function() {
		CB.post({
			objetos: {
				"_x_i_prodservtipoagente_idempresa": '<?= cb::idempresa(); ?>',
				"_x_i_prodservtipoagente_idprodserv": $("[name=_1_u_prodserv_idprodserv]").val(),
				"_x_i_prodservtipoagente_tipoagente": String($("#prodservtipoagente").val())

			},
			parcial: true
		});
	});

	$("#modalfornecedor").click(function() {
		var idprodserv = $("[name=_1_u_prodserv_idprodserv]").val();
		var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';

		CB.modal({
			url: "?_modulo=prodservfornecedor&_acao=u&idprodserv=" + idprodserv + "" + idempresa,
			header: "Fornecedor"
		});
	});

	$("#modalplanejamento").click(function() {
		var idprodserv = $("[name=_1_u_prodserv_idprodserv]").val();
		var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';

		CB.modal({
			url: "?_modulo=planejamentoprodserv&_acao=u&idprodserv=" + idprodserv + "" + idempresa,
			header: "Planejamento de Consumo"
		});
	});

	//------- Funções JS -------

	//------- Funções Módulo -------
	function inserevinculo(vthis, inidproserv) {
		CB.post({
			objetos: {
				"_add_i_objetovinculo_idobjeto": inidproserv,
				"_add_i_objetovinculo_tipoobjeto": 'prodserv',
				"_add_i_objetovinculo_idobjetovinc": $(vthis).val(),
				"_add_i_objetovinculo_tipoobjetovinc": 'prodserv'
			},
			parcial: true,
			posPost: function() {
				if ($("#cbModal").is(':visible')) {
					$("#modalProdutosVinculados").click();
				}
			}
		});
	}

	function excluirvinc(inidobjetovinc) {
		CB.post({
			objetos: {
				"_del_d_objetovinculo_idobjetovinculo": inidobjetovinc
			},
			parcial: true,
			posPost: function() {
				if ($("#cbModal").is(':visible')) {
					$("#modalProdutosVinculados").click();
				}
			}
		});
	}

	function desvincularOpcao(oI) {
		var id = $(oI).attr("idprodservtipoopcao");
		CB.post({
			objetos: {
				"_x_d_prodservtipoopcao_idprodservtipoopcao": id
			},
			parcial: true
		});
	}

	function retirarTeste(inidintertipoteste) {
		CB.post({
			objetos: "_x_d_intertipoteste_idintertipoteste=" + inidintertipoteste,
			parcial: true
		});
	}

	function preencherTipoContaItemConformeContaItem() {
		$("#autoidtipoitem").empty();
		$.ajax({
			type: "get",
			url: "ajax/buscacontaitem.php",
			data: {
				idcontaitem: $("#grupoes").attr('cbvalue')
			},
			success: function(data) {
				$("#autoidtipoitem").html(data);
			},
			error: function(objxmlreq) {
				alert('Erro:<br>' + objxmlreq.status);

			}
		}) //$.ajax
	}

	function excluir(tab, inid) {
		if (confirm("Deseja retirar este?")) {
			CB.post({
				objetos: `_x_d_${tab}_id${tab}=${inid}`,
				parcial: true
			});
		}
	}

	function alerarEspecial(inval) {
		CB.post({
			objetos: `_x_u_prodserv_idprodserv=${$("[name=_1_u_prodserv_idprodserv]").val()}&_x_u_prodserv_especial=${inval}`,
			parcial: true,
			posPost: function() {
				alterTipoLote();
			}
		});
	}

	//ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
	function altimobilizado(inval) {
		CB.post({
			objetos: "_x_u_prodserv_idprodserv=" + $("[name=_1_u_prodserv_idprodserv]").val() + "&_x_u_prodserv_imobilizado=" + inval,
			parcial: true,
			posPost: function() {
				alterTipoLote();
			}
		});
	}

	function altmaterial(inval) {
		CB.post({
			objetos: "_x_u_prodserv_idprodserv=" + $("[name=_1_u_prodserv_idprodserv]").val() + "&_x_u_prodserv_material=" + inval,
			parcial: true,
			posPost: function() {
				alterTipoLote();
			}
		});
	}

	//Atualiza, insere ou deleta os campos da Tipo Lote - (LTM - 05/08/2020)
	function alterTipoLote() {
		$.ajax({
			type: "ajax",
			dataType: 'html',
			url: "ajax/prodserv.php?vopcao=atualizaLoteTipo&vidprod=<?= $_1_u_prodserv_idprodserv ?>",
			parcial: true,
			refresh: false
		});
	}

	function altfabricado(inval) {
		CB.post({
			objetos: "_x_u_prodserv_idprodserv=" + $("[name=_1_u_prodserv_idprodserv]").val() + "&_x_u_prodserv_fabricado=" + inval,
			parcial: true,
			posPost: function() {
				alterTipoLote();
			}
		});
	}

	function altConfiguracaoProduto(e, oldVar) {
		let campo = $(e).val();
		let material = campo == "material" ? "Y" : "N";
		let insumo = campo == "insumo" ? "Y" : "N";
		let imobilizado = campo == "imobilizado" ? "Y" : "N";
		let produtoacabado = campo == "produtoacabado" ? "Y" : "N";
		let consometransf = 'N';
		let idprodserv = $("[name=_1_u_prodserv_idprodserv]").val();

		//@849599 - ALTERAÇÃO TIPO DO PRODUTO NA PRODSERV
		//se for imobilizado ou insumo, nunca consome na transferência
		//https://sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=849599
		//if (imobilizado == "Y" || insumo == "Y" || produtoacabado== "Y") consometransf = "N";
		if (material == "Y") consometransf = "Y";

		objetos = {
			'_x_u_prodserv_idprodserv': idprodserv,
			'_x_u_prodserv_material': material,
			'_x_u_prodserv_insumo': insumo,
			'_x_u_prodserv_imobilizado': imobilizado,
			'_x_u_prodserv_consometransf': consometransf,
			'_x_u_prodserv_produtoacabado': produtoacabado,
			'_x2_i_modulohistorico_campo': campo,
			'_x2_i_modulohistorico_idobjeto': idprodserv,
			'_x2_i_modulohistorico_tipoobjeto': 'prodserv',
			'_x2_i_modulohistorico_valor': 'Y',
			'_x2_i_modulohistorico_valor_old': 'N'
		};

		CB.post({
			objetos: new URLSearchParams(objetos).toString(),
			parcial: true
		});
	}

	function alterarPerfilProduto(e) {
		debugger
		let campo = $(e).val();
		let comprado = campo == "comprado" ? "Y" : "N";
		let fabricado = campo == "fabricado" ? "Y" : "N";
		let idprodserv = $("[name=_1_u_prodserv_idprodserv]").val();

		objetos = {
			'_x_u_prodserv_idprodserv': idprodserv,
			'_x_u_prodserv_comprado': comprado,
			'_x_u_prodserv_fabricado': fabricado,
			'_x2_i_modulohistorico_campo': campo,
			'_x2_i_modulohistorico_idobjeto': idprodserv,
			'_x2_i_modulohistorico_tipoobjeto': 'prodserv',
			'_x2_i_modulohistorico_valor': 'Y',
			'_x2_i_modulohistorico_valor_old': 'N'
		};

		CB.post({
			objetos: new URLSearchParams(objetos).toString(),
			parcial: true
		});
	}

	function hitoricoConfiguracaoProduto(texto) {
		CB.modal({
			titulo: "</strong>" + texto + "</strong>",
			corpo: $("#historico-configuracao-produto").html(),
			classe: 'sessenta',
		});
	}

	function retirarUnidade(inidunidadeobjeto) {
		CB.post({
			objetos: `_x_d_unidadeobjeto_idunidadeobjeto=${inidunidadeobjeto}`,
			parcial: true
		});
	}

	function inserirUnidade(inidund) {
		CB.post({
			objetos: `_x_i_unidadeobjeto_idobjeto=${$("[name=_1_u_prodserv_idprodserv]").val()}&_x_i_unidadeobjeto_idunidade=${inidund}&_x_i_unidadeobjeto_tipoobjeto=prodserv`,
			parcial: true
		});
	}

	function retirarUnidadeNegocio(inidunidadeobjeto) {
		CB.post({
			objetos: `_x_d_plantelobjeto_idplantelobjeto=${inidunidadeobjeto}`,
			parcial: true
		});
	}

	function inseirUnidadeNegocio(inidund) {
		CB.post({
			objetos: `_x_i_plantelobjeto_idobjeto=${$("[name=_1_u_prodserv_idprodserv]").val()}&_x_i_plantelobjeto_idplantel=${inidund}&_x_i_plantelobjeto_tipoobjeto=prodserv`,
			parcial: true
		});
	}

	function altcomissionado(inval, venda) {

		if (inval == 'Y' && venda == 'N') return alertAtencao("Favor mudar produto para venda antes de configurar para comissionado.");

		CB.post({
			objetos: `_x_u_prodserv_idprodserv=${$("[name=_1_u_prodserv_idprodserv]").val()}&_x_u_prodserv_comissionado=${inval}`,
			parcial: true,
			posPost: function() {
				alterTipoLote();
			}
		});
	}

	function altlicenca(inval) {
		CB.post({
			objetos: `_x_u_prodserv_idprodserv=${$("[name=_1_u_prodserv_idprodserv]").val()}&_x_u_prodserv_licenca=${inval}`,
			parcial: true
		});
	}

	function altctransf(inval) {
		CB.post({
			objetos: `_x_u_prodserv_idprodserv=${$("[name=_1_u_prodserv_idprodserv]").val()}&_x_u_prodserv_consometransf=${inval}`,
			parcial: true
		});
	}

	function altEst(inval) {
		CB.post({
			objetos: `_x_u_prodserv_idprodserv=${$("[name=_1_u_prodserv_idprodserv]").val()}&_x_u_prodserv_retornarest=${inval}`,
			parcial: true
		});
	}

	function altEpi(inval) {
		CB.post({
			objetos: `_x_u_prodserv_idprodserv=${$("[name=_1_u_prodserv_idprodserv]").val()}&_x_u_prodserv_epi=${inval}`,
			parcial: true
		});
	}

	function altLoteAut(inval) {
		CB.post({
			objetos: `_x_u_prodserv_idprodserv=${$("[name=_1_u_prodserv_idprodserv]").val()}&_x_u_prodserv_geraloteautomatico=${inval}`,
			parcial: true
		});
	}

	function carregaDadosFinanceiro() {
		var strCabecalho = `</strong>Favor preencher estes dados com informações interestaduais.</strong>
							<button type="button" class="btn btn-success btn-xs" onclick="CB.post()" title="Salvar" style="float: right; margin-top: 14px; margin-right: 8px; display: block;">
							<i class="fa fa-circle"></i>Salvar</button>`;
		var data = $("#financInfo")[0].innerHTML;
		$('#cbModal').removeClass('url');
		$('#cbModal').removeClass('noventa');
		$('#cbModal').removeClass('setenta');
		$('#cbModal').addClass('cinquenta');
		$('#cbModal').addClass('titulo');
		$('#cbModal').addClass('fade');
		$('#cbModal').attr("modal", "modalfinanceiro");
		$("#cbModalTitulo").html((strCabecalho));
		$("#cbModalCorpo").html(data);
		$('#cbModal').modal('show');
	}

	//LTM 08092020: 371740 - Alterado para chamar o Modal quando fizer alguma inserção ou remoção de campos.
	$("#modalfinanceiro").click(function() {
		carregaDadosFinanceiro();
	});

	$(".fichaestoquemodal").click(function() {
		var tabela = $(this).attr('tabela');
		var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';
		var idprodserv = $("[name=_1_u_prodserv_idprodserv]").val();
		CB.modal({
			url: "?_modulo=fichatecnicaprodserv&_acao=u&idprodserv=" + idprodserv + idempresa,
			header: "Ficha Kardex"
		});
	});

	function novoprodservcfop() {
		CB.post({
			objetos: `_x_i_prodservcfop_idprodserv=${$("[name=_1_u_prodserv_idprodserv]").val()}`,
			posPost: function() {
				carregaDadosFinanceiro();
			}
		});
	}

	function excluirprodservcfop(inid) {
		if (confirm("Deseja retirar este valor?")) {
			CB.post({
				objetos: `_x_d_prodservcfop_idprodservcfop=${inid}`,
				posPost: function() {
					//$('#cbModal').modal('hide');
					carregaDadosFinanceiro();
				}
			});
		}
	}

	function delanaliseteste(inIdanaliseteste) {
		CB.post({
			objetos: `_x_d_analiseteste_idanaliseteste=${inIdanaliseteste}`,
			parcial: true,
			posPost: function() {
				if ($("#cbModal").is(':visible')) {
					var aux = $("#cbModal").attr('modal');
					$("#" + aux).click();
				}
			}
		});
	}

	function insanaliseteste(inIdanaliseqst) {
		CB.post({
			objetos: `_x_i_analiseteste_idanaliseqst=${inIdanaliseqst}`,
			parcial: true,
			posPost: function() {
				if ($("#cbModal").is(':visible')) {
					var aux = $("#cbModal").attr('modal');
					$("#" + aux).click();
				}
			}
		});
	}

	function inativaobjeto(inid, inobj) {
		if (confirm("Deseja retirar este?")) {
			CB.post({
				objetos: `_x_u_${inobj}_id${inobj}=${inid}&_x_u_${inobj}_status=INATIVO`,
				parcial: true,
				posPost: function() {
					if ($("#cbModal").is(':visible')) {
						var aux = $("#cbModal").attr('modal');
						$("#" + aux).click();
					}
				}
			});
		}
	}

	function novoobjeto(inobj) {
		CB.post({
			objetos: `_x_i_${inobj}_idprodserv=${$("[name=_1_u_prodserv_idprodserv]").val()}`,
			parcial: true,
			posPost: function() {
				if ($("#cbModal").is(':visible')) {
					var aux = $("#cbModal").attr('modal');
					$("#" + aux).click();
				}
			}
		});
	}

	function alteraFormulaIns(idprodservformulains, campo, qtd) {
		CB.post({
			objetos: `_ifi1_u_prodservformulains_idprodservformulains=${idprodservformulains}&_ifi1_u_prodservformulains_${campo}=${qtd.value}`,
			parcial: true,
			posPost: function() {
				$(`#${idprodservformulains}_prodservformulains_${campo}`).val(qtd.value);
			}
		});
	}

	function excluirinsumo(inidprodservformulains) {
		if (confirm("Deseja realmente retirar o Insumo do Produto?")) {
			CB.post({
				objetos: "_x_d_prodservformulains_idprodservformulains=" + inidprodservformulains,
				posPost: function(data, textStatus, jqXHR) {
					criarModalProdServVinc();
				}
			});
		}
	}

	$("#modalprodservvinc").click(criarModalProdServVinc);

	function criarModalProdServVinc() {
		var strCabecalho = "</strong>Produto(s) e Serviço(s) Vinculado(s)</strong>";
		var data = $($("#prodservvincInfo")[0].innerHTML);
		data.find("#tableformulas").attr("id", "tableformulas_modal")
		data.find("#inputSearchProdservFormula").on('keyup', pesquisarFormulas);
		$('#cbModal').removeClass('url');
		$('#cbModal').removeClass('setenta');
		$('#cbModal').removeClass('noventa');
		$('#cbModal').addClass('sessenta');
		$('#cbModal').addClass('fade');
		$('#cbModal').attr("modal", "modalprodservvinc");
		$("#cbModalTitulo").html((strCabecalho));
		$("#cbModalCorpo").html(data);
		$('#cbModal').modal('show');
	}

	$("#grupoes").attr('vnulo', '');

	function altvenda(inval) {
		CB.post({
			objetos: "_x_u_prodserv_idprodserv=" + $("[name=_1_u_prodserv_idprodserv]").val() + "&_x_u_prodserv_venda=" + inval,
			parcial: true,
			posPost: function() {
				alterTipoLote();
			}
		});
	}

	function altcomprado(inval) {
		CB.post({
			objetos: "_x_u_prodserv_idprodserv=" + $("[name=_1_u_prodserv_idprodserv]").val() + "&_x_u_prodserv_comprado=" + inval,
			parcial: true
		});
	}

	function excluiritem(tabela, inid) {
		if (confirm("Deseja retirar este valor?")) {
			CB.post({
				objetos: "_x_d_" + tabela + "_id" + tabela + "=" + inid,
				parcial: true
			});
		}
	}

	function desvincularTipoalerta(oI) {
		var id = $(oI).attr("idprodservtipoalerta");
		CB.post({
			objetos: {
				"_x_d_prodservtipoalerta_idprodservtipoalerta": id
			},
			parcial: true
		});
	}

	function desvincularTipoagente(oI) {
		var id = $(oI).attr("idprodservtipoagente");
		CB.post({
			objetos: {
				"_x_d_prodservtipoagente_idprodservtipoagente": id
			},
			parcial: true
		});
	}

	function updanaliset(vthis, inIdanaliseteste) {
		CB.post({
			objetos: "_x_u_analiseteste_idanaliseteste=" + inIdanaliseteste + "&_x_u_analiseteste_idprodserv=" + $(vthis).val(),
			parcial: true,
			posPost: function() {
				if ($("#cbModal").is(':visible')) {
					var aux = $("#cbModal").attr('modal');
					$("#" + aux).click();
				}
			}
		});
	}

	function alteratipo(vthis) {
		CB.post({
			objetos: {
				"_xx_u_prodserv_idprodserv": $("[name=_1_u_prodserv_idprodserv]").val(),
				"_xx_u_prodserv_idtipoprodserv": $(vthis).val()
			},
			parcial: true
		});
	}

	function novografico() {
		CB.post({
			objetos: "_x_i_prodservtipoopcaoespecie_idprodserv=" + $("[name=_1_u_prodserv_idprodserv]").val(),
			parcial: true
		});
	}

	function excluirgrafico(inid) {
		if (confirm("Deseja excluir a configuração de Gráfico?")) {
			CB.post({
				objetos: "_x_d_prodservtipoopcaoespecie_idprodservtipoopcaoespecie=" + inid,
				parcial: true
			});
		}
	}

	function removerRelatorio(inidprodservtiporelatorio) {
		CB.post({
			objetos: "_x_d_prodservtiporelatorio_idprodservtiporelatorio=" + inidprodservtiporelatorio,
			parcial: true
		});
	}

	function inserirRelatorio(inid) {
		CB.post({
			objetos: "_x_i_prodservtiporelatorio_idprodserv=" + $("[name=_1_u_prodserv_idprodserv]").val() + "&_x_i_prodservtiporelatorio_idtiporelatorio=" + inid,
			parcial: true
		});
	}

	function excluirServicoVinculado(idprodservvinculo) {
		CB.post({
			objetos: {
				"_del_d_prodservvinculo_idprodservvinculo": idprodservvinculo
			},
			parcial: true,
			posPost: function() {
				if ($("#cbModal").is(':visible')) {
					$("#modalServicosVinculados").click();
				}
			}
		});
	}

	function excluirTagsVinculado(idprodservvinculo, idobjetovinc = null, element = null) {
		debugger
		if (idobjetovinc != null) {
			concat = '_del1_d_objetovinculo_idobjetovinculo=' + idobjetovinc + '&';
		} else {
			concat = '';
		}

		CB.post({
			objetos: concat + "_del_d_prodservvinculo_idprodservvinculo=" + idprodservvinculo,
			parcial: true,
			posPost: function() {
				if ($("#cbModal").is(':visible')) {
					$("#modalTagsVinculadas").click();
				}

				if (element) excluirObjetoVinculo(element, idobjetovinc);
			}
		});
	}

	function vinculaTipoTagNaTagSala(idprodservvinculo, vthis, idobjetovinculo = null) {
		if (idobjetovinculo != null) {
			CB.post({
				objetos: {
					"_1_i_objetovinculo_idobjeto": idprodservvinculo,
					"_1_i_objetovinculo_tipoobjeto": 'prodservvinculo',
					"_1_i_objetovinculo_idobjetovinc": $(vthis).val(),
					"_1_i_objetovinculo_tipoobjetovinc": 'tagtipo',
				},
				parcial: true,
				posPost: function() {
					if ($("#cbModal").is(':visible')) {
						$("#modalTagsVinculadas").click();
					}
				}
			});
		} else {
			CB.post({
				objetos: {
					"_1_u_objetovinculo_idobjetovinculo": idobjetovinculo,
					"_1_u_objetovinculo_idobjeto": idprodservvinculo,
					"_1_u_objetovinculo_tipoobjeto": 'prodservvinculo',
					"_1_u_objetovinculo_idobjetovinc": $(vthis).val(),
					"_1_u_objetovinculo_tipoobjetovinc": 'tagtipo',
				},
				parcial: true,
				posPost: function() {
					if ($("#cbModal").is(':visible')) {
						$("#modalTagsVinculadas").click();
					}
				}
			});
		}

	}

	function alterarAlerta(checkbox, idprodservvinculo) {
		if (checkbox.checked) {
			objetos = {
				"_x_u_prodservvinculo_idprodservvinculo": idprodservvinculo,
				"_x_u_prodservvinculo_alerta": 'Y'
			};
		} else {
			objetos = {
				"_x_u_prodservvinculo_idprodservvinculo": idprodservvinculo,
				"_x_u_prodservvinculo_alerta": 'N'
			};
		}

		CB.post({
			objetos: objetos,
			parcial: true,
			posPost: function() {
				if ($("#cbModal").is(':visible')) {
					$("#modalServicosVinculados").click();
				}
			}
		});
	}

	function liberaTaguiavel() {
		var taguiavel = $('.taguiavel');
		if ($("#tipo").val() == "PRODUTO") {
			if (taguiavel.css('display') === "none") {
				taguiavel.css('display', 'block');
			}
		} else {
			taguiavel.css('display', 'none');
		}
	}

	$().ready(function() {
		$("#tipo").change(function() {
			// Armazena o valor selecionado
			var novoValor = $(this).val();

			// Mostra a caixa de confirmação
			if (confirm("Você selecionou o tipo " + novoValor + ", deseja continuar?")) {
				// Se o usuário clicar em "OK", prossegue com a função
				liberaTaguiavel();
			} else {
				// Se o usuário clicar em "Cancelar", volta ao valor anterior
				$(this).val($(this).data('valorAnterior'));
			}

			// Armazena o valor atual como valor anterior para a próxima mudança
			$(this).data('valorAnterior', novoValor);
		});

		// Define o valor inicial como data attribute quando a página carrega
		$("#tipo").data('valorAnterior', $("#tipo").val());
	});

	function inserirObjempresa(vthis) {
		if (confirm("Deseja realmente relacionar esta empresa ao cadastro?")) {
			CB.post({
				objetos: "_x_i_objempresa_empresa=" + $(vthis).val() + "&_x_i_objempresa_idobjeto=" + $("[name=_1_u_prodserv_idprodserv").val() + "&_x_i_objempresa_objeto=prodserv"
			});
		}
	}

	function pesquisarFormulas() {
		var filter, table, tr, a, i, txtValue;
		filter = this.value.toUpperCase();
		table = document.getElementById("tableformulas_modal");
		tr = table.getElementsByTagName("tr");
		for (i = 0; i < tr.length; i++) {
			if (i > 0) {
				a = tr[i].getElementsByTagName("td");
				some = true;
				txtValue = a[0].textContent || a[0].innerText;
				txtValue = txtValue;
				if (txtValue.toUpperCase().indexOf(filter) > -1) {
					tr[i].style.display = "";
				} else {
					tr[i].style.display = "none";
				}
			}
		}
	}

	if (prodservTipo == "SERVICO") {
		function criaConfig(countAdd) {
			addNovaDiv = ``;
			PlanteisLigados.forEach((e, i) => {
				if (e.idplantelobjeto)
					addNovaDiv += `<div class="form-check form-check-inline alinhamentoUnidadeNegocio">
										<input class="form-check-input" type="radio" name="unidade${countAdd}" id="plantel${countAdd}" value="${e.idplantel}" onclick="atualizaUnidade(${countAdd});">
										<label class="form-check-label" for="plantel${countAdd}">${e.plantel}</label>
								</div>`;
			});

			let novoCampo = `
							<div class="col-sm-12 novaConfig config${countAdd}" data-campo="${countAdd}">
								<div class="panel-body">
									<div class="row">
										<div class="col-sm-12 text-left">
											<label>Campos Dinâmicos:</label>
											<i id="addCampo${countAdd}" class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer addCampo"
												onclick="addCampo(${countAdd});" title="Relacionar processo"></i>
												<span class="escolhaUnidadeNegocio">Escolha uma Unidade de Negócio:</span>
													${addNovaDiv}
													<div class="form-check form-check-inline alinhamentoUnidadeNegocio">
														<input class="form-check-input" type="radio" name="unidade${countAdd}" id="plantel${countAdd}" value="todas" onclick="atualizaUnidade(${countAdd});">
														<label class="form-check-label" for="plantel${countAdd}">Todas</label>
													</div>
											<div class="panel-body">
												<div class="col-sm-12">
													<div class="col-sm-10 col-sm-offset-1">
														<div class="teste" id="camposBody${countAdd}">
														</div>
													</div>
												</div>
											</div>
											<div style="display:inline;padding-right:150px;">
												<span class="btn btn-sm btn-danger size3" onclick="deleteConfig(this, ${countAdd});" 
												title="Excluir"><i class="fa fa-minus pointer"></i></span>
											</div>
										</div>
									</div>
								</div>
								<hr>
							</div>`;

			return novoCampo;
		}

		//Função que atualiza a unidade de negocio no jsonconfig 
		function atualizaUnidade(index) {
			var radios = $("input[name='unidade" + index + "']:checked").val();

			for (var x = 0; x < unidadeNeg.length; x++) {
				if (unidadeNeg[x].index === parseInt(index)) {
					unidadeNeg[x].unidade = radios;
				}
			}
			unidadeNegocio.unidadeBloco = unidadeNeg;
			$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
		}

		//Função que cria os campos dinamicos de cada bloco
		function addCampo(index) {
			var radios = $("input[name='unidade" + index + "']:checked").val();
			if (radios != null) {
				valCal = index;
				let novoCampo = criaCampo('', index);
				$('#camposBody' + index).append($(novoCampo));
				$('#editarTipo' + count).hide();
				let campo = {
					index: index,
					indice: count,
					tipo: "identificador",
					titulo: "",
					vinculo: "INDIVIDUAL",
					options: [],
					calculo: "NAO",
					ordem: count
				};

				if (unidadeNegocio.tags &&
					unidadeNegocio.tags.length &&
					unidadeNegocio.tags.length > 0) {

					$(".vinculo" + count).append('<option value="tag">Equipamento</option>');
				}

				if (unidadeNegocio.documentos &&
					unidadeNegocio.documentos.length &&
					unidadeNegocio.documentos.length > 0) {

					$(".vinculo" + count).append('<option value="sgdoc">Documento</option>');
				}

				if (unidadeNegocio.pessoas &&
					unidadeNegocio.pessoas.length &&
					unidadeNegocio.pessoas.length > 0) {

					$(".vinculo" + count).append('<option value="pessoa">Pessoa</option>');
				}

				for (var x = 0; x < unidadeNeg.length; x++) {
					if (unidadeNeg[x].index === parseInt(index)) {
						unidadeNeg[x].personalizados.push(campo);
					}
				}

				unidadeNegocio.unidadeBloco = unidadeNeg;
				$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));

				count++;

			} else {
				alert("Escolha uma Unidade de Negócio!");
			}
		}

		function deleteConfig(element, index) {
			let excluido = false;
			for (var i = 0; i < unidadeNeg.length; i++) {
				if (unidadeNeg[i].index === index) {
					if (unidadeNeg[i].personalizados.length == 0) {
						$(element).closest('.config' + index).remove();
						unidadeNeg.splice(i, 1);
						excluido = true;
					} else {
						alert("É necessário excluir os campos antes de excluir o bloco!");
						break;
					}
				}
				if (excluido && i != unidadeNeg.length) {
					unidadeNeg[i].index--;
					for (j = 0; j < unidadeNeg[i].personalizados.length; j++) {
						unidadeNeg[i].personalizados[j].index--;
					}
				}
			}
			countAdd = unidadeNeg.length;
			unidadeNegocio.unidadeBloco = unidadeNeg;
			$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
			// atualizaJson();
		}

		function criaCampo(titulo, indexCampo) {
			for (var i = 0; i < unidadeNeg.length; i++) {
				for (var j = 0; j < unidadeNeg[i].personalizados.length; j++) {
					if (unidadeNeg[i].personalizados[j].indice === count) {
						valCal = unidadeNeg[i].personalizados[j].index;
					}
				}
			}

			if (titulo === undefined) {
				titulo = '';
			}

			const optionsApontamento = unidadeNeg[indexCampo].personalizados.filter(item => !['resultado', 'validacao'].includes(item.tipo)).map(item => `<option value="${item.indice}">${item.titulo}</option>`)
			let optionTipoCampo = `<option value="validacao">Campo de validação</option>`;
			const campoValidacao = unidadeNeg[indexCampo].personalizados.find(item => item.tipo == 'validacao');

			if (campoValidacao && campoValidacao.indice != count) optionTipoCampo = '';

			/**
			 * Era utilizado no novo campo, motivo desconhecido
			 * Removido para não afetar a ordenação dos campos de configuração
			 */
			// <input type="hidden" name="auxOrdem${count}" value="${count}" class="auxCurrentposition">

			let novoCampo = ` <div class="row novocampo campo${count}" data-indexConfig="${indexCampo}" data-campo="${count}" style="margin-bottom:8px;" >
								<div class="col-xs-12 col-sm-1 sortable-item"><i class="fa fa-arrows cinzaclaro hover move" title="Ordenar"></i>
									<input type="hidden" name="ordem${count}" value="${count}" class="currentposition" onchange="atualizaJson();"> 
								</div>
								<div class="col-xs-12 col-sm-2">
									<label>Título:</label>
									<input type="text" placeholder="Nome do campo" onkeyup="atualizaTitulo(this, ${count});" value="${titulo}" vnulo>
								</div>
								<div class="col-xs-12 col-sm-3">
									<label>Tipo de campo:</label>
									<select class="preto tipocampo tipo${count}" onchange="atualizaTipo(this, ${count}, ${indexCampo});" >
										<option value="identificador">Campo identificador</option>
										<option value="input">Campo de entrada</option>
										<option value="textarea">Campo descritivo</option>
										<option value="checkbox">Campo checkbox</option>
										<option value="data">Campo de data</option>
										<option value="hora">Campo de hora</option>
										<option value="numerico">Campo numérico</option>
										<option value="selecionavel">Campo selecionável</option>
										<option value="fixo">Campo fixo</option>
										${optionTipoCampo}
									</select>
								</div>
								<div class="col-xs-12 col-sm-2 campo-validacao">
									<label>Apontar para:</label>
									<select class="apontar${count} apontar-para desabilitado" disabled onchange="atualizaVinculoValidacao(this, ${count}, ${indexCampo});">
										<option value="">Selecionar</option>
										${optionsApontamento.join(' ')}
									</select>
								</div>
								<div class="col-xs-12 col-sm-2">
									<label>Tipo de Resultado:</label>
									<select class="vinculo preto vinculo${count}" onchange="atualizaVinculo(this, ${count}); atualizaJson();" >
										<option value="INDIVIDUAL">Individual</option>
										<option value="AGRUPADO">Agrupado</option>
									</select>
								</div>
								<div class="col-xs-12 col-sm-2 d-flex align-items-center">
									<div class="form-check form-check-inline" style="display:inline;padding-right: 10px;">
										<input class="form-check-input" type="checkbox" name="calculo${valCal }" id="calculo${count}" value="${count}" onclick="atualizaCalculo(this, ${valCal});"/>
										<label class="form-check-label" for="calculo${count}">Campo de cálculo</label>
									</div>
									<span class="btn btn-sm btn-info size3 mr-2" id="editarTipo${count}" onclick="atualizaTipo(this, ${count}, ${indexCampo});" title="Editar"><i class="fa fa-bars pointer"></i></span>
									<span class="btn btn-sm btn-danger size3" onclick="deleteCampo(this, ${count}, ${indexCampo});" title="Excluir"><i class="fa fa-minus pointer"></i></span>
								</div>
							</div>`;
			return novoCampo;
		}

		function atualizaVinculoValidacao(element, index, indexCampo) {
			unidadeNeg.forEach(function(campos) {
				if (campos.index == indexCampo) {
					campos.personalizados.forEach(function(personalizado) {
						if (personalizado.indice === index) {
							personalizado.indiceCampoValidacao = $(element).val();

							return true;
						}
					});

					return true;
				}
			});

			unidadeNegocio.unidadeBloco = unidadeNeg;
			$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
		}

		function atualizaTipo(element, index, indexCampo, limparModal = true) {
			indexCampoAtivo = indexCampo;
			indiceCampoPersonalizadoAtivo = index;
			element = $(element).closest('.novocampo').find('.tipocampo');

			if (['selecionavel', 'fixo'].includes($(element).val()) || ['selecionavel', 'fixo'].includes($(element).closest('.novocampo').find('.preto').val())) {
				$('#editarTipo' + index.toString()).show();

				localStorage.setItem('lastIndiceCampo', index.toString());
				optionCount = 0;

				$(".novooption").remove();

				unidadeNeg.forEach(function(campos, chaveCampo) {
					campos.personalizados.forEach(function(personalizado, chave) {
						if (personalizado.indice === index) {
							CB.modal({
								titulo: 'Configuração',
								corpo: criaConfigOption(personalizado.indice),
								rodape: `<button id="cancelarOption" type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
										<span id="salvarOption" class="btn btn-success" onclick="atualizaJson();" data-dismiss="modal">Salvar</span>`,
								limparModal
							})

							CB.oModal.find('.optionBody').sortable({
								helper: "original",
								handle: '.move',
								update: (e, ui) => {
									const indiceCampo = ui.item.data('campo');
									unidadeNeg.forEach((campos, key) => {
										let posicaoCampoPersonalizado = campos.personalizados.findIndex(item => item.indice == indiceCampoPersonalizadoAtivo);

										if (posicaoCampoPersonalizado == -1) return;

										unidadeNeg[key]['personalizados'][posicaoCampoPersonalizado]['options'] = unidadeNeg[key]['personalizados'][posicaoCampoPersonalizado]['options'].map(item => {
												return {
													...item,
													ordem: CB.oModal.find(`.optionBody .option${item.indice}`).index() + 1
												}
											}).sort((a, b) => (a.ordem || 0) - (b.ordem || 0))
											.map((item, key) => {
												if (!item.parametros)
													return {
														...item,
														indice: key,
													}

												return {
													...item,
													indice: key,
													parametros: item.parametros.map(param => {
														return {
															...param,
															indiceCampo: key
														}
													})
												}
											});
									});

									CB.oModal.find('.optionBody > div').each((index, element) => {
										const itemClass = `option${$(element).data('parametro')}`;
										const newClass = `option${index}`;
										const inputDesc = $(element).find(` > div:nth-child(2) input`);
										const inputCalc = $(element).find(` > div:nth-child(3) > input`);
										const btnAddParam = $(element).find(` > div:nth-child(4) > span`);
										const btnRemoveParam = $(element).find(` > div:nth-child(5) > span`);

										inputDesc.attr('onchange', `atualizaOption(this, ${indiceCampoPersonalizadoAtivo}, ${index});`);
										btnAddParam.attr('onclick', `atualizaTipoParametros(this, ${index}, ${indiceCampoPersonalizadoAtivo});`).attr('id', `parametros${index}`);
										btnRemoveParam.attr('onclick', `deleteOption(this, ${index});`);

										$(element).removeClass(itemClass).addClass(newClass);
										$(element).attr('data-parametro', index)
									})

									unidadeNegocio.unidadeBloco = unidadeNeg;
									$("#jsonconfig").val(JSON.stringify(unidadeNegocio));
								}
							});

							personalizado.tipo = $(element).val();
						}

						// Adicionando options no modal
						if (personalizado &&
							personalizado.options &&
							personalizado.options.length > 0) {
							personalizado.options.forEach(function(option) {
								if (option.indiceCampo === index) {
									let novoOption = criaOption(option.nome, option.calculo, personalizado.indice, option.indice);
									CB.oModal.find('.optionBody').append($(novoOption));

									// Verificar se é utilizado
									if (option)
										optionsBackup = JSON.parse(JSON.stringify(option));
									else
										optionsBackup = [];
								}
								optionCount++;
							});
						}
					});
				});
			} else {
				$('#editarTipo' + index.toString()).hide();
				unidadeNeg.forEach(function(campos) {
					campos.personalizados.forEach(function(personalizado) {
						if (personalizado.indice === index) {
							personalizado.tipo = $(element).val();
						}
					});
				});
			}

			if ($(element).val() == 'validacao') {
				if (!unidadeNeg[indexCampo].personalizados.some(item => item.tipo == 'resultado')) {
					// O indice está alto, porque esse campo sempre deve ser o ultimo
					const novoPersonalizado = {
						calculo: 'NAO',
						index: indexCampo,
						indice: 999999,
						ordem: 999999,
						tipo: 'resultado',
						titulo: 'RESULTADO',
						vinculo: "INDIVIDUAL",
						options: []
					}

					unidadeNeg.forEach(item => {
						if (item.index == indexCampo) {
							if (!item.personalizados.some(personalizado => personalizado.tipo == 'resultado')) {
								item.personalizados.push(novoPersonalizado)
								count++;
							}
						}
					});
				}

				atualizaValoresApontamento(indexCampo, index)
			} else {
				unidadeNeg.forEach((item, key) => {
					if (item.index == indexCampo) {
						if (!item.personalizados.some(personalizado => personalizado.tipo == 'validacao'))
							unidadeNeg[key].personalizados = item.personalizados.filter(personalizado => personalizado.tipo != 'resultado');

						item.personalizados.forEach(personalizado => {
							if (!$(`#camposBody${indexCampo} .tipocampo`).get().some(item => item.value === 'validacao')) {
								$(`#camposBody${indexCampo} .tipocampo.tipo${personalizado.indice} option[value="validacao"]`).remove();

								$(`#camposBody${indexCampo} .tipocampo.tipo${personalizado.indice}`).append('<option value="validacao">Campo de validação</option>');
							}
						})
					}
				});

				$(element).closest('.novocampo').find('.campo-validacao select').attr('disabled', true).addClass('desabilitado').val('')
			}

			unidadeNegocio.unidadeBloco = unidadeNeg;
			$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
		}

		function atualizaValoresApontamento(indexCampo, indice) {
			let optionsApontamento = unidadeNeg.find(item => item.index == indexCampo).personalizados.filter(item => item.tipo == 'selecionavel').map(item => `<option value="${item.indice}">${item.titulo === '' ? 'Sem titulo' : item.titulo}</option>`)

			// Habilitar campo de apontamento
			$(`#camposBody${indexCampo} .tipocampo:not(.tipo${indice}) option[value="validacao"]`).remove();
			$(`#camposBody${indexCampo} .apontar${indice}`).removeAttr('disabled').removeClass('desabilitado');

			if (!optionsApontamento.length) {
				optionsApontamento = [`<option value="" disabled>Nenhum campo encontrado</option>`];
			}

			$(`#camposBody${indexCampo} .apontar${indice}`).html(`<option value="">Selecionar</option> ${optionsApontamento.join(' ')}`);
		}


		function atualizaTitulo(element, index) {
			unidadeNeg.forEach(function(campos) {
				campos.personalizados.forEach(function(personalizado) {
					if (personalizado.indice === index) {
						personalizado.titulo = $(element).val();
					}
				});
				unidadeNegocio.unidadeBloco = unidadeNeg;
				$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
			});
		}

		function deleteCampo(element, index, indexCampo = false) {
			$(element).closest('.novocampo').remove();
			let excluido = false;

			if (indexCampo !== false && $(element).parent().parent().find('.tipocampo').val() == 'validacao') {
				unidadeNeg.forEach((config, configKey) => {
					if (config.index == indexCampo) {
						unidadeNeg[configKey].personalizados = unidadeNeg[configKey].personalizados.filter(personalizado => personalizado.tipo != 'resultado');

						return true;
					}
				})
			}

			for (var i = 0; i < unidadeNeg.length; i++) {
				if (unidadeNeg[i].index != indexCampo) continue;
				for (var j = 0; j < unidadeNeg[i].personalizados.length; j++) {
					if (unidadeNeg[i].personalizados[j].indice === index) {
						unidadeNeg[i].personalizados.splice(j, 1);
						excluido = true;
					}
				}
			}

			if (excluido) {
				var aux = 0;
				var auxOption = 0;
				for (var i = 0; i < unidadeNeg.length; i++) {
					for (var j = 0; j < unidadeNeg[i].personalizados.length; j++) {
						if (unidadeNeg[i].personalizados[j].indice == 0) {
							unidadeNeg[i].personalizados[j].indice = aux;
							for (k = 0; k < unidadeNeg[i].personalizados[j].options.length; k++) {
								if (unidadeNeg[i].personalizados[j].options.length > 0 && unidadeNeg[i].personalizados[j].options.indice == 0) {
									unidadeNeg[i].personalizados[j].options[k].indice = auxOption;
									unidadeNeg[i].personalizados[j].options[k].indiceCampo = aux;
									auxOption++;
								}
							}
							aux++;
						} else {
							unidadeNeg[i].personalizados[j].indice = aux;
							for (k = 0; k < unidadeNeg[i].personalizados[j].options.length; k++) {
								if (unidadeNeg[i].personalizados[j].options.length > 0) {
									unidadeNeg[i].personalizados[j].options[k].indice = auxOption;
									unidadeNeg[i].personalizados[j].options[k].indiceCampo = aux;
									auxOption++;
								}
							}
							aux++;
						}
					}
					count = aux;
				}
			}

			unidadeNegocio.unidadeBloco = unidadeNeg;
			$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
		}

		function atualizaVinculo(element, index) {
			unidadeNeg.forEach(function(campos) {
				campos.personalizados.forEach(function(personalizado) {
					if (personalizado.indice === index) {
						personalizado.vinculo = $(element).val();
					}
				});
				unidadeNegocio.unidadeBloco = unidadeNeg;
				$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
			});
		}

		//Função que atualiza o campo de calculo no jsonconfig, e habilita e desabilita os campos 
		function atualizaCalculo(vthis, index) {
			var radioCalc = $(vthis).val();
			unidadeNeg.forEach(function(campos) {
				campos.personalizados.forEach(function(personalizado) {
					if (personalizado.indice == radioCalc && personalizado.calculo == "NAO") {
						personalizado.calculo = "SIM";
						$("#calculo" + radioCalc).prop('checked', true);
					} else if (personalizado.indice == radioCalc && personalizado.calculo != "NAO") {
						personalizado.calculo = "NAO";
						$("#calculo" + radioCalc).prop('checked', true);
					}
					if (personalizado.indice != radioCalc && personalizado.index == index) {
						personalizado.calculo = "NAO";
						$("#calculo" + personalizado.indice).prop('checked', false);
					}
				});
				unidadeNegocio.unidadeBloco = unidadeNeg;
				$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
			});
			atualizaJson();
		}

		function atualizaJson() {
			CB.post({
				objetos: {
					"_x_u_prodserv_idprodserv": $("[name=_1_u_prodserv_idprodserv]").val(),
					"_x_u_prodserv_jsonconfig": $("[name=_1_u_prodserv_jsonconfig]").val()
				},
				parcial: true
			});
		}

		//Conforme o tipo do teste prepara a tela para reagir a funções/comandos específicos
		sSeletor = '#diveditor';
		oDescritivo = $("[name=_1_" + CB.acao + "_prodserv_textoinclusaores]");

		//editor2
		sSeletor2 = '#diveditor2';
		oDescritivo2 = $("[name=_1_" + CB.acao + "_prodserv_textopadrao]");

		sSeletor3 = '#diveditor3';
		oDescritivo3 = $("[name=_1_" + CB.acao + "_prodserv_textointerpretacao]");


		if (tinyMCE.editors["diveditor"]) {
			tinyMCE.editors["diveditor"].remove();
		}
		if (tinyMCE.editors["diveditor2"]) {
			tinyMCE.editors["diveditor2"].remove();
		}
		if (tinyMCE.editors["diveditor3"]) {
			tinyMCE.editors["diveditor3"].remove();
		}
		//Inicializa Editor
		tinymce.init({
			selector: sSeletor,
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
			setup: function(editor) {
				editor.on('init', function(e) {
					this.setContent(oDescritivo.val());
				});
			}
		});
		//Inicializa Editor 2
		tinymce.init({
			selector: sSeletor2,
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
			setup: function(editor) {
				editor.on('init', function(e) {
					this.setContent(oDescritivo2.val());
				});
			}
		});

		tinymce.init({
			selector: sSeletor3,
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
			setup: function(editor) {
				editor.on('init', function(e) {
					this.setContent(oDescritivo3.val());
				});
			}
		});

		//Antes de salvar atualiza o textarea
		CB.prePost = function() {
			if (Array.isArray(grupoConfiguracaoReferencia) && grupoConfiguracaoReferencia.length) {
				const inputConfigValue = JSON.stringify(grupoConfiguracaoReferencia);
				// const inputConfigHTML = `<input name="_1_u_prodserv_jsonconfig" type="text" id="input-json-referencias" value="${inputConfigValue}" hidden />`;
				// CB.oModuloForm.append(inputConfigHTML);
				$('#jsonconfig').val(inputConfigValue);
			}


			if (tinyMCE.get('diveditor')) {
				oDescritivo.val(tinyMCE.get('diveditor').getContent());
			}
			if (tinyMCE.get('diveditor2')) { //editor2
				oDescritivo2.val(tinyMCE.get('diveditor2').getContent());
			}
			if (tinyMCE.get('diveditor3')) { //editor2
				oDescritivo3.val(tinyMCE.get('diveditor3').getContent());
			}

			if ($('select.tipocampo').get().some(item => $(item).val() == 'validacao')) {
				if ($($('select.tipocampo').get().find(item => $(item).val() == 'validacao')).parent().find('+ .campo-validacao select').val() == '') {
					alertAtencao('Campo "Apontar para" obrigatório para o tipo campo "Validação"');

					return false
				}
			}

		}

		function criaConfigOption(indiceOption) {
			return `<p>Adicione abaixo todas as opçoes selecionáveis</p>
					<div class="row">
						<div class="col-lg-3">
							<span id="addOption" onclick="addOption(${indiceOption});" class="btn btn-success size15" style="padding: 10px;" title="Criar opções dinâmicas">
								<i class="fa fa-plus pointer"></i> <b>Opção selecionável</b>
							</span>
						</div>
					</div>
					<br>
					<div class="panel panel-default">
						<div class="panel-heading w-100 d-flex flex-wrap">
							<div class="col-xs-1"></div>
							<div class="col-xs-4"><span>Descrição</span></div>
							<div class="col-xs-2"><span>Campo calculo</span></div>
							<div class="col-xs-3" style="text-align: center;"><span>Add Parâmetros</span></div>
							<div class="col-xs-2"><span>Ações</span></div>
						</div>
						<div class="panel-body optionBody optionsBody${indiceOption} table-hover table-striped" data-campo="${indiceOption}">
						</div>
					</div>`;
		}

		function addOption(index) {
			const optionsCount = getOptionCount()
			let indiceCampoUltimo = localStorage.getItem('lastIndiceCampo');

			CB.oModal.find('.optionBody').append(criaOption('', '', index, optionsCount))

			let option = {
				indiceCampo: index,
				indice: optionsCount,
				nome: "",
				ordem: optionsCount + 1,
				parametros: []
			};

			unidadeNeg.forEach(function(campos) {
				campos.personalizados.forEach(function(personalizado) {
					if (personalizado.indice === parseInt(indiceCampoUltimo)) {
						personalizado.options.push(option);
					}
				});
				unidadeNegocio.unidadeBloco = unidadeNeg;
				$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
			});
			optionCount++;
		}

		function getOptionCount() {
			const indiceCampoPersonalizado = unidadeNeg[indexCampoAtivo]['personalizados'].findIndex(item => item.indice == indiceCampoPersonalizadoAtivo);

			if (indiceCampoPersonalizado !== -1) {
				return unidadeNeg[indexCampoAtivo]['personalizados'][indiceCampoPersonalizado]['options'].length
			}

			return unidadeNeg[indexCampoAtivo]['personalizados'][indiceCampoPersonalizadoAtivo] ? unidadeNeg[indexCampoAtivo]['personalizados'][indiceCampoPersonalizadoAtivo]['options'].length : 0
		}

		function criaOption(nome, calc, indiceOption, indiceParametro) {
			if (calc === undefined) {
				calc = '';
			}
			if (nome === undefined) {
				nome = '';
			}
			var check = (calc == "Y") ? "checked = 'true'" : ''
			let novaOption = `<div class="row d-flex flex-wrap align-items-center novooption mb-4 option${indiceParametro}" data-campo="${indiceOption}" data-parametro="${indiceParametro}">
									<div class="col-xs-1">
										<i class="fa fa-arrows cinzaclaro hover move" title="Ordenar"></i>
									</div>
									<div class="col-lg-4">
										<input type="text" placeholder="Nome da opção" value="${nome}" onchange="atualizaOption(this, ${indiceOption}, ${indiceParametro});">
									</div>
									<div class="col-lg-2" style="text-align: center;">
										<span style="vertical-align:middle; padding-right:20px;" ><input style="vertical-align:middle"${check} type="radio" name="grupocalc" onclick="calculaOption(this, ${indiceParametro}, ${indiceOption});" value="${calc}"></span>
									</div>
									<div class="col-lg-3" style="text-align: center;">
										<span class="btn btn-sm btn-info size3" id="parametros${indiceParametro}" onclick="atualizaTipoParametros(this, ${indiceParametro}, ${indiceOption});" title="Editar"><i class="fa fa-bars pointer"></i></span>
									</div>
									<div class="col-lg-2">
										<span class="btn btn-sm btn-danger size3" onclick="deleteOption(this, ${indiceParametro});"
											title="Excluir">
											<i class="fa fa-minus pointer"></i>
										</span>
									</div>
									<div class="row configOptionsBodyParametros${indiceParametro}"></div>
								</div>`;

			return novaOption;
		}

		function atualizaOption(element, index, parametro = false) {
			let indiceCampo = localStorage.getItem('lastIndiceCampo');
			unidadeNeg.forEach(function(campos) {
				campos.personalizados.forEach(function(personalizado) {
					if (personalizado.indice == index)
						personalizado.options.forEach(function(option) {
							if (option.indice === parametro) {
								option.nome = $(element).val();
							}
						});

				});
			});
			unidadeNegocio.unidadeBloco = unidadeNeg;
			$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
		}

		function calculaOption(element, indicePersonalizado, indiceOption) {
			var valor = $(element).val()
			let indiceCampo = localStorage.getItem('lastIndiceCampo');
			unidadeNeg.forEach(function(campos) {
				campos.personalizados.forEach(function(personalizado) {
					if (personalizado.indice == indiceOption)
						personalizado.options.forEach(function(option) {
							if (option.indice === indicePersonalizado) {
								option.calculo = "Y";
							} else {
								option.calculo = "N";
							}
						});

				});
			});
			unidadeNegocio.unidadeBloco = unidadeNeg;
			$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
		}

		function deleteOption(element, index) {
			$(element).closest('.novooption').remove();

			let indiceCampo = localStorage.getItem('lastIndiceCampo');
			let excluido = false;

			const camposPersonalizados = unidadeNeg[indexCampoAtivo];

			if (indexCampoAtivo == undefined)
				return alertAtencao('Indice dos campos dinâmicos ativo não definido!');

			camposPersonalizados.personalizados.forEach((personalizado, personalizadoIndex) => {
				let indiceCampoPersonalizadoRemover = unidadeNeg[indexCampoAtivo].personalizados.findIndex(item => item.indice == indiceCampoPersonalizadoAtivo);

				if (
					(
						personalizado.indice == indiceCampoPersonalizadoAtivo &&
						indiceCampoPersonalizadoRemover !== -1
					) ||
					(
						personalizado.indice == indiceCampoPersonalizadoAtivo &&
						unidadeNeg[indexCampoAtivo].personalizados[indiceCampoPersonalizadoAtivo] != undefined
					)
				) {
					if (indiceCampoPersonalizadoRemover !== -1)
						return unidadeNeg[indexCampoAtivo].personalizados[indiceCampoPersonalizadoRemover].options = personalizado.options.filter(item => item.indice != index);

					return unidadeNeg[indexCampoAtivo].personalizados[indiceCampoPersonalizadoAtivo].options = personalizado.options.filter(item => item.indice != index);
				}
			})

			unidadeNegocio.unidadeBloco = unidadeNeg;
			$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
			// atualizaJson();
		}

		if (acao == 'u') {
			var campos = 0;
			var blocos = 0;
			var naoExecutarPush = false;
			var config = JSON.parse($("#jsonconfig").val());
			let existeValidacao = false;

			if (typeof(config) == 'object') {
				if (config.personalizados && config.personalizados.length > 0) {
					let unidade = {
						index: countAdd,
						unidade: "todas",
						personalizados: [] == undefined ? "" : config.personalizados
					};
					unidadeNegocio.unidadeBloco = unidadeNeg;
					unidadeNeg.push(unidade);
					config = unidadeNegocio;
					naoExecutarPush = true;
				}

				if (config.unidadeBloco && config.unidadeBloco.length > 0) {
					config.unidadeBloco.forEach(function(bloco) {
						countAdd = bloco.index;
						let novaconfig = criaConfig(bloco.index);
						$(".novoCampoConfig").append($(novaconfig));
						if (!naoExecutarPush) {
							let unidadeAux = {
								index: bloco.index,
								unidade: bloco.unidade === undefined ? "todas" : bloco.unidade,
								personalizados: bloco.personalizados === undefined ? [] : bloco.personalizados.map(item => {
									return {
										...item,
										indice: item.tipo === 'resultado' ? 99999 : item.indice,
										ordem: item.tipo === 'resultado' ? 99999 : item.ordem
									}
								})
							};

							unidadeAux.personalizados = atualizarIndicesDuplicados(unidadeAux.personalizados);

							unidadeNegocio.unidadeBloco = unidadeNeg;
							unidadeNeg.push(unidadeAux);
						}
						bloco.unidade == undefined ? $("input[name=unidade" + bloco.index + "][value=todas]").prop('checked', true) : $("input[name=unidade" + bloco.index + "][value=" + bloco.unidade + "]").prop('checked', true);
						var auxCount = 0;


						bloco.personalizados.sort(function(a, b) {
							if (a.ordem > b.ordem) {
								return 1;
							}
							if (a.ordem < b.ordem) {
								return -1;
							}
							// a must be equal to b
							return 0;
						});

						bloco.personalizados.forEach(function(campo) {
							if (campo.tipo == 'validacao') existeValidacao = true;

							if (campo.ordem == undefined) {
								campo["ordem"] = campo.tipo === 'resultado' ? 999999 : campo.indice;
							}

							// campo.ordem != campo.indice ? campo.ordem : campo.indice;
							campo.indice = campo.ordem != campo.indice ? campo.indice : campo.ordem;
							count = campo.indice;
							campo.options.forEach(function(option) {
								option['indiceCampo'] = campo.indice;
							});
							if (!campo.index) {
								if (campo.indice === parseInt(campos)) {
									//bloco.personalizados.splice(campo.indice,1);
									campo['index'] = campo.tipo === 'resultado' ? 999999 : bloco.index;
									campo['calculo'] = campo.calculo == "SIM" ? "SIM" : "NAO";

									campo.options.forEach(function(option) {
										option['indiceCampo'] = campo.indice;
										option['indice'] = auxCount;
										auxCount++;
									});
								}
							}

							let novoCampo = criaCampo(campo.titulo, bloco.index);
							$('#camposBody' + bloco.index).append($(novoCampo));

							if (config.tags && config.tags.length && config.tags.length > 0) {

								$(".vinculo" + count).append('<option value="tag">Equipamento</option>');
							}

							if (config.pessoas && config.pessoas.length && config.pessoas.length > 0) {

								$(".vinculo" + count).append('<option value="pessoa">Pessoa</option>');
							}

							if (config.documentos && config.documentos.length && config.documentos.length > 0) {

								$(".vinculo" + count).append('<option value="sgdoc">Documento</option>');
							}
							$("input[name=ordem" + campo.indice + "]").val(campo.ordem);
							$(".tipo" + campo.indice + " option[value='" + campo.tipo + "']").attr("selected", "selected");
							$(".vinculo" + campo.indice + " option[value='" + campo.vinculo + "']").attr("selected", "selected");
							campo.calculo == "SIM" ? $("#calculo" + campo.indice + "").prop('checked', true) : $("#calculo" + campo.indice + "").prop('checked', false);

							if (campo.tipo !== 'selecionavel' && campo.tipo !== 'fixo') {
								$('#editarTipo' + campo.indice).hide();
							}
							// Validando se há campo de apontamento
							if (campo.tipo == 'validacao' && campo.indiceCampoValidacao != undefined) {
								$(`#camposBody${campo.index} .apontar${campo.indice}`).removeAttr('disabled').removeClass('desabilitado').val(campo.indiceCampoValidacao)
							}

							if (campo.tipo == 'resultado') {
								$(`.campo${count}`).addClass('hide');
							}

							campos++;
						});
						blocos++;
					});
					//campos--;
					count = campos;
					//blocos--;
					countAdd = blocos;

					unidadeNegocio.unidadeBloco = unidadeNeg;
					$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));

				} else {
					$('[data-id="tipopessoa"]').hide();
				}

				unidadeNegocio = config;

			} else {
				alertAtencao("Configuração armazenada inválida", "Erro de Configuração");
			}

		} else {
			$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
		}

		function atualizarIndicesDuplicados(data) {
			data.forEach(obj => {
				let seenIndices = new Set();

				obj.options.forEach(option => {
					while (seenIndices.has(option.indice)) {
						option.indice++;
					}
					seenIndices.add(option.indice);
					option.ordem = option.indice;
				});
			});

			return data;
		}

		$(function() {
			$(".teste").sortable({
				handle: ".sortable-item",
				cursor: "move",
				opacity: 0.7,

				stop: function() {
					$('input.currentposition').each(function(idx) {
						$(this).attr("value", idx);
					});
				},
				update: function(event, objUi) {
					unidadeNeg.forEach((config, keyConfig) => {
						if (config.index == objUi.item.data('indexconfig')) {
							config.personalizados.forEach((campo, keyCampo) => {
								if (campo.tipo == 'validacao') {
									const campoApontamento = objUi.item.parent().find(`[data-campo="${campo.indiceCampoValidacao}"]`);

									if (campoApontamento)
										unidadeNeg[keyConfig].personalizados[keyCampo].indiceCampoValidacao = campoApontamento.index();
								}

								unidadeNeg[keyConfig].personalizados[keyCampo] = {
									...campo,
									ordem: objUi.item.parent().find(`[data-campo="${campo.indice}"]`).index() + 1,
									indice: objUi.item.parent().find(`[data-campo="${campo.indice}"]`).index()
								}
							})
						}
					})

					// ordenarBloco();
					unidadeNegocio.unidadeBloco = unidadeNeg;
					$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
					// atualizaJson();
				}

			}).disableSelection();
		});

		function ordenarBloco() {
			$.each($(".novocampo"), function(i, otr) {
				var indiceCampo = $(otr).attr("data-campo");

				unidadeNeg.forEach(function(campos) {
					campos.personalizados.forEach(function(p) {
						if (indiceCampo == p.indice) {
							p.ordem = i;
						}
					});
				});

				unidadeNegocio.unidadeBloco = unidadeNeg;
				$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
			});
		}
	}

	$(".historicoUn").webuiPopover({
		trigger: "click",
		placement: "right",
		width: 500,
		delay: {
			show: 300,
			hide: 0
		}
	});

	$(".btnhistperfil").webuiPopover({
		trigger: "click",
		placement: "right",
		width: 500,
		delay: {
			show: 300,
			hide: 0
		}
	});


	function removerTipoResultadoSelect() {
		$(`.tipocampo option[value="validacao"]`).remove()
	}

	function alteravalordescr(campo, valor, tabela, inid, texto) {
		htmlTrModelo = "";
		htmlTrModelo = `<div id="alt${campo}${inid}">
		<table class="table table-hover">
			<tr>
				<td>${texto}</td>
				<td>
					<input name="_h1_i_${tabela}_idobjeto" value="${inid}" type="hidden">
					<input name="_h1_i_${tabela}_campo" value="${campo}" type="hidden">
					<input name="_h1_i_${tabela}_tipoobjeto" value="prodserv" type="hidden">
					<input name="_h1_i_${tabela}_valor_old" value="${valor}" type="hidden">
					<input name="_h1_i_${tabela}_valor" value="${valor}" type="text">
				</td>
			</tr>
			<tr>
				<td>Justificativa:</td>
				<td>
					<input id="justificativa" name="_h1_i_${tabela}_justificativa" vnulo class="size50">
				</td>
			</tr>
		</table>
	</div>`;


		if (campo == 'previsaoentrega') {
			var objfrm = $(htmlTrModelo);
			objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");
		} else {
			var objfrm = $(htmlTrModelo);
			objfrm.find("#ndroptipo option[value='" + valor + "']").attr("selected", "selected");
			objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");
		}

		strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='CB.post();' style='float: right; margin-top: 14px;'><i class='fa fa-circle'></i>Salvar</button></strong>";

		CB.modal({
			titulo: strCabecalho,
			corpo: "<table>" + objfrm.html() + "</table>",
			classe: 'sessenta',
			aoAbrir: function(vthis) {
				/*
                $("[name='_h1_i_modulohistorico_valor']").daterangepicker({
                    "singleDatePicker": true,
                    "locale": CB.jDateRangeLocale
                }).on('apply.daterangepicker', function(ev, picker) {
                    console.log(picker.startDate.format('YYYY-MM-DD'));
                    $(this).html(picker.startDate.format("DD/MM/YYYY") || "");
                });
				*/

				$(`[name="_h1_i_${tabela}_valor"]`).val(valor);
			}
		});
	}

	function modalhist(div) {
		var htmloriginal = $(`#${div}`).html();

		CB.modal({
			titulo: "</strong>Histórico de Alteração:</strong>",
			corpo: htmloriginal,
			classe: 'sessenta'
		});
	}

	function alteravalor(campo, valor, tabela, inid, texto) {
		htmlTrModelo = "";
		htmlTrModelo = `<div id="alt${campo}${inid}">
            <table class="table table-hover">
                <tr>
                    <td>${texto}</td>
                    <td>
                        <input name="_h1_i_${tabela}_idobjeto" value="${inid}" type="hidden">
                        <input name="_h1_i_${tabela}_campo" value="${campo}" type="hidden">
                        <input name="_h1_i_${tabela}_tipoobjeto" value="prodserv" type="hidden">
                        <input name="_h1_i_${tabela}_valor_old" value="${valor}" type="hidden">
                       	<select  id="ndroptipo"  name="_h1_i_${tabela}_valor" class="size10" >
							<?= fillselect(ProdServController::buscarUnidadeVolume(), $valor); ?>
						</select>
                    </td>
                </tr>
                <tr>
                    <td>Justificativa:</td>
                    <td>
                        <select id="justificativa" name="_h1_i_${tabela}_justificativa" onchange="alteraoutros(this,'${tabela}')" vnulo class="size50">
                            <?= fillselect(ProdServController::$_justificativa) ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>`;

		if (campo == 'previsaoentrega') {
			var objfrm = $(htmlTrModelo);
			objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");
		} else {
			var objfrm = $(htmlTrModelo);
			objfrm.find("#ndroptipo option[value='" + valor + "']").attr("selected", "selected");
			objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");
		}

		strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='CB.post();' style='float: right; margin-top: 14px;'><i class='fa fa-circle'></i>Salvar</button></strong>";

		CB.modal({
			titulo: strCabecalho,
			corpo: "<table>" + objfrm.html() + "</table>",
			classe: 'sessenta',
			aoAbrir: function(vthis) {
				/*
                $("[name='_h1_i_modulohistorico_valor']").daterangepicker({
                    "singleDatePicker": true,
                    "locale": CB.jDateRangeLocale
                }).on('apply.daterangepicker', function(ev, picker) {
                    console.log(picker.startDate.format('YYYY-MM-DD'));
                    $(this).html(picker.startDate.format("DD/MM/YYYY") || "");
                });
				*/

				$(`[name="_h1_i_${tabela}_valor"]`).val(valor);
			}
		});
	}

	function alteraoutros(vthis, tabela) {
		valor = $(vthis).val();
		if (valor == 'OUTROS') {

			$(vthis).parent().append('<input style="margin-top:4px;" id="justificaticaText" name="_h1_i_' + tabela + '_justificativa" value="" class="size50" type="text" placeholder="Digite aqui a sua justificativa" />');
			$('#justificativa').remove();
		} else {
			$('#justificaticaText').remove();
		}
	}

	function alterarValor(campo, valor, tabela, inid, texto) {
		htmlTrModelo = $(`#modulohistorico`).html();
		htmlTrModelo = htmlTrModelo.replace("#namerotulo", texto);
		htmlTrModelo = htmlTrModelo.replace("#name_tipoobjeto", "_h1_i_" + tabela + "_tipoobjeto");
		htmlTrModelo = htmlTrModelo.replace("#name_idobjeto", "_h1_i_" + tabela + "_idobjeto");
		htmlTrModelo = htmlTrModelo.replace("#name_auditcampo", "_h1_i_" + tabela + "_campo");
		htmlTrModelo = htmlTrModelo.replace("#name_campo", "_h1_i_" + tabela + "_valor");
		htmlTrModelo = htmlTrModelo.replace("#name_valorold", "_h1_i_" + tabela + "_valor_old");
		htmlTrModelo = htmlTrModelo.replace("#name_justificativa", "_h1_i_" + tabela + "_justificativa");
		htmlTrModelo = htmlTrModelo.replace("#valor_campo_old", valor);
		htmlTrModelo = htmlTrModelo.replace("#valor_campo", valor);
		var objfrm = $(htmlTrModelo);
		objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");
		objfrm.find("[name='_h1_i_modulohistorico_campo']").attr("value", campo);

		strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='CB.post();'><i class='fa fa-circle'></i>Salvar</button></strong>";

		CB.modal({
			titulo: strCabecalho,
			corpo: "<table>" + objfrm.html() + "</table>",
			classe: 'sessenta',
			aoAbrir: function(vthis) {
				$("[name='_h1_i_modulohistorico_valor']").change(function() {
					var obj = {};
					valueCampo = $("[name='_h1_i_modulohistorico_valor']").val();
					$("[name='_1_" + acao + "_prodserv_" + campo + "']").val(valueCampo);
					if (valueCampo == 'Y' && campo == 'venda') {
						$("[name='_1_" + acao + "_prodserv_nfe']").val('Y');
					}
				});
			}
		});
	}

	if (prodservTipo == "SERVICO") {

		function atualizaTipoParametros(element, indiceOption, indiceParametro = false) {
			indiceOptionAtiva = indiceOption;

			localStorage.setItem('lastIndiceCampoParametros', indiceOption.toString());
			optionCountParametros = 0;

			CB.modal({
				titulo: 'Configuração de parâmetros',
				corpo: criaConfigParametros(indiceOption, indiceParametro),
				aoFechar: () => atualizaTipo($(`#editarTipo${indiceCampoPersonalizadoAtivo}`), indiceCampoPersonalizadoAtivo, indexCampoAtivo, false),
				rodape: `<button id="cancelarOption" type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
						<span id="salvarOption" class="btn btn-success" onclick="atualizaJson();" data-dismiss="modal">Salvar</span>`,
			});

			unidadeNeg.forEach(function(campos, chaveCampo) {
				const parametros = campos.personalizados.length ? (campos.personalizados.find(itemOption => itemOption.indice == indiceParametro)?.options?.find(itemParametro => itemParametro.indice == indiceOption)?.parametros ?? []) : [];

				parametros.forEach(function(parametro) {
					CB.oModal.find('.optionsBodyParametros').append(criaOptionParametros(parametro.valorinicial, parametro.valorfinal, parametro.resultado, indiceParametro, parametro.indice))
					optionCountParametros++;
				});
			});

			// Ordenação
			CB.oModal.find('.optionsBodyParametros').sortable({
				helper: "original",
				handle: '.move',
				update: (e, ui) => {
					const indiceOption = ui.item.data('option');
					const indiceCampo = ui.item.data('campo');

					unidadeNeg.forEach((campos, key) => {
						if (!campos.personalizados.length) return;

						let posicaoCampoPersonalizado = campos.personalizados.findIndex(item => item.indice == indiceCampoPersonalizadoAtivo);
						let posicaoOption = campos['personalizados'][posicaoCampoPersonalizado]['options'].findIndex(item => item.indice == indiceOptionAtiva);

						unidadeNeg[key]['personalizados'][posicaoCampoPersonalizado]['options'][posicaoOption].parametros = unidadeNeg[key]['personalizados'][posicaoCampoPersonalizado]['options'][posicaoOption].parametros.map(item => {
							return {
								...item,
								ordem: CB.oModal.find(`.optionsBodyParametros .option${item.indice}`).index() + 1
							}
						}).sort((a, b) => (a.ordem || 0) - (b.ordem || 0));
					})

					$(`.option${indiceCampo}`)
						.removeClass(`.option${indiceCampo}`)
						.addClass($(`.option${indiceCampo}`).index());

					unidadeNegocio.unidadeBloco = unidadeNeg;
					$("#jsonconfig").val(JSON.stringify(unidadeNegocio));
				}
			});
		}

		function criaConfigParametros(indiceOption, countOption) {
			return `<p>Adicione abaixo todas as opçoes selecionáveis</p>
					<div class="row">
						<div class="col-lg-3">
							<span id="addOptionParametros" onclick="addOptionParametro(${indiceOption}, ${countOption});" class="btn btn-success size15" style="padding: 10px;" title="Criar opções dinâmicas">
								<i class="fa fa-plus pointer"></i> <b>Opção selecionável</b>
							</span>
						</div>
					</div>
					<br>
					<div class="panel panel-default">
						<div class="panel-heading w-100 d-flex flex-wrap">
							<div class="col-xs-1"></div>
							<div class="col-xs-2"><span>De</span></div>
							<div class="col-xs-2"><span>Até</span></div>
							<div class="col-xs-5"><span>Resultado</span></div>
							<div class="col-xs-1"><span>Ações</span></div>
						</div>
						<div class="panel-body optionsBodyParametros optionsBodyParametros${countOption} table-hover table-striped" data-indiceOption="${indiceOption}" data-indiceParametro="${countOption}"></div>
					</div>`
		}

		function addOptionParametro(indiceOption, countOption) {
			let indiceCampoUltimo = localStorage.getItem('lastIndiceCampoParametros');

			CB.oModal.find('.optionsBodyParametros').append(criaOptionParametros('', '', '', indiceOption, optionCountParametros + 1))

			let parametro = {
				indiceCampo: indiceOptionAtiva,
				indice: optionCountParametros + 1,
				ordem: optionCountParametros + 1,
				valorinicial: "",
				valorfinal: "",
				resultado: "",
			};

			unidadeNeg.forEach(function(campos, campoKey) {
				campos.personalizados.forEach(function(personalizado, personalizadoKey) {
					if (personalizado.indice == indiceCampoPersonalizadoAtivo)
						personalizado.options.forEach(function(option, optionKey) {
							if (option && !option.parametros) option.parametros = [];

							if (option.indice == indiceOption) {
								unidadeNeg[campoKey]['personalizados'][personalizadoKey]['options'][optionKey]['parametros'] = [...option.parametros, parametro]
							}
						});
				});
			});

			unidadeNegocio.unidadeBloco = unidadeNeg;
			$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));

			optionCountParametros++;
		}

		function criaOptionParametros(valorinicial, valorfinal, resultado, indiceOption, count) {
			valorinicial = (valorinicial === undefined) ? '' : valorinicial;
			valorfinal = (valorfinal === undefined) ? '' : valorfinal;
			resultado = (resultado === undefined) ? '' : resultado;

			return `<div class="row d-flex flex-wrap align-items-center novooption mb-4 option${count}" data-option="${indiceOption}" data-campo="${count}">
									<div class="col-xs-1">
										<i class="fa fa-arrows cinzaclaro hover move" title="Ordenar"></i>
									</div>
									<div class="col-lg-2">
										<input type="number" placeholder="Valor Inicial" nomecampo="valorinicial" value="${valorinicial}" onchange="atualizaOptionParametros(this, ${indiceOption}, ${count});">
									</div>
									<div class="col-lg-2">
										<input type="number" placeholder="Valor Final" nomecampo="valorfinal" value="${valorfinal}" onchange="atualizaOptionParametros(this, ${indiceOption}, ${count});">
									</div>
									<div class="col-lg-5" style="text-align: center;">
										<input type="text" placeholder="Resultado" nomecampo="resultado" value="${resultado}" onchange="atualizaOptionParametros(this, ${indiceOption}, ${count});">
									</div>
									<div class="col-lg-1">
										<span class="btn btn-sm btn-danger size3" onclick="deleteOptionParametros(this, ${indiceOption}, ${count});"
											title="Excluir">
											<i class="fa fa-minus pointer"></i>
										</span>
									</div>
								</div>`;
		}

		function atualizaOptionParametros(element, indiceOption, index) {
			let indiceCampo = localStorage.getItem('lastIndiceCampoParametros');
			unidadeNeg.forEach(function(campos) {
				campos.personalizados.forEach(function(personalizado) {
					if (personalizado.indice == indiceCampoPersonalizadoAtivo)
						personalizado.options.forEach(function(option) {
							if (option.indice == indiceOptionAtiva)
								option.parametros.forEach(function(parametro) {
									if (parametro.indice === parseInt(index)) {
										parametro[$(element).attr('nomecampo')] = $(element).val();
									}
								});
						});
				});
			});
			unidadeNegocio.unidadeBloco = unidadeNeg;
			$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
		}

		function deleteOptionParametros(element, indiceOption, indiceParametro) {
			$(element).closest('.novooption').remove();
			const camposPersonalizados = unidadeNeg.map(item => item.personalizados)[0];

			unidadeNeg.forEach((campo, campoIndex) => {
				campo.personalizados.forEach((personalizado, personalizadoIndex) => {
					if (personalizado.indice == indiceCampoPersonalizadoAtivo)
						personalizado.options.forEach((option, optionIndex) => {
							if (indiceOptionAtiva == option.indice && option.parametros.some(param => param.indice == indiceParametro))
								unidadeNeg[campoIndex].personalizados[personalizadoIndex].options[indiceOptionAtiva].parametros = option.parametros.filter(item => item.indice != indiceParametro);
						})
				})
			})

			// unidadeNegocio.unidadeBloco = unidadeNeg.filter(item => item.personalizados. personaindiceCampoPersonalizadoAtivo);
			$("#jsonconfig").val("" + JSON.stringify(unidadeNegocio));
		}
	}

	$('body').on('click', '#cancelarOptionParametros', function() {
		$(`#${$(this).data('id')}`).modal('hide');
		$('body:has( .modal.in)').addClass('modal-open');
		$('.modal.in').modal('handleUpdate');
	})

	function atualizaObjetoVinculo(element) {
		const idTagTipo = $(element).parent().parent().parent().find('select.auto-tipotag').val();
		const idTag = $($(element).parent().find('.tag-select').get(1)).val();

		if (!idTag) {
			return excluirObjetoVinculo(element, idTagTipo);
		}

		if (idTagTipo) {
			$.ajax({
				type: "post",
				url: "ajax/prodserv.php",
				data: {
					vopcao: 'atualizarVinculoComTagTipo',
					idobjeto: idTag,
					idobjetovinc: idTagTipo,
					idProdserv: idprodserv,
				},
				success: function(data) {
					console.log(data);

					$(element).addClass('hide');
					$(element).parent().parent().parent().find('.remover').removeClass('hide');
					CB.post();
				},
				error: function(objxmlreq) {
					alert('Erro:<br>' + objxmlreq.status);

				}
			}) //$.ajax
		} else {
			alertAtencao('ocorreu um erro!');
		}
	}

	function excluirObjetoVinculo(element, idTagTipo = false) {

		if (!idTagTipo) {
			var idTagTipo = $(element).parent().parent().find('.auto-tipotag').attr('cbvalue');
			var tagSelect = $($(element).parent().parent().find('.tag-select').get(1));
		} else {
			var tagSelect = $($(element).parent().find('.tag-select').get(1));
		}

		if (idTagTipo) {
			$.ajax({
				type: "post",
				url: "ajax/prodserv.php",
				data: {
					vopcao: 'excluirVinculoComTagTipo',
					idobjetovinc: idTagTipo,
					idProdserv: idprodserv,
				},
				success: function(data) {
					tagSelect.selectpicker('deselectAll');

					$(element).addClass('hide');
					$(element).parent().parent().find('.atualizar-tag').addClass('hide');
					CB.post();
				},
				error: function(objxmlreq) {
					alert('Erro:<br>' + objxmlreq.status);

				}
			}) //$.ajax
		}
	}

	if(prodservTipo == 'PRODUTO' && CB.acao == 'u'){
		
		function verificarSelecao() {
			return Array.from(document.getElementsByName("configuracao_produto")).some(item => item.checked);
		}

		CB.on('prePost', function(){
			if(!verificarSelecao())
			{
				alertAtencao('Selecione ao menos uma opção de configuração.');
				return false;
			}
		}) 
	}

	//------- Funções Módulo -------

	$('.timeout-hist').on('click', function() {
		montarHistCampo(historicoCampoVlrVenda);
	})

	//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape1
</script>