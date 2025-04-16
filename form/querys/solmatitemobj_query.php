<?

class SolMatItemObjQuery implements DefaultQuery
{
    public static $table = 'solmatitemobj';
    public static $pk = 'idsolmatitemobj';

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }  

    public static function buscarTagsVinculadasPorIdSolMatItem()
    {
        return "SELECT 
                    t.idtag,
                    concat(e.sigla,'-',t.tag) AS tag,
                    t.idunidade,
                    e.idempresa,
                    t.modelo,
                    fabricante,
                    t.descricao,
                    tt.tagtipo, 
                    tagclass,
                    t.status, 
                    s.idsolmatitem, 
                    so.idsolmatitemobj, 
                    so.idtagpaianterior,
                    so.idunidadeanterior
                FROM solmatitemobj so 
                JOIN tag t ON t.idtag = so.idobjeto
                JOIN tagtipo tt ON t.idtagtipo = tt.idtagtipo 
                JOIN tagclass tc ON t.idtagclass = tc.idtagclass
                JOIN solmatitem s ON s.idsolmatitem = so.idsolmatitem
                JOIN empresa e ON t.idempresa = e.idempresa
                WHERE so.tipoobjeto='tag'
                AND s.idsolmatitem = ?idsolmatitem?";
    }
}

?>