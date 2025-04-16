<?

class SgDepartamentoQuery
{
    public static $table = 'sgdepartamento';
    public static $pk = 'idsgdepartamento';

    private const buscarPorChavePrimariaSQLPadrao = " SELECT * 
                                                FROM ?table? t
                                                JOIN empresa e ON(t.idempresa = e.idempresa)
                                                WHERE ?pk? in (?pkval?)
                                                AND t.status = '?status?'";

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQLPadrao,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarLpGrupoPorIdSgDepartamento()
    {
        return "SELECT lpo.idlpobjeto, lp.idlp, CONCAT(e.sigla,' - ',lp.descricao) as descricao, lpg.lpgrupopar as idlpgrupopai, lpg.idlpgrupo as idlpgrupofilho, lpg.descricao as descricaogrupofilho, lpgpai.descricao as descricaogrupopai
                FROM lpobjeto lpo
                JOIN carbonnovo._lp lp ON(lp.idlp = lpo.idlp)
                LEFT JOIN empresa e on (e.idempresa = lp.idempresa)
                JOIN sgdepartamento sgdep ON(sgdep.idsgdepartamento = lpo.idobjeto AND lpo.tipoobjeto = 'sgdepartamento')
                JOIN carbonnovo._lpobjeto clpo ON(clpo.idlp = lp.idlp AND clpo.tipoobjeto = 'lpgrupo')
                JOIN carbonnovo._lpgrupo lpg ON(lpg.idlpgrupo = clpo.idobjeto)
                JOIN carbonnovo._lpgrupo lpgpai ON(lpgpai.idlpgrupo = lpg.lpgrupopar)
                WHERE sgdep.idsgdepartamento = ?idsgdepartamento?
                AND lp.status = 'ATIVO'
                AND lpg.status = 'ATIVO'
                GROUP BY lp.idlp
                ORDER BY e.sigla, lp.descricao ASC";
    }

    public static function buscarTodosSetoresDeUmDepartamento()
    {
        return "SELECT * from sgsetor se where status='ATIVO' and se.idsgdepartamento = ?idsgdepartamento?";
    }

    public static function buscarSgDepartamentoPorIdSgDepartamento()
    {
        return "SELECT * FROM sgdepartamento where status = 'ATIVO' and  idsgdepartamento = ?idsgdepartamento?";
    }

    public static function buscarSgDepartamentoPorIdSgareaEGetIdEmpresa()
    {
        return "SELECT 
                    CONCAT(e.sigla, ' - ', sd.departamento) as departamento, 
                    sd.idsgdepartamento, 
                    ov.idobjetovinculo
                FROM sgdepartamento sd
                JOIN empresa e ON(e.idempresa = sd.idempresa)
                INNER JOIN objetovinculo ov ON sd.idsgdepartamento = ov.idobjetovinc AND tipoobjetovinc = 'sgdepartamento'
                WHERE ov.idobjeto = '?idsgarea?'
                AND sd.status = 'ATIVO'
                ORDER BY sd.departamento ASC";
    }

    public static function buscarDepartamentosDisponiveisParaVinculoPorIdSgareaEGetIdEmpresa()
    {
        return "SELECT 
                    sgdep.idsgdepartamento, 
                    CONCAT(e.sigla, ' - ', sgdep.departamento) as departamento
                FROM sgdepartamento sgdep
                JOIN empresa e ON(e.idempresa = sgdep.idempresa)
                WHERE 1
                ?getidempresa?
                AND sgdep.status='ATIVO' 
                AND NOT EXISTS (
                    SELECT 1
                    FROM objetovinculo 
                    WHERE idobjeto = '?idsgarea?'
                    AND tipoobjeto = 'sgarea'
                    AND idobjetovinc = sgdep.idsgdepartamento
                    AND tipoobjeto = 'sgdepartamento'
                )";
    }

    public static function buscarSgDepartamentoPorDepartamentoEIdEmpresa()
    {
        return "SELECT *
                FROM sgdepartamento 
                WHERE departamento in(?departamento?)
                AND status='ATIVO'
                AND idempresa = ?idempresa?";
    }

