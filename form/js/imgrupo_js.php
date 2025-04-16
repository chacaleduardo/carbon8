<script>
var emaisVirtuaisDisponiveisParaVinculo = <?= json_encode($emaisVirtuaisDisponiveisParaVinculo) ?>;
var pessoasDisponiveisParaVinculo = <?= json_encode($pessoasDisponiveisParaVinculo) ?>;
var gruposDeAreasDepsSetoresDisponiveisParaVinculo = <?= json_encode($gruposDeAreasDepsSetoresDisponiveisParaVinculo) ?>;
var fluxosLiberadosCriarEvento = <?= json_encode($fluxosLiberadosCriarEvento) ?>;

//Autocomplete de Emails vinculados
$("#emailvinc").autocomplete({
    source: emaisVirtuaisDisponiveisParaVinculo
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {

            lbItem = item.email_original;

            return $('<li>')
                    .append('<a>' + lbItem + '</a>')
                    .appendTo(ul);
        };
    }
    ,select: function(event, ui){
        CB.post({
            objetos: {
                "_x_i_emailvirtualconfimgrupo_idimgrupo": $(":input[name=_1_"+CB.acao+"_imgrupo_idimgrupo]").val()
               ,"_x_i_emailvirtualconfimgrupo_idemailvirtualconf":ui.item.idemailvirtualconf
            }
            ,parcial: true
        });
    }	
});  

//Autocomplete de funcionarios vinculados
$("#imgrupovinc").autocomplete({
	source: pessoasDisponiveisParaVinculo
	,delay: 0
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {

			lbItem = item.nomecurto;
			
			return $('<li>')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
		};
	}
	,select: function(event, ui){
		CB.post({
			objetos: {
				"_x_i_imgrupopessoa_idimgrupo": $(":input[name=_1_"+CB.acao+"_imgrupo_idimgrupo]").val()
			   ,"_x_i_imgrupopessoa_idpessoa": ui.item.idpessoa
			   ,"_x_i_imgrupopessoa_inseridomanualmente":	'Y'
			}
			,parcial: true
		});
		 // AtualizaBim();
	}
});	

$("#objetovinculo").autocomplete({
	source: gruposDeAreasDepsSetoresDisponiveisParaVinculo
	,delay: 0
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {

			lbItem = item.name;
			
			return $('<li>')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
		};
	}
	,select: function(event, ui){
		CB.post({
			objetos: {
				 "_x_i_objetovinculo_idobjeto": $(":input[name=_1_"+CB.acao+"_imgrupo_idimgrupo]").val()
				,"_x_i_objetovinculo_tipoobjeto": "imgrupo"
				,"_x_i_objetovinculo_idobjetovinc" : ui.item.id
				,"_x_i_objetovinculo_tipoobjetovinc": ui.item.tipo
			}
			,parcial: true
		});
	}
});	

$("#fluxovinculados").autocomplete({
	source: fluxosLiberadosCriarEvento
	,delay: 0
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {

			lbItem = item.tipoobjeto;
			
			return $('<li>')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
		};
	}
	,select: function(event, ui){
		CB.post({
			objetos: {
				 "_x_i_fluxoobjeto_idfluxo": ui.item.idfluxo
				,"_x_i_fluxoobjeto_idobjeto": $(":input[name=_1_"+CB.acao+"_imgrupo_idimgrupo]").val()
				,"_x_i_fluxoobjeto_tipoobjeto": 'imgrupo'
				,"_x_i_fluxoobjeto_tipo": 'CRIADOR'
			}
			,parcial: true
		});
	}
});	

function inativavinculo(inid){    		
	CB.post({
	    objetos: "_x_d_objetovinculo_idobjetovinculo="+inid
	    ,parcial:true
	});
}

function AtualizaBim()
{
	 $.ajax({
			type: "get",
			url : "cron/bim.php",

			success: function(data){
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
function novoobjeto(inobj){
    CB.post({
		objetos: "_x_i_"+inobj+"_idimgrupo="+$("[name=_1_u_imgrupo_idimgrupo]").val()
		,parcial:true
    });
   
}

// GVT - 19/05/2020 - Função para desvincular o grupo do emailvirtualconf
function desvincularEmail(inid){
    CB.post({
        objetos: "_x_d_emailvirtualconfimgrupo_idemailvirtualconfimgrupo="+inid
        ,parcial:true
    });      
}

function inativaobjeto(inid,inobj){    		
	CB.post({
	    objetos: "_x_d_imgrupopessoa_idimgrupopessoa="+inid
	    ,parcial:true
	});    
	
	//AtualizaBim();
}

// GVT - 19/05/2020 - Função para abrir modal do cadastro de um novo email virtual
function novoemail(){
	CB.modal({
		url:"?_modulo=emailvirtualconf&_acao=i&_modo=form"
		,header:"Email Virtual"
	});
}

function excluirFluxo(inid){
	CB.post({
	    objetos: "_x_d_fluxoobjeto_idfluxoobjeto="+inid
	    ,parcial:true
	});  
}

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>