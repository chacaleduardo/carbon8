<?

require_once(__DIR__.'/_iquery.php');

class UnidadeQuery implements DefaultQuery
{
    public static $table = 'unidade';
    public static $pk = 'idunidade';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarUnidadesDisponiveisPorUnidadeObjeto(){
        return "SELECT 
                u.idunidade
                ,u.unidade
                ,o.idunidadeobjeto 
                ,o.criadopor
                ,o.criadoem
            FROM unidade u 
                LEFT JOIN unidadeobjeto o ON(o.idunidade = u.idunidade AND o.tipoobjeto  = '?tipoobjeto?' AND o.idobjeto = ?idobjeto?)
            WHERE u.status = 'ATIVO'  
            ?andidempresa?
            ?andidtipounidade?
             ORDER BY o.idunidadeobjeto desc,u.unidade";
    }

    public static function buscarUnidadesPorUnidadeObjeto(){
        return "SELECT 
                u.idunidade
                ,u.unidade
                ,o.idunidadeobjeto 
                ,o.criadopor
                ,o.criadoem
            FROM unidade u 
                 JOIN unidadeobjeto o ON(o.idunidade = u.idunidade AND o.tipoobjeto  = '?tipoobjeto?' AND o.idobjeto = ?idobjeto?)
            WHERE u.status = 'ATIVO'  
            ?andidempresa?
            ?andidtipounidade?
            ORDER BY u.unidade";
    }

    public static function buscarUnidadesDisponiveisPorUnidadeObjetoSemVincComIdObjeto()
    {
        return "SELECT 
                    unidade.idempresa, unidade.idunidade, unidade.unidade
                FROM unidade
                JOIN empresa e ON (e.idempresa = unidade.idempresa)
                JOIN unidadeobjeto ON (unidadeobjeto.idunidade = unidade.idunidade)
                WHERE unidadeobjeto.idunidade NOT IN (
                    SELECT uo2.idunidade
                    FROM unidadeobjeto uo2
                    WHERE uo2.idobjeto = ?idobjeto?
                    AND uo2.tipoobjeto = '?tipoobjeto?'
                )
                AND e.status = 'ATIVO'
                AND unidade.status = 'ATIVO'
                AND unidade.idempresa = ?idempresa?
                GROUP BY unidade.idunidade";
    }

    public static function buscarUnidadesPorIdEmpresa()
    {
        return "SELECT idunidade, unidade
                FROM unidade
                WHERE status = 'ATIVO' 
                AND idempresa = ?idempresa?
                UNION 
                SELECT idunidade, if(status = 'INATIVO', concat(unidade,' (Inativo)'), unidade) AS unidade
                FROM unidade
                WHERE idunidade = '?idunidade?'";
    }

    public static function buscarUnidadesDisponiveisParaVinculoPorIdSgsetorEIdEmpresa()
    {
        // Pegar todas as unidades que nao tenham vinculo
        return "SELECT u.idempresa, u.idunidade, u.unidade
                FROM unidade u
                WHERE u.status = 'ATIVO' AND u.idtipounidade !=28
    /*MAF: Esta restrição estava impedindo a Paula de configurar unidades na tela sgsetor.php. Removido temporariamente
                AND NOT EXISTS (
                    SELECT 1
                    FROM unidadeobjeto uo 
                    WHERE uo.idunidade = u.idunidade
                    AND uo.tipoobjeto IN('sgsetor','sgdepartamento','sgarea','sgconselho')
                )
    */
                 ?idempresa?
                UNION
                -- Pegar unidades dos irmaos de uma unidade
                SELECT u.idempresa, u.idunidade, u.unidade
                FROM objetovinculo ov
                JOIN objetovinculo ov2 ON(ov.idobjeto = ov2.idobjeto AND ov.tipoobjeto = 'sgdepartamento' AND ov2.tipoobjeto = 'sgdepartamento')
                JOIN unidadeobjeto uo on uo.idobjeto = ov2.idobjetovinc and ov2.tipoobjetovinc = 'sgsetor'
                JOIN unidade u on uo.idunidade = u.idunidade and uo.tipoobjeto = 'sgsetor' and u.status='ATIVO' AND u.idtipounidade !=28
                ?where?
                ?idempresa?
                AND NOT EXISTS(
                    SELECT 1
                    FROM unidadeobjeto
                    WHERE idobjeto = ov.idobjetovinc
                    AND tipoobjeto = 'sgsetor'
                    AND u.idunidade = idunidade
                ) group by idunidade ORDER BY unidade ";
    }
    

