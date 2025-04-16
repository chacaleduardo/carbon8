<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "rateio";
$pagvalcampos = array(
	"idrateio" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from rateio where idrateio = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$pesquisa='UNIDADE';

$idempresa =$_1_u_rateio_idempresa;
$ano 	= $_1_u_rateio_ano;
$mes 	= $_1_u_rateio_mes;

global $totalempresa;
global $totaloempresa;

$custeado='N';



if($_1_u_rateio_status=='FECHADO'){


    $sqldata="SELECT DATE(CONCAT('".$ano."', '-', ".$mes.", '-01')) AS dataini, LAST_DAY(DATE(CONCAT('".$ano."', '-', ".$mes.", '-01'))) AS datafim";
    $resdata =  d::b()->query($sqldata) or die("Falha ao montar as datas (P): " .mysqli_error(d::b()). "<p>SQL: $sqldata");
    $rowData=mysqli_fetch_assoc($resdata);

    $dataini = $rowData['dataini'];
    $datafim = $rowData['datafim'];

    $andfechado=" and ri.idrateio=".$_1_u_rateio_idrateio." ";
    $strdatadataconclusao = " ";
    $strdataalteradoem = " ";
    $strdatadtemissao = " ";

}elseif(!empty($mes) and !empty($ano) ){
    $sqldata="SELECT DATE(CONCAT('".$ano."', '-', ".$mes.", '-01')) AS dataini, LAST_DAY(DATE(CONCAT('".$ano."', '-', ".$mes.", '-01'))) AS datafim";
    $resdata =  d::b()->query($sqldata) or die("Falha ao montar as datas (P): " .mysqli_error(d::b()). "<p>SQL: $sqldata");
    $rowData=mysqli_fetch_assoc($resdata);

    $dataini = $rowData['dataini'];
    $datafim = $rowData['datafim'];

    $strdatadataconclusao = " and r.dataconclusao between  '".$dataini." 00:00:00'  and  '".$datafim." 23:59:59'";
    $strdataalteradoem = " and rid.alteradoem between  '".$dataini." 00:00:00'  and  '".$datafim." 23:59:59'";
    $strdatadtemissao = " and n.dtemissao between  '".$dataini." 00:00:00'  and  '".$datafim." 23:59:59'";


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
 rid.situacao,
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
    JOIN `rateioitemdest` `rid` ON (`rid`.`idrateioitem` = `ri`.`idrateioitem` and rid.custeado='".$custeado."')
    JOIN `unidade` `u` ON (`u`.`idunidade` = `rid`.`idobjeto`
    AND `rid`.`tipoobjeto` = 'unidade')
    where r.idempresa = ".$idempresa." 
    and r.status != 'CANCELADO' 
    and r.custo > 0
    ".$andfechado."
    ".$strdatadataconclusao."
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
    JOIN `rateioitemdest` `rid` ON (`rid`.`idrateioitem` = `ri`.`idrateioitem` and rid.custeado='".$custeado."')
    JOIN `unidade` `u` ON (`u`.`idunidade` = `rid`.`idobjeto`
    AND `rid`.`tipoobjeto` = 'unidade')
    WHERE
     `n`.`tiponf` NOT IN ('S' , 'R')
        AND n.geracontapagar ='Y'
        AND n.tipocontapagar = 'D'       
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."       
        and n.status='CONCLUIDO'
         and `i`.`qtd` > 0
         ".$andfechado."
         ".$strdataalteradoem."
      
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
    JOIN `rateioitemdest` `rid` ON (`rid`.`idrateioitem` = `ri`.`idrateioitem` and rid.custeado='".$custeado."')
    JOIN `unidade` `u` ON (`u`.`idunidade` = `rid`.`idobjeto`
    AND `rid`.`tipoobjeto` = 'unidade')

    WHERE
     `n`.`tiponf` IN ('S' , 'R')    
        AND n.geracontapagar ='Y'
        AND n.tipocontapagar = 'D'  
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."      
        and n.status='CONCLUIDO'
        and `i`.`qtd` > 0
        ".$andfechado."
         ".$strdatadtemissao."  
) `a`
 JOIN `rateioitem` `ri` ON (((`ri`.`idobjeto` = `a`.`idnfitem`)
    AND (`ri`.`tipoobjeto` =  `a`.`tiponf`))))
 JOIN `rateioitemdest` `rid` ON ((`rid`.`idrateioitem` = `ri`.`idrateioitem`) and rid.custeado='".$custeado."' and rid.situacao not in ('ALMOXARIFADO','INVESTIMENTO')))
 JOIN `unidade` `u` ON (((`u`.`idunidade` = `rid`.`idobjeto`)
    AND (`rid`.`tipoobjeto` = 'unidade')  ))
LEFT JOIN centrocusto ct on(ct.idcentrocusto = u.idcentrocusto)   
    )
LEFT JOIN `empresa` `e` ON ((`e`.`idempresa` = `u`.`idempresa`))))
WHERE
(`a`.`somarelatorio` = 'Y' ) group by idrateioitemdest";

$vw8despesasInvestimento =  str_replace( "not in ('ALMOXARIFADO','INVESTIMENTO')",  "='INVESTIMENTO'",  $vw8despesas);



//produto transferido a partir do almoxarifado e produtos manuais
?>
<style>    
i[aria-expanded="true"]{
  color:#e79500;
}
.somatorio_percentual{
    float: right;
    width: 60px;
    text-align: right;
    background: #ffffffa1;
    margin: 0px 4px;
    padding: 2px 8px;
    border-radius: 8px;
    font-weight: normal;
    font-size:9px;
    
}
.somatorio_percentual_faturamento{
    float: right;
    width: 60px;
    text-align: right;
    background: #ffffffa1;
    margin: 0px 4px;
    padding: 2px 8px;
    border-radius: 8px;
    font-weight: normal;
    font-size:9px;
    
}
.somatorio_valor{
    float: right;
    width: 100px;
    text-align: right;
    background: #ffffffa1;
    margin: 0px 4px;
    padding: 2px 8px;
    border-radius: 8px;
    font-weight: normal;
    font-size:9px;
}
.agrupamentorateio{
    
    margin: 10px 0;
    border: 1px solid #eee;
    border-left-width: 5px;
    border-radius: 3px;
    border-color: silver;
   
}

button.btn.btn-dafault.active{
    background-color:#E79500;
    color:#fff !important;
}
   .divbody   th {
        font-size: 12px;
       
    }  
    td {
        font-size: 10px;
    }
    .divbody .panel-heading  {
        font-size: 12px;
        text-transform: uppercase !important; 
         color:black !important;
    }

    .panel{
        margin:2px !important;
    }
    .panel-body{
        padding-top: 8px !important
    }
    .divtotal{
        border: 20px;
        font-size: 12px;
        color:black !important;
    }
    @media print {
        .ocultar{
            display:none;
        }
        .impressao{
            width: 1000px;
        }
        .fa-arrows-v{
            display:none;
        }
        .cabecalho{
            border-bottom: 1px dotted black;
        }
        .empresarateio{
            border-bottom: 1px dotted black;
        }
    }

div.cabecalho:hover {
	background:#DCDCDC !important;
	color: black ;
	box-shadow: 2px 2px 5px 0px rgba(0,0,0,0.45);
}


