<?
class FluxoQuery {

    public static function buscarTokenInicialPorIdEventoTipo()
    {
        return "SELECT fs.idfluxostatus
            FROM fluxo f 
                JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo 
                    AND f.idobjeto = ?ideventotipo? 
                    AND f.tipoobjeto = 'ideventotipo' 
                    AND f.status = 'ATIVO'
                JOIN "._DBCARBON."._status s ON s.idstatus = fs.idstatus
            WHERE s.statustipo='INICIO'";
    }

    public static function buscarParticipantesPorIdEventoEIdeventoTipo()
    {
        return "SELECT mfo.idobjeto,
                    mfo.tipoobjeto 
                FROM fluxo ms
                JOIN fluxoobjeto mfo ON mfo.idfluxo = ms.idfluxo
                AND mfo.idobjeto NOT IN ( 
                    SELECT idobjeto 
                    FROM fluxostatuspessoa 
                    WHERE idmodulo = ?idevento?
                    AND modulo = 'evento'
                )
                WHERE tipo = 'PARTICIPANTE' 
                AND ms.idobjeto = ?ideventotipo?
                AND ms.modulo = 'evento' 
                AND ms.status = 'ATIVO'";
    }

    public static function verificarSePessoaPodeEntrarNoEvento()
    {
        return "SELECT fo.*
                FROM fluxo f 
                JOIN fluxoobjeto fo ON(fo.idfluxo = f.idfluxo)
                WHERE f.idobjeto = ?ideventotipo? 
                AND f.tipoobjeto = 'ideventotipo'
                AND fo.idobjeto = ?idpessoa?
                AND fo.tipo = 'PARTICIPANTE'";
    }

    public static function buscarPessoasQuePrecisamAssinarPorIdEventoTipo()
    {
        return "SELECT mfo.idobjeto
                FROM fluxo ms
                JOIN fluxoobjeto mfo ON mfo.idfluxo = ms.idfluxo
                WHERE ms.idobjeto = '?ideventotipo?'
                AND assina in ('INDIVIDUAL', 'PARCIAL', 'TODOS')
                AND mfo.tipoobjeto = 'pessoa'";
    }

    public static function buscarStatusDosEventos()
    {
        return "SELECT DISTINCT cs.rotulo
                FROM fluxo f
                JOIN eventotipo et on(et.ideventotipo = f.idobjeto and f.tipoobjeto = 'ideventotipo')
                JOIN evento e on(e.ideventotipo = et.ideventotipo)
                JOIN fluxostatus fs ON(fs.idfluxostatus = e.idfluxostatus)
                JOIN carbonnovo._status cs ON(cs.idstatus = fs.idstatus)
                WHERE f.modulo = 'evento'
                AND cs.status = 'ATIVO'
                AND f.status = 'ATIVO'
                AND et.status = 'ATIVO'
                GROUP BY cs.idstatus";
    }

    public static function buscarTipoBotaoPorModuloeStatus()
    {
        return "SELECT fs.idfluxostatus, 
                    s.statustipo,
                    s.tipobotao
                FROM fluxo f
                    JOIN fluxostatus fs ON (f.idfluxo = fs.idfluxo AND f.modulo = '?modulo?' AND f.status = 'ATIVO')
                    JOIN carbonnovo._status s ON (fs.idstatus = s.idstatus AND s.statustipo = '?status?')";
    }

    public static function buscarStatusDoFluxo()
    {
        return "SELECT s.rotulo, t.status 
                FROM ?tabela? t 
                JOIN fluxostatus fs ON t.idfluxostatus = fs.idfluxostatus
                JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus
                WHERE t.?_primary? = '?idobjeto?'";
    }

