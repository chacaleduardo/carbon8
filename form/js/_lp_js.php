<script>

var _idpk = $('[name="_1_u__lpgrupo_idlpgrupo"]').val() || getUrlParameter('idlpgrupo') || "";

(function(){
	if(getUrlParameter("_idempresa") != ""){
		CB.setWindowHistory(removerParametroGet('_idempresa').split('?')[1]);
	}
})();

$(".nav-tabs li").on("click",(event) =>{
	$(event.target).hover(function(){
		$(this).css("background-color","#c5c5c5");
	},function(){
		$(this).css("background-color","#9a9da0");
	}).css("background-color","#9a9da0");
	$(event.target).parent().siblings().find("a").hover(function(){
		$(this).css("background-color","");
	}).css("background-color","");
	
});
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
function checkfilhos(vthis,modulotipo){
	$(vthis).parent().parent().siblings().find('[type="checkbox"]['+modulotipo+']').each((i,e)=>{
			$(e).prop('checked',$(vthis).prop('checked'));
	});
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
            // posPost: function(data, textStatus, jqXHR){
                
            //     $(vthis).attr("permissao", spermissao || "");
            //     if(CB.lastInsertId>0){
            //         $(vthis).attr("idlpmodulo", CB.lastInsertId);
            //     }
            //     $(vthis).removeClass(Ricon).addClass(Nicon);
            //     $(vthis).parent().parent().parent().find('button').first().removeClass(Rbtn).addClass(Nbtn);
                
            // }
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
}}

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
function relacionaLpRepIdPessoa(vthis){

}

function relacionaLpRepIdContaItem(vthis){

	
}




//Aplicar a ordenação vista na tela aos Mà³dulos do Database
// function aplicarOrdenacaoModulos(){

// strPost = "";
// i=2;
// eCom="";
// $.each($(".targetSelecionados table"), function(){
//     var sIdLpMod=$(this).attr("cbidlpmodulo");
//     if(sIdLpMod){
//         strPost += eCom+"_"+i+"_u__lpmodulo_idlpmodulo="+sIdLpMod
//             +"&_"+i+"_u__lpmodulo_ord="+i;
//         eCom="&";
//         i++;
//     }
// })

// //Salva as alteraçàµes
// CB.post({
//     objetos: strPost
// });
// }

function desvincularempresa(inId){
CB.post({
    objetos: {
        "_x_d__lpobjeto_idlpobjeto" : inId
    }
    ,parcial: true		
});
}

function desvincularSnippet(inO){
var id = $(inO).attr("idlpobjeto");
CB.post({
    objetos: {
        "_x_d__lpobjeto_idlpobjeto":id
    }
    ,parcial: true		
});
}

function relacionaSnippet(inO, idlp){
var idsnippet = $(inO).attr("idsnippet");
CB.post({
    objetos: {
        "_x_i__lpobjeto_idlp": idlp
       ,"_x_i__lpobjeto_idobjeto": idsnippet
       ,"_x_i__lpobjeto_tipoobjeto": "_snippet"
    }
    ,parcial: true
});
}
//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
//@ sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>1


function carregandoLPIndicador(display = 'show'){
	if(display == 'hide'){
		$("#circularProgressIndicator").hide();
		$("#mainPanel").removeClass("disabledbutton");
	}else if(display == 'show'){
		$("#circularProgressIndicator").show();
		$("#mainPanel").addClass("disabledbutton");
	}
}

function editarNomeLp(vthis, idLpGrupo, descr,status){
	let beforeChange = $(vthis).html();

	let $oInput = $(`
		<div style="display: inline-flex;">
			<input type="text" id="edit_lpgrupo_${idLpGrupo}" style="width:70%;" value="${descr}">
			<div class="editButton">
				<i class="fa fa-times vermelho" id="edit_lpgrupo_cancel_${idLpGrupo}"></i>
			</div>
			<div class="editButton">
				<i class="fa fa-check verde" id="edit_lpgrupo_confirm_${idLpGrupo}"></i>
			</div>
			<div class="alterastatus">
				<select idgrupo="${idLpGrupo}" id="status_painel_${idLpGrupo}">
				<option value="ATIVO" ${status == 'ATIVO'?'selected':''}>ATIVO</option>
				<option value="INATIVO" ${status == 'INATIVO'?'selected':''}>INATIVO</option>
				</select>
			</div>
		</div>
	`);

	$oInput.find(`#edit_lpgrupo_cancel_${idLpGrupo}`).on('click', function(){
		$(vthis).html(beforeChange);
	});
	$oInput.find(`#status_painel_${idLpGrupo}`).on('change', function(){
		inativagrupo('painel',this)
	});

	$oInput.find(`#edit_lpgrupo_confirm_${idLpGrupo}`).on('click', function(){
		let str = $(`#edit_lpgrupo_${idLpGrupo}`).val();

		if(str.trim() == descr.trim()){
			$(vthis).html(beforeChange);
			return;
		} 

		if(str.trim() == ""){
			$(`#edit_lpgrupo_${idLpGrupo}`).addClass("alertaCbvalidacao");
			alertAtencao("Campo não pode ser vazio!");
			return;
		}

		let obj = {};
		$("li[id^='lpgrupo_"+idLpGrupo+"_']").each((i, o) => {
			obj[`_x${i}_u__lp_idlp`] = $(o).attr('idlp');
			obj[`_x${i}_u__lp_descricao`] = str.trim();
		});

		if(Object.keys(obj).length == 0){
			$(vthis).html(beforeChange);
			return;
		}

		obj[`_w_u__lpgrupo_idlpgrupo`] = idLpGrupo;
		obj[`_w_u__lpgrupo_descricao`] = str.trim();

		CB.post({
			objetos: obj,
			parcial: true,
		});
	});

	$(vthis).html($oInput);
	$(`#edit_lpgrupo_${idLpGrupo}`).select();
}

