<?
require_once(__DIR__."/_iquery.php");

class FluxoStatusQuery implements DefaultQuery
{
    public static $table = 'fluxostatus';
    public static $pk = 'idfluxostatus';

   public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    } 

    public static function buscarStatusTipoEnviadoFluxo(){
        return "SELECT 
                fs.idfluxostatus
            FROM
                fluxostatus fs
                    JOIN
                carbonnovo._status s ON (s.idstatus = fs.idstatus)
            WHERE
                fs.idfluxo = ?idfluxo?
                    AND s.statustipo = 'ENVIADO'
                    AND s.status = 'ATIVO'";
    }

    public static function buscarRotuloStatusFluxo()
    {
        return "SELECT s.rotulo, t.status FROM ?tabela? t JOIN fluxostatus fs ON t.idfluxostatus = fs.idfluxostatus
                  JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus
                 WHERE t.?primarykey? = '?idobjeto?'";
    }

    public static function buscarRotuloStatusFluxoPorTipoBotao()
    {
        return "SELECT cs.rotulo, cs.statustipo
                FROM fluxo f
                JOIN fluxostatus fs ON(fs.idfluxo = f.idfluxo)
                JOIN "._DBCARBON."._status cs ON(cs.idstatus = fs.idstatus)
                WHERE modulo = '?modulo?'
                AND tipobotao = '?tipobotao?'";
    }

    public static function buscarFluxostatusPorIdFluxoStatus()
    {
        return "SELECT novamensagem FROM fluxostatus WHERE idfluxostatus = ?idfluxostatus?";
    }

    public static function buscarBotoesFluxoPorTabela() {
        return "SELECT fsb.ocultar,
                    ete.idfluxostatus,
                    s.cor,
                    s.botao,
                    s.cortexto,   
                    s.rotulo,  
                    s.rotuloresp,  
                    s.statustipo,
                    s.tipobotao,                              
                    fsb.botaocriador,
                    fsb.botaoparticipante,
                    fsb.idfluxostatus AS idfluxostatusf,
                    fh.idfluxostatushist,
                    fsb.ordem,
                    ete.idfluxo,
                    t.criadopor
            FROM ?tabela? t
                JOIN fluxostatus ete ON (ete.idfluxostatus = t.idfluxostatus)
                JOIN fluxo f ON ete.idfluxo = f.idfluxo ?sql?
                LEFT JOIN fluxostatushist fh ON fh.idfluxostatus = ete.idfluxostatus 
                    AND fh.status = 'PENDENTE' 
                    AND fh.idmodulo = t.?primary?
                    AND fh.modulo = '?modulo?'
                JOIN fluxostatus fsr ON (fsr.idfluxostatus = ete.idfluxostatus) -- busco a linha do eventoresp
                JOIN fluxostatus fsb ON FIND_IN_SET(fsb.idfluxostatus, fsr.fluxo) 
                LEFT JOIN fluxostatuslp fl ON fl.idfluxostatus = ete.idfluxostatus 
                    AND fl.idlp in (?lps?)
                JOIN "._DBCARBON."._status s ON s.idstatus = fsb.idstatus 
                    AND NOT EXISTS (SELECT 1 
                        FROM fluxostatus fs2 
                        WHERE fs2.idfluxostatus = fsb.idfluxostatus 
                            AND FIND_IN_SET(fs2.idfluxostatus, ete.fluxoocultar)
                    )
            WHERE ?primary? = ?idobjeto?
            ORDER BY fsb.ordem";
    }

    public static function buscarFluxoStatusPorTabela () {
        return "SELECT idfluxostatus, status
            FROM ?tabela?
            WHERE ?primary? = '?idobjeto?'";
    }

    public static function buscarFluxoStatusInicialAtivoPorModulo () {
        return "SELECT fs.idfluxostatus, s.statustipo, fs.idetapa
            FROM fluxostatus fs 
                JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus
                JOIN fluxo f ON fs.idfluxo = f.idfluxo
            WHERE f.modulo = '?modulo?' ?status?
                AND f.status = 'ATIVO' ?sqlAnd?";
    }

    public static function atualizarFluxoStatusPorTabela() {
        return "UPDATE ?tabela?
            SET idfluxostatus = '?idfluxostatus?', status = '?status?', alteradoem = sysdate(), alteradopor = '?usuario?' 
            WHERE ?primary? = '?idobjeto?'";
    }

    public static function buscarOrdemFluxoStatusPorModuloeId(){
        return "SELECT ordem, ocultar
            FROM fluxostatus fs 
                JOIN fluxo f ON fs.idfluxo = f.idfluxo 
                AND f.modulo = '?modulo?' ?clausula?
            WHERE idfluxostatus = '?idfluxostatus?'";
    }

    public static function buscarFluxoStatusPorIdStatusF(){
        return "SELECT DISTINCT fs.idfluxostatus AS idfluxostatus
            FROM fluxostatus fs 
            WHERE fs.fluxo like '%?idstatusf?%'
                AND fs.idetapa is not null
                AND EXISTS (
                    SELECT 1
                    FROM fluxo f
                    WHERE fs.idfluxo = f.idfluxo
                        AND f.modulo = '?modulo?' 
                        ?clausula1?
                )
                AND fs.idetapa = (
                    SELECT DISTINCT(e.idetapa) AS idetapa
                    FROM etapa e
                        JOIN fluxostatus fs ON e.idetapa = fs.idetapa
                    WHERE e.modulo = '?modulo?' ?clausula2?
                    ORDER BY e.ordem DESC LIMIT 1
                )";
    }

    public static function buscarStatusPorIdFluxoStatus()
    {
        return "SELECT statustipo, s.rotuloresp
                  FROM fluxostatus fs JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus
                 WHERE fs.idfluxostatus = ?idfluxostatus?";
    }

    public static function buscarFluxoComprasPrompt()
    {
        return "SELECT fs.idfluxostatus, s.rotulo
                  FROM fluxo f JOIN fluxostatus fs ON fs.idfluxo = f.idfluxo
                  JOIN "._DBCARBON."._status s on s.idstatus = fs.idstatus
                 WHERE f.status = 'ATIVO' 
                   AND f.modulo = '?modulo?' 
                   ?ocultarWhere?
                   ?statusWhere?
                   AND fs.idetapa IS NOT NULL";
    }
}
?>