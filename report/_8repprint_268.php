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

$sql_check_LP = "select 1 from carbonnovo._lprep where idlp in (".getModsUsr("LPS").") and idrep= ".$_GET["_idrep"]."";
$chk = d::b()->query($sql_check_LP) or die('ERRO AO VERIFICAR LP');
if (mysqli_num_rows($chk) == 0) {
	die('<div>Você não Possui permissão para acessar esse Relatório</div>');
}

$_modulo = $_GET["_modulo"];
if ($_GET["relatorio"]) {
	$_idrep = $_GET["relatorio"];
} else {
	$_idrep = $_GET["_idrep"];
}


if ($_REQUEST['idsgsetor']) {
	$v_tipoobjetovinc = 'sgsetor';
	$v_idobjetovinc = $_REQUEST['idsgsetor'];
	$clausorg = "and vwo.tipoobjeto = '".$v_tipoobjetovinc."' and vwo.idobjeto in ( ".$v_idobjetovinc.")";
	$joinorg = "join sgsetor sc on sc.idsgsetor = vwo.idobjeto and sc.status = 'ATIVO'";
	$camposorg = ",idsgsetor, setor";
	$camposorgorder = "idempresarateio,idempresa,setor,";
	$c1 = 'idsgsetor';
	$c2 = 'setor';
	$tipo = 'SETOR';
	$tipoc = 'DEPARTAMENTO, ÁREA, CONSELHO E EMPRESA';
} elseif ($_REQUEST['idsgdepartamento']) {
	$v_tipoobjetovinc = 'sgdepartamento';
	$v_idobjetovinc = $_REQUEST['idsgdepartamento'];
	$clausorg = "and vwo.tipoobjeto = '".$v_tipoobjetovinc."' and vwo.idobjeto in (".$v_idobjetovinc.")";
	$joinorg = "join sgdepartamento sc on sc.idsgdepartamento = vwo.idobjeto and sc.status = 'ATIVO'";
	$camposorg = ",idsgdepartamento, departamento";
	$camposorgorder = "idempresarateio,idempresa,departamento,";
	$c1 = 'idsgdepartamento';
	$c2 = 'departamento';
	$tipo = 'DEPARTAMENTO';
	$tipoc = 'ÁREA, CONSELHO E EMPRESA';
} elseif ($_REQUEST['idsgarea']) {
	$v_tipoobjetovinc = 'sgarea';
	$v_idobjetovinc = $_REQUEST['idsgarea'];
	$clausorg = "and vwo.tipoobjeto = '".$v_tipoobjetovinc."' and vwo.idobjeto in ( ".$v_idobjetovinc.")";
	$joinorg = "join sgarea sc on sc.idsgarea = vwo.idobjeto and sc.status = 'ATIVO'";
	$camposorg = ",idsgarea, area";
	$camposorgorder = "idempresarateio,idempresa,idsgarea,area,";
	$c1 = 'idsgarea';
	$c2 = 'area';
	$tipo = 'ÁREA';
	$tipoc = 'CONSELHO E EMPRESA';
} elseif ($_REQUEST['idsgconselho']) {
	$v_tipoobjetovinc = 'sgconselho';
	$v_idobjetovinc = $_REQUEST['idsgconselho'];
	$clausorg = "and vwo.tipoobjeto = '".$v_tipoobjetovinc."' and vwo.idobjeto in ( ".$v_idobjetovinc.")";
	$joinorg = "join sgconselho sc on sc.idsgconselho = vwo.idobjeto and sc.status = 'ATIVO'";
	$camposorg = ",idsgconselho, conselho";
	$camposorgorder = "idempresarateio,idempresa,idsgconselho,conselho,";
	$c1 = 'idsgconselho';
	$c2 = 'conselho';
	$tipo = 'CONSELHO';
	$tipoc = 'EMPRESA';
} else {
	$v_tipoobjetovinc = 'empresa';
	$v_idobjetovinc = $_REQUEST['idempresa'];
	$clausorg = "";
	$joinorg = "join sgempresa sc on sc.idsgempresa = vwo.idempresa join sgconselho sgc on sgc.idempresa = sc.idempresa and sgc.status = 'ATIVO'";
	$camposorg = ",idsgempresa, sgempresa";
	$camposorgorder = "idempresarateio,idempresa,idsgempresa,sgempresa,";
	$c1 = 'idsgempresa';
	$c2 = 'sgempresa';
	$tipo = 'EMPRESA';
	$tipoc = '';
}

if ($_REQUEST['idempresa']) {
	$clausorg2 .= " and idempresarateio in ( ".$_REQUEST['idempresa'].") ";
}
if ($_REQUEST['idcontaitem']) {
	$clausorg2 .= " and v.idcontaitem in ( ".$_REQUEST['idcontaitem'].") ";
}

if ($_REQUEST['idtipoprodserv']) {
	$clausorg2 .= " and v.idtipoprodserv in ( ".$_REQUEST['idtipoprodserv'].") ";
}