    public static function buscarFluxoModulo(){
        return "SELECT e.etapa, 
                    e.idetapa, 
                    e.ordem,
                    mf.idfluxostatus, 
                    s.rotulo, 
                    s.statustipo,
                    s.tipobotao,
                    mh.status,                    
                    mh.idfluxostatushist,
                    concat(ifnull(ps.nomecurto,ps.nome),' (',ifnull(em.sigla,''),')') as criadopor,
                    mh.criadoem,
                    concat(ifnull(ps.nomecurto,ps.nome),' (',ifnull(em.sigla,''),')') as alteradopor,
                    mh.alteradoem,
                    f.idobjeto,
                    mf.ocultar,
                    mf.idstatus,
                    mf.botaocriador,
                    mf.numfluxostatus,
                    mf.ordem AS ordemfs,
                    tb.idfluxostatus AS idfluxostatustab,
                    st.statustipo AS statustipotab
            FROM fluxo f
                JOIN fluxostatus mf ON f.idfluxo = mf.idfluxo ?clausula?
                JOIN etapa e ON e.idetapa = mf.idetapa
                JOIN "._DBCARBON."._status s ON s.idstatus = mf.idstatus
                JOIN ?tabela? tb ON tb.?primary? = ?idobjeto?
                JOIN fluxostatus ft ON ft.idfluxostatus = tb.idfluxostatus
                JOIN "._DBCARBON."._status st ON st.idstatus = ft.idstatus
                LEFT JOIN fluxostatushist mh ON mh.idfluxostatus = mf.idfluxostatus 
                    AND mh.idmodulo = '?idobjeto?' 
                    AND mh.modulo = '?modulo?' 
                    AND mh.idfluxostatuspessoa IS NULL
                LEFT JOIN pessoa ps on(ps.usuario= mh.criadopor)
                LEFT JOIN empresa em on(em.idempresa=ps.idempresa)
            WHERE f.modulo = '?modulo?' 
                AND f.status = 'ATIVO' 
            ORDER BY etapa, mh.criadoem, mh.status;";
    }

    public static function buscarConfiguracaoAssinaturaFluxo() {
        return "SELECT r.assina, r.inidstatus
            FROM fluxo f 
                JOIN fluxoobjeto r ON r.idfluxo = f.idfluxo 
                    AND r.tipo = 'PARTICIPANTE' 
                    AND f.modulo = '?modulo?'";
    }

    public static function buscarDadosResultadoAmostra () {
        return "SELECT fs.idfluxostatus, 
                f.idfluxo,
                ?idfluxostatushist?
                fs.ordem,
                s.statustipo,
                s.tipobotao,
                ?criadopor?,
                m.modulo,
                r.idamostra
                ?idresultado?
            FROM ?from?
                JOIN unidadeobjeto uo ON ?unidade? = uo.idunidade 
                    AND uo.tipoobjeto = 'modulo'
                JOIN "._DBCARBON."._modulo m on m.modulo = uo.idobjeto 
                    AND m.modulotipo = '?modulotipo?' 
                JOIN fluxo f ON f.modulo = uo.idobjeto 
                    AND f.status = 'ATIVO'
                JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo
                JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus 
                    AND s.statustipo = '?statustipo?'
            WHERE r.?primary? = '?idobjeto?' ?status?";
    }

    public static function buscarFluxoStatusHist() {
        return "SELECT fs.idfluxostatus, 
                f.idfluxo,
                fs.ordem,
                s.statustipo,
                s.tipobotao,
                (SELECT idfluxostatushist 
                    FROM fluxostatushist fh 
                    WHERE fh.idmodulo = t.?primary?
                        AND fh.modulo = f.modulo 
                        AND status = 'PENDENTE' 
                    ORDER BY idfluxostatushist DESC LIMIT 1
                ) AS idfluxostatushist
            FROM fluxo f 
                JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo 
                    AND f.modulo = '?modulo?' 
                    AND f.status = 'ATIVO' 
                JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus 
                    AND s.statustipo = '?status?' ?validaTipo?
                JOIN ?tabela? t
                ?join?
            WHERE t.?primary? = '?idobjeto?' ?sql? ?ordem?";
    }

    public static function buscarFluxoStatus(){
        return "SELECT fs.idfluxostatus,
                fs.idetapa,
                fh.status,
                ft.idfluxostatus AS idfluxostatustab,
                st.statustipo AS statustipotab,
                st.tipobotao
            FROM fluxo f 
                JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo  
                    AND f.modulo = '?modulo?' 
                    AND f.status = 'ATIVO' 
                    AND fs.idetapa IS NOT NULL
                    ?clausula1?
                JOIN etapa e ON e.idetapa = fs.idetapa
                JOIN ?tabela? tb ON tb.?primary? = '?idprimary?'
                JOIN fluxostatus ft ON ft.idfluxostatus = tb.idfluxostatus
                JOIN "._DBCARBON."._status st ON st.idstatus = ft.idstatus
                LEFT JOIN fluxostatushist fh ON fh.idfluxostatus = fs.idfluxostatus 
                    AND fh.idmodulo = '?idmodulo?' 
                    AND fh.modulo = '?modulo?'
            ?clausula2?
        ORDER BY e.etapa";
    }

