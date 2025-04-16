<?
require_once(__DIR__ . "/_iquery.php");
class ObjetoVinculoQuery implements DefaultQuery
{
    public static $table = 'objetovinculo';
    public static $pk = 'idobjetovinculo';

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function inserirObjetoVinculo()
    {
        return "INSERT INTO objetovinculo (
            idobjeto, tipoobjeto, idobjetovinc, tipoobjetovinc, criadopor,
            criadoem, alteradopor, alteradoem
        ) VALUES (
            ?idobjeto?, '?tipoobjeto?', ?idobjetovinc?, '?tipoobjetovinc?' ,'?criadopor?',
            '?criadoem?', '?alteradopor?', '?alteradoem?'
        )";
    }

    public static function buscarTagsTipoVinculadasAoTagTipo()
    {
        return "SELECT tagtipo AS nome,t.idtagtipo,ov.idobjetovinculo 
            FROM objetovinculo ov 
                JOIN tagtipo t ON (t.idtagtipo = ov.idobjetovinc AND ov.tipoobjetovinc='tagtipo') 
                JOIN empresa e ON (e.idempresa = t.idempresa)
            WHERE ov.tipoobjeto = 'tagtipo'
                AND ov.idobjeto = ?idtagtipo?
            ORDER BY nome";
    }

    public static function buscarTagsTipoLocalizacaoVinculadasAoTagTipo()
    {
        return "SELECT tagtipo AS nome, t.idtagtipo, ov.idobjetovinculo 
            FROM objetovinculo ov 
                JOIN tagtipo t ON (t.idtagtipo = ov.idobjeto AND ov.tipoobjeto='tagtipo') 
                JOIN empresa e ON (e.idempresa = t.idempresa)
            WHERE ov.tipoobjetovinc = 'tagtipo'
                AND ov.idobjetovinc = ?idtagtipo?
        ORDER BY nome";
    }

    public static function buscarIdobjetoVincConcat()
    {
        return "SELECT group_concat(idobjetovinc) AS idobjetovinc FROM objetovinculo WHERE tipoobjeto = '?tipoobjeto?' AND tipoobjetovinc = '?tipoobjetovinc?' AND idobjeto = ?idobjeto?;";
    }

    public static function buscarObjetoVinculoProdservServico()
    {
        return "SELECT 
            *
        FROM
            objetovinculo
        WHERE
            idobjeto = ?idtipoteste?
                AND tipoobjeto = 'prodserv'";
    }

    public static function buscarAgentesVinculadosProdserv()
    {
        return "SELECT 
            p.idprodserv, 
            IFNULL(p.descrcurta, p.descr) AS descr
        FROM
            objetovinculo o
                JOIN
            prodserv p ON (p.idprodserv = o.idobjetovinc)
        WHERE
            o.idobjeto = ?idtipoteste?
                AND o.tipoobjeto = 'prodserv'";
    }


    public static function buscarAgentesVinculados()
    {
        return "SELECT 
            p.idprodserv, p.descr
        FROM
            prodserv p
                JOIN
            unidadeobjeto u ON (u.idunidade = 9
                AND u.idobjeto = p.idprodserv
                AND u.tipoobjeto = 'prodserv')
        WHERE
            p.tipo = 'PRODUTO'
                AND p.status = 'ATIVO'
                AND p.especial = 'Y'
        ORDER BY p.descr";
    }

    public static function buscarAreasDeUmConselhoPorAreaEIdEmpresa()
    {
        return "SELECT c.conselho,ov.idobjeto,a.idsgarea, a.area
                FROM objetovinculo ov
                JOIN sgconselho c ON (ov.idobjeto = c.idsgconselho AND ov.tipoobjeto = 'sgconselho')
                JOIN sgarea a ON (ov.idobjetovinc = a.idsgarea AND ov.tipoobjetovinc = 'sgarea' AND ov.tipoobjeto = 'sgconselho')
                WHERE a.area in (?area?)
                AND a.status = 'ATIVO'
                AND a.idempresa = ?idempresa?";
    }

    public static function buscarSgAreaPorDepartamentoEIdEmpresa()
    {
        return "SELECT 
                    a.area,o.idobjeto,d.idsgdepartamento, d.departamento
                FROM objetovinculo o
                JOIN sgdepartamento d ON (o.idobjetovinc = d.idsgdepartamento)
                JOIN sgarea a ON (o.idobjeto = a.idsgarea)
                WHERE o.tipoobjeto = 'sgarea'
                AND d.departamento IN(?departamento?)
                AND d.status='ATIVO'
                AND d.idempresa = ?idempresa?";
    }

    public static function buscarPorTipoObjetOIdObjetoVincTipoObjetoVinc()
    {
        return "SELECT ov.idobjetovinculo
                FROM objetovinculo ov
                WHERE ov.idobjetovinc = ?idobjetovinc?
                AND ov.tipoobjetovinc = '?tipoobjetovinc?'
                AND ov.tipoobjeto = '?tipoobjeto?'";
    }

    public static function atualizarIdobjetoTipoObjetoPorIdObjetoVinculo()
    {
        return "UPDATE objetovinculo 
                SET idobjeto = ?idobjeto?
                WHERE idobjetovinculo = ?idobjetovinculo?";
    }

    public static function buscarGruposDeAreasDepsSetoresPorIdImGrupo()
    {
        return "SELECT *
                FROM (
                    SELECT o.idobjetovinculo
                        , s.idsgsetor AS id
                        ,CONCAT(e.sigla, ' - SETOR - ',s.setor) AS name
                        ,'sgsetor' AS tipo
                    FROM sgsetor s
                    JOIN empresa e ON(e.idempresa = s.idempresa)
                    JOIN objetovinculo o ON (o.idobjetovinc = s.idsgsetor AND o.tipoobjetovinc = 'sgsetor') AND (o.idobjeto = ?idimgrupo? AND o.tipoobjeto = 'imgrupo') 
                UNION
                    SELECT o.idobjetovinculo
                        , s.idsgarea AS id
                        ,CONCAT(e.sigla, ' - AREA - ',s.area) AS name
                        ,'sgarea' AS tipo
                    FROM sgarea s
                    JOIN empresa e ON(e.idempresa = s.idempresa)
                    JOIN objetovinculo o ON (o.idobjetovinc = s.idsgarea AND o.tipoobjetovinc = 'sgarea') AND (o.idobjeto = ?idimgrupo? AND o.tipoobjeto = 'imgrupo') 
                UNION
                    SELECT o.idobjetovinculo
                        , s.idsgdepartamento AS id
                        ,CONCAT(e.sigla, ' - DEPART. - ',s.departamento) AS name
                        ,'sgdepartamento' AS tipo
                    FROM sgdepartamento s
                    JOIN empresa e ON(e.idempresa = s.idempresa)
                    JOIN objetovinculo o ON (o.idobjetovinc = s.idsgdepartamento AND o.tipoobjetovinc = 'sgdepartamento') AND (o.idobjeto = ?idimgrupo? AND o.tipoobjeto = 'imgrupo' )
                ) a
                ORDER BY name DESC";
    }

    public static function buscarObjetovinculoEObjetosVinculados()
    {
        return "SELECT *
                from 
                    (SELECT idobjetovinculo
                    from objetovinculo
                    where 
                        idobjeto = '?idobjeto?'
                        and tipoobjeto = '?tipoobjeto?'
                        and idobjetovinc = '?idobjetovinc?'
                        and tipoobjetovinc = '?tipoobjetovinc?'
                    union all
                    select idobjetovinculo
                    from objetovinculo
                    where 
                        idobjetovinc = '?idobjeto?'
                        and tipoobjetovinc = '?tipoobjeto?'
                        and idobjeto = '?idobjetovinc?'
                        and tipoobjeto = '?tipoobjetovinc?' ) a";
    }

    public static function buscarGruposDeAreasDepsSetoresDisponiveisParaVinculoPorIdImGrupo()
    {
        return "SELECT *
                FROM (
                    SELECT a.idsgsetor AS id
                        ,CONCAT(e.sigla, ' - SETOR - ',a.setor) AS name
                        ,'sgsetor' AS tipo
                    FROM sgsetor a JOIN empresa e ON e.idempresa = a.idempresa
                    WHERE a.status='ATIVO'
                    AND NOT EXISTS (
                            SELECT 1
                            FROM objetovinculo v
                            WHERE v.tipoobjeto = 'imgrupo' 
                            AND v.tipoobjetovinc = 'sgsetor' 
                            AND v.idobjeto = ?idimgrupo? 
                            AND v.idobjetovinc = a.idsgsetor)
                    UNION
                    SELECT a.idsgarea AS id
                        ,CONCAT(e.sigla, ' - AREA - ',a.area) AS name
                        ,'sgarea' AS tipo
                    FROM sgarea a JOIN empresa e ON e.idempresa = a.idempresa
                    WHERE a.status='ATIVO'
                    AND NOT EXISTS(
                            SELECT 1
                            FROM objetovinculo v
                            WHERE v.tipoobjeto = 'imgrupo' 
                            AND v.tipoobjetovinc = 'sgarea' 
                            AND v.idobjeto = ?idimgrupo? 
                            AND v.idobjetovinc = a.idsgarea)
                    UNION
                    SELECT a.idsgdepartamento AS id
                        ,CONCAT(e.sigla, ' - DEPART. - ',a.departamento) AS name
                        ,'sgdepartamento' AS tipo
                    FROM sgdepartamento a JOIN empresa e ON e.idempresa = a.idempresa
                    WHERE a.status='ATIVO'
                    AND NOT EXISTS(
                            SELECT 1
                            FROM objetovinculo v
                            WHERE v.tipoobjeto = 'imgrupo' 
                            AND v.tipoobjetovinc = 'sgdepartamento' 
                            AND v.idobjeto = ?idimgrupo? 
                            AND v.idobjetovinc = a.idsgdepartamento)
                ) a
                ORDER BY tipo, name desc";
    }

    public static function buscarObjetoVinculoPorTipoObjetoTipoObjetoVinc()
    {
        return "SELECT * FROM objetovinculo WHERE idobjeto = '?idobjeto?' AND tipoobjeto = '?tipoobjeto?' AND tipoobjetovinc = '?tipoobjetovinc?'";
    }

    public static function apagarObjetoVinculoIdObjetoIdObjetoVinc()
    {
        return "DELETE FROM objetovinculo 
                 WHERE idobjeto = '?idobjeto?' AND tipoobjeto = '?tipoobjeto?' AND idobjetovinc = ?idobjetovinc? AND tipoobjetovinc = '?tipoobjetovinc?'";
    }

    public static function buscarPaiPorIdObjetoETipoObjeto()
    {
        return "SELECT *
                FROM ?tipoobjetopai? t
                WHERE EXISTS(
                    SELECT *
                    FROM objetovinculo
                    WHERE idobjeto = t.id?tipoobjetopai?
                    AND tipoobjeto = '?tipoobjetopai?'
                    AND tipoobjetovinc = '?tipoobjeto?'
                    AND idobjetovinc = ?idobjeto?
                );";
    }

    public static function buscarAreasPorIdEmpresaEClausula()
    {
        return "SELECT  o.idobjeto,a.idsgarea, a.area
                FROM objetovinculo o
                JOIN sgarea a ON (o.idobjetovinc = a.idsgarea)
                WHERE o.tipoobjeto = 'sgconselho'
                AND a.status='ATIVO'
                AND a.idempresa = ?idempresa?
                ?clausula?";
    }

    public static function buscarSgDepartamentoPorIdEmpresaEClausula()
    {
        return "SELECT 
                    o.idobjeto,d.idsgdepartamento, d.departamento
                FROM objetovinculo o
                JOIN sgdepartamento d ON (o.idobjetovinc = d.idsgdepartamento)
                WHERE o.tipoobjeto = 'sgarea'
                AND d.status='ATIVO' 
                AND d.idempresa = ?idempresa?
                ?clausula?";
    }

    public static function buscarSgSetorPorIdempresaEClausula()
    {
        return "SELECT  o.idobjeto,s.idsgsetor, s.setor
                FROM objetovinculo o
                JOIN sgsetor s ON (o.idobjetovinc = s.idsgsetor)
                WHERE o.tipoobjeto = 'sgdepartamento'
                AND s.status='ATIVO'
                AND s.idempresa = ?idempresa? 
                ?clausula?";
    }

    public static function deletarVinculoPorIdObjetoETipoObjeto()
    {
        return "DELETE FROM objetovinculo where idobjeto = ?idobjeto? and tipoobjeto = '?tipoobjeto?' and tipoobjetovinc = '?tipoobjetovinc?'";
    }

    public static function buscarResultadoeAmostraVinculadoPorLote()
    {
        return "SELECT r.idresultado, ov.tipoobjeto, a.idamostra
            FROM objetovinculo ov 
                JOIN loteativ l ON l.idloteativ = ov.idobjetovinc 
                    AND ov.tipoobjetovinc = 'loteativ'
                JOIN resultado r ON r.idresultado = ov.idobjeto
                JOIN amostra a ON a.idamostra = r.idamostra
            WHERE l.idlote = '?idlote?'";
    }

    public static function buscarProdservObjetoVinculoPorIdobjetoTipoobjetoTipo()
    {
        return "SELECT o.idobjetovinculo,
                       IF(p.descrcurta = '' OR p.descrcurta IS NULL, p.descr, p.descrcurta) AS descr,
                       p.idprodserv
                  FROM objetovinculo o JOIN prodserv p ON (p.idprodserv = o.idobjetovinc)
                 WHERE o.idobjeto = ?idprodserv?
                   AND o.tipoobjeto = '?tipoobjeto?'
                   AND p.tipo = '?tipo?'
              ORDER BY descr";
    }

    public static function buscarAmostraResultadoVinculadosAtividade()
    {
        return "SELECT a.exercicio,
                       a.idregistro,
                       a.idamostra,
                       r.idresultado,
                       r.idtipoteste,
                       r.status,
                       r.conformidade,
                       uo.idobjeto AS modulo
                  FROM amostra a JOIN resultado r ON r.idamostra = a.idamostra
                  JOIN objetovinculo o ON o.idobjeto = r.idresultado AND o.tipoobjeto = '?tipoobjeto?' AND o.idobjetovinc = ?idobjetovinc? AND o.tipoobjetovinc = '?tipoobjetovinc?'
                  JOIN unidadeobjeto uo ON uo.idunidade = a.idunidade
                  JOIN carbonnovo._modulo m ON m.modulo = uo.idobjeto  AND uo.tipoobjeto = 'modulo' AND m.modulotipo = 'resultado'";
    }

    public static function buscarGruposPorIdObjetoVincETipoObjetoVinc()
    {
        return "SELECT ov.idobjetovinculo, g.idimgrupo, g.grupo
                FROM objetovinculo ov
                JOIN imgrupo g on(g.idimgrupo = ov.idobjeto AND ov.tipoobjeto = 'imgrupo' AND ov.idobjetovinc = ?idobjetovinc? AND ov.tipoobjetovinc = '?tipoobjetovinc?')
                JOIN ?tipoobjetovinc? t on(ov.idobjetovinc = t.id?tipoobjetovinc?)
                AND t.status = 'ATIVO'
                ORDER BY g.grupo;";
    }

    public static function buscarDocsDisponiveisParaVinculo()
    {
        return "SELECT s.idsgdoc, s.titulo 
        FROM sgdoc s
        JOIN empresa e ON(e.idempresa = s.idempresa)
        WHERE s.idempresa = ?idempresa?
        AND NOT EXISTS (
            SELECT 1
            FROM objetovinculo o 
            WHERE o.idobjeto = ?idevento?
            AND o.tipoobjeto = 'evento'
            AND s.idsgdoc = o.idobjetovinc 
            AND o.tipoobjetovinc = 'sgdoc'
        )";
    }

    public static function buscarDocumentosVinculadosPorIdEvento()
    {
        return "SELECT doc.idsgdoc, ov.idobjetovinculo, CONCAT(doc.idregistro, ' - ', doc.titulo) as titulo
                FROM objetovinculo ov
                JOIN sgdoc doc ON ov.idobjetovinc = doc.idsgdoc AND ov.tipoobjetovinc = 'sgdoc' 
                AND ov.tipoobjeto = 'evento' AND ov.idobjeto = ?idevento?;";
    }

    public static function inserirVinculosNoTagTipo()
    {
        return "INSERT INTO objetovinculo (idobjeto, tipoobjeto, idobjetovinc, tipoobjetovinc)
                VALUES(?idTagTipo? ,'tagtipo', ?idTag?, 'tag')";
    }
    public static function removerVinculosNoTagTipo()
    {
        return "DELETE FROM objetovinculo 
                WHERE idobjetovinculo in (
                    SELECT *
                    FROM (
                        SELECT ov.idobjetovinculo as idObjetoVinculoTag
                        FROM prodservvinculo p
                        JOIN tag t ON t.idtag = p.idobjeto AND p.tipoobjeto = 'tagsala'
                        JOIN objetovinculo o on o.idobjeto = p.idprodservvinculo and o.tipoobjeto = 'prodservvinculo' and o.tipoobjetovinc = 'tagtipo'
                        join objetovinculo ov on ov.idobjeto = o.idobjetovinc and ov.tipoobjetovinc = 'tag'
                        WHERE p.idprodserv = ?idprodserv?
                        and ov.idobjeto = ?idTagTipo?
                    ) as tmp
                )";
    }

    public static function buscarVinculosTipoTagPorIdProdserv()
    {
        return "SELECT t.idtag as idProdserv, ov.idobjetovinc as idTag , ov.idobjetovinculo as idObjetoVinculoTag
                FROM prodservvinculo p
                JOIN tag t ON t.idtag = p.idobjeto AND p.tipoobjeto = 'tagsala'
                JOIN objetovinculo o on o.idobjeto = p.idprodservvinculo and o.tipoobjeto = 'prodservvinculo' and o.tipoobjetovinc = 'tagtipo'
                join objetovinculo ov on ov.idobjeto = o.idobjetovinc and ov.tipoobjetovinc = 'tag'
                WHERE p.idprodserv = ?idprodserv?
                and ov.idobjeto in(?idTagTipo?)
                and ov.idobjetovinc in(?idTag?)";
    }

    public static function buscarCargosVinculadosAoSetor()
    {
        return "SELECT
        *
            FROM
                objetovinculo o
            LEFT JOIN
                sgcargo s ON(o.idobjeto = s.idsgcargo)
            LEFT JOIN
                sgsetor ss ON(o.idobjetovinc = ss.idsgsetor)
            WHERE
                o.idobjeto = s.idsgcargo
            AND 
                o.idobjetovinc = ?idsgsetor?
            AND
                o.tipoobjeto = 'sgcargo'";
    }

    public static function buscarCteVinculadasPorIdNf(){
        return "SELECT p.nome, nf.idnf, ov.idobjetovinc as idcte, ov.idobjetovinculo
            from objetovinculo ov
            JOIN nf ON ov.idobjeto = nf.idnf and ov.tipoobjeto = 'nf' AND ov.tipoobjetovinc = 'cte'
            JOIN nf cte ON ov.idobjetovinc = cte.idnf
            join pessoa p ON p.idpessoa = cte.idpessoa
            where nf.idnf = ?idnf?";
    }

    public static function buscarComprasVinculadasPorIdNf(){
        return "SELECT ov.idobjetovinculo, nf.idnf, p.nome, nf.idempresa
                from nf cte
                join objetovinculo ov on cte.idnf  = ov.idobjetovinc AND ov.tipoobjetovinc = 'cte'
                JOIN nf on ov.idobjeto = nf.idnf AND ov.tipoobjeto = 'nf'
                JOIN pessoa p ON p.idpessoa = nf.idpessoa 
                WHERE cte.idnf = ?idnf?";
    }

    public static function buscarTestesPorIdLoteAtiv() {
        return "SELECT ov.*, r.idfluxostatus, r.idamostra
                from objetovinculo ov
                join resultado r on r.idresultado = ov.idobjeto and tipoobjeto = 'resultado'
                where ov.tipoobjetovinc = 'loteativ' 
                and ov.idobjetovinc = ?idloteativ?
                order by r.idamostra";
    }
}
