var Chat = function(){

	self=							this;//Este objeto

	self.init=function(){
		//Caso não seja inicializado corretamente retornar false
		if(arguments.length==0 || typeof arguments !== "object"){
			console.log("JS Chat: nenhum parâmetro informado");
			return false;
		}
		//Inicializa o container html
		$("body").append(self.chatContainer);

		//Inicializa os objetos padrão
		self.oNotificacoes=				$(arguments[0].notificacoes||"");
		self.oBadge=					$(arguments[0].badge||"");
		self.oIBadge=					$(arguments[0].iBadge||"");
		self.oBadgeTarefa=				$(arguments[0].badgeTarefa||"");
		self.oIBadgeTarefa=				$(arguments[0].iBadgeTarefa||"");
		self.oListaNotificacoes=		$(arguments[0].listaNotificacoes||"");
		self.oListaNotificacoesHeader=	$(arguments[0].listaNotificacoesHeader||"");
		self.oListaNotificacoesFooter=	$(arguments[0].listaNotificacoesFooter||"");
		self.oChatContainer=			$(arguments[0].chatContainer||"");
		self.oSideBar=					self.oChatContainer.find(".sideBar");

		self.oChatPainelContatos=				$("#cbChatPainelContatos");
		self.oGrupoContatosOnlineNotificacao=	$("#cbChatContatosOnlineNotificacao");
		self.oGrupoContatosOfflineNotificacao=	$("#cbChatContatosOfflineNotificacao");
		self.oGrupoContatosOnline=				$("#cbChatContatosOnline");
		self.oGrupoContatosOffline=				$("#cbChatContatosOffline");
		self.oChatFullscreen=					$("#cbChatFullscreen");

		self.oConversation=				self.oChatContainer.find(".conversation");
		self.oAvatarProfile=			self.oChatContainer.find("#avatarProfile");
		self.oChatBody=					self.oChatContainer.find("#chatBody");
		self.oChatMsgAnteriores=		self.oChatContainer.find("#chatMsgAnteriores");
		self.oCCabecalho=				self.oChatContainer.find("#chatCabecalho");
		self.oCAvatar=					self.oChatContainer.find("#chatAvatar");
		self.oCNome=					self.oChatContainer.find("#chatNome");
		self.oCInfo=					self.oChatContainer.find("#chatCabInfo");
		self.oCStatus=					self.oChatContainer.find("#chatStatus");
		self.oIconeToggleContatos=		self.oChatContainer.find("#chatIconeToggleContatos");
		self.oChatNovaMensagem=			self.oChatContainer.find("#chatNovaMensagem");
		self.hHtmlListaNotificacaoItem= arguments[0].htmlListaNotificacaoItem||false;			

		self.intervaloRefresh=			15000
		self.maximizado=				false;
		self.jContatos=					{};
		self.aContatos=					[];
		self.avatarDefault=				"inc/img/avatardefault.png";
		self.avatarDefaultGrp=				"inc/img/avatardefaultgrp.png";
		self.avatarTarefa=				"inc/img/avatartarefa.png";
		self.avatarDir=					"upload/";

		//Configuracoes
		self.sCorBadgeAtivo="#fff";
		self.sCorBadgeInativo="#fff";
		self.iLimiteBadgeAtencao=99;//A partir desta quantidade o badge começa a piscar
		self.iMaxNotificacoesVisiveis=(gChatMaxHistorico!==""?gChatMaxHistorico:10);//Número máximo de notificações no histórico
		self.velAnimacaoShow=null;
		self.velAnimacaoHide=null; 

		self.hSkelContato= `
	<div id="contato_%objetocontato%_%idcontato%" class="row sideBar-body contato %status%" idcontato="%idcontato%" objetocontato="%objetocontato%" status="%status%" title="%title%" onclick="chat.abrirConversa(%idcontato%,'%objetocontato%');$('#chatNovaMensagem').focus();" order="%order%">
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
				<span id="ramalfixo" class="time-meta pull-left">%ramalfixo%</span><span class="time-meta pull-right">%data%</span>
				</div>
				<div id="badge_contato_%objetocontato%_%idcontato%" class="badge fundovermelho pull-right sideBar-naoLidas hidden" imensagens="0"></div>
			</div>
		</div>
		<i class="fa fa-check-square fa-2x checkContato verde hidden"></i>
	</div>`;

		self.hSkelBalao=`<div class="row message-body" idimmsg="%idimmsg%" idimmsgbody="%idimmsgbody%" direcao="%senderreceiver%" status="%status%" tipo="%tipo%" statustarefa="%statustarefa%" style="background-color:%bgcolor%;">
		<div class="col-sm-12 messageMain%senderreceiver%">
			<i class="fa fa-asterisk chatNovaMsg" status="%status%"></i>
			<div class="%senderreceiver%">
				<div class="dropdown msgOpcoes">
					<i id="opcoesMsg_%idimmsg%" idimmsg="%idimmsg%" class="fa fa-chevron-down fa-2x pointer cinza"  data-toggle="dropdown" title="Alterar opções" onclick="chat.toggleOpcoesMsg('opcoesMsg_%idimmsg%')"></i>
					<ul class="dropdown-menu %menuright%">
						<li class="fonte09 nowrap" onclick="chat.msg2tarefa(%idimmsgbody%,true)"><span><i class="fa fa-calendar-check-o laranja"></i> Transformar em tarefa</span></li>
						<li class="fonte09 nowrap"  onclick="chat.apagarMsg(%idimmsgbody%)"><span><i class="fa fa-trash vermelho"></i> Apagar</span></li>
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

		self.hSkelLink=`<a href="%link%" target="%target%"><i class="%icone%"></i>&nbsp;%textolink%</a>`;

		//Editor rte
		tinymce.init({
			selector: "#chatNovaMensagem"
			,language: 'pt_BR'
			,inline: true /* não usar iframe */
			,menubar: false
			//,paste_as_text: true
			,paste_data_images: true
			,plugins: 'paste image imagetools lists'
			//,toolbar: 'removeformat bold bullist numlist'
			,toolbar: false
			,fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt"
			,removeformat: [
				{selector: 'b,strong,em,i,font,u,strike', remove : 'all', split : true, expand : false, block_expand: true, deep : true},
				{selector: 'span', attributes : ['style', 'class'], remove : 'empty', split : true, expand : false, deep : true},
				{selector: '*', attributes : ['style', 'class'], split : false, expand : false, deep : true}
			]
			,setup: function (editor) {
				editor.on('KeyDown', function(event) {
					if (event.keyCode == 13 && !event.shiftKey){
						event.preventDefault();
						event.stopPropagation();
						//salva
						self.enviar();
						return false;
					}
				});
			}

		});
		
		//Controla upload do avatar e recupera a imagem
		self.oAvatarProfile.dropzone({
			previewTemplate: $("#cbDropzone").html()
			,url:"form/_arquivo.php"
			,idObjeto: gIdpessoa
			,tipoObjeto: 'pessoa'
			,tipoArquivo: 'AVATAR'
			,sending: function(file, xhr, formData){
				//Ajusta parametros antes de enviar via post
				formData.append("idobjeto", this.options.idObjeto);
				formData.append("tipoobjeto", this.options.tipoObjeto);
				formData.append("tipoarquivo", this.options.tipoArquivo);
			}
			,success: function(file, response){
				//Ao realizar o upload com sucesso
				this.options.loopArquivos(response);
			}
			,init: function() {
				var thisDropzone = this;
				$.ajax({
					url: this.options.url+"?tipoobjeto="+this.options.tipoObjeto+"&idobjeto="+this.options.idObjeto+"&tipoarquivo="+this.options.tipoArquivo
				}).done(function(data, textStatus, jqXHR) {
					thisDropzone.options.loopArquivos(data);
				})
			}
			,loopArquivos: function(data){
				jResp = jsonStr2Object(data);
				if(jResp.length>0){
					//Recupera o último arquivo
					//@todo: substituir arquivo anterior
					nomeArquivo = jResp[jResp.length-1].nome;
					if(nomeArquivo){
						self.oAvatarProfile.attr("src","upload/"+nomeArquivo);
					}
				}
			}
		});
		
		
	}
	//Mostrar popup interno
	self.abrirContainerChat=function(){
		self.oChatContainer.show();
	};

	self.maximizar=function(){
		self.maximizado=true;
		self.oChatContainer.addClass("maximizar");
		self.oConversation.addClass("col-md-7");
		self.oChatContainer.find(".side").show(300);
		self.oIconeToggleContatos.removeClass("fa-chevron-left").addClass("fa-chevron-right");
	};
	self.minimizar=function(){
		self.maximizado=false;
		self.oChatContainer.removeClass("maximizar");
		self.oChatContainer.find(".conversation").removeClass("col-md-7");
		self.oChatContainer.find(".side").hide();
		self.oIconeToggleContatos.removeClass("fa-chevron-right").addClass("fa-chevron-left");
	};

	//Sinalizar mais 1 nova mensagem no badge de notificações
	self.incrementarBadge=function(){
		iBadge=self.oIBadge.attr("ibadge")||0;
		iBadge++;
		sBadge="";
		//Se for maior que o limite, mostrar caractere ou ícone especial ao invés da quantidade
		if(iBadge>self.iLimiteBadgeAtencao){
			sBadge="!";
			self.oIBadge.addClass("blink");
		}else{
			sBadge=iBadge;
			self.oIBadge.removeClass("blink");
		}
		self.oIBadge.attr("ibadge",iBadge).html(sBadge).show();
		self.oBadge.attr("title",iBadge+" mensagens não visualizadas").css("color",self.sCorBadgeAtivo).show();
	};

	self.incrementarBadgeTarefa=function(inMsg){
		var msg=inMsg;
		//Inicializa mensagens
		self.oBadgeTarefa.data(self.oBadgeTarefa.data("mensagens")||{mensagens:{lidas:{},naolidas:{}}});

    //if(!self.oBadgeTarefa.data("mensagens") || !self.oBadgeTarefa.data("mensagens").naolidas[msg.idimmsg]){
            if(msg.tipo=="T" || msg.tipo=="A"){
		if(msg.statustarefa=="A" && !self.oBadgeTarefa.data("mensagens").naolidas[msg.idimmsg]){
			
			//Armazena a tarefa nao lida para não incrementar novamente no próximo refresh
			self.oBadgeTarefa.data("mensagens").naolidas[msg.idimmsg]=msg;
			
			iBadge=self.oIBadgeTarefa.attr("ibadge")||0;
			iBadge++;
			sBadge="";
			//Se for maior que o limite, mostrar caractere ou ícone especial ao invés da quantidade
			if(iBadge>self.iLimiteBadgeAtencao){
				sBadge="!";
				self.oBadgeTarefa.addClass("blink");
			}else{
				sBadge=iBadge;
				self.oBadgeTarefa.removeClass("blink");
			}
			self.oIBadgeTarefa.attr("ibadge",iBadge).html(sBadge).show();
			self.oBadgeTarefa.attr("title",iBadge+" novas tarefas").css("color",self.sCorBadgeAtivo).show();
		}
            }
	};

	//Resetar badge
	self.resetBadge=function(){
		self.oIBadge.attr("ibadge",0).hide();
		self.oBadge.css("color",self.sCorBadgeInativo).removeAttr("title");
	}

	//Remover 1 mensagem do badge de notificações
	self.decrementarBadge=function(inQt){
		if(inQt==0){
			self.resetBadge();
		}else{
			iBadge=parseInt(self.oIBadge.attr("ibadge"))||0;
			sBadge="";
			//Se for maior que 99 mostrar aviso especial
			if(iBadge>self.iLimiteBadgeAtencao){
				sBadge="!";
			}
			//Se for maior que 0 manter cor ativa
			if(iBadge>0){
				iBadge=iBadge-inQt;
				sBadge=iBadge;
				self.oBadge.css("color",self.sCorBadgeAtivo);
			}
			//se for igual a 0 "desabilitar" badge
			if(iBadge===0){
				self.resetBadge();
			}else{
				self.oIBadge.attr("ibadge",iBadge).html(sBadge);
			}
		}
	};
	
	//Para cada mensagem nova, cria-se 1 item de notificação na listagem
	self.novoItemListaNotificacoes=function(){
		var args=arguments[0];
		
		//Verifica se a Msg já existe na lista suspensa
		if(self.oListaNotificacoes.find("li.cbItemNotificacao[idimmsg="+args.idimmsg+"]").length<=0){

			//Verifica visibilidade conforme número máximo de mensagens visíveis
			iNotificacoes=self.oListaNotificacoes.find("li.cbItemNotificacao").length;
			if(iNotificacoes>=self.iMaxNotificacoesVisiveis){
				var oEsconder=self.oListaNotificacoes.find("li.cbItemNotificacao").not(".hidden").filter(":last");
				oEsconder.addClass("hidden");
			}

			//Realiza a substituição de placeholders para cada parametro de entrada
			var oItemMsg=self.hHtmlListaNotificacaoItem;
			$.each(args, function(i,o){
				if(typeof o !== "function"){
					oItemMsg = oItemMsg.replace(new RegExp("%"+i+"%","g"),args[i])
				}
			});

			$oItemMsg=$(oItemMsg);

			if(args.status=="N"){
				//Incrementa o badge
				self.incrementarBadge();
				args.onNovaMensagem(args.idimmsg);
			}

			//Injeta o html com a nova notificacao
			$oItemMsg.insertAfter(self.oListaNotificacoesHeader);
		}
	}

	self.getContatos=function(inCallback){
		
		if(tamanho(self.jContatos)===0){
			var form = new FormData();
			form.append("call", "contatos");
			form.append("_jwt", Cookies.get('jwt'));

			var settings = {
			  "async": true,
			  "crossDomain": true,
			  "url": "im/bim.php",
			  "method": "POST",
			  "headers": {
				"cache-control": "no-cache"
			  },
			  "processData": false,
			  "contentType": false,
			  "mimeType": "multipart/form-data",
			  "data": form	
			}

			$.ajax(settings).done(function (response) {
				var contatos=jsonStr2Object(response);
				contatos=self.ordenarContatos(contatos);
				inCallback(contatos);
			});
		}else{
			inCallback(self.aContatos);
		}
	};

	self.montaContato=function(inContato){
		var contato = inContato;
		
		var sStatus="offline";
		if(contato.online==="1" || contato.objetocontato==="imgrupo"){
			sStatus="online";
		}

		var sAvatar="";
		var sData="";
		var sRotulo="";
		//Ajusta propriedades conforme o tipo de destino: Contato (mensagem direta) ou Grupo
		if(contato.objetocontato==="pessoa"){
			sData=moment(contato.ultimoping||"").fromNow()||"";
			sAvatar=contato.arquivoavatar?self.avatarDir+contato.arquivoavatar:self.avatarDefault;
		}else if(contato.objetocontato==="imgrupo"){
			sData="";
			sAvatar=contato.arquivoavatar?self.avatarDir+contato.arquivoavatar:self.avatarDefaultGrp;
		}else{
			sData="";
			sAvatar=self.avatarDefault;
		}

		sRotulo=contato.rotulo?contato.rotulo.toUpperCase():"Usuário ["+contato.idcontato+"] sem nome";

		var sContato = self.hSkelContato
								.replace(/%idcontato%/g,contato.idcontato)
								.replace(/%objetocontato%/g,contato.objetocontato)
								.replace(/%status%/g,sStatus)
								.replace(/%order%/g,contato.order)
								.replace(/%img%/g,sAvatar)
								.replace(/%rotulo%/g,sRotulo)
								.replace(/%title%/g,sRotulo)
								.replace(/%data%/g,sData)
								.replace(/%ramalfixo%/g, contato.ramalfixo);

		//Inicializa dados de contato e mensagens
		var oContato=$(sContato).data({contato: contato}).data({mensagens:{lidas:{},naolidas:{}}});
		return oContato;
	}

	self.montarContatos=function(inCallback){
		var callback = inCallback;
		
		//Recupera listagem de contatos passando um callback
		self.getContatos(function(inContatos){

			$.each(inContatos, function(i,contato){
				//Monta obj Json amigável com os contatos
				self.jContatos[contato.objetocontato+"_"+contato.idcontato]=contato;
				
				//Verifica se é o próprio usuário
				if(gIdpessoa==contato.idcontato && contato.objetocontato=="pessoa"){
					return true;
				}
				
				if(self.oSideBar.find("#contato_"+contato.objetocontato+"_"+contato.idcontato).length<=0){
					var tmpContato = self.montaContato(contato);
					self.oGrupoContatosOnline.append(tmpContato);
				}
			});
			
			if(typeof callback === "function"){
				callback();
			}
		});
	};

	self.ordenarContatos=function(inContatos){
		console.log(inContatos);
		inContatos.sort(function(cAtual, cProx){
			
			var msgA=cAtual.ultimamsg||"";
			var msgB=cProx.ultimamsg||"";
			
			
			//SE NÃO TEMOS A ULTIMA DATA DA ÚLTIMA MENSAGEM ENVIADA PELOS CONTATOS
			if (msgA == "" && msgB == ""){
				
				//console.log("Entrei - "+cAtual.rotulo+"("+cAtual.objetocontato+" | "+cAtual.online+")"+" - "+cProx.rotulo+"("+cProx.objetocontato+" | "+cProx.online+")");
				
				
				//se o contato atual estiver online

				if (cAtual.online == 1){
					//se ambos estão online
					//pessoa-pessoa
					//pessoa-grupo
					//grupo-grupo
					if (cAtual.online == cProx.online){
						//se for pessoa, vem primeiro
						
						if (cAtual.objetocontato == 'pessoa'){
							if (cAtual.objetocontato > cProx.objetocontato){
								//console.log("<br>1**" +cAtual.objetocontato+" - "+cProx.objetocontato);
								return -1;
							}else{
								//console.log("<br>2**" +cAtual.objetocontato+" - "+cProx.objetocontato);
								if (cAtual.rotulo > cProx.rotulo){
									//console.log("<br>2.1**" +cAtual.rotulo+" - "+cProx.rotulo);
									return +1;
								}else{
									//console.log("<br>2.2**" +cAtual.rotulo+" - "+cProx.rotulo);
									return -1;
								}
								
							}
						}else{
							
							//console.log("<br>3**" +cAtual.objetocontato+" - "+cProx.objetocontato);
						}
						
						
						
						
					//se atual é online e o proximo está offline
					//pessoa-pessoa
					//grupo-pessoa
					
					}else{
						//console.log("<br>4**"+cAtual.objetocontato+" - "+cProx.objetocontato);
						if (cAtual.objetocontato == 'pessoa'){
							//console.log("<br>4.1**" +cAtual.rotulo+" - "+cProx.rotulo);
							return -1;
						}else{
							//console.log("<br>4.2**" +cAtual.rotulo+" - "+cProx.rotulo);
							
							return +1;
						}
					}
						
						
									
				//se o contato atual estiver offline
				//pessoa-pessoa
				//pessoa-grupo
				}else if (cAtual.online == 0){
					
					

					if (cProx.objetocontato == 'pessoa'){
						if (cProx.online == 0){
							if (cAtual.rotulo > cProx.rotulo){
								//console.log("<br>5.1**" +cAtual.rotulo+" - "+cProx.rotulo);
								return +1;
							}else{
								//console.log("<br>5.2**" +cAtual.rotulo+" - "+cProx.rotulo);
								return -1;
							}
						}else{
							return +1;
						}
					}else{
						return -1;
					}
					
					//se o proximo estiver online
					
				}
				
				//SE O CONTATO ATUAL ESTIVER OFFLINE E PROXIMO ESTIVER ONLINE
				//if (cAtual.online < cProx.online){
					//console.log(cAtual.objetocontato+' - '+cProx.objetocontato);
					//SE O OBJETO FOR PESSOA, SOBE E O GRUPO DESCE
					//if (cAtual.objetocontato < cProx.objetocontato){
					//	return +1;
					//}
					//if (cAtual.objetocontato > cProx.objetocontato){
					//	return -1;
					//}
					
				//SE O CONTATO ATUAL ESTIVER OFFLINE E O PROXIMO ONLINE
				//}else if (cAtual.online > cProx.online){
				//	return +1;
					
				//}else{
				//	if (cAtual.objetocontato < cProx.objetocontato){
				//		return +1;
				//	}
				//	if (cAtual.objetocontato > cProx.objetocontato){
				//		return -1;
				////	}
				//}
				//if (cAtual.ultimoping < cProx.ultimoping) return +1;
				//if (cAtual.ultimoping > cProx.ultimoping) return -1;
				
				//if (cAtual.rotulo < cProx.rotulo) return -1;
				//if (cAtual.rotulo > cProx.rotulo) return +1;
				//return 0;
			}
			
			
			
			if (msgA != "" && msgB == ""){
				return -1;
			}
			if (msgA == "" && msgB != ""){
				return +1;
			}
			if (msgA != "" && msgB != ""){
				if (msgA < msgB) return +1;
				if (msgA > msgB) return -1;
			}
			
			
			return 0;
			
			
		//	var msgA=cAtual.ultimamsg||"";
		//	var msgB=cProx.ultimamsg||"";
		//	if (msgA < msgB) return +1;
		//	if (msgA > msgB) return -1;
		//	return 0;
			
			//Comparar primeiro se nao possui data, ordena por rotulo, e por última considera a data
			//return (cAtual.rotulo > cProx.rotulo && !cAtual.ultimamsg)||(cAtual.ultimamsg!==null && msgA > msgB)||-1;
		})

		return inContatos;
	};

	self.toggleContatos=function(){
		if(self.maximizado){
			self.minimizar();
		}else{
			self.maximizar();
		}
	};
	
	self.preencheCabecalhoInfo=function(inContato){
		if(inContato.objetocontato=="pessoa"){
			self.oCInfo.html(inContato.cargo);
		}else if(inContato.objetocontato=="imgrupo"){
			//Mostra todos os membros em modo texto
			var sMembros="";
			var sSep="";
			$.each(inContato.membros, function(i,c){
				sMembros+=sSep+c.nome;
				sSep=", ";
			});

			self.oCInfo.data({"membros":inContato.membros}).html(sMembros);
		}
	}

	self.abrirConversa=function(inIdContato,inObjetocontato,inIdimmsg){
		
		//jContato=$(".contato[idcontato="+inIdContato+"]").data("contato");
		jContato=self.jContatos[inObjetocontato+"_"+inIdContato];

		self.oCAvatar.attr("src",(jContato.arquivoavatar?self.avatarDir+jContato.arquivoavatar:self.avatarDefault));
		self.oCNome.html(self.montaLinkPessoa(jContato));
		self.preencheCabecalhoInfo(jContato);
		self.oChatNovaMensagem.val("");
		//self.oCStatus.html(jContato.cargo);
		self.oChatBody.attr("objetocontato",jContato.objetocontato).attr("idcontato",jContato.idcontato);
		self.oChatBody.find(".message-body").remove();
		self.oConversation.removeClass("hidden");

		//Recupera conversação
		self.conversa({
			idcontato: jContato.idcontato
			,objetocontato: jContato.objetocontato
			,callback: function(inConversa){
				
				var vBaloes="";
				$.each(inConversa, function(i, msg){
					self.mostraBalao(msg);
				});
				//$(vBaloes).insertAfter(self.oChatMsgAnteriores);
				self.abrirContainerChat();

				//Retira a marcação visual de todas as conversas e deixa somente 1 ativa (conversando)
				$("#cbChatPainelContatos [id*=contato_]").removeClass("conversando").filter("#contato_"+jContato.objetocontato+"_"+jContato.idcontato).addClass("conversando")

				//Marcar todas como lidas
				self.marcarLida({idcontato:jContato.idcontato,objetocontato:jContato.objetocontato,idimmsg:"*"});

				//Tenha empacotar cada imagem dentro de um Span, para colocação de um elemento de edição, para ser possível excluir a imagem do db
				self.oChatBody.find("img").webuiPopover({closeable:true,content:function(){return self.opcoesImagemMsg(this);}});
				self.scrollParaMensagem();
			}
		});
	};
	
	self.montaLinkPessoa=function(inContato){
		switch (inContato.objetocontato) {
			case "pessoa":
				return `<a href="?_modulo=pessoa&_acao=u&idpessoa=${inContato.idcontato}" target="_blank">${inContato.rotulo}</a>`;
				break;
				
			case "imgrupo":
				return inContato.rotulo;
				break;

			default:
				return inContato.rotulo;
				break;
		}
	};

	self.balao=function(inPar){
		inPar.senderreceiver=(inPar.sender=="eu")?"sender":"receiver";
		inPar.idimmsg=inPar.idimmsg||null;
		inPar.idimmsgbody=inPar.idimmsgbody||null;
		inPar.msg=inPar.msg||null;
		inPar.criadoem=inPar.criadoem||null;
		
		//Melhorias na visualização de datas
		var dAgora = moment();
		var dMsg = moment(inPar.criadoem);
		var sData = (dAgora.diff(dMsg,"day")>0)?dMsg.fromNow():dMsg.format("HH:mm");
		var sDataTitle=dMsg.format("D/MM/YY HH:mm:ss");

		//Anexos
		var hLinks="";
		if(tamanho(inPar._anexos)>0){
			
			$.each(inPar._anexos, function(idArq, arq){
				//console.log(arq);
				if(arq.tipo==="L"){
					//<a href="%link%" target="%target%"><i class="%icone%"></i>%textolink%</a>
					hLinks+=self.hSkelLink.replace(/%link%/g,arq.arq)
											.replace(/%target%/g,"_blank")
											.replace(/%icone%/g,"fa fa-external-link")
											.replace(/%textolink%/g,arq.nome);
				}
			});
		}
		var mRight=(inPar.sender=="eu")?"dropdown-menu-right":"";
		var strBalao = self.hSkelBalao.replace(/%senderreceiver%/g,inPar.senderreceiver)
									.replace(/%idimmsg%/g,inPar.idimmsg)
									.replace(/%idimmsgbody%/g,inPar.idimmsgbody)
									.replace(/%status%/g,inPar.status)
									.replace(/%statustarefa%/g,inPar.statustarefa)
									.replace(/%datatarefa%/g,inPar.datatarefa||"")
									.replace(/%tipo%/g,inPar.tipo)
									.replace(/%msg%/g,inPar.msg)
									.replace(/%data%/g,sData)
									.replace(/%datatitle%/g,sDataTitle)
									.replace(/%msgAnexos%/g,hLinks)
									.replace(/%menuright%/g,mRight);

		if(inPar.idimgrupo && inPar.sender!=="eu"){
			var nomeSender="";
			//Recupera os dados do contato sender através dos "membros do grupo"

			var oMembroGrupo=self.jContatos["imgrupo_"+inPar.idimgrupo].membros[inPar.sender];
			if(oMembroGrupo){
				strBalao=strBalao.replace("%sendernome%",`<span style="color:#${oMembroGrupo.bg}">${oMembroGrupo.nome}</span>`);
			}else{
				var oMembroPessoa=self.jContatos["pessoa_"+inPar.sender];
				console.log(oMembroPessoa);
				strBalao=strBalao.replace("%sendernome%",`<span style="color:#cc0000">${oMembroPessoa.rotulo}</span>`);;
			}
		}else{
			strBalao=strBalao.replace("%sendernome%","");
		}
		
		return strBalao;
	}

	self.mostraBalao=function(inMsg){
		sSenderReceiver=(inMsg.sender=="eu")?"sender":"receiver";
		var hBalao = self.balao(inMsg);
		$(hBalao).appendTo(self.oChatBody);
	}

	self.scrollParaMensagem=function(inidimmsg){
		var idimmsg=inidimmsg||false;
		if(!idimmsg){
			self.oChatBody.animate({ scrollTop: self.oChatBody.prop("scrollHeight")}, 500);
		}
	}

	self.conversa=function(inPar){
		
		inPar.idcontato=inPar.idcontato||false;
		inPar.objetocontato=inPar.objetocontato||false;
		inPar.idimsgm=inPar.idimsgm||null;
		inPar.callback=inPar.callback||null;
		
		var form = new FormData();
		form.append("call", "conversa");
		form.append("sender", inPar.idcontato);
		form.append("objetocontato", inPar.objetocontato);
		form.append("idimmsg", inPar.idimsgm);
		form.append("_jwt", Cookies.get('jwt'));

		var settings = {
		  "async": true,
		  "crossDomain": true,
		  "url": "im/bim.php",
		  "method": "POST",
		  "headers": {
			"cache-control": "no-cache"
		  },
		  "processData": false,
		  "contentType": false,
		  "mimeType": "multipart/form-data",
		  "data": form
		}

		$.ajax(settings).done(function (response) {
			inPar.callback(jsonStr2Object(response));
		});
	}
	
	self.enviar=function(inOpt){
//		alert("Em manutenção. Aguarde!");	
		inOpt=inOpt||{};
		
		var txtMsg=inOpt.msg||tinymce.get("chatNovaMensagem").getContent();

		var sContatos = inOpt.contatos?JSON.stringify(inOpt.contatos):`[{'idcontato':'${self.oChatBody.attr("idcontato")}','objetocontato':'${self.oChatBody.attr("objetocontato")}'}]`;
		var sMsgtipo = inOpt.msgtipo?inOpt.msgtipo:"M";
		var sDatatarefa = inOpt.datatarefa?inOpt.datatarefa:"";
		var sModulopk = inOpt.modulopk?inOpt.modulopk:"";
		var sModulo = inOpt.modulo?inOpt.modulo:"";

		//Verifica se o usuário digitou algo
		if(self.html2Txt(txtMsg).length>0){

			var form = new FormData();
			form.append("call", "enviar");
			form.append("contatos", sContatos);
			form.append("msgtipo", sMsgtipo);
			form.append("msg", txtMsg);
			form.append("datatarefa", sDatatarefa);
			form.append("modulopk", sModulopk);
      form.append("modulo", sModulo);

      //Se for enviado o idimmsgbody, será um compartilhamento de mensagem. O corpo da mensagem não será criado
			if(inOpt.idimmsgbody){
				form.append("idimmsgbody", idimmsgbody);
			}
			
			form.append("_jwt", Cookies.get('jwt'));

			var settings = {
			  "async": true,
			  "crossDomain": true,
			  "url": "im/bim.php",
			  "method": "POST",
			  "headers": {
				"cache-control": "no-cache"
			  },
			  "processData": false,
			  "contentType": false,
			  "mimeType": "multipart/form-data",
			  "data": form,
			  beforeSend: function(){
				self.desabilitaEditor(true);
			  }
			};

			$.ajax(settings).done(function (data, textStatus, jqXHR) {
				self.desabilitaEditor(false);
				jResponse=jsonStr2Object(data);
				if(jResponse.code=="MSG_ENVIADA" || jResponse.code=="MSG_ENVIADA_OFFLINE"){
					//Verifica se trata-se de uma chamada customizada ao método: enviar({...:...})
					if(tamanho(inOpt)===0){
						sBalao = self.balao({
							sender: "eu"
							,idimmsg: jResponse.idimmsg
							,idimmsgbody: jResponse.idimmsgbody
							,msg: txtMsg
							,criadoem: jResponse.criadoem
						});
						//Limpa o texto
						self.oChatNovaMensagem.html("");
						//Insere por ultimo
						$(sBalao).appendTo(self.oChatBody);
						//Tenha empacotar cada imagem dentro de um Span, para colocação de um elemento de edição, para ser possível excluir a imagem do db
						self.oChatBody.find("img").wrap("<span class='imagem'></span>");
						//Scroll
						self.scrollParaMensagem();
						//Mover o contato para cima
						self.moverContatos({[self.oChatBody.attr("objetocontato")+"_"+self.oChatBody.attr("idcontato")]:"topo"});
					}
					
					//Verifica se será anexado um "arquivo" (que pode ser código html)
					if(inOpt.anexo){
						self.novoAnexo({idimmsgbody: jResponse.idimmsgbody, anexo: inOpt.anexo});
					}

					//Executa callback
					if(typeof inOpt.callback==="function"){
						inOpt.callback(true, data,textStatus,jqXHR);
					}
				}else if(jResponse.code=="MANUTENCAO"){
					alertAtencao("Em manutenção. Aguarde!");
				}else{
					console.warn(data);
					inOpt.callback(false, data,textStatus,jqXHR);
				}
			});
		}
	};
	
	self.novoAnexo=function(inOpt){
		inOpt=inOpt||{};
		
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
		  "url": "im/bim.php",
		  "method": "POST",
		  "headers": {
			"cache-control": "no-cache"
		  },
		  "processData": false,
		  "contentType": false,
		  "mimeType": "multipart/form-data",
		  "data": form,
		  beforeSend: function(){
			
		  }
		};
		$.ajax(settings).done(function (data, textStatus, jqXHR) {
			jResponse=jsonStr2Object(data);
			if(jResponse.code=="ANEXO_OK"){
				console.log("Arquivo anexado com sucesso");
			}else{
				alertErro("Erro ao anexar arquivo");
				console.error(data);
			}
		});
	};
	
	self.desabilitaEditor=function(inDesabilita){
		if(inDesabilita){
			tinymce.activeEditor.setMode('readonly');
			tinymce.activeEditor.$().addClass("fade");
		}else{
			tinymce.activeEditor.setMode('design');
			tinymce.activeEditor.$().removeClass("fade");
		}
	};
	self.historico=function(inPar){
		if(!inPar || !inPar.callback){
			console.error("chat.historico: Um callback deve ser fornecido para este método.")
			return false;
		}
		
		var acao=inPar.acao||"historico";
		
		//Dispara montagem dos contatos
		self.montarContatos(function(){
		
			//Recupera hostórico de todas as conversaççoes
			var form = new FormData();
			form.append("call", acao);
			form.append("_jwt", Cookies.get('jwt'));

			var settings = {
				global: false,     //this makes sure ajaxStart is not triggered
			  "async": true,
			  "crossDomain": true,
			  "url": "im/bim.php",
			  "method": "POST",
			  "headers": {
				"cache-control": "no-cache"
			  },
			  "processData": false,
			  "contentType": false,
			  "mimeType": "multipart/form-data",
			  "data": form
			}

			$.ajax(settings).done(function (response) {
				inPar.callback(jsonStr2Object(response));
			});
		});
	};

	self.opcoesImagemMsg=function(inImg){
		$img = $(inImg);
		//Recupera o id da mensagem e do corpo
		vIdimmsg = $img.closest("[idimmsg]").attr("idimmsg");
		vIdimmsgbody = $img.closest("[idimmsg]").attr("idimmsgbody");
		//Salva uma propriedade unica na imagem para poder recupera-la
		$img.attr("idimmsg",vIdimmsg);
		if(vIdimmsg&&vIdimmsgbody){
			return "<span class='nowrap pointer hoververmelho' onclick='chat.excluirImagemDb("+vIdimmsg+","+vIdimmsgbody+",this)'><i class='fa fa-trash'></i> Excluir imagem</span>";
		}
	}
	
	self.excluirImagemDb=function(inIdimmsg, inIdimmsgbody, inObjClique){
		//Esconde o popup
		$(inObjClique).closest(".webui-popover[id*=webuiPopover]").remove();
		//Remove a imagem do DOM
		self.oChatBody.find("img[idimmsg="+inIdimmsg+"]").remove();
		//Salva o novo conteúdo no db
		var oBalaoMsg = self.oChatBody.find(".message-body[idimmsg="+inIdimmsg+"] .message-text");
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
		  "url": "im/bim.php",
		  "method": "POST",
		  "headers": {
			"cache-control": "no-cache"
		  },
		  "processData": false,
		  "contentType": false,
		  "mimeType": "multipart/form-data",
		  "data": form
		};

		$.ajax(settings).done(function (response) {
			jResponse=jsonStr2Object(response);
			if(jResponse.code=="MSG_ALTERADA"){
				//atualiza somente o HTML com o que veio do DB, para conferência
				oBalaoMsg.html(jResponse.msg);
				//Tenha empacotar cada imagem dentro de um Span, para colocação de um elemento de edição, para ser possível excluir a imagem do db
				oBalaoMsg.find("*").filter("img").wrap("<span class='imagem'></span>");
				alertSalvo("Imagem excluída!");
			}
		});

	};
	/*
	self.novaMensagemKeypress=function(inEvent){
		if(inEvent.charCode==13 && !inEvent.shiftKey){
			alert("sarvar");
		}
	}*/

	self.marcarLida=function(inPar){
		if(inPar.idimmsg){
			var form = new FormData();
			form.append("call", "ler");
			if(inPar.idcontato){
				form.append("idcontato", inPar.idcontato);
				form.append("objetocontato", inPar.objetocontato);
			}
			form.append("idimmsg", inPar.idimmsg);
			form.append("_jwt", Cookies.get('jwt'));

			var settings = {
			  "async": true,
			  "crossDomain": true,
			  "url": "im/bim.php",
			  "method": "POST",
			  "headers": {
				"cache-control": "no-cache"
			  },
			  "processData": false,
			  "contentType": false,
			  "mimeType": "multipart/form-data",
			  "data": form
			};

			$.ajax(settings).done(function (response){
				if(inPar.idimmsg=="*"){
					//Desmarcar tudo do sender					
					var oBadge_contato=$("#badge_contato_"+inPar.objetocontato+"_"+inPar.idcontato);

					//Decrementar o total de mensagens não lidas
					self.decrementarBadge(tamanho($("#contato_"+inPar.objetocontato+"_"+inPar.idcontato).data("mensagens").naolidas));

					//Resetar o badge e resetar mensagens não lidas no objeto DATA do Jquery
					$("#contato_"+inPar.objetocontato+"_"+inPar.idcontato).data("mensagens").naolidas={};
					oBadge_contato.removeClass("hidden").attr("imensagens",0).html("");

				}else if(inPar.idimmsg!=="*"){
					null;
				}
			});
		}
	}

	self.notificacao=function(){

		args=arguments[0];
		if(args.beep){
			gSomNovaMensagem.play();
		}
 
		Notification.requestPermission().then(function(result){		
			var sTitulo="";
			var sCorpo="";
			var sIcone="";
			if(args.iNovasMensagens==1){
				sTitulo =  self.jContatos[self.msg2hid(args.ultimaMensagem)].rotulo;
				sCorpo = self.html2Txt(self.html2Ent(args.ultimaMensagem.msg||""));
				sIcone = self.jContatos[self.msg2hid(args.ultimaMensagem)].arquivoavatar?"./upload/"+self.jContatos[self.msg2hid(args.ultimaMensagem)].arquivoavatar:self.avatarDefault;
			}else{
				sTitulo =  "("+args.iNovasMensagens+" mensagens)";
				sCorpo = self.html2Txt(self.html2Ent(args.ultimaMensagem.msg||""));
				sIcone = self.jContatos[self.msg2hid(args.ultimaMensagem)].arquivoavatar?"./upload/"+self.jContatos[self.msg2hid(args.ultimaMensagem)].arquivoavatar:self.avatarDefault;
			}
			//Mostrar notificação do browser somente se o chat estiver fechado
			//@todo: verificar preferencias do usuário
			//if(!chat.oChatBody.is(":visible")){
				notificacao({
					titulo: sTitulo
					,corpo: sCorpo
					,icone: sIcone
					,id: new Date().getTime()
				});
			//}
		});
	};



	self.html2Ico=function(inHtml){
		return inHtml.replace(/<img\b[^>]*>/ig,"<i class='fa fa-image'></i>").trim();
	};
	self.html2Ent=function(inHtml){
		return inHtml.replace(/<img\b[^>]*>/ig,"\u{1F5CB}").trim();
	};
	self.html2Txt=function(inHtml){
		inHtml=inHtml.replace(/(<([^>]+)>)/ig, "").replace(/&nbsp;/ig," ").trim();
		inHtml=htmldecode(inHtml);
		return inHtml;
	};
	
	self.pesquisarContato=function(inObj){
		$oSearch=$(inObj);
		strSearch=$oSearch.val();
		if(strSearch.trim().length<=0){
			$(".sideBar .contato").show(self.velAnimacaoShow);
		}else{
			$.each($(".sideBar .contato"), function(i,contato){
				$contato=$(contato);
				vSelecionado=$contato.hasClass("selecionado")?true:false;//Somente contatos
				if(fullTextCompare(strSearch,contato.title, true)||vSelecionado){
					console.log(contato.title);
					$contato.show(self.velAnimacaoShow);
				}else{
					$contato.hide(self.velAnimacaoHide);
				}
			});
		}
	};
	
	self.trataEvento=function(inMsg){
		var jEvento = jsonStr2Object(inMsg.msg);
		if(jEvento){
			if(jEvento.online){
				//Move o contato para o grupo online. @todo: verificar se existem mensagens nao lidas
				self.moverContatos({["pessoa_"+jEvento.online]:"online"});
			}else if(jEvento.offline){
				//Move o contato para o grupo offline. @todo: verificar se existem mensagens nao lidas
				self.moverContatos({["pessoa_"+jEvento.offline]:"offline"});
			}else if(jEvento.apagarmsg){
				//Apagar mensagem
				self.oChatBody.find("[idimmsgbody="+jEvento.apagarmsg+"]").attr("tipo","X").addClass("highlight");
			}
		}
	};
	
	/*
	 * Mover os contatos agrupando conforme o status e as notificações
	 * @param json {objetocontato_idcontato: ["online"||"offline"||"onlineNotificacao"||"offlineNotificacao"]}
	 */
	self.moverContatos=function(inContatos){
		$.each(inContatos, function(objetocontatoIdcontato,grupoStatus){
			$contato=$("#contato_"+objetocontatoIdcontato);
			switch (grupoStatus){
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
							.attr("status","offline")
							.hide()
							//.prependTo(self.oGrupoContatosOffline)
							.fadeIn('slow');
					break;
				case "online":
					$contato
							.removeClass("offline")
							.addClass("online")
							.attr("status","online")
							.hide()
							//.prependTo(self.oGrupoContatosOnline)
							.fadeIn('slow');
					break;
				default:
					console.warn("chat.moverContatos: status não previsto: "+grupoStatus);
					break;
			}
			console.log(objetocontatoIdcontato+"-"+grupoStatus);
		});
	};
	
	self.toggleFullscreen=function(inForce){
		if(!inForce && self.oChatContainer.hasClass("fullscreen")){
			self.oChatContainer.removeClass("fullscreen");
			self.oChatFullscreen.removeClass("fa-window-restore").addClass("fa-window-maximize");
		}else{
			document.title=".: Chat :.";
			self.oChatContainer.addClass("fullscreen");
			self.oChatFullscreen.removeClass("fa-window-maximize").addClass("fa-window-restore");
		}
	};
	//Monta um atributo id (#) de html, para recuperar o objeto do contato referente à mensagem
	self.msg2hid=function(inMsg){
		var hIdContato="";
		if(!inMsg.idimgrupo){
			hIdContato = "pessoa_"+inMsg.sender;
		}else if(inMsg.idimgrupo){
			hIdContato = "imgrupo_"+inMsg.idimgrupo;
		}else{
			console.error("js: chat: msg2hid: valor msg.idimgrupo não esperado. Impossível recuperar o id do contato.");
		}
		return hIdContato;
	};

	self.msg2tarefa=function(inIdimmsgbody,inAcao){
		var form = new FormData();
		form.append("call", "msg2tarefa");

		form.append("idimmsgbody", inIdimmsgbody);
		form.append("transformar", inAcao);
		form.append("_jwt", Cookies.get('jwt'));

		var settings = {
		  "async": true,
		  "crossDomain": true,
		  "url": "im/bim.php",
		  "method": "POST",
		  "headers": {
			"cache-control": "no-cache"
		  },
		  "processData": false,
		  "contentType": false,
		  "mimeType": "multipart/form-data",
		  "data": form
		};

		$.ajax(settings).done(function (response){
			if(jsonStr2Object(response).code=="TRANS_MSG_OK"){
				self.oChatBody.find("[idimmsgbody="+inIdimmsgbody+"]").attr("tipo","T");
			}
		});
	}
	
	self.apagarMsg=function(inIdimmsgbody,inCallback){
		var form = new FormData();
		form.append("call", "apagar");
		form.append("idimmsgbody", inIdimmsgbody);
		form.append("_jwt", Cookies.get('jwt'));

		var settings = {
		  "async": true,
		  "crossDomain": true,
		  "url": "im/bim.php",
		  "method": "POST",
		  "headers": {
			"cache-control": "no-cache"
		  },
		  "processData": false,
		  "contentType": false,
		  "mimeType": "multipart/form-data",
		  "data": form
		};

		$.ajax(settings).done(function (response){
			if(jsonStr2Object(response).code=="X_MSG_OK"){
				self.oChatBody.find("[idimmsgbody="+inIdimmsgbody+"]").attr("tipo","X");
        if(typeof inCallback == "function"){
        	inCallback();
        }
			}
		});
	}

	self.filtrarContatos=function(inFiltro){
		$filtro=$(inFiltro);
		//Aplica/remove a "seleção" para o item selecionado
		if($filtro.hasClass("selecionado")){
			$filtro.removeClass("selecionado");
		}else{
			$filtro.addClass("selecionado");
		}
		
		//Se existir no mínimo 1 filtro selecionado, altera o ícone
		$filtros=$("#menuFiltros");
		$selecionados=$filtros.find(".selecionado");
		$filtroI = $("#menuFiltrosIcone");
		
		//Monta uma estutura de atributos selecionados para ser comparada em seguida com cada contato
		if($selecionados.length>0){
			jFiltrosSel={};
			$.each($selecionados, function(i,o){
				var jfiltro=$(o).data();
				//Recupera o tipo do filtro (status,objetocontato,etc...)
				var kfiltro=Object.keyAt(jfiltro, 0);
				var vfiltro=jfiltro[Object.keyAt(jfiltro, 0)];
				
				jFiltrosSel[kfiltro]=jFiltrosSel[kfiltro]||{};
				jFiltrosSel[kfiltro][vfiltro]=true;
			});
			//Compor a coleção de atributos selecionados
			$filtros.data({filtrosSelecionados:jFiltrosSel});
			$filtroI.addClass("laranja");
		}else{
			$filtros.data({filtrosSelecionados:false});
			$filtroI.removeClass("laranja");
		}

		//Loop em cada contato
		$.each(chat.oChatPainelContatos.find("[idcontato]"),function(i,o){
			$o=$(o);
			vStatus=$o.attr("status");
			vObjeto=$o.attr("objetocontato");
			$menufiltros=$("#menuFiltros");

			compStatus=true;
			compObjeto=true;

			//Compara o status com os filtros selecionados
			if($menufiltros.data().filtrosSelecionados && $menufiltros.data().filtrosSelecionados.status && !$menufiltros.data().filtrosSelecionados.status[vStatus]){
				compStatus=false;
			}

			//Compara o objetocontato com os filtros selecionados
			if($menufiltros.data().filtrosSelecionados && $menufiltros.data().filtrosSelecionados.objetocontato && !$menufiltros.data().filtrosSelecionados.objetocontato[vObjeto]){
				compObjeto=false;
			}

			//console.log(`status:${compStatus} objeto:${compObjeto}`);
			if(compStatus && compObjeto){
				$o.show();
			}else{
				$o.hide();
			}
		});
	}

	self.mostrarLista=function(){};
	self.marcarTodasComoLidas=function(){};
	self.abrirMensagemEspecifica=function(){};
	
	self.mostrarMensagensAnteriores=function(){};
	self.mostrarPainelDeContatos=function(){};

	self.montarHeaderContatos=function(){};
	self.montarHeaderConversa=function(){};
	self.enviarEmoticon=function(){};

	self.chatContainer = `<style>
#cbChatContainer {
	position: fixed;
    overflow: hidden;
	bottom: 4px;
    height: calc(100% - 68px);
	height: calc(100% - 44px);
    margin: auto;
    padding: 0;
    box-shadow: 0 33px 29px 0 rgba(0, 0, 0, .06), 0 1px 12px 0 rgba(0, 0, 0, .5);
    right: 10px;
	width: 30%;
	display: none;
	z-index: 10000;
	
}
#cbChatContainer.maximizar{
	width: 60%;
}
#cbChatContainer.maximizar #cbChatCorpo.side{
	display: visible;
}
#cbChatContainer.fullscreen {
    width: 100%;
    height: 100%;
    padding: 0px;
    margin: 0px;
    right: 0px;
    bottom: 0px;
}
#cbChatCorpo {
  background-color: #e3dfdb;
  height: 100%;
  overflow: hidden;
  margin: 0;
  padding: 0;
  box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .06), 0 2px 5px 0 rgba(0, 0, 0, .2);
}

