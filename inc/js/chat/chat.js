var Chat = function() {

    self = this;
    self.started=false;
    self.contatosRecuperados=false;
    self.contatosRecuperarAvatar=false;
    self.init = function() {
        
	//Caso o chat tenha sido inicializado, não inicializar novamente
        if(self.started===true){
            console.log("JS Chat: Inicialização interrompida");
            return false;
        }

        //Caso não seja inicializado corretamente retornar false
        if (arguments.length == 0 || typeof arguments !== "object") {
            console.log("JS Chat: nenhum parâmetro informado");
            return false;
        }

        //Inicializa o container html
        $("body").append(self.chatContainer);

        //Inicializa os objetos padrão
        self.oNotificacoes = $(arguments[0].notificacoes || "");
        self.oBadge = $(arguments[0].badge || "");
        self.oIBadge = $(arguments[0].iBadge || "");
        self.oBadgeTarefa = $(arguments[0].badgeTarefa || "");
        self.oIBadgeTarefa = $(arguments[0].iBadgeTarefa || "");
        self.oListaNotificacoes = $(arguments[0].listaNotificacoes || "");
        self.oListaNotificacoesHeader = $(arguments[0].listaNotificacoesHeader || "");
        self.oListaNotificacoesFooter = $(arguments[0].listaNotificacoesFooter || "");
        self.oChatContainer = $(arguments[0].chatContainer || "");
        self.oSideBar = self.oChatContainer.find(".sideBar");
        self.oChatPainelContatos = $("#cbChatPainelContatos");
        self.oGrupoContatosOnlineNotificacao = $("#cbChatContatosOnlineNotificacao");
        self.oGrupoContatosOfflineNotificacao = $("#cbChatContatosOfflineNotificacao");
        self.oGrupoContatosOnline = $("#cbChatContatosOnline");
        self.oGrupoContatosOffline = $("#cbChatContatosOffline");
        self.oChatFullscreen = $("#cbChatFullscreen");
        self.oConversation = self.oChatContainer.find(".conversation");
        self.oAvatarProfile = self.oChatContainer.find("#avatarProfile");
        self.oChatBody = self.oChatContainer.find("#chatBody");
        self.oChatMsgAnteriores = self.oChatContainer.find("#chatMsgAnteriores");
        self.oCCabecalho = self.oChatContainer.find("#chatCabecalho");
        self.oCAvatar = self.oChatContainer.find("#chatAvatar");
        self.oCNome = self.oChatContainer.find("#chatNome");
        self.oCInfo = self.oChatContainer.find("#chatCabInfo");
        self.oCInfo2 = self.oChatContainer.find("#chatCabInfo2");
        self.oCStatus = self.oChatContainer.find("#chatStatus");
        self.oIconeToggleContatos = self.oChatContainer.find("#chatIconeToggleContatos");
        self.oChatNovaMensagem = self.oChatContainer.find("#chatNovaMensagem");
        self.hHtmlListaNotificacaoItem = arguments[0].htmlListaNotificacaoItem || false;

        self.intervaloRefresh = 15000
        self.maximizado = false;
        self.jContatos = {};
        self.aContatos = [];
        self.avatarDefault = "inc/img/avatardefault.png";
        self.avatarDefaultGrp = "inc/img/avatardefaultgrp.png";
        self.avatarTarefa = "inc/img/avatartarefa.png";
        self.avatarDir = "upload/avatar/"
        self.icoRingInt = "inc/img/ligInterna.png";
        self.icoRingExt = "inc/img/ligExterna.png";
        
        self.endpointEnviar = "https://app.laudo.io/api/bim/";

        //Configuracoes
        self.sCorBadgeAtivo = "#fff";
        self.sCorBadgeInativo = "#fff";
        self.iLimiteBadgeAtencao = 99;
        //A partir desta quantidade o badge comea a piscar
        self.iMaxNotificacoesVisiveis = (gChatMaxHistorico !== "" ? gChatMaxHistorico : 10);
        //Número máximo de notificações no histórico
        self.velAnimacaoShow = null;
        self.velAnimacaoHide = null;

        self.hSkelContato = `
            <div id="contato_%objetocontato%_%idcontato%" class="row sideBar-body contato %status% %objetocontato%_%idcontato%" tipocontato="%tipocontato%" idcontato="%idcontato%" objetocontato="%objetocontato%" status="%status%" title="%title%" onclick="chat.abrirConversa(%idcontato%,'%objetocontato%');$('#chatNovaMensagem').focus();" order="%order%">
                <div class="col-sm-3 col-xs-3 sideBar-avatar">
                    <div class="avatar-icon">
                        <img src="%img%">
                        <i class="fa fa-circle chatStatusContato"></i>
                    </div>
                </div>
                <div class="col-sm-9 col-xs-9 sideBar-main">
                    <div class="row">
                        <div class="col-sm-12 col-xs-12 sideBar-name">
                            <span class="name-meta  pull-left" style="width: 100%"><p style="margin:0px">%rotulo%</p></span><br>

                        </div>
                        <div class="col-sm-12 col-xs-12 sideBar-name">
                        <span id="ramalfixo" class="time-meta pull-left">%ramalfixo%</span>
			<span class="time-meta obs pull-right">%data%</span>
                        </div>
                        <div id="badge_contato_%objetocontato%_%idcontato%" class="badge fundovermelho pull-right sideBar-naoLidas hidden" imensagens="0"></div>
                    </div>
                </div>
                <i class="fa fa-check-square fa-2x checkContato verde hidden"></i>
            </div>`;

            self.hSkelBalao = `<div class="row message-body" ondblclick="chat.responder(%idimmsgbody%,this);" idimmsg="%idimmsg%" idimmsgbody="%idimmsgbody%" direcao="%senderreceiver%" status="%status%" tipo="%tipo%" statustarefa="%statustarefa%" style="background-color:%bgcolor%;">
                <div class="col-sm-12 messageMain%senderreceiver%">
                    <i class="fa fa-asterisk chatNovaMsg" status="%status%"></i>
                    <div class="%senderreceiver%">
                        <div class="dropdown msgOpcoes">
                            <i id="opcoesMsg_%idimmsg%" idimmsg="%idimmsg%" class="fa fa-chevron-down fa-2x pointer cinza"  data-toggle="dropdown" title="Alterar opções" onclick="chat.toggleOpcoesMsg('opcoesMsg_%idimmsg%')"></i>
                            <ul class="dropdown-menu %menuright%">
                                <li class="fonte09 nowrap" onclick="chat.responder(%idimmsgbody%,this)"><span><i class="fa fa-reply laranja"></i> Responder</span></li>
                                <li class="fonte09 nowrap" style="display:none" onclick="chat.msg2tarefa(%idimmsgbody%,true)"><span><i class="fa fa-calendar-check-o laranja"></i> Transformar em tarefa</span></li>
                                <li class="fonte09 nowrap" style="display: %displayapagar%" onclick="chat.apagarMsg(%idimmsgbody%)"><span><i class="fa fa-trash vermelho"></i> Apagar</span></li>
                            </ul>
                        </div>
                        <table class="chatTbMsg">
                            <tr>
                                <td rowspan="2" class="msgIndicadores">
                                    <i class="fa fa-calendar-check-o bold pointer chatMsgIconeTarefa" title="Data tarefa: %datatarefa%" onclick=abrirTarefa(%idimmsg%)></i>
                                </td>
                                <td>
                                    <div class="message-sender">%sendernome%</div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="message-text"><div class="msgAnexos">%msgAnexos%</div>%msg%</div>
                                    <i class="fa fa-check chatStatusEntrega pull-right" status="%status%"></i>
                                    <span class="message-time pull-right" title="%datatitle%">%data%</span>
                                    <span class="msgApagada">Mensagem apagada</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>`;


        self.hSkelBalaoWithReply = `<div class="row message-body" ondblclick="chat.responder(%idimmsgbody%,this);" idimmsg="%idimmsg%" idimmsgbody="%idimmsgbody%" direcao="%senderreceiver%" status="%status%" tipo="%tipo%" statustarefa="%statustarefa%" style="background-color:%bgcolor%;">
            <div class="col-sm-12 messageMain%senderreceiver%">
                <i class="fa fa-asterisk chatNovaMsg" status="%status%"></i>
                <div class="%senderreceiver%">
                    <div class="dropdown msgOpcoes">
                        <i id="opcoesMsg_%idimmsg%" idimmsg="%idimmsg%" class="fa fa-chevron-down fa-2x pointer cinza"  data-toggle="dropdown" title="Alterar opções" onclick="chat.toggleOpcoesMsg('opcoesMsg_%idimmsg%')"></i>
                        <ul class="dropdown-menu %menuright%">
                            <li class="fonte09 nowrap" onclick="chat.responder(%idimmsgbody%,this)"><span><i class="fa fa-reply laranja"></i> Responder</span></li>
                            <li class="fonte09 nowrap" style="display:none" onclick="chat.msg2tarefa(%idimmsgbody%,true)"><span><i class="fa fa-calendar-check-o laranja"></i> Transformar em tarefa</span></li>
                            <li class="fonte09 nowrap" style="display: %displayapagar%" onclick="chat.apagarMsg(%idimmsgbody%)"><span><i class="fa fa-trash vermelho"></i> Apagar</span></li>
                        </ul>
                    </div>
                    <table class="chatTbMsg">
                        <tr>
                            <td rowspan="2" class="msgIndicadores">
                                <i class="fa fa-calendar-check-o bold pointer chatMsgIconeTarefa" title="Data tarefa: %datatarefa%" onclick=abrirTarefa(%idimmsg%)></i>
                            </td>
                            <td>
                                <div class="message-sender">%sendernome%</div>
                            </td>
                        </tr>
                        <div style="width: 100%;" onclick="self.scrollParaReply(%msgReplyBodyId%);">
                            <div role="button" style="display: -webkit-flex; display: flex; position: relative; z-index: 1; border-radius: 4px; overflow: hidden; background-color: rgba(0, 0, 0, 0.07); cursor: pointer;">
                                <span style="background-color: #91ab01 !important; border-top-left-radius: 7.5px; border-bottom-left-radius: 7.5px;"></span>
                                <div style="min-height: 42px; max-height: 82px; display: -webkit-flex; display: flex; padding: 4px 12px 8px 8px; -webkit-align-items: center; align-items: center; overflow: hidden; -webkit-flex-grow: 1; flex-grow: 1;">
                                    <div style="overflow: hidden;">
                                        <div role="button" style="color: #74cff8; display: -webkit-flex; display: flex; font-size: 12.8px; font-weight: 500; line-height: 22px;">
                                            <span dir="auto">%msgPessoa%</span>
                                        </div>
                                        <div role="button" style="font-size: 10px; line-height: 10px; max-height: 60px; overflow: hidden; word-break: break-word; color: rgba(0, 0, 0, 0.6);">
                                            <span dir="auto">%msgReply%</span>
                                        </div>
                                    </div>
                                </div>
                                %imgMini%
                            </div>
                        </div>
                        <tr>
                            <td style="margin-top: 10px !important;">
                                <div class="message-text" style="margin-top: 10px !important;"><div class="msgAnexos">%msgAnexos%</div>%msg%</div>
                                <i class="fa fa-check chatStatusEntrega pull-right" status="%status%"></i>
                                <span class="message-time pull-right" title="%datatitle%">%data%</span>
                                <span class="msgApagada">Mensagem apagada</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>`;

        self.hSkelLink = `<a href="%link%" target="%target%"><i class="%icone%"></i>&nbsp;%textolink%</a>`;

        tinymce.init({
            selector: "#chatNovaMensagem"
            , language: 'pt_BR'
            , inline: true
            , menubar: false
            , paste_data_images: true
            , plugins: 'paste image imagetools lists'
            //,paste_as_text: true
            //,toolbar: 'removeformat bold bullist numlist'
            , toolbar: false
            , fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt"
            , removeformat: [
                {selector: 'b,strong,em,i,font,u,strike', remove: 'all', split: true, expand: false, block_expand: true, deep: true},
                {selector: 'span', attributes: ['style', 'class'], remove: 'empty', split: true, expand: false, deep: true},
                {selector: '*', attributes: ['style', 'class'], split: false, expand: false, deep: true}
            ]
            , setup: function(editor) {
                editor.on('KeyDown', function(event) {
                    if (event.keyCode == 13 && !event.shiftKey) {
                        event.preventDefault();
                        event.stopPropagation();
                        self.enviar();
                        return false;
                    }
                });
            }
            ,images_dataimg_filter: function(img) {
                debugger;
                return img.hasAttribute('internal-blob');
              }
            , images_upload_handler: function (blobInfo, success, failure) {
                debugger;
            }
        });
		
        //Controla upload do avatar e recupera a imagem
        self.oAvatarProfile.dropzone({
            previewTemplate: $("#cbDropzone").html()
            , url: "form/_arquivo.php"
            , idObjeto: gIdpessoa
            , tipoObjeto: 'pessoa'
            , tipoArquivo: 'AVATAR'
            , caminho: self.avatarDir
            , sending: function(file, xhr, formData) {
                formData.append("idobjeto", this.options.idObjeto);
                formData.append("tipoobjeto", this.options.tipoObjeto);
                formData.append("tipoarquivo", this.options.tipoArquivo);
                formData.append("caminho", this.options.caminho);
            }
            , success: function(file, response) {
                this.options.loopArquivos(response);
            }
            , init: function() {
                var thisDropzone = this;
                $.ajax({
                    url: this.options.url + "?tipoobjeto=" + this.options.tipoObjeto + "&idobjeto=" + this.options.idObjeto + "&tipoarquivo=" + this.options.tipoArquivo
                }).done(function(data, textStatus, jqXHR) {
                    thisDropzone.options.loopArquivos(data);
                })
            }
            , loopArquivos: function(data) {
                jResp = jsonStr2Object(data);
                if (jResp.length > 0) {
                    nomeArquivo = jResp[jResp.length - 1].nome;
                     if (nomeArquivo && self.oAvatarProfile) {
                        self.oAvatarProfile.attr("src", self.avatarDir + nomeArquivo);
                    }
                }
            }
        });
	self.started=true;
    }
    
    self.abrirContainerChat = function() {
        self.oChatContainer.show();
    };

    self.maximizar = function() {
        self.maximizado = true;
        self.oChatContainer.addClass("maximizar");
        self.oConversation.addClass("col-md-7");
        self.oChatContainer.find(".side").show(300);
        self.oIconeToggleContatos.removeClass("fa-chevron-left").addClass("fa-chevron-right");
    };

    self.minimizar = function() {
        self.maximizado = false;
        self.oChatContainer.removeClass("maximizar");
        self.oChatContainer.find(".conversation").removeClass("col-md-7");
        self.oChatContainer.find(".side").hide();
        self.oIconeToggleContatos.removeClass("fa-chevron-right").addClass("fa-chevron-left");
    };

    //Sinalizar mais 1 nova mensagem no badge de notificações
    self.incrementarBadge = function() {
        
        iBadge = self.oIBadge.attr("ibadge") || 0;
        iBadge++;
        sBadge = "";
        //Se for maior que o limite, mostrar caractere ou ícone especial ao invÃ©s da quantidade
        if (iBadge > self.iLimiteBadgeAtencao) {
            sBadge = "!";
            self.oIBadge.addClass("blink");
        } else {
            sBadge = iBadge;
            self.oIBadge.removeClass("blink");
        }

        self.oIBadge.attr("ibadge", iBadge).html(sBadge).show();
        self.oBadge.attr("title", iBadge + " mensagens não visualizadas").css("color", self.sCorBadgeAtivo).show();
    };

    self.incrementarBadgeTarefa = function(inMsg) {

        var msg = inMsg;
        //Inicializa mensagens
        self.oBadgeTarefa.data(self.oBadgeTarefa.data("mensagens") || {mensagens: {lidas: {}, naolidas: {}}});

        //if(!self.oBadgeTarefa.data("mensagens") || !self.oBadgeTarefa.data("mensagens").naolidas[msg.idimmsg]){
        if (msg.tipo == "T" || msg.tipo == "A") {
            if (msg.statustarefa == "A" && !self.oBadgeTarefa.data("mensagens").naolidas[msg.idimmsg]) {

                //Armazena a tarefa nao lida para nao incrementar novamente no próximo refresh
                self.oBadgeTarefa.data("mensagens").naolidas[msg.idimmsg] = msg;

                iBadge = self.oIBadgeTarefa.attr("ibadge") || 0;
                iBadge++;
                sBadge = "";
                //Se for maior que o limite, mostrar caractere ou ícone especial ao invÃ©s da quantidade
                if (iBadge > self.iLimiteBadgeAtencao) {
                    sBadge = "!";
                    self.oBadgeTarefa.addClass("blink");
                } else {
                    sBadge = iBadge;
                    self.oBadgeTarefa.removeClass("blink");
                }
                self.oIBadgeTarefa.attr("ibadge", iBadge).html(sBadge).show();
                self.oBadgeTarefa.attr("title", iBadge + " novas tarefas").css("color", self.sCorBadgeAtivo).css("cursor", "pointer").show();
            }
        }
		//Após carregar totalmente a página, libera o ícone de Compartilhar, pois não estava carregando os contatos (Lidiane - 26-02-2020)
		$('#cbCompartilharItem').css({ display: "" });
        $('.cbCompartilharItem').css('display','');
    };

    //Resetar badge
    self.resetBadge = function() {
        self.oIBadge.attr("ibadge", 0).hide();
        self.oBadge.css("color", self.sCorBadgeInativo).removeAttr("title");
    }

    //Remover 1 mensagem do badge de notificações
    self.decrementarBadge = function(inQt) {
        if (inQt == 0) {
            self.resetBadge();
        } else {
            iBadge = parseInt(self.oIBadge.attr("ibadge")) || 0;
            sBadge = "";
            //Se for maior que 99 mostrar aviso especial
            if (iBadge > self.iLimiteBadgeAtencao) {
                sBadge = "!";
            }
            //Se for maior que 0 manter cor ativa
            if (iBadge > 0) {
                iBadge = iBadge - inQt;
                sBadge = iBadge;
                self.oBadge.css("color", self.sCorBadgeAtivo);
            }
            //se for igual a 0 "desabilitar" badge
            if (iBadge === 0) {
                self.resetBadge();
            } else {
                self.oIBadge.attr("ibadge", iBadge).html(sBadge);
            }
        }
    };

    //Para cada mensagem nova, cria-se 1 item de notificação na listagem
    self.novoItemListaNotificacoes = function() {
        var args = arguments[0];

        //Verifica se a Msg já existe na lista suspensa
        if (self.oListaNotificacoes.find("li.cbItemNotificacao[idimmsg=" + args.idimmsg + "]").length <= 0) {

            //Verifica visibilidade conforme número máximo de mensagens visíveis
            iNotificacoes = self.oListaNotificacoes.find("li.cbItemNotificacao").length;
            if (iNotificacoes >= self.iMaxNotificacoesVisiveis) {
                var oEsconder = self.oListaNotificacoes.find("li.cbItemNotificacao").not(".hidden").filter(":last");
                oEsconder.addClass("hidden");
            }

            //Realiza a substituição de placeholders para cada parametro de entrada
            var oItemMsg = self.hHtmlListaNotificacaoItem;
            $.each(args, function(i, o) {
                if (typeof o !== "function") {
                    oItemMsg = oItemMsg.replace(new RegExp("%" + i + "%", "g"), args[i])
                }
            });

            $oItemMsg = $(oItemMsg);

            if (args.status == "N") {
                self.incrementarBadge();
                args.onNovaMensagem(args.idimmsg);
            }

            //Injeta o html com a nova notificacao
            $oItemMsg.insertAfter(self.oListaNotificacoesHeader);
        }
    }

    self.getContatos = function(inCallback) {

        if (tamanho(self.jContatos) === 0) {
            
            var form = new FormData();
            form.append("call", "contatos");
            form.append("_jwt", Cookies.get('jwt'));

            var settings = {
                "async": true,
                "crossDomain": true,
                "url": "inc/php/im/bim.php",
                "method": "POST",
                "headers": {
                    "cache-control": "no-cache"
                },
                "processData": false,
                "contentType": false,
                "mimeType": "multipart/form-data",
                "data": form
            }

            $.ajax(settings).done(function(response) {
                var contatos = jsonStr2Object(response);
                self.contatosRecuperados=true;//maf: desativar recuperacoes posteriores
                contatos = self.ordenarContatos(contatos);
                inCallback(contatos);
            });

        } else {
            inCallback(self.aContatos);
        }
    };

    self.verificaImagem = function(imgSrc) {
        
        var xhr = new XMLHttpRequest();
        xhr.open('HEAD', imgSrc, false);
        xhr.send();

        if (xhr.status == "404") {
            return false;
        } else {
            return true;
        }

    }

    self.montaContato = function(inContato) {
        
        var contato = inContato;
        var sStatus = "offline";

	//maf: o chat nao controlará mais o status do funcionario
        //if (contato.online === "1" || contato.objetocontato === "imgrupo") {
	if(contato.objetocontato === "imgrupo") {
		sStatus = "online";
        }

        var sAvatar = "";
        var sData = "";
        var sRotulo = "";
        //Ajusta propriedades conforme o tipo de destino: Contato (mensagem direta) ou Grupo
        if (contato.objetocontato === "pessoa") {

            sData = moment(contato.ultimoping || "").fromNow() || "";
            sAvatar = contato.arquivoavatar && self.contatosRecuperarAvatar ? self.avatarDir + contato.arquivoavatar : self.avatarDefault;
            
            //Verifica se a imagem foi carregada sem erro do servidor,
            //caso contrário define a default
            /*if (sAvatar != self.avatarDefault) {
                if (!self.verificaImagem(sAvatar)) {
                    sAvatar = self.avatarDefault;
                }
            }*/
            
            var mem = '';

        } else if (contato.objetocontato === "imgrupo") {
            
            sData = "";
            sAvatar = contato.arquivoavatar && self.contatosRecuperarAvatar ? self.avatarDir + contato.arquivoavatar : self.avatarDefaultGrp;
            
            var sMembros = "";
            var sSep = "";
            
            $.each(inContato.membros, function(i, c) {
                sMembros += sSep + c.nome;
                sSep = ", ";
            });

            var mem = (sMembros);

        } else {

            sData = "";
            sAvatar = self.avatarDefault;
            var mem = '';

        }

        sRotulo = contato.rotulo ? contato.rotulo.toUpperCase() : "Usuário [" + contato.idcontato + "] sem nome";
        str = sRotulo.replace(/<BR ?\/?>/g, " ");
        str = str.replace(" + ", " ");
        str = str + ' &#xA;&#xA; ' + mem;
		ramal = contato.ramalfixo.replace('<i class="fa fa-phone-square"></i>&nbsp','');
		

        var tit = sRotulo.replace(" - ", "<BR>+<BR>");

        var sContato = self.hSkelContato
            .replace(/%idcontato%/g, contato.idcontato)
            .replace(/%objetocontato%/g, contato.objetocontato)
            .replace(/%status%/g, sStatus)
            .replace(/%order%/g, contato.order)
            .replace(/%img%/g, sAvatar)
            .replace(/%rotulo%/g, tit)
            .replace(/%title%/g, str + ramal)
            .replace(/%data%/g, sData)
            .replace(/%ramalfixo%/g, contato.ramalfixo)
            .replace(/%tipocontato%/g, contato.tipocontato);

        //Inicializa dados de contato e mensagens
        var oContato = $(sContato).data({contato: contato}).data({mensagens: {lidas: {}, naolidas: {}}});
        return oContato;
    }

    self.montarContatos = function(inCallback) {
       
        var callback = inCallback;
        if(self.contatosRecuperados===true){//maf290321: Monta ps contatos somente 1 vez. Desativar montagens posteriores
            if (typeof callback === "function") {
                callback();
            }
        }else{
            //Recupera listagem de contatos passando um callback
            self.getContatos(function(inContatos) {
    
                $.each(inContatos, function(i, contato) {
                    //maf290321: @todo: remontar somente se houver algum contato nao existente. melhorar logica e local de atualizacao
                    if(tamanho(self.jContatos[contato.objetocontato + "_" + contato.idcontato])===0){
                        //Monta obj Json amigável com os contatos
                        self.jContatos[contato.objetocontato + "_" + contato.idcontato] = contato;
                    }
    
                    //Verifica se Ã© o próprio usuário
                    if (gIdpessoa == contato.idcontato && contato.objetocontato == "pessoa") {
                        // return true;
                    }
    
                    var tmpContato = self.montaContato(contato);
    
    					let tmpContatoMontado = undefined;
    					if (contato.tipocontato == "chat") {
    						tmpContatoMontado = self.oSideBar.find("#cbChatContatosOnline #contato_" + contato.objetocontato + "_" + contato.idcontato);
    						if(tmpContatoMontado.length == 0){
    							self.oGrupoContatosOnline.append(tmpContato);
    						}else{
    							//maf280321: replace. @todo: melhorar esta logica
    							tmpContatoMontado.html(tmpContato.html());
    						}
    					}
    
    					if (contato.tipocontato == "compartilhamento") {
    					 	tmpContatoMontado = self.oSideBar.find("#cbChatContatosOnlineNotificacao #contato_" + contato.objetocontato + "_" + contato.idcontato);
    						if(tmpContatoMontado.length == 0){
    							self.oGrupoContatosOnlineNotificacao.append(tmpContato);
    						}else{
    							//maf280321: replace. @todo: melhorar esta logica
    							tmpContatoMontado.html(tmpContato.html());
    						}
    					}
    
    
                });
    
                if (typeof callback === "function") {
                    callback();
                }
            });
        }
/* maf: @otod: mostrar foto ao passar o mouse
$('.avatar-icon img').on("mouseover", function(e){
	$img = $(e.target);
	if(!$img.data("plugin_webuiPopover")){
		$img.webuiPopover({
	        placement:'left',
	        width:200,
	        height:"auto",
	        padding:"0px",
	        animation:'pop',
	        trigger:"hover",
	        delay: {//show and hide delay time of the popover, works only when trigger is 'hover',the value can be number or object
        	    show: 1000,
	            hide: 300
	        },
	        content:function(){
	            var tmpHtml = $(this).html();
	            //$(this).html("");
        	    return `<img style="height:100%: margin:0 auto;width: 100%;" src=${this.src}>`;
	        }
    	})
	}
});
*/
    };

    self.ordenarContatos = function(inContatos) {
    	if(!inContatos)return false;   
        inContatos.sort(function(cAtual, cProx) {

            var msgA = cAtual.ultimamsg || "";
            var msgB = cProx.ultimamsg || "";

            if (msgA == "" && msgB == "") {

                if (cAtual.online == 1) {
                   
                    if (cAtual.online == cProx.online) {

                        if (cAtual.objetocontato == 'pessoa') {

                            if (cAtual.objetocontato > cProx.objetocontato) {
                                return -1;
                            } else {
                                
                                if (cAtual.rotulo > cProx.rotulo) {
                                    return +1;
                                } else {
                                    return -1;
                                }

                            }
                        }

                    } else {
                        
                        if (cAtual.objetocontato == 'pessoa') {
                            return -1;
                        } else {
                            return +1;
                        }
                    }

                } else if (cAtual.online == 0) {

                    if (cProx.objetocontato == 'pessoa') {
                        if (cProx.online == 0) {
                            if (cAtual.rotulo > cProx.rotulo) {
                                return +1;
                            } else {
                                return -1;
                            }
                        } else {
                            return +1;
                        }
                    } else {
                        return -1;
                    }

                }

            }

            if (msgA != "" && msgB == "") {
                return -1;
            }

            if (msgA == "" && msgB != "") {
                return +1;
            }

            if (msgA != "" && msgB != "") {
                if (msgA < msgB)
                    return +1;
                if (msgA > msgB)
                    return -1;
            }

            return 0;

        })
        self.aContatos=inContatos;
        return inContatos;
    };

    self.toggleContatos = function() {

        if (self.maximizado) {
            self.minimizar();
        } else {
            self.maximizar();
        }

    };

    self.preencheCabecalhoInfo = function(inContato) {

        if (inContato.objetocontato == "pessoa") {

            self.oCInfo.html(inContato.cargo);
            self.oCInfo2.html(inContato.cargo);

        } else if (inContato.objetocontato == "imgrupo") {
           
            var sMembros = "";
            var sSep = "";

            $.each(inContato.membros, function(i, c) {
                sMembros += sSep + c.nome;
                sSep = ", ";
            });

            self.oCInfo.data({"membros": inContato.membros}).html(sMembros);
            self.oCInfo2.data({"membros": inContato.membros}).html(sMembros);
        }
    }

    self.abrirConversa = function(inIdContato, inObjetocontato, inIdimmsg) {

        jContato = self.jContatos[inObjetocontato + "_" + inIdContato];

        self.oCAvatar.attr("src", (jContato.arquivoavatar ? self.avatarDir + jContato.arquivoavatar : self.avatarDefault));
        self.oCNome.html(self.montaLinkPessoa(jContato));
        self.preencheCabecalhoInfo(jContato);
        self.oChatNovaMensagem.val("");
        self.oChatBody.attr("objetocontato", jContato.objetocontato).attr("idcontato", jContato.idcontato);
        self.oChatBody.find(".message-body").remove();
        self.oConversation.removeClass("hidden");

        $("body").find(".lightBoxGroup").remove();

        self.conversa({
            idcontato: jContato.idcontato, 
            objetocontato: jContato.objetocontato,
            callback: function(inConversa) {

                var vBaloes = "";

                $.each(inConversa, function(i, msg) {
                    self.mostraBalao(msg);
                });

                self.abrirContainerChat();

                //Retira a marcação visual de todas as conversas e deixa somente 1 ativa (conversando)
                $("#cbChatPainelContatos [id*=contato_]").removeClass("conversando").filter("#contato_" + jContato.objetocontato + "_" + jContato.idcontato).addClass("conversando")

                //Marcar todas como lidas
                self.marcarLida({idcontato: jContato.idcontato, objetocontato: jContato.objetocontato, idimmsg: "*"});

                //Tenha empacotar cada imagem dentro de um Span,
                //para colocação de um elemento de edição, para ser possível excluir a imagem do db

                self.oChatBody.find("img").click(function() {
                    return self.opcoesImagemMsg(this);
                });

                self.scrollParaMensagem();
            }
        });
    };

    self.montaLinkPessoa = function(inContato) {
        switch (inContato.objetocontato) {
            case "pessoa":
                return `${inContato.rotulo}`;
                break;

            case "imgrupo":
                return inContato.rotulo;
                break;

            default:
                return inContato.rotulo;
                break;
        }
    };

    self.balao = function(inPar) {

        inPar.senderreceiver = (inPar.sender == "eu") ? "sender" : "receiver";
        inPar.idimmsg = inPar.idimmsg || null;
        inPar.idimmsgbody = inPar.idimmsgbody || null;
        inPar.msg = inPar.msg || null;
        inPar.criadoem = inPar.criadoem || null;
        inPar.modulo = inPar.modulo || null;
        inPar.modulopk = inPar.modulopk || null;

        //Melhorias na visualização de datas
        var dAgora = moment();
        var dMsg = moment(inPar.criadoem);
        var sData = (dAgora.diff(dMsg, "day") > 0) ? dMsg.format("L") : dMsg.format("HH:mm");
        var sDataTitle = dMsg.format("D/MM/YY HH:mm:ss");

        //Anexos
        var hLinks = "";

        if (tamanho(inPar._anexos) > 0) {

            $.each(inPar._anexos, function(idArq, arq) {

                if (arq.tipo === "L") {
                    
                    hLinks += self.hSkelLink
                        .replace(/%link%/g, arq.arq)
                        .replace(/%target%/g, "_blank")
                        .replace(/%icone%/g, "fa fa-external-link")
                        .replace(/%textolink%/g, arq.nome);
                }
            });
        }

        var strBalao = "";
        var mRight = (inPar.sender == "eu") ? "dropdown-menu-right" : "none";
        var displayapagar = (inPar.sender == "eu") ? "block" : "none";
        
        if (inPar.modulo != null && inPar.modulo == 'MSGREPLY') {
            
            let pessoa = '';
            let imageMini = null;
            let direcao = $(".message-body[idimmsgbody=" + inPar.modulopk + "]").attr('direcao');
            let msgReply = $(".message-body[idimmsgbody=" + inPar.modulopk + "] .message-text > p").text();
            let imgReply = $(".message-body[idimmsgbody=" + inPar.modulopk + "] .message-text > p > img");

            if (direcao == 'sender') {
                pessoa = "Voc&ecirc;";
            } else {
                pessoa = $("#chatNome").text();
            }

            if ($(imgReply).attr('src')) {
                imageMini = "<div style='width: 100px; display: block; background-size: cover; background-image: url("+$(imgReply).attr('src')+");'><div>";
                msgReply = "Foto"
            }
       
            strBalao = self.hSkelBalaoWithReply
                .replace(/%senderreceiver%/g, inPar.senderreceiver)
                .replace(/%idimmsg%/g, inPar.idimmsg)
                .replace(/%idimmsgbody%/g, inPar.idimmsgbody)
                .replace(/%status%/g, inPar.status)
                .replace(/%displayapagar%/g, displayapagar)
                .replace(/%statustarefa%/g, inPar.statustarefa)
                .replace(/%datatarefa%/g, inPar.datatarefa || "")
                .replace(/%imgMini%/g, imageMini || "")
                .replace(/%tipo%/g, inPar.tipo)
                .replace(/%msg%/g, inPar.msg)
                .replace(/%msgPessoa%/g, pessoa)
                .replace(/%msgReply%/g, msgReply)
                .replace(/%msgReplyBodyId%/g, inPar.modulopk)
                .replace(/%data%/g, sData)
                .replace(/%datatitle%/g, sDataTitle)
                .replace(/%msgAnexos%/g, hLinks)
                .replace(/%menuright%/g, mRight);

        } else {

            strBalao = self.hSkelBalao.replace(/%senderreceiver%/g, inPar.senderreceiver)
                .replace(/%idimmsg%/g, inPar.idimmsg)
                .replace(/%idimmsgbody%/g, inPar.idimmsgbody)
                .replace(/%status%/g, inPar.status)
                .replace(/%displayapagar%/g, displayapagar)
                .replace(/%statustarefa%/g, inPar.statustarefa)
                .replace(/%datatarefa%/g, inPar.datatarefa || "")
                .replace(/%tipo%/g, inPar.tipo)
                .replace(/%msg%/g, inPar.msg)
                .replace(/%data%/g, sData)
                .replace(/%datatitle%/g, sDataTitle)
                .replace(/%msgAnexos%/g, hLinks)
                .replace(/%menuright%/g, mRight);
        }
         
        if (inPar.idimgrupo && inPar.sender !== "eu") {

            var nomeSender = "";
            var oMembroGrupo = self.jContatos["imgrupo_" + inPar.idimgrupo].membros[inPar.sender];
            
            if (oMembroGrupo) {
                strBalao = strBalao.replace("%sendernome%", `<span style="color:#${oMembroGrupo.bg}">${oMembroGrupo.nome}</span>`);
            } else {

                var oMembroPessoa = self.jContatos["pessoa_" + inPar.sender];

                if (typeof oMembroPessoa !== "undefined") {
                    strBalao = strBalao.replace("%sendernome%", `<span style="color:#cc0000">${oMembroPessoa.rotulo}</span>`);
                }

            }

        } else {
            strBalao = strBalao.replace("%sendernome%", "");
        }

        return strBalao;
    }

    self.mostraBalao = function(inMsg) {
        
        sSenderReceiver = (inMsg.sender == "eu") ? "sender" : "receiver";
        
        var hBalao = self.balao(inMsg);
        $(hBalao).appendTo(self.oChatBody);

        //Após criar o balão de mensagem, se neste balão tiver alguma imagem
        //Irá colocá-las no mesmo lightbox-group
        if (inMsg.msg && inMsg.msg.includes('<img')) {
            
            let imgs = self.oChatBody.find(".message-body[idimmsg=" + inMsg.idimmsg + "] .message-text").find('img');

            imgs.each(function() {

                let data = $(this).attr('src');

                if (data && data != undefined && data != null) {
                    $("body").append('<a class="lbox'+inMsg.idimmsg+' lightBoxGroup" href="'+data+'" data-lightbox="lightBoxGroup"></a>');
                }

            });

        }

    }

    self.scrollParaMensagem = function(inidimmsg) {
        
        var idimmsg = inidimmsg || false;
        
        if (!idimmsg) {
            self.oChatBody.animate({scrollTop: self.oChatBody.prop("scrollHeight")}, 500);
        }

    }

    self.scrollParaReply = function(inIdimmsgbody) {
   
        var offset = $(".message-body[idimmsgbody=" + inIdimmsgbody + "]").prop('offsetTop');
        $("#chatBody").animate({scrollTop: offset - 60}, 300);

    }

    self.conversa = function(inPar) {

        inPar.idcontato = inPar.idcontato || false;
        inPar.objetocontato = inPar.objetocontato || false;
        inPar.idimsgm = inPar.idimsgm || null;
        inPar.callback = inPar.callback || null;

        var form = new FormData();
        form.append("call", "conversa");
        form.append("sender", inPar.idcontato);
        form.append("objetocontato", inPar.objetocontato);
        form.append("idimmsg", inPar.idimsgm);
        form.append("_jwt", Cookies.get('jwt'));

        var settings = {
            "async": true,
            "crossDomain": true,
            "url": "inc/php/im/bim.php",
            "method": "POST",
            "headers": {
                "cache-control": "no-cache"
            },
            "processData": false,
            "contentType": false,
            "mimeType": "multipart/form-data",
            "data": form
        }

        $.ajax(settings).done(function(response) {
            inPar.callback(jsonStr2Object(response));
        });
    }

    self.enviar = function(inOpt) {
        
        inOpt = inOpt || {};

        let replyId = localStorage.getItem("replyId");

        var isReplying = false;
        var txtMsg = inOpt.msg || tinymce.get("chatNovaMensagem").getContent();

        var sContatos = inOpt.contatos ? JSON.stringify(inOpt.contatos) : `[{"idcontato":"${self.oChatBody.attr("idcontato")}","objetocontato":"${self.oChatBody.attr("objetocontato")}"}]`;
        var sModulo = inOpt.modulo ? inOpt.modulo : "";        
        var sMsgtipo = inOpt.msgtipo ? inOpt.msgtipo : "M";
        var sModulopk = inOpt.modulopk ? inOpt.modulopk : "";
        var sDatatarefa = inOpt.datatarefa ? inOpt.datatarefa : "";

        if (self.html2Txt(txtMsg).length > 0 || self.html2Ent(txtMsg).length > 0) {

            var form = new FormData();

            if (replyId != null && replyId != undefined && !isNaN(replyId)) {
                sModulo = 'MSGREPLY';
                sModulopk = replyId;
                isReplying = true;
            }

            if(txtMsg.substr(0, 3) !== "<p>" && txtMsg.substr(txtMsg.length-4,txtMsg.length) !== "</p>"){
                txtMsg = "<p>"+txtMsg+"</p>";
            }

            form.append("call", "enviar");
            form.append("contatos", sContatos);
            form.append("msgtipo", sMsgtipo);
            form.append("msg", txtMsg);
            form.append("datatarefa", sDatatarefa);
            form.append("modulopk", sModulopk);
            form.append("modulo", sModulo);


            //Se for enviado o idimmsgbody, será um compartilhamento de mensagem. 
            //O corpo da mensagem não será criado
            if (inOpt.idimmsgbody) {
                form.append("idimmsgbody", idimmsgbody);
            }

            form.append("_jwt", Cookies.get('jwt'));

            var settings = {
                "async": true,
                "crossDomain": true,
                "url": "inc/php/im/bim.php", //self.endpointEnviar,
                "method": "POST",
                "headers": {
                    "cache-control": "no-cache",
                    "jwt" :localStorage.getItem("jwt"),
                },
                "processData": false,
                "contentType": false,
                "mimeType": "multipart/form-data",
                "data": form,
                beforeSend: function() {
                    self.desabilitaEditor(true);
                }
            };

            $.ajax(settings).done(function(data, textStatus, jqXHR) {

                self.desabilitaEditor(false);
                jResponse = jsonStr2Object(data);

                if (jResponse.code == "MSG_ENVIADA" || jResponse.code == "MSG_ENVIADA_OFFLINE") {
                    //Verifica se trata-se de uma chamada customizada ao mÃ©todo: enviar({...:...})
  
                    if (isReplying) {
                        $('._1vDUw').remove();
                    }
                    
                    if (tamanho(inOpt) === 0) {

                        if (replyId != null && replyId != undefined && !isNaN(replyId)) {

                            sBalao = self.balao({
                                sender: "eu",
                                msg: txtMsg,
                                modulo: 'MSGREPLY',
                                modulopk: sModulopk,
                                idimmsg: jResponse.idimmsg,
                                criadoem: jResponse.criadoem,
                                idimmsgbody: jResponse.idimmsgbody
                            });
                            
                            localStorage.removeItem("replyId");

                        } else {

                            sBalao = self.balao({
                                sender: "eu",
                                msg: txtMsg,
                                idimmsg: jResponse.idimmsg,
                                criadoem: jResponse.criadoem,
                                idimmsgbody: jResponse.idimmsgbody
                            });

                        }

                        //Limpa o texto
                        self.oChatNovaMensagem.html("");
                        //Insere por ultimo
                        $(sBalao).appendTo(self.oChatBody);
                        
                         //Após criar o balão de mensagem, se neste balão tiver alguma imagem
                        //Irá colocá-las no mesmo lightbox-group
                        if (txtMsg.includes('<img')) {
                            
                            var img = self.oChatBody.find(".message-body[idimmsgbody=" + jResponse.idimmsgbody + "] .message-text > p > img");
                            var data = $(img).attr('src');
                            
                            if (data && data != undefined && data != null) {
                                $("body").append('<a class="lbox'+jResponse.idimmsg+' lightBoxGroup" href="'+data+'" data-lightbox="lightBoxGroup"></a>');
                            }

                        }

                        //Tenha empacotar cada imagem dentro de um Span, para colocação de um
                        //elemento de edição, para ser possível excluir a imagem do db
                        //self.oChatBody.find("img").wrap("<span class='imagem'></span>");
                        console.log(self.oChatBody.find("img"));
                        
                        self.oChatBody.find("img").click(function() {
                            return self.opcoesImagemMsg(this);
                        });

                        //Scroll
                        self.scrollParaMensagem();
                        //Mover o contato para cima
                        self.moverContatos({[self.oChatBody.attr("objetocontato") + "_" + self.oChatBody.attr("idcontato")]: "topo"});
                    }
                   
                    //Verifica se será anexado um "arquivo" (que pode ser código html)
                    if (inOpt.anexo) {
                        self.novoAnexo({idimmsgbody: jResponse.idimmsgbody, anexo: inOpt.anexo});
                    }

                    //Executa callback
                    if (typeof inOpt.callback === "function") {
                        inOpt.callback(true, data, textStatus, jqXHR);
                    }

                } else if (jResponse.code == "MANUTENCAO") {
                    alertAtencao("Em manutenção. Aguarde!");
                }else if (jResponse.erro){
                    alertErro(jResponse.erro);
                } else {
                    console.warn(data);
                    if(typeof inOpt.callback == "function"){
                        inOpt.callback(false, data, textStatus, jqXHR);
                    }
                }
            });
        }
    };

    self.novoAnexo = function(inOpt) {

        inOpt = inOpt || {};

        var sIdimmsgbody = inOpt.idimmsgbody;
        var sArquivo = inOpt.anexo.link;
        var nomeArquivo = inOpt.anexo.nome;

        var form = new FormData();
        form.append("call", "anexo");
        form.append("arquivo", sArquivo);
        form.append("nome", nomeArquivo);
        form.append("idimmsgbody", sIdimmsgbody);
        form.append("_jwt", Cookies.get('jwt'));

        var settings = {
            "async": true,
            "crossDomain": true,
            "url": "inc/php/im/bim.php",
            "method": "POST",
            "headers": {
                "cache-control": "no-cache"
            },
            "processData": false,
            "contentType": false,
            "mimeType": "multipart/form-data",
            "data": form,
            beforeSend: function() {

            }
        };

        $.ajax(settings).done(function(data, textStatus, jqXHR) {

            jResponse = jsonStr2Object(data);

            if (jResponse.code == "ANEXO_OK") {
                console.log("Arquivo anexado com sucesso");
            } else {
                alertErro("Erro ao anexar arquivo");
                console.error(data);
            }
        });
    };

    self.desabilitaEditor = function(inDesabilita) {

        if (inDesabilita) {
            tinymce.activeEditor.setMode('readonly');
            tinymce.activeEditor.$().addClass("fade");
        } else {
            tinymce.activeEditor.setMode('design');
            tinymce.activeEditor.$().removeClass("fade");
        }

    };

    self.historico = function(inPar) {

        if (!inPar || !inPar.callback) {
            console.error("chat.historico: Um callback deve ser fornecido para este mÃ©todo.")
            return false;
        }

        var acao = inPar.acao || "historico";

        //Dispara montagem dos contatos
        self.montarContatos(function() {

            //Recupera histórico de todas as conversas
            var form = new FormData();
            form.append("call", acao);
            form.append("_jwt", Cookies.get('jwt'));

            var settings = {
                global: false,
                "async": true,
                "crossDomain": true,
                "url": "inc/php/im/bim.php",
                "method": "POST",
                "headers": {
                    "cache-control": "no-cache"
                },
                "processData": false,
                "contentType": false,
                "mimeType": "multipart/form-data",
                "data": form
            }

            $.ajax(settings).done(function(response) {
                inPar.callback(jsonStr2Object(response));
            });
        });
    };

    self.opcoesImagemMsg = function(inImg) {

        $img = $(inImg);
        //Recupera o id da mensagem e do corpo
        vIdimmsg = $img.closest("[idimmsg]").attr("idimmsg");
        vIdimmsgbody = $img.closest("[idimmsg]").attr("idimmsgbody");

        chat.expandirImagemDb(vIdimmsg,vIdimmsgbody,this);

        /*$img = $(inImg);
        //Recupera o id da mensagem e do corpo
        vIdimmsg = $img.closest("[idimmsg]").attr("idimmsg");
        vIdimmsgbody = $img.closest("[idimmsg]").attr("idimmsgbody");
    
        //Salva uma propriedade unica na imagem para poder recupera-la
        $img.attr("idimmsg", vIdimmsg);

        if (vIdimmsg && vIdimmsgbody) {
            return "<span class='nowrap pointer'\
                onclick='chat.expandirImagemDb("+vIdimmsg+","+vIdimmsgbody+",this)'>\
                <i class='fa fa-eye'></i> Expandir imagem</span>\
                <br><br><span class='nowrap pointer hoververmelho'\
                onclick='chat.excluirImagemDb(" + vIdimmsg + "," + vIdimmsgbody + ",this)'>\
                <i class='fa fa-trash'></i> Excluir imagem</span>";
        }*/

    }

    self.expandirImagemDb = function(inIdimmsg, inIdimmsgbody, inObjClique) {

        var oBalaoMsg = self.oChatBody.find(".message-body[idimmsg=" + inIdimmsg + "] .message-text");
        var hBalao = oBalaoMsg.html();
        var img = self.oChatBody.find(".message-body[idimmsg=" + inIdimmsg + "] .message-text > p > img");
        var data = $(img).attr('src');

        $(inObjClique).closest(".webui-popover[id*=webuiPopover]").remove();
        $(".lbox"+inIdimmsg).click();
        
    };

    self.excluirImagemDb = function(inIdimmsg, inIdimmsgbody, inObjClique) {
        //Esconde o popup
        $(inObjClique).closest(".webui-popover[id*=webuiPopover]").remove();
        //Remove a imagem do DOM
        self.oChatBody.find("img[idimmsg=" + inIdimmsg + "]").remove();
        //Salva o novo conteúdo no db
        var oBalaoMsg = self.oChatBody.find(".message-body[idimmsg=" + inIdimmsg + "] .message-text");
        var hBalao = oBalaoMsg.html();

        //Realiza o Update do CORPO da mensagem somente
        var form = new FormData();
        form.append("call", "alterar");
        form.append("idimmsgbody", inIdimmsgbody);
        form.append("message", hBalao);
        form.append("_jwt", Cookies.get('jwt'));

        var settings = {
            "async": true,
            "crossDomain": true,
            "url": "inc/php/im/bim.php",
            "method": "POST",
            "headers": {
                "cache-control": "no-cache"
            },
            "processData": false,
            "contentType": false,
            "mimeType": "multipart/form-data",
            "data": form
        };

        $.ajax(settings).done(function(response) {

            jResponse = jsonStr2Object(response);

            if (jResponse.code == "MSG_ALTERADA") {
                //atualiza somente o HTML com o que veio do DB, para conferÃªncia
                oBalaoMsg.html(jResponse.msg);
                //Tenha empacotar cada imagem dentro de um Span, para colocação de um elemento de edição,
                // para ser possível excluir a imagem do db
               // oBalaoMsg.find("*").filter("img").wrap("<span class='imagem'></span>");
                alertSalvo("Imagem excluída!");
            }

        });

    };
 
    self.marcarLida = function(inPar) {
       
        if (inPar.idimmsg) {

            var form = new FormData();
            form.append("call", "ler");

            if (inPar.idcontato) {
                form.append("idcontato", inPar.idcontato);
                form.append("objetocontato", inPar.objetocontato);
            }

            form.append("idimmsg", inPar.idimmsg);
            form.append("_jwt", Cookies.get('jwt'));

            var settings = {
                "async": true,
                "crossDomain": true,
                "url": "inc/php/im/bim.php",
                "method": "POST",
                "headers": {
                    "cache-control": "no-cache"
                },
                "processData": false,
                "contentType": false,
                "mimeType": "multipart/form-data",
                "data": form
            };

            $.ajax(settings).done(function(response) {
                
                if (inPar.idimmsg == "*") {
                    //Desmarcar tudo do sender
                    var oBadge_contato = $("#cbChatContatosOnline div[tipocontato='chat'] #badge_contato_" + inPar.objetocontato + "_" + inPar.idcontato);

                    //Decrementar o total de mensagens não lidas
                    self.decrementarBadge(tamanho($("#contato_" + inPar.objetocontato + "_" + inPar.idcontato).data("mensagens").naolidas));

                    //Resetar o badge e resetar mensagens não lidas no objeto DATA do Jquery
                    $("#contato_" + inPar.objetocontato + "_" + inPar.idcontato).data("mensagens").naolidas = {};
                    oBadge_contato.removeClass("hidden").attr("imensagens", 0).html("");

                } else if (inPar.idimmsg !== "*") {
                    //Desmarcar tudo do sender
                    var oBadge_contato = $("#badge_contato_" + inPar.objetocontato + "_" + inPar.idcontato);

                    //Decrementar o total de mensagens não lidas

                    if ($("#cbChatContatosOnline div[tipocontato='chat'] #contato_" + inPar.objetocontato + "_" + inPar.idcontato) &&
                        $("#cbChatContatosOnline div[tipocontato='chat'] #contato_" + inPar.objetocontato + "_" + inPar.idcontato).data("mensagens") && 
                        $("#cbChatContatosOnline div[tipocontato='chat'] #contato_" + inPar.objetocontato + "_" + inPar.idcontato).data("mensagens").naolidas) {

                        self.decrementarBadge(tamanho($("#cbChatContatosOnline div[tipocontato='chat'] #contato_" + inPar.objetocontato + "_" + inPar.idcontato).data("mensagens").naolidas));
                    }
                    
                    //Resetar o badge e resetar mensagens não lidas no objeto DATA do Jquery
                    if ($("#contato_" + inPar.objetocontato + "_" + inPar.idcontato) && 
                        $("#contato_" + inPar.objetocontato + "_" + inPar.idcontato).data("mensagens") &&
                        $("#contato_" + inPar.objetocontato + "_" + inPar.idcontato).data("mensagens").naolidas) {

                        $("#contato_" + inPar.objetocontato + "_" + inPar.idcontato).data("mensagens").naolidas = {};
                        
                        oBadge_contato.removeClass("hidden").attr("imensagens", 0).html("");
                    }
                }
            });
        }
    }

    self.notificacao = function() {

        args = arguments[0];

        if (args.beep) {
		try{
            		gSomNovaMensagem.play();
		}catch(e){
			null;
		}
        }

        Notification.requestPermission().then(function(result) {
            
            var sTitulo = "";
            var sCorpo = "";
            var sIcone = "";
            
            if (args.iNovasMensagens == 1) {

                sTitulo = self.jContatos[self.msg2hid(args.ultimaMensagem)].rotulo;
                sCorpo = self.html2Txt(self.html2Ent(args.ultimaMensagem.msg || ""));
                sIcone = self.jContatos[self.msg2hid(args.ultimaMensagem)].arquivoavatar ? "./upload/" + self.jContatos[self.msg2hid(args.ultimaMensagem)].arquivoavatar : self.avatarDefault;
            
            } else {

                sTitulo = "(" + args.iNovasMensagens + " mensagens)";
                sCorpo = self.html2Txt(self.html2Ent(args.ultimaMensagem.msg || ""));
                //console.log(args.ultimaMensagem);

                if (typeof self.jContatos[self.msg2hid(args.ultimaMensagem)] === 'undefined') {
                    sIcone = 'inc/img/avatardefault.png';
                } else {
                    sIcone = self.jContatos[self.msg2hid(args.ultimaMensagem)].arquivoavatar ? "./upload/" + self.jContatos[self.msg2hid(args.ultimaMensagem)].arquivoavatar : self.avatarDefault;
                }

            }
            //Mostrar notificação do browser somente se o chat estiver fechado
            //@todo: verificar preferencias do usuário
            //if(!chat.oChatBody.is(":visible")){
            notificacao({
                titulo: sTitulo,
                corpo: sCorpo,
                icone: sIcone,
                id: new Date().getTime()
            });
            //}
        });
    };

    self.html2Ico = function(inHtml) {
        return inHtml.replace(/<img\b[^>]*>/ig, "<i class='fa fa-image'></i>").trim();
    };

    self.html2Ent = function(inHtml) {
        return inHtml.replace(/<img\b[^>]*>/ig, "\u{1F5CB}").trim();
    };

    self.html2Txt = function(inHtml) {
        inHtml = inHtml.replace(/(<([^>]+)>)/ig, "").replace(/&nbsp;/ig, " ").trim();
        inHtml = htmldecode(inHtml);
        return inHtml;
    };

    self.pesquisarContato = function(inObj) {

        $oSearch = $(inObj);
        strSearch = $oSearch.val();

        if (strSearch.trim().length <= 0) {

            $(".sideBar .contato").removeClass("ocultoSearch").show(self.velAnimacaoShow);

        } else {

            $.each($(".sideBar .contato"), function(i, contato) {

                $contato = $(contato);
                vSelecionado = $contato.hasClass("selecionado") ? true : false;//Somente contatos

                if (fullTextCompare(strSearch, contato.title, true) || vSelecionado) {
                    $contato.removeClass("ocultoSearch").show(self.velAnimacaoShow);
                } else {
                    $contato.addClass("ocultoSearch").hide(self.velAnimacaoHide);
                }

            });

        }
    };

    self.trataEvento = function(inMsg) {

        var jEvento = jsonStr2Object(inMsg.msg);

        if (jEvento) {

            if (jEvento.online) {
                //Move o contato para o grupo online. @todo: verificar se existem mensagens nao lidas
                self.moverContatos({["pessoa_" + jEvento.online]: "online"});
            } else if (jEvento.offline) {
                //Move o contato para o grupo offline. @todo: verificar se existem mensagens nao lidas
                self.moverContatos({["pessoa_" + jEvento.offline]: "offline"});
            } else if (jEvento.apagarmsg) {
                //Apagar mensagem
                self.oChatBody.find("[idimmsgbody=" + jEvento.apagarmsg + "]").attr("tipo", "X").addClass("highlight");
            }

        }

    };

    /*
     * Mover os contatos agrupando conforme o status e as notificações
     * @param json {objetocontato_idcontato: ["online"||"offline"||"onlineNotificacao"||"offlineNotificacao"]}
     */
    self.moverContatos = function(inContatos) {
        $.each(inContatos, function(objetocontatoIdcontato, grupoStatus) {
            $contato = $("." + objetocontatoIdcontato);
            switch (grupoStatus) {
                case "topo":
                    $contato
                            .hide()
                            .prependTo(self.oGrupoContatosOnline)
                            .fadeIn('slow');
                    break;
                case "offline":
                    $contato
                            .removeClass("online")
                            .addClass("offline")
                            .attr("status", "offline");
                            //.hide()
                            //.prependTo(self.oGrupoContatosOffline)
                            //.fadeIn('slow');
                    break;
                case "online":
			$contato
				.removeClass("offline")
				.addClass("online")
				.attr("status", "online");
				//.hide().closest("#cbChatContatosOnline")
				//.prepend($contato)
				//.find($contato.fadeIn(500));
                    break;
                default:
                    console.warn("chat.moverContatos: status não previsto: " + grupoStatus);
                    break;
            }
            //console.log(objetocontatoIdcontato + "-" + grupoStatus);
        });
    };

    self.toggleFullscreen = function(inForce) {

        if (!inForce && self.oChatContainer.hasClass("fullscreen")) {

            self.oChatContainer.removeClass("fullscreen");
            self.oChatFullscreen.removeClass("fa-window-restore").addClass("fa-window-maximize");
            
        } else {

            document.title = ".: Chat :.";
            self.oChatContainer.addClass("fullscreen");
            self.oChatFullscreen.removeClass("fa-window-maximize").addClass("fa-window-restore");

        }
    };

    self.imageUpload = function() {

        console.log("Entrei");
        console.log($("#hiddenFile"));

        $("#hiddenFile").click();
        
    };

    //Monta um atributo id (#) de html, para recuperar o objeto do contato referente Ã© mensagem
    self.msg2hid = function(inMsg) {

        var hIdContato = "";

        if (!inMsg.idimgrupo) {
            hIdContato = "pessoa_" + inMsg.sender;
        } else if (inMsg.idimgrupo) {
            hIdContato = "imgrupo_" + inMsg.idimgrupo;
        } else {
            console.error("js: chat: msg2hid: valor msg.idimgrupo não esperado. Impossível recuperar o id do contato.");
        }

        return hIdContato;
    };

    self.msg2tarefa = function(inIdimmsgbody, inAcao) {

        var form = new FormData();
        form.append("call", "msg2tarefa");

        form.append("idimmsgbody", inIdimmsgbody);
        form.append("transformar", inAcao);
        form.append("_jwt", Cookies.get('jwt'));

        var settings = {
            "async": true,
            "crossDomain": true,
            "url": "inc/php/im/bim.php",
            "method": "POST",
            "headers": {
                "cache-control": "no-cache"
            },
            "processData": false,
            "contentType": false,
            "mimeType": "multipart/form-data",
            "data": form
        };

        $.ajax(settings).done(function(response) {
            if (jsonStr2Object(response).code == "TRANS_MSG_OK") {
                self.oChatBody.find("[idimmsgbody=" + inIdimmsgbody + "]").attr("tipo", "T");
            }
        });
    }

    self.responder = function(inIdimmsgbody, element) {

        $('._1vDUw').remove();

        var responseContainer = `<div class="_1vDUw">
            <div class="rstyJ" style="transform: translateY(0px);">
                <div class="_2bdRS">
                    <div class="_21gzc">
                        <div class="_1iJeo _1VeYA" role="">
                            <span class="bg-color-2 EebcE"></span>
                            <div class="_3sEgI">
                                <div class="_1lKj0">
                                    <div class="_111ze color-2" role="">
                                        <span dir="auto" class="_2a1Yw"></span>
                                    </div>
                                    <div class="Y9G3K _B0pu">
                                        <span class="Y9G3Ki"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="_3SX5f">
                    <div role="button" id="closeBtn">
                        <span data-icon="x" class="">
                            <svg id="Layer_1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path opacity=".45" fill="#263238" d="M19.1 17.2l-5.3-5.3 5.3-5.3-1.8-1.8-5.3 5.4-5.3-5.3-1.8 1.7 5.3 5.3-5.3 5.3L6.7 19l5.3-5.3 5.3 5.3 1.8-1.8z"></path></svg>
                        </span>
                    </div>
                </div>
            </div>
            <object class="_3lztd" data="about:blank" type="text/html"></object>
        </div>
        <script>
            $(document).ready(function() {
                $('#closeBtn').on('click', function() {
                    $('._1vDUw').remove();
                    localStorage.removeItem("replyId");
                });
            });	
        </script>`;

        $(responseContainer).insertAfter($("#chatBody"));
        
        let direcao = $(".message-body[idimmsgbody=" + inIdimmsgbody + "]").attr('direcao');
        let idMsg = $(".message-body[idimmsgbody=" + inIdimmsgbody + "]").attr('idimmsg');
        let idMsgBody = $(".message-body[idimmsgbody=" + inIdimmsgbody + "]").attr('idimmsgbody');
        let text = $(".message-body[idimmsgbody=" + inIdimmsgbody + "] .message-text > p").text();
        
        //let image = $(element).parent().parent().next().find(".message-text > p > img");

        let image = $(".message-body[idimmsgbody=" + inIdimmsgbody + "] .message-text > p > img");
        
        if ($(image).attr('src')) {
            let imageMini = "<div style='width: 100px; display: block; background-size: cover; background-image: url("+$(image).attr('src')+");'><div>";
            $(imageMini).insertAfter($("._3sEgI"));
        }
        
        if (direcao == 'sender') {
            $("._2a1Yw").text("Eu");
        } else {
            $("._2a1Yw").text($("#chatNome").text());
        }
       
        $(".Y9G3Ki").text(text);

        localStorage.setItem("replyId", idMsgBody);
        
    }

    self.apagarMsg = function(inIdimmsgbody, inCallback) {
        
        var form = new FormData();
        form.append("call", "apagar");
        form.append("idimmsgbody", inIdimmsgbody);
        form.append("_jwt", Cookies.get('jwt'));

        var settings = {
            "async": true,
            "crossDomain": true,
            "url": "inc/php/im/bim.php",
            "method": "POST",
            "headers": {
                "cache-control": "no-cache"
            },
            "processData": false,
            "contentType": false,
            "mimeType": "multipart/form-data",
            "data": form
        };

        $.ajax(settings).done(function(response) {

            if (jsonStr2Object(response).code == "X_MSG_OK") {

                self.oChatBody.find("[idimmsgbody=" + inIdimmsgbody + "]").attr("tipo", "X");

                if (typeof inCallback == "function") {
                    inCallback();
                }
            }
        });
    }

    //maf: a opcao de filtros está separada do refresh, para automatizar quem está online/offline
    self.filtrarContatos = function(inFiltro) {

        $filtro = $(inFiltro);
        //Aplica/remove a "seleção" para o item selecionado
        if ($filtro.hasClass("selecionado")) {
            $filtro.removeClass("selecionado");
        } else {
            $filtro.addClass("selecionado");
        }

        self.refreshFiltroContatos();

    }

    self.recuperarAvatar = function() {
        $.each(self.jContatos, function(i, contato) {

            let tmpContatoMontado=undefined;
            if (contato.tipocontato == "chat") {
                tmpContatoMontado = self.oSideBar.find("#cbChatContatosOnline #contato_" + contato.objetocontato + "_" + contato.idcontato+" .sideBar-avatar .avatar-icon img");
                tmpContatoMontado.attr("src", self.avatarDir+contato.arquivoavatar);
            }
        })
    }


    self.refreshFiltroContatos = function(){

        //Se existir no mánimo 1 filtro selecionado, altera o ícone
        $filtros = $("#menuFiltros");
        $selecionados = $filtros.find(".selecionado");
        $filtroI = $("#menuFiltrosIcone");

        //Monta uma estutura de atributos selecionados para ser comparada em seguida com cada contato
        if ($selecionados.length > 0) {

            jFiltrosSel = {};

            $.each($selecionados, function(i, o) {

                var jfiltro = $(o).data();
                //Recupera o tipo do filtro (status,objetocontato,etc...)
                var kfiltro = Object.keyAt(jfiltro, 0);
                var vfiltro = jfiltro[Object.keyAt(jfiltro, 0)];

                jFiltrosSel[kfiltro] = jFiltrosSel[kfiltro] || {};
                jFiltrosSel[kfiltro][vfiltro] = true;
            });
            //Compor a coleção de atributos selecionados
            $filtros.data({filtrosSelecionados: jFiltrosSel});
            $filtroI.addClass("laranja");
        } else {
            $filtros.data({filtrosSelecionados: false});
            $filtroI.removeClass("laranja");
        }

	let iVisiveis=0;

        //Loop em cada contato
        $.each(chat.oChatPainelContatos.find("[idcontato]"), function(i, o) {

            $o = $(o);
            vStatus = $o.attr("status");
            vObjeto = $o.attr("objetocontato");
            $menufiltros = $("#menuFiltros");

            compStatus = true;
            compObjeto = true;

            //Compara o status com os filtros selecionados
            if ($menufiltros.data().filtrosSelecionados && $menufiltros.data().filtrosSelecionados.status && !$menufiltros.data().filtrosSelecionados.status[vStatus]) {
                compStatus = false;
            }

            //Compara o objetocontato com os filtros selecionados
            if ($menufiltros.data().filtrosSelecionados && $menufiltros.data().filtrosSelecionados.objetocontato && !$menufiltros.data().filtrosSelecionados.objetocontato[vObjeto]) {
                compObjeto = false;
            }

            //console.log(`status:${compStatus} objeto:${compObjeto}`);
            if (compStatus && compObjeto) {
		iVisiveis++;
                $o.show();
            } else {
                $o.hide();
            }
        });

	if(iVisiveis>0){
		iVisiveis = $("#cbChatContatosOnline .contato[objetocontato=pessoa]:visible").length;
		$("#legendaFiltros").html(iVisiveis+" contatos selecionados").show();
	}else{
		//$("#cbChatContatosOnline .contato[objetocontato=pessoa]:visible").length
		$("#legendaFiltros").html("").hide();
	}

    }

    self.mostrarLista = function() {};
    self.marcarTodasComoLidas = function() {};
    self.abrirMensagemEspecifica = function() {};

    self.mostrarMensagensAnteriores = function() {};
    self.mostrarPainelDeContatos = function() {};

    self.montarHeaderContatos = function() {};
    self.montarHeaderConversa = function() {};
    self.enviarEmoticon = function() {};

    self.chatContainer = `<div id="cbChatContainer">
        <div id="cbChatCorpo" class="row">
            <div class="col-sm-5 side">
            <div class="side-one">
                <div class="row heading">
                <div class="col-sm-12 col-xs-12 heading-avatar">
                    <div class="heading-avatar-icon">
                        <img src="inc/img/avatarprofile.png" id="avatarProfile" title="Clique para alterar a foto de seu perfil">&nbsp;&nbsp;<span style='color:red'>&nbsp;&nbsp;</span>
                    </div>
                </div>
                <div class="col-sm-1 col-xs-1  heading-dot  pull-right hidden">
                    <i class="fa fa-ellipsis-v fa-2x  pull-right" aria-hidden="true"></i>
                </div>
                <div class="col-sm-2 col-xs-2 heading-compose  pull-right hidden">
                    <i class="fa fa-comments fa-2x  pull-right" aria-hidden="true"></i>
                </div>
                </div>

                <div class="row searchBox">
                <div class="col-sm-12 searchBox-inner">
                    <div class="form-group has-feedback barraPesquisaContato">
                        <input id="searchText" type="text" class="form-control" name="searchText" placeholder="Pesquisar contatos" onkeyup="chat.pesquisarContato(this)">
                        <span class="glyphicon glyphicon-search form-control-feedback cinzaclaro"></span>
                    </div>
                    <div id="barraPesquisaFiltrar" class="dropdown">
                        <i id="menuFiltrosIcone" style="margin-left: 50% !important;"  class="fa fa-filter fa-2x cinzaclaro hoverlaranja pointer" data-toggle="dropdown"></i>
                        <ul class="dropdown-menu" id="menuFiltros">
                            <li class="categoria">Status:</li>
                            <li class="nowrap" data-status="online" onclick="chat.filtrarContatos(this)"><span>Online</span></li>
                            <li class="nowrap" data-status="offline" onclick="chat.filtrarContatos(this)"><span>Offline</li>
                            <li class="categoria">Tipo:</li>
                            <li class="nowrap" data-objetocontato="imgrupo" onclick="chat.filtrarContatos(this)"><span>Grupos</span></li>
                            <li class="nowrap" data-objetocontato="pessoa" onclick="chat.filtrarContatos(this)"><span>Pessoas</li>
                        </ul>
                    </div>

                </div>
		<div class="col-sm-12" id="legendaFiltros"></div>
                </div>
                <div id="cbChatPainelContatos" class="row sideBar">
                    <div id="cbChatContatosOnlineNotificacao" style="display:none"></div>
                    <div id="cbChatContatosOfflineNotificacao"></div>
                    <div id="cbChatContatosOnline"></div>
                    <div id="cbChatContatosOffline"></div>
                </div>
            </div>
            </div>

            <div class="col-sm-12 conversation hidden">
            <div class="row heading">
                <div class="col-sm-1 col-xs-1">
                    <i class="fa fa-chevron-left fa-2x fade" aria-hidden="true" onclick="chat.toggleContatos()" id="chatIconeToggleContatos"></i>
                </div>
                <div class="col-sm-2 col-md-1 col-xs-3 heading-avatar">
                <div class="heading-avatar-icon">
                    <img src="inc/img/avatardefault.png" id="chatAvatar">
                </div>
                </div>
                <div class="col-sm-6 col-xs-6 heading-name" id="chatCabecalho">
                <div class="heading-name-meta" id="chatNome"></div>
                <div class="heading-name-data" id="chatCabInfo"></div>
                <div class="heading-name-data" id="chatCabInfo2"></div>
                <span class="heading-online" id="chatStatus"></span>
                </div>
                <div class="col-sm-1 col-xs-1 heading-dot pull-right" title="Fechar">
                    <i class="fa fa-close fa-2x  pull-right fade" aria-hidden="true" onclick="$('#cbChatContainer').hide()"></i>
                </div>
                <div class="col-sm-1 col-xs-1 heading-dot pull-right" title="Maximizar/Restaurar">
                    <i class="fa fa-window-maximize fa-2x  pull-right fade" aria-hidden="true" onclick="chat.toggleFullscreen()" id="cbChatFullscreen"></i>
                </div>

                <div class="col-sm-1 col-xs-1 heading-dot pull-right" title="Anexar Imagem">
                    <i class="fa fa-image fa-2x  pull-right fade" aria-hidden="true" onclick="chat.imageUpload()" id="cbChatImageUpload"></i>
                </div>
            </div>

            <div class="row message" id="chatBody" onmouseenter="chat.oChatBody.find('.message-body[status=N]').attr('status','L')">
                <div class="messagebg"></div>
                <div class="row message-previous hidden" id="chatMsgAnteriores">
                <div class="col-sm-12 previous">
                    <a onclick="previous(this)" id="ankitjain28" name="20">
                    Mostrar mensagens anteriores
                    </a>
                </div>
                </div>
            </div>
            <div id="chatResposta" class="row reply">
                <div class="col-sm-1 col-xs-1 reply-emojis hidden">
                <i class="fa fa-smile-o fa-2x"></i>
                </div>
                <div class="col-sm-10 col-xs-10 reply-main">
                    <div class="form-control" rows="1" id="chatNovaMensagem" onclick="self.marcarLida({objetocontato:chat.oChatBody.attr('objetocontato'),idcontato:chat.oChatBody.attr('idcontato'),idimmsg:'*'});"></div>
                </div>
                <input type="file" id="hiddenFile" style="display:none">

                <div class="col-sm-1 col-xs-1 reply-send" title="Enviar" onclick="chat.enviar()">
                <i class="fa fa-send fa-3x" aria-hidden="true"></i>
                </div>
            </div>
            </div>
            <i class="fa fa-close fa-2x pull-right pointer" style="margin-right: 10px;margin-top: 5px;color: silver;" onclick="$('#cbChatContainer').hide()"></i>
        </div>
    </div>
    <script>

        function readURL(input) {

            if (input.files && input.files[0]) {
            
                var reader = new FileReader();
        
                reader.onload = function(e) {

                    var myImage = $('<img/>');
                    myImage.attr('src', e.target.result);
                    $('#chatNovaMensagem').append(myImage);
                    input.files = null;

                }

                reader.readAsDataURL(input.files[0]);

            }
        }

        $(document).ready(function() {
            
            $('.message-text img').on('click', function() {
                
                $('.enlargeImageModalSource').attr('src', $(this).attr('src'));
                $('#enlargeImageModal').modal('show');
            });

            $("#hiddenFile").change(function() {
                readURL(this);
            });
        });
    </script>`;

    self.toggleMsgTarefa = function() {

        $oTbPopup = $("#chatTbNovaMensagemPopup");

        if ($oTbPopup.attr("immsgtipo") == "M" || $oTbPopup.attr("immsgtipo") == "A") {
            $oTbPopup.attr("immsgtipo", "T");
        } else {
            $oTbPopup.attr("immsgtipo", "M");
        }
    };

    self.toggleMsgAssinatura = function() {

        $oTbPopup = $("#chatTbNovaMensagemPopup");

        if ($oTbPopup.attr("immsgtipo") == "M" || $oTbPopup.attr("immsgtipo") == "T") {
            $oTbPopup.attr("immsgtipo", "A");
        } else {
            $oTbPopup.attr("immsgtipo", "M");
        }
    };

    self.enviarMsgModal = function(msg) {
        //Recupera todos os contatos do modal

        // GVT 27/05/2020 - Alterado o ID para busca de contatos. Antes: cbChatPainelContatos, Depois: cbChatPainelContatosCopia. Pois havia dois ID's no momento da execução
        $oContatos = CB.oModal.find("#cbChatPainelContatosCopia .contato.selecionado");
        $aContatos = [];

        //Separa os contatos que foram selecionados e coloca em array
        var iC = 0;
        $.each($oContatos, function(i, o) {
            iC++;
            $o = $(o);
            $aContatos.push({"idcontato": $o.attr("idcontato"), "objetocontato": $o.attr("objetocontato")})
            //console.log(o);
        });

        //Recupera a mensagem
        //var txtMsg = tinymce.get("chatNovaMensagemPopup").getContent();
        var txtMsg = msg;
        //Recupera o tipo da mensagem
        var sMsgtipo = $("#chatTbNovaMensagemPopup").attr("immsgtipo");
    
/* maf: o tipo é sempre M. está fixo no atributo do elemento #chatTbNovaMensagemPopup
        if (sMsgtipo != "T" && sMsgtipo != "M" && sMsgtipo != "A") {
            sMsgtipo = "M";
        }
*/     
/* maf: o tipo é sempre M. está fixo no atributo do elemento #chatTbNovaMensagemPopup
        var $oDtIni = $("#chatIDataIniMsg");

        var sDataIni = sMsgtipo == "T" ? $oDtIni.val() : "";
*/
         var sDataIni = "";
/* maf: o tipo é sempre M. está fixo no atributo do elemento #chatTbNovaMensagemPopup
        //Verifica se o usuário informou a data para a tarefa
        if (sMsgtipo === "T" && sDataIni.length <= 1) {
            $oDtIni.removeClass("highlight").addClass("highlight");
            //console.log(txtMsg);
            alertAtencao("Informe corretamente uma data para a tarefa!");
            return false;
        }
*/
        //Verifica se o usuário digitou algo
        if (self.html2Txt(txtMsg).length <= 0) {
            $("#chatNovaMensagemPopup").removeClass("highlight").addClass("highlight");
            alertAtencao("Informe a mensagem corretamente");
            return false;
        }

        //Verifica se o usuário selecionou algum contato
        if (iC == 0) {
            alertAtencao("Selecione os contatos desejados");
            return false;
        }

        //Recupera o anexo
        var hAnexo = $("#chatArqPopup").html();
        var vNomeAnexo = self.html2Txt($("#chatArqPopupNome").html());
        //
        var moduloPk = getUrlParameter(CB.jsonModulo.parget);
        var moduloNome = CB.modulo;
        //Envia a mensagem
        chat.enviar({
            contatos: $aContatos
            , msg: "<p>"+txtMsg+"</p>"
            , msgtipo: sMsgtipo
            , datatarefa: sDataIni
            , modulopk: moduloPk
            , modulo: moduloNome
            , anexo: {"link": hAnexo, "nome": vNomeAnexo}
            , callback: function(sucesso, data, textStatus, jqXHR) {
                if (sucesso) {
                    alertSalvo("Mensagem enviada");
                    CB.oModal.modal('hide');
                } else {
                    alertErro(data);
                }
            }
        });
    };

    self.toggleOpcoesMsg = function(inMsgMsg) {
        console.log(inMsgMsg);
    };

    return self;
}


