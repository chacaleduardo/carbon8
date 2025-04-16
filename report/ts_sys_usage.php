<?php
#versao para Plotly: https://plotly.com/javascript/line-charts/
require_once "../inc/php/redists/vendor/autoload.php";

error_reporting(E_ALL ^E_NOTICE ^E_WARNING);
use Palicao\PhpRedisTimeSeries\TimeSeries;
use Palicao\PhpRedisTimeSeries\Client\RedisClient;
use Palicao\PhpRedisTimeSeries\Client\RedisConnectionParams;
use Palicao\PhpRedisTimeSeries\AggregationRule;
use Palicao\PhpRedisTimeSeries\Filter;

//Expor a conexao para comandos livres
$rcli = new RedisClient(
	new Redis(),
	new RedisConnectionParams("192.168.0.1", "6380")
);
//Biblioteca do modulo timeseries
$ts = new TimeSeries(
	$rcli
);

$adata = [];
$ips = []; //Nomes das series (legenda grafico)
$ipsresumo=array();

$dini=new DateTimeImmutable();
$dfim=new DateTimeImmutable();

$intervalo=empty($_GET["interval"])?'30 minutes':$_GET["interval"];
$aggreg=empty($_GET["aggregation"])?100:(int)$_GET["aggregation"];

$dini=$dini->sub(DateInterval::createFromDateString($intervalo));

//Executa mrange por label o=cb (origem: carbon). a sigla pequena foi usada para economizar memoria
$samples = $ts->multiRangeWithLabels(
	new Filter('o', 'cb')
	,$dini
	,$dfim
	,null
	,new AggregationRule('sum', $aggreg*1000)
);

//print_r($samples);//die();

foreach ($samples as $i => $sample) {

		//Chave da amostra (IP ou string contendo o script)
		$ip = explode(":", $sample->getKey())[3];

		//Captura os labels associados ao datapoint
		$labels=[];
		foreach ($sample->getLabels() as $label) {
			$l=$label->getKey();
			$v=$label->getValue();
			$labels[$l]=$v;
		}
		
		//Value: coleta o valor da(s) amostra(s)
		$vlr=$sample->getValue();
		
		//$kf
		$ipsresumo[$ip]["x"][]=$sample->getDateTime()->setTimezone(new DateTimeZone('-0300'))->format('Y-m-d H:i:s');
		$ipsresumo[$ip]["y"][]=$vlr;
		$ipsresumo[$ip]["iqueries"]+=$vlr;
		$ipsresumo[$ip]["labels"]=$labels;

}

//Loop para montar array no formato final do plotly
$adata=[];
foreach ($ipsresumo as $ip => $vlr) {
	//Ignorar valores inferiores ao corte
	if(!empty($_GET["maiorque"]) and (int)$vlr["iqueries"]<=(int)$_GET["maiorque"]){
		continue;
	}

	//Mostra no grafico
	$adata[]=array(
		"x"=>$vlr["x"]
		,"y"=>$vlr["y"]
		//,"type"=>"scatter"
		,"mode"=>'lines'
		,"name"=>$ip." <b>".$vlr["iqueries"]."</b>"
		,"iqueries"=>$vlr["iqueries"]
		//,"visible" =>  "legendonly"//Iniciar com a serie oculta
		//,"hoverinfo"=> 'skip' //https://plotly.com/javascript/hover-text-and-formatting/
		// ,"hovertext"=>"bla"
		,"hovertemplate"=>
			"<b>".$ip
			."</b><br>"
			."<br>Queries: %{y}"
			."<br>Mod: ".$vlr["labels"]["m"]
			."<br>Usr: ".$vlr["labels"]["u"]
			."<br>Hora: %{x}"
			."<br><extra></extra>"
	);
}

//Ordenar desc pelo "name", que eh a chave (ip) no redis. O usort foi usado para nao desordenar a key
usort($adata, function ($a, $b) {
	if ($a["iqueries"] == $b["iqueries"]){
		return 0;
	}
	return ($a["iqueries"] > $b["iqueries"]) ? -1 : 1;
});


strtoupper(explode(".",basename($_SERVER["SCRIPT_FILENAME"]))[0])

//echo(json_encode($adata, JSON_PRETTY_PRINT));

?>

<head>
	<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
	<script src="https://cdn.plot.ly/plotly-locale-pt-br-latest.js"></script>
</head>
<body>
<div id="myDiv"></div>

<script>

var data = <?=json_encode($adata)?>;


var layout = {
	//showlegend: true,
//	xaxis:{hoverformat: '.2f'},

	legend: {"orientation": "h", y: -0.3},//https://plotly.com/javascript/hover-text-and-formatting/#advanced-hovertemplate
	hovermode: 'closest',
	title: "Queries MySQL X Cliente"
};
Plotly.newPlot('myDiv', data, layout, {locale: "pt-BR", displaylogo: false});

</script>
</body>
