<?
include_once("../inc/php/validaacesso.php");
baseToGet($_GET["_filtros"]);
// CONTROLLERS
require_once(__DIR__."/../form/controllers/menurelatorio_controller.php");
require_once(__DIR__."/../form/controllers/empresa_controller.php");

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
$_chavefts = $arrRep["chavefts"];
$_tabfull = $arrRep["tabfull"];
?>
<html>
<head>
	<? require_once(__DIR__."/_8repprint_head.php") ?>
</head>
<body>
<?
//print_r($_arrpagpsq);
if (!empty($_GET)){

	$_sqlwhere = " where ";
	$_and = "";
	$_iclausulas = 0;
	
	//Loop nos parâmetros GET para montar as cláusulas where
	require_once(__DIR__."/scripts/_8repprint_montarclausulawhere.php");

	//print_r($arrRep["_datas"]);
	$_sqldata = '';	
	require_once(__DIR__."/scripts/_8repprint_montaclausuladata.php");
				
	// Definir Preferencias do usuario
	require_once(__DIR__."/scripts/_8repprint_ajustaprefusuario.php");			
	
	//Abre variavel de sessao para que ela possa ser acessada pelo modulo de interceptacao de eventos
	//Isto permitira saber se existe clausula where ou nao
	$_SESSION["SEARCH"]["CLAUSULAS"] = (string)$_iclausulas;
	
	if($_iclausulas > 0){
		$_sqlresultado = getDbTabela($_tab).".". $_tab ." ".$_sqlwhere;
		//$_sqlresultado .=getidempresa('idempresa','');
	}else{
		$_sqlresultado = getDbTabela($_tab).".". $_tab." a";
		$_sqlresultado .= " where idempresa = ".cb::idempresa();
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

	$strs = "Nenhum Registro encontrado";

	$_i = 0;
    $_numcolunas = count($relatorios[0]);
	$_ipagpsqres = count($relatorios);
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
		<td rowspan="3" style="width:200;"><img src="<?=$figurarelatorio?>"></td>
		<td class="header"><?=$_header?></td>
		<td><a class="btbr20" href="<?=$_SERVER['REQUEST_URI']?>&reportexport=csv" target="_blank">Exportar .csv</a></td>
	</tr>
	<tr>
		<td class="subheader"><h2><?=$_rep;?></h2>
		(<?=$strs?>)</td>
	</tr>
	</table>
	<br>
	<fieldset class="fldsheader">
	  <legend>Início da Impressão <?=$_nomeimpressao?></legend>
	</fieldset>
	<table class="normal">
		<tr class='header'>
<?

	/*
	 * MONTA O CABECALHO
	 */
	$conteudoexport;// guarda o conteudo para exportar para csv
	$strtabheader = "\n<tr class='header'>";
	//coloca um contador numerico do lado esquerdo da tabela, isto é repetido também na montagem de cada linha da tabela
	if($_showtotalcounter == "Y"){
		$strtabheader .= "<td class='tdcounter'></td>";
	}

	foreach(array_keys($relatorios[0]) as $key => $coluna)
	{
	    if($arrRep["_filtros"][$coluna]["visres"] == 'Y'){
	    	//A VIRGULA E SO ENTRE OS VALORES NO INICIO DA LINHA E NO FINAL NÃO TEM VIRGULA
	    	if(!empty($conteudoexport)){
	    		$conteudoexport.=";";
	    	}
	    	
			$strtabheader .= "<td class='header'>" . $arrRep["_filtros"][$coluna]["rotulo"] ."</td>";
	    }	$conteudoexport.= "\"".$arrRep["_filtros"][$coluna]["rotulo"]."\"";// GRAVA O ROTULO DOS CABEÇALHOS NA VARIAVEL PARA GERAR O CSV
	    $_i++;
	}
	
	$conteudoexport.="\n";//QUEBRA DE LINHA NO CONTEUDO CSV
	$strtabheader .= "</tr>";

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

	$vtotal=0;
	$vicmsdeson=0;
	$fretemtotal=0;
	$frete=0;
	$vqtd = 0;
	$estado = '';
	$$especie='';
    // while ($_row = mysql_fetch_array($_resultados)){
	foreach($relatorios as $_row)
	{
		if(empty($estado)){
			$estado=$_row['uf'];
			$especie=$_row['especie'];
		}
		
		if($estado!=$_row['uf']){
			$mostrar = '<tr style="background-color:#eee; font-size:10px"><td colspan="'.($_numcolunas-4).'" ><b>TOTAL '.$especie.' '.$estado.'</td><td align="right"><b>'.number_format($vicmsdeson, 2, ',','.').'</b></td><td align="right"><b>'.number_format($vtotal, 2, ',','.').'</b></td><td align="right"><b>'.number_format($frete, 2, ',','.').'</b></td><td align="right"><b>'.number_format($fretemtotal, 2, ',','.').'</b></td></tr>';
			$vtotal=0;
			$vqtd = 0;
			$vicmsdeson=0;
			$fretemtotal=0;
			$frete=0;
			$estado=$_row['uf'];
			$especie=$_row['especie'];
			
		}else{
			$mostrar = '';
		}
		$vqtd 	= 	$vqtd  + $_row['freteitem'];
		$frete 	= 	$frete  + $_row['freteitem'];
		$vicmsdeson 	= 	$vicmsdeson  + $_row['vicmsdeson'];
		$fretemtotal = $fretemtotal+$_row['fretemtotal'];
		
		$vsubtotal=$subtotal+$_row['vlritem'];
		$vtotal	=	$vtotal + $_row['total'];

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

		/*
		* EFETUA HIGHLIGHT
		* O loop irá passar por todas as linhas encontradas, logo, valores MENORES no campo ord da tabelda highlight sempre terão prioridade
		*/
		$_tmphlcolor = "";
		$_boocond = false;
		//print_r($_arrhlcond[$_pagpsq]);
		if(!empty($_arrhlcond[$_pagpsq])){

	        foreach ($_arrhlcond[$_pagpsq] as $_fldcond => $_cond) {
			if ($_boocond==false){
				//echo ("<br>cond:[".$_cond['cond']."-".$_cond["valor1"]."]<br>");
				switch($_cond["cond"]){
					
					case "=":
						if ($_row[$_cond["col"]] == $_cond["valor1"]){
							$_tmphlcolor = $_cond["cor"];
							$_boocond = true;
						}
						break;
					case "!=":
						if ($_row[$_cond["col"]] != $_cond["valor1"]){
							$_tmphlcolor = $_cond["cor"];
							$_boocond = true;
						}
						break;
					case ">":
						if ($_row[$_cond["col"]] > $_cond["valor1"]){
							$_tmphlcolor = $_cond["cor"];
							$_boocond = true;
						}
						break;
					case ">=":
						if ($_row[$_cond["col"]] >= $_cond["valor1"]){
							$_tmphlcolor = $_cond["cor"];
							$_boocond = true;
						}
						break;
					case "<":
						if ($_row[$_cond["col"]] < $_cond["valor1"]){
							$_tmphlcolor = $_cond["cor"];
							$_boocond = true;
						}
						break;
					case "<=":
						if ($_row[$_cond["col"]] <= $_cond["valor1"]){
							$_tmphlcolor = $_cond["cor"];
							$_boocond = true;
						}
						break;
					case "between":
						if (($_row[$_cond["col"]] >= $_cond["valor1"]) and ($_row[$_cond["col"]] <= $_cond["valor2"])){
							$_tmphlcolor = $_cond["cor"];
							$_boocond = true;
						}
						break;
					default:
						break;
				}
			}
		}
	}//if(!empty($_arrhlcond[$_pagpsq])){

	###################################### Escreve linhas da <Table>
	echo $mostrar;
	$_strhlcolor = "style=\"background-color:".$_tmphlcolor.";\"  ";
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
	foreach($_row as $coluna => $valor)
	{
		$_stralign="";
		$_strvlrhtml="";

		$_nomecol = $coluna;
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
				$_arrsoma[$_tab][$_nomecol] = $_arrsoma[$_tab][$_nomecol] + $valor;
				
			}
			
			/*
			* Trata campo de longtext
			*/
			if($arrRep["_filtros"][$_nomecol]["datatype"]=='longtext'){
				$_strvlrhtml = nl2br($valor);
			}elseif($arrRep["_filtros"][$_nomecol]["datatype"]=='datetime'){
				$_strvlrhtml = validadatadbweb($valor);
			}elseif($arrRep["_filtros"][$_nomecol]["datatype"]=='date'){
				$_strvlrhtml = dma($valor);
			}else{
				$_strvlrhtml = $valor;
			}
			
			$_strvlrhtml=aplicaMascara($arrRep["_filtros"][$_nomecol]["mascara"], $_strvlrhtml);					

			if (is_numeric($valor)){
				$total[$_i] = $total[$_i] + $valor;
			}
			//SE FOR UM NOVO <TD> ELE NÃO COMEÇA COM VIRGULA NO CSV
			if($_i>0){
				$conteudoexport.=";";//COLOCA A VIRGULA ENTRE OS VALORES 
			}
			
			$conteudoexport.="\"".$_strvlrhtml."\"";//GRAVA O VALOR DO CAMPO PARA GERAR O CSV ENTRE ASPAS

			//Se o hyperlink não estiver vazio ele monta o link
			if(!empty($arrRep["_filtros"][$_nomecol]["hyperlink"])){
				//O HREF contém uma barra ('/') o que significa que é uma URL relativa, que vai desconsiderar qualquer pasta informada neste link
				$_hyperlink="<a target=_blank href='/".$arrRep["_filtros"][$_nomecol]["hyperlink"].$valor."'>".$_strvlrhtml."</a>";
				$_colorlink="class=\"link\" ";
				$_corfont= "<font color='Blue'>";
				$_corfontfim="</font>";
									
			}
			
			//Finalmente: desenha o campo na tela
			if(!empty($arrRep["_filtros"][$_nomecol]["hyperlink"])){
				echo "<td>".$_hyperlink."</td>";
			}else{
				echo "<td ".$_stralign." ".$_hyperlink." ".$_colorlink.">".$_corfont.$_strvlrhtml.$_corfontfim."</td>";
			}
			
			
		}
		$_i++;
	}
		$conteudoexport.="\n";//QUEBRA A LINHA DO CONTEUDO CSV
}
?>
		</tr>
