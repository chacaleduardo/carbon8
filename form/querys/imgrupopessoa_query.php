<?

require_once(__DIR__."/_iquery.php");

class ImGrupoPessoaQuery implements DefaultQuery
{
    public static $table = "imgrupopessoa";
    public static $pk = 'idimgrupopessoa';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarPessoasPorIdImGrupo()
    {
        return "SELECT p.nome,p.idpessoa,f.idimgrupopessoa,p.idtipopessoa, f.inseridomanualmente
                FROM imgrupopessoa f 
                LEFT JOIN pessoa p ON(NOT p.status='INATIVO' AND f.idpessoa = p.idpessoa) 
                WHERE f.idimgrupo = ?idimgrupo?
                order by p.nome";
    }

    public static function buscarGrupoEPessoasPorIdImGrupoPessoa()
    {
        return "SELECT idpessoaemail
                FROM imgrupopessoa p, imgrupo g
                WHERE p.idimgrupo = g.idimgrupo
                AND p.idimgrupopessoa = ?idimgrupopessoa?";
    }

    public static function deletarImGrupoPessoaPorIdImGrupoPessoa()
    {
        return "delete
                    f.*
                from imgrupopessoa d ,immsgconfdest c,immsgconfdest f
                where d.idimgrupopessoa = ?idimgrupopessoa?
                and c.idobjeto = d.idimgrupo
                and c.objeto ='imgrupo'
                and f.inseridomanualmente='N'
                and f.idobjeto=d.idpessoa
                and f.objeto ='pessoa'
                and f.idimmsgconf = c.idimmsgconf";
    }

    public static function buscarPessoasDeUmGrupoQueNaoEstejamVinculadas()
    {
        return "SELECT gp.idempresa, gp.idpessoa
                FROM imgrupopessoa gp 
                LEFT JOIN pessoa p on p.idpessoa = gp.idpessoa
                WHERE gp.idimgrupo = '?idimgrupo?' 
                AND gp.idpessoa NOT IN (
                    SELECT idobjeto FROM fluxostatuspessoa 
                    WHERE idobjeto = gp.idpessoa 
                    AND tipoobjeto = 'pessoa' 
                    AND idmodulo = '?idevento?'
                    AND modulo = 'evento'
                )";
    }

    public static function excluirPessoasComStatusAtivarOuInativarDosGrupos()
    {
        return "DELETE FROM imgrupopessoa 
                WHERE EXISTS (
                    SELECT 1 
                    FROM imgrupo g 
                    WHERE g.status IN ('ATIVAR','INATIVAR') 
                    AND g.idimgrupo = imgrupopessoa.idimgrupo
                )
                AND inseridomanualmente = 'N'";
    }

