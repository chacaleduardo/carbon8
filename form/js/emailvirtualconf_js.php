<script>

var idEmailVirtualConf = <?= $_1_u_emailvirtualconf_idemailvirtualconf ?? 'false' ?>;
var pessoas = <?= json_encode($pessoas) ?>;
var gruposDisponiveisParaVinculo = <?= json_encode($gruposDisponiveisParaVinculo) ?>;
var buscarWebmailAssinaturaTemplateDisponiveisParaaVinculo = <?= json_encode($buscarWebmailAssinaturaTemplateDisponiveisParaaVinculo) ?>;
var jPessoas = <?= json_encode($arrIdPessoas) ?>;

// GVT - 19/05/2020 - Adicionado o botão de atualizar BIM afim de atualizar os emails virtuais
function AtualizaBim(){
	$.ajax({
		type: "get",
		url : "cron/bim.php",
		success: function(data){
				//alert('OK');
				 alertSalvo('Bim Atualizado');
				 location.reload();
		},

		error: function(objxmlreq){
				alert('Erro:<br>'+objxmlreq.status); 
		}
	});
	
}

if(!$("#AtualizarBim").length){
	$( "#cbSalvar" ).after( '<button id="AtualizarBim" type="button" class="btn btn-info btn-xs" onclick=" AtualizaBim()" title="Atualizar Bim"><i class="fa fa-refresh"></i>Atualizar Bim</button>' );
}

$(document).keyup(function(e) {
     if (e.key === "Escape") { // escape key maps to keycode `27`
        // <DO YOUR WORK HERE>
		$( "#AtualizarBim" ).remove();
    }
});

//autocomplete de Pessoas
$("[name=emailvirtualconfpessoa]").autocomplete({
    source: pessoas
    ,delay: 0
    ,select: function(event, ui){
        insericontato(ui.item.idpessoa);		
    },create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
            return $('<li>').append("<a>"+item.nome+"</a><span class='spanemail'>["+item.email+"]</span>").appendTo(ul);

        };
    }	
});

//autocomplete de grupos
$("[name=emailvirtualconfimgrupo]").autocomplete({
    source: gruposDisponiveisParaVinculo
    ,delay: 0
    ,select: function(event, ui){
        inserigrupo(ui.item.idimgrupo);		
    },create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
            return $('<li>').append("<a>"+item.nome+"</a>").appendTo(ul);

        };
    }	
});

// GVT - 19/05/2020 - Função para vincular a pessoa com o email virtual
function insericontato(inid)
{
    CB.post({
		objetos: "_x_i_emailvirtualconfpessoa_idemailvirtualconf="+$("[name=_1_u_emailvirtualconf_idemailvirtualconf]").val()+"&_x_i_emailvirtualconfpessoa_idpessoa="+ inid
		,parcial: true
		,msgSalvo: false
    });
}

// GVT - 19/05/2020 - Função para vincular o grupo com o email virtual
function inserigrupo(inid){
   
    CB.post({
		objetos: "_x_i_emailvirtualconfimgrupo_idemailvirtualconf="+$("[name=_1_u_emailvirtualconf_idemailvirtualconf]").val()+"&_x_i_emailvirtualconfimgrupo_idimgrupo="+ inid
		,parcial: true
		,msgSalvo: false
    });
}   

function showtemplate(intitulo,inid){
	CB.modal({
		titulo: intitulo,
		corpo: $("#webmailassinatura_"+inid).html().replace('z-index: -1;',''),
		classe: "sessenta"
	});
}

function deletaidentidade(inpessoa){
	if(confirm("Deseja realmente excluir essa identidade de e-mail?")){
		var obj = {};

		inpessoa.forEach((o,i)=>{
			obj[`_wp${i}_d_webmailassinaturaobjeto_idwebmailassinaturaobjeto`] = o;
		});

		CB.post({
			objetos: obj
			,parcial: true
		});
	}
}
function delWebmailAssinatura1(vid){

CB.post({
	objetos: {
		"_x_d_webmailassinaturaobjeto_idwebmailassinaturaobjeto" : vid
	}
	,parcial:true
});
}

