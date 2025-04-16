<script src="/inc/js/jquery/jquery-1.11.2.min.js"></script>
<?
include_once("../inc/php/validaacesso.php");
baseToGet($_GET["_filtros"]);

function seo_friendly_url($string)
{
	$string = str_replace(array('[\', \']'), '', $string);
	$string = preg_replace('/\[.*\]/U', '', $string);
	$string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
	$string = htmlentities($string, ENT_COMPAT, 'utf-8');
	$string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string);
	$string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/'), '-', $string);
	return strtolower(trim($string, '-'));
}
function containsDecimal($value)
{

	if (strpos($value, ".") !== false) {
		return true;
	}
	return false;
}
function verificaData($data)
{
	//cria um array
	$array = explode('/', $data);

	//garante que o array possue tres elementos (dia, mes e ano)
	if (count($array) == 3) {
		$dia = (int)$array[0];
		$mes = (int)$array[1];
		$ano = (int)$array[2];

		//testa se a data é válida
		if (checkdate($mes, $dia, $ano)) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}


if (!empty($_GET["reportexport"])) {
	ob_start(); //não envia nada para o browser antes do termino do processamento
}

$sql_check_LP = "select 1 from carbonnovo._lprep where idlp in (" . getModsUsr("LPS") . ") and idrep= " . $_GET["_idrep"] . "";
$chk = d::b()->query($sql_check_LP) or die('ERRO AO VERIFICAR LP');
if (mysqli_num_rows($chk) == 0) {
	die('<div>Você não Possui permissão para acessar esse Relatório</div>');
}


if ($_GET["relatorio"]) {
	$_idrep = $_GET["relatorio"];
} else {
	$_idrep = $_GET["_idrep"];
}


if (empty($_idrep)) {
	die("Relat&oacute;rio n&atilde;o informado!");
}

if ($_idrep == 21) {
	d::b()->query("SET sql_mode = 'NO_UNSIGNED_SUBTRACTION';");
}
//Recupera a definicao das colunas da view ou table default da pagina
$arrRep = getConfRelatorio($_idrep);
//Facilita a utilização do array
$arrRep = $arrRep[$_idrep];

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
	<title><?= $_header ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />
	<style type="text/css">
		table {
			page-break-inside: auto;
			width: 100%
		}

		tr {
			page-break-inside: avoid;
			page-break-after: auto
		}

		thead {
			display: table-header-group
		}

		tfoot {
			display: table-footer-group
		}

		@media print {

			.no-print,
			.no-print * {
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

		td table {
			font-size: 10px !important;
			border: 1px solid;
			border-collapse: inherit;
		}
	</style>
</head>

<body>
	<?

	if (!empty($_GET)) {

		$_sqlwhere = " where ";
		$_and = "";
		$_iclausulas = 0;

		//Loop nos parâmetros GET para montar as cláusulas where
		while (list($_col, $_val) = each($_GET)) {
			$_between = false;
			if (!empty($_val) and ($_col != "_modulo") and ($_col != "_rep") and (substr($_col, -2) != "_2")) {

				//Montar clausula para colunas between
				if (substr($_col, -2) == "_1") {
					$_col = substr($_col, 0, -2); //Transforma do nome do campo para capturar informacoes de tipo
					$_colval1 = $_GET[$_col . "_1"];
					$_colval2 = $_GET[$_col . "_2"];
					if (verificaData($_colval2)) {
						$_colval2 = $_colval2 . ' 23:59:59';
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


				//die();


				//Montar clausula somente para campos que estejam marcados como psqkey
				if ($_psqkey == "Y" and $_insmanual == "N") {
					if ($_between) {
						$_sqlwhere .= $_and . "(" . $_col . " between " . evaltipocoldb($_tab, $_col, $_datatype, $_colval1) . " and " . evaltipocoldb($_tab, $_colval2, $_datatype, $_colval2) . ")";
					} else {


						if ($_like == 'Y') {
							if ($_datatype == 'text') {
								$_datatype = 'varchar';
							}
							$_sqlwhere .= $_and . $_col . " like '%" . substr(substr(evaltipocoldb($_tab, $_col, $_datatype, $_val), 1), 0, -1) . "%'";
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
									$_value .= "'" . $value . "'" . $virg;
								}
							}

							$_sqlwhere .= $_and . $_col . " in (" . $_value . ")";
						} else if ($_in == 'Y') {
							if ($_datatype == 'text') {
								$_datatype = 'varchar';
								$_sqlwhere .= $_and . $_col . " in (" . substr(substr(evaltipocoldb($_tab, $_col, $_datatype, $_val), 1), 0, -1) . ")";
							} else {
								$_sqlwhere .= $_and . $_col . " in (" . $_val . ")";
							}
						} else {
							$_sqlwhere .= $_and . $_col . " = " . evaltipocoldb($_tab, $_col, $_datatype, $_val);
						}
					}


					$_and = " and ";
					$_iclausulas++;
				} else {
					echo "\n<!-- Campo Ignorado: " . $_col . " - Manual: " . $_insmanual . " -->";
				}
			}
		}
		//print_r($arrRep["_datas"]);
		$_sqldata = '';
		if (!empty($arrRep["_datas"])) {

			if ($_REQUEST['_fds']) {
				//echo 'aqui';
				$data = explode('-', $_REQUEST['_fds']);
				$data1 = $data[0];
				$data2 = $data[1];
				if (verificaData($data2)) {
					$data2 = $data2 . ' 23:59:59';
				}


				if ($data1 and $data2) {
					while (list($ko, $vo) = each($arrRep["_datas"])) {
						//echo '<br>';
						$vo = str_replace('a.dataamostra', 'c.dataamostra', $vo);

						$_sqldata .= $_or . "(" . $vo . " between " . evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2) . ")";
						$_or = " or ";
					}
				}

				$_sqldata = ' and (' . $_sqldata . ') ';
			}
		}


		if (!empty($_GET["_fts"])) {
			//Ajusta preferencias do usuario
			userPref("u", $_modulo . "._fts", $_GET["_fts"]);



			$arrFk = retPkFullTextSearch($_tabfull, $_GET["_fts"]/*, $_GET["_pagina"],$_arrModConf["limite"]*/);
			//print_r($arrFk);
			//	echo '<br>';
			$countArrFk = $arrFk["foundRows"];
			if ($countArrFk > 0) {

				$strPkFts = implode(",", $arrFk["arrPk"]);
				$strPkFts = $aspa . implode(($aspa . "," . $aspa), $arrFk["arrPk"]) . $aspa;
				$str_fts = " and " . $_chavefts . " in (" . $strPkFts . ")";
			}
		}


		//echo $_sqlwhere."\n";
		//Abre variavel de sessao para que ela possa ser acessada pelo modulo de interceptacao de eventos
		//Isto permitira saber se existe clausula where ou nao
		$_SESSION["SEARCH"]["CLAUSULAS"] = (string)$_iclausulas;

		if ($_iclausulas > 0) {
			$_sqlresultado = getDbTabela($_tab) . "." . $_tab . " " . $_sqlwhere;
			// $_sqlresultado .=" and idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"];
		} else {
			$_sqlresultado = getDbTabela($_tab) . "." . $_tab . " a";
			$_sqlresultado .= " where idempresa = " . cb::idempresa();
		}

		if (trim($_compl) != '') {
			$_sqlresultado .= ' ' . trim($_compl);
		}



		// RETRINGIR CONSULTA A UNIDADE MARCADA NA LP-------------------------------------------------------------
		$lps = getModsUsr("LPS");
		$sqlFlgUnidade = "Select flgunidade from " . _DBCARBON . "._lprep where idrep=" . $_idrep . " and idlp in(" . $lps . ") order by flgunidade desc";

		$rrep = d::b()->query($sqlFlgUnidade) or die("Erro ao verificar unidade no relatorio: " . mysql_error(d::b()));
		if (mysql_num_rows($rrep) >= 1) {
			while ($r = mysql_fetch_array($rrep)) {
				if ($r['flgunidade'] == 'Y') {
					$_sqlresultado .= " and exists (select 1 from pessoa p where p.idpessoa = " . $_SESSION['SESSAO']['IDPESSOA'] . " and p.idunidade = a.idunidade)";
					break;
				}
			}
		}

		//print_r($arrRep);

		$strselectfields = "";
		$strord = "";
		$strvirg = "";

		//Concatenar campos para o select
		if (!empty($arrRep["_colvisiveis"])) {

			//Transformar em string de 'Select n,...'
			//print_r($arrRep["_colvisiveis"]);
			//die();
			while (list($ko, $vo) = each($arrRep["_colvisiveis"])) {
				if ($arrRep["_filtros"][$vo]["tsum"] == 'Y') {
					if (containsDecimal($vo)) {
						$strselectfields .= $strvirg . 'round(sum(' . $vo . '),2) as ' . $vo;
					} else {
						$strselectfields .= $strvirg . 'round(sum(' . $vo . '),2) as ' . $vo;
					}
				} else {
					$strselectfields .= $strvirg . $vo;
				}
				$strvirg = ", ";
			}

			$strselectfields = "select " . $strselectfields . " ";

			//Reseta Variaveis de controle de virgula
			$strvirg = "";
		}

		//Concatenar clausulas para Order By
		if (!empty($arrRep["_orderby"])) {
			//Ordenar pelo valor indicado no campo 'ordseq', que é a KEY deste array
			ksort($arrRep["_orderby"]);

			//Transformar em string de 'Order By' para o banco
			while (list($ko, $vo) = each($arrRep["_orderby"])) {
				$strord .= $strvirg . $vo;
				$strvirg = ", ";
			}

			//Concatena a ultima parte da string
			$strord = " order by a.exercicio, a.idregistro, ps.ordem" . $strord;
		}
		$strvirg = "";
		if (!empty($arrRep["_groupby"])) {
			//Ordenar pelo valor indicado no campo 'ordseq', que é a KEY deste array
			ksort($arrRep["_groupby"]);

			//Transformar em string de 'Order By' para o banco
			while (list($ko, $vo) = each($arrRep["_groupby"])) {
				$strgrp .= $strvirg . $vo;
				$strvirg = ", ";
			}

			//Concatena a ultima parte da string
			$strgrp = " group by " . $strgrp;
		}

		//echo '**'.$strgrp;
		/****************************************************************************
		 * CONCATENACAO PRINCIPAL DO SELECT A SER EXECUTADO PARA O RELATORIO        *
		 ****************************************************************************/

		$str_fts = str_replace('idresultado', 'r.idresultado', $str_fts);
		$str_fts = str_replace('idregistro', 'a.idregistro', $str_fts);
		$str_fts = str_replace('nucleoamostra', 'a.nucleoamostra', $str_fts);
		$str_fts = str_replace('exercicio', 'a.exercicio', $str_fts);
		$str_fts = str_replace('idtipoamostra', 'a.idtipoamostra', $str_fts);
		$str_fts = str_replace('idsubtipoamostra', 'a.idsubtipoamostra', $str_fts);
		$str_fts = str_replace('lote', 'a.lote', $str_fts);
		$str_fts = str_replace('granja', 'a.granja', $str_fts);
		$str_fts = str_replace('galpao', 'a.galpao', $str_fts);

		$str_fts = str_replace('finalidade', 'ef.finalidade', $str_fts);
		$str_fts = str_replace(' idade', ' a.idade', $str_fts);
		$str_fts = str_replace('dataamostra', 'a.dataamostra', $str_fts);
		$str_fts = str_replace('datacoleta', 'a.datacoleta', $str_fts);
		$str_fts = str_replace('criadoem', 'ra.criadoem', $str_fts);


		$_sqldata = str_replace('a.dataamostra', 'dataamostra', $_sqldata);
		$_sqldata = str_replace('idregistro', 'a.idregistro', $_sqldata);
		$_sqldata = str_replace('nucleoamostra', 'a.nucleoamostra', $_sqldata);
		$_sqldata = str_replace('exercicio', 'a.exercicio', $_sqldata);
		$_sqldata = str_replace('idtipoamostra', 'a.idtipoamostra', $_sqldata);
		$_sqldata = str_replace('idsubtipoamostra', 'a.idsubtipoamostra', $_sqldata);
		$_sqldata = str_replace('lote', 'a.lote', $_sqldata);
		$_sqldata = str_replace('granja', 'a.granja', $_sqldata);
		$_sqldata = str_replace('galpao', 'a.galpao', $_sqldata);

		$_sqldata = str_replace('finalidade', 'ef.finalidade', $_sqldata);
		$_sqldata = str_replace(' idade', ' a.idade', $_sqldata);
		$_sqldata = str_replace('dataamostra', 'dataamostra', $_sqldata);
		$_sqldata = str_replace('datacoleta', 'a.datacoleta', $_sqldata);
		$_sqldata = str_replace('criadoem', 'ra.criadoem', $_sqldata);


		// $strselectfields = str_replace('exercicio', 'a.exercicio',$strselectfields);
		$strselectfields = str_replace('galinha', "'GALINHA' AS galinha", $strselectfields);
		$strselectfields = str_replace('nucleosexaminados', " count(distinct c.idnucleo) AS nucleosexaminados", $strselectfields);
		$strselectfields = str_replace('nucleospositivos', " count(distinct c.nucleopositivo) AS NucleosPositivos", $strselectfields);
		$strselectfields = str_replace('amostrasexaminadas', " sum(c.quantidade) AS AmostrasExaminadas", $strselectfields);
		$strselectfields = str_replace('amostraspositivas', " sum(c.positividade) AS AmostrasPositivas", $strselectfields);
		$strselectfields = str_replace("spp", "count(distinct if(tipoalerta = 'SPP',c.nucleopositivo,null)) as SPP", $strselectfields);
		$strselectfields = str_replace("sg", "count(distinct if(tipoalerta = 'SG',c.nucleopositivo,null)) as SG", $strselectfields);
		$strselectfields = str_replace("sp", "count(distinct if(tipoalerta = 'SP',c.nucleopositivo,null)) as SP", $strselectfields);
		$strselectfields = str_replace("se,", "count(distinct if(tipoalerta = 'SE',c.nucleopositivo,null)) as SE,", $strselectfields);
		$strselectfields = str_replace(", st", ", count(distinct if(tipoalerta = 'ST',c.nucleopositivo,null)) as ST", $strselectfields);
		$strselectfields = str_replace("ms", "count(distinct if(tipoalerta = 'MS',c.nucleopositivo,null)) as MS", $strselectfields);
		$strselectfields = str_replace(" mg", " count(distinct if(tipoalerta = 'MG',c.nucleopositivo,null)) as MG", $strselectfields);
		$strselectfields = str_replace('finalidade', 'ef.finalidade', $strselectfields);
		$strselectfields = str_replace('nroamostra', " sum(c.nroamostra) AS nroamostra", $strselectfields);


		$strselectfields = str_replace('teste', "(case c.teste
						when 'BACT' then 'BACT'
						when 'BACT1' then 'BACT'
						when 'BACT2' then 'BACT'
						when 'BACT3' then 'BACT'
						when 'BACT IN 10' then 'BACT'
						else c.teste
					end) as teste", $strselectfields);
		$strselectfields = str_replace(' idade', ' a.idade', $strselectfields);
		$strselectfields = str_replace(' dataamostra', 'DMA(a.dataamostra) AS dataamostra', $strselectfields);
		$strselectfields = str_replace('datacoleta', ' DMA(a.datacoleta) AS datacoleta', $strselectfields);
		$strselectfields = str_replace('criadoem', 'ra.criadoem', $strselectfields);

		$_sqlresultado = $strselectfields . "  from
				(SELECT STRAIGHT_JOIN a.exercicio AS exercicio,
						  a.idregistro AS idregistro,
						  a.dataamostra AS dataamostra,
						  LEFT(DATE_FORMAT(a.dataamostra, _UTF8'%M'), 3) AS mes,
						  MONTH(a.dataamostra) AS mesint,
						
				(SELECT MAX(endereco.uf) AS uf
				 FROM endereco
				 WHERE (endereco.idpessoa = r.idsecretaria)) AS uf,
						  sta.subtipoamostra AS tipoave,
						  r.idtipoteste AS idtipoteste,
						  t.tipoteste AS tipoteste,
						  t.sigla AS teste,
						  -- TIPOEXPLORACAO(ef.tipoespecie, ef.finalidade, CAST(a.idade AS UNSIGNED)) AS tipoexploracao,
							TIPOEXPLORACAO( SUBSTRING_INDEX(SUBSTRING_INDEX(ef.finalidade, ' ', 1), ' ', -1), TRIM( SUBSTR(ef.finalidade, LOCATE(' ', 'Avós Corte')) ), CAST(a.idade AS UNSIGNED)) AS tipoexploracao,
						  -- concat(ifnull(ef.especie,''),' ',ifnull(ef.finalidade,'')) as tipoexploracao2,
							SUBSTRING_INDEX(SUBSTRING_INDEX(ef.finalidade, ' ', 1), ' ', -1) as tipoexploracao2,
						  -- ef.tipoespecie as tipoexploracao2,
						  n.nucleo AS nucleo,
						  n.idnucleo,
						  IF((r.positividade > 0 and r.alerta = 'Y'), n.idnucleo, NULL) AS nucleopositivo,
						  r.quantidade AS quantidade,
						  r.positividade AS positividade,
						  IF((r.idsecretaria > ''), 'Y', 'N') AS oficial,
						  r.tipoalerta,
						  r.alerta,
						  a.nroamostra
			FROM 
			amostra a
			JOIN subtipoamostra sta on sta.idsubtipoamostra = a.idsubtipoamostra
			JOIN resultado r ON r.idamostra = a.idamostra " . $_sqldata . " and r.idtipoteste in (640, 718 , 719, 720, 2000, 678, 617, 619,622, 624, 595,596) and r.status!='CANCELADO'
			JOIN prodserv p on p.idprodserv = r.idtipoteste
			JOIN vwtipoteste t ON t.idtipoteste = r.idtipoteste
			JOIN nucleo n ON n.idnucleo = a.idnucleo
			LEFT JOIN titulo tt ON tt.titulo = t.tipoespecial
			LEFT JOIN especiefinalidade ef on ef.idespeciefinalidade=a.idespeciefinalidade
			where 1
			 and a.idunidade =1

			) c 
			where
						(c.oficial = 'Y')
		
		" . $_and_idempresa . $str_fts . " group by c.exercicio , c.mes , c.uf , 'GALINHA' , c.tipoexploracao, c.tipoexploracao2, (case c.teste
						when 'BACT' then 'BACT'
						when 'BACT1' then 'BACT'
						when 'BACT2' then 'BACT'
						when 'BACT3' then 'BACT'
						when 'BACT IN 10' then 'BACT'
						else c.teste
					end) 
					order by 
					c.exercicio, c.mesint, c.uf,  c.tipoexploracao, c.tipoexploracao2, 
					(case c.teste
						when 'BACT' then 'BACT'
						when 'BACT1' then 'BACT'
						when 'BACT2' then 'BACT'
						when 'BACT3' then 'BACT'
						when 'BACT IN 10' then 'BACT'
						else c.teste
					end)";
		//die();
		//Abre variavel de sessao para que ela possa ser acessada pelo modulo de interceptacao de eventos
		$_SESSION["SEARCH"]["SQL"] = $_sqlresultado;

		echo "<!-- " . $_sqlresultado . " -->";	//echo $_sqlresultado;

		$_resultados = d::b()->query($_sqlresultado);
		if (!$_resultados) {
			die('<b>Falha na execucao da Consulta para o Report:</b> ' . mysql_error() . "<br>" . $_sqlresultado);
		}

		$_arrtab = retarraytabdef($_tab);
		//print_r($_ arrtab); die();

		$_i = 0;
		$_numcolunas = mysql_num_fields($_resultados);
		$_ipagpsqres = mysql_num_rows($_resultados);
		if ($_ipagpsqres == 1) {
			$strs = $_ipagpsqres . " Registro encontrado";
		} elseif ($_ipagpsqres > 1) {
			$strs = $_ipagpsqres . " Registros encontrados";
		} else {
			$strs = "Nenhum Registro encontrado";
		}

		$_nomeimpressao = "[" . md5(date('dmYHis')) . "] gerada em [" . date(" d/m/Y H:i:s") . "]";

		/*
	$sqlfig="select figrelatorio from empresa where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
	$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	$figrel=mysqli_fetch_assoc($resfig);

	//$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
	$figurarelatorio = "../inc/img/repheader.png";
	*/

		// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
		$sqlfig = "select logosis from empresa where idempresa =" . $_SESSION["SESSAO"]["IDEMPRESA"];
		$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: " . mysql_error());
		$figrel = mysqli_fetch_assoc($resfig);

		//$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
		//$figurarelatorio = "../inc/img/repheader.png";
		$figurarelatorio = $figrel["logosis"];

		?>
		<div style="display:none; text-align: left; font-size: 9px;" class="n_linhas"><span id="nlinha"><?= $strs ?></span></div>

		<table class="tbrepheader">
			<tr>
				<td rowspan="3" style="width:200;"><img src="<?= $figurarelatorio ?>"></td>
				<td class="header"><? //=$_header
									?></td>
				<td><a class="btbr20 no-print" href="<?= $_SERVER['REQUEST_URI'] ?>&reportexport=csv" target="_blank">Exportar .csv</a></td>
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
		//print_r($arrRep);
		/*
	 * MONTA O CABECALHO
	 */
		$conteudoexport; // guarda o conteudo para exportar para csv
		$strtabheader = "\n<thead><tr class='header'>";
		//coloca um contador numerico do lado esquerdo da tabela, isto é repetido também na montagem de cada linha da tabela
		if ($_showtotalcounter == "Y") {
			$strtabheader .= "<td class='tdcounter'></td>";
		}

		//	echo  $_i.' - '.$_numcolunas;
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
			//echo $_metacmp->name.'<br>';
			//echo $arrRep["_colvisiveis"][$_i+1].' - '.$_i.'<br>';
			if ($arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["visres"] == 'Y') {
				// if($arrRep["_filtros"][$_metacmp->name]["visres"] == 'Y'){

				//A VIRGULA E SO ENTRE OS VALORES NO INICIO DA LINHA E NO FINAL NÃO TEM VIRGULA
				if (!empty($conteudoexport)) {
					$conteudoexport .= ";";
				}
				//echo 'opa'.$arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["rotulo"];
				if (strpos(strtolower($arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["rotulo"]), ' as ') !== false) {
					//echo 'aqio';
					$val = explode(' as ', strtolower($arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["rotulo"]));
					$arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["rotulo"] = $val[1];
				}

				$strtabheader .= "<td class='header' id='" . seo_friendly_url(str_replace('', '', $arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["rotulo"])) . "' style=\"text-align:" . $arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["align"] . "\">" . str_replace('', '', $arrRep["_filtros"][$arrRep["_colvisiveis"][$_i + 1]]["rotulo"]) . "</td>";
			}
			$conteudoexport .= "\"" . $arrRep["_filtros"][$_metacmp->name]["rotulo"] . "\""; // GRAVA O ROTULO DOS CABEÇALHOS NA VARIAVEL PARA GERAR O CSV
			$_i++;
		} //while ($_i < $_numcolunas) {

		$conteudoexport .= "\n"; //QUEBRA DE LINHA NO CONTEUDO CSV
		$strtabheader .= "</tr></thead><tbody>";

		/*
	 * Variaveis para cabecalho do report
	 */
		$strpagini = "\n<fieldset class='fldsheader'><legend>Impressão " . $_nomeimpressao . "</legend></fieldset>";
		$strtabini = "\n<table class='normal'>";
		$strtabheader = $strtabheader;

		/*
	 * MONTA A TABELA
	 */
		$_ilinha = 0; //armazena o ttotal de registros
		$_ilinhaquebra = 0; //armazena parialmente o numero de registros se houver quebra automatica configurada
		$strnewpage = "<span class='newreppage'></span>";

		while ($_row = mysql_fetch_array($_resultados)) {
			$_ilinha++;
			$_i = 0;

			//verifica se o parametro de quebra automatica esta configurado. caso negativo escreve o cabecalho somente 1 vez. E tambem se for a primeira linha, desenha o cabecalho pelo 'else'
			if ($_pbauto > 0 and $_ilinha > 1) {
				//verifica quando é que uma nova quebra sera colocada
				if ($_pbauto > ($_ilinhaquebra + 1)) {
					//echo "\n#".$_ilinhaquebra;
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

			//echo("<br>[$_strhlcolor]<br>");
			echo "\n<tr class=\"res\" " . $_link . " " . $_strhlcolor . ">";
			//coloca um contador numerico do lado esquerdo da tabela
			if ($_showtotalcounter == "Y") {
				echo "<td class='tdcounter'>" . $_ilinha . "</td>";
			}
			if ($_numlinha == "Y") {
		?><td style="background-color:none;"><?= $_ilinha ?></td><?
															}

															/*
		 * Montagem dos <TD>s
		 */
		while ($_i < $_numcolunas) {
			$_stralign = "";
			$_strvlrhtml = "";

			$_nomecol = $_arridxcol[$_i];
			//$_i.'<br>';
			$_nomecol = $arrRep["_colvisiveis"][$_i + 1];
			///echo $_i.'<br>';
			$_colorlink = "";
			$_hyperlink = "";
			$_corfont = "";
			$_corfontfim = "";
			//Escreve Campo
			if ($arrRep["_filtros"][$_nomecol]["visres"] == 'Y') {

				//ajusta o alinhamento dentro da celula. caso esquerda. nao preencher para nao gerar html desnecessariamente
				//echo $arrRep["_filtros"][$_nomecol]["align"];
				if ($arrRep["_filtros"][$_nomecol]["align"] != "left") {
					$_stralign = "align='" . $arrRep["_filtros"][$_nomecol]["align"] . "'";
				}

				//se for para somar o valor do campo
				if ($arrRep["_filtros"][$_nomecol]["acsum"] == 'Y') {

					$_arrsoma[$_tab][$_nomecol] = $_arrsoma[$_tab][$_nomecol] + $_row[$_i];
				}


				/*
				 * Trata campo de longtext
				 */
						if ($arrRep["_filtros"][$_nomecol]["datatype"] == 'longtext') {
							$_strvlrhtml = nl2br($_row[$_i]);
						} elseif ($arrRep["_filtros"][$_nomecol]["datatype"] == 'datetime') {
							$_strvlrhtml = validadatadbweb($_row[$_i]);
						} elseif ($arrRep["_filtros"][$_nomecol]["datatype"] == 'date') {
							$_strvlrhtml = dma($_row[$_i]);
						} else {
							$_strvlrhtml = $_row[$_i];
						}

						$_strvlrhtml = aplicaMascara($arrRep["_filtros"][$_nomecol]["mascara"], $_strvlrhtml);

						if (is_numeric($_row[$_i])) {
							$total[$_i] = $total[$_i] + $_row[$_i];
						}
						//SE FOR UM NOVO <TD> ELE NÃO COMEÇA COM VIRGULA NO CSV
						if ($_i > 0) {
							$conteudoexport .= ";"; //COLOCA A VIRGULA ENTRE OS VALORES 
						}

						$conteudoexport .= "\"" . $_strvlrhtml . "\""; //GRAVA O VALOR DO CAMPO PARA GERAR O CSV ENTRE ASPAS

						//Se o hyperlink não estiver vazio ele monta o link
						if (!empty($arrRep["_filtros"][$_nomecol]["hyperlink"])) {
							//O HREF contém uma barra ('/') o que significa que é uma URL relativa, que vai desconsiderar qualquer pasta informada neste link
							$_hyperlink = "<a target=_blank href='/" . $arrRep["_filtros"][$_nomecol]["hyperlink"] . $_row[$_i] . "'>" . $_strvlrhtml . "</a>";
							$_colorlink = "class=\"link\" ";
							$_corfont = "<font color='Blue'>";
							$_corfontfim = "</font>";
						}

						//Finalmente: desenha o campo na tela
						if (!empty($arrRep["_filtros"][$_nomecol]["hyperlink"])) {
							echo "<td>" . $_hyperlink . "&nbsp;</td>";
						} else {
							echo "<td " . $_stralign . " " . $_hyperlink . " " . $_colorlink . ">" . $_corfont . $_strvlrhtml . $_corfontfim . "&nbsp;</td>";
						}
					}
					$_i++;
				}
				$conteudoexport .= "\n"; //QUEBRA A LINHA DO CONTEUDO CSV
			}

			//	print_r($_arrsoma);
					?>
		</tr>
		<?

		if (!empty($_arrsoma)) {
		?>
			<tr class="res">
				<td colspan="500" class="inv">&nbsp;</td>
			</tr>
			<tr class="res">
				<?
				$_y = 0;
				while ($_y < $_numcolunas) {
					$_stralign = "";
					$_strvlrhtml = "";
					$_nomecol = $_arridxcol[$_y];
					$_nomecol = $arrRep["_colvisiveis"][$_y + 1];

					if ($arrRep["_filtros"][$_nomecol]["visres"] == 'Y' and $arrRep["_filtros"][$_nomecol]["acsum"] == 'Y') {

						if ($arrRep["_filtros"][$_nomecol]["acsum"] == 'Y') {
							//echo 'entrei';
							//$tipocalc="Soma:";
						}
						echo ("<td class=\"tot\" style=\"text-align:" . $arrRep["_filtros"][$_nomecol]["align"] . "\">");
						if (containsDecimal($_arrsoma[$_tab][$_nomecol])) {
							echo ($tipocalc . " " . number_format($_arrsoma[$_tab][$_nomecol], 2, ',', '.'));
						} else {
							echo ($tipocalc . " " . number_format($_arrsoma[$_tab][$_nomecol], 0, ',', '.'));
						}

						echo ("</td>");
						//echo $arrRep["_filtros"][$_nomecol]["datatype"];



					} else {
						echo ("<td class=\"inv\"></td>");
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
	<?

		/*
	 * Desenha a legenda
	 */

	} //if (!empty($_GET)){
	?>

	<?
	if (defined("_RODAPEDIR")) $varfooter = _RODAPEDIR;
	?>
	<footer>

	</footer>
</body>
<fieldset class="fldsfooter">
	<legend>Fim da Impressão <?= $_nomeimpressao . " " . $varfooter ?></legend>
</fieldset>
</body>

</html>
<?
if (!empty($_GET["reportexport"])) {
	ob_end_clean(); //não envia nada para o browser antes do termino do processamento

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
	header("Content-type: text/csv; charset=UTF-8");
	header("Content-Disposition: attachment; filename=" . $infilename . ".csv");
	header("Pragma: no-cache");
	header("Expires: 0");

	echo iconv('UTF-8', 'ISO-8859-1', $conteudoexport);
}
?>