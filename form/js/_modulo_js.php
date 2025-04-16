<??>
<script>
var jsonTabCarbonApp = <?=jsonTabelasCarbonApp()?>;
var jRepDisponiveis = <?=_moduloController::jsonRepDisponiveis($_1_u__modulo_modulo)?>;
var $jLp = <?=_moduloController::jsonLpsDisponiveis( $_1_u__modulo_modulo, $_SESSION['SESSAO']['IDPESSOA'] );?>;
var $jEtiqueta= <?=_moduloController::jsonEtiquetasDisponiveis($_1_u__modulo_idmodulo);?>;
var $jImp= <?=_moduloController::jsonImpressorasDisponiveis( $_1_u__modulo_idmodulo );?>;
var styleSheetList = document.styleSheets;
var hIcones="";
var separador="";
var $oARelatorio = $("#associarRelatorio");
var jsonAc = jQuery.map(jRepDisponiveis, function(o, id) {
	return {"label": o.rep, value:o.idrep ,"cssicone":o.cssicone}
});

const historicoCampoTimeout = <?= json_encode($listaHistoricoFab) ?>;

$(document).ready(function(){
  $("#mais").click(function(){
    $("#selectlps").toggle();
  });
});

$('.selectpicker').selectpicker('render');

$("[group]").on("change",function(){
	let group = $(this).attr("group");
	if($(this).attr("campo")  == "idempresa" || $(this).attr("campo")  == "idunidade"){
		let idempresa = $("#"+group+"_jclauswhere_idempresa").val() != undefined? "\"idempresa\":\""+$("#"+group+"_jclauswhere_idempresa").val().join()+"\"":"\"idempresa\":\"\"";
		let idunidade = $("#"+group+"_jclauswhere_idunidade").val() != undefined? "\"idunidade\":\""+$("#"+group+"_jclauswhere_idunidade").val().join()+"\"":"";
		let virgula = (idunidade.length > 0 && idempresa.length > 0) ? "," : ""
		$("[name='_share"+group+"_u_share_jclauswhere']").html(`{${idempresa}${virgula} ${idunidade}}`)
	}
	if ($(this).attr("campo") == "ovalue") {
		$("[name='_share"+group+"_u_share_ovalue']").val($(this).val())
	}
});


//Monta um seletor de à­cones de acordo com parte do nome dos arquivos CSS informados
$.each(styleSheetList, function(i,o){
	var prefClasse;
	//Procura por Css por parte do nome do arquivo
	if(o.href && /laudofonts|fontawesome/.test(o.href)){
		//Adicionar o prefixo padrao para utilizacao da fonte css
		if(/laudofonts/.test(o.href)){
			prefClasse = "laudoicon";
		}else if(/fontawesome/.test(o.href)){
			prefClasse = "fa";
		}
		hIcones += separador;
		//Loop em todas as classes css
		oRules=o.rules||o.cssRules;
		$.each(oRules, function(ir,or){
			if(or.type=="1"){
				//Extrai a string referente ao seletor Css do icone
				if(/::/.test(or.selectorText)){
					var strIco = or.selectorText.match(/.*?(?=::|$)/)[0].replace(/^\./, '');
					hIcones += `<i class="${prefClasse + " " + strIco} fa-2x hoververmelho" style="margin:3px;" cssicone="${prefClasse + " " +strIco}" title="${prefClasse + " " +strIco}"" onclick="alteraIcone(this)"></i>`;
                }
            }
		});

		separador = "<hr>";
	}
});

if($("[name=_1_u__modulo_idmodulo]").val()){
	$("#svganexo").dropzone({
		idObjeto: $("[name=_1_u__modulo_idmodulo]").val()
		,tipoObjeto: '_modulo'
		,tipoArquivo: 'SVG'
		,onComplete: function (file){
			if(!file) return;

			let $ov = $("#svganexo");
			let $ob = $ov.find(".dz-complete");

			let caminho = $ob.attr('title') == 'undefined' 
				? JSON.parse(file.xhr.response)[0].nome 
				: $ob.attr('title');

			$ov.addClass('carregado').siblings().hide();
			$ov.prepend(`
				<img style="width:40px;margin-right:15px;" src="./upload/${caminho}">
			`);
		}
	});
}

