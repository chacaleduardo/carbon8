<?

?>
<script>

var $jDocvinc = [];
var $jFuncAssinado=[];
var $jImgrupovinc = [];
var $jCli = [];
var $jTipoSubtDoc = [];
var $idPessoa = <?=$_SESSION["SESSAO"]["IDPESSOA"]?>;
var $editor1=$("#editor1");
var scrollWait;

if($("[name=_1_u_sgdoc_idsgdoc]").val()){
    $jDocvinc      = <?=getJDocvinc();?>;
    $jFuncAssinado = <?=DocumentoController::toJson(listaDocfuncassinatura()) ?? []?>;
    $jImgrupovinc  = <?=DocumentoController::toJson(getJImgrupovinc());?>;
    $jCli          = <?=$JSON->encode($arrCli)?>;
    $jTipoSubtDoc  = <?=getJTipodoc();?>;

}

//Montar legenda para o usuário
CB.montaLegenda({"#5cb85c": "Documento Assinado (versão atual)", "#337ab7": "Assinatura Solicitada.", "#f0ad4e": "Assinatura Pendente", "silver": "Assinatura Pendente (participante individual)"});
CB.oPanelLegenda.css( "zIndex", 901).addClass('screen');
$('.selectpicker').selectpicker('render');
$("#funcvinc2").change((e) =>{
		!$(e.target).val() ? $("#btnsalvar").addClass('hidden') : $("#btnsalvar").removeClass("hidden")
});

if (getUrlParameter('idsgdoccp') && getUrlParameter('idsgdoc')) {
	window.location.assign("?_modulo="+getUrlParameter('_modulo')+"&_acao=u&idsgdoc="+getUrlParameter('idsgdoc'));
}
//Controla o evento scroll para que ele não seja executado imediatamente. Isto evita alterações oriundas da renderização dos elementos na tela
window.onscroll = () => {
clearTimeout(scrollWait);
scrollWait = setTimeout(scrollFinished,500);
}

//Armazena o scroll vertical do editor wysiwyg
$editor1.on("scroll", function(){

	$(":input[name=_1_"+CB.acao+"_sgdoc_scrolleditor]").val($editor1.scrollTop());	
	console.log($editor1.scrollTop());

});

CB.on("prePost", function (inParam) {
	if (inParam === undefined) {
			inParam = {
				objetos:{}
			};
		}
	if(inParam.objetos===undefined || inParam.parcial !== true){
		let $editor=tinyMCE.get('editor1');
		if($editor){
			$(":input[name=_1_"+CB.acao+"_sgdoc_conteudo]").val($editor.getContent());
		}
	}else if(inParam.parcial === true && inParam.objetos){
		// add objetos
		let $editor=tinyMCE.get('editor1');
		if($editor){
			if ($('[name=_1_'+CB.acao+'_sgdoc_status]').val() != 'APROVADO' && !(inParam.objetos['_removeContent_u_sgdoc_idsgdoc'])) {
				$(":input[name=_1_"+CB.acao+"_sgdoc_conteudo]").val($editor.getContent().replaceAll("'","&#39"));
				inParam.objetos["_addDocContent_u_sgdoc_conteudo"] = $(":input[name=_1_"+CB.acao+"_sgdoc_conteudo]").val()
				inParam.objetos["_addDocContent_u_sgdoc_idsgdoc"] = $("[name=_1_"+CB.acao+"_sgdoc_idsgdoc]").val()
			}
		}
	}
	
	return inParam;
})

/*
 * Duplicar  [ctrl]+[d]
 */
$(document).keydown(function(event) {

if (!((event.ctrlKey || event.altKey) && event.keyCode == 68)) return true;

if(!teclaLiberada(event)) return;//Evitar repetição do comando abaixo

janelamodal('?_modulo=documento&_acao=i&idsgdoccp=<?=$_1_u_sgdoc_idsgdoc?>');

return false;
});

if ($("#nao_edita_doc").val() == "Y") {
    ST.on('posCarregaFluxo', function(){               
        $('#cbSteps .botaofluxo').hide();
    });     
}

//Filtrar lista de assinaturas

