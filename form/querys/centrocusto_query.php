<?

require_once(__DIR__."/_iquery.php");

class CentroCustoQuery implements DefaultQuery
{
    public static $table = "centrocusto";
    public static $pk = 'idcentrocusto';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarCentroCusto()
    {
        return "SELECT * FROM centrocusto where status='ATIVO' ORDER BY centrocusto";
    }
}

?>
