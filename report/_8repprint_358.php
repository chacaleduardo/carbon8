<?
include_once("../inc/php/validaacesso.php");

baseToGet($_GET["_filtros"]);


function seo_friendly_url($string){
    $string = str_replace(array('[\', \']'), '', $string);
    $string = preg_replace('/\[.*\]/U', '', $string);
    $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
    $string = htmlentities($string, ENT_COMPAT, 'utf-8');
    $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string );
    $string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/') , '-', $string);
    return strtolower(trim($string, '-'));
}
function containsDecimal( $value ) {

    if ( strpos( $value, "." ) !== false ) {
        return true;
    }
    return false;
}
function verificaData($data){
//cria um array
$array = explode('/', $data);

	//garante que o array possue tres elementos (dia, mes e ano)
	if(count($array) == 3){
		$dia = (int)$array[0];
		$mes = (int)$array[1];
		$ano = (int)$array[2];

		//testa se a data é válida
		if(checkdate($mes, $dia, $ano)){
			return true;
		}else{
		return false;
		}
	}else{
		return false;
	}
}

function obterMesesNoIntervalo($dataInicial, $dataFinal) {
    $meses = array();
    

    $dataAtual = new DateTime($dataInicial);
    $dataFinal = new DateTime($dataFinal);

    while ($dataAtual <= $dataFinal) {
        $meses['mes'][$dataAtual->format('m')] = $dataAtual->format('m');
        $meses['ano'][$dataAtual->format('Y')] = $dataAtual->format('Y');
        $dataAtual->modify('+1 month');
    }

    return $meses;
}


if(!empty($_GET["reportexport"])){
	ob_start();//não envia nada para o browser antes do termino do processamento
}

if($_GET['_idrep']==254){
	$strFluxoDre= " and c.fluxocaixa='Y' ";
}else{
	$strFluxoDre=" and c.dre='Y' ";
}

$sql_check_LP = "select 1 from carbonnovo._lprep where idlp in (".getModsUsr("LPS").") and idrep= ".$_GET["_idrep"]."";
$chk = d::b()->query($sql_check_LP) or die('ERRO AO VERIFICAR LP');

echo("<!-- SQL_LP: ".$sql_check_LP." -->");
if(mysqli_num_rows($chk) == 0){
	die('<div>Você não Possui permissão para acessar esse Relatório</div>');
}

$_modulo = $_GET["_modulo"];
if ($_GET["relatorio"]){
	$_idrep = $_GET["relatorio"];
}else{
	$_idrep = $_GET["_idrep"];
}

$tipo = 'EMPRESA';


if(empty($_idrep)){
	die("Relat&oacute;rio n&atilde;o informado!");
}

if ($_idrep == 21){
	d::b()->query("SET sql_mode = 'NO_UNSIGNED_SUBTRACTION';");
}
//Recupera a definicao das colunas da view ou table default da pagina
$arrRep=getConfRelatorio($_idrep);
//Facilita a utilização do array
$arrRep=$arrRep[$_idrep];

//var_dump($arrRep);
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
$arrayGrafico=array();
$tipoGraphRelatorio = $arrRep["tipograph"];
?>
<html>
<head>
<title><?=$_rep.' '.$_GET["_fds"]?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/css/fontawesome/font-awesome.min.css" media="all" rel="stylesheet" type="text/css" />
<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
<script src="../inc/js/moment/moment.min.js"></script>