.atualizando{
    color: #747474;
    animation: fa-spin 2s infinite linear;
}
    </style>

<style>
        .sticky-div {
            background-color: rgba(230,230, 230, 0.8);
            position: relative;
            width: 100%;
            padding: 10px 0px;
            margin:0px;
            border-radius:6px
        }
         
        .start {
            height: 100px;
        }
         
        .end {
            height: 500px;
        }
    </style>

<?
$year  = ( date("Y"));
$myear  = ( date("Y")-1);
for ($year; $year >= $myear; $year--){
	$arrAno[$year]=$year;
}

$arrMes[1]='Jan';
$arrMes[2]='Fev';
$arrMes[3]='Mar';
$arrMes[4]='Abr';
$arrMes[5]='Mai';
$arrMes[6]='Jun';
$arrMes[7]='Jul';
$arrMes[8]='Ago';
$arrMes[9]='Set';
$arrMes[10]='Out';
$arrMes[11]='Nov';
$arrMes[12]='Dez';

?>

    <div class="panel panel-default" >
        <div class="panel-heading" >   
            <div class="" style="height: 40px;">
                <div style="float: left; margin-top: 10px;">Rateio de Custos <?=$_1_u_rateio_mes?>/<?=$_1_u_rateio_ano?>&nbsp;&nbsp;&nbsp;&nbsp;</div>
                <div style="float:right;">        
                    <span class="dropdown" style="margin-left:12px;">
                        <button class="btn btn-info dropdown-toggle  btn-primary" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="">
                        <span class="fa fa-print"></span>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenu1" style="color:#898989; font-size:11px;text-transform:uppercase;text-transform: uppercase;
                        margin-top: 17px; left: -120px;">
                                
                        <li style="padding: 2px 0px;"><a href="javascript:void(0)" onclick="reldet(1);" data-value="another action" style="color:#898989 !important;">Rateio das Unidades</a></li>
                    </ul>
                    </span>
                </div>
            </div>        
        </div>
        <div class="panel-body" style="min-height: 206px; max-height: 206px; overflow-y: auto; " >
            <table>
            <tr>
                <td  align="right"></td>
                <td>
                    <input name="_1_<?=$_acao?>_rateio_idrateio" type="hidden" value="<?=$_1_u_rateio_idrateio?>" readonly='readonly'>
                    <input name="idempresa" type="hidden" value="<?=$_1_u_rateio_idempresa?>" readonly='readonly'>    
                    <input name="ano" type="hidden" value="<?=$_1_u_rateio_ano?>" readonly='readonly'>    
                    <input name="mes" type="hidden" value="<?=$_1_u_rateio_mes?>" readonly='readonly'>
                    <input name="status" type="hidden" value="PENDENTE" readonly='readonly'>
                    <input value="<?=$dataini?>" type="hidden" name="dataini" id="dataini">    
                    <input value="<?=$datafim?>" type="hidden" name="datafim" id="datafim">             
                </td>                       
            </tr>  
            </table>
            <?
        //desativado vencimento
        if((!empty($dataini) and !empty($datafim)) and !empty($idempresa)){
            $sqlc="select r.idrateiocusto,r.idempresa,r.datainicio,r.datafim,r.stidrateioitemdest,r.valorun,r.valor,r.status,r.criadopor,r.criadoem,r.alteradopor,r.alteradoem,
                        CASE
                            WHEN r.tiporateio = 'CUSTO'  THEN 'Custo de Produção'
                            WHEN r.tiporateio = 'QUANTIDADE'  THEN 'Quantidade produzida'
                            WHEN r.tiporateio = 'VALOR VENDA'  THEN 'Valor de Venda'
                            WHEN r.tiporateio = 'VOLUME'  THEN 'Volume de Produção'
                            ELSE r.tiporateio
                        END as tiporateio,u.unidade,u.tipocusto
                        from rateiocusto r join lotecusto l on(l.idrateiocusto = r.idrateiocusto)
                        left join unidade u on(u.idunidade=l.idobjeto and l.tipoobjeto='unidade')
                         where r.status='ATIVO' and r.idempresa= ".$idempresa." AND r.datainicio = '".$dataini."' and r.datafim ='".$datafim."'  group by r.idrateiocusto order by u.unidade";
            //'QUANTIDADE','Quantidade produzida' union select 'CUSTO','Custo de Produção' union select 'VALOR VENDA','Valor de Venda' union select 'VOLUME','Volume de Produção'
            $resc =  d::b()->query($sqlc); 
            $qtdc=mysqli_num_rows($resc);

            if($qtdc>0){
        ?>
     <div class="panel panel-default" style="border-color: #c5dfad !important ">
        <div class="panel-heading cabecalho" style="background-color: #c5dfad !important">
            <table style="width:100%">
                <tbody><tr>
                <td style="width:95%">
                    <b>Rateios já Atribuídos neste Período</b> 
                </td>
                <td style="width:5%">
                <i style="float:right" class="fa fa-arrows-v fa-2x branco pointer" title="Detalhar" data-toggle="collapse" href="#jacusteado" aria-expanded="" ></i>
                </td>
                </tr>
            </tbody>
        </table>
       </div>
        <div class="panel-body collapse" id="jacusteado"> 
            
            <div class="row" style="">
	            <div class="col-md-12">                
                <table style="width:100%" class="table-striped">
              
                <tr style="font-size:9px">
                    <th >Tipo do Rateio</td>
                    <th>Unidade</td>
                    <th  class='totalempresa nowrap' align="right">Valor Total Atribuido</th>
                    <th>Criado por</th>
                    <th >Criado em</th>
                    <th ></th>
                </tr>
         <? 
                $cd = 0;
                $ci = 0;
                $total = 0;
                while ($rowc=mysqli_fetch_assoc($resc)) {
                    if($rowc['tipocusto']=='CD'){
                        $cd = $cd+$rowc['valor'];
                    }else{
                        $ci = $ci+$rowc['valor'];
                    }                    
                    
                    $total = $total+$rowc['valor'];

            ?>
               
                <tr style="font-size:9px">
                    <td><?=$rowc['tiporateio']?></td>
                    <td><?=$rowc['unidade']?>-<?=$rowc['tipocusto']?></td>
                    <td  class='totalempresa nowrap' align="right"><?=number_format(tratanumero((double)$rowc['valor']), 2, ',', '.')?></td>
                    <td ><?=$rowc['criadopor']?></td>
                    <td ><?=dmahms($rowc['criadoem'])?></td>
                    <td>
                        <a id="cadcliente" class="fa fa-bars pointer hoverazul" stidrateioitemdest=<?=$rowc['stidrateioitemdest']?>  dataini= title="Cadastro de  Cliente" onclick="rateiocusto(this,'<?=$rowc['datainicio']?>','<?=$rowc['datafim']?>',<?=$rowc['idrateiocusto']?>)"></a>
                    </td>
                 </tr>
            <?
                }
        ?>    
                </table>

                </div>
            </div>
            <div class="row">
                <div class="col-md-4"> 
                    Custo Direto: <b><?=number_format(tratanumero((double)$cd), 2, ',', '.')?></b>
                </div>
                <div class="col-md-4" style="text-align: center;"> 
                    Custo Indireto: <b><?=number_format(tratanumero((double)$ci), 2, ',', '.')?></b>
                </div>
                <div class="col-md-4" style="text-align-last: end;"> 
                    Total: <b><?=number_format(tratanumero((double)$total), 2, ',', '.')?></b>
                </div>
            </div>
           </div>
        </div>
           
            <?
            }
        }
