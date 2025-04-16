<?
require_once(__DIR__."/_iquery.php");

class DescricaoAmostraQuery implements DefaultQuery
{
    public static $table = 'descricaoamostra';
    public static $pk = 'iddescricaoamostra';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarDescricaoAmostraAtivoParaFillSelect(){
        return "SELECT descricao,descricao FROM descricaoamostra WHERE status = 'ATIVO' ORDER BY descricao ASC";
    }
    
    public static function buscarDescricaoAmostraAtivo(){
        return "SELECT * FROM descricaoamostra WHERE status = 'ATIVO' ORDER BY descricao ASC";
    }
}