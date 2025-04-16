<?
class RateioItemDestOriQuery
{
    public static function inserirRateioItemDestOri()
    {
        return "INSERT INTO rateioitemdest (idempresa, 
                                            idrateioitem, 
                                            idobjeto, 
                                            tipoobjeto, 
                                            valor, 
                                            idpessoa, 
                                            criadopor, 
                                            criadoem, 
                                            alteradopor, 
                                            alteradoem) 
                                    VALUES (?idempresa?, 
                                            ?idrateioitem?, 
                                            ?idobjeto?, 
                                            '?tipoobjeto?', 
                                            '?valor?', 
                                            ?idpessoa?, 
                                            '?usuario?', 
                                            '?datacriacao?', 
                                            '?usuario?', 
                                            '?datacriacao?')";
    }
}

?>