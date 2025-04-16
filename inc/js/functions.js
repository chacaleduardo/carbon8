/*
 * Tratamento generico de erros mostrando o nome da funcao executada
 */

/*
	function signContent(obj)

	obj:{
		// ---------- Obrigatório -------------- //
		path: <String> => Caminho do arquivo onde o programador irá tratar os dados do conteúdo a sua maneira
		content: <Obj> => Conteúdo que será mandado para o arquivo de tratamento
		selector: <String>  => Seletor do botão que será desabilitado no momento da requisição
		// ------------------------------------- //
	}

	content:{
		// ---------- Obrigatório -------------- //
		idpessoa: <String/Int> => Identificador da pessoa que está assinando
		// ------------------------------------- //
		parâmetros: <String/Array/Object> => quaisquer outros parâmetros para serem tratados no arquivo 'path' ou conteúdo que será assinado
	}

	retorno <Boolean>:
		true:
		false: 
*/


function signContent(obj){
	if((typeof obj !== "object") || (obj === undefined)){
		console.warn("Parâmetro da função 'signContent' não é um objeto");
		return false;
	}

	if((obj.path === undefined) || (obj.path === "")){
		console.warn("Atributo 'path' indefinido");
		return false;
	}

	if((typeof obj.content !== "object") || (obj.content === undefined)){
		console.warn("Atributo 'content' não é um Objeto");
		return false;
	}
	
	if((obj.selector === undefined) || (obj.selector === "")){
		console.warn("Atributo 'selector' indefinido");
		return false;
	}

	if((obj.content.idpessoa === undefined) || (obj.content.idpessoa === "")){
		console.warn("Atributo 'idpessoa' do objeto 'content' indefinido ou tipo de dado inválido");
		return false;
	}

	if((typeof obj.content.idpessoa !== "string") && (typeof obj.content.idpessoa !== "number")){
		console.warn("Atributo 'idpessoa' do objeto 'content' possui tipo inválido");
		return false;
	}

	$(obj.selector).attr("disabled", true);

	$.ajax({
		type: "post"
		,url : obj.path
		,data: obj.content
	}).done(function(data, textStatus, jqXHR) {
		var formato = jqXHR.getResponseHeader("x-cb-formato");
		var resposta = jqXHR.getResponseHeader("x-cb-resposta");
		if(formato == "aut"){
			switch(resposta){
				case '-3':
					alert("Consulte as mensagens de erro");
					Cookies.remove(btoa("certificado"));
					$(obj.selector).attr("disabled", false);
					return false;
				case '-2':
					alert("Verifique o Certificado Digital");
					Cookies.remove(btoa("certificado"));
					$(obj.selector).attr("disabled", false);
					return false;
				case '-1': 
					alert("Senha Inválida, Tente Novamente");
					Cookies.remove(btoa("certificado"));
					$("input:password#_senhacert").val("");
					$(obj.selector).attr("disabled", false);
					return false;
				case '0':
					if(typeof CB != "undefined"){
						var $oBmodal = $(`<div class="row">
											<div class="col-md-2"></div>
											<div class="col-md-8">
												<input type="password" id="_senhacert" placeholder="Insira a senha do Certificado"/>
											</div>
											<div class="col-md-2"></div>
										</div>
										<div class="row">
											<div class="col-md-4"></div>
											<div class="col-md-4" style="text-align:center;" id="_enviarcert"></div>
											<div class="col-md-4"></div>
										</div>`);
					}else{
						var $oBmodal = $(`<div class="input-group">
											<div class="col-md-12">
												<input class="form-control" type="password" id="_senhacert" placeholder="Insira a senha do Certificado"/>
											</div>
										</div>
										<br>
										<div class="input-group">
											<div class="col-md-12" style="text-align:center;" id="_enviarcert"></div>
										</div>`);
					}
					
					$oBmodal.find("#_enviarcert").append(
						$(`<button class="btn btn-primary" placeholder="Insira a senha do Certificado">Enviar</button>`).on('click',function(){
							var pass = $("input:password#_senhacert").val();
							if(pass != ""){
								$("input:password#_senhacert").css('border', '');
								var in30Min = 1/48;
								Cookies.set(btoa("certificado"),btoa(pass),{expires: in30Min});
								signContent(obj);
							}else{
								$("input:password#_senhacert").css('border', '1.5px solid red');
							}
						})
					);

					if(typeof CB != "undefined"){
						CB.modal({
							titulo:"Senha Certificado"
							,corpo:[$oBmodal]
							,classe: "trinta"
						});
					}else{
						$("#cbModal").find("#cbModalCorpo").append($oBmodal);
						$("#cbModal").find(".modal-title").html("Senha Certificado");
						$("#cbModal").find(".modal-dialog").css('width','20%');
						$("#cbModal").on('hidden.bs.modal', function () {
							$this = $(this);
							$this.find("#cbModalTitulo").empty();
							$this.find("#cbModalCorpo").empty();
							$this.unbind("hidden.bs.modal");
						});
						$("#cbModal").modal('show');
					}

					$(obj.selector).attr("disabled", false);
					return false;
				case '1':

					$(obj.selector).attr("disabled", false);

					$("#cbModal").modal('hide');

					if(obj.signConfirmed === true || obj.signConfirmed === undefined){
						alertSalvo("Conteúdo Assinado");
					}

					if(obj.hideButtonOnSign === true){
						$(obj.selector).hide();
					}

					if(typeof obj.onSign === "function"){
						obj.onSign(this);
					}
					
					if(typeof CB != "undefined"){
						CB.loadUrl({
							urldestino: CB.urlDestino + window.location.search
						});
					}
					
					// executa funcao de callback devolvendo a resposta
					if (obj.posSing && typeof(obj.posSing) === "function") {
						obj.posSing(obj, data, textStatus, jqXHR);
					}

					return true;
				default: 
					$(obj.selector).attr("disabled", false);
					console.warn("Resposta inesperada, verifique o retorno do cabeçalho");
					return false;
			}
		}
	});

	//$(obj.selector).attr("disabled", false);
}
function excluirloteconstransacao(idtransacao){debugger
	try {
		if($(`[name$=_lote_status]`).val() == "CANCELADO"){
			alertAtencao("Lote cancelado, transação não pode ser excluída!");
			return false;
		}
		data = JSON.parse($(`#excluirLoteconsTransacao${idtransacao}`).val())
		if(data.ids.length > 0){
			let obj = {};
			for(let d in data.ids){
				obj[`_x${d}_u_lotecons_idlotecons`] = data.ids[d];
				obj[`_x${d}_u_lotecons_status`] = "INATIVO";
			}
			// não deletar fracao so inativar o consumo
			let idlotefracao = data.idlotefracao;
			if(idlotefracao != null){
					obj[`_x300_u_lotefracao_idlotefracao`] = idlotefracao;
					obj[`_x300_u_lotefracao_qtdini`] = 0;
					obj[`_x300_u_lotefracao_qtd`] = 0;
				}
			
			CB.post({
				objetos: obj
				,parcial:true
				,posPost: function(){
					$("#cbModalCorpo").html("");
					$('#cbModal').modal('hide');
				}
			});
	
		}
	} catch (error) {
		console.error(error)
	}
}


function _controleImpressaoModulo(inParam){

	if(!inParam.modulo || !inParam.grupo || !inParam.idempresa){
		console.error("[Error]: Parâmetros 'modulo' ou 'grupo' ou 'idempresa' não encontrados");
		return false;
	}

	if(inParam.objetos && typeof inParam.objetos != "object"){
		console.error("[Error]: Parâmetro 'objetos' não é do tipo OBJECT");
		return false;
	}

	const imprimirEtiqueta = (inParam, callback) => {
		$.ajax({
			method: "POST",
			url: "./inc/php/impressao/controleImpressaoModulo.php?__command=imprimirEtiqueta",
			data: inParam
		})
		.done(function(data, textStatus, jqXHR) {
			try{
				let parseData = JSON.parse(data);
				if(parseData["erro"]){
					alertErro(parseData["erro"]);
				}else if(parseData["sucesso"]){
					alertAzul(parseData["sucesso"], "Imprimindo:");
				}else{
					alertAtencao(data);
				}

				if(callback && typeof callback == 'function'){
					callback(data, textStatus, jqXHR);
				}
			}catch(e){
				alertAtencao(data);
			}
		});
	}

	const montarModal = (inParam, data) => {
		try{
			let $oModalContent;
			let parseData = JSON.parse(data);

			if(parseData["erro"]){
				$oModalContent = $(`
					<div class="alert alert-danger" role="alert">
						${parseData["erro"]}
			  		</div>
				`);
			}else{
				if(parseData.length == 1 && parseData[0].contimp == 1 && inParam.impressaoDireta == true){
					let callback;
					if(inParam.posPrint && typeof inParam.posPrint == 'function'){
						callback = inParam.posPrint;
						inParam.posPrint = null;
					}

					let info = inParam;
					info['ip'] = parseData[0].impressoras[0].ip;
					info["idetiqueta"] = parseData[0].idetiqueta;
					info["nomeetiqueta"] = parseData[0].nomeetiqueta;
					info["linguagem"] = parseData[0].linguagem;
					info["quantidade"] = 1;
					imprimirEtiqueta(info, callback);
					
				}else{
					let etiquetasRadio = "";
					let etiquetaImagem = "";
					let impressoras = "";
					let options = "";
					let rotuloImpressora;
					let checked = "checked";
					let display = "";
	
					// Layout do Modal
					for(let etiqueta of parseData){
	
						etiqueta.imagem = (etiqueta.imagem) ?
							`<img src="${etiqueta.imagem}" alt="Etiqueta">`
							: `<div style="border: 1px solid;margin-right: 100px;padding: 50px;">Etiqueta sem pré-visualização</div>`;
	
						etiquetaImagem += `
							<div class="form-check etiquetaImagem" style="padding: 10px 0px;${display}" etiqueta="etiquetaRadio_${etiqueta.idetiqueta}">
								${etiqueta.imagem}
							</div>`;
	
						etiquetasRadio += `
							<div class="form-check" style="padding: 10px 0px;">
								<input class="form-check-input pointer" type="radio" name="etiquetaRadio" id="etiquetaRadio_${etiqueta.idetiqueta}" value="${etiqueta.idetiqueta}" ${checked}>
								<label class="form-check-label pointer" for="etiquetaRadio_${etiqueta.idetiqueta}">
									${etiqueta.rotuloetiqueta}
								</label>
							</div>`;
	
					
	
						if(etiqueta.impressoras.length > 0){
							for(let impressora of etiqueta.impressoras){
								rotuloImpressora = `TAG ${impressora.tag} - ${impressora.descricao} - ${impressora.fabricante}`;
	
								options += `
									<option value="${impressora.ip}#${etiqueta.idetiqueta}#${etiqueta.nomeetiqueta}#${etiqueta.linguagem}">${rotuloImpressora}</option>
								`;
							}
						
	
							impressoras += `
								<select style="border: 1px solid #adadad !important;width: 100% !important;${display}" class="etiquetaImpressora" 
								etiqueta="etiquetaRadio_${etiqueta.idetiqueta}">
									${options}
								</select>
							`;
						}else{
							impressoras += `
								<div class="form-check etiquetaImpressora" style="${display}" etiqueta="etiquetaRadio_${etiqueta.idetiqueta}">
									<div class="alert alert-danger" role="alert">
										Não existem impressoras configuradas para essa etiqueta
									</div>
								</div>`;
						}
	
						options = "";
						checked = "";
						display = "display:none;";
					
					}
				
					$oModalContent = $(`
						<div class="col-md-12">
							<div class="col-md-4">
								<div class="row">
									<div class="col-sm-4" style="font-size:inherit;line-height:1.5;">Pré-Visualização:</div>
									<div class="col-sm-12">
										${etiquetaImagem}
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="row">
									<div class="col-sm-4" style="font-size:inherit;line-height:1.5;">Etiquetas:</div>
									<div class="col-sm-12">
										${etiquetasRadio}
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="row">
									<div class="col-sm-12" style="font-size:inherit;line-height:1.5;">Impressoras:</div>
									<div class="col-sm-12">
										${impressoras}
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12" style="text-align: right;padding: 15px;">
							<label>Cópias:</label>
							<input style="width:5%;margin-right: 50px;" type="number" min="1" value="1" id="controleimp_qtd"/>
							<button class="btn btn-primary" id="controleimp_imprimir">Imprimir</button>
							<button class="btn btn-secondary" id="controleimp_cancelar">Cancelar</button>
						</div>
					`);
	
					// Funções de clique nos inputs RADIO e impressora
					$oModalContent.find("[name='etiquetaRadio']").on('click', function(){
						$(".etiquetaImagem").not(`[etiqueta='${this.id}']`).hide();
						$(".etiquetaImpressora").not(`[etiqueta='${this.id}']`).hide();
						$(`[etiqueta='${this.id}']`).show()
					});
	
					$oModalContent.find("#controleimp_cancelar").on('click', function(e){
						$("#cbModal").modal('hide');
					});
	
					$oModalContent.find("#controleimp_imprimir").on('click', inParam,function(e){
						let callback;
						if(e.data.posPrint && typeof e.data.posPrint == 'function'){
							callback = e.data.posPrint;
							e.data.posPrint = null;
						}
	
						let info = $(".etiquetaImpressora:visible").val().split("#");
						if(info.length == 4){
							e.data["ip"] = info[0];
							e.data["idetiqueta"] = info[1];
							e.data["nomeetiqueta"] = info[2];
							e.data["linguagem"] = info[3];
							e.data["quantidade"] = $("#controleimp_qtd").val();
							imprimirEtiqueta(e.data, callback);
						}
					});


				}

				
			}
			if($oModalContent){
				CB.modal({
					titulo: "Etiquetas Disponíveis",
					corpo: [$oModalContent],
					classe: 'noventa',
				});
			}
		}catch( error ){
			console.error(error);
		}
	}

	const buscarConfEtiquetas = (inParam) => {
		$.ajax({
			method: "POST",
			url: "./inc/php/impressao/controleImpressaoModulo.php?__command=confEtiqueta",
			data: {
				modulo: inParam.modulo,
				grupo: inParam.grupo,
				idempresa: inParam.idempresa
			}
		})
		.done(function(data, textStatus, jqXHR) {
			montarModal(inParam, data);
		});
	}

	buscarConfEtiquetas(inParam);
}



