<?
require_once(__DIR__ . "/_iquery.php");

class FluxoStatusHistQuery implements DefaultQuery {

    public static $table = "fluxostatushist";
	public static $pk = "idfluxostatushist";

	
	public static function buscarPorChavePrimaria()
	{
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
	}

    public static function inserir()
    {
        return "INSERT INTO fluxostatushist (
                    idempresa, idfluxostatus, idfluxostatuspessoa, idmodulo, modulo, criadopor, criadoem, alteradopor, alteradoem
                ) VALUES (
                    ?idempresa?, ?idfluxostatus?, ?idfluxostatuspessoa?, ?idmodulo?, '?modulo?', '?criadopor?', ?criadoem?, '?alteradopor?', ?alteradoem?
                )";
    }

    public static function verificarStatusEnviarNf() {
        return "SELECT 
            fh.idfluxostatushist,
            fs.idfluxostatus,
            fs.ocultar,
            fs.idfluxo,
            fs.ordem
        FROM
            fluxostatushist fh
                JOIN
            fluxostatus fs ON (fs.idfluxostatus = fh.idfluxostatus)
        WHERE
            fh.modulo = 'pedido'
                AND fh.idmodulo = ?idnf?
                AND EXISTS( SELECT 
                    1
                FROM
                    carbonnovo._status s
                WHERE
                    s.idstatus = fs.idstatus
                        AND s.statustipo = 'ENVIAR'
                        AND s.status = 'ATIVO')
                AND fh.status = 'PENDENTE'
                AND NOT EXISTS( SELECT 
                    1
                FROM
                    fluxostatushist fh1
                        JOIN
                    fluxostatus fs1 ON (fs1.idfluxostatus = fh1.idfluxostatus)
                WHERE
                    fh1.modulo = 'pedido'
                        AND fh1.idmodulo = ?idnf?
                        AND EXISTS( SELECT 
                            1
                        FROM
                            carbonnovo._status s1
                        WHERE
                            s1.idstatus = fs1.idstatus
                                AND s1.statustipo IN ('CANCELADO' , 'CONCLUIDO', 'DEVOLVIDO', 'ENVIADO', 'RECUSADO')
                                AND s1.status = 'ATIVO')
                        AND fh1.status IN ('ATIVO'))";
    }


    public static function buscarInfoFluxoResultado(){
        return"SELECT 
                UPPER(s.rotulo) AS valor,
                fh.criadopor,
                fh.criadoem,
                p.assinateste
            FROM
                fluxostatushist fh
                    JOIN
                fluxostatus fs ON fh.idfluxostatus = fs.idfluxostatus
                    JOIN
                carbonnovo._status s ON fs.idstatus = s.idstatus
                    JOIN
                pessoa p ON fh.criadopor = p.usuario
            WHERE
                modulo = '?modulo?'
                    AND idmodulo = ?idresultado?
            ORDER BY fh.criadoem ASC";
    }

    public static function buscarFluxoStatusHistPorIdEvento()
    {
        return "SELECT 1
                FROM fluxostatushist
                WHERE idmodulo = ?idmodulo?
                AND modulo = 'evento'";
    }

    public static function deletarFluxostatushist()
    {
            return "DELETE FROM fluxostatushist WHERE idmodulo =?idresultado? AND modulo = '?modulo?' AND idfluxostatus = ?idfluxostatusass? ORDER BY idfluxostatushist LIMIT 1";
    }

    public static function buscarQuantidadeFluxoStatusHistPorModulo(){
        return "SELECT count(*) AS contador
            FROM fluxostatushist fh 
            WHERE status <> 'INATIVO' 
                AND idmodulo = '?idobjeto?' 
                AND modulo = '?modulo?'";
    }

    public static function inserirFluxoStatusHist () {
        return "INSERT INTO fluxostatushist (idempresa, idfluxostatus, idmodulo, modulo, status, criadopor, criadoem, alteradopor, alteradoem) 
            VALUES (?idempresa?, '?idfluxostatus?', '?idmodulo?', '?modulo?', '?status?', '?criadopor?', now(), '?criadopor?', now())";
    }

    public static function buscarQuantidadeFluxoStatusHistPorModuloEFluxoStatus () {
        return "SELECT count(*) AS contador
            FROM fluxostatushist fh 
            WHERE status <> 'INATIVO' 
                AND idmodulo = '?idmodulo?' 
                AND modulo = '?modulo?' 
                AND idfluxostatus = '?idfluxostatus?'";
    }

    public static function buscarFluxoStatusHistPorModuloEFluxoStatus () {
        return "SELECT idfluxostatushist
            FROM fluxostatushist fh 
            WHERE status <> 'INATIVO' 
                AND idmodulo = '?idmodulo?' 
                AND modulo = '?modulo?' 
                AND idfluxostatus = '?idfluxostatus?'
                ?clausula?";
    }

    public static function InsertFluxoEsDiario(){
        return "INSERT INTO fluxo_es_diario (idfluxo, idfluxostatus, entrada, saida, saldo, atraso, criadoem)
        VALUES (?idfluxo?, ?idfluxostatus?, ?entrada?, ?saida?, ?saldo?, ?atraso?, '?criadoem?');";
    }
    
    public static function buscarPrazoFLuxo(){
        return "SELECT 
                    fsh.idfluxostatushist,
                    f.colprazod,
                    fs.prazod,
                    fsh.idmodulo,
                    f.modulo,
                    fsh.alteradoem, 
                    cbm.tab,
                    mto.col
                FROM
                    fluxostatushist fsh
                        INNER JOIN
                    fluxostatus fs ON fsh.idfluxostatus = fs.idfluxostatus
                        INNER JOIN
                    fluxo f ON f.idfluxo = fs.idfluxo
                        INNER JOIN
                    carbonnovo._modulo cbm ON cbm.modulo = f.modulo
                        INNER JOIN
                    carbonnovo._mtotabcol mto ON mto.tab = cbm.tab AND mto.primkey = 'Y'
                WHERE
                    fsh.idfluxostatushist = '?idfluxostatushist?'
                AND
                    f.colprazod IS NOT NULL
                AND
                    fs.prazod IS NOT NULL
                AND
                    fsh.idmodulo IS NOT NULL";
    }

    public static function atualizarStatusFluxostatushist(){
        return "UPDATE 
            fluxostatushist SET status = 'ATIVO', alteradoem = sysdate(), alteradopor = '?usuario?' ?atrasodias?
            WHERE idfluxostatushist = '?idfluxostatushist?'";
    }

    public static function buscarQuantidadeFluxoStatusHistPorModuloEAtividade(){
        return "SELECT count(*) AS contador
            FROM fluxostatushist fh 
                JOIN loteativ l ON fh.idfluxostatus = l.idfluxostatus 
                    AND l.idetapa = '?idetapa?'
            WHERE idmodulo = '?idmodulo?' 
                AND modulo = '?modulo?' 
                AND fh.idfluxostatus = '?idfluxostatus?' 
                AND fh.status <> 'INATIVO'";
    }

    public static function buscarFluxoStatusHistPendentePorModuloeUsuario () {
        return "SELECT idfluxostatushist 
            FROM fluxostatushist 
            WHERE idmodulo = '?idmodulo?'
                AND modulo = '?modulo?' 
                AND idfluxostatus = '?idfluxostatus?'
                AND status = 'PENDENTE'
                AND criadopor = '?usuario?'";
    }

    public static function buscarFluxoStatusHistPendentePorModulo () {
        return "SELECT idfluxostatushist
            FROM fluxostatushist fh
            WHERE idmodulo = '?idmodulo?' 
                AND modulo = '?modulo?' 
                AND fh.status = 'PENDENTE'
                AND EXISTS (
                    SELECT 1
                    FROM fluxostatus fs
                    WHERE fs.idfluxostatus = fh.idfluxostatus
                )";
    }

    public static function buscarFluxoStatusPorModuloeId () {
        return "SELECT idfluxostatushist
            FROM fluxostatushist fh 
            WHERE idmodulo = '?idmodulo?'
                AND modulo = '?modulo?' 
                AND idfluxostatus = '?idfluxostatus?'";
    }

    public static function alterarStatusFluxoStatusHistPorModulo () {
        return "UPDATE fluxostatushist
            SET status = '?status?',
                alteradoem = sysdate(), 
                alteradopor = '?usuario?'
            WHERE modulo = '?modulo?' 
                AND idmodulo = '?idmodulo?'
                ?clausula?";
    }

    public static function inativarFluxosHistPorModulo () {
        return "UPDATE fluxostatushist fh 
                JOIN fluxostatus fs ON fh.idfluxostatus = fs.idfluxostatus
            SET fh.alteradoem = sysdate(), 
                status = 'INATIVO'
            WHERE fh.idfluxostatushist >= '?idfluxostatushist?' 
                AND modulo = '?modulo?' 
                AND idmodulo = '?idmodulo?'";
    }

    public static function buscarFluxoStatusPendentePorModuloeId () {
        return "SELECT idfluxostatushist
            FROM fluxostatushist
            WHERE idmodulo = '?idmodulo?' 
                AND modulo = '?modulo?' 
                AND status = 'PENDENTE'";
    }

    public static function buscarUltimoStatusHist () {
        return "SELECT idfluxostatushist, idfluxostatus, status
                from fluxostatushist
                where idmodulo = ?idlote?
                and modulo = 'lotealmoxarifado'
                order by idfluxostatushist desc 
                limit 1";
    }
}
?>