<?
require_once(__DIR__ . '/_iquery.php');

class EventoQuery implements DefaultQuery
{

    public static $table = 'evento';
    public static $pk = 'idevento';

    private const buscarPorChavePrimariaSQLPadrao = "SELECT et.assinar, t.*
                                                    FROM ?table? t
                                                    JOIN eventotipo et ON et.ideventotipo = t.ideventotipo
                                                    WHERE ?pk? in (?pkval?)";

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarPorChavePrimariaPadrao()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQLPadrao, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function inserir()
    {
        return "INSERT INTO evento (
                        ideventotipo, idempresa, ideventopai, idpessoa, idequipamento, 
                        idpessoaev, modulo, idmodulo, idsgsetor, idsgdepartamento, jsonhistorico, 
                        evento, status, idfluxostatus, prazo, jsonconfig, descricao, inicio, iniciohms, 
                        fim, fimhms, duracaohms, periodicidade, repetirevento, repetirate, fimsemana, 
                        resultado, versao, jsonresultado, cor, servico, nomecompleto, complemento, 
                        formaatendimento, classificacao, textocurto1, textocurto2, textocurto3, textocurto4, 
                        textocurto5, textocurto6, textocurto7, textocurto8, textocurto9, textocurto10, textocurto11, textocurto12, textocurto13, textocurto14, textocurto15, prioridade, diainteiro, datainicio, datafim, horainicio, 
                        horafim, criadopor, criadoem, alteradopor, alteradoem, motivo, idsgdoc
            ) VALUES (
                ?ideventotipo?, ?idempresa?, ?ideventopai?, '?idpessoa?', '?idequipamento?', 
                '?idpessoaev?', '?modulo?', ?idmodulo?, ?idsgsetor?, ?idsgdepartamento?, '?jsonhistorico?', 
                '?evento?', '?status?', ?idfluxostatus?, ?prazo?, '?jsonconfig?', '?descricao?', ?inicio?, ?iniciohms?, 
                ?fim?, ?fimhms?, ?duracaohms?, '?periodicidade?', '?repetirevento?', ?repetirate?, '?fimsemana?', 
                '?resultado?', '?versao?', '?jsonresultado?', '?cor?', '?servico?', '?nomecompleto?', '?complemento?', 
                '?formaatendimento?', '?classificacao?', '?textocurto1?', '?textocurto2?', '?textocurto3?', '?textocurto4?', 
                '?textocurto5?', '?textocurto6?', '?textocurto7?', '?textocurto8?', '?textocurto9?', '?textocurto10?', '?textocurto11?', '?textocurto12?', '?textocurto13?', '?textocurto14?', '?textocurto15?'
                , '?prioridade?', '?diainteiro?', ?datainicio?, ?datafim?, ?horainicio?, 
                ?horafim?, '?criadopor?', ?criadoem?, '?alteradopor?', ?alteradoem?, '?motivo?', '?idsgdoc?'
            )";
    }

    public static function criarEventoAlerta()
    {
        return "INSERT INTO evento 
        (	ideventotipo, idempresa, idpessoa, modulo, idmodulo, evento, 
            idfluxostatus, prazo,  descricao, inicio, iniciohms, fim, fimhms,
            criadopor, criadoem, alteradopor, alteradoem
        )
    VALUES 		
        (	'?ideventotipo?', ?idempresa?, '?idpessoa?', '?modulo?', '?idmodulo?', '?titulocurto?', 
            '?idfluxostatus?','?prazo?', '?mensagem?',  DATE_FORMAT(now(),'%Y-%m-%d'), DATE_FORMAT(now(),'%h:%m:%s'), DATE_FORMAT(now(),'%Y-%m-%d'), DATE_FORMAT(now(),'%h:%m:%s'),
            'immsgconf',DATE_FORMAT(now(),'%Y-%m-%d %h:%m:%s'),'immsgconf',DATE_FORMAT(now(),'%Y-%m-%d %h:%m:%s')
        )";
    }

    public static function buscarEventosVinculadosAoModulo()
    {
        return "SELECT * From (SELECT e.idevento, e.evento, e.criadopor, e.prazo, s.rotulo AS status, eventotipo
                    FROM evento e join eventotipo t on t.ideventotipo = e.ideventotipo 
                    JOIN fluxostatus f ON f.idfluxostatus = e.idfluxostatus
                    JOIN " . _DBCARBON . "._status s ON s.idstatus = f.idstatus
                WHERE e.modulo like '%?_modulo?%' and e.idmodulo ='?idpk?' ?and?
                union all
                SELECT e.idevento, e.evento, e.criadopor, e.prazo, s.rotulo AS status, eventotipo
                    from eventoobj o join evento e on (e.idevento = o.idevento)
                    join eventotipo t on t.ideventotipo = e.ideventotipo 
                    JOIN fluxostatus f ON (f.idfluxostatus = e.idfluxostatus)
                    JOIN " . _DBCARBON . "._status s ON s.idstatus = f.idstatus
                    where o.objeto in ('?_modulo?')
                    and o.idobjeto= '?idpk?'
                union all
                SELECT e.idevento, e.evento, e.criadopor, e.prazo, s.rotulo AS status, eventotipo  
                    FROM evento e join eventotipo t on t.ideventotipo = e.ideventotipo 
                    JOIN fluxostatus f ON f.idfluxostatus = e.idfluxostatus
                    JOIN carbonnovo._status s ON s.idstatus = f.idstatus
                WHERE e.idequipamento = '?idpk?') e order by prazo desc";
    }

    public static function buscarEventosPorTipoEClassificacao()
    {
        return "SELECT 
                    e.idevento,
                    e.evento,
                    e.criadopor,
                    e.prazo,
                    s.rotulo AS status,
                    eventotipo
                FROM evento e
                    JOIN eventotipo t ON (t.ideventotipo = e.ideventotipo)
                    JOIN fluxostatus f ON (f.idfluxostatus = e.idfluxostatus)
                    JOIN " . _DBCARBON . "._status s ON (s.idstatus = f.idstatus)
                WHERE
                    e.classificacao = '?classificacao?'
                        AND e.ideventotipo = ?ideventotipo?
                ORDER BY e.idevento DESC";
    }

    public static function inserirComentarioEvento()
    {
        return "INSERT INTO modulocom (idempresa, idmodulo, modulo, descricao, status, criadopor, criadoem, alteradopor, alteradoem)
            VALUES (?idempresa?, ?idevento?, 'evento', '?descricao?', 'ATIVO', '?usuario?', now(), '?usuario?', now())";
    }

    public static function buscarVariaveisDoEvento()
    {
        return "SELECT e.*, 
                    es.rotulo AS status,
                    DATE_FORMAT(e.inicio, '%d/%m/%Y') AS iniciodata,
                    et.cor,
                    esr.statustipo AS posicao,
                    et.sla,
                    et.prazo AS configprazo,
                    et.relacionamento,
                    es.rotulo, 
                    ets.ordem, 
                    ets.fluxoocultar,
                    IF(et.prazo = 'Y', 'show', 'hide') AS mostraprazo,
                    IF(et.prazo = 'Y', 'hide', 'show') AS mostradata,
                    DATE_ADD(e.alteradoem, INTERVAL HOUR(es.sla) HOUR) AS dataslaprazo,
                    ROUND(GREATEST(100 - (FN_WORKTIME(e.criadoem,
                    IF(NOW() > CONCAT(e.prazo, ' 17:00:00'),
                        CONCAT(e.prazo, ' 17:00:00'),
                        NOW())) * 100) / FN_WORKTIME(e.criadoem, CONCAT(e.prazo, ' 17:00:00')), 0),
                    0) AS slaprazo,
                    CONCAT(TIMESTAMPDIFF(DAY, NOW(), IF(et.prazo = 'N', 
                                                CONCAT(e.fim, ' ', e.fimhms),
                                                CONCAT(e.prazo, ' 17:00:00'))), 'd ',
                                                    MOD(TIMESTAMPDIFF(HOUR, NOW(), 
                                                    IF(et.prazo = 'N', 
                                                CONCAT(e.fim, ' ', e.fimhms),
                                                CONCAT(e.prazo, ' 17:00:00'))), 24), 'h ',
                                                    MOD(TIMESTAMPDIFF(MINUTE, NOW(),
                                                    IF(et.prazo = 'N',
                                                CONCAT(e.fim, ' ', e.fimhms),
                                                CONCAT(e.prazo, ' 17:00:00'))), 60),
                        'm ') AS prazorestante,
                    TIME_FORMAT(TIMEDIFF(es.sla,
                        TIME_FORMAT(SEC_TO_TIME((FN_WORKTIME(e.criadoem,
                                IF(((SELECT MIN(r1.alteradoem)
                                        FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                        JOIN eventotipo et ON et.ideventotipo = e1.ideventotipo
                                        JOIN fluxo ms ON ms.idobjeto = et.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
                                        JOIN fluxostatus ets1 ON ets1.idfluxo = ms.idfluxo 
                                        JOIN " . _DBCARBON . "._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                        WHERE r1.idmodulo = e.idevento) IS NOT NULL),
                                    (SELECT MIN(r1.alteradoem)
                                        FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                        JOIN eventotipo et ON et.ideventotipo = e1.ideventotipo
                                        JOIN fluxo ms ON ms.idobjeto = et.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
                                        JOIN fluxostatus ets1 ON ets1.idfluxo = ms.idfluxo 
                                        JOIN " . _DBCARBON . "._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                        WHERE r1.idmodulo = e.idevento), NOW())) * 60)), '%H:%i:%s')), '%H:%i') AS datasla,
                    ROUND(((TIME_TO_SEC(TIME_FORMAT(TIMEDIFF(es.sla,
                    TIME_FORMAT(SEC_TO_TIME((FN_WORKTIME(e.criadoem,
                            IF(((SELECT MIN(r1.alteradoem)
                                    FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                    JOIN eventotipo et ON et.ideventotipo = e1.ideventotipo
                                    JOIN fluxo ms ON ms.idobjeto = et.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
                                    JOIN fluxostatus ets1 ON ets1.idfluxo = ms.idfluxo 
                                    JOIN " . _DBCARBON . "._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                WHERE r1.idmodulo = e.idevento) IS NOT NULL),
                                (SELECT MIN(r1.alteradoem)
                                    FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                    JOIN eventotipo et ON et.ideventotipo = e1.ideventotipo
                                    JOIN fluxo ms ON ms.idobjeto = et.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
                                    JOIN fluxostatus ets1 ON ets1.idfluxo = ms.idfluxo 
                                    JOIN " . _DBCARBON . "._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                    WHERE r1.idmodulo = e.idevento), NOW())) * 60)), '%H:%i:%s')), '%H:%i:%s')) * 100) / TIME_TO_SEC(es.sla)), 0) AS percentual,
                    et.privado
            FROM evento e JOIN eventotipo et ON et.ideventotipo = e.ideventotipo
            JOIN fluxo ms ON ms.idobjeto = et.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
            LEFT JOIN fluxostatus ets ON ets.idfluxo = ms.idfluxo AND ets.idfluxostatus = e.idfluxostatus
            LEFT JOIN fluxostatuspessoa er ON er.idmodulo = e.idevento AND er.modulo = 'evento' AND er.idobjeto = " . $_SESSION["SESSAO"]["IDPESSOA"] . " AND er.tipoobjeto = 'pessoa' 
                AND ets.idfluxostatus = er.idfluxostatus
            LEFT JOIN eventosla es ON (((es.ideventotipo = e.ideventotipo) AND (e.prioridade = es.prioridade) AND (e.servico = es.servico)))
            LEFT JOIN " . _DBCARBON . "._status es ON es.idstatus = ets.idstatus
            LEFT JOIN " . _DBCARBON . "._status esr ON esr.idstatus = ets.idstatus
            WHERE e.idevento = '#pkid'";
    }

    public static function buscarTokenInicialDoEventoPorIdEvento()
    {
        return "SELECT DISTINCT(f.idfluxostatus) AS idfluxostatus
                FROM evento e 
                JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.tipoobjeto = 'ideventotipo' AND ms.status = 'ATIVO'
                JOIN fluxostatus f ON f.idfluxo = ms.idfluxo 
                JOIN " . _DBCARBON . "._status s ON s.idstatus = f.idstatus
                LEFT JOIN " . _DBCARBON . "._statustipo st ON s.statustipo = st.statustipo
                WHERE e.idevento = '?idevento?'
                AND st.statustipo = 'INICIO'";
    }

    public static function atualizarStatusParaLidoPorIdEventoEIdPessoa()
    {
        return "UPDATE fluxostatuspessoa
                SET visualizado='1' 
                WHERE idmodulo = ?idevento?
                AND modulo = 'evento'
                AND tipoobjeto='pessoa'
                AND idobjeto = ?idpessoa?";
    }

    public static function buscarEventosFilhosPorIdEvento()
    {
        return "SELECT e.idevento,
                        e.evento,
                        e.descricao,
                        e.inicio,
                        e.iniciohms,
                        e.fim,
                        e.fimhms,
                        e.prazo,
                        t.tag,
                        t.descricao,
                        e.status
                FROM evento e LEFT JOIN tag t ON(t.idtag=e.idequipamento)
                WHERE e.ideventopai = ?idevento?
                ORDER BY e.inicio";
    }

    public static function buscarEventosFilhosPorIdEventoCount()
    {
        return "SELECT count(e.ideventopai) as `count`
                FROM evento e
                WHERE e.ideventopai = ?idevento?";
    }

    public static function buscarEquipamentosPorIdEvento()
    {
        return "SELECT e.idevento,e.evento,e.descricao,e.inicio,e.iniciohms,e.fim,e.fimhms,e.prazo,t.tag,t.descricao
                FROM evento e 
                LEFT JOIN tag t ON(t.idtag=e.idequipamento)
                WHERE e.idevento = ?idevento?";
    }

    public static function atualizarIdModuloEModuloPorIdEvento()
    {
        return "UPDATE evento
                SET ?setidmodulo? modulo = '?modulo?'
                where idevento = '?idevento?'";
    }

    public static function buscarPessoasVinculadasNoEventoPorIdEvento()
    {
        return "SELECT idfluxostatuspessoa, e.idpessoa, e.idempresa
                FROM evento e 
                LEFT JOIN fluxostatuspessoa r ON e.idevento = r.idmodulo AND r.modulo = 'evento' AND tipoobjeto = 'pessoa'  AND r.idobjeto = e.idpessoa
                WHERE e.idevento = '?idevento?'";
    }

    public static function buscarEventoPorIdEventoEGetidEmpresa()
    {
        return "SELECT * FROM evento WHERE idevento = ?idevento? ?getidempresa?";
    }

    public static function buscarPessoasDeGruposInseridasOuNaoManualmentePorIdEvento()
    {
        return "SELECT DISTINCT NULL AS idfluxostatuspessoa, 
                    e.idempresa as idempresa,
                    gp.idpessoa as idobjeto,
                    gp.idimgrupo as idobjetoext, 
                    'imgrupo' as tipoobjetoext,
                    'N' as inseridomanualmente,
                    e.idpessoa,
                    t.eventotipo,
                    e.evento
                FROM evento e 
                JOIN fluxostatuspessoa r on r.idmodulo = e.idevento AND r.modulo = 'evento' and `r`.`tipoobjeto` = 'imgrupo' 
                JOIN eventotipo t ON t.ideventotipo = e.ideventotipo
                JOIN imgrupopessoa gp on gp.idimgrupo = r.idobjeto
                LEFT JOIN fluxostatuspessoa r2 on r2.idmodulo = e.idevento AND r2.modulo = 'evento' and r2.idobjetoext = r.idobjetoext and r2.idobjeto = gp.idpessoa
                LEFT JOIN pessoa p on p.idpessoa = gp.idpessoa
                WHERE r.idmodulo = '?idevento?' AND r.modulo = 'evento' and r2.idobjeto is null and not gp.idpessoa = e.idpessoa
                UNION
                SELECT DISTINCT NULL as idfluxostatuspessoa,  
                    e.idempresa as idempresa,
                    r.idobjeto as idobjeto,
                    NULL as idobjetoext, 
                    NULL as tipoobjetoext,
                    'Y' as inseridomanualmente,
                    e.idpessoa,
                    t.eventotipo,
                    e.evento
                FROM evento e
                JOIN fluxostatuspessoa r on r.idmodulo = e.idevento AND r.modulo = 'evento' and `r`.`tipoobjeto` = 'pessoa' 
                JOIN eventotipo t ON t.ideventotipo = e.ideventotipo
                WHERE r.idmodulo = '?idevento?' AND r.modulo = 'evento'";
    }

    public static function buscarPessoasDeGruposPorIdEvento()
    {
        return "SELECT DISTINCT NULL AS idfluxostatuspessoa,
                    e.idempresa AS idempresa, 
                    e.idevento AS idmodulo, 
                    gp.idpessoa AS idobjeto, 
                    gp.idimgrupo as idobjetoext,
                    e.idpessoa,
                    t.eventotipo,
                    e.evento
                FROM evento e 
                JOIN fluxostatuspessoa r on r.idmodulo = e.idevento AND r.modulo = 'evento' AND `r`.`tipoobjeto` = 'imgrupo' 
                JOIN eventotipo t ON t.ideventotipo = e.ideventotipo
                JOIN imgrupopessoa gp on gp.idimgrupo = r.idobjeto
                LEFT JOIN fluxostatuspessoa r2 on r2.idmodulo = e.idevento AND r2.modulo = 'evento' AND r2.idobjetoext = r.idobjetoext AND r2.idobjeto = gp.idpessoa
                LEFT JOIN pessoa p on p.idpessoa = gp.idpessoa
                WHERE r.idmodulo = '?idevento?' and r2.idobjeto is null and not gp.idpessoa = e.idpessoa";
    }

    public static function atualizarPrazoInicioEFIm()
    {
        return "UPDATE evento SET prazo = ?prazo?, inicio = ?inicio?, fim = ?fim? WHERE idevento = ?idevento?";
    }

    public static function atualizarStatusDoEvento()
    {
        return "UPDATE evento SET status = '?status?' WHERE idevento = ?idevento? ";
    }

    public static function atualizarStatusFluxostatusDoEvento()
    {
        return "UPDATE evento SET status = '?status?', idfluxostatus = ?idfluxostatus?, alteradoem = now(), alteradopor = '?alteradopor?' where idevento = ?idevento? ";
    }

    public static function atualizarInicioEFim()
    {
        return "UPDATE evento
                SET inicio = '?inicio?', iniciohms = '?iniciohms?', fim = '?fim?', fimhms = '?fimhms?',  alteradopor = '?alteradopor?',  alteradoem = now() 
                where idevento = '?idevento?'";
    }

    public static function atualizarPrazoPorIdEvento()
    {
        return "UPDATE evento 
                SET prazo = '?prazo?', alteradopor = '?alteradopor?',  alteradoem = now()
                WHERE idevento = '?idevento?'";
    }

    public static function atualizarRepetirAte()
    {
        return "UPDATE evento SET repetirate = ?repetirate?, repetirevento = '?repetirevento?' WHERE idevento = ?idevento?;";
    }

    public static function buscarEventosFilhosPorDataInicio()
    {
        return "SELECT idevento
                FROM evento e
                WHERE ideventopai = ?idevento?
                AND inicio = '?datainicio?'";
    }

    public static function atualizarEventosFilhos()
    {
        return "UPDATE evento
                SET
                    evento 		= '?evento?',
                    inicio 		= '?inicio?',
                    prazo 		= '?prazo?',
                    iniciohms 	= '?iniciohms?',
                    fim 		= '?fim?',
                    fimhms 		= '?fimhms?'
                WHERE idevento in (?ideventofilho?)";
    }

    public static function buscarEventosAdicionais()
    {
        return "SELECT pr.* 
                FROM evento f 
                JOIN eventoobj pr on f.ideventopai = pr.idevento
                WHERE f.idevento = ?idevento?
                AND NOT EXISTS (
                    SELECT 1 
                    FROM eventoobj fr 
                    WHERE fr.idevento = f.idevento 
                    AND fr.idobjeto = pr.idobjeto 
                    AND fr.objeto = pr.objeto
                )";
    }

    public static function buscarEventosAdicionaisDeEventosFilhos()
    {
        return "SELECT fr.* 
                FROM evento f 
                JOIN eventoobj fr on f.idevento=fr.idevento
                WHERE f.idevento = ?idevento?
                AND NOT EXISTS (
                    SELECT 1
                    FROM eventoobj pr 
                    WHERE pr.idevento = f.ideventopai 
                    AND fr.idobjeto = pr.idobjeto 
                    AND fr.objeto = pr.objeto
                )";
    }

    public static function buscarPessoasResponsaveis()
    {
        return "SELECT 
                    f.idpessoa, 
                    pr.idempresa, 
                    pr.idobjeto,
                    pr.tipoobjeto,
                    pr.status,
                    pr.idfluxostatus,
                    pr.oculto,
                    pr.idobjetoext,
                    pr.tipoobjetoext,
                    pr.inseridomanualmente,
                    pr.visualizado,
                    pr.assinar,
                    pr.editar
                FROM evento f 
                JOIN fluxostatuspessoa pr on f.ideventopai = pr.idmodulo AND pr.modulo = 'evento'
                WHERE f.idevento = ?idevento?
                AND NOT EXISTS (
                    SELECT 1 FROM fluxostatuspessoa fr 
                    WHERE fr.idmodulo=f.idevento 
                    AND fr.idobjeto=pr.idobjeto 
                    AND fr.tipoobjeto=pr.tipoobjeto
                )";
    }

    public static function buscarEventosFilhosSemStatusPorIdEvento()
    {
        return "SELECT * 
                FROM evento 
                WHERE (status is null OR status = '')
                AND ideventopai = ?idevento?";
    }

    public static function verificarDiaDaSemana()
    {
        return "SELECT DAYOFWEEK('?data?') as dia";
    }

    public static function deletarEventosPorRangeDeDataEIdEventoPai()
    {
        return "DELETE FROM evento 
                WHERE (status is null OR status = '')
                AND ideventopai = ?ideventopai?
                AND (inicio < '?inicio?' or fim > '?fim?')";
    }

    public static function deletarEventosForaDoRangeDeDataEIdEventoPai()
    {
        return "DELETE FROM evento 
                WHERE (status is null OR status = '')
                AND ideventopai = ?ideventopai?
                AND (inicio > '?inicio?' or fim < '?fim?')";
    }

    public static function buscarEventosPorIdEventoTipoIdPessoaEData()
    {
        return "SELECT e.idevento as idevento, 
                        'EVENTO' as tipo,
                        '' as idobjeto,
                        '' as objeto,
                        e.diainteiro,
                        CONCAT(e.idevento, '</br>', (
                            IF(et.ideventotipo = 14, CONCAT(UPPER(p.nomecurto), '<br/>'), '')
                        ), UPPER(e.evento)) as evento,
                        e.evento as eventooriginal,
                        e.inicio 	as 	inicio, 
                        e.iniciohms as 	iniciohms, 
                        e.datainicio as datainicio,
                        e.datafim as datafim,
                        e.fim as 	fim, 
                        e.fimhms 	as 	fimhms,
                        e.jsonconfig	as 	jsonconfig,
                        e.ideventotipo,
                        IF(s.statustipo in ('FIM', 'CANCELADO', 'CONCLUIDO'), '#666', et.cor) AS cor
                FROM evento as 	e JOIN	eventotipo 	as 	et 	ON e.ideventotipo = et.ideventotipo
                JOIN pessoa p ON(p.idpessoa = e.idpessoa)
                JOIN fluxostatuspessoa	as er ON er.idmodulo = e.idevento AND er.modulo = 'evento'
                JOIN fluxo ms ON ms.idobjeto = et.ideventotipo AND ms.modulo = 'evento'
                JOIN fluxostatus ets ON ets.idfluxostatus = e.idfluxostatus AND ets.idfluxo = ms.idfluxo
                JOIN " . _DBCARBON . "._status s ON ets.idstatus = s.idstatus
                WHERE not ets.idstatus  in (6,8,9)
                AND (
                    e.inicio >= '?dataInicio?' AND e.fim <= '?dataFim?'
                    OR e.datainicio >= '?dataInicio?' AND e.datafim <= '?dataFim?'
                )
                AND er.tipoobjeto = 'pessoa'
                AND er.idobjeto =	?idpessoa?
                AND e.ideventotipo in 	(?ideventotipo?)
                AND e.ideventopai 	!= 	''
                AND e.idevento 		= 	er.idmodulo and er.modulo = 'evento'
                AND (s.statustipo != 'CANCELADO' OR s.statustipo is null)
                UNION
                SELECT e.idevento as idevento, 
                        'EVENTO' as tipo,
                        '' as idobjeto,
                        '' as objeto,
                        e.diainteiro,
                        CONCAT(e.idevento, '</br>', (
                            IF(et.ideventotipo = 14, CONCAT(UPPER(p.nomecurto), '<br/>'), '')
                        ), UPPER(e.evento)) as evento,
                        e.evento as eventooriginal,
                        e.inicio 	as 	inicio, 
                        e.iniciohms as 	iniciohms,
                        e.datainicio as datainicio,
                        e.datafim as datafim,
                        e.fim as 	fim, 
                        e.fimhms 	as 	fimhms,
                        e.jsonconfig	as 	jsonconfig,
                        e.ideventotipo,
                        IF(s.statustipo in ('FIM', 'CANCELADO', 'CONCLUIDO'), '#666', et.cor) AS cor
                FROM evento as 	e JOIN	eventotipo 	as 	et 	ON e.ideventotipo = et.ideventotipo
                JOIN pessoa p ON(p.idpessoa = e.idpessoa)
                JOIN fluxostatuspessoa	as er ON er.idmodulo = e.idevento AND er.modulo = 'evento'
                JOIN fluxo ms ON ms.idobjeto = et.ideventotipo AND ms.modulo = 'evento'
                JOIN fluxostatus ets ON ets.idfluxostatus = e.idfluxostatus AND ets.idfluxo = ms.idfluxo
                JOIN " . _DBCARBON . "._status s ON ets.idstatus = s.idstatus
                WHERE not ets.idstatus  in (6,8,9)
                AND (
                    e.inicio >= '?dataInicio?' AND e.fim <= '?dataFim?'
                    OR e.datainicio >= '?dataInicio?' AND e.datafim <= '?dataFim?'
                )
                AND er.tipoobjeto = 'pessoa'
                AND er.idobjeto =	?idpessoa?
                AND e.ideventotipo in 	(?ideventotipo?)
                AND e.ideventopai 	is 	null
                AND e.repetirate	is 	null
                AND e.idevento 		= 	er.idmodulo and er.modulo = 'evento'
                AND (s.statustipo != 'CANCELADO' OR s.statustipo is null)";
    }

    public static function inserirIdSgdocEmEvento()
    {
        return "UPDATE evento SET idsgdoc = ?idsgdoc? WHERE idevento = ?idevento?;";
    }

    public static function buscarQuantEventos()
    {
        return "SELECT count(e.idevento) AS quantidade
                FROM evento e
                join eventotipo et on et.ideventotipo = e.ideventotipo
                ?filtrominievento?
                WHERE et.dashboard = 'Y'
                AND e.idevento IN (
                    SELECT er.idmodulo 
                    FROM fluxostatuspessoa er
                    WHERE er.idobjeto = ?idpessoa?
                    AND er.tipoobjeto = 'pessoa'
                    ?filtrodetarefas?
                    AND er.idmodulo = e.idevento 
                    AND er.modulo = 'evento'
                    AND IF (e.ideventopai, e.inicio <= date_format(now(), '%Y-%m-%d'), 1) = 1 
                    AND repetirate is null 
                    ?filtrodeocultos?
                )";
    }

    public static function buscarListaDeEventos()
    {
        return "SELECT
                    IFNULL(p.nomecurto,p.nome) AS nomecurto,
                    e.modulo,
                    e.idmodulo,
                    e.idevento,
                    e.idpessoa,
                    e.evento,
                    es.rotulo AS STATUS, 
                    DATE_FORMAT(e.inicio, '%d/%m/%Y') AS iniciodata,
                    e.inicio,
                    e.iniciohms,
                    e.fim,
                    e.fimhms,
                    e.criadoem,
                    e.prazo,
                    e.modulo,
                    e.idmodulo,
                    et.eventotipo,
                    et.cor,
                    et.sla,
                    er.visualizado,
                    e.descricao,
                    et.ideventotipo, 
                    es.statustipo AS posicaofim,
                    CONCAT(DATE_FORMAT(e.criadoem, '%m/%d/%Y %H:%i'),' - ',if(et.anonimo = 'Y','<b><i>ANÔNIMO</i></b>',p.nomecurto)) as criadoempor,
                    CONCAT(DATE_FORMAT(e.criadoem, '%m/%d/%Y %H:%i'),' - ',p.nomecurto) AS criadoempor, CONCAT(DATE_FORMAT(e.criadoem, '%m/%d/%Y %H:%i'),' - ',p.nomecurto) AS alteradoempor,
                    es.cor AS corstatus,
                    es.cortexto AS cortextostatus,
                    es.rotulo,
                    es.cor AS corstatusresp,
                    es.rotuloresp AS rotuloresp, 
                    ROUND(GREATEST(100-(fn_worktime(e.criadoem, if (NOW()> CONCAT(e.prazo,' 17:00:00'), CONCAT(e.prazo,' 17:00:00'), NOW()))*100)/ fn_worktime(e.criadoem, CONCAT(e.prazo,' 17:00:00')), 0),0) AS slaprazo,
                    es.statustipo AS posicao,
                    et.prazo AS configprazo,
                    if(et.prazo = 'Y', 'show', 'hide') AS mostraprazo,
                    if(et.prazo = 'Y', 'hide', 'show') AS mostradata,
                    et.travasala,
                    e.diainteiro,
                    e.duracaohms,
                    e.idequipamento,
                    er.alteradoem, 
                    DATE_ADD(er.alteradoem, INTERVAL HOUR(esla.sla) HOUR) AS dataslaprazo, 
                    TIME_FORMAT(TIMEDIFF(esla.sla,
                        TIME_FORMAT(SEC_TO_TIME((FN_WORKTIME(e.criadoem,
                            IF(((SELECT MIN(r1.alteradoem)
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                JOIN " . _DBCARBON . "._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                WHERE r1.idmodulo = e.idevento) IS NOT NULL),
                                (SELECT MIN(r1.alteradoem) 
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                JOIN " . _DBCARBON . "._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                WHERE r1.idmodulo = e.idevento), 
                        NOW())))), '%H:%i:%s')),
                    '%H:%i') AS datasla,
                    ROUND(((TIME_TO_SEC(TIME_FORMAT(TIMEDIFF(esla.sla,
                        TIME_FORMAT(SEC_TO_TIME((FN_WORKTIME(e.criadoem,
                            IF(((SELECT MIN(r1.alteradoem)
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                WHERE r1.idmodulo = e.idevento) IS NOT NULL),
                                (SELECT MIN(r1.alteradoem)
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                JOIN " . _DBCARBON . "._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                WHERE r1.idmodulo = e.idevento),
                        NOW())) * 60)), '%H:%i:%s')),
                    '%H:%i:%s')) * 100) / TIME_TO_SEC(esla.sla)), 0) AS percentual,	
                    CONCAT(TIMESTAMPDIFF(day,now(),if(et.prazo = 'N', CONCAT(e.fim, ' ',e.fimhms), CONCAT(e.prazo, ' 17:00:00'))) , 'd ',
                            MOD( TIMESTAMPDIFF(hour,now(),if(et.prazo = 'N', CONCAT(e.fim, ' ',e.fimhms), CONCAT(e.prazo, ' 17:00:00'))), 24), 'h ',
                            MOD( TIMESTAMPDIFF(minute,now(),if(et.prazo = 'N', CONCAT(e.fim, ' ',e.fimhms), CONCAT(e.prazo, ' 17:00:00'))), 60), 'm ') as prazorestante,
                    TIME_FORMAT(TIMEDIFF(esla.sla,
                        TIME_FORMAT(SEC_TO_TIME((FN_WORKTIME(e.criadoem,
                            IF(((SELECT MIN(r1.alteradoem)
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                JOIN " . _DBCARBON . "._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                WHERE r1.idmodulo = e.idevento) IS NOT NULL),
                                (SELECT MIN(r1.alteradoem)
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                JOIN " . _DBCARBON . "._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                WHERE r1.idmodulo = e.idevento),
                        NOW())) * 60)), '%H:%i:%s')),
                    '%H:%i:%s') AS temporeal,
                    TIME_FORMAT(TIMEDIFF(esla.sla,
                        TIME_FORMAT(SEC_TO_TIME((FN_WORKTIME(e.criadoem,
                            IF(((SELECT MIN(r1.alteradoem)
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                JOIN " . _DBCARBON . "._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                WHERE r1.idmodulo = e.idevento) IS NOT NULL),
                                (SELECT MIN(r1.alteradoem) 
                                FROM fluxostatuspessoa r1 JOIN evento e1 ON e1.idevento = r1.idmodulo AND r1.modulo = 'evento'
                                JOIN fluxo ms1 ON ms1.idobjeto = e1.ideventotipo AND ms1.modulo = 'evento'
                                JOIN fluxostatus ets1 ON ets1.idfluxo = ms1.idfluxo 
                                JOIN " . _DBCARBON . "._status esr ON ets1.idstatus = esr.idstatus AND esr.statustipo IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                                WHERE r1.idmodulo = e.idevento), 
                        NOW())) * 60)), '%H:%i:%s')),
                    '%H:%i') AS slaprazo,
                    er.oculto,
                    et.anonimo
            FROM evento e JOIN pessoa p on p.idpessoa = e.idpessoa
            JOIN eventotipo et on et.ideventotipo = e.ideventotipo
            JOIN fluxostatuspessoa er on er.idmodulo = e.idevento AND er.modulo = 'evento'
            LEFT JOIN fluxostatus ets on ets.idfluxostatus = e.idfluxostatus
            LEFT JOIN " . _DBCARBON . "._status es on es.idstatus = ets.idstatus
            LEFT JOIN eventosla esla ON esla.ideventotipo = e.ideventotipo AND e.prioridade = esla.prioridade AND e.servico = esla.servico
            ?filtrominievento?
            WHERE et.dashboard = 'Y'
                AND er.idobjeto = ?idpessoa?
                AND er.tipoobjeto = 'pessoa'
                AND IF (e.ideventopai, e.inicio <= date_format(now(), '%Y-%m-%d'), 1) = 1
                AND repetirate is null
                ?filtrodetarefa?
                ?filtrodeocultos?
                ?filtrodeeventotipo?
                ?ordem?
                ?carregamentoassincrono?";
    }

    public static function buscarBotoes()
    {
        return "SELECT et.ocultar,
                    r.idfluxostatuspessoa, 
                    e.idfluxostatus AS idstatus, 
                    ese.cor AS corstatus, 
                    ese.cortexto AS cortextostatus, 
                    ese.rotulo,
                    esr.idstatus AS idstatusresp, 
                    esr.cor AS corstatusresp, 
                    esr.rotuloresp AS rotuloresp,
                    es.botao, 
                    es.cor, 
                    es.cortexto, 
                    es.idstatus AS idstatusf,
                    e.idevento,
                    et.botaocriador AS botaocriador,
                    et.botaoparticipante AS botaoparticipante,
                    e.criadopor
                    ?colunas?
                FROM evento e
                    JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.modulo = 'evento' AND ms.status = 'ATIVO'
                    JOIN fluxostatus ete ON(ete.idfluxostatus = e.idfluxostatus AND ete.idfluxo = ms.idfluxo)
                    JOIN " . _DBCARBON . "._status ese ON ete.idstatus = ese.idstatus
                    JOIN fluxostatuspessoa r ON e.idevento = r.idmodulo AND r.modulo = 'evento'
                    JOIN " . _DBCARBON . "._status esr ON ete.idstatus = esr.idstatus
                    JOIN fluxostatus et ON(et.idstatus = ete.idstatus AND et.idfluxo = ms.idfluxo)
                LEFT JOIN " . _DBCARBON . "._status es ON FIND_IN_SET(es.idstatus, et.fluxo) 
                LEFT JOIN fluxostatus etsf ON(etsf.idstatus = ete.idstatus AND etsf.idfluxo = ms.idfluxo)
                AND NOT EXISTS (SELECT 1 FROM " . _DBCARBON . "._status ese2
                            WHERE ese2.idstatus = es.idstatus AND FIND_IN_SET(ese2.idstatus, ete.fluxoocultar))
                    WHERE ?where?
                ORDER BY es.rotuloresp;";
    }

    public static function buscarEventosQueNaoEstejamOcultosPorIdEventoEIdPessoa()
    {
        return "SELECT p.nomecurto, 
                    e.idevento, 
                    e.idpessoa,
                    e.evento,
                    e.status, 
                    e.inicio,
                    e.iniciohms,
                    e.fim,
                    e.fimhms,
                    e.criadoem,
                    e.prazo,		
                    e.modulo,
                    e.idmodulo,
                    et.eventotipo
                FROM evento e, pessoa p, eventotipo et
                WHERE e.idevento = ?idevento?
                AND p.idpessoa = e.idpessoa
                AND e.ideventotipo = et.ideventotipo
                AND EXISTS (
                    SELECT 1
                    FROM fluxostatuspessoa er
                    WHERE er.idobjeto = ?idpessoa?
                    AND er.tipoobjeto = 'pessoa'
                    AND er.idmodulo = e.idevento
                    AND modulo = 'evento'
                    AND er.oculto != 1
                ) ORDER BY e.prazo asc";
    }

    public static function buscarPessoasDoEventoPaiQueNaoEstejamNoEventoAtual()
    {
        return "SELECT pr.*
                FROM evento f 
                JOIN  fluxostatuspessoa pr ON f.ideventopai = pr.idmodulo AND pr.modulo = 'evento'
                WHERE f.idevento = '?idevento?'
                AND NOT EXISTS(
                    SELECT 1 
                    FROM fluxostatuspessoa fr 
                    WHERE fr.idmodulo = f.idevento 
                    AND pr.modulo = 'evento' 
                    AND fr.idobjeto = pr.idobjeto 
                    AND fr.tipoobjeto = pr.tipoobjeto
                );";
    }

    public static function buscarResponsaveisPelosEventosFilhos()
    {
        return "SELECT fr.idfluxostatuspessoa
                FROM evento f 
                JOIN fluxostatuspessoa fr ON fr.idmodulo = f.idevento AND fr.modulo = 'evento'
                WHERE f.idevento = '?ideventofilho?' 
                AND NOT EXISTS (
                    SELECT 1 
                    FROM fluxostatuspessoa pr 
                    WHERE pr.idmodulo = f.ideventopai 
                    AND pr.modulo = 'evento' 
                    AND pr.idobjeto = fr.idobjeto 
                    AND fr.tipoobjeto = pr.tipoobjeto
                )";
    }

    public static function buscarEventoFilhoComEventoAddPorIdEventoPai()
    {
        return "SELECT e.idevento 
                FROM evento e 
                JOIN eventoadd ea ON e.ideventopai = ea.idevento
                WHERE ideventopai = ?ideventopai?
                GROUP BY e.idevento;";
    }

    public static function buscarStatusInicialDoEvento()
    {
        return "SELECT 
                    fs.idfluxostatus,
                    e.idempresa		
                FROM evento e 
                JOIN fluxo f ON  f.idobjeto = e.ideventotipo AND f.tipoobjeto = 'ideventotipo' AND f.status = 'ATIVO'
                JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo
                JOIN  " . _DBCARBON . "._status s ON s.idstatus = fs.idstatus
                WHERE e.idevento = ?idevento?
                AND s.statustipo = 'INICIO'";
    }

    public static function buscarEventosQueDevemSerOcultados()
    {
        return 'SELECT CONCAT("
                    UPDATE fluxostatuspessoa
                    SET oculto = 1
                    WHERE idfluxostatuspessoa = ", fp.idfluxostatuspessoa,";
                ") as "update"
                FROM evento e
                JOIN fluxostatuspessoa fp on(fp.idmodulo = e.idevento and fp.modulo = "evento")
                JOIN fluxostatus fs on(fs.idfluxostatus = fp.idfluxostatus)
                JOIN carbonnovo._status cs on(cs.idstatus = fs.idstatus)
                WHERE (fs.fluxo is null or fs.fluxo = "")
                AND fp.oculto = 0
                AND fs.ocultar = "Y"';
    }

    public static function buscarStatusDaPessoaDeUmEvento()
    {
        return "SELECT s.rotulo,s.idstatus,e.idevento
                FROM evento e
                JOIN fluxostatuspessoa r on e.idevento = r.idevento
                JOIN fluxo ms ON e.ideventotipo = ms.idobjeto AND ms.modulo = 'evento'
                JOIN fluxostatus es ON es.idstatus = r.idstatus and es.idfluxo = ms.idfluxo
                JOIN " . _DBCARBON . "._status s ON s.idstatus = es.idstatus
                WHERE r.idfluxostatuspessoa = ?idfluxostatuspessoa?
                AND r.tipoobjeto ='pessoa' 
                ORDER BY es.ordem  desc limit 1";
    }

    public static function atualizarIdStatusPorIdEvento()
    {
        return "UPDATE evento 
                SET idstatus = '?idstatus?'
                WHERE idevento = ?idevento?";
    }

    public static function buscarBotoesFluxoEvento()
    {
        return "SELECT fsb.ocultar,
                    r.idfluxostatuspessoa,
                    r.idfluxostatus,
                    s.cor,
                    s.botao,
                    s.cortexto,    
                    s.statustipo,
                    s.tipobotao,                                  
                    e.idevento,
                    fsb.botaocriador,
                    fsb.botaoparticipante,
                    fsb.idfluxostatus AS idfluxostatusf,
                    fh.idfluxostatushist,
                    fsb.ordem,
                    ete.idfluxo,
                    e.criadopor
            FROM evento e
                JOIN fluxostatus ete ON (ete.idfluxostatus = e.idfluxostatus)
                JOIN fluxostatuspessoa r ON e.idevento = r.idmodulo 
                    AND r.modulo = 'evento'
                LEFT JOIN fluxostatushist fh ON fh.idfluxostatus = r.idfluxostatus 
                    AND fh.status = 'PENDENTE' 
                    AND fh.idmodulo = e.idevento 
                    AND fh.modulo = 'evento'
                JOIN fluxostatus fsr ON (fsr.idfluxostatus = r.idfluxostatus)
                JOIN fluxostatus fsb ON FIND_IN_SET(fsb.idfluxostatus, fsr.fluxo) 
                LEFT JOIN fluxostatuslp fl ON fl.idfluxostatus = ete.idfluxostatus 
                    AND fl.idlp in (?lps?)
                JOIN "._DBCARBON."._status s ON s.idstatus = fsb.idstatus 
                AND NOT EXISTS (SELECT 1 
                    FROM fluxostatus fs2 
                    WHERE fs2.idfluxostatus = fsb.idfluxostatus 
                        AND FIND_IN_SET (fs2.idfluxostatus, ete.fluxoocultar)
                )
            WHERE r.idmodulo = ?idobjeto? 
                AND r.idobjeto = ?idpessoa?
                AND r.tipoobjeto = 'pessoa' 
            ORDER BY fsb.ordem";
    }

    public static function buscarBotoesFluxoEventoFluxoFixo()
    {
        return "SELECT fsb.ocultar,
                    r.idfluxostatuspessoa,
                    r.idfluxostatus,
                    s.cor,
                    s.botao,
                    s.cortexto,    
                    s.statustipo,
                    s.tipobotao,                                  
                    e.idevento,
                    fsb.botaocriador,
                    fsb.botaoparticipante,
                    fsb.idfluxostatus AS idfluxostatusf,
                    fh.idfluxostatushist,
                    fsb.ordem,
                    ete.idfluxo,
                    e.criadopor
            FROM evento e
                JOIN fluxostatus ete ON (ete.idfluxostatus = e.idfluxostatus)
                JOIN fluxostatuspessoa r ON e.idevento = r.idmodulo AND r.modulo = 'evento' AND idobjeto = 98070 AND tipoobjeto = 'pessoa'
                LEFT JOIN fluxostatushist fh ON fh.idfluxostatus = r.idfluxostatus 
                    AND fh.status = 'PENDENTE' 
                    AND fh.idmodulo = e.idevento 
                    AND fh.modulo = 'evento'
                JOIN fluxostatus fsr ON (fsr.idfluxostatus = r.idfluxostatus)
                JOIN fluxostatus fsb ON FIND_IN_SET(fsb.idfluxostatus, fsr.fluxo) 
                LEFT JOIN fluxostatuslp fl ON fl.idfluxostatus = ete.idfluxostatus 
                    AND fl.idlp in (?lps?)
                JOIN "._DBCARBON."._status s ON s.idstatus = fsb.idstatus 
                AND NOT EXISTS (SELECT 1 
                    FROM fluxostatus fs2 
                    WHERE fs2.idfluxostatus = fsb.idfluxostatus 
                        AND FIND_IN_SET (fs2.idfluxostatus, ete.fluxoocultar)
                )
            WHERE e.idevento = ?idobjeto? 
            ORDER BY fsb.ordem";
    }

    public static function buscarDashboardTi()
    {
        return "SELECT
                    'dashti' as panel_id,
                    'col-md-2' as panel_class_col,
                    'SUPORTE TI' as panel_title,
                    'dashticorrecao' as card_id,
                    'col-md-12 col-sm-12 col-xs-6' as card_class_col, 
                    concat('_modulo=evento&_pagina=0&_ordcol=idevento&_orddir=desc&_filtrosrapidos={%22idstatus%22:%223,2,8,38,29,7%22,%22ideventotipo%22:%2228,53%22}') as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    if (count(1) > 0,'danger','success') as card_color,
                    if (count(1) > 0,'danger','success') as card_border_color,
                    '' as card_bg_class,
                    'correção' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'SUPORTE TI - CORREÇÃO' as card_title_modal,
                    '_modulo=evento&_acao=u' as card_url_modal
                FROM evento `e`
                JOIN " . _DBCARBON . "._status `est` ON ((`est`.`idstatus` = `e`.`idstatus`))
                WHERE `e`.`idstatus`  in (3,2,8,38,29,7) and e.ideventotipo in (28,53)";
    }

    public static function buscarFiguraCabecalhoRelatorio()
    {
        return "SELECT 
                    idevento, e.prazo, evento, eventotipo, dma(e.criadoem) as criadoem, er.idfluxostatuspessoa
                From evento e
                JOIN `eventotipo` `et` ON ((`et`.`ideventotipo` = `e`.`ideventotipo`))
                JOIN `fluxostatuspessoa` `er` ON (((`er`.`idmodulo` = `e`.`idevento`)
                AND (`er`.`modulo` = 'evento')
                AND (`er`.`tipoobjeto` = 'pessoa')))
                WHERE e.ideventotipo = 17 
                and er.idobjeto = ?idobjeto?
                and oculto = 0
                ORDER BY e.criadoem asc";
    }

    public static function removerEventoFilhosSemStatus()
    {
        return "DELETE FROM evento 
                WHERE (status is null OR status = '')
                AND ideventopai = ?idevento?";
    }

    public static function buscarLinksVinculados()
    {
        return  "SELECT el.ideventolink, el.link, el.titulo
                FROM eventolink el
                WHERE el.idevento = ?idevento?
                ORDER BY el.link";
    }

    public static function buscarEventoApontamento()
    {
        return "SELECT dmahms(a.criadoem) as criadoem,
                       ifnull(p.nomecurto, p.nome) as nome,
                       a.valor,
                       a.valordecimal,
                       a.descr,
                       a.ideventoapontamento,
                       a.criadopor,
                       r.descricao
                  FROM eventoapontamento a JOIN pessoa p ON(p.usuario = a.criadopor)
             LEFT JOIN eventorelacionamento r ON r.ideventorelacionamento = a.ideventorelacionamento
                 WHERE a.status = 'ATIVO'
                   AND a.idevento = ?idevento? 
              ORDER BY a.criadoem DESC;";
    }

    public static function buscarEventosDestaque()
    {
        return 'SELECT e.idevento, et.cor, e.evento, et.eventotipo, e.criadoem
        FROM eventotipo et 
        JOIN evento e on et.ideventotipo = e.ideventotipo
        JOIN fluxostatus fs on fs.idfluxostatus = e.idfluxostatus
        JOIN fluxostatuspessoa fp on fp.idmodulo = e.idevento and fp.modulo = "evento" and fp.idobjeto = ?idpessoa? and fp.tipoobjeto = "pessoa"
        WHERE et.destaque = "Y"
        AND fp.oculto = 0
        ORDER BY e.criadoem DESC';
    }


    public static function inserirHorasExec()
    {
        return "UPDATE evento
                    SET horasexec = '?horasexec?'
                    WHERE idevento = ?idevento?";
    }

    public static function buscarValorEventoApontamento()
    {
        return "SELECT TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(valor))), '%H:%i:%s') AS valor,
        SUM(valordecimal) AS valordecimal
        FROM eventoapontamento where idevento = ?idevento?
        AND status = 'ATIVO'";
    }

    public static function buscarValorDecimal()
    {
        return "SELECT 
        horasexec,
        ROUND(CAST(SUBSTRING_INDEX(horasexec, ':', 1) AS UNSIGNED) + (CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(horasexec, ':', 2),':',- 1)AS UNSIGNED) / 60),2) AS valordecimal
    FROM
        evento
    WHERE
        idevento = ?idevento?";
    }

    public static function buscarEventosPorIdEventoParaKaban()
    {
        return "SELECT e.sigla,
                        e.idevento,
                        e.criadoem,
                        e.evento,
                        e.cliente,
                        e.urgencia,
                        e.criadopor,
                        e.setor,
                        e.modulo,
                        e.classificacao,
                        e.responsavel,
                        e.bonificado,
                        e.previsao,
                        e.registrado,
                        e.dmadatainicio,
                        e.dmadatafim,
                        e.vencido,
                        IFNULL(r1.visualizado,'N') as viu,
                        e.idfluxostatus,
                        e.fluxostatus,
                        e.ordem,
                        (select DATEDIFF(CURDATE(), f.criadoem) AS dias_no_status 
                            from evento ev 
                            join fluxostatushist f on f.idmodulo = ev.idevento
                                and f.modulo = 'evento' AND ev.idfluxostatus = f.idfluxostatus
                            where ev.idevento = e.idevento and f.idfluxostatuspessoa is null order by f.criadoem desc limit 1) as diasnostatus
                    FROM vwsuportetecnologia e
                        LEFT JOIN fluxostatuspessoa r1 ON (e.idevento = r1.idmodulo AND r1.modulo = 'evento' AND r1.idobjeto = ?idpessoa? AND r1.tipoobjeto = 'pessoa' and r1.visualizado =0 )
                    WHERE e.ideventotipo = 28
                    ?filtros?
                    GROUP BY e.idevento
                    ORDER BY e.ordem";
    }

    public static function UpdateEventoOrder()
    {
        return "UPDATE evento
                SET ordem = ?ordem?
                WHERE idevento = ?idevento?";
    }
}