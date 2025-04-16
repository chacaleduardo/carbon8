var _idpk = $('[name="*_lp_idlp"]').val() || getUrlParameter('idlp') || "";

/* deprecated */
function mostrarbtnsSalvar(idlp,fun){
	if($(`#cbModalCorpo`).find('[type="checkbox"][modulofilho]:checked').length > 0){
		$("#cbpost_mods_"+idlp).find('.btn-rel').hide()
	}else{
		$("#cbpost_mods_"+idlp).find('.btn-rel').show()
	}
	(fun == 'hide')?
	$("#cbpost_mods_"+idlp).hide():
	$("#cbpost_mods_"+idlp).show()
}

//chamada pelo dropzone
function relacionaLpModulo(inMod,inIdLpModulo,inPermissao,inidlp){

    if(inMod==""){
        alert("Erro: MODULO vazio!");
        return;
    }else{

        var sacao, spermissao;
        var ord = "";

        //se nao houver registro na tabela, inserir permissao de [L]eitura
        if(inIdLpModulo==""){
            sacao="i";
            spermissao="r";
            ord += "&_ajax_"+sacao+"__lpmodulo_ord=9999";

        //caso exista leitura, alterar para Escrita (w)
        }else if(inIdLpModulo!="" && inPermissao=="r"){
            sacao="u";
            spermissao="w";

        //caso exista escrita, alterar para Leitura (r)
        }else if(inIdLpModulo!="" && inPermissao=="w"){
            sacao="u";
            spermissao="r";
        }else{
            //caso exista na tabela algum registro fora do padrão
            sacao = "d";
        }

        var strPost = "_ajax_"+sacao+"__lpmodulo_idlp="+inidlp
                        +"&_ajax_"+sacao+"__lpmodulo_modulo="+inMod
                        +"&_ajax_"+sacao+"__lpmodulo_permissao="+spermissao
                        +"&_ajax_"+sacao+"__lpmodulo_idlpmodulo="+inIdLpModulo+ord;

        CB.post({
            objetos: strPost
        });
    }
}


//chamada ao clicar no botao edit
function relacionaLpModuloBT(inPermissao,inidlp){
	var strPost = "";
	$(`#cbModalCorpo`).find('[type="checkbox"]:checked').not('[modulorep]').not('[moduloreppai]').each((i,e)=>{
		inIdLpModulo=$(e).attr("idlpmodulo");
		inMod=$(e).attr("modulo");

		if(inMod==""){
			console.log("Erro: MODULO vazio!");

		}else{

			var sacao;
			var ord = "";

			//se nao houver registro na tabela, inserir permissao de [L]eitura
			if(inIdLpModulo=="" && inPermissao !='d'){
				sacao="i";
				ord += "&_ajax"+i+"_"+sacao+"__lpmodulo_ord=9999";
				strPost +=   "&_ajax"+i+"_"+sacao+"__lpmodulo_idlp="+inidlp
							+"&_ajax"+i+"_"+sacao+"__lpmodulo_modulo="+inMod
							+"&_ajax"+i+"_"+sacao+"__lpmodulo_permissao="+inPermissao+ord;

			//caso exista leitura, alterar para Escrita (w)
			}else if(inIdLpModulo!="" && inPermissao !='d'){
				sacao="u";
				strPost += "&_ajax"+i+"_"+sacao+"__lpmodulo_idlp="+inidlp
							+"&_ajax"+i+"_"+sacao+"__lpmodulo_modulo="+inMod
							+"&_ajax"+i+"_"+sacao+"__lpmodulo_permissao="+inPermissao
							+"&_ajax"+i+"_"+sacao+"__lpmodulo_idlpmodulo="+inIdLpModulo+ord;

			//caso exista escrita, alterar para Leitura (r)
			}/*else if(inIdLpModulo!="" && inPermissao=="w"){
				sacao="u";
				spermissao="r";
			}*/else{
				//caso exista na tabela algum registro fora do padrão
				sacao = "d";
				strPost += "&_ajax"+i+"_"+sacao+"__lpmodulo_idlpmodulo="+inIdLpModulo+ord;
			}

		}
	});

	$(`#cbModalCorpo`).find('[type="checkbox"][modulorep]:checked').each((i,e)=>{
		inIdLpRep=$(e).attr("idlprep");
		inRep=$(e).attr("idrep");

		if(inRep==""){
			console.log("Erro: idrep vazio!");
		}else{

			var sacao;

			//se nao houver registro na tabela, inserir permissao de [L]eitura
			if(inIdLpRep=="" && inPermissao != 'd'){
				sacao ="i";
				strPost += "&_ajaxrep"+i+"_"+sacao+"__lprep_idlp="+inidlp
							+"&_ajaxrep"+i+"_"+sacao+"__lprep_idrep="+inRep;
				

			//caso exista leitura, alterar para Escrita (w)
			}else if(inPermissao == 'd' && inIdLpRep != ''){
				//caso exista na tabela algum registro fora do padrão
				sacao = "d";
				strPost += "&_ajaxrep"+i+"_"+sacao+"__lprep_idlprep="+inIdLpRep;
			}
		}
	})

	CB.post({
		objetos: strPost,
		parcial:true,
		refresh:false
	});

}