$("#filter").keyup(function() {

    // Retrieve the input field text and reset the count to zero
    var filter = $(this).val(),
    count = 0;

    // Loop through the comment list
    $('.filtrarAssinaturaPorNome .col-md-12').each(function() {


        // If the list item does not contain the text phrase fade it out
        if ($(this).text().search(new RegExp(filter, "i")) < 0) {
        $(this).hide();  // MY CHANGE

        // Show the list item if the phrase matches and increase the count by 1
        } else {
        $(this).show(); // MY CHANGE
        count++;
        }

    });

});

if ($("#_permissaoeditardoctemp_").val() != "Y" && $("#docmaster").val() != "Y") {
	$("#editor1").addClass('hidden');
	$("[tinydisabled]").addClass('hidden');
	$("[tinyactive]").removeClass('hidden');
}else{
	$("body").removeClass("somenteleitura")
}

if($("#carimbopendente"+$idPessoa).length > 0 && $(":input[name=_1_"+CB.acao+"_sgdoc_status]").val() == "APROVADO"){
    botaoAssinar($("#carimbopendente"+$idPessoa).attr("idcarrimbo"),$(":input[name=_1_"+CB.acao+"_sgdoc_versao]").val());
}

if(($(":input[name=_1_"+CB.acao+"_sgdoc_status]").val() == "APROVADO" || $(":input[name=_1_"+CB.acao+"_sgdoc_status]").val() =="REPROVADO" || $(":input[name=_1_"+CB.acao+"_sgdoc_status]").val()  == 'OBSOLETO') && $(":input[name=_1_"+CB.acao+"_sgdoc_idsgdoc]").val()){
    $(":input[name=_1_"+CB.acao+"_sgdoc_idsgdoctipodocumento]").prop("disabled", true);
    $(":input[name=_1_"+CB.acao+"_sgdoc_titulo]").prop("disabled", true);
    $(":input[name=_1_"+CB.acao+"_sgdoc_responsavel]").prop("disabled", true);     
    $(":input[name=_1_"+CB.acao+"_sgdoc_idpessoa]").prop("disabled", true); 
    $(":input[name=_1_"+CB.acao+"_sgdoc_fim]").prop("disabled", true); 
     $(":input[name*=_u_sgdocpag_]").prop("disabled", true); 
}

//Resetar o objeto tinymce para não ficar desabilitado no refresh/reload
if(tinyMCE.editors["editor1"]){tinyMCE.editors["editor1"].remove()};

$(".flquestionario").each((i,e)=>{
	if(tinyMCE.editors[$(e).attr('name')]){
		tinyMCE.editors[$(e).attr('name')].remove()
	}
});
	
//Inicializa Editor
tinymce.init({
	selector: "#editor1"
	,language: 'pt_BR'
	,inline: true /* não usar iframe */
	,toolbar: 'formatselect | removeformat | fontsizeselect | forecolor backcolor | bold | alignleft aligncenter alignright alignjustify | subscript superscript | bullist numlist | table | pagebreak'
	,menubar: false
	,plugins: ['table','pagebreak','textcolor','lists']
	,fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt"
	,content_style: "html body .mce-content-body {color:black;}"
	//,pagebreak_separator: "<div style='page-break-before: always;clear:both;'></div>"
	,setup: function (editor) {
		editor.on('init', function (e) {
			//Recupera o conteudo do DB
			if($(":input[name=_1_"+CB.acao+"_sgdoc_conteudo]").length){
				this.setContent($(":input[name=_1_"+CB.acao+"_sgdoc_conteudo]").val());
				setTimeout(function(){
					$editor1.removeClass("tranparente").addClass("opaco");
				$editor1.scrollTop($(":input[name=_1_"+CB.acao+"_sgdoc_scrolleditor]").val());
			}, 1000);
                    }
		});
	}

});
//Inicializa Editor
tinymce.init({
	selector: ".tiny"
	,language: 'pt_BR'
	,inline: true /* não usar iframe */
	,body_class: 'mult_editor'
	,toolbar: 'formatselect | removeformat | fontsizeselect | forecolor backcolor | bold | subscript superscript | bullist numlist '
	,menubar: false
	,plugins: []
	,width:400
	,max_widht:400
	,resize: "both"
	,fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt"
	,content_style: "html body .mce-content-body {color:black;}"
	//,pagebreak_separator: "<div style='page-break-before: always;clear:both;'></div>"
	,setup: function (editor) {
		editor.on('init', function (e) {
			//Recupera o conteudo do DB
			this.setContent($(this.bodyElement).siblings('textarea').val());
			if ($(this.bodyElement).attr('editavel')) {
				
			}
			setTimeout(function(prop){
				$(prop.bodyElement).removeClass("tranparente").addClass("opaco");
			}, 1000,this);

		});
        editor.on('input', atualizaConteudo);
        editor.on('Change', atualizaConteudo);
	}
});

