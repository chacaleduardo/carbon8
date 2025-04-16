<?
require_once(__DIR__.'/_iquery.php');

class SgdocpagQuery implements DefaultQuery{
    public static $table = 'sgdocpag';
    public static $pk = 'idsgdocpag';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarPaginasOrdenadas(){
        return "SELECT *
                FROM sgdocpag
                WHERE 
                    idsgdoc=?idsgdoc?
                ORDER BY pagina ASC";
    }

    public static function deletarSgdocpagPorIdsgdoc(){
        return "DELETE from sgdocpag where idsgdoc = ?idsgdoc?";
    }
}
?>