function mostrabotaoCheck(tipoB,idlp){

}

//chamada ao clicar no botao edit
function relacionaLpRepBT(vthis,inRep,inidlp){
    inPermissao=$(vthis).attr("permissao");
    inIdLpRep=$(vthis).attr("idlprep");

    if(inRep==""){
        alert("Erro: MODULO vazio!");
        return;
    }else{

        var sacao, spermissao;
        var ord = "";

        //se nao houver registro na tabela, inserir permissao de [L]eitura
        if(inIdLpRep==""){
            sacao="i";
            spermissao="r";
            Nbtn="btn-primary";
            Rbtn="";
            Nicon="fa-lock";
            Ricon="fa-ban";
            var strPost = "_ajax_"+sacao+"__lprep_idlp="+inidlp
                        +"&_ajax_"+sacao+"__lprep_idrep="+inRep;
            

        //caso exista leitura, alterar para Escrita (w)
        }else{
            //caso exista na tabela algum registro fora do padrão
            sacao = "d";
            Rbtn="btn-danger";
            Nbtn="";
            Nicon="fa-ban";
            Ricon="fa-pencil";
            var strPost = "_ajax_"+sacao+"__lprep_idlprep="+inIdLpRep;
        }

        CB.post({
            objetos: strPost,
            parcial:true,
            refresh:false,
            posPost: function(data, textStatus, jqXHR){
                
                $(vthis).attr("permissao", spermissao || "");
                if(CB.lastInsertId>0){
                    $(vthis).attr("idlprep", CB.lastInsertId);
                }
                $(vthis).removeClass(Ricon).addClass(Nicon);
                $(vthis).parent().parent().parent().find('button').first().removeClass(Rbtn).addClass(Nbtn);
                
            }
        });
    }
}

//chamada ao clicar no botao edit
function relacionaLpRepUBT(vthis){

    inIdLpRep=$(vthis).attr("idlprep");
    inflgunidade=$(vthis).attr("flgunidade");


    if(inIdLpRep==""){
        alert("Falha: Relatório não associado!");
        return;
    }else{
        var sacao, spermissao;
        var ord = "";

        //se nao houver registro na tabela, inserir permissao de [L]eitura
        if(inflgunidade=="Y"){
            sacao="u";
            Nbtn="btn-primary";
			Rbtn = "";
            

            var strPost = "_ajax_"+sacao+"__lprep_idlprep="+inIdLpRep
                        +"&_ajax_"+sacao+"__lprep_flgunidade=N";

        //caso exista leitura, alterar para Escrita (w)
        }else{
            //caso exista na tabela algum registro fora do padrão
            sacao = "u";
            Rbtn="btn-danger";
			Nbtn = "";
            
            
            var strPost = "_ajax_"+sacao+"__lprep_idlprep="+inIdLpRep
                        +"&_ajax_"+sacao+"__lprep_flgunidade=Y";
        }

        CB.post({
            objetos: strPost,
            parcial:true,
            refresh:false,
            posPost: function(data, textStatus, jqXHR){
                
                $(vthis).attr("permissao", spermissao || "");
                if(CB.lastInsertId>0){
                    $(vthis).attr("idlprep", CB.lastInsertId);
                }
                
                $(vthis).parent().parent().parent().find('button').first().removeClass(Rbtn).addClass(Nbtn);
                
            }
        });
    }
}
function relacionaLpRepIdPessoa(vthis){

	inIdLpRep=$(vthis).attr("idlprep");
	inflgidpessoa=$(vthis).attr("flgidpessoa");


	if(inIdLpRep==""){
		alert("Falha: Relatório não associado!");
		return;
	}else{
		var sacao, spermissao;
		var ord = "";

		//se nao houver registro na tabela, inserir permissao de [L]eitura
		if(inflgidpessoa=="Y"){
		sacao="u";
		Nbtn="btn-primary";
		

		var strPost = "_ajax_"+sacao+"__lprep_idlprep="+inIdLpRep
					+"&_ajax_"+sacao+"__lprep_flgidpessoa=N";

		//caso exista leitura, alterar para Escrita (w)
		}else{
			//caso exista na tabela algum registro fora do padrão
			sacao = "u";
			Rbtn="btn-danger";
			
			
			var strPost = "_ajax_"+sacao+"__lprep_idlprep="+inIdLpRep
						+"&_ajax_"+sacao+"__lprep_flgidpessoa=Y";
		}

		CB.post({
			objetos: strPost,
			parcial:true,
			refresh:false,
			posPost: function(data, textStatus, jqXHR){
				
				$(vthis).attr("permissao", spermissao);
				if(CB.lastInsertId>0){
					$(vthis).attr("idlprep", CB.lastInsertId);
				}
				
				$(vthis).parent().parent().parent().find('button').first().removeClass(Rbtn).addClass(Nbtn);
				
			}
		});
	}
}

