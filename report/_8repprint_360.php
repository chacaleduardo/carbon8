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
				$_sqldata = " between " . evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2) . "";	
		
			}
		}
		
		
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
		
		//Reseta Variaveis de controle de virgula
		$strvirg = "";
	}

	
	
	/****************************************************************************
	 * CONCATENACAO PRINCIPAL DO SELECT A SER EXECUTADO PARA O RELATORIO        *
	 ****************************************************************************/

	// $strselectfields = "select idnf, empresa, contaitem, tipoprodserv, qtd, un, descr, vlrlote, valor, rateio";
	// $_REQUEST['idobjeto'] = 159;
	 //$_REQUEST['tipoobjeto'] = 'sgdepartamento';
if($_REQUEST['idempresa']){
	$clausorg2 .= " and a.idempresa in ( ".$_REQUEST['idempresa'].") ";
}


if($_REQUEST['idtipoprodserv']){
	$clausorg2 .= " and a.idtipoprodserv in ( ".$_REQUEST['idtipoprodserv'].") ";
}



$vw8despesas="	";



$_sqlresultado =$strselectfields." from vwrep_360 where 1 ";
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
		<a onclick="gerarCsvPrint('Movimentacao lotes - Insumos')" class="btbr20 no-print" id="btn_export" href="#">  Download .CSV</a>
		</td>
		
	</tr>
	<tr>
		<td class="subheader"><h2><?=($_rep);?></h2>
		(<?=$strs?>)</td>
	</tr>
	</table>
	<br>

<?
	$sqlpd="select p.idprodserv,p.descr,p.codprodserv,p.un,t.tipoprodserv as subcategoria,c.contaitem as categoria
				from prodserv p
				left join tipoprodserv t on(t.idtipoprodserv=p.idtipoprodserv)
				left join prodservcontaitem i on(i.idprodserv=p.idprodserv and i.status='ATIVO')
				left join contaitem c on(c.idcontaitem=i.idcontaitem)
				where  p.idprodserv in (".$_REQUEST['idprodserv'].")  group by p.idprodserv";
	$respd = d::b()->query($sqlpd);
	while($rowpd = mysql_fetch_assoc($respd)){

?>

	<div class="normal" style="
    BACKGROUND: #aaa;
    margin: 20px 0px;
    padding: 20px; font-size:16px
"><?=$rowpd['descr']?> - <?=$rowpd['categoria']?>/<?=$rowpd['subcategoria']?></div>
<?
	/*
	 * MONTA O CABECALHO
	 */
	$conteudoexport;// guarda o conteudo para exportar para csv

	$conteudoexport='"";"NFE";"QTD";"UN";"PRODUTO";"VALOR ITEM";"RECEBIMENTO";"VALOR"';// substitui por valores padrão
	$conteudoexport.="\n";//QUEBRA DE LINHA NO CONTEUDO CSV
	

	/*
	 * Variaveis para cabecalho do report
	 */
	$strpagini = "\n<fieldset class='fldsheader'><legend>Impressão ".$_nomeimpressao."</legend></fieldset>";
	$strtabini = "\n<table  class='normal table-striped'>";


    $sqlit="WITH RECURSIVE week_dates AS (
				SELECT 
					DATE_SUB(DATE('".$dataInicial."'), INTERVAL (WEEKDAY(DATE('".$dataInicial."')) + 1) % 7 DAY) AS week_start, -- Início no domingo
					DATE_ADD(DATE_SUB(DATE('".$dataInicial."'), INTERVAL (WEEKDAY(DATE('".$dataInicial."')) + 1) % 7 DAY), INTERVAL 6 DAY) AS week_end, -- Fim no sábado
					WEEK(DATE('".$dataInicial."'), 0) AS week_number -- Número da semana dentro do ano, com a semana iniciando no domingo
				UNION ALL
				SELECT 
					DATE_ADD(week_start, INTERVAL 7 DAY) AS week_start,
					DATE_ADD(week_end, INTERVAL 7 DAY) AS week_end,
					WEEK(DATE_ADD(week_start, INTERVAL 7 DAY), 0) AS week_number -- Atualiza o número da semana dentro do ano
				FROM week_dates
				WHERE week_start <=  '".$dataFinal."'
			)
            SELECT 
                week_number,
                week_start,
                week_end
            FROM 
                week_dates
            WHERE 
                week_start <= '".$dataFinal."'
            ORDER BY 
                week_start";
    $resit = d::b()->query($sqlit);

