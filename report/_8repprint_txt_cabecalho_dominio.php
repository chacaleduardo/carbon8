<?
include_once("../inc/php/validaacesso.php");
// CONTROLLERS
require_once(__DIR__."/../form/controllers/menurelatorio_controller.php");
require_once(__DIR__."/../form/controllers/empresa_controller.php");

baseToGet($_GET["_filtros"]);

if (!empty($_GET["reportexport"])) {
	ob_start(); //não envia nada para o browser antes do termino do processamento
}

// $sql_check_LP = "select 1 from carbonnovo._lprep where idlp in (".getModsUsr("LPS").") and idrep= ".$_GET["_idrep"]."";
// $chk = d::b()->query($sql_check_LP) or die('ERRO AO VERIFICAR LP');

$verificarLp = MenuRelatorioController::verificarLpPorIdLpEIdRep(getModsUsr("LPS"), $_GET["_idrep"]);
if (!$verificarLp) {
	die('<div>Você não Possui permissão para acessar esse Relatório</div>');
}

$_idrep = $_GET["_idrep"];

if ($_GET["relatorio"]) {
	$_idrep = $_GET["relatorio"];
}

if (empty($_idrep)) {
	die("Relat&oacute;rio n&atilde;o informado!");
}

if ($_idrep == 21) {
	// d::b()->query("SET sql_mode = 'NO_UNSIGNED_SUBTRACTION';");
	MenuRelatorioController::alterarSQLMode('NO_UNSIGNED_SUBTRACTION');
}
//Recupera a definicao das colunas da view ou table default da pagina
// $arrRep=getConfRelatorio($_idrep);
$arrRep = MenuRelatorioController::buscarConfiguracaoRelatorioPorIdRep($_idrep);
//Facilita a utilização do array
$arrRep = $arrRep[$_idrep];

$_rep = $arrRep["rep"];
$_header = $arrRep["header"];
$_footer = $arrRep["footer"]; // Não usa
$_showfilters = $arrRep["showfilters"]; // Não usa
$_tab = $arrRep["tab"];
$_newgrouppagebreak = $arrRep["newgrouppagebreak"]; // Não usa
$_pbauto = $arrRep["pbauto"];
$_showtotalcounter = $arrRep["showtotalcounter"];
$_compl = $arrRep["compl"];
$_descr = $arrRep["descr"];
$_rodape = $arrRep["rodape"];
$_chavefts = $arrRep["chavefts"]; // Não usa
$_tabfull = $arrRep["tabfull"]; // Não usa
$valorPosFixado = $arrRep['valorposfixado'] ?? '';

$eixoX = "";
$eixoY = [];
$arrayGrafico = array();
$tipoGraphRelatorio = $arrRep["tipograph"];
?>
<html>

<head>
	<? require_once(__DIR__."/_8repprint_head.php") ?>
</head>