if( $("[name=_1_u_sgdoc_idsgdoc]").val() ){
	$("#upload").dropzone({
		idObjeto: $("[name=_1_u_sgdoc_idsgdoc]").val()
		,tipoObjeto: 'sgdoc'
		,idPessoaLogada: $idPessoa
	});
	$("#uploadcertificado").dropzone({
		idObjeto: $("[name=_1_u_sgdoc_idsgdoc]").val()
		,tipoObjeto: 'sgdoccertificado'
		,idPessoaLogada: $idPessoa
	});
}

function assinatodos(idsgdoc){
	CB.post({
		objetos :{
			"_1_u_sgdoc_idsgdoc":idsgdoc
			,"versao_doc":$("[name*=_sgdoc_versao]").val()
			,"carrimbo_todos":"Y"
		},parcial:true
	})
}

function vinculadoc(inidoctipo,inidsgdoc,qst=null){
	if (inidoctipo == 'avaliacao') {
		idsgdoctipodoc = $('#nova_avaliacao').val();
		titulo = $('#nova_avaliacaot').val();
	} 
	if (inidoctipo == 'treinamento') {
		idsgdoctipodoc = $('#novo_treinamento').val();
		titulo = $('#novo_treinamentot').val();
	}
	if (titulo == '') {
		alertAtencao("Titulo não pode estar vazio!");
	}
	if (idsgdoctipodoc == '') {
		alertAtencao("Classificação não pode estar vazio!");
	}
	if (qst == "Y") {
		_qst_ = "Y";
	}else{
		_qst_ = null;
	}
	if (titulo != '' && idsgdoctipodoc != ''){
		CB.post({
		objetos:{
		"_new_doc":"Y"
		,"_x_i_sgdoc_idsgdoctipo":inidoctipo
		,"_x_i_sgdoc_idsgdoctipodocumento":idsgdoctipodoc
		,"_x_i_sgdoc_status":'AGUARDANDO'
	    ,"_x_i_sgdoc_titulo": titulo
	    ,"_versao_": $("[name*=_sgdoc_versao]").val()
	    ,"_vinculo_": inidsgdoc
		,_qst_
		}
		,parcial: true
		});
	}
}

function apagaclass(inidsgdoc){
if(confirm("Ao trocar a classificação do documento todo o conteudo será apagado. Deseja prosseguir?")){	
	$('[name=_1_u_sgdoc_conteudo]').val('');	
        CB.post({
        objetos: {
			"_removeContent_u_sgdoc_idsgdoc" : inidsgdoc
			,"_removeContent_u_sgdoc_idsgdoctipodocumento" : ''
			,"_removeContent_u_sgdoc_conteudo" : ''
		}
		,parcial:true

        });
    }
}

function criaclassificacao(){
	janelamodal('?_modulo=tipodocumento&_acao=i',1000,1100);
}

function botaoVoltarVersao(){
		CB.novoBotaoUsuario({
			id:"voltarVersaoDoc"
			,rotulo:"Voltar Versão"
			,icone:"fa fa-undo"
			,class:"btn btn-danger"
			,onclick:function(){
				let versaoatual = $(":input[name=_1_"+CB.acao+"_sgdoc_conteudo]").val()
				var htmlmodal = $(`#mdvoltarversao`).html()

					CB.modal({
						titulo: "</strong>Voltar Versão do Documento</strong>",
						corpo: [htmlmodal],
						classe: 'cinquenta',
					});
			}
		});
}

function limpaclassificacao(inidoctipo){
	
	if (inidoctipo == 'avaliacao') {
		//nome = "Avaliação";
		idsgdoctipodoc = $('#nova_avaliacao');
		$(idsgdoctipodoc).val('');
		CB.post({
		objetos: {
				"_x_u_sgdoc_idsgdoc": $(":input[name=_1_"+CB.acao+"_sgdoc_idsgdoc]").val()
				,"_x_u_sgdoc_tipoavaliacao": ''
			}
			,parcial: true
	});
	}
	if(inidoctipo == 'treinamento'){
		//nome = "Treinamento";
		idsgdoctipodoc = $('#novo_treinamento');
		$(idsgdoctipodoc).val('');
		CB.post({
		objetos: {
				"_x_u_sgdoc_idsgdoc": $(":input[name=_1_"+CB.acao+"_sgdoc_idsgdoc]").val()
				,"_x_u_sgdoc_tipotreinamento": ''
			}
			,parcial: true
	});
	}
}

