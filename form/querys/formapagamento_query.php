<?
require_once(__DIR__ . '/_iquery.php');

class FormaPagamentoQuery implements DefaultQuery
{
    public static $table = 'formapagamento';
    public static $pk = 'idformapagamento';

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function listarFormaPagamentoAtivo()
    {
        return "SELECT idformapagamento,
                       descricao 
                  FROM formapagamento 
                 WHERE status = 'ATIVO' AND debito = 'Y' 
                 ?idempresa?
                 ?orderBy?";
    }

    public static function listarFormaPagamentoAtivoPorLp()
    {
        return "SELECT idformapagamento,
                       descricao 
                  FROM formapagamento f
                 WHERE f.status = 'ATIVO' AND f.debito = 'Y' 
                    AND EXISTS (SELECT 1 from objetovinculo ov WHERE ov.idobjeto in (?idobjetovinc?) AND ov.tipoobjeto = '_lp' and ov.tipoobjetovinc = '?tipoobjetovinc?' and ov.idobjetovinc = f.idformapagamento )
                    ?idempresa?
                    ?restricaoEmpresa?
                    ?orderBy?";
    }

    public static function listarFormaPagamentoAtivoDistinct()
    {
        return "SELECT DISTINCT(f.formapagamento) AS id, 
                       formapagamento
                  FROM formapagamento f
                 WHERE f.status = 'ATIVO' 
                 ?idempresa?
              ORDER BY f.formapagamento";
    }

    public static function buscarConfiguracoesFormaPagamento()
    {
        return "SELECT idagencia, 
                       agruppessoa, 
                       agrupado, 
                       idempresa,
                       IFNULL(diasentrada, '') AS campo,
                       agrupnota,
                       agrupfpagamento
                  FROM formapagamento
                 WHERE idformapagamento = ?idformapagamento?";
    }


    public static function buscarFormapagamentoAgenciaPorFormapagamento()
    {
        return "SELECT 
                    f.formapagamento, a.*
                FROM
                    formapagamento f,
                    agencia a
                WHERE
                    a.idagencia = f.idagencia
                        AND f.idformapagamento =  ?idformapagamento?";
    }

    public static function buscarFormapagamentoCreditoPornota()
    {
        return "SELECT 
                        idformapagamento, descricao
                    FROM
                        formapagamento
                    WHERE
                        status = 'ATIVO' AND credito = 'Y'
                            AND agrupnota = 'Y'
                            ?idempresa?
                    ORDER BY ord , descricao DESC";
    }

    public static function buscarFormapagamentoAgrupadoPorEmpresa()
    {
        return "SELECT 
                    c.idformapagamento, c.descricao
                FROM
                    formapagamento c
                WHERE
                    c.status = 'ATIVO'
                        AND c.agruppessoa = 'N'
                        AND c.agrupado = 'Y'
                        AND c.idempresa = ?idempresa?
                ORDER BY c.ord , c.descricao";
    }

    public static function buscarFormapagamentoAgrupadoPorEmpresaPorLP()
    {
        return "SELECT 
                    c.idformapagamento, c.descricao
                FROM
                    formapagamento c
                WHERE
                    c.status = 'ATIVO'
                        AND c.agruppessoa = 'N'
                        AND c.agrupado = 'Y'
                        AND c.idempresa = ?idempresa?
                        AND EXISTS (SELECT 1 from objetovinculo ov WHERE ov.idobjeto in (?idobjetovinc?) AND ov.tipoobjeto = '_lp' and ov.tipoobjetovinc = '?tipoobjetovinc?' and ov.idobjetovinc = c.idformapagamento )
                ORDER BY c.ord , c.descricao";
    }

    public static function buscarInfFormapagamentoPorId()
    {
        return "SELECT 
                        *
                    FROM
                        formapagamento
                    WHERE
                        idformapagamento = ?idformapagamento?";
    }

    public static function buscarInfFormapagamentoPorIdObjetoOrigemEIdObjeto()
    {
        return "SELECT descricao, 
                       idcontapagar, 
                       valor, 
                       a.nome
                  FROM (SELECT f.agrupado,
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
                          FROM contapagaritem i JOIN contapagar cp ON (cp.idcontapagar = i.idcontapagar)
                          JOIN formapagamento f ON (f.idformapagamento = i.idformapagamento)
                         WHERE i.idobjetoorigem = ?idobjetoorigem?
                           AND i.tipoobjetoorigem LIKE 'tipoobjetoorigem%' 
                    UNION 
                        SELECT f.agrupado,
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
                                             AND i.idobjetoorigem = ?idobjetoorigem?
                                             AND i.tipoobjetoorigem LIKE 'tipoobjetoorigem%')
                           AND (c.tipoobjeto LIKE ('tipoobjeto%') OR c.tipoobjeto LIKE ('gnre')) ORDER BY parcela) AS u
                  JOIN arquivo a ON (u.idcontapagar = a.idobjeto AND a.tipoobjeto = 'contapagar' AND tipoarquivo = 'ANEXO')";
    }

    public static function buscarFormaPagamentoPorStatusEIdEmpresa()
    {
        return "SELECT idformapagamento,
                       CONCAT(IF(debito = 'Y' AND credito != 'Y', '(D) ', IF(credito = 'Y' AND debito != 'Y', '(C) ', IF(debito = 'Y' AND credito = 'Y', '(C/D) ', ''))), descricao) AS descricao
                  FROM formapagamento 
                 WHERE status = '?status?'
                       ?getidempresa?
              ORDER BY ord, descricao ASC";
    }

    public static function buscarFormaPagamentoPorIdPessoa()
    {
        return "SELECT idformapagamento, idunidade, idagencia
                  FROM formapagamento 
                 WHERE status = 'ATIVO'
                   AND idpessoa IN(?idpessoa?)
                   AND idempresa = ?idempresa?";
    }

    public static function listarFormaPagamentoPorEmpresa()
    {
        return "SELECT idformapagamento,descricao, formapagamento
                        FROM formapagamento 
                        WHERE status = 'ATIVO' 
                        ?idempresa?
                        ORDER BY descricao";
    }

    public static function buscarFormaPagamentoPorParcela()
    {
        return "SELECT 
                    f.agrupnota,
                    f.agrupado,
                    i.idcontapagar,
                    f.formapagamento,
                    f.agruppessoa
                FROM
                    contapagaritem i
                        JOIN
                    formapagamento f ON (f.idformapagamento = i.idformapagamento)
                WHERE
                    i.idcontapagaritem = ?idcontapagaritem?";
    }

    public static function buscarFormaPagamentoAtivaPorIdEmpresaEFormaPagamento()
    {
        return "SELECT p.idformapagamento, p.descricao
                FROM formapagamento p
                where p.status= 'ATIVO'   
                and p.idempresa = ?idempresa?
                and formapagamento = '?formapagamento?'";
    }
}
