<script type="text/Javascript">
	var lpsSetor = <?= json_encode($lpsSetor ?? []) ?>;
	var pessoasDisponiveisParaVinculo = <?= json_encode($pessoasDisponiveisParaVinculo ?? []) ?>;
	var gruposDisponiveisParaVinculo = <?= json_encode($gruposDisponiveisParaVinculo ?? []) ?>; 
	var unidadesDisponiveisParaVinculo = <?= json_encode($unidadesDisponiveisParaVinculo ?? []) ?>;
	var statusDoSetor = <?= $_1_u_sgsetor_status ? "'$_1_u_sgsetor_status'" : 'false' ?>;
	var idModulo = <?= $_1_u_sgsetor_idsgsetor ?? 'false' ?>;

	var unidadesDisponiveisParaVinculoEnvio = <?= JSON_ENCODE($_1_u_sgsetor_idsgsetor ? $unidadesDisponiveisParaVinculoEnvio : []) ?>;

	function setorPessoa()
	{
		CB.modal({
			titulo: "<strong>Histórico de Pessoas do Setor</strong>",
			corpo: $("#historico").html(),
			classe: 'sessenta',
		});
			
	}

	$("#coordenadorsetor").autocomplete({
		source: pessoasDisponiveisParaVinculo
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {

				lbItem = item.nome;
				condItem= item.responsavel;
				if(condItem = 'N'){
					return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
				}
			};
		}
		,select: function(event, ui){
			CB.post({
				objetos: {
					"_x_i_pessoaobjeto_idobjeto":		$(":input[name=_1_"+CB.acao+"_sgsetor_idsgsetor]").val()
				,"_x_i_pessoaobjeto_idpessoa":	ui.item.idpessoa
				,"_x_i_pessoaobjeto_tipoobjeto":	'sgsetor'
				,"_x_i_pessoaobjeto_responsavel": 'Y'
				}
				,parcial: true
			});
		}
	});	


	//Deletar LP do Setor(Lidiane - 13-03-2020)
	function desvincularLp(inid){
		//debugger;
		CB.post({
			objetos: "_x_d_lpobjeto_idlpobjeto="+inid
			,parcial:true
		});    	
	}

	

	function desvincularUnidade(inid) {
		CB.post({
			objetos: "_x_d_unidadeobjeto_idunidadeobjeto=" + inid,
			parcial: true,
		});
	}



	function inativavinculo(inid){    		
		CB.post({
			objetos: "_x_d_objetovinculo_idobjetovinculo="+inid
			,parcial:true
		});    
		
	}

	function AlteraStatus(vthis){
		
		//alert($(vthis).attr('idsgareasetor'));
		var id;
		id = $(vthis).attr('idsgareasetor');
		status = $(vthis).attr('status');
		//alert(status);
		var novostatus, cor, novacor;
		if (status == 'ATIVO'){
			novostatus = 'INATIVO';
			cor = 'verde hoververde';
			novacor = 'vermelho hoververmelho';
			
			
		}else{
			novostatus = 'ATIVO';	
			cor = 'vermelho hoververmelho';
			novacor = 'verde hoververde';
		}
		CB.post({
					objetos: "_x_u_sgareasetor_idsgareasetor="+id+"&_x_u_sgareasetor_status="+novostatus
					,parcial:true
					,refresh: false
					,msgSalvo: "Status Alterado"
					,posPost: function(){
						$(vthis).removeClass(cor);
						$(vthis).addClass(novacor);
						$(vthis).attr('status', novostatus);
						$(vthis).attr('title', novostatus);
						//removeClass("vermelho hoververmelho").addClass("verde hoververde");
					}
				});
		
	}


	function AlteraComunicacao(vthis){
		var id;
		var idempresa	    = <?=idempresa();?>;
		var tipoobjeto	    = 'imgrupo';
		var idobjetoorigem  = $(vthis).attr('idobjetoorigem');
		var idobjetodestino = $(vthis).attr('idobjetodestino');
		var tiporegra	    = $(vthis).attr('tiporegra');
		idimregra	    	= $(vthis).attr('idimregra');
		var status 			= $(vthis).attr('status');
		var novostatus, cor, novacor;

		if (idimregra){
			if (status == 'ATIVO'){
			cor = 'verde hoververde';
			novacor = 'vermelho hoververmelho';
			CB.post({
					objetos: "_x_u_imregra_idimregra="+idimregra+"&_x_u_imregra_status=INATIVO"
					,parcial:true
					,msgSalvo: "Status Alterado"
					,posPost: function(){
						$(vthis).removeClass(cor);
						$(vthis).addClass(novacor);
					} 
				});
				
			}else{

			cor = 'vermelho hoververmelho';
			novacor = 'verde hoververde';
			CB.post({
					objetos: "_x_u_imregra_idimregra="+idimregra+"&_x_u_imregra_status=ATIVO"
					,parcial:true
					,msgSalvo: "Status Alterado"
					,posPost: function(){
						$(vthis).removeClass(cor);
						$(vthis).addClass(novacor);
					} 
				});
			}
			
			
		}else{
			cor = 'vermelho hoververmelho';
			novacor = 'verde hoververde';
			
			
			if (tiporegra == 'GRUPO'){
				CB.post({
					objetos: "_x_i_imregra_idempresa="+idempresa+"&_x_i_imregra_tipoobjetoorigem="+tipoobjeto+"&_x_i_imregra_idobjetoorigem="+idobjetoorigem+"&_x_i_imregra_tipoobjetodestino="+tipoobjeto+"&_x_i_imregra_idobjetodestino="+idobjetodestino+"&_x_i_imregra_tiporegra="+tiporegra+"&_x_i_imregra_status=ATIVO"
					,parcial:true
					,refresh:true
					,msgSalvo: "Salvo"
					,posPost: function(){
						$(vthis).removeClass(cor);
						$(vthis).addClass(novacor);
						$(vthis).attr('status', novostatus);
						$(vthis).attr('title', novostatus);
					}
				});
			}else{
				CB.post({
					objetos: "_x_i_imregra_idempresa="+idempresa+"&_x_i_imregra_tipoobjetoorigem="+tipoobjeto+"&_x_i_imregra_idobjetoorigem="+idobjetoorigem+"&_x_i_imregra_tipoobjetodestino="+tipoobjeto+"&_x_i_imregra_idobjetodestino="+idobjetodestino+"&_x_i_imregra_tiporegra="+'MENSAGEMDIRETA'+"&_x_i_imregra_status=ATIVO"+"&_y_i_imregra_idempresa="+idempresa+"&_y_i_imregra_tipoobjetoorigem="+tipoobjeto+"&_y_i_imregra_idobjetoorigem="+idobjetoorigem+"&_y_i_imregra_tipoobjetodestino="+tipoobjeto+"&_y_i_imregra_idobjetodestino="+idobjetodestino+"&_y_i_imregra_tiporegra="+'GRUPO'+"&_y_i_imregra_status=ATIVO"+"&_z_i_imregra_idempresa="+idempresa+"&_z_i_imregra_tipoobjetoorigem="+tipoobjeto+"&_z_i_imregra_idobjetoorigem="+idobjetoorigem+"&_z_i_imregra_tipoobjetodestino="+tipoobjeto+"&_z_i_imregra_idobjetodestino="+idobjetodestino+"&_z_i_imregra_tiporegra="+'GRUPOGRUPO'+"&_z_i_imregra_status=ATIVO"
					,parcial:true
					,refresh:true
					,msgSalvo: "Salvo"
					,posPost: function(){
						$(vthis).removeClass(cor);
						$(vthis).addClass(novacor);
						$(vthis).attr('status', novostatus);
						$(vthis).attr('title', novostatus);
					}
				});
			} 
		}
	}


	function AtualizaBim(){
		$.ajax({
                type: "get",
                url : "cron/bim.php",
    
                success: function(data){
                    if(data.error)
                    {
                        return alertAtencao(data.error);
                    }

                    alertSalvo('Bim Atualizado');
					setTimeout(() => document.location.reload(true), 600);
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


	function novoobjeto(inobj){
		CB.post({
            objetos: "_x_i_"+inobj+"_idsgsetor="+$("[name=_1_u_sgsetor_idsgsetor]").val()
            ,parcial:true
		});
	}

	function altcheck(vtab,vcampo,vid,vcheck){
		CB.post({
			objetos: "_x_u_"+vtab+"_id"+vtab+"="+vid+"&_x_u_"+vtab+"_"+vcampo+"="+vcheck        
		})
	}

	function inativaobjeto(inid,inobj){    		
		CB.post({
			objetos: {
                "_x_d_pessoaobjeto_idpessoaobjeto":inid
            }
            ,parcial: true
		});    
	} 

	function desvincularCoordenadorSetor(inid){
		CB.post({
			objetos: "_x_d_pessoaobjeto_idpessoaobjeto="+inid
			,parcial:true
		});    	
	}

	CB.prePost = function()
	{
		$('#unidade-setor').removeClass('alertaCbvalidacao');
		if(!$('#unidade-setor').val() && !$('#unidades-vinculadas').children().get().length && $('#status').val() == 'ATIVO' && statusDoSetor == 'ATIVO')	
		{
			$('#unidade-setor').addClass('alertaCbvalidacao');
			alertAtencao('Campo unidade obrigatorio!');
			return false;
		}
	}

	function novalp() {

		debugger;

		var strCabecalho = "</strong>Adicionar LP</strong>";
		var htmloriginal = $("#novalp").html();
		var objfrm = $(htmloriginal);

		objfrm.find(".lpsetor").attr("name", "_x_idlp");


		//criaAutocompletesUn();
		CB.modal({
			titulo: strCabecalho,
			corpo: [objfrm],
			aoAbrir : function(){

				criaAutocompletesLP();
			
			}
		});


	}

	//Autocomplete de Setores vinculados
	function criaAutocompletesLP() {

		$(":input[name=_x_idlp]").autocomplete({
		source: lpsSetor
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {

				lbItem = item.descricao;
				
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		}
		,select: function(event, ui){
			CB.post({
				objetos: {
					"_x_i_lpobjeto_idobjeto": $(":input[name=_1_"+CB.acao+"_sgsetor_idsgsetor]").val()
				,"_x_i_lpobjeto_idlp": ui.item.idlp
				,"_x_i_lpobjeto_tipoobjeto": 'sgsetor'
				}
				,parcial: false,
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
	});
	}

	function novogrupo() {

		debugger;

		var strCabecalho = "</strong>Adicionar Grupos</strong>";
		var htmloriginal = $("#novogrupo").html();
		var objfrm = $(htmloriginal);

		objfrm.find(".objetovinculo").attr("name", "_x_idimgrupo");

		//criaAutocompletesUn();
		CB.modal({
			titulo: strCabecalho,
			corpo: [objfrm],
			aoAbrir : function(){

				criaAutocompletesGrupo();
			
			}
		});
	}

//Autocomplete de Setores vinculados
function criaAutocompletesGrupo() {

$(":input[name=_x_idimgrupo]").autocomplete({
		source: gruposDisponiveisParaVinculo
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {

				lbItem = item.grupo;
				
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul); 
			};
		}
		,select: function(event, ui){
			CB.post({
				objetos: {
					"_x_i_objetovinculo_idobjeto": ui.item.idimgrupo
					,"_x_i_objetovinculo_tipoobjeto": "imgrupo"
					,"_x_i_objetovinculo_idobjetovinc" : idModulo
					,"_x_i_objetovinculo_tipoobjetovinc": "sgsetor"
				}
				,parcial: true,
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
	});
}



function novopessoa() {

	debugger;

	var strCabecalho = "</strong>Adicionar Grupos</strong>";
	var htmloriginal = $("#novopessoa").html();
	var objfrm = $(htmloriginal);

	objfrm.find(".sgsetorvinc").attr("name", "_x_idsgsetor");

	//criaAutocompletesUn();
	CB.modal({
		titulo: strCabecalho,
		corpo: [objfrm],
		aoAbrir : function(){

			criaAutocompletesNovaPessoa();
		
		}
	});
}

//Autocomplete de Setores vinculados
function criaAutocompletesNovaPessoa() {

	$(":input[name=_x_idsgsetor]").autocomplete({
		source: pessoasDisponiveisParaVinculo
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {

				lbItem = item.nome;
				condItem= item.responsavel;
				if(condItem = 'N'){
					return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
				}
			};
		}
		,select: function(event, ui){
			CB.post({
				objetos: {
					"_x_i_pessoaobjeto_idobjeto":		$(":input[name=_1_"+CB.acao+"_sgsetor_idsgsetor]").val()
				,"_x_i_pessoaobjeto_idpessoa":	ui.item.idpessoa
				,"_x_i_pessoaobjeto_tipoobjeto":	'sgsetor'
				
				
				}
				,parcial: true,
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
	});
}



function unidadepadrao() {

debugger;

var strCabecalho = "</strong>Adicionar unidades solicitadoras de custo</strong>";
var htmloriginal = $("#unidadepadrao").html();
var objfrm = $(htmloriginal);

objfrm.find(".unidade-setor").attr("name", "_x_idunidadepd");


//criaAutocompletesUn();
CB.modal({
	titulo: strCabecalho,
	corpo: [objfrm],
	aoAbrir : function(){
	
			criaAutocompletesUnPd();
		
	}
});


}

function criaAutocompletesUnPd() {
		if(statusDoSetor == 'ATIVO'){

		console.log(unidadesDisponiveisParaVinculo);
		$(":input[name=_x_idunidadepd]").autocomplete({
			source: unidadesDisponiveisParaVinculo,
			delay: 0,
			create: function() {
				$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
					lbItem = item.unidade;
					return $('<li>')
						.append('<a>' + lbItem + '</a>')
						.appendTo(ul);
				};
			},
			select: function(event, ui) {
				CB.post({
					objetos: {
						"_x_i_unidadeobjeto_idempresa": ui.item.idempresa,
						"_x_i_unidadeobjeto_idunidade": ui.item.idunidade,
						"_x_i_unidadeobjeto_idobjeto": idModulo,
						"_x_i_unidadeobjeto_tipoobjeto": `sgsetor`
					},
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
		});
	}
}

	
function novaunidade() {

debugger;

var strCabecalho = "</strong>Adicionar unidades solicitadoras de custo</strong>";
var htmloriginal = $("#novaunidade").html();
var objfrm = $(htmloriginal);

objfrm.find(".inidunidade").attr("name", "_x_idunidade");


//criaAutocompletesUn();
CB.modal({
	titulo: strCabecalho,
	corpo: [objfrm],
	aoAbrir : function(){
	
			criaAutocompletesUn();
		
	}
});


}

function criaAutocompletesUn() {

if(statusDoSetor == 'ATIVO') {
	console.log(unidadesDisponiveisParaVinculo);
	$(":input[name=_x_idunidade]").autocomplete({
		source: unidadesDisponiveisParaVinculo,
		delay: 0,
		create: function() {
			$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
				lbItem = item.unidade;
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		},
		select: function(event, ui) {
			CB.post({
				objetos: {
					"_x_i_unidadeobjeto_idempresa": ui.item.idempresa,
					"_x_i_unidadeobjeto_idunidade": ui.item.idunidade,
					"_x_i_unidadeobjeto_idobjeto": idModulo,
					"_x_i_unidadeobjeto_tipoobjeto": `sgsetor`,
					"_x_i_unidadeobjeto_padrao": `N`
				},
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
	});
}

}




function enviocusto() {

debugger;

var strCabecalho = "</strong>Adicionar unidades envio de custo</strong>";
var htmloriginal = $("#enviocusto").html();
var objfrm = $(htmloriginal);

objfrm.find(".unidadesenviocusto").attr("name", "_x_idunidadeev");


//criaAutocompletesUn();
CB.modal({
	titulo: strCabecalho,
	corpo: [objfrm],
	aoAbrir : function(){
	
		criaAutocompletesUnEv();
		
	}
});


}

function criaAutocompletesUnEv() {

if(statusDoSetor == 'ATIVO') {
console.log(unidadesDisponiveisParaVinculoEnvio);
$(":input[name=_x_idunidadeev]").autocomplete({
	source: unidadesDisponiveisParaVinculoEnvio,
	delay: 0,
	create: function() {
		$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
			lbItem = item.unidade;
			return $('<li>')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
		};
	},
	select: function(event, ui ) {
		CB.post({
			objetos: {
				"_x_i_unidaderateio_idempresa": ui.item.idempresa,
				"_x_i_unidaderateio_idunidade": $("#valorunidadepadrao").val(),
				"_x_i_unidaderateio_idobjeto": ui.item.idunidade,
				"_x_i_unidaderateio_tipoobjeto": `unidadepadrao`
			},
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
});
}

}

function desvincularUnidadeRateio(inid) {
		CB.post({
			objetos: {
				"_x_d_unidaderateio_idunidaderateio": inid
			},
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

function atualizarateio(vthis,inidunidaderateio){
	debugger;


	// Get the tbody element
	const tbody = document.getElementById('trunidadesenviocusto');

	// Get all input elements within the tbody with name ending in 'rateio'
	const inputs = tbody.querySelectorAll('.valorrateio');

	// Initialize the sum variable
	let sum = 0;

	// Loop through the inputs and sum their values
	inputs.forEach(input => {
		sum += parseFloat(input.value) || 0; // Ensure value is treated as a number and handle NaN
	});

	// You can use the sum for any purpose you need
	console.log('Total sum of rateio:', sum);

	// Example: Display the sum in an alert or update the DOM
	///alert('Total sum of rateio: ' + sum);

	if(sum<=100){
		CB.post({
				objetos: {
					"_x_u_unidaderateio_idunidaderateio": inidunidaderateio,
					"_x_u_unidaderateio_rateio": $(vthis).val()
				},
				parcial: false
			});

	}else{
		alert("A Soma dos percentuais dos envios de custo não pode ultrapassar 100 %");
		$(vthis).val('');
	}

}

document.querySelectorAll('#unidades-vinculadas input[name$="rateio"]').forEach(input => {
input.addEventListener('change', function() {
	atualizarateio(this);
});
});



	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>