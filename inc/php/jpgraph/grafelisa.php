<?
DEFINE("TTF_DIR", "../inc/fonts/");

function geragrafelisa1($arrgrafelisa)
{

	include_once("jpgraph.php");
	include_once("jpgraph_bar.php");

	$arrtmp18grupos = array();
	$xlabels = array();

	for ($ii = 0; $ii <= 18; $ii++) {
		$arrtmp18grupos[$ii] = 0;
		$xlabels[$ii] = $ii;
	}

	//print_r($arrtmp18grupos);

	while (list($grupo, $quant) = each($arrgrafelisa)) {

		$arrtmp18grupos[$grupo] = $quant;
	}

	$data1y  = $arrtmp18grupos;

	// Create the graph. These two calls are always required
	$graph = new Graph(500, 150);
	$graph->SetScale("textlin");


	$graph->img->SetMargin(50, 30, 20, 40);
	$graph->SetMarginColor('white');
	$graph->SetFrame(true, 'silver', 1);

	$arrbarplot = array();

	if (!empty($data1y)) {
		$b1plot = new BarPlot($data1y);
		$b1plot->SetFillColor("#00ffff");
		$b1plot->value->SetFormat('%d');
		$b1plot->value->SetFont(FF_ARIAL, FS_NORMAL, 7);
		$b1plot->value->SetColor("black", "darkred");

		$b1plot->value->Show();
		$b1plot->value->HideZero();

		$arrbarplot[0] = $b1plot; // Plotar somente se contiver valores
	}

	if (empty($arrbarplot)) {
		return "../img/falhagrafico1.gif";
	}

	$graph->xaxis->SetTickLabels($xlabels);

	// Create the grouped bar plot
	$gbplot = new GroupBarPlot($arrbarplot);
	$gbplot->SetWidth(0.6);

	// ...and add it to the graPH
	$graph->Add($gbplot);

	$graph->title->Set("Resultado Elisa Atual");
	$graph->xaxis->title->Set("Group");
	$graph->xaxis->SetColor('gray3');
	$graph->yaxis->SetColor('gray3');
	$graph->yaxis->scale->SetGrace(10);

	$graph->title->SetFont(FF_FONT1, FS_BOLD);

	$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

	$urlimg = "../tmp/graph/" .  session_id() . "_" . md5(uniqid(time())) . ".png";

	$graph->SetMargin(25, 10, 10, 10);

	$graph->Stroke($urlimg);

	return $urlimg;
}

