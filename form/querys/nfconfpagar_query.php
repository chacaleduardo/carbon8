<?
class NfConfPagar
{
    public static function buscarIdNfConfPagar()
    {
        return "SELECT DMA(n.datareceb) AS dmadatareceb, n.*
                  FROM nfconfpagar n
                 WHERE n.idnf = ?idnf?
              ORDER BY n.datareceb";
    }

    public static function atualizarProporcaoNfConfPagar()
    {
        return "UPDATE nfconfpagar SET proporcao = null WHERE idnf = ?idnf?";
    }

	public static function inserirIdNfContaPagar()
    {
         return "INSERT INTO nfconfpagar (idnf, idempresa, parcela, criadopor, criadoem, alteradopor, alteradoem)
                 VALUES (?idnf?, ?idempresa?, '?parcela?', '?usuario?', now(), '?usuario?', now())";
    }

    public static function inserirIdNfContaPagarDataReceb()
    {
         return "INSERT INTO nfconfpagar (idnf, idempresa, datareceb, parcela, criadopor, criadoem, alteradopor, alteradoem)
                 VALUES (?idnf?, ?idempresa?, '?datareceb?', '?parcela?', '?usuario?', now(), '?usuario?', now())";
    }

    public static function inserirIdNfContaPagarDataRecebProporcao()
    {
         return "INSERT INTO nfconfpagar (idnf, idempresa, datareceb, parcela, proporcao, valorparcela, criadopor, criadoem, alteradopor, alteradoem)
                 VALUES (?idnf?, ?idempresa?, ?datareceb?, '?parcela?', '?proporcao?', '?valorparcela?', '?usuario?', now(), '?usuario?', now())";
    }

    public static function apagarNfConfPagar()
    {
         return "DELETE FROM nfconfpagar WHERE idnfconfpagar = ?idnfconfpagar?";
    }

    public static function apagarNfConfPagarPorIdnf()
    {
         return "DELETE FROM nfconfpagar WHERE idnf = ?idnf?";
    }
    
    public static function atualizarDatarecebNfConfPagar()
    {
        return "UPDATE nfconfpagar SET datareceb = null WHERE idnf = ?idnf?";
    }

    public static function buscarNfconfpagarPorIdnf()
    {
       return "SELECT 
                   n.proporcional, c.*
               FROM
                   nfconfpagar c
                       JOIN
                   nf n ON (n.idnf = c.idnf)
               WHERE
                   c.idnf = ?idnf?
                   AND datareceb IS NOT NULL and datareceb !='0000-00-00'                    
                       order by c.datareceb";
    }

    public static function buscarNfconfpagarOrdenadoPorOrdemDescrescente()
    {
       return "SELECT * FROM nfconfpagar c WHERE c.idnf = ?idnf? ORDER BY idnfconfpagar DESC";
    }

    public static function buscarNfconfpagar()
    {
       return "SELECT * FROM nfconfpagar c WHERE c.idnf = ?idnf?";
    }
    
    public static function somarProporcaoNfconfpagarPorIdnf()
    {
        return "SELECT 
                    n.proporcional, SUM(c.proporcao) AS proporcao
                FROM
                    nfconfpagar c
                        JOIN
                    nf n ON (n.idnf = c.idnf)
                WHERE
                    c.idnf = ?idnf?";
    }

    public static function buscarIdParcelaNfConfPagarPorIdNf()
    {
        return "SELECT idnfconfpagar, parcela FROM nfconfpagar WHERE idnf = ?idnf? AND (parcela = ?parcela? OR parcela IS NULL) ORDER BY idnfconfpagar ASC LIMIT 1;";
    }

    public static function inserirIdNfContaPagarDataRecebFormaPagamento()
    {
         return "INSERT INTO nfconfpagar (idnf, idformapagamento, idempresa, proporcao, parcela, datareceb, criadopor, criadoem, alteradopor, alteradoem)
                 VALUES (?idnf?, ?idformapagamento?, '?idempresa?', '?proporcao?', '?parcela?', '?datareceb?', '?usuario?', now(), '?usuario?', now())";
    }
}
?>