if(idEmailVirtualConf)
{
	$("#outrasassinaturas").autocomplete({
		source: buscarWebmailAssinaturaTemplateDisponiveisParaaVinculo
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				if(item.principal == 'Y'){
					return $('<li>').append("<a>"+item.descricao+"</a><i class='fa fa-star' title='Template Principal da Empresa'></i>").appendTo(ul);
				}else{
					return $('<li>').append("<a>"+item.descricao+"</a>").appendTo(ul);
				}

			};
		}
		,select: function(event, ui){
			$.ajax({
				type: "POST",
				url : "ajax/replaceassinaturaemail.php",
				data: { 
					id: idEmailVirtualConf,
					tipo: 'EMAILVIRTUAL',
					idtemplate: ui.item.id,
					template: ui.item.template
				},
				success: 
					function(data, textStatus, jqXHR){

						if(jqXHR.getResponseHeader('X-CB-RESPOSTA') == 'id'){
							var aux = $("<div>"+data+"</div>");
							$("body").append(aux.find("#_temp").html());
							var idwebmailassinaturaobjeto = jqXHR.getResponseHeader('idwebmailassinaturaobjeto');
							setTimeout(async function(){
								try{
									let dataUrl = await domtoimage.toPng($("#_template").get(0))
									let img = new Image();
									img.src = dataUrl;
									aux.find("#_temp").html(img);
									$("#_template").remove();
									CB.post({
										urlArquivo: 'ajax/replaceassinaturaemail.php?salvar=Y',
										refresh: 'refresh',
										objetos: {
											idwebmailassinaturaobjeto: idwebmailassinaturaobjeto,
											htmlassinatura: aux.html().replaceAll("'","&#39"),
											pessoas: jPessoas
										},
										posPost: function(data,texto,jqXHR){
											let resp = JSON.parse(data);
											if(resp["erro"]){
												alert(resp["erro"])
											}
										}
									});
								}catch(error) {
									console.error('oops, something went wrong!', error);
									$("#_temp").remove();
								};
							}, 500);
						} else {
							console.error('Verifique assinatura e template do email.');
						}
				},

				error: function(objxmlreq){
					alertErro('Erro:<br>'+objxmlreq.status);
				}
			});
		}	
	});

	function criarAssinaturaCampos(){
		CB.post({
			objetos: {
				"_Ncampos_i_assinaturaemailcampos_tipoobjeto" : 'EMAILVIRTUAL',
				"_Ncampos_i_assinaturaemailcampos_idobjeto" : $(":input[name=_1_"+CB.acao+"_emailvirtualconf_idemailvirtualconf]").val()
			}
			,parcial:true
		});
	}
}

// GVT - 19/05/2020 - Função para desvincular a pessoa do email virtual
function retirar(inidemailvirtualconfpessoa){
	CB.post({
		objetos: "_x_d_emailvirtualconfpessoa_idemailvirtualconfpessoa="+inidemailvirtualconfpessoa
        ,parcial: true
		,msgSalvo: false
	});
}

// GVT - 19/05/2020 - Função para desvincular o grupo do email virtual
function retirargrupo(inidemailvirtualconfimgrupo){
	CB.post({
		objetos: "_x_d_emailvirtualconfimgrupo_idemailvirtualconfimgrupo="+inidemailvirtualconfimgrupo
        ,parcial: true
		,msgSalvo: false
	});
}

CB.on('posPost', function(obj){
	if(obj.inParam.parcial && obj.inParam.msgSalvo === false){
		alertAzul("Atualizando destinatários do grupo de e-mail","Aguarde:")
		CB.post();
	}
});
</script>
<!-- script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"></script -->
<script src="inc/js/dom-to-image/dom-to-image.min.js"></script>