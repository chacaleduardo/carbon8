<script src="./../../inc/js/mapaequipamento/color-palette.js" type="text/Javascript"></script>
<script>
// Declarar todas as injeções de PHP no JS
var jTagTipo = <?=$jTagTipo?>;
var jProdserv = <?=$jProdserv?>;
var jTagTipoLocalizacao = <?=$jTagTipoLocalizacao?>;

// Declarar todas as variáveis globais de JS
//Monta um seletor de ícones de acordo com parte do nome dos arquivos CSS informados
var hIcones = "";

// *************************************************** 04/12/2019 GABRIEL TIBURCIO ****************************************************************** //
	
// ********************************* FUNÇÃO PARA MUDAR A COR DE FUNDO E O ÍCONE DE CADA BOTÃO QUE ESTÁ SELECIONADO ********************************** //

// Listar todas as execuções que serão feitas ao carregar o módulo
$(document).ready(function(){
	$("#campos button").each(function(){
		if(this.value){
			$("#"+this.id).css("background-color","#5cb85c");
			$("#"+this.id).css("color","white");
			$("#"+this.id).css("border-color","#4cae4c");
			$("#"+this.id+" i").removeClass("fa fa-eye-slash").addClass("fa fa-eye");
		}
	});
});

// Monta conteúdo do popover dos ícones dos Tipos de Tag
$.each(document.styleSheets, function(i,o){
	var separador = "";
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

// Declarar todas as funções "estilo" JQuery

// Popover de Ícones
$("#seletoricones").webuiPopover({
	title:'Selecionar ícone para o Módulo',
	content: hIcones,
	width:1000,
});
    
$("#busca_tagtipo").autocomplete({
    source: jTagTipo
    ,delay: 0
    ,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
		return $('<li>').append("<a>"+item.nome+"</a>").appendTo(ul);
		};
	}
    ,select: function(event, ui){
        CB.post({
			objetos : {
                "_x_i_objetovinculo_idobjetovinc": ui.item.idtagtipo
                ,"_x_i_objetovinculo_tipoobjetovinc":'tagtipo'
				,"_x_i_objetovinculo_idobjeto":$("[name$=_tagtipo_idtagtipo]").val()
				,"_x_i_objetovinculo_tipoobjeto":'tagtipo'
            }
            ,parcial: true
        });
    }
});
 
$("#busca_tagtipovinc").autocomplete({
	source: jTagTipoLocalizacao
	,delay: 0
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
		return $('<li>').append("<a>"+item.nome+"</a>").appendTo(ul);
		};
	}
    ,select: function(event, ui){
        CB.post({
			objetos : {
                "_x_i_objetovinculo_idobjeto": ui.item.idtagtipo
                ,"_x_i_objetovinculo_tipoobjeto":'tagtipo'
				,"_x_i_objetovinculo_idobjetovinc":$("[name$=_tagtipo_idtagtipo]").val()
				,"_x_i_objetovinculo_tipoobjetovinc":'tagtipo'
            }
            ,parcial: true
        });
    }
});
    
$("#busca_prodserv").autocomplete({
	source: jProdserv
	,delay: 0
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
		return $('<li>').append("<a>"+item.descr+"</a>").appendTo(ul);
		};
	}
	,select: function(event, ui){
        CB.post({
			objetos : {
                "_x_u_prodserv_idprodserv": ui.item.idprodserv
				,"_x_u_prodserv_idtagtipo":$("[name$=_tagtipo_idtagtipo]").val()

            }
            ,parcial: true
        });
    }
});

// Declarar todas as demais funções utilizadas

// ******************************* FUNÇÃO QUE ADICIONA/DELETA O BOTÃO NA TABELA TIPOTAGCAMPOS PARA SER UTILIZADO NA TELA DE TAGS ***************************** //
// 
// OBS: É NECESSÁRIO QUE O CB POST DÊ REFRESH NA PÁGINA PARA QUE AS CORES DO BOTÃO E OS VALORES SEJAM ALTERADOS
function mostraCampo(btn,classificacao,tipo){
	let botao = btn.id;
	let valor = $("#"+botao).val();
	if(!(valor)){
		CB.post({
			objetos: "_x_i_tipotagcampos_idclassificacao="+classificacao+"&_x_i_tipotagcampos_idtagtipo="+tipo+"&_x_i_tipotagcampos_campo="+botao
		});
	}else{
		CB.post({
			objetos: "_x_d_tipotagcampos_idtipotagcampos="+valor
		});
	}
}

function alteraProdserv( vthis, idtagtipo ){
	let idprodserv = $(vthis).val();

	CB.post({
		objetos: "_x_u_prodserv_idprodserv="+idprodserv+"&_x_u_prodserv_idtagtipo="+idtagtipo
	});
}

function desvinculaProdserv( idprodserv ){
    if(confirm("Deseja realmente remover o Produto?")){
		CB.post({
			"objetos":"_x_u_prodserv_idprodserv="+idprodserv+"&_x_u_prodserv_idtagtipo="
			,parcial:true
       	});
    }  
}

function flgrevisado(vthis){
    let atval=$(vthis).attr('atval');
    let idtag=$(vthis).attr('idtag');
    CB.post({
		objetos: "_x_u_tagtipo_idtagtipo="+idtag+"=&_x_u_tagtipo_bioensaio="+atval	
        ,parcial:true
        ,posPost: function(){
			if(atval=='Y'){
				$(vthis).attr('atval','N');
			}else{
				$(vthis).attr('atval','Y');
			}
        }
    })
}

function desvincula(inid){
	CB.post({
		objetos : {
			"_x_d_objetovinculo_idobjetovinculo": inid
		}
		,parcial:true
	});
}

function alteraIcone(inObj){
	$("[name=_1_u_tagtipo_cssicone]").val($(inObj).attr("cssicone"));

	$('#seletoricones').attr('class', `${$(inObj).attr("cssicone")} fa-2x`);
}

<?
if($_GET['_showerrors']=='Y'){
	echo showControllerErrors(TagTipoController::$controllerErrors);
}
?>

loadColorPalette('<?= $_1_u_tagtipo_cor ?>');

$(".color-palette").on('click', 'li', function()
{
	let JQelement = $(this);

	$('.color-palette .active').removeClass('active');

	JQelement.addClass('active');

	$('#_cortipo').attr('value', JQelement.data('color'));
});

// adicionar rodapé em todos os JS de forms p/ ser possível debuggar em produção
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||| 17/02/2021 PEDRO LIMA |||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
</script>
