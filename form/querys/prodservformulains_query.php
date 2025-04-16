<?
class ProdservFormulaInsQuery
{
	public static function listarProdservFormulaIns()
    {
		return "SELECT * 
				  FROM prodservformulains i LEFT JOIN prodserv p ON (i.idprodserv = p.idprodserv)
				 WHERE i.idprodservformula = ?idprodservformula?
				   AND i.status = 'ATIVO'
			  ORDER BY ord ASC";
	}	

	

	public static function listarProdservFormulaInsComprados()
    {
		return "SELECT i.*,p.comprado 
				  FROM prodservformulains i  JOIN prodserv p ON (i.idprodserv = p.idprodserv)
				 WHERE i.idprodservformula = ?idprodservformula?
				   AND i.status = 'ATIVO'
			  ORDER BY p.comprado  DESC";
	}	

	public static function listarFormulas()
	{
		return "SELECT e.sigla,
					   IF(p.tipo = 'SERVICO', p.descr, IF(p.descrcurta = '' OR p.descrcurta IS NULL, p.descr, p.descrcurta)) AS descr,
					   CONCAT(pl.plantel, ' - ', f.rotulo, '-', IFNULL(f.dose, '--'), ' Doses ', ' (', f.volumeformula, ' ', f.un, ')') AS rotulo,
					   i.qtdi,
					   i.qtdi_exp,
					   p.idprodserv,
					   i.idprodservformulains,
					   qtdpd,
					   qtdpd_exp
				  FROM prodservformulains i JOIN prodservformula f ON (f.idprodservformula = i.idprodservformula AND f.status = 'ATIVO')
				  JOIN prodserv p ON (p.idprodserv = f.idprodserv AND p.status = 'ATIVO')
			 LEFT JOIN plantel pl ON (pl.idplantel = f.idplantel)
			 LEFT JOIN empresa e ON (e.idempresa = p.idempresa)
				 WHERE i.idprodserv = ?idprodserv?
				   AND i.status = 'ATIVO'
			  ORDER BY descr";
	}

	public static function buscarInsumosAtividadeNaoAtivos()
	{
		return "SELECT a.idprocprativinsumo,
					   CONCAT(e.sigla, ' - ', p.descr) AS descr,
					   p.codprodserv,
					   p.idprodserv,
					   i.idprodservformulains,
					   i.qtdi,
					   i.qtdi_exp,
					   f.cor,
					   f.idprodservformula,
					   f.editar
				  FROM procprativinsumo a JOIN prodservformulains i ON i.idprodservformulains = a.idprodservformulains
				  JOIN prodservformula f ON (f.idprodservformula = i.idprodservformula AND f.status NOT IN ('INATIVO'))
				  JOIN prodserv p ON p.idprodserv = i.idprodserv
				  JOIN empresa e ON e.idempresa = p.idempresa
				 WHERE a.idprativ = ?idprativ?
				 AND a.idprodservprproc = ?idprodservprproc?
				 AND i.status = 'ATIVO'
			ORDER BY f.idprodservformula, i.ord";
	}

	public static function buscarDadosProdservFormulaInsPorIdProdservFormula()
	{
		return "SELECT * FROM prodservformulains WHERE idprodservformula = ?idprodservformula? AND status = '?status?';";
	}

	public static function buscarFilhosProdservFormulaInsPorIdProdservFormula()
	{
		return "SELECT pf1.idprodservformulains AS idnovo,
					   pf2.idprodservformulains AS idantigo
				  FROM prodservformulains pf1 JOIN prodservformulains pf2 ON (pf1.idprodserv = pf2.idprodserv AND pf2.status = 'ATIVO')
				 WHERE pf1.idprodservformula = ?idprodservformulanova?
				   AND pf2.idprodservformula = ?idprodservformula?
				   AND pf1.status = 'ATIVO';";
	}

	public static function inserirProservFormulaIns()
	{
		return "INSERT INTO prodservformulains (idempresa, 
												idprodservformula,
												idprodserv,
												qtdi,
												chkvolume,
												listares,
												ord,
												status,
												criadopor,
												criadoem,
												alteradopor,
												alteradoem)
               							 VALUES (?idempresa?,
										 		?idprodservformula?,
												?idprodserv?,
												?qtdi?, 
												'?chkvolume?',
												'?listares?',
												?ord?,
												'?status?',
												'?criadopor?',
												now(),
												'?alteradopor?',
												now())";
	}


	public static function buscarLotePorProdservFormula()
	{
		return "SELECT DISTINCT (l.idlote) AS idlote, l.partida, l.exercicio
				  FROM prodservformulains i JOIN prodservformula f ON i.idprodserv = f.idprodserv
				  JOIN prodservformulains fi ON fi.idprodservformula = f.idprodservformula
				  JOIN lote l ON l.idprodserv = fi.idprodserv AND l.tipoobjetosolipor = 'resultado'
				  JOIN resultado r ON r.idresultado = l.idobjetosolipor
				  JOIN amostra a ON a.idamostra = r.idamostra
				  JOIN lotefracao lf ON lf.idlote = l.idlote
				 WHERE i.idprodservformula = ?idprodservformula?
				   AND a.idpessoa = ?idpessoa?
				   AND fi.status = 'ATIVO'
				   AND l.status NOT IN ('ESGOTADO' , 'REPROVADO')
				   AND lf.status = 'DISPONIVEL'
				   AND NOT EXISTS(SELECT 1 FROM solfabitem sf WHERE sf.idsolfab = ?idsolfab? AND sf.tipoobjeto = 'lote' AND sf.idobjeto = l.idlote)";
	}
}
?>