<?
class LoteAtivQuery{
    public static function buscarFluxoFormalizacaoPorAtividade () {
        return "SELECT idloteativ,
                la.status,
                la.idetapa,
                f.idfluxostatus,
                la.idfluxostatus AS idfluxostatusprativ,
                p.statuspai
            FROM loteativ la JOIN formalizacao f ON la.idlote = f.idlote
                JOIN prativ p ON la.idprativ = p.idprativ
                JOIN fluxostatus fs ON la.idfluxostatus = fs.idfluxostatus
                JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus 
                    AND s.tipobotao = 'INICIO'
            WHERE f.idformalizacao = '?idobjeto?'";
    }

    public static function buscarFluxoStatusLoteAtiv () {
        return "SELECT p.statuspai, la.idfluxostatus  
            FROM loteativ la 
                JOIN prativ p ON p.idprativ = la.idprativ
            WHERE la.idloteativ = '?idloteativ?'";
    }

    public static function buscarQuantidadeAtividadesCompletas () {
        return "SELECT COUNT(l.status) AS total,                            
                    COUNT(CASE WHEN l.status IN ('CONCLUIDO') THEN 0 END) AS countcompleto,
                    (SELECT l.idetapa
                        FROM loteativ l JOIN formalizacao f ON l.idlote = f.idlote
                        WHERE l.status IN ('PENDENTE') 
                            AND f.idformalizacao = '?idobjeto?' LIMIT 1
                    ) AS idetapa
            FROM loteativ l 
                JOIN formalizacao f ON l.idlote = f.idlote 
            WHERE idetapa = (
                SELECT idetapa 
                FROM loteativ l 
                    JOIN formalizacao f ON l.idlote = f.idlote
                WHERE f.idformalizacao = '?idobjeto?' 
                    AND l.status IN ('CONCLUIDO') 
                    AND idetapa <> (
                        SELECT l.idetapa
                        FROM loteativ l 
                            JOIN formalizacao f ON l.idlote = f.idlote
                        WHERE l.status IN ('PENDENTE') 
                            AND f.idformalizacao = '?idobjeto?' 
                        LIMIT 1
                    )
                ORDER BY idloteativ DESC LIMIT 1
                )
                AND idformalizacao = '?idobjeto?'";
    }

    public static function buscarFluxoStatusLoteAtivPorFormalizacao() {
        return "SELECT l.idfluxostatus, s.statustipo
            FROM loteativ l 
                JOIN formalizacao f ON l.idlote = f.idlote 
                JOIN fluxostatus fs ON l.idfluxostatus = fs.idfluxostatus
                JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus
            WHERE l.idetapa = '?idetapa?'
                AND f.idformalizacao = '?idobjeto?'";
    }

    public static function deletarLoteAtivPorLote () {
        return "DELETE FROM loteativ WHERE idlote = '?idlote?'";
    }

    public static function buscarValorMaxAtividadePorIdLoteEStatus()
    {
        return "SELECT MAX(idloteativ) AS idloteativ
                  FROM loteativ
                 WHERE idlote = ?idlote?
                   AND status IN ('PENDENTE', 'PROCESSANDO')";
    }

    public static function buscarLoteAtivPorIdLote()
    {
        return "SELECT * FROM loteativ WHERE idlote = ?idlote?";
    }

    public static function buscarSalasParaReserva()
    {
        return "SELECT a.execucao,
                       a.execucaofim,
                       o.idobjeto AS idtag,
                       p.travasala,
                       a.idloteativ,
                       t.idtagreserva
                  FROM loteativ a JOIN loteobj o ON (o.idloteativ = a.idloteativ AND o.tipoobjeto = 'tag' AND o.idobjeto IS NOT NULL)
                  JOIN prativ p ON (p.idprativ = a.idprativ)
             LEFT JOIN tagreserva t ON (t.idtag = o.idobjeto AND t.idobjeto = a.idloteativ AND t.objeto = 'loteativ')
                 WHERE a.idlote = ?idlote?
                   AND a.execucao IS NOT NULL
                   AND execucaofim IS NOT NULL";
    }
    
    public static function buscarStatusPaiProcessoPorIdLote()
    {
        return "SELECT p.statuspai
                  FROM loteativ la JOIN prativ p ON (p.idprativ = la.idprativ)
                 WHERE la.idlote = ?idlote?
                   AND la.status IN ('PENDENTE', 'PROCESSANDO')
                   AND p.status = 'APROVADO'
              ORDER BY la.ord
                 LIMIT 1";
    }

