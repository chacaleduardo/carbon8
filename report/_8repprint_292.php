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

		$strselectfields = "select   contaitem , tipoprodserv,'".$_REQUEST['status']."' as status , sum(valor) as valor,idcontaitem"; 

		
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

if($_REQUEST['idempresa']){
	$clausorg2 .= " and a.idempresa in ( ".$_REQUEST['idempresa'].") ";
}

if($_REQUEST['idcontaitem']){
	$clausorg2 .= " and a.idcontaitem in ( ".$_REQUEST['idcontaitem'].") ";
}

if($_REQUEST['idtipoprodserv']){
	$clausorg2 .= " and a.idtipoprodserv in ( ".$_REQUEST['idtipoprodserv'].") ";
}

if($_REQUEST['idagencia']){
	$clausorg2 .= " and a.idagencia in ( ".$_REQUEST['idagencia'].") ";
}

if($_REQUEST['tipo']){	

	$_value=null;
	$_val=explode(',',$_REQUEST['tipo']);
	$virg='';
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
	$ctipo = " and cp.tipo  in (" . $_value . ") ";
	}
}


if($_REQUEST['status']){	

	 $_value=null;
	 $_val=explode(',',$_REQUEST['status']);
	 $virg='';
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
	 $cstatus = " and cp.status  in (" . $_value . ") ";
	 }
}

if($_REQUEST['tiporelatorio']){
	$strdate = 'datareceb';
}


if($_REQUEST['layout']=="SIMPLES"){
	$simples='hide';
}else{
	$simples='';
}



$vw8despesas="	
SELECT 
'PREV' AS `tiponf`,
`c`.`contaitem` AS `contaitem`,
`c`.`idcontaitem` AS `idcontaitem`,
c.idcontatipo,
`c`.`cor` AS `cor`,
`c`.`somarelatorio` AS `somarelatorio`,
`c`.`previsao` AS `previsao`,
`cp`.`status` AS `status`,
`cp`.`tipo` AS `tipo`,
`c`.`faturamento` AS `faturamento`,
`c`.`ordem` AS `ordem`,
f.descricao AS `descricao`,
`cp`.`idcontapagar` AS `idnf`,
`cp`.`datareceb` AS `datareceb`,
`cp`.`idempresa` AS `idempresa`,
`e`.`empresa` AS `empresa`,
`e`.`corsistema` AS `corsistema`,
`cp`.`idagencia` AS `idagencia`,
`cp`.`idcontapagar` AS `idcontapagar`,
`cp`.`parcela` AS `parcela`,
`cp`.`parcelas` AS `parcelas`,
`p`.`idtipoprodserv` AS `idtipoprodserv`,
`i`.`idconfcontapagar` AS `idnfitem`,
1 AS `qtd`,
'UN' AS `un`,
(`cp`.`valor` * -(1))  AS `total`,
`p`.`tipoprodserv` AS `tipoprodserv`,
'PREVISAO' AS `nnfe`,
`cp`.`valor` AS `vlritem`,
cp.idunidade,
c.fluxocaixa,
c.dre
FROM
`contapagar` `cp`	
join formapagamento f on(cp.idformapagamento = f.idformapagamento )
join confcontapagar i on(i.idformapagamento = cp.idformapagamento AND i.status='ATIVO')
JOIN `tipoprodserv` `p` ON (`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)
JOIN `contaitem` `c` ON (`c`.`idcontaitem` = `i`.`idcontaitem`  ".$strFluxoDre.") 
JOIN `empresa` `e` ON (`e`.`idempresa` = `cp`.`idempresa`)
WHERE `cp`.`tipoespecifico` IN ('REPRESENTACAO','IMPOSTO')
AND `cp`.`tipo` = 'D'
AND `cp`.`valor` > 0
AND cp.status in ('ABERTO','FECHADO')
".$cstatus." ".$ctipo."
 AND `cp`.`datareceb` BETWEEN   ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
 group by cp.idcontapagar
UNION ALL
SELECT 
'PREV' AS `tiponf`,
`c`.`contaitem` AS `contaitem`,
`c`.`idcontaitem` AS `idcontaitem`,
c.idcontatipo,
`c`.`cor` AS `cor`,
`c`.`somarelatorio` AS `somarelatorio`,
`c`.`previsao` AS `previsao`,
`cp`.`status` AS `status`,
`cp`.`tipo` AS `tipo`,
`c`.`faturamento` AS `faturamento`,
`c`.`ordem` AS `ordem`,
f.descricao AS `descricao`,
`cp`.`idcontapagar` AS `idnf`,
`cp`.`datareceb` AS `datareceb`,
`cp`.`idempresa` AS `idempresa`,
`e`.`empresa` AS `empresa`,
`e`.`corsistema` AS `corsistema`,
`cp`.`idagencia` AS `idagencia`,
`cp`.`idcontapagar` AS `idcontapagar`,
`cp`.`parcela` AS `parcela`,
`cp`.`parcelas` AS `parcelas`,
`p`.`idtipoprodserv` AS `idtipoprodserv`,
`i`.`idformapagamentopessoa` AS `idnfitem`,
1 AS `qtd`,
'UN' AS `un`,
(((i.previsao/f.previsao)*cp.valor)* -(1)) AS `total`,
`p`.`tipoprodserv` AS `tipoprodserv`,
'PREVISAO' AS `nnfe`,
((i.previsao/f.previsao)*cp.valor) AS `vlritem`,
cp.idunidade,
c.fluxocaixa,
c.dre
FROM
`contapagar` `cp`	
join formapagamento f on(cp.idformapagamento = f.idformapagamento  and  f.formapagamento ='C.CREDITO')
Join formapagamentopessoa i on(i.idformapagamento=f.idformapagamento)
JOIN `tipoprodserv` `p` ON (`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)
JOIN `contaitem` `c` ON (`c`.`idcontaitem` = `i`.`idcontaitem` ".$strFluxoDre.") 
JOIN `empresa` `e` ON (`e`.`idempresa` = `cp`.`idempresa`)
WHERE `cp`.`tipoespecifico` = 'AGRUPAMENTO'
AND `cp`.`tipo` = 'D'
AND `cp`.`valor` > 0
AND cp.status in ('ABERTO','FECHADO')
".$cstatus." ".$ctipo."
 AND `cp`.`datareceb` BETWEEN   ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
 group by cp.idcontapagar,`i`.`idformapagamentopessoa`
 UNION ALL
 SELECT 
