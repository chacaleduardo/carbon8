function openTab(evt, cityName) {
    // Declare all variables
    var i, tabcontent, tablinks;

    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
}

var cloneEvento = '<a class="toggle" href="#example" id="abrir">' +
    ' 	<div class="row eventoRow" style="color:#333 !important;padding:8px;position:relative;">' +
    '		<div class="col-lg-2 col-sm-3 col-xs-6 atalhoEvento"  style="font-size: 12px; color: #333333;">' +
    '			<div class="col-lg-12 col-xs-12" style="font-size: 12px;">' +
    '				<div style="border-radius:15px;color:#333;padding: 2px 6px;font-size:9px;word-break:normal;text-align:center;text-transform:uppercase;" class="eventotipo"></div>' +
    '			</div>' +
    '		</div>' +
    '		<div class="col-lg-4 col-sm-9 col-xs-12 atalhoHist" style="display: block; word-break: break-word;font-size: 12px;">' +
    '			<div class="col-lg-12 col-xs-12 descricao" style="min-height: 24px;border-bottom:1px solid #ddd;text-transform:uppercase;font-size:10px;margin:0px 12px;"></div>' +
    '		</div>' +
    '	<div class="col-lg-5 col-sm-8 col-xs-12 atalhoPart" style="font-size: 12px;">' +
    '		<div class="col-lg-6 col-xs-12 " style="font-size: 12px;">' +
    '			<div class="hrefs" style="border-radius:15px;width:100%;text-transform:uppercase;color:#fff;padding: 2px 6px;font-size:9px;word-break:normal;text-align:center;"><i class="fa fa-calendar" style="font-size: 12px; line-height: 9px;margin-right:4px;"></i></div>' +
    '		</div>' +
    '		<div class="col-lg-3 col-xs-12 origem" style="font-size: 10px; color: #333;">' +
    '			<i class="fa fa-user" style="font-size: 12px; line-height: 9px;margin-right:4px;color:#999;"></i>' +
    '		</div>' +
	'		<div class="col-lg-3 col-xs-12 prazo" style="font-size: 10px; color: #FFFFFF;">'+
	'		</div>'+
    '	</div>' +
    '	<div class="col-lg-1 col-xs-12" >' +
    '		<div class="linkmodulo">' +
    '          <i style="float:right; font-size:22px; margin-right:4px;" class="fa fa-paperclip fa-2x pointer fade hrefmodulo" title="Link Modulo" onclick=""></i>' +
    '       </div>' +
    '	</div>' +
	'</div>';

$("#searchInput").keyup(function() {
	if ($("#searchInput").val() == "") {
		let filter = $("#btn-2").hasClass('selecionado') ? 'todas' : $("#btn-1").hasClass('selecionado') ?
			'minhas' : 'ocultos';
		$(".eventos").empty();
		toggleFiltrarTarefas(filter);
	}
});

$("#searchInput").on('keypress', function(e) {
    if (e.which == 13) {
        $(".eventos").empty();
        if ($("#searchInput").val() == "") {
            let filter = $("#btn-2").hasClass('selecionado') ? 'todas' : $("#btn-1").hasClass('selecionado') ?
                'minhas' : 'ocultos';
            toggleFiltrarTarefas(filter);
        } else {
            search();
        }
    }
});

$("#exibir").click(function() {
    let offset = $(".eventoRow").length;
    let filter = $("#btn-2").hasClass('selecionado') ? 'todas' : $("#btn-1").hasClass('selecionado') ?
        'minhas' : 'ocultos';
    loadEventos(offset, filter);
});

function novaTarefa(miniEvento = false, ideventoadd, objeto, idobjeto) {
    CB.compartilharAlerta('carregaTiposAlerta', function callback(lastInsertId) {
		if(miniEvento == true){
			novaTarefaMiniEvento(lastInsertId, ideventoadd);
		}
        getEvento(lastInsertId, miniEvento);		
    }, objeto, idobjeto);
}

function novaTarefaMiniEvento(lastInsertId, ideventoadd) {
	CB.post({
		objetos: {
			"_x_i_eventoobj_idevento":$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
			,"_x_i_eventoobj_idobjeto": lastInsertId
			,"_x_i_eventoobj_objeto": 'evento'
			,"_x_i_eventoobj_ideventoadd": ideventoadd
		}
		,parcial: true
	});
}

function toggleFiltrarTarefas(filtro, idEvento = false, ideventoadd = null) {
    $('.link').attr("href", function() {
		$(this).attr('href', $(this).attr('href').replace("vfilter=todas", "vfilter="));
		$(this).attr('href', $(this).attr('href').replace("vfilter=ocultos", "vfilter="));
		$(this).attr('href', $(this).attr('href').replace("vfilter=", "vfilter=" + filtro));
    });

    $("#exibir").show();
    $("#exibidos").text("0");
    $(".eventos").empty();
    $("#exibir").text("Exibir mais");
	$("#searchInput").val("");
	if(idEvento == true){
		loadEventosMiniEvento(0, filtro, ideventoadd);
	} else {
		loadEventos(0, filtro);
	}
}

function getEvento(idEvento) {
	var token = Cookies.get('jwt') || localStorage.getItem("jwt") || "";

	ordenacao = 'e.idevento asc';
    fetch('ajax/evento.php?vopcao=eventos&videvento=' + idEvento + '&vordenacao=' + ordenacao, {
		headers: {
				"Content-Type": "application/json",
				"authorization": token
			}
		}).then(function(response) {
        return response.json();
    }).then(function(data) {
		if(data.error)
		{
			return alertAtencao(data.error);
		}

        if (data && data.length > 0) {
            criaEventos(data);
        }
    });
}

function loadEventosMiniEvento(offset, filter, ideventoadd) 
{
	var token = Cookies.get('jwt') || localStorage.getItem("jwt") || "";
    vfiltro = filter;
    if (Number.isInteger(parseInt(vfiltro, 10))) {
        var str = '&videvento=' + vfiltro;
        $('#example').removeClass('is-visible');
    } else {
        var str = '';
    }

    if (($("#exibidos").text() != $("#totais").text()) || $("#totais").text() == 0) {
        fetch('ajax/evento.php?vopcao=eventos&voffset=' + offset + '&vfilter=' + filter + '&minievento=Y&vordenacao=idevento&vfilterEventoTipo=' + filter +
			  '&ideventoadd='+ideventoadd+
            str, {
                headers: {
                    "Content-Type": "application/json",
					"authorization": token
                }
            }).then(function(response) {
            return response.json();
        }).then(function(data) {
			if(data.error)
			{
				return alertAtencao(data.error);
			}

            if (data && data.length > 0) {
                //alert('criar');
				criaEventos(data, ideventoadd);
            } else {
                //$("#exibidos").text("0");
                //$("#totais").text("0");
            }
        });
    }
}


//Insere Novo Bloco no Evento - LTM (06/07/2020)
function NovoBloco(inidevento,inideventotipoadd, titulo,tipoobjeto){
	CB.post({
		objetos: {
			"_x_i_eventoadd_idobjeto":inideventotipoadd
			,"_x_i_eventoadd_objeto":'ideventotipoadd'
			,"_x_i_eventoadd_idevento":inidevento
			,"_x_i_eventoadd_titulo":titulo
			,"_x_i_eventoadd_tipoobjeto":tipoobjeto
		}
		,parcial: true
	});
}


//Insere Novo Bloco no Evento - LTM (06/07/2020)
function adicionarNovoBloco(inidevento, iniord, repetir, objeto){
	$.ajax({
		type: "get",
		url: "ajax/evento.php?vopcao=adicionarNovoBloco&videvento="+inidevento+"&vorder="+iniord+"&vtipoobjeto=minievento&vrepetir="+repetir+"&vobjeto="+objeto,
		success: function(data){
			if(data.error)
			{
				return alertAtencao(data.error);
			}

			CB.post();
		}
		,parcial: true
        ,refresh: false
	});	  
}

function modalEvento(o, url) {
    $(o).removeClass("naoVisualizado");
    let filter = $(o).attr("idevento");
    CB.modal({
        url: url,
        header: "Evento",
        id: filter,
        aoFechar: function(inPar) {
            loadEventos(0, filter);
        }
    });
}

