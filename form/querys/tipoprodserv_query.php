<?
class TipoProdServQuery
{
	public static function listarProdservTipoProdServ()
	{
		return "SELECT t.idtipoprodserv, t.tipoprodserv, p.idprodserv
				  FROM prodserv p JOIN tipoprodserv t ON (t.idtipoprodserv = p.idtipoprodserv ?idempresa? )
				 WHERE p.idprodserv IN (?idprodservs?)";
	}

	public static function listarProdservTipoProdServPorEmpresa()
	{
		return "SELECT idtipoprodserv, tipoprodserv
				  FROM tipoprodserv t 
				 WHERE t.idempresa = ?idempresa?
			  ORDER BY tipoprodserv";
	}

	public static function listarContaItemTipoProdservTipoProdServ()
	{
		return "SELECT e.idtipoprodserv, t.tipoprodserv, e.idcontaitem
					FROM contaitemtipoprodserv e 
				  	JOIN tipoprodserv t ON (t.idtipoprodserv = e.idtipoprodserv) and t.status = 'ATIVO'
				WHERE e.idcontaitem IN (?idcontaitens?) 
			  	ORDER BY t.tipoprodserv";
	}

	public static function listarTipoProdservTipoProdServ()
	{
		return "SELECT 
					t.idtipoprodserv, t.tipoprodserv
				FROM
					tipoprodserv t
						JOIN
					prodserv p ON (p.idtipoprodserv = t.idtipoprodserv
						AND p.status = 'ATIVO'
						AND p.tipo = 'PRODUTO'
						AND p.venda = 'Y'
						AND p.fabricado = 'Y')
				WHERE
					t.idempresa = ?idempresa? AND t.status = 'ATIVO'
				GROUP BY t.idtipoprodserv
				ORDER BY t.tipoprodserv";
	}

	public static function buscarContaItem()
	{
		return "SELECT e.idtipoprodserv, t.tipoprodserv, ov.idobjetovinc
			   FROM contaitemtipoprodserv e JOIN tipoprodserv t ON (t.idtipoprodserv = e.idtipoprodserv)
		  LEFT JOIN objetovinculo ov ON ov.idobjetovinc = e.idtipoprodserv AND ov.tipoobjetovinc = 'contaitemtipoprodserv' 
		  		AND ov.idobjeto = ?idobjeto? AND ov.tipoobjeto = '?tipoobjeto?'
			  WHERE e.idcontaitem IN (?idcontaitens?) AND t.status = 'ATIVO' AND t.compra = 'Y'
		   GROUP BY e.idtipoprodserv
		   ORDER BY CASE WHEN idobjetovinc IS NOT NULL THEN 0 ELSE 1 END, t.tipoprodserv;";
	}

	public static function buscarProdservTipoProdServ()
	{
		return "SELECT idtipoprodserv, tipoprodserv
				  FROM tipoprodserv t 
				 WHERE 1
				 ?idempresa?
			  ORDER BY tipoprodserv";
	}

	public static function buscarContaItemTipoProdservTipoProdServ()
	{
		return "SELECT 
					e.idtipoprodserv, t.tipoprodserv
				FROM
					contaitemtipoprodserv e
						JOIN
					tipoprodserv t ON (t.idtipoprodserv = e.idtipoprodserv)
				WHERE
					e.idcontaitem =?idcontaitem?					
				ORDER BY t.tipoprodserv";
	}

	public static function buscarTipoProdservPorApp()
	{
		return "SELECT tp.tipoprodserv,
					   tp.idtipoprodserv,
                       tp.idpessoa,
                       c.idcontaitem,
					   c.contaitem
				  FROM tipoprodserv tp JOIN contaitemtipoprodserv citp ON tp.idtipoprodserv = citp.idtipoprodserv
                  JOIN contaitem c ON c.idcontaitem = citp.idcontaitem
				  JOIN objetovinculo ov ON ov.idobjetovinc = c.idcontaitem AND ov.tipoobjetovinc = 'contaitem' AND ov.idobjeto in (?idobjeto?) AND ov.tipoobjeto = '_lp'
				 WHERE tp.app = 'Y'
				   AND tp.status = 'ATIVO'
				   ?condicao?
			  GROUP BY tp.idtipoprodserv
			  ORDER BY tp.tipoprodserv";
	}

