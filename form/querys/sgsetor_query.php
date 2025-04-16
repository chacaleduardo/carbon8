<?
require_once(__DIR__."/_iquery.php");


class SgsetorQuery implements DefaultQuery{
    public static $table = "sgsetor";
    public static $pk = 'idsgsetor';

    public const buscarPorChavePrimariaSQLPadrao = " SELECT t.* 
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

    public static function buscarSgSetorPorIdEmpresa()
    {
        return "SELECT *
                FROM sgsetor
                WHERE status = 'ATIVO' 
                AND idempresa in(?idempresa? )
                ORDER BY setor ASC";
    }

    public static function buscarSetoresDiponiveisParaVinculoPorIdSgDepartamento()
    {
        return "SELECT 
                    CONCAT(e.sigla, ' - ', ss.setor) as setor,
                    ss.idsgsetor,
                    ss.idempresa,
                    ss.idunidade,
                    ss.idpessoaemail,
                    ss.desc,
                    ss.grupo,
                    ss.idsgsetorpar,
                    ss.status,
                    ss.idtipopessoa,
                    ss.criadopor,
                    ss.criadoem,
                    ss.alteradopor,
                    ss.alteradoem,
                    ss.idsgdepartamento
                FROM sgsetor ss
                JOIN empresa e ON(ss.idempresa = e.idempresa)
                WHERE ss.status='ATIVO'
                AND NOT EXISTS (
                        SELECT 1 
                        FROM objetovinculo ov
                        WHERE ss.idsgsetor = ov.idobjetovinc 
                        AND ov.tipoobjetovinc = 'sgsetor'
                        AND ov.tipoobjeto = 'sgdepartamento'
                )
                ?getidempresa?
                ORDER BY ss.setor";
    }

    public static function buscarSetoresDiponiveisParaVinculoPorIdSgareaEGetIdEmpresa()
    {
        return "SELECT s.idsgsetor, s.setor
                FROM sgsetor s
                WHERE s.status='ATIVO' 
                    AND NOT EXISTS (
                        SELECT 1
                        FROM sgareasetor v
                        WHERE v.idsgarea= ?idsgarea?
                        AND v.idsgsetor=s.idsgsetor
                    )
                ?getidempresa?
                ORDER BY s.setor ASC";
    }

    public static function buscarSetoresPorIdSgDepartamento()
    {
        return "SELECT 
                    CONCAT(e.sigla, ' - ', s.setor) as setor, s.idsgsetor, ov.idobjetovinculo
                FROM sgsetor s
                JOIN empresa e ON(e.idempresa = s.idempresa)
                INNER JOIN objetovinculo ov ON s.idsgsetor=ov.idobjetovinc 
                AND ov.tipoobjetovinc = 'sgsetor'
                WHERE ov.idobjeto =  '?idsgdepartamento?' 
                AND s.status = 'ATIVO'
                ORDER BY e.sigla, s.setor ASC";
    }

    public static function buscarPessoasPorIdSgSetor()
    {
        return "SELECT p.nome,p.idpessoa,p.idtipopessoa
                FROM sgsetor s ,pessoa p
                WHERE s.idsgsetor = ?idsgsetor?
                AND p.status in ('ATIVO','PENDENTE')
                AND p.idtipopessoa=s.idtipopessoa
                ORDER BY p.nome";
    }

    public static function buscarGruposPorIdSgSetor()
    {
        return "SELECT o.idobjetovinculo, s.idsgsetor, s.setor
                FROM sgsetor s
                JOIN objetovinculo o on o.idobjetovinc = s.idsgsetor
                AND o.tipoobjetovinc = 'sgsetor' 
                AND o.idobjeto = ?idsgsetor?
                AND o.tipoobjeto = 'sgsetor'
                AND s.status = 'ATIVO'
                ORDER BY s.setor";
    }

    public static function buscarGruposDeChatPorIdSgsetor()
    {
        return "SELECT 
                    regra.idimregra, regra.status, g.grupo as grupodestino, regra.tiporegra, if (regra.idobjetodestino, regra.idobjetodestino, g.idimgrupo) as idobjetodestino,
                    if (regra.idobjetoorigem, regra.idobjetoorigem, go.idimgrupo) as idobjetoorigem
                FROM sgsetor s
                JOIN imgrupo g ON(g.idobjetoext = s.idsgsetor AND not idsgsetor = ?idsgsetor?)
                JOIN imgrupo go ON(go.idobjetoext = ?idsgsetor?)
                LEFT JOIN (
                    SELECT *
                    FROM (
                        SELECT * FROM (
                            SELECT idimregra, r.status, r.tiporegra, r.idobjetoorigem, r.idobjetodestino
                            FROM imregra r 
                            UNION
                            SELECT IF(tiporegra = 'GRUPO', null, idimregra) AS idimregra, IF (
                                    tiporegra = 'GRUPO', null, r.status
                                ) AS status, r.tiporegra, r.idobjetodestino, r.idobjetoorigem 
                            FROM imregra r 
                        ) b ORDER BY idobjetoorigem, idobjetodestino, tiporegra, idimregra desc
                    )a GROUP BY idobjetoorigem, idobjetodestino, tiporegra
                ) regra ON(regra.idobjetodestino = g.idimgrupo and regra.idobjetoorigem = go.idimgrupo)
                ORDER BY setor, tiporegra";
    }

