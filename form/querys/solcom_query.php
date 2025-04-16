<?
class SolcomQuery
{
    public static function listarSolicitacaoCompraVincultadaCotacao()
    {
        return "SELECT CONCAT(e.sigla,' - ', s.idsolcom) AS siglaidsolcom, 
						 s.idsolcom,
						 s.criadoem, 
						 si.idsolcomitem, 
						 si.qtdc, 
						 si.un, 
						 CONCAT(e2.sigla,' - ', p.descr) AS descr, 
						 si.obs, 
						 si.urgencia,
						 si.dataprevisao,
						 si.idprodserv,
						 c.contaitem, 
						 pe.nomecurto,
						 ps.nome, 
						 p.estminautomatico,
						 p.tempocompra,
						 p.codprodserv,
						 uv2.descr AS unidadeprod,
						 ps.idpessoa, 
						 pf.unforn, 
						 pf.valconv,
						 pf.idprodservforn,
						 pf.codforn,
						 uv.descr AS unidadedescr,
						 count(pf.idprodservforn) AS qtdfornecedor
					FROM solcom s JOIN solcomitem si ON s.idsolcom = si.idsolcom 
					JOIN prodserv p ON p.idprodserv = si.idprodserv
					JOIN prodservcontaitem pc ON pc.idprodserv = p.idprodserv
					JOIN objetovinculo ov ON ov.idobjetovinc = pc.idcontaitem AND ov.tipoobjetovinc = 'contaitem'
					JOIN objetovinculo ov2 ON ov2.idobjetovinc = p.idtipoprodserv AND ov2.tipoobjetovinc = 'contaitemtipoprodserv' AND ov2.idobjeto = ?idobjeto? and ov2.tipoobjeto = '?tipoobjeto?'
					JOIN contaitem c ON c.idcontaitem = ov.idobjetovinc
					JOIN pessoa pe ON pe.idpessoa = s.idpessoa
					JOIN empresa e ON e.idempresa = s.idempresa
					JOIN empresa e2 ON e2.idempresa = p.idempresa
			   LEFT JOIN prodservforn pf ON pf.idprodserv = p.idprodserv AND pf.status = 'ATIVO' AND pf.multiempresa = 'N'
			   LEFT JOIN pessoa ps ON pf.idpessoa = ps.idpessoa
			   LEFT JOIN unidadevolume uv ON uv.un = pf.unforn
			   LEFT JOIN unidadevolume uv2 ON uv2.un = p.un
				   WHERE s.status IN ('APROVADO', 'CONCLUIDO') AND (si.status = 'PENDENTE')
					 AND ov.idobjeto = ?idobjeto? and ov.tipoobjeto = '?tipoobjeto?'
					 AND p.idempresa = ?idempresa?
				GROUP BY pf.idprodservforn, s.idsolcom
				ORDER BY p.idprodserv, c.contaitem,s.idsolcom";
    }

	public static function buscarQuantidadeItensSolcomPorIdSolcolmItem()
	{
		return "SELECT s.idsolcom, s.idempresa 
				  FROM solcomitem si JOIN solcom s ON s.idsolcom = si.idsolcom 
				 WHERE si.idsolcom = (SELECT idsolcom FROM solcomitem WHERE idsolcomitem = ?idsolcomitem?)";
	}

	public static function buscarComentarioSolcom()
	{
		return "SELECT s.idsolcom, sc.descricao, sc.idmodulo, sc.criadoem, IFNULL(p.nomecurto, sc.criadopor) AS criadopor
                FROM solcom s JOIN modulocom sc ON s.idsolcom = sc.idmodulo
                JOIN pessoa p ON p.usuario = sc.criadopor
               WHERE s.idsolcom = ?idsolcom? AND sc.modulo = 'solcom'
            ORDER BY sc.criadoem DESC";
	}

	public static function buscarDadosProdutoPorIdsolcomItem()
	{
		return "SELECT s.idunidade, s.idempresa, si.qtdc, p.descr, p.un, p.idprodserv, s.idsolcom 
				  FROM solcom s JOIN solcomitem si ON s.idsolcom = si.idsolcom
				  JOIN prodserv p ON p.idprodserv = si.idprodserv
				 WHERE si.idsolcomitem = ?idsolcomitem? AND (idsolmatitem IS NULL OR idsoltagitem IS NULL)";
	}

	public static function atualizarStatusSolcom()
	{
		return "UPDATE solcom SET status = '?status?' WHERE idsolcom = ?idsolcom?";
	}

	public static function inserirSolmatItem()
	{
		return "INSERT INTO solmatitem (idempresa, 
										idsolmat, 
										qtdc, 
										idprodserv, 
										descr, 
										un, 
										criadopor, 
										criadoem, 
										alteradopor, 
										alteradoem)
								 VALUES ?idempresa?, 
										?idsolmat?, 
										'?qtdc?', 
										'?idprodserv?', 
										'?descr?', 
										'?un?',
										'?usuario?',
										sysdate(),
										'?usuario?',
										sysdate())";
	}
}
?>