<?
require_once(__DIR__.'/_iquery.php');

class SgdoctipodocumentocamposQuery implements DefaultQuery{
    public static $table = 'sgdoctipodocumentocampos';
    public static $pk = 'idsgdoctipodocumentocampos';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }


    public static function buscarCamposVisiveisPorIdsgdoctipodocumento(){
        return "SELECT c.col,
                        tc.rotpsq AS rotcurto,
                        tc.dropsql AS code,
                        tc.datatype,
                        c.code AS texto,
                        c.prompt,
                        c.editavel,
                        c.idsgdoctipodocumentocampos
                FROM sgdoctipodocumentocampos c 
                    JOIN carbonnovo._mtotabcol tc ON (tc.tab = c.tabela AND tc.col=c.col) 
                WHERE c.idsgdoctipodocumento=?idsgdoctipodocumento?
                    AND c.visivel = 'Y'
                ORDER BY CASE WHEN c.ord IS NULL THEN 999 ELSE c.ord END";
    }


}
?>