<?
class SolfabItemQuery
{
	public static function buscarDadosSolfabItem()
    {
        return "SELECT DISTINCT(tra.idamostra) AS idamostra,
                       tra.idregistro,
                       tra.exercicio,
                       tra.status,
                       tra.idunidade
                  FROM solfabitem i JOIN lote l ON l.idlote = i.idobjeto AND i.tipoobjeto = 'lote'
                  JOIN resultado r ON r.idresultado = l.idobjetosolipor AND l.tipoobjetosolipor = 'resultado'
                  JOIN amostra a ON r.idamostra = a.idamostra
                  JOIN amostra tra ON a.idamostratra = tra.idamostra
                 WHERE i.idsolfab = ?idsolfab?";
    }

    public static function buscarLoteSolfabItem()
    {
        return "SELECT idobjeto as idlote from solfabitem where idsolfab = ?idsolfab? and tipoobjeto = 'lote'";
    }

    public static function buscarItensSolfabRelatorio()
    {
        return "SELECT p.descr,
                       l.partida,
                       l.exercicio,
                       r.idresultado,
                       ta.idregistro,
                       ta.exercicio AS exercicioam,
                       (SELECT CONCAT(sf2.idsolfab, ' - ', sf2.nsei)
                          FROM solfabitem si2 JOIN solfab sf2 ON sf2.idsolfab = si2.idsolfab
                         WHERE si2.idobjeto = l.idlote
                           AND sf2.idsolfab != s.idsolfab
                           AND si2.tipoobjeto = 'lote'
                           AND sf2.status = 'APROVADO'
                      ORDER BY sf2.idsolfab DESC
                         LIMIT 1) AS ultimasolfab
                   FROM solfabitem s JOIN lote l ON l.idlote = s.idobjeto AND s.tipoobjeto = 'lote' and l.status!='CANCELADO'
                   JOIN prodserv p ON p.idprodserv = l.idprodserv
                   JOIN resultado r ON r.idresultado = l.idobjetosolipor AND l.tipoobjetosolipor = 'resultado'
                   JOIN amostra a ON a.idamostra = r.idamostra
                   JOIN amostra ta ON a.idamostratra = ta.idamostra
                  WHERE s.idsolfab = ?idsolfab?
               ORDER BY a.idregistro, a.exercicio, l.partida, l.exercicio";
    }

    public static function buscarItensSolfabRelatorioStatusNotIN()
    {
        return "SELECT CONCAT(a.dataamostra, ' ', TIME(a.criadoem)) AS dataamostrah,
                       p.descr,
                       l.partida,
                       l.exercicio,
                       r.idresultado,
                       a.idregistro,
                       a.exercicio AS exercicioam,
                       l.orgao,
                       (SELECT CONCAT(sf2.idsolfab, ' - ', sf2.nsei)
                          FROM solfabitem si2 JOIN solfab sf2 ON sf2.idsolfab = si2.idsolfab
                         WHERE si2.idobjeto = l.idlote
                           AND si2.tipoobjeto = 'lote'
                           AND sf2.status = 'APROVADO'
                      ORDER BY sf2.dataaprovacao ASC LIMIT 1) AS ultimasolfab
                 FROM solfabitem s JOIN lote l ON l.idlote = s.idobjeto AND s.tipoobjeto = 'lote' -- and l.status!='CANCELADO'
                 JOIN prodserv p ON p.idprodserv = l.idprodserv
                 JOIN resultado r ON r.idresultado = l.idobjetosolipor AND l.tipoobjetosolipor = 'resultado'
                 JOIN amostra a ON a.idamostra = r.idamostra
                WHERE r.status NOT IN ('CANCELADO' , 'OFFLINE')
                  AND s.idsolfab = ?idsolfab?
             ORDER BY r.idresultado, l.partida, l.exercicio";
    }