$strtabheader2 ="<thead>
<tr class='header'><td class='header' style='white-space: nowrap; text-align:left'>Unidade</td><td class='header' style='white-space: nowrap; text-align:left'>Tipo</td>";
    $li=0;
    while($rit = mysql_fetch_assoc($resit)){   
        //$exdata = explode('-',$rit['week_start']);
  
        $strtabheader2 =  $strtabheader2."<td  class='header' style='white-space: nowrap; text-align:left' title='".$rit['week_start']." - ".$rit['week_end']."'>Semana ".$rit['week_number']."</td>";
        $arrdti[$li]=$rit['week_start'];
        $arrdtf[$li]=$rit['week_end'];
        $li++;
    }
    $strtabheader2= $strtabheader2."</tr></thead>";


	/*
	 * MONTA A TABELA
	 */
	$_ilinha = 0;//armazena o ttotal de registros
	$_ilinhaquebra = 0;//armazena parialmente o numero de registros se houver quebra automatica configurada
	$_graphLinha = 0;
	$strnewpage = "<span class='newreppage'></span>";

	if(!empty($_REQUEST['idunidade'])){
		$_strunidade= " idunidade in(".$_REQUEST['idunidade'].")";
	}else{
		$_strunidade=" idtipounidade in (3,8,5,21,12,7,2,5,11,13) and status ='ATIVO' and primtipounidade='Y' ";
	}

    $sqlun="select idunidade,unidade from unidade where idempresa in (".$_REQUEST['idempresa'].") and ".$_strunidade." ";
	//$sqlun="select idunidade,unidade from unidade where idempresa in (".$_REQUEST['idempresa'].") and  idtipounidade in (3) and status ='ATIVO' and primtipounidade='Y'";
    $_resun = d::b()->query($sqlun);

    echo $strpagini;
    echo $strtabini;
    echo $strtabheader2;
	$descarte=0;
	$resdesc='';
	$union='';

    $linha=0;
    while ($_rowun = mysql_fetch_assoc($_resun)){
        if($linha>0){
?>
            <tr style='background-color: #fff; border: none;'>
                <td style='background-color: #fff; border: none;' colspan="10"></td>
            </tr>
<?
        }
        $linha++;
		/*
		SELECT 
     round(ifnull(i.valor,'0.00'),2) as valor
FROM
    etl e join etlitem i on(i.idetl =e.idetl and i.idobjeto = 11  -- idprodserv
    )
WHERE
    e.idetlconf = 9   
    and  e.criacao = '2024-10-01'
    and  e.objeto ='vwLoteEstoque'    
    and  e.idobjeto =13 -- idunidade
		*/
       
  ?>
        <tr class="res">
        <td><?=$_rowun['unidade']?></td>
        <td>Estoque</td>
<?
    for ($x = 0; $x < $li; $x++) {

        $_sqldatae = " e.criacao ='" .$arrdtf[$x]. "'";	

        //estoque
        $sce="SELECT 
					round(ifnull(i.valor,'0.00'),2) as valor
				FROM
					etl e join etlitem i on(i.idetl =e.idetl and i.idobjeto = ".$rowpd['idprodserv']."
					)
				WHERE
					e.idetlconf =13
					and  ".$_sqldatae."
					and  e.objeto ='vwLoteEstoquegeral'    
					and  e.idobjeto =".$_rowun['idunidade']." ";
             echo("<!-- estoque ".$sce." -->");
             $rce = d::b()->query($sce);
			 $qtde=mysqli_num_rows($rce);
             $wce=mysqli_fetch_assoc($rce);
			 if($qtde<1){
				$wce['valor']='0.00';
			 }
?>
    <td><?=number_format(tratanumero($wce['valor']), 2, ',', '.')?> - <?=$rowpd['un']?></td>	
<?
    } // FIM LOOP ENTRADA
?>
    	</tr>
        <tr class="res">
        <td><?=$_rowun['unidade']?></td>
        <td>Entrada</td>
<?
    for ($x = 0; $x < $li; $x++) {

        $_sqldata = " between '" .$arrdti[$x]. "' and '" .$arrdtf[$x]. "'";	

        //CREDITO
        $sc="select round(ifnull(sum(qtd),0),2) as qtd,round(ifnull(sum(valor),0),2) as valor  from (
            select  sum((c.qtdc*i.qtd)/l.qtdprod) AS qtd, sum(((c.qtdc*i.qtd)/l.qtdprod)*(i.valorun)) as valor
             from lotecons c join lotefracao f on(f.idlotefracao = c.idlotefracao and f.idunidade = ".$_rowun['idunidade'].")
            	 	join lote l on(l.idlote=f.idlote  and f.idunidade !=l.idunidade)
              	 	join loteitem i on(l.idlote=i.idlote) and i.idprodserv in (".$rowpd['idprodserv'].")
			where c.criadoem ".$_sqldata."
				and c.qtdc >0 and c.status!='INATIVO'
				and c.tipoobjeto != 'lote'
				and c.idempresa in ( ".$_REQUEST['idempresa'].") and c.idobjeto is not null
			union
				select  sum((f.qtdini*i.qtd)/l.qtdprod) as qtd,sum(((f.qtdini*i.qtd)/l.qtdprod)*(i.valorun)) as valor
				from  lotefracao f 
						join lote l on(l.idlote=f.idlote and f.idunidade!=l.idunidade)
					 	join loteitem i on(l.idlote=i.idlote) and i.idprodserv in (".$rowpd['idprodserv'].")
				where f.criadoem ".$_sqldata."
					and f.idunidade  =  ".$_rowun['idunidade']."
					and f.idempresa  in ( ".$_REQUEST['idempresa'].") 
			union
            select sum(f.qtdini) as qtd,sum(f.qtdini*l.vlrlote) as valor from lotefracao f 
				join lote l on(l.idlote=f.idlote and l.status !='CANCELADO' and l.idprodserv in (".$rowpd['idprodserv'].") and l.idempresa in (".$_REQUEST['idempresa']."))
			where  f.idunidade =  ".$_rowun['idunidade']."  
				and f.criadoem  ".$_sqldata." 
			 union
			 select sum(c.qtdc) as qtd,sum(c.qtdc*l.vlrlote) as valor from lotecons c
					join lotefracao f on(f.idlotefracao = c.idlotefracao and f.idunidade  = ".$_rowun['idunidade'].")
			 		join lote l on(l.idlote=f.idlote and l.status !='CANCELADO' and l.idprodserv in (".$rowpd['idprodserv'].")  and f.idunidade !=l.idunidade)
				where c.criadoem ".$_sqldata." 
					and c.status!='INATIVO'
					and c.tipoobjeto != 'lote'
					and c.qtdc > 0
					and c.idempresa in ( ".$_REQUEST['idempresa'].")  and c.idobjeto is not null
			 ) as u";
             echo("<!-- credito ".$sc." -->");
             $rc = d::b()->query($sc);
             $wc=mysqli_fetch_assoc($rc);
?>
    <td><?=number_format(tratanumero($wc['qtd']), 2, ',', '.')?> - <?=$rowpd['un']?></td>	
<?
    } // FIM LOOP ENTRADA
