<script>
    //------- Injeção PHP no Jquery -------
    jFunc = <?= json_encode($jFunc) ?> || 0; // autocomplete Funcinario
    jForn = <?= json_encode($jForn) ?> || 0; //autocomplete Fornecedor
    jpag = <?= json_encode($jPag) ?> || 0; ////autocomplete de Pagamentos
    idcotacao = '<?= $_1_u_cotacao_idcotacao ?>';
    idempresa = '<?= $_1_u_cotacao_idempresa ?>';
    tiponf = '<?= $_1_u_cotacao_tiponf ?>';
    var jsonitens = <?= json_encode($infoNf['fillSelectNovoItem']); ?>;
    var proposta = [<?= $arrprop ?>];
    var acao = '<?= $_acao ?>';
    var grupo_es = '<?= $idcontaitemSelected ?>';
    var qtdProdSolcom = '<?= $qtdProdSolcom ?>';
    const referenciaCotacao = '<?= $_1_u_cotacao_referencia ?>';

    let dadosForcast = {};

    <? if ($idcontaitemSelected) { ?>
        buscarContaItem(grupo_es)
    <? } ?>

    var _idpk = $('[name="_1_u_cotacao_idcotacao"]').val() || getUrlParameter('idcotacao') || "";

    //------- Injeção PHP no Jquery -------

    //------- Variáveis Globais -------
    //------- Variáveis Globais -------

    //------- Exececuções para Carregar o módulo ----------

    carregaPreferenciasJson(_idpk, CB.jsonModulo.jsonpreferencias);

    //ler teclas digitas
    $(document).ready(function() {
        var vstatus = $("#status").val();
        // listens for any navigation keypress activity
        $(document).keypress(function(e) {
            if (e.keyCode == 13) { //Enter 
                //se for um desses ai onde esta o target chama a função
                if (e.target.id == 'nome' || e.target.id == 'descri') {
                    getDadosATualizaInfoAbas('cotacao_todos');
                    e.preventDefault();
                }
                //senão desabilita o enter
                if (vstatus == "INICIO") {
                    e.preventDefault();
                }
            }
        });
    });

    CB.on("prePost", function(inParam) {
        if (inParam === undefined) {
            inParam = {
                objetos: {},
                parcial: false
            };
        }

        if (inParam.objetos["STenviarstatus"] || inParam.parcial !== true) {
            $("input[name^='_1_u_cotacao']").each((i, e) => {
                inParam.objetos[$(e).attr("name")] = (!$(e).attr("cbvalue")) ? $(e).val() : $(e).attr("cbvalue");
            });

            $("input[name^='_1_i_cotacao']").each((i, e) => {
                inParam.objetos[$(e).attr("name")] = (!$(e).attr("cbvalue")) ? $(e).val() : $(e).attr("cbvalue");
            });

            inParam.objetos['sel_picker_idcontaitemtipoprodserv'] = $("#sel_picker_idcontaitemtipoprodserv").val();
            inParam.objetos['sel_picker_idcontaitem'] = $('#sel_picker_idcontaitem').val();

            inParam.parcial = true;
            return inParam;
        }
    });

    $('.qtdProdSolicitacao').text(qtdProdSolcom ? qtdProdSolcom : 0);
    $('.badgesolcom').text(qtdProdSolcom ? qtdProdSolcom : 0);

    CB.preLoadUrl = function() {
        //Como o carregamento é via ajax, os popups ficavam aparecendo após o load
        $(".webui-popover").remove();
    }
    //------- Exececuções para Carregar o módulo ----------    

    //------- Funções JS -------
    //mapear autocomplete de clientes
    jFunc = jQuery.map(jFunc, function(o, id) {
        return {
            "label": o.nomecurto,
            value: id + ""
        }
    });

    //mapear autocomplete de fornecedores
    jForn = jQuery.map(jForn, function(o, id) {
        return {
            "label": o.nome,
            value: id + ""
        }
    });

    jpag = jQuery.map(jpag, function(o, id) {
        return {
            "label": o.descricao,
            value: id
        }
    });

    $("[name*=_cotacao_idresponsavel]").autocomplete({
        source: jFunc,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
            };
        }
    });

    $("[name*=_f_nome]").autocomplete({
        source: jForn,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
            };
        },
        select: function(event, ui) {
            pesquisaprod_forn(ui.item.value);
        }
    });

    $(".forma_pagamento").autocomplete({
        source: jpag,
        delay: 0,
        select: function(event, ui) {},
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
            };
        }
    });

    $(".modalObsInternaClique").click(function() {
        let idsolcomitem = $(this).attr('idsolcomitem');
        let obsinterna = $(".modalobsinterna" + idsolcomitem).html();
        CB.modal({
            corpo: obsinterna,
            titulo: "Observação",
            classe: 'quarenta'
        });
    });

    $(".modalMigrarCotacaoClique").click(function() {
        let idnf = $(this).attr('idnf');
        let migrarcotacao = $(`.modalmigrarcotacao${idnf}`).html();
        CB.modal({
            corpo: migrarcotacao,
            titulo: "Migrar Cotação",
            classe: 'quarenta'
        });
    });

    $(".modalObservacaoClique").click(function() {
        let idnfitem = $(this).attr('idnfitem');
        let observacao = $(`#modalObservacao${idnfitem}`).html();
        CB.modal({
            corpo: observacao,
            titulo: `Observação <button type="button" class="btn btn-success btn-xs" onclick="salvarObservacao(${idnfitem})" title="Salvar" style="float: right; margin-top: 14px; margin-right: 8px; display: block;">
                                 <i class="fa fa-circle"></i>Salvar</button>`,
            classe: 'quarenta'
        });
    });

    $('.alterarDtEmissao').on('apply.daterangepicker', function(ev, picker) {
        let idnf = $(ev.currentTarget).attr('idnf');
        setdt(idnf, picker.startDate.format('DD/MM/YYYY'));
    });

    $(".modalProdServ").click(abrirModalProdserv);

    $(".itensSemelhantes").each(function(index, elemento) {
        $(this).webuiPopover({
            trigger: "click",
            placement: "bottom-left",
            width: 500,
            delay: {
                show: 300,
                hide: 0
            }
        });
    });


    $(".historicocompras").on("click", function(index, elemento) {
        vthis = $(this)
        nfitem = vthis.attr('idnfitem')
        idprodserv = vthis.attr('idprodserv')
        if ($(`#target-${nfitem}`).html().length == 0) {
            vthis.find('a').addClass('blink')
            $.ajax({
                type: "GET",
                async: false,
                url: `ajax/historicocomprasprodserv.php?idprodserv=${idprodserv}`,
                success: function(data) {
                    $(`#target-${nfitem}`).append(data)
                    vthis.webuiPopover({
                        trigger: "click",
                        placement: "bottom-left",
                        width: 500,
                        delay: {
                            show: 300,
                            hide: 0
                        }
                    });
                    setTimeout(() => {
                        vthis.find('a').removeClass('blink')
                        vthis.click()
                    }, 500)
                }
            });
        }
    });

    if (proposta !== []) {
        proposta.map(x => {
            $(`#propostaanexa_${x}`).webuiPopover({
                url: `#content_${x}`
            });
        });
    }

    $('#picker_contaitemprodserv').selectpicker('render');
    $('#picker_contaitemprodserv').on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue) {
        if ($(e.currentTarget).val()) {
            $('#sel_picker_idcontaitemtipoprodserv').val($(e.currentTarget).val().join());
            $('.altertaSalvar').removeClass('hidden');

        } else {
            $('#sel_picker_idcontaitemtipoprodserv').val('');
        }
    });

    $('#picker_grupoes').selectpicker('render');
    $('#picker_grupoes').on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue) {
        if ($(e.currentTarget).val()) {
            $('#sel_picker_idcontaitem').val($(e.currentTarget).val().join());
            idcontaitem = $(e.currentTarget).val().join();
        } else {
            $('#sel_picker_idcontaitem').val('');
            idcontaitem = '';
        }

        buscarContaItem(idcontaitem);
    });

    if ($("[name=_1_u_cotacao_idcotacao]").val()) {
        $(".cbupload").dropzone({
            idObjeto: $("[name=_1_u_cotacao_idcotacao]").val(),
            tipoObjeto: 'cotacao',
            idPessoaLogada: '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>'
        });
    }

    $("input[name*='_nfitem_previsaoentrega']").on('apply.daterangepicker', function(ev, picker) {
        $(this).html(picker.startDate.format("DD/MM/YYYY") || "");
        var previsao = picker.startDate.format("DD/MM/YYYY") || "";
        var idnf = $(this).attr("_idnf") || "";
        var idnfitem = $(this).attr("_idnfitem") || "";

        if (previsao != "" && idnf != "" && idnfitem != "") {
            CB.post({
                objetos: "_iX_u_nfitem_idnfitem=" + idnfitem + "&_iX_u_nfitem_previsaoentrega=" + previsao + "&_nX_u_nf_idnf=" + idnf + "&_nX_u_nf_previsaoentrega=" + previsao,
                parcial: true,
                refresh: false,
                msgSalvo: "Atualizada Previsão"
            });
        } else {
            alertAtencao('Não foi possível adicionar previsão de entrega');
            console.warn('verifique o valor dos parâmetros da função');
        }
    }).on("change", function(ev, picker) {
        var previsao = $(this).val();
        var idnf = $(this).attr("_idnf") || "";
        var idnfitem = $(this).attr("_idnfitem") || "";

        if (previsao != "" && idnf != "" && idnfitem != "") {
            CB.post({
                objetos: "_iX_u_nfitem_idnfitem=" + idnfitem + "&_iX_u_nfitem_previsaoentrega=" + previsao + "&_nX_u_nf_idnf=" + idnf + "&_nX_u_nf_previsaoentrega=" + previsao,
                parcial: true,
                refresh: false,
                msgSalvo: "Atualizada Previsão"
            });
        } else {
            alertAtencao('Não foi possível adicionar previsão de entrega');
            console.warn('verifique o valor dos parâmetros da função');
        }
    });

    $("input[name*='_nfitem_validade']").on('apply.daterangepicker', function(ev, picker) {
        $(this).html(picker.startDate.format("DD/MM/YYYY") || "");
        var validade = picker.startDate.format("DD/MM/YYYY") || "";
        let previsaoentrega = $(this).attr('name').replace('validade', 'previsaoentrega');
        let previsao = $(`[name=${previsaoentrega}]`).val();
        let idnf = $(`[name=${previsaoentrega}]`).attr('_idnf') || "";
        let idnfitem = $(`[name=${previsaoentrega}]`).attr("_idnfitem") || "";

        if (previsao != "" && idnf != "" && idnfitem != "") {
            CB.post({
                objetos: "_iX_u_nfitem_idnfitem=" + idnfitem + "&_iX_u_nfitem_validade=" + validade,
                parcial: true,
                refresh: false,
                msgSalvo: "Atualizada Validade"
            });
        } else {
            alertAtencao('Não foi possível adicionar Validade');
            console.warn('verifique o valor dos parâmetros da função');
        }
    }).on("change", function(ev, picker) {
        var validade = $(this).val();
        var idnf = $(this).attr("_idnf") || "";
        var idnfitem = $(this).attr("_idnfitem") || "";

        if (validade != "" && idnf != "" && idnfitem != "") {
            CB.post({
                objetos: "_iX_u_nfitem_idnfitem=" + idnfitem + "&_iX_u_nfitem_validade=" + validade,
                parcial: true,
                refresh: false,
                msgSalvo: "Atualizada Validade"
            });
        } else {
            alertAtencao('Não foi possível adicionar Validade');
            console.warn('verifique o valor dos parâmetros da função');
        }
    });

    //Setar todos os elementos que está com status cancelado como oculto com preferencia para oculto - Fábio solicitou - 362861 - (LTM - 27-07-2020)
    $.each(CB.oModuloForm.find("[data-toggle=collapse]"), function(i, o) {
        $o = $(o);
        var shref = $o.attr("href");
        $oc = $(shref);
        selecao = $oc.selector;

        if ($(selecao + "iddiv").val() == "REPROVADO" || $(selecao + "iddiv").val() == "CANCELADO") {
            var idmodulo = selecao.replace("#", "");
            //CB.setPrefUsuario('m','{"cotacao":{"collapse":{"'+idmodulo+'":"Y"}}}');
            $oc.removeClass("collapse in");
            $oc.addClass("collapse");
        }
    });

    $(document).ready(function() {
        $("#inputFiltro").on("keyup", function() {
            var value = $(this).val().toLowerCase().normalize("NFD").replace(/[^a-zA-Zs]/, "");
            $(".cotacaoabas tbody tr").filter(function() {
                let seletor = $(this).attr("data-text");
                if (!seletor) {
                    seletor = $(this).text();
                }
                $(this).toggle(seletor.toLowerCase().normalize("NFD").replace(/[^a-zA-Zs]/, "").indexOf(value) > -1)
            });

            $(".cotacaoItensNota div").not('.webui-popover-content, .grupo_es_oculto').filter(function() {
                let seletor = $(this).attr("data-text");
                if (!seletor) {
                    seletor = $(this).text();
                }
                $(this).toggle(seletor.toLowerCase().normalize("NFD").replace(/[^a-zA-Zs]/, "").indexOf(value) > -1)
            });

            $(".cotacaoabas .esconderdiv").each(function() {
                if (value.length > 0) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        });
    });
    //------- Funções JS -------

    //------- Funções Módulo -------
    function cancelarItemSolcom(multi, idsolcomitem) {
        let objeto = "";
        i = 1;
        enviar = false;
        if (multi == 'true') {
            let idsolcomitem;
            $('.itemsolcom').each(function(key, val) {
                if (val.checked) {
                    objeto += "&_sia" + i + "_u_solcomitem_idsolcomitem=" + $(val).attr('idsolcomitem') + "&_sia" + i + "_u_solcomitem_status=REPROVADO&solcom=Y";
                    i++;
                    enviar = true;
                }
            });
        } else {
            objeto += "_sia" + i + "_u_solcomitem_idsolcomitem=" + idsolcomitem + "&_sia" + i + "_u_solcomitem_status=REPROVADO&solcom=Y";
            enviar = true;
        }

        if (enviar) {
            CB.post({
                objetos: objeto,
                parcial: true,
                refresh: 'refresh'
            });
        } else {
            alert('Nenhum item foi selecionado.');
        }
    }

    function checkallSolcom(check) {
        if (check.checked)
            $('.itemsolcom').prop('checked', 'checked');
        else
            $('.itemsolcom').removeProp('checked');
    }

    function addSolcom(multi, idsolcomitem) {
        let arrIdsolcom = [];

        objeto = `&_si_u_cotacao_idcotacao=${idcotacao}&_si_u_cotacao_tiponf=${tiponf}`;
        if (multi == 'true') {
            $('.itemsolcom').each(function(key, elem) {
                if (elem.checked) {
                    arrIdsolcom.push($(elem).attr('idsolcomitem'));
                    idprodserv = $(elem).attr('idprodserv');
                    itemalerta_forn = ($(`#cotacao_solcom #itemalerta_forn_${idprodserv}`).val()) ? $(`#cotacao_solcom #itemalerta_forn_${idprodserv}`).val() : "";
                    objeto += `&itemalerta_forn_${idprodserv}=${itemalerta_forn}&`;
                }
            });
            idsolcomitem = arrIdsolcom.join();
        } else {
            idsolcomitem = idsolcomitem;
        }

        objeto += `&idsolcomitem=${idsolcomitem}`;

        if (idsolcomitem.length > 0) {
            CB.post({
                objetos: objeto,
                parcial: true,
                refresh: 'refresh'
            });
        } else {
            alert('Nenhum item foi selecionado.');
        }
    }

    function esconderMostrarTodosFornecedores(tipo) {
        let state = $("#esconderMostrarTodosFornecedores").attr('state');
        let allCollapses = (state == 'Y' || state == undefined) ? 'N' : 'Y';
        let atualizarTodasProdservCollapse = "";
        CB.setPrefUsuario('m', `{"${CB.modulo}":{"collapse":{"prodservtotal":"${allCollapses}"}}}`);

        $("#esconderMostrarTodosFornecedores").attr('state', allCollapses);

        $(".cotacao_todos_fornecedores").each(function() {
            if (tipo == 'prodservsolcom') {
                idprodserv = $(this).attr('idprodserv') + '_' + $(this).attr('idsolcom');
            } else {
                idprodserv = $(this).attr('idprodserv');
            }

            if (idprodserv != undefined) {
                atualizarTodasProdservCollapse += `"${tipo}${idprodserv}":"${allCollapses}",`;
                if (allCollapses == 'N') {
                    $(`.${tipo}${idprodserv}`).removeClass("collapse");
                    $(`.${tipo}${idprodserv}`).addClass("collapse in");
                    if ($(`#prodservPrincipal${idprodserv}`).length > 0) {
                        if ($(`#prodservPrincipal${idprodserv}`).attr('style').indexOf('display: none') < 0) {
                            $(`.${tipo}${idprodserv}`).css("display", "");
                        }
                    }
                } else {
                    $(`.${tipo}${idprodserv}`).removeClass("collapse in");
                    $(`.${tipo}${idprodserv}`).addClass("collapse");
                    if ($(`#prodservPrincipal${idprodserv}`).length > 0) {
                        if ($(`#prodservPrincipal${idprodserv}`).attr('style').indexOf('display: none') < 0) {
                            $(`.${tipo}${idprodserv}`).css("display", "none");
                        }
                    }
                }
            }
        });

        //Enviado todos de Uma vez, pois quando tinha mais de 2000 itens o Chrome não suportou quando enviado um a um.
        atualizarTodasProdservCollapse = atualizarTodasProdservCollapse.substring(0, atualizarTodasProdservCollapse.length - 1);
        CB.setPrefUsuario('m', `{"${CB.modulo}":{"collapse":{${atualizarTodasProdservCollapse}}}}`);
    }

    //Adiciona os Produtos em Alerta
    function addProdutoAlerta(multi, tipo, idprodservalerta) {
        let checked = false;
        let cbpost = false;
        objeto = `_si_u_cotacao_idcotacao=${idcotacao}&_si_u_cotacao_tiponf=${tiponf}&multi=${multi}`;
        if (multi == 'true') {
            idprodservalerta = "";
            idprodservalertaqtd = "";
            $('#' + tipo + ' #itemalerta').each(function(key, val) {
                idprodserv = $(val).attr('idprodservalerta');
                if (val.checked && $('#' + tipo + " #itemalertaqtd" + idprodserv).val() > 0) {
                    itemalerta_forn = ($('#' + tipo + " #itemalerta_forn_" + idprodserv).val()) ? $('#' + tipo + " #itemalerta_forn_" + idprodserv).val() : "";
                    idprodservalerta += idprodserv;
                    idprodservalerta += ',';
                    objeto += `&idprodservalertaqtd${idprodserv}=${$('#'+tipo+" #itemalertaqtd"+idprodserv).val()}&itemalerta_forn_${idprodserv}=${itemalerta_forn}&`;
                    cbpost = true;
                } else if (val.checked && $('#' + tipo + " #itemalertaqtd" + idprodserv).val() <= 0) {
                    cbpost = false;
                    checked = true;
                }
            });

            //Verifica se tem prodserv para enviar POST
            if (idprodservalerta) {
                objeto += `&idprodservalerta=${idprodservalerta}`;
            }

        } else {
            if ($('#' + tipo + " #itemalertaqtd" + idprodservalerta).val() > 0) {
                objeto += `&idprodservalerta=${idprodservalerta}&idprodservalertaqtd${idprodservalerta}=${$('#'+tipo+" #itemalertaqtd"+idprodservalerta).val()}&`;
                cbpost = true;
            } else {
                alert('Selecione a Quantidade');
                cbpost = false;
            }
        }

        if (cbpost == true) {
            CB.post({
                objetos: objeto,
                parcial: true,
                refresh: 'refresh'
            });
        } else if (checked == true) {
            alert('Verifique se os campos Quantidade estão preenchidos');
        }
    }

    function marcarTodosProdutoAlerta(check) {
        if (check.checked)
            $('.itemalerta').prop('checked', 'checked');
        else
            $('.itemalerta').removeProp('checked');
    }

    function alterarContaItem(vthis, idnfitem) {
        let idcontaitem = $(vthis).val();
        $.ajax({
            type: "POST",
            async: false,
            url: "ajax/cotacao_ajax.php",
            data: {
                "idcontaitem": idcontaitem,
                "comandoExecutar": "atualizarContaItemProdservSemCadastro"
            },
            success: function(data) {
                $(`.modal_idtipoprodserv${idnfitem}`).append(data);
            }
        });
    }

    function duplicarcompra(vidnf) {
        var strCabecalho = `</strong>Duplicar Cotação ${vidnf} <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='geranota(${vidnf});'><i class='fa fa-circle'></i>Salvar</button></strong>`;
        $("#cbModalTitulo").html((strCabecalho));

        var htmloriginal = $("#compra" + vidnf).html();
        var objfrm = $(htmloriginal);
        objfrm.find("#_f_nome" + vidnf).attr("id", "x_f_nome");
        $("#cbModalCorpo").html(objfrm.html());
        $('#cbModal').modal('show');
    }

    function geranota(vidnf) {
        var htmloriginal = $("#cbModalCorpo").html();
        var objfrm = $(htmloriginal);
        var varinput = objfrm.find(":input").serialize();

        var instr = varinput.split("__").join("#");
        var novofornecedor = $("#x_f_nome").attr("cbvalue");

        CB.post({
            objetos: `_x_u_nf_idnf=${vidnf}&duplicar=Y&${instr}&idpessoa=${novofornecedor}`,
            parcial: true,
            posPost: function(resp, status, ajax) {
                if (status = "success") {
                    $("#cbModalCorpo").html("");
                    $('#cbModal').modal('hide');
                } else {
                    $("#cbModalCorpo").html("");
                    $('#cbModal').modal('hide');
                    alert(resp);
                }
            }
        });
    }

    function abrirModalProdserv(event) {
        let vthis = event.currentTarget;
        let $header = '';
        let idprodserv = $(vthis).attr('idprodserv');
        let modulo = $(vthis).attr('modulo');

        if (modulo == 'prodserv') {
            $header = 'Produto e Serviço';
        } else if (modulo == 'calculosestoque') {
            $header = 'Calculos Estoque';
        } else {
            $header = 'Fornecedor';
        }
        CB.modal({
            url: `?_modulo=${modulo}&_acao=u&idprodserv=${idprodserv}`,
            header: $header,
            classe: 'noventa'
        });
    }

    function alterarCotacao(vidnf, vthis) {
        CB.post({
            objetos: `_1_u_nf_idnf=${vidnf}&_1_u_nf_idobjetosolipor=${$(vthis).val()}&_migrar_cotacao=Y`,
            parcial: true
        })
    }

    function setdt(inid, indate) {
        CB.post({
            objetos: `_x_u_nf_idnf=${inid}&_x_u_nf_dtemissao=${indate}`,
            parcial: true,
            refresh: false,
            msgSalvo: "Atualizada Emissão"
        });
    }

    function salvarNf(vidnf, i) {
        //pega todos os inputs checkados 		
        var inputprenchido = $("#nftable" + vidnf).children().find('input,select,textarea').not('.calendario,.forma_pagamento,[name*="nfitem_prodservdescr"]');
        var idformapagamento = '_' + i + '_u_nf_idformapagamento=' + $("#formapagamento" + vidnf).attr('cbvalue');
        //pega todos os input e os inputs checkados e transforma em string para enviar para submit 		
        var envsubmit = decodeURIComponent($(inputprenchido).parent().parent().find('input,select, textarea').not('.calendario,.forma_pagamento,[name*="nfitem_prodservdescr"]').serialize());
        var esubmit = idformapagamento + '&' + envsubmit;
        var vsubmit = esubmit.replace(/\+/g, ' ');
        console.log(vsubmit);

        CB.post({
            objetos: vsubmit,
            parcial: true,
            refresh: false,
            msgSalvo: "Item Salvo"
        })
    }

    function mostrarModalGrupoES(idnfitem, descricao, i) {
        if (descricao.length > 60) {
            desc = descricao.substring(0, 60).concat('...');
        } else {
            desc = descricao;
        }

        let objhtml = $($("#tb_" + idnfitem).html());

        let contaitem = objhtml.find("#idcontaitem" + idnfitem);
        let tipoprodserv = objhtml.find("#idtipoprodserv" + idnfitem);

        contaitem.attr({
            'name': `_${i}_u_nfitem_idcontaitem`,
            'onchange': `atribuirValorSelect(this,${idnfitem},'contaitem')`
        });

        tipoprodserv.attr({
            'name': `_${i}_u_nfitem_idtipoprodserv`,
            'onchange': `atribuirValorSelect(this,${idnfitem},'tipoprodserv')`,
            'class': `modal_idtipoprodserv${idnfitem}`
        });

        contaitem.removeAttr('id');
        tipoprodserv.removeAttr('id');

        contaitem.find('option:selected').removeAttr('selected');
        tipoprodserv.find('option:selected').removeAttr('selected');

        let valcontaitem = $("#idcontaitem" + idnfitem).val()
        let valtipoprodserv = $("#idtipoprodserv" + idnfitem).val()

        contaitem.val(valcontaitem)
        tipoprodserv.val(valtipoprodserv)

        contaitem.find(`option[value="${valcontaitem}"]`).attr('selected', true);
        tipoprodserv.find(`option[value="${valtipoprodserv}"]`).attr('selected', true);

        objhtml.find('[nomodal]').remove();

        CB.modal({
            titulo: `</strong>${desc}</strong> <button type="button" class="btn btn-success btn-xs" onclick="salvarGrupoES(${idnfitem}, ${i})" title="Salvar" style="float: right; margin-top: 14px; margin-right: 8px; display: block;">
                                 <i class="fa fa-circle"></i>Salvar</button>`,
            corpo: [objhtml],
            classe: 'cinquenta',
        });
    }

    function alterarValor(campo, valor, tabela, inid, texto) {
        htmlTrModelo = $(`#modulohistorico${inid}`).html();
        htmlTrModelo = htmlTrModelo.replace("#namerotulo", texto);
        htmlTrModelo = htmlTrModelo.replace("#name_tipoobjeto", "_h1_i_" + tabela + "_tipoobjeto");
        htmlTrModelo = htmlTrModelo.replace("#name_idobjeto", "_h1_i_" + tabela + "_idobjeto");
        htmlTrModelo = htmlTrModelo.replace("#name_auditcampo", "_h1_i_" + tabela + "_campo");
        htmlTrModelo = htmlTrModelo.replace("#name_campo", "_h1_i_" + tabela + "_valor");
        htmlTrModelo = htmlTrModelo.replace("#name_valorold", "_h1_i_" + tabela + "_valor_old");
        htmlTrModelo = htmlTrModelo.replace("#name_justificativa", "_h1_i_" + tabela + "_justificativa");
        htmlTrModelo = htmlTrModelo.replace("#valor_campo_old", valor);
        htmlTrModelo = htmlTrModelo.replace("#valor_campo", valor);
        var objfrm = $(htmlTrModelo);
        objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");

        strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='CB.post();'><i class='fa fa-circle'></i>Salvar</button></strong>";

        CB.modal({
            titulo: strCabecalho,
            corpo: "<table>" + objfrm.html() + "</table>",
            classe: 'sessenta',
            aoAbrir: function(vthis) {
                $("[name='_h1_i_modulohistorico_valor']").daterangepicker({
                    "singleDatePicker": true,
                    "locale": CB.jDateRangeLocale
                }).on('apply.daterangepicker', function(ev, picker) {
                    $(this).html(picker.startDate.format("DD/MM/YYYY") || "");
                    $(`#_1_${acao}_cotacao_${campo}`).attr("value", picker.startDate.format('DD/MM/YYYY'));
                });

                $(`[name="_h1_i_${tabela}_campo"]`).val(campo);
            }
        });
    }

    function alterarMoeda(vthis, inidnfitem, inmoeda) {
        var moeda = $(vthis).attr('moeda');
        var novamoeda;
        if (inmoeda != '') {
            if (moeda == 'BRL') {
                novamoeda = inmoeda;
            } else {
                novamoeda = 'BRL';
            }
        } else {
            if (moeda == 'BRL') {
                novamoeda = 'USD';
            } else if (moeda == 'USD') {
                novamoeda = 'EUR';
            } else {
                novamoeda = 'BRL';
            }
        }

        CB.post({
            objetos: "_x_u_nfitem_idnfitem=" + inidnfitem + "&_x_u_nfitem_moeda=" + novamoeda,
            parcial: true,
            posPost: function(data, textStatus, jqXHR) {
                vthis.innerText = novamoeda;
                $(vthis).attr('moeda', novamoeda);
            }
        });
    }

    function alterarCamposNf(idnfitem, tabela, campo, check, vthis, idnf) {
        switch ($(vthis).prop('type')) {
            case 'checkbox':
                newinval = check
                break;
            default:
                newinval = $(vthis).val()
                break;
        }

        CB.post({
            objetos: "_x_u_" + tabela + "_id" + tabela + "=" + idnfitem + "&_x_u_" + tabela + "_" + campo + "=" + newinval,
            parcial: true,
            refresh: false,
            posPost: function() {
                if (tabela == "nfitem") {
                    if ($(vthis).prop('type') == 'checkbox') {
                        if (newinval == "Y") {
                            $(`a.solcomvalida[idnfitem='${idnfitem}']`).attr('nfe', 'Y');
                            $(vthis).attr("onclick", `alterarCamposNf(${idnfitem}, '${tabela}', '${campo}', 'N', this, ${idnf})`);
                        } else if (newinval == "N") {
                            $(`a.solcomvalida[idnfitem='${idnfitem}']`).attr('nfe', 'N');
                            $(vthis).attr("onclick", `alterarCamposNf(${idnfitem}, '${tabela}', '${campo}', 'Y', this, ${idnf})`);
                        }
                    } else {
                        $(vthis).attr("onchange", `alterarCamposNf(${idnfitem}, '${tabela}', '${campo}', 'Y', this, ${idnf})`);
                    }

                    atualizaValoresNf(idnfitem, idnf);

                    let arrayCampos = ['vlritem', 'des', 'vst', 'valipi', 'aliqipi'];
                    if (arrayCampos.indexOf(campo) != -1) {
                        let qtdsol = parseFloat($(`#qtdsol${idnfitem}`).val());
                        let valorunitario = parseFloat($(`#nfitem${idnfitem}`).val());
                        let desconto = parseFloat($(`#des${idnfitem}`).val());
                        let ipi = $(`#ipi${idnfitem}`).val().replace(",", ".") != '' ? parseFloat($(`#ipi${idnfitem}`).val().replace(",", ".")) : 0;
                        let totalItem = (qtdsol * valorunitario) - (qtdsol * desconto);
                        let valorTotalItem = totalItem + ((totalItem * ipi) / 100);
                        $(`#totalext${idnfitem}`).html(Number(valorTotalItem).toLocaleString('pt-BR', {
                            minimumFractionDigits: 2
                        }));
                    }
                }
            }
        });
    }

    function atualizaValoresNf(idnfitem, idnf) {
        $.ajax({
            type: "post",
            url: "ajax/cotacao_ajax.php",
            data: {
                "idnfitem": idnfitem,
                "comandoExecutar": "atualizarValoresNf"
            },
            success: function(data) { // retorno 200 do servidor apache                
                var Jdata = JSON.parse(data);
                $("#totalsemdesc" + idnf).html(Number(Jdata.totalsemdesc).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
                $("#desconto" + idnf).html(Number(Jdata.desconto).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
                $("#totalcomdesc" + idnf).html(Number(Jdata.totalcomdesc).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));

                $("#totalNf" + idnf).val(Jdata.totalcomdesc);
            },
            error: function(objxml) { // nao retornou com sucesso do apache
                alert('Erro: ' + objxml.status);
            }
        }) //$.ajax
    }

    function salvarObservacao(idnfitem) {
        let observacao = $(`#_${idnfitem}_nfitem_obs`).val();
        CB.post({
            objetos: {
                "_s_u_nfitem_idnfitem": idnfitem,
                "_s_u_nfitem_obs": observacao,
            },
            parcial: true
        });
    }

    function atualizarCampo(comentario, idnfitem) {
        $(`#_${idnfitem}_nfitem_obs`).text(comentario.value);
    }

    function mostrarOcultarProdutoEstoqueMinimo(vthis) {
        let cboculto = $(vthis).attr('cboculto');
        if (cboculto == 'Y') {
            $('.esconderMostrarEstoqueMinimo').show();
            $(vthis).attr('cboculto', 'N').attr('title', 'Ocultar Estoque Mínimo menor que zero');
            $(vthis).removeClass('filtroAtivo selecionado');
        } else {
            $('.esconderMostrarEstoqueMinimo').hide();
            $(vthis).attr('cboculto', 'Y').attr('title', 'Mostrar Estoque Mínimo menor que zero');
            $(vthis).addClass('filtroAtivo selecionado');
        }
    }

    function autoCompleteNovoItem() {
        $('.autocompletenovoitem').each(function(index, elemento) {
            let idpessoa = $(elemento).attr('idpessoa');
            let itens = jsonitens.filter((obj, index) => obj.idpessoa == idpessoa);
            $(elemento).autocomplete({
                source: itens,
                delay: 0,
                select: function(event, ui) {
                    console.log(ui);
                    if (confirm("Deseja inserir este item?")) {
                        let idnf = $(event.currentTarget).attr('idnf');
                        if (ui.item.idprodserv) {
                            unidade = (ui.item.unforn == 'null' || ui.item.unforn == null) ? '' : ui.item.unforn;
                            $_post = `_x_i_nfitem_idnf=${idnf}&_x_i_nfitem_idprodserv=${ui.item.idprodserv}&_x_i_nfitem_un=${unidade}&_x_i_nfitem_idprodservforn=${ui.item.idprodservforn}&_x_i_nfitem_qtd=1`;
                            CB.post({
                                objetos: $_post,
                                refresh: "refresh"
                            });
                        }
                    }
                },
                create: function() {
                    $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                        return $('<li>').append("<a>" + item.descr + "</a>").appendTo(ul);
                    };
                }
            });
        });
    }

    function inserirNovoItem(idnf) {
        oTbItens = $(`.adicionarNovoItem${idnf}`);
        htmlTrModelo = $(`#modeloNovoItem${idnf}`).html();
        novoTr = "<tr class='trNovoItem'>" + htmlTrModelo + "</tr>";
        oTbItens.append(novoTr);
        autoCompleteNovoItem();
    }

    function excluirItem(inid) {
        if (confirm("Deseja retirar este item?")) {
            let virg = '';
            let idsolcom = '';
            $(`a.solcomvalida[idnfitem='${inid}']`).each(function(i, elemento) {
                let objSolcom = $(elemento);
                idsolcom += virg + $(objSolcom).attr('idsolcom');
                virg = ",";
            });

            let idprodserv = $(`a.solcomvalida[idnfitem='${inid}']`).attr('idprodserv');
            CB.post({
                objetos: `_x_d_nfitem_idnfitem=${inid}&idsolcom=${idsolcom}&idprodserv=${idprodserv}&idcotacao=${idcotacao}`,
                parcial: true
            });
        }
    }

    function inserirNovoCte(inidnf) {
        if (confirm("Gerar CTE para esta cotação?")) {
            CB.post({
                objetos: "_x_i_nf_tiponf=T&_x_i_nf_idobjetosolipor=" + inidnf + "&_x_i_nf_tipoobjetosolipor=nf",
                parcial: true
            });
        }
    }

    function atualizarQtd(vthis, idnfitem) {
        var valor = $(vthis).val();
        $_post = `_w_u_nfitem_idnfitem=${idnfitem}&_w_u_nfitem_qtd=${valor}&_w_u_nfitem_qtdsol=${valor}`;
        CB.post({
            objetos: $_post,
            parcial: true
        });
    }

    function salvarDescr(vthis, idnfitem) {
        CB.post({
            objetos: `_x_u_nfitem_idnfitem=${idnfitem}&_x_u_nfitem_prodservdescr=${$(vthis).val()}`,
            refresh: false,
            msgSalvo: "Atualizada a descrição"
        })
    }

    function editarFornecedor() {
        $("#x_f_nome").removeAttr('disabled');
        $("#x_f_nome").css({
            'background-color': ''
        });
        $("[name=x_f_nome]").autocomplete({
            source: jForn,
            delay: 0,
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
                };
            }
        });
    }

    function atribuirValorSelect(vthis, idnfitem, tipo) {
        let req;
        if (!document.getElementById("idtipoprodserv" + idnfitem)) {
            req = false;
        } else {
            req = true;
        }

        if (vthis.value == "") {
            $("#btn_" + idnfitem).addClass('laranja');
            $("#btn_" + idnfitem).attr('title', 'Categoria e/ou Subcategoria não Atribuídas');
        } else if (!req && tipo == 'contaitem') {
            $("#btn_" + idnfitem).removeClass('laranja');
            $("#btn_" + idnfitem).removeAttr('title');
        }

        if (tipo == 'contaitem') {
            $("#iidcontaitem" + idnfitem).val(vthis.value);
            $("#idcontaitem" + idnfitem + " option:selected").removeAttr('selected');
            $("#idcontaitem" + idnfitem).val(vthis.value).change();

            if (req) {
                $("#iidtipoprodserv" + idnfitem).val("");
            }

        } else {
            $("#iidtipoprodserv" + idnfitem).val(vthis.value);
            $("#idtipoprodserv" + idnfitem + " option:selected").removeAttr('selected');
            $("#idtipoprodserv" + idnfitem).val(vthis.value).change();

            if (vthis.value != "") {
                $("#btn_" + idnfitem).removeClass('laranja');
                $("#btn_" + idnfitem).removeAttr('title');
            }
        }
    }

    function salvarGrupoES(idnfitem, i) {
        let idcontaitem = $(`[name="_${i}_u_nfitem_idcontaitem"] option:selected`).val();
        let idtipoprodserv = $(`[name="_${i}_u_nfitem_idtipoprodserv"] option:selected`).val();
        let post = false;
        if (idcontaitem && idtipoprodserv) {
            $_post = `_ct_u_nfitem_idnfitem=${idnfitem}&_ct_u_nfitem_idcontaitem=${idcontaitem}&_ct_u_nfitem_idtipoprodserv=${idtipoprodserv}`;
            post = true;
        } else if (idcontaitem) {
            $_post = `_ct_u_nfitem_idnfitem=${idnfitem}&_ct_u_nfitem_idcontaitem=${idcontaitem}`;
            post = true;
        } else if (idtipoprodserv) {
            $_post = `_ct_u_nfitem_idnfitem=${idnfitem}&_ct_u_nfitem_idtipoprodserv=${idtipoprodserv}`;
            post = true;
        }

        if (post == true) {
            CB.post({
                objetos: $_post,
                parcial: true
            });
        }
    }

    function setvalnfitem(vthis, inidnfitem, invlritemext) {
        var valconv = $(vthis).val().replace(",", ".");
        var nvalor = valconv * invlritemext;
        $('#nfitem' + inidnfitem).val(nvalor);
    }

    function validarCamposPreenchidos(vthis, vidnf, statusAnterior, idtipoprodserv, valortipoprodserv) {
        var qtdcheck = $(`#cotacao${vidnf} [name="namenfec"]:checked`).length;
        var vstatus = $(vthis).val();
        var inputprenchido = $(`#cotacao${vidnf} [name="namenfec"]:checked`);
        var inputprevisao = $(inputprenchido).parent().parent().find("[id*='nfitem_previsaoentrega']");
        var diasentrada = $(`.diasentrada${vidnf}`).val();
        var parcelas = $(`.parcelas${vidnf}`).val();
        var prosseguir = true;
        if (vstatus == 'AUTORIZADO' || vstatus == 'AUTORIZADA') {
            var i = 0;
            while (inputprevisao.length > i) {

                var nfitem_previsaoentrega = $(inputprevisao[i]).val();

                if (nfitem_previsaoentrega == "") {
                    alert('É necessário preencher a previsão de Entrega.');
                    $(vthis).val("");
                    $(`.nfstatus${vidnf}`).val(statusAnterior);
                    prosseguir = false;
                    return;

                }
                i++;
            }
        }

        if (vstatus == 'APROVADO') {
            var nameValue = 'tipoprodserv';

            // Seleciona todos os inputs com o name especificado e itera sobre eles
            $(`input[name="${nameValue}"]`).each(function() {
                var id = $(this).attr('id'); // Pega o ID do input
                var value = $(this).val(); // Pega o valor do input

                var valorSemPontos = value.replace(/\./g, '');

                // Passo 2: Substituir a vírgula (separador decimal) por um ponto
                var valorComPontoDecimal = valorSemPontos.replace(',', '.');

                // Passo 3: Converter para número
                var value = parseFloat(valorComPontoDecimal);

                if (id == idtipoprodserv && value < parseFloat($(`#totalNf${vidnf}`).val())) {
                    alert(`Sem orçamento disponível para essa nf: ${vidnf} Valor disponível: ${value} Valor solicitado: ` + parseFloat($(`#totalNf${vidnf}`).val()));
                    prosseguir = false;
                    return;
                }
            });

            //vamos pegar os valores 
        }

        if (vstatus == 'APROVADO' || status == 'PREVISAO' || vstatus == 'AUTORIZADO' || vstatus == 'AUTORIZADA') {
            var formapagamento = $('#formapagamento' + vidnf).attr('cbvalue');

            if (formapagamento == "") {
                alert('É necessário preencher a forma de pagamento.');
                $(vthis).val("");
                $(`.nfstatus${vidnf}`).val(statusAnterior);
                prosseguir = false;
                return;
            }

            var dtemissao = $('.dtemissao' + vidnf).val();

            if (dtemissao == "") {
                alert('É necessário preencher a data de emissão.');
                $(vthis).val("");
                $(`.nfstatus${vidnf}`).val(statusAnterior);
                prosseguir = false;
                return;
            }

            var arrayOfIds = $.map(inputprenchido, function(n, i) {
                return n.id;
            });

            var arr = [];
            jQuery.each(arrayOfIds, function(i, val) {
                var texto = $("#nfitem_previsaoentrega" + val).val();

                //alert(texto);
                if (texto == "") {
                    alert('É necessário preencher todas as previsões.');
                    $(vthis).val("");
                    $(`.nfstatus${vidnf}`).val(statusAnterior);
                    prosseguir = false;
                    return;
                }
                arr[texto] = texto;
            });

            if (Object.keys(arr).length > 1) {
                alert('As datas de previsão devem ser iguais');
                $(vthis).val("");
                $(`.nfstatus${vidnf}`).val(statusAnterior);
                prosseguir = false;
                return;
            }
        }

        if (vstatus == 'APROVADO' || vstatus == 'PREVISAO') {
            var itensNaoMarcados = false;
            var produto = "";
            var virg = "";
            //busca os itens associados a uma solcom que estejam desmarcados
            $(`#cotacao${vidnf} a.solcomvalida`).each(function(i, o) {
                let objSolcom = $(o);
                if (objSolcom.attr('idprodserv')) {
                    itensNaoMarcados = $(`a.solcomvalida[nfe='Y'][idprodserv='${objSolcom.attr('idprodserv')}']`).length < 1;
                    if (itensNaoMarcados == true) {
                        produto += virg + "- " + objSolcom.attr('descr');
                        virg = "<br> ";
                    }
                }
            });

            if (itensNaoMarcados) {
                alertAtencao(`Nâo é possível Aprovar. <br> Existem itens da Solicitação de Compras não Selecionados em nenhuma Cotação:  <br>` + produto);
                $(`.nfstatus${vidnf}`).val(statusAnterior);
                prosseguir = false;
                return;
            }
        }

        if (vstatus == 'APROVADO' && (diasentrada == "" || parcelas == "")) {
            (diasentrada == "" && parcelas == "") ?
            mensagem = "Os campos 1º Vencimento e Dias Parcelas não foram prenchido": diasentrada == "" ? mensagem = "O campo 1º Vencimento está vazio" : mensagem = "O campo Dias Parcelas está vazio";
            alertAtencao(`Nâo é possível Aprovar. <br>  ${mensagem}`);
            $(`.nfstatus${vidnf}`).val(statusAnterior);
            prosseguir = false;
            return;
        }

        //LTM - 07-04-2021 - Retorna o idFluxoStatus Selecionado
        var idfluxostatus = getIdFluxoStatus('nfentrada', vstatus);

        if (qtdcheck == 0 && (vstatus == 'APROVADO' || vstatus == 'PREVISAO')) {
            alert('Para aprovar a compra deve-se selecionar pelo menos um item!');
            $(`.nfstatus${vidnf}`).val(statusAnterior);
            prosseguir = false;
        } else if ((vstatus == 'APROVADO' || vstatus == 'PREVISAO') && $('#formapagamento' + vidnf).val() == '') {
            alert("Favor preencher a forma de pagamento.");
            $(vthis).val($("#nfstatus" + vidnf).val());
            $(`.nfstatus${vidnf}`).val(statusAnterior);
            prosseguir = false;
        } else {

            if (diasentrada == "0") {
                if (!confirm("O valor do campo \" 1º Vencimento\" igual a 0 deseja continuar?")) {
                    $(`.nfstatus${vidnf}`).val(statusAnterior);
                    $(`.diasentrada${vidnf}`).focus();
                    return;
                    prosseguir = false;
                }
            }

            if (prosseguir == true) {

                if (vstatus == 'APROVADO' || vstatus == 'PREVISAO') {
                    var idformapamento = $('#formapagamento' + vidnf).attr('cbvalue');

                    if (confirm("Deseja enviar email de aprovação?")) {
                        var tipoGerarPdf = '&tipogerarpdf=cotacaoaprovada&gerarpdf=' + true;
                        str = "_x_u_nf_idnf=" + vidnf + "&_x_u_nf_status=" + vstatus + "&_x_u_nf_idfluxostatus=" + idfluxostatus + "&_x_u_nf_emailaprovacao=Y&_x_u_nf_idformapagamento=" + idformapamento + tipoGerarPdf;
                    } else {
                        str = "_x_u_nf_idnf=" + vidnf + "&_x_u_nf_status=" + vstatus + "&_x_u_nf_idfluxostatus=" + idfluxostatus + "&_x_u_nf_idformapagamento=" + idformapamento;
                    }
                } else if (vstatus == 'CANCELADO' || vstatus == 'REPROVADO') {
                    let virg = "";
                    let parametros = "";
                    $(`#cotacao${vidnf} a.solcomvalida`).each(function(i, elemento) {
                        let idprodserv = $(elemento).attr('idprodserv');
                        let inputchecked = $(elemento).attr('nfe');
                        if (inputchecked == 'Y') {
                            parametros += `${virg}${$(elemento).attr('idsolcom')}-${$(elemento).attr('idprodserv')}`;
                            virg = ",";
                        }
                    });
                    str = `_x_u_nf_idnf=${vidnf}&_x_u_nf_status=${vstatus}&_x_u_nf_idfluxostatus=${idfluxostatus}&parametros=${parametros}&idcotacao=${idcotacao}&_status=CANCELADO`;

                } else {
                    str = "_x_u_nf_idnf=" + vidnf + "&_x_u_nf_status=" + vstatus + "&_x_u_nf_idfluxostatus=" + idfluxostatus;
                }
                if (vstatus == 'APROVADO' || vstatus == 'PREVISAO') {
                    $("#divcor" + vidnf).removeClass("ABERTO ENVIADO RESPONDIDO AUTORIZADO AUTORIZADA CONCLUIDO REPROVADO CANCELADO DIVERGENCIA").addClass("APROVADO");
                    $("[setDisable" + vidnf + "]").prop('disabled', false);
                } else if (vstatus == 'ABERTO') {
                    $("#divcor" + vidnf).removeClass("APROVADO PREVISAO ENVIADO RESPONDIDO AUTORIZADO AUTORIZADA CONCLUIDO REPROVADO CANCELADO DIVERGENCIA").addClass("ABERTO");
                    $("[setDisable" + vidnf + "]").prop('disabled', false);
                } else if (vstatus == 'ENVIADO') {
                    $("#divcor" + vidnf).removeClass("ABERTO APROVADO PREVISAO RESPONDIDO AUTORIZADO AUTORIZADA CONCLUIDO REPROVADO CANCELADO DIVERGENCIA").addClass("ENVIADO");
                    $("[setDisable" + vidnf + "]").prop('disabled', true);
                } else if (vstatus == 'RESPONDIDO') {
                    $("#divcor" + vidnf).removeClass("ABERTO ENVIADO APROVADO PREVISAO AUTORIZADO AUTORIZADA CONCLUIDO REPROVADO CANCELADO DIVERGENCIA").addClass("RESPONDIDO");
                    $("[setDisable" + vidnf + "]").prop('disabled', false);
                } else if (vstatus == 'AUTORIZADO' || vstatus == 'AUTORIZADO') {
                    $("#divcor" + vidnf).removeClass("ABERTO ENVIADO RESPONDIDO APROVADO PREVISAO CONCLUIDO REPROVADO CANCELADO DIVERGENCIA").addClass("AUTORIZADO");
                    $("[setDisable" + vidnf + "]").prop('disabled', false);
                } else if (vstatus == 'CONCLUIDO') {
                    $("#divcor" + vidnf).removeClass("ABERTO ENVIADO RESPONDIDO AUTORIZADO AUTORIZADA APROVADO PREVISAO REPROVADO CANCELADO DIVERGENCIA").addClass("CONCLUIDO");
                    $("[setDisable" + vidnf + "]").prop('disabled', false);
                } else if (vstatus == 'REPROVADO') {
                    $("#divcor" + vidnf).removeClass("ABERTO ENVIADO RESPONDIDO AUTORIZADO AUTORIZADA CONCLUIDO APROVADO PREVISAO CANCELADO DIVERGENCIA").addClass("REPROVADO");
                    $("[setDisable" + vidnf + "]").prop('disabled', false);
                } else if (vstatus == 'CANCELADO') {
                    $("#divcor" + vidnf).removeClass("ABERTO ENVIADO RESPONDIDO AUTORIZADO AUTORIZADA CONCLUIDO REPROVADO APROVADO PREVISAO DIVERGENCIA").addClass("CANCELADO");
                    $("[setDisable" + vidnf + "]").prop('disabled', false);
                } else if (vstatus == 'DIVERGENCIA') {
                    $("#divcor" + vidnf).removeClass("ABERTO ENVIADO RESPONDIDO AUTORIZADO AUTORIZADA CONCLUIDO REPROVADO CANCELADO APROVADO PREVISAO").addClass("DIVERGENCIA");
                    $("[setDisable" + vidnf + "]").prop('disabled', false);
                }

                //LTM - 07-04-2021 - Altera o Historico da Nfentrada
                var idFluxoStatusHist = getIdFluxoStatusHist('nfentrada', vidnf);
                CB.post({
                    objetos: str,
                    parcial: true,
                    msgSalvo: false,
                    refresh: false,
                    posPost: function() {
                        CB.post({
                            parcial: true,
                            refresh: false,
                            urlArquivo: 'ajax/_fluxo.php?fluxo=fluxo',
                            refresh: false,
                            objetos: {
                                "_modulo": 'nfentrada',
                                "_primary": 'idnf',
                                "_idobjeto": vidnf,
                                "idfluxo": '',
                                "idfluxostatushist": idFluxoStatusHist,
                                "idstatusf": idfluxostatus,
                                "statustipo": vstatus,
                                "idfluxostatus": idfluxostatus,
                                "idfluxostatuspessoa": '',
                                "ocultar": '',
                                "prioridade": '20',
                                "tipobotao": '',
                                "acao": "alterarstatus"
                            }
                        });
                    }
                });
            } else {
                $(`.nfstatus${vidnf}`).val(statusAnterior);
            }
        }
    }

    function checkall(idnf, check) {
        var a = $('.' + idnf).prop('checked', check.checked);
        var n = 100;
        var str = '';
        a.each(function() {
            var aux;
            if (this.checked === true) {
                aux = 'Y';
            } else {
                aux = 'N';
            }
            var x = this.id;
            str += "_" + n + "_u_nfitem_idnfitem=" + x + "&_" + n + "_u_nfitem_nfe=" + aux + "&";
            n++;
        });
        str = str.substring(0, (str.length - 1));
        console.log(str);
        CB.post({
            objetos: str,
            refresh: false,
            parcial: true,
            posPost: function() {
                $.ajax({
                    type: "post",
                    url: "ajax/cotacao_ajax.php",
                    data: {
                        "idnf": idnf,
                        "comandoExecutar": "atualizarValoresNf"
                    },
                    success: function(data) { // retorno 200 do servidor apache                                    
                        try {
                            var Jdata = JSON.parse(data);
                            $("#totalsemdesc" + idnf).html(Number(Jdata.totalsemdesc).toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            }));
                            $("#desconto" + idnf).html(Number(Jdata.desconto).toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            }));
                            $("#totalcomdesc" + idnf).html(Number(Jdata.totalcomdesc).toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            }));

                        } catch (error) {

                        }
                    },
                    error: function(objxml) { // nao retornou com sucesso do apache
                        alert('Erro: ' + objxml.status);
                    }
                }) //$.ajax                    
            }
        });
    }

    function deslocar(inidnfitem) {
        if (confirm("Deseja realmente deslocar o item?")) {
            CB.post({
                objetos: "_nova_u_nfitem_idnfitem=" + inidnfitem,
                parcial: true
            });
        }
    }

    function atualizarFrete(vthis, inidnf) {
        CB.post({
            objetos: "_atfrete_u_nf_idnf=" + inidnf + "&_atfrete_u_nf_frete=" + $(vthis).val(),
            parcial: true
        })
    }

    function atualizarParcelas(vthis, inidnf) {
        if ($(vthis).attr('id') == 'parcelas' && $(`.parcelas${inidnf}`).val() > 1) {
            $(`.intervaloClass${inidnf}`).show();
        } else if ($(vthis).attr('id') == 'parcelas' && $(`.parcelas${inidnf}`).val() == 1) {
            $(`.intervaloClass${inidnf}`).hide();
        }

        if (($(`.dtemissao${inidnf}`).val()).length == 0) {
            alert("Preencher campo Emissão NF.");
        } else {
            CB.post({
                objetos: {
                    "_parc_u_nf_idnf": inidnf,
                    "_parc_u_nf_parcelas": $(`.parcelas${inidnf}`).val(),
                    "_parc_u_nf_dtemissao": $(`.dtemissao${inidnf}`).val(),
                    "_parc_u_nf_diasentrada": $(`.diasentrada${inidnf}`).val(),
                    "_parc_u_nf_intervalo": $(`.intervalo${inidnf}`).val(),
                    "intervaloant": $(`.intervaloant${inidnf}`).val(),
                },
                refresh: false,
                msgSalvo: "Atualizada Quantidade de Parcelas"
            });
        }
    }

    function excluirItemDuplicarCompra(vthis) {
        $(vthis).parent().parent().remove();
    }

    function buscarContaItem(grupo_es) {
        if (grupo_es) {
            $.ajax({
                type: "POST",
                async: false,
                url: "ajax/cotacao_ajax.php",
                data: {
                    "idgrupoes": grupo_es,
                    "idcotacao": idcotacao,
                    "comandoExecutar": "buscarContaItem"
                },
                success: function(data) {
                    //Transforma a string json em objeto
                    jsonContaItem = jsonStr2Object(data);
                    if (jsonContaItem) {
                        $("#picker_contaitemprodserv").html(jsonContaItem['option']);
                        $("#picker_contaitemprodserv").attr('title', jsonContaItem['strings']);
                        $(".stgringsubcategoria").html(jsonContaItem['strings']);
                        $("#picker_contaitemprodserv").selectpicker("refresh");
                        if (jsonContaItem['ids']) {
                            $("#sel_picker_idcontaitemtipoprodserv").val(jsonContaItem['ids']);
                        }
                    } else {
                        alertErro("Javascript: Erro ao recuperar Dados Conta Item");
                    }
                }
            });
        } else {
            $("#picker_contaitemprodserv").selectpicker('val', '');
            $("#picker_contaitemprodserv").find('option').remove();
            $("#picker_contaitemprodserv").selectpicker("refresh");

        }
    }

    function altflagemail(inid, intab, incol, inval, flag) {
        if (flag == 1) {
            var idemailvirtualconf = $("#emailunico").val();
            var idempresa = $("#idempresaemail").val();

            if (incol == 'emailaprovacao') {
                var aux = 'COTACAOAPROVADA';
                var tipoGerarPdf = '&tipogerarpdf=cotacaoaprovada';
            } else {
                var aux = 'COTACAO';
                var tipoGerarPdf = '&tipogerarpdf=cotacao';
            }

            var pdf = '&gerarpdf=' + true;

            CB.post({
                objetos: "_x_u_" + intab + "_id" + intab + "=" + inid + "&_x_u_" + intab + "_" + incol + "=" + inval + pdf + tipoGerarPdf,
                refresh: false,
                parcial: true,
                posPost: function() {
                    altremetenteemail(inid, idemailvirtualconf, aux, idempresa)
                }
            });
        } else {
            if (flag == 2) {
                var setemail = $("#setemail").val();
                var pdf = '&gerarpdf=' + true;
                if (setemail == "1") {
                    alert("É necessário escolher um remetente para o envio");
                } else {
                    CB.post({
                        objetos: "_x_u_" + intab + "_id" + intab + "=" + inid + "&_x_u_" + intab + "_" + incol + "=" + inval + pdf,
                        refresh: false,
                        parcial: true
                    });
                }
            }
        }
    }

    function altremetenteemail(idnf, idemailvirtualconf, tipoenvio, idempresa) {
        CB.post({
            objetos: {
                "_w_i_empresaemailobjeto_idempresa": idempresa,
                "_w_i_empresaemailobjeto_idemailvirtualconf": idemailvirtualconf,
                "_w_i_empresaemailobjeto_tipoenvio": tipoenvio,
                "_w_i_empresaemailobjeto_tipoobjeto": 'nf',
                "_w_i_empresaemailobjeto_idobjeto": idnf,
            },
            parcial: true
        })
    }

    function visualizacao(vthis) {
        let vs = vthis.value
        CB.setPrefUsuario('m', '{"' + CB.modulo + '":{"visualizacao":' + vs + '}}', undefined, function(data) {
            vUrl = CB.urlDestino + window.location.search;
            CB.loadUrl({
                urldestino: vUrl
            });
        });
    }

    function historico(tipo, inid, texto) {
        CB.modal({
            titulo: "</strong>Histórico " + texto + "</strong>",
            corpo: $("#" + tipo + inid).html(),
            classe: 'sessenta',
        });
    }

    function selecionaFornecedor(idprodserv, referAba, idsolcom) {
        let check = false;
        let arrprodservforn = [];
        let prodservforn = $(`#${referAba} #itemalerta_forn_${idprodserv}`).val();
        if (prodservforn.length > 0) {
            arrprodservforn = prodservforn.split(',');
        }

        //Verifica se todos os inputs dos Fornecedores estão checkados. 
        //Caso algum esteja marcar o Input do Produto Checkado também. Caso contrário, desmarcar
        $(`#${referAba} .checkTodosProduto${idprodserv}`).each(function(key, val) {
            idprodservforn = $(val).attr('idprodservforn');
            if (val.checked) {
                check = true;
                if (arrprodservforn.indexOf(idprodservforn) == -1) {
                    arrprodservforn.push(idprodservforn);
                }
            } else {
                arrprodservforn = arrprodservforn.filter(prodservforn => prodservforn != `${idprodservforn}`);
            }
        });

        $(`#${referAba} #itemalerta_forn_${idprodserv}`).val(arrprodservforn.join());

        $(`#${referAba} .itemTodosProduto${idprodserv}_${idsolcom}`).prop('checked', check);
    }

    //--------------------------- Habilita e Desabilita Abas ------------------------------------------
    function carregaPreferenciasJson(idpk, json) {
        carregandoAbas('show');
        controlaNavTabAndPill("tab", idpk, json);
        controlaNavTabAndPill("pill", idpk, json);
    }

    function controlaNavTabAndPill(tipo, idpk, json) {
        $.each(CB.oModuloForm.find("[data-toggle=" + tipo + "]").not(".define"), function(i, o) {
            $o = $(o);
            $o.addClass("define");
            let shref = $o.attr("href");
            let khref = shref.substr(1); //Remover a # do id para não gerar erro de path no mysql
            let abaativa = $(shref);

            let objJson = json || CB.jsonModulo.jsonpreferencias;
            let navType = "nav" + tipo;

            //Verifica se o elemento com collapse possui alguma preferência de usuário salva
            if (objJson["cotacao_" + idpk] && objJson["cotacao_" + idpk][navType] && objJson["cotacao_" + idpk][navType][khref]) {
                $oc = $(shref);
                $oc.removeClass("active");
                col = objJson["cotacao_" + idpk][navType][khref];

                if (col == "N") {
                    $oc.removeClass("active");
                    $o.parent().removeClass("active");
                } else {
                    $oc.addClass("active").addClass('in');
                    $o.parent().addClass("active");
                }
            } else if (khref == 'cotacao_solcom') {
                $oc = $(shref);
                $oc.removeClass("active");

                CB.setPrefUsuario('m', '{"' + CB.modulo + '":{"cotacao_' + idpk + '":{"' + navType + '":{"' + khref + '":"Y"}}}}');
                $body = $(shref);
                $body.addClass('active').addClass('in').removeClass('hidden');
                $body.siblings().removeClass('active').removeClass('in').addClass('hidden');
                $oc.addClass("active").addClass('in');
                $o.parent().addClass("active");
            }

            let idcontaitem = $("#sel_picker_idcontaitemtipoprodserv").val();
            let idgrupoes = $('#sel_picker_idcontaitem').val();
            //Ao selecionar o Tipo Sugestão, buscará estes dados a partir deste momento
            if (idcontaitem.length > 0) {
                if (khref == 'cotacao_todos' && abaativa.hasClass("active")) {
                    if (khref == 'cotacao_solcom' && abaativa.hasClass("active")) {
                        $("#circularProgressIndicator").hide();
                    }
                }
            } else if (idgrupoes.length == 0) {
                $("#" + khref).append(`<div class="col-md-4" style="float: none;"><div class="alert alert-warning" role="alert">É necessário Escolher Categoria.</div></div>`);
                $("#circularProgressIndicator").hide();
                $('.badgeorc').text(0);
            } else {
                $("#" + khref).append(`<div class="col-md-4" style="float: none;"><div class="alert alert-warning" role="alert">É necessário Escolher  Subcategoria.</div></div>`);
                $("#circularProgressIndicator").hide();
            }

            $o.on('click', function(e) {
                $("#circularProgressIndicator").show();

                $this = $(this); //Objeto atual. Geralmente um panel-heading
                let shref = $this.attr("href");
                let khref = shref.substr(1); //Remover a # do id para não gerar erro de path no mysql
                $body = $(shref);

                if ($body.html().length == 0) {
                    getDadosATualizaInfoAbas(khref);
                }

                $('.li_cotacao').each(function(index, element) {
                    let valueListaCotacao = $(element).attr("value");
                    if (valueListaCotacao == khref) {
                        //Abrir
                        CB.setPrefUsuario('m', '{"' + CB.modulo + '":{"cotacao_' + idpk + '":{"' + navType + '":{"' + khref + '":"Y"}}}}');
                        $body.addClass('active').addClass('in').removeClass('hidden');
                        $body.siblings().removeClass('active').removeClass('in').addClass('hidden');
                        $this.parent().siblings().removeClass('active')
                        $this.parent().addClass('active');
                    } else {
                        $oc = $(valueListaCotacao);
                        $oc.removeClass("active");

                        CB.setPrefUsuario('m', '{"' + CB.modulo + '":{"cotacao_' + idpk + '":{"' + navType + '":{"' + valueListaCotacao + '":"N"}}}}');
                        $bodyInativo = $(shref);
                        $bodyInativo.addClass('active').addClass('in').removeClass('hidden');
                        $bodyInativo.siblings().removeClass('active').removeClass('in').addClass('hidden');
                        $oc.addClass("active").addClass('in');
                        $o.parent().removeClass("active");
                    }
                });
            });

            $("#circularProgressIndicator").hide();
        });
    }

    function carregandoAbas(display = 'show') {
        if (display == 'hide') {
            $("#circularProgressIndicator").hide();
            $("#mainPanel").removeClass("disabledbutton");
        } else if (display == 'show') {
            $("#circularProgressIndicator").show();
            $("#mainPanel").addClass("disabledbutton");
        }
    }

    //Carrega a Sugestão de Compras para trazer a quantidade no boolet
    let idcontaitem = $("#sel_picker_idcontaitemtipoprodserv").val() ? $("#sel_picker_idcontaitemtipoprodserv").val() : "";
    if (idcontaitem.length > 0) {
        getDadosATualizaInfoAbas('cotacao_sugestao');
    }

    function getDadosATualizaInfoAbas(khref) {
        let idcontaitem = $("#sel_picker_idcontaitemtipoprodserv").val() ? $("#sel_picker_idcontaitemtipoprodserv").val() : "";
        let dadosbusca = $("#descri").val();
        let ArrayIdprodserv = [];
        let idprodserv = "";
        let qtdTotal;

        if (khref == 'cotacao_sugestao') {
            tipo = 'getProdutoAlerta';
            table = 'table_sugestao_compras';
            divmensagem = 'mostraProdutosTodos';
        } else if (khref == 'cotacao_todos') {
            $(".pesquisaritem").prop("disabled", true);
            $(".pesquisaritem").removeClass("fa fa-search pointer branco");
            $(".pesquisaritem").addClass("fa fa-spinner pointer branco fa-spin");
            tipo = 'getTodosProdutos';
            table = "table_cotacao_todos";
            divmensagem = 'mostraProdutosTodos';
            $('.mostraOcultos').show();
        }

        $('#' + khref + ' #itemalerta').each(function(key, el) {
            ArrayIdprodserv.push($(el).attr('idprodservalerta'));
        });

        //Array para Listar os itens adicionando a medida que forem pesquisados.
        if (ArrayIdprodserv.length > 0) {
            idprodserv = ArrayIdprodserv.join();
        }

        $.ajax({
            type: "POST",
            async: false,
            url: "ajax/cotacao_ajax.php",
            data: {
                "idcontaitem": idcontaitem,
                "idcotacao": idcotacao,
                "tipo": tipo,
                "dadosbusca": dadosbusca,
                "idprodservs": idprodserv,
                "idempresa": idempresa,
                "comandoExecutar": "listarSugestaoTodos"
            },
            success: function(data) {
                array = JSON.parse(data);
                html = array.html;
                qtd = array.qtd;

                if (tipo == 'getProdutoAlerta') {
                    $('.badgeorc').text(qtd ? qtd : 0);
                    $('.qtdProdSugestao').text(qtd ? qtd : 0);
                } else {
                    $("#circularProgressPesquisa").hide();
                    $('.mostraProdutosTodos').css("display", "");
                    qtdSpan = $('.qtdProdTodos').attr('qtd') ? parseInt($('.qtdProdTodos').attr('qtd')) : 0;
                    qtdTotal = qtdSpan + qtd;
                    $('.qtdProdTodos').text(qtdTotal).attr('qtd', qtdTotal);

                    $(".pesquisaritem").removeClass("fa fa-spinner pointer branco fa-spin");
                    $(".pesquisaritem").addClass("fa fa-search pointer branco");
                }

                if (html && ((khref == 'cotacao_sugestao') || (qtdTotal > 0 && khref == 'cotacao_todos'))) {
                    $("." + table).append(html);
                } else if (idcontaitem.length == 0) {
                    $("." + divmensagem).html(`<div class="col-md-4" style="float: none;"><div class="alert alert-warning" role="alert">É necessário Escolher  Subcategoria.</div></div>`);
                }

                //Carrega a Função que abre o Modal, pois o elemento foi criado neste momento e não o encontrava.
                $(".modalProdServ").click(abrirModalProdserv);

                $("#circularProgressIndicator").hide();
            }
        });
    }

    function alterarImportacao(checkbox, idcotacao) {
        if (checkbox.checked) {
            objetos = {
                "_x_u_cotacao_idcotacao": idcotacao,
                "_x_u_cotacao_importacao": 'S'
            };
        } else {
            objetos = {
                "_x_u_cotacao_idcotacao": idcotacao,
                "_x_u_cotacao_importacao": 'N'
            };
        }

        CB.post({
            objetos: objetos,
            parcial: true
        });
    }

    if ($('.cotacaoabas').length == 0) {
        $('.tipotext').addClass('hidden');
    }

    $(document).ready(function() {
        if ($('#sel_picker_idcontaitemtipoprodserv').val()) {
            $.ajax({
                type: "GET",
                url: `ajax/getValTipoprodserv.php`,
                data: {
                    "idtipoprodserv": $('#sel_picker_idcontaitemtipoprodserv').val(),
                    "referencia": $('#_1_u_cotacao_referencia').val(),
                    "tipo": "valores"
                },
                success: function(data) {
                    if (!data) {
                        $('#corpo-forecast-itens').html(`
                            <div class="w-100">
                                <span>Ocorreu um erro</span>
                            </div>`);

                        return false;
                    }
                    const dataJson = JSON.parse(data);

                    if (dataJson.error) {
                        const divError = `<span class="alert alert-warning block">${dataJson.error}</span>`;
                        $('#forecast-compra').html(divError);
                        $('#btn-forecast')
                            .attr('title', dataJson.error)
                            .attr('disabled', true)
                            .removeAttr('onclick');

                        $('#corpo-forecast-itens').html(`<div class="w-100 d-flex text-lg"><span>Ocorreu um erro</span></div>`)

                        return false;
                    }

                    $('#link-forecast').attr('href', dataJson.linkForecast).attr('target', '_blank');

                    if (dataJson.linkForecast == '#')
                        $('#link-forecast').attr('title', 'Forecast não encontrado para esta cotação.');

                    if (!dataJson.categorias) $('#btn-forecast').attr('title', 'Forecast não encontrado para esta cotação.').attr('disabled', true);

                    if (!dataJson.html) {
                        $('#corpo-forecast-itens').html(`
                            <div class="w-100">
                                <span>Nenhum registro encontrado</span>
                            </div>`);

                        return false;
                    }

                    dataJson.categorias = ordenarObjetoForeCast(dataJson.categorias);

                    $('#corpo-forecast-itens').html(dataJson.html);

                    dadosForcast = dataJson;
                },
                error: err => {
                    alert('asdasd');
                    console.log(err);

                    $('#corpo-forecast-itens').html(`
                    <div class="w-100">
                        <span>Ocorreu um erro</span>
                    </div>`);
                }
            });
        }
    });
    <?php if ($_acao != 'i') { ?>

        function mostrainfo(tipo) {
            //validar se já está na tela
            if ($('.retornacancelados').html() != '' && tipo == 'cotacao') {
                if ($('.retornacancelados').css('display') == 'block' || $('.retornacancelados').css('display') == '') {
                    $('.retornacancelados').css('display', 'none');
                    $('#expandCancelado').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                } else {
                    $('.retornacancelados').css('display', 'block');
                    $('#expandCancelado').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                }
                return;
            }

            $.ajax({
                type: "GET",
                async: false,
                url: 'ajax/getcotacaocanceladareprovada.php',
                data: {
                    "tipo": tipo,
                    "idcotacao": <?= $_1_u_cotacao_idcotacao ?>,
                    "idempresa": <?= $_1_u_cotacao_idempresa ?>
                },
                success: function(data) {
                    if (tipo == "cotacao") {
                        $('.retornacancelados').html(data);
                        $('#expandCancelado').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    } else {
                        CB.modal({
                            corpo: data,
                            titulo: (tipo == 'cotacao' ? "Cotações REPROVADAS/CANCELADAS" : "Origem Solicitação de Orçamento"),
                            classe: 'cinquenta'
                        });
                    }
                }
            });
        }
    <?php } ?>


    function mostraitenscot(element, targetId) {
        const content = document.getElementById(targetId);
        const toggle = element; // O elemento clicado (o ícone)

        // Verifica o estado atual do conteúdo
        const isHidden = content.style.display === 'none' || content.style.display === '';

        if (isHidden) {
            // Mostra o conteúdo e muda o ícone para "up"
            content.style.display = 'block';
            toggle.classList.remove('fa-chevron-down');
            toggle.classList.add('fa-chevron-up');
            toggle.setAttribute('title', 'Recolher');
        } else {
            // Esconde o conteúdo e muda o ícone para "down"
            content.style.display = 'none';
            toggle.classList.remove('fa-chevron-up');
            toggle.classList.add('fa-chevron-down');
            toggle.setAttribute('title', 'Expandir');
        }
    }

    function mostracotacao(status) {
        // Seleciona todos os elementos com a classe cotacaoCONCLUIDO
        const contents = document.getElementsByClassName(`cotacao${status}`);
        // Seleciona o ícone (único neste caso)
        const toggle = document.getElementById(`expand${status}`);

        // Verifica o estado atual baseado no primeiro elemento
        const isHidden = contents[0].style.display === 'none' || contents[0].style.display === '';

        // Aplica a mudança a todos os elementos com a classe
        Array.from(contents).forEach(content => {
            if (isHidden) {
                content.style.display = 'block';
            } else {
                content.style.display = 'none';
            }
        });

        // Atualiza o ícone
        if (isHidden) {
            toggle.classList.remove('fa-chevron-down');
            toggle.classList.add('fa-chevron-up');
            toggle.setAttribute('title', 'Recolher');
        } else {
            toggle.classList.remove('fa-chevron-up');
            toggle.classList.add('fa-chevron-down');
            toggle.setAttribute('title', 'Expandir');
        }
    }

    function abrirtudo(element, aba) {
        const $contents = $('.cotacao' + aba);
        const $toggle = $(element); // O elemento clicado (o ícone "Expandir todos")
        const isExpanding = $toggle.attr('title').trim() === 'Expandir todos';

        let hasHidden = false;
        let hasVisible = false;

        $contents.each(function() {
            const $content = $(this);
            const isHidden = $content.css('display') === 'none' || $content.css('display') === '';
            const $panel = $content.closest('.panel-default');
            const $chevron = $panel.find('.panel-heading .fa-chevron-down, .panel-heading .fa-chevron-up');

            if (isExpanding && isHidden) {
                $content.css('display', 'block');
                if ($chevron.length) {
                    $chevron
                        .removeClass('fa-chevron-down')
                        .addClass('fa-chevron-up')
                        .attr('title', 'Recolher');
                }
            } else if (!isExpanding && !isHidden) {
                $content.css('display', 'none');
                if ($chevron.length) {
                    $chevron
                        .removeClass('fa-chevron-up')
                        .addClass('fa-chevron-down')
                        .attr('title', 'Expandir');
                }
            }

            if ($content.css('display') === 'none' || $content.css('display') === '') {
                hasHidden = true;
            } else {
                hasVisible = true;
            }
        });

        if (hasHidden) {
            $toggle
                .removeClass('fa-chevron-up')
                .addClass('fa-chevron-down')
                .attr('title', 'Expandir todos')
            /* .text(' Expandir todos') */
            ;
        } else {
            $toggle
                .removeClass('fa-chevron-down')
                .addClass('fa-chevron-up')
                .attr('title', 'Recolher todos')
            /* .text(' Recolher todos') */
            ;
        }
    }

    $(document).ready(function() {
        // Move apenas o conteúdo de cada div para seu respectivo container
        $('.emandamento').html($('.resandamento').html());
        $('.emaprovado').html($('.resaprovado').html());
        $('.emconcluido').html($('.resconcluido').html());

        //faz isso com os dado que foram selecionados
        $('.infocategoria').attr('title', $('.stringcategoria').html());
    });

    jQuery(document).ready(function($) {
        var isTooltipVisible = false;

        // Evento de hover
        $('.xg-custom-button').hover(
            function() {
                if (!isTooltipVisible) {
                    $(this).find('.xg-tooltip')
                        .css('display', 'block')
                        .hide()
                        .slideDown(200);
                }
            },
            function() {
                if (!isTooltipVisible) {
                    $(this).find('.xg-tooltip')
                        .slideUp(200);
                }
            }
        );

        // Evento de clique
        $('#meuBotao').click(function() {
            var $tooltip = $(this).find('.xg-tooltip');

            if (!isTooltipVisible) {
                $tooltip
                    .css('display', 'block')
                    .hide()
                    .slideDown(200);
                isTooltipVisible = true;
            } else {
                $tooltip.slideUp(200);
                isTooltipVisible = false;
            }
        });
    });


    //------- Funções Módulo -------

    //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape1

    //usa mascara no campo referencia
    function mascararData(input) {
        let v = input.value;
        v = v.replace(/\D/g, '');
        if (v.length > 2) {
            v = v.substring(0, 2) + '/' + v.substring(2);
        }
        v = v.substring(0, 7);
        if (v.length >= 2) {
            let mes = parseInt(v.substring(0, 2));
            if (mes > 12) v = '12' + v.substring(2);
        }
        input.value = v;
    }

    function abrirModalForecast() {
        debugger
        let corpo = '';
        const meses = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
        if (dadosForcast && Object.keys(dadosForcast).length && Object.keys(dadosForcast.categorias).length) {
            corpo = `<h3>Abaixo os valores referentes aos orçamentos disponíveis</h3>`;

            for (let idCategoria in dadosForcast.categorias) {
                let categoria = dadosForcast.categorias[idCategoria];
                let subCategorias = categoria.subcategorias ? Object.values(categoria.subcategorias) : [];
                let resumoMeses = Array.from(dadosForcast.resumoMeses);
                let valorOriginalMeses = dadosForcast.valorMeses;
                // let valorMeses = [];
                let acumuladoAtual = 0;
                let larguraColunas = 100 / dadosForcast.resumoMeses.length + 2;

                corpo += `<div class="container-itens-forecast w-100 text-uppercase mb-3 font-bold" style="color: rgba(152, 152, 152, 1);">
                            <div class="w-100 categoria-item collapsed" style="border: 1px solid rgba(152, 152, 152, 1);" data-toggle="collapse" href="#forecast-item-${idCategoria}">
                                <div class="w-100 d-flex flex-between px-4 py-3 text-white" style="background-color: rgba(152, 152, 152, 1)">
                                    <h5 class="text-uppercase m-0">${categoria.contaitem}</h5>
                                    <i class="fa fa-chevron-down"></i>
                                </div>
                            </div>
                           <div class="w-100 collapse" id="forecast-item-${idCategoria}">
                                <div class="w-100 d-flex text-lg">
                                    <div class="p-1 pl-4" style="width: ${larguraColunas}%;border: 1px solid rgba(152, 152, 152, 1)"><span>subcategorias</span></div>
                                    ${resumoMeses.map(mes => `<div class="text-center p-1" style="width: ${larguraColunas}%;border: 1px solid rgba(152, 152, 152, 1)"><span>${mes}/25</span></div>`).join('')}
                                    <div class="text-center p-1" style="width: ${larguraColunas}%;border: 1px solid rgba(152, 152, 152, 1)"><span>Acumulado Atual</span></div>
                                </div>
                                ${subCategorias.map(item => `
                                    <div class="w-100 d-flex text-lg">
                                        <div class="p-1 pl-4" style="width: ${larguraColunas}%;border: 1px solid rgba(152, 152, 152, 1)"><span>${item.tipoprodserv}</span></div>
                                        ${Object.values(item.tresMeses).map(valorMes => `<div class="text-center p-1" style="width: ${larguraColunas}%;border: 1px solid rgba(152, 152, 152, 1)"><span class="${valorMes <= 0 ? 'text-danger' : 'text-success'}">${formatarValorBRL((parseFloat(valorMes < 0 ? valorMes * -1 : valorMes)))}</span></div>`).join('')}
                                        <div class="text-center p-1" style="width: ${larguraColunas}%;border: 1px solid rgba(152, 152, 152, 1)"><span class="${item.acumuladoTresMeses <= 0 ? 'text-danger' : 'text-success'}">${formatarValorBRL(Math.abs(item.acumuladoTresMeses))}</span></div>
                                    </div>`).join('')
                                }
                           </div>
                        </div>`;
            }
        }

        CB.modal({
            titulo: 'Forecast de compras - 2025',
            limparModal: false,
            corpo,
        });
    }

    function abrirModalRelatoriosPorUnidade() {
        const corpo = `<h3>Abaixo a soma dos valores dos últimos 6 meses.</h3>
                        <div class="w-100 text-uppercase mb-5 font-bold" style="color: rgba(152, 152, 152, 1);">
                            <div class="w-100" style="border: 1px solid rgba(152, 152, 152, 1);">
                                <div class="w-100 d-flex flex-between px-4 py-3 text-white" style="background-color: rgba(152, 152, 152, 1)">
                                    <h5 class="text-uppercase m-0">INSUMOS produção e serviços</h5>
                                    <i class="fa fa-chevron-up"></i>
                                </div>
                            </div>
                            <div class="w-100 d-flex text-lg">
                                <div class="p-1 pl-4" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>subcategorias</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>Jan/25</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>Fev/25</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>Mar/25,8</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>Acumulado Atual</span></div>
                            </div>
                            <div class="w-100 d-flex text-lg">
                                <div class="p-1 pl-4" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>adjuvante</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>R$ 767.280,84</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span class="text-success">R$ 239.560,04</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>R$ 527.720,8</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span class="text-success">R$ 10.000,00</span></div>
                            </div>
                        </div>
                        <div class="w-100 text-uppercase font-bold" style="color: rgba(152, 152, 152, 1);">
                            <div class="w-100" style="border: 1px solid rgba(152, 152, 152, 1);">
                                <div class="w-100 d-flex flex-between px-4 py-3 text-white" style="background-color: rgba(152, 152, 152, 1)">
                                    <h5 class="text-uppercase m-0">INSUMOS produção e serviços</h5>
                                    <i class="fa fa-chevron-up"></i>
                                </div>
                            </div>
                            <div class="w-100 d-flex text-lg">
                                <div class="p-1 pl-4" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>subcategorias</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>Jan/25</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>Fev/25</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>Mar/25,8</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>Acumulado Atual</span></div>
                            </div>
                            <div class="w-100 d-flex text-lg">
                                <div class="p-1 pl-4" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>adjuvante</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>R$ 767.280,84</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span class="text-success">R$ 239.560,04</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span>R$ 527.720,8</span></div>
                                <div class="text-center p-1" style="width: 20%;border: 1px solid rgba(152, 152, 152, 1)"><span class="text-success">R$ 10.000,00</span></div>
                            </div>
                        </div>`;

        CB.modal({
            titulo: 'Acumulado - Unidade',
            corpo,
            limparModal: false,
            aoFechar: () => {
                abrirModalForecast();
            }
        })
    }

    function modalcategoria() {
        CB.modal({
            titulo: "Categorias selecionadas",
            corpo: $(".stringcategoria").html(),
            classe: 'vinte',
        });
    }

    function modalsubcategoria() {
        CB.modal({
            titulo: "Subcategorias selecionadas",
            corpo: $(".stgringsubcategoria").html(),
            classe: 'vinte',
        });
    }

    function ordenarObjetoForeCast(data) {
        // Converte o objeto principal em um array e ordena pelo campo 'contaitem'
        const sortedArray = Object.entries(data)
            .map(([key, value]) => {
                if (value.subcategorias) {
                    // Ordena as subcategorias pelo campo 'tipoprodserv'
                    value.subcategorias = Object.values(value.subcategorias).sort((a, b) =>
                        a.tipoprodserv.localeCompare(b.tipoprodserv)
                    );
                }
                return {
                    id: key,
                    ...value
                };
            })
            .sort((a, b) => a.contaitem.localeCompare(b.contaitem));

        return sortedArray;
    }
</script>