?>

        </div>
    </div>

<div class="row sticky-div">
    <div class="col-md-12">
        <button type="button" class="btn btn-dafault hide" style="margin:0px 4px;color:#666;font-size: 8px !important;float:left;border:none" title="Faturamento %" 
        onclick="mostraPercentual(this,'somatorio_percentual_faturamento');">
            <i class="fa fa-eye fa-1x"></i>FATURAMENTO
        </button> 

        <button type="button" class="btn btn-dafault hide" style="margin:0px 4px;color:#666;font-size: 8px !important;float:left;border:none" title="Editar rateio em lote" 
        onclick="mostraPercentual(this,'somatorio_percentual');" >
            <i class="fa fa-eye  fa-1x"></i>DESPESA
        </button> 
        <button id="ratear"  type="button" class="btn btn-primary hide" style="margin:0px 4px;color:#666;font-size: 8px !important;float:right;background-color:#ed0e0e40  !important;border:none" title="Editar rateio em lote" onclick="modalRateio(this,'nfitem','RATEAR');" idrateioitem="<?=$row['idrateioitem']?>" >
            <i class="fa fa-pencil fa-1x"></i>RATEAR
        </button> 
        <button  id="cobrar"  type="button" class="btn btn-primary hidden" style="margin:0px 4px;color:#666;font-size: 8px !important;float:right;background-color:#4878df !important;border:none" title="Cobrar rateio em lote" onclick="modalRateio(this,'emlote','COBRAR');" idrateioitem="<?=$row['idrateioitem']?>" >
            <i class="fa fa-money fa-1x"></i>RATEAR CUSTO
        </button> 
        <button  id="editar"  type="button" class="btn btn-primary hide" style="margin:0px 4px;color:#666;font-size: 8px !important;float:right;background-color:#7dc937  !important;border:none" title="Editar rateio em lote" onclick="modalRateio(this,'emlote','RATEAR');" idrateioitem="<?=$row['idrateioitem']?>" >
            <i class="fa fa-pencil fa-1x"></i>EDITAR
        </button> 
    </div>