//Excluir este mÃ©todo após cache
function removerParametroGet(key, url) {
    
    if (!url)
        url = window.location.href;

    var hashParts = url.split('#');

    var regex = new RegExp("([?&])" + key + "=.*?(&|#|$)", "i");

    if (hashParts[0].match(regex)) {
        //REMOVE KEY AND VALUE
        url = hashParts[0].replace(regex, '$1');

        //REMOVE TRAILING ? OR &
        url = url.replace(/([?&])$/, '');

        //ADD HASH
        if (typeof hashParts[1] !== 'undefined' && hashParts[1] !== null)
            url += '#' + hashParts[1];
    }

    return url;
}

refresh = function(inAcao) {

    chat.historico({
        acao: inAcao || "refresh",
        callback: function(inMsgs) {

            var iNovamsg = 0;
            var iNovatarefa = 0;
            var iTarefavencida = 0;
            var ultimaMsg = false;
            var ultimaTarefa = false;
            var oContatosMover = {};
            var dataHoje = (new Date()).toISOString().split("T")[0];

            $.each(inMsgs, function(i, msg) {
                //Caso não exista o sender pela immsgbody, 
                //ignorar o erro (msgbody apagado) e tentar montar a próxima mensagem
                if (msg.tipo == "M" && !msg.sender) {
                    return true;
                }

                //Caso seja uma tarefa criada pelo usuário para ele mesmo
                if (msg.sender == "eu" && (msg.tipo == "T" || msg.tipo == "A")) {
                    
                    self.incrementarBadgeTarefa(msg);

                    if (msg.datatarefa) {
                        
                        var hIcone = `<i class="cbAvatar tipo${msg.tipo} ${(msg.tipo == "M") ? "" : "fa fa-envelope-o"}" style="background-image:url(${(msg.tipo == "M") ? (avatarContato !== "") ? chat.avatarDir + avatarTarefa : chat.avatarTarefa : ""});"></i>`;

                        //var oBadge_contato=$("#badge_contato_"+self.msg2hid(msg));
                        //Incrementar o badge e armazenar mensagem não 
                        //lida no objeto DATA do Jquery
                        if (msg.tipo == "T") {
                            var datadaTarefa = (new Date(msg.datatarefa)).toISOString().split("T")[0];
                        } else {
                            var datadaTarefa = (new Date(msg.datatarefa)).toISOString().split("A")[0];
                        }

                        if (datadaTarefa < dataHoje && msg.statustarefa == 'A') {
                            iNovatarefa++;
                            //$("#contato_"+self.msg2hid(msg)).data("mensagens").naolidas[msg.idimmsg]=msg;
                            //var iMensagensNovas=parseInt(oBadge_contato.attr("imensagens"))+1;
                            //oBadge_contato.removeClass("hidden").attr("imensagens",iMensagensNovas).html(iMensagensNovas);
                            //oContatosMover[self.msg2hid(msg)]="topo";

                            //Armazena última mensagem
                            ultimaTarefa = msg;
                            //self.incrementarBadge();
                        }
                    }

                    //return true;//Interrompe aqui mas continua o loop
                } else {

                    //Caso exista alguma mensagem não lida que o usuário não pode ver mais
                    if (msg.tipo == "M" && msg.sender !== "eu" && !self.jContatos[self.msg2hid(msg)]) {
                        console.error("js: refresh: callback: Existiam mensagens não lidas que o usuário não pode ver mais. Foram marcadas como lidas no servidor");
                        self.marcarLida({idcontato: msg.sender, objetocontato: msg.objetocontato, objetocontato: msg.objetocontato, idimmsg: msg.idimmsg});
                        return true;
                    }

                    //Conforme o tipo de mensagem realiza tratamento específico
                    if (msg.tipo == "E") {

                        self.trataEvento(msg);

                    } else if (msg.tipo == "M" || msg.tipo == "T" || msg.tipo == "A") {

                        //Incrementa badge de mensagem que foi transformada em tarefa
                        if (msg.tipo == "T") {

                            self.incrementarBadgeTarefa(msg);

                            if (msg.datatarefa) {
                                
                                if (typeof self.jContatos[self.msg2hid(msg)] === 'undefined') {
                                    
                                    var rotuloContato = '';
                                    var avatarContato = 'inc/img/avatardefaultgrp.png';

                                } else {

                                    var rotuloContato = self.jContatos[self.msg2hid(msg)].rotulo || "?";
                                    var avatarContato = self.jContatos[self.msg2hid(msg)].arquivoavatar || "";
                                
                                }

                                var hIcone = `<i class="cbAvatar tipo${msg.tipo} ${(msg.tipo == "M") ? "" : "fa fa-envelope-o"}" style="background-image:url(${(msg.tipo == "M") ? (avatarContato !== "") ? chat.avatarDir + avatarTarefa : chat.avatarTarefa : ""});"></i>`;

                                //var oBadge_contato=$("#badge_contato_"+self.msg2hid(msg));
                                //Incrementar o badge e armazenar mensagem não lida no objeto DATA do Jquery
                                var datadaTarefa = (new Date(msg.datatarefa)).toISOString().split("T")[0];
                                if (datadaTarefa < dataHoje && msg.statustarefa == 'A') {
                                    
                                    iNovatarefa++;
                                   
                                    //Armazena última mensagem
                                    ultimaTarefa = msg;
                                    //self.incrementarBadge();
                                }
                            }


                        } else if (msg.tipo == "A") {

                            self.incrementarBadgeTarefa(msg);

                            if (msg.datatarefa) {

                                var rotuloContato = self.jContatos[self.msg2hid(msg)].rotulo || "?";
                                var avatarContato = self.jContatos[self.msg2hid(msg)].arquivoavatar || "";
                                var hIcone = `<i class="cbAvatar tipo${msg.tipo} ${(msg.tipo == "M") ? "" : "fa fa-envelope-o"}" style="background-image:url(${(msg.tipo == "M") ? (avatarContato !== "") ? chat.avatarDir + avatarTarefa : chat.avatarTarefa : ""});"></i>`;

                                //var oBadge_contato=$("#badge_contato_"+self.msg2hid(msg));
                                //Incrementar o badge e armazenar mensagem não lida no objeto DATA do Jquery
                                var datadaTarefa = (new Date(msg.datatarefa)).toISOString().split("A")[0];
                                if (datadaTarefa < dataHoje && msg.statustarefa == 'A') {
                                    iNovatarefa++;
                               
                                    ultimaTarefa = msg;
                                    
                                }

                            }

                        } else {

                            if (self.jContatos[self.msg2hid(msg)]) {//Verifica se AINDA Ã© um contato válido (existente)

                                var rotuloContato = self.jContatos[self.msg2hid(msg)].rotulo || "?";
                                var avatarContato = self.jContatos[self.msg2hid(msg)].arquivoavatar || "";
                                var hIcone = `<i class="cbAvatar tipo${msg.tipo} ${(msg.tipo == "M") ? "" : "fa fa-envelope-o"}" style="background-image:url(${(msg.tipo == "M") ? (avatarContato !== "") ? chat.avatarDir + avatarContato : chat.avatarDefault : ""});"></i>`;

                                var oBadge_contato = $("#cbChatContatosOnline div[tipocontato='chat'] #badge_contato_" + self.msg2hid(msg));
                                //Incrementar o badge e armazenar mensagem não lida no objeto DATA do Jquery

                                if (msg.status == "N" && msg.sender !== "eu" && !$("#contato_" + self.msg2hid(msg)).data("mensagens").naolidas[msg.idimmsg]) {
                                    iNovamsg++;

                                    $("#contato_" + self.msg2hid(msg)).data("mensagens").naolidas[msg.idimmsg] = msg;
                                    var iMensagensNovas = parseInt(oBadge_contato.attr("imensagens")) + 1;
                                    oBadge_contato.removeClass("hidden").attr("imensagens", iMensagensNovas).html(iMensagensNovas);
                                    oContatosMover[self.msg2hid(msg)] = "topo";
                                    console.log(self.msg2hid(msg));
                                    //Armazena Ãltima mensagem
                                    ultimaMsg = msg;
                                    self.incrementarBadge();
                                }

                                //Verifica se o chat está aberto para mostrar imediatamente a nova mensagem
                               // if (chat.oChatBody.is("#cbChatContatosOnline  [tipocontato='chat'] [idcontato=" + msg.sender + "]:visible")) {
								if(chat.oChatBody.is("[idcontato="+msg.sender+"]:visible")){
                                    if (chat.oChatBody.find(".message-body[idimmsg=" + msg.idimmsg + "]").length <= 0) {
                                        self.mostraBalao(msg);
                                        self.scrollParaMensagem();
                                        self.marcarLida({idcontato: msg.sender, objetocontato: jContato.objetocontato, idimmsg: msg.idimmsg});


                                    }
                                }
                            }
                        }
                      
                    } else if (msg.tipo == "P") {
                        //Mensagens do PABX
                        if(!msg.msg){
                            console.warn("Mensagem inválida do Pabx");
                            console.warn(msg);
                            return false;
                        }else{
                            jPbx=jsonStr2Object(msg.msg);
                            if(jPbx.ring){
                                let sTit="Ligação recebida "+moment().format("DD/MM H:m");
                                let ico=jPbx.origem&&jPbx.origem=="URA"?self.icoRingExt:self.icoRingInt;
                                console.log(sTit);
                                notificacao({
                                    titulo: sTit,
                                    corpo: jPbx.ring,
                                    icone: ico,
                                    id: new Date().getTime()
                                });
                            }
                        }
                    } else if (msg.tipo == "X") {
                        null;
                    } else {
                        console.warn("Tipo de mensagem não previsto: " + msg.tipo);
                    }
                }
            });
            //Depois de ler todas as mensagens
            if (iNovamsg > 0) {

                self.notificacao({
                    beep: true, 
                    iNovasMensagens: iNovamsg, 
                    ultimaMensagem: ultimaMsg, 
                    mensagens: inMsgs, 
                    icone: self.jContatos[self.msg2hid(ultimaMsg)].arquivoavatar, 
                    callback: function(inArgsNotificacao) {
                        
                        self.abrirContainerChat();
                        self.maximizar();
                        self.montarContatos();
                       
                        if (inArgsNotificacao.ultimaMensagem.idimgrupo) {
                            var objContato = "imgrupo";
                            var idContato = inArgsNotificacao.ultimaMensagem.idimgrupo;
                        } else {
                            var objContato = "pessoa";
                            var idContato = inArgsNotificacao.ultimaMensagem.sender;
                        }

                        self.abrirConversa(idContato, objContato);

                        $('#chatNovaMensagem').focus();
                        
                    }
                });

                self.moverContatos(oContatosMover);
                
            }

            if (iNovatarefa > 0 && false) {
                self.notificacao({
                    beep: true, 
                    iNovasMensagens: iNovatarefa, 
                    ultimaMensagem: ultimaTarefa, 
                    mensagens: inMsgs, 
                    icone: self.avatarTarefa, 
                    callback: function(inArgsNotificacao) {

                        $(CB.oModuloHeader, CB.oModuloHeaderBg).addClass('hidden');

                        CB.loadUrl({urldestino: 'form/_dashboard.php'});
                       
                        if (inArgsNotificacao.ultimaMensagem.idimgrupo) {
                            var objContato = "imgrupo";
                            var idContato = inArgsNotificacao.ultimaMensagem.idimgrupo;
                        } else {
                            var objContato = "pessoa";
                            var idContato = inArgsNotificacao.ultimaMensagem.sender;
                        }

                    }
                });
            }
        }
    });
};

