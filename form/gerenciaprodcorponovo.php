<?
require_once("../inc/php/validaacesso.php");

if($_POST){
    include_once("../inc/php/cbpost.php");
}

$urini = $_SERVER['QUERY_STRING'];
//UNIQUE_ID para controlar o select na pagina carrimbocorpo.php
$uniqueid = $_SERVER['UNIQUE_ID'];
//unset($_SESSION["controleass"]);
?>

	

<style type="text/css">
.divsup{
	border: 0px; /*borda zerada para nao ocasionar barras de rolagem*/
	/* height: 100%;*/
	vertical-align: top;
}
.divmeio{
display: table-cell; 
height: 100%; 
width: 30px;
vertical-align:middle;
}


/* footer= linha onde fica os botàµes assinar retirar assinatura e o alerta*/
#Footer {
	text-align:center;
	align:center;
	color: black;
	border: 1px solid silver;
	position:fixed;
	/**adjust location**/
	right: 0px;
	bottom: 0px;
	padding: 0 10px 0 10px;
	width: 100%;
	/* Netscape 4, IE 4.x-5.0/Win and other lesser browsers will use this */
	_position: absolute;
}
.clsFootera {
	background: #00FF00;
}	
.clsFooterf {
	background: silver;
}

/* Classes para os botes de direita e esquerda */
.gor{ 
	position: relative;
	top: 280px;
	float: right;
	margin: 0px 0px 0px 0px;
	width:30px;
	z-index: 10; 
}
.gol{ 
	position: relative;
	top: 280px;
	float: left;
	margin: 0px 0px 0px 0px;
	width:30px;
	z-index: 10; 
}
.imggo{
	opacity:0.35;
	filter:alpha(opacity=35);
	border:0;
	cursor:pointer;
}	

.insumosEspeciais a i.fa{
		display: inline-block !important;
	}	

	.itemestoque{
	Xwidth:100%;
	width:auto;
	display: inline-block;
	text-align: right;
	margin: 3px;
}
.itemestoque.especial{
	display:none;
}
.itemestoque.especial.especialvisivel{
	display:inline-block !important;
}

div.container4 {
    width: 3em;
    height: 3em;
    position: relative;
}
div.container4 p {
    margin: 0;
    position: absolute;
    top: 50%;
    left: 50%;
    margin-right: -50%;
    transform: translate(-50%, -50%);
}

.alinhamento{
	padding-left: 20px !important;
	padding-right: 20px !important;
}

#alinhamento{
	padding-left: 20px !important;
	padding-right: 20px !important;
}

/* as classes de estilo da pag carrimbocorpo foram colocadas aqui */
</style>

<!-- ESSE MODAL EXISTE APENAS QUANDO A SEMENTE ESTÁ AUTORIZADA, RECEBE AÇÃO DE CLIQUE E O RETORNO É A LISTA DE SOLFAB QUE ESTÁ PRESENTE
	/AS INFORMAÇÕES NAS LINHAS SÃO ADICIONADAS NO EACH UTILIZANDO A RESPOSTA DO AJAX -- ALBT 15/07/2021 - EVENTO: https://sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=471202 -->
<div id="historico" style="display: none;"> 
	<div class="row">
		 <table style="margin-left: 5px;">
			 <tr> 
				<th class="alinhamento">Sol. Fab.</th>
			 	<th  class="alinhamento">Lote</th>
				 <th  class="alinhamento">Status</th>
			</tr>
			<tr>
				<td class="idsolfab" id="alinhamento" ></td>
				<td class="lote" id="alinhamento"></td>
				<td class="status" id="alinhamento"></td>
			</tr>
		 </table>
	</div>
 </div> <!-- FINAL DA DIV COM ID=HISTORICO  -->

<script type="text/javascript">
    //ler teclas digitas