'PREV' AS `tiponf`,
`c`.`contaitem` AS `contaitem`,
`c`.`idcontaitem` AS `idcontaitem`,
c.idcontatipo,
`c`.`cor` AS `cor`,
`c`.`somarelatorio` AS `somarelatorio`,
`i`.`previsao` AS `previsao`,
`cp`.`status` AS `status`,
`cp`.`tipo` AS `tipo`,
`c`.`faturamento` AS `faturamento`,
`c`.`ordem` AS `ordem`,
f.descricao AS `descricao`,
`cp`.`idcontapagar` AS `idnf`,
`cp`.`datareceb` AS `datareceb`,
`cp`.`idempresa` AS `idempresa`,
`e`.`empresa` AS `empresa`,
`e`.`corsistema` AS `corsistema`,
`cp`.`idagencia` AS `idagencia`,
`cp`.`idcontapagar` AS `idcontapagar`,
`cp`.`parcela` AS `parcela`,
`cp`.`parcelas` AS `parcelas`,
`p`.`idtipoprodserv` AS `idtipoprodserv`,
`i`.`idformapagamentopessoa` AS `idnfitem`,
1 AS `qtd`,
'UN' AS `un`,
(cp.valor * -(1))  AS `total`,
`p`.`tipoprodserv` AS `tipoprodserv`,
'PREVISAO' AS `nnfe`,
cp.valor AS `vlritem`,
cp.idunidade,
c.fluxocaixa,
c.dre
FROM
`contapagar` `cp`	
join formapagamento f on(cp.idformapagamento = f.idformapagamento  and  f.formapagamento ='BOLETO' AND f.agruppessoa='Y')
Join formapagamentopessoa i on(i.idformapagamento=f.idformapagamento  AND i.idpessoa =cp.idpessoa)
JOIN `tipoprodserv` `p` ON (`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)
JOIN `contaitem` `c` ON (`c`.`idcontaitem` = `i`.`idcontaitem` ".$strFluxoDre.") 
JOIN `empresa` `e` ON (`e`.`idempresa` = `cp`.`idempresa`)
WHERE `cp`.`tipoespecifico` = 'AGRUPAMENTO'
AND `cp`.`tipo` = 'D'
AND `cp`.`valor` > 0
AND cp.status in ('ABERTO','FECHADO')
".$cstatus." ".$ctipo."
AND `cp`.`datareceb` BETWEEN   ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
 group by cp.idcontapagar,`i`.`idformapagamentopessoa`
UNION ALL
SELECT 
`n`.`tiponf` AS `tiponf`,
	`c`.`contaitem` AS `contaitem`,
	`c`.`idcontaitem` AS `idcontaitem`,
	c.idcontatipo,
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
	`cp`.`idagencia` AS `idagencia`,
	`cp`.`idcontapagar` AS `idcontapagar`,
	`cp`.`parcela` AS `parcela`,
	`cp`.`parcelas` AS `parcelas`,
	`p`.`idtipoprodserv` AS `idtipoprodserv`,
	`i`.`idnfitem` AS `idnfitem`,
	`i`.`qtd` AS `qtd`,
	IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
	(((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - ifnull(`n`.`frete`,0))) * ifnull(`n`.`frete`,0))) / `n`.`total`) * `cp`.`valor`) * -(1)) AS `total`,
	`p`.`tipoprodserv` AS `tipoprodserv`,
	`n`.`nnfe` AS `nnfe`,
	`i`.`vlritem` AS `vlritem`,
	n.idunidade,
	c.fluxocaixa,
	c.dre
FROM
	((((((`nf` `n`
	JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
	AND (`i`.`nfe` = 'Y'))))
	JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
	JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`) ".$strFluxoDre."))
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
	".$cstatus." ".$ctipo."
	AND `cp`.`datareceb` BETWEEN   ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
UNION ALL 

SELECT 
`n`.`tiponf` AS `tiponf`,
	`c`.`contaitem` AS `contaitem`,
	`c`.`idcontaitem` AS `idcontaitem`,
	c.idcontatipo,
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
	`cp`.`idagencia` AS `idagencia`,
	`cp`.`idcontapagar` AS `idcontapagar`,
	`cp`.`parcela` AS `parcela`,
	`cp`.`parcelas` AS `parcelas`,
	`p`.`idtipoprodserv` AS `idtipoprodserv`,
	`i`.`idnfitem` AS `idnfitem`,
	`i`.`qtd` AS `qtd`,
	IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
	(((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - ifnull(`n`.`frete`,0))) * ifnull(`n`.`frete`,0))) / `n`.`total`) * sum(`ci`.`valor`)) * -(1)) AS `total`,
	`p`.`tipoprodserv` AS `tipoprodserv`,
	`n`.`nnfe` AS `nnfe`,
	`i`.`vlritem` AS `vlritem`,
	n.idunidade,
	c.fluxocaixa,
	c.dre