$("#seletoricones").webuiPopover({
	title:'Selecionar ícone para o Módulo',
	content: hIcones,
	width:1000,
});

//Autocomplete de Tabelas
$(":input[name=_1_"+CB.acao+"__modulo_tab]").autocomplete({
	source: jsonTabCarbonApp
	,delay: 0
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
			vitem = "<span class='cinzaclaro'>"+item.db+".</span>" + item.tab;
			return $('<li>')
				.append('<a>' + vitem + '</a>')
				.appendTo(ul);
		};
	},
	select: function(event, ui){
		this.value = ui.item.tab;
	}/*,
	create: function( event, ui ) {
		mostraDetalhesCliente();
	}*/
});

//Autocomplete de lps
$("#selectlps").autocomplete({
        source: $jLp
        ,delay: 0
        ,create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
            return $('<li>').append("<a>"+item.sigla+"</a>").appendTo(ul);
            };
        }
        ,select: function(event, ui){
        CB.post({
            objetos : {
                "_x_i__lpmodulo_idlp":ui.item.idlp
                ,"_x_i__lpmodulo_modulo": $("input[name='_1_u__modulo_modulo']").val()
                ,"_x_i__lpmodulo_permissao":'w'
            }
            ,parcial: true
        });
    }
});

// autocomplete etiquetas
$("#busca_etiquetas").autocomplete({
        source: $jEtiqueta
        ,delay: 0
        ,create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
            return $('<li>').append("<a>"+item.rotuloetiqueta+"</a>").appendTo(ul);
            };
        }
        ,select: function(event, ui){
        CB.post({
			objetos : {
                "_x_i_etiquetaobjeto_idobjeto":$("[name$=idmodulo]").val()
				,"_x_i_etiquetaobjeto_tipoobjeto":'modulo'
				,"_x_i_etiquetaobjeto_idetiqueta":ui.item.idetiqueta
            }
            ,parcial: true
        });
    }
});

// autocomplete impressoras
$("#busca_impressora").autocomplete({
        source: $jImp
        ,delay: 0
        ,create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
            return $('<li>').append("<a>"+item.nome+" - <span class='cinzaclaro fonte08'>"+item.fabricante+"</span></a>").appendTo(ul);
            };
        }
        ,select: function(event, ui){
        CB.post({
			objetos : {
                "_x_i_objetovinculo_idobjetovinc": ui.item.idtag
                ,"_x_i_objetovinculo_tipoobjetovinc":'tag'
				,"_x_i_objetovinculo_idobjeto":$("[name$=__modulo_idmodulo]").val()
				,"_x_i_objetovinculo_tipoobjeto":'modulo'
            }
            ,parcial: true
        });
    }
});

$oARelatorio.autocomplete({
	source: jsonAc
	,delay: 0
	,select: function(){
		CB.post({
			objetos:"_x_i__modulorep_modulo="+$("input[name='_1_u__modulo_modulo']").val()+"&_x_i__modulorep_idrep="+$(this).cbval()+"&_x_i__modulorep_ord=999",
			parcial:true
		});
	}
	,noMatch: function(objAc){
		console.log("Executei callback");
		//Cria um novo report
		CB.post({
			objetos:"_x_i__rep_rep="+objAc.term
			,refresh: false
			,msgSalvo: "Report criado"
			,posPost: function(data, textStatus, jqXHR){
				//Associa o report criado com o modulo atual
				CB.post({
					objetos:"_x_i__modulorep_modulo="+$("input[name='_1_u__modulo_modulo']").val()+"&_x_i__modulorep_idrep="+CB.lastInsertId+"&_x_i__modulorep_ord=999",
					parcial:true
				});
			}
		});
	}
})
//Uma propriedade com o json é adicionada ao objeto para tornar possà­vel a consulta posterior
.data("nucleos",jRepDisponiveis);

function motivo(vthis){
	$('#_motivo_').css('display','block').attr('name','_1mo_i_fluxostatushistmotivo_motivo');
	$(vthis).css('display','none');
	$('#_motivo_').parent().append("<input type='hidden' name='_1mo_i_fluxostatushistmotivo_modulo' value='"+$('[name="_1_u__modulo_modulo"]').val()+"'>")
}
function deletamotivo(inid){
	CB.post({
		objetos :{
			"_motivo_d_fluxostatushistmotivo_idfluxostatushistmotivo" : inid
		}
		,parcial : true
	})
}

