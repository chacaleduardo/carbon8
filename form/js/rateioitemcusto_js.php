<script>
function vizualizacao(vthis){
    debugger;
  // Verifica se o valor selecionado é 'PRODUTO'
  if ($(vthis).val() == 'PRODUTO') {
        // Mostra a tabela de produtos e oculta a tabela de serviços
        $('#filtrosprodutos').show();
        $('#filtroservicos').hide();
    } else {
        // Caso contrário, oculta a tabela de produtos e mostra a tabela de serviços
        $('#filtrosprodutos').hide();
        $('#filtroservicos').show();
    }

    $("#resultadolotes").html('');
    $("#resultado").html('');
    $('#cbalterar').addClass('hidden');
    $('#cbalterar2').removeClass('hidden');

    $("#tiporateio option").filter(function() {
     return $(this).val() == 'SELECIONE';  // Verifica o valor, não o texto
    }).prop("selected", true);

}

function pesquisarproduto(vthis){
    debugger;
    $(vthis).html('<span class="fa fa-spinner fa-pulse"></span>');
    var dataini = $("[name=dataini]").val();
    var datafim = $("[name=datafim]").val();
   
    par='NOK';
    var vurl='ajax/htmllotecusto.php?tipo=PRODUTO&dataini='+dataini+'&datafim='+datafim;
    if( $("[name=idprodserv_pr]").val()!=null){
        vurl= vurl+"&idprodserv="+$("[name=idprodserv_pr]").val();
        par='OK';
    }

    if( $("[name=idtipoprodserv_pr]").val()!=null){
        vurl= vurl+"&idtipoprodserv="+$("[name=idtipoprodserv_pr]").val();
        par='OK';
    }
    if( $("[name=idplantel_pr]").val()!=null){
        vurl= vurl+"&idplantel="+$("[name=idplantel_pr]").val();
        par='OK';
    }
   
    if( $("[name=statuslote]").val()!=null){
        vurl= vurl+"&status="+$("[name=statuslote]").val();
        par='OK';
    }
    if( $("[name=especificacaoprod]").val()!=null){
        vurl= vurl+"&especificacaoprod="+$("[name=especificacaoprod]").val();
        par='OK';
    }
 
    if(dataini==null || datafim==null ){
        alert('Não foi possível verificar o intervalo selecionado.');        
        $(vthis).html('<span class="fa fa-search"></span>');
    }else{
        $.ajax({
            type: "get",
            url: vurl,
            success: function(data) {
                $(vthis).html('<span class="fa fa-search"></span>');
                $("#resultado").html(data);
            },

            error: function(objxmlreq) {
                $(vthis).html('<span class="fa fa-search"></span>');
                alert('Erro:<br>' + objxmlreq.status);

            }
        }) //$.ajax
    }
    $("#resultadolotes").html('');
    $('#cbalterar').addClass('hidden');
    $('#cbalterar2').removeClass('hidden');

    $("#tipo option").filter(function() {
     return $(this).val() == 'SELECIONE';  // Verifica o valor, não o texto
    }).prop("selected", true);
     
}
function pesquisarservico(vthis){
    debugger;
    $(vthis).html('<span class="fa fa-spinner fa-pulse"></span>');
    var dataini = $("[name=dataini]").val();
    var datafim = $("[name=datafim]").val();

    par='NOK';
    var vurl='ajax/htmllotecusto.php?tipo=SERVICO&dataini='+dataini+'&datafim='+datafim;
    if( $("[name=idprodserv_sr]").val()!=null){
        vurl= vurl+"&idprodserv="+$("[name=idprodserv_sr]").val();
        par='OK';
    }

    if( $("[name=idtipoprodserv_sr]").val()!=null){
        vurl= vurl+"&idtipoprodserv="+$("[name=idtipoprodserv_sr]").val();
        par='OK';
    }
    if( $("[name=idplantel_sr]").val()!=null){
        vurl= vurl+"&idplantel="+$("[name=idplantel_sr]").val();
        par='OK';
    }
 
    if(dataini==null || datafim==null ){
        alert('Não foi possível verificar o intervalo selecionado.');        
        $(vthis).html('<span class="fa fa-search"></span>');
    }else{
        $.ajax({
            type: "get",
            url: vurl,
            success: function(data) {
                $(vthis).html('<span class="fa fa-search"></span>');
                $("#resultado").html(data);
            },

            error: function(objxmlreq) {
                $(vthis).html('<span class="fa fa-search"></span>');
                alert('Erro:<br>' + objxmlreq.status);

            }
        }) //$.ajax
    }
    $("#resultadolotes").html('');
    $('#cbalterar').addClass('hidden');
    $('#cbalterar2').removeClass('hidden');

    $("#tiporateio option").filter(function() {
     return $(this).val() == 'SELECIONE';  // Verifica o valor, não o texto
    }).prop("selected", true);
}


