<script>
	//------- Injeção PHP no Jquery -------
	//------- Injeção PHP no Jquery -------

	//------- Funções JS -------
	$(document).ready(function(){
		$("#inputFiltro").on("keyup", function() {
			var value = $(this).val().toLowerCase().normalize("NFD").replace(/[^a-zA-Zs]/, "");            
			seletorUnidade(value);
			seletorPessoa(value);
		});
		$("#inputFiltroempresa").on("keyup", function() {
			var value = $(this).val().toLowerCase().normalize("NFD").replace(/[^a-zA-Zs]/, "");            
			seletorEmpresa(value);
		});
	});
	//------- Funções JS -------

	//------- Funções Módulo -------
	function seletorEmpresa(value)
	{
		$(".empresa").filter(function() {
			let seletor = $(this).attr("data-text");
			if(!seletor){
				seletor = $(this).text();
			}
			$(this).toggle(seletor.toLowerCase().normalize("NFD").replace(/[^a-zA-Zs]/, "").indexOf(value) > -1);
		});
	}
	function seletorUnidade(value)
	{
		$(".unidade").filter(function() {
			let seletor = $(this).attr("data-text");
			if(!seletor){
				seletor = $(this).text();
			}
			$(this).toggle(seletor.toLowerCase().normalize("NFD").replace(/[^a-zA-Zs]/, "").indexOf(value) > -1);
		});
	}
	function seletorPessoa(value)
	{
		$(".pessoa").filter(function() {
			let seletor = $(this).attr("data-text");
			if(!seletor){
				seletor = $(this).text();
			}
			$(this).toggle(seletor.toLowerCase().normalize("NFD").replace(/[^a-zA-Zs]/, "").indexOf(value) > -1);
		});
	}


	$(document).ready(function(){
		$("#inputFiltro2").on("keyup", function() {
			var value = $(this).val().toLowerCase().normalize("NFD").replace(/[^a-zA-Zs]/, "");            
			seletorRateio(value);
		});
	});

	
	function seletorRateio(value)
	{
		$(".itemrateio").filter(function() {
			let seletor = $(this).attr("data-text");
			if(!seletor){
				seletor = $(this).text();
			}
			$(this).toggle(seletor.toLowerCase().normalize("NFD").replace(/[^a-zA-Zs]/, "").indexOf(value) > -1);
		});
	}


	function showhistoricolote(idlotecons = null)
	{
		if (idlotecons) {
			CB.modal({
				titulo: "</strong>Histórico do consumo</strong>",
				corpo: $("#consumolote_" + idlotecons).html(),
				classe: 'sessenta',
			});
		} else {
			alertAtencao("Identificador do lote vazio", "Erro de Lote");
		}
	}

	function showhistoricoitem(idnfitem = null) 
	{
		$.ajax({
			type: "get",
			url: "ajax/rateioinfo.php",
			data: {
				idnfitem: idnfitem
			},
			success: function(data) { // retorno 200 do servidor apache
				CB.modal({
					titulo: "</strong>Outras Informações</strong>",
					corpo: data,
					classe: 'sessenta'
				});
			},
			error: function(objxml) { // nao retornou com sucesso do apache
				alert('Erro: ' + objxml.status);
			}
		}) //$.ajax
	}

	function alterartodos(porempresa) 
	{
		var vqtd = $('#formulario').find("input:checkbox:checked").length;
		if (vqtd > 0) 
		{
			var nobjrat = $('#formulario').find("input:checkbox:not(:checked)");
			nobjrat.each(function(index, value) {
				$(this).parent().parent().remove();
			});

			var inputdest = $('#formulario').find('input.rateioitem').serialize();
			var objvalor = $('#formulario').find('input.valorrateio');
			var strvalor = '';
			var valor = 0;
			objvalor.each(function(index, value) { 
				if (this.value == '') {
					//remove o <tr>
					$(this).parent().parent().remove();

				} else {
					strvalor = strvalor + "&" + $(this).attr('name') + "=" + $(this).val().replace(',', '.');
					valor = valor + parseFloat(this.value.replace(',', '.'));
				}
			});

			valor = Math.round(valor * 100) / 100
			var objdest = $('#formulario').find('input.idunidade');
			var strdest = '';
			objdest.each(function(index, value) {
				strdest = strdest + "&" + $(this).attr('name') + "=" + $(this).val();
			});

			var objdestU = $('#formulario').find('input.idpessoa');
			objdestU.each(function(index, value) {
				strdest = strdest + "&" + $(this).attr('name') + "=" + $(this).val();
			});

			var objdestU = $('#formulario').find('input.idempresa');
			objdestU.each(function(index, value) {
				strdest = strdest + "&" + $(this).attr('name') + "=" + $(this).val();
			});


			var strinputfim = inputdest + strdest + strvalor + "&telarateiotodos=Y&porempresa="+porempresa;

			//alert(strinputfim);
			
			if (valor == 100) {
			
				CB.post({
					objetos: strinputfim,
					parcial: true
				});
			} else {
				alert('O valor total da(s) porcentagen(s) deve ser 100%');
			}
			
		} else {
			alert('É necessário selecionar os itens que deseja alterar o rateio');
		}
	}

	function calcular(vthis) 
	{
		var objvalor = $('#formulario').find('input.valorrateio');
		var valor = 0;
		objvalor.each(function(index, value) {

			if (this.value != '') {

				valor = valor + parseFloat(this.value.replace(',', '.'));
			}
		});
		valor = Math.round(valor * 100) / 100
		if (valor > 100) {
			alert('Soma dos valores não pode ultrapassar 100%');
			$(vthis).val('');
			$('#cbalterar').addClass('hidden');
			$('#cbalterar2').removeClass('hidden');
		} else if (valor < 100) {
			$('#cbalterar').addClass('hidden');
			$('#cbalterar2').removeClass('hidden');
		} else if (valor == 100) {
			$('#cbalterar').removeClass('hidden');
			$('#cbalterar2').addClass('hidden');
		}
		$('#totalvalor').html(valor + "%");
	}

	function mostrarpessoa(vthis) 
	{
		$("#inputFiltro").val("");
		seletorPessoa("");

		$('.fa-building').removeClass('verde');
		if ($(vthis).is(".verde")) {
			$(vthis).removeClass('verde');
			$('.unidade').removeClass('hide');
			$('.pessoa').addClass('hide');
		} else {
			$(vthis).addClass('verde');
			$('.unidade').addClass('hide');
			$('.pessoa').removeClass('hide');
		}

		var objvalor = $('#formulario').find('input.fracaofunc');
		var valor = 0;
		var quantf = 0;
		var td = '';
		objvalor.each(function(index, value) {
			quantf = quantf + parseFloat($(this).attr('quant'));
			if (this.value != '') {
				td = this.parentElement;

				$(td).find('input.valorrateio').val('');
			}
		});
		valor = Math.round(valor * 100) / 100
		if (valor > 100) {
			alert('Soma dos valores não pode ultrapassar 100%');
			$('#cbalterar').addClass('hidden');
			$('#cbalterar2').removeClass('hidden');
		} else if (valor < 100) {
			$('#cbalterar').addClass('hidden');
			$('#cbalterar2').removeClass('hidden');
		} else if (valor == 100) {
			$('#cbalterar').removeClass('hidden');
			$('#cbalterar2').addClass('hidden');
		}
		valor = Math.round(valor * 100) / 100
		$('#totalvalor').html(valor + "%");
	}

	function calculafunc(vthis) 
	{
		$('.fa-group').removeClass('verde');
		$('.unidade').removeClass('hide');

		$("#inputFiltro").val("");

		if ($(vthis).is(".verde")) 
		{
			$(vthis).removeClass('verde');
			var objvalor = $('#formulario').find('input.fracaofunc');
			var valor = 0;
			var quantf = 0;
			var td = '';
			objvalor.each(function(index, value) {
				quantf = quantf + parseFloat($(this).attr('quant'));
				if (this.value != '') {
					td = this.parentElement;

					$(td).find('input.valorrateio').val('');
				}
			});
			valor = Math.round(valor * 100) / 100
			if (valor > 100) {
				alert('Soma dos valores não pode ultrapassar 100%');
				$('#cbalterar').addClass('hidden');
				$('#cbalterar2').removeClass('hidden');
			} else if (valor < 100) {
				$('#cbalterar').addClass('hidden');
				$('#cbalterar2').removeClass('hidden');
			} else if (valor == 100) {
				$('#cbalterar').removeClass('hidden');
				$('#cbalterar2').addClass('hidden');
			}
			valor = Math.round(valor * 100) / 100
			$('#totalvalor').html(valor + "%");

		} else {
			$(vthis).addClass('verde');
			var objvalor = $('#formulario').find('input.fracaofunc');
			var valor = 0;
			var quantf = 0;
			var td = '';
			objvalor.each(function(index, value) {
				quantf = quantf + parseFloat($(this).attr('quant'));
				if (this.value != '') {
					td = this.parentElement;
					valor = valor + parseFloat(this.value.replace(',', '.'));
					porc = Math.round(this.value.replace(',', '.') * 100) / 100
					$(td).find('input.valorrateio').val(porc)
				}
			});
			valor = Math.round(valor * 100) / 100
			if (valor > 100) {
				alert('Soma dos valores não pode ultrapassar 100%');
				$('#cbalterar').addClass('hidden');
				$('#cbalterar2').removeClass('hidden');
			} else if (valor < 100) {
				$('#cbalterar').addClass('hidden');
				$('#cbalterar2').removeClass('hidden');
			} else if (valor == 100) {
				$('#cbalterar').removeClass('hidden');
				$('#cbalterar2').addClass('hidden');
			}
			valor = Math.round(valor * 100) / 100
			$('#totalvalor').html(valor + "%");
		}
	}

	function selecionar(vthis, idtipoprodserv) 
	{
		var itens = $("." + idtipoprodserv).parent().find("input:checkbox.changeacao");
		itens.each((k, v) => {
			if ($(vthis).prop('checked') == true) {
				$(v).prop('checked', true);
			} else {
				$(v).prop('checked', false);
			}
		});
	}

	function carregasub(idrateioitem, idrateioitemdest) 
	{
		$.ajax({
			type: "get",
			url: "ajax/rateioinfo.php",
			data: {
				idrateioitem: idrateioitem,
				idrateioitemdest: idrateioitemdest
			},
			success: function(data) { // retorno 200 do servidor apache
				$("#col" + idrateioitem).html(data);
			},
			error: function(objxml) { // nao retornou com sucesso do apache
				alert('Erro: ' + objxml.status);
			}
		}) //$.ajax
	}

	function restaurartodos(){

		if (confirm("Deseja Limpar os Registros Selecionados?")) {

			var vqtd = $('#formulario').find("input:checkbox:checked").not('[name*="marcardesmarcar"]').length;
			if (vqtd > 0) {
				var nobjrat = $('#formulario').find("input:checkbox:not(:checked)");

				nobjrat.each(function(index, value) {
					$(this).parent().parent().remove();
				});

				var inputdest = $('#formulario').find('input.rateioitem').serialize();

				

				var strinputfim = inputdest + "&restaurartodos=Y";
				/*
					if (window.frameElement) {
						$(window.parent.document).find("#_inputmodalrateiosemmodificacao_").attr('mod', 'Y');
					}
				*/
					CB.post({
						objetos: strinputfim,
						parcial: true
					});
				
			} else {
				alert('É necessário selecionar os itens que deseja limpar o rateio');
			}
		}
	}

	function restaurartodosdest(){

		if (confirm("Deseja Limpar os Registros Selecionados?")) {

			var vqtd = $('#formulario').find("input:checkbox:checked").not('[name*="marcardesmarcar"]').length;
			if (vqtd > 0) {
				var nobjrat = $('#formulario').find("input:checkbox:not(:checked)");

				nobjrat.each(function(index, value) {
					$(this).parent().parent().remove();
				});

				var inputdest = $('#formulario').find('input.rateioitem').serialize();

				

				var strinputfim = inputdest + "&restaurartodosdest=Y";
				/*
					if (window.frameElement) {
						$(window.parent.document).find("#_inputmodalrateiosemmodificacao_").attr('mod', 'Y');
					}
					*/
					CB.post({
						objetos: strinputfim,
						parcial: true
					});
				
			} else {
				alert('É necessário selecionar os itens que deseja limpar o rateio');
			}
		}
}

