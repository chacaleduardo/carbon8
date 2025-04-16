<?
class _SnippetQuery
{
    public static function buscarSnippetsPorLpIdEmpresaEModulos()
    {
        return "SELECT 
                    u.idsnippet,
                    u.snippet,
                    IF(a.nome IS NULL,
                        u.cssicone,
                        CONCAT('/upload/', a.nome)) AS cssicone,
                    u.code,
                    u.msgconfirm,
                    u.tipo,
                    u.modulo,
                    u.modulopar,
                    u.ordem
                FROM (
                        SELECT DISTINCT
                            s.idsnippet,
                            s.snippet,
                            s.cssicone,
                            s.code,
                            s.msgconfirm,
                            s.tipo,
                            s.modulo,
                            m.modulopar,
                            s.ordem
                        FROM "._DBCARBON."._snippet s
                        JOIN "._DBCARBON."._lpobjeto lo ON lo.tipoobjeto = '_snippet'
                        JOIN carbonnovo._modulo m on(m.modulo = s.modulo)
                        AND lo.idobjeto = s.idsnippet
                        AND lo.idlp IN (?lps?)
                        WHERE s.status = 'ATIVO'
                        AND EXISTS ( 
                            SELECT 1
                            FROM carbonnovo._lp l
                            WHERE lo.idlp = l.idlp AND l.idempresa = ?idempresa?
                        )
                        AND m.tipo != 'SNIPPET'
                        GROUP BY s.snippet 
                        UNION ALL 
                        SELECT 
                            idmodulo,
                            rotulomenu,
                            cssicone,
                            CONCAT('window.location.href=\"?_modulo=',m.modulo,'&_idempresa=?idempresa??mostrarMenu?\"'),
                            NULL,
                            'MOD',
                            modulo,
                            m.modulopar,
                            m.ord
                        FROM "._DBCARBON."._modulo m
                        WHERE m.tipo = 'SNIPPET'
                        AND m.status = 'ATIVO'
                        AND m.modulo IN (?modulos?)
                ) AS u
                LEFT JOIN carbonnovo._modulo m ON (m.modulo = u.modulo)
                LEFT JOIN arquivo a ON (a.idobjeto = m.idmodulo AND a.tipoobjeto = '_modulo' AND a.tipoarquivo = 'SVG')
                ?modulopar?
                ORDER BY u.ordem";
    }

    public static function buscarSnippetPorNotificacaoEEmpresa()
    {
        return "SELECT idsnippet 
                  FROM "._DBCARBON."._snippet 
                 WHERE notificacao = 'Y'
                 ?getidempresa?";
    }
}
?>