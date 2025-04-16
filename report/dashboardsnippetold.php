<link rel="stylesheet" href="./inc/css/dashboard.css" />

	  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/packery/2.0.0/packery.pkgd.min.js"></script>
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/draggabilly/2.1.0/draggabilly.pkgd.min.js"></script>
<style>
.carddash{
	width:220px;
	float:left;
}

    html,
body {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100%;
}

body {
  position: relative;
  overflow-x: hidden;
}

.grid-item:hover {
  cursor: all-scroll;
}

@media (max-width: 767px) {
  .grid-item {
    padding: 5px 10px;
    width: 100%;
  }
}

.packery-drop-placeholder {
  outline: 1px dashed #f00;
  outline-offset: -6px;
  /* transition position changing */
  -webkit-transition: -webkit-transform 0.2s;
  transition: transform 0.2s;
}

</style>
<?
require_once("../inc/php/functions.php");

 
 
 
 
	$ss = "SELECT d.iddashboard
			,d.dashboard
			,d.dashboard_title
			,d.url
			,d.idunidade
			,d.especial
			,d.titulo
			,d.code
			,d.panel_id
			,d.panel_class_col
			,concat('".$titulo." ', panel_title) as panel_title
			,d.card_id
			,d.card_class_col
			,d.card_url
			,d.card_notification_bg
			,d.card_notification
			,d.card_color
			,d.card_border_color
			,d.card_bg_class
			,d.card_title
			,d.card_title_sub
			,d.card_value as card_value
			,d.card_icon
			,d.card_title_modal
			,d.card_url_modal
			FROM laudo.dashboard d
				JOIN "._DBCARBON."._lpobjeto lo on lo.tipoobjeto='dashboard' 
					and lo.idobjeto=d.iddashboard
					and lo.idlp in(".getModsUsr("LPS").")
					where d.status = 'ATIVO'
					order by
			d.dashboard, d.panel_id, card_id";

        $rs = d::b()->query($ss) or die("Erro ao recuperar snippets: ". mysqli_error(d::b()));
	
	$i = 0;
	$unionall = '';
	
	while($_row= mysqli_fetch_assoc($rs)){ 

		$dashlotescond = " and l.idunidade = '".$_row['idunidade']."' and l.especial = '".$_row['url']."'";	
		$linkunidade 		= $_row['idunidade'];
		$especial = $_row['especial'];
		$titulo = $_row['titulo'];
		$sqldash .= 	$unionall.$_row['code'];
		$unionall = ' UNION ALL ';
		
		
		$conf[$_row['card_id']]['card_id']				= $_row['card_id'];
		$conf[$_row['card_id']]['card_class_col']				= $_row['card_class_col'];
		$conf[$_row['card_id']]['card_url']				= $_row['card_url'];
		$conf[$_row['card_id']]['card_notification_bg']				= $_row['card_notification_bg'];
		$conf[$_row['card_id']]['card_notification']				= $_row['card_notification'];
		$conf[$_row['card_id']]['card_border_color']				= $_row['card_border_color'];
		$conf[$_row['card_id']]['card_color']				= $_row['card_color'];
		$conf[$_row['card_id']]['card_bg_class']				= $_row['card_bg_class'];
		$conf[$_row['card_id']]['card_title']				= $_row['card_title'];
		$conf[$_row['card_id']]['card_title_sub']				= $_row['card_title_sub'];
		$conf[$_row['card_id']]['card_title_modal']				= $_row['card_title_modal'];
		$conf[$_row['card_id']]['card_url_modal']				= $_row['card_url_modal'];
		$conf[$_row['card_id']]['card_value']				= $_row['card_value'];
		$conf[$_row['card_id']]['card_icon']				= $_row['card_icon'];
		
		
	}
	
	//echo '<pre>'.$sqldash.'</pre>';
	$resfig = d::b()->query("SET GLOBAL group_concat_max_len=4294967295;") or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	$resfig = d::b()->query("SET SESSION group_concat_max_len=4294967295;") or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	$resfig = d::b()->query($sqldash) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	$i = -1;
	$j = 0;
	
	while ($_row = mysql_fetch_assoc($resfig)){
		
		if ($panel_id != $_row['panel_id']){
			$panel_id = $_row['panel_id'];
			$i++;
			
			$json[$i]['panel_id']							= $_row['panel_id'];
			$json[$i]['panel_class_col']					= $_row['panel_class_col'];
			$json[$i]['panel_title']						= $_row['panel_title'];
			$j = 0;
		}else{
			$json[$i]['panel_class_col']					= 'col-md-'.(($j)*2).' aqui '.$j;
		}
		
		/*$json[$i]['cards'][$j]['card_id']					= $conf[$_row['card_id']]['card_id'];
		$json[$i]['cards'][$j]['card_class_col']					= $conf[$_row['card_id']]['card_class_col'];
		$json[$i]['cards'][$j]['card_url']					= $conf[$_row['card_id']]['card_url'];
		$json[$i]['cards'][$j]['card_notification_bg']					= $conf[$_row['card_id']]['card_notification_bg'];
		$json[$i]['cards'][$j]['card_notification']					= $conf[$_row['card_id']]['card_notification'];
		$json[$i]['cards'][$j]['card_border_color']					= $conf[$_row['card_id']]['card_border_color'];
		$json[$i]['cards'][$j]['card_color']					= $conf[$_row['card_id']]['card_color'];
		$json[$i]['cards'][$j]['card_bg_class']					= $conf[$_row['card_id']]['card_bg_class'];
		$json[$i]['cards'][$j]['card_title']					= $conf[$_row['card_id']]['card_title'];
		$json[$i]['cards'][$j]['card_title_sub']					= $conf[$_row['card_id']]['card_title_sub'];
		$json[$i]['cards'][$j]['card_title_modal']					= $conf[$_row['card_id']]['card_title_modal'];
		$json[$i]['cards'][$j]['card_url_modal']					= $conf[$_row['card_id']]['card_url_modal'];
		$json[$i]['cards'][$j]['card_value']					= $_row['card_value'];
		$json[$i]['cards'][$j]['card_icon']					= $conf[$_row['card_id']]['card_icon'];
		$j++;
		*/
		
		$json[$i]['cards'][$j]['card_id']					= $_row['card_id'];
		$json[$i]['cards'][$j]['card_class_col']			= 'carddash';
		$json[$i]['cards'][$j]['card_url']					= $_row['card_url'];
		$json[$i]['cards'][$j]['card_notification_bg']		= $_row['card_notification_bg'];
		$json[$i]['cards'][$j]['card_notification']			= $_row['card_notification'];
		$json[$i]['cards'][$j]['card_border_color']			= $_row['card_border_color'];
		$json[$i]['cards'][$j]['card_color']				= $_row['card_color'];
		$json[$i]['cards'][$j]['card_bg_class']				= $_row['card_bg_class'];
		$json[$i]['cards'][$j]['card_title']				= $_row['card_title'];
		$json[$i]['cards'][$j]['card_title_sub']			= $_row['card_title_sub'];
		$json[$i]['cards'][$j]['card_title_modal']			= $_row['card_title_modal'];
		$json[$i]['cards'][$j]['card_url_modal']			= $_row['card_url_modal'];
		$json[$i]['cards'][$j]['card_value']				= $_row['card_value'];
		$json[$i]['cards'][$j]['card_icon']					= $_row['card_icon'];
		$j++;
		
		
		
	}
	
	//print_r($json);
	console.log(json); 
	
	$json = json_encode($json);
	//echo $json;