    public static function inserirAtividade()
    {
        return "INSERT INTO loteativ (idempresa,
                                      idlote,
                                      idprativ,
                                      ativ,
                                      ord,
                                      dia,
                                      loteimpressao, 
                                      statuslote,
                                      nomecurtoativ, 
                                      bloquearstatus,
                                      idetapa, 
                                      idprprocprativ, 
                                      idfluxostatus,
                                      duracao,
                                      tempoestimado,
                                      tempogastoobrigatorio,
                                      criadopor,
                                      criadoem,
                                      alteradopor,
                                      alteradoem)
                              VALUES (?idempresa?,
                                      ?idlote?,
                                      ?idprativ?,
                                      '?ativ?',
                                      ?ord?,
                                      '?dia?',
                                      '?loteimpressao?', 
                                      '?statuslote?',
                                      '?nomecurtoativ?', 
                                      '?bloquearstatus?',
                                      ?idetapa?, 
                                      ?idprprocprativ?, 
                                      ?idfluxostatus?,
                                      '?duracao?',
                                      '?tempoestimado?',
                                      '?tempogastoobrigatorio?',
                                      '?usuario?',
                                      SYSDATE(),
                                      '?usuario?',
                                      SYSDATE())";
    }

    public static function atualizarDataExecucaoAtividade()
    {   
        return "UPDATE loteativ 
                   SET execucao = DATE_ADD('?execucao?', INTERVAL (IFNULL(dia, 1) - 1) DAY), execucaofim = NULL
                 WHERE dia > 0 AND idlote = ?idlote?";
    }

    public static function apagarSalasReserva()
    {
        return "DELETE t.* FROM loteativ a JOIN tagreserva t ON (t.idobjeto = a.idloteativ AND t.objeto = 'loteativ') 
                 WHERE a.idlote = ?idlote?
                   AND (a.execucao IS NULL OR execucaofim IS NULL)";
    }

    public static function apagarAtividadeESalasReserva()
    {
        return "DELETE o.*, t.* 
                  FROM loteativ a JOIN loteobj o ON (o.idloteativ = a.idloteativ AND o.tipoobjeto = 'tag' AND o.idobjeto IS NOT NULL)
                  JOIN prativ p ON (p.idprativ = a.idprativ)
             LEFT JOIN tagreserva t ON (t.idtag = o.idobjeto AND t.idobjeto = a.idloteativ AND t.objeto = 'loteativ') 
                 WHERE a.idlote = ?idlote?
                   AND a.execucao IS NOT NULL";
    }

    public static function buscarIdRegistroTitulacaoPorIdLote()
    {
        return "SELECT a.idregistro
                from loteativ la
                join objetovinculo ov on ov.idobjetovinc = la.idloteativ and ov.tipoobjetovinc= 'loteativ'
                join resultado r on r.idresultado = ov.idobjeto and ov.tipoobjeto = 'resultado'
                join amostra a on a.idamostra = r.idamostra
                join prodserv ps on r.idtipoteste = ps.idprodserv
                where la.idlote = ?idlote?
                and ps.descr like 'titulação%'";
    }

    public static function buscarIdRegistroInativacaoEsterialidadePorIdLote()
    {
        return "SELECT distinct a.idregistro
                from loteativ la
                join objetovinculo ov on ov.idobjetovinc = la.idloteativ and ov.tipoobjetovinc= 'loteativ'
                join resultado r on r.idresultado = ov.idobjeto and ov.tipoobjeto = 'resultado'
                join amostra a on a.idamostra = r.idamostra
                join subtipoamostra sta on a.idsubtipoamostra = sta.idsubtipoamostra
                where la.idlote = ?idlote?
                and sta.idsubtipoamostra = 363";
    }

    public static function atualizarStatusAtividade() {
        return "UPDATE loteativ 
                SET status = '?status?', bloquearstatus = '?bloquearstatus?'
                WHERE idloteativ = ?idloteativ?";
    }

    public static function buscarLoteAtivAtual() {
        return "SELECT la.idloteativ, a.logistica, la.ord, la.status
                from loteativ la
                join prativ a on la.idprativ = a.idprativ
                WHERE la.idlote = ?idlote?
                AND la.status != 'CONCLUIDO'
                ORDER by la.ord asc
                limit 1;";
    }

    public static function atualizarStatusLoteAtiv() {
        return "UPDATE loteativ SET status = '?status?', idfluxostatus = ?idfluxostatus? WHERE idloteativ = ?idloteativ?;";
    }

    public static function buscarCustoTestes() {

        return "SELECt sum(qry.valor) as valor, qry.idlote
                    from (
                            SELECT 
                                round(round(sum(c.qtdd*l.vlrlote),4)  * r.quantidade, 4) as valor, a.idlote
                                FROM loteativ a
                                JOIN objetovinculo o ON o.tipoobjetovinc  = 'loteativ' AND o.idobjetovinc = a.idloteativ
                                JOIN lotecons c ON c.idobjeto=o.idobjeto AND c.tipoobjeto='resultado' AND c.qtdd >0 AND c.status='ABERTO'
                                JOIN lote l ON l.idlote=c.idlote
                                JOIN resultado r ON r.idresultado=c.idobjeto 
                                JOIN amostra am ON am.idamostra=r.idamostra
                                JOIN prodserv p ON p.idprodserv=r.idtipoteste
                                WHERE  a.idlote in (?idlote?)
                                GROUP BY idresultado
                                UNION
                                select
                                  ifnull  (r.custo,0) as valor, at.idlote
                                from loteativ at 
                                join  bioensaio b on(b.idloteativ) =at.idloteativ
                                join analise a on(a.idobjeto = b.idbioensaio AND a.objeto = 'bioensaio')
                                join servicoensaio s on( s.idobjeto = a.idanalise AND s.tipoobjeto ='analise')
                                join resultado r on(r.idservicoensaio = s.idservicoensaio and r.status != 'CANCELADO')
                                join prodserv p on(p.idprodserv = r.idtipoteste)
                                join amostra am on(am.idamostra = r.idamostra)
                                where at.idlote in (?idlote?)
                            ) as qry GROUP BY qry.idlote;";
    }

    public static function alteraStatusLoteAtivPorResultado()
    {
        return "UPDATE loteativ SET status = '?statusloteativ?', alteradopor = '?pessoapost?', alteradoem = NOW() WHERE idloteativ = ?idloteativ?";
    }

    public static function buscaResultadosVinculados()
    {
        return "SELECT COUNT(*) AS resultados
                FROM loteativ la
                        JOIN objetovinculo o ON (o.idobjetovinc = la.idloteativ)
                        JOIN resultado r ON (r.idresultado = o.idobjeto)
                WHERE la.idloteativ = '?idloteativ?'
                AND r.idresultado NOT IN('?idresultado?')
                AND r.status NOT IN ('ASSINADO', 'FECHADO')
                ORDER BY la.idloteativ;";
    }
}
?>