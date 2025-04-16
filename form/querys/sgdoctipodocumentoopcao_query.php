<?
require_once(__DIR__.'/_iquery.php');

class SgdoctipodocumentoopcaoQuery implements DefaultQuery{
    public static $table = 'sgdoctipodocumentoopcao';
    public static $pk = 'idsgdoctipodocumentoopcao';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarPorIdsgdoctipodocumentocampos(){
        return "SELECT rotulo, rotulodescritivo FROM sgdoctipodocumentoopcao WHERE idsgdoctipodocumentocampos = ?idsgdoctipodocumentocampos? ";
    }
}
?>