<?
class LoteFracaoMovQuery
{
    public static function reterLote() {
        return "INSERT INTO lotefracaomov (idempresa, idtagdim, qtd, status, idlotefracao, criadopor, criadoem, alteradopor, alteradoem)
                VALUES(?idempresa?, ?idtagdim?, ?qtd?, '?status?', ?idlotefracao?, '?usuario?', NOW(), '?usuario?', NOW())";
    }

    public static function buscarLoteMovPorIdLoteFracaoEIdTagDim() {
        return "SELECT idlotefracaomov
                from lotefracaomov l 
                where idlotefracao = ?idlotefracao?
                and idtagdim = ?idtagdim?";
    }

    public static function buscarLoteFracaoMovPorIdLoteFracaoMov() {
        return "SELECT *
                from lotefracaomov
                where idlotefracaomov = ?idlotefracaomov?";
    }

    public static function buscarLoteFracaoMovPorIdLoteFracao() {
        return "SELECT 
                    lfm.idempresa, 
                    lfm.status, 
                    lfm.qtd,
                    lfm.criadopor, 
                    lfm.criadoem, 
                    td.coluna, 
                    td.linha, 
                    td.caixa, 
                    CONCAT(e.sigla, '-', t.descricao) as descricao, 
                    IF(u.idtipounidade = 11, 'Y', 'N') as retem
                FROM lotefracaomov lfm
                JOIN lotefracao lf ON lf.idlotefracao = lfm.idlotefracao 
                JOIN unidade u ON u.idunidade = lf.idunidade 
                JOIN tagdim td on td.idtagdim = lfm.idtagdim
                JOIN tag t ON t.idtag = td.idtag
                JOIN empresa e ON e.idempresa = t.idempresa
                WHERE lfm.idlotefracao in(?idlotefracao?)
                AND lfm.idempresa = ?idempresa?";
    }

    public static function buscarLoteFracaoMovPorIdTagDim() {
        return "SELECT 
                    lfm.qtd,
                    CONCAT(l.partida, '/', l.exercicio) as partida,
                    pl.plantel
                from lotefracaomov lfm
                JOIN lotefracao lf ON lf.idlotefracao = lfm.idlotefracao 
                JOIN lote l ON l.idlote = lf.idlote
                JOIN prodserv p on p.idprodserv = l.idprodserv 
                JOIN prodservformula f on f.idprodserv = p.idprodserv
                JOIN plantel pl on pl.idplantel = f.idplantel
                WHERE lfm.idtagdim = ?idtagdim?
                GROUP BY lfm.idlotefracaomov";
    }

    public static function buscarTagDimEPosicoesPorIDtag() {
        return "SELECT td.idtagdim, td.coluna, td.linha, td.caixa, count(lfm.idlotefracao) as qtd
                FROM tagdim td
                LEFT JOIN tag t ON t.idtag = td.idtag
                LEFT JOIN lotefracaomov lfm ON lfm.idtagdim = td.idtagdim 
                WHERE td.idtag = ?idtag?
                GROUP by td.idtagdim; ";
    }

    public static function retirarProduto() {
        return "UPDATE lotefracaomov
                SET qtd = qtd - ?qtdretirada?
                WHERE idlotefracaomov = ?idlotefracaomov?";
    }

    public static function alocarProdutos() {
        return "UPDATE lotefracaomov
                SET qtd = qtd + ?qtdalocada?
                WHERE idtagdim = ?idtagdim?
                AND idlotefracao = ?idlotefracao?";
    }

    public static function atualizarQuantidadeLoteFracaoMov() {
        return "UPDATE lotefracaomov
                SET qtd = ?qtd?
                WHERE idlotefracaomov = ?idlotefracaomov?";
    }
}