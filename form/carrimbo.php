<?
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/laudo.php");

$urini = $_SERVER['QUERY_STRING'];
//UNIQUE_ID para controlar o select na pagina carrimbocorpo.php
$uniqueid = $_SERVER['UNIQUE_ID'];
//unset($_SESSION["controleass"]);
?>
<html>
<head>
	<title>Carrimbo</title>
	
	<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
	<script src="../inc/js/functions.js"></script>
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

html{
	margin: 0px;
	padding: 0px;
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
}


	body {
		margin: auto;
		margin-top: 0.2cm;
		margin-bottom: 1cm;
		<!-- padding: 3mm 10mm; -->
		width: 21cm;
	}
	.quebrapagina{
		page-break-before:always;
		border: 2px solid #c0c0c0;
		width: 120%;
		margin: 1.5cm -1.5cm;
	}
	.rot{
		color: gray;
	}



.ordContainer{
	display: flex;
	flex-direction: column;
}
.ord1{order: 1;}
.ord2{order: 2;}
.ord3{order: 3;}
.ord4{order: 4;}
.ord5{order: 5;}
.ord6{order: 6;}
.ord7{order: 7;}
.ord8{order: 8;}
.ord9{order: 9;}
.ord10{order: 10;}
.ord11{order: 11;}
.ord12{order: 12;}
.ord13{order: 13;}
.ord14{order: 14;}
.ord15{order: 15;}
.ord16{order: 16;}
.ord17{order: 17;}
.ord18{order: 18;}
.ord19{order: 19;}
.ord20{order: 20;}
.ord21{order: 21;}
.ord22{order: 22;}
.ord23{order: 23;}
.ord24{order: 24;}
.ord25{order: 25;}
.ord26{order: 26;}
.ord27{order: 27;}
.ord28{order: 28;}
.ord29{order: 29;}
.ord30{order: 30;}
.ord31{order: 31;}
.ord32{order: 32;}
.ord33{order: 33;}
.ord34{order: 34;}
.ord35{order: 35;}
.ord36{order: 36;}
.ord37{order: 37;}
.ord38{order: 38;}
.ord39{order: 39;}
.ord40{order: 40;}
.ord41{order: 41;}
.ord42{order: 42;}
.ord43{order: 43;}
.ord44{order: 44;}
.ord45{order: 45;}
.ord46{order: 46;}
.ord47{order: 47;}
.ord48{order: 48;}
.ord49{order: 49;}
.ord50{order: 50;}
.ord51{order: 51;}
.ord52{order: 52;}
.ord53{order: 53;}
.ord54{order: 54;}
.ord55{order: 55;}
.ord56{order: 56;}
.ord57{order: 57;}
.ord58{order: 58;}
.ord59{order: 59;}
.ord60{order: 60;}
.ord61{order: 61;}
.ord62{order: 62;}
.ord63{order: 63;}
.ord64{order: 64;}
.ord65{order: 65;}
.ord66{order: 66;}
.ord67{order: 67;}
.ord68{order: 68;}
.ord69{order: 69;}
.ord70{order: 70;}
.ord71{order: 71;}
.ord72{order: 72;}
.ord73{order: 73;}
.ord74{order: 74;}
.ord75{order: 75;}
.ord76{order: 76;}
.ord77{order: 77;}
.ord78{order: 78;}
.ord79{order: 79;}
.ord80{order: 80;}
.ord81{order: 81;}
.ord82{order: 82;}
.ord83{order: 83;}
.ord84{order: 84;}
.ord85{order: 85;}
.ord86{order: 86;}
.ord87{order: 87;}
.ord88{order: 88;}
.ord89{order: 89;}
.ord90{order: 90;}
.ord91{order: 91;}
.ord92{order: 92;}
.ord93{order: 93;}
.ord94{order: 94;}
.ord95{order: 95;}
.ord96{order: 96;}
.ord97{order: 97;}
.ord98{order: 98;}
.ord99{order: 99;}
.ord100{order: 100;}


[class*='5']{width: 5%;}
[class*='10']{width: 9%;}
[class*='15']{width: 15%;}
[class*='20']{width: 20%;}
[class*='25']{width: 25%;}
[class*='30']{width: 30%;}
[class*='35']{width: 35%;}
[class*='40']{width: 39.99%;}
[class*='45']{width: 45%;}
[class*='50']{width: 50%;}
[class*='55']{width: 55%;}
[class*='60']{width: 60%;}
[class*='65']{width: 65%;}
[class*='70']{width: 70%;}
[class*='75']{width: 75%;}
[class*='80']{width: 80%;}
[class*='85']{width: 85%;}
[class*='90']{width: 90%;}
[class*='95']{width: 95%;}
[class*='100']{width: 100%;}
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
	width: 99%;
	margin: 0mm 0mm;
}
.linhainferior{
	border-bottom: 1px dashed gray;
}
.col{
	display: table-cell;
	white-space: nowrap;
	padding: 1.5mm 1mm;
}
.row.grid .col{
	border: 1px solid silver;
	
}
.row.grid .col:first-child{
	border-top: 1px solid silver;
}
.col.grupo {}
.col.grupo .titulogrupo{
	margin: 0px;
	border-bottom: 1px solid silver;
	color: #777777;
	font-weight: bold;
	Xmargin-bottom: 2mm;
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
.sublinhado{
	border-bottom: 1px dashed gray;
}
.fonte8{
	font-size: 8px;
}
.resultadodescritivo{
	margin: 0 0;        
}
.resultadodescritivo p{
	margin: 0 0;	
}
p{
    font-size: 9px;
}
span{
     font-size: 9px !important;
}



.divsup{
	border: 0px; /*borda zerada para nao ocasionar barras de rolagem*/
	<!-- height: 100%; -->
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

	var urlx="carrimbocorpo.php?<?=$urini?>&uniqueid=<?=$uniqueid?>&acao=" + botao;
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