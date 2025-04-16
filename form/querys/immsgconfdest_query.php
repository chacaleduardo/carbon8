<?

class ImMsgConfDestQuery
{
    public static function buscarImMsgConfDestPorIdobjeto()
    {
        return " SELECT
                    d.idimmsgconfdest,c.titulo,c.idimmsgconf,d.objeto,d.status
                FROM immsgconfdest d,immsgconf c
                WHERE d.objeto = '?objeto?'
                AND d.idimmsgconf = c.idimmsgconf  
                AND d.idobjeto= ?idobjeto?";
    }

    public static function buscarImMgsgConfDestQueryPorIdImGrupoEIdPessoa()
    {
        return "SELECT 
                    d.idimmsgconf
                FROM immsgconfdest d 
                WHERE d.objeto = 'imgrupo'
                AND d.idobjeto = ?idimgrupo?
                AND not exists (select 1 from immsgconfdest d1 where d1.idimmsgconf=d.idimmsgconf and d.idobjeto= ?idpessoa? and d.objeto='pessoa')";
    }

    public static function deletarPessoasInativasDoAlerta()
    {
        return "DELETE FROM immsgconfdest 
                WHERE objeto = 'pessoa' 
                AND idobjeto IN (
                    SELECT * 
                    FROM (
                        SELECT idpessoa FROM pessoa p WHERE p.status = 'INATIVO' AND p.idtipopessoa = 1
                    ) a
                )";
    }

    public static function buscarPessoasQueNaoFacamParteDoGrupoDeConfiguracaoDeAlerta()
    {
        return "SELECT idimmsgconfdest as id
                FROM immsgconfdest er
                WHERE NOT er.idobjetoext is null 
                AND er.objetoext = 'imgrupo' 
                AND NOT EXISTS (
                    SELECT 1 FROM  imgrupopessoa igp 
                    WHERE igp.idimgrupo = er.idobjetoext 
                    AND igp.idpessoa = er.idobjeto 
                    AND er.objeto = 'pessoa'
                )";
    }

    public static function deletarPorChavePrimaria()
    {
        return "DELETE FROM immsgconfdest 
                WHERE idimmsgconfdest IN (?id?);";
    }

    public static function inserirPessoasAdicionadasNaConfiguracaoDeAlerta()
    {
        return "INSERT INTO immsgconfdest (
                    SELECT DISTINCT 
                        NULL AS idimmsgconfdest, 
                        p.idempresa AS idempresa, 
                        e.idimmsgconf, 
                        gp.idpessoa, 
                        'pessoa' AS objeto, 
                        r.idobjeto, 
                        'imgrupo' AS objetoext, 
                        'N' AS inseridomanualmente, 
                        'ATIVO', 
                        e.criadopor,
                        e.criadoem,
                        e.alteradopor, 
                        NOW()
                FROM immsgconf e
                JOIN immsgconfdest r ON r.idimmsgconf = e.idimmsgconf AND `r`.`objeto` = 'imgrupo'
                JOIN imgrupopessoa gp ON gp.idimgrupo = r.idobjeto
                LEFT JOIN immsgconfdest r2 ON r2.idimmsgconf = r.idimmsgconf AND r2.objetoext = 'imgrupo' AND r2.idobjetoext = r.idobjeto AND r2.idobjeto = gp.idpessoa
                LEFT JOIN pessoa p ON p.idpessoa = gp.idpessoa
                WHERE r2.idobjeto IS NULL 
                AND NOT EXISTS (
                    SELECT 1 
                    FROM immsgconfdest d2 
                    WHERE d2.idimmsgconf = e.idimmsgconf 
                    AND d2.idobjeto = gp.idpessoa 
                    AND d2.objeto = 'pessoa'
                    )
                )";
    }

    public static function buscarPessoasParaEnviarAlerta()
    {
        return "SELECT distinct(idpessoa) as idpessoa, nome, idobjetoext, objetoext
        from (	
            SELECT 
                p.idpessoa, p.nome, c.idobjetoext, c.objetoext
            FROM
                pessoa p,
                immsgconfdest c
            WHERE
                c.objeto = 'pessoa'
                AND c.status='ATIVO'
                AND p.idpessoa = c.idobjeto
                AND c.idimmsgconf = ?idimmsgconf?
                AND p.status = 'ATIVO'
                AND NOT EXISTS (SELECT 1 FROM evento e join eventoobj eo on eo.idevento = e.idevento and eo.objeto = 'immsgconf' and idobjeto = ?idimmsgconf?
                join fluxostatuspessoa mp on e.idevento = mp.idmodulo and mp.modulo = 'evento'  where mp.idobjeto = p.idpessoa and mp.tipoobjeto = 'pessoa' and e.modulo = '?modulo?' and e.idmodulo = '?idpk?' and mp.oculto = 0)
                ?clausula?
            ) as u";
    }

    public static function buscarPessoasVinculadasAoAlerta()
    {
        return "SELECT d.idimmsgconfdest,s.nome,s.idpessoa,d.inseridomanualmente,d.criadopor,d.criadoem,d.status,s.idtipopessoa
                from immsgconfdest d,pessoa s
                where s.idpessoa = d.idobjeto
                    and d.objeto ='pessoa'
                    and d.idimmsgconf = ?idimmsgconf?
                order by s.nome";
    }

    public static function buscarPessoasNaoVinculadasAoAlerta()
    {
        return "SELECT 
                    a.idpessoa
                    ,concat(a.nome,'-',t.tipopessoa) as nome
                from pessoa a join tipopessoa t on (t.idtipopessoa=a.idtipopessoa)
                where a.idempresa =?idempresa?
                    and a.status ='ATIVO'
                    ?andtppes?
                        and not exists(
                                SELECT 1
                                FROM immsgconfdest v
                                where  v.idimmsgconf= ?idimmsgconf?
                                    and v.objeto ='pessoa'
                                    and a.idpessoa = v.idobjeto				
                        )
                order by a.nome asc";
    }
}

?>