function editaclassificacao(inidoctipo){
	if (inidoctipo == 'avaliacao') {
		//nome = "Avaliação";
		idsgdoctipodoc = $('#nova_avaliacao').val();
	}
	if(inidoctipo == 'treinamento'){
		//nome = "Treinamento";
		idsgdoctipodoc = $('#novo_treinamento').val();
	}
	janelamodal('?_modulo=tipodocumento&_acao=u&idsgdoctipodocumento='+idsgdoctipodoc,1000,1100);
}

function tipoclassificacao(tipoclass){
    if (tipoclass == 'treinamento') {
        CB.post({
                objetos: {
                    "_x_u_sgdoc_idsgdoc": $(":input[name=_1_"+CB.acao+"_sgdoc_idsgdoc]").val()
                    ,"_x_u_sgdoc_tipotreinamento": $('#novo_treinamento').val()
                }
                ,parcial: true
            });
    }
    if (tipoclass == 'avaliacao') {
        CB.post({
                objetos: {
                    "_x_u_sgdoc_idsgdoc": $(":input[name=_1_"+CB.acao+"_sgdoc_idsgdoc]").val()
                    ,"_x_u_sgdoc_tipoavaliacao": $('#nova_avaliacao').val()
                }
                ,parcial: true
            });
    }
}

function atualizaConteudo()
{
	if (($(this.bodyElement).attr('editavel') == 'Y') || ('<?=$_1_u_sgdoc_criadopor?>' == '<?=$_SESSION['SESSAO']['USUARIO']?>')) {
		if(!($(this.bodyElement).siblings('textarea').val() == this.getContent())){
			$(this.bodyElement).siblings('textarea').val(this.getContent())
		}
	}else{
		alertAtencao("Este campo não é editavel.");
		this.setContent($(this.bodyElement).siblings('textarea').val());
	}
    
}

function botaoAssinar(inidcarrimbo,versao){
    $bteditar = $("#btAssina");
    if($bteditar.length==0){
	CB.novoBotaoUsuario({
	    id:"btAssina"
	    ,rotulo:"Assinar"
	    ,class:"verde"
	    ,icone:"fa fa-pencil"
	    ,onclick:function(){
			// GVT - 10/03/2021 - Removido o ajax a pedido do Marcelo Amorim por falta de segurança
			CB.post({
				objetos: "idcarrimbo="+inidcarrimbo+"&versao="+versao
				,urlArquivo: "ajax/assinarcarrimbo.php?idcarrimbo="+inidcarrimbo+"&versao="+versao
				,parcial:true
				,refresh: false
				,msgSalvo: false
				,posPost: function (data, textStatus, jqXHR) {
					if(jqXHR["responseText"] == "Erro"){
                        alertAtencao("Erro ao assinar");
					} else if(jqXHR["responseText"] == "Treinamento"){
                        alertAtencao("É necessário concluir o treinamento antes de assinar este documento");
					}else{
						alertSalvo("Assinado");
						$('#btAssina').hide();
					}
				}
			});
	    }
	});
    }
}

function criaassinatura(idpessoa,modulo,idmodulo,versao,cassinar,idfluxostatuspessoa, idcarrimbo = null){
	if(cassinar=='N'){
		//Desmarca a opção de Assinar, apagando o id do carrimbo
		CB.post({
			objetos:"_c1_d_carrimbo_idcarrimbo="+idcarrimbo
			,parcial: true
		});	   
	}else{
		if(versao>0){
		 CB.post({
				objetos: {
					"_x_i_carrimbo_idpessoa": idpessoa,
					"_x_i_carrimbo_tipoobjeto": modulo,
					"_x_i_carrimbo_idobjeto": idmodulo,
					"_x_i_carrimbo_versao": versao,
					"_y_u_fluxostatuspessoa_idfluxostatuspessoa": idfluxostatuspessoa,
					"_y_u_fluxostatuspessoa_assinar":'Y' 
					
				},
				parcial: true
			});
		}else{
			 CB.post({
				objetos: {
					"_x_i_carrimbo_idpessoa": idpessoa,
					"_x_i_carrimbo_tipoobjeto": modulo,
					"_x_i_carrimbo_idobjeto": idmodulo,
					"_x_i_carrimbo_versao": versao,
					"_y_u_fluxostatuspessoa_idfluxostatuspessoa": idfluxostatuspessoa,
					"_y_u_fluxostatuspessoa_assinar":'Y'				
				},
				parcial: true
			});
		}
	}
}


