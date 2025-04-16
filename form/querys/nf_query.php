<?
class NfQuery
{
    public static function atualizarEnvioEmailNf()
    {
        return "UPDATE nf SET envioemail = '?envioemail?' WHERE idnf = ?idnf?";
    }

    public static function atualizarEnvioEmailNfComLog()
    {
        return "UPDATE nf SET envioemail = '?envioemail?', logemail = concat(ifnull(logemail,''),'?msg?') WHERE idnf = ?idnf?";
    }

    public static function atualizarNFParaEnviado()
    {
        return "UPDATE nf set envioemailorc = 'A', status = 'ENVIADO', idfluxostatus = 1159 where idnf = ?idnf?";
    }

    public static function atualizarEnvioEmailOrcParaO()
    {
        return "UPDATE nf set envioemailorc = '?envioemailorc?' where idnf =  ?idnf?";
    }

    public static function atualizarEnvioEmailOrcComLog()
    {
        return "UPDATE nf set envioemailorc = '?envioemailorc?', logemail = concat(ifnull(logemail,''),'?msg?') where idnf =  ?idnf?";
    }

    public static function atualizarEnvioEmailAprovacaoNF()
    {
        return "UPDATE nf set emailaprovacao = '?emailaprovacao?' where idnf =  ?idnf?";
    }

    public static function atualizarTrasnportadoraNF()
    {
        return "UPDATE nf set idtransportadora = '?idtransportadora?' where idnf =  ?idnf?";
    }

    public static function atualizarEnvioNfe()
    {
        return "UPDATE nf set envionfe = '?envionfe?' where idnf =  ?idnf?";
    }

    public static function buscarNFOrcamentoProdutoParaEnvioDeEmail()
    {
        return "SELECT n.idnf,
                        p.idpessoa,
                        p.nome as cliente,
                        n.emailorc as emailorc,
                        n.comissao,
                        n.alteradopor,
                        n.tipoobjetosolipor,
                        n.idobjetosolipor,
                        n.idempresa
                FROM nf n,pessoa p
                where 
                    p.idpessoa = n.idpessoa
                    and n.tiponf ='V'
                    and n.envioemailorc = 'Y'";
    }

    public static function buscarNFParaEnvioNFP()
    {
        return "SELECT n.emaildadosnfe,
                        n.emaildadosnfemat,
                        n.tipoenvioemail,
                        p.razaosocial,
                        n.idnf,
                        SUBSTRING(n.idnfe,4) as idnfe,
                        n.nnfe,
                        n.idnf,
                        n.emaildanfe,
                        n.emailboleto,
                        n.emailxml,
                        n.enviarastreador,
                        n.rastreador,
                        n.idempresa,
                        dma(prazo) as envio,
                        obsenvio as previsao,
                        t.nome as transportadora,
                        t.url,
                        p.idpessoa,
                        n.comissao,
                        n.tipoobjetosolipor,
                        n.idobjetosolipor,
                        n.alteradopor,
                        n.idendrotulo
                from pessoa p,
                    nf n
                    left join  pessoa t on (t.idpessoa = n.idtransportadora)
                where
                    p.idpessoa = n.idpessoa
                    and n.xmlret is not null
                    and n.envionfe = 'CONCLUIDA' 
                    and n.envioemail = 'Y'";
    }

    public static function buscarXMLParaEnvioDeEmail()
    {
        return "SELECT xmlret,  
                        SUBSTRING(idnfe,4) as idnfe,
                        nnfe
                from nf
                where
                    idnf = ?idnf?";
    }

    public static function buscarInfosEtiquetaPedidoGeral()
    {
        return "SELECT 
                    n.idnf,
                    n.nnfe,
                    n.idtransportadora,
                    n.qvol AS qvol,
                    p.nome AS transportadora,
                    `c`.`cidade` AS `cidade`,
                    `e`.`uf` AS `uf`,
                    emp.*,
                    IF((`e`.`idendereco` IS NOT NULL),
                        CONCAT(
                            IFNULL(NULL, `e`.`logradouro`),
                            ' ',
                            IFNULL(NULL, `e`.`endereco`),
                            ', ',
                            IFNULL(NULL, `e`.`numero`),
                            ' - ',
                            IFNULL(NULL, `e`.`complemento`)
                            ),
                        CONCAT(
                            IFNULL(NULL, `e2`.`logradouro`),
                            ' ',
                            IFNULL(NULL, `e2`.`endereco`),
                            ', ',
                            IFNULL(NULL, `e2`.`numero`),
                            ' - ',
                            IFNULL(NULL, `e2`.`complemento`),
                            ' - '
                        )
                    ) AS `enderecototal`,
                    e.cep,
                    e.bairro,
                    e.idpessoa,
                    IFNULL(n.idendrotulo,e2.idendereco) as idendereco
                FROM
                    (((((`nf` `n`
                    JOIN empresa emp ON (emp.idempresa = n.idempresa)
                    LEFT JOIN `endereco` `e` ON ((`e`.`idendereco` = `n`.`idendrotulo`)))
                    LEFT JOIN `endereco` `e2` ON (((`e2`.`idpessoa` = `n`.`idpessoa`)
                        AND (`e2`.`idtipoendereco` = 3)
                        AND (`e2`.`status` = 'ATIVO'))))
                    LEFT JOIN `nfscidadesiaf` `c` ON ((`e`.`codcidade` = `c`.`codcidade`)))
                    LEFT JOIN `nfscidadesiaf` `c2` ON ((`e2`.`codcidade` = `c2`.`codcidade`)))
                    LEFT JOIN `pessoa` `p` ON ((`p`.`idpessoa` = `n`.`idtransportadora`)))
                WHERE
                    idnf =  ?idnf?
                GROUP BY idnf";
    }

