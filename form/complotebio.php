<?
include_once("../inc/php/validaacesso.php");

$_SESSION['arraynucleo']=array();
//die();
?>

<!-- Biblioteca do grafico -->   
<script src="inc/js/amcharts/amcharts.js"></script>
<script src="inc/js/amcharts/serial.js"></script>

<!-- Dados para o gráfico -->
<script src="ajax/complotebioDados.php"></script>

<script>

var chart = null;

/*
 * Cada núcleo precisa ter uma cor distinta, independente se estiver selecionado ou não, para facilitar a comparação.
 * Isto está sendo feito na atualização da drop de núcleos
 * As cores serão fixas nos núcleos seguindo a sequàªncia do array global CB.arrcores, repetindo ao final
 */
var arrCoresNucleos = {}; 

//Loop nos objetos do gráfico: não está sendo utilizado
AmCharts.addInitHandler(function(chart) {
  for (var x = 0; x < chart.graphs.length; x++) {
    var graph = chart.graphs[x];    
  }
});

//funcao para atualizar o gráfico
function atualizaGrafico(){				

	if($.trim($('#idlote').val()).length!=0 && $.trim($('#idnucleo').val()).length!=0 && $.trim($('#idtipoteste').val()).length!=0){

		vidlote = $('#idlote').val();
	
		var dadosGraf = montaJsonGrafico();//objeto json agrupando os núcleos por idade	 
		console.log(JSON.stringify(dadosGraf));
		
		// SERIAL CHART
		chart = new AmCharts.AmSerialChart();
		chart.dataProvider = dadosGraf;
		chart.categoryField = "categoriaIdade";
		chart.startDuration = 0;
		chart.plotAreaBorderColor = "#DADADA";
		chart.backgroundAlpha = 1;
		chart.fontFamily = "arial";
		chart.numberFormatter= {precision:-1, decimalSeparator:',', thousandsSeparator:'.'}

		// AXES
		// Category
		var categoryAxis = chart.categoryAxis;
		//categoryAxis.title = "Semanas";
		categoryAxis.fontSize=12;
		categoryAxis.gridPosition = "start";
		categoryAxis.gridAlpha = 0.1;
		categoryAxis.axisAlpha = 0;
		
		// Value
		var valueAxis = new AmCharts.ValueAxis();
		valueAxis.title = "GMT";
		valueAxis.axisAlpha = 0;
		valueAxis.gridAlpha = 0.1;
		chart.addValueAxis(valueAxis);
		
		// GRAPHS
		//Loop nos nucleos selecionados para informar ao gráfico quem serão as barras
		$.each($("#idnucleo").val(),function(k,v){
			vIdnucleo=v;
		
			vNucleo = jNucleos["nucleos"][vIdnucleo];
		
			var graph = new AmCharts.AmGraph();
			graph.type = "column";
			graph.title = vNucleo;
			graph.valueField = "barra"+vIdnucleo;
			graph.balloonText = "[[value]] [[description]]";
			graph.descriptionField = "vac"+vIdnucleo;
			graph.customBulletField = "icon"+vIdnucleo;
			graph.bulletSize = 36;
			graph.bulletOffset = 20;
			graph.lineAlpha = 0;
			graph.fillAlphas = 1;
			graph.urlField = "url"+vIdnucleo;
			graph.urlTarget = "_blank"
			//graph1.colorField = "cor"+vIdnucleo; //ajusta somente a cor da barra (?)
			//graph1.fillColorsField = "cor"+vIdnucleo; //ajusta somente a cor da barra (?)
			graph.fillColors = arrCoresNucleos[vIdnucleo];
			graph.showHandOnHover=true;

			chart.addGraph(graph);
		
		})
		
		// LEGEND
		var legend = new AmCharts.AmLegend();
		chart.addLegend(legend);
		
		// WRITE
		chart.write("chartdiv");
	}
}

function limpaGrafico(){
	if(chart != null){
		chart.clear();
		chart = null;
	}
}

//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>
</script> 
<style>
.divtab {
	display:none;
}
#chartdiv{
	position: fixed;
	left: 0px;
}
a[href*=amcharts]{
	display: none !important;
}
</style>