function relacionaLpRepIdContaItem(vthis){

	inIdLpRep=$(vthis).attr("idlprep");
	inflgcontaitem=$(vthis).attr("flgcontaitem");
	if(inIdLpRep==""){
		alert("Falha: Relatório não associado!");
		return;
	}else{
		var sacao, spermissao;
		var ord = "";

		//se nao houver registro na tabela, inserir permissao de [L]eitura
		if(inflgcontaitem=="Y"){
			sacao="u";
			Nbtn="btn-primary";
			

			var strPost = "_ajax_"+sacao+"__lprep_idlprep="+inIdLpRep
						+"&_ajax_"+sacao+"__lprep_flgcontaitem=N";

		//caso exista leitura, alterar para Escrita (w)
		}else{
			//caso exista na tabela algum registro fora do padrão
			sacao = "u";
			Rbtn="btn-danger";
			
			
			var strPost = "_ajax_"+sacao+"__lprep_idlprep="+inIdLpRep
						+"&_ajax_"+sacao+"__lprep_flgcontaitem=Y";
		}


		CB.post({
			objetos: strPost,
			parcial:true,
			refresh:false,
			posPost: function(data, textStatus, jqXHR){
				
				$(vthis).attr("permissao", spermissao);
				if(CB.lastInsertId>0){
					$(vthis).attr("idlprep", CB.lastInsertId);
				}
				
				$(vthis).parent().parent().parent().find('button').first().removeClass(Rbtn).addClass(Nbtn);
				
			}
		});
	}
}

function carregandoLPIndicador(display = 'show'){
	if(display == 'hide'){
		$("#circularProgressIndicator").hide();
		$("#mainPanel").removeClass("disabledbutton");
	}else if(display == 'show'){
		$("#circularProgressIndicator").show();
		$("#mainPanel").addClass("disabledbutton");
	}
}


function SincronizarLp(idlpdestino) {
    
    var b_sincronizalp = 'N';
    var v_sincronizalp = document.getElementById('sincronizalp');
    if (v_sincronizalp.checked){
        b_sincronizalp = 'Y';
    }
    
    var b_sincronizadash = 'N';
    var v_sincronizadash = document.getElementById('sincronizadash');
    if (v_sincronizadash.checked){
        b_sincronizadash = 'Y';
    }
    
    var b_sincronizaconfig = 'N';
    var v_sincronizaconfig = document.getElementById('sincronizaconfig');
    if (v_sincronizaconfig.checked){
        b_sincronizaconfig = 'Y';
    }
    
    var b_sincronizaparticipantes = 'N';
    var v_sincronizaparticipantes = document.getElementById('sincronizaparticipantes');
    if (v_sincronizaparticipantes.checked){
        b_sincronizaparticipantes = 'Y';
    }    
        
	$.ajax({
		type: "post",
		url : "cron/sincronizalp.php",
		data: { 
			idlp     		  : $('#idlporigem').val(),
			idlpdestino 	  : idlpdestino,
			sincronizalp	  : b_sincronizalp,
		    sincronizadash    : b_sincronizadash,
			sincronizaconfig  : b_sincronizaconfig,
			sincronizaparticipantes  : b_sincronizaparticipantes,
		},
		success: function(data){
		    alertSalvo('LP Importada com sucesso');
		},error: function(objxmlreq){
			alert('Erro:<br>'+objxmlreq.status); 
		}
	});	
}

function attJsonPref(idpk) {

	$.ajax({
		type: 'get',
		cache: false,
		url: "form/_moduloconf.php",
		data: "_modulo="+CB.modulo+CB.sToken,
		success: function(data){
			var jsonMod = jsonStr2Object(data); 
			if(!jsonMod){
				alertErro(data);
			}else{
				carregaPreferenciasJson(idpk, jsonMod.jsonpreferencias);
			}
		}
	});
	
}

