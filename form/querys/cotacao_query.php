<?
class CotacaoQuery
{
	public static function buscarItensSemelhantes()
    {
        return "SELECT ((ni.total+ifnull(ni.valipi,0)) / (IF(IFNULL(ni.qtd, 1) = 0, 1, ni.qtd) * IF((pf.valconv < 1 OR pf.valconv IS NULL), 1, pf.valconv))) AS vlritem, 
                        n.idnf,
                        UPPER(nome) AS nome,
                        ni.idprodserv,
                        ni.idnfitem,
                        ni.nfe,
                        st.rotulo
                   FROM nf n JOIN cotacao c ON c.idcotacao = n.idobjetosolipor AND n.tipoobjetosolipor = 'cotacao'
                   JOIN nfitem ni ON ni.idnf = n.idnf
                   JOIN fluxostatus fs ON fs.idfluxostatus = n.idfluxostatus
                   JOIN "._DBCARBON."._status st ON st.idstatus = fs.idstatus
              LEFT JOIN prodserv ps ON ps.idprodserv = ni.idprodserv
              LEFT JOIN prodservforn pf ON pf.idprodserv = ni.idprodserv AND pf.idprodservforn = ni.idprodservforn AND pf.status = 'ATIVO' AND pf.idpessoa = n.idpessoa
                   JOIN pessoa p ON p.idpessoa = n.idpessoa
                  WHERE ni.idprodserv IN (?idprodservs?) AND c.idcotacao = ?idcotacao?
               ORDER BY vlritem";
    }

    public static function listarCotacao()
    {
        return "SELECT c.idcotacao,
                       CONCAT(c.idcotacao, ' - ', c.titulo) AS titulo,
                       GROUP_CONCAT(ov1.idobjetovinc) AS concatcontaitemtipoprodserv,
                       GROUP_CONCAT(ov2.idobjetovinc) AS concatcontaitem
                  FROM cotacao c JOIN objetovinculo ov1 ON ov1.idobjeto = c.idcotacao AND ov1.tipoobjeto = 'cotacao' AND ov1.tipoobjetovinc = 'contaitemtipoprodserv' AND ov1.idobjetovinc IN (?idTipoItens?)
             LEFT JOIN objetovinculo ov2 ON ov2.idobjeto = c.idcotacao AND ov2.tipoobjeto = 'cotacao' AND ov2.tipoobjetovinc = 'contaitem' AND ov2.idobjetovinc IN (?idcontaitens?)
                 WHERE idcotacao != ?idobjeto? 
                  AND c.status NOT IN ('CONCLUIDO' , 'CANCELADO')
                  AND c.idempresa = ?idempresa?
             GROUP BY c.idcotacao
              ORDER BY titulo";
    }

    public static function listarSugestaoTodos()
    {
        return "SELECT p.idprodserv,
                       p.codprodserv,
                       p.descr,
                       p.un,
                       p.pedido_automatico,
                       p.pedidoautomatico,
                       p.idunidadeest,
                       p.estmin,
                       p.tempocompra,
                       p.qtdest,
                       p.estmin,
                       p.destoque,
                       p.mediadiaria,
                       p.tempocompra,
                       p.sugestaocompra,
                       p.sugestaocompra2,
                       ps.nome, 
                       p.estminautomatico,
                       p.tempocompra,
                       uv2.descr AS unidadeprod,
                       ps.idpessoa, 
                       f.unforn, 
                       f.valconv,
                       f.idprodservforn,
                       f.codforn,
                       uv.descr AS unidadedescr,
                       c.idcotacao,
                       c.prazo,
                       c.status AS statusorc,
                       s.rotulo
                  FROM vw8prodestoque p LEFT JOIN prodservforn f ON f.idprodserv = p.idprodserv AND f.status = 'ATIVO' AND f.multiempresa = 'N'
             LEFT JOIN unidadevolume uv ON uv.un = f.unforn
             LEFT JOIN unidadevolume uv2 ON uv2.un = p.un
             LEFT JOIN pessoa ps ON (ps.idpessoa = f.idpessoa)
             LEFT JOIN cotacao c ON c.idcotacao = p.ultimoorcamento 
             LEFT JOIN fluxostatus fs ON fs.idfluxostatus = c.idfluxostatus
             LEFT JOIN "._DBCARBON."._status s ON s.idstatus = fs.idstatus
                 WHERE p.status = 'ATIVO' AND p.idtipoprodserv IN (?idtipoprodserv?) AND p.comprado = 'Y' ?where?
                AND EXISTS (SELECT 1 FROM prodservcontaitem pi JOIN objetovinculo ov ON ov.idobjetovinc = pi.idcontaitem AND ov.idobjeto = ?idobjeto? AND ov.tipoobjeto = 'cotacao' AND ov.tipoobjetovinc = 'contaitem'
                                    WHERE pi.idprodserv = p.idprodserv)
                AND p.idempresa = ?idempresa?
            ORDER BY p.descr, ps.nome;";
    }

