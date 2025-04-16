<?
require_once(__DIR__."/_iquery.php");

class EmpresaCobrancaQuery implements DefaultQuery
{
    public static $table = 'empresacobranca';
    public static $pk = 'idempresacobranca';

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarCobrancasPorIdempresa(){
        return "SELECT * from empresacobranca where idempresa = ?idempresa?";
    }

}