    public static function buscarUnidadesDisponiveisParaVinculo()
    {
        return "SELECT u.idempresa, u.idunidade, u.unidade
                FROM unidade u
                JOIN empresa e ON(u.idempresa = e.idempresa)
                WHERE u.status = 'ATIVO' 
                ?filtroempresa?
                ?idempresa?
                ORDER BY e.sigla, u.unidade";
    }
    public static function buscarUnidadesDisponiveisParaVinculoEnvio()
    {
        return "SELECT u.idempresa, u.idunidade, u.unidade
                FROM unidade u
                JOIN empresa e ON(u.idempresa = e.idempresa)
                WHERE u.status = 'ATIVO' 
                AND  EXISTS (
                    SELECT 1
                    FROM unidadeobjeto uo 
                    WHERE uo.idunidade = u.idunidade
                    and uo.padrao='Y'
                    AND uo.tipoobjeto IN('sgsetor','sgdepartamento','sgarea','sgconselho')
                )
                ?idempresa?
                ORDER BY e.sigla, u.unidade";
    }
    

    public static function buscarUnidadesAtivasPorIdEmpresa(){
        return "SELECT *
                FROM unidade 															
                WHERE status = 'ATIVO' 
                AND idempresa = ?cbidempresa?
                ORDER BY unidade";
    }

    public static function buscarModuloDaUnidadePorIdunidade()
    {
        return "SELECT m.modulo
                FROM  unidadeobjeto u 
                JOIN "._DBCARBON."._modulo m on (u.idobjeto = m.modulo)
                WHERE
                    u.tipoobjeto = 'modulo' 
                    and m.modulotipo = 'lote'
                    and u.idempresa = 1 
                    and u.idunidade = ?idunidade?";
    }

    public static function buscarUnidadePorIdEventoTipo()
    {
        return "SELECT 
                    qry_distinct.idunidade, 
                    qry_distinct.unidade,
                    qry_distinct.idobjeto,
                    qry_distinct.tipoobjeto,
                    qry_distinct.idunidadeobjeto 
                FROM (
                        SELECT u.idunidade, u.unidade, unidadeobjeto.idobjeto, 'eventotipo' as tipoobjeto, unidadeobjeto.idunidadeobjeto 
                        FROM unidadeobjeto
                        JOIN unidade u ON(u.idunidade = unidadeobjeto.idunidade)
                        WHERE u.idempresa = ?idempresa?
                        AND unidadeobjeto.idobjeto = ?ideventotipo?
                        AND unidadeobjeto.tipoobjeto = 'eventotipo'
                    ) as qry_distinct
                GROUP BY qry_distinct.idunidade";
    }

    public static function buscarConselhosAreasDepsSetoresPorIdUnidade()
    {
        return "SELECT
                    uo.idunidadeobjeto, uo.idobjeto, uo.tipoobjeto,
                        CASE
                            WHEN uo.tipoobjeto = 'sgconselho' then CONCAT(ec.sigla, ' - ', c.conselho)
                            WHEN uo.tipoobjeto = 'sgarea' then CONCAT(ea.sigla, ' - ', a.area)
                            WHEN uo.tipoobjeto = 'sgdepartamento' then CONCAT(ed.sigla, ' - ', sgdep.departamento)
                            WHEN uo.tipoobjeto = 'sgsetor' then CONCAT(es.sigla, ' - ', s.setor)
                            WHEN uo.tipoobjeto = 'pessoas' then CONCAT(ep.sigla, ' - ', p.nome)
                        END
                     as label
                FROM unidade u
                JOIN unidadeobjeto uo ON(uo.idunidade = u.idunidade)
                LEFT JOIN sgconselho c ON(c.idsgconselho = uo.idobjeto AND uo.tipoobjeto = 'sgconselho')
                LEFT JOIN empresa ec ON(ec.idempresa = c.idempresa)
                LEFT JOIN sgarea a ON(a.idsgarea = uo.idobjeto AND uo.tipoobjeto = 'sgarea')
                LEFT JOIN empresa ea ON(ea.idempresa = a.idempresa)
                LEFT JOIN sgdepartamento sgdep ON(sgdep.idsgdepartamento = uo.idobjeto AND uo.tipoobjeto = 'sgdepartamento')
                LEFT JOIN empresa ed ON(ed.idempresa = sgdep.idempresa)
                LEFT JOIN sgsetor s ON(s.idsgsetor = uo.idobjeto AND uo.tipoobjeto = 'sgsetor')
                LEFT JOIN empresa es ON(es.idempresa = s.idempresa)
                LEFT JOIN pessoa p ON(p.idpessoa = uo.idobjeto AND uo.tipoobjeto = 'pessoas')
                LEFT JOIN empresa ep ON(ep.idempresa = p.idempresa)
                WHERE u.idunidade = ?idunidade?
                AND uo.tipoobjeto in('sgconselho', 'sgarea', 'sgdepartamento', 'sgsetor', 'pessoas')";
    }

