<?
include_once("../inc/php/validaacesso.php");
baseToGet($_GET["_filtros"]);

if(!empty($_GET["reportexport"])){
	ob_start();//não envia nada para o browser antes do termino do processamento
}

$sql_check_LP = "select 1 from carbonnovo._lprep where idlp in (".getModsUsr("LPS").") and idrep= ".$_GET["_idrep"]."";
$chk = d::b()->query($sql_check_LP) or die('ERRO AO VERIFICAR LP');
if(mysqli_num_rows($chk) == 0){
	die('<div>Você não Possui permissão para acessar esse Relatório</div>');
}


$_modulo = $_GET["_modulo"];
$_idrep = $_GET["_idrep"];

if(empty($_modulo)){
	die("M&oacute;dulo n&atilde;o informado!");
}
if(empty($_idrep)){
	die("Relat&oacute;rio n&atilde;o informado!");
}

if ($_idrep == 21){
	d::b()->query("SET sql_mode = 'NO_UNSIGNED_SUBTRACTION';");
}
//Recupera a definicao das colunas da view ou table default da pagina
$arrRep=getConfRelatoriosModulo($_modulo,true,$_idrep);
//Facilita a utilização do array
$arrRep=$arrRep[$_idrep];

$_rep = $arrRep["rep"];
$_header = $arrRep["header"];
$_footer = $arrRep["footer"];
$_showfilters = $arrRep["showfilters"];
if ($_GET['relatorio']){
	echo $_tab = $_GET['relatorio'];
	
}else{
	$_tab = $arrRep["tab"];
}

$_newgrouppagebreak = $arrRep["newgrouppagebreak"];
$_pbauto = $arrRep["pbauto"];
$_showtotalcounter = $arrRep["showtotalcounter"];

?>
<html>
<head>
<title><?=$_header?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../css/rep.css" media="all" rel="stylesheet" type="text/css" />
<style>
html{
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 10px;
	margin:0px;
	padding:0px;
}

body{
	margin:0px;
	padding:0px;
}
.tbrepheader{
	border: 0px;
	width: 100%;
}
.tbrepheader .header{
	font-size: 13px;
	font-weight: bold;
}

.tbrepheader .subheader{
	font-size: 10px;
	color: gray;
}
.tbrepheader .titulo{
	font-size: 18px;
	font-weight: bold;
}
.tbrepheader .res{
	font-size: 18px;
}
.normal{
	border: 1px solid silver;
	border-collapse: collapse;	
}
.normal table{
	table-layout: fixed;
    word-wrap: break-word;
	    
}
.normal td{
	border: 1px solid silver;
	padding: 0px 3px 0px 3px;
	text-transform: uppercase;
	padding-top: 4px;
    padding-bottom: 4px;
}

.normal .header{
	font-size: 10px;
	font-weight: bold;
	color: rgb(75,75,75);
	background-color: rgb(222,222,222);
}
.normal .res{
	font-size: 10px;
}
.normal .res .link{
	background-color:#FFFFFF;
	cursor:pointer;
}
.normal .res .tot{
	background-color:#E8E8E8;
	font-weight: bold;	
	text-align: center;
}
.normal .res .inv{
	border: 0px;
}
.normal .tdcounter{
	border:1px dotted rgb(222,222,222);
	background-color:white;
	color:silver;
	font-size:8px;
}
.newreppage{
	page-break-before: always;
}
.fldsheader{
	border:none;
	border-top: 2px solid silver;
	height: 0px;
	margin: 0px;
	padding: 0px;
	padding-bottom: 5px;
	padding-left:5px;
}
.fldsheader legend{
	font-size: 8px;
	color: gray;
	background-color: white;
}
.fldsfooter{
	border:none;
	border-top: 2px solid silver;
	height: 0px;
	margin: 0px;
	padding: 0px;
	margin-top: 5px;
	padding-left:5px;
}
.fldsfooter legend{
	font-size: 8px;
	color: gray;
	background-color: white;
}
a.btbr20{
	display: none;
}

