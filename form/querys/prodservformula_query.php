<?
require_once(__DIR__."/_iquery.php");

class ProdservFormulaQuery implements DefaultQuery
{
    public static $table = 'prodservformula';
    public static $pk = 'idprodservformula';

    public const buscarPorChavePrimaria = "SELECT t.*
                                            FROM ?table? t 
                                            WHERE ?pk? = ?pkval?";

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimaria, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }  

	public static function buscarProdutosFormuladosEmAlertaDeProducao()
    {
        return "SELECT 
        u2.*,        
        u4.idloteprod,
        IFNULL(u3.y, 0) AS y
    FROM
        (SELECT 
                pf.idprodservformula,
                p.idprodserv,
                p.idempresa,
                p.descr,                
                e.sigla,
                CONCAT(pf.rotulo, '-', IFNULL(pf.dose, '--'), ' Doses ', ' (', pf.volumeformula, ' ', pf.un, ')') AS rotulo,
                pf.estmin,
                pf.estmin_exp,
                pf.estminautomatico,
                pf.temporeposicao,
                pf.idunidadeest,
                SUM(IFNULL(f.qtd, 0)) AS total,
                (SELECT 
                        IFNULL(SUM(qf.qtd), 0)
                    FROM
                        lote q
                    JOIN lotefracao qf ON (qf.idlote = q.idlote
                        AND qf.idunidade IN (?idUnidadePadrao?)) AND q.piloto = 'N'
                    WHERE
                        q.idprodserv = p.idprodserv
                            AND pf.idprodservformula = q.idprodservformula
                            AND q.status = 'QUARENTENA') AS quar,
                (SELECT IFNULL(SUM(lr.qtd), 0)
                    FROM lote lrc
                    JOIN lotereserva lr ON lr.idlote = lrc.idlote AND lrc.piloto = 'N'
                    WHERE lrc.idprodserv = p.idprodserv AND lrc.status NOT IN ('CANCELADO', 'REPROVADO') AND lr.status = 'PENDENTE' AND lr.tipoobjeto = 'nfitem') AS qtdlotereserva,
                p.idtipoprodserv AS idtipoprodserv,
                pc.idcontaitem AS idcontaitem,
                m.modulo,
                u3.unidade,
                u3.idunidade
            FROM
                prodserv p
            JOIN prodservformula pf ON (pf.idprodserv = p.idprodserv
                AND pf.estmin IS NOT NULL
                AND pf.estmin != 0.00
                AND pf.status IN ('ATIVO' , 'REVISAO')
                AND pf.idunidadealerta IN (?idUnidadePadrao?))
            JOIN unidade u3 ON u3.idunidade = pf.idunidadealerta
            JOIN unidadeobjeto uo ON uo.tipoobjeto = 'modulo'
                AND uo.idunidade = u3.idunidade
            JOIN "._DBCARBON."._modulo m ON m.modulo = uo.idobjeto
                AND m.status = 'ATIVO'
                AND m.modulotipo IN (?modulotipo?)
            LEFT JOIN lote l ON (l.idprodserv = p.idprodserv
                AND l.status in ('APROVADO')
                AND l.idloteorigem IS NULL
                AND l.idprodservformula = pf.idprodservformula)
            LEFT JOIN lotefracao f ON (f.idlote = l.idlote
                AND f.idunidade = pf.idunidadeest
                AND f.status = 'DISPONIVEL')
                AND l.piloto = 'N'
            LEFT JOIN empresa e ON (e.idempresa = p.idempresa)
            LEFT JOIN prodservcontaitem pc ON ((pc.idprodserv = p.idprodserv))
            WHERE p.tipo = 'PRODUTO'
                    AND p.status = 'ATIVO'
                    AND p.fabricado = 'Y'
                    ?clausulaVenda?    
            GROUP BY pf.idprodservformula) u2
    LEFT JOIN
            (SELECT 
                n.idprodservformula,
                ?campoSomaqtd?
            FROM lote n JOIN lotefracao lf ON lf.idlote = n.idlote
            JOIN prodserv p ON (p.idprodserv = n.idprodserv AND p.fabricado = 'Y') 
            ?joinProdserFormula?
            WHERE
                ?wherestatus?
                ?condicaoUnidadePadrao?
                AND n.idloteorigem IS NULL
            GROUP BY n.idprodservformula) u3 ON u3.idprodservformula = u2.idprodservformula
    LEFT JOIN
            (SELECT 
                n.idlote AS idloteprod,
                n.idprodservformula
            FROM
                lote n
            JOIN prodserv p ON (p.idprodserv = n.idprodserv
                AND p.fabricado = 'Y') 
            WHERE
                 n.status IN ('QUARENTENA','PROCESSANDO','LIBERADO', 'TRIAGEM') 
                AND n.idunidade IN (?idUnidadePadrao?)
                AND n.idloteorigem IS NULL
            GROUP BY n.idprodservformula) u4 ON u4.idprodservformula = u2.idprodservformula
        ?whereminimo?            
        ORDER BY idempresa , descr, idcontaitem, idunidade, idtipoprodserv";
    }

    public static function buscarDadosConsumoFormula(){
        return "SELECT 
            f.estmin,
            f.mediadiaria,
            f.tempocompra,
            IFNULL(SUM(lf.qtd), 0) AS vqtdest
        FROM
            prodserv p
                JOIN
            prodservformula f ON (f.idprodserv = p.idprodserv
                AND f.status = 'ATIVO')
                LEFT JOIN
            lote l ON (l.idprodserv = p.idprodserv
                AND l.idprodservformula = f.idprodservformula
                AND l.status IN ('APROVADO' , 'QUARENTENA'))
                LEFT JOIN
            lotefracao lf ON (lf.idlote = l.idlote
                AND lf.idunidade = f.idunidadeest
                AND lf.status = 'DISPONIVEL')
        WHERE
            p.status = 'ATIVO' AND p.fabricado = 'Y'
                AND p.tipo = 'PRODUTO'
                AND f.idprodservformula = ?idprodservformula?
        GROUP BY f.idprodservformula";
    }

    public static function buscarInsumosFormulaResultado(){
        return "SELECT 
            *
        FROM
            prodservformula
        WHERE
            idprodserv = ?idtipoteste?
        AND status = 'ATIVO'
        ORDER BY ordem , idprodservformula ASC";
    }

    public static function buscarInsumosServicoEmAndamento()
    {
        return "SELECT p.descr,
                       i.qtdi,
                       i.qtdi_exp,
                       i.idprodserv,
                       i.status,
                       i.alteradoem,
                       i.alteradopor
                  FROM prodserv p JOIN prodservformulains i ON i.idprodserv = p.idprodserv
                  JOIN prodservformula f ON f.idprodservformula = i.idprodservformula
                 WHERE f.idprodserv = ?idprodserv?
                   AND f.idprodservformula = ?idprodservformula?
                   AND f.status = '?satusFormula?'
                   AND i.status = '?statusFormulaIns?'
              ORDER BY i.ord, i.alteradoem ASC";
    }

    public static function buscarValorVendaFormula()
    {
        return "SELECT 
                    IFNULL(vlrvenda, '0.00') AS vlrvenda,IFNULL(comissao,'0.00') AS comissao
                FROM
                    prodservformula
                WHERE
                    idprodservformula = ?idprodservformula?";
    }

    public static function buscarFormulaPorProdserv()
    {
        return "SELECT 
                    idprodservformula,
                    CONCAT(f.rotulo,
                            ' ',
                            IFNULL(f.dose, ' '),
                            ' ',
                            p.conteudo,
                            ' ',
                            ' (',
                            f.volumeformula,
                            ' ',
                            f.un,
                            ')') AS rotulo
                FROM
                    prodservformula f
                        JOIN
                    prodserv p ON (p.idprodserv = f.idprodserv)
                WHERE
                    f.idprodserv = ?idprodserv?";
    }

    public static function buscarFormulaAtivaPorProdserv()
    {
        return "SELECT 
                    idprodservformula,
                    CONCAT(f.rotulo,
                            ' ',
                            IFNULL(f.dose, ' '),
                            ' ',
                            p.conteudo,
                            ' ',
                            ' (',
                            f.volumeformula,
                            ' ',
                            f.un,
                            ')') AS rotulo
                FROM
                    prodservformula f
                        JOIN
                    prodserv p ON (p.idprodserv = f.idprodserv)
                WHERE f.status ='ATIVO'
                AND  f.idprodserv = ?idprodserv?";
    }

    public static function buscarRotuloFormulaPorId()
    {
        return "SELECT f.idprodservformula,
                    CONCAT(f.rotulo,
                            ' ',
                            IFNULL(f.dose, ' '),
                            ' ',
                            p.conteudo,
                            ' ',
                            ' (',
                            f.volumeformula,
                            ' ',
                            f.un,
                            ')') AS rotulo,p.descr,p.codprodserv
                FROM
                    prodservformula f
                        JOIN
                    prodserv p ON p.idprodserv = f.idprodserv
                WHERE
                    f.idprodservformula = ?idprodservformula?";
    }

    public static function listarProdservFormulaPlantel()
    {
        return "SELECT f.*, p.plantel
                  FROM prodservformula f LEFT JOIN plantel p ON (p.idplantel = f.idplantel)
                  JOIN fluxostatus fs ON fs.idfluxostatus = f.idfluxostatus
                 WHERE f.idprodserv = ?idprodserv? ?condicaoWhere?
              ORDER BY fs.ordem, f.status, f.ordem, f.idprodservformula ASC";
    }

    public static function buscarIdprodservFormula()
    {
        return "SELECT idprodservformula 
                  FROM prodservformula 
                 WHERE idprodserv = ?idprodserv?
                   AND status != 'INATIVO' 
              ORDER BY idprodservformula DESC 
                 LIMIT 1";
    }

    public static function buscarProdServFormulaPorIdProdServEStatus()
    {
        return "SELECT * 
                FROM prodservformula 
                WHERE status = '?status?' 
                AND idprodserv = ?idprodserv?";
    }

    public static function atualizarCalculoEstoqueProdservFormula()
    {
        return "UPDATE prodservformula 
                   SET qtdest = '?qtdest?',
                       destoque = '?destoque?',
                       mediadiaria = '?mediadiaria?',
                       estminautomatico = '?estminautomatico?',
                       pedidoautomatico = '?pedidoautomatico?',
                       pedido_automatico = '?pedido_automatico?',
                       sugestaocompra2 = '?sugestaocompra2?',
                       alteradopor = '?usuario?',
                       alteradoem = ?alteradoem?
                 WHERE idprodservformula = ?idprodservformula?";
    }

    public static function buscarFormulaPlantelObjeto()
    {
        return "SELECT f.*
                  FROM plantelobjeto o JOIN prodservformula f ON (f.idprodserv = o.idobjeto AND f.idplantel = o.idplantel AND f.status = 'ATIVO')
                 WHERE o.idplantelobjeto = ?idplantelobjeto?";
    }

    public static function buscarProdservFormulaInsPorIdProdservFormulaIns()
    {
        return "SELECT ps.idprodservformula, ps.versao, ps.editar
                  FROM prodservformulains pi JOIN prodservformula ps ON ps.idprodservformula = pi.idprodservformula
                 WHERE pi.idprodservformulains = ?idprodservformulains?";
    }

    public static function buscarProdservDeFormulaEFormulaInsPorStatusEIdProdservFormula()
    {
        return "SELECT DISTINCT (sem.idprodserv) AS idprodserv
                  FROM prodservformula lf JOIN prodservformulains f ON (f.idprodservformula = lf.idprodservformula)
                  JOIN prodserv p ON (p.idprodserv = f.idprodserv  AND p.especial = 'Y' AND p.status = 'ATIVO')
                  JOIN prodservformula ps ON (lf.idplantel = ps.idplantel AND p.idprodserv = ps.idprodserv AND ps.status = 'ATIVO')
                  JOIN prodservformulains psi ON (ps.idprodservformula = psi.idprodservformula)
                  JOIN prodserv sem ON (sem.idprodserv = psi.idprodserv AND sem.descr LIKE 'semente%' AND sem.status = 'ATIVO' AND sem.especial = 'Y' AND sem.idprodserv NOT IN (2567, 2568, 2659, 2574, 3882, 3881))
                 WHERE lf.idprodservformula = ?idprodservformula?
                   AND psi.status = 'ATIVO'
                   AND lf.status = 'ATIVO' 
             UNION 
                SELECT DISTINCT (sem1.idprodserv) AS idprodserv
                  FROM prodservformula lf JOIN prodservformulains f ON (f.idprodservformula = lf.idprodservformula)
                  JOIN prodserv p ON (p.idprodserv = f.idprodserv AND p.especial = 'Y' AND p.status = 'ATIVO')
                  JOIN prodservformula ps ON (lf.idplantel = ps.idplantel AND p.idprodserv = ps.idprodserv AND ps.status = 'ATIVO')
                  JOIN prodservformulains psi ON (ps.idprodservformula = psi.idprodservformula)
                  JOIN prodserv sem ON (sem.idprodserv = psi.idprodserv AND sem.descr NOT LIKE 'semente%' AND sem.status = 'ATIVO' AND sem.especial = 'Y' AND sem.idprodserv NOT IN (2567, 2568, 2659, 2574, 3882, 3881))
                  JOIN prodservformula psf ON (sem.idprodserv = psf.idprodserv AND psf.status = 'ATIVO')
                  JOIN prodservformulains psfi ON (psf.idprodservformula = psfi.idprodservformula)
                  JOIN prodserv sem1 ON (sem1.idprodserv = psfi.idprodserv AND sem1.descr LIKE 'semente%' AND sem1.status = 'ATIVO' AND sem1.especial = 'Y' AND sem1.idprodserv NOT IN (2567, 2568, 2659, 2574, 3882, 3881))
                 WHERE lf.idprodservformula = ?idprodservformula?
                   AND psi.status = 'ATIVO'
                   AND lf.status = 'ATIVO'";
    }

    public static function buscarInsumoPorIdProdserv()
    {
        return "SELECT p.idprproc,
                       p.proc,
                       pai.idprativ,
                       pai.idprocprativinsumo,
                       pai.idprodservprproc,
                       pi.idprodservformulains,
                       pi.idprodserv,
                       pi.qtdi,
                       pi.qtdi_exp
                  FROM prodservformula f JOIN prodservformulains pi ON pi.idprodservformula = f.idprodservformula
                  JOIN procprativinsumo pai ON pai.idprodservformulains = pi.idprodservformulains
                  JOIN prodservprproc pp ON pp.idprodservprproc = pai.idprodservprproc
                  JOIN prproc p ON p.idprproc = pp.idprproc
                 WHERE f.idprodserv = ?idprodserv?
                   AND pi.status = 'ATIVO'";
    }

    public static function buscarVolumeEQtdProdservFormula()
    {
        return "SELECT IFNULL(f.volumeformula, '') AS volumeform,
                       IFNULL(f.qtdpadraof, '') AS qtdpadrao,
                       IFNULL(f.qtdpadraof_exp, '') AS qtdpadrao_exp,
                       f.un
                  FROM prodservformula f
                 WHERE f.idprodservformula = ?idprodservformula?";
    }

    public static function buscarProcessoLigadoFormula()
    {
        return "SELECT pp.idprproc
                  FROM prodservformula f JOIN prodservformulains pi ON pi.idprodservformula = f.idprodservformula
                  JOIN procprativinsumo pai ON pai.idprodservformulains = pi.idprodservformulains
                  JOIN prodservprproc pp ON pai.idprodservprproc = pp.idprodservprproc
                 WHERE f.idprodservformula = ?idprodservformula?
                   AND pi.status = 'ATIVO'
                 LIMIT 1";
    }

    public static function buscarProcessoServico()
    {
        return "SELECT s.idprproc
                  FROM prodservformula f JOIN prodserv p ON (p.idprodserv = f.idprodserv AND p.tipo = 'SERVICO')
                  JOIN prodservprproc s ON (s.idprodserv = p.idprodserv)
                 WHERE f.idprodservformula = ?idprodservformula?
                 LIMIT 1";
    }

    public static function buscarIdProdservFormulaPorIdProdserv()
    {
        return "SELECT 
                    f.idprodservformula,
                    CONCAT(f.rotulo,
                            ' ',
                            IFNULL(f.dose, ' '),
                            ' ',
                            p.conteudo,
                            ' ',
                            ' (',
                            f.volumeformula,
                            ' ',
                            f.un,
                            ')') AS rotulo
                FROM
                    prodservformula f
                        JOIN
                    prodserv p ON p.idprodserv = f.idprodserv
                WHERE
                    f.idprodserv = ?idprodserv?
                        AND f.status = 'ATIVO'";
    }

    public static function buscarInsumos()
    {
        return "SELECT  a.idamostra,
                        a.idregistro,
                        a.idunidade,
                        a.exercicio,
                        r.idresultado,
                        l2.idlote,
                        l2.partida,
                        l2.status statuslote,
                        l2.vencimento,
                        IF(l2.vencimento < SYSDATE(), 'red', '') AS corv,
                        sf.idsolfab,
                        sf.status statussolfab,
                        sf.idlote idlotesolfab,
                        sf.idsolfabitem,
                        a.status AS status,
                        (SELECT sf2.idsolfab 
                           FROM solfabitem si2 JOIN solfab sf2 ON sf2.idsolfab = si2.idsolfab
                          WHERE si2.idobjeto = l2.idlote
                           AND si2.tipoobjeto = 'lote'
                           AND sf2.status IN ('APROVADO', 'UNIFICADO')
                      ORDER BY sf2.idsolfab ASC LIMIT 1) AS ultimasolfab
                   FROM prodservformula f JOIN prodservformulains i2 ON i2.idprodservformula = f.idprodservformula
                   JOIN lote l2 ON l2.idprodserv = i2.idprodserv
                   JOIN lotefracao lf ON lf.idlote = l2.idlote
                   ?solfabitem?
                   JOIN resultado r ON r.idresultado = l2.idobjetosolipor
                   JOIN amostra a ON a.idamostra = r.idamostra
              LEFT JOIN (SELECT ta.idsolfab,
                                ta.idlote,
                                ta.status,
                                ti.idobjeto,
                                ti.idsolfabitem
                           FROM solfabitem ti JOIN solfab ta ON ta.idsolfab = ti.idsolfab
                          WHERE ti.tipoobjeto = 'lote') sf ON sf.idobjeto = l2.idlote
                  WHERE l2.tipoobjetosolipor = 'resultado'
                    -- AND l2.status!='CANCELADO'
                    AND l2.idobjetosolipor > 0
                    ?semsolfabitem?
                    AND a.idpessoa IN (?idpessoa?)
                    AND i2.status = 'ATIVO'
                    AND f.idprodserv = ?idprodserv? 
               ORDER BY a.exercicio, a.idregistro";
    }

    public static function atualizarCustoArvoreProdservFormula()
    {
        return "UPDATE prodservformula 
                   SET vlrcusto = '?vlrcusto?', atualizaarvore = 'Y'
                 WHERE idprodservformula = ?idprodservformula?";
    }

    public static function atualizarArvoreProdservFormula()
    {
        return "UPDATE prodservformula 
                   SET atualizaarvore = 'Y'
                 WHERE idprodservformula = ?idprodservformula?";
    }

    public static function inserirProdservFormulaComSelect()
    {
        return "INSERT INTO prodservformula (idempresa,
	                                         idprodserv,
                                             qtdpadraof,
                                             idplantel,
                                             especie,
                                             vlrvenda,
                                             vlrcusto,
                                             comissao,
                                             idunidadeest,
                                             idunidadealerta,
                                             status, 
                                             idfluxostatus,
                                             ordem,
                                             estmin,
                                             estmin_exp,
                                             estminautomatico,
                                             pedido,
                                             pedidoautomatico,
                                             temporeposicao,
                                             estoqueseguranca,
                                             tempocompra,
                                             pedido_automatico,
                                             destoque,
                                             atualizaarvore)
	                                  SELECT idempresa,
                                             idprodserv,
                                             0,
                                             idplantel,
                                             especie,
                                             vlrvenda,
                                             vlrcusto,
                                             comissao,
                                             idunidadeest,
                                             idunidadealerta,
                                             status,
                                             idfluxostatus,
                                             ordem,
                                             0,
                                             0,
                                             0,
                                             0,
                                             0,
                                             0,
                                             0,
                                             0,
                                             0,
                                             0,
                                             atualizaarvore
                                        FROM prodservformula
                                       WHERE idprodservformula = ?idprodservformula?";
    }
}

?>