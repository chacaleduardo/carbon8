<?
error_reporting(E_ALL);
require_once("../inc/php/validaacesso.php");
if(!empty($_GET["reportexport"])){
    session_cache_expire(1);
    session_cache_limiter("private");
    ob_start();//não envia nada para o browser antes do termino do processamento
}
//ini_set("display_errors","1");
//error_reporting(E_ALL);

################################################## Atribuindo o resultado do metodo GET
$ano 	= $_GET["ano"];
$mes 	= $_GET["mes"];

//$idagencia 		= $_GET["idagencia"];
$idprodserv=$_GET["idprodserv"];
$pesquisa = 'UNIDADE';
$status = $_GET["status"];
$idsgdepartamento = $_GET["idsgdepartamento"];
$pendente = $_GET["pendente"];

if(empty($_GET["idempresa"])){
	$idempresa = $_GET["_idempresa"];
} else {
	$idempresa = $_GET["idempresa"];
}

if(empty($status)){
    $status='PENDENTE';
    $custeado='N';
}elseif($status=='PENDENTE'){
    $custeado='N';
}elseif($status=='PENDENTETODOS'){
    $custeado='N';
}else{
    $custeado='Y';
}


if(!empty($mes) and !empty($ano) and $status!='PENDENTETODOS'){
    $sqldata="SELECT DATE(CONCAT('".$ano."', '-', ".$mes.", '-01')) AS dataini, LAST_DAY(DATE(CONCAT('".$ano."', '-', ".$mes.", '-01'))) AS datafim";
    $resdata =  d::b()->query($sqldata) or die("Falha ao montar as datas (P): " .mysqli_error(d::b()). "<p>SQL: $sqldata");
    $rowData=mysqli_fetch_assoc($resdata);

    $dataini = $rowData['dataini'];
    $datafim = $rowData['datafim'];
	$vencimento_1=dma($rowData['dataini']);
	$vencimento_2=dma($rowData['datafim']);

    $strdata = " between  '".$dataini." 00:00:00'  and  '".$datafim." 23:59:59'";

}elseif(!empty($mes) and !empty($ano) and $status=='PENDENTETODOS'){
    $strdata = " <  '".$datafim." 23:59:59'";
}





