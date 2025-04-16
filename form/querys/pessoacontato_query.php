<?
class PessoaContatoQuery{
    public static function buscarNumeroAmostraPorUnidadeCliente(){
        return "SELECT 
        (SELECT 
                COUNT(*)
            FROM
                pessoacontato c
                    JOIN
                amostra a ON (a.idpessoa = c.idpessoa)
            WHERE
                c.idcontato = ?idpessoa? AND a.idunidade = 1) AS diagnostico,
        (SELECT 
                COUNT(*)
            FROM
                pessoacontato c
                    JOIN
                amostra a ON (a.idpessoa = c.idpessoa)
            WHERE
                c.idcontato = ?idpessoa?
                    AND a.idunidade IN (6 , 9)) AS autogena";
    }

    public static function buscarPessoaPorContato()
    {
        return "SELECT p.idpessoa
                  FROM pessoacontato c JOIN pessoa p ON (p.idpessoa = c.idpessoa  AND p.idtipopessoa = ?idtipopessoa?)
                 WHERE c.idcontato = ?idcontato?";
    }

    public static function buscarPessoasEEmailPorContatoETipoPessoa()
    {
        return "SELECT c.participacaoprod,
                        c.participacaoserv,
                        p.idpessoa,
                        p.nome,
                        p.email
				FROM pessoacontato c,pessoa p
				where c.idpessoa = ?idpessoa?
                    and p.email is not null
                    and p.email !=''
                    and p.idtipopessoa = ?idtipopessoa?
                    and c.idcontato = p.idpessoa";
    }

    public static function buscarEmailsOficiaisPositivosPorSecretaria()
    {
        return "SELECT email,p.receberes
                from pessoa p,pessoacontato c
                where p.status='ATIVO'
                    and p.receberes is not null and p.receberes !=''
                    and p.email is not null and p.email != ''
                    and p.idpessoa = c.idcontato
                    and c.idpessoa= ?idsecretaria?";
    }

    public static function buscarEmailsOficiaisPorSecretaria()
    {
        return "SELECT email,p.receberestodos	as receberes
                from pessoa p,pessoacontato c
                where p.status='ATIVO'
                    and p.receberestodos is not null and p.receberestodos !=''
                    and p.idpessoa = c.idcontato
                    and c.idpessoa= ?idsecretaria?";
    }

    public static function buscarEmailsOficiaisPorIdpessoaIdempresa()
    {
        return "SELECT 
                        pc.idcontato, p.email, pc.receberes as tipo,p.usuario, pc.somenteoficial
                    FROM
                        pessoacontato pc
                            JOIN
                        pessoa p ON (pc.idcontato = p.idpessoa)
                    WHERE
                        pc.idpessoa = ?idpessoa? AND p.idtipopessoa = 3
                            AND p.email <> ''
                            AND pc.receberes <> ''
                            AND p.usuario <> ''
                            AND p.idempresa = ?idempresa?";
    }

    public static function buscarEmailsOficiaisPorIdpessoaIdempresa2()
    {
        return "SELECT 
                        pc.idcontato, p.email, pc.receberestodos as tipo,p.usuario, pc.somenteoficial
                    FROM
                        pessoacontato pc
                            JOIN
                        pessoa p ON (pc.idcontato = p.idpessoa)
                    WHERE
                        pc.idpessoa = ?idpessoa? AND p.idtipopessoa = 3
                            AND p.email <> ''
                            AND p.usuario <> ''
                            AND pc.receberestodos <> ''
                            AND p.idempresa =?idempresa?";
    }
}
?>