function htmldecode(str) {

    var d = document.createElement("div");
    d.innerHTML = str;

    return typeof d.innerText !== 'undefined' ? d.innerText : d.textContent;

}

function cloneContatosChat(inOpt) {

    inOpt = inOpt || {};

    //Copia o painel de contatos
    $oContatos = $($("#cbChatCorpo .side").html());

    //Altera o evento de clique
    $.each($($oContatos).find("[idcontato]"), function(i, oc) {

        $oc = $(oc);
        //Elimina o evento onclick e cria outro, preparado para callback
        $oc.attr("onclick", "").on("click", function() {

            $o = $(this);

            if ($o.hasClass("selecionado")) {

                $o.removeClass("selecionado");

            } else {

                $o.addClass("selecionado");

            }
            //Executa ação customizada após clique no contato
            if (inOpt.onclick && typeof inOpt.onclick === "function") {
                inOpt.onclick($o);
            }
        });

        $oc.find(".badge").html("");

    });

    // GVT 27/05/2020 - Alterado o ID para busca de contatos. Antes: cbChatPainelContatos, Depois: cbChatPainelContatosCopia. Pois havia dois ID's no momento da execução
    $oContatos
        .find("#cbChatPainelContatos")
        .attr("id","cbChatPainelContatosCopia")
        //.find("#cbChatContatosOnlineNotificacao")
        //.attr("id","cbChatContatosOnlineNotificacaoCopia")
        ;

    return $oContatos;
}

