<?
class SolcomItemQuery
{
    public static function buscarItensSolcom()
    {
		return "SELECT idsolcom, idprodserv, urgencia FROM solcomitem WHERE idcotacao = ?idcotacao? AND idprodserv IN (?idprodservs?);";
	}

	public static function buscarItensSolcomPorNf()
    {
		return "SELECT si.idsolcom, si.idprodserv, si.urgencia, n.idnf, ni.idnfitem
				  FROM solcomitem si JOIN nfitem ni ON ni.idprodserv = si.idprodserv
                  JOIN nf n ON n.idnf = ni.idnf AND n.idobjetosolipor = si.idcotacao
				 WHERE ni.idnf = '?idnf?';";
	}

	public static function buscarSolcomQuantidadeItensSolcomCotacao()
    {
		return "SELECT count(idsolcomitem) AS count, idsolcomitem
				  FROM solcomitem si JOIN nfitem ni ON ni.idprodserv = si.idprodserv
				  JOIN nf n ON n.idnf = ni.idnf 
				 WHERE si.idsolcom IN (?idsolcom?) AND si.idprodserv = '?idprodserv?' AND n.idobjetosolipor = si.idcotacao
				   AND si.idcotacao = '?idcotacao?'
				   AND si.status = 'ASSOCIADO'
				   AND n.status NOT IN ('CANCELADO', 'REPROVADO')
			  GROUP BY si.idsolcomitem";
	}

	public static function buscarSolcomItemPorIdSolcomItem()
    {
		return "SELECT idprodserv, qtdc FROM solcomitem WHERE idsolcomitem = ?idsolcomitem?";
	}

	public static function buscarProdutosInseridosSolcomItem()
    {
		return "SELECT group_concat(idprodserv) AS stidprodserv FROM solcomitem WHERE idsolcom = ?idsolcom?;";
	}

	public static function atualizarStatusIdcotacaoSolcomItem()
    {
        return "UPDATE solcomitem SET idcotacao = ?idcotacao?, status = '?status?', alteradopor = '?usuario?', alteradoem = now() WHERE idsolcomitem = ?idsolcomitem?";
    }

	public static function atualizarSolcomItensAssociados()
    {
        return "UPDATE solcomitem SET idcotacao = ?novo_idcotacao?, alteradopor = '?usuario?', alteradoem = now() WHERE idcotacao = ?idcotacao? AND idprodserv = ?idprodserv?";
    }

	public static function buscarItensSolcomAssociadosSolmat()
	{
		return "SELECT ?colunas? 
					   si.idcotacao, 
					   si.idprodserv, 
					   si.idsolcomitem, 
					   si.urgencia, 
					   si.descr, 
					   si.un, 
					   si.status, 
					   si.qtdc, 
					   si.obs, 
					   si.idsolcomitem,
					   si.solmatautomatica,
					   si.qtdsolmatautomatica,
					   valormedio,
					   sm.idsolmat, 
					   st.idsolmat AS idsoltag, 
					   sm.tipo,
					   mc.descricao, 
					   wsi.idsolcomitem AS restauraitem, 
					   if(wsi.idsolcomitem IS NULL, false, true) AS restauraitemassociado, 
                       CASE WHEN wsi.idsolcomitem IS NULL THEN 2
                            WHEN si.status in ('REPROVADO') THEN 3 
                            WHEN si.status in ('CANCELADO') THEN 4
                            ELSE 1  
					   END AS ordem_itens,
					   COUNT(a.idarquivo) AS totalArquivo,
					   c.vinculo
                  FROM solcomitem si ?condicaoJoin?
             LEFT JOIN (SELECT si.idsolcomitem FROM solcomitem si JOIN solcom s ON s.idsolcom = si.idsolcom 
			 			 WHERE NOT EXISTS(SELECT 1 FROM cotacao c  JOIN nf n ON n.idobjetosolipor = c.idcotacao AND tipoobjetosolipor = 'cotacao' AND n.nnfe = 'Y'
                                            	   JOIN nfitem ni ON ni.idnf = n.idnf
										   WHERE c.idcotacao = si.idcotacao
                                             AND ni.idprodserv = si.idprodserv
                                             AND ((n.status IN ('APROVADO' , 'CONCLUIDO', 'CONFERIDO', 'AUTORIZADA', 'AUTORIZADO', 'RESPONDIDO', 'ENVIADO', 'ABERTO', 'DIVERGENCIA') AND ni.nfe = 'Y') OR n.status  IN ('ABERTO', 'PREVISAO', 'AGUARDANDO', 'TRANSFERIDO', 'DIVERGENCIA', 'RESPONDIDO', 'ENVIADO', 'APROVADO')))
                                             AND s.status IN ('APROVADO') 
                                             AND si.status NOT IN ('CANCELADO', 'REPROVADO')) wsi ON wsi.idsolcomitem = si.idsolcomitem
             LEFT JOIN modulocom mc ON mc.idmodulo = ?idsolcom? AND mc.modulo = 'solcom' AND mc.descricao LIKE CONCAT('%reprovou o item da compra%', si.descr, '%')
			 LEFT JOIN arquivo a ON a.idobjeto = si.idsolcomitem AND tipoobjeto = 'solcomitem'
			 LEFT JOIN solmatitem smi ON smi.idsolmatitem = si.idsolmatitem
             LEFT JOIN solmat sm ON sm.idsolmat = smi.idsolmat AND sm.tipo = 'MATERIAL' 
             LEFT JOIN solmat st ON st.idsolmat = si.idsoltagitem AND st.tipo = 'EQUIPAMENTOS'
			 LEFT JOIN prodservcontaitem pi ON pi.idprodserv = si.idprodserv
			 LEFT JOIN contaitem c ON c.idcontaitem = pi.idcontaitem 
                 WHERE si.idsolcom = ?idsolcom?  
			     ?condicaoAnd?
			GROUP BY si.idsolcomitem
             ORDER BY ordem_itens, si.criadoem";
	}

