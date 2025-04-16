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
<script src="/inc/js/jquery/jquery-1.11.2.min.js"></script>

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
	
	//echo '**'.$strgrp;
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
		<td><a class="btbr20 no-print" href="<?=$_SERVER['REQUEST_URI']?>&reportexport=csv" target="_blank">Exportar .csv</a></td>
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
//print_r($arrRep);
	/*
	 * MONTA O CABECALHO
	 */
	$conteudoexport;// guarda o conteudo para exportar para csv
	$strtabheader = "\n<thead><tr class='header'>";
	//coloca um contador numerico do lado esquerdo da tabela, isto é repetido também na montagem de cada linha da tabela
	if($_showtotalcounter == "Y"){
		$strtabheader .= "<td class='tdcounter'></td>";
	}
	
	foreach(array_keys($relatorios[0]) as $key => $coluna)
	{
		if($arrRep["_filtros"][$arrRep["_colvisiveis"][$key+1]]["visres"] == 'Y'){
	    	//A VIRGULA E SO ENTRE OS VALORES NO INICIO DA LINHA E NO FINAL NÃO TEM VIRGULA
	    	if(!empty($conteudoexport)){
	    		$conteudoexport.=";";
	    	}

			if (strpos(strtolower($arrRep["_filtros"][$arrRep["_colvisiveis"][$key+1]]["rotulo"]), ' as ') !== false) {
				$val = explode(' as ',strtolower($arrRep["_filtros"][$arrRep["_colvisiveis"][$key+1]]["rotulo"]));
				$arrRep["_filtros"][$arrRep["_colvisiveis"][$key+1]]["rotulo"] = $val[1];
			}
	    	
			$strtabheader .= "<td class='header' id='".MenuRelatorioController::urlAmigavel(str_replace('`','',$arrRep["_filtros"][$arrRep["_colvisiveis"][$key+1]]["rotulo"]))."' style=\"text-align:".$arrRep["_filtros"][$arrRep["_colvisiveis"][$key+1]]["align"]."\">" . str_replace('`','',$arrRep["_filtros"][$arrRep["_colvisiveis"][$key+1]]["rotulo"])."</td>";
	    }	
		if(!empty($arrRep["_filtros"][$_metacmp->name]["rotulo"])){
			$conteudoexport.= "\"".$arrRep["_filtros"][$_metacmp->name]["rotulo"]."\"";// GRAVA O ROTULO DOS CABEÇALHOS NA VARIAVEL PARA GERAR O CSV
		}else{
			$conteudoexport.= "\"".str_replace('`','',$arrRep["_filtros"][$arrRep["_colvisiveis"][$key+1]]["rotulo"])."\"";// GRAVA O ROTULO DOS CABEÇALHOS NA VARIAVEL PARA GERAR O CSV
		}
		
	    $_i++;
	}
	
	$conteudoexport.="\n";//QUEBRA DE LINHA NO CONTEUDO CSV
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
	$especie = '';
	$totalPlantel = -1;

	foreach($relatorios as $_row)
	{
		$_ilinha++;
		$_i = 0;	

		if(empty($especie)){
			$especie = $_row["eventotipo"];
		}
		
		if($especie != $_row["eventotipo"]){
			$totalPlantel++;
			$mostrar = '<tr style="background-color:#eee; font-size:10px"><td colspan="3"><b>TOTAL '.$especie.'</b></td><td colspan="2" id="linhaseparacao" style="height:10px;text-align: center;"><b>'.$totalPlantel.' registros</b></td></tr>';
			$especie = $_row["1"];
			$totalPlantel = 0;
		}else{
			$mostrar = '';
			$totalPlantel++;
		}

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
		
		echo $mostrar;
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
					$total[$_i] = $total[$_i] + $_row[$coluna];
				}
				//SE FOR UM NOVO <TD> ELE NÃO COMEÇA COM VIRGULA NO CSV
				if($_i>0){
					$conteudoexport.=";";//COLOCA A VIRGULA ENTRE OS VALORES 
				}
				
				$conteudoexport.="\"".$_strvlrhtml."\"";//GRAVA O VALOR DO CAMPO PARA GERAR O CSV ENTRE ASPAS

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
		$conteudoexport.="\n";//QUEBRA A LINHA DO CONTEUDO CSV
    }

