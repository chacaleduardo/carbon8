<?
require_once(__DIR__ . "/_iquery.php");

class PessoaCrmvQuery implements DefaultQuery{

    public static $table = "pessoacrmv";
	public static $pk = "idpessoacrmv";

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ['table' => self::$table,'pk' =>  self::$pk]);
    }

}
?>