function criaEventos(eventos, ideventoadd = null) {

    if (eventos && eventos.length > 0) {
		
        eventos.forEach(function(evento) {
            $("#totais").text(evento.totalResultados);

            let verify = $("#idevento_" + evento.idevento);

            if (!verify.length) {

                let apenasOcultos = $("#btn-3").hasClass('selecionado') ? true : false;
                let clone = $(cloneEvento).clone();
                let prazo = "";
                let color = "";
                let descricao = "";

                let corLinha = 'button-blue';

				if (evento.prazorestante == '0d 00h 00m 00s ') {
					prazorestante = '<i>venc.</i>';
				} else {
					if (typeof evento.prazorestante === "undefined") {
						prazorestante = evento.prazorestante;
					} else {
						prazorestante = evento.prazorestante.split(" ");
						if (prazorestante[0] != '0d') {
							if (prazorestante[0].indexOf('-') !== -1) {
								prazorestante = '<i>venc.</i>';
							} else {
								prazorestante = prazorestante[0];
							}
						} else if (prazorestante[1] != '00h') {
							if (prazorestante[1].indexOf('-') !== -1) {
								prazorestante = '<i>venc.</i>';
							} else {
								prazorestante = prazorestante[1];
							}
						} else if (prazorestante[2] != '00m') {
							if (prazorestante[2].indexOf('-') !== -1) {
								prazorestante = '<i>venc.</i>';
							} else {
								prazorestante = prazorestante[2];
							}
						} else if (prazorestante[3] != '00s') {
							if (prazorestante[3].indexOf('-') !== -1) {
								prazorestante = '<i>venc.</i>';
							} else {
								prazorestante = prazorestante[3];
							}
						}
					}								
				}

                if (evento.configprazo == 'N') {
                    dataTarefa = '<span class="dataTarefa">' + evento.prazo + '</span>';
					if(evento.coricone){
						$(clone).find('.eventoRow #boxdata').css(JSON.parse(evento.coricone));
					}
                } else {
                    dataTarefa = '<span class="dataTarefa">' + prazorestante + '</span>';
                    if (prazorestante == '<i>venc.</i>') {
                        $(clone).find('.eventoRow .dataTarefa').css("background", "#ac202e");
                        $(clone).find('.eventoRow .dataTarefa').css("color", "#fff");
                        $(clone).find('.eventoRow .dataTarefa').css("box-shadow", "none");
                    }
                }

                if (evento.visualizado == 1) {
                    $(clone).find('.eventoRow').addClass("");
                } else {
                    $(clone).find('.eventoRow').addClass("naoVisualizado");
                }
                $(clone).find('.eventoRow [mostraprazo]').addClass(evento.mostraprazo);
                $(clone).find('.eventoRow [mostradata]').addClass(evento.mostradata);
                $(clone).find('.eventoRow').attr("modulo", evento.modulo);
                $(clone).find('.eventoRow').attr("idmodulo", evento.idmodulo);
                $(clone).find('.eventoRow').attr("mostradata", evento.mostradata);
                $(clone).find('.eventoRow').attr("travasala", evento.travasala);
                $(clone).find('.eventoRow').attr("diainteiro", evento.diainteiro);
                $(clone).find('.eventoRow').attr("duracaohms", evento.duracaohms);
                $(clone).find('.eventoRow').attr("idequipamento", evento.idequipamento);
                $(clone).find('.eventoRow').attr("iniciodata", evento.iniciodata);
                $(clone).find('.eventoRow').attr("inicio", evento.inicio);
                $(clone).find('.eventoRow').attr("iniciohms", evento.iniciohms);
                $(clone).find('.eventoRow').attr("prazo", evento.prazo);
                $(clone).find('.eventoRow').attr("configprazo", evento.configprazo);
                $(clone).find('.eventoRow').attr("posicao", evento.posicao);
                $(clone).find('.eventoRow').attr("posicaofim", evento.posicaofim);
                $(clone).find('.eventoRow').attr("id", "idevento_" + evento.id);
                $(clone).find('.eventoRow').attr("idfluxostatuspessoa", evento.idfluxostatuspessoa);
                $(clone).find('.eventoRow').attr("eventotipo", evento.eventotipo);
                $(clone).find('.eventoRow').attr("cor", evento.cor);
                $(clone).find('.eventoRow').attr("corstatus", evento.corstatus);
                $(clone).find('.eventoRow').attr("cortextostatus", evento.cortextostatus);
                $(clone).find('.eventoRow').attr("corstatusresp", evento.corstatusresp);
                $(clone).find('.eventoRow').attr("rotuloresp", evento.rotuloresp);
                $(clone).find('.eventoRow').attr("status", evento.status);
                $(clone).find('.eventoRow').attr("evento", evento.evento);
                $(clone).find('.eventoRow').attr("descricao", evento.descricao);
                $(clone).find('.eventoRow').attr("fluxo", evento.fluxo);
                $(clone).find('.eventoRow').attr("criadoempor", evento.criadoempor);
                $(clone).find('.eventoRow').attr("alteradoempor", evento.alteradoempor);
                $(clone).find('.eventoRow').attr("idevento", evento.idevento);
                $(clone).find('.eventoRow').attr("slaprazo", evento.slaprazo);
                $(clone).find('.eventoRow').attr("ideventos", evento.idevento);
                $(clone).find('.eventoRow').attr("fim", evento.fim);

                $(clone).find('.progress-wrap').attr("data-progresspercent", evento.slaprazo);
                $(clone).find('.progress-bar').css("width", evento.slaprazo);

                $(clone).find('.eventotipo').css("border", "1px solid " + evento.cor);
                $(clone).find('.eventotipo').css("color", evento.cor);
                $(clone).find('.ideventos').text(evento.idevento);
                $(clone).find('.ideventos').css("cursor", "pointer");
                // $(clone).find('.ideventos').attr("onclick", "javascript:modalEvento(this,'?_modulo=evento&_acao=u&idevento="+evento.idevento+"')");
                $(clone).find('.origem').append(evento.nomecurto);
                $(clone).find('.descricao').html(evento.evento);
                $(clone).find('.eventotipo').html(evento.eventotipo +
                    '<div style="text-align: center;font-size: 9px; padding: 0px 4px; color: #333; background-color: transparent; width: auto;" class="ideventos alert-warning">' +
                    evento.idevento + '</div>');

                //Alterado para aparecer o prazo de vencimento. Lidiane (23/06/2020)  	
                now = new Date;
                var colorprazo;
                var classe;

				//Valida se o Prazo é maior que hoje. Se for fica cinza escuro, senão, vermelho
                if((evento.dataslaprazo != null && Date.parse(evento.dataslaprazo) >= Date.parse(moment().format('YYYY-MM-DD')) && evento.fim == 'Y')
					|| (evento.inicio != null && Date.parse(evento.inicio) >= Date.parse(moment().format('YYYY-MM-DD')))
					|| (evento.prazo2 != null && Date.parse(evento.prazo2) >= Date.parse(moment().format('YYYY-MM-DD')))
					|| (evento.posicaofim == 'FIM' || evento.posicaofim == 'CANCELADO' || evento.posicaofim == 'CONCLUIDO')
					|| evento.col == 'inicio'){
                    colorprazo = '';
					colorText = 'black';
                } else {
					colorprazo = '#DC143C;';
					colorText = 'white';
                }
	
				//Valida se será prazo ou inicio
                if(evento.configprazo == 'Y'){
                    classe = 'calendariotime';
                } else {
                    classe = 'calendarioprazo';            
                }
				
				//Valida qual data aparecerá na lisatagem. CAso seja negativo aparecerá a data
				if(evento.dataslaprazo != null && Math.sign(evento.dataslaprazo) == 1){
					dataPrazo = evento.slaprazo;
				} else {
					dataPrazo = evento.prazo;
				}
				
                $(clone).find('.prazo').html(
					'<div style="color:'+colorText+'; border-radius:15px;width:100%;text-transform:uppercase;background:'+colorprazo+'; 2px 25px 0px 30px; font-size:10px;word-break:normal;text-align:center;">'+
                        '<p class="'+classe+'" value="'+dataPrazo+'" name="novoprazo">'+ dataPrazo +'</p>'+
                    '</div>');

                //$(clone).find('.dataAlerta').empty();
                $(clone).find('.dataTarefa').append(dataTarefa);
                $(clone).find('.hoverlaranja').attr("onclick", "visualizarComentarios(" + evento.idevento +
                    "," + evento.idpessoa + ")");
                $(clone).find('.hoververde').attr("onclick", "visualizarResponsaveis(" + evento.idevento + "," +
                    evento.idpessoa + ")");

                // $(clone).find('.hrefs').attr("onclick", "javascript:modalEvento(this,'?_modulo=evento&_acao=u&idevento="+evento.idevento+"')");
                $(clone).find('.hrefs').css("background", evento.corstatus);
                $(clone).find('.hrefs').css("color", evento.cortextostatus);
                $(clone).find('.hrefs').append(evento.rotulo);

                if (evento.modulo != undefined && evento.modulo != '' && evento.modulo != 'evento') {

                    //$(clone).find('.hrefmodulo').attr("onclick", "javascript:janelamodal('"+evento.modulo+"')");
                    $(clone).find('.hrefmodulo').attr("onclick", "javascript:janelamodal('?_modulo=" + evento
                        .modulo + "&_acao=u&" + evento.chavemodulo + "=" + evento.idmodulo + "')");
                } else {

                    $(clone).find(".linkmodulo").hide();
                }

                if (apenasOcultos) {
                    $(clone).find('.hoververmelho').attr("onclick", "verificaDesocultar(" + evento.idevento +
                        ")");
                    $(clone).find('.hoververmelho').attr("title", "Desocultar tarefa");
                    $(clone).find('.hoververmelho').removeClass("fa-eye-slash");
                    $(clone).find('.hoververmelho').addClass("fa-eye");
                } else {
                    $(clone).find('.hoververmelho').attr("onclick", "verificaOcultar(" + evento.idevento + ")");
                }

                $(clone).find('.checker').attr("data-idevento", evento.idevento);
                $(clone).find('.vertarefa').attr("onclick",
                    "modalEvento(this,'?_modulo=evento&_acao=u&idevento=" + evento.idevento + "')");
                //$(clone).find('.hoverlaranja').attr("onClick", "popupCompartilharTarefa("+evento.idevento+")");
                //$(clone).attr("id", "idevento_"+evento.idevento);

                if (evento.visualizado == undefined || evento.visualizado == "" || evento.visualizado == "0" ||
                    evento.visualizado == 0) {

                  //  $(clone).css("background-color", "#c4ebf5");
                    $(clone).addClass("naoVisualizado")
                } else {

                    $(clone).css('background-color', corLinha);
                }
				
				if(ideventoadd == null){
					$(clone).appendTo(".eventos");
				} else {
					$(clone).appendTo(".eventos_"+ideventoadd);
				}
				
				$(clone).find('.prazo').html(
					'<div style="color:'+colorText+'; border-radius:15px;width:100%;text-transform:uppercase;background:'+colorprazo+'; 2px 25px 0px 30px; font-size:10px;word-break:normal;text-align:center;">'+
						'<p class="'+classe+'" value="'+dataPrazo+'" name="novoprazo">'+ dataPrazo +'</p>'+
					'</div>');

				if(ideventoadd == null){
					$(clone).appendTo(".eventos");
				} else {
					$(clone).appendTo(".eventos_"+ideventoadd);
				}
					
                $("#exibidos").text($(".eventoRow").length);

                if ($("#exibidos").text() == $("#totais").text()) {
                    $("#exibir").text("");
                } else {
                    $("#exibir").text("Exibir mais");
                }
            } else {
                $ev = $("#idevento_" + evento.idevento);

                if (evento.configprazo == 'N') {
                    dataTarefa = '<span class="dataTarefa">' + evento.prazo + '</span>';
                    $ev.find('.eventoRow #boxdata').css(JSON.parse(evento.coricone));


                } else {
                    dataTarefa = '<span class="dataTarefa">' + evento.prazorestante + '</span>';
                    if (evento.prazorestante == '<i>venc.</i>') {
                    //    $ev.css("background", "#ac202e");
                    //    $ev.css("color", "#fff");
                    //    $ev.css("box-shadow", "none");

                    }
                }


                if (evento.visualizado == 1) {
                    $ev.addClass("");
                } else {
                    $ev.addClass("naoVisualizado");
                }

                if (evento.oculto == 1 && evento.minievento != 'Y') {
                    $ev.hide();
                }

                $ev.find('.eventoRow [mostraprazo]').addClass(evento.mostraprazo);
                $ev.find('.eventoRow [mostradata]').addClass(evento.mostradata);
                $ev.attr("modulo", evento.modulo);
                $ev.attr("idmodulo", evento.idmodulo);
                $ev.attr("mostradata", evento.mostradata);
                $ev.attr("travasala", evento.travasala);
                $ev.attr("diainteiro", evento.diainteiro);
                $ev.attr("duracaohms", evento.duracaohms);
                $ev.attr("idequipamento", evento.idequipamento);
                $ev.attr("iniciodata", evento.iniciodata);
                $ev.attr("inicio", evento.inicio);
                $ev.attr("iniciohms", evento.iniciohms);
                $ev.attr("prazo", evento.prazo);
                $ev.attr("configprazo", evento.configprazo);
                $ev.attr("posicao", evento.posicao);
                $ev.attr("posicaofim", evento.posicaofim);
                $ev.attr("id", "idevento_" + evento.id);
                $ev.attr("idfluxostatuspessoa", evento.idfluxostatuspessoa);
                $ev.attr("eventotipo", evento.eventotipo);
                $ev.attr("cor", evento.cor);
                $ev.attr("corstatus", evento.corstatus);
                $ev.attr("cortextostatus", evento.cortextostatus);
                $ev.attr("corstatusresp", evento.corstatusresp);
                $ev.attr("rotuloresp", evento.rotuloresp);
                $ev.attr("status", evento.status);
                $ev.attr("evento", evento.evento);
                $ev.attr("descricao", evento.descricao);
                $ev.attr("fluxo", evento.fluxo);
                $ev.attr("criadoempor", evento.criadoempor);
                $ev.attr("alteradoempor", evento.alteradoempor);
                $ev.attr("idevento", evento.idevento);
                $ev.attr("slaprazo", evento.slaprazo);
                $ev.attr("ideventos", evento.idevento);
                $ev.find('.progress-wrap').attr("data-progresspercent", evento.slaprazo);
                $ev.find('.progress-bar').css("width", evento.slaprazo);
                $ev.find('.eventotipo').css("border", "1px solid " + evento.cor);
                $ev.find('.eventotipo').css("color", evento.cor);
                $ev.find('.ideventos').text(evento.idevento);
                $ev.find('.ideventos').css("cursor", "pointer");
                $ev.find('.origem').html(evento.nomecurto);
                $ev.find('.descricao').html(evento.evento);
                $ev.find('.eventotipo').html(evento.eventotipo +
                    '<div style="text-align: center;font-size: 9px; padding: 0px 4px; color: #333; background-color: transparent; width: auto;" class="ideventos alert-warning">' +
                    evento.idevento + '</div>');

                //Alterado para aparecer o prazo de vencimento. Lidiane (23/06/2020)  	
                now = new Date;
                var colorprazo;
                var classe;

               //Valida se o Prazo é maior que hoje. Se for fica cinza escuro, senão, vermelho
			   if((evento.dataslaprazo != null && Date.parse(evento.dataslaprazo) >= Date.parse(moment().format('YYYY-MM-DD')) && evento.fim == 'Y')
			   || (evento.inicio != null && Date.parse(evento.inicio) >= Date.parse(moment().format('YYYY-MM-DD')))
			   || (evento.prazo2 != null && Date.parse(evento.prazo2) >= Date.parse(moment().format('YYYY-MM-DD')))
			   || (evento.posicaofim == 'FIM' || evento.posicaofim == 'CANCELADO' || evento.posicaofim == 'CONCLUIDO')){
					colorprazo = ';';
					colorText = 'black';
				} else {
					colorprazo = '#DC143C;';
					colorText = 'white';
				}

				//Valida se será prazo ou inicio
				if(evento.configprazo == 'Y'){
					classe = 'calendariotime';
				} else {
					classe = 'calendarioprazo';            
				}
				
				//Valida qual data aparecerá na lisatagem. CAso seja negativo aparecerá a data
				if(evento.dataslaprazo != null && Math.sign(evento.dataslaprazo) == 1){
					dataPrazo = evento.slaprazo;
				} else {
					dataPrazo = evento.prazo;
				}			

				$ev.find('.prazo').html(
					'<div style="color:'+colorText+'; border-radius:15px;width:100%;text-transform:uppercase;background:'+colorprazo+'; 2px 25px 0px 30px; font-size:10px;word-break:normal;text-align:center;">'+
                        '<p class="'+classe+'" value="'+dataPrazo+'" name="novoprazo">'+ dataPrazo +'</p>'+
                    '</div>');

                $ev.find('.dataTarefa').html(dataTarefa);
                $ev.find('.hoverlaranja').attr("onclick", "visualizarComentarios(" + evento.idevento + "," +
                    evento.idpessoa + ")");
                $ev.find('.hoververde').attr("onclick", "visualizarResponsaveis(" + evento.idevento + "," +
                    evento.idpessoa + ")");
                $ev.find('.hrefs').css("background", evento.corstatus);
                $ev.find('.hrefs').css("color", evento.cortextostatus);
                $ev.find('.hrefs').html(evento.rotulo);

                if (evento.modulo != undefined && evento.modulo != '') {
                    $ev.find('.hrefmodulo').attr("onclick", "javascript:janelamodal('?_modulo=" + evento
                        .modulo + "&_acao=u&" + evento.chavemodulo + "=" + evento.idmodulo + "')");
                } else {
                    $ev.find(".linkmodulo").hide();
                }


                $ev.find('.checker').attr("data-idevento", evento.idevento);
                $ev.find('.vertarefa').attr("onclick", "modalEvento(this,'?_modulo=evento&_acao=u&idevento=" +
                    evento.idevento + "')");
  
                if (evento.visualizado == undefined || evento.visualizado == "" || evento.visualizado == "0" ||
                    evento.visualizado == 0) {

                    //$ev.css("background-color", "#c4ebf5");
                    $ev.addClass("naoVisualizado")
                } else {

                    // $ev.css('background-color', corLinha);
                }
				
                $("#exibidos").text($(".eventoRow").length);

                if ($("#exibidos").text() == $("#totais").text()) {
                    $("#exibir").text("");
                } else {
                    $("#exibir").text("Exibir mais");
                }
            }
        });
    }


    $('.atalhoPart').click(function(e) {
        //abrirAtalho($(this).parent(), 'participantes');
		 modalEvento($(this).parent(), '?_modulo=evento&_acao=u&idevento=' + $(this).parent().attr("idevento"));
    });

    $('.atalhoHist').click(function(e) {
        //abrirAtalho($(this).parent(), 'conteudo');
		 modalEvento($(this).parent(), '?_modulo=evento&_acao=u&idevento=' + $(this).parent().attr("idevento"));
    });

    $('.atalhoEvento').click(function(e) {
        modalEvento($(this).parent(), '?_modulo=evento&_acao=u&idevento=' + $(this).parent().attr("idevento"));
        $('#example').removeClass('is-visible');
    });
}

