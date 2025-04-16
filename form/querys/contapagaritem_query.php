<?
require_once(__DIR__."/_iquery.php");

class ContaPagarItemQuery implements DefaultQuery
{
    public static $table = "contapagaritem";
    public static $pk = "contapagaritem";

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ['table' => self::$table, 'pk' => self::$pk]);
    }

    public static function buscarParcelaPorNf()
    {
        return "SELECT i.parcela, DATE_ADD(i.datapagto, INTERVAL 1 DAY) AS vdatapagto, nc.proporcao, i.* 
            FROM contapagaritem i 
                LEFT JOIN nfconfpagar nc ON (nc.idnf=i.idobjetoorigem AND nc.datareceb=i.datapagto)
            WHERE i.idobjetoorigem = ?idnf?
                AND i.tipoobjetoorigem = '?tipo?' 
                AND i.status !='INATIVO'";
    }

    public static function buscarContaPagarItem()
    {
        return "SELECT idcontapagar,
                       parcela,
                       parcelas,
                       tipo,
                       status,
                       DMA(datapagto) AS datapagto,
                       DMA(datapagto) AS datareceb,
                       valor,
                       obs
                  FROM contapagaritem
                 WHERE idcontapagaritem = ?idcontapagaritem?";
    }

    public static function inserirParcelaAbertaComissao()
    {
        return "INSERT INTO contapagaritem (
            idempresa,
            status,
            idpessoa,
            idobjetoorigem,
            tipoobjetoorigem,
            idformapagamento,
            tipo,
            parcela,
            parcelas,
            datapagto,
            valor,
            visivel,
            criadopor,
            criadoem,
            alteradopor,
            alteradoem
        ) VALUES (
            ?idempresa?, 
            '?status?', 
            ?idpessoa?, 
            ?idobjetoorigem?, 
            '?tipoobjetoorigem?', 
            ?idformapagamento?, 
            '?tipo?', 
            '?parcela?', 
            '?parcelas?', 
            ?datapagto?, 
            ?valor?, 
            '?visivel?', 
            '?usuario?', 
            sysdate(), 
            '?usuario?', 
            sysdate()
        )";
    }

    public static function inserirValoresContaPagarItem()
    {
        return "INSERT INTO contapagaritem (idempresa,
                                            status,
                                            idpessoa,
                                            idcontaitem,
                                            idobjetoorigem,
                                            tipoobjetoorigem,
                                            tipo,
                                            visivel,
                                            idformapagamento,
                                            parcela,
                                            parcelas,
                                            datapagto,
                                            valor,
                                            criadopor,
                                            criadoem,
                                            alteradopor,
                                            alteradoem)
                                    VALUES (?idempresa?,
                                            '?status?',
                                            ?idpessoa?,
                                            ?idcontaitem?,
                                            ?idobjetoorigem?,
                                            '?tipoobjetoorigem?',
                                            '?tipo?',
                                            '?visivel?',
                                            ?idformapagamento?,
                                            '?parcela?',
                                            '?parcelas?',
                                            ?datapagto?,
                                            '?valor?',
                                            '?usuario?',
                                            now(),
                                            '?usuario?',
                                            now())";
    }


    public static function verificarSeExisteParcelaQuitada()
    {
        return "SELECT 
                    COUNT(*) AS quant
                FROM
                    contapagaritem
                WHERE
                    idobjetoorigem = ?idnf?
                        AND tipoobjetoorigem = 'nf'
                        AND status IN ('QUITADO')";
    }

    public static function verificarSeExisteParcelaInStatusPorIdobjeto()
    {
        return "SELECT 
                    COUNT(*) AS quant
                FROM
                    contapagaritem i
                        JOIN
                    contapagar c ON (c.idcontapagar = i.idcontapagar
                        AND c.status IN ?instatus? )
                WHERE
                    i.idobjetoorigem = ?idobjetoorigem?
                        AND i.tipoobjetoorigem = '?tipoobjetoorigem?'";
    }

    public static function deletaParcelaComissaoPendentePorIdobjeto()
    {
        return "DELETE cc . * FROM contapagar c,
                    contapagaritem cc, contapagar co 
                WHERE
                    c.tipoobjeto = '?tipoobjeto?'                   
                    AND c.idobjeto = ?idobjeto?                                      
                    AND cc.idobjetoorigem = c.idcontapagar
                    AND cc.tipoobjetoorigem = 'contapagar'
                    AND cc.status IN ('INICIO' , 'ABERTO', 'INATIVO')
                    AND co.idcontapagar=cc.idcontapagar
                    AND co.tipoespecifico='REPRESENTACAO'";
    }

    public static function deletaParcelaImpostoPendentePorIdobjeto()
    {
        return "DELETE cc . * FROM contapagar c,
                        contapagaritem cc, contapagar co 
                    WHERE
                        c.tipoobjeto = '?tipoobjeto?'                   
                        AND c.idobjeto = ?idobjeto?                                      
                        AND cc.idobjetoorigem = c.idcontapagar
                        AND cc.tipoobjetoorigem = 'contapagar'
                        AND cc.status IN ('INICIO' , 'ABERTO', 'INATIVO', 'PENDENTE')
                        AND co.idcontapagar=cc.idcontapagar
                        AND co.tipoespecifico='IMPOSTO' 
                        AND co.status !='PENDENTE' ";
    }


    public static function deletaParcelaPendentePorIdobjeto()
    {
        return "DELETE i . * FROM contapagaritem i
                        JOIN
                    contapagar c ON (c.idcontapagar = i.idcontapagar) 
                WHERE
                    i.tipoobjetoorigem = '?tipoobjetoorigem?'
                    AND i.idobjetoorigem = ?idobjetoorigem?
                    AND i.status !='QUITADO'";
    }


    public static function buscarContaPagarItemAbertas()
    {
        return "SELECT i.idcontapagaritem,
                        i.idpessoa,
                        i.idformapagamento,
                        i.idagencia,
                        i.idcontaitem,
                        MONTH(LAST_DAY(i.datapagto) + INTERVAL 1 DAY) AS mes,
                        YEAR(LAST_DAY(i.datapagto) + INTERVAL 1 DAY) AS ano,
                        (LAST_DAY(i.datapagto) + INTERVAL IFNULL(f.diavenc, 1) DAY) AS datavencimento,
                        i.datapagto,
                        f.agruppessoa,
                        f.agrupfpagamento,
                        f.agrupnota,
                        i.idobjetoorigem,
                        i.tipoobjetoorigem,
                        i.valor,
                        i.parcela,
                        i.parcelas,
                        i.tipo,
                        i.visivel,
                        IFNULL(fp.previsao, f.previsao) AS previsao,
                        i.status,
                        i.obs,
                        i.criadopor,
                        f.formapagamento,
                        f.tipoespecifico
                    FROM contapagaritem i JOIN formapagamento f ON (i.idformapagamento = f.idformapagamento)
                    LEFT JOIN formapagamentopessoa fp ON (fp.idformapagamento = f.idformapagamento AND f.agruppessoa = 'Y' AND fp.idpessoa = i.idpessoa)
                    JOIN pessoa p ON p.idpessoa = i.idpessoa
                    WHERE i.status IN ('ABERTO', 'PENDENTE', 'PAGAR')
                    AND (idcontapagar IS NULL OR idcontapagar = '')
                    AND (i.ajuste != 'Y' OR i.ajuste IS NULL)
                    AND i.idpessoa IS NOT NULL
                    AND i.idpessoa != ''
                    AND i.idformapagamento IS NOT NULL
                    AND i.idformapagamento != ''
                    AND i.idempresa = ?idempresa?
                    AND i.datapagto != '0000-00-00'
                    AND i.idagencia IS NOT NULL
                    AND i.idagencia != ''
                    GROUP BY i.idcontapagaritem";
    }

    public static function atualizarIdContaPagarPorIdContaPagarItem()
    {
        return "UPDATE contapagaritem SET idcontapagar = ?idcontapagar? WHERE idcontapagaritem = ?idcontapagaritem? ?AndIdempresa?";
    }

    public static function buscarValorTotalContaPagarItem()
    {
        return "SELECT SUM(i.valor) AS valor FROM contapagaritem WHERE idcontapagar = ?idcontapagar? AND status != 'INATIVO'";
    }

    public static function buscarIdContaPagarItem()
    {
        return "SELECT idcontapagaritem, idcontapagar, datapagto, valor
                  FROM contapagaritem 
                 WHERE idobjetoorigem = ?idobjetoorigem? 
                   AND tipoobjetoorigem = '?tipoobjetoorigem?' 
                   ?andParcela?
                   ?andIdcontapagar?
                   AND status != 'INATIVO'
                ORDER BY idcontapagaritem DESC";
    }

    public static function atualizarContaPagarPorIdContaPagarItem()
    {
        return "UPDATE contapagaritem SET parcelas = ?parcelas?, valor = '?valor?', datapagto = '?datapagto?', alteradopor = '?alteradopor?', alteradoem = now() WHERE idcontapagaritem = ?idcontapagaritem? AND status != 'QUITADO'";
    }

    public static function atualizarStatusContaPagarItem()
    {
        return "UPDATE contapagaritem SET status = ?status?, alteradopor = '?alteradopor?', alteradoem = now() WHERE idcontapagaritem = ?idcontapagaritem? AND status != 'QUITADO'";
    }

    public static function atualizarFormaPagamentoContaPagarItem()
    {
        return "UPDATE contapagaritem SET idformapagamento = ?idformapagamento?, alteradopor = '?alteradopor?', alteradoem = now() WHERE idcontapagaritem = ?idcontapagaritem?";
    }

    public static function atualizarFormaPagamentoAgrupadoContaPagarItem()
    {
        return "UPDATE contapagaritem SET idformapagamento = ?idformapagamento?, idcontapagar = NULL, alteradopor = '?alteradopor?', alteradoem = now() WHERE idcontapagaritem = ?idcontapagaritem?";
    }

    public static function apagarContaPagarItemAPartirdaParcela()
    {
        return "DELETE FROM contapagaritem WHERE tipoobjetoorigem = '?tipoobjetoorigem?' AND idobjetoorigem = '?idobjetoorigem?' AND parcela > ?parcela? AND status != 'QUITADO'";
    }


    public static function AtualizaParcelaPendentePorIdobjeto()
    {
        return "update contapagar i                     
                    set i.status='?status?'
                WHERE
                    i.tipoobjeto = '?tipoobjetoorigem?'
                    AND i.idobjeto = ?idobjetoorigem?
                    AND i.status !='QUITADO'";
    }
}
