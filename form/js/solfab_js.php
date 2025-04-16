
<script>
    //------- Injeção PHP no Jquery -------
	var vIdSolfab = '<?=$arrSFAssociado["idsolfab"] ?>';
    // autocomplete cliente
	JarrLoteSF = <?= $JarrLoteSF ?? [] ?> || [];
    jAdj = <?=$jAdj?> || [];
    //------- Injeção PHP no Jquery -------

    //------- Funções JS -------
	if ($("input.dragsf[type='checkbox']").length == 0) {
		$("#incluirTodasSementes").hide();
		$("#btnIncluirSementes").hide();
	}

	$("#incluirTodasSementes").click(function() {
		let checked = this.checked;
        
		$("input.dragsf[type='checkbox']").each(function(i, o) {
			o.checked = checked;
		})
	})

	$("input.dragsf[type='checkbox']").click(function() {
		let qtdInputCheck = $("input.dragsf[type='checkbox']").length;
		let qtdChecked = $("input.dragsf[type='checkbox']:checked").length;
		var inputCheckAll = document.getElementById("incluirTodasSementes");
		inputCheckAll.checked = (qtdInputCheck == qtdChecked) ? true : false;
	});

	$("#btnIncluirSementes").click(function() {
		let obj = {};

		$("input.dragsf[type='checkbox']:checked").each(function(i, o) {
			obj[`_solfabitem${i}_i_solfabitem_idsolfab`] = vIdSolfab;
			obj[`_solfabitem${i}_i_solfabitem_idobjeto`] = $(o).attr("idlote");
			obj[`_solfabitem${i}_i_solfabitem_tipoobjeto`] = 'lote';
		});

		if (Object.keys(obj).length > 0) {
			CB.post({
				objetos: obj,
				refresh: "refresh"
			});
		} else {
			alertAtencao("Selecione pelo menos uma semente para inclusão");
		}
	});
    //------- Funções JS -------

    //------- Funções Módulo -------
	function excluiSFItem(inIdSFItem) 
    {
		CB.post({
			objetos: "_x_d_solfabitem_idsolfabitem=" + inIdSFItem,
			refresh: "refresh"
		})
	}

	function excluiadj(idsolfabadj) 
    {
		CB.post({
			objetos: "_x_d_solfabadj_idsolfabadj=" + idsolfabadj,
			refresh: "refresh"
		})
	}
	if ($("[name=_1_u_solfab_idsolfab]").val()) 
    {
		$(".cbupload").dropzone({
			idObjeto: $("[name=_1_u_solfab_idsolfab]").val(),
			tipoObjeto: 'solfab',
			idPessoaLogada: '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>'
		});
	}

    //mapear autocomplete de clientes
    jAdj = jQuery.map(jAdj, function(o, id) {
        return {
            "label": o.nome,
            value: id + ""
        }
    });

    //autocomplete de clientes
    $("[name=pessoaadjacente]").autocomplete({
        source: jAdj,
        delay: 0,
        select: function(event, ui) {
            inseriadjacente(ui.item.value);
        },
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);

            };
        }
    });

	function inseriadjacente(inid) {

		CB.post({
			objetos: "_x_i_solfabadj_idsolfab=" + $("[name=_1_u_solfab_idsolfab]").val() + "&_x_i_solfabadj_idpessoa=" + inid,
			parcial: true
		});
	}
    //------- Funções Módulo -------
	//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape100
</script>