<body>
	<?
	if (!empty($_GET)) {
		if (!empty($_GET['mes']) && !empty($_GET['ano'])) {
			$_GET["datapagto_1"] = $_GET['ano'].'-'.$_GET['mes'].'-'."01";
			$_GET["datapagto_2"] = $_GET['ano'].'-'.$_GET['mes'].'-'.cal_days_in_month(CAL_GREGORIAN, $_GET['mes'], $_GET['ano']);
			$datapagto = "01/".$_GET['mes']."/".$_GET['ano'];
		}

		$_sqlwhere = " where ";
		$_and = "";
		$_iclausulas = 0;

		// while (list($_col, $_val) = each($_GET)) {
		foreach ($_GET as $_col => $_val) {

			if ($_col == 'mes' || $_col == 'ano') {
				continue; //Pular o Get do Ano
			}

			$_between = false;
			$_val = urldecode($_val);
			if (!empty($_val) && ($_col != "_modulo") && ($_col != "_rep") && (substr($_col, -2) != "_2")) {

				//Montar clausula para colunas between
				if (substr($_col, -2) == "_1") {
					$_col = substr($_col, 0, -2); //Transforma do nome do campo para capturar informacoes de tipo
					$_colval1 = $_GET[$_col."_1"];
					$_colval2 = $_GET[$_col."_2"];
					$arrRep["_filtros"][$_col]["psqkey"] = 'Y';
					$arrRep["_filtros"][$_col]["inseridomanualmente"] = 'N';
					if (MenuRelatorioController::verificarData($_colval2)) {
						$_colval2 = $_colval2.' 23:59:59';
					}
					$_between = true;
				}

				$_datatype 	= 	$arrRep["_filtros"][$_col]["datatype"];
				$_psqkey 	= 	$arrRep["_filtros"][$_col]["psqkey"];
				$_entre 	= 	$arrRep["_filtros"][$_col]["entre"];
				$_insmanual = 	$arrRep["_filtros"][$_col]["inseridomanualmente"];
				$_like 		= 	$arrRep["_filtros"][$_col]["like"];
				$_inval 	= 	$arrRep["_filtros"][$_col]["inval"];
				$_in 		= 	$arrRep["_filtros"][$_col]["in"];
				$_findinset	= 	$arrRep["_filtros"][$_col]["findinset"];

				//Montar clausula somente para campos que estejam marcados como psqkey
				if ($_psqkey == "Y" && $_insmanual == "N") {
					if ($_between) {
						$_sqlwhere .= $_and."(".$_col." between '".$_GET["datapagto_1"]."' and '".$_GET["datapagto_2"]."')";
					} else {
						if ($_like == 'Y') {
							if ($_datatype == 'text') {
								$_datatype = 'varchar';
							}
							$_sqlwhere .= $_and.$_col." like '%".substr(substr(evaltipocoldb($_tab, $_col, $_datatype, $_val), 1), 0, -1)."%'";
						} else if ($_findinset == 'Y') {
							if ($_datatype == 'text') {
								$_datatype = 'varchar';
							}
							$_sqlwhere .= $_and." find_in_set(".$_val." , ".$_col.") ";
						} else if ($_inval == 'Y') {
							if ($_datatype == 'text') {
								$_datatype = 'varchar';
							}
							$_value = null;
							$_val = explode(',', $_val);
							if (count($_val) >= 1) {
								$arrlenght = count($_val) - 1;
								foreach ($_val as $key => $value) {
									if ($key == $arrlenght) {
										$virg = '';
									} else {
										$virg = ',';
									}
									$_value .= "'".$value."'".$virg;
								}
							}

							$_sqlwhere .= $_and.$_col." in (".$_value.")";
						} else if ($_in == 'Y') {
							if ($_datatype == 'text') {
								$_datatype = 'varchar';
								$_sqlwhere .= $_and.$_col." in (".substr(substr(evaltipocoldb($_tab, $_col, $_datatype, $_val), 1), 0, -1).")";
							} else {
								$_sqlwhere .= $_and.$_col." in (".$_val.")";
							}
						} else {
							$_sqlwhere .= $_and.$_col." = ".evaltipocoldb($_tab, $_col, $_datatype, $_val);
						}
					}

					$_and = " and ";
					$_iclausulas++;
				} else {
					echo "\n<!-- Campo Ignorado: ".$_col." - Manual: ".$_insmanual." -->";
				}
			}
		}

		$_sqldata = '';
		require_once(__DIR__."/scripts/_8repprint_montaclausuladata.php");

		// Definir Preferencias do usuario
		require_once(__DIR__."/scripts/_8repprint_ajustaprefusuario.php");

		//Abre variavel de sessao para que ela possa ser acessada pelo modulo de interceptacao de eventos
		//Isto permitira saber se existe clausula where ou nao
		$_SESSION["SEARCH"]["CLAUSULAS"] = (string)$_iclausulas;

		require_once(__DIR__."/scripts/_8repprint_178_montarclausulaidempresa.php");

		if (trim($_compl) != '') {
			$_sqlresultado .= ' '.trim($_compl);
		}

		// RETRINGIR CONSULTA A UNIDADE MARCADA NA LP-------------------------------------------------------------
		$lps = getModsUsr("LPS");
		// $sqlFlgUnidade="Select flgunidade from "._DBCARBON."._lprep where idrep=".$_idrep." and idlp in(".$lps.") order by flgunidade desc";
		// $rrep = d::b()->query($sqlFlgUnidade) or die("Erro ao verificar unidade no relatorio: ".mysql_error(d::b()));
		$lpRep = MenuRelatorioController::buscarLpRepPorIdRepEIdLps($_idrep, $lps);

		require_once(__DIR__."/scripts/_8repprint_restringirconsultaaunidademarcadanalp.php");


		// RETRINGIR CONSULTA A HIERARQUIA ORGANOGRAMA QUANDO MARCADO NA LPREP-------------------------------------------------------------
		require_once(__DIR__."/scripts/_8repprint_restringirconsultaaoorganogramapelalp.php");

		//--------------------- Validação para filtro com Plantel - LTM (28-07-2020 - 363014) ----------------
		$arrFiltros = retarraytabdef($_tab);
		require_once(__DIR__."/scripts/_8repprint_validafiltroplantel.php");

		$strselectfields = "";
		$strord = "";
		$strvirg = "";

		//Concatenar campos para o select
		require_once(__DIR__."/scripts/_8repprint_concatenarcamposvisiveis.php");

		//Concatenar clausulas para Order By
		require_once(__DIR__."/scripts/_8repprint_concatenarclausulaorderby.php");
		$strvirg = "";

		//Concatenar clausulas para GROUP BY
		require_once(__DIR__."/scripts/_8repprint_ordenarpelocampoordseq.php");

		// RETRINGIR CONSULTA A HIERARQUIA ORGANOGRAMA QUANDO MARCADO NA LPREP-------------------------------------------------------------
		$sqlflgidpessoa = "Select flgidpessoa, flgcontaitem from "._DBCARBON."._lprep where idrep=".$_idrep." and idlp in(".$lps.") and flgcontaitem = 'Y'  order by flgidpessoa desc";

		$rrep = d::b()->query($sqlflgidpessoa) or die("Erro ao verificar flgcontaitem  no relatorio: ".mysql_error(d::b()));
		if (mysql_num_rows($rrep) >= 1 && $_SESSION["SESSAO"]["MIGRACAO"]["CONTAITEM"] != '') {
			$_and_idempresa .= " and idcontaitem in (".$_SESSION["SESSAO"]["MIGRACAO"]["CONTAITEM"].")";
		}

		/****************************************************************************
		 * CONCATENACAO PRINCIPAL DO SELECT A SER EXECUTADO PARA O RELATORIO        *
		 ****************************************************************************/
		$relatorios = MenuRelatorioController::buscarRelatorioDinamico($strselectfields, ($_sqlresultado.$_sqldata.$_and_idempresa.$str_fts.$strgrp.$strord));
		if ($relatorios === false) {
			die('<b>Falha na execucao da Consulta para o Report:</b> '.mysql_error()."<br>".$_sqlresultado);
		}

		$_arrtab = retarraytabdef($_tab);

		$_i = 0;
		$_numcolunas = count($relatorios[0]);
		$_ipagpsqres = count($relatorios);
		$strs = "Nenhum Registro encontrado";

		if ($_ipagpsqres == 1) {
			$strs = $_ipagpsqres." Registro encontrado";
		} elseif ($_ipagpsqres > 1) {
			$strs = $_ipagpsqres." Registros encontrados";
		}

		$_nomeimpressao = "[".md5(date('dmYHis'))."] gerada em [".date(" d/m/Y H:i:s")."]";

		// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
		$empresa = EmpresaController::buscarEmpresaPorIdEmpresa($_GET['_idempresa'] ?? cb::idempresa());
		$figurarelatorio = $empresa["logosis"];

		?>
		<div style="display:none; text-align: left; font-size: 9px;" class="n_linhas"><span id="nlinha"><?= $strs ?></span></div>
		<table class="tbrepheader">
			<tr>
				<td rowspan="3" style="width:50px;"><img style="width:100%;" src="<?= $figurarelatorio ?>"></td>
				<td class="header"><? //=$_header
									?></td>
				<td><a class="btbr20 no-print" style="right:325px;" href="<?= $_SERVER['REQUEST_URI'] ?>&reportexportofx=ofx" target="_blank">Download .ofx</a></td>
				<td><a class="btbr20 no-print" style="right:165px;" href="<?= $_SERVER['REQUEST_URI'] ?>&reportexport=csv" target="_blank">Download .csv</a></td>
				<td><a class="btbr20 no-print" href="<?= $_SERVER['REQUEST_URI'] ?>&reportexporttxt=txt" target="_blank">Download .txt</a></td>
			</tr>
			<tr>
				<td class="subheader">
					<h2><?= ($_rep); ?></h2>
					(<?= $strs ?>)
				</td>
			</tr>
		</table>
		<br>
		<fieldset class="fldsheader">
			<legend>Início da Impressão <?= $_nomeimpressao ?></legend>
		</fieldset>
		<?
		/*
		* MONTA O CABECALHO
		*/
		$conteudoexport; // guarda o conteudo para exportar para csv
		$conteudoexporttxt; // guarda o conteudo para exportar para txt
		$conteudoexporttxtitens; // guarda o conteudo para exportar para txt
		$strtabheader = "\n<thead><tr class='header'>";
		//coloca um contador numerico do lado esquerdo da tabela, isto é repetido também na montagem de cada linha da tabela
		if ($_showtotalcounter == "Y") {
			$strtabheader .= "<td class='tdcounter'></td>";
		}

		// while ($_i < $_numcolunas) {
		foreach (array_keys($relatorios[0]) as $key => $coluna) {
			if ($arrRep["_filtros"][$arrRep["_colvisiveis"][$key + 1]]["visres"] == 'Y') {
				//A VIRGULA E SO ENTRE OS VALORES NO INICIO DA LINHA E NO FINAL NÃO TEM VIRGULA
				if (!empty($conteudoexport) || !empty($conteudoexporttxtitens)) {
					$conteudoexport .= ";";
				}
				if (strpos(strtolower($arrRep["_filtros"][$arrRep["_colvisiveis"][$key + 1]]["rotulo"]), ' as ') !== false) {
					$val = explode(' as ', strtolower($arrRep["_filtros"][$arrRep["_colvisiveis"][$key + 1]]["rotulo"]));
					$arrRep["_filtros"][$arrRep["_colvisiveis"][$key + 1]]["rotulo"] = $val[1];
				}

				$strtabheader .= "<td class='header' id='".MenuRelatorioController::urlAmigavel(str_replace('`', '', $arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["rotulo"]))."' style=\"white-space: nowrap; text-align:".$arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["align"]."\">".str_replace('`', '', $arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["rotulo"])."<br>&nbsp;<i class='fa fa-arrow-down pointer' title='Ordenar Crescente' style='font-size: 0.8em; opacity: 0;' attr='desc'></i>&nbsp;<i class='fa fa-arrow-up pointer' title='Ordenar Decrescente' style='font-size: 0.8em; opacity: 0;' attr='asc'></i></td>";
			}
			if (!empty($arrRep["_filtros"][$coluna]["rotulo"])) {
				$conteudoexport .= "\"".$arrRep["_filtros"][$coluna]["rotulo"]."\""; // GRAVA O ROTULO DOS CABEÇALHOS NA VARIAVEL PARA GERAR O CSV
			} else {
				$conteudoexport .= "\"".str_replace('`', '', $arrRep["_filtros"][$arrRep["_colvisiveis"][$key + 1]]["rotulo"])."\""; // GRAVA O ROTULO DOS CABEÇALHOS NA VARIAVEL PARA GERAR O CSV
			}
			$_i++;
		}

		$conteudoexport .= "\n"; //QUEBRA DE LINHA NO CONTEUDO CSV

		$strtabheader .= "</tr></thead><tbody>";

		/*
		* Variaveis para cabecalho do report
		*/
		$strpagini = "\n<fieldset class='fldsheader'><legend>Impressão ".$_nomeimpressao."</legend></fieldset>";
		$strtabini = "\n<table id='restbl' class='normal'>";
		$strtabheader = $strtabheader;

		/*
		* MONTA A TABELA
		*/
		$_ilinha = 0; //armazena o ttotal de registros
		$_ilinhaquebra = 0; //armazena parialmente o numero de registros se houver quebra automatica configurada
		$_graphLinha = 0;
		$strnewpage = "<span class='newreppage'></span>";

		foreach ($relatorios as $_row) {
			$_ilinha++;
			$_i = 0;

			//verifica se o parametro de quebra automatica esta configurado. caso negativo escreve o cabecalho somente 1 vez. E tambem se for a primeira linha, desenha o cabecalho pelo 'else'
			if ($_pbauto > 0 && $_ilinha > 1) {
				//verifica quando é que uma nova quebra sera colocada
				if ($_pbauto > ($_ilinhaquebra + 1)) {
					$_ilinhaquebra++;
				} else {
					echo "\n</table>";
					echo $strnewpage; //QUEBRA A PAGINA
					echo $strpagini;
					echo $strtabini;
					echo $strtabheader;
					$_ilinhaquebra = 0;
				}
			} else {
				//Escreve o cabecalho somente uma vez
				if ($_ilinha == 1) {
					echo $strpagini;
					echo $strtabini;
					echo $strtabheader;
				}
			}

			###################################### Escreve linhas da <Table>

			echo "\n<tr class=\"res\" ".$_link." ".$_strhlcolor.">";
			//coloca um contador numerico do lado esquerdo da tabela
			if ($_showtotalcounter == "Y") {
				echo "<td class='tdcounter'>".$_ilinha."</td>";
			}

			if ($_numlinha == "Y") {
				?>
				
				<td style="background-color:none;"><?= $_ilinha ?></td>
				<?
			}

			/*
			* Montagem dos <TD>s
			*/
			foreach (array_keys($_row) as $key => $coluna) {
				$_attrHtml = "";
				$_stralign = "";
				$_strvlrhtml = "";
				$_nomecol = $arrRep["_colvisiveis"][$key + 1];
				$_colorlink = "";
				$_hyperlink = "";
				$_corfont = "";
				$_corfontfim = "";
				//Escreve Campo
				if ($arrRep["_filtros"][$_nomecol]["visres"] == 'Y') {
					//ajusta o alinhamento dentro da celula. caso esquerda. nao preencher para nao gerar html desnecessariamente
					if ($arrRep["_filtros"][$_nomecol]["align"] != "left") {
						$_stralign = "align='".$arrRep["_filtros"][$_nomecol]["align"]."'";
					}

					//se for para somar o valor do campo
					if ($arrRep["_filtros"][$_nomecol]["acsum"] == 'Y') {

						//Cria classe de somatoria para fazer a soma com JS no modulo menurelatorio
						$_attrHtml = "acsum='$_nomecol' filtervalue='$_row[$coluna]'";

						$_arrsoma[$_tab][$_nomecol] = $_arrsoma[$_tab][$_nomecol] + $_row[$coluna];
					}

					//se for para somar o valor do campo
					if ($arrRep["_filtros"][$_nomecol]["acavg"] == 'Y') {

						$_arrsomaavg[$_tab][$_nomecol] = $_arrsomaavg[$_tab][$_nomecol] + $_row[$coluna];
					}

					/*
					* Trata colunas inseridas manualmente para que tenham um datatype
					*/
					if (empty($arrRep["_filtros"][$_nomecol]["datatype"])) {
						$t = preg_replace("/[^0-9.]/", "", $_row[$coluna]);
						($t != $_row[$coluna]) ? $arrRep["_filtros"][$_nomecol]["datatype"] = "varchar" : $arrRep["_filtros"][$_nomecol]["datatype"] = "double";
					}

					/*
					* Trata campo de longtext
					*/
					if ($arrRep["_filtros"][$_nomecol]["datatype"] == 'longtext') {
						$_strvlrhtml = nl2br($_row[$coluna]);
					} elseif ($arrRep["_filtros"][$_nomecol]["datatype"] == 'datetime') {
						$_strvlrhtml = validadatadbweb($_row[$coluna]);
					} elseif ($arrRep["_filtros"][$_nomecol]["datatype"] == 'date') {
						$_strvlrhtml = dma($_row[$coluna]);
					} elseif ($arrRep["_filtros"][$_nomecol]["datatype"] == 'decimal' || $arrRep["_filtros"][$_nomecol]["datatype"] == 'double') {
						$graficoY = $_row[$coluna];
						$_strvlrhtml = number_format($_row[$coluna], 2, ',', '.');
					} else {
						$_strvlrhtml = $_row[$coluna];
					}

					$_attrHtml .= "datatype='".$arrRep["_filtros"][$_nomecol]["datatype"]."' mascara='".$arrRep["_filtros"][$_nomecol]["mascara"]."'  eixografico='".$arrRep["_filtros"][$_nomecol]["eixograph"]."' col='".$_nomecol."'  ";

					$_strvlrhtml = aplicaMascara($arrRep["_filtros"][$_nomecol]["mascara"], $_strvlrhtml);



					$arrayGrafico[$_graphLinha][$_nomecol] = $_row[$coluna];

					if (is_numeric($_row[$coluna])) {
						$total[$_i] = $total[$_i] + $_row[$coluna];
					}
					//SE FOR UM NOVO <TD> ELE NÃO COMEÇA COM VIRGULA NO CSV
					if ($_i > 0) {
						$conteudoexport .= ";"; //COLOCA A VIRGULA ENTRE OS VALORES 
						$conteudoexporttxtitens .= ";"; //COLOCA A VIRGULA ENTRE OS VALORES 
					}


					if (!empty($arrRep["_filtros"][$_nomecol]["eixograph"])) {
						if ($arrRep["_filtros"][$_nomecol]["eixograph"] == 'X') {
							$eixoX = $_nomecol;
						} else if ($arrRep["_filtros"][$_nomecol]["eixograph"] == 'Y') {
							$eixoY[] = $_nomecol;
						}
					}

					//Verifica se Possui Máscara de Moeda antes de Jogar no csv.
					if ($arrRep["_filtros"][$_nomecol]["mascara"] == 'MOEDA') {
						$conteudoexport .= strip_tags($_row[$coluna]);
						$conteudoexporttxtitens .= strip_tags($_row[$coluna]);
					} else {
						$conteudoexport .= "\"".str_replace(array("\r\n", "\n", "\r"), ' ', strip_tags($_strvlrhtml))."\""; //GRAVA O VALOR DO CAMPO PARA GERAR O CSV ENTRE ASPAS
						$conteudoexporttxtitens .= "".str_replace(array("\r\n", "\n", "\r"), ' ', strip_tags($_strvlrhtml)).""; //GRAVA O VALOR DO CAMPO PARA GERAR O TXT ENTRE ASPAS
					}

					//Se o hyperlink não estiver vazio ele monta o link
					if (!empty($arrRep["_filtros"][$_nomecol]["hyperlink"])) {
						if ($_strvlrhtml != '0.00') {
							//O HREF contém uma barra ('/') o que significa que é uma URL relativa, que vai desconsiderar qualquer pasta informada neste link
							if (strpos($arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$coluna], 'pk=')) {

								$arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$coluna];
								$valor = explode('pk=', $arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$coluna]);
								$valor = explode('&', $valor[1]);
								$campo = $_row[$valor[0]];

								$_hyperlink = "<a target=_blank href='/".$arrRep["_filtros"][$_nomecol]["hyperlink"].$campo."'>".$_strvlrhtml."</a>";
							} else {
								$_hyperlink = "<a target=_blank href='/".$arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$coluna]."'>".$_strvlrhtml."</a>";
							}

							$_colorlink = "class=\"link\" ";
							$_corfont = "<font color='Blue'>";
							$_corfontfim = "</font>";
						} else {
							$_hyperlink = strip_tags($_strvlrhtml);
						}
					}

					//Finalmente: desenha o campo na tela
					if (!empty($arrRep["_filtros"][$_nomecol]["hyperlink"])) {
						echo "<td ".$_attrHtml." ".$_stralign." >".$_hyperlink."</td>";
					} else {
						echo "<td ".$_attrHtml." ".$_stralign." ".$_hyperlink." ".$_colorlink.">".$_corfont.$_strvlrhtml.$_corfontfim."</td>";
					}
				}
				$_i++;
			}
			$conteudoexport .= "\n"; //QUEBRA A LINHA DO CONTEUDO CSV
			$conteudoexporttxtitens .= "\n"; //QUEBRA A LINHA DO CONTEUDO TXT
		}

		$agencia_dominio = traduzid('agencia', 'idagencia', 'agencia_dominio', $_GET['idagencia']);
		//Cabeçalho do TXT do Domínio
		$conteudoexporttxt = "Cabeçalho;;;;;\n";
		$conteudoexporttxt .= "Competencia;$datapagto;Conta Banco;$agencia_dominio;Saldo Inicial;0\n";
		$conteudoexporttxt .= $conteudoexporttxtitens;

		?>
		</tr>
		<?
		if (!empty($_arrsoma) or !empty($_arrsomaavg)) {
		?>
			<tr class="res bottonLine">
				<td colspan="500" class="inv">&nbsp;</td>
			</tr>
			<tr class="res bottonLine">
				<?

				$_y = 0;
				foreach (array_keys($relatorios[0]) as $key => $coluna) {
					$_stralign = "";
					$_strvlrhtml = "";
					$_nomecol = $arrRep["_colvisiveis"][$key + 1];

					if ($arrRep["_filtros"][$_nomecol]["visres"] == 'Y' && $arrRep["_filtros"][$_nomecol]["acsum"] == 'Y') {

						echo ("<td class=\"tot\" style=\"text-align:".$arrRep["_filtros"][$_nomecol]["align"]."\">");
						echo (aplicaMascara($arrRep["_filtros"][$_nomecol]["mascara"], $_arrsoma[$_tab][$_nomecol]));
						echo ("</td>");
					} elseif ($arrRep["_filtros"][$_nomecol]["visres"] == 'Y' && $arrRep["_filtros"][$_nomecol]["acavg"] == 'Y') {

						echo ("<td class=\"tot\" style=\"text-align:".$arrRep["_filtros"][$_nomecol]["align"]."\">");
						echo (aplicaMascara($arrRep["_filtros"][$_nomecol]["mascara"], ($_arrsomaavg[$_tab][$_nomecol] / $_ilinha)));
						echo ("</td>");
					} else {
						echo ("<td class=\"inv\"></td>");
					}
					$_y++;
				}
				?>
			</tr>
		<?
		}
		?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="<?= $_numcolunas; ?>">
					<div style="width:100%">
						<div style="width:50%;float:left;font-size:9px;"><br>
							<?= htmlspecialchars_decode($_rodape); ?>
						</div>
						<div style="width:50%;float:left;font-size:9px;"><br>
							<? if ($_descr) { ?><strong>LEGENDA:</strong><br>
								<?= nl2br($_descr); ?>
							<? } ?>
						</div>
					</div>
				</td>
			</tr>
		</tfoot>
		</table>

		<!-- Armazena o titulo da consulta para ser usada como titulo do arquivo csv no modulo menurelatorio -->
		<div id="tlt" style="display: none;"><?= $_rep.' '.$_GET["_fds"] ?></div>
		<?
			/*
		* Desenha a legenda
		*/

		}

		if (defined("_RODAPEDIR")) $varfooter = _RODAPEDIR;
		?>
		<footer>

		</footer>
		<fieldset class="fldsfooter">
			<legend>Fim da Impressão <?= $_nomeimpressao." ".$varfooter ?></legend>
		</fieldset>
	</body>
