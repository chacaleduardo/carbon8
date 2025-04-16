<script>
	var acao = '<?= $_acao ?>';
	var idEvento = '<?= $_1_u_evento_idevento ?>';
	var eventoTipo = '<?= $_1_u_evento_ideventotipo ?>';
	var prevhoras ='<?=$eventoTipo['prevhoras']?>';
	var inicioHms = '<?= $p_str_iniciohms ?>';
	var duracaoHms = '<?= $_1_u_evento_duracaohms ?>';
	var temCampoInicio = '<?= $temCampoInicio ?>';
	var jFuncionario    = <?= json_encode($funcionariosDisponiveisParaVinculo)  ?>;
	var jSgsetorvinc    = <?= json_encode($gruposDisponiveisParaVinculo)  ?>;
	var jFuncSetvinc    = <?= json_encode($grupoDeFuncionariosDisponiveisParaVinculo)  ?>;
	var jsonMotivo      = <?= json_encode($motivos) ?>;
	var Jtime			= <?= $Jtime ?>;
	var JtimeDuracao	= <?= json_encode($arrDuracaoTempo) ?>;
	var horarioComercial = <?= json_encode(EventoController::buscarHorarioComercial()) ?>;
	var jTagp			= <?= json_encode($tags) ?>;
	var jPessoap		= <?= json_encode($pessoas) ?>;
	var jSgdocp			= <?= json_encode($sgDoc) ?>;
	var jSgAreas		= <?= json_encode($areas) ?>;
	var jSgDepartamentos= <?= json_encode($departamentos) ?>;
	var jSgSetores		= <?= json_encode($setores) ?>;
	var subevento       = "<?= $subevento ?>";
	var idPessoa    = <?= $idPessoa ?>;
	var nomePessoa  = "<?= $nomePessoa ?>";
	var tokeninicial = '<?= ($fluxounico == 'N') ? $tokeninicial : $_1_u_evento_idfluxostatus ?>';
	var inicio = '<?= $inicio ?>';
	var fim = '<?= $fim ?>';
	var dataTarefa = '<?= $_1_u_evento_inicio ?>';
	var idEquipamentoVinculadoAoEvento = '<?= $_1_u_evento_idequipamento ?>';
	var camposObrigatorios = <?= json_encode($camposObrigatorios) ?>;
	let ultimaSalaVinculada = $("#tageventoobj").val() || '';
	var duracaoDaReserva = document.querySelector('#duracaohms');
	var $jDocvinc = <?= json_encode($jDocvinc) ?>;
	var fluxounico = '<?=$fluxounico?>';

	// Ao clicar duas vezes no id do Evento, copia a URL do Evento - (Lidiane - 03-04-2020)
	var modulo = '<?=($modulo)?>',
		idmodulo = '<?=($idmodulo)?>',
		nomeModulo = '<?=($nomemodulo)?>',
		nmod = '<?=($nmod)?>',
		nmodid = '<?=($nmodid)?>';

	if(acao == 'i')
	{
		var eventoTipo = '<?= $idEventoTipo ?>';
		
		var now = new Date();
		var today = now.getDate() + '/' + ("00" + (now.getMonth() + 1)).slice(-2) + '/' + now.getFullYear();		

		if (modulo == "true")
		{
			let sLink = window.parent.location.search;
			modulo = sLink;

			let urlSplit = sLink.split("&");

			for (let i = 0; i < urlSplit.length; i++) {

				if (urlSplit[i].includes("_modulo")) {

					let modSplit = urlSplit[i].split("=");                    
					modulo = modSplit[1];
				}
			}
			
			idmodulo = removerParametroGet("_modulo", sLink);
			idmodulo = removerParametroGet("_acao", idmodulo);
			idmodulo = idmodulo.replace(/^\?/, "");

			let idModSplit = idmodulo.split("=");            
			nomemodulo = idModSplit[0];            
			idmodulo = idModSplit[1];
			
			if(idmodulo !== 'undefined' && modulo !== 'undefined')
			{
				sLink = "&_1_i_evento_idmodulo="+idmodulo+"&_1_i_evento_modulo="+modulo;
			}else{
				sLink = '';
			}

			CB.post({
				objetos: "_1_i_evento_ideventotipo="+eventoTipo+"&_1_i_evento_idpessoa="+idPessoa+"&_1_i_evento_prazo="+inicio+"&_1_i_evento_inicio="+inicio+"&_1_i_evento_iniciohms="+inicioHms+sLink
				,parcial:true
			});
		}else{
			if(idmodulo !== 'undefined'  && modulo !== 'undefined')
			{
				sLink = "&_1_i_evento_idmodulo="+idmodulo+"&_1_i_evento_modulo="+modulo;
			}else{
				sLink = '';
			}

			CB.post({
				objetos: "_1_i_evento_ideventotipo="+eventoTipo+"&_1_i_evento_idpessoa="+idPessoa+"&_1_i_evento_prazo="+inicio+"&_1_i_evento_inicio="+inicio+"&_1_i_evento_iniciohms="+inicioHms+sLink
				,parcial:true
			});
		}
	}

	modulo          = "<?= $_1_u_evento_modulo?>";
	idmodulo        = "<?= $_1_u_evento_idmodulo?>";
	nomemodulo      = "";
	idevento		= $(":input[name=_1_"+CB.acao+"_evento_idevento]").val();

	let btnRemoverLink = $('.remove-url');

	if(btnRemoverLink.get().length)
	{
		btnRemoverLink.on('click', function(){
			if(!confirm('Remover link?')) return;

			CB.post({
				objetos: {
					_1_u_evento_idevento: idEvento,
					_1_u_evento_url: ""
				},
				parcial: true
			});
		});
	}

	$('#tdsgsetor').show();
	$('#tdfuncionario').hide(); 
	
	$( document ).ready(function() {
		console.log( "ready!" );	
		
	/*	if(acao == 'u' && prevhoras =='Y')
			{	
				VMasker(document.getElementById("acomphoras")).maskPattern('99:99');
				VMasker(document.getElementById("evento_prevhoras")).maskPattern('999:99');
			}
			*/
			
	});
	$('#tdsgsetor').show();
	$('#tdfuncionario').hide(); 


	function setMaskPattern(inputElement) { 
		// Get the user's input value
		var inputValue = inputElement.value;


		// Determine the mask pattern based on the input length
		var maskPattern = (inputValue.length <= 5) ? '99:99' : '999:99';

		// Apply the mask pattern using VMasker
		VMasker(inputElement).maskPattern(maskPattern);
	}


	
	function isDono() {
		if (idPessoa == $('#idpessoa').val()) {
			return true;
		}
		return false;
	}

	if (isDono()) 
	{
		$("#repetirlink").click(function(event) {
			if ($("#repetircheckbox").is(":checked") === true) {
				$("#repetircheckbox").removeAttr("checked");
				$("#repetircheckbox").change();
			} else {
				$("#repetircheckbox").prop("checked", true);
				$("#repetircheckbox").change();
			}
		});

		$("#repetircheckbox").change(function(event) 
		{
			if ($("#repetircheckbox").is(":checked") === true) {

				$("#divrepetir").addClass('d-flex');
				$("[name=_1_" + CB.acao + "_evento_periodicidade]").val("DIARIO");
				$("[name=_1_" + CB.acao + "_evento_fimsemana]").val("N");
				console.log($("[name=_1_" + CB.acao + "_evento_prazo]").val());

				//Se o botão Repetir Evento estiver checado. Desabilita o campo Prazo. Desabilita somente se o campo estiver com valor
				//Alteração realizada em 23/01/2020 - Lidiane
				if($("[name=_1_" + CB.acao + "_evento_prazo]").val() != "")
				{
					$("[name=_1_" + CB.acao + "_evento_prazo]").addClass("desabilitado");
					$("[name=_1_" + CB.acao + "_evento_prazo]").prop("disabled", true);
					
					var now = new Date();
					var today = now.getDate() + '/' + (now.getMonth() + 1) + '/' + now.getFullYear();
					$("[name=_1_" + CB.acao + "_evento_repetirate]").val($("[name=_1_" + CB.acao + "_evento_inicio]").val());
				
				} else {
					$("#divrepetir").removeClass('d-flex');
					$("#divrepetir").hide();
					$("#repetircheckbox").removeAttr("checked");
					alert('Preencha o campo Prazo, antes de habilitar Repetir Evento.') ;
				}	
				

			} else {
				// var repetirate = $("[name=_1_" + CB.acao + "_evento_repetirate]").val();
				
				$("#divrepetir").removeClass('d-flex');
				$("#divrepetir").hide();
				$("[name=_1_" + CB.acao + "_evento_periodicidade]").val("");
				//$("[name=_1_" + CB.acao + "_evento_repetirate]").val("");
				$("[name=_1_" + CB.acao + "_evento_fimsemana]").val("");
				
				//Se o botão Repetir Evento não estiver checado. Habilita o campo Prazo.
				//Alteração realizada em 23/01/2020 - Lidiane
				$("[name=_1_" + CB.acao + "_evento_prazo]").removeClass("desabilitado");
				$("[name=_1_" + CB.acao + "_evento_prazo]").removeAttr("disabled");	
			}
		});

		// $("#duracaohms").click(function(){
		// 	$("[name=_1_" + CB.acao + "_evento_duracaohms]").val("");
		// });

		// Ajusta layout caso evento seja por dia 
		// Atualiza o valor da hora para null caso diainteiro==true
		// e proximo intervalo de 30min exatos caso dia inteiro==false
		$("#diainteirocheckbox").change(function(event)
		{
			if ($("#diainteirocheckbox").is(":checked") === false) {

				$("#timeinicio").show();
				$("#timefim").show();
				$("#datainicio").removeAttr("class", "col-md-4");
				$("#datafim").removeAttr("checked", "col-md-4");
				$("#datainicio").attr("class", "col-md-2");
				$("#datafim").attr("class", "col-md-2");

				let date = new Date();

				$("[name=_1_" + CB.acao + "_evento_iniciohms]").val(
					roundUpToMinuteInterval(date.getHours(), date.getMinutes()));

				$("[name=_1_" + CB.acao + "_evento_fimhms]").val(
					roundUpToMinuteInterval(date.getHours() + 1, date.getMinutes()));

			} else {
				$("#timeinicio").hide();
				$("#timefim").hide();

				$("#datainicio").removeAttr("class", "col-md-2");
				$("#datafim").removeAttr("checked", "col-md-2");
				$("#datainicio").attr("class", "col-md-4");
				$("#datafim").attr("class", "col-md-4");

				$("[name=_1_" + CB.acao + "_evento_iniciohms]").val("00:00:12");
				$("[name=_1_" + CB.acao + "_evento_fimhms]").val("00:00:12");
			}

		});
	}else{
		$("input[name=_1_u_evento_evento]").prop("readonly", "readonly");
		$("textarea[name=_1_u_evento_descricao]").prop("readonly", "readonly");
	}

	function alteraTipoEvento(ideventotipo){
		$.ajax({
			type: "get",
			url : "ajax/alteraevento.php?idevento="+idEvento+"&ideventotipo="+ideventotipo,
			data: { call : "Altera Tipo Evento" },		
			success: function(data){
				alertSalvo('Evento Alterado');
				CB.post({
					"objetos":"_x_u_evento_idevento="+idEvento+"&_x_u_evento_ideventotipo="+ideventotipo
					,parcial:true
				});
			},
			error: function(objxmlreq){
				alert('Erro:<br>'+objxmlreq.status); 
			}
		});
	}

	if ((modulo !== undefined && modulo != '' && modulo != 'evento') || (modulo == 'evento' && idmodulo != idevento && idmodulo != '')) 
	{
		if (modulo == "true") {
			let sLink = window.parent.location.search;
			modulo = sLink;

			let urlSplit = sLink.split("&");

			for (let i = 0; i < urlSplit.length; i++) {

				if (urlSplit[i].includes("_modulo")) {

					let modSplit = urlSplit[i].split("=");                    
					modulo = modSplit[1];
				}
			}

			idmodulo = removerParametroGet("_modulo", sLink);
			idmodulo = removerParametroGet("_acao", idmodulo);
			idmodulo = removerParametroGet("_menu", idmodulo);
			idmodulo = idmodulo.replace(/^\?/, "");

			let idModSplit = idmodulo.split("=");            
			nomemodulo = idModSplit[0];            
			idmodulo = idModSplit[1];
			
			if (nmod != '' &&  nmodid != ''){
				modulo = nmod;
				nomemodulo = 'id'+nmod;
				idmodulo = nmodid; 
			}
		} else {
			if (nmod != '' &&  nmodid != ''){
			
				modulo = nmod;
				nomemodulo = 'id'+nmod;
				idmodulo = nmodid;
			}
		}

		let valModulo = modulo + ": "+ nomemodulo + "=" + idmodulo;

		$("#inputmodulo").hide();
		$("#inputidmodulo").hide();	
		$("#insereLinkModulo").hide();
		$("#divanexolink").hide();
	} else {
		$("#divmodulo").hide();
	}

	if (subevento) 
	{
		$("#rowRepetition").hide();
		//$("#idevento").prop("readonly", "readonly");
		$("#selectTipoEvento").prop("disabled", "disabled");
		$("input[name=_1_u_evento_fim]").prop("readonly", "readonly");
		$("input[name=_1_u_evento_inicio]").prop("readonly", "readonly");

		$("textarea[name=_1_u_evento_descricao]").prop("readonly", "readonly");

		//$(".rowcampos").remove();
		//$(".calendario").removeClass('calendario');
	}

	$(".sgdoceventoobj").autocomplete({
		source: jSgdocp
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				lbItem = item.label;			
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		}
	});

	$(".sgarea-idempresa").autocomplete({
		source: jSgAreas
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				lbItem = item.label;			
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		}
	});

	$(".sgdepartamentoobj").autocomplete({
		source: jSgDepartamentos
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				lbItem = item.label;			
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		}
	});

	$(".sgsetorobj").autocomplete({
		source: jSgSetores
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				lbItem = item.label;			
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		}
	});

	$(".pessoaeventoobj").autocomplete({
		source: jPessoap
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				lbItem = item.label;			
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		}
	});

	$(".tageventoobj").autocomplete({
		source: jTagp
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				lbItem = item.label;			
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		}
		,select: function(event, ui){
			if($(this).attr('travasala')==="Y"){
				var idtag = $(this).attr('cbvalue');
				var idevento=$(":input[name=_1_"+CB.acao+"_evento_idevento]").val();
				var inicio=$(":input[name=_1_"+CB.acao+"_evento_inicio]").val();
				var iniciohms=$(":input[name=_1_"+CB.acao+"_evento_iniciohms]").val();
				//var fim=$(":input[name=_1_"+CB.acao+"_evento_fim]").val();
				var duracaohms=$(":input[name=_1_"+CB.acao+"_evento_duracaohms]").data('duracao');
				var diainteiro=$(":input[name=_1_"+CB.acao+"_evento_diainteiro]").is(":checked");
				if(iniciohms===''){
					alert("Favor preencher a Hora do Início.");
					$("#tageventoobj").val("").cbval("");
						return;
				}
				if(inicio==='' || inicio == undefined){
						inicio=$(":input[name=_1_"+CB.acao+"_evento_prazo]").val();
						
						
					if(inicio==='' || inicio == undefined){
						alert("Favor preencher o Início.");
						$("#tageventoobj").val("").cbval("");
							return;
					}
				} if(iniciohms==='' || iniciohms == undefined){
						iniciohms=moment().format("HH:mm:ss");			
				}
				
				if(duracaohms==='' && diainteiro===false){
					alert("Favor marcar o flag dia inteiro ou indicar a Duração.");
					$("#tageventoobj").val("").cbval("");
						return;
				}
				let verificaReserva = verificaTagReserva(inicio, iniciohms);
				
				if(verificaReserva != 'error' && !verificaReserva)
				{
					let comentarioSala = `trocou a sala ${ultimaSalaVinculada} para ${$(this).val()}`;

					$.ajax({
						type: "post",
						url: "ajax/eventoresp3.php?vopcao=add" +
							"&videvento="+ idEvento +
							"&vobs=" + comentarioSala +
							"&vinicio=" + inicio +
							"&viniciohms=" + iniciohms +
							"&duracaohms=" + duracaohms +
							"&diainteiro=" + diainteiro +
							"&idtag=" + idtag,
						success: function(data) {	
							CB.post({
								"prePost": false,
								"objetos": {}
							});	
						}
					});
				};

			}//if($(this).attr('travasala')==="Y"){

		}

	});

	//autocomplete de motivo
	$("#motivornc").autocomplete({
		source: jsonMotivo
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
			};
		}  
	});
	
	//Autocomplete de Setores vinculados
	$("#pessoavinc").autocomplete({
		source: jFuncionario
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				lbItem = item.label;			
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		}
		,select: function(event, ui){
			CB.post({
				objetos: {
					"_x_i_fluxostatuspessoa_idmodulo":$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
					,"_x_i_fluxostatuspessoa_modulo": 'evento'
					,"_x_i_fluxostatuspessoa_idpessoa":$(":input[name=_1_"+CB.acao+"_evento_idpessoa]").val()
					,"_x_i_fluxostatuspessoa_idempresa": '1'
					,"_x_i_fluxostatuspessoa_idobjeto": ui.item.value
					,"_x_i_fluxostatuspessoa_tipoobjeto": 'pessoa'
					,"_x_i_fluxostatuspessoa_idfluxostatus": tokeninicial
					,"_x_i_fluxostatuspessoa_oculto": '0'
					,"_x_i_fluxostatuspessoa_inseridomanualmente":'S'
				}
				,parcial: true
				,posPost:function(){
					$.ajax({
						type: "post",
						url: "ajax/evento.php?vopcao=atualizaparticipantes&videvento="+$("[name=_1_u_evento_idevento]").val()+"&vidfluxostatuspessoa="+idfluxostatuspessoa+"&vidfluxostatus="+tokeninicial,
						success: function(retorno){
							if(retorno.error)
							{
								return alertAtencao(retorno.error);
							}

							alertAzul("Participantes atualizados","",1000);
							//alert();
							location.reload();

						}
					});
				}
				
			});
		}
	});

	function exmodulo(idevento){
		if(confirm("Deseja realmente remover este link? ")){
			CB.post({
			objetos: {
				"_1_u_evento_idevento":idevento
				,"_1_u_evento_modulo":''
				,"_1_u_evento_idmodulo":''
			}
			,parcial:true
		});	
		}
	}
	function verificaTagReserva(inicio = false, iniciohms = false)
	{
		var idtag = $(":input[name=_1_"+CB.acao+"_evento_idequipamento]").attr("cbvalue");
		var idevento=$(":input[name=_1_"+CB.acao+"_evento_idevento]").val();
		if (!inicio)
		{
			var inicio=$(":input[name=_1_"+CB.acao+"_evento_inicio]").val();
		}

		if(!iniciohms)
		{
			var iniciohms=$(":input[name=_1_"+CB.acao+"_evento_iniciohms]").val();
		}
		//var fim=$(":input[name=_1_"+CB.acao+"_evento_fim]").val();
		var duracaohms=$(":input[name=_1_"+CB.acao+"_evento_duracaohms]").attr('cbvalue');
		var diainteiro=$(":input[name=_1_"+CB.acao+"_evento_diainteiro]").is(":checked");

		let reservada = false;
				
		if($('#tageventoobj').val() != undefined){
			if ($('#tageventoobj').val().length > 0)
			{
				$.ajax({
					type: 'get',
					url: 'ajax/eventoverificatagreserva.php',
					data: {inicio: inicio,iniciohms:iniciohms,duracaohms:duracaohms,diainteiro:diainteiro,idtag:idtag,idevento:idevento},
					async: false,
					
					/*********************************************************************/
					success: function(data){

						reservada = (data == "true");
						if(data=="false") //se retornar false não tem reserva
						{
							$("#reservasala").fadeTo(100,0.9,function() //mostra o messagebox OK
							{ 
								$('#reservasala').html('TAG disponível!').removeClass('messageboxerror').addClass('messageboxok').fadeTo(100,1);
								document.getElementById("cbSalvar").style.display="";
							});		
							
						}else if(data=="true") //se a pagina retornou erro
						{
							$("#reservasala").fadeTo(100,0.9,function()  //mostra mensagem de sala ocupada
							{ 
								$('#reservasala').html('TAG ocupada!').addClass('messageboxerror').fadeTo(100,1);
								document.getElementById("cbSalvar").style.display="none";
								$("#tageventoobj").val("").cbval("");
							});	
							
							if(idEquipamentoVinculadoAoEvento != idtag)
							{
								$("#tageventoobj").val("").cbval("");
								$("#_1_u_evento_idequipamento").val("").cbval("");

								//LTM (17/08/2021): Limpar a tag caso esteja ocupada.
								CB.post({
									objetos: {
										"_1_u_evento_idevento":idevento
										,"_1_u_evento_idequipamento":''
									}
									,parcial: true
									,refresh: false
									,msgSalvo: false
								});	
							}	
						}else
						{
							$("#reservasala").fadeTo(100,0.9,function()  //mostra qualquer condicao diferente. Ex: erro de php
							{ 
								$('#reservasala').html('Erro:<br>'+data).addClass('messageboxerror').fadeTo(100,1);
								document.getElementById("cbSalvar").style.display="none";
								$("#tageventoobj").val("").cbval("");
				
							});
						}
					},

					/*********************************************************************/
					error: function(objxmlreq){
						reservada = 'error';
						$("#reservasala").fadeTo(100,0.1,function()
						{ 
							$('#reservasala').html('Erro geral:<br>'+objxmlreq.status).addClass('messageboxerror').fadeTo(100,1);
								document.getElementById("cbSalvar").style.display="none";
								$("#tageventoobj").focus();
						});
					}
				})//$.ajax
			}
		}

		return reservada;
	}

	function retirapessoa(inid, nome){
		CB.post({
			objetos: {
				"_x_d_fluxostatuspessoa_idfluxostatuspessoa":inid
				,"_comentario_i_modulocom_idmodulo": $(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
				,"_comentario_i_modulocom_modulo": "evento"
				,"_comentario_i_modulocom_status": "ATIVO"
				,"_comentario_i_modulocom_descricao": "removeu "+nome
			}
			,parcial: false
			,posPost:function(){
				$.ajax({
					type: "post",
					url: "ajax/evento.php?vopcao=atualizaparticipantes&videvento="+$("[name=_1_u_evento_idevento]").val(),
					success: function(data){
						if(data.error)
						{
							return alertAtencao(data.error);
						}

						alertAzul("Participantes atualizados","",1000);
						//alert();
						location.reload();

					}
				});
			}
		});
	}

	$("#sgsetorvinc").autocomplete({
		source: jSgsetorvinc
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
			};
		}
		,select: function(event, ui){
			CB.post({
				objetos: {
					"_x_i_fluxostatuspessoa_idmodulo":$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
					,"_x_i_fluxostatuspessoa_modulo": 'evento'
					,"_x_i_fluxostatuspessoa_idempresa": '1'
					,"_x_i_fluxostatuspessoa_idobjeto": ui.item.value
					,"_x_i_fluxostatuspessoa_tipoobjeto": 'imgrupo'
					,"_x_i_fluxostatuspessoa_inseridomanualmente":'S'
					,"_x_i_fluxostatuspessoa_idfluxostatus": tokeninicial
				}
				,parcial: true
				,refresh: false
				,posPost:function(){
				console.log('entrei');
					$.ajax({
						type: "post",
						url: "ajax/evento.php?vopcao=atualizaparticipantes&videvento="+$("[name=_1_u_evento_idevento]").val(),
						success: function(data){
							if(data.error)
							{
								return alertAtencao(data.error);
							}

							alertAzul("Participantes atualizados","",1000);
							//alert();
							location.reload();

						}
					});
				}
			});
		}
	});
	
	function retirasgsetor(inid, nome){
		CB.post({
			objetos: {
				"_x_d_fluxostatuspessoa_idfluxostatuspessoa":inid
				,"_comentario_i_modulocom_idmodulo": $(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
				,"_comentario_i_modulocom_modulo": "evento"
				,"_comentario_i_modulocom_status": "ATIVO"
				,"_comentario_i_modulocom_descricao": "removeu o grupo "+nome
			}
			,parcial: true
			,refresh: false
			,posPost:function(){
				$.ajax({
					type: "post",
						url: "ajax/evento.php?vopcao=atualizaparticipantes&videvento="+$("[name=_1_u_evento_idevento]").val(),
					success: function(data){
						if(data.error)
						{
							return alertAtencao(data.error);
						}

						//alertAzul("Participantes atualizados","",1000);
						location.reload();
					}
				});
			}
		});
	}
	$('.selectpicker').selectpicker('render');
	function adicionaParticipantes(){
		
		obj = {}
		objaux = []
		arrVal = $(`#funcsetvinc`).val()
		arrVal.forEach((e,i)=>{
			var pessoa = e.split("_")[0]
			var nome = e.split("_")[1]
			var tipo = e.split("_")[2]
			if(tipo == 'pessoa'){
				obj[`_x${i}_i_fluxostatuspessoa_idmodulo`] = $(":input[name=_1_"+CB.acao+"_evento_idevento]").val();
				obj[`_x${i}_i_fluxostatuspessoa_modulo`] = 'evento';
				obj[`_x${i}_i_fluxostatuspessoa_idpessoa`] = $(":input[name=_1_"+CB.acao+"_evento_idpessoa]").val();
				obj[`_x${i}_i_fluxostatuspessoa_idempresa`] = '1';
				obj[`_x${i}_i_fluxostatuspessoa_idobjeto`] = pessoa;
				obj[`_x${i}_i_fluxostatuspessoa_tipoobjeto`] = 'pessoa';
				obj[`_x${i}_i_fluxostatuspessoa_idfluxostatus`] = tokeninicial;
				obj[`_x${i}_i_fluxostatuspessoa_oculto`] = '0';
				obj[`_x${i}_i_fluxostatuspessoa_inseridomanualmente`] = 'S';
				obj[`_comentario${i}_i_modulocom_idmodulo`] = $(":input[name=_1_"+CB.acao+"_evento_idevento]").val();
				obj[`_comentario${i}_i_modulocom_modulo`] = "evento";
				obj[`_comentario${i}_i_modulocom_status`] = "ATIVO";
				obj[`_comentario${i}_i_modulocom_descricao`] = "adicionou "+nome;
				objaux.push(`${pessoa}-${tipo}`)
				
			}
			if(tipo == 'grupo'){
				obj[`_x${i}_i_fluxostatuspessoa_idmodulo`] = $(":input[name=_1_"+CB.acao+"_evento_idevento]").val();
				obj[`_x${i}_i_fluxostatuspessoa_modulo`] = 'evento';
				obj[`_x${i}_i_fluxostatuspessoa_idpessoa`] = $(":input[name=_1_"+CB.acao+"_evento_idpessoa]").val();
				obj[`_x${i}_i_fluxostatuspessoa_idempresa`] = '1';
				obj[`_x${i}_i_fluxostatuspessoa_idobjeto`] = pessoa;
				obj[`_x${i}_i_fluxostatuspessoa_tipoobjeto`] = 'imgrupo';
				obj[`_x${i}_i_fluxostatuspessoa_idfluxostatus`] = tokeninicial;
				obj[`_x${i}_i_fluxostatuspessoa_oculto`] = '0';
				obj[`_x${i}_i_fluxostatuspessoa_inseridomanualmente`] = 'S';
				obj[`fluxounico`] = `${fluxounico}`;
				obj[`_comentario${i}_i_modulocom_idmodulo`] = $(":input[name=_1_"+CB.acao+"_evento_idevento]").val();
				obj[`_comentario${i}_i_modulocom_modulo`] = "evento";
				obj[`_comentario${i}_i_modulocom_status`] = "ATIVO";
				obj[`_comentario${i}_i_modulocom_descricao`] = "adicionou o grupo "+nome;
				objaux.push(`${pessoa}-imgrupo`);
			}
		});
		
		CB.post({
			objetos: obj
			,parcial: false						
			,posPost:function(){
				$.ajax({
				type: "post",
					url: "ajax/evento.php?vopcao=atualizaparticipantes&videvento="+$("[name=_1_u_evento_idevento]").val()+"&vtipoobjeto=&idobjeto=&objetos="+objaux.join(),
					success: function(retorno){
						if(retorno.error)
						{
							return alertAtencao(retorno.error);
						}

						alertAzul("Participantes atualizados","",1000);
						//alert();
						location.reload();
					}
				});
			}
		});

		if(tipo == 'pessoa')
		{
			CB.post({
				objetos: {
					"_x_i_fluxostatuspessoa_idmodulo":$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
					,"_x_i_fluxostatuspessoa_modulo": 'evento'
					,"_x_i_fluxostatuspessoa_idpessoa":$(":input[name=_1_"+CB.acao+"_evento_idpessoa]").val()
					,"_x_i_fluxostatuspessoa_idempresa": '1'
					,"_x_i_fluxostatuspessoa_idobjeto": pessoa
					,"_x_i_fluxostatuspessoa_tipoobjeto": 'pessoa'
					,"_x_i_fluxostatuspessoa_idfluxostatus": tokeninicial
					,"_x_i_fluxostatuspessoa_oculto": '0'
					,"_x_i_fluxostatuspessoa_inseridomanualmente":'S'
					,"_comentario_i_modulocom_idmodulo": $(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
					,"_comentario_i_modulocom_modulo": "evento"
					,"_comentario_i_modulocom_status": "ATIVO"
					,"_comentario_i_modulocom_descricao": "adicionou "+ui.item.nome
				}
				,parcial: false						
				,posPost:function(){
					var idobjeto = pessoa;
					$.ajax({
						type: "post",
							url: "ajax/evento.php?vopcao=atualizaparticipantes&videvento="+$("[name=_1_u_evento_idevento]").val()+"&vtipoobjeto=pessoa&idobjeto="+idobjeto,
						success: function(retorno){
							if(retorno.error)
							{
								return alertAtencao(retorno.error);
							}

							alertAzul("Participantes atualizados","",1000);
							//alert();
							location.reload();
						}
					});
				}
				
			});
		}
		
		if(tipo == 'grupo')
		{
			CB.post({
				objetos: {
					"_x_i_fluxostatuspessoa_idmodulo":$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
					,"_x_i_fluxostatuspessoa_modulo": 'evento'
					,"_x_i_fluxostatuspessoa_idempresa": '1'
					,"_x_i_fluxostatuspessoa_idobjeto": pessoa
					,"_x_i_fluxostatuspessoa_tipoobjeto": 'imgrupo'
					,"_x_i_fluxostatuspessoa_inseridomanualmente":'S'
					,"_x_i_fluxostatuspessoa_idfluxostatus": tokeninicial
					,"_comentario_i_modulocom_idmodulo": $(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
					,"_comentario_i_modulocom_modulo": "evento"
					,"_comentario_i_modulocom_status": "ATIVO"
					,"_comentario_i_modulocom_descricao": "adicionou o grupo "+ui.item.nome
				}
				,parcial: false
				,refresh: false
				,posPost:function(){
					var idobjeto = pessoa;
					$.ajax({
						type: "post",
						url: "ajax/evento.php?vopcao=atualizaparticipantes&videvento="+$("[name=_1_u_evento_idevento]").val()+"&vtipoobjeto=grupo&idobjeto="+idobjeto,
						success: function(data){
							if(data.error)
							{
								return alertAtencao(data.error);
							}

							alertAzul("Participantes atualizados","",1000);
							//alert();
							location.reload();
						}
					});
				}
			});
		}

	}
	// $("#funcsetvinc").autocomplete({
	// 	source: jFuncSetvinc
	// 	,delay: 0
	// 	,create: function(){
	// 		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
	// 			return $('<li>').append("<a>"+item.nome+"</a>").appendTo(ul);

	// 		};
	// 	}
	// 	,select: function(event, ui){
	// 		var tipo = ui.item.tipo,
	// 			pessoa = ui.item.pessoa;

	// 		if(tipo == 'pessoa')
	// 		{
	// 			CB.post({
	// 				objetos: {
	// 					"_x_i_fluxostatuspessoa_idmodulo":$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
	// 					,"_x_i_fluxostatuspessoa_modulo": 'evento'
	// 					,"_x_i_fluxostatuspessoa_idpessoa":$(":input[name=_1_"+CB.acao+"_evento_idpessoa]").val()
	// 					,"_x_i_fluxostatuspessoa_idempresa": '1'
	// 					,"_x_i_fluxostatuspessoa_idobjeto": pessoa
	// 					,"_x_i_fluxostatuspessoa_tipoobjeto": 'pessoa'
	// 					,"_x_i_fluxostatuspessoa_idfluxostatus": tokeninicial
	// 					,"_x_i_fluxostatuspessoa_oculto": '0'
	// 					,"_x_i_fluxostatuspessoa_inseridomanualmente":'S'
	// 					,"_comentario_i_modulocom_idmodulo": $(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
	// 					,"_comentario_i_modulocom_modulo": "evento"
	// 					,"_comentario_i_modulocom_status": "ATIVO"
	// 					,"_comentario_i_modulocom_descricao": "adicionou "+ui.item.nome
	// 				}
	// 				,parcial: false						
	// 				,posPost:function(){
	// 					var idobjeto = pessoa;
	// 					$.ajax({
	// 						type: "post",
	// 							url: "ajax/evento.php?vopcao=atualizaparticipantes&videvento="+$("[name=_1_u_evento_idevento]").val()+"&vtipoobjeto=pessoa&idobjeto="+idobjeto,
	// 						success: function(retorno){
	// 							if(retorno.error)
	// 							{
	// 								return alertAtencao(retorno.error);
	// 							}

	// 							alertAzul("Participantes atualizados","",1000);
	// 							//alert();
	// 							location.reload();
	// 						}
	// 					});
	// 				}
					
	// 			});
	// 		}
			
	// 		if(tipo == 'grupo')
	// 		{
	// 			CB.post({
	// 				objetos: {
	// 					"_x_i_fluxostatuspessoa_idmodulo":$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
	// 					,"_x_i_fluxostatuspessoa_modulo": 'evento'
	// 					,"_x_i_fluxostatuspessoa_idempresa": '1'
	// 					,"_x_i_fluxostatuspessoa_idobjeto": pessoa
	// 					,"_x_i_fluxostatuspessoa_tipoobjeto": 'imgrupo'
	// 					,"_x_i_fluxostatuspessoa_inseridomanualmente":'S'
	// 					,"_x_i_fluxostatuspessoa_idfluxostatus": tokeninicial
	// 					,"_comentario_i_modulocom_idmodulo": $(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
	// 					,"_comentario_i_modulocom_modulo": "evento"
	// 					,"_comentario_i_modulocom_status": "ATIVO"
	// 					,"_comentario_i_modulocom_descricao": "adicionou o grupo "+ui.item.nome
	// 				}
	// 				,parcial: false
	// 				,refresh: false
	// 				,posPost:function(){
	// 					var idobjeto = pessoa;
	// 					$.ajax({
	// 						type: "post",
	// 						url: "ajax/evento.php?vopcao=atualizaparticipantes&videvento="+$("[name=_1_u_evento_idevento]").val()+"&vtipoobjeto=grupo&idobjeto="+idobjeto,
	// 						success: function(data){
	// 							if(data.error)
	// 							{
	// 								return alertAtencao(data.error);
	// 							}

	// 							alertAzul("Participantes atualizados","",1000);
	// 							//alert();
	// 							location.reload();
	// 						}
	// 					});
	// 				}
	// 			});
	// 		}
	// 	}
	// });

	if ($("[name=_1_u_evento_idevento]").val()) {
		$(".cbupload").dropzone({
			idObjeto: $("[name=_1_u_evento_idevento]").val(),
			tipoObjeto: 'evento',
			tipoArquivo: 'ANEXO',
			idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"]?>'
		});
	}

	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
	<?
	if(!empty($_1_u_evento_idevento)){
		$sqla="select * from carrimbo 
			where status='PENDENTE' 
			and idobjeto = ".$_1_u_evento_idevento." 
			and tipoobjeto in ('evento')
			and idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
		$resa=d::b()->query($sqla) or die("Erro ao buscar se modulo esta assinado: Erro: ".mysqli_error(d::b())."\n".$sqla);
		$qtda= mysqli_num_rows($resa);
		if($qtda>0){
			$rowa=mysqli_fetch_assoc($resa);
			?>    
			botaoAssinar(<?=$rowa['idcarrimbo']?>);  
			<?	    

		}// if($qtda>0){
	}//if(!empty($_1_u_sgdoc_idsgdoc)){
	?>
	
	if( $("[name=_1_u_atendimento_idatendimento]").val() ){
		$(".cbupload").dropzone({
			idObjeto: $("[name=_1_u_atendimento_idatendimento]").val()
			,tipoObjeto: 'atendimento'
			,idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"]?>'
		});
	}

	function escondebotao(){
		$('#btAssina').hide();
		// document.location.reload(); 
	}
	
	//Antes de salvar atualiza o textarea
	CB.prePost = function(){
		if(tinyMCE.get('diveditor')){
			//falha nbsp: oDescritivo.val( tinyMCE.get('diveditor').getContent({format : 'raw'}).toUpperCase());
			oDescritivo.val( tinyMCE.get('diveditor').getContent().toUpperCase());
		}
	}
	
	sSeletor = '#diveditor';
	oDescritivo = $("[name=_1_"+CB.acao+"_evento_descricao]");

	//Atribuir MCE somente apos método loadUrl
	//CB.posLoadUrl = function(){
		//Inicializa Editor
		if(tinyMCE.editors["diveditor"]){
			tinyMCE.editors["diveditor"].remove();
		}
		//Inicializa Editor
		tinymce.init({
			selector: sSeletor
			,language: 'pt_BR'
			,inline: true /* não usar iframe */
			,toolbar: 'formatselect | removeformat | fontsizeselect | forecolor backcolor | bold | alignleft aligncenter alignright alignjustify | subscript superscript | bullist numlist | table | pagebreak'
			,menubar: false
			,plugins: ['table','textcolor','lists']
			,fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt"
			,setup: function (editor) {
				editor.on('init', function (e) {
					this.setContent(oDescritivo.val());
				});
			}
			,entity_encoding: 'raw'
		});
	//}

	CB.on('posLoadUrl', function(){
		if(CB.logado){
			let linkIframe = '<?=$linkIframe?>' || '';
			$("#iframeModulo").append(`
				<iframe src="${linkIframe}" width="100%" height="100%" frameborder="0"></iframe>
			`);
		}
	});

	$(document).ready(function(){
		if(sessionStorage.getItem('tipoParticpante') == 'funcionario'){
			habilitaFuncionario();
			$("#btfunc").addClass("selecionado");
		}

		if(sessionStorage.getItem('tipoParticpante') == 'setor'){
			habilitaSetor();
			$("#btset").addClass("selecionado");
		}
	});

    //O daterangepicker não dispara o "change" do elemento. Portanto deve ser feita verificação do evento do plugin
	$(".calendarioprazo").on('apply.daterangepicker', function(ev, picker) 
	{
		$(this).html(picker.startDate.format("DD/MM/YYYY") || "");

		$(this).closest(".eventoRow").attr("prazo", picker.startDate.format("YYYY-MM-DD"));
		console.log('prazo1');
		console.log($(this).parent().attr("idevento"));
		$.ajax({
			type: "post",
			url: "ajax/eventoresp3.php?vopcao=add&videvento="+ idEvento + "&vprazo=" + picker.startDate.format("YYYY-MM-DD"),
			success: function(data) {
				if(data && data.error) return alertAtencao(data.error);

				alertAzul("Prazo Atualizado", "", 1000);
				atualizaComentario();

			}
		});
	}).on('change', function() {
		ev.stopPropagation();
		$(this).html(picker.startDate.format("DD/MM/YYYY") || "");
		if ($(this).closest(".eventoRow").attr("prazo") == '') {
			var str = "Definiu o prazo" +
				"\n para: " + picker.startDate.format("DD/MM/YYYY");
		} else {
			var str = "Alterou o prazo" +
				"\n de: " + moment($(this).closest(".eventoRow").attr("prazo")).format('DD/MM/YYYY') +
				"\n para: " + picker.startDate.format("DD/MM/YYYY");
		}
		$(this).closest(".eventoRow").attr("prazo", picker.startDate.format("YYYY-MM-DD"));
		console.log('prazo2');
		console.log($(this).parent().attr("idevento"));
		
		$.ajax({
			type: "post",
			url: "ajax/eventoresp3.php?vopcao=add&videvento="+ idEvento + "&vdatainicio=" + picker.startDate.format(
				"YYYY-MM-DD hh:mm:ss"),
			success: function(data) {
				if(data && data.error) return alertAtencao(data.error);

				alertAzul("Prazo Atualizado", "", 1000);
				atualizaComentario();

			}
		});
	});

	$(".calendariotime").on('apply.daterangepicker', function(ev, picker) 
	{
		$(this).closest(".eventoRow").attr("prazo", picker.startDate.format("YYYY-MM-DD"));

		if ($(this).closest(".eventoRow").attr("travasala") == 'Y' && $(this).closest(".eventoRow").attr(
				"idequipamento")) {
			var nurl = "ajax/eventoverificatagreserva.php?" +
				"execucao=" + $(this).closest(".eventoRow").attr("iniciodata") + ' ' + $(this).closest(
					".eventoRow").attr("iniciohms") +
				"&inicio=" + $(this).closest(".eventoRow").attr("iniciodata") +
				"&iniciohms=" + $(this).closest(".eventoRow").attr("iniciohms") +
				"&duracaohms=" + $(this).closest(".eventoRow").attr("duracaohms") +
				"&diainteiro=" + $(this).closest(".eventoRow").attr("diainteiro") +
				"&idtag=" + $(this).closest(".eventoRow").attr("idequipamento") +
				"&idevento=" + $(this).closest(".eventoRow").attr("idevento");
			var travado = 'false';
			$.ajax({
				type: "post",
				url: nurl,
				success: function(data) {
					if (data == "true") {
						alertAzul("TAG ocupada!", "", 1000);
						var travado = 'true';
						//LTM (17/08/2021): Limpar a tag caso esteja ocupada.
						$("#_1_u_evento_idequipamento").val("").cbval("");
						CB.post({
							objetos: {
								"_1_u_evento_idevento":idevento
								,"_1_u_evento_idequipamento":''
							}
							,parcial: true
							,refresh: false
							,msgSalvo: false
						});	
					} else {
						var travado = 'false';
					}
				}
			});

		} else {
			var travado = 'false';
		}
		
		if (travado == 'false') {
			if ($(this).closest(".eventoRow").attr("idequipamento") === undefined) {
				var idtag = '';
			} else {
				var idtag = $(this).closest(".eventoRow").attr("idequipamento");
			}
			$.ajax({
				type: "post",
				url: "ajax/eventoresp3.php?vopcao=add" +
					"&videvento="+ idEvento +
					"&vinicio=" + picker.startDate.format("YYYY-MM-DD") +
					"&viniciohms=" + $('#iniciohms').val() +
					"&duracaohms=" + $(this).closest(".eventoRow").attr("duracaohms") +
					"&diainteiro=" + $(this).closest(".eventoRow").attr("diainteiro") +
					"&idtag=" + idtag,
				success: function(data) {
					if(data && data.error) return alertAtencao(data.error);

					let verificaTagReserva = verificaTagReserva(picker.startDate.format("DD/MM/YYYY"),picker.startDate.format("HH:mm:ss"));

					if(verificaTagReserva != 'error' && !verificaTagReserva)
					{
						alertAzul("Data Atualizada ", "", 1000);
						atualizaComentario();
						$(this).html(picker.startDate.format("DD/MM/YYYY") + '<br>' + picker.startDate.format("HH:mm:"));
					}
				}
			});
		}
	});
	
	function alteraPrazo(inIdEvento, inPrazo, inNovoPrazo, tipocampo, rotulo = null) {
		switch(tipocampo) {
			case 'prazosolicitante':
				descricaoPrazo = 'Prazo Solicitante';
			break;
			case 'prazoresponsavel':
				descricaoPrazo = 'Prazo Responsável';
			break;
			default:
				descricaoPrazo = (rotulo == null) ? 'Prazo' : rotulo;
		} 

		if ($(`[name=_1_${CB.acao}_evento_${tipocampo}]`).val() == '') {
			var str = `Definiu o campo "${descricaoPrazo}"
				\n para:  ${moment(inNovoPrazo).format('DD/MM/YYYY')}`;
		} else {
			var str = `Alterou o campo "${descricaoPrazo}"
				\n de: ${$("[name=_1_"+ CB.acao +"_evento_"+ tipocampo +"]").val()}
				\n para: ${moment(inNovoPrazo).format('DD/MM/YYYY')}`;
		}

		
		$.ajax({
			type: "post",
			url: "ajax/eventoresp3.php?vopcao=add&videvento="+ idEvento + "&vobs=" + str + "&vprazo=" +
				inNovoPrazo,
			success: function(data) {
				if(data && data.error) return alertAtencao(data.error);

				alertAzul("Prazo Atualizado", "", 1000);
				atualizaComentario();
				//$('.calendarioprazo').css("background","#FFFFFF");
				console.log(moment(inNovoPrazo).format('DD/MM/YYYY'));
				console.log(inNovoPrazo);
				$("[name=_1_" + CB.acao + "_evento_prazo]").val(moment(inNovoPrazo).format('DD/MM/YYYY'));
				CB.post();
			}
		});
	}


	function alteraData(inNovaData, inNovaDatahms, inDiaInteiro, inDuracaohms, inTravaSala, inIdEquipamento, e) 
	{
		if ($("[name=_1_" + CB.acao + "_evento_inicio]").val() != '') {
			var str = "Definiu o prazo" +
				"\n para: " + moment(inNovaData).format('DD/MM/YYYY') + ' ' + inNovaDatahms;
		} else {
			var str = "Alterou a data" +
			"\n de: " +$("[name=_1_" + CB.acao + "_evento_inicio]").val() + ' ' + $("[name=_1_" + CB.acao + "_evento_iniciohms]").val() +
			"\n para: " + moment(inNovaData).format('DD/MM/YYYY') + ' ' + inNovaDatahms;
		}

		if($('#duracaohms').get().length && $('#duracaohms').val())
			str += ` até as ${$('#duracaohms').val()}`;
		
		$("[name=_1_" + CB.acao + "_evento_prazo]").val(moment(inNovaData).format('DD/MM/YYYY'));

		if (inTravaSala == 'Y' && inIdEquipamento) {

			var nurl = "ajax/eventoverificatagreserva.php?" +
				"inicio=" + inNovaData +
				"&iniciohms=" + inNovaDatahms +
				"&duracaohms=" + inDuracaohms +
				"&diainteiro=" + inDiaInteiro +
				"&idtag=" + inIdEquipamento +
				"&idevento=" + idEvento;
				
			var travado = 'false';
			$.ajax({
				type: "post", 
				url: nurl,
				async: false,
				success: function(data) {
					if (data == "true") {
						alertAzul("TAG ocupada!", "", 1000);
						travado = 'true';
						//LTM (17/08/2021): Limpar a tag caso esteja ocupada.
						$("#_1_u_evento_idequipamento").val("").cbval("");
						CB.post({
							objetos: {
								"_1_u_evento_idevento":idevento
								,"_1_u_evento_idequipamento":''
							}
							,parcial: true
							,refresh: false
							,msgSalvo: false
						});	
					} else {
						travado = 'false';
					}

				}
			});

		} else {
			var travado = 'false';
		}

		if (travado == 'false') {

			if (inIdEquipamento === undefined) {
				var idtag = '';

			} else {
				var idtag = inIdEquipamento;
			}
			$.ajax({
				type: "post",
				url: "ajax/eventoresp3.php?vopcao=add" +
					"&videvento="+ idEvento +
					"&vobs=" + str +
					"&vinicio=" + inNovaData +
					"&viniciohms=" + inNovaDatahms +
					"&duracaohms=" + inDuracaohms +
					"&diainteiro=" + inDiaInteiro +
					"&idtag=" + idtag,
				async: false,
				success: function(data) {
					if(data && data.error) return alertAtencao(data.error);

					alertAzul("Data Atualizada", "", 1000);
					atualizaComentario();
					$('.calendariotime').css("background","#FFFFFF");
					$('.calendariotime').css("color","#555555");
					$("[name=_1_" + CB.acao + "_evento_iniciohms]").attr("value", moment(inNovaDatahms, 'HH:mm').format('HH:mm'));
					$("[name=_1_" + CB.acao + "_evento_iniciohms]").attr("cbvalue", inNovaDatahms);
					$("[name=_1_" + CB.acao + "_evento_inicio]").attr("value", moment(inNovaData).format('DD/MM/YYYY'));
					e.html(data);
					CB.post();
					
					//return (data);
				}
			});
		}
	}

	function atualizaComentario() 
	{
		$("#tblComentarios tbody").html("");
		$.ajax({
			type: "ajax",
			dataType: 'json',
			url: "ajax/eventoresp3.php?vopcao=load&videvento="+idEvento,
			success: function(data) {
				if(data && data.error) return alertAtencao(data.error);

				var peopleHTML = '';
				if (data === null) {} else {
					$(this).css("background","#FFFFFF");
					for (i = 0; i < data.length; i++) {
						const HOUR = 1000 * 60 * 60;
						const anHourAgo = moment(data[i]["criadoem"]).add(68, 'minutes');
						if ("<?=addslashes($_SESSION["SESSAO"]["NOMECURTO"]);?>" == data[i]["nomecurto"] && moment(Date.now()) < anHourAgo) {
							var dl =
								'<i style="display:none" class=\"fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable\" onclick=\"excluiComentario(' +
								data[i]['idmodulocom'] + ')\" title="Excluir!"></i>';
						} else {
							var dl = '';
						}
						peopleHTML += "<tr idmodulocom=" + data[i]['idmodulocom'] + ">" +
							"<td class='tblComentariosItem'>" +
							moment(data[i]["criadoem"], 'YYYY-MM-DD HH:mm').format('DD/MM/YYYY HH:mm') +" - "+ data[i]["nomecurto"] + ": " + data[i]["descricao"] + "</td>" +
							"<td style='w'> " + dl + "</td>" +
							"</tr>";
					}
					$("#tblComentarios tbody").html(peopleHTML);
					adicionarLinkComentarios();
				}
			},
			error: function(objxml) {
				document.body.style.cursor = "default";
				alert('Erro: ' + objxml.status);
			}
		});
	}

	function copiaLink()
	{
		const input = document.createElement('input');
		document.body.appendChild(input);
		input.value = `${window.location.origin}/?_modulo=evento&_acao=u&idevento=${$("[name='_1_u_evento_idevento']").val()}`;
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

	function copiaGit(){
		const input = document.createElement('input');
		document.body.appendChild(input);
		input.value = `@${$("#idEventoTitulo label").text()} - ${$("[name='_1_u_evento_evento']").val()}`.toUpperCase();
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

	function adicionarLinkComentarios(){
		$("#tblComentarios .tblComentariosItem").each(function(i, o){
			let regex = /(https?|chrome):\/\/[^\s$.?#].[^\s]*$|[\w\.]+\.[\w]+/gm;

			if(!o.textContent.match(regex)) return true;

			let replaced = o.textContent.replaceAll(regex, function(url) {

				if(url.match(/(https?|chrome):\/\/[^\s$.?#].[^\s]*$/gm)){
					return `<a href="${url}">${url}</a>`;
				}else if(url.match(/[\w\.]+\.[\w]+/gm)){
					return `<a href="javascript:janelamodal('upload/${url}')">${url}</a>`;					
				}else{
					return url;
				}
			});

			$(o).html($.parseHTML(replaced.replaceAll('\n', '<br>')));
		});
	}

	function modalFileDropzone ( files = [] ) {
		
		let imagePreview = "";

		if(files.length == 1 && isImage(files[0].name)){
			imagePreview += `<img src="${URL.createObjectURL(files[0])}"/>`;
		}else{

			for(let file of files){
				let fileSize = humanFileSize(file.size);
				imagePreview += `
					<div class="col-md-6 filePreview" filename="${file.name}" title="${file.name}">
						<div>
							<div class="filePreviewIcon">
								<i class="fa fa-file branco"></i>
							</div>
							<div class="filePreviewInfo">
								<span class="filePreviewInfoName">${file.name}</span>
								<span>${fileSize}</span>
							</div>
							<div class="filePreviewIconRemove hidden">
								<i class="fa fa-close"></i>
							</div>
						</div>
					</div>
				`;
			}
		}

		$oModalContent = $(`
			<div id="modalFileInputMessage">
				<div>
					<input type="text" placeholder="Adicione um comentário"/>
					<button>
						<i class="fa fa-paper-plane"></i>
					</button>
				</div>
			</div>
			<div id="modalFilePreview">
				${imagePreview}
			</div>
		`);

		CB.modal({
			titulo: "Adicionar arquivo",
			corpo: [$oModalContent],
			classe: 'quarenta',
			aoAbrir: function ( modal ) {
				$("#modalFilePreview .filePreview").on('mouseover',function(){
					$(this).addClass("filePreviewRemove");
					$(this).find(".filePreviewIcon").addClass("hidden");
					$(this).find(".filePreviewInfo").addClass("hidden");
					$(this).find(".filePreviewIconRemove").removeClass("hidden");
				});

				$("#modalFilePreview .filePreview").on('mouseout',function(){
					$(this).removeClass("filePreviewRemove");
					$(this).find(".filePreviewIcon").removeClass("hidden");
					$(this).find(".filePreviewInfo").removeClass("hidden");
					$(this).find(".filePreviewIconRemove").addClass("hidden");
				});

				$("#modalFilePreview .filePreview").on('click',function(){
					if($(this).hasClass("filePreviewRemove")){
						let filename = $(this).attr('filename');
						files = files.filter( file => file.name != filename);
						$(this).remove();

						if(files.length == 0)
							$('#cbModal').modal('hide');
					}
				});

				$("#modalFileInputMessage button").on('click',function(){
					let uploadComplete = files.length - 1;
					let dropzone = Dropzone.instances.filter(instance => instance.element.id == 'mainDropzone')[0];
					dropzone.options.mensagem = $("#modalFileInputMessage input").val();

					dropzone.options.onComplete = function ( file ) {
						if(uploadComplete == 0){
							atualizaComentario($("[name=_1_u_evento_idevento]").val());
							this.onComplete = undefined;
							this.mensagem = undefined;
						}else{
							uploadComplete--;
						}
					};

					for(let file of files){
						dropzone.addFile(file);
					}
					
					$('#cbModal').modal('hide');
					alertSalvo();
				});
			}
		});
	}

	function isImage(filename) {
		let parts = filename.split('.');
		let ext = parts[parts.length - 1];
		switch (ext.toLowerCase()) {
			case 'jpg':
			case 'gif':
			case 'bmp':
			case 'png':
			//etc
			return true;
		}
		return false;
	}

	function humanFileSize(bytes, si=false, dp=1) {
		const thresh = si ? 1000 : 1024;

		if (Math.abs(bytes) < thresh) {
			return bytes + ' B';
		}

		const units = si 
			? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'] 
			: ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
		let u = -1;
		const r = 10**dp;

		do {
			bytes /= thresh;
			++u;
		} while (Math.round(Math.abs(bytes) * r) / r >= thresh && u < units.length - 1);


		return bytes.toFixed(dp) + ' ' + units[u];
	}

	//Quando aplicar o Ctrl + S e a pessoa não tiver acesso ao módulo, permitir apenas quando tiver um comentário
	$(document).keydown(function(event) 
	{
		//[ctrl]+[s] -- Liberar Para salvar o comentário, caso tenha alguma informação neste campo
		if (!(String.fromCharCode(event.which).toLowerCase() == 's' && (event.ctrlKey||event.altKey)) && !(event.which == 19) && $('[name*=_modulocom_descricao]').val())
		{
			ST.desbloquearCBPost();
		}
	});

	if($("[name=_1_u_evento_idevento]").val()){
		adicionarLinkComentarios();

		document.onpaste = function(event){

			let itens = (event.clipboardData || event.originalEvent.clipboardData).items;
			let list = [];
			for(let item of itens){
				if (item.kind === 'file') {
					list.push(item.getAsFile());
				}
			}

			if(list.length > 0)
				modalFileDropzone(list);
		}
	}

	<? if(in_array('upload', $camposObrigatorios)) 
	{ ?>
		CB.prePost = function()
		{
			if(!$(".cbupload > div").length)
			{
				alertAtencao('Campo: anexo de arquivos obrigatório.');

				return false;
			};
		};
	<? } ?>

	if(temCampoInicio === '1')
	{
		let tempoPesquisa;

		$('.calendariotimeevento').daterangepicker({
			"autoUpdateInput": true,
			"showDropdowns": false,
			"autoApply": true,
			"timePicker": false,
			"singleDatePicker": true,
			"linkedCalendars": false,
			"opens": "left",
			"locale": CB.jDateRangeLocale,
			"minDate": moment().subtract(9, 'years'),  
			"maxDate": moment().add(1, 'years'),
			"format": 'DD.MM.YYYY HH:mm'
		}).on("click", function(e, picker) {
			e.stopPropagation();
		}).on("apply.daterangepicker", function(e, picker) {
			if($('#tageventoobj').get().length)
			{
				let dataSelecionada = picker.startDate.format(picker.locale.format),
					dataDiaSeguinte = moment().add(1, 'days').format(picker.locale.format);

				if(dataSelecionada == dataDiaSeguinte)
				{
					$('#lista-horario-inicio li:first-child').click();
				}

				let verificaReserva = verificaTagReserva(dataSelecionada);
				if(verificaReserva != 'error' && verificaReserva)
				{
					return false;
				}
			}

			//$(this).html(picker.startDate.format("DD/MM/YYYY")||"");
			var html = alteraData(picker.startDate.format("YYYY-MM-DD"),$('#iniciohms').val(),$(this).closest(".eventoRow").attr("diainteiro"),$("#duracaohms").val(),$(this).closest(".eventoRow").attr("travasala"),$(this).closest(".eventoRow").attr("idequipamento"),$(this));
			picker.element.val(picker.startDate.format(picker.locale.format));
			$(this).css("background","#357ebd");
			$(this).css("color","#fff");
			$(this).closest(".eventoRow").attr("iniciodata", picker.startDate.format("DD/MM/YYYY"));
			$(this).closest(".eventoRow").attr("inicio", picker.startDate.format("YYYY-MM-DD"));
			$(this).closest(".eventoRow").attr("iniciohms", picker.startDate.format("HH:mm:ss"));
		});

		CB.prePost = () => {
			if($('#diainteiro:checked').get().length)
			{
				return (verificaTagReserva() == false);
			}

			let JQhoraInicio = $('#iniciohms'),
				JQlistaHoraInicio = $('#lista-horario-inicio li'),
				JQhoraDuracao = $('#duracaohms'),
				JQlistaHoraDuracao = $('#lista-horario-duracao li');

				return (validarHorario(JQhoraInicio, inicioHms.substr(0, 5), JQlistaHoraInicio) && validarHorario(JQhoraDuracao, duracaoHms.substr(0, 5), JQlistaHoraDuracao));
		};

		var dadosHorario = {},
			duracao = {};

		// Montar lista no campo especifico
		$('#lista-horario-inicio').append(montarListarDeHorarios(horarioComercial));

		$('#diainteiro').on('change', function() {
			let verificaReserva = verificaTagReserva();

			let JQcampoDuracao = $('#duracaohms');

			if(!this.checked && JQcampoDuracao.val() == '')
			{
				// JQcampoDuracao.val(JQcampoDuracao.attr('value'));
				// JQcampoDuracao.attr('cbvalue', duracao[JQcampoDuracao.val()]);

				JQcampoDuracao.removeAttr('disabled');
				calcularHorariosDeDuracao();
			}

			if(verificaReserva != 'error' && !verificaReserva)
			{
				CB.post({
					"prePost": false,
					"objetos": {}
				});
			}
		});

		if($('#iniciohms').val())
		{
			calcularHorariosDeDuracao();
		}

		// Evento para atribuir valor da opcao no campo
		$('.lista-select').on('click', 'li', function()
		{
			let JQlista = $($(this).parent()),
				JQcampo = $(`#${JQlista.data('target')}`);
			
			JQcampo.attr('value', $(this).data('value'));
			JQcampo.val($(this).data('value'));

			if($(this).data('duracao'))
			{
				JQcampo.attr('cbvalue', $(this).data('duracao'));
			} else {
				$('#duracaohms').attr('value', '');
			}

			removerClasseDeErro(JQcampo);
			esconderListaSelect();
			calcularHorariosDeDuracao();
		});

		// Mostrar todos os valores
		$('#iniciohms').on('dblclick', function(e)
		{
			esconderListaSelect();

			if($(this).next().children().length)
			{
				$(this).next().children().removeClass('hidden');

				return $(this).next().addClass('block');
			}

			$(this).next().addClass('block');
		});

		// Mostrar todos os valores
		$('#duracaohms').on('dblclick', function()
		{
			esconderListaSelect();
			return $(this).next().addClass('block');
		});

		// Tirar foco do campos especifico
		$('#iniciohms, #duracaohms').on('blur', function() {
			setTimeout(() => {
				esconderListaSelect();

				if($('#iniciohms').val().substr(0, 5) != inicioHms.substr(0, 5) || $('#duracaohms').attr('cbvalue').substr(0, 5) != duracaoHms.substr(0, 5))
				{
					if(!validarHorario($(this), null, $($(this).next().find('li'))) || !$('#tageventoobj').val()) return false;

					let diaInteiro = $('#diainteiro:checked').get().length ? 'Y' : 'N',
						data = {
							dia: $('#data-inicio').val().split('/')[0],
							mes: $('#data-inicio').val().split('/')[1],
							ano: $('#data-inicio').val().split('/')[2]
						};

					if(!verificaTagReserva())
					{
						alteraData(`${data.ano}-${data.mes}-${data.dia}`, $('#iniciohms').val(), diaInteiro, $('#duracaohms').attr('cbvalue'), $(this).closest(".eventoRow").attr("travasala"),$(this).closest(".eventoRow").attr("idequipamento"),$(this));
					}
				}
			}, 200);
		});

		$('#duracaohms').on('blur', function()
		{
			let JQhorarioDuracao = $($('#lista-horario-duracao li').get().filter(element => $(element).data('value') == $('#duracaohms').val().substr(0,5))).data('duracao');

			if(JQhorarioDuracao == undefined)
			{
				$(this).attr('cbvalue', 'null');
				return false;
			}

			if(JQhorarioDuracao != duracaoHms.substr(0, 5))
			{
				$(this).attr('cbvalue', JQhorarioDuracao);
				if($('#tageventoobj').val())
				{
					let verificaReserva = verificaTagReserva();
					if(verificaReserva != 'error' && !verificaReserva)
					{
						CB.post({
							"prePost": false,
							"objetos": {}
						});
					}
				}
			}
		});

		// Evento para pesquisar horario
		$('#iniciohms').on('keydown', function()
		{
			let listaDeElementos = $(this).next().children().get();

			clearInterval(tempoPesquisa);
			tempoPesquisa = setTimeout(() => {
				if(!$(this).val()) $(this).attr('value', '');

				filtrarListaDeElementos(listaDeElementos, $(this).val());
				removerClasseDeErro($(this));
			}, 400);

			mostrarListaDeHorarios('lista-horario-inicio');
		});

		// Evento para pesquisar horario
		$('#duracaohms').on('keydown', function()
		{
			let listaDeElementos = $(this).next().children().get();

			clearInterval(tempoPesquisa);
			tempoPesquisa = setTimeout(() => {
				filtrarListaDeElementos(listaDeElementos, $(this).val());

				removerClasseDeErro($(this));
			}, 400);

			mostrarListaDeHorarios('lista-horario-duracao');
		});

		function removerClasseDeErro(JQelement)
		{
			if(JQelement.hasClass('input-error'))
			{
				JQelement.removeClass('input-error');
			}
		}

		function validarHorario(JQhora, horaAntiga, JQlistaHora)
		{
			let atributoData = JQlistaHora.data('duracao') ? 'duracao' : 'value',
				atributoDeVerificacao = atributoData == 'duracao' ? JQhora.attr('cbvalue').substr(0, 5) : JQhora.val().substr(0, 5);

			if(horaAntiga == atributoDeVerificacao) return true;

			let horarioValido = JQlistaHora.get().filter(element => $(element).data('value') == JQhora.val().substr(0, 5)).length;

			if($('#iniciohms').hasClass('input-error') || $('#duracaohms').hasClass('input-error'))
			{
				alertAtencao('Horário inválido. Selecionar um dos valores apresentados.');
				return false;
			}

			if((!horarioValido || horarioValido == undefined))
			{
				JQhora.addClass('input-error');
				alertAtencao('Horário inválido. Selecionar um dos valores apresentados.');
				return false;
			}

			return true;
		}

		function filtrarListaDeElementos(listaDeElementos, busca)
		{
			for(let chave in listaDeElementos)
			{
				if($(listaDeElementos[chave]).data('value').split(':')[0].indexOf(busca) === -1) 
				{
					$(listaDeElementos[chave]).addClass('hidden');
					continue;
				}

				$(listaDeElementos[chave]).removeClass('hidden');
			}
		}

		function montarListarDeHorarios(horarios, duracao = false)
		{
			let elementoLi = '',
				dataAtual = new Date(),
				campoDataInicio = $('#data-inicio'),
				atributoDuracao;

			let dataInicio = `${campoDataInicio.val().split('/')[2]}/${campoDataInicio.val().split('/')[1]}/${campoDataInicio.val().split('/')[0]}`

			for(let chave in horarios)
			{
				atributoDuracao = '';

				if(duracao) atributoDuracao = `data-duracao=${duracao[chave]}`;

				elementoLi += `<li data-value="${chave}" ${atributoDuracao}>
									${horarios[chave]}
							</li>`;
			}

			return elementoLi;
		}

		function esconderListaSelect()
		{
			$('.lista-select').removeClass('block');
		}

		function mostrarListaDeHorarios(id = false)
		{
			if(id)
			{
				return $(`#${id}`).addClass('block');
			}

			$('.lista-select').addClass('block');
		}

		function calcularHorariosDeDuracao()
		{
			let JQhorarioInicioReserva = $('#iniciohms'),
				JQduracaoReserva = $(duracaoDaReserva),
				duracaoPadrao = JQduracaoReserva.attr('cbvalue').substr(0, 5);

			removerClasseDeErro(JQhorarioInicioReserva);

			if($('#diainteiro:checked').get().length)
			{
				return JQduracaoReserva.attr('disabled', true);
			}

			let horaDeInicio = parseInt(JQhorarioInicioReserva.val().split(':')[0]),
				minutoDeInicio = parseInt(JQhorarioInicioReserva.val().split(':')[1]);

			let option = "";

			let hora,
				minuto;

			dadosHorario = {},
			duracao = {};

			if(JQduracaoReserva.next().children().length)
			{
				JQduracaoReserva.next().children().remove();	
			}

			for(let horario in JtimeDuracao)
			{
				let horaDuracao = parseInt(horario.split(':')[0]),
					minutoDuracao = parseInt(horario.split(':')[1]);

				hora = horaDeInicio;

				if(horaDuracao)
				{
					hora += horaDuracao;

					if(hora > 23)
					{
						hora -= 24;
					}
				}

				minuto = (minutoDeInicio * 3600) + (minutoDuracao * 3600);

				if(minuto / 3600 >= 60)
				{
					hora++;
					if (hora > 23) hora -= 24;

					minuto = (minuto / 3600) % 60;
				} else 
				{
					minuto /= 3600;
				}

				dadosHorario[`${hora.toString().padStart(2, '0')}:${minuto.toString().padStart(2, '0')}`] = `${hora.toString().padStart(2, '0')}:${minuto.toString().padStart(2, '0')} (${JtimeDuracao[horario]})`;
				duracao[`${hora.toString().padStart(2, '0')}:${minuto.toString().padStart(2, '0')}`] = horario;

				if(duracaoPadrao && (duracaoPadrao == horario))
				{
					JQduracaoReserva.attr('value', `${hora.toString().padStart(2, '0')}:${minuto.toString().padStart(2, '0')}`);
				}
			};

			if(duracaoPadrao == 'null' || duracaoPadrao == '00:00' || duracaoPadrao == '')
			{
				JQduracaoReserva.attr('value', dadosHorario[Object.keys(dadosHorario)[0]]);
				JQduracaoReserva.val(dadosHorario[Object.keys(dadosHorario)[0]]);
				JQduracaoReserva.attr('cbvalue', duracao[Object.keys(dadosHorario)[0]]);
				removerClasseDeErro(JQduracaoReserva);
			}

			JQduracaoReserva.next().append(montarListarDeHorarios(dadosHorario, duracao));

			JQduracaoReserva.removeAttr('disabled');
		}
	}

	if($('.calendario')){
		$('.calendario').each((i,e)=>{
			$(e).on('apply.daterangepicker', function(e2, picker){

				if($(e2.target).attr("name") == "_1_u_evento_prazosolicitante" || $(e2.target).attr("name") == "_1_u_evento_prazoresponsavel")
				{
					nomeCampo = ($(e2.target).attr("name") == "_1_u_evento_prazosolicitante") ? 'prazosolicitante' : 'prazoresponsavel';					
					alteraPrazo($(this).data('idevento'), $(this).closest(".eventoRow").attr("prazo"), picker.startDate.format("YYYY-MM-DD"), nomeCampo);	
				}

				if($(e2.target).attr("name") == "_1_u_evento_prazo" || $(e2.target).attr("name") == "_1_u_evento_datafim")
				{
					nomeCampo = ($(e2.target).attr("name") == "_1_u_evento_datafim") ? 'datafim' : 'prazo';
					alteraPrazo($(this).data('idevento'), $(this).closest(".eventoRow").attr("prazo"), picker.startDate.format("YYYY-MM-DD"), nomeCampo, $(e2.target).attr("rotulo"));
				}

				picker.element.val(picker.startDate.format(picker.locale.format));
			});
		})
	}

	$(document).ready(function() {
		let JQcampoCalendario = $('.calendarioprazo, .calendariotime, .calendariotimeevento');

		if(JQcampoCalendario.get().length)
		{
			setTimeout(() => {
				JQcampoCalendario.each((key, element) => {
					if($(element).hasClass('calendarioprazo') && !$(element).data('daterangepicker'))
					{
						$(element).daterangepicker({
							"autoUpdateInput": false,
							"singleDatePicker": true,
							"showDropdowns": true,
							"linkedCalendars": false,
							"opens": "left",
							"locale": CB.jDateRangeLocale
						}).on("click", function(e, picker) {
							e.stopPropagation();
						}).on("apply.daterangepicker", function(e, picker) {
							if($('#tageventoobj').get().length)
							{
								let verificaReserva = verificaTagReserva(picker.startDate.format(picker.locale.format));
								if(verificaReserva != 'error' && verificaReserva)
								{
									return false;
								}
							}

							alteraPrazo($(this).data('idevento'), $(this).closest(".eventoRow").attr("prazo"), picker.startDate.format("YYYY-MM-DD"), 'prazo');
							picker.element.val(picker.startDate.format(picker.locale.format));
							$(this).html(picker.startDate.format("DD/MM")||"");
							$(this).css("background","#357ebd");
							$(this).closest(".eventoRow").attr("prazo", picker.startDate.format("YYYY-MM-DD"));
						});
					}

					$(element).data('daterangepicker').setStartDate(dataTarefa);
					$(element).data('daterangepicker').setEndDate(dataTarefa);
					$(element).data('daterangepicker').element.val($(element).data('daterangepicker').startDate.format($(element).data('daterangepicker').locale.format));
				});
			}, 400);
		}

		if(acao != 'i' && $('.checklist').get().length)
		{
			let JQchecklist = $('.checklist'),
				JQchecklistHabilitarCampo = JQchecklist.find('.habilitar-campo'),
				JQchecklistGrupoDeItems = JQchecklist.find('.checklist-group'),
				JQchecklistCampo = JQchecklist.find('.checklist-input textarea'),
				JQchecklistControles = JQchecklist.find('.checklist-controls'),
				JQchecklistItem = $('.checklist-item'),
				JQchecklistItemCampoEdicao = $('.checklist-item-edit'),
				JQchecklistItemTitulo = $('.checklist-item-text'),
				JQchecklistBtnSalvar = $('.checklist-save'),
				JQchecklistProgress = $('.checklist-progress');

			JQchecklist.on('click','.checklist-item-text', habilitarEdicao);
			JQchecklist.on('click','.checklist-save', salvarItem);
			JQchecklist.on('click', '.checklist-cancel-edit', _ => $('.checklist-item.editing').removeClass('editing'));

			JQchecklistItemCampoEdicao.on('keydown',  e => {
				e.stopPropagation();
				if(e.shiftKey) return true;
				if(e.keyCode == 13) return salvarItem();
			})

			JQchecklistHabilitarCampo.on('click', function()
			{
				JQchecklist.addClass('active');
				JQchecklistCampo.focus();
			});

			JQchecklistControles.find('.adicionar').on('click', adicionarItem);
			JQchecklistControles.find('.cancelar').on('click', cancelar);
			JQchecklistCampo.on('keydown', e => {
				e.stopPropagation();

				if(e.shiftKey) return true;
				if(e.keyCode == 13) return adicionarItem();
				if(e.keyCode == 27) return cancelar();
			});

			$(window).on('keyup', e => {
				e.stopPropagation();

				if(e.keyCode == 27)
				{
					// alert('teste');
					cancelar();

					return false;
				};
			})

			JQchecklist.on('click', '.checklist-remove', function ()
			{
				removerItem($(this).data('idchecklistitem'));
			});

			JQchecklist.on('click','.checklist-item input[type="checkbox"]', function()
			{
				let idChecklistItem = this.id.split('-')[1];
				atualizarPorcentagem();

				let parametros = {
					dados: {
						ideventochecklistitem: idChecklistItem,
						checked: (this.checked ? 'Y' : 'N')
					},
					typeParam: 'array'
				};

				ajaxEventoChecklist('atualizarEventoChecklistItem', parametros);
			});

			if(JQchecklist.data('obrigatorio'))
			{
				CB.prePost = function()
				{
					if(!JQchecklistGrupoDeItems.children().length)
					{
						alertAtencao('Item do checklist obrigatório.');

						return false;
					};
				};
			}

			function habilitarEdicao()
			{
				JQchecklist.find('.editing').removeClass('editing');
				$(this).parent().addClass('editing');

				moverCursorParaOFinal(JQchecklist.find('.editing textarea').get(0));

				// JQchecklist.find('.editing textarea').focus();
			}

			function salvarItem()
			{				
				let JQchecklistItemEditando = $('.checklist-item.editing'),
				    JQchecklistItemTitulo = JQchecklistItemEditando.find('.checklist-item-text span'),
					JQchecklistItemTituloEditado = JQchecklistItemEditando.find('.checklist-item-edit textarea');

				if(!JQchecklistItemTituloEditado.val() || !JQchecklistItemTituloEditado.val().replaceAll('\n', ''))
				{
					return removerItem(JQchecklistItemEditando.find('[data-idchecklistitem]').data('idchecklistitem'));
				}

				JQchecklistItemTitulo.text(JQchecklistItemTituloEditado.val());

				let parametros = {
					dados: {
						ideventochecklistitem: JQchecklistItemEditando.find('[data-idchecklistitem]').data('idchecklistitem'),
						titulo: JQchecklistItemTituloEditado.val()
					},
					typeParam: 'array'
				};

				$('.checklist-item.editing').removeClass('editing');

				ajaxEventoChecklist('atualizarEventoChecklistItem', parametros);
			}
			
			function cancelar(e)
			{
				JQchecklist.removeClass('active');
				JQchecklistCampo.val('');
			}

			function adicionarItem()
			{
				let JQcheckListAtiva = $('.checklist.active'),
					idEventoChecklist = JQcheckListAtiva.data('ideventochecklist');

				if(!JQchecklistCampo.val() || !JQchecklistCampo.val().replaceAll('\n', '')) return cancelar();

				let parametros = {
					dados: {
						ideventochecklist: idEventoChecklist,
						titulo: JQchecklistCampo.val(),
						idempresa: JQchecklist.data('idempresa')
					},
					typeParam: 'array'
				};

				let onSuccess = res => {
					let dados = JSON.parse(res);

					JQchecklistGrupoDeItems.append(criarItemChecklist(`${dados.ideventochecklistitem}`, JQchecklistCampo.val()));
					JQchecklistCampo.val('');
					JQchecklistCampo.focus();
					atualizarPorcentagem();	
				}

				ajaxEventoChecklist('inserirEventoChecklistItem', parametros, onSuccess);
			}

			function removerItem(idItem)
			{
				$(`.checklist-item-${idItem}`).remove();
				atualizarPorcentagem();
				ajaxEventoChecklist('removerEventoChecklistItem', idItem);
			}

			function ajaxEventoChecklist(metodo, parametros, success = false, error = false)
			{
				let ajaxConfig = {
					method: 'POST',
					url: '/ajax/eventochecklist.php',
					data: {
						action: metodo,
						params: parametros,
					},
					success,
					error
				}

				if(success) ajaxConfig.success = success;
				if(error) ajaxConfig.error = error;

				$.ajax(ajaxConfig);
			}

			function criarItemChecklist(id, label)
			{
				return `<div class="checklist-item checklist-item-${id}">
							<div>
								<input id="${id}" type="checkbox" type="checkbox" value="Y" />
								<label for="${id}"><i class="fa fa-check text-gray-10"></i></label>
							</div>
							<div class="checklist-item-text">
								<span>${label}</span>
							</div>
							<div class="checklist-item-edit">
								<textarea name="" id="">${label}</textarea>
								<button class="mt-2 btn btn-primary btn-xs checklist-save">Salvar</button>
								<button class="mt-2 btn btn-secondary btn-xs checklist-cancel-edit">Cancelar</button>
							</div>
							<i class="fa fa-trash checklist-remove" data-idchecklistitem="${id}"></i>
						</div>`;
			}

			function atualizarPorcentagem()
			{
				let eventoCheckistItens = JQchecklistGrupoDeItems.children().length;
				let porcentagem = parseInt(((eventoCheckistItens ? (100 / eventoCheckistItens) : 0) * JQchecklistGrupoDeItems.find('input:checked').get().length).toFixed());
				JQchecklistProgress.find('span').text(`${porcentagem}%`);
				JQchecklistProgress.find('.checklist-progress-bar').css('backgroundSize', `${porcentagem}%`);

				if(porcentagem == 100)
				{
					return JQchecklistProgress.find('.checklist-progress-bar').addClass('finished');
				}

				JQchecklistProgress.find('.checklist-progress-bar').removeClass('finished')
			}

			function moverCursorParaOFinal(end) {
				var len = end.value.length;
				
				// Mostly for Web Browsers
				if (end.setSelectionRange) {
					end.focus();
					end.setSelectionRange(len, len);
				} else if (end.createTextRange) {
					var t = end.createTextRange();
					t.collapse(true);
					t.moveEnd('character', len);
					t.moveStart('character', len);
					t.select();
				}
			}

			atualizarPorcentagem();
		} else 
		{
			$('#insert-checklist').on('click', function()
			{
				CB.post({
					objetos: {
						_1_i_eventochecklist_idevento: idEvento,
						_1_i_eventochecklist_idempresa: gIdEmpresa
					}
				});
			});
		}
	});

	if($('.tbTags tbody').get().length)
	{
		$('.tbTags tbody').sortable({
			axis: 'y',
			handle : ".move",
			update: function(event, ui) {
				let posicaoAtual = ui.item.index();

				CB.post({
					objetos: {
						_x_u_eventoobj_ideventoobj: ui.item.data('ideventoobj'),
						_x_u_eventoobj_ord: posicaoAtual
					},
					refresh: false,
					parcial: true
				});
			}
		});
	}

	//Autocomplete de Documentos vinculados
	$("#docvinc").autocomplete({
		source: $jDocvinc
		,delay: 0
		,create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);

			};
		}
		,select: function(event, ui){
			CB.post({
				objetos: {
					"_x_i_objetovinculo_idobjeto": idEvento
					,"_x_i_objetovinculo_tipoobjeto": 'evento'
					,"_x_i_objetovinculo_idobjetovinc": ui.item.value
					,"_x_i_objetovinculo_tipoobjetovinc":  'sgdoc'
				}
				// ,parcial: true
			});
		}
	});

	function desvincularDocumento(idObjetoVinculo)
	{
		if(!confirm('Remover este documento!?')) return;

		CB.post({
			objetos: {
				"_x_d_objetovinculo_idobjetovinculo": idObjetoVinculo
			}
		});
	}

	function adicionarLink() {
		const link = $('#inputlinkmultiplo').val();
		const titulo = $('#input_titulo_link').val();

		if(!validarLink(link)) {
			alertAtencao('Link inválido!');
			return;
		}

		CB.post({
			objetos: {
				"_x_i_eventolink_idevento": idEvento,
				"_x_i_eventolink_titulo": titulo,
				"_x_i_eventolink_link": link
			}
		});
	}

	function removerLink(idEventoLink) {
		if(!confirm('Remover link do evento?')) return;

		CB.post({
			objetos: {
				"_x_d_eventolink_ideventolink": idEventoLink
			}
		});
	}

	function validarLink(link) {
		const regexLink = /^(https?:\/\/)?([\w.-]+)\.([a-z]{2,})(\/\S*)?$/i;
		return regexLink.test(link);
	}

	function eventoapontamento()
	{
		var acomphoras = $("#acomphoras").val();
		var descr = $("#evento_descr").val();
		var relacionamento = $("#evento_relacionamento").val();

		if(!acomphoras){
			alert("Informar o tempo gasto.");
		}else{
			if(acomphoras.length == 5 || acomphoras.length == 6 ){

				var partes = acomphoras.split(':');
				var horas = parseInt(partes[0], 10);
				var minutos = parseInt(partes[1], 10);
				var segundos = parseInt(partes[2], 10) || 0;
				var decimal = horas + minutos / 60 + segundos / 3600;			
				var decimal2 = decimal.toFixed(2); // Arredonda para 2 casas decimais

				if($('[name="evento_relacionamento"]').length > 0){
					CB.post({
						objetos: {
							"_x_i_eventoapontamento_idevento": idEvento,
							"_x_i_eventoapontamento_valor": acomphoras,
							"_x_i_eventoapontamento_valordecimal": decimal2,
							"_x_i_eventoapontamento_ideventorelacionamento": relacionamento,
							"_x_i_eventoapontamento_descr": descr
						}
						// ,parcial: true
					});
				} else {
					CB.post({
						objetos: {
							"_x_i_eventoapontamento_idevento": idEvento,
							"_x_i_eventoapontamento_valor": acomphoras,
							"_x_i_eventoapontamento_valordecimal": decimal2,
							"_x_i_eventoapontamento_descr": descr
						}
						// ,parcial: true
					});
				}
			}
		}
	}

	
	function atualizaprev(vthis)
	{

	

		var prev=$(vthis).val();
		var valoratual=$(vthis).attr( "valoratual" );
	/*
		var regex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;

		if (!regex.test(prev)) {
			alert("Por favor, insira um formato de hora válido (HH:mm).");
			// Você pode limpar o campo ou tomar outras ações, se necessário
		}
		*/

		if((prev.length==5  && valoratual.substring(5, 0) != prev) || (prev.length==6  && valoratual.substring(6, 0) != prev)){


			var partes = prev.split(':');
			var horas = parseInt(partes[0], 10);
			var minutos = parseInt(partes[1], 10);
			var segundos = parseInt(partes[2], 10) || 0;
			var decimal = horas + minutos / 60 + segundos / 3600;			
			var decimal2= decimal.toFixed(2); // Arredonda para 2 casas decimais
			
		
			CB.post({
				objetos: {
					"_x_u_evento_idevento": idEvento
					,"_x_u_evento_prevhoras": prev
					,"_x_u_evento_prevhorasdec": decimal2
					,"_comentario_i_modulocom_idmodulo":idEvento
					,"_comentario_i_modulocom_modulo": "evento"
					,"_comentario_i_modulocom_status": "ATIVO"
					,"_comentario_i_modulocom_descricao": "Alterou a previsão de execução para "+prev+" hora(s)."
					
				}
				// ,parcial: true
			});
		}
		
	}

	function mostrarSomenteVinculados(vcolvinc, vcol, value){
		campoSelecionado = $(value).val();
		campoSelecionadoold = $(`.${vcol}_old`).val();

		campoEventoTipo = vcolvinc.split(',');

		$.each(campoEventoTipo, function(n1, elem1){
			$(`.${elem1} option`).each(function (n, elem){ 
				campo = $(elem).attr('campo');
				if(campo == campoSelecionado){
					$(elem).css('display','block');
				} else{
					$(elem).css('display','none');
				}

				if(campoSelecionadoold != campoSelecionado){
					$(elem).removeAttr('selected')
				}
			});
		});
	}

	function criaSolmat(idEventoAdd) {
		CB.modal({
			header:"Solicitação de materias",
			url: `/?_modulo=solmat&_acao=i&idevento=${idevento}&idEventoAdd=${idEventoAdd}&_idempresa=${gIdEmpresa}`,
			aoFechar: (param) => {
				$.ajax({
					type: "get",
					url : "ajax/evento.php",
					data: { 
						vopcao : "buscarSolmatsVinculadas",
						idevento: idevento,
						ideventoadd: idEventoAdd
					},
					success: function(data){
						const solmatArr = JSON.parse(data)

						if(!solmatArr.length) {
							console.log('Nenhuma solmat adicionada!');
							return;
						};

						const cor = "";
						const solmat = solmatArr[solmatArr.length - 1];
						const eventoAddDiv = $(`#vinculos-eventoadd-${solmat.ideventoadd}`);
						const solmatHTML = `<a target="_blank" href="?_modulo=solmat&_acao=u&idsolmat=${solmat.idsolmat}&_idempresa=${solmat.idempresa}">
													<div class="row"
														style="color:#333 !important;padding:8px; position:relative;">
														<div class="col-lg-2 col-sm-3 col-xs-6 atalhoEvento" style="font-size: 12px; color: #333333;">
															<div class="col-lg-12 col-xs-12" style="font-size: 12px;text-align:center">
																<div
																	style="border-radius:15px;border:1px solid;padding: 2px 6px;font-size:9px;word-break:normal;text-align:center;text-transform:uppercase;">
																	Solicitação
																</div>
															</div>
														</div>

														<div class="col-lg-4 col-sm-9 col-xs-12 atalhoHist"
															style="display: block; word-break: break-word;font-size: 12px;">
															<div class="col-lg-12 col-xs-12 descricao"
																style="display:flex; flex-direction:column;min-height: 24px;border-bottom:1px solid #ddd;text-transform:uppercase;font-size:10px;margin:0px 12px;">
																<div>
																	<strong>Unidade origem:</strong> <span>${solmat.unidadeOrigem}</span>
																</div>
																<div>
																	<strong>Unidade destino:</strong> <span>${solmat.unidadeDestino}</span>
																</div>
															</div>
														</div>
														<div class="col-lg-5 col-sm-8 col-xs-12 atalhoPart" style="font-size: 12px;">
															<div class="col-lg-6 col-xs-12 " style="font-size: 12px;">
																<div
																	style="display:flex; flex-direction:column;border-radius:15px;width:100%;text-transform:uppercase;padding: 2px 6px;font-size:9px;word-break:normal;text-align:center;">
																	<strong>Status</strong>
																	<span>${solmat.status}</span>
																</div>								
															</div>
															<div class="col-lg-3 col-xs-12 origem" style="display:flex; flex-direction:column;font-size: 10px; color: #333;">
																<strong>
																	CRIADO POR
																</strong>
																<span>${solmat.nomecurto ?? 'Usuário desconhecido'}</span>
															</div>
														</div>
													</div>
												</a>`;

						eventoAddDiv.append(solmatHTML);
					},
					error: function(objxmlreq){
						alert('Erro:<br>'+objxmlreq.status); 
					}
				});
			}
		})
	}

	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>