	public static function buscarEmpresa()
	{
		return "SELECT 
					e.idempresa,e.empresa, e.razaosocial
				FROM
					empresa e
						
				WHERE
					e.idempresa = ?idempresa?";
	}

	public static function buscarExercicioPorIdprev()
	{
		return "SELECT 
                    exercicio
                FROM
                    (SELECT YEAR(NOW()) AS exercicio 
                    UNION SELECT YEAR(DATE_ADD(NOW(), INTERVAL 1 YEAR)) AS exercicio 
                    UNION SELECT YEAR(DATE_ADD(NOW(), INTERVAL 2 YEAR)) AS exercicio 
                    UNION SELECT YEAR(DATE_ADD(NOW(), INTERVAL 3 YEAR)) AS exercicio
                    UNION SELECT YEAR(DATE_ADD(NOW(), INTERVAL 4 YEAR)) AS exercicio
                    UNION SELECT YEAR(DATE_ADD(NOW(), INTERVAL 5 YEAR)) AS exercicio
                    UNION SELECT YEAR(DATE_ADD(NOW(), INTERVAL 6 YEAR)) AS exercicio
                    UNION SELECT YEAR(DATE_ADD(NOW(), INTERVAL 7 YEAR)) AS exercicio
                    UNION SELECT YEAR(DATE_ADD(NOW(), INTERVAL 8 YEAR)) AS exercicio
                    UNION SELECT YEAR(DATE_ADD(NOW(), INTERVAL 9 YEAR)) AS exercicio
                    UNION SELECT YEAR(DATE_ADD(NOW(), INTERVAL 10 YEAR)) AS exercicio) AS u
                WHERE
                    NOT EXISTS( SELECT 
                            1
                        FROM
							tipoprodservempresa p
                        WHERE
                            p.exercicio = u.exercicio
                                AND p.idtipoprodserv = ?idtipoprodserv?
                                   AND p.idempresaprev = ?idempresa?) ORDER BY exercicio";
	}

	public static function buscarPrevisaoTipoprodservExercicio()
	{
		return "SELECT 
                    exercicio
                FROM
					tipoprodservempresa 
                WHERE
                    idtipoprodserv = ?idtipoprodserv? AND idempresaprev = ?idempresa? 
                GROUP BY exercicio";
	}


	public static function buscarPrevisaoTipoprodservMes()
	{
		return "SELECT 
                    *
                FROM
					tipoprodservempresa p
                WHERE
                    p.idtipoprodserv = ?idtipoprodserv? AND idempresaprev = ?idempresa? AND exercicio= ?exercicio?                
                ORDER BY mes";
	}

	public static function buscarSubCategoriaPorNf()
	{
		return "SELECT e.idtipoprodserv, t.tipoprodserv, ni.idnfitem
				  FROM nfitem ni JOIN contaitemtipoprodserv e ON e.idcontaitem = ni.idcontaitem
				  JOIN tipoprodserv t ON t.idtipoprodserv = e.idtipoprodserv
				 WHERE ni.idnf = ?idnf?
			UNION 
                SELECT t.idtipoprodserv, t.tipoprodserv, ni.idnfitem
				  FROM nfitem ni JOIN prodserv p ON p.idprodserv = ni.idprodserv
                  JOIN tipoprodserv t ON t.idtipoprodserv = p.idtipoprodserv
				 WHERE ni.idnf = ?idnf?
			ORDER BY tipoprodserv";
	}


	public static function buscarValorTotalPrevisao()
	{
		return "SELECT 
				SUM(tpe.previsao) AS previsao
			FROM
				tipoprodserv tp
					LEFT JOIN
				tipoprodservempresa tpe ON (tpe.idtipoprodserv = tp.idtipoprodserv)
			WHERE
				tp.idtipoprodserv = '?idtipoprodserv?' AND tpe.exercicio = '?exercicio?'";
	}
}
