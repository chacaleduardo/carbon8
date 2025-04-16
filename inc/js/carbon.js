//Framework Carbon
function carbon() {

    return {

        logado: false,
        ajaxPostAtivo: false,
        autoLoadUrl: "",
        locationSearch: window.location.search.split("?")[1],
        modulo: undefined,
        canal: "browser",
        acao: "",
        jsonModulo: {},
        eventos: new Map(),
        onReady: undefined,
        urlDestino: undefined,
        pesquisaAjaxAtivo: false,
        pesquisaAjaxStringAtual: "",
        pesquisaAjaxPagina: 1,
        pesquisaAjaxTotalPaginas: 0,
        limparResultados: false,
        jsonAutoFiltro: false,
        lastInsertId: null,
        arrCores: ["#ff6600"
            , "#fcd202"
            , "#b0de09"
            , "#0d8ecf"
            , "#cc0000"
            , "#999999"
            , "#cd0d74"
            , "#0000cc"
            , "#00cc00"
            , "#990000"
            , "#a04000"
            , "#9f8401"
            , "#7a9a06"
            , "#0a536e"
            , "#505050"
            , "#680a3e"
            , "#00004e"
            , "#008400"
            , "#f5f5f5"],
        fAcoesResultados: {
            html: $("<ul id='cbAcoesResultados' class='pagination'></ul>"),
            item: $("<li><i id='' class='' title='' onclick=''></i></li>"),
            init: function (inPar) {
                $that = this;
                if (inPar) {
                    $.each(inPar, function (i, acao) {
                        $that.html.append("<li><i id='moduloacaoid" + acao.moduloacaoid + "' class='" + acao.class + "' title='" + acao.rotulo + "' onclick='" + acao.onclick + "'></i></li>");
                    })
                }
            }
        },
        /*
         * Inicializa as variáveis e objetos padrão
         */
        init: function () {
            CB.oBody = $("body");
            CB.oMenuSuperior = $("#cbMenuSuperior");
            CB.oMenuLateral = $('#sidebar');
            CB.oModalSnippetAcao = $('#modal-snippet-action');
            CB.oContainer = $("#cbContainer");
            CB.oModuloForm = $("#cbModuloForm");
            CB.oModuloHeader = $("#cbModuloHeader");
            CB.oModuloHeaderBg = $("#cbModuloHeaderBg");
            CB.oModuloBreadcrumb = $("#cbModuloBreadcrumb");
            CB.oModuloPesquisa = $("#cbModuloPesquisa");
            CB.oResultadosInfo = $("<span id='cbResultadosInfo'></span>");
            CB.ocbLimparFiltros = $("#cbLimparFiltros");
            CB.oResultadosLimparOrderBy = $("<a class='right' id='cbResultadosLimparOrderBy' onclick='CB.limparOrderBy()'>Limpar ordenação</a>");
            CB.oIconePesquisando = $("#cbIconePesquisando");
            CB.oModuloResultados = $("#cbModuloResultados");
            CB.oBtRep = CB.oModuloHeader.find("#cbRep");
            CB.oBtNovo = CB.oModuloHeader.find("#cbNovo");
            CB.oBtSalvar = CB.oModuloHeader.find("#cbSalvar");
            CB.oBtCompartilharItem = $("#cbCompartilharItem");
            CB.oBtCompartilharAlerta = $("#cbCompartilharAlerta");
            CB.oBtRestaurar = CB.oModuloHeader.find("#cbRestaurar");
            CB.oModuloIcone = CB.oModuloHeader.find("#cbModuloIcone");
            CB.oPainelNavegacao = $(".painelNavegacao");
            CB.oNavRegAtual = $(".painelNavegacao .navRegAtual");
            CB.oBtNavRegAnt = $(".painelNavegacao .gNavRegAnt");
            CB.oBtNavRegProx = $(".painelNavegacao .gNavRegProx");
            CB.oFiltroRapido = $("#cbFiltroRapido");
            CB.oTextoPesquisa = $("#cbModuloPesquisa #cbTextoPesquisa");
            CB.oDaterange = $("#cbDaterange");
            CB.oDaterangeTexto = $("#cbDaterangeTexto");
            CB.oDaterangeCol = $("#cbDaterangeCol");
            CB.jDateRangeLocale = {
                "format": "DD/MM/YYYY",
                "separator": " - ",
                "applyLabel": "Ok",
                "cancelLabel": "Limpar",
                "fromLabel": "De",
                "toLabel": "Até",
                "customRangeLabel": "Outro intervalo",
                "daysOfWeek": ["Do", "Se", "Te", "Qu", "Qi", "Se", "Sa"],
                "monthNames": ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"]
            };
            CB.jDatetimeRangeLocale = {
                "format": "DD/MM/YYYY h:mm:ss",
                "separator": " - ",
                "applyLabel": "Ok",
                "cancelLabel": "Limpar",
                "fromLabel": "De",
                "toLabel": "Até",
                "customRangeLabel": "Outro intervalo",
                "daysOfWeek": ["Do", "Se", "Te", "Qu", "Qi", "Se", "Sa"],
                "monthNames": ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"]
            };
            CB.oCarregando = $("#cbCarregando");
            CB.oTitle = $("#cbTitle");
            CB.acao = getUrlParameter("_acao");
            CB.oModal = $("#cbModal");
            CB.oModalCarregando = $("#cbModalCarregando");
            CB.gToken = "N";
            CB.modalCallback = undefined;
            CB.compartilharCallback = undefined;

            //Caso o usuário não esteja logado, inicializar tela de login, e caso esteja logado, mas não exista módulo informado, informar vazio
            CB.modulo = (CB.logado) ? this.modulo || "" : "_login";

            CB.oPanelLegenda = $("#cbPanelLegenda");
            CB.oPanelLegendaBotaoFlutuante = $("#cbPanelBotaoFlutuante");

            //verifica a existencia de token do sislaudo
            CB.sToken = getUrlParameter('_token') == '' ? getUrlParameter('token') : getUrlParameter('_token');
            CB.sToken = (CB.sToken !== '') ? '&_token=' + CB.sToken : '';

            /*
             * Coloca o formulario em modo autoload. Executa primeiro a chamada do formulario,
             * e a pesquisa irá ser carregada em modo hidden, devido à adição classes css !important
             */
            if (CB.autoLoadUrl == "Y") {
                $("body").addClass("minimizado").addClass("autoloadurl");
            }

            /*
             * Pressionamento de teclas no campo de pesquisa
             */
            CB.oTextoPesquisa.on("keyup", function (e) {
                CB.limparResultados = true;
                CB.resetVarPesquisa();
                CB.pesquisaAjaxStringAtual = this.value;
                var keyCode = e.keyCode || e.which;
                //Executa Pesquisa com Enter
                if (keyCode == 13) {
                    CB.pesquisar({ resetVarPesquisa: true });
                }
                //Reinicializa pesquisa com Backspace
                if (keyCode == 8 && this.value.length === 0) {

                }
            });

            CB.oMenuSuperior.on("mouseenter", function (e) {
                $("body").removeClass("minimizado");
            });

            /*
             * Calendário geral no campo de pesquisa para _fds
             */
            CB.oDaterange.daterangepicker({
                "showDropdowns": true,
                "minDate": moment("01012006", "DDMMYYYY"),
                "locale": CB.jDateRangeLocale,
                ranges: {
                    'Hoje': [moment(), moment()],
                    'Ontem': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Amanhã': [moment().add(1, 'days'), moment().add(1, 'days')],
                    'Esta Semana': [moment().subtract(new Date().getDay(), 'days'), moment().endOf('week')],
                    'Últimos 7 dias': [moment().subtract(6, 'days'), moment()],
                    'Próximos 7 dias': [moment(), moment().add(6, 'days')],
                    'Últimos 30 dias': [moment().subtract(29, 'days'), moment()],
                    'Próximos 30 dias': [moment(), moment().add(29, 'days')],
                    'Este mês': [moment().startOf('month'), moment().endOf('month')],
                    'Mês passado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Próximo mês': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')],
                    //'Últimos 365 dias': [moment().subtract(365, 'days'), moment()],
                    'Este Ano': [moment().startOf('year'), moment().endOf('year')],
                    'Ano passado': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
                    'Próximo Ano': [moment().add(1, 'year').startOf('year'), moment().add(1, 'year').endOf('year')]
                },
                "opens": "left"

            }).on('cancel.daterangepicker', function (ev, picker) {
                CB.oDaterange.val('').attr("cbdata", "").addClass("cinzaclaro");
                CB.oDaterangeTexto.html("");

                CB.oDaterange.find("#cbCloseDaterange").off('click').addClass('hide');

                CB.limparResultados = true;
                CB.resetVarPesquisa();
            }).on('apply.daterangepicker', function (ev, picker) {
                CB.setIntervaloDataPesquisa(picker.startDate, picker.endDate);
                CB.limparResultados = true;
                CB.resetVarPesquisa();

            });
        },

        /*
         * Ajusta o calendario da caixa de pesquisa 
         */
        setIntervaloDataPesquisa: function (inMomentIni, inMomentFim, inRotulo) {

            if (inRotulo) {
                CB.oDaterangeTexto.html(inRotulo);
            } else {
                CB.oDaterangeTexto.html(inMomentIni.format("DD/MM/YY") + ' ' + inMomentFim.format("DD/MM/YY"));
            }

            //if(CB.oDaterange.data('daterangepicker')){

            CB.oDaterange.removeClass("cinzaclaro").attr("cbdata", inMomentIni.format("DD/MM/YYYY") + '-' + inMomentFim.format("DD/MM/YYYY"));
            CB.oDaterange.data('daterangepicker').setStartDate(inMomentIni);
            CB.oDaterange.data('daterangepicker').setEndDate(inMomentFim);

            CB.oDaterange.find("#cbCloseDaterange").on('click', () => {
                CB.oDaterange.data('daterangepicker').oldStartDate = inMomentIni;
                CB.oDaterange.data('daterangepicker').oldEndDate = inMomentFim;
                CB.oDaterange.data('daterangepicker').clickCancel();
            })
                .removeClass('hide');

            //}
        },

        //funcao para criar um novo evento do tipo selecionado
        criaEvento: function (vchave, modulo, objeto, idobjeto, idempresa, label) {
            if(confirm(`Você realmente quer criar o evento ${label}`)){
                let curl = '';
                if (objeto === 'undefined') {
                    objeto = false;
                } else {

                    if (idobjeto === 'undefined') {
                        //  alert(idobjeto);
                        idobjeto = false;
                    } else {

                        curl = '&modulo=' + objeto + '&idmodulo=' + idobjeto;
                        //alert(curl);
                    }
                }

                let dataAtual = new Date();
                let dataAtualFormat = new Intl.DateTimeFormat('pt-BR').format(dataAtual);

                $('#modalEventoTipo').modal('hide');

                if (modulo == "true") {
                    modulo = "&modulo=true";
                } else {
                    modulo = "";
                }
                //      alert(idobjeto + objeto + curl);
                CB.modal({
                    url: " ?_modulo=evento&_acao=i&inicio=" + dataAtualFormat
                        + "&fim=" + dataAtualFormat + "&dataclick=" + dataAtualFormat
                        + modulo + "&eventotipo=" + vchave + "&calendario=true" + curl,
                    //_modo=form
                    header: "Evento",
                    callback: function retorno(data, status, jqXHR) {

                        if (CB.compartilharCallback) {
                            var lastInsertId = jqXHR.getResponseHeader('x-cb-pkid');
                            CB.compartilharCallback(lastInsertId);
                        }

                    },
                });
            }
        },

        // GVT - 11/12/2020
        /*
            **************************************************************************************************************************************************************
                FUNÇÃO CB.on(label, fn): Associa uma função fn a um label arbitrário.
        
                    - label:    String      - Nome da função a ser executada pelo evento CB.trigger     (Obrigatóriamente o parâmetro deve ser uma String).
                    - fn:       Function    - Implemetação da função que será executada em CB.trigger   (Obrigatóriamente o parâmetro deve ser uma Function).

                OBS:
                    - É possível associar duas ou mais funções ao mesmo label, portanto ao chamar CB.trigger(), todas as funções associadas ao label serão executadas.

            **************************************************************************************************************************************************************

                EXEMPLOS DE IMPLEMENTAÇÃO:
            
                CB.on('minhaFuncao', function(data){    
                    alert('Entrei na minhaFuncao');         
                    console.log(data); // <= Saída: 'Hello minhaFuncao'         
                }); 

                CB.trigger('minhaFuncao','Hello minhaFuncao');  // Executa a função

                CB.on('minhaOutraFuncao', function(data){
                    alert('Entrei na minhaOutraFuncao');
                    data(); // <=  Saída: 'Também posso passar funções';
                });

                CB.trigger('minhaOutraFuncao', () => console.log('Também posso passar funções')); // Executa a função

                CB.off('minhaFuncao');      // Exclui as função
                CB.off('minhaOutraFuncao'); // Exclui as função

            **************************************************************************************************************************************************************
        */
        on: function (label, fn) {

            if ((fn && typeof fn === 'function') && (label && typeof label === 'string')) {

                var events = CB.eventos;

                if (events.has(label)) { // Verifica a existência do label passado como parâmento na lista de eventos do Carbon.

                    const oldEvents = events.get(label); // Recupera lista de funções que possuem o respectivo label como chave.

                    let exists = false;

                    for (let f of oldEvents) {
                        if (f) {
                            if (f.toString() === fn.toString()) {
                                exists = true;
                                break;
                            }
                        }
                    }

                    if (!exists) {
                        events.set(label, [...oldEvents, fn]) // Set uma nova lista de funções para o label, mesclando com a lista antiga.
                        return oldEvents.length;
                    } else {
                        return false;
                    }

                } else {

                    events.set(label, [fn]); // Inicia uma lista de funções para o respectivo label.

                    return 0;
                }

            } else {

                console.error("Function .on: Verifique os parâmetros da função");
                return false;
            }

        },

        // GVT - 11/12/2020
        /*
            **************************************************************************************************************************************************************
                FUNÇÃO CB.trigger(label [, data]): Executa todas as funções associadas ao label caso existam, passando dados (data) como opcional.
        
                    - label:    String      - Nome da função a ser executada (Obrigatóriamente o parâmetro deve ser uma String).
                    - data:     *           - Qualquer tipo de dado que será passado como parâmetro para a função associada ao label.

            **************************************************************************************************************************************************************
        */
        trigger: function (label, ...data) {

            if (label && typeof label === 'string') {

                var events = CB.eventos;

                const listeners = events.get(label); // Recupera lista de funções que possuem o respectivo label como chave.

                if (Array.isArray(listeners) && listeners.length) { // Verifica se listeners é um Array e se o label existe neste Array .

                    returnList = [];
                    listeners.forEach(event => {
                        if (event) {
                            returnList.push(event(...data));
                        }
                    }); // Executa todas as funções associadas ao respectivo label.

                    return returnList;
                }

                return [];
            } else {

                console.error("Function .trigger: Verifique os parâmetros da função");
                return [];
            }

        },

        // GVT - 11/12/2020
        /*
            **************************************************************************************************************************************************************
                FUNÇÃO CB.off(label): Exclui todas as funções associadas ao label caso existam.
        
                    - label:    String      - Nome da função a ser excluída (Obrigatóriamente o parâmetro deve ser uma String).

            **************************************************************************************************************************************************************
        */
        off: function (label, index) {

            if (label && typeof label === 'string') {

                var events = CB.eventos;

                if (index && typeof index == "number") {
                    let allEvents = events.get(label);
                    delete allEvents[index];
                    events.set(label, allEvents);
                } else {
                    events.delete(label); // Exclui as funções com o respectivo label.
                }

            } else {

                console.error("Function .off: Verifique o parâmetro da função");

            }
        },

        //Monta e abre um modal para selecionar o tipo de evento que a pessoa deseja compartilhar o módulo
        compartilharAlerta: function (opcaoFiltro, callback, objeto, idobjeto) {

            CB.compartilharCallback = undefined;

            //Valores default para objeto e idobjeto - LTM (09/03/2021) 
            idobjeto = idobjeto || getUrlParameter(CB.jsonModulo.pk);
            if (idobjeto) {
                objeto = objeto || CB.jsonModulo.modulo;
            }

            var novoModal = `<div class='modal-body'>
                                <div class='row m-0' id='optionsTipos'></div>
                            </div>`;

            var strCabecalho = "<label class='fa fa-share-alt'></label>&nbsp;&nbsp;Compartilhar Evento:";

            if (callback) {
                if (typeof callback === 'function') {
                    CB.compartilharCallback = callback;
                }
            }

            $.ajax({
                type: "get",
                url: "ajax/eventotipo.php?vopcao=" + opcaoFiltro,
                data: {
                    vobjeto: objeto,
                    vidobjeto: idobjeto
                },
                success: function (data) {

                    let response = JSON.parse(data);
                    let len = response.length;
                    let modulo;

                    if (opcaoFiltro == "carregaTipos") {
                        modulo = "true";
                    } else {
                        modulo = "false";
                    }

                    if (response[0]["objeto"] && response[0]["idobjeto"]) {
                        objeto = response[0]["objeto"];
                        idobjeto = response[0]["idobjeto"];
                    }

                    if (objeto != undefined || idobjeto != undefined) {
                        //  sLink = '?_modulo='+objeto+'&_acao=u&id'+objeto+'='+idobjeto;
                    }


                    for (let i = 0; i < len; i++) {
                        /*maf280619: incompatível com ie11
                        let eventoTipo = `  <span style='background-color: #337ab7; margin-top: 10px;' class='list-group-item btn btn-light'>
                                                <a class='selectTipo pointer' id='eventoTipo`+response[i]["value"]+`' 
                                                    style='color: #FFF; font-size: 16px; text-align: center; width: 100%; padding: 5px'; 
                                                    onclick='CB.criaEvento("`+response[i]["value"]+`","`+modulo+`")';>`+response[i]["label"]+`
                                                </a>
                                            </span>`
                        */
                        var ttl = (!response[i]["eventotitle"]) ? "" : `title ='${response[i]["eventotitle"]}'`;
                        var eventoTipo = `<span  ${ttl} style='background-color: #337ab7; margin-top: 10px;border:4px solid #fff;padding:8px;border-radius:4px;' class='btn btn-light col-xs-6 col-md-4'> 
                                            <a class='selectTipo pointer' id='eventoTipo ${response[i]["value"]}' style='color: #FFF; font-size: 10px; text-transform:uppercase; text-align: center; width: 100%;'; onclick='CB.criaEvento(\"${response[i]["value"]}\",\"${modulo}\",\"${objeto}\",\"${idobjeto}\",\"${response[i]["idempresa"]}\", \"${response[i]["label"]}\")';>${response[i]["label"]}</a> 
                                        </span>`;
                        $("#optionsTipos").append(eventoTipo);
                    }
                },
                error: function (objxml) {
                    document.body.style.cursor = "default";
                    alert('Erro: ' + objxml.status);
                }
            });

            $("#cbModalTitulo").html(strCabecalho);

            //Mostra o popup
            $('#cbModal #cbModalCorpo').find("*").remove();

            $('#cbModal #cbModalCorpo')
                .append(novoModal)
                .append("<hr><hr><hr>");

            $('#cbModal').show();

            $('#cbModal').addClass('cinquenta').modal();

            var sLink = window.location.search;


            // Prepara a descrição para o hyperlink
            var sADesc = removerParametroGet("_modulo", sLink);
            sADesc = removerParametroGet("_acao", sADesc);
            sADesc = sADesc.replace(/^\?/, "");
            sADesc = (CB.jsonModulo.rotulomenu || document.title || "") + ": " + sADesc;

            sADesc = `<a href="${sLink}" target="_blank" class="pointer"><i class="fa fa-paperclip">&nbsp;</i>${sADesc}</a><p></p>`;
            console.log(sLink);
            $("#chatArqPopup").html(sLink);
            $("#chatArqPopupNome").html(sADesc);
        },

        /*
         * Reinicializa o módulo de pesquisa, resetando variáveis e painel de resultados
         */
        resetModPesquisa: function () {

            CB.resetVarPesquisa();
            CB.resetDadosPesquisa();

            $.ajax({
                type: 'get',
                cache: false,
                url: "form/_moduloconf.php",
                data: "_modulo=" + CB.modulo + "&_userPref=resetFts",
                success: function (data) {
                    console.info("resetModPesquisa: Reset de preferências executado incondicionalmente. Verificar servidor.");
                }
            });
        },

        /*
         * Reinicializa variáveis para nova pesquisa
         */
        resetVarPesquisa: function () {
            CB.pesquisaAjaxAtivo = false;
            CB.pesquisaAjaxPagina = 1;
            CB.pesquisaAjaxTotalPaginas = 0;
        },
        /*
         * Limpar da tela os dados de pesquisa existentes 
         */
        resetDadosPesquisa: function () {
            console.info("resetDadosPesquisa: Pesquisa reinicializada");
            CB.oModuloResultados.addClass("hidden").html("");
            //CB.oResultadosInfo.html("");
            CB.limparResultados = false;
        },
        /*
         * Login na aplicação
         */
        login: function () {
            this.post();
        },

        /*
         * Recupera os parâmetros de configuração do módulo e os campos de filtro
         */
        inicializaModulo: function (opt) {

            opt = opt || {};

            CB.oCarregando.show();
            const acaoAtual = CB.acao ? `&_acao=${CB.acao}` : '';

            $.ajax({
                type: 'get',
                cache: false,
                url: "form/_moduloconf.php",
                data: `_modulo=${CB.modulo}${CB.sToken}${acaoAtual}`,
                beforeSend: function () {
                    //reset
                },
                success: function (data, textStatus, jqXHR) {
                    //Valida json contendo os campos de filtro
                    var jsonMod = jsonStr2Object(data);
                    if (!jsonMod) {
                        alertErro(data);
                    } else {
                        //Armazena o json completo
                        CB.jsonModulo = jsonMod;

                        //Inicializa opções
                        CB.jsonModulo.jsonpreferencias._filtrosrapidos = CB.jsonModulo.jsonpreferencias._filtrosrapidos || {};

                        //Inicializa Variaveis Modulo
                        CB.modulo = jsonMod.modulo;
                        CB.urlDestino = jsonMod.urldestino;

                        if (jqXHR.getResponseHeader('cb-canal')) {
                            CB.canal = jqXHR.getResponseHeader('cb-canal');
                        } else if (getUrlParameter('cb-canal')) {
                            CB.canal = getUrlParameter('cb-canal');
                        } else {
                            CB.canal = 'browser';
                        }

                        console.info(`CB.canal: getResponseHeader: ${jqXHR.getResponseHeader('cb-canal')} - getUrlParameter: ${getUrlParameter('cb-canal')}`);

                        //Ajusta titulo da janela
                        CB.oTitle.html(jsonMod.rotulomenu);

                        //padroes para restauracao
                        CB.oTabRest = jsonMod.tabrest;
                        CB.oStatusRest = jsonMod.statusrest;

                        // se o usuario tem o modulo para restaurar
                        CB.oOpRestaurar = jsonMod.oprestaurar;

                        //Ajusta a classe do menu atual para ATIVO
                        CB.oMenuLateral.find("li[cbmodulo=" + CB.modulo + "], div[cbmodulo=" + CB.modulo + "]").addClass("ativo");
                        CB.oMenuSuperior.find("li[cbmodulo=" + CB.modulo + "]").addClass("ativo");

                        console.info("todo: inicializaModulo: Ajustar classe ATIVO quando estiver selecionado um submenu");

                        //Adiciona atributos ao BODY para permitir estilos CSS
                        $("body").attr("cbtipo", CB.jsonModulo.tipo).attr("cbready", CB.jsonModulo.ready);

                        //Adiciona css customizado
                        CB.setModCustomCss(jsonMod.csscustom);

                        //Configura o container bootstap
                        if (jsonMod.largurafixa == "Y") {
                            CB.oContainer.css("display", "block");
                        }

                        // Restringindo alterações dos campos do módulo de acordo com a permisao na lp
                        if (CB.logado && !['w', undefined].includes(CB.jsonModulo.permissaomodulo) && CB.jsonModulo.modulo != 'evento' && CB.jsonModulo.ready != 'URL') {
                            CB.posLoadUrl = () => {
                                CB.bloquearCamposTela();

                                CB.off('prePost');
                                CB.prePost = () => {
                                    let corpoToast = CB.jsonModulo.lpsusuario ? `<span class="block mb-3">Sem permissão para <strong>editar</strong> este módulo.</span>
                                                <span class="block mb-3">Para solicitar acesso, entre em contato com o seu <strong>Gestor</strong>. </span>
                                                <div class="flex align-items-center">
                                                    <img class="toast-copy mr-3" title="Clique aqui para copiar o código de erro." src="/form/img/copy.svg" alt="Icone" class="mr-3">
                                                    <span code-copy><strong>Código de erro</strong>: ${CB.jsonModulo.lpsusuario}. </span>
                                                </div>` : `<span class="block mb-3">Módulo não editavel.</span>`

                                    alertAtencao(corpoToast);

                                    return false;
                                }
                            };
                        }

                        if (CB.jsonModulo.scriptBloqueio) {

                            CB.posLoadUrl = () => {
                                let script = '<script src="inc/js/bloqueio-sessao-tela.js"><\/script>';
                                $('body').append(script);
                            }
                        }

                        //Executa primeiro a chamada do formulario, e após isso a pesquisa irá ser carregada em modo hidden
                        //Quando o formulário for do tipo URL, não executar esta chamada para evitar duplicações de ajax
                        if (CB.autoLoadUrl == "Y" && jsonMod.ready == "FILTROS") {
                            CB.loadUrl({
                                urldestino: CB.urlDestino + "?" + CB.locationSearch
                            });
                        }

                        //Prepara o módulo conforme o tipo
                        if (jsonMod.ready == "URL") {
                            //var vUrl = jsonMod.urldestino +"?_modulo="+jsonMod.modulo;
                            vUrl = jsonMod.urldestino + window.location.search;
                            //vUrl = alteraParametroGet("_modulo",jsonMod.modulo,vUrl);
                            CB.loadUrl({
                                //urldestino: jsonMod.urldestino +"?_modulo="+jsonMod.modulo
                                urldestino: vUrl
                            });

                            if (CB.jsonModulo.cbheader == "Y" || CB.jsonModulo.btsalvar == "Y") {
                                CB.oModuloHeader.removeClass("hidden");
                                CB.oModuloHeaderBg.removeClass("hidden");

                                CB.oTextoPesquisa.attr("placeholder", $("<div/>").html(jsonMod.titulofiltros).text() + "...");
                                CB.oModuloIcone.addClass(jsonMod.cssiconepar || jsonMod.cssicone);
                                CB.oModuloBreadcrumb.attr("href", "?_modulo=" + jsonMod.modulo)
                                var sBreadcrumb = (jsonMod.rotulomenupar.length == 0) ? jsonMod.rotulomenu : jsonMod.rotulomenupar + "&nbsp;/&nbsp;" + jsonMod.rotulomenu;
                                sBreadcrumb += "<i class='fa fa-angle-down fade' title='Alterar opções para o Módulo'></i>";
                                CB.oModuloBreadcrumb.find("#cbModuloBreadcrumbRotulo").html(sBreadcrumb)
                                CB.oModuloBreadcrumb.find("#cbModuloBreadcrumbOpcoes")
                                    .html("<li class='nowrap'><a href='?_modulo=_modulo&_acao=u&idmodulo=" + CB.modulo + "' target='_blank'><i class='fa fa-wrench'></i>Editar Módulo</a></li>");
                                CB.oBtNovo.addClass("hidden");
                                CB.oBtSalvar.addClass("hidden");
                                CB.oBtRestaurar.addClass("disabled");
                            }

                            if (CB.jsonModulo.btsalvar == "Y") {
                                CB.oBtNovo.addClass("hidden");
                                CB.oBtSalvar.removeClass("disabled").removeClass("hidden");
                                CB.oBtRestaurar.removeClass("disabled");
                            }

                        } else if (jsonMod.ready == "FILTROS") {

                            //Desabilita o botão de compartilhar
                            CB.oBtCompartilharItem.addClass("hidden");

                            //Desabilita o botão de compartilhar
                            CB.oBtCompartilharAlerta.addClass("hidden");

                            //Habilita botao "novo"
                            if (CB.jsonModulo.btnovo == "Y" || (CB.jsonModulo.btnovooptions && gHabilitarMatriz == "Y")) CB.oBtNovo.removeClass("disabled");

                            if (CB.jsonModulo.btsalvar != "Y") CB.oBtSalvar.addClass("hidden");

                            //Habilita o botão de relatórios
                            if (tamanho(CB.jsonModulo.relatorios) > 0) CB.oBtRep.removeClass("disabled");

                            //Mostrar o botão de calendário para FDS
                            if (CB.jsonModulo.filtrardata !== "Y") {
                                CB.oDaterange.addClass("hidden");
                            }

                            if (jsonMod.filtrosdata) {
                                for (let f of Object.keys(jsonMod.filtrosdata)) {
                                    let op = jsonMod.filtrosdata[f];
                                    CB.oDaterangeCol.find("ul").append(`<li class="dropdown-item" cbdatacolv=${f}>${op.rotulo}</li>`);
                                }

                                CB.oDaterangeCol.find("li").on("click", function () {
                                    CB.oDaterangeCol.attr("cbdatacol", $(this).attr("cbdatacolv"));
                                    $(this).addClass("activecol");
                                    $('#cbDaterangeColText').html($(this).text())
                                    $(this).siblings().removeClass("activecol");
                                });
                            } else {
                                CB.oDaterangeCol.addClass("hidden");
                            }

                            CB.oModuloHeader.removeClass("hidden");
                            CB.oModuloHeaderBg.removeClass("hidden");
                            CB.oModuloPesquisa.removeClass("hidden");

                            //Converte HTML entities para serem mostradas corretamente. Caso contrário, acentos ficariam como &aacute; ou &atilde; etc.
                            CB.oTextoPesquisa.attr("placeholder", $("<div/>").html(jsonMod.titulofiltros).text() + "...");
                            CB.oModuloIcone.addClass(jsonMod.cssiconepar || jsonMod.cssicone);
                            CB.oModuloBreadcrumb.attr("href", "?_modulo=" + jsonMod.modulo)
                            var sBreadcrumb = (jsonMod.rotulomenupar.length == 0) ? jsonMod.rotulomenu : jsonMod.rotulomenupar + "&nbsp;/&nbsp;" + jsonMod.rotulomenu;
                            sBreadcrumb += "<i class='fa fa-angle-down fade' title='Alterar opções para o Módulo'></i>";
                            CB.oModuloBreadcrumb.find("#cbModuloBreadcrumbRotulo").html(sBreadcrumb);
                            CB.oModuloBreadcrumb.find("#cbModuloBreadcrumbOpcoes")
                                .html("<li class='nowrap'><a href='?_modulo=_modulo&_acao=u&idmodulo=" + CB.modulo + "' target='_blank'><i class='fa fa-wrench'></i> Editar Módulo</a></li>");

                            //Recupera parâmetros de Auto Filtro, para casos em que a pesquisa já vem parametrizada de algum outro módulo
                            CB.jsonAutoFiltro = CB.getAutoFiltro();

                            //Monta painel de Filtros rapidos. Isto deve ser executado antes do resgate das preferências do usuário, para permitir manuseio de valores
                            CB.json2Filtros(jsonMod);

                            //Prepara Ações (botões) para serem executadas pelo usuário conforme configuração
                            if (jsonMod.acoes) {
                                CB.fAcoesResultados.init(jsonMod.acoes);
                            }

                            //Verifica se o usuário ajustou a preferência de memória da última pesquisa
                            jsonMod.jsonpreferencias.memorizaPesquisa = jsonMod.jsonpreferencias.memorizaPesquisa || "N";

                            //Verifica se a pagina de pesquisa foi chamada com parametros Json para auto-filtro. Caso contrário recupera as preferências do usuário (Última pesquisa executada por ele)
                            if (CB.jsonAutoFiltro === false) {
                                //Recupera última pesquisa (preferências do usuário)
                                if (jsonMod.jsonpreferencias._fts && jsonMod.jsonpreferencias.memorizaPesquisa == "Y") {
                                    //var strLatin = decodeURIComponent(escape(jsonMod.jsonpreferencias._fts));
                                    var strLatin = jsonMod.jsonpreferencias._fts;
                                    CB.oTextoPesquisa.val(strLatin);
                                }

                                if (jsonMod.jsonpreferencias._fds) {
                                    var arrDatas = jsonMod.jsonpreferencias._fds.split('-');
                                    CB.setIntervaloDataPesquisa(moment(arrDatas[0], "DD/MM/YYYY"), moment(arrDatas[1], "DD/MM/YYYY"));

                                    if (jsonMod.jsonpreferencias._fdscol) {
                                        CB.oDaterangeCol.attr("cbdatacol", jsonMod.jsonpreferencias._fdscol);
                                        CB.oDaterangeCol.find(`li[cbdatacolv='${jsonMod.jsonpreferencias._fdscol}']`).addClass("activecol");
                                    } else {
                                        CB.oDaterangeCol.find("li").first().addClass("activecol");
                                        CB.oDaterangeCol.attr("cbdatacol", CB.oDaterangeCol.find("li").first().attr("cbdatacolv"));
                                    }
                                } else {
                                    CB.oDaterangeCol.find("li").first().addClass("activecol");
                                    CB.oDaterangeCol.attr("cbdatacol", CB.oDaterangeCol.find("li").first().attr("cbdatacolv"));
                                    //A pedido de Daniel, colocar últimos 30 dias como default
                                    //Retirado até que se crie uma rotina mais consistente para isto
                                    //CB.setIntervaloDataPesquisa(moment().subtract(29, 'days'), moment(),"Últimos 30 dias");
                                }
                                if (jsonMod.jsonpreferencias._filtrosrapidos && jsonMod.jsonpreferencias.memorizaPesquisa == "Y") {
                                    /*
                                     * Atribui os valores incondicionalmente a cada objeto de filtro,
                                     * mesmo que recuperação dos valores de preferência(drops) de Filtros serão executados de forma ASSÍNCRONA, dentro da requisição que recupera os registros de cada dropdown
                                     * Isto permite que a opção que o usuário selecionou anteriormente seja devolvida à tela APÓS a recuperação de todos os registros do DB, deixando a tela mais fluída e independente
                                     */
                                    $.each(jsonMod.jsonpreferencias._filtrosrapidos, function (k, v) {
                                        CB.oFiltroRapido.find("[cbCol=" + k + "]").attr("cbId", v);
                                    })
                                }
                            } else {
                                $(CB.jsonAutoFiltro).each(function (i, o) {

                                    //Caso a chave [col] ou [id] não tenham sido enviada via json, gerar erro
                                    if (o.col == undefined) console.error("Js: CB.inicializaModulo: chave 'col' não enviada via Get");
                                    if (o.id == undefined) console.error("Js: CB.inicializaModulo: chave 'id' não enviada via Get");

                                    //Verifica se a coluna enviada existe como Filtro Rápido. Caso contrário, monta combinação na caixa de pesquisa textual
                                    if (CB.oFiltroRapido.find("[cbcol=" + o.col + "]").length == 1) {
                                        CB.setFiltroRapido({ col: o.col, id: o.id, valor: o.valor, rot: o.rot });
                                    } else {
                                        CB.oTextoPesquisa.val(CB.oTextoPesquisa.val() + " " + o.col + ":" + o.id);
                                    }
                                });
                            }

                            if (jsonMod.jsonpreferencias.orderby) {
                                CB.jsonModulo.jsonpreferencias.orderBy = jsonMod.jsonpreferencias.orderby;
                            }

                            //Caso seja recuperacao direta de formulario, nao executar a pesquisa
                            if (CB.autoLoadUrl != "Y" && (jsonMod.jsonpreferencias._fts || jsonMod.jsonpreferencias._fds || jsonMod.jsonpreferencias._filtrosrapidos)) {
                                if (jsonMod.jsonpreferencias.memorizaPesquisa == "Y") {
                                    CB.pesquisar();
                                }
                            }
                        }

                    }
                    //Executa callback
                    if (typeof opt.posInit == "function") {
                        opt.posInit();
                    }

                    CB.trigger('posInit', data);
                    //CB.oCarregando.hide();
                }
            });
        },

        getAutoFiltro: function () {
            var sAutoFiltro = getUrlParameter("_autofiltro");
            if (sAutoFiltro.length == 0) {
                return false;
            } else {
                return jsonStr2Object(sAutoFiltro);
            }
        },
        go: function (inParGet) {
            let _idempresa = getUrlParameter('_idempresa');
            if (_idempresa != '' && getUrlParameter("_idempresa", inParGet) == "") {
                _idempresa = '&_idempresa=' + _idempresa;
            } else {
                _idempresa = "";
            }
            CB.setWindowHistory("_modulo=" + CB.modulo + "&_acao=u" + _idempresa + "&" + inParGet);

            //Troca os dados no formulario
            CB.loadUrl({ urldestino: CB.urlDestino + "?_acao=u" + _idempresa + "&" + inParGet });
        },

        fecharForm: function () {

            //Ignora casos de formulário direto
            if (CB.jsonModulo.ready == "URL") {
                $("body").removeClass("minimizado");
                return false;
            } else {

                //Remove os estilos
                $("body").removeClass("autoloadurl novo readonly");

                //Reseta o formulario
                CB.oModuloForm.html("").addClass("hidden");

                CB.acao = "u";

                //Desabilita o botão de compartilhar
                CB.oBtCompartilharItem.addClass("hidden");

                //Desabilita o botão de compartilhar
                CB.oBtCompartilharAlerta.addClass("hidden");

                //Desabilita o botão de salvar
                CB.oBtSalvar.addClass("disabled");
                CB.oBtRestaurar.addClass("hidden");

                //Habilita o botao de novo
                if (CB.jsonModulo.btnovo == "Y" || (CB.jsonModulo.btnovooptions && gHabilitarMatriz == "Y")) CB.oBtNovo.removeClass("disabled");

                //Remove botões do usuário
                CB.removeBotoesUsuario();

                //Mostra a pesquisa
                $([CB.oModuloResultados, CB.oModuloPesquisa]).each(function () {
                    $(this).removeClass("hidden");
                });

                // GVT - focar no campo de pesquisa por texto ao fechar formulário
                CB.oTextoPesquisa.focus();

                //Trocar a url do browser SEM executar a navegação. Isto permite que o F5 seja executado com sucesso
                window.history.pushState(null, window.document.title, "?_modulo=" + CB.modulo);
                CB.trigger('posFecharForm');

                // Executa o desbloqueio da sessão na tela. 

                let queryParams = CB.locationSearch.split('&').reduce(function (acc, param) {
                    var [key, value] = param.split('=');
                    acc[key] = value;
                    return acc;
                }, {});

                if (queryParams.hasOwnProperty('_modulo')) {
                    if (queryParams.hasOwnProperty('id' + queryParams['_modulo'])) {
                        $.ajax({
                            url: 'ajax/_removebloqueiotela.php?pk=' + queryParams['id' + queryParams['_modulo']] + '&modulo=' + queryParams['_modulo'], // O caminho para o seu script PHP
                            type: 'POST',
                            success: function (response) {
                                console.log('Resposta do PHP:', response);
                            },
                            error: function (xhr, status, error) {
                                console.error('Erro na requisição AJAX:', error);
                            }
                        });
                    }
                    let boxBloqueio = $('#box-bloqueio');
                    boxBloqueio.addClass('hidden')
                }

            }
        },

        novo: function (parGet) {//@487013 - MULTI EMPRESA

            if (!parGet) {
                let content = `<div class="col-md-12" id="cbModalNovoEmpresas">`;
                for (let idempresa in CB.jsonModulo.btnovooptions) {
                    let empresa = CB.jsonModulo.btnovooptions[idempresa];
                    content += `
                    <div class="col-md-3">
                        <a onclick="CB.novo(${idempresa})">
                            <img src="${empresa.iconemodal}">
                        </a>
                    </div>`;
                }

                content += `</div>`;

                CB.modal({
                    titulo: "</strong>EMPRESA DESTINO</strong>",
                    corpo: content,
                    classe: 'quarenta',
                });
            } else {
                let strLocationSearch = "_modulo=" + CB.modulo + "&_acao=i&_idempresa=" + parGet;

                CB.acao = "i";

                $("#cbModal").modal("hide");
                CB.setWindowHistory(strLocationSearch);
                CB.loadUrl({ urldestino: CB.urlDestino + "?" + strLocationSearch });
            }
        },

        rep: function (inRelatorios, inmodulo = false) {

            let oRelatorios = inRelatorios || CB.jsonModulo.relatorios;

            var hRep = "<div class=\"col-md-4\"><div class=\"panel panel-default\" id=\"rep_%idrep%\"><div class=\"panel-heading\"><i class=\"fa %cssicone%\"></i>&nbsp;%rep%<a class=\"fa fa-bars fade hoverazul pull-right\" title=\"Editar Relat\xF3rio\" href=\"?_modulo=_rep&_acao=u&idrep=%idrep%\" target=\"_blank\"></a></div><div class=\"panel-body\"><div class=\"cbRepFiltros\" id=\"repFiltros%idrep%\">%filtros%</div><div class=\"cbRepExtrair\"><button class=\"btn btn-primary pull-right\" onclick=\"CB.extrairRep(%idrep%)\">&nbsp;Extrair</button></div></div></div></div>";

            var nLinhai = '<div class="row">';
            var nLinhaf = '</div>';
            var c = 0;
            //Altera o cabeçalho da janela modal
            $("#cbModalTitulo")
                .html("Extrair Relatórios");

            var hReps = "";

            $.each(oRelatorios, function (idrep, rep) {
                c = c + 1;

                if (c == 1) {
                    //alert(c);
                    var tRep = nLinhai + hRep;
                } else if (c == 4 || c == 7 || c == 10) {
                    //alert(c);
                    var tRep = nLinhaf + nLinhai + hRep;
                    c = 0;
                } else {
                    var tRep = hRep;
                }

                var hFiltros = "<table>";

                if (inmodulo.length > 0) {
                    hFiltros += '<tr><td><input type="hidden" name="_modulo" value="' + inmodulo + '" modrep="' + inmodulo + '"></td></tr>';
                } else {
                    hFiltros += '<tr><td>-' + inmodulo + '-</td></tr>';
                }

                if (rep.showfilters === "Y") {
                    if (tamanho(rep._filtros) == 0 || !rep._filtros) {
                        console.error("JS: carbon.rep(): Report [" + rep.rep + "] com falha na configuração dos Filtros");
                        return false;
                    }
                    $.each(rep._filtros, function (col, f) {
                        var sCbValue = tamanho(f.json) > 0 ? "cbvalue" : "";
                        var sCalendario = f.calendario === "Y" ? "calendario" : "";

                        if (f.entre === "Y") {
                            hFiltros += "<tr><td>"
                                .concat(
                                    f.rotulo,
                                    ':</td><td><label>entre&nbsp;</label></td><td class="input-group"><input type="number" name="'
                                )
                                .concat(col, '_1" class="')
                                .concat(sCalendario, '" col="')
                                .concat(col, '" idrep="%idrep%" ')
                                .concat(
                                    sCbValue,
                                    '><label class="input-group-addon">&nbsp;e&nbsp;</label><input type="number" name="'
                                )
                                .concat(col, '_2" class="')
                                .concat(sCalendario, '" col="')
                                .concat(col, '" idrep="%idrep%" ')
                                .concat(sCbValue, "></td></tr>");
                        } else {
                            hFiltros += "<tr><td>"
                                .concat(
                                    f.rotulo,
                                    '</td><td></td><td colspan="3"><input name="'
                                )
                                .concat(col, '" class="')
                                .concat(sCalendario, '" col="')
                                .concat(col, '" idrep="%idrep%" ')
                                .concat(sCbValue, "></td></tr>");
                        }

                    });
                    hFiltros += "</table><hr>";
                    tRep = tRep.replace(/%filtros%/g, hFiltros);
                } else {
                    tRep = tRep.replace(/%filtros%/g, "");
                }

                var cssicone = rep.cssicone ? rep.cssicone : "fa fa-file-text-o";

                hReps += tRep.replace(/%rep%/g, rep.rep)
                    .replace(/%idrep%/g, rep.idrep)
                    .replace(/%cssicone%/g, cssicone);

            });
            $("#cbModal #cbModalCorpo").html(hReps);
            CB.estilizarCalendarios();
            $.each($("#cbModal #cbModalCorpo").find("[cbvalue]"), function (i, o) {
                $o = $(o);
                var vIdRep = $(o).attr("idrep");
                var vCol = $(o).attr("col");
                var jSource = jsonStr2Object(oRelatorios[vIdRep]._filtros[vCol].json);
                jSource = jQuery.map(jSource, function (o, k) {
                    //Recupera a chave e o valor para o autocomplete no formato {key: value}
                    return { "value": Object.keyAt(o, 0), "label": o[Object.keyAt(o, 0)] };
                });

                //Recupera a primeira opção para a drop
                if (oRelatorios[vIdRep]._filtros[vCol].psqreq == "Y") {
                    $o.val(jSource[0].label).attr("cbvalue", jSource[0].value);
                }

                //Monta o autocomplete
                $o.autocomplete({
                    source: jSource
                    , delay: 0
                    , select: function () {
                        //alert(0);
                    }
                })
            })
            $("#cbModal").addClass("noventa").modal();
        },

        extrairRep: function (inIdRep) {
            var jRep = CB.jsonModulo.relatorios[inIdRep];
            var link = '';

            if (jRep.showfilters !== "Y") {
                janelamodal(jRep.url + "?_modulo=" + CB.jsonModulo.modulo + "&_idrep=" + jRep.idrep);
            } else {
                $oInputs = $("#rep_" + jRep.idrep).find("input[col]");
                var iv = 0;
                var sGet = "";
                var sE = "";
                $.each($oInputs, function (i, o) {
                    $o = $(o);
                    sVal = $o.cbval() ? $o.cbval() : $o.val();
                    //scol=CB.jsonModulo.relatorios[jRep.idrep].filtros[]
                    if (sVal) {
                        iv++;
                        sGet += sE + $o.attr("name") + "=" + sVal;
                        sE = "&";
                    }
                });


                if (iv === 0) {
                    alertAtencao("Nenhum parâmetro informado para o relatório ", jRep.rep);
                } else {
                    //console.log(sGet);
                    if ($("#rep_" + jRep.idrep).find("input[modrep]")) {
                        link = jRep.url + "?_modulo=" + $("#rep_" + jRep.idrep).find("input[modrep]").val() + "&_idrep=" + jRep.idrep + "&" + sGet;
                    } else {
                        link = jRep.url + "?_modulo=" + CB.jsonModulo.modulo + "&_idrep=" + jRep.idrep + "&" + sGet;
                    }

                    if (getUrlParameter("_idempresa") != "") {
                        link = link + "&_idempresa=" + getUrlParameter("_idempresa");
                    } else {
                        link = link;
                    }
                    console.log(link);
                    janelamodal(link);
                }
            }
        },

        restaurar: function () {

            var strCabecalho = "<strong>Selecione o novo status desejado</strong>";


            var inidrest = getUrlParameter(CB.jsonModulo.parget);

            var str = '';

            $.each(JSON.parse(CB.oStatusRest), function (index, value) {
                var id = Object.keyAt(value, 0);
                var valor = value[id];
                str = str + '<option value="' + id + '">' + valor + "</option>";
            });

            var $htmloriginal = $(`<div class="row">
                                        <div class="col-md-3"></div>
                                        <div class="col-md-6" nowrap>
                                            Status:
                                            <input id="_restaurar_u_${CB.oTabRest}_id${CB.oTabRest}" type="hidden" value="${inidrest}">
                                            <select id="_restaurar_u_${CB.oTabRest}_status">
                                                ${str}
                                            </select>
                                        </div>
                                        <div class="col-md-3"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4"></div>
                                        <div class="col-md-4"></div>
                                        <div class='col-md-4' style="text-align: end;" id='cbSalvar'></div>
                                    </div>`);

            $htmloriginal.find("#cbSalvar").append(
                $(`<button id="cbSalvar" type="button" class="btn btn-danger btn-xs"><i class="fa fa-circle"></i>Salvar</button>`).on('click', function () {
                    var id = $(`#_restaurar_u_${CB.oTabRest}_id${CB.oTabRest}`).val() || "";
                    var status = $(`#_restaurar_u_${CB.oTabRest}_status`).val() || "";

                    if (id != "" && status != "") {
                        CB.post({
                            objetos: `_restaurar_u_${CB.oTabRest}_id${CB.oTabRest}=${id}&_restaurar_u_${CB.oTabRest}_status=${status}`
                            , parcial: true
                        });
                    } else {
                        alertAtencao("Não foi possível restaurar o item");
                    }
                })
            );

            CB.modal({
                titulo: strCabecalho
                , corpo: [$htmloriginal]
                , classe: 'trinta'
            });
        },
        /*
         * Carrega a URL do modulo
         * urldestino: url a ser carregada
         * render: função para controle da renderização das informações recuperadas
         */
        posLoadUrl: null,
        preLoadUrl: null,
        loadUrl: function (opt) {
            //Executa evento
            if (CB.preLoadUrl && typeof (CB.preLoadUrl) === "function") {
                CB.preLoadUrl(opt);
            }

            CB.trigger('preLoadUrl', opt);

            inOpt = opt;

            console.info("todo: validar searchStrings inválidas");

            $.ajax({
                type: 'get',
                cache: false,
                url: inOpt.urldestino,
                data: "_modulo=" + CB.modulo,
                beforeSend: function () {
                    //Armazena os parâmetros GET (geralmente provenientes de clique em resultados de pesquisa) para serem concatenados posteriormente
                },
                success: function (data, textStatus, jqXHR) {
                    if (jqXHR.getResponseHeader("X-CB-REDIR") != null) {
                        window.location.assign(jqXHR.getResponseHeader("X-CB-REDIR"));
                    }

                    //Coloca o formulario em estado Modal, que é quando ele é invocado dentro de um modal
                    if (getUrlParameter("_modo") == "form") {
                        CB.oBody.addClass("form");
                    }

                    //Minimiza o menu superior
                    if (CB.modulo != "_login" && CB.jsonModulo.menufixo == "N") {
                        $("body").addClass("minimizado");
                    }

                    //Caso seja chamada direta com _acao e id via GET, nao alterar objetos
                    if (this.autoLoadUrl != "Y") {
                        console.info("todo: chamada direta com _acao e ID");
                    }

                    //Caso seja chamada de formulário, não mostrar botão de fechar
                    if (CB.jsonModulo.ready != "URL") {
                        var htmlAdicional = "<i class='fa fa-times cbFecharForm' title='Fechar' onclick='CB.fecharForm()' style='font-size: 18px;'>";
                    }
                    //Ajusta elementos conforme a ação atual, ajustada via clique ou por parametro GET
                    if (CB.acao == "i" && CB.modulo != "_login") {
                        //Estilo diferente para o formulário e body
                        $("body").addClass("novo");

                        //Habilita o botao de salvar
                        CB.oBtSalvar.removeClass("disabled");
                        //Desabilita o botao de novo
                        CB.oBtNovo.addClass("disabled");

                    } else if (CB.modulo == "_login") {
                        htmlAdicional = "";
                    } else {
                        //Habilita o botao de salvar
                        if (jqXHR.getResponseHeader("CB-READONLY") == "Y") {

                            CB.oBtNovo.addClass("disabled");
                            CB.oBtSalvar.addClass("disabled");
                            //CB.oBtRestaurar.removeClass("disabled");
                        } else {

                            CB.oBtSalvar.removeClass("disabled");

                            if (CB.oOpRestaurar == 'N' || (!CB.oTabRest)) {
                                CB.oBtRestaurar.addClass("hidden");
                            } else {
                                CB.oBtRestaurar.removeClass("hidden");
                            }
                        }
                        //Habilita o botão de compartilhar
                        CB.oBtCompartilharItem.removeClass("hidden");

                        //Habilita o botão de compartilhar
                        CB.oBtCompartilharAlerta.removeClass("hidden");
                    }

                    /*
                     * Tenta mostrar o resultado da requisição no frame de formularios
                     * Será feita verificação de erros de javascript no carregamento das informações
                     */

                    if (inOpt.render && typeof (inOpt.render) === "function") {
                        //Verifica se existe alguma função para tratamento das informações. Caso contrário faz a verificação do carregamento dos javascript e atribui ao formulário principal
                        inOpt.render(data, textStatus, jqXHR);
                    } else {

                        try {
                            //Carrega normalmente
                            CB.oModuloForm.html("").html(data).removeClass("hidden").append(htmlAdicional);
                            //Gerar estatistica de falha em fillselect
                            if (getUrlParameter('_modulo') != "alterasenha") {
                                try {
                                    _stat("fillselect", this.url.split("?")[0])
                                } catch (e) {
                                    console.warn(e);
                                }
                            }
                        } catch (e) {
                            CB.validaScriptOrigemAjax(data, e);
                            return false;
                        }
                    }

                    //Esconde a pesquisa
                    $([CB.oModuloResultados, CB.oModuloPesquisa]).each(function () {
                        $(this).addClass("hidden");
                    });

                    console.info("todo: atribui calendarios aos campos de data (pt-br)");

                    //Encontra o primeiro elemento visivel e coloca o foco nele
                    CB.focoInicial();

                    CB.oModuloForm.on('change keyup keydown', 'input, textarea, select', function (e) {
                        //gcbSalvar.removeClass("disabled");
                        //console.info("todo: desabilitar botao de salvar");
                    });

                    //atribuir funcao de cbpost para acionamento com 'enter'
                    if (CB.jsonModulo.postonenter == "Y") {
                        CB.postOnEnter();
                    }

                    console.info("todo: efetuar toggle nos bototes de controle do formulario");
                    //CB.toggleBotoesControleForm(true);

                    //Estilizar Checkboxes com plugin Bootstrap Toggle
                    CB.estilizarCheckbox(CB.oModuloForm);

                    CB.estilizarCalendarios();

                    //Estilizar select 2
                    /*$("select[class*=select2]").select2({
                        language: "pt-BR"
                    });*/

                    /*
                     * Ajustar funcionalidade de Popover
                     * Para que isto funcione, os objetos para "content" do popver devem estar contidos dentro de um elemento com o atributo [data-toggle="popover"]
                     * Assim, eles serão copiados para o centúdo do popover e depois deletados
                     */
                    /*
                   $('[data-toggle="popover"]').webuiPopover({
                       width:300,
                       height:535,
                       padding:false,
                       animation:'pop',
                       content:function(){
                           var tmpHtml = $(this).html();
                           $(this).html("");
                           return tmpHtml;
                       }

                   }).on('shown.webui.popover',function(e){
                       alert('dg')
                   });     */

                    CB.preparaEditaveis();
                    CB.controlaCollapse();

                    CB.trigger('posLoadUrl', data, textStatus, jqXHR);
                    //Executa evento
                    if (CB.posLoadUrl && typeof (CB.posLoadUrl) === "function") {
                        CB.posLoadUrl(data);

                    }
                }
            });
        },

        /*
         * Ajusta o foco inicial
         */
        focoInicial: function () {
            //CB.oModuloForm.find(":input:not(input[type=button],input[type=submit],button,select):visible:first").focus();
        },

        /*
         * Executar Post com 'enter'
         */
        postOnEnter: function () {
            //campos input 'autocomplete' serao excluidos do enter, devendo-se realizar a chamada cbpost manualmente para cada caso. O curinga eh necessario visto que os autocompletes podem ter mais de uma classe atribuida
            CB.oModuloForm.find(":input:not([type=button],[type=submit],a,button,textarea,[class*=ui-autocomplete-input],[class*=acinsert])").on("keyup", function (e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode == 13) {
                    if (!teclaLiberada(e)) return;//Evitar repetição do comando abaixo
                    CB.post();
                }
            });
            console.info("todo: postOnEnter: Nao esta sendo executado. Nao atribuir OnEnter a objetos incomuns ao formulário. Ex: plugins jQuery ou Bootstrap");
        },

        toggleBotoesControleForm: function (inMostrar) {
            if (inMostrar) {
                CB.oBtFecharForm.show();
                //gBtNovo.show();
                CB.oBtSalvar.show();
            } else {
                CB.oBtFecharForm.hide();
                //gBtNovo.hide();
                CB.oBtSalvar.hide();
                CB.oNavRegAtual.text("");
                CB.oPainelNavegacao.hide();
            }
        },
        //limpoar os filtos da pesquisa
        limparFiltros: function () {
            //limpar campos selectpicker sem utilizar as funções padrões do plugin (selectpicker)
            //o uso das funções proprias do plugin ocasionam quebra das requisições dos filtros via ajax
            //buscando filtros ativos
            $.map($('.picker.filtroAtivo'), function (el) {
                //forçando função interna do picker para remoção dos campos selecionados
                $(el).find('.bs-deselect-all').trigger('click');
                //devolvendo ao estado inicial e label padrão do campo
                CB.resetFiltro($(el).attr('cbcol'));
                $(el).removeClass('filtroAtivo');
            });
            // limpar botão ocultar
            $("[cbcol='ocultar']").removeClass("filtroAtivo");
            // limpar busca de texto
            CB.oTextoPesquisa.val('');
            // limpar compo de periodo da busca
            CB.oDaterange.data('daterangepicker').clickCancel();
            CB.oDaterangeCol.find("#cbDaterangeColText").text('');
            CB.oDaterangeCol.find("li").removeClass("activecol");
            CB.oDaterange.data('daterangepicker').clickCancel();
            // limpar prefericias do usuario para o modulo no banco
            CB.setPrefUsuario('d', CB.modulo + '._filtrosrapidos');
            CB.setPrefUsuario('d', CB.modulo + '._fts');
            CB.setPrefUsuario('d', CB.modulo + '._fds');
            CB.setPrefUsuario('d', CB.modulo + '._fdscol');
            // remover botão de limpar
            CB.ocbLimparFiltros.hide();
        },
        // limpar ordenação das colunas de resultados
        limparOrderBy: function () {
            // limpar prefericias do usuario de ordenação de colunas
            CB.setPrefUsuario('d', CB.modulo + '.orderby');
            // desativar botão de ordenação
            $('td').find('i.ativo').removeClass('ativo');
            $('thead').find('td.ativo').removeClass('ativo');
            // limpar filtros nas preferencias do carbon
            CB.jsonModulo.jsonpreferencias.orderBy = {};
            CB.jsonModulo.jsonpreferencias.orderby = {};
            // resetar a pesquisa
            CB.pesquisar({ resetVarPesquisa: true });
        },

        estilizarCheckbox: function (inObjPai) {
            //inObjPai.find('input[type=checkbox][data-toggle^=toggle]').bootstrapToggle();
        },

        estilizarCalendarios: function () {
            /* Calendario Simples
            * Acompanhar discussão em torno do autoupdate: https://github.com/dangrossman/bootstrap-daterangepicker/issues/815 e https://github.com/dangrossman/bootstrap-daterangepicker/pull/794
            * @todo: Verificar necessidade de trocar o plugin
            */
            $(".calendario").daterangepicker({
                "autoUpdateInput": false,
                "singleDatePicker": true,
                "showDropdowns": true,
                "linkedCalendars": false,
                "opens": "left",
                "locale": CB.jDateRangeLocale
            }).on("apply.daterangepicker", function (e, picker) {
                picker.element.val(picker.startDate.format(picker.locale.format));
            });

            $(".calendariodatahora").daterangepicker({
                "autoUpdateInput": false,
                "singleDatePicker": true,
                "showDropdowns": true,
                "linkedCalendars": false,
                "opens": "left",
                "locale": CB.jDatetimeRangeLocale
            }).on("apply.daterangepicker", function (e, picker) {
                picker.element.val(picker.startDate.format(picker.locale.format));
            });

            $("body").delegate(".btn-group:not(.bootstrap-select) > button", "click", function (e) {

                $this = $(this);

                if ($this.hasClass("selecionado")) {
                    $this.removeClass("selecionado");
                } else {
                    $this.closest(".btn-group").find("button").removeClass("selecionado");
                    $this.addClass("selecionado");
                }
                //O evento de clique estava sendo atribuido mais de 1x ao objeto, causando comportamento inverso: selecionado e depois de-selecionando
                //https://developer.mozilla.org/pt-BR/docs/Web/API/Event/stopImmediatePropagation
                e.stopImmediatePropagation();
            });
        },

        preparaEditaveis: function () {
            $(".editavel").on("click", function () {
                $this = $(this);
                $this.attr("contentEditable", true);
                $this.focus();
            }).on("blur", function () {
                $this = $(this);
                $this.attr("contentEditable", false);
            })
        },

        controlaCollapse: function () {

            $.each(CB.oModuloForm.find("[data-toggle=collapse]"), function (i, o) {
                $o = $(o);
                var shref = $o.attr("href");
                var khref = shref.substr(1);//Remover a # do id para não gerar erro de path no mysql

                //Verifica se o elemento com collapse possui alguma preferência de usuário salva
                if (CB.jsonModulo.jsonpreferencias.collapse && CB.jsonModulo.jsonpreferencias.collapse[khref]) {

                    $oc = $(shref);
                    $oc.removeClass("collapse in");
                    col = CB.jsonModulo.jsonpreferencias.collapse[khref];

                    if (col == "N") {
                        $oc.addClass("collapse in");
                    } else {
                        $oc.addClass("collapse");
                    }
                } else {
                    $oc = $(shref);
                    if (!$oc.hasClass("collapse")) {
                        $oc.addClass("collapse in");
                    }
                }

                //Executa futuramente (no clique) o armazenamento do estado do collapse, nas preferências do usuário
                $o.on("click", function () {
                    $this = $(this);//Objeto atual. Geralmente um panel-heading
                    var shref = $this.attr("href");
                    var khref = shref.substr(1);//Remover a # do id para não gerar erro de path no mysql
                    let val;
                    $body = $(shref);
                    if (CB.modulo != 'despesasitem') {// neste modulo não queremos que traga as informacoes conforme escolha do usuario na tela na hora
                        if ($body.hasClass("collapse") && $body.hasClass("in")) {//Está aberto
                            //Fechar
                            CB.setPrefUsuario('m', '{"' + CB.modulo + '":{"collapse":{"' + khref + '":"Y"}}}');
                            val = 'Y';

                        } else {
                            //Abrir
                            CB.setPrefUsuario('m', '{"' + CB.modulo + '":{"collapse":{"' + khref + '":"N"}}}');
                            val = 'N';
                        }
                        CB.jsonModulo.jsonpreferencias.collapse[khref] ? CB.jsonModulo.jsonpreferencias.collapse[khref] = val : null;
                    }

                });
            });
        },

        /*
         * Chamar Modal
         * Parâmetros (Json):
            {
                header: Html (texto) a ser colocado no elemento [modal-header]
                ,title: Html (texto) a ser colocado no elemento [modal-title]
                ,corpo: Html|[Objetos Jquery] 
                    -> texto: sera colocado via metodo html() no elemento [modal-body]
                    -> Objetos: será realizado um loop e colocado pelo metodo append()
                ,url: Caso esta chave seja enviada, irá carregar o conteúdo dela dentro do modal.
                      * Caso seja informada, irá ignorar header e corpo em html
                ,classe: String com classes separadas por espaço a serem atribuidas ao modal
                ,callback: callback global
                ,aoAbrir: função a ser executada on(show)
                ,aoFechar: callback a ser executado on(hidden)
         */
        modal: function (oPar) {

            //maf191219: armazenar todos os parametros de entrada para serem utilizados no onclose/callback
            CB.oModal.data("parametros", oPar);

            // GVT / 25/05/2020 - Alterado a função callback para executar quando o modal for fechar. Antes: 'hidden.bs.modal', Depois: 'hide.bs.modal'
            CB.oModal.on('hidden.bs.modal', function (e) {
                $this = $(this);
                $this.data("parametros");

                if ($this.data("parametros") === undefined) return;

                //Verifica se o programador enviou uma funcao de callback
                if (typeof $this.data("parametros").aoFechar === "function") {
                    $this.data("parametros").aoFechar($this.data("parametros"));
                }

                var classe = $this.data("parametros").classe;
                if (classe !== undefined && classe !== "") {
                    $this.removeClass($this.data("parametros").classe);
                }

                //Remove (reset) parametros de funcionamento de CB.modal()
                if ($this.data("parametros").limparModal !== false) {
                    $this.find("#cbModalTitulo").empty();
                    $this.find("#cbModalCorpo").empty();
                    $this.find("#cbModalRodape").empty();
                }

                $this.removeData("parametros");
                //Remove o registro de evento JQuery
                $this.unbind("hidden.bs.modal");
            })

            CB.modalCallback = undefined;

            $("#cbModalTitulo").empty();
            $("#cbModalCorpo").empty();
            $("#cbModalRodape").empty();

            if (!oPar) {
                CB.oModal.modal('show');
                return false;
            } else {

                if (oPar.url) {

                    oPar.url = oPar.menu === true ? oPar.url : alteraParametroGet("_menu", "N", oPar.url);

                    var sHeader = oPar.header ? "titulo" : "";

                    CB.oModal.find(".modal-title").append(oPar.header || "");
                    CB.oModal.find(".modal-body").append("<iframe src='" + oPar.url + "' id='iframeModal'></iframe>")
                    CB.oModal.removeClass()
                        .addClass("modal fade in")
                        .addClass("url")
                        .addClass(oPar.classe || "noventa")
                        .addClass(sHeader)
                        .modal('show');
                } else if (oPar.titulo || oPar.corpo || oPar.rodape) { // GVT - 27/05/2020 - Criada a condição para quando estamos instanciando um novo modal onde TITULO ou CORPO não estão vazios

                    CB.oModal.find(".modal-title").html(oPar.titulo || "");
                    CB.oModal.find("#cbModalRodape").html(oPar.rodape);

                    // GVT - 27/05/2020 - Condição verifica o tipo de dado do campo CORPO para adicionar o conteúdo no ".modal-body"
                    if (typeof oPar.corpo === "string") {
                        CB.oModal.find(".modal-body").html(oPar.corpo || "");
                    } else if (typeof oPar.corpo === "object") {
                        oPar.corpo.forEach(function (o, i) {
                            //Carrega todas as propriedades e eventos
                            CB.oModal.find(".modal-body").append(o);
                        })
                    } else {
                        console.error("modal: tipo de parâmetro [corpo] não esperado");
                    }

                    CB.oModal.removeClass()
                        .addClass("modal")
                        .addClass(oPar.classe || "")
                        .modal('show');

                } else {
                    CB.oModal.modal('show');
                }

                //Verifica a existencia de função global, a ser executada ao abrir o modal
                if (oPar.aoAbrir) {
                    if (typeof oPar.aoAbrir === 'function') {
                        oPar.aoAbrir(this);
                    }
                }

                //Verifica a existencia de callback global, a ser executado em qualquer área do aplicativo
                if (oPar.callback) {
                    if (typeof oPar.callback === 'function') {
                        CB.modalCallback = oPar.callback;
                    }
                }
            }
        },

        prePost: null,
        posPost: null,
        /**
        * @param {String} aString
        * @param {Boolean} aCaseSensitive
        * @param {Boolean} aBackwards
        * @param {Boolean} aWrapAround
        * @param {Boolean} aWholeWord
        * @param {Boolean} aSearchInFrames
        * @param {Boolean} aShowDialog
        * @returns {Boolean}
        * @static
        */
        post: function (inParam) {
            if (CB.ajaxPostAtivo && CB.jsonModulo.ajaxparalelo === "N") {
                console.warn("carbon.post ativo: Ação cancelada. Para permitir salvamentos em paralelo configure o módulo.");
                alertAtencao("Aguarde: ação anterior ainda não concluída")
                return false;
            }

            var respPrepost = null;

            let respTriggerPrepost = CB.trigger('prePost', inParam);

            if (respTriggerPrepost.includes(false)) {
                console.warn("prePost: false");
                return false;
            } else {
                for (let o of respTriggerPrepost) {
                    if (typeof o === "object") {
                        inParam = inParam || {};
                        Object.assign(inParam, o);
                    }
                }
            }

            //Executa eventos preliminares para posts do Carbon somente
            if (CB.prePost && typeof (CB.prePost) === "function" &&
                (!inParam || inParam.objetos === undefined)) {
                //Executa o pre-post
                respPrepost = CB.prePost(inParam);
                if (respPrepost === false) {
                    console.warn("prePost: false");
                    return false;
                } else if (typeof (respPrepost) === "object") {
                    console.warn("prePost: object");
                    inParam = inParam || {};
                    Object.assign(inParam, respPrepost);
                }
            }

            if (!CB.oModuloForm.is(':visible') && !inParam.objetos) {
                console.warn("post: CB.oModuloForm não está visível. Ação cancelada.");
                return false;
            }



            //armazena objetos input serializados
            vdados = "";
            var verificaHeadersCarbon;
            //mensagem de confirmacao (por padrao = confirmado)
            var confirmado = true;

            //Variavel local com os objetos do form para pegar a quantidade de objetos a serem enviados via post, e adicionar objetos novos posteriormente
            var objFormInput = $("");
            if ((!inParam || inParam.objetos === undefined) || (inParam.parcial !== true)) {
                //Separar objetos especiais
                sInputsSelector = ":input:not([cbvalue]):not([type=checkbox]):not([type=radio])";
                //Objetos do formulario
                objFormInput = CB.oModuloForm.find(sInputsSelector);
                //Objetos dentro de Modais
                objFormInput = objFormInput.add(CB.oModal.find(sInputsSelector));
                //Objetos Especiais
                objFormInput = objFormInput.add(CB.oModuloForm.find(":input[cbvalue][name]").map(function () {
                    return ($("<input name='" + $(this).attr("name") + "' value='" + $(this).cbval() + "'>")[0])
                }));
                objFormInput = objFormInput.add(CB.oModuloForm.find(":input[type=checkbox]").map(function () {
                    if ($(this).is(":checked")) {
                        return ($("<input name='" + $(this).attr("name") + "' value='" + $(this).val() + "'>")[0])
                    } else {
                        if ($(this).attr("uncheckedvalue") === undefined) {
                            return ($("<input name='" + $(this).attr("name") + "' value=''>")[0])
                        } else {
                            return ($("<input name='" + $(this).attr("name") + "' value='" + $(this).attr("uncheckedvalue") + "'>")[0])
                        }
                    }

                }));
                objFormInput = objFormInput.add(CB.oModuloForm.find(":input[type=radio]:checked").map(function () {
                    if (($(this).attr("name") != undefined)) {
                        return ($("<input name='" + $(this).attr("name") + "' value='" + $(this).val() + "'>")[0])
                    }

                }));
            }



            //verifica se algum parametro foi enviado
            if (inParam == undefined) {
                //Inicializa o objeto de parametros
                inParam = {};
                //inicializa variavel com url para arquivo da requisição
                inParam.urlArquivo = undefined;

                inParam.customInputs = false;

            } else {

                inParam.bypass = inParam.bypass || false;

                //Armazena a posica vertical do formulario
                if (inParam.memoriaVertical === true) {
                    memoriavertical();
                }

                //valida mensagem de confirmacao de post
                if (inParam.confirmacao != undefined) {

                    if (inParam.confirmacao === true) {
                        confirmado = confirm("Deseja prosseguir com a alteração?");
                    } else {
                        confirmado = confirm(inParam.confirmacao);
                    }

                    if (confirmado == false) {
                        return;
                    }
                }

                //Quando o parâmetro inParam.objetos for utilizado (post manual), valida entrada como string ou serializa objetos
                if (typeof (inParam.objetos) === "string") {
                    //vdados = inParam.objetos;
                    //Transformar a string informada em objetos
                    $objtmp = inParam.objetos.deserialize();
                    objFormInput = objFormInput.add($objtmp);

                } else if (typeof (inParam.objetos) === "object") {
                    objFormInput = objFormInput.add(inParam.objetos.obj2input());
                } else {
                    alert("post: inParam.objetos deve ser informado como String ou Object");
                }
            }



            //chamada padrao do carbon para substituir <form>s
            if (!objFormInput.validacampos()) {
                console.log("post: validacampos==false: return");
                return false;
            } else {
                vdados = objFormInput.serialize();
            }

            //verifica recarregamento de html
            if (inParam.refresh == undefined) {
                inParam.refresh = "repost";
            }

            if (inParam.ajaxType == undefined) {
                inParam.ajaxType = "post";
            }
            if (inParam.urlArquivo == undefined) {
                //Apos o clique na tabela de resultados do _modulofiltros, a variavel global ParGet é preenchida com os parametros GET gerados. Isto permite pagina de post customizado.
                if (CB.urlDestino.split("?").length > 1) {
                    alert("Js: Erro: O parâmetro urldestino não pode conter parametros GET ou separador '?'");
                } else {

                    let interrogacao = "?";
                    if (CB.locationSearch && CB.locationSearch.includes("?")) {
                        interrogacao = "";
                    }

                    //Deve-se concatenar vazio na variavel, para o replace ser feito com sucesso
                    var tmpParGet = alteraParametroGet("_modulo", CB.modulo, CB.urlDestino + interrogacao + CB.locationSearch);

                    //Monta a URL a ser chamada via POST
                    //inParam.urlArquivo = (gModuloUrlParget!=undefined && gModuloUrlParget.length > 1)? gModuloUrldestino + "?" + gModuloUrlParget : gModuloUrldestino;
                    //inParam.urlArquivo = gModuloUrldestino + "?" + tmpParGet;
                    inParam.urlArquivo = tmpParGet;
                }
            }

            //Parâmetros adicionais
            inParam.urlArquivo = inParam.urlArquivo + "&_refresh=" + inParam.refresh;

            //Instanciar eventos definidos pelo usuário
            if (CB.posPost && typeof (CB.posPost) === "function") {
                inParam.posPost = CB.posPost;
            }

            //Post
            if (inParam.urlArquivo == undefined) {
                alert("submitajax: O atributo inParam.urlArquivo ou cbmodulourl do objeto gFrameForms está vazio!");
            } else {

                var validaBloqueioPost = false;

                //fecha todos os avisos do jgrowl
                //jQuery.jGrowl('close');
                console.info("todo: post: fechar todos os avisos popup")

                //captura o elemento que ceontém o foco, para devolver o foco após o processamento do post
                var objFoco = document.activeElement.name;

                //Evia os dados via ajax
                console.info("todo: validar searchStrings inválidas");

                var queryString = inParam.urlArquivo.split('?')[1];

                var queryParams = queryString.split('&').reduce(function (acc, param) {
                    var [key, value] = param.split('=');
                    acc[key] = value;
                    return acc;
                }, {});

                console.log('CB.jsonModulo.timeout', CB.jsonModulo.timeout)

                jQuery.ajax({


                    type: inParam.ajaxType, /*get/post*/
                    url: inParam.urlArquivo,
                    data: vdados,
                    objCB: CB,
                    beforeSend: function (jqXHR, settings) {
                        if (CB.jsonModulo.timeout != '' && CB.jsonModulo.timeout != '00:00' && CB.jsonModulo.timeout != null) {
                                if (queryParams.hasOwnProperty('_modulo')) {
                                    if (queryParams.hasOwnProperty('id' + queryParams['_modulo'])) {
                                        $.ajax({
                                            url: 'ajax/_consultabloqueiotela.php?pk=' + queryParams['id' + queryParams['_modulo']] + '&modulo=' + queryParams['_modulo'], // O caminho para o seu script PHP
                                            type: 'POST',
                                            success: function (response) {
                                                console.log('Resposta do PHP2:', response);
                                                console.log('validaBloqueioPost2', validaBloqueioPost)
                                            },
                                            error: function (xhr, status, error) {
                                                validaBloqueioPost = true
                                                console.error('Erro na requisição AJAX:', error);

                                            }
                                        });
                                    }
                                }
                        } else {
                            console.log('Modulo não tem timeout.')
                        }

                        CB.ajaxPostAtivo = true;
                        //Executa eventos declarados dentro dos parâmetros de cb.post
                        if (inParam && inParam.beforeSend && typeof inParam.beforeSend === "function") {
                            inParam.beforeSend(jqXHR, settings);
                        }

                        vdados = "";
                        jqXHR.setRequestHeader("X-CB-AJAX", "Y");

                        //nao recuperar os dados gerados apos o post
                        if (inParam.refresh == "refresh") {
                            jqXHR.setRequestHeader("HTTP_X_CB_REFRESH", "N");
                        }

                        //maf030320: efetuar bypass nas restricoes de modulo
                        if (inParam.bypass === true) {
                            jqXHR.setRequestHeader("CB-BYPASS", "Y");
                        }

                    }


                }).done(function (data, textStatus, jqXHR) {//sucesso

                    CB.ajaxPostAtivo = false;

                    console.log('validaBloqueioPost', validaBloqueioPost)
                    if (validaBloqueioPost == false) {
                        //Armazena certificado JWT
                        if (jqXHR.getResponseHeader("jwt")) {
                            Cookies.set('jwt', jqXHR.getResponseHeader("jwt"), { expires: 7 });
                            localStorage.setItem('jwt', jqXHR.getResponseHeader("jwt"));
                        }

                        CB.lastInsertId = null;

                        if (window.frameElement) {

                            let modalCallback = window.parent.CB.modalCallback;

                            if (modalCallback && typeof modalCallback === 'function') {
                                modalCallback(data, textStatus, jqXHR);
                            }
                            //cbPostCallback(jqXHR,data,objFoco);
                        }

                        if (jqXHR.getResponseHeader("X-CB-REDIR") != null) {
                            window.location.assign(jqXHR.getResponseHeader("X-CB-REDIR"));
                        } else {

                            //Verifica URL de retorno em casos de insert, e SOMENTE para os casos de post normal. Em casos de post controlado via programação (geralmente em casos de update), a URL para refresh não deve ser alterada
                            //Nos casos de Token, não remontar a url
                            if (inParam.refresh == "repost" && CB.acao == "i" &&
                                jqXHR.getResponseHeader("X-CB-PKID") != null &&
                                jqXHR.getResponseHeader("X-CB-PKID") != "" &&
                                CB.gToken != "Y") {

                                //descartar casos de post ajax que nem devem recarregar a pagina. Ex: [ajax] ao inves do numero da linha [0]
                                if (jqXHR.getResponseHeader("X-CB-PKID").length > 0) {

                                    //var vUrlNova = alteraParametroGet("_acao","u",window.location.search.split("?")[1]);
                                    var vUrlNova = alteraParametroGet("_acao", "u", window.location.search);
                                    vUrlNova = alteraParametroGet(jqXHR.getResponseHeader("X-CB-PKFLD"), jqXHR.getResponseHeader("X-CB-PKID"), vUrlNova);

                                    CB.setWindowHistory(vUrlNova);

                                } else {
                                    console.warn("Header Pk com length <= 0");
                                }

                            }

                            if (inParam.refresh !== false && jqXHR.status == 200 &&
                                jqXHR.getResponseHeader("X-CB-RESPOSTA") == "1" &&
                                jqXHR.getResponseHeader("X-CB-FORMATO") == "html") {
                                //Trata novos inserts manuais através de CBPOST. Isto permite que o programador realizae ações ajax paralelas sem enviar todos os campos da página via post/get
                                if (jqXHR.getResponseHeader("X-CB-PKID") != null &&
                                    jqXHR.getResponseHeader("X-CB-PKID") != "" &&
                                    CB.gToken != "Y") {
                                    CB.lastInsertId = jqXHR.getResponseHeader("X-CB-PKID");
                                }
                                /**************************************************************************************
                                 * Esta função NÃO deve executar return, pois se trata de uma requisição ASYNCHRONOUS *
                                 **************************************************************************************/
                                //var dataclean = data.replace(/(\r\n|\n|\r)/gm,"");//maf0811: executar limpeza de possiveis quebras de linha

                                //Ajusta a ação. Este ajuste deve ser feito antes de qualquer comando, pois em caso de repost as variáveis que vêm do servidor já estão como "u"
                                CB.acao = "u";

                                //verifica se eh repost
                                if (inParam.refresh == "repost") {
                                    CB.oModuloForm.html(data);
                                    CB.controlaCollapse();
                                }

                                //verifica eh refresh (ajax simples)
                                if (inParam.refresh == "refresh") {
                                    //window.location.reload();
                                    vUrl = CB.urlDestino + window.location.search;
                                    //vUrl = alteraParametroGet("_modulo",jsonMod.modulo,vUrl);
                                    CB.loadUrl({
                                        //urldestino: jsonMod.urldestino +"?_modulo="+jsonMod.modulo
                                        urldestino: vUrl
                                    });
                                }

                                //verifica eh reload: não executa repost, e não recarrega o carbon inteiro através de refresh
                                if (inParam.refresh == "reload") {
                                    CB.go(CB.urlDestino + window.location.search);
                                }

                                //atribui funcionalidade de submit no clique do 'enter'. Isto eh necessario apos o recarregamento.
                                if (CB.jsonModulo.postonenter == "Y") {
                                    CB.postOnEnter();
                                    console.info("todo: post: corrigir multiplos acionamentos do Enter no postOnEnter ");
                                }

                                //Remove estilos visuais
                                $("body").removeClass("novo");

                                //Mostra a mensagem de sucesso
                                alertSalvo(inParam.msgSalvo);


                                if (inParam == undefined) {
                                    $("#cbModal").hide();
                                }

                                // executa funcao de callback devolvendo a resposta
                                if (inParam.callback && typeof (inParam.callback) === "function") {
                                    inParam.callback(jqXHR, data, objFoco);
                                }



                                CB.trigger('posPost', { data: data, status: textStatus, jqXHR: jqXHR, inParam: inParam });

                                // executa funcao de callback devolvendo a resposta
                                if (inParam.posPost && typeof (inParam.posPost) === "function") {
                                    inParam.posPost(data, textStatus, jqXHR);
                                }

                                //executa callback instanciado para a pagina em questao
                                if (typeof (cbPostCallback) === "function") {
                                    cbPostCallback(jqXHR, data, objFoco);
                                }

                                CB.estilizarCalendarios();

                            } else if (inParam.refresh === false && jqXHR.status == 200 && jqXHR.getResponseHeader("X-CB-RESPOSTA") == "1") {

                                //Trata novos inserts manuais através de CBPOST. Isto permite que o programador realizae ações ajax paralelas sem enviar todos os campos da página via post/get
                                if (jqXHR.getResponseHeader("X-CB-PKID") != null && jqXHR.getResponseHeader("X-CB-PKID") != "" && CB.gToken != "Y") {
                                    CB.lastInsertId = jqXHR.getResponseHeader("X-CB-PKID");
                                }

                                CB.trigger('posPost', { data: data, status: textStatus, jqXHR: jqXHR, inParam: inParam });

                                // executa funcao de callback devolvendo a resposta
                                if (inParam.posPost && typeof (inParam.posPost) === "function") {
                                    inParam.posPost(data, textStatus, jqXHR);
                                }

                                //Mostra mensagem
                                alertSalvo(inParam.msgSalvo);
                                //Resposta que vai mostrar um alert na tela
                            } else if (jqXHR.status == 200 && jqXHR.getResponseHeader("X-CB-RESPOSTA") == "0" && jqXHR.getResponseHeader("X-CB-FORMATO") == "alert") {

                                console.info("todo: post: efetuar log dos erros e mostrar link pro usuario")
                                alertAtencao(data);

                            } else if (jqXHR.status == 200 && jqXHR.getResponseHeader("X-CB-RESPOSTA") == "0" && jqXHR.getResponseHeader("X-CB-FORMATO") == "erro") {

                                console.info("todo: post: efetuar log dos erros e mostrar link de erro pro usuario")
                                alertErro(data);

                            } else if (jqXHR.status == 200 && jqXHR.getResponseHeader("X-CB-FORMATO") == "bool") {

                                if (jqXHR.getResponseHeader("X-CB-RESPOSTA") != "1") {
                                    console.error("Erro. Parâmetro X-CB-RESPOSTA no header da resposta (bool) está diferente de 1: [" + jqXHR.getResponseHeader("X-CB-RESPOSTA") + "]");
                                    alert("Mensagem do servidor:\n" + data);
                                    return false;
                                }

                                // executa funcao de callback devolvendo a resposta
                                if (inParam.callback && typeof (inParam.callback) === "function") {
                                    inParam.callback(data);
                                }

                                //executa callback instanciado para a pagina em questao
                                if (typeof (cbPostCallback) === "function") {
                                    cbPostCallback(jqXHR, data, objFoco);
                                }

                                CB.trigger('posPost', { data: data, status: textStatus, jqXHR: jqXHR, inParam: inParam });

                                // executa funcao de callback devolvendo a resposta
                                if (inParam.posPost && typeof (inParam.posPost) === "function") {
                                    inParam.posPost(data, textStatus, jqXHR);
                                }

                                if (inParam.refresh == "reload" || inParam.refreshPagina) {
                                    window.location.reload();
                                }

                            } else if (jqXHR.status == 200 && (inParam.ajaxType == "get")) {

                                // executa funcao de callback devolvendo a resposta
                                if (inParam.callback && typeof (inParam.callback) === "function") {
                                    inParam.callback(jqXHR, data, objFoco);
                                }

                                CB.trigger('posPost', { data: data, status: textStatus, jqXHR: jqXHR, inParam: inParam });

                                // executa funcao de callback devolvendo a resposta
                                if (inParam.posPost && typeof (inParam.posPost) === "function") {
                                    inParam.posPost(data, textStatus, jqXHR);
                                }

                                if (inParam.refreshPagina) {
                                    window.location.reload();
                                }

                            } else if (jqXHR.status == 200 &&
                                (jqXHR.getResponseHeader("X-CB-RESPOSTA") == null ||
                                    jqXHR.getResponseHeader("X-CB-FORMATO") == null)) {

                                alert("functions.js: Servidor de Aplicação não enviou Headers do Carbon. Probabilidades:\n 1 - Não foi realizado o 'include_once CBPOST'\n 2 - Nenhum objeto foi enviado ao CBPOST.\n[Response Headers]:\n\n" + jqXHR.getAllResponseHeaders().toString());
                                console.log(jqXHR.getAllResponseHeaders());
                            } else {
                                alert("functions.js: Headers não previstos. [Response Headers]:\n\n" + jqXHR.getAllResponseHeaders().toString());
                            }

                            let queryParams = CB.locationSearch.split('&').reduce(function (acc, param) {
                                var [key, value] = param.split('=');
                                acc[key] = value;
                                return acc;
                            }, {});
                            if (CB.jsonModulo.timeout != '' && CB.jsonModulo.timeout != '00:00' && CB.jsonModulo.timeout != null) {
                                if (queryParams.hasOwnProperty('_modulo')) {
                                    if (queryParams.hasOwnProperty('id' + queryParams['_modulo'])) {
                                        $.ajax({
                                            url: 'ajax/_renovabloqueiotela.php?pk=' + queryParams['id' + queryParams['_modulo']] + '&modulo=' + queryParams['_modulo'], // O caminho para o seu script PHP
                                            type: 'POST',
                                            success: function (response) {
                                                console.log(response);
                                                if (response['status']) {

                                                    let boxBloqueio = $('#box-bloqueio');
                                                    let bloqueioIcone = $("#box-bloqueio .bloqueio-icone")
                                                    let bloqueioTimer = $('#box-bloqueio .bloqueio-timer')
                                                    boxBloqueio.removeClass('hidden')
                                                    bloqueioTimer.html(convertSecondsToMMSS(response['timeout']))
                                                    stopTimer()
                                                    startTimer('#box-bloqueio .bloqueio-timer', response['timeout'])
                                                    console.log('bloqueioTimer', response['timeout'])
                                                    boxBloqueio.find('p').each(function () {
                                                        $(this).removeClass('bg-danger text-white')
                                                    })
                                                    bloqueioIcone.removeClass('text-white')
                                                    bloqueioIcone.attr('title', 'Aviso: Tela em modo EDIÇÃO')

                                                }
                                            },
                                            error: function (xhr, status, error) {
                                                console.error('Erro na requisição AJAX:', error);
                                            }
                                        });
                                    }
                                    let boxBloqueio = $('#box-bloqueio');
                                    boxBloqueio.addClass('hidden')
                                }
                            }
                        }
                    }

                }).fail(function (objxmlreq, ajaxOptions, thrownError) {
                    CB.ajaxPostAtivo = false;
                });


            }
        },
        setWindowHistory: function (inStrLocation) {
            CB.locationSearch = inStrLocation;
            let interrogacao = "?";
            if (inStrLocation && inStrLocation.includes("?")) {
                interrogacao = "";
            }
            window.history.pushState(null, window.document.title, interrogacao + inStrLocation);
        },

        /*
         * Cria dinamicamente os elementos HTML para filtros conforme configuração de Filtros para o  Módulo
         * Para cada coluna configurada como JSON cria um filtro rápido, efetuando uma requisição Ajax para recuperar opcoes em formato também json
         */
        json2Filtros: function (inJsonFiltros) {
            CB.trigger('preJson2Filtros', inJsonFiltros);

            var objFiltroRapido = "\
                <div class='btn-group' role='group' cbTab='$tab' cbCol='$col' cbRot='$rotPesquisa'>\
                    <span type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' data-content=''> \
                        <span class='txt'>$rotPesquisa</span>\
                        <span class='caret'></span>\
                        <span class='fa fa-close' title='Limpar filtro' onclick='CB.resetFiltro(\"$col\");fim(event);'></span>\
                    </span>\
                    <ul class='dropdown-menu'>\
                            <li class='resetFiltro'><a href='javascript:CB.resetFiltro(\"$col\")'>Limpar filtro<i class='fa fa-close'></i></a></li>\
                            \
                    </ul>\
                </div>";

            var bMostraFiltros = false;
            //Loop nos filtros configurados no módulo
            $.each(inJsonFiltros.colunas, function (index) {
                if (this.prompt == "json") {
                    var tmpFiltro = objFiltroRapido.replace(/\$rotPesquisa/g, (this.rotcurto || this.col)).replace(/\$col/g, this.col).replace(/\$tab/g, this.tab);
                    var tmpCol = this.col;
                    var tmpRot = (this.rotpsq || this.col);
                    //Adiciona o filtro com um evento para recuperar os itens do filtro
                    var oTmpFiltro = $(tmpFiltro).on("click", function () { CB.ativaJsonFiltro(this); });

                    /*
                     * Validação se é status. Caso seja será setado o Valor ATIVO em todos os módulos que tem ele.
                     * sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=313387 - Lidiane - 18/05/2020 
                     */
                    CB.validaStatusAtivoJsonFiltro(this);

                    CB.oFiltroRapido.append(oTmpFiltro);
                    bMostraFiltros = true;
                } else if (this.prompt == "jsonpicker") {
                    //Adiciona o filtro com um evento para recuperar os itens do filtro
                    var $tmpFiltro = CB.getJsonFiltroPicker(this);


                    CB.validaStatusAtivoJsonFiltro(this);
                    CB.oFiltroRapido.append($tmpFiltro);
                    bMostraFiltros = true;
                }
            });//$.each

            var objFiltroRapidoEntre = "\
            <div class='input-group' role='group' cbCol='' cbRot='$rotPesquisa' style='margin-left: 4px;width:229px; background: #eee;padding-left: 4px;color: #949494 !important;'>$rotPesquisa\
                <input name='$col_1' id='$col_1' class='faixaentre' type='text' value='' cbcolentre='$col_1'>\
                <label class='input-group-addon'>e</label>\
                <input name='$col_2' id='$col_2' class='faixaentre' type='text' value='' cbcolentre='$col_2'></div>";

            //Loop nos filtros configurados no módulo   
            $.each(inJsonFiltros.colunas, function (index) {

                if (this.entre == "Y") {
                    var tmpFiltro = objFiltroRapidoEntre.replace(/\$rotPesquisa/g, (this.rotcurto || this.col)).replace(/\$col/g, this.col);
                    var tmpCol = this.col;
                    var tmpRot = (this.rotpsq || this.col);
                    CB.oFiltroRapido.append(tmpFiltro);
                    bMostraFiltros = true;
                }

            });

            if (bMostraFiltros == true) {
                CB.oFiltroRapido.removeClass("hidden");
            }

            CB.trigger('posJson2Filtros', inJsonFiltros);
            //@todo: json2Filtros:  testar casos de telas sem filtro rapido
        },

        getJsonFiltroPicker: function (inCol) {
            let objFiltroRapidoPicker = `
                <div class='btn-group picker' role='group' cbTab='$tab' cbCol='$col' cbRot='$rotPesquisa' estado='inicial'>
                    <span type='button' class='btn btn-default'>
                        <span class='txt'>$rotPesquisa</span>
                        <span class='caret'></span>
                        <span class='aguarde blink'><i class='fa fa-hourglass'></i></span>
                        <span class='fa fa-close' title='Limpar filtro' onclick='CB.resetFiltro(\"$col\");fim(event);'></span>
                    </span>

                    <select class='selectpicker' id='picker_$col' multiple data-live-search='true' data-actions-box="true" data-selected-text-format="count > 1" data-count-selected-text= "{0} Selecionados">
                            
                    </select>
                </div>`;

            let tmpFiltro = objFiltroRapidoPicker
                .replace(/\$rotPesquisa/g, (inCol.rotcurto || inCol.col))
                .replace(/\$col/g, inCol.col)
                .replace(/\$tab/g, inCol.tab);

            let $oF = $(tmpFiltro);//@todo: alterar para instrucao let
            $oF.on("click", function () {
                $this = $(this);

                var sCol = $this.attr("cbcol") || "";
                var sTab = $this.attr("cbtab") || "";
                var sRot = $this.attr("cbrot") || "";

                if (sCol == "" || sTab == "") {
                    console.warn("Filtro json incompleto:");
                    console.warn(inCol);
                    return false;//@todo: verificar a consequencia do false
                }

                if ($this.attr("estado") == "inicial") {
                    $this.attr("estado", "carregando");

                    var $oDropOpt = $this.find(".selectpicker");

                    //Recupera as opções das drops em modo deferred (.when)
                    $.ajax({
                        url: "eventcode/mtotabcol/" + sTab + "__" + sCol + "__prompt.php",
                        data: "_modulo=" + CB.modulo,
                        type: 'get',
                        cache: false,
                        //dataType: "json",
                        dataType: "text",
                        beforeSend: function (jqXHR, settings) {
                            CB.trigger('preGetJsonFiltroPicker', jqXHR, settings);
                        }
                    }).done(function (data) {
                        if (data === "") {
                            console.warn("Js: json2Filtros: string json vazia: " + (this.url || ""));
                        } else {
                            data = jsonStr2Object(data);

                            //Deve-se montar um Array [], e não um objeto {}
                            if (!(data instanceof Array)) {
                                console.log("O arquivo deve retornar Array de Javascript válido. Ex: [{},{}...]");
                                alertErro("Consulte o Log de erros.", "Filtro Rápido inválido:");
                                return false;
                            }

                            //Adiciona as opções ao Filtro
                            $.each(data, function (index, value) {
                                var id = Object.keyAt(value, 0);
                                var valor = value[id];

                                //$oDropOpt.append("<li><a href='javascript:CB.filtrar({col:\""+sCol+"\",id:\""+id+"\",valor:\""+valor+"\",rot:\""+sRot+"\"})'>"+valor+"</a></li>");
                                $oDropOpt.append(`<option value="${id}" col="${sCol}" rot="${sRot}">${valor}</option>`);
                            });

                            $('#picker_' + inCol.col)
                                .selectpicker({
                                    title: inCol.rotcurto || inCol.col,
                                    selectAllText: '<span class="glyphicon glyphicon-check"></span>',
                                    deselectAllText: '<span class="glyphicon glyphicon-remove"></span>'
                                })
                                .selectpicker('toggle')
                                .selectpicker('refresh')
                                .selectpicker('render');
                            $this.attr("estado", "carregado");
                            if ($this.attr("cbId")) {
                                let values = $this.attr("cbId").split(",");
                                $('#picker_' + inCol.col).selectpicker("val", values);
                                let ul = $($this).find(".dropdown-menu .inner");
                                let listaOrdenada = $($this).find(".dropdown-menu .inner").children().sort(function (li1, li2) {
                                    let res = 0;
                                    ($(li1).hasClass("selected") && !$(li2).hasClass("selected")) ? res = -1 : res = 0;
                                    return res;
                                });
                                ul.html(listaOrdenada);
                            }
                        }
                    });

                }
            }).on("change", function () {
                let column = $(this).attr("cbcol");
                let selectedItem = $('#picker_' + inCol.col).val();
                CB.setFiltroRapidoPicker({ col: column, valor: selectedItem });
                let ul = $(this).find(".dropdown-menu .inner");
                let listaOrdenada = $(this).find(".dropdown-menu .inner").children().sort(function (li1, li2) {
                    let res = 0;
                    ($(li1).hasClass("selected") && !$(li2).hasClass("selected")) ? res = -1 : res = 0;
                    return res;
                });
                ul.html(listaOrdenada);

            });

            return $oF;
        },

        ativaJsonFiltro: function (inThis) {
            //console.log(inThis);
            var $oF = $(inThis);
            var sCol = $oF.attr("cbcol") || "";
            var sTab = $oF.attr("cbtab") || "";
            var sRot = $oF.attr("cbrot") || "";

            if ($oF.attr("estado") == "carregado") {
                console.log("Filtro json previamente carregado");
                return false;
            } else if (sCol == "" || sTab == "") {
                console.warn("Filtro json incompleto:");
                console.warn(inThis);
                return false;
            } else {
                var $oDropOpt = $oF.find(".dropdown-menu");
                $oDropOpt.append("<li class='aguardeCarregando'><span class='cinzaclaro blink'><i class='fa fa-hourglass'></i> Aguarde...</span></li>");

                //Recupera as opções das drops em modo deferred (.when)
                $.ajax({
                    url: "eventcode/mtotabcol/" + sTab + "__" + sCol + "__prompt.php",
                    data: "_modulo=" + CB.modulo,
                    type: 'get',
                    cache: false,
                    //dataType: "json",
                    dataType: "text",
                    beforeSend: function (req) {
                        //req.setRequestHeader("Pragma", "cache");
                        //req.setRequestHeader("Cache-Control","max-age=67");
                    }
                }).done(function (data) {

                    if (data === "") {
                        console.warn("Js: json2Filtros: string json vazia: " + (this.url || ""));
                    } else {

                        data = jsonStr2Object(data);

                        //Deve-se montar um Array [], e não um objeto {}
                        if (!(data instanceof Array)) {
                            console.log("O arquivo deve retornar Array de Javascript válido. Ex: [{},{}...]");
                            alertErro("Consulte o Log de erros.", "Filtro Rápido inválido:");
                            return false;
                        }

                        $oDropOpt.find(".aguardeCarregando").remove();
                        //Adiciona as opções ao Filtro
                        $.each(data, function (index, value) {
                            var id = Object.keyAt(value, 0);
                            var valor = value[id];

                            $oDropOpt.append("<li><a href='javascript:CB.filtrar({col:\"" + sCol + "\",id:\"" + id + "\",valor:\"" + valor + "\",rot:\"" + sRot + "\"})'>" + valor + "</a></li>");

                            //Verifica nas preferências do usuário se essa coluna foi previamente selecionada, e atribui o valor selecionado à dropdown correspondente
                            if (CB.jsonModulo.jsonpreferencias._filtrosrapidos[sCol] == id && CB.jsonAutoFiltro == false) {
                                //Simula seleção de opção nas dropdown de filtro rápido
                                CB.setFiltroRapido({ "col": sCol, "id": id, "valor": valor, "rot": sRot });
                            }
                        });
                        $oF.attr("estado", "carregado");
                    }
                }).fail(function (objxmlreq, ajaxOptions, thrownError) {
                    console.log("Erro inesperado");
                    return false;
                });
            }
        },

        /*
         * Função para validar se o Satus é ativo ou inativo. Caso seja será setado o Valor ATIVO em todos os módulos que tem ele.
         * sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=313387 - Lidiane - 27/04/2020 
         */
        validaStatusAtivoJsonFiltro: function (inThis) {
            var $oF = $(inThis);
            var sCol = $oF.attr("col") || "";
            var sTab = $oF.attr("tab") || "";
            var sRot = $oF.attr("rotcurto") || "";
            var sPrp = $oF.attr("prompt") || "";

            if ($oF.attr("estado") == "carregado") {
                console.log("Filtro json previamente carregado");
                return false;
            } else if (sCol == "" || sTab == "") {
                console.warn("Filtro json incompleto:");
                console.warn(inThis);
                return false;
            } else {
                //Inicializa opções


                if (CB.jsonModulo.jsonpreferencias._filtrosrapidos[sCol]) {

                    //Recupera as opções das drops em modo deferred (.when)
                    $.ajax({
                        url: "eventcode/mtotabcol/" + sTab + "__" + sCol + "__prompt.php",
                        data: "_modulo=" + CB.modulo,
                        type: 'get',
                        cache: false,
                        dataType: "text",
                        beforeSend: function (jqXHR, settings) {
                            CB.trigger('preValidaStatusAtivoJsonFiltro', jqXHR, settings);
                        }
                    }).done(function (data) {
                        if (data === "") {
                            console.warn("Js: json2Filtros: string json vazia: " + (this.url || ""));
                        } else {
                            data = jsonStr2Object(data);
                            let valores = CB.jsonModulo.jsonpreferencias._filtrosrapidos[sCol];

                            if (sPrp == "json") {
                                for (let d of data) {
                                    if (d.hasOwnProperty(valores)) {
                                        CB.setFiltroRapido({ "col": sCol, "id": valores, "valor": d[valores], "rot": sRot });
                                        break;
                                    }
                                }
                            } else if (sPrp == "jsonpicker") {
                                valores = valores.split(",");
                                let arrtmp = new Array();
                                for (let v of valores) {
                                    for (let d of data) {
                                        if (d.hasOwnProperty(v)) {
                                            arrtmp.push(d);
                                            break;
                                        }
                                    }
                                }
                                if (arrtmp.length > 0) {
                                    CB.setFiltroRapidoPicker({ col: sCol, valor: arrtmp, pref: true })
                                }

                            }

                        }
                    }).fail(function (objxmlreq, ajaxOptions, thrownError) {
                        console.log("Erro inesperado");
                        return false;
                    });

                }

            }

        },
        /*
         * Preparar Filtros rápidos pré-existentes para executar juntamente com a pesquisa
         * Parâmetros:
         * col: nome da coluna para cláusula where
         * id: valor a ser utilizado na cláusula where
         * valor: coluna descritiva do valor associado
         * rot: rótulo da coluna
         * Ex: {col:'idpessoa', id:'999', valor:'maria', rot:'Nome do Cliente'}
         */
        setFiltroRapido: function (inOpt) {

            inOpt.valor = unescape(inOpt.valor) || inOpt.id;
            inOpt.rot = unescape(inOpt.rot) || "";

            var objFiltroGrp = CB.oFiltroRapido.find("[cbcol=" + inOpt.col + "]");
            var objFiltro = objFiltroGrp.find(".btn[data-content]");

            //Armazena o valor selecionado para ser utilizado na pesquisa. Isto deve ser feito em separado da apresentação na tela, visto que a pesquisa é Assíncrona à recuperação de itens das dropdown de filtros
            //CB.jsonModulo.jsonpreferencias._filtrosrapidos[inOpt.col]=inOpt.id;

            //Atribui o valor selecionado ao item de filtro
            objFiltroGrp.attr("cbId", inOpt.id);

            //Atribui rótulo com o valor selecionado
            objFiltro.addClass("filtroAtivo")
                .find(".txt")
                .html(inOpt.valor);
            //Cria popup
            objFiltro.webuiPopover("destroy")
                .webuiPopover({
                    placement: "top-right",
                    style: "filtroRapido",
                    trigger: "hover",
                    title: 'Filtrando por <strong>' + inOpt.rot + '</strong>:',
                    content: inOpt.valor + '<span class="fa fa-close vermelho pull-right pointer" style="line-height: inherit;" title="Retirar filtro" onclick="CB.resetFiltro(\'' + inOpt.col + '\');fim(event);"></span>'
                });
            CB.ocbLimparFiltros.show();
        },

        setFiltroRapidoPicker: function (inOpt) {

            var objFiltroGrp = CB.oFiltroRapido.find("[cbcol=" + inOpt.col + "]");
            var objFiltro = objFiltroGrp.find("span.btn.btn-default");

            if (inOpt.valor === null) {
                objFiltro.parent().removeClass("filtroAtivo");
                $(`[data-id="picker_${inOpt.col}"]`).removeClass("filtroAtivoPicker");
            } else {
                CB.ocbLimparFiltros.show();
                objFiltro.parent().addClass("filtroAtivo");
                $(`[data-id="picker_${inOpt.col}"]`).addClass("filtroAtivoPicker");

                if (inOpt.pref) {
                    if (inOpt.valor.length > 1) {
                        objFiltro.find(".txt").html(`${inOpt.valor.length} Selecionados`);
                    } else {
                        let key = Object.keyAt(inOpt.valor[0])
                        objFiltro.find(".txt").html(`${inOpt.valor[0][key]}`);
                    }
                }

            }

            //Armazena o valor selecionado para ser utilizado na pesquisa. Isto deve ser feito em separado da apresentação na tela, visto que a pesquisa é Assíncrona à recuperação de itens das dropdown de filtros
            //CB.jsonModulo.jsonpreferencias._filtrosrapidos[inOpt.col]=inOpt.id;

            //Atribui o valor selecionado ao item de filtro
            if (inOpt.pref) {
                var virg = "";
                var valor = "";
                for (let v of inOpt.valor) {
                    valor += virg + Object.keyAt(v);
                    virg = ","
                }
                objFiltroGrp.attr("cbId", valor);
            } else {
                objFiltroGrp.attr("cbId", inOpt.valor);
            }

        },

        resetFiltro: function (inCol) {
            var objFiltroGrp = CB.oFiltroRapido.find("[cbcol=" + inCol + "]");
            var objFiltro = objFiltroGrp.find(".btn");

            //Limpa o valor do json de filtros rápidos do Módulo
            CB.jsonModulo.jsonpreferencias._filtrosrapidos[inCol] = "";

            //Atribui o valor (vazio) selecionado ao item de filtro
            objFiltroGrp.attr("cbId", "");

            //Recupera o rótulo de pesquisa
            var strRot = objFiltroGrp.attr("cbRot");

            //Remove o valor selecionado e atribui no rótulo de pesquisa
            objFiltro.webuiPopover("destroy")
                .removeClass("filtroAtivo")
                .find(".txt")
                .html(strRot);
            //Executa imediatamente a nova pesquisa
            //CB.pesquisar({resetVarPesquisa:true});
        },

        /*
         * Monta parametro GET com os filtros rápidos para pesquisa (todos: selecionados ou não)
         */
        NAOFUNCIONAgetFiltrosRapidos: function () {
            if (CB.jsonModulo.jsonpreferencias._filtrosrapidos === undefined) {
                console.warn("getFiltrosRapidos: Nenhum filtro encontrado em CB.jsonModulo.jsonpreferencias._filtrosrapidos");
                return "{}";
            } else {
                var dataFiltros = {};
                $.each(CB.jsonModulo.jsonpreferencias._filtrosrapidos, function (col, val) {
                    dataFiltros[col] = val;
                });
                return JSON.stringify(dataFiltros);
            }
        },
        getFiltrosRapidos: function () {
            var dataFiltros = {};
            $.each(CB.oFiltroRapido.find("[cbCol]"), function (index) {
                dataFiltros[$(this).attr("cbCol")] = $(this).attr("cbId") || "";
            });
            return JSON.stringify(dataFiltros);
        },

        getFiltrosRapidosEntre: function () {
            var dataFiltros = {};
            $.each(CB.oFiltroRapido.find("[cbcolentre]"), function (index) {
                dataFiltros[$(this).attr("cbcolentre")] = $(this).val() || "";
            });
            return JSON.stringify(dataFiltros);
        },

        getFiltrosRapidosRelatorio: function () {
            var dataFiltros = "";
            $.each(CB.oFiltroRapido.find("[cbCol]"), function (index) {
                if ($(this).attr("cbId") !== undefined) {
                    dataFiltros = dataFiltros + "&" + $(this).attr("cbCol") + "=" + $(this).attr("cbId") || "";
                }
            });
            return dataFiltros;
        },

        getFiltrosRapidosRelatorioEntre: function () {
            var dataFiltros = "";
            $.each(CB.oFiltroRapido.find("[cbcolentre]"), function (index) {
                if ($(this).val() !== undefined) {
                    dataFiltros = dataFiltros + "&" + $(this).attr("cbcolentre") + "=" + $(this).val() || "";
                }
            });
            return dataFiltros;
        },


        /*
         * Filtrar a pesquisa
         */
        filtrar: function (inOpt) {
            CB.setFiltroRapido(inOpt);
            //Executa imediatamente a nova pesquisa
            //CB.pesquisar({resetVarPesquisa:true});
        },


        /*
         * Executar pesquisa
         */
        prepararRelatorio: function (oConf, url) {
            oConf = oConf || {};
            oConf.resetVarPesquisa = oConf.resetVarPesquisa || false;

            /*
             * Indicar que será executada uma nova pesquisa, limpando parâmetros de paginação atuais
             * Geralmente utilizado para "nova pesquisa" (Ex: clique no botão de pesquisa)
             * O reset das variáveis não ocorre em caso de pesquisas paginadas
             */
            if (oConf.resetVarPesquisa === true) {
                CB.limparResultados = true;
                CB.resetVarPesquisa();
            }

            //Verifica se já existe alguma requisição de pesquisa sendo executada
            if (CB.pesquisaAjaxAtivo) {
                console.log("CB.pesquisar: Aguardando requisição término ajax anterior")
            } else {

                /* Caso não haja nenhum parâmetro Filtro/texto/data informado, a requisição não é enviada, e assim, em caso de retirada do último Filtro Rápido selecionado, este não é enviado para ser retirado das prefrências do usuário 
                if(CB.jsonModulo.psqfull=="N" &&
                        //Object.size(CB.jsonModulo.jsonpreferencias._filtrosrapidos)==0 &&
                        CB.oFiltroRapido.find("[cbCol][cbId][cbId!='']").length==0 &&
                        CB.oTextoPesquisa.val().trim().length==0 &&
                        CB.oDaterange.attr("cbdata").length==0){
                    if(oConf.resetVarPesquisa){
                        alertAtencao("Informe um parâmetro para executar a Pesquisa!");
                    }
                }else*/
                {

                    //CB.aguarde(true);

                    var strData = "";
                    var strEcom = "";

                    //A pagina de pesquisa deve ser enviada para a modulofiltrospesquisa.php
                    var vGet = "";

                    //Controle de paginação
                    //vGet = vGet+"&_pagina="+CB.pesquisaAjaxPagina;

                    //Texto digitado pelo usuario
                    var strTextoPesquisa = CB.oTextoPesquisa.val().trim();
                    vGet = (strTextoPesquisa.length > 0) ? vGet + "&_fts=" + strTextoPesquisa : vGet;

                    //Data informada pelo usuario
                    vGet = (CB.oDaterange.attr("cbdata").length > 0) ? vGet + "&_fds=" + CB.oDaterange.attr("cbdata") : vGet;

                    //Filtros rápidos informados pelo usuário
                    console.info("todo: pesquisar: verificar se o modulo possui informacao de filtros rapidos, para evitar erros ou valores vazios no GET");
                    vGet = vGet + CB.getFiltrosRapidosRelatorio() + CB.getFiltrosRapidosRelatorioEntre();

                    //Enviar para o servidor ordenação para pesquisa
                    if (CB.jsonModulo.jsonpreferencias.orderBy != undefined && CB.jsonModulo.jsonpreferencias.orderBy._ordcol) {
                        vGet = vGet + "&_ordcol=" + CB.jsonModulo.jsonpreferencias.orderBy._ordcol;
                        vGet = vGet + "&_orddir=" + CB.jsonModulo.jsonpreferencias.orderBy._orddir;
                    } else {
                        //Verifica se foi enviada ordenação pela URL, junto à algum _autofiltro
                        v_ordcol = getUrlParameter("_ordcol");
                        v_orddir = getUrlParameter("_orddir");
                        if (v_ordcol !== undefined && v_orddir !== undefined && v_ordcol !== "" && v_orddir !== "") {
                            vGet = vGet + "&_ordcol=" + v_ordcol;
                            vGet = vGet + "&_orddir=" + v_orddir;
                        }
                    }
                    let _idempresa = getUrlParameter('_idempresa');
                    if (_idempresa != '' && getUrlParameter("_idempresa", url) == "") {
                        _idempresa = '&_idempresa=' + _idempresa;
                    } else {
                        _idempresa = "";
                    }
                    janelamodal(url + _idempresa + "&" + vGet);




                }//if(CB.jsonModulo.psqfull=="N" &&
            }//if(CB.pesquisaAjaxAtivo){
        },




        /*
         * Executar pesquisa
         */
        pesquisar: function (oConf) {
            oConf = oConf || {};
            oConf.resetVarPesquisa = oConf.resetVarPesquisa || false;

            CB.trigger('prePesquisar', oConf);

            /*
             * Indicar que será executada uma nova pesquisa, limpando parâmetros de paginação atuais
             * Geralmente utilizado para "nova pesquisa" (Ex: clique no botão de pesquisa)
             * O reset das variáveis não ocorre em caso de pesquisas paginadas
             */
            if (oConf.resetVarPesquisa === true) {
                CB.limparResultados = true;
                CB.resetVarPesquisa();
            }

            //Verifica se já existe alguma requisição de pesquisa sendo executada
            if (CB.pesquisaAjaxAtivo) {
                console.log("CB.pesquisar: Aguardando requisição término ajax anterior")
            } else {

                /* Caso não haja nenhum parâmetro Filtro/texto/data informado, a requisição não é enviada, e assim, em caso de retirada do último Filtro Rápido selecionado, este não é enviado para ser retirado das prefrências do usuário 
                if(CB.jsonModulo.psqfull=="N" &&
                        //Object.size(CB.jsonModulo.jsonpreferencias._filtrosrapidos)==0 &&
                        CB.oFiltroRapido.find("[cbCol][cbId][cbId!='']").length==0 &&
                        CB.oTextoPesquisa.val().trim().length==0 &&
                        CB.oDaterange.attr("cbdata").length==0){
                    if(oConf.resetVarPesquisa){
                        alertAtencao("Informe um parâmetro para executar a Pesquisa!");
                    }
                }else*/
                {

                    CB.aguarde(true);

                    var strData = "";
                    var strEcom = "";

                    //A pagina de pesquisa deve ser enviada para a modulofiltrospesquisa.php
                    var vGet = "_modulo=" + CB.modulo + "&_cbcanal=" + CB.canal;

                    //Controle de paginação
                    vGet = vGet + "&_pagina=" + CB.pesquisaAjaxPagina;

                    //Texto digitado pelo usuario
                    var strTextoPesquisa = CB.oTextoPesquisa.val().trim().replace("-", " ").replace(/[^\u00C0-\u00C3\u00C7-\u00CE\u00D2-\u00D5\u00D9-\u00DB\u00E0-\u00E3\u00E7-\u00EE\u00F2-\u00F5\u00F9-\u00FBa-zA-Z0-9. '"]/g, "");


                    vGet = (strTextoPesquisa.length > 0) ? vGet + "&_fts=" + strTextoPesquisa : vGet;

                    //Data informada pelo usuario
                    vGet = (CB.oDaterange.attr("cbdata").length > 0) ? vGet + "&_fds=" + CB.oDaterange.attr("cbdata") + "&_fdscol=" + CB.oDaterangeCol.attr("cbdatacol") : vGet;

                    //Filtros rápidos informados pelo usuário
                    console.info("todo: pesquisar: verificar se o modulo possui informacao de filtros rapidos, para evitar erros ou valores vazios no GET");
                    vGet = vGet + "&_filtrosrapidos=" + CB.getFiltrosRapidos() + "&_registrosentre=" + CB.getFiltrosRapidosEntre();

                    let _idempresa = getUrlParameter("_idempresa");
                    if (_idempresa != "") {
                        vGet = vGet + "&_idempresa=" + _idempresa;
                    }

                    //Enviar para o servidor ordenação para pesquisa
                    if (CB.jsonModulo.jsonpreferencias.orderBy != undefined && CB.jsonModulo.jsonpreferencias.orderBy._ordcol) {
                        vGet = vGet + "&_ordcol=" + CB.jsonModulo.jsonpreferencias.orderBy._ordcol;
                        vGet = vGet + "&_orddir=" + CB.jsonModulo.jsonpreferencias.orderBy._orddir;
                        CB.oResultadosLimparOrderBy.show();
                    } else {
                        CB.oResultadosLimparOrderBy.hide();
                        //Verifica se foi enviada ordenação pela URL, junto à algum _autofiltro
                        v_ordcol = getUrlParameter("_ordcol");
                        v_orddir = getUrlParameter("_orddir");
                        if (v_ordcol !== undefined && v_orddir !== undefined && v_ordcol !== "" && v_orddir !== "") {
                            vGet = vGet + "&_ordcol=" + v_ordcol;
                            vGet = vGet + "&_orddir=" + v_orddir;
                        }
                    }

                    //Efetua a requisição
                    $.ajax({
                        type: 'get',
                        cache: false,
                        url: 'form/_modulofiltrospesquisa.php',
                        data: vGet,
                        dataType: "json",
                        beforeSend: function () {
                            //Controla fila de requisições ajax. Caso exista erro de lógica, múltiplas requisições ajax serão feitas em paralelo
                            CB.pesquisaAjaxAtivo = true;
                        },
                        success: function (data) {
                            CB.pesquisaAjaxAtivo = false;
                            //Json contem resultados encontrados?
                            if (!$.isEmptyObject(data)) {


                                if (CB.pesquisaAjaxPagina == 1) {
                                    var tblRes = CB.montaTableResultados(data);
                                } else
                                    var tblRes = CB.montaTableResultados(data, false, false);


                                if (CB.limparResultados == true) {
                                    CB.resetDadosPesquisa();
                                }

                                if (CB.pesquisaAjaxPagina == 1) {
                                    CB.pesquisaAjaxTotalPaginas = data.numpaginas;
                                }

                                if (getUrlParameter("_idempresa")) {
                                    sessionStorage.setItem('_idempresa', getUrlParameter("_idempresa"));
                                }
                                CB.oResultadosInfo.attr("numrows", data.numrows).html(data.numrows + " resultados encontrados</div>");

                                if (CB.pesquisaAjaxPagina == 1) {
                                    CB.oModuloResultados
                                        .prepend(CB.oResultadosInfo)
                                        .append(CB.fAcoesResultados.html)
                                        .append(CB.oResultadosLimparOrderBy)
                                        .append("<hr>")
                                        .append(tblRes);
                                }

                                $(".cbcolsum").webuiPopover({
                                    trigger: 'hover',
                                    placement: 'top',
                                    content: function (data) {
                                        if ($(this).attr("masc") == "M") {
                                            return `<b>Total: </b>${parseFloat($(this).attr("cbcolsum").replace(",", ".")).toLocaleString("pt-BR", { minimumFractionDigits: 2, style: 'currency', currency: 'BRL' })}`;
                                        } else if ($(this).attr("masc") == "N") {
                                            return `<b>Total: </b>${parseFloat($(this).attr("cbcolsum").replace(",", ".")).toLocaleString()}`;
                                        } else {
                                            return `<b>Total: </b>${$(this).attr("cbcolsum")}`;
                                        }
                                    }
                                });

                                /*
                                 * Monta Legenda: deve ser montada somente após a tabela de resultados ter sido finalizada.
                                 * @todo: melhorar a técnica, pois atualmente o recurso flexbox para ordenação de elementos não é suportado pelo windows 9, que ainda é utilizado
                                 */
                                //CB.oModuloResultados.find("ul.legenda").remove();
                                if (data.legenda != undefined) {
                                    CB.montaLegenda(data.legenda);
                                }

                                /*if(CB.jsonModulo.btimprimir=="Y"){
                                    CB.fAcoesResultados.find("#cbOpImprimir").removeClass("hidden");
                                }*/

                                /*
                                 * Possibilita ordenação das linhas de resultado para interagir com a coluna padrão 'ord'
                                 */
                                if (CB.jsonModulo.ordenavel === "Y") {
                                    $("#restbl tbody").sortable({
                                        update: function (event, objUi) {
                                            CB.ordenaPesquisaCarbon();
                                            //CB.pesquisar({resetVarPesquisa:true});
                                            CB.ordenar('ord', 'asc');
                                        }
                                    });
                                }

                                if (CB.pesquisaAjaxPagina == 1)
                                    CB.oModuloResultados.removeClass("hidden");
                                else
                                    tblRes.find('tbody tr').appendTo(CB.oModuloResultados.find("#restbl tbody"));

                                var thoriginal = tblRes.find('thead tr');
                                var width = {};
                                if (thoriginal.length > 0) {
                                    thoriginal.find("td").each(function (e, i) {
                                        width[e] = $(i).width();
                                    });
                                } else {
                                    $("#restbl thead tr").find("td").each(function (e, i) {
                                        width[e] = $(i).width();
                                    });
                                }
                                $(window).scroll(function (element) {
                                    var tblResTh = tblRes.find('thead');
                                    var tblResTb = tblRes.find('tbody');
                                    var isPositionFixed = (tblResTh.css('position') == 'fixed');
                                    if ($(this).scrollTop() >= 160 && !isPositionFixed) {
                                        tblResTh.css({ 'position': 'fixed', 'top': '40px', 'background-color': 'white' });
                                        tblResTb.find("td").each(function (e, i) {
                                            $(i).width(width[e])
                                        });
                                        tblResTh.find("td").each(function (e, i) {
                                            $(i).width(width[e])
                                        });
                                    }
                                    if ($(this).scrollTop() < 160 && isPositionFixed) {
                                        tblResTh.css({ 'position': 'static', 'top': '0px' });
                                        tblResTb.find("td").each(function (e, i) {
                                            $(i).width(width[e])
                                        });
                                        tblResTh.find("td").each(function (e, i) {
                                            $(i).width(width[e])
                                        });
                                    }
                                });
                            } else {
                                if ($.isPlainObject(data)) {
                                    //Um objeto json vazio retornou
                                    alertAtencao("Nenhum resultado encontrado!");
                                } else {
                                    alertErro(data);
                                }
                            }

                            CB.trigger('posPesquisar', oConf, data);
                        },
                        complete: function () {
                            CB.aguarde(false);
                            if (CB.limparResultados == true) {
                                CB.resetDadosPesquisa();
                            }
                        }
                    });
                }//if(CB.jsonModulo.psqfull=="N" &&
            }//if(CB.pesquisaAjaxAtivo){
        },
        ordenaPesquisaCarbon: function () {
            var oR = $("#restbl");                //Resultados da pesquisa
            var tdPk = oR.attr("tdpk");           //tag filho contendo o valor para update
            var tab = CB.jsonModulo.tabpesquisa;  //Tabela a ser atualizada via post
            var pk = CB.jsonModulo.pk;            //coluna pk para post

            var objetosCarbon = {};
            var il = 0;
            //Loop nos tds que contém o valor da PK
            $.each(oR.find("tbody tr td:nth-child(" + tdPk + ")"), function (i, o) {
                var $td = $(o);
                var vpk = $td.html();
                //Monta o update com a nova ordenacao
                objetosCarbon["_ord" + il + "_u_" + tab + "_" + pk] = vpk;
                objetosCarbon["_ord" + il + "_u_" + tab + "_ord"] = il;

                il++;
            });

            //Salva
            CB.post({
                objetos: objetosCarbon
                , parcial: true
                , refresh: false
                , msgSalvo: "Ordenação concluída"
            });
        },
        /*
         * Tranforma o json gerado pelo carbon em table para ser apresentado na tela
         */
        json2tr: function (inJson) {

            var tblBody = "";
            var tblHdr = ""
            var tRows = ""
            var tCols = "";
            var bgColor = "";
            var strNav = "";
            var strParget = "";
            var strEcom = "";
            var idNav = 0;
            var textAlign = {
                'L': 'text-left',
                'C': 'text-center',
                'R': 'text-right',
            };
            var tdCheckboxTH = ''
            var tdCheckboxTB = ''
            if (CB.jsonModulo.checkbox == 'Y') {
                var tdCheckboxTH = `<td class="thimpressaocheck text-center"><div>Imprimir</div><div><i class="fa fa-caret-down fa-2x azul pointer"></i></div></td>`
                var tdCheckboxTB = `<td class="tdimpressaocheck text-center"><input class="impressaocarbon" type="checkbox"></td>`
            }
            //Header
            var iH = 0;
            iColPk = null;

            $.each(inJson.cols, function (col, rotcurto) {
                var strColAsc, strColDesc, strColSum, colSumClass = "", attrMasc = "";

                //Verifica se a coluna enviada é a PK, e relacionar esta coluna com o TD de acordo com o id do array [cols] que existe dentro dos [rows] que serão montados abaixo
                //Desta maneira é possível marcar o TD como PK sem consumir recursos excessivos de comparação nos loops de row >> cols
                if (col === CB.jsonModulo.pk) {
                    iColPk = (iH + 1) + "";//Incrementar (para utilização por jquery) e converter para string para permitir comparações de null ou undefined
                }

                //Verifica se a coluna em questão foi ordenada, para ajustar o ícone
                if (CB.jsonModulo.jsonpreferencias.orderBy != undefined && CB.jsonModulo.jsonpreferencias.orderBy._ordcol != undefined && CB.jsonModulo.jsonpreferencias.orderBy._ordcol == col) {
                    strColAsc = (CB.jsonModulo.jsonpreferencias.orderBy._orddir == "asc") ? 'ativo' : '';
                    strColDesc = (CB.jsonModulo.jsonpreferencias.orderBy._orddir == "desc") ? 'ativo' : '';
                }

                if (inJson.colssum[col]) {
                    colSumClass = "cbcolsum";
                    strColSum = `cbcolsum="${inJson.colssum[col]}"`;
                }
                try {
                    if (CB.jsonModulo.colunas[col].masc) {
                        attrMasc = "masc='" + CB.jsonModulo.colunas[col].masc + "'";
                    }
                } catch (error) {

                }


                var sOrdCres = "";
                var sOrdDecr = "";


                //Texto para title conforme tipo da coluna
                try {//Evitar erros quando chamada for via popup
                    console.info("todo: pesquisar: tratar comportamento de chamadas via popup")
                    if (CB.jsonModulo.colunas[col] != undefined) {
                        if (CB.jsonModulo.colunas[col].datatype == "date" || CB.jsonModulo.colunas[col].datatype == "datetime") {
                            sOrdCres = "Mais antigos primeiro";
                            sOrdDecr = "Mais recentes primeiro";
                        } else {
                            sOrdCres = "Ordenar Crescente";
                            sOrdDecr = "Ordenar Decrescente";
                        }
                    }
                } catch (e) {
                    sOrdCres = "Ordenar Crescente";
                    sOrdDecr = "Ordenar Decrescente";
                }

                //Icones de configuracao da coluna
                strColConf = "<i id='cbOrdCres' class='fa fa-arrow-down " + strColAsc + "' title='" + sOrdCres + "' onclick=\"CB.ordenar('" + col + "','asc')\"/>" +
                    "<i id='cbOrdDecr' class='fa fa-arrow-up " + strColDesc + "' title='" + sOrdDecr + "' onclick=\"CB.ordenar('" + col + "','desc')\"/>";

                tCols += "<td col='" + col + "' " + attrMasc + " class='" + strColAsc + strColDesc + " " + colSumClass + "' " + strColSum + ">" + rotcurto + strColConf + "</td>";
                iH++;
            });
            tblHdr = "<tr class='text-center'>" + tdCheckboxTH + tCols + "</tr>";

            if (inJson.numrows == 0) {
                console.warn("json2tr: Erro: modulofiltrospesquisa retornou parametros da tabela sem nenhum resultado na pesquisa");
            }

            //Body Rows
            $.each(inJson.rows, function (i, row) {
                tCols = "";
                bgColor = "";
                strNav = "";
                idNav++;

                //Loop nas colunas pela ordem em que vieram
                $.each(row.cols, function (i, scol) {
                    let dType, sTranformed;
                    //maf260221: transformar quebras de linha em <br>, e adicionar elementos visuais para collapse
                    try {
                        dtype = CB.jsonModulo.colunas[Object.keys(inJson.cols)[i]].datatype;
                        if (dtype == "longtext") {
                            scol = `<longtext rows="all">${scol.replace(/(\n)/gm, "\n<br>")}<mostratudo><i class="fa fa-angle-down fade" title="Mostrar tudo"></i></mostratudo></longtext>`
                        }
                    } catch (e) {
                        null;
                    }
                    align = inJson.align[Object.keys(inJson.cols)[i]];
                    try {
                        masc = CB.jsonModulo.colunas[Object.keys(inJson.cols)[i]].masc;
                        if (masc == 'M') {
                            tCols += "<td class='" + textAlign[align] + "'>" + parseFloat(scol.replace(",", ".")).toLocaleString("pt-BR", { minimumFractionDigits: 2, style: 'currency', currency: 'BRL' }) + "</td>";
                        } else if (masc == 'N') {
                            tCols += "<td class='" + textAlign[align] + "'>" + parseFloat(scol.replace(",", ".")).toLocaleString() + "</td>";
                        } else {
                            tCols += "<td class='" + textAlign[align] + "'>" + scol + "</td>";
                        }
                    } catch (error) {
                        tCols += "<td class='" + textAlign[align] + "'>" + scol + "</td>";
                    }
                });

                //Altera a cor do background conforme highlights do carbon
                bgColor = (row.bgcolor) ? " style='background-color:" + row.bgcolor + "'" : "";

                //Atribui os parametros GET para o clique na linha
                strParget = "";
                strEcom = "";

                if (!row.parget) {
                    strParget = " onclick='alert(\"PARGET desconfigurado\")'";
                    strNav = "";
                } else {
                    $.each(row.parget, function (par, val) {
                        strParget += strEcom + par + "=" + val;
                        strEcom = "&";
                    });

                    strParget = " goParam='" + strParget + "'";

                    strNav = " nav=\"" + row.nav + "\" id=\"" + idNav + "\"";
                }

                //Finaliza a montagem do TR
                tblBody += "<tr" + bgColor + strParget + strNav + ">" + tdCheckboxTB + tCols + "</tr>";
            });

            //Devolve o html da tabela
            return {
                "iTdPk": iColPk,
                "tblHdr": tblHdr,
                "tblBody": tblBody
            }

        },

        montaTableResultados: function (inData, inOnClick, montaTh = true) {

            //console.log(inData, inOnClick);
            var objTr = CB.json2tr(inData);
            var iTdpk = objTr.iTdPk || "";
            var tblRes = $("<table id='restbl' tdpk='" + iTdpk + "'><thead></thead><tbody></tbody></table>");

            tblRes.addClass("table table-hover table-striped table-condensed");

            //Atribui o header à table de resultados    
            if (montaTh) {
                try {
                    if (objTr.tblHdr) {
                        tblRes.find("thead").html(objTr.tblHdr);
                    }
                } catch (e) {
                    alert("json2tr: Tags html incorretas para o THEAD. Inspecione Console de Erros");
                    console.error(objTr.tblHdr);
                }
            }

            //Atribui o body à table de resultados
            try {
                tblRes.find("tbody").html(objTr.tblBody);
            } catch (e) {
                alert("json2tr: Tags html incorretas para o TBODY. Inspecione Console de Erros");
                console.error(objTr.tblBody);
            }

            tblRes.find(".thimpressaocheck i").on('click', function (event) {
                if ($(this).prop('checkall')) {
                    $(this).removeProp("checkall")
                    $('.impressaocarbon').prop('checked', false)
                } else {
                    $(this).prop("checkall", true)
                    $('.impressaocarbon').prop('checked', true)
                }
            });
            //Verifica se vai executar ação de clique padrão ou informada pelo programador
            if (typeof inOnClick === "function") {
                tblRes.on('click', 'tbody tr', function (event) {
                    inOnClick(this, event);
                });
            } else {
                tblRes.on('click', 'tbody tr td', function (event) {
                    if (!$(this).hasClass("tdimpressaocheck")) {
                        var tmptr = $(this);

                        //Ativa o TR
                        tmptr.parent().addClass('ativo').siblings().removeClass('ativo');

                        //Verifica se vai abrir numa nova janela ou carregar via ajax
                        if (CB.jsonModulo.novajanela == "L") {//abre um link
                            CB.acao = "u";
                            janelamodal(CB.urlDestino + "?_acao=" + CB.acao + "&" + tmptr.parent().attr("goParam"));
                        } else if (CB.jsonModulo.novajanela == "M") {//abre o modulo
                            CB.acao = "u";
                            janelamodal("?_modulo=" + CB.modulo + "&_acao=" + CB.acao + "&" + tmptr.parent().attr("goParam"));
                        } else {
                            CB.acao = "u";
                            //Carrega o formulario associado
                            if (tmptr.parent().attr("goParam")) {
                                CB.go(tmptr.parent().attr("goParam"));
                            } else {
                                console.warn("Atributo goParam() não configurado no TR:");
                                console.warn(tmptr[0]);
                            }
                        }

                        //Atualiza painel de navegacao
                        //atualizaPainelNavegacao(tmptr);
                        console.info("todo: atualizaPainelNavegacao()");
                    }
                });
            }

            return tblRes;
        },
        imprimirResultados: function () {
            this.goUrlImpressao = function () {
                if (CB.jsonModulo.urlprint == "") {
                    var vUrlPrint = CB.jsonModulo.urldestino;
                } else {
                    var vUrlPrint = CB.jsonModulo.urlprint;
                }
                ids = "";
                virg = "";
                if ($("#restbl tbody").find('input:checked').length == 0) {
                    var imprimeTodos = true;
                } else {
                    var imprimeTodos = false;
                }
                $.each($("#restbl tbody tr"), function (k, v) {
                    //@512930 - NÃO CONSIGO IMPRIMIR OS RESULTADOS AGRUPADOS NO DIAGNOSTICO DO LAUDO
                    // Alteração faz como que a função deixe de pegar o idempresa ou outro parametro e
                    // contatene apenas a chave primaria do módulo 
                    if (!imprimeTodos) {
                        if ($(v).children('.tdimpressaocheck').find('input').prop('checked')) {
                            explode = $(v).attr("goparam").split('&');
                            explode.forEach(function (params) {
                                v = params.split("=");
                                if (v[1].length >= 1 && v[0] == CB.jsonModulo.pk) {
                                    ids += virg + v[1];
                                    virg = ",";
                                }
                            });
                        }
                    } else {
                        explode = $(v).attr("goparam").split('&');
                        explode.forEach(function (params) {
                            v = params.split("=");
                            if (v[1].length >= 1 && v[0] == CB.jsonModulo.pk) {
                                ids += virg + v[1];
                                virg = ",";
                            }
                        })
                    }
                });
                janelamodal(vUrlPrint + "?_vids=" + ids);
            }

            if (CB.jsonModulo.btimprimirconf == 'Y') {
                if ($("#restbl tbody").find('input:checked').length == 0) {
                    var iRes = CB.oResultadosInfo.attr("numrows");
                } else {
                    var iRes = $("#restbl tbody").find('input:checked').length;
                }

                if (parseInt(iRes) > 50) {
                    if (confirm("Deseja realmente imprimir " + iRes + " resultados?")) {
                        this.goUrlImpressao();
                    }
                } else {
                    this.goUrlImpressao();
                }
            }
        },
        ordenar: function (inColuna, inDir) {
            CB.limparResultados = true;
            CB.resetVarPesquisa();
            CB.jsonModulo.jsonpreferencias.orderBy = {};
            CB.jsonModulo.jsonpreferencias.orderBy._ordcol = inColuna;
            CB.jsonModulo.jsonpreferencias.orderBy._orddir = inDir;
            CB.pesquisar();
        },
        aguarde: function (inShowHide) {
            if (inShowHide) {
                CB.oIconePesquisando.removeClass("hidden");
                CB.oModuloPesquisa.addClass("disabled");
            } else {
                CB.oIconePesquisando.addClass("hidden");
                CB.oModuloPesquisa.removeClass("disabled");
            }
        },
        /* 
         * Ao recuperar conteúdo dinâmico (formulários via ajax) pode ocorrer erro de javascript, o que impede o jquery de prosseguir e montar os objetos html
         * Esses erros ocorrem de maneira "silenciosa" porque geram erros somente no console
         * Nesta versão estão sendo tratados 2 "tipos" de erros distintos de javascript:
         * 1 - Js include: Erros de código em scripts "externos" carregados pela tag <script src='script'></script>
         * 2 - Js inline: Erros de código dentro de tags <script>codigo</script>
         */
        validaScriptOrigemAjax: function (inData, errorStack) {
            //console.info(""+errorStack.stack);
            console.info(errorStack);
            console.info(inData);
            console.error("Carbon: validaScriptOrigemAjax: Erro de javascript ao recuperar conteúdo dinâmico (formulários via ajax). Verifique os scripts indicados");
            vData = inData;
            //Para cada script encontrado, tenta executar o carregamento e o eval, para reproduzir o erro
            var ifilter = false;
            $.each($(inData).filter("script"), function (i, script) {
                ifilter = true;
                var sSrc = $(script).attr("src") || "inline";
                //Simula o erro
                try {
                    if (sSrc != "inline") {
                        //O script externo deve ser carregado da mesma maneira como faz o jquery. Isto gera uma requisição ajax.
                        $._evalUrl(sSrc).responseText;
                    } else {
                        //O script está escrito no corpo do HTML e deve ser somente validado
                        if (vData && script.innerHTML == "") {
                            alert("Erro: Tag <script> incompleta.\nProvavelmente ocorreu erro no código do lado do Servidor e a Tag de fechamento </script> não foi alcançada.");
                            console.log("========== Últimas linhas do script: ==========\n\n" + vData.substr(-2000));
                        } else {
                            eval($(script).html());
                        }
                    }
                    if (script.innerHTML != "") {
                        console.info("Script [" + sSrc + "] não apresentou erro.");
                    }
                } catch (e) {
                    vTexto = "O script [" + sSrc + "] apresenta erro:";
                    console.warn(vTexto);
                    alert(vTexto + ":\n\n" + e);
                    //console.error(e);
                    //console.error(script);
                    //evaluate novamente no script para localização do erro
                    eval($(script).html());
                }
            })
            if (!ifilter) console.error("O método $.filter não retornou nenhum script: o Html testado provavelmente possui algum erro. Ex: tag <div> sem </div>");
            console.info($(inData));
        },
        montaLegenda: function (inDataLegenda) {

            var oPainelLeg = $("<ul class='cbLegenda'></ul>");
            $.each(inDataLegenda, function (cor, legenda) {
                oPainelLeg.append("<li><i style='background-color:" + cor + ";'></i>" + legenda + "</li>");
            });

            var sToggle = (CB.jsonModulo.jsonpreferencias.legenda != undefined && CB.jsonModulo.jsonpreferencias.legenda == "N") ? "hide" : "show";
            CB.oPanelLegenda.find("#cbPanelLegendaBody").collapse(sToggle).html(oPainelLeg);
            CB.oPanelLegenda.removeClass("hidden");
        },
        /**
         * Cria um botão Flutuante iconetexto (pode ser icone ou texto) setado de acordo com o 
         * segundo.
         * O inDataLegenda refere-se ao conteúdo que aparecerá no float
         * Modelo Solcom
         */
        montaBotaoFlutuante: function (iconetexto, tipoiconetexto, inDataLegenda) {

            let oPainelLeg;
            let qtdcabecalhoTexto = $(".cabecalhoTexto").length;
            let oPainelCabecalho = $(`#descricaobotao`);
            oPainelCabecalho.append(`<span class="cabecalhoTexto"></span>`);
            if (tipoiconetexto == 'icone' && qtdcabecalhoTexto === 0) {
                oPainelCabecalho.append(`<i class="${iconetexto}"></i>`);
            } else if (qtdcabecalhoTexto === 0) {
                oPainelCabecalho.append(`<label>${iconetexto}</label>`);
            }

            if (inDataLegenda) {
                oPainelLeg = $("<div class='cbBotaoFlutuante'></div>");
                oPainelLeg.append(inDataLegenda);
            }

            let sToggle = (CB.jsonModulo.jsonpreferencias.botaoflutuante != undefined && CB.jsonModulo.jsonpreferencias.botaoflutuante == "N") ? "hide" : "show";
            CB.oPanelLegendaBotaoFlutuante.find("#cbPanelBotaoFlutuanteBody").collapse(sToggle).html(oPainelLeg);
            CB.oPanelLegendaBotaoFlutuante.removeClass("hidden");

            if (CB.jsonModulo.jsonpreferencias.botaoflutuante == 'N') {
                $("#cbPanelBotaoFlutuante").css("height", "auto");
                $("#cbPanelBotaoFlutuante").css("width", "auto");
                $("#cbPanelBotaoFlutuante").css("overflow-y", "hidden");
                $("#cbPanelBotaoFlutuanteFechar").hide();
                $("#cbPanelBotaoFlutuanteAbrir").show();
                $('#cbSalvarComentario').hide();
            } else {
                $("#cbPanelBotaoFlutuante").css("height", "80%");
                $("#cbPanelBotaoFlutuante").css("width", "33%");
                $("#cbPanelBotaoFlutuante").css("overflow-y", "scroll");
                $("#cbPanelBotaoFlutuanteFechar").show();
                $("#cbPanelBotaoFlutuanteAbrir").hide();
                $('#cbSalvarComentario').show();
            }

            $('#cbPanelBotaoFlutuante').show();
        },
        setPrefUsuario: function (inAcao, inPath, inValue, inCallback) {
            $.ajax({
                type: 'POST',
                cache: false,
                url: "inc/php/userPref.php",
                data: "_acao=" + inAcao + "&_path=" + inPath + "&_valor=" + inValue,
                success: function (data) {
                    console.info("Preferência alterada:" + data);
                    if (inCallback && typeof (inCallback) === "function") {
                        inCallback(data);
                    }
                }
            });
        },
        setModCustomCss: function (inCss) {
            if (inCss) {
                $("<style>")
                    .html(inCss)
                    .appendTo("head");
            }
        },
        novoBotaoUsuario: function (inParam) {

            var onclick = (inParam.onclick || function () { });
            var onmouseover = (inParam.onmouseover || function () { });

            $bt = $("<button \
                    id='"+ (inParam.id || "") + "' \
                    type='button' \
                    class='btn btn-xs cbBotaoUsuario "+ (inParam.class || "") + "' \
                    title='"+ (inParam.title || "") + "' \
                    onclick='$(this).data().onclick()' \
                    onmouseover='$(this).data().onmouseover()'>\
                        <i class='"+ (inParam.icone || "") + "'></i>" + (inParam.rotulo || "") + "\
                    </button>");

            $bt.data("onclick", onclick);
            $bt.data("onmouseover", onmouseover)

            if (CB.jsonModulo.btsalvar == 'N') {
                CB.oContainer.prepend($($bt));
            } else {
                CB.oModuloHeader.append($($bt));
            }

        },
        removeBotoesUsuario: function () {
            CB.oModuloHeader.find(".cbBotaoUsuario").remove();
        },
        snippet: function (inIdsnippet) {
            if (inIdsnippet) {
                $.ajax({
                    type: 'get',
                    cache: false,
                    url: "form/_snippet.php",
                    data: "idsnippet=" + inIdsnippet,
                    success: function (data) {
                        //console.info("Preferência alterada:"+data);
                    }
                });
            }
        },
        filtrarElementos: function (inObj) {

        },
        mostraRecuperaSenha: function (inUsr, inUsrMail) {

            let suser = "";
            let usrmail;
            let hideinput;
            let hidewell;
            let lbuseremail = "Email:";
            if (inUsr) {
                suser = "<tr><td class='fonte14'>Usuário:</td><td class='bold fonte14'>" + inUsr + "</td></tr>";
            } else {
                lbuseremail = "Usuário/Email:";
            }

            if (inUsrMail) {
                usrmail = inUsrMail;
                hideinput = "hidden";
                hidewell = "";
            } else {
                usrmail = "";
                hideinput = "";
                hidewell = "hidden";
            }

            let sinfo = usrmail == "" ? "Informe abaixo o usuário ou o email cadastrado no sistema:" : "Um email será enviado para o email cadastrado:";

            var strCaixaTexto = sinfo +
                "<br/><br/><div class='form-inline'>" +
                "<div class='form-group'><table>" +
                suser +
                "<tr><td class='fonte14'>" + lbuseremail + "</td><td class='bold fonte14'><input value='" + usrmail + "' type='text' class='form-control " + hideinput + "' id='__usuarioemail' placeholder='" + lbuseremail + "'>" +
                "" + usrmail + "" +
                "  </div></td><tr><td></td><td>" +
                "  <br/><button class='btn btn-default btn-primary' onclick='CB.recuperarSenha()'><i class='fa fa-envelope'></i>Enviar Email</button>" +
                "</td></table></div>";

            $("#cbModal").modal();
            $("#cbModal #cbModalCorpo").html(strCaixaTexto);
            $("#cbModal #cbModalTitulo").html("Gerar nova senha");
        },
        recuperarSenha: function () {

            strUsuarioEmail = $("#__usuarioemail").val();
            if (strUsuarioEmail == undefined || strUsuarioEmail == "") {
                alertAtencao("Informe corretamente um usuário ou email válido!");
            } else {
                //Realiza a chamada da pagina para recuperação de senha
                $.ajax({
                    type: 'get',
                    cache: false,
                    url: 'ajax/recuperarSenhaEmail.php',
                    data: "passo=1&usuarioemail=" + strUsuarioEmail + "&modulo=recuperasenha&idobjeto=0&idpessoa=0",
                    dataType: "text",
                    beforeSend: function () {
                    },
                    success: function (data, textStatus, jqXHR) {
                        if (jqXHR.getResponseHeader("X-CB-RESPOSTA") == "1") {
                            alertAzul(data, '', 15000);
                        } else {
                            alertErro(data);
                        }
                    }
                });
            }
        },
        bloquearCamposTela: () => {
            CB.oContainer
                .css({
                    'cursorPointer': 'none'
                })
                .attr('title', 'Modo leitura')
                .addClass('leitura');

            CB.oModuloForm.find(" :input, .cbupload").each(function () {
                $(this)
                    .attr("disabled", true)
                    .addClass("desabilitado");
            });
        }
    };
}

var CB = new carbon();

/*
 * Função padrão para verificação de nomeclatura de inputs
 * Trabalha de acordo com a function.php.explodeInputNameCarbon, salvo diferenças de regex
 */
String.prototype.explodeInputNameCarbon = function () {
    var regexp = /_(\w+?)_(\w+?)_(\w+?)_(\w+)/g;
    return regexp.exec(this);
}

String.prototype.escapeForJson = function () {
    return this.replace(/"/g, '\\"').replace(/{/g, '\\{').replace(/}/g, '\\}');
}


/********************************************
 * Funções de apoio para o framework
 ********************************************/
/*
 * Controlar visualização de atividade Ajax
 */
function atividadeAjax(inAcao) {
    if (CB.oModalCarregando) {
        if (inAcao == true) {
            $(".ajaxActivity").show();
            CB.oModalCarregando.show();
        } else {
            $(".ajaxActivity").hide();
            CB.oModalCarregando.hide();
        }
    }
}

/*
 * Mostrar mensagem de carregamento/termino para requisições Ajax
 */
$(document).ajaxSend(
    function (event, jqxhr, settings) {
        //@487013 - MULTI EMPRESA
        if (!settings.url.includes("?")) {
            settings.url += "?";
        }
        //Concatenar _idempresa em todas as requisições ajax posteriores requisicao original
        s_idempresa = getUrlParameter("_idempresa");
        if (s_idempresa) {
            jqxhr.setRequestHeader("_idempresa", s_idempresa);
            settings.url = alteraParametroGet("_idempresa", s_idempresa, settings.url)
        }

        let token = Cookies.get('jwt') || localStorage.getItem("jwt") || "";
        if (token) {
            jqxhr.setRequestHeader("authorization", token);
        }
        //Carregar o id do usuario para extracao de relatorios do servidor web
        if (typeof gIdpessoa == "string" && gIdpessoa.length > 1) {
            jqxhr.setRequestHeader("cb-idpessoa", gIdpessoa);
        }

        if (typeof gCbCanal == "string" && gCbCanal.length > 1) {
            jqxhr.setRequestHeader("cb-canal", gCbCanal);
        }
    }
).ajaxStart(
    function () {
        // alertAguarde();
        atividadeAjax(true);
    }

).ajaxStop(
    function (event, xhr, options) {
        $(".aguarde").remove();//Isto remove o elemento e impede a animação de movimentação para cima

        if (CB.oTextoPesquisa && CB.oTextoPesquisa.is(":visible") && CB.pesquisaAjaxPagina == 1) {
            //Força o cursor para o fim do texto do input
            tmpVal = CB.oTextoPesquisa.val();
            //CB.oTextoPesquisa.focus().val("").blur().focus().val(tmpVal);
        }
        atividadeAjax(false);
        if (typeof CB.oCarregando !== "undefined") {
            CB.oCarregando.hide();
        }
    }
).ajaxError(function (event, request, settings) {

    atividadeAjax(false);

    var strErro, abrirUrl;

    if (request.status == 200 && request.getResponseHeader("X-CB-RESPOSTA") == "0" && request.getResponseHeader("X-CB-FORMATO") == "alert") {
        alertAtencao(request.responseText);

    } else if (request.status == 200 && request.readyState == 4) {
        alertErro("Formatação de Json inválida! Consultar console de erros.");
        //@todo: incluir validador de json $._evalUrl("inc/js/jsonValidator/ajv.min.js").responseText;
        console.warn("Falha ao recuperar json: " + settings.url);
        console.error(request.responseText);
        alertErro(`<pre>${request.responseText}</pre>`);

    } else if (request.status == 500) {
        strErro = "Erro 500: Falha no código na URL requisitada:<br>" + settings.url + "<br>";
        abrirUrl = "<p class='text-align-right'><a href='" + settings.url + "' target='_blank' class='btn btn-default btn-sm'>Abrir url</a></p>";
        alertErro(strErro + abrirUrl);

    } else if (request.status == 520) {
        console.error(request.responseText);
        alertErro("Consulte o log de Erros!");

    } else if (request.status == 401 && request.getResponseHeader("X-CB-RESPOSTA") == "0" && request.getResponseHeader("X-CB-FORMATO") == "login") {
        alertAtencao(request.responseText);
    } else if (request.status == 401) {
        strErro = "Você não está logado!<br>";
        abrirUrl = "<a href='javascript:janelamodal(\"?_modulo=_login\")'>Clique aqui para fazer o login novamente.</a>"
        alertErro(strErro + abrirUrl);

    } else if (request.status == 404) {
        strErro = "Erro 404: Arquivo não encontrado:<br>" + settings.url + "<br>";
        abrirUrl = "<p class='text-align-right'><a href='" + settings.url + "' target='_blank' class='btn btn-default btn-sm'>Abrir url</a></p>"
        alertErro(strErro + abrirUrl);

    } else if (request.statusText == "abort" && settings.backgroundMessage) {
        alertAzul(settings.backgroundMessage);
        console.log("Requisição ajax abortada");
    } else {
        strErro = "ajaxError: Resultado da Requisição [" + settings.dataType + "]: " + request.status + " - " + request.statusText;
        console.log(strErro);
        alertErro(request.responseText);
    }

});

/*
 * Detecção de scroll vertical da pagina, para recuperar registros de forma paginada, caso o formulário não esteja visível
 */
$(window).bind('scroll', function (ev) {

    if (typeof CB.oModuloForm !== "undefined" && CB.oModuloForm.hasClass("hidden")) {
        //Altura do viewport
        var clientHeight = document.body.clientHeight;
        //Altura do documento
        var windowHeight = $(this).outerHeight();
        //Top position
        var scrollY = $(this).scrollTop();

        //Efetua a paginação
        var pontoMudanca = (clientHeight - windowHeight) / 1.5;
        if (scrollY >= pontoMudanca) {
            if (!CB.pesquisaAjaxAtivo) {
                if (CB.pesquisaAjaxPagina < CB.pesquisaAjaxTotalPaginas) {
                    CB.pesquisaAjaxPagina++;
                    CB.pesquisar();
                } else {
                    console.log("window.scroll: máximo de páginas [" + CB.pesquisaAjaxTotalPaginas + "] atingido. nenhuma ação.");
                }
            } else {
                console.log("window.scroll: ajax ativo. nenhuma ação.");
            }
        }
    }
});

/*
 * Detecção de ESC para fechar formulário
 */
$(document).keydown(function (e) {
    if (e.keyCode == 27) {//Esc

        console.info("$(document).keydown: Verificar em que condições executar o fecharform() para que os resultados vazios não sejam mostrados desnecessariamente");

        //maf190220: Verifica se o formulario foi chamado dentro de um modal (popup). Neste caso deve-se fechar o modal chamador (caller)
        if (window.frameElement) {
            //Recupera o modal pai (caller)
            $oModalCaller = window.parent.CB.oModal;
            if ($oModalCaller.hasClass('in') && $oModalCaller.data("parametros").url !== "") {
                $oModalCaller.modal("hide");
            } else {
                console.log("ESC: nenhuma ação prevista: oModal sem class[in] ou url vazia");
            }
        }

        //Se nenhum modal ter sido instanciado, ou algum tiver sido instanciado e estiver sendo mostrado na tela
        if (!($("#cbModal").data('bs.modal') || {}).isShown) {
            CB.fecharForm();
            //console.log(moment(new Date)+": "+$("#cbModal").data('bs.modal').isShown);
        }
    }
});

/*
 * Executar cbpost com [ctrl]+[s]
 */
$(document).keydown(function (event) {

    //PHOL 08/08/2022 Removido event.wich da condição abaixo para não disparar CB.post() ao apertar a tecla pause/break
    if (!(String.fromCharCode(event.which).toLowerCase() == 's' && (event.ctrlKey || event.altKey))) return true;
    //MCC 20/11/2019 IMPLEMENTAÇÃO PARA O CTRL + S DO EVENTO MENU LATERAL 
    if ($('#example').hasClass('is-visible')) return false;
    CB.post();

    event.preventDefault();
    return false;
});

/*
 * Novo registro [ctrl]+[+]
 */
$(document).keydown(function (event) {

    if (!((event.altKey || event.ctrlKey) && event.which == 187)) return true;

    CB.novo();

    event.preventDefault();
    return false;
});

(function ($) {
    $.fn.replaceClass = function (pFromClass, pToClass) {

        if (!this.hasClass(pFromClass)) {
            return false;
        }

        return this.removeClass(pFromClass).addClass(pToClass);
    };
}(jQuery));