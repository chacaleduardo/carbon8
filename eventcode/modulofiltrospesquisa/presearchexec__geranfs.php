<?
//unset($_SESSION["SEARCH"]["FROM"]);

$gexercicio=$_GET["exercicio"];

if(!empty($gexercicio)){
	//Obervacao
	$_SESSION["SEARCH"]["FROM"][0] = "(
		SELECT 
			`p`.`idempresa` AS `idempresa`,
			`p`.`idpessoa` AS `idpessoa`,
			`p`.`nome` AS `nome`,
			`r`.`idresultado` AS `idresultado`,
			`a`.`idamostra` AS `idamostra`,
			COUNT(0) AS `fechados`,
			(SELECT 
					COUNT(0) AS `count(0)`
				FROM
					`vwtipoteste` `t`
					JOIN `resultado` `rr`
					JOIN `amostra` `aa`
					join unidade uu on( uu.idunidade= aa.idunidade)
				WHERE
					((`t`.`idtipoteste` = `rr`.`idtipoteste`)
						AND (`rr`.`status` IN ('ABERTO' , 'PROCESSANDO'))
						AND (`rr`.`idamostra` = `aa`.`idamostra`)
						and ((uu.idtipounidade=1 and rr.cobrar='Y') or ( uu.idtipounidade !=1 and rr.cobrancaobrig='Y'))
						AND (`aa`.`idpessoa` = `p`.`idpessoa`))) AS `abertos`,
			DATE_FORMAT(MIN(`a`.`dataamostra`),
					_utf8mb3 '%d/%m/%Y') AS `menordata`,
			DATE_FORMAT(MAX(`a`.`dataamostra`),
					_utf8mb3 '%d/%m/%Y') AS `maiordata`,
			MAX(`a`.`exercicio`) AS `exercicio`,
			`r`.`cobrar` AS `cobrar`,
			`r`.`alteradoem` AS `alteradoem`
		FROM
			`pessoa` `p`
			JOIN `amostra` `a` 
			JOIN `resultado` `r`
			join unidade u on( u.idunidade= a.idunidade)
		WHERE
			((`p`.`idpessoa` = `a`.`idpessoa`)
				-- AND ((`a`.`idunidade` = 1)	OR (`r`.`cobrancaobrig` = 'Y'))
				and ((u.idtipounidade=1 and r.cobrar='Y') or ( u.idtipounidade !=1 and r.cobrancaobrig='Y'))
				AND (`r`.`idamostra` = `a`.`idamostra`)
				AND (`r`.`status` IN ('ABERTO' , 'PROCESSANDO', 'FECHADO','CONFERIDO', 'ASSINADO'))
				AND EXISTS( SELECT 
					1 AS `1`
				FROM
					(`notafiscal` `nf`
					JOIN `notafiscalitens` `nfi`)
				WHERE
					((`nf`.`idnotafiscal` = `nfi`.`idnotafiscal`)
						AND (`nfi`.`idresultado` = `r`.`idresultado`)))
				IS FALSE)
	AND a.exercicio = ".$gexercicio."
		GROUP BY `p`.`idpessoa` , `p`.`nome` , `r`.`cobrar`
	) _a ";

}

//die()
?>