if ($_REQUEST['idagencia']) {
	$clausorg2 .= " and v.idagencia in ( ".$_REQUEST['idagencia'].") ";
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

$eixoX = "";
$eixoY = [];
$arrayGrafico = array();
$tipoGraphRelatorio = $arrRep["tipograph"];
?>
<html>

<head>
	<title><?= $_rep.' '.$_GET["_fds"] ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<link href="../inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />
	<link href="../inc/css/fontawesome/font-awesome.min.css" media="all" rel="stylesheet" type="text/css" />
	<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
	<script src="../inc/js/moment/moment.min.js"></script>

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

		@media print {
			body {
				-webkit-print-color-adjust: exact;
			}
		}

		td.inv,
		td.tot {
			border: none !important;
			background: #bbb;
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
		foreach ($_GET  as $_col => $_val) {
			$_between = false;
			if (!empty($_val) and ($_col != "_modulo") and ($_col != "_rep") and (substr($_col, -2) != "_2")) {

				//Montar clausula para colunas between
				if (substr($_col, -2) == "_1") {
					$_col = substr($_col, 0, -2); //Transforma do nome do campo para capturar informacoes de tipo
					$_colval1 = $_GET[$_col."_1"];
					$_colval2 = $_GET[$_col."_2"];
					if (verificaData($_colval2)) {
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
				if ($_psqkey == "Y" and $_insmanual == "N") {
					if ($_between) {
						$_sqlwhere .= $_and."(".$_col." between ".evaltipocoldb($_tab, $_col, $_datatype, $_colval1)." and ".evaltipocoldb($_tab, $_colval2, $_datatype, $_colval2).")";
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
		if (!empty($arrRep["_datas"])) {

			if ($_REQUEST['_fds']) {
				//echo 'aqui';
				$data = explode('-', $_REQUEST['_fds']);
				$data1 = $data[0];
				$data2 = $data[1];
				if (verificaData($data2)) {
					$data2 = $data2.' 23:59:59';
				}


				if ($data1 and $data2) {
					while (list($ko, $vo) = each($arrRep["_datas"])) {
						//echo '<br>';
						$_sqldata .= $_or."(".$vo." between ".evaltipocoldb($_tab, $vo, 'datetime', $data1)." and ".evaltipocoldb($_tab, $data2, 'datetime', $data2).")";
						$_or = " or ";
					}
				}

				$_sqldata = ' and ('.$_sqldata.') ';
			}
		}


		if (!empty($_GET["_fts"])) {
			//Ajusta preferencias do usuario
			userPref("u", $_modulo."._fts", $_GET["_fts"]);

			$arrFk = retPkFullTextSearch($_tabfull, $_GET["_fts"]/*, $_GET["_pagina"],$_arrModConf["limite"]*/);
			$countArrFk = $arrFk["foundRows"];
			$aspa = "'";
			if ($countArrFk > 0) {

				$strPkFts = implode(",", $arrFk["arrPk"]);
				$strPkFts = $aspa.implode(($aspa.",".$aspa), $arrFk["arrPk"]).$aspa;
				$str_fts = " and ".$_chavefts." in (".$strPkFts.")";
			}
		}

		//Abre variavel de sessao para que ela possa ser acessada pelo modulo de interceptacao de eventos
		//Isto permitira saber se existe clausula where ou nao
		$_SESSION["SEARCH"]["CLAUSULAS"] = (string)$_iclausulas;

		if ($_iclausulas > 0) {
			$_sqlresultado = getDbTabela($_tab).".".$_tab." ".$_sqlwhere;
			if (empty($_GET['idempresa']) && cb::habilitarMatriz() == 'N') {
				//$_sqlresultado .=getidempresa('idempresa',$_modulo);
				$wIdempresa = ($rowEmpresa['idempresa'] == 0) ? cb::idempresa() : $rowEmpresa['idempresa'];

				$_sqlresultado .=  $_and." idempresa in (".$wIdempresa.")";
				$_and = " and ";
			} elseif (empty($_GET['idempresa']) && cb::habilitarMatriz() != 'N') {
				$sqlEmpresa = "SELECT ifnull(group_concat(e.idempresa),0) as idempresa
                        FROM empresa e JOIN  objempresa o ON o.empresa = e.idempresa
                        WHERE e.status = 'ATIVO' AND o.idobjeto = '".$_SESSION["SESSAO"]["IDPESSOA"]."' AND o.objeto = 'pessoa'";
				$resEmpresa = d::b()->query($sqlEmpresa) or die("Erro ao recuperar Empresa: ".mysql_error());
				$rowEmpresa = mysqli_fetch_assoc($resEmpresa);

				$wIdempresa = ($rowEmpresa['idempresa'] == 0) ? cb::idempresa() : $rowEmpresa['idempresa'];

				$_sqlresultado .= $_and." idempresa in (".$wIdempresa.")";
				$_and = " and ";
			}
		} else {
			$_sqlresultado = getDbTabela($_tab).".".$_tab." ".$_sqlwhere;
			if (empty($_GET['idempresa']) && cb::habilitarMatriz() == 'N') {

				//$_sqlresultado .=" where 1 ".getidempresa('idempresa',$_modulo);
				$wIdempresa = ($rowEmpresa['idempresa'] == 0) ? cb::idempresa() : $rowEmpresa['idempresa'];

				$_sqlresultado .= $_and." idempresa in (".$wIdempresa.")";
				$_and = " and ";
			} elseif (empty($_GET['idempresa']) && cb::habilitarMatriz() != 'N') {

				$sqlEmpresa = "SELECT ifnull(group_concat(e.idempresa),0) as idempresa
                        FROM empresa e JOIN  objempresa o ON o.empresa = e.idempresa
                        WHERE e.status = 'ATIVO' AND o.idobjeto = '".$_SESSION["SESSAO"]["IDPESSOA"]."' AND o.objeto = 'pessoa'";

				$resEmpresa = d::b()->query($sqlEmpresa) or die("Erro ao recuperar Empresa: ".mysql_error());
				$rowEmpresa = mysqli_fetch_assoc($resEmpresa);

				$wIdempresa = ($rowEmpresa['idempresa'] == 0) ? cb::idempresa() : $rowEmpresa['idempresa'];

				$_sqlresultado .=  $_and." idempresa in (".$wIdempresa.")";
				$_and = " and ";
			}
		}

		if (trim($_compl) != '') {
			$_sqlresultado .= ' '.trim($_compl);
		}

		// RETRINGIR CONSULTA A UNIDADE MARCADA NA LP-------------------------------------------------------------
		$lps = getModsUsr("LPS");
		$sqlFlgUnidade = "Select flgunidade from "._DBCARBON."._lprep where idrep=".$_idrep." and idlp in(".$lps.") order by flgunidade desc";

		$rrep = d::b()->query($sqlFlgUnidade) or die("Erro ao verificar unidade no relatorio: ".mysql_error(d::b()));
		if (mysql_num_rows($rrep) >= 1) {
			while ($r = mysql_fetch_array($rrep)) {
				if ($r['flgunidade'] == 'Y') {
					$_sqlresultadounidade .= " and exists (select 1 from vw8PessoaUnidade pu where pu.idpessoa = ".$_SESSION['SESSAO']['IDPESSOA']." and pu.idunidade = v.idunidade)";
					break;
				}
			}
		}

		// RETRINGIR CONSULTA A HIERARQUIA ORGANOGRAMA QUANDO MARCADO NA LPREP-------------------------------------------------------------
		$sqlflgidpessoa = "Select flgidpessoa from "._DBCARBON."._lprep where idrep=".$_idrep." and idlp in(".$lps.") order by flgidpessoa desc";

		$rrep = d::b()->query($sqlflgidpessoa) or die("Erro ao verificar flgidpessoa no relatorio: ".mysql_error(d::b()));
		if (mysql_num_rows($rrep) >= 1) {
			while ($r = mysql_fetch_array($rrep)) {
				if ($r['flgidpessoa'] == 'Y') {
					$_sqlresultadounidade .= getOrganogramaRep('idpessoafun');
					break;
				}
			}
		}

		//--------------------- Validação para filtro com Plantel - LTM (28-07-2020 - 363014) ----------------
		$arrFiltros = retarraytabdef($_tab);
		if (array_key_exists("STRCONTATOCLIENTE", $_SESSION["SESSAO"]) and array_key_exists("idpessoa", $arrFiltros)) {
			$_sqlresultado .= " and idpessoa in( ".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].",".$_SESSION["SESSAO"]["IDPESSOA"].") ";
		}

		$strselectfields = "";
		$strord = "";
		$strvirg = "";

		//Concatenar campos para o select
		if (!empty($arrRep["_colvisiveis"])) {

			foreach($arrRep["_colvisiveis"] as $ko => $vo) {
				if ($arrRep["_filtros"][$vo]["tsum"] == 'Y') {
					if (containsDecimal($vo)) {
						$strselectfields .= $strvirg.'sum('.$vo.') as '.$vo;
					} else {
						$strselectfields .= $strvirg.'sum('.$vo.') as '.$vo;
					}
				} else {
					$strselectfields .= $strvirg.$vo;
				}
				$strvirg = ", ";
			}

			$strselectfields = "select ".$strselectfields." ";

			//Reseta Variaveis de controle de virgula
			$strvirg = "";
		}

		//Concatenar clausulas para Order By
		if (!empty($arrRep["_orderby"])) {
			//Ordenar pelo valor indicado no campo 'ordseq', que é a KEY deste array
			ksort($arrRep["_orderby"]);

			//Transformar em string de 'Order By' para o banco
			foreach($arrRep["_orderby"] as $ko => $vo) {
				$strord .= $strvirg.$vo;
				$strvirg = ", ";
			}

			//Concatena a ultima parte da string
			$strord = " order by ".$strord;
		}
		$strvirg = "";
		if (!empty($arrRep["_groupby"])) {
			//Ordenar pelo valor indicado no campo 'ordseq', que é a KEY deste array
			ksort($arrRep["_groupby"]);

			//Transformar em string de 'Order By' para o banco
			foreach ($arrRep["_groupby"] as $ko => $vo) {
				$strgrp .= $strvirg.$vo;
				$strvirg = ", ";
			}

			//Concatena a ultima parte da string
			$strgrp = " group by ".$strgrp;
		}

		/****************************************************************************
		 * CONCATENACAO PRINCIPAL DO SELECT A SER EXECUTADO PARA O RELATORIO        *
		 ****************************************************************************/

		// $strselectfields = "select idnf, empresa, contaitem, tipoprodserv, qtd, un, descr, vlrlote, valor, rateio";
		// $_REQUEST['idobjeto'] = 159;
		//$_REQUEST['tipoobjeto'] = 'sgdepartamento';

		$vw8despesas =
			"SELECT `a`.`tiponf` AS `tiponf`,
					`a`.`idcontaitem` AS `idcontaitem`,
					`a`.`contaitem` AS `contaitem`,
					`a`.`idtipoprodserv` AS `idtipoprodserv`,
					`a`.`tipoprodserv` AS `tipoprodserv`,
					`a`.`cor` AS `cor`,
					`a`.`previsao` AS `previsao`,
					`a`.`status` AS `status`,
					`a`.`tipo` AS `tipo`,
					`a`.`faturamento` AS `faturamento`,
					`a`.`ordem` AS `ordem`,
					`a`.`descricao` AS `descricao`,
					`a`.`idnf` AS `idnf`,
					`a`.`datareceb` AS `datareceb`,
					`a`.`idempresa` AS `idempresa`,
					`a`.`idagencia` AS `idagencia`,
					`a`.`idnfitem` AS `idnfitem`,
					`a`.`idcontapagar` AS `idcontapagar`,
					`a`.`qtd` AS `qtd`,
					`a`.`un` AS `un`,
					`a`.`total` AS `total`,
					`a`.`parcela` AS `parcela`,
					`a`.`parcelas` AS `parcelas`,
					`a`.`nnfe` AS `nnfe`,
					`a`.`vlritem` AS `vlritem`,
					ROUND(sum(IF((`rid`.`valor` IS NOT NULL),
								(`a`.`total` * (`rid`.`valor` / 100)),
								`a`.`total`)),
							2) AS `rateio`,
					`rid`.`valor` AS `vlrrateio`,
					IF((`rid`.`valor` IS NOT NULL),
						'Y',
						'N') AS `rateado`,
					`ri`.`idrateio` AS `idrateio`,
					`ri`.`idrateioitem` AS `idrateioitem`,
					`rid`.`idrateioitemdest` AS `idrateioitemdest`,
					`rid`.`tipoobjeto` AS `tipoobjeto`,
					`rid`.`idobjeto` AS `idobjeto`,
					`u`.`idunidade` AS `idunidade`,
					`u`.`unidade` AS `unidade`,
					IFNULL(`e`.`idempresa`, `a`.`idempresa`) AS `idempresarateio`,
					IFNULL(`e`.`sigla`,  `a`.`sigla`) AS `siglarateio`,
					IFNULL(`e`.`empresa`,`a`.`empresa`) AS `empresarateio`,
					IFNULL(`e`.`corsistema`,
							`a`.`corsistema`) AS `corsistema`,
							a.idunidade as idunidadenf,
					`tu`.`idcentrocusto` AS `idtipounidade`,
					`tu`.`centrocusto` AS `tipounidade`
					FROM
					((((((SELECT 
						`n`.`tiponf` AS `tiponf`,
							`c`.`contaitem` AS `contaitem`,
							`c`.`idcontaitem` AS `idcontaitem`,
							`c`.`cor` AS `cor`,
							`c`.`somarelatorio` AS `somarelatorio`,
							`c`.`previsao` AS `previsao`,
							`cp`.`status` AS `status`,
							`cp`.`tipo` AS `tipo`,
							`c`.`faturamento` AS `faturamento`,
							`c`.`ordem` AS `ordem`,
							IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
							`n`.`idnf` AS `idnf`,
							`cp`.`datareceb` AS `datareceb`,
							`cp`.`idempresa` AS `idempresa`,
							`e`.`empresa` AS `empresa`,
							`e`.`corsistema` AS `corsistema`,
							`e`.`sigla` as `sigla`,
							`cp`.`idagencia` AS `idagencia`,
							`cp`.`idcontapagar` AS `idcontapagar`,
							`cp`.`parcela` AS `parcela`,
							`cp`.`parcelas` AS `parcelas`,
							`p`.`idtipoprodserv` AS `idtipoprodserv`,
							`i`.`idnfitem` AS `idnfitem`,
							`i`.`qtd` AS `qtd`,
							IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
							(((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - `n`.`frete`)) * `n`.`frete`)) / `n`.`total`) * `cp`.`valor`) * -(1)) AS `total`,
							`p`.`tipoprodserv` AS `tipoprodserv`,
							`n`.`nnfe` AS `nnfe`,
							`i`.`vlritem` AS `vlritem`,
							n.idunidade
					FROM
						((((((`nf` `n`
					JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
						AND (`i`.`nfe` = 'Y'))))
					JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
					JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)))
					JOIN `contapagar` `cp` ON (((`cp`.`idobjeto` = `n`.`idnf`)
						AND (`cp`.`tipoobjeto` = 'nf'))))
					LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
					JOIN `empresa` `e` ON ((`e`.`idempresa` = `cp`.`idempresa`)))
					WHERE
						((`cp`.`tipoespecifico` <> 'AGRUPAMENTO')
							AND (`cp`.`status` NOT IN('INATIVO','DEVOLVIDO','CANCELADO'))
							AND (`cp`.`tipo` = 'D')
							AND (`cp`.`valor` > 0)
							AND (`n`.`tiponf` NOT IN ('S' , 'R'))) 
							AND `cp`.`status` <> 'ABERTO'
						
							AND `cp`.`datareceb` BETWEEN  ".evaltipocoldb($_tab, $vo, 'datetime', $data1)." and ".evaltipocoldb($_tab, $data2, 'datetime', $data2)."
					UNION ALL 

					SELECT 
						`n`.`tiponf` AS `tiponf`,
							`c`.`contaitem` AS `contaitem`,
							`c`.`idcontaitem` AS `idcontaitem`,
							`c`.`cor` AS `cor`,
							`c`.`somarelatorio` AS `somarelatorio`,
							`c`.`previsao` AS `previsao`,
							`cp`.`status` AS `status`,
							`cp`.`tipo` AS `tipo`,
							`c`.`faturamento` AS `faturamento`,
							`c`.`ordem` AS `ordem`,
							IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
							`n`.`idnf` AS `idnf`,
							`cp`.`datareceb` AS `datareceb`,
							`cp`.`idempresa` AS `idempresa`,
							`e`.`empresa` AS `empresa`,
							`e`.`corsistema` AS `corsistema`,
							`e`.`sigla` as `sigla`,
							`cp`.`idagencia` AS `idagencia`,
							`cp`.`idcontapagar` AS `idcontapagar`,
							`cp`.`parcela` AS `parcela`,
							`cp`.`parcelas` AS `parcelas`,
							`p`.`idtipoprodserv` AS `idtipoprodserv`,
							`i`.`idnfitem` AS `idnfitem`,
							`i`.`qtd` AS `qtd`,
							IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
							(((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - `n`.`frete`)) * `n`.`frete`)) / `n`.`total`) * sum(`ci`.`valor`)) * -(1)) AS `total`,
							`p`.`tipoprodserv` AS `tipoprodserv`,
							`n`.`nnfe` AS `nnfe`,
							`i`.`vlritem` AS `vlritem`,
							n.idunidade
					FROM
						(((((((`contapagar` `cp`
					JOIN `contapagaritem` `ci` ON (((`cp`.`idcontapagar` = `ci`.`idcontapagar`)
						AND (`ci`.`tipoobjetoorigem` = 'nf'))))
					JOIN `nf` `n` ON ((`ci`.`idobjetoorigem` = `n`.`idnf`)))
					JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
						AND (`i`.`nfe` = 'Y'))))
					JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
					JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)))
					LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
					JOIN `empresa` `e` ON ((`e`.`idempresa` = `cp`.`idempresa`)))
					WHERE
						((`cp`.`tipoespecifico` = 'AGRUPAMENTO')
							AND (`cp`.`status` NOT IN('INATIVO','DEVOLVIDO','CANCELADO'))
							AND (`ci`.`status` NOT IN('INATIVO','DEVOLVIDO','CANCELADO'))
							AND (`cp`.`tipo` = 'D')
							AND (`cp`.`valor` > 0)
							AND (`n`.`tiponf` NOT IN ('S' , 'R'))) 
							AND `cp`.`status` <> 'ABERTO'

							AND `cp`.`datareceb` BETWEEN  ".evaltipocoldb($_tab, $vo, 'datetime', $data1)." and ".evaltipocoldb($_tab, $data2, 'datetime', $data2)."
							and `i`.`qtd` > 0
					group by cp.idcontapagar,i.idnfitem
					UNION ALL SELECT 
						`n`.`tiponf` AS `tiponf`,
							`c`.`contaitem` AS `contaitem`,
							`c`.`idcontaitem` AS `idcontaitem`,
							`c`.`cor` AS `cor`,
							`c`.`somarelatorio` AS `somarelatorio`,
							`c`.`previsao` AS `previsao`,
							`cp`.`status` AS `status`,
							`cp`.`tipo` AS `tipo`,
							`c`.`faturamento` AS `faturamento`,
							`c`.`ordem` AS `ordem`,
							IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
							`n`.`idnf` AS `idnf`,
							`cp`.`datareceb` AS `datareceb`,
							`cp`.`idempresa` AS `idempresa`,
							`e`.`empresa` AS `empresa`,
							`e`.`corsistema` AS `corsistema`,
							`e`.`sigla` as `sigla`,
							`cp`.`idagencia` AS `idagencia`,
							`cp`.`idcontapagar` AS `idcontapagar`,
							`cp`.`parcela` AS `parcela`,
							`cp`.`parcelas` AS `parcelas`,
							`p`.`idtipoprodserv` AS `idtipoprodserv`,
							`i`.`idnfitem` AS `idnfitem`,
							`i`.`qtd` AS `qtd`,
							IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
							((((IFNULL(`i`.`total`, 0) * (`n`.`total` /  ifnull(n.subtotal,n.total))) / `n`.`total`) * `cp`.`valor`) * -(1)) AS `total`,
							`p`.`tipoprodserv` AS `tipoprodserv`,
							`n`.`nnfe` AS `nnfe`,
							`i`.`vlritem` AS `vlritem`,
							n.idunidade
					FROM
						((((((`nf` `n`
					JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
						AND (`i`.`nfe` = 'Y'))))
					JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
					JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)))
					JOIN `contapagar` `cp` ON (((`cp`.`idobjeto` = `n`.`idnf`)
						AND (`cp`.`tipoobjeto` = 'nf'))))
					LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
					JOIN `empresa` `e` ON ((`e`.`idempresa` = `cp`.`idempresa`)))
					WHERE
						((`cp`.`tipoespecifico` <> 'AGRUPAMENTO')
							AND (`cp`.`status` NOT IN('INATIVO','DEVOLVIDO','CANCELADO'))
							AND (`cp`.`tipo` = 'D')
							AND (`cp`.`valor` > 0)
							AND (`n`.`tiponf` IN ('S' , 'R'))) 
							AND `cp`.`status` <> 'ABERTO'

							AND `cp`.`datareceb` BETWEEN  ".evaltipocoldb($_tab, $vo, 'datetime', $data1)." and ".evaltipocoldb($_tab, $data2, 'datetime', $data2)."
							and `i`.`qtd` > 0
					UNION ALL 
					SELECT 
						`n`.`tiponf` AS `tiponf`,
							`c`.`contaitem` AS `contaitem`,
							`c`.`idcontaitem` AS `idcontaitem`,
							`c`.`cor` AS `cor`,
							`c`.`somarelatorio` AS `somarelatorio`,
							`c`.`previsao` AS `previsao`,
							`cp`.`status` AS `status`,
							`cp`.`tipo` AS `tipo`,
							`c`.`faturamento` AS `faturamento`,
							`c`.`ordem` AS `ordem`,
							IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
							`n`.`idnf` AS `idnf`,
							`cp`.`datareceb` AS `datareceb`,
							`cp`.`idempresa` AS `idempresa`,
							`e`.`empresa` AS `empresa`,
							`e`.`corsistema` AS `corsistema`,
							`e`.`sigla` as `sigla`, 
							`cp`.`idagencia` AS `idagencia`,
							`cp`.`idcontapagar` AS `idcontapagar`,
							`cp`.`parcela` AS `parcela`,
							`cp`.`parcelas` AS `parcelas`,
							`p`.`idtipoprodserv` AS `idtipoprodserv`,
							`i`.`idnfitem` AS `idnfitem`,
							`i`.`qtd` AS `qtd`,
							IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
							((((IFNULL(`i`.`total`, 0) * (`n`.`total` /  ifnull(n.subtotal,n.total))) / `n`.`total`) * sum(`ci`.`valor`)) * -(1)) AS `total`,
							`p`.`tipoprodserv` AS `tipoprodserv`,
							`n`.`nnfe` AS `nnfe`,
							`i`.`vlritem` AS `vlritem`,
							n.idunidade
					FROM
						(((((((`contapagar` `cp`
					JOIN `contapagaritem` `ci` ON (((`cp`.`idcontapagar` = `ci`.`idcontapagar`)
						AND (`ci`.`tipoobjetoorigem` = 'nf'))))
					JOIN `nf` `n` ON ((`ci`.`idobjetoorigem` = `n`.`idnf`)))
					JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
						AND (`i`.`nfe` = 'Y'))))
					JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
					JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)))
					LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
					JOIN `empresa` `e` ON ((`e`.`idempresa` = `cp`.`idempresa`)))
					WHERE
						((`cp`.`tipoespecifico` = 'AGRUPAMENTO')
							AND (`cp`.`status` NOT IN('INATIVO','DEVOLVIDO','CANCELADO'))
							AND (`ci`.`status` NOT IN('INATIVO','DEVOLVIDO','CANCELADO'))
							AND (`cp`.`tipo` = 'D')
							AND (`cp`.`valor` > 0)
							AND (`n`.`tiponf` IN ('S' , 'R')))
							AND `cp`.`status` <> 'ABERTO'
							AND `cp`.`datareceb` BETWEEN  ".evaltipocoldb($_tab, $vo, 'datetime', $data1)." and ".evaltipocoldb($_tab, $data2, 'datetime', $data2)."
							and `i`.`qtd` > 0
						group by cp.idcontapagar,i.idnfitem
					) `a`
					JOIN `rateioitem` `ri` ON (((`ri`.`idobjeto` = `a`.`idnfitem`)
						AND (`ri`.`tipoobjeto` = 'nfitem'))))
					JOIN `rateioitemdest` `rid` ON ((`rid`.`idrateioitem` = `ri`.`idrateioitem`)))
					JOIN `unidade` `u` ON (((`u`.`idunidade` = `rid`.`idobjeto`)
						AND (`rid`.`tipoobjeto` = 'unidade'))))
					JOIN `centrocusto` `tu` on `tu`.`idcentrocusto` = `u`.`idcentrocusto`
					JOIN `empresa` `e` ON ((`e`.`idempresa` = `u`.`idempresa`))))
					WHERE
					(`a`.`somarelatorio` = 'Y')  group by idrateioitemdest, idcontapagar ";

		$_sqlresultado =
			"SELECT tipo,
					idempresa,
					qtd,
					un,
					contaitem,
					idcontaitem,
					idtipo,
					idrateio,
					idrateioitem,
					idrateioitemdest,
					idnf,
					nnfe,
					idobjeto,
					tipoobjeto,
					idtipoprodserv,
					tipoprodserv,
					descr,
					vlrlote,
					rateio,
					valor,
					empresa,
					dtemissao,
					corsistema,
					rateado,
					idempresarateio AS idempresarateio,
					siglarateio,
					idagencia,
					datareceb,
					idunidade AS idobjetovinc,
					unidade AS tipoobjetovinc,
					siglarateio AS sigla,
					CONCAT('<a href=\"?_modulo=',		
						CASE
						WHEN idunidadenf in (254,301,355,363,370,413)  THEN 'comprasrh'
						WHEN idunidadenf in (321,335,344,345,349)  THEN 'comprassocios'
						WHEN idunidadenf in (290,312,350,351,378)  THEN 'nfcte'
						WHEN idunidadenf in (313,333,334,364,367)  THEN 'nfentrada'
						WHEN idunidadenf in (312,323,340,342,346)  THEN 'nfrdv'					   
						ELSE 'nfentrada'  
					END,'&_acao=u&_idempresa=',idempresa,'&idnf=',idnf,'\" target=\"_blank\">',if(nnfe='',idnf,nnfe),'</a>') as linknf,
					idunidade,
					unidade,
					empresarateio,
					idtipounidade,
					tipounidade
			FROM (SELECT 'nfitem' AS tipo,
						v.idempresa,
						ROUND(v.qtd, 2) AS qtd,
						v.un,
						v.contaitem,
						v.idcontaitem,
						v.idnfitem AS idtipo,
						v.idrateio,
						v.idrateioitem,
						v.idrateioitemdest,
						v.idnf,
						v.nnfe,
						IFNULL(v.idobjeto, v.idempresa) AS idobjeto,
						IFNULL(v.tipoobjeto, 'aratiar') AS tipoobjeto,
						v.idtipoprodserv,
						v.tipoprodserv,
						v.descricao AS descr,
						v.vlritem AS vlrlote,
						v.rateio AS rateio,
						CONCAT('<a target=\"_blank\" href=\"?_modulo=rateioitemdest&_acao=u&tipo=rateio&stidrateioitemdest=',idrateioitemdest,'\">',round(vlrrateio,2),'%</a>') AS valor,
						v.empresarateio AS empresa,
						v.datareceb AS dtemissao,
						v.corsistema,
						v.rateado,
						v.idempresarateio,
						v.empresarateio AS empresarateio,
						v.siglarateio AS siglarateio2,
						v.datareceb,
						v.idagencia,
						v.siglarateio,
						v.idunidadenf,
						v.idunidade,
						v.unidade,
						v.idtipounidade,
						v.tipounidade				
					FROM (".$vw8despesas.") v
					WHERE 1
					".$clausorg2."
					".$_sqlresultadounidade.") as u
		ORDER BY idempresarateio, tipounidade, tipoobjetovinc, contaitem, tipoprodserv,empresa, descr,dtemissao";

		$_sqlresultado = $strselectfields.",idrateioitemdest, empresa, idempresarateio, corsistema from (".$_sqlresultado." ) bb ";
		echo ("<!-- rateio ".$_sqlresultado." -->");
		//echo "<!-- ".array_key_exists("STRCONTATOCLIENTE", $_SESSION["SESSAO"])." -->";	//echo $_sqlresultado;

		//Abre variavel de sessao para que ela possa ser acessada pelo modulo de interceptacao de eventos
		$_SESSION["SEARCH"]["SQL"] = $_sqlresultado;
		//echo "<!-- ".$_sqlresultado." -->";	

		$_resultados = d::b()->query($_sqlresultado);
		if (!$_resultados) {
			die('<b>Falha na execucao da Consulta para o Report:</b> '.mysql_error()."<br>".$_sqlresultado);
		}

		$_arrtab = retarraytabdef($_tab);

		$_i = 0;
		//var_dump($arrRep["_filtros"][$arrRep["_colvisiveis"]]);
		$_numcolunas = mysql_num_fields($_resultados);
		$_ipagpsqres = mysql_num_rows($_resultados);
		if ($_ipagpsqres == 1) {
			$strs = $_ipagpsqres." Registro encontrado";
		} elseif ($_ipagpsqres > 1) {
			$strs = $_ipagpsqres." Registros encontrados";
		} else {
			$strs = "Nenhum Registro encontrado";
		}

		$_nomeimpressao = "[".md5(date('dmYHis'))."] gerada em [".date(" d/m/Y H:i:s")."]";


		// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
		$sqlfig = "select logosis from empresa where idempresa =".cb::idempresa();
		$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
		$figrel = mysqli_fetch_assoc($resfig);

		$figurarelatorio = $figrel["logosis"];
		?>
		<div style="display:none; text-align: left; font-size: 9px;" class="n_linhas"><span id="nlinha"><?= $strs ?></span></div>

		<table class="tbrepheader">
			<tr>
				<td rowspan="3" style="width:50px;"><img style="width:100%;" src="<?= $figurarelatorio ?>"></td>
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
		<?
		/*
		* MONTA O CABECALHO
		*/

		$conteudoexport; // guarda o conteudo para exportar para csv

		$conteudoexport = '"EMPRESA";"CENTRO DE CUSTO";"UNIDADE";"Categoria";"TIPO";"NFe";"QTD";"UN";"PRODUTO";"RATEIO";"RECEBIMENTO";"RATEIO R$"';

		$conteudoexport .= "\n"; //QUEBRA DE LINHA NO CONTEUDO CSV


		/*
		* Variaveis para cabecalho do report
		*/
		$strpagini = "\n<fieldset class='fldsheader'><legend>Impressão ".$_nomeimpressao."</legend></fieldset>";
		$strtabini = "\n<table id='restbl' class='nivel0 normal'>";
		$strtabinidiff = "\n<table id='restbl' class='nivel0 normal'>";
		$strtabheader = $strtabheader;

		/*
		* MONTA A TABELA
		*/
		$_ilinha = 0; //armazena o ttotal de registros
		$_ilinhaquebra = 0; //armazena parialmente o numero de registros se houver quebra automatica configurada
		$_graphLinha = 0;
		$strnewpage = "<span class='newreppage'></span>";


		$empresarateio = '';
		$tipounidade = '';
		$empresa = '';
		$tipoprodserv = '';
		$contaitem = '';
		$virgula = '';
		while ($_row = mysqli_fetch_array($_resultados)) {
			//EMPRESA;CENTRO DE CUSTO;UNIDADE;Categoria;TIPO;NFe;QTD;UN;PRODUTO;RATEIO;RECEBIMENTO;RATEIO R$

			$conteudoexport .= "\"".strip_tags($_row['empresarateio'])."\";\"".strip_tags($_row['tipounidade'])."\";\"".strip_tags($_row['tipoobjetovinc'])."\";\"".strip_tags($_row['contaitem'])."\";\"".strip_tags($_row['tipoprodserv'])."\";\"".strip_tags($_row['linknf'])."\";\"".number_format(strip_tags($_row['qtd']), 2, ',', '.')."\";\"".strip_tags($_row['un'])."\";\"".strip_tags($_row['descr'])."\";\"".number_format(str_replace("%", "", strip_tags($_row['valor'])), 2, ',', '.')."\";\"".dma($_row['datareceb'])."\";\"".number_format(strip_tags($_row['rateio']), 2, ',', '.')."\"";
			$conteudoexport .= "\n"; //QUEBRA DE LINHA NO CONTEUDO CSV

			$v_idrateioitemdest .= $virgula.$_row['idrateioitemdest'];
			$virgula = ',';			
			$_ilinha++;
			$_i = 0;

			//verifica se o parametro de quebra automatica esta configurado. caso negativo escreve o cabecalho somente 1 vez. E tambem se for a primeira linha, desenha o cabecalho pelo 'else'
			if ($_pbauto > 0 and $_ilinha > 1) {
				//verifica quando é que uma nova quebra sera colocada
				if ($_pbauto > ($_ilinhaquebra + 1)) {
					$_ilinhaquebra++;
				} else {
					echo "\n</table>";
					echo $strnewpage; //QUEBRA A PAGINA
					$_ilinhaquebra = 0;
				}
			} else {
				//Escreve o cabecalho somente uma vez
				if ($_ilinha == 1) {
				}
			}


			###################################### Escreve linhas da <Table>

			//var_dump($_row);

			$valor_empresarateio[$_row[0]] 											+= $_row[16];
			$valor_tipounidade[$_row[0]][$_row[2]] 									+= $_row[16];
			$valor_empresa[$_row[0]][$_row[2]][$_row[4]] 							+= $_row[16];
			$valor_contaitem[$_row[0]][$_row[2]][$_row[4]][$_row[6]] 				+= $_row[16];
			$valor_tipoprodserv[$_row[0]][$_row[2]][$_row[4]][$_row[6]][$_row[8]] 	+= $_row[16];
			$v_total 																+= $_row[16];


			if ($empresarateio <>  $_row[0] || $empresarateio == '') {



				if ($empresarateio == '') {
					echo "\n</table>";
				}

				if ($empresarateio <>  $_row[0] && $empresarateio <> '') {
					echo "</table></td></tr></table></td></tr></table></td></tr></table></td></tr></table></td></tr>";
				}
				$tipounidade = '';
				$empresa = '';
				$tipoprodserv = '';
				$contaitem = '';

				echo $strtabini;
				echo "<tr class='res' style='height:40px; background: #aaa;border-left: 4px solid ".$_row['corsistema']."'>
				<td style='width:60%;text-transform:uppercase;'>".$_row[1]."</td>
				<td style='width:30%'>
					<div title='Valor Total' class='somatorio_valor valor_empresarateio_".$_row[0]."'>R$ 0,00</div>                    
					<div title='Percentual Despesa' class='somatorio_percentual percentual_empresarateio_".$_row[0]."'>R$ 0,00</div>
					<div title='Percentual Faturamento' class='somatorio_percentual_faturamento percentualfaturamento_empresarateio_".$_row[0]."'>R$ 0,00</div>
				</td>
				<td style='width:10%'><i style='float:right' class='fa fa-arrows-v fa-2x cinzaescuro pointer' title='Detalhar' data-toggle='collapse' href='#c_".$_row[0]."' aria-expanded='' ></i></td>
			</tr>
			<tr class='res'>
				<td colspan='12' class='sub' style='padding: 0px 0px;border:none !important'>
					<table class='nivel0 normal collapse _sqlresultado' id='c_".$_row[0]."' style='margin:1px;'> ";
				$empresarateio =  $_row[0];
				//echo $strtabheader;
			}

			//var_dump($_row);

			if ($tipounidade <>  $_row[2] || $tipounidade == '') {

				if ($tipounidade == '') {
					//	echo "\n</table>";
				}

				if ($tipounidade <>  $_row[2] && $tipounidade <> '') {
					//echo '<span id="sim">queeee'.$_row[7].'__'.$_row[14].'</span>';
					echo "</table></td></tr></table></td></tr></table></td></tr></table></td></tr>";
				}
				$empresa = '';
				$tipoprodserv = '';
				$contaitem = '';


				echo "<tr class='res' style='height:40px; background: #bbb;'>
				<td class='sub' style='width: 2% !important;'></td>
				<td style='width:58%'>".$_row[3]."</td>
				<td style='width:30%'>
				<div title='Valor Total' class='somatorio_valor valor_tipounidade_".$_row[0]."_".$_row[2]."'>R$ 0,00</div>                    
				<div title='Percentual Despesa' class='somatorio_percentual percentual_tipounidade_".$_row[0]."_".$_row[2]."'>R$ 0,00</div>
				<div title='Percentual Faturamento' class='somatorio_percentual_faturamento percentualfaturamento_tipounidade_".$_row[0]."_".$_row[2]."'>R$ 0,00</div>
				</td>
				<td style='width:10%'><i style='float:right' class='fa fa-arrows-v fa-2x cinzaescuro pointer' title='Detalhar' data-toggle='collapse' 
				href='#c_".$_row[0]."_".$_row[2]."' aria-expanded='' ></i></td>
			</tr>
			<tr class='res'>
				<td colspan='12' class='sub' style='padding: 2px 0px;border:none !important'>
					<table class='nivel1 normal collapse' id='c_".$_row[0]."_".$_row[2]."'> ";

				$tipounidade =  $_row[2];
				//echo $strtabheader;
			}


			if ($empresa <>  $_row[4] || $empresa == '') {

				if ($empresa == '') {
					//	echo "\n</table>";
				}

				if ($empresa <>  $_row[4] && $empresa <> '') {
					//echo '<span id="sim">queeee'.$_row[7].'__'.$_row[14].'</span>';
					echo "</table></td></tr></table></td></tr></table></td></tr>";
				}
				$tipoprodserv = '';
				$contaitem = '';


				echo "<tr class='res' style='height:40px; background: #ccc;'>
				<td class='sub' style='width: 4% !important;'></td>
				<td style='width:56%'>".$_row[5]."</td>
				<td style='width:30%'>
				<div title='Valor Total' class='somatorio_valor valor_empresa_".$_row[0]."_".$_row[2]."_".$_row[4]."'>R$ 0,00</div>                    
				<div title='Percentual Despesa' class='somatorio_percentual percentual_empresa_".$_row[0]."_".$_row[2]."_".$_row[4]."'>R$ 0,00</div>
				<div title='Percentual Faturamento' class='somatorio_percentual_faturamento percentualfaturamento_empresa_".$_row[0]."_".$_row[2]."_".$_row[4]."'>R$ 0,00</div>
				</td>
				<td style='width:10%'><i style='float:right' class='fa fa-arrows-v fa-2x cinzaescuro pointer' title='Detalhar' data-toggle='collapse' 
				href='#c_".$_row[0]."_".$_row[2]."_".$_row[4]."' aria-expanded='' ></i></td>
			</tr>
			<tr class='res'>
				<td colspan='12' class='sub' style='padding: 2px 0px;border:none !important'>
					<table class='nivel2 normal collapse' id='c_".$_row[0]."_".$_row[2]."_".$_row[4]."'> ";

				$empresa =  $_row[4];
				//echo $strtabheader;
			}

			if ($contaitem <>  $_row[6] || $contaitem == '') {

				if ($contaitem == '') {
					//	echo "\n</table>";
				}

				if ($contaitem <>  $_row[6] && $contaitem <> '') {
					//echo '<span id="sim">olhaaaa'.$_row[7].'__'.$_row[14].'</span>';
					echo "</table></td></tr></table></td></tr>";
				}
				$tipoprodserv = '';

				//echo $strtabini;
				echo "<tr class='res' style='height:40px; background: #ddd;'>
				<td class='sub' style='width: 6% !important;'></td>
				<td style='width:54%'>".$_row[7]."</td>
				<td style='width:30%'>
					<div title='Valor Total' class='somatorio_valor valor_contaitem_".$_row[0]."_".$_row[2]."_".$_row[4]."_".$_row[6]."'>R$ 0,00</div>                    
					<div title='Percentual Despesa' class='somatorio_percentual percentual_contaitem_".$_row[0]."_".$_row[2]."_".$_row[4]."_".$_row[6]."'>R$ 0,00</div>
					<div title='Percentual Faturamento' class='somatorio_percentual_faturamento percentualfaturamento_contaitem_".$_row[0]."_".$_row[2]."_".$_row[4]."_".$_row[6]."'>R$ 0,00</div>
				</td>
				<td style='width:10%'><i style='float:right' class='fa fa-arrows-v fa-2x cinzaescuro pointer' title='Detalhar' data-toggle='collapse'
				href='#c_".$_row[0]."_".$_row[2]."_".$_row[4]."_".$_row[6]."' aria-expanded='' ></i></td>
			</tr>
			<tr class='res'>
				<td colspan='12' class='sub' style='padding: 2px 0px;border:none !important'>
					<table class='nivel3 normal collapse' id='c_".$_row[0]."_".$_row[2]."_".$_row[4]."_".$_row[6]."'> ";
				$contaitem =  $_row[6];
				//echo $strtabheader;
			}

			if ($tipoprodserv <>  $_row[8] || $tipoprodserv == '') {
				//echo '<span id="sim">simmm '.$tipoprodserv.'</span>';
				if ($tipoprodserv == '') {
					//	echo "\n</table>";
				}

				if ($tipoprodserv <>  $_row[8] && $tipoprodserv <> '') {
					//	echo '<span id="sim">olhaaaa'.$_row[7].'__'.$_row[14].'</span>';
					echo "</table></td></tr>";
				}

				//echo $strtabini;
				echo "	<tr class='res' style='height:40px; background: #eee;'>
						<td class='sub' style='width: 8% !important;'></td>
						<td style='width:52%'>".$_row[9]."</td>
						<td style='width:30%'>
							<div title='Valor Total' class='somatorio_valor valor_tipoprodserv_".$_row[0]."_".$_row[2]."_".$_row[4]."_".$_row[6]."_".$_row[8]."'>R$ 0,00</div>                    
							<div title='Percentual Despesa'  class='somatorio_percentual percentual_tipoprodserv_".$_row[0]."_".$_row[2]."_".$_row[4]."_".$_row[6]."_".$_row[8]."'>R$ 0,00</div>
							<div title='Percentual Faturamento' class='somatorio_percentual_faturamento percentualfaturamento_tipoprodserv_".$_row[0]."_".$_row[2]."_".$_row[4]."_".$_row[6]."_".$_row[8]."'>R$ 0,00</div>
						</td>
						<td style='width:10%'><i style='float:right' class='fa fa-arrows-v fa-2x cinzaescuro pointer' title='Detalhar' data-toggle='collapse' 
						href='#c_".$_row[0]."_".$_row[2]."_".$_row[4]."_".$_row[6]."_".$_row[8]."' aria-expanded='' ></i></td>
					</tr>
					<tr class='res'>
						<td colspan='12' class='sub' style='border:none !important; width:8%; padding:2px 0px'>
							<table class='nivel4 ".$tipoprodserv."_".$_row[8]." normal collapse' id='c_".$_row[0]."_".$_row[2]."_".$_row[4]."_".$_row[6]."_".$_row[8]."'>";
				$tipoprodserv =  $_row[8];
				echo $strtabheader;
			}

			//inicial
			$_i = 10;

			echo "\n<tr class=\"res\" ".$_link." ".$_strhlcolor." ".$tipoprodserv."_".$_row[2].">";
			//coloca um contador numerico do lado esquerdo da tabela
			if ($_showtotalcounter == "Y") {
				echo "<td class='tdcounter'>".$_ilinha."</td>";
			}
			if ($_numlinha == "Y") {
				?><td style="background-color:none;"><?= $_ilinha ?></td><?
			}

			/*
			* Montagem dos <TD>s
			*/

		echo "<td class='sub' style='width: 8% !important;'></td>";
		while ($_i < $_numcolunas) {

			$_attrHtml = "";
			$_stralign = "";
			$_strvlrhtml = "";
			$_nomecol = $_arridxcol[$_i];
			$_nomecol = $arrRep["_colvisiveis"][$_i + 1];
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
					$_attrHtml = "acsum='$_nomecol' filtervalue='$_row[$_i]'";

					$_arrsoma[$_tab][$_nomecol] = $_arrsoma[$_tab][$_nomecol] + $_row[$_i];
				}

				//se for para somar o valor do campo
				if ($arrRep["_filtros"][$_nomecol]["acavg"] == 'Y') {

					$_arrsomaavg[$_tab][$_nomecol] = $_arrsomaavg[$_tab][$_nomecol] + $_row[$_i];
				}

				/*
				 * Trata colunas inseridas manualmente para que tenham um datatype
				 */
				if (empty($arrRep["_filtros"][$_nomecol]["datatype"])) {
					$t = preg_replace("/[^0-9.]/", "", $_row[$_i]);
					($t != $_row[$_i]) ? $arrRep["_filtros"][$_nomecol]["datatype"] = "varchar" : $arrRep["_filtros"][$_nomecol]["datatype"] = "double";
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
				} elseif ($arrRep["_filtros"][$_nomecol]["datatype"] == 'decimal' || $arrRep["_filtros"][$_nomecol]["datatype"] == 'double') {
					$graficoY = $_row[$_i];
					$_strvlrhtml = number_format($_row[$_i], 2, ',', '.');
				} else {
					$_strvlrhtml = $_row[$_i];
				}

				$_attrHtml .= "datatype='".$arrRep["_filtros"][$_nomecol]["datatype"]."' mascara='".$arrRep["_filtros"][$_nomecol]["mascara"]."'  eixografico='".$arrRep["_filtros"][$_nomecol]["eixograph"]."' col='".$_nomecol."'  ";

				$_strvlrhtml = aplicaMascara($arrRep["_filtros"][$_nomecol]["mascara"], $_strvlrhtml);



				$arrayGrafico[$_graphLinha][$_nomecol] = $_row[$_i];

				if (is_numeric($_row[$_i])) {
					$total[$_i] = $total[$_i] + $_row[$_i];
				}
				//SE FOR UM NOVO <TD> ELE NÃO COMEÇA COM VIRGULA NO CSV
				if ($_i > 0) {
					//$conteudoexport.=";";//COLOCA A VIRGULA ENTRE OS VALORES 
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
					//$conteudoexport.=strip_tags($_row[$_i]);
				} else {
					//$conteudoexport.="\"".strip_tags($_strvlrhtml)."\"";//GRAVA O VALOR DO CAMPO PARA GERAR O CSV ENTRE ASPAS
				}

				//Se o hyperlink não estiver vazio ele monta o link
				if (!empty($arrRep["_filtros"][$_nomecol]["hyperlink"])) {
					if ($_strvlrhtml != '0.00') {
						//O HREF contém uma barra ('/') o que significa que é uma URL relativa, que vai desconsiderar qualquer pasta informada neste link
						if (strpos($arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$_i], 'pk=')) {

							$arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$_i];
							$valor = explode('pk=', $arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$_i]);
							$valor = explode('&', $valor[1]);
							$campo = $_row[$valor[0]];

							$_hyperlink = "<a target=_blank href='/".$arrRep["_filtros"][$_nomecol]["hyperlink"].$campo."'>".$_strvlrhtml."</a>";
						} else {
							$_hyperlink = "<a target=_blank href='/".$arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$_i]."'>".$_strvlrhtml."</a>";
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
					echo "<td a ".$_attrHtml." ".$_stralign." >".$_hyperlink."</td>";
				} else {
					echo "<td b ".$_attrHtml." ".$_stralign." ".$_hyperlink." ".$_colorlink.">".$_corfont.$_strvlrhtml.$_corfontfim."</td>";
				}
			}
			$_i++;
		}
		//$conteudoexport.="\n";//QUEBRA A LINHA DO CONTEUDO CSV
		$_graphLinha++;
	}

	?>
		</table>
		</td>
		</tr>
		</table>
		</td>
		</tr>
		</table>
		</td>
		</tr>
		</table>
		</td>
		</tr>
		</table>
		</td>
		</tr>
		</table>
		</td>
		</tr>

		<input type="text" name="faturamentoprogramado" id="faturamentoprogramado" placeholder="Faturamento Programado (R$)" />
		<script>
			$(document).ready(function() {
				$("#faturamentoprogramado").on("change paste keyup", function() {
					document.cookie = "c_faturamentoprogramado = " + ("#faturamentoprogramado").val();
					<?php $v_total_totalnf = $_COOKIE['c_faturamentoprogramado']; ?>
				});
			});
		</script>

		<?
		//echo '<pre>'.$_sqlresultado.'</pre>';
		$data1 = explode('/', $data1);
		$data1 = $data1[2].'-'.$data1[1].'-'.$data1[0];

		$data2 = explode(' ', $data2);
		$data2 = explode('/', $data2[0]);
		$data2 = $data2[2].'-'.$data2[1].'-'.$data2[0].' 23:59:59';


		if ($_REQUEST['idempresa'] != '') {
			$sqlc = "and idempresa in (".$_REQUEST['idempresa'].")";
			$sqlc2 = "and `cp`.`idempresa` in (".$_REQUEST['idempresa'].")";
		}
		

		if ($v_idrateioitemdest != '') {


			$_sqlresultadodiff =
				"SELECT rateio,
						tipo,
						idempresa,
						qtd,
						un,
						contaitem,
						idcontaitem,
						idtipo,
						idrateio,
						idrateioitem,
						idrateioitemdest,
						idnf,
						nnfe,
						idobjeto,
						tipoobjeto,
						idtipoprodserv,
						tipoprodserv,
						descr,
						vlrlote,
						valor,
						empresa,
						dtemissao,
						corsistema,
						rateado,
						idempresarateio AS idempresarateio,
						siglarateio,
						idagencia,
						datareceb,
						idobjetovinc,
						tipoobjetovinc,
						sigla,
						idunidade,
						unidade
					FROM
						(SELECT 
							'nfitem' AS tipo,
								e.idempresa,
								ROUND(v.qtd, 2) AS qtd,
								v.un,
								v.contaitem,
								v.idcontaitem,
								v.idnfitem AS idtipo,
								v.idrateio,
								v.idrateioitem,
								v.idrateioitemdest,
								v.idnf,
								v.nnfe,
								IFNULL(v.idobjeto, e.idempresa) AS idobjeto,
								IFNULL(v.tipoobjeto, 'aratiar') AS tipoobjeto,
								v.idtipoprodserv,
								v.tipoprodserv,
								v.descricao AS descr,
								v.vlritem AS vlrlote,
								v.rateio AS rateio,
								CONCAT('<a target=\"_blank\" href=\"?_modulo=rateioitemdest&_acao=u&tipo=rateio&stidrateioitemdest=',idrateioitemdest,'\">',round(vlrrateio,2),'%</a>') AS valor,
								v.empresarateio AS empresa,
								v.datareceb AS dtemissao,
								e.corsistema,
								v.rateado,
								v.idempresarateio,
								v.siglarateio,
								vwo.idobjeto AS idobjetovinc,
								vwo.tipoobjeto AS tipoobjetovinc,
								v.datareceb,
								v.idagencia,
								e.sigla,
								v.idunidadenf,
								v.idunidade,
								v.unidade		
						FROM (".$vw8despesas.") v
						JOIN vw8organogramaunidade vwo on FIND_IN_SET(v.idunidade, vwo.idunidade)
						JOIN empresa e on e.idempresa = v.idempresa
						WHERE rateado = 'Y'
						".$clausorg2."
						AND not idrateioitemdest in (".$v_idrateioitemdest.")
						GROUP BY idrateioitemdest, idcontapagar) as u";

			$_sqlresultadodiff = "select sum(rateio)  as rateio, group_concat(distinct idrateioitemdest) as idrateioitemdest from (".$_sqlresultadodiff." ) bb ";

			//echo '<div style="text-transform:lowercase;"><pre>'.$_sqlresultadodiff.'</pre></div>';
			$_resresultadodiff = d::b()->query($_sqlresultadodiff) or die("Erro ao recuperar Empresa: ".mysql_error());
			$_rowdiff = mysqli_fetch_assoc($_resresultadodiff);
			$v_total_dif = $_rowdiff['rateio'];
		} else {
			$v_total_dif = 0;
		}

		?>
		<span class="normal" style="background:#fff;border:none;height:40px" class="inv">&nbsp;&nbsp;&nbsp;&nbsp;</span>
		</tr>

		</table>

		<table class="normal">

			<!--<?= $_rowdiff['idrateioitemdest']; ?>-->
			<tr style="border: none !important;background: #aaa;height:40px;">
				<td style='width:70%'>RATEIO TOTAL</td>
				<td colspan="7" style='width:25%'>
					<div class='somatorio_valor v_total_valor'>R$ <?= number_format(tratanumero($v_total_valor), 2, ',', '.'); ?></div>
					<div title='Percentual Despesa' class='somatorio_percentual'><?= number_format(tratanumero(($v_total * 100) / ($v_total)), 2, ',', '.') ?>%</div>
				</td>

				<td style='width:5%'></td>
			</tr>

			<tr style="border: none !important;background: #bbb;border-top:1px solid #aaa;height:40px;display:none">
				<td style='width:70%'>DESPESAS <small>empresa(s)</small></td>
				<td colspan="7" style='width:25%'>
					<div title='Valor Total' class='somatorio_valor'>R$ <?= number_format(tratanumero($v_desp_total), 2, ',', '.'); ?></div>
					<div title='Percentual Despesa' class='hide somatorio_percentual'><?= number_format(tratanumero((($v_total + $v_total_diff) * 100) / $v_desp_total), 2, ',', '.') ?>%</div>
					<div title='Percentual Faturamento' class='hide somatorio_percentual_faturamento ' style="background:#bbb"></div>
				</td>

				<td style='width:5%'></td>
			</tr>

			<tr style="border: none !important;background: #aaa;height:40px;">
				<td style='width:70%'>FATURAMENTO <small>planejado</small></td>
				<td colspan="7" style='width:25%'>
					<div title='Valor Total' class='somatorio_valor'>R$ <?= number_format(tratanumero($v_total_totalnf), 2, ',', '.'); ?></div>
					<div title='Percentual Despesa' class='hide somatorio_percentual ' style="background:#bbb"></div>
					<div title='Percentual Faturamento' class='somatorio_percentual_faturamento'><?= number_format(tratanumero((($v_total + $v_total_diff) * 100) / $v_total_totalnf), 2, ',', '.') ?>%</div>
				</td>

				<td style='width:5%'></td>
			</tr>
		</table>
		</tbody>

		</table>

		<!-- Armazena o titulo da consulta para ser usada como titulo do arquivo csv no modulo menurelatorio -->
		<div id="tlt" style="display: none;"><?= $_rep.' '.$_GET["_fds"] ?></div>

	<?
	/*
	 * Desenha a legenda
	 */

	}
	?>

	<?
	if (defined("_RODAPEDIR")) $varfooter = _RODAPEDIR;
	?>
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