function voltaversaosave(idsgdoc,versaoanterior,status, idfluxostatus){
	let versao = $('#cbModalCorpo').find('#voltaversao').val();
	let desc =$('#cbModalCorpo').find('#descvoltaversao').val();

	if(versao =="" || desc.length < 10){
		alert('Preencha os campos corretamente e forneça uma descrição com no mínimo 10 caractares')
	} else {
		if (confirm('Tem certeza que deseja voltar a versão do Documento? Essa ação é Irreversível')) {
			$.ajax({
					type: "POST",
					url : "ajax/voltaversao.php",
					data: { versao_desejada : versao,
							versao_atual: versaoanterior,
							status: status,
							idfxstatus: idfluxostatus,
							descricao_motivo : desc,
							iddoc : idsgdoc
					},
					success: function(data){
						window.location.href = "?_modulo=documento&_acao=u&idsgdoc="+idsgdoc;                  
					}
			})
		} else {
			$("[data-dismiss=modal]").click()
		}
	}

}

function modalhistorirestauracaoversao(desc){
	let versaoatual = $('[name$=_sgdoc_versao]').val() || 0;
	var htmlmodalversao = $('#descversoes').html()
		CB.modal({
			titulo: "</strong>Histórico de Restaurações do Documento</strong>",
			corpo: [htmlmodalversao],
			classe: 'setenta',
		});
}

 if($("#docmaster").val() == "Y"){

	if($('#voltarVersaoDoc').length == 0){
		botaoVoltarVersao()
	}
 } 

function retirapessoa(inidfluxostatuspessoa){
		CB.post({
			objetos: "_x_d_fluxostatuspessoa_idfluxostatuspessoa="+inidfluxostatuspessoa
			,parcial: true
		});
}
function retiragrupo(inidimgrupo, inidmodulo, inidfluxostatuspessoa, local){
		CB.post({
			objetos: `_x_d_fluxostatuspessoa_tipoobjetoext=${local}&_x_d_fluxostatuspessoa_idobjetoext=${inidimgrupo}&_x_d_fluxostatuspessoa_idmodulo=${inidmodulo}&_x_d_fluxostatuspessoa_modulo=documento&_x_d_fluxostatuspessoa_idfluxostatuspessoa=${inidfluxostatuspessoa}&_local_=${local}`
			,parcial: true
		});
}


function abredoc(iddoc){
    
    CB.go("idsgdoc="+iddoc);
}

function desvincularDoc(oI){
	var id = $(oI).attr("idsgdocvinc");
	var aux = controllCollapse();
	CB.post({
		objetos: {
			"_x_d_sgdocvinc_idsgdocvinc":id
		}
		,parcial: true	
		,posPost: function(){
			showCollapse(aux);
		}
	});
}
function altrestrito(inval,inidsgdoc){
	CB.post({
		objetos: "_x_u_sgdoc_idsgdoc="+inidsgdoc+"&_x_u_sgdoc_restrito="+inval
		,parcial:true
	});
}


function desvincularSetor(inidivinc){
	var aux = controllCollapse();
	CB.post({
		objetos: {
			"_x_d_vinculos_idvinculos":inidivinc
		}
		,parcial: true
		,posPost: function(){
			showCollapse(aux);
		}			
	});
}

function desvincula(inidivinc){
	var aux = controllCollapse();
	CB.post({
		objetos: {
			"_x_d_sgdocvinc_idsgdocvinc":inidivinc
		}
		,parcial: true
		,posPost: function(){
			showCollapse(aux);
		}			
	});
}
function desvincularLoc(inidivinc,idlocal,tipolocal){
	var aux = controllCollapse();
	CB.post({
		objetos: {
			"_x_d_vinculos_idvinculos":inidivinc
			,"_idlocal_":idlocal
			,"_tipolocal_":tipolocal
		}
		,parcial: true
		,posPost: function(){
			showCollapse(aux);
		}			
	});
}
function controllCollapse(){
	var ids = new Array("#imgrupoCollapse","#setoresCollapse","#rnc","#docCollapse");
	var aux = new Array();
	ids.forEach(function(i){
		if($(i).hasClass("in")){
			aux.push(i);
		}
	});
	return aux;
}