<style type="text/css">
	table { page-break-inside:auto; width:100% }
	tr    { page-break-inside:avoid; page-break-after:auto }
	thead { display:table-header-group }
	tfoot { display:table-footer-group }
	@media print{    
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

	td.inv, td.tot {
		border: none !important;
		background: #bbb;
	}
	#conteudo_relatorio {
		margin-bottom: 60px !important;
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
	while (list($_col, $_val) = each($_GET)) {
		$_between = false;
		if(!empty($_val) and ($_col != "_modulo") and ($_col != "_rep") and (substr($_col,-2) != "_2")){

			//Montar clausula para colunas between
			if (substr($_col,-2)=="_1"){
				$_col = substr($_col,0,-2); //Transforma do nome do campo para capturar informacoes de tipo
				$_colval1 = $_GET[$_col."_1"];
				$_colval2 = $_GET[$_col."_2"];
				if (verificaData($_colval2)){
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
			if($_psqkey=="Y" and $_insmanual=="N"){
				if($_between){	
					$_sqlwhere .= $_and . "(" . $_col . " between " . evaltipocoldb($_tab, $_col, $_datatype, $_colval1) . " and " . evaltipocoldb($_tab, $_colval2, $_datatype, $_colval2) . ")";
				}else{
						
					
					if ($_like == 'Y'){
						if ($_datatype == 'text'){
							$_datatype = 'varchar';
						}
						$_sqlwhere .= $_and . $_col . " like '%" . substr(substr(evaltipocoldb($_tab, $_col, $_datatype, $_val), 1), 0, -1)."%'" ;
					}else if ($_findinset == 'Y'){
						if ($_datatype == 'text'){
							$_datatype = 'varchar';
						}
						$_sqlwhere .= $_and." find_in_set(".$_val." , ".$_col.") ";
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

	$_sqldata = '';	
	if(!empty($arrRep["_datas"])){
	
		if ($_REQUEST['_fds']){
			//echo 'aqui';
			$data = explode('-',$_REQUEST['_fds']);
			$data1 = $data[0];
			$data2 = $data[1];

		
			$dataInicial=validadate($data1);
			$dataFinal=validadate($data2);


			$listaDeMeses = obterMesesNoIntervalo($dataInicial, $dataFinal);


			$virgm='';
			$strmes='';
			foreach ($listaDeMeses['mes'] as $mes) {
				$strmes=$strmes.$virgm.$mes;
				$virgm=',';
			}
			//$strmes

			$virg='';
			$strano='';
			foreach ($listaDeMeses['ano'] as $ano) {
				$strano=$strano.$virg.$ano;
				$virg=',';
			}


			if (verificaData($data2)){
				 $data2 = $data2.' 23:59:59';
				
			}
			
		
		if ($data1 and $data2){
			while (list($ko, $vo) = each($arrRep["_datas"])) {
				//echo '<br>';
				$_sqldata .= $_or . "(" . $vo . " between " . evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2) . ")";	
				$_or = " or ";
			}
		}
		
		$_sqldata = ' and ('.$_sqldata.') ';	
		}
		
	
	
	}
				
			
	if(!empty($_GET["_fts"])){
		//Ajusta preferencias do usuario
		userPref("u", $_modulo."._fts", $_GET["_fts"]);
		
		
		
		$arrFk = retPkFullTextSearch($_tabfull, $_GET["_fts"]/*, $_GET["_pagina"],$_arrModConf["limite"]*/);
		$countArrFk=$arrFk["foundRows"];
		$aspa = "'";
		if($countArrFk>0){
			
			$strPkFts = implode(",", $arrFk["arrPk"]);
			$strPkFts = $aspa . implode(($aspa.",".$aspa), $arrFk["arrPk"]) . $aspa;
			$str_fts = " and ".$_chavefts . " in (".$strPkFts.")";
		}
	}
			
			
	//Abre variavel de sessao para que ela possa ser acessada pelo modulo de interceptacao de eventos
	//Isto permitira saber se existe clausula where ou nao
	$_SESSION["SEARCH"]["CLAUSULAS"] = (string)$_iclausulas;
	
	if($_iclausulas > 0){
		$_sqlresultado = getDbTabela($_tab).".". $_tab ." ".$_sqlwhere;
		if(empty($_GET['idempresa']) && cb::habilitarMatriz() == 'N'){
			//$_sqlresultado .=getidempresa('idempresa',$_modulo);
			$wIdempresa = ($rowEmpresa['idempresa'] == 0) ? cb::idempresa() : $rowEmpresa['idempresa'];

		    $_sqlresultado .=  $_and." idempresa in (".$wIdempresa.")";
		    $_and = " and ";
		}elseif(empty($_GET['idempresa']) && cb::habilitarMatriz() != 'N'){
			$sqlEmpresa = "SELECT ifnull(group_concat(e.idempresa),0) as idempresa
                        FROM empresa e JOIN  objempresa o ON o.empresa = e.idempresa
                        WHERE e.status = 'ATIVO' AND o.idobjeto = '".$_SESSION["SESSAO"]["IDPESSOA"]."' AND o.objeto = 'pessoa'";
			$resEmpresa = d::b()->query($sqlEmpresa) or die("Erro ao recuperar Empresa: ".mysql_error());
			$rowEmpresa = mysqli_fetch_assoc($resEmpresa);

        $wIdempresa = ($rowEmpresa['idempresa'] == 0) ? cb::idempresa() : $rowEmpresa['idempresa'];

		$_sqlresultado .= $_and." idempresa in (".$wIdempresa.")";
		$_and = " and ";
		}
		
	}else{
		$_sqlresultado = getDbTabela($_tab).".". $_tab ." ".$_sqlwhere;
		if(empty($_GET['idempresa']) && cb::habilitarMatriz() == 'N'){

			//$_sqlresultado .=" where 1 ".getidempresa('idempresa',$_modulo);
			$wIdempresa = ($rowEmpresa['idempresa'] == 0) ? cb::idempresa() : $rowEmpresa['idempresa'];

	    	$_sqlresultado .= $_and." idempresa in (".$wIdempresa.")";
	        $_and = " and ";
		    

		}elseif(empty($_GET['idempresa']) && cb::habilitarMatriz() != 'N'){

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
	
	if (trim($_compl) != ''){
		$_sqlresultado .= ' '.trim($_compl);
	}


	

	
	// RETRINGIR CONSULTA A UNIDADE MARCADA NA LP-------------------------------------------------------------
	$lps=getModsUsr("LPS");
	$sqlFlgUnidade="Select flgunidade from "._DBCARBON."._lprep where idrep=".$_idrep." and idlp in(".$lps.") order by flgunidade desc";

	$rrep = d::b()->query($sqlFlgUnidade) or die("Erro ao verificar unidade no relatorio: ".mysql_error(d::b()));
	if(mysql_num_rows($rrep)>=1 ){ 
		while ($r = mysql_fetch_array($rrep)){
			if($r['flgunidade']=='Y'){
				if ($wIdempresa == ''){
			    	$_sqlresultadounidade .= " and idempresa = ".cb::idempresa()."";
				}else{
				    	$_sqlresultadounidade .= " and idempresa in (".$wIdempresa.")";
				}
				$_sqlresultadounidade .= " and exists (select 1 from vw8PessoaUnidade pu where pu.idpessoa = ".$_SESSION['SESSAO']['IDPESSOA']." and pu.idunidade = ".$_tab.".idunidade)";
				break;
			}
		}
	}


	// RETRINGIR CONSULTA A HIERARQUIA ORGANOGRAMA QUANDO MARCADO NA LPREP-------------------------------------------------------------
	$sqlflgidpessoa="Select flgidpessoa, flgcontaitem from "._DBCARBON."._lprep where idrep=".$_idrep." and idlp in(".$lps.") and flgidpessoa = 'Y'  order by flgidpessoa desc";

	$rrep = d::b()->query($sqlflgidpessoa) or die("Erro ao verificar flgidpessoa no relatorio: ".mysql_error(d::b()));
	if(mysql_num_rows($rrep)>=1 ){ 
		$_sqlresultadounidade .= getOrganogramaRep('idpessoafun');

	}
	
		// RETRINGIR CONSULTA A HIERARQUIA ORGANOGRAMA QUANDO MARCADO NA LPREP-------------------------------------------------------------
	$sqlflgidpessoa="Select flgidpessoa, flgcontaitem from "._DBCARBON."._lprep where idrep=".$_idrep." and idlp in(".$lps.") and flgcontaitem = 'Y'  order by flgidpessoa desc";

	$rrep = d::b()->query($sqlflgidpessoa) or die("Erro ao verificar flgidpessoa no relatorio: ".mysql_error(d::b()));
	if(mysql_num_rows($rrep)>=1 and $_SESSION["SESSAO"]["MIGRACAO"]["CONTAITEM"] != ''){ 
		$_sqlresultadounidade .= " and idcontaitem in (".$_SESSION["SESSAO"]["MIGRACAO"]["CONTAITEM"].")";
	}





	$strselectfields = "";
	$strord = "";
	$strvirg = "";

	//Concatenar campos para o select
	if(!empty($arrRep["_colvisiveis"])){

		while (list($ko, $vo) = each($arrRep["_colvisiveis"])) {
			if ($arrRep["_filtros"][$vo]["tsum"] == 'Y'){
					if (containsDecimal($vo)){
						$strselectfields .= $strvirg.'sum('.$vo.') as '.$vo;
					}else{
						$strselectfields .= $strvirg.'sum('.$vo.') as '.$vo;
					}
			}else{
				$strselectfields .= $strvirg.$vo;
			}
			$strvirg = ", ";
		}
	
		$strselectfields = "select ".$strselectfields." "; 

		$strselectfields = "select  idcontaitem , contaitem , idtipoprodserv , tipoprodserv ,
		idcontatipo , contatipo ,idcontatipogrupo , grupo  ,  
		linknf , replace(round(qtd,2),'.',',')  as qtd , un ,
		descr  , vlritem , datareceb ,  valor  , corsistema,tipogrupo,idsgrupo,previsao,contaitemprev,contatipoprev,contatipogrupoprev,percentual"; 

		
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
	$strvirg = "";
	if(!empty($arrRep["_groupby"])){
		//Ordenar pelo valor indicado no campo 'ordseq', que é a KEY deste array
		ksort($arrRep["_groupby"]);

		//Transformar em string de 'Order By' para o banco
		while (list($ko, $vo) = each($arrRep["_groupby"])) {
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
	 
if($_REQUEST['idempresa']){
	// $clausorg2 .= " and a.idempresa in ( ".$_REQUEST['idempresa'].") ";

	$sqlEmpresa = "SELECT *	FROM empresa e WHERE e.idempresa  in ( ".$_REQUEST['idempresa'].")";

	$resEmpresa = d::b()->query($sqlEmpresa) or die("Erro ao recuperar Empresa2: ".mysql_error());
	$rowE = mysqli_fetch_assoc($resEmpresa);

	if($rowE['filial']=='Y'){
		
		$sqlMF = "SELECT  group_concat(idmatriz) as idmatriz  FROM matrizconf where idempresa in (".$_REQUEST['idempresa'].") AND matrizfilial = 'Y'";
		$resMF = d::b()->query($sqlMF) or die("Erro ao buscar relacao filial matriz: ".mysql_error());
		$rowMF = mysqli_fetch_assoc($resMF);

		$clausulaIdempresa = " and ( n.idempresafat in (".$_REQUEST['idempresa'].")  or n.idempresa in  (".$_REQUEST['idempresa']."))";
		$clausulaIdempresaNFS=" and n.idempresa in  (".$_REQUEST['idempresa'].") ";
		$_idempresaMatriz=$rowMF['idmatriz'];
	}else{
		$clausulaIdempresa = " and n.idempresa in (".$_REQUEST['idempresa'].")";
		$clausulaIdempresaNFS=" and n.idempresa in  (".$_REQUEST['idempresa'].") ";
		$_idempresaMatriz=$_REQUEST['idempresa'];
	}
}

if($_REQUEST['idcontaitem']){
	$clausorg2 .= " and a.idcontaitem in ( ".$_REQUEST['idcontaitem'].") ";
}

if($_REQUEST['idcontatipogrupo']){
	$clausulaidcontatipogrupo = " and g.idcontatipogrupo in ( ".$_REQUEST['idcontatipogrupo'].") ";
}

if($_REQUEST['idcontatipo']){
	$clausulaidcontatipo = " and t.idcontatipo in ( ".$_REQUEST['idcontatipo'].") "; 
	$leftcontatipo = "";
}else{
	$leftcontatipo = "LEFT";
}

if($_REQUEST['idtipoprodserv']){
	$clausorg2 .= " and a.idtipoprodserv in ( ".$_REQUEST['idtipoprodserv'].") ";
}
/*
if($_REQUEST['idagencia']){
	$clausorg2 .= " and a.idagencia in ( ".$_REQUEST['idagencia'].") ";
}
	*/


if(!empty($_REQUEST['status'])){
	$cstatus = " and cp.status in(".$_REQUEST['status'].") ";
}else{
   $cstatus='';
}

if($_REQUEST['tiporelatorio']){
	$strdate = 'datareceb';
}

if($_REQUEST['tipo']){
	$cp_tipo = $_REQUEST['tipo'];
}else{
	$cp_tipo = 'C,D';
}


$stragencia= $_REQUEST['idagencia'];
$arragencia = explode(",", $stragencia);
$stidagenciaunico = $arragencia[0];

$strempresa= $_REQUEST['idempresa'];
$arrempresa = explode(",", $strempresa);
$stidempresaunico = $arrempresa[0];

$vwcontaitemtp=" union all
					select 
					'' as tiponf,c.contaitem,c.idcontaitem,c.idcontatipo,c.cor,c.somarelatorio,c.previsao  ,'' as status,'' as tipo,c.faturamento
					,c.ordem, 
					'' as `descricao`,
					'' as `idnf`,
					'' as `datareceb`,
					'1' as `idempresa`,
					`e`.`empresa` AS `empresa`,
					`e`.`corsistema` AS `corsistema`,
					`p`.`idtipoprodserv` AS `idtipoprodserv`,
					'' as `idnfitem`,
					'' as `qtd`,
					'' as`un`,
					0 as  total,
					`p`.`tipoprodserv` AS `tipoprodserv`,
					'' as `nnfe`,
					'0' as `vlritem`,
					1 as idunidade,
					c.fluxocaixa,
					c.dre,
                    '' as emissao
					from contaitem c 
					join contaitemtipoprodserv ci on(ci.idcontaitem=c.idcontaitem)
					join tipoprodserv p on(p.idtipoprodserv = ci.idtipoprodserv)
					JOIN `empresa` `e` ON (`e`.`idempresa` = `p`.`idempresa`)
					where c.idempresa=".$stidempresaunico." ";

if($cp_tipo=='C,D' || $cp_tipo=='D'){
	$vw8despesas="
	SELECT -- debito
		`n`.`tiponf` AS `tiponf`,
		`c`.`contaitem` AS `contaitem`,
		`c`.`idcontaitem` AS `idcontaitem`,
		c.idcontatipo,
		`c`.`cor` AS `cor`,
		`c`.`somarelatorio` AS `somarelatorio`,
		`c`.`previsao` AS `previsao`,
		`n`.`status` AS `status`,
		`n`.`tipocontapagar` AS `tipo`,
		`c`.`faturamento` AS `faturamento`,
		`c`.`ordem` AS `ordem`,
		IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
		`n`.`idnf` AS `idnf`,
		`n`.`dtemissao` AS `datareceb`,
		`n`.`idempresa` AS `idempresa`,
		`e`.`empresa` AS `empresa`,
		`e`.`corsistema` AS `corsistema`,
		`p`.`idtipoprodserv` AS `idtipoprodserv`,
		`i`.`idnfitem` AS `idnfitem`,
		`i`.`qtd` AS `qtd`,
		IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
		(((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - ifnull(`n`.`frete`,0))) * ifnull(`n`.`frete`,0))) / `n`.`total`) * `n`.`total`) * -(1)) AS `total`,
		`p`.`tipoprodserv` AS `tipoprodserv`,
		`n`.`nnfe` AS `nnfe`,
		`i`.`vlritem` AS `vlritem`,
		n.idunidade,
		c.fluxocaixa,
		c.dre,
		n.dtemissao as emissao
		FROM
			((((((`nf` `n`
			JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
			AND (`i`.`nfe` = 'Y') AND (`i`.`cobrar` = 'Y'))))
			JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
			JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)  ".$strFluxoDre." ))
			)
			LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
			JOIN `empresa` `e` ON ((`e`.`idempresa` = `n`.`idempresa`)))
		WHERE
		n.geracontapagar='Y' 
			and `n`.`tipocontapagar`='D'
			AND (`n`.`tiponf` NOT IN ('S' , 'R','V'))
			and `i`.`qtd` > 0    	
			and n.status not in('CANCELADO','DEVOLVIDO','INUTILIZADO','RECUSADO','REPROVADO') 
			".$clausulaIdempresa."   
			and n.dtemissao BETWEEN    ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
	UNION ALL -- debito
		SELECT 
		`n`.`tiponf` AS `tiponf`,
			`c`.`contaitem` AS `contaitem`,
			`c`.`idcontaitem` AS `idcontaitem`,
			c.idcontatipo,
			`c`.`cor` AS `cor`,
			`c`.`somarelatorio` AS `somarelatorio`,
			`c`.`previsao` AS `previsao`,
			`n`.`status` AS `status`,
			`n`.`tipocontapagar` AS `tipo`,
			`c`.`faturamento` AS `faturamento`,
			`c`.`ordem` AS `ordem`,
			IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
			`n`.`idnf` AS `idnf`,
			n.dtemissao AS `datareceb`,
			`n`.`idempresa` AS `idempresa`,
			`e`.`empresa` AS `empresa`,
			`e`.`corsistema` AS `corsistema`,
			`p`.`idtipoprodserv` AS `idtipoprodserv`,
			`i`.`idnfitem` AS `idnfitem`,
			`i`.`qtd` AS `qtd`,
			IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
			((((IFNULL(`i`.`total`, 0) * (`n`.`total` / ifnull(n.subtotal,n.total))) / `n`.`total`) * `n`.`total`) * -(1)) AS `total`,
			`p`.`tipoprodserv` AS `tipoprodserv`,
			`n`.`nnfe` AS `nnfe`,
			`i`.`vlritem` AS `vlritem`,
			n.idunidade,
			c.fluxocaixa,
			c.dre,
			n.dtemissao as emissao
		FROM
			((((((`nf` `n`
			JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
			AND (`i`.`nfe` = 'Y') AND (`i`.`cobrar` = 'Y'))))
			JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
			JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)   ".$strFluxoDre." )))
			LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
			JOIN `empresa` `e` ON ((`e`.`idempresa` = `n`.`idempresa`)))
		WHERE
			n.geracontapagar='Y'
			and `n`.`tipocontapagar`='D'
			AND (`n`.`tiponf` IN ('S' , 'R')) 
			and n.status not in('CANCELADO','DEVOLVIDO','INUTILIZADO','RECUSADO','REPROVADO')   
			".$clausulaIdempresa."   
			and n.dtemissao BETWEEN    ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
			and `i`.`qtd` > 0    
";
}

if($cp_tipo=='C,D'){
	$vw8despesas.="union all -- credito";
}

if($cp_tipo=='C,D' || $cp_tipo=='C'){
	$vw8despesas.=" 
	-- tipovenda
	SELECT n.tiponf,c.contaitem,c.idcontaitem,c.idcontatipo,c.cor,c.somarelatorio,c.previsao
			,n.status,`n`.`tipocontapagar` as tipo,c.faturamento
			,c.ordem,   
			IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
			`n`.`idnf` AS `idnf`,
			`n`.`dtemissao` AS `datareceb`,
			`n`.`idempresa` AS `idempresa`,
			`e`.`empresa` AS `empresa`,
			`e`.`corsistema` AS `corsistema`,
			`p`.`idtipoprodserv` AS `idtipoprodserv`,
			`i`.`idnfitem` AS `idnfitem`,
			`i`.`qtd` AS `qtd`,
			IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
			CASE
				WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0)+ IFNULL(`i`.`voutro`, 0) + IFNULL(`i`.`vseg`, 0))/n.total)*n.total),2)  
				ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total- ifnull(`n`.`frete`,0) ))* (ifnull(`n`.`frete`,0))))/n.total)*n.total),2)
			END as total,
			`p`.`tipoprodserv` AS `tipoprodserv`,
			`n`.`nnfe` AS `nnfe`,
			`i`.`vlritem` AS `vlritem`,
			n.idunidade,
			c.fluxocaixa,
			c.dre,
			n.dtemissao as emissao
		FROM nf n 
			join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0 AND (`i`.`cobrar` = 'Y'))          
			join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
			join contaitem c on(c.idcontaitem=i.idcontaitem    ".$strFluxoDre."   )  
			LEFT JOIN `prodserv` `ps` ON (`ps`.`idprodserv` = `i`.`idprodserv`)
			JOIN `empresa` `e` ON (`e`.`idempresa` = `n`.`idempresa`)
		where n.geracontapagar='Y' 
			and `n`.`tiponf`='V'
			-- and n.nnfe is not null
			and i.idprodserv is null 
			and n.status not in('CANCELADO','DEVOLVIDO','INUTILIZADO','RECUSADO','REPROVADO') 
			".$clausulaIdempresa."    
			and n.dtemissao  BETWEEN    ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
	UNION ALL -- debito
		SELECT 
			`n`.`tiponf` AS `tiponf`,
			`c`.`contaitem` AS `contaitem`,
			`c`.`idcontaitem` AS `idcontaitem`,
			c.idcontatipo,
			`c`.`cor` AS `cor`,
			`c`.`somarelatorio` AS `somarelatorio`,
			`c`.`previsao` AS `previsao`,
			`n`.`status` AS `status`,
			`n`.`tipocontapagar` AS `tipo`,
			`c`.`faturamento` AS `faturamento`,
			`c`.`ordem` AS `ordem`,
			IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
			`n`.`idnf` AS `idnf`,
			`n`.`dtemissao` AS `datareceb`,
			`n`.`idempresa` AS `idempresa`,
			`e`.`empresa` AS `empresa`,
			`e`.`corsistema` AS `corsistema`,
			`p`.`idtipoprodserv` AS `idtipoprodserv`,
			`i`.`idnfitem` AS `idnfitem`,
			`i`.`qtd` AS `qtd`,
			IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
			(((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - ifnull(`n`.`frete`,0))) * ifnull(`n`.`frete`,0))) / `n`.`total`) * `n`.`total`) ) AS `total`,
			`p`.`tipoprodserv` AS `tipoprodserv`,
			`n`.`nnfe` AS `nnfe`,
			`i`.`vlritem` AS `vlritem`,
			n.idunidade,
			c.fluxocaixa,
			c.dre,
			n.dtemissao as emissao
		FROM
			((((((`nf` `n`
			JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
			AND (`i`.`nfe` = 'Y') AND (`i`.`cobrar` = 'Y'))))
			JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
			JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)  ".$strFluxoDre." ))
			)
			LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
			JOIN `empresa` `e` ON ((`e`.`idempresa` = `n`.`idempresa`)))
		WHERE
			n.geracontapagar='Y' 
			and `n`.`tipocontapagar`='C'
			AND (`n`.`tiponf` NOT IN ('S' , 'R','V'))
			and `i`.`qtd` > 0    	
			and n.status not in('CANCELADO','DEVOLVIDO','INUTILIZADO','RECUSADO','REPROVADO') 
			".$clausulaIdempresa."   
			and n.dtemissao BETWEEN    ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
	
	union all -- tipovenda
		SELECT n.tiponf,c.contaitem,c.idcontaitem,c.idcontatipo,c.cor,c.somarelatorio,c.previsao
			,n.status,`n`.`tipocontapagar` as tipo,c.faturamento
			,c.ordem,  
			IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
			`n`.`idnf` AS `idnf`,
			`n`.`dtemissao` AS `datareceb`,
			`n`.`idempresa` AS `idempresa`,
			`e`.`empresa` AS `empresa`,
			`e`.`corsistema` AS `corsistema`,
			`p`.`idtipoprodserv` AS `idtipoprodserv`,
			`i`.`idnfitem` AS `idnfitem`,
			`i`.`qtd` AS `qtd`,
			IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
			CASE
				WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0)+ IFNULL(`i`.`voutro`, 0)+ IFNULL(`i`.`vseg`, 0))/n.total)*n.total),2)  
				ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-ifnull(`n`.`frete`,0)))* (ifnull(`n`.`frete`,0))))/n.total)*n.total),2)
			END as total,
			`p`.`tipoprodserv` AS `tipoprodserv`,
			`n`.`nnfe` AS `nnfe`,
			`i`.`vlritem` AS `vlritem`,
			n.idunidade,
			c.fluxocaixa,
			c.dre,
			n.dtemissao as emissao
		FROM nf n 
			join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0 AND (`i`.`cobrar` = 'Y') ) 
			join prodserv ps on(ps.idprodserv =i.idprodserv)
			join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
			join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
			join contaitem c on(c.idcontaitem=pc.idcontaitem   ".$strFluxoDre."  )
			JOIN `empresa` `e` ON (`e`.`idempresa` = `n`.`idempresa`)
		where  n.geracontapagar='Y' 
			and `n`.`tiponf`='V'  
			-- and n.nnfe is not null
			and n.status not in('CANCELADO','DEVOLVIDO','INUTILIZADO','RECUSADO','REPROVADO') 
			".$clausulaIdempresa."     
			AND n.dtemissao BETWEEN   ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
	UNION ALL -- C as tipo
		SELECT
			'SV' as tiponf,c.contaitem,c.idcontaitem,c.idcontatipo,c.cor,c.somarelatorio,c.previsao  ,n.status,'C' as tipo,c.faturamento
			,c.ordem,  
			IFNULL(`ps`.`descr`, `i`.`descricao`) AS `descricao`,
			`n`.`idnotafiscal` AS `idnf`,
			`n`.`emissao` AS `datareceb`,
			`n`.`idempresa` AS `idempresa`,
			`e`.`empresa` AS `empresa`,
			`e`.`corsistema` AS `corsistema`,
			`p`.`idtipoprodserv` AS `idtipoprodserv`,
			`i`.`idnotafiscalitens` AS `idnfitem`,
			`i`.`quantidade` AS `qtd`,
			'TESTE' AS `un`,
			round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(n.total/n.total),2) as total,
			`p`.`tipoprodserv` AS `tipoprodserv`,
			`n`.`nnfe` AS `nnfe`,
			`i`.`valor` AS `vlritem`,
			1 as idunidade,
			c.fluxocaixa,
			c.dre,
			n.emissao
		FROM notafiscal n 
			join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal  ) 
			join prodserv ps on(ps.idprodserv=i.idprodserv)
			join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
			join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
			join contaitem c on(c.idcontaitem=pc.idcontaitem   ".$strFluxoDre." )   
			JOIN `empresa` `e` ON (`e`.`idempresa` = `n`.`idempresa`)
		where n.geracontapagar = 'Y' 
			and n.status not in('CANCELADO','DEVOLVIDO','INUTILIZADO','RECUSADO','REPROVADO') 
			".$clausulaIdempresaNFS."        
			AND n.emissao BETWEEN   ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
			group by  i.idnotafiscal,i.idprodserv,i.valor,i.desconto
	union all -- credito
		SELECT 'SV' as tiponf,c.contaitem,c.idcontaitem,c.idcontatipo,c.cor,c.somarelatorio,c.previsao,n.status,'C' as tipo,c.faturamento
			,c.ordem,
			`i`.`descricao` AS `descricao`,
			`n`.`idnotafiscal` AS `idnf`,
			`n`.`emissao` AS `datareceb`,
			`n`.`idempresa` AS `idempresa`,
			`e`.`empresa` AS `empresa`,
			`e`.`corsistema` AS `corsistema`,
			`p`.`idtipoprodserv` AS `idtipoprodserv`,
			`i`.`idnotafiscalitens` AS `idnfitem`,
			`i`.`quantidade` AS `qtd`,
			'TESTE' AS `un`,
			round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(n.total/n.total),2) as total,
			`p`.`tipoprodserv` AS `tipoprodserv`,
			`n`.`nnfe` AS `nnfe`,
			`i`.`valor` AS `vlritem`,
			1 as idunidade,
			c.fluxocaixa,
			c.dre,
			n.emissao
		FROM notafiscal n 
			join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal and i.idprodserv is null ) 
			join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
			join contaitem c on(c.idcontaitem=i.idcontaitem   ".$strFluxoDre." )   
			JOIN `empresa` `e` ON (`e`.`idempresa` = `n`.`idempresa`)
		where n.geracontapagar = 'Y' 
			and n.status not in('CANCELADO','DEVOLVIDO','INUTILIZADO','RECUSADO','REPROVADO') 
			".$clausulaIdempresaNFS."                                                          
			AND n.emissao BETWEEN  ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
		group by  i.idnotafiscal,i.idtipoprodserv,i.valor,i.desconto 
		".$vwcontaitemtp;
}