function trataEx(e) {
	
    console.log(e);
    var nomeFuncao = trataEx.caller.toString().split(' ')[1].split("(")[0];
    alert("Erro javascript:\nJavascript Function [ "+nomeFuncao+" ]:\n"+e.message);
}

//Alternativa para casos de falta de inclusao do js smallbox
if(!$.smallBox){
	$.smallBox=function(inPar){
		alert(inPar.content);
	}
}

function showToast({title, message, color, timeout, type, sound}) {
	const toast = Toastify({
		duration: timeout,
		node: $(`<div class="toast" style="background: rgba(${color}, .1); color: rgba(${color}, 1)">
			<img src="/form/img/toast-${type}.svg" alt="Icone" />
			<div class="toast-body" style="color: rgba(${color}, .5)">
				<span class="title" style="color: rgba(${color}, 1)">${title}</span>
				<span class="text" style="color: rgba(${color}, 1)">${message}</span>
			</div>
			<i class="fa fa-close toast-close-icon"></i>
		</div>`).get(0),
		style: {
			background: '#FFF',
			borderLeft: `6px solid rgba(${color}, 1)`,
			padding: 0
		},
		gravity: "top", // `top` or `bottom`
		position: "right", // `left`, `center` or `right`
		stopOnFocus: true, // Prevents dismissing of toast on hover
		className: "toast"
	  }).showToast();

	  const toastElement = $(toast.toastElement);

	  toastElement.find('.toast-close-icon').on('click', () => {
		toast.hideToast()
	  });

	  toastElement.find('.toast-copy').on('click', function () {
			const textCopy = toastElement.find('[code-copy]').text();

			if(textCopy)
				navigator.clipboard.writeText(textCopy).then(function() {
					alertSalvo("Texto copiado com sucesso!", 'Copiado');
				}).catch(function(err) {
					alertErro("Copiar","Erro ao copiar");
					console.error("Erro ao copiar:", err);
				});
	  });

	  if(sound) {
		const audio = new Audio(`/sound/sound-${sound}.mp3`); // URL de um som

		audio.oncanplaythrough = () => audio.play();
	  }
}

function alertErro(inMsg,inTitulo,inTimeout){
	inMsg=(inMsg==undefined)?"":""+inMsg;
	inMsg = ""+inMsg;
	vTitulo = (inTitulo!=undefined && inTitulo!="")?inTitulo:"Erro:";

	// $.smallBox({
	// 	title : "<i class='fa-fw fa fa-exclamation-triangle'></i> <strong>"+vTitulo+"</strong><i class='botClose fa fa-times' id='botClose3'></i>",
	// 	content : inMsg.replace(/(?:\r\n|\r|\n)/g, '<br />'),
	// 	color : "#A65858",
	// 	colortime : 100,
	// 	timeout: inTimeout || undefined
	// });
	showToast({
		title: vTitulo, 
		message: inMsg,
		color: '185, 27, 26',
		timeout: inTimeout,
		type: 'danger',
		sound: 'danger'
	});
}

function alertAtencao(inMsg,inTitulo,inTimeout){
	inMsg=(inMsg==undefined)?"":""+inMsg;
	inMsg = ""+inMsg;
	iTimeout = (inTimeout!=undefined && inTimeout>0)?inTimeout:1000;
	vTitulo = (inTitulo!=undefined && inTitulo!="")?inTitulo:"Atenção:";
	showToast({
		title: inTitulo ?? 'Alerta', 
		message: inMsg,
		color: '169, 127, 0',
		timeout: 2000,
		type: 'alert',
		sound: 'alert'
	});
	// $.smallBox({
	// 	title : "<i class='fa-fw fa fa-info-circle'></i> <strong>"+vTitulo+"</strong><i class='botClose fa fa-times' id='botClose3'></i>",
	// 	content : inMsg.replace(/(?:\r\n|\r|\n)/g, '<br />'),
	// 	color : "#EBB400",
	// 	timeout: iTimeout
	// });
}

function alertAzul(inMsg,inTitulo,inTimeout){
	inMsg=(inMsg==undefined)?"":""+inMsg;
	inMsg = ""+inMsg;
	iTimeout = (inTimeout!=undefined && inTimeout>0)?inTimeout:3000;
	// $.smallBox({
	// 	title : "<i class='fa-fw fa fa-info-circle'></i> <strong>"+(inTitulo||"")+"</strong><i class='botClose fa fa-times' id='botClose3'></i>",
	// 	content : inMsg.replace(/(?:\r\n|\r|\n)/g, '<br />'),
	// 	color : "blue",
	// 	timeout: iTimeout
	// });
	showToast({
		title: inTitulo ?? 'Informativo', 
		message: inMsg,
		color: '19, 2, 255',
		timeout: inTimeout,
		type: 'info'
	});
}

function alertSalvo(inMsg, title = 'Salvo'){
	//Não mostrar mensagem
	if(inMsg===false)return true;
	
	inMsg=(inMsg==undefined)?"":""+inMsg;
	inMsg = (inMsg)?inMsg:"Dados atualizados com sucesso!";

	// $.smallBox({
	// 	content: "<i class='fa-fw fa fa-check'></i><strong>"+inMsg+"</strong>",
	// 	color : "#96C965",
	// 	timeout: 4000,
	// 	sound_file: 'salvo'
	// });
	showToast({
		title: title, 
		message: inMsg,
		color: '0, 111, 39',
		timeout: 1000,
		type: 'success',
		sound: 'success'
	});
	//Reduz o tamanho da caixa. Este comando nao pode ser executado via addClass(), porque ao fim do timeout do smallBox o removeClass() eh executado, resetando a classe original
	$("#divSmallBoxes [id^=smallbox]").last()
			.css("width","auto")
			.css("text-align","center")
			.css("max-height","31px")
			.css("padding-right","8px");

}

function alertAguarde(){
	// $.smallBox({
	// 	content: "<i class='fa-fw fa fa-clock-o'></i><strong>Aguarde</strong>",
	// 	color : "silver",
	// 	timeout: 1000,
	// 	sound : false
	// });
	showToast({
		title: 'Aguarde', 
		message: 'Sua solicitação está em processamento. Por favor, aguarde.',
		color: '130, 130, 130',
		timeout: 1000,
		type: 'wait',
		// sound: 'wait'
	});
	//Reduz o tamanho da caixa. Este comando nao pode ser executado via addClass(), porque ao fim do timeout do smallBox o removeClass() eh executado, resetando a classe original
	//$("#divSmallBoxes [id^=smallbox]").last().css("width","7em").css("text-align","center").css("max-height","31px");
	//Recupera o último smallbox criado para atribuir propriedades gerenciáveis
	$ultimobox = $("#smallbox"+SmallBoxes)
		.addClass("aguarde")
		.css("width","7em")
		.css("text-align","center")
		.css("max-height","31px");
}

function alertaLogin(inMsg){
	inMsg=(inMsg==undefined)?"":""+inMsg;
	inMsg=inMsg||"";
	
	// var htmlAlerta = "<div class='alert alert-warning aviso' role='alert'> \
	// 	"+inMsg+" \
	// 	</div>";

	// CB.oModuloForm.before($(htmlAlerta));

	alertAtencao(inMsg)

}

function buscaCamposParavalidar(name,atributo){
	let elementoName = name;
    let elemento = $(`[name='${elementoName}']`);
	let retorno = false
	if (elemento.length > 0) {
		// Iterar sobre os atributos do elemento
		$.each(elemento[0].attributes, function(index, attr) {
				if(atributo.includes(attr.name)){
					retorno = elemento[0]
					return retorno
				}
		});}
		return retorno
}
/*
 * Plugin Jquery para realizar validação dos campos a serem enviados por submit
 */
jQuery.fn.validacampos = function(){
	var retorno = false;

	//loop em cada elemento INPUT, para verificacao de propriedades de validação de cliente no carbon
	jQuery.each(this, function() {
		let elementoName = this.getAttribute('name');

		const atributosArray = [
			'valfa',
			'vcnpj',
			'vcpf',
			'vcpfcnpj',
			'vdata',
			'vdecimal',
			'vnulo',
			'vemail',
			'vmoeda',
			'vnumero',
			'vpwd1',
			'vregex'
		]


		const buscaAtributo = buscaCamposParavalidar(elementoName,atributosArray)

		// Se o Atributo existe no elemento passado
		if (buscaAtributo){
			if (!cbvalidacao(this)){
				retorno = false
				return false
			}
		}else{
			retorno = true;
		}
	});

	if (retorno) {
		return this
	}
};

/*
 * Validar campos de formularios. inObj: coleção de elementos
 */
