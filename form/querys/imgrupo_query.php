<?

require_once(__DIR__."/_iquery.php");

class ImGrupoQuery implements DefaultQuery
{
    public static $table = "imgrupo";
    public static $pk = 'idimgrupo';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function atualizarStatusPorIdObjetoExt()
    {
        return "UPDATE imgrupo SET status = '?status?' where idobjetoext = ?idobjetoext?;";
    }

    public static function atualizarStatusDeGruposDeSetores()
    {
        return "UPDATE imgrupo g
                JOIN sgsetor s ON(s.idsgsetor = g.idobjetoext AND tipoobjetoext = 'sgsetor')
                SET g.status =  s.status WHERE NOT g.status = s.status;";
    }

    public static function atualizarNomeDosGruposDeAcordoComOSetor()
    {
        return "UPDATE  imgrupo g
                JOIN sgsetor s ON(s.idsgsetor = g.idobjetoext AND tipoobjetoext = 'sgsetor' AND NOT g.grupo =  s.setor)
                SET g.grupo =  s.setor;";
    }

    public static function inativarStatusDeGruposDesfeitosDeSetores()
    {
        return "UPDATE imgrupo g
                JOIN sgsetor s ON(s.idsgsetor = g.idobjetoext AND tipoobjetoext = 'sgsetor' AND NOT g.grupo =  s.setor)
                SET g.grupo =  s.setor, g.status = 'INATIVO' WHERE NOT g.grupo = 'Y'";
    }

    public static function atualizarGruposDeSetoresVinculados()
    {
        return "UPDATE imgrupo g
                JOIN sgsetor s ON (s.idsgsetor = g.idobjetoext AND tipoobjetoext = 'sgsetor' AND NOT g.grupo =  s.setor)
                SET g.grupo =  s.setor";
    }

    public static function atualizarGruposAtivosDeSetores()
    {
        return "UPDATE imgrupo SET status = 'ATIVO' WHERE idobjetoext IN (SELECT idsgsetor FROM sgsetor WHERE status = 'ATIVO') AND tipoobjetoext = 'sgsetor'";
    }

    public static function atualizarGruposInativosDeSetores()
    {
        return "UPDATE imgrupo SET status = 'INATIVO' WHERE idobjetoext IN (SELECT idsgsetor FROM sgsetor WHERE (status = 'INATIVO' OR (grupo != 'Y' && grupolideranca != 'Y'))) AND tipoobjetoext = 'sgsetor'";
    }

    public static function buscarGruposPorIdEmailVirtualConf()
    {
        return "SELECT e.idemailvirtualconfimgrupo, CONCAT(es.sigla, ' - ', p.grupo) AS grupo,p.idimgrupo
                FROM imgrupo p 
                JOIN emailvirtualconfimgrupo e ON(e.idimgrupo = p.idimgrupo and e.idemailvirtualconf = ?idemailvirtualconf?)
                JOIN empresa es ON(es.idempresa = p.idempresa)
                WHERE 1
                AND p.status = 'ATIVO'";
    }

    public static function buscarGruposPorIdImGrupoEGetIdEmpresa()
    {
        return "SELECT i.idimgrupo, i.grupo
                FROM imgrupo i 
                WHERE i.status	= 'ATIVO'
                AND	i.idimgrupo	in (?idimgrupo?)
                ?getidempresa?";
    }

    public static function buscarGruposDisponiveisParaVinculoNoEvento()
    {
        return "SELECT idimgrupo, grupo
                FROM imgrupo g
                WHERE 1
                ?getidempresa?
                AND status='ATIVO'
                AND NOT EXISTS
                (
                    SELECT 1
                    FROM fluxostatuspessoa r
                    WHERE r.idmodulo = '?idevento?' 
                    AND r.modulo = 'evento'
                    AND r.tipoobjeto ='imgrupo'
                    AND g.idimgrupo = r.idobjeto
                )
                ORDER BY grupo ASC";
    }

    public static function buscarGruposAtivosPorIdImGrupoEGetIdEmpresa()
    {
        return "SELECT
                    i.idimgrupo,
                    i.grupo,
                    i.idempresa
                FROM imgrupo i 
                WHERE i.status	= 'ATIVO'
                AND	i.idimgrupo	in (?idimgrupo?)
                ?getidempresa?";
    }

