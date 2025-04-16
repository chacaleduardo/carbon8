<?
require_once(__DIR__."/_iquery.php");

class MapaEquipamentoQuery implements DefaultQuery
{
    public static $table = 'mapaequipamento';
    public static $pk = 'idmapaequipamento';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarMapaPorIdTag()
    {
        return "SELECT `idmapaequipamento`, `idtag`, `json`
                FROM mapaequipamento
                WHERE idtag in(?idtag?)";
    }

    public static function buscarUltimoRegistro()
    {
        return "SELECT idmapaequipamento
                FROM mapaequipamento
                ORDER BY idmapaequipamento DESC
                LIMIT 1";
    }
}

?>