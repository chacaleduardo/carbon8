<?

class EmailVirtualConfQuery
{
    public static function buscarEmailVirtualConfPorIdPessoaEmail()
    {
        return "SELECT * 
                FROM emailvirtualconf
                WHERE 1
                ?getidempresa?
                AND tipoemailvirtual='PESSOA'
                AND idpessoaemail = ?idpessoaemail?";
    }

    public static function buscarEmailOriginal()
    {
        return "SELECT idemailvirtualconf,
                        email_original 
                FROM emailvirtualconf 
                WHERE email_original <> '' 
                    AND status = 'ATIVO' ";
    }

    public static function buscarGrupoDeAssinaturaPorEmailDestinoComNotExistsPorIdPessoa()
    {
        return "SELECT w.email, group_concat(wp.idwebmailassinaturaobjeto) as idwebmailassinaturaobjetos
                FROM emailvirtualconf e
                JOIN webmailassinatura w ON (e.email_original = w.email)
                JOIN webmailassinaturaobjeto wp ON (w.idwebmailassinatura = wp.idwebmailassinatura)
                WHERE w.status = 'ATIVO'
                AND tipoobjeto = 'emailvirtualconf'
                AND w.email <> ''
                AND e.emails_destino LIKE '%?emaildestino?%'
                AND e.status = 'ATIVO'
                AND NOT EXISTS(
                    SELECT 1
                    FROM webmailassinaturaobjeto wp1
                    WHERE wp1.idobjeto = ?idpessoa?
                    AND wp1.tipoobjeto = 'pessoa'
                    AND wp1.idwebmailassinatura = wp.idwebmailassinatura
                    AND wp1.idwebmailassinaturatemplate = wp.idwebmailassinaturatemplate
                )
                AND wp.tipo = 'EMAILVIRTUAL'
                GROUP BY w.idwebmailassinatura";
    }

    public static function buscarEmailsVirtuaisDisponiveisParaVinculoPorIdImGrupoEGetIdEmpresa()
    {
        return "SELECT * 
                FROM emailvirtualconf e
                WHERE 1 ?getidempresa?
                AND e.tipoemailvirtual = 'GRUPO'
                AND e.status = 'ATIVO'
                AND NOT EXISTS (
                        SELECT 1 
                        FROM emailvirtualconfimgrupo i 
                        WHERE 1 ?getidempresa2?
                        AND i.idimgrupo = '?idimgrupo?' 
                        AND i.idemailvirtualconf = e.idemailvirtualconf
                    )";
    }

    public static function buscarGruposDisponiveisParaVinculoPorIdEmailVirtualConf()
    {
        return "SELECT p.idimgrupo, CONCAT(e.sigla, ' - ', p.grupo) as nome
                FROM imgrupo p JOIN empresa e ON e.idempresa = p.idempresa
                WHERE NOT EXISTS (
                    SELECT 1 
                    FROM emailvirtualconfimgrupo c
                    WHERE c.idimgrupo = p.idimgrupo 
                    AND c.idemailvirtualconf = ?idemailvirtualconf?
                )
                and p.status in ('ATIVO')
                order by p.grupo";
    }

    public static function buscarEmailsDeDestinoPorIdEmailVirtualConfEGetIdEmpresa()
    {
        return "SELECT idemailvirtualconf,original,GROUP_CONCAT(emaildest SEPARATOR ' ')  as emaildest
                FROM
                (
                    SELECT e.idemailvirtualconf as idemailvirtualconf,e.email_original as original,if(p.webmailemail <> '',p.webmailemail,p.email) AS emaildest
                    FROM emailvirtualconf e
                    JOIN emailvirtualconfpessoa ec ON (ec.idemailvirtualconf = e.idemailvirtualconf)
                    JOIN pessoa p ON (ec.idpessoa = p.idpessoa)
                    WHERE p.status = 'ATIVO'
                    AND e.status = 'ATIVO'
                    AND (webmailemail <> '' or email <> '')
                    AND if(p.webmailemail <> '',p.webmailemail REGEXP '^[A-Za-z0-9._]+@[A-Za-z0-9.-]+[.][A-Za-z]+$',p.email REGEXP '^[A-Za-z0-9._]+@[A-Za-z0-9.-]+[.][A-Za-z]+$')
                UNION
                SELECT 
                    em.idemailvirtualconf as idemailvirtualconf,em.email_original as original,if(p.webmailemail <> '',p.webmailemail,p.email) AS emaildest
                FROM emailvirtualconf em
                    join emailvirtualconfimgrupo evg on (em.idemailvirtualconf = evg.idemailvirtualconf)
                    join imgrupopessoa igp on (evg.idimgrupo = igp.idimgrupo)
                    join imgrupo im on (igp.idimgrupo = im.idimgrupo)
                    join pessoa p on (igp.idpessoa = p.idpessoa)
                WHERE
                    em.tipoemailvirtual = 'GRUPO'
                    AND im.status = 'ATIVO'
                    AND p.status = 'ATIVO'
                    AND em.status = 'ATIVO'
                    AND (webmailemail <> '' or email <> '')
                    AND if(p.webmailemail <> '',p.webmailemail REGEXP '^[A-Za-z0-9._]+@[A-Za-z0-9.-]+[.][A-Za-z]+$',p.email REGEXP '^[A-Za-z0-9._]+@[A-Za-z0-9.-]+[.][A-Za-z]+$')
            ) as u
            WHERE u.idemailvirtualconf = ?idemailvirtualconf?
            GROUP BY idemailvirtualconf";
    }

