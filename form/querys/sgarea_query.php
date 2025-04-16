<?

require_once(__DIR__."/_iquery.php");

class SgAreaQuery implements DefaultQuery
{
    public static $table = 'sgarea';
    public static $pk = 'idsgarea';

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

    public static function buscarSgAreaPorIdSgArea()
    {
        return "SELECT * FROM sgarea where status = 'ATIVO' and  idsgarea = ?idsgarea?";
    }

    public static function buscarSgAreaPorAreaEIdEmpresa()
    {
        return "SELECT *
                FROM sgarea 
                WHERE area in (?area?)
                AND status = 'ATIVO'
                AND idempresa = ?idempresa?";
    }

    public static function buscarSgAreaPorIdempresa()
    {
        return "SELECT * 
                FROM sgarea
                WHERE status = 'ATIVO' 
                AND idempresa = ?idempresa? 
                ORDER BY area ASC";
    }

    public static function buscarSgareaPorIdSgconselho()
    {
        return "SELECT 
                    CONCAT(e.sigla, ' - ', a.area) as area, 
                    a.idsgarea, 
                    ov.idobjetovinculo
                FROM sgarea a
                JOIN empresa e ON(e.idempresa = a.idempresa)
                JOIN objetovinculo ov ON (ov.idobjetovinc = a.idsgarea AND ov.tipoobjetovinc = 'sgarea' AND ov.tipoobjeto = 'sgconselho')
                WHERE ov.idobjeto = ?idsgconselho?
                AND a.status = 'ATIVO'
                ORDER BY a.area";
    }

    public static function buscarAreasDisponiveisParaVinculoPorIdSgconselho()
    {
        return "SELECT a.idsgarea, a.area
                FROM sgarea a
                WHERE a.status = 'ATIVO'
                AND NOT EXISTS (
                    SELECT 1
                    FROM objetovinculo
                    WHERE idobjeto = ?idsgconselho?
                    AND tipoobjeto = 'sgconselho'
                    AND tipoobjetovinc = 'sgarea'
                    AND idobjetovinc = a.idsgarea
                )
                ?getidempresa?";
    }

    public static function buscarAreasPorIdEmpresa()
    {
        return "SELECT a.idsgarea, CONCAT(e.sigla, ' - ', a.area) AS area
                FROM sgarea a
                JOIN empresa e ON(e.idempresa = a.idempresa)
                WHERE e.idempresa IN(?idempresa?)
                AND a.status = 'ATIVO'
                ORDER BY a.area";
    }

    public static function buscarPessoasVinculadasEPessoasDoGrupoVinculado()
    {
        return "SELECT DISTINCT 
                    a.idempresa
                    , p.idpessoa
                    , p.nomecurto
                    , CONCAT(e.sigla, ' - ', a.area) as grupo
                    , a.desc as descr
                    , a.idsgarea as idobjetoext
                    , 'sgarea' as tipoobjetoext
                FROM sgarea a
                    LEFT JOIN pessoaobjeto fas on fas.idobjeto=a.idsgarea AND fas.tipoobjeto = 'sgarea'
                    LEFT JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO'
                    LEFT JOIN empresa e ON e.idempresa = a.idempresa
                    WHERE NOT a.status='INATIVO'
                    AND a.grupo = 'Y'
                    AND NOT e.status='INATIVO'
                UNION
                SELECT DISTINCT 
                    a.idempresa
                    , p.idpessoa
                    , p.nomecurto
                    , CONCAT(e.sigla, ' - ', a.area) as grupo
                    , a.desc as descr
                    , a.idsgarea as idobjetoext
                    , 'sgarea' as tipoobjetoext
                FROM sgarea a
                    JOiN objetovinculo o on o.idobjeto = a.idsgarea and o.tipoobjeto = 'sgarea'
                    JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc AND fas.tipoobjeto = 'sgarea'
                    JOIN sgarea sa ON fas.idobjeto = sa.idsgarea AND NOT sa.status='INATIVO'
                    JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO' and a.grupo = 'Y'
                    JOIN empresa e ON e.idempresa = a.idempresa												
                union
                SELECT DISTINCT 
                    a.idempresa
                    , p.idpessoa
                    , p.nomecurto
                    , CONCAT(e.sigla, ' - ', if (s.idsgarea, s.area, a.grupo)) as grupo
                    , a.descr as descr
                    -- , if(s.idsgarea, s.idsgarea, if(l.idlp, l.idlp, a.idimgrupo))  as idobjetoext
                    ,s.idsgarea as idobjetoext
                    -- , if(s.idsgarea, 'sgarea', if(l.idlp, '_lp', 'imgrupo'))   as tipoobjetoext  
                    ,'sgarea' as tipoobjetoext  
                FROM imgrupo a
                    JOIN sgarea s on s.idsgarea = a.idobjetoext and a.tipoobjetoext in ('sgarea')
                    LEFT JOIN carbonnovo._lp l on l.idlp = a.idobjetoext and a.tipoobjetoext in ('_lp')
                    JOiN objetovinculo o on o.idobjeto = a.idimgrupo and o.tipoobjeto = 'imgrupo'
                    JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgarea' and o.tipoobjetovinc = 'sgarea'
                    JOIN sgarea sa ON fas.idobjeto = sa.idsgarea AND NOT sa.status='INATIVO'
                    JOIN pessoa p on p.idpessoa=fas.idpessoa AND NOT p.status='INATIVO' and a.tipoobjetoext = 'sgarea'
                    JOIN empresa e ON e.idempresa = s.idempresa";
    }
}

?>