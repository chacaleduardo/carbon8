<?

require_once(__DIR__."/_iquery.php");

class TipoUnidadeQuery implements DefaultQuery
{
    public static $table = "tipounidade";
    public static $pk = 'idtipounidade';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarTipos()
    {
        return "SELECT * FROM tipounidade WHERE status='ATIVO' ORDER BY tipounidade";
    }
}

?>