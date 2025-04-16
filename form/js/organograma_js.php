<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
	<script>
		/***************************************************************************
    Exemplo JSON vindo do Server
***************************************************************************/
		//debugger


		var jsonOrgan = <?= $JsonOrg ?>;
		var controlaMostragem = <?= $controlaMostragem ?>;
		var jsonPessoaSetor = <?= $JsonPessoaSetor ?>;


		var $organogramaEx = $("#organograma-exemplo"),
			fnShowHide,
			fnOffset,
			fnTamanhoHorizontal;

		/***************************************************************************
		    Criação do fluxograma no DOM
		***************************************************************************/
		$('[name=empresa]').on('change', (e, i) => {
			window.location.href = '?_idempresa=' + $(e.target).val();
		});

		(function createDOM() {
			var ul, li, div, docfrag, i = 0;
			for (var property in jsonOrgan) {
				if (jsonOrgan.hasOwnProperty(property)) {
					if (!document.getElementById("ul-" + jsonOrgan[property].pai)) {
						docfrag = document.createDocumentFragment();

						//var ul
						//<ul id="ul-" + jsonOrgan[property].pai" class="hide"></ul>
						ul = document.createElement("ul");
						ul.setAttribute("id", "ul-" + jsonOrgan[property].pai);
						ul.setAttribute("class", "hide");
						docfrag.appendChild(ul);

						document.getElementById("li-" + jsonOrgan[property].pai).appendChild(docfrag)
					}

					//var li
					//<li id="li-" + jsonOrgan[property].idcargo></li>
					li = document.createElement("li");
					li.setAttribute("id", "li-" + jsonOrgan[property].id);

					if (jsonOrgan[property].tipo === 'SETOR') {

						var str = '<div class="wrap-infos wrap-infos-padrao has-people zoom ' + jsonOrgan[property].tipo + '" data-toggle="collapse" totalpsetor="' + jsonOrgan[property].nresp + '" data-target="#funcionarios' + i + '" style="background-color:' + jsonOrgan[property].cor + '">' + '<p class="nome">' + jsonOrgan[property].nome + (jsonOrgan[property].responsavel || '') + "</p></div>";
						var strAux = '<div id="funcionarios' + i + '" class="collapse func">';
						strAux += '<div>';
						for (var aux in jsonPessoaSetor) {
							if (jsonPessoaSetor.hasOwnProperty(aux)) {
								if (jsonOrgan[property].nome === jsonPessoaSetor[aux].setor) {
									strAux += `<div class="cfuncionario" style="display: grid;grid-template-columns: 1fr 1fr;border-bottom: 1px solid;;">
													<div class='p-1 text-left' style='white-space: nowrap'>${jsonPessoaSetor[aux].nome}</div>
													<div class='p-1 text-left' style='white-space: nowrap'>${jsonPessoaSetor[aux].cargo}</div>
												</div>`;
								}
							}
						}
						
						strAux += "</div>";
						strAux += "</div>";
						str += strAux;
						document.getElementById("ul-" + jsonOrgan[property].pai).appendChild(li).innerHTML = str;
						i++;
						//<div id="funcionarios'+i+'" class="collapse func"><div class="row"><div class="col-sm-12">GABRIEL</div></div><div class="row"><div class="col-sm-12">GABRIEL</div></div></div>'
					} else {
						let totalp = "";
						switch (jsonOrgan[property].tipo) {
							case 'CONSELHO':
								totalp = 'totalconselho="' + jsonOrgan[property].nresp + '"';
								break;
							case 'AREA':
								totalp = 'totalparea="' + jsonOrgan[property].nresp + '"';
								break;
							case 'DEPARTAMENTO':
								totalp = 'totalpdepartamento="' + jsonOrgan[property].nresp + '"';
								break;
							default:
								break;
						}
						document.getElementById("ul-" + jsonOrgan[property].pai).appendChild(li).innerHTML = '<div class="wrap-infos wrap-infos-padrao zoom has-people mais ' + jsonOrgan[property].tipo + '" ' + totalp + ' style="background-color:' + jsonOrgan[property].cor + '">' + '<p class="nome"><b style="font-weight: bold;">' + jsonOrgan[property].nome + "</b>" + (jsonOrgan[property].responsavel || '') + "</p> </div>";
					}
				}
			}
		})();

		/***************************************************************************
		    Tamanho horizontal (scrollbar) da tela
		 ***************************************************************************/

		fnTamanhoHorizontal = function($thisButton) {
			//debugger;
			var u, l = [];
			$(".main").css("width", 100000);
			$(".main")
				.find("ul")
				.not(".hide")
				.each(function(i) {
					l[i] = $(this).width();
				});
			l.sort(function(a, b) {
				return a - b;
			});
			u = l.pop();
			var id = $($thisButton).attr("data-target");
			var s = $(id).width() + u + 100;
			$(".main")
				.css("width", s);
		};

		/***************************************************************************
		    Click nos Botões de Zoom
		***************************************************************************/

		fnShowHide = function(element) {
			element
				.toggleClass("mais")
				.toggleClass("menos");
			element
				.siblings("ul")
				.toggleClass("hide");
			$(".hightlight")
				.removeClass("hightlight");
			element
				.prevAll(".wrap-infos")
				.addClass("hightlight");

		};


		/***************************************************************************
		    Alinhando o ramo ativo mais próximo do centro da tela.
		   ***************************************************************************/

		fnOffset = function($btn) {
			if ($($btn).hasClass("AREA")) {
				if ($($btn).hasClass("menos"))
					$("body").css("zoom", "67%");
				else
					$("body").css("zoom", "100%");
			} else {
				if ($($btn).hasClass("DEPARTAMENTO")) {
					if ($($btn).hasClass("menos"))
						$("body").css("zoom", "67%");
					else
						$("body").css("zoom", "67%");
				} else {
					if ($($btn).hasClass("SETOR")) {
						if ($($btn).hasClass("menos"))
							$("body").css("zoom", "100%");
						else
							$("body").css("zoom", "67%");
					}
				}
			}
			var l, t;
			l = $btn.offset().left;
			t = $btn.offset().top;

			setTimeout(function() {
				$(document).scrollLeft(l - $(window).width() / 2 + 100);
				$(document).scrollTop(t);
			}, 25);
		};

		$organogramaEx.find(".zoom").each(function() {
			var $thisButton = $(this);
			$thisButton.on("click", function() {
				fnShowHide($thisButton);
				fnTamanhoHorizontal($thisButton);
				fnOffset($thisButton);
			});
		});





		/***************************************************************************
		    Seletores Adicionais
		   ***************************************************************************/

		$(".organograma li:last-child")
			.addClass("ultimo-filho");
		$(".organograma li:only-child")
			.addClass("filho-unico");

		/***************************************************************************
		    Ramos a mostrar logo após carregamento da página
		   ***************************************************************************/

		switch (controlaMostragem) {
			case 0:
				$("#ul-1").removeClass("hide");
				break;
			case 1:
				$("#ul-1,#ul-2").removeClass("hide");
				break;
			case 2:
				$("#ul-1,#ul-2,#ul-3,#ul-4").removeClass("hide");
				break;
			case 3:
				$("#ul-1,#ul-2,#ul-3,#ul-5").removeClass("hide");
				break;
			case 4:
				$("#ul-1,#ul-2,#ul-3,#ul-4,#ul-5").removeClass("hide");
				$("#funcionarios0").removeClass("collapse");
				break;
			default:
				$("#ul-1").removeClass("hide");
				break;
		}


		$(function() {
			var curDown = false,
				curYPos = 0,
				curXPos = 0;
			$(window).mousemove(function(m) {
				if (curDown === true) {
					$(window).scrollTop($(window).scrollTop() + (curYPos - m.pageY));
					$(window).scrollLeft($(window).scrollLeft() + (curXPos - m.pageX));
				}
			});

			$(window).mousedown(function(m) {
				curDown = true;
				curYPos = m.pageY;
				curXPos = m.pageX;
			});

			$(window).mouseup(function() {
				curDown = false;
			});
		});

		$("#pesquisa").click(function() { //debugger;
			$("#icon")
				.toggleClass("glyphicon-chevron-down")
				.toggleClass("glyphicon-chevron-up");
			if ($("#pesquisa").hasClass("aux1")) {
				$("#pesquisa").toggleClass("aux2").toggleClass("aux1");
				$("#pesquisa").css("margin-top", "161px");
			} else {
				$("#pesquisa").css("margin-top", "0px");
				$("#pesquisa").toggleClass("aux2").toggleClass("aux1");
			}
		});

		if ($(".SEMVINCULO").length) {
			$(".SEMVINCULO").each((i, o) => {
				let totald = 0;
				let $oCirculoDep = $(`<div id="target">
								<div id="text">${totald}</div>
							</div>`);
				$(o).prepend($oCirculoDep);
				$(o).parent().parent().siblings().attr("totalpempresa", totald)
			})
		}

		$(".SETOR").siblings().each((i, o) => {
			let pessoas = $(o).find(".cfuncionario").length;
			let totals = pessoas + parseInt($(o).siblings().attr('totalpsetor'));
			let $oCirculoSet = $(`<div id="target">
								<div id="text">${totals}</div>
							</div>`);

			$(o).siblings().prepend($oCirculoSet).attr('totalpsetor', totals);

			let totald = totals + parseInt($(o).parent().parent().siblings().attr("totalpdepartamento"));

			$(o).parent().parent().siblings().attr('totalpdepartamento', totald);

		});

		$(".DEPARTAMENTO").each((i, o) => {
			let pessoas = parseInt($(o).attr("totalpdepartamento"));
			let totald = pessoas + parseInt($(o).parent().parent().siblings().attr("totalparea"));
			let $oCirculoDep = $(`<div id="target">
								<div id="text">${pessoas}</div>
							</div>`);
			$(o).prepend($oCirculoDep);
			$(o).parent().parent().siblings().attr("totalparea", totald)
		})

		$(".AREA").each((i, o) => {
			let conselho = $(o).parent().parent().siblings().attr("totalconselho") || 0;
			let pessoas = parseInt($(o).attr("totalparea"));
			let totald = pessoas + parseInt(conselho);
			let $oCirculoDep = $(`<div id="target">
								<div id="text">${pessoas}</div>
							</div>`);
			$(o).prepend($oCirculoDep);
			$(o).parent().parent().siblings().attr("totalconselho", totald)
		});

		$(".CONSELHO").each((i, o) => {
			let conselho = $(o).parent().parent().siblings().attr("totalpempresa") || 0;
			let pessoas = parseInt($(o).attr("totalconselho"));
			let totald = pessoas + parseInt(conselho);
			let $oCirculoDep = $(`<div id="target">
								<div id="text">${pessoas}</div>
							</div>`);
			$(o).prepend($oCirculoDep);
			$(o).parent().parent().siblings().attr("totalpempresa", totald)
		});

		let $oCirculoCeo = $(`<div id="target">
								<div id="text">${$(".ceo").attr("totalpempresa")}</div>
							</div>`);
		$(".ceo").prepend($oCirculoCeo);

		let $oCirculoDep = $(`<div id="target">
								<div id="text">${$(".ceo").attr("totalpempresa")}</div>
							</div>`);
		$(".empresa").prepend($oCirculoDep);
	</script>