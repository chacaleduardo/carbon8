<?

require_once(__DIR__ . "/_iquery.php");

class PessoaobjetoQuery implements DefaultQuery
{
    public static $table = "pessoaobjeto";
    public static $pk = 'idpessoaobjeto';

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarPorIdobjetoTipoobjeto()
    {
        return "SELECT po.*, p.nomecurto
                FROM pessoaobjeto po
                LEFT JOIN pessoa p ON(p.idpessoa = po.idpessoa)
                WHERE po.idobjeto = ?idobjeto?
                AND po.tipoobjeto = '?tipoobjeto?'";
    }

    public static function buscarPorIdobjetoTipoobjetoJoinPessoa()
    {
        return "SELECT p.idpessoa, p.nomecurto
                FROM pessoaobjeto po
                    JOIN pessoa p ON(p.idpessoa = po.idpessoa)
                WHERE po.idobjeto = ?idobjeto?
                    AND po.tipoobjeto = '?tipoobjeto?'";
    }

    public static function buscarPorIdobjetoTipoobjetoComPessoaStatusAtivo()
    {
        return "SELECT p.nomecurto
                FROM pessoaobjeto po
                JOIN pessoa p ON(p.idpessoa = po.idpessoa)
                LEFT JOIN fluxostatus fs on(fs.idfluxostatus = p.idfluxostatus)
                LEFT JOIN carbonnovo._status cs on(cs.idstatus = fs.idstatus)
                WHERE po.idobjeto = ?idobjeto?
                AND po.tipoobjeto = '?tipoobjeto?'
                AND p.organograma = 'Y'
                AND cs.statustipo in('ATIVO', 'PENDENTE', 'AFASTADO')
                ORDER BY p.nome asc";
    }

    public static function buscarPessoasVinculadasAUmSetor()
    {
        return "SELECT *
                FROM pessoaobjeto po
                JOIN pessoa p ON(p.idpessoa = po.idpessoa)
                LEFT JOIN sgcargo c ON(c.idsgcargo = p.idsgcargo)
                WHERE p.status = 'ATIVO'
                GROUP BY p.idpessoa
                ORDER BY p.nome ASC";
    }

    public static function buscarPorIdobjetoTipoobjetoComPessoaResponsavelEStatusAtivo()
    {
        return "SELECT po.*, p.*,s.cargo
                FROM pessoaobjeto po
                JOIN pessoa p ON(p.idpessoa = po.idpessoa)
                LEFT JOIN fluxostatus fs on(fs.idfluxostatus = p.idfluxostatus)
                LEFT JOIN carbonnovo._status cs on(cs.idstatus = fs.idstatus)
                LEFT JOIN sgcargo s on(s.idsgcargo = p.idsgcargo)
                WHERE po.idobjeto = ?idobjeto?
                AND po.tipoobjeto = '?tipoobjeto?'
                AND po.responsavel='Y'
                AND p.organograma = 'Y'
                AND cs.statustipo in('ATIVO', 'PENDENTE', 'AFASTADO')
                ORDER BY s.cargo,p.nome";
    }

    public static function buscarPorIdobjetoTipoobjetoComPessoaResponsavel()
    {
        return "SELECT po.*, p.*
                FROM pessoaobjeto po
                JOIN pessoa p ON(p.idpessoa = po.idpessoa)
                LEFT JOIN fluxostatus fs on(fs.idfluxostatus = p.idfluxostatus)
                LEFT JOIN carbonnovo._status cs on(cs.idstatus = fs.idstatus)
                WHERE po.idobjeto = ?idobjeto?
                AND po.tipoobjeto = '?tipoobjeto?'
                AND po.responsavel='Y'";
    }

    public static function buscarSetorPorFuncionario()
    {
        return "SELECT s.setor
                FROM pessoaobjeto ps, pessoa p, sgsetor s 
                WHERE ps.idpessoa = p.idpessoa 
                AND ps.idobjeto = s.idsgsetor 
                AND ps.tipoobjeto = 'sgsetor' 
                AND p.nome LIKE '%?funcionario?%' 
                AND s.status = 'ATIVO' 
                AND p.status ='ATIVO'";
    }

    public static function buscarPessoasPorIdEmpresa()
    {
        return "SELECT if(p.nomecurto is null, p.nome, p.nomecurto) AS nomecurto,s.setor,ifnull(c.cargo,'Sem Cargo') as cargo
                FROM pessoaobjeto ps
                JOIN pessoa p on(p.idpessoa = ps.idpessoa)
                JOIN sgsetor s on(s.idsgsetor = ps.idobjeto and ps.tipoobjeto = 'sgsetor')
                LEFT JOIN fluxostatus fs on(fs.idfluxostatus = p.idfluxostatus)
                LEFT JOIN carbonnovo._status cs on(cs.idstatus = fs.idstatus)
                LEFT JOIN sgcargo c on(c.idsgcargo = p.idsgcargo)
                WHERE ps.idpessoa = p.idpessoa 
                AND ps.responsavel='N' 
                AND p.idempresa = ?idempresa?
                AND p.organograma = 'Y'
                AND cs.statustipo in('ATIVO', 'PENDENTE', 'AFASTADO')
                ORDER BY c.nivel asc,c.cargo asc,p.nomecurto asc,p.nome asc";
    }

