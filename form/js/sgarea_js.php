<script>

var idModulo = <?= $_1_u_sgarea_idsgarea ?? 'false' ?>;
var acao = '<?= $_acao ?>';
var statusSgarea = <?= "'$_1_u_sgarea_status'" ?? 'false' ?>;
var pessoasDisponiveisParaVinculo = <?= json_encode($pessoasDisponiveisParaVinculo ?? []) ?>;
var departamentosDisponiveisParaVinculo = <?= json_encode($departamentosDisponiveisParaVinculo ?? []) ?>;
var lpsDisponiveisParaVinculo = <?= json_encode($lpsDisponiveisParaVinculo ?? []) ?>;
var unidadesDisponiveisParaVinculo = <?= json_encode($unidadesDisponiveisParaVinculo ?? []) ?>;
var possuiVinculoComUnidade = '<?= $possuiVinculoComUnidade ?>';
var gruposDisponiveisParaVinculo = <?= json_encode($gruposDisponiveisParaVinculo); ?>;
var unidadesDisponiveisParaVinculoEnvio = <?= JSON_ENCODE($_1_u_sgarea_idsgarea ? $unidadesDisponiveisParaVinculoEnvio : []) ?>;

function areaPessoa(){ 
    CB.modal({
            titulo: "</strong>Histórico de Gerente de Área</strong>",
            corpo: $("#historico").html(),
            classe: 'sessenta',
    });
		
}
function desvincularPessoa(inid){
	CB.post({
	    objetos: "_x_d_pessoaobjeto_idpessoaobjeto="+inid
	    ,parcial:true
	});
}

function desvincularArea(inid){
	CB.post({
	    objetos: "_x_d_objetovinculo_idobjetovinculo="+inid
	    ,parcial:true
		
	});
}

function desvincularPessoaArea(inid){
	CB.post({
	    objetos: "_x_d_pessoaobjeto_idpessoaobjeto="+inid
	    ,parcial:true,	
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

function desvincularAreaDepartamento(inid){
	CB.post({
	    objetos: "_x_d_objetovinculo_idobjetovinculo="+inid
	    ,parcial:true,	posPost: function(resp, status, ajax) {
					if (status = "success") {

						$("#cbModalCorpo").html("");
						$('#cbModal').modal('hide');
						
					} else {
						alert(resp);
					}
				}		
		
	});
}

//Altera se será grupo ou não (Lidiane - 13-03-2020)
function altcheck(vtab,vcampo,vid,vcheck){
    CB.post({
        objetos: "_x_u_"+vtab+"_id"+vtab+"="+vid+"&_x_u_"+vtab+"_"+vcampo+"="+vcheck        
    }); 
}

//Deletar Lp da Área (Lidiane - 13-03-2020)
function desvincularLp(inid){
	CB.post({
	    objetos: "_x_d_lpobjeto_idlpobjeto="+inid
	    ,parcial:true,
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

function desvincularUnidade(inid) {
	CB.post({
		objetos: "_x_d_unidadeobjeto_idunidadeobjeto=" + inid,
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


function inativavinculo(inid){    		
	CB.post({
		objetos: "_x_d_objetovinculo_idobjetovinculo="+inid
		,parcial:true
	});    
	
}

//Autocomplete de Setores vinculados
$("#pessoaobjeto").autocomplete({
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
				"_x_i_pessoaobjeto_idobjeto": $(":input[name=_1_"+CB.acao+"_sgarea_idsgarea]").val()
			   ,"_x_i_pessoaobjeto_idpessoa": ui.item.idpessoa
			   ,"_x_i_pessoaobjeto_tipoobjeto": 'sgarea'
			   ,"_x_i_pessoaobjeto_responsavel": 'Y'
			}
			,parcial: false
		});
	}
});




function AlteraStatus(vthis)
{
	var id;
	id = $(vthis).attr('idsgareasetor');
	status = $(vthis).attr('status');

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
					
					//removeClass("vermelho hoververmelho").addClass("verde hoververde");
                    
                   AtualizaBim();
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
					
					//removeClass("vermelho hoververmelho").addClass("verde hoververde");
                     
                   AtualizaBim();
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
					//removeClass("vermelho hoververmelho").addClass("verde hoververde");
				
					AtualizaBim();
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
				
					AtualizaBim();
				}
			});
		}
    }
}

