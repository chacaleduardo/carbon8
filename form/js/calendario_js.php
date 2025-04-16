<!-- FullCalendar -->
<!-- <script src="/inc/fullcalendar/main.min.js"></script> -->
<script>
    var jsonEvents = v_calendarioferiados;

    var fullcalendar = '';
    var eventosAux = [];
    var eventoTipoActive = [];
    var eventoTipoConfig = [];

    var strEventoTipoActive = "";

    // function carregaEventos(eventoTipo = '', start, end) {
    //     return new Promise(function(resolve, reject) {
    //         $.ajax({
    //             type: "get",
    //             url: "ajax/carregacal.php?veventotipo=" + eventoTipo + "&start=" + start + "&end=" + end,
    //             success: function(data) {
    //                 if (data.error)
    //                     return alertAtencao(data.error);

    //                 let eventos = JSON.parse(data);

    //                 if (eventos !== undefined) {
    //                     eventosAux = [...eventos];
    //                     resolve(eventos);
    //                 } else {
    //                     resolve("error");
    //                 }
    //             }
    //         });

    //     });
    // }

    function salvaConfig(tiposAtivos) {
        if (!tiposAtivos.length) {
            return localStorage.setItem("eventotipoconfig", JSON.stringify(eventoTipoActive));
        }

        return localStorage.setItem("eventotipoconfig", JSON.stringify(tiposAtivos));
    }

    function altpsq(vtipo = false, vchave = false) {
        $('body').css('cursor', 'wait');
        $('#limpar-filtro').removeClass('disabled');

        let dayStart = $($('#calendar').find("[data-date]")[0]).attr('data-date');
        let dayEnd = $($('#calendar').find("[data-date]")[41]).attr('data-date');

        let mountStart = $($('#calendar').find("[data-month]")[0]).attr('data-month');
        let mountEnd = $($('#calendar').find("[data-month]")[41]).attr('data-month');

        let yearStart = $($('#calendar').find("[data-year]")[0]).attr('data-year');
        let yearEnd = $($('#calendar').find("[data-year]")[41]).attr('data-year');

        let start = `${yearStart}-${mountStart}-${dayStart}`;
        let end = `${yearEnd}-${mountEnd}-${dayEnd}`;


        let classe = "";
        let tipos = {
            bttag: 'tag',
            btevento: 'eventos',
            btop: 'op',
            btsala: 'sala',
            btequip: 'equipamento',
            btveiculo: 'veiculo',
            btprateleira: 'prateleira'
        };

        if (['eventos', 'tag', 'op'].includes(vtipo)) {
            classe = "ativo aberto";
        } else {
            classe = "active";
        }

        if (vchave) {
            vchave += "";

            let JQtipoSelecionado = $(`#${vtipo}-${vchave}`);

            if (!JQtipoSelecionado.hasClass(classe)) {
                JQtipoSelecionado.addClass(classe);
                //retirar todos ao marcar tag

                JQtipoSelecionado.addClass(`${classe}`);

                if (vchave == 'bttag' || vchave == 'btevento' || vchave == 'btop') {
                    if (vchave == 'bttag') {
                        let JQlista = $(".lista-tag, .lista-tag > div");

                        if (JQlista.find('.ativo.aberto')) {
                            if (JQlista.find('.ativo.aberto').parent().next().hasClass('hidden')) {
                                JQlista.find('.ativo.aberto').parent().next().removeClass('hidden')
                            }
                        }

                        JQlista.removeClass("hidden");
                    } else {
                        $(`.lista-${tipos[vchave]}`).removeClass("hidden");
                    }
                } else {
                    $(`.tag-${tipos[vchave]} .lista-item`).removeClass("hidden");
                }

                eventoTipoActive = eventoTipoActive.filter(element => element);

                if (!eventoTipoActive.includes(`'${vtipo}-${vchave}'`))
                    eventoTipoActive.push(`'${vtipo}-${vchave}'`);

                strEventoTipoActive = eventoTipoActive.join(',');
            } else { //desmarcar
                JQtipoSelecionado.removeClass(`${classe}`);

                // Verificar se existe filhos e remover
                let JQtipoFilhos = $(`.${vchave}`);

                if (vtipo == 'eventos')
                    JQtipoFilhos = $(`.evento`);

                if (vtipo == 'op')
                    JQtipoFilhos = $(`.listaop`);

                JQtipoFilhos.removeClass(`${classe} active`)

                for (var i = 0; i < eventoTipoActive.length; i++) {
                    if (eventoTipoActive[i] == `'${vtipo}-${vchave}'` || eventoTipoActive[i].search(`${JQtipoFilhos.data('tipojson')}-`) !== -1)
                        eventoTipoActive[i] = false;
                }

                // retirar todos aos desmarcar lista tag
                if (vchave == 'bttag') {
                    $("#eventobttag").removeClass("aberto");
                    $(".lista-tag").addClass("hidden");
                } else if (vchave == 'btevento') {
                    $('#eventobtevento').removeClass('aberto');
                    $(".lista-eventos").addClass("hidden");

                } else if (vchave == 'btop') {
                    $('.lista-op').addClass('hidden');
                } else if (vchave == 'btequip') {
                    $("#eventobtequip").removeClass(`${classe}`);
                    $(".tag-equipamento .lista-item").addClass("hidden");
                } else if (vchave == 'btsala') {
                    $("#eventobtsala").removeClass(`${classe}`);
                    $(".tag-sala .lista-item").addClass("hidden");
                } else if (vchave == 'btveiculo') {
                    $("#eventobtveiculo").removeClass(`${classe}`);
                    $(".tag-veiculo .lista-item").addClass("hidden");

                } else if (vchave == 'btprateleira') {
                    $("#eventobtprateleira").removeClass(`${classe}`);
                    $(".tag-prateleira .lista-item").addClass("hidden");
                }

                eventoTipoActive = eventoTipoActive.filter(element => element);
                strEventoTipoActive = eventoTipoActive.join(',');
            }

            salvaConfig(eventoTipoActive);
        }

        if(strEventoTipoActive){

            carregaEventos(strEventoTipoActive, start, end).then((eventos) => {
                if (eventos !== "error") {
                    removerEventosAtuais();
                    renderEventos(eventos);
                }
    
            });
        }

        if (!eventoTipoActive.length) {
            $('#limpar-filtro').addClass('disabled');
        }

        $('body').css('cursor', '');;
    }

    function removerEventosAtuais() {
        // fullcalendar.removeAllEvents();
    }

    function criaEvento(vchave) {
        $('#modalEventoTipo').modal('hide');
        vdataptbr = $("#dateParaSislaudo").val();
        // formata data para o formato do brasil
        vdataptbr = vdataptbr.split("-").reverse().join("/");

        CB.modal({
            url: "?_modulo=evento&_acao=i&inicio=" + vdataptbr + "&fim=" + vdataptbr + "&eventotipo=" + vchave + "&calendario=true&_modo=form&datacalendario=true",
            header: "Evento",
            callback: function(data, textStatus, jqXHR) {
                let lastInsertId = jqXHR.getResponseHeader("X-CB-PKID");

                getEvento(lastInsertId);
            },
        });
    };

    function exibirTodos(exibirTodos) {
        CB.loadUrl({
            urldestino: CB.urlDestino + "?_modulo=calendario&exibirtodos=" + exibirTodos
        });
    }

    function getEvento(idEvento) {
        var token = Cookies.get('jwt') || localStorage.getItem("jwt") || "";
        var self = this;

        fetch('ajax/evento.php?vopcao=getevento&videvento=' + idEvento, {
            headers: {
                "authorization": token
            }
        }).then(function(response) {
            return response.json();
        }).then(function(data) {
            if (data.error) {
                return alertAtencao(data.error);
            }

            if (data && data.length > 0) {
                self.eventosAux.push({
                    "id": data[0].idevento,
                    "title": data[0].evento ?? 'Novo evento',
                    "start": data[0].inicio + " " + data[0].iniciohms,
                    "end": data[0].fim + " " + data[0].fimhms,
                    "color": data[0].cor,
                    "allDay": data[0].diainteiro,
                    "url": '?_modulo=evento&_acao=u&idevento=' + data[0].idevento
                });

                removerEventosAtuais();
                // fullcalendar.addEventSource(self.eventosAux);
                // fullcalendar.addEventSource(jsonEvents);
            }

        });
    }


    function atualizaTipoEvento() {
        var eventoConfig = false,
            idDoElemento,
            JQelemento;

        if (localStorage.getItem("eventotipoconfig")) {
            eventoConfig = JSON.parse(localStorage.getItem("eventotipoconfig"));
        }

        if (eventoConfig && eventoConfig != "") {

            for (let i = 0; i < eventoConfig.length; i++) {
                if (eventoConfig[i].search('-') === -1) continue;

                idDoElemento = eventoConfig[i].replaceAll(["'"], '');
                JQelemento = $(`#${idDoElemento}`);

                if (JQelemento.prop('tagName') == 'LI') {
                    JQelemento.addClass("active");
                } else {
                    JQelemento.addClass("ativo aberto");
                }

                var chave = JQelemento.attr("id");

                eventoTipoActive.push(eventoConfig[i]);
            }

        } else {
            eventoConfig = [];
        }

        if (eventoTipoActive && eventoTipoActive != "") {

            strEventoTipoActive = eventoTipoActive.join(',');
        }

        // fullcalendar = new FullCalendar.Calendar($('#calendar').get(0), {
        //     locale: 'pt-br',
        //     headerToolbar: {
        //         left: 'prev,next,today',
        //         center: 'title',
        //         right: 'dayGridMonth,timeGridWeek,timeGridDay'
        //     },
        //     eventDisplay: 'block',
        //     initialView: 'dayGridMonth',
        //     selectable: true,
        //     selectMirror: true,
        //     displayEventEnd: {
        //         dayGridMonth: false,
        //         dayGridWeek: true,
        //         default: true
        //     },
        //     allDayContent: 'Dia todo',
        //     buttonText: {
        //         'today': 'Hoje',
        //         'month': 'Mês',
        //         'week': 'Semana',
        //         'day': 'Dia'
        //     },
        //     eventSources: [{}],
        //     timeZone: 'UTC',
        //     eventContent: event => {
        //         return {
        //             html: `<div class="fc-event-main-frame">
        //                         <div class="fc-event-time">${event.timeText}</div>
        //                         <div class="fc-event-title-container">
        //                             <div class="fc-event-title fc-sticky">
        //                                 ${event.event.title}            
        //                             </div>
        //                         </div>
        //                     </div>`
        //         };
        //     },
        //     eventTimeFormat: function(date) {
        //         date.start.marker = new Date(`${date.start.year}-${date.start.month + 1}-${date.start.day} ${date.start.hour}:${date.start.minute}:${date.start.second}`);

        //         let horarioInicio = `${Intl.DateTimeFormat('pt-br', {hour: '2-digit', minute:'2-digit'}).format(date.start.marker).split(':')[0]}:${Intl.DateTimeFormat('pt-br', {hour: '2-digit', minute:'2-digit'}).format(date.start.marker).split(':')[1]}H`,
        //             horarioFim = '';


        //         if (date.end) {
        //             date.end.marker = new Date(`${date.end.year}-${date.end.month + 1}-${date.end.day} ${date.end.hour}:${date.end.minute}:${date.end.second}`);
        //             horarioFim = ` - ${Intl.DateTimeFormat('pt-br', {hour: '2-digit', minute:'2-digit'}).format(date.end.marker).split(':')[0]}:${Intl.DateTimeFormat('pt-br', {hour: '2-digit', minute:'2-digit'}).format(date.end.marker).split(':')[1]}H`
        //         }

        //         return `${horarioInicio}${horarioFim}`;
        //     },
        //     views: {
        //         timeGridWeek: {
        //             titleRangeSeparator: ' \u2013 '
        //         }
        //     },
        //     editable: false,
        //     select: function(event) {
        //         let dataInicioStr = dma(event.startStr);
        //         let dataClick = dataInicioStr;

        //         let diaClick = dataClick.split("/")[0];
        //         let mesClick = dataClick.split("/")[1];
        //         let anoClick = dataClick.split("/")[2];

        //         dataClick = anoClick + ("0" + mesClick).slice(-2) + ("0" + diaClick).slice(-2);

        //         let anoAtual = new Date().getFullYear();
        //         let mesAtual = new Date().getMonth().toString().padStart(2, "0") + 1;
        //         let diaAtual = new Date().getDate().toString().padStart(2, "0");

        //         if (mesAtual < 10) {
        //             mesAtual = '0' + mesAtual;
        //         }
        //         let dataAtual = '' + anoAtual + mesAtual + diaAtual;

        //         if (dataClick < dataAtual) {
        //             console.log("Não é possível criar evento retroativo" + dataClick + "<" + dataAtual);
        //         } else {
        //             if (this.name == "timeGridDay") {
        //                 vdataptbr = dataInicioStr;
        //                 vdatafimptbr = dataInicioStr;
        //                 vdataptbrdma = dataInicioStr;
        //             } else {
        //                 vdataptbr = dataInicioStr;
        //                 vdatafimptbr = dataInicioStr;
        //                 vdataptbrdma = dataInicioStr;
        //             }

        //             if (eventoTipoActive.length == 1) {
        //                 var ideventotipo = eventoTipoActive[0].replaceAll("'", '');
        //             } else {
        //                 var ideventotipo = "";
        //             }

        //             $('#modalEventoTipo').modal('show');
        //             $(".modal-backdrop").hide();
        //         }
        //     },
        //     eventClassNames: event => {
        //         if (event.event.extendedProps.feriado) return ['feriado'];
        //     },
        //     eventClick: function(event) {
        //         event.jsEvent.preventDefault();
        //         CB.modal({
        //             url: `${event.event.url}&_modo=form`,
        //             header: "Evento"
        //         });
        //     }
        // });
    }

    $('.fechar-lista').on('click', function() {
        // Abrir
        if ($('.barra-filtro').hasClass('hidden')) {
            $('.header i').css('transform', 'rotate(0)');
            $('.coluna-calendario').removeClass('col-md-12');
            $('.coluna-calendario').addClass('col-md-9');
            // fullcalendar.updateSize();

            return $('.barra-filtro').removeClass('hidden');
        }

        $('.header i').css('transform', 'rotate(180deg)');
        $('.coluna-calendario').removeClass('col-md-9');
        $('.coluna-calendario').addClass('col-md-12');
        // fullcalendar.updateSize();

        return $('.barra-filtro').addClass('hidden');
    });

    $(document).ready(function() {
        atualizaTipoEvento();
        altpsq();

        $('#calendar > div.fc-toolbar.fc-header-toolbar > div.fc-left').click(function() {
            altpsq(0);
        });

        var initData = $('#calendar').context.lastModified.substr(0, 2);
        var mesCalendario = initData.substr(0, 2);

        if (!eventoTipoActive.length) {
            $('#limpar-filtro').addClass('disabled');
        }

        if ($("#tag-bttag").hasClass('ativo')) {
            $(".lista-tag > div").removeClass("hidden");

            if ($("#tag-btequip").hasClass('ativo')) {
                $('.tag-equipamento, .tag-equipamento .lista-item').removeClass('hidden');
            }

            if ($("#tag-btsala").hasClass('ativo')) {
                $('.tag-sala .lista-item').removeClass('hidden');
            }

            if ($("#tag-btveiculo").hasClass('ativo')) {
                $('.tag-veiculo .lista-item').removeClass('hidden');
            }

            if ($("#tag-btprateleira").hasClass('ativo')) {
                $('.tag-prateleira .lista-item').removeClass('hidden');
            }

        }

        if ($("#op-btop").hasClass('ativo')) {
            $('.lista-op').removeClass('hidden');
        }

        if ($("#eventos-btevento").hasClass('ativo')) {
            $(".lista-eventos").removeClass("hidden");
        }
    });

    $('#limpar-filtro').on('click', function() {
        eventoTipoActive = [];
        $(this).addClass('disabled');

        $('.active').removeClass('active');
        $('.ativo').removeClass('ativo');
        $('.aberto').removeClass('aberto');
        $('.lista-item').addClass('hidden');
        $('.lista-tag').addClass('hidden');
        $('.lista-op').addClass('hidden');

        salvaConfig(eventoTipoActive);
        removerEventosAtuais();
        // fullcalendar.render();
    });

    let timeoutId;

    $('.coluna-calendario').on('click', '.fc-button-group .fc-prev-button,.fc-button-group .fc-next-button', function() {
        clearInterval(timeoutId)

        timeoutId = setTimeout(() => {
            altpsq(0);
        }, 400)
    });
</script> 