?>

<script>

function montapanel(json, callback){
	var i=0;
	 var panel = '<div class="container"><div class="row"><div class="grid">';
	 var passou=false;
	 console.log(json);
	 json.forEach(function(item, index) {
		 i++;
		 console.log('criar '+item.panel_id);
		 if($('#' + item.panel_id).length == 0){
			  console.log('passou '+item.panel_id);
			 passou = true;
		   panel = panel + 
		  `	<!-- LOOP DOS PAINÉIS --> 
				
					<div class="col-md-4 grid-item">
						<div class="panel panel-default panel-primary"  style="border-color:#F3F3F3">
							<div class="panel-body">
								<h3 class="text-on-pannel text-primary" id="${item.panel_title}"><strong class="text-uppercase"> ${item.panel_title}</strong></h3>
								<div id="${item.panel_id}">
								</div>
							</div>
						</div>
					</div>
			
			<!-- FIM LOOP DOS PAINÉIS -->`;
			
			
			
			
		}
	 
	 });
	 $('#cbModuloForm').append(panel);
	 /* if(passou){
		$('#cbModuloForm').html(panel);
	  } */
	 callback(json);
	 
}  
	 
montaCards=function(json){

	var card = '';
	var hide='';
	json.forEach(function(item, index) {

	console.log('Painel ' + item.panel_id + ' '+ $('#' + item.panel_id).length);
	if($('#' + item.panel_id).length > 0){

		item["cards"].forEach(function(i, x) {
				//console.log(i.card_id);
			console.log('Card '+$('#' + i.card_id).length);
			if($('#' + i.card_id).length == 0){   
				
				card = `
					<!-- LOOP DOS BLOCOS -->
					<div id="${i.card_id}">
						<div class="${i.card_class_col} mb-4 pointer hovercinzaclaro" onclick="popLink('${i.card_url}','${i.card_title_modal}','${i.card_border_color}','${i.card_url_modal}')" >
							<span id="cbIBadgeSnippet2" class="${i.card_notification_bg} badge badgedash hide" style="" ibadge="${i.card_notification}">${i.card_notification}</span>
							<div class="card border-left-${i.card_border_color} shadow h-100 py-2 bg-${i.card_bg_class}"style="border-radius:8px;">
								<div class="card-body">
									<div class="row no-gutters align-items-center">
										<div class="col-md-12">
											<div class="text-xs negrito text-uppercase mb-1" style="color:#888;text-align:left;padding:0px 8px">${i.card_title}</div>
											
										</div>
										
									</div>
									<div class="row">
										<div class="col-md-12">
										<div class="h6 mb-0 font-weight_bold text_gray-800 titulo-${i.card_color}" style="text-align:center;font-weight:bolder;"><span id='card_value'>${i.card_value}</span></div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
										<div  style="text-align:left;font-weight:bolder;"><span id='card_title_sub' class="bg-${i.card_border_color}" card_titlesub>${i.card_title_sub}</span></div>
										</div>
									</div>
								</div>
							</div>
						</div> 
					</div> 
						
					<!-- FIM LOOP DOS BLOCOS --> `;
				
					$('#' + item.panel_id).append(card);
					$('#' + item.panel_id).find( "#cbIBadgeSnippet2" ).hide();
				
			}else{
				console.log('existe'); 
				$('#' + item.panel_id).find( "#cbIBadgeSnippet2" ).removeClass();
				$('#' + item.panel_id).find( "#cbIBadgeSnippet2" ).addClass(' badge badgedash aaaa');
				$('#' + item.panel_id).find( "#cbIBadgeSnippet2" ).addClass(i["card_notification_bg"]);
				$('#' + item.panel_id).find( "#cbIBadgeSnippet2" ).html(i["card_notification"]);
  
			 
			}
					
		});
		
		/* if (card != ''){
		
		$('#' + item.panel_id).append(card);
		card = '';
		} */
	}
	
	
	}); 
	 //return (card);
} 

	
	