function desvinculaetiqueta(inid){
	CB.post({
		objetos : {
			"_x_d_etiquetaobjeto_idetiquetaobjeto": inid
		}
		,parcial:true
	});
}
function criaShare(inid){
	CB.post({
		objetos : {
			"_x_i_share_sharemetodo": 'modulofiltrospesquisa',
			"_x_i_share_otipo": 'cb::usr',
			"_x_i_share_okey": 'IDEMPRESA',
			"_x_i_share_modulo": $("[name='_1_u__modulo_modulo']").val(),
			"_x_i_share_acesso": "Y",
			"_x_i_share_ptipoobj": "table",
			"_x_i_share_pobj": $("[name='_1_u__modulo_tab']").val(),
		}
		,parcial:true
	});
}
function desvinculaimp(inid){
	CB.post({
		objetos : {
			"_x_d_objetovinculo_idobjetovinculo": inid,
			"_a_u__modulo_descricao":$("[name='_1_u__modulo_descricao']").val(),
			"_a_u__modulo_idmodulo":$("[name='_1_u__modulo_idmodulo']").val()
		}
		,parcial:true
	});
}

function inserirunidade(vthis){
 CB.post({
            objetos: "_x_i_unidadeobjeto_idobjeto="+$("[name=_1_u__modulo_modulo]").val()+"&_x_i_unidadeobjeto_idunidade="+$(vthis).val()+"&_x_i_unidadeobjeto_tipoobjeto=modulo&_a_u__modulo_descricao="+$("[name='_1_u__modulo_descricao']").val()+"&_a_u__modulo_idmodulo="+$("[name='_1_u__modulo_idmodulo']").val()
    });
}
function inserirempresa(vthis){
 CB.post({
            objetos: "_100_i_objempresa_idobjeto="+$("[name=_1_u__modulo_idmodulo]").val()+"&_100_i_objempresa_empresa="+$(vthis).val()+"&_100_i_objempresa_objeto=modulo&_a_u__modulo_descricao="+$("[name='_1_u__modulo_descricao']").val()+"&_a_u__modulo_idmodulo="+$("[name='_1_u__modulo_idmodulo']").val()
			, parcial: true
    });
}
function togglechavefts(inColuna,inRadio){
	if(inColuna && inRadio.checked){
		CB.post({
			objetos: "_x_u__modulo_idmodulo="+$("[name=_1_u__modulo_idmodulo]").val()+"&_x_u__modulo_chavefts="+inColuna
			,refresh: false
			,msgSalvo: "Chave FTS alterada!"
		});
	}
}

function toggleOrdenavel(oCheck){
	sOrdenavel=oCheck.checked?"Y":"N";
	CB.post({
		objetos: "_x_u__modulo_modulo="+$("[name=_1_u__modulo_modulo]").val()+"&_x_u__modulo_ordenavel="+sOrdenavel
		,refresh: false
		,parcial: true
		,msgSalvo: "Ordenação alterada!"
	});
}

function toggleDisponivelApp(oCheck){
	sDisponivel=oCheck.checked ? "Y" :"N";
	CB.post({
		objetos: "_x_u__modulo_idmodulo="+$("[name=_1_u__modulo_idmodulo]").val()+"&_x_u__modulo_disponivelapp="+sDisponivel
		,parcial: true
	});
}

function toggle(inId,inCol,inChk){

	var vYN = (inChk.checked) ? "Y" : "N";
	if(inId. length == 0) {
		col = $(inChk).attr('col');
		var strPost = `_ajax_i__modulofiltros_modulo=${$("[name=_1_u__modulo_modulo]").val()}&_ajax_i__modulofiltros_col=${col}&_ajax_i__modulofiltros_${inCol}=${vYN}`;
	} else {
		var strPost = `_ajax_u__modulofiltros_idmodulofiltros=${inId}&_ajax_u__modulofiltros_${inCol}=${vYN}`;
	}

	CB.post({
		objetos: strPost,
		refresh: false
	});
}