    public static function buscarUnidadesSgdepartamentoPorIdSgsetor()
    {
        return "SELECT u.idunidade
                FROM sgsetor sg
                JOIN sgdepartamento sd ON (sd.idsgdepartamento = sg.idsgdepartamento)
                JOIN unidade u ON (u.idobjeto = sd.idsgdepartamento AND u.tipoobjeto = 'sgdepartamento')
                WHERE sg.idsgsetor = ?idsgsetor?";
    }

    public static function buscarPessoasVinculadasEPessoasDoGrupoVinculado()
    {
        return "SELECT DISTINCT
                    a.idempresa
                    , p.idpessoa
                    , p.nomecurto
                    , CONCAT(e.sigla, ' - ', a.setor) as grupo
                    , a.desc as descr
                    , a.idsgsetor as idobjetoext
                    , 'sgsetor' as tipoobjetoext
                FROM sgsetor a
                    LEFT JOIN pessoaobjeto fas on fas.idobjeto=a.idsgsetor AND fas.tipoobjeto = 'sgsetor'
                    LEFT JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO'
                    LEFT JOIN empresa e ON e.idempresa = a.idempresa
                    WHERE NOT a.status='INATIVO'
                    AND a.grupo = 'Y'
                UNION
                -- Setor (GRUPO) vinculado ao setor
                SELECT DISTINCT 
                    a.idempresa
                    , p.idpessoa
                    , p.nomecurto
                    , CONCAT(e.sigla, ' - ', a.setor) as grupo
                    , a.desc as descr
                    , a.idsgsetor as idobjetoext
                    , 'sgsetor' as tipoobjetoext
                FROM sgsetor a
                    JOiN objetovinculo o on o.idobjeto = a.idsgsetor and o.tipoobjeto = 'sgsetor'
                    JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc AND fas.tipoobjeto = 'sgsetor'
                    JOIN sgsetor sg ON fas.idobjeto = sg.idsgsetor AND NOT sg.status='INATIVO'
                    JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO' and a.grupo = 'Y'
                    JOIN empresa e ON e.idempresa = a.idempresa
                UNION
                -- Pessoas vinculadas pelo tipo
                SELECT DISTINCT
                    s.idempresa
                    , p.idpessoa
                    , p.nome
                    , s.setor as grupo
                    , s.desc as descr
                    , s.idsgsetor as idobjetoext
                    , 'sgsetor' as tipoobjetoext
                FROM sgsetor s
                JOIN pessoa p ON(p.idtipopessoa = s.idtipopessoa)
                AND NOT p.status = 'INATIVO'
                AND s.grupo = 'Y'
                AND NOT s.status = 'INATIVO'
                JOIN empresa e ON e.idempresa = s.idempresa
                WHERE NOT EXISTS(
                    SELECT 1
                    FROM pessoaobjeto
                    WHERE idobjeto = s.idsgsetor
                    AND tipoobjeto = 'sgsetor'
                    AND idpessoa = p.idpessoa
                )
                UNION
                -- Trazer coordenadores do departamento do grupo
                SELECT DISTINCT
                    a.idempresa
                    , p.idpessoa
                    , p.nomecurto
                    , CONCAT(e.sigla, ' - ', a.setor) as grupo
                    , a.desc as descr
                    , a.idsgsetor as idobjetoext
                    , 'sgsetor' as tipoobjetoext
                FROM sgsetor a
                    join objetovinculo ov on ov.idobjetovinc = a.idsgsetor and ov.tipoobjetovinc = 'sgsetor'
                    JOIN pessoaobjeto po on po.idobjeto = ov.idobjeto AND po.tipoobjeto = 'sgdepartamento'
                    JOIN pessoa p on p.idpessoa = po.idpessoa 
                    JOIN empresa e ON e.idempresa = a.idempresa
                    AND NOT p.status='INATIVO' and a.grupo = 'Y'
                    AND NOT a.status='INATIVO'
                UNION
                SELECT DISTINCT 
                    a.idempresa
                    , p.idpessoa
                    , p.nomecurto
                    , CONCAT(e.sigla, ' - ', if (s.idsgsetor, s.setor, a.grupo)) as grupo
                    , a.descr as descr
                    -- , if(s.idsgsetor, s.idsgsetor, if(l.idlp, l.idlp, a.idimgrupo))  as idobjetoext
                    ,s.idsgsetor as idobjetoext
                    -- , if(s.idsgsetor, 'sgsetor', if(l.idlp, '_lp', 'imgrupo'))   as tipoobjetoext  
                    ,'sgsetor' as tipoobjetoext  
                FROM imgrupo a
                    JOIN sgsetor s on s.idsgsetor = a.idobjetoext and a.tipoobjetoext in ('sgsetor')
                    LEFT JOIN carbonnovo._lp l on l.idlp = a.idobjetoext and a.tipoobjetoext in ('_lp')
                    JOiN objetovinculo o on o.idobjeto = a.idimgrupo and o.tipoobjeto = 'imgrupo'
                    JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgsetor' and o.tipoobjetovinc = 'sgsetor'
                    JOIN sgsetor sg ON fas.idobjeto = sg.idsgsetor AND NOT sg.status='INATIVO'
                    JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO' and a.tipoobjetoext = 'sgsetor'
                    JOIN empresa e ON e.idempresa = s.idempresa";
    }
}