function showCollapse(aux){
	aux.forEach(function(i){
		$(i).addClass( "in" );
	});
}

function relacionaUn(inid){

	CB.post({
		objetos: {
			"_x_i_unidadeobjeto_idobjeto":		$(":input[name=_1_"+CB.acao+"_sgdoc_idsgdoc]").val()
		   ,"_x_i_unidadeobjeto_tipoobjeto":	"sgdoc"
		   ,"_x_i_unidadeobjeto_idunidade":	inid
		}
		,parcial: true
	});
}

function desvincularUn(inid){
    CB.post({
	objetos: {
		"_x_d_unidadeobjeto_idunidadeobjeto":	inid		
	}
	,parcial: true
    });    
} 
function setcopiactr(vthis){
   
   var vimg = $("#imagencopia").attr("src");
    //alert(vimg);
    if(vimg !='../inc/img/copiancontrolada.gif'){
        $("#imagencopia").attr("src","../inc/img/copiancontrolada.gif");
    }else{
        $("#imagencopia").attr("src","../inc/img/copiacontrolada.gif");
    }
}
function editardoc(vthis){
	if($(vthis).val() == 'N' ||  $(vthis).val() == ''){
		edita = 'Y';
	}else{
		edita = 'N';
	}
   CB.post({
	  objetos:{
		"_x_u_fluxostatuspessoa_idfluxostatuspessoa": $(vthis).attr('idfluxo')
		,"_x_u_fluxostatuspessoa_editar": edita
	  }
	  ,parcial: true
   });
}

function novapagina(vp){
    CB.post({
        objetos: {
            "_x_i_sgdocpag_idsgdoc":$(":input[name=_1_"+CB.acao+"_sgdoc_idsgdoc]").val()
            ,"_x_i_sgdocpag_pagina":vp
        }
        ,parcial: true
    });
}

function excluirpagina(vidsgdocpag){
    CB.post({
        objetos: {
            "_x_d_sgdocpag_idsgdocpag":vidsgdocpag           
        }
        ,parcial: true
    });
}

function fnovornc(idsgdoc){
	var aux = controllCollapse();
    CB.post({
	objetos: {
	    "_x_i_sgdoc_idsgdoctipo":'rnc'
		,"_x_i_sgdoc_status":'AGUARDANDO'
	    ,"_x_i_sgdoc_titulo":'RNC  <?=$_1_u_sgdoc_idsgdoctipo?> ID: <?=$_1_u_sgdoc_idregistro?> '
	}
	,parcial: true
	,refresh: false
	,posPost: function(){
		$("#novoteste").hide();
	    fvinculadoc(idsgdoc, CB.lastInsertId);
		showCollapse(aux);
	}
	
    }); 
}    

function fvinculadoc(idsgdoc, idrnc){
  
    CB.post({
		objetos: {
			"_x_u_sgdoc_idrnc":idrnc
			,"_x_u_sgdoc_idsgdoc":idsgdoc
		}
		,parcial: true
		,refresh: 'refresh'
    }); 
}

//mapear autocomplete de clientes
$jCli = jQuery.map($jCli, function(o, id) {
    return {"label": o.nome, value:id+"" ,"tipo":o.tipo}
});

//autocomplete de clientes
$("[name*=_sgdoc_idpessoa]").autocomplete({
    source: $jCli
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"<span class='cinzaclaro'> "+item.tipo+"</span></a>").appendTo(ul);
        };
    }	
});
// FIM autocomplete cliente