</div>
<?
function corpo($res,$tiporel,$idempresa){
    global $totalgeral,$arrvtipo,$arrvcontaitem,$arrvemp,$nfclass,$prodservclass,$totalempresa,$totaloempresa,$custeado;
    global $v_empresa, $v_idobjeto, $v_contaitem, $v_tipoprodserv, $v_total;
    $ires = mysqli_num_rows($res);  
    if($tiporel=='rateio'){
        $back="style='background-color: #b6cbf9 !important'";
        $border ="style='border-color: #b6cbf9 !important '";
        $str="<b>DESPESAS PARA CUSTEIO</b>";
        $class = "emlote";
    }elseif($tiporel=='rateioalm'){
        $back="style='background-color: #c5dfad !important'";
        $border ="style='border-color: #c5dfad !important '";
        $str="<b>INVESTIMENTO</b>";
        $class = "emlote";
    }else{
        $tiporel = 'aratiar';
        $back="style='background-color: #ed0e0e40  !important'";
        $border="style='border-color: #ed0e0e40  !important'";
        $str="<b>Despesas sem Rateio</b>";
        $class = "nfitem";
    }

?>



    <div class="panel panel-default agrupamentorateio " <?=$border?> >
        <div class="panel-heading cabecalho" <?=$back?>>
            <table style="width:100%">
                <tr>
                <td style="width:70%">
                    <?=$str?> 
                </td>
                <td style="width:25%" >
                    <div class='somatorio_valor valor_total_<?=$tiporel?>'>0</div>
                
                    <div class='somatorio_percentual percentual_total_<?=$tiporel?>'>0</div>

                    <div class='hide somatorio_percentual_faturamento percentualfaturamento_total_<?=$tiporel?>'>0</div>
                </td>
                </tr>
            </table>
        </div>
        <div class="panel-body" >
    
<?if($ires>0){
    $vtipo=0;
    $vempresarateio=0;
    $vempresa=0;
   //$arrvemp=array();
   //$arrvtipo=array();
    while($row=mysqli_fetch_assoc($res)){
        if($custeado=='Y'){
            $row['corsistema']='#1a9217';
        }
        
        $i=$i+1;
        if($row['rateado']){
            $totalempresa=$totalempresa+$row['rateio'];
        }else{
            $totaloempresa=$totaloempresa+$row['rateio'];
        }   

        $v_total[$tiporel] += $row['rateio'];
        //Soma Por Empresa
        $v_empresa[$row['idempresarateio'].$tiporel] += $row['rateio'];

        //Soma Por Unidade
        $v_idobjeto[$row['idempresarateio'].$tiporel][$row['idobjeto'].$row['tipoobjeto']] += $row['rateio'];

        //Soma Por Grupo ES
        $v_contaitem[$row['idempresarateio'].$tiporel][$row['idobjeto'].$row['tipoobjeto']][$row['idcontaitem']] += $row['rateio'];

        //Soma Por Tipo Prodserv
        $v_tipoprodserv[$row['idempresarateio'].$tiporel][$row['idobjeto'].$row['tipoobjeto']][$row['idcontaitem']][$row['idtipoprodserv']] += $row['rateio'];
        

        $total=$total+$row['rateio'];

        // INICIO EMPRESA
        if(($idempresarateio === false) or ($row['idempresarateio'] != $idempresarateio )){ 
            if($idempresarateio !== false){  
                echo "</table></div></div></div></div></div></div></div></div>";
            
                $vtipo=0;
            }
            $idobjeto = false;
            $tipoobjeto = false;
            $idcontaitem = false;
            $idtipoprodserv=false;
        ?>        
            <div class="panel panel-default" style="margin-top:8px !important;">
                <div class="panel-heading empresarateio  pointer" style="border-left: 4px solid <?=$row['corsistema'];?>;background:#bbb" >        
                    <table class="pointer 1" style="width:100%">
                        <tr>
                            <td  style="width: 5%">
                            <?if($tiporel!='rateioalm'){?>
                                <input type='checkbox' class="todosinicio pointer <?=$tiporel;?> hide"  title="Selecionar todos os itens do(a)  <?=$row['empresa']?> " onclick="marcarTodos(this)">
                           <?}?>
                            </td>
                            <td style="width: 65%">
                              
                                <div class="col-md-12" style="text-transform: uppercase;">
                                    <?=$row['siglarateio']?> 
                                </div>
                                    
                            </td>
                            <td style="text-align: right;width:25%" >
                                <div class='somatorio_valor valor_empresarateio_<?=$row['idempresarateio'];?><?=$tiporel?>'>0
                                </div>
                    
                                <div  class='somatorio_percentual percentual_empresarateio_<?=$row['idempresarateio'];?><?=$tiporel?>'>0
                                </div>

                                <div  class='hide somatorio_percentual_faturamento percentualfaturamento_empresarateio_<?=$row['idempresarateio'];?><?=$tiporel?>'>0
                                </div>
                            </td>
                            <td style="width:5%">
                                <i style="float:right" class="fa fa-arrows-v fa-2x branco pointer" title="Detalhar" data-toggle="collapse" href="#p_empresarateio_<?=$row['idempresarateio']?><?=$tiporel?>" aria-expanded="" ></i>
                            </td>
                        </tr>
                    </table>        
                </div>
                <div style="padding:4px;background:#ddd" class="panel-body collapse" id="p_empresarateio_<?=$row['idempresarateio']?><?=$tiporel?>" >  
                    
            <?

            $idempresarateio = $row['idempresarateio'];
           
        }

    //FIM EMPRESA    
        
    //INICIO UNIDADE/EMPRESA

        if(($idobjeto === false) or ($row['idobjeto'] != $idobjeto) or ($row['tipoobjeto'] != $tipoobjeto)){ 
            if(($idobjeto !== false)){ 
                echo "</table></div></div></div></div></div></div>";
                $vtipo=0;    
         }
          
            $idcontaitem = false;
            $idtipoprodserv=false; 
           
        ?>        
                    <div class="panel panel-default" style="margin-top:8px !important;">
                        <div class="panel-heading grupoes cabecalho pointer" style="border-left: 4px solid <?=$row['corsistema'];?>;background:#ccc" >        
                            <table class="pointer 2" style="width:100%">
                                <tr>
                                <td  style="width: 5%">
                                <?if($tiporel!='rateioalm'){?>
                                    <input type='checkbox' class="todosinicio pointer <?=$tiporel;?>"  title="Selecionar todos os itens do(a)  <?=$row['empresa']?> " onclick="marcarTodos(this)">
                                <?}?>
                                </td>
                                    <td style="width: 65%">
                                       
                                        <div class="col-md-12" style="text-transform: uppercase;">
                                            <?=$row['empresa']?> - <?=$row['tipocusto']?>
                                        </div>
                                            
                                    </td>
                                    <td style="text-align: right;width:23%" >
                                        <div class='somatorio_valor valor_objeto_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>'>0</div>
                            
                                        <div class='somatorio_percentual percentual_objeto_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>'>0</div>
                                
                                        <div class='hide somatorio_percentual_faturamento percentualfaturamento_objeto_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>'>0</div>
                                    </td>
                                    <td style="width:7%"><i style="float:right" class="fa fa-arrows-v fa-2x branco pointer" title="Detalhar" data-toggle="collapse" href="#p_objeto_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>" aria-expanded="" ></i></td>
                                </tr>
                            </table>        
                        </div>
                        <div style="padding:4px;background:#eee" class="panel-body collapse" id="p_objeto_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>" >  
        

    <?
       $idobjeto = $row['idobjeto'];
       $tipoobjeto = $row['tipoobjeto'];
    }

    //FIM UNIDADE/EMPRESA

    //INICIO GRUPOES
    if(($idcontaitem === false) or ($row['idcontaitem'] != $idcontaitem)){ 
        if(($idcontaitem !== false)){ 
            echo "</table></div></div></div></div>";
        }
        $idtipoprodserv=false;
?>
                            
                            <div class='panel panel-default' style="margin-top:8px !important;">
                                <div class="panel-heading pointer" style="background:#ddd" >    
                                    <table class="pointer 3" style="width:100%">
                                        <tr >
                                            <th style="width: 5%; color:#4e4a4a; "  ></th>
                                            <th title="Tipo do(s) Iten(s)" style="width: 60%; color:#4e4a4a"> 
                                            
                                                <div class="col-md-12">
                                                <?=$row['contaitem']?>
                                                </div>
                                            </th>
                                            <th title="Soma do(s) Iten(s)"   style="width: 25%;text-align: right;  color:#4e4a4a;">
                                                <div class='somatorio_valor valor_contaitem_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>_<?=$row['idcontaitem']?>'>
                                                0</div>                    
                                                <div class='somatorio_percentual percentual_contaitem_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>_<?=$row['idcontaitem']?>'>
                                                0</div>

                                                <div class='hide somatorio_percentual percentualfaturamento_contaitem_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>_<?=$row['idcontaitem']?>'>
                                                0</div>
                                                </th>
                                            <th style="color:#4e4a4a;width: 10%;"><i style="float:right" class="fa fa-arrows-v fa-2x branco pointer" title="Detalhar"  data-toggle="collapse" href="#p_contaitem_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>_<?=$row['idcontaitem']?>" aria-expanded="" ></i></th>
                                        </tr>
                                    </table>
                                </div>
                                <div style="padding:4px;background:#fafafa" class="panel-body collapse" id="p_contaitem_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>_<?=$row['idcontaitem']?>">
<?
        $idcontaitem = $row['idcontaitem'];
       

    }

    //FIM GRUPOES

    //INICIO TIPOITEM

    if($row['idtipoprodserv']!=$idtipoprodserv){
        if(($idtipoprodserv !== false)){
            echo "</table></div></div>";
            $vtipo=0;
            
        }
    ?>
                                    <div class='panel panel-default itens' style="margin-top:8px !important;">
                                        <div class="panel-heading pointer" style="background:#eee" >
                                            <table class="pointer 4" style="width:100%">
                                                <tr >
                                                    <th style="width: 5%" class="emp<?=$row['idobjeto']?>_<?=$row['tipoobjeto']?>">
                                                    <?if($tiporel!='rateioalm'){?>
                                                        <input type='checkbox' class="todos pointer <?=$tiporel;?> hide"  title="Selecionar itens do tipo  <?=$row['tipoprodserv']?>" onclick="marcarTodos(this)">
                                                    <?}?>
                                                    </th>
                                                    <th title="Tipo do(s) Iten(s)" style="width: 55%"> 
                                                        <div class="col-md-12">
                                                            <?=$row['tipoprodserv']?>
                                                        </div>
                                                    </th>
                                                    <th title="Soma do(s) Iten(s)"   style="text-align: right;width: 25%">
                                                        <div class='somatorio_valor valor_tipoprodserv_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>_<?=$row['idcontaitem']?>_<?=$row['idtipoprodserv']?> nowrap'>
                                                        0</div>
                                                    
                                                        <div class='somatorio_percentual percentual_tipoprodserv_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>_<?=$row['idcontaitem']?>_<?=$row['idtipoprodserv']?> nowrap'>
                                                        0</div>

                                                        <div class='hide somatorio_percentual percentualfaturamento_tipoprodserv_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>_<?=$row['idcontaitem']?>_<?=$row['idtipoprodserv']?> nowrap'>
                                                        0</div>
                                                    </th>
                                                    <th style="width: 15%"><i style="float:right" class="fa fa-arrows-v fa-2x branco pointer" title="Detalhar"  data-toggle="collapse" href="#p_tipoprodserv_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>_<?=$row['idcontaitem']?>_<?=$row['idtipoprodserv']?>" aria-expanded=""></i></th>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="panel-body collapse" id="p_tipoprodserv_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>_<?=$row['idcontaitem']?>_<?=$row['idtipoprodserv']?>"">
                                            <table class="table-striped "  style="width: 100%; float:right;">
                                                <tr>
                                                    <th style="width:2%"></th>
                                                    <th style="width:6%;text-align:right">Qtd</th>
                                                    <th style="width:2%;text-align:center">Un</th>
                                                    <th style="width:51%;text-align:left">Item</th> 
                                                    <th style="width:5%;text-align:left">Tipo</th> 
                                                    <th style="width:5%;text-align:center">Data</th> 
                                                    <th style="width:5%;text-align:right" title='Valor unitário sem rateio'>Valor Un</th>
                                                    <th style="width:5%;text-align:right">Rateio</th>
                                                    <th style="width:14%;text-align:center" title='Valor total com rateio'>Valor</th>   
                                                </tr>
        <?
            $idtipoprodserv=$row['idtipoprodserv'];
            
        }
        ?>
                                        
                                                <tr>
                                                    <td  class="sel<?=$row['idtipoprodserv']?>_<?=$row['idobjeto']?>_<?=$row['tipoobjeto']?> emp<?=$row['idobjeto']?>_<?=$row['tipoobjeto']?>">
                                                    <?if($tiporel!='rateioalm'){?>
                                                        <input type="checkbox" class="<?=$class;?> <?=$tiporel;?> changeacao hide" acao="i" atname="checked[<?=$i?>]"  data-class="<?=$class;?>"  data-idrateioitemdest="<?=$idrateioitemdest;?>" value="<?if($class=='nfitem'){echo $row['idtipo'];}else{echo $row['idrateioitemdest'];}?>"  style="border:0px" onclick="liberaBotoes()">
                                                        
                                                    <?}?>
                                                        <?if($row['situacao']=='INVESTIMENTO'){
                                                            $situacao='CRIADO';
                                                            $corinv="#7dc937";
                                                        }else {
                                                            $situacao='INVESTIMENTO';
                                                            $corinv="4878df";
                                                        }
                                                        ?>
                                                        <button id="Investimento" type="button" class="btn btn-primary" style="margin:0px 4px;color:#666;font-size: 10px !important;float:right;background-color:<?=$corinv?> !important;border:none" title="Marcar como investimento" situacao='<?=$situacao?>' onclick="investimento(this,<?=$row['idrateioitemdest']?>);" >
                                                            Investimento
                                                        </button>
                                                   
                                                        <input type="hidden" name="_<?=$i?>_u_rateioitemdest_idrateioitemdest" value="<?=$row['idrateioitemdest']?>">
                                                    </td>
                                                    <td title="Item"  style="text-align: right;">              
                                                        <?=number_format(tratanumero((double)$row['qtd']), 2, ',', '.'); ?></td>
                                                    <td><?=$row['un']?></td>
                                                    <td style="text-align: left;"><?=$row['descr']?></td>              
                                                    <td style="text-align: right;" >
                                                    <?if($row['tipo']=='lotecons'){
                                                        $sl="select tipoobjetoconsumoespec from lotecons where idlotecons=".$row['idtipo'];
                                                        $rl =  d::b()->query($sl) or die('Erro ao buscar tipo do consumo');
                                                        $rwl=mysqli_fetch_assoc($rl);
                                                        if($rwl['tipoobjetoconsumoespec']=='solmatitem'){
                                                            echo("REQUISIÇÃO");
                                                            $tipocons='REQUISICAO';
                                                        }else{
                                                            echo ("TRANSFERÊNCIA");
                                                            $tipocons='TRANSFERENCIA';
                                                        }
                                                    
                                                    }else{
                                                        echo ("COMPRA");
                                                        $tipocons='COMPRA';
                                                    }?>
                                                    </td>
                                                    <td style="text-align: left;" ><?=dma($row['dtemissao'])?></td>
                                                    <td  style="text-align: right;" >R$ <?=number_format(tratanumero((double)$row['vlrlote']), 2, ',', '.');?>
                                                    </td>
                                                    <td style="text-align: right;">
                                                    <?if($tiporel!='rateioalm'){?>
                                                        <?=number_format(tratanumero((double)$row['valor']), 2, ',', '.');?>%
                                                    <?}else{
                                                        if(empty($row['valor'])){?> 
                                                        <a title="Editar Rateio" onclick="editarrateio(<?=$row['idnf']?>,<?=$row['idempresa']?>);" idrateioitemdest="<?=$row['idrateioitemdest']?>" idrateioitem="<?=$row['idrateioitem']?>"  class="hoverazul pointer">
                                                            <?=number_format(tratanumero((double)$row['valor']), 2, ',', '.');?>%
                                                        </a>
                                                    <?
                                                        }else{?>
                                                        <a title="Editar Rateio" onclick="modalRateio(this,'idrateioitemdest','RATEAR');" idrateioitemdest="<?=$row['idrateioitemdest']?>" idrateioitem="<?=$row['idrateioitem']?>"  class="hoverazul pointer">
                                                            <?=number_format(tratanumero((double)$row['valor']), 2, ',', '.');?>%
                                                        </a>
                                                    <?  }
                                                    }
                                                    ?>
                                                    </td> 
                                                    <td  style="text-align: right;" >
                                                    <?
                                                    /*
                                                        if($row['tipo']=='nfitem'){?>
                                                        <div id="consumolote_<?=$row['idtipo']?><?=$row['idrateioitemdest']?>" style="display: none">
                                                            <?=$nfclass->listanfitem($row['idtipo']);?>
                                                        </div>
                                                        <a style="margin-right:65px;" title="Compra" class=" hoverazul  pointer" onclick="showhistoricoitem(<?=$row['idtipo']?><?=$row['idrateioitemdest']?>);">  R$ <?=number_format(tratanumero((double)$row['rateio']), 2, ',', '.');?></a>
                                            
                                                        <?
                                                        
                                                        } 
                                                        */
                                                            echo("R$ ".number_format(tratanumero((double)$row['rateio']), 2, ',', '.'));
                                                        
                                                        /*
                                                        if($row['tipo']=='lotecons'){                                                
                                                     
                                                        ?>				 				
                                                        <div id="consumolote_<?=$row['idrateioitemdest']?>" style="display: none">
                                                            <?=$prodservclass->listalotecons($row['idtipo']);?>
                                                        </div>
                                                        <a title="Histórico" class=" hoverazul  pointer" onclick="showhistoricolote(<?=$row['idtipo']?><?=$row['idrateioitemdest']?>);"> R$ <?=number_format(tratanumero((double)$row['rateio']), 2, ',', '.');?></a>
                                                        <?
                                                       
                                                        }
                                                       */
                                                         
                                                        ?>
                                                    </td>
                                            
                                                </tr>      
                                        
                                    <?         
                                        
                                        }
                                
                                        
                                    ?>       
                                            
                                            </table>
                                        </div>
                                    </div>
                                </div>           
                            </div>
                        </div>           
                    </div>
                </div>           
            </div>
                                
<?
}
?>
                             
        </div>
    </div>
    <br>
    <br>
    <br>
    <p>
    <br>   

<?  



}// fim corpo
/*
 * colocar condição para executar select
 */
