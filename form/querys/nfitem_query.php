<?
class NfItemQuery{
    public static function buscarComissaoNfPorPessoa(){
        return "SELECT c.idpessoa, ROUND( SUM( n.total * (c.pcomissao / 100) ), 2 ) AS comissao
            FROM nfitem n 
                JOIN nfitemcomissao c ON (c.idnfitem = n.idnfitem) 
                JOIN pessoa p ON (p.idpessoa = c.idpessoa AND p.status IN ('PENDENTE', 'ATIVO'))
            WHERE n.idnf = ?idnf? 
                AND n.nfe='Y' 
            GROUP BY c.idpessoa";
    }

    public static function buscarNtItens()
    {
        return "SELECT p.idprodserv,
				 IF((i.un IS NULL OR i.un = ''), p.un, i.un) AS unidade,
				 p.descr,
				 p.codprodserv,
				 p.sugestaocompra2,
				 pf.codforn,
				 pf.unforn,
				 pf.converteest,
				 pf.valconv,
				 t.tipoprodserv,
				 i.idnf,
				 i.nfe,
				 i.moeda,
				 i.total,
				 i.valipi,
				 i.aliqipi,
				 i.des,
				 i.totalext,
				 i.idnfitem,
				 i.qtd,
				 i.qtdsol,				 
				 i.prodservdescr,
				 IFNULL(i.idtipoprodserv, p.idtipoprodserv) as idtipoprodserv,
				 i.idcontaitem,
				 i.moedaext,
				 i.convmoeda,
				 i.vlritemext,
				 i.vlritem,
				 i.validade,
				 i.previsaoent,
				 i.previsaoentrega,
				 i.obs,
				 l.idlote,
				 ((i.total + IFNULL(i.valipi,0)) / (IF(IFNULL(i.qtd, 1) = 0, 1, i.qtd) * IF(pf.valconv < 1 OR pf.valconv IS NULL, 1, pf.valconv))) AS vlr,
				 (SELECT IF(idlote is null, vlritem2, vlritem) 
					FROM(SELECT CONCAT(n1.idnf, '#', ROUND(IFNULL(l1.vlrlote, 0), 2)) AS vlritem,
						    	CONCAT(n1.idnf,'#' ,round(ifnull((i1.total/(i1.qtd*if(ifnull(f.valconv,1)= 0.00 , 1 ,ifnull(f.valconv,1)) )),0),2)) as vlritem2,
								n1.dtemissao,
								l1.idlote,
                            	n1.idnf
						   FROM nf n1 JOIN nfitem i1 ON n1.idnf = i1.idnf AND i1.nfe = 'Y' AND i1.qtd > 0
					  LEFT JOIN lote l1 ON l1.idnfitem = i1.idnfitem AND l1.qtdprod > 0
					  LEFT JOIN lotecons lc ON (lc.idobjetoconsumoespec = i1.idnfitem AND lc.tipoobjetoconsumoespec = 'nfitem' AND lc.qtdc > 0)
					  LEFT JOIN lote l2 ON (l2.idlote = lc.idlote) 
					  LEFT JOIN prodservforn f ON (f.idprodservforn = i1.idprodservforn)
						  WHERE n1.tiponf NOT IN ('R' , 'D', 'T', 'E', 'V') AND i1.idprodserv = p.idprodserv 
						  	AND n1.idnf <> n.idnf
							and n1.idempresafat is null
						    AND n1.status IN ('APROVADO', 'DIVERGENCIA', 'CONCLUIDO')) AS uc 
					   ORDER BY dtemissao DESC, idnf DESC LIMIT 1) as ultimacompra
			FROM nfitem i JOIN nf n ON i.idnf = n.idnf
	   LEFT JOIN prodserv p  ON p.idprodserv = i.idprodserv
	   LEFT JOIN prodservforn pf ON (pf.idprodserv = i.idprodserv AND pf.idprodservforn = i.idprodservforn AND pf.status = 'ATIVO' AND pf.idpessoa = n.idpessoa)
	   LEFT JOIN tipoprodserv t ON (t.idtipoprodserv = p.idtipoprodserv)
	   LEFT JOIN prodservcontaitem pci ON pci.idprodserv = i.idprodserv
	   LEFT JOIN lote l ON l.idnfitem = i.idnfitem
		   WHERE i.idnf IN (?idnfs?) and i.nfe != 'C' 
		ORDER BY CASE WHEN n.status IN ('APROVADO', 'DIVERGENCIA', 'CONCLUIDO', 'CONFERIDO') AND i.nfe = 'Y' THEN 0 
					  WHEN n.status IN ('CANCELADO', 'REPROVADO') THEN 3
					  ELSE 1 END, n.idnf, if(n.status IN('APROVADO', 'PREVISAO', 'DIVERGENCIA', 'CONCLUIDO', 'CONFERIDO'), i.nfe, '') DESC, p.descr";
    }

	public static function buscarValoresNfitem()
	{
		return "SELECT SUM(IF(i.total > 0, i.total, i.totalext) + i.valipi + (i.des * i.qtd)) AS totalsemdesc,
    				   SUM(i.des * i.qtd) AS desconto,
					   SUM(IF(i.total > 0, i.total, i.totalext) + i.valipi) AS totalcomdesc
				  FROM nfitem i
				 WHERE i.nfe = 'Y' 
				   AND i.idnf = ?idnf?
				   ?idempresa?";
	}

	public static function buscarValoresNfitemJoinNfitem()
	{
		return "SELECT SUM(IF(i1.total > 0, i1.total, i1.totalext) + i1.valipi + (i1.des * i1.qtd)) AS totalsemdesc,
    				   SUM(i1.des * i1.qtd) AS desconto,
					   SUM(IF(i1.total > 0, i1.total, i1.totalext) + i1.valipi) AS totalcomdesc
				  FROM nfitem i JOIN nfitem i1 ON i1.idnf = i.idnf
				 WHERE i1.nfe = 'Y' 
				   AND i.idnfitem = ?idnfitem?
				   ?idempresa?";
	}

