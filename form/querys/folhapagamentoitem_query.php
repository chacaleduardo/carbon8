<?
require_once(__DIR__."/_iquery.php");

class FolhapaPamentoItemQuery
{
    public static function buscarGruposConciliacao()
    {
        return "SELECT cfi.datalancamento, 
                       cfi.descricaolancamento,                         
                       cfi.codigoevento, 
                       GROUP_CONCAT(DISTINCT cfi.descricaolancamento) AS descricaolancamento, 
                       cfi.historicoevento, 
                       p.idpessoa, 
                       IFNULL(p.nomecurto, p.nome) AS nomecurto, 
                       p.idempresa,
                       SUM(cfi.valorlancamento) AS valorlancamento,
                       COUNT(descricaolancamento) AS contlancamento,
                       n.idnf
                  FROM folhapagamentoitem cfi JOIN pessoa p ON p.idpessoa = cfi.idpessoa
             LEFT JOIN nf n ON n.idobjetosolipor = cfi.idfolhapagamento AND n.tipoobjetosolipor = 'folhapagamento' AND n.controle = cfi.codigoevento
                 WHERE cfi.idfolhapagamento = '?idfolhapagamento?'
                 GROUP BY cfi.codigoevento, cfi.datalancamento
                 ORDER BY cfi.datalancamento";
    }

    public static function buscarDetalhamentoLancamento()
    {
        return "SELECT cfi.idfolhapagamentoitem,
                       cfi.datalancamento, 
                       cfi.descricaolancamento, 
                       cfi.valorlancamento, 
                       p.idpessoa, 
                       IF(p.nomecurto IS NULL, p.nome, p.nomecurto) AS nomecurto,
                       CONCAT(IF(p.nomecurto IS NULL, p.nome, p.nomecurto),' - ', cfi.descricaolancamento,' - ', cfi.historicoevento) AS descricaoitem, 
                       p.idempresa,
                       cc.idpessoa AS idfornecedor,
                       cc.idformapagamento,
                       cc.idconfcontapagar,
                       cc.tipo AS tipoorc,
                       rfi.idcontaitem,
                       rfi.idtipoprodserv,
                       cc.tpnf,
                       n.idnf
                  FROM folhapagamentoitem cfi JOIN pessoa p ON p.idpessoa = cfi.idpessoa
                  JOIN rhtipoevento rt ON rt.historicodominio = cfi.codigoevento
                  JOIN confcontapagar cc ON cc.idrhtipoevento = rt.idrhtipoevento AND cc.idempresa = cfi.idempresa
             LEFT JOIN rheventofolha rf ON rf.idrheventofolha = p.idrheventofolha 
             LEFT JOIN rheventofolhaitem rfi ON rfi.idrheventofolha = rf.idrheventofolha AND rfi.idrhtipoevento = rt.idrhtipoevento
             LEFT JOIN nf n ON n.idobjetosolipor = cfi.idfolhapagamento AND n.tipoobjetosolipor = 'folhapagamento' AND n.idpessoa = cc.idpessoa AND n.controle = '?codigoevento?'
                 WHERE cfi.idfolhapagamento = '?idfolhapagamento?'
                   AND cfi.codigoevento = '?codigoevento?'
                 ORDER BY p.nome";
    }

    public static function buscarLancamentosRepetidos()
    {
        return "SELECT 1
                  FROM folhapagamentoitem cfi
                 WHERE cfi.datalancamento = '?datalancamento?'
                   AND cfi.idpessoa = '?idpessoa?'
                   AND cfi.valorLancamento = '?valorLancamento?'
                   AND cfi.codigoevento = '?codigoevento?'";
    }

    public static function buscarClassificacao(){
        return "SELECT cc.tipo, IF(historicoevento LIKE '%FERIAS%', 'FOLHAFERIAS', '') as tipofolha
                  FROM folhapagamentoitem fpi JOIN rhtipoevento re ON re.historicodominio = fpi.codigoevento
                  JOIN confcontapagar cc ON cc.idconfcontapagar = re.idconfcontapagar
                 WHERE fpi.codigoevento = '?codigoevento?'";
    }

    public static function removerLancamentoFolhaPonto()
    {
        return "DELETE FROM folhapagamentoitem WHERE idfolhapagamento = '?idfolhapagamento?'";
    }
}
?>