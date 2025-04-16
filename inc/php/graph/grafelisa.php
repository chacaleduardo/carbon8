<?
/*
function geragrafelisa($arrelisa){

include_once("jpgraph.php");
include_once("jpgraph_bar.php");

$i = 0;
$arrtmpgmt = array();
$arrtmppadrao = array();

while(list($grupo,$quant)=each($arrelisa)){

	$arrtmpgmt[$i] = number_format($vgmt["gmt"], 2, '.','');
	}
	$arrtmppadrao[$i] = number_format($vgmt["padrao"], 2, '.','');
	$i++;
}

print_r($arrtmpidade);
print_r($arrtmpgmt);
print_r($arrtmppadrao);



//$data1y=$arrpadraogmt;
$data1y  = $arrtmppadrao;
$data2y  = $arrtmpgmt;
$xlabels = $arrtmpidade;

// Create the graph. These two calls are always required
$graph = new Graph(600,250);
$graph->SetScale("textlin");
//$graph->SetColor();//Cor da area de plotagem dos graficos
$graph->SetShadow(true,3,array(215,215,215));
$graph->img->SetMargin(50,30,20,40);
$graph->SetMarginColor(array(237,237,237));
$graph->SetFrameBevel(1,true,"gray");

// Create the bar plots

$arrbarplot = array();

if(!empty($data1y)){
	$b1plot = new BarPlot($data1y);
	$b1plot->SetFillColor("dodgerblue3");
	$arrbarplot[0] = $b1plot; // Plotar somente se contiver valores
}
//$b1plot->value->Show();
if(!empty($data2y)){
	$b2plot = new BarPlot($data2y);
	$b2plot->SetFillColor("goldenrod1");
	$b2plot->value->Show();
	$arrbarplot[1] = $b2plot; // Plotar somente se contiver valores
}
//$b2plot->value->SetAngle(45);

if(empty($arrbarplot)){
	return "../img/falhagrafico1.gif";
}

$graph->xaxis->SetTickLabels($xlabels);
//$graph->xaxis->SetTextLabelInterval(2); //Espcamento dos labels do axis X
//$graph->yaxis->scale->SetGrace(6); //Reduz a altura do eixo Y

// Create the grouped bar plot
$gbplot = new GroupBarPlot($arrbarplot);
$gbplot->SetWidth(0.7);

// ...and add it to the graPH
$graph->Add($gbplot);

$graph->title->Set("Resultado GMT");
$graph->xaxis->title->Set("Semanas");
$graph->yaxis->title->Set("GMT");
$graph->yaxis->SetLabelMargin(1);
$graph->yaxis->SetTitleMargin(30);

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

// Display the graph
//$graph->Stroke();

$urlimg = "../tmp/graph/" .  session_id() . "_" . md5(uniqid(time())) . ".png";

$graph->Stroke($urlimg);

return $urlimg;
//echo "../tmp";
}
*/


function geragrafelisa($arrgrafelisa){

include_once("jpgraph.php");
include_once("jpgraph_bar.php");

$arrtmp18grupos = array();
$xlabels = array();

for ( $ii=0; $ii <= 18; $ii++){
	$arrtmp18grupos[$ii] = 0;
	$xlabels[$ii] = $ii;
}

//print_r($arrtmp18grupos);

while(list($grupo,$quant)=each($arrgrafelisa)){

	$arrtmp18grupos[$grupo] = $quant;
	
}

//print_r($arrtmp18grupos);print_r(	$xlabels);
//die;//

$data1y  = $arrtmp18grupos;
/*$data2y  = $arrtmpgmt;
$xlabels = $arrtmpidade;*/

// Create the graph. These two calls are always required
//$graph = new Graph(600,250);
$graph = new Graph(350,185);
$graph->SetScale("textlin");


$graph->img->SetMargin(50,30,20,40);
$graph->SetMarginColor('white');
$graph->SetFrame(true,'silver',1);


$arrbarplot = array();

if(!empty($data1y)){
	$b1plot = new BarPlot($data1y);
	$b1plot->SetFillColor("#00ffff");
	$b1plot->value->SetFormat('%d');
	$b1plot->value->SetFont(FF_ARIAL,FS_NORMAL,7);
	$b1plot->value->SetColor("black","darkred");
	
	$b1plot->value->Show();
	$b1plot->value->HideZero();
 
	$arrbarplot[0] = $b1plot; // Plotar somente se contiver valores
}

if(empty($arrbarplot)){
	return "../img/falhagrafico1.gif";
}

$graph->xaxis->SetTickLabels($xlabels);


//$graph->yaxis->scale->SetGrace(100); //Reduz a altura do eixo Y

// Create the grouped bar plot
$gbplot = new GroupBarPlot($arrbarplot);
$gbplot->SetWidth(0.6);

// ...and add it to the graPH
$graph->Add($gbplot);

$graph->title->Set("Resultado Elisa Atual");
$graph->xaxis->title->Set("Group");
//$graph->yaxis->title->Set("Valor");
//$graph->yaxis->SetLabelMargin(5);
//$graph->yaxis->SetTitleMargin(1);
$graph->xaxis->SetColor('gray3'); 
$graph->yaxis->SetColor('gray3'); 
$graph->yaxis->scale->SetGrace(10);

$graph->title->SetFont(FF_FONT1,FS_BOLD);

//$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

// Display the graph
//$graph->Stroke();

$urlimg = "../tmp/graph/" .  session_id() . "_" . md5(uniqid(time())) . ".png";

$graph->SetMargin(25,10,10,10);

$graph->Stroke($urlimg);

return $urlimg;

}


?>


