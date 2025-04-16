<script>
	//------- Injeção PHP no Jquery -------
	var arraygraf = <?=json_encode($arrgraf)?> || "";
	var arraygrafc = <?=json_encode($arrgrafc)?> || "";
	var onLoadGraph = '<?=$onLoadGraph?>';
	var idunidadeest = '<?=$_1_u_prodserv_idunidadeest?>';
	var estmin = '<?=$estmin?>';
	
	//------- Injeção PHP no Jquery -------

	//------- Variáveis Globais -------
	$("#lotetodosmodal").click(function() {
		var idprodserv = $(this).attr('validprodserv');

		CB.modal({
			url: `?_modulo=lotetodos&_acao=u&idprodserv=${idprodserv}`,
			header: "Todos os Lotes"
		});
	});

	$("#lotetodosforumulamodal").click(function() {
		var idprodserv = $(this).attr('validprodserv');

		var idprodservformula = $(this).attr('validprodservformula');
		CB.modal({
			url: `?_modulo=lotetodos&_acao=u&idprodserv=${idprodserv}&idprodservformula=${idprodservformula}`,
			header: "Todos os Lotes"
		});
	});

	$(document).ready(function() {
		gerargraficoun(onLoadGraph);
		gerargraficocompra();
		idunidade_navtab = $(`#idunidade_navtab_${idunidadeest}`);
		if (idunidade_navtab != 'undefined') {
			$(`#idunidade_navtab_${idunidadeest}`).remove();
			$('ul.nav-tabs').prepend(idunidade_navtab);
			$(`#idunidade_navtab_${idunidadeest} a`).trigger('click');
		}
	});

	$("#modalfornecedor").click(function() {
		var idprodserv = $("[name=_1_u_prodserv_idprodserv]").val();
		var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';

		CB.modal({
			url: `?_modulo=prodservfornecedor&_acao=u&idprodserv=${idprodserv}${idempresa}`,
			header: "Fornecedor"
		});
	});
	//------- Variáveis Globais -------

	//------- Exececuções para Carregar o módulo ----------
	function carregarCalculo(inid, idprodserv, temformula) 
	{
		var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';
		$("#corpocal").html('<div id="cbCarregando"></div>');

		if (temformula = 'Y') {
			var str = `idprodserv=${idprodserv}&idprodservformula=${inid}&_menu=N${idempresa}`;
		} else {
			var str = `idprodserv=${idprodserv}&_menu=N${idempresa}`;
		}

		CB.go(str);
	}

	// GVT - 10/04/2020
	function calculaestoqueminimo(vthis, campo, mediadiaria, tabela)  // função calcula o estoque mínimo automático
	{
		var flag = 1;
		switch (campo) {
			case 'temporeposicao':
				if (vthis.value == undefined || vthis.value == "") {
					var valor = 30;
				} else {
					var valor = vthis.value;
				}
				if ($("[name*=_" + tabela + "_estoqueseguranca]").val() == undefined || $("[name*=_" + tabela + "_estoqueseguranca]").val() == "") {
					var valestoqueseguranca = 0;
				} else {
					var valestoqueseguranca = $("[name*=_" + tabela + "_estoqueseguranca]").val();
				}
				//var estoqueminimo = Math.round((mediadiaria * valor) + (mediadiaria * valestoqueseguranca));
				var estoqueminimo = ((mediadiaria * valor) + (mediadiaria * valestoqueseguranca)).toFixed(2);
				CB.post({
					objetos: "_temporeposicao_u_" + tabela + "_id" + tabela + "=" + $(":input[name=_1_" + CB.acao + "_" + tabela + "_id" + tabela + "]").val() + "&_temporeposicao_u_" + tabela + "_estminautomatico=" + estoqueminimo + "&_temporeposicao_u_" + tabela + "_estoqueseguranca=" + valestoqueseguranca + "&_temporeposicao_u_" + tabela + "_temporeposicao=" + valor,
					refresh: true,
					parcial: true
				});
				break;
			case 'estoqueseguranca':
				if (vthis.value == undefined || vthis.value == "") {
					var valor = 0;
				} else {
					var valor = vthis.value;
				}
				if ($("[name*=_" + tabela + "_temporeposicao]").val() == undefined || $("[name*=_" + tabela + "_temporeposicao]").val() == "") {
					var valtemporeposicao = 30;
				} else {
					var valtemporeposicao = $("[name*=_" + tabela + "_temporeposicao]").val();
				}

				//var estoqueminimo = Math.round((mediadiaria * valtemporeposicao) + (mediadiaria * valor));
				var estoqueminimo = ((mediadiaria * valtemporeposicao) + (mediadiaria * valor)).toFixed(2);

				CB.post({
					objetos: "_estoqueseguranca_u_" + tabela + "_id" + tabela + "=" + $(":input[name=_1_" + CB.acao + "_" + tabela + "_id" + tabela + "]").val() + "&_estoqueseguranca_u_" + tabela + "_estminautomatico=" + estoqueminimo + "&_estoqueseguranca_u_" + tabela + "_estoqueseguranca=" + valor + "&_estoqueseguranca_u_" + tabela + "_temporeposicao=" + valtemporeposicao,
					refresh: true,
					parcial: true
				});
				break;
			default:
				flag = 0;
				alert("Impossível realizar calculo de estoque mínimo automático");
				console.warning("Parâmetros para a função calculaestoqueminimo inválidos");
				break;
		}

		if (flag) {
			$("#prodserv_estminautomatico").val(estoqueminimo);
			$("#div_prodserv_estminautomatico").html(estoqueminimo);
			calculapedidoautomatico(this, mediadiaria, tabela);
		}
	}

	function calculapedidoautomatico(vthis, mediadiaria, tabela) 
	{
		var temporeposicao = $("[name*=_" + tabela + "_temporeposicao]").val();
		var minimoauto = $("#" + tabela + "_estminautomatico").val();
		var qtdest = $("#" + tabela + "_qtdest").val();
		var qtdpa = $("#" + tabela + "_qtdpa").val();
		var tempocompra = $("[name*=_" + tabela + "_tempocompra]").val();
		var pedidoautomatico = (mediadiaria * tempocompra).toFixed(2);
		var pedido_automatico = ((mediadiaria * temporeposicao) + (minimoauto - qtdest) - qtdpa).toFixed(2);

		//Alteração/Atualização do "Sugestão Compra" em tela ao modificar o "Tempo de compra"
		$('#sugestaocompra').text(((parseFloat(mediadiaria) * parseFloat(tempocompra)) + (parseFloat(estmin) - parseFloat(qtdest))).toFixed(2));

		if (pedido_automatico < 0) {
			pedido_automatico = 0;
		}

		var valorId = $(":input[name=_1_"+CB.acao+"_"+tabela+"_id"+tabela+"]").val();
		CB.post({
			objetos: `_pedidoautomatico_u_${tabela}_id${tabela}=${valorId}&_pedidoautomatico_u_${tabela}_pedidoautomatico=${pedidoautomatico}&_pedidoautomatico_u_${tabela}_pedido_automatico=${pedido_automatico}&_pedidoautomatico_u_${tabela}_tempocompra=${tempocompra}`,
			refresh: true,
			parcial: true
		});

		$("#prodserv_pedidoautomatico").val(pedidoautomatico);
		vpedidoautomatico = pedidoautomatico;
		if (pedido_automatico > pedidoautomatico) {
			vpedidoautomatico = pedido_automatico;
		}

		$("#div_prodserv_pedidoautomatico").html(vpedidoautomatico);
		$("#pa1").html("PA 1: " + pedidoautomatico);
		$("#pa2").html("PA 2: " + pedido_automatico);
	}

	function verloteunidade(vthis, inidunidade) 
	{
		$('.loteunidade').addClass("hidden");
		$('#unidade' + inidunidade).removeClass("hidden");
		gerargraficoun(inidunidade)
	}

	function showhistoricolote(idlote = null) 
	{
		if (idlote) {
			CB.modal({
				titulo: "</strong>Histórico do Lote</strong>",
				corpo: $("#consumolote_" + idlote).html(),
				classe: 'sessenta',
			});
		} else {
			alertAtencao("Identificador do lote vazio", "Erro de Lote");
		}
	}

	function alteravunpadrao(idlote, vunpadrao) 
	{
		CB.post({
			objetos: "_x_u_lote_idlote=" + idlote + "&_x_u_lote_vunpadrao=" + vunpadrao,
			parcial: true
		});
	}

	function gerargraficoun(idunidade) 
	{
		$('#chartdiv').html('');
		var arraygraf_final = arraygraf[idunidade];

		var chart = AmCharts.makeChart("chartdiv", {
			"type": "serial",
			"theme": "none",
			"legend": {
				"useGraphSettings": true
			},
			"dataProvider": arraygraf_final,
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
				"title": "Debito",
				"valueField": "debito",
				"fillAlphas": 0
			}, {
				"valueAxis": "v2",
				"lineColor": "#45B823",
				"bullet": "round",
				"bulletBorderThickness": 1,
				"hideBulletsCount": 30,
				"title": "Credito",
				"valueField": "credito",
				"fillAlphas": 0
			}, {
				"valueAxis": "v3",
				"lineColor": "#DDEA11",
				"bullet": "round",
				"bulletBorderThickness": 1,
				"hideBulletsCount": 30,
				"title": "Estoque",
				"valueField": "estoque",
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

	function gerargraficocompra() 
	{
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

	function Hestmim(tipo, inid, texto) 
	{
		CB.modal({
			titulo: "</strong>Histórico " + texto + "</strong>",
			corpo: $("#" + tipo + inid).html(),
			classe: 'sessenta',
		});
	}
	function planejamento(texto) 
	{
		CB.modal({
			titulo: "</strong>" + texto + "</strong>",
			corpo: $("#planejamento").html(),
			classe: 'sessenta',
		});
	}

	function alteravalor(campo, valor, tabela, inid, texto) 
	{
		htmlTrModelo = $("#alt" + campo + inid).html();

		htmlTrModelo = htmlTrModelo.replace("#namerotulo", texto);
		htmlTrModelo = htmlTrModelo.replace("#name_campo", "_1_u_" + tabela + "_" + campo);
		htmlTrModelo = htmlTrModelo.replace("#name_justificativa", "_1_u_" + tabela + "_justificativa");

		if (campo == 'estmin' || campo=='tempoconsrateio') {
			htmlTrModelo = htmlTrModelo.replace("#valor_campo", valor);
			var objfrm = $(htmlTrModelo);
		} else {
			var objfrm = $(htmlTrModelo);
			objfrm.find("#ndroptipo option[value='" + valor + "']").attr("selected", "selected");
		}

		strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='CB.post();'><i class='fa fa-circle'></i>Salvar</button></strong>";

		CB.modal({
			titulo: strCabecalho,
			corpo: "<table>" + objfrm.html() + "</table>",
			classe: 'sessenta',
		});
	}

	function alteraoutros(vthis, tabela) 
	{
		valor = $(vthis).val();
		if (valor == 'OUTROS') {
			$(vthis).parent().append('<input style="margin-top:4px;" id="justificaticaText" name="_1_u_' + tabela + '_justificativa" value="" class="size50" type="text" placeholder="Digite aqui a sua justificativa" />');
		} else {
			$('#justificaticaText').remove();
		}
	}
	//------- Exececuções para Carregar o módulo ----------

	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>