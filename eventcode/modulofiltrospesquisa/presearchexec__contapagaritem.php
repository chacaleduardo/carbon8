<?
if($_GET['_modulo'] == 'contapagaritem'){
	$_SESSION["SEARCH"]["FROM"][0] = "(SELECT 
											`c`.`idcontapagar` AS `idcontapagar`,
											`c`.`idempresa` AS `idempresa`,
											`c`.`idpessoa` AS `idpessoa`,
											`ci`.`contaitem` AS `contaitem`,
											`c`.`idcontaitem` AS `idcontaitem`,
											`c`.`idobjeto` AS `idobjeto`,
											`c`.`tipoobjeto` AS `tipoobjeto`,
											`c`.`idagencia` AS `idagencia`,
											`p`.`nome` AS `nome`,
											`a`.`agencia` AS `agencia`,
											`c`.`tipo` AS `tipo`,
											`c`.`formapagto` AS `formapagto`,
											`c`.`parcela` AS `parcela`,
											`c`.`parcelas` AS `parcelas`,
											`c`.`datareceb` AS `datareceb`,
											SUM(`cpi`.`valor`) AS `valor`,
											`c`.`status` AS `status`,
											`c`.`tipoespecifico` AS `tipoespecifico`,
											`c`.`alteradoem` AS `alteradoem`,
											`c`.`alteradopor` AS `alteradopor`
										FROM
											`contapagar` `c` JOIN `pessoa` `p` ON `p`.`idpessoa` = `c`.`idpessoa`
											JOIN `contaitem` `ci` ON `ci`.`idcontaitem` = `c`.`idcontaitem`
											JOIN `contapagaritem` `cpi` ON `cpi`.`idcontapagar` = `c`.`idcontapagar`
											JOIN `agencia` `a` ON `c`.`idagencia` = `a`.`idagencia`
										WHERE
											`c`.`tipoespecifico` IN ('REPRESENTACAO' , 'AGRUPAMENTO', 'IMPOSTO')
										GROUP BY `c`.`idcontapagar`) _a";
}


?>