function editarpedido(pedido)
{  
    $("[name="+pedido+"]").removeAttr("readonly");
	$("[name="+pedido+"]").removeClass("desabilitado");
}


function deletaeventoadd(inid){
	CB.post({
		objetos: {
			"_x_d_eventoadd_ideventoadd":inid
		}
		,parcial: true
	}); 
}
//TipoEvento
function retirasgsetor(inid){
    CB.post({
        objetos: {
            "_x_d_fluxostatuspessoa_idfluxostatuspessoa":inid
        }
        ,parcial: true
        ,refresh: false
        ,posPost:function(){
            $.ajax({
                type: "post",
                 url: "ajax/evento.php?vopcao=atualizaparticipantes&videvento="+$("[name=_1_u_evento_idevento]").val(),
                success: function(data){
					if(data.error)
					{
						return alertAtencao(data.error);
					}

                    //alertAzul("Participantes atualizados","",1000);
                    location.reload();

                }
            });
        }
    });
}

function fnovornc(idevento){
    let titulo              = $("#motivornc").val();
    let idsgdocdocumento    = $("#motivornc").attr("cbvalue");
  
    CB.post({
        objetos: {
            "_x_i_sgdoc_idsgdoctipo":'rnc'
            ,"_x_i_sgdoc_status":'AGUARDANDO'
            ,"_x_i_sgdoc_idsgdoctipodocumento":idsgdocdocumento
            ,"_x_i_sgdoc_titulo":titulo
			,"_chamaprechange_":true
        }
        ,parcial: true
        ,refresh: false
        ,posPost: function(){
            //Alteração realizada, pois havia dois campos com idsgdoc - Lidiane (28-04-2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=314921
            CB.post({
                objetos: {
                    "_x_u_evento_idevento":idevento
                    ,"_x_u_evento_idsgdocrnc":CB.lastInsertId
                }
                ,parcial: true
            }); 
            //fvinculadoc(idevento, CB.lastInsertId);
        }
    }); 
}

