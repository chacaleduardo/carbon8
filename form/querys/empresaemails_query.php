<?
require_once(__DIR__.'/_iquery.php');

class EmpresaEmailsQuery
{
    public static function buscarQuantidadeTipoEnvioEmpesaEmails()
    {
        return "SELECT count(1) as qtd, tipoenvio 
				  FROM empresaemails 
				 WHERE idempresa = ?idempresa? 
			  GROUP BY tipoenvio";
    }

    public static function buscarDominio()
    {
        return "SELECT em.idemailvirtualconf, em.idempresa, ev.email_original AS dominio, em.tipoenvio
                  FROM empresaemails em
                  JOIN emailvirtualconf ev on (em.idemailvirtualconf = ev.idemailvirtualconf)
                 WHERE em.idempresa = ?idempresa?
                   AND ev.status = 'ATIVO'";

                   
    }

    public static function buscarDominioPorTipoenvio()
    {
        return "SELECT em.idemailvirtualconf, em.idempresa, ev.email_original AS dominio, em.tipoenvio
                  FROM empresaemails em
                  JOIN emailvirtualconf ev on (em.idemailvirtualconf = ev.idemailvirtualconf)
                 WHERE em.idempresa = ?idempresa?
                 ?and?
                   AND ev.status = 'ATIVO'";

                   
    }

    public static function buscarEmailOrcamentoProduto()
    {
        return "SELECT 
                    *
                FROM
                    empresaemails
                WHERE
                    tipoenvio = 'ORCPROD' AND idempresa = ?idempresa? ";
    }

    public static function buscarEmailOrcamentoProdutoPorNf()
    {
        return "SELECT 
                        *
                    FROM
                        empresaemailobjeto
                    WHERE
                        tipoenvio = 'ORCPROD'
                            AND tipoobjeto = 'nf'
                            AND idobjeto = ?idnf?
                            AND idempresa = ?idempresa?
                    ORDER BY idempresaemailobjeto DESC
                    LIMIT 1";
    }

    public static function buscarEmailfilaPorNf(){
        return "SELECT 
        m.idmailfila
    FROM
        mailfila m
    WHERE
        m.tipoobjeto = 'orcamentoprod'
            AND m.idobjeto = ?idnf?
         
    ORDER BY
        idmailfila DESC LIMIT 1";
    }

    public static function buscarEmailOrcamentoProdutoServicoPorEmpresa()
    {
        return "SELECT *
                 FROM empresaemails 
                 WHERE tipoenvio IN ('NFP','NFPS') 
                 ?idempresa? ";
    }

    public static function buscarEmpresaemailobjPorTipoId()
    {
        return "SELECT 
                    *
                FROM
                    empresaemailobjeto
                WHERE
                    tipoenvio = '?tipo?' AND tipoobjeto = 'nf'
                        AND idobjeto = ?idnf?       
                ORDER BY idempresaemailobjeto DESC
                LIMIT 1";
    }

    public static function buscarEmpresaemailobjPorTipoIdempresa()
    {
        return "SELECT 
                    em.idemailvirtualconf,
                    ev.email_original AS dominio,
                    em.idempresa
                FROM
                    empresaemails em
                        JOIN
                    emailvirtualconf ev ON (em.idemailvirtualconf = ev.idemailvirtualconf)
                WHERE
                    em.tipoenvio = '?tipo?'
                        AND ev.status = 'ATIVO'
                        AND em.idempresa = ?idempresa? ";
    }

    public static function buscarEmpresaemailCotacao()
    {
        return "SELECT ev.email_original as dominio 
                FROM empresaemails em
                JOIN emailvirtualconf ev on (em.idemailvirtualconf = ev.idemailvirtualconf)
                WHERE em.tipoenvio = '?tipoenvio?'
                AND em.idempresa = ?idempresa?
                AND ev.status = 'ATIVO'
                ORDER BY em.idempresaemails asc limit 1";
    }

}
?>