    public static function buscarValorTotalCotacao()
    {
        return "SELECT SUM(n.total) AS total
                  FROM nf n
                 WHERE n.idobjetosolipor = ?idcotacao?
                   AND n.tipoobjetosolipor = 'cotacao'
                   AND n.status not in('CANCELADO', 'REPROVADO')";
    }

    public static function buscarPendenciasPessoa()
    {
        return "SELECT 
                    (SELECT COUNT(*) FROM nf WHERE tiponf = 'C' AND status= 'DIVERGENCIA' AND dtemissao BETWEEN SUBDATE(sysdate(), INTERVAL 1 year) AND sysdate() AND idpessoa =?idpessoa?) AS  divergencia,
                    (SELECT COUNT(*) FROM nf WHERE tiponf = 'C'AND pendente = 'N' AND status= 'DIVERGENCIA' AND dtemissao BETWEEN SUBDATE(sysdate(), INTERVAL 1 year) AND sysdate() AND idpessoa =?idpessoa?) AS  normal,
                    (SELECT COUNT(*) FROM nf WHERE tiponf = 'C' AND pendente = 'Y' AND resolvido = 'N' AND status= 'DIVERGENCIA' AND dtemissao BETWEEN SUBDATE(sysdate(), INTERVAL 1 year) AND sysdate() AND idpessoa =?idpessoa?)  AS pendnresolv,
                    (SELECT COUNT(*) FROM nf WHERE tiponf = 'C' AND pendente = 'Y' AND resolvido = 'Y' AND status= 'DIVERGENCIA' AND dtemissao BETWEEN SUBDATE(sysdate(), INTERVAL 1 year) AND sysdate() AND idpessoa =?idpessoa?) AS pendresolv";
    }

    public static function buscarNfPorTipoObjetoSoliPor()
    {
        return "SELECT p.idpessoa,
                        p.nome,
                        p.observacaore,
                        p.emailresult,
                        p.cpfcnpj,
                        CASE WHEN (n.previsaoentrega < CURDATE() AND n.status = 'APROVADO') THEN 'atrasado' ELSE 'normal' END AS pedidoentrega,
                        n.status,
                        fs.ordem AS ordemstatus,
                        n.idnf,
                        n.tiponf,
                        n.idnforigem,
                        n.idfinalidadeprodserv,
                        n.dtemissao,
                        n.emailaprovacao,
                        n.envioemailorc,
                        n.pedidoext,
                        n.aoscuidados,
                        n.telefone,
                        n.marcartodosnfitem,
                        n.modfrete,
                        n.frete,
                        n.idtransportadora,
                        n.obsenvio,
                        n.formapgto,
                        n.idformapagamento,
                        n.diasentrada,
                        n.parcelas,
                        n.intervalo,
                        n.obs,
                        n.obsinterna,
                        n.idnfe,
                        n.criadoem,
                        n.criadopor
                FROM nf n JOIN pessoa p ON p.idpessoa = n.idpessoa
                JOIN fluxostatus fs ON n.idfluxostatus = fs.idfluxostatus 
               WHERE n.idobjetosolipor = ?idobjetosolipor? 
                     ?idempresa?
                     ?cancelado?
                 AND n.tipoobjetosolipor = '?tipoobjetosolipor?'
            ORDER BY fs.ordem, idnf ASC";
    }

    public static function buscarProdservPelaNf()
    {
        return "SELECT DISTINCT(i.idprodserv) as idprodserv,
                       i.qtd,
                       i.idnf
                  FROM nf n JOIN nfitem i ON i.idnf = n.idnf
                 WHERE n.idobjetosolipor = ?idobjetosolipor?
                   AND n.tipoobjetosolipor = '?tipoobjetosolipor?'
                   AND i.idprodserv IS NOT NULL
                   ?idempresa?
            GROUP BY i.idprodserv";
    }

    public static function buscarItensNfPorIdProdserv()
    {
        return "SELECT p.nome,
                       i.qtd,
                       i.total, 
                       i.nfe, 
                       i.idnfitem, 
                       i.vlritem, 
                       i.obs, 
                       n.status, 
                       i.previsaoentrega,
                       i.idnf, 
                       i.vlritemext, 
                       i.moedaext, 
                       i.totalext
                  FROM nf n JOIN nfitem i ON i.idnf = n.idnf
                  JOIN pessoa p ON p.idpessoa = n.idpessoa
                    WHERE n.idobjetosolipor = ?idobjetosolipor?
                    ?idempresa?
                    AND n.tipoobjetosolipor = '?tipoobjetosolipor?'
                    AND i.idprodserv = ?idprodserv? 
               ORDER BY p.nome";
    }