    public static function buscarPessoaObjetoPorIdPessoa()
    {
        return "SELECT *
                FROM pessoaobjeto po
                where po.idpessoa = ?idpessoa?";
    }

    public static function buscarLpPorIdPessoa()
    {
        return "SELECT * 
                FROM (
                    SELECT e.sigla, l.idlp,l.descricao,o.idlpobjeto
                    FROM pessoaobjeto ps
                    JOIN sgsetor s ON(s.idsgsetor = ps.idobjeto AND ps.tipoobjeto = 'sgsetor' AND s.status='ATIVO')
                    JOIN lpobjeto o ON(o.idobjeto = s.idsgsetor AND o.tipoobjeto = 'sgsetor')
                    JOIN " . _DBCARBON . "._lp l on l.status='ATIVO' and l.idlp=o.idlp
                    JOIN empresa e on e.idempresa = l.idempresa
                    WHERE ps.idpessoa = ?idpessoa?
                    UNION
                    SELECT e.sigla, l.idlp,l.descricao,o.idlpobjeto
                        from pessoaobjeto ps
                        join sgdepartamento s on s.idsgdepartamento=ps.idobjeto and  ps.tipoobjeto = 'sgdepartamento' and s.status='ATIVO'
                        join lpobjeto o on o.idobjeto = s.idsgdepartamento and  o.tipoobjeto = 'sgdepartamento'
                        join " . _DBCARBON . "._lp l on l.status='ATIVO' and l.idlp=o.idlp
                        join empresa e on e.idempresa = l.idempresa
                        where ps.idpessoa= ?idpessoa?
                    UNION
                        SELECT e.sigla, l.idlp,l.descricao,o.idlpobjeto
                            from pessoaobjeto ps
                            join sgarea s on s.idsgarea=ps.idobjeto and  ps.tipoobjeto = 'sgarea' and s.status='ATIVO'
                            join lpobjeto o on o.idobjeto = s.idsgarea and  o.tipoobjeto = 'sgarea'
                            join " . _DBCARBON . "._lp l on l.status='ATIVO' and l.idlp=o.idlp
                            join empresa e on e.idempresa = l.idempresa
                            where ps.idpessoa= ?idpessoa?
                    UNION
                        SELECT e.sigla, l.idlp,l.descricao,o.idlpobjeto
                            from pessoaobjeto ps
                            join sgconselho s on s.idsgconselho=ps.idobjeto and  ps.tipoobjeto = 'sgconselho' and s.status='ATIVO'
                            join lpobjeto o on o.idobjeto = s.idsgconselho and  o.tipoobjeto = 'sgconselho'
                            join " . _DBCARBON . "._lp l on l.status='ATIVO' and l.idlp=o.idlp
                            join empresa e on e.idempresa = l.idempresa
                            where ps.idpessoa= ?idpessoa?
                    UNION
                    SELECT e.sigla, l.idlp,l.descricao,ps.idlpobjeto
                    FROM 
                        lpobjeto ps
                        join " . _DBCARBON . "._lp l on l.idlp=ps.idlp and l.status='ATIVO'
                        join empresa e on e.idempresa = l.idempresa
                    WHERE ps.tipoobjeto ='pessoa' 
                    AND ps.idobjeto = ?idpessoa?
                ) a 
                ORDER BY sigla";
    }

    public static function buscarPessoaObjetoPorIdPessoaObjeto()
    {
        return "SELECT po.idpessoa, po.idempresa, po.idobjeto, s.idsgdepartamento
                FROM pessoaobjeto po
                JOIN pessoa p on p.idpessoa = po.idpessoa
                JOIN sgdepartamento s on s.idsgdepartamento = po.idobjeto and po.tipoobjeto = 'sgdepartamento' 
                WHERE idpessoaobjeto = ?idpessoaobjeto?";
    }

    public static function buscarFuncionariosPorIdSgSetor()
    {
        return "SELECT p.nome,p.idpessoa,f.idpessoaobjeto,p.idtipopessoa
                FROM pessoaobjeto f 
                LEFT JOIN pessoa p ON(f.idpessoa = p.idpessoa)
                WHERE f.idobjeto = ?idsgsetor?
                AND f.tipoobjeto='sgsetor' 
                AND p.status in ('ATIVO','PENDENTE')
                AND f.responsavel = 'N'
                ORDER BY p.nome";
    }

    public static function buscarCoodenadoresPorIdObjetoTipoObjetoEGetIdEmpresa()
    {
        return "SELECT p.nome,p.idpessoa,f.idpessoaobjeto,p.idtipopessoa
                FROM pessoaobjeto f
                LEFT JOIN pessoa p ON(f.idpessoa = p.idpessoa)
                WHERE f.idobjeto = ?idobjeto?
                ?getidempresa?
                AND tipoobjeto = '?tipoobjeto?' 
                AND f.responsavel = 'Y'
                ORDER BY p.nome";
    }