<?
	$mostrar = '<tr style="background-color:#eee; font-size:10px"><td colspan="'.($_numcolunas-4).'" ><b>TOTAL '.$especie.' '.$estado.'</td><td align="right"><b>'.number_format($vicmsdeson, 2, ',','.').'</b></td><td align="right"><b>'.number_format($vtotal, 2, ',','.').'</b></td><td align="right"><b>'.number_format($frete, 2, ',','.').'</b></td><td align="right"><b>'.number_format($fretemtotal, 2, ',','.').'</b></td></tr>';
	
	echo $mostrar;
	if(!empty($_arrsoma)){
?>		
		<tr class="res">
			<td colspan="500" class="inv">&nbsp;</td>
		</tr>

		<tr class="res">
<?		
		$_y=0;
		foreach(array_keys($relatorios[0]) as $key => $coluna)
		{
			$_stralign="";
			$_strvlrhtml="";
    		$_nomecol = $coluna;
		
    		if($arrRep["_filtros"][$_nomecol]["visres"] == 'Y' and $arrRep["_filtros"][$_nomecol]["acsum"]=='Y'){ 
    		
    			if($arrRep["_filtros"][$_nomecol]["acsum"]=='Y'){
    				//$tipocalc="Soma:";
    			}
    			echo("<td class=\"tot\">");
    			echo($tipocalc." ".number_format($_arrsoma[$_tab][$_nomecol], 2, ',','.'));
    			echo("</td>");
    		}else{
    			echo("<td class=\"inv\"></td>");
    		}
		    $_y++;		
		}  
?>
		</tr>
<?	 		
	}
