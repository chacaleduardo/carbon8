<?
include_once("../inc/php/validaacesso.php");

$_SESSION['arraynucleo']=array();
//die();
?>

<!-- Biblioteca do grafico -->   
<script src="inc/js/amcharts/amcharts.js"></script>
<script src="inc/js/amcharts/serial.js"></script>

<!-- Dados para o gráfico -->
<!-- <script src="ajax/comploteDados.php"></script> -->
<style>
.divtab {
	display:none;
}

#chartdiv{
	position: absolute;
	width: 100%;
	left: 0px;
	margin: 1rem 0;
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
				<th>Unidade:</th>
				<th>Núcleos:</th>
				<th>Tipo de Teste:</th>		
				<th>Baseline:</th>		
				<th>Semanas:</th>		
			</tr>
			<tr>	
				<td>
					<select id="idcliente" multiple="multiple" title="Selecione a Unidade"></select>
				</td>
				<td>
					<select id="idnucleo" multiple="multiple" title="Selecione os Núcleos" ></select>
					<input type="checkbox" id="situacao" value="INATIVO" onchange="atualizaNucleo();"><font   color="gray">ABATIDO</font>
				</td>
				<td>
					<select id="idtipoteste" title="Selecione os Tipos de Teste" ></select>
				</td>								
				<td>
					<input id="baseline" type="number" title="Valor base" />
				</td>								
				<td>
					<select id="idades" multiple="multiple" title="Semanas"></select>
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

	var oIdcliente = $("#idcliente");
	var oIdnucleo = $("#idnucleo");
	var oIdtipoteste = $("#idtipoteste");
	var oSituacao = $("#situacao");
	let idades = [];
	let dadosGraf = [];//objeto json agrupando os núcleos por idade
	var chart = null;

		/*
		* Cada núcleo precisa ter uma cor distinta, independente se estiver selecionado ou não, para facilitar a comparação.
		* Isto está sendo feito na atualização da drop de núcleos
		* As cores serão fixas nos núcleos seguindo a sequàªncia do array global CB.arrcores, repetindo ao final
		*/
		var arrCoresNucleos = {}; 

		// Valor do baseline (valor de base no gráfico)
		var baseline = null;
		const valueAxis = new AmCharts.ValueAxis();
		const baselineAxis = new AmCharts.ValueAxis();

		baselineAxis.axisAlpha = 1;
		baselineAxis.gridAlpha = 1;

		const guides = [
			{
				fillAlpha: 1,
				lineAlpha: 1,
				"value": baseline,
				"toValue": baseline,
				balloonText: `${baseline}`,
				lineThickness: 1,
				fillColor: '#000',
				position: 'left',
				above: true
			}
		];

		//Loop nos objetos do gráfico: não está sendo utilizado
		AmCharts.addInitHandler(function(chart) {
		for (var x = 0; x < chart.graphs.length; x++) {
			var graph = chart.graphs[x];    
		}
		});

		//funcao para atualizar o gráfico
		function atualizaGrafico(){				
			if($.trim($('#idcliente').val()).length!=0 && $.trim($('#idnucleo').val()).length!=0 && $.trim($('#idtipoteste').val()).length!=0){

				vIdcliente = $('#idcliente').val();

				dadosGraf = montaJsonGrafico();//objeto json agrupando os núcleos por idade	 
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
				categoryAxis.title = "Semanas";
				categoryAxis.fontSize=12;
				categoryAxis.gridPosition = "start";
				categoryAxis.gridAlpha = 0.1;
				categoryAxis.axisAlpha = 0;
				
				// Value
				valueAxis.title = "GMT";
				valueAxis.axisAlpha = 0;
				valueAxis.gridAlpha = 0.1;
				valueAxis.guides = guides;

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

		function addBaseline() {
			if(chart) {
				baselineAxis.removeGuide(guides);

				if(baselineAxis) {
					guides[0].value = baseline;
					guides[0].toValue = baseline
					guides[0].balloonText = `${baseline}`

					baselineAxis.guides = guides;

					chart.addValueAxis(baselineAxis);
					chart.validateNow();
				}
			}
		}

	//Armazenar temorariamente os testes conforme núcleos selecionados
	var arrTestes={};

	//Atribui o plugin selectpicker
	$([oIdcliente,oIdnucleo,oIdtipoteste]).selectpicker({
		liveSearch: true
	});

	$('#idades').selectpicker();

	//Atualiza Unidades
	async function atualizaUnidades(){

		await $.ajax({
                url: "ajax/comploteDados.php",
                type: "GET",	
                data: { 
                    acao : "buscarunidades"
                },
                beforeSend: function(inxhr){
                    oIdcliente.addClass("carregando");
                },
                success: function( data ){
                    try {
						jClientes = JSON.parse(data);
                        if(jClientes.unidades!=undefined){
							//Recupera o Json com as unidades, realiza loop e monta o objeto
							$.each(jClientes["unidades"], function(k,v){
								oIdcliente.append("<option value='"+k+"'>" + v.nome + '</option>');
							});
							//Atualiza o obj na tela
							oIdcliente.selectpicker("refresh");
						}else{
							alertAtencao("Nenhuma Unidade foi encontrada.\nProvavelmente o usuário não está configurado.");
						}
                    } catch (e) {
                        alertErro(e.toString());
                    }
                    oIdcliente.removeClass("carregando");
                },						
                error: function( objxmlreq ){
                    console.error(objxmlreq);
                }
            });
	}
	//Evento onclick UNIDADE
	oIdcliente.on('changed.bs.select', function (e) {
		$valsel = $.trim($(this).val());
		if($valsel.length!==0){
			atualizaNucleo($(this).val());
		}else{
			limpaGrafico();
		}
	});
	
	//Atualiza Nucleos
	async function atualizaNucleo(){
		var arrClientes = oIdcliente.val();

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
		await $.ajax({
			url: "ajax/comploteDados.php",
			type: "GET",	
			data: { 
				acao : "buscarnucleo",
				idpessoa : arrClientes.join()
			},
			beforeSend: function(inxhr){
			},
			success: function( data ){

				data = JSON.parse(data);

				/*
				arrIdades
				arrTmp
				arrClienteNucleos
				arrNucleos
				arrTestes
				arrVacinas
				*/

				globalThis.jClienteNucleos = data.arrClienteNucleos
				globalThis.jIdades = data.arrIdades
				globalThis.jVacinas = data.arrVacinas
				globalThis.jNucleos = data.arrNucleos

				if(typeof(jClienteNucleos["clientes"]) != 'undefined'){
					$.each(arrClientes,function(i,idCliente){
						if (jClienteNucleos["clientes"].hasOwnProperty(idCliente)){
						
						//Loop nos nucleos
						$.each(jClienteNucleos["clientes"][idCliente], function(idnucleo,nucleo){
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
								atualizaArrTestes(idCliente, vIdNucleo);

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
									atualizaArrTestes(idCliente, vIdNucleo,data.arrTestes);
								}
							}
						});
					}
				});
				}
			},						
			error: function( objxmlreq ){
				console.error(objxmlreq);
			}
		});
		$.each(arrClientes,function(i,idCliente){
		
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

	$('#idades').on('change', function () {
		filtrarPorSemana($(this).val());
	});
	
	/*
	 * Atualiza o array de testes concatenando todos os nucleos encontrados, associando com o nome do teste para mostrar na drop
	 * Esta função é chamada várias vezes, porque o mesmo teste pode ser executado em vários núcleos 
	 */
	function atualizaArrTestes(inIdCliente, inIdnucleo,jTestes){
		$.each(jClienteNucleos["clientes"][inIdCliente][inIdnucleo].testes, function(k,v){
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
							if(!idades.includes(vIdade))
								idades.push(vIdade);
					
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
			jGraf += '"categoriaIdade":"'+ idade +'"';

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

		atualizarSelectIdades();


		//Transforma a String em Objeto json e devolve para o gráfico
		return JSON.parse(jGraf);

	}
	
	//Inicia o processo atualizando a drop de unidades
	atualizaUnidades();

	//Para testes: automatizar seleção ao carregar a pagina
	//$('#idcliente').selectpicker('val', '360'); atualizaNucleo(); $('#idnucleo').selectpicker('val', [9693, 9880]); $('#idtipoteste').selectpicker('val', '626');atualizaGrafico();

	//Verifica se a drop de Clientes possui seleção. Caso contrário efetua highlight
	setTimeout(function(){
		if($.trim(oIdcliente.val()).length==0){
			//Highlight visual
			oIdcliente.data().selectpicker.$button.addClass("highlightVerde");
		}
	},1000);

	var timeout = null
	$('#baseline').on('keyup', function() {
		if(this.value == baseline) return;

		clearTimeout(timeout)
		timeout = setTimeout(function() {          
			baseline = this.value
			addBaseline();
		}.bind(this), 800);
	});

	function atualizarSelectIdades() {
		$('#idades').html('');

		let idadesHTML = idades.sort().map(idade => (
			`<option value='${idade}'>${idade} ${idade == 1 ? 'Semana' : 'Semanas'}</option>`
		)).join(' ');

		$('#idades')
			.html(idadesHTML)
			.selectpicker('refresh');
	}

	
	function filtrarPorSemana(idade) {
		let dadosFiltrados =  dadosGraf;
		
		if(idade && idade.length)
			dadosFiltrados = dadosGraf.filter(item => idade.includes(item.categoriaIdade));

		chart.dataProvider = dadosFiltrados;
		chart.validateData();
	}

	
//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
