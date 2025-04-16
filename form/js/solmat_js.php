<script>
    //------- Injeção PHP no Jquery -------
    const modulo = '<?= $_GET['_modulo'] ?>';
    let idsolmat = <?=$_1_u_solmat_idsolmat ? $_1_u_solmat_idsolmat : 0;?>;
    let jprodservtemp = <?=$jprodservtemp ? json_encode($jprodservtemp) : '[]'; ?>; // autocomplete jTipoProdServ
    var _acao = '<?=$_acao ?>';
    var idempresa = '<?=$_1_u_solmat_idempresa?>';
    var idpessoaSessao = '<?=$_SESSION["SESSAO"]["IDPESSOA"] ?>';
	var itemPendenteAprovacao='<?=$itemPendenteAprovacao?>';
    //------- Injeção PHP no Jquery -------

    //------- Funções JS -------
    CB.prePost = function() {
		let url = removerParametroGet("idsolmatcp", window.location.href);
		window.history.pushState(null, window.document.title, url);
	}

    //autocomplete de jTipoProdServ
	$("#insidprodserv").autocomplete({
		source: jprodservtemp,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				return $('<li>').append("<a>" + item.descr + " - " + item.un + " - <span style='color:#b6b6b6;'>" + item.codprodserv + "</span></a>").appendTo(ul);
			};
		},
		select: function(event, ui) {

			CB.post({
				objetos: {
					"_w_i_solmatitem_descr": ui.item.descr,
					"_w_i_solmatitem_idsolmat": $("#idsolmat").val(),
					"_w_i_solmatitem_idprodserv": ui.item.idprodserv,
					"_w_i_solmatitem_un": ui.item.un
				},
				parcial: true
			});
		}
	});

	$('.soll').each(function(key, element) {
		let quantidadeFilhos = $(element).find('+ div > table > tbody > tr:not(.cabitem)').get().length;

		if($(element).find('+ div > table > tbody > tr:not(.cabitem).fundoVerde').get().length == quantidadeFilhos)
			$(this).addClass('fundoVerde');
	});

    /*
	 * Duplicar solmat [ctrl]+[d]
	 */
	$(document).keydown(function(event) {
        if (!((event.ctrlKey || event.altKey) && event.keyCode == 68)) return true;
        if (!teclaLiberada(event)) return; //Evitar repetição do comando abaixo
        janelamodal('?_modulo=' + CB.modulo + '&_acao=i&idsolmatcp=' + $("[name='_1_u_solmat_idsolmat']").val());
        return false;
    });

    $('.cbFecharForm').on('change', function(){
        console.log('fechar');
        if($('#cbModuloForm').html() == ''){
            $('#cbPanelBotaoFlutuante').remove();
        }
    });

    $(document).ready(function () {
		if($("#comentarioopoup").length && $('#cbModuloForm').html()!=''){
			var comentario = $("#comentarioopoup")[0].innerHTML;
			CB.montaBotaoFlutuante('fa fa-comment fa-3x pointer', 'icone', comentario);
			CB.oPanelLegenda.css( "zIndex", 901).addClass('screen');
			if($('#cbSalvarComentario').length == 0)
			{
				$('#descricaobotao').append(`<button id="cbSalvarComentario" type="button" class="btn btn-success btn-xs" onclick="addCometario()" title="Salvar" style="float: right;margin-top: 8px;margin-right: 8px;">
					<i class="fa fa-circle"></i>Salvar
				</button>`);
			}
		}
	});

    if ($(`[name=_1_${_acao}_solmat_idsolmat]`).val()) {
		$(".cbupload").dropzone({
			idObjeto: $(`[name=_1_${_acao}_solmat_idsolmat]`).val(),
			tipoObjeto: 'solmat',
			idPessoaLogada: idpessoaSessao
		});
	}

    CB.on('posLoadUrl', function(){
        <?
        if($escondeCadAss == true) { 
            ?>
            $('#cass_assoc').hide();
        <? } ?>
        if(CB.jsonModulo.jsonpreferencias.botaoflutuante == 'N'){
            $('#cbSalvarComentario').hide();
        } else {
            $('#cbSalvarComentario').show();
        }
    });

    $(document).keydown(function(e) {
	    if (e.keyCode == 27) {
            $('#cbPanelBotaoFlutuante').hide();
        }
    });

	$(document).on('keypress', 'input.lotecons_qtdd', function(e) {
		var $this = $(this);
		var key = (window.event) ? event.keyCode : e.which;
		var dataAcceptDot = $this.data('accept-dot');
		var dataAcceptComma = $this.data('accept-comma');
		var acceptDot = (typeof dataAcceptDot !== 'undefined' && (dataAcceptDot == true || dataAcceptDot == 1) ? true  :false);
		var acceptComma = (typeof dataAcceptComma !== 'undefined' && (dataAcceptComma == true || dataAcceptComma == 1) ? true : false);

		if((key > 47 && key < 58) || (key == 46 && acceptDot) || (key == 44 && acceptComma)) {
			return true;
		} else {
			return (key == 8 || key == 0) ? true : false;
		}
	});
    //------- Funções JS -------

    //------- Funções Módulo -------
    if(modulo == 'solmat') 
    {
        $('.soll:has(+ div tr.fundoverde)').each(function(key, element) {
            $(element).next().get(0).classList.replace('collapse-in', 'collapse');
        });
    }



	$('#gerar-qrcode').on('click', _ => {

		CB.modal({
			titulo: "Lotes",
			corpo: `<div>
						<div id="qrcode" ondblclick="copiaLinkConferencia()" class="d-flex justify-content-center"></div>
						<div>
							<button class="btn btn-info" onclick="janelamodal('?_modulo=solmatconferencia&_acao=u&idsolmat=${idsolmat}')">Acessar</button>
						</div>
					</div>`
		});

		new QRCode(document.getElementById("qrcode"), {
			text: `${window.location.hostname}/?_modulo=solmatconferencia&_acao=u&idsolmat=${idsolmat}`,
			width: 128,
    		height: 128,
		});
	});
	function copiaLinkConferencia()
	{
		const input = document.createElement('input');
		document.body.appendChild(input);
		input.value = `${window.location.hostname}/?_modulo=solmatconferencia&_acao=u&idsolmat=${idsolmat}`;
		input.select();
		const isSuccessful = document.execCommand('copy');
		input.style.display = 'none';
		if (!isSuccessful) {
			console.error('Failed to copy text.');
		} else {
			alertAzul("Link Copiado","",1000);
		}
		document.body.removeChild(input);
	}

	function consomePendentes(idsolmat)
	{
		if(confirm('Deseja realmente consumir todos os lotes pendentes?')){
			CB.post({
				objetos: {
					"_conspend_u_solmat_idsolmat":idsolmat
				}, parcial:true
			});
		}
	}

    function atualizaCampo(comentario)
    {
        $("#_99_i_modulocom_descricao").val(comentario.value);
    }

    function showModal() {
		_controleImpressaoModulo({
			modulo: getUrlParameter("_modulo"),
			grupo: 1,
			idempresa: idempresa || "1",
			objetos:{
				idsolmat: $(`[name=_1_${_acao}_solmat_idsolmat]`).val()
			}
		});
	}

    function calculoestoquedomodal(prodserv, prodservformula) 
    {
		var tabela = $(this).attr('tabela');

		if (tabela = 'prodservformula') 
        {
			if (prodservformula == null) {
				prodservformula = "";
			}
			var idprodservformula = prodservformula;
			var idprodserv = prodserv;
			CB.modal({
				url: `?_modulo=calculosestoque&_acao=u&idprodserv=${idprodserv}&idprodservformula=${idprodservformula}`,
				header: "Cálculos Estoque"
			});

		} else {

			var idprodserv = $("[name=_1_u_prodserv_idprodserv]").val();
			CB.modal({
				url: `?_modulo=calculosestoque&_acao=u&idprodserv=${idprodserv}`,
				header: "Cálculos Estoque"
			});
		}
	}

    function excluir(idsolmatitem) 
    {
		if (confirm("Deseja realmente excluir o Material selecionado?")) {

			CB.post({
				"objetos": `_x_d_solmatitem_idsolmatitem=${idsolmatitem}`
			});
		}
	}

    function mudarunidade(vthis) 
    {
		CB.post({
			objetos: `_x_${_acao}_solmat_idunidade=${vthis.value}&_x_${_acao}_solmat_idsolmat=${$("#idsolmat").val()}`
		});
	}

	function formula(vthis, nsolmat) 
    {
		CB.post({
			objetos: {
				"_p1_u_solmatitem_idsolmatitem": $(`[name=_x${nsolmat}_u_solmatitem_idsolmatitem]`).val(),
				"_p1_u_solmatitem_idprodservformula": $(vthis).val()
			},
			parcial: true
		});
	}

    function addCometario()
    {	
		console.log($("#_99_i_modulocom_descricao").val())
        ST.desbloquearCBPost();
        CB.post({
            objetos:{
                "_99_i_modulocom_descricao": $("#_99_i_modulocom_descricao").val(),
                "_99_i_modulocom_idmodulo": $("#_99_i_modulocom_idmodulo").val(),
                "_99_i_modulocom_modulo": $("#_99_i_modulocom_modulo").val()
            },
            parcial: true  
        });
    }

    function gerarfracao(vthis, idsolmatitem, idlote, idlotefracao, qtd, qtdc) 
    {
		var consAtual= 0;

		$(`.solmatitem${idsolmatitem}`).find(`input.mw-input`).each((i,e)=>{
			consAtual = consAtual + ($(e).val() == '' ? 0 : parseFloat($(e).val()));
		})
		if(consAtual >= qtdc){
			//Fechar Collapse do item deslocado 
			$(`div#solmatitem${idsolmatitem}`).removeClass("collapse in");
			$(`div#solmatitem${idsolmatitem}`).addClass("collapse");   
		}
		
		var str = "_x_i_lotefracao_idlote=" + idlote +
			"&_x_i_lotefracao_idunidade=" + $(`[name=_1_${_acao}_solmat_idunidade]`).val() +
			"&_x_i_lotefracao_idlotefracaoorigem=" + idlotefracao +
			"&_x_i_lotefracao_idobjeto=" + idsolmatitem +
			"&_x_i_lotefracao_tipoobjeto=solmatitem" +
			"&_x_i_lotefracao_qtd=" + $(vthis).val() + "&_qtdmaximo_=" + qtd + "&_qtdcmaximo_=" + qtdc;

			if(consAtual >= qtdc){
				$(`.solmatitem${idsolmatitem}`).addClass('fundoAmarelo');
				CB.setPrefUsuario('m',`{"${CB.modulo}":{"collapse":{"solmatitem${idsolmatitem}":"Y"}}}`);
			}
			$(vthis).attr('readonly','readonly')
			
		CB.post({
			objetos: str,
			parcial: true,
			refresh: false,
		});
	}

    function excluirlotecons(...data) 
    {
		if (data.length > 0) {
			let obj = {};
			for (let d in data) {
				if (d < data.length - 1) {
					obj[`_x${d}_u_lotecons_idlotecons`] = data[d];
					obj[`_x${d}_u_lotecons_status`] = "INATIVO";
				}
			}
			let idlotefracao = data[data.length - 1];
			if (idlotefracao != null) {
				obj[`_x300_d_lotefracao_idlotefracao`] = idlotefracao;
			}

			CB.post({
				objetos: obj,
				posPost: function(data, textStatus, jqXHR) {
					let modal = $('.modal-body');
					let vid = modal.find("input[type='hidden']").attr('idlote');
					modal.html($("#consumo_" + vid).html())
				},
				parcial: true
			});
		}
	}

    function consumo(vid) 
    {
		CB.modal({
			titulo: "</strong>Histórico do Lote</strong>",
			corpo: $("#consumo_" + vid).html(),
			classe: 'sessenta',
			parcial: true
		});
	}

    function alteraAltura(mostrar)
	{
		if(mostrar == 'Y'){
			$("#cbPanelBotaoFlutuante").css("height", "80%");
			$("#cbPanelBotaoFlutuante").css("width", "33%");
			$("#cbPanelBotaoFlutuante").css("overflow-y", "scroll");
			$("#cbPanelBotaoFlutuanteFechar").show();
			$("#cbPanelBotaoFlutuanteAbrir").hide();
			$('#cbSalvarComentario').show();		
		} else {
			$("#cbPanelBotaoFlutuante").css("height", "auto");
			$("#cbPanelBotaoFlutuante").css("width", "auto");
			$("#cbPanelBotaoFlutuante").css("overflow-y", "hidden");
			$("#cbPanelBotaoFlutuanteFechar").hide();
			$("#cbPanelBotaoFlutuanteAbrir").show();
			$('#cbSalvarComentario').hide();
		}
	}

    function esconderMostrarTodos(tipo)
    {
        let state = $("#esconderMostrarTodos").attr('state');        
        let allCollapses = (state == 'Y') ? 'N' : 'Y';
		let delayAltura,
			delayCollapse;

        CB.setPrefUsuario('m',`{"${CB.modulo}":{"collapse":{"solmatitemtotal":"${allCollapses}"}}}`);

        $("#esconderMostrarTodos").attr('state', allCollapses);
		clearInterval(delayCollapse);
		clearInterval(delayAltura);

        $("."+tipo).each(function() {
            CB.setPrefUsuario('m',`{"${CB.modulo}":{"collapse":{"solmatitem${$(this).attr('idnfitem')}":"${allCollapses}"}}}`);

			let JQdivColapse = $($(this).attr('href'));

            if(allCollapses == 'N'){
                JQdivColapse.removeClass("collapse");
                JQdivColapse.addClass("collapse in");   

				delayAltura = setTimeout(() => {
					JQdivColapse.css('height', `${JQdivColapse.get(0).scrollHeight}px`);
				}, 100);
            } else {
				JQdivColapse.css('height', `0px`);

				delayCollapse = setTimeout(() => {
					JQdivColapse.removeClass("collapse in");
	                JQdivColapse.addClass("collapse");
				}, 150);
            }
        });
    }

	//--- bloco de rubricas

	const canvas = document.getElementById('canvas');
