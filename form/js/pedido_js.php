<?
$arrayObjeto =  PedidoController::BuscarConsumoNfitemSelecionadosPorIdNf($_1_u_nf_idnf);
$array = array_column($arrayObjeto, "idobjeto");
$jsonArray = json_encode($array);
?>
<script>
    let _1_u_nf_idempresa='<?=$_1_u_nf_idempresa?>';
    let idfluxostatus = '<?=FluxoController::getIdFluxoStatus('lote', 'ABERTO', $idunidadelote); ?>';
    let idunidadelote ='<?=$idunidadelote?>';
    let _1_u_nf_envionfe = '<?=$_1_u_nf_envionfe?>';
    let _1_u_nf_idnf = '<?=$_1_u_nf_idnf?>';
    let sessao_idpessoa='<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>';
    let arrayaux='<?=$arrayaux?>';
    let vtotal ='<?=$vtotal?>';
    const ctesVinculadas = <?= json_encode($ctesVinculadas)  ?>;
    const ctesVinculadasNf = <?= json_encode($ctesVinculadasNF)  ?>;
    const nfCte = <?= json_encode(NfEntradaController::buscarCTEDisponiveisParaVinculo($_1_u_nf_idnf, $_1_u_nf_idempresa)) ?>;
    const idsArrayNfitem = <?= $jsonArray?>;
    const tipoNF = '<?= $_1_u_nf_tpnf ?>';
    let idendrotuloOption = `<?=fillselect(PedidoController::listarEnderecoPessoaPorTipo($stridpessoa,'2,3'))?>`;

    let _1_u_nf_status = "<?= $_1_u_nf_status ?>";
    jCli = <?=$jCli?>; // autocomplete cliente
    jsonProd = <?= $jsonProd ?>; //// autocomplete produto
    jnatop = <?= $jnatop ?>;


    if (_1_u_nf_status == 'CONCLUIDO' || _1_u_nf_status == 'REPROVADO' || _1_u_nf_status == 'CANCELADO' ) { 
        
        $("#cbModuloForm").find('input').not('[name*="nf_idnf"],[name*="nf_status"],[name*="nf_idfluxostatus"],[name*="statusant"],[name*="nf_gnre"],[id*="cbTextoPesquisa"],[name*="contapagaritem_valor"],[id*="emailorcamento"],[name*="nameemailnfe"], [name*="_modalnovaparcelacontapagar_tipo_"], [name*="valornovaparc"],[name*="vencnovaparc"],[name*="nf_idvendedor"] ').prop("disabled", true);
        $("#cbModuloForm").find("select").not('[name="_1_u_nf_idtransportadora"], [name="formapagnovaparc"],[name*="nf_idvendedor"] ').prop("disabled", true);
        $("#cbModuloForm").find("textarea").not('[name*="_nf_infcpl"],[name*="nf_emaildadosnfe"],[name*="nf_emaildadosnfemat"],[name*="nf_infcorrecao"]').prop("disabled", true);
      
    }

    //mapear autocomplete de clientes
    jCli = jQuery.map(jCli, function(o, id) {
        return {
            "label": o.nome,
            value: id + "",
            "tipo": o.tipo
        }
    });



    //mapear autocomplete de clientes
    jnatop = jQuery.map(jnatop, function(o, id) {
        return {
            "label": o.natop,
            value: id + ""
        }
    });

    $("#dtemissaonf").on("apply.daterangepicker", function(ev, picker) {
        CB.post({
            objetos: `_dt_u_nf_idnf=${$("[name=_1_u_nf_idnf]").val()}&_dt_u_nf_dtemissao=${picker.startDate.format("DD/MM/YYYY")}`,
            parcial: true,
            posPost: function(resp, status, ajax) {
                gerarParcela = $("[name=_1_u_nf_geracontapagar]").val();
                if(gerarParcela == 'Y'){
                    atualizaparc(`<input name="_1_u_nf_dtemissao" id="dtemissaonf" value="${picker.startDate.format("DD/MM/YYYY")}">`, 'dtemissao');
                }
            }
        });        
    });
    
    if (_1_u_nf_envionfe == "CONCLUIDA") { 
        $("#cancelanf").hide();
        $("#toggle_cancelanfe").click(function() {
            $("#cancelanf").toggle("fast")
        });
    }

    if ($('[name=_1_u_nf_infcorrecao]').val() !='' || _1_u_nf_envionfe != 'CONCLUIDA') {
        $("#cartacorrecao").hide();
        $("#toggle_cartacorrecao").click(function() {
            $("#cartacorrecao").toggle("fast")
        });
     } 


    //autocomplete de clientes
    $("[name*=_nf_idpessoa]").autocomplete({
        source: jCli,
        delay: 0,
        select: function(event, ui) {
            preencheendereco(ui.item.value);
        },
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "<span class='cinzaclaro'> " + item.tipo + "</span></a>").appendTo(ul);
            };
        }
    });



    //autocomplete de clientes
    $("[name*=clientenovaparc]").autocomplete({
        source: jCli,
        delay: 0,
        create: function() {

            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "<span class='cinzaclaro'> " + item.tipo + "</span></a>").appendTo(ul);
            };
        }
    });


    $("[name*=_nf_idnatop]").autocomplete({
        source: jnatop,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
            };
        },
        select: function(event, ui) {
            inserinatop(ui.item.value, $("[name*=_nf_idnf]").val())
        }
    });
    // FIM autocomplete cliente

    //autocomplete de cliente fat
    $("[name*=_nf_idpessoafat]").autocomplete({
        source: jCli,
        delay: 0,
        select: function(event, ui) {
            preencheenderecofat(ui.item.value);
        },
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "<span class='cinzaclaro'> " + item.tipo + "</span></a>").appendTo(ul);
            };
        }
    });
    // FIM autocomplete cliente
    $('#cbModal').on('click.removerCteNf', '.btn-remover-cte-nf', function() {        
        const elementJQ = $(this),
                idNf = elementJQ.data('idnf');

        if(!idNf) return alertAtencao('ID nf não encontrado.');
        if(!confirm('Remover vínculo da CTE desta compra?')) return false;

        CB.oModal.off('click.removerCte');
        CB.oModal.off('click.removerCteNf');
        CB.posPost = () => $('#btn-modal-frete').click();

        CB.post({
            objetos: {
                _1_u_nf_idnf: idNf,
                _1_u_nf_idobjetosolipor: null,
                _1_u_nf_tipoobjetosolipor: null
            },
            parcial: true
        })
    }); 

    $('#btn-modal-frete').on('click', function() {
        let ctesVinculadasHTML = '',
            ctesVinculadasNfHTML = '';

        if(ctesVinculadasNf.length) {

            ctesVinculadasNfHTML = '<hr class="w-100"><div class="w-100 d-flex mt-3 flex-col">'
            ctesVinculadasNfHTML += ctesVinculadasNf.map(item => (`
                                                        <div class="d-flex w-100 mb-2">
                                                            <a class="d-block col-xs-11" target="_blank" href="?_modulo=nfcte&_acao=u&idnf=${item.idnf}&_idempresa=${_1_u_nf_idempresa}">
                                                                ${item.idnf} - ${item.nome}
                                                            </a>
                                                            <i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer btn-remover-cte col-xs-1 text-right" data-idnf="${item.idobjetovinculo}" alt="Excluir !"></i>
                                                        </div>`
                                                    )).join(' ');
            ctesVinculadasNfHTML += `</div><hr class="w-100">`;
        };

        /*
        if(ctesVinculadas.length) {

            ctesVinculadasHTML = '<hr class="w-100"><div class="w-100 d-flex mt-3 flex-col">'
            ctesVinculadasHTML += ctesVinculadas.map(item => (`
                                                        <div class="d-flex w-100 mb-2">
                                                            <a class="d-block col-xs-11" target="_blank" href="?_modulo=nfcte&_acao=u&idnf=${item.idcte}&_idempresa=${_1_u_nf_idempresa}">
                                                                ${item.idcte} - ${item.nome}
                                                            </a>    
                                                        </div>`
                                                    )).join(' ');
            ctesVinculadasHTML += `</div><hr class="w-100">`;
        }
*/
        const corpo = `<div class="w-100 d-flex flex-col align-items-center">
                            <h3 class="w-100">Selecione ou crie CTEs a serem vinculadas.</h3>
                            <div class="w-100 d-flex form-group flex-wrap">
                                <label class="w-100">Selecionar CTE</label>
                                <div class="w-100 d-flex flex-between align-items-center">
                                    <select id="input-cte" type="text" class="col-xs-11 mr-2" data-live-search="true" multiple>
                                        ${nfCte.map(item => `<option value="${item.idnf}">[${item.idnf}] - ${item.nome}</option>`).join(' ')}
                                    </select>
                                    <i onclick="vincularCte();" class="fa fa-plus-circle fa-2x verde pointer" title="Adicionar CTe"></i>
                                </div>
                               
                            </div>
                            <h3 class="mt-0">ou</h3>
                            <div class="d-flex">
                                <button class="btn btn-success text-uppercase d-flex align-items-center font-bold px-5 py-3" onclick='novoCte()'>
                                    <i class="fa fa-plus fa-1x pointer" title="Adicionar CTe"></i>
                                    Gerar nova CTE
                                </button>
                            </div>
                           ${ctesVinculadasNfHTML}
                        </div>`;

        CB.modal({
            titulo: 'Criar ou víncular CTE',
            corpo,
            aoAbrir: () => {
                $('#input-cte').selectpicker();
            }
        })
    });

    $('#cbModal').on('click.removerCte', '.btn-remover-cte', function() {
        if(!confirm('Remover vínculo da CTE deste pedido?')) return false;

        const elementJQ = $(this),
              idObjetoVinculo = elementJQ.data('idobjetovinculo');

        if(!idObjetoVinculo) return alertAtencao('Este vínculo não pode ser excluido.');
        CB.oModal.off('click.removerCte');
        CB.oModal.off('click.removerCteNf');
        CB.posPost = () => $('#btn-modal-frete').click();
        
        CB.post({
            objetos: {
                _1_d_objetovinculo_idobjetovinculo: idObjetoVinculo
            },
            parcial: true
        });
    }); 

    function vincularCte() {
        if(!confirm('Víncular CTE a esta compra?')) return false;

        const cteIdArray = $('#input-cte').val();

        if(cteIdArray && cteIdArray.length) {
            let cteInsertObj = {};

            cteIdArray.forEach((idCte, key) => {
                cteInsertObj[`_${key + 1}_i_objetovinculo_idobjeto`] = _1_u_nf_idnf;
                cteInsertObj[`_${key + 1}_i_objetovinculo_tipoobjeto`] = 'nf';
                cteInsertObj[`_${key + 1}_i_objetovinculo_idobjetovinc`] = idCte;
                cteInsertObj[`_${key + 1}_i_objetovinculo_tipoobjetovinc`] = 'cte';
            });

            CB.oModal.off('click.removerCte');
            CB.oModal.off('click.removerCteNf');
            CB.posPost = () => $('#btn-modal-frete').click();

            CB.post({
                objetos: cteInsertObj,
                parcial: true
            });
        }
    } 

    //Autocomplete de produtos (nfitem)
    function criaAutocompletesProd() {
        $("#tbItens .idprodserv").autocomplete({
            source: jsonProd,
            delay: 0,
            select: function() {
                console.log($(this).cbval());
                preencheFormula($(this).cbval(), $(this).attr('name') + 'formula');
            }
        });
    }



    function showModalEtiqueta() {
        _controleImpressaoModulo({
            modulo: getUrlParameter("_modulo"),
            grupo: 1,
            idempresa: _1_u_nf_idempresa || "1",
            objetos: {
                idnf: $("[name='_1_u_nf_idnf']").val(),
                modulo: getUrlParameter("_modulo"),
            }
        });
    }
    $(function() {
        $('[data-toggle="popover"]').popover({
            html: true,
            content: function() {
                let ModalPopoverId = $(this).attr("href").replaceAll("#", "")
                return $("#modalpopover_" + ModalPopoverId).html();
            }
        });
    });

    $(".showdivrecibo").click(function()
	{   
        let strCabecalho = `</strong>Recibos Gerados</strong>`;
		var modalsolmat = $("#modalrecibosgerados")[0].innerHTML;
		CB.modal({
			corpo: modalsolmat,
			titulo: strCabecalho,
			classe: 'quarenta'
		});
	});

    function novolote(inidnfitem, inqtd, inprodserv, date) {
        
        CB.post({
            objetos: `_x_i_lote_idprodserv=` + inprodserv + `&_x_i_lote_status=ABERTO&_x_i_lote_idfluxostatus=`+idfluxostatus+`&_x_i_lote_idnfitem=` + inidnfitem + `&_x_i_lote_qtdpedida=` + inqtd + `&_x_i_lote_qtdprod=` + inqtd + `&_x_i_lote_exercicio=` + date + `&_x_i_lote_idunidade=` +idunidadelote,
            parcial: true
        })
    }

    function showModal() {
        var strCabecalho = "<strong>Nova Parcela <button id='cbSalvar' type='button'  style='margin-left:370px' class='btn btn-danger btn-xs' onclick='geracontapagar();'><i class='fa fa-circle'></i>Salvar</button></strong> ";
        $("#cbModalTitulo").html((strCabecalho));

        var htmloriginal = $("#novaparcela").html();
        var objfrm = $(htmloriginal);

        objfrm.find("#formapagnovaparc").attr("name", "_modalnovaparcelacontapagar_idformapagamento_");
        objfrm.find("#valornovaparc").attr("name", "_modalnovaparcelacontapagar_valor_");
        objfrm.find("#vencnovaparc").attr("name", "_modalnovaparcelacontapagar_datapagto_");

        CB.modal({
            corpo: objfrm.html(),
            titulo: strCabecalho
        })
    }

    function geracontapagar() {
        let valTipo = $("[name='_modalnovaparcelacontapagar_tipo_']:checked").val() || "C";
        let idpessoa = $("[name=_1_u_nf_idpessoafat]").attr('cbvalue') || $("[name=_1_u_nf_idpessoafat]").val();

        var str = "_x9_i_contapagar_idformapagamento=" + $("[name=_modalnovaparcelacontapagar_idformapagamento_]").val() +
            "&_x9_i_contapagar_status=PENDENTE&_x9_i_contapagar_parcela=" + $('#parcela_parcelas').val() + "&_x9_i_contapagar_parcelas=" + $('#parcela_parcelas').val() + "&_x9_i_contapagar_valor=" + $("[name=_modalnovaparcelacontapagar_valor_]").val() +
            "&_x9_i_contapagar_datapagto=" + $("[name=_modalnovaparcelacontapagar_datapagto_]").val() +
            "&_x9_i_contapagar_datareceb=" + $("[name=_modalnovaparcelacontapagar_datapagto_]").val() +
            "&_x9_i_contapagar_tipo=" + valTipo + "&_x9_i_contapagar_visivel=S&_x9_i_contapagar_idpessoa=" + idpessoa +
            "&_x9_i_contapagar_tipoobjeto=nf&_x9_i_contapagar_idobjeto=" + $("[name=_1_u_nf_idnf]").val();

        CB.post({
            objetos: str,
            parcial: true,
            posPost: function(resp, status, ajax) {
                if (status = "success") {
                    $("#cbModalCorpo").html("");
                    $('#cbModal').modal('hide');
                } else {
                    alert(resp);
                }
            }
        });
    }
  
    function convertemoeda(vthis, inidnfitem) {

        var convmoeda = $(vthis).val();
        if (convmoeda.match(/,/)) {
            var convmoeda = convmoeda.replace(",", ".");
        }
        var inputext = jQuery('input[name$="vlritemext"]');
        var inputliq = jQuery('input[name$="vlrliq"]');
        var valueext = [];
        var valueliq = [];
        for (var i = 0; i < inputext.length; i++) {
            valueext.push($(inputext[i]).val());
            valueliq.push($(inputliq[i]).val());
            if (i >= 1) {
                var valueext1 = valueext[i];
                $(inputliq[i]).val(valueext1 * convmoeda);
            } else {
                $(inputliq[i]).val(valueext * convmoeda);
            }
        }

    }
  
 

    function preencheFormula(idprodserv, nomeformula) {
        if (idprodserv) {
            $.ajax({
                type: "get",
                url: "ajax/buscaformula.php?idprodserv=" + idprodserv,
                success: function(data) { //alert(data);
                    if (data.trim() == 'VAZIO') {
                        $("select[name='" + nomeformula + "']").addClass('hidden');
                      
                    } else {
                        $("select[name='" + nomeformula + "']").removeClass('hidden');
                        $("select[name='" + nomeformula + "']").html(data);
                       
                    }
                },
                error: function(objxmlreq) {
                    alert('Erro:<br>' + objxmlreq.status);
                }
            }) //$.ajax

        } else {
            console.warn("js: preencheendereco: Erro: idIdpessoa não informado;")
        }
    }

    // cria o item e chama o autocomplete
    function novoItem() {
        oTbItens = $("#tbItens tbody");
        iNovoItem = (oTbItens.find("input.idprodserv").length + 11);
        htmlTrModelo = $("#modeloNovoIten").html();
        htmlTrModelo = htmlTrModelo.replace("#nameidnfitem", "_" + iNovoItem + "#idnfitem");
        htmlTrModelo = htmlTrModelo.replace("#nameord", "_" + iNovoItem + "#ord");
        htmlTrModelo = htmlTrModelo.replace("#nameidprodserv", "_" + iNovoItem + "#idprodserv");
        htmlTrModelo = htmlTrModelo.replace("#nameidprodserv", "_" + iNovoItem + "#idprodserv");
        htmlTrModelo = htmlTrModelo.replace("#nameidprodserv", "_" + iNovoItem + "#idprodserv");
        htmlTrModelo = htmlTrModelo.replace("#nameidprodservformula", "_" + iNovoItem + "#idprodservformula");
        htmlTrModelo = htmlTrModelo.replace("#nameidprodservformula", "_" + iNovoItem + "#idprodservformula");
        htmlTrModelo = htmlTrModelo.replace("#nameidprodservformula", "_" + iNovoItem + "#idprodservformula");
        htmlTrModelo = htmlTrModelo.replace("#nameprodservdescr", "_" + iNovoItem + "#prodservdescr");
        htmlTrModelo = htmlTrModelo.replace("#nameprodservdescr", "_" + iNovoItem + "#prodservdescr");
        htmlTrModelo = htmlTrModelo.replace("#nameprodservdescr", "_" + iNovoItem + "#prodservdescr");
        htmlTrModelo = htmlTrModelo.replace("#namequantidade", "_" + iNovoItem + "#quantidade");
        htmlTrModelo = htmlTrModelo.replace("#botaodescritivo", "_" + iNovoItem + "#btdescritivo");
        htmlTrModelo = htmlTrModelo.replace("#botaodescritivo", "_" + iNovoItem + "#btdescritivo");
        htmlTrModelo = htmlTrModelo.replace("#botaoidprodserv", "_" + iNovoItem + "#btidprodserv");
        htmlTrModelo = htmlTrModelo.replace("#botaoidprodserv", "_" + iNovoItem + "#btidprodserv");

        htmlTrModelo = htmlTrModelo.replace(/#irow/g, iNovoItem);

        novoTr = "<tr class='dragExcluir nfitem'>" + htmlTrModelo + "</tr>";
        oTbItens.append(novoTr);
        criaAutocompletesProd();
    }

    function ordenaItens() {
        $.each($("#tbItens tbody").find("tr"), function(i, otr) {
            //Recupera objetos de update e de insert
            $(this).find(":input[name*=nfitem_ord],:input[name*=ord]").val(i);
        })
    }

    function excluiritem(idnfitem) {
        CB.post({
            "objetos": "_x_d_nfitem_idnfitem=" + idnfitem,
            parcial: true
        });
    }

    $("#excluirItem").droppable({
        accept: ".dragExcluir",
        drop: function(event, ui) {
            //verifica se existe o idresultado em mode de update. caso positivo, alternar para excluir
            $idnfitem = $(ui.draggable).attr("idnfitem");
            if (parseInt($idnfitem) && CB.acao !== "i") {
                if (confirm("Deseja realmente excluir o teste selecionado?")) {
                    ui.draggable.remove();
                    CB.post({
                        "objetos": "_x_d_nfitem_idnfitem=" + $idnfitem
                    });
                }
            } else {
                if ($(ui.draggable).find(":input[name*=#idnfitem]").length == 1) { //Modo de inclusão
                    ui.draggable.remove();
                }
            }
        }
    });

    function preencheendereco() {
        vIdPessoa = $(":input[name=_1_" + CB.acao + "_nf_idpessoa]").cbval();

        if (vIdPessoa) {
            $("#idendereco").html("<option value=''>Procurando....</option>");
            	
            $.ajax({
                type: "get",
                url: "ajax/buscaendereco.php?idpessoa=" + vIdPessoa,
                success: function(data) {
                    $("#idendereco").html(data);
                },
                error: function(objxmlreq) {
                    alert('Erro:<br>' + objxmlreq.status);
                }
            }) //$.ajax

        } else {
            console.warn("js: preencheendereco: Erro: idIdpessoa não informado;")
        }
    } //function preencheendereco(){

    function preencheenderecofat() {
        vIdPessoa = $(":input[name=_1_" + CB.acao + "_nf_idpessoafat]").cbval();

        if (vIdPessoa) {
            $("#idenderecofat").html("<option value=''>Procurando....</option>");
           
            $.ajax({
                type: "get",
                url: "ajax/buscaendereco.php?idpessoa=" + vIdPessoa,
                success: function(data) {
                    $("#idenderecofat").html(data);
                },
                error: function(objxmlreq) {
                    alert('Erro:<br>' + objxmlreq.status);
                }
            }) //$.ajax

        } else {
            console.warn("js: preencheendereco: Erro: idIdpessoa não informado;")
        }
    } //function preencheendereco(){

    //funcão para imprimir a etiqueta
    function imprimeEtiqueta(inid) {
        if (confirm("Imprimir Cupom?")) {

            $.ajax({
                type: "get",
                url: "ajax/impetiquetaped.php?idnf=" + inid,
                success: function(data) {
                    console.log(data);
                    alertAzul("Enviado para impressão", "", 1000);

                }
            });
        }
       
    }

    function financeiro(inidnfitem, inlinha) {

        var strCabecalho = "</strong><span class='h5'>" + $("#descr" + inidnfitem).val() + "&nbsp;&nbsp;</span> <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='CB.post();'><i class='fa fa-circle'></i>Salvar</button></strong>";
       

        var htmloriginal = $("#Modfiscal" + inidnfitem).html();
        var objfrm = $(htmloriginal);

        objfrm.find("#valipi" + inidnfitem).attr("name", "_" + inlinha + "_u_nfitem_valipi");
        objfrm.find("#valpis" + inidnfitem).attr("name", "_" + inlinha + "_u_nfitem_pis");
        objfrm.find("#valbcpis" + inidnfitem).attr("name", "_" + inlinha + "_u_nfitem_bcpis");
        objfrm.find("#valcofins" + inidnfitem).attr("name", "_" + inlinha + "_u_nfitem_cofins");
        objfrm.find("#pDevol" + inidnfitem).attr("name", "_" + inlinha + "_u_nfitem_pDevol");
        objfrm.find("#vIPIDevol" + inidnfitem).attr("name", "_" + inlinha + "_u_nfitem_vIPIDevol");
        objfrm.find("#valbccofins" + inidnfitem).attr("name", "_" + inlinha + "_u_nfitem_bccofins");
        objfrm.find("#basecalc" + inidnfitem).attr("name", "_" + inlinha + "_u_nfitem_basecalc");
        objfrm.find("#vicmsdeson" + inidnfitem).attr("name", "_" + inlinha + "_u_nfitem_vicmsdeson");


        objfrm.find("#obs" + inidnfitem).attr("name", "_" + inlinha + "_u_nfitem_obs");
       
        CB.modal({
            titulo: strCabecalho,
            corpo: objfrm.html(),
            classe: 'noventa'
        });

    } //function financeiro(inidnfitem,inlinha){

    function esgotarlote(vidlote) {
        //LTM - 07-04-2021 - Retorna o idFluxoStatus Selecionado
        var idfluxostatus = getIdFluxoStatus('loteproducao', 'ESGOTADO');
        var idFluxoStatusHist = getIdFluxoStatusHist('loteproducao', vidlote);
        document.body.style.cursor = 'wait';
        if (confirm("Deseja alterar o status do lote para esgotado?")) {
            CB.post({
                objetos: "_x_u_lote_idlote=" + vidlote +
                    "&_x_u_lote_idfluxostatus=" + idfluxostatus +
                    "&_x_u_lote_status=ESGOTADO&_x_u_lote_qtddisp=0",
                posPost: function() {
                    CB.post({
                        urlArquivo: 'ajax/_fluxo.php?fluxo=fluxo',
                        refresh: false,
                        objetos: {
                            "_modulo": 'loteproducao',
                            "_primary": 'idlote',
                            "_idobjeto": vidlote,
                            "idfluxo": '',
                            "idfluxostatushist": idFluxoStatusHist,
                            "idstatusf": idfluxostatus,
                            "statustipo": 'ESGOTADO',
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
        }
        document.body.style.cursor = '';
    }

    //inserir um item no estoque pedido e da reload no objeto modal
    function pedidolotecons(inidnfitem, inidlote) {
        CB.post({
            objetos: "_x_i_lotecons_idobjeto=" + inidnfitem + "&_x_i_lotecons_tipoobjeto=nfitem&_x_i_lotecons_idlote=" + inidlote
        });
    }

    function dlotecons(inidlotecons) {
        CB.post({
            objetos: "_x_d_lotecons_idlotecons=" + inidlotecons
        });
    }

    function showdivrecibo() {
        document.getElementById('inputrecibo').style.display = "inline-block"; // 1 
        document.getElementById('rotrecibo').style.display = "none"; // 2
    }

    function altcheck(vtab, vcampo, vid, vcheck) {
        CB.post({
            objetos: "_x_u_" + vtab + "_id" + vtab + "=" + vid + "&_x_u_" + vtab + "_" + vcampo + "=" + vcheck,
            parcial: true
        });
    }

    function altcheckmanual(vtab, vcampo, vid, vcheck) {
        if(vcheck == 'Y'){
            alertAtencao('Ao habilitar esta configuração, as alterações terão efeito apenas neste pedido. Para que a modificação seja permanente, a configuração deve ser ajustada no cadastro do produto.');
        }

        CB.post({
            objetos: "_x_u_" + vtab + "_id" + vtab + "=" + vid + "&_x_u_" + vtab + "_" + vcampo + "=" + vcheck,
            parcial: true,
            posPost: function(resp, status, ajax) {
                $("#cbModalCorpo").html("");
                $('#cbModal').modal('hide');
            }
        });
    }

    function gerarRemessa(idnf, idcontapagar, idformapagamento, vcheck)
    {
        (vcheck.checked == true) ? vthis = "Y": vthis = "N";
        if(vthis == 'Y')
        {
            var str = `_1_u_nf_idnf=${idnf}&_1_u_nf_idformapagamento=${idformapagamento}&idcontapagar_remessa=${idcontapagar}`;

            CB.post({
                objetos: str,
                parcial: true,
                posPost: function() {
                    boletopdf(idcontapagar, 'Y', boleto);
                }
            });
        }        
    }

    function marcarTodosRemessa(check)
    {
        if(check.checked)
        {
            $('.gerarremessa').prop('checked', 'checked');
            $('.enviarRemessa').show();
        } else {
            $('.gerarremessa').removeProp('checked');
            $('.enviarRemessa').hide();
        }      
    }

    function gerarRemessaTodos(idnf, idformapagamento, vcheck)
    {
        arrIdContaPagar = [];
        $('.gerarremessa').each(function(key, elem){
            if(elem.checked)
            {
                arrIdContaPagar.push($(elem).attr('idcontapagar'));
            }  
        });

        if(arrIdContaPagar)
        {
            var str = `_1_u_nf_idnf=${idnf}&_1_u_nf_idformapagamento=${idformapagamento}&idcontapagar_remessa=${arrIdContaPagar}&tipo=enviarTodos`;
            CB.post({
                objetos: str,
                parcial: true,
                posPost: function() {
                    str1 = '';
                    i = 0;
                    jQuery.each(arrIdContaPagar, async function(key, inidcontapagar){      
                        comercial = (i == 0) ? '' : '&';          
                        str1 += `${comercial}_x${i}_u_contapagar_idcontapagar=${inidcontapagar}&_x${i}_u_contapagar_boletopdf=Y`;
                        i++;
                    });
                    str2 = "&_y_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_y_u_nf_emailboleto=Y";
                    
                    CB.post({
                        objetos: str1 + str2,
                        parcial: true,
                        refresh: false,
                        msgSalvo: "Gerando boletos...",
                        posPost: function() {
                            jQuery.each(arrIdContaPagar, async function(key, inidcontapagar){
                                boleto = $(`.boleto_${inidcontapagar}`).attr('boleto');
                                let vurl = "inc/boletophp/" + boleto + ".php?idcontapagar=" + inidcontapagar + "&geraarquivo=Y&gravaarquivo=Y";
                                document.body.style.cursor = 'wait';
                                $.get(
                                    vurl,
                                    resposta => (resposta == "OK") ? alertAzul("Boleto gerado!") : alert(resposta)
                                );
                                document.body.style.cursor = '';
                            });
                        }
                    });                                       
                }
            });
        }
    }

    function certnfitem(vtab, vcampo, vid, vcheck, vidproduto) {
        CB.post({
            objetos: "_x_u_" + vtab + "_id" + vtab + "=" + vid + "&_x_u_" + vtab + "_" + vcampo + "=" + vcheck,
            parcial: true
        });
    }

    // gera o boleto e marca para envio
    function boletopdf(inidcontapagar, vcheck, boleto) 
    {
        if(vcheck.type == 'checkbox'){
            (vcheck.checked == true) ? vcheck = "Y": vcheck = "N";
        }
        
        str1 = "_x_u_contapagar_idcontapagar=" + inidcontapagar + "&_x_u_contapagar_boletopdf=" + vcheck;
        str2 = "&_y_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_y_u_nf_emailboleto=Y";

        if (vcheck == 'Y') {
            str = str1 + str2;
        } else {
            str = str1;
        }

        CB.post({
            objetos: str,
            parcial: true,
            refresh: false,
            msgSalvo: "Gerando boleto...",
            posPost: function() {
                if (vcheck == 'Y') {
                    let vurl = "inc/boletophp/" + boleto + ".php?idcontapagar=" + inidcontapagar + "&geraarquivo=Y&gravaarquivo=Y";
                    document.body.style.cursor = 'wait';
                    $.get(
                        vurl,
                        resposta => (resposta == "OK") ? alertAzul("Boleto gerado!") : alert(resposta)
                    );
                    document.body.style.cursor = '';
                }
            }
        });
    }

    function envioemailorc(inidnf, inval, flag) {

        if (flag == 1) {
            if (inval != 'Y') {
                CB.post({
                    objetos: "_x_u_nf_idnf=" + inidnf + "&_x_u_nf_envioemailorc=" + inval
                });
            } else {
                var idnf = $("[name=_1_u_nf_idnf]").val();
                var idemailvirtualconf = $("#emailunico").val();
                var idempresa = $("#idempresaemail").val();

                if (confirm("Deseja realmente enviar o email?")) {
                    //gerar o arquivo com os detalhes para envio
                    vurl = "report/nf.php?_acao=u&idnf=" + inidnf + "&geraarquivo=Y&gravaarquivo=Y";

                    $.ajax({
                        type: "get",
                        url: vurl,
                        success: function(data) {
                            console.log(data);
                            retorno = data.split("script>");
                            if(retorno[2] == 'OK'){
                                alertAzul("Gerado arquivo para envio.", "", 1000);
                                CB.post({
                                    objetos: "_x_u_nf_idnf=" + inidnf + "&_x_u_nf_envioemailorc=" + inval,
                                    posPost: function() {
                                        altremetenteemail(idnf, idemailvirtualconf, 'ORCPROD', idempresa)
                                    }
                                });
                            } else {
                                alertAtencao(retorno[2]);
                            }                      
                        }
                    });
                }
            }
        } else {
            if (flag == 2) {
                var setemail = $("#setemail").val();

                if (setemail == "1") {
                    alert("É necessário escolher um remetente para o envio");
                } else {
                    if (inval != 'Y') {
                        CB.post({
                            objetos: "_x_u_nf_idnf=" + inidnf + "&_x_u_nf_envioemailorc=" + inval
                        });
                    } else {

                        if (confirm("Deseja realmente enviar o email?")) {
                            //gerar o arquivo com os detalhes para envio
                            vurl = "report/nf.php?_acao=u&idnf=" + inidnf + "&geraarquivo=Y&gravaarquivo=Y";

                            $.ajax({
                                type: "get",
                                url: vurl,
                                success: function(data) {
                                    console.log(data);
                                    alertAzul("Gerado arquivo para envio.", "", 1000);
                                    CB.post({
                                        objetos: "_x_u_nf_idnf=" + inidnf + "&_x_u_nf_envioemailorc=" + inval
                                    });

                                }
                            });
                        }
                    }
                }
            }
        }
    }

    function enviaemailped(inidnf, inval) {
        if (inval != 'N') {
            var fqdt = "Deseja realmente enviar o email?";
        } else {
            var fqdt = "Não enviar o email?";
        }

        if (confirm(fqdt)) {
            CB.post({
                objetos: "_x_u_nf_idnf=" + inidnf + "&_x_u_nf_enviaemailped=" + inval
            });
        }
    }

    function cancelanfe() {

        vurl = "../inc/nfe/sefaz4/func/nfcancel.php?idnotafiscal="+_1_u_nf_idnf;

        if (confirm("Justificar o motivo do cancelamento no campo Inf. NFe (Mínimo 15 caracteres)!!!")) {
            if (confirm("Deseja realmente cancelar a Nota Fiscal no Sefaz?")) {
                $.ajax({
                    type: "get",
                    url: vurl,
                    success: function(data) {
                        alert(data);
                        document.location.reload();
                    },
                    error: function(objxmlreq) {
                        alert('Erro:\n' + objxmlreq.status);
                    }
                }) //$.ajax
            }
        }
    }

    function enviarcartacor() {
        vurl = "../inc/nfe/sefaz4/func/nfcorrecao.php?idnotafiscal="+_1_u_nf_idnf;

        $.ajax({
            type: "get",
            url: vurl,
            success: function(data) {
                alert(data);
                document.location.reload();
            },
            error: function(objxmlreq) {
                alert('Erro:\n' + objxmlreq.status);
            }
        }) //$.ajax

    }

    function alterartransportadora(vthis, inidnf) {
        var inidtranspr = vthis.value;
        if (confirm("Alterar a transportadora com a NF concluída, resultará no envio de uma Carta de Correção. Deseja realmente alterar?")) {
            CB.post({
                objetos: `_alteratransp_u_nf_idnf=` + inidnf + `&_alteratransp_u_nf_idtransportadora=` + inidtranspr,
                parcial: true                
            })
        }
    }

    function envionfe(pedidopend) {

        $('#limparfilial').removeAttr('onclick').removeClass('pointer').removeClass('fa-clone').addClass('not-allowed');

        if (pedidopend == 1) {
            alert("Dado(s) da(s) partida(s) não informado(s)!!!");
        }

        if ($('#inadimplencia').attr('inadimplencia') == 1) {
            msg = "ALERTA: Cliente com parcela em ATRASO. Deseja enviar a Nota Fiscal para a Sefaz?";
        } else {
            msg = "Deseja enviar a Nota Fiscal para a Sefaz?";
        }

        vurl = "../inc/nfe/sefaz4/func/nfenvio.php?idnotafiscal="+_1_u_nf_idnf;

        if (confirm(msg)) {
            $.ajax({
                type: "get",
                url: vurl,
                success: function(data) {
                    alert(data);
                    document.location.reload();
                },
                error: function(objxmlreq) {
                    alert('Erro:\n' + objxmlreq.status);
                }
            }) //$.ajax
        }

        $('#limparfilial').attr('onclick', 'limparFilial()').removeClass('not-allowed').addClass('fa-eraser').addClass('pointer');
    }

    function consultanfe() {
        vurl = "../inc/nfe/sefaz4/func/recibo.php?idnotafiscal="+_1_u_nf_idnf;
        $.ajax({
            type: "get",
            url: vurl,
            success: function(data) {
                alert(data);
                document.location.reload();
            },
            error: function(objxmlreq) {
                alert('Erro:\n' + objxmlreq.status);
            }
        }) //$.ajax
    }

    function inrotulo(inidpessoa, inidcontato, inidendereco, inidnf) {
        if (confirm("Deseja vincular o endereço ao Rótulo?")) {
            var idrotulo = $("#idrotulo").val();
            CB.post({
                objetos: "_x_i_rotuloresultado_idpessoa=" + inidpessoa + "&_x_i_rotuloresultado_idcontato=" + inidcontato + "&_x_i_rotuloresultado_idendereco=" + inidendereco + "&_x_i_rotuloresultado_idnf=" + inidnf + "&_x_i_rotuloresultado_idrotulo=" + idrotulo
            });
        }
    }

    function verificatransp(vthis, inidtranspr, innome) {
        var idtransportadora = $("[name=_1_u_nf_idtransportadora]").val();

        if (confirm("Transportadora padrão ( " + innome + " ), Deseja realmente alterar a transportadora?") == true) {
            $("[name=_1_u_nf_idtransportadora] option[value='" + idtransportadora + "']").attr("selected", "selected");
        } else {
            location.reload();
        }


    }

    function collapseiten(vcollase) {
        if (vcollase == "N") {
            //nao mostrar
            $(".collapseall").removeClass( "collapse in" ).addClass( "collapse" );
            $(".collapseFechar").hide();
            $(".collapseAbrir").show();

            /* CB.post({
                objetos: `_x_u_nf_idnf=` + $("[name=_1_u_nf_idnf]").val() + `&collapse=N`

            })  */
        } else {
            //mostrar
            $(".collapseall").removeClass( "collapse" ).addClass( "collapse in" );
            $('.estoque').removeAttr('style');
            $(".collapseFechar").show();
            $(".collapseAbrir").hide();
           /*  CB.post({
                objetos: `_x_u_nf_idnf=` + $("[name=_1_u_nf_idnf]").val() + `&collapse=Y`

            }) */
        }

    }

    //Salvar o estado de cada atividade: collapse/collapse in
    $('[id*=nfiteminfo]').on('hide.bs.collapse', function(e) {
        vIdnfitem = $("#" + e.currentTarget.id).attr("idnfitem");
        CB.post({
            objetos: `_x_u_nfitem_idnfitem=${vIdnfitem}&_x_u_nfitem_collapse=collapse`, 
            parcial: true,
            refresh: false
        })
    }).on('show.bs.collapse', function(e) {
        vIdnfitem = $("#" + e.currentTarget.id).attr("idnfitem");
        CB.post({
            objetos: `_x_u_nfitem_idnfitem=${vIdnfitem}&_x_u_nfitem_collapse=collapse in`,
            parcial: true,
            refresh: false
        })
    });

    CB.prePost = function() {
        if(tipoNF == '1') {
            if($('.excedendo-estoque-lote').length) {
                alert('Há lotes que excedem quantidade do estoque');
                return false;
            }

            if($('.excedendo-pedido').length)  {
                alert('Há itens sobrando, por favor ajuste a quantidade.');
                return false;
            }

            if($('.faltando-lote').length)  {
                if(!confirm('Faltam itens. Deseja continuar?')) return false;
            }
        }
        
        //Caso seja mudança de status
        if ((_1_u_nf_status != "PEDIDO") && ($("[name=_1_u_nf_status]").val() == "PEDIDO")) {
            //Adicionar 1 input ao POST do carbon, para ser referenciado posteriormente do lado do servidor
            return {
                objetos: "collapse=Y"
            }
        }
        if ((_1_u_nf_status != "INICIO" || _1_u_nf_status != 'SOLICITADO') && ($("[name=_1_u_nf_status]").val() == "INICIO" || _1_u_nf_status == 'SOLICITADO')) {
            //Adicionar 1 input ao POST do carbon, para ser referenciado posteriormente do lado do servidor
            return {
                objetos: "collapse=N"
            }

        }
    }

    if ($("[name=_1_u_nf_idnf]").val()) {
        $("#cbupload").dropzone({
            idObjeto: $("[name=_1_u_nf_idnf]").val(),
            tipoObjeto: 'nf',
            idPessoaLogada: sessao_idpessoa
        });
        $("#cbupload-custom").dropzone({
            idObjeto: $("[name=_1_u_nf_idnf]").val(),
            tipoObjeto: 'nfComprovante',
            idPessoaLogada: sessao_idpessoa
        });
    }

    function alttipoemail(vid, vcheck, idemailvirtualconf, idempresa, dominio, idempresaemailobjeto = null) {
        CB.post({
            objetos: `_1_u_nf_idnf=${vid}&_1_u_nf_tipoenvioemail=${vcheck}&_1_u_nf_emaildadosnfe=${$("[name=_1_u_nf_emaildadosnfe]").val()}&_1_u_nf_emaildadosnfemat=${$("[name=_1_u_nf_emaildadosnfemat]").val()}`,
            parcial: true,
            refresh: false,
            msgSalvo: vcheck + " selecionado.",
            posPost: function() {
                if (vcheck == 'VENDA') {
                    altremetenteemail(vid, idemailvirtualconf, 'NFP', idempresa, idempresaemailobjeto)
                } else {
                    if (vcheck == 'MATERIAL') {
                        altremetenteemail(vid, idemailvirtualconf, 'NFPS', idempresa, idempresaemailobjeto)
                    }
                }
            }
        });
    }

    function envioemail(vthis, inidnf) {
        let totalwarn = $("input[name='namecert']:checked").siblings("i.fa-warning").length;
        if (totalwarn === 0) {
            var inval = $(vthis).attr('valor');

            if ($("input[type=radio]:checked").length > 0) {

                if (inval != 'N') {
                    var fqdt = "Deseja realmente enviar o email?";
                } else {
                    var fqdt = "Não enviar o email?";
                }

                if (confirm(fqdt)) {
                    CB.post({
                        objetos: "_1_u_nf_idnf=" + inidnf + "&_1_u_nf_envioemail=" + inval + "&_1_u_nf_emaildadosnfe=" + $("[name=_1_u_nf_emaildadosnfe]").val() + "&_1_u_nf_emaildadosnfemat=" + $("[name=_1_u_nf_emaildadosnfemat]").val(),
                        parcial: true,
                        refresh: false,
                        msgSalvo: "Salvo",
                        posPost: function() {
                            $(vthis).removeClass("verde");
                            $(vthis).removeClass("vermelho");
                            $(vthis).removeClass("cinza");
                            $(vthis).removeClass("amarelo");
                            $('#cartanovo').removeClass("verde");
                            $('#cartanovo').removeClass("vermelho");
                            $('#cartanovo').removeClass("cinza");
                            $('#cartanovo').removeClass("amarelo");

                            if (inval == 'Y') {
                                $(vthis).addClass("amarelo");
                                $('#cartanovo').addClass("amarelo");
                                $(vthis).attr("valor", "N");
                            } else {
                                $(vthis).addClass("cinza");
                                $('#cartanovo').addClass("cinza");
                                $(vthis).attr("valor", "Y");
                            }

                        }
                    });
                }
            } else {
                alert("Selecione o EMAIL de Saída!!!");
            }
        } else {
            alert("Existem certificados pendentes a serem gerados!");
        }
    }

    /*
     * Duplicar pedido [ctrl]+[d]
     */
    $(document).keydown(function(event) {

        if (!((event.ctrlKey || event.altKey) && event.keyCode == 68)) return true;

        if (!teclaLiberada(event)) return; //Evitar repetição do comando abaixo

        if (confirm("Deseja duplicar o pedido?")) {
            ST.desbloquearCBPost();
            CB.post({
                objetos: "_x_i_nf_idobjetosolipor="+_1_u_nf_idnf+"&_x_i_nf_tipoobjetosolipor=nf",
                parcial: true,
                posPost: function(data, textStatus, jqXHR) {
                    shpedido(CB.lastInsertId);
                }
            });
        } //if(confirm("Deseja duplicar o pedido?")){

        return false;
    });

    function shpedido(inidpedido) {
        janelamodal('?_modulo=pedido&_acao=u&idnf=' + inidpedido + '');
    }

    function preencheti(inidnf) {

        $("#idtipoprodserv" + inidnf).html("<option value=''>Procurando....</option>");

        $.ajax({
            type: "get",
            url: "ajax/buscacontaitem.php",
            data: {
                idcontaitem: $("#idcontaitem" + inidnf).val()
            },

            success: function(data) {
                $("#idtipoprodserv" + inidnf).html(data);
            },

            error: function(objxmlreq) {
                alert('Erro:<br>' + objxmlreq.status);

            }
        }) //$.ajax

    }

    function editarclientefat() {

        $("[name=_1_u_nf_idpessoafat]").removeClass("desabilitado");
        $("[name=_1_u_nf_idenderecofat]").removeClass("desabilitado");
        $("[name=_1_u_nf_idpessoafat]").removeAttr("disabled");
        $("[name=_1_u_nf_idenderecofat]").removeAttr("disabled");
    }

    function editarobsint() {
        if ($('#obsinput').attr('class') == "hidden") {
            $('#obsinput').removeClass('hidden');
            $('#obstxt').addClass('hidden');
            $('#obsinterna').addClass('collapse in');
        } else {
            $('#obstxt').removeClass('hidden');
            $('#obsinput').addClass('hidden');
        }
    }
/*
    function editarobsped() {
        if ($('#obsinputped').attr('class') == "hidden") {
            $('#obsinputped').removeClass('hidden');
            $('#obstxtped').addClass('hidden');
            $('#obspedido').addClass('collapse in');
        } else {
            $('#obstxtped').removeClass('hidden');
            $('#obsinputped').addClass('hidden');
        }
    }
*/
    function editarobs() {

        if ($('#obsinputobs').attr('class') == "hidden") {
            $('#obsinputobs').removeClass('hidden');
            $('#obstxtobs').addClass('hidden');
        } else {
            $('#obstxtobs').removeClass('hidden');
            $('#obsinputobs').addClass('hidden');
        }
    }

    function editaremail(inobs, inobsinput) {

        if ($('#' + inobsinput).attr('class') == "hidden") {
            $('#' + inobsinput).removeClass('hidden');
            $('#' + inobs).addClass('hidden');
        } else {
            $('#' + inobs).removeClass('hidden');
            $('#' + inobsinput).addClass('hidden');
        }

    }

    function atualizavl(vthis, inidcontapagar) {
        CB.post({
            objetos: "_1_u_contapagar_idcontapagar=" + inidcontapagar + "&_1_u_contapagar_valor=" + $(vthis).val(),
            parcial: true
        });
    }

    function atualizavlitem(vthis, inidcontapagar) {
        CB.post({
            objetos: "_atitem_u_contapagaritem_idcontapagaritem=" + inidcontapagar + "&_atitem_u_contapagaritem_valor=" + $(vthis).val(),
            parcial: true
        });
    }

    function atualizavlcp(vthis, inidcontapagar) {
        CB.post({
            objetos: "_1_u_contapagar_idcontapagar=" + inidcontapagar + "&_1_u_contapagar_valor=" + $(vthis).val(),
            parcial: true
        });
    }

    function atualizaobscp(vthis, inidcontapagar) {
        CB.post({
            objetos: `_x_u_contapagar_idcontapagar=` + inidcontapagar + `&_x_u_contapagar_obs=` + $(vthis).val(),
            parcial: true
        })
    }


    function novaformalizacao(idnfitem, idprodserv, idpessoa, idprodservformula,especial) {

        var qtdd = $("#" + idnfitem + "_loteqtdd").val();
        var idnf = $("[name=_1_u_nf_idnf]").val();

        if(especial=='Y'){//autogenas
            var idsolfab = $("#" + idnfitem + "_loteidsolfab").val();     
            if (qtdd == 0) {
                alert('Favor informar a quantidade solicitada.');
            } else if (idprodservformula == 0) {
                alert('Favor selecionar a Formula.');
            } else if (idsolfab == 0) {
                alert('Favor selecionar a Solicitação de Fabricação.');
            } else {

                //LTM - 07-04-2021 - Retorna o idFluxoStatus Selecionado
                var idfluxostatus = getIdFluxoStatus('loteproducao', 'FORMALIZACAO');
                var idempresaUrl = (getUrlParameter("_idempresa")) ? "&_1_i_lote_idempresa=" + getUrlParameter("_idempresa") : '';

                CB.post({
                    objetos: `_1_i_lote_qtdpedida=` + qtdd +
                        `&_1_i_lote_idobjetoprodpara=` + idnfitem +
                        `&_1_i_lote_tipoobjetoprodpara=nfitem&_1_i_lote_idprodservformula=` + idprodservformula +
                        `&_1_i_lote_idpessoa=` + idpessoa +
                        `&_1_i_lote_idprodserv=` + idprodserv +
                        `&_1_i_lote_idsolfab=` + idsolfab +
                        `&_1_i_lote_idunidade=2` +
                        idempresaUrl,
                    parcial: true
                })
            }
        }else{

            if (qtdd == 0) {
                alert('Favor informar a quantidade solicitada.');
            } else if (idprodservformula == 0) {
                alert('Favor selecionar a Formula.');
            } else {

                //LTM - 07-04-2021 - Retorna o idFluxoStatus Selecionado
                var idfluxostatus = getIdFluxoStatus('loteproducao', 'FORMALIZACAO');
                var idempresaUrl = (getUrlParameter("_idempresa")) ? "&_1_i_lote_idempresa=" + getUrlParameter("_idempresa") : '';

                CB.post({
                    objetos: `_1_i_lote_qtdpedida=` + qtdd +
                        `&_1_i_lote_idobjetoprodpara=` + idnfitem +
                        `&_1_i_lote_tipoobjetoprodpara=nfitem&_1_i_lote_idprodservformula=` + idprodservformula +
                        `&_1_i_lote_idpessoa=` + idpessoa +
                        `&_1_i_lote_idprodserv=` + idprodserv +
                        `&_1_i_lote_idunidade=2` +
                        idempresaUrl,
                    parcial: true
                })
            }

        }    
    }

    function listaagente(vthis, idnfitem, idpessoa, idprodservformula) {

        if (idprodservformula == 0) {
            alert('Favor selecionar a Fórmula.');
        } else {
            $.ajax({
                type: "get",
                url: "ajax/buscaagentes.php",
                data: {
                    idsolfab: $(vthis).val(),
                    idprodservformula: idprodservformula,
                    idpessoa: idpessoa
                },

                success: function(data) {
                    $("#listaagentes" + idnfitem).html(data);
                },

                error: function(objxmlreq) {
                    alert('Erro:<br>' + objxmlreq.status);

                }
            }) //$.ajax
        }

    }

    function atualizaclientefat(vthis, inidnf) {
      
            CB.post({
                objetos: "_alt_u_nf_idnf=" + inidnf + "&_alt_u_nf_idpessoafat=" + $(vthis).attr("cbvalue"),
                parcial: true
            })
        
    }

    function inserinatop(cfop, inidnf) {

        var qtditens = $('#tbItens tr:not(.cabitem)').length;

        if(qtditens>0){
            if (confirm("Alterar a Natureza da Operação, também irá alterar o CFOP de todos os itens, deseja continuar?")) {

                CB.post({
                    objetos: "_1_u_nf_idnf=" + inidnf + "&_1_u_nf_idnatop=" + cfop + "&alteranatop=Y",
                    parcial: true,
                    posPost: function() {
                        novoItem();
                    }
                })
            }else{
                document.location.reload();
            }

        }else{
            CB.post({
                objetos: "_1_u_nf_idnf=" + inidnf + "&_1_u_nf_idnatop=" + cfop + "&alteranatop=Y",
                parcial: true,
                posPost: function() {
                    novoItem();
                }
            })
        }

       
    }

    function alteragnre(vthis) {
        CB.post({
            objetos: "_1_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_1_u_nf_nnfe=" + $("[name=_1_u_nf_nnfe]").val() + "&_1_u_nf_gnre=" + $(vthis).val() + "&_1_u_nf_dtemissao=" + $("[name=_1_u_nf_dtemissao]").val() + "&gnreval=" + $("[name=gnreval]").val(),
            parcial: true
        })
    }

    function alterafatgnre(vthis, vidcontapagar) {
        CB.post({
            objetos: "_1_u_contapagar_idcontapagar=" + vidcontapagar + "&_1_u_contapagar_obs=" + $(vthis).val(),
            parcial: true
        })
    }

    function atualizainfcpl(vthis) {
        CB.post({
            objetos: "_1_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_1_u_nf_infcpl=" + $(vthis).val(),
            parcial: true
        })
    }

    function emaildadosnfemat(vthis) {
        CB.post({
            objetos: "_1_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_1_u_nf_emaildadosnfemat=" + $(vthis).val(),
            parcial: true
        })
    }

    function emaildadosnfe(vthis) {
        CB.post({
            objetos: "_1_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_1_u_nf_emaildadosnfe=" + $(vthis).val(),
            parcial: true
        })
    }

    function atualizact(vthis) {
        CB.post({
            objetos: "_1_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_1_u_nf_infcorrecao=" + $(vthis).val(),
            parcial: true
        })
    }

    function gerarcomissao(indatapagto, invalor, inidpessoa, idformapagamento, idcontapagar, instatus) {

        CB.post({
            objetos: "_1_i_contapagaritem_datapagto=" + indatapagto + "&_1_i_contapagaritem_idformapagamento=" + idformapagamento + "&_1_i_contapagaritem_idpessoa=" + inidpessoa + "&_1_i_contapagaritem_idobjetoorigem=" + idcontapagar + "&_1_i_contapagaritem_tipoobjetoorigem=contapagar&_1_i_contapagaritem_status=" + instatus + "&_1_i_contapagaritem_valor=" + invalor,
            parcial: true
        })

    }

    function atualizaparc(vthis) {
        CB.post({
            objetos: "_parc_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_parc_u_nf_parcelas=" + $(vthis).val() ,
            parcial: true       
        })
    }

    function importacao(instr) {
        var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';
        CB.modal({
            url: '?_modulo=infimportacao' + instr + idempresa,
            header: 'Email'
        })
    }

    function altremetenteemail(idnf, idemailvirtualconf, tipoenvio, idempresa, idempresaemailobjeto = null) {
        if(idempresaemailobjeto?.length == 0) {
            CB.post({
                objetos: `_w_i_empresaemailobjeto_idempresa=${idempresa}&_w_i_empresaemailobjeto_idemailvirtualconf=${idemailvirtualconf}&_w_i_empresaemailobjeto_tipoenvio=${tipoenvio}&_w_i_empresaemailobjeto_tipoobjeto=nf&_w_i_empresaemailobjeto_idobjeto=${idnf}`,
                parcial: true
            })
        } else {
            CB.post({
                objetos: `_w_u_empresaemailobjeto_idempresaemailobjeto=${idempresaemailobjeto}&_w_u_empresaemailobjeto_idempresa=${idempresa}&_w_u_empresaemailobjeto_idemailvirtualconf=${idemailvirtualconf}&_w_u_empresaemailobjeto_tipoenvio=${tipoenvio}&_w_u_empresaemailobjeto_tipoobjeto=nf&_w_u_empresaemailobjeto_idobjeto=${idnf}`,
                parcial: true
            })
        }
    }

    function consumo(inidlote) {
       
        CB.modal({
            titulo: "</strong>Histórico do Lote</strong>",
            corpo: $("#consumo" + inidlote).html(),
            classe: 'sessenta',
        });

    }

    function transferirnf() {
        $.ajax({
            type: "get",
            url: "ajax/htmltransferirnf.php",
            data: {
                idnf: $("[name=_1_u_nf_idnf]").val()
            },

            success: function(data) {

                CB.modal({
                    titulo: "</strong>Gerar NF de retorno  <button type='button' class='btn btn-danger btn-xs' onclick='salvartransferencia();'><i class='fa fa-circle'></i>Salvar</button></strong>",
                    corpo: data,
                    classe: 'sessenta'
                });
            },

            error: function(objxmlreq) {
                alert('Erro:<br>' + objxmlreq.status);

            }
        }) //$.ajax	

    }

    function salvartransferencia() {
        if ($("[name=_dev_u_nf_idpessoatransf]").val() == '' || $("[name=_dev_u_nf_idempresatransf]").val() == '') {
            alert("É necessário marcar um dos itens e selecionar a natureza da operação.");
        } else {

            var strcbpost = "_dev_u_nf_idpessoatransf=" + $("[name=_dev_u_nf_idpessoatransf]").val() + "&_dev_u_nf_idfinalidadeprodserv=" + $("[name=_dev_u_nf_idfinalidadeprodserv]").val() + "&_dev_u_nf_idempresatransf=" + $("[name=_dev_u_nf_idempresatransf]").val() + "&_dev_u_nf_statustransf=PENDENTE&_dev_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val();

            console.log(strcbpost);
            CB.post({
                objetos: strcbpost,
                parcial: true,
                msgSalvo: "Gerada Transferência",
                posPost: function(resp, status, ajax) {
                    if (status = "success") {
                        $("#cbModalCorpo").html("");
                        $('#cbModal').modal('hide');
                    } else {
                        alert(resp);
                    }
                }
            });
        }

    }

    function duplicarvenda() {
        $.ajax({
            type: "get",
            url: "ajax/htmlduplicar.php",
            data: {
                idnf: $("[name=_1_u_nf_idnf]").val()
            },

            success: function(data) {

                CB.modal({
                    titulo: "</strong>Dividir Nota <button type='button' class='btn btn-danger btn-xs' onclick='salvarduplicar();'><i class='fa fa-circle'></i>Salvar</button></strong>",
                    corpo: data,
                    classe: 'sessenta'
                });
            },

            error: function(objxmlreq) {
                alert('Erro:<br>' + objxmlreq.status);

            }
        }) //$.ajax	

    }

    function salvarduplicar() {
        var allInputs = $("#itensdev").find("input").serialize();

        CB.post({
            objetos: allInputs + "&nf_dividir=Y&idnfori=" + $("[name=_1_u_nf_idnf]").val(),
            parcial: true,
            posPost: function(resp, status, ajax) {
                $('#cbModal').modal('hide');
            }
        })
    }

    function preencheemitente() {
        $("#idpessoaenv").html("<option value=''>Procurando....</option>");

        $.ajax({
            type: "get",
            url: "ajax/buscafornecedor.php",
            data: {
                idempresa: $("#idempresaenv").val()
            },

            success: function(data) {
                $("#idpessoaenv").html(data);
            },

            error: function(objxmlreq) {
                alert('Erro:<br>' + objxmlreq.status);

            }
        }) //$.ajax
    }

    function atualizaproporcao(vthis, vidnfconfpagar) {
        var valor = 0;
        $(":input[name*=nfconfpagar_proporcao]").each(function() {
            var string1 = $(this).val();
            var numero = parseFloat(string1.replace(',', '.'));
            valor = valor + numero;
        });
        console.log(valor);
        if (valor > 100) {
            alert("A soma das proporções não deve passar de 100.");
            $(vthis).val('');
        } else {
            CB.post({
                objetos: "_pr_u_nfconfpagar_idnfconfpagar=" + vidnfconfpagar + "&_pr_u_nfconfpagar_proporcao=" + $(vthis).val() + "&valor_total="+vtotal,
                parcial: true
            })
        }
    }

    //O daterangepicker não dispara o "change" do elemento. Portanto deve ser feita verificação do evento do plugin
    $(":input[name*=nfconfpagar_datareceb]").on('change', function() {
        CB.post({
            objetos: "_pr_u_nfconfpagar_idnfconfpagar=" + $(this).attr('idnfconfpagar') + "&_pr_u_nfconfpagar_datareceb=" + $(this).val(),
            parcial: true
        })
    });
    $(":input[name*=data_formalizacao]").on('change', function(ev, picker) {
        CB.post({
            objetos: "_f1_u_formalizacao_idformalizacao=" + $(ev.target).attr('idform') + "&_f1_u_formalizacao_envio=" + $(ev.target).val(),
            parcial: true
        });
    }).on('apply.daterangepicker', function(ev, picker) {
        CB.post({
            objetos: "_f1_u_formalizacao_idformalizacao=" + $(ev.target).attr('idform') + "&_f1_u_formalizacao_envio=" + picker.startDate.format('DD/MM/YYYY'),
            parcial: true
        });
    });

    function alterainput(vthis, botaomostrar, campomostrar, campoocultar) {

        $(vthis).addClass("hidden");
        $("[name=" + botaomostrar + "]").removeClass("hidden");
        $("[name=" + campomostrar + "]").removeClass("hidden");
        $("[name=" + campoocultar + "]").addClass("hidden");

    }


    function nfconfpagar(inidnfconfpagar, li) {
        var strCabecalho =
            $("#cbModalTitulo").html((strCabecalho));

        var htmloriginal = $("#" + li + "_editarnfconfpagar").html();
        var objfrm = $(htmloriginal);

        objfrm.find("#" + li + "_nfconfpagar_idnfconfpagar").attr("name", "_999_u_nfconfpagar_idnfconfpagar");
        objfrm.find("#" + li + "_nfconfpagar_obs").attr("name", "_999_u_nfconfpagar_obs");



        CB.modal({
            titulo: "</strong>Observações para pagamento <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='salvanfconfpagar();'><i class='fa fa-circle'></i>Salvar</button></strong>",
            corpo: objfrm.html(),
            classe: 'cinquenta'
        });

    }

    function salvanfconfpagar() {

        var strcbpost = "_999_u_nfconfpagar_idnfconfpagar=" + $("[name=_999_u_nfconfpagar_idnfconfpagar]").val() + "&_999_u_nfconfpagar_obs=" + $("[name=_999_u_nfconfpagar_obs]").val();

        console.log(strcbpost);
        CB.post({
            objetos: strcbpost,
            parcial: true,
            msgSalvo: "Salvo",
            posPost: function(resp, status, ajax) {
                if (status = "success") {
                    $("#cbModalCorpo").html("");
                    $('#cbModal').modal('hide');
                } else {
                    alert(resp);
                }
            }
        });

    }

    function atualizaintervalo(vthis) {
        CB.post({
            objetos: "_ati_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_ati_u_nf_intervalo=" + $(vthis).val(),
            parcial: true
        })
    }

    function atualizadiasentrada(vthis) {
        CB.post({
            objetos: "_ati_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_ati_u_nf_diasentrada=" + $(vthis).val(),
            parcial: true
        })
    }

    function abrecomissao(inidnfitem) {
        var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';

        CB.modal({
            url: "?_modulo=nfitemcomissao&_acao=u&idnfitem=" + inidnfitem + idempresa,
            header: "Comissão do Item"
        });
    }

    function abrecomissaonf(inidnf) {
        var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';

        CB.modal({
            url: "?_modulo=nfitemcomissao&_acao=u&idnf=" + inidnf + idempresa,
            header: "Comissão do Item"
        });
    }


    function atualizaf(vthis, idnfitem) {
        CB.post({
            objetos: "_x_u_nfitem_idnfitem=" + idnfitem + "&_x_u_nfitem_idprodservformula=" + $(vthis).val(),
            parcial: true
        })
    }


    arrstatus = ["FATURAR", "ENVIAR", "ENVIADO", "CONCLUIDO"];

    if(arrstatus!=undefined && Array.isArray(arrstatus)){
        instatus =  arrstatus.includes(_1_u_nf_status);
    }else{
        instatus = false;
    }

    if (_1_u_nf_idnf !='' && instatus) {
        if (arrayaux='') {
            arrayaux = 0;
        }
    
        var jCert = arrayaux;

        if (jCert != 0) {
            var $oCert = $(`<button class="btn btn-primary btn-xs" style="display:inline-flex" title="Gerar Certificados de Análise">Gerar Certificados</button>`).on('click', async function() {
                $(this).prop("disabled", true);
                alertAzul("Em instantes os certificados serão gerados. Não atualize a tela.", "Gerando Certificados");

                let nOks = Object.keys(jCert).length;
                let contador = 0;
                let state = false;
                let response;
                let obj = Object();
                CB.ajaxPostAtivo = true;
                while (!state && (contador < nOks)) { // Condições para impedir uma nova requisição sem a primeira requisição finalizar
                    state = true;

                    $(this).text(contador + " / " + nOks + " Completos");
                    $(this).append(`<div class="loadercert"></div>`);

                    let nfitem = Object.keys(jCert)[contador];

                    response = await fetch("form/certanalise.php?_acao=u&idlote=" + jCert[nfitem].idlote + "&geraarquivo=Y&gravaarquivo=Y&gerarautomatico=Y", {
                        method: "GET"
                    });

                    response = await response.text();

                    if (response == "OK") {
                        obj["_x" + nfitem + "_u_nfitem_idnfitem"] = nfitem;
                        obj["_x" + nfitem + "_u_nfitem_cert"] = "Y";
                    } else {
                        alert("Ocorreu um problema ao gerar o certificado para:\n" + jCert[nfitem].descr + "\nConsulte o log de erros.");
                        console.warn(response);
                    }

                    contador++;
                    state = false;
                }
                CB.ajaxPostAtivo = false;
                $(this).text(contador + "/" + nOks + " Completos");
                if (Object.keys(obj).length > 0 && (nOks * 2) == Object.keys(obj).length) {
                    alertAzul("Certificados Gerados. Aguarde o salvamento automático.");
                    CB.post({
                        objetos: obj,
                        parcial: true
                    });
                } else {
                    alertErro('Um ou mais certificados não foram gerados corretamente.<br>Entre em contato com a equipe de TI.', 'Erro ao Gerar Certificado(s)');
                    $(this).remove();
                }

            });
            $("#_certificadodeanalise_").append($oCert);
        }
    }

    function validavalor(vthis) {
        var valor = $(vthis).val();
        var valormin = $(vthis).attr('min');
        if (valor < valormin) {
            alert('Valor mínimo de ' + valormin + ' conforme cadastro.');
            $(vthis).val(valormin);
        }
    }

    $(document).ready(function() {
        $(window).ready(function() {
            if ($("[name='_1_u_nf_idnf']").val()) {
                let totalwarn = $("input[name='namecert']:checked").siblings("i.fa-warning").length;
                if (totalwarn > 0) {
                    $("#warningcert").append(`<i class="fa fa-warning vermelho" title="Existem certificados pendentes a serem gerados"></i>`)
                }
            }
        })
    })

    function alterarTagName(vth) {
        let vthis = $(vth);
        let $oInput = $(`<input data-qtddisponivel="${vthis.data('qtddisponivel')}" class="${vthis.attr('class')}" identificador="${vthis.attr('identificador')}" item="${vthis.attr('item')}" onkeyup="alterarTagNameFilhos(this)" onblur="alterarTagNameFilhos(this)" onchange="somarItensSelecionados(this)" name="${vthis.attr('name')}" style="${vthis.attr('style')}" title="${vthis.attr('title')}">`)

        $oInput.insertAfter(vthis);
        $oInput.focus();

        vthis.remove();
    }

    function alterarTagNameFilhos(vth) {
        let id = $(vth).attr('identificador');
        if (vth.value.length > 0) {
            $(".changetag_" + id + " button").each((i, o) => {
                let parent = $(o).parent()
                parent.append($(`<input name="${$(o).attr('name')}" value="${$(o).val()}">`));
                $(o).remove();
            })
        } else {
            let vthis = $(vth);
            if ($(`button[identificador='${vthis.attr('identificador')}']`).length == 0) {
                $(".changetag_" + id + " input").each((i, o) => {
                    let parent = $(o).parent()
                    parent.append($(`<button name="${$(o).attr('name')}" value="${$(o).val()}"></button>`));
                    $(o).remove();
                });

                $(`<button identificador="${vthis.attr('identificador')}" class="${vthis.attr('class')}" item="${vthis.attr('item')}" name="${vthis.attr('name')}" onclick="alterarTagName(this)" style="${vthis.attr('style')}" title="${vthis.attr('title')}"></button>`)
                    .insertAfter(vthis);
                vthis.remove();
            }

        }
    }

    function setdtemissao(indate,envionfe) {

        if(envionfe!='CONCLUIDA'){
            $("[name='_1_u_nf_dtemissao']").val(indate + ' 00:00:00');
            str = "&_parc_u_nf_dtemissao=" + indate + ' 00:00:00';
        }else{
            str = "";
        }        

        CB.post({
            objetos: "_parc_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_parc_u_nf_parcelas=" + $('[name=_1_u_nf_parcelas]').val() + "&_parc_u_nf_envio=" + indate+str ,
            parcial: true,
            posPost: function(data, textStatus, jqXHR) {
                CB.post();
            }
        })
    }

    function atualizaconfpagar() {
        var strcbpost = '';
        var i = 0;
        $(".confcontapagar").each(function() {
            i++;
            console.log($(this).attr('datagerada'));
            $(this).val($(this).attr('datagerada'));
            strcbpost = strcbpost.concat("&_" + i + "_u_nfconfpagar_datareceb=" + $(this).attr('datagerada') + "&_" + i + "_u_nfconfpagar_idnfconfpagar=" + $(this).attr('idnfconfpagar'));

        });

        console.log(strcbpost);

        CB.post({
            objetos: strcbpost,
            parcial: true,
            refresh: false,
            posPost: function(resp, status, ajax) {
                CB.post();
            }
        });

    }

    function novoCte() {

        let idtransp = $("[name='_1_u_nf_idtransportadora']").val();
        let nnfe = $("[name='_1_u_nf_nnfe']").val();
        var idempresafat = $("[name='_1_u_nf_idempresafat']").val();

        if(idempresafat != undefined){
            var idempfat ='&_x_i_nf_idempresafat='+idempresafat;
        }else{
            var idempfat='';
        }

        if (confirm("Gerar CTE para este pedido?")) {
            if (idtransp == '') {
                alert("Favor selecionar o transportador antes de gerar o CTe.");
            } else if (nnfe == '') {
                alert("É necessário emitir a nota para gerar o CTe.");
            } else {
                CB.oModal.off('click.removerCte');
                CB.oModal.off('click.removerCteNf');

                CB.post({
                    objetos: "_x_i_nf_tiponf=T&_x_i_nf_idpessoa=" + idtransp + "&_x_i_nf_idobjetosolipor=" + $("[name='_1_u_nf_idnf']").val() + "&_x_i_nf_tipoobjetosolipor=nf"+idempfat,
                    parcial: true
                })
            }
        }
    }

    function editarFormula(idprodservformula) {
        if ($(".selectFormula" + idprodservformula).is(":hidden")) {
            $('.labelForumla' + idprodservformula).css("display", "none");
            $('.selectFormula' + idprodservformula).css("display", "block");
        } else {
            $('.labelForumla' + idprodservformula).css("display", "block");
            $('.selectFormula' + idprodservformula).css("display", "none");
        }
    }

    function novopedido() {

        $('#Copiaritens').removeAttr('onclick').removeClass('pointer').removeClass('fa-clone').addClass('loading');

        $.ajax({
            type: "get",
            url: "ajax/htmlnovopedido.php",
            data: {
                idnf: $("[name=_1_u_nf_idnf]").val(),
                idempresafat: $("[name=_1_u_nf_idempresafat]").val()
            },

            success: function(data) {
                CB.modal({
                    titulo: "</strong>Gerar Pedido <button type='button' class='btn btn-danger btn-xs' onclick='SalvarNovoPedido();'><i class='fa fa-circle'></i>Gerar</button></strong>",
                    corpo: data,
                    classe: 'sessenta',
                    aoFechar: function() {
                        $('#Copiaritens').attr('onclick', 'novopedido()').removeClass('loading').addClass('fa-clone').addClass('pointer');
                    }
                });
            },

            error: function(objxmlreq) {
                alert('Erro:<br>' + objxmlreq.status);
            }
        }) //$.ajax
    }

    function SalvarNovoPedido() {
        if (confirm("Gerar pedido de transferência?")) {

            var vstr = '';
            var virg = '';
            $("#itensdev").find('input:checked').each(function(i, o) {
                vstr += virg;
                vstr += $(o).attr("idnfitem");
                virg = ',';
            })


            console.log(vstr);
            if ($("[name=_dev_i_nf_idnatop]").val() == '' || vstr == '') {
                alert("É necessário marcar um dos itens e selecionar a natureza da operação.");
            } else {

                var strcbpost = "pedido_idpessoa=" + $("[name=pedido_idpessoa]").attr('cbvalue') + "&pedido_idnatop=" + $("[name=_dev_i_nf_idnatop]").val() + "&pedido_idnfitem=" + vstr + "&_1_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val();

                console.log(strcbpost);

                CB.post({
                    objetos: strcbpost,
                    parcial: true,
                    posPost: function(data, textStatus, jqXHR) {
                        if (jqXHR.getResponseHeader("X-CB-PKID") &&
                            jqXHR.getResponseHeader("X-CB-PKFLD") == "idnf") {
                            janelamodal("?_modulo=pedido&_acao=u&idnf=" + jqXHR.getResponseHeader("X-CB-PKID"));
                        } else {
                            alert("js: gerarDevolução: A resposta de inserção não retornou a coluna `idnf` ou Autoincremento.");
                        }
                    }
                });

            }
        }
    }

    function novacompra() {
        if (confirm("Gerar Nota de Entrada?")) {

           
            if ($("[name=_1_u_nf_idempresafat]").val()== '') {
                alert("É necessário selecionar a filial.");
            } else {

                var strcbpost = "pedido_idempresafat=" + $("[name=_1_u_nf_idempresafat]").val() + "&_1_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val();

                console.log(strcbpost);

                CB.post({
                    objetos: strcbpost,
                    parcial: true,
                    posPost: function(data, textStatus, jqXHR) {
                        if (jqXHR.getResponseHeader("X-CB-PKID") &&
                            jqXHR.getResponseHeader("X-CB-PKFLD") == "idnf") {
                            janelamodal("?_modulo=nfentrada&_acao=u&idnf=" + jqXHR.getResponseHeader("X-CB-PKID"));
                        } else {
                            alert("js: gerarDevolução: A resposta de inserção não retornou a coluna `idnf` ou Autoincremento.");
                        }
                    }
                });

            }
        }
    }



    function popLink(url, title, color, urlmodal, urltipo) {
        $("#_dashresultscontent").remove();
        if (urltipo == 'JS') {
           
            eval(url);
        } else {
           

            var strCabecalho = "</strong><label class='fonte08'><span class='titulo-" + color + "'>" + title + "</span></label></strong>";

            //Altera o cabeçalho da janela modal
            $("#cbModalTitulo")
                .html(strCabecalho)
                .append("&nbsp;&nbsp;<label id='resultadosEncontrados' class='fonte08'></label>")
                .append(`<i class='fa fa-file-excel-o floatright' id='btPrintNucleo' title='Impressão' onclick="gerarCsv('${title}')"></i>`)
                .append(`<i class='fa fa-print floatright' id='btPrintNucleo' title='Impressão' onclick="printNucleo('${title}')"></i>`)            
            ;

            if (url != '' && url != 'null') {
               
                if (url.search("php") >= 0 || url.search("novajanela") >= 0) {
                    link = './' + url;
                    janelamodal(url);
                } else {
                    link = 'form/_modulofiltrospesquisa.php?_pagina=0'

                    //Realiza a chamada da pagina de pesquisa manualmente
                    $.ajax({
                        context: this,
                        type: 'get',
                        cache: false,
                        url: link,
                        data: url,
                        dataType: "json",
                        beforeSend: function() {
                            alertAguarde();

                        },
                        error: function(data) {

                            var str = JSON.stringify(data);
                            var part = str.substring(str.lastIndexOf("[") + 1, str.lastIndexOf("]"));
                            if (part) {
                                alertAtencao("Sem permissão ao módulo: " + part + "<br>Favor entrar em contato com Departamento de Processos - Ramal: 110");
                            }

                        },
                        success: function(data, status, jqXHR) {

                            //Json contem resultados encontrados?
                            if (!$.isEmptyObject(data)) {
                                //Nos casos onde existia um número muito grande linhas, o browser estava apresentando lentidão. Caso o número de linhas seja > configuracao do Mà³dulo, direcionar para tela de search
                                if (parseInt(data.numrows) > parseInt(CB.jsonModulo.limite) || data.numrows > 2000) {
                                    alertAtencao("Mais de " + CB.jsonModulo.limite + " resultados foram encontrados!\n<a href='?" + vGetAutofiltro + "' target='_blank' style='color:#00009a;'><i class='fa fa-filter'></i> Clique aqui para filtrar os resultados encontrados.</a>");
                                    janelamodal("?" + vGetAutofiltro);
                                } else {

                                    $("#cbModal").addClass("noventa").modal();
                                    var tblRes = CB.montaTableResultados(data, function(obj, event) {

                                        oTr = $(obj);
                                        oTr.css("backgroundColor", "transparent");
                                      

                                        janelamodal('?' + urlmodal + '&' + oTr.attr("goParam"));

                                    });
                                    $("#cbModal #cbModalCorpo").html(tblRes);

                                    $("body").append(`<div id="_dashresultscontent" class="hideshowtable">
													<table>${tblRes.html()}</table>
												</div>`);

                                    if (data.numrows) {
                                        $("#resultadosEncontrados").html("(" + data.numrows + " resultados encontrados)").attr("cbnumrows", data.numrows);
                                    }
                                }
                            } else {

                                alert("Nenhum resultado encontrado.");

                            }
                        },
                        complete: function() {
                            CB.aguarde(false);
                            if (CB.limparResultados == true) {
                                CB.resetDadosPesquisa();
                            }
                        }
                    });
                }
            }
        }
    }



	//------- Funções JS -------
    $(".historicoEnvio").webuiPopover({
        trigger: "click",
        placement: "left",
        width: 500,
        delay: {
            show: 300,
            hide: 0
        }
    });

    $(".historicoObs").webuiPopover({
        trigger: "click",
        placement: "right",
        width: 500,
        delay: {
            show: 300,
            hide: 0
        }
    });

    CB.on('prePost', function(){
        let alerta = false;
        let parcela, parcelaArray = '';
        hoje = new Date();
        virgula = '';
        const status = ['ABERTO', 'FECHADO', 'PENDENTE'];
        $(".datarecebparc").each((i, event)=> {
            dataCadastro = $(event).attr('value').split('/');
			parcela = $(event).attr('parcela');
            statusCI = $(event).attr('statusCI');
            datareceb = new Date(dataCadastro[2], dataCadastro[1] - 1, dataCadastro[0], 23,59,59);
            if((datareceb < hoje ? true : false) && status.indexOf(statusCI) >= 0){
                alerta = true;
				parcelaArray += virgula + parcela;
                virgula = ', ';
            }
        });
               
        if(alerta){
            if(!confirm(`A data de Recebimento da(s) parcela(s) ${parcelaArray} está(ão) \nanterior(es) a data atual.\n\n Deseja continuar?`)){
                return {objetos:{},msgSalvo:false,refresh:false}
            }
        }
    });

	//------- Funções Módulo -------
	function alteravalor(campo, valor, tabela, inid, texto) 
    {
        htmlTrModelo = "";
        htmlTrModelo = `<div id="alt${campo}${inid}">
            <table class="table table-hover">
                <tr>
                    <td>${texto}</td>
                    <td>
                        <input name="_h1_i_${tabela}_idobjeto" value="${inid}" type="hidden">
                        <input name="_h1_i_${tabela}_campo" value="${campo}" type="hidden">
                        <input name="_h1_i_${tabela}_tipoobjeto" value="nf" type="hidden">
                        <input name="_h1_i_${tabela}_valor_old" value="${valor}" type="hidden">
                        <input name="_h1_i_${tabela}_valor" value="${valor}" class="size10 calendario" type="text">
                    </td>
                </tr>
                <tr>
                    <td>Justificativa:</td>
                    <td>
                        <select id="justificativa" name="_h1_i_${tabela}_justificativa" onchange="alteraoutros(this,'${tabela}')" vnulo class="size50">
                            <?=fillselect(PedidoController::$_justificativa)?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>`;

        if (campo == 'previsaoentrega') 
        {
            var objfrm = $(htmlTrModelo);
            objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");
        } else {
            var objfrm = $(htmlTrModelo);
            objfrm.find("#ndroptipo option[value='" + valor + "']").attr("selected", "selected");
            objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");
        }

        strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='CB.post();' style='float: right; margin-top: 14px;'><i class='fa fa-circle'></i>Salvar</button></strong>";
    
        CB.modal({
            titulo: strCabecalho,
            corpo: "<table>" + objfrm.html() + "</table>",
            classe: 'sessenta',
            aoAbrir: function(vthis) {
                $("[name='_h1_i_modulohistorico_valor']").daterangepicker({
                    "singleDatePicker": true,
                    "locale": CB.jDateRangeLocale
                }).on('apply.daterangepicker', function(ev, picker) {
                    console.log(picker.startDate.format('YYYY-MM-DD'));
                    $(this).html(picker.startDate.format("DD/MM/YYYY") || "");
                });

                 $(`[name="_h1_i_${tabela}_valor"]`).val(valor);
            }
        });
    }

    function alteraoutros(vthis, tabela) 
	{
		valor = $(vthis).val();
		if (valor == 'OUTROS') {
           
			$(vthis).parent().append('<input style="margin-top:4px;" id="justificaticaText" name="_h1_i_' + tabela + '_justificativa" value="" class="size50" type="text" placeholder="Digite aqui a sua justificativa" />');
            $('#justificativa').remove();
        } else {
			$('#justificaticaText').remove();
		}
	}


    function alteraValorObs(campo, valor, tabela, inid, texto) 
    {
        htmlTrModelo = "";
        htmlTrModelo = `<div id="alt${campo}${inid}">
            <table class="table table-hover">
                <tr>                   
                    <td>
                        <input name="_h1_i_${tabela}_idobjeto" value="${inid}" type="hidden">
                        <input name="_h1_i_${tabela}_campo" value="${campo}" type="hidden">
                        <input name="_h1_i_${tabela}_tipoobjeto" value="nf" type="hidden">
                        <input name="_h1_i_${tabela}_valor_old" value="${valor}" type="hidden">                   
                        <textarea class="caixa" style="height: 85px; overflow: hidden; overflow-wrap: break-word; resize: horizontal;" name="_h1_i_${tabela}_valor">${valor}</textarea>
                    </td>
                </tr>
                      </table>
        </div>`;

        var objfrm = $(htmlTrModelo);

        strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='CB.post();' style='float: right; margin-top: 14px;'><i class='fa fa-circle'></i>Salvar</button></strong>";
    
        CB.modal({
            titulo: strCabecalho,
            corpo: "<table>" + objfrm.html() + "</table>",
            classe: 'sessenta',
            aoAbrir: function(vthis) {
           

                 $(`[name="_h1_i_${tabela}_valor"]`).val(valor);
            }
        });
    }


    function setafilial(vthis){
        if (confirm("Alterar faturamento para Filial?")) {          
          
            CB.post({
                objetos: "_filial_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_filial_u_nf_idempresafat=" + $(vthis).val() ,
                parcial: true
            })

        }

    }

    function limparFilial(){
        $('#limparfilial').removeAttr('onclick').removeClass('pointer').removeClass('fa-eraser').addClass('loading');

        if (confirm("Retirar Filial?")) {  
            CB.post({
                objetos: "_limpafilial_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_limpafilial_u_nf_idempresafat=0",
                parcial: true,
                posPost: function(resp, status, ajax) {
                    if (status = "success") {
                       CB.post();
                    } else {
                        alert(resp);
                    }
                }
            });
        }

        $('#limparfilial').attr('onclick', 'limparFilial()').removeClass('loading').addClass('fa-eraser').addClass('pointer');
    }

    
    function setarAlterarValor(val)
    {
        $(".alterar_valor").val(val);
    }

    function showModalComissao() {
        var strCabecalho = "<strong>Nova Comissão <button id='cbSalvar' type='button' style='margin-left:370px' class='btn btn-danger btn-xs' onclick='gerarNovaComissao();'><i class='fa fa-circle'></i>Salvar</button></strong> ";
        $("#cbModalTitulo").html((strCabecalho));

        var htmloriginal = $("#novacomissao").html();
        var objfrm = $(htmloriginal);

        objfrm.find("#vencnovacomissao").attr("name", "_modal_vencnovacomissao_");
        objfrm.find("#formapagnovaparc").attr("name", "_modal_formapagnovaparc_");
        objfrm.find(".gerarNovaComissao").attr("class", "_modal_valornovacomissao_");
        
        CB.modal({
            corpo: objfrm.html(),
            titulo: strCabecalho
        })
    }

    function gerarNovaComissao() {
        i = 0;
        var idfluxostatus = getIdFluxoStatus('contapagar', 'ABERTO');
        let str1 = '';

        $('._modal_valornovacomissao_').each(function(key, elem){
            if($(elem).val() > 0)
            {
                comercial = (key == 0) ? '' : '&';
                data = new Date($("[name=_modal_vencnovacomissao_]").val());
                dataFormatada = data.toLocaleDateString('pt-BR', {timeZone: 'UTC'});

                str1 += `${comercial}_xc${key}_i_contapagaritem_idempresa=${_1_u_nf_idempresa}&_xc${key}_i_contapagaritem_idunidade=${idunidadelote}`;
                str1 += `&_xc${key}_i_contapagaritem_idobjetoorigem=${$('.idcontaPagarComissao').val()}&_xc${key}_i_contapagaritem_tipoobjetoorigem=contapagar`;
                str1 += `&_xc${key}_i_contapagaritem_idpessoa=${$(elem).attr('idpessoa')}&_xc${key}_i_contapagaritem_idformapagamento=${$('.idformapagamentoComissao').val()}`;
                str1 += `&_xc${key}_i_contapagaritem_parcela=${$('#parcela_parcelas').val()-1}&_xc${key}_i_contapagaritem_parcelas=${$('#parcela_parcelas').val()-1}`;
                str1 += `&_xc${key}_i_contapagaritem_datapagto=${dataFormatada}&_xc${key}_i_contapagaritem_valor=${$(elem).val()}`;
                str1 += `&_xc${key}_i_contapagaritem_status=ABERTO&_xc${key}_i_contapagaritem_tipo=D&_xc${key}_i_contapagaritem_visivel=S&atualizaComissao=Y`;
                i++;
            }  
        });

        CB.post({
            objetos: str1,
            parcial: true,
            posPost: function(resp, status, ajax) {
                if (status = "success") {
                    $("#cbModalCorpo").html("");
                    $('#cbModal').modal('hide');
                } else {
                    alert(resp);
                }
            }
        });
    }

    $(".dtemissaonf").on("apply.daterangepicker", function(ev, picker) {
        let idnfconfpagar = $(ev.currentTarget).attr('idnfconfpagar');
        let name = $(ev.currentTarget).attr('name');
        idpessoafat = $("[name=_1_u_nf_idpessoafat]").attr('cbvalue');
        CB.post({
            objetos: `_uc_u_nfconfpagar_idnfconfpagar=${idnfconfpagar}&_uc_u_nfconfpagar_datareceb=${picker.startDate.format("DD/MM/YYYY")}&_1_u_nf_idnf=${$("[name=_1_u_nf_idnf]").val()}&_1_u_nf_parcelas=${$("[name=_1_u_nf_parcelas]").val()}&_1_u_nf_dtemissao=${$("[name=_1_u_nf_dtemissao]").val()}&_1_u_nf_total=${$("[name=_1_u_nf_total]").val()}&_1_u_nf_intervalo=${$("[name=_1_u_nf_intervalo]").val()}&_1_u_nf_diasentrada=${$("[name=_1_u_nf_diasentrada]").val()}&_1_u_nf_status=${$("[name=_1_u_nf_status]").val()}&_1_u_nf_idformapagamento=${$("[name=_1_u_nf_idformapagamento]").val()}&_1_u_nf_tiponf=${$("[name=_1_u_nf_tiponf]").val()}&_1_u_nf_geracontapagar=${$("[name=_1_u_nf_geracontapagar]").val()}&_1_u_nf_comissao=${$("[name=_1_u_nf_comissao]").val()}&_1_u_nf_idpessoafat=${idpessoafat}`,
            parcial: true,
        }); 
    });

    function calculaComissao(vthis)
    {
        idcontapagar = vthis.value;
        valorParcela = $(`.contaPagar${idcontapagar}`).attr('valorparcelacomissao');
        formapagamento = $(`.contaPagar${idcontapagar}`).attr('formapagamento');
        datapagtoComissao = $(`.contaPagar${idcontapagar}`).attr('contadatapagto');

        $("[name=_modal_vencnovacomissao_]").val(datapagtoComissao);
        $(".idcontaPagarComissao").val(idcontapagar);
        $("[name=_modal_vencnovacomissao_]").removeAttr('disabled');
        $("._modal_valornovacomissao_").removeAttr('disabled')

        /*$('._modal_valornovacomissao_').each(function(key, elem){
            comissao = $(elem).attr('comissao');
            valorComissao = (valorParcela * comissao) / 100;
            $(`._modal_valornovacomissao_#valornovacomissao${$(elem).attr('idpessoa')}`).val(valorComissao.toFixed(2));
        });*/
    }

    $('.liitens').on('input', 'input.changetag', function(e) {
        const elementJQ = $(e.currentTarget);
        const quantidadeDebitada = parseFloat(elementJQ.data('qtdd'));
        const id = elementJQ.attr('item');
        const linhaItemJQ = elementJQ.parent().parent(),
            linhaItemNfJQ = $(`.nfitem[idnfitem="${id}"]`);

        let totalItens = 0;
        let total = parseFloat($(`#vlr_qtd_${id}`).val());
        $(`.somarQtd${id}`).each(function(key, elem){
            if($(elem).val() != '') {
                totalItens = totalItens + parseInt($(elem).val());    
            }                    
        });

        if(totalItens == 0) {
            linhaItemNfJQ.removeClass('excedendo-pedido');
            $(`.totalItens${id}`).html('');
            return;
        };
        const quantidadeRestanteOuFaltando = total - totalItens;
        
        if(parseFloat(elementJQ.val()) > parseFloat(elementJQ.data('qtddisponivel')) + quantidadeDebitada)  {
            linhaItemNfJQ.addClass('excedendo-estoque-lote');
            linhaItemJQ.addClass('excedendo-estoque-lote');
        } else {
            linhaItemJQ.removeClass('excedendo-estoque-lote');
            linhaItemNfJQ.removeClass('excedendo-estoque-lote');
        }

        let labelInfo = `Faltam: ${quantidadeRestanteOuFaltando} item(ns)`;
        if(quantidadeRestanteOuFaltando < 0) labelInfo = `Sobra: ${quantidadeRestanteOuFaltando * -1} item(ns)`;
        
        if(totalItens > parseFloat(total)) {
            linhaItemNfJQ.addClass('excedendo-pedido');
            $(`.totalItens${id}`).html(`<label class="alert-warning">Qtd Selecionada: ${totalItens} <br /> ${labelInfo} </label>`);
        } else {
            linhaItemNfJQ.removeClass('excedendo-pedido');
            linhaItemJQ.removeClass('excedendo-pedido');
        }

        if(totalItens < total) {
            linhaItemNfJQ.addClass('faltando-lote');
        } else linhaItemNfJQ.removeClass('faltando-lote');

        if(totalItens > 0 && total != totalItens)
            $(`.totalItens${id}`).html(`<label class="alert-warning">Qtd Selecionada: ${totalItens} <br /> ${labelInfo} </label>`);
        else {
            $(`.totalItens${id}`).html('');
        }
    });

    function somarItensSelecionados(vthis){
        let id = $(vthis).attr('item');

        const elementJQ = $(vthis),
            linhaItemJQ = elementJQ.parent().parent(),
            linhaItemNfJQ = $(`.nfitem[idnfitem="${id}"]`);

        let totalItens = 0;
        let total = $(`#vlr_qtd_${id}`).val();
        $(`.somarQtd${id}`).each(function(key, elem){
            if($(elem).val() != '') {
                totalItens = totalItens + parseInt($(elem).val());    
            }                    
        });

        if(totalItens == 0) return;
        const quantidadeRestanteOuFaltando = total - totalItens;
        let labelInfo = `Faltam: ${quantidadeRestanteOuFaltando} item(ns)`;

        if(quantidadeRestanteOuFaltando < 0) labelInfo = `Sobra: ${quantidadeRestanteOuFaltando * -1} item(ns)`;

        if(parseFloat(elementJQ.val()) > parseFloat(elementJQ.data('qtddisponivel')))  {
            linhaItemNfJQ.addClass('excedendo-estoque-lote');
            linhaItemJQ.addClass('excedendo-estoque-lote');
            
            return;
        }

        linhaItemJQ.removeClass('excedendo-estoque-lote');
        linhaItemNfJQ.removeClass('excedendo-estoque-lote');

        if(totalItens > (total + totalItens)) {
            linhaItemNfJQ.addClass('excedendo-pedido');
            $(`.totalItens${id}`).html(`<label class="alert-warning">Qtd Selecionada: ${totalItens} <br /> ${labelInfo} </label>`);
        } else {
            linhaItemNfJQ.removeClass('excedendo-pedido');
            linhaItemJQ.removeClass('excedendo-pedido');
        }

        if(totalItens < total) {
            linhaItemNfJQ.addClass('faltando-lote');
        } else linhaItemNfJQ.removeClass('faltando-lote');

        if(totalItens > 0 && total != totalItens)
            $(`.totalItens${id}`).html(`<label class="alert-warning">Qtd Selecionada: ${totalItens} <br /> ${labelInfo} </label>`);
        else 
            $(`.totalItens${id}`).html('');
    }
    
    $(`.qtditem`).each(function(key, elem){
        $(elem).each(function(key2, elem2){
            somarItensSelecionados(elem2);
        });
    });
    
    function nossonum(inidnf){
        if (confirm("Atualizar o Nosso Número de boleto?")) {     
            CB.post({
                objetos: "_nosso_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() ,
                parcial: true
            })
        }
    }

    function calculaTrib(vthis,idnfitem){

        const qtd = parseInt($(vthis).val());
        const total = parseInt($("#nfitem_total"+idnfitem).val());

        var vlritem=total/qtd;

        $("#vuntrib"+idnfitem).val(vlritem.toFixed(4));

    }

    removerVariasNfitem = () => {
    
        var nfitem = $('.checkbox-nfitem:checked');
        var ids = []; 

        const checkboxes = document.querySelectorAll('.checkbox-nfitem:checked');
        const idsSelecionados = Array.from(checkboxes).map(checkbox => checkbox.value);
        const idsComuns = idsArrayNfitem.filter(id => idsSelecionados.includes(id));

        if(idsComuns.length  > 0){
            alert("Favor retirar o(s) consumo(s) do(s) item(ns) selecionado(s) antes de excluir.")
            return;
        }

        if (!confirm("Tem certeza que deseja remover os itens selecionados?")) {
            return;
        }

        nfitem.each((i, el) => {
            var idnfitem = $(el).data('idnfitem');
            ids.push('_del' + idnfitem + '_d_nfitem_idnfitem=' + idnfitem);
        });

        console.log(ids.join('&'));

        CB.post({
            objetos: ids.join('&')
        });
    }
    
    function gerarCertificadoTodosLotes(){debugger;
        $(".checked_item:checked").each(function(key, elem){
            idnfitem = $(elem).attr('idnfitem');
            lote = ($(`.lote_${idnfitem}`));
            idlote = lote.attr('idlote');

            $.ajax({
                type: "GET",
                url: "form/certanalise.php",
                data: {
                    _acao: 'u',
                    idlote: idlote,
                    geraarquivo: 'Y',
                    gravaarquivo: 'Y',
                    gerarautomatico: 'Y'
                },
                success: function(data) {
                    console.log(`Certificado do lote ${idlote} gerado com sucesso.`);
                },
                error: function(objxmlreq) {
                    alert('Erro:<br>' + objxmlreq.status);
                }
            });
        });
    }

    function gerarCertificadoTodosLotes(){debugger;
        $(".checked_item:checked").each(function(key, elem){
            idnfitem = $(elem).attr('idnfitem');
            lote = ($(`.lote_${idnfitem}`));
            idlote = lote.attr('idlote');

            $.ajax({
                type: "GET",
                url: "form/certanalise.php",
                data: {
                    _acao: 'u',
                    idlote: idlote,
                    geraarquivo: 'Y',
                    gravaarquivo: 'Y',
                    gerarautomatico: 'Y'
                },
                success: function(data) {
                    console.log(`Certificado do lote ${idlote} gerado com sucesso.`);
                },
                error: function(objxmlreq) {
                    alert('Erro:<br>' + objxmlreq.status);
                }
            });
        });
    }

    function marcarTodosCertificados(vcheck){
        var strObjetos = "";
        $(".checked_item").each(function(key, elem){
            idnfitem = $(elem).attr('idnfitem');
            (strObjetos.length != 0) ? strObjetos += "&" : '';
            strObjetos += `_xni_u_nfitem_idnfitem=${idnfitem}&_xni_u_nfitem_cert=${vcheck}`;
        });

        CB.post({
            objetos: strObjetos,
            parcial: true
        });        
    }

    CB.on('posLoadUrl', function(){
        let todosChecados = true;
        let lotesemcertificado = 0;
        $(".checked_item:not(:checked)").each(function(key, elem){
            //valida se todos estão checados
            todosChecados = false;
        });        

        if(todosChecados && $(".checked_item").length > 0){
            $('.chekTodos').prop('checked', true).attr("onclick", "marcarTodosCertificados('N')");
        }

        $(".checked_item:checked").each(function(key, elem){
            //Verifica se existe algum lote sem certificado
            idnfitem = $(elem).attr('idnfitem');
            lotesemcertificado = (idnfitem.length > 0) ? 1 : 0;
        });

        if(lotesemcertificado == 1 && $(".checked_item").length > 0){
            $('.certificado_todos').show();
        }
    });

    function editarclienteEntrega(campo, valor, tabela, inid, texto, classe) {
		htmlTrModelo = "";
		htmlTrModelo = `<div id="alt${campo}${inid}">
                            <table class="table table-hover">
                                <tr>
                                    <td>Endereço Entrega</td>
                                    <td>
                                        <input name="_hent_i_${tabela}_idobjeto" value="${inid}" type="hidden">
                                        <input name="_hent_i_${tabela}_campo" value="${campo}" type="hidden">
                                        <input name="_hent_i_${tabela}_tipoobjeto" value="pedido" type="hidden">
                                        <input name="_hent_i_${tabela}_valor_old" value="${valor}" type="hidden">
                                        <select class="size40" name="_hent_i_${tabela}_valor" class="idendrotulo" vnulo>
                                            <option value=""></option>
                                            ${idendrotuloOption}
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Justificativa:</td>
                                    <td>
                                        <input id="justificativa" name="_hent_i_${tabela}_justificativa" vnulo class="size50">
                                    </td>
                                </tr>
                            </table>
                        </div>`;

		strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='CB.post();' style='float: right; margin-top: 14px;'><i class='fa fa-circle'></i>Salvar</button></strong>";

		CB.modal({
			titulo: strCabecalho,
			corpo: `<table>${htmlTrModelo}</table>`,
			classe: 'sessenta',
			aoAbrir: function(vthis) {
				$(`[name="_h1_i_${tabela}_valor"]`).val(valor);
			}
		});
	}

    function modalhist(div) {
		var htmloriginal = $(`#${div}`).html();

		CB.modal({
			titulo: "</strong>Histórico de Alteração:</strong>",
			corpo: htmloriginal,
			classe: 'sessenta'
		});
	}

    //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>
