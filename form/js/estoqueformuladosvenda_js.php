<script>
	//------- Injeção PHP no Jquery -------
	var faltaFuturo = '<?=$faltaFuturo?>';
	var falta = '<?=$falta?>';
	//------- Injeção PHP no Jquery -------

	$(document).ready(function() {
		$("#filtroAlertaEstoque").on("keyup", function() {
			let value = $(this).val().toLowerCase().normalize("NFD").replace(/[^a-zA-Zs]/, "");
			$("#restbl .restblbody tr").filter(function() {
				$(this).toggle($(this).text().toLowerCase().normalize("NFD").replace(/[^a-zA-Zs]/, "").indexOf(value) > -1)
			});
		});
	});

	//Montar legenda para o usuário
	CB.montaLegenda({
		"#FDD0C7": "Produto com Estoque Futuro em Falta menor do que 1.",
		"#98FB98": "Produto com Estoque Futuro em Falta maior do que 0."
	});
	CB.oPanelLegenda.css("zIndex", 901);

	CB.on('posLoadUrl', function(){
		(falta > 0) ? $('.verdeqtd').text(falta) : $('.verdeqtd').hide();
		(faltaFuturo > 0) ? $('.vermelhoqtd').text(faltaFuturo) : $('.vermelhoqtd').hide();
	});

	function sortTable(e) 
	{
		var th = e.target.parentElement;
		$(e.target).addClass("azul");
		$(th).addClass("ativo");
		$(e.target).siblings().removeClass("azul");
		$(th).siblings().removeClass("ativo");
		$(e.target.parentElement).siblings().each((e, o) => {
			$(o).children().removeClass('azul').css('opacity', '0')
		})
		var ordenacao = $(e.target).attr("attr");
		switch (ordenacao) {
			case 'asc':
				colunas = -1;
				break;
			case 'desc':
				colunas = 1;
				break;

			default:
				colunas = 1
				break;
		}

		var n = 0;
		while (th.parentNode.cells[n] != th) ++n;
		var order = th.order || 1;
		//th.order = -order;
		var t = this.closest("thead").nextElementSibling;
		var bottonLine = $(t.rows).filter('.bottonLine');

		t.innerHTML = Object.keys($(t.rows).not('.bottonLine'))
			.filter(k => !isNaN(k))
			.map(k => t.rows[k])
			.sort((a, b) => order * (isNaN(typed(a)) && isNaN(typed(b))) ? ((typed(a).localeCompare(typed(b)) > 0) ? colunas : -colunas) : (typed(a) > typed(b) ? colunas : -colunas))
			.map(r => r.outerHTML)
			.join('')

		function typed(tr) {
			var s = $(tr.cells[n]).attr('filtervalue');

			if (s.match(",")) {
				isNaN(s.replaceAll(",", ".")) ? s = s.toString() : s = s.replaceAll(",", ".")
			}
			if (isNaN(s) && s.match(/^[a-zA-Z]+/)) {
				var d = s;
				var date = d;
			} else {
				if (s.match("/") && s.match(/^[a-zA-Z]+/) == null) {

					var d = mda(s);
					var date = Date.parse(d);
				} else {
					var d = s;
					var date = d;
				}

			}
			if (!isNaN(date)) {
				return isNaN(date) ? s.toLowerCase() : Number(date);
			} else {
				if (!isNaN(s.replaceAll(",", '.'))) {
					return Number(s.replaceAll(",", '.'));
				} else {

					return s.toLowerCase();
				}
			}
		}

		$('#restbl tbody').append(bottonLine);
	}


	$('#restbl thead th i').on('click', sortTable);

	$('#restbl thead th').mouseover(function() {
		$(this).children().not("[id=cbOrdCres], [id=cbOrdDecr]").each((e, o) => {
			$(o).css("opacity", "1").addClass('hoverazul')
		})
	});

	$('#restbl thead th').mouseout(function() {
		$(this).children().not("[id=cbOrdCres], [id=cbOrdDecr]").each((e, o) => {
			if (!$(o).hasClass('azul')) {
				$(o).css("opacity", "0").removeClass('hoverazul')
			}
		})
	});

	function alterarValorCampos(campo, valor, tabela, inid, texto) 
	{
		htmlTrModelo = $(`#alterarValor`).html();
		htmlTrModelo = htmlTrModelo.replace(`#namerotulo`, texto);
		htmlTrModelo = htmlTrModelo.replace(`#name_justificativa`, `_1_u_${tabela}_justificativa`);
		htmlTrModelo = htmlTrModelo.replace(`#name_idtabela`, `_1_u_${tabela}_id${tabela}`);
		htmlTrModelo = htmlTrModelo.replace(`#valor_idtabela`, inid);
		
		if (campo == 'estmin') {
			htmlTrModelo = htmlTrModelo.replace(`#valor_campo`, valor);
			htmlTrModelo = htmlTrModelo.replace(`#name_campo_input`, `_1_u_${tabela}_${campo}`);
			var objfrm = $(htmlTrModelo);
			objfrm.find(`#campoinput`).removeAttr("disabled").show();
			objfrm.find(`#camposelect`).attr("disabled", "disabled").hide();
		} else {
			htmlTrModelo = htmlTrModelo.replace(`#name_campo_select`, `_1_u_${tabela}_${campo}`);
			var objfrm = $(htmlTrModelo); 
			objfrm.find(`#camposelect option[value='${valor}']`).attr("selected", "selected");
			objfrm.find(`#campoinput`).attr("disabled", "disabled").hide();
			objfrm.find(`#camposelect`).removeAttr("disabled").show();
		}

		strCabecalho = `</strong>Alterar ${texto} <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='formatarValor()'><i class='fa fa-circle'></i>Salvar</button></strong>`;

		CB.modal({
			titulo: strCabecalho,
			corpo: `<table>${objfrm.html()}</table>`,
			classe: 'sessenta',
		});

	}

	function formatarValor()
	{
		estim = $('[name="_1_u_prodservformula_estmin"]').val();
		if(estim != undefined)
		{
			if(estim.indexOf("d") == -1 && estim.indexOf("e") == -1)
			{
				estim = estim.replace(".", "").replace(",", ".");
			}

			$('[name="_1_u_prodservformula_estmin"]').val(estim);
		}

		CB.post();
	}

	CB.on('posPost',()=>{
		$("#cbModal").modal("hide");
	});

	$(window).on('scroll load', function(){
		var win_scl = $(window).scrollTop(); // valor do scroll da janela
		var nav = $('#restbl thead'); // menu
		var nav_ant = nav.parent().prev('table'); // div antes do menu
		var nav_hgt = nav.outerHeight(); // altura do menu
		var nav_ant_hgt = nav_ant.outerHeight(); // altura da div antes do menu
		var nav_ant_top = nav_ant.offset().top; // distância da div antes do menu ao topo
		var nav_ant_dst = nav_ant_top-win_scl; // distancia do final da div antes do menu ao topo da janela
		var width = [];
		if(nav_ant_dst <= nav_ant_hgt && win_scl > nav_ant_hgt) {
			nav.css({'position': 'fixed', 'top': '40px','background-color':'white', 'width': '91.4%', 'z-index': '999'});
			$('#restbl tbody').find('td').each(function (e,i){
				width[e] = $(i).width();
			});
			nav.find("th").each(function (e,i){
				$(i).width(width[e]);
			});
			// adiciono margem superior à primeira div depois do menu
			// $("#restbl thead").css('margin-top',nav_hgt+'px');
		}else{
			nav.css({'position': '', 'top': ''});;
			nav.children().children().css('width',nav.width)
			// retiro margem superior à primeira div depois do menu
			// $("#restbl thead").css('margin-top','0');
		}
	});

	// adicionar rodapé em todos os JS de forms p/ ser possível debuggar em produção
	//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>