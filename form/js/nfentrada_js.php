<script>
	//------- Injeção PHP no Jquery -------
	var status = '<?=$_1_u_nf_status?>';
	var arrayModulo = '<?=json_encode(addslashes(getModsUsr("MODULOS")))?>';
	var tiponf = '<?=$_1_u_nf_tiponf?>';
    var idpessoa = '<?=$_SESSION["SESSAO"]["IDPESSOA"] ?>';
    var idnf = '<?=$_1_u_nf_idnf ?>';
    var idfluxostatus = '<?=FluxoController::getIdFluxoStatus('lote', 'ABERTO', $idunidadelote); ?>';
    var idunidadelote = '<?=$idunidadelote?>';
    var pagvalmodulo = '<?=$pagvalmodulo?>'; 
    var readonly = "<?=$readonly?>";
    var listarObjetoOrigem = <?=json_encode(NfEntradaController::buscarNfitemPorIdobjetoTipoobjetoEIdNfOrigemNew($_1_u_nf_idnf, 'nfitem')) ?> || 0;  
    var dadosSolcom = <?=json_encode(NfEntradaController::buscarItensSolcomPorNf($_1_u_nf_idnf));?> || 0;
    var fillselectContaItemAtivo = <?=json_encode(NfEntradaController::buscarContaItemAtivoShare($_1_u_nf_tiponf)); ?> || 0;
    var qtdConversaoMoeda = '<?=$qtdConversaoMoeda?>';
    var internacional = '<?=$internacional?>';
    var moedainternacional = '<?=$_1_u_nf_moedainternacional?>';
    var totalcp = '<?=$totalcp?>';
    var listarUnidadeObjetoItem = <?=json_encode(NfEntradaController::buscarUnidadeObjetoLoteModuloPorIdnf($_1_u_nf_idnf, ''))?> || 0;
    var listarLote = <?=(empty($lote2)) ? 0 : json_encode(NfEntradaController::buscarUnidadeObjetoLoteModuloPorIdnf($_1_u_nf_idnf, $lote2))?>;
    var listaTagEmpresa = <?=json_encode(NfEntradaController::buscarTagEmpresaPorIdNf($_1_u_nf_idnf)); ?> || 0; 
    var tagNf = <?=json_encode(NfEntradaController::buscarItensTagPorIdNf($_1_u_nf_idnf))?> || 0;
    var qtdConversaoMoedaInternacional = '<?=json_encode(NfEntradaController::buscarSeExisteConversaoMoedaInternacional($_1_u_nf_idnf)) ?>' || 0;
    var $_formalizacao = '<? NfEntradaController::buscarFormalizacaoPorIdLote($nfItem["idlote"]) ?>';
    var unidadeVolume = <?=json_encode(NfEntradaController::listarUnidadeVolume()); ?> || 0;
    var siscomex = '<?=$_1_u_nf_siscomex?>';
    var icms = '<?=$_1_u_nf_valicms?>';
    var imp_serv = '<?=$imp_serv?>';
    var totalimpostoImportacao = 0;
    var totalvalipi = 0;
    var totalpis = 0;
    var totalcofins = 0;
    var separacaoProdutosNcm = '';
    var $nfOrigem = <?=json_encode(NfEntradaController::buscarNfPorIdObjetoItem($_1_u_nf_idnf)); ?> || 0;   
    var _modulo = '<?=$_GET['_modulo']?>';
    var contaItemProdserv = <?=json_encode(NfEntradaController::buscarContaItemProdservContaItemPorNf($_1_u_nf_idnf, $_1_u_nf_tiponf)) ?> || 0;
    var _idpk = $('[name="_1_u_nf_idnf"]').val() || getUrlParameter('idnf') || "";
    var calendario = '<?=$calendario?>';
	var modulo = '<?=$pagvalmodulo?>';
	var qtdProdutosCadastrados = '<?=$qtdProdutosCadastrados?>';
	var qtdProdutosSemCadastro = '<?=$qtdProdutosSemCadastro?>';
    var debounce = null;
    var debounceCadastrado = null;
    var debounceNaoCadastrado = null;
    var idcliente = '<?=$_1_u_nf_idpessoa?>';  
    var nfCte = <?= json_encode(NfEntradaController::buscarCTEDisponiveisParaVinculo($_1_u_nf_idnf, $_1_u_nf_idempresa)) ?>;
    const ctesVinculadas = <?= json_encode($ctesVinculadas)  ?>;
    const ctesVinculadasNf = <?= json_encode($ctesVinculadasNF)  ?>;
    const idEmpresa = '<?= $_1_u_nf_idempresa  ?>';
    var tipoconsumo = '<?=$tipoconsumo?>';

	jCli = <?=json_encode($arrayCliente)?> || 0;// autocomplete cliente
    jpag = <?=json_encode($arrayFormaPagamento)?>  || 0;
	jsonProd = <?=json_encode(NfEntradaController::buscarProdutoServicoComprado())?> || 0;//// autocomplete produto
	jprodservtemp = <?=json_encode(NfEntradaController::buscarProdutoServico($_1_u_nf_tiponf))?> || 0;
    itensCadastrados = <?=json_encode($listarProdutosCadastrados)?> || {};
    itensNaoCadastrados = <?=json_encode($listarProdutoSemCadastro)?> || {};
    tagClasses = <?=json_encode(NfEntradaController::buscarTagClass($_1_u_nf_status))?> || {};
    _manutencao = <?=json_encode(NfEntradaController::$_manutencao)?> || {};
    tagsDisponinveis = <?=json_encode(NfEntradaController::buscarTagsDisponiveisParaVinculo(cb::idempresa())) ?> || {};  
    buscarSubCategoriaPorNf = <?=json_encode(NfEntradaController::buscarSubCategoriaPorNf($_1_u_nf_idnf))?> || 0;
	
    idSpedC100 = '<?=$idSpedC100?>';
    <? if($_1_u_nf_idnf){ ?>
        var qtdArquivo = '<?=NfEntradaController::buscarArquivoPorTipoObjetoEIdObjeto('nf', $_1_u_nf_idnf)?>';
    <? } else { ?>
        var qtdArquivo = 0 ;
    <? } ?>
    var gIdEmpresa = "<?=cb::idempresa();?>";
	//------- Injeção PHP no Jquery -------

	//------- Funções JS -------
	$(".colorirVermelho").css("color", "red");

	//--------------------------------- mapear autocomplete de clientes -----------------------------------------------
	jCli = jQuery.map(jCli, function(o, id) {
		return {"label": o.nome, value: id+"", "tipo": o.tipo}
	});

	//autocomplete de clientes
	$("[name*=_nf_idpessoa]").autocomplete({
		source: jCli,
		delay: 0,
		create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				return $('<li>').append(`<a>${item.label}<span class='cinzaclaro'>${item.tipo}</span></a>`).appendTo(ul);
			};
		}, 
        select: function(event, ui){
            buscarDadoFornecedor(ui.item.value);
        }
	});
	// FIM autocomplete cliente  
    //--------------------------------- mapear autocomplete de clientes -----------------------------------------------

    //--------------------------------- autocomplete de Pagamentos --------------------------------------------------
	jpag = jQuery.map(jpag, function(o, id) {
		return {"label": o.descricao, value:id}
	});

    $("#forma_pag").autocomplete({
        source: jpag,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
            };
        }
    });
    //--------------------------------- autocomplete de Pagamentos --------------------------------------------------

    // Converter para array de pares [id, descrição]
    var fillselectContaItemAtivoArray = Object.entries(fillselectContaItemAtivo);
    fillselectContaItemAtivoArray.sort(function(a, b) {
        return a[1].localeCompare(b[1]);
    });

	//mapear autocomplete de jTipoProdServ
	//autocomplete de jTipoProdServ
    $(document).on('click', '.insidprodserv', function() {
        if (!$(this).data("ui-autocomplete")) {
            $(this).autocomplete({
                source: jprodservtemp,
                delay: 0,
                select: function(event, ui){
                    $(event).val(ui.item.value);
                    $(event).attr('cbvalue', ui.item.value);
                },
                create: function(){
                    $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                        return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
                    };
                }
            });
        }
    });

    $('#cbModal').on('click.removerCte', '.btn-remover-cte', function() {
        const elementJQ = $(this),
                idObjetoVinculo = elementJQ.data('idobjetovinculo');

        removerVinculoCompra(idObjetoVinculo);
    }); 

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

    $('#vincular-compra').on('click', function () {
        const idNfCompra = $('#input-vincular-compra').val();

        if(!idNfCompra) return alertAtencao('Informe o id da compra.');

        $.ajax({
            url: '../../ajax/nfentrada_ajax.php',
            method: 'GET',
            dataType: 'json',
            data: {
                comandoExecutar: 'buscarComprasDisponiveisParaVinculo',
                idNf: idnf,
                idEmpresa,
                idNfBusca: idNfCompra
            },
            success: res => {
                if(!res || !res.length) return alertAtencao('Nf não encontrada.');

                CB.post({
                    parcial: true,
                    objetos: {
                        '_1_i_objetovinculo_idobjeto': idNfCompra,
                        '_1_i_objetovinculo_tipoobjeto': 'nf',
                        '_1_i_objetovinculo_idobjetovinc': idnf,
                        '_1_i_objetovinculo_tipoobjetovinc': 'cte'
                    }
                });
            },
            error: err => {
                console.log(err);
                alertAtencao('Ocorreu um erro');
            }
        });
    });

	$(function () {
		$('[data-toggle="popover"]').popover({
			html: true,
			content: function(){
				let ModalPopoverId = $(this).attr("href").replaceAll("#","")
				return $("#modalpopover_"+ModalPopoverId).html();
			}
		});
	});
    
	//PHOL: @516100 - LIBERAR MODIFICAÇÃO Categoria E TIPO
    //@882441 - DESABILITAR CAMPOS NOS STATUS DO FLUXO FINAL
	if((status == "CONCLUIDO" || status == "REPROVADO" || status == "CANCELADO") /* && (arrayModulo.indexOf('comprasmaster') != -1)*/)
	{
		$("#cbModuloForm").find('input').not('[name*="_nf_idformapagamentoant"],[name*="_nfitem_idtipoprodserv"],[name*="_nfitem_idcontaitem"],[name*="nfitem_idnfitem"],[name*="nf_idpessoa"],[name*="nfconfpagar_idnfconfpagar"],[name*="namesped"],[name*="nf_idnf"],[name*="idcontapagarobs"],[name*="contapagaritem_idcontaitem"],[name*="statusant"],[id*="cbTextoPesquisa"],[name*="contapagaritem_valor"],[name*="nf_prazo"],[name*="_1_u_nf_dtemissao"],[name*="_nf_status"],[name*="_1_u_nf_comissao"],[name*="_1_u_nf_idpessoafat"],[name*="_1_u_nf_total"], [name*="vencnovapart"], [name*="valornovaparc"], [name*="_modalnovaparcelacontapagar_tipo_"], [name="_1_u_nf_tiponf"]').prop( "disabled", true );
		$("#cbModuloForm").find("select" ).not('[name*="nfitemxml_idprodserv"],[name*="nfitem_idtipoprodserv"],[name*="nfitem_idcontaitem"],[name*="formapagnovaparc"],[name*="contapagaritem_idcontaitem"],[name*="contapagaritem_status"],[name*="contapagaritem_idformapagamento"],[name*="_nf_idfinalidadeprodserv"],[name*="nfitemxml_idnfitem"]').prop( "disabled", true );
		$("#cbModuloForm").find("textarea").not('[name*="nfconfpagar_obs"],[name*="_nf_infcpl"],[name*="_nf_obsinterna"],[name*="nf_emaildadosnfe"],[name*="nf_emaildadosnfemat"],[name*="nf_infcorrecao"]').prop( "disabled", true );
	}

    $("#arqforndrop").dropzone({
        url: "form/_arquivo.php",
        idObjeto: $("[name=_1_u_nf_idnf]").val() || idnf,
        tipoObjeto: 'cotacaoforn',
        idPessoaLogada: idpessoa
    });

    $(document).ready(function() {
        //Verifica disponibilidade de numeronfe
        $("#nnfe").blur(function() {
            verifnnfe();
        });

        //Verifica disponibilidade de numeronfe
        $("#idpessoa").blur(function() {
            verifnnfe();
        });
    });

    $().ready(function() {
        $("#formapgto").change(function() {
            if ($("#formapgto").val() == "C.CREDITO") {
                $("#lbcartao").show();
                $("#cartao").show();
            } else {
                $("#idcartao option").prop("selected", false);
                $("#lbcartao").hide();
                $("#cartao").hide();
            }
        });

        $("#idcontaitem").change(function() {
            $("#idcontadesc").html("<option value='sda'>Procurando....</option>");

            $.post("ajax/dropdesc.php", {
                    idcontaitem: $("#idcontaitem").val()
                },
                function(resposta) {
                    $("#idcontadesc").html(resposta);
                }
            );
        });

        $("#cbModalCorpo").html("");
        if ($("#cbModal:visible").length > 0)
            $('#cbModal').modal('hide');
    });

    $("#parcelas").change(function() {
        if ($("#parcelas").val() > 1) {
            $("#divtab1").css("display", "block");
            $("#divtab2").css("display", "block");
        } else {
            $("#divtab1").css("display", "none");
            $("#divtab2").css("display", "none");
        }
    });

    if ($("[name=_1_u_nf_idnf]").val()) 
    {
        $(".cbupload").dropzone({
            idObjeto: $("[name=_1_u_nf_idnf]").val(),
            tipoObjeto: 'nf',
            idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"] ?>'
        });
    }
    
    /*
     * Duplicar Compras [ctrl]+[d]
     */
    $(document).keydown(function(event) {
        if (!((event.ctrlKey || event.altKey) && event.keyCode == 68)) return true;
        if (!teclaLiberada(event)) return; //Evitar repetição do comando abaixo
        janelamodal(`?_modulo=${CB.modulo}&_acao=i&idnfcp=${idnf}`);
        return false;
    });

    $(".prazo").on('apply.daterangepicker', function(ev, picker) {
        $(this).html("<i class='fa fa-refresh'></i> " + picker.startDate.format("DD/MM/YYYY") || "");
        CB.post({
            objetos: "_1_u_contapagar_idcontapagar=" + $(this).closest("tr").attr("value") + "&_1_u_contapagar_datareceb=" + picker.startDate.format("DD-MM-YYYY"),
            parcial: true
        });
    });

    $("#fdata1").on('apply.daterangepicker', function(ev, picker) {
        $(this).html("<i class='fa fa-refresh'></i> " + picker.startDate.format("DD/MM/YYYY") || "");
        CB.post({
            objetos: `_1_u_nf_idnf=${idnf}&_1_u_nf_prazo=${picker.startDate.format("DD-MM-YYYY")}`,
            parcial: true
        });
    });

    // $("#fdata2").on('apply.daterangepicker', function(ev, picker) {
    //     $(this).html("<i class='fa fa-refresh'></i> " + picker.startDate.format("DD/MM/YYYY") || "");
    //     CB.post({
    //         objetos: `_1_u_nf_idnf=${idnf}&_1_u_nf_prazo=${picker.startDate.format("DD-MM-YYYY")}`,
    //         parcial: true
    //     });
    // });

    $(function() {
        $("input[name='_1_u_nf_idnfe']").on('input', function(e) {
            $(this).val($(this).val().replace(/[^0-9]/g, ''));
        });
    });

    //O daterangepicker não dispara o "change" do elemento. Portanto deve ser feita verificação do evento do plugin
    $(":input[name*=nfconfpagar_datareceb]").on('change', function() {
        CB.post({
            objetos: "_pr_u_nfconfpagar_idnfconfpagar=" + $(this).attr('idnfconfpagar') + "&_pr_u_nfconfpagar_datareceb=" + $(this).val()
        })
    });

    $('#tdsgdepartamento').show();
    $('#tdfuncionario').hide();
    $('#tdempresa').hide();

     $('.dataconfdate').on('apply.daterangepicker', function(ev, picker) {
        let inI = this.getAttribute('idnfconfpagar')
        setdtdataconf(inI, picker.startDate.format('DD/MM/YYYY'));
    });

    $("#rateiomodal").click(function() {
        var idnf = $("[name=_1_u_nf_idnf]").val()
        var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';
        CB.modal({
            url: "?_modulo=rateioitemdest&_acao=u&tipo=nf&idnf=" + idnf + idempresa,
            header: "Editar Rateio",
            aoFechar: function() {
                verificarateio();
            }
        });
    });

    $(".modalControlesNfClique").on("click", function() {
        var idnf = $("[name=_1_u_nf_idnf]").val();
        CB.modal({
            url: `?_modulo=compracontrolenf&_acao=u&idnf=${idnf}`,
            header: "Controles NF",
            aoFechar:function(){
                vUrl = CB.urlDestino + window.location.search;
                CB.loadUrl({
                    urldestino: vUrl
                });
            }
        });
    });

    $(".modalPagamentoClique").on("click", function(){
        var idnf = $("[name=_1_u_nf_idnf]").val();
        var vlrtotal = $("[name=vlrtotal]").val();
        CB.modal({
            url: `?_modulo=comprapagamento&_acao=u&idnf=${idnf}&vlrtotal=${vlrtotal}`,
            header: "Pagamento",
            aoFechar:function(){
                vUrl = CB.urlDestino + window.location.search;
                CB.loadUrl({
                    urldestino: vUrl
                });
            }
        });
    });

    $(".modalConferenciaClique").on("click", function() {
        var idnf = $("[name=_1_u_nf_idnf]").val();
        CB.modal({
            url: `?_modulo=compraconferencia&_acao=u&idnf=${idnf}&_moduloPai=${pagvalmodulo}`,
            header: "Conferência",
            aoFechar:function(){
                vUrl = CB.urlDestino + window.location.search;
                CB.loadUrl({
                    urldestino: vUrl
                });
            }
        });
    });

    $(".modalLogisitcaClique").on("click", function() {
        var idnf = $("[name=_1_u_nf_idnf]").val();
        var frete = $(`[name=_1_u_nf_frete]`).val() ? 'R$ ' + $(`[name=_1_u_nf_frete]`).val() : "-"
        CB.modal({
            corpo: $(`<div>
                        <div class="col-md-12">
                            <table class="table table-striped planilha">
                                <tr>
                                    <td>
                                        Local de retirada:
                                    </td>
                                    <td>
                                        ${$(`#localretirada`).val() || "-" }
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Contato do responsavel pela venda:
                                    </td>
                                    <td>
                                    ${$(`#contatopessoa`).val() || "-" }
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Transportador:
                                    </td>
                                    <td>
                                        ${$(`#transportadora`).val() || "Não preenchido" }
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Valor do frete:
                                    </td>
                                    <td>
                                        ${ frete }
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>`).html(),
            titulo: "Informações Logística",
            classe: 'd-flex align-items-center',
        });
    });

    $(".modalRecebimentoClique").on("click", function() {
        var idnf = $("[name=_1_u_nf_idnf]").val();
        CB.modal({
            url: `?_modulo=comprarecebimento&_acao=u&idnf=${idnf}`,
            header: "Recebimento",
            aoFechar:function(){
                vUrl = CB.urlDestino + window.location.search;
                CB.loadUrl({
                    urldestino: vUrl
                });
            }
        });
    });

    function inserirImpostoImportacao(idnfitem, indice){
        htmlTrModelo = $(`#imposto_importacao_${idnfitem}`).html();   
        htmlTrModelo = htmlTrModelo.replace('id="#imp_idnfitem"', `name="_${indice}_u_nfitem_idnfitem"`);  
        htmlTrModelo = htmlTrModelo.replace('id="#imp_impostoimportacao"', `name="_${indice}_u_nfitem_impostoimportacao"`);  
        htmlTrModelo = htmlTrModelo.replace('id="#imp_valipi"', `name="_${indice}_u_nfitem_valipi"`);  
        htmlTrModelo = htmlTrModelo.replace('id="#imp_pis"', `name="_${indice}_u_nfitem_pis"`);  
        htmlTrModelo = htmlTrModelo.replace('id="#imp_cofins"', `name="_${indice}_u_nfitem_cofins"`);  
        htmlTrModelo = htmlTrModelo.replace('id="#imp_valicms"', `name="_${indice}_u_nfitem_valicms"`);

        CB.modal({
            corpo: htmlTrModelo,
            titulo: `Imposto <button id='cbSalvar' style='float: right; margin-top: 15px;' type='button' class='btn btn-danger btn-xs' onclick='CB.post()'><i class='fa fa-circle'></i>Salvar</button>`,
            classe: 'trinta',
        });
    }

    $(".modalImpostoClique").on("click", function() {
        CB.modal({
            corpo: $(`#imposto_total`).html(),
            titulo: `Imposto <button id='cbSalvar' style='float: right; margin-top: 15px;' type='button' class='btn btn-danger btn-xs' onclick='CB.post()'><i class='fa fa-circle'></i>Salvar</button>`,
            classe: 'trinta',
        });
    });

    $('#btn-modal-frete').on('click', function() {
        let ctesVinculadasHTML = '',
            ctesVinculadasNfHTML = '';

        const editar = (status != "CONCLUIDO"),
            habilitaVincular = !ctesVinculadasNf.length && !ctesVinculadas.length;

        if(ctesVinculadasNf.length) {

            ctesVinculadasNfHTML = '<hr class="w-100"><div class="w-100 d-flex mt-3 flex-col">'
            ctesVinculadasNfHTML += ctesVinculadasNf.map(item => (`
                                                        <div class="d-flex w-100 mb-2">
                                                            <a class="d-block col-xs-11" target="_blank" href="?_modulo=nfcte&_acao=u&idnf=${item.idnf}&_idempresa=${idEmpresa}">
                                                                ${item.idnf} - ${item.nome}
                                                            </a>
                                                            <i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer btn-remover-cte col-xs-1 text-right" data-idnf="${item.idobjetovinculo}" alt="Excluir !"></i>
                                                        </div>`
                                                    )).join(' ');
            ctesVinculadasNfHTML += `</div><hr class="w-100">`;
        }else if(ctesVinculadas.length) {

            ctesVinculadasHTML = '<hr class="w-100"><div class="w-100 d-flex mt-3 flex-col">'
            ctesVinculadasHTML += ctesVinculadas.map(item => (`
                                                        <div class="d-flex w-100 mb-2">
                                                            <a class="d-block col-xs-11" target="_blank" href="?_modulo=nfcte&_acao=u&idnf=${item.idcte}&_idempresa=${idEmpresa}">
                                                                ${item.idcte} - ${item.nome}
                                                            </a>
                                                            <i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer btn-remover-cte col-xs-1 text-right" data-idobjetovinculo="${item.idobjetovinculo}" alt="Excluir !"></i>
                                                        </div>`
                                                    )).join(' ');
            ctesVinculadasHTML += `</div><hr class="w-100">`;
        }
        
        const corpo = `<div class="w-100 d-flex flex-col align-items-center">
                            ${habilitaVincular ? `<h3 class="w-100">Selecione ou crie CTEs a serem vinculadas.</h3>` : ''}
                            <div class="w-100 d-flex form-group flex-wrap">
                                ${habilitaVincular ? `
                                    <label class="w-100">Selecionar CTE</label>
                                    <div class="w-100 d-flex flex-between align-items-center">
                                        <select id="input-cte" type="text" class="col-xs-11 mr-2" data-live-search="true" multiple ${habilitaVincular ? '' : 'disabled'}>
                                            ${nfCte.map(item => `<option value="${item.idnf}">[${item.idnf}] - ${item.nome}</option>`).join(' ')}
                                        </select>
                                        ${habilitaVincular ? `<i onclick="vincularCte();" class="fa fa-plus-circle fa-2x verde pointer" title="Adicionar CTe"></i>` : ''}
                                    </div>` : ''}
                                ${ctesVinculadasHTML}
                            </div>
                            ${habilitaVincular ? `
                                <h3 class="mt-0">ou</h3>
                                <div class="d-flex">
                                    <button class="btn btn-success text-uppercase d-flex align-items-center font-bold px-5 py-3" onclick='novoCte(<?=$_1_u_nf_idnf;?>)'>
                                        <i class="fa fa-plus fa-1x pointer" title="Adicionar CTe"></i>
                                        Gerar nova CTE
                                    </button>
                                </div>
                            ` : ''}
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

    $(".observacao").webuiPopover({
		trigger: "hover",
		placement: "right",
        width: 250,
		delay: {
			show: 300,
			hide: 0
		}
	});

    if ($("[name=_1_u_nf_idnf]").val()) 
    {
		$("#xmlnfe").dropzone({
            previewTemplate: $("#cbDropzone").html(),
            url: "form/_arquivo.php",
            idObjeto: $("[name=_1_u_nf_idnf]").val(),
            tipoObjeto: 'nf',
            tipoArquivo: 'XMLNFE',
            sending: function(file, xhr, formData) {
                formData.append("idobjeto", this.options.idObjeto);
                formData.append("tipoobjeto", this.options.tipoObjeto);
                formData.append("tipoarquivo", this.options.tipoArquivo);
            },
            success: function(file, response) {
                console.log("Upload completo para o arquivo:", file.name);
                altfimnfe(tipoconsumo);
            },
        });
	}

    CB.on('posPost',() => {
        if(idSpedC100.lenght > 0 && qtdArquivo == 0)
        {
            CB.post({
                objetos: `_9999_d_spedc100_idspedc100=${idspedc100}`
            });
        }
	});

    $(".historicoPrevisaoEntrega").webuiPopover({
        trigger: "click",
        placement: "right",
        width: 700,
        delay: {
            show: 300,
            hide: 0
        }
    });

	$(".oEmailorc").webuiPopover({
		trigger: "hover",
		placement: "right",
		delay: {
			show: 300,
			hide: 0
		}
	});

    
	//------- Funções JS -------

	//------- Funções Módulo -------
    function removerVinculoCompra(idObjetoVinculo, isCte = false) {
        if(!confirm('Remover vínculo da CTE desta compra?')) return false;
        if(!idObjetoVinculo) return alertAtencao('Id vínculo não encontrado.');

        if(!isCte) {
            CB.oModal.off('click.removerCte');
            CB.oModal.off('click.removerCteNf');
            CB.posPost = () => $('#btn-modal-frete').click();            
        }

        CB.post({
            objetos: {
                _1_d_objetovinculo_idobjetovinculo: idObjetoVinculo
            },
            parcial: true
        });
    }

    function setdtdataconf(inI, indate) 
    {
        var buttonWarnig = $('.btn-warning');
        if(buttonWarnig.length == 0){
            $('.divBotaoSalvar').find('button.btn-warning').remove();
            $('.divBotaoSalvar').after(`&nbsp;<button class="btn btn-warning pointer" onclick="salvarProporcao()"> <i class="fa fa-warning "></i> Salvar alterações </button>`);
        }
    }

    function salvarProporcao(){
        let atualizaDataProporcao = {};
        $('.dataconfdate').each(function(key, event){
            indice = key + 1;
            atualizaDataProporcao[`_prop${indice}_u_nfconfpagar_idnfconfpagar`] = $(event).attr('idnfconfpagar');
            atualizaDataProporcao[`_prop${indice}_u_nfconfpagar_datareceb`] = $(event).val();
        });

        CB.post({
            objetos: atualizaDataProporcao,
            parcial: true,
            msgSalvo: "Data atualizada"
        });

    }

	function gravaproduto(inidprodserv,inidnfitem){
		CB.post({
			objetos: "_x_u_nfitem_idnfitem="+inidnfitem+"&_x_u_nfitem_idprodserv="+inidprodserv
			,parcial: true  
			,posPost: function(resp,status,ajax){
				if(status="success"){
					$("#cbModalCorpo").html("");
					$('#cbModal').modal('hide');
				}else{
					alert(resp);
				}
			}
		})  
	}

	function preencheti(inidnf)
	{    
    	$("#idtipoprodserv"+inidnf).html("<option value=''>Procurando....</option>");
	
		$.ajax({
			type: "get",
			url : "ajax/buscacontaitem.php",
			data: { idcontaitem : $("#idcontaitem"+inidnf).val()//,idcontaitem : $("[name=modal_idcontaitem]").val()
			},
			success: function(data){
				$("#idtipoprodserv"+inidnf).html(data);
				$("[name=modal_idtipoprodserv]").html(data);					
			},
			error: function(objxmlreq){
				alert('Erro:<br>'+objxmlreq.status); 
			}
        })//$.ajax    
	}

	function preenchetimodal(inidnfitem, val)
	{
    	$("[name=modal_idtipoprodserv]").html("<option value=''>Procurando....</option>");
	
		$.ajax({
			type: "get",
			url : "ajax/buscacontaitem.php",
			data: {
				idcontaitem : val
			},
			success: function(data){
				$("[name=modal_idtipoprodserv]").html(data);
				$("#idtipoprodserv"+inidnfitem).html(data);

				if(!$("#idtipoprodserv"+inidnfitem).val() || !$("#idcontaitem"+inidnfitem).val()){
					$("#btn_"+inidnfitem).addClass('laranja');
					$("#btn_"+inidnfitem).attr('title','Categoria e/ou Subcategoria não Atribuídas');
				}else{
					$("#btn_"+inidnfitem).removeClass('laranja');
					$("#btn_"+inidnfitem).removeAttr('title');
				}
			},
			error: function(objxmlreq){
				alert('Erro:<br>'+objxmlreq.status);
			}
        })//$.ajax    
	}

	function atributonnfe(vthis) 
	{
        if ($(vthis).val() == 'M') {
            $("#nnfe").removeAttr("vnulo", "");
        } else {
            $("#nnfe").attr("vnulo", "");
        }
    }

	function duplicarcompra(vidnf) 
	{
        var strCabecalho = "</strong>Complementar Cotação " + vidnf + " <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='geranota(" + vidnf + ");'><i class='fa fa-circle'></i>Salvar</button></strong>";
        $("#cbModalTitulo").html((strCabecalho));

        var htmloriginal = $("#compra" + vidnf).html();
        var objfrm = $(htmloriginal);
        objfrm.find("#_f_nome" + vidnf).attr("id", "x_f_nome");

        CB.modal({
            titulo: strCabecalho,
            corpo: objfrm.html(),
            classe: 'sessenta',
            aoAbrir: function(vthis) {
                CB.oModal.find('.calendario').daterangepicker({
                    "singleDatePicker": true,
                    "showDropdowns": true,
                    "linkedCalendars": false,
                    "opens": "left",
                    "locale": {format: 'DD/MM/YYYY'}
                }).on('apply.daterangepicker', function(ev, picker) {
                    console.log(picker.startDate.format('DD/MM/YYYY'));
                    $(this).val(picker.startDate.format('DD/MM/YYYY'));
                });
            }
        });
    }

	function mostrarmodalgt(idnfitem, descricao, i) 
	{
        if (descricao.length > 60) {
            desc = descricao.substring(0, 60).concat('...');
        } else {
            desc = descricao;
        }

        let objhtml = $($("#tb_" + idnfitem).html());

        let contaitem = objhtml.find("#idcontaitem" + idnfitem);
        let tipoprodserv = objhtml.find("#idtipoprodserv" + idnfitem);

        contaitem.attr({
            'name': 'modal_idcontaitem',
            'onchange': `atribuivalorselect(this,${idnfitem},'contaitem')`,
            'disabled': status == 'CONCLUIDO' ? true : false
        });
        tipoprodserv.attr({
            'name': 'modal_idtipoprodserv',
            'onchange': `atribuivalorselect(this,${idnfitem},'tipoprodserv')`,
            'disabled': status == 'CONCLUIDO' ? true : false
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
            titulo: "</strong>" + desc + "</strong>",
            corpo: [objhtml],
            classe: 'cinquenta',
        });
    }

	function setvalnfitem(vthis, inidnfitem) 
	{
        var valconv = $(vthis).val().replace(",", ".");
        var invlritemext = $(`.convmoeda-${inidnfitem}`).val().replace(",", ".");
        var nvalor = valconv * invlritemext;
        $(`#${inidnfitem}`).val(nvalor.toFixed(4));
        $(`#_${inidnfitem}10_u_nfitem_vlritem`).val(nvalor.toFixed(4));

        var qtd = $(`.qtd-${inidnfitem}`).val().replace(",", ".");
        var total = qtd * nvalor;
        $(`.total-${inidnfitem}`).val(total.toFixed(2));
    }

	function alteramoeda(vthis, inidnfitem, inmoeda) 
	{
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

	function analisaval($this) 
	{
        if (parseFloat($($this).val()) < 0) {
            alertAtencao('O valor do item não pode ser menor que 0.00');
            $($this).focus();
            return false;
        }
        return true;
    }

    function saveRH(linha,vthis){
        if(analisaval(vthis)){
            if(CB.modulo == "comprasrh"){
                oldVal = parseFloat($(`[name=_${linha}_u_nfitem_vlritem]`).attr('oldvalue'));
                newVal = parseFloat($(`[name=_${linha}_u_nfitem_vlritem]`).val());
                qtd = parseFloat($(`[name=_${linha}_u_nfitem_qtd]`).val());
                desc = parseFloat($(`[name=_${linha}_u_nfitem_des]`).val()) || 0;
                totalItem = (newVal - desc) * qtd;
                idnfitem = $(`[name=_${linha}_u_nfitem_idnfitem]`).val();
                $(`[name=_${linha}_u_nfitem_total]`).val(totalItem.toFixed(2));
                total = 0;
                $(`[name$=_u_nfitem_total]`).each(function(){
                    total += parseFloat($(this).val());
                });
                totaldes = 0;
                $(`[name$=_u_nfitem_des]`).each(function(){
                    totaldes += parseFloat($(this).val());
                });
                $(`#vlrtotalnf`).html(total.toFixed(2));
                CB.post({
                    objetos: `_x_u_nfitem_idnfitem=${idnfitem}&_x_u_nfitem_vlritem=${newVal}`,
                    parcial: true,
                    refresh: false,
                });
            }
        }
    }

    function alteraqtd(linha,vthis){
        if(analisaval(vthis)){
            if(CB.modulo == "comprasrh"){
                qtd = parseFloat($(vthis).val());
                vlritem = parseFloat($(`[name=_${linha}_u_nfitem_vlritem]`).val());
                desc = parseFloat($(`[name=_${linha}_u_nfitem_des]`).val()) || 0;
                totalItem = (vlritem - desc) * qtd;
                $(`[name=_${linha}_u_nfitem_total]`).val(totalItem.toFixed(2));
                total = 0;
                $(`[name$=_u_nfitem_total]`).each(function(){
                    total += parseFloat($(this).val());
                });
                totaldes = 0;
                $(`[name$=_u_nfitem_des]`).each(function(){
                    totaldes += parseFloat($(this).val());
                });
                $(`#vlrtotalnf`).html(total.toFixed(2));
                idnfitem = $(`[name=_${linha}_u_nfitem_idnfitem]`).val();
                CB.post({
                    objetos: `_x_u_nfitem_idnfitem=${idnfitem}&_x_u_nfitem_qtd=${qtd}`,
                    parcial: true,
                    refresh: false,
                });
            }
        }

    }

	function gerarlotem(inidnfitem, rotulo, qtd, un, $qtdexiste, larguraTabela) 
	{
        htmlTrModelo = $("#lote_" + inidnfitem).html();
        htmlTrModelo = htmlTrModelo.replace("#lote_idnfitem", "_1#idnfitem");
        htmlTrModelo = htmlTrModelo.replace("#lote_qtdprod", "_1#qtdprod");
        htmlTrModelo = htmlTrModelo.replace("#lote_idprodserv", "_1#idprodservlote");
        htmlTrModelo = htmlTrModelo.replace("#lote_exercicio", "_1#exercicio");
        htmlTrModelo = htmlTrModelo.replace("tr_lote_", "");
        htmlTrModelo = htmlTrModelo.replace("quantidade", "qtd_lote");

        var objfrm = $(htmlTrModelo);
        largura = (larguraTabela == 'Y') ? 'style="width: 30%;"' : '';

        strCabecalho = `<strong title="${qtd} - ${un} - ${rotulo}" class="col-xs-10" style="text-wrap: nowrap; max-width: 100%; overflow: hidden; text-overflow: ellipsis; margin-top: 12px;">
                            ${qtd} - ${un} - ${rotulo}
                        </strong>
                        <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='geralotes(${qtd}, ${inidnfitem}, ${$qtdexiste});' style="float: right; margin-top: 14px; margin-right: 8px; display: block;"><i class='fa fa-circle'></i>Salvar</button>`;

        CB.modal({
            titulo: strCabecalho,
            corpo: `<table class='table table-hover planilha grade' ${largura}>${objfrm.html()}</table>`,
            classe: 'sessenta',
            aoAbrir: function(vthis) {
                $("[name='_h1_i_modulohistorico_valor']").daterangepicker({
                    "singleDatePicker": true,
                }).on('apply.daterangepicker', function(ev, picker) {
                    console.log(picker.startDate.format('YYYY-MM-DD'));
                });
            }
        });
    }

	function relacionaprodserv(inidnfitem) 
	{
        let content = `
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">			                           
                            <table class="table table-striped planilha" >
                                <tr>
                                    <td><input type="text" cbvalue="" value="" style="width: 50em;" class="ui-autocomplete-input idprodservNftItem"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;

        CB.modal({
            titulo: "</strong>Selecione o produto</strong>",
            corpo: content,
            classe: 'quarenta', 
            aoAbrir: function(){
                CB.oModal.find('.idprodservNftItem').autocomplete({
                    source: jsonProd,
                    delay: 0,
                    create: function() {
                        $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                            return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
                        };
                    },
                    select: function(event, ui){
                        gravaproduto(ui.item.value, inidnfitem);
                    }
                });
            }
        });
    }

	function excluitmp(vthis) {
        $(vthis).parent().parent().remove();
    }

	function novalinha(vthis, inidnfitem) 
	{
        var linha = $("#cbModalCorpo tbody tr").length;

        TrModelo = $(".tr_lote_" + inidnfitem).html();
        TrModelo = TrModelo.replace("#lote_idnfitem", "_" + linha + "#idnfitem");
        TrModelo = TrModelo.replace("#lote_qtdprod", "_" + linha + "#qtdprod");
        TrModelo = TrModelo.replace("#lote_idprodserv", "_" + linha + "#idprodservlote");
        TrModelo = TrModelo.replace("#lote_exercicio", "_" + linha + "#exercicio");
        TrModelo = TrModelo.replace("tr_lote_", "");
        TrModelo = TrModelo.replace("quantidade", "qtd_lote");
        TrModelo = "<tr>" + TrModelo + "</tr>";

        var tr_id2 = $(vthis).parent().parent(); // pega a tr pelo id
        // adiciona o elemento criado, a partir do nó pai (no caso <table>)
        tr_id2.before(TrModelo);
    }

    function excluir(idnfitem) 
    {
        if (confirm("Deseja realmente excluir o item selecionado?")) 
        {
            CB.post({
                "objetos": "_x_d_nfitem_idnfitem=" + idnfitem,
                parcial: true
            });
        }
    }

    function novoCte(inidnf) 
    {
        if (confirm("Gerar CTE para esta compra?")) 
        {
            CB.oModal.off('click.removerCte');
            CB.oModal.off('click.removerCteNf');

            CB.posPost = () => $('#btn-modal-frete').click();    

            CB.post({
                objetos: {
                    _xcte_i_nf_tiponf: 'T',
                    _xcte_i_nf_idobjetosolipor: inidnf,
                    _xcte_i_nf_tipoobjetosolipor: 'nf'
                },
                parcial: true
            });
        }
    }

    function vincularCte() {
        if(!confirm('Víncular CTE a esta compra?')) return false;

        const cteIdArray = $('#input-cte').val();

        if(cteIdArray && cteIdArray.length) {
            let cteInsertObj = {};

            cteIdArray.forEach((idCte, key) => {
                cteInsertObj[`_${key + 1}_i_objetovinculo_idobjeto`] = idnf;
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

    function atualizafrete(vthis, inidnf) 
    {
        CB.post({
            objetos: "_atfrete_u_nf_idnf=" + inidnf + "&_atfrete_u_nf_frete=" + $(vthis).val(),
            parcial: true
        });
    }

    function atualizadesconto(vthis, inidnf) 
    {
        CB.post({
            objetos: "_atdesc_u_nf_idnf=" + inidnf + "&_atdesc_u_nf_desconto=" + $(vthis).val(),
            parcial: true,
            posPost: function(){
                CB.post();
            }
        });
    }

    function atualizaTotal(totalComDesconto) 
    {
        let pis = $("input[nome='_1_u_nf_pis']").val().replace(",", ".")
        let cofins = $("input[name='_1_u_nf_cofins']").val().replace(",", ".")
        let csll = $("input[name='_1_u_nf_csll']").val().replace(",", ".")
        let irrf = $("input[name='_1_u_nf_ir']").val().replace(",", ".")
        let inss = $("input[name='_1_u_nf_inss']").val().replace(",", ".")
        let issretido = $("input[name='_1_u_nf_issret']").val().replace(",", ".")
        let total = (totalComDesconto - pis - cofins - csll - irrf - inss - issretido).toFixed(2);

        $('#vlrtotal').val(total);
    }

    function excluiritem(idnfitem) 
    {
        if (confirm("Deseja realmente excluir o teste selecionado?")) 
        {
            CB.post({
                "objetos": "_x_d_nfitem_idnfitem=" + idnfitem,
                parcial: true
            });
        }
    }

    function atualizadiasentrada(vthis) 
    {
        CB.post({
            objetos: "_ati_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_ati_u_nf_diasentrada=" + $(vthis).val(),
            parcial: true,
            refresh: false,
            msgSalvo: false,
            posPost: function(d, t, j) {
                CB.post({
                    objetos: {},
                    parcial: false,
                    msgSalvo: "Vencimento Atualizado"
                });
            }
        })
    }

    function atualizaintervalo(vthis) 
    {
        CB.post({
            objetos: "_ati_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_ati_u_nf_intervalo=" + $(vthis).val(),
            parcial: true
        })
    }

    function geralotes(qtd, idnfitem, qtdexiste) 
    {
        vqtd = Number.parseFloat(qtd);
        soma = qtdexiste;
        numeros = document.querySelectorAll(".qtd_lote" + idnfitem)
            .forEach((elemento) => {
                soma += Number.parseFloat(elemento.value);
            });
        console.log(soma);

        if (soma > vqtd) {
            alert("Quantidade selecionada (" + soma + ") maior que a quantidade do item(" + vqtd + ").");
        } else {
            var vInputs = $("#cbModalCorpo").find(":input").serialize().split("%23").join("#");
            var vidnf = $("[name=_1_u_nf_idnf]").val();

            CB.post({
                objetos: "_x_u_nf_idnf=" + vidnf + "&geralotes=Y&" + vInputs,
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
    }

    function historico(tipo, inid, texto) 
    {
        CB.modal({
            titulo: "</strong>Histórico " + texto + "</strong>",
            corpo: $("#" + tipo + inid).html(),
            classe: 'sessenta',
        });
    }

    function atualizaconf(vthis, campo) 
    {
        CB.post({
            objetos: `_x_u_nf_idnf=` + $("[name=_1_u_nf_idnf]").val() + `&_x_u_nf_` + campo + `=` + $(vthis).val(),
            parcial: true,
            refresh: false
        })
    }

    // função para geração de tags automaticamente para os produtos
    function criaTags(qtd, descr, idobjetoorigem, tipoobjetoorigem) 
    {
        if (qtd > 0) 
        {
            if (confirm("Tem certeza que deseja criar " + qtd + " Tag(s) de " + descr)) 
            {
                var str = "";
                var k = 1;

                while (k <= qtd) {
                    str = str + "_" + k + "_i_tag_idempresa=<?=cb::idempresa() ?>&_" + k + "_i_tag_descricao=" + descr + "&_" + k + "_i_tag_idobjetoorigem=" + idobjetoorigem + "&_" + k + "_i_tag_tipoobjetoorigem=" + tipoobjetoorigem + "&_" + k + "_i_tag_status=ABERTA&";
                    k++;
                }
                var str1 = str.substr(0, (str.length - 1));

                CB.post({
                    objetos: str1,
                    parcial: true
                });
            }
        } else {
            alert('Por favor, insira a quantidade.');
        }
    }

    function verifnnfe() 
    {
        var vnnfe = $("#nnfe").val();
        vnnfe = vnnfe.trim();
        var vidpessoa = $("#idpessoa").attr("cbvalue");
        var vidnf = $("#idnf").val();
        if (vnnfe != "" && vidpessoa != "") //validar somente se o campo contiver algum valor
        { 
            $.ajax({
                type: 'get',
                url: 'ajax/verifnnfe.php',
                data: {
                    nnfe: vnnfe,
                    idpessoa: vidpessoa,
                    idnf: vidnf
                },
                /*********************************************************************/
                success: function(data) {

                    if (data == "OK") //se o nome de usuario estiver livre (a pagina php chamada retornou a string OK)
                    {
                        $("#msgbox").fadeTo(100, 0.9, function() //mostra o messagebox
                            {
                                $(this).html('Ok!').addClass('messageboxok').fadeTo(100, 1);
                                document.getElementById("cbSalvar").style.display = "";
                            });
                     //se a pagina retornou erro 
                    } else if (data == "ERRO"){
                        //mostra qualquer condicao diferente. Ex: erro de php
                        $("#msgbox").fadeTo(100, 0.9, function(){
                            $(this).html('Erro:<br>' + data).addClass('messageboxerror').fadeTo(100, 1);
                            document.getElementById("cbSalvar").style.display = "none";
                            document.getElementById("nnfe").focus();
                        });
                    } else {
                        //mostra qualquer condicao diferente. Ex: erro de php
                        $("#msgbox").fadeTo(100, 0.9, function(){
                            $(this).html('Erro:<br>' + data).addClass('messageboxerror').fadeTo(100, 1);
                            document.getElementById("cbSalvar").style.display = "none";
                            document.getElementById("nnfe").focus();
                        });
                    }
                },
                /*********************************************************************/
                error: function(objxmlreq) {
                    $("#msgbox").fadeTo(100, 0.1, function() {
                        $(this).html('Erro:<br>' + objxmlreq.status).addClass('messageboxerror').fadeTo(100, 1);
                        document.getElementById("submit").style.display = "none";
                        document.getElementById("nnfe").focus();
                    });
                }
            }) //$.ajax
        } else {
            //se o campo nao contiver valor, esconder a msgbox
            $("#msgbox").fadeOut();
        }
    }

    function novolote(inidnfitem, inqtd, inprodserv, date) 
    {
        CB.post({
            objetos: `_x_i_lote_idprodserv=` + inprodserv + `&_x_i_lote_status=ABERTO&_x_i_lote_idfluxostatus=${idfluxostatus}&_x_i_lote_idnfitem=${inidnfitem}&_x_i_lote_qtdpedida=${inqtd}&_x_i_lote_qtdprod=${inqtd}&_x_i_lote_exercicio=${date}&_x_i_lote_idunidade=${idunidadelote}`,
            parcial: true
        })
    }

    function conferencia(incampo) 
    {
        var idfluxostatus = getIdFluxoStatus(pagvalmodulo, 'DIVERGENCIA');
        var idFluxoStatusHist = getIdFluxoStatusHist(pagvalmodulo, 'DIVERGENCIA');
        CB.post({
            objetos: `_x_i_nfpendencia_idnf=${$("[name=_1_u_nf_idnf]").val()}&_1_u_nf_idnf=${$("[name=_1_u_nf_idnf]").val()}&_1_u_nf_tiponf=${$("#tiponf").val()}&_1_u_nf_status=DIVERGENCIA&_1_u_nf_idfluxostatus=${idfluxostatus}`,
            parcial: true,
            posPost: function() {
                CB.post({
                    urlArquivo: 'ajax/_fluxo.php?fluxo=fluxo',
                    refresh: false,
                    objetos: {
                        "_modulo": pagvalmodulo,
                        "_primary": 'idnf',
                        "_idobjeto": $("[name=_1_u_nf_idnf]").val(),
                        "idfluxo": '',
                        "idfluxostatushist": idFluxoStatusHist,
                        "idstatusf": idfluxostatus,
                        "statustipo": 'DIVERGENCIA',
                        "idfluxostatus": idfluxostatus,
                        "idfluxostatuspessoa": '',
                        "ocultar": '',
                        "prioridade": '20',
                        "tipobotao": '',
                        "acao": "alterarstatus"
                    }
                });
            }
        })
    }

    function atualizaobscp(vthis, inidcontapagar) 
    {
        CB.post({
            objetos: `_x_u_contapagar_idcontapagar=` + inidcontapagar + `&_x_u_contapagar_obs=` + $(vthis).val(),
            parcial: true
        })
    }

    function atualizavlitem(vthis, inidcontapagar) 
    {
        CB.post({
            objetos: "_atitem_u_contapagaritem_idcontapagaritem=" + inidcontapagar + "&_atitem_u_contapagaritem_valor=" + $(vthis).val(),
            parcial: true
        });
    }

    function mostraInputFormapagamento(vthis){
        $(vthis).hide();
        $(vthis).siblings("label").hide();
        $(vthis).siblings("input").css("display","block");
        $(vthis).siblings("select").css("display","block");
    }

    function atualizavlcp(vthis, inidcontapagar) {
        CB.post({
            objetos: "_1_u_contapagar_idcontapagar=" + inidcontapagar + "&_1_u_contapagar_valor=" + $(vthis).val(),
            parcial: true
        });
    }

    function atualizacontaitem(vthis, vidformapagamento, vstatus, vidcontapagar, vidcontapagaritem) 
    {
        CB.post({
            objetos: `_altcp_u_contapagar_idcontapagar=${vidcontapagar}&_altcpi_u_contapagaritem_idcontapagaritem=${vidcontapagaritem}&idformapagamento=${$(vthis).val()}&statuscontapagar=${vstatus}&idformapagamentoant=${vidformapagamento}`,
            parcial: true
        });
    }

    function excluiritemtemp(vthis) 
    {
        if (confirm("Deseja realmente excluir este item?")) {
            $(vthis).parent().parent().remove();
        }
    }

    function showModal() 
    {
        var strCabecalho = "<strong>Nova Parcela <button id='cbSalvar' type='button'  style='margin-left:370px' class='btn btn-danger btn-xs' onclick='geracontapagar();'><i class='fa fa-circle'></i>Salvar</button></strong> ";

        var htmloriginal = $("#novaparcela").html();
        var objfrm = $(htmloriginal);

        objfrm.find("#formapagnovaparc").attr("name", "_modalnovaparcelacontapagar_idformapagamento_");
        objfrm.find("#valornovaparc").attr("name", "_modalnovaparcelacontapagar_valor_");
        objfrm.find("#vencnovapart").attr("name", "_modalnovaparcelacontapagar_datapagto_");

        CB.modal({
            corpo: objfrm.html(),
            titulo: strCabecalho
        })
    }

    function geracontapagar() 
    {
        let valTipo = $("[name='_modalnovaparcelacontapagar_tipo_']:checked").val() || "D";
        var str = "_x9_i_contapagar_idformapagamento=" + $("[name=_modalnovaparcelacontapagar_idformapagamento_]").val() +
            "&_x9_i_contapagar_status=PENDENTE&_x9_i_contapagar_parcela=" + $('#parcela_parcelas').val() + "&_x9_i_contapagar_parcelas=" + $('#parcela_parcelas').val() + "&_x9_i_contapagar_valor=" + $("[name=_modalnovaparcelacontapagar_valor_]").val() +
            "&_x9_i_contapagar_datapagto=" + $("[name=_modalnovaparcelacontapagar_datapagto_]").val() +
            "&_x9_i_contapagar_datareceb=" + $("[name=_modalnovaparcelacontapagar_datapagto_]").val() +
            "&_x9_i_contapagar_tipo=" + valTipo + "&_x9_i_contapagar_visivel=S&_x9_i_contapagar_idpessoa=" + $("[name=_1_u_nf_idpessoa]").attr("cbvalue") +
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

    function consumo(inidlote)
    {
        CB.modal({
            titulo: "</strong>Histórico do Lote</strong>",
            corpo: $("#consumo" + inidlote).html(),
            classe: 'sessenta',
        });
    }
    
    function editarrateio() 
    {
        var idnf = $("[name=_1_u_nf_idnf]").val()
        var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';
        CB.modal({
            url: "?_modulo=rateioitemdest&_acao=u&tipo=nf&idnf=" + idnf + idempresa,
            header: "Editar Rateio",
            aoFechar: function() {
                location.reload();
            }
        });
    }

    function editarcobranca(idnf) 
    {
        CB.modal({
            url: "?_modulo=rateioitemdestnf&_acao=u&tipo=nf&idnf=" + idnf ,
            header: "Cobrança de Rateio",
            aoFechar: function() {
                location.reload();
            }
        });
    }


    function verificarateio() 
    {
        $.ajax({
            type: "get",
            url: "ajax/verificarateio.php",
            data: {
                idnf: $("[name=_1_u_nf_idnf]").val()
            },
            success: function(data) {
                $('#rateiomodal').html(data);
            }
        }) //$.ajax	
    }

    function atribuivalorselect(vthis, idnfitem, tipo) 
    {
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
                preenchetimodal(idnfitem, vthis.value);
            }

        } else {
            $("#iidtipoprodserv" + idnfitem).val(vthis.value);
            $("#idtipoprodserv" + idnfitem + " option:selected").removeAttr('selected');
            $("#idtipoprodserv" + idnfitem).val(vthis.value).change();

            if (vthis.value != "") {
                $("#btn_" + idnfitem).removeClass('laranja');
                $("#btn_" + idnfitem).removeAttr('title');
            }

            tiponf = $('#tiponf').val();
            idnf = $('#idnf').val();
            iidcontaitem = $("#iidcontaitem"+idnfitem).val();
            iidtipoprodserv = $("#iidtipoprodserv"+idnfitem).val();
            prazo=$("[name=_1_u_nf_prazo]").val();

            CB.post({
                objetos: `_x_u_nfitem_idnfitem=${idnfitem}&_x_u_nfitem_idcontaitem=${iidcontaitem}&_x_u_nfitem_idtipoprodserv=${iidtipoprodserv}&_1_u_nf_tiponf=${tiponf}&_1_u_nf_idnf=${idnf}&_1_u_nf_prazo=${prazo}&scape=Y`,
                parcial: true,
                refresh: false
            });
        }
    }

    function alteraoutros(vthis) 
    {
        valor = $(vthis).val();
        if (valor == 'OUTROS') {
            $(vthis).parent().append('<input style="margin-top:4px;" id="justificaticaText" name="_h1_i_modulohistorico_justificativa" value="" class="size50" type="text" placeholder="Digite aqui a sua justificativa" />');
        } else {
            $('#justificaticaText').remove();
        }
    }

    function geranota(vidnf) 
    {
        let instr = $("#cbModalCorpo").find('input').serialize().split("__").join("#");
        let novofornecedor = $("#x_f_nome").attr("cbvalue");

        CB.post({
            objetos: "_x_u_nf_idnf=" + vidnf + "&duplicar=Y&" + instr + "&idpessoa=" + novofornecedor,
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

    function editarclientefat() 
    {
        $("[name=_1_u_nf_idpessoa]").removeClass("desabilitado");
        $("[name=_1_u_nf_idpessoa]").removeAttr("disabled");
    }

    function buscarDadoFornecedor(vIdPessoa)
    {
        $.ajax({
            type: "POST",
            async: false,
            url : "ajax/nfentrada_ajax.php",
            data: { "idpessoa": vIdPessoa, "comandoExecutar": "buscarDadosPessoa" },
            success: function(data)
            {
                //Transforma a string json em objeto
                jdadosPessoa = jsonStr2Object(data);
                if(jdadosPessoa)
                {
                    $(".razaosocial").html(jdadosPessoa.razaosocial);
                    $(".cpfcnpj").html(jdadosPessoa.cpfcnpj);		
                }else{
                    alertErro("Javascript: Erro ao recuperar Dados Conta Item");
                }
            }
        });
    }

    function salvarNovoItem(vthis){
        obj = {};
        $(vthis).parent().parent().find("input").each(function(){
            obj[$(this).attr("name")] = $(this).val();
        });
        obj["_1_u_nf_idnf"] = $("[name=_1_u_nf_idnf]").val();

        CB.post({
            objetos: obj,
            parcial: true,
            refresh: false,
            posPost: function(){
                location.reload();
            }
        });
    }

    function verificavalor(vthis){
        
        if($(vthis).val() < 1){
            if (!confirm("Valor menor que 1 o lote não irá aparecer na listagem, deseja realmente retirar este lote da devolução?")){
                valor =  $(vthis).attr('valor');
                $(vthis).val(valor);
            }   
        }
    }
    
    CB.on('prePost', function(){
        let alerta = false;
        let parcela, parcelaArray = '';
        hoje = new Date();
        virgula = '';
        const status = ['ABERTO', 'FECHADO', 'PENDENTE'];
        $(".prazoparc").each((i, event)=> {
            dataCadastro = $(event).attr('value').split('/');
            parcela = $(event).attr('parcela');
            statusCI = $(event).attr('statusCI');
            prazo = new Date(dataCadastro[2], dataCadastro[1] - 1, dataCadastro[0], 23,59,59);
            if((prazo < hoje ? true : false) && status.indexOf(statusCI) >= 0 && CB.modulo != 'comprasrh'){
                alerta = true;
                parcelaArray += virgula + parcela;
                virgula = ', ';
            }
        });
               
        if(alerta){
            if(!confirm(`A data de Recebimento da(s) parcela(s) ${parcelaArray} está(ão) \nanterior(es) a data atual.\n\nDeseja continuar?`)){
                return {objetos:{},msgSalvo:false,refresh:false}
            }
        }
    });

    CB.on('prePost', () => {
        if((($('#fdata4').length && moment($('#fdata').val(), 'DD/MM/YYYY') > moment($('#fdata4').val(), 'DD/MM/YYYY')) ||
            ($('#fdata2').length && moment($('#fdata').val(), 'DD/MM/YYYY') > moment($('#fdata2').val(), 'DD/MM/YYYY')) ||
            ($('#fdata1').length && moment($('#fdata').val(), 'DD/MM/YYYY') > moment($('#fdata1').val(), 'DD/MM/YYYY'))) && CB.modulo != 'comprasrh') {
            alertAtencao('Data de Recebimento anterior a data de emissão!');

            return false
        }
    });

    function vincularTags(inidnfitem) 
	{
        htmlTrModelo = $(`#vinculotag_${inidnfitem}`).html();
        htmlTrModelo = htmlTrModelo.replace("#tag_idnfitem", "_1#idnfitem");
        htmlTrModelo = htmlTrModelo.replace("#tag_idtagclass", "_1#idtagclass");
        htmlTrModelo = htmlTrModelo.replace("#tag_idtag", "_1#idtag").replace("#idtag_idtag", "idtag_");
        htmlTrModelo = htmlTrModelo.replace("#tag_categoria", "_1#categoria");
        htmlTrModelo = htmlTrModelo.replace("#tag_km", "_1#km");
        htmlTrModelo = htmlTrModelo.replace("#div_categoria", "_1#categoria");
        htmlTrModelo = htmlTrModelo.replace("#div_km", "_1#km_rodados");
        htmlTrModelo = htmlTrModelo.replace("#tag_idnfitemacao", "_1#idnfitemacao");

        strCabecalho = `<strong><button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='gerarTag()'><i class='fa fa-circle'></i>Salvar</button></strong>`;

        CB.modal({
            titulo: strCabecalho,
            corpo: `<table>${htmlTrModelo}</table>`,
            classe: 'quarenta',
            aoAbrir: function(vthis) {
                $("[name='_h1_i_modulohistorico_valor']").daterangepicker({
                    "singleDatePicker": true,
                }).on('apply.daterangepicker', function(ev, picker) {
                    console.log(picker.startDate.format('YYYY-MM-DD'));
                });
            }
        });
    }

    function mostrarTagsPorTagClass(idnfitem, vthis)
    {
        campoSelecionado = $(vthis).val();
        campoSelecionadoold = $(`#idtag_${idnfitem}_old`).val();

        $(`#idtag_${idnfitem} option`).each(function (n, elem){ 
            idtagclass = $(elem).attr('idtagclass');
            if(idtagclass == campoSelecionado || idtagclass == undefined){
                $(elem).css('display','block');
            } else{
                $(elem).css('display','none');
            }

            if(campoSelecionadoold != campoSelecionado){
                $(elem).removeAttr('selected')
            }
        });

        if(campoSelecionado == 3){
            $('._categoria').show();
            $('._km_rodados').show();
        } else {
            $('._categoria').hide();
            $('._km_rodados').hide();
        }
    }

    function gerarTag() 
    {
        if($("[name=_1#idnfitemacao]").val() == undefined || $("[name=_1#idnfitemacao]").val() == ''){
            condicao = 'i';
            campoidnfitemacao = '';
        } else {
            condicao = 'u';
            campoidnfitemacao = `&_nit9_${condicao}_nfitemacao_idnfitemacao=${$("[name=_1#idnfitemacao]").val()}`;
        }

        var idempresa = (getUrlParameter("_idempresa")) ? getUrlParameter("_idempresa") : '';
        var str = `_nit9_${condicao}_nfitemacao_idnfitem=${$("[name=_1#idnfitem]").val()}`+
                  `&_nit9_${condicao}_nfitemacao_idobjeto=${$("[name=_1#idtag]").val()}` +                  
                  `&_nit9_${condicao}_nfitemacao_tipoobjeto=tag` +
                  `&_nit9_${condicao}_nfitemacao_idobjetoext=${$("[name=_1#idtagclass]").val()}` +                  
                  `&_nit9_${condicao}_nfitemacao_tipoobjetoext=tagclass` +
                  `&_nit9_${condicao}_nfitemacao_categoria=${$("[name=_1#categoria]").val()}` +
                  `&_nit9_${condicao}_nfitemacao_idempresa=${idempresa}` +
                  `&_nit9_${condicao}_nfitemacao_kmrodados=${$("[name=_1#km]").val()}` +
                  `&_nit9_${condicao}_nfitemacao_status=PENDENTE` +
                  campoidnfitemacao;

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

    function altcheck(vtab, vcampo, vid, vcheck) {
        CB.post({
            objetos: `_x_u_${vtab}_id${vtab}=${vid}&_x_u_${vtab}_${vcampo}=${vcheck}&_x_u_${vtab}_nfe=${vcheck}`,
            parcial: true
        });
    }

    function manifestanf(vthis){       
       CB.post({
            objetos: `_1_u_nf_idnf=${idnf}&_1_u_nf_idnfe=${$(vthis).val()}`,
            parcial: true,
            posPost: function() {
                enviomanifestacao();
            }
        });
    }

    function enviomanifestacao() 
	{
        vurl = "../inc/nfe/sefaz4/func/manidest.php?idnotafiscal="+idnf;
        if (confirm("Confirmar recebimento da Nota Fiscal para a Sefaz?")) 
		{
            $.ajax({
                type: "get",
                url: vurl,
                success: function(data) {
                    //altfimnfe(tipoconsumo);
                    alert(data);
                    document.location.reload();
                },
                error: function(objxmlreq) {
                    alert('Erro:\n' + objxmlreq.status);
                }
            }) //$.ajax
        }
    }

    function altfimnfe(infim) 
	{
        var str;
        if (infim == 'faticms') {
            str = `_x_u_nf_idnf=` + $("[name=_1_u_nf_idnf]").val() + `&_x_u_nf_faticms=Y&_x_u_nf_consumo=N&_x_u_nf_imobilizado=N&_x_u_nf_outro=N&_x_u_nf_comercio=N`;
        }
        if (infim == 'consumo') {
            str = `_x_u_nf_idnf=` + $("[name=_1_u_nf_idnf]").val() + `&_x_u_nf_faticms=N&_x_u_nf_consumo=Y&_x_u_nf_imobilizado=N&_x_u_nf_outro=N&_x_u_nf_comercio=N`;
        }
        if (infim == 'imobilizado') {
            str = `_x_u_nf_idnf=` + $("[name=_1_u_nf_idnf]").val() + `&_x_u_nf_faticms=N&_x_u_nf_consumo=N&_x_u_nf_imobilizado=Y&_x_u_nf_outro=N&_x_u_nf_comercio=N`;
        }
        if (infim == 'outro') {
            str = `_x_u_nf_idnf=` + $("[name=_1_u_nf_idnf]").val() + `&_x_u_nf_faticms=N&_x_u_nf_consumo=N&_x_u_nf_imobilizado=N&_x_u_nf_outro=Y&_x_u_nf_comercio=N`;
        }
        if (infim == 'comercio') {
            str = `_x_u_nf_idnf=` + $("[name=_1_u_nf_idnf]").val() + `&_x_u_nf_faticms=N&_x_u_nf_consumo=N&_x_u_nf_imobilizado=N&_x_u_nf_outro=N&_x_u_nf_comercio=Y`;
        }

        CB.post({
            objetos: str,
            parcial: true,
            posPost: function() {
                gerainfsped();
            }
        })
    }

    function alterarMoeda(){
        let content = `<div class="row">
                <div class="col-md-12">
                    <div class="panel-heading">			                           
                        <table class="table table-striped planilha" >
                            <tr>
                                <td colspan="2"><b>Escolher moeda e aplicar a todos itens:</b></td>
                            </tr>
                            <tr>
                                <td>Moeda</td>
                                <td class="valorMoeda" style="display: none;">Valor</td>
                            </tr>
                            <tr>
                                <td style="width: 50%;">
                                    <select class="size5 moedanova" style="border: 1px solid #DDDDDD; width: 100% !important;" onchange="mudarMoeda(this)">
                                        <option value="BRL">BRL</option>
                                        <option value="USDNACIONAL">USD NACIONAL</option>
                                        <option value="EURNACIONAL">EUR NACIONAL</option>
                                        <option value="USDCOMEX">USD COMEX</option>
                                        <option value="EURCOMEX">EUR COMEX</option>
                                    </select></td>
                                <td class="valorMoeda" style="display: none;"><input type="text" value="" class="valorUnitarioMoeda" name="valorUnitarioMoeda" style="border: 1px solid #DDDDDD; width: 100%;"></td>
                            </tr>
                            <tr>
                                <td style="text-align: center;">
                                    <button type="button" onclick="fecharModal()" class="btn btn-xs botaofluxo" style="margin:3px; background:#d00038; color:#ffffff; height: 25px; width: 100%;">
                                        CANCELAR
                                    </button>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" onclick="inserirValorMoeda()" class="btn btn-xs botaofluxo" style="margin:3px; background:#0f8041; color:#ffffff; height: 25px; width: 100%;">
                                        APLICAR
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>`;

        CB.modal({
            titulo: "</strong>Conversão</strong>",
            corpo: content,
            classe: 'trinta', 
            aoAbrir: function(){
                CB.oModal.find('.idprodservNftItem').autocomplete({
                    source: jsonProd,
                    delay: 0,
                    create: function() {
                        $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                            return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
                        };
                    },
                    select: function(event, ui){
                        gravaproduto(ui.item.value, inidnfitem);
                    }
                });
            }
        });
    }

    function mudarMoeda(vthis) {
        moeda = $(vthis).val();
        $('.valorMoeda').css("display", (moeda == 'BRL') ? "none" : "block");
    }

    function formatarStringDecimal(string) {
        if(string != undefined) {
            if(string > 0 || string.length > 0) {
                let stringFormatada = string.toString().replace(/,/g, '.');
                return resultMoeda = parseFloat(stringFormatada.replace(/\.(?=.*\.)/g, ''));
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    function inserirValorMoeda(){ 
        let moedanova = $('.moedanova').val();
        let valorUnitarioMoeda = $('.valorUnitarioMoeda').val();
        var objetomoeda = '';
        var resultMoeda = formatarStringDecimal(valorUnitarioMoeda);

        if(resultMoeda > 0) {
            let moedainternacional = (moedanova == 'USDNACIONAL' || moedanova == 'EURNACIONAL') ? 'N' : 'Y';
            $('.idnfitem').each(function(index, elem){
                let name = $(elem).attr('nomecampo');
                let variavel = name.replace('idnfitem','');
                let id = $(elem).attr('value');
                
                var valconv = ($(`.nfitem_vlritemext_${id}`).val() == null) ? 0 : formatarStringDecimal($(`.nfitem_vlritemext_${id}`).val());
                var nvalor = valconv * resultMoeda;
                $(`#${id}`).val(nvalor.toFixed(4));

                var qtd = formatarStringDecimal($(`.qtd-${id}`).val());
                var total = qtd * nvalor;
                $(`.total-${id}`).val(total.toFixed(2));

                objetomoeda += `&${name}=${id}&${variavel}moeda=BRL&${variavel}moedaext=${moedanova}&${variavel}convmoeda=${valorUnitarioMoeda}&${variavel}moedainternacional=${moedainternacional}&${variavel}vlritem=${nvalor}&${variavel}total=${total}`;
            });

            CB.post({
                objetos: `${objetomoeda}&convMoeda=true&_nf_u_nf_idnf=${idnf}&_nf_u_nf_moedainternacional=${moedainternacional}`,
                parcial: true,
                posPost: function(data, textStatus, jqXHR) {
                    //vthis.innerText = moedanova;
                    //$(vthis).attr('moeda', moedanova);
                }
            });
        } else {
            alert("O valor não pode ser vazio.")
        }
    }

    function criarNfentrada(inidnf, objeto, valor) {
        if (confirm("Gerar esta compra?")) 
        {
            valoritem = valor.toString().replace(/\D/g,"");
            valor = formatarStringDecimal(valoritem);

            CB.post({
                objetos: {
                    _ximp_u_nf_idnf:inidnf,
                    _idobjetosolipor:inidnf,
                    _tipoobjetosolipor: 'nf',
                    _objeto: objeto,
                    _valor: valor,
                    _idcliente: idcliente
                },
                parcial: true
            });
        }
    }

    function atualizarValorCompra(vthis, inidnf, objeto) 
    {
        var valor = $(vthis).val().replace(",", ".");
        CB.post({
            objetos: `_ximp_u_nf_idnf=${inidnf}&_ximp_u_nf_${objeto}=${valor}`,
            parcial: true
        });
    }

    function atualizaproporcao(vthis, vidnfconfpagar) 
    {
        var valor = 0;
        $(":input[name*=nfconfpagar_proporcao]").each(function() {
            var string1 = $(this).val();
            var numero = parseFloat(string1.replace(',', '.'));
            valor = valor + numero;
        });

        if (valor > 100) {
            alert("A soma das proporções não deve passar de 100.");
            $(vthis).val('');
        } else {
            CB.post({
                objetos: "_pr_u_nfconfpagar_idnfconfpagar=" + vidnfconfpagar + "&_pr_u_nfconfpagar_proporcao=" + $(vthis).val(),
                parcial: true
            })
        }
    }

    function atualizaparc(vthis) 
    {
        CB.post({
            objetos: "_parc_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_parc_u_nf_parcelas=" + $(vthis).val(),
            parcial: true
        })
    }

    function nfconfpagar(inidnfconfpagar, li) 
    {
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

    function salvanfconfpagar() 
    {
        var strcbpost = "_999_u_nfconfpagar_idnfconfpagar=" + $("[name=_999_u_nfconfpagar_idnfconfpagar]").val() + "&_999_u_nfconfpagar_obs=" + $("[name=_999_u_nfconfpagar_obs]").val();

        console.log(strcbpost);
        CB.post({
            objetos: strcbpost,
            parcial: true,
            msgSalvo: "Salvo",
            posPost: function(resp, status, ajax) 
            {
                if (status = "success") {
                    $("#cbModalCorpo").html("");
                    $('#cbModal').modal('hide');
                } else {
                    alert(resp);
                }
            }
        });
    }

    function multiplicarnf(vidnf) 
    {
        CB.post({
            objetos: `_x_u_nf_idnf=${$("[name=_1_u_nf_idnf]").val()}&qtdvezes=${$("[name=qtdvezes]").val()}&intervalo=${$("[name=intervalo]").val()}&tipointervalo=${$("[name=tipointervalo]").val()}&multiplicar=Y`,
            parcial: true
        })
    }

    function atualizafinalidade(vthis) 
    {
        CB.post({
            objetos: "_atf_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_atf_u_nf_idfinalidadeprodserv=" + $(vthis).val(),
            parcial: true,
            posPost: function(resp, status, ajax) {
                gerainfsped();             
            }
        });
    }

    function atnfitemxml(vthis, inidnfitemxml, coluna,consumo) 
	{ 
        fnidpessoa = $(vthis).attr('fnidpessoa');
        cprodforn = $(vthis).attr('cprodforn');
        if(consumo!='Y'){
            $('.nfitemxml_idprodserv').each(function(i, o) {
                if($(o).attr('cprodforn') != cprodforn) {
                    $(o).find(`[value="${$(vthis).val()}"]`).remove();
                }
            });  
        }

        CB.post({
            objetos: `_atx_u_nfitemxml_idnfitemxml=${inidnfitemxml}&_atx_u_nfitemxml_${coluna}=${$(vthis).val()}&fncprodforn=${cprodforn}&fnidpessoa=${fnidpessoa}`,
            parcial: true,
            refresh: false
        });
    }

    function gerainfsped() 
    {
        var prazo = $("[name=_1_u_nf_prazo]").val();
        if (!prazo && (gIdEmpresa != 4)) {
            alert('Para atualizar as informações preencher a data de entrada.');
        } else {

            var idnotafiscal = $("#idnf").val();
            vurl = "inc/php/gerainfsped.php?idnf=" + idnotafiscal;

            $.ajax({
                type: "get",
                url: vurl,
                success: function(data) {
                    gerainfspedfiscal()
                },
                error: function(objxmlreq) {
                    alert('Erro:\n' + objxmlreq.status);
                }
            }) //$.ajax
        }
    }

    function gerainfspedfiscal() 
    {
        var idnotafiscal = $("#idnf").val();
        vurl = "inc/php/gerainfspedfiscal.php?idnf=" + idnotafiscal;

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

    function devolvernf() 
	{
        $.ajax({
            type: "get",
            url: "ajax/htmldevolvenf.php",
            data: {
                idnf: $("[name=_1_u_nf_idnf]").val()
            },
            success: function(data) {
                CB.modal({
                    titulo: "</strong>Devolver Nota <button type='button' class='btn btn-danger btn-xs' onclick='salvardevolucao();'><i class='fa fa-circle'></i>Salvar</button></strong>",
                    corpo: data,
                    classe: 'sessenta'
                });
            },
            error: function(objxmlreq) {
                alert('Erro:<br>' + objxmlreq.status);
            }
        }) //$.ajax	
    }

    function salvardevolucao() 
    {
        var vstr = '';
        var virg = '';
        $("#itensdev").find('input:checked').each(function(i, o) {
            vstr += virg;
            vstr += $(o).attr("idnfitemxml");
            virg = ',';
        });

        console.log(vstr);
        if ($("[name=_dev_i_nf_idnatop]").val() == '' || vstr == '') {
            alert("É necessário marcar um dos itens e selecionar a natureza da operação.");
        } else {

            var strcbpost = "nf_idcontato=" + $("[name=_dev_i_nf_idcontato]").val() + "&nf_idnatop=" + $("[name=_dev_i_nf_idnatop]").val() + "&idnfitemxml=" + vstr + "&_1_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val();
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

    function getnfe() 
	{
        vurl = "../inc/nfe/sefaz4/func/getnfe.php?idnotafiscal="+idnf;
        if (confirm("Baixar o XML da NF?")) 
		{
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

    function salvachavecte(vthis){       
       CB.post({
            objetos: `_1_u_nf_idnf=${idnf}&_1_u_nf_idnfe=${$(vthis).val()}`,
            parcial: true,
            posPost: function() {
            vinculacte();
            }
        });
    }

    function vinculacte() 
	{
        var idnotafiscal = $("#idnf").val();
        vurl = "ajax/vinculacte.php?idnf=" + idnotafiscal;

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

    function gerainfcte() 
	{
        var idnotafiscal = $("#idnf").val();
        vurl = "inc/php/gerainfcte.php?idnf=" + idnotafiscal;

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

    //Exclui o XML, caso seja de outra Nota Fiscal - Lidiane (19/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=327401
    function excluirxml(idnf) 
	{
        if (confirm("Deseja realmente excluir o XML?")) 
		{
            CB.post({
                objetos: '_retxml_u_nf_idnf=' + idnf + '&_retxml_u_nf_xmlret=&_retxml_u_nf_envionfe=PENDENTE',
                parcial: true
            });
        }
    }

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
                            <?=fillselect(NfEntradaController::$_justificativaPrazo)?>
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

    function atualizapendencia(inidnfpendencia, vthis, campo) 
    {
        CB.post({
            objetos: `_x_u_nfpendencia_idnfpendencia=` + inidnfpendencia + `&_x_u_nfpendencia_` + campo + `=` + $(vthis).val(),
            parcial: true
        });
    }

    function conferenciaok(inidnfpendencia) 
    {
        var today = new Date();
        var date = today.getDate() + '-' + (today.getMonth() + 1) + '-' + today.getFullYear();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        var dateTime = date + ' ' + time;
        CB.post({
            objetos: `_x_u_nfpendencia_idnfpendencia=` + inidnfpendencia + `&_x_u_nfpendencia_status=RESOLVIDO&_x_u_nfpendencia_datatratativa=${dateTime}`,
            parcial: true
        });
    }

    function atualizanfconferenciaitem(vthis, inid) 
    {
        CB.post({
            objetos: `_x_u_nfconferenciaitem_idnfconferenciaitem=` + inid + `&_x_u_nfconferenciaitem_resultado=` + $(vthis).val(),
            parcial: true,
            refresh: false
        })
    }

    function alteraoutros(vthis) 
    {
        valor = $(vthis).val();
        if (valor == 'OUTROS') {
            $(vthis).parent().append('<input style="margin-top:4px;" id="justificaticaText" name="_h1_i_modulohistorico_justificativa" value="" class="size50" type="text" placeholder="Digite aqui a sua justificativa" vnulo/>');
            $('.justificativa').attr('name', '');
        } else {
            $('#justificaticaText').remove();
            $('.justificativa').attr('name', '_h1_i_modulohistorico_justificativa');
        }
    }
    
    function listarItensCompra(tipoLista){
        var ListagemTodosItens = (tipoLista == 'cadastrado') ? itensCadastrados : itensNaoCadastrados;
        var $vsubtotal = 0;
        var i = 0;
        var duplicarNf = '';
        var mostrarUnidade = '';
        var solcom = '';
        var valtotaldesconto = 0;
        var vtotalicms = 0;
        var vtotalipi = 0;            
        var $vnaocobrar = '';
        var $ncmOld = '';
        var optionContaItemAtivo = '';
        var $tqtdprod = 0;
        var convmoeda = '';
        var gridApi; // Define the variable to store the grid API
        var gridColumnApi; // Define the variable to store the column API
        let somarCadastrado = 0;
        let somarNaoCadastrado = 0;
        let desNaoCadastrado = 0,
                valipiTotalCadastrado = 0,
                valipiTotalNaoCadastrado = 0,
                cofinsTotalCadastrado = 0,
                cofinsTotalNaoCadastrado = 0,
                valicmsTotalCadastrado = 0,
                valicmsTotalNaoCadastrado = 0,
                impostoimportacaoTotalCadastrado = 0,
                impostoimportacaoTotalNaoCadastrado = 0,
                pisTotalCadastrado = 0,
                pisTotalNaoCadastrado = 0,
                vstTotalCadastrado = 0,
                vstTotalNaoCadastrado = 0,
                basecalc = 0;

        if((qtdProdutosCadastrados > 0 && status == 'CONCLUIDO' && tipoLista == 'cadastrado') || (qtdProdutosSemCadastro > 0 && status == 'CONCLUIDO' && tipoLista == 'naocadastrado') || (status != 'CONCLUIDO')) {
            
            //Esconde a coluna NFe
            const shouldHideTipoNfRtEdSoColumn = (rowData) => {
                if (!Array.isArray(rowData)) {
                    return true;
                }

                return rowData.some(item => (tiponf === 'R' || tiponf == 'T' || tiponf == 'E' || tiponf == 'D' || tiponf == 'S' || tiponf == 'O'));
            }

            //Esconde a coluna Qtdsol
            const shouldHideTipoNfRtEdColumn = (rowData) => {
                if (!Array.isArray(rowData)) {
                    return true;
                }

                return rowData.some(item => (tiponf == 'R' || tiponf == 'T' || tiponf == 'E' || tiponf == 'D'));
            }

            //Esconde a coluna UN
            const shouldHideTipoNfTeDrColumn = (rowData) => {
                if (!Array.isArray(rowData)) {
                    return true;
                }

                return rowData.some(item => (tiponf == 'T' || tiponf == 'E' || tiponf == 'D' || tiponf == 'R'));
            }

            //Esconde a coluna Tx Cv e Valor Un $
            const shouldHideTipoNfConversaoMoedaColumn = (rowData) => {
                if (!Array.isArray(rowData)) {
                    return true;
                }

                return rowData.some(item => (qtdConversaoMoeda == 0) || (tipoLista == 'cadastrado' && qtdProdutosCadastrados == 0) || (tipoLista == 'naocadastrado' && qtdProdutosSemCadastro == 0));
            }

            const shouldHideTipoNfConversaoMoedaColumnMoeda = (rowData) => {
                if (!Array.isArray(rowData)) {
                    return true;
                }

                return rowData.some(item => (qtdConversaoMoeda == 0) || (tipoLista == 'cadastrado' && qtdProdutosCadastrados == 0) || (tipoLista == 'naocadastrado' && qtdProdutosSemCadastro == 0));
            }

            const shouldHideTipoNfConversaoMoedaColumnMoedaExt = (rowData) => {
                if (!Array.isArray(rowData)) {
                    return true;
                }

                return rowData.some(item => (qtdConversaoMoeda == 0) || (tipoLista == 'cadastrado' && qtdProdutosCadastrados == 0) || (tipoLista == 'naocadastrado' && qtdProdutosSemCadastro == 0));
            }

            //Esconde a coluna Lote e Tag
            const shouldHideTipoNfRtEdNaoCadstradoColumn = (rowData) => {
                if (!Array.isArray(rowData)) {
                    return true;
                }

                return rowData.some(item => (tiponf == 'R' || tiponf == 'T' || tiponf == 'E' || tiponf == 'D' || tipoLista == 'naocadastrado'));
            }                                           
            
            
            class CustomHeaderMoeda {
                init(params) {
                    this.eGui = document.createElement('div');
                    if(internacional == false) {
                        this.eGui.innerHTML = `<div class="customHeader">
                                                Valor Un 
                                                <a id="cblogomenu" href="javascript:alterarMoeda();" style="padding-left: 7px;">
                                                <span class="fa fa-money preto"></span>
                                            </div>`;
                    } else {
                        this.eGui.innerHTML = `Valor Un`;
                    }
                }
                getGui() {
                    return this.eGui;
                }
            }

            class CustomHeaderMoedaInternacional {
                init(params) {
                    this.eGui = document.createElement('div');
                    if(internacional == true) {
                        this.eGui.innerHTML = `<div class="customHeader">
                                                ${params.displayName}
                                                <a id="cblogomenu" href="javascript:alterarMoeda();">
                                                <img src="./inc/img/dots_three_circle.png" style="width: 15px;"></a>
                                            </div>`;
                    } else {
                        this.eGui.innerHTML = `Valor Un`;
                    }
                }
                getGui() {
                    return this.eGui;
                }
            }

            const columnDefs = [
                { 
                    field: 'cobrar', //0
                    headerName: "Cobrar",
                    width: 50,
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            let checked = (params.data.cobrar == 'Y') ? 'checked' : '';
                            let disablednamedfec = (status == 'CONCLUIDO') ? 'disabled' : '';
                            let vchecked = (params.data.cobrar == 'Y') ? 'N' : 'Y';

                            if(params.data.cobrar == 'Y') {
                                if (params.data.moeda == "BRL") {
                                    $vsubtotal = $vsubtotal + params.data.total + ((qtdConversaoMoedaInternacional > 0) ? 0 : params.data.valipi);
                                    $moeda = params.data.moeda;
                                } else {
                                    $vsubtotal = $vsubtotal + params.data.totalext;
                                    $moeda = params.data.moeda;
                                }
                            }else{
                                if (params.data.moeda == "BRL") {
                                    $vnaocobrar = $vnaocobrar + params.data.total + ((qtdConversaoMoedaInternacional > 0) ? 0 : params.data.valipi);
                                    
                                } else {
                                    $vnaocobrar = $vnaocobrar + params.data.totalext;
                                }
                            }
                            
                            return `<input title="Ao desmarcar este, o valor do mesmo, não é contabilizado na fatura." type="checkbox" ${checked} ${disablednamedfec} name="namenfec" onclick="altcheck('nfitem', 'cobrar', ${params.data.idnfitem}, '${vchecked}')">`;
                        }
                    }
                }, 
                { 
                    field: 'qtdsol', //1 cp_qtdsol
                    headerName: "Qtd Sol",
                    width: 120,
                    hide: shouldHideTipoNfRtEdColumn(ListagemTodosItens),
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            var compraOrigem = '';
                            idnfitem = params.data.idnfitem;
                            Origem = $nfOrigem[idnfitem];

                            if(tipoLista == 'cadastrado'){
                                if(params.data.tipoobjetoitem == 'nfitem' && params.data.idobjetoitem != null){
                                    if(Origem != null){
                                        compraOrigem = `<a title="Compra Origem - ${Origem['idnf']}" class="fa fa-bars fade pointer hoverazul" href="?_modulo=nfentrada&_acao=u&idnf=${Origem['idnf']}" target="_blank" style="padding-left: 5px;"></a>`;
                                    }                                    
                                }
                                
                                valor = ((params.data.qtdsol == null) ? '' : formatarMoeda(params.data.qtdsol, 4)) + compraOrigem;
                            } else {
                                valor = '';
                            }
                            
                            return valor;
                        } else {
                            return '';
                        }
                    }
                },  
                { 
                    field: 'qtd', //2 cp_qtd
                    headerName: "Qtd",
                    width: 100,
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            var duplicarNf = '';
                            var classColorirVermelho = '';
                            if(parseFloat(params.data.qtd) < parseFloat(params.data.qtdsol) && params.data.qtdsol != null){
                                classColorirVermelho = 'colorirVermelho';
                                duplicarNf = `<div class="col-md-2 alinhamento-topo">`;
                                if(listarObjetoOrigem.qtdLinhas < 1){
                                    duplicarNf += `<a class="fa fa-plus-circle verde hoververde  pointer " title="Duplicar Compra" onclick="duplicarcompra(${idnf})"></a>
                                                </div>`;
                                }

                                if(listarObjetoOrigem.qtdLinhas >= 1){
                                    duplicarNf += `<a title="Complemento Criado - ${listarObjetoOrigem.dados.idnf}" class="fa fa-bars fade pointer hoverazul" href="?_modulo=nfentrada&_acao=u&idnf=${listarObjetoOrigem.dados.idnf}" target="_blank"></a>`;
                                }
                                duplicarNf += `</div>`;
                            } 

                            htlmQtd = `<div class="col-md-12 padding-zero h-100 d-flex justify-between align-items-center" style="gap: .5rem;">
                                            <div class="col-md-9 padding-zero h-100">                                                
                                                <input ${readonly} class="idnfitem alterar-${params.data.idnfitem+10}" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_idnfitem" size="8" type="hidden" value="${params.data.idnfitem}">
                                                <input class="alterar-${params.data.idnfitem+10}" nomeCampo="_${params.data.idnfitem+10}_qtd_old" type="hidden" value="${params.data.qtd}">
                                                <!-- Se a quantidade solicitada for diferente da quantidade, o texto será exibido em vermelho -->
                                                <input ${readonly} class="${classColorirVermelho} h-100 alinhar-direita qtd-${params.data.idnfitem} alterar-${params.data.idnfitem+10} nfitem_qtd" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_qtd" type="text" value="${formatarMoeda(params.data.qtd, 4)}" vdecimal onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" onblur="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)">
                                            </div>
                                            ${duplicarNf}
                                        </div>`;                           

                            return htlmQtd;
                        } else {
                            var valorCampo = params.value.valorInput;  // Supondo que você tenha 'valorCampo' nos dados

                            var inputElement = document.createElement('input');
                            inputElement.setAttribute('style', 'border: 1px solid silver;');
                            inputElement.setAttribute('name', `_${valorCampo}#quantidade`);
                            inputElement.setAttribute('tipocampo', 'quantidade');
                            inputElement.setAttribute('valorcampo', `_${valorCampo}`);
                            inputElement.setAttribute('campo', `_${valorCampo}-quantidade`);
                            inputElement.setAttribute('id', `qtd-${valorCampo}`);
                            inputElement.setAttribute('title', 'Qtd');
                            inputElement.setAttribute('placeholder', 'Qtd');
                            inputElement.setAttribute('class', `size5 guardar-${valorCampo} alterarNovosCampos a_${valorCampo}-quantidade`);

                            // Adiciona evento de blur          
                            tipoCampo =  (tipoLista == 'cadastrado') ? 'idprodserv' : 'prodservdescr';        
                            inputElement.addEventListener('keydown', function() {
                                if (event.key === 'Tab') {
                                    event.preventDefault(); 
                                    moveToNextCell(params, valorCampo, tipoCampo);
                                }
                            });

                            return inputElement;
                        }
                    }
                },
                { 
                    field: 'un', //3 cp_un
                    headerName: "UN",
                    width: 50,
                    hide: shouldHideTipoNfTeDrColumn(ListagemTodosItens),
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            if(params.data.idprodservforn == null && params.data.dprodserv == null && status == 'INICIO'){
                                if(params.data.idprodserv == null && status == 'INICIO'){
                                    optionUnidade = '';
                                    for(let keyVol in unidadeVolume) {
                                        var chekedContaItem = (keyVol == params.data.unidade) ? 'checked' : '';
                                        optionUnidade += `<option value="${keyVol}" ${chekedContaItem}>${unidadeVolume[keyVol]}</option>`;
                                    }

                                    var selectUnidade = `<select nomeCampo="_${params.data.idnfitem+10}_u_nfitem_un" class="alterar-${params.data.idnfitem+10}" style="width: 60px;" onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" onblur="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)">
                                                            <option value=""></option>
                                                            ${optionUnidade}
                                                        </select>`;
                                } else {
                                    var selectUnidade = params.data.unidade;
                                }
                            } else {
                                var selectUnidade = params.data.unidade;
                            }
                            return selectUnidade;
                        }
                    }
                },
                { 
                    field: 'prodservdescr', // 4 descrição do Produto  cp_descricao
                    headerName: "Descrição Item",
                    width: 300,
                    filter: true,
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            var solcom = '';
                            var descricaoItem = '';

                            if(params.data.idprodserv != null){
                                if (params.data.codforn == null || params.data.codforn == '') {
                                    var descricaoItem = `${params.data.descr} - ${params.data.codprodserv}`;
                                } else {
                                    var descricaoItem = params.data.codforn;
                                }

                                if(descricaoItem != null){
                                    descricaoItem = descricaoItem.replace(/'/g, "\\'");
                                }

                                var LinkDescricaoItem = `<a class="pointer" title="${params.data.codprodserv}" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=${params.data.idprodserv}')">
                                                            <small> ${descricaoItem}</small></a>`;
                            } else {
                                var readonlyPessoa = ((params.data.idpessoa != null) || (status == 'CONCLUIDO' && status == 'CANCELADO')) ? "readonly='readonly'" : "";
                                var LinkDescricaoItem = `<input ${readonlyPessoa} class="alterar-${params.data.idnfitem+10} insidprodserv" type="text" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_prodservdescr" value="${params.data.prodservdescr}" onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" onblur="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)">`;
                            }

                            if(dadosSolcom.qtdLinhas != 0){
                                infoSolcom = dadosSolcom.dados[params.data.idprodserv];
                                for(let key in infoSolcom){
                                    solcom += `<label class="idbox" style="margin-left: 5px; margin-right: 5px;">
                                                <a title="Solicitação Compras" class="fade pointer hoverazul" style="padding: 3px;" idprodserv="${params.data.idprodserv}" href="?_modulo=solcom&_acao=u&idsolcom=${key}" target="_blank">
                                                    ${key}
                                                </a>
                                            </label>`;
                                }
                            }
                            return LinkDescricaoItem + solcom;
                        } else {
                            if(params.value.valorInput == 'cadastrado'){
                                return params.value.input;
                            } else { 
                                var valorCampo = params.value.valorInput;
                                var inputElement = document.createElement('input');
                                inputElement.setAttribute('style', 'width: 35em;');
                                inputElement.setAttribute('name', `_${valorCampo}#prodservdescr`);
                                inputElement.setAttribute('tipocampo', 'prodservdescr');
                                inputElement.setAttribute('valorcampo', `_${valorCampo}`);
                                inputElement.setAttribute('campo', `_${valorCampo}-prodservdescr`);
                                inputElement.setAttribute('id', `_${valorCampo}-prodservdescr`);
                                inputElement.setAttribute('title', 'Produto');
                                inputElement.setAttribute('placeholder', 'Selecione o produto');
                                inputElement.setAttribute('type', 'text');
                                inputElement.setAttribute('onchange', `guardarDadosNovos(${valorCampo}, this)`);
                                inputElement.setAttribute('class', `insidprodservnaocadastrado guardar-${valorCampo} alterarNovosCampos a_${valorCampo}-prodservdescr`);

                                // Adiciona evento de blur
                                inputElement.addEventListener('keydown', function() {
                                    if (event.key === 'Tab') {
                                        event.preventDefault(); 
                                        moveToNextCell(params, valorCampo, 'vlritem');
                                    }
                                });

                                return inputElement;
                            }
                        }
                    }
                },
                { 
                    field: 'idcontaitem', // 5 Conta Item cp_grupoes
                    headerName: "C/CS",
                    headerTooltip: "C/CS",
                    width: 50,
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            let arrStatusNf = ['REPROVADO', 'CANCELADO'];
                            var disabledStatus = (arrStatusNf.indexOf(status) >= 0 )  ? 'disabled' : '';
                            var optionContaItemProdserv = '';
                            var optionSubCategoria = '';

                            if(params.data.idprodserv == null){
                                var descricaoItem = params.data.prodservdescr;
                            } else {
                                if (params.data.codforn == null || params.data.codforn == '') {
                                    var descricaoItem = `${params.data.descr} - ${params.data.codprodserv}`;
                                } else {
                                    var descricaoItem = params.data.codforn;
                                }
                            }

                            if(descricaoItem != null){
                                descricaoItem = descricaoItem.replace(/'/g, "\\'");
                            }

                            if(params.data.idprodserv == null){
                                for(let key in fillselectContaItemAtivoArray) {
                                    let fillselectContaItemAtivo = fillselectContaItemAtivoArray[key];
                                    let chekedContaItem = (fillselectContaItemAtivo[0] == params.data.idcontaitem) ? 'selected="selected"' : '';
                                    optionContaItemAtivo += `<option value="${fillselectContaItemAtivo[0]}" ${chekedContaItem}>${fillselectContaItemAtivo[1]}</option>`;
                                }
                                
                                var listarContaItem = `<input type="hidden" nomodal class="alterar-${params.data.idnfitem+10}" id="iidcontaitem${params.data.idnfitem}" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_idcontaitem" value="${params.data.idcontaitem}">
                                                            <select id="idcontaitem${params.data.idnfitem}" name="" style="width: 100%" vnulo ${disabledStatus}>
                                                                <option value=""></option>
                                                                ${optionContaItemAtivo}
                                                            </select>`;
                            } else {
                                contaItemProdservSelected = contaItemProdserv[params.data.idnfitem];
                                for(let key in contaItemProdservSelected) {
                                    let chekedContaItem = (key == params.data.idcontaitem) ? 'selected="selected"' : '';
                                    optionContaItemProdserv += `<option value="${key}" ${chekedContaItem}>${contaItemProdservSelected[key]['contaitem']}</option>`;
                                }
                                
                                var listarContaItem = `<input type="hidden" nomodal id="iidcontaitem${params.data.idnfitem}" class="alterar-${params.data.idnfitem+10}" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_idcontaitem" value="${params.data.idcontaitem}">
                                                            <select id="idcontaitem${params.data.idnfitem}" name="" style="width: 100%" vnulo ${disabledStatus}>
                                                                <option value=""></option>
                                                                ${optionContaItemProdserv}
                                                            </select>`;
                            }
                            
                            htmlSubCategoriaPorNf = buscarSubCategoriaPorNf[params.data.idnfitem];
                            for(let key in htmlSubCategoriaPorNf) {
                                let chekedSubCategoria = (key == params.data.idtipoprodserv) ? 'selected="selected"' : '';
                                optionSubCategoria += `<option value="${key}" ${chekedSubCategoria}>${htmlSubCategoriaPorNf[key]['tipoprodserv']}</option>`;
                            }
                            
                                
                            var cp_grupoes = `<div id="tb_${params.data.idnfitem}" style="display: none;">
                                            <table style="width: 100%">
                                                <tr style="padding: 15px;">
                                                    <th>Categoria</th>
                                                    <th></th>
                                                    <th>SubCategoria</th>
                                                </tr>
                                                <tr>
                                                    <td style="width: 45% !important;" class="cp_grupoes">${listarContaItem}</td>
                                                    <td style="width: 10%;"></td>
                                                    <td style="width: 45%;" id="td${params.data.idnfitem}" class="cp_tipo 1">                                    
                                                        <input type="hidden" nomodal id="iidtipoprodserv${params.data.idnfitem}" class="alterar-${params.data.idnfitem+10}" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_idtipoprodserv" value="${params.data.idtipoprodserv}">
                                                        <select id="idtipoprodserv${params.data.idnfitem}" name="" style="width: 100%" vnulo ${disabledStatus}>
                                                            <option value=""></option>
                                                            ${optionSubCategoria}
                                                        </select>                                       
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>`;

                            if(params.data.idcontaitem == null || params.data.idcontaitem == 0 || params.data.idtipoprodserv == null || params.data.idtipoprodserv == 0) {
                                var mostrarModalGrupo = `<i class="btn fa fa-info-circle laranja" title="Categoria e/ou Subcategoria não Atribuídas" id="btn_${params.data.idnfitem}" onclick="mostrarmodalgt(${params.data.idnfitem}, '${descricaoItem}', ${params.data.idnfitem+10})"></i>`;
                            } else {
                                var mostrarModalGrupo = `<i class="btn fa fa-info-circle" id="btn_${params.data.idnfitem}" onclick="mostrarmodalgt(${params.data.idnfitem},'${descricaoItem}', ${params.data.idnfitem+10})"></i>`;
                            }
                            return mostrarModalGrupo + cp_grupoes;
                        }
                    }
                },
                { 
                    field: 'moeda', // 6 Taxa de Conversão cp_taxaconsersao
                    headerName: "Tx Cv",
                    headerComponent: CustomHeaderMoedaInternacional, 
                    width: 150,
                    hide: shouldHideTipoNfConversaoMoedaColumnMoeda(ListagemTodosItens),
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            var tdMoedaExt = '';
                            var convmoeda = '';
                            if(params.data.moedaext != null && params.data.moedaext != 'BRL'){
                                var tdMoedaExt = `<label class="alert-warning">${params.data.moedaext}</label>`;
                                if(params.data.moeda == 'BRL'){
                                    moeda = (params.data.convmoeda == null) ? '' : params.data.convmoeda;
                                    var convmoeda = `<input vnulo ${readonly} class="alinhar-direita convmoeda-${params.data.idnfitem} alterar-${params.data.idnfitem+10}" style="width: 60px;" title="Câmbio BRL" placeholder="Câmbio" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_convmoeda" type="text" value="${moeda}" onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" onblur="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)">`;
                                }                            
                            } else if(qtdConversaoMoeda > 0) {
                                var tdMoedaExt = '';
                            }
                            return tdMoedaExt + convmoeda;
                        }
                    }
                },
                { 
                    field: 'moedaext', // 7 cp_valorundolar
                    headerName: "Valor Un $",
                    headerComponent: CustomHeaderMoedaInternacional,
                    hide: shouldHideTipoNfConversaoMoedaColumnMoedaExt(ListagemTodosItens), 
                    width: 100,
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            if(params.data.moedaext != null && params.data.moedaext != 'BRL'){
                                var tdMoedaExt = `<input ${readonly} class="alterar-${params.data.idnfitem+10}" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_moedaext" type="hidden" value="${params.data.moedaext}">
                                            <input ${readonly} class="alinhar-direita nfitem_vlritemext_${params.data.idnfitem} alterar-${params.data.idnfitem+10}" style="width: 100%;" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_vlritemext" type="text" value="${params.data.vlritemext}" onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this); setvalnfitem(this, ${params.data.idnfitem});" onblur="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)">`;
                            } else if(qtdConversaoMoeda > 0) {
                                var tdMoedaExt = ``;
                            }

                            return tdMoedaExt;
                        }
                    }
                },
                { 
                    field: 'vlritem', // 8 cp_valorun
                    headerName: `Valor Un`,
                    headerComponent: CustomHeaderMoeda, 
                    width: 100,
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            let $cbt = (params.data.vlritemext > 1) ? (params.data.vlritem < 1 ? "btn-danger" : "btn-primary ") : "btn-success";

                            if (params.data.moeda == "BRL") {
                                vlritem = (params.data.vlritem == null) ? '' : params.data.vlritem;
                                var inputValorItem = `<input type="text" ${readonly} class="alinhar-direita alterar-${params.data.idnfitem+10} nfitem_vlritem" style="width: 100%;" id="${params.data.idnfitem}" onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_vlritem" oldvalue="${vlritem}" value="${formatarMoeda(vlritem, 4)}" onblur="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)">`;
                            } else {
                                vlritemext = (params.data.vlritemext == null) ? '' : params.data.vlritemext;
                                var inputValorItem = `<input ${readonly} class="alterar-${params.data.idnfitem+10}" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_moedaext" type="hidden" value="${params.data.moeda}">
                                                    <input ${readonly} class="alinhar-direita" style="width: 100%;" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_vlritemext" type="text" value="${vlritemext}" onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" onblur="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)">`; 
                            }

                            var htmlValorItemExt = `<button ${readonly} title="Moeda" moeda="${params.data.moeda}" type="button" class="btn ${$cbt} btn-xs pointer" onclick="alteramoeda(this, ${params.data.idnfitem}, '${params.data.moedaext}')">
                                                ${params.data.moeda}
                                            </button>
                                            ${inputValorItem}`;
                            return inputValorItem;    
                        } else if(params.data.vlritem != null) {
                            return params.value.input;
                        }
                    }
                },
                { 
                    field: 'desc', // 9 cp_desc
                    headerName: "Desc Un",
                    width: 100,
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            des = (params.data.des == null) ? '' : params.data.des;

                            return `<input ${readonly} class="alinhar-direita alterar-${params.data.idnfitem+10} size4 nfitem_des" style="width: 100%;" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_des" type="text" value="${formatarMoeda(des, 4)}" vdecimal onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" onblur="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)">`;
                        } else if(params.data.desc != null) {
                            return params.value.input;
                        }
                    } 
                },
                { 
                    field: 'cp_total_imposto', // 10 cp_total_imposto cp_cfop
                    headerName: "Imposto",
                    headerTooltip: "Imposto", 
                    hide: shouldHideTipoNfRtEdSoColumn(ListagemTodosItens) || !internacional,                    
                    width: 50,
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            botaoClick = `onclick="inserirImpostoImportacao(${params.data.idnfitem}, ${params.data.idnfitem+10})"`;

                            inputsHTML = `<input type="hidden" id="aliqipi${params.data.idnfitem}" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_aliqipi" value="${params.data.aliqipi}" class="alterar-${params.data.idnfitem+10}" onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" onblur="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)">
                                            <a idnfitem="${params.data.idnfitem}" indice="${params.data.idnfitem+10}" ${botaoClick}>                                                                    
                                                <img src="../inc/img/lionicon.png" style="width: 15px; height: 15px;">
                                            </a>`;

                            return inputsHTML; 
                        }
                    }
                },
                {
                    field: 'basecalc', 
                    headerName: "BC",
                    hide: internacional,
                    width: 100,
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {                            
                            basecalc = 0.0;

                            if (!params.data.basecalc && params.data.total) {
                                basecalc = params.data.total.toLocaleString('en-US', {minimumFractionDigits: 2});
                            } 

                            return `<input title="BC\nNCM: ${params.data.ncm}" id="#imp_basecalc" nomeCampo="_${params.data.idnfitem + 10}_u_nfitem_basecalc" class="size10 alterar-${params.data.idnfitem+10}" value="${basecalc}" type="text" placeholder="Digitar o BC" style="pointer-events: none;" onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" onblur="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" />`;
                        }
                    }
                },
                {
                    field: 'aliqicms', 
                    headerName: "ICMS %",
                    hide: internacional,
                    width: 100,
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {                            
                            let aliqicms = (!params.data.aliqicms) ? '0.00' : params.data.aliqicms;

                            return `<input id="#imp_aliqicms" nomeCampo="_${params.data.idnfitem + 10}_u_nfitem_aliqicms" class="size10 imposto alterar-${params.data.idnfitem+10}" value="${aliqicms}" type="text" placeholder="ICMS %" title="ICMS % \nNCM: ${params.data.ncm}" onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" onblur="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)"/>`;
                        }
                    }
                },
                {
                    field: 'valicms', 
                    headerName: "ICMS R$",
                    hide: internacional,
                    width: 100,
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {                            
                            let valicms = (!params.data.valicms) ? '0.00' : params.data.valicms;;

                            return `<input id="#imp_valicms" nomeCampo="_${params.data.idnfitem + 10}_u_nfitem_valicms" class="size10 imposto alterar-${params.data.idnfitem+10}" value="${valicms}" type="text" placeholder="ICMS R$" title="ICMS R$ \nNCM: ${params.data.ncm}" onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" onblur="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)"/>`;
                        }
                    }
                },
                {
                    field: 'vst', 
                    headerName: "ICMS ST",
                    hide: internacional,
                    width: 100,
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {                            
                            let vst = (!params.data.vst) ? '0.00' : params.data.vst;

                            return `<input id=#imp_vst" nomeCampo="_${params.data.idnfitem + 10}_u_nfitem_vst" class="size10 imposto alterar-${params.data.idnfitem+10}" value="${vst}" type="text" placeholder="ICMS ST" title="ICMS ST \nNCM: ${params.data.ncm}" onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" onblur="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)">`;
                        }
                    }
                },
                {
                    field: 'aliqipi', 
                    headerName: "IPI %",
                    hide: internacional,
                    width: 100,
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {                            
                            let aliqipi = (!params.data.aliqipi) ? '0.00' : params.data.aliqipi;

                            return `<input id="#imp_aliqipi" nomeCampo="_${params.data.idnfitem + 10}_u_nfitem_aliqipi" value="${aliqipi}" onchange="alterarValorHiddenIpi(this, ${params.data.idnfitem})" onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" onblur="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" class="size10 imposto alterar-${params.data.idnfitem+10}" type="text" placeholder="IPI %" title="IPI % \nNCM: ${params.data.ncm}" />`;
                        }
                    }
                },
                { 
                    field: 'total', // 11 cp_valor
                    headerName: "Valor Total",
                    width: 100,
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            if (params.data.moeda == "BRL") {
                                var inputValorItem = `<input ${readonly}  class="alinhar-direita alterar-${params.data.idnfitem+10}" style="width: 100%;" id="${params.data.idnfitem}" onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" onblur="analisaval(this); salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_vlritem" oldvalue="${params.data.vlritem}" type="text" value="${formatarMoeda(params.data.vlritem, 4)}">`;
                                $valorTotalUnidade = (internacional && moedainternacional == 'Y') ? parseFloat(params.data.total) : parseFloat(params.data.total) + parseFloat(params.data.valipi);
                                var inputTotal = `<input ${readonly} class="alinhar-direita total-${params.data.idnfitem} alterar-${params.data.idnfitem+10}" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_total" onkeyup="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)" class="size5" type="text" value="${formatarMoeda($valorTotalUnidade.toFixed(2), 2)}" vdecimal onblur="salvarDadosAlterados(${params.data.idnfitem}, ${params.data.idnfitem+10}, this)">`;
                            } else {
                                var inputValorItem = `<input ${readonly} class="alterar-${params.data.idnfitem+10}" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_moedaext" type="hidden" value="${params.data.moeda}">
                                                    <input ${readonly} class="alinhar-direita alterar-${params.data.idnfitem+10}" style="width: 100%;" nomeCampo="_${params.data.idnfitem+10}_u_nfitem_vlritemext" type="text" value="${formatarMoeda(params.data.vlritemext, 4)}" onchange="saveDadosAltearados(${params.data.idnfitem+10}, this)">`; 
                                        
                                var inputTotal = params.data.totalext;
                            }

                            return inputTotal;
                        }
                    }
                },
                { 
                    field: 'idlote', // 12 cp_lote
                    headerName: "Lote",
                    width: 50,
                    hide: shouldHideTipoNfRtEdNaoCadstradoColumn(ListagemTodosItens),
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            var trLote = '';
                            var tr_lote = '';
                            var divLote = '';
                            var idfnitemOld = '';
                            var $tqtdprod = 0;
                            var descricaoItem = '';
                            var $figuranovo = 'fa-plus-circle verde';
                            let larguraTabela = '';

                            if(tiponf != 'R'){
                                $figuranovo = 'fa-plus-circle verde';
                                let loteNfItem = listarUnidadeObjetoItem[params.data.idnfitem];
                                
                                if(typeof(loteNfItem) != 'undefined') {
                                    if(Object.keys(loteNfItem).length > 0){
                                        trLote += `<tr>
                                                        <th>QTD</th>
                                                        <th>UN</th>
                                                        <th>Descrição</th>
                                                        <th>Status</th>
                                                    </tr>`;
                                    }
                                }

                                for(let keyUnidade in loteNfItem){                
                                    $figuranovo = 'fa-bars cinza';
                                    unidadeObjetoItem = loteNfItem[keyUnidade];
                                    if (unidadeObjetoItem.status != 'CANCELADO') {
                                        $tqtdprod = $tqtdprod + parseFloat(unidadeObjetoItem.qtdprod);
                                        cor = '';
                                    } else {
                                        cor = 'background-color: #dcdcdc;opacity: 0.5;';
                                    }
                                    
                                    trLote += `<tr style="${cor}" title="${unidadeObjetoItem.status}">
                                                    <td>${unidadeObjetoItem.qtdprod}</td>
                                                    <td>${unidadeObjetoItem.unlote}</td>
                                                    <td>
                                                        <a class=" hoverazul pointer" onclick="janelamodal('?_modulo=${unidadeObjetoItem.idobjeto}&_acao=u&idlote=${unidadeObjetoItem.idlote}');">
                                                            ${unidadeObjetoItem.partida}-${unidadeObjetoItem.exercicio}
                                                        </a>
                                                    </td>
                                                    <td>${unidadeObjetoItem.rotulo}</td>
                                                </tr>`;

                                    var linkLote = `<a class="fa fa-bars cinza fa-x  btn-lg poiter" onclick="janelamodal('?_modulo=${unidadeObjetoItem.idobjeto}&_acao=u&idlote=${params.data.idlote2}');" title="${params.data.partida2}/${params.data.exercicio2}"></a>;`;
                                }

                                if (parseFloat(params.data.qtd) > $tqtdprod && (status != 'CANCELADO' && status != 'REPROVADO') ) {
                                    unidade = (params.data.unidade == null) ? '' : params.data.unidade;
                                    larguraTabela = 'Y';
                                    tr_lote = `<tr>
                                                    <th>QTD</th>
                                                    <th>UN</th>
                                                    <th>Ação</th>
                                                </tr>
                                                <tr class="tr_lote_${params.data.idnfitem}">
                                                    <td class="nowrap">
                                                        <input name="#lote_idnfitem" value="${params.data.idnfitem}" type="hidden">
                                                        <input title="Qtd do lote a ser criado" class='quantidade${params.data.idnfitem} size7' name="#lote_qtdprod" value="" type="text" class="size10">
                                                        <input name="#lote_idprodserv" value="${params.data.idprodserv}" type="hidden">
                                                        <input name="#lote_exercicio" value="<?=date("Y") ?>" type="hidden">
                                                    </td>
                                                    <td>${unidade}</td>
                                                    <td><i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer" onclick="excluitmp(this)" title="Excluir"></i></td>
                                                </tr>
                                                <tr>
                                                    <td colspan='3'>
                                                        <a class="fa fa-plus-circle fa-x verde btn-lg pointer" onclick="novalinha(this, '${params.data.idnfitem}')" title="Adicionar mais um Lote"></a>
                                                    </td>
                                                </tr>`;
                                }

                                divLote = `<div id="lote_${params.data.idnfitem}" style="display: none">
                                                    <table class="table table-hover planilha grade" ${larguraTabela}>
                                                        ${trLote}
                                                        <tr><td colspan='3'></td></tr>
                                                        ${tr_lote}
                                                    </table>                            
                                            </div>`;                    
                            } 

                            if (params.data.tipo = "PRODUTO") // produto de venda de entrar no mesmo lote 
                            { 
                                if (params.data.idprodserv != null) 
                                {
                                    //ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
                                    if (params.data.idlote2 != null) 
                                    {
                                        $lote = listarLote[params.data.idnfitem];
                                        linkLote = `<a class="fa fa-bars cinza fa-x  btn-lg pointer" onclick="janelamodal('?_modulo=${$lote[params.data.idlote2].idobjeto}&_acao=u&idlote=${params.data.idlote2}');" title="${params.data.partida2}/${params.data.exercicio2}"></a>`;
                                    } else if(params.data.qtd > 0 && params.data.cobrar == 'Y') {
                                        unidade = (params.data.unidade == null) ? '' : params.data.unidade;

                                        if (params.data.codforn == null || params.data.codforn == '') {
                                            $descricaolt = params.data.descr + " - " + params.data.codprodserv;
                                            $descricaolt = $descricaolt.replace("'", '');
                                        } else {
                                            $descricaolt = params.data.codforn.replace("'", '');                                            
                                        }

                                        var descricaoSegura = $descricaolt.replace(/['"]/g, "");

                                        var linkLote = `<a class="fa fa-x ${$figuranovo} btn-lg pointer" onclick="gerarlotem(${params.data.idnfitem}, '${descricaoSegura}', '${params.data.qtd}', '${unidade}', '${$tqtdprod}', '${larguraTabela}')" title="Novo Lote"></a>`;
                                    } else {
                                        var linkLote = "-";
                                    }
                                } else {
                                    var linkLote = `<a class="fa fa-pencil fa-1x btn-lg cinzaclaro hoverazul pointer" title="Relacionar Produto" onclick="relacionaprodserv(${params.data.idnfitem});"></a>`;
                                }
                            } else {
                                var linkLote = `<span>-</span>`;
                            }

                            valipi = (params.data.valipi == null) ? '' : params.data.valipi.toLocaleString('en-US', {minimumFractionDigits: 2});
                            pis = (params.data.pis == null) ? '' : params.data.pis.toLocaleString('en-US', {minimumFractionDigits: 2});
                            cofins = (params.data.cofins == null) ? '' : params.data.cofins.toLocaleString('en-US', {minimumFractionDigits: 2});
                            ncm = (params.data.ncm == null) ? '' : params.data.ncm.toLocaleString('en-US', {minimumFractionDigits: 2});
                            impostoimportacao = (params.data.impostoimportacao == null) ? '' : params.data.impostoimportacao.toLocaleString('en-US', {minimumFractionDigits: 2});
                            aliqicms = (params.data.aliqicms == null) ? '' : params.data.aliqicms.toLocaleString('en-US', {minimumFractionDigits: 2});
                            valicms = (params.data.valicms == null) ? '' : params.data.valicms.toLocaleString('en-US', {minimumFractionDigits: 2});
                            //vst = (params.data.vst == null) ? '' : params.data.vst.toLocaleString('en-US', {minimumFractionDigits: 2});
                            aliqipi = (params.data.aliqipi == null) ? '' : params.data.aliqipi.toLocaleString('en-US', {minimumFractionDigits: 2});
                            
                            if ((params.data.basecalc == null || params.data.basecalc == 0) && params.data.total) {
                                basecalc = params.data.total.toLocaleString('en-US', {minimumFractionDigits: 2});
                            } 

                            if(params.data.idprodserv != null){
                                if (params.data.codforn == null || params.data.codforn == '') {
                                    var descricaoItem = `${params.data.descr} - ${params.data.codprodserv}`;
                                } else {
                                    var descricaoItem = params.data.codforn;
                                }

                                if(descricaoItem != null){
                                    descricaoItem = descricaoItem.replace(/'/g, "\\'");
                                }
                                
                            } else {
                                descricaoItem = params.data.prodservdescr;
                            }

                            vst = (params.data.vst == null) ? '0.00' : params.data.vst;
                            aliqicms = (params.data.aliqicms == null) ? '0.00' : params.data.aliqicms;
                            valicms = (params.data.valicms == null) ? '0.00' : params.data.valicms;
                            aliqipi = (params.data.aliqipi == null) ? '0.00' : params.data.aliqipi;
                            var divImposto = `<div id="imposto_importacao_${params.data.idnfitem}" style="display: none" class="panel-body">
                                            <table class="table table-hover planilha grade">                            
                                                <tr>
                                                    <td><b>Imposto do Item:</b></td>
                                                    <td>
                                                        <a href="/?_modulo=prodserv&_acao=u&_idempresa=${params.data.idempresa}&idprodserv=${params.data.idprodserv}" target="_blank">${descricaoItem}</a>
                                                        <input id="#imp_idnfitem" value="${params.data.idnfitem}" type="hidden">
                                                    </td>                                                                
                                                </tr>                         
                                                <tr>
                                                    <td><b>NCM:</b></td>
                                                    <td><div class="div_ncm">${ncm}</div></td>
                                                </tr>     
                                                <tr>
                                                    <td colspan="2">
                                                        <table class="table planilha" style="border: 2px solid #ddd;">
                                                            <thead>
                                                                <tr>
                                                                    <th class="col-xs-2 alinhar-centro">Impostos</th>
                                                                    <th class="alinhar-centro">Valor</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="alinhar-centro">II</td>
                                                                    <td class="alinhar-centro"><input id="#imp_impostoimportacao" class="size10 alinhar-direita" value="${impostoimportacao}" type="text"></td>
                                                                </tr>
                                                                <tr class="bc-cinza">
                                                                    <td class="alinhar-centro">IPI</td>
                                                                    <td class="alinhar-centro"><input id="#imp_valipi" class="size10 alinhar-direita" value="${valipi}" type="text"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="alinhar-centro">PIS</td>
                                                                    <td class="alinhar-centro"><input id="#imp_pis" class="size10 alinhar-direita" value="${pis}" type="text"></td>
                                                                </tr>
                                                                <tr class="bc-cinza">
                                                                    <td class="alinhar-centro">COFINS</td>
                                                                    <td class="alinhar-centro"><input id="#imp_cofins" class="size10 alinhar-direita" value="${cofins}" type="text"></td>
                                                                </tr>
                                                            </tbody>                                        
                                                        </table>
                                                    </td>
                                                </tr>                      
                                            </table>
                                        </div>`;

                            idfnitemOld = params.data.idnfitem;

                            return divLote + linkLote + divImposto;
                        }
                    }
                },
                { 
                    field: 'tag', // 13 cp_tag
                    headerName: "Tag",
                    width: 50,
                    hide: shouldHideTipoNfRtEdNaoCadstradoColumn(ListagemTodosItens),
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            if(tiponf != 'R')
                            {    
                                // @513502 - TAGS COM NOMENCLATURA INCORRETA NA NF
                                // adicionado join com empresa para obtenção da sigla     
                                htmlTag = '';
                                htmtr = '';
                                idnfitem = params.data.idnfitem;
                                tagsItem = listaTagEmpresa.dados[idnfitem];

                                if (tagsItem == undefined && tagNf.idtagclass != 3) 
                                {
                                    if (params.data.idprodservforn == null && params.data.valconv == null && params.data.converteest == "Y") {
                                        $qtdtag = Math.round(params.data.qtd * params.data.valconv);
                                    } else {
                                        $qtdtag = Math.round(params.data.qtd);
                                    }

                                    descr = (params.data.descr == null) ? '' : params.data.descr.replace("/'|\"/", '');
                                    
                                    var htmlTag = `<a class="fa fa-plus-circle fa-x verde btn-lg pointer" onclick="criaTags(${$qtdtag }, '${descr}', ${params.data.idnfitem}, 'nfitem');" title="Nova Tag" id="maistag"></a>`;
                                } else if(tagNf.idtagclass == 3){ 
                                    var htmlTag = `<div style="text-align: center;">-</div>`;
                                } else if(tagsItem != undefined) {
                                    
                                    for(let key in tagsItem){
                                        $_tag = tagsItem[key];
                                        htmtr += `<tr>
                                                        <td>
                                                            <label class="alert-warning">${$_tag.sigla}-${$_tag.tag}</label>
                                                        </td>
                                                        <td>
                                                            <span class="input-group-addon pointer hoverazul" onclick="janelamodal('?_modulo=tag&_acao=u&idtag=${$_tag.idtag}')" title="Abrir Tag">
                                                                <i class="fa fa-bars pointer"></i>
                                                            </span>
                                                        </td>`;
                                    }

                                    htmlTag = `<a class="fa fa-x fa-bars cinza btn-lg pointer" onclick="mostrarTag(${params.data.idnfitem}, '${params.data.descr}')" title="Tag"></a>
                                        <div id="tag_${params.data.idnfitem}" style="display: none">
                                                    <table class="table table-hover planilha grade">
                                                        <tr>
                                                            <th>Tag</th>
                                                            <th>Link</th>
                                                        </tr>
                                                        ${htmtr}
                                                    </table>                            
                                            </div>`; 
                                }
                            }  else {
                                htmlTag = '';
                            }

                            return htmlTag;
                        }
                    }
                },
                { 
                    field: 'idobjetoitem', // 14 cp_vincular_tag
                    headerName: "Vc Tag",
                    headerTooltip: "Vincular Tag",
                    width: 50,
                    hide: shouldHideTipoNfRtEdNaoCadstradoColumn(ListagemTodosItens),
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            var htmlTagClasse = '';
                            var htmlVinculoTag = '';
                            
                            if(tipoLista == 'cadastrado') {
                                var htmlManutencao = '';
                                var htmlTagsDisponiveis = '';
                                let arrStatusNf = ['REPROVADO', 'CANCELADO'];
                                var disabledStatus = (arrStatusNf.indexOf(status) >= 0 )  ? 'disabled' : '';
                                var $corbotao = (tagNf.idobjeto == null) ? 'fa-plus-circle verde' : 'fa-bars cinza';

                                for(let keyTag in tagClasses){
                                    htmlTagClasse += `<option value="${keyTag}">${tagClasses[keyTag]}</option>`;
                                }

                                for(let keyManutencao in _manutencao){
                                    htmlManutencao += `<option value="${keyManutencao}">${_manutencao[keyManutencao]}</option>`;
                                }

                                idobjetoext = (tagNf.idobjetoext == null) ? '' : tagNf.idobjetoext;
                                for(let keyTag in tagsDisponinveis){
                                    tag = tagsDisponinveis[keyTag];
                                    tagSelected = (tag.idtag == idobjetoext) ? 'selected="selected"' : '';
                                    
                                    htmlTagsDisponiveis += `<option value='${tag.idtag}' ${tagSelected} idtagclass='${tag.idtagclass}'>${tag.descricao}</option>`;
                                }
                                
                                kmrodados = (tagNf.kmrodados == null) ? '' : tagNf.kmrodados;
                                idobjeto = (tagNf.idobjeto == null) ? '' : tagNf.idobjeto;
                                idnfitemacao = (tagNf.idnfitemacao == null) ? '' : tagNf.idnfitemacao;

                                htmlVinculoTag = `<div class="d-flex align-items-center justify-content-center">
                                            <a class="fa fa-x ${$corbotao} btn-lg pointer" onclick="vincularTags(${params.data.idnfitem});" title="Vincular Tag" id="vincularTag"></a>                    
                                            <div id="vinculotag_${params.data.idnfitem}" style="display: none">
                                                <div class="col-xs-12">
                                                    <div class="row m-0 d-flex align-items-right date-options">                            
                                                        <div class="w-100"> 
                                                            <label for="" class="mb-1">Tipo Tag</label>
                                                            <div class="col-xs-12 d-flex px-0">
                                                                <select id="#tag_idtagclass" onchange="mostrarTagsPorTagClass('${params.data.idnfitem}', this)">
                                                                    ${htmlTagClasse}
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12">
                                                    <div class="row m-0 d-flex align-items-right date-options">                            
                                                        <div class="w-100"> 
                                                            <label for="" class="mb-1">Selectionar Tag</label>
                                                            <div class="col-xs-12 d-flex px-0">
                                                                <input id="#tag_idnfitem" type="hidden" value="${params.data.idnfitem}">
                                                                <input id="idtag_${params.data.idnfitem}_old" type="hidden" value="${idobjeto}">
                                                                <input id="#tag_idnfitemacao" type="hidden" value="${idnfitemacao}">
                                                                <select id="#tag_idtag" vnulo ${disabledStatus}>
                                                                    <option value=""></option>
                                                                    ${htmlTagsDisponiveis}
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 #div_categoria">
                                                    <div class="row m-0 d-flex align-items-right date-options">                            
                                                        <div class="w-100"> 
                                                            <label for="" class="mb-1">Categoria</label>
                                                            <div class="col-xs-12 d-flex px-0">
                                                                <select id="#tag_categoria" style="width: 100%" vnulo ${disabledStatus}>
                                                                    <option value=""></option>
                                                                    ${htmlManutencao}
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 #div_km">
                                                    <div class="row m-0 d-flex align-items-right date-options" style="padding-bottom: 25px;">                            
                                                        <div class="w-100"> 
                                                            <label for="" class="mb-1">KM</label>
                                                            <div class="col-xs-12 d-flex px-0">
                                                                <input id="#tag_km" value="${kmrodados}" type="text">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>`;       
                            }

                            return htmlVinculoTag;
                        }
                    }
                },
                { 
                    field: null, // 15 botão excluir / link nfs
                    headerName: "Ação",
                    headerTooltip: "Ação",
                    width: 50,
                    cellRenderer: params => {
                        if (!params.data.qtd?.newrow) {
                            var link = '';
                            if(params.data.tipoobjetoitem == 'nf' && params.data.idobjetoitem != null){                                 
                                if(params.data.tiponfobji == 'V')
                                    modulonf = "pedido";
                                else if(jQuery.inArray(params.data.tiponfobji, ['C', 'S', 'T', 'E', 'M', 'B', 'F']))
                                    modulonf = "nfentrada";
                                else if(params.data.tiponfobji ==  'R')
                                    modulonf = "comprasrh";
                                else if(params.data.tiponfobji ==  'D')
                                    modulonf = "comprassocios";

                                link = `<a class="fa fa-bars pointer hoverazul" title="Nf" onclick="janelamodal('?_modulo=${modulonf}&_acao=u&idnf=${params.data.idobjetoitem}')"></a>`;
                            } else if(params.data.tipoobjetoitem == 'notafiscal' && params.data.idobjetoitem != null){
                                link = `<a class="fa fa-bars pointer hoverazul" title="Nf" onclick="janelamodal('?_modulo=nfs&_acao=u&idnotafiscal=${params.data.idobjetoitem}')"></a>`;
                            } else {
                                if ((status == "INICIO" || status == "PREVISAO") && params.data.idlote == null && params.data.idlote2 == null) {
                                    link = `<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" onclick="excluir(${params.data.idnfitem})" Title="Excluir!"></i>`; 
                                } else {
                                    link = '-';
                                }
                            }

                            return link;
                        }
                    }
                }
            ];

            const localeText = {
                // Traduções comuns
                page: "Página",
                more: "Mais",
                to: "para",
                of: "de",
                next: "Próximo",
                last: "Último",
                first: "Primeiro",
                previous: "Anterior",
                loadingOoo: "Carregando...",
                selectAll: "Selecionar Tudo",
                searchOoo: "Procurar...",
                blanks: "Em branco",
                filterOoo: "Filtrando...",
                applyFilter: "Aplicar Filtro",
                equals: "Igual",
                notEqual: "Diferente",
                lessThan: "Menor que",
                greaterThan: "Maior que",
                lessThanOrEqual: "Menor ou igual a",
                greaterThanOrEqual: "Maior ou igual a",
                inRange: "No intervalo",
                contains: "Contém",
                notContains: "Não contém",
                startsWith: "Começa com",
                endsWith: "Termina com",
                // Traduções para a parte de paginação
                pageNext: "Próxima Página",
                pageLast: "Última Página",
                pageFirst: "Primeira Página",
                pagePrevious: "Página Anterior",
                pageSizeSelectorLabel: "Quantidade de Páginas:",
                ariaPageSizeSelectorLabel: "Quantidade de Páginas:",
                // Traduções para a parte de agrupamento
                group: "Grupo",
                groupColumns: "Colunas de Grupo",
                groupColumnsEmptyMessage: "Arraste aqui para agrupar",
                // Adicione mais traduções conforme necessário
                noRowsToShow: "Sem linhas para mostrar"
            };

            function moveToNextCell(params, valorCampo, campo) {
                const nextInput = document.getElementsByClassName(`a_${valorCampo}-${campo}`);
                if (nextInput) {
                    $(nextInput).focus()
                    $(nextInput).click();
                }
            }

            if(tipoLista == 'cadastrado'){
                const gridOptionsCadastrado = {
                    pagination: true,
                    paginationPageSize: getSavedPageSize(),
                    paginationPageSizeSelector: [50, 100, 200, 500, 1000, 1500, 2000],
                    onPaginationChanged: onPaginationChanged,
                    rowData: ListagemTodosItens,
                    getRowId: params => { 
                        return params.data.idnfitem; // Classe padrão para os outros
                    },
                    domLayout: 'autoHeight',
                    localeText: localeText,

                    onGridReady: function(params) {
                        gridApiCadastrado = params.api; // Assign the API to the variable

                        clearTimeout(debounceCadastrado);
                        debounceCadastrado = setTimeout(() => {
                            calculateSum(tipoLista);
                        }, 30);       
                    },
                    columnDefs: columnDefs,
                    defaultColDef: {
                        editable: false,
                    }
                }
                new agGrid.createGrid(document.querySelector('#tbItensCadastrado'), gridOptionsCadastrado);             
            }
            
            if(tipoLista == 'naocadastrado'){
                const gridOptionsNaoCadastrado = {
                    pagination: true,
                    paginationPageSize: getSavedPageSize(),
                    suppressMovable:true,
                    paginationPageSizeSelector: [50, 100, 200, 500, 1000, 1500, 2000],
                    onPaginationChanged: onPaginationChanged,
                    rowData: ListagemTodosItens,
                    domLayout: 'autoHeight',
                    localeText: localeText,
                    onGridReady: function(params) {
                        console.log("Grid is ready");
                        gridApiNaoCadastrado = params.api; // Assign the API to the variable 

                        clearTimeout(debounceNaoCadastrado);
                        debounceNaoCadastrado = setTimeout(() => {
                            calculateSum(tipoLista);
                        }, 30);                  
                    },
                    columnDefs: columnDefs,
                    defaultColDef: {
                        editable: false,
                    }
                }                
                new agGrid.createGrid(document.querySelector('#tbItensNaoCadastrados'), gridOptionsNaoCadastrado); 
            }
            
            function addRowCadastrado() {
                if (gridApiCadastrado) {
                    contadorCadastrado = $('.addNovaLinhaCadastroContador').val();
                    const newRow = {
                        qtd: {
                            input: "<input style=' border: 1px solid silver;' name='_"+contadorCadastrado+"#quantidade' id='insprodservqtd' campo='_"+contadorCadastrado+"-quantidade' title='Qtd' placeholder='Qtd' type='text' class='size5 guardar-"+contadorCadastrado+" alterarNovosCampos a_"+contadorCadastrado+"-quantidade' onblur='guardarDadosNovos("+contadorCadastrado+", this)'>",
                            newrow: true,
                            valorInput: contadorCadastrado
                        },
                        prodservdescr: {
                            input: "<input type='text' style='width: 35em;' name='_"+contadorCadastrado+"#idprodserv' id='insidprodserv' class='insidprodserv guardar-"+contadorCadastrado+" alterarNovosCampos a_"+contadorCadastrado+"-idprodserv' campo='_"+contadorCadastrado+"-idprodserv' placeholder='Selecione o produto' onblur='guardarDadosNovos("+contadorCadastrado+", this)'>",
                            newrow: true,
                            valorInput: 'cadastrado'
                        },
                        cp_total_imposto: {
                            input: "",
                            newrow: true
                        }
                        // Adicione outros campos aqui conforme necessário
                    }
                    
                    $('.addNovaLinhaCadastroContador').val(parseInt(contadorCadastrado) + 1);

                    gridApiCadastrado.applyTransaction({add: [newRow]});
                    gridApiCadastrado.redrawRows();

                    setTimeout(function() {
                        preencherCamposInsert();
                        $('.inputsAlterar').find('input').each(function(index, element) {                            
                            if($(element).attr('class').indexOf('idprodserv') >= 0){
                                $(`.a_${$(element).attr('indice')}-idprodserv`).attr('cbvalue', $(element).attr('cbvalue'));
                            }
                        });
                    }, 100);
                } else {
                    console.error("Grid API Cadastrado não está disponível");
                } 
            }

            function addRowNaoCadastrado() {
                contadorNaoCadastrado = $('.addNovaLinhaNaoCadastroContador').val();
                const newRow = {
                    qtd: {
                        input: "<input style='border: 1px solid silver;' name='_"+contadorNaoCadastrado+"#quantidade' tipocampo='quantidade' valorcampo='_"+contadorNaoCadastrado+"' campo='_"+contadorNaoCadastrado+"-quantidade' id='insprodservqtd' title='Qtd' placeholder='Qtd' type='text' class='size5 guardar-"+contadorNaoCadastrado+" alterarNovosCampos a_"+contadorNaoCadastrado+"-quantidade' onchange='guardarDadosNovos("+contadorNaoCadastrado+", this)'>",
                        newrow: true,
                        valorInput: contadorNaoCadastrado
                    },
                    prodservdescr: {
                        input: "<input type='text' style='width: 35em;' class='insidprodservnaocadastrado guardar-"+contadorNaoCadastrado+" alterarNovosCampos a_"+contadorNaoCadastrado+"-prodservdescr' name='_"+contadorNaoCadastrado+"#prodservdescr' id='_"+contadorNaoCadastrado+"-prodservdescr' campo='_"+contadorNaoCadastrado+"-prodservdescr' placeholder='Selecione o produto' onchange='guardarDadosNovos("+contadorNaoCadastrado+", this)' >",
                        newrow: true,
                        valorInput: contadorNaoCadastrado
                    },
                    vlritem: {
                        input: "<input name='_"+contadorNaoCadastrado+"#vlritem' id='_"+contadorNaoCadastrado+"-vlritem' campo='_"+contadorNaoCadastrado+"-vlritem' class='size8 guardar-"+contadorNaoCadastrado+" alterarNovosCampos a_"+contadorNaoCadastrado+"-vlritem' type='text' placeholder='Informe o Valor Unitário' onchange='guardarDadosNovos("+contadorNaoCadastrado+", this)'>",
                        newrow: true
                    },
                    des: {
                        input: "<input name='_"+contadorNaoCadastrado+"#des' campo='_"+contadorNaoCadastrado+"-des' class='guardar-"+contadorNaoCadastrado+" alterarNovosCampos a_"+contadorNaoCadastrado+"-des' type='text' placeholder='Informe o Desconto' onchange='guardarDadosNovos("+contadorNaoCadastrado+", this)'>",
                        newrow: true
                    },
                    cfop: {
                        input: "<input name='_"+contadorNaoCadastrado+"#cfop' class='guardar-"+contadorNaoCadastrado+" alterarNovosCampos' type='text' placeholder='CFOP' onchange='guardarDadosNovos("+contadorNaoCadastrado+")'>",
                        newrow: true
                    },
                    cp_total_imposto: {
                        input: "",
                        newrow: true
                    } 
                }

                $('.addNovaLinhaNaoCadastroContador').val(parseInt(contadorNaoCadastrado) + 1);

                if (gridApiNaoCadastrado) {
                    gridApiNaoCadastrado.applyTransaction({add: [newRow] });
                    gridApiNaoCadastrado.refreshCells({ force: true });
                } else {
                    console.error("Grid API Não Cadastrado não está disponível");
                }
                
                preencherCamposInsert();
                calculateSum(tipoLista);
            }           

            // Adicionar Nova Linha
            $(".tbItensCadastradosDiv").on('click', '#addRowCadastrado', function(event, a) {
                clearTimeout(debounce);
                debounce = setTimeout(() => {
                    addRowCadastrado('cadastrado');
                }, 300);
            });

            $(".tbItensNaoCadastradosDiv").on('click', '#addRowNaoCadastrado', function(event, a) {
                clearTimeout(debounce);
                debounce = setTimeout(() => {
                    addRowNaoCadastrado('naocadastrado');
                }, 300);
            });

            function calculateSum(tipoLista) {

                if(tipoLista == 'cadastrado') {
                    gridApiCadastrado.forEachNode(node => {
                        subTotal = (node.data.cobrar == 'Y') ? node.data.total : 0;
                        subtotalCadastrado += formatarStringDecimal(subTotal);
                        valipiTotalCadastrado += formatarStringDecimal(node.data.valipi);
                        cofinsTotalCadastrado += formatarStringDecimal(node.data.cofins);
                        impostoimportacaoTotalCadastrado += formatarStringDecimal(node.data.impostoimportacao);
                        pisTotalCadastrado += formatarStringDecimal(node.data.pis);
                    });
                }

                if(tipoLista == 'naocadastrado') {
                    gridApiNaoCadastrado.forEachNode(node => {
                        subTotal2 = (node.data.cobrar == 'Y') ? node.data.total : 0;
                        subtotalNaoCadastrado += formatarStringDecimal(subTotal2);
                        valipiTotalNaoCadastrado += formatarStringDecimal(node.data.valipi);
                        cofinsTotalNaoCadastrado += formatarStringDecimal(node.data.cofins);
                        impostoimportacaoTotalNaoCadastrado += formatarStringDecimal(node.data.impostoimportacao);
                        pisTotalNaoCadastrado += formatarStringDecimal(node.data.pis);
                    });
                }

                if((qtdProdutosCadastrados > 0 && tipoLista == 'cadastrado')){
                    $('#subtotalCadastrado').val(subtotalCadastrado.toFixed(2));
                } else if(qtdProdutosSemCadastro > 0 && tipoLista == 'naocadastrado') {
                    $('#subtotalNaoCadastrado').val(subtotalNaoCadastrado.toFixed(2));                     
                } else if((qtdProdutosCadastrados == 0 && tipoLista == 'cadastrado')){
                    $('#subtotalCadastrado').val(0);                         
                } else if(qtdProdutosSemCadastro == 0 && tipoLista == 'naocadastrado'){
                    $('#subtotalNaoCadastrado').val(0);
                } 
                
                setarValorImpostos(valipiTotalCadastrado, valipiTotalNaoCadastrado, 'nf_valipiTotalCadastrado', 'nf_valipiTotalNaoCadastrado');
                setarValorImpostos(cofinsTotalCadastrado, cofinsTotalNaoCadastrado, 'nf_cofinsTotalCadastrado', 'nf_cofinsTotalNaoCadastrado');
                setarValorImpostos(impostoimportacaoTotalCadastrado, impostoimportacaoTotalNaoCadastrado, 'nf_imposotimportacaoTotalCadastrado', 'nf_imposotimportacaoTotalNaoCadastrado');
                setarValorImpostos(pisTotalCadastrado, pisTotalNaoCadastrado, 'nf_pisTotalCadastrado', 'nf_pisTotalNaoCadastrado');

                totalDesconto = (($('#descCadastrado').val() == null) ? 0 : formatarStringDecimal($('#descCadastrado').val())) + (($('#descNaoCadastrado').val() == null) ? 0 : formatarStringDecimal($('#descNaoCadastrado').val()));
                totalValipiTotalCadastrado = ($('#nf_valipiTotalCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_valipiTotalCadastrado').val());
                totalValipiTotalNaoCadastrado = ($('#nf_valipiTotalNaoCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_valipiTotalNaoCadastrado').val());
                cofinsTotalCadastrado = ($('#nf_cofinsTotalCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_cofinsTotalCadastrado').val());
                cofinsTotalNaoCadastrado = ($('#nf_cofinsTotalNaoCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_cofinsTotalNaoCadastrado').val());
                impostoimportacaoTotalCadastrado = ($('#nf_imposotimportacaoTotalCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_imposotimportacaoTotalCadastrado').val());
                impostoimportacaoTotalNaoCadastrado = ($('#nf_imposotimportacaoTotalNaoCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_imposotimportacaoTotalNaoCadastrado').val());
                pisTotalCadastrado = ($('#nf_pisTotalCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_pisTotalCadastrado').val());
                pisTotalNaoCadastrado = ($('#nf_pisTotalNaoCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_pisTotalNaoCadastrado').val());
                $('.nf_desconto').val(totalDesconto.toFixed(2));
                
                valorIPI = ($('#nf_valipiTotalCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_valipiTotalCadastrado').val());

                // Atualiza o rodapé com os totais
                subtotalCadastrado = ($('#subtotalCadastrado').val() == null || $('#subtotalCadastrado').val() == '') ? 0 : formatarStringDecimal($('#subtotalCadastrado').val());
                subtotalNaoCadastrado = ($('#subtotalNaoCadastrado').val() == null || $('#subtotalNaoCadastrado').val() == '') ? 0 : formatarStringDecimal($('#subtotalNaoCadastrado').val());
                frete = ($("[name=_1_u_nf_frete]").val() == null || $("[name=_1_u_nf_frete]").val() == '') ? 0 : formatarStringDecimal($("[name=_1_u_nf_frete]").val());
                impServ = (imp_serv == null || imp_serv == '') ? 0 : formatarStringDecimal(imp_serv);

                SubTotalTotalGeral = subtotalCadastrado + subtotalNaoCadastrado + valipiTotalNaoCadastrado - totalDesconto;
                totalTotalGeral = subtotalCadastrado + subtotalNaoCadastrado + totalValipiTotalCadastrado + totalValipiTotalNaoCadastrado + cofinsTotalCadastrado + cofinsTotalNaoCadastrado
                                   + frete + impostoimportacaoTotalCadastrado + impostoimportacaoTotalNaoCadastrado
                                   + pisTotalCadastrado + pisTotalNaoCadastrado - impServ - totalDesconto;
                $('#totalSubTotal').html(formatarValorBRL(SubTotalTotalGeral.toFixed(2), ''));
                $('#totalTotal').html(formatarValorBRL(totalTotalGeral.toFixed(2), ''));
                $('.valorTotalImpostoServico').html(formatarValorBRL(totalTotalGeral.toFixed(2), ''));   
                $('.vlrsubtotal').val(SubTotalTotalGeral.toFixed(2)); 
                $('.vlrtotal').val(totalTotalGeral.toFixed(2));     
                $('#pis_servico').attr('onchange', "atualizaTotal('"+totalTotalGeral+"')");
                $('#pis_cofins').attr('onchange', "atualizaTotal('"+totalTotalGeral+"')");
                $('#pis_csll').attr('onchange', "atualizaTotal('"+totalTotalGeral+"')");
                $('#pis_ir').attr('onchange', "atualizaTotal('"+totalTotalGeral+"')");      
                $('#pis_inss').attr('onchange', "atualizaTotal('"+totalTotalGeral+"')");      
                $('#pis_iss').attr('onchange', "atualizaTotal('"+totalTotalGeral+"')");      
                $('#pis_issret').attr('onchange', "atualizaTotal('"+totalTotalGeral+"')");      

                alerta = '';
                totalcpFloat = parseFloat(totalcp);
                if((internacional != null && moedainternacional == 'Y') && (totalTotalGeral.toFixed(2) > totalcpFloat.toFixed(2) || totalTotalGeral.toFixed(2) < totalcpFloat.toFixed(2))){
                    alerta = `<i title="Valor ${formatarValorBRL(totalcpFloat.toFixed(2))} diferente com o da nota ${formatarValorBRL(totalTotalGeral.toFixed(2), '')}" class="fa fa-exclamation-triangle laranja btn-lg pointer"></i>`;
                } else if ((totalTotalGeral.toFixed(2) > totalcpFloat.toFixed(2)) || (totalTotalGeral.toFixed(2) < totalcpFloat.toFixed(2))) {
                    alerta = `<i title="Valor ${formatarValorBRL(totalcpFloat.toFixed(2))} diferente com o da nota ${formatarValorBRL(totalTotalGeral.toFixed(2))}" class="fa fa-exclamation-triangle laranja btn-lg pointer"></i>`;
                }

                $('.valorDiferenteNota').html(alerta);
            }

            // Função para obter o tamanho da página salvo
            function getSavedPageSize() {
                let objJson = CB.jsonModulo.jsonpreferencias;
                let navType = "nav"+tipoLista;
                let tipoJson = `pagesize_${tipoLista}`;
                let QtdLinhas = 50;
                if (objJson[`nfentrada_${_idpk}`] && objJson[`nfentrada_${_idpk}`][navType] && objJson[`nfentrada_${_idpk}`][navType][tipoJson]){
                    QtdLinhas = parseInt(objJson["nfentrada_"+_idpk][navType][tipoJson]);
                }
            
                return QtdLinhas;
            }

            // Função que salva o tamanho da página
            function onPaginationChanged(params) {
                let navType = "nav"+tipoLista;
                let tipoJson = `pagesize_${tipoLista}`;
                const currentPageSize = params.api.paginationGetPageSize();

                CB.setPrefUsuario('m',`{"${CB.modulo}":{"nfentrada_${_idpk}":{"${navType}":{"${tipoJson}":"${currentPageSize}"}}}}`,'');
            }

            function setarValorImpostos(impostoCadastrado, impostoNaoCadastrado, campoCadastrado, CampoNaoCadastrado){
                if((impostoCadastrado > 0 && tipoLista == 'cadastrado')){
                    $(`#${campoCadastrado}`).val(impostoCadastrado.toFixed(4));
                } else if(impostoNaoCadastrado > 0 && tipoLista == 'naocadastrado') {                
                    $(`#${CampoNaoCadastrado}`).val(impostoNaoCadastrado.toFixed(4));                    
                } else if((impostoCadastrado == 0 && tipoLista == 'cadastrado')){                
                    $(`#${campoCadastrado}`).val(0);                    
                } else if(impostoNaoCadastrado == 0 && tipoLista == 'naocadastrado'){
                    $(`#${CampoNaoCadastrado}`).val(0);
                }
            }
        }
    }

    function mostrarTag(inidnfitem, descricaoItem) {
        strCabecalho = `<strong class="col-xs-10" style="text-wrap: nowrap; max-width: 100%; overflow: hidden; text-overflow: ellipsis; margin-top: 12px;">
                            Tag referente ao item ${descricaoItem}
                        </strong>`;

        let objfrm = $($(`#tag_${inidnfitem}`).html());

        CB.modal({
            titulo: strCabecalho,
            corpo: `<table class='table table-hover planilha grade'>${objfrm.html()}</table>`,
            classe: 'quarenta',
        });
    }

    if(CB.acao == 'u') {
        listarItensCompra('cadastrado');
        listarItensCompra('naocadastrado');
    }    

    $(document).keydown(function(event) {
        if (!((event.ctrlKey || event.altKey) && event.keyCode == 86)) return true;        

        setTimeout(function() {
            $(`#${$(event.target).attr('id')}`).blur(); // Remove o foco do campo de entrada
        }, 100);

        if (!teclaLiberada(event)) return; //Evitar repetição do comando abaixo
    });
    
    function salvarDadosAlterados(idnfitem, indice, event){
        
        let novosInputs = '';

        if($(`#_${indice}_u_nfitem_idnfitem`).length == 0){
            novosInputs += `<input name="_${indice}_u_nfitem_idnfitem" id="_${indice}_u_nfitem_idnfitem" novoCampo="_${indice}_u_nfitem_idnfitem" type="hidden" value="${idnfitem}">`;
        }

        campoAlterar = $(event).attr('nomecampo');        

        $(`.alterar-${indice}`).each(function(index, element) {

            if($(`#${$(element).attr('nomeCampo')}`).length == 0){
                novosInputs += `<input name="${$(element).attr('nomeCampo')}" id="${$(element).attr('nomeCampo')}" novoCampo="${$(element).attr('nomeCampo')}" type="hidden" value="${$(element).val()}">`;
            } else {
                if($(`#${$(element).attr('nomeCampo')}`).attr('novoCampo') == campoAlterar){
                    $(`#${$(element).attr('nomeCampo')}`).val($(element).val());
                }
            } 
        }); 

        $('.inputsAlterar').html($('.inputsAlterar').html() + novosInputs);

        qtd = ($(`#_${indice}_u_nfitem_qtd`).val() == null || $(`#_${indice}_u_nfitem_qtd`).val() == '') ? 0 : formatarStringDecimal($(`#_${indice}_u_nfitem_qtd`).val());
        vlritem = ($(`#_${indice}_u_nfitem_vlritem`).val() == null || $(`#_${indice}_u_nfitem_vlritem`).val() == '') ? 0 : formatarStringDecimal($(`#_${indice}_u_nfitem_vlritem`).val());
        des = ($(`#_${indice}_u_nfitem_des`).val() == null || $(`#_${indice}_u_nfitem_des`).val() == '') ? 0 : formatarStringDecimal($(`#_${indice}_u_nfitem_des`).val());
        cfop = ($(`#_${indice}_u_nfitem_cfop`).val() == null || $(`#_${indice}_u_nfitem_cfop`).val() == '') ? 0 : formatarStringDecimal($(`#_${indice}_u_nfitem_cfop`).val());
        aliqicms = ($(`#_${indice}_u_nfitem_aliqicms`).val() == null || $(`#_${indice}_u_nfitem_aliqicms`).val() == '') ? 0 : formatarStringDecimal($(`#_${indice}_u_nfitem_aliqicms`).val());
        aliqipi = ($(`#_${indice}_u_nfitem_aliqipi`).val() == null || $(`#_${indice}_u_nfitem_aliqipi`).val() == '') ? 0 : formatarStringDecimal($(`#_${indice}_u_nfitem_aliqipi`).val());
        //vst = ($(`#_${indice}_u_nfitem_vst`).val() == null || $(`#_${indice}_u_nfitem_vst`).val() == '') ? 0 : formatarStringDecimal($(`#_${indice}_u_nfitem_vst`).val());
        valorTotal = (qtd * vlritem) - des + cfop + aliqicms + aliqipi + vst;
        $(`.total-${idnfitem}`).val(formatarMoeda(valorTotal.toFixed(2), 2));
    }

    function salvarDadosAlteradosRateio(idnfitem, indice, event){
        
        let novosInputsRateio = '';

        if($(`#_${indice}_u_nfitem_idnfitem`).length == 0){
            novosInputsRateio += `<input name="_${indice}_u_nfitem_idnfitem" id="_${indice}_u_nfitem_idnfitem" novoCampo="_${indice}_u_nfitem_idnfitem" type="hidden" value="${idnfitem}">`;
        }

        campoAlterar = $(event).attr('nomecampo');        

        $(`.alterar-${indice}`).each(function(index, element) {

            if($(`#${$(element).attr('nomeCampo')}`).length == 0){
                novosInputsRateio += `<input name="${$(element).attr('nomeCampo')}" id="${$(element).attr('nomeCampo')}" novoCampo="${$(element).attr('nomeCampo')}" type="hidden" value="${$(element).val()}">`;
            } else {
                if($(`#${$(element).attr('nomeCampo')}`).attr('novoCampo') == campoAlterar){
                    $(`#${$(element).attr('nomeCampo')}`).val($(element).val());
                }
            } 
        }); 

        $('.inputsAlterar').html($('.inputsAlterar').html() + novosInputsRateio);
    }

    function guardarDadosNovos(indice, event){
        let novosInputs = '';  
        campoAlterar = $(event).attr('campo');
        tipocampo = $(event).attr('tipocampo');
        valorcampo = $(event).attr('valorcampo');

        $(`.guardar-${indice}`).each(function(index, element) {
            if($(`.${$(element).attr('campo')}`).length == 0){
                attrCbvalue = ($(element).attr('cbvalue') == null) ? '' : `cbvalue="${$(element).attr('cbvalue')}"`;
                attrTitulo = ($(element).attr('title') == null) ? '' : `title="${$(element).attr('title')}"`;
                novosInputs += `<input id="${$(element).attr('id')}" indice="${indice}" class="${$(element).attr('campo')}" ${attrCbvalue} ${attrTitulo} type="hidden" value="${$(element).val()}">`;
            } else {
                if($(`.${$(element).attr('campo')}`).attr('class') == campoAlterar){
                    $(`.${$(element).attr('campo')}`).val($(element).val());

                    if($(element).attr('cbvalue') != null){
                        $(`.${$(element).attr('campo')}`).attr('cbvalue', $(element).attr('cbvalue'));
                    }
                }
            } 
        }); 

        $('.inputsAlterar').html($('.inputsAlterar').html() + novosInputs);
    }
    
    function preencherCamposInsert(){
        $(`.alterarNovosCampos`).each(function(index, element) {
            $(`.a${$(element).attr('campo')}`).val($(`.${$(element).attr('campo')}`).val());
            
            if($(element).attr('cbvalue')) {
                $(`.a${$(element).attr('cbvalue')}`).val($(`.${$(element).attr('cbvalue')}`).val());
            }
        }); 
    }

    // Atualiza o rodapé com os totais
    subtotalCadastrado = ($('#subtotalCadastrado').val() == null || $('#subtotalCadastrado').val() == '') ? 0 : formatarStringDecimal($('#subtotalCadastrado').val());
    subtotalNaoCadastrado = ($('#subtotalNaoCadastrado').val() == null || $('#subtotalNaoCadastrado').val() == '') ? 0 : formatarStringDecimal($('#subtotalNaoCadastrado').val());
    totalDes = ($("[name=nf_desconto]").val() == null || $("[name=nf_desconto]").val() == '') ? 0 : formatarStringDecimal($("[name=nf_desconto]").val());
    frete = ($("[name=_1_u_nf_frete]").val() == null || $("[name=_1_u_nf_frete]").val() == '') ? 0 : formatarStringDecimal($("[name=_1_u_nf_frete]").val());
    impServ = (imp_serv == null || imp_serv == '') ? 0 : formatarStringDecimal(imp_serv);

    totalDesconto = (($('#descCadastrado').val() == null) ? 0 : formatarStringDecimal($('#descCadastrado').val())) + (($('#descNaoCadastrado').val() == null) ? 0 : formatarStringDecimal($('#descNaoCadastrado').val()));
    totalValipiTotalCadastrado = ($('#nf_valipiTotalCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_valipiTotalCadastrado').val());
    totalValipiTotalNaoCadastrado = ($('#nf_valipiTotalNaoCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_valipiTotalNaoCadastrado').val());
    cofinsTotalCadastrado = ($('#nf_cofinsTotalCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_cofinsTotalCadastrado').val());
    cofinsTotalNaoCadastrado = ($('#nf_cofinsTotalNaoCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_cofinsTotalNaoCadastrado').val());
    valicmsTotalCadastrado = ($('#nf_valicmsTotalCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_valicmsTotalCadastrado').val());
    valicmsTotalNaoCadastrado = ($('#nf_valicmsTotalNaoCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_valicmsTotalNaoCadastrado').val());
    impostoimportacaoTotalCadastrado = ($('#nf_imposotimportacaoTotalCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_imposotimportacaoTotalCadastrado').val());
    impostoimportacaoTotalNaoCadastrado = ($('#nf_imposotimportacaoTotalNaoCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_imposotimportacaoTotalNaoCadastrado').val());
    pisTotalCadastrado = ($('#nf_pisTotalCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_pisTotalCadastrado').val());
    pisTotalNaoCadastrado = ($('#nf_pisTotalNaoCadastrado').val() == null) ? 0 : formatarStringDecimal($('#nf_pisTotalNaoCadastrado').val());

    SubTotalTotalGeral = subtotalCadastrado + subtotalNaoCadastrado - totalDes;
    totalTotalGeral = subtotalCadastrado + subtotalNaoCadastrado + totalValipiTotalCadastrado + totalValipiTotalNaoCadastrado + cofinsTotalCadastrado + cofinsTotalNaoCadastrado
                        + valicmsTotalCadastrado + valicmsTotalNaoCadastrado + frete + impostoimportacaoTotalCadastrado + impostoimportacaoTotalNaoCadastrado
                        + pisTotalCadastrado + pisTotalNaoCadastrado - impServ - totalDesconto;
    $('#totalSubTotal').html(formatarValorBRL(SubTotalTotalGeral.toFixed(2), ''));
    $('#totalTotal').html(formatarValorBRL(totalTotalGeral.toFixed(2), ''));
    $('.valorTotalImpostoServico').html(formatarValorBRL(totalTotalGeral.toFixed(2), ''));   
    $('.vlrsubtotal').val(SubTotalTotalGeral.toFixed(2)); 
    $('.vlrtotal').val(totalTotalGeral.toFixed(2));   
    $('#pis_servico').attr('onchange', "atualizaTotal('"+totalTotalGeral+"')");
    $('#pis_cofins').attr('onchange', "atualizaTotal('"+totalTotalGeral+"')");
    $('#pis_csll').attr('onchange', "atualizaTotal('"+totalTotalGeral+"')");
    $('#pis_ir').attr('onchange', "atualizaTotal('"+totalTotalGeral+"')");   
    $('#pis_inss').attr('onchange', "atualizaTotal('"+totalTotalGeral+"')");    
    $('#pis_iss').attr('onchange', "atualizaTotal('"+totalTotalGeral+"')");    
    $('#pis_issret').attr('onchange', "atualizaTotal('"+totalTotalGeral+"')");

    alerta = '';
    totalcpFloat = parseFloat(totalcp);
    if((internacional != null && moedainternacional == 'Y') && (totalTotalGeral.toFixed(2) > totalcpFloat.toFixed(2) || totalTotalGeral.toFixed(2) < totalcpFloat.toFixed(2))){
        alerta = `<i title="Valor ${formatarValorBRL(totalcpFloat.toFixed(2))} diferente com o da nota ${formatarValorBRL(totalTotalGeral.toFixed(2))}" class="fa fa-exclamation-triangle laranja btn-lg pointer"></i>`;
    } else if ((totalTotalGeral.toFixed(2) > totalcpFloat.toFixed(2)) || (totalTotalGeral.toFixed(2) < totalcpFloat.toFixed(2))) {
        alerta = `<i title="Valor ${formatarValorBRL(totalcpFloat.toFixed(2))} diferente com o da nota ${formatarValorBRL(totalTotalGeral.toFixed(2))}" class="fa fa-exclamation-triangle laranja btn-lg pointer"></i>`;
    }

    $('.valorDiferenteNota').html(alerta);
 
    //--------------------------- Habilita e Desabilita Abas Conforme Experiência do Usuário ------------------------------------------
    carregaPreferenciasJson(_idpk, CB.jsonModulo.jsonpreferencias);

    function carregaPreferenciasJson(idpk, json)
    {
        carregandoAbas('show');
        controlaNavTab("tab", idpk, json);
    }

    function carregandoAbas(display = 'show')
    {
        if(display == 'hide'){
            $("#circularProgressIndicator").hide();
            $("#mainPanel").removeClass("disabledbutton");
        }else if(display == 'show'){
            $("#circularProgressIndicator").show();
            $("#mainPanel").addClass("disabledbutton");
        }
    }

    function controlaNavTab(tipo, idpk, json) 
    {
        $.each(CB.oModuloForm.find("[data-toggle="+tipo+"]"),function(i,o) {
            $o = $(o);
            $o.addClass("define");
            let shref = $o.attr("href");
            let khref = shref.substr(1);//Remover a # do id para não gerar erro de path no mysql
            let abaativa = $(shref);

            let objJson = json || CB.jsonModulo.jsonpreferencias;
            let navType = "nav"+tipo;

            if(objJson["nfentrada_"+idpk] != undefined) {
                if(objJson["nfentrada_"+idpk][navType] != undefined) {
                    todosY = Object.values(objJson["nfentrada_"+idpk][navType]).every(function(valor) {
                        return valor === "Y";
                    });

                    if(todosY == true) { objJson["nfentrada_"+idpk][navType] = ''; }
                }
            }

            //Verifica se o elemento com collapse possui alguma preferência de usuário salva
            if (objJson["nfentrada_"+idpk] && objJson["nfentrada_"+idpk][navType] && objJson["nfentrada_"+idpk][navType][khref])
            {
                $oc = $(shref);
                $oc.removeClass("active");
                col = objJson["nfentrada_"+idpk][navType][khref];

                if (col == "N") {
                    $oc.removeClass("active");
                    $o.parent().removeClass("active");
                } else {
                    $oc.addClass("active").addClass('in');
                    $o.parent().addClass("active");
                }
            } 
            
            $o.on('click', function(e){
                $("#circularProgressIndicator").show();

                $this = $(this);//Objeto atual. Geralmente um panel-heading
                let shref = $this.attr("href");
                let khref = shref.substr(1);//Remover a # do id para não gerar erro de path no mysql
                $body = $(shref); 
                
                $('.li_nfentrada').each(function(index, element) {

                    refNentrada = `nfentrada_${idpk}`;
                    let valueListaCotacao = $(element).attr("value");
                    if(valueListaCotacao == khref && CB.jsonModulo.jsonpreferencias[refNentrada] != null)
                    {
                        //Abrir
                        CB.setPrefUsuario('m',`{"${CB.modulo}":{"nfentrada_${idpk}":{"${navType}":{"${khref}":"Y"}}}}`,'',()=>{
                            $body.addClass('active').addClass('in').removeClass('hidden');
                            $body.siblings().removeClass('active').removeClass('in').addClass('hidden');
                            $this.parent().siblings().removeClass('active')
                            $this.parent().addClass('active');                            
                            CB.jsonModulo.jsonpreferencias[refNentrada].navtab[khref] = 'Y';
                        });
                        
                    } else if(CB.jsonModulo.jsonpreferencias[refNentrada] != null) {
                        $oc = $(valueListaCotacao);
                        $oc.removeClass("active");

                        CB.setPrefUsuario('m',`{"${CB.modulo}":{"nfentrada_${idpk}":{"${navType}":{"${valueListaCotacao}":"N"}}}}`, '', ()=>{
                            $bodyInativo = $(shref);
                            $bodyInativo.addClass('active').addClass('in').removeClass('hidden');
                            $bodyInativo.siblings().removeClass('active').removeClass('in').addClass('hidden');
                            $oc.addClass("active").addClass('in');
                            $o.parent().removeClass("active");   
                            refNentrada = `nfentrada_${idpk}`;
                            CB.jsonModulo.jsonpreferencias[refNentrada].navtab[valueListaCotacao] = 'N';
                        });                        
                    } else {
                        var yesno = (valueListaCotacao == khref) ? 'Y' : 'N';
                        CB.setPrefUsuario('m',`{"${CB.modulo}":{"nfentrada_${_idpk}":{"${navType}":{"${valueListaCotacao}":"${yesno}"}}}}`,'');
                    }                    
                });
            });

            $("#circularProgressIndicator").hide();
        });
    }

    CB.on('prePost', () => {
        carregaPreferenciasJson(_idpk, CB.jsonModulo.jsonpreferencias);
    });

    CB.on('posPost', () => {
		carregaPreferenciasJson(_idpk, CB.jsonModulo.jsonpreferencias);
	});
    //--------------------------- Habilita e Desabilita Abas Conforme Experiência do Usuário ------------------------------------------
    //------- Funções Módulo -------
    
    /*Funções para o botão de transferir*/
    function transferir() {
        $.ajax({
            type: "POST",
            url: "ajax/htmltransferirconta.php?valortotal=" + $('#vlrtotal').val(),
            data: {
                valortotal: $('#vlrtotal').val(),
                idformapagamento: $("#forma_pag").attr('cbvalue'),
                notafiscal: $("#nnfe").val(),
                fornecedor:  $('#idpessoa').attr('cbvalue'),
            },

            success: function(data) {
                CB.modal({
                    titulo: "</strong>Transferência <button type='button' class='btn btn-warning btn-xs' onclick='transferirconta();'><i class='fa fa-circle'></i>Confirmar Transferência</button></strong>",
                    corpo: data,
                    classe: 'sessenta',
                    aoAbrir: function(vthis) {
                        $(".calendario").daterangepicker({
                            "singleDatePicker": true,
                            "showDropdowns": true,
                            "linkedCalendars": false,
                            "opens": "left",
                            "locale": {
                                format: 'DD/MM/YYYY'
                            }
                        }).on('apply.daterangepicker', function(ev, picker) {
                            console.log(picker.startDate.format('DD/MM/YYYY hh:mm:ss'));
                            $(this).val(picker.startDate.format('DD/MM/YYYY hh:mm:ss'));
                        });
                    }
                });
            },
        })
    }

    function transferirconta() {
        var dados = $('#dadostranferencia').find(':input').serialize();
        dados += "&_transf_u_nf_idnf=" + $("#idnf").val();
        dados += "&_transf_u_nf_status=" + $("#statusnota").val();
        dados += "&_orig_i_nf_idpessoa=" + $("[name=_orig_i_nf_idpessoa]").val();
        dados += "&_dest_i_nf_idpessoa=" + $("[name=_dest_i_nf_idpessoa]").attr('cbvalue');

        CB.post({
            objetos: dados,
            parcial: true,
            posPost: function(resp, status, ajax) {

                $.ajax({
                    type: "post",
                    url: "ajax/htmlresultadotransferencia.php",
                    data: {
                        idretornoremessa: $("#idnf").val(),
                        local: 'nfentradatransferencia',
                    },

                    success: function(data) {
                        CB.modal({
                            titulo: "</strong>Resultado transferência</strong>",
                            corpo: data,
                            classe: 'sessenta',
                        });
                    },
                })
            }
        })
    }

    function transferido() {
        $.ajax({
            type: "post",
            url: "ajax/htmlresultadotransferencia.php",
            data: {
                idretornoremessa: $("#idnf").val(),
                local: 'nfentradatransferencia',
            },
            success: function(data) {
                CB.modal({
                    titulo: "</strong>Resultado transferência</strong>",
                    corpo: data,
                    classe: 'sessenta',
                });
            },
        })
    }

    function seletorPessoa(value)
	{
		$(".pessoa").filter(function() {
			let seletor = $(this).attr("data-text");
			if(!seletor){
				seletor = $(this).text();
			}
			$(this).toggle(seletor.toLowerCase().normalize("NFD").replace(/[^a-zA-Zs]/, "").indexOf(value) > -1);
		});
	}
  
    $(".mostrarAnexos").webuiPopover({
        trigger: "click",
        placement: "bottom-left",
        width: 500,
        delay: {
            show: 100,
            hide: 0
        }
    });

    function preencherPrazo(valor){
        alterarData = (valor == 'fdata2') ? 'fdata1' : 'fdata2';
        $(`#${alterarData}`).val($(`#${valor}`).val());
    }

    function alterartodosNf(porempresa, _acao) 
	{
		var vqtd = $('#formulario').find("input:checkbox:checked").length;
		if (vqtd > 0) 
		{
			var nobjrat = $('#formulario').find("input:checkbox:not(:checked)");
			nobjrat.each(function(index, value) {
				$(this).parent().parent().remove();
			});

			var inputdest = $('#formulario').find('input.rateioitem').serialize();
			var objvalor = $('.inputsAlterarRateio').find('input.valorrateio');
			var strvalor = '';
			var valor = 0;
			objvalor.each(function(index, value) { 
				if (this.value == '') {
					//remove o <tr>
					$(this).parent().parent().remove();

				} else {
					strvalor = strvalor + "&" + $(this).attr('name') + "=" + $(this).val().replace(',', '.');
					valor = valor + parseFloat(this.value.replace(',', '.'));
				}
			});

			valor = Math.round(valor * 100) / 100
			var objdest = $('.inputsAlterarRateio').find('input.idunidade');
			var strdest = '';
			objdest.each(function(index, value) {
				strdest = strdest + "&" + $(this).attr('name') + "=" + $(this).val();
			});

			var objdestU = $('.inputsAlterarRateio').find('input.idpessoa');
			objdestU.each(function(index, value) {
				strdest = strdest + "&" + $(this).attr('name') + "=" + $(this).val();
			});

			var objdestU = $('.inputsAlterarRateio').find('input.idempresa');
			objdestU.each(function(index, value) {
				strdest = strdest + "&" + $(this).attr('name') + "=" + $(this).val();
			});
            
            inputs = '';
            $('.changeacao').each(function(index, element){
                if($(element).is(":checked") == true && $(element).attr('acao') == 'i'){
                    $(`.rateioitem${$(element).attr('indice')}`).each(function(index, value){
                        inputs += `&_r${$(value).attr('indice')}_i${$(value).attr('campoName')}=${$(value).attr('value')}`;
                    });
                }
            });            

            var strinputfim = `${inputdest}${strdest}${strvalor}&telarateiotodos=Y&porempresa=${porempresa}${inputs}&_1_u_nf_tiponf=${tiponf}&_1_u_nf_idnf=${idnf}`;			
			
			if (valor == 100) {
			
				CB.post({
					objetos: strinputfim,
					parcial: true,
                    refresh: 'refresh'
				});
			} else {
				alert('O valor total da(s) porcentagen(s) deve ser 100%');
			}
			
		} else {
			alert('É necessário selecionar os itens que deseja alterar o rateio');
		}        
	}

    $('.tbItensCadastradosDiv').on('input', '.nfitem_vlritem', alteraCampoMoeda, 4);
    $('.tbItensCadastradosDiv').on('input', '.nfitem_qtd', alteraCampoMoeda, 4);
    $('.tbItensCadastradosDiv').on('input', '.nfitem_des', alteraCampoMoeda, 4);

    function alteraCampoMoeda(e, casasDecimais) {
        let element = e.target;
        let valor = element.value;

        element.value = formatarMoeda(valor, casasDecimais);
    }

    function formatarMoeda(valor, casasDecimais) {
        if(valor !== "" && valor !== null) {
            // Converte o valor para string e remove caracteres indesejados, mantendo apenas números e o sinal de menos
            valor = valor.toString().replace(/[^0-9,-]/g, '').replace(/(?!^)-/g, '');

            // Se o valor for negativo, mantém o sinal e remove para formatar a parte numérica
            let negativo = valor.startsWith('-') ? '-' : '';
            valor = valor.replace('-', '');

            // Separa parte inteira e decimal com base no número de casas decimais desejado
            var parteInteira = valor.slice(0, -casasDecimais) || '0'; // Tudo menos as últimas casas decimais, ou '0' se vazio
            var parteDecimal = valor.slice(-casasDecimais).padEnd(casasDecimais, '0'); // Últimas casas decimais, preenche com zeros

            // Formata a parte inteira com pontos como separadores de milhar
            parteInteira = parteInteira.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

            // Combina parte inteira e decimal com vírgula
            return negativo + parteInteira + (casasDecimais > 0 ? ',' + parteDecimal : '');
        } else {
            return "0,00";
        }
    }

    function alterarValorHiddenIpi(vthis, idnfitem){
        $(`#aliqipi${idnfitem}`).val($(vthis).val());
    }

    function alteraEdicaoDataEntrada() {
        if($('[value="nfentrada_pagamento"].active').length) {
            if($('#fdata2').hasClass('desabilitado')) {
                $('#fdata2').removeAttr('disabled')
                return $('#fdata2').removeClass('desabilitado');
            }

            $('#fdata1').attr('disabled', 'true')
            $('#fdata1').addClass('desabilitado');

            $('#fdata4').attr('disabled', 'true')
            $('#fdata4').addClass('desabilitado');

        } else if($('[value="nfentrada_conferencia"].active').length) {
            if($('#fdata4').hasClass('desabilitado')) {
                $('#fdata4').removeAttr('disabled')
                return $('#fdata4').removeClass('desabilitado');
            }

            $('#fdata1').attr('disabled', 'true')
            $('#fdata1').addClass('desabilitado');

            $('#fdata2').attr('disabled', 'true')
            $('#fdata2').addClass('desabilitado');
            
        } else {
            if($('#fdata1').hasClass('desabilitado')) {
                $('#fdata1').removeAttr('disabled')
                return $('#fdata1').removeClass('desabilitado');
            }

            $('#fdata2').attr('disabled', 'true')
            $('#fdata2').addClass('desabilitado');

            $('#fdata4').attr('disabled', 'true')
            $('#fdata4').addClass('desabilitado');
        }
    }

    $('.atualiza-recebimento').on('apply.daterangepicker', function(ev, picker) {
        const elementJQ = $(this);

        // Valor selecionado no DateRangePicker
        const selectedValue = picker.startDate.format('DD/MM/YYYY');
        console.log(selectedValue);

        // Atualiza os outros campos
        $('#fdata1, #fdata2, #fdata4').val(selectedValue);
    });

    function guardarDadosNovosRateio(indiceRateio, event){
        
        let novosInputsRateio = '';

        campoAlterar = $(event).attr('nomecampo');        

        $(`.alterar-rateio-${indiceRateio}`).each(function(index, element) {

            if($(`#${$(element).attr('nomeCampo').replace('#', '-')}`).length == 0){
                novosInputsRateio += `<input name="${$(element).attr('nomeCampo')}" class="${$(element).attr('tipo')}" id="${$(element).attr('nomeCampo').replace('#', '-')}" novoCampo="${$(element).attr('nomeCampo')}" type="hidden" value="${$(element).val()}">`;
            } else {
                if($(`#${$(element).attr('nomeCampo').replace('#', '-')}`).attr('novoCampo') == campoAlterar){
                    $(`#${$(element).attr('nomeCampo').replace('#', '-')}`).val($(element).val());
                }
            } 
        }); 

        $('.inputsAlterarRateio').html($('.inputsAlterarRateio').html() + novosInputsRateio);
    }

    //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape2
</script>