function carregaPreferenciasJson(idpk, json){
	carregandoLPIndicador('show');

	controlaNavTabAndPill("tab", idpk, json);
	controlaNavTabAndPill("pill", idpk, json);

	if(CB.oModuloForm.find("li[role=presentation].active").length == 0) {
		$(CB.oModuloForm.find("[tab=participantes]")[0]).trigger('click')	
	}

	carregandoLPIndicador('hide');
	
	$(document).ready(function() {
		$('.modulo-colapse').on('click', (el) => {
			if (el.target.classList.contains('collapsed')) {
				el.target.classList.remove('glyphicon-chevron-down');
				el.target.classList.add('glyphicon-chevron-up')
			} else {
				el.target.classList.remove('glyphicon-chevron-up');
				el.target.classList.add('glyphicon-chevron-down');
			}
		});

		$(".modulos").each(function() {
			$(this).on('click', (el) => {
				//console.log(el)
				$(el.target.querySelector('.modulo-colapse.glyphicon')).trigger('click')
			});
		});
		

		$("#lptabs .modulo-colapse.glyphicon").each((el)=>{
			if($(el).attr('aria-expanded')==='true'){
				$(el).classList.toggle('glyphicon-chevron-down',"glyphicon-chevron-up");
			}
		})

		$("div.collapse.in").each(function(index, el){
			//debugger;
			let icon = $(el).parent().find('.glyphicon');
			if(icon.length > 0){
				icon.addClass('glyphicon-chevron-up').removeClass('glyphicon-chevron-down');
			}
		})
	});
}

function controlaNavTabAndPill(tipo, idpk, json) {
	
	$.each(CB.oModuloForm.find("[data-toggle="+tipo+"]").not(".define"),function(i,o) {
		$o = $(o);
		$o.addClass("define");
		var shref = $o.attr("href");
		var khref = shref.substr(1);//Remover a # do id para não gerar erro de path no mysql

		var objJson = json || CB.jsonModulo.jsonpreferencias;

		var navType = "nav"+tipo;

		//Verifica se o elemento com collapse possui alguma preferência de usuário salva
		if (objJson["lp_"+idpk] && objJson["lp_"+idpk][navType] && objJson["lp_"+idpk][navType][khref]){

			$oc = $(shref);
			$oc.removeClass("active");
			col = objJson["lp_"+idpk][navType][khref];

			if (col == "N") {
				$oc.removeClass("active");
				$o.parent().removeClass("active");
			} else {
				$oc.addClass("active").addClass('in');
				$o.parent().addClass("active");
			}
		}

		$o.on('click',function(e){

			$this=$(this);//Objeto atual. Geralmente um panel-heading
			var shref=$this.attr("href");
			var khref=shref.substr(1);//Remover a # do id para não gerar erro de path no mysql
			$body=$(shref);
			if($body.hasClass("active") && $body.hasClass("in")){//Está aberto
				//Fechar
				if ($body.is(":visible")) {
					CB.setPrefUsuario('d', CB.modulo+'.lp_'+idpk+'.'+navType, undefined, function(){
						CB.setPrefUsuario('m','{"'+CB.modulo+'":{"lp_'+idpk+'":{"'+navType+'":{"'+khref+'":"N"}}}}');
						$body.removeClass('active').removeClass('in').addClass('hidden');
						$this.parent().removeClass('active');
					});
				}else{
					CB.setPrefUsuario('d', CB.modulo+'.lp_'+idpk+'.'+navType, undefined, function(){
						CB.setPrefUsuario('m','{"'+CB.modulo+'":{"lp_'+idpk+'":{"'+navType+'":{"'+khref+'":"Y"}}}}');
						$body.addClass('active').addClass('in').removeClass('hidden');
						$body.siblings().removeClass('active').removeClass('in').addClass('hidden');
						$this.parent().siblings().removeClass('active')
						$this.parent().addClass('active');
					});
				}
			}else{
				//Abrir
				CB.setPrefUsuario('d', CB.modulo+'.lp_'+idpk+'.'+navType, undefined, function(){
					CB.setPrefUsuario('m','{"'+CB.modulo+'":{"lp_'+idpk+'":{"'+navType+'":{"'+khref+'":"Y"}}}}');
					$body.addClass('active').addClass('in').removeClass('hidden');
					$body.siblings().removeClass('active').removeClass('in').addClass('hidden');
					$this.parent().siblings().removeClass('active')
					$this.parent().addClass('active');
				});
			}
		});
	});
}

