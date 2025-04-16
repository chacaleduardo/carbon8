<?
class EtapaQuery 
{
	public static function buscarEtapaPorTipoObjeto()
	{
		return "SELECT idetapa, etapa
				  FROM etapa
				 WHERE status = 'ATIVO'
				   AND modulo LIKE ('?modulo?%')
				   AND tipoobjeto = '?tipoobjeto?'
				   AND idobjeto = '?idobjeto?'
			  ORDER BY etapa";
	}
}	
?>