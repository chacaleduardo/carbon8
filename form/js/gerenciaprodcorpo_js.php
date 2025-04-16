<script>
	//------- Injeção PHP no Jquery -------
	uniqueid = '<?=$uniqueid?>';
    urini = '<?=$urini?>';
	//------- Injeção PHP no Jquery -------

    //------- Funções JS -------
    $(document).ready(function()
    {
        listaresultado('ini');
        
        // listens for any navigation keypress activity
        $(document).keypress(function(e)
        {	
            if(e.keyCode == 13){//Enter                 
                e.preventDefault();	
                listaresultado("prox");
            }	
        });

        //Esconder e mostrar botoes de navegacao
        $('.imggo').each(function() {
            $(this).hover(function() {
                $(this).stop().animate({ opacity: 1.0 }, 200);
            },
            function() {
                $(this).stop().animate({ opacity: 0.35 }, 200);
            });
        });
        
        //mover os botoes de navegacao para que aparecam sempre no meio da tela
        $(window).scroll(function()
        {
            //captura o meio da janela e soma com o scroll
            var vtop = ($(window).height()/2)+$(window).scrollTop();
            //alinha os dois botoes no meio da tela
            $('#gor').stop().animate({top:vtop+"px" },{queue: false, duration: 200});
            $('#gol').stop().animate({top:vtop+"px" },{queue: false, duration: 200});
            //alinhar o botao navegador à  esquerda
            $('#gol').css("left",$(window).scrollLeft());
            $('#gor').css("left",$(window).scrollLeft());//inserido hermes 06-09-2019
            //para alinhar o botao navegador à  direita, eh necessario retirar a propriedade original, para que ele possa ser deslocado de forma absoluta
            //retirado hermes 06-09-2019
            //$('#gor').css("position","absolute");
            //$('#gor').css("left",($(window).width()+$(window).scrollLeft())-30);
        });
    });
    //------- Funções JS -------

    //------- Funções Módulo -------
    function pesquisar(vthis) 
    {
		var idpessoa = $("[name=idpessoa]").attr("cbvalue");
		var idprodserv = $("[name=idprodserv]").attr("cbvalue");
		var status = $("[name=status]").val();
		var idplantel = $("[name=idplantel]").val();
		var validacao = $("[name=validacao]").val();

		CB.modal({
			url: `?_modulo=gerenciaprodcorpo&idprodserv=${idprodserv}&idpessoa=${idpessoa}&status=${status}&validacao=${validacao}&idplantel=${idplantel}&_modo=form`,
			header: "Gerência de Produto"
		});
	}

    //função que monta a tela de assinatura monta o corpo e as interpretaçoes
    function listaresultado(botao)
    {
        //simula clique no botao de navegacao conforme o tipo da acao
        if(botao == "prox"){
            $("#imggor").css("opacity","1.0");
        }
        if(botao == "ant"){
            $("#imggol").css("opacity","1.0");
        }

        var urlx = window.location.origin+`/form/gerenciaprodhtml.php?${urini}&uniqueid=${uniqueid}&acao=${botao}`;
        //UNIQUE_ID para controlar o select da pagina assinarresutladocorpo.php
        
        window.status = urlx;
        //ajax para montar o corpo do assinarresultado
        $.ajax({
            type: "get",
            url : urlx,
            data: {},
            success: function(data){			
                $('#conteudo').html(data+'<br /><br /><br /><br />');					
            },
            error: function(objxmlreq){
                alert('Erro:<br>'+objxmlreq.status); 
            }
        })//$.ajax		

        //Esconde os botoes de navegacao
        $("#imggor").stop().animate({ opacity: 0.35 }, 50);
        $("#imggol").stop().animate({ opacity: 0.35 }, 50);			
    }

    function alterast(vthis, inidlote)
    {
        var situacao = $(vthis).attr('situacao');
        var status = $(vthis).attr('status');
        var nova_st;
        var ant_bt;
        var bt;
    
        if(situacao == 'APROVADO'){
            nova_st = 'REPROVADO';
            ant_bt = "btn-success";
            bt = "btn-danger";

        }else if(situacao == 'REPROVADO' && status != 'APROVADO'){
            nova_st = 'PENDENTE';
            ant_bt = "btn-danger";
            bt = "btn-warning";
        }else if(situacao == 'REPROVADO' && status == 'APROVADO'){
            nova_st = 'APROVADO';
            ant_bt = "btn-danger";
            bt = "btn-success";
        }else{
            nova_st = 'REPROVADO';
            ant_bt = "btn-warning";
            bt = "btn-danger";
        }
    
        CB.post({
            objetos: `_x_u_lote_idlote=${inidlote}&_x_u_lote_situacao=${nova_st}`,   
            parcial: true,
            refresh: true,
            posPost: function(data, textStatus, jqXHR){
                $(vthis).attr('situacao', nova_st);
                $(vthis).removeClass(ant_bt).addClass( bt );
                listaresultado('atual');
            }
        });
    }   

    function inativaprodserv(inidprodservforn, vthis)
    {
        var status = $(vthis).attr('vstatus');
        if(status == 'Y'){
            var nstatus = 'N';
            var classr = 'vermelho';
            var classad = 'verde';
            var classr2 = 'hoververmelho';
            var classad2 = 'hoververde';
            var texto = ' ATIVO';
        }else{
            var classr = 'verde';
            var classad = 'vermelho';
            var nstatus = 'Y';
            var classr2 = 'hoververde';
            var classad2 = 'hoververmelho';
            var texto = ' INATIVO';
        }
        CB.post({
            objetos: `_x_u_prodservforn_idprodservforn=${inidprodservforn}&_x_u_prodservforn_valido=${status}`,
            parcial: true,	
            refresh: false,
            posPost: function(data, textStatus, jqXHR){
                $(vthis).attr('vstatus', nstatus);
                $(vthis).removeClass(classr).addClass(classad);
                $(vthis).removeClass(classr2).addClass(classad2);
                $(vthis).html(texto);
            }
        }); 
    }

    function inativapool(inidpool)
    {
        CB.post({
            objetos: `_x_u_lotepool_idlotepool=${inidpool}&_x_u_lotepool_status=INATIVO`,
            parcial: true,
            refresh: false,
            posPost: function(data, textStatus, jqXHR){            
                listaresultado('atual');
            }
        });	        
    }

    function gerapool(inidlote)
    {
        CB.post({
            objetos: "_x_i_pool_status=ATIVO",
            refresh: false,
            parcial: true,
            posPost: function(data, textStatus, jqXHR){
                geralotepool(jqXHR.getResponseHeader("x-cb-pkid"),inidlote);	
            }
        });	        
    }
    
    function geralotepool(inidpool, inidlote)
    {
        CB.post({
            objetos: `_x_i_lotepool_idpool=${inidpool}&_x_i_lotepool_idlote=${inidlote}`,
            parcial: true,
            refresh: false,
            posPost: function(data, textStatus, jqXHR){			
                listaresultado('atual');
            }
        });	
    }

    function criapool(inidlote, inidlote2)
    {
        CB.post({
            objetos: "_x_i_pool_status=ATIVO",
            refresh: false,
            parcial: true,
            posPost: function(data, textStatus, jqXHR){
                geralotepoollote(jqXHR.getResponseHeader("x-cb-pkid"),inidlote,inidlote2);	
            }
        });	       
    }

    function geralotepoollote(inidpool, inidlote, inidlote2)
    {
        CB.post({
            objetos: `_x_i_lotepool_idpool=${inidpool}&_x_i_lotepool_idlote=${inidlote}&_y_i_lotepool_idpool=${inidpool}&_y_i_lotepool_idlote=${inidlote2}`,
            parcial: true,
            refresh: false,
            posPost: function(data, textStatus, jqXHR){			
                listaresultado('atual');
            }
        });	
    }

    function validaproduto(idprodservforn, usuario, vdata)
    {
        CB.post({
            objetos: `_x_u_prodservforn_idprodservforn=${idprodservforn}&_x_u_prodservforn_validadopor=${usuario}&_x_u_prodservforn_validadoem=${vdata}`,
            parcial: true,
            refresh: false,
            posPost: function(data, textStatus, jqXHR){			
                listaresultado('atual');
            }
        });	
    }

    function retiravalidaproduto(idprodservforn)
    {
            CB.post({
            objetos: `_x_u_prodservforn_idprodservforn=${idprodservforn}&_x_u_prodservforn_validadopor=' '&_x_u_prodservforn_validadoem=' '`,
            parcial: true,
            refresh: false,
            posPost: function(data, textStatus, jqXHR){			
                listaresultado('atual');
            }
        });	
    }

    function geravalidaproduto(idprodserv, idpessoa, idprodservformula, usuario, vdata)
    {
        CB.post({
            objetos: `_x_i_prodservforn_idprodserv=${idprodserv}&_x_i_prodservforn_idpessoa=${idpessoa}&_x_i_prodservforn_idprodservformula=${idprodservformula}&_x_i_prodservforn_validadopor=${usuario}&_x_i_prodservforn_validadoem=${vdata}`,
            parcial:true,	
            refresh:false,
            posPost: function(data, textStatus, jqXHR){			
                listaresultado('atual');
            }
        });	
    }

    function geraprodservforn(vthis, acao, idprodserv, idpessoa, idprodservformula, idprodservforn){
        $(vthis).val();
        CB.post({
            objetos: `_x_${acao}_prodservforn_idprodserv=${idprodserv}&_x_${acao}_prodservforn_idpessoa=${idpessoa}&_x_${acao}_prodservforn_idprodservformula=${idprodservformula}&_x_${acao}_prodservforn_qtd=${$(vthis).val()}&_x_${acao}_prodservforn_idprodservforn=${idprodservforn}`,
            parcial:true,
            refresh:false,
            posPost: function(data, textStatus, jqXHR){			
                listaresultado('atual');
            }
        });	
    }
    //------- Funções Módulo -------

    //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape_3
</script>