    public static function inserirOuAtualizarPessoasNoGrupo()
    {
        return "REPLACE INTO imgrupopessoa (
                    idempresa, idimgrupo, idpessoa
                )
                SELECT ?idempresa?, idimgrupo, ?idpessoa?
                FROM imgrupo g 
                WHERE g.status IN ('ATIVAR','INATIVAR')
                AND g.idempresa = ?idempresa?
                AND g.tipoobjetoext = '?tipoobjetoext?' 
                AND g.idobjetoext = ?idobjetoext?";
    }

    public static function inserirOuAtualizarPessoasNoGrupoManual()
    {
        return "REPLACE INTO imgrupopessoa (
                    idempresa, idimgrupo, idpessoa
                )
                SELECT ?idempresa?, idimgrupo, ?idpessoa?
                FROM imgrupo g 
                WHERE g.status IN ('ATIVAR','INATIVAR')
                AND g.idempresa = ?idempresa?
                AND g.idimgrupo = ?idimgrupo?";
    }

    public static function deletarPessoasInativasDeGrupos()
    {
        return "DELETE FROM imgrupopessoa 
                WHERE idpessoa IN (
                    SELECT * 
                    FROM (
			            SELECT idpessoa from pessoa p where p.status = 'INATIVO' and p.idtipopessoa = 1
                    ) a
                )";
    }

    public static function deletarPessoasDoGrupoQueNaoFacamParteDoSetorVinculados()
    {
        return "DELETE FROM imgrupopessoa 
                WHERE idimgrupopessoa IN (
                    SELECT *
                    FROM (
                        SELECT gp.idimgrupopessoa
                        FROM imgrupopessoa gp 
                        JOIN imgrupo g ON(gp.idimgrupo = g.idimgrupo AND g.tipoobjetoext = 'sgsetor')
                        WHERE gp.inseridomanualmente = 'N'
                        AND NOT EXISTS(
                            SELECT 1 
                            FROM pessoaobjeto ps 
                            JOIN sgsetor sg ON(ps.idobjeto = sg.idsgsetor AND sg.status='ATIVO')
                            WHERE ps.idpessoa = gp.idpessoa 
                            AND ps.idobjeto = g.idobjetoext
                            AND ps.tipoobjeto = 'sgsetor'
                            UNION
                            SELECT 1 
                            FROM pessoaobjeto ps 
                            JOIN sgdepartamento sd ON(ps.idobjeto = sd.idsgdepartamento AND sd.status='ATIVO' and ps.tipoobjeto = 'sgdepartamento')
                            JOIN objetovinculo ov ON(ov.idobjeto = sd.idsgdepartamento AND ov.tipoobjeto = 'sgdepartamento' and ov.tipoobjetovinc = 'sgsetor')
                            WHERE ov.idobjetovinc = g.idobjetoext and ps.idpessoa = gp.idpessoa
                            UNION
                            SELECT 1
                            FROM sgsetor s
                            JOIN pessoa p ON(p.idtipopessoa = s.idtipopessoa)
                            WHERE s.idsgsetor = g.idobjetoext
                            AND s.status = 'ATIVO'
                            AND NOT p.status = 'INATIVO'
                        )
                        AND NOT EXISTS (
                            SELECT 1
                            FROM objetovinculo o 
                            JOIN pessoaobjeto fas ON(fas.idobjeto=o.idobjetovinc AND fas.tipoobjeto = 'sgsetor')
                            WHERE o.idobjeto = g.idobjetoext 
                            AND o.tipoobjeto = 'sgsetor')
                        ) a
                    )";
    }

    public static function deletarPessoasDoGrupoQueNaoFacamParteDaAreaVinculada()
    {
        return "DELETE FROM imgrupopessoa 
                WHERE idimgrupopessoa IN (
                    SELECT * 
                    FROM (
                        SELECT gp.idimgrupopessoa
                        FROM imgrupopessoa gp 
                        JOIN imgrupo g on gp.idimgrupo = g.idimgrupo AND g.tipoobjetoext = 'sgarea'
                        WHERE gp.inseridomanualmente = 'N' 
                        AND NOT EXISTS(
                            SELECT 1 
                            FROM pessoaobjeto ps 
                            JOIN sgarea sg ON ps.idobjeto = sg.idsgarea AND sg.status='ATIVO'  
                            WHERE ps.idpessoa = gp.idpessoa AND ps.idobjeto = g.idobjetoext AND ps.tipoobjeto = 'sgarea'
                        ) 
                        AND NOT EXISTS (
                            SELECT 1 
                            FROM objetovinculo o 
                            JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc AND fas.tipoobjeto = 'sgarea'
                            WHERE o.idobjeto = g.idobjetoext AND o.tipoobjeto = 'sgarea'
                        )
			        ) a
                )";
    }

    public static function deletarPessoasDoGrupoQueNaoFacamMaisParteDoDepartamentoVinculado()
    {
        return "DELETE FROM imgrupopessoa 
                WHERE idimgrupopessoa IN (
                    SELECT * 
                    FROM (
                        SELECT gp.idimgrupopessoa 
                        FROM imgrupopessoa gp 
                        JOIN imgrupo g on gp.idimgrupo = g.idimgrupo and g.tipoobjetoext = 'sgdepartamento'
                        WHERE gp.inseridomanualmente = 'N' 
                        AND NOT EXISTS (
                            SELECT 1 
                            FROM pessoaobjeto ps 
                            JOIN sgdepartamento sg ON ps.idobjeto = sg.idsgdepartamento AND sg.status='ATIVO'  
                            WHERE ps.idpessoa = gp.idpessoa 
                            AND ps.idobjeto = g.idobjetoext 
                            AND ps.tipoobjeto = 'sgdepartamento'
                        ) AND NOT EXISTS(
                            SELECT 1 
                            FROM objetovinculo o 
                            JOIN pessoaobjeto po ON po.idobjeto = o.idobjetovinc AND o.tipoobjeto = 'sgdepartamento' 
                            WHERE o.idobjeto = g.idobjetoext AND o.tipoobjeto = 'sgdepartamento')
                )a)";
    }

    public static function deletarPessoasDoGrupoQueNaoFacamParteDoConselhoVinculado()
    {
        return "DELETE FROM imgrupopessoa
                WHERE idimgrupopessoa in (
                    SELECT *
                    FROM (
                        SELECT gp.idimgrupopessoa
                        FROM imgrupo g
                        JOIN imgrupopessoa gp ON(g.idimgrupo = gp.idimgrupo)
                        WHERE NOT EXISTS (
                            SELECT * 
                            FROM (
                                -- CONSELHO
                                SELECT c.idsgconselho, 'sgconselho' as tipoobjeto, po.idpessoa, c.status
                                FROM sgconselho c
                                JOIN pessoaobjeto po ON(po.idobjeto = c.idsgconselho AND po.tipoobjeto = 'sgconselho')
                            ) as qry
                            WHERE qry.idpessoa = gp.idpessoa
                            AND qry.idsgconselho = g.idobjetoext
                            AND qry.status = 'ATIVO'
                            GROUP BY qry.idpessoa
                        )
                        AND g.tipoobjetoext = 'sgconselho'
                        AND g.status = 'ATIVO'
                    ) as del
                );";
    }

    public static function deletarPessoasDoGrupoQueNaoFacamParteDaLpVinculada()
    {
        return "DELETE from imgrupopessoa where idimgrupopessoa in (select * from (
			select gp.idimgrupopessoa from imgrupopessoa gp 
			join imgrupo g on gp.idimgrupo = g.idimgrupo and g.tipoobjetoext = '_lp'
			where gp.inseridomanualmente = 'N' and
			not exists(select 1 from lpobjeto lo where lo.idobjeto = gp.idpessoa and lo.idlp = g.idobjetoext and lo.tipoobjeto = 'pessoa' )
			and
			not exists(select 1 from lpobjeto lo JOIN pessoaobjeto fas on fas.idobjeto=lo.idobjeto and (fas.tipoobjeto = 'sgsetor' or fas.tipoobjeto = 'sgarea'  or fas.tipoobjeto = 'sgdepartamento') and (lo.tipoobjeto = 'sgsetor' or lo.tipoobjeto = 'sgarea'  or lo.tipoobjeto = 'sgdepartamento') where gp.idpessoa=fas.idpessoa)
			)a)";
    }

    public static function deletarPessoasDoGrupoCujoSetorNaoFacaMaisParteDaLp()
    {
        return "DELETE from imgrupopessoa 
                where idimgrupopessoa in (
                    select * from (
                        select gp.idimgrupopessoa from imgrupopessoa gp 
                        join imgrupo g on gp.idimgrupo = g.idimgrupo and g.tipoobjetoext = '_lp'
                        where gp.inseridomanualmente = 'N' and
                        not exists( 
                            select 1 
                            from lpobjeto lo 
                            JOIN pessoaobjeto fas on fas.idobjeto=lo.idobjeto and ( fas.tipoobjeto = 'sgsetor' or fas.tipoobjeto = 'sgarea'  or fas.tipoobjeto = 'sgdepartamento') and (lo.tipoobjeto = 'sgsetor' or lo.tipoobjeto = 'sgarea'  or lo.tipoobjeto = 'sgdepartamento') 
                            where gp.idpessoa=fas.idpessoa
                        )
                    ) a
                );";
    }

    public static function deletarPessoasDoGrupoManualQueNaoFacamParteDoSetorVinculado()
    {
        return "DELETE from imgrupopessoa 
                where idimgrupopessoa in (
                    select * 
                    from (
                        select gp.idimgrupopessoa 
                        from imgrupopessoa gp 
                        join imgrupo g on gp.idimgrupo = g.idimgrupo and g.tipoobjetoext = 'manual'
                        where gp.inseridomanualmente = 'N' 
                        and g.status = 'ATIVO' 
                        and not exists (
                            select 1 
                            from objetovinculo o 
                            JOIN pessoaobjeto po on po.idobjeto = o.idobjetovinc and po.tipoobjeto = 'sgsetor'
                            where o.idobjeto = g.idimgrupo and o.tipoobjeto = 'imgrupo' and o.tipoobjetovinc= 'sgsetor'
                        )
                        and not exists ( 
                            select 1 
                            from objetovinculo o 
                            JOIN pessoaobjeto po on po.idobjeto = o.idobjetovinc and po.tipoobjeto = 'sgdepartamento'
                            where o.idobjeto = g.idimgrupo and o.tipoobjeto = 'imgrupo'
                        ) 
                        and not exists(
                            select 1 
                            from objetovinculo o 
                            JOIN pessoaobjeto po on po.idobjeto = o.idobjetovinc and po.tipoobjeto = 'sgarea'
                            where o.idobjeto = g.idimgrupo and o.tipoobjeto = 'imgrupo'
                        )
                        and not exists(
                            select 1 
                            from objetovinculo o 
                            JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgconselho'
                            where o.idobjeto = g.idimgrupo and o.tipoobjeto = 'imgrupo'
                        ) 
                    ) a
                )";
    }
}

?>