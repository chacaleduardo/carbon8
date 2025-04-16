<?
require_once(__DIR__."/_iquery.php");

class UfQuery implements DefaultQuery
{
    public static $table = 'uf';
    public static $pk = 'iduf';

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarTodasUf()
    {
        return "SELECT * from uf";
    }


}

?>