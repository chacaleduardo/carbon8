<script>
	//------- Injeção PHP no Jquery -------
	jsonProd = <?=$JSON->encode($jsonProd)?> || ""; //// autocomplete produto
	//@521713 - APRESENTAR EMPRESA LISTA FORNECEDOR
	//Adicionado join com empresa para trazer a sigla junto com no nome dos fornecedores
	jsonForn = <?=$JSON->encode(ProdservFornecedorController::buscarFornecedorPorSessionIdEmpresaEIdTipoPessoa(5))?> || "";

	var idprodserv = '<?=$_1_u_prodserv_idprodserv?>';
	//------- Injeção PHP no Jquery -------

	//------- Funções JS ------
	//mapear autocomplete de fornecedores
	jsonForn = jQuery.map(jsonForn, function(o, id) {
        return {"label": o.descricao, value:id+""}
    });

	$(".fornecedor").autocomplete({
		source: jsonForn,
		delay: 0,
		select: function(event, ui) {			
			CB.post({
				objetos: `_pf_u_prodservforn_idpessoa=${ui.item.value}&_pf_u_prodservforn_idprodservforn=${$(this).attr('idprodservforn')}`,
				parcial: true
			});
		},
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
            };
        }
	});

	function autoCompleteProduto()
    {
		$('.prodempresa').each(function(index, elemento){
            let idempresa = $(elemento).attr('idempresa');
			let idprodservforn = $(elemento).attr('idprodservforn');
            var itens = jsonProd.filter((obj, index) => obj.idempresa == idempresa);
            $(elemento).autocomplete({
                source: itens,
                delay: 0,
                select: function(event, ui) {
					let obj = Object();
					obj["_x_u_prodservforn_idprodservforn"] = idprodservforn;
					obj["_x_u_prodservforn_idprodservori"] = ui.item.idprodserv;
					CB.post({
						objetos: obj,
						parcial: true
					});
                },
                create: function() {
                    $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                        return $('<li>').append("<a>" + item.descr + "</a>").appendTo(ul);
                    };
                }
            });
        });
	}

	$(".mostrarAlterado").webuiPopover({
        trigger: "hover",
        placement: "left",
        width: 400,
        delay: {
            show: 300,
            hide: 0
        }
    });
	//------- Funções JS ------

	//------- Funções Módulo -------
	function idpessoa(vthis, idprodservforn) 
	{
        let idpessoa = $(vthis).val();
        CB.post({
            objetos: `_pf_u_prodservforn_idpessoa=${idpessoa}&_pf_u_prodservforn_idprodservforn=${idprodservforn}`,
            parcial: true
        })
    }

	function unforn(vthis, idprodservforn) 
	{
        let unforn = $(vthis).val();
        if ($('#ivalconv' + idprodservforn).val() != '') 
		{
            var valconv = $('#ivalconv' + idprodservforn).val();
            var sn = 'Y';
            CB.post({
                objetos: `_pf_u_prodservforn_unforn=${unforn}&_pf_u_prodservforn_converteest=${sn}&_pf_u_prodservforn_valconv=${valconv}&_pf_u_prodservforn_idprodservforn=${idprodservforn}`
            })
        }
    }

	function valconv(vthis, idprodservforn) 
	{
        var iunforn = $('#iunforn' + idprodservforn).val();
        let valconv = $(vthis).val();
        if (valconv != '' && valconv > 0) {
            var nvcheck = 'Y';
        } else {
            var nvcheck = 'N';
            valconv = 1;
        }
        CB.post({
            objetos: `_pf_u_prodservforn_valconv=${valconv}&_pf_u_prodservforn_converteest=${nvcheck}&_pf_u_prodservforn_unforn=${iunforn}&_pf_u_prodservforn_idprodservforn=${idprodservforn}`,
            parcial: true
        })
    }

	function excluirFornecedor(inid, inobj) 
	{
        if (confirm("Deseja Excluir este?")) 
		{
            CB.post({
                objetos: `_x_d_${inobj}_id${inobj}=${inid}`,
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

	function inativaobjeto(inid, inobj) 
	{
        if (confirm("Deseja retirar este?")) 
		{
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

	function habilitarFornecedor(inid, inobj) 
	{
        CB.post({
            objetos: `_x_u_${inobj}_id${inobj}=${inid}&_x_u_${inobj}_status=ATIVO`,
            parcial: true,
            posPost: function() {
                if ($("#cbModal").is(':visible')) {
                    var aux = $("#cbModal").attr('modal');
                    $("#" + aux).click();
                }
            }
        });
    }

	function condforn(vthis, idprodservforn) 
	{
        let condforn = $(vthis).val();
        CB.post({
            objetos: `_pf_u_prodservforn_codforn=${condforn}&_pf_u_prodservforn_idprodservforn=${idprodservforn}`,
            parcial: true
        })
    }

    function cprodforn(vthis, idprodservforn) 
	{
        let cprodforn = $(vthis).val();
        CB.post({
            objetos: `_pf_u_prodservforn_cprodforn=${cprodforn}&_pf_u_prodservforn_idprodservforn=${idprodservforn}`,
            parcial: true
        })
    }


	function converteforn(vthis, idprodservforn) 
	{
        if (!$(vthis).is(':checked')) 
		{
            $('#unforn' + idprodservforn).addClass('hide').removeClass('show');
            $('#ivalconv' + idprodservforn).addClass('hide').removeClass('show').val('');
            $(vthis).attr('vcheck', 'N');
            var sn = 'N';
            var valconv = 1;

            CB.post({
                objetos: `_pf_u_prodservforn_converteest=${sn}&_pf_u_prodservforn_valconv=${valconv}&_pf_u_prodservforn_idprodservforn=${idprodservforn}`,
                parcial: true
            });
        } else {

            $('#unforn' + idprodservforn).removeClass('hide').addClass('show');
            $('#ivalconv' + idprodservforn).removeClass('hide').addClass('show');
            $(vthis).attr('vcheck', 'Y');
        }
    }

	function novoobjeto(inobj, multi) 
	{
        CB.post({
            objetos: `_x_i_prodservforn_idprodserv=${idprodserv}&_x_i_prodservforn_multiempresa=${multi}`,
            parcial: true

        });
    }
	//------- Funções Módulo -------

	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>