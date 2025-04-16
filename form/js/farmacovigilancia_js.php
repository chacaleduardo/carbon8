<script>
    //------- Injeção PHP no Jquery -------
    jCli = <?=json_encode($arrayCliente)?> || 0;// autocomplete cliente
    jProdserv = <?=json_encode($arrayProdserv)?> || 0;// autocomplete cliente
    jsonEspecieFinalidade = <?=json_encode($arrayEspecie)?>;
    jsonEndereco = <?=json_encode($arrayEndereco)?> || 0;
    var _idpk = $('[name="_1_u_farmacovigilancia_idfarmacovigilancia"]').val() || getUrlParameter('idfarmacovigilancia') || "";
    var idfarmacovigilancia = '<?=$_1_u_farmacovigilancia_idfarmacovigilancia?>';
    //------- Injeção PHP no Jquery -------

    //------- Funções JS -------
    //------------------ Cliente -----------------------
    jCli = jQuery.map(jCli, function(o, id) {
        return {"label": o.nome, value: id+"", "tipo": o.tipo}
    });

    //autocomplete de clientes
    $("[name*=_farmacovigilancia_idpessoa]").autocomplete({
        source: jCli,
        delay: 0,
        create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $('<li>').append(`<a>${item.label}<span class='cinzaclaro'>${item.tipo}</span></a>`).appendTo(ul);
            }
        }, 
        select: function(event, ui){
            preencherEndereco(ui.item.value);
        }
    });
    //------------------ Cliente -----------------------

    //------------------ Produto -----------------------
    jProdserv = jQuery.map(jProdserv, function(o, id) {
        return {"label": o.descr, value: id+""}
    });

    //autocomplete de clientes
    $("[name*=_farmacovigilancia_idprodserv]").autocomplete({
        source: jProdserv,
        delay: 0,
        create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $('<li>').append(`<a>${item.label}</a>`).appendTo(ul);
            }
        }, 
        select: function(event, ui){
            preencherLote(ui.item.value);
        }
    });
    //------------------ Produto -----------------------

    //------------------ Espécie -----------------------
    jsonEspecieFinalidade = jQuery.map(jsonEspecieFinalidade, function(o, id) {
        return {"label": o.descr, value: id+""}
    });

    //autocomplete de clientes
    $("[name*=_farmacovigilancia_especie]").autocomplete({
        source: jsonEspecieFinalidade,
        delay: 0,
        create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $('<li>').append(`<a>${item.label}</a>`).appendTo(ul);
            }
        }, 
        select: function(event, ui){
            preencherLote(ui.item.value);
        }
    });
    //------------------ Espécie -----------------------

    //------------------ Endereco -----------------------
    jsonEndereco = jQuery.map(jsonEndereco, function(o, id) {
        return {"label": o.descr, value: id+""}
    });

    //autocomplete de clientes
    $("[name*=_farmacovigilancia_idendereco]").autocomplete({
        source: jsonEndereco,
        delay: 0,
        create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $('<li>').append(`<a>${item.label}</a>`).appendTo(ul);
            }
        }, 
        select: function(event, ui){debugger
            preencherLote(ui.item.value);
        }
    });
    //------------------ Endereco -----------------------

    //------- Funções JS -------

    //------- Funções Módulo -------
    function preencherEndereco(vIdPessoa) {
        if (vIdPessoa) {            	
            $.ajax({
                type: "get",
                url: `ajax/buscaendereco.php?idpessoa=${vIdPessoa}&dados=Y`,
                success: function(data) {
                    if(data){
                        dados = JSON.parse(data)
                        $(".enderecocliente").html(`<select name="_1_<?=$_acao?>_farmacovigilancia_idendereco" class="form-control"><option></option>${dados.endereco}</select>`);
                        $(".telefonecliente").html(dados.telefone);
                        $(".emailcliente").html(dados.email);
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

    function preencherLote(vIdProdserv) {
        if (vIdProdserv) {            	
            $.ajax({
                type: "get",
                url: `ajax/lote.php`,
                data: {
                    action: 'burscarLotePorProdserv',
                    params: [vIdProdserv]
                },
                dataType: 'json',
                success: function(data) {
                    let select = '';
                    if(data){
                        dados = data
                        for(var key of Object.keys(dados)) {
                            lote = dados[key];
                            select += `<option value='${key}'>${lote["lote"]}</option>`;
                        }
                        $("[name*=_farmacovigilancia_idlote]").remove();
                        $(".farmacovigilancia_idlote").html(`<select name="_1_<?=$_acao?>_farmacovigilancia_idlote" class="form-control"><option></option>${select}</select>`);
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

    function alteravalor(tipo, event){
        editar = $(event).attr('editar');
        if(tipo == 'evento'){
            if(editar == 'Y'){
                $('.inputEvento').show();
                $('.labelEvento').hide();
                $(event).attr('editar', 'N');
                $('.inputEvento').removeAttr('disabled');
            } else {
                $('.inputEvento').hide();
                $('.labelEvento').show();
                $(event).attr('editar', 'Y');
                $('.inputEvento').attr("disabled", "disabled");
            }        
        }

        if(tipo == 'endereco'){
            if(editar == 'Y'){
                $('.inputEndereco').show();
                $('.labelEndereco').hide();
                $(event).attr('editar', 'N');
                $('.inputEndereco').removeAttr('disabled');
            } else {
                $('.inputEndereco').hide();
                $('.labelEndereco').show();
                $(event).attr('editar', 'Y');
                $('.inputEndereco').attr("disabled", "disabled");
            }
        }

        if(tipo == 'Nf'){
            if(editar == 'Y'){
                $('.inputNf').show();
                $('.labelNf').hide();
                $(event).attr('editar', 'N');
                $('.inputNf').removeAttr('disabled');
            } else {
                $('.inputNf').hide();
                $('.labelNf').show();
                $(event).attr('editar', 'Y');
                $('.inputNf').attr("disabled", "disabled");
            }        
        }
    }

    $('.telefoneinformante').mask('(99) 99999-9999');

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

            //Verifica se o elemento com collapse possui alguma preferência de usuário salva
            if (objJson["farmaco_"+idpk] && objJson["farmaco_"+idpk][navType] && objJson["farmaco_"+idpk][navType][khref])
            {
                $oc = $(shref);
                $oc.removeClass("active");
                col = objJson["farmaco_"+idpk][navType][khref];

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
                
                $('.li_farmaco').each(function(index, element) {
                  
                    let valueListaCotacao = $(element).attr("value");
                    if(valueListaCotacao == khref)
                    {
                        //Abrir
                        CB.setPrefUsuario('m',`{"${CB.modulo}":{"farmaco_${idpk}":{"${navType}":{"${khref}":"Y"}}}}`,'',()=>{
                            $body.addClass('active').addClass('in').removeClass('hidden');
                            $body.siblings().removeClass('active').removeClass('in').addClass('hidden');
                            $this.parent().siblings().removeClass('active')
                            $this.parent().addClass('active');
                            CB.jsonModulo.jsonpreferencias['farmaco_'+idpk].navtab[khref] = 'Y';
                        });
                        
                    } else {
                        $oc = $(valueListaCotacao);
                        $oc.removeClass("active");

                        CB.setPrefUsuario('m',`{"${CB.modulo}":{"farmaco_${idpk}":{"${navType}":{"${valueListaCotacao}":"N"}}}}`, '', ()=>{
                            $bodyInativo = $(shref);
                            $bodyInativo.addClass('active').addClass('in').removeClass('hidden');
                            $bodyInativo.siblings().removeClass('active').removeClass('in').addClass('hidden');
                            $oc.addClass("active").addClass('in');
                            $o.parent().removeClass("active");   
                            CB.jsonModulo.jsonpreferencias['farmaco_'+idpk].navtab[valueListaCotacao] = 'N';
                        });
                        
                    }
                    
                });
            });

            $("#circularProgressIndicator").hide();
        });
    }

    $(".info_farmaco").each(function(index, elemento)
    {
        $(this).webuiPopover({
            trigger: "click",
            placement: "left",
            width: 500,
            delay: {
                show: 300,
                hide: 0
            }
        });
    });

    function alterarFarmaco(vthis){
        campoFarmaco = $(vthis).is(':checked') ? 'Y' : 'N';
        CB.post({
            objetos: `_1_u_farmacovigilancia_idfarmacovigilancia=${idfarmacovigilancia}&_1_u_farmacovigilancia_farmacovigilancia=${campoFarmaco}`,
            parcial: true
        });
    }

    function inserirProdutoFarmacovigilancia(idfarmaco){
        CB.post({
            objetos: `_1_i_produtofarmacovigilancia_idfarmacovigilancia=${idfarmaco}`,
            parcial: true
        });
    }
    
    function salvarCampos(vthis, campo){
        var campoFarmaco = $(vthis).val();
        CB.post({
            objetos: `_1_u_farmacovigilancia_idfarmacovigilancia=${idfarmacovigilancia}&_1_u_farmacovigilancia_${campo}=${campoFarmaco}`,
            parcial: true,
            refresh:false
        });
    }
    //--------------------------- Habilita e Desabilita Abas Conforme Experiência do Usuário ------------------------------------------

    CB.on('prePost',function(data){
        carregaPreferenciasJson(_idpk, CB.jsonModulo.jsonpreferencias);
    });

    $("#cbRestaurar").attr("onclick", "confirmaRestauracao()");

    function confirmaRestauracao(){
        if(confirm("Deseja Restaurar este Formulário?")){ 
            var strPost = `_fv_u_farmacovigilancia_condicaosaude=&_fv_u_farmacovigilancia_reacaoprevia=&_fv_u_farmacovigilancia_qtdanimaisvacinados=`+
						  `&_fv_u_farmacovigilancia_qtdanimaisacometidos=&_fv_u_farmacovigilancia_qtdanimaisrecuperados=&_fv_u_farmacovigilancia_qtdanimaismortos=`+
                          `&_fv_u_farmacovigilancia_ladopescoco=&_fv_u_farmacovigilancia_qtdvomito=&_fv_u_farmacovigilancia_qtdsangramento=`+
                          `&_fv_u_farmacovigilancia_tamagulha=&_fv_u_farmacovigilancia_abcessos=&_fv_u_farmacovigilancia_qtdsequelas=`+
                          `&_fv_u_farmacovigilancia_reacaoadversa=&_fv_u_farmacovigilancia_tratamentoreacaoadversa=&_fv_u_farmacovigilancia_outroproduto=`+
                          `&_fv_u_farmacovigilancia_produto=&_fv_u_farmacovigilancia_administracao=&_fv_u_farmacovigilancia_fabricante=`+
                          `&_fv_u_farmacovigilancia_dosagem=&_fv_u_farmacovigilancia_lote=&_fv_u_farmacovigilancia_aplicacao=`+
                          `&_fv_u_farmacovigilancia_bula=&_fv_u_farmacovigilancia_boaspraticas=&_fv_u_farmacovigilancia_condicoesusoagulha=`+
                          `&_fv_u_farmacovigilancia_descricaodesvio=&_fv_u_farmacovigilancia_associacaorazoavel=&_fv_u_farmacovigilancia_aplicarantidoto=`+
                          `&_fv_u_farmacovigilancia_recorrencia=&_fv_u_farmacovigilancia_ditribuicaosinais=&_fv_u_farmacovigilancia_razoavelanatomico=`+
                          `&_fv_u_farmacovigilancia_farmacologicotoxicologico=&_fv_u_farmacovigilancia_doseefeito=&_fv_u_farmacovigilancia_associacaofarmacotoxi=`+
                          `&_fv_u_farmacovigilancia_exameslaboratoriais=&_fv_u_farmacovigilancia_eventorelatado=&_fv_u_farmacovigilancia_outraexplicacao=`+
                          `&_fv_u_farmacovigilancia_informacaoinsuficiente=&_fv_u_farmacovigilancia_planoacao=`+
                          `&_fv_u_farmacovigilancia_farmacovigilancia=N&_fv_u_farmacovigilancia_idfarmacovigilancia=${idfarmacovigilancia}`+
                          `&_h1_i_modulohistorico_idobjeto=${idfarmacovigilancia}&_h1_i_modulohistorico_tipoobjeto=farmacovigilancia`+
                          `&_h1_i_modulohistorico_campo=formulario&_h1_i_modulohistorico_justificativa=Restauração do Módulo`;
            CB.post({
                objetos: strPost,
                parcial: true,
                refresh: true,
                posPost: function(data, textStatus, jqXHR){
					
					$('.li_farmaco').each(function(index, element) {
                        $this = $(element);//Objeto atual. Geralmente um panel-heading
                        let valueListaCotacao = $(element).attr("value");
                        var shref = $(element).find("href");
                        $body = $(shref);
                        
                        if(valueListaCotacao == khref)
                        {
                            //Abrir
                            CB.setPrefUsuario('m',`{"${CB.modulo}":{"farmaco_${idpk}":{"${navType}":{"${khref}":"Y"}}}}`,'',()=>{
                                $body.addClass('active').addClass('in').removeClass('hidden');
                                $body.siblings().removeClass('active').removeClass('in').addClass('hidden');
                                $this.parent().siblings().removeClass('active')
                                $this.parent().addClass('active');
                                CB.jsonModulo.jsonpreferencias['farmaco_'+idpk].navtab[khref] = 'Y';
                            });
                            
                        } else {
                            $oc = $(valueListaCotacao);
                            $oc.removeClass("active");

                            CB.setPrefUsuario('m',`{"${CB.modulo}":{"farmaco_${idpk}":{"${navType}":{"${valueListaCotacao}":"N"}}}}`, '', ()=>{
                                $bodyInativo = $(shref);
                                $bodyInativo.addClass('active').addClass('in').removeClass('hidden');
                                $bodyInativo.siblings().removeClass('active').removeClass('in').addClass('hidden');
                                $oc.addClass("active").addClass('in');
                                $o.parent().removeClass("active");   
                                CB.jsonModulo.jsonpreferencias['farmaco_'+idpk].navtab[valueListaCotacao] = 'N';
                            });
                            
                        }
                    }); 

                    location.reload();
				}
            });
        } 
    }
    
    //------- Funções Módulo -------

    //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>