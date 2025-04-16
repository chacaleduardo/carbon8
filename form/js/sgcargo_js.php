<script>
	var funcoesDisponiveisParaVinculo = <?= json_encode($funcoesDisponiveisParaVinculo) ?>;
	var pessoasDisponiveisParaVinculo = <?= json_encode($pessoasDisponiveisParaVinculo) ?>;
	var areasDepsSetoresDisponiveisParaVinculo = <?= json_encode($areasDepsSetoresDisponiveisParaVinculo) ?>;

//Autocomplete de Setores vinculados
$("#sgareavinc").autocomplete({
	source: funcoesDisponiveisParaVinculo
	,delay: 0
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {

			lbItem = item.funcao;
			
			return $('<li>')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
		};
	}
	,select: function(event, ui){
		CB.post({
			objetos: {
				"_x_i_sgcargofuncao_idsgcargo":		$(":input[name=_1_"+CB.acao+"_sgcargo_idsgcargo]").val()
			   ,"_x_i_sgcargofuncao_idsgfuncao":	ui.item.idsgfuncao
			   ,"_x_i_sgcargofuncao_status":	'ATIVO'
			}
			
		
		});
		
	}
});

//Autocomplete de funcionarios vinculados
$("#sgpessoavinc").autocomplete({
	source: pessoasDisponiveisParaVinculo
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
		CB.post({
			objetos: 
				"_x_u_pessoa_idpessoa="+ui.item.idpessoa +"&_x_u_pessoa_idsgcargo="+$(":input[name=_1_"+CB.acao+"_sgcargo_idsgcargo]").val()
				,parcial: true
		});

	}
});

//Autocomplete de Setores vinculados
$("#sgsetorvinc").autocomplete({
	source: areasDepsSetoresDisponiveisParaVinculo
	,delay: 0
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) 
		{
			lbItem = item.name;			
			return $('<li>')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
		};
	}
	,select: function(event, ui){
		CB.post({
			objetos: {

				"_1_i_objetovinculo_idobjeto":+$("[name=_1_u_sgcargo_idsgcargo]").val(),
				"_1_i_objetovinculo_tipoobjeto":'sgcargo',
			   "_1_i_objetovinculo_idobjetovinc": ui.item.id,
			   "_1_i_objetovinculo_tipoobjetovinc": ui.item.tipo,
			   
			}
			,parcial: true
		});
	}
});

CB.prePost = function(){
    $.ajax({
            type: "get",
            url : "cron/bim.php",

            success: function(data){
                   // alert('OK');
            },

            error: function(objxmlreq){
                    alert('Erro:<br>'+objxmlreq.status); 

            }
    })//$.ajax

}

function AlteraStatus(vthis){
	
	//alert($(vthis).attr('idsgareasetor'));
	var id;
	id = $(vthis).attr('idsgcargofuncao');
	status = $(vthis).attr('status');
	//alert(status);
	var novostatus, cor, novacor;
    if (status == 'ATIVO'){
		novostatus = 'INATIVO';
		cor = 'verde hoververde';
		novacor = 'vermelho hoververmelho';
		
		
    }else{
		novostatus = 'ATIVO';	
		cor = 'vermelho hoververmelho';
		novacor = 'verde hoververde';
    }
	//alert("_x_u_sgareasetor_idsgareasetor="+id+"&_x_u_sgareasetor_status="+novostatus);
    CB.post({
				objetos: "_x_u_sgcargofuncao_idsgcargofuncao="+id+"&_x_u_sgcargofuncao_status="+novostatus
				
				,refresh: true
				,msgSalvo: "Status Alterado"
				,posPost: function(){
					$(vthis).removeClass(cor);
					$(vthis).addClass(novacor);
					$(vthis).attr('status', novostatus);
					$(vthis).attr('title', novostatus);
					$('#'+id).addClass('hide');
					//removeClass("vermelho hoververmelho").addClass("verde hoververde");
				}
			});
    
}

function editarclientefat(){  
    $("#sgsetorvinc").removeClass("desabilitado");
	 $("#sgsetorvinc").removeAttr("disabled");
}

function inativaobjeto(inid,inobj){    		
    CB.post({
	objetos: "_x_u_pessoa_idpessoa="+ inid +"&_x_u_pessoa_idsgcargo="
	
    });    
}

	sSeletor = '#diveditor';
	oDescritivo = $("[name=_1_"+CB.acao+"_sgcargo_obs]");

	//Atribuir MCE somente apà³s método loadUrl
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
				
function desvincularSetor(inid,ui){
		//debugger;
		CB.post({
			objetos:{ "_1_d_objetovinculo_idobjeto": + $("[name=_1_u_sgcargo_idsgcargo]").val(),
			"_1_d_objetovinculo_idobjetovinculo":inid

			,parcial:true 
			}
		});    	
	}
	 //}

	//Antes de salvar atualiza o textarea
	CB.prePost = function(){
		if(tinyMCE.get('diveditor')){
			//falha nbsp: oDescritivo.val( tinyMCE.get('diveditor').getContent({format : 'raw'}).toUpperCase());
			oDescritivo.val( tinyMCE.get('diveditor').getContent().toUpperCase());
		}
	}
        
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