//Autocomplete de Tipo e Subtipo de doc
if($jTipoSubtDoc != null){
	$jTipoSubtDoc = jQuery.map($jTipoSubtDoc, function(o, id) {
	return {"label": o.tipodocumento, value:o.idsgdoctipodocumento ,"tipo":o.tipo ,"qst":o.qst}
});

$(":input[name=_1_"+CB.acao+"_sgdoc_idsgdoctipodocumento]").autocomplete({
	source: $jTipoSubtDoc
	,delay: 0
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
			return $('<li>').append("<a>"+item.label+"<span class='cinzaclaro'> "+item.tipo+"</span></a>").appendTo(ul);
                        
		};
	}
	,select: function(event, ui){
		CB.post({
			objetos: {
				"_xxxx_u_sgdoc_idsgdoc": $(":input[name=_1_"+CB.acao+"_sgdoc_idsgdoc]").val()
				,"_xxxx_u_sgdoc_idsgdoctipodocumento":ui.item.value
				,"_qst_":ui.item.qst
			}
			,parcial: true
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

			lbItem = "<div class='cinzaclaro fonte08'>"+item.tipodocumento+" - Id:"+item.idsgdoc+"</div>" + item.titulo;
			
			return $('<li>')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
		};
	}
	,select: function(event, ui){
		var aux = controllCollapse();
		CB.post({
			objetos: {
				"_x_i_sgdocvinc_idsgdoc": $(":input[name=_1_"+CB.acao+"_sgdoc_idsgdoc]").val()
				,"_x_i_sgdocvinc_iddocvinc":ui.item.idsgdoc
			}
			,parcial: true
			,posPost: function(){
				showCollapse(aux);
			}
		});
	}
});

function addColaboradorPicker(){
		var setor = '';
		let inputVal = $('#funcvinc2').val();
		let arrIns = ""; 
		let comercial = '';
		cont = 1;
		inputVal.forEach((element,index) => {
			setor = 'N';
			[idbojeto,tipo,objeto] = (element.split(" -- "));
			console.log(idbojeto,tipo,objeto);
			if(tipo == 'sgdepartamento'){
				if(confirm(`Deseja inserir os setores de ${objeto}?`)){
					setor = 'Y';
				}
			}
			arrIns += `${comercial}_${cont}_i_fluxostatuspessoa_idmodulo=${$(":input[name=_1_"+CB.acao+"_sgdoc_idsgdoc]").val()}&_${cont}_i_fluxostatuspessoa_modulo=${CB.modulo}&_${cont}_i_fluxostatuspessoa_idobjeto=${idbojeto}&_${cont}_i_fluxostatuspessoa_tipoobjeto=${tipo}&_${cont}_i_fluxostatuspessoa_setor=${setor}`;
			comercial = '&';
			cont++;
		});
		console.log(arrIns);

		var aux = controllCollapse();
		CB.post({
			objetos: arrIns
			,parcial: true
			,posPost: function(){
				showCollapse(aux);
			}
		});
}

$("#funcvinc").autocomplete({
	source: $jImgrupovinc
	,delay: 0
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {

			lbItem = item.objeto;
			
			return $('<li>')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
		};
	}
	,select: function(event, ui){
		var setor = '';
		//VAlidação para inserir os setores que estão dentro do Departamento.
		if(ui.item.tipo == 'sgdepartamento')
		{
			if(confirm("Deseja inserir os Setores?"))
			{
				setor = '&inserirsetor=Y';
			}
		}

		var aux = controllCollapse();
		CB.post({
			objetos: "_x_i_fluxostatuspessoa_idmodulo="+$(":input[name=_1_"+CB.acao+"_sgdoc_idsgdoc]").val()+"&_x_i_fluxostatuspessoa_modulo=documento&_x_i_fluxostatuspessoa_idobjeto="+ui.item.idobjeto+"&_x_i_fluxostatuspessoa_tipoobjeto="+ui.item.tipo+setor
			,parcial: true
			,posPost: function(){
				showCollapse(aux);
			}
		});
	}
});

$("#funcvinctreinamento").autocomplete({
	source: $jFuncAssinado
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
		var aux = controllCollapse();
		CB.post({
			objetos: {
				"_x_i_fluxostatuspessoa_idmodulo":		$(":input[name=_1_"+CB.acao+"_sgdoc_idsgdoc]").val()
			   ,"_x_i_fluxostatuspessoa_modulo":	'documento'
			   ,"_x_i_fluxostatuspessoa_idobjeto":	ui.item.idpessoa
			   ,"_x_i_fluxostatuspessoa_tipoobjeto":	'pessoa'
			}
			,parcial: true
			,posPost: function(){
				showCollapse(aux);
			}
		});
	}
});
<?
if($_GET['_showerrors']=='Y'){
	echo showControllerErrors(DocumentoController::$controllerErrors);
}
?>

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>