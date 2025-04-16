<?
class ObjEmpresaQuery
{
	public static function buscarObjempresaPorIdObjempresa()
	{
		return "SELECT idobjeto AS idprodserv, empresa
				  FROM objempresa
				 WHERE idobjempresa = ?idobjempresa?";
	}
}
?>