<?
?>
<script>
    jCli = <?= $JSON->encode(getClientesEst()) ?>; // autocomplete cliente
    jProd = <?= $JSON->encode(getProdEst()) ?>; // autocomplete produto
    jLoteAnimal = <?= $JSON->encode(getLoteAnimalEst()) ?>; // autocomplete lote animal

    function deletalt(inidlote, inidbioensaio) {
        debugger;
        CB.post({
            objetos: "_x_u_bioensaio_idbioensaio=" + inidbioensaio + "&_x_u_bioensaio_idlote=null"
        })
    }

    function deletaltpd(inidbioensaio) {
        debugger;
        CB.post({
            objetos: "_x_u_bioensaio_idbioensaio=" + inidbioensaio + "&_x_u_bioensaio_idlotepd=NULL",
            parcial: true
        })
    }

    function mostrarmodal(idanalise) {
        CB.modal({
            titulo: "Locais de Bioensaio",
            corpo: $("#modallocalbioensaio_" + idanalise).html(),
            classe: "oitenta"
        })
    }

    //mapear autocomplete de clientes
    jCli = jQuery.map(jCli, function(o, id) {
        return {
            "label": o.nome,
            value: id
        }
    });
    //autocomplete de clientes
    $("[name$=_bioensaio_idpessoa]").autocomplete({
        source: jCli,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
            };
        }
    });

    //mapear autocomplete de produto
    jProd = jQuery.map(jProd, function(o, id) {
        return {
            "label": o.descr,
            value: id
        }
    });
    //autocomplete de produto
    $("[name$=_bioensaio_idlotepd]").autocomplete({
        source: jProd,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
            };
        },
        select: function(event, ui) {
            CB.post({
                objetos: {
                    "_x_u_bioensaio_idbioensaio": $("[name$=_bioensaio_idbioensaio]").val(),
                    "_x_u_bioensaio_idlotepd": ui.item.value
                },
                parcial: true
            });
        }
    });


    
    //mapear autocomplete de lote animal
    jLoteAnimal = jQuery.map(jLoteAnimal, function(o, id) {
        return {
            "label": o.descr,
            value: id
        }
    });
    //autocomplete de lote animal
    $("[name$=_bioensaio_idlote]").autocomplete({
        source: jLoteAnimal,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
            };

        },
        select: function(event, ui) {
            inserelotecons(ui.item.value);
        }
    });

    function inserelotecons(inidlotefracao) {
        CB.post({
            objetos: "_x_i_lotecons_idlotefracao=" + inidlotefracao + "&_x_i_lotecons_qtdd=" + $("[name=_1_u_bioensaio_qtd]").val() + "&_x_i_lotecons_idobjeto=" + $("[name=_1_u_bioensaio_idbioensaio]").val() + "&_x_i_lotecons_tipoobjeto=bioensaio"
        });
    }

    function novaanalise() {
        CB.post({
            objetos: "_x_i_analise_idobjeto=" + $("[name=_1_u_bioensaio_idbioensaio]").val() + "&_x_i_analise_objeto=bioensaio",
            parcial: true
        });
    }

    function setanalise(inI) {
        if ($("[name=datadzero" + inI + "]").val() == "") {
            alertAtencao('Por favor, preencha o campo de data');
            $("[name=datadzero" + inI + "]").focus();
            return false
        } else {
            CB.post({
                objetos: "_x_u_analise_idanalise=" + $('#idanalise' + inI).val() + "&_x_u_analise_datadzero=" + $("[name=datadzero" + inI + "]").val() + "&datadzeroold=" + $("[name=datadzeroold" + inI + "]").val() + "&_x_u_analise_idbioterioanalise=" + $('#idbioterioanalise' + inI).val() + "&_x_u_analise_qtd=" + $('#qtd' + inI).val()
                ,parcial:true
            });
        }
    }

    $('[name^="datadzero"].calendario').on('apply.daterangepicker', function(ev, picker) {
        setanalisedt($(ev.target).attr("idanalise"), picker.startDate.format('DD/MM/YYYY'));
    });

    function qtdanalise(vthis, inI, limit_qtd) {
        debugger;
        if ($("[name=datadzero" + inI + "]").val() == "") {
            alertAtencao('Por favor, preencha o campo de data');
            $("[name=datadzero" + inI + "]").focus();
            return false
        } else {
            var new_qtd = $(vthis).val();
            if (new_qtd > limit_qtd) {
                alertAtencao(`Quantidade máxima é ${limit_qtd}`);
                $(vthis).val(limit_qtd);
            } else {
                CB.post({
                    objetos: "_x_u_analise_idanalise=" + $('#idanalise' + inI).val() + "&_x_u_analise_qtd=" + $('#qtd' + inI).val(),
                    parcial: true
                });
            }
        }
    };

    function setanalisedt(inI, indate) {
        //debugger;
        CB.post({
            objetos: "_x_u_analise_idanalise=" + $('#idanalise'+inI).val() + "&_x_u_analise_datadzero=" + indate + "&datadzeroold=" + $("[name=datadzeroold" + inI + "]").val() + "&_x_u_analise_idbioterioanalise=" + $('#idbioterioanalise' + inI).val() + "&_x_u_analise_qtd=" + $('#qtd' + inI).val()
        });
    }

    function uservico(vthis, inidservicoensaio) {
        var strCabecalho = "</strong>SERVIÇO <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='salvarAnalise()'><i class='fa fa-circle'></i>Salvar</button></strong>";
        $("#cbModalTitulo").html((strCabecalho));

        var htmloriginal = $("#servico").html();
        var objfrm = $(htmloriginal);

        objfrm.find("#idservicoensaio").attr("name", "_999_u_servicoensaio_idservicoensaio");
        objfrm.find("#idservicoensaio").attr("value", inidservicoensaio);
        /*
           if($(vthis).attr('tservico')=='TRANSFERENCIA'){
               objfrm.find("#fdata").attr("disabled", 'disabled');
           }
           objfrm.find("#fdata").attr("name", "_999_u_servicoensaio_data");
           objfrm.find("#fdata").attr("value",  $(vthis).attr('dmadata'));

           objfrm.find("#fdata2").attr("name", "old_servicoensaio_data");
           objfrm.find("#fdata2").attr("value",  $(vthis).attr('dmadata'));
           */

        objfrm.find("#status").attr("name", "_999_u_servicoensaio_status");
        objfrm.find("#status option[value='" + $(vthis).attr('status') + "']").attr("selected", "selected");

        objfrm.find("#dia").attr("name", "_999_u_servicoensaio_dia");
        objfrm.find("#dia").attr("value", $(vthis).attr('dia'));

        objfrm.find("#ndropservico").attr("name", "_999_u_servicoensaio_idservicobioterio");
        // objfrm.find("#ndropservico option[value='TRANSFERENCIA']").attr("selected", "selected");
        objfrm.find("#ndropservico option[value='" + $(vthis).attr('tservico') + "']").attr("selected", "selected");

        objfrm.find("#tipoobjeto").attr("name", "_999_u_servicoensaio_tipoobjeto");

        objfrm.find("#ndropanalise").attr("name", "_999_u_servicoensaio_idobjeto");
        objfrm.find("#ndropanalise option[value='" + $(vthis).attr('tanalise') + "']").attr("selected", "selected");

        objfrm.find("#obsdias").text($(vthis).attr('difdias'));

        objfrm.find("#observ").attr("name", "_999_u_servicoensaio_obs");
        objfrm.find("textarea#observ").text($(vthis).attr('observ'));

        $("#cbModalCorpo").html(objfrm.html());
        $('#cbModal').modal('show');

    }

    function salvarAnalise(){
        CB.post({
            objetos: {
                        "_999_u_servicoensaio_idservicoensaio":$("[name='_999_u_servicoensaio_idservicoensaio']").val(),
                        "_999_u_servicoensaio_status":$("[name='_999_u_servicoensaio_status']").val(),
                        "_999_u_servicoensaio_dia":$("[name='_999_u_servicoensaio_dia']").val(),
                        "_999_u_servicoensaio_idservicobioterio":$("[name='_999_u_servicoensaio_idservicobioterio']").val(),
                        "_999_u_servicoensaio_tipoobjeto":$("[name='_999_u_servicoensaio_tipoobjeto']").val(),
                        "_999_u_servicoensaio_idobjeto":$("[name='_999_u_servicoensaio_idobjeto']").val(),
                        "_999_u_servicoensaio_obs":$("[name='_999_u_servicoensaio_obs']").val(),
                    },
            parcial:true
        });
    }

    function iservico(inidanalise) {
        var strCabecalho = "</strong>SERVIÇO <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='CB.post();'><i class='fa fa-circle'></i>Salvar</button></strong>";
        $("#cbModalTitulo").html((strCabecalho));

        var htmloriginal = $("#servico").html();
        var objfrm = $(htmloriginal);

        objfrm.find("#idservicoensaio").attr("name", "_999_i_servicoensaio_idservicoensaio");
        objfrm.find("#ndropanalise").attr("name", "_999_i_servicoensaio_idobjeto");
        objfrm.find("#ndropanalise option[value='" + inidanalise + "']").attr("selected", "selected");
        objfrm.find("#tipoobjeto").attr("name", "_999_i_servicoensaio_tipoobjeto");
        objfrm.find("#ndropservico").attr("name", "_999_i_servicoensaio_idservicobioterio");
        //objfrm.find("#fdata").attr("name", "_999_i_servicoensaio_data");	                
        objfrm.find("#status").attr("name", "_999_i_servicoensaio_status");
        objfrm.find("#dia").attr("name", "_999_i_servicoensaio_dia");
        objfrm.find("#observ").attr("name", "_999_i_servicoensaio_obs");

        $("#cbModalCorpo").html(objfrm.html());
        $('#cbModal').modal('show');
    }

    function gerartestes(inidanalise) {
        CB.post({
            objetos: "i_analise_idanalise=" + inidanalise
        });
    }

    function novoTeste(inidanalise, inidservicoensaio) {
        CB.post({
            //parcial: true,
            objetos: "i_teste_idanalise=" + inidanalise + "&i_teste_idservico=" + $('#idservico' + inidservicoensaio).val() + "&i_teste_idtipoteste=" + $('#idtipoteste' + inidservicoensaio).val() + "&i_teste_qtdteste=" + $('#qtdteste' + inidservicoensaio).val()
        });
    }

    function atnvteste(inidservicoensaio) {
        $('#qtdteste' + inidservicoensaio).removeClass("hidden");
        $('#idtipoteste' + inidservicoensaio).removeClass("hidden");
    }

    function altservico(vidservico) {

        CB.post({
            objetos: "_x_d_servicoensaio_idservicoensaio=" + vidservico,
            refresh: "refresh"
        });

    }

    function delresultado(vidresultado) {

        CB.post({
            objetos: "_x_d_resultado_idresultado=" + vidresultado,
            refresh: "refresh"
        });

    }

    function setcontroleanalise(vthis, inidanalise) {
        if (confirm("Deseja inserir o controle para esta analise?")) {
            const gerarProtocolo = confirm("Deseja criar testes/formulários associados?");

            CB.post({
                objetos: {
                    _setcontrole_u_analise_idanalise: inidanalise,
                    _setcontrole_u_analise_idbioensaioctr: $(vthis).val(),
                    idbioensaioant: $(vthis).attr("idbioensaioctr"),
                    gerarProtocolo 
                },
                parcial: true
            });
        }
    }

    function resetcontroleanalise(vthis, inidanalise) {
        if (confirm("Deseja retirar o controle para esta analise?")) {
            CB.post({
                objetos: "_setcontrole_u_analise_idanalise=" + inidanalise + "&_setcontrole_u_analise_idbioensaioctr=&idbioensaioant=" + $(vthis).attr("idbioensaioctr"),
                parcial: true
            });
        }
    }

    function dbioterioind(vidbioterioind) {
        CB.post({
            objetos: "_x_d_identificador_ididentificador=" + vidbioterioind,
            refresh: "refresh"
        });
    }

    function ibioterioind(vidanalise) {
        CB.post({
            objetos: "_x_i_identificador_tipoobjeto=analise&_x_i_identificador_idobjeto=" + vidanalise,
            refresh: "refresh"
        });
    }

    function iulocalensaio(inidtag, inidlocalensaio, inidanalise, inigaiola) {
        debugger;
        $_post = "_x_u_localensaio_idlocalensaio=" + inidlocalensaio + "&_x_u_localensaio_idtag=" + inidtag + "&_x_u_localensaio_gaiola=" + inigaiola + "&_x_u_localensaio_status=AGENDADO";

        CB.post({
            objetos: $_post,
            parcial: true
        });
    }

    function dellocalensaio(inidtag, inidlocalensaio, inidanalise, inigaiola) {
        debugger;
        $_post = "_x_u_localensaio_idlocalensaio=" + inidlocalensaio + "&_x_u_localensaio_idtag=";

        CB.post({
            objetos: $_post,
            parcial: true
        });
    }

    function flgagrupar(vidbioensaio, vagrupar) {

        CB.post({
            objetos: "_x_u_bioensaio_idbioensaio=" + vidbioensaio + "&_x_u_bioensaio_agrupar=" + vagrupar,
            refresh: "refresh"
        });
    }

    function bioensaiosgdoc(vthis) {
        CB.post({
            objetos: "_x_i_bioensaiosgdoc_idbioensaio=" + $("[name=_1_u_bioensaio_idbioensaio]").val() + "&_x_i_bioensaiosgdoc_idsgdoc=" + $(vthis).val()

        });
    }

    function dbioensaiosgdoc(vidbioensaiosgdoc) {
        CB.post({
            objetos: "_x_d_bioensaiosgdoc_idbioensaiosgdoc=" + vidbioensaiosgdoc,
            refresh: "refresh"
        });
    }

    function iubioensaiodes(inid, vthis) {

        if (inid === 'd') {
            $_post = "_x_d_bioensaiodes_idbioensaiodes=" + vthis;
        } else {
            $_post = "_x_i_bioensaiodes_idbioensaioc=" + $(vthis).val() + "&_x_i_bioensaiodes_idbioensaio=" + $("[name=_1_u_bioensaio_idbioensaio]").val();
        }

        CB.post({
            objetos: $_post,
            refresh: "refresh"
        });
    }

    if ($("[name=_1_u_bioensaio_idbioensaio]").val()) {
        $(".cbupload").dropzone({
            idObjeto: $("[name=_1_u_bioensaio_idbioensaio]").val(),
            tipoObjeto: 'bioensaio',
            idPessoaLogada: $("#idPessoaLogada").val()
        });
    }

    function imprimeEtiqueta(inIdobjeto, inTipoobjeto) {
        var imprimir = true;
        CB.imprimindo = true;

        if (!confirm("Deseja realmente enviar para a impressora?")) {
            imprimir = false;
        }

        if (imprimir) {
            $.ajax({
                type: "get",
                url: "ajax/impetiquetabioensaio.php?idobjeto=" + inIdobjeto + "&tipoobjeto=" + inTipoobjeto,
                success: function(data) {
                    console.log(data);
                    alertAzul("Enviado para impressão", "", 1000);

                }
            });
        }
    }

    function deleteprotocolo(inIdanalise) {
        debugger;
        $_post = "_x_d_analise_idanalise=" + inIdanalise;
        CB.post({
            objetos: $_post,
            refresh: "refresh"
        });
    }

    CB.posPost = function() {

        $("#cbModalCorpo").html("");
        $('#cbModal').modal('hide');
    }


    function imprimeEtiquetasoro(inidservicoensaio) {
        var imprimir = true;
        CB.imprimindo = true;

        if (!confirm("Deseja realmente enviar para a impressora?")) {
            imprimir = false;
        }

        if (imprimir) {
            $.ajax({
                type: "get",
                url: "ajax/impetiquetabioensaiosoro.php?idservicoensaio=" + inidservicoensaio,
                success: function(data) {
                    console.log(data);
                    alertAzul("Enviado para impressão", "", 1000);

                }
            });
        }
    }
    //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>