$vw8despesas = 
" SELECT 
`a`.`tiponf` AS `tiponf`,
`a`.`idcontaitem` AS `idcontaitem`,
`a`.`contaitem` AS `contaitem`,
`a`.`idtipoprodserv` AS `idtipoprodserv`,
`a`.`tipoprodserv` AS `tipoprodserv`,
`a`.`cor` AS `cor`,
`a`.`previsao` AS `previsao`,
`a`.`faturamento` AS `faturamento`,
`a`.`ordem` AS `ordem`,
`a`.`descricao` AS `descricao`,
`a`.`idnf` AS `idnf`,
`a`.`dtemissao` AS `dtemissao`,
`a`.`idempresa` AS `idempresa`,
`a`.`idnfitem` AS `idnfitem`,
`a`.`qtd` AS `qtd`,
`a`.`un` AS `un`,
`a`.`total` AS `total`,
`a`.`nnfe` AS `nnfe`,
`a`.`vlritem` AS `vlritem`,
ROUND((IF((`rid`.`valor` IS NOT NULL),
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
CASE  
    WHEN '".$pesquisa."'='CENTROCUSTO' THEN ct.idcentrocusto
    ELSE `rid`.`idobjeto` END AS `idobjeto`,
`u`.`idunidade` AS `idunidade`,
`u`.`unidade` AS `unidade`,
CASE WHEN u.tipocusto ='CI' THEN 'CUSTO INDIRETO'
     ELSE 'CUSTO DIRETO' END AS tipocusto,
IFNULL(`e`.`idempresa`,  `a`.`idempresa`) AS `idempresarateio`,
IFNULL(`e`.`empresa`, `a`.`empresa`) AS `siglarateio`,
CASE WHEN '".$pesquisa."'='CENTROCUSTO' THEN ct.centrocusto
      WHEN  `u`.`unidade` IS NULL THEN   `a`.`empresa`
      WHEN  `u`.`unidade` IS NOT NULL THEN  `u`.`unidade` END AS empresarateio,
IFNULL(`e`.`corsistema`,
        `a`.`corsistema`) AS `corsistema`
FROM
((((((
select 
 'resultado' AS `tiponf`,
        `c`.`contaitem` AS `contaitem`,
        `c`.`idcontaitem` AS `idcontaitem`,
        `c`.`cor` AS `cor`,
        `c`.`somarelatorio` AS `somarelatorio`,
        `c`.`previsao` AS `previsao`,
        `c`.`faturamento` AS `faturamento`,
        `c`.`ordem` AS `ordem`,
        `ps`.`descr` AS `descricao`,
        `r`.`idresultado` AS `idnf`,
		r.dataconclusao AS `dtemissao`,
        `r`.`idempresa` AS `idempresa`,
        `e`.`empresa` AS `empresa`,
        `e`.`corsistema` AS `corsistema`,       
        `p`.`idtipoprodserv` AS `idtipoprodserv`,
        `r`.`idresultado` AS `idnfitem`,
        `r`.`quantidade` AS `qtd`,
        'TESTE' AS `un`,
        ((r.custo) * -(1)) AS `total`,
        `p`.`tipoprodserv` AS `tipoprodserv`,
        concat(a.idregistro,'/',a.exercicio) AS `nnfe`,
        (r.custo) AS `vlritem`        
    from resultado r join prodserv ps on(ps.idprodserv=r.idtipoteste)
    join amostra a on(a.idamostra=r.idamostra)
    JOIN `tipoprodserv` `p` ON (`p`.`idtipoprodserv` = `ps`.`idtipoprodserv`)
    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv and pc.status='ATIVO')
    JOIN `contaitem` `c` ON (`c`.`idcontaitem` = `pc`.`idcontaitem`)
    JOIN `empresa` `e` ON (`e`.`idempresa` = `r`.`idempresa`)
    JOIN `rateioitem` `ri` ON (`ri`.`idobjeto` = `r`.`idresultado`
    AND `ri`.`tipoobjeto` = 'resultado')
    JOIN `rateioitemdest` `rid` ON (`rid`.`idrateioitem` = `ri`.`idrateioitem` )
    JOIN `unidade` `u` ON (`u`.`idunidade` = `rid`.`idobjeto`
    AND `rid`.`tipoobjeto` = 'unidade')
    where r.idempresa = ".$idempresa." 
    and r.status != 'CANCELADO' 
    and r.custo > 0
    and r.dataconclusao ".$strdata."
UNION ALL
SELECT 
        'nfitem' AS `tiponf`,
        `c`.`contaitem` AS `contaitem`,
        `c`.`idcontaitem` AS `idcontaitem`,
        `c`.`cor` AS `cor`,
        `c`.`somarelatorio` AS `somarelatorio`,
        `c`.`previsao` AS `previsao`,
        `c`.`faturamento` AS `faturamento`,
        `c`.`ordem` AS `ordem`,
        IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
        `n`.`idnf` AS `idnf`,
        `n`.`dtemissao` AS `dtemissao`,
        `n`.`idempresa` AS `idempresa`,
        `e`.`empresa` AS `empresa`,
        `e`.`corsistema` AS `corsistema`,      
        `p`.`idtipoprodserv` AS `idtipoprodserv`,
        `i`.`idnfitem` AS `idnfitem`,
        `i`.`qtd` AS `qtd`,
        IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
        (((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - `n`.`frete`)) * `n`.`frete`)) ) ) * -(1)) AS `total`,
        `p`.`tipoprodserv` AS `tipoprodserv`,
        `n`.`nnfe` AS `nnfe`,
        `i`.`vlritem` AS `vlritem`
    FROM
    `nf` `n`
    JOIN `nfitem` `i` ON (`i`.`idnf` = `n`.`idnf`
        AND `i`.`nfe` = 'Y')
    JOIN `tipoprodserv` `p` ON (`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)
    JOIN `contaitem` `c` ON (`c`.`idcontaitem` = `i`.`idcontaitem`)
    LEFT JOIN `prodserv` `ps` ON (`ps`.`idprodserv` = `i`.`idprodserv`)
    JOIN `empresa` `e` ON (`e`.`idempresa` = `n`.`idempresa`)
    JOIN `rateioitem` `ri` ON (`ri`.`idobjeto` = `i`.`idnfitem`
    AND `ri`.`tipoobjeto` = 'nfitem')
    JOIN `rateioitemdest` `rid` ON (`rid`.`idrateioitem` = `ri`.`idrateioitem` )
    JOIN `unidade` `u` ON (`u`.`idunidade` = `rid`.`idobjeto`
    AND `rid`.`tipoobjeto` = 'unidade')
    WHERE
     `n`.`tiponf` NOT IN ('S' , 'R')
       
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."       
        and n.status='CONCLUIDO'
         and `i`.`qtd` > 0
        and rid.alteradoem ".$strdata."
      
UNION ALL
SELECT 
        'nfitem' AS `tiponf`,
        `c`.`contaitem` AS `contaitem`,
        `c`.`idcontaitem` AS `idcontaitem`,
        `c`.`cor` AS `cor`,
        `c`.`somarelatorio` AS `somarelatorio`,
        `c`.`previsao` AS `previsao`,
        `c`.`faturamento` AS `faturamento`,
        `c`.`ordem` AS `ordem`,
        IFNULL(`ps`.`descr`, `i`.`prodservdescr`) AS `descricao`,
        `n`.`idnf` AS `idnf`,
        `n`.`dtemissao` AS `dtemissao`,
        `n`.`idempresa` AS `idempresa`,
        `e`.`empresa` AS `empresa`,
        `e`.`corsistema` AS `corsistema`,       
        `p`.`idtipoprodserv` AS `idtipoprodserv`,
        `i`.`idnfitem` AS `idnfitem`,
        `i`.`qtd` AS `qtd`,
        IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
        ((((IFNULL(`i`.`total`, 0) * (`n`.`total` / ifnull(n.subtotal,n.total))) ) ) * -(1)) AS `total`,
        `p`.`tipoprodserv` AS `tipoprodserv`,
        `n`.`nnfe` AS `nnfe`,
        `i`.`vlritem` AS `vlritem`
    FROM
        `nf` `n`
    JOIN `nfitem` `i` ON (`i`.`idnf` = `n`.`idnf`
        AND `i`.`nfe` = 'Y')
    JOIN `tipoprodserv` `p` ON (`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)
    JOIN `contaitem` `c` ON (`c`.`idcontaitem` = `i`.`idcontaitem`)
    LEFT JOIN `prodserv` `ps` ON (`ps`.`idprodserv` = `i`.`idprodserv`)
    JOIN `empresa` `e` ON (`e`.`idempresa` = `n`.`idempresa`)
    JOIN `rateioitem` `ri` ON (`ri`.`idobjeto` = `i`.`idnfitem`
    AND `ri`.`tipoobjeto` = 'nfitem')
    JOIN `rateioitemdest` `rid` ON (`rid`.`idrateioitem` = `ri`.`idrateioitem`)
    JOIN `unidade` `u` ON (`u`.`idunidade` = `rid`.`idobjeto`
    AND `rid`.`tipoobjeto` = 'unidade')

    WHERE
     `n`.`tiponf` IN ('S' , 'R')    
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."      
        and n.status='CONCLUIDO'
        and `i`.`qtd` > 0
        and n.dtemissao  ".$strdata."  
) `a`
 JOIN `rateioitem` `ri` ON (((`ri`.`idobjeto` = `a`.`idnfitem`)
    AND (`ri`.`tipoobjeto` =  `a`.`tiponf`))))
 JOIN `rateioitemdest` `rid` ON ((`rid`.`idrateioitem` = `ri`.`idrateioitem`)))
 JOIN `unidade` `u` ON (((`u`.`idunidade` = `rid`.`idobjeto`)
    AND (`rid`.`tipoobjeto` = 'unidade')  ))
LEFT JOIN centrocusto ct on(ct.idcentrocusto = u.idcentrocusto)   
    )