$(document).ready(function()
{
	listaresultado('ini');
	
		// listens for any navigation keypress activity
	$(document).keypress(function(e)
	{	
		if(e.keyCode==13){//Enter 
			
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
//função que monta a tela de assinatura monta o corpo e as interpretaçoes
function  listaresultado(botao){
	//simula clique no botao de navegacao conforme o tipo da acao
	if(botao=="prox"){
		$("#imggor").css("opacity","1.0");
	}
	if(botao=="ant"){
		$("#imggol").css("opacity","1.0");
	}
// adicionar /carbon8/ antes do form para funcionar no ambiente local 
	if(getUrlParameter("_idempresa") != ''){
		var urlempresa = "&_idempresa="+getUrlParameter("_idempresa");
	}else{
		var urlempresa = "";
	}
	var urlx=window.location.origin+"/form/gerenciaprodhtmlnovo.php?<?=$urini?>&uniqueid=<?=$uniqueid?>&acao=" + botao + urlempresa;
	//UNIQUE_ID para controlar o select da pagina assinarresutladocorpo.php
	var vuniqueid = "<?=$uniqueid?>";
	
	window.status=urlx;
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


function alterast(vthis,inidlote){

    // alert(inidrhevento);
    //var situacao = $(vthis).attr('situacao');
	var status = $(vthis).attr('status');
    var nova_st;
    var ant_bt;
    var bt;
   
	if(status=='ABERTO'){
        nova_st='AUTORIZADA';
		htmlst='AUTORIZADA';
		ant_bt="btn-secondary";
        bt="btn-primary";
		atualizastatus(vthis,inidlote,nova_st,bt,ant_bt,htmlst);
    }else if(status=='AUTORIZADA'){
		// INICIO DO AJAX, VERIFICA SE A SEMENTE CLICADA ESTÁ EM ALGUMA SOLFAB(SOLFABITEM) RELACIONADA COM STATUS ACEITÁVEIS (EXCETO CANCELADO E REPROVADO)
			$.ajax({
			type: "get",
			url : `ajax/verificasemente.php?status=${status}&idlote=${inidlote}`,
			success: function(data){ //se a funcao tiver sucesso, executa todo script abaixo
				var resp =  JSON.parse(data);  //converte a resposta em json
				if(resp.length > 0){  //verifica o tamanho
					//FUNCAO DE HISTORICO
					if($('#histsem').length == 0){   //verificacao se ja existe historico, em caso de duplo clique em cima da semente, mostra apenas uma vez
					$.each(resp, function(k, v){   //repeticao na resposta, para primeiras informacoes
						$.each(v, function(c, j){ //repeticao no v(segundo array de informacoes)
							$('.idsolfab').append(`<a title="Solfab" href="?_modulo=solfab&amp;_acao=u&amp;idsolfab=`+c+`" target="_blank">`+c+`</a></br>`); //link para solfab, um link para cada uma relacionada
							$('.status').append(j+`</br>`);  //status da solfab em questão 
							// APPEND's acima responsaveis por montar a tabela depois de tudo pronto
						});
						$('.lote').append(`<a title="Semente" href="?_acao=u&amp;_modulo=semente&amp;idlote=${inidlote}" target="_blank"> ${inidlote}</a></br>`);
					});
						$('<td> <a class="fa fa-search azul pointer hoverazul" id="histsem"   title="Histórico" onClick="historico();" ></a></td>').insertAfter(".anterior");
					}
					alert('A semente está em uso, confira as informações na lupa ');
					/*	COMENTÁRIO REFERENTE A TROCA DE STATUS DE AUTORIZADA PARA NÃO AUTORIZADA, QUE AGORA (15/07/2021) ESTÁ BLOQUEADO 
						EVENTO : https://sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=471202 - ALBT - 15/07/2021
			
						nova_st='AUTORIZADA';
						htmlst='AUTORIZADA';
						ant_bt="btn-danger";
						bt="btn-primary";*/
				}else{ // SE A SEMENTE NAO ESTIVER RELACIONADA COM NENHUMA SOLFAB ATUALIZA O STATUS PARA NAO AUTORIZADA. 
					nova_st='NAO AUTORIZADA';
					htmlst='NÃO AUTORIZADA';
					ant_bt="btn-primary";
					bt="btn-danger";
					atualizastatus(vthis,inidlote,nova_st,bt,ant_bt,htmlst);
				}
			},
			error: function(objxmlreq){
				alert('Erro:<br>'+objxmlreq.status);  
			}
		});  //FINAL DO AJAX
	}else if(status=='NAO AUTORIZADA'){
		nova_st='AUTORIZADA';
		htmlst='AUTORIZADA';
		ant_bt="btn-danger";
        bt="btn-primary";
		atualizastatus(vthis,inidlote,nova_st,bt,ant_bt,htmlst);
	}else if(status=='APROVADO'){
		nova_st='CANCELADO';
		htmlst='CANCELADO';
		ant_bt="btn-success";
        bt="btn-danger";
		atualizastatus(vthis,inidlote,nova_st,bt,ant_bt,htmlst);
	}else if(status=='CANCELADO'){
		nova_st='APROVADO';
		htmlst='APROVADO';
		ant_bt="btn-danger";
        bt="btn-success";
		atualizastatus(vthis,inidlote,nova_st,bt,ant_bt,htmlst);
	}

} 
function atualizastatus(vthis,inidlote,nova_st,bt,ant_bt,htmlst){   //responsavel por fazer o cbpost -- adicionado para facilitar o controle de status 
																	// evento https://sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=471202 - ALBT - 15/07/2021
    //LTM - 07-04-2021 - Retorna o idFluxoStatus Selecionado
   	var idfluxostatus = getIdFluxoStatus('semente', nova_st);

	//LTM - 07-04-2021 - Altera o Historico da Lote
	var idFluxoStatusHist = getIdFluxoStatusHist('semente', inidlote);
  
CB.post({
	objetos: "_x_u_lote_idlote="+inidlote+"&_x_u_lote_status="+nova_st+"&_x_u_lote_idfluxostatus="+idfluxostatus        
	,parcial:true
	,refresh:false
	,posPost: function(data, textStatus, jqXHR){
		$(vthis).attr('status',nova_st);
		$('#span_'+inidlote).html(htmlst);
		$(vthis).removeClass( ant_bt ).addClass( bt );
		//listaresultado('atual');
		CB.post({
			urlArquivo: 'ajax/_fluxo.php?fluxo=fluxo',
			refresh: false,
			objetos: {
				"_modulo": 'semente',
				"_primary": 'idlote',
				"_idobjeto": inidlote,
				"idfluxo": '',
				"idfluxostatushist": idFluxoStatusHist,
				"idstatusf": idfluxostatus,
				"statustipo": nova_st,
				"idfluxostatus": idfluxostatus,
				"idfluxostatuspessoa": '',
				"ocultar": '',
				"prioridade": '20',
				"tipobotao": '',
				"acao": "alterarstatus"                    
			}
		});
	}
});
}
function historico(){
	CB.modal({
            titulo: "</strong>Histórico da Semente</strong>",
            corpo: $("#historico").html(),
            classe: 'trinta'
    });
}   

function inativaprodserv(inidprodservforn,vthis){

    var status = $(vthis).attr('vstatus');
    if(status=='Y'){
        var nstatus='N';
        var classr = 'vermelho';
        var classad='verde';
        var classr2='hoververmelho';
        var classad2='hoververde';
        var texto=' ATIVO';
    }else{
        var classr = 'verde';
        var classad='vermelho';
        var nstatus='Y';
        var classr2='hoververde';
        var classad2='hoververmelho';
        var texto=' INATIVO';
    }
     CB.post({
        objetos: "_x_u_prodservforn_idprodservforn="+inidprodservforn+"&_x_u_prodservforn_valido="+status
        ,parcial: true	
        ,refresh:false
        ,posPost: function(data, textStatus, jqXHR){
            $(vthis).attr('vstatus',nstatus);
            $(vthis).removeClass( classr ).addClass( classad );
            $(vthis).removeClass( classr2 ).addClass( classad2 );
            $(vthis).html(texto);
            //listaresultado('atual');
        }
     }); 
}

function inativapool(inidpool, inidlote){
	$.ajax({
			type: "get",
			url : `ajax/verificasemente.php?status=${status}&idlote=${inidlote}`,
			success: function(data){ //se a funcao tiver sucesso, executa todo script abaixo
				var resp =  JSON.parse(data);  //converte a resposta em json
				if(resp.length > 0){  //verifica o tamanho
					if($('#histsem').length == 0){   //verificacao se ja existe historico, em caso de duplo clique em cima da semente, mostra apenas uma vez
					$.each(resp, function(k, v){   //repeticao na resposta, para primeiras informacoes
						$.each(v, function(c, j){ //repeticao no v(segundo array de informacoes)
							$('.idsolfab').append(`<a title="Solfab" href="?_modulo=solfab&amp;_acao=u&amp;idsolfab=`+c+`" target="_blank">`+c+`</a></br>`); //link para solfab, um link para cada uma relacionada
							$('.status').append(j+`</br>`);  //status da solfab em questão 
							// APPEND's acima responsaveis por montar a tabela depois de tudo pronto
						});
						$('.lote').append(`<a title="Semente" href="?_acao=u&amp;_modulo=semente&amp;idlote=${inidlote}" target="_blank"> ${inidlote}</a></br>`);
					});
						$('<td> <a class="fa fa-search azul pointer hoverazul" id="histsem"   title="Histórico" onClick="historico();" ></a></td>').insertAfter(".anterior");
					}
					alert('A semente está em uso, confira as informações na lupa');
					/*	COMENTÁRIO REFERENTE A TROCA DE STATUS DE AUTORIZADA PARA NÃO AUTORIZADA, QUE AGORA (19/07/2021) ESTÁ BLOQUEADO 
						EVENTO : https://sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=471202 - ALBT - 19/07/2021   */
				}else{ // SE A SEMENTE NAO ESTIVER RELACIONADA COM NENHUMA SOLFAB ATUALIZA O STATUS PARA NAO AUTORIZADA. 
					CB.post({
						objetos: "_x_u_lotepool_idlotepool="+inidpool+"&_x_u_lotepool_status=INATIVO"
						,parcial:true
						,refresh:false
						,posPost: function(data, textStatus, jqXHR){
							listaresultado('atual');
						}
						});
				}
			},
			error: function(objxmlreq){
				alert('Erro:<br>'+objxmlreq.status);  
			}
		});  //FINAL DO AJAX
	
}


function gerapool(inidlote){
    CB.post({
        objetos: "_x_i_pool_status=ATIVO"
        ,refresh:false
		,parcial:true
		,posPost: function(data, textStatus, jqXHR){
		  geralotepool(jqXHR.getResponseHeader("x-cb-pkid"),inidlote);	
		}
    });	
	
}
function geralotepool(inidpool,inidlote){
	CB.post({
        objetos: "_x_i_lotepool_idpool="+inidpool+"&_x_i_lotepool_idlote="+inidlote
   		,parcial:true
		,refresh:false
		,posPost: function(data, textStatus, jqXHR){			
			listaresultado('atual');
		}
    });	
}

function geralotepoold(inidpool,inidlote,idlotepool){
	CB.post({
        objetos: "_x_i_lotepool_idpool="+inidpool+"&_x_i_lotepool_idlote="+inidlote+"&_xs_u_lotepool_idlotepool="+idlotepool+"&_xs_u_lotepool_status=INATIVO"
   		,parcial:true
		,refresh:false
		,posPost: function(data, textStatus, jqXHR){			
			listaresultado('atual');
		}
    });	
}

function criapool(inidlote,inidlote,ord){
    CB.post({
        objetos: "_x_i_pool_status=ATIVO&_x_i_pool_ord="+ord
        ,refresh:false
		,parcial:true
		,posPost: function(data, textStatus, jqXHR){
			geralotepool(jqXHR.getResponseHeader("x-cb-pkid"),inidlote);	
		}
    });	
	
}
function criapoold(inidlote,inidlote,ord,idlotepool){
    CB.post({
        objetos: "_x_i_pool_status=ATIVO&_x_i_pool_ord="+ord
        ,refresh:false
		,parcial:true
		,posPost: function(data, textStatus, jqXHR){
			geralotepoold(jqXHR.getResponseHeader("x-cb-pkid"),inidlote,idlotepool);	
		}
    });	
	
}

function geralotepoollote(inidpool,inidlote,inidlote2){
	CB.post({
        objetos: "_x_i_lotepool_idpool="+inidpool+"&_x_i_lotepool_idlote="+inidlote+"&_y_i_lotepool_idpool="+inidpool+"&_y_i_lotepool_idlote="+inidlote2
   		,parcial:true
		,refresh:false
		,posPost: function(data, textStatus, jqXHR){			
			listaresultado('atual');
		}
    });	
}
/*
//Permitir ordenar/arrastar os TR de insumos
$(".tblotes tbody").sortable({
	update: function(event, objUi){
		ordenaInsumos();
	}
});

//Permitir dropar o insumo
$(".soltavel").droppable({
	drop: function( event, ui ) {
		$this=$(this);//TR
		var idlote=ui.draggable.attr("idloteins");
		var idpool=$this.attr("idpool");
		var idlote2=$this.attr("idloteins");
		debugger;
		if(idpool==""){
			criapool(idlote,idlote2);
		}else{
			geralotepool(idpool,idlote);
		}
	}
});
*/
function validaproduto(idprodservforn,usuario,vdata){
	CB.post({
        objetos: "_x_u_prodservforn_idprodservforn="+idprodservforn+"&_x_u_prodservforn_validadopor="+usuario+"&_x_u_prodservforn_validadoem="+vdata
   		,parcial:true	
		,refresh:false
		,posPost: function(data, textStatus, jqXHR){			
			listaresultado('atual');
		}
    });	
}

function retiravalidaproduto(idprodservforn){
		CB.post({
        objetos: "_x_u_prodservforn_idprodservforn="+idprodservforn+"&_x_u_prodservforn_validadopor=' '&_x_u_prodservforn_validadoem=' '"
   		,parcial:true	
		,refresh:false
		,posPost: function(data, textStatus, jqXHR){			
			listaresultado('atual');
		}
    });	
}

function geravalidaproduto(idprodserv,idpessoa,idprodservformula,usuario,vdata){
	CB.post({
        objetos: "_x_i_prodservforn_idprodserv="+idprodserv+"&_x_i_prodservforn_idpessoa="+idpessoa+"&_x_i_prodservforn_idprodservformula="+idprodservformula+"&_x_i_prodservforn_validadopor="+usuario+"&_x_i_prodservforn_validadoem="+vdata
   		,parcial:true	
		,refresh:false
		,posPost: function(data, textStatus, jqXHR){			
			listaresultado('atual');
		}
    });	
}

function geraprodservforn(vthis,acao,idprodserv,idpessoa,idprodservformula,idprodservforn){
	$(vthis).val();
	CB.post({
        objetos: "_x_"+acao+"_prodservforn_idprodserv="+idprodserv+"&_x_"+acao+"_prodservforn_idpessoa="+idpessoa+"&_x_"+acao+"_prodservforn_idprodservformula="+idprodservformula+"&_x_"+acao+"_prodservforn_qtd="+$(vthis).val()+"&_x_"+acao+"_prodservforn_idprodservforn="+idprodservforn
   		,parcial:true
		,refresh:false
		,posPost: function(data, textStatus, jqXHR){			
			listaresultado('atual');
		}
    });	
}


//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>;
</script>
</head>	
<body onload="listaresultado('ini')">
<div id="gol" class="gol"><img src="../inc/img/gol.png" class="imggo" id="imggol" onclick="listaresultado('ant')"></div>
<div id="gor" class="gor"><img src="../inc/img/gor.png" class="imggo" id="imggor" onclick="listaresultado('prox')"></div>

<div class="divsup">
	<div id="conteudo" style="display: table-cell; height: 100%; width: 100%;">
	<!-- conteudo aqui aparece a assinaresultado corpo -->
	</div>
</div>
</body>
</html>
<?
$retdel = delgraf();
?>