#ramalfixo{
	font-size:10px;
	color: #666;
}
.side {
  padding: 0;
  margin: 0;
  height: 100%;
  display: none;
}
.side-one {
  padding: 0;
  margin: 0;
  height: 100%;
  width: 100%;
  z-index: 1;
  position: relative;
  display: block;
  top: 0;
}

.side-two {
  padding: 0;
  margin: 0;
  height: 100%;
  width: 100%;
  z-index: 2;
  position: relative;
  top: -100%;
  left: -100%;
  -webkit-transition: left 0.3s ease;
  transition: left 0.3s ease;
}

#chatIconeToggleContatos{
	left: -13px;
    top: 3px;
    position: inherit;
}

.heading {
  padding: 10px 16px 10px 15px;
  margin: 0;
  height: 60px;
  width: 100%;
  background-color: #eee;
  z-index: 1000;
}

.heading-avatar {
  padding: 0;
  cursor: pointer;

}

.heading-avatar-icon img {
  border-radius: 50%;
  height: 40px;
  width: 40px;
	object-fit: cover; /* não efetuar stretch da imagem */
}

.heading-name {
  padding: 0 !important;
  cursor: pointer;
}

.heading-name-meta,
.heading-name-data{
    font-weight: 700;
    font-size: 100%;
    padding: 0px 17px;
    padding-bottom: 0;
    text-align: left;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #000;
    display: block;
}
.heading-name-data{
	cursor: default;
    color: gray;
    font-size: 12px;
    font-weight: normal;

	white-space: pre-line;
    max-height: 30px;
    position: absolute;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: normal;
    padding-right: 0px;
}
.heading-online {
  display: none;
  padding: 0 5px;
  font-size: 12px;
  color: #93918f;
}
.heading-compose {
  padding: 0;
}

