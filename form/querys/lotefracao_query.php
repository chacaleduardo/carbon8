<?
require_once(__DIR__."/_iquery.php");

class LoteFracaoQuery implements DefaultQuery{

    public static $table = "lotefracao";
	public static $pk = "idlotefracao";

	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ['table'=>self::$table,'pk'=>self::$pk]) ;
	}

    public static function inserir()
    {
        return "INSERT INTO lotefracao (
                    idempresa, idlote, idunidade, idobjeto, 
                    tipoobjeto, idlotefracaoorigem, qtd, 
                    qtd_exp, qtdini, qtdini_exp, idtransacao, 
                    status, conferido, criadopor, criadoem, 
                    alteradopor, alteradoem, idlotebkp
                ) VALUES (
                    ?idempresa?, ?idlote?, ?idunidade?, ?idobjeto?, 
                    '?tipoobjeto?', '?idlotefracaoorigem?', ?qtd?, 
                    '?qtd_exp?', ?qtdini?, '?qtdini_exp?', ?idtransacao?, 
                    '?status?', '?conferido?', '?criadopor?', ?criadoem?, 
                    '?alteradopor?', ?alteradoem?, ?idlotebkp?
                )";
    }

    public static function inserirLoteFracao()
    {
        return "INSERT INTO lotefracao (idempresa,
                                        idunidade,
                                        qtd,
                                        qtdini,
                                        idlote,
                                        idtransacao,
                                        idlotefracaoorigem,
                                        criadopor,
                                        criadoem,
                                        alteradopor,
                                        alteradoem)
                                VALUES (?idempresa?,
                                        ?idunidade?,
                                        '?qtd?',
                                        '?qtdini?',
                                        ?idlote?,
                                        ?idtransacao?,
                                        ?idlotefracaoorigem?,
                                        '?usuario?',
                                        NOW(),
                                        '?usuario?',
                                        NOW())";
    }

    public static function inserirLoteFracaoStatus()
    {
        return "INSERT INTO lotefracao (idempresa,
                                        idunidade,
                                        qtd,
                                        qtdini,
                                        idlote,
                                        idtransacao,
                                        idlotefracaoorigem,
                                        status,
                                        criadopor,
                                        criadoem,
                                        alteradopor,
                                        alteradoem)
                                VALUES (?idempresa?,
                                        ?idunidade?,
                                        '?qtd?',
                                        '?qtdini?',
                                        ?idlote?,
                                        ?idtransacao?,
                                        ?idlotefracaoorigem?,
                                        '?status?',
                                        '?usuario?',
                                        NOW(),
                                        '?usuario?',
                                        NOW())";
    }

    public static function buscarInsumosServicoConcluido(){
        return "SELECT  
                    p.descr,
                    c.qtdd AS qtdi,
                    c.qtdd_exp AS qtdi_exp,
                    p.idprodserv,
                    r.idresultado
                from prodservformula f
                JOIN prodservformulains i on i.idprodservformula = f.idprodservformula
                join resultado r on r.idtipoteste = f.idprodserv
                join prodserv p on p.idprodserv = i.idprodserv
                JOIN lote l on p.idprodserv  = l.idprodserv
                JOIN lotefracao lf ON (lf.idlote = l.idlote)
                JOIN lotecons c ON (lf.idlote = c.idlote AND  lf.idlotefracao = c.idlotefracao AND c.idobjeto = r.idresultado AND c.tipoobjeto = 'resultado' AND c.tipoobjetoconsumoespec = 'prodservformula' AND c.idobjetoconsumoespec = f.idprodservformula)
                where r.idresultado = ?idresultado?
                and f.idprodservformula = ?idprodservformula?
                and i.status = 'ATIVO';";
    }

    public static function buscarPrimeiroDiaConsumoLote()
    {
        return "SELECT TIMESTAMPDIFF(DAY, cast(c.criadoem as date), UTC_DATE()) AS tempocriacaolote
                  FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                  JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd > 0 OR c.qtdc > 0) AND c.idlotefracao = lf.idlotefracao)
             LEFT JOIN solmatitem si ON si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem'
                 WHERE l.idprodserv = ?idprodserv? 
                   AND l.status NOT IN ('CANCELADO', 'CANCELADA') 
                   AND lf.idunidade = ?idunidade?
                   AND l.idprodservformula = ?idprodservformula?
                   AND c.criadoem > DATE_SUB(NOW(), INTERVAL 365 DAY)
              ORDER BY c.criadoem
                 LIMIT 1;";
    }

    public static function buscarLoteConsComSolmatItemPorIdUnidade()
    {
        return "SELECT c.idlotecons,
                       l.unpadrao,
                       c.tipoobjeto,
                       c.idobjeto,
                       c.qtdd,
                       c.qtdc,
                       l.partida,
                       l.exercicio,
                       c.criadoem,
                       c.criadopor,
                       c.obs,
                       c.idlote,
                       c.idlotefracao,
                       l.qtdprod_exp,
                       c.status,
                       lf.qtd,
                       lf.qtd_exp,
                       si.idsolmat
                  FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                  JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd > 0 OR c.qtdc > 0) AND c.idlotefracao = lf.idlotefracao)
             LEFT JOIN solmatitem si ON si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem'
                 WHERE l.idprodserv = ?idprodserv?
                   AND l.status NOT IN ('CANCELADO', 'CANCELADA')
                   AND lf.idunidade = ?idunidade?
                   ?in_str?
                   AND c.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiaslote? DAY)
              ORDER BY c.criadoem";
    }

    public static function buscarQtdLoteFracao()
    {
        return "SELECT qtd,qtd_exp,idlote from lotefracao where idlotefracao= ?idlotefracao?";
    }

    public static function buscarLotefracaoPorIdloteIdunidade()
    {
        return "SELECT 
                    idlotefracao
                FROM
                    lotefracao
                WHERE
                    idlote = ?idlote?
                        AND idunidade = ?idunidade?";
    }

    public static function buscarLoteFracaoPorIdLoteEIdUnidade()
    {
        return "SELECT * 
                FROM lotefracao 
                WHERE idlote = ?idlote? 
                AND idunidade = ?idunidade?";
    }

    public static function buscarInformacoesDaUnidadeDeOrigemPorIdLoteFracao()
    {
        return "SELECT 
                    u.convestoque,
                    l.valconvori as valconv,
                    l.converteest as convertido,
                    l.unlote,
                    l.idprodserv,
                    l.vunpadrao,
                    p.consometransf,
                    p.imobilizado,
                    u.idtipounidade
                FROM lotefracao f 
                JOIN lote l ON(l.idlote=f.idlote)
                JOIN unidade u ON(u.idunidade=f.idunidade)
                JOIN prodserv p ON(p.idprodserv=l.idprodserv)
                WHERE f.idlotefracao = ?idlotefracao?";
    }

    public static function buscarConsumoIntervalo60diasPorIdUnidadeEstIdProdservIdProdservFormula()
    {
        return "SELECT c.idlotefracao, c.qtdd, c.qtdc, c.status
                  FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                  JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd > 0 OR c.qtdc > 0) AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')
                 WHERE lf.idunidade = ?idunidadeest?
                   AND l.idprodserv = ?idprodserv?
                   AND l.status NOT IN ('CANCELADO', 'CANCELADA')
                   ?idprodservformula?
                   AND c.criadoem > DATE_SUB(NOW(), INTERVAL 60 DAY)";
    }

    public static function buscarConvEstoque()
    {
        return "SELECT u.convestoque
                  FROM lotefracao l 
                  JOIN unidade u ON (l.idunidade = u.idunidade)
                 WHERE l.idlotefracao = ?idlotefracao?";
    }

    public static function buscarFracaoPorLoteEUnidade()
    {
        return "SELECT lt.idunidade AS idunidadelt, l.*, f.idformalizacao
                  FROM lotefracao l LEFT JOIN formalizacao f ON (l.idlote = f.idlote)
                  JOIN lote lt ON (lt.idlote = l.idlote)
                 WHERE l.idlote = ?idlote?
                   AND l.idunidade = ?idunidade?";
    }

    public static function buscarFracaoPorIdLoteFracao()
    {
        return "SELECT * FROM lotefracao WHERE idlotefracao = ?idlotefracao?";
    }

    public static function atualizarIdLoteFracaoOrigemIdLoteFracaoLotefracao()
    {
        return "UPDATE lotefracao set idlotefracaoorigem = ?idlotefracaoorigem? where  idlotefracao = ?idlotefracao?";
    }

    public static function buscarSomasLoteFracao()
    {
        return "SELECT SUM(qtdini) AS qtdc,
                       SUM(qtdd) AS qtdd,
                       SUM(qtdd) - SUM(qtdini) AS qtddif,
                       qtdprod_exp
                  FROM (SELECT qtdini, 0 AS qtdd, l.qtdprod_exp
                          FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                         WHERE lf.idlote = ?idlote?
                           AND lf.idunidade = ?idunidade? 
                    UNION ALL 
                        SELECT c.qtdc AS qtdini, 0 AS qtdd, l.qtdprod_exp
                          FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                          JOIN lotecons c ON (c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')
                         WHERE lf.idlote = ?idlote?
                           AND lf.idunidade = ?idunidade?
                           AND c.qtdc > 0 
                    UNION ALL 
                        SELECT 0 AS qtdini, c.qtdd, l.qtdprod_exp
                          FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                          JOIN lotecons c ON (c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')
                         WHERE lf.idlote = ?idlote?
                           AND lf.idunidade = ?idunidade?
                           AND c.qtdd > 0) AS u";
    }

    public static function atualizarQtdLoteFracao()
    {
        return "UPDATE lotefracao lf JOIN lote l ON l.idlote = lf.idlote SET lf.qtd = '?qtd?', lf.qtdini = '?qtdini?' 
                 WHERE idnfitem = ?idnfitem?";
    }

    public static function atualizarLoteFracaoPorIdTransacao()
    {
        return "UPDATE lotefracao SET status = '?status?', qtd = '?qtdini?', qtdini = '?qtdini?' 
                 WHERE idtransacao = ?idtransacao?";
    }
    
}

?>