    public static function buscarSolicitacaoComprasAssociadoCotacao()
    {
        return "SELECT n.idnf, 
                       s.idsolcom, 
                       s.criadoem, 
                       s.criadopor, 
                       s.status, 
                       u.unidade, 
                       p.nomecurto, 
                       ps.descrcurta
                  FROM nf n JOIN nfitem ni ON ni.idnf = n.idnf AND n.idobjetosolipor = ?idobjetosolipor? AND tipoobjetosolipor = '?tipoobjetosolipor?'
                  JOIN prodserv ps ON ps.idprodserv = ni.idprodserv
                  JOIN solcomitem si ON si.idcotacao = n.idobjetosolipor AND si.idprodserv = ni.idprodserv
                  JOIN solcom s ON s.idsolcom = si.idsolcom
                  JOIN unidade u ON u.idunidade = s.idunidade
                  JOIN pessoa p ON p.idpessoa = s.idpessoa
                 WHERE n.status NOT IN ('REPROVADO', 'CANCELADO')";
    }

    public static function buscarDadosFornecedorNf()
    {
        return "SELECT c.prazo, n.criadoem, p.nomecurto
                  FROM nf n JOIN cotacao c
             LEFT JOIN pessoa p ON (c.idresponsavel = p.idpessoa)
                 WHERE n.idnf = ?idnf?
                   AND c.idcotacao = n.idobjetosolipor
                   AND n.tipoobjetosolipor = 'cotacao'";
    }

    public static function buscarItensNfPorIdNf()
    {
        return "SELECT p.idprodserv,
                       p.codprodserv,
                       p.descr,
                       p.un AS unidade,
                       p.validadeforn,
                       pf.codforn,
                       pf.unforn,
                       DMA(ci.validade) AS dmavalidade,
                       ci.*,
                       ci.un AS unidadeci
                  FROM nfitem ci JOIN nf n ON ci.idnf = n.idnf
             LEFT JOIN prodserv p ON p.idprodserv = ci.idprodserv
             LEFT JOIN prodservforn pf ON (pf.idprodserv = ci.idprodserv 
                   AND pf.idprodservforn = ci.idprodservforn
                   AND pf.status = 'ATIVO'
                   AND pf.idpessoa = n.idpessoa)
                 WHERE ci.nfe != 'C'
                   AND n.idnf = '?idnf?'
              ORDER BY p.descr";
    }

    public static function buscarIdNfPorTipoObjetoStatusIdpessoa()
    {
        return "SELECT f.idnf
                  FROM nf f
                 WHERE f.idobjetosolipor = ?idobjetosolipor?
                   AND f.tipoobjetosolipor = '?tipoobjetosolipor?'
                   AND f.status = '?status?'
                   AND f.idpessoa = ?idpessoa?";
    }

    public static function buscarFornecedoresPertencentesCotacao()
    {
        return "SELECT p.idprodservforn, 
                       p.converteest, 
                       p.unforn, 
                       p.valconv,
                       p.idpessoa,
                       n.idnf,
                       ps.descr,
                       ps.un,
                       ps.tipo,
                       ps.descr,
                       pc.idcontaitem AS idgrupoes,
                       ni.idprodserv AS idprodservnfitem,
                       ni.idnfitem,
                       n.diasentrada,
                       n.dtemissao as dtemissaoorig
                  FROM prodservforn p LEFT JOIN nf n ON (n.idobjetosolipor = ?idobjetosolipor? 
                   AND n.tipoobjetosolipor = '?tipoobjetosolipor?' 
                   AND n.status IN ('INICIO' , 'ABERTO') 
                   AND n.idpessoa = p.idpessoa)
             LEFT JOIN nfitem ni on n.idnf = ni.idnf AND ni.idprodservforn = p.idprodservforn
                  JOIN prodserv ps ON (ps.idprodserv = p.idprodserv) 
             LEFT JOIN prodservcontaitem pc ON (pc.idprodserv = ps.idprodserv)
                 WHERE p.status = 'ATIVO'
                   AND p.multiempresa = 'N'
                   ?cond_where?
                   AND p.idprodserv = ?idprodserv?
              ORDER BY p.idpessoa";
    }

    public static function buscarNfPorIdnf()
    {
        return "SELECT idnf,
                       idpessoa,
                       idobjetosolipor,
                       tipoobjetosolipor,
                       tpnf,
                       tiponf,
                       nnfe,
                       parcelas,
                       idformapagamento,
                       diasentrada,
                       intervalo,
                       geracontapagar,
                       idfinalidadeprodserv,
                       modfrete,
                       frete,
                       idunidade,
                       total,
                       tipocontapagar,
                       comissao,
                       idpessoafat,
                       entrega,
                       idnfe,
                       dmahms(dtemissao) as dtemissao,
                       status,
                       dtemissao as dtemissaoorig
                  FROM nf
                 WHERE idnf = ?idnf?";
    }

