<?
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/laudo.php");

$urini = $_SERVER['QUERY_STRING'];
//UNIQUE_ID para controlar o select na pagina assinarresultadocorpo.php
$uniqueid = $_SERVER['UNIQUE_ID'];
//unset($_SESSION["controleass"]);
?>
<html>
<head>
	<title>Assinar Resultados</title>
	<link href="../inc/css/emissaoresultadopdf.css" rel="stylesheet" type="text/css" />
	<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
	<script src="../inc/js/functions.js"></script>
	<script src="../inc/js/tinymce/tinymce.min.js"></script>
	<script src="../inc/js/cookie/js.cookie.js"></script>
<style type="text/css">
.resdesc table{
	width: 100% !important;
}
html{
	margin: 0px;
	padding: 0px;
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	zoom:175%;
}
body { 
	margin: 0px;
	padding: 0px;
	height: 100%;	

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
/* botao seleção de interpreção sem foco*/
.btintnormal {
		border: 1px solid silver;
		height: 40px;
		width:  180px;	
		font-size: 12px;
		margin: 5px; 
		padding: 2px;
		background-color:silver;
		display: inline-table;
		cursor: pointer;
	} 
/* botao seleção de interpreção com foco*/	
.btintfoco {
		border: 1px solid silver;
		height: 40px;
		width:  180px;	
		font-size: 12px;
		font-weight: bold;
		margin: 5px;
		padding: 2px;
		background-color:#f8f282;
		display: inline-table;
		cursor: pointer;
	} 
/* footer= linha onde fica os botões assinar retirar assinatura e o alerta*/
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
.bteditar{
        cursor:pointer;
        border:solid 1px #ccc;
        background:rgb(250,254,235);
        color:black;
        height: 30px;
        /*background:url(../img/btbg.gif) repeat-x left top;*/
}
.bteditarfoco{
        background:rgb(255,246,0);
		cursor:pointer;
        border:solid 1px #ccc;
        color:black;
        height: 30px;
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
	position: fixed;
	top: 140px;
	float: right;
	margin: 0px 0px 0px 0px;
	width:30px;
	z-index: 10; 
	right:0px;
}
.gol{ 
	position: fixed;
	top: 140px;
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
/* as classes de estilo da pag assinaresultadocorpo foram colocadas aqui */
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
		if(e.keyCode==37){///Botao para o lado esquerdo
			e.preventDefault();	
			listaresultado("ant");
		}
		if(e.keyCode==39){//Botao para o lado direito
			e.preventDefault();	
			listaresultado("prox");
		}
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
//	$(window).scroll(function()
//	{
//		//captura o meio da janela e soma com o scroll
//		var vtop = ($(window).height()/2)+$(window).scrollTop();
//		//alinha os dois botoes no meio da tela
//  		$('#gor').stop().animate({top:vtop+"px" },{queue: false, duration: 200});
//		$('#gol').stop().animate({top:vtop+"px" },{queue: false, duration: 200});
//		//alinhar o botao navegador à  esquerda
//		$('#gol').css("left",$('#conteudo').scrollLeft());
//		//para alinhar o botao navegador à  direita, eh necessario retirar a propriedade original, para que ele possa ser deslocado de forma absoluta
//		$('#gor').css("position","absolute");
//		$('#gor').css("left",($('#conteudo').width()+$('#conteudo').scrollLeft())+30);
//	});

});
function selecionabt(sel){//seleciona a interpretacao com o mouse	
	var btsel= sel.id;
	var objinter =  document.getElementById("interpretacao").childNodes;
	var idobjfoco;
	var idatual;
	var idproximo;
	var idanterior;
		
	for(i=0; i < objinter.length;i++){
		if(objinter[i].className == 'btintfoco'){
			//objinter[i].className = 'btintnormal';
			idobjfoco = objinter[i].id;//botao que esta com o foco			
		}else{
			objinter[i].className = 'btintnormal';
			//alert(objinter[i].id);  
		}
	}
	if(idobjfoco != btsel){//se o botão onde esta o foco for diferente do botão clicado
		document.getElementById(idobjfoco).className = 'btintnormal';
		document.getElementById(btsel).className = 'btintfoco';
	}
	var vfrase = $("#" + btsel).html();//pega a frase que esta no foco
	
  strfrase="<textarea  rows='5'	cols='40' id='idfrasedit'>"+ vfrase+ "</textarea>"; //concatena a frase com textarea
	
		      $("#fraseedicao").html(strfrase);//mostra a frase no campo id fraseedicao	
}
//alterna o foco entre os botàµes
function focointerpretacao(inv){	
	var objinter =  document.getElementById("interpretacao").childNodes;
	var idobjfoco;
	var idatual;
	var idproximo;
	var idanterior;
	
	for(i=0; i < objinter.length;i++){
		if(objinter[i].className == 'btintfoco'){
			//objinter[i].className = 'btintnormal';
			idobjfoco = objinter[i].id;//botao que esta com o foco			
		}else{
			objinter[i].className = 'btintnormal';
			//alert(objinter[i].id);  
		}
	}

	idatual=Number(idobjfoco.split("-")[1]);//pega o id atual o numero apà³s o traço		
	idproximo = "fraseint-" + (idatual+1);	
	idanterior = "fraseint-" + (idatual-1);

	if(inv == 40 && document.getElementById(idproximo).className == "btintnormal"){//se for para seta para baixo o foco será o proximo
		document.getElementById(idobjfoco).className = 'btintnormal';
		document.getElementById(idproximo).className = 'btintfoco';
	}
	if(inv == 38 && document.getElementById(idanterior).className == "btintnormal"){//se for para seta para cima o foco será o anterior

		document.getElementById(idobjfoco).className = 'btintnormal';
		document.getElementById(idanterior).className = 'btintfoco';
	}
}
//função para levar o texto da frase para dentro do corpo do resultado
function linkafrase(){
	var objinter =  document.getElementById("interpretacao").childNodes;
	var idobjfoco;
	//roda para pegar o ID que esta com o foco
	for(i=0; i < objinter.length;i++){
		if(objinter[i].className == 'btintfoco'){
			idobjfoco = objinter[i].id;
		}
	}

	idatual=Number(idobjfoco.split("-")[1]);
	var vfrase = $("#" + idobjfoco).html();//pega a frase que esta no foco
	
  strfrase="<textarea  rows='3'	cols='60' id='idfrasedit' tabindex=1 vnulo>"+ vfrase+ "</textarea>"; //concatena a frase com textarea
	
		      $("#fraseedicao").html(strfrase);//mostra a frase no campo id fraseedicao
		      $('#idfrasedit').focus();	
}
/*
 * Funçàµes para Assinar Retirar assinatura e Alerta
 */
 function assina(vinacao,inidresultado,inoficial,inalerta,intipoteste){	
	debugger;
	var vidfrasedit = $("#idfrasedit").val();	 
	// se existir o campo de frase este campo não pode ser vazio	 
	//if($("#idfrasedit").length>0 && $("#idfrasedit").val()=="" && (intipoteste!="DESCRITIVO")){
	//	alertErro("à necessário preencher a interpretação para assinar o resultado!");
	//	return false;
	//}
	if(inoficial === 'Y' && inalerta=== 'Y'){
		alert("Resultado oficial com alerta, será enviado email para Secretaria!");
		vemailsec="A";
	}else{
		vemailsec="N";
	}
	if (getUrlParameter("_idempresa") != '') {
		link = "../form/inclusaoresultado.php?_modulo=<?=$_GET['_modulo']?>&_idempresa="+getUrlParameter("_idempresa");
	} else {
		link = "../form/inclusaoresultado.php?_modulo=<?=$_GET['_modulo']?>";
	}
	$.ajax({	
		type: "post"
		,url : link
		,data: {
			"frase" : vidfrasedit,
			"_1_u_resultado_idresultado" : inidresultado,
			"acao" : vinacao,
			"emailsec": vemailsec,
			"modulo": '<?=$_GET['_modulo']?>'
		}					
		,success: function(data){// retorno 200 do servidor apache
			vdata = data.replace(/(\r\n|\r|\n|\s)/g, "");

			if(vdata=="OK"){
				if(vinacao=='assinar'){
					vcor = '#00FF00';
				}else{
					vcor = 'silver';
				}
				document.getElementById("Footer").style.backgroundColor = vcor;
				document.body.style.cursor = "default";
				if(vinacao=='assinar'){
					listaresultado('prox');
				}	
			}else{
				alert(data);
				document.body.style.cursor = "default";
			}
		}
	});
}

	function alertateste(inidresultado,inoficial,inobj){
		var emailsec;				
		vacao = "";
		if(inobj.checked){
			vacao = "true";
			vacaoalerta = "DISPARADO";
		}else{
			vacao = "false";
			vacaoalerta = "RETIRADO";
		}
		//colocar o campo emailsec na tabela resultado como Y ou N para enviar emails de resultados possitivos para a secretaria
		// ser for oficial e se estiver marcado como alerta sim fazer a pergunta
		if(inoficial =='Y' && vacao== "true"){
			alert("Resultado oficial com alerta, será enviado email para secretaria!!!");
			//if(confirm("Deseja enviar email para Secretaria?")) {
				emailsec="A";
			//}else{
			//	emailsec="N";
			//}
		}else{
			 emailsec="N";
		}
		
		vurl = "../ajax/alertateste.php?acao="+vacao+"&idpessoa=<?=$lppag_idpessoa?>&idresultado="+inidresultado+"&emailsec="+emailsec;
		document.body.style.cursor = "wait";

		$.ajax({
			type: 'get',
			url: vurl,
			success: function(data){// retorno 200 do servidor apache
			    vdata = data.replace(/(\r\n|\r|\n)/g, "");

				if(vdata=="OK"){
					alert('O Alerta foi '+vacaoalerta+' para o teste selecionado!');
					document.body.style.cursor = "default";
				}else{
					alert(data);
	                document.body.style.cursor = "default";
				}
		    },	     
		    error: function(objxml){ // nao retornou com sucesso do apache
	        	document.body.style.cursor = "default";
	        	alert('Erro: '+objxml.status);
	   		}
	    })//$.ajax
	}
 
