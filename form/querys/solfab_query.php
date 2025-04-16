<?
class SolfabQuery{   

    public static function buscarSolfabPorIds(){
        return "SELECT 
                        s.idsolfab,
                        CONCAT(s.idsolfab,
                                '-',
                                l.partida,
                                '/',
                                l.exercicio) AS solfab
                    FROM
                        solfab s,
                        lote l
                    WHERE
                        s.idsolfab IN (?stridsf?)
                            AND l.idlote = s.idlote";

    }

    public static function buscarItensSolfabPorIdpessoEWhere()
    {
        return "SELECT s.idsolfab, l.idprodserv
                  FROM solfab s JOIN solfabitem si ON (s.idsolfab = si.idsolfab)
                  JOIN lote l ON (l.idlote = si.idobjeto AND si.tipoobjeto = 'lote')
                 WHERE s.idpessoa = ?idpessoa?
                   AND s.status NOT IN ('REPROVADO' , 'CANCELADO')
                   ?WherePedido?
              ORDER BY idprodserv";
    }

    public static function buscarSolfabELotePool()
    {
        return "SELECT DATE_FORMAT(vs.criadoem, '%d/%m/%Y %H:%i:%s') AS criadoem,
                       vs.idpessoa,
                       vs.idsolfab,
                       lp.idpool,
                       vs.rotulosolfab,
                       vs.exercicio,
                       vs.statussolfab,
                       vs.statuslotesolfab,
                       vs.idlotesolfab,
                       vs.idsolfabitem,
                       vs.idloteitem,
                       vs.rotuloloteitem,
                       vs.idprodserv,
                       vs.statuslotesolfabitem,
                       vs.statussemente,
                       pi.codprodserv,
                       vs.situacao,
                       vs.tipificacao,
                       vs.orgao,
                       vs.alerta,
                       vs.flgalerta,
                       po.ord
                  FROM vwsolfab vs LEFT JOIN prodserv pi ON pi.idprodserv = vs.idprodserv
             LEFT JOIN lotepool lp ON (vs.idloteitem = lp.idlote AND lp.status = 'ATIVO')
             LEFT JOIN pool po ON (lp.idpool = po.idpool AND po.status = 'ATIVO')
                 WHERE ?sqlin?
              ORDER BY vs.criadoem, lp.idpool , vs.idsolfab DESC";
    }

    public static function buscarDataAprovacaoSolfab()
    {
        return "SELECT dataaprovacao FROM solfab WHERE idsolfab = ?idsolfab?";
    }

    public static function buscarSolfabJoinLotePorIdSolfab()
    {
        return "SELECT s.idsolfab, l.partida, l.exercicio
                  FROM solfab s LEFT JOIN lote l ON (l.idlote = s.idlote)
                 WHERE s.idsolfab = ?idsolfab?";
    }

    public static function buscarStatusSolfabPorIdSolfabEIdEmpresa()
    {
        return "SELECT status FROM solfab WHERE idsolfab = ?idsolfab? ?getidempresa?";
    }

    public static function buscarDadosSolfabRelatorio()
    {
        return "SELECT l.exercicio,
                       l.qtdpedida,
                       ps.descr AS descr_prod,
                       CONVERT( LPAD(REPLACE(l.partida, ps.codprodserv, ''), '3', '0') USING LATIN1) AS partida,
                       p.razaosocial, 
                       e.nomepropriedade AS nome,
                       (SELECT CONCAT(IFNULL(en.logradouro, ''), ' ', 
                                      IFNULL(en.endereco, ''), ', ', 
                                      IFNULL(en.numero, ''), ', ',
                                      IF((IFNULL(en.complemento, '') <> ''), CONCAT(IFNULL(en.complemento, ''), ', '), ''),
                                      IFNULL(en.bairro, ''), ' - ', CONCAT(SUBSTR(en.cep, 1, 5), '-', SUBSTR(en.cep, 6, 3)), ' - ',
                                      IFNULL(cs.cidade, ''), '/',
                                      IFNULL(en.uf, ''))
                          FROM endereco en LEFT JOIN nfscidadesiaf cs ON cs.codcidade = en.codcidade
                         WHERE en.status = 'ATIVO'
                           AND en.idpessoa = l.idpessoa
                           AND en.idtipoendereco = 6) AS enderecosacado,
                       e.cnpjend AS cpfcnpj,
                       e.inscest AS inscrest,
                       s.*
                  FROM lote l JOIN pessoa p ON p.idpessoa = l.idpessoa
                  JOIN prodserv ps ON ps.idprodserv = l.idprodserv
                  JOIN solfab s ON s.idlote = l.idlote
             LEFT JOIN endereco e ON (l.idpessoa = e.idpessoa AND e.status = 'ATIVO' AND e.idtipoendereco = 6)
                 WHERE s.idsolfab = ?idsolfab?";
    }
}
?>