function cloneNovaMensagemChat(inOpt) {

    $oNMsg = $($("#chatResposta").html());

    return $oNMsg;
}

function compartilharItem() {

    $oContatosChat = cloneContatosChat({
        onclick: function(inContato) {
            console.log(inContato);
        }
    });

    $tbNovaMsg = $(`
	    <div id="chatArqPopup" class="hidden"></div>
        <table width="100%" id="chatTbNovaMensagemPopup" immsgtipo="M">
            <tr>
                <td><label>Assunto:</label></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="4">
                        <textarea rows="5" id="chatNovaMensagemPopup"></textarea>
                </td>
                <td>
                    <i class="fa fa-send fa-3x cinza fade pointer" aria-hidden="true" title="Compartilhar/Enviar" onclick="chat.enviarMsgModal($('#chatNovaMensagemPopup').val());"></i>
                </td>
            </tr>
            <tr>
                <td class=""></td>
            </tr>
            <tr>
                <td>
                    <div id="chatArqPopupNome">
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="btn-group" role="group" aria-label="...">

                        <button onclick="chat.toggleMsgAssinatura()" type="button" class="_btnAssinatura btn btn-default fa fa-edit hoverlaranja pointer" title="Transformar em assinatura" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>
                        <input id="chatIDataIniMsg" class="calendario pull-left" style="width:100px" placeholder="Prazo">
                    </div>
                </td>
            </tr>
        </table>
    `);

    //Altera o cabeçalho do modal
    strCabecalho = "<label class='fa fa-comment-o'></label>&nbsp;&nbsp;Compartilhar item:";
    //$("#cbModalTitulo").html(strCabecalho);

    //Limpando o popup
    //$('#cbModal #cbModalCorpo').find("*").remove();

/*
    $('#cbModal #cbModalCorpo')
        .append($tbNovaMsg)
        .append("<hr>")
        .append($oContatosChat);
*/

    // GVT 27/05/2020 - Alterado a construção do modal, seguindo o padrão da função modal() do Carbon.
    CB.modal({
        titulo: strCabecalho,
        corpo: [$tbNovaMsg, "<hr>", $oContatosChat], //Dica: Fornecer HTML (string) ou elementos Jquery
        classe: 'cinquenta',
        //aoFechar: () => { tinymce.remove("#chatNovaMensagemPopup")} // Função callback para fechar o editor quando o modal for fechar "on('hide.bs.modal')".
    });


    //Editor rte
    /*
    tinymce.init({
        language: 'pt_BR',
        selector: "#chatNovaMensagemPopup", 
        inline: true, 
        menubar: false, 
        toolbar: false,
        paste_data_images: true, 
        plugins: 'paste image imagetools lists',
        // maf 27/05/2020: o menubar nao esta sendo mostrado, portanto isto é desnecessario
        fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt", 
        removeformat: [
            {selector: 'b,strong,em,i,font,u,strike', remove: 'all', split: true, expand: false, block_expand: true, deep: true},
            {selector: 'span', attributes: ['style', 'class'], remove: 'empty', split: true, expand: false, deep: true},
            {selector: '*', attributes: ['style', 'class'], split: false, expand: false, deep: true}
        ],
        //,paste_as_text: true
        //,toolbar: 'removeformat bold bullist numlist', 
        //maf: nao se trata de chat, portanto verificar necessidade de codigo para shift + enter
        setup: function(editor) {
            editor.on('KeyDown', function(event) {
                if (event.keyCode == 13 && !event.shiftKey) {
                    event.preventDefault();
                    event.stopPropagation();
                    self.enviar();
                    return false;
                }
            });
        }
    });*/

/*
    $('#cbModal')
        .addClass('quarenta')
        .modal()
        .on('hidden.bs.modal', function (e) {
            tinymce.remove("#chatNovaMensagemPopup")
        });*/

    var sLink = window.location.search;

    // Prepara a descrição para o hyperlink
    var sADesc = removerParametroGet("_modulo", sLink);
    sADesc = removerParametroGet("_acao", sADesc);
    sADesc = sADesc.replace(/^\?/, "");
    sADesc = (CB.jsonModulo.rotulomenu || document.title || "") + ": " + sADesc;

    sADesc = '<a href="' + sLink + '" target="_blank" class="pointer"><i class="fa fa-paperclip">&nbsp;</i>' + sADesc + '</a><p></p>'
   
    $("#chatArqPopup").html(sLink);
    $("#chatArqPopupNome").html(sADesc);
}