//função que monta a tela de assinatura monta o corpo e as interpretaçoes
function  listaresultado(botao){
	//simula clique no botao de navegacao conforme o tipo da acao
	if(botao=="prox"){
		$("#imggor").css("opacity","1.0");
	}
	if(botao=="ant"){
		$("#imggol").css("opacity","1.0");
	}

	var urlx="assinaresultadocorpo.php?<?=$urini?>&uniqueid=<?=$uniqueid?>&acao=" + botao;
	//UNIQUE_ID para controlar o select da pagina assinarresutladocorpo.php
	var vuniqueid = "<?=$uniqueid?>";
	
	window.status=urlx;
	//ajax para montar o corpo do assinarresultado
	$.ajax({
		type: "get",
		url : urlx,
		data: {},
		success: function(data){
			try{
				$('#conteudo').html(data+'<br /><br /><br /><br />');
			}catch(e){
				console.log(e);
			}finally{
				
			}

		},
		error: function(objxmlreq){
			alert('Erro:<br>'+objxmlreq.status); 
		}
	})//$.ajax		

	//Esconde os botoes de navegacao
	$("#imggor").stop().animate({ opacity: 0.35 }, 50);
	$("#imggol").stop().animate({ opacity: 0.35 }, 50);			
}
tinyMCE.editors["resm"].remove();
function iniciaEdicao(){
	$(this.event.target).val("Gravar");
	$(this.event.target).attr("onclick","gravaEdicao(this)");

	
	$(`#btassina`).attr("disabled",true);
	$(`#btretira`).attr("disabled",true);
	$(`#imggor`).hide();
	$(`#imggol`).hide();


	$("td[tabela], span[tabela]").each(function(){
		$(this).attr("contenteditable","true");
		$(this).css("background-color","yellow");
	});
	tinymce.init({
		selector: 'div[tabela]#resm',
		language: 'pt_BR',
		// inline: true /* não usar iframe */ ,
		toolbar: ' italic | subscript superscript | formatselect | removeformat | fontsizeselect | | bullist numlist | table |  alignleft aligncenter alignright alignjustify',
		menubar: false,
		plugins: ['table', 'autoresize', 'imagetools', 'contextmenu', 'advlist', 'template'],
		setup: function(editor) {
			editor.on('init', function(e) {
				this.setContent($(`div[tabela]#resm`).html());
			});
		},
		entity_encoding: 'raw'
	});
	$(`[campodata]`).each((i,e)=>{
		$(e).on('input', function() {
		const divContent = $(this).html();
			if (isValidDate(divContent)) {
				$(this).css("background-color","yellow");
			} else {
				$(this).css("background-color","red");

			}
		});
	})

}