//print_r($arrRep);
//print_r($_arrsoma);
?>
		</tr>
<?
	$totalPlantel++;
	$mostrar = '<tr style="background-color:#eee; font-size:10px"><td colspan="3"><b>TOTAL '.$especie.'</b></td><td colspan="2" id="linhaseparacao" style="height:10px;text-align: center;"><b>'.$totalPlantel.' registros</b></td></tr>';
	echo $mostrar;

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
				
    			if($arrRep["_filtros"][$_nomecol]["acsum"]=='Y'){
    				//echo 'entrei';
					//$tipocalc="Soma:";
    			}
				echo("<td class=\"tot\" style=\"text-align:".$arrRep["_filtros"][$_nomecol]["align"]."\">");
				if (containsDecimal($_arrsoma[$_tab][$_nomecol])){
						echo($tipocalc." ".number_format($_arrsoma[$_tab][$_nomecol], 2, ',','.'));
					}else{
						echo($tipocalc." ".number_format($_arrsoma[$_tab][$_nomecol], 0, ',','.'));
					}
					
				echo("</td>");
				//echo $arrRep["_filtros"][$_nomecol]["datatype"];
    			
    			
    			
    		}elseif($arrRep["_filtros"][$_nomecol]["visres"] == 'Y' and $arrRep["_filtros"][$_nomecol]["acavg"]=='Y'){
				if($arrRep["_filtros"][$_nomecol]["acavg"]=='Y'){
    				//echo 'entrei';
					//$tipocalc="Soma:";
    			}
				echo("<td class=\"tot\" style=\"text-align:".$arrRep["_filtros"][$_nomecol]["align"]."\">");
				if (containsDecimal($_arrsomaavg[$_tab][$_nomecol])){
						echo($tipocalc." ".number_format(($_arrsomaavg[$_tab][$_nomecol]/$_ilinha), 2, ',','.'));
					}else{
						echo($tipocalc." ".number_format(($_arrsomaavg[$_tab][$_nomecol]/$_ilinha), 0, ',','.'));
					}
					
				echo("</td>");
				//echo $arrRep["_filtros"][$_nomecol]["datatype"];
			}else{
    			echo("<td class=\"inv\"></td>");
    		}
		    $_y++;		
		}  
?>
		</tr>
<?	 		
	}	?>
	

	
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

}//if (!empty($_GET)){
?>
 
<?
if(defined("_RODAPEDIR")) $varfooter= _RODAPEDIR;
?>
    <footer>
     
    </footer>
</body>
	<fieldset class="fldsfooter">
	<legend>Fim da Impressão <?=$_nomeimpressao . " ".$varfooter?></legend>
	</fieldset>
</body>
</html>
<?
if(!empty($_GET["reportexport"])){
	if($_GET["_debug"]!=="true"){
		ob_end_clean();//não envia nada para o browser antes do termino do processamento
	}
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
	//LTM - 05-10-2020 - 375916: Alterado pois não estava imprimindo no excel e no libre estava desconfigurando os caracteres especiais. 
	//Devido a correção dos resultados congelados no banco não há necessidade de usar o iconv
	
	header('Content-Encoding: UTF-8');
    header('Content-Type: text/csv; charset=utf-8' );
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	if($_GET["_debug"]!=="true"){
		header("Content-Disposition: attachment; filename=".$infilename.".csv");
	}
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo "\xEF\xBB\xBF";
	
	echo $conteudoexport;
	exit();

	
}
?>
