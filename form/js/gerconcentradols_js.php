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

		CB.on('posLoadUrl',function(){
			$(".esconde_abas_div").each(function(i, elemento) {
				var escondeAba = $(`.esconde${$(elemento).attr('div_prodserv')}`);
				if(escondeAba.find('.naoocultar').length == 0)
				{
					$(`.esconde${$(elemento).attr('div_prodserv')}`).hide();
				}
			});
		});
	}
	//------- Funções JS -------

	//------- Funções Módulo -------
	function pesquisar(vthis) 
    {
        $(vthis).html('<span class="fa fa-spinner fa-pulse"></span>');
        var idpessoa = $("[name=idpessoa]").attr("cbvalue");
        var idprodserv = $("[name=idprodserv]").attr("cbvalue");
        var status = $("[name=status]").val();
        var tipo = $("[name=tipo]").val();
		var tipoagente = $("[name=tipoagente]").val();
		var envio = $("[name=envio]").val();

        var str = "idprodserv=" + idprodserv + "&idpessoa=" + idpessoa + "&status=" + status + "&tipo=" + tipo+"&envio="+envio+"&tipoagente="+tipoagente;

        CB.go(str);
    }
	//------- Funções Módulo -------

	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape_1
</script>