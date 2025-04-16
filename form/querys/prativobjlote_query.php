<?

class PrativObjLoteQuery
{
	public static function buscarAtividadesLotePorIdLoteIdPrativEIdObjetoDescNaoNulos()
	{
		return "SELECT o.idprativobj, o.tipoobjeto, o.idobjeto, o.descr
				  FROM prativobjlote o
				 WHERE o.idlote = ?idlote?
				   AND o.idprativ = ?idprativ?
				   AND (idobjeto IS NOT NULL OR descr IS NOT NULL)
			  ORDER BY o.ord";
	}

	public static function apagarObjetoPorIdLote()
	{
		return "DELETE FROM prativobjlote WHERE idlote = ?idlote?";
	}

	public static function inserirPrativObjetoPorSelect()
	{
		return "INSERT INTO prativobjlote (idlote,
										   idprativobj,
										   idempresa,
										   idprativ,
										   idobjeto,
										   tipoobjeto,
										   descr,
										   inputmanual,
										   ord,
										   criadopor,
										   criadoem,
										   alteradopor,
										   alteradoem)
								   (SELECT ?idlote?,
								    	   o.idprativobj,
										   o.idempresa,
										   o.idprativ,
										   o.idobjeto,
										   o.tipoobjeto,
										   o.descr,
										   o.inputmanual,
										   o.ord,
										   '?usuario?',
										   SYSDATE(),
										   '?usuario?',
										   SYSDATE()
									  FROM prativobj o
									 WHERE o.idprativ = ?idprativ?
									   AND (o.idobjeto IS NOT NULL OR o.descr IS NOT NULL))";
	}
}
?>