<?
class ContaPagarQuery
{

    public static function buscarFaturaNf()
    {
        return "SELECT parcela, c.datareceb AS vdatapagto, c.* 
            FROM contapagar c 
            WHERE c.idobjeto = ?idnf? 
                AND c.tipoobjeto='?tipo?'  
                AND c.status !='INATIVO'";
    }

    public static function buscarQuantidadeParcelasPorStatusTipoObjeto()
    {
        return "SELECT COUNT(*) AS quant
                  FROM contapagar
                 WHERE status = '?status?'
                   AND tipoobjeto = '?tipoobjeto?'
                   AND idobjeto = '?idobjeto?'";
    }

    public static function buscarQuantidadeBoletosRemessaItem()
    {
        return "SELECT COUNT(*) AS quant
                  FROM remessaitem i JOIN contapagar c ON c.idcontapagar = i.idcontapagar
                  JOIN remessa r ON r.idremessa = i.idremessa
                 WHERE c.tipoobjeto = '?tipoobjeto?'
                   AND c.idobjeto = ?idobjeto?";
    }

    public static function inserirContaPagarComIdContaItem()
    {
        return "INSERT INTO contapagar (idempresa,
                                        idcontaitem,
                                        idagencia,
                                        idpessoa,
                                        tipoobjeto,
                                        idobjeto,
                                        parcela,
                                        parcelas,
                                        valor,
                                        datapagto,
                                        datareceb,
                                        status,
                                        idfluxostatus,
                                        idformapagamento,
                                        tipo,
                                        visivel,
                                        intervalo,
                                        criadopor,
                                        criadoem,
                                        alteradopor,
                                        alteradoem)
                                 VALUES (?idempresa?,
                                        ?idcontaitem?,
                                        ?idagencia?,
                                        ?idpessoa?,
                                        '?tipoobjeto?',
                                        ?idobjeto?,
                                        '?parcela?',
                                        '?parcelas?',
                                        '?valor?',
                                        '?datapagto?',
                                        '?datareceb?',
                                        '?status?',
                                        ?idfluxostatus?,
                                        ?idformapagamento?,
                                        '?tipo?',
                                        '?visivel?',
                                        '?intervalo?',
                                        '?usuario?',
                                        now(),
                                        '?usuario?',
                                        now())";
    }

    public static function inserirContaPagarSemIdContaItem()
    {
        return "INSERT INTO contapagar (idempresa,
                                        idagencia,
                                        idpessoa,
                                        tipoobjeto,
                                        idobjeto,
                                        parcela,
                                        parcelas,
                                        valor,
                                        datapagto,
                                        datareceb,
                                        status,
                                        idfluxostatus,
                                        idformapagamento,
                                        tipo,
                                        visivel,
                                        intervalo,
                                        criadopor,
                                        criadoem,
                                        alteradopor,
                                        alteradoem)
                                 VALUES (?idempresa?,
                                        ?idagencia?,
                                        ?idpessoa?,
                                        '?tipoobjeto?',
                                        ?idobjeto?,
                                        '?parcela?',
                                        '?parcelas?',
                                        '?valor?',
                                        '?datapagto?',
                                        '?datareceb?',
                                        '?status?',
                                        ?idfluxostatus?,
                                        ?idformapagamento?,
                                        '?tipo?',
                                        '?visivel?',
                                        '?intervalo?',
                                        '?usuario?',
                                        now(),
                                        '?usuario?',
                                        now())";
    }

    public static function apagarContaPagarItemPorTipoObjetoOrigemJoinContaPagar()
    {
        return "DELETE cc.* 
                  FROM contapagar c JOIN contapagaritem cc ON cc.idobjetoorigem = c.idcontapagar
                 WHERE c.tipoobjeto = '?tipoobjeto?'
                   AND c.idobjeto = '?idobjeto?'
                   AND cc.tipoobjetoorigem = 'contapagar'
                   AND cc.status IN ('INICIO' , 'ABERTO')";
    }

    public static function apagarContaPagarItemPorIdContaPagarJoinContaPagar()
    {
        return "DELETE cc.* 
                  FROM contapagar c JOIN contapagaritem cc ON cc.idobjetoorigem = c.idcontapagar
                 WHERE c.tipoobjeto = '?tipoobjeto?'
                   AND c.idobjeto = '?idobjeto?'
                   AND cc.idcontapagar = c.idcontapagar
                   AND cc.status IN ('INICIO' , 'ABERTO')";
    }

    public static function apagarContaPagarItemPorTipoObjetoOrigem()
    {
        return "DELETE FROM contapagaritem WHERE tipoobjetoorigem = '?tipoobjeto?' AND idobjetoorigem = '?idobjeto?' AND status IN ('INICIO','PENDENTE','ABERTO')";
    }

    public static function apagarContaPagarPorTipoObjeto()
    {
        return "DELETE FROM contapagar WHERE tipoobjeto = '?tipoobjeto?' AND idobjeto = '?idobjeto?'  AND status != 'QUITADO'";
    }

    public static function buscarCreditoVencidoPorPessoa()
    {
        return "SELECT 
                    idcontapagar
                FROM
                    contapagar
                WHERE   
                    idpessoa = ?idpessoa? 
                    AND datapagto < NOW()
                    AND tipo = 'C'
                    AND status IN ('ABERTO' , 'PENDENTE')";
    }

    public static function buscarFaturaBoletoPorNf()
    {
        return "SELECT 
                        *
                    FROM
                        contapagar
                    WHERE
                        idobjeto = ?idnf? AND boletopdf = 'Y'
                            AND tipoobjeto IN ('nf')";
    }

    public static function buscarFaturaBoletoPorNotaFiscal()
    {
        return "SELECT idcontapagar,
                        parcela,
                        parcelas
                from contapagar 
                where idobjeto = ?idnotafiscal?
                    and boletopdf='Y'
                    and tipoobjeto = 'notafiscal'";
    }

    public static function buscarFaturaPorId()
    {
        return "SELECT 
                idcontapagar,
                parcela,
                parcelas,
                intervalo,
                formapagto,
                tipo,
                status,
                DMA(datapagto) AS datapagto,
                DMA(datareceb) AS datareceb,
                valor,
                obs,
                boletopdf
            FROM
                contapagar
            WHERE
                idcontapagar = ?idcontapagar?";
    }

    public static function buscarFaturaPorPedido()
    {
        return "SELECT 
                    a.boleto,
                    c.idcontapagar,
                    c.parcela,
                    c.parcelas,
                    c.intervalo,
                    c.formapagto,
                    c.tipo,
                    c.status,
                    DMA(c.datapagto) AS datapagto,
                    DMA(c.datareceb) AS datareceb,
                    c.valor,
                    c.obs,
                    c.boletopdf
                FROM
                    contapagar c
                        JOIN
                    agencia a ON (a.idagencia = c.idagencia)
                WHERE
                    c.idobjeto = ?idnf? AND c.tipo = 'D'
                        AND NOT EXISTS( SELECT 
                            1
                        FROM
                            contapagaritem i
                        WHERE
                            i.idcontapagar = c.idcontapagar
                                AND i.ajuste = 'N'
                                AND i.tipoobjetoorigem = 'nf')
                        AND (c.tipoobjeto LIKE ('nf%')
                        OR c.tipoobjeto LIKE ('gnre'))
                ORDER BY c.parcela";
    }

    public static function buscarComissaoPorIdnf()
    {
        return "SELECT * FROM(
                        SELECT 
                        i.idcontapagaritem,
                        i.datapagto,
                        i.valor,
                        i.status AS status_item,
                        p.nome,
                        p.idpessoa,
                        c.idcontapagar,
                        c.datareceb,
                        c.status,
                        i.parcelas,
                        i.parcela
                    FROM
                        contapagar ci
                            JOIN
                        contapagaritem i ON (ci.idcontapagar = i.idobjetoorigem
                            AND i.tipoobjetoorigem = 'contapagar')
                            JOIN
                        contapagar c ON (c.idcontapagar = i.idcontapagar and c.tipoespecifico='REPRESENTACAO')
                            JOIN
                        pessoa p ON (p.idpessoa = i.idpessoa ?stridpessoa? )
                    WHERE
                        ci.idobjeto = ?idnf?
                            AND NOT EXISTS( SELECT 
                                1
                            FROM
                                contapagaritem ii
                            WHERE
                                ii.idcontapagar = ci.idcontapagar)
                            AND ci.tipoobjeto LIKE ('nf%') 
                    UNION SELECT 
                        i.idcontapagaritem,
                        i.datapagto,
                        i.valor,
                        i.status AS status_item,
                        p.nome,
                        p.idpessoa,
                        c.idcontapagar,
                        c.datareceb,
                        c.status,
                        ci.parcelas,
                        ci.parcela
                    FROM
                        contapagaritem ci
                            JOIN
                        contapagaritem i ON (ci.idcontapagar = i.idobjetoorigem
                            AND i.tipoobjetoorigem = 'contapagar')
                            JOIN
                        contapagar c ON (c.idcontapagar = i.idcontapagar and c.tipoespecifico='REPRESENTACAO')
                            JOIN
                        pessoa p ON (p.idpessoa = i.idpessoa ?stridpessoa?)
                    WHERE
                        ci.idobjetoorigem =?idnf?
                            AND ci.tipoobjetoorigem LIKE 'nf%'
                )AS u GROUP BY idcontapagaritem ORDER BY parcela, nome";
    }

    public static function buscarTotalComissaoPorNf()
    {
        return "SELECT 
                c.idpessoa,
                ROUND(SUM(n.total * (c.pcomissao / 100)), 2) AS comissao
            FROM
                nfitem n
                    JOIN
                nfitemcomissao c ON (c.idnfitem = n.idnfitem)
                    JOIN
                pessoa p ON (p.idpessoa = c.idpessoa ?stridpessoa?)
                    -- AND p.status IN ('PENDENTE' , 'ATIVO')
            WHERE
                n.idnf = ?idnf?
                    AND n.nfe = 'Y'";
    }

    public static function buscarSeExisteItemFaturaPorObj()
    {
        return "SELECT 
                    COUNT(*) AS quant
                FROM
                    contapagar c
                WHERE
                    c.tipoobjeto = '?tipoobjeto?'
                        AND c.idobjeto = ?idobjeto?
                        AND EXISTS( SELECT 
                            1
                        FROM
                            contapagaritem i
                        WHERE
                            i.idcontapagar = c.idcontapagar
                                AND i.tipoobjetoorigem = 'contapagar')";
    }

    public static function buscarContapagarProgramadaPorIdobjeto()
    {
        return "SELECT 
                        c.*
                    FROM
                        contapagar c
                    WHERE
                        c.tipoobjeto = '?tipoobjeto?'
                            AND c.progpagamento = 'S'
                            AND c.idobjeto = ?idobjeto?";
    }

    public static function buscarContapagaritemProgramadaPorIdobjeto()
    {
        return "SELECT 
                    c.*
                FROM
                    contapagar c
                        JOIN
                    contapagaritem i ON (i.idcontapagar = c.idcontapagar)
                WHERE
                    i.tipoobjetoorigem = '?tipoobjeto?'
                        AND i.idobjetoorigem = ?idobjeto?
                        AND c.progpagamento = 'S'";
    }
    public static function buscarContapagaritemComissaoProgramadaPorIdobjeto()
    {
        return "SELECT 
                    cc.*
                FROM
                    contapagar c,
                    contapagaritem cc
                WHERE
                    c.tipoobjeto = '?tipoobjeto?'
                        AND c.idobjeto = ?idobjeto?
                        AND c.progpagamento = 'S'
                        AND cc.idobjetoorigem = c.idcontapagar
                        AND cc.tipoobjetoorigem = 'contapagar'
                        AND cc.status IN ('INICIO' , 'ABERTO', 'PENDENTE')";
    }

    public static function buscarFaturaSemComissaoPorIdobjeto()
    {
        return "SELECT 
                        c.idcontapagar AS id
                    FROM
                        contapagar c
                    WHERE
                        c.tipoobjeto = '?tipoobjeto?'
                            AND c.status != 'QUITADO'
                            AND NOT EXISTS( SELECT 
                                1
                            FROM
                                contapagaritem i
                            WHERE
                                i.idcontapagar = c.idcontapagar
                                    AND i.tipoobjetoorigem = 'contapagar')
                            AND c.idobjeto = ?idobjeto? ";
    }

    public static function deletaFaturaSemComissaoPorIdobjeto()
    {
        return "DELETE c . * FROM contapagar c 
                    WHERE
                        c.tipoobjeto = '?tipoobjeto?'
                        AND c.status != 'QUITADO'
                        AND NOT EXISTS( SELECT 
                            1
                        FROM
                            contapagaritem i
                        
                        WHERE
                            i.idcontapagar = c.idcontapagar
                            AND i.tipoobjetoorigem = 'contapagar')
                        AND c.idobjeto = ?idobjeto?";
    }

    public static function somarValorFaturaPorIdobjeto()
    {
        return "SELECT 
                    SUM(valor) AS vvalor, MAX(parcela) AS mparcela, idempresa
                FROM
                    contapagar
                WHERE
                    idobjeto = ?idobjeto?
                        AND tipoobjeto = '?tipoobjeto?'";
    }

    public static function ajustarFaturaMaisUmCentavo()
    {
        return "UPDATE contapagar 
                    SET 
                        valor = valor + 0.01
                    WHERE
                        idobjeto = ?idobjeto?
                            AND tipoobjeto = '?tipoobjeto?'
                            AND status != 'QUITADO'
                            AND parcela = ?parcela?
                            AND idempresa = ?idempresa?";
    }

    public static function ajustarFaturaMenosUmCentavo()
    {
        return "UPDATE contapagar 
                SET 
                    valor = valor - 0.01
                WHERE
                    idobjeto = ?idobjeto?
                        AND tipoobjeto = '?tipoobjeto?'
                        AND parcela = 1
                        AND status != 'QUITADO'
                        AND idempresa = ?idempresa?";
    }

    public static function buscarSeExisteComissaoPendetePorIdobjeto()
    {
        return "SELECT 
                    COUNT(*) AS quant
                FROM
                    contapagar c,
                    contapagaritem cc
                WHERE
                    c.tipoobjeto = '?tipoobjeto?'
                        AND c.idobjeto = ?idobjeto?
                        AND cc.idobjetoorigem = c.idcontapagar
                        AND cc.tipoobjetoorigem = 'contapagar'
                        AND cc.status != 'QUITADO'";
    }

    public static function buscarContaPagarFormaPagamentoPorIdObejtoOrigem()
    {
        return "SELECT f.agrupado,
                       i.ajuste,
                       i.obs AS obsi,
                       f.descricao,
                       f.agruppessoa,
                       f.agrupnota,
                       i.idcontapagar,
                       i.parcela,
                       i.parcelas,
                       i.idformapagamento,
                       i.valor,
                       'contapagaritem' AS tobj,
                       i.idcontapagaritem,
                       i.status
                  FROM contapagaritem i JOIN contapagar cp ON (cp.idcontapagar = i.idcontapagar AND i.ajuste = 'N')
                  JOIN formapagamento f ON (f.idformapagamento = i.idformapagamento)
                 WHERE i.idobjetoorigem = ?idobjetoorigem?
                  AND i.tipoobjetoorigem LIKE '?tipoobjetoorigem?%' 
            UNION 
                SELECT f.agrupado,
                       'N' AS ajuste,
                       '' AS obsi,
                       f.descricao,
                       f.agruppessoa,
                       f.agrupnota,
                       c.idcontapagar,
                       c.parcela,
                       c.parcelas,
                       c.idformapagamento,
                       c.valor,
                       'contapagar' AS tobj,
                       c.idcontapagar AS idcontapagaritem,
                       c.status
                  FROM contapagar c JOIN formapagamento f ON (f.idformapagamento = c.idformapagamento)
                 WHERE c.idobjeto = ?idobjeto?
                   AND NOT EXISTS(SELECT 1 FROM contapagaritem i 
                                   WHERE i.idcontapagar = c.idcontapagar 
                                     AND i.ajuste = 'N' 
                                     AND i.idobjetoorigem = ?idobjetoorigem?
                                     AND i.tipoobjetoorigem LIKE 'nf%')
                   AND (c.tipoobjeto LIKE ('?tipoobjetoorigem?%') OR c.tipoobjeto LIKE ('?tipoobjeto?'))
              ORDER BY parcela";
    }

    public static function buscarNfContaPagar()
    {
        return "SELECT n.idnf,
                       n.status,
                       cp.idcontapagar,
                       cp.idobjeto,
                       cp.criadopor,
                       cp.criadoem AS criadoemcp,
                       fsh.*
                  FROM nf n JOIN contapagar cp ON cp.idobjeto = n.idnf
             LEFT JOIN (SELECT fsh.criadoem AS criadoemfs,
                               fsh.idfluxostatus,
                               fsh.idmodulo,
                               fs.idfluxo,
                               s.statustipo
                          FROM fluxostatushist fsh LEFT JOIN fluxostatus fs ON fs.idfluxostatus = fsh.idfluxostatus
                          JOIN " . _DBCARBON . "._status s ON s.idstatus = fs.idstatus AND s.statustipo = 'CONCLUIDO'
                         WHERE  modulo = '?modulo?') AS fsh ON fsh.idmodulo = n.idnf
                  WHERE fsh.idmodulo = ?idmodulo?
                    AND cp.idcontapagar = ?idcontapagar?
                    AND cp.criadoem > fsh.criadoemfs
                  LIMIT 1;";
    }

    public static function somarValorParcelasPorFatura()
    {
        return "SELECT 
                        SUM(valor) as nvalor
                FROM
                    contapagaritem
                WHERE
                    status != 'INATIVO'
                        AND idcontapagar = ?idcontapagar?";
    }

    public static function AtualizaValorFatura()
    {
        return "UPDATE contapagar 
        SET 
            valor = '?valor?'
        WHERE
            status != 'INATIVO'
                AND idcontapagar = ?idcontapagar?";
    }

    public static function AtualizarStatusContaPagar()
    {
        return "UPDATE contapagar SET status = '?status?', idfluxostatus = ?idfluxostatus? WHERE status != 'QUITADO' AND idcontapagar = ?idcontapagar?";
    }

    public static function buscarIdContaPagarPorIdFormapagamentoIdPessoaDataReceb()
    {
        return "SELECT idcontapagar FROM contapagar
                 WHERE idformapagamento = ?idformapagamento?
                   ?AndIdpessoa?
                   AND idagencia = ?idagencia?
                   AND idempresa = ?idempresa?
                   AND status = 'ABERTO'
                   AND tipoespecifico = '?tipoespecifico?'
                   AND datareceb >= '?datareceb?'
              ORDER BY datareceb ASC
                 LIMIT 1";
    }

    public static function atualizarValorContaPagar()
    {
        return "UPDATE contapagar SET valor = '?valor?', alteradopor = '?usuario?', alteradoem = NOW() WHERE idcontapagar = ?idcontapagar?";
    }

    public static function buscarIdContaPagar()
    {
        return "SELECT idcontapagar, status, valor
                  FROM contapagar 
                 WHERE idobjeto = ?idobjeto? 
                   AND tipoobjeto = '?tipoobjeto?' 
                   ?andParcela?
                   AND status != 'INATIVO'
                ORDER BY idcontapagar DESC";
    }

    public static function buscarIdContbuscarNotasDiferentesAtualContaPagaraPagar()
    {
        return "SELECT 1
                  FROM contapagar 
                 WHERE idpessoa = ?idpessoa? 
                   AND idempresa = ?idempresa?
                   AND tipoespecifico = 'AGRUPAMENTO'
                   AND idformapagamento = ?idformapagamento?
                   AND tipoobjeto = '?tipoobjeto?'
                   AND idobjeto <> ?idobjeto?
                   AND idcontapagar = ?idcontapagar?";
    }


    public static function atualizarContaPagarPorIdContaPagar()
    {
        return "UPDATE contapagar SET parcelas = ?parcelas?, valor = '?valor?', datapagto = '?datapagto?', datareceb = '?datapagto?' WHERE idcontapagar = ?idcontapagar?";
    }

    public static function atualizarContaPagarFormaPagamentoPorIdContaPagar()
    {
        return "UPDATE contapagar SET idagencia = ?idagencia?, idformapagamento = '?idformapagamento?', alteradoem = now(), alteradopor = '?alteradopor?' WHERE idcontapagar = ?idcontapagar?";
    }

    public static function apagarContaPagarAPartirdaParcela()
    {
        return "DELETE FROM contapagar WHERE tipoobjeto = '?tipoobjeto?' AND idobjeto = '?idobjeto?' AND parcela > ?parcela? AND status != 'QUITADO'";
    }

    public static function buscarExtratoAppPorEmpresaFormaPagamentoEPeriodo()
    {
        return "SELECT nfi.idnf, nfi.prodservdescr as descricao, i.obs, i.valor as total, nf.dtemissao
                from contapagar e
                join contapagaritem i on i.idcontapagar = e.idcontapagar
                join nf on nf.idnf = i.idobjetoorigem and tipoobjetoorigem = 'nf'
                join nfitem as nfi on nfi.idnf = nf.idnf
                where e.idformapagamento = ?idFormaPagamento?
                and e.idempresa = ?idEmpresa?
                and nf.dtemissao between '?dataInicio?' and '?dataFim?'
                and nf.app = 'Y'
                order by nf.dtemissao asc, nf.total asc";
    }

    public static function buscarExtratoAppPorContapagarIdFormaPagamentoEEmpresa()
    {
        return "SELECT c.idcontapagar, n.idnf, p.nome as descricao, c.obs, c.valor as total, n.dtemissao, c.idcontapagaritem
                FROM pessoa p 
                JOIN contapagaritem c ON c.tipoobjetoorigem = 'nf'
                JOIN nf n ON n.idnf = c.idobjetoorigem AND n.idpessoa = p.idpessoa  
                left JOIN pessoa ps ON (c.idpessoa = ps.idpessoa)
                LEFT JOIN formapagamento f ON (f.idformapagamento = c.idformapagamento)
                LEFT JOIN contapagar cp ON (cp.idcontapagar = c.idcontapagar)
                LEFT JOIN plantelobjeto po ON (po.idobjeto = n.idpessoa AND po.tipoobjeto = 'pessoa')
                LEFT JOIN plantel pl ON (pl.idplantel = po.idplantel)
                WHERE c.idcontapagar = ?idcontapagar?
                and c.status in ('ABERTO', 'PENDENTE')
                and cp.idformapagamento = ?idformapagamento?
                and cp.idempresa = ?idempresa?
                order by DATE(n.dtemissao) asc, c.valor asc;";
    }

    public static function buscarValorItensFaturamento() {
        return "SELECT sum(valor) as valor
                FROM (
                    SELECT i.valor
                  FROM contapagaritem i JOIN contapagar cp ON (cp.idcontapagar = i.idcontapagar AND i.ajuste = 'N')
                  JOIN formapagamento f ON (f.idformapagamento = i.idformapagamento)
                 WHERE i.idobjetoorigem = ?idobjetoorigem?
                    AND i.tipoobjetoorigem LIKE '?tipoobjetoorigem?%' 
                UNION ALL
                    SELECT c.valor
                    FROM contapagar c JOIN formapagamento f ON (f.idformapagamento = c.idformapagamento)
                    WHERE c.idobjeto = ?idobjeto?
                    AND NOT EXISTS(SELECT 1 FROM contapagaritem i 
                                    WHERE i.idcontapagar = c.idcontapagar 
                                        AND i.ajuste = 'N' 
                                        AND i.idobjetoorigem = ?idobjetoorigem?
                                        AND i.tipoobjetoorigem LIKE 'nf%')
                    AND (c.tipoobjeto LIKE ('?tipoobjetoorigem?%') OR c.tipoobjeto LIKE ('?tipoobjeto?'))
                ) as qry";
    }

    public static function buscarFaturasPorIdFormapagamento() {
        return "SELECT cp.idcontapagar, cp.idempresa, cp.valor
                FROM contapagar cp
                WHERE idformapagamento = ?idformapagamento?
                AND cp.idempresa = ?idempresa?
                AND cp.status = 'ABERTO'
                AND NOT EXISTS (
                    SELECT 1
                    FROM conciliacaofinanceira
                    WHERE idcontapagar = cp.idcontapagar
            )
            ?union?";
    }
}
