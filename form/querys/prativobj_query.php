<?
class PrativObjQuery
{
	public static function buscarPrativOpcaoPorTipo()
	{
		return "SELECT p.*, o.idprativobj
				  FROM prativopcao p LEFT JOIN prativobj o ON (o.tipoobjeto = '?tipoobjeto?' AND o.idobjeto = p.idprativopcao AND o.idprativ = ?idprativ?)
				 WHERE p.status = 'ATIVO'
				   AND p.tipo IN (?tipo?)
			  ORDER BY p.ord";
	}

	public static function buscarPrativObjPorTipoObjeto()
	{
		return "SELECT o.*, t.descr
				  FROM prativobj o JOIN prodserv t ON o.idobjeto = t.idprodserv
				 WHERE t.tipo = '?tipo?'
				   AND o.tipoobjeto = '?tipoobjeto?'
				   AND o.idprativ = ?idprativ?";
	}

	public static function buscarATividadesPorIdPrativETipoObjeto()
	{
		return "SELECT o.*
				  FROM prativobj o
				 WHERE o.tipoobjeto = '?tipoobjeto?'
				   AND o.idprativ = ?idprativ?
			  ORDER BY o.ord, o.idprativobj";
	}

	public static function buscarPrativObjPorTipoEIdPrativ()
	{
		return "SELECT o.*, t.tagtipo
				  FROM prativobj o JOIN tagtipo t ON t.idtagtipo = o.idobjeto
				 WHERE t.idtagclass = ?idtagclass?
				   AND o.tipoobjeto = '?tipoobjeto?'
				   AND o.idprativ = ?idprativ?
			 	   ?order?";
	}

	public static function buscarPrativObjEEmpresaPorTipoEIdPrativ()
	{
		return "SELECT o.*, CONCAT(e.sigla, ' - ', t.descr) AS descr
				  FROM prativobj o JOIN prodserv t ON o.idobjeto = t.idprodserv AND o.tipoobjeto = '?tipoobjeto?' 
				  JOIN empresa e ON e.idempresa = t.idempresa
				 WHERE t.tipo = '?tipo?' 
				   AND o.idprativ = ?idprativ?
			  ORDER BY ord";
	}

	public static function buscarObjetoPorTipoObjetoEIdPrativ()
	{
		return "SELECT t.tagtipo
				  FROM prativobj o JOIN tagtipo t ON (t.idtagclass = ?idtagclass? AND t.idtagtipo = o.idobjeto AND t.status = 'ATIVO' ?getidempresa?
        		   AND EXISTS( SELECT 1 FROM tag t2 WHERE t2.idtagtipo = t.idtagtipo))
				 WHERE o.tipoobjeto = '?tipoobjeto?'
				   AND o.idprativ = ?idprativ?
				   ?getidempresa?";
	}

	public static function buscarObjetoPorIdPrativEIdObjetoEDescrNaoNulos()
	{
		return "SELECT idprativobj, tipoobjeto, idobjeto, descr
				  FROM prativobj
				 WHERE idprativ = ?idprativ?
				   AND (idobjeto IS NOT NULL OR descr IS NOT NULL)
			  ORDER BY ord";
	}

