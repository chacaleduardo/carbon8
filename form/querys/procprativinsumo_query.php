<?
class ProcPrativInsumovQuery
{
	public static function buscarInsumosEFormulasProdsev()
	{	
		return "SELECT a.idprocprativinsumo,
					   a.idprodservprproc,
					   a.idprativ,
					   p.descr,
					   p.codprodserv,
					   p.idprodserv,
					   i.idprodservformulains,
					   f.cor,
					   i.qtdi,
					   i.qtdi_exp,
					   f.idprodservformula,
					   f.editar
				  FROM procprativinsumo a JOIN prodservformulains i ON i.idprodservformulains = a.idprodservformulains
				  JOIN prodservformula f ON f.idprodservformula = i.idprodservformula
				  JOIN prodserv p ON p.idprodserv = i.idprodserv
				 WHERE a.idprativ = ?idprativ?
				   AND i.status = 'ATIVO'
			  ORDER BY f.idprodservformula, i.ord";
	}

	public static function inserirInsumo()
	{
		return "INSERT INTO procprativinsumo (idempresa,
											  idprodservprproc,
											  idprativ,
											  idprprocprativ,
											  idprodservformulains)
									   SELECT pr.idempresa,
									   		  pr.idprodservprproc,
											  pr.idprativ,
											  pr.idprprocprativ,
											  ?idnovo?
										 FROM prodservformulains pf JOIN procprativinsumo pr ON (pf.idprodservformulains = pr.idprodservformulains)
										WHERE pf.idprodservformula = ?idprodservformula?
										  AND pr.idprodservformulains = ?idprodservformulains?";
	}
}
?>