	public static function buscarItensSolcomCancelados()
	{
		return "SELECT 1 FROM solcomitem WHERE idsolcom = ?idsolcom? AND status IN ('CANCELADO', 'REPROVADO')";
	}

	public static function buscarItensSolcomAssociadosCotacao()
	{
		return "SELECT DISTINCT n.idnf, 
					   n.status, 
					   n.obsinterna, 
					   if(p.nomecurto = '', p.nome, p.nomecurto) AS nome, 
					   ni.qtd, 
					   ni.qtdsol, 
					   ni.vlritem, 
					   ni.total, 
					   n.previsaoentrega, 
					   st.rotulo
				  FROM cotacao c JOIN nf n ON n.idobjetosolipor = c.idcotacao
				  JOIN nfitem ni ON ni.idnf = n.idnf
				  JOIN solcomitem si ON si.idcotacao = c.idcotacao AND si.idprodserv = ni.idprodserv                           
				  JOIN pessoa p ON p.idpessoa = n.idpessoa
				  JOIN fluxostatus fx ON fx.idfluxostatus = n.idfluxostatus
				  JOIN "._DBCARBON."._status st ON st.idstatus = fx.idstatus
				 WHERE c.idcotacao = ?idcotacao? AND si.idprodserv = ?idprodserv?
				  AND ni.nfe = 'Y' AND n.status NOT IN ('REPROVADO', 'CANCELADO');";
	}

	public static function buscarItensSolcomGerarSolmat()
	{
		return "SELECT idsolcomitem, descr, qtdc
				  FROM solcomitem si
				 WHERE si.idsolmatitem IS NULL
				   AND si.idsolcom = ?idsolcom?
				   AND EXISTS (SELECT 1 FROM cotacao c JOIN nf n ON n.idobjetosolipor = c.idcotacao
								 JOIN nfitem ni ON ni.idnf = n.idnf AND n.status IN ('CONFERIDO', 'CONCLUIDO') AND ni.idprodserv = si.idprodserv
								WHERE si.idcotacao = c.idcotacao AND ni.nfe = 'Y')";
	}

	public static function buscarItensSolcomGerarSoltag()
	{
		return "SELECT idsolcomitem, descr, qtdc
				  FROM solcomitem si JOIN prodservcontaitem pi ON pi.idprodserv = si.idprodserv
				  JOIN contaitem c ON c.idcontaitem = pi.idcontaitem
				 WHERE si.idsoltagitem IS NULL
				   AND si.idsolcom = ?idsolcom?
				   AND c.vinculo = 'soltag'
				   AND EXISTS (SELECT 1 FROM cotacao c JOIN nf n ON n.idobjetosolipor = c.idcotacao
								 JOIN nfitem ni ON ni.idnf = n.idnf AND n.status IN ('CONFERIDO', 'CONCLUIDO') AND ni.idprodserv = si.idprodserv
								WHERE si.idcotacao = c.idcotacao AND ni.nfe = 'Y')";
	}

	public static function buscarQtdItensSolcomItem()
	{
		return "SELECT idsolcom FROM solcomitem WHERE idsolcom = ?idsolcom?";
	}

	public static function atualizarStatusSolcomItem()
	{
		return "UPDATE solcomitem SET status = '?status?' 
                 WHERE idsolcom = ?idsolcom?
                   AND status = 'PENDENTE'";
	}

	public static function atualizarSolmatSolcomItem()
	{
		return "UPDATE solcomitem SET idsolmatitem = '?idsolmatitem?' 
		    	 WHERE idsolcomitem = ?idsolcomitem?";
	}

	public static function atualizarSolmatSolTagItem()
	{
		return "UPDATE solcomitem SET idsoltagitem = '?idsoltagitem?' 
		    	 WHERE idsolcomitem = ?idsolcomitem?";
	}

	public static function atualizarIdProdservPorIdSolcomItem()
	{
		return "UPDATE solcomitem SET idprodserv = '?idprodserv?' WHERE idsolcomitem = ?idsolcomitem?";
	}
}