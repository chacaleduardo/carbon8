<?
require_once(__DIR__."/_iquery.php");


class ShareQuery implements DefaultQuery{
    public static $table = "share";
    public static $pk = 'idshare';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarRegraPorModuloSharemetodo(){
        return "SELECT * FROM share where modulo='?modulo?' and sharemetodo='modulofiltrospesquisa'";
    }
}