if(!empty($idempresa)  and (!empty($datafim))){


    $sql="select  
                    tipo,idempresa,qtd,un,contaitem,idcontaitem,idtipo,idrateio,idrateioitem,idrateioitemdest,situacao,idnf,nnfe,idobjeto,tipoobjeto,
                    idtipoprodserv,tipoprodserv,descr,vlrlote,rateio,valor,empresa,dtemissao,corsistema,rateado, idempresarateio as idempresarateio,siglarateio,tipocusto
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
                        situacao,
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
                        tipocusto
                FROM
                    (".$vw8despesasInvestimento.") v
                WHERE
                    rateado = 'Y'                
                ) as u
                order by idempresarateio,tipoobjeto,empresa,idobjeto,contaitem,tipoprodserv,descr,dtemissao";

                echo "<!-- investimento";
                echo $sql;
                echo "-->";
                
                if (!empty($sql)){
                    $res =  d::b()->query($sql) or die("Falha ao pesquisar consumos investimento: " . mysqli_error() . "<pre>SQL: $sql</pre>");
                    corpo($res,'rateioalm',$idempresa);      
                }


                   $sql="select  
                                tipo,idempresa,qtd,un,contaitem,idcontaitem,idtipo,idrateio,idrateioitem,idrateioitemdest,situacao,idnf,nnfe,idobjeto,tipoobjeto,
                                idtipoprodserv,tipoprodserv,descr,vlrlote,rateio,valor,empresa,dtemissao,corsistema,rateado, idempresarateio as idempresarateio,siglarateio,tipocusto
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
                                    situacao,
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
                                    tipocusto
                            FROM
                                (".$vw8despesas.") v
                            WHERE
                                rateado = 'Y'                
                            ) as u
                            order by idempresarateio,tipoobjeto,empresa,idobjeto,contaitem,tipoprodserv,descr,dtemissao";
                


                echo "<!-- rateio";
                echo $sql;
                echo "-->";
               // die('<pre>'.$sql.'</pre>');
                if (!empty($sql)){
                    $res =  d::b()->query($sql) or die("Falha ao pesquisar consumos: " . mysqli_error() . "<p>SQL: $sql");
                    corpo($res,'rateio',$idempresa); 
                    
                //    var_dump($v_tipoprodserv);
                    //global $v_empresa, $v_idobjeto, $v_contaitem, $v_tipoprodserv;
                    $somatorio = $v_total['rateio'] + $v_total['aratiar']+$v_total['rateioalm'];
                    echo "<script>$('.valor_total_aratiar').html('R$ ".number_format(tratanumero((double)$v_total['aratiar']), 2, ',', '.')."');</script>";
                    echo "<script>$('.percentual_total_aratiar').html('".number_format(tratanumero(($v_total['aratiar']*100)/$somatorio), 2, ',', '.')."%');</script>";
                    echo "<script>$('.percentualfaturamento_total_aratiar').html('".number_format(tratanumero(($v_total['aratiar']*100)/$v_faturamento), 2, ',', '.')."%');</script>";
                    echo "<script>$('.valor_total_rateio').html('R$ ".number_format(tratanumero((double)$v_total['rateio']), 2, ',', '.')."');</script>";
                    echo "<script>$('.percentual_total_rateio').html('".number_format(tratanumero(($v_total['rateio']*100)/$somatorio), 2, ',', '.')."%');</script>";
                    echo "<script>$('.percentualfaturamento_total_rateio').html('".number_format(tratanumero(($v_total['rateio']*100)/$v_faturamento), 2, ',', '.')."%');</script>";

                    echo "<script>$('.valor_total_rateioalm').html('R$ ".number_format(tratanumero((double)$v_total['rateioalm']), 2, ',', '.')."');</script>";
                    echo "<script>$('.percentual_total_rateioalm').html('".number_format(tratanumero(($v_total['rateioalm']*100)/$somatorio), 2, ',', '.')."%');</script>";
                    echo "<script>$('.percentualfaturamento_total_rateioalm').html('".number_format(tratanumero(($v_total['rateialm']*100)/$v_faturamento), 2, ',', '.')."%');</script>";


                    foreach ($v_empresa as $key => $value ){

                        $rest = substr($key, -6); //somente as 6 ultimas letras da chave

                        if($rest == 'aratiar'){
                            $tot = $v_total['aratiar'];
                        }elseif($rest=='eioalm'){
                            $tot = $v_total['rateioalm'];
                        }else{
                            $tot = $v_total['rateio'];
                        }

                        
                   //     echo "$key = $value<br>";
                        echo "<script>$('.valor_empresarateio_".$key."').html('R$ ".number_format(tratanumero((double)$value), 2, ',', '.')."');</script>";
                        echo "<script>$('.percentual_empresarateio_".$key."').html('".number_format(tratanumero((double)($value*100)/$tot), 2, ',', '.')."%');</script>";
                        echo "<script>$('.percentualfaturamento_empresarateio_".$key."').html('".number_format(tratanumero(($value*100)/$v_faturamento), 2, ',', '.')."%');</script>";
                        foreach ($v_idobjeto[$key] as $key1 => $value1 ){
                            echo "<script>$('.valor_objeto_".$key."_".$key1."').html('R$ ".number_format(tratanumero((double)$value1), 2, ',', '.')."');</script>";
                            echo "<script>$('.percentual_objeto_".$key."_".$key1."').html('".number_format(tratanumero(($value1*100)/$value), 2, ',', '.')."%');</script>";
                            echo "<script>$('.percentualfaturamento_objeto_".$key."_".$key1."').html('".number_format(tratanumero(($value1*100)/$v_faturamento), 2, ',', '.')."%');</script>";
                            foreach ($v_contaitem[$key][$key1] as $key2 => $value2 ){
                                echo "<script>$('.valor_contaitem_".$key."_".$key1."_".$key2."').html('R$ ".number_format(tratanumero((double)$value2), 2, ',', '.')."');</script>";
                                echo "<script>$('.percentual_contaitem_".$key."_".$key1."_".$key2."').html('".number_format(tratanumero(($value2*100)/$value1), 2, ',', '.')."%');</script>";
                                echo "<script>$('.percentualfaturamento_contaitem_".$key."_".$key1."_".$key2."').html('".number_format(tratanumero(($value2*100)/$v_faturamento), 2, ',', '.')."%');</script>";
                                foreach ($v_tipoprodserv[$key][$key1][$key2] as $key3 => $value3 ){
                                    echo "<script>$('.valor_tipoprodserv_".$key."_".$key1."_".$key2."_".$key3."').html('R$ ".number_format(tratanumero((double)$value3), 2, ',', '.')."');</script>";
                                    echo "<script>$('.percentual_tipoprodserv_".$key."_".$key1."_".$key2."_".$key3."').html('".number_format(tratanumero(($value3*100)/$value2), 2, ',', '.')."%');</script>";
                                    echo "<script>$('.percentualfaturamento_tipoprodserv_".$key."_".$key1."_".$key2."_".$key3."').html('".number_format(tratanumero(($value3*100)/$v_faturamento), 2, ',', '.')."%');</script>";
                                    echo "<script>console.log('.valor_tipoprodserv_".$key."_".$key1."_".$key2."_".$key3."');</script>";
                                    echo "<script>console.log($value3);</script>";
                                    echo "<script>console.log($value2);</script>";
                                    
                                }
                            }
                        }

                    }

                    while (list($empr,$arridobj1 ) = each($arrvcontaitem)){ 
                        while (list($tipo,$arridobj2 ) = each($arridobj1)){ 
        
                            while (list($idojb,$arrtipop2 ) = each($arridobj2)){                
                                $valore=($arrvemp[$empr][$tipo][$idojb]);
                                $valore_p = round((($valore*100)/ $totalgeral),2);
                ?>
                                <div class="empresa hidden" id='empresap<?=$tipo?>_<?=$idojb?>'>
                                    <?=number_format(tratanumero((double)$valore_p), 2, ',', '.');?>%
                                </div>
                <? 
                                while (list($idcont,$arrf ) = each($arrtipop2)){
                                    $valori= $arrvcontaitem[$empr][$tipo][$idojb][$idcont]; 
                                    $valori_p = round((($valori*100)/ $valore),2);
                    ?>
                                    <div class="contaitem hidden" id='contaitemp<?=$idcont?>_<?=$idojb?>'>
                                        <?=number_format(tratanumero((double)$valori_p), 2, ',', '.');?>%
                                    </div>
                    <?
                    // $arrvemp[$idempresarateio][$tipoobjeto][$idobjeto]
                                    while(list($idtipop,$arrt ) = each($arrvtipo[$empr][$tipo][$idojb][$idcont])){
                                        $valorc= $arrvtipo[$empr][$tipo][$idojb][$idcont][$idtipop]; 
                                        $valorc_p = round((($valorc*100)/ $valori),2);
                                        ?>
                                        <div class="tipoprodserv hidden" id='tipop<?=$idtipop?>_<?=$idcont?>_<?=$idojb?>'>
                                            <?=number_format(tratanumero((double)$valorc_p), 2, ',', '.');?>%
                                        </div>
                        <?
                                    }
                                    reset($arrvtipo);
                                } 
                            }
                        }
                    }
                ?>
                    <div class="totalg hidden" id='totalgeral' id='totalgeral' valor="<?=$totalgeral?>"  >
                    R$ <?=number_format(tratanumero((double)$totalgeral), 2, ',', '.');?>
                    </div>
                    <div class="hidden"  id='totalempresa' valor="<?=$totalempresa?>"  >
                    R$ <?=number_format(tratanumero((double)$totalempresa), 2, ',', '.');?>
                    </div>
                    <div class="hidden"  id='totaloempresa' valor="<?=$totaloempresa?>"  >
                    R$ <?=number_format(tratanumero((double)$totaloempresa), 2, ',', '.');?>
                    </div>
                <?
                }
            
         // }
    
  

}

