<?
require_once(__DIR__ . "/_iquery.php");

class CarimboQuery implements DefaultQuery {

    public static $table = "carrimbo";
	public static $pk = "idcarrimbo";

	
	public static function buscarPorChavePrimaria()
	{
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
	}

    public static function inserir()
    {
        return "INSERT INTO carrimbo (
                    idempresa, idpessoa,idobjeto, tipoobjeto, idobjetoext, tipoobjetoext,
                    status, criadopor, criadoem, alteradopor, alteradoem
                )
                VALUES (
                    ?idempresa?, ?idpessoa?, ?idobjeto?, '?tipoobjeto?', ?idobjetoext?, '?tipoobjetoext?',
                    '?status?', '?criadopor?', '?criadoem?', '?alteradopor?', '?alteradoem?'
                )";
    }

    public static function buscarAssinaturaTraResultado(){
        return "SELECT 
                    idpessoa
                FROM
                    carrimbo
                WHERE
                    tipoobjeto = 'amostratra'
                        AND idobjeto = ?idamostra?
                        AND status = 'ASSINADO'";
    }

    public static function buscarUltimaAssinaturaPendentePorIdObjetoTipoObjetoIdPessoa(){
        return "SELECT * 
                FROM carrimbo
                WHERE status='PENDENTE' 
                    AND idobjeto = ?idobjeto? 
                    AND tipoobjeto='?tipoobjeto?'
                    AND idpessoa=?idpessoa?
                ORDER BY idcarrimbo DESC
                LIMIT 1";
    }

    public static function deletarPorIdObjetoTipoObjetoEIdPessoa()
    {
        return "DELETE FROM carrimbo 
                WHERE  idpessoa    = ?idpessoa?
                AND idobjeto    = ?idobjeto?
                AND tipoobjeto  = '?tipoobjeto?'
                AND status 		= 'PENDENTE'";
    }
    
    public static function InserirAssinaturaResultado(){
        return "INSERT INTO `laudo`.`carrimbo`
                            (`idempresa`,
                            `idpessoa`,
                            `idobjeto`,
                            `tipoobjeto`,
                            `status`,
                            `criadopor`,
                            `criadoem`,
                            `alteradopor`,
                            `alteradoem`, 
                            `assinatura`)
                            VALUES
                            (?idempresa?,
                            ?idpessoa?,
                            ?idresultado?,
                            'resultado',
                            'ASSINADO',
                            '?usuario?',
                            now(),
                            '?usuario?',
                            now(),
                            '?assinatura?'
                            );
                            ";
    }

    public static function buscarAssinaturaPorStatusIdPessoaIdObjetoETipoObjeto()
    {
        return "SELECT
                    c.idcarrimbo,
                    c.status
                FROM carrimbo c
                WHERE c.status IN ('PENDENTE', 'ATIVO')
                AND c.idpessoa    = ?idpessoa?
                AND c.idobjeto    = ?idobjeto?
                AND c.tipoobjeto  in (?tipoobjeto?) 
                ORDER BY idcarrimbo desc
                LIMIT 1";
    }

    public static function deletarObjetoCarimbo()
    {
        return "DELETE FROM carrimbo 
                WHERE idobjeto= ?idobjeto?
                AND idpessoa = ?idpessoa?
                AND idcarrimbo = ?idcarrimbo?
                AND status = 'PENDENTE'";
    }

    public static function deletarAssinaturasPendentesDeColaboradoresInativos()
    {
        return "DELETE c
                FROM carrimbo c 
                WHERE c.status = 'PENDENTE' 
                AND c.idpessoa IN (SELECT p.idpessoa FROM pessoa p WHERE p.idpessoa = c.idpessoa AND p.status = 'INATIVO')";
    }

    public static function buscarAssinaturaTEA()
    {
        return "SELECT c.status, 
                        c.idcarrimbo, 
                        c.idpessoa,
                        c.criadoem,
                        (SELECT a.idarquivo FROM arquivo a WHERE a.tipoarquivo in('AMOSTRA')  and a.tipoobjeto = 'amostra' and a.idobjeto = '?idamostra?' limit 1) AS idarquivo
                FROM carrimbo c 
                WHERE c.idobjeto = '?idamostra?'
                    AND c.tipoobjeto = '?modulo?' AND idobjetoext = 882 AND tipoobjetoext = 'idfluxostatus'";
    }

    public static function buscarConferenciaAmostra()
    {
        return "SELECT c.idcarrimbo,
                        c.idobjeto,
                        c.status
                FROM carrimbo c
                    join amostra a on (a.idamostra = c.idobjeto and a.status = 'CONFERIDO')
                where c.idobjeto = ?idamostra?";
    }

    public static function buscarAssinaturaPessoa()
    {
        return "SELECT p.idpessoa,
                       p.nome,
                       CASE
                            WHEN c.status = 'ATIVO' THEN DMA(c.alteradoem)
                            ELSE ''
                       END AS dataassinatura,
                       CASE
                            WHEN c.status = 'ATIVO' THEN 'ASSINADO'
                            ELSE 'PENDENTE'
                       END AS status
                  FROM carrimbo c JOIN pessoa p ON p.idpessoa = c.idpessoa
                 WHERE c.status IN (?status?)
                   AND c.tipoobjeto = '?tipoobjeto?'
                   AND c.idobjeto = ?idobjeto?
              ORDER BY nome";
    }
    
    public static function buscarAssinaturaAmostra()
    {
        return "SELECT *
                from carrimbo c
                where c.idobjeto = ?idamostra?
                    and c.idempresa = ?idempresa?
                    and c.tipoobjeto='amostra' and c.status='ASSINADO'";
    }

    public static function verificarAssinaturaAmostra()
    {
        return "SELECT idcarrimbo,
                        idpessoa,
                        status,
                        alteradoem
                FROM carrimbo 
                WHERE idobjeto = '?idamostra?'
                    AND tipoobjeto = '?modulo?'
                    AND idobjetoext = '882'
                    AND tipoobjetoext = 'idfluxostatus'
                    AND status IN ('PENDENTE','ASSINADO')";
    }

    public static function deletarAssinaturasPendentesPorIdobjetoIdobjetoextTipoobjetoext()
    {
        return "DELETE FROM carrimbo WHERE idobjeto = '?idobjeto?' AND idobjetoext = '?idobjetoext?' AND tipoobjetoext = '?tipoobjetoext?' AND status = 'PENDENTE'";
    }

    public static function alterarDataAssinatura()
    {
        return "UPDATE carrimbo
                    SET alteradoem = '?data?'
                WHERE idcarrimbo = ?idcarrimbo?";
    }

    public static function buscarAssinaturaFluxo(){
        return "SELECT c.idobjeto, 
                    a.idarquivo, 
                    c.status, 
                    c.idobjetoext, 
                    f.idfluxostatus, 
                    c.alteradoem, 
                    c.criadoem,
                    c.alteradopor, 
                    p.nome AS alteradopor, 
                    p2.nome AS criadopor, 
                    c.idcarrimbo
            FROM carrimbo c 
                LEFT JOIN fluxostatus f ON c.idobjetoext = f.idfluxostatus 
                    AND c.status <> 'INATIVO'
                JOIN pessoa p ON p.idpessoa = c.idpessoa
                JOIN pessoa p2 ON p2.usuario = c.criadopor
                LEFT JOIN arquivo a ON a.tipoarquivo = 'ANEXO' 
                    AND a.tipoobjeto = '?tabela?' 
                    AND a.idobjeto = '?idobjeto?'              
            WHERE c.idobjeto = '?idobjeto?'  
                AND c.tipoobjeto = '?modulo?'";
    }

    public static function buscarAssinaturasPendentesPorPessoa(){
        return "SELECT idcarrimbo, criadoem
            FROM carrimbo 
            WHERE status='PENDENTE' 
                AND idobjeto = '?idobjeto?'
                AND tipoobjeto = '?modulo?'
                AND idobjetoext <> '' 
                AND tipoobjetoext <> ''
                AND idpessoa='?idpessoa?'";
    }

    public static function buscarAssinaturasPendentes(){
        return "SELECT c.idobjeto
            FROM carrimbo c 
            WHERE idobjeto = '?idobjeto?' 
                AND tipoobjeto = '?modulo?' 
                AND c.status = 'PENDENTE'";
    }

    public static function buscarAssinaturasAtivas(){
        return "SELECT c.idobjeto
            FROM carrimbo c 
            WHERE idobjeto = '?idobjeto?'  
                AND tipoobjeto = '?modulo?' 
                AND c.status IN ('ATIVO', 'ASSINADO', 'CANCELADO')";
    }

    public static function buscarQuantidadeAssinaturasPendentesEAtivas(){
        return "SELECT 
                    COUNT(CASE WHEN c.status = 'PENDENTE' THEN 0 END) AS countpendente,
                    COUNT(CASE WHEN c.status IN ('ATIVO',  'ASSINADO') THEN 0 END) AS countativo
            FROM carrimbo c 
            WHERE idobjeto = '?idobjeto?'
                AND tipoobjeto = '?modulo?'";
    }

    public static function buscarCarimboPorTabelaEModulo() {
        return "SELECT idcarrimbo 
            FROM carrimbo
            WHERE idobjeto = '?idobjeto?' 
                AND tipoobjeto IN ('?modulo?', '?tabela?')";
    }

    public static function inserirAssinaturaPendenteFluxo () {
        return "INSERT INTO carrimbo 
                (idempresa, 
                idpessoa, 
                idobjeto, 
                tipoobjeto, 
                idobjetoext, 
                tipoobjetoext, 
                status,     
                tipoassinatura,
                criadopor, 
                criadoem, 
                alteradopor, 
                alteradoem)
            VALUES
                (?idempresa?,
                '?idpessoa?',
                '?idobjeto?',
                '?tipoobjeto?', 
                '?idobjetoext?',
                'idfluxostatus',
                'PENDENTE',
                '?tipoassinatura?',
                '?criadopor?',
                now(),
                '?criadopor?',
                now());";
    }

    public static function buscarAssinaturaPorIdObjetoTipoObjetoStatus(){
        return "SELECT *
            FROM carrimbo c 
            WHERE idobjeto = '?idobjeto?' 
                AND tipoobjeto = '?tipoobjeto?' 
                AND c.status ?status?";
    }

    public static function buscarAssinaturaComArquivoFluxoStatus(){
        return "SELECT c.idobjeto, a.idarquivo
            FROM carrimbo c
                LEFT JOIN arquivo a ON a.tipoarquivo = 'ANEXO' 
                    AND a.tipoobjeto = 'amostra' 
                    AND a.idobjeto = '?idobjeto?'
            WHERE c.idobjeto = '?idobjeto?' 
                AND c.tipoobjeto = '?tipoobjeto?' 
                AND c.status IN ('PENDENTE') 
                AND c.idobjetoext = 882 
                AND c.tipoobjetoext = 'idfluxostatus'";
    }

    public static function buscarPessoaAssinaturaStatusAssinado()
    {
        return "SELECT idpessoa
                  FROM carrimbo
                 WHERE tipoobjeto = '?tipoobjeto?'
                   AND (idobjetoext <> 882 OR idobjetoext IS NULL)
                   AND idobjeto = ?idamostra?
                   AND status = 'ASSINADO'";
    }

    public static function BuscarDescrProdservVacina()
    {
        return "SELECT p.idprodserv,
                       p.descrcurta,
                       p.fabricado
                       FROM 
                            lote l
                       JOIN 
                            prodserv p ON (p.idprodserv = l.idprodserv)
                       WHERE
                            l.idlote = '?idlote?'";
    }
}
?>