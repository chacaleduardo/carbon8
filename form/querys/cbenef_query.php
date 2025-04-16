<?
class CbenefQuery {
    public static function buscarItensPorIdCbenef() {
        return "SELECT idcbenefitem, cst, ncm, cbenef
                from cbenefitem
                where idcbenef = ?idcbenef?
                order by idcbenefitem desc";
    }
}