?>
    </tr>
    <tr class="res">
        <td><?=$_rowun['unidade']?></td>
        <td>Saída</td>
<?    
    for ($x = 0; $x < $li; $x++) {

        $_sqldata = " between '" .$arrdti[$x]. "' and '" .$arrdtf[$x]. "'";	

		$sd="select round(ifnull(sum(qtd),0),2) as qtd, round(ifnull(sum(valor),0),2) as valor  from (
			select sum((c.qtdd*i.qtd)/l.qtdprod) as qtd,sum(((c.qtdd*i.qtd)/l.qtdprod)*(i.valorun)) as valor
			from lotecons c 
				join lotefracao f on(f.idlotefracao = c.idlotefracao and f.idunidade  = ".$_rowun['idunidade'].")
				join lote l on(l.idlote=f.idlote)
			 	join loteitem i on(l.idlote=i.idlote) and i.idprodserv in (".$rowpd['idprodserv'].")
			where c.criadoem ".$_sqldata." 
				and c.status!='INATIVO'
				and c.tipoobjeto != 'lote'
				and c.idempresa  in ( ".$_REQUEST['idempresa'].")  and c.idobjeto is not null
			union  
			select sum(c.qtdd) as qtd,sum(c.qtdd*l.vlrlote) as valor from lotecons c
				join lotefracao f on(f.idlotefracao = c.idlotefracao and f.idunidade  = ".$_rowun['idunidade'].")
			 	join lote l on(l.idlote=f.idlote and l.status !='CANCELADO' and l.idprodserv in (".$rowpd['idprodserv']."))
			where c.criadoem ".$_sqldata." 
				and c.status!='INATIVO'
				and c.tipoobjeto != 'lote'
				and c.idempresa in ( ".$_REQUEST['idempresa'].")  and c.idobjeto is not null) as u;";

			echo("<!-- debito ".$sd." -->");
			$rd = d::b()->query($sd);
			$wd=mysqli_fetch_assoc($rd);
    ?>

    <td><?=number_format(tratanumero($wd['qtd']), 2, ',', '.')?> - <?=$rowpd['un']?></td>		
