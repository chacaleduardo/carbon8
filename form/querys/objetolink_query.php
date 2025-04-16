<?
class ObjetoLinkQuery
{
	public static function buscarLinkPorTipoObjeto()
    {
        return "SELECT idobjetolink, link
				  FROM objetolink 
				 WHERE idobjeto = ?idobjeto? AND tipoobjeto = '?tipoobjeto?'";
    }
} 
?>