?>

<script>
var custeado = "<?= $custeado ?>";

if (custeado === 'Y') {
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.disabled = true;
    });
}
function selecionartipo(vthis) {
    debbuger;
    var empresa = $(vthis).val()
    $.ajax({
        type: "get",
        url : "ajax/atulizatipoprod.php",
        data: {empresa:empresa},

        success: function(data){
            $("[name=idtipoprodserv]").empty(); 
            $("[name=idtipoprodserv]").append("<option></option>"); 
            try {
                var json = JSON.parse(data)
                $.each(json, function(key,value) {
                $("[name=idtipoprodserv]").append($("<option value='"+value.idtipoprodserv+"'>"+value.tipoprodserv+"</option>"));
                });
            } catch (err) {
                console.log('erro no ajax')
            }
        },

        error: function(objxmlreq){
                alert('Erro:<br>'+objxmlreq.status); 
        }
    });
}

function atualizar(vthis){

    $('#btatualizar').toggleClass('atualizando');

    $.ajax({
        type: "get",
        url : "cron/atualizarateioproduto.php",                          
        success: function(data){
            if(data=='OK'){
                location.reload();
            }else{
                alertErro(data);
            }
        },
        error: function(objxmlreq){
            alert('Erro:<br>'+objxmlreq.status);
        }
    });


}

