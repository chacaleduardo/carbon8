<?
include_once("../inc/php/validaacesso.php");
// CONTROLLERS
require_once(__DIR__."/../form/controllers/menurelatorio_controller.php");
require_once(__DIR__."/../form/controllers/empresa_controller.php");
baseToGet($_GET["_filtros"]);

if(!empty($_GET["reportexport"])){
	ob_start();//não envia nada para o browser antes do termino do processamento
}

$verificarLp = MenuRelatorioController::verificarLpPorIdLpEIdRep(getModsUsr("LPS"), $_GET["_idrep"]);
if(!$verificarLp){
	die('<div>Você não Possui permissão para acessar esse Relatório</div>');
}

$_idrep = $_GET["_idrep"];

if ($_GET["relatorio"]){
	$_idrep = $_GET["relatorio"];
}

if ($_idrep == 172 || $_idrep == 94 ||  $_idrep == 18 || $_idrep == 127 ||  $_idrep == 22 || $_idrep == 81 ||  $_idrep == 149){
	
	if(array_key_exists("rhfolha", getModsUsr("MODULOS")) != 1){
		die();
	}
	
}

if(empty($_idrep)){
	die("Relat&oacute;rio n&atilde;o informado!");
}

if ($_idrep == 21){
	MenuRelatorioController::alterarSQLMode('NO_UNSIGNED_SUBTRACTION');
}
//Recupera a definicao das colunas da view ou table default da pagina
$arrRep=MenuRelatorioController::buscarConfiguracaoRelatorioPorIdRep($_idrep);
//Facilita a utilização do array
$arrRep=$arrRep[$_idrep];

//print_r($arrRep);
//die();
$_rep = $arrRep["rep"];
$_header = $arrRep["header"];
$_footer = $arrRep["footer"];
$_showfilters = $arrRep["showfilters"];
$_tab = $arrRep["tab"];
$_newgrouppagebreak = $arrRep["newgrouppagebreak"];
$_pbauto = $arrRep["pbauto"];
$_showtotalcounter = $arrRep["showtotalcounter"];
$_compl = $arrRep["compl"];
$_descr = $arrRep["descr"];
$_rodape = $arrRep["rodape"];
$_chavefts = $arrRep["chavefts"];
$_tabfull = $arrRep["tabfull"];
?>
<html>
<head>
	<title><?=$_rep.' '.$_GET["_fds"]?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<link href="../inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />
	<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
	<style type="text/css">
		table { page-break-inside:auto; width:100% }
			tr    { page-break-inside:avoid; page-break-after:auto }
			thead { display:table-header-group }
			tfoot { display:table-footer-group }
			@media print
		{    
			.no-print, .no-print *
			{
				display: none !important;
			}
			footer {
			position: fixed;
			bottom: 0;
		}
		}
		footer {
		font-size: 9px;
		color: #f00;
		text-align: center;
		}
		td table{
			font-size: 10px !important;
			border: 1px solid;
			border-collapse: inherit;
		}
		@media print {
		body {-webkit-print-color-adjust: exact;}
		}
	</style>
</head>
<body>
<?