    public static function buscarIdunidadePorTipoUnidade()
    {
        return "SELECT idunidade FROM unidade WHERE idtipounidade = ?idtipounidade? AND idempresa = ?idempresa? AND status = 'ATIVO'";
    }

    public static function buscarIdunidadePorTipoUnidadeDescricao()
    {
        return "SELECT idunidade FROM unidade WHERE idtipounidade = ?idtipounidade? AND unidade like ('?unidade?') AND idempresa = ?idempresa? AND status = 'ATIVO'";
    }


    public static function deletarUnidadesPorIdObjetoETipoObjeto()
    {
        return "DELETE FROM unidadeobjeto
                WHERE idobjeto = ?idobjeto?
                AND tipoobjeto = '?tipoobjeto?'";
    }

    public static function buscarGruposConcatenadosUnidadeObjetoVinculo()
    {
        return "SELECT group_concat(u.idunidade) as idunidades 
                  FROM unidade u JOIN objetovinculo ov ON (ov.idobjetovinc = u.idunidade AND ov.tipoobjetovinc = '?tipoobjetovinc?' AND ov.tipoobjeto = '?tipoobjeto?')
                 WHERE ov.idobjeto in  (?idobjeto?) and u.idempresa = ?idempresa?;";
    }

    public static function buscarUnidadeAtivaPorIdUnidade()
    {
        return "SELECT u.idunidade,u.unidade
                  FROM unidade u
                 WHERE u.status = 'ATIVO' 
                   AND idunidade IN (?idunidades?)";
    }

    public static function listarFillSelectUnidadeAtivo()
    {
        return "SELECT u.idunidade,u.unidade
                  FROM unidade u
                 WHERE u.status='ATIVO' 
                 ?whereUnidade?
                 ?idempresa?
        ORDER BY unidade";
    }

    public static function buscarGrupoConcatPessoaUnidade()
    {
        return "SELECT group_concat(idunidade) AS idunidade FROM vw8PessoaUnidade WHERE idpessoa = ?idpessoa?";
    }

    public static function buscarUnidadePorIdtipoIdempresa()
    {
        return "SELECT 
                    idunidade
                FROM
                    unidade
                WHERE
                    idtipounidade in (?idtipounidade?) AND status = 'ATIVO'
                        AND idempresa =?idempresa?";
    }

    public static function buscarUnidadesPorTipoObjeto(){
        return "SELECT u.idunidade,
                        u.unidade,
                        o.idunidadeobjeto 
                  FROM unidade u LEFT JOIN unidadeobjeto o ON(o.idunidade = u.idunidade AND o.tipoobjeto  = '?tipoobjeto?' AND o.idobjeto = ?idobjeto?)
                 WHERE u.status = 'ATIVO'  
                 ?idempresa?
              ORDER BY u.unidade";
    }

    public static function buscarIdUnidadesPorIdObjetoTipoObjetoEIdEmpresa()
    {
        return "SELECT group_concat(u.idunidade) as idunidades 
                FROM unidade u 
                JOIN objetovinculo ov ON (ov.idobjetovinc = u.idunidade AND ov.tipoobjetovinc = 'unidade' AND ov.tipoobjeto = '?tipoobjeto?')
                where ov.idobjeto in  (?idobjeto?) 
                and u.idempresa = ?idempresa?
                AND u.requisicao = 'Y'";
    }

    public static function buscarUnidadesPorClausulaUnidadeGetIdEmpresaEUnion()
    {
        return "SELECT u.idunidade, u.unidade
                FROM unidade u
                WHERE u.status = 'ATIVO'
                ?clausulaunidade?
                ?getidempresa?
                ?union?
                ORDER BY unidade";
    }

    public static function buscarUnidadeDoModuloPorIdModuloGetIdEmpresa()
    {
        return "SELECT o.idunidade
                FROM unidadeobjeto o 
                JOIN unidade u on(u.idunidade = o.idunidade ?getidempresa? and u.status='ATIVO')
                WHERE o.idobjeto='?idobjeto?' 
                AND o.tipoobjeto = 'modulo'";
    }

    public static function buscarTagsPorIdTagClassIdTagTipoEIdUnidade()
    {
        return "SELECT  t.idtag, t.descricao
                FROM unidade u
                JOIN tag t
                WHERE t.idtagclass = ?idtagclass?
                AND t.idtagtipo in (?idtagtipo?)
                AND  t.idunidade = ?idunidade?
                GROUP BY t.descricao
                ORDER BY t.descricao";
    }