    public static function atualizarNomeDosGruposDeAcordoComOVinculo()
    {
        return "UPDATE imgrupo g 
                JOIN ?banco?.?tabela? t ON t.?chaveprimaria? = g.idobjetoext AND tipoobjetoext = '?tabela?' AND NOT g.grupo = t.?colunadescricao?
                SET g.grupo =  t.?colunadescricao?";
    }

    public static function ativarGruposCujoVinculoEstejaAtivoEDefinidoParaGerarGrupo()
    {
        return "UPDATE imgrupo 
                SET status = 'ATIVO' 
                WHERE tipoobjetoext = '?tabela?' 
                AND idobjetoext IN (
                    SELECT ?chaveprimaria? 
                    FROM ?banco?.?tabela? 
                    WHERE (status = 'ATIVO' AND grupo = 'Y')
                )";
    }

    public static function inativarGruposCujoVinculoEstejaInativoEDefinidoParaNaoGerarGrupo()
    {
        return "UPDATE imgrupo 
                SET status = 'INATIVO' 
                WHERE tipoobjetoext = '?tabela?' 
                AND idobjetoext IN (
                    SELECT ?chaveprimaria? 
                    FROM ?banco?.?tabela? 
                    WHERE (status = 'INATIVO' OR grupo != 'Y')
                )";
    }

    public static function buscarPessoasVinculadasManualmente()
    {
        return "SELECT DISTINCT 
                    a.idempresa
                    , p.idpessoa
                    , p.nomecurto
                    , CONCAT(e.sigla, ' - ', a.grupo) as grupo
                    , a.descr as descr
                    -- , if(s.idsgsetor, s.idsgsetor, if(l.idlp, l.idlp, a.idimgrupo))  as idobjetoext
                    ,a.idimgrupo as idobjetoext
                    -- , if(s.idsgsetor, 'sgsetor', if(l.idlp, '_lp', 'imgrupo'))   as tipoobjetoext  
                    ,'manual' as tipoobjetoext
                    , a.grupolideranca
                    , fas.responsavel
                FROM imgrupo a									
                    JOiN objetovinculo o on o.idobjeto = a.idimgrupo and o.tipoobjeto = 'imgrupo'
                    JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgsetor' and o.tipoobjetovinc = 'sgsetor'
                    JOIN sgsetor sg ON fas.idobjeto = sg.idsgsetor AND NOT sg.status='INATIVO'
                    JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO'
                    JOIN empresa e ON e.idempresa = a.idempresa
                    WHERE a.tipoobjetoext = 'manual'
                UNION
                SELECT DISTINCT 
                    a.idempresa
                    , p.idpessoa
                    , p.nomecurto
                    , CONCAT(e.sigla, ' - ', a.grupo) as grupo
                    , a.descr as descr
                    -- , if(s.idsgarea, s.idsgarea, if(l.idlp, l.idlp, a.idimgrupo))  as idobjetoext
                    ,a.idimgrupo as idobjetoext
                    -- , if(s.idsgarea, 'sgarea', if(l.idlp, '_lp', 'imgrupo'))   as tipoobjetoext  
                    ,'manual' as tipoobjetoext  
                    , a.grupolideranca                                                    
                    , fas.responsavel
                FROM imgrupo a									
                    JOiN objetovinculo o on o.idobjeto = a.idimgrupo and o.tipoobjeto = 'imgrupo'
                    JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgarea' and o.tipoobjetovinc = 'sgarea'
                    JOIN sgarea sa ON fas.idobjeto = sa.idsgarea AND NOT sa.status='INATIVO'
                    JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO'
                    JOIN empresa e ON e.idempresa = a.idempresa
                    WHERE a.tipoobjetoext = 'manual'
                UNION
                SELECT DISTINCT 
                    a.idempresa
                    , p.idpessoa
                    , p.nomecurto
                    , CONCAT(e.sigla, ' - ', a.grupo) as grupo
                    , a.descr as descr
                    -- , if(s.idsgdepartamento, s.idsgdepartamento, if(l.idlp, l.idlp, a.idimgrupo))  as idobjetoext
                    ,a.idimgrupo as idobjetoext
                    -- , if(s.idsgdepartamento, 'sgdepartamento', if(l.idlp, '_lp', 'imgrupo'))   as tipoobjetoext  
                    ,'manual' as tipoobjetoext   
                    , a.grupolideranca 
                    , fas.responsavel                                                  
                FROM imgrupo a									
                    JOiN objetovinculo o on o.idobjeto = a.idimgrupo and o.tipoobjeto = 'imgrupo'
                    JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgdepartamento' and o.tipoobjetovinc = 'sgdepartamento'
                    JOIN sgdepartamento sd ON fas.idobjeto = sd.idsgdepartamento AND NOT sd.status='INATIVO'
                    JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO'
                    JOIN empresa e ON e.idempresa = a.idempresa
                    WHERE a.tipoobjetoext = 'manual'";
    }