    public static function buscarSgDepartamentoPorSetorEIdEmpresa()
    {
        return "SELECT d.departamento
                FROM sgdepartamento d
                JOIN objetovinculo ov ON(ov.idobjeto = d.idsgdepartamento and ov.tipoobjeto = 'sgdepartamento')
                JOIN sgsetor s ON(s.idsgsetor = ov.idobjetovinc AND ov.tipoobjetovinc = 'sgsetor')
                AND d.status = 'ATIVO'
                AND s.status = 'ATIVO'
                AND d.idempresa = ?idempresa?
                AND s.setor IN(?setor?)";
    }

    public static function buscarSgDepartamentoPorIdEmpresa()
    {
        return "SELECT * 
                FROM sgdepartamento
                WHERE status = 'ATIVO'
                AND idempresa in(?idempresa?)
                ORDER BY departamento ASC";
    }

    public static function buscarSgDepartamentoPorGetIdEmpresa()
    {
        return "SELECT *
                FROM sgdepartamento
                WHERE status = 'ATIVO'
                ?getidempresa?
                ORDER BY departamento";
    }

    public static function buscarGrupoESPorIdSgDepartamento()
    {
        return "SELECT ci.idcontaitem, ci.contaitem, ov.idobjetovinculo
                FROM sgdepartamento sgdep
                JOIN objetovinculo ov on(ov.idobjeto = sgdep.idsgdepartamento and ov.tipoobjeto = 'sgdepartamento')
                JOIN contaitem ci on(ci.idcontaitem = ov.idobjetovinc and ov.tipoobjetovinc = 'contaitem')
                JOIN empresa e ON(ci.idempresa = e.idempresa)
                WHERE sgdep.idsgdepartamento = ?idsgdepartamento?
                AND sgdep.status = 'ATIVO'
                AND ci.status = 'ATIVO'
                ORDER BY e.sigla, ci.contaitem ASC";
    }

    public static function buscarUnidadePorIdSgDepartamento()
    {
        return "SELECT u.*
                FROM sgdepartamento sd
                JOIN unidade u ON(u.idobjeto = sd.idsgdepartamento and u.tipoobjeto = 'sgdepartamento')
                WHERE sd.idsgdepartamento = ?idsgdepartamento?";
    }

    public static function buscarPessoasVinculadasEPessoasDoGrupoVinculado()
    {
        return "SELECT DISTINCT 
                    a.idempresa
                    , p.idpessoa
                    , p.nomecurto
                    , CONCAT(e.sigla, ' - ', a.departamento) as grupo
                    , a.desc as descr
                    , a.idsgdepartamento as idobjetoext
                    , 'sgdepartamento' as tipoobjetoext
                FROM sgdepartamento a
                    LEFT JOIN pessoaobjeto fas on fas.idobjeto=a.idsgdepartamento AND fas.tipoobjeto = 'sgdepartamento'
                    LEFT JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO'
                    LEFT JOIN empresa e ON e.idempresa = a.idempresa
                    WHERE a.grupo = 'Y'
                    AND NOT a.status='INATIVO'
                    AND NOT e.status='INATIVO'
                UNION
                    SELECT DISTINCT
                        sgdep.idempresa
                        , p.idpessoa
                        , p.nomecurto
                        , CONCAT(e.sigla, ' - ', sgdep.departamento) as grupo
                        , sgdep.desc as descr
                        , sgdep.idsgdepartamento as idobjetoext
                        , 'sgdepartamento' as tipoobjetoext
                    FROM sgdepartamento sgdep
                    JOIN objetovinculo ov ON(ov.idobjeto = sgdep.idsgdepartamento AND ov.tipoobjeto = 'sgdepartamento')
                    JOIN pessoaobjeto po ON(po.idobjeto = ov.idobjetovinc AND po.tipoobjeto = 'sgsetor')
                    JOIN pessoa p ON(p.idpessoa = po.idpessoa)
                    JOIN empresa e ON(e.idempresa = sgdep.idempresa)
                    WHERE sgdep.status = 'ATIVO' AND sgdep.grupo = 'Y'
                UNION
                SELECT DISTINCT 
                    a.idempresa
                    , p.idpessoa
                    , p.nomecurto
                    , CONCAT(e.sigla, ' - ', if (s.idsgdepartamento, s.departamento, a.grupo)) as grupo
                    , a.descr as descr
                    -- , if(s.idsgdepartamento, s.idsgdepartamento, if(l.idlp, l.idlp, a.idimgrupo))  as idobjetoext
                    ,s.idsgdepartamento as idobjetoext
                    -- , if(s.idsgdepartamento, 'sgdepartamento', if(l.idlp, '_lp', 'imgrupo'))   as tipoobjetoext  
                    ,'sgdepartamento' as tipoobjetoext  
                FROM imgrupo a
                    JOIN sgdepartamento s on s.idsgdepartamento = a.idobjetoext and a.tipoobjetoext in ('sgdepartamento')
                    LEFT JOIN carbonnovo._lp l on l.idlp = a.idobjetoext and a.tipoobjetoext in ('_lp')
                    JOiN objetovinculo o on o.idobjeto = a.idimgrupo and o.tipoobjeto = 'imgrupo'
                    JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgdepartamento' and o.tipoobjetovinc = 'sgdepartamento'
                    JOIN sgdepartamento sd ON fas.idobjeto = sd.idsgdepartamento AND NOT sd.status='INATIVO'
                    JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO' and a.tipoobjetoext = 'sgdepartamento'
                    JOIN empresa e ON e.idempresa = s.idempresa";
    }