$_sqlresultado = "select 
	`a`.`tiponf` AS `tiponf`,
	`a`.`idcontaitem` AS `idcontaitem`,
	`a`.`contaitem` AS `contaitem`,
	`a`.`idtipoprodserv` AS `idtipoprodserv`,
	`a`.`tipoprodserv` AS `tipoprodserv`,
	concat('<a href=\"?_modulo=',			
	CASE
	WHEN a.tiponf in ('R')  THEN 'comprasrh'
    WHEN a.tiponf in ('V')  THEN 'pedido'
	WHEN a.tiponf in ('D')  THEN 'comprassocios'
	WHEN a.tiponf in ('SV')  THEN 'nfs'
	WHEN a.tiponf in ('PREV')  THEN 'contapagar'
	ELSE 'nfentrada'  
	END,'&_acao=u&_idempresa=',a.idempresa,'&',
	CASE
	WHEN a.tiponf in ('SV')  THEN 'idnotafiscal'
	WHEN a.tiponf in ('PREV')  THEN 'idcontapagar'
	ELSE 'idnf'  
	END
	,'=',a.idnf,'\" target=\"_blank\">',case when a.nnfe='' then a.idnf when a.nnfe is null then a.idnf else a.nnfe end,'</a>') as linknf,
	`a`.`cor` AS `cor`,
	`g`.`cor` AS `corsistema`,
	ifnull(`pe`.`previsao`,'0.00') AS `previsao`,
	`a`.`status` AS `status`,
	`a`.`tipo` AS `tipo`,
	`a`.`faturamento` AS `faturamento`,
	`g`.`ordem` AS `ordem`,
		a.descricao AS descr,
	`a`.`idnf` AS `idnf`,
	`a`.`datareceb` AS `datareceb`,
	 a.emissao,
	`a`.`idempresa` AS `idempresa`,
		`a`.`idnfitem` AS `idnfitem`,
	`a`.`qtd` AS `qtd`,
	`a`.`un` AS `un`,
	`a`.`total` AS `total`,
	`a`.`total` AS `valor`,
	`a`.`nnfe` AS `nnfe`,
	`a`.`vlritem` AS `vlritem`,
	t.idcontatipo,
	t.contatipo,
	g.idcontatipogrupo,
	g.grupo,
	a.idempresa as idempresarateio,
	e.empresa,
	e.empresa as empresarateio,
	g.tipo as tipogrupo,
	g.percentual,
	(select group_concat(o.idobjetovinc) as idsgrupo from objetovinculo o where  o.idobjeto = g.idcontatipogrupo
	and o.tipoobjeto ='contatipogrupo' 
	and o.tipoobjetovinc='contatipogrupo') as idsgrupo,
	ifnull(p3.previsao,'0.00') as contaitemprev,
    ifnull(p2.previsao,'0.00') as contatipoprev,
    ifnull(p1.previsao,'0.00') as contatipogrupoprev
	from
		contatipogrupo g
		".$leftcontatipo." join contatipo t on g.idcontatipogrupo = t.idcontatipogrupo  and t.status='ATIVO' ".$clausulaidcontatipo."
		left join (".$vw8despesas.") a on(t.idcontatipo = a.idcontatipo ".$clausorg2.")
		left join empresa e on(e.idempresa=a.idempresa)
		left join (select sum(previsao) as previsao, idtipoprodserv from vwtipoprodservprevisao pe1 where pe1.idempresaprev in ( ".$_idempresaMatriz.")  and pe1.exercicio in(".$strano.") and pe1.mes in(".$strmes.")  group by pe1.idtipoprodserv ) pe on(pe.idtipoprodserv=a.idtipoprodserv)
		left join (select sum(previsao) as previsao, idcontaitem from vwcontaitemprevisao pe3 where pe3.idempresaprev in ( ".$_idempresaMatriz.")  and  pe3.exercicio in(".$strano.") and  pe3.mes in(".$strmes.")  group by pe3.idcontaitem) p3 on(p3.idcontaitem=a.idcontaitem)
		left join (select sum(previsao) as previsao, idcontatipo from vwcontatipoprevisao pe2 where pe2.idempresaprev in ( ".$_idempresaMatriz.")  and pe2.exercicio in(".$strano.") and pe2.mes in(".$strmes.")  group by pe2.idcontatipo) p2 on(p2.idcontatipo=t.idcontatipo)
		left join (select sum(previsao) as previsao, idcontatipogrupo from vwcontatipogrupoprevisao pe1 where pe1.idempresaprev in ( ".$_idempresaMatriz.")  and pe1.exercicio in(".$strano.") and pe1.mes in(".$strmes.")  group by pe1.idcontatipogrupo) p1 on(p1.idcontatipogrupo=g.idcontatipogrupo)

	where 
	 (EXISTS( SELECT 
                    1
                FROM
                    `contapagaritem` `i`
                WHERE
                    ((`i`.`idobjetoorigem` = `a`.`idnf`)
                        AND (`i`.`tipoobjetoorigem` = (CASE
                        WHEN (`a`.`tiponf` = 'SV') THEN 'notafiscal'
                        ELSE 'nf'
                    END))
                        AND (`i`.`status` <> 'INATIVO')))
                OR EXISTS( SELECT 
                    1
                FROM
                    `contapagar` `i`
                WHERE
                    ((`i`.`idobjeto` = `a`.`idnf`)
                        AND (`i`.`tipoobjeto` = (CASE
                        WHEN (`a`.`tiponf` = 'SV') THEN 'notafiscal'
                        ELSE 'nf'
                    END))
                        AND (`i`.`status` <> 'INATIVO'))) or g.tipo ='N' )
	and g.status='ATIVO' and g.idempresa in ( ".$_idempresaMatriz.") ".$clausulaidcontatipogrupo."
	order by g.ordem,grupo,t.ordem,contatipo,a.ordem,contaitem, tipoprodserv,idtipoprodserv,descr";


