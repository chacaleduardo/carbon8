<?

class SgareaGrupoQuery
{
    public static function buscarAreaGrupoPorIdImGrupo()
    {
        return "SELECT s.status as idsgareagrupostatus, a.area, s.*
                FROM sgareagrupo s,sgarea a 
                WHERE a.idsgarea = s.idsgarea 
                AND a.status = 'ATIVO' 
                AND s.idimgrupo = ?idimgrupo?
                ORDER BY a.area";
    }
}

?>