$(document).ready(function(){
	if(sessionStorage.getItem('tipoParticpante') == 'funcionario'){
		habilitaFuncionario();
		$("#btfunc").addClass("selecionado");
	}
	
	if(sessionStorage.getItem('tipoParticpante') == 'setor'){
		habilitaSetor();
		$("#btset").addClass("selecionado");
	}
});

function escondebotao(){
    $('#btAssina').hide();
   // document.location.reload(); 
}

//Permite ordenação dos elementos
$(".tbTags tbody").sortable({
	update: function(event, objUi){
		ordenaStatusTbTags();
	}
});

function ordenaStatusTbTags(){
	$.each($(".tbTags tbody").find("tr"), function(i,otr){
		$(this).find(":input[name*=eventoobj_ord]").val(i);
	});
}

//Permite ordenação dos elementos
$(".ordenarBloco").sortable({
	update: function(event, objUi){
		ordenarBloco();
	}
});

function ordenarBloco(){
	$.each($(".ordenarBloco").find(".ordenarBlocoItem"), function(i,otr){
		$(this).find(":input[name*=eventoadd_ord]").val(i);
	});
}

$(function(){
    var textArea = $('.textdin'),
    hiddenDiv = $(document.createElement('div')),
    content = null;
    
    textArea.addClass('noscroll');
    hiddenDiv.addClass('hiddendiv');
    
    $(textArea).after(hiddenDiv);
    
    textArea.on('keyup', function(){
        content = $(this).val();
        content = content.replace(/\n/g, '<br>');
        hiddenDiv.html(content + '<br class="lbr">');
        $(this).css('height', hiddenDiv.height());
    }).on('click',function(){
        content = $(this).val();
        content = content.replace(/\n/g, '<br>');
        hiddenDiv.html(content + '<br class="lbr">');
        $(this).css('height', hiddenDiv.height());
    });
});

//Lista os eventos que foram alterados de cada pessoa com a possibilidade de Voltar ao evento anterior (Lidiane 11-03-2020)
function carregaFiltroTipoEvento(idfluxostatuspessoa, idpessoa, idevento)
{	
	$.ajax({
		type: "post",
		url: "ajax/evento.php?vopcao=retornaStatus&idobjeto="+idfluxostatuspessoa+"&idpessoa="+idpessoa+"&videvento="+idevento,
		success: function(data) 
		{
			if(data.error)
			{
				return alertAtencao(data.error);
			}

			var radio = '<div class="panel-body" id="'+idpessoa+'" style="margin-left: 40px;" aria-expanded="false">';
			radio += '<fieldset class="scheduler-border">';
			radio += '<table style="font-size:11px;">';
			if(data != 'null'){
				radio += '<tr><td>Clique no Botão para voltar status anterior do Evento<br /></td></tr>';
				radio += '<tr><td></td></tr>';
				radio += '<tr><td>';
				var obj = JSON.parse(data);
				var acao = "";
				var historico = obj['historico'];
				var botao = obj['botao'];
				
				for(i in botao) {	
					var itembotao = botao[i];	
					acao = "AlteraStatusfluxopessoa("+idfluxostatuspessoa+","+itembotao.idfluxostatus+", '"+itembotao.oculto+"', true, '"+itembotao.botao+"', '"+itembotao.nomecurto+"', 'true')";
					radio += '<button onclick="'+acao+'" type="button" style="float: left; margin-left: 5px; margin-bottom: 7px; margin-top: -2px; display: block;color:#fff;background:'+itembotao.cor+';color:'+itembotao.cortexto+'" class="btn btn-xs">';
					radio += '<i class="fa fa-refresh"></i>'+itembotao.botao+'</button>';						
				}
				radio += '<br /><br />';
				radio += '</tr></td>';
				radio += '<tr><td>';
				for(i in historico) {	
					var itemhist = historico[i];	
					radio += '<div style="padding-bottom: 10px;">'
					radio += '<span class="circle button-'+itemhist.cor+'" style="background:'+itemhist.cor+'"></span>&nbsp;&nbsp;'+itemhist.criadoem;
					radio += '<br />';
					radio += '</div>'; 
				}
			} else {
				radio += '<tr><td>Não existe histórico.<br /></td></tr>';
			}	
			radio += '</tr></td>';
			radio += '</table>';
			radio += '</fieldset>'; 
			radio += '</div>'; 
			$('#collapse-'+idpessoa).html(radio);	
			CB.loadUrl();
		}
	});	
}

function carregaFiltroTipoEventoUnico(
		idfluxostatuspessoa, idpessoa, idfluxostatusevento,
		statusEvento, nomeCurto
	) {	
	const btnRestaurarStatus = `<div class="panel-body" id="${idpessoa}" style="margin-left: 40px;" aria-expanded="false">
									<fieldset class="scheduler-border">
										<table style="font-size:11px;">
											<tbody>
												<tr><td>Clique no Botão para retornar ao evento.<br></td></tr>
												<tr><td></td></tr>
												<tr>
													<td>
														<button class="btn btn-primary" onclick="AlteraStatusfluxopessoa(${idfluxostatuspessoa},${idfluxostatusevento}, 'N', true, '${statusEvento}', '${nomeCurto}', true)";">
															Revelar evento
														</button>
														<br>
														<br>
													</td>
												</tr>
											</tbody>
										</table>
									</fieldset>
								</div>`;
	
	$('#collapse-'+idpessoa).html(btnRestaurarStatus);
}

//EventoTipo
$('.selectpicker').selectpicker('render');

//Permite ordenação dos elementos
$("#eventostatus tbody").sortable({
	update: function(event, objUi){
		ordenaStatus();
	}
});

function ordenaStatus(){
	$.each($("#eventostatus tbody").find("tr"), function(i,otr){
		$(this).find(":input[name*=ordem]").val(i);
	});
}

function novoadd(inideventotipo){
    CB.post({
        objetos: "_x_i_eventotipoadd_ideventotipo="+inideventotipo
    });
}

function selectOnlyThis(id) {
    $(".chkinicial").prop('checked', false);
	$("#"+id+"").prop('checked', true);
}

function selectOnlyThis2(id) {
    $(".chkfinal").prop('checked', false);
	$("#"+id+"").prop('checked', true);
}

$('.tagRow').hide();
$('.docRow').hide();
$('.pessoaRow').hide();
$('#tdsgsetor').hide();
$('#tdfuncionario').show();
$('#tdsgsetor2').hide();
$('#tdfuncionario2').show();

$statusDisp=$("#statusDisponiveis");

skelStatus = `<tr token="%token%" itr="%itr%">
		<td><i class="fa fa-arrows cinzaclaro hover move" title="Ordenar"></i></td>
		<td>
			<div class="input-group input-group-sm">
			
				<span class="fa %icocor% colorpicker input-group-addon pointer dropdown-toggle " data-toggle="dropdown" title="Alterar cor" style="color:%color%" color="%color%" >
				</span>
				<ul class="dropdown-menu colorpalette" token="%token%" style="background:#e1e1e1"></ul>
				<input autocomplete="off" type="text" class="form-control statuses"  id="status_%itr%" value="%status%">
				<input autocomplete="off" type="text" class="form-control acoes"  id="acao_%itr%" value="%acao%">
			</div>
		</td>
		<td><input type="radio" id="inicial_%itr%"  aria-label="..." value="" onclick="selectOnlyThis(this.id)" %inicial%  class="chkinicial inicial" ></td>
		<td><input type="radio" id="final_%itr%"  aria-label="..." value="" onclick="selectOnlyThis2(this.id)" %final%  class="chkfinal final" ></td>
		<td><input type="checkbox" id="dono_%itr%"  aria-label="..." value="" %dono%  class="chkdono dono" ></td>
		<td><input type="checkbox" id="ndono_%itr%"  aria-label="..." value="" %ndono%  class="chkndono ndono" ></td>
		<td><input type="checkbox" id="ocultacri_%itr%"  aria-label="..." value="" %ocultacri%  class="chkocultacri ocultacri" ></td>
		<td><input type="checkbox" id="desocultacri_%itr%"  aria-label="..." value="" %desocultacri%  class="chkdesocultacri desocultacri" ></td>
		<td><input type="checkbox" id="ocultaind_%itr%"  aria-label="..." value="" %ocultaind%  class="chkocultaind ocultaind" ></td>
		<td><input type="checkbox" id="desocultaind_%itr%"  aria-label="..." value="" %desocultaind%  class="chkdesocultaind desocultaind" ></td>7
		<td><input type="checkbox" id="oculta_%itr%"  aria-label="..." value="" %oculta%  class="chkoculta oculta" ></td>
		<td><input type="checkbox" id="desoculta_%itr%"  aria-label="..." value="" %desoculta%  class="chkdesoculta desoculta" ></td>
		<td><input type="checkbox" id="assina_%itr%"  aria-label="..." value="" %assina%  class="chkassina assina" ></td>
		<td><input type="checkbox" id="exclui_%itr%"  aria-label="..." value="" %exclui%  class="chkexclui exclui" ></td>
		<td><input type="checkbox" id="restaura_%itr%"  aria-label="..." value="" %restaura%  class="chkrestaura restaura" ></td>
		
		

			<input id="prioridade_%itr%" autocomplete="off" type="hidden" class="form-control prioridade"  style="width:100%;" value="%prioridade%"  >
		<td class="proxes"></td>
		<td align="center">					
		<a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="removeStatus(this)" title="Excluir"></a>				
		</td>
    </tr>`;
    
