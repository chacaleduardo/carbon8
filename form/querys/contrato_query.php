<?
class ContratoQuery{

    public static function listarContratoPorPessoa(){
        return "SELECT 
                c.idcontrato,
                c.titulo,
                DMA(c.vigencia) AS vigencia,
                DMA(c.vigenciafim) AS vigenciafim
            FROM
                contratopessoa cp
                    JOIN
                contrato c ON (c.idcontrato = cp.idcontrato
                    AND c.status = 'ATIVO'
                    AND c.tipo = 'P')
            WHERE
                cp.idpessoa =  ?idpessoa? ";

    }

    public static function buscarValorContatoProdutoFomulado(){

        return "SELECT 
                        IFNULL(cf.valor, '0.00') AS valor
                    FROM
                        prodserv p
                            JOIN
                        desconto d ON (d.idtipoteste = p.idprodserv)
                            JOIN
                        contratopessoa c ON (c.idpessoa = ?idpessoa?
                            AND d.idcontrato = c.idcontrato)
                            JOIN
                        contrato ct ON (ct.idcontrato = c.idcontrato
                            AND ct.status = 'ATIVO')
                            JOIN
                        contratoprodservformula cf ON (d.iddesconto = cf.iddesconto
                            AND cf.idprodservformula = ?idprodservformula?)
                            JOIN
                        prodservformula f ON (f.idprodservformula = cf.idprodservformula)
                    WHERE
                        p.idprodserv = ?idprodserv?";

    }

    public static function buscarComissaoContatoProduto(){

        return "SELECT 
                    c.idcontrato, o.idpessoa, o.comissao
                FROM
                    contratopessoa p
                        JOIN
                    contrato c ON (c.idcontrato = p.idcontrato
                        AND c.tipo = 'P')
                        JOIN
                    desconto d ON (d.idcontrato = c.idcontrato)
                        JOIN
                    contratocomissao o ON (o.iddesconto = d.iddesconto)
                WHERE
                    p.idpessoa = ?idpessoa?
                        AND d.idtipoteste = ?idprodserv?";

    }


    public static function buscarDescontoContratoPorProduto(){
         return "SELECT 
                    IFNULL(d.valor, '0.00') AS valor
                FROM
                    desconto d
                        JOIN
                    contratopessoa c ON (c.idpessoa = ?idpessoa?
                        AND d.idcontrato = c.idcontrato)
                        JOIN
                    contrato ct ON (ct.idcontrato = c.idcontrato
                        AND ct.status = 'ATIVO')
                WHERE
                    d.idtipoteste = ?idprodserv?";
    }
}
?>