	public static function buscarAtividadesTagsPorIdEmpresaEIdPrativ()
	{
		return "SELECT IF(u.status = 'LOCADO' AND tr.idobjeto IS NOT NULL, tr.idobjeto, u.idtag) AS idtag,
					   IF(u.status = 'LOCADO' AND tr.idobjeto IS NOT NULL, t2.tag, u.tag) AS tag,
					   IF(u.status = 'LOCADO' AND tr.idobjeto IS NOT NULL, et2.sigla, u.sigla) AS sigla,
					   IF(u.status = 'LOCADO' AND tr.idobjeto IS NOT NULL, t2.descricao, u.descricao) AS descricao,
					   u.idtagtipo,
					   u.idtagpai,
					   GROUP_CONCAT(DISTINCT u.idtagpai SEPARATOR '#') AS idtag_pais,
					   u.inputmanual
				  FROM (SELECT t.idtag,
				  			   t.tag,
							   e.sigla,
							   t.descricao,
							   t.idtagtipo,
							   s.idtagpai,
							   po.inputmanual,
							   t.status
						  FROM tag t JOIN tagsala s ON s.idtag = t.idtag
					 LEFT JOIN prativobj po ON po.idobjeto = t.idtagtipo AND po.tipoobjeto = 'tagtipo' AND idprativ = ?idprativ?
					 LEFT JOIN empresa e on(e.idempresa = t.idempresa)
					 	 WHERE t.idtagclass in(1, 14)
						 ?tagPorSessionIdempresa?
						   AND t.idtagtipo = ?idtagtipo?
					UNION 
						SELECT t.idtag,
							   t.tag,
							   e.sigla,
							   t.descricao,
							   t.idtagtipo,
							   s.idtagpai,
							   po.inputmanual,
							   t.status
						  FROM tag t JOIN tagsala s ON s.idtag = t.idtag
					 LEFT JOIN prativobj po ON po.idobjeto = t.idtagtipo AND po.tipoobjeto = 'tagtipo' AND idprativ = ?idprativ?
					 LEFT JOIN empresa e on(e.idempresa = t.idempresa)
					 	 WHERE t.idtagclass in(2, 17)
						 ?tagPorSessionIdempresa?
						   AND t.idtagtipo = ?idtagtipo?
					UNION 
						SELECT t.idtag,
							   t.tag,
							   e.sigla,
							   t.descricao,
							   t.idtagtipo,
							   t.idtag AS idtagpai,
							   po.inputmanual,
							   t.status
						  FROM tag t LEFT JOIN prativobj po ON po.idobjeto = t.idtagtipo AND po.tipoobjeto = 'tagtipo' AND idprativ = ?idprativ?
						  LEFT JOIN empresa e on(e.idempresa = t.idempresa)
						 WHERE t.idtagclass in(2, 17)
						 ?tagPorSessionIdempresa?
						   AND t.idtagtipo = ?idtagtipo?) AS u
			  LEFT JOIN tagreserva tr ON tr.idtag = u.idtag AND tr.objeto = 'tag'
			  LEFT JOIN tag t2 ON t2.idtag = tr.idobjeto
			  LEFT JOIN empresa et2 ON et2.idempresa = t2.idempresa
			   GROUP BY u.idtag 
		UNION 
				 SELECT t.idtag AS idtag,
				 		t.tag AS tag,						
						e.sigla,
						t.descricao AS descricao,
						t.idtagtipo,
						s.idtagpai,
						GROUP_CONCAT(DISTINCT s.idtagpai SEPARATOR '#') AS idtag_pais,
						po.inputmanual
				   FROM loteobj lo JOIN tag t ON t.idtag = lo.idobjeto AND lo.tipoobjeto = 'tag'
				   JOIN empresa e ON e.idempresa = t.idempresa
				   JOIN tagsala s ON s.idtag = t.idtag
			  LEFT JOIN prativobj po ON po.idobjeto = t.idtagtipo
			  		AND po.tipoobjeto = 'tagtipo'
				  WHERE lo.idlote = ?idlote?
			   GROUP BY t.idtag;";
	}

	public static function buscarObjetoPorIdPrativobj()
	{
		return "SELECT p.idprativobj,
					   p.descr,
					   p.inputmanual,
					   '?grupo?' AS grupo,
					   p.ord
				  FROM prativobj p
				 WHERE p.idprativobj = ?idprativobj?
			  ORDER BY p.ord";
	}

	public static function buscarSalaAtividade()
	{
		return "SELECT t.idtag
				  FROM prativobj p JOIN tagtipo tt ON tt.idtagtipo = p.idobjeto
				  JOIN tag t ON t.idtagtipo = tt.idtagtipo
				 WHERE p.idprativ = ?idprativ?
				   AND tt.idtagclass = 2
				   ?idempresa?
				   AND p.tipoobjeto = '?tipoobjeto?'
			  ORDER BY t.descricao";
	}

	public static function buscarEquipamentoESala()
	{
		return "SELECT t.idtag, 
					   tc.tagclass
				  FROM prativobj p JOIN tagtipo tt ON tt.idtagtipo = p.idobjeto AND p.tipoobjeto = '?tipoobjeto?'
				  JOIN tag t ON t.idtagtipo = tt.idtagtipo
				  JOIN tagclass tc ON tc.idtagclass = tt.idtagclass
				  JOIN tagsala s ON s.idtag = t.idtag
				 WHERE p.idprativ = ?idprativ?
				   AND tt.idtagclass = 1
				   ?idempresa?
				   AND s.idtagpai = ?idtagpai?
			  ORDER BY t.descricao";
	}

	public static function apagarPrativObj()
	{
		return "DELETE FROM prativobj WHERE idprativobj = ?idprativobj?";
	}

	public static function buscarPrAtivPorIdObjetoTipoObjetoETipo()
	{
		return "SELECT po.idprativobj, a.idprativ, a.ativ, a.descr
				from prativobj po
				join prodserv s on s.idprodserv = po.idobjeto
				join prativ a on a.idprativ = po.idprativ
				where po.idobjeto = ?idobjeto? and po.tipoobjeto = '?tipoobjeto?'              
				and s.tipo = '?tipo?'";
	}
}
?>