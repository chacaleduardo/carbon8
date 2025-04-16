function Status() 
{
    return {
        _eventos: new Map(),
        _modulo: "",
        _primary: "",
        _idobjeto:  "",
        _acao:  "",
        _estado: false,
        _esconderRestaurar: 'N',
        indexPrePost: null,
        inicializaVariaveis: function(){
            ST._modulo = CB.jsonModulo.modulo
            ST._primary = CB.jsonModulo.pk
            ST._idobjeto = getUrlParameter(CB.jsonModulo.pk)
        },
        
        carregarFluxos: function(){

            CB.on('posLoadUrl',function(data){
                //Valida se é i para não aparecer os Fluxos
                if(CB.acao != 'i' && !ST._estado) { 
                    ST._estado = true;
                    ST.inicializaVariaveis();
                    ST.verificarInicio(data);
                } else {
                    ST._estado =  false;
                }
            });

            CB.on('posPost',function(data){
                //Valida se é i para não aparecer os Fluxos
                if(CB.acao != 'i'  && !ST._estado) { 
                    ST._estado = true;
                    ST.inicializaVariaveis();
                    ST.verificarInicio(data);
                } else {
                    ST._estado =  false;
                }
            });                      
        },

        CarregarStatus: function (data)
        {
            var post = {
                _modulo: ST._modulo,
                _primary: ST._primary,
                _idempresa: getUrlParameter('_idempresa'),
                _idobjeto:  ST._idobjeto,
                acao:  'carregarcadastrofluxo'
            };

            if($("#cbSteps").length === 0 && !$("#cbSteps").hasClass("fluxocarregado")){
                if(CB.logado){                        
                    fetch('ajax/_fluxo.php',{
                        method: 'POST',
                        body: Object.keys(post)
                            .map(k => `${encodeURIComponent(k)}=${encodeURIComponent(post[k])}`)
                            .join('&'),
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    })
                    .then(res => { 
                        if(res.headers.get('x-cb-resposta') != "0"){
                            if($("#cbSteps").length === 0 && !$("#cbSteps").hasClass("fluxocarregado")){
                                CB.oModuloForm.prepend(`
                                    <div id="cbSteps" class="row fluxocarregando"><g>Carregando Etapas...</g></div>        
                                `);
                            }                            
                        }
                        
                        ST._estado = false;
                        return res.text();
                        
                    }).then(data => {
                        var botao = "", li = "", onclickacao = "";
                        var obj = JSON.parse(data);
                        var indice = 0;
                        let idfluxostatustab = null,
                            statustipotab = null,
                            permissaoBotaoStatus = false;
                        etapas = obj.etapas;
                        
                        ST.desbloquearCBPost();

                        ST._esconderRestaurar = 'N';
                        if(obj['esconderRestaurar'] == 'Y') {
                            ST._esconderRestaurar = 'Y';
                        }

                        if(ST._idobjeto != undefined || etapas != undefined)
                        {
                            var a = 0;                            
                            fluxo = `<div class="progressbar-wrapper">`;

                            if(etapas != undefined) {
                                fluxo += `<ul id="cbProgressBar" class="progressbar etapa">`;

                                let len = Object.keys(etapas).length;
                                for(let key in etapas) {
                                    let status = etapas[key];
                                    for(let idstatus in status) {
                                        dados = status[idstatus];
                                        if(indice != dados.etapa) {
                                            fluxo += li;

                                            let transparente = (a == 0 || a == len - 1) ? 'sem-cor' : '';

                                            fluxo += `<li id="li_${dados.idetapa}" idfluxostatus="${dados.idfluxostatus}" class="cinza progresso ${transparente}">
                                                <div class="number">
                                                    <div class="numbercolor"><span>${dados.etapa}</span></div>
                                                </div>
                                                <div class="divstatus">${ST.carregaRotulo(dados, dados.statustipo)}
                                            `;
                                            
                                            indice = dados.etapa;
                                            li = "</div></li>";
                                        
                                        } else {
                                            fluxo += ST.carregaRotulo(dados, dados.statustipo);
                                        }

                                        statusAnterior = dados.status;
                                        idfluxostatustab = dados.idfluxostatustab;
                                        statustipotab = dados.statustipotab;
                                    }
                                    a++;
                                }
                                fluxo += `</ul>`;
                            }                              

                            if(ST._idobjeto != undefined) {
                                fluxo += `<div id="cbProgressBarAcoes">
                                    <div id="div_fluxo_botoes">`;
                                botao = obj.botao;
                                if(botao)
                                {
                                    for(var key of Object.keys(botao))
                                    { 
                                        mostrabotao = botao[key];
                                        if ((mostrabotao.botaocriador == 'Y' && mostrabotao.criadopor.toLowerCase() == mostrabotao.usuario.toLowerCase()) 
                                            || (mostrabotao.botaoparticipante == 'Y' && mostrabotao.criadopor.toLowerCase() != mostrabotao.usuario.toLowerCase()) 
                                           // || (mostrabotao.botaoparticipante == 'Y' && mostrabotao.criadopor == mostrabotao.usuario) 
                                            || (mostrabotao.botaocriador != 'Y' && mostrabotao.botaoparticipante != 'Y'))
                                        {                                           
                                            if(obj.permissao.problema != undefined && Array.isArray(obj.permissao.problema)){
                                                rateioproblema =  obj.permissao.problema.includes("RATEIO");
                                            }else{
                                                rateioproblema = false;
                                            }      
                                            
                                            if(obj.permissaoLP != undefined){
                                                if(obj.permissaoLP[parseInt(mostrabotao.idfluxostatusf)] != undefined){
                                                    obj.permissao = obj.permissaoLP[parseInt(mostrabotao.idfluxostatusf)];
                                                }
                                            }

                                            permissaoBotaoStatus = (typeof(obj.permissao.status) == 'object') ? obj.permissao.status?.includes(mostrabotao.statustipo) : mostrabotao.statustipo == obj.permissao.status;
                                                                          
                                            if((permissaoBotaoStatus || rateioproblema || mostrabotao.idfluxostatusf == obj.permissao.idfluxostatusf) 
                                                    && (mostrabotao.statustipo != "CANCELADO" || ST._modulo == 'nfs') && obj.permissao.esconderbotao == 'Y' && (ST._modulo == obj.permissao.modulo ||  ST._modulo.includes(obj.permissao.modulo) )){
                                                let text = '';
                                                if(obj.permissao.problema && Object.keys(obj.permissao.problema).length > 0) {
                                                    for(e in obj.permissao.problema){
                                                        if(obj.permissao.problema[e] == "RATEIO"){
                                                            text +='É necessário informar o rateio para concluir a Compra.\\n';
                                                        }else if(obj.permissao.problema[e] == "DIFERENCA"){
                                                            text +="Há uma diferença entre os item(ns) do Xml e os item(ns) comprado(s) favor corrigir a diferença para concluir a Nota.\\n";
                                                        }else if(obj.permissao.problema[e] == "SIGLA"){
                                                            text +="Existe(m) item(ns) do Xml sem a sigla favor informar a sigla para concluir a Nota.\\n";
                                                        }else if(obj.permissao.problema[e] == "DATAENTRADA"){
                                                            text +="Favor preencher a Data de Recebimento na tela de pagamento, antes de concluir a compra.\\n";
                                                        }  
                                                        
                                                        if(obj.permissao.problema[e] == "COMISSAO"){
                                                            text += "Favor verificar o valor das comissões.\\n";
                                                        } 

                                                        if(obj.permissao.problema[e] == "TOTALNFVALORFAT"){
                                                            text += "O valor do pedido, deve ser o mesmo da soma das faturas, antes da Conclusão do pedido. </br> Salve o pedido novamente antes de Concluir..\\n";
                                                        } 
                                                        
                                                        if(obj.permissao.problema[e] == "TOTALNFVALORFATCOMPRA"){
                                                            text += "O valor da Compra, deve ser o mesmo da soma das faturas, antes da Conclusão da Compra. </br> Salve a Compra novamente antes de Concluir..\\n";
                                                        }
                                                        
                                                        if(obj.permissao.problema[e] == "XMLASSOCIADO"){
                                                            text += "Todos os Itens do XML devem ser associados ao Produto.\\n";
                                                        } 
                                                        if(obj.permissao.problema[e] == "XMLASSOCIADOCTE"){
                                                            text += "E necessário carregar o XML neste CTe.\\n";
                                                        }
                                                        
                                                        if(obj.permissao.problema[e] == "ITEMCOBRANCA"){
                                                            text += "Nota não possui item Faturado, marcar item NFe.\\n";
                                                        } 
                                                        if(obj.permissao.problema[e] == "CFOPNAOCONVERTIDO"){
                                                            text += "CFOP sem conversão,favor cadastrar CFOP de entrada e carregar o xml novamente.\\n";
                                                        }
                                                        
                                                        if(obj.permissao.problema[e] == "GERARPARCELAS"){
                                                            text += "Favor Gerar Parcelas.\\n";
                                                        } 
                                                        
                                                        if(obj.permissao.problema[e] == "GRUPOESTIPOITEM"){
                                                            text += "Existem itens sem Categoria ou Subcategoria preenchidos.\\n";
                                                        } 
                                                        
                                                        if(obj.permissao.problema[e] == "SEMPAGAMENTO"){
                                                            text += "Sem Forma de Pagamento. Favor selecionar.\\n";
                                                        }

                                                        if(obj.permissao.problema[e] == "PENDENCIA"){
                                                            text += "Existe(m) pendência(s) não resolvida(s).\\n";
                                                        }

                                                        if(obj.permissao.problema[e] == "ATUALIZARXML"){
                                                            text += "Favor atualizar os Itens do XML.\\n";
                                                        } 
                                                        
                                                        if(obj.permissao.problema[e] == "DATAENTRADACONTROLENF"){
                                                            text += "Favor preencher Data Entrada no Controles NF.\\n";
                                                        }
                                                        
                                                        if(obj.permissao.problema[e] == "RESULTADO"){
                                                            text += "O colaborador que fechou o resultado não pode conferir o mesmo.\\n";
                                                        } 
                                                        
                                                        if(obj.permissao.problema[e] == "AMOSTRA"){
                                                            text += "O colaborador que criou a amostra não pode conferir a mesma.\\n";
                                                        }

                                                        if(obj.permissao.problema[e] == "DIFERENCAENTREITENS"){
                                                            text += "Há uma diferença entre os item(ns) do Xml e os item(ns) comprado(s) favor corrigir a diferença para concluir a Nota..\\n";
                                                        }

                                                        if(obj.permissao.problema[e] == "ENTRADAPREENCHIDA"){
                                                            text += "Nota não possui item Faturado, marcar item NFe.\\n";
                                                        }

                                                        if(obj.permissao.problema[e] == "FLEGARBOLETO"){
                                                            text += "Não possui Boleto Gerado.\\n";
                                                        }

                                                        if(obj.permissao.problema[e] == "CONTAITEM"){
                                                            text += "Existem itens sem categoria ou tipo preenchidos.\\n";
                                                        }

                                                        if(obj.permissao.problema[e] == "RESULTADOTRA"){
                                                            text += "Para fechar esse resultado é nescessário conferir o TRA.\\n";
                                                        }
                                                        
                                                        if(obj.permissao.problema[e] == "AMOSTRATRA"){
                                                            text += "Você não possui permissão para conferir o TRA.\\n";
                                                        }
                                                        
                                                        if(obj.permissao.problema[e] == "DOCUMENTOREVISAO"){
                                                            text += "Você não pode aprovar o documento revisado por você mesmo.\\n";
                                                        }

                                                        if(obj.permissao.problema[e] == "SOLMATITEMPENDENTE"){
                                                            text += "Item pendente para aprovação na solicitação de materiaiss.\\n";
                                                        }
                                                        
                                                        if(obj.permissao.problema[e] == "NOTANAOCONCLUIDA"){
                                                            text += "A Nota Fiscal deve ser Concluída antes.\\n";
                                                        }

                                                        if(obj.permissao.problema[e] == "ITENSSEMLOTE"){
                                                            text += "A Nota Fiscal possui Itens sem Lote.\\n";
                                                        }

                                                        if(obj.permissao.problema[e] == "ITENSNULOS"){
                                                            text += "Existem Itens com valores Nulos. Favor preenchê-los.\\n";
                                                        }

                                                        if(obj.permissao.problema[e] == "SEMMODULO"){
                                                            text += `Os Itens abaixo não tem módulo configurado. Favor alterar a unidade do produto: ${obj.permissao.listagem[e]}.\\n`;
                                                        }

                                                        if(obj.permissao.problema[e] == "RATEIORH"){
                                                            text += obj.permissao?.nome+" não tem rateio configurado.\\n";
                                                        }

                                                        if(obj.permissao.problema[e] == "SOLICITADONULL"){
                                                            text += "Favor preencher todos os campos do Pagamento\\n";
                                                        }
                                                    }
                                                }

                                                if(ST._modulo == 'nfentrada' &&  text != ''){
                                                    onclickacao =  `alertAtencao('`+text+`')`;
                                                } else if(ST._modulo == 'comprasrh'  &&  text != ''){
                                                    onclickacao =  `alertAtencao('`+text+`')`;
                                                } else if(ST._modulo == 'nfs'  &&  text != ''){
                                                    onclickacao =  `alertAtencao('`+text+`')`;
                                                }  else if(ST._modulo == 'nfcte'  &&  text != ''){
                                                    onclickacao =  `alertAtencao('`+text+`')`;
                                                } else if(ST._modulo == 'comprassocios'  &&  text != ''){
                                                    onclickacao =  `alertAtencao('`+text+`')`;
                                                } else if(ST._modulo == 'tag'){
                                                    onclickacao =  `alertAtencao('TAG já Locada! Verificar em ORIGEM TAG')`;
                                                } else if(ST._modulo == 'solcom'){
                                                    onclickacao =  `alertAtencao('Item(ns) Não Cadastrados Pendentes para Aprovação.')`;
                                                } else if(ST._modulo == 'pedido'  &&  text != ''){
                                                    onclickacao =  `alertAtencao('`+text+`')`;
                                                }else if(ST._modulo == 'pedidofaturamento'  &&  text != ''){
                                                    onclickacao =  `alertAtencao('`+text+`')`;
                                                } else if(ST._modulo == 'resultaves'  &&  text != ''){
                                                    onclickacao =  `alertAtencao('`+text+`')`;
                                                } else if(ST._modulo == 'resultsuinos'  &&  text != ''){
                                                    onclickacao =  `alertAtencao('`+text+`')`;
                                                } else if(ST._modulo == 'amostraaves'  &&  text != ''){
                                                    onclickacao =  `alertAtencao('`+text+`')`;
                                                } else if(ST._modulo == 'amostratra'  &&  text != ''){
                                                    onclickacao =  `alertAtencao('`+text+`')`;
                                                } else if(ST._modulo == 'documento'  &&  text != '') {
                                                    onclickacao =  `alertAtencao('`+text+`')`;
                                                } else if(ST._modulo == 'lotealmoxarifado'  &&  text != '') {
                                                    onclickacao =  `alertAtencao('`+text+`')`;
                                                } else if(ST._modulo == 'nfentrada'  &&  text != '') {
                                                    onclickacao =  `alertAtencao('`+text+`')`;
                                                } else if(obj.permissao.mensagem != undefined) {
                                                    onclickacao =  `alertAtencao('${obj.permissao.mensagem}')`;
                                                } else{
                                                    onclickacao =  `alertAtencao('O Lote não está aprovado ou quantidade de insumos insuficiente')`;
                                                }
                                                
                                            } else if(ST._modulo == obj.permissao.modulo                                                       
                                                      && (obj.permissao.esconderbotaoass == 'ERROASSTODOS' || obj.permissao.esconderbotaoass == 'ERROASSPARCIAL' || obj.permissao.esconderbotaoass == 'ERROASSINDIVIDUAL')
                                                      && mostrabotao.statustipo != 'CANCELADO' && mostrabotao.statustipo != 'ASSINA'){
                                                onclickacao =  `alertAtencao('Por favor, o responsável deverá assinar antes de mudar o status do Documento.')`;
                                            } else {
                                                onclickacao =  `ST.alterarstatus(`+ mostrabotao.idfluxo +`, '`+ mostrabotao.idfluxostatushist +`', `+ mostrabotao.idfluxostatus +`, `+ mostrabotao.idfluxostatusf +`, '`+ mostrabotao.statustipo +`', '`+ mostrabotao.idfluxostatuspessoa +`', '`+ mostrabotao.ocultar +`', '`+ mostrabotao.prioridade +`', '`+ mostrabotao.tipobotao +`', '`+ obj.permissaoassinatura.idcarrimbo +`')`;                              
                                            }
                                            
                                            let onclickpermissao = '';
                                            let cursor = '';
                                            //Se for evento não tem essa permissão
                                            if((obj.permissaoassinatura.idcarrimbo && mostrabotao.statustipo == 'ASSINAR' && ST._modulo == obj.permissaoassinatura.modulo) //Aparece o botão para quem irá assinar definido no Fluxo
                                                || (mostrabotao.statustipo != 'ASSINA' && ST._modulo != 'evento') 
                                                || (obj.permissaoassinatura.idcarrimbo  && mostrabotao.statustipo != 'ASSINA' && ST._modulo != 'evento') //Aparece os que não tem nenhuma restrição
                                                || (ST._modulo == 'evento')) 
                                            {
                                                if(obj.permissaolp != 'r' && mostrabotao.permissaolp != 'r')
                                                {
                                                    onclickpermissao = `onclick="`+ onclickacao +`"`;
                                                } else {
                                                    onclickpermissao = `disabled`;
                                                    cursor = ` pointer-events: auto !important;`;
                                                }

                                                let btnStyle = `margin:3px; background:${mostrabotao.cor}; color:${mostrabotao.cortexto}; ${cursor}`;

                                                fluxo += `
                                                    <button 
                                                        id="fluxostatus_${mostrabotao.idfluxostatusf}" 
                                                        type="button" 
                                                        assina="false" 
                                                        class="btn btn-xs botaofluxo"
                                                        token="${mostrabotao.rotuloresp}" 
                                                        style="${btnStyle}" 
                                                        ${onclickpermissao} >
                                                        <i class="fa fa-refresh"></i>${mostrabotao.botao}
                                                    </button>
                                                `;
                                                
                                                
                                                if(mostrabotao.permissaolp == 'r')
                                                {
                                                    ST.permissaoLP(); 
                                                }                                                
                                            }                                            
                                        }
                                    }
                                    
                                } else if(obj.permissaolp == 'r'){
                                    ST.permissaoLP();   
                                }

                                fluxo += `</div>`;

                                let histRest = ST.getCarregaHistoricoRestaurar() || '[]';
                                if(histRest != '[]') {
                                    fluxo += `<div id="hist_rest"><i title="Histórico Restauração" class="fa btn-sm fa-info-circle 2x azul pointer hoverazul tip" onclick='ST.carregaHistoricoRestaurar(${histRest});'></i></div></div></div>`;
                                }
                            }   
                            
                            if($('#cbSteps').hasClass("fluxocarregando")){

                                $('#cbSteps').html(fluxo).addClass('fluxocarregado').removeClass('fluxocarregando'); 
                            }
                            
                            ST.trigger('posCarregaFluxo');                     
                        }                          
                        ST.carregarFluxoObjeto(idfluxostatustab, statustipotab);
                        ST._estado = false;
                        if(obj.permissaoassinatura.idcarrimbo){
                            ST.botaoAssinarGeral(obj.permissaoassinatura.idcarrimbo, obj.permissaoassinatura.criadoem);
                        } 
                        
                        //Remove o onclick do Botão Restaurar, adicionando o novo Click                        
                        if(ST._esconderRestaurar == 'Y'){
                            $("#cbRestaurar").hide();
                        } else {
                            $("#cbRestaurar").attr("onclick", "ST.listaRestaurarFluxo()");
                            $("#cbRestaurar").show();
                        }                        

                    }).catch(e=>{
                        console.error(e);
                        ST._estado = false;
                    });
                } else {
                    ST._estado =  false;
                }
            } else {
                ST._estado =  false;
            }
        },

        permissaoLP: function()
        {
            $("#cbModuloForm").find('input').prop( "disabled", true ).addClass("desabilitado");
            $("#cbModuloForm").find("select" ).prop( "disabled", true ).addClass("desabilitado");
            $("#cbModuloForm").find("textarea").prop( "disabled", true ).addClass("desabilitado");
            $("#cbModuloForm").find('button').prop( "disabled", true );
            $("#cbModuloForm").find('div').prop( "disabled", true );
            //$("#cbModuloForm").find("i").remove();
            $("#cbModuloForm").find("button").attr("disabled", true);   
            $('#divenda').hide();
            $('#cadcliente').hide();
            $('#cadcontatos').hide();
            $('#altcliente').hide();
            $('#endereco').hide();
            $('#cadendereco').hide();
            $('#cliqarrast').hide(); 
            $('#formSF').hide(); 
            $('.insobspgt').hide();    
            $('.estoque').hide();
            $('#envemail').hide();
            
            //Desbloquear o comentário do Evento
            $('[name*=_modulocom_descricao]').removeAttr("disabled").removeClass('desabilitado');
            $('[name*=_modulocom_idmodulocom]').removeAttr("disabled").removeAttr("readonly");
            $('[name*=_modulocom_idempresa]').removeAttr("disabled").removeAttr("readonly");
            $('[name*=_modulocom_idmodulo]').removeAttr("disabled").removeAttr("readonly");
            $('[name*=_modulocom_modulo]').removeAttr("disabled").removeAttr("readonly");
            $('[name*=_modulocom_status]').removeAttr("disabled").removeAttr("readonly");
            $('[name*=_evento_ideventotipo]').removeAttr("disabled").removeAttr("readonly");
            $('[name*=_evento_idevento]').removeAttr("disabled").removeAttr("readonly");

            ST.bloquearCBPost();

        },

        desbloquearCBPost: function () 
        {
            if(ST.indexPrePost != null && typeof ST.indexPrePost == "number"){
                CB.off('prePost', ST.indexPrePost);
                ST.indexPrePost = null;
            }
        },

        bloquearCBPost: function () 
        {
            ST.indexPrePost = CB.on('prePost', function(){
                alertErro('Sem permissão salvar o módulo neste STATUS.');
                return false;
            });
        },

        enviarstatus: function(idfluxo, idfluxostatushist, idfluxostatus, idstatusf, statustipo, idfluxostatuspessoa, ocultar, prioridade, tipobotao, idcarrimbo,log)
        {
            //(ALBT - 14/04/2021)  funcao feita para enviar o novo status quando houver alteração do mesmo - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=404949
            //LTM (29-03-2021)  - CBPost com posPost para validar os dados do formulário antes de alterar o status.
           CB.post({
                urlArquivo: 'ajax/_fluxo.php?fluxo=fluxo&_modulo='+ST._modulo,
                refresh: 'refresh',
                objetos: {
                    "_modulo": ST._modulo,
                    "_primary": ST._primary,
                    "_idobjeto": ST._idobjeto,
                    "idfluxo": idfluxo,
                    "idfluxostatushist": idfluxostatushist,
                    "idstatusf": idstatusf,
                    "statustipo": statustipo,
                    "idfluxostatus": idfluxostatus,
                    "idfluxostatuspessoa": idfluxostatuspessoa,
                    "ocultar": ocultar,
                    "prioridade": prioridade,
                    "tipobotao": tipobotao,
                    "idcarrimbo": idcarrimbo,
                    "log": log,
                    "acao": "alterarstatus"                     
                },
                posPost: function(data,texto,jqXHR){
                    let meusatus = $('[name*=_status]').filter(function() {
                        return this.name.match(/1_u\w+_status/);
                    });
                    
                    if(statustipo){
                        meusatus.val(statustipo);
                    }

                    CB.post({objetos:{"STenviarstatus":true}});
                }
            });
        },

        alterarstatus: function(idfluxo, idfluxostatushist, idfluxostatus, idstatusf, statustipo, idfluxostatuspessoa, ocultar, prioridade, tipobotao, idcarrimbo, log = 0) 
        {
            if(typeof this.customFunction == 'function') {
                const returnoFuncao  = this.customFunction(idfluxo, idfluxostatushist, idfluxostatus, idstatusf, statustipo, idfluxostatuspessoa, ocultar, prioridade, tipobotao, idcarrimbo, log = 0);

                if(!returnoFuncao) return;
            }

            if(statustipo == 'CANCELADO' || statustipo == 'OCULTO'){ //(ALBT - 14/04/2021) condicao para enviar um alerta de confirmação se a pessoa realmente quer cancelar o evento clicado. - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=404949
                if(confirm(`Deseja realmente ${statustipo == 'CANCELADO' ? 'cancelar' : 'ocultar'} esse registro?`)){
                    $("[vnulo]").removeAttr("vnulo");
                   ST.enviarstatus(idfluxo, idfluxostatushist, idfluxostatus, idstatusf, statustipo, idfluxostatuspessoa, ocultar, prioridade, tipobotao, undefined, log);
                }
            } else {
                ST.enviarstatus(idfluxo, idfluxostatushist, idfluxostatus, idstatusf, statustipo, idfluxostatuspessoa, ocultar, prioridade, tipobotao, idcarrimbo, log);
            }
        },

        verificarInicio: function()
        {
            if(getUrlParameter('_modulo') != "alterasenha"){
                let idempresa = gIdEmpresaModulo || "";
                let modulo = ST._modulo;
                let idobjeto = ST._idobjeto;
                let token = getUrlParameter("token");

                if(token != ''){
                    token = "?token="+token;
                }
                
                $.ajax({
                    method: 'post'
                    ,url: 'ajax/getinfoempresa.php'+token
                    ,data: {
                        idempresa,
                        modulo,
                        idobjeto
                    }
                }).done(function(data,texto,jqXHR){
                    try{
                        let info = JSON.parse(data);

                        if(info['erro']){
                            alertErro(info['erro']);
                            return;
                        }

                        let logoEmpresaHTML = `<div class='form-logo-empresa mult_imp'><label class="alert-warning">${info[gIdEmpresaModulo].sigla}</label></div>`,
                            JQdivLogo = $(".panel-heading:first .sigla-empresa");

                        $(".panel-heading:first").attr('style', `background-color: ${info[gIdEmpresaModulo].corsistema} !important`);
                        $(".panel-heading:first").css("color",'white');

                        if(JQdivLogo.get().length)
                        {
                            JQdivLogo.html(logoEmpresaHTML);
                        } else if (!$(".panel-heading:first .sigla-empresa .mult_imp").get().length)
                        {
                            JQdivLogo.prepend(logoEmpresaHTML);
                        }

                        JQdivLogo.addClass('col-xs-12');
                    }catch(err){
                        console.warn(err)
                    }                    
                });
            
            $("#cbRestaurar").hide();

            //Altera a cor da Etapa conforme andamento do Status
            $.ajax({
                method: 'post'
                ,url: 'ajax/_fluxo.php'
                ,data: {
                    "_modulo": ST._modulo,
                    "_primary": ST._primary,
                    "_idobjeto": ST._idobjeto,
                    "acao": "verificarinicio"
                }
            }).done(function(data,texto,jqXHR){
                    if(jqXHR.getResponseHeader("X-CB-RESPOSTA") == 0){
                        ST._estado =  false;
                        alertAtencao(jqXHR.getResponseHeader("X-CB-FORMATO"));
                    } else if(jqXHR.getResponseHeader("X-CB-RESPOSTA") == 10) {
                        console.log('sem fluxo');
                        $("#cbRestaurar").show();
                        ST._estado =  false;
                        return false;
                    } else if(jqXHR.getResponseHeader("X-CB-FORMATO") == '999_i') {
                        let meusatus = $('[name*=_status]').filter(function() {
                            return this.name.match(/_1_u_\w+_status/);
                        });
                        if(jqXHR.getResponseHeader("X-CB-RESPOSTA")){
                            meusatus.val(jqXHR.getResponseHeader("X-CB-RESPOSTA"));
                        }
                        ST.CarregarStatus();
                    } else {
                        ST.CarregarStatus();
                    }
                    
                    ST.trigger('posVerificarInicio', data,texto,jqXHR);
                });
            }

        },

        carregaRotulo: function(fluxo, posicaoStatus)
        {
            var rotulo, block = "", onclick = "";
            if(fluxo.botaocriador == 'Y' && fluxo.criadopor != fluxo.usuario || (posicaoStatus == 'CANCELADO' || posicaoStatus == 'FIM' || posicaoStatus == 'CONCLUIDO')){
                block = 'style="cursor: no-drop" class="nodrop"';
            }

            if((fluxo.status == "ATIVO" || fluxo.status == "ASSINA") && fluxo.status != "CANCELADO") { 
                classe = '<c class="fa fa-check concluidoi"/>';
            } else if(fluxo.statustipo == "CANCELADO" && fluxo.status == "ATIVO") { 
                classe = '<c class="fa fa-times canceladoired"/>';
            } else if(["CANCELADO","DEVOLVIDO","RECUSADO","REPROVADO", "EXTRAVIADO"].includes(fluxo.status)) { 
                classe = '<c class="fa fa-times canceladoi"/>';
            } else if(fluxo.status == "PENDENTE" && ST._modulo.indexOf("formalizacao") != 0) { 
                classe = '<c class="fa fa-check concluidoi"/>';
            } else if(ST._modulo.indexOf("formalizacao") != 0 && fluxo.statusloteativ == 'ATIVO'){
                classe = '<c class="fa fa-check concluidoi"/>';
            } else {
                classe = '<c class="fa fa-minus-circle inativoi"/>';
            }
            
            var historico = fluxo.hist;
            var titulo = "";
            var alter = "";
            if(Object.keys(historico).valueOf() != "") 
            {
                for(var key of Object.keys(historico).reverse())
                {
                    hist = historico[key];
                    alter += hist.criadoem + " - " + hist.criadopor + "\n";
                }
                titulo += "title='"+alter+"'";
            } else { titulo = "title='--/--/-- --:--'"; }

            rotulo = `<div `+ titulo +`"> 
                        <div> 
                        <span ` + block + ` ` + onclick + `>`;
                            if(fluxo.numfluxostatus){
                                rotulo += fluxo.etapa + `.` + fluxo.numfluxostatus + ` `;
                            }
                            rotulo += fluxo.rotulo;
            rotulo += '</span>'+classe;

            var historicocarrimbo = fluxo.carrimbo.historico || {};
            var titulocarrimbo = "";
            var idobjetoextval;
            var statusassinatura;

            if(Object.keys(historicocarrimbo).valueOf() != "") 
            {
                var altercarrimbo = "";
                for(var key2 of Object.keys(historicocarrimbo))
                {
                    histcarrimbo = historicocarrimbo[key2];
                    if(histcarrimbo.idfluxostatus == fluxo.idfluxostatus)
                    {
                        if(histcarrimbo.status == 'ASSINADO' || histcarrimbo.status == 'CONFERIDO')
                        {
                            altercarrimbo += histcarrimbo.alteradopor  + " - " + histcarrimbo.alteradoem + " - " + histcarrimbo.status + "\n";
                        } else {
                            altercarrimbo += histcarrimbo.alteradopor + " - --/--/-- --:-- - PENDENTE \n";
                        }
                        
                        if(histcarrimbo.idfluxostatus && !idobjetoextval){
                            idobjetoextval = histcarrimbo.idfluxostatus;
                        } else {
                            idobjetoextval = histcarrimbo.idobjetoext;
                        }

                        if(histcarrimbo.status == 'PENDENTE' && fluxo.idfluxostatus == idobjetoextval){
                            statusassinatura = 'pendente';
                        } else if((histcarrimbo.status == 'CONFERIDO' || histcarrimbo.status == 'ASSINADO')  && fluxo.idfluxostatus == idobjetoextval) {
                            statusassinatura = 'assinado';
                        }
                    }
                }

                titulocarrimbo = "title='"+altercarrimbo+"'";                

            } else { 
                titulocarrimbo = "title='--/--/-- --:--'"; 
                idobjetoextval = "";
            }

            if(statusassinatura == 'pendente'){
                rotulo += '<i class="fa fa-pencil-square-o ativosignature" '+ titulocarrimbo +' />';
            } else if(statusassinatura == 'assinado') {
                rotulo += '<i class="fa fa-pencil-square-o concluidosignature" '+ titulocarrimbo +' />';
            }

            rotulo += '</div></div>';
            
            return rotulo;
        },

        carregarFluxoObjeto: function(idfluxostatustab, statustipotab)
        {
            var alteracor = "";
            //Altera a cor da Etapa conforme andamento do Status
            $.ajax({
                method: 'post'
                ,url: 'ajax/_fluxo.php'
                ,data: {
                    "_modulo": ST._modulo,
                    "_primary": ST._primary,
                    "_idobjeto": ST._idobjeto,
                    "acao": "carregarfluxoobjeto"
                }
            }).done(function(data,texto,jqXHR){
                if(jqXHR.getResponseHeader("X-CB-RESPOSTA") == 0){
                    alertAtencao(jqXHR.getResponseHeader("X-CB-FORMATO"));
                }

                var cor = JSON.parse(data);
                if(cor != null) 
                {
                    // Colori as etapas de cinza ou verde
                    // e defini todos os progressos p/ cinza
                    for(var key in cor){
                        colorirFluxo = cor[key];
                        $('#li_'+colorirFluxo.idetapa).removeClass('cinza').addClass(colorirFluxo.class);
                        $('#li_'+colorirFluxo.idetapa).removeClass('cor-cinza').addClass('cor-cinza');
                        if(colorirFluxo.class == 'cancelado'){
                            alteracor = 'cancelado';
                        }
                        
                        if(colorirFluxo.class=='cinza'){
                            $('#li_'+colorirFluxo.idetapa+' .divstatus .concluidoi').removeClass('concluidoi').css('color', '#adadadad');
                        }
                    }

                    var inicio, fim, list_li = $("#cbProgressBar li");

                    list_li.each((i,o) => {
                        if($(o).hasClass("activeout")){
                            inicio = i;
                            return false;
                        }
                    });

                    list_li.each((i,o) => {
                        if($(o).hasClass("activeout")){
                            fim = i;
                        }                     
                    });
                    
                    list_li.each((i,o) => {
                        if(i == fim){
                            $(o).removeClass('cor-vermelho cor-cinza cor-verde').addClass('cor-verde');
                            return false;
                        }else if(i >= inicio){
                            $(o).removeClass('cor-vermelho cor-cinza').addClass('cor-verde');
                        }
                    });

                    if(alteracor == 'cancelado'){
                        list_li.each((i,o) => {
                            if(i >= fim){
                                $(o).removeClass('cor-verde-cinza cor-cinza cor-verde').addClass('cor-vermelho');
                            }
                        });
                    }
                }
            });
        },

        listaRestaurarFluxo: function()
        {
            $.ajax({
                method: 'post'
                ,url: 'ajax/_fluxo.php'
                ,data: {
                    "_modulo": ST._modulo,
                    "_primary": ST._primary,
                    "_idobjeto": ST._idobjeto,
                    "acao": "listarestaurarfluxo"
                }
            }).done(function(data,texto,jqXHR){

                var strCabecalho = "<strong>Selecione o novo status desejado</strong>";
                var str = '';
                var options = '';

                var fluxo = JSON.parse(data);
                for(var key of Object.keys(fluxo))
                {
                    
                    listarFluxo = fluxo[key];
                    if (!isNaN(key)) {
                        str += '<tr>';
                        str += '<td><input type="radio" status="' + listarFluxo.statustipo + '" idfluxostatus="' + listarFluxo.idfluxostatus + '" name="idfluxostatushist" value="' + listarFluxo.idfluxostatushist + '"></td>';
                        str += '<td><label>'+ listarFluxo.rotulo +'</label></td>';  
                        str += '</tr>';
                    }
                }
                var option = JSON.parse(data);
                if (option.motivo) {
                    for(var k of Object.keys(option.motivo)) {
                        op = option.motivo[k]
                        options += '<option value="'+op+'">'+op+'</option>';
                    }
                }
                if (options == '') {
                    options="<option value='Correção'>Correção</option>";
                }
                var $htmloriginal = $(`<div class="row">
                                            <div class="col-md-3"></div>
                                            <div class="col-md-6" nowrap>
                                                <label>Status:</label>
                                                <table style="width:100%;" id="fluxo">
                                                    <tbody>                                                        
                                                        ${str}
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="col-md-3"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                               <label>Motivo:</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                            	<select id='_edicaomotivo_' title='' tabindex='-99'>
                                                    ${options}
                                           	    </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                            <label>Obs.:</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type='text' id='_edicaoobs_'  value=''>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4"></div>
                                            <div class="col-md-4"></div>
                                            <div class='col-md-4' style="text-align: end;" id='salvarRestauracao'></div>
                                        </div>`);

                $htmloriginal.find("#salvarRestauracao").append(
                    $(`<button type="button" class="btn btn-danger btn-xs"><i class="fa fa-circle"></i>Salvar</button>`).on('click',function(){ 
                        var idfluxostatushist = $(`[name='idfluxostatushist']:checked`).val() || "";
                        var status = $(`[name='idfluxostatushist']:checked`).attr('status') || "";
                        var idfluxostatus = $(`[name='idfluxostatushist']:checked`).attr('idfluxostatus');
                        var motivo = $(`#_edicaomotivo_`).val() || "";
                        var motivoobs = $(`#_edicaoobs_`).val() || "";

                        if(idfluxostatushist != ""){
                            if (motivo != '' && motivoobs != '' && motivoobs.trim().length >= 5) {
                                $.ajax({
                                    method: 'post'
                                    ,url: 'ajax/_fluxo.php'
                                    ,data: {
                                        "_modulo": ST._modulo,
                                        "_primary": ST._primary,
                                        "_idobjeto": ST._idobjeto,
                                        "idfluxostatushist": idfluxostatushist,
                                        "status": status,
                                        "idfluxostatus": idfluxostatus,
                                        "motivo": motivo,
                                        "motivoobs": motivoobs,
                                        "acao": "restaurarfluxo"
                                    }
                                }).done(function(data,texto,jqXHR){
                                    if(jqXHR.getResponseHeader("X-CB-RESPOSTA") == 0){
                                        alertAtencao(jqXHR.getResponseHeader("X-CB-FORMATO"));
                                    }
    
                                    let meusatus = $('[name*=_status]').filter(function() {
                                        return this.name.match(/_1_u_\w+_status/);
                                    });
                                    
                                    if(status){
                                        meusatus.val(status);
                                    }
    
                                    //CB.post();
                                    vUrl = CB.urlDestino + window.location.search;
                                    CB.loadUrl({
                                        urldestino: vUrl
                                    });
                                    $('#cbModal').modal('hide');
                                });
                            }else{
                                alertAtencao("O motivo deve conter ao menos 5 caracteres!");
                            }
                        }
                    })
                );

                CB.modal({
                    titulo: strCabecalho
                    ,corpo: [$htmloriginal]
                    ,classe: 'trinta'
                });
            });
        },

        getCarregaHistoricoRestaurar: function()
        {
            $.ajax({
                method: 'post'
                ,url: 'ajax/_fluxo.php'
                ,async: false
                ,data: {
                    "_modulo": ST._modulo,
                    "_idobjeto": ST._idobjeto,
                    "acao": "carregaHistoricoRestaurar"
                }
            }).done(function(data,texto,jqXHR){
                if(jqXHR.getResponseHeader("X-CB-RESPOSTA") == 0){
                    alertAtencao(jqXHR.getResponseHeader("X-CB-FORMATO"));
                }
                dados = data;
            });

            return dados;
        },

        carregaHistoricoRestaurar: function(historico)
        {
            let str = '';
            for(var key of Object.keys(historico))
            {
                var historicodetalhes = historico[key];
                str += `<tr>`;
                str += `<td>${historicodetalhes.rotulo}</td>`;
                str += `<td>${historicodetalhes.motivo}</td>`;
                if(historicodetalhes.permitealterar == true){
                    str += `<td>
                                <input type="text" class="comentarioRestaurar size25" idfluxostatushistobs="${historicodetalhes.idfluxostatushistobs}" readonly value="${historicodetalhes.motivoobs}"/>
                                <i class="fa fa-pencil azul editacomentario"></i>
                                <button class="btn  btn-xs btn-success salvacomentario hidden"> Salvar</button>
                            </td>`;
                }else{
                    str += `<td>${historicodetalhes.motivoobs}</td>`;
                }
                str += `<td>${historicodetalhes.nome}</td>`;
                str += `<td>${historicodetalhes.criadoem}</td>`;
                str += '</tr>';
            }

            let strCabecalho = "<strong>Histórico Restauração</strong>";
            let $htmloriginal = $(`<div class="row">
                                        <div class="col-md-12" nowrap>
                                            <table style="width:100%;" id="fluxo">
                                                <tbody>                                                        
                                                    <th>Status</th>
                                                    <th>Motivo</th>
                                                    <th>Observação</th>
                                                    <th>Criado Por</th>
                                                    <th>Criado Em</th>
                                                </tbody>
                                                ${str}
                                            </table>
                                        </div>
                                        <div class="col-md-3"></div>
                                    </div>`);

            CB.modal({
                titulo: strCabecalho
                ,corpo: [$htmloriginal]
                ,classe: 'cinquenta'
            });
            $(".editacomentario").on("click",(element)=>{
                $(element.target).siblings('input').removeAttr('readonly').focus()
                $(element.target).siblings('button').removeClass('hidden')
                $(element.target).addClass('hidden')
            });
            $(".salvacomentario").on("click",(element)=>{
                $(element.target).addClass("blink")
                let idfluxostatushistobs = $(element.target).siblings('input').attr('idfluxostatushistobs')
                let newcoment = $(element.target).siblings('input').val()
                $.ajax({
                    method: 'post'
                    ,url: 'ajax/_fluxo.php'
                    ,async: false
                    ,data: {
                        "idfluxostatushistobs": idfluxostatushistobs,
                        "motivoobs": newcoment,
                        "acao": "atualizarHistoricoRestaurar"
                    }
                }).done(function(data,texto,jqXHR){
                    $(element.target).removeClass("blink")
                    $(element.target).siblings('i').removeClass('hidden')
                    $(element.target).addClass('hidden')
                    if(jqXHR.getResponseHeader("X-CB-RESPOSTA") == 0){
                        alertAtencao(jqXHR.getResponseHeader("X-CB-FORMATO"));
                    }
                    if(data.trim() == "OK"){
                        $(element.target).siblings('input').prop("readonly",true)
                        alertSalvo("Alterado com sucesso!")
                    }else{
                        console.log("atualização de comentario falhou")
                    }
                });
            });
        },

        permissoesbotoes: function()
        {
            $.ajax({
                method: 'post'
                ,url: 'ajax/_fluxo.php'
                ,async: false
                ,data: {
                    "_modulo": ST._modulo,
                    "_idobjeto": ST._idobjeto,
                    "acao": "permissoesbotoes"
                }
            }).done(function(data,texto,jqXHR){
                if(jqXHR.getResponseHeader("X-CB-RESPOSTA") == 0){
                    alertAtencao(jqXHR.getResponseHeader("X-CB-FORMATO"));
                }
                return data;
            });
        },

        botaoAssinarGeral: function (idcarrimbo, criadoem)
        {
            $bteditar = $("#btAssina");
            if($bteditar.length==0){
                CB.novoBotaoUsuario({
                    id:"btAssina"
                    ,rotulo:"Assinar"
                    ,class:"verde"
                    ,icone:"fa fa-pencil"
                    ,onclick:() => {
                        ST.assinar(idcarrimbo, criadoem)
                    }
                });
            }
        },

        assinar : function (idcarrimbo, criadoem){
            /* if(ST._modulo == 'amostratraprovisorio' || ST._modulo == 'amostratra'){
                let $oModal = $(`
                <div class="row">
                        <div class="col-md-12">
                            <table style="width:100%;">
                                <table style="width:100%;">
                                    <tr style="cursor: pointer;border-radius: 80px;">
                                        <td>Registro TEA: `+criadoem+`</td>
                                    </tr>
                                    <tr style="cursor: pointer;border-radius: 80px;">
                                        <td><input placeholder="Não Informado" autocomplete="off" id="_data_assinatura_fluxo_" type="text" class="calendario"></td>
                                    </tr>
                                    <tr style="cursor: pointer;border-radius: 80px;align-items: center;text-align: center;">
                                        <td><br><button id="_confirm_assinatura_fluxo_" class="btn btn-success btn-xs">Confirmar Data</button></td>
                                    </tr>
                                </table>
                            </table>
                        </div>
                    </div>
                `);

                var date = new Date();

                //O daterangepicker não dispara o "change" do elemento. Portanto deve ser feita verificação do evento do plugin
                $oModal.find("#_data_assinatura_fluxo_").daterangepicker({
                    timePicker: true,
                    "singleDatePicker": true,
                    "showDropdowns": true,
                    "linkedCalendars": false,
                    "opens": "left",
                    "locale": {format: 'DD/MM/YYYY hh:mm:ss'},
                    "timePicker24Hour": true,
                    horarioComercial: false,
                    startDate: moment(date)
                }).on('apply.daterangepicker', function(ev, picker) {
                    $(this).attr("data", picker.startDate.format("YYYY-MM-DD HH:mm:ss"))
                });

                $oModal.find("#_confirm_assinatura_fluxo_").on("click", function(){
                    var conteudo = {
                        idpessoa : gIdpessoa || "",
                        modulo : ST._modulo || "",
                        idpagina : ST._idobjeto || "",
                        idcarrimbo : idcarrimbo || "",
                        status: "ASSINADO",
                        alteradoem: $("#_data_assinatura_fluxo_").attr("data")
                    }
                    signContent({ 
                        path: "ajax/_certs.php",
                        content: conteudo,
                        selector: "#btAssina",
                        hideButtonOnSign: true,
                        posSing: function(){  }
                    });
                });

                CB.modal({
                    titulo: "</strong>Escolha Data Assinatura</strong>",
                    corpo: [$oModal],
                    classe: 'vinte',
                });
            } else {*/
                var conteudo = {
                    idpessoa : gIdpessoa || "",
                    modulo : ST._modulo || "",
                    idpagina : ST._idobjeto || "",
                    idcarrimbo : idcarrimbo || "",
                    status: "ASSINADO"
                }
                signContent({ 
                    path: "ajax/_certs.php",
                    content: conteudo,
                    selector: "#btAssina",
                    hideButtonOnSign: true,
                    posSing: function(){  }
                });
            //}
        },

        on: function(label, fn){

			if((fn && typeof fn === 'function') && (label && typeof label === 'string')){

				var events = ST._eventos;

				if(events.has(label)){ // Verifica a existência do label passado como parâmento na lista de eventos do Carbon.

					const oldEvents = events.get(label); // Recupera lista de funções que possuem o respectivo label como chave.

					let exists = false;

					for(let f of oldEvents){
						if(f){
							if(f.toString() === fn.toString()){
								exists = true;
								break;
							}
						}
					}

					if(!exists){
						events.set(label, [ ...oldEvents, fn ]) // Set uma nova lista de funções para o label, mesclando com a lista antiga.
						return oldEvents.length;
					}else{
						return false;
					}
					
				}else{

					events.set(label, [ fn ]); // Inicia uma lista de funções para o respectivo label.

					return 0;
				}

			}else{

				console.error("Function .on: Verifique os parâmetros da função");
				return false;
			}

		},

        off: function(label, index){

			if(label && typeof label === 'string'){

				var events = ST._eventos;

				if(index && typeof index == "number"){
					let allEvents = events.get(label);
					delete allEvents[index];
					events.set(label, allEvents);
				}else{
					events.delete(label); // Exclui as funções com o respectivo label.
				}
				
			}else{

				console.error("Function .off: Verifique o parâmetro da função");

			}
		},

        trigger: function(label, ...data){

			if(label && typeof label === 'string'){

				var events = ST._eventos;

				const listeners = events.get(label); // Recupera lista de funções que possuem o respectivo label como chave.

				if (Array.isArray(listeners) && listeners.length) { // Verifica se listeners é um Array e se o label existe neste Array .

					returnList = [];
					listeners.forEach(event => {
						if(event){
							returnList.push( event(...data));
						}
					}); // Executa todas as funções associadas ao respectivo label.

					return returnList;
				}

				return [];
			}else{

				console.error("Function .trigger: Verifique os parâmetros da função");
				return [];
			}

		}
    }
}

ST = new Status();
ST.carregarFluxos();


//LTM - 07-04-2021 - Retorna o idFluxoStatus Selecionado
function getIdFluxoStatus(modulo, vstatus, idmodulo){
    $.ajax({
        type: "post",
        url: "ajax/_fluxo.php?fluxo=fluxo",
        async: false,
        data: {
            "_modulo": modulo,
            "_idobjeto": idmodulo,
            "status": vstatus,
            "acao": "getidfluxostatus"
        },
        success: function(data) {
            idfluxostatus = data;
        }
    });  

    return idfluxostatus;
}

function getIdFluxoStatusHist(_modulo, vidnf){
    $.ajax({
        type: "post",
        url: "ajax/_fluxo.php?fluxo=fluxo",
        async: false,
        data: {
            "_modulo": _modulo,
            "_idobjeto": vidnf,
            "acao": "getidfluxostatushist"
        },
        success: function(data) {
            idfluxostatushist = data;
        }
    });  

    return idfluxostatushist;
}

