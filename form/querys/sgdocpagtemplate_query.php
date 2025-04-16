<?
require_once(__DIR__.'/_iquery.php');

class SgdocpagtemplateQuery implements DefaultQuery{
    public static $table = 'sgdocpagtemplate';
    public static $pk = 'idsgdocpagtemplate';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarTemplate(){
        return "SELECT tc.*
                FROM sgdocpagtemplate t
                    JOIN sgdocpagtemplatecampos tc ON (tc.idsgdocpagtemplate = t.idsgdocpagtemplate)
                WHERE t.idsgdoctipodocumento = ?idsgdoctipodocumento?
                    AND t.pagina=?pagina?";
    }
}
?>