//Desenhar os elementos HTML la tela  
montaHTML=function(){

	var json2 =  [{
		"panel_id":"lotemalertavendas",
 		"panel_class_col": "col-md-12",
 		"panel_title": "LOTE EM ALERTA VENDAS",
 		"cards": [{
			"card_id":"quantidadedeamostras",
 			"card_class_col": "col-md-2 col-sm-4 col-xs-6",
 			"card_url": "report/relevento.php?_acao=u&amp;idevento=315419",
 			"card_notification_bg": "fundovermelho",
 			"card_notification": "66",
 			"card_color": "",
 			"card_border_color": "danger",
 			"card_bg_class": "danger",
 			"card_title": "Quantidade de Amostras",
 			"card_value": "129",
 			"card_icon": "fa-print"
 		}]
 	}
 ];
 
 
 
 var json = <?=$json;?>;

	montapanel(json, function(resultado){
		montaCards(resultado);
	});
	
	 
}


function popLink(url,title,color,urlmodal){
//	alert(url);
		vGet = "_modulo=formalizacao&_pagina=0&_ordcol=idlote&_orddir=desc&&_fts=form&especial=Y";
	

	var strCabecalho = "</strong><label class='fonte08'><span class='titulo-"+color+"'>"+title+"</span></label></strong>";

	//Altera o cabeçalho da janela modal
	$("#cbModalTitulo")
				.html(strCabecalho)
				.append("&nbsp;&nbsp;<label id='resultadosEncontrados' class='fonte08'></label>")
				.append("<i class='fa fa-print floatright' id='btPrintNucleo' title='Impressão' onclick=\"printNucleo(2)\"></i>")
				.append("<i class='fa fa-eye floatright' title='Marcar como visualizados' onclick=\"resetNotificacoesPorNucleo(2)\"></i>")
	;

	console.log('teste'+url);  
	if (url != '' && url != 'null'){
		console.log('teste'+url);
	if (url.search("php")>=0 || url.search("novajanela")>=0){
		link= './'+url;
		janelamodal(url);
	}else{
		link='form/_modulofiltrospesquisa.php'
	
	//Realiza a chamada da pagina de pesquisa manualmente
	$.ajax({
		context: this,
		type: 'get', 
		cache: false,
		url: link,
		data: url,
		dataType: "json",
		beforeSend: function(){
			alertAguarde();
		},
		success: function(data, status, jqXHR){

			//Json contem resultados encontrados?
			if(!$.isEmptyObject(data)){
				//Nos casos onde existia um número muito grande linhas, o browser estava apresentando lentidão. Caso o número de linhas seja > configuracao do Mà³dulo, direcionar para tela de search
				if(parseInt(data.numrows)>parseInt(CB.jsonModulo.limite)||data.numrows>2000){
					alertAtencao("Mais de "+CB.jsonModulo.limite+" resultados foram encontrados!\n<a href='?" + vGetAutofiltro+"' target='_blank' style='color:#00009a;'><i class='fa fa-filter'></i> Clique aqui para filtrar os resultados encontrados.</a>");
					janelamodal("?" + vGetAutofiltro);
				}else{
					$("#cbModal").addClass("noventa").modal();
					var tblRes = CB.montaTableResultados(data, function(obj, event){

						oTr = $(obj);
						oTr.css("backgroundColor","transparent");
						
							//janelamodal("'"+urlmodal+"'" + oTr.attr("goParam"));
					
						janelamodal('?'+urlmodal+'&' + oTr.attr("goParam"));
						
					});
					$("#cbModal #cbModalCorpo").html(tblRes);

					if(data.numrows){
						$("#resultadosEncontrados").html("("+data.numrows+" resultados encontrados)").attr("cbnumrows",data.numrows);
					}
				}
			}else{
				
					alert("Nenhum resultado encontrado.");
				
			}
		},
		complete: function(){
			CB.aguarde(false);
			if(CB.limparResultados==true){
				CB.resetDadosPesquisa();
			}
		}
	});
	}
	}
}

$('#cbModal').one('hide.bs.modal', function(){
	CB.inicializaModulo();
});
	<? // } ?>
	montaHTML();
</script>
      
	  <script>
	  // initialize Packery
var $grid = $('.grid').packery({
  itemSelector: '.grid-item'
});
	  </script>