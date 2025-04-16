<?
require_once(__DIR__."/_iquery.php");

class SolmatQuery implements DefaultQuery
{
    public static $table = 'solmat';
    public static $pk = 'idsolmat';

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }  

    public static function buscarInfosParaCupomFiscal()
    {
		return "SELECT  si.idsolmat,
                        si.idsolmatitem,
                        si.descr,
                        IFNULL(fh.criadopor,s.criadopor) AS criadopor,
                        IFNULL(fh.criadoem,s.criadoem) AS criadoem,
                        s.tipo,
                        p.un,
                        si.obs,
                        t.descricao,
                        u.unidade AS destino,
                        lc.qtdd,
                        lf.qtd AS qtdrestante,
                        u1.unidade AS origem,
                        l.idlote,
                        CONCAT(l.partida,'/',l.exercicio) AS partida,
                        p.descr,
                        CONCAT(ta.descricao,' ',
                        CONCAT(CASE d.coluna 
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
                                END,' ',d.linha) )AS campo
                FROM solmatitem si 
                    JOIN solmat s ON(s.idsolmat = si.idsolmat)
                    JOIN unidade u ON(s.idunidade = u.idunidade)
                    JOIN unidade u1 ON(s.unidade = u1.idunidade)
                    JOIN lotecons lc ON(lc.tipoobjetoconsumoespec = 'solmatitem' AND lc.idobjetoconsumoespec = si.idsolmatitem and lc.status != 'INATIVO')
                    JOIN lote l ON(lc.idlote = l.idlote) 
                    JOIN lotefracao lf ON lf.idlote = l.idlote AND lf.idlotefracao = lc.idlotefracao AND l.idempresa = lf.idempresa
                    JOIN prodserv p ON(p.idprodserv = l.idprodserv) 
                    LEFT JOIN tag t ON (s.idtag = t.idtag)
                    LEFT JOIN lotelocalizacao ll ON( ll.idlote = l.idlote AND ll.idempresa = lf.idempresa)
                    LEFT JOIN tagdim d ON(ll.tipoobjeto = 'tagdim' AND d.idtagdim = ll.idobjeto)
                    LEFT JOIN tag ta ON (ta.idtag = d.idtag)
                    LEFT JOIN fluxostatushist fh ON (fh.idfluxostatus=?idsolicitado? AND fh.modulo = '?modulo?' AND fh.idmodulo = si.idsolmat AND fh.status='ATIVO')
                WHERE
                    si.idsolmat =?idsolmat?
                    AND qtdd > 0
                GROUP BY
                    l.idlote
                ORDER BY
                    ta.descricao asc,
                    d.coluna asc,
                    d.linha asc";
	}

    public static function inserirSolmat()
	{
		return "INSERT INTO solmat (idempresa, 
                                    status,
                                    idfluxostatus, 
                                    tipo, 
                                    idunidade, 
                                    unidade, 
                                    criadopor, 
                                    criadoem, 
                                    alteradopor, 
                                    alteradoem)
                                VALUES (?idempresa?, 
                                    '?status?', 
                                    ?idfluxostatus?, 
                                    '?tipo?',
                                    ?idunidade?, 
                                    '?unidade?', 
                                    '?usuario?',
                                    sysdate(),
                                    '?usuario?',
                                    sysdate())";
	}

    public static function buscarComentariosPorIdSolMat()
    {
        return "SELECT s.idsolmat, sc.descricao, sc.idmodulo, sc.criadoem,  IFNULL(p.nomecurto,sc.criadopor) AS criadopor
                FROM solmat s 
                JOIN modulocom sc ON(s.idsolmat = sc.idmodulo) 
                JOIN pessoa p ON(p.usuario = sc.criadopor)
                WHERE s.idsolmat = ?idsolmat? 
                ORDER BY sc.criadoem DESC";
    }

    public static function buscarRandomico()
    {
        return "SELECT FLOOR(RAND() * 1000000000) AS idtransacao";
    }
}