$_sqlresultado =$strselectfields." from (".$_sqlresultado." ) bb where 1 ".$_sqlresultadounidade." ";
echo("<!-- rateio ".$_sqlresultado." -->");
	//echo "<!-- ".array_key_exists("STRCONTATOCLIENTE", $_SESSION["SESSAO"])." -->";	//echo $_sqlresultado;


	//Abre variavel de sessao para que ela possa ser acessada pelo modulo de interceptacao de eventos
	$_SESSION["SEARCH"]["SQL"] = $_sqlresultado;

	//echo "<!-- ".$_sqlresultado." -->";	

	$_resultados = d::b()->query($_sqlresultado);
	if (!$_resultados) {
	    die('<b>Falha na execucao da Consulta para o Report:</b> ' . mysql_error() . "<br>" . $_sqlresultado);
	}

	$_arrtab = retarraytabdef($_tab);

	$_i = 0;
    //var_dump($arrRep["_filtros"][$arrRep["_colvisiveis"]]);
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


	// GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
	$sqlfig="select logosis from empresa where idempresa =".cb::idempresa();
	$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	$figrel=mysqli_fetch_assoc($resfig);

	$figurarelatorio = $figrel["logosis"];
	
?>
	<div style="display:none; text-align: left; font-size: 9px;" class="n_linhas"><span  id="nlinha"><?=$strs?></span></div>

	<table class="tbrepheader">
	<tr>
		<td rowspan="3" style="width:50px;"><img style="width:100%;" src="<?=$figurarelatorio?>"></td>
		<td class="header"><? //=$_header?></td>
		<td><!--a class="btbr20 no-print" href="<?=$_SERVER['REQUEST_URI']?>&reportexport=csv" target="_blank">Download .csv</a-->
		<a onclick="gerarCsvPrint('Fluxo de Caixa - DRE')" class="btbr20 no-print" id="btn_export" href="#">  Download .CSV</a>
		</td>
		
	</tr>
	<tr>
		<td class="subheader"><h2><?=($_rep);?></h2>
		(<?=$strs?>)</td>
	</tr>
	</table>
	<br>
	<div class="normal" style="
    BACKGROUND: #aaa;
    margin: 20px 0px;
    padding: 20px; font-size:9px
