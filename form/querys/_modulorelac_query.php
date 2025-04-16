<?
require_once(__DIR__."/_iquery.php");

class _ModuloRelacQuery
{
    public static function buscarRegistroPorTabDeColDeTabParaColPara()
    {
        return "SELECT tp.*
                FROM ?tabDe? t
                JOIN ?tabPara? tp ON(t.?colDe? = tp.?colPara?)
                WHERE t.?pk? IN(?pkval?)";
    }
}

?>