function carregaLP(vthis, idlp, idlpgrupo) {
	var $this = $($(vthis).attr('href'));

	if($this.hasClass("init")){
		$this.addClass('loading').removeClass('init');
		carregandoLPIndicador('show');
		let idempresa = $this.attr('idempresa');
		let _showerrors = getUrlParameter('_showerrors') != ""?"&_showerrors=Y":"";
		$.ajax({
			type: "post",
			url : "ajax/getLP.php?_idempresa="+idempresa+_showerrors,
			data: { 
				idlp 		: idlp,
				idlpgrupo 	: idlpgrupo,
			},
			success: function(data){
				$this.html(data);
				carregandoLPIndicador('hide');
				$this.addClass('load').removeClass('loading');
				filterByExhibition(undefined, false, "on");
				CB.controlaCollapse();
			},error: function(objxmlreq){
				alert('Erro:<br>'+objxmlreq.status); 
			}
		});	
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

	if(json && json["lpgrupo_"+idpk] && json["lpgrupo_"+idpk]["navtab"] && json["lpgrupo_"+idpk]["navpill"]){

		let navTabKey 	= Object.keys(json["lpgrupo_"+idpk]["navtab"])[0] || "";
		let navPillKey 	= Object.keys(json["lpgrupo_"+idpk]["navpill"])[0] || "";

		if(navTabKey != "" && navPillKey != "" && document.getElementById(navPillKey)){

			let idempresa 	= $("#"+navPillKey).attr('idempresa');
			let idlpgrupo 	= navTabKey.split("_")[1] || "";
			let idlp 		= navPillKey.split("_")[1] || "";
			let _showerrors = getUrlParameter('_showerrors') != ""?"&_showerrors=Y":"";
			$.ajax({
				type: "post",
				url : "ajax/getLP.php?_idempresa="+idempresa+_showerrors,
				data: { 
					idlp 		: idlp,
					idlpgrupo 	: idlpgrupo,
				},
				success: function(data){
					$("#"+navPillKey).html(data);
					$("#"+navPillKey).addClass("load").removeClass("init");
					//ajustaPreferencias();
					//ajustaPreferenciasDisponiveis();
					filterByExhibition(undefined, false, "on");
					carregandoLPIndicador('hide');
					CB.controlaCollapse();
				},error: function(objxmlreq){
					alert('Erro:<br>'+objxmlreq.status); 
				}
			});
		}else{
			carregandoLPIndicador('hide');
		}
	}else{
		carregandoLPIndicador('hide');
	}
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
		if (objJson["lpgrupo_"+idpk] && objJson["lpgrupo_"+idpk][navType] && objJson["lpgrupo_"+idpk][navType][khref]){

			$oc = $(shref);
			$oc.removeClass("active");
			col = objJson["lpgrupo_"+idpk][navType][khref];

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
			if(CB.modulo!='despesasitem'){// neste modulo não queremos que traga as informacoes conforme escolha do usuario na tela na hora
				if($body.hasClass("active") && $body.hasClass("in")){//Está aberto
					//Fechar
					if ($body.is(":visible")) {
						CB.setPrefUsuario('d', CB.modulo+'.lpgrupo_'+idpk+'.'+navType, undefined, function(){
							CB.setPrefUsuario('m','{"'+CB.modulo+'":{"lpgrupo_'+idpk+'":{"'+navType+'":{"'+khref+'":"N"}}}}');
							$body.removeClass('active').removeClass('in').addClass('hidden');
							$this.parent().removeClass('active');
						});
					}else{
						CB.setPrefUsuario('d', CB.modulo+'.lpgrupo_'+idpk+'.'+navType, undefined, function(){
							CB.setPrefUsuario('m','{"'+CB.modulo+'":{"lpgrupo_'+idpk+'":{"'+navType+'":{"'+khref+'":"Y"}}}}');
							$body.addClass('active').addClass('in').removeClass('hidden');
							$body.siblings().removeClass('active').removeClass('in').addClass('hidden');
							$this.parent().siblings().removeClass('active')
							$this.parent().addClass('active');
						});
					}
				}else{
					//Abrir
					CB.setPrefUsuario('d', CB.modulo+'.lpgrupo_'+idpk+'.'+navType, undefined, function(){
						CB.setPrefUsuario('m','{"'+CB.modulo+'":{"lpgrupo_'+idpk+'":{"'+navType+'":{"'+khref+'":"Y"}}}}');
						$body.addClass('active').addClass('in').removeClass('hidden');
						$body.siblings().removeClass('active').removeClass('in').addClass('hidden');
						$this.parent().siblings().removeClass('active')
						$this.parent().addClass('active');
					});
				}
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
status = $(vthis).val()

	CB.post({
		objetos: "_x_u__lp_idlp="+inidlp+"&_x_u__lp_status="+status
	});
}

function alteraStatusGrupo(vthis,inidlp)
{
	status = $(vthis).val()
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

function AtualizaBim(){
	 $.ajax({
			type: "get",
			url : "cron/bim.php",

			success: function(data){
					//alert('OK');
						alertSalvo('Bim Atualizado');
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

function showModalLp(){
	$oModal = $(`
		<div id="nova_lp">
			<div class="col-md-12">
				<label for="nova_lpgrupo_nome">LP:</label>
				<input type="text" id="nova_lpgrupo_nome">
			</div>
			<div class="col-md-12">
				<label for="nova_lpgrupo_empresa">Empresa:</label>
				<select id="nova_lpgrupo_empresa">
					<?fillselect("SELECT idempresa,sigla from empresa e where status='ATIVO' AND EXISTS (select 1 from objempresa oe where oe.idobjeto = ".$_SESSION['SESSAO']['IDPESSOA']." and oe.objeto = 'pessoa' and oe.empresa = e.idempresa)")?>
				</select>
			</div>
			<div class="col-md-12" style="text-align: right;margin: 10px 0px 10px 0px;">
				<button onclick="criaLpGrupo()" class="btn btn-success btn-sm">
					<i class="fa fa-plus"></i>Adicionar LP
				</button>
			</div>
		</div>
	`);

	CB.modal({
		titulo: "</strong>Adicionar LP</strong>",
		corpo: [$oModal],
		classe: 'vinte',
	});	
}

function showModalEmpresa(idlpGrupo){

	$oModal = $(`
		<div id="nova_lp">
			<div class="col-md-12">
				<label for="nova_lp_empresa">Empresa:</label>
				<select id="nova_lp_empresa">
					<?fillselect("SELECT idempresa,sigla from empresa e where status='ATIVO' AND EXISTS (select 1 from objempresa oe where oe.idobjeto = ".$_SESSION['SESSAO']['IDPESSOA']." and oe.objeto = 'pessoa' and oe.empresa = e.idempresa)")?>
				</select>
			</div>
			<div class="col-md-12" style="text-align: right;margin: 10px 0px 10px 0px;">
				<button onclick="criaLpEmpresa(${idlpGrupo})" class="btn btn-success btn-sm">
					<i class="fa fa-plus"></i>Adicionar Empresa
				</button>
			</div>
		</div>
	`);

	$("[id^='lpgrupo_"+idlpGrupo+"_']").each((i, o) => {
		// Remove as opções das empresas que já existem para aquele lpgrupo
		let idempresa = o.id.split("_")[2];
		$oModal.find("#nova_lp_empresa option").remove("[value='"+idempresa+"']");
	});

	CB.modal({
		titulo: "</strong>Adicionar Empresa</strong>",
		corpo: [$oModal],
		classe: 'vinte',
	});	
}


function showModalSincronizarLp(idLp){

	$oModal = $(`
		<div id="nova_lp">
			<div class="col-md-12">
				<label for="idlporigem">LP de Origem:</label>
				<select id="idlporigem">
					<?fillselect(" SELECT 
                                        l.idlp, concat(lgp.descricao, ' > ', e.sigla, ' - ',lg.descricao) as asas
                                   FROM 
                                        "._DBCARBON."._lp l JOIN "._DBCARBON."._lpobjeto lo ON lo.idlp = l.idlp
                                   JOIN 
                                        "._DBCARBON."._lpgrupo lg ON lg.idlpgrupo = lo.idobjeto AND lo.tipoobjeto = 'lpgrupo'
                                   JOIN 
                                        "._DBCARBON."._lpgrupo lgp ON lgp.idlpgrupo = lg.lpgrupopar and lgp.status = 'ATIVO'
                                   JOIN 
                                        empresa e on e.idempresa = l.idempresa and e.status = 'ATIVO' 
                                   where
                                        l.status = 'ATIVO'
                                   order by
                                        lgp.descricao,  e.sigla, lg.descricao;
                                   
                                   ")?>
				</select>
			</div>
			<div class="col-md-12">
                <input type="checkbox" id="sincronizaconfig" name="sincronizaconfig" checked>
                <label for = "subscribeNews">Sincroniza Configurações</ label>
			</div>
			<div class="col-md-12">
                <input type="checkbox" id="sincronizaparticipantes" name="sincronizaparticipantes">
                <label for = "subscribeNews">Sincroniza Participantes</ label>
			</div>
			<div class="col-md-12">
                <input type="checkbox" id="sincronizalp" name="sincronizalp" checked>
                <label for = "subscribeNews">Sincroniza Módulos</ label>
			</div>
			<div class="col-md-12">
                <input type="checkbox" id="sincronizadash" name="sincronizadash" checked>
                <label for = "subscribeNews">Sincroniza Dashboard</ label>
			</div>
			
			<div class="col-md-12">
			    <div class="alert alert-warning" role="alert">
			        <div class="row">
			            <div class="col-md-12">
			                 <p>Ao importar, as configurações de acesso aos módulos poderão ser modificadas e o Dashboard também será substituído.</p>
			                 <p>Em outras palavras, as permissões e a aparência do sistema serão atualizadas de acordo com as configurações da importação.</p>
			                 <p>Essa alteração não poderá ser restaurada.</p>
			            </div>	
			          
			         </div>
			    </div>
   
			</div>
			<div class="col-md-12" style="text-align: right;margin: 10px 0px 10px 0px;">
				<button onclick="SincronizarLp(${idLp})" class="btn btn-success btn-sm">
					<i class="fa fa-upload"></i>Importar
				</button>
			</div>
		</div>
	`);


	CB.modal({
		titulo: "</strong>Importar Configurações</strong>",
		corpo: [$oModal],
		classe: 'trinta',
	});	
}

function criaLpEmpresa(idlpGrupo){
	if (!$("#nova_lp_empresa").val()) {
		alertAtencao("Campo não pode ser vazio!");
		$("#nova_lp_empresa").focus();
	}else{

		let lpEmpresa = $("#nova_lp_empresa").val();
		let lpDescricao = $("#lpgrupo_"+idlpGrupo+"_descricao").val();

		CB.post({
			objetos : {
				"_new_i__lp_idempresa" 	: lpEmpresa,
				"_new_i__lp_grupo" 		: 'N',
				"_new_i__lp_fullaccess" : 'N',
				"_new_i__lp_descricao" 	: lpDescricao,
				"_new_i__lp_status" 	: 'ATIVO',
				"_lpgrupo_idlpgrupo_"	: idlpGrupo,
			},
			parcial : true,
			posPost : function(){
				$("#cbModal").modal('hide');
			}
		})
	}
}

function criaLpGrupo(){
	if (!$("#nova_lpgrupo_nome").val()) {
		alertAtencao("Campo não pode ser vazio!");
		$("#nova_lpgrupo_nome").focus();
	}else{

		let lpNome 			= $("#nova_lpgrupo_nome").val();
		let lpEmpresa 		= $("#nova_lpgrupo_empresa").val();
		let LpGrupoId		= $("[name='_1_u__lpgrupo_idlpgrupo']").val();

		CB.post({
			objetos : {
				"_new_i__lpgrupo_lpgrupopar" 	: LpGrupoId,
				"_new_i__lpgrupo_descricao" 	: lpNome,
				"_new_i__lpgrupo_status" 		: "ATIVO",
				"_lp_idempresa_"				: lpEmpresa,
			},
			parcial : true,
			posPost : function(){
				$("#cbModal").modal('hide');
			}
		})
	}
}

function CopiaLP(vthis,idlp,rot,idobj,tobj) {
	idempresa = $(vthis).val();
	if (idempresa) {
		CB.post({
			objetos:{
				"copia_lp_empresa":idempresa
					,"acao":"copiaLP"
					,"idlpgrupo":$("[name='_1_u__lpgrupo_idlpgrupo']").val()
					,"copia_lp_descr":$("[name='_1_u__lpgrupo_descricao']").val()
					,"copia_lp_rot":rot
					,"lp_copiar_id":idlp
			}
		});	
	}
	
}

function abreModalMod(idlp,idlpgrupo,idempresa){
	CB.modal({
			titulo: "</strong>Módulos Disponíveis</strong>",
			url:'?_modulo=confmoduloslp&_acao=u&idlp='+idlp,
			classe: 'noventa',
		});
}

function abreModalDash(idlp,idlpgrupo,idempresa){

		CB.modal({
			titulo: "</strong>Dashboard</strong>",
			url:'?_modulo=confdashslp&_acao=u&idlp='+idlp,
			classe: 'noventa',
		});
}

function ajustaPreferencias(){

	$(".col-md-12 .targetSelecionados").append(`
		<div id="inicial" class="row"><div class="col-md-12"><p style="font-weight: bold;">Módulo Inicial:</p></div></div><br>
		<div id="ocultos" class="row"><div class="col-md-12"<p style="font-weight: bold;">Funcionalidade:</p></div></div><br>
		<div id="snippets" class="row"><div class="col-md-12"<p style="font-weight: bold;">Snippet:</p></div></div><br>
		<div id="menu" class="row"><div class="col-md-12"<p style="font-weight: bold;">Módulos Menu:</p></div></div><br>
		<div id="inativos" class="row"><div class="col-md-12"<p style="font-weight: bold;">Módulos Inativos:</p></div></div>
	`);

	$(".col-md-12 .targetSelecionados").each((i,o)=>{
		targetPai = $(o);
		$(o).find("table[modulopai]").each((e1,o1)=>{
			let modulotipo = $(o1).attr('modulotipo');

			switch(modulotipo){
				case 'moduloinicial':
					targetPai.find("#inicial").append($(o1));
					break;
				case 'modulooculto':
					targetPai.find("#ocultos").append($(o1));
					break;
				case 'modulomenu':
					targetPai.find("#menu").append($(o1));
					break;
				case 'moduloinativo':
					targetPai.find("#inativos").append($(o1));
					break;
				case 'modulosnippet':
					targetPai.find("#snippets").append($(o1));
					break;
				default:
					break;
			}

			if($(o1).find("td.targetFilhos table").length == 0){
				let modulo = $(o1).attr('cbmodulo');
				$(o1).find(`#${modulo}_collapse`).removeClass('collapse');
				$(o1).find(`#${modulo}_arrow i`).remove();
			}
		});
	});


	$(".filhocollapse.collapse.in").each((i,o)=>{
		let modulo = $(o).attr('mod');
		$(o).siblings(`#${modulo}_arrow`).find('i').addClass('fa-angle-up').removeClass('fa-angle-down');
	});

	let arr = ['ocultos','snippets','menu','inativos'];
	for (let i of arr){
		if($("#"+i).find('table').length == 0){
			$("#"+i).remove()
		}
	}
}

function ajustaPreferenciasDisponiveis(){

	$(".divmodulos .targetDisponiveis").append(`
		<div id="ocultos"><p style="font-weight: bold;">Funcionalidade:</p></div><br>
		<div id="snippets"><p style="font-weight: bold;">Snippet:</p></div><br>
		<div id="menu"><p style="font-weight: bold;">Módulos Menu:</p></div><br>
		<div id="inativos"><p style="font-weight: bold;">Módulos Inativos:</p></div>
	`);

	$(".divmodulos .targetDisponiveis").each((i,o)=>{
		targetPai = $(o);
		$(o).find("table[modulodisponiveis]").each((e1,o1)=>{
			let modulotipodisponveis = $(o1).attr('modulotipodisponveis');

			switch(modulotipodisponveis){
				case 'modulooculto':
					targetPai.find("#ocultos").append($(o1));
					break;
				case 'modulomenu':
					targetPai.find("#menu").append($(o1));
					break;
				case 'moduloinativo':
					targetPai.find("#inativos").append($(o1));
					break;
				case 'modulosnippet':
					targetPai.find("#snippets").append($(o1));
					break;
				default:
					break;
			}

			if($(o1).find("td.targetFilhos table").length == 0){
				let modulo = $(o1).attr('cbmodulo');
				$(o1).find(`#${modulo}_collapse`).removeClass('collapse');
				$(o1).find(`#${modulo}_arrow i`).remove();
			}
		})

	});
	$(".filhocollapse.collapse.in").each((i,o)=>{
		let modulo = $(o).attr('mod');
		$(o).siblings(`#${modulo}_arrow`).find('i').addClass('fa-angle-up').removeClass('fa-angle-down');
	});

	let arr = ['ocultos','snippets','menu','inativos'];
	for (let i of arr){
		if($("#"+i).find('table').length == 0){
			$("#"+i).remove()
		}
	}
}
function filterByText(vthis){
	let filterContent = vthis.value.toUpperCase();

	(filterContent.length == 0) ? CB.controlaCollapse() : $("tr[mod].collapse").collapse('show');

	$('.targetSelecionados button').each((i,o)=>{
		let title = $(o).attr('title');
		if($(o).find("span").text().toUpperCase().indexOf(filterContent) > -1){
			$(`table[cbmodulo="${title}"]`).addClass('visivel').removeClass('invisivel');
		}else{
			$(`table[cbmodulo="${title}"]`).addClass('invisivel').removeClass('visivel');
		}
	});

	$('table.visivel[modulofilho]').each((i,o)=>{
		$(o).parents("table[modulopai].invisivel").addClass('visivel').removeClass('invisivel');
	});
}

function filterByExhibition(vthis, alteraPref = true, defaultValue = ""){
	let filterContent;
	if(vthis){
		filterContent = vthis.value;
	}else if(defaultValue){
		filterContent = defaultValue;
	}else{
		return false;
	}
	
	let path = getUrlParameter("_modulo")+".filterByExhibition";

	switch ( filterContent ) {
		case 'pref':
			CB.controlaCollapse();
			break;
		case 'on':
			$("tr[mod].collapse").collapse('show');
			break;
		case 'off':
			$("tr[mod].collapse").collapse('hide');
			break;
		default:
			console.warn("[filterByExhibition] Nenhuma opção válida para filtro por exibição");
			return;
	}

	if(alteraPref){
		CB.setPrefUsuario('u',path, filterContent);
	}
}

function filterByPermission(vthis){
	let filterContent = vthis.value;
	
	let filter = function (classe){
		if(classe == 'noclass'){
			CB.controlaCollapse();
			$('.targetSelecionados button').each((i,o)=>{
				let title = $(o).attr('title');
				$(`table[cbmodulo="${title}"]`).addClass('visivel').removeClass('invisivel');
			});
		}else{
			$("tr[mod].collapse").collapse('show');
			$('.targetSelecionados button').each((i,o)=>{
				let title = $(o).attr('title');

				if($(o).hasClass(classe)){
					$(`table[cbmodulo="${title}"]`).addClass('visivel').removeClass('invisivel');
				}else{
					$(`table[cbmodulo="${title}"]`).addClass('invisivel').removeClass('visivel')
				}
			});
		}
		
		$('table[modulofilho]').each((i,o)=>{
			$(o).parents("table[modulopai].invisivel").addClass('visivel').removeClass('invisivel');
		});
	}

	switch ( filterContent ) {
		case 'a':
			filter('noclass');
			break;
		case 'n':
			filter('nopermission');
			break;
		case 'r':
			filter('btn-primary');
			break;
		case 'w':
			filter('btn-danger');
			break;
		default:
			console.warn("[filterByPermission] Nenhuma opção válida para filtro por permissão")
			break;
	}
}

CB.on('prePost', function(inParam){
	if($("input.json-changed").length > 0){
		if(confirm("Existem configurações da Lista de Permissão não salvos. Deseja salvá-las?")){
			$("input.json-changed").each(function(i, o){
				let idlp = $(o).attr('idlp');
				salvarDashConf(idlp);
			});
			if(inParam && inParam.parcial && inParam.parcial == true){
				inParam.parcial = undefined;
			}
		}
	}
	return true;
})

function inativagrupo(tipoobjeto,vthis){
	let idobjeto;
	if (tipoobjeto == 'grupo') {
		idobjeto = $('[name="_1_u__lpgrupo_idlpgrupo"]').val() || getUrlParameter('idlpgrupo') || undefined;
	}else if('painel'){
		idobjeto = $(vthis).attr("idgrupo");
	}

	if(idobjeto !== undefined && idobjeto != ''){
		CB.post({
			objetos: {
				"tipobjeto_inativar_":tipoobjeto,
				"idobjeto_inativar_":idobjeto,
				"status_inativar_":$(vthis).val(),
			}
		})
	}
}

function salvarDashConf(idLp){
	$(`#popMenuDash_${idLp}`).webuiPopover('hide');

	let jDash = getJDashBoardConf(idLp);
	let temp;
	let ordem;

	for(let i in jDash["dashgrupo"]){
		if(jDash["dashgrupo"][i]){
			for(let j in jDash["dashgrupo"][i]["dashpanel"]){
				if(jDash["dashgrupo"][i]["dashpanel"][j]){
					let idGrupo = jDash["dashgrupo"][i]["id"];
					let idPanel = jDash["dashgrupo"][i]["dashpanel"][j]["id"];

					jDash["dashgrupo"][i]["dashpanel"][j]["dashcard"] = [];
					jDash["dashgrupo"][i]["dashpanel"][j]["ymax"] = $("#dashpanel-conf-"+idPanel+"-"+idGrupo+"-"+idLp).attr('y-max');

					$("#dashpanel-conf-"+idPanel+"-"+idGrupo+"-"+idLp+" .card-space").children().each(function(k, o){
						let $o = $(o);

						jDash["dashgrupo"][i]["dashpanel"][j]["dashcard"].push({
							iddashcard : $o.attr('iddashcard'),
							x : $o.parent().attr('x'),
							y : $o.parent().attr('y'),
							cor: $o.attr('cor'),
							titulo: $o.attr('titulo'),
							titulopersonalizado: $o.attr('titulopersonalizado') || '',
							subtitulo: $o.attr('subtitulo'),
						});
					});

					ordem = parseInt($("#div-conf-"+idPanel+"-"+idGrupo+"-"+idLp).attr('ordem'));

					if(jDash["dashgrupo"][i]["dashpanel"][j]["ordem"] != ordem){
						jDash["dashgrupo"][i]["dashpanel"][j]["ordem"] = ordem;

						if(jDash["dashgrupo"][i]["dashpanel"][ordem]){
							jDash["dashgrupo"][i]["dashpanel"][ordem]["ordem"] = j;
						}

						temp = jDash["dashgrupo"][i]["dashpanel"][ordem];
						jDash["dashgrupo"][i]["dashpanel"][ordem] = jDash["dashgrupo"][i]["dashpanel"][j];
						jDash["dashgrupo"][i]["dashpanel"][j] = temp;
					}
				}
			}

			ordem = $("#div-conf-"+jDash["dashgrupo"][i]["id"]+"-"+idLp).attr('ordem');
			if(jDash["dashgrupo"][i]["ordem"] != ordem){
				jDash["dashgrupo"][i]["ordem"] = ordem;

				if(jDash["dashgrupo"][ordem]){
					jDash["dashgrupo"][ordem]["ordem"] = i;
				}
				

				temp = jDash["dashgrupo"][ordem];
				jDash["dashgrupo"][ordem] = jDash["dashgrupo"][i];
				jDash["dashgrupo"][i] = temp;
			}
		}
	}

	jDash["dashgrupo"] = jDash["dashgrupo"].filter(n => n);
	for(let i in jDash["dashgrupo"]){
		jDash["dashgrupo"][i]["id"] = i;
		jDash["dashgrupo"][i]["dashpanel"] = jDash["dashgrupo"][i]["dashpanel"].filter(n => n);
		for(let j in jDash["dashgrupo"][i]["dashpanel"]){
			jDash["dashgrupo"][i]["dashpanel"][j]["id"] = j;
		}
	}

	setJDashBoardConf(idLp, jDash);
	jsonDashConfAlterado(idLp, 'hide')
}

function editarDashGrupo(descr, idLp, idGrupo){
	let vthis = $(`#rot-conf-${idGrupo}-${idLp}`);
	let beforeChange = vthis.html();

	$(`#pop-conf-${idGrupo}-${idLp}`).webuiPopover('hide');

	let $oInput = $(`
		<div style="display: inline-flex;">
			<input type="text" id="edit_conf_${idGrupo}_${idLp}" style="width:80%;" value="${descr}">
			<div class="editButton pointer"><i class="fa fa-times vermelho" id="edit_conf_cancel_${idGrupo}_${idLp}"></i></div>
			<div class="editButton pointer"><i class="fa fa-check verde" id="edit_conf_confirm_${idGrupo}_${idLp}"></i></div>
		</div>
	`);

	$oInput.find(`#edit_conf_cancel_${idGrupo}_${idLp}`).on('click', function(){
		$(vthis).html(beforeChange);
		$(`#pop-conf-${idGrupo}-${idLp}`).webuiPopover({content:popMenuDashConfGrupo(idLp, idGrupo, descr)});
	});

	$oInput.find(`#edit_conf_confirm_${idGrupo}_${idLp}`).on('click', function(){
		let str = $(`#edit_conf_${idGrupo}_${idLp}`).val();

		if(str.trim() == descr.trim()){
			$(vthis).html(beforeChange);
			$(`#pop-conf-${idGrupo}-${idLp}`).webuiPopover({content:popMenuDashConfGrupo(idLp, idGrupo, descr)});
			return;
		} 

		if(str.trim() == ""){
			$(`#edit_conf_${idGrupo}_${idLp}`).addClass("alertaCbvalidacao");
			alertAtencao("Campo não pode ser vazio!");
			return;
		}

		let jDash = getJDashBoardConf(idLp);
		jDash["dashgrupo"][idGrupo]["rotulo"] = str.trim();
		setJDashBoardConf(idLp, jDash);
		jsonDashConfAlterado(idLp);

		$(vthis).html(beforeChange.replace(descr, str.trim()));
		$(`#pop-conf-${idGrupo}-${idLp}`).webuiPopover({content:popMenuDashConfGrupo(idLp, idGrupo, str.trim())});
	});

	$(vthis).html($oInput);
	$(`#edit_conf_${idGrupo}_${idLp}`).select();
}

function editarDashPanel(descr, idLp, idGrupo, idPanel){
	let vthis = $(`#rot-conf-${idPanel}-${idGrupo}-${idLp}`);
	let beforeChange = vthis.html();

	$(`#pop-conf-${idPanel}-${idGrupo}-${idLp}`).webuiPopover('hide');

	let $oInput = $(`
		<div style="display: inline-flex;">
			<input type="text" id="edit_conf_${idPanel}_${idGrupo}_${idLp}" style="width:80%;" value="${descr}">
			<div class="editButton pointer"><i class="fa fa-times vermelho" id="edit_conf_cancel_${idPanel}_${idGrupo}_${idLp}"></i></div>
			<div class="editButton pointer"><i class="fa fa-check verde" id="edit_conf_confirm_${idPanel}_${idGrupo}_${idLp}"></i></div>
		</div>
	`);

	$oInput.find(`#edit_conf_cancel_${idPanel}_${idGrupo}_${idLp}`).on('click', function(){
		$(vthis).html(beforeChange);
		$(`#pop-conf-${idPanel}-${idGrupo}-${idLp}`).webuiPopover({content:popMenuDashConfPanel(idLp, idGrupo, idPanel, descr)});
	});

	$oInput.find(`#edit_conf_confirm_${idPanel}_${idGrupo}_${idLp}`).on('click', function(){
		let str = $(`#edit_conf_${idPanel}_${idGrupo}_${idLp}`).val();

		if(str.trim() == descr.trim()){
			$(vthis).html(beforeChange);
			$(`#pop-conf-${idPanel}-${idGrupo}-${idLp}`).webuiPopover({content:popMenuDashConfPanel(idLp, idGrupo, idPanel, descr)});
			return;
		} 

		if(str.trim() == ""){
			$(`#edit_conf_${idPanel}_${idGrupo}_${idLp}`).addClass("alertaCbvalidacao");
			alertAtencao("Campo não pode ser vazio!");
			return;
		}

		let jDash = getJDashBoardConf(idLp);
		jDash["dashgrupo"][idGrupo]["dashpanel"][idPanel]["rotulo"] = str.trim();
		setJDashBoardConf(idLp, jDash);
		jsonDashConfAlterado(idLp);

		$(vthis).html(beforeChange.replace(descr, str.trim()));
		$(`#pop-conf-${idPanel}-${idGrupo}-${idLp}`).webuiPopover({content:popMenuDashConfPanel(idLp, idGrupo, idPanel, str.trim())});
	});

	$(vthis).html($oInput);
	$(`#edit_conf_${idPanel}_${idGrupo}_${idLp}`).select();
}

function removerDash(idLp, idGrupo, idPanel){
	let str = (idPanel) ? "painel" : "grupo";
	if(!confirm("Deseja realmente excluir este "+str+"?"));

	let jDash = getJDashBoardConf(idLp);
	if(idPanel != undefined){
		delete jDash["dashgrupo"][idGrupo]["dashpanel"][idPanel];
		setJDashBoardConf(idLp, jDash);

		$(`#pop-conf-${idPanel}-${idGrupo}-${idLp}`).webuiPopover('hide');
		$(`#div-conf-${idPanel}-${idGrupo}-${idLp}`).remove();

		$("#dashpanel-conf-"+idGrupo+"-"+idLp+">div[ordem]").each(function(i, o){
			let ordem = o.getAttribute('ordem');
			if(i != ordem){
				$("#dashpanel-conf-"+idGrupo+"-"+idLp+">div[ordem='"+i+"']").attr('ordem', ordem);
				o.setAttribute('ordem', i);
			}
		});
	}else{
		delete jDash["dashgrupo"][idGrupo];
		setJDashBoardConf(idLp, jDash);

		$(`#pop-conf-${idGrupo}-${idLp}`).webuiPopover('hide');
		$(`#div-conf-${idGrupo}-${idLp}`).remove();

		$("#dashgrupo-conf-"+idLp+">div[ordem]").each(function(i, o){
			let ordem = o.getAttribute('ordem');
			if(i != ordem){
				$("#dashgrupo-conf-"+idLp+">div[ordem='"+i+"']").attr('ordem', ordem);
				o.setAttribute('ordem', i);
			}
		});
	}
	jsonDashConfAlterado(idLp);
}

function novoDashGrupo(idLp){
	let oInp = $("#novo-dashgrupo-input-"+idLp);
	let str = oInp.val().trim();
	let corSistema = oInp.attr('cor-sistema') || "#666666";
	
	if(str != ""){
		let jDashConf = getJDashBoardConf(idLp);
		let posicao = jDashConf["dashgrupo"].push({
			rotulo      : str,
			corsistema  : corSistema,
			dashpanel   :[],
		});

		jDashConf["dashgrupo"][posicao - 1]["id"] = posicao - 1;
		jDashConf["dashgrupo"][posicao - 1]["ordem"] = posicao - 1;

		setJDashBoardConf(idLp, jDashConf);

		oInp.val("");

		construirDashGrupo('w', posicao - 1, idLp, getJDashBoardConf(idLp));
		jsonDashConfAlterado(idLp);
	}
}

function novoDashPanel(idGrupo, idLp){
	let oInp = $("#novo-dashpanel-input-"+idGrupo+"-"+idLp);
	let str = oInp.val().trim();
	
	if(str != ""){
		let jDashConf = getJDashBoardConf(idLp);

		// aqui também poderia ser utilizado o .filter
		// mas como o ID do grupo é o mesmo que a posição dele no Array,
		// podemos acessá-lo dessa maneira

		let posicao = jDashConf["dashgrupo"][idGrupo]["dashpanel"].push({
			rotulo     	: str,
			ymax 		: 0,
			dashcard   	:[],
		});

		jDashConf["dashgrupo"][idGrupo]["dashpanel"][posicao - 1]["id"] = posicao - 1;
		jDashConf["dashgrupo"][idGrupo]["dashpanel"][posicao - 1]["ordem"] = posicao - 1;

		setJDashBoardConf(idLp, jDashConf);

		oInp.val("");

		construirDashPanel('w', posicao - 1, idGrupo, idLp, getJDashBoardConf(idLp));
		jsonDashConfAlterado(idLp);
	}
}

function addEnterEventDash(vthis, idlp){
	if(!vthis.onkeyup){
		vthis.onkeyup = function(e) {
			if(e.keyCode == 13){
				$(vthis).siblings().click();
			}
		};
	}
}

function removeEnterEventDash(vthis, idlp){
	vthis.onkeyup = null;
}

function popMenuDashConfGrupo(idLp, idGrupo, descr){
	let popContent = `
		<ul class="popul">
			<li class="pointer" onclick="editarDashGrupo('${descr}', ${idLp}, ${idGrupo})"><i class="fa fa-pencil"></i> Editar Grupo</li>
			<li class="pointer" onclick="ordenarDashPanel(${idLp}, ${idGrupo})"><i class="fa fa-arrows"></i> Ordenar Painéis</li>
			<li class="pointer" onclick="removerDash(${idLp}, ${idGrupo})"><i class="fa fa-trash"></i> Excluir Grupo</li>
		</ul>
	`;

	return popContent;
}

function popMenuDashConfPanel(idLp, idGrupo, idPanel, descr){
	let popContent = `
		<ul class="popul">
			<li class="pointer" onclick="editarDashPanel('${descr}', ${idLp}, ${idGrupo}, ${idPanel})"><i class="fa fa-pencil"></i> Editar Painel</li>
			<li class="pointer" onclick="addCardDashPanel(${idPanel}, ${idGrupo}, ${idLp});"><i class="fa fa-plus"></i> Adicionar Indicadores</li>
			<li class="pointer" onclick="editarCard(${idPanel},${idGrupo}, ${idLp});"><i class="fa fa-edit"></i> Editar Indicadores</li>
			<li class="pointer" onclick="removerDash(${idLp}, ${idGrupo}, ${idPanel})"><i class="fa fa-trash"></i> Excluir Painel</li>
		</ul>
	`;

	return popContent;
}
function editarCard(idPanel,idGrupo,idlp){
	$(`i.fa-ellipsis-v`).addClass('hidden');
	$(`#pop-conf-${idPanel}-${idGrupo}-${idlp}`).webuiPopover('hide');
	$('.input-group').addClass('hidden');
	$(`#rot-conf-${idPanel}-${idGrupo}-${idlp} :first-child`).first().append(`<button id="button-conf-${idPanel}-${idGrupo}-${idlp}" class="btn btn-success btn-xs" onclick="voltaCard(${idPanel},${idGrupo},${idlp})" style="margin-left: 20px;" ><i class="fa fa-check"></i> Finalizar</button>`)
	$(`#rot-conf-${idPanel}-${idGrupo}-${idlp} :first-child`).first().append(`<button id="button-restaurar-${idPanel}-${idGrupo}-${idlp}" class="btn btn-primary btn-xs" onclick="restauraCard(${idPanel},${idGrupo},${idlp})" style="margin-left: 20px;" ><i class="fa fa-arrow-circle-left"></i> Restaurar Indicadores</button>`)

	$("#dashpanel-conf-"+idPanel+"-"+idGrupo+"-"+idlp).find("[iddashcard]").each(function(i,e){
		let beforeChange,$card,$oInput;
		$card = $(e);
		beforeChange = $(e).find('.text-xs.mb-1').html();
		if (beforeChange != '') {
			$oInput = $(`
				<div style="display: inline-flex;">
					<input type="text" id="edit_card_${i}_${idlp}" style="width:70%;" value="${beforeChange}">
					<div class="editButton">
						<i class="fa fa-times vermelho" id="edit_card_cancel_${i}_${idlp}"></i>
					</div>
					<div class="editButton">
						<i class="fa fa-check verde" id="edit_card_confirm_${i}_${idlp}"></i>
					</div>
				</div>
			`);
			
			$oInput.find(`#edit_card_cancel_${i}_${idlp}`).on('click', function(event){
				$(e).find('.text-xs.mb-1').html(beforeChange);
			});
			$oInput.find(`#edit_card_confirm_${i}_${idlp}`).on('click',$(e), function(event){
				$str = $(`#edit_card_${i}_${idlp}`).val();
				if($str != beforeChange){
					$(e).attr("titulopersonalizado",$str);
					$(e).find('.text-xs.mb-1').html($str);
					jsonDashConfAlterado(idlp,'show');
				}else{
					$(e).find('.text-xs.mb-1').html(beforeChange);
				}
			});
			$(e).find('.text-xs.mb-1').html($oInput);
		}
		
	});
}

function voltaCard(idPanel,idGrupo,idlp){
	$("#dashpanel-conf-"+idPanel+"-"+idGrupo+"-"+idlp).find("[iddashcard]").each(function(i,e){
		if ($(e).find(`#edit_card_${i}_${idlp}`).val()) {
			if(($(e).attr("titulo") != $(e).find(`#edit_card_${i}_${idlp}`).val()) || ($(e).attr("titulopersonalizado") != $(e).find(`#edit_card_${i}_${idlp}`).val())){
				$(e).attr("titulopersonalizado",$(e).find(`#edit_card_${i}_${idlp}`).val());
				jsonDashConfAlterado(idlp,'show');
			}
			$(e).find('.text-xs.mb-1').html($(e).find(`#edit_card_${i}_${idlp}`).val());
		}
	});
	$(`i.fa-ellipsis-v`).removeClass('hidden');
	$('.input-group').removeClass('hidden');
	$(`#button-conf-${idPanel}-${idGrupo}-${idlp}`).remove();
	$(`#button-restaurar-${idPanel}-${idGrupo}-${idlp}`).remove();
}

function restauraCard(idPanel,idGrupo,idlp){
	$("#dashpanel-conf-"+idPanel+"-"+idGrupo+"-"+idlp).find("[iddashcard]").each(async function(i,e){
		let iddash = $(e).attr('iddashcard');
		await $.ajax({
			type: "get",
			url : "ajax/getCardName.php",
			data: { iddashcard : iddash },
			success: function(data){
				$(e).attr('titulo',data);
				$(e).attr('titulopersonalizado','');
				$(e).find('.text-xs.mb-1').html(data);
			},
			error: function(objxmlreq){
				console.error('Erro:<br>'+objxmlreq.status); 
			}
		});
		jsonDashConfAlterado(idlp,'show');
	});
	$(`i.fa-ellipsis-v`).removeClass('hidden');
	$('.input-group').removeClass('hidden');
	$(`#button-conf-${idPanel}-${idGrupo}-${idlp}`).remove();
	$(`#button-restaurar-${idPanel}-${idGrupo}-${idlp}`).remove();
}

function addCardDashPanel(idPanel, idGrupo, idLp){

	if($(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).hasClass('drag-drop-enable')){
		$(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).removeClass('drag-drop-enable');

		$(`#div-conf-${idPanel}-${idGrupo}-${idLp} button`).addClass('hidden');
		$(`#excluir-conf-${idPanel}-${idGrupo}-${idLp}`).addClass('hidden');

		$(`#dashgrupo-conf-${idLp}`).children('.hidden').removeClass('hidden');
		$(`#dashpanel-conf-${idGrupo}-${idLp}`).children('.hidden').removeClass('hidden');

		$(`i.fa-ellipsis-v`).removeClass('hidden');
		$('.input-group').removeClass('hidden');

		disableDragDrop(idPanel, idGrupo, idLp);
	}else{
		$(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).addClass('drag-drop-enable');

		$(`#div-conf-${idPanel}-${idGrupo}-${idLp} button.hidden`).removeClass('hidden');
		$(`#excluir-conf-${idPanel}-${idGrupo}-${idLp}`).removeClass('hidden');

		$(`#pop-conf-${idPanel}-${idGrupo}-${idLp}`).webuiPopover('hide');

		$(`#dashgrupo-conf-${idLp}`).children().not(`#div-conf-${idGrupo}-${idLp}`).addClass('hidden');
		$(`#dashpanel-conf-${idGrupo}-${idLp}`).children().not(`#div-conf-${idPanel}-${idGrupo}-${idLp}`).addClass('hidden');

		$(`i.fa-ellipsis-v`).addClass('hidden');

		$('.input-group').addClass('hidden');

		enableDragDrop(idPanel, idGrupo, idLp);
	}

}

function disableDragDrop(idPanel, idGrupo, idLp){
	$(`.column-${idPanel}-${idGrupo}-${idLp}.drop-enable`).droppable('destroy');
	$(`#dashcards-disponiveis-${idLp} div[iddashcard].card.drag-enable`).draggable('destroy');
	$(`#excluir-conf-${idPanel}-${idGrupo}-${idLp}`).droppable('destroy');
	$(`.column-${idPanel}-${idGrupo}-${idLp} > div[iddashcard]`).draggable('destroy');
	
	$(`.column-${idPanel}-${idGrupo}-${idLp}`).removeClass("drop-enable");
	$(`#dashcards-disponiveis-${idLp} div[iddashcard].card`).removeClass("drag-enable");
	$(`#excluir-conf-${idPanel}-${idGrupo}-${idLp}`).removeClass('drop-enable');
}

function enableDragDrop(idPanel, idGrupo, idLp){
	let options = {
		helper: function( event ) {
			return $( "<div class='ui-widget-header' style='width:70%'>"+event.currentTarget.innerHTML+"</div>" );
		},
	}

	$(`#dashcards-disponiveis-${idLp} div[iddashcard].card`).not('.drag-enable').draggable(options);

	$(`.column-${idPanel}-${idGrupo}-${idLp} > div[iddashcard]`).draggable(options);

	$(`#excluir-conf-${idPanel}-${idGrupo}-${idLp}`).not('drop-enable').droppable({
		accept: `.column-${idPanel}-${idGrupo}-${idLp} > div[iddashcard]`,
		drop: function( event, ui ) {
			$(`.dashcard-disponivel[iddashcard="${ui.draggable.attr('iddashcard')}"]`).show();
			ui.draggable.remove();
			jsonDashConfAlterado(idLp);
		}
	});

	$(`.column-${idPanel}-${idGrupo}-${idLp}`).not(".drop-enable").droppable({
		accept: `.column-${idPanel}-${idGrupo}-${idLp} > div[iddashcard], #dashcards-disponiveis-${idLp} div[iddashcard].card`,
		drop: function( event, ui ) {
			if($(event.target).children().length == 0){
				if(!(ui.draggable.parent().hasClass('card-space'))){
					ui.draggable.parent().append(ui.draggable.clone().removeClass('drag-enable'));
					ui.draggable.parent().hide();
					enableDragDrop(idPanel, idGrupo, idLp);
				}
				$(event.target).append(ui.draggable);
				jsonDashConfAlterado(idLp);
			}
		}
	});

	$(`.column-${idPanel}-${idGrupo}-${idLp}`).addClass("drop-enable");
	$(`#dashcards-disponiveis-${idLp} div[iddashcard].card`).addClass("drag-enable");
	$(`#excluir-conf-${idPanel}-${idGrupo}-${idLp}`).addClass('drop-enable');
}

function constroiCardSpaceByLvl(level, idPanel, idGrupo, idLp){
	let x = 12;
	let col = 1;
	let content = "";

	for(let i = 0; i < x; i++){
		content += `<div class="card-space column-${idPanel}-${idGrupo}-${idLp} p10 col-md-${col} card-space-height" x="${i}" y="${level}" style="height: 91px;"></div>`;
	}

	return content;
}

function adicionarLinhaDashPanel(idPanel, idGrupo, idLp){
	let ymax = $(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).attr('y-max');
	$(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).append(constroiCardSpaceByLvl(parseInt(ymax)+1,idPanel, idGrupo, idLp));
	$(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).attr('y-max', parseInt(ymax)+1);
	enableDragDrop(idPanel, idGrupo, idLp);
	jsonDashConfAlterado(idLp);
}

function removerLinhaDashPanel(idPanel, idGrupo, idLp){
	let ymax = $(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).attr('y-max');
	if(ymax > 0){
		$(`.column-${idPanel}-${idGrupo}-${idLp}[y=${ymax}]`).remove();
		$(`#dashpanel-conf-${idPanel}-${idGrupo}-${idLp}`).attr('y-max', parseInt(ymax)-1);
		jsonDashConfAlterado(idLp);
	}
}

function getJDashBoardConf(idLp){
	return JSON.parse($("#jsondashboardconf_"+idLp).val());
}

function setJDashBoardConf(idLp, json){
	$("#jsondashboardconf_"+idLp).val(JSON.stringify(json));
}

function jsonDashConfAlterado(idLp, cmd = 'show'){
	if(cmd == 'show'){
		$("#_salvarJsondashboardconf_"+idLp).addClass('json-changed');
		$("#_salvarJsondashboardconfIndicator_"+idLp).removeClass('hidden');
	}else{
		$("#_salvarJsondashboardconf_"+idLp).removeClass('json-changed');
		$("#_salvarJsondashboardconfIndicator_"+idLp).addClass('hidden');
	}
}

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>