">DESPESAS POR <?=$tipo;?> <?=$_REQUEST['tiporelatorio']?></div>
<?
	/*
	 * MONTA O CABECALHO
	 */
	$conteudoexport;// guarda o conteudo para exportar para csv
	$strtabheader = "\n<thead><tr class='header res'>";
	//coloca um contador numerico do lado esquerdo da tabela, isto é repetido também na montagem de cada linha da tabela
	if($_showtotalcounter == "Y"){
		$strtabheader .= "<td class='tdcounter'></td>";
	}
	//var_dump($arrRep["_filtros"][$arrRep["_colvisiveis"]]);
	//inicial
	$_i = 8;
	$strtabheader .= "<td class='sub' style='width: 1% !important;'></td>";
	while ($_i < $_numcolunas) {
	    $_metacmp = mysql_fetch_field($_resultados, $_i);
	    if (!$_metacmp) {
	        die("Nenhuma informacao de design retornou do SQL de Resultados");
	    }
		
	    $_arridxcol[$_i] = $_metacmp->name;
	    
		if($arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["visres"] == 'Y'){

	    	//A VIRGULA E SO ENTRE OS VALORES NO INICIO DA LINHA E NO FINAL NÃO TEM VIRGULA
	    	if(!empty($conteudoexport)){
	    		$conteudoexport.=";";
	    	}
			if (strpos(strtolower($arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["rotulo"]), ' as ') !== false) {
				$val = explode(' as ',strtolower($arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["rotulo"]));
				$arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["rotulo"] = $val[1];
			}
	    	
			$strtabheader .= "<td class='header' id='".seo_friendly_url(str_replace('`','',$arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["rotulo"]))."' style=\"white-space: nowrap; text-align:".$arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["align"]."\">" . str_replace('`','',$arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["rotulo"])."<br>&nbsp;<i class='fa fa-arrow-down pointer' title='Ordenar Crescente' style='font-size: 0.8em; opacity: 0;' attr='desc'></i>&nbsp;<i class='fa fa-arrow-up pointer' title='Ordenar Decrescente' style='font-size: 0.8em; opacity: 0;' attr='asc'></i></td>";
	    }	
		if($_REQUEST['layout']!='SEMITEM'){
			if(!empty($arrRep["_filtros"][$_metacmp->name]["rotulo"])){
				$conteudoexport.= "\"".$arrRep["_filtros"][$_metacmp->name]["rotulo"]."\"";// GRAVA O ROTULO DOS CABEÇALHOS NA VARIAVEL PARA GERAR O CSV
			}else{
				$conteudoexport.= "\"".str_replace('`','',$arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["rotulo"])."\"";// GRAVA O ROTULO DOS CABEÇALHOS NA VARIAVEL PARA GERAR O CSV
			}
		}
	    $_i++;
	}
	$conteudoexport='"";"NFE";"QTD";"UN";"PRODUTO";"VALOR ITEM";"RECEBIMENTO";"VALOR"';// substitui por valores padrão
	$conteudoexport.="\n";//QUEBRA DE LINHA NO CONTEUDO CSV
	$strtabheader .= "</tr></thead><tbody>";

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
	$_ilinha = 0;//armazena o ttotal de registros
	$_ilinhaquebra = 0;//armazena parialmente o numero de registros se houver quebra automatica configurada
	$_graphLinha = 0;
	$strnewpage = "<span class='newreppage'></span>";



	$empresarateio = '';
	$empresa = '';
	$tipoprodserv = '';
	$contaitem = '';
	$virgula = '';
    while ($_row = mysql_fetch_array($_resultados)){
		if(empty($idcontatipogrupo_primeiro) and $_row[22]=='Y'){
			$idcontatipogrupo_primeiro = $_row[6];// primeira linha e o da receita bruta
			$grupobasecalculo='sobre '.$_row[7];
		}
		$v_idrateioitemdest .= $virgula.$_row['idrateioitemdest'];
		$virgula = ',';
	/*	if (!empty($c1)){
			 $_row[4] = $_row[$c1];
			 $_row[5] = $_row[$c2];

		}else{
			$_row[4] = $_row['idempresa'];
			$_row[5] = $_row['empresa'];
		}
*/


	$_ilinha++;
	$_i = 0;

	//verifica se o parametro de quebra automatica esta configurado. caso negativo escreve o cabecalho somente 1 vez. E tambem se for a primeira linha, desenha o cabecalho pelo 'else'
	if($_pbauto>0 and $_ilinha>1){
		//verifica quando é que uma nova quebra sera colocada
		if($_pbauto>($_ilinhaquebra+1)){
			$_ilinhaquebra++;
		}else{
			echo "\n</table>";
			echo $strnewpage;//QUEBRA A PAGINA
			$_ilinhaquebra=0;


		}
	}else{
		//Escreve o cabecalho somente uma vez
		if($_ilinha==1){
		}
	}

	
	###################################### Escreve linhas da <Table>
	
	//var_dump($_row);

	$valor_empresarateio[$_row[6]] += $_row[14];
	$valor_empresa[$_row[6]][$_row[4]] += $_row[14];
	$valor_contaitem[$_row[6]][$_row[4]][$_row[0]] += $_row[14];
	$valor_tipoprodserv[$_row[6]][$_row[4]][$_row[0]][$_row[2]] += $_row[14];
	$v_total += $_row[14];


	if ($empresarateio <>  $_row[6] || $empresarateio == ''){
		


		if ($empresarateio == ''){
			echo "\n</table>";
		}

		if ($empresarateio <>  $_row[6] && $empresarateio <> ''){
				//echo '<span id="sim">queeee'.$_row[7].'__'.$_row[14].'</span>';
				echo "</table></td></tr></table></td></tr></table></td></tr></table></td></tr>";
		}
		$empresa = '';
		$tipoprodserv = '';
		$contaitem = '';
		if($_row[16]=='I'){
			$strcollapse="<i style='float:right' class='fa fa-arrows-v fa-2x cinzaescuro pointer' title='Detalhar' data-toggle='collapse' href='#c_".$_row[6]."' aria-expanded='' ></i>";
			$strprevisao="<div title='Previsão' class='somatorio_previsao valor_previsao_".$_row[6]."'>R$ ".number_format(tratanumero($_row[21]), 2, ',', '.')."</div><div title='Delta' class='delta delta_".$_row[6]." hide'>R$ 0,00</div>";
			$strcolor="#ccc";
		} else if ($_ilinha == $_resultados->num_rows) {
			$strcollapse="";
			$valor_idsgrupo[$_row[6]]=$_row[17];
			$strprevisao="<div class='somatorio_previsao previsao_somatorio_final'></div>";
			$strcolor="#bbb";
		} else {
			$strcollapse="";
			$valor_idsgrupo[$_row[6]]=$_row[17];
			$strprevisao="<div></div>";
			$strcolor="#bbb";
		}
		
		echo $strtabini;
		echo "<tr class='res' style='height:40px; background: ".$strcolor.";border-left: 4px solid ".$_row['corsistema']."'>
				<td style='width:60%;text-transform:uppercase;'>".$_row[7]."</td>
				<td style='width:30%'>
					<div title='Valor Total' class='somatorio_valor valor_empresarateio_".$_row[6]."'>R$ 0,00</div>      
					".$strprevisao." 
					              
					<!-- div title='Percentual Despesa' class='somatorio_percentual percentual_empresarateio_".$_row[6]."'>R$ 0,00</div -->
					<!-- div title='Percentual Faturamento' class='somatorio_percentual_faturamento percentualfaturamento_empresarateio_".$_row[6]."'>R$ 0,00</div -->
				</td>
				<td style='width:10%'>
				<div style='display: contents;' title='% ".$grupobasecalculo."' class='preceitabruta preceitabruta_".$_row[6]."'></div>
				".$strcollapse."
				</td>
			</tr>
			<tr class=''>
				<td colspan='12' class='sub' style='padding: 0px 0px;border:none !important'>
					<table class='nivel0 normal collapse _sqlresultado' id='c_".$_row[6]."' style='margin:1px;'> ";
			$empresarateio =  $_row[6] ;
			//echo $strtabheader;
		}
		
//var_dump($_row);



	if ($empresa <>  $_row[4] || $empresa == ''){
		
		if ($empresa == ''){
		//	echo "\n</table>";
		}

		if ($empresa <>  $_row[4] && $empresa <> ''){
				//echo '<span id="sim">queeee'.$_row[7].'__'.$_row[14].'</span>';
				echo "</table></td></tr></table></td></tr></table></td></tr>";
		}
		$tipoprodserv = '';
		$contaitem = '';
		

		echo "<tr class='res' style='height:40px; background: #cccccc8c;'>
				<td class='sub' style='width: 2% !important;'></td>
				<td style='width:58%'>".$_row[5]."</td>
				<td style='width:30%'>
				<div title='Valor Total' class='somatorio_valor valor_empresa_".$_row[6]."_".$_row[4]."'>R$ 0,00</div> 
				<div title='Previsão' class='somatorio_previsao valor_previsao_".$_row[6]."_".$_row[4]."'>R$ ".number_format(tratanumero($_row[20]), 2, ',', '.')."</div>   
				<div title='Delta' class='delta delta_".$_row[6]."_".$_row[4]." hide'>R$ 0,00</div>                      
				<!-- div title='Percentual Despesa' class='somatorio_percentual percentual_empresa_".$_row[6]."_".$_row[4]."'>R$ 0,00</div -->
				<!-- div title='Percentual Faturamento' class='somatorio_percentual_faturamento percentualfaturamento_empresa_".$_row[6]."_".$_row[4]."'>R$ 0,00</div -->
				</td>
				<td style='width:10%'><i style='float:right' class='fa fa-arrows-v fa-2x cinzaescuro pointer' title='Detalhar' data-toggle='collapse' href='#c_".$_row[6]."_".$_row[4]."' aria-expanded='' ></i></td>
			</tr>
			<tr class=''>
				<td colspan='12' class='sub' style='padding: 2px 0px;border:none !important'>
					<table class='nivel1 normal collapse' id='c_".$_row[6]."_".$_row[4]."'> ";

			$empresa =  $_row[4] ;
			//echo $strtabheader;
		}
		



		if ($contaitem <>  $_row[0] || $contaitem == ''){
		
		if ($contaitem == ''){
		//	echo "\n</table>";
		}

		if ($contaitem <>  $_row[0] && $contaitem <> ''){
				//echo '<span id="sim">olhaaaa'.$_row[7].'__'.$_row[14].'</span>';
				echo "</table></td></tr></table></td></tr>";
		}
		$tipoprodserv = '';
		
		//echo $strtabini;
		echo "<tr class='res' style='height:40px; background: #dddddd78;'>
				<td class='sub' style='width: 4% !important;'></td>
				<td style='width:56%'>".$_row[1]."</td>
				<td style='width:30%'>
					<div title='Valor Total' class='somatorio_valor valor_contaitem_".$_row[6]."_".$_row[4]."_".$_row[0]."'>R$ 0,00</div>      
					<div title='Previsão' class='somatorio_previsao valor_previsao_".$_row[6]."_".$_row[4]."_".$_row[0]."'>R$ ".number_format(tratanumero($_row[19]), 2, ',', '.')."</div>  
					<div title='Delta' class='delta delta_".$_row[6]."_".$_row[4]."_".$_row[0]." hide'>R$ 0,00</div>                    
					<!-- div title='Percentual Despesa' class='somatorio_percentual percentual_contaitem_".$_row[6]."_".$_row[4]."_".$_row[0]."'>R$ 0,00</div -->
					<!-- div title='Percentual Faturamento' class='somatorio_percentual_faturamento percentualfaturamento_contaitem_".$_row[6]."_".$_row[4]."_".$_row[0]."'>R$ 0,00</div -->
				</td>
				<td style='width:10%'><i style='float:right' class='fa fa-arrows-v fa-2x cinzaescuro pointer' title='Detalhar' data-toggle='collapse' href='#c_".$_row[6]."_".$_row[4]."_".$_row[0]."' aria-expanded='' ></i></td>
			</tr>
			<tr class=''>
				<td colspan='12' class='sub' style='padding: 2px 0px;border:none !important'>
					<table class='nivel2 normal collapse' id='c_".$_row[6]."_".$_row[4]."_".$_row[0]."'> ";
			$contaitem =  $_row[0] ;
			//echo $strtabheader;
		}
		
		//$v_total += $_row[11];




		if ($tipoprodserv <>  $_row[2] || $tipoprodserv == ''){
			//echo '<span id="sim">simmm '.$tipoprodserv.'</span>';
			if ($tipoprodserv == ''){
			//	echo "\n</table>";
			}
		
			if ($tipoprodserv <>  $_row[2] && $tipoprodserv <> ''){
			//	echo '<span id="sim">olhaaaa'.$_row[7].'__'.$_row[14].'</span>';
				echo "</table></td></tr>";
			}
			if($_REQUEST['layout']!='SEMITEM'){
				$collapse_tipo="<i style='float:right' class='fa fa-arrows-v fa-2x cinzaescuro pointer' title='Detalhar' data-toggle='collapse' href='#c_".$_row[6]."_".$_row[4]."_".$_row[0]."_".$_row[2]."' aria-expanded='' ></i>";
			}else{
				$collapse_tipo="";
			}
			//echo $strtabini;
			echo "	<tr class='res' style='height:40px; background: #eeeeee70;'>
						<td class='sub' style='width: 6% !important;'></td>
						<td style='width:54%'>".$_row[3]."</td>
						<td style='width:30%'>	
							<div title='Valor Total' class='somatorio_valor valor_tipoprodserv_".$_row[6]."_".$_row[4]."_".$_row[0]."_".$_row[2]."'>R$ 0,00</div>  
							<div title='Previsão' class='somatorio_previsao valor_previsao_".$_row[6]."_".$_row[4]."_".$_row[0]."_".$_row[2]."'>R$ ".number_format(tratanumero($_row[18]), 2, ',', '.')."</div>   
							<div title='Delta' class='delta delta_".$_row[6]."_".$_row[4]."_".$_row[0]."_".$_row[2]." hide'>R$ 0,00</div>                
							<!-- div title='Percentual Despesa'  class='somatorio_percentual percentual_tipoprodserv_".$_row[6]."_".$_row[4]."_".$_row[0]."_".$_row[2]."'>R$ 0,00</div -->
							<!-- div title='Percentual Faturamento' class='somatorio_percentual_faturamento percentualfaturamento_tipoprodserv_".$_row[6]."_".$_row[4]."_".$_row[0]."_".$_row[2]."'>R$ 0,00</div -->
						</td>
						<td style='width:10%'>".$collapse_tipo."</td>
					</tr>
					<tr class=''>
						<td colspan='12' class='sub' style='border:none !important; width:8%; padding:2px 0px'>
							<table class='nivel4 ".$tipoprodserv."_".$_row[2]." normal collapse' id='c_".$_row[6]."_".$_row[4]."_".$_row[0]."_".$_row[2]."'>";
				$tipoprodserv =  $_row[2] ;
				if($_REQUEST['layout']!='SEMITEM'){
					echo $strtabheader;
				}
				
		}
		
			


		
		//inicial
		$_i = 8;
		if($_REQUEST['layout']!='SEMITEM'){

			echo "\n<tr class=\"res\" ". $_link ." ". $_strhlcolor ." ". $tipoprodserv."_".$_row[2].">";
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

			echo "<td class='sub' style='width: 8% !important;'></td>";
			$primeirocampo='';
			$pc='N';
			while ($_i < $_numcolunas) {

				$_attrHtml="";
				$_stralign="";
				$_strvlrhtml="";
				$_nomecol = $_arridxcol[$_i];
				$_nomecol = $arrRep["_colvisiveis"][$_i+1];
				$_colorlink="";
				$_hyperlink="";
				$_corfont= "";
				$_corfontfim="";
				//Escreve Campo
				if($arrRep["_filtros"][$_nomecol]["visres"] == 'Y'){

					if($pc=='N'){
						$pc='Y';
						$primeirocampo= strip_tags($_row[$_i]);
					}
		
					//ajusta o alinhamento dentro da celula. caso esquerda. nao preencher para nao gerar html desnecessariamente
					if($arrRep["_filtros"][$_nomecol]["align"]!="left"){
						$_stralign = "align='".$arrRep["_filtros"][$_nomecol]["align"]."'";
					}
					
					//se for para somar o valor do campo
					if($arrRep["_filtros"][$_nomecol]["acsum"]=='Y'){

						//Cria classe de somatoria para fazer a soma com JS no modulo menurelatorio
						$_attrHtml = "acsum='$_nomecol' filtervalue='$_row[$_i]'";

						$_arrsoma[$_tab][$_nomecol] = $_arrsoma[$_tab][$_nomecol] + $_row[$_i];
					}
					
					//se for para somar o valor do campo
					if($arrRep["_filtros"][$_nomecol]["acavg"]=='Y'){
						
						$_arrsomaavg[$_tab][$_nomecol] = $_arrsomaavg[$_tab][$_nomecol] + $_row[$_i];
						
					}
					

					/*
					* Trata colunas inseridas manualmente para que tenham um datatype
					*/
					if(empty($arrRep["_filtros"][$_nomecol]["datatype"])){
						$t = preg_replace("/[^0-9.]/", "",$_row[$_i]);
						($t != $_row[$_i]) ? $arrRep["_filtros"][$_nomecol]["datatype"]="varchar" : $arrRep["_filtros"][$_nomecol]["datatype"]="double";
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
					}elseif($arrRep["_filtros"][$_nomecol]["datatype"]=='decimal' || $arrRep["_filtros"][$_nomecol]["datatype"]=='double'){
						$graficoY=$_row[$_i];
						$_strvlrhtml = number_format($_row[$_i], 2, ',','.');
					}else{
						$_strvlrhtml = $_row[$_i];
					}

					$_attrHtml .= "datatype='".$arrRep["_filtros"][$_nomecol]["datatype"]."' mascara='".$arrRep["_filtros"][$_nomecol]["mascara"]."'  eixografico='".$arrRep["_filtros"][$_nomecol]["eixograph"]."' col='".$_nomecol."'  ";

					$_strvlrhtml=aplicaMascara($arrRep["_filtros"][$_nomecol]["mascara"], $_strvlrhtml);					
					


					$arrayGrafico[$_graphLinha][$_nomecol] = $_row[$_i];
					
					if (is_numeric($_row[$_i])){
						$total[$_i] = $total[$_i] + $_row[$_i];
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

					//Verifica se Possui Máscara de Moeda antes de Jogar no csv.
					if($arrRep["_filtros"][$_nomecol]["mascara"] == 'MOEDA' || $arrRep["_filtros"][$_nomecol]["datatype"]=='decimal' || $arrRep["_filtros"][$_nomecol]["datatype"]=='double' || $arrRep["_filtros"][$_nomecol]["datatype"]=='int' ){
						$conteudoexport.="\"".strip_tags(number_format($_row[$_i], 2, ',','.'))."\"";
					} else {
						$conteudoexport.="\"".strip_tags($_strvlrhtml)."\"";//GRAVA O VALOR DO CAMPO PARA GERAR O CSV ENTRE ASPAS
				}

					//Se o hyperlink não estiver vazio ele monta o link
					if(!empty($arrRep["_filtros"][$_nomecol]["hyperlink"])){
						if ($_strvlrhtml != '0.00'){
							//O HREF contém uma barra ('/') o que significa que é uma URL relativa, que vai desconsiderar qualquer pasta informada neste link
							if (strpos($arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$_i] , 'pk=')){
								
								$arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$_i];
								$valor = explode('pk=',$arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$_i]);
								$valor = explode('&',$valor[1]);
								$campo = $_row[$valor[0]];
								
								$_hyperlink="<a target=_blank href='/".$arrRep["_filtros"][$_nomecol]["hyperlink"].$campo."'>".$_strvlrhtml."</a>";
							}else{
								$_hyperlink="<a target=_blank href='/".$arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$_i]."'>".$_strvlrhtml."</a>";
							}
							
							$_colorlink="class=\"link\" ";
							$_corfont= "<font color='Blue'>";
							$_corfontfim="</font>";
						}else{
							$_hyperlink = strip_tags($_strvlrhtml);
						}
											
					}
					
					//Finalmente: desenha o campo na tela
					if(!empty($primeirocampo)){
					
						if(!empty($arrRep["_filtros"][$_nomecol]["hyperlink"])){
							echo "<td a ".$_attrHtml." ".$_stralign." >".$_hyperlink."</td>";
						}else{
							echo "<td b ".$_attrHtml." ".$_stralign." ".$_hyperlink." ".$_colorlink.">".$_corfont.$_strvlrhtml.$_corfontfim."</td>";
						}
					}
				}
				$_i++;
			}
			$conteudoexport.="\n";//QUEBRA A LINHA DO CONTEUDO CSV
		}//if($_REQUEST['layout']!='SEMITEM'){
		$_graphLinha++;
    }


	