    public static function buscarEmailsDeDestinoPorGetIdEmpresa()
    {
        return "SELECT idemailvirtualconf,original,GROUP_CONCAT(emaildest SEPARATOR ' ')  as emaildest
                FROM (
                    SELECT e.idemailvirtualconf as idemailvirtualconf,e.email_original as original,p.webmailemail AS emaildest
                    FROM emailvirtualconf e
                    JOIN emailvirtualconfpessoa ec ON (ec.idemailvirtualconf = e.idemailvirtualconf)
                    JOIN pessoa p ON (ec.idpessoa = p.idpessoa)
                    WHERE p.status = 'ATIVO'
                        AND (webmailemail <> '' or email <> '')
                        AND e.tipoemailvirtual = 'GRUPO'
                        AND e.status = 'ATIVO'
                        AND if(p.webmailemail <> '',p.webmailemail REGEXP '^[A-Za-z0-9._]+@[A-Za-z0-9.-]+[.][A-Za-z]+$',p.email REGEXP '^[A-Za-z0-9._]+@[A-Za-z0-9.-]+[.][A-Za-z]+$')
                UNION
                SELECT 
                    em.idemailvirtualconf as idemailvirtualconf,em.email_original as original,p.webmailemail AS emaildest
                FROM emailvirtualconf em
                    join emailvirtualconfimgrupo evg on (em.idemailvirtualconf = evg.idemailvirtualconf)
                    join imgrupopessoa igp on (evg.idimgrupo = igp.idimgrupo)
                    join imgrupo im on (igp.idimgrupo = im.idimgrupo)
                    join pessoa p on (igp.idpessoa = p.idpessoa)
                WHERE em.tipoemailvirtual = 'GRUPO'
                    AND em.status = 'ATIVO'
                    AND im.status = 'ATIVO'
                    AND p.status = 'ATIVO'
                    AND (webmailemail <> '' or email <> '')
                    AND if(p.webmailemail <> '',p.webmailemail REGEXP '^[A-Za-z0-9._]+@[A-Za-z0-9.-]+[.][A-Za-z]+$',p.email REGEXP '^[A-Za-z0-9._]+@[A-Za-z0-9.-]+[.][A-Za-z]+$')
            ) as u
        GROUP BY idemailvirtualconf";
    }

    public static function atualizarEmailOriginalEEmailDestinoPorIdEmailVirtualConf()
    {
        return "UPDATE emailvirtualconf 
                SET email_original = '?emailoriginal?', emails_destino = '?emaildestino?'
                WHERE idemailvirtualconf = ?idemailvirtualconf?";
    }

    public static function buscarEmailsParaEnvioDeEmailCotacao()
    {
        return "SELECT v.email_original as email
                FROM empresaemailobjeto e 
                    JOIN emailvirtualconf v ON (e.idemailvirtualconf = v.idemailvirtualconf) 
                WHERE e.tipoenvio = '?tipoenvio?'
                    AND e.tipoobjeto = 'nf'
                    AND e.idobjeto = ?idnf?
                    AND e.idempresa = ?idempresa?
                    AND v.status = 'ATIVO'
                ORDER BY e.idempresaemailobjeto desc limit 1";
    }

    public static function buscarDominioDaempresaPorTipoEnvio(){
        return "SELECT idemailvirtualconf 
                                FROM empresaemails
                                WHERE tipoenvio = '?tipoenvio?'
                                AND idempresa = ?idempresa?;";
    }
}

?>