</html>
<?
if (!empty($_GET["reportexport"])) {
	if ($_GET["_debug"] !== "true") {
		ob_end_clean(); //não envia nada para o browser antes do termino do processamento
	}
	/* Gerar o nome do arquivo para exportar
	 * Substitui qualquer caractere estranho pelo sinal de '_'
	 * Caracteres que NAO SERAO substituidos:
	 *   - qualquer caractere de A a Z (maiusculos)
	 *   - qualquer caracteres de a a z (minusculos)
	 *   - qualquer caractere de 0 a 9
	 *   - e pontos '.'
	 */
	$infilename = empty($_header) ? $_rep : $_header;
	$infilename = preg_replace("/[^A-Za-z0-9s.]/", "", $infilename);
	//gera o csv
	//LTM - 05-10-2020 - 375916: Alterado pois não estava imprimindo no excel e no libre estava desconfigurando os caracteres especiais. 
	//Devido a correção dos resultados congelados no banco não há necessidade de usar o iconv

	header('Content-Encoding: UTF-8');
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	if ($_GET["_debug"] !== "true") {
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

if (!empty($_GET["reportexporttxt"])) {
	if ($_GET["_debug"] !== "true") {
		ob_end_clean(); //não envia nada para o browser antes do termino do processamento
	}
	/* Gerar o nome do arquivo para exportar
	 * Substitui qualquer caractere estranho pelo sinal de '_'
	 * Caracteres que NAO SERAO substituidos:
	 *   - qualquer caractere de A a Z (maiusculos)
	 *   - qualquer caracteres de a a z (minusculos)
	 *   - qualquer caractere de 0 a 9
	 *   - e pontos '.'
	 */
	$infilename = empty($_header) ? $_rep : $_header;
	$infilename = preg_replace("/[^A-Za-z0-9s.]/", "", $infilename);
	//gera o csv
	//LTM - 05-10-2020 - 375916: Alterado pois não estava imprimindo no excel e no libre estava desconfigurando os caracteres especiais. 
	//Devido a correção dos resultados congelados no banco não há necessidade de usar o iconv

	header('Content-Encoding: UTF-8');
	header('Content-Type: text/txt; charset=utf-8');
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	if ($_GET["_debug"] !== "true") {
		header("Content-Disposition: attachment; filename=".$infilename.".txt");
	}
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo "\xEF\xBB\xBF";

	echo $conteudoexporttxt;
	exit();
}

if (!empty($_GET["reportexportofx"])) {
	if ($_GET["_debug"] !== "true") {
		ob_end_clean(); //limpa o conteúdo
	}
	//vamos pegar os dados da conta
	$sql = "SELECT * FROM agencia WHERE idagencia = {$_GET["idagencia"]}";
	$agencia = d::b()->query($sql) or die('ERRO AO COLETAR AGENCIA');

	$dadosagencia = mysql_fetch_assoc($agencia);

	$bankId = $dadosagencia['nbanco'];
	$accountId = str_pad(str_replace('-', '', $dadosagencia['nconta']), 12, "0", STR_PAD_LEFT); //"000123456789";
	$accountType = "CHECKING"; // CHECKING para conta corrente, SAVINGS para poupança
	$currency = "BRL";
	$startDate = str_pad(str_replace('-', '', $_colval1), 14, "0"); //"20240101000000"; // Formato: YYYYMMDDHHMMSS
	$endDate = str_pad(str_replace('-', '', $_colval2), 14, "0"); //"20240101000000"; // Formato: YYYYMMDDHHMMSS

	foreach ($relatorios as $relatorio) {
		$transactions[] =
			[
				"date" => str_pad(str_replace('-', '', $relatorio["datapagto"]), 14, "0"), // Formato: YYYYMMDDHHMMSS
				"amount" => $relatorio['soma'] != '' ? $relatorio['soma'] * 1 : $relatorio['subtrai'] * -1,
				"id" => $relatorio['numerodocumento'],
				"memo" => preg_replace('/\d/', '', $relatorio['historico'])
			];
	}

	// Cabeçalho do arquivo OFX
	$header = "OFXHEADER:100\nDATA:OFXSGML\nVERSION:102\nSECURITY:NONE\nENCODING:USASCII\nCHARSET:1252\nCOMPRESSION:NONE\nOLDFILEUID:NONE\nNEWFILEUID:NONE\n\n";

	// Corpo do arquivo OFX
	$body = "<OFX>
	  <SIGNONMSGSRSV1>
		<SONRS>
		  <STATUS>
			<CODE>0</CODE>
			<SEVERITY>INFO</SEVERITY>
		  </STATUS>
		  <DTSERVER>$endDate</DTSERVER>
		  <LANGUAGE>POR</LANGUAGE>
		</SONRS>
	  </SIGNONMSGSRSV1>
	  <BANKMSGSRSV1>
		<STMTTRNRS>
		  <STATUS>
			<CODE>0</CODE>
			<SEVERITY>INFO</SEVERITY>
		  </STATUS>
		  <STMTRS>
			<CURDEF>$currency</CURDEF>
			<BANKACCTFROM>
			  <BANKID>$bankId</BANKID>
			  <ACCTID>$accountId</ACCTID>
			  <ACCTTYPE>$accountType</ACCTTYPE>
			</BANKACCTFROM>
			<BANKTRANLIST>
			  <DTSTART>$startDate</DTSTART>
			  <DTEND>$endDate</DTEND>";

	// Adiciona transações ao corpo do arquivo
	foreach ($transactions as $txn) {
		$body .= "
			  <STMTTRN>
				<TRNTYPE>DEBIT</TRNTYPE>
				<DTPOSTED>{$txn['date']}</DTPOSTED>
				<TRNAMT>{$txn['amount']}</TRNAMT>
				<FITID>{$txn['id']}</FITID>
				<MEMO>{$txn['memo']}</MEMO>
			  </STMTTRN>";
	}

	// Finaliza o corpo do arquivo
	$body .= "
			</BANKTRANLIST>
		  </STMTRS>
		</STMTTRNRS>
	  </BANKMSGSRSV1>
	</OFX>";

	// Combina o cabeçalho e o corpo
	$ofxContent = $header.$body;

	// Nome do arquivo OFX
	$filename = "extratodominio.ofx";

	// Configura os headers para download
	header('Content-Encoding: UTF-8');
	header('Content-Type: application/x-ofx');
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Length: '.strlen($ofxContent));
	if ($_GET["_debug"] !== "true") {
		header('Content-Disposition: attachment; filename="'.$filename.'"');
	}
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	// Envia o conteúdo do arquivo OFX para o navegador
	echo $ofxContent;

	exit();
}

require_once 'graficos_relatorio.php';
?>

<script class="normal">
	function sortTable(e) {
		var th = e.target.parentElement;
		$(e.target).addClass("azul");
		$(th).addClass("ativo");
		$(e.target).siblings().removeClass("azul");
		$(th).siblings().removeClass("ativo");
		$(e.target.parentElement).siblings().each((e, o) => {
			$(o).children().removeClass('azul').css('opacity', '0')
		})
		var ordenacao = $(e.target).attr("attr");
		switch (ordenacao) {
			case 'asc':
				colunas = -1;
				break;
			case 'desc':
				colunas = 1;
				break;

			default:
				colunas = 1
				break;
		}

		var n = 0;
		while (th.parentNode.cells[n] != th) ++n;
		var order = th.order || 1;
		//th.order = -order;
		var t = this.closest("thead").nextElementSibling;
		var bottonLine = $(t.rows).filter('.bottonLine');

		t.innerHTML = Object.keys($(t.rows).not('.bottonLine'))
			.filter(k => !isNaN(k))
			.map(k => t.rows[k])
			.sort((a, b) => order * (isNaN(typed(a)) && isNaN(typed(b))) ? ((typed(a).localeCompare(typed(b)) > 0) ? colunas : -colunas) : (typed(a) > typed(b) ? colunas : -colunas))
			.map(r => r.outerHTML)
			.join('')

		function typed(tr) {

			var s = tr.cells[n].innerText;
			var dataType = tr.cells[n].attributes.datatype.value;

			debugger
			if (dataType == 'varchar') {

				if (!s || /^\s*$/.test(s)) {
					s = 'zzzzzzzzzzz';
				}

			} else if (dataType == 'decimal' || dataType == 'int' || dataType == 'double') {
				//trata números	

				s = s.replace('R$ ', '')
				s = s.replaceAll('.', '').replaceAll(',', '.')


				if (!s || /^\s*$/.test(s)) {
					s = '9999999999999';
				}

			}

			if (s.match(",")) {
				isNaN(s.replaceAll(",", ".")) ? s = s.toString() : s = s.replaceAll(",", ".")
			}
			if (isNaN(s) && s.match(/^[a-zA-Z]+/)) {
				var d = s;
				var date = d;
			} else {
				if (s.match("/") && s.match(/^[a-zA-Z]+/) == null) {

					var d = mda(s);
					var date = Date.parse(d);
				} else {
					var d = s;
					var date = d;
				}

			}
			if (!isNaN(date)) {
				return isNaN(date) ? s.toLowerCase() : Number(date);
			} else {
				if (!isNaN(s.replaceAll(",", '.'))) {
					return Number(s.replaceAll(",", '.'));
				} else {

					return s.toLowerCase();
				}
			}
		}

		$('#restbl tbody').append(bottonLine);
	}


	$('#restbl thead td i').on('click', sortTable);

	$('#restbl thead td').mouseover(function() {
		$(this).children().not("[id=cbOrdCres], [id=cbOrdDecr]").each((e, o) => {
			$(o).css("opacity", "1").addClass('hoverazul')
		})
	});

	$('#restbl thead td').mouseout(function() {
		$(this).children().not("[id=cbOrdCres], [id=cbOrdDecr]").each((e, o) => {
			if (!$(o).hasClass('azul')) {
				$(o).css("opacity", "0").removeClass('hoverazul')
			}
		})
	});
</script>