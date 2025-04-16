<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../api/prodserv/index.php");
require_once("../model/prodserv.php");
require_once("../model/nf.php");
//Chama a Classe prodserv
$prodservclass = new PRODSERV();
$nfclass= new NF();

if($_POST){
    require_once("../inc/php/cbpost.php");
}
################################################## Atribuindo o resultado do metodo GET
$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$idtipoprodserv = $_GET["idtipoprodserv"];
$idtipoprodserv     = $_GET["idtipoprodserv"];
$_idcontaitem        = $_GET["_idcontaitem"];
$idagencia 		= $_GET["idagencia"];
$idprodserv=$_GET["idprodserv"];
$pesquisa = $_GET["pesquisa"];
$idsgdepartamento = $_GET["idsgdepartamento"];
if(empty($_GET["idempresa"])){
	$idempresa = cb::idempresa();
} else {
	$idempresa = $_GET["idempresa"];
}
global $totalempresa;
global $totaloempresa;

//$dataini = validadate($vencimento_1);
$datafim = validadate($vencimento_2);    

if(!empty($idagencia) and $idagencia!='null'){
 $anddesp=" and cp.idagencia in (".$idagencia.") ";
 $andagencia=" and idagencia in (".$idagencia.") ";
}

if (!empty($_idcontaitem) and $_idcontaitem!='null') {
    $stridcontaitem = " and a.idcontaitem in (" . $_idcontaitem . ") ";
} else {
    $stridcontaitem = "";
}





function getAgenciaArr($idagencia, $idempresa = NULL){
    if(!empty($idempresa)){
		$idempresa = "AND idempresa = '$idempresa'";
	}else{
		$idempresa = 'AND idempresa ='.cb::idempresa();
	}
	if(count(getModsUsr("AGENCIAS")) > 0) {$agencias = getModsUsr("AGENCIAS"); } else {$agencias = "''";}
	$sql = "SELECT idagencia, agencia 
			  FROM agencia a 
			 WHERE status = 'ATIVO' 
			   AND idagencia IN (".$agencias.")
			$idempresa  
		  ORDER BY ord";
	
	return $sql;
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
IFNULL(`e`.`idempresa`,  `a`.`idempresa`) AS `idempresarateio`,
IFNULL(`e`.`empresa`, `a`.`empresa`) AS `siglarateio`,
CASE WHEN '".$pesquisa."'='CENTROCUSTO' THEN ct.centrocusto
      WHEN  `u`.`unidade` IS NULL THEN   `a`.`empresa`
      WHEN  `u`.`unidade` IS NOT NULL THEN  `u`.`unidade` END AS empresarateio,
IFNULL(`e`.`corsistema`,
        `a`.`corsistema`) AS `corsistema`
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
        `n`.`idempresa` AS `idempresa`,
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
        (((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - `n`.`frete`)) * `n`.`frete`)) ) ) * -(1)) AS `total`,
        `p`.`tipoprodserv` AS `tipoprodserv`,
        `n`.`nnfe` AS `nnfe`,
        `i`.`vlritem` AS `vlritem`
FROM
    ((((((`nf` `n`
JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
    AND (`i`.`nfe` = 'Y'))))
JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)))
JOIN `contapagar` `cp` ON (((`cp`.`idobjeto` = `n`.`idnf`)
    AND (`cp`.`tipoobjeto` = 'nf'))))
LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
JOIN `empresa` `e` ON ((`e`.`idempresa` = `n`.`idempresa`))
JOIN `rateioitem` `ri` ON (`ri`.`idobjeto` = `i`.`idnfitem`
AND `ri`.`tipoobjeto` = 'nfitem')
JOIN `rateioitemdest` `rid` ON (`rid`.`idrateioitem` = `ri`.`idrateioitem` and rid.status='PENDENTE')
JOIN `unidade` `u` ON (`u`.`idunidade` = `rid`.`idobjeto`
AND `rid`.`tipoobjeto` = 'unidade')
)
WHERE
    ((`cp`.`tipoespecifico` <> 'AGRUPAMENTO')
        AND (`cp`.`status` <> 'INATIVO')
        AND (`cp`.`tipo` = 'D')
        AND (`cp`.`valor` > 0)
        AND (`n`.`tiponf` NOT IN ('S' , 'R'))) 
        AND `cp`.`status` <> 'ABERTO'
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."
        AND `n`.`dtemissao` <=  '".$datafim." 23:59:59'
        and n.status='CONCLUIDO'
      
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
        `n`.`idempresa` AS `idempresa`,
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
        (((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - `n`.`frete`)) * `n`.`frete`)) ) ) * -(1)) AS `total`,
        `p`.`tipoprodserv` AS `tipoprodserv`,
        `n`.`nnfe` AS `nnfe`,
        `i`.`vlritem` AS `vlritem`
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
JOIN `empresa` `e` ON ((`e`.`idempresa` = `n`.`idempresa`))
JOIN `rateioitem` `ri` ON (`ri`.`idobjeto` = `i`.`idnfitem`
AND `ri`.`tipoobjeto` = 'nfitem')
JOIN `rateioitemdest` `rid` ON (`rid`.`idrateioitem` = `ri`.`idrateioitem` and rid.status='PENDENTE')
JOIN `unidade` `u` ON (`u`.`idunidade` = `rid`.`idobjeto`
AND `rid`.`tipoobjeto` = 'unidade')
)
WHERE
    ((`cp`.`tipoespecifico` = 'AGRUPAMENTO')
        AND (`cp`.`status` <> 'INATIVO')
        AND (`ci`.`status` <> 'INATIVO')
        AND (`cp`.`tipo` = 'D')
        AND (`cp`.`valor` > 0)
        AND (`n`.`tiponf` NOT IN ('S' , 'R'))) 
        AND `cp`.`status` <> 'ABERTO'
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."       
        and n.status='CONCLUIDO'
         and `i`.`qtd` > 0
         AND `n`.`dtemissao` <=  '".$datafim." 23:59:59'
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
        `n`.`idempresa` AS `idempresa`,
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
        ((((IFNULL(`i`.`total`, 0) * (`n`.`total` / ifnull(n.subtotal,n.total))) ) ) * -(1)) AS `total`,
        `p`.`tipoprodserv` AS `tipoprodserv`,
        `n`.`nnfe` AS `nnfe`,
        `i`.`vlritem` AS `vlritem`
FROM
    ((((((`nf` `n`
JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
    AND (`i`.`nfe` = 'Y'))))
JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)))
JOIN `contapagar` `cp` ON (((`cp`.`idobjeto` = `n`.`idnf`)
    AND (`cp`.`tipoobjeto` = 'nf'))))
LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
JOIN `empresa` `e` ON ((`e`.`idempresa` = `n`.`idempresa`))
JOIN `rateioitem` `ri` ON (`ri`.`idobjeto` = `i`.`idnfitem`
AND `ri`.`tipoobjeto` = 'nfitem')
JOIN `rateioitemdest` `rid` ON (`rid`.`idrateioitem` = `ri`.`idrateioitem` and rid.status='PENDENTE')
JOIN `unidade` `u` ON (`u`.`idunidade` = `rid`.`idobjeto`
AND `rid`.`tipoobjeto` = 'unidade')
)
WHERE
    ((`cp`.`tipoespecifico` <> 'AGRUPAMENTO')
        AND (`cp`.`status` <> 'INATIVO')
        AND (`cp`.`tipo` = 'D')
        AND (`cp`.`valor` > 0)
        AND (`n`.`tiponf` IN ('S' , 'R'))) 
        AND `cp`.`status` <> 'ABERTO'
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."
      
        and n.status='CONCLUIDO'
         and `i`.`qtd` > 0
         AND `n`.`dtemissao` <=  '".$datafim." 23:59:59'
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
        `n`.`idempresa` AS `idempresa`,
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
        ((((IFNULL(`i`.`total`, 0) * (`n`.`total` / ifnull(n.subtotal,n.total))) ) ) * -(1)) AS `total`,
        `p`.`tipoprodserv` AS `tipoprodserv`,
        `n`.`nnfe` AS `nnfe`,
        `i`.`vlritem` AS `vlritem`
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
JOIN `empresa` `e` ON ((`e`.`idempresa` = `n`.`idempresa`))
JOIN `rateioitem` `ri` ON (`ri`.`idobjeto` = `i`.`idnfitem`
AND `ri`.`tipoobjeto` = 'nfitem')
JOIN `rateioitemdest` `rid` ON (`rid`.`idrateioitem` = `ri`.`idrateioitem` and rid.status='PENDENTE')
JOIN `unidade` `u` ON (`u`.`idunidade` = `rid`.`idobjeto`
AND `rid`.`tipoobjeto` = 'unidade')
)
WHERE
    ((`cp`.`tipoespecifico` = 'AGRUPAMENTO')
        AND (`cp`.`status` <> 'INATIVO')
        AND (`ci`.`status` <> 'INATIVO')
        AND (`cp`.`tipo` = 'D')
        AND (`cp`.`valor` > 0)
        AND (`n`.`tiponf` IN ('S' , 'R')))
        AND `cp`.`status` <> 'ABERTO'
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."      
        and n.status='CONCLUIDO'
       and `i`.`qtd` > 0
       AND `n`.`dtemissao` <=  '".$datafim." 23:59:59'
) `a`
 JOIN `rateioitem` `ri` ON (((`ri`.`idobjeto` = `a`.`idnfitem`)
    AND (`ri`.`tipoobjeto` = 'nfitem'))))
 JOIN `rateioitemdest` `rid` ON ((`rid`.`idrateioitem` = `ri`.`idrateioitem`) and rid.status='PENDENTE'))
 JOIN `unidade` `u` ON (((`u`.`idunidade` = `rid`.`idobjeto`)
    AND (`rid`.`tipoobjeto` = 'unidade') and u.idtipounidade !=3 ))
LEFT JOIN centrocusto ct on(ct.idcentrocusto = u.idcentrocusto)   
    )
LEFT JOIN `empresa` `e` ON ((`e`.`idempresa` = `u`.`idempresa`))))
WHERE
(`a`.`somarelatorio` = 'Y' ".$stridcontaitem." ) group by idrateioitemdest";


$vw8despesasalm = 
" SELECT 
`a`.`tiponf` AS `tiponf`,
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
IFNULL(`e`.`idempresa`,  `a`.`idempresa`) AS `idempresarateio`,
IFNULL(`e`.`empresa`, `a`.`empresa`) AS `siglarateio`,
CASE WHEN '".$pesquisa."'='CENTROCUSTO' THEN ct.centrocusto
      WHEN  `u`.`unidade` IS NULL THEN   `a`.`empresa`
      WHEN  `u`.`unidade` IS NOT NULL THEN  `u`.`unidade` END AS empresarateio,
IFNULL(`e`.`corsistema`,
        `a`.`corsistema`) AS `corsistema`
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
        `n`.`idempresa` AS `idempresa`,
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
        (((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - `n`.`frete`)) * `n`.`frete`)) ) ) * -(1)) AS `total`,
        `p`.`tipoprodserv` AS `tipoprodserv`,
        `n`.`nnfe` AS `nnfe`,
        `i`.`vlritem` AS `vlritem`
FROM
    ((((((`nf` `n`
JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
    AND (`i`.`nfe` = 'Y'))))
JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)))
JOIN `contapagar` `cp` ON (((`cp`.`idobjeto` = `n`.`idnf`)
    AND (`cp`.`tipoobjeto` = 'nf'))))
LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
JOIN `empresa` `e` ON ((`e`.`idempresa` = `n`.`idempresa`))
JOIN `rateioitem` `ri` ON (`ri`.`idobjeto` = `i`.`idnfitem`
AND `ri`.`tipoobjeto` = 'nfitem')
JOIN `rateioitemdest` `rid` ON (`rid`.`idrateioitem` = `ri`.`idrateioitem` and rid.status='PENDENTE')
JOIN `unidade` `u` ON (`u`.`idunidade` = `rid`.`idobjeto`
AND `rid`.`tipoobjeto` = 'unidade')
)
WHERE
    ((`cp`.`tipoespecifico` <> 'AGRUPAMENTO')
        AND (`cp`.`status` <> 'INATIVO')
        AND (`cp`.`tipo` = 'D')
        AND (`cp`.`valor` > 0)
        AND (`n`.`tiponf` NOT IN ('S' , 'R'))) 
        AND `cp`.`status` <> 'ABERTO'
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."
         and n.status='CONCLUIDO'
         AND `n`.`dtemissao` <=  '".$datafim." 23:59:59'
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
        `n`.`idempresa` AS `idempresa`,
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
        (((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - `n`.`frete`)) * `n`.`frete`)) ) ) * -(1)) AS `total`,
        `p`.`tipoprodserv` AS `tipoprodserv`,
        `n`.`nnfe` AS `nnfe`,
        `i`.`vlritem` AS `vlritem`
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
JOIN `empresa` `e` ON ((`e`.`idempresa` = `n`.`idempresa`))
JOIN `rateioitem` `ri` ON (`ri`.`idobjeto` = `i`.`idnfitem`
AND `ri`.`tipoobjeto` = 'nfitem')
JOIN `rateioitemdest` `rid` ON (`rid`.`idrateioitem` = `ri`.`idrateioitem` and rid.status='PENDENTE')
JOIN `unidade` `u` ON (`u`.`idunidade` = `rid`.`idobjeto`
AND `rid`.`tipoobjeto` = 'unidade')
)
WHERE
    ((`cp`.`tipoespecifico` = 'AGRUPAMENTO')
        AND (`cp`.`status` <> 'INATIVO')
        AND (`ci`.`status` <> 'INATIVO')
        AND (`cp`.`tipo` = 'D')
        AND (`cp`.`valor` > 0)
        AND (`n`.`tiponf` NOT IN ('S' , 'R'))) 
        AND `cp`.`status` <> 'ABERTO'
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."
        and n.status='CONCLUIDO'
        and `i`.`qtd` > 0
        AND `n`.`dtemissao` <=  '".$datafim." 23:59:59'
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
        `n`.`idempresa` AS `idempresa`,
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
        ((((IFNULL(`i`.`total`, 0) * (`n`.`total` / ifnull(n.subtotal,n.total))) ) ) * -(1)) AS `total`,
        `p`.`tipoprodserv` AS `tipoprodserv`,
        `n`.`nnfe` AS `nnfe`,
        `i`.`vlritem` AS `vlritem`
FROM
    ((((((`nf` `n`
JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
    AND (`i`.`nfe` = 'Y'))))
JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)))
JOIN `contapagar` `cp` ON (((`cp`.`idobjeto` = `n`.`idnf`)
    AND (`cp`.`tipoobjeto` = 'nf'))))
LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
JOIN `empresa` `e` ON ((`e`.`idempresa` = `n`.`idempresa`))
JOIN `rateioitem` `ri` ON (`ri`.`idobjeto` = `i`.`idnfitem`
AND `ri`.`tipoobjeto` = 'nfitem')
JOIN `rateioitemdest` `rid` ON (`rid`.`idrateioitem` = `ri`.`idrateioitem` and rid.status='PENDENTE')
JOIN `unidade` `u` ON (`u`.`idunidade` = `rid`.`idobjeto`
AND `rid`.`tipoobjeto` = 'unidade')
)
WHERE
    ((`cp`.`tipoespecifico` <> 'AGRUPAMENTO')
        AND (`cp`.`status` <> 'INATIVO')
        AND (`cp`.`tipo` = 'D')
        AND (`cp`.`valor` > 0)
        AND (`n`.`tiponf` IN ('S' , 'R'))) 
        AND `cp`.`status` <> 'ABERTO'
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."
        and n.status='CONCLUIDO'
        and `i`.`qtd` > 0
        AND `n`.`dtemissao` <=  '".$datafim." 23:59:59'
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
        `n`.`idempresa` AS `idempresa`,
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
        ((((IFNULL(`i`.`total`, 0) * (`n`.`total` / ifnull(n.subtotal,n.total))) ) ) * -(1)) AS `total`,
        `p`.`tipoprodserv` AS `tipoprodserv`,
        `n`.`nnfe` AS `nnfe`,
        `i`.`vlritem` AS `vlritem`
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
JOIN `empresa` `e` ON ((`e`.`idempresa` = `n`.`idempresa`))
JOIN `rateioitem` `ri` ON (`ri`.`idobjeto` = `i`.`idnfitem`
AND `ri`.`tipoobjeto` = 'nfitem')
JOIN `rateioitemdest` `rid` ON (`rid`.`idrateioitem` = `ri`.`idrateioitem` and rid.status='PENDENTE')
JOIN `unidade` `u` ON (`u`.`idunidade` = `rid`.`idobjeto`
AND `rid`.`tipoobjeto` = 'unidade')
)
WHERE
    ((`cp`.`tipoespecifico` = 'AGRUPAMENTO')
        AND (`cp`.`status` <> 'INATIVO')
        AND (`ci`.`status` <> 'INATIVO')
        AND (`cp`.`tipo` = 'D')
        AND (`cp`.`valor` > 0)
        AND (`n`.`tiponf` IN ('S' , 'R')))
        AND `cp`.`status` <> 'ABERTO'
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."
        and n.status='CONCLUIDO'
        and `i`.`qtd` > 0
        AND `n`.`dtemissao` <=  '".$datafim." 23:59:59'
) `a`
 JOIN `rateioitem` `ri` ON (((`ri`.`idobjeto` = `a`.`idnfitem`)
    AND (`ri`.`tipoobjeto` = 'nfitem'))))
 JOIN `rateioitemdest` `rid` ON ((`rid`.`idrateioitem` = `ri`.`idrateioitem`) and rid.status='PENDENTE'))
 JOIN `unidade` `u` ON (((`u`.`idunidade` = `rid`.`idobjeto`)
    AND (`rid`.`tipoobjeto` = 'unidade') and u.idtipounidade in (3) ))
LEFT JOIN centrocusto ct on(ct.idcentrocusto = u.idcentrocusto)   
    )
LEFT JOIN `empresa` `e` ON ((`e`.`idempresa` = `u`.`idempresa`))))
WHERE
(`a`.`somarelatorio` = 'Y' ".$stridcontaitem." ) group by idrateioitemdest";


$vw8despesas_semrateio = 
" SELECT 
`a`.`tiponf` AS `tiponf`,
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
`a`.`total` AS `rateio`,
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
IFNULL(`e`.`idempresa`,  `a`.`idempresa`) AS `idempresarateio`,
IFNULL(`e`.`empresa`, `a`.`empresa`) AS `siglarateio`,
IFNULL(`u`.`unidade`,
       `a`.`empresa`) AS `empresarateio`,
IFNULL(`e`.`corsistema`,
        `a`.`corsistema`) AS `corsistema`
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
        `n`.`idempresa` AS `idempresa`,
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
        (((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - `n`.`frete`)) * `n`.`frete`)) ) ) * -(1)) AS `total`,
        `p`.`tipoprodserv` AS `tipoprodserv`,
        `n`.`nnfe` AS `nnfe`,
        `i`.`vlritem` AS `vlritem`
FROM
    ((((((`nf` `n`
JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
    AND (`i`.`nfe` = 'Y'))))
JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)))
JOIN `contapagar` `cp` ON (((`cp`.`idobjeto` = `n`.`idnf`)
    AND (`cp`.`tipoobjeto` = 'nf'))))
LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
JOIN `empresa` `e` ON ((`e`.`idempresa` = `n`.`idempresa`)))
WHERE
    ((`cp`.`tipoespecifico` <> 'AGRUPAMENTO')
        AND (`cp`.`status` <> 'INATIVO')
        AND (`cp`.`tipo` = 'D')
        AND (`cp`.`valor` > 0)
        AND (`n`.`tiponf` NOT IN ('S' , 'R'))) 
        AND `cp`.`status` <> 'ABERTO'
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."
        and n.dtemissao BETWEEN DATE_SUB(now(), INTERVAL 1 month) and DATE_ADD(now(), INTERVAL 1 DAY)
        and n.status='CONCLUIDO'
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
        `n`.`idempresa` AS `idempresa`,
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
        (((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - `n`.`frete`)) * `n`.`frete`)) ) ) * -(1)) AS `total`,
        `p`.`tipoprodserv` AS `tipoprodserv`,
        `n`.`nnfe` AS `nnfe`,
        `i`.`vlritem` AS `vlritem`
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
JOIN `empresa` `e` ON ((`e`.`idempresa` = `n`.`idempresa`)))
WHERE
    ((`cp`.`tipoespecifico` = 'AGRUPAMENTO')
        AND (`cp`.`status` <> 'INATIVO')
        AND (`ci`.`status` <> 'INATIVO')
        AND (`cp`.`tipo` = 'D')
        AND (`cp`.`valor` > 0)
        AND (`n`.`tiponf` NOT IN ('S' , 'R'))) 
        AND `cp`.`status` <> 'ABERTO'
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."
        and n.dtemissao BETWEEN DATE_SUB(now(), INTERVAL 1 month) and DATE_ADD(now(), INTERVAL 1 DAY)
        and n.status='CONCLUIDO'
        and `i`.`qtd` > 0
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
        `n`.`idempresa` AS `idempresa`,
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
        ((((IFNULL(`i`.`total`, 0) * (`n`.`total` / ifnull(n.subtotal,n.total))) ) ) * -(1)) AS `total`,
        `p`.`tipoprodserv` AS `tipoprodserv`,
        `n`.`nnfe` AS `nnfe`,
        `i`.`vlritem` AS `vlritem`
FROM
    ((((((`nf` `n`
JOIN `nfitem` `i` ON (((`i`.`idnf` = `n`.`idnf`)
    AND (`i`.`nfe` = 'Y'))))
JOIN `tipoprodserv` `p` ON ((`p`.`idtipoprodserv` = `i`.`idtipoprodserv`)))
JOIN `contaitem` `c` ON ((`c`.`idcontaitem` = `i`.`idcontaitem`)))
JOIN `contapagar` `cp` ON (((`cp`.`idobjeto` = `n`.`idnf`)
    AND (`cp`.`tipoobjeto` = 'nf'))))
LEFT JOIN `prodserv` `ps` ON ((`ps`.`idprodserv` = `i`.`idprodserv`)))
JOIN `empresa` `e` ON ((`e`.`idempresa` = `n`.`idempresa`)))
WHERE
    ((`cp`.`tipoespecifico` <> 'AGRUPAMENTO')
        AND (`cp`.`status` <> 'INATIVO')
        AND (`cp`.`tipo` = 'D')
        AND (`cp`.`valor` > 0)
        AND (`n`.`tiponf` IN ('S' , 'R'))) 
        AND `cp`.`status` <> 'ABERTO'
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."
        and n.dtemissao BETWEEN DATE_SUB(now(), INTERVAL 1 month) and DATE_ADD(now(), INTERVAL 1 DAY)
        and n.status='CONCLUIDO'
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
        `n`.`idempresa` AS `idempresa`,
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
        ((((IFNULL(`i`.`total`, 0) * (`n`.`total` / ifnull(n.subtotal,n.total))) ) ) * -(1)) AS `total`,
        `p`.`tipoprodserv` AS `tipoprodserv`,
        `n`.`nnfe` AS `nnfe`,
        `i`.`vlritem` AS `vlritem`
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
JOIN `empresa` `e` ON ((`e`.`idempresa` = `n`.`idempresa`)))
WHERE
    ((`cp`.`tipoespecifico` = 'AGRUPAMENTO')
        AND (`cp`.`status` <> 'INATIVO')
        AND (`ci`.`status` <> 'INATIVO')
        AND (`cp`.`tipo` = 'D')
        AND (`cp`.`valor` > 0)
        AND (`n`.`tiponf` IN ('S' , 'R')))
        AND `cp`.`status` <> 'ABERTO'
        AND `n`.`idempresa` = ".$idempresa." 
        ".$anddesp."
        and n.dtemissao BETWEEN DATE_SUB(now(), INTERVAL 1 month) and DATE_ADD(now(), INTERVAL 1 DAY)
        and n.status='CONCLUIDO'
        and `i`.`qtd` > 0
) `a`
LEFT JOIN `rateioitem` `ri` ON (((`ri`.`idobjeto` = `a`.`idnfitem`)
    AND (`ri`.`tipoobjeto` = 'nfitem'))))