    public static function inativarGruposDoOrganogramaPorTipoObjeto()
    {
        return "UPDATE imgrupo SET status = 'INATIVAR' WHERE inseridomanualmente='N' AND NOT status = 'INATIVO' AND tipoobjetoext = '?tipoobjeto?'";
    }

    public static function ativarGruposDoOrganogramaPorIdObjetoETipoObjeto()
    {
        return "UPDATE imgrupo SET status='ATIVAR' 
                WHERE idempresa = ?idempresa? 
                AND NOT status = 'INATIVO' 
                AND idobjetoext = ?idobjeto?
                AND tipoobjetoext = '?tipoobjeto?'";
    }

    public static function ativarGruposManuais()
    {
        return "UPDATE imgrupo SET status='ATIVAR' 
                WHERE idempresa = ?idempresa? 
                AND NOT status = 'INATIVO' 
                AND idimgrupo = ?idimgrupo?";
    }

    public static function inserirGrupoCasoNaoExista()
    {
        return "INSERT INTO imgrupo (
            idempresa, grupo, idobjetoext, tipoobjetoext, descr, status,
            criadopor, criadoem, alteradopor, alteradoem
        )
        SELECT * 
        FROM ( 
            SELECT ?idempresa? as ide,
            '?grupo?' as gr,
            '?idobjeto?' as idex,
            '?tipoobjeto?' as te,
            '?descricao?' as de,
            'ATIVO' as st,
            null as cr,
            now() as ce,
            null as ar,
            now() as ae
        ) AS tmp
        WHERE NOT EXISTS (
            SELECT 1 
            FROM imgrupo 
            WHERE idempresa = ?idempresa? 
            AND idobjetoext = '?idobjeto?' 
            AND tipoobjetoext='?tipoobjeto?'
        )";
    }

    public static function deletarGruposComStatusInativar()
    {
        return "DELETE FROM imgrupo WHERE status='INATIVAR' AND inseridomanualmente='N'";
    }

    public static function ativarPessoasComStatusAtivar()
    {
        return "UPDATE imgrupo SET status = 'ATIVO' WHERE status = 'ATIVAR'";
    }

    public static function buscarGruposPorIdObjetoExtETipoObjetoExt()
    {
        return "SELECT *
                FROM imgrupo
                WHERE idobjetoext = ?idobjetoext?
                AND tipoobjetoext = '?tipoobjetoext?'";
    }

    public static function buscarGruposNaoVinculadosAoAlerta()
    {
        return "SELECT s.idimgrupo,s.grupo
                from  imgrupo s 
                where s.status='ATIVO' 
                
                    and not exists(
                            SELECT 1
                            FROM immsgconfdest v
                            where  v.idimmsgconf= ?idimmsgconf?
                                and v.objeto ='imgrupo'
                                and s.idimgrupo = v.idobjeto				
                    ) ?getidempresa? 
                order by s.grupo";
    }

    public static function buscarGruposVinculadosAoAlerta()
    {
        return "SELECT d.idimmsgconfdest,s.grupo,s.idimgrupo,d.criadopor,d.criadoem,s.status
                from immsgconfdest d,imgrupo s
                where s.idimgrupo = d.idobjeto
                    and d.objeto ='imgrupo'
                    and d.idimmsgconf =  ?idimmsgconf?
                order by s.grupo";
    }

    public static function buscarGruposDisponiveisParaVinculoPorIdObjetoVincTipoObjetoVincEGetIdEmpresa()
    {
        return "SELECT g.idimgrupo, g.grupo
                FROM imgrupo g
                WHERE g.status='ATIVO'
                ?getidempresa?
                AND NOT EXISTS (
                    SELECT 1
                    FROM objetovinculo v
                    where  
                        v.tipoobjeto = 'imgrupo' AND
                        v.tipoobjetovinc = '?tipoobjetovinc?' AND	
                        v.idobjeto = g.idimgrupo AND
                        v.idobjetovinc = ?idobjetovinc?
                    )
                ORDER BY g.grupo";
    }
}

?>