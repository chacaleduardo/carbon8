<script type="text/Javascript">
    var statusSgconselho = <?= "'$_1_u_sgconselho_status'" ?? 'false' ?>;
    var idModulo = <?= $_1_u_sgconselho_idsgconselho ?? 'false' ?>;
    var pessoasDisponiveisParaVinculo = <?= json_encode($pessoasDisponiveisParaVinculo) ?>;
    var lpsDisponiveisParaVinculo = <?= json_encode($lpsDisponiveisParaVinculo) ?>;
    var unidadesDisponiveisParaVinculo = <?= json_encode($unidadesDisponiveisParaVinculo) ?>;
    var areasDisponiveisParaVinculo = <?= json_encode($areasDisponiveisParaVinculo) ?>;
    var possuiVinculoComUnidade = '<?= $possuiVinculoComUnidade ?>';
    var gruposDisponiveisParaVinculo = <?= json_encode($gruposDisponiveisParaVinculo); ?>;
    var unidadesDisponiveisParaVinculoEnvio = <?= JSON_ENCODE($_1_u_sgconselho_idsgconselho ? $unidadesDisponiveisParaVinculoEnvio : []) ?>;
    (function()
    {
        $('#btn-historico').on('click', conselhoPessoa);
    })();

    function conselhoPessoa()
    {
        CB.modal({
                titulo: "</strong>Histórico de Conselheiro</strong>",
                corpo: $("#historico").html(),
                classe: 'sessenta',
        });
            
    }

    function desvincularArea(inid)
    {
        CB.post({
            objetos: "_x_d_objetovinculo_idobjetovinculo="+inid
            ,parcial:true
            ,posPost: function(resp, status, ajax) {
                if (status = "success") {

                    $("#cbModalCorpo").html("");
                    $('#cbModal').modal('hide');
                    
                } else {
                    alert(resp);
                }
            }
        });
    }

    function desvincularPessoaArea(inid){
        //debugger;
        CB.post({
            objetos: "_x_d_pessoaobjeto_idpessoaobjeto="+inid
            ,parcial:true
            ,posPost: function(resp, status, ajax) {
                if (status = "success") {

                    $("#cbModalCorpo").html("");
                    $('#cbModal').modal('hide');
                    
                } else {
                    alert(resp);
                }
            }
            
        });    	
        //AtualizaBim();
    }

    //Altera se será grupo ou não (Lidiane - 13-03-2020)
    function altcheck(vtab,vcampo,vid,vcheck){
        CB.post({
            objetos: "_x_u_"+vtab+"_id"+vtab+"="+vid+"&_x_u_"+vtab+"_"+vcampo+"="+vcheck        
        }); 
    }

    //Deletar Lp da Área (Lidiane - 13-03-2020)
    function desvincularLp(inid){
        //debugger;
        CB.post({
            objetos: "_x_d_lpobjeto_idlpobjeto="+inid
            ,parcial:true
            ,posPost: function(resp, status, ajax) {
                if (status = "success") {

                    $("#cbModalCorpo").html("");
                    $('#cbModal').modal('hide');
                    
                } else {
                    alert(resp);
                }
            }	
            
        });    	
        //AtualizaBim();
    }

    function desvincularUnidade(inid) {
        CB.post({
            objetos: "_x_d_unidadeobjeto_idunidadeobjeto=" + inid,
            parcial: true
            ,posPost: function(resp, status, ajax) {
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
			,parcial:true ,posPost: function(resp, status, ajax) {
                if (status = "success") {

                    $("#cbModalCorpo").html("");
                    $('#cbModal').modal('hide');
                    
                } else {
                    alert(resp);
                }
            }
		});    	
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

    CB.prePost = function()
    {
        $('#unidade-conselho').removeClass('alertaCbvalidacao');
        if(!$('#unidade-conselho').val() 
            && possuiVinculoComUnidade != '1' 
            && $('#status').val() == 'ATIVO' 
            && statusSgconselho == 'ATIVO'
            && $('.conselheiro-vinculado tr').get().length)	
        {
            $('#unidade-conselho').addClass('alertaCbvalidacao');
            alertAtencao('É necessário associar uma unidade para o responsável deste Conselho!');
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
        if(statusSgconselho == 'ATIVO'){

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
                        "_x_i_lpobjeto_idobjeto": $(":input[name=_1_"+CB.acao+"_sgconselho_idsgconselho]").val()
                        ,"_x_i_lpobjeto_idlp": ui.item.idlp
                        ,"_x_i_lpobjeto_tipoobjeto": 'sgconselho'
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
        if(statusSgconselho == 'ATIVO'){
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
                            ,"_x_i_objetovinculo_tipoobjetovinc": "sgconselho"
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
    }

    
function novaarea() {

debugger;

var strCabecalho = "</strong>Adicionar área</strong>";
var htmloriginal = $("#novaarea").html();
var objfrm = $(htmloriginal);

objfrm.find(".conselho-area").attr("name", "_x_idsgarea");


//criaAutocompletesUn();
CB.modal({
	titulo: strCabecalho,
	corpo: [objfrm],
	aoAbrir : function(){

		criaAutocompletesArea();
	
	}
});


}


//Acrescentado para inserir a Pessoa responsável pela àrea (Lidiane - 04-03-2020)
//Autocomplete de Setores vinculados
function criaAutocompletesArea() {
    if(statusSgconselho == 'ATIVO'){

        $(":input[name=_x_idsgarea]").autocomplete({			
            source: areasDisponiveisParaVinculo,
            delay: 0,
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {

                    lbItem = item.area;

                    return $('<li>')
                        .append('<a>' + lbItem + '</a>')
                        .appendTo(ul);
                };
            },
            select: function(event, ui) {
                CB.post({
                    objetos: {
                                "_x_i_objetovinculo_idobjeto": idModulo,
                                "_x_i_objetovinculo_tipoobjeto": 'sgconselho',
                                "_x_i_objetovinculo_idobjetovinc": ui.item.idsgarea,
                                "_x_i_objetovinculo_tipoobjetovinc": 'sgarea',
                                // Atualiza conselho na area
                                "_x_u_sgarea_idsgarea": ui.item.idsgarea,
                                "_x_u_sgarea_idsgconselho": idModulo
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

function novapessoa() {

    debugger;

    var strCabecalho = "</strong>Adicionar conselheiro</strong>";
    var htmloriginal = $("#novapessoa").html();
    var objfrm = $(htmloriginal);

    objfrm.find(".pessoaobjeto").attr("name", "_x_idpessoa");


    //criaAutocompletesUn();
    CB.modal({
        titulo: strCabecalho,
        corpo: [objfrm],
        aoAbrir : function(){

            criaAutocompletesPessoa();
        
        }
    });


}


//Acrescentado para inserir a Pessoa responsável pela àrea (Lidiane - 04-03-2020)
//Autocomplete de Setores vinculados
function criaAutocompletesPessoa() {
    
    if(statusSgconselho == 'ATIVO'){

        $(":input[name=_x_idpessoa]").autocomplete({			
            source: pessoasDisponiveisParaVinculo,
            delay: 0,
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function (ul, item)
                {
                    lbItem = item.nome;
                    condItem= item.responsavel;
                    if(condItem = 'N')
                    {
                        return $('<li>')
                        .append('<a>' + lbItem + '</a>')
                        .appendTo(ul);
                    }
                };
            },
            select: function(event, ui) {
                CB.post({
                    objetos: {
                        "_x_i_pessoaobjeto_idobjeto": idModulo,
                        "_x_i_pessoaobjeto_idpessoa": ui.item.idpessoa,
                        "_x_i_pessoaobjeto_tipoobjeto": 'sgconselho',
                        "_x_i_pessoaobjeto_responsavel": 'Y'
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


function unidadepadrao() {

    debugger;

    var strCabecalho = "</strong>Adicionar unidades solicitadoras de custo</strong>";
    var htmloriginal = $("#unidadepadrao").html();
    var objfrm = $(htmloriginal);

    objfrm.find(".unidade-conselho").attr("name", "_x_idunidadepd");


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

    if(statusSgconselho == 'ATIVO') {
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
                                "_x_i_unidadeobjeto_tipoobjeto": `sgconselho`
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

    if(statusSgconselho == 'ATIVO') {
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
                        "_x_i_unidadeobjeto_tipoobjeto": `sgconselho`,
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

if(statusSgconselho == 'ATIVO') {
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