	public static function buscarCtePorIdNfe()
	{

		/* SELECT n.idnf, p.nome,0 as idobjetovinculo
		 FROM nfitem i JOIN nf n ON n.idnf = i.idnf
		 JOIN pessoa p ON p.idpessoa = n.idpessoa
	    WHERE i.obs like('%?idnfe?%')
    UNION */

		return "select * from(	
				SELECT n.idnf, p.nome,0 as idobjetovinculo
				  FROM nf n
				  JOIN pessoa p ON p.idpessoa = n.idpessoa
				 WHERE n.idobjetosolipor = ?idobjetosolipor?
				   AND n.tipoobjetosolipor = '?tipoobjetosolipor?'
				   AND n.tiponf IN (?tiponf?)                                   
				UNION 
				 SELECT n.idnf, p.nome,o.idobjetovinculo
				  FROM objetovinculo o
                   JOIN nf n on(o.idobjetovinc=n.idnf)
				  JOIN pessoa p ON p.idpessoa = n.idpessoa
				 WHERE o.tipoobjeto= 'nf'
                 and  o.tipoobjetovinc = 'cte'
                 and o.idobjeto= ?idobjetosolipor?
				 ) as u group by u.idnf";
	}

	public static function buscarCte()
	{
		return "select * from(
		SELECT 0 as idobjetovinculo,
						n.idnf,
						p.nome,
						n.total as frete
				  FROM nf n 
				  JOIN pessoa p ON p.idpessoa = n.idpessoa
				 WHERE n.idobjetosolipor = ?idobjetosolipor?
				   AND n.tipoobjetosolipor = '?tipoobjetosolipor?'
				   AND n.tiponf IN (?tiponf?)
				 UNION
				 SELECT o.idobjetovinculo, 
						n.idnf,
						p.nome,
						n.total as frete
					FROM
						objetovinculo o
							JOIN
						nfitem i ON (o.idobjetovinc = i.idnf)
							JOIN
						nf n ON n.idnf = i.idnf
							JOIN
						pessoa p ON p.idpessoa = n.idpessoa
					WHERE
						o.tipoobjeto = 'nf'
							AND o.tipoobjetovinc = 'cte'
							AND o.idobjeto =  ?idobjetosolipor?
							 ) as u group by u.idnf";
	}

	public static function buscarDadosNfItemPorIdNfItem()
	{
		return "SELECT idprodserv,
					   prodservdescr,
					   idprodservforn,
					   idprodserv,
					   idtipoprodserv,
					   un,
					   idcontaitem,
					   vlritem,
					   obs
				  FROM nfitem 
				 WHERE idnfitem = ?idnfitem?";
	}

	public static function buscarDadosNfPorIdNfItem()
    {
        return "SELECT n.idnf,
                       n.idobjetosolipor,
                       n.tipoobjetosolipor,
                       n.idpessoa,
                       n.idobjetosolipor,
                       n.tipoobjetosolipor,
                       n.tpnf,
                       n.tiponf,
					   n.idunidade,
					   n.diasentrada,
                       n.dtemissao as dtemissaoorig
                  FROM nf n JOIN nfitem i ON i.idnf = n.idnf
                 WHERE i.idnfitem = ?idnfitem?";
    }

	public static function buscarValorFretePorItensNf()
    {
		return "SELECT i.idnfitem,
					   ROUND((i.total / n.subtotal) * '?frete?', 2) AS novofrete
				  FROM nfitem i JOIN nf n ON (n.idnf = i.idnf)
				 WHERE i.nfe = 'Y' AND i.idnf = ?idnf?";
	}

	public static function buscarRateioNfItem()
	{
		return "SELECT i.idnfitem
				  FROM nfitem i JOIN contaitem c ON (c.idcontaitem = i.idcontaitem AND c.somarelatorio = 'Y')
				  JOIN nf n ON(n.idnf = i.idnf AND n.tiponf = 'C')
				 WHERE i.nfe = 'Y' 
				   AND i.qtd > 0
        		   AND i.vlritem > 0
				   AND i.idnf = ?idnotafiscal?
        		   AND NOT EXISTS(SELECT 1 FROM rateioitem r JOIN rateioitemdest d ON (d.idrateioitem = r.idrateioitem) 
				   				   WHERE r.idobjeto = i.idnfitem AND r.tipoobjeto = '?tipoobjeto?')";
	}

	public static function atualizarQtdNfItem()
	{
		return "UPDATE nfitem SET qtd = qtd + ?qtd?, qtdsol = qtdsol + ?qtd? WHERE idnfitem = ?idnfitem?";
	}

	public static function inserirNfItem()
	{
		return "INSERT INTO nfitem (idnf, 
                                	tiponf, 
									idprodservforn, 
									idempresa, 
									un, 
									qtd, 
									qtdsol, 
									nfe, 
									idprodserv, 
									idtipoprodserv, 
									idcontaitem, 
									criadopor, 
									criadoem, 
									alteradopor, 
									alteradoem)
							VALUES (?idnf?, 
									'?tiponf?', 
									?idprodservforn?, 
									?idempresa?,
									'?un?', 
									'?qtd?', 
									'?qtdsol?', 
									'?nfe?', 
									?idprodserv?, 
									?idtipoprodserv?, 
									?idcontaitem?, 
									'?usuario?', 
									now(), 
									'?usuario?', 
									now())";
	}

	public static function inserirNfItemAPP()
	{
		return "INSERT INTO nfitem (idnf, 
                                	tiponf, 
									idempresa,
									qtd, 
									idtipoprodserv, 
									idcontaitem, 
									prodservdescr, 
									moeda,
									vlritem,
									basecalc,
									total,
									nfe,
									criadopor, 
									criadoem, 
									alteradopor, 
									alteradoem)
							VALUES (?idnf?, 
									'?tiponf?', 
									?idempresa?, 
									?qtd?,
									?idtipoprodserv?, 
									?idcontaitem?, 
									'?prodservdescr?', 
									'?moeda?', 
									'?vlritem?', 
									'?basecalc?', 
									'?total?', 
									'?nfe?',
									'?usuario?', 
									now(), 
									'?usuario?', 
									now())";
	}

	public static function buscarNfitemPorIdnfitem()
	{
		return "SELECT 
						*
					FROM
						nfitem
					WHERE
						idnfitem = ?idnfitem?";
	}

	public static function buscarNfitemPorIdnf()
	{
		return "SELECT 
						*
					FROM
						nfitem
					WHERE
						idnf = ?idnf?";
	}

	public static function buscarReservaNfitemPorIdnf()
	{
		return "SELECT 
						lr.idlotereserva,lr.idlote,i.idnfitem,l.partida, l.exercicio,lr.qtd,f.idlotefracao,lr.tipoobjetoconsumoespec,lr.idobjetoconsumoespec
					FROM
						nfitem i,
						lotereserva lr
							JOIN
						lote l ON (l.idlote = lr.idlote
							AND l.status <> 'CANCELADO')
							JOIN
						lotefracao f ON (f.idlote = l.idlote
							AND f.status = 'DISPONIVEL'
							AND f.qtd >= lr.qtd)
							JOIN 
						unidade u ON ( f.idunidade = u.idunidade and u.idtipounidade = 21 )
					WHERE
						lr.status = 'PENDENTE'
							AND lr.idobjeto = i.idnfitem
							AND lr.tipoobjeto = 'nfitem'
							AND lr.qtd > 0
							AND i.idnf = ?idnf? ";
	}

	public static function deletarNfitemComissaoPorId()
	{
		return "DELETE FROM nfitemcomissao 
					WHERE
						idnfitem =?idnfitem?";
	}

	public static function buscarNfitemComConsumo()
	{
		return "SELECT 
					i.*
				FROM
					nfitem i
						JOIN
					lotecons c ON (c.idobjeto = i.idnfitem
						AND c.tipoobjeto = 'nfitem')
				WHERE
					i.nfe = 'Y' AND i.idnf =  ?idnf?
				GROUP BY i.idnfitem";
	}

	public static function buscarNfitemDanfe()
	{
		return "SELECT 
					COUNT(idnf) AS contador
				FROM
					nfitem
				WHERE
					idnf = ?idnf? AND nfe = 'Y'";
	}

	public static function buscarQtdpa()
    {
		return "SELECT IFNULL(SUM(i.qtd * IF(pf.valconv > 0, pf.valconv, 1)), 0) AS qtdpa
				  FROM nfitem i JOIN nf n ON (n.idnf = i.idnf AND n.tiponf = 'C' AND i.nfe = 'Y' AND status IN ('APROVADO', 'CONFERIDO'))
			 LEFT JOIN prodservforn pf ON (pf.idprodservforn = i.idprodservforn)
			 	 WHERE i.idprodserv = ?idprodserv?";
	}

	public static function somarFretePorIdnf()
	{
		return "SELECT 
					IFNULL(SUM(frete), 0) AS sumfrete
				FROM
					nfitem
				WHERE
					idnf = ?idnf?";
	}

	public static  function buscarNfitemPorIdobjetoTipoobjeto()
	{
		return "SELECT 
					*
				FROM
					nfitem i
				WHERE
					i.idobjetoitem = ?idobjetoitem?
						AND i.tipoobjetoitem = '?tipoobjetoitem?'
						and not exists(select 1 from contapagaritem c where c.idobjetoorigem=i.idnf and c.tipoobjetoorigem='nf' and c.status='QUITADO')";
	}

	public static function deletarNfitemPorId()
	{
		return "DELETE FROM nfitem 
					WHERE
						idnfitem =?idnfitem?";
	}

	public static  function buscarNfitemPorIdobjetoTipoobjetoIdconfcontapagar()
	{
		return "SELECT 
		*
	FROM
		nfitem
	WHERE
		idobjetoitem = ?idobjetoitem?
			AND tipoobjetoitem = '?tipoobjetoitem?'
			AND idconfcontapagar = ?idconfcontapagar?";
	}

	public static function buscarXmlNfItem()
    {
		return "SELECT x.idnfitemxml,
					   x.prodservdescr AS descr,
					   x.idprodserv,
					   p.descr AS descricaoprod,
					   x.qtd,
					   x.un,
					   x.valor,
					   x.des AS desconto,
					   x.vst,
					   x.cst,
					   x.cfop,
					   x.aliqicms AS aliq_icms,
					   x.valicms AS vicms,
					   x.valipi AS vipi,
					   x.frete,
					   x.fretepor,
					   x.descontopor,
					   x.descontoem,
					   x.freteem,
					   x.basecalc,
					   i.idnfitem,
					   x.outro,
					   x.cprod,
					   (SELECT COUNT(*) FROM nfitem ii WHERE ii.idnfitem = i.idnfitem) AS qtdnfitem
				  FROM nfitemxml x LEFT JOIN nfitem i ON (i.idnfitemxml = x.idnfitemxml) AND i.nfe = 'Y'
			 LEFT JOIN prodserv p ON (p.idprodserv = x.idprodserv)
				 WHERE x.status = 'Y' AND x.idnf = ?idnf?  group by  x.idnfitemxml";
	}

	public static function buscarProdutoItemProdservQueNaoExisteXml()
    {
		return "SELECT * FROM(
						SELECT p.idprodserv, p.codprodserv, IF(p.descrcurta IS NULL, p.descr, p.descrcurta) AS descr, i.vlritem
						FROM nfitem i JOIN prodserv p ON (p.idprodserv = i.idprodserv)
						WHERE ((i.nfe = 'Y') or (i.nfe = 'N' and i.cobrar='N'))
						?strand?
						AND i.idnf = ?idnf?
						AND i.qtd > 0
					UNION
						SELECT p.idprodserv, p.codprodserv, p.descr, '' as vlritem
						FROM prodserv p
						WHERE p.idprodserv = '?idprodserv?') as u
			  ORDER BY codprodserv";
	}

	public static function buscarSeExisteConversaoMoeda()
    {
		return "SELECT 1
				  FROM nfitem i
				 WHERE i.moedaext IN ('USD', 'EUR')
				   AND i.vlritemext IS NOT NULL
				   AND i.nfe = 'Y'
				   AND i.idnf = ?idnf?";
	}

	public static function buscarSeExisteConversaoMoedaInternacional()
    {
		return "SELECT 1
				  FROM nfitem i
				 WHERE i.moedaext IN ('USD', 'EUR')
				   AND i.vlritemext IS NOT NULL
				   AND i.moedainternacional = 'Y'
				   AND i.nfe = 'Y'
				   AND i.idnf = ?idnf?";
	}

	public static function listarItensCadastrados()
    {
		return "SELECT CONCAT(IFNULL(CONCAT(e.sigla, ' - '), ''), p.descr) AS descr,
					   p.tipo,
					   p.codprodserv,
					   p.ncm,
					   IFNULL(pf.codforn, CONCAT(IFNULL(CONCAT(e.sigla, ' - '), ''), pf.codforn)) AS codforn,
					   pf.unforn,
					   pf.converteest,
					   pf.valconv,
					   CASE i.un
					   	WHEN NULL THEN p.un
						WHEN '' THEN p.un
						ELSE i.un
					   END AS unidade,
					   p.local,
					   p.vlrvenda,
					   i.idnf,
					   i.idnfitem,
					   i.idprodserv,
					   i.idprodservformula,
					   i.qtd,
					   i.un,
					   i.qtdsol,
					   i.vlritem,
					   i.vlritemext,
					   i.convmoeda,
					   i.moeda,
					   i.moedaext,
					   i.frete,
					   i.total,
					   i.totalext,
                       i.des,
					   i.aliqipi,
					   i.vst,
					   i.idtipoprodserv,
					   i.idcontaitem,
					   i.prodservdescr,
					   i.qtddev,
					   i.valipi,
					   i.idpessoa,
					   i.idobjetoitem,
					   i.tipoobjetoitem,
                       i.aliqicms,
					   i.cfop,
					   i.valicms,
					   i.impostoimportacao,
					   i.pis,
					   i.cofins,
					   i.valicms,
					   l.idlote,
					   l2.idlote AS idlote2,
					   l2.partida AS partida2,
					   l2.exercicio AS exercicio2,
					   t.tipoprodserv,
					   p.imobilizado,
					   p.venda,
					   i.cobrar,
					   tnf.tiponf as tiponfobji,
                       i.idempresa
				  FROM nf n JOIN nfitem i ON i.idnf = n.idnf
				  JOIN prodserv p ON (p.idprodserv = i.idprodserv)
			 LEFT JOIN lote l ON (l.idnfitem = i.idnfitem and l.status != 'CANCELADO')
			 LEFT JOIN prodservforn pf ON (pf.idprodserv = i.idprodserv AND pf.idprodservforn = i.idprodservforn AND pf.status = 'ATIVO' AND pf.idpessoa = n.idpessoa)
			 LEFT JOIN tipoprodserv t ON (t.idtipoprodserv = p.idtipoprodserv)
			 LEFT JOIN empresa e ON (e.idempresa = p.idempresa)
			 LEFT JOIN lotecons lc ON ?joinConsumo? AND lc.qtdc > 0
			 LEFT JOIN lote l2 ON (l2.idlote = lc.idlote and l2.status != 'CANCELADO')
			 LEFT JOIN (SELECT tiponf, nobi.idnf FROM nf nobi) tnf ON tnf.idnf = i.idobjetoitem AND i.tipoobjetoitem = 'nf'
				 WHERE ((i.nfe = 'Y') or (i.nfe = 'N' and i.cobrar='N'))
				   AND n.idnf = ?idnf?
			  GROUP BY i.idnfitem
			  ORDER BY IF(i.moedaext IS NULL, '', p.ncm), p.descr";
	}