    public static function buscarFornecedoresPertencentesIdnf()
    {
        return "SELECT p.idprodservforn, 
                       p.converteest, 
                       p.unforn, 
                       p.valconv,
                       ps.un,
                       ps.tipo,
                       pc.idcontaitem AS idgrupoes
                  FROM prodservforn p LEFT JOIN nf n ON n.idpessoa = p.idpessoa
                  JOIN prodserv ps ON (ps.idprodserv = p.idprodserv) 
             LEFT JOIN prodservcontaitem pc ON (pc.idprodserv = ps.idprodserv)
                 WHERE n.idnf = ?idnf?
                   AND p.status = 'ATIVO'
                   AND p.idprodserv = ?idprodserv?
                 LIMIT 1";
    }

    public static function buscarNfPorIdNfDeslocamento()
    {
        return "SELECT idnf
                  FROM nf
                 WHERE idobjetosolipor = ?idobjetosolipor?
                   AND tipoobjetosolipor = '?tipoobjetosolipor?'
                   AND idpessoa = ?idpessoa?
                   AND idnf ?sinal? ?idnf?
                   AND status = 'INICIO'
                ORDER BY idnf
                LIMIT 1";
    }

    public static function buscarNfPessoaPorIdNf()
    {
        return "SELECT n.frete,
                       p.nome,
                       p.idpessoa,
                       n.dtemissao,
                       total,
                       idnfe,
                       tiponf,
                       n.idtransportadora,
                       n.previsaoentrega,
                       n.total,
                       n.idnf,
                       n.nnfe,
                       p.nome,dma(n.dtemissao) as emissao,
                       n.status
                  FROM nf n JOIN pessoa p ON (p.idpessoa = n.idpessoa)
                 WHERE n.idnf = ?idnf?";
    }

    public static function buscarProdutoNfPorIdNf()
    {
        return "SELECT idprodserv, idobjetosolipor FROM nf n JOIN nfitem ni ON ni.idnf = n.idnf WHERE n.idnf = ?idnf?";
    }

    public static function buscarInformacoesCotacao()
    {
        return "SELECT SUM(i.total) + SUM(i.valipi) + n.frete AS total,
                       n.idnf,
                       n.idpessoa,
                       IFNULL(n.diasentrada, 28) AS diasentrada,
                       n.idagencia,
                       IFNULL(n.parcelas, 1) AS parcelas,
                       IFNULL(n.intervalo, 1) AS intervalo,
                       n.idcontaitem,
                       n.formapgto,
                       DATE_FORMAT(n.dtemissao, '%Y-%m-%d') emissao
                  FROM nf n JOIN nfitem i ON n.idnf = i.idnf
                 WHERE n.idnf = ?idnf?
                   AND i.nfe = 'Y'
              GROUP BY n.idnf";
    }

    public static function buscarNfPorIdNfENfe()
    {
        return "SELECT SUM((IFNULL(i.vlritem, 0) - IFNULL(i.des, 0)) * i.qtd) AS subtotal,
                       (SUM(((IFNULL(i.vlritem, 0) - IFNULL(i.des, 0)) * qtd) + IFNULL(i.vseg, 0) + IFNULL(i.valipi, 0) + IFNULL(i.voutro, 0)) + IFNULL(n.frete, 0)) AS total,
                       n.idnf,
                       n.idpessoa,
                       IFNULL(n.diasentrada, 28) AS diasentrada,
                       n.idagencia,
                       IFNULL(n.parcelas, 1) AS parcelas,
                       IFNULL(n.intervalo, 1) AS intervalo,
                       n.idcontaitem,
                       n.formapgto,
                       DATE_FORMAT(n.dtemissao, '%Y-%m-%d') emissao,
                       n.idformapagamento
                  FROM nf n JOIN nfitem i ON i.idnf = n.idnf
                 WHERE n.idnf = ?idnf?
                   AND i.nfe = 'Y'
              GROUP BY n.idnf";
    }

    public static function buscarGrupoESTipoObjetoSoliPor()
    {
        return "SELECT n.idnf
                  FROM nf n JOIN nfitem ni ON ni.idnf = n.idnf 
                  JOIN prodserv p ON p.idprodserv = ni.idprodserv
                  JOIN prodservcontaitem ps ON ps.idprodserv = p.idprodserv 
                 WHERE n.idobjetosolipor = ?idobjetosolipor? AND n.tipoobjetosolipor = '?tipoobjetosolipor?' AND ps.idcontaitem = ?idcontaitem?";
    }

    public static function atualizarDataVisualizacaoFornecedor()
    {
        return "UPDATE nf SET visualizadoem = now() WHERE idnf = ?idnf?";
    }

    public static function atualizarNfParaCanceladoComStatusDiferenteConcluido()
    {
        return "UPDATE nf 
                   SET status = 'CANCELADO', idfluxostatus = ?idfluxostatus?
                 WHERE status != 'CONCLUIDO' AND idobjetosolipor = ?idobjetosolipor? AND tipoobjetosolipor = '?tipoobjetosolipor'";
    }

    public static function atualizarNfTotalSubtotal()
    {
        return "UPDATE nf 
                   SET total = '?total?', subtotal = '?subtotal?'
                 WHERE idnf =  ?idnf?";
    }