CB.on('posLoadUrl',function(){
	carregaPreferenciasJson(_idpk, CB.jsonModulo.jsonpreferencias);
});

CB.on('posPost',function(){
	if(_idpk){
		attJsonPref(_idpk);
	}
});

function desvincularpessoaSetDeptArea(inId){
	CB.post({
		objetos: {
			"_x_d_lpobjeto_idlpobjeto":inId
		}
		,parcial: true		
	});
}

function desvincularDashboard(inO){
	var id = $(inO).attr("idobjeto");
	CB.post({
		objetos: {
			"_x_d__lpobjeto_idlpobjeto":id
		}
		,parcial: true		
	});
}

function retiraundneg(inidunidadeobjeto){
	CB.post({
		objetos: "_x_d_plantelobjeto_idplantelobjeto="+inidunidadeobjeto
	});
}

function inseriundneg(idlp,inidund){
	CB.post({
		objetos: "_x_i_plantelobjeto_idobjeto="+idlp+"&_x_i_plantelobjeto_idplantel="+inidund+"&_x_i_plantelobjeto_tipoobjeto=lp"
	});
}

function alteraStatusLP(vthis,inidlp){
	let status = $(vthis).val()

	CB.post({
		objetos: "_x_u__lp_idlp="+inidlp+"&_x_u__lp_status="+status
	});
}

/* deprecated */
function alteraStatusGrupo(vthis,inidlp){
	let status = $(vthis).val()
	CB.post({
		objetos: "_x_u__lp_idlp="+inidlp+"&_x_u__lp_grupo="+status, 
		parcial: true
	});
}

function inserirAgencia(idlp,inidund){
	CB.post({
		objetos: "_x_i_objetovinculo_idobjeto="+idlp+"&_x_i_objetovinculo_tipoobjeto=_lp&_x_i_objetovinculo_idobjetovinc="+inidund+"&_x_i_objetovinculo_tipoobjetovinc=agencia"
	});
}

function inserirContaitem(idlp,inidund){
	CB.post({
		objetos: "_x_i_objetovinculo_idobjeto="+idlp+"&_x_i_objetovinculo_tipoobjeto=_lp&_x_i_objetovinculo_idobjetovinc="+inidund+"&_x_i_objetovinculo_tipoobjetovinc=contaitem"
	});
}
function inserirUnidade(idlp,inidund){
	CB.post({
		objetos: "_x_i_objetovinculo_idobjeto="+idlp+"&_x_i_objetovinculo_tipoobjeto=_lp&_x_i_objetovinculo_idobjetovinc="+inidund+"&_x_i_objetovinculo_tipoobjetovinc=unidade"
	});
}
function inserirTP(idlp,inidund){
	CB.post({
		objetos: "_x_i_objetovinculo_idobjeto="+idlp+"&_x_i_objetovinculo_tipoobjeto=_lp&_x_i_objetovinculo_idobjetovinc="+inidund+"&_x_i_objetovinculo_tipoobjetovinc=tipopessoa"
	});
}
function excluir(tab, inid){
    if(confirm("Deseja retirar este?")){		
        CB.post({
			objetos: "_x_d_"+tab+"_id"+tab+"="+inid
			,parcial: true
        });
    }    
}

function alttipocontato(incampo,inval,idlp){
     CB.post({
	objetos: "_x_u__lp_idlp="+idlp+"&_x_u__lp_"+incampo+"="+inval
	, parcial:true
    });
}

function solicitarAssinatura(inIdlpModulo, flag){
	CB.post({
		objetos:"_solassiantura_u__lpmodulo_idlpmodulo="+inIdlpModulo+"&_solassiantura_u__lpmodulo_solassinatura="+flag,
		parcial:true
	});
}

function alteraobjetovinculo(idlp,tipoobjeto,idobjeto,tipoobjetovinc,vthis){
	$.ajax({
			type: "post",
			url : "ajax/alteraobjetovinculoLP.php",
			data:{
				idlp,
				tipoobjeto,
				idobjeto,
				tipoobjetovinc,
				tabela:tipoobjetovinc
			},
			success: function(data){
				$(vthis).remove();
				alertSalvo("Salvo");
			},
			error: function(objxmlreq){
				alert('Erro:<br>'+objxmlreq.status); 
			}
	});
}

function abreModalDash(idlp,idlpgrupo,idempresa){

		CB.modal({
			titulo: "</strong>Dashboard</strong>",
			url:'?_modulo=confdashslp&_acao=u&idlp='+idlp,
			classe: 'noventa',
		});
}