<?

class FeriadoQuery
{
    public static function buscarFeriadoPorIdEmpresaEShare()
    {
        return "select * from feriado where status='ATIVO' and idempresa = ?idempresa? ?share?";
    }
}

?>