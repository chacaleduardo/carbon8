<script>
	//------- Injeção PHP no Jquery -------
	jCli = <?=json_encode($jCli)?> || [];
	jProd = <?=json_encode($jProd)?> || [];
	status = '<?=$status?>';
	//------- Injeção PHP no Jquery -------

	//------- Funções JS -------
	//mapear autocomplete de clientes
	jCli = jQuery.map(jCli, function(o, id) {
		return {"label": o.nome, value:id}
	});	
	//autocomplete de clientes
	$("[name*=idpessoa]").autocomplete({
		source: jCli,
		delay: 0,
		create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
			return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
			};
		}	
	});
	
	//mapear autocomplete de clientes
	jProd = jQuery.map(jProd, function(o, id) {
		return {
			"label": o.descr,
			value: id
		}
	});
	//autocomplete 
	$("[name*=idprodserv]").autocomplete({
		source: jProd,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
			};
		}
	});
	
	$(document).keypress(function(e) {
        if (e.which == 13) {
            pesquisar();
        }
    });

	// copiar o texto no cabecalho
	$(document).ready(function() {
        var arrayIdD = $.map($(".demanda"), function(n, i) {
            return n.id;
        });

        jQuery.each(arrayIdD, function(i, val) {
            var texto = $("#" + val).html();
            $("." + val).html(texto);
        });

        var obs = $("#obsfim").html();
        $("#obsinicio").html(obs);

    });

	if(status == 'FALTA')
	{	
		$(document).ready(function() {
            var arrayOfIds = $.map($(".ocultar"), function(n, i) {
                return n.id;
            });

            jQuery.each(arrayOfIds, function(i, val) {
                $("." + val).hide();
            });
        });
	}

    $(".estoque_demanda").each(function(i, elemento) {
		var parametro = $(elemento).attr('parametro');
		var parametroLote = $(elemento).attr('parametroLote');
		var estoqueValue = $(`#${parametroLote}`).val();
		var demandaValue = $(elemento).val();
		var statusValue = $(`#status_${parametro}`).val();
		var parametroanterior = '';
		const ArrayStatus = ["LIBERADO", "APROVADO"];

		$('.vacinaSuficiente').prop('checked', true);
		
		if(parametroanterior != parametro)
		{
			insuficiente = false;
		}

		if((parseFloat(demandaValue) >= parseFloat(estoqueValue)) || !ArrayStatus.includes(statusValue) || insuficiente == true)
		{
			$(`.estoque${parametro}`).removeClass('suficiente');
			$(`.estoque${parametro}`).addClass('insuficiente');			
			$(`.estoque${parametro}`).hide();
			parametroanterior = parametro;
			insuficiente = true;
			console.log(parametro + ' - insuficiente - Estoque: ' + estoqueValue + ' - Demanda: ' + demandaValue);
		} else if((parseFloat(demandaValue) < parseFloat(estoqueValue)) && ArrayStatus.includes(statusValue) && insuficiente == false) {
			$(`.estoque${parametro}`).addClass('suficiente');
			$(`.estoque${parametro}`).show();	
			parametroanterior = parametro;
			insuficiente = false;
			console.log(parametro + ' - suficiente - Estoque: ' + estoqueValue + ' - Demanda: ' + demandaValue);
		} 
	});

    CB.on('posLoadUrl',function(){
		$(".esconde_abas_div").each(function(i, elemento) {
			var escondeAba = $(`.escondeAba_${$(elemento).attr('div_formalizacao')}`);
			if(escondeAba.find('.insuficiente.suficiente').length > 0){
				$(`.escondeAba_${$(elemento).attr('div_formalizacao')}`).hide();
				$(escondeAba.find('.insuficiente.suficiente')).each(function(i, elemento2) {
					$(elemento2).removeClass('insuficiente suficiente').addClass('insuficiente');
				});
			}
			else if(escondeAba.find('.suficiente').length == 0)
			{
				$(`.escondeAba_${$(elemento).attr('div_formalizacao')}`).hide();
			}
		});

		$(".contarSuficiente").html($('.suficiente').length);
	});

	$('.formalizacao-popover').each((key, element) => {
		$(element).popover({
			html: true,
			trigger: 'hover',
			content: function() {
				return $(`.popover_${$(element).data('formalizacao')}`).html();
			}
		});
	});

	//------- Funções JS -------

	//------- Funções Módulo -------
	function pesquisar(vthis) 
    {
        $(vthis).html('<span class="fa fa-spinner fa-pulse"></span>');
        var idpessoa = $("[name=idpessoa]").attr("cbvalue");
		var idprodserv = $("[name=idprodserv]").attr("cbvalue");
        var str = `&idpessoa=${idpessoa}&idprodserv=${idprodserv}`;
        CB.go(str);
    }

	function mostrarSuficiente(vthis)
	{
		if (vthis.checked) {
			$(".insuficiente").hide();
			$(".contarSuficiente").html($('.suficiente').length);
		} else {
			$(".insuficiente").show();
			$(".contarSuficiente").html($('.suficiente').length + $(".insuficiente").length);
		}
	}
	//------- Funções Módulo -------

	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape_1
</script>