//var_dump($valor_tipoprodserv);
foreach ($valor_empresarateio as $keyavx => $valueavx) {
	echo "<input type='hidden' class='normal indicador' id='valor_empresarateio_".$keyavx."' value='R$ ".number_format(tratanumero((float)$valueavx), 2, ',', '.')."'/>";
	$v_total_valor += $valueavx;
	$v_total_valor_empr[$keyavx] += $valueavx;
	echo "<input type='hidden' class='normal indicador' id='percentual_empresarateio_".$keyavx."' value='".number_format(tratanumero(($valueav * 100) / ($v_total + $v_total_dif)), 2, ',', '.')."%'/>";
	$v_total_percentual += number_format(tratanumero(($valueav * 100) / ($v_total + $v_total_dif)), 2, ',', '.');
	echo "<input type='hidden' class='normal indicador' id='percentualfaturamento_empresarateio_".$keyavx."' value='".number_format(tratanumero(($valueav * 100) / $v_total_totalnf), 2, ',', '.')."%'/>";
	$v_total_percentualfaturamento += number_format(tratanumero(($valueav * 100) / $v_total_totalnf));


	foreach ($valor_tipounidade[$keyavx] as $keyav => $valueav) {
		echo "<input type='hidden' class='normal indicador' id='valor_tipounidade_".$keyavx."_".$keyav."' value='R$ ".number_format(tratanumero((float)$valueav), 2, ',', '.')."'/>";

		echo "<input type='hidden' class='normal indicador' id='percentual_tipounidade_".$keyavx."_".$keyav."' value='".number_format(tratanumero(($valueav * 100) / ($v_total + $v_total_dif)), 2, ',', '.')."%'/>";

		echo "<input type='hidden' class='normal indicador' id='percentualfaturamento_tipounidade_".$keyavx."_".$keyav."' value='".number_format(tratanumero(($valueav * 100) / $v_total_totalnf), 2, ',', '.')."%'/>";

		foreach ($valor_empresa[$keyavx][$keyav] as $keya => $valuea) {
			echo "<input type='hidden' class='normal indicador' id='valor_empresa_".$keyavx."_".$keyav."_".$keya."' value='R$ ".number_format(tratanumero((float)$valuea), 2, ',', '.')."'/>";

			echo "<input type='hidden' class='normal indicador' id='percentual_empresa_".$keyavx."_".$keyav."_".$keya."' value='".number_format(tratanumero(($valuea * 100) / ($v_total + $v_total_dif)), 2, ',', '.')."%'/>";

			echo "<input type='hidden' class='normal indicador' id='percentualfaturamento_empresa_".$keyavx."_".$keyav."_".$keya."' value='".number_format(tratanumero(($valuea * 100) / $v_total_totalnf), 2, ',', '.')."%'/>";


			foreach ($valor_contaitem[$keyavx][$keyav][$keya] as $key => $value) {
				echo "<input type='hidden' class='normal indicador' id='valor_contaitem_".$keyavx."_".$keyav."_".$keya."_".$key."' value='R$ ".number_format(tratanumero((float)$value), 2, ',', '.')."'/>";

				echo "<input type='hidden' class='normal indicador' id='percentual_contaitem_".$keyavx."_".$keyav."_".$keya."_".$key."' value='".number_format(tratanumero(($value * 100) / ($v_total + $v_total_dif)), 2, ',', '.')."%'/>";

				echo "<input type='hidden' class='normal indicador' id='percentualfaturamento_contaitem_".$keyavx."_".$keyav."_".$keya."_".$key."' value='".number_format(tratanumero(($value * 100) / $v_total_totalnf), 2, ',', '.')."%'/>";


				foreach ($valor_tipoprodserv[$keyavx][$keyav][$keya][$key] as $k => $v) {
					echo "<input type='hidden' class='normal indicador' id='valor_tipoprodserv_".$keyavx."_".$keyav."_".$keya."_".$key."_".$k."' value='R$ ".number_format(tratanumero((float)$v), 2, ',', '.')."'/>";

					echo "<input type='hidden' class='normal indicador' id='percentual_tipoprodserv_".$keyavx."_".$keyav."_".$keya."_".$key."_".$k."' value='".number_format(tratanumero(($v * 100) / ($v_total + $v_total_dif)), 2, ',', '.')."%'/>";

					echo "<input type='hidden' class='normal indicador' id='percentualfaturamento_tipoprodserv_".$keyavx."_".$keyav."_".$keya."_".$key."_".$k."' value='".number_format(tratanumero(($v * 100) / $v_total_totalnf), 2, ',', '.')."%'/>";
				}
			}
		}
	}
}




echo "<input type='hidden' class='normal indicador' id='v_total_valor' value='R$ ".number_format(tratanumero((float)$v_total_valor), 2, ',', '.')."'/>";
echo "<input type='hidden' class='normal indicador' id='v_total_percentual' value='".number_format(tratanumero(($v_total_valor * 100) / $v_total_totalnf), 2, ',', '.')."%'/>";
echo "<input type='hidden' class='normal indicador' id='v_total_percentualfaturamento' value='".number_format(tratanumero(($v_total_valor * 100) / $v_total_totalnf), 2, ',', '.')."%'/>";


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

	$('.indicador').each(function(index, item) {
		//debugger;
		$('.' + $(item).attr('id')).html($(item).val());
	});
</script>