const context = canvas.getContext('2d');
let isDrawing = false;

// Função para começar o desenho
function startDrawing(e) {
    isDrawing = true;
    context.beginPath();
    const { x, y } = getCoordinates(e);
    context.moveTo(x, y);
}

// Função para continuar o desenho
function continueDrawing(e) {
    if (isDrawing) {
        const { x, y } = getCoordinates(e);
        context.lineTo(x, y);
        context.stroke();
    }
}

// Função para finalizar o desenho
function endDrawing() {
    isDrawing = false;
}

// Função para obter as coordenadas do evento (mouse ou toque)
function getCoordinates(e) {
    let clientX, clientY;
    if (e.touches && e.touches.length > 0) {
        clientX = e.touches[0].clientX;
        clientY = e.touches[0].clientY;
    } else {
        clientX = e.clientX;
        clientY = e.clientY;
    }
    const rect = canvas.getBoundingClientRect();
    return {
        x: clientX - rect.left,
        y: clientY - rect.top
    };
}

// Adicionando os listeners de eventos para mouse e toque
canvas.addEventListener('mousedown', startDrawing);
canvas.addEventListener('mousemove', continueDrawing);
canvas.addEventListener('mouseup', endDrawing);
canvas.addEventListener('mouseout', endDrawing);

canvas.addEventListener('touchstart', startDrawing);
canvas.addEventListener('touchmove', continueDrawing);
canvas.addEventListener('touchend', endDrawing);

