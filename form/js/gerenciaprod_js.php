<script>
	//------- Injeção PHP no Jquery -------
	jCli = <?=json_encode($arrCli)?> || [];
	jProd = <?=json_encode($arrProd)?> || [];
    uniqueid = '<?=$uniqueid?>';
    urini = '<?=$urini?>';
    idsnippet = '<?=$idsnippet?>';
	//------- Injeção PHP no Jquery -------

    //------- Funções JS -------
    //mapear autocomplete de clientes
	jCli = jQuery.map(jCli, function(o, id) {
		return {
			"label": o.nome,
			value: id
		}
	});

	//autocomplete de clientes
	$("[name*=idpessoa]").autocomplete({
		source: jCli,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
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
    //------- Funções JS -------

    //------- Funções Módulo -------
    function pesquisar(vthis) 
    {
		var idpessoa = $("[name=idpessoa]").attr("cbvalue");
		var idprodserv = $("[name=idprodserv]").attr("cbvalue");
		var status = $("[name=status]").val();
		var idplantel = $("[name=idplantel]").val();
		var validacao = $("[name=validacao]").val();

		CB.modal({
			url: `?_modulo=gerenciaprodcorpo&idprodserv=${idprodserv}&idpessoa=${idpessoa}&status=${status}&validacao=${validacao}&idplantel=${idplantel}&_modo=form`,
			header: "Gerência de Produto"
		});
	}
    //------- Funções Módulo -------

    //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape_1
</script>