skelProxStatus=`<i title="%title%" class="iproxstatus fa %selico% dropdown-toggle %selecionado%" token="%token%" data-toggle="dropdown" style="color:%color%;" onclick="selecionaProxStatus(this)"></i>`;

var camposPersonalizados = [];

var jsonconfig = {
	rnc: false,
	alerta: false,
	assinar: false,
	arquivo: false,
	calendario: false,
	permissoes: {
		setores: [],
		funcionarios: []
	},
	configprazo: false,
	configstatus: false,
	tags: [],
	pessoas: [],
	statuses: [],
	documentos: [],
	personalizados: []
};

$('.selecttipo').selectpicker({
	liveSearch: true
});

function criaOption(nome, count) {

	if (nome === undefined) {
		nome = '';
	}

	let novaOption = '<div class="row novooption option'+count+'" data-campo="'+count+'">\
			<div class="col-lg-3">\
				<label>Descrição:</label>\
				<input type="text" placeholder="Nome da opção" id="descricaoOption'+count+'" value="'+nome+'" onchange="atualizaOption(this, '+count+');">\
			</div>\
			<div class="col-lg-3">\
				<span style="margin-top: 18px"\
					class="btn btn-sm btn-danger size3" id="deletarOption'+count+'" onclick="deletarOption(this, '+count+');"\
					title="Excluir"><i class="fa fa-minus pointer"></i></span>\
			</div>\
		</div>';

	return novaOption;
}

function removeStatus(e){
	$(e).closest('tr').remove();
}

function criaPermissao(item, title, modulo, status) {
	
	return '<tr id="'+modulo+item.value+'">\
				<td style="min-width: 10px;" id="statuses">\
					<span class="circle button-blue"></span>\
				</td>\
				<td >'+item.label+'</td>\
				<td>\
					<a class="fa fa-bars fa-1x pointer hoverazul" title="'+title+'"\
						onclick="janelamodal(\'?_modulo='+modulo+'&_acao=u&id'+modulo+'='+item.value+'\')"></a>\
				</td>\
				<td align="center">\
					<a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable"\
						onclick="removePermissao(this, '+item.value+', \''+modulo+'\')" title="Excluir"></a>\
				</td>\
			</tr>';
}

function toggle(inId,inChk){

	var vYN = (inChk.checked)?"Y":"N";
	
	var strPost = "_ajax_u_eventotipocampos_ideventotipocampos="+inId
				+ "&_ajax_u_eventotipocampos_visivel="+vYN;

	CB.post({
		objetos: strPost
		,refresh: false
	});
}

function atualizavalor(vthis,inideventotipo){
	
    var strval= $(vthis).val();
    CB.post({
        objetos: {
            "_x_u_eventotipo_ideventotipo":inideventotipo
            ,"_x_u_eventotipo_tagtipoobj":strval

        }
        ,parcial: true
        ,refresh:false
    });
}

function atualizavalordoc(vthis,inideventotipo){
	
    var strval= $(vthis).val();
    CB.post({
        objetos: {
            "_x_u_eventotipo_ideventotipo":inideventotipo
            ,"_x_u_eventotipo_sgdoctipoobj":strval

        }
        ,parcial: true
        ,refresh:false
    });
}

function atualizavalorpessoa(vthis,inideventotipo){
	
    var strval= $(vthis).val();
    CB.post({
        objetos: {
            "_x_u_eventotipo_ideventotipo":inideventotipo
            ,"_x_u_eventotipo_tipopessoaobj":strval

        }
        ,parcial: true
        ,refresh:false
    });
}

function atualizavalorad(vthis,inideventotipo){
	
    var strval= $(vthis).val();
    CB.post({
        objetos: {
            "_x_u_eventotipoadd_ideventotipoadd":inideventotipo
            ,"_x_u_eventotipoadd_tagtipoobj":strval

        }
        ,parcial: true
        ,refresh:false
    });
}
function atualizavalordocad(vthis,inideventotipo){
	
    var strval= $(vthis).val();
    CB.post({
        objetos: {
            "_x_u_eventotipoadd_ideventotipoadd":inideventotipo
            ,"_x_u_eventotipoadd_sgdoctipoobj":strval

        }
        ,parcial: true
        ,refresh:false
    });
}

function atualizavalorpessoaad(vthis,inideventotipo){
	
    var strval= $(vthis).val();
    CB.post({
        objetos: {
            "_x_u_eventotipoadd_ideventotipoadd":inideventotipo
            ,"_x_u_eventotipoadd_tipopessoaobj":strval

        }
        ,parcial: true
        ,refresh:false
    });
}

function atcampo(inideventotipo,incampo,invalor){
    CB.post({
        objetos: "_1_u_eventotipo_ideventotipo="+inideventotipo+"&_1_u_eventotipo_"+incampo+"="+invalor 
		,parcial: true	
		,refresh:false
    });
}

function eventotipocampos(ideventotipocampos,invis,nord){
    
    if(invis=="N"){
        nord='';
    }
    
    
    CB.post({
        objetos: "_1_u_eventotipocampos_ideventotipocampos="+ideventotipocampos+"&_1_u_eventotipocampos_visivel="+invis+"&_1_u_eventotipocampos_ord="+nord  
	,parcial: true

    });
}

function atcampoad(inideventotipoadd,incampo,invalor){
	CB.post({
        objetos: "_1_u_eventotipoadd_ideventotipoadd="+inideventotipoadd+"&_1_u_eventotipoadd_"+incampo+"="+invalor 
		,parcial: true	
		,refresh:false
    });
}

function equipamentocheckedad(event,inideventotipo) {
	if (event.checked) {
		$('#tagtipoobjad'+inideventotipo).show();
		
		atcampoad(inideventotipo,'tag','Y');
	} else {
		$('#tagtipoobjad'+inideventotipo).hide();
	
		
		$('option[value="tag"]').remove();
		CB.post({			
			objetos:"_x_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_x_u_eventotipoadd_tagtipoobj=''&_1_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_1_u_eventotipoadd_tag=N" 
			,parcial: true				
		});
		//atcampo(inideventotipo,'tag','N');
	}
}

function prodservcheckedad(event,inideventotipo) {
	if (event.checked) {
		
		$('#prodservtipoobjad'+inideventotipo).show();
		atcampoad(inideventotipo,'prodserv','Y');
	} else {
		
		$('[data-id="prodserv"]').hide();
	
		$('#prodservtipoobjad'+inideventotipo).remove();
		CB.post({			
			objetos:"_x_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_x_u_eventotipoadd_prodservtipoobj=''&_1_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_1_u_eventotipoadd_prodserv=N" 
			,parcial: true				
		});
		//atcampo(inideventotipo,'prodserv','N');
	}
}

function minieventocheckedad(event,inideventotipo) {
	if(event.checked) {
		$('#planilhagrade'+inideventotipo).hide();
		$('.equipamento'+inideventotipo).attr('disabled', true);
		$('.documento'+inideventotipo).attr('disabled', true);
		$('.pessoa'+inideventotipo).attr('disabled', true);
		$('.prodserv'+inideventotipo).attr('disabled', true);
		atcampoad(inideventotipo,'minievento','Y');	
	} else {
		$('#planilhagrade'+inideventotipo).show();
		$('.equipamento'+inideventotipo).attr('readonly', true);
		$('.documento'+inideventotipo).attr('readonly', true);
		$('.pessoa'+inideventotipo).attr('readonly', true);
		$('.prodserv'+inideventotipo).attr('readonly', true);
		CB.post({			
			objetos:"_x_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_1_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_1_u_eventotipoadd_minievento=N" 
			,parcial: true				
		});
	}
}

function documentocheckedad(event,inideventotipo) {
	if (event.checked) {
		
		$('#sgdoctipoobjad'+inideventotipo).show();
		atcampoad(inideventotipo,'sgdoc','Y');
	} else {
		
		$('[data-id="tipodocumento"]').hide();
	
		$('#sgdoctipoobjad'+inideventotipo).remove();
		CB.post({			
			objetos:"_x_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_x_u_eventotipoadd_sgdoctipoobj=''&_1_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_1_u_eventotipoadd_sgdoc=N" 
			,parcial: true				
		});
		//atcampo(inideventotipo,'sgdoc','N');
	}
}

function pessoacheckedad(event,inideventotipo) {
	if (event.checked) {
		
		$('#tipopessoaobjad'+inideventotipo).show();
		atcampoad(inideventotipo,'pessoa','Y');
	} else {
		
		$('#tipopessoaobjad'+inideventotipo).hide();
	
		$('option[value="pessoa"]').remove();
		CB.post({			
			objetos:"_x_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_x_u_eventotipoadd_tipopessoaobj=''&_1_u_eventotipoadd_ideventotipoadd="+inideventotipo+"&_1_u_eventotipoadd_pessoa=N" 
			,parcial: true				
		});
		//atcampo(inideventotipo,'pessoa','N');
	}
}

