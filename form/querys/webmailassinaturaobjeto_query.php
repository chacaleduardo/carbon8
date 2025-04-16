<?

class WebmailAssinaturaObjeto
{   
    public static function inserirTemplate()
    {
        return "INSERT INTO `laudo`.`webmailassinaturaobjeto` (
                    `idobjeto`, `tipoobjeto`, `idempresa`, `idwebmailassinatura`, 
                    `htmlassinatura`, `idwebmailassinaturatemplate`, `tipo`, 
                    `criadopor`, `criadoem`, `alteradopor`, `alteradoem`
                )
                SELECT 
                    ?idobjeto?, '?tipoobjeto?', idempresa,
                    idwebmailassinatura, htmlassinatura, idwebmailassinaturatemplate,
                    tipo, criadopor, criadoem, alteradopor, alteradoem
                FROM webmailassinaturaobjeto 
                where idwebmailassinaturaobjeto IN (?idwebmailassinaturaobjetos?)";
    }

    public static function buscarAssinaturaDeGruposDeEmailPorIdPessoa()
    {
        return "SELECT wp.idwebmailassinaturaobjeto, w.email, wp.htmlassinatura, wt.descricao 
                FROM webmailassinaturaobjeto wp 
                JOIN webmailassinatura w ON (wp.idwebmailassinatura = w.idwebmailassinatura) 
                JOIN webmailassinaturatemplate wt ON (wp.idwebmailassinaturatemplate = wt.idwebmailassinaturatemplate) 
                WHERE wp.tipo = 'EMAILVIRTUAL'
                AND tipoobjeto = 'pessoa'
                AND idobjeto = ?idpessoa?";
    }
}

?>