function gerarrateio(vthis){
  debugger;

  if($(vthis).val()=='SELECIONE'){

    $("#resultadolotes").html('');
    $('#cbalterar').addClass('hidden');
    $('#cbalterar2').removeClass('hidden');
  }else{
      // Seleciona todos os checkboxes com a classe 'changeacao' que estão marcados
        var vqtd = $('#resultado input.changeacao:checked').length;
        
        if (vqtd > 0) {
            // Cria um array com os valores de cada checkbox selecionado
            var valores = $('#resultado input.changeacao:checked').map(function() {
            return $(this).val();
            }).get(); // get() transforma o objeto jQuery em um array de valores

            // Junta os valores em uma string, separada por vírgulas
            var idloteString = valores.join(',');

            var valorrateio=$('#valorrateiototal').val();
            
            $.ajax({
                    type: "post",
                    url : "ajax/htmlcalculacusto.php",
                    data: { 
                        tipo : $(vthis).val(),
                        valor: valorrateio,
                        inidlote: idloteString
                    },
                    success: function(data) {             
                        $("#resultadolotes").html(data);
                        $('#cbalterar').removeClass('hidden');
                        $('#cbalterar2').addClass('hidden');
                    },

                    error: function(objxmlreq) {               
                        alert('Erro:<br>' + objxmlreq.status);
                    }
                }) //$.ajax

        } else {
            alert('É necessário selecionar os itens que deseja alterar o valor');
        }

  }

}

function custeartodos(porempresa) 
	{
		var vqtd = $('#resultadolotes').find("input").length;
		if (vqtd > 0) 
		{
			var inputdest = $('#resultadolotes').find('input.rateioitem').serialize();
			var stidrateioitemdest = $('#stidrateioitemdest').val();
        
            var strvalores="&dataini="+$("[name=dataini]").val()+"&datafim="+$("[name=datafim]").val()+"&tiporateio="+$("#tipo").val()+"&valorrateio="+$('#valorrateiototal').val()+"&valorrateioun="+$('#valorrateioun').val()+"&idunidade="+$('#idunidade').val();

			var strinputfim = inputdest+"&stidrateioitemdest="+stidrateioitemdest+strvalores;

		
			
			if(confirm("Deseja atribuir o valores aos lotes?")){
			
				CB.post({
					objetos: strinputfim,
					parcial: true,
                    posPost: function(data, textStatus, jqXHR){
                        debugger;
                        if(jqXHR.getResponseHeader('IDRATEIOCUSTO'))
                        {
                            var rateiocusto = jqXHR.getResponseHeader('IDRATEIOCUSTO')
                            var _location = location.href;
                            window.history.pushState(null, window.document.title, _location+'&idrateiocusto='+rateiocusto);
                            _location = null;
                            
                            window.location.reload();
                            
                        }
                    }
				});
			} 
			
		} else {
			alert('É necessário selecionar os itens que deseja alterar');
		}
	}

function limparcustos(){
    if(confirm("Deseja realmente limpar os custos do rateio dos lotes?")){

        var idrateiocusto = $('#idrateiocusto').val();

        CB.post({
                objetos: "_x1_u_rateiocusto_idrateiocusto="+idrateiocusto+"&_x1_u_rateiocusto_status=INATIVO",
                parcial: true,
                posPost: function(data, textStatus, jqXHR){
                    debugger;
                 // Obter a URL atual
                var _location = new URL(window.location.href);

                // Remover o parâmetro 'idrateiocusto'
                _location.searchParams.delete('idrateiocusto');

                // Atualizar a URL e recarregar a página
                window.location.href = _location.toString();
                   
                }
            });
    }
}

   

    $('.selectpicker').select();
    $('.selectpicker').selectpicker('render');
</script>