$j = 0;

$empresarateio = '';
$empresa = '';
$tipoprodserv = '';
$contaitem = '';


?>
 
	
	<!-- Armazena o titulo da consulta para ser usada como titulo do arquivo csv no modulo menurelatorio -->
	<div  id="tlt" style="display: none;"><?=$_rep.' '.$_GET["_fds"]?></div>
	
<?

	/*
	 * Desenha a legenda
	 */

}



	//echo '<pre>'.$_sqlresultado.'</pre>';
	$data1 = explode('/',$data1);
	$data1 = $data1[2].'-'.$data1[1].'-'.$data1[0];

	$data2 = explode(' ',$data2);
	$data2 = explode('/',$data2[0]);
	$data2 = $data2[2].'-'.$data2[1].'-'.$data2[0].' 23:59:59';


	if ($_REQUEST['idempresa'] != ''){
		$sqlc = "and idempresa in (".$_REQUEST['idempresa'].")";
		$sqlc2 = "and `cp`.`idempresa` in (".$_REQUEST['idempresa'].")";
	}

	$sqlFat = "
		select sum(total) as totalnf from
		vw8pedido where 
		dtemissao between '".date('Y-m-d', strtotime($data1))."' and '".date('Y-m-d H:i:s', strtotime($data2))."'
		".$sqlc."
	    and status in ('ENVIAR','ENVIADO','TRANSFERIDO','CONCLUIDO')
		and natoptipo = 'venda'
        and vlritem > 0
		and tiponf = 'V' ";
        
        echo "<!-- sqlFat ".$sqlFat." -->";
        // comentado 20/12/2022 pois estava lento no sistema
/*	$resFat =  d::b()->query($sqlFat) or die("Falha ao pesquisar dados de Faturamento (P): " .mysqli_error(d::b()). "<p>SQL: $sqlFat");

	//	$rowFat=mysqli_fetch_assoc($resFat);

	//	$v_prod_totalnf = $rowFat['totalnf'];

	
		$sqlFatS="select  sum(total) as total from vwnf where 
			emissao between '".date('Y-m-d', strtotime($data1))."' and '".date('Y-m-d H:i:s', strtotime($data2))."'
			".$sqlc."
			and status in ('FATURADO','CONCLUIDO')";
			$resFatS =  d::b()->query($sqlFatS) or die("Falha ao pesquisar dados de Faturamento (S): " .mysqli_error(d::b()). "<p>SQL: $sqlFatS");

			$rowFatS=mysqli_fetch_assoc($resFatS);
					
		$v_serv_totalnf = $rowFatS['total'];

		$v_total_totalnf = $v_prod_totalnf+$v_serv_totalnf; */

	?>
</table></td></tr></table></td></tr></table></td></tr></table></td></tr></table></td></tr></table>


