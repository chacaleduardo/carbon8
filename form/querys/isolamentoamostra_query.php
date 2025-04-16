<?
require_once(__DIR__."/_iquery.php");

class IsolamentoAmostraQuery implements DefaultQuery
{
    public static $table = 'isolamentoamostra';
    public static $pk = 'idisolamentoamostra';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarisolamentoamostraAtivoParaFillSelect(){
        return "SELECT descricao,descricao FROM isolamentoamostra WHERE status = 'ATIVO' ORDER BY descricao ASC";
    }
    
    public static function buscarisolamentoamostraAtivo(){
        return "SELECT * FROM isolamentoamostra WHERE status = 'ATIVO' ORDER BY descricao ASC";
    }
}