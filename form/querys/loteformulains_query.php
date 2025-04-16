<?
class LoteFormulaInsQuery
{
	public static function apagarLoteFormulaInsPorIdLote()
	{
		return "DELETE FROM loteformulains WHERE idlote = ?idlote?";
	}

	public static function inserirFormulaInsPorSelect()
	{
		return "INSERT INTO loteformulains (idempresa,
											idlote,
											idprodservformulains,
											idprodserv,
											codprodserv,
											especial,
											fabricado,
											descr,
											descrcurta,
											descrgenerica,
											estmin,
											qtdpadrao,
											qtdpadrao_exp
											,un,
											ord,
											qtdi,
											qtdi_exp,
											qtdpd,
											qtdpd_exp,
											idprodservformula,
											rotulo,
											cor,
											idprodservpai,
											criadopor,
											criadoem,
											alteradopor,
											alteradoem)
									(SELECT ?idempresa?,
											?idlote?,
											i.idprodservformulains,
											i.idprodserv,
											p.codprodserv,
											p.especial,
											p.fabricado,
											p.descr,
											p.descrcurta,
											p.descrgenerica,
											p.estmin,
											IFNULL(f.qtdpadraof, p.qtdpadrao) AS qtdpadrao,
											IFNULL(f.qtdpadraof_exp, p.qtdpadrao_exp) AS qtdpadrao_exp,
											(CASE WHEN p.unconv = NULL THEN p.un
												  WHEN p.unconv = '' THEN p.un
												  ELSE p.un END) AS un,
											i.ord,
											i.qtdi,
											i.qtdi_exp,
											i.qtdpd,
											i.qtdpd_exp,
											f.idprodservformula,
											f.rotulo,
											f.cor,
											f.idprodserv AS idprodservpai,
											'?usuario?',
											SYSDATE(),
											'?usuario?',
											SYSDATE()
									   FROM prodservformulains i JOIN prodservformula f ON (f.idprodservformula = i.idprodservformula AND f.status = 'ATIVO')
									   JOIN prodserv p ON p.idprodserv = i.idprodserv
									  WHERE f.idprodservformula = ?idprodservformula?
									    AND i.status = 'ATIVO')";
	}

	public static function inserirFormulaInsSementes()
	{
		return "INSERT INTO loteformulains (idempresa,
											idlote,
											idprodservformulains,
											idprodserv,
											codprodserv,
											especial,
											fabricado,
											descr,
											descrcurta,
											descrgenerica,
											estmin,
											qtdpadrao,
											qtdpadrao_exp
											,un,
											ord,
											qtdi,
											qtdi_exp,
											qtdpd,
											qtdpd_exp,
											idprodservformula,
											rotulo,
											cor,
											idprodservpai,
											criadopor,
											criadoem,
											alteradopor,
											alteradoem)
									(SELECT ?idempresa?,
											?idlote?,
											i.idprodservformulains,
											i.idprodserv,
											p.codprodserv,
											p.especial,
											p.fabricado,
											p.descr,
											p.descrcurta,
											p.descrgenerica,
											p.estmin,
											IFNULL(f.qtdpadraof, p.qtdpadrao) AS qtdpadrao,
											IFNULL(f.qtdpadraof_exp, p.qtdpadrao_exp) AS qtdpadrao_exp,
											(CASE WHEN p.unconv = NULL THEN p.un
												  WHEN p.unconv = '' THEN p.un
												  ELSE p.un END) AS un,
											i.ord,
											i.qtdi,
											i.qtdi_exp,
											i.qtdpd,
											i.qtdpd_exp,
											f.idprodservformula,
											f.rotulo,
											f.cor,
											f.idprodserv AS idprodservpai,
											'?usuario?',
											SYSDATE(),
											'?usuario?',
											SYSDATE()
									   FROM prodservformula fi JOIN prodservformulains ii ON (fi.idprodservformula = ii.idprodservformula)
									   JOIN prodservformula f ON (f.idprodserv = ii.idprodserv)
									   JOIN prodservformulains i ON (f.idprodservformula = i.idprodservformula AND f.status = 'ATIVO')
									   JOIN prodserv p ON (i.idprodserv = p.idprodserv AND p.especial = 'Y')
									  WHERE fi.idprodservformula = ?idprodservformula?
									    AND i.status = 'ATIVO')";
	}
}
?>