<div class="panel panel-default">
	<div class="panel-heading">
	Comparativo de N&uacute;cleos	
	</div>
	<div class="panel-body" style="display:inline-block;">
		<div style="margin-bottom: 6px;">
			<table>
			<tr>
				<th>Produto:</th>
				<th>Núcleos:</th>
				<th>Tipo de Teste:</th>		
			</tr>
			<tr>	
				<td>
					<select id="idlote" multiple="multiple" title="Selecione o Lote"></select>
				</td>
				<td>
					<select id="idnucleo" multiple="multiple" title="Selecione os Núcleos" ></select>
					<input type="checkbox" id="situacao" value="INATIVO" onchange="atualizaNucleo();"><font   color="gray">ABATIDO</font>
				</td>
				<td>
					<select id="idtipoteste" title="Selecione os Tipos de Teste" ></select>
				</td>								
			</tr>
			</table>
		</div>
		<div>
			<div id="chartdiv" style="height: 400px; min-width:99%; margin:8px; margin-top: 20px;"></div>
		</div>
	</div>
</div>
 
<script>

	var oIdlote = $("#idlote");
	var oIdnucleo = $("#idnucleo");
	var oIdtipoteste = $("#idtipoteste");
	var oSituacao = $("#situacao");

	//Armazenar temorariamente os testes conforme núcleos selecionados
	var arrTestes={};

	//Atribui o plugin selectpicker
	$([oIdlote,oIdnucleo,oIdtipoteste]).selectpicker({
		liveSearch: false
	});

	//Atualiza Unidades
	function atualizaUnidades(){

		if(JProd.produtos!=undefined){
			//Recupera o Json com as unidades, realiza loop e monta o objeto
			$.each(JProd["produtos"], function(k,v){
				oIdlote.append("<option value='"+k+"'>" + v.descr + '</option>');
			});
			//Atualiza o obj na tela
			oIdlote.selectpicker("refresh");
		}else{
			alertAtencao("Nenhuma Unidade foi encontrada.\nProvavelmente o usuário não está configurado.");
		}

	}
	//Evento onclick UNIDADE
	oIdlote.on('changed.bs.select', function (e) {
		$valsel = $.trim($(this).val());
		if($valsel.length!==0){
			atualizaNucleo($(this).val());
		}else{
			limpaGrafico();
		}
	});
	
	//Atualiza Nucleos
	function atualizaNucleo(){
		debugger;
		var arrProd = oIdlote.val();

		//Prepara para highlight
		oIdnucleo.data().selectpicker.$button.removeClass("highlightVerde");

		//Reset no array de testes
		arrTestes={};
		
		//Remove os itens anteriores
		oIdnucleo.empty();

		//Reset nas cores
		arrCoresNucleos={};
		var iEncontrados=0;
		var iCor=0;
		
		$.each(oIdlote.val(),function(i,idlote){
		
		if(typeof(jProdNucleos["produtos"]) != 'undefined'){
		
		if (jProdNucleos["produtos"].hasOwnProperty(idlote)){
			
			//Loop nos nucleos
			$.each(jProdNucleos["produtos"][idlote], function(idnucleo,nucleo){
				vIdNucleo = idnucleo;
				vNucleo = nucleo;

				//Adiciona os novos itens
				if(oSituacao.prop("checked")){
					iEncontrados++;

					//Controla a cor da barra
					if(iCor>CB.arrCores.length)iCor=0;
					arrCoresNucleos[vIdNucleo]=CB.arrCores[iCor];
					iCor++;

					//Lista Tudo
					oIdnucleo.append("<option value='"+vIdNucleo+"'  data-content='<i style=\"color:"+arrCoresNucleos[vIdNucleo]+"\" class=\"fa fa-square\"></i>&nbsp;&nbsp;"+ vNucleo.nucleo +"'>" + vNucleo.nucleo + '</option>');

					//Atualiza o ARRAY de Testes
					atualizaArrTestes(idlote, vIdNucleo);

				}else{
					//Lista somente com situacao=ATIVO
					if(vNucleo.situacao=="ATIVO"){
						iEncontrados++;

						//Controla a cor da barra
						if(iCor>CB.arrCores.length)iCor=0;
						arrCoresNucleos[vIdNucleo]=CB.arrCores[iCor];
						iCor++;

						//Lista nucleo selecionado
						oIdnucleo.append("<option value='"+vIdNucleo+"'  data-content='<i style=\"color:"+arrCoresNucleos[vIdNucleo]+"\" class=\"fa fa-square\"></i>&nbsp;&nbsp;"+ vNucleo.nucleo +"'>" + vNucleo.nucleo + '</option>');

						//Atualiza o ARRAY de Testes
						atualizaArrTestes(idlote, vIdNucleo);
					}
				}
			});
		}
		}
		});
		//Alerta caso não seja encontrado nenhum nucleo vivo
		if(iEncontrados==0){
			alert("Nenhum Nucleo VIVO encontrado");
			//Reinicializa drop de testes
			oIdtipoteste.empty();
		}
		
		//
		atualizaTestes();

		//Atualiza o obj na tela
		oIdnucleo.selectpicker("refresh");
		
		//Highlight visual
		oIdnucleo.data().selectpicker.$button.addClass("highlightVerde");
		
		//tenta atualizar o gráfico
		atualizaGrafico();
	}

	//Evento onclick NUCLEOS
	oIdnucleo.on('changed.bs.select', function (e) {
		$valsel = $.trim($(this).val());
		if($valsel.length!==0){
		
			//Verifica se a drop de testes possui seleção. Caso contrário efetua highlight
			if($.trim(oIdtipoteste.val()).length==0){
				//Prepara para highlight
				oIdtipoteste.data().selectpicker.$button.removeClass("highlightVerde");
				
				setTimeout(function(){
					//Highlight visual
					oIdtipoteste.data().selectpicker.$button.addClass("highlightVerde");
				}, 10);
			}
		
			atualizaGrafico();
		}else{
			limpaGrafico();
		}
		
	});

	//Evento onclick TIPOTESTE
	oIdtipoteste.on('changed.bs.select', function (e) {
		$valsel = $.trim($(this).val());
		if($valsel.length!==0){
			atualizaGrafico();
		}else{
			limpaGrafico();
		}
	});
	
	/*
	 * Atualiza o array de testes concatenando todos os nucleos encontrados, associando com o nome do teste para mostrar na drop
	 * Esta função é chamada várias vezes, porque o mesmo teste pode ser executado em vários núcleos 
	 */
	function atualizaArrTestes(inidlote, inIdnucleo){
		$.each(jProdNucleos["produtos"][inidlote][inIdnucleo].testes, function(k,v){
			arrTestes[k]=jTestes["tiposteste"][k]["descr"];
		});
	}
	
	//Atualizar a drop de testes conforme o array
	function atualizaTestes(){
		//Prepara para highlight
		oIdtipoteste.data().selectpicker.$button.removeClass("highlightVerde");
		
		oIdtipoteste.empty();
		var sPrimeiroOpt = "<option value=''></option>";

		//Loop nos tipos de teste
		$.each(arrTestes, function(k,v){
			oIdtipoteste.append(sPrimeiroOpt);
			sPrimeiroOpt="";
			oIdtipoteste.append("<option value='"+k+"'>" + v + '</option>');
		});

		//Efetua highlight visual
		oIdtipoteste.data().selectpicker.$button.addClass("highlightVerde");
		
		//Atualiza o objeto na tela
		oIdtipoteste.selectpicker("refresh");
		
		//Limpa o gráfico
		limpaGrafico();
	}
	
	//Montar o json para o grafico conforme as opçàµes selecionadas
	function montaJsonGrafico(){
		
		//Opção selecionada
		idTipotesteSelecionado = oIdtipoteste.val();

		//Array para os dados filtrados
		arrResultadosFiltrados={};

		/*
		 * Transforma o json do servidor em array javascript já filtrado conforme o que estiver selecionado nas drops
		 * As chaves do Array serão as idades, por estas serem o Axis X do gráfico
		 */
		$.each(oIdnucleo.val(), function(k,v){

			vIdNucleo=v;
			
			//Loop nas Idades
			$.each(jIdades["idades"], function(idade,nucleos){
				vIdade = idade;
				vNucleos = nucleos;
				
				//Verifica se existe o núcleo na idade
				vNucleo = vNucleos[vIdNucleo];
				if(vNucleo!=undefined){

					//Loop nos nucleos que tàªm teste pra idade
					$.each(vNucleo, function(idtipoteste,resultado){
						//Verifica se o teste realizado é o mesmo selecionado na drop
						if(idtipoteste==idTipotesteSelecionado){
					
							//Inicializa a idade
							if(arrResultadosFiltrados[vIdade]==undefined){
								arrResultadosFiltrados[vIdade]=[];
							}

							//Cria um elemento com o restante das informaçàµes
							elem = {};
							elem.idnucleo = vIdNucleo;
							elem.gmt = resultado.gmt;
							elem.vacina = resultado.vacina;
							elem.idresultado = resultado.idres;

							arrResultadosFiltrados[vIdade].push(elem);

						}
					});
				}
				
			});

			if(jVacinas && jVacinas.nucleos){
				$.each(jVacinas.nucleos, (idnucleo,vacinas) => {
					if(idnucleo === vIdNucleo){
						$.each(vacinas, (i, infos) => {
							vIdade = infos.idade;

							if(arrResultadosFiltrados[vIdade]==undefined){
								arrResultadosFiltrados[vIdade]=[];
							}
							
							//Cria um elemento com o restante das informaçàµes
							elem = {};
							elem.idnucleo = vIdNucleo;
							elem.gmt = infos.gmt;
							elem.vacina = infos.vacinas;
							elem.idresultado = infos.idres;

							arrResultadosFiltrados[vIdade].push(elem);
						});
					}
				});
			}
		})

		/*
		 * Neste ponto o array está filtrado, mas não está formatado no padrão do AmCharts
		 * Isto foi dividido por ter causado complexidade na montagens dos arrays
		 * Formata o array encontrado para o padrão do gráfico:
		 */
		jGraf='[';
		virg='';
		
		//console.log(JSON.stringify(arrResultadosFiltrados));
		
		$.each(arrResultadosFiltrados, function(idade,dadosGraf){

			jGraf += virg + '{';
			jGraf += '"categoriaIdade":"Dia: '+ idade +'"';

			//Loop em todos os resultados de todos os nucleos encontrados
			$.each(dadosGraf, function(k,item){
				jGraf += ',"barra'+item.idnucleo+'":"'+ item.gmt+'"';
				jGraf += ',"vac'+item.idnucleo+'":"'+ item.vacina+'"';
				(item.idresultado !== 0) ? jGraf += ',"url'+item.idnucleo+'":"report/emissaoresultado.php?idresultado='+ item.idresultado +'"' : jGraf += ',"url'+item.idnucleo+'":""';
				jGraf += ',"cor'+item.idnucleo+'":"'+ arrCoresNucleos[item.idnucleo] +'"';
				(item.vacina !== "") ? jGraf += ',"icon'+item.idnucleo+'":"inc/img/fa-syringe.png"' : jGraf += ',"icon'+item.idnucleo+'":""';
			})
			
			jGraf += "}";
			virg=",";
		})
		jGraf += "]";

		//Transforma a String em Objeto json e devolve para o gráfico
		return JSON.parse(jGraf);

	}
	
	//Inicia o processo atualizando a drop de unidades
	atualizaUnidades();

	//Para testes: automatizar seleção ao carregar a pagina
	//$('#idcliente').selectpicker('val', '360'); atualizaNucleo(); $('#idnucleo').selectpicker('val', [9693, 9880]); $('#idtipoteste').selectpicker('val', '626');atualizaGrafico();

	//Verifica se a drop de Clientes possui seleção. Caso contrário efetua highlight
	setTimeout(function(){
		if($.trim(oIdlote.val()).length==0){
			//Highlight visual
			oIdlote.data().selectpicker.$button.addClass("highlightVerde");
		}
	},1000);
	
//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
