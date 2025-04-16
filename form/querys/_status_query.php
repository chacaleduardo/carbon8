<?
require_once(__DIR__."/_iquery.php");


class _StatusQuery implements DefaultQuery{
    public static $table = _DBCARBON.'_status';
    public static $pk = 'idstatus';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarIdStatusInicialPorModuloIdobjetoTipoObjeto(){
        return "SELECT mf.idstatus 
                FROM fluxo ms
                    JOIN fluxostatus mf ON (ms.idfluxo = mf.idfluxo  AND ms.modulo = '?modulo?' AND ms.tipoobjeto = '?tipoobjeto?' AND ms.idobjeto = '?idobjeto?' AND ms.status = 'ATIVO')
                    JOIN "._DBCARBON."._status s ON s.idstatus = mf.idstatus AND s.statustipo = 'INICIO'
                WHERE 1 ?getidempresa?";
    }

    public static function buscarStatusDoHistoricoDeEventosDaPessoa() 
    {
        return "SELECT
                    eh.idfluxostatus, 
                    eh.idfluxostatuspessoa, 
                    fp.oculto, 
                    fs.ocultar,
                    eh.criadoem, 
                    es.botao, 
                    es.cor, 
                    es.cortexto, 
                    p.nomecurto
                FROM "._DBCARBON."._status es 
                JOIN fluxostatus fs ON es.idstatus = fs.idstatus AND es.statustipo <> 'INICIO'
                JOIN fluxostatushist eh ON eh.idfluxostatus = fs.idfluxostatus AND eh.idfluxostatuspessoa = ?idfluxostatuspessoa?
                JOIN fluxostatuspessoa fp ON fp.idfluxostatuspessoa = eh.idfluxostatuspessoa                          
                JOIN pessoa p ON fp.idobjeto = p.idpessoa AND tipoobjeto = 'pessoa' AND p.idpessoa = ?idpessoa?
                WHERE eh.idmodulo = '?idevento?' 
                AND eh.modulo = 'evento'
                GROUP BY eh.idfluxostatus
                ORDER BY eh.criadoem desc";
    }

    public static function buscarStatusParaVinculoPorIdFluxoStatus()
    {
        return "SELECT e.idstatus,
                        CONCAT('[', botao, '] (U - ', e.rotuloresp, ') (E - ', e.rotulo, ') ', IF(e.statustipo is null, '', CONCAT(' - Status Tipo: ', e.statustipo, ' - ID Status: ',if(e.tipobotao <> '', e.tipobotao, ''), ' - ',  e.idstatus))) AS rotuloresp
                FROM "._DBCARBON."._status e
                WHERE e.status = 'ATIVO'
                AND NOT EXISTS(
                    SELECT 1 
                    FROM fluxostatus s
                    WHERE s.idstatus = e.idstatus
                    AND s.idfluxo = ?idfluxo?
                    AND s.idfluxostatus != ?idfluxostatus?) 
                ORDER BY e.botao , e.rotuloresp , e.rotulo";
    }

    public static function buscarStatusOP() {
        return "SELECT s.idstatus, s.rotulo
                from carbonnovo._status s
                where exists (
                    select 1
                    from formalizacao f
                    join fluxostatus fs on fs.idfluxostatus = f.idfluxostatus
                    join carbonnovo._status cs on cs.idstatus = fs.idstatus and cs.idstatus = s.idstatus
                );";
    }
}