    public static function buscarDepartamentosSemVinculoPorIdEmpresaEClausula()
    {
        return "SELECT 
                    d.idsgdepartamento, d.departamento
                FROM sgdepartamento d
                WHERE NOT EXISTS(
                    SELECT 
                        1
                    FROM objetovinculo o
                    WHERE o.tipoobjeto = 'sgarea'
                    AND o.tipoobjetovinc = 'sgdepartamento'
                    AND o.idobjetovinc = d.idsgdepartamento
                )
                ?clausula?
                AND d.status='ATIVO'
                AND d.idempresa = ?idempresa?";
    }

    public static function buscarDepartamentoSgDepartamentoPorIdSgDepartamento()
    {
        return "SELECT idsgdepartamento, departamento 
                  FROM sgdepartamento 
                 WHERE idsgdepartamento = ?idsgdepartamento?";
    }

    public static function buscarGroupConcatPessoasVinculadasAoSgDepartamentoPorIdSgDepartamentoEClausula()
    {
        return "SELECT GROUP_CONCAT(p.idpessoa) AS idpessoa
                FROM (
                    SELECT po.idpessoa
                    FROM sgdepartamento sgdep
                    JOIN pessoaobjeto po ON(sgdep.idsgdepartamento = po.idobjeto AND po.tipoobjeto = 'sgdepartamento')
                    WHERE sgdep.idsgdepartamento  in (?idsgdepartamento?)
                    ?clausula?
                    AND sgdep.status = 'ATIVO'
                    UNION
                    SELECT po.idpessoa
                    FROM objetovinculo ov
                    JOIN sgdepartamento sgdep ON(sgdep.idsgdepartamento = ov.idobjeto AND ov.tipoobjeto = 'sgdepartamento')
                    JOIN sgsetor s ON(s.idsgsetor = ov.idobjetovinc AND ov.tipoobjetovinc = 'sgsetor')
                    JOIN pessoaobjeto po ON(po.idobjeto = s.idsgsetor AND po.tipoobjeto = 'sgsetor')
                    WHERE ov.idobjeto  in (?idsgdepartamento?)
                    ?clausula?
                    AND s.status = 'ATIVO'
                    AND sgdep.status = 'ATIVO'
                ) as qry
                JOIN pessoa p ON(p.idpessoa = qry.idpessoa)
                WHERE p.status = 'ATIVO'";
    }
}

?>