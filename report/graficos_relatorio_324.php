<?php

function criarInputSelectAgrupadorGraficoLinha($_idrep){
    $sqlfld = "SELECT 
                tc.col,
                tc.rotcurto, 
                rc.eixograph
            from (" . _DBCARBON . "._rep r 
                join " . _DBCARBON . "._mtotabcol tc) 
                left join " . _DBCARBON . "._repcol rc on (rc.idrep = r.idrep and r.tab = tc.tab and rc.col = tc.col)
            where 
                r.tab = tc.tab  
                and rc.eixograph in ('X','G')
                and rc.visres='Y'
                and r.idrep = " . $_idrep . " ";


    $resfld = d::b()->query($sqlfld) or die("Erro ao recuperar colunas: " . mysql_error(d::b()));


    $options = "";
    $gShow = 0;
    while ($rf = mysql_fetch_array($resfld)) {

        if (empty($rf['rotcurto'])) {
            $rt = $rf['col'];
        } else {
            $rt = $rf['rotcurto'];
        }

        if ($rf['eixograph'] == "G") {
            $gShow++;
            $options .= "<option value=" . $rf['col'] . ">" . $rt . "</option>";
        } else if ($rf['eixograph'] == "X") {
            $options .= "<option value='' selected>" . $rt . "</option>";
        }
    }

    $inputSelect = '   
        <div style="display:none;  margin-top:15px;" class="pull-right _report_amchart_ col-md-6"  id="inputAgrupamento">
            <div id="_lb_agp_" class="col-md-5" style="text-align: end; margin-top: 5px;">
                <label>Dados Agrupados por:</label>
            </div>
            <div class="col-md-7">
                <select id="getSeparator" onchange="mudarAgrupamento(this)">
                    ' . $options . '
                </select>                
            </div>  
        </div>  
        ';

    if ($gShow >= 1) {
        return $inputSelect;
    }
}


echo criarInputSelectAgrupadorGraficoLinha($_idrep);

?>