/* Botao branco fonte 8 */
a.btbr20:link{
	position: fixed;

	right: 15px;

    font-weight: bold;
    font-size:20px;
    color: silver;
    
	border: 1px solid #d7d7d7;
    cursor: pointer;

    padding-left: 5px;
    padding-right: 5px;
    padding-bottom: 1px;
    margin-left: 5px;
      
	background: #cccccc; /* para browsers sem suporte a CSS 3 */

	/* Gradiente */
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ececec', endColorstr='#dcdcdc'); /* IE */
	background: -webkit-gradient(linear, left top, right top, from(#ececec), to(#dcdcdc)); /* webkit */
	background: -moz-linear-gradient(top,  #ececec, #dcdcdc); /* FF */

    /* Arredondamento */
	-moz-border-radius: 8px;
 	-webkit-border-radius: 8px;
 	border-radius: 8px 8px 8px 8px;
 	
 	text-decoration: none;
}
a.btbr20:hover
{
    font-weight: bold;
    font-size:20px;
    color: silver;
    
	border: 1px solid #d7d7d7;
    cursor: pointer;

    padding-left: 5px;
    padding-right: 5px;
    padding-bottom: 1px;
    margin-left: 5px;

	background: #eaeaf4; /* para browsers sem suporte a CSS 3 */

	/* Gradiente */
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff0000', endColorstr='#c02900'); /* IE */
	background: -webkit-gradient(linear, left top, right top, from(#ff0000), to(#c02900)); /* webkit */
	background: -moz-linear-gradient(top, #ffffff, #e1e1e1); /* FF */

    /* Arredondamento */
	-moz-border-radius: 8px;
 	-webkit-border-radius: 8px;
 	border-radius: 8px 8px 8px 8px;
 	text-decoration: none;
} 
a.btbr20:visited {
	border: 1px solid silver;
	color:white;
	text-decoration: none;
}
a.btbr20{
	display: block;
}

</style>
</head>
<body>
<?
//print_r($_arrpagpsq);
if (!empty($_GET)){

	$_sqlwhere = " where ";
	$_and = "";
	$_iclausulas = 0;

	//Loop nos parâmetros GET para montar as cláusulas where
	while (list($_col, $_val) = each($_GET)) {
		$_between = false;
		if(!empty($_val) and ($_col != "_modulo") and ($_col != "_rep") and (substr($_col,-2) != "_2")){

			//Montar clausula para colunas between
			if (substr($_col,-2)=="_1"){
				$_col = substr($_col,0,-2); //Transforma do nome do campo para capturar informacoes de tipo
				$_colval1 = $_GET[$_col."_1"];
				$_colval2 = $_GET[$_col."_2"];
				$_between = true;
			}

			$_datatype = $arrRep["_filtros"][$_col]["datatype"];
			$_psqkey = 	$arrRep["_filtros"][$_col]["psqkey"];
			$_entre = 	$arrRep["_filtros"][$_col]["entre"];
			$_insmanual = 	$arrRep["_filtros"][$_col]["inseridomanualmente"];
			$_like = 	$arrRep["_filtros"][$_col]["like"];
			$_inval = 	$arrRep["_filtros"][$_col]["inval"];
			
			
 
			//Montar clausula somente para campos que estejam marcados como psqkey
			if($_psqkey=="Y" and $_insmanual=="N"){
				if($_between){	
					$_sqlwhere .= $_and . "(" . $_col . " between " . evaltipocoldb($_tab, $_col, $_datatype, $_colval1) . " and " . evaltipocoldb($_tab, $_colval2, $_datatype, $_colval2) . ")";
				}else{
					
					if ($_like == 'Y'){
						if ($_datatype == 'text'){
							$_datatype = 'varchar';
						}
						$_sqlwhere .= $_and . $_col . " like '%" . substr(substr(evaltipocoldb($_tab, $_col, $_datatype, $_val), 1), 0, -1)."%'" ;
					}else if ($_inval == 'Y'){
						if ($_datatype == 'text'){
							$_datatype = 'varchar';
						}
						$_value=null;
						$_val=explode(',',$_val);
						if(count($_val)>=1){
							$arrlenght=count($_val)-1;
							foreach ($_val as $key => $value) {
								if($key==$arrlenght){
									$virg='';
								} else {
									$virg=',';
								}
								$_value.="'".$value."'".$virg;
							}
						}

						$_sqlwhere .= $_and . $_col . " in (" . $_value . ")" ;
					}else if ($_in == 'Y'){
						if ($_datatype == 'text'){
							$_datatype = 'varchar';
							$_sqlwhere .= $_and . $_col . " in (" . substr(substr(evaltipocoldb($_tab, $_col, $_datatype, $_val), 1), 0, -1).")" ;
						}else{
							$_sqlwhere .= $_and . $_col . " in (".$_val.")" ;
						}
					}else{
						$_sqlwhere .= $_and . $_col . " = " . evaltipocoldb($_tab, $_col, $_datatype, $_val);
					}
					
				}


				$_and = " and ";
				$_iclausulas++;
			}else{
				echo "\n<!-- Campo Ignorado: ".$_col." - Manual: ".$_insmanual." -->";
			}
		}
	}
	//echo $_sqlwhere."\n";
	//Abre variavel de sessao para que ela possa ser acessada pelo modulo de interceptacao de eventos
	//Isto permitira saber se existe clausula where ou nao
	$_SESSION["SEARCH"]["CLAUSULAS"] = (string)$_iclausulas;
	
	if($_iclausulas > 0){
		$_sqlresultado = getDbTabela($_tab).".". $_tab ." ".$_sqlwhere;
		$_sqlresultado .="  ".getidempresa('idempresa',$_modulo);
	}else{
		$_sqlresultado = getDbTabela($_tab).".". $_tab." a";
		$_sqlresultado .= " where 1 ".getidempresa('idempresa',$_modulo);
	}

	
		
	// RETRINGIR CONSULTA A UNIDADE MARCADA NA LP-------------------------------------------------------------
	$lps=getModsUsr("LPS");
	$sqlFlgUnidade="Select flgunidade from "._DBCARBON."._lprep where idrep=".$_idrep." and idlp in(".$lps.") order by flgunidade desc";

	$rrep = d::b()->query($sqlFlgUnidade) or die("Erro ao verificar unidade no relatorio: ".mysql_error(d::b()));
	if(mysql_num_rows($rrep)>=1 ){ 
		while ($r = mysql_fetch_array($rrep)){
			if($r['flgunidade']=='Y'){
				$_sqlresultado .= " and exists (select 1 from pessoa p where p.idpessoa = ".$_SESSION['SESSAO']['IDPESSOA']." and p.idunidade = a.idunidade)";
				break;
			}
		}
	}
	
	$strselectfields = "";
	$strord = "";
	$strvirg = "";

	//Concatenar campos para o select
	if(!empty($arrRep["_colvisiveis"])){

		//Transformar em string de 'Select n,...'

		while (list($ko, $vo) = each($arrRep["_colvisiveis"])) {
			$strselectfields .= $strvirg.$vo;
			$strvirg = ", ";
		}

		$strselectfields = "select ".$strselectfields." "; 
		
		//Reseta Variaveis de controle de virgula
		$strvirg = "";
	}

	//Concatenar clausulas para Order By
	if(!empty($arrRep["_orderby"])){
		//Ordenar pelo valor indicado no campo 'ordseq', que é a KEY deste array
		ksort($arrRep["_orderby"]);

		//Transformar em string de 'Order By' para o banco
		while (list($ko, $vo) = each($arrRep["_orderby"])) {
			$strord .= $strvirg.$vo;
			$strvirg = ", ";
		}

		//Concatena a ultima parte da string
		$strord = " order by ".$strord; 
	}
	
	/****************************************************************************
	 * CONCATENACAO PRINCIPAL DO SELECT A SER EXECUTADO PARA O RELATORIO        *
	 ****************************************************************************/
	$_sqlresultado = $strselectfields." from ".$_sqlresultado.$_and_idempresa.$strord;
	
	//Abre variavel de sessao para que ela possa ser acessada pelo modulo de interceptacao de eventos
	$_SESSION["SEARCH"]["SQL"] = $_sqlresultado;

	echo "<!-- ".$_sqlresultado." -->";	//echo $_sqlresultado;

	$_resultados = d::b()->query($_sqlresultado);
	if (!$_resultados) {
	    die('<b>Falha na execucao da Consulta para o Report:</b> ' . mysql_error() . "<br>" . $_sqlresultado);
	}

	$_arrtab = retarraytabdef($_tab);
	//print_r($_ arrtab); die();

	$_i = 0;
    $_numcolunas = mysql_num_fields($_resultados);
	$_ipagpsqres = mysql_num_rows($_resultados);
	if($_ipagpsqres==1){
		$strs = $_ipagpsqres." Registro encontrado";
	}elseif($_ipagpsqres>1){
		$strs = $_ipagpsqres." Registros encontrados";
	}else{
		$strs = "Nenhum Registro encontrado";
	}

	$_nomeimpressao = "[".md5(date('dmYHis'))."] gerada em [".date(" d/m/Y H:i:s")."]";

	/*
	$sqlfig="select figrelatorio from empresa where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
	$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	$figrel=mysqli_fetch_assoc($resfig);

	//$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
	$figurarelatorio = "../inc/img/repheader.png";
	*/
	
	// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
	$sqlfig="select logosis from empresa where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
	$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	$figrel=mysqli_fetch_assoc($resfig);

	//$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
	//$figurarelatorio = "../inc/img/repheader.png";
	$figurarelatorio = $figrel["logosis"];
	
?>
	<div style="display:none; text-align: left; font-size: 9px;" class="n_linhas"><span  id="nlinha"><?=$strs?></span></div>

	<table class="tbrepheader">
	<tr>
		<td rowspan="3" style="width:200;"><img src="<?=$figurarelatorio?>"></td>
		<td class="header"><?=$_header?></td>
		<td><a class="btbr20" href="<?=$_SERVER['REQUEST_URI']?>&reportexport=csv" target="_blank">Exportar .csv</a></td>
	</tr>
	<tr>
		<td class="subheader">(<?=$strs?>)</td>
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
	while ($_i < $_numcolunas) {
	    $_metacmp = mysql_fetch_field($_resultados, $_i);
	    if (!$_metacmp) {
	        die("Nenhuma informacao de design retornou do SQL de Resultados");
	    }
	    /* Escrever na tela os parametros de cada campo
	    echo "
		blob:         $_metacmp->blob
		max_length:   $_metacmp->max_length
		multiple_key: $_metacmp->multiple_key
		name:         $_metacmp->name
		not_null:     $_metacmp->not_null
		numeric:      $_metacmp->numeric
		primary_key:  $_metacmp->primary_key
		table:        $_metacmp->table
		type:         $_metacmp->type
		default:      $_metacmp->def
		unique_key:   $_metacmp->unique_key
		unsigned:     $_metacmp->unsigned
		zerofill:     $_metacmp->zerofill
		";*/

	    $_arridxcol[$_i] = $_metacmp->name;
	    
	    //echo($arrRep["_filtros"][$_metacmp->name]["rotulo"]);
	    
	    if($arrRep["_filtros"][$_metacmp->name]["visres"] == 'Y'){
	    	//A VIRGULA E SO ENTRE OS VALORES NO INICIO DA LINHA E NO FINAL NÃO TEM VIRGULA
	    	if(!empty($conteudoexport)){
	    		$conteudoexport.=";";
	    	}
	    	
			$strtabheader .= "<td class='header'>" . $arrRep["_filtros"][$_metacmp->name]["rotulo"] ."</td>";
	    }	$conteudoexport.= "\"".$arrRep["_filtros"][$_metacmp->name]["rotulo"]."\"";// GRAVA O ROTULO DOS CABEÇALHOS NA VARIAVEL PARA GERAR O CSV
	    $_i++;
	}//while ($_i < $_numcolunas) {
	
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

    while ($_row = mysql_fetch_array($_resultados)){
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
    	while ($_i < $_numcolunas) {
		$_stralign="";
		$_strvlrhtml="";

    		$_nomecol = $_arridxcol[$_i];
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
    			if($arrRep["_filtros"][$_nomecol]["tsum"]=='Y'){
    				$_arrsoma[$_tab][$_nomecol] = $_arrsoma[$_tab][$_nomecol] + $_row[$_i];
    			}
    			
				/*
				 * Trata campo de longtext
				 */
				if($arrRep["_filtros"][$_nomecol]["datatype"]=='longtext'){
					$_strvlrhtml = nl2br($_row[$_i]);
				}elseif($arrRep["_filtros"][$_nomecol]["datatype"]=='datetime'){
					$_strvlrhtml = validadatadbweb($_row[$_i]);
				}elseif($arrRep["_filtros"][$_nomecol]["datatype"]=='date'){
					$_strvlrhtml = dma($_row[$_i]);
				}else{
					$_strvlrhtml = $_row[$_i];
				}
				
				$_strvlrhtml=aplicaMascara($arrRep["_filtros"][$_nomecol]["mascara"], $_strvlrhtml);					

				if (is_numeric($_row[$_i])){
					$total[$_i] = $total[$_i] + $_row[$_i];
				}
				//SE FOR UM NOVO <TD> ELE NÃO COMEÇA COM VIRGULA NO CSV
				if($_i>0){
					$conteudoexport.=";";//COLOCA A VIRGULA ENTRE OS VALORES 
				}
				
				$conteudoexport.="\"".$_strvlrhtml."\"";//GRAVA O VALOR DO CAMPO PARA GERAR O CSV ENTRE ASPAS

				//Se o hyperlink não estiver vazio ele monta o link
    			if(!empty($arrRep["_filtros"][$_nomecol]["hyperlink"])){
    				//O HREF contém uma barra ('/') o que significa que é uma URL relativa, que vai desconsiderar qualquer pasta informada neste link
    				$_hyperlink="<a target=_blank href='/".$arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$_i]."'>".$_strvlrhtml."</a>";
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

	if(!empty($_arrsoma)){
?>		
		<tr class="res">
			<td colspan="500" class="inv">&nbsp;</td>
		</tr>
		<tr class="res">
<?		
		$_y=0;
		while ($_y < $_numcolunas) {
			$_stralign="";
			$_strvlrhtml="";
    		$_nomecol = $_arridxcol[$_y];
		
    		if($arrRep["_filtros"][$_nomecol]["visres"] == 'Y' and $arrRep["_filtros"][$_nomecol]["tsum"]=='Y'){ 
    		
    			if($arrRep["_filtros"][$_nomecol]["tsum"]=='Y'){
    				$tipocalc="Soma:";
    			}
    			echo("<td class=\"tot\">");
    			echo($tipocalc." ".$_arrsoma[$_tab][$_nomecol]);
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
	if(!empty($_arrhlcond[$_pagpsq])){
?>
	<fieldset style="width:0%;padding: 0px;"><legend>Legenda:</legend>
	<table>
<?
		foreach ($_arrhlcond[$_pagpsq] as $_fldcond => $_cond) {
?>
	<tr style="padding: 0px;margin: 0px;">
		<td style="padding: 0px;margin: 0px;border:1px solid gray; background-color:<?=$_cond["cor"]?>">&nbsp;&nbsp;&nbsp;&nbsp;<td>
		<td style="padding: 0px;margin: 0px;border:none;">&nbsp;<td>
		<td style="padding: 0px;margin: 0px;border:none;" nowrap><?=$_cond["legenda"]?><td>

	</tr>
<?
		}
?>
	</table>
	</fieldset>
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
	header("Content-type: text/csv; charset=UTF-8");
	header("Content-Disposition: attachment; filename=".$infilename.".csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	echo iconv('UTF-8', 'ISO-8859-1', $conteudoexport);
	
}
?>
<style>
.header{
	/* width:<?=100/$_numcolunas;?>%; */
	border-collapse: collapse;
}
hr{
 border: none;
    height: 1px;
    /* Set the hr color */
    color: silver; /* old IE */
    background-color: silver; /* Modern Browsers */
}
</style>
<?=$total[0];?> - <?=$total[1];?> - <?=$total[2];?> 