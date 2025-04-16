<?

class FormalizacaoSubTipoQuery
{
    public static function buscarFormalizacaoSubTipoPorShare()
    {
        return "SELECT *
                FROM formalizacaosubtipo f 
                WHERE status = 'ATIVO'
                ?share?";
    }
}

?>