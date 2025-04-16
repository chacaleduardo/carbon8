<?

class WebmailAssinaturaTemplateQuery
{
    public static function buscarWebmailAssinaturaTemplateSemVincPorIdPessoa()
    {
        return "SELECT *
                FROM webmailassinaturatemplate t
                WHERE t.tipo = 'COLABORADOR'
                AND t.status = 'ATIVO'
                AND NOT EXISTS (
                    SELECT 1
                    FROM webmailassinaturaobjeto wp 
                    JOIN webmailassinatura w ON (wp.idwebmailassinatura = w.idwebmailassinatura) 
                    WHERE wp.idobjeto = ?idpessoa? 
                    AND wp.idwebmailassinaturatemplate = t.idwebmailassinaturatemplate 
                    AND w.email = '?webmail?'
                )";
    }

    public static function buscarWebmailAssinaturaTemplateDisponiveisParaVinculoPorIdEmailVirtualConf()
    {
        return "SELECT w.idwebmailassinaturatemplate as id, w.descricao, w.htmltemplate as template
                FROM webmailassinaturatemplate w
                WHERE w.status = 'ATIVO' 
                AND w.tipo = 'EMAILVIRTUAL'
                AND NOT EXISTS(
                    SELECT 1 FROM webmailassinaturaobjeto we WHERE we.tipo = 'EMAILVIRTUAL' AND we.idwebmailassinaturatemplate = w.idwebmailassinaturatemplate AND we.tipoobjeto='emailvirtualconf' AND we.idobjeto = ?idemailvirtualconf?
                )";
    }
}

?>