.heading-compose i {
  text-align: center;
  padding: 5px;
  color: #93918f;
  cursor: pointer;
}

.heading-dot {
  padding: 0;
  margin-left: 10px;
}

.heading-dot i {
  text-align: right;
  padding: 5px;
  color: #93918f;
  cursor: pointer;
}

.searchBox {
  padding: 0 !important;
  margin: 0 !important;
  height: 50px;
  width: 100%;
}

.searchBox-inner {
  height: 100%;
  width: 100%;
  padding: 10px !important;
  background-color: #fbfbfb;
}


/*#searchBox-inner input {
  box-shadow: none;
}*/

.searchBox-inner input:focus {
  outline: none;
  border: none;
  box-shadow: none;
}

.searchBox-inner .barraPesquisaContato{
	display: inline-block;
	width: 85%;
}

#barraPesquisaFiltrar{
	display: inline-block;
	width: 10%;
}

.sideBar {
  padding: 0 !important;
  margin: 0 !important;
  background-color: #fff;
  overflow-y: auto;
  border: 1px solid #f7f7f7;
  height: calc(100% - 120px);
}

.sideBar-body {
  position: relative;
  padding: 4px !important;
  border-bottom: 1px solid #f7f7f7;
  height: 58px;
  margin: 0 !important;
  cursor: pointer;
  border-bottom: 1px solid #eee;
}