function pesquisar(vthis){
    debugger;
   // $(vthis).addClass( "blink" );
   $(vthis).html('<span class="fa fa-spinner fa-pulse"></span>');
   // var vencimento_1 = $("[name=vencimento_1]").val();
    // var vencimento_2 = $("[name=vencimento_2]").val();
    var mes = $("[name=mes]").val();
    var ano = $("[name=ano]").val();
    var idempresa = $("[name=idempresa]").val();
    var idprodserv = $("[name=idprodserv]").val();
    var pesquisa = $("[name=pesquisa]").val();
    var status = $("[name=status]").val();
    var idcontaitem=$("#picker_grupoes").val();

    if(mes==undefined || mes==''){
        alert('É necessário selecionar o Mês.');
    }else if(ano==undefined){
        alert('É necessário selecionar o Ano.');
    }else{
        var str="&mes="+mes+"&ano="+ano+"&idempresa="+idempresa+"&pesquisa="+pesquisa+"&status="+status ;
        CB.go(str);
    }

}



function showhistoricolote(idlotecons=null){
	if(idlotecons){
		CB.modal({
			titulo: "</strong>Histórico do consumo</strong>",
			corpo: $("#consumolote_"+idlotecons).html(),
			classe: 'sessenta',
		});
	}else{
		alertAtencao("Identificador do lote vazio", "Erro de Lote");
	}
}

function showhistoricoitem(idlotecons=null){
	if(idlotecons){
		CB.modal({
			titulo: "</strong>Dados da compra</strong>",
			corpo: $("#consumolote_"+idlotecons).html(),
			classe: 'sessenta',
		});
	}else{
		alertAtencao("Identificador do lote vazio", "Erro de Lote");
	}
}

function marcarTodos(vthis){
    var checkboxes = $(vthis).closest('.panel-default').find(':checkbox');
    checkboxes.prop('checked',  $(vthis).is(':checked'));


    liberaBotoes();
}

function liberaBotoes(){
    debugger;
    if($('input.aratiar[type="checkbox"]').is(':checked')){
        $('#ratear').removeClass('hidden');
    }else{
        $('#ratear').addClass('hidden');
    }

    if($('input.rateio[type="checkbox"]').is(':checked') == true || $('input.rateioalm[type="checkbox"]').is(':checked')  == true ){
        $('#editar').removeClass('hidden');
        $('#cobrar').removeClass('hidden');
    }else{
        $('#editar').addClass('hidden');
        $('#cobrar').addClass('hidden');
    }
  /*  
    if($('input.rateioalm[type="checkbox"]').is(':checked')){
        $('#editar').removeClass('hidden');
    }else{
        $('#editar').addClass('hidden');
    }
*/
}

function contarSelecionados() {
            // Seleciona todos os checkboxes com a classe 'todosinicio'
            const checkboxes = document.querySelectorAll('.todosinicio');
            // Filtra e conta quantos estão marcados
            const selecionados = Array.from(checkboxes).filter(checkbox => checkbox.checked).length;
            return selecionados;
}

