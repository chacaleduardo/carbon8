<?
require_once(__DIR__.'/_iquery.php');

class SgdocQuery implements DefaultQuery{
    public static $table = 'sgdoc';
    public static $pk = 'idsgdoc';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function inserir()
    {
        return "INSERT INTO sgdoc (
                        idempresa, idregistro, idsgdoctipo, idsgdoctipodocumento,
                        idunidade, idpessoa, titulo, idsgtipodoc, cpctr, idequipamento,
                        idsgtipodocsub, versao, revisao, copia, status, idfluxostatus,
                        tipoacesso, conteudo, acompversao, regalteracao, idsgdoccopia,
                        responsavel, responsavelsec, inicio, fim, idrnc, grau, impacto,
                        nota, resultado, observacao, datavencimento, restrito, tipotreinamento,
                        tipoavaliacao, criadopor, criadoem, alteradopor, alteradoem,
                        conteudoold, scrolleditor, iddocumentoorigem
                    )
                    VALUES
                    (
                        ?idempresa?, ?idregistro?, '?idsgdoctipo?', ?idsgdoctipodocumento?,
                        ?idunidade?, ?idpessoa?, '?titulo?', ?idsgtipodoc?, '?cpctr?', ?idequipamento?,
                        ?idsgtipodocsub?, ?versao?, ?revisao?, ?copia?, '?status?', ?idfluxostatus?,
                        '?tipoacesso?', '?conteudo?', '?acompversao?', '?regalteracao?', ?idsgdoccopia?,
                        '?responsavel?', '?responsavelsec?', ?inicio?, ?fim?, ?idrnc?, '?grau?', '?impacto?',
                        '?nota?', '?resultado?', '?observacao?', ?datavencimento?, '?restrito?', ?tipotreinamento?,
                        ?tipoavaliacao?, '?criadopor?', ?criadoem?, '?alteradopor?', ?alteradoem?,
                        '?conteudoold?', ?scrolleditor?, ?iddocumentoorigem?
                    );";
    }

    public static function buscarInfosTipoDocumentoQueOUsuarioPossui(){
        return "SELECT sg.idsgdoctipodocumento,
                        sg.tipodocumento,
                        UPPER(sg.idsgdoctipo) AS tipo,
                        sg.flquestionario AS qst
                FROM sgdoctipodocumento sg 
                    JOIN objetovinculo ov ON (sg.idsgdoctipodocumento = ov.idobjeto AND ov.tipoobjeto = 'tipodocumento')
                    JOIN imgrupopessoa gp ON ((gp.idimgrupo = ov.idobjetovinc AND ov.tipoobjetovinc = 'imgrupo') OR (gp.idpessoa = ov.idobjetovinc AND ov.tipoobjetovinc = 'pessoa'))
                WHERE sg.status = 'ATIVO'
                    AND sg.idsgdoctipo ='?idsgdoctipo?' 
                    AND ((ov.idobjetovinc = '?idpessoa?' AND ov.tipoobjetovinc = 'pessoa') OR (gp.idpessoa = '?idpessoa?')) 
                    AND sg.idsgdoctipo ='?idsgdoctipo?'  
                ORDER BY sg.idsgdoctipo, sg.tipodocumento";
    }

    public static function buscarInfosTipoDocumento(){
        return "SELECT idsgdoctipodocumento,
                        tipodocumento,
                        UPPER(idsgdoctipo) AS tipo,
                        flquestionario AS qst
                FROM sgdoctipodocumento 
                WHERE
                    status = 'ATIVO'
                    AND idsgdoctipo ='?idsgdoctipo?'
                ORDER BY tipo,tipodocumento";
    }

    public static function buscarDocsQuePodemSerVinculados(){
        return "SELECT d.idsgdoc,
                        CONCAT(d.idregistro,'-',d.titulo) AS titulo,
                        t.tipodocumento
                FROM sgdoc d FORCE INDEX(status_empresa)
                    LEFT JOIN sgdoctipodocumento t ON (t.idsgdoctipodocumento=d.idsgdoctipodocumento AND t.status='ATIVO')
                WHERE  d.status!='OBSOLETO'
                    ?sqlidempresa?
                    AND NOT EXISTS(-- Não mostrar documentos já vinculados
                            SELECT 1 FROM sgdocvinc v WHERE v.idsgdoc = ?idsgdoc? AND v.iddocvinc=d.idsgdoc
                        )
                    AND d.idsgdoctipo NOT IN ('avaliacao')
                ORDER BY t.tipodocumento,d.titulo";
    }

    public static function buscarDocsVinculadosSemAvaliacaoTreinamento(){
        return "SELECT v.idsgdocvinc,
                        d.idsgdoc,
                        d.idregistro,
                        d.titulo,
                        v.criadopor,
                        v.criadoem
                FROM sgdocvinc v 
                    JOIN sgdoc d ON (d.idsgdoc=v.iddocvinc AND d.idsgdoctipo NOT IN('avaliacao') and d.idempresa = ?idempresa?)
                WHERE v.idsgdoc = ?idsgdoc?";
    }

    public static function buscarDocsVinculados(){
        return "SELECT v.idsgdoc,
                        d.titulo
                FROM sgdocvinc v
                    JOIN sgdoc d on (v.idsgdoc = d.idsgdoc)
                WHERE v.iddocvinc = ?idsgdoc? AND v.idsgdoc NOT IN ('') order by d.titulo";
    }

    public static function buscarParticipantesDeDocsVinculados(){
        return "SELECT 
                    p.idpessoa, IFNULL(p.nomecurto,p.nome) as nomecurto
                FROM
                    fluxostatuspessoa f
                        LEFT JOIN
                    pessoa p ON (f.idobjeto = p.idpessoa)
                        AND f.tipoobjeto = 'pessoa'
                WHERE
                    f.idmodulo = ?idsgdoc?
                        AND f.modulo like '%documento%'
                        AND tipoobjeto = 'pessoa'
                ORDER BY p.nomecurto";
    }

    public static function buscarParticipantesParaVincularAoDoc(){
        return "SELECT 
                    sa.idsgsetor AS idobjeto,
                    CONCAT(e.sigla, ' - ',sa.setor) AS objeto,
                    'sgsetor' AS tipo
                    ,4 AS ord
                FROM
                    sgsetor sa
                    JOIN empresa e ON (e.idempresa = sa.idempresa)
                WHERE
                    sa.status = 'ATIVO'
                    ?documentosgsetorPorSessionIdempresa?
                    AND NOT EXISTS(SELECT 
                                    1
                                    FROM
                                        fluxostatuspessoa f
                                    WHERE
                                        f.idobjeto = sa.idsgsetor
                                        AND f.tipoobjeto = 'sgsetor'
                                        AND f.idmodulo = ?idsgdoc?
                                        AND f.modulo like '%documento%'
                                    ) 
                UNION
                    SELECT 
                        p.idpessoa AS idobjeto,
                        CONCAT(e.sigla, ' - ', IFNULL(p.nomecurto,p.nome)) AS objeto,
                        'pessoa' AS tipo
                        ,5 AS ord
                    FROM
                        pessoa p
                        JOIN empresa e ON (e.idempresa = p.idempresa)
                    WHERE
                        p.status = 'ATIVO'
                        ?documentopessoaPorSessionIdempresa?
                        AND p.idtipopessoa = 1
                        AND NOT EXISTS( SELECT 
                                            1
                                        FROM
                                            fluxostatuspessoa f
                                        WHERE
                                            f.idobjeto = p.idpessoa
                                                AND f.idmodulo = ?idsgdoc?
                                        AND f.modulo like '%documento%'
                                        )
                UNION
                    SELECT 
                        a.idsgdepartamento AS idobjeto
                        ,CONCAT(e.sigla, ' - DEPARTAMENTO - ',a.departamento) AS objeto
                        ,'sgdepartamento' AS tipo
                        ,3 AS ord
                    FROM sgdepartamento a
                    JOIN empresa e ON (e.idempresa = a.idempresa)
                    WHERE
                        NOT EXISTS(SELECT 
                                        1
                                    FROM fluxostatuspessoa f
                                    WHERE 
                                        f.idobjeto = a.idsgdepartamento
                                        AND f.tipoobjeto = 'sgdepartamento'
                                        AND f.idmodulo = ?idsgdoc?
                                        AND f.modulo like '%documento%'
                                    )
                        ?documentosgdepartamentoPorSessionIdempresa?
                        AND a.status = 'ATIVO'
                UNION 
                    SELECT 
                        a.idsgarea AS idobjeto
                        ,CONCAT(e.sigla, ' - AREA - ',a.area) AS objeto
                        ,'sgarea' AS tipo
                        ,2 AS ord
                    FROM sgarea a
                        JOIN empresa e ON (e.idempresa = a.idempresa)
                    WHERE
                        NOT EXISTS(SELECT 
                                        1
                                    FROM fluxostatuspessoa f
                                    WHERE 
                                        f.idobjeto = a.idsgarea
                                        AND f.tipoobjeto = 'sgarea'
                                        AND f.idmodulo = ?idsgdoc?
                                        AND f.modulo like '%documento%'
                                    )
                        ?documentosgareaPorSessionIdempresa?
                        AND a.status = 'ATIVO'
                UNION
                        SELECT 
                        c.idsgconselho AS idobjeto
                        ,CONCAT(e.sigla, ' - CONSELHO - ',c.conselho) AS objeto
                        ,'sgconselho' AS tipo
                        ,1 AS ord
                    FROM sgconselho c
                        JOIN empresa e ON (e.idempresa = c.idempresa)
                    WHERE
                        NOT EXISTS(SELECT 
                                        1
                                    FROM fluxostatuspessoa f
                                    WHERE 
                                        f.idobjeto = c.idsgconselho
                                        AND f.tipoobjeto = 'sgconselho'
                                        AND f.idmodulo = ?idsgdoc?
                                        AND f.modulo like '%documento%'
                                    )
                        ?documentosgcoselhoPorSessionIdempresa?
                        AND  c.status = 'ATIVO'
                ORDER BY ord ASC, objeto asc;";
    }

    public static function buscarParticipantesVinculadosComSetor(){
        return "SELECT r.idfluxostatuspessoa, 
                        IF(s.nomecurto is null, s.nome, s.nomecurto) AS nomecurto, 
                        s.idpessoa,
                        im.setor,
                        r.idpessoa as vinculadopor,
                        r.inseridomanualmente,
                        r.editar,
                        r.criadopor,
                        r.criadoem,
                        r.status,
                        s.idtipopessoa,
                        rg.idfluxostatuspessoa AS idfluxostatuspessoagrupo,
                        r.assinar,
                        rg.idobjeto,
                        c.idcarrimbo,
                        c.alteradoem,
                        c.status as statuscar,
                        c.versao as versao,
                        e.versao as versaoDoc,
                        case when r.idobjetoext != '' and r.idobjetoext is not null  then 1 
                        else 2 end as ordemsetor,
                        case when c.status ='ASSINADO' and c.versao=e.versao then 1 
                        when c.status ='ASSINADO' and c.versao<e.versao then 2
                        when c.status ='PENDENTE' and c.versao=e.versao then 3
                        when c.status ='PENDENTE' and c.versao<e.versao then 4
                        else 5 end as ordemass,
                        rg.tipoobjeto as tipolocal,
                        case when rg.tipoobjeto = 'sgarea' then sa.area
                        when rg.tipoobjeto = 'sgdepartamento' then sd.departamento
                        when rg.tipoobjeto = 'sgsetor' then ss.setor
                        else '' end as local,
                        case when rg.tipoobjeto = 'sgarea' then 1
                        when rg.tipoobjeto = 'sgdepartamento' then 2
                        when rg.tipoobjeto = 'sgsetor' then 3
                        else 4 end as localord
                    FROM fluxostatuspessoa r
                    JOIN sgdoc e ON r.idmodulo = e.idsgdoc AND r.modulo like '%documento%'
                    JOIN pessoa s ON s.idpessoa = r.idobjeto  AND r.tipoobjeto ='pessoa'
                    left JOIN carrimbo c on e.idsgdoc = c.idobjeto  and c.idpessoa = s.idpessoa and c.tipoobjeto = 'documento' and c.versao = e.versao
                    left join pessoaobjeto gp on (s.idpessoa = gp.idpessoa and gp.tipoobjeto='sgsetor')
                    left join sgsetor im on (im.idsgsetor = gp.idobjeto)
                    LEFT JOIN sgsetor ss ON ss.idsgsetor = r.idobjetoext and r.tipoobjetoext='sgsetor'  AND ss.status = 'ATIVO' 
                    LEFT JOIN sgdepartamento sd ON sd.idsgdepartamento = r.idobjetoext and r.tipoobjetoext='sgdepartamento'  AND sd.status = 'ATIVO' 
                    LEFT JOIN sgarea sa ON sa.idsgarea = r.idobjetoext and r.tipoobjetoext='sgarea'  AND sa.status = 'ATIVO' 
                    LEFT JOIN fluxostatuspessoa rg ON rg.idobjeto = r.idobjetoext AND rg.idmodulo = r.idmodulo AND rg.modulo like '%documento%' and rg.tipoobjeto !='pessoa'
                    WHERE r.idmodulo =  ?idsgdoc?
                    GROUP BY s.idpessoa
                    ORDER BY  localord asc, local asc, ordemass asc,  nomecurto asc";
    }

    public static function buscarParticipantesVinculadosSemSetor(){
        return "SELECT r.idfluxostatuspessoa, 
                        IF(s.nomecurto is null, s.nome, s.nomecurto) AS nomecurto, 
                        s.idpessoa,
                        im.setor,
                        r.idpessoa as vinculadopor,
                        r.inseridomanualmente,
                        r.editar,
                        r.criadopor,
                        r.criadoem,
                        r.status,
                        s.idtipopessoa,
                        rg.idfluxostatuspessoa AS idfluxostatuspessoagrupo,
                        r.assinar,
                        rg.idobjeto,
                        c.idcarrimbo,
                        c.alteradoem,
                        c.status as statuscar,
                        c.versao as versao,
                        case when r.idobjetoext != '' and r.idobjetoext is not null  then 1 
                        else 2 end as ordemsetor,
                        case when c.status ='ASSINADO' and c.versao=e.versao then 1 
                        when c.status ='ASSINADO' and c.versao<e.versao then 2
                        when c.status ='PENDENTE' and c.versao=e.versao then 3
                        when c.status ='PENDENTE' and c.versao<e.versao then 4
                        else 5 end as ordemass,
                        rg.tipoobjeto as tipolocal,
                        case when rg.tipoobjeto = 'sgarea' then sa.area
                        when rg.tipoobjeto = 'sgdepartamento' then sd.departamento
                        when rg.tipoobjeto = 'sgsetor' then ss.setor
                        else '' end as local,
                        case when rg.tipoobjeto = 'sgarea' then 1
                        when rg.tipoobjeto = 'sgdepartamento' then 2
                        when rg.tipoobjeto = 'sgsetor' then 3
                        else 4 end as localord
                FROM fluxostatuspessoa r
                    JOIN sgdoc e ON r.idmodulo = e.idsgdoc AND r.modulo like '%documento%'
                    JOIN pessoa s ON s.idpessoa = r.idobjeto  AND r.tipoobjeto ='pessoa'
                    left JOIN carrimbo c on e.idsgdoc = c.idobjeto  and c.idpessoa = s.idpessoa and c.tipoobjeto = 'documento'
                    left join pessoaobjeto gp on (s.idpessoa = gp.idpessoa and gp.tipoobjeto='sgsetor')
                    left join sgsetor im on (im.idsgsetor = gp.idobjeto)
                    LEFT JOIN sgsetor ss ON ss.idsgsetor = r.idobjetoext and r.tipoobjetoext='sgsetor' -- AND ss.status = 'ATIVO' 
                    LEFT JOIN sgdepartamento sd ON sd.idsgdepartamento = r.idobjetoext and r.tipoobjetoext='sgdepartamento' -- AND sd.status = 'ATIVO' 
                    LEFT JOIN sgarea sa ON sa.idsgarea = r.idobjetoext and r.tipoobjetoext='sgarea' -- AND sa.status = 'ATIVO' 
                    LEFT JOIN fluxostatuspessoa rg ON rg.idobjeto = r.idobjetoext AND rg.idmodulo = r.idmodulo AND rg.modulo like '%documento%' and rg.tipoobjeto !='pessoa'
                WHERE r.idmodulo =  ?idsgdoc?
                GROUP BY s.idpessoa
                ORDER BY  localord asc, local asc, im.setor is null, ordemass asc,  nomecurto asc";
    }

    public static function buscarSetorDepsAreasVaziosNoDoc(){
        return "SELECT 
                    case when f.tipoobjeto = 'sgarea' then sa.area
                        when f.tipoobjeto = 'sgdepartamento' then sd.departamento
                        when f.tipoobjeto = 'sgsetor' then ss.setor
                        else '' end as local,
                        case when f.tipoobjeto = 'sgarea' then sa.idsgarea
                            when f.tipoobjeto = 'sgdepartamento' then sd.idsgdepartamento
                            when f.tipoobjeto = 'sgsetor' then ss.idsgsetor
                            else '' end as idlocal,
                        f.idfluxostatuspessoa,
                        f.tipoobjeto
                FROM
                    fluxostatuspessoa f
                    LEFT JOIN sgsetor ss ON ss.idsgsetor = f.idobjeto and f.tipoobjeto='sgsetor' -- AND ss.status = 'ATIVO' 
                    LEFT JOIN sgdepartamento sd ON sd.idsgdepartamento = f.idobjeto and f.tipoobjeto='sgdepartamento' -- AND sd.status = 'ATIVO' 
                    LEFT JOIN sgarea sa ON sa.idsgarea = f.idobjeto and f.tipoobjeto='sgarea' -- AND sa.status = 'ATIVO' 
                WHERE
                    f.idmodulo = ?idsgdoc?
                        AND f.modulo LIKE 'documento%'
                        AND f.tipoobjeto in ('sgdepartamento','sgsetor','sgarea')
                        AND not exists 
                            (SELECT 1 from fluxostatuspessoa f1
                            where f1.idobjetoext = f.idobjeto
                                    and f1.tipoobjeto = 'pessoa'
                                    and f1.tipoobjetoext = f.tipoobjeto
                                    and f.modulo=f1.modulo
                                    and f.idmodulo=f1.idmodulo
                                    and f1.idfluxostatuspessoa != f.idfluxostatuspessoa)";
    }

    public static function buscarUltimaAssinatura(){
        return "SELECT c.idcarrimbo,
                        c.versao,
                        c.status,
                        c.alteradoem 
                FROM sgdoc s
                    JOIN carrimbo c on s.idsgdoc = c.idobjeto 
                WHERE c.status      in ('PENDENTE','ASSINADO')
                    AND c.idpessoa    = ?idpessoa?
                    AND c.idobjeto    = ?idsgdoc?
                    and c.tipoobjeto like '%documento%'
                    order by c.versao desc                                  
                LIMIT 1";
    }

    public static function buscarTiposDeDocQueOUsuarioPodeCriar(){
        return "SELECT 
                    st.idsgdoctipo,
                    st.rotulo
                FROM
                    fluxoobjeto fo
                        JOIN
                    fluxo f ON (f.idfluxo = fo.idfluxo)
                        JOIN
                    sgdoctipo st ON (st.idsgdoctipo = f.idobjeto)
                        LEFT JOIN
                    imgrupopessoa gp on((gp.idimgrupo = fo.idobjeto and fo.tipoobjeto = 'imgrupo') or (gp.idpessoa = fo.idobjeto and fo.tipoobjeto = 'pessoa'))
                WHERE
                    gp.idpessoa = ?idpessoa?
                    and st.idsgdoctipo not in ('avaliacao') -- removido a pedido do william 03/04/2024
                    and st.status='ATIVO'
                group by idsgdoctipo
                ORDER BY rotulo";
    }

    public static function verificaPermissaoSgdoc(){
        return "SELECT p.idpessoa, p.nomecurto FROM fluxostatuspessoa fp join pessoa p on(fp.idobjeto = p.idpessoa)
                WHERE fp.idobjeto = ?idpessoa? 
                AND fp.idmodulo = ?idsgdoc? AND fp.modulo like '%documento%'
                UNION
                SELECT p.idpessoa, p.nomecurto FROM pessoa p JOIN sgdoc d on(d.criadopor = p.usuario)
                WHERE d.idsgdoc = ?idsgdoc?
                and p.idpessoa = ?idpessoa? ";
    }

    public static function verificaPermissaoSgdocEdicao(){
        return "SELECT editar
                FROM fluxostatuspessoa 
                WHERE idobjeto = ?idpessoa?
                    and tipoobjeto = 'pessoa'
                    and idmodulo=?idsgdoc?
                    and modulo like '%documento%'
                UNION
                SELECT 'N' as editar
                FROM sgdocupd su 
                    JOIN pessoa p on(su.criadopor = p.usuario)
                WHERE su.idsgdoc = ?idsgdoc?
                and p.idpessoa = ?idpessoa?
                LIMIT 1";
    }

    public static function pegarDataVencimento(){
        return "SELECT vencimento FROM laudo.vwsgdoc where idsgdoc= ?idsgdoc?";
    }
    
    public static function buscarDocVinculadosPorTipo(){
        return "SELECT vc.idsgdocvinc,
                        sg.idsgdoc,
                        sg.titulo
                FROM sgdocvinc vc 
                    JOIN sgdoc sg ON (vc.iddocvinc = sg.idsgdoc)
                WHERE  sg.idsgdoctipo = '?tipo?' and vc.idsgdoc = ?idsgdoc?";
    }
    
    public static function buscarPessoasSemAssinatura(){
        return "SELECT fs.idobjeto AS idpessoa
                FROM fluxostatuspessoa fs
                    JOIN pessoa p ON(p.idpessoa = fs.idobjeto and p.status='ATIVO')
                WHERE
                    fs.modulo='?modulo?' 
                    AND fs.tipoobjeto='pessoa' 
                    AND fs.idmodulo = ?idsgdoc?
                    AND fs.idobjetoext IS NOT NULL 
                    AND NOT EXISTS(SELECT 1 FROM carrimbo c WHERE c.idpessoa=fs.idobjeto AND c.tipoobjeto='?modulo?' AND c.idobjeto=fs.idmodulo AND c.versao= ?versao?)
                    AND NOT EXISTS(SELECT 1 FROM carrimbo c WHERE c.idpessoa=fs.idobjeto AND c.idobjeto=fs.idmodulo AND c.tipoobjeto='?modulo?' AND c.versao< ?versao?  AND c.status='PENDENTE')";
    }

    public static function buscarSgdocPorIdSgdocTipoEGetIdEmpresa()
    {
        return "SELECT d.idsgdoc,
                        concat(d.idregistro,'-',d.titulo) as titulo,
                        d.idsgdoctipo
                FROM sgdoc d
                WHERE 1
                ?getidempresa?
                AND d.idsgdoctipo in ('?idsgdoctipo?')
                ORDER BY d.titulo";
    }

    public static function buscarSgdocPorIdSgdocTipo()
    {
        return "SELECT idsgdoc, idsgdoctipo, titulo 
                FROM sgdoc 
                WHERE idsgdoctipo in('?idsgdoctipo?')";
    }

    public static function buscarAssinaturaPorIdPessoaIdObjetoETipoObjeto()
    {
        return "SELECT c.idcarrimbo,
                    c.status,
                    if(s.versao = c.versao, null, s.versao) as versao
                FROM sgdoc s
                JOIN carrimbo c on s.idsgdoc = c.idobjeto and (s.versao = c.versao or c.versao = 0)
                WHERE c.status      in ('PENDENTE', 'ATIVO')
                AND c.idpessoa    = ?idpessoa?
                AND c.idobjeto    = ?idobjeto?
                AND c.tipoobjeto  = '?tipoobjeto?'
                LIMIT 1";
    }

    public static function buscarIdFluxoStatusPorIdSgDoc () {
        return "SELECT fs.idfluxostatus
            FROM sgdoc sg 
                JOIN fluxo f ON sg.idsgdoctipo = f.idobjeto 
                    AND f.modulo = 'documento' 
                    AND f.status = 'ATIVO'
                JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo
                JOIN "._DBCARBON."._status s ON s.idstatus = fs.idstatus 
                    AND s.statustipo = '?statustipo?'
            WHERE sg.idsgdoc = '?idsgdoc?'";
    }

    public static function buscarDocumentoParaBioensaio()
    {
        return "SELECT idsgdoc,
                        concat(idsgdoc,'-',titulo) 
                FROM sgdoc d
                WHERE  d.idsgtipodoc = ?idsgtipodoc?
                and d. idempresa = ?idempresa?
                order by idsgdoc";
    }

    public static function buscarDocumentoDoBioensaio()
    {
        return "SELECT d.titulo,
                        a.idsgdoc,
                        a.idbioensaiosgdoc,
                        a.versao,
                        a.revisao,
                        u.idsgdocupd
                FROM sgdoc d
                    JOIN bioensaiosgdoc a on (d.idsgdoc = a.idsgdoc)
                    JOIN sgdocupd u on(u.idsgdoc = d.idsgdoc and u.versao=a.versao)
                WHERE  a.idbioensaio = ?idbioensaio?
                order by d.titulo";
    }

    public static function buscarSgDocPorIdUnidadeEIdEmpresa()
    {
        return "SELECT doc.idsgdoc, doc.titulo, e.sigla, t.rotulo, doc.idempresa
                from sgdoc doc
                JOIN empresa e ON(e.idempresa = doc.idempresa)
                JOIN sgdoctipo t ON(t.idsgdoctipo = doc.idsgdoctipo)
                where doc.idunidade = ?idunidade?
                AND doc.idempresa = ?idempresa?
                order by doc.titulo";
    }

    public static function buscarSgDocDisponiveisParaVinculoEmUnidades()
    {
        return "SELECT sgdoc.idsgdoc, sgdoc.titulo, e.sigla, t.rotulo
                from sgdoc
                JOIN empresa e ON(e.idempresa = sgdoc.idempresa)
                JOIN sgdoctipo t ON(t.idsgdoctipo = sgdoc.idsgdoctipo)
                WHERE (idunidade is null OR idunidade = '')
                AND sgdoc.idempresa = ?idempresa?
                GROUP BY sgdoc.idsgdoc;";
    }

    public static function buscarSgDocDisponiveisParaVinculoEmProdserv()
    {
        return "SELECT idsgdoc,CONCAT('ID ',idregistro,' - ',titulo) as titulo
                from sgdoc
                WHERE idsgdoctipodocumento in (1,3)
                and status = 'APROVADO'
                and idempresa = ?idempresa?";
    }
}
?>