.sideBar-body:hover {
  background-color: #f2f2f2;
}

.contato .chatStatusContato{
    position: absolute;
    bottom: -2px;
    right: 12px;
    font-size: 20px;
	opacity: 0;
}
.contato.offline .avatar-icon img{
	transition: opacity 1s ease;
	opacity: .4;
}
.contato.offline:hover .avatar-icon img{
	opacity: 1;
}

.contato.offline .chatStatusContato{
    color: silver;
	opacity: 1;
}
.contato.online:not([objetocontato=imgrupo]) .chatStatusContato{
    color: #4CAF50;
	opacity: 1;
}

.sideBar-avatar {
  text-align: center;
  padding: 0 !important;
}

.avatar-icon img {
  border-radius: 50%;
  height: 49px;
  width: 49px;
	object-fit: cover; /* não efetuar stretch da imagem */
}

.sideBar-main {
  padding: 0 !important;
}

.sideBar-main .row {
  padding: 0 !important;
  margin: 0 !important;
}

.sideBar-name {
  padding: 0px !important;
  overflow-x: hidden;
}

.name-meta {
  font-size: 10px;
  padding: 1% !important;
  text-align: left;
  text-overflow: ellipsis;
  color: #000;
  margin-top:4px;
}

.name-meta p{
	margin:0px !important;
}
.sideBar-time {
    padding: 0px !important;
    white-space: nowrap;
    top: 11px;
    position: absolute;
    right: 0px;
}