function equipamentochecked(event,inideventotipo) {
	if (event.checked) {
		$('#tagtipoobj').show();
		
		atcampo(inideventotipo,'tag','Y');
	} else {
		$('#tagtipoobj').hide();
	
		
		$('option[value="tag"]').remove();
		CB.post({			
			objetos:"_x_u_eventotipo_ideventotipo="+inideventotipo+"&_x_u_eventotipo_tagtipoobj=''&_1_u_eventotipo_ideventotipo="+inideventotipo+"&_1_u_eventotipo_tag=N" 
			,parcial: true				
		});
		//atcampo(inideventotipo,'tag','N');
	}
}

function documentochecked(event,inideventotipo) {
	if (event.checked) {
		
		$('#sgdoctipoobj').show();
		atcampo(inideventotipo,'sgdoc','Y');
	} else {
		
		$('[data-id="tipodocumento"]').hide();
	
		$('#sgdoctipoobj').remove();
		CB.post({			
			objetos:"_x_u_eventotipo_ideventotipo="+inideventotipo+"&_x_u_eventotipo_sgdoctipoobj=''&_1_u_eventotipo_ideventotipo="+inideventotipo+"&_1_u_eventotipo_sgdoc=N" 
			,parcial: true				
		});
		//atcampo(inideventotipo,'sgdoc','N');
	}
}

function pessoachecked(event,inideventotipo) {
	if (event.checked) {
		
		$('#tipopessoaobj').show();
		atcampo(inideventotipo,'pessoa','Y');
	} else {
		
		$('#tipopessoaobj').hide();
	
		$('option[value="pessoa"]').remove();
		CB.post({			
			objetos:"_x_u_eventotipo_ideventotipo="+inideventotipo+"&_x_u_eventotipo_tipopessoaobj=''&_1_u_eventotipo_ideventotipo="+inideventotipo+"&_1_u_eventotipo_pessoa=N" 
			,parcial: true				
		});
		//atcampo(inideventotipo,'pessoa','N');
	}
}

function eventotipochecked(event,inideventotipo,incampo){
	if (event.checked) {		
		atcampo(inideventotipo,incampo,'Y');
	} else {		
		atcampo(inideventotipo,incampo,'N');
	}	
}

function prazochecked(event,inideventotipo) {
	if (event.checked) {
		jsonconfig.configprazo = true;
		$('.config-prazo').show();
		atcampo(inideventotipo,'prazo','Y');
	} else {
		jsonconfig.configprazo = false;
		$('.config-prazo').hide();
		atcampo(inideventotipo,'prazo','N');
	}
}

function retiraeventotiporesp(inid){
	    CB.post({
        objetos: {
            "_x_d_fluxoobjeto_idfluxoobjeto":inid
        }
        ,parcial: true
    });
}

function showfuncionarioAlerta() {
	$('#tdsgsetor').hide();
	$('#tdfuncionario').show();
}

function showsgsetorAlerta() {
	$('#tdsgsetor').show();
	$('#tdfuncionario').hide();
}
function showfuncionario2() {
	$('#tdsgsetor2').hide();
	$('#tdfuncionario2').show();
}

function showsgsetor2() {
	$('#tdsgsetor2').show();
	$('#tdfuncionario2').hide();
}

function OrdenaPersonalizados(){
	$campos=$("#camposBody > .novocampo ");
	iCampos=$campos.length;
	var i =0;
	//Loop em cada TR
	$.each($campos, function(i,campo){
		
		$campo=$(campo);
		i++;

	});
}

function alteraCorStatus(inObj,inITr,inToken){
	$statusDisp.find("i[token="+inToken+"]").css("color",$(inObj).css("color"))
}

function selecionaProxStatus(inObj,inideventotipo,arideventostatus){
	$ico=$(inObj);
	if($ico.hasClass("selecionado")){
		$ico.removeClass("fa-circle selecionado").addClass("fa-circle-o");
	}else{
		$ico.removeClass("fa-circle-o").addClass("fa-circle selecionado");
	}
        
        CB.post({
            objetos: {
                "_x_u_eventotipo_ideventotipo": inideventotipo,
                "_x_u_eventotipo_inideventostatus": arideventostatus
            },
            parcial: true
        });
}

function getStatuses(){

	var statuses=[];
	var chinicial = '';
	var chfinal = '';	
	var chdono = '';
	var chndono = '';
	var chocultaind = '';
	var chdesocultaind = '';
	var chocultacri = '';
	var chdesocultacri = '';
	var chexclui = '';
	var chassina = '';
	var chrestaura = '';
	var choculta = '';
	var chdesoculta = '';
	$.each($("#statusDisponiveis tr"),function(i,tr){
		$tr=$(tr);
		$status=$tr.find("input.statuses");
		$acao=$tr.find("input.acoes");
		var token=$tr.attr("token");
		var prioridade=$tr.find("input.prioridade");
		var inicial=$tr.find("input.inicial");
		var acao=$tr.find("input.acao");
		var final=$tr.find("input.final");
		var dono=$tr.find("input.dono");
		var ndono=$tr.find("input.ndono");
		var ocultaind=$tr.find("input.ocultaind");
		var desocultaind=$tr.find("input.desocultaind");
		var ocultacri=$tr.find("input.ocultacri");
		var desocultacri=$tr.find("input.desocultacri");
		var assina=$tr.find("input.assina");
		var exclui=$tr.find("input.exclui");
		var restaura=$tr.find("input.restaura");
		var oculta=$tr.find("input.oculta");
		var desoculta=$tr.find("input.desoculta");
		var color=$tr.find(".colorpicker").attr("color");
		var proxes=getProxes();
		

		//Verifica se já existia um status tokenizado (sem acentos ou espaços)
		if(!$status.val()||$status.val()==""){
			alertAtencao("Informe corretamente o Status!","Campo vazio",1000);
			$status.focus().addClass("highlight");
			statuses=false;
			return false;//break loop

		}else if(!color || color.lenght==0){
			alertAtencao("Informe corretamente uma cor para o Status!","Status sem cor",1000);
			$tr.find(".colorpicker").addClass("highlight");
			statuses=false;
			return false;//break loop
		} /*else if(!proxes){
			alertAtencao("Informe corretamente os Próximos Status!","Status incompleto",1000);
			$tr.find("td.proxes").addClass("highlight");
			statuses=false;
			return false;//break loop
		}*/ else{
			var sNovoToken=(!token||token.length==0)?$status.val().replace(/[^a-zA-Z]+/g, '').toLowerCase():token;
			var sColor=color;

			if (inicial[0] !== 'undefined'){
				if (inicial[0].checked){
					chinicial = true;
				}else{
					chinicial = false;
				}	
			}
			if (final[0] !== 'undefined'){
				if (final[0].checked){
					chfinal = true;
				}else{
					chfinal = false;
				}	
			}
			if (dono[0] !== 'undefined'){
				if (dono[0].checked){
					chdono = true;
				}else{
					chdono = false;
				}	
			}
			if (ndono[0] !== 'undefined'){
				if (ndono[0].checked){
					chndono = true;
				}else{
					chndono = false;
				}	
			}
			if (ocultaind[0] !== 'undefined'){
				if (ocultaind[0].checked){
					chocultaind = true;
				}else{
					chocultaind = false;
				}	
			}
			if (desocultaind[0] !== 'undefined'){
				if (desocultaind[0].checked){
					chdesocultaind = true;
				}else{
					chdesocultaind = false;
				}	
			}
			if (ocultacri[0] !== 'undefined'){
				if (ocultacri[0].checked){
					chocultacri = true;
				}else{
					chocultacri = false;
				}	
			}
			if (desocultacri[0] !== 'undefined'){
				if (desocultacri[0].checked){
					chdesocultacri = true;
				}else{
					chdesocultacri = false;
				}	
			}
			if (assina[0] !== 'undefined'){
				if (assina[0].checked){
					chassina = true;
				}else{
					chassina = false;
				}	
			}
			if (exclui[0] !== 'undefined'){
				if (exclui[0].checked){
					chexclui = true;
				}else{
					chexclui = false;
				}	
			}
			if (restaura[0] !== 'undefined'){
				if (restaura[0].checked){
					chrestaura = true;
				}else{
					chrestaura = false;
				}	
			}
			if (oculta[0] !== 'undefined'){
				if (oculta[0].checked){
					choculta = true;
				}else{
					choculta = false;
				}	
			}
			if (desoculta[0] !== 'undefined'){
				if (desoculta[0].checked){
					chdesoculta = true;
				}else{
					chdesoculta = false;
				}	
			}
			statuses.push({
				"status":$status.val()
				,"token":sNovoToken
				,"color":sColor
				,"proxes":proxes
				,"prioridade":prioridade.val()
				,"inicial":chinicial
				,"final":chfinal
				,"dono":chdono
				,"ndono":chndono
				,"ocultaind":chocultaind
				,"desocultaind":chdesocultaind
				,"ocultacri":chocultacri
				,"desocultacri":chdesocultacri
				,"assina":chassina
				,"exclui":chexclui
				,"restaura":chrestaura
				,"oculta":choculta
				,"desoculta":chdesoculta
				,"acao":$acao.val()
	        	});

		}
	});
	return statuses;
}

function atualizaToken(e){
    $(e).closest("tr").attr("token",e.value);
}


