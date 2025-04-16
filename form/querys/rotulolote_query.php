<?

class RotuloLoteQuery {
    public static function buscarRotuloLotePorIdLote() {
        return "SELECT
        lr.idloterotulo,
        fr.status,
        fr.idformularotulo,
        fr.idprodservformularotulo,
        lr.formula as loterotulaformula,
        lr.cepas as loterotulacepas,
        lr.indicacao as loterotulaindicacao,
               r.titulo,
               concat('PART.:',lpad(l.npartida, 3, '0'),'/',SUBSTRING(l.exercicio,3)) as partida,
                               upper(concat('FABR.:',LEFT(DATE_FORMAT(l.fabricacao, '%M'),3),'/',right(DATE_FORMAT(l.fabricacao, '%Y'),2))) as fabricacao,
                               upper(concat('VENC.:',LEFT(DATE_FORMAT(l.vencimento, '%M'),3),'/',right(DATE_FORMAT(l.vencimento, '%Y'),2))) as vencimento,
                               IF(cs.tipobotao = 'FIM', lr.indicacao,  IFNULL(NULLIF(fr.indicacao, ''),  r.indicacao)) as indicacao,
                               IF(cs.tipobotao = 'FIM', lr.formula,  IFNULL(NULLIF(fr.formula, ''),  r.formula)) as formula,
                               r.idprodservformula,
                               IF(cs.tipobotao = 'FIM', lr.cepas,  IFNULL(NULLIF(fr.cepas, ''),  r.cepas)) as cepas,
                               IF(cs.tipobotao = 'FIM', lr.modousar, r.modousar) as modousar, 
                               IF(cs.tipobotao = 'FIM', lr.programa, r.programa) as programa, 
                               r.idfrasco,
                               IF(cs.tipobotao = 'FIM', lr.descricao, r.descricao) as descricao, 
                              IF(cs.tipobotao = 'FIM', lr.conteudo, r.conteudo) as conteudo
        from lote l 
        left join loterotulo lr on lr.idlote = l.idlote
        left join fluxostatus fs on (fs.idfluxostatus = l.idfluxostatus)
        left join carbonnovo._status cs on cs.idstatus = fs.idstatus
                   left join formularotulo fr on fr.idprodservformula = l.idprodservformula
                   left join prodservformularotulo r on (fr.idprodservformularotulo=r.idprodservformularotulo)
                where l.idlote =?idlote?";
    }

    public static function sincronizarDado() {
        return "UPDATE loterotulo
                ?updateformula?
                ?updatecepas?
                ?updateindicacao?
                where idloterotulo = ?idloterotulo?";
    }
}

?>