.time-meta {
  text-align: right;
  font-size: 10px;
  padding: 1% !important;
  color: rgba(0, 0, 0, .4);
  vertical-align: baseline;
}

/*New Message*/

.newMessage {
  padding: 0 !important;
  margin: 0 !important;
  height: 100%;
  position: relative;
  left: -100%;
}
.newMessage-heading {
  padding: 10px 16px 10px 15px !important;
  margin: 0 !important;
  height: 100px;
  width: 100%;
  background-color: #00bfa5;
  z-index: 1001;
}

.newMessage-main {
  padding: 10px 16px 0 15px !important;
  margin: 0 !important;
  height: 60px;
  margin-top: 30px !important;
  width: 100%;
  z-index: 1001;
  color: #fff;
}

.newMessage-title {
  font-size: 18px;
  font-weight: 700;
  padding: 10px 5px !important;
}
.newMessage-back {
  text-align: center;
  vertical-align: baseline;
  padding: 12px 5px !important;
  display: block;
  cursor: pointer;
}
.newMessage-back i {
  margin: auto !important;
}

.composeBox {
  padding: 0 !important;
  margin: 0 !important;
  height: 60px;
  width: 100%;
}

.composeBox-inner {
  height: 100%;
  width: 100%;
  padding: 10px !important;
  background-color: #fbfbfb;
}

.composeBox-inner input:focus {
  outline: none;
  border: none;
  box-shadow: none;
}

.compose-sideBar {
  padding: 0 !important;
  margin: 0 !important;
  background-color: #fff;
  overflow-y: auto;
  border: 1px solid #f7f7f7;
  height: calc(100% - 160px);
}

/*Conversation*/

.conversation {
  padding: 0 !important;
  margin: 0 !important;
  height: 100%;
  /*width: 100%;*/
  border-left: 1px solid rgba(0, 0, 0, .08);
  /*overflow-y: auto;*/
  float: right;
}

.message {
  padding: 0 !important;
  margin: 0 !important;
  background-size: cover;
  overflow-y: auto;
  border: 1px solid #e1d9d1;
  height: calc(100% - 120px);
}
.message .messagebg{
	margin: 0;
    padding: 0;
    border: 0;
	position: absolute;
    top: 60px; /* descontar header */
    left: 0;
    height: 100%;
    width: 100%;
	background-image: url(inc/js/chat/bg.png);
	opacity: 0.03;
    background-repeat: repeat repeat;
}
.message-previous {
  margin : 0 !important;
  padding: 0 !important;
  height: auto;
  width: 100%;
}
.previous {
  font-size: 15px;
  text-align: center;
  padding: 10px !important;
  cursor: pointer;
}

.previous a {
  text-decoration: none;
  font-weight: 700;
}

.message-body {
  margin: 0 !important;
  padding: 0 !important;
  width: auto;
  height: auto;
  position: relative;
}

.message-body[direcao=receiver][status=N]{
    background: rgba(255, 255, 198,1);
}
.message-body[direcao=receiver][status=L]{
    background: rgba(255, 255, 198,0);
	transition: background-color 3s;
}
.message-body .msgOpcoes{
	position: absolute;
    right: 5px;
    top: -2px;
	display: none;
    opacity:0.5;
}
.message-body:hover .msgOpcoes{
	display:block;
}
.message-body .msgOpcoes:hover{
	display:block;
	opacity: 1;
}
								
.message-body .chatMsgIconeTarefa{
	display: none;
	color: silver;
	-webkit-transition: color 2s ease-out;
	-moz-transition: color 2s ease-out;
	-o-transition: color 2s ease-out;
	transition: color 2s ease-out;
}

.message-body[tipo=T] .chatMsgIconeTarefa{
	display: unset;
	color: #ec971f;
}

.message-body .msgApagada{
	display: none;
	color: rgba(0,0,0,0.4);
	font-style: oblique;
	font-size: 10px;
}
.message-body[tipo=X] .msgApagada{
	display: unset;
}
.message-body[tipo=X] .chatNovaMsg,
.message-body[tipo=X] .msgOpcoes,
.message-body[tipo=X] .chatMsgIconeTarefa,
.message-body[tipo=X] .message-text,
.message-body[tipo=X] .chatStatusEntrega,
.message-body[tipo=X] .message-time{
	display: none!important;
}
.message-body[tipo=X] .messageMainreceiver,
.message-body[tipo=X] .messageMainsender,
.message-body[tipo=X] .receiver,
.message-body[tipo=X] .sender{
    padding-top: 0px!important;
    padding-bottom: 0px!important;
}
.message-body[tipo=X] .receiver ,
.message-body[tipo=X] .sender{
	opacity: 0.7;
}

.message-body[tipo=X] .message-sender{
	opacity: 0.6;
	font-size: 9px;
}

.messageMainreceiver {
  /*padding: 10px 20px;*/
  max-width: 90%;
}

.messageMainsender {
	padding: 3px 20px !important;
	max-width: 100%;
	right: 0px;
}

.message-sender{
	text-shadow: 1px 1px 0px rgba(0, 0, 0, 0.15);
}

.message-text {
  margin: 0 !important;
  padding: 5px !important;
  word-wrap:break-word;
  font-weight: 200;
  font-size: 14px;
  padding-bottom: 0 !important;
}

.message-time {
  margin: 0 !important;
  margin-left: 50px !important;
  font-size: 12px;
  text-align: right;
  color: #9a9a9a;

}

.receiver {
  width: auto !important;
  padding: 4px 10px 7px !important;
  border-radius: 10px 10px 10px 0;
  background: #ffffff;
  font-size: 12px;
  word-wrap: break-word;
  display: inline-block;
  box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
  position: relative;
}

.sender {
  float: right;
  Xwidth: auto !important;
  max-width: 90%!important;
  background: #dcf8c6;
  border-radius: 10px 10px 0 10px;
  padding: 4px 10px 7px !important;
  font-size: 12px;
  
  display: inline-block;
  word-wrap: break-word;
  box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
  position: relative;
}


