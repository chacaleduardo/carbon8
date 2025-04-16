<?
class LoteFormulaQuery
{
	public static function buscarInsumoFormula()
	{
		return "SELECT f.idprproc,
					   f.proc,
					   f.idprativ,
					   f.idprocprativinsumo,
					   f.idprodservprproc,
					   f.idprodservformulains,
					   f.idprodserv,
					   f.qtdi,
					   f.qtdi_exp
				  FROM loteformula f
				 WHERE f.idlote = ?idlote?";
	}

	public static function inserirLoteFormulaPorSelect()
	{
		return "INSERT INTO loteformula (idempresa,
										 idlote,
										 idprproc,
										 proc,
										 idprativ,
										 idprocprativinsumo,
										 idprodservprproc,
										 idprodservformulains,
										 idprodserv,
										 qtdi,
										 qtdi_exp,
										 criadopor,
										 criadoem,
										 alteradopor,
										 alteradoem)
								 (SELECT ?idempresa?,
								 		 l.idlote,
										 p.idprproc,
										 p.proc,
										 pai.idprativ,
										 pai.idprocprativinsumo,
										 pai.idprodservprproc,
										 pi.idprodservformulains,
										 pi.idprodserv,
										 pi.qtdi,
										 pi.qtdi_exp,
										 '?usuario?',
										 SYSDATE(),
										 '?usuario?',
										 SYSDATE()
									FROM lote l JOIN prodservformula f ON (f.idprodserv = l.idprodserv)
									JOIN prodservformulains pi ON pi.idprodservformula = f.idprodservformula
									JOIN procprativinsumo pai ON pai.idprodservformulains = pi.idprodservformulains
									JOIN prodservprproc pp ON pai.idprodservprproc = pp.idprodservprproc
									JOIN prproc p ON p.idprproc = pp.idprproc
								   WHERE l.idlote = ?idlote?
								     AND pi.status = 'ATIVO')";
	}

	public static function apagarLoteFormulaPorIdLote()
	{
		return "DELETE FROM loteformula WHERE idlote = ?idlote?";
	}


}
?>