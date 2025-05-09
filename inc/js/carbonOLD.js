//Framework Carbon
function carbon() {

	return {

		logado: false,
		ajaxPostAtivo: false,
		autoLoadUrl: "",
		locationSearch: window.location.search.split("?")[1],
		modulo: undefined,
		acao: "",
		jsonModulo: {},
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
            ,"#fcd202"
            ,"#b0de09"
            ,"#0d8ecf"
            ,"#cc0000"
            ,"#999999"
            ,"#cd0d74"
            ,"#0000cc"
            ,"#00cc00"
            ,"#990000"
            ,"#a04000"
            ,"#9f8401"
            ,"#7a9a06"
            ,"#0a536e"
            ,"#505050"
            ,"#680a3e"
            ,"#00004e"
            ,"#008400"],
		fAcoesResultados: {
			html: $("<ul id='cbAcoesResultados' class='pagination'></ul>")
			,item: $("<li><i id='' class='' title='' onclick=''></i></li>")
			,init: function(inPar){
				$that=this;
				if(inPar){
					$.each(inPar, function(i,acao){
						//console.log(acao);
						$that.html.append("<li><i id='moduloacaoid"+acao.moduloacaoid+"' class='"+acao.class+"' title='"+acao.rotulo+"' onclick='"+acao.onclick+"'></i></li>");
					})
				}
			}
		},
		/*
		 * Inicializa as variáveis e objetos padrão
		 */
		init: function(){
			
			CB.oMenuSuperior= $("#cbMenuSuperior");
			CB.oContainer=$("#cbContainer");
			CB.oModuloForm= $("#cbModuloForm");
			CB.oModuloHeader= $("#cbModuloHeader");
			CB.oModuloHeaderBg= $("#cbModuloHeaderBg");
			CB.oModuloBreadcrumb=$("#cbModuloBreadcrumb");
			CB.oModuloPesquisa= $("#cbModuloPesquisa");
			CB.oResultadosInfo = $("<span id='cbResultadosInfo'></span>");
			CB.oIconePesquisando = $("#cbIconePesquisando");
			CB.oModuloResultados=$("#cbModuloResultados");
			CB.oBtRep= CB.oModuloHeader.find("#cbRep");
			CB.oBtNovo= CB.oModuloHeader.find("#cbNovo");
			CB.oBtSalvar= CB.oModuloHeader.find("#cbSalvar");
			CB.oBtCompartilharItem= $("#cbCompartilharItem");
			
			CB.oModuloIcone= CB.oModuloHeader.find("#cbModuloIcone");
			CB.oPainelNavegacao= $(".painelNavegacao");
			CB.oNavRegAtual= $(".painelNavegacao .navRegAtual");
			CB.oBtNavRegAnt= $(".painelNavegacao .gNavRegAnt");
			CB.oBtNavRegProx= $(".painelNavegacao .gNavRegProx");
			CB.oFiltroRapido= $("#cbFiltroRapido");
			CB.oTextoPesquisa= $("#cbModuloPesquisa #cbTextoPesquisa");
			CB.oDaterange= $("#cbDaterange");
			CB.oDaterangeTexto= $("#cbDaterangeTexto");
			CB.jDateRangeLocale = {
			        "format": "DD/MM/YYYY",
			        "separator": " - ",
			        "applyLabel": "Ok",
			        "cancelLabel": "Limpar",
			        "fromLabel": "De",
			        "toLabel": "Até",
			        "customRangeLabel": "Outro intervalo",
			        "daysOfWeek": ["Do","Se","Te","Qu","Qi","Se","Sa"],
			        "monthNames": ["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],
			        "firstDay": 1
			    };
			CB.jDatetimeRangeLocale = {
			        "format": "DD/MM/YYYY h:mm:ss",
			        "separator": " - ",
			        "applyLabel": "Ok",
			        "cancelLabel": "Limpar",
			        "fromLabel": "De",
			        "toLabel": "Até",
			        "customRangeLabel": "Outro intervalo",
			        "daysOfWeek": ["Do","Se","Te","Qu","Qi","Se","Sa"],
			        "monthNames": ["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],
			        "firstDay": 1
			    };
			CB.oCarregando= $("#cbCarregando");
			CB.oTitle=$("#cbTitle");
			CB.acao = getUrlParameter("_acao");
			CB.oModal = $("#cbModal");
			CB.oModalCarregando = $("#cbModalCarregando");
			CB.gToken="N";
			
			//Caso o usuário não esteja logado, inicializar tela de login, e caso esteja logado, mas não exista módulo informado, informar vazio
	    	CB.modulo=(CB.logado)?this.modulo||"":"_login";
	    	
			CB.oPanelLegenda = $("#cbPanelLegenda");
			
                        //verifica a existencia de token do sislaudo
                        CB.sToken = getUrlParameter('_token')==''?getUrlParameter('token'):getUrlParameter('_token');
                        CB.sToken=(CB.sToken!=='')?'&_token='+CB.sToken:'';
                        
			/*
			 * Coloca o formulario em modo autoload. Executa primeiro a chamada do formulario,
			 * e a pesquisa irá ser carregada em modo hidden, devido à adição classes css !important
			 */
			if(CB.autoLoadUrl=="Y"){
				$("body").addClass("minimizado").addClass("autoloadurl");
			}
				
			/*
			 * Pressionamento de teclas no campo de pesquisa
			 */
			CB.oTextoPesquisa.on("keyup", function(e){
				CB.limparResultados=true;
				CB.resetVarPesquisa();
				CB.pesquisaAjaxStringAtual=this.value;
				var keyCode = e.keyCode || e.which;
				//Executa Pesquisa com Enter
				if(keyCode==13){
					CB.pesquisar({resetVarPesquisa: true});
				}
				//Reinicializa pesquisa com Backspace
				if(keyCode==8 && this.value.length===0){
					CB.resetModPesquisa();
				}
			});

			CB.oMenuSuperior.on("mouseenter", function(e){
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
		           '7 dias': [moment().subtract(6, 'days'), moment()],
		           'Últimos 30 dias': [moment().subtract(29, 'days'), moment()],
				   'Próximos 7 dias': [moment(), moment().add(6, 'days')],
		           'Próximos 30 dias': [moment(), moment().add(29, 'days')],
		           'Este mês': [moment().startOf('month'), moment().endOf('month')],
		           'Mês passado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
				   'Últimos 365 dias': [moment().subtract(365, 'days'), moment()]
		        },
			    "opens": "left"

			}).on('cancel.daterangepicker', function(ev, picker) {
				CB.oDaterange.val('').attr("cbdata","").addClass("cinzaclaro");
				CB.oDaterangeTexto.html("");
				CB.limparResultados=true;
				CB.resetVarPesquisa();
				
			}).on('apply.daterangepicker', function(ev, picker) {
				CB.setIntervaloDataPesquisa(picker.startDate, picker.endDate);
				CB.limparResultados=true;
				CB.resetVarPesquisa();
			});
		},
		
		/*
		 * Ajusta o calendario da caixa de pesquisa 
		 */
		setIntervaloDataPesquisa: function(inMomentIni, inMomentFim, inRotulo){
			if(inRotulo){
				CB.oDaterangeTexto.html(inRotulo);
			}else{
				CB.oDaterangeTexto.html(inMomentIni.format("DD/MM/YY") + ' ' + inMomentFim.format("DD/MM/YY"));			
			}
			CB.oDaterange.removeClass("cinzaclaro").attr("cbdata",inMomentIni.format("DD/MM/YYYY") + '-' + inMomentFim.format("DD/MM/YYYY"));
			CB.oDaterange.data('daterangepicker').setStartDate(inMomentIni);
			CB.oDaterange.data('daterangepicker').setEndDate(inMomentFim);
		},

		/*
		 * Reinicializa o módulo de pesquisa, resetando variáveis e painel de resultados
		 */
		resetModPesquisa: function(){
			CB.resetVarPesquisa();
			CB.resetDadosPesquisa();
			
	    	$.ajax({
				type: 'get',
				cache: false,
				url: "form/_moduloconf.php",
				data: "_modulo="+CB.modulo+"&_userPref=resetFts",
				success: function(data){
					console.info("resetModPesquisa: Reset de preferências executado incondicionalmente. Verificar servidor.");
				}
			});
		},

		/*
		 * Reinicializa variáveis para nova pesquisa
		 */
		resetVarPesquisa: function(){
			CB.pesquisaAjaxAtivo=false;
			CB.pesquisaAjaxPagina=1;
			CB.pesquisaAjaxTotalPaginas=0;
		},
		/*
		 * Limpar da tela os dados de pesquisa existentes 
		 */
		resetDadosPesquisa: function(){
			console.info("resetDadosPesquisa: Pesquisa reinicializada");
			CB.oModuloResultados.addClass("hidden").html("");
			//CB.oResultadosInfo.html("");
			CB.limparResultados=false;
		},
		/*
		 * Login na aplicação
		 */
		login: function(){
			this.post();
		},

		/*
		 * Recupera os parâmetros de configuração do módulo e os campos de filtro
		 */
	    inicializaModulo: function(opt){

	    	CB.oCarregando.show();
                
	    	$.ajax({
				type: 'get',
				cache: false,
				url: "form/_moduloconf.php",
				data: "_modulo="+CB.modulo+CB.sToken,
				beforeSend: function(){
					//reset
				},
				success: function(data){
	
					//Valida json contendo os campos de filtro
					var jsonMod = jsonStr2Object(data); 
					if(!jsonMod){
						alertErro(data);
					}else{
						//Armazena o json completo
						CB.jsonModulo = jsonMod;

						//Inicializa opções
						CB.jsonModulo.jsonpreferencias._filtrosrapidos=CB.jsonModulo.jsonpreferencias._filtrosrapidos||{};
						
						//Inicializa Variaveis Modulo
						CB.modulo = jsonMod.modulo;
						CB.urlDestino = jsonMod.urldestino;
			 				
						//Ajusta titulo da janela
			 			CB.oTitle.html(jsonMod.rotulomenu);	
			 				
						//Ajusta a classe do menu atual para ATIVO
						CB.oMenuSuperior.find("li[cbmodulo="+CB.modulo+"]").addClass("ativo");
						console.info("todo: inicializaModulo: Ajustar classe ATIVO quando estiver selecionado um submenu");

						//Adiciona atributos ao BODY para permitir estilos CSS
						$("body").attr("cbtipo",CB.jsonModulo.tipo).attr("cbready",CB.jsonModulo.ready);
						
						//Adiciona css customizado
						CB.setModCustomCss(jsonMod.csscustom);
						
						//Configura o container bootstap
						if(jsonMod.largurafixa=="Y"){
							CB.oContainer.css("display","block");
						}
						
						//Executa primeiro a chamada do formulario, e após isso a pesquisa irá ser carregada em modo hidden
						//Quando o formulário for do tipo URL, não executar esta chamada para evitar duplicações de ajax
						if(CB.autoLoadUrl=="Y" && jsonMod.ready=="FILTROS"){
							CB.loadUrl({
								urldestino: CB.urlDestino +"?"+CB.locationSearch
							});
						}

						//Prepara o módulo conforme o tipo
						if(jsonMod.ready=="URL"){
							//var vUrl = jsonMod.urldestino +"?_modulo="+jsonMod.modulo;
							vUrl = jsonMod.urldestino + window.location.search;
							//vUrl = alteraParametroGet("_modulo",jsonMod.modulo,vUrl);
							CB.loadUrl({
								//urldestino: jsonMod.urldestino +"?_modulo="+jsonMod.modulo
								urldestino: vUrl
							});

							if(CB.jsonModulo.btsalvar=="Y"){
								CB.oBtNovo.addClass("hidden");
								CB.oBtSalvar.removeClass("disabled")
								CB.oModuloHeader.removeClass("hidden");
								CB.oModuloHeaderBg.removeClass("hidden");

								CB.oTextoPesquisa.attr("placeholder", $("<div/>").html(jsonMod.titulofiltros).text()+"...");
								CB.oModuloIcone.addClass(jsonMod.cssiconepar||jsonMod.cssicone);
								CB.oModuloBreadcrumb.attr("href","?_modulo="+jsonMod.modulo)
								var sBreadcrumb = (jsonMod.rotulomenupar.length==0)?jsonMod.rotulomenu:jsonMod.rotulomenupar+"&nbsp;/&nbsp;"+jsonMod.rotulomenu;
								sBreadcrumb += "<i class='fa fa-angle-down fade' title='Alterar opções para o Módulo'></i>";
								CB.oModuloBreadcrumb.find("#cbModuloBreadcrumbRotulo").html(sBreadcrumb)
								CB.oModuloBreadcrumb.find("#cbModuloBreadcrumbOpcoes")
											.html("<li class='nowrap'><a href='?_modulo=_modulo&_acao=u&modulo="+CB.modulo+"' target='_blank'><i class='fa fa-wrench'></i>Editar Módulo</a></li>");

							}
							
						}else if(jsonMod.ready=="FILTROS"){
							
							//Desabilita o botão de compartilhar
							CB.oBtCompartilharItem.addClass("hidden");
							
			 				//Habilita botao "novo"
							if(CB.jsonModulo.btnovo=="Y") CB.oBtNovo.removeClass("disabled");

							//Habilita o botão de relatórios
							if(tamanho(CB.jsonModulo.relatorios)>0) CB.oBtRep.removeClass("disabled");

							//Mostrar o botão de calendário para FDS
							if(CB.jsonModulo.filtrardata!=="Y"){
								CB.oDaterange.addClass("hidden");
							}

			 				CB.oModuloHeader.removeClass("hidden");
			 				CB.oModuloHeaderBg.removeClass("hidden");
			 				CB.oModuloPesquisa.removeClass("hidden");

			 				//Converte HTML entities para serem mostradas corretamente. Caso contrário, acentos ficariam como &aacute; ou &atilde; etc.
			 				CB.oTextoPesquisa.attr("placeholder", $("<div/>").html(jsonMod.titulofiltros).text()+"...");
			 				CB.oModuloIcone.addClass(jsonMod.cssiconepar||jsonMod.cssicone);
			 				CB.oModuloBreadcrumb.attr("href","?_modulo="+jsonMod.modulo)
			 				var sBreadcrumb = (jsonMod.rotulomenupar.length==0)?jsonMod.rotulomenu:jsonMod.rotulomenupar+"&nbsp;/&nbsp;"+jsonMod.rotulomenu;
							sBreadcrumb += "<i class='fa fa-angle-down fade' title='Alterar opções para o Módulo'></i>";
			 				CB.oModuloBreadcrumb.find("#cbModuloBreadcrumbRotulo").html(sBreadcrumb);
							CB.oModuloBreadcrumb.find("#cbModuloBreadcrumbOpcoes")
											.html("<li class='nowrap'><a href='?_modulo=_modulo&_acao=u&modulo="+CB.modulo+"' target='_blank'><i class='fa fa-wrench'></i> Editar Módulo</a></li>");
									
			 				//Recupera parâmetros de Auto Filtro, para casos em que a pesquisa já vem parametrizada de algum outro módulo
			 				CB.jsonAutoFiltro = CB.getAutoFiltro();
			 				
			 				//Monta painel de Filtros rapidos. Isto deve ser executado antes do resgate das preferências do usuário, para permitir manuseio de valores
							CB.json2Filtros(jsonMod);

							//Prepara Ações (botões) para serem executadas pelo usuário conforme configuração
							if(jsonMod.acoes){
								CB.fAcoesResultados.init(jsonMod.acoes);
							}
							
							//Verifica se o usuário ajustou a preferência de memória da última pesquisa
							jsonMod.jsonpreferencias.memorizaPesquisa=jsonMod.jsonpreferencias.memorizaPesquisa||"N";

							//Verifica se a pagina de pesquisa foi chamada com parametros Json para auto-filtro. Caso contrário recupera as preferências do usuário (Última pesquisa executada por ele)
							if(CB.jsonAutoFiltro===false){
								//Recupera última pesquisa (preferências do usuário)
								if(jsonMod.jsonpreferencias._fts && jsonMod.jsonpreferencias.memorizaPesquisa=="Y"){
									//var strLatin = decodeURIComponent(escape(jsonMod.jsonpreferencias._fts));
									var strLatin = jsonMod.jsonpreferencias._fts;
									CB.oTextoPesquisa.val(strLatin);
								}
								if(jsonMod.jsonpreferencias._fds){
									var arrDatas = jsonMod.jsonpreferencias._fds.split('-');
									CB.setIntervaloDataPesquisa(moment(arrDatas[0],"DD/MM/YYYY"), moment(arrDatas[1],"DD/MM/YYYY"));
								}else{
									//A pedido de Daniel, colocar últimos 30 dias como default
									//Retirado até que se crie uma rotina mais consistente para isto
									//CB.setIntervaloDataPesquisa(moment().subtract(29, 'days'), moment(),"Últimos 30 dias");
								}
								if(jsonMod.jsonpreferencias._filtrosrapidos && jsonMod.jsonpreferencias.memorizaPesquisa=="Y"){
									/*
									 * Atribui os valores incondicionalmente a cada objeto de filtro,
									 * mesmo que recuperação dos valores de preferência(drops) de Filtros serão executados de forma ASSÍNCRONA, dentro da requisição que recupera os registros de cada dropdown
									 * Isto permite que a opção que o usuário selecionou anteriormente seja devolvida à tela APÓS a recuperação de todos os registros do DB, deixando a tela mais fluída e independente
									 */
									$.each(jsonMod.jsonpreferencias._filtrosrapidos, function(k,v){
										CB.oFiltroRapido.find("[cbCol="+k+"]").attr("cbId",v);
									})
								}
							}else{
								$(CB.jsonAutoFiltro).each(function(i, o){

									//Caso a chave [col] ou [id] não tenham sido enviada via json, gerar erro
									if(o.col==undefined) console.error("Js: CB.inicializaModulo: chave 'col' não enviada via Get");
									if(o.id==undefined) console.error("Js: CB.inicializaModulo: chave 'id' não enviada via Get");

									//Verifica se a coluna enviada existe como Filtro Rápido. Caso contrário, monta combinação na caixa de pesquisa textual
									if(CB.oFiltroRapido.find("[cbcol="+o.col+"]").length==1){
										CB.setFiltroRapido({col:o.col,id:o.id,valor:o.valor,rot:o.rot});
									}else{
										CB.oTextoPesquisa.val(CB.oTextoPesquisa.val()+" "+o.col+":"+o.id);
									}
								});
							}

							if(jsonMod.jsonpreferencias.orderby){
								CB.jsonModulo.jsonpreferencias.orderBy = jsonMod.jsonpreferencias.orderby;
							}
							
							//Caso seja recuperacao direta de formulario, nao executar a pesquisa
							if(CB.autoLoadUrl!="Y"&&(jsonMod.jsonpreferencias._fts || jsonMod.jsonpreferencias._fds || jsonMod.jsonpreferencias._filtrosrapidos)){
								if(jsonMod.jsonpreferencias.memorizaPesquisa=="Y"){
									CB.pesquisar();
								}
							}
						}
					}
					//CB.oCarregando.hide();
				}
			});
	    },
	    getAutoFiltro: function(){
	    	var sAutoFiltro = getUrlParameter("_autofiltro");
	    	if(sAutoFiltro.length==0){
	    		return false;
	    	}else{
	    		return jsonStr2Object(sAutoFiltro); 
	    	}
	    },
		go: function(inParGet){

			CB.setWindowHistory("_modulo="+CB.modulo+"&_acao=u&"+inParGet);

			//Troca os dados no formulario
			CB.loadUrl({urldestino: CB.urlDestino+"?_acao=u&"+inParGet});
		},

		fecharForm: function(){
			
			//Ignora casos de formulário direto
			if(CB.jsonModulo.ready=="URL"){
				$("body").removeClass("minimizado");
				return false;
			}else{
				
				//Remove os estilos
				$("body").removeClass("autoloadurl novo readonly");
	
				//Reseta o formulario
				CB.oModuloForm.html("").addClass("hidden");
	
				CB.acao = "u";
	
				//Desabilita o botão de compartilhar
				CB.oBtCompartilharItem.addClass("hidden");
	
				//Desabilita o botão de salvar
				CB.oBtSalvar.addClass("disabled");
	
				//Habilita o botao de novo
				if(CB.jsonModulo.btnovo=="Y") CB.oBtNovo.removeClass("disabled");

				//Remove botões do usuário
				CB.removeBotoesUsuario();

				//Mostra a pesquisa
				$([CB.oModuloResultados, CB.oModuloPesquisa]).each(function(){
					$(this).removeClass("hidden");
				});
				
				//Trocar a url do browser SEM executar a navegação. Isto permite que o F5 seja executado com sucesso
				window.history.pushState(null, window.document.title, "?_modulo="+CB.modulo);
			}
		},

		novo: function(){
			
			CB.acao="i";
		
			var strLocationSearch = "_modulo="+CB.modulo+"&_acao=i";
			
			CB.setWindowHistory(strLocationSearch);

			//Troca os dados no formulario
			CB.loadUrl({urldestino: CB.urlDestino+"?"+strLocationSearch});

			//Altera o estilo do formulario
			//CB.oModuloForm.addClass("novo");
		},

		rep: function(inRelatorios){
			
			oRelatorios=inRelatorios||CB.jsonModulo.relatorios;
			
			var hRep=`<div class="col-md-4">
				<div class="panel panel-default" id="rep_%idrep%">
					<div class="panel-heading"><i class="fa %cssicone%"></i>&nbsp;%rep%<a class="fa fa-bars fade hoverazul pull-right" title="Editar Relatório" href="?_modulo=_rep&_acao=u&idrep=%idrep%" target="_blank"></a></div>
					<div class="panel-body">
						<div class="cbRepFiltros" id="repFiltros%idrep%">%filtros%</div>
						<div class="cbRepExtrair">
							<button class="btn btn-primary pull-right" onclick="CB.extrairRep(%idrep%)">
								&nbsp;Extrair
							</button>
						</div>
					</div>
				</div>
			</div>`;


			 var nLinhai = '<div class="row">';
                        var nLinhaf = '</div>';
                        var c = 0;
			//Altera o cabeçalho da janela modal
			$("#cbModalTitulo")
						.html("Extrair Relatórios");

			var hReps="";

			$.each(oRelatorios, function(idrep,rep){
                            c = c + 1;
                                
                                if (c == 1){
                                    //alert(c);
                                    var tRep = nLinhai + hRep;
                                }else if ( c == 4 || c == 7 || c == 10 ){
                                     //alert(c);
                                    var tRep = nLinhaf + nLinhai + hRep;
                                    c = 0;
                                }else{
                                    var tRep = hRep;
                                }
				var hFiltros="<table>";

				if(rep.showfilters==="Y"){
					if(tamanho(rep._filtros)==0 || !rep._filtros){
						console.error("JS: carbon.rep(): Report ["+rep.rep+"] com falha na configuração dos Filtros");
						return false;
					}
					$.each(rep._filtros, function(col,f){
						var sCbValue=tamanho(f.json)>0?"cbvalue":"";
						var sCalendario=f.calendario==="Y"?"calendario":"";
						//colGet=col.replace(/%\ %/,"").toLowerCase();//Subtituir espaçõs
						if(f.entre==="Y"){
							hFiltros += `<tr>
			<td>${f.rotulo}:</td>
			<td><label>entre&nbsp;</label></td>
			<td class="input-group">
				<input name="${col}_1" class="${sCalendario}" col="${col}" idrep="%idrep%" ${sCbValue}>
				<label class="input-group-addon">&nbsp;e&nbsp;</label>
				<input name="${col}_2" class="${sCalendario}" col="${col}" idrep="%idrep%" ${sCbValue}></td>
			</tr>`;
						}else{
							hFiltros += `<tr><td>${f.rotulo}</td><td></td>
										<td colspan="3">
										<input name="${col}" class="${sCalendario}" col="${col}" idrep="%idrep%" ${sCbValue}>
										</td></tr>`;
						}
					});
					hFiltros += "</table><hr>";
					tRep = tRep.replace(/%filtros%/g,hFiltros);
				}else{
					tRep = tRep.replace(/%filtros%/g,"");
				}

				var cssicone=rep.cssicone?rep.cssicone:"fa fa-file-text-o";

				hReps += tRep.replace(/%rep%/g,rep.rep)
							.replace(/%idrep%/g,rep.idrep)
							.replace(/%cssicone%/g,cssicone);

			});
			$("#cbModal #cbModalCorpo").html(hReps);
			CB.estilizarCalendarios();
			$.each($("#cbModal #cbModalCorpo").find("[cbvalue]"), function(i,o){
				$o=$(o);
				var vIdRep=$(o).attr("idrep");
				var vCol=$(o).attr("col");
				var jSource=jsonStr2Object(oRelatorios[vIdRep]._filtros[vCol].json);
				jSource=jQuery.map(jSource, function(o,k){
					//Recupera a chave e o valor para o autocomplete no formato {key: value}
					return {"value": Object.keyAt(o, 0), "label":o[Object.keyAt(o, 0)]};
				});
				
				//Recupera a primeira opção para a drop
				if(oRelatorios[vIdRep]._filtros[vCol].psqreq=="Y"){
					$o.val(jSource[0].label).attr("cbvalue",jSource[0].value);
				}

				//Monta o autocomplete
				$o.autocomplete({
					source: jSource
					,delay: 0
					,select: function(){
						//alert(0);
					}
				})
			})
			$("#cbModal").addClass("noventa").modal();	
		},

		extrairRep: function(inIdRep){
			var jRep = CB.jsonModulo.relatorios[inIdRep];

			if(jRep.showfilters!=="Y"){
				janelamodal(jRep.url+"?_modulo="+CB.jsonModulo.modulo+"&_idrep="+jRep.idrep);
			}else{
				$oInputs = $("#rep_"+jRep.idrep).find("input[col]");
				var iv=0;
				var sGet="";
				var sE="";
				$.each($oInputs, function(i,o){
					$o=$(o);
					sVal=$o.cbval()?$o.cbval():$o.val();
					//scol=CB.jsonModulo.relatorios[jRep.idrep].filtros[]
					if(sVal){
						iv++;
						sGet+=sE+$o.attr("name")+"="+sVal;
						sE="&";
					}
				});
				if(iv===0){
					alertAtencao("Nenhum parâmetro informado para o relatório ",jRep.rep);
				}else{
					console.log(sGet);
					janelamodal(jRep.url+"?_modulo="+CB.jsonModulo.modulo+"&_idrep="+jRep.idrep+"&"+sGet);
				}
			}
		},
		/*
		 * Carrega a URL do modulo
		 * urldestino: url a ser carregada
		 * render: função para controle da renderização das informações recuperadas
		 */
		posLoadUrl: null,
		preLoadUrl: null,
		loadUrl: function(opt){
			//Executa evento
			if (CB.preLoadUrl && typeof(CB.preLoadUrl) === "function") {
				CB.preLoadUrl(opt);

			}
			inOpt = opt;

			console.info("todo: validar searchStrings inválidas");
			$.ajax({
				type: 'get',
				cache: false,
				url: inOpt.urldestino,
				data: "_modulo="+CB.modulo,
				beforeSend: function(){
					//Armazena os parâmetros GET (geralmente provenientes de clique em resultados de pesquisa) para serem concatenados posteriormente
				},
				success: function(data, textStatus, jqXHR){
	
					//Minimiza o menu superior
					if(CB.modulo!="_login" && CB.jsonModulo.menufixo=="N") $("body").addClass("minimizado");
					
					//Caso seja chamada direta com _acao e id via GET, nao alterar objetos
					if(this.autoLoadUrl!="Y"){
						console.info("todo: chamada direta com _acao e ID");
					}
	
					//Caso seja chamada de formulário, não mostrar botão de fechar
					if(CB.jsonModulo.ready!="URL"){
						var htmlAdicional = "<i class='fa fa-times cbFecharForm' title='Fechar' onclick='CB.fecharForm()'>";
					}
					//Ajusta elementos conforme a ação atual, ajustada via clique ou por parametro GET
					if(CB.acao=="i" && CB.modulo!="_login"){
						//Estilo diferente para o formulário e body
						$("body").addClass("novo");

						//Habilita o botao de salvar
						CB.oBtSalvar.removeClass("disabled");
						//Desabilita o botao de novo
						CB.oBtNovo.addClass("disabled");
					}else if(CB.modulo=="_login"){
						htmlAdicional = "";
					}else{
						//Habilita o botao de salvar
						if(jqXHR.getResponseHeader("CB-READONLY")=="Y"){
							CB.oBtNovo.addClass("disabled");
							CB.oBtSalvar.addClass("disabled");
						}else{
							CB.oBtSalvar.removeClass("disabled");
						}
						//Habilita o botão de compartilhar
						CB.oBtCompartilharItem.removeClass("hidden");
					}

					/*
					 * Tenta mostrar o resultado da requisição no frame de formularios
					 * Será feita verificação de erros de javascript no carregamento das informações
					 */

					if(inOpt.render && typeof(inOpt.render) === "function"){
						//Verifica se existe alguma função para tratamento das informações. Caso contrário faz a verificação do carregamento dos javascript e atribui ao formulário principal
						inOpt.render(data, textStatus, jqXHR);
					}else{
						try{
						//Carrega normalmente
							CB.oModuloForm.html("").html(data).removeClass("hidden").append(htmlAdicional);
						}catch(e){
							CB.validaScriptOrigemAjax(data, e);
							return false;
						}
					}

					//Esconde a pesquisa
					$([CB.oModuloResultados, CB.oModuloPesquisa]).each(function(){
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
					if(CB.jsonModulo.postonenter=="Y"){
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
						alert('dg');
					});					
					
					CB.preparaEditaveis();
					
					/* / executa funcao de callback devolvendo a resposta
					if (inOpt.callback && typeof(inOpt.callback) === "function") {
						inOpt.callback(data);
					}*/
					
					CB.controlaCollapse();
					
					//Executa evento
					if (CB.posLoadUrl && typeof(CB.posLoadUrl) === "function") {
						CB.posLoadUrl(data);
						
					}
				}
			});
		},
		
	    /*
	     * Ajusta o foco inicial
	     */
	    focoInicial: function(){
			//CB.oModuloForm.find(":input:not(input[type=button],input[type=submit],button,select):visible:first").focus();
		},
		
		/*
		 * Executar Post com 'enter'
		 */
		postOnEnter: function (){
			//campos input 'autocomplete' serao excluidos do enter, devendo-se realizar a chamada cbpost manualmente para cada caso. O curinga eh necessario visto que os autocompletes podem ter mais de uma classe atribuida
			CB.oModuloForm.find(":input:not([type=button],[type=submit],a,button,textarea,[class*=ui-autocomplete-input],[class*=acinsert])").on("keyup",function(e){
				var keyCode = e.keyCode || e.which;
				if(keyCode==13){
					if(!teclaLiberada(e)) return;//Evitar repetição do comando abaixo
					CB.post();
				}
			});
			console.info("todo: postOnEnter: Nao esta sendo executado. Nao atribuir OnEnter a objetos incomuns ao formulário. Ex: plugins jQuery ou Bootstrap");
		},
		
		toggleBotoesControleForm: function(inMostrar){
			if(inMostrar){
				CB.oBtFecharForm.show();
				//gBtNovo.show();
				CB.oBtSalvar.show();
			}else{
				CB.oBtFecharForm.hide();
				//gBtNovo.hide();
				CB.oBtSalvar.hide();
				CB.oNavRegAtual.text("");
				CB.oPainelNavegacao.hide();
			}
			
		},

		estilizarCheckbox: function(inObjPai){
			//inObjPai.find('input[type=checkbox][data-toggle^=toggle]').bootstrapToggle();
		},
		
		estilizarCalendarios: function(){
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
		   }).on("apply.daterangepicker", function(e, picker) {
			   picker.element.val(picker.startDate.format(picker.locale.format));
		   });

		   $(".calendariodatahora").daterangepicker({
			   "autoUpdateInput": false,
			   "singleDatePicker": true,
			   "showDropdowns": true,
			   "linkedCalendars": false,
			   "opens": "left",
			   "locale": CB.jDatetimeRangeLocale
		   }).on("apply.daterangepicker", function(e, picker) {
			   picker.element.val(picker.startDate.format(picker.locale.format));
		   });
                   
                   $( "body" ).delegate( ".btn-group:not(.bootstrap-select) > button", "click", function(e) {
                            $this=$(this);
                            if($this.hasClass("selecionado")){
                                    $this.removeClass("selecionado");
                        }else{
                                    $this.closest(".btn-group").find("button").removeClass("selecionado");
                                    $this.addClass("selecionado");
                            }
			//O evento de clique estava sendo atribuido mais de 1x ao objeto, causando comportamento inverso: selecionado e depois de-selecionando
			//https://developer.mozilla.org/pt-BR/docs/Web/API/Event/stopImmediatePropagation
			e.stopImmediatePropagation();
                    })
                   
		},
		
		preparaEditaveis: function(){
			$(".editavel").on("click",function(){
				$this=$(this);
				$this.attr("contentEditable",true);
				$this.focus();
			}).on("blur",function(){
				$this=$(this);
				$this.attr("contentEditable",false);
			})
		},
		
		controlaCollapse:function(){
			$.each(CB.oModuloForm.find("[data-toggle=collapse]"),function(i,o){
				$o=$(o);
				var shref=$o.attr("href");
				var khref=shref.substr(1);//Remover a # do id para não gerar erro de path no mysql

				//Verifica se o elemento com collapse possui alguma preferência de usuário salva
				if(CB.jsonModulo.jsonpreferencias.collapse && CB.jsonModulo.jsonpreferencias.collapse[khref]){

					$oc=$(shref);
					$oc.removeClass("collapse in");
					col=CB.jsonModulo.jsonpreferencias.collapse[khref];

					if(col=="N"){
						$oc.addClass("collapse in");
					}else{
						$oc.addClass("collapse");
					}
				}
				
				//Executa futuramente (no clique) o armazenamento do estado do collapse, nas preferências do usuário
				$o.on("click",function(){
					$this=$(this);//Objeto atual. Geralmente um panel-heading
					var shref=$this.attr("href");
					var khref=shref.substr(1);//Remover a # do id para não gerar erro de path no mysql
					$body=$(shref);
					if($body.hasClass("collapse") && $body.hasClass("in")){//Está aberto
						//Fechar
						CB.setPrefUsuario('m','{"prodserv":{"collapse":{"'+khref+'":"Y"}}}');
					}else{
						//Abrir
						CB.setPrefUsuario('m','{"prodserv":{"collapse":{"'+khref+'":"N"}}}');
					}
					
				});
			});
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
		post: function(inParam){

			if(CB.ajaxPostAtivo && CB.jsonModulo.ajaxparalelo==="N"){
				console.warn("carbon.post ativo: Ação cancelada. Para permitir salvamentos em paralelo configure o módulo.");
				alertAtencao("Aguarde: ação anterior ainda não concluída")
				return false;
			}

			var respPrepost=null;

			//Executa eventos preliminares para posts do Carbon somente
			if (CB.prePost && typeof(CB.prePost) === "function" && (!inParam || inParam.objetos===undefined)) {
				//Executa o pre-post
				respPrepost = CB.prePost(inParam);
				if(respPrepost===false){
					console.warn("prePost: false");
					return false;
				}else if(typeof(respPrepost)==="object"){
					console.warn("prePost: object");
					inParam=inParam||{};
					Object.assign(inParam, respPrepost);
				}
			}

			if(!CB.oModuloForm.is(':visible') && !inParam.objetos){
				console.warn("post: CB.oModuloForm não está visível. Ação cancelada.");
				return false;
			}

			//armazena objetos input serializados
			vdados="";
			var verificaHeadersCarbon;
			//mensagem de confirmacao (por padrao = confirmado)
			var confirmado=true;

			//Variavel local com os objetos do form para pegar a quantidade de objetos a serem enviados via post, e adicionar objetos novos posteriormente
			var objFormInput=$("");
			if(!inParam || (inParam.parcial!==true)){
				//Separar objetos especiais
				sInputsSelector=":input:not([cbvalue]):not([type=radio]), [type=radio]:checked";
				//Objetos do formulario
				objFormInput = CB.oModuloForm.find(sInputsSelector);
				//Objetos dentro de Modais
				objFormInput = objFormInput.add(CB.oModal.find(sInputsSelector));
				//Objetos Especiais
				objFormInput = objFormInput.add(CB.oModuloForm.find(":input[cbvalue]").map(function(){
					return($("<input name='"+$(this).attr("name")+"' value='"+$(this).cbval()+"'>")[0])
				}));
			}

			//verifica se algum parametro foi enviado
			if(inParam == undefined){
				//Inicializa o objeto de parametros
				inParam={};
				//inicializa variavel com url para arquivo da requisição
				inParam.urlArquivo=undefined;
				
				inParam.customInputs=false;
			}else{
				//Armazena a posica vertical do formulario
				if(inParam.memoriaVertical===true){
					memoriavertical();
				}

				//valida mensagem de confirmacao de post
				if(inParam.confirmacao!=undefined){
					if(inParam.confirmacao===true){
						confirmado=confirm("Deseja prosseguir com a alteração?");
					}else{
						confirmado=confirm(inParam.confirmacao);
					}
					if(confirmado==false){
						return;
					}
				}

				//Quando o parâmetro inParam.objetos for utilizado (post manual), valida entrada como string ou serializa objetos
				if(typeof(inParam.objetos)==="string"){
					//vdados = inParam.objetos;
					//Transformar a string informada em objetos
					$objtmp = inParam.objetos.deserialize();
					objFormInput = objFormInput.add($objtmp);

				}else if(typeof(inParam.objetos)==="object"){
					objFormInput = objFormInput.add(inParam.objetos.obj2input());
				}else{
					alert("post: inParam.objetos deve ser informado como String ou Object");
				}
			}

			//chamada padrao do carbon para substituir <form>s
			if(!objFormInput.validacampos()){
				console.log("post: validacampos==false: return");
				return false;
			}else{
				vdados = objFormInput.serialize();
			}

			//verifica recarregamento de html
			if(inParam.refresh==undefined){
				inParam.refresh="repost";
			}
			
			if(inParam.ajaxType==undefined){
				inParam.ajaxType="post";
			}
		
			if(inParam.urlArquivo==undefined){
				//Apos o clique na tabela de resultados do _modulofiltros, a variavel global ParGet é preenchida com os parametros GET gerados. Isto permite pagina de post customizado.
				if(CB.urlDestino.split("?").length>1){
					alert("Js: Erro: O parâmetro urldestino não pode conter parametros GET ou separador '?'");
				}else{

					//Deve-se concatenar vazio na variavel, para o replace ser feito com sucesso
					var tmpParGet = alteraParametroGet("_modulo",CB.modulo,CB.urlDestino + "?" + CB.locationSearch);

					//Monta a URL a ser chamada via POST
					//inParam.urlArquivo = (gModuloUrlParget!=undefined && gModuloUrlParget.length > 1)? gModuloUrldestino + "?" + gModuloUrlParget : gModuloUrldestino;
					//inParam.urlArquivo = gModuloUrldestino + "?" + tmpParGet;
					inParam.urlArquivo = tmpParGet;
				}
			}
		
			//Parâmetros adicionais
			inParam.urlArquivo = inParam.urlArquivo + "&_refresh="+inParam.refresh;

			//Instanciar eventos definidos pelo usuário
			if (CB.posPost && typeof(CB.posPost) === "function") {
				inParam.posPost = CB.posPost;
			}

			//Post
			if(inParam.urlArquivo==undefined){
				alert("submitajax: O atributo inParam.urlArquivo ou cbmodulourl do objeto gFrameForms está vazio!");
			}else{
		
				//fecha todos os avisos do jgrowl
				//jQuery.jGrowl('close');
				console.info("todo: post: fechar todos os avisos popup")
		
				//captura o elemento que ceontém o foco, para devolver o foco após o processamento do post
				var objFoco = document.activeElement.name;
				
				//Evia os dados via ajax
				console.info("todo: validar searchStrings inválidas");
				jQuery.ajax({
		
					type: inParam.ajaxType, /*get/post*/
					url: inParam.urlArquivo,
					data: vdados,
					beforeSend: function(jqXHR, settings){
						CB.ajaxPostAtivo=true;
						//Executa eventos declarados dentro dos parâmetros de cb.post
						if (inParam && inParam.beforeSend && typeof inParam.beforeSend==="function"){
							inParam.beforeSend(jqXHR, settings);
						}
				
						vdados="";
			            jqXHR.setRequestHeader("X-CB-AJAX", "Y");
		
			            //nao recuperar os dados gerados apos o post
						if(inParam.refresh=="refresh"){
				            jqXHR.setRequestHeader("HTTP_X_CB_REFRESH", "N");
						}
		
					}
				}).done(function(data, textStatus, jqXHR) {//sucesso
					CB.ajaxPostAtivo=false;

					//Armazena certificado JWT
					if(jqXHR.getResponseHeader("jwt")){
						Cookies.set('jwt', jqXHR.getResponseHeader("jwt"), { expires: 7 });
					}

					CB.lastInsertId=null;

					if(!CB.logado && jqXHR.getResponseHeader("X-CB-REDIR")!=null){
						window.location.assign(jqXHR.getResponseHeader("X-CB-REDIR"));
					}else{
						
						//Verifica URL de retorno em casos de insert, e SOMENTE para os casos de post normal. Em casos de post controlado via programação (geralmente em casos de update), a URL para refresh não deve ser alterada
						//Nos casos de Token, não remontar a url
						if(inParam.refresh=="repost" && CB.acao=="i" && jqXHR.getResponseHeader("X-CB-PKID")!=null && jqXHR.getResponseHeader("X-CB-PKID")!="" && CB.gToken!="Y"){
			
							//descartar casos de post ajax que nem devem recarregar a pagina. Ex: [ajax] ao inves do numero da linha [0]
							if(jqXHR.getResponseHeader("X-CB-PKID").length > 0){

								var vUrlNova = alteraParametroGet("_acao","u",window.location.search.split("?")[1]);
								vUrlNova = alteraParametroGet(jqXHR.getResponseHeader("X-CB-PKFLD"),jqXHR.getResponseHeader("X-CB-PKID"),vUrlNova);

								CB.setWindowHistory(vUrlNova);

							}else{
								console.warn("Header Pk com length <= 0");
							}
			
						}
			
						if(inParam.refresh!==false && jqXHR.status==200 && jqXHR.getResponseHeader("X-CB-RESPOSTA")=="1" && jqXHR.getResponseHeader("X-CB-FORMATO")=="html"){
							//Trata novos inserts manuais através de CBPOST. Isto permite que o programador realizae ações ajax paralelas sem enviar todos os campos da página via post/get
							if(jqXHR.getResponseHeader("X-CB-PKID")!=null && jqXHR.getResponseHeader("X-CB-PKID")!="" && CB.gToken!="Y"){
								CB.lastInsertId=jqXHR.getResponseHeader("X-CB-PKID");
							}
							/**************************************************************************************
							 * Esta função NÃO deve executar return, pois se trata de uma requisição ASYNCHRONOUS *
							 **************************************************************************************/		
							//var dataclean = data.replace(/(\r\n|\n|\r)/gm,"");//maf0811: executar limpeza de possiveis quebras de linha
			
							//Ajusta a ação. Este ajuste deve ser feito antes de qualquer comando, pois em caso de repost as variáveis que vêm do servidor já estão como "u"
							CB.acao="u";
						
		
							//verifica se eh repost
							if(inParam.refresh=="repost"){
								CB.oModuloForm.html(data);
							}
			
							//verifica eh refresh (ajax simples)
							if(inParam.refresh=="refresh"){
								//window.location.reload();
								vUrl = CB.urlDestino + window.location.search;
								//vUrl = alteraParametroGet("_modulo",jsonMod.modulo,vUrl);
								CB.loadUrl({
									//urldestino: jsonMod.urldestino +"?_modulo="+jsonMod.modulo
									urldestino: vUrl
								});
							}
			
			
							//verifica eh reload: não executa repost, e não recarrega o carbon inteiro através de refresh
							if(inParam.refresh=="reload"){								
								CB.go(CB.urlDestino+window.location.search);
							}

							//atribui funcionalidade de submit no clique do 'enter'. Isto eh necessario apos o recarregamento.
							if(CB.jsonModulo.postonenter=="Y"){
								CB.postOnEnter();
								console.info("todo: post: corrigir multiplos acionamentos do Enter no postOnEnter ");
							}
			
							//Remove estilos visuais
							$("body").removeClass("novo");
								
							//Mostra a mensagem de sucesso
							alertSalvo();

							// executa funcao de callback devolvendo a resposta
							if (inParam.callback && typeof(inParam.callback) === "function") {
								inParam.callback(jqXHR,data,objFoco);
							}
							
							// executa funcao de callback devolvendo a resposta
							if (inParam.posPost && typeof(inParam.posPost) === "function") {
								inParam.posPost(data, textStatus, jqXHR);
							}

							//executa callback instanciado para a pagina em questao
							if(typeof(cbPostCallback) === "function"){
								cbPostCallback(jqXHR,data,objFoco);
							}

							CB.estilizarCalendarios();
						}else if(inParam.refresh===false && jqXHR.status==200 && jqXHR.getResponseHeader("X-CB-RESPOSTA")=="1"){
							
							//Trata novos inserts manuais através de CBPOST. Isto permite que o programador realizae ações ajax paralelas sem enviar todos os campos da página via post/get
							if(jqXHR.getResponseHeader("X-CB-PKID")!=null && jqXHR.getResponseHeader("X-CB-PKID")!="" && CB.gToken!="Y"){
								CB.lastInsertId=jqXHR.getResponseHeader("X-CB-PKID");
							}
							
							// executa funcao de callback devolvendo a resposta
							if (inParam.posPost && typeof(inParam.posPost) === "function") {
								inParam.posPost(data, textStatus, jqXHR);
							}

							//Mostra mensagem
							alertSalvo(inParam.msgSalvo);
						//Resposta que vai mostrar um alert na tela
						}else if(jqXHR.status==200 && jqXHR.getResponseHeader("X-CB-RESPOSTA")=="0" && jqXHR.getResponseHeader("X-CB-FORMATO")=="alert"){
							
							console.info("todo: post: efetuar log dos erros e mostrar link pro usuario")
							alertAtencao(data);

						}else if(jqXHR.status==200 && jqXHR.getResponseHeader("X-CB-RESPOSTA")=="0" && jqXHR.getResponseHeader("X-CB-FORMATO")=="erro"){
							
							console.info("todo: post: efetuar log dos erros e mostrar link de erro pro usuario")
							alertErro(data);
			
						}else if(jqXHR.status==200 && jqXHR.getResponseHeader("X-CB-FORMATO")=="bool"){

							if(jqXHR.getResponseHeader("X-CB-RESPOSTA")!="1"){
								alert("Erro. Parâmetro X-CB-RESPOSTA no header da resposta (bool) está diferente de 1: ["+jqXHR.getResponseHeader("X-CB-RESPOSTA")+"]");
							}
							
							// executa funcao de callback devolvendo a resposta
							if (inParam.callback && typeof(inParam.callback) === "function") {
								inParam.callback(data);
							}
			
							//executa callback instanciado para a pagina em questao
							if(typeof(cbPostCallback) === "function"){
								cbPostCallback(jqXHR,data,objFoco);
							}

							// executa funcao de callback devolvendo a resposta
							if (inParam.posPost && typeof(inParam.posPost) === "function") {
								inParam.posPost(data, textStatus, jqXHR);
							}
							
							if (inParam.refreshPagina) {
								window.location.reload();
							}
							
						}else if(jqXHR.status==200 && (inParam.ajaxType=="get") ){
							
							// executa funcao de callback devolvendo a resposta
							if (inParam.callback && typeof(inParam.callback) === "function") {
								inParam.callback(jqXHR,data,objFoco);
							}
			
							// executa funcao de callback devolvendo a resposta
							if (inParam.posPost && typeof(inParam.posPost) === "function") {
								inParam.posPost(data, textStatus, jqXHR);
							}
								
							if (inParam.refreshPagina) {
								window.location.reload();
							}
							
						}else if(jqXHR.status==200 && (jqXHR.getResponseHeader("X-CB-RESPOSTA")==null || jqXHR.getResponseHeader("X-CB-FORMATO")==null)){
							alert("functions.js: Servidor de Aplicação não enviou Headers do Carbon. Probabilidades:\n 1 - Não foi realizado o 'include_once CBPOST'\n 2 - Nenhum objeto foi enviado ao CBPOST.\n[Response Headers]:\n\n"+jqXHR.getAllResponseHeaders().toString());
							
						}else{
							alert("functions.js: Headers não previstos. [Response Headers]:\n\n"+jqXHR.getAllResponseHeaders().toString());
						}
					}
					
				}).fail(function(objxmlreq,ajaxOptions, thrownError) {
					CB.ajaxPostAtivo=false;
					/* maf: esta sendo tratado genericamente
					alert('functions.js cbpost():'+objxmlreq.status+"\n\nThrown:"+thrownError);
					*/
				});//$.ajax
			}//if(incaminho==undefined){
		},


		setWindowHistory: function(inStrLocation){
			CB.locationSearch=inStrLocation;
			window.history.pushState(null, window.document.title, "?"+inStrLocation);
		},

		/*
		 * Cria dinamicamente os elementos HTML para filtros conforme configuração de Filtros para o  Módulo
		 * Para cada coluna configurada como JSON cria um filtro rápido, efetuando uma requisição Ajax para recuperar opcoes em formato também json
		 */
		json2Filtros: function (inJsonFiltros){
		
			var objFiltroRapido = "\
<div class='btn-group' role='group' cbCol='$col' cbRot='$rotPesquisa'>\
	<span type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' data-content=''> \
		<span class='txt'>$rotPesquisa</span>\
		<span class='caret'></span>\
		<span class='fa fa-close' title='Limpar filtro' onclick='CB.resetFiltro(\"$col\");fim(event);'></span>\
	</span>\
	<ul class='dropdown-menu'>\
			<li class='resetFiltro'><a href='javascript:CB.resetFiltro(\"$col\")'>Limpar filtro<i class='fa fa-close'></i></a></li>\
	</ul>\
</div>";
			//Loop nos filtros configurados no módulo
			$.each(inJsonFiltros.colunas, function(index) {
				if(this.prompt=="json"){
					var tmpFiltro = objFiltroRapido.replace(/\$rotPesquisa/g, (this.rotcurto||this.col)).replace(/\$col/g,this.col);
					var tmpCol = this.col;
					var tmpRot = (this.rotpsq||this.col);
					CB.oFiltroRapido.append(tmpFiltro).removeClass("hidden");

					//Recupera as opções das drops em modo deferred (.when)
					$.ajax({
				        url: "eventcode/mtotabcol/"+this.tab+"__"+this.col+"__prompt.php",
						data: "_modulo="+CB.modulo,
						type: 'get',
						cache: false,
						//dataType: "json",
						dataType: "text",
						beforeSend: function(req){
			                req.setRequestHeader("Pragma", "cache");
			                req.setRequestHeader("Cache-Control","max-age=67");
			            }
					}).done(function(data){
							
						data=jsonStr2Object(data);

						//Deve-se montar um Array [], e não um objeto {}
						if(!(data instanceof Array)){
							console.log("O arquivo deve retornar Array de Javascript válido. Ex: [{},{}...]");
							alertErro("Consulte o Log de erros.","Filtro Rápido inválido:");
							return false;
						}
						
						$.each(data, function(index, value) {
							var id = Object.keyAt(value, 0);
							var valor = value[id];
							//Adiciona a opção ao Filtro
							var oFiltroRapidoGrp = CB.oFiltroRapido.find("[cbcol="+tmpCol+"]");
							oFiltroRapidoGrp.find(".dropdown-menu").append("<li><a href='javascript:CB.filtrar({col:\""+tmpCol+"\",id:\""+id+"\",valor:\""+valor+"\",rot:\""+tmpRot+"\"})'>"+valor+"</a></li>");
							
							//Verifica nas preferências do usuário se essa coluna foi previamente selecionada, e atribui o valor selecionado à dropdown correspondente
							if(CB.jsonModulo.jsonpreferencias._filtrosrapidos[tmpCol] == id && CB.jsonAutoFiltro == false){
								//Simula seleção de opção nas dropdown de filtro rápido
								CB.setFiltroRapido({"col":tmpCol,"id":id,"valor":valor,"rot":tmpRot});
							}
						})
					}).fail(function(objxmlreq,ajaxOptions, thrownError){
						console.log("Erro inesperado");
						return false;
					});
				}
			});//$.each
			console.info("todo: json2Filtros:  testar casos de telas sem filtro rapido");
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
		setFiltroRapido: function(inOpt){
			
			inOpt.valor=unescape(inOpt.valor)||inOpt.id;
			inOpt.rot=unescape(inOpt.rot)||"";
			
			var objFiltroGrp = CB.oFiltroRapido.find("[cbcol="+inOpt.col+"]");
			var objFiltro = objFiltroGrp.find(".btn[data-content]");

			//Armazena o valor selecionado para ser utilizado na pesquisa. Isto deve ser feito em separado da apresentação na tela, visto que a pesquisa é Assíncrona à recuperação de itens das dropdown de filtros
			//CB.jsonModulo.jsonpreferencias._filtrosrapidos[inOpt.col]=inOpt.id;
			
			//Atribui o valor selecionado ao item de filtro
			objFiltroGrp.attr("cbId",inOpt.id);
			
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
						title: 'Filtrando por <strong>'+inOpt.rot+'</strong>:', 
						content: inOpt.valor + '<span class="fa fa-close vermelho pull-right pointer" style="line-height: inherit;" title="Retirar filtro" onclick="CB.resetFiltro(\''+inOpt.col+'\');fim(event);"></span>'
					});
		},

		resetFiltro: function(inCol){
			var objFiltroGrp = CB.oFiltroRapido.find("[cbcol="+inCol+"]");
			var objFiltro = objFiltroGrp.find(".btn");

			//Limpa o valor do json de filtros rápidos do Módulo
			CB.jsonModulo.jsonpreferencias._filtrosrapidos[inCol]="";
			
			//Atribui o valor (vazio) selecionado ao item de filtro
			objFiltroGrp.attr("cbId","");
			
			//Recupera o rótulo de pesquisa
			var strRot = objFiltroGrp.attr("cbRot");
			
			//Remove o valor selecionado e atribui no rótulo de pesquisa
			objFiltro.webuiPopover("destroy")
					.removeClass("filtroAtivo")
						.find(".txt")
						.html(strRot);
			
			//Executa imediatamente a nova pesquisa
			CB.pesquisar({resetVarPesquisa:true});
		},

		/*
		 * Monta parametro GET com os filtros rápidos para pesquisa (todos: selecionados ou não)
		 */
		NAOFUNCIONAgetFiltrosRapidos: function(){
			if(CB.jsonModulo.jsonpreferencias._filtrosrapidos===undefined){
				console.warn("getFiltrosRapidos: Nenhum filtro encontrado em CB.jsonModulo.jsonpreferencias._filtrosrapidos");
				return "{}";
			}else{
				var dataFiltros={};
				$.each(CB.jsonModulo.jsonpreferencias._filtrosrapidos, function(col, val) {
					dataFiltros[col] = val;
				});
				return JSON.stringify(dataFiltros);
			}
		},
		getFiltrosRapidos: function(){
			var dataFiltros={};
			$.each(CB.oFiltroRapido.find("[cbCol]"), function(index) {
				dataFiltros[$(this).attr("cbCol")] = $(this).attr("cbId")||"";
			});
			return JSON.stringify(dataFiltros);
		},
		/*
		 * Filtrar a pesquisa
		 */
		filtrar: function(inOpt){
			CB.setFiltroRapido(inOpt);
			//Executa imediatamente a nova pesquisa
			CB.pesquisar({resetVarPesquisa:true});
		},

		/*
		 * Executar pesquisa
		 */
		pesquisar: function(oConf){
			oConf=oConf||{};
			oConf.resetVarPesquisa = oConf.resetVarPesquisa||false;

			/*
			 * Indicar que será executada uma nova pesquisa, limpando parâmetros de paginação atuais
			 * Geralmente utilizado para "nova pesquisa" (Ex: clique no botão de pesquisa)
			 * O reset das variáveis não ocorre em caso de pesquisas paginadas
			 */
			if(oConf.resetVarPesquisa===true){
				CB.limparResultados=true;
				CB.resetVarPesquisa();
			}

			//Verifica se já existe alguma requisição de pesquisa sendo executada
			if(CB.pesquisaAjaxAtivo){
				console.log("CB.pesquisar: Aguardando requisição término ajax anterior")
			}else{
				
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
					var vGet = "_modulo="+CB.modulo;
	
					//Controle de paginação
					vGet = vGet+"&_pagina="+CB.pesquisaAjaxPagina;
	
					//Texto digitado pelo usuario
					var strTextoPesquisa = CB.oTextoPesquisa.val().trim();
					vGet = (strTextoPesquisa.length>0) ? vGet+"&_fts="+strTextoPesquisa : vGet;
						
					//Data informada pelo usuario
					vGet = (CB.oDaterange.attr("cbdata").length>0) ? vGet+"&_fds="+CB.oDaterange.attr("cbdata") : vGet;
	
					//Filtros rápidos informados pelo usuário
					console.info("todo: pesquisar: verificar se o modulo possui informacao de filtros rapidos, para evitar erros ou valores vazios no GET");
					vGet = vGet+"&_filtrosrapidos="+CB.getFiltrosRapidos();
						
					//Enviar para o servidor ordenação para pesquisa
					if(CB.jsonModulo.jsonpreferencias.orderBy!=undefined && CB.jsonModulo.jsonpreferencias.orderBy._ordcol){
						vGet = vGet+"&_ordcol="+CB.jsonModulo.jsonpreferencias.orderBy._ordcol;
						vGet = vGet+"&_orddir="+CB.jsonModulo.jsonpreferencias.orderBy._orddir;
					}else{
						//Verifica se foi enviada ordenação pela URL, junto à algum _autofiltro
						v_ordcol=getUrlParameter("_ordcol");
						v_orddir=getUrlParameter("_orddir");
						if(v_ordcol!==undefined && v_orddir!==undefined && v_ordcol!=="" && v_orddir!==""){
							vGet = vGet+"&_ordcol="+v_ordcol;
							vGet = vGet+"&_orddir="+v_orddir;
						}
					}
	
					//Efetua a requisição
					$.ajax({
						type: 'get',
						cache: false,
						url: 'form/_modulofiltrospesquisa.php',
						data: vGet,
						dataType: "json",
						beforeSend: function(){
							//Controla fila de requisições ajax. Caso exista erro de lógica, múltiplas requisições ajax serão feitas em paralelo
							CB.pesquisaAjaxAtivo=true;
						},
						success: function(data){
							CB.pesquisaAjaxAtivo=false;
							//Json contem resultados encontrados?
							if(!$.isEmptyObject(data)){
							
								var tblRes = CB.montaTableResultados(data);
	
								if(CB.limparResultados==true){
									CB.resetDadosPesquisa();
								}
								
								if(CB.pesquisaAjaxPagina==1){
									CB.oResultadosInfo.attr("numrows",data.numrows).html(data.numrows+" resultados encontrados</div>");
									CB.pesquisaAjaxTotalPaginas = data.numpaginas;
								}
								CB.oModuloResultados
									.append(CB.fAcoesResultados.html)
									.append(CB.oResultadosInfo)
									.append("<hr>")
									.append(tblRes);
	
								/*
								 * Monta Legenda: deve ser montada somente após a tabela de resultados ter sido finalizada.
								 * @todo: melhorar a técnica, pois atualmente o recurso flexbox para ordenação de elementos não é suportado pelo windows 9, que ainda é utilizado
								 */
								//CB.oModuloResultados.find("ul.legenda").remove();
								if(data.legenda!=undefined){
									CB.montaLegenda(data.legenda);
								}

								/*if(CB.jsonModulo.btimprimir=="Y"){
									CB.fAcoesResultados.find("#cbOpImprimir").removeClass("hidden");
								}*/

								/*
								 * Possibilita ordenação das linhas de resultado para interagir com a coluna padrão 'ord'
								 */
								if(CB.jsonModulo.ordenavel==="Y"){
									$("#restbl tbody").sortable({
										update: function(event, objUi){
											CB.ordenaPesquisaCarbon();
											//CB.pesquisar({resetVarPesquisa:true});
											CB.ordenar('ord','asc');
										}
									});
								}

								CB.oModuloResultados.removeClass("hidden");
							}else{
								if($.isPlainObject(data)){
									//Um objeto json vazio retornou
									alertAtencao("Nenhum resultado encontrado!");
								}else{
									alertErro(data);
								}
							}
						},
						complete: function(){
							CB.aguarde(false);
							if(CB.limparResultados==true){
								CB.resetDadosPesquisa();
							}
						}
					});
				}//if(CB.jsonModulo.psqfull=="N" &&
			}//if(CB.pesquisaAjaxAtivo){
		},
		ordenaPesquisaCarbon: function(){
			var oR=$("#restbl");				//Resultados da pesquisa
			var tdPk=oR.attr("tdpk");			//tag filho contendo o valor para update
			var tab=CB.jsonModulo.tabpesquisa;	//Tabela a ser atualizada via post
			var pk=CB.jsonModulo.pk;			//coluna pk para post

			var objetosCarbon={};
			var il=0;
			//Loop nos tds que contém o valor da PK
			$.each(oR.find("tbody tr td:nth-child("+tdPk+")"),function(i,o){
				var $td=$(o);
				var vpk=$td.html();
				//Monta o update com a nova ordenacao
				objetosCarbon["_ord"+il+"_u_"+tab+"_"+pk]=vpk;
				objetosCarbon["_ord"+il+"_u_"+tab+"_ord"]=il;

				il++;
			});

			//Salva
			CB.post({
				objetos:objetosCarbon
				,parcial: true
				,refresh: false
				,msgSalvo:"Ordenação concluída"
			});
		},
		/*
		 * Tranforma o json gerado pelo carbon em table para ser apresentado na tela
		 */
		json2tr: function(inJson){
			
			var tblBody="";
			var tblHdr=""
			var tRows=""
			var tCols="";
			var bgColor="";
			var strNav="";
			var strParget="";
			var strEcom="";
			var idNav=0;
			
			//Header
			var iH=0;
			iColPk=null;
			
			$.each(inJson.cols, function(col, rotcurto) {
				var strColAsc,strColDesc="";
				
				//Verifica se a coluna enviada é a PK, e relacionar esta coluna com o TD de acordo com o id do array [cols] que existe dentro dos [rows] que serão montados abaixo
				//Desta maneira é possível marcar o TD como PK sem consumir recursos excessivos de comparação nos loops de row >> cols
				if(col===CB.jsonModulo.pk){
					iColPk=(iH+1)+"";//Incrementar (para utilização por jquery) e converter para string para permitir comparações de null ou undefined
				}
				
				//Verifica se a coluna em questão foi ordenada, para ajustar o ícone
				if(CB.jsonModulo.jsonpreferencias.orderBy!=undefined && CB.jsonModulo.jsonpreferencias.orderBy._ordcol!=undefined && CB.jsonModulo.jsonpreferencias.orderBy._ordcol==col){
					strColAsc=(CB.jsonModulo.jsonpreferencias.orderBy._orddir=="asc")?'ativo':'';
					strColDesc=(CB.jsonModulo.jsonpreferencias.orderBy._orddir=="desc")?'ativo':'';
				}
				
				var sOrdCres="";
				var sOrdDecr="";
				
				
				//Texto para title conforme tipo da coluna
				try{//Evitar erros quando chamada for via popup
					console.info("todo: pesquisar: tratar comportamento de chamadas via popup")
					if(CB.jsonModulo.colunas[col]!=undefined){
						if(CB.jsonModulo.colunas[col].datatype=="date" || CB.jsonModulo.colunas[col].datatype=="datetime"){
							sOrdCres="Mais antigos primeiro";
							sOrdDecr="Mais recentes primeiro";
						}else{
							sOrdCres="Ordenar Crescente";
							sOrdDecr="Ordenar Decrescente";
						}
					}
				}catch(e){
					sOrdCres="Ordenar Crescente";
					sOrdDecr="Ordenar Decrescente";
				}
				
				//Icones de configuracao da coluna
				strColConf = "<i id='cbOrdCres' class='fa fa-arrow-down "+strColAsc+"' title='"+sOrdCres+"' onclick=\"CB.ordenar('"+col+"','asc')\"/>" +
								"<i id='cbOrdDecr' class='fa fa-arrow-up "+strColDesc+"' title='"+sOrdDecr+"' onclick=\"CB.ordenar('"+col+"','desc')\"/>";
			
				tCols += "<td col='"+col+"' class='"+strColAsc+strColDesc+"'>" + rotcurto + strColConf +"</td>";
				iH++;
			});
			tblHdr = "<tr>"+tCols+"</tr>";
			
			if(inJson.numrows==0){
				console.warn("json2tr: Erro: modulofiltrospesquisa retornou parametros da tabela sem nenhum resultado na pesquisa");
			}
			
			//Body Rows
			$.each(inJson.rows, function(i, row) {
				tCols="";
				bgColor="";
				strNav="";
				idNav++;
				
				//Loop nas colunas pela ordem em que vieram
				$.each(row.cols, function(i, col) {
					tCols += "<td>"	+col+"</td>";
				});

				//Altera a cor do background conforme highlights do carbon
				bgColor = (row.bgcolor)?" style='background-color:"+row.bgcolor+"'":"";
				
				//Atribui os parametros GET para o clique na linha
				strParget="";
				strEcom="";
				
				if(!row.parget){
					strParget = " onclick='alert(\"PARGET desconfigurado\")'";
					strNav = "";
				}else{
					$.each(row.parget, function(par, val) {
						strParget += strEcom + par + "=" + val;
						strEcom="&";
					});

					strParget = " goParam='"+strParget+"'";

					strNav = " nav=\""+row.nav+"\" id=\""+idNav+"\"";
				}

				//Finaliza a montagem do TR
				tblBody += "<tr"+bgColor + strParget + strNav +">"+tCols+"</tr>";
			});
			
			//Devolve o html da tabela
			return {
				"iTdPk":iColPk,
				"tblHdr" : tblHdr,
				"tblBody": tblBody 
			}
		
		},
		
		montaTableResultados: function(inData, inOnClick){
			
			var objTr = CB.json2tr(inData);
			var iTdpk = objTr.iTdPk||"";
			var tblRes = $("<table id='restbl' tdpk='"+iTdpk+"'><thead></thead><tbody></tbody></table>");
			
			tblRes.addClass("table table-hover table-striped table-condensed");

			//Atribui o header à table de resultados	
			try {
				if(objTr.tblHdr){
					tblRes.find("thead").html(objTr.tblHdr);
				}
			} catch (e) {
				alert("json2tr: Tags html incorretas para o THEAD. Inspecione Console de Erros");
				console.error(objTr.tblHdr);
			}
			
			//Atribui o body à table de resultados
			try {
				tblRes.find("tbody").html(objTr.tblBody);
			} catch (e) {
				alert("json2tr: Tags html incorretas para o TBODY. Inspecione Console de Erros");
				console.error(objTr.tblBody);
			}
		
			//Verifica se vai executar ação de clique padrão ou informada pelo programador
			if(typeof inOnClick==="function"){
				tblRes.on('click', 'tbody tr', function(event) {
					inOnClick(this, event);
				});
			}else{
				tblRes.on('click', 'tbody tr', function(event) {
					var tmptr = $(this);
					
					//Ativa o TR
					tmptr.addClass('ativo').siblings().removeClass('ativo');
					
					//Verifica se vai abrir numa nova janela ou carregar via ajax
					if(CB.jsonModulo.novajanela=="L"){//abre um link
						CB.acao="u";
						janelamodal(CB.urlDestino + "?_acao="+CB.acao+"&" + tmptr.attr("goParam"));						
					}else if(CB.jsonModulo.novajanela=="M"){//abre o modulo
						CB.acao="u";						
						janelamodal("?_modulo="+CB.modulo+"&_acao="+CB.acao+"&" + tmptr.attr("goParam"));
					}else{
						CB.acao="u";
						//Carrega o formulario associado
						if(tmptr.attr("goParam")){
							CB.go(tmptr.attr("goParam"));
						}else{
							console.warn("Atributo goParam() não configurado no TR:");
							console.warn(tmptr[0]);
						}
					}
					
					//Atualiza painel de navegacao
					//atualizaPainelNavegacao(tmptr);
					console.info("todo: atualizaPainelNavegacao()");
				});
			}
			
			return tblRes;
		},
		imprimirResultados: function(){
			this.goUrlImpressao = function(){
				var vUrlPrint = CB.jsonModulo.urldestino;
				
				ids="";
				virg="";
				$.each($("#restbl tbody tr"), function(k,v){
					vid = $(v).attr("goparam").split("=")[1];
					if(vid.length>=1){
						ids += virg + $(v).attr("goparam").split("=")[1];
						virg=",";
					}
				})
				janelamodal(vUrlPrint+"?_vids="+ids);
			}

			var iRes = CB.oResultadosInfo.attr("numrows");

			if(parseInt(iRes)>50){
				if(confirm("Deseja realmente imprimir "+iRes+" resultados?")){
					this.goUrlImpressao();
				}
			}else{
				this.goUrlImpressao();
			}
		},
		ordenar: function(inColuna,inDir){
			CB.limparResultados=true;
			CB.resetVarPesquisa();
			CB.jsonModulo.jsonpreferencias.orderBy={};
			CB.jsonModulo.jsonpreferencias.orderBy._ordcol = inColuna;
			CB.jsonModulo.jsonpreferencias.orderBy._orddir = inDir;
			CB.pesquisar();
		},
		aguarde: function(inShowHide){
			if(inShowHide){
				CB.oIconePesquisando.removeClass("hidden");
				CB.oModuloPesquisa.addClass("disabled");
			}else{
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
		validaScriptOrigemAjax: function(inData, errorStack){
			console.info(""+errorStack.stack);
			console.error("Carbon: validaScriptOrigemAjax: Erro de javascript ao recuperar conteúdo dinâmico (formulários via ajax). Verifique os scripts indicados");
			vData=inData;
			//Para cada script encontrado, tenta executar o carregamento e o eval, para reproduzir o erro
			var ifilter=false;
			$.each($(inData).filter("script"), function(i,script){
				ifilter=true;
				var sSrc = $(script).attr("src")||"inline";
				//Simula o erro
				try{
					if(sSrc!="inline"){
						//O script externo deve ser carregado da mesma maneira como faz o jquery. Isto gera uma requisição ajax.
						$._evalUrl(sSrc).responseText;
					}else{
						//O script está escrito no corpo do HTML e deve ser somente validado
						if(vData && script.innerHTML==""){
							alert("Erro: Tag <script> incompleta.\nProvavelmente ocorreu erro no código do lado do Servidor e a Tag de fechamento </script> não foi alcançada.");
							console.log("========== Últimas linhas do script: ==========\n\n"+vData.substr(-2000));
						}else{
							eval($(script).html());
						}
					}
					if(script.innerHTML!=""){
						console.info("Script ["+sSrc+"] não apresentou erro.");
					}
				}catch(e){
					vTexto="O script ["+sSrc+"] apresenta erro:";
					console.warn(vTexto);
					alert(vTexto+":\n\n"+e);
					//console.error(e);
					//console.error(script);
					//evaluate novamente no script para localização do erro
					eval($(script).html());
				}
			})
			if(!ifilter)console.error("O método $.filter não retornou nenhum script: o Html testado provavelmente possui algum erro. Ex: tag <div> sem </div>");
		},
		montaLegenda: function(inDataLegenda){
			
			var oPainelLeg = $("<ul class='cbLegenda'></ul>");
			$.each(inDataLegenda, function(cor, legenda){
				oPainelLeg.append("<li><i style='background-color:"+cor+";'></i>"+legenda+"</li>");
			});
			
			var sToggle = (CB.jsonModulo.jsonpreferencias.legenda!=undefined && CB.jsonModulo.jsonpreferencias.legenda=="N")?"hide":"show";
			CB.oPanelLegenda.find("#cbPanelLegendaBody").collapse(sToggle).html(oPainelLeg);
			CB.oPanelLegenda.removeClass("hidden");
		},
		setPrefUsuario: function(inAcao,inPath,inValue){
			$.ajax({
				type: 'get',
				cache: false,
				url: "inc/php/userPref.php",
				data: "_acao="+inAcao+"&_path="+inPath+"&_valor="+inValue,
				success: function(data){
					console.info("Preferência alterada:"+data);
				}
			});
		},
		setModCustomCss: function(inCss){
			if(inCss){
				$("<style>")
				.html(inCss)
				.appendTo("head");
			}
		},
		novoBotaoUsuario: function(inParam){
			
			var onclick = (inParam.onclick||function(){});
			var onmouseover = (inParam.onmouseover||function(){});
			
			$bt = $("<button \
					id='"+(inParam.id||"")+"' \
					type='button' \
					class='btn btn-xs cbBotaoUsuario "+(inParam.class||"")+"' \
					title='"+(inParam.title||"")+"' \
					onclick='$(this).data().onclick()' \
					onmouseover='$(this).data().onmouseover()'>\
						<i class='"+(inParam.icone||"")+"'></i>"+(inParam.rotulo||"")+"\
					</button>");
			
			$bt.data("onclick", onclick);
			$bt.data("onmouseover", onmouseover)
			
			CB.oModuloHeader.append($($bt));
		},
		removeBotoesUsuario: function(){
			CB.oModuloHeader.find(".cbBotaoUsuario").remove();
		},
		snippet: function(inIdsnippet){
			if(inIdsnippet){
				$.ajax({
					type: 'get',
					cache: false,
					url: "form/_snippet.php",
					data: "idsnippet="+inIdsnippet,
					success: function(data){
						//console.info("Preferência alterada:"+data);
					}
				});
			}
		},
		filtrarElementos: function(inObj){
			
		}
  };
}