LEFT JOIN `rateioitemdest` `rid` ON ((`rid`.`idrateioitem` = `ri`.`idrateioitem`)))
LEFT JOIN `unidade` `u` ON (((`u`.`idunidade` = `rid`.`idobjeto`)
    AND (`rid`.`tipoobjeto` = 'unidade'))))
LEFT JOIN `empresa` `e` ON ((`e`.`idempresa` = `u`.`idempresa`))))
WHERE
(`a`.`somarelatorio` = 'Y' ".$stridcontaitem." )";


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


<div class="row ocultar">
    <div class="col-md-4" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Filtros para Listagem </div>
        <div class="panel-body" >
        <div class="row"> 
	    <div class="col-md-12">
	    <table>
        <tr>
            <td  align="right">Empresa: </td>
            <td></td>
		    <td colspan="10">
				<select name="idempresa" name="idempresa" onchange="selecionarAgencia(this)">
					<?
                    $sql ='select * from (SELECT idempresa,nomefantasia from empresa where idempresa in (select idempresa from matrizconf where idmatriz='.cb::idempresa().') and status = "ATIVO" and exists
                    (select 1 from objempresa oe where oe.empresa = empresa.idempresa and oe.objeto = "pessoa" and oe.idobjeto = '.$_SESSION["SESSAO"]["IDPESSOA"].' )
                    UNION
                    SELECT idempresa,nomefantasia from empresa where idempresa ='.cb::idempresa().') a order by idempresa;';

					fillselect($sql,$idempresa);
					?>				
				</select>
			</td>
        </tr>
        <tr>
		<td align="right">Agência:</td> 
            <td></td>
		<td colspan="10">
          

            <select  name="idagencia"  id="idagencia" campo="idagencia"   class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                <?
                $sqlagencia=getAgenciaArr($idagencia, $idempresa);
                $resag =  d::b()->query($sqlagencia) or die("Falha ao buscar agencias: " . mysqli_error() . "<p>SQL: $sqlagencia");  
               
                $selected = '';
                while($rowempresa=mysqli_fetch_assoc($resag)){
                    $selected = (in_array($rowempresa['idagencia'],explode(",",$idagencia)) != false)?'selected':'';
                    echo '<option '.$selected.' data-tokens="'.retira_acentos($rowempresa['agencia']).'" value="'.$rowempresa['idagencia'].'" >'.$rowempresa['agencia'].'</option>';
                }
                ?>
            </select>

		</td>
	    </tr>
        <tr>
            <td align="right">Categoria:</td>
            <td></td>
            <td colspan="10">
                <select style="width:350px" name="idcontaitem"  id="picker_grupoes"  class="selectpicker valoresselect"  data-actions-box="true" multiple="multiple" data-live-search="true">
                <?$arcontaitem = explode(',',$_idcontaitem);  

                
                    $sqlm="SELECT distinct
                    c.idcontaitem,c.contaitem
                    FROM contaitem c
                    JOIN objetovinculo ov ON ov.idobjetovinc = c.idcontaitem AND ov.tipoobjetovinc = 'contaitem' AND ov.idobjeto in (".getModsUsr("LPS").") AND ov.tipoobjeto = '_lp'
                        WHERE c.status = 'ATIVO'
                        ".share::otipo('cb::usr')::contaitemlp("c.idcontaitem")."
                        order by contaitem";
                    $resm =  d::b()->query($sqlm)  or die("Erro buscar funcionarios 1 sql:".$sqlm);
                    while ($rowm = mysqli_fetch_assoc($resm)) {
                        if (in_array($rowm['idcontaitem'],$arcontaitem)){
                                $selected= 'selected';
                        }else{
                                $selected= '';
                        }

                        echo '<option data-tokens="'.retira_acentos($rowm['contaitem']).'" value="'.$rowm['idcontaitem'].'" '.$selected.' >'.$rowm['contaitem'].'</option>'; 
                    }?>
                </select>  
                <input type="hidden" name="sel_picker_idcontaitem" id="sel_picker_idcontaitem" value="<?=$_idcontaitem?>">
                
            </td>
        </tr>   
          <tr>
		<td class="rotulo">Final do Período</td>
		<td></td>
		<td><input autocomplete="off" name="vencimento_2" vpar="" id="vencimento_2"class="calendario" size="10" style="width: 90px;" value="<?=$vencimento_2?>" autocomplete="off"></td>
	    </tr>                              
	    <!--tr>
		<td class="rotulo">Período</td>
		<td><font class="9graybold">entre</font></td>
		<td><input autocomplete="off" name="vencimento_1" vpar="" id="vencimento_1" class="calendario" size="10" style="width: 90px;" value="<?=$vencimento_1?>" autocomplete="off"></td>
		<td><font class="9graybold">&nbsp;e&nbsp;</font></td>
		<td><input autocomplete="off" name="vencimento_2" vpar="" id="vencimento_2"class="calendario" size="10" style="width: 90px;" value="<?=$vencimento_2?>" autocomplete="off"></td>
	    </tr --> 
        <tr>
            <td align="right">Visualização:</td> 
            <td></td>
            <td colspan="10">
                <select name="pesquisa"  id="pesquisa" >
                    <?fillselect("select 'UNIDADE','Unidade' union select 'CENTROCUSTO','Centro de Custo'",$pesquisa);?>
                </select>	
            </td>
	    </tr>
            
        <!--tr>
		<td align="right">Departamento:</td> 
        <td></td>
		<td colspan="10">
		    <select name="idsgdepartamento"  id="idsgdepartamento" >
                    <option value=""></option>
			<?fillselect("select idsgdepartamento,departamento 
                            from sgdepartamento 
                            where status = 'ATIVO'   ".getidempresa('idempresa','prodserv')." order by departamento",$idsgdepartamento);?>
		    </select>	
            
		</td>
	    </tr-->     
	    </table>	
            <div class="row"> 
                <div class="col-md-9"></div>
                <div class="col-md-2">
                <button id="cbPesquisar" title="Pesquisar" class="btn btn-default btn-primary" onclick="pesquisar(this)">
                    <span class="fa fa-search"></span>
                </button> 
                </div>
                <div class="col-md-1">
                <!-- button title="Relatório" class="btn btn-default btn-primary" onclick="relatorio()">
                    <span class="fa fa-bars"></span>
                </!-->
                <button style="margin-left: 10px;" title="Atualizar Rateio do almoxarifado" class="btn btn-default btn-primary" onclick="atualizar(this)">
                    <span id="btatualizar" class="fa fa-refresh"></span>
                </button>               
               
                <!-- button title="Faturas com Divergência" class="btn btn-default btn-primary" onclick="comparar()">
                    <span class="fa fa-info-circle"></span>
                </button-->
                </div>	   
            </div>
        </div>

</div>
        </div>
           
        </div>
        </div>
        <div class="col-md-3" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Rateio </div>
        <div class="panel-body" >
        <div class="row"> 
	 

	    <div class="col-md-12">
        <?/* desativado vencimento
        if((!empty($vencimento_1) and !empty($vencimento_2)) ){
            $dataini = validadate($vencimento_1);
            $datafim = validadate($vencimento_2);    
            $sql = "   SELECT
                            empresa,
                            sum(v.rateio) as rateio ,
                            round(sum(rateio) * 100.0 / sum(sum(rateio)) Over(),0) as percentual
                        FROM
                            (".$vw8despesas.") v
                        join empresa e on e.idempresa = idempresarateio 
                        group by 
                            idempresarateio
                        order by  
                        idempresarateio,tipoobjeto,empresa";
   
            $res =  d::b()->query($sql) or die("Falha ao pesquisar despesasd: " . mysqli_error(d::b()) . "<p>SQL: $sql");  
            
            ?>
        <table style="width:100%" class="table-striped" style="font-size:9px !important">
           <?
              while($row=mysqli_fetch_assoc($res)){
                $t_rateio = $t_rateio + $row['rateio'];
            ?>
            <?if(!empty($idempresa)){?>
            <tr>
                <td style="font-size:9px !important"><?=$row['empresa'];?></td>
                <td style="font-size:9px !important" align="right"><?=$row['percentual'];?>%</td>
                <td style="font-size:9px !important"  align="right">R$ <?=number_format(tratanumero((double)$row['rateio']), 2, ',', '.');?></td>
            </tr>
           <? } ?>
            
        
        <?}
        ?>
        
        </table> 
        
        <table style="width:100%" class="table-striped">
            <?if(!empty($idempresa)){?>
            <tr style="background:#ddd">
                <td style="font-size:9px !important" ><b>TOTAL:</b></td>
                <td style="font-size:9px !important"  class='nowrap' align="right"><b>R$ <?=number_format(tratanumero((double)$t_rateio), 2, ',', '.');?></b></td>
            </tr>
           <? } ?>
</table>   
        <?}
        
        */
        ?>



       

        </div>
        
        </div>
        </div>
           
        </div>
        </div>
    <div class="col-md-3" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Dados Financeiros</div>
        <div class="panel-body" >
        <div class="row"> 
	 
        
	    <div class="col-md-12">
        <?
        /* desativado vencimento
        if((!empty($vencimento_1) and !empty($vencimento_2)) ){?>
        <table style="width:100%" class="table-striped" >
           
            <?if(!empty($idempresa)){?>


            <?
$sqlFat="select  sum(totalnf) as totalnf from(
select totalnf from
vw8pedidofaturamento where 
dtemissao between '".$dataini ."' and '".$datafim ."' 
and idempresa=".$idempresa." 
".$andagencia."
AND natoptipo = 'venda'
AND status IN ('ENVIAR' , 'ENVIADO', 'TRANSFERIDO', 'CONCLUIDO')
and nnfe is not null
and tiponf = 'V' GROUP BY idnf) a ";
$resFat =  d::b()->query($sqlFat) or die("Falha ao pesquisar dados de Faturamento (P): " .mysqli_error(d::b()). "<p>SQL: $sqlFat");

$rowFat=mysqli_fetch_assoc($resFat);
            ?>
            <tr style="font-size:9px">
                <td style="font-size:9px !important">FATURAMENTO (PROD):</td>
                <td style="font-size:9px !important" class='totalempresa nowrap' align="right"></td>
                <td style="font-size:9px !important" class='totalempresa nowrap' align="right">R$ <?=number_format(tratanumero((double)$rowFat['totalnf']), 2, ',', '.')?></td>
            </tr>
           
  
            <?
            
$sqlFatS="select  sum(total) as total from vwnf where 
emissao between '".$dataini ."' and '".$datafim ."' 
and idempresa=".$idempresa." 
".$andagencia."
and status in ('FATURADO','CONCLUIDO')";
$resFatS =  d::b()->query($sqlFatS) or die("Falha ao pesquisar dados de Faturamento (S): " .mysqli_error(d::b()). "<p>SQL: $sqlFatS");

$rowFatS=mysqli_fetch_assoc($resFatS);
            ?>
            <tr style="font-size:9px">
                <td style="font-size:9px !important">FATURAMENTO (SERV):</td>
                <td style="font-size:9px !important" class='totalempresa nowrap' align="right"></td>
                <td style="font-size:9px !important" class='totalempresa nowrap' align="right">R$ <?=number_format(tratanumero((double)$rowFatS['total']), 2, ',', '.');?></td>
            </tr>
            <?
 $sqlDes="select  sum(total) as total from (
    SELECT 
    SUM(total) AS total
FROM
    (SELECT 
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
            `i`.`vlritem` AS `vlritem`
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
            AND (`cp`.`status` <> 'INATIVO')
            AND (`cp`.`tipo` = 'D')
            AND (`cp`.`valor` > 0)
            AND (`n`.`tiponf` NOT IN ('S' , 'R')))
            AND `cp`.`status` <> 'ABERTO'
            AND `cp`.`idempresa` = ".$idempresa." 
            ".$anddesp."
            and n.dtemissao BETWEEN  '".$dataini ." 01:00:00' and '".$datafim ." 23:59:00'
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
            `cp`.`idagencia` AS `idagencia`,
            `cp`.`idcontapagar` AS `idcontapagar`,
            `cp`.`parcela` AS `parcela`,
            `cp`.`parcelas` AS `parcelas`,
            `p`.`idtipoprodserv` AS `idtipoprodserv`,
            `i`.`idnfitem` AS `idnfitem`,
            `i`.`qtd` AS `qtd`,
            IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
            (((((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) + (((IFNULL(`i`.`total`, 0) + IFNULL(`i`.`valipi`, 0)) / (`n`.`total` - `n`.`frete`)) * `n`.`frete`)) / `n`.`total`) * `ci`.`valor`) * -(1)) AS `total`,
            `p`.`tipoprodserv` AS `tipoprodserv`,
            `n`.`nnfe` AS `nnfe`,
            `i`.`vlritem` AS `vlritem`
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
            AND (`cp`.`status` <> 'INATIVO')
            AND (`ci`.`status` <> 'INATIVO')
            AND (`cp`.`tipo` = 'D')
            AND (`cp`.`valor` > 0)
            AND (`n`.`tiponf` NOT IN ('S' , 'R')))
            AND `cp`.`status` <> 'ABERTO'
            AND `cp`.`idempresa` = ".$idempresa." 
            ".$anddesp."
            and n.dtemissao BETWEEN  '".$dataini ." 01:00:00' and '".$datafim ." 23:59:00'
          
            AND `i`.`qtd` > 0 UNION ALL SELECT 
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
            `i`.`vlritem` AS `vlritem`
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
            AND (`cp`.`status` <> 'INATIVO')
            AND (`cp`.`tipo` = 'D')
            AND (`cp`.`valor` > 0)
            AND (`n`.`tiponf` IN ('S' , 'R')))
            AND `cp`.`status` <> 'ABERTO'
            AND `cp`.`idempresa` = ".$idempresa." 
            ".$anddesp."
            and n.dtemissao BETWEEN  '".$dataini ." 01:00:00' and '".$datafim ." 23:59:00'
         
            AND `i`.`qtd` > 0 UNION ALL SELECT 
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
            `cp`.`idagencia` AS `idagencia`,
            `cp`.`idcontapagar` AS `idcontapagar`,
            `cp`.`parcela` AS `parcela`,
            `cp`.`parcelas` AS `parcelas`,
            `p`.`idtipoprodserv` AS `idtipoprodserv`,
            `i`.`idnfitem` AS `idnfitem`,
            `i`.`qtd` AS `qtd`,
            IFNULL(`i`.`un`, `ps`.`un`) AS `un`,
            ((((IFNULL(`i`.`total`, 0) * (`n`.`total` / ifnull(n.subtotal,n.total) )) / `n`.`total`) * `ci`.`valor`) * -(1)) AS `total`,
            `p`.`tipoprodserv` AS `tipoprodserv`,
            `n`.`nnfe` AS `nnfe`,
            `i`.`vlritem` AS `vlritem`
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
            AND (`cp`.`status` <> 'INATIVO')
            AND (`ci`.`status` <> 'INATIVO')
            AND (`cp`.`tipo` = 'D')
            AND (`cp`.`valor` > 0)
            AND (`n`.`tiponf` IN ('S' , 'R')))
            AND `cp`.`status` <> 'ABERTO'
            AND `cp`.`idempresa` = ".$idempresa." 
            ".$anddesp."
            and n.dtemissao BETWEEN  '".$dataini ." 01:00:00' and '".$datafim ." 23:59:00'
        
            AND `i`.`qtd` > 0) a where (`a`.`somarelatorio` = 'Y')

) a";

echo "<!--  sqlDes ". $sqlDes." -->";
$resDes =  d::b()->query($sqlDes) or die("Falha ao pesquisar dados de Despesasx: " .mysqli_error(d::b()). "<p>SQL: $sqlDes");

$rowDes=mysqli_fetch_assoc($resDes);
            ?>        
            

            <tr style="font-size:9px">
                <td style="font-size:9px !important" >DESPESAS:</td>
                <td  style="font-size:9px !important"  class='totaloempresa nowrap' align="right"></td>
                <td  style="font-size:9px !important"  class='totaloempresa nowrap' align="right">R$ <?=number_format(tratanumero((double)$rowDes['total']), 2, ',', '.');?></td>
               
            </tr>

            <?
                $v_faturamento = $rowFat['totalnf'] +  $rowFatS['total'];
                $v_fintotal =  $rowFat['totalnf'] +  $rowFatS['total'] +  $rowDes['total'];
            ?>

            <tr style="background:#ddd">
                <td style="font-size:9px !important" ><b>TOTAL:</b></td>
                <td style="font-size:9px !important"  align="right"><b></b></td>
                <td style="font-size:9px !important"  align="right"><b>R$ <?=number_format(tratanumero((double)$v_fintotal), 2, ',', '.');?></b></td>
            </tr>
            
            <?}?>
            
        </table>
        <?}
        */
        ?>
        </div>
        
        </div>
        </div>
           
        </div>
        </div>
<? /*
    <div class="col-md-3 hide" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Filtros para Listagem </div>
        <div class="panel-body" >
        <div class="row"> 
	 
	 
	 
        <div class="col-md-12">
        <?if((!empty($vencimento_1) and !empty($vencimento_2)) ){
         $dataini = validadate($vencimento_1);
         $datafim = validadate($vencimento_2);    
        ?>
        <table>
            
        <?
        $sqlal="select  
                    ifnull(sum(rateio),0) as valor
                    from (
                            select  
                                l.vlrlote,round(((ifnull(l.vlrlote,0)*(l.qtdprod))*(f.qtd/l.qtdprod))/ifnull(n.parcelas,1),2) as rateio,(f.qtd/l.qtdprod),l.qtdprod,f.qtd,n.parcelas
                            from contapagar cp   
                                join contapagaritem cpi on(cp.idcontapagar=cpi.idcontapagar)
                                join nf n  on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf'and n.tiponf in ('T','S','M','E','R','D','B','C')) 
                                join nfitem ni  on(ni.idnf=n.idnf )
                                join lote l  on(l.idnfitem=ni.idnfitem and l.status!='CANCELADO')
                                join prodserv p on(l.idprodserv=p.idprodserv)
                                join lotefracao f on(f.idlote=l.idlote and f.status='DISPONIVEL' and f.qtd>0)
                                join unidade u on(u.idunidade=f.idunidade and u.idtipounidade=3)			
                                
                            where p.tipo='PRODUTO'
                            and cp.valor>0 and cp.status!='INATIVO'
                            and cp.tipo='D'
                                    and cp.datareceb between '".$dataini ."' and '".$datafim ."' 
                                    and cp.idempresa=".$idempresa."
                        
                    ) as u";
        $resal =  d::b()->query($sqlal) or die("Falha ao pesquisar produtos no almoxarifado: " . mysqli_error() . "<p>SQL: $sqlal");
      
        $rowal=mysqli_fetch_assoc($resal);
?>
        <tr>
            <td>Pagos Pela Empresa no Almoxarifado:</td>
            <td class='nowrap'  id="pagosempresa" valor="<?=$rowal['valor']?>" align="right">R$  <?=number_format(tratanumero((double)$rowal['valor']), 2, ',', '.');?></td>
            <td></td>
        </tr>
<?
          
        $sqldes="select  
                ifnull(sum(rateio),0) as valor
                from (
                        select  
                            round(((ifnull(l.vlrlote,0)*(l.qtdprod))*(c.qtdd/l.qtdprod))/ifnull(n.parcelas,1),2) as rateio
                        from contapagar cp   
                            join contapagaritem cpi on(cp.idcontapagar=cpi.idcontapagar)
                            join nf n  on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf'and n.tiponf in ('T','S','M','E','R','D','B','C')) 
                            join nfitem ni  on(ni.idnf=n.idnf )
                            join lote l  on(l.idnfitem=ni.idnfitem and l.status!='CANCELADO')
                            join prodserv p on(l.idprodserv=p.idprodserv)
                            join lotefracao f on(f.idlote=l.idlote )
                            join unidade u on(u.idunidade=f.idunidade and u.idtipounidade=3)
                            join lotecons c on(c.idlote=l.idlote and c.status!='INATIVO' and c.idlotefracao = f.idlotefracao and c.tipoobjeto is null and c.idobjeto is null and c.qtdd>0)
                            
                        where p.tipo='PRODUTO'
                        and cp.valor>0 and cp.status!='INATIVO'
                        and cp.tipo='D'
                        and cp.datareceb between '".$dataini ."' and '".$datafim ."' 
                        and cp.idempresa=".$idempresa."
                    
                ) as u";
        $resdes =  d::b()->query($sqldes) or die("Falha ao pesquisar produtos no almoxarifado descartados: " . mysqli_error() . "<p>SQL: $sqldes");

        $rowdes=mysqli_fetch_assoc($resdes);
        ?>
        <tr>
        <td>Descartados no Almoxarifado:</td>
        <td class='nowrap'  id="descateempresa" valor="<?=$rowdes['valor']?>" align="right">R$  <?=number_format(tratanumero((double)$rowdes['valor']), 2, ',', '.');?></td>
        <td></td>
        </tr>
        <?
              
        $sqlv="select  
        ifnull(sum(rateio),0) as valor
        from (
                select  
                    round(((ifnull(l.vlrlote,0)*(l.qtdprod))*(c.qtdd/l.qtdprod))/ifnull(n.parcelas,1),2) as rateio
                from contapagar cp   
                    join contapagaritem cpi on(cp.idcontapagar=cpi.idcontapagar)
                    join nf n  on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf'and n.tiponf in ('T','S','M','E','R','D','B','C')) 
                    join nfitem ni  on(ni.idnf=n.idnf )
                    join lote l  on(l.idnfitem=ni.idnfitem and l.status!='CANCELADO')
                    join prodserv p on(l.idprodserv=p.idprodserv)
                    join lotefracao f on(f.idlote=l.idlote )
                    join unidade u on(u.idunidade=f.idunidade and u.idtipounidade=3)
                    join lotecons c on(c.idlote=l.idlote and c.status!='INATIVO' and c.idlotefracao = f.idlotefracao and c.tipoobjeto = 'nfitem'
                    and c.idobjeto is not null and c.qtdd>0)
                    
                where p.tipo='PRODUTO'
                and cp.tipo='D'
                and cp.valor>0 and cp.status!='INATIVO'
                and cp.datareceb between '".$dataini ."' and '".$datafim ."' 
                and cp.idempresa=".$idempresa."
            
        ) as u";
        $resv =  d::b()->query($sqlv) or die("Falha ao pesquisar produtos no almoxarifado vendido: " . mysqli_error() . "<p>SQL: $sqlv");

        $rowv=mysqli_fetch_assoc($resv);
        ?>
        <tr>
        <td>Pedido de Materiais:</td>
        <td class='nowrap'  id="materialempresa" valor="<?=$rowv['valor']?>" align="right">R$  <?=number_format(tratanumero((double)$rowv['valor']), 2, ',', '.');?></td>
        <td></td>
        </tr>
        <?
              
        $sqlv="select  
        ifnull(sum(rateio),0) as valor
        from (
                select  
                    round(((ifnull(l.vlrlote,0)*(l.qtdprod))*(c.qtdd/l.qtdprod))/ifnull(n.parcelas,1),2) as rateio
                from contapagar cp   
                    join contapagaritem cpi on(cp.idcontapagar=cpi.idcontapagar)
                    join nf n  on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf'and n.tiponf in ('T','S','M','E','R','D','B','C')) 
                    join nfitem ni  on(ni.idnf=n.idnf )
                    join lote l  on(l.idnfitem=ni.idnfitem and l.status!='CANCELADO')
                    join prodserv p on(l.idprodserv=p.idprodserv)
                    join lotefracao f on(f.idlote=l.idlote )
                    join unidade u on(u.idunidade=f.idunidade and u.idtipounidade=3)
                    join lotecons c on(c.idlote=l.idlote and c.status!='INATIVO' and c.idlotefracao = f.idlotefracao and c.tipoobjetoconsumoespec ='loteativ' and c.idobjetoconsumoespec is not null and c.qtdd>0)
                    
                where p.tipo='PRODUTO'
                and cp.tipo='D'
                and cp.valor>0 and cp.status!='INATIVO'
                and cp.datareceb between '".$dataini ."' and '".$datafim ."' 
                and cp.idempresa=".$idempresa."
            
        ) as u";
        $resv =  d::b()->query($sqlv) or die("Falha ao pesquisar produtos no almoxarifado produção: " . mysqli_error() . "<p>SQL: $sqlv");

        $rowv=mysqli_fetch_assoc($resv);
        ?>
        <tr>
        <td>Saída do Almoxarifado (Produção):</td>
        <td class='nowrap'  id="almoxarifadopd" valor="<?=$rowv['valor']?>" align="right">R$  <?=number_format(tratanumero((double)$rowv['valor']), 2, ',', '.');?></td>
        <td></td>
        </tr>
        <tr>
            <td>Total</td>
            <td class='nowrap'  id="somavaloresempresa" align="right">R$ 0,0</td>
            <td></td>
        </tr>
        </table>
        <?}?>
        </div>

</div>
        </div>
           
        </div>
        </div>
 
 */ ?> 
</div>
<div class="row sticky-div">
    <div class="col-md-12">
        <button type="button" class="btn btn-dafault" style="margin:0px 4px;color:#666;font-size: 8px !important;float:left;border:none" title="Faturamento %" 
        onclick="mostraPercentual(this,'somatorio_percentual_faturamento');">
            <i class="fa fa-eye fa-1x"></i>FATURAMENTO
        </button> 

        <button type="button" class="btn btn-dafault" style="margin:0px 4px;color:#666;font-size: 8px !important;float:left;border:none" title="Editar rateio em lote" 
        onclick="mostraPercentual(this,'somatorio_percentual');" >
            <i class="fa fa-eye  fa-1x"></i>DESPESA
        </button> 
        <button id="ratear"  type="button" class="btn btn-primary hidden" style="margin:0px 4px;color:#666;font-size: 8px !important;float:right;background-color:#ed0e0e40  !important;border:none" title="Editar rateio em lote" onclick="modalRateio(this,'nfitem','RATEAR');" idrateioitem="<?=$row['idrateioitem']?>" >
            <i class="fa fa-pencil fa-1x"></i>RATEAR
        </button> 
        <button  id="cobrar"  type="button" class="btn btn-primary hidden" style="margin:0px 4px;color:#666;font-size: 8px !important;float:right;background-color:#4878df !important;border:none" title="Cobrar rateio em lote" onclick="modalRateio(this,'emlote','COBRAR');" idrateioitem="<?=$row['idrateioitem']?>" >
            <i class="fa fa-money fa-1x"></i>COBRAR
        </button> 
        <button  id="editar"  type="button" class="btn btn-primary hidden" style="margin:0px 4px;color:#666;font-size: 8px !important;float:right;background-color:#7dc937  !important;border:none" title="Editar rateio em lote" onclick="modalRateio(this,'emlote','RATEAR');" idrateioitem="<?=$row['idrateioitem']?>" >
            <i class="fa fa-pencil fa-1x"></i>EDITAR
        </button> 
    </div>
</div>
<?
function corpo($res,$tiporel,$idempresa){
    global $totalgeral,$arrvtipo,$arrvcontaitem,$arrvemp,$nfclass,$prodservclass,$totalempresa,$totaloempresa;
    global $v_empresa, $v_idobjeto, $v_contaitem, $v_tipoprodserv, $v_total;
    $ires = mysqli_num_rows($res);  
    if($tiporel=='rateio'){
        $back="style='background-color: #c5dfad !important'";
        $border ="style='border-color: #c5dfad !important '";
        $str="<b>Despesas rateadas</b>";
        $class = "emlote";
    }elseif($tiporel=='rateioalm'){
        $back="style='background-color: #b6cbf9 !important'";
        $border ="style='border-color: #b6cbf9 !important '";
        $str="<b>Despesas almoxarifado</b>";
        $class = "emlote";
    }else{
        $tiporel = 'aratiar';
        $back="style='background-color: #ed0e0e40  !important'";
        $border="style='border-color: #ed0e0e40  !important'";
        $str="<b>Despesas sem rateio</b>";
        $class = "nfitem";
    }

?>



    <div class="panel panel-default agrupamentorateio" <?=$border?> >
        <div class="panel-heading cabecalho" <?=$back?>>
            <table style="width:100%">
                <tr>
                <td style="width:70%">
                    <?=$str?> 
                </td>
                <td style="width:25%" >
                    <div class='somatorio_valor valor_total_<?=$tiporel?>'>0</div>
                
                    <div class='somatorio_percentual percentual_total_<?=$tiporel?>'>0</div>

                    <div class='somatorio_percentual_faturamento percentualfaturamento_total_<?=$tiporel?>'>0</div>
                </td>
                </tr>
            </table>
        </div>
        <div class="panel-body" >
    
<?if($ires>0){
    $vtipo=0;
    $vempresarateio=0;
    $vempresa=0;
   //$rempresa=traduzid('empresa','idempresa','empresa',$_SESSION["SESSAO"]["IDEMPRESA"]);
   //$arrvemp=array();
   //$arrvtipo=array();
    while($row=mysqli_fetch_assoc($res)){
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
                                <input type='checkbox' class="todosinicio pointer <?=$tiporel;?>"  title="Selecionar todos os itens do(a)  <?=$row['empresa']?> " onclick="marcarTodos(this)">
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

                                <div  class='somatorio_percentual_faturamento percentualfaturamento_empresarateio_<?=$row['idempresarateio'];?><?=$tiporel?>'>0
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
                                            <?=$row['empresa']?> 
                                        </div>
                                            
                                    </td>
                                    <td style="text-align: right;width:23%" >
                                        <div class='somatorio_valor valor_objeto_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>'>0</div>
                            
                                        <div class='somatorio_percentual percentual_objeto_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>'>0</div>
                                
                                        <div class='somatorio_percentual_faturamento percentualfaturamento_objeto_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>'>0</div>
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

                                                <div class='somatorio_percentual percentualfaturamento_contaitem_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>_<?=$row['idcontaitem']?>'>
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
                                                        <input type='checkbox' class="todos pointer <?=$tiporel;?>"  title="Selecionar itens do tipo  <?=$row['tipoprodserv']?>" onclick="marcarTodos(this)">
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

                                                        <div class='somatorio_percentual percentualfaturamento_tipoprodserv_<?=$row['idempresarateio']?><?=$tiporel?>_<?=$row['idobjeto']?><?=$row['tipoobjeto']?>_<?=$row['idcontaitem']?>_<?=$row['idtipoprodserv']?> nowrap'>
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
                                                    <th style="width:5%;text-align:left">Empresa</th>
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
                                                        <input type="checkbox" class="<?=$class;?> <?=$tiporel;?> changeacao" acao="i" atname="checked[<?=$i?>]"  data-class="<?=$class;?>"  data-idrateioitemdest="<?=$idrateioitemdest;?>" value="<?if($class=='nfitem'){echo $row['idtipo'];}else{echo $row['idrateioitemdest'];}?>"  style="border:0px" onclick="liberaBotoes()">
                                                     <?}?>
                                                        <input type="hidden" name="_<?=$i?>_u_rateioitemdest_idrateioitemdest" value="<?=$row['idrateioitemdest']?>">
                                                    </td>
                                                    <td title="Item"  style="text-align: right;">              
                                                        <?=number_format(tratanumero((double)$row['qtd']), 2, ',', '.'); ?></td>
                                                    <td><?=$row['un']?></td>
                                                    <td style="text-align: left;"><?=$row['descr']?></td>              
                                                    <td><?=traduzid('empresa','idempresa','sigla',$row['idempresa'])?></td>
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
                                                    <?if($row['tipo']=='lotecons'){?>				 				
                                                        <div id="consumolote_<?=$row['idrateioitemdest']?>" style="display: none">
                                                            <?=$prodservclass->listalotecons($row['idtipo']);?>
                                                        </div>
                                                        <a title="Histórico" class=" hoverazul  pointer" onclick="showhistoricolote(<?=$row['idtipo']?><?=$row['idrateioitemdest']?>);"> R$ <?=number_format(tratanumero((double)$row['rateio']), 2, ',', '.');?></a>
                                                        <?}elseif($row['tipo']=='nfitem'){?>
                                                        <div id="consumolote_<?=$row['idtipo']?><?=$row['idrateioitemdest']?>" style="display: none">
                                                            <?=$nfclass->listanfitem($row['idtipo']);?>
                                                        </div>
                                                        <a style="margin-right:65px;" title="Compra" class=" hoverazul  pointer" onclick="showhistoricoitem(<?=$row['idtipo']?><?=$row['idrateioitemdest']?>);">  R$ <?=number_format(tratanumero((double)$row['rateio']), 2, ',', '.');?></a>
                                            
                                                        <?}?>
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
<?  



}// fim corpo
/*
 * colocar condição para executar select
 */
if($_GET and !empty($idempresa)  and (!empty($vencimento_2)) or (!empty($idprodserv))){

  /*  
    if (!empty($vencimento_1) or !empty($vencimento_2)){
        $dataini = validadate($vencimento_1);
        $datafim = validadate($vencimento_2);

        if ($dataini and $datafim){
               // $clausula .= " and ( n.dtemissao  BETWEEN '" . $dataini ." 00:00:00' and '" .$datafim ." 23:59:59')"."  ";
               // $clausulac.=" and (c.criadoem  BETWEEN  '" . $dataini ." 00:00:00' and '" .$datafim ." 23:59:59')  ";
                $clausula.=" and datareceb between '".$dataini ."' and '".$datafim ."' ";
        }else{
                die ("Datas n&atilde;o V&aacute;lidas!");
        }
    }
    */
                $sql="select  
                tipo,idempresa,qtd,un,contaitem,idcontaitem,idtipo,idrateio,idrateioitem,idrateioitemdest,idnf,nnfe,idobjeto,tipoobjeto,
                idtipoprodserv,tipoprodserv,descr,vlrlote,rateio,valor,empresa,dtemissao,corsistema,rateado,'aratiar' as idempresarateio, siglarateio
            from (
                    SELECT 
                        'nfitem' AS tipo,
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
                            datareceb AS dtemissao,
                            corsistema,
                            rateado,
                            idempresarateio,
                            siglarateio
                    FROM
                        (".$vw8despesas_semrateio .") v
                    WHERE
                        rateado = 'N'
            ) as u
            order by idempresarateio,tipoobjeto,empresa,idobjeto,contaitem,tipoprodserv,descr,dtemissao";
//die('<pre>'.$sql.'</pre>');
                echo "<!--";
                echo $sql;
                echo "-->";
                if (!empty($sql)){
                    $res =  d::b()->query($sql) or die("Falha ao pesquisar consumos sem rateio: " . mysqli_error() . "<pre>SQL: $sql</pre>");
                    corpo($res,'aratiar',$idempresa);      
                }

                $sql="select  
                tipo,idempresa,qtd,un,contaitem,idcontaitem,idtipo,idrateio,idrateioitem,idrateioitemdest,idnf,nnfe,idobjeto,tipoobjeto,
                idtipoprodserv,tipoprodserv,descr,vlrlote,rateio,valor,empresa,dtemissao,corsistema,rateado, idempresarateio as idempresarateio,siglarateio
            from (


            SELECT 
                'nfitem' AS tipo,
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
                    datareceb AS dtemissao,
                    corsistema,
                    rateado,
                    idempresarateio,
                    siglarateio
            FROM
                (".$vw8despesasalm.") v
            WHERE
                rateado = 'Y'
        --        and idempresarateio = 4
          
            
                                         
            ) as u
            order by idempresarateio,tipoobjeto,empresa,idobjeto,contaitem,tipoprodserv,descr,dtemissao";


//die('<pre>'.$sql.'</pre>');
                echo "<!--";
                echo $sql;
                echo "-->";
                if (!empty($sql)){
                    $res =  d::b()->query($sql) or die("Falha ao pesquisar consumos sem rateio alm: " . mysqli_error() . "<pre>SQL: $sql</pre>");
                    corpo($res,'rateioalm',$idempresa);      
                }


                        $sql="select  
                                tipo,idempresa,qtd,un,contaitem,idcontaitem,idtipo,idrateio,idrateioitem,idrateioitemdest,idnf,nnfe,idobjeto,tipoobjeto,
                                idtipoprodserv,tipoprodserv,descr,vlrlote,rateio,valor,empresa,dtemissao,corsistema,rateado, idempresarateio as idempresarateio,siglarateio
                            from (


                            SELECT 
                                'nfitem' AS tipo,
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
                                    datareceb AS dtemissao,
                                    corsistema,
                                    rateado,
                                    idempresarateio,
                                    siglarateio
                            FROM
                                (".$vw8despesas.") v
                            WHERE
                                rateado = 'Y'
                        --        and idempresarateio = 4
                          
                            
                                                         
                            ) as u
                            order by idempresarateio,tipoobjeto,empresa,idobjeto,contaitem,tipoprodserv,descr,dtemissao";
                


                echo "<!--";
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
   // $(vthis).addClass( "blink" );
   $(vthis).html('<span class="fa fa-spinner fa-pulse"></span>');
    var vencimento_1 = $("[name=vencimento_1]").val();
    var vencimento_2 = $("[name=vencimento_2]").val();
    var idtipoprodserv = $("[name=idtipoprodserv]").val();
    var idprodserv = $("[name=idprodserv]").val();
    var pesquisa = $("[name=pesquisa]").val();
    var idsgdepartamento=$("[name=idsgdepartamento]").val();
    var idempresa=$("[name=idempresa]").val();
    var idagencia=$("[name=idagencia]").val();
    var idcontaitem=$("#picker_grupoes").val();
    
    
    var str="idempresa="+idempresa+"&idagencia="+idagencia+"&vencimento_2="+vencimento_2+"&idsgdepartamento="+idsgdepartamento+"&_idcontaitem="+idcontaitem+"&pesquisa="+pesquisa ;
    CB.go(str);
}

function relatorio(){
    var vencimento_1 = $("[name=vencimento_1]").val();
    var vencimento_2 = $("[name=vencimento_2]").val();
    var idtipoprodserv = $("[name=idtipoprodserv]").val();
    var idprodserv = $("[name=idprodserv]").val();
    var pesquisa = $("[name=pesquisa]").val();
    var idsgdepartamento=$("[name=idsgdepartamento]").val();
    var idempresa=$("[name=idempresa]").val();
    var str="_idempresa="+idempresa+"&vencimento_1="+vencimento_1+"&vencimento_2="+vencimento_2+"&idsgdepartamento="+idsgdepartamento+"&idtipoprodserv="+idtipoprodserv+"&pesquisa="+pesquisa ;
	
	janelamodal('report/rateioitem.php?_acao=u&'+str);

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

debugger;

    liberaBotoes();
}

function liberaBotoes(){
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



function modalRateio(vthis, tipo,funcao){
    var v_idrateioitemdest = '';
    var virgula = '';
    var v_num;
    var v_ratear = false;
    var v_idempresa=$("[name=idempresa]").val();
    var v_idnfitem = '';
    var v_url;

    if(funcao=="COBRAR"){
        var titulo = "Cobrar Rateio";
    }else{
      var titulo = "Editar Rateio";
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

        v_url = "?_modulo=rateioitemdest&_acao=u&tipo=rateio&funcao="+funcao+"&stidrateioitemdest="+v_idrateioitemdest+"&_idempresa="+v_idempresa;

    }else if(tipo == 'idrateioitemdest'){
         v_idrateioitemdest =$(vthis).attr('idrateioitemdest');
         v_url = "?_modulo=rateioitemdest&_acao=u&tipo=rateio&funcao="+funcao+"&stidrateioitemdest="+v_idrateioitemdest+"&_idempresa="+v_idempresa;
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

        v_url = "?_modulo=rateioitemdest&_acao=u&tipo=nf&idnfitem="+v_idnfitem+"&_idempresa="+v_idempresa;

    


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
        aoFechar: function(){
            $("#_inputmodalrateiosemmodificacao_").remove()
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

    
</script>
