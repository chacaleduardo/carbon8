<?

function geragrafelisagmt($arrgrafelisagmt){

if(!empty($arrgrafelisagmt)){

	include_once("jpgraph.php");
	include_once("jpgraph_bar.php");

	$xlabels = array();
	$i = 0;
	$arrgmt = array();

	while(list($idade,$gmt)=each($arrgrafelisagmt)){
	
		$arrgmt[$i] = $gmt;
		$xlabels[$i] = $idade;
		$i++;
	}
	//print_r($arrgrafelisagmt);
	//print_r($arrgmt);
	//print_r($xlabels);
	//die;

	$data1y  = $arrgmt;
	/*$data2y  = $arrtmpgmt;
	$xlabels = $arrtmpidade;*/

	// Create the graph. These two calls are always required

	$graph = new Graph(350,185);
	$graph->SetScale("textlin");


	$graph->img->SetMargin(50,30,20,40);
	$graph->SetMarginColor('white');
	$graph->SetFrame(true,'silver',1);

	$arrbarplot = array();

	if(!empty($data1y)){
		$b1plot = new BarPlot($data1y);
		$b1plot->SetFillColor("#ffff00");
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
	//$gbplot->SetWidth(0.6);

	// ...and add it to the graPH
	$graph->Add($gbplot);

	$graph->title->Set("Historico GMT Elisa");
	$graph->xaxis->title->Set("Semanas");
	$graph->yaxis->scale->SetGrace(10);
	$graph->yaxis->title->Set("GMT");
	$graph->yaxis->SetLabelMargin(3);
	$graph->yaxis->SetTitleMargin(35);
	$graph->xaxis->SetColor('gray3'); 
	$graph->yaxis->SetColor('gray3'); 

	$graph->title->SetFont(FF_FONT1,FS_BOLD);

	//$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

	// Display the graph
	//$graph->Stroke();

	$urlimg = "../tmp/graph/" .  session_id() . "_" . md5(uniqid(time())) . ".png";

	//$graph->SetMargin(25,10);

	$graph->Stroke($urlimg);

	return $urlimg;
	//echo "../tmp";
}//if(!empty($arrgrafelisagmt)){
}
?>