// Desabilitar a seleção de texto na área do canvas
canvas.addEventListener('touchstart', (e) => {
    e.preventDefault();
});



	document.getElementById('clearButton').addEventListener('click', () => {
        context.clearRect(0, 0, canvas.width, canvas.height);
       
    });
	
    document.getElementById('trashButton').addEventListener('click', () => {
        context.clearRect(0, 0, canvas.width, canvas.height);
       
		
		CB.post({
			objetos: {
				"_1_u_assinatura_idassinatura": $('#imgassinatura').attr('value'),			
				"_1_u_assinatura_status": 'INATIVO'		
			},
			parcial: true
		});
    });

    document.getElementById('saveButton').addEventListener('click', () => {
        const signatureData = canvas.toDataURL();
        // Enviar signatureData para o PHP para salvar no banco de dados
        console.log(signatureData);
        $("#imgassinatura").removeClass( "hide" );
        $("#clearButton").removeClass( "hide" );
        $('#imgassinatura').attr('src',signatureData);

		CB.post({
			objetos: {
				"_1_i_assinatura_idobjeto": $('#idsolmat').val(),
				"_1_i_assinatura_tipoobjeto": 'solmat',
				"_1_i_assinatura_status": 'ASSINADO',
				"_1_i_assinatura_assinatura": signatureData,
				"_1_i_assinatura_tipoassinatura": 'base64'			
			},
			parcial: true
			
		});


    });

	function verificadisp(vthis,idsolmatitem,vardisp){
		debugger;		
		const planejado =parseInt($('#'+vardisp).val());
		const solicitado =parseInt($(vthis).val());		
		const estoque =parseInt($(vthis).attr("estoque"));	
		
		if(estoque < solicitado){
			alert("Estoque disponível menor que a quantidade solitada.");
			$(vthis).val('');
		}else{
			if( solicitado > planejado){
				alert('O valor '+$(vthis).val()+' e maior do que o planejado disponível '+$('#'+vardisp).val());
				if(confirm('Deseja Justificar?')){
					planejamento(idsolmatitem,'Justificativa do consumo excedente.','Y',$(vthis).val());
				}else{
					$(vthis).val('');
				}
			}else{
				CB.post({
					objetos: {
						"_1_u_solmatitem_idsolmatitem": idsolmatitem,					
						"_1_u_solmatitem_status": 'APROVADO'	
					},
					parcial: true,
					refresh:false,
					posPost: function(){
						$('#fig_'+vardisp).removeClass('vermelho');
						$('#fig_'+vardisp).addClass('verde');
						//removeClass("vermelho hoververmelho").addClass("verde hoververde");
					}
				});
			}

		}

	}
	
	function verificaestoque(vthis,idsolmatitem){
		debugger;		
		
		const solicitado =parseInt($(vthis).val());		
		const estoque =parseInt($(vthis).attr("estoque"));	
		
		if(estoque < solicitado){
			alert("Estoque disponível menor que a quantidade solitada.");
			$(vthis).val('');
		}
	}


	function planejamento(idsolmatitem,texto,salvar,valor) 
	{
		var htmloriginal = $("#planejamento"+idsolmatitem).html();
	  	var objfrm = $(htmloriginal);

		if(salvar=='Y')
		{
			btsalvar="<button id='cbSalvar' type='button'  style='margin-left:250px' class='btn btn-danger btn-xs' onclick='salvarjustificativa("+idsolmatitem+");'><i class='fa fa-circle'></i>Salvar</button>";
		}else{
			btsalvar='';
		}

		  objfrm.find("#"+idsolmatitem+"_justificativa").attr("class", ""+idsolmatitem+"_justificativa");
		  objfrm.find("#"+idsolmatitem+"_qtdc").attr("class", ""+idsolmatitem+"_qtdc");
		  objfrm.find("#"+idsolmatitem+"_qtdc").val(valor);
		// objfrm.find("#"+idsolmatitem+"_idsolmatitem").attr("name", "_"+idsolmatitem+"_u_solmatitem_idsolmatitem");
		

		CB.modal({
			titulo: "</strong>" + texto + ""+btsalvar+"  </strong>",
			corpo: [objfrm],
			classe: 'sessenta',
		});
	}

	function salvarjustificativa(idsolmatitem){
		debugger;
		
		if($("."+idsolmatitem+"_justificativa").val() == ''){
			alert('É necessário informar a justificativa do consumo.');
		}else{
			CB.post({
					objetos: {
						"_1_u_solmatitem_idsolmatitem": idsolmatitem,	
						"_1_u_solmatitem_status": "PENDENTE",	
						"_1_u_solmatitem_qtdc":$("."+idsolmatitem+"_qtdc").val(),					
						"_1_u_solmatitem_justificativa":$("."+idsolmatitem+"_justificativa").val()	
					},
					parcial: true,
					posPost: function(){
					$("#cbModalCorpo").html("");
					$('#cbModal').modal('hide');
					}
				});
		}
	}

	function aprovarconsumo(idsolmatitem,usuario){
		debugger;
		
		
			CB.post({
					objetos: {
						"_apv_u_solmatitem_idsolmatitem": idsolmatitem,	
						"_apv_u_solmatitem_status": "APROVADO",
						"_apv_u_solmatitem_aprovadopor": usuario						
					},
					parcial: true,
					posPost: function(){
					$("#cbModalCorpo").html("");
					$('#cbModal').modal('hide');
					}
				});
		
	}


	// function excluirloteconst(...data){
	// 	if(data.length > 0){
	// 		let obj = {};
	// 		for(let d in data){
	// 			if(d < data.length - 1){
	// 				obj[`_x${d}_u_lotecons_idlotecons`] = data[d];
	// 				obj[`_x${d}_u_lotecons_status`] = "INATIVO";					
	// 			}
	// 		}

	// 		CB.post({
	// 			objetos: obj
	// 			,parcial:true
	// 			,posPost: function(){
	// 				$("#cbModalCorpo").html("");
	// 				$('#cbModal').modal('hide');
	// 			}
	// 		});

	// 	}
	// }
    //------- Funções Módulo -------
	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>