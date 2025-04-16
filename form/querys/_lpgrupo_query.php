<?
require_once(__DIR__."/_iquery.php");
class _LpGrupoQuery implements DefaultQuery
{
    public static $table = _DBCARBON.'_lpgrupo';
    public static $pk = 'idlpgrupo';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarGruposPorLpgrupopar(){
        return "SELECT 
                    idlpgrupo, descricao,status
                FROM
                    "._DBCARBON."._lpgrupo
                WHERE
                    lpgrupopar = ?idlpgrupo?
                    order by status asc";
    }

    public static function buscarLpGrupoPorIdLp()
    {
        return "SELECT 
                    lpg.lpgrupopar
                FROM carbonnovo._lpgrupo lpg
                JOIN carbonnovo._lpobjeto lpo ON(lpo.idobjeto = lpg.idlpgrupo AND lpo.tipoobjeto = 'lpgrupo')
                WHERE lpo.idlp = ?idlp?";
    }
}

?>