function cbvalidacao(inObj){
	console.log('cbvalidacao', inObj)
	var vBackColor = "#FFFCB0";
	
	/*
	 * Efetua loop nos objetos HTML para validação ainda no cliente
	 */
    var i;
	
	vobj = inObj;
	//alert(vobj.name + ': ' + vobj.style.display);
	if(inObj.name){
		$vobj = $("[name="+inObj.name+"]");
		if($vobj.is(':radio')) {

			if($vobj.attr("vnulo")!=undefined && $vobj.filter(":checked").length==0 && $vobj.is(":disabled")!=true && $vobj.is(":visible")){
				$vobj.addClass("alertaCbvalidacao");
				$vobj.focus();
				alertAtencao("Este campo de Múltipla Escolha é obrigatório, e precisa ser informado");
				$vobj.one("change", function(){$vobj.removeClass("alertaCbvalidacao");});
				return false;
			}
		}else{
			if($vobj.attr("vnulo") != undefined  && vobj.disabled!=true && $vobj.is(":visible")){// vnulo - o campo não pode ser nulo e estar habilitado, pois campos disabled nao sao enviados via POST
				if ($vobj.val() == "" || $vobj.val() == null){
					$vobj.addClass("alertaCbvalidacao");
					$vobj.focus();
					alertAtencao("Este campo é obrigatório, e precisa ser informado");
					$vobj.one("change", function(){$vobj.removeClass("alertaCbvalidacao");});
					return false;
				}
			}
		}
	}

	if(vobj.attributes["vnumero"] != undefined && vobj.value != "" && vobj.disabled!=true){// vnumero - verifica se o campo é número inteiro
		if (validanumero(vobj.value) == false){
			$vobj.addClass("alertaCbvalidacao");
			vobj.focus();
		    alertAtencao("O valor informado não é um NÚMERO válido: " + vobj.value);
			$vobj.one("change", function(){$vobj.removeClass("alertaCbvalidacao");});
			return false;
		}
	}
	if(vobj.attributes["valfa"] != undefined && vobj.value != "" && vobj.disabled!=true){// valfa - verifica se o campo possui somente caracteres a-z A-Z 0-9 _
		
		if (validaalfa(vobj.value) == false){
			$vobj.addClass("alertaCbvalidacao");
			vobj.focus();
		    alertAtencao("O valor informado possui caracteres inválidos: " + vobj.value);
			$vobj.one("change", function(){$vobj.removeClass("alertaCbvalidacao");});
			return false;

		}
	}

	if(vobj.attributes["vregex"] != undefined && vobj.value != "" && vobj.disabled!=true){// valfa - verifica se o campo possui somente caracteres a-z A-Z 0-9 _
		
		if (validaRegex(vobj.value,vobj.attributes["vregex"]) == false){
			$vobj.addClass("alertaCbvalidacao");
			vobj.focus();
		    alertAtencao("O valor informado possui caracteres inválidos: " + vobj.value);
			$vobj.one("change", function(){$vobj.removeClass("alertaCbvalidacao");});
			return false;

		}
	}
	// vdata - propriedade que verificar se o campo é data
	if((vobj.attributes["vdata"] != undefined && vobj.value != "") && (vobj.value != "")  && vobj.disabled!=true){
		if (validadata(vobj.value) == false){
			$vobj.addClass("alertaCbvalidacao");
			vobj.focus();
		    alertAtencao("O valor informado não é uma DATA válida: " + vobj.value + "   (Formato Válido : 'DD/MM/AAAA')");
			$vobj.one("change", function(){$vobj.removeClass("alertaCbvalidacao");});
			return false;
		}
	}
	// vcnpj - propriedade que verificar se o campo é CNPF válido
	if(vobj.attributes["vcnpj"] != undefined && vobj.value != "" && vobj.disabled!=true){
		if (valida_cnpj(vobj.value) == false){
			$vobj.addClass("alertaCbvalidacao");
			vobj.focus();
		    alertAtencao("O CNPJ informado não é Válido: " + vobj.value);
			$vobj.one("change", function(){$vobj.removeClass("alertaCbvalidacao");});
			return false;
		}
	}
	// vcpf - propriedade que verificar se o campo é CNPF válido

	if(vobj.attributes["vcpf"] != undefined && vobj.value != "" && vobj.disabled!=true){
		if (valida_cpf(vobj.value) == false){
			$vobj.addClass("alertaCbvalidacao");
			vobj.focus();
		    alertAtencao("O CPF informado não é Válido: " + vobj.value);
			$vobj.one("change", function(){$vobj.removeClass("alertaCbvalidacao");});
			return false;
		}
	}
	// vcpfcnpj - propriedade que verificar se o campo é CNPF ou CPF válido conforme a quantidade de caracteres informada
	if(vobj.attributes["vcpfcnpj"] != undefined && vobj.value != "" && vobj.disabled!=true){
		//alert("["+vobj.value+"]:"+vobj.value.length);
		if(vobj.value.length==11){
			if (valida_cpf(vobj.value) == false){
				$vobj.addClass("alertaCbvalidacao");
				vobj.focus();
			    alertAtencao("O CPF informado não é Válido: " + vobj.value);
				$vobj.one("change", function(){$vobj.removeClass("alertaCbvalidacao");});
				return false;
			}
		}else if(vobj.value.length==14){
			if (valida_cnpj(vobj.value) == false){
				$vobj.addClass("alertaCbvalidacao");
				vobj.focus();
			    alertAtencao("O CNPJ informado não é Válido: " + vobj.value);
				$vobj.one("change", function(){$vobj.removeClass("alertaCbvalidacao");});
				return false;
			}	
		}else{
			$vobj.addClass("alertaCbvalidacao");
			vobj.focus();
			alertAtencao("O CPF ou CNPJ informado deve conter 11 ou 14 números respectivamente");
			$vobj.one("change", function(){$vobj.removeClass("alertaCbvalidacao");});
			return false;
		}
	}

	// vemail - propriedade que verificar se o campo é email válido
	if(vobj.attributes["vemail"] != undefined && vobj.value != ""  && vobj.disabled!=true){

		if (validaemail(vobj.value) == false){
			$vobj.addClass("alertaCbvalidacao");
			vobj.focus();
		    alertAtencao("O EMAIL informado não válido: " + vobj.value );
			$vobj.one("change", function(){$vobj.removeClass("alertaCbvalidacao");});
			return false;
		}
	}
	// vmoeda - propriedade para verificar se o campo é moeda
	if(vobj.attributes["vmoeda"] != undefined && vobj.value != ""  && vobj.disabled!=true){
		if (validamoeda(vobj.value) == false){
			$vobj.addClass("alertaCbvalidacao");
			vobj.focus();
		    alertAtencao("Este não é um valor de Moeda Válido - " + vobj.value);
			$vobj.one("change", function(){$vobj.removeClass("alertaCbvalidacao");});
			return false;
		}
	}
		// vmoeda - propriedade para verificar se o campo é moeda
	if(vobj.attributes["vdecimal"] != undefined && vobj.value != ""  && vobj.disabled!=true){
		if (validadecimal(vobj.value) == false){
			$vobj.addClass("alertaCbvalidacao");
			vobj.focus();
		    alertAtencao("Este não é um valor Válido - " + vobj.value);
			$vobj.one("change", function(){$vobj.removeClass("alertaCbvalidacao");});
			return false;
		}
	}
	if(vobj.attributes["vpwd1"] != undefined && vobj.value != "" && vobj.disabled!=true){

		vPwd2 = jQuery(":input[vpwd2]").val();

		if (validaconfirmasenha(vobj.value,vPwd2) == false){
			$vobj.addClass("alertaCbvalidacao");
			vobj.focus();
		    alertAtencao("Os campos SENHA e CONFIRMAÇÃO DE SENHA informados são diferentes! Informe o mesmo valor para os 2 campos.");
			$vobj.one("change", function(){$vobj.removeClass("alertaCbvalidacao");});
			return false;
		}
	}


	//cbpost(cObj);
	return true;

}

// Validar CEP
function validacep(pvalor) {
	var valid9 = "0123456789-";
	var valid8 = "01234568"
	var hyphencount = 0;

if (pvalor.length!=9 && pvalor.length!=8) {
	alert("Entre com os oito ou nove números do cep!");
	return false;
}
for (var i=0; i < pvalor.length; i++) {
	temp = "" + pvalor.substring(i, i+1);
	if (pvalor.length == 9){
    	if (valid9.indexOf(temp) == "-1") {
	     		alert("Você informou caracteres inválidos para o CEP!");
	        	return false;
    	}else{
    		 if (pvalor.charAt(5) != "-") {
    		 	alert("O caractere '-' não está na posição correta ! ");
	        	return false;
    		 }else{
    		 	varposicao1 = pvalor.substring(0,5) + "";
    		 	varposicao2 = pvalor.substring(6,9) + "";
    		 	document.getElementById("cep").value = varposicao1 + varposicao2;
    		 	return true;
    		 }
    	}
	}
	if (pvalor.length == 8){
		if (valid8.indexOf(temp) == "-1") {
	     		alert("Você informou caracteres inválidos para o CEP!");
	        	return false;
    	}else{
    	   return pvalor;
    	}
	}
}

}


// Validar Senha
function validaconfirmasenha(pVal1,pVal2)
{

	if (pVal1 != pVal2){
		return false;
	}else{
		return true;
	}

}
// Validar decimal
function validadecimal(pVal)
{
	//maf 090309: valores decimais estavam sendo tratados com virgula. Alterado par tratar com ponto (.)
	var reTipoVirg = /^[+-]?((\d+|\d{1,3}(\.\d{3})+)(\,\d*)?|\,\d+)$/;
	var reTipoPt = /^[+-]?((\d+|\d{1,3}(\.\d{3})+)(\.\d*)?|\.\d+)$/;
	
	if(reTipoVirg.test(pVal) || reTipoPt.test(pVal)){
		return true;
	}else{
		return false;
	}
	
}
// Validar o CPF
function valida_cpf(cpf)
      {
      var numeros, digitos, soma, i, resultado, digitos_iguais;
      digitos_iguais = 1;
      if (cpf.length < 11)
            return false;
      for (i = 0; i < cpf.length - 1; i++)
            if (cpf.charAt(i) != cpf.charAt(i + 1))
                  {
                  digitos_iguais = 0;
                  break;
                  }
      if (!digitos_iguais)
            {
            numeros = cpf.substring(0,9);
            digitos = cpf.substring(9);
            soma = 0;
            for (i = 10; i > 1; i--)
                  soma += numeros.charAt(10 - i) * i;
            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado != digitos.charAt(0))
                  return false;
            numeros = cpf.substring(0,10);
            soma = 0;
            for (i = 11; i > 1; i--)
                  soma += numeros.charAt(11 - i) * i;
            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado != digitos.charAt(1))
                  return false;
            return true;
            }
      else
            return false;
      }
//Validar o CNPJ
function valida_cnpj(cnpj)
      {
      var numeros, digitos, soma, i, resultado, pos, tamanho, digitos_iguais;
      digitos_iguais = 1;
      if (cnpj.length < 14 && cnpj.length < 15)
            return false;
      for (i = 0; i < cnpj.length - 1; i++)
            if (cnpj.charAt(i) != cnpj.charAt(i + 1))
                  {
                  digitos_iguais = 0;
                  break;
                  }
      if (!digitos_iguais)
            {
            tamanho = cnpj.length - 2;
            numeros = cnpj.substring(0,tamanho);
            digitos = cnpj.substring(tamanho);
            soma = 0;
            pos = tamanho - 7;
            for (i = tamanho; i >= 1; i--)
                  {
                  soma += numeros.charAt(tamanho - i) * pos--;
                  if (pos < 2)
                        pos = 9;
                  }
            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado != digitos.charAt(0))
                  return false;
            tamanho = tamanho + 1;
            numeros = cnpj.substring(0,tamanho);
            soma = 0;
            pos = tamanho - 7;
            for (i = tamanho; i >= 1; i--)
                  {
                  soma += numeros.charAt(tamanho - i) * pos--;
                  if (pos < 2)
                        pos = 9;
                  }
            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado != digitos.charAt(1))
                  return false;
            return true;
            }
      else
            return false;
      }
