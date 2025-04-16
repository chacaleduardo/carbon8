<?
require_once(__DIR__."/_iquery.php");

class NotaFiscalItensQuery implements DefaultQuery
{
    public static $table = 'notafiscalitens';
    public static $pk = 'idnotafiscalitens';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarItensPorIdresultado(){
        return "SELECT * FROM notafiscalitens nfi WHERE nfi.idresultado = ?idresultado?";
    }

    public static function buscarItensPorIdamostra(){
        return "SELECT i.* 
                FROM resultado r,notafiscalitens i
                WHERE r.idamostra = ?idamostra?
                    AND r.idresultado = i.idresultado";
    }
}
?>