?>					   
	  </table>	  	 
<?
	/*
	 * Desenha a legenda
	 */
	if(!empty($_descr)){
?>
	
	<table  class="normal" style="width:400px; margin-top: 40px; font-size:10px; text-transform:uppercase;">
<tr class="header" style="padding: 0px;margin: 0px;">
		<td >LEGENDA</td>


	</tr>
		<tr style="padding: 0px;margin: 0px;">
		<td ><?=nl2br($_descr);?></td>


	</tr>

	</table>
	
<?
	}
}//if (!empty($_GET)){

if(defined("_RODAPEDIR")) $varfooter= _RODAPEDIR;
?>
	<fieldset class="fldsfooter">
	<legend>Fim da Impressão <?=$_nomeimpressao . " ".$varfooter?></legend>
	</fieldset>
</body>
</html>
<?
if(!empty($_GET["reportexport"])){
	ob_end_clean();//não envia nada para o browser antes do termino do processamento
	
	/* Gerar o nome do arquivo para exportar
	 * Substitui qualquer caractere estranho pelo sinal de '_'
	 * Caracteres que NAO SERAO substituidos:
	 *   - qualquer caractere de A a Z (maiusculos)
	 *   - qualquer caracteres de a a z (minusculos)
	 *   - qualquer caractere de 0 a 9
	 *   - e pontos '.'
	 */ 
	$infilename = empty($_header)?$_rep:$_header;
	$infilename = preg_replace("/[^A-Za-z0-9s.]/", "", $infilename);
	//gera o csv
	header("Content-type: text/csv; charset=utf-8");
	header("Content-Disposition: attachment; filename=".$infilename.".csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	echo iconv('UTF-8', 'ISO-8859-1', $conteudoexport);
	
}
?>