var CB = new carbon();

/*
 * Função padrão para verificação de nomeclatura de inputs
 * Trabalha de acordo com a function.php.explodeInputNameCarbon, salvo diferenças de regex
 */
String.prototype.explodeInputNameCarbon = function(){
	var regexp = /_(\w+?)_(\w+?)_(\w+?)_(\w+)/g;
	return regexp.exec(this);
}


/********************************************
 * Funções de apoio para o framework
 ********************************************/
/*
 * Controlar visualização de atividade Ajax
 */
function atividadeAjax(inAcao){
	if(CB.oModalCarregando){
		if(inAcao==true){
			$(".ajaxActivity").show();
			CB.oModalCarregando.show();
		}else{
			$(".ajaxActivity").hide();
			CB.oModalCarregando.hide();
		}
	}
}

/*
 * Mostrar mensagem de carregamento/termino para requisições Ajax
 */
$(document).ajaxStart(
	function(){
		alertAguarde();
		atividadeAjax(true);
	}

).ajaxStop(
	function(event, xhr, options){
		$(".aguarde").remove();//Isto remove o elemento e impede a animação de movimentação para cima
		
    	if(CB.oTextoPesquisa && CB.oTextoPesquisa.is(":visible") && CB.pesquisaAjaxPagina==1){
    		//Força o cursor para o fim do texto do input
    		tmpVal = CB.oTextoPesquisa.val();
    		//CB.oTextoPesquisa.focus().val("").blur().focus().val(tmpVal);
    	}
    	atividadeAjax(false);
		if(typeof CB.oCarregando !=="undefined"){
			CB.oCarregando.hide();
		}
	}
).ajaxError(function(event, request, settings){
	
	atividadeAjax(false);

	var strErro, abrirUrl;
	
	if(request.status==200 && request.getResponseHeader("X-CB-RESPOSTA")=="0" && request.getResponseHeader("X-CB-FORMATO")=="alert"){
		alertAtencao(request.responseText);
		
	}else if(request.status==200 && request.readyState==4){
		alertErro("Formatação de Json inválida! Consultar console de erros.");
		//@todo: incluir validador de json $._evalUrl("inc/js/jsonValidator/ajv.min.js").responseText;
		console.warn("Falha ao recuperar json: "+settings.url);
		console.error(request.responseText);
		
	}else if(request.status==500){
		strErro = "Erro 500: Falha no código na URL requisitada:<br>"+ settings.url+"<br>";
		abrirUrl = "<p class='text-align-right'><a href='"+settings.url+"' target='_blank' class='btn btn-default btn-sm'>Abrir url</a></p>";
		alertErro(strErro + abrirUrl);
		
	}else if(request.status==520){
		console.error(request.responseText);
		alertErro("Consulte o log de Erros!");

	}else if(request.status==401){
		strErro = "Você não está logado!<br>";
		abrirUrl = "<a href='javascript:janelamodal(\"?_modulo=_login\")'>Clique aqui para fazer o login novamente.</a>"
		alertErro(strErro + abrirUrl);
		
	}else if(request.status==404){
		strErro = "Erro 404: Arquivo não encontrado:<br>"+ settings.url+"<br>";
		abrirUrl = "<p class='text-align-right'><a href='"+settings.url+"' target='_blank' class='btn btn-default btn-sm'>Abrir url</a></p>"
		alertErro(strErro + abrirUrl);
		
	}else if(request.statusText=="abort" && settings.backgroundMessage){
		alertAzul(settings.backgroundMessage);
		console.log("Requisição ajax abortada");
	}else{
		strErro = "ajaxError: Resultado da Requisição ["+settings.dataType+"]: "+request.status+" - " + request.statusText;
		console.log(strErro);
		alertErro(request.responseText);
	}

}).on("click", ".nav.nav-tabs a", function() {
      vThis = $(this);
      vNavTabs = $(vThis).parent().parent();
      vConteudos = vNavTabs.siblings("section, div");
 
      //Retira o ACTIVE de todas as tabs, e ajusta somente para o objeto clicado
      vNavTabs.children().removeClass("active").filter("li").has("a[tab="+vThis.attr("tab")+"]").addClass("active")
      //Coloca HIDDEN em todos os conteudos, e mostra somente o relativo a Tab selecionada
      vConteudos.addClass("hidden").filter("#"+vThis.attr("tab")).removeClass("hidden");
});

