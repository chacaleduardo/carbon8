<script>
	//------- Injeção PHP no Jquery -------
	jpag = <?=json_encode($arrayFormaPagamento)?>  || 0;
	//------- Injeção PHP no Jquery -------

	//------- Funções JS -------
	//autocomplete de Pagamentos
	jpag = jQuery.map(jpag, function(o, id) {
		return {"label": o.descricao, value:id}
	});
      
	$("#forma_pag").autocomplete({
        source: jpag,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
            };
        }
    });

    $(".observacao").webuiPopover({
		trigger: "hover",
		placement: "right",
        width: 250,
		delay: {
			show: 300,
			hide: 0
		}
	});
	//------- Funções JS -------
    function mostraInputFormapagamento(vthis){
        $(vthis).hide();
        $(vthis).siblings("label").hide()
        $(vthis).siblings("#forma_pag").removeAttr('disabled').css("display","block");
    }
	//------- Funções Módulo -------
	function altcheck(vtab, vcampo, vid, vcheck) 
    {
        CB.post({
            objetos: "_x_u_" + vtab + "_id" + vtab + "=" + vid + "&_x_u_" + vtab + "_" + vcampo + "=" + vcheck,
            parcial: true
        });
    }

	function atualizaproporcao(vthis, vidnfconfpagar) 
    {
        var valor = 0;
        $(":input[name*=nfconfpagar_proporcao]").each(function() {
            var string1 = $(this).val();
            var numero = parseFloat(string1.replace(',', '.'));
            valor = valor + numero;
        });

        if (valor > 100) {
            alert("A soma das proporções não deve passar de 100.");
            $(vthis).val('');
        } else {
            CB.post({
                objetos: "_pr_u_nfconfpagar_idnfconfpagar=" + vidnfconfpagar + "&_pr_u_nfconfpagar_proporcao=" + $(vthis).val(),
                parcial: true
            })
        }
    }

	function atualizaparc(vthis) 
    {
        CB.post({
            objetos: "_parc_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_parc_u_nf_parcelas=" + $(vthis).val(),
            parcial: true
        })
    }

    function nfconfpagar(inidnfconfpagar, li) 
    {
        var strCabecalho =
        $("#cbModalTitulo").html((strCabecalho));
        var htmloriginal = $("#" + li + "_editarnfconfpagar").html();
        var objfrm = $(htmloriginal);

        objfrm.find("#" + li + "_nfconfpagar_idnfconfpagar").attr("name", "_999_u_nfconfpagar_idnfconfpagar");
        objfrm.find("#" + li + "_nfconfpagar_obs").attr("name", "_999_u_nfconfpagar_obs");

        CB.modal({
            titulo: "</strong>Observações para pagamento <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='salvanfconfpagar();'><i class='fa fa-circle'></i>Salvar</button></strong>",
            corpo: objfrm.html(),
            classe: 'cinquenta'
        });
    }

    function salvanfconfpagar() 
    {
        var strcbpost = "_999_u_nfconfpagar_idnfconfpagar=" + $("[name=_999_u_nfconfpagar_idnfconfpagar]").val() + "&_999_u_nfconfpagar_obs=" + $("[name=_999_u_nfconfpagar_obs]").val();

        console.log(strcbpost);
        CB.post({
            objetos: strcbpost,
            parcial: true,
            msgSalvo: "Salvo",
            posPost: function(resp, status, ajax) 
            {
                if (status = "success") {
                    $("#cbModalCorpo").html("");
                    $('#cbModal').modal('hide');
                } else {
                    alert(resp);
                }
            }
        });
    }

    function multiplicarnf(vidnf) 
    {
        CB.post({
            objetos: `_x_u_nf_idnf=` + $("[name=_1_u_nf_idnf]").val() + `&qtdvezes=` + $("[name=qtdvezes]").val() + `&intervalo=` + $("[name=intervalo]").val() + `&tipointervalo=` + $("[name=tipointervalo]").val() + `&multiplicar=Y`,
            parcial: true
        })
    }
	//------- Funções Módulo -------
	
	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>