<script>
	//------- Injeção PHP no Jquery -------
	let status = '<?=$_1_u_nf_status?>';
	let idpessoa = <?=$_SESSION["SESSAO"]["IDPESSOA"]?>
	//------- Injeção PHP no Jquery -------

	//------- Funções JS -------
	$(function() {
		$('.caixa').autosize();
	});

	if(status != "ENVIADO" && status != "RESPONDIDO") 
	{
		$("input").not('[name*="nf_idnf"]').prop("disabled", true);
		$("select").prop("disabled", true);
		$("textarea").not('[name*="_nf_infcpl"]').prop("disabled", true);
	}

	$("#parcelas").change(function() {
		if ($("#parcelas").val() > 1) {
			$("#divtab1").css("display", "block");
			$("#divtab2").css("display", "block");
		} else {
			$("#divtab1").css("display", "none");
			$("#divtab2").css("display", "none");
		}
	});

	$(".trnormal").on("click", function() 
	{
		if ($(this).attr("alertaimg") != "") 
		{
			let idprodserv = $(this).attr("alertaimg");
			$(this).removeAttr("alertaimg");
			if ($(".prodserv_" + idprodserv).length > 0)
				alertAtencao("Este item possui anexos!");
		}
	});

	(function() {
		$.each($(".popoverprodserv"), (i, e) => {
			let idprodserv = $(e).attr("idprodserv");
			let popoverContent = $(".prodserv_" + idprodserv).html();
			$(e).webuiPopover({
				title: `Anexos <i  class="fa fa-times fa-lg preto hoververmelho" onclick="(fechapopover(this))"></i>`,
				content: popoverContent
			});
		})
	})();

	$("input[name*='_nfitem_previsaoentrega']").on('apply.daterangepicker', function(ev, picker) {
		$(this).html(picker.startDate.format("DD/MM/YYYY") || "");
		var previsao = picker.startDate.format("DD/MM/YYYY") || "";
		var idnf = $(this).attr("_idnf") || "";
		var idnfitem = $(this).attr("_idnfitem") || "";

		if (previsao != "" && idnf != "" && idnfitem != "") {
			CB.post({
				objetos: "_iX_u_nfitem_idnfitem=" + idnfitem + "&_iX_u_nfitem_previsaoentrega=" + previsao + "&_nX_u_nf_idnf=" + idnf + "&_nX_u_nf_previsaoentrega=" + previsao,
				parcial: true,
				refresh: false
			});
		} else {
			alertAtencao('Não foi possível adicionar previsão de entrega');
			console.warn('verifique o valor dos parâmetros da função');
		}
	});

	if ($("[name=_1_u_nf_idnf]").val()) {
		$(".cbupload").dropzone({
			idObjeto: $("[name=_1_u_nf_idnf]").val(),
			tipoObjeto: 'cotacaoforn',
			tipoArquivo: 'ANEXO',
			idPessoaLogada: idpessoa
		});
	}

	//------- Funções JS -------
	
	//------- Funções Módulo -------
	function salvacampo(vthis, tab, inid, campo) 
	{
		CB.post({
			objetos: "_1_u_" + tab + "_id" + tab + "=" + inid + "&_1_u_" + tab + "_" + campo + "=" + $(vthis).val(),
			parcial: true,
			refresh: false
		});
	}

	function salvacampor(vthis, tab, inid, campo) 
	{
		CB.post({
			objetos: "_1_u_" + tab + "_id" + tab + "=" + inid + "&_1_u_" + tab + "_" + campo + "=" + $(vthis).val(),
			parcial: true,
			reload: true
		});
	}

	function salvacampoext(vthis, tab, inid, campo, inmoeda) 
	{
		CB.post({
			objetos: "_1_u_" + tab + "_id" + tab + "=" + inid + "&_1_u_" + tab + "_" + campo + "=" + $(vthis).val() + "&_1_u_" + tab + "_moedaext=" + inmoeda,
			parcial: true,
			reload: true
		});
	}

	function atualizaparc(inidnf) 
	{
		parcelas = $('.parcelas').val();
		diasentrada = $('.diasentrada').val();
		intervalo = $('.intervalo').val();
		CB.post({
			objetos: `_parc_u_nf_idnf=${inidnf}&_parc_u_nf_parcelas=${parcelas}&_parc_u_nf_diasentrada=${diasentrada}&_parc_u_nf_intervalo=${intervalo}`,
			parcial: true,
			refresh: false,
			posPost: function(data, textStatus, jqXHR) {
				atualizafpgto();
			}
		})
	}

	function atualizafpgto() 
	{
		if ($('#parcelas').val() > 1) {
			$('#divtab1').removeClass('hide');
			$('#divtab2').removeClass('hide');
		} else {
			$('#divtab1').addClass('hide');
			$('#divtab2').addClass('hide');
		}
	}

	function fechapopover(vthis) 
	{
		var divs = $(vthis).parents();
		$(divs['2']).removeClass('in').addClass('out').css('display', 'none')
	}

	function atualizafrete(vthis, inidnf) 
	{
		CB.post({
			objetos: "_atfrete_u_nf_idnf=" + inidnf + "&_atfrete_u_nf_frete=" + $(vthis).val(),
			parcial: true
		})
	}

	//Alertar o Fonecedor para Clicar no botão a fim de alterar o status da Cotação
	if (status == 'ENVIADO') 
	{
		window.addEventListener("beforeunload", function(event) {
			alertAtencao('Para confirmar o envio da Cotação é necessário clicar no botão: "Enviar Cotação".', "", 4000);
			event.preventDefault();
			event.returnValue = 'Para confirmar o envio da Cotação é necessário clicar no botão: "Enviar Cotação".';
			return '';
		});
	}
	//------- Funções Módulo -------
</script>