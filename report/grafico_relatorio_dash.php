<style>
    .chartbutton{
        position:fixed;
        right:170px;
        top:3px;
        font-weight: bold;
        font-size: 20px;
        color: silver;
        border: 1px solid #d7d7d7;
        cursor: pointer;
        padding-left: 5px;
        padding-right: 5px;
        padding-bottom: 1px;
        border-radius: 8px;
    }

    .chartbutton:hover{
        background: -webkit-gradient(linear, left top, right top, from(#ff0000), to(#c02900));
    }
</style>
<script src="../inc/js/amcharts/amcharts.js"></script>
<script src="../inc/js/amcharts/serial.js"></script>
<script src="../inc/js/functions.js"></script>

<?php

$sqlfld = "SELECT 
            tc.tab, 
            tc.col,
            rc.psqkey,
            rc.psqreq,
            tc.datatype, 
            tc.rotcurto, 
            rc.idrepcol,
            rc.idrep,
            rc.visres,
            rc.align,
            rc.grp,
            rc.ordseq,
            rc.ordtype,
            rc.tsum,
            rc.tavg,
            rc.hyperlink,
            rc.entre,
            rc.inseridomanualmente,
            rc.calendario,
            rc.like,
            rc.in,
            rc.inval,
            rc.json,
            (case when isnull(rc.idrepcol) then 'i' else 'u' end) as act,
            rc.ordcol,
            rc.acsum,
            rc.acavg,
            rc.eixograph
            from ("._DBCARBON."._rep r 
            join "._DBCARBON."._mtotabcol tc) 
            left join "._DBCARBON."._repcol rc on (rc.idrep = r.idrep and r.tab = tc.tab and rc.col = tc.col)
            where 
            r.tab = tc.tab
            and (rc.eixograph is null or rc.eixograph='')
            and rc.visres='Y'
            and r.idrep = ".$_idrep."
            UNION ALL -- Colunas inseridas manualmente
            SELECT '',rc.col,rc.psqkey,rc.psqreq,'','',rc.idrepcol,'',rc.visres,rc.align,rc.grp,rc.ordseq,rc.ordtype,rc.tsum,rc.tavg,rc.hyperlink,rc.entre,rc.inseridomanualmente,rc.calendario,rc.like,rc.in,rc.inval,rc.json,(case when isnull(rc.idrepcol) then 'i' else 'u' end) as act, rc.ordcol, rc.acsum, rc.acavg, ''
            from "._DBCARBON."._repcol rc
            join "._DBCARBON."._rep r on r.idrep=rc.idrep and rc.idrep=".$_idrep." and rc.inseridomanualmente='Y' 
            order by visres, ordcol  ";

    $resfld = d::b()->query($sqlfld) or die("Erro ao recuperar colunas: ".mysql_error(d::b()));

    
   $options="";
   while ($rf = mysql_fetch_array($resfld)) {
       $options.="<option value=".$rf['col'].">".$rf['rotcurto']."</option>";
   }       
   $inputSelect='
    <div style="display:none;  margin-top:15px;" class="_report_amchart_ col-md-6"  id="inputAgrupamento">
            <select style="width:90%;" onchange="mudarAgrupamento(this)">
                <option value="" selected>Agrupamento Gráfico</option>
                '.$options.'
            </select>      
    </div>  
    ';

echo $inputSelect;


?>

<script class="_report_amchart_">
    //https://www.amcharts.com/demos-v3/

    // @TODO: essa função talvez seja utilizada somente p/ gráficos do tipo LINHA e BARRA,
    // então deve-se renomeá-la p/ melhorar identificação
    function getGraphData(){;
        let graphEixoX = <?=json_encode($arrEixoX)?>;
        let graphEixoY = <?=json_encode($arrEixoY)?>;

        let graphData = [];

        for(let i in graphEixoX){

            // Caso o valor do Eixo Y seja um Not a Number, converte-lo p/ Number pra não quebrar o gráfico
            if(isNaN(graphEixoY[i])){
                graphEixoY[i] = graphEixoY[i].replace(/[^\d.,-]/g, '');
            }
            
            // Lista de Pontos X e Y p/ o gráfico
            graphData.push({
                "X": graphEixoX[i],
                "Y": graphEixoY[i]
            });
        }

        return graphData;
    }

    (function(){

        let mainChart = $(`<div id="mainchart">
            <div id="chartdiv" style="display:none;width:100%;height:500px;"></div>
        </div>`);

        let $buttonShowHideGraph = $(`<button class="chartbutton inicial">Mostrar Gráfico</button>`).on('click', function(){
            let vthis = $(this);
            let chartdiv = $("#chartdiv");
            if(vthis.hasClass('inicial')){

                // Carrega gráfico com base no tipo de relatório configurado
                let tipoGrafico = "<?=$tipoGraphRelatorio?>";
                switch(tipoGrafico){
                    case 'LINHA': gerarGraficoMultiLinhas();break;
                    default: break;
                }
                
                vthis.text("Esconder Gráfico");
                chartdiv.show();
                vthis.addClass('carregado').removeClass('inicial');

            }else if(vthis.hasClass('carregado') && chartdiv.is(":visible")){

                // Esconde gráfico quando clicar no botão
                vthis.text("Mostrar Gráfico");
                chartdiv.hide();
            }else if(vthis.hasClass('carregado') && !chartdiv.is(":visible")){

                // Mostra gráfico quando clicar no botão
                vthis.text("Esconder Gráfico");
                chartdiv.show();
            }
        });

        // Cria o botão dinamicamente e o adicionda depois do header do relatório,
        // porém pode ser revisto esse seletor
        mainChart.append($buttonShowHideGraph).insertAfter($(".tbrepheader"));
        if (getUrlParameter("_open_chart") == "Y") {
            $(".chartbutton").click();
        }
    })();
    


    function gerarGraficoMultiLinhas(separator){
        var arr = <?=json_encode($arrayGrafico)?>;
        var eixoX = '<?=$eixoX?>';
        var eixoY = '<?=$eixoY?>';
        

        var categories = [];
        for (let o of arr){
            categories.indexOf(o[separator]) === -1 ? categories.push(o[separator]) : null;
        }

        $('#alert_agrupamento').remove();
        if(categories.length >=10){            
            alert('O gráfico apresenta '+categories.length+' linhas no eixo Y, podendo não ser visualizado corretamente. Para resolver o problema mude o agrupamento ou reduza o período selecionado.')
            $('#inputAgrupamento').append(' <i id="alert_agrupamento" class="fa fa-exclamation-triangle laranja blink" style="margin-left:10px;" aria-hidden="true" title="O gráfico apresenta '+categories.length+' linhas no eixo Y, podendo não ser visualizado corretamente. Para resolver o problema mude o agrupamento ou reduza o período selecionado."></i>')
        }

        var config = {
            graphs : [],
        };
        for(let i in categories){
            let pos = (i % 2 == 0) ? "left" : "right";
            let color = gerar_cor(1);

            config.graphs.push({
                "valueAxis": "v"+i,
                "lineColor": color,
                "bullet": "round",
                "bulletBorderThickness": 1,
                "hideBulletsCount": 30,
                "title": categories[i],
                "balloonText": "<b style='text-align: start;'>[[diferenca]]</b><p style='text-align: start;font-size: 15px;line-heigth'><b>[[value]]</b></p>",
                "lineThickness":2,
                "valueField": categories[i],
                "fillAlphas": 0,
            });
        }

        var arrAux = [];
  
        for(let i in arr){
            arrAux[i] = {[eixoX]: arr[i][eixoX], 'diferenca':arr[i]['diferenca']};
            for(let category of categories){
                arrAux[i][category] = (category == arr[i][separator]) ? arr[i][eixoY] : null;
            }
        }

        console.log(arrAux);

        var arrFinal = [];
        var dateAnt = "";
        var j = -1;
        for(let i in arrAux){
            if(arrAux[i][eixoX] != dateAnt){
                j++;
                dateAnt = arrAux[i][eixoX].split('-');
                if(dateAnt['2'].length >=3){
                    let s= dateAnt['2'].split(' ');
                    dateAnt = s['0']+'/'+dateAnt['1']+'/'+dateAnt['0']+' '+s['1'];
                } else {                    
                    dateAnt = dateAnt['2']+'/'+dateAnt['1']+'/'+dateAnt['0']
                }
                if(arrAux[i]['diferenca'] != undefined){
                    arrFinal[j] = {[eixoX] : dateAnt, 'diferenca':`
                            <span style='display: inline-flex; align-items: center; margin-bottom:4px;text-align:start;'>
                                <img  align='left' vertical-align="top" height='20px' width='20px'  src='entrada.png'/>
                                    <span>`+arrAux[i][`diferenca`][`entrada`]+`</span>
                            </span>
                            <span style='display: inline-flex; align-items: center;text-align:start'>
                                <img  align='left' height='20px' width='20px'  src='saida.png'/>
                                <span>`+arrAux[i][`diferenca`][`saida`]+`</span>
                            </span>
                        `};
                }else{
                    arrFinal[j] = {[eixoX] : dateAnt};
                }
                dateAnt = arrAux[i][eixoX];
                for(let category of categories){
                    arrFinal[j][category] = (arrAux[i][category] > 0) ? parseFloat(arrAux[i][category]) : 0;
                }
            }else{
                for(let category of categories){
                    arrFinal[j][category] += (arrAux[i][category] > 0) ? parseFloat(arrAux[i][category]) : 0;
                }
            }
        }

        console.log(arrFinal);

        arrFinal = arrFinal.map(function(e){
            for(let category of categories){
                e[category] = (e[category] == 0) ? 0 : e[category];
            }
            return e;
        })

        AmCharts.makeChart("chartdiv", {
            "type": "serial",
            "theme": "none",
            "legend": {
                "useGraphSettings": true
            },
            "numberFormatter": {
                "precision": 2,
                "decimalSeparator": ",",
                "thousandsSeparator": "."
            },"valueAxes": [
            {
                "axisAlpha": 1,
                "position": "left",
                //"minimum": -1,
                "labelsEnabled": true,
                "fontSize": 13
            }],
            "dataDateFormat": "DD/MM/YYYY HH:NN:SS",
            "dataProvider": arrFinal,
            "synchronizeGrid":true,
            "graphs": config.graphs,
            "chartScrollbar": {},
            "chartCursor": {
                "cursorPosition": "mouse"
            },
            "categoryField": eixoX,
            "categoryAxis": {
                "parseDates": false,
                "offset":5,
                "axisColor": "#DADADA",
                "minorGridEnabled": true,                
                "labelRotation": 30
            },
            "export": {
                "enabled": true,
                "position": "bottom-right"
            }
        });
    }


function gerar_cor(opacidade = 1) {

let r = parseInt(Math.random() * 100);

let g = parseInt(Math.random() * 255);

let b = parseInt(Math.random() * 255);

return `rgba(${r}, ${g}, ${b}, ${opacidade})`;

}

function adicionaZero(numero){
    if (numero <= 9) 
        return "0" + numero;
    else
        return numero; 
}
</script>