//Inserido em 17-03-2020 - Lidiane
function AtualizaBim(){
	$.ajax({
		type: "get",
		url : "cron/bim.php",
		success: function(data){
			//alert('OK');
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
	objetos: "_x_i_"+inobj+"_idsgarea="+$("[name=_1_u_sgarea_idsgarea]").val()
	,parcial:true
	,posPost: function(){
		   AtualizaBim();
		}
    });
   
}


function inativaobjeto(inid,inobj){    		
	CB.post({
	    objetos: "_x_d_sgareasetor_idsgareasetor="+inid
	    ,parcial:true
		,posPost: function(){
		   AtualizaBim();
		}
		
	});    
	
	AtualizaBim();
}

CB.prePost = function()
{
	$('#unidade-area').removeClass('alertaCbvalidacao');
	if(!$('#unidade-area').val() 
		&& possuiVinculoComUnidade != '1' 
		&& $('#status').val() == 'ATIVO' 
		&& statusSgarea == 'ATIVO'
		&& $('.coordenador-vinculado tr').get().length)	
	{
		$('#unidade-area').addClass('alertaCbvalidacao');
		alertAtencao('É necessário associar uma unidade para o responsável desta Área!');
		return false;
	}
}


function novalp() {

	debugger;

	var strCabecalho = "</strong>Adicionar LP</strong>";
	var htmloriginal = $("#novalp").html();
	var objfrm = $(htmloriginal);

	objfrm.find(".lparea").attr("name", "_x_idlp");


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
	source: lpsDisponiveisParaVinculo
	,delay: 0
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {

			lbItem = item.lp;
			
			return $('<li>')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
		};
	}
	,select: function(event, ui){
		CB.post({
			objetos: {
				"_x_i_lpobjeto_idobjeto": $(":input[name=_1_"+CB.acao+"_sgarea_idsgarea]").val()
			   ,"_x_i_lpobjeto_idlp": ui.item.idlp
			   ,"_x_i_lpobjeto_tipoobjeto": 'sgarea'
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


function novogrupo(){

debugger;

var strCabecalho = "</strong>Adicionar Grupo Vinculado</strong>";
var htmloriginal = $("#novogrupo").html();
var objfrm = $(htmloriginal);

objfrm.find(".vincular-grupo").attr("name", "_x_idimgrupo");

//criaAutocompletesUn();
CB.modal({
	titulo: strCabecalho,
	corpo: [objfrm],
	aoAbrir : function(){

		criaAutocompletesGrupo();
	
	}
});

}

// Inserir grupo
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
				,"_x_i_objetovinculo_tipoobjetovinc": "sgarea"
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



function novodepartamento() {

debugger;

var strCabecalho = "</strong>Adicionar departamento</strong>";
var htmloriginal = $("#novodepartamento").html();
var objfrm = $(htmloriginal);

objfrm.find(".areadepartamento").attr("name", "_x_idsgdepartamento");


//criaAutocompletesUn();
CB.modal({
	titulo: strCabecalho,
	corpo: [objfrm],
	aoAbrir : function(){

		criaAutocompletesDepartamento();
	
	}
});


}


//Acrescentado para inserir a Pessoa responsável pela àrea (Lidiane - 04-03-2020)
//Autocomplete de Setores vinculados
function criaAutocompletesDepartamento() {

$(":input[name=_x_idsgdepartamento]").autocomplete({			
	source: departamentosDisponiveisParaVinculo,
	delay: 0,
	create: function() {
		$(this).data('ui-autocomplete')._renderItem = function(ul, item) {

			lbItem = item.departamento;

			return $('<li>')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
		};
	},
	select: function(event, ui) {
		CB.post({
			objetos: {
				"_x_i_objetovinculo_idobjeto": $(":input[name=_1_"+CB.acao+"_sgarea_idsgarea]").val() 
			   ,"_x_i_objetovinculo_tipoobjeto": 'sgarea'
			   ,"_x_i_objetovinculo_idobjetovinc": ui.item.idsgdepartamento
			   ,"_x_i_objetovinculo_tipoobjetovinc": 'sgdepartamento'
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



function unidadepadrao() {

debugger;

var strCabecalho = "</strong>Adicionar unidades solicitadoras de custo</strong>";
var htmloriginal = $("#unidadepadrao").html();
var objfrm = $(htmloriginal);

objfrm.find(".unidade-area").attr("name", "_x_idunidadepd");


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

if(statusSgarea == 'ATIVO') {
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
					"_x_i_unidadeobjeto_tipoobjeto": `sgarea`,
					"_x_i_unidadeobjeto_padrao": `Y`
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

objfrm.find(".inidunidade").attr("name", "_x_idunidadepd");


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

if(statusSgarea == 'ATIVO') {
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
					"_x_i_unidadeobjeto_tipoobjeto": `sgarea`,
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

if(statusSgarea == 'ATIVO') {
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