<?
require_once(__DIR__.'/_iquery.php');

class SgdoctipodocumentoQuery implements DefaultQuery{
    public static $table = 'sgdoctipodocumento';
    public static $pk = 'idsgdoctipodocumento';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarMotivos()
    {
        return "SELECT idsgdoctipodocumento, tipodocumento
                FROM sgdoctipodocumento
                WHERE status='ativo'
                AND idsgdoctipo='rnc'
                ORDER BY tipodocumento";
    }
}
?>