if (!empty($_GET)){

	$_sqlwhere = " where ";
	$_and = "";
	$_iclausulas = 0;
	$arrNomeXml = array();
	$arrXml = array();
	//Loop nos parâmetros GET para montar as cláusulas where
	require_once(__DIR__."/scripts/_8repprint_montarclausulawhere.php");

	$_sqldata = '';	
	if(!empty($arrRep["_datas"]))
	{
		if ($_REQUEST['_fds'])
		{
			//echo 'aqui';
			$data = explode('-',$_REQUEST['_fds']);
			$data1 = $data[0];
			$data2 = $data[1];
			if (MenuRelatorioController::verificarData($data2)){
				$data2 = $data2.' 23:59:59';
			}
			
			if ($data1 and $data2)
			{
				foreach ($arrRep["_datas"] as $ko => $vo)
				{
					if($_GET['tiponf']=='C'){
						$vo='prazo';
					} else if($_GET['tiponf']=='V'){
						$vo='dtemissao';
					}
					$_sqldata .= $_or . "(" . $vo . " between " . evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2) . ")";	
					$_or = " or ";
				}
			}

			$_sqldata = ' and ('.$_sqldata.') ';	
		}
	}
				
			
	// Definir Preferencias do usuario
	require_once(__DIR__."/scripts/_8repprint_ajustaprefusuario.php");

	//Abre variavel de sessao para que ela possa ser acessada pelo modulo de interceptacao de eventos
	//Isto permitira saber se existe clausula where ou nao
	$_SESSION["SEARCH"]["CLAUSULAS"] = (string)$_iclausulas;
	
	if($_iclausulas > 0){
		$_sqlresultado = getDbTabela($_tab).".". $_tab ." ".$_sqlwhere;
		if(empty($_GET['idempresa'])){
			$_sqlresultado .= " and idempresa = ".cb::idempresa();
		}
		
	}else{
		$_sqlresultado = getDbTabela($_tab).".". $_tab." a";
		if(empty($_GET['idempresa'])){
			$_sqlresultado .= " where idempresa = ".cb::idempresa();
		}
	}
	
	if (trim($_compl) != ''){
		$_sqlresultado .= ' '.trim($_compl);
	}

	//--------------------- Validação para filtro com Plantel - LTM (28-07-2020 - 363014) ----------------
	$arrFiltros = retarraytabdef($_tab); 
	require_once(__DIR__."/scripts/_8repprint_validafiltroplantel.php");

	// RETRINGIR CONSULTA A UNIDADE MARCADA NA LP-------------------------------------------------------------
	$lps=getModsUsr("LPS");
	$lpRep = MenuRelatorioController::buscarLpRepPorIdRepEIdLps($_idrep, $lps);
	
	require_once(__DIR__."/scripts/_8repprint_164_restringirconsultaaunidademarcadanalp.php");

	$strselectfields = "";
	$strord = "";
	$strvirg = "";

	//Concatenar campos para o select
	require_once(__DIR__."/scripts/_8repprint_145_concatenarcamposelect.php");

	//Concatenar clausulas para Order By
	require_once(__DIR__."/scripts/_8repprint_concatenarclausulaorderby.php");

	$strvirg = "";
	
	//Concatenar clausulas para GROUP BY
	require_once(__DIR__."/scripts/_8repprint_ordenarpelocampoordseq.php");

	/****************************************************************************
	 * CONCATENACAO PRINCIPAL DO SELECT A SER EXECUTADO PARA O RELATORIO        *
	 ****************************************************************************/
	$relatorios = MenuRelatorioController::buscarRelatorioDinamico($strselectfields, ($_sqlresultado.$_sqldata.$_and_idempresa.$str_fts.$strgrp.$strord));
	if ($relatorios === false) {
	    die('<b>Falha na execucao da Consulta para o Report:</b> ' . mysql_error() . "<br>" . $_sqlresultado);
	}

	$_arrtab = retarraytabdef($_tab);
	//print_r($_ arrtab); die();

	$_i = 0;
	$_numcolunas = count($relatorios[0]);
	$_ipagpsqres = count($relatorios);
	$strs = "Nenhum Registro encontrado";

	if($_ipagpsqres==1){
		$strs = $_ipagpsqres." Registro encontrado";
	}elseif($_ipagpsqres>1){
		$strs = $_ipagpsqres." Registros encontrados";
	}

	$_nomeimpressao = "[".md5(date('dmYHis'))."] gerada em [".date(" d/m/Y H:i:s")."]";
	
	// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
	$empresa = EmpresaController::buscarEmpresaPorIdEmpresa($_GET['_idempresa'] ?? cb::idempresa());
	$figurarelatorio = $empresa["logosis"];
	
?>
	<div style="display:none; text-align: left; font-size: 9px;" class="n_linhas"><span  id="nlinha"><?=$strs?></span></div>

	<table class="tbrepheader">
	<tr>
		<td rowspan="3" style="width:50px;"><img style="width:100%;" src="<?=$figurarelatorio?>"></td>
		<td class="header"><? //=$_header?></td>
		<td><a class="btbr20 no-print" id="downloadXML" href="<?=$_SERVER['REQUEST_URI']?>&reportexport=zip">Download XML .zip</a></td>
	</tr>
	<tr>
		<td class="subheader"><h2><?=($_rep);?></h2>
		(<?=$strs?>)</td>
	</tr>
	</table>
	<br>
	<fieldset class="fldsheader">
	  <legend>Início da Impressão <?=$_nomeimpressao?></legend>
	</fieldset>