<script class="_report_amchart_">
    //https://www.amcharts.com/demos-v3/
    function controlaTamanhoStringEixoX(arrayFinal,eixoX,eixoY,tamanhoString){

        let controleStringEixoX = arrayFinal.map(function(obj){

            let eixoXString = obj[eixoX];
            let palavrasStringEixoX = "";
            let arrPalavra = eixoXString == null ? '' : eixoXString.trim().toLowerCase().split(" "); 

            if(arrPalavra != ""){
                for (let index = 0; index < arrPalavra.length; index++) {
                    if(arrPalavra[index] !=""){
                        palavrasStringEixoX += arrPalavra[index][0].toUpperCase() + arrPalavra[index].substr(1);                            
                        palavrasStringEixoX += " ";
                    }     
                }
            } else {
                palavrasStringEixoX = "";
            }   

            let objetoRetorno = {
                valorOriginal: palavrasStringEixoX
            };

            if(palavrasStringEixoX.length >= tamanhoString){               
                
                palavrasStringEixoX = palavrasStringEixoX.substr(0, tamanhoString) + "...";                
                return {
                    ...objetoRetorno,
                    [eixoX] : palavrasStringEixoX,
                    [eixoY] : obj[eixoY]
                }

            } else {

                return {
                    ...objetoRetorno,
                    [eixoX] : palavrasStringEixoX, [eixoY] : obj[eixoY]
                }

            }
            return {
                ...objetoRetorno,
                [eixoX] : palavrasStringEixoX, [eixoY] : obj[eixoY]
            }

        })
        
        return controleStringEixoX;
    }

    

    function criaArrayDadosGraficoModeloObjetoChaveValor(arrayDadosPrimario, eixoYAlterado = false){
        if(arrayDadosPrimario.length == 0) return [];
        
        let arr = arrayDadosPrimario;
        let eixoX = '<?= $eixoX ?>';
        let eixoY = eixoYAlterado ? eixoYAlterado : '<?= $eixoY[0] ?>';
        let numeroDeItensPorObjetoNoArrayArr = Object.keys(arr[0]).length;
        let arrAux = [];
        let arrAgp = [];
        let arrFinal = [];
        arrFinal[0]=[];
        arrFinal[1]=[];

        if(numeroDeItensPorObjetoNoArrayArr == 2){           
            arrAux = arr;
        } else {
    
            for (let i in arr) {              

                arrAux[i] = {
                    [eixoX]: arr[i][eixoX],
                    [eixoY]: arr[i][eixoY]
                };
            }
        }


        for (let i in arrAux) {
            let valCampoEixoX = arrAux[i][eixoX];
            let valCampoEixoY = parseFloat(arrAux[i][eixoY] || 0);

            
            if( arrAgp[valCampoEixoX] ){
                arrAgp[valCampoEixoX][eixoY] += valCampoEixoY;
            }else{
                arrAgp[valCampoEixoX] = {
                    [eixoX] : valCampoEixoX,
                    [eixoY] : valCampoEixoY,
                };
            }
        }
        
        //adiciona nomes dos eixos X e Y na primeira posição do array        
        arrFinal[0].push({'eixoX' : eixoX, 'eixoY' :eixoY})

        for(let o in arrAgp){
            arrFinal[1].push(arrAgp[o]);
        }

        return arrFinal;
    }

    function redimensionarDivDoGrafico(numeroPosicoesDoArrFinal){
        if(numeroPosicoesDoArrFinal > 22) {        
        let hg = numeroPosicoesDoArrFinal - 22;
        hg = (hg * 22)+500;
        $('#chartdiv').height(hg)
        } else {
            $('#chartdiv').height(500)
        }    
    }


    function gerarGraficoBarrasAgrupadas(arrFilter = []){
        let arrayDados =   arrFilter.length == 0 ?  <?= json_encode($arrayGrafico) ?> : arrFilter;

        if(arrayDados.length == 0) return;

        let arrFinal = criaArrayDadosGraficoModeloObjetoChaveValor(arrayDados);
        var config = {
            graphs: [],
        };

            function criarArrayDadosGraficoBarrasAgrupadas(arrayDados){
                let eixoX = '<?= $eixoX ?>';
                let arrEixoY = <?= json_encode(array_unique($eixoY)) ?>;
                let tamanhoMaxStringEixoX=20;

                let arrayDadosInicioTratamento = arrayDados.map(function(obj){
                    let stringEixoX= obj[eixoX];
                    let newStringEixoX = stringEixoX == null ? '' : stringEixoX.trim().toLowerCase().split(" "); 
                    let newStringObjEixoY=[];
                    let palavrasStringEixoX="";

                    //Passa String eixo X para Minuscula
                    if(newStringEixoX != ""){
                        for (let index = 0; index < newStringEixoX.length; index++) {
                            if(newStringEixoX[index] !=""){
                                palavrasStringEixoX += newStringEixoX[index][0].toUpperCase() + newStringEixoX[index].substr(1);                            
                                palavrasStringEixoX += " ";
                            }     
                        }
                    } else {
                        palavrasStringEixoX = "Não Definido";
                    } 
                    
                    //Controla Tamanho da string eixo X
                    if(palavrasStringEixoX.length >= tamanhoMaxStringEixoX){      
                        palavrasStringEixoX = palavrasStringEixoX.substr(0, tamanhoMaxStringEixoX) + "...";                
                    } else {
                        palavrasStringEixoX = palavrasStringEixoX;
                    }

                    for (let index = 0; index < arrEixoY.length; index++) {
                        
                        let stringEixoY = arrEixoY[index];
                        newStringObjEixoY[stringEixoY] = `${obj[stringEixoY]}`;

                    }
                    
                    return {[eixoX] : palavrasStringEixoX, ...newStringObjEixoY}
                    
                })
                console.log(arrayDadosInicioTratamento);
                return arrayDadosInicioTratamento;
            }


        let arrayDadosTratados =criarArrayDadosGraficoBarrasAgrupadas(arrayDados);
        let meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
        let eixoX = '<?= $eixoX ?>';
        let arrEixoY = <?= json_encode(array_unique($eixoY)) ?>;


        for (let index = 0; index < arrEixoY.length; index++) {    

            config.graphs.push({
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                "fillAlphas": 0.8,
                //"labelText": "[[value]]",
                "lineAlpha": 0.3,
                "title": meses[index],
                "type": "column",
                "color": "#000000",
                "valueField": arrEixoY[index]
            });
        }

        console.log(config)

        var chart = AmCharts.makeChart("chartdiv", {
            "type": "serial",
            "theme": "none",
            "legend": {
                "horizontalGap": 10,
                "maxColumns": 1,
                "position": "right",
                "useGraphSettings": true,
                "markerSize": 10
            },
            "numberFormatter": {
                "precision": 2,
                "decimalSeparator": ",",
                "thousandsSeparator": "."
            },
            "dataProvider": arrayDadosTratados,
            "valueAxes": [{
                "stackType": "regular",
                "axisAlpha": 0.3,
                "gridAlpha": 0
            }],
            "graphs": config.graphs,
            "categoryField": eixoX,
            "categoryAxis": {
                "position": "left",
                "labelRotation": 30,
                "labelFrequency" : 0,
                "parseDates" : false
            },
            "export": {
                "enabled": false
            }
        
        });

    }

    function getHighLowVal (arr = [], key) {
        let lowest = Number.POSITIVE_INFINITY;
        let highest = Number.NEGATIVE_INFINITY;
        let tmp;
        for (let i = arr.length-1; i>=0; i--) {
            tmp = arr[i][key];
            if (tmp < lowest) lowest = tmp;
            if (tmp > highest) highest = tmp;
        }

        return {
            min: lowest,
            max: highest
        };
    }

    function gerarGraficoBarrasVerticais(arrFilter = []) {

        let arrayDados =   arrFilter.length == 0 ?  <?= json_encode($arrayGrafico) ?> : arrFilter;

        if(arrayDados.length == 0) return;

        let totalEixoY = 0;
        let arrFinal = criaArrayDadosGraficoModeloObjetoChaveValor(arrayDados);        
        let eixoX = arrFinal[0][0].eixoX;
        let eixoY = arrFinal[0][0].eixoY;

    
        arrFinal = arrFinal[1];
        let minMax = getHighLowVal(arrFinal, eixoY);

        let numeroPosicoesDoArrFinal = arrFinal.length;

        let valorMaximoArrFinal = minMax.max;
        let valorMinimoArrFinal = minMax.min;

        let valorMedioArrFinal = (valorMaximoArrFinal - valorMinimoArrFinal) / 2;
        let valorMedioSuperior = (valorMedioArrFinal + valorMedioArrFinal) * 0.333;
        let valorMedioInferior = (valorMedioArrFinal - valorMedioArrFinal) * 0.333;

        let dadosGrafico = controlaTamanhoStringEixoX(arrFinal,eixoX,eixoY,20);   

        //console.log(dadosGrafico);

        redimensionarDivDoGrafico(numeroPosicoesDoArrFinal); 
        
        
        for (let index = 0; index < dadosGrafico.length; index++) {
            totalEixoY += dadosGrafico[index][eixoY];
        }

        
        let dadosGraficoFinal = dadosGrafico.map(function(obj){
            let proporcao = ((obj[eixoY]/totalEixoY)*100).toFixed(2);
            return {[eixoX] : obj[eixoX], [eixoY] : obj[eixoY], percentual : proporcao}
        })


        totalY = totalEixoY.toLocaleString("pt-BR")


        AmCharts.addInitHandler(function(chart) {

            let dataProvider = chart.dataProvider;
            let colorRanges = chart.colorRanges;


            // Based on https://www.sitepoint.com/javascript-generate-lighter-darker-color/
            function ColorLuminance(hex, lum) {

                // validate hex string
                hex = String(hex).replace(/[^0-9a-f]/gi, '');
                if (hex.length < 6) {
                    hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
                }
                lum = lum || 0;

                // convert to decimal and change luminosity
                let rgb = "#",
                    c, i;
                for (i = 0; i < 3; i++) {
                    c = parseInt(hex.substr(i * 2, 2), 16);
                    c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);
                    rgb += ("00" + c).substr(c.length);
                }

                return rgb;
            }

            if (colorRanges) {

                let item;
                let range;
                let valueProperty;
                let value;
                let average;
                let variation;
                for (let i = 0, iLen = dataProvider.length; i < iLen; i++) {

                    item = dataProvider[i];

                    for (let x = 0, xLen = colorRanges.length; x < xLen; x++) {

                        range = colorRanges[x];
                        valueProperty = range.valueProperty;
                        value = item[valueProperty];

                        if (value >= range.start && value <= range.end) {
                            average = (range.start - range.end) / 2;

                            if (value <= average)
                            variation = (range.variation * -1) / value * average;
                            else if (value > average)
                            variation = range.variation / value * average;

                            item[range.colorProperty] = ColorLuminance(range.color, variation.toFixed(2));
                        }
                    }
                }
            }

        }, ["serial"]);

        let chart = AmCharts.makeChart("chartdiv", {
            "type": "serial",
            "colorRanges": [{
                "start": valorMedioSuperior-1,
                "end": valorMaximoArrFinal,
                "color": "#3ADF00",
                "variation": 0.2,
                "valueProperty": eixoY,
                "colorProperty": "color"
            }, {
                "start": valorMedioInferior,
                "end": valorMedioSuperior,
                "color": "#D7DF01",
                "variation": 0.2,
                "valueProperty": eixoY,
                "colorProperty": "color"
            }, {
                "start": valorMinimoArrFinal,
                "end": valorMedioInferior+1,
                "color": "#DF3A01",
                "variation": 0.2,
                "valueProperty": eixoY,
                "colorProperty": "color"
            }],
            "allLabels": [{
                "y": "10%",
                "x": "80%",
                "size": 18,
                "bold": true,
                "text": `TOTAL ${totalY}`,
                "color": "#555"
            }],
            "dataProvider": dadosGraficoFinal,
            "theme": "none",
            "numberFormatter": {
                "precision": 2,
                "decimalSeparator": ",",
                "thousandsSeparator": "."
            },
            "graphs": [{
                "balloonText": `[[${eixoX}]] <br>KW/H [[value]] <br>([[percentual]]%)`,
                "fillAlphas": 1,
                "lineAlpha": 0.2,
                "labelText": "[[value]]",
                "title": eixoX,
                "type": "column",
                "valueField": eixoY,
                "colorField": "color"
            }],
            "depth3D": 0,
            "angle": 0,
            "rotate": false,
            "categoryField": eixoX,
            "chartScrollbar": {},
            "categoryAxis": {
                "gridPosition": "start",
                "fillAlpha": 0.05,
                "labelRotation": 30,
                "position": "left",
            },
            "valueField": eixoY,
            "titleField": eixoX,
        });

    }

    function gerarGraficoBarrasLaterais(arrFilter = []) {

        let arrayDados =   arrFilter.length == 0 ?  <?= json_encode($arrayGrafico) ?> : arrFilter;

        if(arrayDados.length == 0) return;

        let totalEixoY = 0;
        let arrFinal = criaArrayDadosGraficoModeloObjetoChaveValor(arrayDados);        
        let eixoX = arrFinal[0][0].eixoX;
        let eixoY = arrFinal[0][0].eixoY;


        arrFinal = arrFinal[1].sort(function(a, b){return b[eixoY]-a[eixoY]});
        let numeroPosicoesDoArrFinal = arrFinal.length;
        let valorMaximoArrFinal = arrFinal[0][eixoY];
        let valorMinimoArrFinal = arrFinal[arrFinal.length-1][eixoY];
        let valorMedioArrFinal = (valorMaximoArrFinal-valorMinimoArrFinal)/2;
        let valorMedioSuperior = valorMedioArrFinal+valorMedioArrFinal*0.333;
        let valorMedioInferior = valorMedioArrFinal-valorMedioArrFinal*0.333;

        let dadosGrafico = controlaTamanhoStringEixoX(arrFinal,eixoX,eixoY,20);   

        //console.log(dadosGrafico);

        redimensionarDivDoGrafico(numeroPosicoesDoArrFinal); 


        for (let index = 0; index < dadosGrafico.length; index++) {
            totalEixoY += dadosGrafico[index][eixoY];
        }


        let dadosGraficoFinal = dadosGrafico.map(function(obj){
            let proporcao = ((obj[eixoY]/totalEixoY)*100).toFixed(2);
            return {[eixoX] : obj[eixoX], [eixoY] : obj[eixoY], percentual : proporcao}
        })


        totalY = totalEixoY.toLocaleString("pt-BR")


        AmCharts.addInitHandler(function(chart) {

            let dataProvider = chart.dataProvider;
            let colorRanges = chart.colorRanges;


            // Based on https://www.sitepoint.com/javascript-generate-lighter-darker-color/
            function ColorLuminance(hex, lum) {

                // validate hex string
                hex = String(hex).replace(/[^0-9a-f]/gi, '');
                if (hex.length < 6) {
                    hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
                }
                lum = lum || 0;

                // convert to decimal and change luminosity
                let rgb = "#",
                    c, i;
                for (i = 0; i < 3; i++) {
                    c = parseInt(hex.substr(i * 2, 2), 16);
                    c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);
                    rgb += ("00" + c).substr(c.length);
                }

                return rgb;
            }

            if (colorRanges) {

                let item;
                let range;
                let valueProperty;
                let value;
                let average;
                let variation;
                for (let i = 0, iLen = dataProvider.length; i < iLen; i++) {

                    item = dataProvider[i];

                    for (let x = 0, xLen = colorRanges.length; x < xLen; x++) {

                        range = colorRanges[x];
                        valueProperty = range.valueProperty;
                        value = item[valueProperty];

                        if (value >= range.start && value <= range.end) {
                            average = (range.start - range.end) / 2;

                            if (value <= average)
                            variation = (range.variation * -1) / value * average;
                            else if (value > average)
                            variation = range.variation / value * average;

                            item[range.colorProperty] = ColorLuminance(range.color, variation.toFixed(2));
                        }
                    }
                }
            }

        }, ["serial"]);


        let chart = AmCharts.makeChart("chartdiv", {
            "type": "serial",
            "colorRanges": [{
                "start": valorMedioSuperior-1,
                "end": valorMaximoArrFinal,
                "color": "#3ADF00",
                "variation": 0.2,
                "valueProperty": eixoY,
                "colorProperty": "color"
            }, {
                "start": valorMedioInferior,
                "end": valorMedioSuperior,
                "color": "#D7DF01",
                "variation": 0.2,
                "valueProperty": eixoY,
                "colorProperty": "color"
            }, {
                "start": valorMinimoArrFinal,
                "end": valorMedioInferior+1,
                "color": "#DF3A01",
                "variation": 0.2,
                "valueProperty": eixoY,
                "colorProperty": "color"
            }],
            "allLabels": [{
                "y": "85%",
                "x": "80%",
                "size": 18,
                "bold": true,
                "text": `TOTAL ${totalY}`,
                "color": "#555"
            }],
            "dataProvider": dadosGraficoFinal,
            "theme": "none",
            "numberFormatter": {
                "precision": 2,
                "decimalSeparator": ",",
                "thousandsSeparator": "."
            },
            "graphs": [{
                "balloonText": "KW/H:[[value]] "+" ([[percentual]]%)",
                "fillAlphas": 1,
                "lineAlpha": 0.2,
                "labelText": "[[value]]"+"([[percentual]]%)",
                "title": eixoX,
                "type": "column",
                "valueField": eixoY,
                "colorField": "color"

            }],
            "depth3D": 0,
            "angle": 0,
            "rotate": true,
            "categoryField": eixoX,
            "categoryAxis": {
                "gridPosition": "start",
                "fillAlpha": 0.05,
                "position": "left"
            },
            "valueField": eixoY,
            "titleField": eixoX,
        });

    }


    function gerarGraficoPizza(arrFilter = [], separator = false) {
        let arrayDados =   arrFilter.length == 0 ?  <?= json_encode($arrayGrafico) ?> : arrFilter;
        const valorPosFixado = '<?= $valorPosFixado ?? ''?>';

        if(arrayDados.length == 0) return;

        let arrFinal = criaArrayDadosGraficoModeloObjetoChaveValor(arrayDados, separator);        
        let eixoX = arrFinal[0][0].eixoX;
        let eixoY = separator ? separator : arrFinal[0][0].eixoY;
        let totalEixoY = 0;
        arrFinal = arrFinal[1].sort(function(a, b){return b[eixoY]-a[eixoY]});
        let numeroPosicoesDoArrFinal = arrFinal.length;

        let dadosGrafico = controlaTamanhoStringEixoX(arrFinal,eixoX,eixoY,20);  
        //console.log(dadosGrafico);

        redimensionarDivDoGrafico(numeroPosicoesDoArrFinal);      

        for (let index = 0; index < dadosGrafico.length; index++) {
            totalEixoY += dadosGrafico[index][eixoY];
        }

        let totalY = totalEixoY.toLocaleString("pt-BR");

        function criarArrayCoresGrafico(){
            let valorMaximoArrFinal = arrFinal[0][eixoY];
            let valorMinimoArrFinal = arrFinal[arrFinal.length-1][eixoY];
            let valorMedioArrFinal = (valorMaximoArrFinal-valorMinimoArrFinal)/2;
            let valorMedioSuperior = valorMedioArrFinal+valorMedioArrFinal*0.333;
            let valorMedioInferior = valorMedioArrFinal-valorMedioArrFinal*0.333;
            let red = 250;
            let green = 230;
            let blue = 230;
            let rgb = "";

            color=[];

            for (let index = 0; index < arrFinal.length; index++) {            
                if(arrFinal[index][eixoY] > valorMedioSuperior){
                    //start 'RGB(30, 230 , 30)'
                    rgb = `RGB(30,${green},30)`;
                    color.push(rgb);
                    green = green - 30;

                } else if(arrFinal[index][eixoY] < valorMedioSuperior && arrFinal[index][eixoY] > valorMedioInferior){
                    //start 'RGB(30, 200, 230 )
                    rgb = `RGB(30,${blue},230)`;
                    color.push(rgb);
                    blue = blue - 30;

                } else if(arrFinal[index][eixoY] < valorMedioInferior){
                    //start 'RGB(230, 200, 30 )'
                    rgb = `RGB(${red},30,30)`;
                    color.push(rgb);
                    red = red - 30;

                }            
            }

            return color;
        }     


        color=criarArrayCoresGrafico();

        let fatiasSelecionadas = [];

        const charConfig = {
            "type": "pie",
            "startDuration": 0,
            "theme": "none",
            "addClassNames": true,
            labelText: '[[title]]: [[percents]]%',
            "legend":{
                "position" : "right",
                "valueWidth" : 100,
                "labelWidth" : 140,
                "autoMargins" : true,
                "markerSize" : 10,
                "verticalGap" : 4
            },
            "allLabels": [{
                "y": "90%",
                "align": "left",
                "size": 15,
                "bold": true,
                "text": `TOTAL ${totalY}`,
                "color": "#555"
            }], 
            "numberFormatter": {
                "precision": 2,
                "decimalSeparator": ",",
                "thousandsSeparator": "."
            },
            "colors": color,
            "innerRadius": "30%",
            "defs": {
                "filter": [{
                "id": "shadow",
                "width": "200%",
                "height": "200%",
                "feOffset": {
                    "result": "offOut",
                    "in": "SourceAlpha",
                    "dx": 0,
                    "dy": 0
                },
                "feGaussianBlur": {
                    "result": "blurOut",
                    "in": "offOut",
                    "stdDeviation": 5
                },
                "feBlend": {
                    "in": "SourceGraphic",
                    "in2": "blurOut",
                    "mode": "normal"
                }
                }]
            },
            "dataProvider": dadosGrafico,
            "valueField": eixoY,
            "titleField": eixoX,
            "listeners": [{
                "event": "clickSlice",
                "method": function(e) {
                    const valor = e.dataItem.dataContext.valorOriginal;
                    let campoFiltro = $('#inputFiltro');

                    // Atualizando o array de fatias selecionadas
                    const itensSelecionados = e.dataItem.dataContext;
                    const index = fatiasSelecionadas.findIndex(item => item.toLowerCase().trim() === itensSelecionados.valorOriginal.toLowerCase().trim());

                    if (index === -1) {
                        fatiasSelecionadas.push(itensSelecionados.valorOriginal.trim());
                    } else {
                        fatiasSelecionadas.splice(index, 1);
                    }

                    campoFiltro.val(fatiasSelecionadas.join(','));

                    filtrarTabela();
                }
            }]
        };

        if(valorPosFixado) charConfig.legend.valueText = `[[${eixoY}]] ${valorPosFixado}`;

        var chart = AmCharts.makeChart("chartdiv", charConfig);

        chart.addListener("init", handleInit);
  
        chart.addListener("rollOverSlice", function(e) {
            handleRollOver(e);
        });
        
        function handleInit(){
            chart.legend.addListener("rollOverItem", handleRollOver);
        }
        
        function handleRollOver(e){
            var wedge = e.dataItem.wedge.node;
            wedge.parentNode.appendChild(wedge);
        }
    }

    function gerarGraficoMultiLinhas(separator,arrFilter = []) {

        let arr = <?= json_encode($arrayGrafico) ?>;

        if(arr.length == 0) return;

        let eixoX = '<?= $eixoX ?>';
        let eixoY = '<?= $eixoY[0] ?>';
        let categories = [];
        let arrAux = [];
        let arrFinal = [];
        let config = {
            graphs: [],
        };

        if(!separator){
            separator = eixoY;
            categories.push(eixoY);
        }else{
            for (let o of arr) {
                categories.indexOf(o[separator]) === -1 ? categories.push(o[separator]) : null;
            }
        }

        $('#alert_agrupamento').remove();
        if (categories.length >= 10) {
            $('#_lb_agp_').append(' <i id="alert_agrupamento" class="fa fa-exclamation-triangle laranja blink" style="margin-left:10px;" aria-hidden="true" title="O gráfico apresenta ' + categories.length + ' linhas no eixo Y, podendo não ser visualizado corretamente. Para resolver o problema mude o agrupamento ou reduza o período selecionado."></i>')
        }

        for (let i in categories) {
            let ballonText = "";
            if(arr[0]['diferenca']){
                ballonText = "<b style='text-align: start;'>[[diferenca]]</b><p style='text-align: start;font-size: 15px;line-heigth'><b>[[value]]</b></p>";
            }else{
                ballonText = categories[i] +': [[value]]';
            } 

            config.graphs.push({
                "balloonText": ballonText,
                "valueAxis": "v" + i,
                "lineColor": gerar_cor(1),
                "bullet": "round",
                "bulletBorderThickness": 1,
                "hideBulletsCount": 30,
                "title": categories[i],
                "valueField": categories[i],
                "fillAlphas": 0,
            });
        }

        if(separator != eixoY){
            for (let i in arr) {
                arrAux.push({ [eixoX]: arr[i][eixoX] });
                for (let category of categories) {
                    arrAux[i][category] = (category == arr[i][separator]) ? arr[i][eixoY] : 0;
                }
            }
        }else{
            arrAux = arr;
        }
        
        let regx = /[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]|^(19|20)\d\d[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$/;
        let datesAnt = new Map();
        let j = -1;
        for (let i in arrAux) {

            if (datesAnt.has(arrAux[i][eixoX])) {
                let k = datesAnt.get(arrAux[i][eixoX]);
                for (let category of categories) {
                    arrFinal[k][category] += isNaN(arrAux[i][category]) ? 0 : parseFloat(arrAux[i][category]);
                }
            } else {
                j++;

                datesAnt.set(arrAux[i][eixoX], j);
                arrFinal[j] = { [eixoX]: arrAux[i][eixoX] };

                if(arrAux[i]['diferenca']){
                    arrFinal[j]['diferenca'] = `
                        <span style='display: inline-flex; align-items: center; margin-bottom:4px;text-align:start;'>
                            <img  align='left' vertical-align="top" height='20px' width='20px'  src='entrada.png'/>
                                <span>`+arrAux[i]['diferenca']['entrada']+`</span>
                        </span>
                        <span style='display: inline-flex; align-items: center;text-align:start'>
                            <img  align='left' height='20px' width='20px'  src='saida.png'/>
                            <span>`+arrAux[i]['diferenca']['saida']+`</span>
                        </span>
                    `;
                }

                
                for (let category of categories) {
                    arrFinal[j][category] = isNaN(arrAux[i][category]) ? 0 : parseFloat(arrAux[i][category]);
                }
            }
        }

        if(regx.test(arrFinal[0][eixoX])){
            arrFinal = arrFinal.sort(function(a, b){
                return moment(a[eixoX]).valueOf() - moment(a[eixoX]).valueOf();
            });

            arrFinal = arrFinal.map(function(a) {
                a[eixoX] = moment(a[eixoX]).format('DD/MM/YYYY');
                return a;
            });
        }

        arrFinal = arrFinal.map(function(e) {
            for (let category of categories) {
                e[category] = (e[category] == 0) ? null : e[category];
            }
            return e;
        })

        if(arrFilter.length > 0){            
            
            console.log(arrFinal);
            let arrPosFilter = [];
            for(let values of arrFinal){
                let index = arrFilter.findIndex(eixoXPosFilter => eixoXPosFilter == values[eixoX]);
                if(index > -1){
                    arrPosFilter.push(values);
                }
            }
            arrFinal = arrPosFilter;
            console.log(arrFinal);

        }
        

        let chart = AmCharts.makeChart("chartdiv", {
            "type": "serial",
            "theme": "none",
            "legend": {
                "useGraphSettings": true,
                "valueWidth":100
            },
            "numberFormatter": {
                "precision": 2,
                "decimalSeparator": ",",
                "thousandsSeparator": "."
            },
            "dataDateFormat": "DD/MM/YYYY HH:NN:SS",
            "dataProvider": arrFinal,
            "synchronizeGrid": true,
            "graphs": config.graphs,
            "chartScrollbar": {},
            "chartCursor": {
                "cursorPosition": "mouse"
            },
            "categoryField": eixoX,
            "categoryAxis": {
                "parseDates": false,
                "axisColor": "#DADADA",
                "minorGridEnabled": true,
                "labelRotation": 30
            },            
        });
    }


    function gerar_cor(opacidade = 1) {
        let r = parseInt(Math.random() * 100);
        let g = parseInt(Math.random() * 255);
        let b = parseInt(Math.random() * 255);
        return `rgba(${r}, ${g}, ${b}, ${opacidade})`;
    }


</script>