LEFT JOIN `empresa` `e` ON ((`e`.`idempresa` = `u`.`idempresa`))))
WHERE
(`a`.`somarelatorio` = 'Y'  ) group by idrateioitemdest";


$sql="select  
  tipo,idempresa,qtd,un,contaitem,idcontaitem,idtipo,idrateio,idrateioitem,idrateioitemdest,idnf,nnfe,idobjeto,tipoobjeto,
   idtipoprodserv,tipoprodserv,descr,vlrlote,sum(rateio) as rateio,valor,empresa,dtemissao,corsistema,rateado, idempresarateio as idempresarateio,siglarateio,tipocusto,unidade
from (


SELECT 
	tiponf AS tipo,
	idempresa,
	qtd,
	un,
	contaitem,
	idcontaitem,
	idnfitem AS idtipo,
	idrateio,
	idrateioitem,
	idrateioitemdest,
	idnf,
	nnfe,
	ifnull(idobjeto,idempresa) as idobjeto,
	ifnull(tipoobjeto,'aratiar') as tipoobjeto,
	idtipoprodserv,
	tipoprodserv,
	descricao AS descr,
	vlritem AS vlrlote,
	rateio AS rateio,
	vlrrateio AS valor,
	empresarateio AS empresa, 
	 dtemissao,                                
	corsistema,
	rateado,
	idempresarateio,
	siglarateio,
	tipocusto,
	unidade
FROM
(".$vw8despesas.") v
WHERE
rateado = 'Y'
				 
) as u  group by idobjeto
order by unidade,idempresarateio,tipoobjeto,empresa,idobjeto,contaitem,tipoprodserv,descr,dtemissao";