<?
    }// fim loop saida
?>
    </tr>
    <tr class="res">
        <td><?=$_rowun['unidade']?></td>
        <td>Descarte</td>
<?    

    for ($x = 0; $x < $li; $x++) {

        $_sqldata = " between '" .$arrdti[$x]. "' and '" .$arrdtf[$x]. "'";	
		
			$sdes="select round(ifnull(sum(qtd),0),2) as qtd,round(ifnull(sum(valor),0),2) as valor 
			 from (
				select sum((c.qtdd*i.qtd)/l.qtdprod) as qtd,sum(((c.qtdd*i.qtd)/l.qtdprod)*(i.valorun)) as valor
				from lotecons c 
					join lotefracao f on(f.idlotefracao = c.idlotefracao and f.idunidade  = ".$_rowun['idunidade'].")
					join lote l on(l.idlote=f.idlote)
				 	join loteitem i on(l.idlote=i.idlote) and i.idprodserv in (".$rowpd['idprodserv'].")
				where c.criadoem ".$_sqldata." 
					and c.status!='INATIVO'
					and c.idempresa  in ( ".$_REQUEST['idempresa'].")  and c.idobjeto is  null
				union  
				select sum(c.qtdd) as qtd,sum(c.qtdd*l.vlrlote) as valor
				 from lotecons c
					join lotefracao f on(f.idlotefracao = c.idlotefracao and f.idunidade  = ".$_rowun['idunidade'].")
				 	join lote l on(l.idlote=f.idlote and l.status !='CANCELADO' and l.idprodserv in (".$rowpd['idprodserv']."))
				where c.criadoem ".$_sqldata." 
					and c.status!='INATIVO'
					and c.idempresa in ( ".$_REQUEST['idempresa'].")  and c.idobjeto is  null) as u;";
	
				echo("<!-- descarte ".$sdes." -->");
				$rdes = d::b()->query($sdes);
				$wdes=mysqli_fetch_assoc($rdes);

			
?>  

        	
       
		<td><?=number_format(tratanumero($wdes['qtd']), 2, ',', '.')?> - <?=$rowpd['un']?></td>		
<?
    }// fim loop descarte
?>
	</tr>
<?
	
    }
?>
 	
 </table>

 <br>
 <p>
<?


	}
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


	?>
</table></td></tr></table></td></tr></table></td></tr></table></td></tr></table></td></tr></table>

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

	
    function gerarCsvPrint( tituloCsv ) {

		debugger;


if($(".normal").is(":visible")){
	var CsvContent = "";
	var virg = "";

	$(".normal").find("tr.res, tr.header").each((i, o) => {
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

</script>