    public static function inserirNf()
    {
        return "INSERT INTO nf (idpessoa, 
                                idempresa, 
                                idobjetosolipor, 
                                tipoobjetosolipor, 
                                status, 
                                idfluxostatus, 
                                tpnf,
                                tiponf, 
                                idunidade, 
                                criadopor, 
                                criadoem, 
                                alteradopor, 
                                alteradoem)
                        VALUES (?idpessoa?, 
                                ?idempresa?, 
                                '?idobjetosolipor?', 
                                '?tipoobjetosolipor?', 
                                '?status?', 
                                '?idfluxostatus?', 
                                '?tpnf?', 
                                '?tiponf?', 
                                '?idunidade?', 
                                '?usuario?', 
                                now(), 
                                '?usuario?', 
                                now())";
    }

    public static function inserirNfDuplicada()
    {
        return "INSERT INTO nf (idpessoa, 
                                idempresa, 
                                idobjetosolipor, 
                                tipoobjetosolipor, 
                                status, 
                                idfluxostatus, 
                                tpnf,
                                tiponf, 
                                idunidade, 
                                criadopor, 
                                criadoem, 
                                alteradopor, 
                                alteradoem)
                        VALUES (?idpessoa?, 
                                ?idempresa?, 
                                '?idobjetosolipor?', 
                                '?tipoobjetosolipor?', 
                                '?status?', 
                                '?idfluxostatus?', 
                                '?tpnf?', 
                                '?tiponf?', 
                                '?idunidade?', 
                                '?usuario?', 
                                now(), 
                                '?usuario?', 
                                now())";
    }

    public static function inserirNfTransportadora()
    {
        return "INSERT INTO nf (idpessoa, 
                                idempresa, 
                                idobjetosolipor, 
                                tipoobjetosolipor, 
                                status, 
                                idfluxostatus, 
                                tiponf, 
                                idunidade, 
                                previsaoentrega, 
                                idformapagamento, 
                                subtotal, 
                                total, 
                                parcelas, 
                                dtemissao, 
                                criadopor, 
                                criadoem, 
                                alteradopor, 
                                alteradoem)
                        VALUES (?idpessoa?, 
                                ?idempresa?, 
                                '?idobjetosolipor?', 
                                '?tipoobjetosolipor?',
                                '?status?', 
                                '?idfluxostatus?', 
                                '?tiponf?', 
                                '?idunidade?', 
                                '?previsaoentrega?', 
                                ?idformapagamento?, 
                                '?subtotal?', 
                                '?total?', 
                                '?parcelas?', 
                                '?dtemissao?', 
                                '?usuario?', 
                                now(), 
                                '?usuario?', 
                                now())";
    }

    public static function inserirNfFolhaPagamento()
    {
        return "INSERT INTO nf (idpessoa, 
                                idempresa, 
                                idobjetosolipor, 
                                tipoobjetosolipor, 
                                status, 
                                idfluxostatus, 
                                tpnf,
                                tiponf, 
                                idunidade, 
                                controle,
                                dtemissao,
                                tipoorc,
                                parcelas,
                                diasentrada,
                                idformapagamento,
                                nnfe,
                                criadopor, 
                                criadoem, 
                                alteradopor, 
                                alteradoem)
                        VALUES (?idpessoa?, 
                                ?idempresa?, 
                                '?idobjetosolipor?', 
                                '?tipoobjetosolipor?', 
                                '?status?', 
                                '?idfluxostatus?', 
                                '?tpnf?', 
                                '?tiponf?', 
                                '?idunidade?', 
                                '?controle?',
                                '?dtemissao?',
                                '?tipoorc?',
                                '?parcelas?',
                                '?diasentrada?',
                                '?idformapagamento?',
                                '?nnfe?',
                                '?usuario?', 
                                now(), 
                                '?usuario?', 
                                now())";
    }

    public static function buscarNfPorId()
    {
        return "SELECT 
                    *
                FROM
                    nf
                WHERE
                    idnf = ?idnf?";
    }

    public static function buscarNfPorRefnfeETipoNf()
    {
        return "SELECT * 
                  FROM nf
                 WHERE refnfe LIKE '%?refnfe?%'
                   AND tiponf = '?tiponf?'";
    }

    public static function atualizaNftransferencia()
    {
        return "UPDATE nf SET statustransf = '?status?' where idnf = ?idnf?";
    }

    public static function atualizarNfValorFrete()
    {
        return "UPDATE nf
				SET 
					frete = '?frete?'
				WHERE
					idnf = ?idnf?";
    }

    public static function buscarNfEFluxoStatusPorTipoObjetoSoliPor()
    {
        return "SELECT idnf, 
                       nnfe, 
                       dtemissao, 
                       n.idfluxostatus, 
                       upper(s.rotulo) AS 'status', 
                       n.idempresa,
                       n.tiponf
                  FROM nf n LEFT JOIN fluxostatus fs ON n.idfluxostatus = fs.idfluxostatus
             LEFT JOIN " . _DBCARBON . "._status s ON fs.idstatus = s.idstatus
                 WHERE idobjetosolipor = ?idobjetosolipor? AND tipoobjetosolipor = '?tipoobjetosolipor?'
              ORDER BY idnf;";
    }

    public static function buscarNfPessoaPorIdNfe()
    {
        return "SELECT n.total,
                       n.idnf,
                       n.nnfe,
                       p.nome,
                       DMA(n.dtemissao) AS emissao,
                       n.status,
                       n.tiponf
                  FROM nf n JOIN pessoa p ON p.idpessoa = n.idpessoa
                 WHERE n.idnfe LIKE ('%idnfe%')";
    }

    public static function buscarPrevisaoEntregaPorIdNf()
    {
        return "SELECT  IF(previsaoentrega = '0000-00-00', NULL, DATE_FORMAT(previsaoentrega, '%d/%m/%Y')) AS previsaoentrega FROM nf WHERE idnf = ?idnf?";
    }

    public static function buscarFornecedorPorNnfe()
    {
        return "SELECT count(*) as quant FROM nf WHERE idpessoa = ?idpessoa? AND status != 'CANCELADO' AND nnfe = '?nnfe?' ?condicao?";
    }

    public static function buscarNFProdservForn()
    {
        return "SELECT p.idprodservforn, p.unforn
                  FROM nf n JOIN prodservforn p ON (p.idpessoa = n.idpessoa AND p.idprodserv = ?idprodserv?)
                 WHERE n.idnf = ?idnf?
                   AND p.status = 'ATIVO'
                 LIMIT 1";
    }

    public static function buscarItensPorIdNf()
    {
        return "SELECT (SUM(i.total) - IFNULL(n.frete, 0) - IFNULL(n.pis, 0) - IFNULL(n.cofins, 0) - IFNULL(n.csll, 0) - IFNULL(n.ir, 0) - IFNULL(n.inss, 0) - IFNULL(n.issret, 0)) AS total,
                       n.idnf,
                       n.idpessoa,
                       n.idunidade,
                       IFNULL(n.diasentrada, 28) AS diasentrada,
                       n.idagencia,
                       IFNULL(n.parcelas, 1) AS parcelas,
                       IFNULL(n.intervalo, 1) AS intervalo,
                       n.idcontaitem,
                       n.idformapagamento,
                       '?dtemissao?' AS emissao,
                       n.tiponf,
                       n.tipocontapagar,
                       i.idprodserv
                  FROM nf n LEFT JOIN nfitem i ON (i.idnf = n.idnf AND i.nfe = 'Y')
                 WHERE n.idnf = ?idnf?
              GROUP BY i.idnf";
    }

    public static function buscarEnderecoPessoaNf()
    {
        return "SELECT e.idendereco,
                       CONCAT(e.endereco, '-', e.uf) AS endereco,
                       CASE
                            WHEN e.uf = 'MG' THEN 'DENTRO'
                            ELSE 'FORA'
                       END AS destino
                  FROM endereco e JOIN nf n ON e.idpessoa = n.idpessoa
                 WHERE e.idtipoendereco = 2
                   AND e.status = 'ATIVO'
                   AND n.idnf = ?idnf?";
    }

    public static function atualizarTransportadoraNf()
    {
        return "UPDATE nf n SET n.idtransportadora = ?idtransportadora? WHERE n.idnf = ?idnf?";
    }

    public static function buscarDashboardSuprimentos()
    {
        return "SELECT
                    'dashcompras' as panel_id,
                    'col-md-4' as panel_class_col,
                    'COMPRAS' as panel_title,
                    'dashcomprasaprovadas' as card_id,
                    'col-md-6 col-sm-6 col-xs-6' as card_class_col, 
                    concat('_modulo=nfentrada&_pagina=0&_ordcol=idnf&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO%22}') as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    if (count(1) > 0,'danger','success') as card_color,
                    if (count(1) > 0,'danger','success') as card_border_color,
                    '' as card_bg_class,
                    'aprovadas' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'COMPRAS - APROVADAS' as card_title_modal,
                    '_modulo=nfentrada&_acao=u' as card_url_modal
                FROM `nf` `n`
                WHERE n.status = 'APROVADO'
                ?getidempresa?
                AND n.tiponf IN ('C' , 'T', 'E', 'S', 'M', 'F', 'B')
                AND  DATE_ADD(DATE_FORMAT(dtemissao,'%Y-%m-%d'), interval 1 year) < CURRENT_DATE
                UNION ALL
                SELECT
                    'dashcompras' as panel_id,
                    'col-md-4' as panel_class_col,
                    'COMPRAS' as panel_title,
                    'dashcomprasaprovadas' as card_id,
                    'col-md-6 col-sm-6 col-xs-6' as card_class_col, 
                    concat('_modulo=nfentrada&_pagina=0&_ordcol=idnf&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO%22,%22statusenvio%22:%22EM ATRASO%22}') as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    if (count(1) > 0,'danger','success') as card_color,
                    if (count(1) > 0,'danger','success') as card_border_color,
                    '' as card_bg_class,
                    'em atraso' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'COMPRAS - APROVADAS EM ATRASO' as card_title_modal,
                    '_modulo=nfentrada&_acao=u' as card_url_modal
                FROM `nf` `n`
                WHERE n.status = 'APROVADO'
                ?getidempresa?
                AND n.tiponf IN ('C' , 'T', 'E', 'S', 'M', 'F', 'B') 
                AND  DATE_ADD(DATE_FORMAT(dtemissao,'%Y-%m-%d'), interval 7 day) < CURRENT_DATE";
    }

    public static function buscarNfPorTipoNfEIdNf()
    {
        return "SELECT xmlret FROM nf WHERE tiponf = '?tiponf?' AND idnf = ?idnf?";
    }

    public static function buscarEnvioLoteReservaPorIdLote()
    {
        return "SELECT n.envio
                  FROM lotereserva c JOIN nfitem i ON (i.idnfitem = c.idobjeto)
                  JOIN nf n ON (i.idnf = n.idnf)
                 WHERE c.idlote = ?idlote?
                   AND c.tipoobjeto = '?tipoobjeto?'
                   and c.qtd > 0
              ORDER BY n.envio ASC
                 LIMIT 1";
    }

    public static function atualizarNfXmlRetEnvioNfe()
    {
        return "UPDATE nf SET xmlret = null, envionfe = ?envionfe?,idnfe=null WHERE idnf = ?idnf?";
    }

    public static function atualizarNfXmlVinculo()
    {
        return "UPDATE nfentradaxml SET idnf = null WHERE idnf = ?idnf?";
    }

    public static function atualizarNfIdnfeDtemissaoPorIdnf()
    {
        return "UPDATE nf SET idnfe = '?idnfe?', dtemissao = '?dtemissao?' WHERE idnf = ?idnf?";
    }

    public static function buscarNfePorIdNfItem()
    {
        return "SELECT IFNULL(n.nnfe, n.idnf) AS nnfe, n.idnf, u.idunidade
                  FROM nfitem i JOIN nf n ON (n.idnf = i.idnf AND n.status != 'CANCELADO')
                  JOIN pessoa p ON (n.idpessoa = p.idpessoa)
             LEFT JOIN unidade u ON (u.idtipounidade = 21 AND n.idempresa = u.idempresa AND u.status = 'ATIVO')
                 WHERE i.idnfitem = ?idnfitem?";
    }

    public static function atualizarIdTipoProdservPorIdProdserv()
    {
        return "UPDATE nf n JOIN nfitem i ON (i.idnf = n.idnf AND i.nfe = 'Y') JOIN prodserv p ON (p.idprodserv = i.idprodserv) 
                   SET i.idtipoprodserv = p.idtipoprodserv
                 WHERE n.dtemissao > '2021-12-31 23:59:00' AND p.idprodserv = ?idprodserv?";
    }

    public static function atualizarIdContaItemPorIdProdserv()
    {
        return "UPDATE nf n JOIN nfitem i ON (i.idnf = n.idnf AND i.nfe = 'Y')
                  JOIN contaitemtipoprodserv p ON (p.idtipoprodserv = i.idtipoprodserv) 
                   SET i.idcontaitem = p.idcontaitem
                 WHERE n.dtemissao > '2021-12-31 23:59:00'
                   AND i.idcontaitem != p.idcontaitem
                   AND i.idprodserv = ?idprodserv?";
    }

    public static function buscarTipoNatPorIdnf()
    {
        return "SELECT 
					nt.idnatop, nt.natop, nt.finnfe, nt.tpnf, nt.natoptipo
				FROM
					nf n
						JOIN
					natop nt ON (nt.idnatop = n.idnatop)
				WHERE
					n.idnf =?idnf? ";
    }
    public static function verificaFeriadoFds()
    {
        return "SELECT verificaFeriadoFds('?timestamp?' ) as eFeriado";
    }
    public static function buscarNfPorIdpessoaIdempresaStatus()
    {
        return "SELECT * FROM nf n
                        WHERE n.idpessoa = ?idpessoa? 
                        AND n.status = '?status?'
                        AND n.idempresa =?idempresa?
                        AND n.tipocontapagar ='C'
                        AND n.tipoorc='COBRANCA'";
    }

    public static function buscarFormaPagamentoPorIdNf()
    {
        return "SELECT formapagamento FROM nf n JOIN formapagamento fp ON fp.idformapagamento = n.idformapagamento
                 WHERE n.idnf = ?idnf?";
    }

    public static function buscarFreteInternacional()
    {
        return "SELECT ninpnac.idnf as idnfimpnac,
                       ninpint.idnf as idnfimpint,
                       naero.idnf as idnfaerop,
                       nhoi.idnf as idnfhonimp,
                       nicms.idnf as idicms,
                       nsis.idnf as idsiscomex
                  FROM nf n LEFT JOIN nf ninpnac ON ninpnac.idobjetosolipor = n.idnf AND ninpnac.tipoobjetosolipor = 'nf' AND ninpnac.objeto = 'impnac'
                  LEFT JOIN nf ninpint ON ninpint.idobjetosolipor = n.idnf AND ninpint.tipoobjetosolipor = 'nf' AND ninpint.objeto = 'impint'
                  LEFT JOIN nf naero ON naero.idobjetosolipor = n.idnf AND naero.tipoobjetosolipor = 'nf' AND naero.objeto = 'aerop'
                  LEFT JOIN nf nhoi ON nhoi.idobjetosolipor = n.idnf AND nhoi.tipoobjetosolipor = 'nf' AND nhoi.objeto = 'honimp'
                  LEFT JOIN nf nicms ON nicms.idobjetosolipor = n.idnf AND nicms.tipoobjetosolipor = 'nf' AND nicms.objeto = 'icms'
                  LEFT JOIN nf nsis ON nsis.idobjetosolipor = n.idnf AND nsis.tipoobjetosolipor = 'nf' AND nsis.objeto = 'siscomex'
                 WHERE n.idnf = ?idnf?";
    }

    public static function buscarContaPagarItemPorIdNf()
    {
        return "SELECT cf.idconciliacaofinanceira, cf.status as statusconciliacao, ci.idcontapagar, ci.idcontapagaritem, nf.dtemissao, ci.valor, ci.status, p.nome, ci.idempresa
                FROM nf 
                JOIN contapagaritem ci ON ci.idobjetoorigem = nf.idnf and ci.tipoobjetoorigem = 'nf'
                JOIN conciliacaofinanceira cf on cf.idcontapagar = ci.idcontapagar
                JOIN pessoa p ON p.idpessoa = ci.idpessoa
                WHERE ci.idobjetoorigem = ?idnf?;";
    }


    public static function buscarValorImpostoTotalItem()
    {
        return "SELECT n.idnf, 
                       ni.vlritem, 
                       ((IFNULL(ni.impostoimportacao, 0) + IFNULL(ni.valipi, 0) + IFNULL(ni.pis, 0) + IFNULL(ni.cofins, 0)) / ni.qtd) as valorcomimpostoitem,
                       (SELECT IF(count(1) > 0, 'Y', 'N') as 'internacional' FROM nfitem ni2 WHERE ni2.idnf = ni.idnf AND ni2.moedaext is not null AND ni2.moedaext != 'BRL' AND moedainternacional = 'Y') as 'internacional'
                  FROM nfitem ni JOIN nf n ON n.idnf = ni.idnf 
                WHERE ni.nfe = 'Y' ?condicao?
                AND n.status NOT IN ('CANCELADO')
            ORDER BY ni.idnfitem DESC LIMIT 1";
    }

    public static function buscarValorImpostoTotalPorTotalItem()
    {
        return "SELECT ((n.freteimpnacional + n.freteimpinternacional + n.aeroportuaria + n.honorarioimportacao + n.icms + n.siscomex) / SUM(qtd)) as valorcomimposto
                       FROM nf n JOIN nfitem ni ON ni.idnf = n.idnf 
                      WHERE n.idnf = '?idnf?' AND ni.nfe = 'Y';";
    }

    public static function buscarValorItem()
    {
        return "SELECT (IFNULL(ni.total, 0) + IFNULL(ni.valipi, 0) + IFNULL(ni.frete, 0)) / (ni.qtd * IF(pf.valconv < 1 OR pf.valconv IS NULL, 1, pf.valconv)) AS valoritem
                       FROM nf n JOIN nfitem ni ON ni.idnf = n.idnf 
                  LEFT JOIN prodservforn pf ON pf.idprodserv = ni.idprodserv AND pf.idprodservforn = ni.idprodservforn AND pf.status = 'ATIVO' AND pf.converteest = 'Y'   
                      WHERE n.idnf = '?idnf?' AND ni.nfe = 'Y'
                        AND ni.idprodserv = ?idprodserv?;";
    }

    public static function buscarCTEDisponiveisParaVinculo()
    {
        return "SELECT nf.idnf, p.nome
                FROM nf
                JOIN pessoa p on p.idpessoa = nf.idpessoa 
                WHERE tiponf = 'T'
                AND nf.status not in('CONCLUIDO', 'CANCELADO')
                AND nf.idempresa = ?idempresa?";
    }

    public static function buscarComprasDisponiveisParaVinculo()
    {
        return "SELECT vw.idnf
                from nf vw
                where status != 'CANCELADO'
                and idempresa = ?idempresa?
                AND idnf = ?idnfbusca?
                and not exists (
                    select 1
                    from objetovinculo o 
                    where idobjetovinc = ?idnf?
                    and tipoobjetovinc = 'cte'
                    and idobjeto = vw.idnf
                    and tipoobjeto = 'nf'
                );";
    }


    public static function buscarNfDanfe()
    {
        return "SELECT 
                    n.total AS frete, o.idobjeto AS idnf
                FROM
                    objetovinculo o
                        JOIN
                    nf n ON n.idnf = o.idobjetovinc
                WHERE
                    o.tipoobjeto = 'nf'
                        AND o.tipoobjetovinc = 'cte'
                        AND o.idobjetovinc = ?idnf? limit 1;";
    }

    public static function buscarLotePorIdNf()
    {
        return "SELECT ni.idnfitem, 
                       ni.total,
                       ni.valipi,
                       ni.impostoimportacao,
                       ni.valipi,
                       ni.pis,
                       ni.cofins,
                       ni.qtd,
                       n.total as totalnf,
                       l.idlote,
                       pf.converteest,
                       pf.valconv
                  FROM lote l JOIN nfitem ni ON ni.idnfitem = l.idnfitem
                  JOIN nf n ON n.idnf = ni.idnf
             LEFT JOIN prodservforn pf ON pf.idprodservforn = ni.idprodservforn
                 WHERE n.idnf = ?idnf?;";
    }

    public static function buscarLoconsPorNfItem()
    {
        return "SELECT 1 FROM nf WHERE idnf = ?idobjetosolipor? AND tiponf = 'V'";
    }
}