FROM
	`contapagar` `cp`
	JOIN `contapagaritem` `ci` ON (`cp`.`idcontapagar` = `ci`.`idcontapagar` AND `ci`.`tipoobjetoorigem` = 'nf')
	JOIN `nf` `n` ON (`ci`.`idobjetoorigem` = `n`.`idnf`)
	JOIN `nfitem` `i` ON (`i`.`idnf` = `n`.`idnf` AND `i`.`nfe` = 'Y')
	JOIN `tipoprodserv` `p` ON (`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)
	JOIN `contaitem` `c` ON (`c`.`idcontaitem` = `i`.`idcontaitem` ".$strFluxoDre." )
	join formapagamento f on(cp.idformapagamento = f.idformapagamento  and ((cp.status not in ('ABERTO','FECHADO')) OR ((`f`.`formapagamento` <> 'C.CREDITO')  and (`f`.`formapagamento` <> 'BOLETO' or f.agruppessoa <> 'Y') )))
	LEFT JOIN `prodserv` `ps` ON (`ps`.`idprodserv` = `i`.`idprodserv`)
	JOIN `empresa` `e` ON (`e`.`idempresa` = `cp`.`idempresa`)
WHERE
((`cp`.`tipoespecifico` = 'AGRUPAMENTO')
	AND (`cp`.`status` NOT IN('INATIVO','DEVOLVIDO','CANCELADO'))
	AND (`ci`.`status` NOT IN('INATIVO','DEVOLVIDO','CANCELADO'))
	AND (`cp`.`tipo` = 'D')
	AND (`cp`.`valor` > 0)
	AND (`n`.`tiponf` NOT IN ('S' , 'R'))) 
	".$cstatus." ".$ctipo."
	AND `cp`.`datareceb` BETWEEN   ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
	and `i`.`qtd` > 0
	group by cp.idcontapagar,i.idnfitem
UNION ALL SELECT 
`n`.`tiponf` AS `tiponf`,
	`c`.`contaitem` AS `contaitem`,
	`c`.`idcontaitem` AS `idcontaitem`,
	c.idcontatipo,
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
	`cp`.`idagencia` AS `idagencia`,
	`cp`.`idcontapagar` AS `idcontapagar`,
	`cp`.`parcela` AS `parcela`,
	`cp`.`parcelas` AS `parcelas`,
	`p`.`idtipoprodserv` AS `idtipoprodserv`,
	`i`.`idnfitem` AS `idnfitem`,
	`i`.`qtd` AS `qtd`,
	IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
	((((IFNULL(`i`.`total`, 0) * (`n`.`total` / ifnull(n.subtotal,n.total))) / `n`.`total`) * `cp`.`valor`) * -(1)) AS `total`,
	`p`.`tipoprodserv` AS `tipoprodserv`,
	`n`.`nnfe` AS `nnfe`,
	`i`.`vlritem` AS `vlritem`,
	n.idunidade,
	c.fluxocaixa,
	c.dre
FROM
	((((((`nf` `n`
	JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
	AND (`i`.`nfe` = 'Y'))))
	JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
	JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`) ".$strFluxoDre." ))
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
	".$cstatus." ".$ctipo."
	AND `cp`.`datareceb` BETWEEN   ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
	and `i`.`qtd` > 0
UNION ALL 
SELECT 
`n`.`tiponf` AS `tiponf`,
	`c`.`contaitem` AS `contaitem`,
	`c`.`idcontaitem` AS `idcontaitem`,
	c.idcontatipo,
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
	`cp`.`idagencia` AS `idagencia`,
	`cp`.`idcontapagar` AS `idcontapagar`,
	`cp`.`parcela` AS `parcela`,
	`cp`.`parcelas` AS `parcelas`,
	`p`.`idtipoprodserv` AS `idtipoprodserv`,
	`i`.`idnfitem` AS `idnfitem`,
	`i`.`qtd` AS `qtd`,
	IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
	((((IFNULL(`i`.`total`, 0) * (`n`.`total` / ifnull(n.subtotal,n.total))) / `n`.`total`) * sum(`ci`.`valor`)) * -(1)) AS `total`,
	`p`.`tipoprodserv` AS `tipoprodserv`,
	`n`.`nnfe` AS `nnfe`,
	`i`.`vlritem` AS `vlritem`,
	n.idunidade,
	c.fluxocaixa,
	c.dre
