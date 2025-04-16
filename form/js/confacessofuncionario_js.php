<script type="text/Javascript">
    var assinaturaFuncionario = <?= JSON_ENCODE($assinaturaDoFuncionario) ?>;
    var outrasAssinaturasDeFuncionarios = <?= JSON_ENCODE($outrasAssinaturasDeFuncionarios) ?>;
    var emailVirtual      = <?= JSON_ENCODE($emailVirtual) ?>;
    var grupoDeAssinatura = <?= JSON_ENCODE($grupoDeAssinatura) ?>;
    var novoCertificadoDigital = <?= JSON_ENCODE($novoCertificadoDigital) ?>;
    var idPessoaLogada = '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>';
    var webMail = '<?= $webMailEmail ?? 'null' ?>';
    var imMsgConf = <?= JSON_ENCODE($imMsgConf) ?>;
    var assinaturaDeGruposDeEmail = <?= JSON_ENCODE($assinaturaDeGruposDeEmail) ?>;
    var assinaturaDeFuncionariosRelacionados = <?= JSON_ENCODE($assinaturaDeFuncionariosRelacionados) ?>;

    if( $("[name=_1_u_pessoa_idpessoa]").val() ){
        $(".cbupload").dropzone({
            idObjeto: $("[name=_1_u_pessoa_idpessoa]").val(),
            tipoObjeto: 'idpessoa',
            idPessoaLogada: idPessoaLogada,
            caminho: 'upload/'
        });
    }

    /**
	 * Salva o vinculo que sera removido
	 */
	function inativaobjeto(inid, inobj)
	{
		CB.post({
			objetos: `_x_d_${inobj}_id${inobj}=${inid}`,
			parcial: true
		});
	}

    //Autocomplete de Setores vinculados
    $("#immsgconf").autocomplete({
        source: imMsgConf
        ,delay: 0
        ,create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                lbItem = item.titulo;			
                return $('<li>')
                    .append('<a>' + lbItem + '</a>')
                    .appendTo(ul);
            };
        }
        ,select: function(event, ui){
            CB.post({
                objetos: {
                    "_x_i_immsgconfdest_idobjeto":$(":input[name=_1_"+CB.acao+"_pessoa_idpessoa]").val(),
                    "_x_i_immsgconfdest_idimmsgconf": ui.item.idimmsgconf,
                    "_x_i_immsgconfdest_objeto": 'pessoa'
                },
                parcial: true
            });
        }
    });

    function historico() {
		CB.modal({
			titulo: "</strong>Histórico</strong>",
			corpo: $("#historico").html(),
			classe: 'sessenta',
		});
	}

    $("#certanexo").dropzone({
        idObjeto: $("[name=_1_u_pessoa_idpessoa]").val()
        ,tipoObjeto: 'pessoa'
        ,tipoArquivo: 'CERTIFICADO'
        ,idPessoaLogada: idPessoaLogada
        ,maxFiles: 1
        ,init: function(){
            this.on("sending", function(file, xhr, formData){
                formData.append("idobjeto", this.options.idObjeto);
                formData.append("tipoobjeto", this.options.tipoObjeto);
                formData.append("tipoarquivo", this.options.tipoArquivo);
                formData.append("idPessoaLogada", this.options.idPessoaLogada);
            });

            this.on("error", function(file,response,xhr){
                if(xhr.getResponseHeader('x-cb-formato') == 'erro' && xhr.getResponseHeader('x-cb-resposta') == '0'){
                    alertAtencao("Formato do Arquivo de Certificado Inválido");
                }else{
                    alertErro("Ocorreu um erro inesperado");
                }
            });

            this.on("addedfile", function(file) {
                if((Object.keys(novoCertificadoDigital).length && file.id !== undefined) || (!Object.keys(novoCertificadoDigital).length && file.id === undefined)){
                    var removeButton = Dropzone.createElement("<i class='fa fa-trash hoververmelho' title='Apagar arquivo'></i>");

                    var _this = this;

                    removeButton.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        if(confirm("Deseja realmente excluir o arquivo?")){  

                            _this.removeFile(file);
                            CB.post({
                                objetos:"_9999_d_novocertificadodigital_idnovocertificadodigital="+file.id
                            })
                        }
                    });


                    file.previewElement.appendChild(removeButton);

                    file.previewElement.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        janelamodal("upload/cert/"+file.nome);
                    });
                }else{
                    this.removeFile(file);
                    alert("Só é possível ter um arquivo de Certificado por vez.\nÉ necessário excluir o arquivo antigo para poder adicionar um novo.");
                }

            });

            if(Object.keys(novoCertificadoDigital).length){
                var mockFile = { 
                    name: novoCertificadoDigital.nome,
                    nome: novoCertificadoDigital.nome,
                    caminho: novoCertificadoDigital.caminho,
                    id: novoCertificadoDigital.id
                };

                this.emit("addedfile", mockFile).emit("complete", mockFile);
            }
        }
    });

    $("#imagemassinatura").dropzone({
        url: "form/_arquivo.php"
        , idObjeto: $("[name=_1_u_pessoa_idpessoa]").val()
        , tipoObjeto: 'pessoa'
        , tipoArquivo: 'ASSINATURA'
        , idPessoaLogada: idPessoaLogada
        , maxFiles: 1
        , caminho: 'upload/cert/'
    });

    // Emails
    if(Object.keys(assinaturaDeFuncionariosRelacionados).length){
        function showWebmailAssinatura(vid){
            CB.modal({
                titulo: "Assinatura Funcionário",
                corpo: Object.values(assinaturaDeFuncionariosRelacionados[vid]),
                classe: "sessenta"
            });
        }
    }

    if(Object.keys(assinaturaDeGruposDeEmail).length){
        function showWebmailAssinatura1(vid)
        {
            CB.modal({
                titulo: "Assinatura Grupo de E-mail",
                corpo: Object.values(assinaturaDeGruposDeEmail[vid]),
                classe: "sessenta"
            });
        }
    }

    function delWebmailAssinatura1(vid)
    {
        CB.post({
            objetos: {
                "_x_d_webmailassinaturaobjeto_idwebmailassinaturaobjeto" : vid
            }
            ,parcial:true
        });
    }

    function delWebmailAssinatura(vid)
    {
        CB.post({
            objetos: {
                "_x_d_webmailassinaturaobjeto_idwebmailassinaturaobjeto" : vid
            }
            ,parcial:true
        });
    }

    function criarAssinaturaCampos()
    {
        CB.post({
            objetos: {
                "_Ncampos_i_assinaturaemailcampos_tipoobjeto" : 'COLABORADOR',
                "_Ncampos_i_assinaturaemailcampos_idobjeto" : $(":input[name=_1_"+CB.acao+"_pessoa_idpessoa]").val()
            }
            ,parcial:true
        });
    }
    if(webMail)
    {
        $("#assinaturasfunc").autocomplete({
            source: assinaturaFuncionario
            ,delay: 0
            ,create: function(){
                $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                    return $('<li>').append("<a>"+item.email+"</a>").appendTo(ul);
                };
            }
            ,select: function(event, ui){
                CB.post({
                    urlArquivo: 'ajax/montamultiassinatura.php?',
                    refresh: 'refresh',
                    parcial:true,
                    objetos: {
                        id: '<?= $_1_u_pessoa_idpessoa ?>' || 0,
                        tipoobjeto: 'pessoa',
                        idwebmailassinaturaobjetos: ui.item.idwebmailassinaturaobjetos,
                    },
                });
            }		
        });

        $("#assinaturasgrupo").autocomplete({
            source: grupoDeAssinatura
            ,delay: 0
            ,create: function(){
                $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                    return $('<li>').append("<a>"+item.email+"</a>").appendTo(ul);
                };
            }
            ,select: function(event, ui){
                CB.post({
                    urlArquivo: 'ajax/montamultiassinatura.php?',
                    refresh: 'refresh',
                    parcial:true,
                    objetos: {
                        id: '<?= $_1_u_pessoa_idpessoa ?>' || 0,
                        tipoobjeto: 'pessoa',
                        idwebmailassinaturaobjetos: ui.item.idwebmailassinaturaobjetos,
                    },
                });
            }	
        });

         $("#outrasassinaturas").autocomplete({
            source: outrasAssinaturasDeFuncionarios
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
                        id: '<?= $_1_u_pessoa_idpessoa ?>' || 0,
                        tipo: 'PESSOA',
                        idtemplate: ui.item.idwebmailassinaturatemplate,
                        template: ui.item.htmltemplate
                    },
                    success: function(data, textStatus, jqXHR){
                        if(data.error)
                        {
                            return alertAtencao(res.error);
                        }

                        if(jqXHR.getResponseHeader('X-CB-RESPOSTA') == 'id'){
                            var aux = $("<div>"+data+"</div>");
                            $("body").append($(`<div id='templateimg'>${aux.find("#_temp").html()}</div>`));
                            var idwebmailassinaturaobjeto = jqXHR.getResponseHeader('idwebmailassinaturaobjeto');
                            setTimeout(async function(){
                                try{
                                    let dataUrl = await domtoimage.toPng($("#templateimg #_template").get(0))
                                    let img = new Image();
                                    img.src = dataUrl;
                                    aux.find("#_temp").html(img);
                                    $("#templateimg").remove();
                                    CB.post({
                                        urlArquivo: 'ajax/replaceassinaturaemail.php?salvar=Y',
                                        refresh: 'refresh',
                                        objetos: {
                                            idwebmailassinaturaobjeto: idwebmailassinaturaobjeto,
                                            htmlassinatura: aux.html().replaceAll("'","&#39")
                                        },
                                        posPost: function(data,texto,jqXHR){
                                            let resp = JSON.parse(data);
                                            if(resp["erro"]){
                                                alert(resp["erro"])
                                            }
                                        }
                                    });
                                }catch(error){
                                    console.error('oops, something went wrong!', error);
                                    $("#_template").remove();//mudar selector
                                }
                                
                            }, 500);
                        }
                    },

                    error: function(objxmlreq){
                        alertErro('Erro:<br>'+objxmlreq.status);
                    }
                });

            }	
        });
    }
    
    //Autocomplete de email virtual
    $("#emailvirtual").autocomplete({
        source: emailVirtual
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
                    "_x_i_emailvirtualconf_idpessoaemail":$(":input[name=_1_"+CB.acao+"_pessoa_idpessoa]").val()
                    ,"_x_i_emailvirtualconf_tipoemailvirtual": 'PESSOA'
                    ,"_x_i_emailvirtualconf_email_original": ui.item.label
                    ,"_x_i_emailvirtualconf_emails_destino":$(":input[name=_1_"+CB.acao+"_pessoa_webmailemail]").val()
                }
                ,parcial: true
            });
        }
    });

    // CB.posPost = function() {debugger
	// 	let templateEmailArray = [];

	// 	$('.templates_email').each((key, element) => {
	// 		templateEmailArray.push({
	// 			idassinaturaemailcampos: $('input[name=_ass1_u_assinaturaemailcampos_idassinaturaemailcampos]').val(),
	// 			idtemplate: $(element).attr('idtemplate'),
	// 			idwebmailassinaturaobjeto: $(element).attr('idwebmailassinaturaobjeto')
	// 		});
	// 	});

	// 	// Atualiza html da assinatura
	// 	$.ajax({
	// 		url: 'ajax/replaceassinaturaemail.php?salvar=Y&gerar=Y',
	// 		method: 'POST',
	// 		data: {
	// 			templates: templateEmailArray
	// 		},
	// 		success: res => {
    //             if(res.error)
    //             {
    //                 return alertAtencao(res.error);
    //             }

	// 			console.log('Assinatura atualizada.');
	// 		},
	// 		error: err => {
	// 			console.log(err);
	// 		}
	// 	});
	// };

	$('.conteudo-assinatura-email input').on('blur', function() {
		CB.post({
			objetos: {
				'_ass1_u_assinaturaemailcampos_idassinaturaemailcampos': $('input[name=_ass1_u_assinaturaemailcampos_idassinaturaemailcampos]').val(),
				'_ass1_u_assinaturaemailcampos_nome': $('input[name=_ass1_u_assinaturaemailcampos_nome]').val().replaceAll("'","&#39"),
				'_ass1_u_assinaturaemailcampos_cargo': $('input[name=_ass1_u_assinaturaemailcampos_cargo]').val().replaceAll("'","&#39"),
				'_ass1_u_assinaturaemailcampos_telefone': $('input[name=_ass1_u_assinaturaemailcampos_telefone]').val().replaceAll("'","&#39"),
				'_ass1_u_assinaturaemailcampos_ramal': $('input[name=_ass1_u_assinaturaemailcampos_ramal]').val().replaceAll("'","&#39"),
				'_ass1_u_assinaturaemailcampos_celular': $('input[name=_ass1_u_assinaturaemailcampos_celular]').val().replaceAll("'","&#39")
			},
			refresh: false,
			parcial: true
		});
	});

    // Na teoria essa função nunca deve receber um array vazio
	// uma vez que sua chamada é feita p/ apagar o webmailassinaturaobjeto do funcionário
	// e de todos que estão vinculados a ele
	// portanto sempre terá pelo menos um registro no array para ser apagado
	function deletaidentidade(inpessoa = []) {
		if (confirm("Deseja realmente excluir essa identidade de e-mail?")) {
			var obj = {};

			inpessoa.forEach((o, i) => {
				obj[`_wp${i}_d_webmailassinaturaobjeto_idwebmailassinaturaobjeto`] = o;
			});

			CB.post({
				objetos: obj,
				parcial: true
			});
		}
	}

    if ($("[name=_1_u_pessoa_idpessoa]").val())
    {
		$("#avatarFoto").dropzone({
			url: "form/_arquivo.php",
			idObjeto: $("[name=_1_u_pessoa_idpessoa]").val(),
			tipoObjeto: 'pessoa',
			tipoArquivo: 'AVATAR',
			caminho: 'upload/avatar/',
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
					url: this.options.url + "?caminho=" + this.options.caminho + "&tipoobjeto=" + this.options.tipoObjeto + "&idobjeto=" + this.options.idObjeto + "&tipoarquivo=" + this.options.tipoArquivo
				}).done(function(data, textStatus, jqXHR) {
					thisDropzone.options.loopArquivos(data);
				})
			},
			loopArquivos: function(data) {
				jResp = jsonStr2Object(data);
				if (jResp.length > 0) {
					nomeArquivo = jResp[jResp.length - 1].nome;
					if (nomeArquivo) {
						$("#avatarFoto").attr("src", "upload/avatar/" + nomeArquivo);
					}
				}
			}
		});
	}
// adicionar rodapé em todos os JS de forms p/ ser possível debuggar em produção
//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||| 14/03/2023 PEDRO LIMA |||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
</script>
<script src="inc/js/dom-to-image/dom-to-image.min.js"></script>