    public static function buscarAmostrasVinculadasSolfab()
    {
        return "SELECT DISTINCT(a.idamostra) AS idamostra,
                       a.idregistro,
                       a.exercicio,
                       a.status,
                       a.idunidade
                  FROM solfabitem i JOIN lote l ON i.idobjeto = l.idlote AND i.tipoobjeto = 'lote'
                  JOIN resultado r ON l.idobjetosolipor = r.idresultado AND l.tipoobjetosolipor = 'resultado'
                  JOIN amostra a ON a.idamostra = r.idamostra
                 WHERE i.idsolfab = ?idsolfab?
                   AND r.status NOT IN ('CANCELADO', 'OFFLINE')
                   AND NOT EXISTS(SELECT 1 FROM solfabitem i2 JOIN solfab s ON s.idsolfab = i2.idsolfab
                                   WHERE i2.idobjeto = i.idobjeto
                                     AND i2.tipoobjeto = 'lote'
                                     AND i2.idsolfab != i.idsolfab
                                     AND s.status = 'APROVADO')
              ORDER BY idamostra ASC";
    }

    public static function buscarArquivoSolfabItem()
    {
      return "SELECT DISTINCT(ar.idarquivo) AS id, ar.caminho
                FROM solfabitem i JOIN lote l ON l.idlote = i.idobjeto AND i.tipoobjeto = 'lote'
                JOIN resultado r ON r.idresultado = l.idobjetosolipor AND l.tipoobjetosolipor = 'resultado'
                JOIN amostra a ON a.idamostra = r.idamostra
                JOIN arquivo ar ON a.idamostratra = ar.idobjeto AND ar.tipoobjeto = 'amostra' AND ar.tipoarquivo = 'ANEXO'
               WHERE i.idsolfab = ?idsolfab?
                 AND r.status NOT IN ('CANCELADO', 'OFFLINE')";
    } 

    public static function atualizarAtualizarLotePorIdSolfab()
    {
        return "UPDATE solfabitem s JOIN lote l 
                   SET l.status = 'APROVADO',
                       l.situacao = 'APROVADO'
                 WHERE s.idsolfab = ?idsolfab?
                   AND l.idlote = s.idobjeto
                   AND s.tipoobjeto = 'lote'
                   AND l.status = 'AUTORIZADA'";
    }

    public static function inserirSolfabItem()
    {
        return "INSERT INTO solfabitem (idempresa, idsolfab, idobjeto, tipoobjeto, criadopor, criadoem, alteradopor, alteradoem)
                     VALUES (?idempresa?, ?idsolfab?, ?idobjeto?, '?tipoobjeto?', '?usuario?', SYSDATE(), '?usuario?', SYSDATE())";
    }

    public static function buscarLoteSolfab()
    {
        return "SELECT  idlote from solfab where idsolfab = ?idsolfab?";
    }

    
    public static function buscaInfRateioSemente()
    {
        return "SELECT 
                  r.custo,
                  (IFNULL(l.vlrlotetotal, 0) + r.custo) AS vlrlotetotal,
                  (IFNULL(l.vlrlotetotal, 0) + r.custo) / l.qtdprod AS vlrlote,
                  r.idresultado,
                  l.idlote,
                  a.idunidade,
                  d.idrateioitemdest
              FROM
                  lote l
                      JOIN
                  resultado r ON (r.idresultado = l.idobjetosolipor)
                      JOIN
                  amostra a ON (a.idamostra = r.idamostra)
                      JOIN
                  rateioitem ri ON ri.idobjeto = r.idresultado
                      AND ri.tipoobjeto = 'resultado'
                      JOIN
                  rateioitemdest d ON d.idrateioitem = ri.idrateioitem
                      AND d.custeado = 'N'
              WHERE
                  l.idlote = ?idlote? limit 1";
    }

    public static function atualizaValorLote()
    {
        return "UPDATE lote set vlrlote=vlrlote+'?vlrlote?',vlrlotetotal=vlrlotetotal+'?vlrlotetotal?'  where idlote = ?idlote?";
    }

    public static function atualizaRateioitemdest()
    {
        return "UPDATE rateioitemdest SET custeado='Y',idrateiocusto='?idrateiocusto?' where idrateioitemdest = ?idrateioitemdest?";
    }
}

?>