?>
<html>
<head>
<title>Sislaudo - Rateio Custo</title>

</head>

<link href="../inc/css/rep.css" media="all" rel="stylesheet" type="text/css">
<style>
    
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
</style>
<script language="javascript">

var reloadpage = true;//Utilizado para informar à req.xml para efetuar refresh APÓS a respota
var xmlonreadystate = "xmldocU=xmldoc.toUpperCase();if(xmldocU.indexOf('ERR')>0){alert(xmldoc);}";


</script>
<style data-cke-temp="1" type="text/css" media="screen">

a.btbr20{
	display: block;
}

</style>
<body>

<?
if($_GET){

?>

<table class="tbrepheader">
	
	<tr>
		<td rowspan="3" style="width:200;">
                    <!--
                    <img src="../img/repheader.png">
                    -->
                </td>
		<td class="header">Relatório Rateio de Custos</td>
		<td><a class="btbr20" href="<?=$_SERVER['REQUEST_URI']?>&reportexport=csv" target="_blank">Exportar .csv</a></td>
	</tr>
	<tr>
		<td class="subheader">(Período entre <?=$vencimento_1?> e <?=$vencimento_2?>)</td>
	</tr>	
</table>
<br>
<table class="normal" style="font-size: 10px;">
	<tr class="header">
		<td align="center">Unidade</td>
		<td align="center">Custo Direto</td>
		<td align="center">Custo Indireto</td>
	</tr>
	<?



	$ip =9999;//variavel para o form
	$res = d::b()->query($sql) or die("Falha ao buscar items  " . mysqli_error(d::b()) . "<p>SQL: $sql");
	$tci=0; $tcd=0; $total=0;
	while ($row = mysqli_fetch_assoc($res)){
		$total=$total+$row["rateio"];
		if($row["tipocusto"]=="CUSTO INDIRETO"){
			$ci=$row["rateio"];
			$cd=0;
			$tci=$tci+$row["rateio"];
		}else{
			$ci=0;
			$cd=$row["rateio"];
			$tcd=$tcd+$row["rateio"];
		}
	
?>	
	
		<tr class="respreto">
			<td><?=$row["unidade"]?></td>
			<td align='right'><?=number_format(tratanumero((double)$cd), 2, ',', '.')?></td>
			<td align='right'><?=number_format(tratanumero((double)$ci), 2, ',', '.')?></td>
		</tr>
<?	
 
                
	}//while ($row = mysqli_fetch_array($res)){
?>
	<tr class="respreto">
		<td>Valor Total: <b><?=number_format(tratanumero((double)$total), 2, ',', '.')?></b></td>
		<td align='center'>Total Custo Direto: <b><?=number_format(tratanumero((double)$tcd), 2, ',', '.')?></b></td>
		<td align='center'>Total Custo Indireto: <b><?=number_format(tratanumero((double)$tci), 2, ',', '.')?></b></td>
	</tr>
</table>
<?
}//if($_GET){
?>
</body>
</html>