<? /*
<div class="normal" style="background:#fff;border:none;height:40px" class="inv">&nbsp;</div>

<table class="normal">
<tr style="border: none !important;background: #aaa;height:40px;">
	<td style='width:70%'>FATURAMENTO <small>empresa(s)</small></td>
	<td colspan="7" style='width:25%'>
		<div title='Valor Total' class='somatorio_valor'>R$ <?=number_format(tratanumero($v_total_totalnf), 2, ',', '.');?></div>                    
		<div title='Percentual Despesa' class='hide somatorio_percentual ' style="background:#bbb"></div>
		<div title='Percentual Faturamento' class='hide somatorio_percentual_faturamento'><?//=number_format(tratanumero((($v_total+$v_total_diff)*100)/$v_total_totalnf), 2, ',', '.')?></div>
	</td>	

	<td style='width:5%'></td>
</tr>
</table>
 
*/?>
<!-- Armazena o titulo da consulta para ser usada como titulo do arquivo csv no modulo menurelatorio -->
<div  id="tlt" style="display: none;"><?=$_rep.' '.$_GET["_fds"]?></div>
	
	
	<?
if(defined("_RODAPEDIR")) $varfooter= _RODAPEDIR;
/*?>
    <footer>
     
</footer>
	<fieldset class="fldsfooter">
	<legend>Fim da Impressão <?=$_nomeimpressao . " ".$varfooter?></legend>
	</fieldset>
	 */?>
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

//print_r($valor_idsgrupo); echo('<br>');

foreach($valor_idsgrupo as $keygrupo => $valuegrupo){
	$varidgrupo=explode(',',$valuegrupo);
	//echo('idgruposomar['.$keygrupo.']'); print_r($varidgrupo); echo('<br>');	
	foreach($varidgrupo as $idgrupo){
		//echo('idgrupo['.$idgrupo.']'); echo(' valor='.$valor_empresarateio[$idgrupo]); echo('<br>');
		$valor_empresarateio[$keygrupo]+=$valor_empresarateio[$idgrupo];
	}
}



//var_dump($valor_tipoprodserv);
foreach ($valor_empresarateio as $keyav => $valueav ){
	

	echo "<input type='hidden' class='normal indicador' id='valor_empresarateio_".$keyav."' value='R$ ".number_format(tratanumero((double)$valueav), 2, ',', '.')."'/>";
	$v_total_valor += $valueav;
	$v_total_valor_empr[$keyav] += $valueav;
	echo "<input type='hidden' class='normal indicador' id='percentual_empresarateio_".$keyav."' value='".number_format(tratanumero(($valueav*100)/($v_total+$v_total_dif)), 2, ',', '.')."%'/>";
	$v_total_percentual += number_format(tratanumero(($valueav*100)/($v_total+$v_total_dif)), 2, ',', '.');
	echo "<input type='hidden' class='normal indicador' id='percentualfaturamento_empresarateio_".$keyav."' value='".number_format(tratanumero(($valueav*100)/$v_total_totalnf), 2, ',', '.')."%'/>";
	$v_total_percentualfaturamento += number_format(tratanumero(($valueav*100)/$v_total_totalnf));
		
	foreach ($valor_empresa[$keyav] as $keya => $valuea ){
		echo "<input type='hidden' class='normal indicador' id='valor_empresa_".$keyav."_".$keya."' value='R$ ".number_format(tratanumero((double)$valuea), 2, ',', '.')."'/>";

		echo "<input type='hidden' class='normal indicador' id='percentual_empresa_".$keyav."_".$keya."' value='".number_format(tratanumero(($valuea*100)/($v_total+$v_total_dif)), 2, ',', '.')."%'/>";

		echo "<input type='hidden' class='normal indicador' id='percentualfaturamento_empresa_".$keyav."_".$keya."' value='".number_format(tratanumero(($valuea*100)/$v_total_totalnf), 2, ',', '.')."%'/>";
	

		foreach ($valor_contaitem[$keyav][$keya] as $key => $value ){
			echo "<input type='hidden' class='normal indicador' id='valor_contaitem_".$keyav."_".$keya."_".$key."' value='R$ ".number_format(tratanumero((double)$value), 2, ',', '.')."'/>";

			echo "<input type='hidden' class='normal indicador' id='percentual_contaitem_".$keyav."_".$keya."_".$key."' value='".number_format(tratanumero(($value*100)/($v_total+$v_total_dif)), 2, ',', '.')."%'/>";
		
			echo "<input type='hidden' class='normal indicador' id='percentualfaturamento_contaitem_".$keya."_".$key."' value='".number_format(tratanumero(($value*100)/$v_total_totalnf), 2, ',', '.')."%'/>";
		

			foreach ($valor_tipoprodserv[$keyav][$keya][$key] as $k => $v ){
				echo "<input type='hidden' class='normal indicador' id='valor_tipoprodserv_".$keyav."_".$keya."_".$key."_".$k."' value='R$ ".number_format(tratanumero((double)$v), 2, ',', '.')."'/>";
		
				echo "<input type='hidden' class='normal indicador' id='percentual_tipoprodserv_".$keyav."_".$keya."_".$key."_".$k."' value='".number_format(tratanumero(($v*100)/($v_total+$v_total_dif)), 2, ',', '.')."%'/>";
			
				echo "<input type='hidden' class='normal indicador' id='percentualfaturamento_tipoprodserv_".$keyav."_".$keya."_".$key."_".$k."' value='".number_format(tratanumero(($v*100)/$v_total_totalnf), 2, ',', '.')."%'/>";
			
			}
		}
	}
}


foreach ($valor_empresarateio_diff as $keyav => $valueav ){
	echo "<input type='hidden' class='normal indicador' id='valor_empresarateio_diff_".$keyav."' value='R$ ".number_format(tratanumero((double)$valueav), 2, ',', '.')."'/>";
	$v_total_valor_diff += $valueav;
	$v_total_valor_empr[$keyav] += $valueav;

	echo "<input type='hidden' class='normal indicador' id='percentual_empresarateio_diff_".$keyav."' value='".number_format(tratanumero(($valueav*100)/($v_total+$v_total_dif)), 2, ',', '.')."%'/>";
	$v_total_percentual_diff += number_format(tratanumero(($valueav*100)/($v_total+$v_total_dif)), 2, ',', '.');
	echo "<input type='hidden' class='normal indicador' id='percentualfaturamento_empresarateio_diff_".$keyav."' value='".number_format(tratanumero(($valueav*100)/$v_total_totalnf), 2, ',', '.')."%'/>";
	$v_total_percentualfaturamento += number_format(tratanumero(($valueav*100)/$v_total_totalnf));
		
	foreach ($valor_empresa_diff[$keyav] as $keya => $valuea ){
		echo "<input type='hidden' class='normal indicador' id='valor_empresa_diff_".$keyav."_".$keya."' value='R$ ".number_format(tratanumero((double)$valuea), 2, ',', '.')."'/>";

		echo "<input type='hidden' class='normal indicador' id='percentual_empresa_diff_".$keyav."_".$keya."' value='".number_format(tratanumero(($valuea*100)/($v_total+$v_total_dif)), 2, ',', '.')."%'/>";

		echo "<input type='hidden' class='normal indicador' id='percentualfaturamento_empresa_diff_".$keyav."_".$keya."' value='".number_format(tratanumero(($valuea*100)/$v_total_totalnf), 2, ',', '.')."%'/>";
	
		foreach ($valor_contaitem_diff[$keyav][$keya] as $key => $value ){
			echo "<input type='hidden' class='normal indicador' id='valor_contaitem_diff_".$keyav."_".$keya."_".$key."' value='R$ ".number_format(tratanumero((double)$value), 2, ',', '.')."'/>";

			echo "<input type='hidden' class='normal indicador' id='percentual_contaitem_diff_".$keyav."_".$keya."_".$key."' value='".number_format(tratanumero(($value*100)/($v_total+$v_total_dif)), 2, ',', '.')."%'/>";
			
			echo "<input type='hidden' class='normal indicador' id='percentualfaturamento_contaitem_diff_".$keya."_".$key."' value='".number_format(tratanumero(($value*100)/$v_total_totalnf), 2, ',', '.')."%'/>";
	

			foreach ($valor_tipoprodserv_diff[$keyav][$keya][$key] as $k => $v ){
				echo "<input type='hidden' class='normal indicador' id='valor_tipoprodserv_diff_".$keyav."_".$keya."_".$key."_".$k."' value='R$ ".number_format(tratanumero((double)$v), 2, ',', '.')."'/>";

				echo "<input type='hidden' class='normal indicador' id='percentual_tipoprodserv_diff_".$keyav."_".$keya."_".$key."_".$k."' value='".number_format(tratanumero(($v*100)/($v_total+$v_total_dif)), 2, ',', '.')."%'/>";
			
				echo "<input type='hidden' class='normal indicador' id='percentualfaturamento_tipoprodserv_diff_".$keyav."_".$keya."_".$key."_".$k."' value='".number_format(tratanumero(($v*100)/$v_total_totalnf), 2, ',', '.')."%'/>";
		
			}
		}
	}
}



echo "<input type='hidden' class='normal indicador' id='v_total_valor' value='R$ ".number_format(tratanumero((double)$v_total_valor), 2, ',', '.')."'/>";
echo "<input type='hidden' class='normal indicador' id='v_total_valor_diff' value='R$ ".number_format(tratanumero((double)$v_total_valor_diff), 2, ',', '.')."'/>";
echo "<input type='hidden' class='normal indicador' id='v_total_percentual' value='".number_format(tratanumero(($v_total_valor*100)/$v_total_totalnf), 2, ',', '.')."%'/>";
echo "<input type='hidden' class='normal indicador' id='v_total_percentualfaturamento' value='".number_format(tratanumero(($v_total_valor*100)/$v_total_totalnf), 2, ',', '.')."%'/>";


require_once 'graficos_relatorio.php';
?>