    public static function buscarContatoPessoa()
    {
        return "SELECT 
                    c.idcontato, nome
                FROM
                    pessoa p,
                    pessoacontato c
                WHERE
                    p.status IN ('ATIVO' , 'PENDENTE')
                        AND p.idpessoa = c.idcontato
                        ?pessoasPorSessionIdempresa?
                        AND c.idpessoa = ?idpessoa?
                ORDER BY nome";
    }

    public static function buscarPessoaObjetoAreaSetor()
    {
        return "SELECT sa.idsgareasetor
                  FROM pessoaobjeto ps JOIN sgareasetor sa ON sa.idsgsetor = ps.idobjeto
                 WHERE ps.idpessoa = ?idpessoa? 
                   AND ps.tipoobjeto = '?tipoobjeto?'";
    }

    public static function deletarVinculoDePessoasDeletadas()
    {
        return "DELETE po 
                FROM pessoaobjeto po 
                WHERE NOT EXISTS (SELECT 1 FROM pessoa p WHERE p.idpessoa = po.idpessoa AND p.status != 'INATIVO')";
    }

    public static function deletarVinculoPorIdObjetoETipoObjeto()
    {
        return "DELETE FROM pessoaobjeto where idobjeto = ?idobjeto? and tipoobjeto = '?tipoobjeto?'";
    }

    public static function buscarPessoasPorIdResponsavel()
    {
        // -- Buscando equipe do setor
        return "SELECT po.idpessoa, s.idsgsetor as idobjeto, po.tipoobjeto, pof.idpessoa
                FROM sgsetor s
                JOIN pessoaobjeto po ON po.idobjeto = s.idsgsetor AND po.tipoobjeto = 'sgsetor' AND po.responsavel = 'Y'
                JOIN pessoaobjeto pof ON pof.idobjeto = po.idobjeto AND po.tipoobjeto = pof.tipoobjeto
                WHERE po.idpessoa = ?idpessoa?
                -- Buscando equipe do departamento
                UNION
                SELECT po.idpessoa, d.idsgdepartamento as idobjeto, po.tipoobjeto, pof.idpessoa
                FROM sgdepartamento d
                JOIN pessoaobjeto po ON po.idobjeto = d.idsgdepartamento AND po.tipoobjeto = 'sgdepartamento' AND po.responsavel = 'Y'
                JOIN objetovinculo ov ON ov.idobjeto = d.idsgdepartamento  AND ov.tipoobjeto = 'sgdepartamento' AND ov.tipoobjetovinc = 'sgsetor'
                JOIN pessoaobjeto pof ON pof.idobjeto = ov.idobjetovinc AND pof.tipoobjeto =  ov.tipoobjetovinc
                WHERE po.idpessoa = ?idpessoa?
                -- Buscando equipe da area
                UNION
                SELECT v.idpessoa, a.idsgarea as idobjeto, po.tipoobjeto, v.idpessoa
                FROM sgarea a
                JOIN pessoaobjeto po ON po.idobjeto = a.idsgarea AND po.tipoobjeto = 'sgarea' AND po.responsavel = 'Y'
                JOIN (
                    -- Pegar gestor departamento
                    SELECT pod.idpessoa, a.idsgarea
                    FROM sgarea a
                    JOIN objetovinculo ovp ON ovp.idobjeto = a.idsgarea AND ovp.tipoobjeto = 'sgarea' AND ovp.tipoobjetovinc = 'sgdepartamento'
                    JOIN pessoaobjeto pod ON pod.idobjeto = ovp.idobjetovinc AND pod.tipoobjeto = ovp.tipoobjetovinc
                    UNION
                    SELECT pof.idpessoa, a.idsgarea
                    FROM sgarea a
                    JOIN objetovinculo ovp ON ovp.idobjeto = a.idsgarea AND ovp.tipoobjeto = 'sgarea' AND ovp.tipoobjetovinc = 'sgdepartamento'
                    JOIN objetovinculo ov ON ov.idobjeto = ovp.idobjetovinc AND ov.tipoobjeto = 'sgdepartamento' AND ov.tipoobjetovinc = 'sgsetor'
                    JOIN pessoaobjeto pof ON pof.idobjeto = ov.idobjetovinc AND pof.tipoobjeto = ov.tipoobjetovinc
                ) as v ON v.idsgarea = a.idsgarea
                WHERE po.idpessoa = ?idpessoa?;";
    }

    public static function verificarGestor()
    {
        return "SELECT 1
                FROM pessoaobjeto
                WHERE responsavel = 'Y'
                AND idpessoa = ?idpessoa?
                AND idempresa = ?idempresa?";
    }

    public static function buscarPessoaDepartamento()
    {
        return "SELECT po2.idpessoa
                  FROM pessoaobjeto po JOIN objetovinculo ov ON ov.idobjetovinc = po.idobjeto AND ov.tipoobjetovinc = 'sgsetor' AND ov.tipoobjeto = 'sgdepartamento'
                  JOIN pessoaobjeto po2 ON po2.idobjeto = ov.idobjeto AND po2.tipoobjeto = 'sgdepartamento'
                 WHERE po.idpessoa = '?idpessoa?'";
    }
}