<?
	/*
	 * MONTA O CABECALHO
	 */
	$strtabheader = "\n<thead><tr class='header'>";
	//coloca um contador numerico do lado esquerdo da tabela, isto é repetido também na montagem de cada linha da tabela
	if($_showtotalcounter == "Y"){
		$strtabheader .= "<td class='tdcounter'></td>";
	}
	
	foreach(array_keys($relatorios[0]) as $key => $coluna)
	{	
		if($arrRep["_filtros"][$arrRep["_colvisiveis"][$key+1]]["visres"] == 'Y'){

			if (strpos(strtolower($arrRep["_filtros"][$arrRep["_colvisiveis"][$key+1]]["rotulo"]), ' as ') !== false) {
				//echo 'aqio';
				$val = explode(' as ',strtolower($arrRep["_filtros"][$arrRep["_colvisiveis"][$key+1]]["rotulo"]));
				$arrRep["_filtros"][$arrRep["_colvisiveis"][$key+1]]["rotulo"] = $val[1];
			}
	    	
			$strtabheader .= "<td class='header' id='".MenuRelatorioController::urlAmigavel(str_replace('`','',$arrRep["_filtros"][$arrRep["_colvisiveis"][$key+1]]["rotulo"]))."' style=\"text-align:".$arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["align"]."\">" . str_replace('`','',$arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["rotulo"])."</td>";
	    }	
		
	    $_i++;
	}//while ($_i < $_numcolunas) {
	
	$strtabheader .= "</tr></thead><tbody>";

	/*
	 * Variaveis para cabecalho do report
	 */
	$strpagini = "\n<fieldset class='fldsheader'><legend>Impressão ".$_nomeimpressao."</legend></fieldset>";
	$strtabini = "\n<table class='normal'>";
	$strtabheader = $strtabheader;

	/*
	 * MONTA A TABELA
	 */
	$_ilinha = 0;//armazena o ttotal de registros
	$_ilinhaquebra = 0;//armazena parialmente o numero de registros se houver quebra automatica configurada
	$strnewpage = "<span class='newreppage'></span>";

	foreach($relatorios as $_row)
	{
		$_ilinha++;
		$_i = 0;

		//verifica se o parametro de quebra automatica esta configurado. caso negativo escreve o cabecalho somente 1 vez. E tambem se for a primeira linha, desenha o cabecalho pelo 'else'
		if($_pbauto>0 and $_ilinha>1){
			//verifica quando é que uma nova quebra sera colocada
			if($_pbauto>($_ilinhaquebra+1)){
				//echo "\n#".$_ilinhaquebra;
				$_ilinhaquebra++;
			}else{
				echo "\n</table>";
				echo $strnewpage;//QUEBRA A PAGINA
				echo $strpagini;
				echo $strtabini;
				echo $strtabheader;
				$_ilinhaquebra=0;


			}
		}else{
			//Escreve o cabecalho somente uma vez
			if($_ilinha==1){
				echo $strpagini;
				echo $strtabini;
				echo $strtabheader;
			}
		}

	
		###################################### Escreve linhas da <Table>
		
		//echo("<br>[$_strhlcolor]<br>");
			echo "\n<tr class=\"res\" ". $_link ." ". $_strhlcolor . ">";
		//coloca um contador numerico do lado esquerdo da tabela
		if($_showtotalcounter == "Y"){
			echo "<td class='tdcounter'>".$_ilinha."</td>";
		}
		if($_numlinha == "Y"){
			?><td style="background-color:none;"><?=$_ilinha?></td><?
		}

		/*
		 * Montagem dos <TD>s
		 */
		foreach(array_keys($_row) as $key => $coluna) 
		{
			$_stralign="";
			$_strvlrhtml="";
			//$_i.'<br>';
			$_nomecol = $arrRep["_colvisiveis"][$key+1];
			///echo $key.'<br>';
    		$_colorlink="";
    		$_hyperlink="";
    		$_corfont= "";
    		$_corfontfim="";
	    	//Escreve Campo
    	    if($arrRep["_filtros"][$_nomecol]["visres"] == 'Y'){
 	
    			//ajusta o alinhamento dentro da celula. caso esquerda. nao preencher para nao gerar html desnecessariamente
    			//echo $arrRep["_filtros"][$_nomecol]["align"];
    			if($arrRep["_filtros"][$_nomecol]["align"]!="left"){
    				$_stralign = "align='".$arrRep["_filtros"][$_nomecol]["align"]."'";
				}
    			
    	    	//se for para somar o valor do campo
    			if($arrRep["_filtros"][$_nomecol]["acsum"]=='Y'){
					
    				$_arrsoma[$_tab][$_nomecol] = $_arrsoma[$_tab][$_nomecol] + $_row[$coluna];
    			}
				
				//se for para somar o valor do campo
    			if($arrRep["_filtros"][$_nomecol]["acavg"]=='Y'){
					
    				$_arrsomaavg[$_tab][$_nomecol] = $_arrsomaavg[$_tab][$_nomecol] + $_row[$coluna];
					
    			}
				
    			
				/*
				 * Trata campo de longtext
				 */
				if($arrRep["_filtros"][$_nomecol]["datatype"]=='longtext'){
					$_strvlrhtml = nl2br($_row[$coluna]);
				}elseif($arrRep["_filtros"][$_nomecol]["datatype"]=='datetime'){
					$_strvlrhtml = validadatadbweb($_row[$coluna]);
				}elseif($arrRep["_filtros"][$_nomecol]["datatype"]=='date'){
					$_strvlrhtml = dma($_row[$coluna]);
				}elseif($arrRep["_filtros"][$_nomecol]["datatype"]=='decimal'){
					$_strvlrhtml = number_format($_row[$coluna], 2, ',','.');
				}else{
					$_strvlrhtml = $_row[$coluna];
				}
				
				$_strvlrhtml=aplicaMascara($arrRep["_filtros"][$_nomecol]["mascara"], $_strvlrhtml);					

				if (is_numeric($_row[$coluna])){
					$total[$key] = $total[$key] + $_row[$coluna];
				}
				
				
				if($key == 3){
					if($_row[$coluna] != ''){
						$arrNomeXml[].=$_row[$coluna];
					} else {
						$arrNomeXml[].='não consta';
					}
				}

				if($key == 4){
					if($_row[$coluna] != ''){
						$arrXml[].=$_row[$coluna];
					} else {
						$arrXml[].='não consta';
					}
				}			

				//Se o hyperlink não estiver vazio ele monta o link
    			if(!empty($arrRep["_filtros"][$_nomecol]["hyperlink"])){
					if ($_strvlrhtml != '0.00'){
						//O HREF contém uma barra ('/') o que significa que é uma URL relativa, que vai desconsiderar qualquer pasta informada neste link
						if (strpos($arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$coluna] , 'pk=')){
							
							$arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$coluna];
							 $valor = explode('pk=',$arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$coluna]);
							// print_r($valor);
							$valor = explode('&',$valor[1]);
							//print_r($valor,);
							 $campo = $_row[$valor[0]];
							
							$_hyperlink="<a target=_blank href='/".$arrRep["_filtros"][$_nomecol]["hyperlink"].$campo."'>".$_strvlrhtml."</a>";
						}else{
							$_hyperlink="<a target=_blank href='/".$arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$coluna]."'>".$_strvlrhtml."</a>";
						}
						
						$_colorlink="class=\"link\" ";
						$_corfont= "<font color='Blue'>";
						$_corfontfim="</font>";
					}else{
						$_hyperlink = strip_tags($_strvlrhtml);
					}
    				    				
    			}
				
				//Finalmente: desenha o campo na tela
				if(!empty($arrRep["_filtros"][$_nomecol]["hyperlink"])){
					echo "<td ".$_stralign." >".$_hyperlink."&nbsp;</td>";
				}else{
					echo "<td ".$_stralign." ".$_hyperlink." ".$_colorlink.">".$_corfont.$_strvlrhtml.$_corfontfim."&nbsp;</td>";
				}
	    	}
	    	$_i++;
    	}
    }

