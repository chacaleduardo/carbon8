<?
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/laudo.php");


if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

$urini = $_SERVER['QUERY_STRING'];
//UNIQUE_ID para controlar o select na pagina carrimbocorpo.php
$uniqueid = $_SERVER['UNIQUE_ID'];
//unset($_SESSION["controleass"]);
?>
<html>
<head>
	<title>Conferência de Amostra</title>
	
	<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
	<script src="../inc/js/functions.js"></script>
	<link href="../inc/css/bootstrap/css/bootstrap.css" media="all" rel="stylesheet" type="text/css" />
	<link href="../inc/css/bootstrap/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css" />
	<link href="..\inc\css\fontawesome\font-awesome.min.css" rel="stylesheet">

<style type="text/css">
  
* {
	text-shadow: none !important;
	filter:none !important;
	-ms-filter:none !important;
	font-family: Helvetica, Arial;
	font-size: 10px;
	-webkit-box-sizing: border-box; 
	-moz-box-sizing: border-box;    
	box-sizing: border-box; 
}

	
	body {
		margin: auto;
		margin-top: 0.2cm;
		margin-bottom: 1cm;
		 
		 
	}
	/* .quebrapagina{
		page-break-before:always;
		border: 2px solid #c0c0c0;
		width: 120%;
		margin: 1.5cm -1.5cm;
	} */
	.rot{
		color: gray;
	}
.col-md-3, .col-md-4, .col-md-5{
	font-size:10px;
}


.ordContainer{
	display: flex;
	flex-direction: column;
}
header{
	 background-color: white;
	 top: 0;
	 height: 1cm;
	 line-height: 1cm;
	 display: table;
}
hr{
	margin: 0;
}
.logosup{
	height: inherit;
	line-height: inherit;
	display: table-cell;
}
.logosup img{
	height: 0.5cm;
	vertical-align: middle;
}
.titulodoc{
	height: inherit;
	line-height: inherit;
	display: table-cell;
	text-align: center;
	font-size: 0.5cm;
	font-weight: bold;
}
.row{
	display: table;
	table-layout: fixed;
	width: 100%;
	margin: 0mm 0mm; 
}
  
.rot{
	overflow: hidden;
	font-size: 9px;
}
.quebralinha{
	white-space: normal;
}
[class*='margem0.0']{
	margin: 0 0;
}
.hidden{
	display: none;
} 

.divsup{
	border: 0px; /*borda zerada para nao ocasionar barras de rolagem*/
	  height: 100%;  
	vertical-align: top;
}
.divmeio{
display: table-cell; 
height: 100%; 
width: 30px;
vertical-align:middle;
}