function isValidDate(dateString) {
	// Verifica se a data está no formato dd/mm/aaaa
	const datePattern = /^(\d{2})\/(\d{2})\/(\d{4})$/;
	if (!datePattern.test(dateString)) {
		return false;
	}

	const parts = dateString.split('/');
	const day = parseInt(parts[0], 10);
	const month = parseInt(parts[1], 10) - 1; // Mês no objeto Date começa em 0 (Janeiro) até 11 (Dezembro)
	const year = parseInt(parts[2], 10);

	const date = new Date(year, month, day);
	// Verifica se a data criada é válida
	if (date.getFullYear() === year && date.getMonth() === month && date.getDate() === day) {
		return true;
	} else {
		return false;
	}
}

async function gravaEdicao(vthis){
	obj = {}

	if (tinyMCE.editors["resm"]) {
		//falha nbsp: oDescritivo.val( tinyMCE.get('resm').getContent({format : 'raw'}).toUpperCase());
		$(`#resm`).html( tinyMCE.get('resm').getContent().replace(/[a-z]/gi, function(char) {
			return char;
		}));
		tinyMCE.editors["resm"].remove();
	}
	await $("td[tabela], span[tabela], div[tabela]").each(function(i,e){
		obj[`_${$(e).attr("idpk")}_u_${$(e).attr("tabela")}_${$(e).attr("campo")}`] = $(e).html().replace(/&nbsp;/g," ").trim()
	});
		
	console.log(obj);
	try{


		let response = await fetch("../ajax/postedicaoresultado.php", { 
			method: "POST",
			headers: {
				"Content-Type": "application/x-www-form-urlencoded",
				"authorization": (Cookies.get('jwt') || localStorage.getItem("jwt") || "")
			},
			body: JSON.stringify(obj)
		});
		let json = await response.json();
		console.log(json);
	}catch(e){
		console.log(e);
	}finally{
		$("td[tabela], span[tabela], div[tabela]").each(function(i,e){
			$(e).off("click");
			$(e).off("focus");
			$(e).off("focusout");
			$(e).attr("contenteditable","false");
			$(e).css("background-color","white");
		});
		$(vthis).val("Editar");
		$(vthis).attr("onclick","iniciaEdicao()");
		$(`#btassina`).attr("disabled",false);
		$(`#btretira`).attr("disabled",false);
		$(`#bteditar`).attr("disabled",false);
		$(`#imggor`).show();
		$(`#imggol`).show();
	}
}



</script>
</head>	
<body onload="listaresultado('ini')">
<div id="gol" class="gol"><img src="../inc/img/gol.png" class="imggo" id="imggol" onclick="listaresultado('ant')"></div>
<div id="gor" class="gor"><img src="../inc/img/gor.png" class="imggo" id="imggor" onclick="listaresultado('prox')"></div>

<div class="divsup">
	<div id="conteudo" style="display: table-cell; height: 100%; width: 100%; padding:0 5%;">
	<!-- conteudo aqui aparece a assinaresultado corpo -->
	</div>
</div>
</body>
</html>
<?
$retdel = delgraf();
?>