FROM
	`contapagar` `cp`
	JOIN `contapagaritem` `ci` ON (`cp`.`idcontapagar` = `ci`.`idcontapagar` AND `ci`.`tipoobjetoorigem` = 'nf')
	JOIN `nf` `n` ON (`ci`.`idobjetoorigem` = `n`.`idnf`)
	JOIN `nfitem` `i` ON (`i`.`idnf` = `n`.`idnf` AND `i`.`nfe` = 'Y')
	JOIN `tipoprodserv` `p` ON (`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)
	JOIN `contaitem` `c` ON (`c`.`idcontaitem` = `i`.`idcontaitem` ".$strFluxoDre." )
	LEFT JOIN `prodserv` `ps` ON (`ps`.`idprodserv` = `i`.`idprodserv`)
	join formapagamento f on(cp.idformapagamento = f.idformapagamento  and ((cp.status not in ('ABERTO','FECHADO')) OR ((`f`.`formapagamento` <> 'C.CREDITO')  and (`f`.`formapagamento` <> 'BOLETO' or f.agruppessoa <> 'Y') )))
	JOIN `empresa` `e` ON (`e`.`idempresa` = `cp`.`idempresa`)
WHERE
(`cp`.`tipoespecifico` = 'AGRUPAMENTO')
	AND (`cp`.`status` NOT IN('INATIVO','DEVOLVIDO','CANCELADO'))
	AND (`ci`.`status` NOT IN('INATIVO','DEVOLVIDO','CANCELADO'))
	AND (`cp`.`tipo` = 'D')
	AND (`cp`.`valor` > 0)
	AND (`n`.`tiponf` IN ('S' , 'R'))
	AND `cp`.`datareceb` BETWEEN   ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
	and `i`.`qtd` > 0
	".$cstatus." ".$ctipo."
	group by cp.idcontapagar,i.idnfitem
UNION ALL
SELECT n.tiponf,c.contaitem,c.idcontaitem,c.idcontatipo,c.cor,c.somarelatorio,c.previsao
	,cp.status,cp.tipo,c.faturamento
	,c.ordem,   
	IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
	`n`.`idnf` AS `idnf`,
	`cp`.`datareceb` AS `datareceb`,
	`cp`.`idempresa` AS `idempresa`,
	`e`.`empresa` AS `empresa`,
	`e`.`corsistema` AS `corsistema`,
	`cp`.`idagencia` AS `idagencia`,
	`cp`.`idcontapagar` AS `idcontapagar`,
	`cp`.`parcela` AS `parcela`,
	`cp`.`parcelas` AS `parcelas`,
	`p`.`idtipoprodserv` AS `idtipoprodserv`,
	`i`.`idnfitem` AS `idnfitem`,
	`i`.`qtd` AS `qtd`,
	IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
	CASE
		WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0)+ IFNULL(`i`.`voutro`, 0) + IFNULL(`i`.`vseg`, 0))/n.total)*cp.valor),2)  
		ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total- ifnull(`n`.`frete`,0) ))* (ifnull(`n`.`frete`,0))))/n.total)*cp.valor),2)
	END as total,
	`p`.`tipoprodserv` AS `tipoprodserv`,
	`n`.`nnfe` AS `nnfe`,
	`i`.`vlritem` AS `vlritem`,
	n.idunidade,
	c.fluxocaixa,
	c.dre
FROM nf n 
	join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0)          
	join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
	join contaitem c on(c.idcontaitem=i.idcontaitem  ".$strFluxoDre."  )  
	join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
	LEFT JOIN `prodserv` `ps` ON (`ps`.`idprodserv` = `i`.`idprodserv`)
	JOIN `empresa` `e` ON (`e`.`idempresa` = `cp`.`idempresa`)
where cp.tipoespecifico!= 'AGRUPAMENTO'    
	and i.idprodserv is null
	AND cp.datareceb BETWEEN  ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
	and cp.status NOT IN('INATIVO','DEVOLVIDO','CANCELADO')
	and cp.tipo = 'C'
	".$cstatus." ".$ctipo."
union all
 SELECT   n.tiponf,c.contaitem,c.idcontaitem,c.idcontatipo,c.cor,c.somarelatorio,c.previsao
		 ,cp.status,cp.tipo,c.faturamento
		,c.ordem,
		IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
	`n`.`idnf` AS `idnf`,
	`cp`.`datareceb` AS `datareceb`,
	`cp`.`idempresa` AS `idempresa`,
	`e`.`empresa` AS `empresa`,
	`e`.`corsistema` AS `corsistema`,
	`cp`.`idagencia` AS `idagencia`,
	`cp`.`idcontapagar` AS `idcontapagar`,
	`cp`.`parcela` AS `parcela`,
	`cp`.`parcelas` AS `parcelas`,
	`p`.`idtipoprodserv` AS `idtipoprodserv`,
	`i`.`idnfitem` AS `idnfitem`,
	`i`.`qtd` AS `qtd`,
	IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
	CASE
		WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0)+ IFNULL(`i`.`voutro`, 0) + IFNULL(`i`.`vseg`, 0))/n.total)*sum(ci.valor)),2)  
		ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-ifnull(`n`.`frete`,0)))* (ifnull(`n`.`frete`,0))))/n.total)*sum(ci.valor)),2)
	END as total,
	`p`.`tipoprodserv` AS `tipoprodserv`,
	`n`.`nnfe` AS `nnfe`,
	`i`.`vlritem` AS `vlritem`,
	n.idunidade,
	c.fluxocaixa,
	c.dre
FROM contapagar cp
	join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
								and ci.tipoobjetoorigem ='nf')
	join nf n on(ci.idobjetoorigem =n.idnf  ) 
	join nfitem i on(i.idnf=n.idnf 
						and i.nfe='Y' -- and i.total>0
						)          
	join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
	join contaitem c on(c.idcontaitem=i.idcontaitem  ".$strFluxoDre." )
		LEFT JOIN `prodserv` `ps` ON (`ps`.`idprodserv` = `i`.`idprodserv`)
	JOIN `empresa` `e` ON (`e`.`idempresa` = `cp`.`idempresa`)
where cp.tipoespecifico = 'AGRUPAMENTO' 
	and i.idprodserv is null
	AND cp.datareceb BETWEEN  ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
	and cp.status NOT IN('INATIVO','DEVOLVIDO','CANCELADO')
	and ci.status NOT IN('INATIVO','DEVOLVIDO','CANCELADO')
	and cp.tipo = 'C'
	".$cstatus." ".$ctipo."
	group by cp.idcontapagar,i.idnfitem
union all
SELECT n.tiponf,c.contaitem,c.idcontaitem,c.idcontatipo,c.cor,c.somarelatorio,c.previsao
	,cp.status,cp.tipo,c.faturamento
	,c.ordem,  
	IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
	`n`.`idnf` AS `idnf`,
	`cp`.`datareceb` AS `datareceb`,
	`cp`.`idempresa` AS `idempresa`,
	`e`.`empresa` AS `empresa`,
	`e`.`corsistema` AS `corsistema`,
	`cp`.`idagencia` AS `idagencia`,
	`cp`.`idcontapagar` AS `idcontapagar`,
	`cp`.`parcela` AS `parcela`,
	`cp`.`parcelas` AS `parcelas`,
	`p`.`idtipoprodserv` AS `idtipoprodserv`,
	`i`.`idnfitem` AS `idnfitem`,
	`i`.`qtd` AS `qtd`,
	IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
	CASE
		WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0)+ IFNULL(`i`.`voutro`, 0)+ IFNULL(`i`.`vseg`, 0))/n.total)*cp.valor),2)  
		ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-ifnull(`n`.`frete`,0)))* (ifnull(`n`.`frete`,0))))/n.total)*cp.valor),2)
	END as total,
	`p`.`tipoprodserv` AS `tipoprodserv`,
	`n`.`nnfe` AS `nnfe`,
	`i`.`vlritem` AS `vlritem`,
	n.idunidade,
	c.fluxocaixa,
	c.dre
FROM nf n 
	join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0 ) 
	join prodserv ps on(ps.idprodserv =i.idprodserv)
	join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
	join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
	join contaitem c on(c.idcontaitem=pc.idcontaitem ".$strFluxoDre." )   
	join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')                                     
	JOIN `empresa` `e` ON (`e`.`idempresa` = `cp`.`idempresa`)
where cp.tipoespecifico!= 'AGRUPAMENTO'    
	AND cp.datareceb BETWEEN  ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
	and cp.status NOT IN('INATIVO','DEVOLVIDO','CANCELADO')
	and cp.tipo = 'C'
	".$cstatus." ".$ctipo."
union all
SELECT   n.tiponf,c.contaitem,c.idcontaitem,c.idcontatipo,c.cor,c.somarelatorio,c.previsao
	,cp.status,cp.tipo,c.faturamento
	,c.ordem,
	IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
	`n`.`idnf` AS `idnf`,
	`cp`.`datareceb` AS `datareceb`,
	`cp`.`idempresa` AS `idempresa`,
	`e`.`empresa` AS `empresa`,
	`e`.`corsistema` AS `corsistema`,
	`cp`.`idagencia` AS `idagencia`,
	`cp`.`idcontapagar` AS `idcontapagar`,
	`cp`.`parcela` AS `parcela`,
	`cp`.`parcelas` AS `parcelas`,
	`p`.`idtipoprodserv` AS `idtipoprodserv`,
	`i`.`idnfitem` AS `idnfitem`,
	`i`.`qtd` AS `qtd`,
	IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
	CASE
		WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0)+ IFNULL(`i`.`voutro`, 0)+ IFNULL(`i`.`vseg`, 0))/n.total)*sum(ci.valor)),2)  
		ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-ifnull(`n`.`frete`,0)))* (ifnull(`n`.`frete`,0))))/n.total)*sum(ci.valor)),2)
	END as total,
	`p`.`tipoprodserv` AS `tipoprodserv`,
	`n`.`nnfe` AS `nnfe`,
	`i`.`vlritem` AS `vlritem`,
	n.idunidade,
	c.fluxocaixa,
	c.dre
FROM contapagar cp
	join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
								and ci.tipoobjetoorigem ='nf')
	join nf n on(ci.idobjetoorigem =n.idnf  ) 
	join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0) 
	join prodserv ps on(ps.idprodserv =i.idprodserv)
	join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
	join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
	join contaitem c on(c.idcontaitem=pc.idcontaitem ".$strFluxoDre.")                                      
	JOIN `empresa` `e` ON (`e`.`idempresa` = `cp`.`idempresa`)
where cp.tipoespecifico = 'AGRUPAMENTO' 
	AND cp.datareceb BETWEEN  ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
	and cp.status NOT IN('INATIVO','DEVOLVIDO','CANCELADO')
	and ci.status NOT IN('INATIVO','DEVOLVIDO','CANCELADO')
	and cp.tipo = 'C'
	".$cstatus." ".$ctipo."
	group by cp.idcontapagar,i.idnfitem
union all
SELECT                                    
	'SV' as tiponf,c.contaitem,c.idcontaitem,c.idcontatipo,c.cor,c.somarelatorio,c.previsao  ,cp.status,cp.tipo,c.faturamento
	,c.ordem,  
	IFNULL(`ps`.`descr`, `i`.`descricao`) AS `descricao`,
	`n`.`idnotafiscal` AS `idnf`,
	`cp`.`datareceb` AS `datareceb`,
	`cp`.`idempresa` AS `idempresa`,
	`e`.`empresa` AS `empresa`,
	`e`.`corsistema` AS `corsistema`,
	`cp`.`idagencia` AS `idagencia`,
	`cp`.`idcontapagar` AS `idcontapagar`,
	`cp`.`parcela` AS `parcela`,
	`cp`.`parcelas` AS `parcelas`,
	`p`.`idtipoprodserv` AS `idtipoprodserv`,
	`i`.`idnotafiscalitens` AS `idnfitem`,
	`i`.`quantidade` AS `qtd`,
	'TESTE' AS `un`,
	round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(cp.valor/n.total),2) as total,
	`p`.`tipoprodserv` AS `tipoprodserv`,
	`n`.`nnfe` AS `nnfe`,
	`i`.`valor` AS `vlritem`,
	1 as idunidade,
	c.fluxocaixa,
	c.dre
FROM notafiscal n 
	join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal  ) 
	join prodserv ps on(ps.idprodserv=i.idprodserv)
	join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
	join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
	join contaitem c on(c.idcontaitem=pc.idcontaitem ".$strFluxoDre.")   
	join contapagar cp on(cp.idobjeto = n.idnotafiscal and cp.tipoobjeto ='notafiscal')
	JOIN `empresa` `e` ON (`e`.`idempresa` = `cp`.`idempresa`)
where cp.tipoespecifico!= 'AGRUPAMENTO' 
	AND cp.datareceb BETWEEN  ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
	and cp.status NOT IN('INATIVO','DEVOLVIDO','CANCELADO')
	".$cstatus." ".$ctipo."
	and cp.tipo = 'C' group by  i.idnotafiscal,cp.idcontapagar,i.idprodserv,i.valor
union all
SELECT   
	'SV' as tiponf,c.contaitem,c.idcontaitem,c.idcontatipo,c.cor,c.somarelatorio,c.previsao  ,cp.status,cp.tipo,c.faturamento
	,c.ordem,
	IFNULL(`ps`.`descr`, `i`.`descricao`) AS `descricao`,
	`n`.`idnotafiscal` AS `idnf`,
	`cp`.`datareceb` AS `datareceb`,
	`cp`.`idempresa` AS `idempresa`,
	`e`.`empresa` AS `empresa`,
	`e`.`corsistema` AS `corsistema`,
	`cp`.`idagencia` AS `idagencia`,
	`cp`.`idcontapagar` AS `idcontapagar`,
	`cp`.`parcela` AS `parcela`,
	`cp`.`parcelas` AS `parcelas`,
	`p`.`idtipoprodserv` AS `idtipoprodserv`,
	`i`.`idnotafiscalitens` AS `idnfitem`,
	`i`.`quantidade` AS `qtd`,
	'TESTE' AS `un`,
	round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(ci.valor/n.total),2) as total,
		`p`.`tipoprodserv` AS `tipoprodserv`,
	`n`.`nnfe` AS `nnfe`,
	`i`.`valor` AS `vlritem`,
	1 as idunidade,
	c.fluxocaixa,
	c.dre
FROM contapagar cp
	join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
								and ci.tipoobjetoorigem ='notafiscal')
	join notafiscal n on(ci.idobjetoorigem =n.idnotafiscal  ) 
	join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal  ) 
	join prodserv ps on(ps.idprodserv=i.idprodserv)
	join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
	join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
	join contaitem c on(c.idcontaitem=pc.idcontaitem ".$strFluxoDre." )    
	JOIN `empresa` `e` ON (`e`.`idempresa` = `cp`.`idempresa`)
where cp.tipoespecifico = 'AGRUPAMENTO'                                    
	AND cp.datareceb BETWEEN  ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
	and cp.status NOT IN('INATIVO','DEVOLVIDO','CANCELADO')
	and ci.status NOT IN('INATIVO','DEVOLVIDO','CANCELADO')
	".$cstatus." ".$ctipo."
	and cp.tipo = 'C' group by  i.idnotafiscal,ci.idcontapagaritem,i.idprodserv,i.valor

union all
SELECT 'SV' as tiponf,c.contaitem,c.idcontaitem,c.idcontatipo,c.cor,c.somarelatorio,c.previsao,cp.status,cp.tipo,c.faturamento
	,c.ordem,
	`i`.`descricao` AS `descricao`,
	`n`.`idnotafiscal` AS `idnf`,
	`cp`.`datareceb` AS `datareceb`,
	`cp`.`idempresa` AS `idempresa`,
	`e`.`empresa` AS `empresa`,
	`e`.`corsistema` AS `corsistema`,
	`cp`.`idagencia` AS `idagencia`,
	`cp`.`idcontapagar` AS `idcontapagar`,
	`cp`.`parcela` AS `parcela`,
	`cp`.`parcelas` AS `parcelas`,
	`p`.`idtipoprodserv` AS `idtipoprodserv`,
	`i`.`idnotafiscalitens` AS `idnfitem`,
	`i`.`quantidade` AS `qtd`,
	'TESTE' AS `un`,
	round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(cp.valor/n.total),2) as total,
	`p`.`tipoprodserv` AS `tipoprodserv`,
	`n`.`nnfe` AS `nnfe`,
	`i`.`valor` AS `vlritem`,
	1 as idunidade,
	c.fluxocaixa,
	c.dre
FROM notafiscal n 
	join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal and i.idprodserv is null ) 
	join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
	join contaitem c on(c.idcontaitem=i.idcontaitem ".$strFluxoDre." )   
	join contapagar cp on(cp.idobjeto = n.idnotafiscal and cp.tipoobjeto ='notafiscal')
	JOIN `empresa` `e` ON (`e`.`idempresa` = `cp`.`idempresa`)
where cp.tipoespecifico!= 'AGRUPAMENTO'                                                            
	AND cp.datareceb BETWEEN ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
	and cp.status NOT IN('INATIVO','DEVOLVIDO','CANCELADO')
	".$cstatus." ".$ctipo."
	and cp.tipo = 'C' group by  i.idnotafiscal,cp.idcontapagar,i.idtipoprodserv,i.valor
union all
 SELECT   
	'SV' as tiponf,c.contaitem,c.idcontaitem,c.idcontatipo,c.cor,c.somarelatorio,c.previsao  ,cp.status,cp.tipo,c.faturamento
	,c.ordem, 
	`i`.`descricao`,
	`n`.`idnotafiscal` AS `idnf`,
	`cp`.`datareceb` AS `datareceb`,
	`cp`.`idempresa` AS `idempresa`,
	`e`.`empresa` AS `empresa`,
	`e`.`corsistema` AS `corsistema`,
	`cp`.`idagencia` AS `idagencia`,
	`cp`.`idcontapagar` AS `idcontapagar`,
	`cp`.`parcela` AS `parcela`,
	`cp`.`parcelas` AS `parcelas`,
	`p`.`idtipoprodserv` AS `idtipoprodserv`,
	`i`.`idnotafiscalitens` AS `idnfitem`,
	`i`.`quantidade` AS `qtd`,
	'TESTE' AS `un`,
	round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(ci.valor/n.total),2) as total,
	`p`.`tipoprodserv` AS `tipoprodserv`,
	`n`.`nnfe` AS `nnfe`,
	`i`.`valor` AS `vlritem`,
	1 as idunidade,
	c.fluxocaixa,
	c.dre
FROM contapagar cp
	join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
								and ci.tipoobjetoorigem ='notafiscal')
	join notafiscal n on(ci.idobjetoorigem =n.idnotafiscal  ) 
	join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal and i.idprodserv is null ) 
	join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
	join contaitem c on(c.idcontaitem=i.idcontaitem ".$strFluxoDre." )
		LEFT JOIN `prodserv` `ps` ON (`ps`.`idprodserv` = `i`.`idprodserv`)
	JOIN `empresa` `e` ON (`e`.`idempresa` = `cp`.`idempresa`)
where cp.tipoespecifico = 'AGRUPAMENTO'                                     
	AND cp.datareceb BETWEEN  ".evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2)."
	and cp.status NOT IN('INATIVO','DEVOLVIDO','CANCELADO')
	and ci.status NOT IN('INATIVO','DEVOLVIDO','CANCELADO')
	".$cstatus." ".$ctipo."
	and cp.tipo = 'C' group by  i.idnotafiscal,ci.idcontapagaritem,i.idtipoprodserv,i.valor
	";



	$_sqlresultado = "select 
	`a`.`tiponf` AS `tiponf`,
	`a`.`idcontaitem` AS `idcontaitem`,
	`a`.`contaitem` AS `contaitem`,
	`a`.`idtipoprodserv` AS `idtipoprodserv`,
	`a`.`tipoprodserv` AS `tipoprodserv`,
	concat('<a href=\"?_modulo=',
			
	CASE
	WHEN a.idunidade in (254,301,355,363,370,413)  THEN 'comprasrh'
	WHEN a.idunidade in (321,335,344,345,349)  THEN 'comprassocios'
	WHEN a.idunidade in (290,312,350,351,378)  THEN 'nfcte'
	WHEN a.idunidade in (313,333,334,364,367)  THEN 'nfentrada'
	WHEN a.idunidade in (312,323,340,342,346)  THEN 'nfrdv'

	ELSE 'nfentrada'  
	END,'&_acao=u&_idempresa=',a.idempresa,'&idnf=',a.idnf,'\" target=\"_blank\">',case when a.nnfe='' then a.idnf when a.nnfe is null then a.idnf else a.nnfe end,'</a>') as linknf,
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
	`a`.`idempresa` AS `idempresa`,
	`a`.`idagencia` AS `idagencia`,
	`a`.`idnfitem` AS `idnfitem`,
	`a`.`idcontapagar` AS `idcontapagar`,
	`a`.`qtd` AS `qtd`,
	`a`.`un` AS `un`,
	`a`.`total` AS `total`,
	`a`.`total` AS `valor`,
	`a`.`parcela` AS `parcela`,
	`a`.`parcelas` AS `parcelas`,
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
	(select group_concat(o.idobjetovinc) as idsgrupo from objetovinculo o where  o.idobjeto = g.idcontatipogrupo
	and o.tipoobjeto ='contatipogrupo' 
	and o.tipoobjetovinc='contatipogrupo') as idsgrupo,
	ifnull(p3.previsao,'0.00') as contaitemprev,
    ifnull(p2.previsao,'0.00') as contatipoprev,
    ifnull(p1.previsao,'0.00') as contatipogrupoprev
from
	contatipogrupo g
	left join contatipo t on g.idcontatipogrupo = t.idcontatipogrupo  and t.status='ATIVO'
	left join (".$vw8despesas.") a on(t.idcontatipo = a.idcontatipo ".$clausorg2.")
	left join empresa e on(e.idempresa=a.idempresa)
	left join (select sum(previsao) as previsao, idtipoprodserv from vwtipoprodservprevisao pe1 where pe1.idempresaprev in ( ".$_REQUEST['idempresa'].") group by pe1.idtipoprodserv ) pe on(pe.idtipoprodserv=a.idtipoprodserv)
	left join (select sum(previsao) as previsao, idcontaitem from vwcontaitemprevisao pe3 where pe3.idempresaprev in ( ".$_REQUEST['idempresa'].") group by pe3.idcontaitem) p3 on(p3.idcontaitem=a.idcontaitem)
   	left join (select sum(previsao) as previsao, idcontatipo from vwcontatipoprevisao pe2 where pe2.idempresaprev in ( ".$_REQUEST['idempresa'].") group by pe2.idcontatipo) p2 on(p2.idcontatipo=a.idcontatipo)
	left join (select sum(previsao) as previsao, idcontatipogrupo from vwcontatipogrupoprevisao pe1 where pe1.idempresaprev in ( ".$_REQUEST['idempresa'].") group by pe1.idcontatipogrupo) p1 on(p1.idcontatipogrupo=g.idcontatipogrupo)

where 1 and g.status='ATIVO'
	
 	order by g.ordem,grupo,t.ordem,contatipo,a.ordem,contaitem, tipoprodserv,idtipoprodserv,descr";



$_sqlresultado =$strselectfields." from (".$_sqlresultado." ) bb where 1 ".$_sqlresultadounidade." and valor is not null group  by idcontaitem, idtipoprodserv order by contaitem,tipoprodserv ";
echo("<!-- rateio ".$_sqlresultado." -->");
	//echo "<!-- ".array_key_exists("STRCONTATOCLIENTE", $_SESSION["SESSAO"])." -->";	//echo $_sqlresultado;
	
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

	$sqlfig="select figrelatorio from empresa where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
	$resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: ".mysql_error());
	$figrel=mysqli_fetch_assoc($resfig);

	//$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
	$figurarelatorio = "../inc/img/repheader.png";
	
?>
	<div style="display:none; text-align: left; font-size: 9px;" class="n_linhas"><span  id="nlinha"><?=$strs?></span></div>

	<table class="tbrepheader">
	<tr>
		<td rowspan="3" style="width:200;"><img src="<?=$figurarelatorio?>"></td>
		<td class="header"><? //=$_header?></td>
		<td><a class="btbr20 no-print" href="<?=$_SERVER['REQUEST_URI']?>&reportexport=csv" target="_blank">Exportar .csv</a></td>
	</tr>
	<tr>
		<td class="subheader"><h2><?=($_rep);?> - (<?=$_REQUEST['status']?>)</h2>
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
		if($arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["visres"] == 'Y'){
	   // if($arrRep["_filtros"][$_metacmp->name]["visres"] == 'Y'){

	    	//A VIRGULA E SO ENTRE OS VALORES NO INICIO DA LINHA E NO FINAL NÃO TEM VIRGULA
	    	if(!empty($conteudoexport)){
	    		$conteudoexport.=";";
	    	}
			//echo 'opa'.$arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["rotulo"];
			if (strpos(strtolower($arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["rotulo"]), ' as ') !== false) {
				//echo 'aqio';
				$val = explode(' as ',strtolower($arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["rotulo"]));
				$arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["rotulo"] = $val[1];
			}
	    	//
			if($_metacmp->name!='qtdprod_exp'){
				$strtabheader .= "<td class='header' id='".seo_friendly_url(str_replace('`','',$arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["rotulo"]))."' style=\"text-align:".$arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["align"]."\">" . str_replace('`','',$arrRep["_filtros"][$arrRep["_colvisiveis"][$_i+1]]["rotulo"])."</td>";
			}
	    }
		if($_metacmp->name!='qtdprod_exp'){		
			$conteudoexport.= "\"".$arrRep["_filtros"][$_metacmp->name]["rotulo"]."\"";// GRAVA O ROTULO DOS CABEÇALHOS NA VARIAVEL PARA GERAR O CSV
		}
	    $_i++;
	}//while ($_i < $_numcolunas) {
	
	$conteudoexport.="\n";//QUEBRA DE LINHA NO CONTEUDO CSV
	$strtabheader .= "</tr></thead><tbody>";

	/*
	 * Variaveis para cabecalho do report
	 */
	$strpagini = "\n<fieldset class='fldsheader'><legend>Impressão ".$_nomeimpressao."</legend></fieldset>";
	$strtabini = "\n<table class='normal'>";
	$strtabheader = $strtabheader;
	if(!empty($simples)){
		$strtabheader = '';
	}

	/*
	 * MONTA A TABELA
	 */
	$_ilinha = 0;//armazena o ttotal de registros
	$_ilinhaquebra = 0;//armazena parialmente o numero de registros se houver quebra automatica configurada
	$strnewpage = "<span class='newreppage'></span>";
		$valor=0;
		$idcontaitem=0;
		//contaitem , tipoprodserv ,'".$_REQUEST['status']."' as status, sum(valor) as valor,idcontaitem
    while ($_row = mysql_fetch_array($_resultados)){
	$_ilinha++;
	$_i = 0;

	if($idcontaitem==0){
		$idcontaitem =$_row[4];
		$contaitem =$_row[0];
	}

	if($idcontaitem >0  and $idcontaitem != $_row[4]){
		
		?>
		<tr class="res" style="background-color: #c5c3bf;">
			<td style="border: 1px solid rgb(192, 192, 192);" colspan="2"><b><?=$contaitem?></b></td>
			<td style="border: 1px solid rgb(192, 192, 192);" ><b>TOTAL</b></td>
			<td style="border: 1px solid rgb(192, 192, 192);"><b>R$ <?= number_format($valor, 2, ',','.');?></b></td>
		</tr>
		<?
		$idcontaitem =$_row[4];
		$contaitem =$_row[0];
		$valor=0;
	}
	$valor=$valor+$_row[3];

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
    	echo "\n<tr class=\"res ".$simples." \" ". $_link ." ". $_strhlcolor . ">";
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
			//$_i.'<br>';
			$_nomecol = $arrRep["_colvisiveis"][$_i+1];
			///echo $_i.'<br>';
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
				}elseif($arrRep["_filtros"][$_nomecol]["datatype"]=='double'){
					if(!empty($_row['qtdprod_exp'])){
						$_strvlrhtml =recuperaExpoente($_row[$_i],$_row['qtdprod_exp']);
					}else{
						$_strvlrhtml = number_format(tratanumero($_row[$_i]), 2, ',', '.');
					}
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
					if ($_strvlrhtml != '0.00'){
						//O HREF contém uma barra ('/') o que significa que é uma URL relativa, que vai desconsiderar qualquer pasta informada neste link
						if (strpos($arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$_i] , 'pk=')){
							
							$arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$_i];
							 $valor = explode('pk=',$arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$_i]);
							// print_r($valor);
							$valor = explode('&',$valor[1]);
							//print_r($valor,);
							 $campo = $_row[$valor[0]];
							
							$_hyperlink="<a target=_blank href='/".$arrRep["_filtros"][$_nomecol]["hyperlink"].$campo."'>".$_strvlrhtml."</a>";
						}else{
							$_hyperlink="<a target=_blank href='/".$arrRep["_filtros"][$_nomecol]["hyperlink"].$_row[$_i]."'>".$_strvlrhtml."</a>";
						}
						
						$_colorlink="class=\"link\" ";
						$_corfont= "<font color='Blue'>";
						$_corfontfim="</font>";
					}else{
						$_hyperlink = $_strvlrhtml;
					}
    				    				
    			}
				
				//Finalmente: desenha o campo na tela
				if($_nomecol!='qtdprod_exp'){
					if(!empty($arrRep["_filtros"][$_nomecol]["hyperlink"])){
						echo "<td ".$_stralign." >".$_hyperlink."</td>";
					}else{
						echo "<td ".$_stralign." ".$_hyperlink." ".$_colorlink.">".$_corfont.$_strvlrhtml.$_corfontfim."</td>";
					}
				}
	    	}
	    	$_i++;
    	}
		$conteudoexport.="\n";//QUEBRA A LINHA DO CONTEUDO CSV
    }
	
//	print_r($_arrsoma);
?>
		</tr>
		<tr class="res" style="background-color: #c5c3bf;">
			<td style="border: 1px solid rgb(192, 192, 192);" colspan="2"><b><?=$contaitem?></b></td>
			<td style="border: 1px solid rgb(192, 192, 192);" ><b>TOTAL</b></td>
			<td style="border: 1px solid rgb(192, 192, 192);"><b>R$ <?= number_format($valor, 2, ',','.');?></b></td>
		</tr>
<?

	if(!empty($_arrsoma)){
?>		
		<tr class="res">
			<td colspan="500" class="inv"></td>
		</tr>
		<tr class="res">
<?		
		$_y=0;
		while ($_y < $_numcolunas) {
			$_stralign="";
			$_strvlrhtml="";
    		$_nomecol = $_arridxcol[$_y];
			$_nomecol = $arrRep["_colvisiveis"][$_y+1];
		
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

	header('Content-Disposition: attachment; filename="MailingData.csv"');


	header("Content-Disposition: attachment; filename=".$infilename.".csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo iconv('UTF-8', 'ISO-8859-1', $conteudoexport);

	
}
?>
