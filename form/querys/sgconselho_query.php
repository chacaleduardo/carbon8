<?
require_once(__DIR__."/_iquery.php");

class SgConselhoQuery implements DefaultQuery
{
    public static $table = 'sgarea';
    public static $pk = 'idsgarea';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarSgConselhoPorIdSgConselho()
    {
        return "SELECT * FROM sgconselho where status = 'ATIVO' and idsgconselho = ?idsgconselho?";
    }

    public static function buscarSgConselhoPorIdEmpresa()
    {
        return "SELECT conselho 
                FROM sgconselho 
                WHERE status = 'ATIVO'
                AND idempresa = ?idempresa?
                ORDER BY conselho ASC";
    }

    public static function buscarConselhosPorIdEmpresa()
    {
        return "SELECT *
                FROM sgconselho
                WHERE idempresa = ?idempresa?
                AND status = 'ATIVO'";
    }

    public static function buscarPessoasVinculadasEPessoasDoGrupoVinculado()
    {
        return "SELECT c.idempresa,
                        p.idpessoa,
                        p.nomecurto,
                        CONCAT(e.sigla, ' - ', c.conselho) as grupo,
                        c.desc as descr,
                        c.idsgconselho as idobjetoext,
                        'sgconselho' as tipoobjetoext
                FROM sgconselho c
                JOIN pessoaobjeto po ON(po.idobjeto = c.idsgconselho AND po.tipoobjeto = 'sgconselho')
                JOIN pessoa p ON(p.idpessoa = po.idpessoa)
                JOIN empresa e ON(e.idempresa = c.idempresa)
                WHERE c.grupo = 'Y'
                AND c.status = 'ATIVO'
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
                FROM imgrupo a									
                    JOiN objetovinculo o on o.idobjeto = a.idimgrupo and o.tipoobjeto = 'imgrupo'
                    JOIN pessoaobjeto fas on fas.idobjeto = o.idobjetovinc and fas.tipoobjeto = 'sgconselho' and o.tipoobjetovinc = 'sgconselho'
                    JOIN sgconselho sd ON fas.idobjeto = sd.idsgconselho AND NOT sd.status='INATIVO'
                    JOIN pessoa p on p.idpessoa = fas.idpessoa AND NOT p.status='INATIVO'
                    JOIN empresa e ON e.idempresa = a.idempresa";
    }

    public static function buscarSgConselhoPorIdEmpresaEClausula()
    {
        return "SELECT idsgconselho, conselho
                FROM sgconselho
                WHERE status = 'ATIVO'
                AND idempresa = ?idempresa?
                ?clausula?";
    }
}

?>