<?
class ContaItemQuery
{
	public static function buscarGrupoES()
	{
		return "SELECT * FROM (SELECT c.idcontaitem, c.contaitem, ov.idobjetovinc
								 FROM contaitem c LEFT JOIN objetovinculo ov ON ov.idobjetovinc = c.idcontaitem AND tipoobjetovinc = 'contaitem' 
								  AND ov.idobjeto = ?idobjeto? AND ov.tipoobjeto = '?tipoobjeto?'								  
								WHERE c.status = 'ATIVO'   
								  AND c.compra = 'Y'
								?getidempresa?                                                                      
							UNION 
							  SELECT c.idcontaitem, c.contaitem, ov.idobjetovinc
								FROM contaitem c LEFT JOIN objetovinculo ov ON ov.idobjetovinc = c.idcontaitem AND tipoobjetovinc = 'contaitem'
								 AND ov.idobjeto = ?idobjeto? AND ov.tipoobjeto = '?tipoobjeto?'								 
							   WHERE c.status = 'ATIVO' 
							   	 AND c.compra = 'Y'
								?compartilharCbUserContaitem?) AS c
				ORDER BY CASE WHEN idobjetovinc IS NOT NULL THEN 0 ELSE 1 END, contaitem;";
	}

	public static function buscarContaItemProdservContaItem()
	{
		return "SELECT c.idcontaitem, c.contaitem, pi.idprodserv, pi.idprodservcontaitem
				  FROM prodservcontaitem pi JOIN contaitem c ON c.idcontaitem = pi.idcontaitem
				 WHERE pi.idprodserv IN (?idprodservs?) 
				 ?somarRelatorio?
				 ?union?
			  ORDER BY contaitem";
	}

	public static function buscarContaItemProdservContaItemPorNf()
	{
		return "SELECT ni.idnf, ni.idnfitem, ni.idprodserv, c.idcontaitem, c.contaitem, pi.idprodservcontaitem
				FROM nfitem ni
				JOIN contaitem c ON c.idcontaitem = ni.idcontaitem 
                LEFT JOIN prodservcontaitem pi ON c.idcontaitem = pi.idcontaitem AND ni.idprodserv = pi.idprodserv
				WHERE ni.idnf = ?idnf?
				 ?somarRelatorio?
				 UNION
				SELECT ni.idnf, ni.idnfitem, ni.idprodserv, c.idcontaitem, c.contaitem, pi.idprodservcontaitem
				FROM nfitem ni 
				LEFT JOIN prodservcontaitem pi ON ni.idprodserv = pi.idprodserv
				LEFT JOIN contaitem c ON c.idcontaitem = pi.idcontaitem
				WHERE ni.idnf = ?idnf?";
	}

	public static function buscarContaItem()
	{
		return "SELECT c.idcontaitem, c.contaitem
				  FROM contaitem c
				 WHERE idcontaitem IN (?idcontaitens?)";
	}

	public static function buscarContaItemAtivoShare()
	{
		return "SELECT * FROM (SELECT c.idcontaitem, CONCAT(e.sigla, ' - ', c.contaitem) AS contaitem
								 FROM contaitem c JOIN empresa e ON e.idempresa = c.idempresa
								WHERE c.status = 'ATIVO'
								?somarRelatorio?
								?idempresa?
							UNION 
							   SELECT c.idcontaitem, CONCAT(e.sigla, ' - ', c.contaitem) AS contaitem
								 FROM contaitem c JOIN empresa e ON e.idempresa = c.idempresa
							    WHERE c.status = 'ATIVO'
								?somarRelatorio?
								?compartilharCbUserContaitem?) AS c
					ORDER BY contaitem";
	}

	public static function buscarContaItensDisponiveisParaVinculoPorIdSgDepartamento()
	{
		return "SELECT idcontaitem, contaitem
				FROM contaitem ci
				WHERE ci.idempresa=?idempresa?
				AND ci.status='ATIVO' 
				AND NOT EXISTS (
					SELECT 1 
					FROM objetovinculo ov 
					WHERE ov.idobjeto = ?idsgdepartamento?
					AND tipoobjeto = 'sgdepartamento'
					AND ci.idcontaitem = ov.idobjetovinc
					AND ov.tipoobjetovinc = 'contaitem'
				) order by contaitem";
	}

	public static function buscarContaItemPorIdprodserv()
	{
		return "SELECT 
						*
					FROM
						prodservcontaitem
					WHERE
						idprodserv = ?idprodserv?";
	}
}
?>