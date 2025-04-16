<?
class ProdservTipoOpcaoEspecieQuery
{
	public static function listarProdservTipoOpcaoEspecie()
	{
		return "SELECT ptoe.idprodservtipoopcaoespecie,
					   e.idespeciefinalidade,
					   ptoe.idadeinicio,
					   ptoe.valorinicio,
					   ptoe.valorfim,
					   ptoe.idadeinicio,
					   ptoe.idadefim,
					   ptoe.msg,
					   ptoe.cor
				  FROM prodservtipoopcaoespecie ptoe LEFT JOIN vwespeciefinalidade e ON e.idespeciefinalidade = ptoe.idespeciefinalidade
				 WHERE ptoe.idprodserv = ?idprodserv?
			  ORDER BY e.especie, e.finalidade, e.tipoespecie, ptoe.idespeciefinalidade, idadeinicio, idadefim, ptoe.valorinicio * 1, ptoe.valorfim * 1, valorfim, ptoe.status";
	}
}
?>