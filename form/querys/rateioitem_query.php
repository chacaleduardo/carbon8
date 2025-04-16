<?

class RateioItemQuery
{
    public static function inserir()
    {
        return "INSERT INTO rateioitem (
                    idempresa, idrateio, idobjeto, tipoobjeto, 
                    criadopor, criadoem, alteradopor, alteradoem
                ) VALUES (
                    ?idempresa?, ?idrateio?, ?idobjeto?, '?tipoobjeto?', 
                    '?criadopor?', ?criadoem?, '?alteradopor?', ?alteradoem?
                )";
    }
}

?>