function toggleValor(inId,inCol,invalor){


	var strPost = "_ajax_u__modulofiltros_idmodulofiltros="+inId
				+ "&_ajax_u__modulofiltros_"+inCol+"="+invalor.value;

	CB.post({
		objetos: strPost
		,refresh: false
	});
}

function alteraIcone(inObj){
	let cssIcone = $(inObj).attr("cssicone");

	$("[name=_1_u__modulo_cssicone]").val(cssIcone);

	let rClass = $("#seletoricones").attr('class').split(' ').filter(cls => !['fa','fa-2x','fade'].includes(cls));

	$("#seletoricones").addClass(cssIcone.split(' ')[1]).removeClass(rClass[0]);
}

function resetarFTS(){
	if(confirm("Deseja realmente re-iniciar os dados de Full Text search para a tabela em questão?")){
		vurl=location.origin+location.pathname+CB.urlDestino+"?"+CB.locationSearch+"&_atualizaftstabela=Y";

		var background = $.ajax({
			type: 'GET',
			url: vurl,
			backgroundMessage: "O Full Text Search será re-iniciado para a Tabela.<br>Aguarde notificação."
		});

		//Após 3 segundos, abortar a requisição
		setTimeout(function(){
			background.abort();
		}, 3000);
	}
}

function novoRelatorio(){
	CB.post({'objetos':"_x_i_resultado_idresultado="});
}

//habilitaModuloHostname(this,'cliente_filtrarresultados','sislaudo','122')
function habilitaModuloHostname(inCheck,inModulo,inHostname,inIdftsmodulo){
	if(inCheck.checked){
		CB.post({
			objetos: {"_x_i__ftsmodulo_modulo":inModulo
						,"_x_i__ftsmodulo_hostname":inHostname
			}
			,parcial: true
		});
	}else{
		CB.post({
			objetos: {"_x_d__ftsmodulo_idftsmodulo":inIdftsmodulo
			}
			,parcial: true
		});
	}
}
function permissao(id,modulo,permissao, idempresa, idempresapessoa,idlpmodulos){
	if(idempresa != idempresapessoa){
		if(confirm("Esta LP não pertence a sua empresa de cadastro!\n Deseja prosseguir?")){
			CB.post({
				objetos:{
					"_22_u__lpmodulo_idlp":id,"_22_u__lpmodulo_modulo":modulo,"_22_u__lpmodulo_permissao":permissao,"_22_u__lpmodulo_idlpmodulo":idlpmodulos
				},parcial:true
			});
		}
	}else{
		CB.post({
				objetos:{
					"_22_u__lpmodulo_idlp":id,"_22_u__lpmodulo_modulo":modulo,"_22_u__lpmodulo_permissao":permissao,"_22_u__lpmodulo_idlpmodulo":idlpmodulos
				},parcial:true
			});
	}
}
function romoverlp(id,modulo,idlpmodulos){
	CB.post({
		objetos:{
			"_gs_d__lpmodulo_idlp":id,"_gs_d__lpmodulo_modulo":modulo,"_gs_d__lpmodulo_permissao":undefined,"_gs_d__lpmodulo_idlpmodulo":idlpmodulos
		},parcial:true
	});
}

function deleteEmpresa(idobjempresa)
{
	let idunidadeobjeto = $("#unidade_"+idobjempresa).val() || "";
	CB.post({
		objetos:{
			'_ajax_d_objempresa_idobjempresa':idobjempresa,
			'idunidadeobjeto':idunidadeobjeto,
			"_a_u__modulo_descricao":$("[name='_1_u__modulo_descricao']").val(),
			"_a_u__modulo_idmodulo":$("[name='_1_u__modulo_idmodulo']").val()
			
		},
		parcial: true
	})
}