/*Reply*/

.reply {
  height: 60px;
  width: 100%;
  background-color: #f5f1ee;
  padding: 10px 5px 10px 5px !important;
  margin: 0 !important;
  z-index: 1000;
}

.reply-emojis {
  padding: 5px !important;
}

.reply-emojis i {
  text-align: center;
  padding: 5px 5px 5px 5px !important;
  color: #93918f;
  cursor: pointer;
}

.reply-recording {
  padding: 5px !important;
}

.reply-recording i {
  text-align: center;
  padding: 5px !important;
  color: #93918f;
  cursor: pointer;
}

.reply-send {
  padding: 5px !important;
  top: -4px;
}

.reply-send i {
  text-align: center;
  padding: 5px !important;
  color: #93918f;
  cursor: pointer;
}

.reply-main {
  padding: 2px 5px !important;
}

.reply-main textarea {
  width: 100%;
  resize: none;
  overflow: hidden;
  padding: 5px !important;
  outline: none;
  border: none;
  text-indent: 5px;
  box-shadow: none;
  height: 100%;
  font-size: 16px;
}

.reply-main textarea:focus {
  outline: none;
  border: none;
  text-indent: 5px;
  box-shadow: none;
}

@media screen and (max-width: 700px) {
  #cbChatContainer {
    top: 0;
    height: 100%;
  }
  .heading {
    height: 70px;
    background-color: #009688;
  }
  .fa-2x {
    font-size: 2.3em !important;
  }
  .heading-avatar {
    padding: 0 !important;
  }
  .heading-avatar-icon img {
    height: 50px;
    width: 50px;
  }
  .heading-compose {
    padding: 5px !important;
  }
  .heading-compose i {
    color: #fff;
    cursor: pointer;
  }
  .heading-dot {
    padding: 5px !important;
    margin-left: 10px !important;
  }
  .heading-dot i {
    color: #fff;
    cursor: pointer;
  }
  .sideBar {
    height: calc(100% - 130px);
  }
  .sideBar-body {
    height: 80px;
  }
  .sideBar-avatar {
    text-align: left;
    padding: 0 8px !important;
  }
  .avatar-icon img {
    height: 55px;
    width: 55px;
  }
  
  .sideBar-main {
    padding: 0 !important;
  }
  .sideBar-main .row {
    padding: 0 !important;
    margin: 0 !important;
  }
  .sideBar-name {
    padding: 10px 5px !important;
  }
  .name-meta {
    font-size: 16px;
    padding: 5% !important;
  }
  .sideBar-time {
    padding: 10px !important;
  }
  .time-meta {
    text-align: right;
    font-size: 14px;
    padding: 4% !important;
    color: rgba(0, 0, 0, .4);
    vertical-align: baseline;
  }
  /*Conversation*/
  .conversation {
    padding: 0 !important;
    margin: 0 !important;
    height: 100%;
    /*width: 100%;*/
    border-left: 1px solid rgba(0, 0, 0, .08);
    /*overflow-y: auto;*/
  }
  .message {
    height: calc(100% - 140px);
  }
  .reply {
    height: 70px;
  }
  .reply-emojis {
    padding: 5px 0 !important;
  }
  .reply-emojis i {
    padding: 5px 2px !important;
    font-size: 1.8em !important;
  }
  .reply-main {
    padding: 2px 8px !important;
  }
  .reply-main textarea {
    padding: 8px !important;
    font-size: 18px;
  }
  .reply-recording {
    padding: 5px 0 !important;
  }
  .reply-recording i {
    padding: 5px 0 !important;
    font-size: 1.8em !important;
  }
  .reply-send {
    padding: 5px 0 !important;
  }
  .reply-send i {
    padding: 5px 2px 5px 0 !important;
    font-size: 1.8em !important;
  }
}
#chatNovaMensagem p{
	margin: 0px 0px 0px 0px;
}
#chatNovaMensagem img {
    height: 40px !important;
    Xfloat: left;
    margin-left: 3px;
	margin-bottom: 1px;
    border: 1px solid silver;
}
#chatResposta .mce-content-body {
    height: 60px;
    overflow: hidden;
}
.message {
    height: calc(100% - 141px);
}
.reply {
    height: 82px;
}
.message-text p{
	margin: 0px 0px 0px 0px;
}
.message-text img {
    width: 30%;
    Xfloat: left;
    margin-left: 3px;
	margin-bottom: 3px;
    border: 1px solid silver;
}

.message-text img::before{
	content: "\f054";
	font-family: FontAwesome;
}

.cbItemNotificacao .cbItem{
	float: left;
	max-height: 30px;
}
.cbItemNotificacao .cbItem .cbMsg{
    display: block;
    max-width: 170px;
    text-overflow: ellipsis;
    overflow: hidden;
}
.cbItemNotificacao .cbData{
    float: right;
    margin: 0px;
    padding: 0px;
    top: -2px;
    position: relative;
    font-size: 9px;
    color: silver;
    background-color: white;
}
.cbItemNotificacao .cbItem .cbNomePessoa{
	line-height: 14px;
	display: block;
    font-size: 9px;
    font-weight: bold;
    margin: 0px;
    padding: 0px;
    vertical-align: top;
	color: silver;
}
.cbItemNotificacao{
	border-bottom: 1px solid #ededed;
	padding: 3px 0px;
	height: 38px;
	font-size: 11px;
}
.cbItemNotificacao.statusL{
	background-color: white;
}
.cbItemNotificacao.statusN{
	background-color: #edf2fa;
}

.cbItemNotificacao i.cbAvatar{
    margin: 0px 6px;
    /* background-image: url(); */
    height: 30px;
    width: 30px;
    background-size: 30px;
    background-repeat: no-repeat;
    border-radius: 50%;
	display: inline-block;
	float: left;
}

.cbItemNotificacao .cbItem p {
    display: inline-block;
	white-space: nowrap;
}

.cbItemNotificacao .cbItem img {
    display: none;
}

.cbItem i {
    margin: 0px 10px;
    line-height: inherit;
}
.sideBar-naoLidas{
	bottom: -10px;
    position: absolute;
    right: 0px;
}
.chatNovaMsg{
	display:none;
}
.messageMainreceiver .chatNovaMsg{
	position: absolute;
	left: 4px;
	top: -2px;
	color: #ff6363;
	font-size: 15px;
	text-shadow: 2px 3px 1px silver;
}
.messageMainreceiver .chatNovaMsg[status=N]{
	display: block;
}

.chatStatusEntrega{
	display: none;
}

.messageMainsender .chatStatusEntrega[status=N]{
	display: none;
}
.messageMainsender .chatStatusEntrega[status=L]{
	display: block;
	color: #4FC3F7;
}
.sideBar-body.conversando{
	background-color: #e9ebeb;
}
.sideBar-body.selecionado{
	background-color: #c7ffc7;
}
.sideBar-body.selecionado .checkContato{
	display: block !important;
	position: absolute;
    top: 20px;
}
#chatTbNovaMensagemPopup[immsgtipo=T] #chatITipoMsg{
	color: #ec971f;
	opacity: 1;
}
#chatTbNovaMensagemPopup #chatIDataIniMsg{
	display:none;
}
#chatTbNovaMensagemPopup[immsgtipo=T] #chatIDataIniMsg{
	display: block;
}
#chatTbNovaMensagemPopup[immsgtipo=A] #chatIDataIniMsg{
	display: none;
}
.chatTbMsg{
	Xtable-layout: fixed; /* @todo: word-wrap nao funciona. trocar por divs */
	width: 100%;
}
.chatTbMsg .msgIndicadores{
	vertical-align: top;
	width: 3%;
}
.chatTbMsg, .chatTbMsg tr, .chatTbMsg td{
	padding:0px;
	margin: 0px
}
#chatTbNovaMensagemPopup .mce-content-body{
	height: auto !important;
}
</style>
<div id="cbChatContainer">
  <div id="cbChatCorpo" class="row">
    <div class="col-sm-5 side">
      <div class="side-one">
        <div class="row heading">
          <div class="col-sm-3 col-xs-3 heading-avatar">
            <div class="heading-avatar-icon">
				<img src="inc/img/avatarprofile.png" id="avatarProfile" title="Clique para alterar a foto de seu perfil">
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
				<i id="menuFiltrosIcone" class="fa fa-filter fa-2x cinzaclaro hoverlaranja pointer" data-toggle="dropdown"></i>
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
		  
        </div>
        <div id="cbChatPainelContatos" class="row sideBar">
			<div id="cbChatContatosOnlineNotificacao"></div>
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
        <div class="col-sm-7 col-xs-7 heading-name" id="chatCabecalho">
          <a class="heading-name-meta" id="chatNome"></a>
		  <div class="heading-name-data" id="chatCabInfo"></div>
          <span class="heading-online" id="chatStatus"></span>
        </div>
        <div class="col-sm-1 col-xs-1 heading-dot pull-right" title="Fechar">
			<i class="fa fa-close fa-2x  pull-right fade" aria-hidden="true" onclick="$('#cbChatContainer').hide()"></i>
        </div>
        <div class="col-sm-1 col-xs-1 heading-dot pull-right" title="Maximizar/Restaurar">
			<i class="fa fa-window-maximize fa-2x  pull-right fade" aria-hidden="true" onclick="chat.toggleFullscreen()" id="cbChatFullscreen"></i>
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
        <div class="col-sm-1 col-xs-1 reply-send" title="Enviar" onclick="chat.enviar()">
          <i class="fa fa-send fa-3x" aria-hidden="true"></i>
        </div>
      </div>
    </div>
	<i class="fa fa-close fa-2x pull-right pointer" style="margin-right: 10px;margin-top: 5px;color: silver;" onclick="$('#cbChatContainer').hide()"></i>
  </div>