// Validar email
function validaemail(pVal)
{
	var reTipo = /^[\w!#$%&'*+\/=?^`{|}~-]+(\.[\w!#$%&'*+\/=?^`{|}~-]+)*@(([\w-]+\.)+[A-Za-z]{2,6}|\[\d{1,3}(\.\d{1,3}){3}\])$/;
	return reTipo.test(pVal);
}
// Validar moeda
function validamoeda(pVal)
{
	var reTipo =  /^\d{1,3}(\.\d{3})*\.\d{2}$/;
	return reTipo.test(pVal);
}
// Validar data
function validadata(pVal)
{
	/*
	 * maf 121009: alterado pois estava deixando passar digitacoes do tipo 01/02/09
	 var reTipo = /^((0?[1-9]|[12]\d)\/(0?[1-9]|1[0-2])|30\/(0?[13-9]|1[0-2])|31\/(0?[13578]|1[02]))\/(19|20)?\d{2}$/;
	 */
	var reTipo = /^((0[1-9]|[12]\d)\/(0[1-9]|1[0-2])|30\/(0[13-9]|1[0-2])|31\/(0[13578]|1[02]))\/\d{4}$/;

	return reTipo.test(pVal);
}
// Validar número
function validanumero(pVal)
{
	var reTipo = /^\d+$/;
	return reTipo.test(pVal);
}
// Validar Texto
function validaalfa(pVal)
{
	var reTipo = /^\w+$/;
	return reTipo.test(pVal);
}

function validaRegex(pVal, pRegex){
	var reg = new RegExp(pRegex);
	
	return reg.test(pVal);
  }

/*
 * Validar Strings Json 
 */
function jsonStr2Object(str) {
    var jsonTmp;
    if(typeof str === "object"){
	return str;
    }

    try {
        jsonTmp = JSON.parse(str);
    } catch (e) {
    	console.error("Js: jsonStr2Object: Json informado inválido");
        return false;
    }
    return jsonTmp;
}

/*
 * Recupera informacoes da query string da URL conforme parametro passado
 * Ex: http:localhost/index.php?acao=novo
 * getUrlParameter("acao") = "novo"
 */
function getUrlParameter(name, url){
	if(!url){
		url = location.search;
	}

   return decodeURIComponent(
                (RegExp(name + '=' + '(.+?)(&|$)').exec(unescape(url))||[,""])[1]
        );
}

function alterarEmpresaModal( arrEmpresas = [] )
{
	let JQmodalAlteraEmpresa = $('#modal-altera-empresa');
	let content = "";

	if(JQmodalAlteraEmpresa.get().length)
		return abrirOuFecharModalAlteraEmpresa();

	for (let empresa of arrEmpresas) {
		content += `<div class='bloco-snippet-action pointer p-2'>
						<div class='text-center' onclick="alterarEmpresa(${empresa.idempresa})">
							<img src='${empresa.iconemodal}' class='mb-3' />
						</div>
					</div>`;
	}

	// Estrutura
	let modal = `<div id="modal-altera-empresa" class="modal-snippet-action modal-open">
					<div class="modal-snippet-action-content">
						<div class="w-100">
							<div class="modal-snippet-action-header flex align-items-center relative w-100 mb-4">
								<span class="mx-auto text-gray-10">Selecione uma opção</span>
								<i class="fa fa-remove text-gray-20 absolute right-0 close-modal pointer"></i>
							</div>
							<div class="modal-snippet-action-body">
								${content}
							</div>
						</div>
					</div>
					<div class="overlay"></div>
				</div>`;

	$('body').prepend(modal);

	$('#modal-altera-empresa').on('click', '.close-modal', abrirOuFecharModalAlteraEmpresa);
}

function abrirOuFecharModalAlteraEmpresa()
{
	let JQmodalAlteraEmpresa = $('#modal-altera-empresa');

	if(JQmodalAlteraEmpresa.hasClass('modal-open'))
		return JQmodalAlteraEmpresa.removeClass('modal-open');
	
	return JQmodalAlteraEmpresa.addClass('modal-open');
}

function montaMenuCarbon(lateral = true, superior = true, snippetAcao = true)
{
	if(getUrlParameter('_modulo') != "alterasenha"){
		let JQmenuSuperior = $("#cbMenuSuperior"),
		JQmenuLateral = $('#sidebar'),
		JQmodalSnippetAcao = $('#modal-snippet'),
		JQcontainer = $('#cbContainer');

		if(!lateral && !superior && !snippetAcao)
		{
			JQcontainer.addClass('pt-0 menu-lateral-hidden');
			return true;
		}

		if(!JQmenuSuperior.get().length) JQcontainer.addClass('pt-0');
		if(!JQmenuLateral.get().length) JQcontainer.addClass('menu-lateral-hidden w-100');

		if(JQmenuSuperior.hasClass('loading') || JQmenuSuperior.hasClass('carregado') || 
			JQmenuLateral.hasClass('loading') || JQmenuLateral.hasClass('carregado') ||
			JQmodalSnippetAcao.hasClass('loading') || JQmodalSnippetAcao.hasClass('carregado')
		)
		{
			return true;
		}

		if(lateral) JQmenuSuperior.addClass('loading'); 
		if(superior) JQmenuLateral.addClass('loading');
		if(snippetAcao) JQmodalSnippetAcao.addClass('loading');

		$.ajax({
			url: "ajax/_montamenu.php",
			type: 'get',
			async: false,
			success: data => {
				let menu = {};

				try
				{
					menu = JSON.parse(data);
				} catch(e)
				{
					alertAtencao(e.message);
					alertErro(data);
					console.log(e.message);
				}
		
				if(superior)
				{
					JQmenuSuperior
						.removeClass('loading')
						.addClass('carregado')
						.html(menu.superior);
				} else {
					JQcontainer.addClass('pt-0');
				}
		
				if(lateral)
				{
					if(menu.lateral)
					{
						JQmenuLateral
							.removeClass('loading')
							.addClass('carregado')
							.html(menu.lateral);
					}
				} else {
					JQcontainer.addClass('menu-lateral-hidden w-100');
				}
				
				if(snippetAcao)
				{
					if(menu.modalSnippetAcao)
					{
						JQmodalSnippetAcao
							.removeClass('loading')
							.addClass('carregado')
							.find('.modal-snippet-action-body')
							.html(menu.modalSnippetAcao);
					}
				} else {
					JQmodalSnippetAcao.addClass('hidden');
				}
		
				if (typeof NV === 'undefined')
				{
					return false;
				}	
		
				// NV.init();
			},
			error: err => {
				console.log(err.message);
			}
		});
	}
}

function eventoListenerFiltrarModulos()
{
	var JQinputBuscaSidebar = $('#input-search'),
		JQmenu = $('#menu'),
		JQbtnInpuSearchClear = $('#btn-input-search-clear'),
		onClickDelay;

	let pesquisaLateralUserPref = CB.jsonModulo.jsonpreferencias.pesquisamenulateral;

	JQbtnInpuSearchClear.on('click', _ => {
		JQinputBuscaSidebar.val('');
		filtrarModulos();
	});

	JQinputBuscaSidebar.on('keyup', _ => {
		clearInterval(onClickDelay);
		onClickDelay = setTimeout(_ => filtrarModulos(), 200);
	});

	JQinputBuscaSidebar.on('blur', _ => {
		if(!JQinputBuscaSidebar.val())
			CB.setPrefUsuario('d', `pesquisamenulateral`);
		else 
			CB.setPrefUsuario('u', `pesquisamenulateral`, encodeURIComponent(JQinputBuscaSidebar.val()));
	})

	if(pesquisaLateralUserPref)
	{
		JQinputBuscaSidebar.val(pesquisaLateralUserPref);
		filtrarModulos();
	}

	function filtrarModulos()
	{
		if(!JQinputBuscaSidebar.val())
		{
			CB.setPrefUsuario('d', `pesquisamenulateral`);
			JQbtnInpuSearchClear.addClass('opacity-0');
			JQmenu.children().find('ul').children().removeClass('hidden');
			return JQmenu.children().removeClass('hidden');
		}

		if(JQbtnInpuSearchClear.hasClass('opacity-0'))
			JQbtnInpuSearchClear.removeClass('opacity-0')

		JQmenu.children().each((key, element) => {
			let JQmoduloPrincipal = $(element),
				JQmoduloFilho = JQmoduloPrincipal.find('ul').children(),
				ocultarMenuPrincipal = true;

			if(!JQmoduloFilho.get().length)
			{
				if((JQmoduloPrincipal.attr('cbmodulo') && JQmoduloPrincipal.attr('cbmodulo').toLowerCase().search(JQinputBuscaSidebar.val().toLowerCase()) === -1) && JQmoduloPrincipal.find('span').text().toLowerCase().search(JQinputBuscaSidebar.val().toLowerCase()) === -1)
					JQmoduloPrincipal.addClass('hidden');
				else
					ocultarMenuPrincipal = false
			} else {
				JQmoduloFilho.each((keyFilho, elementFilho) => {
					let JQelementFilho = $(elementFilho);

					if((JQelementFilho.find('li[cbmodulo]').get().length && JQelementFilho.find('li[cbmodulo]').attr('cbmodulo').toLowerCase().search(JQinputBuscaSidebar.val().toLowerCase()) === -1) && JQelementFilho.find('div').text().toLowerCase().search(JQinputBuscaSidebar.val().toLowerCase()) === -1)	
						JQelementFilho.addClass('hidden');
					else {
						JQelementFilho.removeClass('hidden');
						ocultarMenuPrincipal = false;
					}
				});
			}

			if(ocultarMenuPrincipal)
				JQmoduloPrincipal.addClass('hidden');
			else
				JQmoduloPrincipal.removeClass('hidden');
		});
	}
}

function montaModalEmpresa(element)
{
	let mostrarMenu = "";
	let JQelement = $(element);
	if(JQelement.data('menu') == 'N'){
		mostrarMenu = '&_menu=N';
	}
	let linkModalEmpresa = `?_modulo=${JQelement.data('modulo')}&_acao=i${mostrarMenu}`;

	$('#modal-snippet').find('.modal-empresa').removeClass('visible');


	let cbCarregando = CB.oCarregando.clone().addClass('block');
	let cbCarregandoDiv = $(`<div class="absolute backdrop-blur w-100 h-100 flex align-items-center justify-content-center"></div>`);

	cbCarregandoDiv.append(cbCarregando);

	$('.modal-snippet-action-body').append(cbCarregandoDiv);

	$.ajax({
		url: "ajax/empresa.php",
		data: {
			action: 'buscarEmpresasPorModulos',
			params: JQelement.data('modulo')
		},
		type: 'get',
		success: res => {
			ocultarBlocoModalSnippetAcao();

			let modal = JSON.parse(res);

			let modalEmpresa = `<div class="modal-empresa-header">
									<div class='flex align-items-center relative w-100 mb-4'>
										<span class="mx-auto text-gray-10">Empresa destino</span>
										<i class="fa fa-remove text-gray-100 absolute right-0 close-modal pointer"></i>
									</div>
								</div>
								<div class="modal-empresa-body flex-wrap">`;

			Object.values(modal).forEach((empresa) => {
				modalEmpresa += `<div class='p-2'>
									<a href="${linkModalEmpresa}&_idempresa=${empresa.idempresa}" target='_blank' class="pt-0" onclick="ocultarModalSnippetAcao()">
										<img src="${empresa.iconemodal}" alt="" class="mw-100 p-4">
									</a>
								</div>`;
			})

			modalEmpresa += `</div>`;
			
			$('.modal-empresa').html(modalEmpresa);

			mostrarModalEmpresa();

			cbCarregandoDiv.remove();
		}
	});
}

function abrirEmNovaGuia(element)
{
	ocultarModalSnippetAcao();

	mostrarMenu = '';
	let JQelement = $(element);
	if(JQelement.data('menu') == 'N'){
		mostrarMenu = '&_menu=N';
	}
	let url = `?_modulo=${JQelement.data('modulo')}&_acao=i&_idempresa=${gIdEmpresa}${mostrarMenu}`;

	window.open(url, '_blank').focus();
}

function ocultarBlocoModalSnippetAcao()
{
	let JQblocoSnippet = $('#modal-snippet').find('.modal-snippet-action-content > div:first');

	JQblocoSnippet.addClass('opacity-0');

	setTimeout(_ => JQblocoSnippet.addClass('hidden'), 400);
}

function mostrarBlocoModalSnippetAcao()
{
	let JQblocoSnippet = $('#modal-snippet').find('.modal-snippet-action-content > div:first');

	JQblocoSnippet.removeClass('opacity-0');

	setTimeout(_ => JQblocoSnippet.removeClass('hidden'), 400);
}

function ocultarModalEmpresa()
{
	$('.modal-empresa').removeClass('visible');

	setTimeout(_ => $('.modal-empresa').addClass('hidden'), 400);
}

function mostrarModalEmpresa()
{
	$('.modal-empresa').addClass('visible').addClass('opacity-100');

	setTimeout(_ => $('.modal-empresa').removeClass('hidden'), 400);
}

function mostrarModalSnippetAcao() {
	$('#modal-snippet').addClass('modal-open');
};

function ocultarModalSnippetAcao() {
	$('#modal-snippet').removeClass('modal-open');
};

/*
 * Altera parametros da query string da pagina em questao, para efetuar redirecionamentos
 */
function alteraParametroGet(key, value, url) {

	if(value == null)
        value = '';
    var pattern = new RegExp('\\b('+key+'=).*?(&|$)')
    if(url.search(pattern)>=0){
        return url.replace(pattern,'$1' + value + '$2');
    }

	if(url.length>0){
		if(url.includes("?")){
			return url + '&' + key + '=' + value;
		}else{
			return url + '?' + key + '=' + value;
		}
	}else{
		return url + '' + key + '=' + value;
	}
}

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

//Recuperar chave de objeto json diretamente de acordo com o Ã­ndice. Geralmente utilizado para recuperar o primeiro elemento
Object.keyAt = function(obj, index) {
    var i = 0;
    for (var key in obj) {
        if ((index || 0) === i++) return key;
    }
};
//Descobrir o tamanho de um objeto
Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

  /**
   * Remove all diacritics (acentuação) from the given text.
   * @access private
   * @param {String} text
   * @returns {String}
   */
  function normalizeToBase(text) {
    var rExps = [
      {re: /[\xC0-\xC6]/g, ch: "A"},
      {re: /[\xE0-\xE6]/g, ch: "a"},
      {re: /[\xC8-\xCB]/g, ch: "E"},
      {re: /[\xE8-\xEB]/g, ch: "e"},
      {re: /[\xCC-\xCF]/g, ch: "I"},
      {re: /[\xEC-\xEF]/g, ch: "i"},
      {re: /[\xD2-\xD6]/g, ch: "O"},
      {re: /[\xF2-\xF6]/g, ch: "o"},
      {re: /[\xD9-\xDC]/g, ch: "U"},
      {re: /[\xF9-\xFC]/g, ch: "u"},
      {re: /[\xC7-\xE7]/g, ch: "c"},
      {re: /[\xD1]/g, ch: "N"},
      {re: /[\xF1]/g, ch: "n"}
    ];
    $.each(rExps, function () {
      text = text.replace(this.re, this.ch);
    });
    return text;
  }


/*
 * Comparação de 2 strings em modo Full Text Search, implementando AND (^ .* $ e ?=) no regex
 * 
 * Exemplo do regex: /^(?=.*str1)(?=.*str2).*$/im
 * Explicação:
 *   ^ assert position at start of a line
 *   ?= Positive Lookahead
 *   .* matches any character (except newline)
 *   () Groups
 *   $ assert position at end of a line
 *   i modifier: insensitive. Case insensitive match (ignores case of [a-zA-Z])
 *   m modifier: multi-line. Causes ^ and $ to match the begin/end of each line (not only begin/end of string)
 */
function fullTextCompare(myWords, toMatch, inExcludeSpaces){

	//Caso seja necessário comparação sem espaços e sem pontos. Ex: pesquisa de elementos selects melhorada
	if(inExcludeSpaces){
		myWords = myWords.replace(/ /g,"").replace(/\./g,"");
		toMatch = toMatch.replace(/ /g,"").replace(/\./g,"");
	}

	//Substitui algum eventual caracter especial para montar a string do regex sem erros
	myWords=myWords.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
	
	//Remove acentuação
	myWords = normalizeToBase(myWords);
	toMatch = normalizeToBase(toMatch);
	
	//Transforma a capitalização
	myWords = myWords.toLowerCase();
	toMatch = toMatch.toLowerCase();
	
	//console.log("myWords: "+myWords+"  -  toMatch: "+toMatch);
	
	//Divide as palavras e encapsula dentro de grupo regex
	arrWords = myWords.split(" ");
	arrWords = $.map( arrWords, function( n ) {
		return ["(?=.*"+n+")"];
	});
	//Monta o regex completo
	sRegex = new RegExp("^"+arrWords.join("")+".*$","im");

	return(toMatch.match(sRegex)===null?false:true);
}

/*
 * Estender o método $.fn.val() do Jquery
 * Isto permite que o programador controle plugins e ajuste visualmente o que for necessário,
 * mas informe qual será o valor que será enviado via post
 */
var $oldVal = $.fn.val;
$.fn.extend({
	val: function() {

		var valJq = $oldVal.apply(this, arguments);

		/*
		 * Em modo readonly os inputs são transformados em DIVs com caracterÃ­sticas especÃ­ficas.
		 * Isto facilitará a escrita de código para recuperação de valores.
		 * @todo: tratar casos de options e radios
		 */
		if(this.prop("tagName")==="DIV" && this.attr("cbtagoriginal")!==undefined){
			valJq = this.attr("value");
		}

		return valJq;
	}
});


//Capturar ou setar valores de inputs customizados. Ex: $().cbAutocomplete()
$.fn.cbVal=function(inval) {
	if(inval!==undefined){
		$(this).attr("cbValue", inval);
		if(inval===""){//Limpar o value em caso de 'vazio'
			$(this).val("");
		}
	}else{
		 return $(this).attr("cbValue")||$(this).attr("cbvalue")||"";
	}
};
$.fn.cbval = $.fn.cbVal;

//Impedir propagação de eventos do mouse
function fim(e){

	if (!e){
		e=event;
		console.log("Parâmetro 'event' não informado;\nfim(event);");
	} 
	e.cancelBubble = true;
	if (e.stopImmediatePropagation) e.stopImmediatePropagation();
}

function janelamodal(inurl, inalt, inlarg, intop, inleft) {
    //alert(inurl);top
    //itop = (screen.height - inalt);
    if (!intop) {
        if (inalt) {
            if (inalt < (screen.availHeight - 64)) {
                intop = ((screen.availHeight - 64 - inalt) / 2);
                intop = intop.toFixed(0);
            } else {
                inalt = (screen.availHeight - 64); //Tamanhodisponivel - (barratitulo + barra de tarefas)
                intop = 0;
            }
        } else {
            intop = 5;
            inalt = (screen.availHeight - 5);
        }
    }

    if (!inleft) {
        if (inlarg) {
            if (inlarg < screen.availWidth) {
                inleft = ((screen.availWidth - inlarg) / 2);
                inleft = inleft.toFixed(0);
            } else {
                inlarg = (screen.availWidth - 5);
                inleft = 0;
            }
        } else {
            inleft = 5;
            inlarg = (screen.availWidth - 5);
        }
    }

	window.open(inurl);
}

/*
 * Detecta versão inválida do IE
 * Referência: http://browserhacks.com/
 */
function ieInvalido(){

	var isIElte8_1 = !+'\v1';
	var isIElte8_2 = '\v'=='v';
	var isIElte8_3 = document.all && !document.addEventListener;
	var isIElte8_4 = document.all && document.querySelector && !document.addEventListener;

	if(isIElte8_1 || isIElte8_2 || isIElte8_3 || isIElte8_4){
		return true;
	}else{
		return false;
	}
}

/*
 * Alterar Usuário
 */
function alterarUsuario(){
	var tmpUsr = prompt("Informe o usuário com o qual deseja utilizar o Sistema:");
	if(tmpUsr!=undefined && tmpUsr!=""){
		$.ajax({
		  method: "POST",
		  url: "form/_login.php",
		  data: {usuario: tmpUsr}
		})
		.done(function(data, textStatus, jqXHR) {
			if(CB.logado 
					&& jqXHR.getResponseHeader("X-CB-RESPOSTA") 
					&& jqXHR.getResponseHeader("X-CB-REDIR")!=null){
				alert("Atenção: a alteração do usuário foi realizada com sucesso.\n\nVocê será redirecionado para a tela configurada para o usuário ["+tmpUsr+"]\n\nLembre-se que qualquer outra janela aberta permanecerá inválida até que seja efetuado um Logout do sistema!");
				window.location.assign(jqXHR.getResponseHeader("X-CB-REDIR"));
			}else{
				alert("Erro na alteração de usuário");
			}
		});
	}
}


/*
 * Alterar Empresa
 */
function alterarEmpresa(in_idempresa) {
   
	$.ajax({
		method: "POST",
		url: "form/_login.php",
		data: {idempresa: in_idempresa}
	}).done(function(data, textStatus, jqXHR) {
		try{
			let d = JSON.parse(data);
			if(d["success"]){
				abrirOuFecharModalAlteraEmpresa();
				$("#cbModal").modal('hide');
				let novaAba = window.open('./?_idempresa='+in_idempresa, '_blank');
				novaAba.sessionStorage.setItem('_idempresa', in_idempresa);
			}else{
				alertErro("Você não possui permissão para alterar para essa empresa");
				console.error(d["infos"]);
			}
		}catch(e){
			alertErro("Não foi possível alterar de empresa");
			console.error(e);
		}
	});
}

/*
 * De-serializar strings com objetos de formulário
 */
String.prototype.deserialize = function(){
	//filter(Boolean): retira casos em que nao existe o caractere &
	strObj = this;
	$obj = $();
	jQuery.each(strObj.split("&").filter(Boolean), function(i, curObj){

		strName=curObj.split("=")[0];
		strValue = curObj.split("=")[1];

		//Recupera o primeiro objeto do formulario, e verifica se a string de objetos informada esta utilizando o mesmo número de linha para Post
/* @TODO: Validar strings que contém o mesmo número da linha master
 * 		if(strName.split("_")[1]=="1" && $(objFormInput[0]) && $(objFormInput[0]).attr("name").split("_")[1]=="1"){
			strErro = "A linha 1 já está sendo utilizado pelo formulário para Post do Carbon.\nIncremente o valor dos objetos enviados no parÃ¢metro \ncbpost({'objetos':'_1_acao_tabela_coluna'})";
			alertErro(strErro);
			throw new Error(strErro);
		}*/

		//adiciona novo input à colecao de objetos a serem enviados via post
		$obj = $obj.add("<input type='text' "+
			"name='"+strName+"' "+
			"value='"+strValue+"'>");
	});
	return $obj;
};

/*
 * Transformar objetos JS em Inputs
 * Atenção: Declarações de novos métodos para o prototype Object devem vir confinadas em defineproperty para não conflitar com jquery: 
 *	https://stackoverflow.com/a/32169459/1307507
 */
 Object.defineProperty(Object.prototype, 'obj2input',{
	value : function() {
			obj = this;
		  $objinput = $();
		  jQuery.each(obj, function(name, val){
  
			  //adiciona novo input à colecao de objetos a serem enviados via post
			  $objinput = $objinput.add("<input type='text' "+
				  "name='"+name+"' "+
				  "value='"+val+"'>");
		  });
		  return $objinput;
	},
	enumerable : false
});

evalJson = function(inStr){
	try{
		oRet = eval(inStr);
	}catch(e){
		//console.warn("js: evalJson: String inválida: "+strObj+" "+e);
		oRet = null;
	}
	return oRet;
};

/*
 * Para minimizar código em caso de utilização de Object.keys(inObj).length
 * @param {object} inObj
 * @returns {Number|Object.keys.r.length}
 */
function tamanho(inObj) {
	try{
		return Object.keys(inObj).length;
	}catch(e){
		return 0;
	}
}

/*
 * Para minimizar código em caso de utilização de Object.keys(inObj).length
 * @param {object} inObj
 * @returns {Number|Object.keys.r.length}
 */
function primeiro(inObj) {
	try{
		return Object.keys(inObj)[0];
	}catch(e){
		return undefined;
	}
}

/*
 * Ajustar opções default para Bootstrap-select (Selectpicker)
 */

function normalizeToBase(text) {
  var rExps = [
	{re: /[\xC0-\xC6]/g, ch: "A"},
	{re: /[\xE0-\xE6]/g, ch: "a"},
	{re: /[\xC8-\xCB]/g, ch: "E"},
	{re: /[\xE8-\xEB]/g, ch: "e"},
	{re: /[\xCC-\xCF]/g, ch: "I"},
	{re: /[\xEC-\xEF]/g, ch: "i"},
	{re: /[\xD2-\xD6]/g, ch: "O"},
	{re: /[\xF2-\xF6]/g, ch: "o"},
	{re: /[\xD9-\xDC]/g, ch: "U"},
	{re: /[\xF9-\xFC]/g, ch: "u"},
	{re: /[\xC7-\xE7]/g, ch: "c"},
	{re: /[\xD1]/g, ch: "N"},
	{re: /[\xF1]/g, ch: "n"}
  ];
  $.each(rExps, function () {
	text = text.replace(this.re, this.ch);
  });
  return text;
}


(function($) {

	var letterPressed = [];
	var timeOutResetLetters = null;


    $.fn.pesquisaAmpliada = function() {

		search= function(what){

			$drop = that;

			$options = $drop.find("option");

			$.each($options,function(index,opt){
				$opt=$(opt);
				if(fullTextCompare(what, $opt.html(),true)){
					$drop.val($opt.val());
					return false; //found, 'break' the each loop
				}else{
					$drop.val("");
				}
			});
		}	

		that = this;

		that.on("keyup", function (event) {
		  if (event.keyCode === 8 || event.which === 8) { 
		   event.preventDefault(); 
		  } 
		});

		that.keyup(function(e) {
			clearTimeout(timeOutResetLetters);
			timeOutResetLetters = setTimeout(function(){
				letterPressed = [];
			},2500);
			letterPressed.push(String.fromCharCode(e.keyCode));
			search(letterPressed.join(''));
		});
		
        return this;
 
    };
 
}( jQuery ));


function retkey(e) {
    var code;
    if (!e) {
        var e = window.event;
    }
    if (e.keyCode) {
        code = e.keyCode;
    } else if (e.which) {
        code = e.which;
    }
    return code;
}

//Override no método filter para possibilitar comparações full
if($.ui && $.ui.autocomplete){
	$.extend( $.ui.autocomplete, {
		filter: function( array, term ) {
			var termo = term;
			return $.grep( array, function( value ) {
				//Concatenar todos os valores informados pelo programador, separados por espaço, para haver comparação total
				var textoTotalDisponivel="";
				var nbsp="";
				$.each(value, function(i,v){
					if(v){
						textoTotalDisponivel+=nbsp+v;
					}
					nbsp=" ";
				});
				//Compara com o termo digitado pelo usuário
				return fullTextCompare(termo, textoTotalDisponivel);
			});
		}
	});

	//Altera opções default para o widget e adiciona comportamento do carbon para noMatch
	$.widget("ui.autocomplete", $.ui.autocomplete, {
		options: {
			autoFocus: true,
			minLength: 0 /* mostrar a drop caso o usuario apague o texto a ser procurado*/
		},
		_create: function() {

			// GVT - 20/01/2020 - Ordena o array informado durante a criação de autocomplete
			if(this.options.source && (!this.options.order && this.options.order !== false)){
				this.options.source.sort(function(a,b){
					if(a.label > b.label){
						return 1;
					}else{
						if(a.label < b.label){
							return -1;
						}else{
							return 0;
						}
					}
				});
			}

			//this._super("_create");//Funcionava adequadamente. Alterado para _superApply
			this._superApply();

			//Hook para eventos caso necessário
			this._on( this.element, {
				keydown: function( event ) {
					null;
				},
				keypress: function( event ) {
					$el= $(this.element)
					//Executa callback do programador
					if(event.keyCode==$.ui.keyCode.ENTER){
						if(typeof this.options.noMatch=="function" && $el.attr("cbnomatch")==="S"){
							this.options.noMatch(this, event);
							$el.removeClass("noMatch")
						}
					}
				},
				focus: function() {
					null;
				},
				blur: function( event ) {
					null;
				},
				dblclick: function(event,a,b){
					//$el= this.element;
					//Efetuar pesquisa
					this.element.autocomplete("search",this.element.val());
				}
			});

			this.element.bind('autocompleteselect', function(event, ui) {
				//impede o autocomplete de alterar o input
				event.preventDefault();
				//ajusta manualmente o valor do input
				$this = $(this);
				$this.val(ui.item.label);
				$this.cbVal(ui.item.value);
			});

			this.element.bind('autocompletechange', function(event, ui) {
				if(ui.item===null){
					$(this).cbVal("");//Limpa a propriedade do carbon
				}
			});
		},
		__response: function( content ) {
			this._superApply( arguments );
			//verifica se alguma opção válida foi selecionada
			if(content.length==0){
				this._noMatchState(true);
			}else{
				this._noMatchState(false);
			}
		},
		_change: function( event , ui ) {
			this._superApply( arguments );
			//verifica se alguma opção válida foi selecionada
			if($(this.element).cbVal()==="" && $(this.element).val()!=""){
				this._noMatchState(true);
			}if($(this.element).cbVal()==="" && $(this.element).val()==""){
				console.log("teste");
			}else{
				this._noMatchState(false);
			}
		},
		//Método novo para implementar no match
		_noMatchState: function(inNoMatch){
			if(inNoMatch){
				if(typeof this.options.noMatch ==="function"){
					$(this.element).addClass("noMatch").attr("cbnomatch","S");
				}else{
					$(this.element).attr("cbnomatch","S");
				}
			}else{
				$(this.element).removeClass("noMatch").removeAttr("cbnomatch","");
			}
		}
	});
}
	
/*
 * Ajustar as opções default do jquery.ui para minimizar código
 * http://stackoverflow.com/questions/2287045/override-jqueryui-dialog-default-options
 */
if($.ui&&$.ui.sortable&&$.ui.draggable&&$.ui.droppable){
	$.ui.sortable.prototype.options.helper="clone";
	$.ui.sortable.prototype.options.activeClass= "ui-state-default";

	$.ui.draggable.prototype.options.helper="clone";
	$.ui.draggable.prototype.options.revert=true;
	$.ui.droppable.prototype.options.activeClass= "ui-state-default";
	$.ui.droppable.prototype.options.hoverClass="ui-drop-hover";
	$.ui.droppable.prototype.options.tolerance="touch";//[default: intersect: draggable overlaps the droppable at least 50%.] Isto obrigava arrastar objetos grandes até que fosse feito o trespasse da área droppable
}

/*
 * Ajustar as opções default do dropzone para minimizar código
 * Importante: Deve ser colocado somente após o carregamento total da página $(function(){})
 * https://github.com/enyo/dropzone/issues/818
 */

$(function() {
	if(typeof Dropzone == "undefined") return true;

	Dropzone.prototype.defaultOptions.url="form/_arquivo.php";
	Dropzone.prototype.defaultOptions.previewTemplate=$("#cbDropzone").html();
	//Dropzone.prototype.defaultOptions.addRemoveLinks=true;
	Dropzone.prototype.defaultOptions.init=
	function() {
		//Inicia parametros do carbon ao enviar arquivos
		this.on("sending", function(file, xhr, formData){
			formData.append("idobjeto", this.options.idObjeto);
			formData.append("tipoobjeto", this.options.tipoObjeto);
			formData.append("tipoarquivo", this.options.tipoArquivo);
			formData.append("idPessoaLogada", this.options.idPessoaLogada);
			if(this.options.caminho !== undefined){
				formData.append("caminho", this.options.caminho);
			}
			if(this.options.mensagem !== undefined){
				formData.append("mensagem", this.options.mensagem);
			}
		});
		//console.log('this.options.idPessoaLogada');
//console.log(this.options.idPessoaLogada);
		this.on("addedfile", function(file) {

			var removeButton = Dropzone.createElement("<i class='fa fa-trash hoververmelho pull-right' title='Apagar arquivo'></i>");

			var _this = this;

			removeButton.addEventListener("click", function(e) {
				e.preventDefault();
				e.stopPropagation();
				
				if(confirm("Deseja realmente excluir o arquivo?")){  
					// Remove the file preview.
					_this.removeFile(file);
					CB.post({
						objetos:"_9999_d_arquivo_idarquivo="+file.idarquivo
					})
				}
			});

			file.previewElement.setAttribute("title",file.nome);
			
			//Valida se a pessoa que add o arquivo é a mesma que está logada. Somente poderá excluir o arquivo quem add. (Lidiane - 16-03-2020)
			//Retirado para que outras pessoas consigam excluir documento sem ser quem adicionou (06/05/2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=314919)
			//if(file.idpessoa == null || file.idpessoa == this.options.idPessoaLogada){
				// Add the button to the file preview element.
				file.previewElement.appendChild(removeButton);
			//}
			
			file.previewElement.addEventListener("click", function(e) {
				e.preventDefault();
				e.stopPropagation();

				if(_this.options.caminho === undefined){
					if(isMobile === true){
						CB.modal({
							classe: 'oitenta',
							url: "upload/"+file.nome
						});
					}else{
						janelamodal("upload/"+file.nome);
					}
				}else{
					if(isMobile === true){
						CB.modal({
							classe: 'oitenta',
							url: _this.options.caminho+file.nome
						});
					}else{
						janelamodal(_this.options.caminho+file.nome);
					}
				}
			});
			
		});

		this.on('complete', function(file){
			if(this.options.onComplete && typeof(this.options.onComplete) === "function"){
				this.options.onComplete(file);
			}
		})

		thisDropzone = this;

		//Recuperar automaticamente os arquivos do servidor sempre após o dropzone for iniciado em qualquer lugar
		$.ajax({
			url: this.options.url+"?tipoobjeto="+this.options.tipoObjeto+"&idobjeto="+this.options.idObjeto+"&tipoarquivo="+this.options.tipoArquivo,

			// GVT - 24/06/2020 - Adicionado atributo beforeSend para armazenar a instância do Dropzone para ser ultilizado pelo jqXHR no método .done()
			beforeSend: function(inxhr){
				if(thisDropzone){
					inxhr.instanciaDropzone = thisDropzone;
				}
			}
		}).done(function(data, textStatus, jqXHR) {
			jArq = jsonStr2Object(data);
			//Para cada arquivo no servidor, mostrar no plugin
			$.each(jArq, function(i,arq){
				let nomecurto = (arq.nome.length > 25) ? arq.nome.substring(0,28)+"..." : arq.nome
				var mockFile = { 
					name: nomecurto
					,size: arq.tamanhobytes
					,nome: arq.nome
					,criadopor: arq.criadopor
					,datacriacao: arq.datacriacao
					,caminho: arq.caminho
					,idarquivo: arq.idarquivo
					,idpessoa: arq.idpessoa //Acrescentado para validar quem add o arquivo. (Lidiane - 16-03-2020)
				};

				// GVT - 24/06/2020 - Verifica a existência de uma instância de Dropzone atribuída antes da requisição, caso não exista, pega a instância atual
				if(jqXHR.instanciaDropzone){
					jqXHR.instanciaDropzone.emit("addedfile", mockFile).emit("complete", mockFile);
				}else{
					thisDropzone.emit("addedfile", mockFile).emit("complete", mockFile);
				}
			});

			if(jArq.length > 0){
				if(jqXHR.instanciaDropzone){
					$(jqXHR.instanciaDropzone.element).parent().collapse('show');
				}else{
					$(thisDropzone.element).parent().collapse('show');
				}
			}else{
				if(jqXHR.instanciaDropzone){
					$(jqXHR.instanciaDropzone.element).parent().collapse('hide');
				}else{
					$(thisDropzone.element).parent().collapse('hide');
				}
			}
			
		});
	};
});
 /**/

/*
 * Criar novo item de json para os casos de Autocomplete em modo NoMatch que criaram um novo registro no banco de dados
 * O item é criado na opção "source" padrão do autocomplete
 *
$.fn.criaItemJson = function(inObjJson) {
	$this = $(this);
	$this.data('uiAutocomplete').options.source.push(inObjJson);
	//$this.cbval(CB.lastInsertId);
};
*/

/*
 * Controlar repetição de eventos iniciados por keydown
 */
var keyAllowed = {};
function teclaLiberada(e){
	if (keyAllowed [e.which] === false) return;
	keyAllowed [e.which] = false;
	console.log("Keydown: Tecla ["+e.which+"] bloqueada para evitar repetições");
	return true;
}

$(document).keyup(function(e){
	setTimeout(function(){ 
		keyAllowed = {};
		//keyAllowed [e.which] = true;
		if(e.which==13 || e.which==80){
		  console.log("Keyup: Tecla ["+e.which+"] Liberada para nova ação");
		}
	}, 500);
  
});

//Função análoga à functions.php..recuperaExpoente()
function recuperaExpoente(inDouble, inDoubleOriginal){
	if(!inDoubleOriginal|| (inDoubleOriginal.toLowerCase().indexOf("e")===-1 && inDoubleOriginal.toLowerCase().indexOf("d")===-1)){
		//Impedir que esta condição retorne NaN
		if(isNaN(parseFloat(inDouble))){
			return "";
		}else{
			calc = parseFloat(inDouble).toFixed(2);//Arredonda
			return calc;
		}
	}else{
		if(inDoubleOriginal.toLowerCase().indexOf("e")>0){
			$arrExp=inDoubleOriginal.split("e");
			calc = inDouble / Math.pow(10,$arrExp[1]);
			calc = parseFloat(calc.toFixed(2));//Arredonda
			return calc + "e" + $arrExp[1];

		}else if(inDoubleOriginal.toLowerCase().indexOf("d")>0){
			$arrExp=inDoubleOriginal.split("d");
			calc = inDouble / $arrExp[1];
			calc = parseFloat(calc.toFixed(2));//Arredonda
			return calc + "d" + $arrExp[1];
		}else{
			return null;
		}
	}
	null;
}

// GVT - 25/06/2020 - Função assíncrona para verificar a disponibilidade do cpf/cnpj
//                    retorno: 0 = CPF/CNPJ está disponível
//                    retorno: 1 = CPF/CNPJ já existe no banco de dados
//                    retorno: -1 = Entrada inválida - tratar o dado antes da requisição
async function verificacpfcnpj(cpfcnpj){
	if(cpfcnpj != "" && cpfcnpj != "0"){
		cpfcnpj = cpfcnpj.trim();
		const aux = await $.ajax({
			type: 'get',
			url: 'ajax/verificacpfcnpj.php',
			data: {
				cpfcnjpj: cpfcnpj,
			},
		});
		return aux;
	}
}

function dma(inDatetime){
	if(inDatetime && inDatetime!==""){
		return moment(inDatetime,["DD/MM/YYYY","YYYY/MM/DD"]).format("DD/MM/YYYY");
	}else{
		return "";
	}
}

function dmahm(inDatetime){
	if(inDatetime && inDatetime!==""){
		return moment(inDatetime,["DD/MM/YYYY HH:mm","YYYY/MM/DD HH:mm"]).format("DD/MM/YYYY HH:mm");
	}else{
		return "";
	}
}

function dmahms(inDatetime){
	if(inDatetime && inDatetime!==""){
		return moment(inDatetime,["DD/MM/YYYY HH:mm:ss","YYYY/MM/DD HH:mm:ss"]).format("DD/MM/YYYY HH:mm:ss");
	}else{
		return "";
	}
}
function mdahms(inDatetime){
	if(inDatetime && inDatetime!==""){
		return moment(inDatetime,["DD/MM/YYYY HH:mm:ss","YYYY/MM/DD HH:mm:ss"]).format("MM/DD/YYYY HH:mm:ss");
	}else{
		return "";
	}
}
function mda(inDatetime){
	if(inDatetime && inDatetime!==""){
		return moment(inDatetime,["DD/MM/YYYY","YYYY/MM/DD","DD/MM/YYYY HH:mm:ss"]).format("MM/DD/YYYY");
	}else{
		return "";
	}
}

if(typeof moment !== "undefined"){
	moment.updateLocale('pt-br', {
		relativeTime : {
			future : 'em %s',
			past : '%s atrás',
			s : 'seg.',
			ss : '%d seg.',
			m : 'um min.',
			mm : '%d min.',
			h : '1 hora',
			hh : '%d horas',
			d : '1 dia',
			dd : '%d dias',
			M : '1 mês',
			MM : '%d meses',
			y : '1 ano',
			yy : '%d anos'
		},
		dayOfMonthOrdinalParse: /\d{1,2}º/,
		ordinal : '%dº'
	});
}
	
var notificacaoSistema = window.Notification || window.mozNotification || window.webkitNotification;

// A function handler
function notificacao(){
	
	argumentos=arguments[0];
	
    if ('undefined' === typeof notificacaoSistema){
    	console.warn("notificacao: Notificações de sistema não suportadas");
        return false;       //Not supported....
    }

    var noty = new notificacaoSistema(
        argumentos.titulo, {
            body: argumentos.corpo,
            dir: 'auto', // or ltr, rtl
            lang: 'EN', //lang used within the notification.
            tag: 'notificationPopup', //An element ID to get/set the content
            icon: argumentos.icone //The URL of an image to be used as an icon
        }
    );
    noty.onclick = function (event) {
		if(argumentos.callback){
			noty.close();
			window.focus();
			argumentos.callback(argumentos);
		}
    };
    noty.onerror = function () {
        console.log('notification.Error');
    };
    noty.onshow = function () {
        console.log('notification.Show');
    };
    noty.onclose = function () {
        console.log('notification.Close');
    };
    return true;
}

function sobreOSistema(){
	//Altera o cabeçalho do modal
	strCabecalho="<label class='fa fa-question-circle-o azul'></label>&nbsp;&nbsp;Sobre o sistema:";
	$("#cbModalTitulo").html(strCabecalho);

	//Mostra o popup
	$('#cbModal #cbModalCorpo')
			.find("*").remove();
/* maf280619: incompatível com ie11
	$('#cbModal #cbModalCorpo')
			.append(gVersaoSistema + "&nbsp;&nbsp;" + "<a href='?_modulo=controleversao' target='_blank'>Alterar vers&atilde;o</a>")
			.append("<hr>")
			.append(`
<table>
<tr>
	<td class="cinza">Db host:</td>
	<td class="cinza">${gDbHost+" / "+gDbVersao}</td>
</tr>
<tr>
        <td class="cinza">App host:</td> 
        <td class="cinza">${gAppHost+" / "+gAppVersao}</td>
</tr>
</table>`)
	;
*/
$('#cbModal #cbModalCorpo').append(gVersaoSistema + "&nbsp;&nbsp;" + "<a href='?_modulo=controleversao' target='_blank'>Alterar vers&atilde;o</a>").append("<hr>").append("\n<table>\n<tr>\n\t<td class=\"cinza\">Db host:</td>\n\t<td class=\"cinza\">".concat(gDbHost + " / " + gDbVersao, "</td>\n</tr>\n<tr>\n        <td class=\"cinza\">App host:</td> \n        <td class=\"cinza\">").concat(gAppHost + " / " + gAppVersao, "</td>\n</tr>\n</table>"));
	$('#cbModal').addClass('quarenta').modal();
}

/*maf280619: incompatível com ie11
function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}*/
function sleep(ms) {
  return new Promise(function (resolve) {
    return setTimeout(resolve, ms);
  });
}

function construirDashGrupo($mode, idGrupo, idLp, json){
	let jDashConf = json;

	let $dashGrupos = $("#dashgrupo-conf-"+idLp);

	let dashGrupo = jDashConf["dashgrupo"][idGrupo];

	let h3Content = "";
	let inputGroup = "";

	if($mode == 'w'){
		h3Content = `
			<i class="fa fa-ellipsis-v pointer" style="padding: 3px 7px;" id="pop-conf-${dashGrupo.id}-${idLp}"></i>
			<button id="order_panel_${dashGrupo.id}_${idLp}" style="margin-left: 20px;" class="btn btn-success btn-xs hidden" onclick="ordenarDashPanel(${idLp}, ${dashGrupo.id})">
				<i class="fa fa-check" style="margin-right: 0;"></i> Finalizar
			</button>
		`;
		inputGroup = `
			<div class="input-group" style="width: 20%;margin-top: 10px;">
				<input type="text" id="novo-dashpanel-input-${dashGrupo.id}-${idLp}" placeholder="Novo Painel" onfocus="addEnterEventDash(this,${idLp})" onfocusout="removeEnterEventDash(this,${idLp})">
				<span class="input-group-addon pointer" onclick="novoDashPanel(${dashGrupo.id}, ${idLp},$('#novo-dashpanel-input-' + ${dashGrupo.id} + '-' + ${idLp}))">
					<i class="fa fa-check verde"></i>
				</span>
			</div>
		`;
	}

	let oGrupo = `
		<div id="div-conf-${dashGrupo.id}-${idLp}" ordem="${dashGrupo.id}" class="panel panel-primary" style="border-left-color:${dashGrupo.corsistema}; border-left-width:3px; margin-bottom:30px;">				
			<div class="panel-body" style="background:#F3F3F3; padding:30px; border-radius:4px;">
				<h3 id="rot-conf-${dashGrupo.id}-${idLp}" class="text-on-pannel text-primary" style="background: ${dashGrupo.corsistema};color:#F3F3F3 !important;">
					<div>
						<strong class="text-uppercase">${dashGrupo.rotulo}</strong>
						${h3Content}
					</div>
				</h3>
				<div class="row" id="dashpanel-conf-${dashGrupo.id}-${idLp}"></div>
				${inputGroup}
			</div>
		</div>
	`;
	
	$dashGrupos.append(oGrupo);

	if($mode == 'w'){
		$(`#pop-conf-${dashGrupo.id}-${idLp}`)
			.webuiPopover({
				content: popMenuDashConfGrupo(idLp, dashGrupo.id, dashGrupo.rotulo)
			});
	}
}

function construirDashPanel($mode, idPanel, idGrupo, idLp, json){
	let jDashConf = json;

	let $dashPanels = $("#dashpanel-conf-"+idGrupo+"-"+idLp);

	let dashPanel = jDashConf["dashgrupo"][idGrupo]["dashpanel"][idPanel];

	let oCards = "";

	for(let i = 0; i <= dashPanel.ymax; i++){
		oCards += constroiCardSpaceByLvl(i, dashPanel.id, idGrupo, idLp);
	}

	let h3Content = "";
	let h3Trash = "";
	let hidden = "";

	if($mode == 'w'){
		h3Content = `
			<i class="fa fa-ellipsis-v pointer" style="padding: 3px 7px;" id="pop-conf-${dashPanel.id}-${idGrupo}-${idLp}"></i>
			<button class="btn btn-primary btn-xs hidden" style="margin-left: 20px;" onclick="adicionarLinhaDashPanel(${idPanel}, ${idGrupo}, ${idLp})"><i class="fa fa-plus"></i> Linha</button>
			<button class="btn btn-danger btn-xs hidden" style="margin-left: 20px;" onclick="removerLinhaDashPanel(${idPanel}, ${idGrupo}, ${idLp})"><i class="fa fa-minus"></i> Linha</button>
			<button class="btn btn-success btn-xs hidden" style="margin-left: 20px;" onclick="addCardDashPanel(${idPanel}, ${idGrupo}, ${idLp})"><i class="fa fa-check"></i> Finalizar</button>
		`;
		h3Trash = `
			<h3 id="excluir-conf-${dashPanel.id}-${idGrupo}-${idLp}" class="trash-card text-primary hidden">
				<div>
					<i class="fa fa-trash pointer" style="padding: 3px 7px;"></i>
				</div>
			</h3>
		`;
	}else{
		hidden = "hidden";
	}

	let oPanel = `
		<div id="div-conf-${dashPanel.id}-${idGrupo}-${idLp}" ordem="${dashPanel.ordem}" class="col-md-12 painel">
			<h3 id="rot-conf-${dashPanel.id}-${idGrupo}-${idLp}" class="text-on-pannel text-primary">
				<div>
					<strong class="text-uppercase">${dashPanel.rotulo}</strong>
					${h3Content}
				</div>
			</h3>
			${h3Trash}
			<div id="dashpanel-conf-${idPanel}-${idGrupo}-${idLp}" class="col-md-12 ${hidden}" y-max="${dashPanel.ymax}" style="margin-top: 10px;">
				${oCards}
			</div>
		</div>
	`;
	
	$dashPanels.append(oPanel);
	if($mode == 'w'){
		$(`#pop-conf-${dashPanel.id}-${idGrupo}-${idLp}`)
			.webuiPopover({
				content: popMenuDashConfPanel(idLp, idGrupo, dashPanel.id, dashPanel.rotulo)
			});
	}
}

function construirDashCard(card, idPanel, idGrupo, idLp,$mode = 'r'){
	let $Subcard = "";
	let exclamation = "";
	let shadowred = false;
	let shadowblue = '';
	let NomeComId = ($mode == 'r')? card.iddashcard+' - '+ ((card.titulopersonalizado == '')?card.titulo:card.titulopersonalizado) :'';
	
	if ($mode == 'w')
		$Subcard = `
			<i onclick='getEtlRel(${card.iddashcard})' style="position: relative;right: 10px;bottom: 20px;" class='fright cinza negrito pointer fa fa-bar-chart transition hidden'></i>
		`;
	if ($mode == 'r' && card.titulo == '' && card.subtitulo == '') {
		exclamation = `
			<span class="bg-danger badge badgedash pointer" title="Inativo"><i class="fa fa-exclamation-triangle"></i></span>
			`;
		shadowred = true;

	}
	if(card.titulopersonalizado != "" && card.titulopersonalizado != card.titulo && $mode == 'r'){
		shadowblue = 'shadow-blue';
	}

	let rotuloDash = `${(NomeComId == '')? ((card.titulopersonalizado == '')?card.titulo:card.titulopersonalizado) : NomeComId}`;

	let oCard = `
		<div class="card border-left-${card.corBorda || card.cor} ${!shadowred?'shadow':'shadow-red'} ${shadowblue} h-100 hidden dashcard-${card.iddashcard}" iddashcard="${card.iddashcard}" titulo="${card.titulo}" titulopersonalizado="${card.titulopersonalizado || ""}" subtitulo="${card.subtitulo}" cor="${card.cor}" style="border-radius:8px;">
			${exclamation}
			<div class="card-body">
				<div class="row no-gutters align-items-center m-0">
					<div class="col-md-12">
						<div class="text-xs mb-1 p-0" style="color:#888;text-align:left;padding:0px 10px ${rotuloDash}">${rotuloDash}</div>
					</div>
				</div>
				<div class="row m-0">
					<div class="col-md-12">
						<div class="mb-0 font-weight_bold text_gray-800 titulo-${card.cor}" style="text-align:center;font-weight:bolder;">
							<span class="card_value"></span>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div style="text-align:left;font-weight:bolder;">
							<span id="card_title_sub" title="${card.iddashcard}" class="bg-${card.cor}" card_titlesub="">${card.subtitulo}</span>
						</div>
					</div>
					
				</div>
			</div>
		</div>
		${$Subcard}
	`;

	$(`.column-${idPanel}-${idGrupo}-${idLp}[x="${card.x}"][y="${card.y}"]`).append(oCard);
}

function constroiCardSpaceByLvl(level, idPanel, idGrupo, idLp){
	let x = 12;
	let col = 1;
	let content = "";

	for(let i = 0; i < x; i++){
		content += `<div class="card-space column-${idPanel}-${idGrupo}-${idLp} p10 col-md-${col} card-space-height" x="${i}" y="${level}"></div>`;
	}

	return content;
}

/*
 * Maf: Controlar de forma centralizada a atribuição de plugins/métodos a elementos
 	inseridos dinamicamente no DOM.
 	O objeto oMutationObs é declarado na functions.js e é utilizado da seguinte maneira:

 	oMutationObs = {
	
		"class" : { //Neste elemento deve ser informado cada atributo "class" da tag html que vai receber um método/plugin dinamicamente, através de callback
			"colorpalette" : function(){
			 	console.log("Elemento com class colorpalette encontrado:");
			}
			,"form-control" : function(){
				console.log("Form-control encontrado:");
			}
		}

 	}

 	Outras implementações podem ser feitas utilizando-se outros atributos, seguindo a mesma lógica

 */
oMutationObs = {
	"class" : { //Neste elemento deve ser informado cada atributo "class" da tag html que vai receber um método/plugin dinamicamente, através de callback
		"colorpalette" : function($o){
			//Atribui o plugin colorpallete
			$o.colorPalette({colors:[CB.arrCores]}).on('selectColor', function(e) {
				$(e.target).prevAll(".colorpicker")
							.removeClass("fa-circle-o blink")
							.addClass("fa-circle")
							.css("color",e.color)
							.attr("color",e.color||"");
			}); 
		},
		"calendarioprazo" : function($o){
			$o.daterangepicker({
				"autoUpdateInput": false,
				"singleDatePicker": true,
				"showDropdowns": true,
				"linkedCalendars": false,
				"opens": "left",
				"locale": CB.jDateRangeLocale
			}).on("click", function(e, picker) {
				e.stopPropagation();
			}).on("apply.daterangepicker", function(e, picker) {
				alteraPrazo($(this).closest(".eventoRow").attr("idevento"),$(this).closest(".eventoRow").attr("prazo"), picker.startDate.format("YYYY-MM-DD"));
				picker.element.val(picker.startDate.format(picker.locale.format));
				$(this).html(picker.startDate.format("DD/MM")||"");
				$(this).css("background","#357ebd");
				$(this).closest(".eventoRow").attr("prazo", picker.startDate.format("YYYY-MM-DD"));
				
				
	
	
				//configCalendario();
			});
			
		},
		"calendariotime" : function($o){		
			var data = $("[name=_1_" + CB.acao + "_evento_inicio]").val();
			if($("[name=_1_" + CB.acao + "_evento_inicio]").val()!==undefined){
				var dataInicial = moment();
				var data = $("[name=_1_" + CB.acao + "_evento_inicio]").val().split('/');
				data = data[2] + "-" + data[1] + "-" + data[0];
				var hora = $("[name=_1_" + CB.acao + "_evento_iniciohms]").val();
				var dataMinutos = moment(data + ' ' + hora);
				var dataFinal = dataMinutos.diff(dataInicial);
				var d = moment.duration(dataFinal);
				var s = Math.floor(d.asHours()) + "h" + moment.utc(dataFinal).format(" mm") +"m";
				if(data) {
					var startDate = moment().startOf('hour').add(d.asHours() + 1,"hour");
				} else {
					var startDate = moment().startOf('hour').add(1,"hour");
				}
			}

			$o.daterangepicker({
				"autoUpdateInput": false,
				"singleDatePicker": true,
				"showDropdowns": true,
				"linkedCalendars": false,
				"opens": "left",
				"timePicker": true,
				"locale": CB.jDateRangeLocale,
				"startDate": startDate,
				"minDate": moment().subtract(9, 'years'),  
				"maxDate": moment().add(1, 'years'),
				"timePicker24Hour": true,
				"timePickerIncrement": 15,
				"time": {
					enabled: true
				},
				"format": 'DD.MM.YYYY HH:mm'
			}).on("click", function(e, picker) {
				e.stopPropagation();
			}).on("apply.daterangepicker", function(e, picker) {

				//$(this).html(picker.startDate.format("DD/MM/YYYY")||"");
				var html = alteraData($(this).closest(".eventoRow").attr("idevento"),$(this).closest(".eventoRow").attr("iniciodata"), picker.startDate.format("YYYY-MM-DD"),$(this).closest(".eventoRow").attr("iniciohms"),picker.startDate.format("HH:mm:ss"),$(this).closest(".eventoRow").attr("diainteiro"),$(this).closest(".eventoRow").attr("duracaohms"),$(this).closest(".eventoRow").attr("travasala"),$(this).closest(".eventoRow").attr("idequipamento"),$(this));
				picker.element.val(picker.startDate.format(picker.locale.format));
				$(this).css("background","#357ebd");
				$(this).css("color","#fff");
				$(this).closest(".eventoRow").attr("iniciodata", picker.startDate.format("DD/MM/YYYY"));
				$(this).closest(".eventoRow").attr("inicio", picker.startDate.format("YYYY-MM-DD"));
				$(this).closest(".eventoRow").attr("iniciohms", picker.startDate.format("HH:mm:ss"));
			});
			
		},
		ajaxloading: function($o){
			//Adicionar a classe de loading em todos os elementos filho, para que se mostre corretamente o indicador de carregamento, sem ser necessário atribuir a classe a manualmente a esses filhos
			$o.find("*").addClass("ajaxloading");
			//console.log($o);
		}
	}
};

/* maf280619: incompatível com ie11
var MutationObserver    = window.MutationObserver || window.WebKitMutationObserver;
if(MutationObserver){
	var myObserver          = new MutationObserver (mutationHandler);
	var obsConfig           = { 
		childList: true, 
		characterData: true, 
		attributes: true, 
		subtree: true 
	};

	//--- Add a target node to the observer. Can only add one node at a time.
	myObserver.observe (document, obsConfig);

	function mutationHandler (mutationRecords) {
	    mutationRecords.forEach ( function (mutation) {
			mutation.addedNodes.forEach(function(o){
				//console.log(o);
				if(!o.querySelectorAll) return false;
				els=o.querySelectorAll("*");
				els.forEach(function(co){
			        $co=$(co);
					if($co.attr("class")!==undefined && $co.attr("class")!==""){
					    $.each(oMutationObs["class"], function(classe,callback){
			                //console.log($co);
			                if($co.hasClass(classe)){
								callback($co);
			                }
			            })
			        }
			    });
				
			})
	    } );
	}
}
*/

var MutationObserver = window.MutationObserver || window.WebKitMutationObserver;

if (MutationObserver) {
  var mutationHandler = function mutationHandler(mutationRecords) {
    mutationRecords.forEach(function (mutation) {
     if(!mutation.addedNodes.forEach){return false;} 
     mutation.addedNodes.forEach(function (o) {
        //console.log(o);
        if (!o.querySelectorAll) return false;
        els = o.querySelectorAll("*");
        els.forEach(function (co) {
          $co = $(co);

          if ($co.attr("class") !== undefined && $co.attr("class") !== "") {
            $.each(oMutationObs["class"], function (classe, callback) {
              //console.log($co);
              if ($co.hasClass(classe)) {
                callback($co);
              }
            });
          }
        });
      });
    });
  };

  var myObserver = new MutationObserver(mutationHandler);
  var obsConfig = {
    childList: true,
    characterData: true,
    attributes: true,
    subtree: true
  }; //--- Add a target node to the observer. Can only add one node at a time.

  myObserver.observe(document, obsConfig);
}

//maf: gerar id unico para elementos
var uniqueId = function() { 
  return 'cb-' + Math.random().toString(36).substr(2, 16);
};

//maf: Registra o último elemento clicado na tela, para ser utilizado apos fetch/ajax/outros, e adiciona classe de carregamento
$(document).click(function(event) {

    let ot=event.target;
    if(ot.classList.value.includes("ajaxloading")){
	ot.classList.add("ajaxativo");
	ot.id=ot.id || uniqueId();
	document.ajaxativo=ot.id;
	event.stopPropagation();
    }
    //O ID vai estar vazio caso o obj nao o possua
    document.lastClicked=ot.id;
    return true;
});

//maf: monkey patch na funcao fetch original, para conter metodo de remocao de loadings
const { fetch: originalFetch } = window;
window.fetch = async (...args) => {
    let [resource, config ] = args;
    //console.log("Fetch: "+resource);
    const response = await originalFetch(resource, config);

    //Remove classe de elementos iniciadores de requisicao fetch. Caso ocorram problemas de elementos persistentes na tela, utilizar a limpeza total no else
    oAativo=document.getElementById(document.ajaxativo);
    if(oAativo){
        oAativo.classList.remove("ajaxativo");
    }else{
        //Limpeza total
        let els = document.getElementsByClassName("ajaxativo");
        for (var i = 0; els[i]; i++) {
             //remover classe aqui
        }
    }
    document.ajaxativo=null;

    return response;
};

function tratarNumero(num)
{
	if((typeof num === 'NaN') || (num == '' && typeof num !== 'number'))
	{
		return null;
	}

	num = num.toString().replace(",", ".");

	let arraySep = num.split('.');

	if(arraySep.length > 2)
	{
		num = num.replace('/\.(?=.*\.)/', '');

		if(!num) return false;
	}

	return parseFloat(num);
}

function formatarValorBRL(valor, symbol = 'R$') {
	if(!(typeof valor === 'number' && !Number.isInteger(valor)))
		valor = parseFloat(valor);

    // Formata o valor como float com duas casas decimais
    let valorFormatado = valor.toFixed(2).replace('.', ',');

	// Adiciona o ponto para separar as casas de milhar
	valorFormatado = valorFormatado.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    // Adiciona o símbolo da moeda BRL
    return `${symbol} ${valorFormatado}`;
}

function formatarData(data) {
    // Cria um objeto Date a partir da string de data
    const dataObj = new Date(data);

    // Extrai os componentes da data
    const dia = String(dataObj.getDate()).padStart(2, '0');
    const mes = String(dataObj.getMonth() + 1).padStart(2, '0');
    const ano = String(dataObj.getFullYear());
    const horas = String(dataObj.getHours()).padStart(2, '0');
    const minutos = String(dataObj.getMinutes()).padStart(2, '0');
    const segundos = String(dataObj.getSeconds()).padStart(2, '0');

    // Formata a data no padrão desejado
    const dataFormatada = `${dia}/${mes}/${ano}`;

    return dataFormatada;
}

function converterParaFormatoAmericano(data) {
    // Dividir a string em dia, mês e ano
    var partes = data.split('/');
    var dia = partes[0];
    var mes = partes[1];
    var ano = partes[2];

    // Formatar a data no padrão americano
    var dataAmericana = ano + '-' + mes + '-' + dia;
    
    return dataAmericana;
}
async function buscarInfoRelatorioPorIdRep(idrep, config = {}) {
	return $.ajax({
		url: './../../ajax/_rep.php',
		method: 'GET',
		dataType: 'json',
		data: {
			action: 'buscarInfoRelatorioPorIdRep',
			params: idrep
		},
		success: res => {
			if(res.error) return alertAtencao(res.error);
		}
	})
}

async function linkAbrirRelatorio(elementHTML, idrep, config = {}) {
	elementHTML.classList.add('carregando-rep');
	
	let userPrefConfig = {};

	if(!idrep) return alertAtencao(`idrep[${idrep}] inválido!`);

	const repConf = await buscarInfoRelatorioPorIdRep(idrep);

	if(!repConf) return alertAtencao('Ocorreu um erro ao buscar relatório');
	if(repConf.error) {
		elementHTML.classList.remove('carregando-rep');
		return false
	};
	

	userPrefConfig = {
		menurelatorio: {
			[repConf.modulopar]: {
				[repConf.idreptipo]: {
					[idrep]: config
				}
			}
		}
	}

	const filtro = Object.keys(config)
    .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(config[key])}`)
    .join('&');

	CB.setPrefUsuario('d', `menurelatorio`, undefined, () => {
		CB.setPrefUsuario('m', JSON.stringify(userPrefConfig), undefined, () => {
			elementHTML.classList.remove('carregando-rep');
			CB.modal({
				titulo: "</strong>Relatório</strong>",
				url:'report/_8repprint.php?_filtros='+btoa(filtro)+'&_idrep='+idrep+'&_idempresa='+gIdEmpresa,
				classe: 'noventa',
			});
		});
	});
}

if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("/inc/js/service-worker.js")
        .then(() => console.log("Service Worker registrado!"))
        .catch((error) => console.error("Erro ao registrar Service Worker", error));
}

let alterarValorCampo = (campo, valor, tabela, inid, texto, isDecimal = false) => {
	htmlTrModelo = `<div id="alt${campo}${inid}">
						<table class="table table-hover">
							<tr>
								<td>${texto}</td>
								<td>
									<input name="_h1_i_${tabela}_idobjeto" value="${inid}" type="hidden">
									<input name="_h1_i_${tabela}_campo" value="${campo}" type="hidden">
									<input name="_h1_i_${tabela}_tipoobjeto" value="${CB.modulo}" type="hidden">
									<input name="_h1_i_${tabela}_valor_old" value="${valor}" type="hidden">
									<input name="_h1_i_${tabela}_valor" value="${valor}" class="size10" type="text" placeholder="00:00" autocomplete="off" ${isDecimal ? 'vdecimal' :''}>
								</td>
							</tr>
							<tr>
								<td>Justificativa:</td>
								<td>
									<input id="justificativa" name="_h1_i_${tabela}_justificativa" vnulo class="size50">
								</td>
							</tr>
						</table>
					</div>`;

		var objfrm = $(htmlTrModelo);
			objfrm.find("#ndroptipo option[value='" + valor + "']").attr("selected", "selected");
			objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");

		strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='salvaHistCampo()' style='float: right; margin-top: 14px;'><i class='fa fa-circle'></i>Salvar</button></strong>";

		CB.modal({
			titulo: strCabecalho,
			corpo: "<table>" + objfrm.html() + "</table>",
			classe: 'sessenta',
			aoAbrir: function(vthis) {
				$(`[name="_h1_i_${tabela}_valor"]`).val(valor);
			}
		});
}

let salvaHistCampo = () => {
	if ($(`#justificativa`).val().length < 5) return alertAtencao(`Justificativa deve ter pelo menos 5 caracteres`);
	
	CB.post({
		objetos: {
			'_h1_i_modulohistorico_idobjeto': $('[name="_h1_i_modulohistorico_idobjeto"]').val(),
			'_h1_i_modulohistorico_campo': $('[name="_h1_i_modulohistorico_campo"]').val(),
			'_h1_i_modulohistorico_tipoobjeto': $('[name="_h1_i_modulohistorico_tipoobjeto"]').val(),
			'_h1_i_modulohistorico_valor_old': $('[name="_h1_i_modulohistorico_valor_old"]').val(),
			'_h1_i_modulohistorico_valor': $('[name="_h1_i_modulohistorico_valor"]').val(),
			'_h1_i_modulohistorico_justificativa': $('[name="_h1_i_modulohistorico_justificativa"]').val(),
		},
		parcial: true,
		posPost: () => {
			CB.oModal.modal('hide');
		}
	});
}

let montarHistCampo = (hist) => {
	const corpo = `<table class="table table-hover w-100">
						<thead>
							<th>De</th>
							<th>Para</th>
							<th>Justificativa</th>
							<th>Por</th>
							<th>Em</th>
						</thead>
						<tbody>
							${hist.map(item => `
								<tr>
									<td>${item.valor_old ?? '-'}</td>
									<td>${item.valor ?? '-'}</td>
									<td>${item.justificativa}</td>
									<td>${item.nomecurto}</td>
									<td>${dmahms(item.criadoem)}</td>
								</tr>`).join('') ?? `
								<tr>
									<td colspan='5'>Sem histórico.</td>
								</tr>`
							}
						</tbody>
					</table>`;
		
		CB.modal({
			titulo: 'Histórico de Alterações',
			corpo,
		});
}
