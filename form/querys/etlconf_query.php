<?
require_once(__DIR__."/_iquery.php");

class EtlConfQuery implements DefaultQuery
{
    public static $table = 'etlconf';
    public static $pk = 'idetlconf';

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }  

    public static function buscarTabelasDoBancoCarbonELaudo()
    {
        return "select table_schema as db, table_name as tab from information_schema.tables 
                where table_schema='"._DBAPP."'
                union all
                select table_schema,table_name as tab from information_schema.tables 
                where table_schema='"._DBCARBON."'";
    }
}

?>