    public static function buscarTagsPorIdTagTipoEIdUnidade()
    {
        return "SELECT t.idtag, t.descricao
                FROM unidade u  
                JOIN tag t
                WHERE t.idtagtipo IN (
                    SELECT t.idtagtipo
                    FROM objetovinculo ov 
                    JOIN tagtipo t ON (t.idtagtipo = ov.idobjeto AND ov.tipoobjeto='tagtipo') 
                    JOIN empresa e ON (e.idempresa = t.idempresa)
                    WHERE ov.tipoobjetovinc='tagtipo' 
                    AND ov.idobjetovinc = ?idtagtipo?
                ) 
                AND t.idunidade = ?idunidade?
                GROUP BY t.descricao
                ORDER BY t.descricao";
    }

    public static function buscarUnidadeParaExamesBioterio()
    {
        return "SELECT case when t.idtipounidade = 12
                        then  10
                    when t.idtipounidade = 17
                        then 14
                    else 10
                end as destino
                from unidade u
                join tipounidade t on (u.idtipounidade = t.idtipounidade)
                where 1 ?getidempresa?";
    }

    public static function vw8FuncionarioUnidadePorIdPessoaIdEmpresa()
    {
        return "SELECT idunidade FROM vw8FuncionarioUnidade WHERE idpessoa = ?idpessoa? AND idempresa = ?idempresa?";
    }

    public static function buscarUnidadeModuloPorTipoObjetoParaLote()
    {
        return "SELECT m.modulo, u.unidade
                  FROM unidade u JOIN unidadeobjeto o ON (u.idunidade = o.idunidade)
                  JOIN "._DBCARBON."._modulo m ON (o.idobjeto = m.modulo)
                 WHERE u.status = 'ATIVO'
                   AND m.status = 'ATIVO'
                   ?idempresa?
                   AND u.idunidade = ?idunidadeest?
                   AND m.modulo LIKE 'lote%'
                   AND o.tipoobjeto = 'modulo'
                   AND o.idobjeto NOT IN ('lotealertavendas' , 'loteemalertavendasproducao')
              GROUP BY m.modulo";
    }

    public static function buscarUnidadePorIdUnidade()
    {
        return "SELECT idunidade, unidade
                FROM unidade
                WHERE idunidade = ?idunidade?";
    }

    public static function listarUnidadesDisponiveisParaVinculo()
    {
        return "SELECT   u.idempresa, u.idunidade, u.unidade
                FROM unidade u
                JOIN empresa e ON(u.idempresa = e.idempresa)
                WHERE u.status = 'ATIVO' 
                AND NOT EXISTS (
                    SELECT 1
                    FROM unidadeobjeto uo 
                    WHERE uo.idunidade = u.idunidade
                    AND uo.tipoobjeto IN('sgdepartamento')
                    AND uo.idobjeto = ?idsgdepartamento?
                )
                AND NOT EXISTS(
                    SELECT 1
					FROM unidadeobjeto  uo
					JOIN sgsetor s ON(s.idsgsetor = uo.idobjeto AND uo.tipoobjeto = 'sgsetor')  
					JOIN objetovinculo ov ON(ov.idobjetovinc = s.idsgsetor and ov.tipoobjetovinc = 'sgsetor' and ov.tipoobjeto = 'sgdepartamento')   
					WHERE  ov.idobjeto = ?idsgdepartamento?
                    AND uo.idunidade = u.idunidade
					AND u.status = 'ATIVO'

                )
                ?idempresa?
                ORDER BY e.sigla, u.unidade";
    }

    public static function buscarUnidadeDeProducaoPorIdEmpresa() {
        return "SELECT *
                FROM unidade
                WHERE idempresa = ?idempresa?
                AND status = 'ATIVO'
                AND producao = 'Y'";
    }

    public static function buscarUnidadesPorTipoObjetoModulo(){
        return "SELECT * FROM(SELECT u.idunidade,
                                     u.unidade
                                FROM unidade u LEFT JOIN unidadeobjeto o ON(o.idunidade = u.idunidade)
                                JOIN fluxo f ON f.modulo = o.idobjeto
                                WHERE u.status = 'ATIVO' AND o.tipoobjeto = 'modulo'
                                ?idempresa?
                                GROUP BY u.unidade
                        UNION 
                                SELECT u.idunidade,
                                       u.unidade
                                FROM unidade u JOIN prodserv p ON p.idunidadeest = u.idunidade
                                WHERE p.idprodserv = ?idprodserv?) as u
                        ORDER BY unidade";
    }

}
?>