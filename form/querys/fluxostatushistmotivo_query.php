<?
require_once(__DIR__.'/_iquery.php');

class FluxostatushistmotivoQuery implements DefaultQuery{
    public static $table = 'fluxostatushistmotivo';
    public static $pk = 'idfluxostatushistmotivo';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarMotivosPorModulo(){
        return "SELECT *
                FROM fluxostatushistmotivo
                WHERE modulo='?modulo?'";
    }
}