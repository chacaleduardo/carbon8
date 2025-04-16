<?
class PlanejamentoprodservQuery
{


    public static function buscarExercicioPorId()
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
                            planejamentoprodserv p
                        WHERE
                            p.exercicio = u.exercicio
                                AND p.idprodserv = ?idprodserv?
                                ?formula?
                                AND p.idunidade = ?idunidade?) ORDER BY exercicio";
    }

    public static function buscarPlanejamentoprodservExercicio()
    {
        return "SELECT 
                    exercicio
                FROM
                    planejamentoprodserv 
                WHERE
                    idprodserv = ?idprodserv? ?formula?
                GROUP BY exercicio";
    }

    public static function verificaProdservComFormulaEPlanejamentoSem()
    {
        return "SELECT 
                    1
                FROM
                    planejamentoprodserv pp
                WHERE
                    pp.idprodserv = ?idprodserv? AND pp.idprodservformula is null
                    AND exists (select 1 from prodservformula pf where pf.idprodserv = pp.idprodserv AND pf.status = 'ATIVO')
                GROUP BY exercicio";
    }

    public static function buscarPlanejamentoprodservMes()
    {
        return "SELECT 
                    *
                FROM
                    planejamentoprodserv p
                WHERE
                    p.idprodserv = ?idprodserv? AND exercicio = ?exercicio? ?formula?                  
                ORDER BY mes";
    }

    public static function buscarPlanejamentoProdservAdicional()
    {
        return "SELECT 
                        pp.idplanejamentoprodserv
                    FROM
                        planejamentoprodserv p
                            JOIN
                        planejamentoprodserv pp ON (pp.idprodserv = p.idprodserv
                            AND pp.idunidade = p.idunidade
                            AND pp.exercicio = p.exercicio
                            AND pp.idplanejamentoprodserv != p.idplanejamentoprodserv)
                    WHERE
                        p.idplanejamentoprodserv = ?idplanejamentoprodserv?";
    }

    public static function buscarPlanejamentoPorIdProdservMesExercioUnidade()
    {
        return "SELECT pp.planejado, pp.adicional, pp.idprodserv
                  FROM planejamentoprodserv pp JOIN solmatitem si ON si.idprodserv = pp.idprodserv
                WHERE pp.idunidade = ?idunidade? 
                  AND pp.exercicio = ?exercicio?
                  AND pp.mes = ?mes?
                  ?andIdsolmat?
                  ?andIdprodserv?
             GROUP BY pp.idprodserv";
    }

    public static function buscarPlanejamentoPorIdProdservMesExercio()
    {
        return "SELECT pp.planejado,
                    CASE
                        WHEN pp.adicional IS NULL THEN 0
                        WHEN pp.adicional = '' THEN 0
                        ELSE pp.adicional
                    END AS adicional,
                    ((pp.planejado) * ((CASE
                        WHEN pp.adicional IS NULL THEN 0
                        WHEN pp.adicional = '' THEN 0
                        ELSE pp.adicional
                    END) / 100)) + (pp.planejado) AS valor,
                    u.unidade
                FROM planejamentoprodserv pp join unidade u on(u.idunidade=pp.idunidade)
                WHERE pp.idprodserv=?idprodserv?
                AND pp.exercicio = ?exercicio?
                AND pp.mes =  ?mes?";
    }
    public static function buscaCategoria()
    {
        return "SELECT g.grupo, ti.contatipo, c.idcontaitem, c.contaitem, t.idtipoprodserv, t.tipoprodserv, 
                    pc.idplanejamentocompra, pc.idempresa, pc.exercicio, 
                    (pc.jan) jan,
                    (pc.fev) fev,
                    (pc.mar) mar,
                    (pc.abr) abr,
                    (pc.mai) mai,
                    (pc.jun) jun,
                    (pc.jul) jul,
                    (pc.ago) ago,
                    (pc.set) `set`,
                    (pc.out) `out`,
                    (pc.nov) nov,
                    (pc.dez) dez, pc.altera, pc.ajuste, pc.status, pc.idfluxostatus, pc.criadoem, pc.alteradoem, pc.criadopor, pc.alteradopor, count(t.idtipoprodserv)
                FROM contaitem c
                join contatipo ti on (c.idcontatipo = ti.idcontatipo)
                join contatipogrupo g on ti.idcontatipogrupo = g.idcontatipogrupo 
                JOIN contaitemtipoprodserv ct ON ct.idcontaitem = c.idcontaitem 
                JOIN tipoprodserv t ON t.idtipoprodserv = ct.idtipoprodserv
                LEFT JOIN planejamentocompra pc ON  t.idtipoprodserv = pc.idtipoprodserv and pc.exercicio = ?exercicio?
                WHERE t.idempresa = ?idempresa?                    
                    and t.status = 'ATIVO'
                    AND g.classificacao = 'RECEITA'
                    and not exists(select 1 from  prodserv ps where ps.idtipoprodserv = t.idtipoprodserv and  ps.status = 'ATIVO'  AND ps.venda = 'Y' AND ps.fabricado='Y')
                    ?categoria?
                GROUP BY c.idcontaitem, t.idtipoprodserv
                ORDER BY g.ordem, g.grupo, ti.ordem, ti.contatipo, c.ordem;";
    }
    public static function buscaHistorico()
    {
        return "select * from objetojson where idobjeto = ?idforecastcompra? and tipoobjeto='forecastcompra' and versaoobjeto= ?versao?;";
    }
    public static function buscaInsumosCategoria()
    {
        return "SELECT p.idprodserv, p.descr, p.un
                FROM
                    prodservcontaitem i
                    join contaitem c on c.idcontaitem = i.idcontaitem
                    join prodserv p on p.idprodserv = i.idprodserv
                where c.idcontaitem = ?categoria? and p.insumo = 'Y' ;";
    }
    public static function buscaValorPorIdtipo()
    {
        return "SELECT t.idtipoprodserv, t.tipoprodserv, pl.exercicio, pl.mes, SUM(pi.valortotal) as total
                FROM
                    planejamentoprodserv pl
                    LEFT JOIN prodservformula pf ON pf.idprodserv = pl.idprodserv
                    LEFT JOIN prodservformulaitem pi ON pi.idprodservformula = pf.idprodservformula
                    LEFT JOIN prodserv pis ON pis.idprodserv = pi.idprodserv
                    LEFT JOIN prodservcontaitem pc ON pc.idprodserv = pis.idprodserv
                    LEFT JOIN contaitem c ON c.idcontaitem = pc.idcontaitem
                    LEFT JOIN tipoprodserv t ON t.idtipoprodserv = pis.idtipoprodserv
                WHERE
                    pl.idempresa = ?idempresa?
                    AND pl.exercicio = ?exercicio?
                    AND t.idtipoprodserv is not null
                    AND t.idtipoprodserv = ?idtipoprodserv?
                GROUP BY
                    t.idtipoprodserv,
                    pl.exercicio,
                    pl.mes
                ORDER BY
                    t.idtipoprodserv,
                    pl.exercicio,
                    pl.mes;";
    }
    public static function buscaPrevisaoPorTipo()
    {
        return "SELECT *
                FROM
                    planejamentocompra 
                WHERE
                    idempresa = ?idempresa?
                    AND exercicio = ?exercicio?
                    AND idtipoprodserv = ?idtipoprodserv? ";
    }
    public static function listaIdtipoprodservPorContaitem()
    {
        return "SELECT t.idtipoprodserv, t.tipoprodserv
                FROM
                    contaitem c
                    JOIN contaitemtipoprodserv ct ON (
                        ct.idcontaitem = c.idcontaitem
                    )
                    JOIN tipoprodserv t on (
                        t.idtipoprodserv = ct.idtipoprodserv
                    )
                WHERE
                    c.idempresa = 2
                    AND c.idcontaitem = ?idcontaitem?;";
    }
}
