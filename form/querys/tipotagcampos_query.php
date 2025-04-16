<?

 class TipoTagCamposQuery
 {
    public static function buscarTipoTagCamposPorCampoIdTagTipo()
    {
        return "SELECT * FROM tipotagcampos WHERE campo = '?campo?' AND idtagtipo = ?idtagtipo? GROUP BY campo";
    }

    public static function buscarPeloIdTagTipo()
    {
        return "SELECT * FROM tipotagcampos WHERE idtagtipo = ?idtagtipo?;";
    }
 }
?>