<?
require_once(__DIR__."/_iquery.php");


class FluxostatuspessoaQuery implements DefaultQuery{
    public static $table = "fluxostatuspessoa";
    public static $pk = 'idfluxostatuspessoa';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function inserir()
    {
        return "INSERT INTO fluxostatuspessoa (
                    idpessoa, idempresa, idmodulo, modulo, idobjeto, 
                    tipoobjeto, status, idfluxostatus, oculto, 
                    inseridomanualmente, visualizado, assinar, 
                    editar, criadopor, criadoem, alteradopor, alteradoem, ?campos?
                ) 
                VALUES (
                    ?idpessoa?, ?idempresa?, ?idmodulo?, '?modulo?', ?idobjeto?, 
                    '?tipoobjeto?', '?status?', ?idfluxostatus?, ?oculto?,
                    '?inseridomanualmente?', ?visualizado?, '?assinar?',
                    '?editar?', '?criadopor?', ?criadoem?, '?alteradopor?', ?alteradoem?, ?valores?
                )";
    }

    public static function inserirNoEventoAlerta()
    {
        return "INSERT INTO fluxostatuspessoa  
                            (idmodulo, 
                            modulo, 
                            idpessoa, 
                            idempresa, 
                            idobjeto, 
                            tipoobjeto, 
                            idfluxostatus, 
                            oculto, 
                            inseridomanualmente, 
                            criadopor,
                            criadoem, 
                            alteradopor, 
                            alteradoem) 
                    VALUES 
                            (?idmodulo?, 
                            'evento', 
                            1029, 
                            ?idempresa?, 
                            '?idpessoa?',
                            'pessoa',
                            '?idfluxostatus?',
                            0, 
                            'N', 
                            'immsgconf', 
                            DATE_FORMAT(now(),'%Y-%m-%d %h:%m:%s'), 
                            'immsgconf', 
                            DATE_FORMAT(now(), 
                            '%Y-%m-%d %h:%m:%s')
                    )";
    }

    public static function buscarPorIdobjetoTipoobjetoModuloIdmodulo(){
        return "SELECT idfluxostatuspessoa,
                        idobjeto
                FROM fluxostatuspessoa
                WHERE modulo = '?modulo?'
                    AND idmodulo = ?idmodulo?
                    AND idobjeto=?idobjeto?
                    AND tipoobjeto = '?tipoobjeto?'";
    }

    public static function buscarPermissoesPorIdEventoEIdPessoa()
    {
        return "SELECT * 
                FROM fluxostatuspessoa 
                WHERE idobjeto = ?idpessoa?
                AND idmodulo = '?idevento?'
                AND modulo = 'evento'";
    }

    public static function buscarPermissoesAbertoPorIdEvento()
    {
        return "SELECT 
                        o.*
                    FROM
                        evento e
                            JOIN
                        fluxo f ON (f.idobjeto = e.ideventotipo
                            AND tipoobjeto = 'ideventotipo')
                            JOIN
                        fluxoobjeto o ON (o.idfluxo = f.idfluxo
                            AND o.tipoobjeto = 'pessoa'  
                            AND o.tipo='ABERTO')
                            JOIN
                        pessoa p ON p.idpessoa = o.idobjeto
                            AND p.idempresa = e.idempresa
                            AND p.idpessoa=?idpessoa?
                    WHERE
                        e.idevento = ?idevento? ";
    }

    public static function atualizarParaNaoVisualizadoPorIdEvento()
    {
        return "UPDATE fluxostatuspessoa
                SET visualizado = 0 
                WHERE idmodulo = '?idevento?' 
                AND modulo = 'evento'
                AND NOT idobjeto = '?idpessoa?' 
                AND tipoobjeto = 'pessoa'";
    }

    public static function atualizarParaNaoVisualizadoNaoOcultoPorIdmodulo()
    {
        return "UPDATE fluxostatuspessoa
                    set oculto = 1,
                    idfluxostatus = ?idfluxostatus?,
                        alteradoem = now(),
                        alteradopor = '?alteradopor?'
                where modulo = '?modulo?'
                        and idmodulo = ?idmodulo?";
    }

    public static function buscarEventosPorIdFluxostatusPessoa()
    {
        return "SELECT
                    idobjeto, 
                    e.idevento,
                    r.tipoobjeto,
                    r.modulo, 
                    r.idmodulo 
                from fluxostatuspessoa r 
                join evento e on e.idevento = r.idmodulo     
                where idfluxostatuspessoa = '?idfluxostatuspessoa?'";
    }

    public static function buscarFluxoStatuspessoaPorIdObjetoExtEIdEvento()
    {
        return "SELECT idfluxostatuspessoa 
                FROM fluxostatuspessoa 
                WHERE idobjetoext = '?idobjetoext?' 
                AND idmodulo = '?idevento?' 
                AND modulo = 'evento'";
    }

    public static function buscarFluxoStatuspessoaPorIdEvento()
    {
        return "SELECT * FROM fluxostatuspessoa WHERE idmodulo = ?idevento? AND modulo = 'evento'";
    }

    public static function buscarFluxoStatuspessoaPorIdEventoEIdPessoa()
    {
        return "SELECT *
                FROM fluxostatuspessoa 
                WHERE idmodulo = '?idevento?'
                AND modulo = 'evento'
                AND idobjeto = '?idpessoa?' 
                AND tipoobjeto = 'pessoa'";
    }

    public static function buscarObjetoNoEvento()
    {
        return "SELECT *
                FROM fluxostatuspessoa 
                WHERE modulo = '?modulo?' 
                AND idmodulo = ?idmodulo?
                AND tipoobjeto = '?tipoobjeto?' 
                AND idobjeto = '?idobjeto?'";
    }

    public static function verificarSePessoaEstaNoEvento()
    {
        return "SELECT 1 
                FROM fluxostatuspessoa 
                WHERE modulo = 'evento' 
                AND idmodulo = ?idevento?
                AND tipoobjeto = 'pessoa' 
                AND idobjeto = '?idpessoa?'";
    }

    public static function buscarPessoasDeUmEvento()
    {
        return "SELECT
                    r.idobjeto,
                    r.modulo, 
                    r.idmodulo, 
                    et.assinar,
                    e.idpessoa,
                    et.eventotipo
                FROM fluxostatuspessoa r
                JOIN evento e on e.idevento = r.idmodulo AND r.modulo = 'evento'
                JOIN eventotipo et on et.ideventotipo = e.ideventotipo
                WHERE r.tipoobjeto = 'pessoa'
                AND r.idmodulo = ?idevento?";
    }

    public static function buscarTodasPessoasDeUmEventoPorIdEvento()
    {
        return "SELECT r.idobjeto, modulo, idmodulo
                FROM fluxostatuspessoa r
                join evento e on e.idevento = r.idmodulo AND r.modulo = 'evento' 
                where r.tipoobjeto = 'pessoa' 
                and r.idevento = ?idevento?";
    }

    public static function deletarEventosPorRangeDeDataEIdEventoPai()
    {
        return "DELETE o.*
                FROM fluxostatuspessoa o,evento e 
                WHERE e.status is null
                AND e.idevento=o.idmodulo 
                AND o.modulo = 'evento' 
                AND e.ideventopai = ?ideventopai?
                and (e.inicio < '?inicio?' or e.fim > '?fim?')";
    }

    public static function deletarEventosForaDoRangeDeDataEIdEventoPai()
    {
        return "DELETE o.*
                FROM fluxostatuspessoa o,evento e 
                WHERE e.status is null
                AND e.idevento=o.idmodulo 
                AND o.modulo = 'evento' 
                AND e.ideventopai = ?ideventopai?
                and (e.inicio > '?inicio?' or e.fim < '?fim?')";
    }

    public static function buscarPessoasParaListarNoEventoPorIdEvento()
    {
        return "SELECT r.idfluxostatuspessoa, 
                    IF(s.nomecurto is null, s.nome, s.nomecurto) AS nomecurto, 
                    s.idpessoa, 
                    r.visualizado, 
                    r.oculto, 
                    r.inseridomanualmente,
                    r.criadopor,
                    r.criadoem,
                    r.status,
                    s.idtipopessoa, 
                    g.grupo, 
                    CASE
                    WHEN ps.tipoobjeto = 'sgsetor' THEN ss.setor
                    WHEN ps.tipoobjeto = 'sgdepartamento' THEN sd.departamento
                    WHEN ps.tipoobjeto = 'sgarea' THEN sa.area
                    END AS 'setor',
                    rg.idfluxostatuspessoa AS idfluxostatuspessoagrupo,
                    es.rotuloresp AS respstatus,
                    es.cor AS respcor,
                    r.assinar, 
                    et.anonimo,
                    if(e.idpessoa = r.idobjeto, 'Y', 'N') AS dono, 
                    g.idimgrupo,
                    es.statustipo,
                    s.nomecurto
            FROM fluxostatuspessoa r
                JOIN evento e ON r.idmodulo = e.idevento AND r.modulo = 'evento'
                JOIN eventotipo et ON et.ideventotipo = e.ideventotipo
                JOIN pessoa s ON s.idpessoa = r.idobjeto AND r.tipoobjeto ='pessoa'
                LEFT JOIN imgrupo g ON g.idimgrupo = r.idobjetoext 
                LEFT JOIN pessoaobjeto ps ON ps.idpessoa =  s.idpessoa AND ps.tipoobjeto IN ('sgsetor', 'sgdepartamento', 'sgarea') 
                    -- AND ps.tipoobjeto = 'sgsetor' -- (Retirado pq não aparecia o nome das pessoas que estava em outras áreas - Lidiane - 03-04-2020)
                LEFT JOIN sgsetor ss ON ss.idsgsetor = ps.idobjeto AND ss.status = 'ATIVO' 
                LEFT JOIN sgdepartamento sd ON sd.idsgdepartamento = ps.idobjeto AND sd.status = 'ATIVO' 
                LEFT JOIN sgarea sa ON sa.idsgarea = ps.idobjeto AND sa.status = 'ATIVO' 
                LEFT JOIN fluxostatuspessoa rg ON rg.idobjeto = r.idobjetoext AND rg.idmodulo = r.idmodulo AND rg.modulo = 'evento'
                LEFT JOIN fluxostatus fs ON fs.idfluxostatus = r.idfluxostatus
                LEFT JOIN "._DBCARBON."._status es ON(es.idstatus = fs.idstatus)
            WHERE r.idmodulo = '?idevento?'
            GROUP BY s.nome -- (Acrescentado para não repetir os nomes - Lidiane - 03-04-2020)
            ORDER BY g.grupo, s.nome";
    }

    public static function deletarPorIdFluxostatusPessoa()
    {
        return "DELETE from fluxostatuspessoa WHERE idfluxostatuspessoa = ?idfluxostatuspessoa?";
    }

    public static function deletarPessoasInativasDeEventos()
    {
        return "DELETE FROM fluxostatuspessoa 
                WHERE idfluxostatuspessoa IN (
                    SELECT *
                    FROM (
                        SELECT idfluxostatuspessoa 
                        FROM fluxostatuspessoa r 
                        JOIN evento e ON(e.idevento = r.idfluxostatuspessoa AND r.modulo = 'evento')
                        WHERE e.status is null 
                        AND r.tipoobjeto = 'pessoa' 
                        AND r.idobjeto IN (
                            SELECT idpessoa FROM pessoa p WHERE p.status = 'INATIVO' and p.idtipopessoa = 1
                        )
                    ) a
                )";
    }

    public static function deletarPessoasQueNaoFacamParteDoGrupoDentroDosEventos()
    {
        return "DELETE r 
                FROM fluxostatuspessoa r
                JOIN evento e on r.idmodulo = e.idevento AND r.modulo = 'evento'	
                JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.tipoobjeto = 'ideventotipo'
                JOIN fluxostatus es ON es.idfluxo = ms.idfluxo AND e.idfluxostatus = es.idfluxostatus 
                JOIN "._DBCARBON."._status s ON s.idstatus = es.idstatus AND (s.statustipo is null OR s.statustipo = 'INICIO') 
                WHERE r.tipoobjeto = 'pessoa' 
                AND tipoobjetoext = 'imgrupo' 
                AND NOT EXISTS(SELECT 1 FROM imgrupopessoa g WHERE g.idimgrupo = r.idobjetoext AND g.idpessoa = r.idobjeto)";
    }

    public static function inserirOutAtualizarPessoasDoEventoDeAcordoComOGrupo()
    {
        return "REPLACE INTO fluxostatuspessoa (
                SELECT DISTINCT NULL AS idfluxostatuspessoa, 
                    1029 AS idpessoa, 
                    e.idempresa AS idempresa, 
                    e.idevento AS idmodulo, 
                    'evento' AS modulo, 
                    gp.idpessoa AS idobjeto, 
                    'pessoa' AS tipoobjeto, 
                    ets.idstatus AS STATUS, 
                    e.idfluxostatus AS idfluxostatus, 
                    if (ets.ocultar = 'N',0,1) AS oculto, 
                    gp.idimgrupo AS idobjetoext,
                    'imgrupo' AS tipoobjetoext,
                    'N' AS inseridomanualmente, 
                    0 AS visualizado, 
                    'N' AS assinar, 
                    'X' AS editar, 
                    e.criadopor, 
                    e.criadoem, 
                    e.alteradopor, 
                    NOW()
                FROM evento e
                JOIN fluxostatuspessoa r ON r.idmodulo = e.idevento AND r.modulo = 'evento' AND `r`.`tipoobjeto` = 'imgrupo'
                JOIN imgrupopessoa gp ON gp.idimgrupo = r.idobjeto
                LEFT JOIN fluxostatuspessoa r2 ON r2.idmodulo = r.idmodulo AND r2.modulo = 'evento' AND r2.idobjetoext = r.idobjeto AND r2.idobjeto = gp.idpessoa
                LEFT JOIN pessoa p ON p.idpessoa = gp.idpessoa
                JOIN fluxo ms ON ms.idobjeto = e.ideventotipo AND ms.tipoobjeto = 'ideventotipo'
                LEFT JOIN fluxostatus ets ON ets.idfluxo = ms.idfluxo
                JOIN carbonnovo._status s ON s.idstatus = ets.idstatus AND s.statustipo = 'INICIO'
                JOIN fluxostatus etsf ON etsf.idfluxo = e.ideventotipo AND etsf.idfluxostatus = e.idfluxostatus
                JOIN carbonnovo._status s2 ON s2.idstatus = etsf.idstatus AND s2.statustipo NOT IN ('CONCLUIDO', 'CANCELADO', 'FIM')
                JOIN eventotipo et ON et.ideventotipo = e.ideventotipo AND et.status = 'ATIVO'
                WHERE r2.idobjeto IS NULL AND NOT gp.idpessoa = e.idpessoa
                AND NOT EXISTS (SELECT 1 FROM fluxostatuspessoa fp2 WHERE fp2.idmodulo = e.idevento AND fp2.modulo = 'evento' AND fp2.idpessoa = gp.idpessoa))";
    }

    public static function deletarPessoasRemovidasQueNaoPossuemAssinaturaNoDocumentoPorTipoObjetoExt()
    {
        return "DELETE FROM fluxostatuspessoa fp
                WHERE EXISTS (
                    SELECT 1
                    FROM sgdoc
                    JOIN (
                        SELECT idfluxostatuspessoa, idmodulo, modulo, idobjeto, idobjetoext, tipoobjetoext
                        FROM fluxostatuspessoa
                        WHERE modulo like 'documento%' 
                        AND tipoobjeto = 'pessoa' 
                        AND tipoobjetoext = '?tipoobjetoext?'
                    ) as fp2 ON(fp.idmodulo = sgdoc.idsgdoc )
                    WHERE NOT EXISTS(
                        SELECT 1
                        FROM carrimbo
                        WHERE idobjeto = fp.idmodulo
                        AND tipoobjeto = fp.modulo
                        AND idpessoa = fp.idobjeto
                    )
                    AND NOT EXISTS(
                        SELECT 1
                        FROM pessoaobjeto
                        WHERE idobjeto = fp.idobjetoext
                        AND tipoobjeto = fp.tipoobjetoext
                        AND idpessoa = fp.idobjeto
                    )
                    AND fp2.idfluxostatuspessoa = fp.idfluxostatuspessoa
                )";
    }

    public static function deletarPessoasRemovidasDoSetorDepartamentoEAreaQueNaoPossuemAssinaturaDoDocumento()
    {
        return "DELETE dfp.* from fluxostatuspessoa dfp
                WHERE NOT EXISTS(
                    SELECT 1
                    FROM carrimbo crr
                    WHERE crr.idobjeto = dfp.idmodulo and dfp.tipoobjeto = 'pessoa' and crr.idpessoa = dfp.idobjeto
                    AND crr.status IN('ASSINADO', 'ATIVO','PENDENTE')
                )
                AND dfp.idfluxostatuspessoa IN (
                    select tmpfp.idfluxostatuspessoa 
                    from(
                        select fp.*
                        from fluxostatuspessoa fp
                        join sgdoc sd on(sd.idsgdoc = fp.idmodulo and fp.modulo like '%documento%')
                        where fp.tipoobjeto = 'pessoa'
                        and not exists(
                            -- Todas as pessoas de um setor
                            select 1
                            from pessoaobjeto po
                            where po.idobjeto = fp.idobjetoext 
                                and fp.tipoobjetoext = 'sgsetor'
                                and po.tipoobjeto = 'sgsetor'
                                and fp.idobjeto = po.idpessoa
                            union
                                -- Todas as pessoas dos setores do departamento
                                select 1
                                from objetovinculo ov
                                join pessoaobjeto po on(po.idobjeto = ov.idobjetovinc and po.tipoobjeto = 'sgsetor')
                                where ov.idobjeto = fp.idobjetoext and ov.tipoobjeto = 'sgdepartamento'
                                and fp.idobjeto = po.idpessoa
                            union
                                -- Coordenadores do dep
                                select 1
                                from pessoaobjeto po
                                where po.idobjeto = fp.idobjetoext and po.tipoobjeto = 'sgdepartamento'
                                and po.responsavel = 'Y'
                                and fp.idobjeto = po.idpessoa
                            union
                                -- Todos os gerentes de uma area
                                select 1
                                from pessoaobjeto po
                                where po.idobjeto = fp.idobjetoext and po.tipoobjeto = 'sgarea'
                                and fp.idobjeto = po.idpessoa
                                and po.responsavel = 'Y'
                        )
                    ) tmpfp
                    WHERE tmpfp.idobjetoext != ''
                    AND tmpfp.tipoobjetoext != ''
                )";
    }

    public static function inserirPessoasECoordenadoresDeSetoresQueNaoEstejamNoDocumento()
    {
        return "INSERT INTO fluxostatuspessoa
                SELECT
                    NULL AS fluxostatuspessoa,
                    po.idpessoa,
                    po.idempresa, 
                    d.idsgdoc as idmodulo, 
                    fp.modulo as modulo,
                    po.idpessoa as idobjeto, 
                    'pessoa' AS tipoobjeto, 
                    NULL AS status, 
                    NULL AS idfluxostatus, 
                    0 AS oculto, 
                    ov.idobjetovinc as idobjetoext, 
                    ov.tipoobjetovinc as tipoobjetoext, 
                    fp.inseridomanualmente, 
                    0 AS visualizado,
                    'X' AS assinar, 
                    '' AS editar, 
                    'bim' AS criadopor, 
                    now() AS criadoem, 
                    'bim' AS alteradopor, 
                    now() AS alteradoem
                FROM sgdoc d
                JOIN fluxostatuspessoa fp ON(fp.idmodulo = d.idsgdoc AND fp.modulo like 'documento%')
                JOIN objetovinculo ov ON(ov.idobjetovinc = fp.idobjeto AND ov.tipoobjetovinc = 'sgsetor')
                JOIN sgsetor s ON(s.idsgsetor = ov.idobjetovinc)
                JOIN sgdepartamento sgdep ON(sgdep.idsgdepartamento = ov.idobjeto AND ov.tipoobjeto = 'sgdepartamento')
                JOIN pessoaobjeto po ON(po.idobjeto = s.idsgsetor AND po.tipoobjeto = 'sgsetor')
                JOIN pessoa p ON(p.idpessoa = po.idpessoa)
                WHERE NOT EXISTS(
                    SELECT 1
                    FROM fluxostatuspessoa
                    WHERE idmodulo = fp.idmodulo
                    AND modulo = fp.modulo
                    AND idobjeto = po.idpessoa
                    AND tipoobjeto = 'pessoa'
                    AND idobjetoext = s.idsgsetor
                    AND tipoobjetoext = 'sgsetor'
                )
                AND NOT EXISTS(
                    SELECT 1
                    FROM carrimbo crr
                    WHERE crr.idobjeto = fp.idmodulo and crr.tipoobjeto = fp.modulo and crr.idpessoa = po.idpessoa
                    AND crr.status IN('ASSINADO', 'ATIVO','PENDENTE')
                )
                AND p.status IN('ATIVO', 'PENDENTE')
                GROUP BY fp.idmodulo, po.idpessoa, po.idobjeto, po.tipoobjeto
                UNION
                SELECT
                    NULL AS fluxostatuspessoa,
                    po.idpessoa,
                    po.idempresa, 
                    d.idsgdoc as idmodulo, 
                    fp.modulo as modulo,
                    po.idpessoa as idobjeto, 
                    'pessoa' AS tipoobjeto, 
                    NULL AS status, 
                    NULL AS idfluxostatus, 
                    0 AS oculto, 
                    ov.idobjeto as idobjetoext, 
                    ov.tipoobjeto as tipoobjetoext, 
                    fp.inseridomanualmente, 
                    0 AS visualizado,
                    'X' AS assinar, 
                    '' AS editar, 
                    'bim' AS criadopor, 
                    now() AS criadoem, 
                    'bim' AS alteradopor, 
                    now() AS alteradoem
                FROM sgdoc d
                JOIN fluxostatuspessoa fp ON(fp.idmodulo = d.idsgdoc AND fp.modulo like 'documento%')
                JOIN objetovinculo ov ON(ov.idobjetovinc = fp.idobjeto AND ov.tipoobjetovinc = 'sgsetor' AND ov.idobjeto = fp.idobjetoext AND ov.tipoobjeto = 'sgdepartamento')
                JOIN sgsetor s ON(s.idsgsetor = ov.idobjetovinc)
                JOIN sgdepartamento sgdep ON(sgdep.idsgdepartamento = ov.idobjeto AND ov.tipoobjeto = 'sgdepartamento')
                JOIN pessoaobjeto po ON(po.idobjeto = sgdep.idsgdepartamento AND po.tipoobjeto = 'sgdepartamento' and po.responsavel = 'Y')
                JOIN pessoa p ON(p.idpessoa = po.idpessoa)
                WHERE NOT EXISTS(
                    SELECT 1
                    FROM fluxostatuspessoa
                    WHERE idmodulo = fp.idmodulo
                    AND modulo = fp.modulo
                    AND idobjeto = po.idpessoa
                    AND tipoobjeto = 'pessoa'
                    AND idobjetoext = sgdep.idsgdepartamento
                    AND tipoobjetoext = 'sgdepartamento'
                )
                AND p.status IN('ATIVO', 'PENDENTE')
                AND NOT EXISTS(
                    SELECT 1
                    FROM carrimbo crr
                    WHERE crr.idobjeto = fp.idmodulo and crr.tipoobjeto = fp.modulo and crr.idpessoa = po.idpessoa
                    AND crr.status IN('ASSINADO', 'ATIVO','PENDENTE')
                )
                GROUP BY fp.idmodulo, po.idpessoa, po.idobjeto, po.tipoobjeto;";
    }

    public static function inserirCoordenadoresDaAreaVinculadaAoDocumentoCasoNaoEstejam()
    {
        return "INSERT INTO fluxostatuspessoa
                SELECT
                    NULL AS fluxostatuspessoa, fp.idpessoa, po.idempresa, fp.idmodulo, fp.modulo, po.idpessoa, 'pessoa' AS tipoobjeto, NULL AS status, NULL AS idfluxostatus, 0 AS oculto, fp.idobjetoext, fp.tipoobjetoext, fp.inseridomanualmente, 0 AS visualizado,
                    'X' AS assinar, '' AS editar, 'bim' AS criadopor, now() AS criadoem, 'bim' AS alteradopor, now() AS alteradoem
                FROM fluxostatuspessoa fp
                JOIN sgdoc sd on(sd.idsgdoc = fp.idmodulo and fp.modulo like '%documento%')
                JOIN pessoaobjeto po on(po.idobjeto = fp.idobjeto and po.tipoobjeto = 'sgarea' and po.responsavel = 'Y')
                AND NOT EXISTS(
                    SELECT  1
                    FROM fluxostatuspessoa fp2
                    WHERE fp2.tipoobjeto = 'pessoa'
                    AND fp2.idobjeto = po.idpessoa
                    AND fp2.idmodulo = fp.idmodulo
                    AND fp2.modulo = fp.modulo
                )";
    }

    public static function vincularPessoasQueEstejamNoDocumentoTenhaSeuSetorEEstejamSemVinculoNoDocumento()
    {
        return "INSERT INTO fluxostatuspessoa
                SELECT
                    NULL AS fluxostatuspessoa, fp.idpessoa, po.idempresa, fp.idmodulo, fp.modulo,  p.idpessoa as idobjeto, 'pessoa' as tipoobjeto, NULL AS status, NULL AS idfluxostatus, 0 AS oculto, s.idsgsetor as idobjetoext, 'sgsetor 'as tipoobjetoext, fp.inseridomanualmente, 0 AS visualizado,
                    'X' AS assinar, '' AS editar, 'bim' AS criadopor, now() AS criadoem, 'bim' AS alteradopor, now() AS alteradoem
                FROM sgsetor s
                JOIN pessoaobjeto po ON(po.idobjeto = s.idsgsetor AND po.tipoobjeto = 'sgsetor')
                JOIN pessoa p ON(p.idpessoa = po.idpessoa)
                JOIN fluxostatuspessoa fp ON(fp.idobjeto = p.idpessoa AND fp.tipoobjeto = 'pessoa')
                JOIN sgdoc d ON(d.idsgdoc = fp.idmodulo AND fp.modulo like '%documento%')
                WHERE fp.idobjetoext is null
                AND fp.tipoobjetoext is null
                AND p.status = 'ATIVO'
                AND s.status = 'ATIVO'
                AND EXISTS (
                    SELECT 1
                    FROM fluxostatuspessoa
                    WHERE idobjeto = s.idsgsetor
                    AND tipoobjeto = 'sgsetor'
                    AND idmodulo = fp.idmodulo
                    AND modulo like '%documento%'
                )";
    }

    public static function atualizarPessoasRetiradasDoSetorDepartamentoOuAreaQuePossuemAssinaturaDoDocumento()
    {
        return "UPDATE fluxostatuspessoa dfp
                SET dfp.idobjetoext = null, dfp.tipoobjetoext = null
                WHERE EXISTS(
                    SELECT *
                    FROM carrimbo crr
                    WHERE crr.idobjeto = dfp.idmodulo and crr.idpessoa = dfp.idobjeto
                    AND crr.status IN('ASSINADO', 'ATIVO')
                )
                AND dfp.idfluxostatuspessoa IN (
                    select tmpfp.idfluxostatuspessoa 
                    from(
                        select fp.*
                        from fluxostatuspessoa fp
                        join sgdoc sd on(sd.idsgdoc = fp.idmodulo and fp.modulo like '%documento%')
                        where fp.tipoobjeto = 'pessoa'
                        and not exists(
                            -- Todas as pessoas de um setor
                            select 1
                            from pessoaobjeto po
                            where po.idobjeto = fp.idobjetoext 
                                and fp.tipoobjetoext = 'sgsetor'
                                and po.tipoobjeto = 'sgsetor'
                                and fp.idobjeto = po.idpessoa
                            union
                                -- Todas as pessoas dos setores do departamento
                                select 1
                                from objetovinculo ov
                                join pessoaobjeto po on(po.idobjeto = ov.idobjetovinc and po.tipoobjeto = 'sgsetor')
                                where ov.idobjeto = fp.idobjetoext and ov.tipoobjeto = 'sgdepartamento'
                                and fp.idobjeto = po.idpessoa
                            union
                                -- Coordenadores do dep
                                select 1
                                from pessoaobjeto po
                                where po.idobjeto = fp.idobjetoext and po.tipoobjeto = 'sgdepartamento'
                                and po.responsavel = 'Y'
                                and fp.idobjeto = po.idpessoa
                            union
                                -- Todos os gerentes de uma area
                                select 1
                                from pessoaobjeto po
                                where po.idobjeto = fp.idobjetoext and po.tipoobjeto = 'sgarea'
                                and fp.idobjeto = po.idpessoa
                                and po.responsavel = 'Y'
                        )
                    ) tmpfp
                )";
    }

    public static function inserirPessoasQueNaoEstaoVinculadasMasSeuSetorEstaAoDoc()
    {
        return "INSERT INTO fluxostatuspessoa 
                 SELECT
                    NULL AS fluxostatuspessoa, fp.idpessoa, po.idempresa, fp.idmodulo, fp.modulo,  p.idpessoa as idobjeto, 'pessoa' as tipoobjeto, NULL AS status, NULL AS idfluxostatus, 0 AS oculto,fp.idobjetoext, fp.tipoobjetoext AS tipoobjetoext, fp.inseridomanualmente, 0 AS visualizado,
                    'X' AS assinar, '' AS editar, 'bim' AS criadopor, now() AS criadoem, 'bim' AS alteradopor, now() AS alteradoem
                FROM sgsetor s
                JOIN pessoaobjeto po ON(po.idobjeto = s.idsgsetor AND po.tipoobjeto = 'sgsetor')
                JOIN pessoa p ON(p.idpessoa = po.idpessoa)
                JOIN fluxostatuspessoa fp ON(fp.idobjetoext = s.idsgsetor AND fp.tipoobjetoext = 'sgsetor')
                JOIN sgdoc ON(sgdoc.idsgdoc = fp.idmodulo AND fp.modulo like '%documento%')
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM fluxostatuspessoa fp2
                    WHERE fp2.idmodulo = fp.idmodulo
                    AND fp2.modulo = fp.modulo
                    AND fp2.idobjeto = po.idpessoa
                    AND fp2.tipoobjeto = 'pessoa'
                    AND fp2.idobjetoext = fp.idobjetoext
                    AND fp2.tipoobjetoext = 'sgsetor'
                )
                AND s.status = 'ATIVO'
                AND p.status IN('ATIVO', 'PENDENTE')
                AND NOT EXISTS(
                    SELECT 1
                    FROM carrimbo crr
                    WHERE crr.idobjeto = fp.idmodulo and crr.tipoobjeto = fp.modulo and crr.idpessoa = po.idpessoa
                    AND crr.status IN('ASSINADO', 'ATIVO','PENDENTE')
                )
                GROUP BY po.idpessoa, fp.idmodulo";
    }

    public static function atualizarIdStatusPorIdFluxostatusPessoa()
    {
        return "UPDATE fluxostatuspessoa SET idstatus = '?idstatus?'
                WHERE idfluxostatuspessoa = ?idfluxostatuspessoa?";
    }

    public static function definirEventoComoNaoVisualizado()
    {
        return "UPDATE fluxostatuspessoa r, fluxostatuspessoa r2  
                SET r2.visualizado = '0'
                WHERE r.idmodulo = ?idfluxostatuspessoa?
                AND r2.idevento = r.idevento 
                AND r2.tipoobjeto = 'pessoa'
                AND r2.idmodulo != ?idfluxostatuspessoa? 
                AND r2.modulo = 'evento'";
    }

    public static function buscarEventoTipoPorModulo(){
        return "SELECT distinct(e.ideventotipo) AS ideventotipo, et.fluxounico
            FROM fluxostatuspessoa mp 
            JOIN evento e ON mp.idmodulo = e.idevento AND mp.modulo = 'evento'
            JOIN eventotipo et ON et.ideventotipo = e.ideventotipo
            WHERE mp.idmodulo = '?idobjeto?' 
            AND mp.modulo = '?modulo?'";
    }

    public static function buscarQtdFluxoStatusPessoaEvento (){
        return "SELECT COUNT(idfluxostatuspessoa) AS 'contador'
            FROM fluxostatuspessoa fp 
            WHERE fp.idmodulo = '?idmodulo?'
                AND fp.modulo = 'evento'
                AND EXISTS (
                    SELECT 1
                    FROM evento e
                    WHERE fp.idmodulo = e.idevento
                        AND e.idfluxostatus = fp.idfluxostatus
                )";
    }

    public static function removerOcultarDoEvento (){
        return "UPDATE fluxostatuspessoa 
                SET alteradoem = now(), 
                    alteradopor = 'immsgconf', 
                    oculto = '0', 
                    idfluxostatus = (SELECT msf.idfluxostatus 
                                        FROM fluxostatus msf JOIN fluxo ms ON ms.idfluxo = msf.idfluxo 
                                    JOIN carbonnovo._status s ON s.idstatus = msf.idstatus 
                                    WHERE ms.idobjeto = '?ideventotipo?' 
                                        AND ms.modulo = 'evento' 
                                        AND ms.tipoobjeto = 'ideventotipo' 
                                        AND s.statustipo = 'INICIO') 
                WHERE idevento = ?idevento? and idobjeto = ?idpessoa? and tipoobjeto = 'pessoa'";
    }

    public static function atualizarVinculoOrganogramaDePessoasNoDocumento()
    {
        return "UPDATE fluxostatuspessoa as fp
                INNER JOIN (
                    SELECT s.idsgsetor, fp.idfluxostatuspessoa
                    FROM sgdoc d
                    JOIN fluxostatuspessoa fp ON(fp.idmodulo = d.idsgdoc AND fp.modulo like 'documento%')
                    JOIN pessoaobjeto po ON(po.idpessoa = fp.idobjeto AND fp.tipoobjeto = 'pessoa')
                    JOIN sgsetor s ON(s.idsgsetor = po.idobjeto AND po.tipoobjeto = 'sgsetor')
                    JOIN pessoa p ON(p.idpessoa = po.idpessoa)
                    WHERE fp.idobjetoext is null
                    AND fp.tipoobjetoext is null
                    AND EXISTS(
                        SELECT 1
                        FROM fluxostatuspessoa
                        WHERE idmodulo = fp.idmodulo
                        AND modulo = fp.modulo
                        AND idobjeto = po.idobjeto
                        AND tipoobjeto = 'sgsetor'
                        UNION
                        SELECT 1
                        FROM fluxostatuspessoa
                        WHERE idmodulo = fp.idmodulo
                        AND modulo = fp.modulo
                        AND idobjetoext = po.idobjeto
                        AND tipoobjetoext = 'sgsetor'
                    )
                    AND s.status = 'ATIVO'
                    AND p.status IN('ATIVO', 'PENDENTE')
                    GROUP BY fp.idmodulo, po.idpessoa, po.idobjeto, po.tipoobjeto
                ) as tb
                SET idobjetoext = tb.idsgsetor, tipoobjetoext = 'sgsetor'
                WHERE fp.idfluxostatuspessoa = tb.idfluxostatuspessoa";
    }

    public static function buscarParticipanteComGrupoDesatualizadoNoEvento()
    {
        return "SELECT fp.idfluxostatuspessoa, qry.idobjetoext as idgrupo, qry.idpessoa
                FROM fluxostatuspessoa fp
                JOIN (
                    SELECT fp2.idmodulo, fp2.modulo, fp2.idobjetoext, gp.idpessoa
                    FROM fluxostatuspessoa fp2
                    JOIN imgrupopessoa gp ON(fp2.idobjetoext = gp.idimgrupo AND fp2.tipoobjetoext = 'imgrupo')
                ) as qry ON(qry.idmodulo = fp.idmodulo AND qry.modulo = fp.modulo AND qry.idpessoa = fp.idobjeto AND fp.tipoobjeto = 'pessoa')
                WHERE fp.idfluxostatuspessoa = ?idfluxostatuspessoa?
                GROUP BY qry.idpessoa";
    }

    public static function atualizarVinculoParticipanteEvento()
    {
        return 'UPDATE fluxostatuspessoa
                SET idobjetoext = "?idobjetoext?", tipoobjetoext = "imgrupo"
                WHERE idfluxostatuspessoa = ?idfluxostatuspessoa?';
    }

    public static function buscarDocumentosPorIdObjetoETipoObjeto()
    {
        return "SELECT 
                    d.titulo, d.idsgdoc
                FROM
                    fluxostatuspessoa v,
                    sgdoc d
                WHERE
                    v.idmodulo = d.idsgdoc                       
                    AND v.modulo in ('documento','sgdoc')
                    AND v.tipoobjeto = '?tipoobjeto?'
                    AND v.idobjeto = ?idobjeto?
                    and d.idsgdoctipodocumento in (1,3,7,5) 
                    ORDER BY d.titulo";
    }

    public static function atualizarFluxoStatusPessoaPorModulo()
    {   
        return "UPDATE fluxostatuspessoa 
                   SET idfluxostatus = ?idfluxostatus?,
                       alteradopor = '?alteradopor?',
                       alteradoem = now()
                 WHERE idmodulo = ?idmodulo? AND modulo = 'evento'";
    }

    public static function buscarCampoOcultoPessoa()
    {
        return "SELECT oculto FROM fluxostatuspessoa WHERE idmodulo = ?idmodulo? AND modulo = '?modulo?' AND idobjeto = ?idobjeto? AND tipoobjeto = 'pessoa'";
    }
}

?>
