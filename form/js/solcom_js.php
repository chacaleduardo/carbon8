<script>
	//------- Injeção PHP no Jquery -------
	var jprodserv = <?=json_encode($jprodserv['dados'])?> || [];
    var jprodservFabricado = <?=json_encode($jprodservFabricado['dados'])?> || [];
	var removerHiddenReprovado = <? echo ($qtdItensSolcomCancelados > 0) ? 1 : 0 ?>;
	var cbIdempresa = <?=cb::idempresa();?>;
	var idsolcom = '<?=!empty($_1_u_solcom_idsolcom) ? $_1_u_solcom_idsolcom : '' ?>';
	var idpessoa = <?=$_SESSION["SESSAO"]["IDPESSOA"]?>;
    var qtdComentario = '<?=!empty($qtdComentario) ? $qtdComentario : '' ?>';
	//------- Injeção PHP no Jquery -------

	//------- Funções JS -------
	$(".idprodservNaoCadastradoItemSolcom").autocomplete({
		source: jprodserv,
		delay: 0,
		create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
			    return $('<li>').append("<a>"+item.descr+" - "+item.un+" - <span style='color:#b6b6b6;'>"+item.codprodserv+"</span></a>").appendTo(ul);
			};
		},
		select: function(event, ui){
			CB.post({
				objetos:{
					"_s_u_solcomitem_idsolcomitem": $(this).attr('cbvalue'),
					"_s_u_solcomitem_idprodserv": ui.item.idprodserv,
				},
				parcial: false            
			});
		}
	});

	$(".modalimagens").click(function()
	{
		var idsolcomitem = $(this).attr('idsolcomitem');
		var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa="+getUrlParameter("_idempresa") : '';
			
		CB.modal({
			url: `?_modulo=solcomimagensitens&_acao=u&idsolcomitem=${idsolcomitem}${idempresa}`,
			header: `Imagens do Produto`            
		});
	});

	$(".modalobsinterna").click(function()
	{   
		let mostrarobs = $(this).attr('mostrarobs');
		let idnf = $(this).attr('idnf');
		let idprodserv = $(this).attr('idprodserv');

		if(mostrarobs)
		{
			var obsinterna = $(`#modalobsinterna${idnf}${idprodserv}`)[0].innerHTML;
			CB.modal({
				corpo: obsinterna,
				titulo: "Observação",
				classe: 'oitenta'
			});
		}
	});

	$(".modalUrgente").click(function()
	{
		let idsolcomitem = $(this).attr('idsolcomitem');
		let checked = $(this).attr('checked');
		let div = '';
		$.ajax({
			method: 'post'
			,url: 'ajax/solcomitem.php'
			,data: {
				"idsolcomitem": idsolcomitem
			}
		}).done(function(retorno,texto,jqXHR){	
			div += '<div class="panel panel-default" style="margin-top: -5px !important;">';
				div += '<div class="panel-body">';
					div += '<table>';
					div += '<tr><td style="text-align: right;">Urgente:</td>';
					div += '<td><input type="checkbox" id="urgente" name="urgente" '+checked+'></td></tr>';
					div += '<tr><td style="text-align: right;">Justificativa:</td>';
					div += '<td><select name="justificativa" id="justificativa" onchange="justificativa(this)" vnulo>';
						div += '<option></option>';
						div += '<option value="Aumento de Consumo não Previsto">Aumento de Consumo não Previsto</option>';
						div += '<option value="Material Segurança do Trabalho">Material Segurança do Trabalho</option>';
                        div += '<option value="Serviço não Previsto">Serviço não Previsto</option>';
						div += '<option value="MOTIVO">Motivo</option>';
					div += '</select></td></tr>';
					div += '<input type="hidden" id="urgente2" name="urgente2" value="">';
					div += '<tr><td style="text-align: right;">Prazo:</td><td><input type="text" class="calendario" style="width:15em;" id="dataprevisao" name="dataprevisao"></td></tr>';
					div += '<tr><td style="display: none; text-align: right;" id="tdmotivodesc">Descreva o Motivo:</td><td style="display: none;" id="tdmotivoarea"><textarea name="motivo" id="motivo" vnulo></textarea></td>';
					div += '</table>';
				div += '</div>';
			div += '</div>';
			div += '<br /><br />';

			if(retorno)
			{
				let dados = JSON.parse(retorno);
				div += '<div class="modal-header" style="margin-bottom: -37px;">';
					div += '<h4 class="modal-title" id="cbModalTitulo">Histórico Motivo</h4>';
				div += '</div>';
				div += '<div id="cbModalCorpo" class="modal-body">';
					div += '<div class="row">';
							div += '<div class="panel panel-default">';
								div += '<div class="panel-body">';
									div += '<table class="table table-striped planilha">';
										div += '<tr><th>Descrição</th><th>Data Previsão</th><th>Criado em</th><th></th></tr>';
										for(let key of Object.keys(dados))
										{
											let comentario = dados[key];
											div += `<tr><td>${comentario.descricao}</td><td>${comentario.dataprevisao}</td><td>${comentario.criadoem}</td></tr>`;
										}
									div += '</table>';
								div += '</div>';
						div += '</div>';
					div += '</div>';
				div += '</div>';
			}

			CB.modal({
				corpo: div,
				titulo: `Motivo Urgência <button type="button" class="btn btn-success btn-xs" onclick="salvarComentario(${idsolcomitem})" title="Salvar" style="float: right; margin-top: 14px; margin-right: 8px; display: block;">
							<i class="fa fa-circle"></i>Salvar</button>`,
				classe: 'quarenta'
			});	
			$('input[name="dataprevisao"]').daterangepicker({
				timePicker: true,
				"singleDatePicker": true,
				"showDropdowns": true,
				"linkedCalendars": false,
				"opens": "left",
				"locale": {format: 'DD/MM/YYYY h:mm'}
			});
		});	
	});

	$(".detalheSolcom").click(function()
	{
		let idsolcomitem = $(this).attr('idsolcomitem');
		let strCabecalho = `</strong>Local Compra / Observação</strong> <button type="button" class="btn btn-success btn-xs" onclick="CB.post()" title="Salvar" style="float: right; margin-top: 14px; margin-right: 8px; display: block;">
			<i class="fa fa-circle"></i>Salvar
		</button>`;
		let localcompra = $(`#modallocalcompra${idsolcomitem}`)[0].innerHTML;
		$('#cbModal').removeClass('url');
		$('#cbModal').removeClass('cinquenta');
		$('#cbModal').removeClass('setenta');
		$('#cbModal').addClass('quarenta');
		$('#cbModal').addClass('titulo');
		$('#cbModal').addClass('fade');
		$('#cbModal').attr("modal", "modallocalcompraobs");
		$("#cbModalTitulo").html((strCabecalho));
		$("#cbModalCorpo").html(localcompra);
		$('#cbModal').modal('show');
	});

	$(".modalsolmat").click(function()
	{   
        let strCabecalho = `</strong>Criar Solicitação de Materiais</strong> 
                            <button type="button" class="btn btn-success btn-xs" onclick="criarSolmat(${idsolcom})" title="Salvar" style="float: right; margin-top: 14px; margin-right: 8px; display: block;">
			                    <i class="fa fa-circle"></i>Salvar
		                    </button>`;
		var modalsolmat = $("#modalsolmat")[0].innerHTML;
		CB.modal({
			corpo: modalsolmat,
			titulo: strCabecalho,
			classe: 'setenta'
		});
	});

    $(".modalsoltag").click(function()
	{   
        let strCabecalho = `</strong>Criar Solicitação de Materiais</strong> 
                            <button type="button" class="btn btn-success btn-xs" onclick="criarSoltag(${idsolcom})" title="Salvar" style="float: right; margin-top: 14px; margin-right: 8px; display: block;">
			                    <i class="fa fa-circle"></i>Salvar
		                    </button>`;
		var modalsoltag = $("#modalsoltag")[0].innerHTML;
		CB.modal({
			corpo: modalsoltag,
			titulo: strCabecalho,
			classe: 'setenta'
		});
	});

	if(removerHiddenReprovado)
	{
		$(`#satus_reprovadocad`).removeAttr("hidden");
		$(`#satus_reprovadoncad`).removeAttr("hidden");
	}

	//Quando aplicar o Ctrl + S e a pessoa não tiver acesso ao módulo, permitir apenas quando tiver um comentário
	$(document).keydown(function(event) 
	{
		//[ctrl]+[s] -- Liberar Para salvar o comentário, caso tenha alguma informação neste campo
		if (!(String.fromCharCode(event.which).toLowerCase() == 's' && (event.ctrlKey||event.altKey)) && !(event.which == 19) && $('[name*=_99_i_modulocom_descricao]').val())
		{
			ST.desbloquearCBPost();

        } else if (!(String.fromCharCode(event.which).toLowerCase() == 's' && (event.ctrlKey||event.altKey)) && !(event.which == 19) && $('[name*=u_solcomitem_obs]').val()) {
            alteraAlturaTextArea();
        }
	});

	//Esconde Comentário
	$(document).keydown(function(e) {
	    if (e.keyCode == 27) {
            $('#cbPanelBotaoFlutuante').hide();
        }
    });

	if($("[name=_1_u_solcom_idsolcom]").val() ){
        $(".cbupload").dropzone({
            idObjeto: $("[name=_1_u_solcom_idsolcom]").val(),
            tipoObjeto: 'solcom',
            idPessoaLogada: idpessoa
        });
    }

    $("#fotoproduto").dropzone({
		url: "form/_arquivo.php",
		idObjeto: '<?=$_idsolcomitem?>',
		tipoObjeto: 'solcomitem',
		tipoArquivo: 'FOTOPRODUTO',
		caminho: 'upload/fotoproduto/',
		sending: function(file, xhr, formData) {
			formData.append("idobjeto", this.options.idObjeto);
			formData.append("tipoobjeto", this.options.tipoObjeto);
			formData.append("tipoarquivo", this.options.tipoArquivo);
			formData.append("caminho", this.options.caminho);
		},
		success: function(file, response) {
			this.options.loopArquivos(response);
		},
		init: function() {
			var thisDropzone = this;
			$.ajax({
				url: this.options.url + "?caminho="+this.options.caminho+"&tipoobjeto=" + this.options.tipoObjeto + "&idobjeto=" + this.options.idObjeto + "&tipoarquivo=" + this.options.tipoArquivo
			}).done(function(data, textStatus, jqXHR) {
				thisDropzone.options.loopArquivos(data);
			})
		},
		loopArquivos: function(data) {
			jResp = jsonStr2Object(data);
			if (jResp.length > 0) {
				nomeArquivo = jResp[jResp.length - 1].nome;
				if (nomeArquivo) {
					$("#fotoproduto").attr("src", "upload/fotoproduto/" + nomeArquivo);
				}
			}
		}
	});

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

        if(qtdComentario == 0)
        {   
            $('.fa-comment').removeClass('azul');
            $('.fa-comment').addClass('cinza');
        } else {
            $('.fa-comment').removeClass('cinza');
            $('.fa-comment').addClass('azul');
        }

        //Aumenta o Tamanho do TextArea de acordo com o conteúdo inserido.
        alteraAlturaTextArea();
    });
	//------- Funções JS -------

	//------- Funções Módulo -------
	function modalOrcamento(idsolcomitem)
    {
        CB.modal({
            corpo: $("#modalOrcamento"+idsolcomitem).html(),
            titulo: "Orçamentos Disponíveis",
            classe: 'oitenta'
        });	
    }

	function motivoReprovarItem(idsolcomitem)
    {
        $(`#_99rep${idsolcomitem}_i_modulocom_idmodulo`).removeAttr("disabled");
        $(`#_99rep${idsolcomitem}_i_modulocom_modulo`).removeAttr("disabled");
        $(`#_99rep${idsolcomitem}_i_modulocom_descricao`).removeAttr("disabled");
        $(`#_rep${idsolcomitem}_u_solcomitem_idsolcomitem`).removeAttr("disabled");
        $(`#_rep${idsolcomitem}_u_solcomitem_status`).removeAttr("disabled");
        $(`#_rep${idsolcomitem}_u_solcomitem_descr`).removeAttr("disabled");
        CB.modal({
            corpo: $("#modalmotivoreprocacao"+idsolcomitem).html(),
            titulo: "Motivo Reprovação",
            classe: 'sessenta'
        });	
    }

	function restaurarItemSolcom(idsolcomitem)
    {
        CB.post({
            objetos: {
                "_sia_u_solcomitem_idsolcomitem": idsolcomitem,
                "_sia_u_solcomitem_idcotacao": '',
                "_sia_u_solcomitem_status": 'PENDENTE',
            }
        });
    }

	function excluirItemSolcom(idsolcomitem)
    {
        CB.post({
            objetos: {
                "_sia_d_solcomitem_idsolcomitem": idsolcomitem,
            },
            parcial: true
        });
    }

	function atualizaCampoLink(comentario, idsolcomitem = false)
    {
        if(idsolcomitem){
            $(`.linkCotacao${idsolcomitem}`).val(comentario.value);
        }
    }

	function insereLink(idsolcomitem) 
    {
        link = $(`.linkCotacao${idsolcomitem}`).val();
        if(link)
        {
            CB.post({
                objetos: {
                    "_x_i_objetolink_idempresa": cbIdempresa,
                    "_x_i_objetolink_idobjeto": idsolcomitem,
                    "_x_i_objetolink_tipoobjeto": 'solcomitem',
                    "_x_i_objetolink_link": link
                },
                parcial: true,
                posPost: function(data, textStatus, jqXHR){
                    var localcompra = $(`#modallocalcompra${idsolcomitem}`)[0].innerHTML;
                    $("#cbModalCorpo").html(localcompra);

                    //Aumenta o Tamanho do TextArea de acordo com o conteúdo inserido.
                    alteraAlturaTextArea();
                }
            });   
        } 
    }

	function alteraAlturaTextArea()
    {
        $("[name*='u_solcomitem_obs']").each(function() {
            let text = $(this).val();
            let lines = text.split(/\r|\r\n|\n/);
            let count = lines.length;
            let countPalavras = text.length / 60;
            if(count > countPalavras && text.length > 1) {
                $(this).css('height', count * 17);
            } else if(countPalavras > 2 && text.length > 1) {
                $(this).css('height', ((countPalavras + count).toFixed()) * 16);
            }         
        });
    }

	function excluirLink(idobjetolink, idsolcomitem)
    {
        if(confirm("Deseja realmente excluir o link?"))
        {
            CB.post({
                objetos: {
                    "_isi_d_objetolink_idobjetolink": idobjetolink
                },
                parcial: true,
                posPost: function(data, textStatus, jqXHR){
                    var localcompra = $(`#modallocalcompra${idsolcomitem}`)[0].innerHTML;
                    $("#cbModalCorpo").html(localcompra);
                }
            });
        }
    }

	function reprovarItemSolcom(idsolcomitem)
    {
        motivoobs = $(`#_99rep${idsolcomitem}_i_modulocom_descricao`).val();
        let desc = $(`#_rep${idsolcomitem}_u_solcomitem_descr`).val();
        let objeto = `&_99rep${idsolcomitem}_i_modulocom_descricao=${motivoobs}&_99rep${idsolcomitem}_i_modulocom_idmodulo=<?=$_1_u_solcom_idsolcom?>&_99rep${idsolcomitem}_i_modulocom_modulo=solcom&_rep${idsolcomitem}_u_solcomitem_idsolcomitem=${idsolcomitem}&_rep${idsolcomitem}_u_solcomitem_descr=${desc}`;
        if(motivoobs.length > 0)
        {
            CB.post({
                objetos: {
                    "_sia_u_solcomitem_idsolcomitem": idsolcomitem,
                    "_sia_u_solcomitem_status": 'REPROVADO',
                    "_1_u_solcom_idsolcom": '<?=$_1_u_solcom_idsolcom?>',
                },
                posPost: function(data, textStatus, jqXHR){
                    CB.post({
                        objetos: objeto,
                        parcial:true
                    });
                    $('#cbModal').modal('hide');
                    CB.setPrefUsuario('u',CB.modulo+'.botaoflutuante','Y'); 
                    alteraAltura('Y');
                },
                parcial: true,
            });
        } else {
            alerta('Insira o Motivo');
        }
    }

	function novoItem(tipo)
    {
        oTbItens = $(`#tbItens${tipo}`);
        iNovoItem = (oTbItens.find("input.idprodserv").length + 11);

        if(tipo == 'naoCadastrado')
        {
            htmlTrModelo = $(`#modeloNovoItem${tipo}`).html();    
            htmlTrModelo = htmlTrModelo.replace("#qtdItemSolcom", "_"+iNovoItem+"#quantidade");
            htmlTrModelo = htmlTrModelo.replace("#unItemSolcom", "_"+iNovoItem+"#un");
            htmlTrModelo = htmlTrModelo.replace("#idprodservItemSolcom", "_"+iNovoItem+"#prodservdescr");
            htmlTrModelo = htmlTrModelo.replace("#obs", "_"+iNovoItem+"#obs");

        } else {
            htmlTrModelo = $(`#modeloNovoItem${tipo}`).html();    
            htmlTrModelo = htmlTrModelo.replace("#idprodservItemSolcom", "_"+iNovoItem+"#idprodserv");
        }
        
        htmlTrModelo = htmlTrModelo.replace(/#irow/g, iNovoItem);

        oTbItens.append(htmlTrModelo);

        if(tipo == '')
        {
            criaAutocompleteProd(tipo);
        }
        if(tipo == 'fabricado')
        {
            criaAutocompleteProdF();
        }
    }

    function criaAutocompleteProdF()
    {
        //autocomplete de jTipoProdServ
        $(".produtofabricado").autocomplete({
            source: jprodservFabricado
            ,delay: 0
            ,create: function(){
                $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $('<li>').append("<a>"+item.descr+" - "+item.un+" - <span style='color:#b6b6b6;'>"+item.rotulo+"</span></a>").appendTo(ul);
                };
            }
            ,select: function(event, ui){
                             
                
                var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';

                CB.modal({
                    url: "?_modulo=solcomfabricado&_acao=u&idsolcom="+idsolcom+"&idprodservformula=" + ui.item.idprodservformula + "" + idempresa,
                    header: "Solicitação de Itens por Produto Fabricado",
                    aoFechar: function() {
                        location.reload();
                    }
                });
            }
        });
    }

	function criaAutocompleteProd(tipo)
    {
        //autocomplete de jTipoProdServ
        $(".idprodserv").autocomplete({
            source: jprodserv
            ,delay: 0
            ,create: function(){
                $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $('<li>').append("<a>"+item.descr+" - "+item.un+" - <span style='color:#b6b6b6;'>"+item.codprodserv+"</span></a>").appendTo(ul);
                };
            }
            ,select: function(event, ui){
                if(tipo == "")
                {
                    CB.post({
                        objetos:{
                            "_s_i_solcomitem_descr": ui.item.descr,
                            "_s_i_solcomitem_idsolcom": $("#idsolcom").val(),
                            "_s_i_solcomitem_idprodserv": ui.item.idprodserv,
                            "_s_i_solcomitem_status": 'PENDENTE',
                            "_s_i_solcomitem_un": ui.item.un
                        },
                        parcial: false            
                    });

                } else {
                    CB.post({
                        objetos:{
                            "_s_i_solcomitem_qtdc":$("#qtdcadastrado").val(),
                            "_s_i_solcomitem_descr": ui.item.descr,
                            "_s_i_solcomitem_idsolcom": $("#idsolcom").val(),
                            "_s_i_solcomitem_idprodserv": ui.item.idprodserv,
                            "_s_i_solcomitem_status": 'PENDENTE',
                            "_s_i_solcomitem_un": ui.item.un
                        },
                        parcial: false            
                    });
                }
            }
        });
    }

	function salvarComentario(idsolcomitem)
    {   
        if($(`#urgente`).is(':checked'))
        {
            urgente = 'Y';
        } else {
            urgente = 'N';
        }

        if($(`#justificativa`).val() == 'OUTROS'){
            motivoobs = $(`#motivo`).val();
        } else {
            motivoobs = $(`#justificativa`).val();
        }

        CB.post({
            objetos: {
                "_sia_u_solcomitem_idsolcomitem": idsolcomitem,
                "_sia_u_solcomitem_urgencia": urgente,
                "_sia_u_solcomitem_dataprevisao": $(`#dataprevisao`).val(),
            },
            parcial: true,
            posPost: function(data, textStatus, jqXHR){
                if(motivoobs && urgente == 'Y' && motivoobs != $(`#urgente2`).val())
                {
                    CB.post({
                        objetos: {
                            "_99_i_modulocom_descricao": '*<b>URGENTE:</b>* '+motivoobs,
                            "_99_i_modulocom_idmodulo": idsolcomitem,
                            "_99_i_modulocom_modulo": 'solcomitem',
                        },
                        parcial: true
                    });

                    $(`#urgente2`).val(motivoobs)
                } else if(urgente == 'Y'){
                    alerta('Insira o Motivo');
                }
            },
            refresh: 'refresh'
        });
    }

	function criarSolmat(idsolcom)
    {   
        itens = [];
        let objeto = "";
        i = 0;
        let checkitem = false;
        $("[name='solmatitem']:visible").each(function() {
            if($(this).is(':checked'))
            {
                objeto += `&_solmat${i}_u_solcomitem_idsolcomitem=${$(this).val()}&_solmat${i}_u_solcomitem_idsolcom=${idsolcom}`;
                i++;
                checkitem = true;
            } 
        });
        
        if(checkitem)
        {
            CB.post({
                objetos: objeto,
                parcial: true,
                refresh: 'refresh',
                posPost: function(){
                    $('#cbModal').modal('hide');
                }
            });
        } else {
            alerta('Nenhum Item selecionado.');
        }
    }

    function criarSoltag(idsolcom)
    {   
        itens = [];
        let objeto = "";
        i = 0;
        let checkitem = false;
        $("[name='solmatitem']:visible").each(function() {
            if($(this).is(':checked'))
            {
                qtd = $(this).attr('qtd');
                objeto += `&_soltag${i}_u_solcomitem_idsolcomitem=${$(this).val()}&_soltag${i}_u_solcomitem_idsolcom=${idsolcom}&qtdsoltag${i}=${qtd}`;
                i++;
                checkitem = true;
            } 
        });
        
        if(checkitem)
        {
            CB.post({
                objetos: objeto,
                parcial: true,
                refresh: 'refresh',
                posPost: function(){
                    $('#cbModal').modal('hide');
                }
            });
        } else {
            alerta('Nenhum Item selecionado.');
        }
    }


	function justificativa(motivo)
    {
        if(motivo.value == 'OUTROS')
        {
            $('#tdmotivodesc').show();
            $('#tdmotivoarea').show();
        }
    }

	function atualizaCampo(comentario, idsolcomitem = false)
    {
        if(idsolcomitem){
            $(`#_99rep${idsolcomitem}_i_modulocom_descricao`).val(comentario.value);
            $(`#_99rep${idsolcomitem}_i_modulocom_descricao`).attr('value', comentario.value);
        } else {
            $("#_99_i_modulocom_descricao").val(comentario.value);
        }
    }

	if(idsolcom)
	{
		var comentario = $("#comentarioopoup")[0].innerHTML;
        CB.montaBotaoFlutuante('fa fa-comment fa-3x azul pointer', 'icone', comentario);
        CB.oPanelLegenda.css( "zIndex", 901).addClass('screen');
        if($('#cbSalvarComentario').length == 0)
        {
            $('#descricaobotao').append(`<button id="cbSalvarComentario" type="button" class="btn btn-success btn-xs" onclick="addCometario()" title="Salvar" style="float: right;margin-top: 8px;margin-right: 8px;">
                        <i class="fa fa-circle"></i>Salvar
                    </button>`);
        }
	}

	function addCometario()
    {
        ST.desbloquearCBPost();
        CB.post({
            objetos:{
                "_99_i_modulocom_descricao": $("#_99_i_modulocom_descricao").val(),
                "_99_i_modulocom_idmodulo": '<?=$_1_u_solcom_idsolcom?>',
                "_99_i_modulocom_modulo": 'solcom',
            },
            parcial: true  
        });
    }

	function addRemovCheck(vthis)
    {
        if(vthis.checked)
            $(vthis).attr("checked", true);
        else
            $(vthis).removeAttr('checked');
    }

	function esconderMostrarTodos(tipo)
    {
        let state = $("#esconderMostrarTodos").attr('state');        
        let allCollapses = (state == 'Y') ? 'N' : 'Y';
        let delayAltura,
			delayCollapse;

        CB.setPrefUsuario('m',`{"${CB.modulo}":{"collapse":{"solcomitemtotal":"${allCollapses}"}}}`);
        clearInterval(delayCollapse);
		clearInterval(delayAltura);

        $("#esconderMostrarTodos").attr('state', allCollapses);

        $("."+tipo).each(function() {
            CB.setPrefUsuario('m',`{"${CB.modulo}":{"collapse":{"solcomitem${$(this).attr('idnfitem')}":"${allCollapses}"}}}`);
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

	function checkallSolcom(check)
    {
        if(check.checked)
            $('.idsolcomitem').prop('checked', 'checked').attr('checked', true);
        else
            $('.idsolcomitem').removeAttr('checked');
    }

    function alterarImportacao(checkbox, idsolcom) 
	{
		if (checkbox.checked) 
		{
			objetos = {
				"_x_u_solcom_idsolcom": idsolcom,
				"_x_u_solcom_importacao": 'S'
			};
		} else {
			objetos = {
				"_x_u_solcom_idsolcom": idsolcom,
				"_x_u_solcom_importacao": 'N'
			};
		}

		CB.post({
			objetos: objetos,
			parcial: true
		});
	}

    function alterarAutoSolmat(vthis, idsolcomitem) 
	{
		if ($(vthis).attr('valor') == 'N') 
		{
			objetos = {
				"_x_u_solcomitem_idsolcomitem": idsolcomitem,
				"_x_u_solcomitem_solmatautomatica": 'Y'
			};
		} else {
			objetos = {
				"_x_u_solcomitem_idsolcomitem": idsolcomitem,
				"_x_u_solcomitem_solmatautomatica": 'N'
			};
		}

		CB.post({
			objetos: objetos,
			parcial: true
		});
	}

    function calculaproduto(vthis){
        var valorn = $(vthis).val();
        var valorold=$(vthis).attr("valor");
        var valor = valorn / valorold;
       
        $('.valor').each(function(i, obj) {
            nvalor= valor * $(obj).attr('valor');
            $(obj).val(nvalor);
        });

    }

    function retirarinsumo(idprodserv){
        if(confirm("Deseja realmente remover este produto da lista?"))
        {
            $('#tr'+idprodserv).remove();
        }
    }
	//------- Funções Módulo -------

	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>