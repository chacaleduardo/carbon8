<script>
	//------- Injeção PHP no Jquery -------
	var vTipo = "<?=$_1_u_prodserv_tipo ?>";
	var vIdProdServ = "<?=$_1_u_prodserv_idprodserv ?>";
	var vFormulado = "<?=$_1_u_prodserv_fabricado ?>";
	var jCadInsumos = <?=$JSON->encode($arrCadInsumos)?> || "";
	var jCadInsumosLoteServico = <?=$JSON->encode($arrCadInsumosServico)?> || "";
	//Pega o IdFluxo do inicio
	<? $rowFluxo = FluxoController::getidfluxostatusInativo('formulaprocesso', 'REVISAO', 'INICIO'); ?>
	var idfluxostatusInicio = <?=$rowFluxo['idfluxostatus'] ?>;

	<? $rowFluxoServico = FluxoController::getidfluxostatusInativo('loteservico', 'REVISAO', 'INICIO'); ?>
	var idfluxostatusInicioServico = <?=$rowFluxoServico['idfluxostatus'] ?>;

	//Inativo
	<? $rowFluxoInativo = FluxoController::getidfluxostatusInativo('formulaprocesso', 'INATIVO'); ?>
	idfluxostatusInativo = <?=$rowFluxoInativo['idfluxostatus'];?>;

	<? $rowFluxoInativoServico = FluxoController::getidfluxostatusInativo('loteservico', 'INATIVO'); ?>
	idfluxostatusInativoServico = <?=$rowFluxoInativoServico['idfluxostatus'];?>;
	//------- Injeção PHP no Jquery -------

	//------- Funções JS -------
	//Autocomplete Cadastro de Insumos
	$(".prodservformulains_idprodserv").autocomplete({
		source: jQuery.map(jCadInsumos, function(item, id) {
			return {
				"label": item.descr,
				value: id,
				"codprodserv": item.codprodserv,
				"especial": item.especial
			}
		}),
		create: function(event, ui) {
			$this = $(this);
			vDescr = evalJson(`jCadInsumos[${$this.cbval()}].descr`);

			if ($this.cbval() && vDescr) {

				$this.val(vDescr); //Recupera a descrição de cada input durante a inicialização
				$this.data('ui-autocomplete')._renderItem = function(ul, item) {
					lbItem = item.label + " - <span class=cinzaclaro>" + item.codprodserv + "</span>";
					return $('<li>')
						.append('<a>' + lbItem + '</a>')
						.appendTo(ul);
				};
			} else {
				$this.css("color", "#d43f3a").css("border", "1px solid #d43f3a").closest("tr").find(".insumoinativo").removeClass("hidden")
				$this.attr("placeholder", "Erro: Insumo com status INATIVO");
			}
		}
	});

	$(".prodservloteservicoins_idprodserv").autocomplete({
		source: jQuery.map(jCadInsumosLoteServico, function(item, id) {
			return {
				"label": item.descr,
				value: id,
				"codprodserv": item.codprodserv,
				"especial": item.especial
			}
		}),
		create: function(event, ui) {
			$this = $(this);
			vDescr = evalJson(`jCadInsumosLoteServico[${$this.cbval()}].descr`);

			if ($this.cbval() && vDescr) {

				$this.val(vDescr); //Recupera a descrição de cada input durante a inicialização
				$this.data('ui-autocomplete')._renderItem = function(ul, item) {
					lbItem = item.label + " - <span class=cinzaclaro>" + item.codprodserv + "</span>";
					return $('<li>')
						.append('<a>' + lbItem + '</a>')
						.appendTo(ul);
				};
			} else {
				$this.css("color", "#d43f3a").css("border", "1px solid #d43f3a").closest("tr").find(".insumoinativo").removeClass("hidden")
				$this.attr("placeholder", "Erro: Insumo com status INATIVO");
			}
		}
	});

	$("#tbinsumos tr .move").on('mousedown', sortableEvent);

	//Permitir dropar o insumo
	$(".soltavel").droppable({
		drop: function(event, ui) {
			$this = $(this); //TR
			var idprodservformulains = ui.draggable.attr("idprodservformulains");
			var idprativ = $this.attr("idprativ");
			var idprodservprproc = $this.attr("idprodservprproc");
			var iqtd = ui.draggable.find("[name*=prodservformulains_qtdi]").val();
			iprocprativinsumo(idprodservprproc, idprodservformulains, idprativ, iqtd);
		}
	});

	$('.colorpalette').colorPalette({
		colors: [CB.arrCores]
	}).on('selectColor', function(e) {
		e.element.closest(".agrupamento")
			.css("border-color", e.color)
			.find("[name*=_u_prodservformula_cor]").val(e.color);
	});
	//------- Funções JS -------

	//------- Funções Módulo -------
	function trocaedicao(vthis, identficador, versao, status, idfluxostatus) 
	{
		let url_string = window.location.href;
		let url = new URL(url_string);
		let idprodserv = url.searchParams.get("idprodserv");
		let val = $(vthis).val();

		if (val == 'Y') {
			if (confirm('Deseja prosseguir?')) {
				CB.post({
					objetos: {
						'_xxx_u_prodservformula_idprodservformula': $('[name="_psf' + identficador + '_u_prodservformula_idprodservformula"]').val(),
						'_xxx_u_prodservformula_editar': 'N',
						'_xxx_u_prodservformula_status': status,
						'_xxx_u_prodservformula_idfluxostatus': idfluxostatus,
						'_versao_': versao,
						'_idprodserv_': idprodserv
					}
				})
			}
		}
		if (val == 'N') {
			if (confirm('Ao tornar esta formula editável ela será versionada\n Deseja prosseguir?')) {
				CB.post({
					objetos: {
						'_xxx_u_prodservformula_idprodservformula': $('[name="_psf' + identficador + '_u_prodservformula_idprodservformula"]').val(),
						'_xxx_u_prodservformula_editar': 'Y',
						'_xxx_u_prodservformula_versao': versao + 1,
						'_xxx_u_prodservformula_status': status,
						'_xxx_u_prodservformula_idfluxostatus': idfluxostatus,
						'_idprodserv_': idprodserv
					}
				})
			}
		}
	}

	function especieform(inidprodservformula, inidplantel) 
	{
		str = `&_psf1_u_prodservformula_idprodservformula=${inidprodservformula}&_psf1_u_prodservformula_idplantel=${inidplantel}`;
		CB.post({
			objetos: str,
			parcial: true
		})
	}

	function duplicar(idprodserv) 
	{
		CB.post({
			objetos: `_duplicar_u_prodservformula_idprodservformula=${idprodserv}`,
			parcial: true
		});
	}

	function novoatividade(idprodserv) 
	{
		CB.post({
			objetos: `_x_i_prodservprproc_idprodserv=${idprodserv}`
		});
	}

	function excluiratividade(inid) 
	{
		if (confirm("Deseja excluir a configuração de Processo?")) 
		{
			CB.post({
				objetos: `_x_d_prodservprproc_idprodservprproc=${inid}`
			});
		}
	}

	function novaformulacao(inordem, idprodserv, idunidadeest) 
	{
		CB.post({
			objetos: {
				"_x_i_prodservformula_idprodserv": idprodserv,
				"_x_i_prodservformula_ordem": inordem,
				"_x_i_prodservformula_idunidadeest": idunidadeest,
				"_x_i_prodservformula_idfluxostatus": idfluxostatusInicio,
				"_x_i_prodservformula_status": 'REVISAO',
			}
		});
	}

	function inativarformulacao(inidprodservformula) 
	{
		if (confirm("Deseja realmente excluir a formulação?")) 
		{
			let obj = {
				"_psf1_u_prodservformula_idprodservformula": inidprodservformula,
				"_psf1_u_prodservformula_status": 'INATIVO',
				"_psf1_u_prodservformula_idfluxostatus": idfluxostatusInativo
			}

			$('[proc_idprodservformula=' + inidprodservformula + ']').each((i, o) => {
				obj[`_proc${i}_d_procprativinsumo_idprocprativinsumo`] = o.value
			})

			CB.post({
				objetos: obj,
				parcial: true
			});
		}
	}

	function trocaedicaoLoteServico(vthis, identficador, versao, status, idfluxostatus) 
	{
		let url_string = window.location.href;
		let url = new URL(url_string);
		let idprodserv = url.searchParams.get("idprodserv");
		let val = $(vthis).val();

		if (val == 'Y') {
			if (confirm('Deseja prosseguir?')) {
				CB.post({
					objetos: {
						'_xxx_u_prodservloteservico_idprodservloteservico': $('[name="_pls' + identficador + '_u_prodservloteservico_idprodservloteservico"]').val(),
						'_xxx_u_prodservloteservico_editar': 'N',
						'_xxx_u_prodservloteservico_status': status,
						'_xxx_u_prodservloteservico_idfluxostatus': idfluxostatus,
						'_versao_': versao,
						'_idprodserv_': idprodserv
					}
				})
			}
		}
		if (val == 'N') {
			if (confirm('Ao tornar esta formula editável ela será versionada\n Deseja prosseguir?')) {
				CB.post({
					objetos: {
						'_xxx_u_prodservloteservico_idprodservloteservico': $('[name="_pls' + identficador + '_u_prodservloteservico_idprodservloteservico"]').val(),
						'_xxx_u_prodservloteservico_editar': 'Y',
						'_xxx_u_prodservloteservico_versao': versao + 1,
						'_xxx_u_prodservloteservico_status': status,
						'_xxx_u_prodservloteservico_idfluxostatus': idfluxostatus,
						'_idprodserv_': idprodserv
					}
				})
			}
		}
	}


	function novaFormulacaoLoteServico(inordem, idprodserv, idunidadeest) 
	{
		CB.post({
			objetos: {
				"_x_i_prodservloteservico_idprodserv": idprodserv,
				"_x_i_prodservloteservico_ordem": inordem,
				"_x_i_prodservloteservico_idunidadeest": idunidadeest,
				"_x_i_prodservloteservico_idfluxostatus": idfluxostatusInicioServico,
				"_x_i_prodservloteservico_status": 'REVISAO',
			}
		});
	}

	function inativarFormulacaoLoteServico(inidprodservformula) 
	{
		if (confirm("Deseja realmente excluir a Lote Serviço?")) 
		{
			let obj = {
				"_psf1_u_prodservloteservico_idprodservloteservico": inidprodservformula,
				"_psf1_u_prodservloteservico_status": 'INATIVO',
				"_psf1_u_prodservloteservico_idfluxostatus": idfluxostatusInativoServico
			}

			CB.post({
				objetos: obj,
				parcial: true
			});
		}
	}

	function inativarinsumoLoteServico(idprodservloteservicoins) 
	{
		if (confirm("Deseja realmente inativar o Insumo do Produto?")) 
		{
			CB.post({
				objetos: `_x_u_prodservloteservicoins_idprodservloteservicoins=${idprodservloteservicoins}&_x_u_prodservloteservicoins_status=INATIVO`
			});
		}
	}

	function novoInsumoLoteServico(idprodservloteservico, inord) 
	{
		CB.post({
			objetos: `_x_i_prodservloteservicoins_ord=9999&_x_i_prodservloteservicoins_idprodservloteservico=${idprodservloteservico}&_x_i_prodservloteservicoins_ord=${inord}&_x_i_prodservloteservicoins_status=ATIVO`
		});
	}

	function resetplantelLoteServico(idprodservloteservico) 
	{
		if (confirm("Deseja realmente limpar a especie da formula?")) 
		{
			CB.post({
				objetos: `_x_u_prodservloteservico_idprodservloteservico=${idprodservloteservico}&_x_u_prodservloteservico_idplantel=null`
			});
		}
	}

	function ativar(inidprodservformula) 
	{
		if (confirm("Deseja realmente ativar a formulação?")) 
		{
			let obj = {
				"_psf1_u_prodservformula_idprodservformula": inidprodservformula,
				"_psf1_u_prodservformula_idfluxostatus": idfluxostatusInicio,
				"_psf1_u_prodservformula_status": 'REVISAO'
			}
			CB.post({
				objetos: obj,
				parcial: true
			});
		}
	}

	function novoinsumo(inIdprodservformula, inord) 
	{
		CB.post({
			objetos: `_x_i_prodservformulains_ord=9999&_x_i_prodservformulains_idprodservformula=${inIdprodservformula}&_x_i_prodservformulains_ord=${inord}&_x_i_prodservformulains=ATIVO`
		});
	}

	function resetplantel(inidprodservformula) 
	{
		if (confirm("Deseja realmente limpar a especie da formula?")) 
		{
			CB.post({
				objetos: `_x_u_prodservformula_idprodservformula=${inidprodservformula}&_x_u_prodservformula_idplantel=null`
			});
		}
	}

	function inativarinsumo(inidprodservformulains) 
	{
		if (confirm("Deseja realmente inativar o Insumo do Produto?")) 
		{
			CB.post({
				objetos: `_x_u_prodservformulains_idprodservformulains=${inidprodservformulains}&_x_u_prodservformulains_status=INATIVO`
			});
		}
	}

	function excluirprocprativinsumo(inid) 
	{
		if (confirm("Deseja retirar o insumo?")) 
		{
			CB.post({
				objetos: `_x_d_procprativinsumo_idprocprativinsumo=${inid}`
			});
		}
	}

	//Permitir ordenar/arrastar os TR de insumos
	function sortableEvent() 
	{
		let $tBody = $("#tbinsumos tbody");
		//Permitir ordenar/arrastar os TR de insumos
		$tBody.sortable({
			update: function(event, objUi) {
				ordenaInsumos();
			},
			stop: function(event, ui) {
				$(this).sortable("disable");
			}
		});

		$tBody.sortable('enable');
	}

	function ordenaInsumos() 
	{
		$.each($("#tbinsumos tbody").find("tr"), function(i, otr) {
			$(this).find(":input[name*=ord]").val(i);
		});
	}

	function iprocprativinsumo(inidprodservprproc, inidprodservformulains, inidprativ, iqtdi) 
	{
		CB.post({
			objetos: `_x_i_procprativinsumo_idprodservprproc=${inidprodservprproc}&_x_i_procprativinsumo_idprodservformulains=${inidprodservformulains}&_x_i_procprativinsumo_idprativ=${inidprativ}`

		});
	}

	/*
	 * Este procedimento leva en torno de 10~30 segundos para terminar. Por este motivo será chamado em paralelo, para não obstruir o usuário
	 */
	function Alteralistares(vthis) 
	{
		var idprodservformulains = $(vthis).attr('idprodservformulains');
		var listares = $(vthis).attr('listares');
		var novostatus, cor, novacor;
		if (listares == 'Y') 
		{
			cor = 'verdeclaro hoververde';
			novacor = 'laranja hoververmelho';
			CB.post({
				objetos: `_x_u_prodservformulains_idprodservformulains=${idprodservformulains}&_x_u_prodservformulains_listares=N`,
				parcial: true,
				msgSalvo: "Não Aparecer",
				posPost: function() {
					$(vthis).removeClass(cor);
					$(vthis).addClass(novacor);
					$(vthis).attr('listares', 'N');

				}
			});

		} else {
			cor = 'laranja hoververmelho';
			novacor = 'verdeclaro hoververde';
			CB.post({
				objetos: `_x_u_prodservformulains_idprodservformulains=${idprodservformulains}&_x_u_prodservformulains_listares=Y`,
				parcial: true,
				msgSalvo: "Status Alterado",
				posPost: function() {
					$(vthis).removeClass(cor);
					$(vthis).addClass(novacor);
					$(vthis).attr('listares', 'Y');

				}
			});
		}
	}

	function mostraval(inid) 
	{
		strCabecalho = `<strong>Produto(s) utilizado(s)&nbsp;&nbsp;</strong> 
						<div><button class="btn btn-default btn-dark" style="float: right; margin-top: -40px; margin-right: 60px; display: block;" onclick="tableToExcel(${inid})">
							<i class="fa fa-file-excel-o"></i>  Exportar Excel
						</button></div>`;
		htmloriginal = $("#valaorform" + inid).html();
		objfrm = $(htmloriginal);
		CB.modal({
			titulo: strCabecalho,
			corpo: objfrm.html(),
			classe: 'noventa',
			aoAbrir: function(vthis) {
				$("#valaorform" + inid).remove();
				CB.oModal.find('span[href^="#collapse-vallote-"]').on('click', function() {
					let vth = $(this);
					let kHref = CB.oModal.find(vth.attr('href'));

					if (kHref.hasClass('hidden')) {
						kHref.removeClass('hidden');
						vth.children('i').removeClass('fa-angle-right').addClass('fa-angle-down');
						vth.parent().parent().css('font-weight', 'bold');
					} else {
						kHref.addClass('hidden');
						vth.children('i').removeClass('fa-angle-down').addClass('fa-angle-right');
						vth.parent().parent().css('font-weight', 'normal');
					}
				});
				let vTotal = calculaValorAcumuladoPorIdlotecons();
				console.log(`Valor Total Acumulado: ${vTotal}`);
			},
			aoFechar: function(data) {
				$("#htmlmodal_" + inid).append(`
					<div id="valaorform${inid}" style="display:none;">
						<div class="row">
							${data.corpo}
						</div>
					</div>
				`);
			}
		});

	}

	function calculaValorAcumuladoPorIdlotecons(idlotecons = 0, $objPai = null, lvl = 0) 
	{
		let $objFilho = ($objPai === null) ? $(`div[lvl='${lvl}']`) : $objPai.siblings(`#collapse-vallote-${idlotecons}`).find(`div[lvl='${lvl}']`);
		var vTotal = 0;
		var vTotalUn = 0;

		$objFilho.each(function(i, o) {
			let $o = $(o);

			if ($o.children('[vallote]').length > 0) {
				vTotal += parseFloat($o.children('[vallote]').attr('vallote')) || 0;
			} else if ($o.children('[idlotecons-valloteacumulado]').length > 0) {
				let idlotecons = $o.children('[idlotecons-valloteacumulado]').attr('idlotecons-valloteacumulado');
				vTotal += calculaValorAcumuladoPorIdlotecons(idlotecons, $o, lvl + 1);
			}

			if ($o.children('[valloteun]').length > 0) {
				vTotalUn += parseFloat($o.children('[valloteun]').attr('valloteun')) || 0;
			} else if ($o.children('[idlotecons-valloteunacumulado]').length > 0) {
				let idlotecons = $o.children('[idlotecons-valloteunacumulado]').attr('idlotecons-valloteunacumulado');
				vTotalUn += calculaValorAcumuladoPorIdlotecons(idlotecons, $o, lvl + 1);
			}
		});

		if ($objPai != null) 
		{
			atributoValorProdserv = $objPai.children('[idlotecons-valloteacumulado]').attr('value');
			atributoQtdProdserv = $objPai.find(`.qtdun-${idlotecons}`).attr('value');
			$objPai.children('[idlotecons-valloteacumulado]').html(`
				<span style="float:right" title=" R$ ${vTotal}">
					${new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 4 }).format(vTotal)}
				</span>
			`);
			$objPai.children('[idlotecons-valloteacumulado]').attr('vallote', vTotal);
			$(`.idlotecons-valloteacumulado-table-${atributoValorProdserv}`).html(`<b> R$ ${new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 4 }).format(vTotal)}</b>`);
			
			valorUnitario = vTotal / atributoQtdProdserv;
			$objPai.children('[idlotecons-valloteunacumulado]').html(`
				<span style="float:right" title=" R$ ${valorUnitario}">
					${new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 4 }).format(valorUnitario)}
				</span>
			`);
			$objPai.children('[idlotecons-valloteunacumulado]').attr('valloteun', valorUnitario);
			$(`.idlotecons-valloteunacumulado-table-${atributoValorProdserv}`).html(`<b> R$ ${new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 4 }).format(vTotalUn)}</b>`);
			
		}

		return vTotal;
	}

	function mostraval2(inid) 
	{
		strCabecalho = `<strong>Produto(s) utilizado(s)&nbsp;&nbsp;(ver. duplicados)</strong> 
						<div><button class="btn btn-default btn-dark" style="float: right; margin-top: -40px; margin-right: 60px; display: block;" onclick="tableToExcel(${inid}, 'clone')">
							<i class="fa fa-file-excel-o"></i>  Exportar Excel
						</button></div>`;
		htmloriginal = $("#valaorformclone" + inid+"_duplicados").html();
		objfrm = $(htmloriginal);
		CB.modal({
			titulo: strCabecalho,
			corpo: objfrm.html(),
			classe: 'noventa',
			aoAbrir: function(vthis) {
				$("#valaorformclone" + inid+"_duplicados").remove();
				CB.oModal.find('span[href^="#collapse-vallote-"]').on('click', function() {
					let vth = $(this);
					let kHref = CB.oModal.find(vth.attr('href'));

					if (kHref.hasClass('hidden')) {
						kHref.removeClass('hidden');
						vth.children('i').removeClass('fa-angle-right').addClass('fa-angle-down');
						vth.parent().parent().css('font-weight', 'bold');
					} else {
						kHref.addClass('hidden');
						vth.children('i').removeClass('fa-angle-down').addClass('fa-angle-right');
						vth.parent().parent().css('font-weight', 'normal');
					}
				});
				let vTotal = calculaValorAcumuladoPorIdloteconsDuplicado();
				console.log(`Valor Total Acumulado: ${vTotal}`);
			},
			aoFechar: function(data) {
				$("#htmlmodal_" + inid+"_duplicados").append(`
					<div id="valaorformclone${inid}_duplicados" style="display:none;">
						<div class="row">
							${data.corpo}
						</div>
					</div>
				`);
			}
		});

	}

	function calculaValorAcumuladoPorIdloteconsDuplicado(idlotecons = 0, $objPai = null, lvl = 0) 
	{
		let $objFilho = ($objPai === null) ? $(`div[lvl='${lvl}']`) : $objPai.siblings(`#collapse-vallote-duplicado-${idlotecons}`).find(`div[lvl='${lvl}']`);
		var vTotal = 0;
		var vTotalUn = 0;

		$objFilho.each(function(i, o) {
			let $o = $(o);

			if ($o.children('[vallote-duplicado]').length > 0) {
				vTotal += parseFloat($o.children('[vallote-duplicado]').attr('vallote-duplicado')) || 0;
			} else if ($o.children('[idlotecons-valloteacumulado-duplicado]').length > 0) {
				let idlotecons = $o.children('[idlotecons-valloteacumulado-duplicado]').attr('idlotecons-valloteacumulado-duplicado');
				vTotal += calculaValorAcumuladoPorIdloteconsDuplicado(idlotecons, $o, lvl + 1);
			}

			if ($o.children('[valloteun-duplicado]').length > 0) {
				vTotalUn += parseFloat($o.children('[valloteun-duplicado]').attr('valloteun-duplicado')) || 0;
			} else if ($o.children('[idlotecons-valloteunacumulado-duplicado]').length > 0) {
				let idlotecons = $o.children('[idlotecons-valloteunacumulado-duplicado]').attr('idlotecons-valloteunacumulado-duplicado');
				vTotalUn += calculaValorAcumuladoPorIdloteconsDuplicado(idlotecons, $o, lvl + 1);
			}
		});

		if ($objPai != null) 
		{
			atributoValorProdserv = $objPai.children('[idlotecons-valloteacumulado-duplicado]').attr('value');
			atributoQtdProdserv = $objPai.find(`.qtdun-duplicado-${idlotecons}`).attr('value');
			$objPai.children('[idlotecons-valloteacumulado-duplicado]').html(`
				<span style="float:right" title=" R$ ${vTotal}">
					${new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 4 }).format(vTotal)}
				</span>
			`);
			$objPai.children('[idlotecons-valloteacumulado-duplicado]').attr('vallote-duplicado', vTotal);
			$(`.idlotecons-valloteacumulado-duplicado-table-${atributoValorProdserv}`).html(`<b> R$ ${new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 4 }).format(vTotal)}</b>`);
			
			valorUnitario = vTotal / atributoQtdProdserv;
			$objPai.children('[idlotecons-valloteunacumulado-duplicado]').html(`
				<span style="float:right" title=" R$ ${valorUnitario}">
					${new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 4 }).format(valorUnitario)}
				</span>
			`);
			$objPai.children('[idlotecons-valloteunacumulado-duplicado]').attr('valloteun-duplicado', valorUnitario);
			$(`.idlotecons-valloteunacumulado-duplicado-table-${atributoValorProdserv}`).html(`<b> R$ ${new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 4 }).format(vTotalUn)}</b>`);
			
		}

		return vTotal;
	}

	function tableToExcel(idprodservformula,  tipo = null) {
		var table = (tipo == 'clone') ? `_tableresultClone${idprodservformula}` : `_tableresult${idprodservformula}`;
        var name = `arvore-insumos-${vIdProdServ}${new Date().toLocaleDateString().replaceAll('/','-')}`;

        var uri = 'data:application/vnd.ms-excel;base64,'
        , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
        , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
        , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }

        if (!table.nodeType) table = document.getElementById(table)
        var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
        var a = document.createElement('a');
        a.href = uri + base64(format(template, ctx));
        a.download = name;
        a.click();
    }

	//------- Funções Módulo -------

	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape_js
</script>