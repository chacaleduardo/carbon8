<?

class WebmailAssinaturaQuery
{
    public static function buscarWebmailAssinaturaPorWebMail()
    {
        return "SELECT 
                    w.email,
                    group_concat(wp.idwebmailassinaturaobjeto) as idwebmailassinaturaobjetos
                FROM webmailassinatura w
                JOIN webmailassinaturaobjeto wp ON (w.idwebmailassinatura = wp.idwebmailassinatura)
                JOIN pessoa p ON (w.email = p.webmailemail)
                WHERE w.status = 'ATIVO'
                AND TRIM(w.email) != TRIM('?webmail?')
                AND w.email <> ''
                AND p.idpessoa = wp.idobjeto AND wp.tipoobjeto = 'pessoa'
                AND NOT EXISTS (
                    SELECT 1
                    FROM webmailassinaturaobjeto wp1 
                    WHERE wp1.idobjeto = ?idpessoa? 
                    AND wp1.tipoobjeto = 'pessoa' 
                    AND wp1.idwebmailassinatura = wp.idwebmailassinatura
                )
                AND wp.tipo = 'PESSOA'
                GROUP BY w.idwebmailassinatura";
    }

    public static function buscarAssinaturaDeFuncionariosRelacionadosPorIdPessoaEWebMail()
    {
        return "SELECT 
                    w1.idwebmailassinatura, w1.email, wp1.tipo, wp1.idwebmailassinaturaobjeto, wp1.idwebmailassinaturatemplate,wt.descricao, wp1.htmlassinatura
                FROM webmailassinatura w1
                JOIN webmailassinaturaobjeto wp1 ON (w1.idwebmailassinatura = wp1.idwebmailassinatura)
                JOIN webmailassinaturatemplate wt ON (wp1.idwebmailassinaturatemplate = wt.idwebmailassinaturatemplate)
                WHERE w1.status = 'ATIVO'
                AND wp1.idobjeto = ?idpessoa? and wp1.tipoobjeto = 'pessoa'
                AND TRIM(w1.email) != TRIM('webmail')
                AND wp1.tipo = 'PESSOA'
                ORDER BY w1.email";
    }

    public static function buscarTemplatesColaboradorPorIdPessoaEWebMailEmail()
    {
        return "SELECT 
                    w.idwebmailassinatura AS id,w.email,wp.htmlassinatura,
                    wp.idwebmailassinaturaobjeto AS id2, wp.idwebmailassinaturatemplate,
                    wt.descricao,(
                        SELECT group_concat(wo.idwebmailassinaturaobjeto) 
                        FROM webmailassinaturaobjeto wo 
                        WHERE wo.idwebmailassinatura = wp.idwebmailassinatura 
                        AND wo.idwebmailassinaturatemplate = wp.idwebmailassinaturatemplate
                    ) AS removeids
                FROM  webmailassinatura w 
                JOIN webmailassinaturaobjeto wp ON (w.idwebmailassinatura = wp.idwebmailassinatura)
                JOIN webmailassinaturatemplate wt ON (wp.idwebmailassinaturatemplate = wt.idwebmailassinaturatemplate)
                WHERE wp.idobjeto = ?idpessoa?
                AND wp.tipoobjeto='pessoa'
                AND w.email = '?webmailemail?'";
    }

    public static function buscarWebmailAssinaturaPorIdEmailVirtualConfEEmailOriginal()
    {
        return "SELECT 
                    w.idwebmailassinatura as id,
                    w.email,
                    wp.htmlassinatura,
                    wp.idwebmailassinaturaobjeto as id2,
                    wp.idwebmailassinaturatemplate,
                    wt.descricao,
                    (
                        SELECT group_concat(wo.idwebmailassinaturaobjeto) 
                        FROM webmailassinaturaobjeto wo 
                        WHERE wo.idwebmailassinatura = wp.idwebmailassinatura
                        AND wo.idwebmailassinaturatemplate = wp.idwebmailassinaturatemplate
                    ) AS removeids
                    FROM  webmailassinatura w 
                    JOIN webmailassinaturaobjeto wp ON (w.idwebmailassinatura = wp.idwebmailassinatura)
                    JOIN webmailassinaturatemplate wt ON (wp.idwebmailassinaturatemplate = wt.idwebmailassinaturatemplate)
                    WHERE wp.idobjeto = ?idemailvirtualconf?
                    AND wp.tipoobjeto = 'emailvirtualconf'
                    AND w.email = '?emailoriginal?'";
    }
}

?>