$("#addOption").click(function() {

   let novaOption = criaOption('', optionCount);
   let indiceCampo = localStorage.getItem('lastIndiceCampo');

   $('.optionsBody').append($(novaOption));
   let option = {
       indice: optionCount,
       nome:""
   };

   for(var j = 0; j < camposPersonalizados.length; j++) {
       if (camposPersonalizados[j].indice === parseInt(indiceCampo)) {
           camposPersonalizados[j].options.push(option);
       }
   }

optionCount++;

});

function deletarCampo(element, index) {

	for (var i = index+1; i < camposPersonalizados.length; i++) {
 
		$("#deletarCampo"+i).closest('.novocampo').removeClass("campo"+i);
		$("#deletarCampo"+i).closest('.novocampo').addClass("campo"+(i-1));
		$("#deletarCampo"+i).closest('.novocampo').attr('data-campo', (i-1));
 
		$("#titulo"+i).attr('onkeyup', 'atualizaTitulo(this, '+(i-1)+')');
		$("#titulo"+i).attr('id', 'titulo'+(i-1));
 
		$("#tipo"+i).removeClass("tipo"+i);
		$("#tipo"+i).addClass("tipo"+(i-1));
		$("#tipo"+i).attr('onchange', 'atualizaTipo(this, '+(i-1)+')');
		$("#tipo"+i).attr('id', 'tipo'+(i-1));
 
		$("#vinculo"+i).removeClass("vinculo"+i);
		$("#vinculo"+i).addClass("vinculo"+(i-1));
		$("#vinculo"+i).attr('onchange', 'atualizaVinculo(this, '+(i-1)+')');
		$("#vinculo"+i).attr('id', 'vinculo'+(i-1));
 
		$("#deletarCampo"+i).attr('onclick', 'deletarCampo(this, '+(i-1)+')');
		$("#deletarCampo"+i).attr('id', 'deletarCampo'+(i-1));
		$("#editarTipo"+i).attr('onclick', 'atualizaTipo(this, '+(i-1)+')');
		$("#editarTipo"+i).attr('id', 'editarTipo'+(i-1));
	}
	
	$(element).closest('.novocampo').remove();
	let excluido = false;
 
	for(var i = 0; i < camposPersonalizados.length; i++) {
 
		if (camposPersonalizados[i].indice === index) {
			camposPersonalizados.splice(i, 1);
			excluido = true;
		}
		let temp = i-index;
		if (excluido && i != camposPersonalizados.length) {
			camposPersonalizados[i].indice--;
		}
	}
	count = camposPersonalizados.length;
 
	jsonconfig.personalizados = camposPersonalizados;
	//$("#jsonconfig").val(""+JSON.stringify(jsonconfig));
 
 }

 function deletarOption(element, index) {

	let indiceCampo = localStorage.getItem('lastIndiceCampo');
	let excluido = false;
 
	for(var j = 0; j < camposPersonalizados.length; j++) {
		if (camposPersonalizados[j].indice === parseInt(indiceCampo)) {
			for(var i = 0; i < camposPersonalizados[j].options.length; i++) {
 
				$("#descricaoOption"+i).closest('.novocampo').removeClass("option"+i);
				$("#descricaoOption"+i).closest('.novocampo').addClass("option"+(i-1));
				$("#descricaoOption"+i).closest('.novocampo').attr('data-campo', (i-1));
 
				$("#descricaoOption"+i).attr('onchange', 'atualizaOption(this, '+(i-1)+')');
				$("#descricaoOption"+i).attr('id', 'descricaoOption'+(i-1));
 
				$("#deletarOption"+i).attr('onclick', 'deletarOption(this, '+(i-1)+')');
				$("#deletarOption"+i).attr('id', 'deletarOption'+(i-1));
			}
		}
	}
 
	$(element).closest('.novooption').remove();
 
	for(var j = 0; j < camposPersonalizados.length; j++) {
		if (camposPersonalizados[j].indice === parseInt(indiceCampo)) {
			for(var i = 0; i < camposPersonalizados[j].options.length; i++) {
 
				if (camposPersonalizados[j].options[i].indice === index) {
					camposPersonalizados[j].options.splice(i, 1);
					excluido = true;
				}
				
				if (excluido && i != camposPersonalizados[j].options.length) {
					camposPersonalizados[j].options[i].indice--;
				}
 
			}
			optionCount = camposPersonalizados[j].options.length;
		}
	}
 }
 
 function atualizaTitulo(element, index) {
 
	for(var i = 0; i < camposPersonalizados.length; i++) {
		if (camposPersonalizados[i].indice === index) {
			camposPersonalizados[i].titulo = $(element).val();
		}
	}
 
	jsonconfig.personalizados = camposPersonalizados;
	//$("#jsonconfig").val(""+JSON.stringify(jsonconfig));
 
 }
 
 function atualizaTipo(element, index) {
	
	if ($(element).val() == 'selecionavel' || 
		$(element).closest('.novocampo').find('.preto').val() == 'selecionavel') {
		
		element = $(element).closest('.novocampo').find('.preto');
		
		$('#editarTipo'+index.toString()).show();
 
		localStorage.setItem('lastIndiceCampo', index.toString());
		optionCount = 0;
		
		if (camposPersonalizados[index] &&
			camposPersonalizados[index].options) {
			optionsBackup = JSON.parse(JSON.stringify(camposPersonalizados[index].options));
		} else {
			optionsBackup = [];
		}
	
		$(".novooption").remove();
 
		if (camposPersonalizados[index] &&
			camposPersonalizados[index].options &&
			camposPersonalizados[index].options.length > 0) {
 
			camposPersonalizados[index].options.forEach(function(option) {
				
				let novoOption = criaOption(option.nome, optionCount);
				
				camposPersonalizados[index].options[optionCount].indice = optionCount;
				optionCount++;
 
				$('.optionsBody').append($(novoOption));
			
			});
 
		}
 
		$('#modalID').modal('show');
		$(".modal-backdrop").hide();
		
	} else {
		$('#editarTipo'+index.toString()).hide();
	}
 
	for(var i = 0; i < camposPersonalizados.length; i++) {
		if (camposPersonalizados[i] &&
			camposPersonalizados[i].indice === index) {
			camposPersonalizados[index].tipo = $(element).val();
		}
	}
 
	jsonconfig.personalizados = camposPersonalizados;
	//$("#jsonconfig").val(""+JSON.stringify(jsonconfig));
 
 }

 function atualizaVinculo(element, index) {
   
	for(var i = 0; i < camposPersonalizados.length; i++) {
		if (camposPersonalizados[i].indice === index) {
			camposPersonalizados[index].vinculo = $(element).val();
		}
	}
	
	jsonconfig.personalizados = camposPersonalizados;
	//$("#jsonconfig").val(""+JSON.stringify(jsonconfig));
 
 }
 
 
 function atualizaOption(element, index) {
 
	 let indiceCampo = localStorage.getItem('lastIndiceCampo');
	 
	 for(var j = 0; j < camposPersonalizados.length; j++) {
		 if (camposPersonalizados[j].indice === parseInt(indiceCampo)) {
		 
			 for(var i = 0; i < camposPersonalizados[j].options.length; i++) {
				 if (camposPersonalizados[j].options[i].indice === index) {
					 camposPersonalizados[j].options[i].nome = $(element).val();
				 }
			 }
		 }
	 }
 
 }
 
 $("#salvarOption").click(function() {
 
	 jsonconfig.personalizados = camposPersonalizados;
	 //$("#jsonconfig").val(""+JSON.stringify(jsonconfig));
 
	 $('#modalID').modal('hide');
	 
 
	 /*$(".close").click(function() {			
		 $('#cbModal').modal('hide');
	 });*/
		 
	 /*CB.modal({
		 url:"?_modulo=sgarea&_acao=i&_modo=form"
		 ,header:"teste"
	 });*/
 });
 
 $("#cancelarOption").click(function() {
 
	 let indiceCampo = localStorage.getItem('lastIndiceCampo');
 
	 camposPersonalizados[parseInt(indiceCampo)].options = optionsBackup;
 
	 $('#modalID').modal('hide');
 
 });
 
 function atualizaCor() {
	 $("#color").attr("value", $("#color").val());
 }
 
 function novoStatusAlerta(obj){
	 
	  if($(obj).is(":checked")){
			 $statusDisp=$("#statusDisponiveis");
								 
	 $statusDisp.append(
		 skelStatus
			 .replace(/%itr%/g,($("[itr]").length))
			 .replace(/%status%/g,'Assinar" readonly style="background-color:#ddd;"' )
			 .replace(/%icocor%/g,'fa-circle-o verde blink')
			 .replace(/%prioridade%/g,($("[itr]").length)+1)
			 .replace(/%.*%/g,'')
			 .replace(/%token%/g, 'assinar')
			 .replace('class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="removeStatus(this)" title="Excluir"', 'class="fa fa-ban fa-1x cinzaclaro btn-lg ui-droppable"')	
		 );
	  }
 }

function atEventotiporesp(vthis,inideventotiporesp){
     CB.post({			
            objetos:"_x_u_eventotiporesp_ideventotiporesp="+inideventotiporesp+"&_x_u_eventotiporesp_assina="+$(vthis).val()
            ,parcial: true				
    });
}

function getProxes(){
	//a variável $tr vem do contexto superior
	var $proxes = $tr.find(".proxes .iproxstatus");
	var oProxes={};
	if(!$proxes || $proxes.length==0){
		return false;
	}else{
		//Loop nas opções selecionadas para recuperar somente as que estão marcadas
		$proxes.each(function(ipt,opt){
			$opt=$(opt);
			if($(opt).hasClass("selecionado")){
				oProxes[$opt.attr("token")]={};
			}
		});
		return oProxes;
	}
}