    public static function buscarFluxoRestauracao() {
        return "SELECT s.statustipo, 
                s.rotulo, 
                fs.idfluxostatus, 
                fs.ordem
            FROM fluxo f 
                JOIN fluxostatus fs ON fs.idfluxo = f.idfluxo
                JOIN "._DBCARBON."._status s ON s.idstatus = fs.idstatus
            WHERE f.status = 'ATIVO' 
                AND f.modulo = '?modulo?' 
                AND fs.idetapa > 0
                AND fs.ordem < (SELECT f.ordem FROM ?tabela? t JOIN fluxostatus f ON f.idfluxostatus = t.idfluxostatus WHERE t.?_primary? = ?_idobjeto?)
            ORDER BY fs.ordem DESC";
    }

    public static function buscarFluxoRestauracaoModuloComTipo() {
        return "SELECT s.statustipo, 
                       s.rotulo, 
                       fs.idfluxostatus, 
                       fs.ordem
                  FROM fluxo f JOIN fluxostatus fs ON fs.idfluxo = f.idfluxo ?sqlAnd?
                  JOIN "._DBCARBON."._status s ON s.idstatus = fs.idstatus
                 WHERE f.status = 'ATIVO' 
                   AND f.modulo = '?modulo?' 
                   AND fs.idetapa > 0
                   AND fs.ordem < (SELECT f.ordem FROM ?tabela? t JOIN fluxostatus f ON f.idfluxostatus = t.idfluxostatus WHERE t.?_primary? = ?_idobjeto?)
              ORDER BY fs.ordem DESC";
    }

    public static function buscarIdFluxoStatusPorPrProc () {
        return "SELECT fs.idfluxostatus
            FROM fluxo f 
                JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo
                JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus 
                    AND s.statustipo = '?statustipo?' 
                    ?clausula?
                JOIN prproc p ON p.subtipo = f.idobjeto 
                    AND f.tipoobjeto = 'subtipo'
            WHERE f.modulo LIKE 'formalizacao%' 
                AND p.idprproc = '?idprproc?'";
    }

    public static function buscarIdFluxoStatusPorModulo () {
        return "SELECT idfluxostatus
            FROM fluxo f 
                JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo 
                    AND f.status = 'ATIVO'
                JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus 
                    AND s.statustipo = '?statustipo?'
            WHERE f.modulo = '?modulo?'";
    }

    public static function buscarIdFluxoStatusPorStatusTipo () {
        return "SELECT idfluxostatus
            FROM fluxo f
                JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo
                    AND f.modulo = '?modulo?'
                JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus
                    AND s.statustipo = '?statustipo?'
                    ?clausula?";
    }

    public static function buscarStatusPorModulo()
    {
        return "SELECT cs.*
                FROM fluxo f
                join fluxostatus fs on(fs.idfluxo = f.idfluxo)
                join carbonnovo._status cs on(fs.idstatus = cs.idstatus)
                where f.modulo = '?modulo?'
                order by fs.ordem";
    }

    public static function buscarFluxoPorModuloETipoObjeto()
    {
        return "SELECT fx.idfluxostatus, CONCAT(s.rotuloresp, ' - ', s.statustipo) as rotuloresp
                  FROM fluxo f JOIN fluxostatus fx ON f.idfluxo = fx.idfluxo
                  JOIN "._DBCARBON."._status s ON fx.idstatus = s.idstatus
                 WHERE s.status = 'ATIVO' 
                   AND f.modulo like ('?modulo?%')
                   AND f.tipoobjeto = '?tipoobjeto?'
                   AND f.idobjeto = '?idobjeto?'
                   AND s.statustipo NOT IN ('ABERTO', 'CANCELADO', 'APROVADO')  
              ORDER BY rotuloresp";
    }

    public static function buscarStatusDoModulo()
    {
        return "SELECT fs.idfluxostatus, s.rotulo
								FROM fluxo f 
									JOIN fluxostatus fs ON fs.idfluxo = f.idfluxo
									JOIN carbonnovo._status s ON s.idstatus = fs.idstatus
								WHERE f.status = 'ATIVO' AND f.modulo = '?modulo?'
								ORDER BY ordem";
    }

    public static function buscarStatusTipoPorModulo()
    {
        return "SELECT fs.idfluxostatus,s.statustipo
            FROM fluxo f
                JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo
                    AND f.modulo = '?modulo?'
                    AND fs.idfluxostatus = ?idfluxostatus?
                JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus";
    }

    public static function buscarIdfluxostatusInicioPorModulo() {
        return "SELECT idfluxostatus
                FROM fluxo f 
                JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo AND f.status = 'ATIVO'
                JOIN carbonnovo._status s ON fs.idstatus = s.idstatus AND s.tipobotao = 'INICIO'
                WHERE f.modulo = '?modulo?'";
    }
}
?>