<?
if($_GET['_showerrors']=='Y'){
	echo showControllerErrors(_moduloController::$controllerErrors);
}
?>
$(document).on('change', '[name="_1_u__modulo_timeout"], [name="_h1_i_modulohistorico_valor"]', function() {
    let value = $(this).val().replace(/\D/g, ''); // Remove caracteres não numéricos

    // Adiciona zeros à esquerda para garantir que a string tenha pelo menos 4 caracteres
    value = value.padStart(4, '0');

    // Formata a string no formato mm:ss
    value = value.slice(0, 2) + ':' + value.slice(2, 4);

    $(this).val(value);
});
$(document).on('keyup', '[name="_1_u__modulo_timeout"], [name="_h1_i_modulohistorico_valor"]', function() {
    let value = $(this).val().replace(/\D/g, ''); // Remove caracteres não numéricos
    if (value.length >= 3) {
        value = value.slice(0, 2) + ':' + value.slice(2, 4); // Adiciona os dois pontos
    }
    $(this).val(value);
});

	function alteravalor(campo, valor, tabela, inid, texto) {
		htmlTrModelo = "";
		htmlTrModelo = `<div id="alt${campo}${inid}">
            <table class="table table-hover">
                <tr>
                    <td>${texto}</td>
                    <td>
                        <input name="_h1_i_${tabela}_idobjeto" value="${inid}" type="hidden">
                        <input name="_h1_i_${tabela}_campo" value="${campo}" type="hidden">
                        <input name="_h1_i_${tabela}_tipoobjeto" value="_modulo" type="hidden">
                        <input name="_h1_i_${tabela}_valor_old" value="${valor}" type="hidden">
                        <input name="_h1_i_${tabela}_valor" value="${valor}" class="size10" type="text" placeholder="00:00" autocomplete="off">
                    </td>
                </tr>
                <tr>
                    <td>Justificativa:</td>
                    <td>
                        <input id="justificativa" name="_h1_i_${tabela}_justificativa" vnulo class="size50">
                    </td>
                </tr>
            </table>
        </div>`;

		var objfrm = $(htmlTrModelo);
			objfrm.find("#ndroptipo option[value='" + valor + "']").attr("selected", "selected");
			objfrm.find("[name='_h1_i_modulohistorico_justificativa']").attr("vnulo");

		strCabecalho = "</strong>Alterar " + texto + " <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='salvaHist()' style='float: right; margin-top: 14px;'><i class='fa fa-circle'></i>Salvar</button></strong>";

		CB.modal({
			titulo: strCabecalho,
			corpo: "<table>" + objfrm.html() + "</table>",
			classe: 'sessenta',
			aoAbrir: function(vthis) {
				$(`[name="_h1_i_${tabela}_valor"]`).val(valor);
			}
		});
	}

	function salvaHist() {
		if ($(`#justificativa`).val().length < 5) return alertAtencao(`Justificativa deve ter pelo menos 5 caracteres`);
		
		CB.post({
			objetos: {
				'_h1_i_modulohistorico_idobjeto': $('[name="_h1_i_modulohistorico_idobjeto"]').val(),
				'_h1_i_modulohistorico_campo': $('[name="_h1_i_modulohistorico_campo"]').val(),
				'_h1_i_modulohistorico_tipoobjeto': $('[name="_h1_i_modulohistorico_tipoobjeto"]').val(),
				'_h1_i_modulohistorico_valor_old': $('[name="_h1_i_modulohistorico_valor_old"]').val(),
				'_h1_i_modulohistorico_valor': $('[name="_h1_i_modulohistorico_valor"]').val(),
				'_h1_i_modulohistorico_justificativa': $('[name="_h1_i_modulohistorico_justificativa"]').val(),
			},
			parcial: true
		});
	}

	$('.timeout-hist').on('click', () => {
		const corpo = `<table class="table table-hover w-100">
						<thead>
							<th>De</th>
							<th>Para</th>
							<th>Justificativa</th>
							<th>Por</th>
							<th>Em</th>
						</thead>
						<tbody>
							${historicoCampoTimeout.map(item => `
								<tr>
									<td>${item.valor_old ?? '-'}</td>
									<td>${item.valor ?? '-'}</td>
									<td>${item.justificativa}</td>
									<td>${item.nomecurto}</td>
									<td>${dmahms(item.criadoem)}</td>
								</tr>`).join('') ?? `
								<tr>
									<td colspan='5'>Sem histórico.</td>
								</tr>`
							}
						</tbody>
					</table>`;
		
		CB.modal({
			titulo: 'Histórico de Alterações',
			corpo,
		});
	})

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