function compartilharTarefa() {

    $aContatos = [];

    //Recupera todos os contatos do modal
    $oContatos = CB.oModal.find("#cbChatPainelContatos .contato.selecionado");

    //Separa os contatos que foram selecionados e coloca em array
    var iC = 0;

    $.each($oContatos, function(i, o) {
        iC++;
        $o = $(o);
        $aContatos.push({"idcontato": $o.attr("idcontato"), "objetocontato": $o.attr("objetocontato")})
    });

    //Recupera a mensagem
    var txtMsg = tinymce.get("chatNovaMensagemPopup").getContent();

    //Recupera o idimmsgbody
    idimmsgbody = $("#chatTbNovaMensagemPopup").attr("idimmsgbody");

    //Verifica se o usuário digitou algo
    if (self.html2Txt(txtMsg).length <= 0) {
        $("#chatNovaMensagemPopup").removeClass("highlight").addClass("highlight");
        alertAtencao("Informe a mensagem corretamente");
        return false;
    }

    //Verifica se o usuário selecionou algum contato
    if (iC == 0) {
        alertAtencao("Selecione os contatos desejados");
        return false;
    }

    //Compartilhar a mensagem
    chat.enviar({
        msgtipo: 'T', 
        msg: txtMsg,
        contatos: $aContatos, 
        idimmsgbody: idimmsgbody,
        callback: function(sucesso, data, textStatus, jqXHR) {
            if (sucesso) {
                alertSalvo("Mensagem compartilhada");
                CB.oModal.modal('hide');
            } else {
                alertErro(data);
            }
        }
    });
}