</div><script>
$(document).ready(function() {
	console.log('opaaaaaaa1');
    	$('.message-text img').on('click', function() {
			console.log('opaaaaaaa2');
			$('.enlargeImageModalSource').attr('src', $(this).attr('src'));
			$('#enlargeImageModal').modal('show');
		});
		console.log('opaaaaaaa3');
});	</script>`;
                                
	self.toggleMsgTarefa=function(){
		$oTbPopup=$("#chatTbNovaMensagemPopup");
		if($oTbPopup.attr("immsgtipo")=="M" || $oTbPopup.attr("immsgtipo")=="A"){
			$oTbPopup.attr("immsgtipo","T");
		}else{
			$oTbPopup.attr("immsgtipo","M");
		}
	};
        
        self.toggleMsgAssinatura=function(){
		$oTbPopup=$("#chatTbNovaMensagemPopup");
		if($oTbPopup.attr("immsgtipo")=="M" || $oTbPopup.attr("immsgtipo")=="T"){
			$oTbPopup.attr("immsgtipo","A");
		}else{
			$oTbPopup.attr("immsgtipo","M");
		}
	};
	
	self.enviarMsgModal=function(){
		//Recupera todos os contatos do modal
		$oContatos=CB.oModal.find("#cbChatPainelContatos .contato.selecionado");
		$aContatos=[];

		//Separa os contatos que foram selecionados e coloca em array
		var iC=0;
		$.each($oContatos, function(i,o){
			iC++;
			$o=$(o);
			$aContatos.push({'idcontato':$o.attr("idcontato"),'objetocontato':$o.attr("objetocontato")})
			//console.log(o);
		});

		//Recupera a mensagem
		var txtMsg=tinymce.get("chatNovaMensagemPopup").getContent();
		//Recupera o tipo da mensagem
		var sMsgtipo = $("#chatTbNovaMensagemPopup").attr("immsgtipo");
                
                if(sMsgtipo != "T" && sMsgtipo !="M" && sMsgtipo !="A" ){
                    sMsgtipo="M";
                }
	
                // sMsgtipo = sMsgtipo=="T"?"T":"M";
                
		var $oDtIni = $("#chatIDataIniMsg");
                
		var sDataIni = sMsgtipo=="T"?$oDtIni.val():"";

		//Verifica se o usuário informou a data para a tarefa
		if(sMsgtipo==="T" && sDataIni.length<=1){
			$oDtIni.removeClass("highlight").addClass("highlight");
			//console.log(txtMsg);
			alertAtencao("Informe corretamente uma data para a tarefa!");
			return false;
		}
		
		//Verifica se o usuário digitou algo
		if(self.html2Txt(txtMsg).length<=0){
			$("#chatNovaMensagemPopup").removeClass("highlight").addClass("highlight");
			alertAtencao("Informe a mensagem corretamente");
			return false;
		}

		//Verifica se o usuário selecionou algum contato
		if(iC==0){
			alertAtencao("Selecione os contatos desejados");
			return false;
		}

		//Recupera o anexo
		var hAnexo=$("#chatArqPopup").html();
		var vNomeAnexo = self.html2Txt($("#chatArqPopupNome").html());
		//
                var moduloPk = getUrlParameter(CB.jsonModulo.parget); 
                var moduloNome =CB.modulo;
		//Envia a mensagem
		chat.enviar({
			contatos: $aContatos
			,msg: txtMsg
			,msgtipo: sMsgtipo                        
			,datatarefa: sDataIni
			,modulopk: moduloPk
			,modulo: moduloNome
			,anexo: {"link": hAnexo, "nome": vNomeAnexo}
			,callback: function(sucesso, data,textStatus,jqXHR){
				if(sucesso){
					alertSalvo("Mensagem enviada");
					CB.oModal.modal('hide');
				}else{
					alertErro(data);
				}
			}
		});	
	};

	self.toggleOpcoesMsg=function(inMsgMsg){
		console.log(inMsgMsg);
	};

	return self;
}


//Excluir este método após cache
function removerParametroGet(key, url) {
    if (!url) url = window.location.href;

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
	

$(document).ready(function() {
	$(document).ready(function(){
		if(CB.logado && gPermissaochat=="Y"){
			/*
			 * htmlListaNotificacaoItem: variáveis (placeholders) possíveis:
			 *		%idimmsg%
			 *		%msg%
			 *		%title%
			 *		%icone%
			 */
			chat = new Chat();

			chat.init({
				notificacoes: "#cbNotificacoes"
				,badge: "#cbNotificacoes #cbBadge"
				,iBadge: "#cbNotificacoes #cbIBadge"
				,badgeTarefa: "#cbSnippet2"
				,iBadgeTarefa: "#cbIBadgeSnippet2"
				,listaNotificacoes: "#cbNotificacoes #cbListaNotificacoes"
				,listaNotificacoesHeader: "#cbNotificacoes #cbListaNotificacoesHeader"
				,listaNotificacoesFooter: "#cbNotificacoes #cbListaNotificacoesFooter"
				,chatContainer: "#cbChatContainer"
				/*,htmlListaNotificacaoItem: `<li class="cbItemNotificacao pointer status%status%" idcontato="%idcontato%" idimmsg="%idimmsg%" title="%title%" onclick="chat.abrirConversa(%idcontato%,%objetocontato%,%idimmsg%);chat.abrirContainerChat();">
						%icone%				
						<span class="cbItem">
							<div class="cbNomePessoa">%nomepessoa%</div>
							<div class="cbMsg">%msg%</div>
						</span>
						<div class="cbData">%data%</div>
					</li>`*/
			});
			//chat
			refresh=function(inAcao){
					chat.historico({
						acao: inAcao||"refresh" /* historico | refresh */
						,callback: function(inMsgs){
							//console.log(inHist);
							var iNovamsg=0;
							var iNovatarefa=0;
							var iTarefavencida=0;
                                                        var dataHoje = (new Date()).toISOString().split("T")[0];
							var ultimaMsg=false;
							var ultimaTarefa=false;
							var oContatosMover={};
							$.each(inMsgs, function(i, msg){
								//Caso não exista o sender pela immsgbody, ignorar o erro (msgbody apagado) e tentar montar a próxima mensagem
								if(msg.tipo=="M" && !msg.sender){
									return true;
								}

								//Caso seja uma tarefa criada pelo usuário para ele mesmo
								if(msg.sender=="eu" && (msg.tipo=="T" || msg.tipo=="A")){
									self.incrementarBadgeTarefa(msg);
									
									if (msg.datatarefa){
										//var rotuloContato=self.jContatos[self.msg2hid(msg)].rotulo||"?";
										//var avatarContato=self.jContatos[self.msg2hid(msg)].arquivoavatar||"";
										var hIcone = `<i class="cbAvatar tipo${msg.tipo} ${(msg.tipo=="M")?"":"fa fa-envelope-o"}" style="background-image:url(${(msg.tipo=="M")?(avatarContato!=="")?chat.avatarDir+avatarTarefa:chat.avatarTarefa:""});"></i>`;

										//var oBadge_contato=$("#badge_contato_"+self.msg2hid(msg));
										//Incrementar o badge e armazenar mensagem não lida no objeto DATA do Jquery
                                                                            if(msg.tipo=="T"){
										var datadaTarefa = (new Date(msg.datatarefa)).toISOString().split("T")[0];
                                                                            }else{
                                                                                var datadaTarefa = (new Date(msg.datatarefa)).toISOString().split("A")[0];
                                                                            }
										if(datadaTarefa < dataHoje && msg.statustarefa == 'A'){
											iNovatarefa++;
											//$("#contato_"+self.msg2hid(msg)).data("mensagens").naolidas[msg.idimmsg]=msg;
											//var iMensagensNovas=parseInt(oBadge_contato.attr("imensagens"))+1;
											//oBadge_contato.removeClass("hidden").attr("imensagens",iMensagensNovas).html(iMensagensNovas);
											//oContatosMover[self.msg2hid(msg)]="topo";

											//Armazena última mensagem
											ultimaTarefa=msg;
											//self.incrementarBadge();
										}
									}			
												
									//return true;//Interrompe aqui mas continua o loop
								}else{

									//Caso exista alguma mensagem não lida que o usuário não pode ver mais
									if(msg.tipo=="M" && msg.sender!=="eu" && !self.jContatos[self.msg2hid(msg)]){
										console.error("js: refresh: callback: Existiam mensagens não lidas que o usuário não pode ver mais. Foram marcadas como lidas no servidor");
										self.marcarLida({idcontato:msg.sender,objetocontato:msg.objetocontato,objetocontato:msg.objetocontato,idimmsg:msg.idimmsg});
										return true;
									}
									
									//Conforme o tipo de mensagem realiza tratamento específico
									if(msg.tipo=="E"){
										self.trataEvento(msg);
										
									}else if(msg.tipo=="M" || msg.tipo=="T" || msg.tipo=="A"){

										//Incrementa badge de mensagem que foi transformada em tarefa
										if(msg.tipo=="T"){
											self.incrementarBadgeTarefa(msg);
											
												if (msg.datatarefa){
													var rotuloContato=self.jContatos[self.msg2hid(msg)].rotulo||"?";
													var avatarContato=self.jContatos[self.msg2hid(msg)].arquivoavatar||"";
													var hIcone = `<i class="cbAvatar tipo${msg.tipo} ${(msg.tipo=="M")?"":"fa fa-envelope-o"}" style="background-image:url(${(msg.tipo=="M")?(avatarContato!=="")?chat.avatarDir+avatarTarefa:chat.avatarTarefa:""});"></i>`;

													//var oBadge_contato=$("#badge_contato_"+self.msg2hid(msg));
													//Incrementar o badge e armazenar mensagem não lida no objeto DATA do Jquery
													var datadaTarefa = (new Date(msg.datatarefa)).toISOString().split("T")[0];
													if(datadaTarefa < dataHoje && msg.statustarefa == 'A'){
														iNovatarefa++;
														//$("#contato_"+self.msg2hid(msg)).data("mensagens").naolidas[msg.idimmsg]=msg;
														//var iMensagensNovas=parseInt(oBadge_contato.attr("imensagens"))+1;
														//oBadge_contato.removeClass("hidden").attr("imensagens",iMensagensNovas).html(iMensagensNovas);
														//oContatosMover[self.msg2hid(msg)]="topo";
			
														//Armazena última mensagem
														ultimaTarefa=msg;
														//self.incrementarBadge();
													}
			
													//Verifica se o chat está aberto para mostrar imediatamente a nova mensagem
													//if(chat.oChatBody.is("[idcontato="+msg.sender+"]:visible")){
													//	if(chat.oChatBody.find(".message-body[idimmsg="+msg.idimmsg+"]").length<=0){
													//		self.mostraBalao(msg);
													//		self.scrollParaMensagem();
													//	}
													///}
												}
												
												
										}else if(msg.tipo=="A"){
                                                                                    self.incrementarBadgeTarefa(msg);
											
                                                                                    if (msg.datatarefa){
                                                                                            var rotuloContato=self.jContatos[self.msg2hid(msg)].rotulo||"?";
                                                                                            var avatarContato=self.jContatos[self.msg2hid(msg)].arquivoavatar||"";
                                                                                            var hIcone = `<i class="cbAvatar tipo${msg.tipo} ${(msg.tipo=="M")?"":"fa fa-envelope-o"}" style="background-image:url(${(msg.tipo=="M")?(avatarContato!=="")?chat.avatarDir+avatarTarefa:chat.avatarTarefa:""});"></i>`;

                                                                                            //var oBadge_contato=$("#badge_contato_"+self.msg2hid(msg));
                                                                                            //Incrementar o badge e armazenar mensagem não lida no objeto DATA do Jquery
                                                                                            var datadaTarefa = (new Date(msg.datatarefa)).toISOString().split("A")[0];
                                                                                            if(datadaTarefa < dataHoje && msg.statustarefa == 'A'){
                                                                                                    iNovatarefa++;
                                                                                                    //$("#contato_"+self.msg2hid(msg)).data("mensagens").naolidas[msg.idimmsg]=msg;
                                                                                                    //var iMensagensNovas=parseInt(oBadge_contato.attr("imensagens"))+1;
                                                                                                    //oBadge_contato.removeClass("hidden").attr("imensagens",iMensagensNovas).html(iMensagensNovas);
                                                                                                    //oContatosMover[self.msg2hid(msg)]="topo";

                                                                                                    //Armazena última mensagem
                                                                                                    ultimaTarefa=msg;
                                                                                                    //self.incrementarBadge();
                                                                                            }

                                                                                            //Verifica se o chat está aberto para mostrar imediatamente a nova mensagem
                                                                                            //if(chat.oChatBody.is("[idcontato="+msg.sender+"]:visible")){
                                                                                            //	if(chat.oChatBody.find(".message-body[idimmsg="+msg.idimmsg+"]").length<=0){
                                                                                            //		self.mostraBalao(msg);
                                                                                            //		self.scrollParaMensagem();
                                                                                            //	}
                                                                                            ///}
                                                                                    }
                                                                                
                                                                                }else{

											if(self.jContatos[self.msg2hid(msg)]){//Verifica se AINDA Ã© um contato vÃ¡lido (existente)
												var rotuloContato=self.jContatos[self.msg2hid(msg)].rotulo||"?";
												var avatarContato=self.jContatos[self.msg2hid(msg)].arquivoavatar||"";
												var hIcone = `<i class="cbAvatar tipo${msg.tipo} ${(msg.tipo=="M")?"":"fa fa-envelope-o"}" style="background-image:url(${(msg.tipo=="M")?(avatarContato!=="")?chat.avatarDir+avatarContato:chat.avatarDefault:""});"></i>`;

												var oBadge_contato=$("#badge_contato_"+self.msg2hid(msg));
												//Incrementar o badge e armazenar mensagem não lida no objeto DATA do Jquery
												if(msg.status=="N" && msg.sender!=="eu" && !$("#contato_"+self.msg2hid(msg)).data("mensagens").naolidas[msg.idimmsg]){
													iNovamsg++;
													$("#contato_"+self.msg2hid(msg)).data("mensagens").naolidas[msg.idimmsg]=msg;
													var iMensagensNovas=parseInt(oBadge_contato.attr("imensagens"))+1;
													oBadge_contato.removeClass("hidden").attr("imensagens",iMensagensNovas).html(iMensagensNovas);
													oContatosMover[self.msg2hid(msg)]="topo";
		
													//Armazena última mensagem
													ultimaMsg=msg;
													self.incrementarBadge();
												}
		
												//Verifica se o chat está aberto para mostrar imediatamente a nova mensagem
												if(chat.oChatBody.is("[idcontato="+msg.sender+"]:visible")){
													if(chat.oChatBody.find(".message-body[idimmsg="+msg.idimmsg+"]").length<=0){
														self.mostraBalao(msg);
														self.scrollParaMensagem();
													}
												}
											}
										}
										/* /Os parâmetros enviados abaixo substituirão automaticamente qualquer placeholder %string% na variavel htmlListaNotificacaoItem para o método Chat.init({})
										chat.novoItemListaNotificacoes({
											idimmsg: msg.idimmsg
											,idcontato: msg.sender
											,nomepessoa: nomeContato+"["+msg.idimmsg+"]"
											,avatarpessoa: avatarContato
											,msg: self.html2Ico(msg.msg)//Substituir imagens por icones
											,status: msg.status
											,title: self.html2Txt(msg.msg)//Descartar tags html e extrair somente o texto puro
											,icone: hIcone
											,data: moment(msg.data).fromNow()
											//Callback instanciado pelo método novoItemListaNotificacoes
											,onNovaMensagem: function(inIdimmsg){
												//Se for uma nova mensagem que não estiver listada
												iNovamsg++;
												ultimaMsg=msg;
											}
										});*/
									}else if(msg.tipo=="X"){
										null;
									}else{
										console.warn("Tipo de mensagem não previsto: "+msg.tipo);
									}
								}//if(msg.sender=="eu"){}else{
							});
							//Depois de ler todas as mensagens
							if(iNovamsg>0){
								self.notificacao({
									beep: true
									,iNovasMensagens: iNovamsg
									,ultimaMensagem: ultimaMsg
									,mensagens: inMsgs
									,icone: self.jContatos[self.msg2hid(ultimaMsg)].arquivoavatar
									//O callback abaixo será executado no clique no popup mostrado pelo browser
									,callback: function(inArgsNotificacao){
										self.abrirContainerChat();
										self.maximizar();
										self.montarContatos();
										//if(inArgsNotificacao.iNovasMensagens&&inArgsNotificacao.iNovasMensagens==1){
											//if(inArgsNotificacao.ultimaMensagem.sender && inArgsNotificacao.ultimaMensagem.objetocontato){
												if(inArgsNotificacao.ultimaMensagem.idimgrupo){
													var objContato="imgrupo";
													var idContato=inArgsNotificacao.ultimaMensagem.idimgrupo;
												}else{
													var objContato="pessoa";
													var idContato=inArgsNotificacao.ultimaMensagem.sender;
												}
												
												self.abrirConversa(idContato,objContato);
												$('#chatNovaMensagem').focus();
											//}
										//}
									}
								});
								//if(inAcao){
									//Movimentar entre os grupos do painel de contatos
									self.moverContatos(oContatosMover);
								//}
							}
							
							
							
							if(iNovatarefa>0){
								self.notificacao({
									beep: true
									,iNovasMensagens: iNovatarefa
									,ultimaMensagem: ultimaTarefa
									,mensagens: inMsgs
									,icone: self.avatarTarefa
									//O callback abaixo será executado no clique no popup mostrado pelo browser
									,callback: function(inArgsNotificacao){
										$(CB.oModuloHeader, CB.oModuloHeaderBg).addClass('hidden');CB.loadUrl({urldestino: 'form/_dashboard.php'});
										//if(inArgsNotificacao.iNovasMensagens&&inArgsNotificacao.iNovasMensagens==1){
											//if(inArgsNotificacao.ultimaMensagem.sender && inArgsNotificacao.ultimaMensagem.objetocontato){
												if(inArgsNotificacao.ultimaMensagem.idimgrupo){
													var objContato="imgrupo";
													var idContato=inArgsNotificacao.ultimaMensagem.idimgrupo;
												}else{
													var objContato="pessoa";
													var idContato=inArgsNotificacao.ultimaMensagem.sender;
												}
												
												//self.abrirConversa(idContato,objContato);
											//}
										//}
									}
								});
								//if(inAcao){
									//Movimentar entre os grupos do painel de contatos
									//self.moverContatos(oContatosMover);
								//}
							}
						}
					});
			};
			
			//Para testes
			//if(window.location.host==="localhost"){
			//	self.abrirContainerChat();self.maximizar();self.montarContatos();
			//}

			refresh();
			setInterval(
				function(){
					refresh("refresh");
				}
				,self.intervaloRefresh
			);
		}
	});
});

function htmldecode(str){
  var d = document.createElement("div");
  d.innerHTML = str; 
  return typeof d.innerText !== 'undefined' ? d.innerText : d.textContent;
}

function cloneContatosChat(inOpt){
	inOpt=inOpt||{};

	//Copia o painel de contatos
	$oContatos = $($("#cbChatCorpo .side").html());

	//Altera o evento de clique
	$.each($($oContatos).find("[idcontato]"), function(i,oc){
		$oc=$(oc);
		//Elimina o evento onclick e cria outro, preparado para callback
		$oc.attr("onclick","").on("click",function(){
			$o=$(this);
			if($o.hasClass("selecionado")){
				$o.removeClass("selecionado");
			}else{
				$o.addClass("selecionado");
			}
			//Executa ação customizada após clique no contato
			if(inOpt.onclick && typeof inOpt.onclick==="function"){
				inOpt.onclick($o);
			}
		});
		$oc.find(".badge").html("");
	});

	return $oContatos;
}

function cloneNovaMensagemChat(inOpt){

	//Copia o painel de contatos
	$oNMsg = $($("#chatResposta").html());

	return $oNMsg;
}

function compartilharItem(){
	$oContatosChat = cloneContatosChat({
		onclick:function(inContato){
			console.log(inContato);
		}
	});
	
	$oNovaMsg = cloneNovaMensagemChat();
	
	$tbNovaMsg = $(`
	<div id="chatArqPopup" class="hidden"></div>	
	<table width="100%" id="chatTbNovaMensagemPopup" immsgtipo="M">
	<tr>
		<td><label>Assunto:</label></td>
		<td></td>
               
	</tr>
         <tr>
		<td colspan="4">
			<div class="form-control" rows="1" id="chatNovaMensagemPopup"></div>
		</td>
		<td>
			<i class="fa fa-send fa-3x cinza fade pointer" aria-hidden="true" title="Compartilhar/Enviar" onclick="chat.enviarMsgModal();"></i>
		</td>
	</tr>
        <tr>
            <td class="">
                            
            </td>        
        </tr>
        <tr>
            <td><div id="chatArqPopupNome"></div></td>
        </tr>
        <tr>
            <td>
            <div class="btn-group" role="group" aria-label="..."> 
                
                <button onclick="chat.toggleMsgAssinatura()" type="button" class="_btnAssinatura btn btn-default fa fa-edit hoverlaranja pointer" title="Transformar em assinatura" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>  
				<button onclick="chat.toggleMsgTarefa()" type="button" class="_btnCompartilha btn btn-default fa fa-calendar-check-o hoverlaranja pointer" title="Transformar em tarefa" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>
				<input id="chatIDataIniMsg" class="calendario pull-left" style="width:100px" placeholder="Prazo">  
            </div>
            </td>
        </tr>

	
	</table>`);
	
	$tbNovaMsg.find("#colmsg").append($oNovaMsg);
	
	//Altera o cabeçalho do modal
	strCabecalho="<label class='fa fa-share-alt'></label>&nbsp;&nbsp;Compartilhar item:";
	$("#cbModalTitulo").html(strCabecalho);

	//Mostra o popup
	$('#cbModal #cbModalCorpo')
			.find("*").remove();
	
	$('#cbModal #cbModalCorpo')
			.append($tbNovaMsg)
			.append("<hr>")
			.append($oContatosChat);
	
	//Editor rte
	tinymce.init({
		selector: "#chatNovaMensagemPopup"
		,language: 'pt_BR'
		,inline: true /* não usar iframe */
		,menubar: false
		//,paste_as_text: true
		,paste_data_images: true
		,plugins: 'paste image imagetools lists'
		//,toolbar: 'removeformat bold bullist numlist'
		,toolbar: false
		,fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt"
		,removeformat: [
			{selector: 'b,strong,em,i,font,u,strike', remove : 'all', split : true, expand : false, block_expand: true, deep : true},
			{selector: 'span', attributes : ['style', 'class'], remove : 'empty', split : true, expand : false, deep : true},
			{selector: '*', attributes : ['style', 'class'], split : false, expand : false, deep : true}
		]
		,setup: function (editor) {
			editor.on('KeyDown', function(event) {
				if (event.keyCode == 13 && !event.shiftKey){
					event.preventDefault();
					event.stopPropagation();
					//salva
					self.enviar();
					return false;
				}
			});
		}

	});
	CB.estilizarCalendarios();
	
	$('#cbModal').addClass('quarenta').modal();

	//Insere o hyperlink para compartilhamento
	var sLink=window.location.search;
	
	//Prepara a descrição para o hyperlink
	var sADesc=removerParametroGet("_modulo",sLink);
	sADesc=removerParametroGet("_acao",sADesc);
	sADesc=sADesc.replace(/^\?/,"");
	sADesc=(CB.jsonModulo.rotulomenu||document.title||"")+": "+sADesc;
	
	sADesc='<a href="'+sLink+'" target="_blank" class="pointer"><i class="fa fa-paperclip">&nbsp;</i>'+sADesc+'</a><p></p>'
/*
	tinymce.get("chatNovaMensagemPopup").insertContent(
		'<a href="'+sLink+'" target="_blank" class="pointer"><i class="fa fa-external-link">&nbsp;</i>'+sADesc+'</a><p></p>'
	);
*/
	//var sA = '<a href="'+sLink+'" target="_blank" class="pointer"><i class="fa fa-external-link">&nbsp;</i>'+sADesc+'</a>'
	$("#chatArqPopup").html(sLink);
	$("#chatArqPopupNome").html(sADesc);
}


function compartilharTarefa(){

	//Recupera todos os contatos do modal
	$oContatos=CB.oModal.find("#cbChatPainelContatos .contato.selecionado");
	$aContatos=[];

	//Separa os contatos que foram selecionados e coloca em array
	var iC=0;
	$.each($oContatos, function(i,o){
		iC++;
		$o=$(o);
		$aContatos.push({'idcontato':$o.attr("idcontato"),'objetocontato':$o.attr("objetocontato")})
		//console.log(o);
	});

	//Recupera a mensagem
	var txtMsg=tinymce.get("chatNovaMensagemPopup").getContent();

	//Recupera o idimmsgbody
	idimmsgbody=$("#chatTbNovaMensagemPopup").attr("idimmsgbody");

	//Verifica se o usuário digitou algo
	if(self.html2Txt(txtMsg).length<=0){
		$("#chatNovaMensagemPopup").removeClass("highlight").addClass("highlight");
		alertAtencao("Informe a mensagem corretamente");
		return false;
	}

	//Verifica se o usuário selecionou algum contato
	if(iC==0){
		alertAtencao("Selecione os contatos desejados");
		return false;
	}
	
	//Compartilhar a mensagem
	chat.enviar({
		msgtipo:'T'
		,contatos: $aContatos
		,msg: txtMsg
		,idimmsgbody: idimmsgbody
		,callback: function(sucesso, data,textStatus,jqXHR){
			if(sucesso){
				alertSalvo("Mensagem compartilhada");
				CB.oModal.modal('hide');
			}else{
				alertErro(data);
			}
		}
	});	
	

}