function geragrafelisa($arrgrafelisa)
{

	include_once("jpgraph.php");
	include_once("jpgraph_bar.php");

	$arrtmp18grupos = array();
	$xlabels = array();

	for ($ii = 0; $ii <= 18; $ii++) {
		$arrtmp18grupos[$ii] = 0;
		$xlabels[$ii] = $ii;
	}

	while (list($grupo, $quant) = each($arrgrafelisa)) {

		$arrtmp18grupos[$grupo] = $quant;
	}

	//print_r($arrtmp18grupos);print_r(	$xlabels);
	//die;//

	$data1y  = $arrtmp18grupos;
	/*$data2y  = $arrtmpgmt;
	$xlabels = $arrtmpidade;*/

	// Create the graph. These two calls are always required
	//$graph = new Graph(600,250);
	$graph = new Graph(350, 185);
	$graph->SetScale("textlin");


	$graph->img->SetMargin(50, 30, 20, 40);
	$graph->SetMarginColor('white');
	$graph->SetFrame(true, 'silver', 1);


	$arrbarplot = array();

	if (!empty($data1y)) {
		$b1plot = new BarPlot($data1y);
		$b1plot->SetFillColor("#00ffff");
		$b1plot->value->SetFormat('%d');
		$b1plot->value->SetFont(FF_ARIAL, FS_NORMAL, 7);
		$b1plot->value->SetColor("black", "darkred");

		$b1plot->value->Show();
		$b1plot->value->HideZero();

		$arrbarplot[0] = $b1plot; // Plotar somente se contiver valores
	}

	if (empty($arrbarplot)) {
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
	$graph->xaxis->SetColor('gray3');
	$graph->yaxis->SetColor('gray3');
	$graph->yaxis->scale->SetGrace(10);

	$graph->title->SetFont(FF_FONT1, FS_BOLD);

	//$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

	// Display the graph
	//$graph->Stroke();

	$urlimg = "../tmp/graph/" .  session_id() . "_" . md5(uniqid(time())) . ".png";

	$graph->SetMargin(25, 10, 10, 10);

	$graph->Stroke($urlimg);

	return $urlimg;
}



function geragrafelisa4($arrgrafelisa)
{

	include_once("jpgraph.php");
	include_once("jpgraph_bar.php");

	$arrtmp18grupos = array();
	$xlabels = array();

	for ($ii = 0; $ii <= 4; $ii++) {
		$arrtmp18grupos[$ii] = 0;
		$xlabels[$ii] = $ii;
	}

	//print_r($arrtmp18grupos);

	while (list($grupo, $quant) = each($arrgrafelisa)) {

		$arrtmp18grupos[$grupo] = $quant;
	}

	//print_r($arrtmp18grupos);print_r(	$xlabels);
	//die;//

	$data1y  = $arrtmp18grupos;
	/*$data2y  = $arrtmpgmt;
$xlabels = $arrtmpidade;*/

	// Create the graph. These two calls are always required
	//$graph = new Graph(600,250);
	$graph = new Graph(350, 185);
	$graph->SetScale("textlin");


	$graph->img->SetMargin(50, 30, 20, 40);
	$graph->SetMarginColor('white');
	$graph->SetFrame(true, 'silver', 1);


	$arrbarplot = array();

	if (!empty($data1y)) {
		$b1plot = new BarPlot($data1y);
		$b1plot->SetFillColor("#00ffff");
		$b1plot->value->SetFormat('%d');
		$b1plot->value->SetFont(FF_ARIAL, FS_NORMAL, 7);
		$b1plot->value->SetColor("black", "darkred");

		$b1plot->value->Show();
		$b1plot->value->HideZero();

		$arrbarplot[0] = $b1plot; // Plotar somente se contiver valores
	}

	if (empty($arrbarplot)) {
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

	$graph->title->SetFont(FF_FONT1, FS_BOLD);

	//$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

	// Display the graph
	//$graph->Stroke();

	$urlimg = "../tmp/graph/" .  session_id() . "_" . md5(uniqid(time())) . ".png";

	$graph->SetMargin(25, 10, 10, 10);

	$graph->Stroke($urlimg);

	return $urlimg;
}


function geragrafelisaSP($arrgrafelisa)
{

	include_once("jpgraph.php");
	include_once("jpgraph_bar.php");

	$arrtmp18grupos = array();
	$xlabels = array();

	for ($ii = 0; $ii <= 16; $ii++) {
		$arrtmp18grupos[$ii] = 0;
		$xlabels[$ii] = $ii / 10;
		if ($ii == 16) {
			$xlabels[$ii] = ' ' . $xlabels[$ii] . '+';
		}
	}

	//print_r($arrtmp18grupos);

	while (list($grupo, $quant) = each($arrgrafelisa)) {

		$arrtmp18grupos[$grupo] = $quant;
	}

	//print_r($arrtmp18grupos);print_r(	$xlabels);
	//die;//

	$data1y  = $arrtmp18grupos;
	/*$data2y  = $arrtmpgmt;
$xlabels = $arrtmpidade;*/

	// Create the graph. These two calls are always required
	//$graph = new Graph(600,250);
	$graph = new Graph(350, 185);
	$graph->SetScale("textlin");


	$graph->img->SetMargin(50, 30, 20, 40);
	$graph->SetMarginColor('white');
	$graph->SetFrame(true, 'silver', 1);


	$arrbarplot = array();

	if (!empty($data1y)) {
		$b1plot = new BarPlot($data1y);
		$b1plot->SetFillColor("#00ffff");
		$b1plot->value->SetFormat('%d');
		$b1plot->value->SetFont(FF_ARIAL, FS_NORMAL, 6);
		$b1plot->value->SetColor("black", "darkred");

		$b1plot->value->Show();
		$b1plot->value->HideZero();

		$arrbarplot[0] = $b1plot; // Plotar somente se contiver valores
	}

	if (empty($arrbarplot)) {
		return "../img/falhagrafico1.gif";
	}

	$graph->xaxis->SetTickLabels($xlabels);


	//$graph->yaxis->scale->SetGrace(100); //Reduz a altura do eixo Y

	// Create the grouped bar plot
	$gbplot = new GroupBarPlot($arrbarplot);
	$gbplot->SetWidth(0.6);

	// ...and add it to the graPH
	$graph->Add($gbplot);

	$graph->title->Set("RESULTADO ELISA ATUAL");
	$graph->xaxis->title->Set("S/P");
	//$graph->yaxis->title->Set("Valor");
	//$graph->yaxis->SetLabelMargin(5);
	//$graph->yaxis->SetTitleMargin(1);
	$graph->xaxis->SetColor('gray3');
	$graph->yaxis->SetColor('gray3');
	$graph->yaxis->scale->SetGrace(10);

	$graph->title->SetFont(FF_FONT1, FS_BOLD);

	//$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

	// Display the graph
	//$graph->Stroke();

	$urlimg = "../tmp/graph/" .  session_id() . "_" . md5(uniqid(time())) . ".png";

	$graph->SetMargin(25, 10, 10, 10);

	$graph->Stroke($urlimg);

	return $urlimg;
}


function geragrafelisaRESULT($arrgrafelisa)
{

	include_once("jpgraph.php");
	include_once("jpgraph_bar.php");

	$arrtmp18grupos = array();
	$xlabels = array();

	$arrtmp18grupos[0] = 0;
	$xlabels[0] = 'Pos!';
	$arrtmp18grupos[1] = 0;
	$xlabels[1] = 'Neg';
	//print_r($arrtmp18grupos);

	while (list($grupo, $quant) = each($arrgrafelisa)) {

		$arrtmp18grupos[$grupo] = $quant;
	}

	//print_r($arrtmp18grupos);print_r(	$xlabels);
	//die;//

	$data1y  = $arrtmp18grupos;
	/*$data2y  = $arrtmpgmt;
$xlabels = $arrtmpidade;*/

	// Create the graph. These two calls are always required
	//$graph = new Graph(600,250);
	$graph = new Graph(350, 185);
	$graph->SetScale("textlin");


	$graph->img->SetMargin(50, 30, 20, 40);
	$graph->SetMarginColor('white');
	$graph->SetFrame(true, 'silver', 1);


	$arrbarplot = array();

	if (!empty($data1y)) {
		$b1plot = new BarPlot($data1y);
		$b1plot->SetFillColor("#00ffff");
		$b1plot->value->SetFormat('%d');
		$b1plot->value->SetFont(FF_ARIAL, FS_NORMAL, 6);
		$b1plot->value->SetColor("black", "darkred");

		$b1plot->value->Show();
		$b1plot->value->HideZero();

		$arrbarplot[0] = $b1plot; // Plotar somente se contiver valores
	}

	if (empty($arrbarplot)) {
		return "../img/falhagrafico1.gif";
	}

	$graph->xaxis->SetTickLabels($xlabels);


	//$graph->yaxis->scale->SetGrace(100); //Reduz a altura do eixo Y

	// Create the grouped bar plot
	$gbplot = new GroupBarPlot($arrbarplot);
	$gbplot->SetWidth(0.6);

	// ...and add it to the graPH
	$graph->Add($gbplot);

	$graph->title->Set("RESULTADO ELISA ATUAL");
	$graph->xaxis->title->Set("RESULT");
	//$graph->yaxis->title->Set("Valor");
	//$graph->yaxis->SetLabelMargin(5);
	//$graph->yaxis->SetTitleMargin(1);
	$graph->xaxis->SetColor('gray3');
	$graph->yaxis->SetColor('gray3');
	$graph->yaxis->scale->SetGrace(10);

	$graph->title->SetFont(FF_FONT1, FS_BOLD);

	//$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

	// Display the graph
	//$graph->Stroke();

	$urlimg = "../tmp/graph/" .  session_id() . "_" . md5(uniqid(time())) . ".png";

	$graph->SetMargin(25, 10, 10, 10);

	$graph->Stroke($urlimg);

	return $urlimg;
}

function geragrafelisaRESULTSUS($arrgrafelisa)
{

	include_once("jpgraph.php");
	include_once("jpgraph_bar.php");

	$arrtmp18grupos = array();
	$xlabels = array();

	$arrtmp18grupos[0] = 0;
	$xlabels[0] = 'Pos!';
	$arrtmp18grupos[1] = 0;
	$xlabels[1] = 'Neg';
	$arrtmp18grupos[2] = 0;
	$xlabels[2] = 'Sus*';
	//print_r($arrtmp18grupos);

	while (list($grupo, $quant) = each($arrgrafelisa)) {

		$arrtmp18grupos[$grupo] = $quant;
	}

	//print_r($arrtmp18grupos);print_r(	$xlabels);
	//die;//

	$data1y  = $arrtmp18grupos;
	/*$data2y  = $arrtmpgmt;
$xlabels = $arrtmpidade;*/

	// Create the graph. These two calls are always required
	//$graph = new Graph(600,250);
	$graph = new Graph(350, 185);
	$graph->SetScale("textlin");


	$graph->img->SetMargin(50, 30, 20, 40);
	$graph->SetMarginColor('white');
	$graph->SetFrame(true, 'silver', 1);


	$arrbarplot = array();

	if (!empty($data1y)) {
		$b1plot = new BarPlot($data1y);
		$b1plot->SetFillColor("#00ffff");
		$b1plot->value->SetFormat('%d');
		$b1plot->value->SetFont(FF_ARIAL, FS_NORMAL, 6);
		$b1plot->value->SetColor("black", "darkred");

		$b1plot->value->Show();
		$b1plot->value->HideZero();

		$arrbarplot[0] = $b1plot; // Plotar somente se contiver valores
	}

	if (empty($arrbarplot)) {
		return "../img/falhagrafico1.gif";
	}

	$graph->xaxis->SetTickLabels($xlabels);


	//$graph->yaxis->scale->SetGrace(100); //Reduz a altura do eixo Y

	// Create the grouped bar plot
	$gbplot = new GroupBarPlot($arrbarplot);
	$gbplot->SetWidth(0.6);

	// ...and add it to the graPH
	$graph->Add($gbplot);

	$graph->title->Set("RESULTADO ELISA ATUAL");
	$graph->xaxis->title->Set("RESULT");
	//$graph->yaxis->title->Set("Valor");
	//$graph->yaxis->SetLabelMargin(5);
	//$graph->yaxis->SetTitleMargin(1);
	$graph->xaxis->SetColor('gray3');
	$graph->yaxis->SetColor('gray3');
	$graph->yaxis->scale->SetGrace(10);

	$graph->title->SetFont(FF_FONT1, FS_BOLD);

	//$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

	// Display the graph
	//$graph->Stroke();

	$urlimg = "../tmp/graph/" .  session_id() . "_" . md5(uniqid(time())) . ".png";

	$graph->SetMargin(25, 10, 10, 10);

	$graph->Stroke($urlimg);

	return $urlimg;
}