function editardestnf(idrateioitemdest,item,rateio) 
    {
        htmlTrModelo = "";
		htmlTrModelo = $('#destnf'+idrateioitemdest).html();

       
         var objfrm = $(htmlTrModelo);
         

        strCabecalho = "</strong>"+item+" - ("+rateio+")</strong>";
    
        CB.modal({
            titulo: strCabecalho,
            corpo: "<table class='table table-striped planilha'>" + objfrm.html() + "</table>",
            classe: 'sessenta'
        });
    }

function excluirdestnf(stridrateioitemdestnf){
	if(confirm("Deseja realmente excluir a cobrança destes?")){
		var strimput='';
		var strimput2='';

		var idrateioitemdestnf = stridrateioitemdestnf.split(',');
		
		
		for(var i = 0; i < idrateioitemdestnf.length; i++) {
			var l=i+1;
			// Trim the excess whitespace.
			idrateioitemdestnf[i] = idrateioitemdestnf[i].replace(/^\s*/, "").replace(/\s*$/, "");
			

			const string = idrateioitemdestnf[i];

			const inidrateioitemdestnf = string.split('@');

			validrateioitemdestnf = inidrateioitemdestnf[0];		
			
			validrateioitemdest  =  inidrateioitemdestnf[1];
		
			strimput=strimput+"&_"+l+"_d_rateioitemdestnf_idrateioitemdestnf="+validrateioitemdestnf;

			strimput2=strimput2+"&_"+l+"y_u_rateioitemdest_idrateioitemdest="+validrateioitemdest+"&_"+l+"y_u_rateioitemdest_status=PENDENTE";
		
		}
		//alert(strimput+strimput2);

		CB.post({
			objetos: strimput ,
			parcial: true,
			posPost: function(resp, status, ajax) {
				if (status = "success") {
					debugger										
					$("#cbModalCorpo").html("");
					$('#cbModal').modal('hide');				
				} else {
					alert(resp);
				}
			}      
		})
	
	}
}

	//------- Funções Módulo -------

	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>