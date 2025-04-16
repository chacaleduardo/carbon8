<script>
	let idsolmat = <?= $_1_u_solmat_idsolmat ? $_1_u_solmat_idsolmat : 0; ?>;
	let jprodservtemp = <?= json_encode($jprodservtemp) ?>;
	let jpessoa = <?=json_encode($arrayPessoa) ?>;

	CB.prePost = function() {
		let url = removerParametroGet("idsolmatcp", window.location.href);
		window.history.pushState(null, window.document.title, url);
	}

	function atualizaCampo(comentario) {
		$("#_99_i_modulocom_descricao").val(comentario.value);
	}

	function showModal() {
		$.ajax({
			url: 'ajax/soltagimpressora.php',
			type: 'GET',
			data: {
				idsolmat: $('[name=_1_<?= $_acao ?>_solmat_idsolmat]').val()
			},
		});
	}

	function calculoestoquedomodal(prodserv, prodservformula) {
		var tabela = $(this).attr('tabela');

		if (tabela = 'prodservformula') {
			if (prodservformula == null) {
				prodservformula = "";
			}
			var idprodservformula = prodservformula;
			var idprodserv = prodserv;
			CB.modal({
				url: "?_modulo=calculosestoque&_acao=u&idprodserv=" + idprodserv + "&idprodservformula=" + idprodservformula,
				header: "Cálculos Estoque"
			});

		} else {

			var idprodserv = $("[name=_1_u_prodserv_idprodserv]").val();
			CB.modal({
				url: "?_modulo=calculosestoque&_acao=u&idprodserv=" + idprodserv,
				header: "Cálculos Estoque"
			});
		}
	};

	function excluir(idsolmatitem) {
		if (confirm("Deseja realmente excluir o Material selecionado?")) {

			CB.post({
				"objetos": "_x_d_solmatitem_idsolmatitem=" + idsolmatitem
			});
		}
	}

	function excluirTag(idsolmatitemobj) {
		if (confirm("Deseja realmente excluir a TAG selecionada?")) {

			CB.post({
				"objetos": "_x_d_solmatitemobj_idsolmatitemobj=" + idsolmatitemobj
			});
		}
	}

	function mudarunidade(vthis) {
		CB.post({
			objetos: "_x_<?= $_acao ?>_solmat_idunidade=" + vthis.value + "&_x_<?= $_acao ?>_solmat_idsolmat=" + $("#idsolmat").val()
		});
	}

	function formula(vthis, nsolmat) {
		CB.post({
			objetos: {
				"_p1_u_solmatitem_idsolmatitem": $("[name=_x" + nsolmat + "_u_solmatitem_idsolmatitem]").val(),
				"_p1_u_solmatitem_idprodservformula": $(vthis).val()
			},
			parcial: true
		});
	}

	//autocomplete de jTipoProdServ
	$("#insidprodserv").autocomplete({
		source: jprodservtemp,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				let unidade = (item.un ? ` - ${item.un} - ` : '');
				return $('<li>').append(`<a>${item.descr} ${unidade} <span style='color:#b6b6b6;'>${item.codprodserv}</span></a>`).appendTo(ul);
			};
		},
		select: function(event, ui) {

			CB.post({
				objetos: {
					"_w_i_solmatitem_qtdc": $("#qtdcadastrado").val(),
					"_w_i_solmatitem_descr": ui.item.descr,
					"_w_i_solmatitem_idsolmat": $("#idsolmat").val(),
					"_w_i_solmatitem_idprodserv": ui.item.idprodserv,
					"_w_i_solmatitem_un": ui.item.un
				},
				parcial: true
			});
		}
	});

	jpessoa = jQuery.map(jpessoa, function(o, id) {
        return {"label": o.nome, value: id+""}
    });

	//autocomplete de jTipoProdServ
	$(".insidpessoa").autocomplete({
		source: jpessoa,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				return $('<li>').append(`<a>${item.label}</a>`).appendTo(ul);
			};
		},
		select: function(event, ui) {

			CB.post({
				objetos: {
					"_w_u_solmatitem_idsolmatitem": $(this).attr('id'),
					"_w_u_solmatitem_idpessoa": ui.item.value					
				},
				parcial: true
			});
		}
	});
	/*
	* Duplicar solmat [ctrl]+[d]
	*/
	$(document).keydown(function(event) {

		if (!((event.ctrlKey || event.altKey) && event.keyCode == 68)) return true;

		if (!teclaLiberada(event)) return; //Evitar repetição do comando abaixo

		janelamodal('?_modulo=' + CB.modulo + '&_acao=i&idsolmatcp=' + $("[name='_1_u_solmat_idsolmat']").val());

		return false;
	});
	$('.cbFecharForm').on('change', function() {
		console.log('fechar');
		if ($('#cbModuloForm').html() == '') {
			$('#cbPanelBotaoFlutuante').remove();
		}
	});
	$(document).ready(function() {
		if ($("#comentarioopoup").length && $('#cbModuloForm').html() != '') {
			var comentario = $("#comentarioopoup")[0].innerHTML;
			CB.montaBotaoFlutuante('fa fa-comment fa-3x pointer', 'icone', comentario);
			CB.oPanelLegenda.css("zIndex", 901).addClass('screen');
			if ($('#cbSalvarComentario').length == 0) {
				$('#descricaobotao').append(`<button id="cbSalvarComentario" type="button" class="btn btn-success btn-xs" onclick="addCometario()" title="Salvar" style="float: right;margin-top: 8px;margin-right: 8px;">
				<i class="fa fa-circle"></i>Salvar
			</button>`);
			}
		}
	});

	function transfereTag(idempresa, idtag, idsolmatitem, idunidade, idtagpai) {
		console.log(idempresa, idtag, idsolmatitem, idunidade);
		CB.post({
			objetos: {
				"_w_i_solmatitemobj_idempresa": idempresa,
				"_w_i_solmatitemobj_idsolmatitem": idsolmatitem,
				"_w_i_solmatitemobj_idobjeto": idtag,
				"_w_i_solmatitemobj_tipoobjeto": 'tag',
				"_w_i_solmatitemobj_idunidadeanterior": idunidade,
				"_a_u_tag_idtag": idtag,
				"_a_u_tag_idfluxostatus": 1725,
				"_a_u_tag_status": 'ATIVO',
				"_a_u_tag_idunidade": $("[name=_1_u_solmat_idunidade]").val(),
				"_s_i_tagsala_idtag": idtag,
				"_s_i_tagsala_idtagpai": idtagpai
			},
			parcial: true
		});
	}

	function retornarTag(idtag, idsolmatitem, idsolmatitemobj) {
		$("[name=" + idsolmatitemobj + "_idunidadeanterior]").val()
		CB.post({
			objetos: {
				"_w_d_solmatitemobj_idsolmatitemobj": idsolmatitemobj,
				"_a_u_tag_idtag": idtag,
				"_a_u_tag_idfluxostatus": 1722,
				"_a_u_tag_status": 'ESTOQUE',
				"_a_u_tag_idunidade": $("#" + idsolmatitemobj + "_idunidadeanterior").val(),
				"_s_i_tagsala_idtag": idtag,
				"_s_i_tagsala_idtagpai": $("#" + idsolmatitemobj + "_idtagpaianterior").val()
			},
			parcial: true
		});
	}

	function addCometario() {
		console.log($("#_99_i_modulocom_descricao").val())
		ST.desbloquearCBPost();
		CB.post({
			objetos: {
				"_99_i_modulocom_descricao": $("#_99_i_modulocom_descricao").val(),
				"_99_i_modulocom_idmodulo": $("#_99_i_modulocom_idmodulo").val(),
				"_99_i_modulocom_modulo": $("#_99_i_modulocom_modulo").val()
			},
			parcial: true
		});
	}

	function gerarfracao(vthis, idsolmatitem, idlote, idlotefracao, qtd, qtdc) {
		var str = "_x_i_lotefracao_idlote=" + idlote +
			"&_x_i_lotefracao_idunidade=" + $("[name=_1_<?= $_acao ?>_solmat_idunidade]").val() +
			"&_x_i_lotefracao_idlotefracaoorigem=" + idlotefracao +
			"&_x_i_lotefracao_idobjetoconsumoespec=" + idsolmatitem +
			"&_x_i_lotefracao_tipoobjetoconsumoespec=solmatitem&_x_i_lotefracao_idlotefracaoorigem=" + idlotefracao +
			"&_x_i_lotefracao_qtd=" + $(vthis).val() + "&_qtdmaximo_=" + qtd + "&_qtdcmaximo_=" + qtdc;

		CB.post({
			objetos: str,
			parcial: true
		});
	}

	function excluirlotecons(...data) {
		if (data.length > 0) {
			let obj = {};
			for (let d in data) {
				if (d < data.length - 1) {
					obj[`_x${d}_u_lotecons_idlotecons`] = data[d];
					obj[`_x${d}_u_lotecons_status`] = "INATIVO";

				}
			}
			let idlotefracao = data[data.length - 1];
			if (idlotefracao != null) {
				obj[`_x300_d_lotefracao_idlotefracao`] = idlotefracao;
			}

			CB.post({
				objetos: obj,
				posPost: function(data, textStatus, jqXHR) {
					let modal = $('.modal-body');
					let vid = modal.find("input[type='hidden']").attr('idlote');
					modal.html($("#consumo_" + vid).html())
				},
				parcial: true
			});

		}
	}

	function consumo(vid) {
		CB.modal({
			titulo: "</strong>Histórico do Lote</strong>",
			corpo: $("#consumo_" + vid).html(),
			classe: 'sessenta',
			parcial: true
		});
	}
	if ($("[name=_1_<?= $_acao ?>_solmat_idsolmat]").val()) {
		$(".cbupload").dropzone({
			idObjeto: $("[name=_1_<?= $_acao ?>_solmat_idsolmat]").val(),
			tipoObjeto: 'solmat',
			idPessoaLogada: '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>'
		});
	}

	function alteraAltura(mostrar) {
		if (mostrar == 'Y') {
			$("#cbPanelBotaoFlutuante").css("height", "80%");
			$("#cbPanelBotaoFlutuante").css("width", "33%");
			$("#cbPanelBotaoFlutuante").css("overflow-y", "scroll");
			$("#cbPanelBotaoFlutuanteFechar").show();
			$("#cbPanelBotaoFlutuanteAbrir").hide();
			$('#cbSalvarComentario').show();
		} else {
			$("#cbPanelBotaoFlutuante").css("height", "auto");
			$("#cbPanelBotaoFlutuante").css("width", "auto");
			$("#cbPanelBotaoFlutuante").css("overflow-y", "hidden");
			$("#cbPanelBotaoFlutuanteFechar").hide();
			$("#cbPanelBotaoFlutuanteAbrir").show();
			$('#cbSalvarComentario').hide();
		}
	}
	CB.on('posLoadUrl', function() {
		<?
		if ($escondeCadAss == true) {
		?>
			$('#cass_assoc').hide();
		<? } ?>
		if (CB.jsonModulo.jsonpreferencias.botaoflutuante == 'N') {
			$('#cbSalvarComentario').hide();
		} else {
			$('#cbSalvarComentario').show();
		}
	});

	function esconderMostrarTodos(tipo) {
		let state = $("#esconderMostrarTodos").attr('state');
		let allCollapses = (state == 'Y') ? 'N' : 'Y';

		CB.setPrefUsuario('m', `{"${CB.modulo}":{"collapse":{"solmatitemtotal":"${allCollapses}"}}}`);

		$("#esconderMostrarTodos").attr('state', allCollapses);

		$("." + tipo).each(function() {
			CB.setPrefUsuario('m', `{"${CB.modulo}":{"collapse":{"solmatitem${$(this).attr('idnfitem')}":"${allCollapses}"}}}`);
			if (allCollapses == 'N') {
				$($(this).attr('href')).removeClass("collapse");
				$($(this).attr('href')).addClass("collapse in");
			} else {
				$($(this).attr('href')).removeClass("collapse in");
				$($(this).attr('href')).addClass("collapse");
			}
		});
	}
	// CB.montaLegenda({
	// 	"1": "ㅤPedidos AtéㅤㅤㅤㅤㅤEntrega Até ㅤ",
	// 	"2": "ㅤㅤ08:30hs ㅤㅤㅤㅤㅤㅤ10:00hs",
	// 	"3": "ㅤㅤ10:00hs ㅤㅤㅤㅤㅤㅤ11:30hs",
	// 	"4": "ㅤㅤ13:30hs ㅤㅤㅤㅤㅤㅤ15:00hs",
	// 	"5": "ㅤㅤ15:00hs ㅤㅤㅤㅤㅤㅤ16:30hs",
	// 	"6": "ㅤApós 15:00hsㅤㅤDia seguinte 10:00hs"
	// });
	// CB.oPanelLegenda.css("zIndex", 901);
	$(document).keydown(function(e) {
		if (e.keyCode == 27) {
			$('#cbPanelBotaoFlutuante').hide();
		}
	});

	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>