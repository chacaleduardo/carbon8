<?
class LoteLocalizacaoQuery
{
    public static function buscarLoteLocalizacaoEFracaoPorIdTagDimEIdUnidade()
    {
        return "SELECT 
                        f.idlotefracao, 
                        l.qtdprod, 
                        f.idlote,
                        f.qtd,
                        u.convestoque,
                        l.valconvori,
                        f.qtd_exp,
                        l.status,
                        l.partida,
                        l.exercicio, 
                        l.qtdprod_exp, 
                        l.valconvori, 
                        l.unlote, 
                        l.idprodserv, 
                        l.converteest, 
                        l.unpadrao, 
                        ps.descr as prodserv,
                        ll.idobjeto as idtagdim,
                        l.vunpadrao
                FROM  lotelocalizacao ll 
                JOIN lotefracao f ON(ll.idlote = f.idlote) 
                JOIN unidade u ON(u.idunidade = f.idunidade) 
                JOIN lote l ON(f.idlote = l.idlote) 
                JOIN prodserv ps on ps.idprodserv = l.idprodserv
                WHERE ll.idobjeto in(?idtagdim?)
                AND ll.tipoobjeto = 'tagdim' 
                AND f.idunidade = ?idunidade?
                AND f.qtd > 0
                AND ll.idobjeto <> ''
                ORDER BY ll.idobjeto ASC,f.idlote ASC";
    }

    public static function verificarSePossuiLoteFracaoPorIdTagDimEIdUnidade()
    {
        return "SELECT ll.idobjeto as idtagdim, count(ll.idobjeto) as qtd,tg.linha,tg.coluna
                FROM lotelocalizacao ll 
                JOIN lotefracao f ON(ll.idlote = f.idlote)
                JOIN tagdim tg ON (tg.idtagdim = ll.idobjeto)
                WHERE ll.idobjeto in (?idtagdim?)
                AND ll.tipoobjeto = 'tagdim' 
                AND f.idunidade = ?idunidade?
                AND f.qtd > 0
                AND ll.idobjeto <> ''
                GROUP BY tg.linha,tg.coluna;";
    }

    public static function verificarSePossuiLoteFracaoPorIdTagDimEIdEmpresaEIdUnidade()
    {
        return "SELECT ll.idobjeto as idtagdim, count(ll.idobjeto) as qtd
                FROM lotelocalizacao ll 
                JOIN lotefracao f ON(ll.idlote = f.idlote)
                WHERE ll.idobjeto in (?idtagdim?)
                AND ll.tipoobjeto = 'tagdim' 
                AND f.idempresa = ?idempresa?
                AND f.idunidade = ?idunidade?
                AND f.qtd > 0
                AND ll.idobjeto <> ''
                GROUP BY ll.idobjeto";
    }

    public static function buscarLocalizacaoLotePorIdLote()
    {
        return "SELECT tc.idlotelocalizacao,
                       t.idtag,
                       td.idtagdim,
                       CONCAT(t.descricao, ' ', CONCAT(CASE td.coluna
                                                        WHEN 0 THEN '0'
                                                        WHEN 1 THEN 'A'
                                                        WHEN 2 THEN 'B'
                                                        WHEN 3 THEN 'C'
                                                        WHEN 4 THEN 'D'
                                                        WHEN 5 THEN 'E'
                                                        WHEN 6 THEN 'F'
                                                        WHEN 7 THEN 'G'
                                                        WHEN 8 THEN 'H'
                                                        WHEN 9 THEN 'I'
                                                        WHEN 10 THEN 'J'
                                                        WHEN 11 THEN 'K'
                                                        WHEN 12 THEN 'L'
                                                        WHEN 13 THEN 'M'
                                                        WHEN 14 THEN 'N'
                                                        WHEN 15 THEN 'O'
                                                        WHEN 16 THEN 'P'
                                                        WHEN 17 THEN 'Q'
                                                        WHEN 18 THEN 'R'
                                                        WHEN 19 THEN 'S'
                                                        WHEN 20 THEN 'T'
                                                        WHEN 21 THEN 'U'
                                                        WHEN 22 THEN 'V'
                                                        WHEN 23 THEN 'X'
                                                        WHEN 24 THEN 'Z'
                                                    END, ' ', td.linha)) AS campo
                   FROM lotelocalizacao tc JOIN tagdim td ON td.idtagdim = tc.idobjeto
                   JOIN tag t ON t.idtag = td.idtag
                  WHERE tc.idlote = ?idlote?
                    AND tc.tipoobjeto = '?tipoobjeto?'
               ORDER BY campo;";
    }

    public static function transferirLote()
    {
        return "UPDATE lotelocalizacao llu
                SET llu.idobjeto = ?idtagdimdestino?
                WHERE EXISTS(
                    SELECT 1
                    FROM (
                        SELECT 1
                        FROM lotelocalizacao ll 
                        JOIN lotefracao f ON(ll.idlote = f.idlote)
                        WHERE ll.idobjeto in (?idtagdimorigem?)
                        AND ll.tipoobjeto = 'tagdim' 
                        AND f.idempresa = ?idempresa?
                        AND f.idunidade = ?idunidade?
                        AND f.qtd > 0
                        AND ll.idobjeto <> ''
                        AND llu.idlotelocalizacao = ll.idlotelocalizacao
                    ) qry
                )";
    }

    public static function vincularPosicaoPrateleira() {
        return "INSERT INTO lotelocalizacao (idempresa, idlote, idobjeto, tipoobjeto)
                VALUES (?idempresa?, ?idlote?, ?idobjeto?, '?tipoobjeto?')";
    }
}
?>