function novoStatus(){
	$statusDisp=$("#statusDisponiveis");

	$statusDisp.append(
		skelStatus
			.replace(/%itr%/g,($("[itr]").length))
			.replace(/%status%/g,'x" onkeyup="atualizaToken(this)' )
			.replace(/%icocor%/g,'fa-circle-o cinza blink')
			.replace(/%prioridade%/g,($("[itr]").length)+1)
			.replace(/%.*%/g,'')
			
	);
}

//Estende o método selectColor padrão, para atualizar as cores dos ícones de próximo status, relacionados
$(".colorpalette").on("selectColor",function(e) {
	sToken = $(e.target).attr("token");
	if(sToken && sToken!==""){
		$("i[token="+sToken+"]").css("color",e.color);
	}
});

//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape

$("#statusDisponiveis").sortable({
update:function(e,o){
	$('tr').find('.prioridade').each(function( k, v ) {
	 // alert( "Key: " + k + ", Value: " + v.value );
	v.value= k;
	});
}});

$("#camposBody").sortable({
update:function(e,o){
	$('tr').find('.prioridade').each(function( k, v ) {
	 // alert( "Key: " + k + ", Value: " + v.value );
	v.value= k;
	});
}})
   
function toggledataprazo(inColuna,inRadio){
    if(inColuna=='prazo'){
        var prazo='Y';
    }else{
        var prazo='N';
    }
	if(inColuna && inRadio.checked){
		CB.post({
			objetos: "_x_u_eventotipo_ideventotipo="+$(":input[name=_1_"+CB.acao+"_eventotipo_ideventotipo]").val()+"&_x_u_eventotipo_prazo="+prazo
                });
	}
}

function toggleassinatura(inColuna,inRadio){
    if($("#assinart").is(":checked") === true){
        var btat='Y';
    }else{
        var btat='N';
    }
    if($("#assinarp").is(":checked") === true){
        var btap='Y';
    }else{
        var btap='N';
    }

	CB.post({
		objetos: "_x_u_eventotipo_ideventotipo="+$(":input[name=_1_"+CB.acao+"_eventotipo_ideventotipo]").val()+"&_x_u_eventotipo_assinarp="+btap+"&_x_u_eventotipo_assinart="+btat
	});

}
$('.show-tick').css('width','100%');
$('.show-tick').css('padding','0px');

$('button.btn.dropdown-toggle.btn-default').css('width','170px');

function retiraObj(inid){
	CB.post({
		objetos: {
			"_x_d_eventoobj_ideventoobj":inid
		}
		,parcial: true
	});
}

function desabilitaDuracao(){
	if ($("[name=_1_"+CB.acao+"_evento_diainteiro]").is(":checked") === false) {		
		$("[name=_1_"+CB.acao+"_evento_duracaohms]").removeAttr("disabled");
		$("[name=_1_"+CB.acao+"_prodserv_idtipoprodserv]").removeClass("desabilitado");
	} else {
		$("[name=_1_"+CB.acao+"_evento_duracaohms]").prop("disabled", "disabled");
		$("[name=_1_"+CB.acao+"_evento_duracaohms]").val("");
		$("[name=_1_"+CB.acao+"_prodserv_idtipoprodserv]").removeClass("desabilitado");	
		$("[name=_1_"+CB.acao+"_evento_duracaohms]").attr("cbvalue", "");
	}
}

function fvinculadoc(idevento, idsgdoc)
{
	CB.post({
		objetos: {
			"_x_u_evento_idevento":idevento
			,"_x_u_evento_idsgdoc":idsgdoc
		}
		,parcial: true
	}); 
}  

function AlteraStatusfluxopessoa(inidfluxostatuspessoa, inideventostatus, inocultar, hist=null, status=false, nome=null, voltarStatus=false)
{
	$.ajax({
		type: "get",
		url: "ajax/eventostatus.php?inidfluxostatuspessoa="+inidfluxostatuspessoa+"&inideventostatus="+inideventostatus+"&inocultar="+inocultar+"&historico="+hist+"&voltarStatus="+voltarStatus,
		success: function(data){
			//if(data=='ERROASSTODOS'){
			if(data.indexOf("ERROASSTODOS") >= 1){
				//Informação alterada conforme solicitação do Guilherme - Lidiane (04/06/2020)
				alert('Por favor, todas as pessoas envolvidas devem assinar antes de alterar o Status do Evento.') ;
			}
			
			//if(data=='ERROASSPARCIAL'){
			if(data.indexOf("ERROASSPARCIAL") >= 1){
				alert('Conforme cadastro do evento e necessário assinar documento em anexo') ;
			}
			
			//if(data=='ASSPENDENTE'){
			if(data.indexOf("ASSPENDENTE") >= 1){
				alert('Assinatura do anexo pendente') ;
			}	
			
			//Após Validar se a posição do Botão for ASSINA ou REJEITA e atualizar o carrimbo, retira o botão assinatura. (LTM - 15-07-2020)
			if(data.indexOf("RetiraBotaoAssinar") >= 1){
				$('#btAssina').hide(); 
			}

			//Caso a Pessoa não Assine No Individual e esteja no carrimbo, retornará o erro. Não poderá mudar o status do evento. - Lidiane (04/06/2020)
			//Processo para compras  com obrigatoriedade desta assinatura
			//if(data=='ERROASSINDIVIDUAL'){
			if(data.indexOf("ERROASSINDIVIDUAL") >= 1){		
				alert('Por favor, assine antes de alterar o status do Evento.') ;
			}
			
			//Insere historico de quem voltou o status e para quando.
			if(hist != null){
				var str = 'Voltou o Status de '+nome+' para '+status;
				$.ajax({
					type: "post",
					url: "ajax/eventoresp3.php?vopcao=add&videvento="+$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()+ "&vobs=" + str,
					success: function(data) {
						if(data.error) return alertAtencao(data.error);
					}
				});
			}
			
			CB.post();
			//location.reload();

		},
		error: function(data){
			alert('Erro:<br>'+data); 
		}
	});
}

//Valida qual opção de Participante selecionada e seta na sessão para continuar selecionado no resfresh - 29-01-2020 (Lidiane)      
function showfuncionario(){
	habilitaFuncionario();
	sessionStorage.setItem('tipoParticpante', 'funcionario');
}

function showsgsetor(){
	habilitaSetor();	
	sessionStorage.setItem('tipoParticpante', 'setor');
}   

function habilitaFuncionario(){
	$("#tdsgsetor").hide();
	$("#tdfuncionario").show(); 
	$("#btfunc").hasClass("selecionado");
	$("#btset").removeClass("selecionado");
	$("#pessoavinc").focus();
}

function habilitaSetor(){
	$("#tdsgsetor").show();
	$("#tdfuncionario").hide(); 	
	$("#btset").hasClass("selecionado");
	$("#btfunc").removeClass("selecionado");
	$("#sgsetorvinc").focus();  
}

function criaassinatura(idpessoa,modulo,idmodulo,versao,cassinar,idfluxostatuspessoa, idcarrimbo = null, idfluxostatus = null){
	if(cassinar=='N'){
		//Desmarca a opção de Assinar, apagando o id do carrimbo
		$.ajax({
			type: "get",
			url: "ajax/evento.php?vopcao=retiraassinatura&idobjeto="+idmodulo+"&idpessoa="+idpessoa+"&idcarrimbo="+idcarrimbo,
			success: function(data){
				if(data.error)
				{
					return alertAtencao(data.error);
				}

				location.reload();

			},
			error: function(data){
				alert('Erro:<br>'+data); 
			}
		});	   
	}else{
		if(versao>0){
		 	CB.post({
				objetos: {
					"_x_i_carrimbo_idpessoa": idpessoa,
					"_x_i_carrimbo_tipoobjeto": modulo,
					"_x_i_carrimbo_idobjeto": idmodulo,
					"_x_i_carrimbo_tipoobjetoext": 'idfluxostatus',
					"_x_i_carrimbo_idobjetoext": idfluxostatus,
					"_x_i_carrimbo_versao": versao,
					"_y_u_fluxostatuspessoa_idfluxostatuspessoa": idfluxostatuspessoa,
					"_y_u_fluxostatuspessoa_assinar":'Y' 
					
				},
				parcial: true
			});
		}else{
			 CB.post({
				objetos: {
					"_x_i_carrimbo_idpessoa": idpessoa,
					"_x_i_carrimbo_tipoobjeto": modulo,
					"_x_i_carrimbo_idobjeto": idmodulo,
					"_x_i_carrimbo_tipoobjetoext": 'idfluxostatus',
					"_x_i_carrimbo_idobjetoext": idfluxostatus,
					"_y_u_fluxostatuspessoa_idfluxostatuspessoa": idfluxostatuspessoa,
					"_y_u_fluxostatuspessoa_assinar":'Y'				
				},
				parcial: true
			});
		}
	}
}

function botaoAssinar(inidcarrimbo)
{
	$bteditar = $("#btAssina");
	if($bteditar.length==0){
		CB.novoBotaoUsuario({
			id:"btAssina"
			,rotulo:"Assinar"
			,class:"verde"
			,icone:"fa fa-pencil"
			,onclick:function(){
				CB.post({
					objetos: "_x_u_carrimbo_idcarrimbo="+inidcarrimbo+"&_x_u_carrimbo_status=ATIVO"
					,parcial:true  
					,posPost: function(data, textStatus, jqXHR){
						escondebotao();  
					}
				});
			}
		});
	}
}