public static function atualizarNfitemImposto()
	{
		return "UPDATE nfitem 
				SET 
					idnf = NULL,
					dataitem = '?dataitem?',
					prodservdescr = '?prodservdescr?',
					total = '?total?',
					vlritem = '?total?'
				WHERE
					idnfitem = ?idnfitem?";
	}

	public static function atualizarNfitemValorFrete()
	{
		return "UPDATE nfitem 
				SET 
					frete = '?frete?'
				WHERE
					idnf = ?idnf?";
	}
	
	public static function buscarNfitemVendaPorIdnf()
	{
		return "SELECT 
						*
					FROM
						nfitem
					WHERE nfe ='Y' and 
						idnf = ?idnf?";
	}

	public static function listarItensSemCadastro()
    {
		return "SELECT CONCAT(IFNULL(CONCAT(e.sigla, ' - '), ''), p.descr) AS descr,
					   p.tipo,
					   p.codprodserv,
					   IFNULL(pf.codforn, CONCAT(IFNULL(CONCAT(e.sigla, ' - '), ''), pf.codforn)) AS codforn,
					   i.idprodservforn,
					   pf.unforn,
					   pf.converteest,
					   pf.valconv,
					   IF(i.un IS NULL, p.un, i.un) AS unidade,
					   p.local,
					   p.vlrvenda,
					   i.idnfitem,
					   i.idprodserv,
					   i.idprodservformula,
					   i.qtd,i.un,
					   i.qtdsol,
					   i.vlritem,
					   i.vlritemext,
					   i.convmoeda,
					   i.moeda,
					   i.moedaext,
					   i.frete,
					   i.total,
					   i.totalext,
                       i.des,
					   i.aliqipi,
					   i.vst,
					   i.idtipoprodserv,
					   i.idcontaitem,
					   i.prodservdescr,
					   i.qtddev,
					   i.valipi,
					   i.idpessoa,
					   i.idobjetoitem,
					   i.tipoobjetoitem,
                       i.aliqicms,i.cfop,i.valicms,
					   t.tipoprodserv,
					   p.venda,
					   i.cobrar, 
					   tnf.tiponf as tiponfobji,
                       i.idempresa
				  FROM nf n JOIN nfitem i ON i.idnf = n.idnf 
			 LEFT JOIN prodserv p ON (p.idprodserv = i.idprodserv)
			 LEFT JOIN prodservforn pf ON (pf.idprodserv = i.idprodserv AND pf.idprodservforn = i.idprodservforn AND pf.status = 'ATIVO' AND pf.idpessoa = n.idpessoa)
			 LEFT JOIN empresa e ON (e.idempresa = p.idempresa)
			 LEFT JOIN tipoprodserv t ON (t.idtipoprodserv = p.idtipoprodserv)
			 LEFT JOIN (SELECT tiponf, nobi.idnf FROM nf nobi) tnf ON tnf.idnf = i.idobjetoitem AND i.tipoobjetoitem = 'nf'
			 	 WHERE  ((i.nfe = 'Y') or (i.nfe = 'N' and i.cobrar='N'))
				   AND (i.idprodserv IS NULL OR i.idprodserv = '')
				   AND n.idnf = ?idnf?
			  ORDER BY i.prodservdescr";
	}

	public static function buscarRateioNfItemProdserv()
    {
		return "SELECT i.idnfitem,
					   IFNULL(p.descr, i.prodservdescr) AS descr,
					   i.total AS rateio,
					   ri.idrateio,
					   ri.idrateioitem,
					   IFNULL(rd.valor, 100) AS valorateio,
					   rd.*
				  FROM nfitem i LEFT JOIN prodserv p ON (p.idprodserv = i.idprodserv)
			 LEFT JOIN rateioitem ri ON (ri.idobjeto = i.idnfitem AND ri.tipoobjeto = 'nfitem')
			 LEFT JOIN rateioitemdest rd ON (rd.idrateioitem = ri.idrateioitem)
			 	  JOIN contaitem c ON (c.idcontaitem = i.idcontaitem AND c.somarelatorio = 'Y')
				 WHERE -- i.idpessoa IS NULL AND
				    i.qtd > 0
				   and i.nfe ='Y'
				   AND i.idnf = ?idnf?
			  GROUP BY i.idnfitem , rd.idrateioitemdest";
	}

	public static function buscarNfitemContaItem()
    {
		return "SELECT i.idprodserv, i.idnf, i.idnfitem, i.qtd
				  FROM nfitem i JOIN contaitem c ON (c.idcontaitem = i.idcontaitem AND c.somarelatorio = 'Y')
				 WHERE i.nfe = 'Y' 
				   AND i.idprodserv IS NULL
				   AND i.idpessoa IS NULL
				   AND i.qtd > 0
				   AND i.idnf = ?idnf?";
	}

	public static function buscarNfitemContaItemRateio()
    {
		return "SELECT p.idunidadeest,
					   i.idprodserv,
					   i.idnf,
					   rd.idrateioitemdest,
					   i.idnfitem,
					   i.qtd,
					   p.tempoconsrateio
				  FROM nfitem i JOIN contaitem c ON (c.idcontaitem = i.idcontaitem AND c.somarelatorio = 'Y')
				  JOIN prodserv p ON (p.idprodserv = i.idprodserv) 
			 LEFT JOIN rateioitem ri ON (ri.idobjeto = i.idnfitem AND ri.tipoobjeto = 'nfitem')
			 LEFT JOIN rateioitemdest rd ON (rd.idrateioitem = ri.idrateioitem)
				 WHERE i.nfe = 'Y' 
				   AND i.idpessoa IS NULL
				   AND i.qtd > 0
				   ?idprodserv?
				   AND i.idnf = ?idnf?;";
	}

	public static function buscarNfContaItemRateio()
	{
		return "SELECT i.idprodserv, 
					   i.idnf, 
					   rd.idrateioitemdest, 
					   i.idnfitem, 
					   i.qtd
				  FROM nfitem i JOIN contaitem c ON (c.idcontaitem = i.idcontaitem AND c.somarelatorio = 'Y')
			 LEFT JOIN rateioitem ri ON (ri.idobjeto = i.idnfitem AND ri.tipoobjeto = 'nfitem')
			 LEFT JOIN rateioitemdest rd ON (rd.idrateioitem = ri.idrateioitem)
				 WHERE i.nfe = 'Y' 
				   AND i.idprodserv IS NULL
				   AND i.idpessoa IS NULL
				   AND i.qtd > 0
				   AND i.idnf = ?idnf?";
	}

	public static function buscarNfItemSolcom()
	{
		return "SELECT SUM(si.qtdc) AS qtdcom,
					   i.qtd,
					   i.qtdsol,
					   s.idunidade,
					   si.idprodserv,
					   ROUND((SUM(si.qtdc) / i.qtdsol) * 100, 2) AS percentual
				  FROM nfitem i JOIN nf n ON (n.idnf = i.idnf)
				  JOIN cotacao c ON (c.idcotacao = n.idobjetosolipor AND n.tipoobjetosolipor = 'cotacao')
				  JOIN solcomitem si ON (si.idcotacao = c.idcotacao AND si.idprodserv = i.idprodserv)
				  JOIN solcom s ON (s.idsolcom = si.idsolcom)
				 WHERE i.idnfitem = ?idnfitem?
			  GROUP BY s.idunidade, si.idprodserv";
	}
	
	public static function buscarNfItemPorNfe()
	{
		return "SELECT *
				  FROM nfitem
				 WHERE idnf = ?idnf?
				   AND nfe = '?nfe?'
				   AND idprodserv IS NULL";
	}

	public static function buscarNfItemContaPagar()
	{
		return "SELECT c.idcontapagar,
					   n.tiponf AS tiponota,
					   DMA(c.datareceb) AS datarecebimento,
					   c.status,
					   c.valor,
					   c.obs AS obscontapagar,
					   i.*
				  FROM nfitem i JOIN contapagar c ON (c.idobjeto = i.idnf AND c.tipoobjeto = 'nf')
				  JOIN nf n ON (n.idnf = i.idnf)
				 WHERE i.idobjetoitem = ?idobjetoitem?
				   AND i.tipoobjetoitem = '?tipoobjetoitem?'";
	}

	public static  function buscarNfitemPorIdobjetoTipoobjetoEIdNfOrigemNew()
	{
		return "SELECT n.idnf, i.idobjetoitem
				  FROM nf n JOIN nfitem i ON (i.idnf = n.idnf)
				 WHERE n.idnforigem = ?idnforigem?
				   AND i.tipoobjetoitem = '?tipoobjetoitem?'";
	}

	public static  function buscarNfitemPorIdobjetoTipoobjetoEIdNfOrigem()
	{
		return "SELECT n.idnf
				  FROM nf n JOIN nfitem i ON (i.idnf = n.idnf)
				 WHERE n.idnforigem = ?idnforigem? 
				   AND i.idobjetoitem = ?idobjetoitem?
				   AND i.tipoobjetoitem = '?tipoobjetoitem?'";
	}

	public static  function buscarNfporServDesc()
	{
		return "SELECT DISTINCT (idnf) AS idnf
				  FROM nfitem
				 WHERE prodservdescr LIKE ('%?prodservdescr?%')";
	}

	public static  function buscarIdNfeNfItemPorObsNotNULLEIdNf()
	{
		return "SELECT RIGHT(obs, 44) AS idnfe
				  FROM nfitem
				 WHERE obs IS NOT NULL 
				   AND idnf = ?idnf?;";
	}

	public static  function listarItensNfParaDuplicar()
	{
		return "SELECT p.descr,
					   i.idnfitem,
					   i.qtdsol,
					   i.qtd,
					   (i.qtdsol - i.qtd) AS quant,
					   i.prodservdescr
				  FROM nfitem i LEFT JOIN prodserv p ON (i.idprodserv = p.idprodserv)
				 WHERE i.qtdsol > i.qtd
				   AND i.idnf = ?idnf?";
	}

	public static function buscarNfItemIdPessoaNuloNfe()
	{
		return "SELECT 1
				  FROM nfitem
				 WHERE idnf = ?idnf?
				   AND nfe = '?nfe?'
				   AND vlritem > 0
				   AND idpessoa IS NULL";
	}

	public static function buscarValorNfitemXmlNfItem()
	{
		return "SELECT (SELECT SUM(valor) + SUM(valipi) + SUM(outro) - SUM(des) + SUM(frete) + SUM(vst) FROM nfitemxml WHERE idnf = ?idnf? AND status = 'Y') AS valorxml,
    				   ROUND((SELECT SUM(i.total) + SUM(i.valipi) + SUM(n.frete / (SELECT COUNT(*) FROM nfitem ii WHERE ii.idnf = ?idnf? and  ((ii.nfe = 'Y') or (ii.nfe = 'N' and ii.cobrar='N'))))
								FROM nfitem i JOIN nf n ON (n.idnf = i.idnf) WHERE i.idnf = ?idnf? AND ((i.nfe = 'Y') or (i.nfe = 'N' and i.cobrar='N')  OR (i.nfe = 'N' AND i.idprodserv IS NULL))), 2) AS valor,
						(
							SELECT IF(count(1) > 0, 'Y', 'N')
							FROM nfitem i 
							JOIN nf n ON (n.idnf = i.idnf) 
							WHERE i.idnf = ?idnf? AND (
								(i.nfe = 'Y') or 
								(i.nfe = 'N' and i.cobrar='N') OR 
								(i.nfe = 'N' AND i.idprodserv IS NULL)
							) AND i.moedaext is not null AND i.moedaext != 'BRL'
						) as internacional";
	}

	public static function atualizarIdNfItemXmlNfItem()
	{
		return "UPDATE nfitemxml x JOIN nfitem i ON (i.idprodserv = ?idprodserv? AND i.idnf = x.idnf) 
				   SET i.idnfitemxml = x.idnfitemxml
				 WHERE x.idnfitemxml = ?idnfitemxml?";
	}

	public static function buscarLoteNfItemPorIndNf()
	{
		return "SELECT i.idnfitem,
					   ROUND((i.total / n.subtotal) * ?valor?, 4) AS novovalor,
					   ROUND(((i.total / n.subtotal) * ?valor?) / i.qtd, 4) AS novovalorcun,
					   i.idprodservforn,
					   l.qtdprod,
					   l.idlote,
					   l.idunidade,
					   i.total,
					   i.valipi,
					   i.idprodserv
				  FROM nfitem i JOIN nf n ON n.idnf = i.idnf
			 LEFT JOIN lote l ON l.idnfitem = i.idnfitem
				 WHERE i.nfe = 'Y' 
				   AND i.idnf = ?idnf?
				   ?condicaoProdserv?";
	}

	public static function buscarDadosNfitemLote()
	{	
		return "SELECT n.idnf,
					   l.idlote,
					   n.nnfe,
					   n.status,
					   n.dtemissao,
					   n.idobjetosolipor,
					   p.nome,
					   IFNULL(l.unpadrao, ps.un) AS unpadrao,
					   ROUND(IFNULL(l.vlrlote, 0), 4) AS valoritem,
					   ROUND(IFNULL((IF(i.moeda = 'USD', i.totalext, i.total) / (i.qtd * IF(IFNULL(ifnull(l.valconvori,f.valconv), 1) = 0.00, 1, IFNULL(ifnull(l.valconvori,f.valconv), 1)))), 0), 4) AS valoritem2,
					   ifnull(l.valconvori,f.valconv) as  valconv,
					   i.*
				  FROM nfitem i JOIN nf n JOIN pessoa p
			 LEFT JOIN lote l ON (l.idnfitem = i.idnfitem)
			 	  JOIN prodserv ps ON (ps.idprodserv = i.idprodserv)
			 LEFT JOIN prodservforn f ON (f.idprodservforn = i.idprodservforn)
				  JOIN fluxostatus mf ON n.idfluxostatus = mf.idfluxostatus
				 WHERE i.idprodserv = ?idprodserv?
				   AND p.idpessoa = n.idpessoa
				   and n.idempresafat is null
				   AND n.tiponf NOT IN ('D', 'R', 'T', 'E', 'V')
				   AND n.status NOT IN ('CANCELADO' , 'REPROVADO', 'TRANSFERIDO')
				   AND (i.total IS NOT NULL)
				   AND i.qtd > 0
				   AND i.idnf = n.idnf
				   AND i.nfe = 'Y'
				   AND (n.dtemissao > DATE_SUB(NOW(), INTERVAL ?consumodiasgraf? DAY) OR n.dtemissao IS NULL)
			  GROUP BY i.idnf, l.idlote
			  ORDER BY mf.ordem ASC, n.dtemissao DESC";
    }

	public static function buscarDadosNfitemServico()
	{	
		return "SELECT
					n.idnf,
					n.nnfe,
					n.status,
					n.dtemissao,
					n.idobjetosolipor,
					p.nome,
					ps.un AS unpadrao,
					ROUND(0, 4) AS valoritem,
					ROUND(IFNULL((IF(i.moeda = 'USD', i.totalext, i.total) / (i.qtd * IF(IFNULL(f.valconv, 1) = 0.00, 1, IFNULL(f.valconv, 1)))), 0), 4) AS valoritem2,
					f.valconv,
					i.*
				FROM nfitem i 
				JOIN nf n 
				JOIN pessoa p
				JOIN prodserv ps ON (ps.idprodserv = i.idprodserv)
				LEFT JOIN prodservforn f ON (f.idprodservforn = i.idprodservforn)
				JOIN fluxostatus mf ON n.idfluxostatus = mf.idfluxostatus
				WHERE i.idprodserv = ?idprodserv?
				AND p.idpessoa = n.idpessoa
				AND n.tiponf NOT IN ('D', 'R', 'T', 'E', 'V')
				AND n.status NOT IN ('CANCELADO' , 'REPROVADO', 'TRANSFERIDO')
				AND (i.total IS NOT NULL)
				AND i.qtd > 0
				AND i.idnf = n.idnf
				AND i.nfe = 'Y'
				AND (n.dtemissao > DATE_SUB(NOW(), INTERVAL ?consumodiasgraf? DAY) OR n.dtemissao IS NULL) 
				GROUP BY i.idnf
				ORDER BY mf.ordem ASC, n.dtemissao DESC
				";
    }

	public static function buscarFormuladosSemFormula()
	{
		return "SELECT l.idlote,
					   n.dtemissao,
					   DMA(n.dtemissao) AS dmadtemissao,
					   p.nome,
					   IFNULL(l.unpadrao, ps.un) AS unpadrao,
					   ROUND(IFNULL(l.vlrlote, 0), 2) AS valoritem,
					   ROUND(IFNULL((i.total / (i.qtd * IF(IFNULL(f.valconv, 1) = 0.00, 1, IFNULL(f.valconv, 1)))), 2))AS valoritem2
				  FROM nfitem i JOIN nf n JOIN pessoa p
			 LEFT JOIN lote l ON (l.idnfitem = i.idnfitem)
			 	  JOIN prodserv ps ON (ps.idprodserv = i.idprodserv)
			 LEFT JOIN prodservforn f ON (f.idprodservforn = i.idprodservforn)
			 	 WHERE i.idprodserv = ?idprodserv?
				   AND p.idpessoa = n.idpessoa
				   AND n.tiponf NOT IN ('D', 'R', 'T', 'E', 'V')
				   AND n.status NOT IN ('CANCELADO' , 'REPROVADO', 'ABERTO', 'ENVIADO')
				   AND n.dtemissao IS NOT NULL
				   AND (i.total IS NOT NULL)
				   AND i.qtd > 0
				   AND i.idnf = n.idnf
				   AND n.dtemissao > DATE_SUB(NOW(), INTERVAL ?consumodiasgraf? DAY)
				   AND i.nfe = 'Y'
			  ORDER BY n.dtemissao ASC";
	}

	public static function buscarServicosFormuladosSemFormula()
	{
		return "SELECT n.dtemissao,
					   DMA(n.dtemissao) AS dmadtemissao,
					   p.nome,
					   ps.un AS unpadrao,
					   ROUND(0, 2) AS valoritem,
					   ROUND(IFNULL((i.total / (i.qtd * IF(IFNULL(f.valconv, 1) = 0.00, 1, IFNULL(f.valconv, 1)))), 2))AS valoritem2
				  FROM nfitem i JOIN nf n JOIN pessoa p
			 	  JOIN prodserv ps ON (ps.idprodserv = i.idprodserv)
			 LEFT JOIN prodservforn f ON (f.idprodservforn = i.idprodservforn)
			 	 WHERE i.idprodserv = ?idprodserv?
				   AND p.idpessoa = n.idpessoa
				   AND n.tiponf NOT IN ('D', 'R', 'T', 'E', 'V')
				   AND n.status NOT IN ('CANCELADO' , 'REPROVADO', 'ABERTO', 'ENVIADO')
				   AND n.dtemissao IS NOT NULL
				   AND (i.total IS NOT NULL)
				   AND i.qtd > 0
				   AND i.idnf = n.idnf
				   AND n.dtemissao > DATE_SUB(NOW(), INTERVAL ?consumodiasgraf? DAY)
				   AND i.nfe = 'Y'
			  ORDER BY n.dtemissao ASC";
	}

	public static function buscarCotacaoNfitem()
	{
		return "SELECT DISTINCT(c.idcotacao) AS idcotacao
				  FROM nfitem i JOIN nf n ON (i.idnf = n.idnf AND n.tipoobjetosolipor = 'cotacao' ".getidempresa('n.idempresa', 'nf')." AND n.tiponf != 'V'
				   				 AND n.status IN ('ABERTO', 'APROVADO', 'ENVIADO', 'INICIO', 'RECEBIDO', 'RESPONDIDO', 'AUTORIZADO', 'AUTORIZADA'))
				  JOIN cotacao c ON n.idobjetosolipor = c.idcotacao
				  JOIN fluxostatus f ON f.idfluxostatus = n.idfluxostatus
				  JOIN "._DBCARBON."._status s ON s.idstatus = f.idstatus
				 WHERE i.nfe = 'Y' AND i.idprodserv = ?idprodserv?
			  GROUP BY i.idprodserv";
	}

	public static function buscarQtdFornecedorNfItem()
	{
		return "SELECT 1 FROM nfitem WHERE idprodservforn = ?idprodservforn?";
	}

	public static function buscarDataEnvioNfitem()
	{
		return "SELECT n.envio
				  FROM lotereserva c JOIN nfitem i ON (i.idnfitem = c.idobjeto)
				  JOIN nf n ON i.idnf = n.idnf
				WHERE c.idlote = ?idlote? AND c.qtd AND c.tipoobjeto = 'nfitem'
			 ORDER BY n.envio ASC LIMIT 1";
	}

	public static function buscarNnfePorIdNfItem()
	{
		return "SELECT n.nnfe, n.idnf, p.nome
				  FROM nfitem i JOIN nf n ON i.idnf = n.idnf
				  JOIN pessoa p ON p.idpessoa = n.idpessoa
				 WHERE n.status != 'CANCELADO'
				   AND i.idnfitem = ?idnfitem?";
	}

	public static function buscaNfitemFaturar()
	{
		return "SELECT 
						*
					FROM
						nfitem
					WHERE
						cobrar = 'Y' AND idnf = ?idnf?";
	}

	public static function buscarTagPorIdNfItem()
    {
        return "SELECT nia.idnfitemacao,
					   nia.idobjeto,                       
                       nia.idobjetoext,
					   nia.categoria,
                       nia.kmrodados
                  FROM nfitemacao nia 
                 WHERE nia.idnfitem = '?idnfitem?'";
    }

	public static function buscarItensTagPorIdNf()
    {
        return "SELECT nia.idnfitemacao,
					   nia.idobjeto,                       
                       nia.idobjetoext,
					   nia.categoria,
                       nia.kmrodados
                  FROM nfitemacao nia JOIN nfitem ni ON ni.idnfitem = nia.idnfitem
                 WHERE ni.idnf = '?idnf?'";
    }

	public static function buscarCategoriaDevolucao()
	{
		return "SELECT 
					c.idcontaitem, p.idtipoprodserv, t.idnatop
				FROM
					natop t
						JOIN
					nf n ON (n.idnatop = t.idnatop)
						LEFT JOIN
					contaitem c ON (c.status = 'ATIVO' AND c.devolucao = 'Y'
						AND c.idempresa = n.idempresa)
						LEFT JOIN
					contaitemtipoprodserv tp ON (tp.idcontaitem = c.idcontaitem)
						LEFT JOIN
					tipoprodserv p ON (p.idtipoprodserv = tp.idtipoprodserv
						AND p.status = 'ATIVO')
				WHERE
					t.status = 'ATIVO' AND t.finnfe = 4
						AND n.idnf = ?idnf?  limit 1";
	}

	public static function buscarCategoriaDevolucaoEntrada()
	{
		return "SELECT 
					c.idcontaitem, p.idtipoprodserv
				FROM
					finalidadeprodserv t
						JOIN
					nf n ON (n.idfinalidadeprodserv = t.idfinalidadeprodserv and t.devolucao='Y')
						JOIN
					contaitem c ON (c.status = 'ATIVO' AND c.devolucao = 'Y'
						AND c.idempresa = n.idempresa)
						JOIN
					contaitemtipoprodserv tp ON (tp.idcontaitem = c.idcontaitem)
						JOIN
					tipoprodserv p ON (p.idtipoprodserv = tp.idtipoprodserv
						AND p.status = 'ATIVO')
				WHERE
					t.status = 'ATIVO'
						AND n.idnf = ?idnf?  limit 1";

	}
	public static function buscarCategoriaCancelado()
	{
		return "SELECT 
					c.idcontaitem, p.idtipoprodserv
				FROM
				
					nf n 
						JOIN
					contaitem c ON (c.status = 'ATIVO' AND c.cancelamento = 'Y' and c.idempresa = n.idempresa)
						JOIN
					contaitemtipoprodserv tp ON (tp.idcontaitem = c.idcontaitem)
						JOIN
					tipoprodserv p ON (p.idtipoprodserv = tp.idtipoprodserv
						AND p.status = 'ATIVO')
				WHERE
						n.idnf = ?idnf?  limit 1";
	}


	public static function atualizarCategoriaSubcategoriaNfItem()
	{
		return "UPDATE nfitem SET idcontaitem = ?idcontaitem?, idtipoprodserv = ?idtipoprodserv? WHERE idnf = ?idnf?";
	}

	public static function buscarNfPorIdObjetoItem()
	{
		return "SELECT n.idnf,
					n.idobjetosolipor,
					n.tipoobjetosolipor,
					n.idpessoa,
					n.tpnf,
					n.tiponf,
					n.idunidade,
					n.diasentrada,
					n.dtemissao as dtemissaoorig,
					ni.idnfitem 
				FROM nfitem ni 
				JOIN nfitem ni2 ON ni2.idnfitem = ni.idobjetoitem AND ni.tipoobjetoitem = 'nfitem'
				JOIN nf n ON n.idnf = ni2.idnf
				WHERE ni.idnf = ?idnf?;";
	}

	public static function buscarInfoPedido() {
		return "SELECT p.descr,
						f.rotulo, 
						CONCAT(l.partida, '/', l.exercicio, '-', IFNULL(l.partidaext, '')) as partida, 
						i.qtd as qtdretirada,
						-- fr.qtdini as qtdestoque,
						lfm.qtd as qtdestoque,
						lfm.idlotefracaomov,
						IFNULL(s.descrcurta, s.descr) AS selo,
						l.idlote
				FROM nfitem i
				JOIN prodserv p ON i.idprodserv = p.idprodserv 
				JOIN prodservformula f ON f.idprodservformula = i.idprodservformula 
				JOIN lotecons c ON c.idobjeto = i.idnfitem AND c.tipoobjeto = 'nfitem' AND c.qtdd > 0
				JOIN lotefracao fr ON fr.idlotefracao = c.idlotefracao 
				JOIN lote l ON l.idlote = c.idlote
				LEFT JOIN lotecons c2 ON c2.idobjeto = l.idlote AND c2.tipoobjeto = 'lote' AND c2.qtdd > 0
				LEFT JOIN lote l2 ON l2.idlote = c2.idlote 
				LEFT JOIN prodserv s ON s.idprodserv = l2.idprodserv AND s.descr LIKE ('%SELO%')
				LEFT JOIN lotefracaomov lfm ON lfm.idlotefracao = c.idlotefracao
				WHERE i.idnfitem = ?idnfitem?;";
	}

	public static function buscarItensCategoriaESubCategoriaNula()
	{
		return "SELECT idnfitem, idtipoprodserv, idcontaitem 
				  FROM nfitem
				 WHERE idnf = ?idnf?
        		  AND (idtipoprodserv IS NULL OR idcontaitem IS NULL)
				  AND nfe = 'Y';";
	}

	public static function buscarProdutoPorNfItem()
	{
		return "SELECT p.geraloteautomatico,
					   p.un as un_prod,
					   p.descr,
					   p.idprodserv,
					   l.idlote,
					   ni.idprodserv,
					   ni.idnfitem,
					   ni.qtd,
					   ni.un as un_item,
					   ni.vlritem,
					   ni.cobrar, 
					   n.criadoem,
					   pe.cpfcnpj,
					   m.modulo,
                       u.unidade
				  FROM nfitem ni JOIN prodserv p ON p.idprodserv = ni.idprodserv
				  JOIN unidade u ON u.idunidade = p.idunidadeest
				  JOIN nf n ON n.idnf = ni.idnf
				  JOIN pessoa pe ON pe.idpessoa = n.idpessoa
			 LEFT JOIN lote l ON l.idnfitem = ni.idnfitem
			 LEFT JOIN unidadeobjeto o ON o.tipoobjeto = 'modulo' AND o.idunidade = p.idunidadeest AND o.idobjeto like 'lote%' AND  o.idobjeto NOT IN ('lotealertavendas', 'lotesformuladosmeio')
			 LEFT JOIN carbonnovo._modulo m ON m.modulo = o.idobjeto AND m.ready = 'FILTROS' AND m.modulotipo = 'lote' AND m.status = 'ATIVO'
				 WHERE ni.idnf = ?idnf?
				   AND ni.nfe = 'Y';";
	}

	public static function buscarItemValorNulo()
	{
		return "SELECT 1 
				  FROM nfitem ni JOIN empresa e ON e.idempresa = ni.idempresa
				 WHERE ni.vlritem = '0.00' 
				   AND ni.idnf = ?idnf? 
				   AND ni.nfe = 'Y'
				   AND e.filial = 'N';";
	}

	public static function buscaNfitem(){
		return "SELECT * FROM nfitem WHERE idnf = '?idnf?' AND idpessoa = '?idpessoa?' AND codigoitem = '?codigoitem?'";
	}
}
?>
