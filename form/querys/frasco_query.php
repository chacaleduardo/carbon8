<?
class FrascoQuery
{
    public static function buscarFrascos()
    {
        return "SELECT f.idfrasco, f.frasco
                from frasco f
                where f.status = 'ATIVO'";
    }
}
?>