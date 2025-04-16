<?
class FluxoStatusLp {
    public static function buscarPermissaoTabelaPorLp() {
        return "SELECT permissao AS permissaolp 
            FROM ?tabela? t
                LEFT JOIN fluxostatuslp fl ON fl.idfluxostatus = t.idfluxostatus 
                    AND fl.idlp in (?lps?)
            WHERE ?primary? = ?idobjeto?
            ORDER BY permissao DESC LIMIT 1";
    }

    public static function buscarPermissaoTabelaPorLpPorIdFluxoStatus() {
        return "SELECT DISTINCT permissaobotao FROM(SELECT fl.permissaobotao
                                                      FROM carbonnovo._lp l JOIN fluxostatuslp fl ON l.idlp = fl.idlp
                                                      JOIN  lpobjeto lo ON lo.idlp = fl.idlp
                                                      WHERE fl.idfluxostatus = '?idfluxostatus?'
                                                       AND lo.idobjeto = '?idobjeto?'
                                                       AND lo.tipoobjeto = 'pessoa' 
                                                UNION ALL
                                                    SELECT fl.permissaobotao
                                                      FROM carbonnovo._lp l JOIN fluxostatuslp fl ON l.idlp = fl.idlp
                                                      JOIN lpobjeto lo ON lo.idlp = fl.idlp
                                                      JOIN sgconselho sc ON sc.idsgconselho = lo.idobjeto AND lo.tipoobjeto = 'sgconselho' 
                                                      JOIN pessoaobjeto po ON po.idobjeto = sc.idsgconselho AND po.tipoobjeto = 'sgconselho'
                                                     WHERE fl.idfluxostatus = '?idfluxostatus?'
                                                       AND po.idpessoa = '?idobjeto?'
                                                       AND sc.status = 'ATIVO' 
                                                UNION ALL 
                                                    SELECT  fl.permissaobotao
                                                       FROM carbonnovo._lp l JOIN fluxostatuslp fl ON l.idlp = fl.idlp
                                                       JOIN lpobjeto lo ON lo.idlp = fl.idlp
                                                       JOIN sgarea sa ON sa.idsgarea = lo.idobjeto AND lo.tipoobjeto = 'sgarea'
                                                       JOIN pessoaobjeto po ON po.idobjeto = sa.idsgarea AND po.tipoobjeto = 'sgarea'
                                                      WHERE fl.idfluxostatus = '?idfluxostatus?'
                                                        AND po.idpessoa = '?idobjeto?'
                                                       AND sa.status = 'ATIVO' 
                                                UNION ALL 
                                                    SELECT fl.permissaobotao
                                                      FROM carbonnovo._lp l JOIN fluxostatuslp fl ON l.idlp = fl.idlp
                                                      JOIN lpobjeto lo ON lo.idlp = fl.idlp
                                                      JOIN sgdepartamento sd ON sd.idsgdepartamento = lo.idobjeto AND lo.tipoobjeto = 'sgdepartamento'
                                                      JOIN pessoaobjeto po ON po.idobjeto = sd.idsgdepartamento AND po.tipoobjeto = 'sgdepartamento'
                                                     WHERE fl.idfluxostatus = '?idfluxostatus?'
                                                       AND po.idpessoa = '?idobjeto?'
                                                       AND sd.status = 'ATIVO' 
                                                UNION ALL 
                                                    SELECT fl.permissaobotao
                                                      FROM carbonnovo._lp l JOIN fluxostatuslp fl ON l.idlp = fl.idlp
                                                      JOIN lpobjeto lo ON lo.idlp = fl.idlp
                                                      JOIN sgsetor ss ON ss.idsgsetor = lo.idobjeto AND lo.tipoobjeto = 'sgsetor'
                                                      JOIN pessoaobjeto po ON po.idobjeto = ss.idsgsetor AND po.tipoobjeto = 'sgsetor'
                                                     WHERE fl.idfluxostatus = '?idfluxostatus?'
                                                       AND po.idpessoa = '?idobjeto?'
                                                       AND ss.status = 'ATIVO') as permissao
            ORDER BY permissaobotao DESC LIMIT 1";
    }
}
?>