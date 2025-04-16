<script>
		var idModulo = <?= $_1_u_sgdepartamento_idsgdepartamento ? $_1_u_sgdepartamento_idsgdepartamento : 'false' ?>;
		var setoresDisponiveisParaVinculo = <?= JSON_ENCODE($_1_u_sgdepartamento_idsgdepartamento ? $setoresDisponiveisParaVinculo : []) ?>;
		var pessoasDisponiveisParaVinculo = <?= JSON_ENCODE($_1_u_sgdepartamento_idsgdepartamento ? $pessoasDisponiveisParaVinculo : []) ?>;
		var contaItensDisponiveisParaVinculo = <?= JSON_ENCODE($_1_u_sgdepartamento_idsgdepartamento ? $contaItensDisponiveisParaVinculo : []) ?>;
		var unidadesDisponiveisParaVinculo = <?= JSON_ENCODE($_1_u_sgdepartamento_idsgdepartamento ? $unidadesDisponiveisParaVinculo : []) ?>;
		var fillunidadesDisponiveisParaVinculo = <?= JSON_ENCODE($_1_u_sgdepartamento_idsgdepartamento ? $fillUnidadesDisponiveisParaVinculo : []) ?>;
	
		var lpsDisponiveisParaVinculo = <?= JSON_ENCODE($_1_u_sgdepartamento_idsgdepartamento ? $lpsDisponiveisParaVinculo : []) ?>;
        var statusDepartamento = <?=  "'{$_1_u_sgdepartamento_status}'" ?? 'false' ?>;
		var possuiVinculoComUnidade = '<?= $possuiVinculoComUnidade ?>';
		var gruposDisponiveisParaVinculo = <?= json_encode($gruposDisponiveisParaVinculo); ?>;
		var unidadesDisponiveisParaVinculoEnvio = <?= JSON_ENCODE($_1_u_sgdepartamento_idsgdepartamento ? $unidadesDisponiveisParaVinculoEnvio : []) ?>;

		function departamentoPessoa() {
			CB.modal({
				titulo: "</strong>Histórico de Coordenador de Departamento</strong>",
				corpo: $("#historico").html(),
				classe: 'sessenta',
			});

		}

		function inativaobjeto(inid) {
			CB.post({
				objetos: "_x_d_sgdepartamentosetor_idsgdepartamentosetor=" + inid,
				parcial: false

			});
		}

		//Altera se será grupo ou não (Lidiane - 13-03-2020)
		function altcheck(vtab, vcampo, vid, vcheck) {
			CB.post({
				objetos: "_x_u_" + vtab + "_id" + vtab + "=" + vid + "&_x_u_" + vtab + "_" + vcampo + "=" + vcheck
			});
		}

		function desvincular(inid) {
			CB.post({
				objetos: "_x_d_objetovinculo_idobjetovinculo=" + inid,
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

		function desvincularUnidade(inid) {
			CB.post({
				objetos: {
					"_x_d_unidadeobjeto_idunidadeobjeto": inid
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
	

		//Acrescentado para inserir a Pessoa responsável pela àrea (Lidiane - 27-02-2020)
		function desvincularPessoa(inid) {
			//debugger;
			CB.post({
				objetos: "_x_d_pessoaobjeto_idpessoaobjeto=" + inid,
				parcial: true,
				posPost: function() {}
			});
		}

		//Deletar LP do Departamento(Lidiane - 13-03-2020)
		function desvincularLp(inid) {
			CB.post({
				objetos: "_x_d_lpobjeto_idlpobjeto=" + inid,
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

		//Acrescentado para inserir a Pessoa responsável pela àrea (Lidiane - 27-02-2020)
		//Autocomplete de Setores vinculados
		$("#pessoaobjeto").autocomplete({
			source: pessoasDisponiveisParaVinculo,
			delay: 0,
			create: function() {
				$(this).data('ui-autocomplete')._renderItem = function(ul, item) {

					lbItem = item.nome;
					condItem = item.responsavel;
					if (condItem = 'N') {
						return $('<li>')
							.append('<a>' + lbItem + '</a>')
							.appendTo(ul);
					}
				};
			},
			select: function(event, ui) {
				CB.post({
					objetos: {
						"_x_i_pessoaobjeto_idobjeto": $(":input[name=_1_" + CB.acao + "_sgdepartamento_idsgdepartamento]").val(),
						"_x_i_pessoaobjeto_idpessoa": ui.item.idpessoa,
						"_x_i_pessoaobjeto_tipoobjeto": 'sgdepartamento',
						"_x_i_pessoaobjeto_responsavel": 'Y'

					},
					parcial: false

				});
			}
		});

	

		

		//Inserido em 17-03-2020 - Lidiane
		function AtualizaBim() {
			$.ajax({
				type: "get",
				url: "cron/bim.php",
				data: {
					call: "atualiza"
				},
				success: function(data) {					
					if(data.error)
					{
						return alertAtencao('Acesso não autorizado!');
					}

					alertSalvo('Bim Atualizado');

					setTimeout(() => document.location.reload(true), 600);
				},
				error: function(objxmlreq) {
					alert('Erro:<br>' + objxmlreq.status);
				}
			});
		}

		if (!$("#AtualizarBim").length) {
			$("#cbSalvar").after('<button id="AtualizarBim" type="button" class="btn btn-info btn-xs" onclick=" AtualizaBim()" title="Atualizar Bim"><i class="fa fa-refresh"></i>Atualizar Bim</button>');
		}

		$(document).keyup(function(e) {
			if (e.key === "Escape") { // escape key maps to keycode `27`
				// <DO YOUR WORK HERE>
				$("#AtualizarBim").remove();
			}
		});

		CB.prePost = function()
		{
			$('#unidadesdepartamento').removeClass('alertaCbvalidacao');
			if(!$('#unidadesdepartamento').val() && possuiVinculoComUnidade != '1'
				 && $('#status').val() == 'ATIVO' 
				 && statusDepartamento == 'ATIVO'
				 && $('.coordenador-vinculado tr').get().length)
			{
				$('#unidadesdepartamento').addClass('alertaCbvalidacao');
				alertAtencao('É necessário associar uma unidade para o responsável deste Departamento!');
				return false;
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
      
		if(statusDepartamento == 'ATIVO') {
			console.log(fillunidadesDisponiveisParaVinculo);
			$(":input[name=_x_idunidade]").autocomplete({
				source: fillunidadesDisponiveisParaVinculo,
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
							"_x_i_unidadeobjeto_tipoobjeto": `sgdepartamento`,
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

	function unidadepadrao() {

		debugger;

		var strCabecalho = "</strong>Adicionar unidades solicitadoras de custo</strong>";
		var htmloriginal = $("#unidadepadrao").html();
		var objfrm = $(htmloriginal);

		objfrm.find(".unidadesdepartamento").attr("name", "_x_idunidadepd");


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

		if(statusDepartamento == 'ATIVO') {
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
							"_x_i_unidadeobjeto_tipoobjeto": `sgdepartamento`
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
	

	function novosetor() {

		debugger;

		var strCabecalho = "</strong>Adicionar setores</strong>";
		var htmloriginal = $("#novosetor").html();
		var objfrm = $(htmloriginal);

		objfrm.find(".sgdepartamentovinc2").attr("name", "_x_idsgsetor");


		//criaAutocompletesUn();
		CB.modal({
			titulo: strCabecalho,
			corpo: [objfrm],
			aoAbrir : function(){

				criaAutocompletesSetor();
			
			}
		});


	}

	//Autocomplete de Setores vinculados
	function criaAutocompletesSetor() {

		$(":input[name=_x_idsgsetor]").autocomplete({			
			source: setoresDisponiveisParaVinculo,
			delay: 0,
			create: function() {
				$(this).data('ui-autocomplete')._renderItem = function(ul, item) {

					lbItem = item.setor;

					return $('<li>')
						.append('<a>' + lbItem + '</a>')
						.appendTo(ul);
				};
			},
			select: function(event, ui) {
				CB.post({
					objetos: {
						"_x_i_objetovinculo_idobjeto": $(":input[name=_1_" + CB.acao + "_sgdepartamento_idsgdepartamento]").val(),
						"_x_i_objetovinculo_tipoobjeto": 'sgdepartamento',
						"_x_i_objetovinculo_idobjetovinc": ui.item.idsgsetor,
						"_x_i_objetovinculo_tipoobjetovinc": 'sgsetor'
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
	

	function novalp() {

		debugger;

		var strCabecalho = "</strong>Adicionar LP</strong>";
		var htmloriginal = $("#novalp").html();
		var objfrm = $(htmloriginal);

		objfrm.find(".lpdepartamento").attr("name", "_x_idlp");


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
			source: lpsDisponiveisParaVinculo,
			delay: 0,
			create: function() {
				$(this).data('ui-autocomplete')._renderItem = function(ul, item) {

					lbItem = item.descricao;

					return $('<li>')
						.append('<a>' + lbItem + '</a>')
						.appendTo(ul);
				};
			},
			select: function(event, ui) {
				CB.post({
					objetos: {
						"_x_i_lpobjeto_idobjeto": $(":input[name=_1_" + CB.acao + "_sgdepartamento_idsgdepartamento]").val(),
						"_x_i_lpobjeto_idlp": ui.item.idlp,
						"_x_i_lpobjeto_tipoobjeto": 'sgdepartamento'
					},
					parcial: false,
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

	function novacategoria() {

		debugger;

		var strCabecalho = "</strong>Adicionar Categoria</strong>";
		var htmloriginal = $("#novacategoria").html();
		var objfrm = $(htmloriginal);

		objfrm.find(".grupo-es-objeto").attr("name", "_x_idcontaitem");


		//criaAutocompletesUn();
		CB.modal({
			titulo: strCabecalho,
			corpo: [objfrm],
			aoAbrir : function(){

				criaAutocompletesCat();
			
			}
		});


	}

	// Inserir Categoria
	function criaAutocompletesCat() {

		$(":input[name=_x_idcontaitem]").autocomplete({
			source: contaItensDisponiveisParaVinculo,
			delay: 0,
			create: function() {
				$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
					lbItem = item.contaitem;
					return $('<li>')
						.append('<a>' + lbItem + '</a>')
						.appendTo(ul);
				};
			},
			select: function(event, ui) {
				CB.post({
					objetos: {
						"_x_i_objetovinculo_idobjeto": $(":input[name=_1_" + CB.acao + "_sgdepartamento_idsgdepartamento]").val(),
						"_x_i_objetovinculo_tipoobjeto": 'sgdepartamento',
						"_x_i_objetovinculo_idobjetovinc": ui.item.idcontaitem,
						"_x_i_objetovinculo_tipoobjetovinc": 'contaitem'
					},
					parcial: false,
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
						,"_x_i_objetovinculo_tipoobjetovinc": "sgdepartamento"
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

if(statusDepartamento == 'ATIVO') {
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

		//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
	</script>