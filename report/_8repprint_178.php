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

$eixoX = "";
$eixoY = [];
$arrayGrafico=array();
$tipoGraphRelatorio = $arrRep["tipograph"];
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

	$_sqldata = '';	
	require_once(__DIR__."/scripts/_8repprint_montaclausuladata.php");
			
	// Definir Preferencias do usuario
	require_once(__DIR__."/scripts/_8repprint_ajustaprefusuario.php");
				
	//Abre variavel de sessao para que ela possa ser acessada pelo modulo de interceptacao de eventos
	//Isto permitira saber se existe clausula where ou nao
	$_SESSION["SEARCH"]["CLAUSULAS"] = (string)$_iclausulas;
	
	require_once(__DIR__."/scripts/_8repprint_178_montarclausulaidempresa.php");
	

	//--------------------- Validação para filtro com Plantel - LTM (28-07-2020 - 363014) ----------------
	$arrFiltros = retarraytabdef($_tab); 
	require_once(__DIR__."/scripts/_8repprint_validafiltroplantel.php");
	
	// RETRINGIR CONSULTA A UNIDADE MARCADA NA LP-------------------------------------------------------------
	$lps=getModsUsr("LPS");
	$lpRep = MenuRelatorioController::buscarLpRepPorIdRepEIdLps($_idrep, $lps);

	require_once(__DIR__."/scripts/_8repprint_178_restringirporunidademarcadalp.php");

	// RETRINGIR CONSULTA A HIERARQUIA ORGANOGRAMA QUANDO MARCADO NA LPREP-------------------------------------------------------------
	require_once(__DIR__."/scripts/_8repprint_restringirconsultaaoorganogramapelalp.php");

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
	// $_sqlresultado = $strselectfields." from ".$_sqlresultado.$_sqldata.$_and_idempresa.$str_fts.$strgrp.$strord;
	$relatorios = MenuRelatorioController::buscarRelatorioDinamico($strselectfields, ($_sqlresultado.$_sqldata.$_and_idempresa.$str_fts.$strgrp.$strord));

	// $_resultados = d::b()->query($_sqlresultado);
	if ($relatorios === false) {
	    die('<b>Falha na execucao da Consulta para o Report:</b> ' . mysql_error() . "<br>");
	}

	$_arrtab = retarraytabdef($_tab);

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

	require_once(__DIR__."/scripts/_8repprint_106_montacabecalho.php");

	/*
	 * MONTA A TABELA
	 */
	$_ilinha = 0;//armazena o ttotal de registros
	$_ilinhaquebra = 0;//armazena parialmente o numero de registros se houver quebra automatica configurada
	$_graphLinha = 0;
	$strnewpage = "<span class='newreppage'></span>";
		
	$valorg=0;
	$nome = '';
	$grupo='';
    // while ($_row = mysql_fetch_array($_resultados)){
	foreach($relatorios as $_row)
	{
		if(empty($grupo)){
			$grupo=$_row['tipoprodserv'];
		}
		
		if($grupo!=$_row['tipoprodserv']){
			$mostrar2 = '<tr style="text-align: right !important; background-color:#fff; font-size:10px"><td colspan="7" ></td></tr><tr class="res" style=" text-align: right !important;background-color:#ddd; font-size:10px;height: 30px;"><td colspan="6" >TOTAL '.$grupo.'</td><td align="right"><b>'.number_format($valorg, 2, ',','.').'</b></td></tr><tr style="text-align: right !important; background-color:#fff; font-size:10px"><td colspan="6" ></td></tr>';
			$valorg=0; 
			$grupo=$_row['tipoprodserv'];
		}else{
			$mostrar2 = '';
		}
		
		
		if(empty($nome)){
			$nome=$_row['formapgto'];
		}
		
		if($nome!=$_row['formapgto']){
			$mostrar = '<tr style="	text-align: right !important; background-color:#fff; font-size:10px"><td colspan="7" ></td></tr><tr style="	text-align: right !important; background-color:#ccc; font-size:10px;height: 40px;"><td colspan="6" ><b>TOTAL '.$nome.'</td><td align="right"><b>'.number_format($vtotal, 2, ',','.').'</b></td></tr><tr style="	text-align: right !important; background-color:#fff; font-size:10px"><td colspan="6" ></td></tr>';
			$vtotal=0; 
			$nome=$_row['formapgto'];
		}else{
			$mostrar = '';
		}

		$vtotal	=	$vtotal + $_row['valor'];
		$valorg	=	$valorg + $_row['valor'];
		
		
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
	
	echo $mostrar2;
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
    	// while ($_i < $_numcolunas) {
		foreach(array_keys($relatorios[0]) as $key => $coluna)
		{
			$_stralign="";
			$_strvlrhtml="";

    		$_nomecol = $arrRep["_colvisiveis"][$key+1];
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
    			
				/*
				 * Trata campo de longtext
				 */
				if($arrRep["_filtros"][$_nomecol]["datatype"]=='longtext'){
					$_strvlrhtml = nl2br($_row[$coluna]);
				}elseif($arrRep["_filtros"][$_nomecol]["datatype"]=='datetime'){
					$_strvlrhtml = validadatadbweb($_row[$coluna]);
				}elseif($arrRep["_filtros"][$_nomecol]["datatype"]=='date'){
					$_strvlrhtml = dma($_row[$coluna]);
				}else{
					$_strvlrhtml = $_row[$coluna];
				}
				
				$_attrHtml .= "datatype='".$arrRep["_filtros"][$_nomecol]["datatype"]."' mascara='".$arrRep["_filtros"][$_nomecol]["mascara"]."'  eixografico='".$arrRep["_filtros"][$_nomecol]["eixograph"]."' col='".$_nomecol."'  ";
				$_strvlrhtml=aplicaMascara($arrRep["_filtros"][$_nomecol]["mascara"], $_strvlrhtml);		
				
				$arrayGrafico[$_graphLinha][$_nomecol] = $_row[$coluna];

				if (is_numeric($_row[$coluna])){
					$total[$_i] = $total[$_i] + $_row[$coluna];
				}
				//SE FOR UM NOVO <TD> ELE NÃO COMEÇA COM VIRGULA NO CSV
				if($_i>0){
					$conteudoexport.=";";//COLOCA A VIRGULA ENTRE OS VALORES 
				}

				if(!empty($arrRep["_filtros"][$_nomecol]["eixograph"])){
					if($arrRep["_filtros"][$_nomecol]["eixograph"] == 'X'){
						$eixoX = $_nomecol;
					}else if($arrRep["_filtros"][$_nomecol]["eixograph"] == 'Y'){
						$eixoY[] = $_nomecol;
					}
				}
				
				$conteudoexport.="\"".$_strvlrhtml."\"";//GRAVA O VALOR DO CAMPO PARA GERAR O CSV ENTRE ASPAS

				//Se o hyperlink não estiver vazio ele monta o link
    			if(!empty($arrRep["_filtros"][$_nomecol]["hyperlink"])){
    				//O HREF contém uma barra ('/') o que significa que é uma URL relativa, que vai desconsiderar qualquer pasta informada neste link
    				$_hyperlink="<a target=_blank href='/".$arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$coluna]."'>".$_strvlrhtml."</a>";
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
		$_graphLinha++;
    }
?>
		</tr>
<?
	$mostrar = '<tr style="text-align: right !important; background-color:#fff; font-size:10px"><td colspan="6" ></td></tr><tr style="	text-align: right !important; background-color:#eee; font-size:10px;height:30px"><td colspan="'.(count(array_keys($relatorios[0])) - 1).'" ><b>TOTAL '.$nome.'</td><td align="right"><b>'.number_format($vtotal, 2, ',','.').'</b></td></tr>';
	$mostrar2 = '<tr style="text-align: right !important; background-color:#fff; font-size:10px"><td colspan="7" ></td></tr><tr class="res"  style=" 	text-align: right !important; font-size:10px;height:30px"><td colspan="'.(count(array_keys($relatorios[0])) - 1).'" >TOTAL '.$grupo.'</td><td align="right"><b>'.number_format($valorg	, 2, ',','.').'</b></td></tr>';
	echo $mostrar2;
	echo $mostrar;
	if(!empty($_arrsoma)){
?>		

		<tr class="res">
		
<?		
		echo("<td class=\"inv\"></td>");
		foreach(array_keys($relatorios[0]) as $key => $coluna)
		{
			$_stralign="";
			$_strvlrhtml="";
		
    		if($arrRep["_filtros"][$coluna]["visres"] == 'Y' and $arrRep["_filtros"][$coluna]["acsum"]=='Y'){ 
    			echo("<td style='text-align:right' class=\"tot\">");
    			echo($tipocalc." ".number_format($_arrsoma[$_tab][$coluna], 2, ',','.'));
    			echo("</td>");
    		}elseif($key>0){
    			echo("<td class=\"inv\"></td>");
    		}
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
require_once 'graficos_relatorio.php';
?>