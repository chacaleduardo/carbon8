<?
class LoteObjQuery{
    public static function deletarLoteObjPorLote () {
        return "DELETE FROM loteobj WHERE idlote = '?idlote?'";
    }

    public static function buscarObjetosLote()
    {
        return "SELECT idloteobj,
                       idloteativ,
                       descr,
                       idobjeto,
                       tipoobjeto,
                       qtd,
                       qtd_exp,
                       ord
                  FROM loteobj lo
                 WHERE lo.idlote = ?idlote?";
    }

    public static function inserirLoteObj()
    {
        return "INSERT INTO loteobj (idempresa,
                                     idlote,
                                     idprativ,
                                     idloteativ,
                                     idobjeto,
                                     tipoobjeto,
                                     criadopor,
                                     criadoem,
                                     alteradopor,
                                     alteradoem)
                             VALUES (?idempresa?,
                                     ?idlote?,
                                     ?idprativ?,
                                     ?idloteativ?,
                                     ?idobjeto?,
                                     '?tipoobjeto?',
                                     '?usuario?',
                                     SYSDATE(),
                                     '?usuario?',
                                     SYSDATE())";
    }

    public static function inserirLoteObjPorSelect()
    {
        return "INSERT INTO loteobj (idempresa,
                                     idlote,
                                     idprativ,
                                     idloteativ,
                                     idobjeto,
                                     tipoobjeto,
                                     descr,
                                     criadopor,
                                     criadoem,
                                     alteradopor,
                                     alteradoem)
                             (SELECT ?idempresa?,
                                     l.idlote,
                                     p.idprativ,
                                     l.idloteativ,
                                     CASE p.tipoobjeto WHEN 'ctrlproc' THEN p.idprativobj
                                                       WHEN 'prativobj' THEN p.idprativobj
                                                       WHEN 'materiais' THEN p.idprativobj
                                                       ELSE p.idobjeto END AS idobjeto,
                                     p.tipoobjeto,
                                     p.descr,
                                     '?usuario?',
                                     SYSDATE(),
                                     '?usuario?',
                                     SYSDATE()
                                FROM loteativ l JOIN prativobj p ON l.idprativ = p.idprativ
                                JOIN prativ a ON a.idprativ = p.idprativ
                               WHERE l.idlote = ?idlote?
                                 AND p.tipoobjeto != 'tagtipo'
                                 AND a.idprativ = ?idprativ?)";
    }

    public static function buscarSalaVinculadaAoLoteAtiv() {
        return "SELECT 
                    idloteobj,
                    idloteativ,
                    descr,
                    idobjeto,
                    tipoobjeto,
                    qtd,
                    qtd_exp,
                    ord
                FROM loteobj lo
                WHERE lo.idlote = ?idlote?
                and lo.tipoobjeto = 'tag'
                and lo.idloteativ  = ?idloteativ?
                and lo.idobjeto is not null;";
    }
}
?>