function modalRateio(vthis, tipo,funcao){
    var v_idrateioitemdest = '';
    var virgula = '';
    var v_num;
    var v_ratear = false;
    var v_idempresa=$("[name=idempresa]").val();
    var v_idnfitem = '';
    var v_url;

    if(funcao=="COBRAR"){
        var titulo = "Custear Rateio";
        var modulo="rateioitemcusto";
        var dataini=$("#dataini").val();
        var datafim=$("#datafim").val();
        var idempresa=$("#idempresa").val();
        var strdata="&dataini="+dataini+"&datafim="+datafim;

        const selecionados = contarSelecionados();
        if (selecionados > 1) {
            alert(`É permito enviar apenas 1 unidade para custo, você selecionou ${selecionados} unidades.`);
            exit();
        }

    }else{
      var titulo = "Editar Rateio";
      var modulo="rateioitemdest";
      var strdata="";
    }

    if (tipo == 'emlote'){
        
        $('input.emlote:checkbox:checked').each(function() {

        v_num = parseInt($(this).val());
            if( v_num > 0){
                v_idrateioitemdest = v_idrateioitemdest.concat(virgula);
                v_idrateioitemdest = v_idrateioitemdest.concat($(this).val());
                virgula = ',';
                v_ratear = true;
            }
        });

        v_url = "?_modulo="+modulo+"&_acao=u&tipo=rateio&funcao="+funcao+"&stidrateioitemdest="+v_idrateioitemdest+"&_idempresa="+v_idempresa+strdata;

    }else if(tipo == 'idrateioitemdest'){
         v_idrateioitemdest =$(vthis).attr('idrateioitemdest');
         v_url = "?_modulo="+modulo+"&_acao=u&tipo=rateio&funcao="+funcao+"&stidrateioitemdest="+v_idrateioitemdest+"&_idempresa="+v_idempresa+strdata;
         v_ratear = true;
    }else if(tipo == 'nfitem'){
        $('input.nfitem:checkbox:checked').each(function() {

        v_num = parseInt($(this).val());
        
            if( v_num > 0){
                v_idnfitem = v_idnfitem.concat(virgula);
                v_idnfitem = v_idnfitem.concat($(this).val());
                virgula = ',';
                v_ratear = true;
            }
        });

        v_url = "?_modulo="+modulo+"&_acao=u&tipo=nf&idnfitem="+v_idnfitem+"&_idempresa="+v_idempresa+strdata;

    


    }
    
    if (v_ratear === false ){
        alertAtencao('É necessário selecionar os itens que deseja alterar o rateio');
        return;
    }
    
    $("#cbModuloForm").append(`<input type="hidden" id="_inputmodalrateiosemmodificacao_" mod="N">`);
    CB.modal({
        url:v_url,
        header: titulo,
        callback: function(data, textStatus, jqXHR){
            if($("#_inputmodalrateiosemmodificacao_").attr('mod') == 'Y' && textStatus == 'success'){
				$('#cbModal').modal('hide');
                
                $('input[type=checkbox]:checked:enabled').closest( "tr" ).css( "background-color", "#add8e6" );
                $('input[type=checkbox]').prop('checked',false);
                //marcelocunha 29/08/2022 Daniel solicitou que não recarregue a pagina ao salvar um rateio, apenas colorir.
                //location.reload();
            }
        },
        aoFechar: function() {
             // Seleciona todos os checkboxes com a classe 'todosinicio' que estão marcados como checked
                const checkboxes = document.querySelectorAll('input.todosinicio:checked');
                
                checkboxes.forEach(checkbox => {
                    // Encontra o elemento pai com a classe 'panel-default'
                    let parentPanel = checkbox.closest('.panel-default');
                    
                    // Remove o elemento pai do DOM
                    if (parentPanel) {
                        parentPanel.remove();
                    }
                });
        }
    });

}

function rateiocusto(vthis,dataini,datafim,idrateiocusto){
debugger;
    var v_idrateioitemdest=$(vthis).attr('stidrateioitemdest');
    var v_idempresa=$("[name=idempresa]").val();
    var titulo = "Custear Rateio";
    var strdata="&dataini="+dataini+"&datafim="+datafim+"&idrateiocusto="+idrateiocusto;
    var v_url;
    var modulo="rateioitemcusto";
    v_url = "?_modulo="+modulo+"&_acao=u&tipo=rateio&funcao=COBRAR&stidrateioitemdest="+v_idrateioitemdest+"&_idempresa="+v_idempresa+strdata;

    CB.modal({
        url:v_url,
        header: titulo
        
        ,aoFechar: function() {
            location.reload();
        }
        
    });

}

function editarrateio(idnf,idempresa) 
    {
        
        
        CB.modal({
            url: "?_modulo=rateioitemdest&_acao=u&tipo=nf&idnf=" + idnf +"&_idempresa=" +idempresa,
            header: "Editar Rateio",
            aoFechar: function() {
                location.reload();
            }
        });
    }


//menufixo
    stickyElem = document.querySelector(".sticky-div");
    currStickyPos = stickyElem.getBoundingClientRect().top + window.pageYOffset;
    window.onscroll = function() {
         
        if(window.pageYOffset > currStickyPos + 260) {
            stickyElem.style.position = "fixed";
            stickyElem.style.top = "40px";
            stickyElem.style.width = "92%";
        } else {
            stickyElem.style.position = "relative";
            stickyElem.style.top = "initial";
            stickyElem.style.width = "100%";
        }
    }
    
    function mostraPercentual(button,classe) {
        //check if any button has class active and shows the collapse-bottom element 
        if ($(button).hasClass("active")) {
            $("."+classe).hide();
            $(button).removeClass("active");
        } else {
            $("."+classe).show();
            $(button).addClass("active");
        }
    }

    function selecionarAgencia(valor)
    {
        var idempresa = $("[name=idempresa]").val();

        var str="idempresa="+idempresa;
        CB.go(str);
    }
    $('.selectpicker').selectpicker('render');

    $('#picker_grupoes').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue){
        if($(e.currentTarget).val())
        {
            $('#sel_picker_idcontaitem').val($(e.currentTarget).val().join());
            idcontaitem = $(e.currentTarget).val().join();
        } else {
            $('#sel_picker_idcontaitem').val('');
            idcontaitem = '';
        }

       
    });

    
function reldet(inrel){
    debugger;
    var mes = $("[name=mes]").val();
    var ano = $("[name=ano]").val();
    var idempresa = $("[name=idempresa]").val();
    var pesquisa = $("[name=pesquisa]").val();
    var status = $("[name=status]").val();

    if(mes==undefined || mes==''){
        alert('É necessário selecionar o Mês.');
    }else if(ano==undefined){
        alert('É necessário selecionar o Ano.');
    }else{
        var str="&mes="+mes+"&ano="+ano+"&idempresa="+idempresa+"&pesquisa="+pesquisa+"&status="+status ;       
    }

    if(inrel==1){
        janelamodal('report/reprateiocusto.php?'+str+'');    
    }
}

function investimento(vthis,idrateioitemdest){
    debugger;
    $situacao=$(vthis).attr('situacao');

    CB.post({
				objetos: "_inv_u_rateioitemdest_idrateioitemdest="+idrateioitemdest+"&_inv_u_rateioitemdest_situacao="+$situacao
				,parcial:true
				,refresh: false
				,msgSalvo: "Salvo"
				,posPost: function(){

                    if( $situacao=='INVESTIMENTO'){
                        // Change the background color
                        vthis.style.backgroundColor = '#7dc937';
                        // Update the situacao attribute
                        vthis.setAttribute('situacao', 'CRIADO');
                    }else{
                         // Change the background color
                         vthis.style.backgroundColor = '#4878df';
                        // Update the situacao attribute
                        vthis.setAttribute('situacao', 'INVESTIMENTO');
                    }

                }
                   
			})

}


    
</script>
