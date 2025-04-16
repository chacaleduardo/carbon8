<?
class ProdservfornQuery
{
	public static function buscarProdservfornPorIdprodservIdnf()
    {
        return "SELECT 
                        p.idprodservforn
                    FROM
                        nf n
                            JOIN
                        prodservforn p ON (p.idpessoa = n.idpessoa
                            AND p.idprodserv = ?idprodserv?)
                    WHERE
                        n.idnf = ?idnf?
                            AND p.status = 'ATIVO'
                    LIMIT 1";
    }

    public static function buscarProdservfornPorId()
    {
        return "SELECT 
                    *
                FROM
                prodservforn
                WHERE
                    idprodservforn = ?idprodservforn?";
    }

    public static function buscarQtdProdservFornPorIdprodserv()
    {
        return "SELECT 1
                  FROM prodservforn f LEFT JOIN pessoa p ON (p.idpessoa = f.idpessoa)
                 WHERE f.idprodserv = ?idprodserv? 
                   AND f.converteest = 'Y'
                   ?condicaoStatus?
              ORDER BY f.status, f.idprodservforn;";
    }

    public static function buscarProdservFornPorIdprodserv()
    {
        return "SELECT f.idprodservforn,
                       f.codforn,
                       f.unforn,                       
                       f.valconv,
                       f.converteest,
                       f.alteradopor,
                       f.alteradoem,
                       f.status,
                       f.cprodforn,
                       p.idpessoa,
                       IF(p.cpfcnpj != '', CONCAT(p.nome, ' - ', p.cpfcnpj), p.nome) AS nome,
                       em.sigla
                  FROM prodservforn f LEFT JOIN pessoa p ON (p.idpessoa = f.idpessoa)
             LEFT JOIN empresa em ON p.idempresa = em.idempresa
                 WHERE f.idprodserv = ?idprodserv?
                   AND f.multiempresa = 'N'
              ORDER BY f.status, f.idprodservforn;";
    }

    public static function buscarProdservFornProdservPorIdprodserv()
    {
        return "SELECT p.nome,
                       p.idempresa,
                       p.idpessoa,
                       CONCAT(e.sigla, '-', ps.descr) AS descr,
                       f.idprodservforn,
                       f.codforn,
                       f.unforn,
                       f.valconv,
                       f.converteest,
                       f.alteradopor,
                       f.alteradoem,
                       f.status
                  FROM prodservforn f LEFT JOIN pessoa p ON (p.idpessoa = f.idpessoa)
             LEFT JOIN prodserv ps ON (ps.idprodserv = f.idprodservori)
             LEFT JOIN empresa e ON (e.idempresa = ps.idempresa)
                 WHERE f.idprodserv = ?idprodserv?
                   AND f.multiempresa = 'Y'
              ORDER BY f.status, f.idprodservforn;";
    }

    public static function buscarIdProdservFornPorIdprodservIdForn()
    {
        return "SELECT idprodservforn FROM prodservforn WHERE idprodserv = ?idprodserv? AND idpessoa = ?idpessoa?";
    }

    public static function buscarIdProdservPorIdpessoaIdCodForn()
    {
        return "SELECT idprodserv FROM prodservforn WHERE cprodforn = '?cprodforn?' AND idpessoa = ?idpessoa? AND idempresa = ?idempresa?";
    }

    public static function buscarConversaoFornecedorPorCnpj()
    {
        return "SELECT 1
                  FROM prodservforn pf JOIN pessoa p ON p.idpessoa = pf.idpessoa 
                 WHERE SUBSTRING(cpfcnpj, 1, 10) = '?cnpf?' 
                   AND idprodserv = '?idprodserv?'
                   AND unforn = '?unforn?'
                   AND valconv IS NOT NULL";
    } 

}
?>