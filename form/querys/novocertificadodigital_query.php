<?

class NovoCertificadoDigitalQuery
{
    public static function buscarNovoCertificadoDigitalPorIdObjeto()
    {
        return "SELECT * FROM novocertificadodigital WHERE idobjeto = ?idobjeto? AND objeto = '?objeto?'";
    }
}

?>