?>
		</tr>
<?

	if(!empty($_arrsoma) or !empty($_arrsomaavg)){
		
?>		
		<tr class="res">
			<td colspan="500" class="inv">&nbsp;</td>
		</tr>
		<tr class="res">
<?		

		$_y=0;
		foreach (array_keys($relatorios[0]) as $key => $coluna) 
		{
			$_stralign="";
			$_strvlrhtml="";
			$_nomecol = $arrRep["_colvisiveis"][$key+1];
		
    		if($arrRep["_filtros"][$_nomecol]["visres"] == 'Y' and $arrRep["_filtros"][$_nomecol]["acsum"]=='Y'){ 
				
				echo("<td class=\"tot\" style=\"text-align:".$arrRep["_filtros"][$_nomecol]["align"]."\">");
				if (containsDecimal($_arrsoma[$_tab][$_nomecol])){
						echo($tipocalc." ".number_format($_arrsoma[$_tab][$_nomecol], 2, ',','.'));
					}else{
						echo($tipocalc." ".number_format($_arrsoma[$_tab][$_nomecol], 0, ',','.'));
					}
					
				echo("</td>");
    			
    			
    			
    		}elseif($arrRep["_filtros"][$_nomecol]["visres"] == 'Y' and $arrRep["_filtros"][$_nomecol]["acavg"]=='Y'){
				echo("<td class=\"tot\" style=\"text-align:".$arrRep["_filtros"][$_nomecol]["align"]."\">");
				if (containsDecimal($_arrsomaavg[$_tab][$_nomecol])){
						echo($tipocalc." ".number_format(($_arrsomaavg[$_tab][$_nomecol]/$_ilinha), 2, ',','.'));
					}else{
						echo($tipocalc." ".number_format(($_arrsomaavg[$_tab][$_nomecol]/$_ilinha), 0, ',','.'));
					}
					
				echo("</td>");
			}else{
    			echo("<td class=\"inv\"></td>");
    		}
		    $_y++;		
		}  
?>
		</tr>
<?	 		
	}	?>
	
		<?
			$arrXML = $JSON->encode($arrNomeXml);
		?>
	
	</tbody>
	<tfoot>
	
	<tr>
		<td colspan="<?=$_numcolunas;?>">
			<div style="width:100%">
				<div  style="width:50%;float:left;font-size:9px;"><br>
					<?=htmlspecialchars_decode($_rodape);?>
				</div>
				<div  style="width:50%;float:left;font-size:9px;"><br>
				<? if ($_descr) { ?><strong>LEGENDA:</strong><br>
					<?=nl2br($_descr);?>
				<? } ?>
				</div>
			</div>
		</td>
	</tr>
	</tfoot>
	  </table>	  	 