<script class="normal">



	function sortTable(e) {
		var th = e.target.parentElement;
		$(e.target).addClass("azul");
		$(th).addClass("ativo");
		$(e.target).siblings().removeClass("azul");
		$(th).siblings().removeClass("ativo");
		$(e.target.parentElement).siblings().each((e,o)=>{
			$(o).children().removeClass('azul').css('opacity','0')
		})
		var ordenacao = $(e.target).attr("attr");
		switch (ordenacao) {
			case 'asc':
				colunas = -1;
				break;
			case 'desc':
				colunas =  1 ;
				break;
		
			default:
			colunas =  1
				break;
		}

		var n = 0; while (th.parentNode.cells[n] != th) ++n;
		var order = th.order || 1;
		//th.order = -order;
		var t = this.closest("thead").nextElementSibling;
		var bottonLine=$(t.rows).filter('.bottonLine');

		t.innerHTML = Object.keys($(t.rows).not('.bottonLine'))
			.filter(k => !isNaN(k))
			.map(k => t.rows[k])
			.sort((a, b) => order * (isNaN(typed(a))&&isNaN(typed(b))) ? ((typed(a).localeCompare(typed(b)) > 0) ? colunas : -colunas):(typed(a) > typed(b) ? colunas : -colunas))
			.map(r => r.outerHTML)
			.join('')

		function typed(tr) {
			if(tr.cells[n]!= undefined){
			
				var s = tr.cells[n].innerText;
				var dataType = tr.cells[n].attributes.datatype.value;

				debugger
				if(dataType == 'varchar'){
					
					if(!s || /^\s*$/.test(s)){
						s = 'zzzzzzzzzzz';
					}

				} else if(dataType == 'decimal' || dataType == 'int' || dataType == 'double') {
					//trata números	

						s = s.replace('R$ ','')
						s = s.replaceAll('.','').replaceAll(',','.')
					

					if(!s || /^\s*$/.test(s)){
						s = '9999999999999';
					}

				}

				if (s.match(",")) {
					isNaN(s.replaceAll(",","."))?s = s.toString():s = s.replaceAll(",",".")
				}
				if (isNaN(s) && s.match(/^[a-zA-Z]+/)) {
					var d = s;
					var date = d;
				}else{
					if (s.match("/") && s.match(/^[a-zA-Z]+/) == null) {
						
						var d = mda(s);
						var date = Date.parse(d);
					}else{
						var d = s;
						var date = d;
					}

				}
				if (!isNaN(date)) {
					return isNaN(date) ? s.toLowerCase() : Number(date);
				}else{
					if (!isNaN(s.replaceAll(",",'.'))) {
						return  Number(s.replaceAll(",",'.'));
					}else{

						return s.toLowerCase();
					}
				}
			}
		}

		$('#restbl tbody').append(bottonLine);
	}


	$('#restbl thead td i').on('click', sortTable);                                

	$('#restbl thead td').mouseover(function(){
		$(this).children().not("[id=cbOrdCres], [id=cbOrdDecr]").each((e,o)=>{
		$(o).css("opacity","1").addClass('hoverazul')
		})
	});

	$('#restbl thead td').mouseout(function(){
		$(this).children().not("[id=cbOrdCres], [id=cbOrdDecr]").each((e,o)=>{
			if (!$(o).hasClass('azul')) {
				$(o).css("opacity","0").removeClass('hoverazul')
			}
		})
	});

	$('.indicador').each(function(index,item){
		//debugger;
		$('.'+$(item).attr('id')).html($(item).val());
	});

	const valoresSomarPrevisao = Array.from(document.querySelectorAll('#restbl')).map((node) => {
		return parseFloat(node.querySelector('tr').querySelector('td:nth-child(2)').querySelector('div:nth-child(2)').innerHTML.replaceAll('R$ ', '').replaceAll('.', '').replaceAll(',', '.')) || 0
	})

	const previsaoSomada = valoresSomarPrevisao.reduce((acc, valor) => {
		return acc += valor
	}, 0)

	document.querySelector('.previsao_somatorio_final').innerHTML = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(previsaoSomada)
	
    function gerarCsvPrint( tituloCsv ) {

		debugger;

// dowload CSV de TABELAS DIVS
/*
if($("div.table-striped").is(":visible")){
	var CsvContent = "";
	var virg = "";
	var hd="1";
	var value="";

	//monta o header do csv        
		$(`.table-striped`).find(".row").each((i, o) => {
			if(hd==1){
				if($(o).is(":visible")){

					$(o).find("div").each((j, k) => {
						value="";
						if($(k).text().includes("R$")){
						value = parseFloat($(k).text().replaceAll("R$ ","").replaceAll(".","").replace(",",".")).toFixed(2)
						}else{
							value = $(k).text().trim()
						}
						CsvContent += virg + value.trim();
						virg = ";";
					});
					CsvContent += "\n";
					virg = "";
				}
				hd++;
			}
		});
	

	// monta o tbody do csv
	$(`.table-striped .res `).find(".row").each((i, o) => {
		if($(o).is(":visible")){

			$(o).find("div").each((j, k) => {
				value="";
				if($(k).text().includes("R$")){
					value = parseFloat($(k).text().replaceAll("R$ ","").replaceAll(".","").replace(",",".")).toFixed(2)
				}else{
					value = $(k).text().trim()
				}
				CsvContent += virg + value;
				virg = ";";
			});
			CsvContent += "\n";
			virg = "";
		}
	});

	tituloCsv = tituloCsv.toLowerCase().replaceAll(/[^a-zA-Z0-9]/g,'');
	let hiddenElement = document.createElement('a');
	hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(CsvContent);
	hiddenElement.target = '_blank';
	hiddenElement.download = tituloCsv+'.csv';
	hiddenElement.click();
}

*/

// dowload CSV de TABELAS table
/*
if($("table.table-striped").is(":visible")){
	var CsvContent = "";
	var virg = "";

	$("table.table-striped").find("tr").each((i, o) => {
		if($(o).is(":visible")){

			$(o).find("td").each((j, k) => {

				if($(k).attr('colspan')){

					$colspan = $(k).attr('colspan') - 1;
					let ic = 1;

					while (ic <= $colspan) {
						ic++;
						CsvContent += virg +'';
						virg = ";";
					}

				}
				value="";
				if($(k).text().includes("R$")){
					value = parseFloat($(k).text().replaceAll("R$ ","").replaceAll(".","").replace(",",".")).toFixed(2)
				}else{
					value = $(k).text().trim()
				}
				CsvContent += virg + value;
				virg = ";";
			});
			CsvContent += "\n";
			virg = "";

		}
	});

	tituloCsv = tituloCsv.toLowerCase().replaceAll(/[^a-zA-Z0-9]/g,'');

	let hiddenElement = document.createElement('a');
	hiddenElement.href = 'data:text/csv;charset=utf-8,' + '\ufeff' + encodeURI(CsvContent);
	hiddenElement.target = '_blank';
	hiddenElement.download = tituloCsv+'.csv';
	hiddenElement.click();
}*/

if($(".normal").is(":visible")){
	var CsvContent = "";
	var virg = "";

	$(".normal").find("tr.res").each((i, o) => {
		if($(o).is(":visible")){

			$(o).find("td").each((j, k) => {

				if($(k).attr('colspan')){

					$colspan = $(k).attr('colspan') - 1;
					let ic = 1;

					while (ic <= $colspan) {
						ic++;
						CsvContent += virg +'';
						virg = ";";
					}

				}
				value="";
				if($(k).text().includes("R$")){

					value = parseFloat($(k).text().replace("R$ ","").replace(/\./g, "").replace(",", ".")).toFixed(2);
					value = parseFloat(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).replaceAll(".","");
					
				}else{
					value = $(k).text().trim();
				}
				CsvContent += virg + value;
				virg = ";";
			});
			CsvContent += "\n";
			virg = "";

		}
	});

	tituloCsv = tituloCsv.toLowerCase().replaceAll(/[^a-zA-Z0-9]/g,'');

	let hiddenElement = document.createElement('a');
	hiddenElement.href = 'data:text/csv;charset=utf-8,' + '\ufeff' + encodeURI(CsvContent);
	hiddenElement.target = '_blank';
	hiddenElement.download = tituloCsv+'.csv';
	hiddenElement.click();
}
}



// função para calcular o delta
function parseCurrency(value) {
    // Remove the 'R$', spaces, and thousands separators (dots)
    const cleanedValue = value.replace('R$', '').replace(/\./g, '').replace(',', '.');
    return parseFloat(cleanedValue);
}



// Select all elements with the class 'delta' (i.e., the "delta" elements)
const deltasElements = document.querySelectorAll('.delta');

deltasElements.forEach((delta) => {
    // Extract the class name suffix (e.g., 19) from the delta element
    const nome_classe = delta.classList[1];

    // Select the 'Previsão' element using its class name dynamically
    const previsao = document.querySelector(`.valor_previsao${nome_classe.replace('delta', '')}`);

    // Select one of the 'Valor' elements using the possible class names
    const valorElements = [
        document.querySelector(`.valor_empresarateio${nome_classe.replace('delta', '')}`),
        document.querySelector(`.valor_empresa${nome_classe.replace('delta', '')}`),
        document.querySelector(`.valor_contaitem${nome_classe.replace('delta', '')}`),
        document.querySelector(`.valor_tipoprodserv${nome_classe.replace('delta', '')}`)
    ];

    // Find the first non-null valor element
    let valor = valorElements.find(el => el !== null);

    if (previsao && valor) {
        // Parse the 'Previsão' value
        let previsaoValue = parseCurrency(previsao.innerText);
        if (!previsaoValue) { // Check if valorValue is 0, undefined, or empty
            delta.innerText ='';
        }else{
			 // Parse the 'Valor Total' value and handle 0, undefined, or empty values
			let valorValue = parseCurrency(valor.innerText);
			if (!valorValue) { // Check if valorValue is 0, undefined, or empty
				valorValue = 1;
			}

			// Calculate the percentage difference using the formula (valor ÷ previsao) - 1) × 100
			const percentageDifference = ((Math.abs(valorValue) / Math.abs(previsaoValue))  * 100);
			
			// Format the result in Brazilian currency format (R$ X.XXX.XXX,XX)
			delta.innerText = `${percentageDifference.toFixed(2)}%`;
		}


       
    }
});

var idcg_primeiro = <?=$idcontatipogrupo_primeiro?>;// guardar o idcontatipogrupo da primeira linha receita

const receita_bruta = parseFloat(document.querySelector(`.valor_empresarateio_${idcg_primeiro}`).innerHTML.replaceAll("R$ ", "").replaceAll(".", "").replaceAll(",", "."))

const elementos_somatria = document.querySelectorAll(".preceitabruta ")

for (elemento of elementos_somatria) {
    const id = elemento.classList[1].split("_")[1]
    if (id != idcg_primeiro) {
        const valor_comparacao = document.querySelector(`.valor_empresarateio_${id}`).innerHTML.replaceAll("R$ ", "").replaceAll(".", "").replaceAll(",", ".")
        const percentual = ((valor_comparacao) / receita_bruta) * 100
        elemento.innerHTML = `${percentual.toFixed(2)}%`
    }
}



// oculta o percentual da primeira linha RECEITA BRUTA
let tabela = document.querySelector("#restbl");
if (tabela) {
	// Seleciona o primeiro <tr> dentro da tabela
	let primeiroTr = tabela.querySelector("tbody > tr");

	if (primeiroTr) {
		// Seleciona todos os divs dentro do primeiro <tr> que contenham "preceitabruta" na classe
		let divs = primeiroTr.querySelectorAll("div[class*='preceitabruta']");

		// Oculta cada um desses divs
		divs.forEach(div => {
			div.style.display = "none";
		});
	}
}
//FIM oculta o percentual da primeira linha RECEITA BRUTA

</script>
