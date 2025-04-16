<?
require_once(__DIR__."/_iquery.php");

class NucleoVacinaQuery implements DefaultQuery
{
    public static $table = 'nucleovacina';
    public static $pk = 'idnucleovacina';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarNucleosParaComparativo(){
        return "SELECT group_concat(nv.vacina SEPARATOR ' / ') as vacinas,nv.datavacina,nv.idnucleo
                FROM nucleovacina nv 
                WHERE nv.idnucleo = ?idnucleo? AND 
                NOT EXISTS (
                    SELECT 1 
                    FROM amostra a 
                    WHERE a.idnucleo = nv.idnucleo AND 
                    nv.datavacina = CAST(a.idade AS UNSIGNED))
                GROUP BY nv.datavacina";
    }

    
}?>