/*
 * Detecção de scroll vertical da pagina, para recuperar registros de forma paginada, caso o formulário não esteja visível
 */
$(window).bind('scroll', function(ev){

	if(typeof CB.oModuloForm !== "undefined" && CB.oModuloForm.hasClass("hidden")){
		//Altura do viewport
		var clientHeight = document.body.clientHeight;
		//Altura do documento
	    var windowHeight = $(this).outerHeight();
		//Top position
	    var scrollY = $(this).scrollTop();
	
		//Efetua a paginação
	    var pontoMudanca = (clientHeight - windowHeight)/1.5;
	    if( scrollY >= pontoMudanca){
	    	if(!CB.pesquisaAjaxAtivo){
	    		if(CB.pesquisaAjaxPagina < CB.pesquisaAjaxTotalPaginas){
		        	CB.pesquisaAjaxPagina++;
		        	CB.pesquisar();
	    		}else{
	    			console.log("window.scroll: máximo de páginas ["+CB.pesquisaAjaxTotalPaginas+"] atingido. nenhuma ação.");
	    		}
	    	}else{
	    		console.log("window.scroll: ajax ativo. nenhuma ação.");
	    	}
	    }
	}
});

/*
 * Detecção de ESC para fechar formulário
 */
$(document).keydown(function(e) {
	if (e.keyCode == 27) {//Esc
		console.info("$(document).keydown: Verificar em que condições executar o fecharform() para que os resultados vazios não sejam mostrados desnecessariamente");
		//Se nenhum modal ter sido instanciado, ou algum tiver sido instanciado e estiver sendo mostrado na tela
		if($("#cbModal").data('bs.modal')==undefined || ($("#cbModal").data('bs.modal')&&$("#cbModal").data('bs.modal').isShown==false)){
			CB.fecharForm();
			//console.log(moment(new Date)+": "+$("#cbModal").data('bs.modal').isShown);
		}else{
			//console.log(moment(new Date)+": "+$("#cbModal").data('bs.modal').isShown+" <- Fechando Modal");
		}
	}
});

/*
 * Executar cbpost com [ctrl]+[s]
 */
$(document).keydown(function(event) {

    //19 para Mac Command+S
    if (!( String.fromCharCode(event.which).toLowerCase() == 's' && (event.ctrlKey||event.altKey)) && !(event.which == 19)) return true;

    CB.post();

    event.preventDefault();
    return false;
});

/*
 * Novo registro [ctrl]+[+]
 */
$(document).keydown(function(event) {

    if (!((event.altKey||event.ctrlKey) && event.which == 187)) return true;

    CB.novo();

    event.preventDefault();
    return false;
});
