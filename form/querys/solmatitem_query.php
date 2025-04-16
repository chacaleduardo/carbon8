<?
class SolmatItemQuery
{

	public static function inserirSolmatItem()
	{
		return "INSERT INTO solmatitem (idempresa, 
										idsolmat, 
										qtdc, 
										idprodserv, 
										descr, 
										un,
										obs, 
										criadopor, 
										criadoem, 
										alteradopor, 
										alteradoem)
								VALUES (?idempresa?,
										?idsolmat?,
										'?qtdc?',
										?idprodserv?,
										'?descr?',
										'?un?',
										'?obs?',
										'?usuario?',
										sysdate(),
										'?usuario?',
										sysdate())";
	}

	public static function buscarSolMatItemPorIdSolMatGroupConcat()
	{
		return "SELECT group_concat(idprodserv) as stidprodserv 
				FROM solmatitem
				WHERE idsolmat = ?idsolmat?";
	}

	public static function buscarProdServESolMatItemPorIdSolMat()
	{
		return "SELECT p.descr, p.obs, p.codprodserv, i.un, i.*
				FROM solmatitem i  
				JOIN prodserv p ON(p.idprodserv = i.idprodserv)
				WHERE i.idsolmat = ?idsolmat? 
				ORDER BY p.descr,i.criadoem";
	}

	public static function buscarSolMatItemSemCadastroPorIdSolMat()
	{
		return "SELECT i.*
				FROM solmatitem i
				WHERE i.idsolmat = ?idsolmat?
				AND i.idprodserv is null 
				ORDER BY i.descr,i.criadoem";
	}

	public static function buscarSolMatItemPorIdSomatItem()
	{
		return "SELECT idsolmatitem, idsolmat from solmatitem where idsolmatitem = ?idsolmatitem?";
	}

	public static function buscarSolmatitemPendente()
	{
		return "SELECT 
					CASE
						WHEN COUNT(*) > 0 THEN 'Y'
						ELSE 'N'
					END AS pendente
				FROM
					solmatitem
				WHERE
					idsolmat = ?idsolmat? AND status = 'PENDENTE'";
	}

	public static function buscarSolmatitemEstoque()
	{
		return "SELECT 
					ifnull(SUM(f.qtd),0) AS qtd, p.descr, i.qtdc, i.idsolmatitem
				FROM
					solmat s
						JOIN solmatitem i ON (i.idsolmat = s.idsolmat)
						JOIN unidade uu ON (uu.idunidade = s.unidade)
					left JOIN lote l ON (l.idprodserv = i.idprodserv)
					left JOIN lotefracao f ON (f.idlote = l.idlote and f.idunidade = uu.idunidade
						AND f.status = 'DISPONIVEL')      
						JOIN prodserv p ON (p.idprodserv = i.idprodserv)
				WHERE
					l.status IN ('APROVADO')
						AND i.idsolmatitem  = ?idsolmatitem?
				GROUP BY i.idsolmatitem";
	}

	public static function buscarSolmatEstoque()
	{
		return "SELECT 
					ifnull(SUM(f.qtd),0) AS qtd, p.descr, i.qtdc, i.idsolmatitem
				FROM
					solmat s
						JOIN solmatitem i ON (i.idsolmat = s.idsolmat)
						JOIN unidade uu ON (uu.idunidade = s.unidade)
					left JOIN lote l ON (l.idprodserv = i.idprodserv)
					left JOIN lotefracao f ON (f.idlote = l.idlote and f.idunidade = uu.idunidade
						AND f.status = 'DISPONIVEL')      
						JOIN prodserv p ON (p.idprodserv = i.idprodserv)
				WHERE
					l.status IN ('APROVADO')
						AND s.idsolmat = ?idsolmat?
				GROUP BY i.idsolmatitem";
	}
	
	public static function buscarSolmatitemPedente()
	{
		return " SELECT 
					COUNT(*) AS qtd
				FROM
					solmatitem
				WHERE
					idsolmat = ?idsolmat? AND status = 'PENDENTE'
						AND idsolmatitem != ?idsolmatitem?";
	}
	
}
?>