    public static function buscarCotacaoDisponivelPorGrupoEsTipoItem()
    {
        return "SELECT c.idcotacao, CONCAT(e.sigla,' - ', c.titulo) AS titulo, c.prazo, c.status, p.nome 
                  FROM cotacao c JOIN objetovinculo ov ON ov.idobjeto = c.idcotacao AND ov.tipoobjeto = 'cotacao'
                  JOIN pessoa p ON p.idpessoa = c.idresponsavel
                  JOIN prodservcontaitem pc ON pc.idcontaitem = ov.idobjetovinc AND ov.tipoobjetovinc = 'contaitem'
                  JOIN prodserv ps ON ps.idprodserv = pc.idprodserv
                  JOIN empresa e ON e.idempresa = c.idempresa
                  JOIN contaitemtipoprodserv ctp ON ctp.idcontaitem = ov.idobjetovinc AND ov.tipoobjetovinc = 'contaitem' 
                  JOIN objetovinculo ov2 ON ov2.idobjetovinc = ctp.idtipoprodserv AND ov2.tipoobjetovinc = 'contaitemtipoprodserv' 
                   AND ov2.idobjeto = c.idcotacao AND ov2.tipoobjeto = 'cotacao' AND ps.idtipoprodserv = ov2.idobjetovinc
                 WHERE pc.idprodserv = ?idprodserv?
                   AND c.status NOT IN ('CONCLUIDO', 'CANCELADO')
                   AND c.idempresa = ps.idempresa
              ORDER BY prazo DESC";
    }

    public static function buscarCotacoesParaEnvioDeEmail()
    {
        return "SELECT co.idcotacao,
                        p.emailresult as emailresult,
                        p.emailresult as tememail,
                        p.nome,
                        p.idpessoa,
                        c.idnf,
                        concat(co.idcotacao,'.',c.idnf) as nsolicitacao,
                        DATEDIFF(co.prazo,sysdate()) as prazo,
                        dma(co.prazo) as dmaprazo,
                        c.idobjetosolipor,
                        c.tipoobjetosolipor,
                        c.alteradopor,
                        c.idempresa
                FROM nf c,cotacao co,pessoa p
                WHERE  c.tiponf in ('C','S', 'M', 'B')
                    and c.envioemailorc='Y'
                    and co.idcotacao = c.idobjetosolipor 
                    and c.tipoobjetosolipor ='cotacao'
                    and p.idpessoa = c.idpessoa";
    }

    public static function buscarCotacoesAprovadaParaEnvioDeEmail()
    {
        return "SELECT p.emailresult as emailresult,
                        p.nome,p.idpessoa,
                        c.idnf,
                        co.idcotacao,
                        concat(co.idcotacao,'.',c.idnf) as ncompra,
                        DATEDIFF(co.prazo,sysdate())+365 as prazo,
                        dma(DATE_ADD(co.prazo, INTERVAL 365 DAY)) as dmaprazo,
                        c.idobjetosolipor,c.tipoobjetosolipor,c.alteradopor,c.idempresa
                FROM cotacao co, nf c,pessoa p
                WHERE co.idcotacao = c.idobjetosolipor
                    and c.tipoobjetosolipor='cotacao' 
                    and p.status = 'ATIVO'
                    and p.idpessoa = c.idpessoa
                    and c.status = 'APROVADO' 
                    and c.emailaprovacao = 'Y'";
    }

    public static function listarNfitemsxmlPorIdprodserv()
    {
        return "SELECT 
                    n.idnf,
                    e.idempresa,
                    e.sigla,
                    n.dtemissao,
                    p.idpessoa,
                    p.nome,
                    x.valor / x.qtd AS valorun,
                    x.qtd,
                    x.cprod,
                    x.un
                FROM
                    nfitemxml i
                        JOIN
                    nfitemxml x ON (x.cprod = i.cprod AND x.status = 'Y')
                        JOIN
                    nf n ON (n.idnf = x.idnf)
                        JOIN
                    pessoa p ON (p.idpessoa = n.idpessoa)
                        JOIN
                    prodserv ps ON (ps.idprodserv = i.idprodserv)
                        JOIN
                    empresa e ON (e.idempresa = x.idempresa)
                WHERE
                    i.idprodserv = ?idprodserv? AND i.status = 'Y'
                GROUP BY x.idnfitemxml
                ORDER BY dtemissao
                LIMIT 8";
    }
}

?>