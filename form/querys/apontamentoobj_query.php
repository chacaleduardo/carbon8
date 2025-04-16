<?
class ApontamentoObjQuery
{
	public static function buscarApontamentoObj()
	{
		return "SELECT apontamento, 
					   criadopor, 
					   DMAHMS(criadoem) AS criadoem
				  FROM apontamentoobj
				 WHERE idobjeto = ?idobjeto? AND tipoobjeto = '?tipoobjeto?'
			  ORDER BY idapontamentoobj ASC";
	}
}
?>