/* footer= linha onde fica os botões assinar retirar assinatura e o alerta*/
#Footer {
	text-align:center;
	/* align:center; */
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
.btassina{
        cursor:pointer;
        border:solid 1px #ccc;
        background:rgb(235,255,235);
        color:black;
        height: 30px;
        /*background:url(../img/btbg.gif) repeat-x left top;*/
}
.btassinafoco{
        cursor:pointer;
        border:solid 1px #ccc;
        color:black;
        background:rgb(0,255,0);
        height: 30px;
}
.btretira{
        cursor:pointer;
        border:solid 1px #ccc;
        background:rgb(255,235,235);
        color:black;
        height: 30px;
        /*background:url(../img/btbg.gif) repeat-x left top;*/
}
.btretirafoco{
        cursor:pointer;
        border:solid 1px #ccc;
        color:black;
        background:rgb(255,0,0);
        height: 30px;
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
/* as classes de estilo da pag carrimbocorpo foram colocadas aqui */
</style>
<script type="text/javascript">
    //ler teclas digitas
$(document).ready(function()
{
	// listens for any navigation keypress activity
	$(document).keypress(function(e)
	{		//alert(e.keyCode);
		if(e.keyCode== 40 || e.keyCode == 38){//Botao para baixo e para cima
			e.preventDefault();		
			focointerpretacao(e.keyCode);
		}
		/*if(e.keyCode==37){///Botao para o lado esquerdo
			e.preventDefault();	
			listaresultado("ant");
		}
		if(e.keyCode==39){//Botao para o lado direito
			e.preventDefault();	
			listaresultado("prox");
		}*/
		if(e.keyCode==13){//Enter 
			
			if(e.target.id != 'btassina' && e.target.id != 'btretira' && e.target.id != 'idfrasedit'){
				e.preventDefault();					
			}
			//linkafrase();
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
		//para alinhar o botao navegador à  direita, eh necessario retirar a propriedade original, para que ele possa ser deslocado de forma absoluta
		$('#gor').css("position","absolute");
		$('#gor').css("left",($(window).width()+$(window).scrollLeft())-30);
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

	var urlx="confereamostracorpo.php?"+$("#urini").val()+"&uniqueid="+$("#uniqueid").val()+"&acao=" + botao;
	//UNIQUE_ID para controlar o select da pagina assinarresutladocorpo.php
	var vuniqueid = $("#uniqueid").val();
	
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

function carrimbo(vid,vacao,vtipo){    
    
		$.ajax({
		type: "post"
		,url : "../ajax/carrimboobj.php"
		,data: {		
		    "idobjeto" : vid,
		    "tipoobjeto" : 'amostra',
		    "acao":vacao,
                    "tipo":vtipo
		}					
		,success: function(data){// retorno 200 do servidor apache
			vdata = data.replace(/(\r\n|\r|\n)/g, "");

			if(vdata=="OK"){
				if(vacao=='inserir'){
					vcor = '#00FF00';
				}else{
					vcor = 'silver';
				}
				document.getElementById("Footer").style.backgroundColor = vcor;
				document.body.style.cursor = "default";
				if(vacao=='inserir'){
					listaresultado('prox');
				}
				
			}else{
				alert(data);
				document.body.style.cursor = "default";
			}
		}
	});
  
}


</script>
</head>	
<body onload="listaresultado('ini')">
<div id="gol" class="gol"><img src="../inc/img/gol.png" class="imggo" id="imggol" onclick="listaresultado('ant')"></div>
<div id="gor" class="gor"><img src="../inc/img/gor.png" class="imggo" id="imggor" onclick="listaresultado('prox')"></div>
<input type="hidden" id="urini" value="<?=$urini?>">
<input type="hidden" id="uniqueid" value="<?=$uniqueid?>">
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

<script>
	function salvarComentario(vwacao, idmodulocom, descricao){
		
	let acao = vwacao
		if (acao != 'u'){
			$.ajax({
			type: "post"
			,url : "../ajax/conferenciamodulocom.php"
			,data: {
				modulocom_idempresa : '1',
				modulocom_idmodulo :  $("#modulocom_idmodulo").val(),
				modulocom_idmodulocom : idmodulocom,
				modulocom_modulo : 'amostraaves',
				modulocom_descricao : $("#modulocom_descricao").val(),
				modulocom_status : 'ATIVO',
				modulocom_acao : acao
			}			
			,success: function(data){

				if(acao=="i"){
					var dados = JSON.parse(data);
					$("#modulocom_descricao").val('');
					$oLinha =$(`
					<tr id="tr_${dados.idmodulocom}">
					<td id="td_${dados.idmodulocom}" style="line-height: 14px; padding: 8px; font-size: 11px;color:#666; width: 90%;">${dados.alteradoem} - ${dados.criadopor}: ${dados.descricao}</td>
					<td><i class="btn fa fa-pencil fa-1x btn-lg pointer ui-droppable" onclick="salvarComentario('u',`+dados.idmodulocom+`,'${dados.descricao}')" title='Editar!' style="padding: 0;color: blue;"></i></td>
					<td><i class="btn fa fa-trash fa-1x  btn-lg pointer ui-droppable" onclick="salvarComentario('d',`+dados.idmodulocom+`,'${dados.descricao}')" title='Excluir!' style="padding: 0;color: red;"></i></td>
					</tr>
					`);
					$("#tblComentarios").prepend($oLinha);
				} else if(acao=="d"){
					$("#tr_"+idmodulocom).hide('fast', function(){
						$("#tr_"+idmodulocom).remove();
					})
				}
			}	
			});
		} else {
			$("#td_"+idmodulocom).text("")
			$("#td_"+idmodulocom).append(`<input onchange="updateComentario(this,`+idmodulocom+`)" type="text" value="`+descricao+`" style="width: 100%">`)
		}
	}


	function updateComentario(vthis,idmodulocom){debugger
		$.ajax({
			type: "post"
			,url : "../ajax/conferenciamodulocom.php"
			,data: {
				modulocom_idempresa : '1',
				modulocom_idmodulocom : idmodulocom,
				modulocom_descricao : vthis.value,
				modulocom_acao : 'u'
			
			},success: function(data){debugger
				var dados = JSON.parse(data);
				$("#tr_"+idmodulocom).remove();
				$oLinha =$(`
				<tr id="tr_${dados.idmodulocom}">
				<td id="td_${dados.idmodulocom}" style="line-height: 14px; padding: 8px; font-size: 11px;color:#666; width: 90%;">${dados.criadoem} - ${dados.criadopor}: ${dados.descricao}</td>
				<td><i class="btn fa fa-pencil fa-1x  btn-lg pointer ui-droppable" onclick="salvarComentario('u',`+dados.idmodulocom+`,${dados.descricao})" title='Editar!' style="padding: 0;color: blue;"></i></td>
				<td><i class="btn fa fa-trash fa-1x  btn-lg pointer ui-droppable" onclick="salvarComentario('d',`+dados.idmodulocom+`,${dados.descricao})" title='Excluir!' style="padding: 0;color: red;"></i></td>
				</tr>
				`);
				$("#tblComentarios").prepend($oLinha);
			}
		});
	}
</script>