<?

	/*
	 * Desenha a legenda
	 */

}

if(defined("_RODAPEDIR")) $varfooter= _RODAPEDIR;
?>
</body>
	<fieldset class="fldsfooter">
	<legend>Fim da Impressão <?=$_nomeimpressao . " ".$varfooter?></legend>
	</fieldset>
</body>
</html>



<?

if(!empty($_GET["reportexport"])){
	$teste=count($arrXml);

	foreach ($arrNomeXml as $key => $file) {

		$text = $arrXml[$key];
		$filename = "../upload/".$file.".xml";
		$fh = fopen($filename, "w");
		fwrite($fh, $text);
		fclose($fh);
		$f[] = ('../upload/' . $file . '.xml');
	}

	function createZipFile($f = array(), $fileName)
	{
		$zip = new ZipArchive();
		$rc = $zip->open("$fileName.zip", ZipArchive::CREATE);
		if ($rc === true) {
			foreach ($f as $file) {
				$zip->addFile($file);
			}
		}
		$zip->close();
		$fileName = $fileName . '.zip';

		if (file_exists($fileName)) {
		header('Content-Type: application/zip');
		header("Content-Disposition: attachment; filename = $fileName");
		header('Content-Length: ' . filesize($fileName));
		header("Content-Disposition: attachment; filename=\"".basename($fileName)."\"");
		ob_clean();
		flush();
		readfile($fileName);
		unlink($fileName);   
		exit;
		}
	}

	$fileName = 'XML-'.$data1.'-a-'.$data2;
	$fileName = preg_replace("/[^A-Za-z0-9s.]/", "", $fileName);
	createZipFile($f, $fileName);	

}
?>

