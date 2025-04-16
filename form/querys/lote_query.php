<?
require_once(__DIR__ . "/_iquery.php");

class LoteQuery implements DefaultQuery
{

    public static $table = "lote";
    public static $pk = "idlote";

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ['table' => self::$table, 'pk' => self::$pk]);
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo1()
    {
        return "SELECT CONCAT('PART: ',l.partida,'/',l.exercicio) AS partida,
                        pd.descr,
                        p.idpessoa,
                        LEFT(p.nome,52) AS nomeinicio,
                        SUBSTRING(p.nome,53) AS nomefim,
                        CASE pd.venda 
                            WHEN 'x' 
                            THEN UPPER(CONCAT('FAB: ',LEFT(DATE_FORMAT(l.fabricacao, '%M'),3),'/',RIGHT(DATE_FORMAT(l.fabricacao, '%Y'),2)))
                            ELSE CONCAT('FAB: ',dma(l.fabricacao))
                        END AS fabricacao,
                        CASE pd.venda 
                            WHEN 'x' THEN UPPER(CONCAT('VENC: ',LEFT(DATE_FORMAT(l.vencimento, '%M'),3),'/',RIGHT(DATE_FORMAT(l.vencimento, '%Y'),2))) 
                            ELSE CONCAT('VENC: ',dma(l.vencimento))
                        END AS vencimento,      
                        CONCAT(sf.idsolfab,'-',sl.partida,'/',sl.exercicio) AS solfab,
                        l.qtdprod,
                        l.qtdprod_exp,
                        pd.venda,
                        e.nomefantasia
                FROM lote l 
                    LEFT JOIN pessoa p ON (p.idpessoa=l.idpessoa) 
                    LEFT JOIN prodserv pd ON (l.idprodserv= pd.idprodserv)
                    LEFT JOIN solfab sf ON (sf.idsolfab = l.idsolfab)
                    LEFT JOIN lote sl ON (sf.idlote =sl.idlote)
                    LEFT JOIN empresa e ON (l.idempresa =e.idempresa)
                WHERE l.idlote= ?idlote?";
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo16zpl()
    {
        return "SELECT CONCAT(l.partida,'/',l.exercicio) AS partida,
                        p.idpessoa,
                        LEFT(p.nome,24) AS nomeinicio,
                        SUBSTRING(p.nome,25) AS nomefim,
                        e.sigla
                FROM lote l 
                    LEFT JOIN pessoa p ON (p.idpessoa=l.idpessoa) 
                    LEFT JOIN empresa e ON (l.idempresa= e.idempresa)
                WHERE l.idlote= ?idlote?";
    }

    public static function buscarSementesParaEtiquetaFormalizacaoTipo1()
    {
        return "SELECT
                    CONCAT(l.partida,'/',l.exercicio) AS semente
                FROM lotecons c 
                    JOIN lote l ON( c.idlote=l.idlote AND l.tipoobjetosolipor='resultado' ) 
                    JOIN prodserv p ON (p.idprodserv=l.idprodserv AND p.especial ='Y')
                WHERE  qtdd > 0
                AND c.tipoobjeto = 'lote'
                AND c.idobjeto = ?idlote?";
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo2()
    {
        return "SELECT l.idempresa,
                        CONCAT('LOTE: ',l.partida,'/',l.exercicio) AS partida,
                        pd.descr,
                        p.idpessoa,
                        LEFT(p.nome,52) AS nomeinicio,
                        SUBSTRING(p.nome,53) AS nomefim,
                        CASE pd.venda 
                            WHEN 'x'
                            THEN  UPPER(CONCAT('FABRICACAO: ',LEFT(DATE_FORMAT(l.fabricacao, '%M'),3),'/',RIGHT(DATE_FORMAT(l.fabricacao, '%Y'),2)))
                            ELSE CONCAT('FABRICACAO: ',dma(l.fabricacao))
                        END AS fabricacao,
                        CASE pd.venda 
                            WHEN 'x'
                            THEN UPPER(CONCAT('VENCIMENTO: ',LEFT(DATE_FORMAT(l.vencimento, '%M'),3),'/',RIGHT(DATE_FORMAT(l.vencimento, '%Y'),2))) 
                            ELSE CONCAT('VENCIMENTO: ',dma(l.vencimento))
                        END AS vencimento,      
                        CONCAT(sf.idsolfab,'-',sl.partida,'/',sl.exercicio) AS solfab,
                        l.qtdprod,
                        l.qtdprod_exp,
                        pd.venda
                    FROM lote l 
                    LEFT JOIN pessoa p ON(p.idpessoa=l.idpessoa) 
                    LEFT JOIN prodserv pd ON(l.idprodserv= pd.idprodserv)
                    LEFT JOIN solfab sf ON(sf.idsolfab = l.idsolfab)
                    LEFT JOIN lote sl ON(sf.idlote =sl.idlote)
                    WHERE l.idlote = ?idlote?";
    }

    public static function buscarVolumeFormulaDoLoteParaEtiquetaFormalizacao()
    {
        return "SELECT CONCAT(p.volumeformula,' ',p.un) AS formula 
                FROM lote l
                    JOIN prodservformula p ON (l.idprodservformula = p.idprodservformula)
                WHERE l.idlote = ?idlote?";
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo3()
    {
        return "SELECT 
                    p.idprodserv,
                    if(p.descrcurta <> '',p.descrcurta,p.descr) AS descr,
                    LEFT(if(p.descrcurta <> '',p.descrcurta,p.descr),22) AS descrinicio,
                    SUBSTRING(if(p.descrcurta <> '',p.descrcurta,p.descr),23) AS descrfim,
                    CONCAT(l.partida, '/', l.exercicio) AS partida,
                    DMA(l.fabricacao) AS fabricacao,
                    DMA(l.vencimento) AS vencimento
                FROM
                    lote l
                    JOIN prodserv p ON (l.idprodserv = p.idprodserv)
                WHERE
                    l.idlote =  ?idlote?";
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo4()
    {
        return "SELECT  s.partida AS semente,
                        s.exercicio AS  exerciciosem,
                        ps.descr AS descrsemente,
                        l.partida,
                        l.exercicio,
                        LEFT(p.nome,22) AS nome,
                        SUBSTRING(p.nome,23) AS nomefim
                FROM lotecons c
                    JOIN lote s ON (s.idlote = c.idlote)
                    JOIN prodserv ps ON (ps.idprodserv=s.idprodserv)
                    JOIN lote l ON (c.idobjeto = l.idlote)
                    JOIN resultado r ON (s.idobjetosolipor = r.idresultado AND s.tipoobjetosolipor = 'resultado')
                    JOIN amostra a ON (r.idamostra = a.idamostra)
                    JOIN pessoa p ON (p.idpessoa = a.idpessoa)
                WHERE c.idlotecons =  ?idlote?
                    AND c.tipoobjeto = 'lote'";
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo5()
    {
        return "SELECT CONCAT('PARTIDA: ',l.partida,'/',l.exercicio) AS partida,
                        p.idpessoa,
                        LEFT(pd.descr,50) AS descrinicio,
                        SUBSTRING(pd.descr,51,50) AS descrfim,
                        LEFT(p.nome,50) AS nomeinicio,
                        SUBSTRING(p.nome,51,50) AS nomefim,
                        CASE pd.venda 
                            WHEN 'x' THEN  UPPER(CONCAT('FABRICACAO: ',LEFT(DATE_FORMAT(l.fabricacao, '%M'),3),'/',RIGHT(DATE_FORMAT(l.fabricacao, '%Y'),2)))
                            ELSE CONCAT('FABRICACAO: ',dma(l.fabricacao))
                        END AS fabricacao,
                        CASE pd.venda 
                            WHEN 'x' THEN UPPER(CONCAT('VENCIMENTO: ',LEFT(DATE_FORMAT(l.vencimento, '%M'),3),'/',RIGHT(DATE_FORMAT(l.vencimento, '%Y'),2))) 
                            ELSE CONCAT('VENCIMENTO: ',dma(l.vencimento))
                        END AS vencimento,      
                        CONCAT(sf.idsolfab,'-',sl.partida,'/',sl.exercicio) AS solfab,
                        l.qtdprod,
                        l.qtdprod_exp,
                        pd.venda,
                        pd.un,
                        e.nomefantasia
                FROM lote l 
                    LEFT JOIN pessoa p ON(p.idpessoa=l.idpessoa) 
                    LEFT JOIN prodserv pd ON(l.idprodserv= pd.idprodserv)
                    LEFT JOIN solfab sf ON(sf.idsolfab = l.idsolfab)
                    LEFT JOIN lote sl ON(sf.idlote =sl.idlote)
                    LEFT JOIN empresa e ON(e.idempresa=l.idempresa)
                WHERE l.idlote = ?idlote?";
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo5b()
    {
        return "SELECT CONCAT('PARTIDA: ',l.partida,'/',l.exercicio) AS partida,
                        p.idpessoa,
                        LEFT(pd.descr,50) AS descrinicio,
                        SUBSTRING(pd.descr,51,50) AS descrfim,
                        LEFT(p.nome,50) AS nomeinicio,
                        SUBSTRING(p.nome,51,50) AS nomefim,
                        CASE pd.venda 
                            WHEN 'x' THEN  UPPER(CONCAT('FABRICACAO: ',LEFT(DATE_FORMAT(l.fabricacao, '%M'),3),'/',RIGHT(DATE_FORMAT(l.fabricacao, '%Y'),2)))
                            ELSE CONCAT('FABRICACAO: ',dma(l.fabricacao))
                        END AS fabricacao,
                        CASE pd.venda 
                            WHEN 'x' THEN UPPER(CONCAT('VENCIMENTO: ',LEFT(DATE_FORMAT(l.vencimento, '%M'),3),'/',RIGHT(DATE_FORMAT(l.vencimento, '%Y'),2))) 
                            ELSE CONCAT('VENCIMENTO: ',dma(l.vencimento))
                        END AS vencimento,      
                        CONCAT(sf.idsolfab,'-',sl.partida,'/',sl.exercicio) AS solfab,
                        l.qtdprod,
                        l.qtdprod_exp,
                        pd.venda,
                        pd.un,
                        e.nomefantasia
                FROM lote l 
                    LEFT JOIN pessoa p ON(p.idpessoa=l.idpessoa) 
                    LEFT JOIN prodserv pd ON(l.idprodserv= pd.idprodserv)
                    LEFT JOIN solfab sf ON(sf.idsolfab = l.idsolfab)
                    LEFT JOIN lote sl ON(sf.idlote =sl.idlote)
                    LEFT JOIN empresa e ON(e.idempresa =l.idempresa)
                WHERE l.idlote = ?idlote?";
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo8()
    {
        return "SELECT 
                    p.nome,
                    CONCAT(l.partida, '/', l.exercicio) AS partida
                FROM
                    lote l
                JOIN
                    pessoa p ON (l.idpessoa = p.idpessoa)
                WHERE
                    l.idlote =  ?idlote?";
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo12()
    {
        return "SELECT DISTINCT(CONCAT('REG: ', r.idamostra)) AS registro,
                        CONCAT('PART: ', l.partida, '/', RIGHT(l.exercicio, 2)) AS partida,
                        p.idpessoa,
                        LEFT(p.nome, 52) AS nomeinicio,
                        SUBSTRING(p.nome, 53) AS nomefim
                FROM lote l
                    lEFT JOIN pessoa p ON (p.idpessoa = l.idpessoa)
                    JOIN loteativ la ON la.idlote = l.idlote
                    JOIN objetovinculo ov ON ov.idobjetovinc = la.idloteativ AND ov.tipoobjetovinc = 'loteativ'
                    JOIN resultado r ON r.idresultado = ov.idobjeto AND ov.tipoobjeto = 'resultado'
                WHERE l.idlote =  ?idlote?";
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo13e14()
    {
        return "SELECT DISTINCT(CONCAT('REG: ', a.idregistro)) AS registro,
                        CONCAT('PART: ', l.partida, '/', RIGHT(l.exercicio, 2)) AS partida,
                        p.idpessoa,
                        LEFT(p.nome, 22) AS nomeinicio,
                        SUBSTRING(p.nome, 23) AS nomefim
                FROM lote l
                    lEFT JOIN pessoa p ON (p.idpessoa = l.idpessoa)
                    JOIN loteativ la ON la.idlote = l.idlote
                    JOIN objetovinculo ov ON ov.idobjetovinc = la.idloteativ AND ov.tipoobjetovinc = 'loteativ'
                    JOIN resultado r ON r.idresultado = ov.idobjeto AND ov.tipoobjeto = 'resultado'
                    JOIN amostra a ON (r.idamostra = a.idamostra)
                WHERE l.idlote =   ?idlote?";
    }

    public static function buscarInfosEtiquetaFormalizacaoTipo15()
    {
        return "SELECT CONCAT(l.spartida,l.npartida,'/',l.exercicio) AS descr,
                        p.descr AS produto,
                        LEFT(p.descr,30) AS nomeinicio,
                        LEFT(SUBSTRING(p.descr,31),30) AS nomemeio,
                        SUBSTRING(p.descr,61) AS nomefim,
                        dma(l.vencimento) AS vencimento
                FROM lote l 
                    JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                WHERE
                    l.idlote= ?idlote?";
    }

    public static function buscarInfosEtiquetaImpetiquetaLoteAlmox()
    {
        return "SELECT CONCAT(l.spartida,l.npartida,'/',l.exercicio) AS descr,
                        p.descr AS produto,
                        LEFT(p.descr,40) AS nomeinicio,
                        LEFT(SUBSTRING(p.descr,41),40) AS nomemeio,
                        SUBSTRING(p.descr,81) AS nomefim,
                        l.criadoem AS criadoem,
                        dma(l.vencimento) AS vencimento,
                        l.idempresa,
                        e.sigla,
                        CONCAT(t.descricao,' ',CONCAT(CASE tp.coluna 
                            WHEN 0 THEN '0'
                            WHEN 1 THEN 'A'	
                            WHEN 2 THEN 'B' 
                            WHEN 3 THEN 'C' 
                            WHEN 4 THEN 'D'	
                            WHEN 5 THEN 'E'	
                            WHEN 6 THEN 'F'
                            WHEN 7 THEN 'G' 
                            WHEN 8 THEN 'H' 
                            WHEN 9 THEN 'I' 
                            WHEN 10 THEN 'J' 
                            WHEN 11 THEN 'K' 
                            WHEN 12 THEN 'L'
                            WHEN 13 THEN 'M' 
                            WHEN 14 THEN 'N' 
                            WHEN 15 THEN 'O' 
                            WHEN 16 THEN 'P' 
                            WHEN 17 THEN 'Q' 
                            WHEN 18 THEN 'R'
                            WHEN 19 THEN 'S' 
                            WHEN 20 THEN 'T' 
                            WHEN 21 THEN 'U' 
                            WHEN 22 THEN 'V'
                            WHEN 23 THEN 'X' 
                            WHEN 24 THEN 'Z'
                        END ,' ',tp.linha)) AS campo,
                        l.observacao AS obslote
                FROM lote l 
                    JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                    JOIN empresa e ON (l.idempresa = e.idempresa)
                    JOIN lotelocalizacao c ON (c.idlote=l.idlote AND c.tipoobjeto ='tagdim')
                    JOIN tagdim tp ON (tp.idtagdim= c.idobjeto)
                    JOIN tag t ON (tp.idtag = t.idtag)
                WHERE
                    l.idlote = ?idlote? ?str?
                    AND EXISTS (SELECT 1 FROM unidade a WHERE a.idtipounidade = ?idtipounidade? AND a.status = 'ATIVO' AND t.idunidade = a.idunidade)
            UNION
            SELECT CONCAT(l.spartida,l.npartida,'/',l.exercicio) AS descr,
                    p.descr AS produto,
                    LEFT(p.descr,40) AS nomeinicio,
                    LEFT(SUBSTRING(p.descr,41),40) AS nomemeio,
                    SUBSTRING(p.descr,81) AS nomefim,
                    l.criadoem AS criadoem,
                    dma(l.vencimento) AS vencimento,
                    l.idempresa,
                    e.sigla,
                    CONCAT(pe.nomecurto) AS campo,
                    l.observacao AS obslote
            FROM lote l 
                JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                JOIN empresa e ON (l.idempresa = e.idempresa)
                JOIN lotelocalizacao c ON (c.idlote=l.idlote AND c.tipoobjeto ='pessoa')
                JOIN pessoa pe ON (pe.idpessoa = c.idobjeto)
            WHERE
                l.idlote= ?idlote? ?str?
            UNION
            SELECT CONCAT(l.spartida,l.npartida,'/',l.exercicio) AS descr,
                    p.descr AS produto,
                    LEFT(p.descr,40) AS nomeinicio,
                    LEFT(SUBSTRING(p.descr,41),40) AS nomemeio,
                    SUBSTRING(p.descr,81) AS nomefim,
                    l.criadoem AS criadoem,
                    dma(l.vencimento) AS vencimento,
                    l.idempresa,
                    e.sigla,
                    CONCAT(CONCAT(t.descricao,'- TAG ',t.tag))AS campo,
                    l.observacao AS obslote
              FROM lote l 
                JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                JOIN empresa e ON (l.idempresa = e.idempresa)
                JOIN lotelocalizacao c ON (c.idlote=l.idlote AND c.tipoobjeto ='tagsala')
                JOIN tag t ON (t.idtag = c.idobjeto)
              WHERE
                l.idlote= ?idlote? ?str?";
    }

    public static function buscarLotePorIdLote()
    {
        return "SELECT CONCAT(spartida,npartida,'/',exercicio) AS descr,
                       IFNULL(unpadrao, '') AS campo
                  FROM lote
                 WHERE idlote = ?idlote?";
    }

    public static function buscarInfosEtiquetaImpetiquetaLote()
    {
        return "SELECT CONCAT(spartida,npartida,'/',exercicio) AS descr
                FROM lote
                WHERE idlote = ?idlote?";
    }

    public static function buscarLotesDeFormalizacaoEmandamento()
    {
        return "SELECT 
            l.idlote, l.status, l.partida, f.idformalizacao, s.rotulo
        FROM
            lote l
                LEFT JOIN
            formalizacao f ON f.idlote = l.idlote
                LEFT JOIN 
            fluxostatus fx ON fx.idfluxostatus = f.idfluxostatus
                LEFT JOIN 
            carbonnovo._status s on s.idstatus = fx.idstatus
        WHERE
            l.idprodserv = ?idprodserv?
                AND l.idunidade IN (?idUnidadePadrao?)
                AND l.status NOT IN ('APROVADO', 'ESGOTADO', 'CANCELADO', 'REPROVADO', 'RETIDO', 'Aberto')
                AND l.idprodservformula = ?idprodservformula?";
    }

    public static function buscarLotesDeFormalizacaoPorIdSolfab()
    {
        return "SELECT IFNULL(s.rotulo, n.status) AS rotulo, n.idnf, n.status, l.*
                  FROM lote l JOIN prodserv p ON (p.idprodserv = l.idprodserv AND p.venda = 'Y')
                  JOIN nfitem ni ON (ni.idnfitem = l.idobjetoprodpara)
                  JOIN nf n ON (n.idnf = ni.idnf)
             LEFT JOIN fluxostatus fs ON (fs.idfluxostatus = n.idfluxostatus)
             LEFT JOIN " . _DBCARBON . "._status s ON fs.idstatus = s.idstatus
                 WHERE l.idsolfab = ?idsolfab?
              ORDER BY l.criadoem DESC;";
    }

    public static function buscarLoteEmQuarentenaProdutoEmALerta()
    {
        return "SELECT 
            l.partida, l.exercicio, l.idlote
        FROM
            lote l
        WHERE
            l.idprodserv = ?idprodserv?
                AND l.idunidade IN (?idUnidadePadrao?)
                AND l.status = 'QUARENTENA' ";
    }

    public static function buscarSementesGeradasResultado()
    {
        return "SELECT 
            p.descr,
            o.idobjeto,
            l.idlote,
            l.exercicio,
            l.partida,
            p.descr,
            l.criadopor,
            l.criadoem,
            l.tipificacao
        FROM
            lote l
                JOIN
            prodserv p
                LEFT JOIN
            unidadeobjeto o ON (o.tipoobjeto = 'modulo'
                AND o.idobjeto LIKE ('lote%')
                AND o.idunidade = l.idunidade)
        WHERE
            p.idprodserv = l.idprodserv
                AND l.tipoobjetosolipor = 'resultado'
                AND l.idobjetosolipor = ?idresultado?
        GROUP BY l.idlote";
    }


    public static function buscarLotesConsumoInsumoResultado()
    {
        return "SELECT 
            l.partida,
            o.idobjeto,
            f.idlotefracao,
            l.exercicio,
            l.idlote,
            f.qtd AS qtddisp,
            f.qtd_exp AS qtddisp_exp,
            c.idlotecons,
            c.qtdd,
            c.qtdd_exp,
            c.idobjetoconsumoespec,
            l.status
        FROM lote l JOIN lotecons c ON (c.idlote = l.idlote AND c.tipoobjeto = 'resultado' AND c.idobjeto = ?idresultado? AND c.qtdd > 0 AND c.tipoobjetoconsumoespec = 'prodservformula' AND c.idobjetoconsumoespec = ?idprodservformula?)
        JOIN lotefracao f ON (f.idlote = l.idlote AND f.idunidade = ?unidadepadrao? AND c.idlotefracao = f.idlotefracao)
        JOIN unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) ON (o.tipoobjeto = 'modulo' AND f.idunidade = o.idunidade)
        JOIN " . _DBCARBON . "._modulo m ON m.modulo = o.idobjeto AND m.modulotipo = 'lote'
        WHERE l.idprodserv = ?idprodserv?";
    }


    public static function buscarAtribuicoesDeLotesResultado()
    {
        return "SELECT l.partida,
                        f.idlotefracao,
                        l.exercicio,
                        o.idobjeto,
                        l.idlote,
                        f.qtd AS qtddisp,
                        f.qtd_exp AS qtddisp_exp,
                        c.idlotecons,
                        c.qtdd,
                        c.qtdd_exp,
                        c.tipoobjetoconsumoespec,
                        c.idobjetoconsumoespec,
                        u.idunidade
                   FROM lote l JOIN lotefracao f ON (f.idlote = l.idlote AND f.idunidade = ?unidadepadrao?)
              LEFT JOIN lotecons c ON (c.idlote = l.idlote AND c.tipoobjeto = 'resultado' AND c.idobjeto = ?idresultado? AND c.idlotefracao = f.idlotefracao AND c.idobjetoconsumoespec = '?idprodservformula?')
                   JOIN unidade u ON (f.idunidade = u.idunidade)
                   JOIN unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) ON (o.tipoobjeto = 'modulo' AND f.idunidade = o.idunidade and u.idempresa = o.idempresa)
                   JOIN " . _DBCARBON . "._modulo m ON m.modulo = o.idobjeto AND m.modulotipo = 'lote'
                  WHERE l.idprodserv = ?idprodserv?
                    AND l.status = 'APROVADO'
                    AND ((f.status = 'DISPONIVEL') OR EXISTS(SELECT 1 FROM lotecons cc WHERE cc.idlote = l.idlote AND cc.idlotefracao = f.idlotefracao AND c.tipoobjeto = 'resultado' AND c.idobjeto = ?idresultado?) AND c.qtdd > 0)";
    }

    public static function buscarModuloLotePorIdLote()
    {
        return "SELECT m.modulo
            FROM lote l 
                JOIN unidadeobjeto uo ON l.idunidade = uo.idunidade 
                    AND uo.tipoobjeto = 'modulo'
                JOIN " . _DBCARBON . "._modulo m ON m.modulo = uo.idobjeto 
                    AND m.modulotipo = 'lote'
            WHERE l.idlote = '?idobjeto?'";
    }

    public static function buscarQtdLoteAbertoAutorizada()
    {
        return "SELECT SUM(ROUND(IF(l.qtdajust < 1, l.qtdpedida, l.qtdajust), 4)) AS qtd
                  FROM lote l JOIN formalizacao f ON (f.idlote = l.idlote)
                  JOIN fluxostatus fx ON (fx.idfluxostatus = f.idfluxostatus)
                 WHERE fx.idstatus IN (114, 169)
                   AND l.idpessoa = ?idpessoa?
                   AND l.idprodserv = ?idprodservvacina?
                   AND l.idprodservformula = ?idprodservformula?;";
    }

    public static function buscarSementes()
    {
        return "SELECT DISTINCT l.idlote,
                       lp.idpool,
                       op.ord,
                       c.descr,
                       CASE WHEN l.vencimento < (DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 3 MONTH), '%Y-%m-%d')) THEN 'Y'
                       ELSE 'N'
                       END AS vencido,
                       l.idlote,
                       l.flgalerta,
                       l.vencimento,
                       l.exercicio,
                       l.partida,
                       l.spartida,
                       l.exercicio,
                       l.flgalerta
                  FROM prodservformula f JOIN prodservformulains fi ON (fi.idprodservformula = f.idprodservformula)
                  JOIN prodserv p ON (p.idprodserv = fi.idprodserv AND p.especial = 'Y')
                  JOIN lote l ON (l.idprodserv = fi.idprodserv AND l.status = 'APROVADO' AND l.tipoobjetosolipor = 'resultado')
                  JOIN lotefracao fr ON (fr.idlote = l.idlote AND fr.status = 'DISPONIVEL' AND fr.qtd > 0)
                  JOIN resultado r ON (r.idresultado = l.idobjetosolipor)
                  JOIN amostra a ON (a.idamostra = r.idamostra AND a.idpessoa = ?idpessoa?)
                  JOIN prodserv c ON (c.idprodserv = f.idprodserv)
                  JOIN lotepool lp ON (lp.idlote = l.idlote AND lp.status = 'ATIVO')
                  JOIN pool op ON (op.idpool = lp.idpool)
                 WHERE f.idprodserv = ?idprodserv?
                   AND fi.status = 'ATIVO'
                   ?idempresa?
              ORDER BY lp.idpool DESC, l.partida";
    }

    public static function buscarConcentradosSementes()
    {
        return "SELECT l.idlote,
                       f.qtd AS qtddisp,
                       f.qtd_exp AS qtddisp_exp,
                       l.qtdprod,
                       l.qtdprod_exp,
                       pf.qtdpadraof AS qtdpadrao,
                       pf.qtdpadraof_exp AS qtdpadrao_exp,
                       l.status
                  FROM lotecons c JOIN lote l ON (l.idlote = c.idobjeto AND c.tipoobjeto = 'lote' AND l.status NOT IN ('ESGOTADO', 'CANCELADO', 'REPROVADO'))
                  JOIN lotefracao f ON (f.idlote = l.idlote AND f.status = 'DISPONIVEL' AND f.qtd > 0)
                  JOIN prodserv p ON (p.idprodserv = l.idprodserv AND p.especial = 'Y')
                  JOIN prodservformula pf ON (pf.idprodservformula = l.idprodservformula)
                 WHERE c.idlote = ?idlote? AND c.qtdd > 0";
    }

    public static function buscarQuantidadeConcentradosSementes()
    {
        return "SELECT c.idlote, 
                       s.idlote AS idsemente
                  FROM lote l JOIN lotecons c ON (c.idobjeto = l.idlote AND c.tipoobjeto = 'lote' AND c.qtdd > 0)
                  JOIN lote s ON (s.idlote = c.idlote AND s.status = 'APROVADO' AND s.tipoobjetosolipor = 'resultado')
                  JOIN prodserv p ON (p.idprodserv = s.idprodserv AND p.especial = 'Y')
                 WHERE l.idlote = ?idlote?";
    }

    public static function buscarLotesDisponiveis()
    {
        return "SELECT l.idlote,
                       pl.descr,
                       l.partida,
                       l.exercicio,
                       l.status,
                       l.vencimento,
                       f.qtd AS qtddisp,
                       f.qtd_exp AS qtddisp_exp,
                       l.qtdprod,
                       l.qtdprod_exp,
                       GROUP_CONCAT(DISTINCT (CONCAT(s.partida, '/', s.exercicio)) SEPARATOR ' ') AS sementes
                  FROM lote l JOIN lotefracao f ON (f.idlote = l.idlote AND f.status = 'DISPONIVEL' AND f.qtd > 0)
                  JOIN prodserv pl ON (l.idprodserv = pl.idprodserv AND pl.especial = 'Y')
                  JOIN lotecons c ON (c.idobjeto = l.idlote AND c.tipoobjeto = 'lote' AND c.qtdd > 0)
                  JOIN lote s ON (c.idlote = s.idlote)
                  JOIN prodserv p ON (s.idprodserv = p.idprodserv AND p.especial = 'Y')
                  JOIN lotepool lp ON (lp.idlote = s.idlote AND lp.status = 'ATIVO')
                  JOIN pool op ON (op.idpool = lp.idpool)
                 WHERE l.idpessoa = ?idpessoa?
                   AND l.status NOT IN ('CANCELADO', 'REPROVADO')
                   AND l.idprodserv = ?idprodserv?
                   ?idempresa? 
              GROUP BY l.idlote
              ORDER BY l.partida";
    }

    public static function buscarAlertaTipificacaoLote()
    {
        return "SELECT s.idlote,
                       CONCAT(s.partida, '/', s.exercicio) AS partida,
                       s.status,
                       s.tipificacao,
                       op.ord,
                       s.flgalerta
                  FROM lote l JOIN prodserv pl ON (l.idprodserv = pl.idprodserv AND pl.especial = 'Y')
                  JOIN lotecons c ON (c.idobjeto = l.idlote AND c.tipoobjeto = 'lote' AND c.qtdd > 0)
                  JOIN lote s ON (c.idlote = s.idlote AND s.tipoobjetosolipor = 'resultado')
                  JOIN prodserv p ON (s.idprodserv = p.idprodserv AND p.especial = 'Y')
             LEFT JOIN lotepool lp ON (lp.idlote = s.idlote AND lp.status = 'ATIVO')
             LEFT JOIN pool op ON (op.idpool = lp.idpool)
                 WHERE l.idlote = ?idlote?";
    }

    public static function buscarReservaLotePorNfitem()
    {
        return "SELECT 
                        SUM(qtdd / valconvori) AS qtddconv, SUM(qtdd) AS qtd
                    FROM
                        (SELECT 
                        SUM(ifnull(i.qtdd,i.qtdc)) AS qtdd, IFNULL(l.valconvori, 1) AS valconvori
                        FROM
                            lotecons i
                        JOIN lote l ON l.idlote = i.idlote
                        JOIN prodserv p ON p.idprodserv = l.idprodserv
                        JOIN nfitem ni ON ni.idnfitem = i.idobjeto
                        LEFT JOIN lote l2 ON (l2.idlote = l.idloteorigem)
                        WHERE
                            i.tipoobjeto = 'nfitem'
                                AND (i.qtdd > 0 OR i.qtdc > 0)
                                AND i.idobjeto = ?idnfitem?
                        UNION 
                        SELECT 
                            SUM(r.qtd) AS qtdd, IFNULL(l.valconvori, 1) AS valconvori
                        FROM
                            lotereserva r
                        JOIN lote l ON (l.idlote = r.idlote)
                        WHERE
                            r.idobjeto = ?idnfitem?
                                AND r.tipoobjeto = 'nfitem'
                                AND r.status = 'PENDENTE') AS u";
    }

    public static function buscarLoteLoteativ()
    {
        return "SELECT 
                    la.idlote
                FROM
                    lote lpt
                        JOIN
                    loteativ la ON (la.idlote = lpt.idlote)
                WHERE
                    lpt.partida = '?partida?'
                        AND lpt.exercicio = '?exercicio?'
                LIMIT 1";
    }

    public static function buscarLoteAnaliseLote()
    {
        return "SELECT 
                        a.idlote
                    FROM
                        lote lpt
                            JOIN
                        analiselote a ON (a.idlote = lpt.idlote)
                    WHERE
                        lpt.partida =  '?partida?'
                            AND lpt.exercicio = '?exercicio?'
                    LIMIT 1";
    }

    public static function buscarPartidaLote()
    {
        return "SELECT 
                    REPLACE(CONCAT(CONVERT( LPAD(REPLACE(l.partida, p.codprodserv, ''),
                                        '3',
                                        '0') USING LATIN1),
                                '-',
                                l.exercicio),
                        '/',
                        '.') AS npart,
                    p.codprodserv
                FROM
                    lote l,
                    prodserv p
                WHERE
                    l.idprodserv = p.idprodserv
                        AND l.idlote = ?idlote?";
    }

    public static function buscarModuloPorIdlote()
    {
        return "SELECT 
                        o.idobjeto
                    FROM
                        lote l
                            JOIN
                        unidadeobjeto o ON (o.tipoobjeto = 'modulo'
                            AND o.idunidade = l.idunidade)
                            JOIN
                        carbonnovo._modulo m ON (m.modulo = o.idobjeto
                            AND m.ready = 'FILTROS'
                            AND m.modulotipo = 'lote'
                            AND m.status = 'ATIVO')
                    WHERE
                        l.idlote = ?idlote? ";
    }

    public static function buscaFormalizacaoLote()
    {
        return "SELECT f.idformalizacao, f.status, uo.idobjeto AS modulo
                  FROM formalizacao f JOIN unidadeobjeto uo ON uo.idunidade = f.idunidade AND uo.tipoobjeto = 'modulo'
                  JOIN " . _DBCARBON . "._modulo m on (m.modulo = uo.idobjeto and m.modulotipo = 'formalizacao' and m.status = 'ATIVO')
                 WHERE f.idlote = ?idlote?";
    }
    public static function buscarLotelocalizacao()
    {
        return "SELECT 
                    *
                FROM
                    lotelocalizacao l
                WHERE
                    l.idlote =?idlote?";
    }

    public static function verificarLoteReservadoDisponivel()
    {
        return "SELECT 
                    f.*
                FROM
                    lotefracao f
                        JOIN
                    unidade u ON (u.idunidade = f.idunidade
                        AND u.idtipounidade IN (3 , 21))
                        JOIN
                    lote l ON (l.idlote = f.idlote
                        AND l.status NOT IN ('REPROVADO' , 'CANCELADO'))
                WHERE
                    f.idlote = ?idlote?
                        AND f.status = 'DISPONIVEL'";
    }

    public static function liberarLotereservaPorId()
    {
        return "UPDATE lotereserva 
                    SET 
                        status = 'CONCLUIDO'
                    WHERE
                        idlotereserva = ?idlotereserva?";
    }

    public static function buscarLotePorIdprodservIdunidade()
    {
        return "SELECT 
                idlote, idprodserv, status
            FROM
                lote
            WHERE
                idprodserv = ?idprodserv?
                    AND status = 'APROVADO'
                    AND idunidade = ?idunidade?";
    }

    public static function atualizaLoteSolfab()
    {
        return "UPDATE lote 
                    SET 
                        idsolfab = ?idsolfab?
                    WHERE
                        idlote = ?idlote?";
    }

    public static function buscarQtdpaComFormula()
    {
        return "SELECT IFNULL(SUM(qtdpedida), 0) AS qtdpa
                  FROM lote
                 WHERE status IN ('AGUARDANDO', 'FORMALIZACAO', 'PROCESSANDO', 'ABERTO', 'QUARENTENA', 'TRIAGEM')
                   AND idprodserv = ?idprodserv?
                   AND idprodservformula = ?idprodservformula?";
    }

    public static function buscarLoteNfItem()
    {
        return "SELECT l.npartida,
                       l.exercicio,
                       CONCAT(l.partida, '/', l.exercicio) AS partida,
                       DMA(l.fabricacao) AS dataf,
                       DMA(l.vencimento) AS datav,
                       l.criadoem AS lcriadoem,
                       l.vencimento,
                       l.qtdpedida,
                       fr.qtd AS qtddisp,
                       i.qtd,
                       l.status,
                       l.idlote,
                       fr.idlotefracao,
                       (SELECT alteradopor 
                          FROM carrimbo c 
                         WHERE c.tipoobjeto IN ('lotealmoxarifado', 'lotecq', 'lotediagnostico', 'lotediagnosticoautogenas', 'loteproducao', 'loteretem')
                           AND status = 'ATIVO'
                           AND c.idobjeto = l.idlote
                         LIMIT 1) AS idassinadopor,
                       l.idprodservformula,
                       l.idsolfab,
                       l.tipoobjetoprodpara,
                       l.idobjetoprodpara,
                       p.assinatura,
                       p.un,
                       l.status,
                       u.unidade,
                       u.idunidade,
                       u.producao,
                       u.idtipounidade,
                       o.idobjeto,
                       p.descr,
                       p.codprodserv,
                       p.un,
                       p.local,
                       p.vlrvenda,
                       i.*
                  FROM nfitem i JOIN prodserv p ON p.idprodserv = i.idprodserv
                  JOIN lotecons c ON c.idobjeto = i.idnfitem
                  JOIN lote l ON p.idprodserv = l.idprodserv
                  JOIN lotefracao fr ON fr.idlotefracao = c.idlotefracao
                  JOIN unidadeobjeto o ON (o.tipoobjeto = 'modulo' AND o.idunidade = fr.idunidade)
                  JOIN " . _DBCARBON . "._modulo m ON (m.modulo = o.idobjeto AND m.modulotipo = 'lote' AND m.status = 'ATIVO')
                  JOIN unidade u ON (u.idunidade = fr.idunidade)
                 WHERE l.idlote = fr.idlote
                   AND c.tipoobjeto = 'nfitem'
                   AND (c.qtdsol > 0 OR c.qtdc > 0)
                   AND i.idnfitem = ?idnfitem?
              ORDER BY exercicio DESC, npartida DESC";
    }

    public static function buscarLoteFracaoPorIdloteEIdUnidade()
    {
        return "SELECT f.qtdini,
                       f.qtdini_exp,
                       f.idlote,
                       f.idlotefracaoorigem,
                       l.partida,
                       l.exercicio,
                       l.idnfitem,
                       p.fabricado,
                       p.comprado,
                       lf.idunidade,
                       u.unidade,
                       lu.idunidade AS idunidadelote,
                       lu.unidade AS unidadelote,
                       n.idnf,
                       n.nnfe,
                       fo.idformalizacao,
                       f.criadopor,
                       DMAHMS(f.criadoem) AS criadoem,
                       f.qtd,
                       f.idunidade as idunidadefracao,
                       f.idlotefracao,
                       f.qtd_exp
                  FROM lotefracao f JOIN lote l ON (l.idlote = f.idlote)
                  JOIN prodserv p ON (p.idprodserv = l.idprodserv)
             LEFT JOIN lotefracao lf ON (lf.idlotefracao = f.idlotefracaoorigem)
             LEFT JOIN nfitem i ON (i.idnfitem = l.idnfitem)
             LEFT JOIN nf n ON (n.idnf = i.idnf)
             LEFT JOIN unidade u ON (u.idunidade = lf.idunidade)
             LEFT JOIN formalizacao fo ON (fo.idlote = f.idlote)
                  JOIN unidade lu ON (lu.idunidade = l.idunidade)
                  JOIN unidade fu ON (fu.idunidade = f.idunidade)
                 WHERE f.idlote = ?idlote?
                   AND f.idunidade = ?idunidade?";
    }

    public static function buscarRateio()
    {
        return "SELECT u.idlotecons,
                       u.idlote,
                       u.partida,
                       u.exercicio,
                       u.qtdd,
                       u.qtdc,
                       u.idunidade,
                       u.unidade,
                       u.criadoem,
                       u.criadopor,
                       u.unpadrao,
                       u.idsolcomitem
                  FROM (SELECT sm.idlotecons,
                               lp.idlote,
                               l.partida,
                               l.exercicio,
                               ROUND(((cm.qtdd * sm.qtdd) / lp.qtdprod), 2) AS qtdd,
                               '' AS qtdc,
                               ufc.idunidade,
                               ufc.unidade,
                               sm.criadoem,
                               sm.criadopor,
                               sm.obs,
                               si.idsolmat,
                               l.unpadrao,
                               sc.idsolcomitem
                          FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                          JOIN lotecons c ON (lf.idlote = c.idlote AND c.qtdd > 0 AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')
                          JOIN lotefracao lm ON (lm.idlotefracao = c.idobjeto)
                          JOIN unidade um ON (um.idunidade = lm.idunidade AND um.cd = 'Y')
                          JOIN lotecons cm ON (cm.idlotefracao = lm.idlotefracao AND cm.qtdd > 0 AND cm.tipoobjeto = 'lote' AND cm.status = 'ABERTO')
                          JOIN lote lp ON (lp.idlote = cm.idobjeto)
                          JOIN lotecons sm ON (sm.idlote = cm.idobjeto AND sm.qtdd > 0 AND sm.tipoobjeto = 'lotefracao' AND sm.status = 'ABERTO')
                          JOIN lotefracao lfc ON (lfc.idlotefracao = sm.idobjeto)
                          JOIN unidade ufc ON (ufc.idunidade = lfc.idunidade AND ufc.cd = 'N')
                     LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
                     LEFT JOIN solcomitem sc ON (si.idsolmatitem = sc.idsolmatitem)
                         WHERE l.idprodserv = ?idprodserv?
                           AND l.status NOT IN ('CANCELADO' , 'CANCELADA')
                           AND lf.idunidade = ?idunidade?
                           AND c.tipoobjeto = 'lotefracao'
                           AND sm.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiaslote? DAY)
                      GROUP BY cm.idlotecons , sm.idlotecons 
                    UNION 
                        SELECT sm.idlotecons,
                               lp.idlote,
                               l.partida,
                               l.exercicio,
                               ROUND(((cm.qtdd * sm.qtdd) / lp.qtdprod), 2) AS qtdd,
                               '' AS qtdc,
                               ufc.idunidade,
                               ufc.unidade,
                               sm.criadoem,
                               sm.criadopor,
                               'Pedido venda',
                               '' AS idsolmat,
                               l.unpadrao,
                               sc.idsolcomitem
                          FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                          JOIN lotecons c ON (lf.idlote = c.idlote AND c.qtdd > 0 AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')
                          JOIN lotefracao lm ON (lm.idlotefracao = c.idobjeto)
                          JOIN unidade um ON (um.idunidade = lm.idunidade AND um.cd = 'Y')
                          JOIN lotecons cm ON (cm.idlotefracao = lm.idlotefracao AND cm.qtdd > 0 AND cm.tipoobjeto = 'lote' AND cm.status = 'ABERTO')
                          JOIN lote lp ON (lp.idlote = cm.idobjeto)
                          JOIN lotecons sm ON (sm.idlote = cm.idobjeto AND sm.qtdd > 0 AND sm.tipoobjeto = 'nfitem' AND sm.status = 'ABERTO')
                          JOIN nfitem ni ON (ni.idnfitem = sm.idobjeto)
                          JOIN nf n ON (n.idnf = ni.idnf)
                          JOIN unidade ufc ON (ufc.idtipounidade = 21 AND n.idempresa = ufc.idempresa AND ufc.status = 'ATIVO')
                     LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
                     LEFT JOIN solcomitem sc ON (si.idsolmatitem = sc.idsolmatitem)
                         WHERE l.idprodserv = ?idprodserv? 
                           AND l.status NOT IN ('CANCELADO' , 'CANCELADA')
                           AND lf.idunidade = ?idunidade?
                           AND c.tipoobjeto = 'lotefracao'
                           AND sm.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiaslote? DAY)
                      GROUP BY cm.idlotecons , sm.idlotecons 
                    UNION 
                        SELECT cm.idlotecons,
                               l.idlote,
                               l.partida,
                               l.exercicio,
                               cm.qtdd,
                               '' AS qtdc,
                               um.idunidade,
                               um.unidade,
                               cm.criadoem,
                               cm.criadopor,
                               cm.obs,
                               si.idsolmat,
                               l.unpadrao,
                               sc.idsolcomitem
                          FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote) 
                          JOIN lotecons c ON (lf.idlote = c.idlote AND c.qtdd > 0 AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')
                          JOIN lotefracao lm ON (lm.idlotefracao = c.idobjeto)
                          JOIN unidade um ON (um.idunidade = lm.idunidade AND um.cd = 'Y')
                          JOIN lotecons cm ON (cm.idlotefracao = lm.idlotefracao AND cm.qtdd > 0 AND cm.tipoobjeto = 'lotefracao' AND cm.status = 'ABERTO')
                          JOIN lotefracao lfc ON (lfc.idlotefracao = cm.idobjeto)
                          JOIN unidade ufc ON (ufc.idunidade = lfc.idunidade AND ufc.cd = 'N')
                     LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
                     LEFT JOIN solcomitem sc ON (si.idsolmatitem = sc.idsolmatitem)
                         WHERE l.idprodserv = ?idprodserv?
                           AND l.status NOT IN ('CANCELADO' , 'CANCELADA')
                           AND lf.idunidade = ?idunidade?
                           AND c.tipoobjeto = 'lotefracao'
                           AND cm.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiaslote? DAY)
                      GROUP BY cm.idlotecons 
                    UNION 
                        SELECT cm.idlotecons,
                               l.idlote,
                               l.partida,
                               l.exercicio,
                               cm.qtdd,
                               '' AS qtdc,
                               um.idunidade,
                               um.unidade,
                               cm.criadoem,
                               cm.criadopor,
                               cm.obs,
                               '' AS idsolmat,
                               l.unpadrao,
                               sc.idsolcomitem
                          FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                          JOIN lotecons c ON (lf.idlote = c.idlote AND c.qtdd > 0 AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')
                          JOIN lotefracao lm ON (lm.idlotefracao = c.idobjeto)
                          JOIN unidade um ON (um.idunidade = lm.idunidade AND um.cd = 'Y')
                          JOIN lotecons cm ON (cm.idlotefracao = lm.idlotefracao AND cm.qtdd > 0 AND cm.idobjeto IS NULL AND cm.status = 'ABERTO')
                     LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
                     LEFT JOIN solcomitem sc ON (si.idsolmatitem = sc.idsolmatitem)
                         WHERE l.idprodserv = ?idprodserv?
                           AND l.status NOT IN ('CANCELADO', 'CANCELADA')
                           AND lf.idunidade = ?idunidade?
                           AND c.tipoobjeto = 'lotefracao'
                           AND cm.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiaslote? DAY)
                      GROUP BY cm.idlotecons 
                    UNION 
                        SELECT sm.idlotecons,
                               lp.idlote,
                               l.partida,
                               l.exercicio,
                               ROUND(((cm.qtdd * sm.qtdd) / lp.qtdprod), 2) AS qtdd,
                               '' AS qtdc,
                               um.idunidade,
                               um.unidade,
                               sm.criadoem,
                               sm.criadopor,
                               sm.obs,
                               '' AS idsolmat,
                               l.unpadrao,
                               sc.idsolcomitem
                          FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                          JOIN lotecons c ON (lf.idlote = c.idlote AND c.qtdd > 0 AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')
                          JOIN lotefracao lm ON (lm.idlotefracao = c.idobjeto)
                          JOIN unidade um ON (um.idunidade = lm.idunidade AND um.cd = 'Y')
                          JOIN lotecons cm ON (cm.idlotefracao = lm.idlotefracao AND cm.qtdd > 0 AND cm.tipoobjeto = 'lote' AND cm.status = 'ABERTO')
                          JOIN lote lp ON (lp.idlote = cm.idobjeto)
                          JOIN lotecons sm ON (sm.idlote = cm.idobjeto AND sm.qtdd > 0 AND sm.idobjeto IS NULL AND sm.status = 'ABERTO')
                     LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
                     LEFT JOIN solcomitem sc ON (si.idsolmatitem = sc.idsolmatitem)
                         WHERE l.idprodserv = ?idprodserv?
                           AND l.status NOT IN ('CANCELADO', 'CANCELADA')
                           AND lf.idunidade = ?idunidade?
                           AND c.tipoobjeto = 'lotefracao'
                           AND sm.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiaslote? DAY)
                      GROUP BY cm.idlotecons , sm.idlotecons 
                    UNION 
                        SELECT sm.idlotecons,
                               lp.idlote,
                               l.partida,
                               l.exercicio,
                               '' AS qtdd,
                               ROUND(((cm.qtdd * sm.qtdc) / lp.qtdprod), 2) AS qtdc,
                               ufc.idunidade,
                               ufc.unidade,
                               sm.criadoem,
                               sm.criadopor,
                               sm.obs,
                               '' AS idsolmat,
                               l.unpadrao,
                               NULL AS idsolcomitem
                          FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                          JOIN lotecons c ON (lf.idlote = c.idlote AND c.qtdd > 0 AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')
                          JOIN lotefracao lm ON (lm.idlotefracao = c.idobjeto)
                          JOIN unidade um ON (um.idunidade = lm.idunidade AND um.cd = 'Y')
                          JOIN lotecons cm ON (cm.idlotefracao = lm.idlotefracao AND cm.qtdd > 0 AND cm.tipoobjeto = 'lote' AND cm.status = 'ABERTO')
                          JOIN lote lp ON (lp.idlote = cm.idobjeto)
                          JOIN lotecons sm ON (sm.idlote = cm.idobjeto AND sm.qtdc > 0 AND sm.tipoobjeto = 'lotefracao' AND sm.status = 'ABERTO')
                          JOIN lotefracao lfc ON (lfc.idlotefracao = sm.idobjeto)
                          JOIN unidade ufc ON (ufc.idunidade = lfc.idunidade AND ufc.cd = 'N')
                         WHERE l.idprodserv = ?idprodserv?
                           AND l.status NOT IN ('CANCELADO' , 'CANCELADA')
                           AND lf.idunidade = ?idunidade?
                           AND c.tipoobjeto = 'lotefracao'
                           AND sm.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiaslote? DAY)
                      GROUP BY cm.idlotecons , sm.idlotecons 
                    UNION 
                        SELECT cm.idlotecons,
                               l.idlote,
                               l.partida,
                               l.exercicio,
                               cm.qtdd,
                               cm.qtdc,
                               um.idunidade,
                               um.unidade,
                               cm.criadoem,
                               cm.criadopor,
                               cm.obs,
                               si.idsolmat,
                               l.unpadrao,
                               sc.idsolcomitem
                          FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                          JOIN lotecons c ON (lf.idlote = c.idlote AND c.qtdd > 0 AND c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')
                          JOIN lotefracao lm ON (lm.idlotefracao = c.idobjeto)
                          JOIN unidade um ON (um.idunidade = lm.idunidade AND um.cd = 'Y')
                          JOIN lotecons cm ON (cm.idlotefracao = lm.idlotefracao AND cm.qtdc > 0 AND cm.tipoobjeto = 'lotefracao' AND cm.status = 'ABERTO')
                          JOIN lotefracao lfc ON (lfc.idlotefracao = cm.idobjeto)
                          JOIN unidade ufc ON (ufc.idunidade = lfc.idunidade AND ufc.cd = 'N')
                     LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
                     LEFT JOIN solcomitem sc ON (si.idsolmatitem = sc.idsolmatitem)
                         WHERE l.idprodserv = ?idprodserv?
                           AND l.status NOT IN ('CANCELADO' , 'CANCELADA')
                           AND lf.idunidade = ?idunidade?
                           AND c.tipoobjeto = 'lotefracao'
                           AND cm.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiaslote? DAY)
                      GROUP BY cm.idlotecons 
                    UNION 
                        SELECT c.idlotecons,
                               l.idlote,
                               l.partida,
                               l.exercicio,
                               c.qtdd,
                               c.qtdc,
                               u.idunidade,
                               u.unidade,
                               c.criadoem,
                               c.criadopor,
                               c.obs,
                               si.idsolmat,
                               l.unpadrao,
                               sc.idsolcomitem
                          FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                          JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd > 0 OR c.qtdc > 0) AND c.idlotefracao = lf.idlotefracao AND c.tipoobjeto = 'lote' AND c.status = 'ABERTO')
                          JOIN lote lp ON (lp.idlote = c.idobjeto)
                          JOIN unidade u ON (u.idunidade = lp.idunidade AND u.cd = 'N')
                     LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
                     LEFT JOIN solcomitem sc ON (si.idsolmatitem = sc.idsolmatitem)
                         WHERE l.idprodserv = ?idprodserv?
                         AND l.status NOT IN ('CANCELADO' , 'CANCELADA')
                         AND lf.idunidade = ?idunidade?
                         AND c.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiaslote? DAY) 
                    UNION 
                        SELECT c.idlotecons,
                               l.idlote,
                               l.partida,
                               l.exercicio,
                               c.qtdd,
                               c.qtdc,
                               u.idunidade,
                               u.unidade,
                               c.criadoem,
                               c.criadopor,
                               c.obs,
                               si.idsolmat,
                               l.unpadrao,
                               sc.idsolcomitem
                          FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                          JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd > 0 OR c.qtdc > 0) AND c.idlotefracao = lf.idlotefracao AND c.tipoobjeto = 'lotefracao' AND c.status = 'ABERTO')
                          JOIN lotefracao lp ON (lp.idlotefracao = c.idobjeto)
                          JOIN unidade u ON (u.idunidade = lp.idunidade AND u.cd = 'N')
                     LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
                     LEFT JOIN solcomitem sc ON (si.idsolmatitem = sc.idsolmatitem)
                         WHERE l.idprodserv = ?idprodserv?
                           AND l.status NOT IN ('CANCELADO' , 'CANCELADA')
                           AND lf.idunidade = ?idunidade?
                           AND c.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiaslote? DAY) 
                    UNION 
                        SELECT c.idlotecons,
                               l.idlote,
                               l.partida,
                               l.exercicio,
                               c.qtdd,
                               c.qtdc,
                               u.idunidade,
                               u.unidade,
                               c.criadoem,
                               c.criadopor,
                               c.obs,
                               si.idsolmat,
                               l.unpadrao,
                               sc.idsolcomitem
                          FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                          JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd > 0 OR c.qtdc > 0) AND c.idlotefracao = lf.idlotefracao AND c.tipoobjeto = 'nfitem' AND c.status = 'ABERTO')
                          JOIN nfitem i ON (i.idnfitem = c.idobjeto)
                          JOIN nf n ON (n.idnf = i.idnf AND n.status != 'CANCELADO')
                          JOIN unidade u ON (u.idtipounidade = 21 AND n.idempresa = u.idempresa AND u.status = 'ATIVO')
                     LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
                     LEFT JOIN solcomitem sc ON (si.idsolmatitem = sc.idsolmatitem)
                         WHERE l.idprodserv = ?idprodserv?
                           AND l.status NOT IN ('CANCELADO', 'CANCELADA')
                           AND lf.idunidade = ?idunidade?
                           AND c.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiaslote? DAY) 
                    UNION 
                        SELECT c.idlotecons,
                               l.idlote,
                               l.partida,
                               l.exercicio,
                               c.qtdd,
                               c.qtdc,
                               u.idunidade,
                               u.unidade,
                               c.criadoem,
                               c.criadopor,
                               c.obs,
                               si.idsolmat,
                               l.unpadrao,
                               sc.idsolcomitem
                          FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                          JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd > 0 OR c.qtdc > 0) AND c.idlotefracao = lf.idlotefracao AND c.tipoobjeto = 'resultado' AND c.status = 'ABERTO')
                          JOIN resultado i ON (i.idresultado = c.idobjeto)
                          JOIN amostra n ON (n.idamostra = i.idamostra)
                          JOIN unidade u ON (n.idunidade = u.idunidade AND u.cd = 'N')
                     LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
                     LEFT JOIN solcomitem sc ON (si.idsolmatitem = sc.idsolmatitem)
                         WHERE l.idprodserv = ?idprodserv?
                           AND l.status NOT IN ('CANCELADO', 'CANCELADA')
                           AND lf.idunidade = ?idunidade?
                           AND c.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiaslote? DAY) 
                    UNION 
                        SELECT c.idlotecons,
                               l.idlote,
                               l.partida,
                               l.exercicio,
                               c.qtdd,
                               c.qtdc,
                               u.idunidade,
                              u.unidade,
                               c.criadoem,
                               c.criadopor,
                               c.obs,
                               si.idsolmat,
                               l.unpadrao,
                               sc.idsolcomitem
                          FROM lotefracao lf JOIN lote l ON (lf.idlote = l.idlote)
                          JOIN lotecons c ON (lf.idlote = c.idlote AND (c.qtdd > 0 OR c.qtdc > 0) AND c.idlotefracao = lf.idlotefracao AND c.tipoobjeto IS NULL AND c.idobjeto IS NULL AND c.status = 'ABERTO')
                          JOIN unidade u ON (lf.idunidade = u.idunidade AND u.cd = 'N') 
                     LEFT JOIN solmatitem si ON (si.idsolmatitem = c.idobjetoconsumoespec AND c.tipoobjetoconsumoespec = 'solmatitem')
                     LEFT JOIN solcomitem sc ON (si.idsolmatitem = sc.idsolmatitem)
                         WHERE l.idprodserv = ?idprodserv?
                         AND l.status NOT IN ('CANCELADO', 'CANCELADA')
                         AND lf.idunidade = ?idunidade?
                         AND c.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiaslote? DAY)) AS u
                       WHERE u.idsolcomitem IS NULL
                    ORDER BY u.partida, u.criadoem, u.unidade";
    }

    public static function buscarLoteAnimalParaEstudo()
    {
        return "SELECT 
                    f.idlotefracao,
                    concat(e.sigla,' - ',l.partida,'/',l.exercicio,' - ',ifnull(p.descrcurta,p.descr)) as descr
                FROM  lote l  
                    JOIN lotefracao f on(f.idlote=l.idlote AND f.qtd> ?qtd? AND f.status='DISPONIVEL' AND f.idunidade=?idunidade?) 
                    JOIN prodserv p on(l.idprodserv=p.idprodserv)
                    LEFT JOIN empresa e on(e.idempresa=l.idempresa)
                WHERE l.status in ('QUARENTENA','APROVADO')
                    AND exists (select 1 from plantelobjeto po where po.tipoobjeto = 'prodserv'  AND po.idobjeto = p.idprodserv AND po.idplantel=?plantel?)
                    AND p.status='ATIVO'
                    ?getidempresa?";
    }

    public static function buscarLotesParaUsoNaFicha()
    {
        return "SELECT
                    f.idlotefracao as idlote,
                    concat(l.partida,'/',l.exercicio,' - ',ifnull(p.descrcurta,p.descr)) as descr
                from  lote l
                    join lotefracao f on(f.idlote=l.idlote and f.status='DISPONIVEL' and f.idunidade=?idunidadepadrao?) 
                    join prodserv p on(l.idprodserv=p.idprodserv)
                where l.status in ('QUARENTENA','APROVADO')
                    and exists (select 1 from plantelobjeto po where po.tipoobjeto = 'prodserv'  and po.idobjeto = p.idprodserv and po.idplantel=?idplantel?)
                    and p.status='ATIVO'
                    ?getidempresa?";
    }

    public static function buscarLotesParaComparativo()
    {
        return "SELECT l.idlote,
                        concat(l.partida,'/',l.exercicio,' - ',ifnull(p.descrcurta,p.descr)) as descr
                FROM lote l
                    join lotefracao f on(f.idlote=l.idlote)
                    join prodserv p on(p.idprodserv=l.idprodserv and p.venda='Y')
                    join bioensaio b on (l.idlote = b.idlotepd)
                    join nucleo n on (b.idnucleo = n.idnucleo and n.situacao='ATIVO')
                where l.status in ('QUARENTENA', 'APROVADO')
                    ?getidempresa1?
                    and exists(select b.idlotepd
                        from amostra a
                        join nucleo n FORCE INDEX(PRIMARY) on (n.idnucleo = a.idnucleo)
                        join resultado r FORCE INDEX(idamostra) on (r.idamostra = a.idamostra)
                        join bioensaio b on (n.idnucleo = b.idnucleo)
                        join prodserv ps FORCE INDEX(PRIMARY) on (
                        ps.idprodserv = r.idtipoteste 
                        AND ps.tipo in ('SERVICO','PRODUTO') 
                        AND ps.comparativodelotes='Y'
                        )
                        where 1 ?getidempresa2?
                        and b.idlotepd = l.idlote
                        and CAST(a.idade as UNSIGNED) > '')
                group by idlote order by descr; ";
    }

    public static function atualizarStatuseFluxoStatusPorLote()
    {
        return "UPDATE lote 
            SET status = '?status?', 
                idfluxostatus = '?idfluxostatus?' 
            WHERE idlote = '?idlote?'";
    }

    public static function buscarDescrProdutoBioensaio()
    {
        return "SELECT l.idlote,
                        concat(e.sigla,' - ',l.partida,'/',l.exercicio,' - ',ifnull(p.descrcurta,p.descr)) as descr,
                        l.idunidade
                FROM lote l
                JOIN prodserv p on(p.idprodserv=l.idprodserv)
                LEFT JOIN empresa e on(p.idempresa=e.idempresa)
                WHERE l.idlote = ?idlote?";
    }

    public static function buscarDescrLoteBioensaio()
    {
        return "SELECT l.idlote,
                    concat(e.sigla,' - ',l.partida,'/',l.exercicio,' - ',ifnull(p.descrcurta,p.descr)) as descr,
                    l.status,
                    l.idunidade
                FROM  lote l  
                    JOIN prodserv p on(l.idprodserv=p.idprodserv)
                    JOIN lotefracao f on (f.idlote = l.idlote)
                    LEFT JOIN empresa e on(e.idempresa=l.idempresa)
                WHERE f.idlote =?idlote?";
    }

    public static function buscarInfosLoteBioensaio()
    {
        return "SELECT l.idlote, l.partida as lote,concat(l.partida,'/',l.exercicio,' - ',ifnull(p.descrcurta,p.descr)) as descr
                from lote l
                    join prodserv p on(p.idprodserv=l.idprodserv)
                where l.idlote = ?idlote?
                ?getidempresa?";
    }

    public static function buscarDescrDoLoteFicharep()
    {
        return "SELECT concat(l.partida,'/',l.exercicio,' - ',ifnull(p.descrcurta,p.descr)) as descr
                from lote l
                    join lotefracao f ON(f.idlote = l.idlote)
                    join prodserv p on (p.idprodserv = l.idprodserv)
                where f.idlotefracao = ?idlote?";
    }

    public static function buscarLotePorIdObjetosoliporTipoobjetosolipor()
    {
        return "SELECT * from lote where tipoobjetosolipor = '?tipoobjetosolipor?' and idobjetosolipor = ?idobjetosolipor?";
    }

    public static function buscarDashboardComercialSementesVencidas()
    {
        return "SELECT
                    'dashcrm' as panel_id,
                    'col-md-4' as panel_class_col,
                    'CRM' as panel_title,
                    'dashcrmsementesvencidas' as card_id,
                    'col-md-6 col-sm-6 col-xs-6' as card_class_col, 
                    concat('_modulo=semente&_pagina=0&_ordcol=idlote&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO%22,%22vencido%22:%22Y%22}') as card_url,
                    'fundovermelho' as card_notification_bg,
                    '0' as card_notification,
                    if (count(1) > 0,'danger','success') as card_color,
                    if (count(1) > 0,'danger','success') as card_border_color,
                    '' as card_bg_class,
                    'sementes vencidas' as card_title,
                    count(1) as card_value,
                    'fa-print' as card_icon,
                    'CRM - SEMENTES VENCIDAS' as card_title_modal,
                    '_modulo=semente&_acao=u' as card_url_modal
                FROM lote a
                WHERE EXISTS (
                    SELECT 1 
                    from prodserv p 
                    join  unidadeobjeto u on( u.idunidade = 9 and u.idobjeto = p.idprodserv and u.tipoobjeto = 'prodserv')
                    WHERE p.idprodserv=a.idprodserv
                    and p.tipo = 'PRODUTO'
                    and p.status = 'ATIVO' 
                    and p.especial='Y' 
                    and p.idtipoprodserv = 3)
                    and  DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d') >= a.vencimento and a.status = 'APROVADO'
                union all 
                SELECT
                        'dashcrm' as panel_id,
                        'col-md-4' as panel_class_col,
                        'CRM' as panel_title,
                        'dashcrmsementesavencer' as card_id,
                        'col-md-6 col-sm-6 col-xs-6' as card_class_col, 
                        concat('_modulo=semente&_pagina=0&_ordcol=idlote&_orddir=desc&_filtrosrapidos={%22status%22:%22APROVADO%22,%22avencer%22:%22Y%22}') as card_url,
                        'fundovermelho' as card_notification_bg,
                        '0' as card_notification,
                        if (count(1) > 0,'warning','success') as card_color,
                        if (count(1) > 0,'warning','success') as card_border_color,
                        '' as card_bg_class,
                        'sementes a vencer' as card_title,
                        count(1) as card_value,
                        'fa-print' as card_icon,
                        'CRM - SEMENTES A VENCER' as card_title_modal,
                        '_modulo=semente&_acao=u' as card_url_modal
                FROM lote a
                where exists(
                    select 1 
                    from prodserv p join  unidadeobjeto u on( u.idunidade = 9 and u.idobjeto = p.idprodserv and u.tipoobjeto = 'prodserv')
                    where p.idprodserv=a.idprodserv
                    and p.tipo = 'PRODUTO'
                    and p.status = 'ATIVO' 
                    and p.especial='Y' 
                    and p.idtipoprodserv = 3)
                    and a.vencimento between DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d') and DATE_ADD(DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d'), interval 90 day
                ) and a.status = 'APROVADO'";
    }

    public static function buscarUnidadeLotePorIdLote()
    {
        return "SELECT l.idunidade,
                       u.unidade,
                       l.idloteorigem,
                       (SELECT m.modulo FROM unidadeobjeto o 
                                        JOIN " . _DBCARBON . "._modulo m ON (o.tipoobjeto = 'modulo' AND m.modulo = o.idobjeto AND m.modulotipo = 'lote' AND m.status = 'ATIVO')
                                       WHERE (l.idunidade = o.idunidade) LIMIT 1) modulo
                  FROM lote l JOIN unidade u ON (l.idunidade = u.idunidade)
                 WHERE u.status = 'ATIVO'
                   AND l.idlote = ?idlote?";
    }

    public static function buscarUnidadeLotePorIdLoteFracao()
    {
        return "SELECT l.idunidade,
                       u.unidade,
                       (SELECT m.modulo FROM unidadeobjeto o 
                                        JOIN " . _DBCARBON . "._modulo m ON (o.tipoobjeto = 'modulo' AND m.modulo = o.idobjeto AND m.modulotipo = 'lote' AND m.status = 'ATIVO')
                                       WHERE (l.idunidade = o.idunidade) LIMIT 1) modulo
                  FROM lotefracao l JOIN unidade u ON (l.idunidade = u.idunidade)
                 WHERE u.status = 'ATIVO'
                   AND l.idlotefracao = ?idlotefracao?";
    }

    public static function buscarUnidadeLoteFracaoPorIdProdserv()
    {
        return "SELECT x.unidade,
                       x.idunidade,
                       IFNULL(SUM(x.qtd), 0) AS qtdporlote,
                       x.qtd_exp,
                       x.convestoque,
                       x.modulo
                  FROM (SELECT u.unidade,
                               u.idunidade,
                               IF((f.status = 'DISPONIVEL' AND l.status IN ('APROVADO', 'EMANALISE', 'LIBERADO', 'QUARENTENA')), f.qtd, 0) AS qtd,
                               l.qtdprod_exp AS qtd_exp,
                               u.convestoque,
                               m.modulo
                          FROM lote l JOIN lotefracao f ON (l.idlote = f.idlote AND f.qtd > 0) AND l.piloto = 'N'
                     LEFT JOIN resultado r ON (r.idresultado = l.idobjetosolipor AND tipoobjetosolipor = 'resultado')
                     LEFT JOIN amostra a ON (a.idamostra = r.idamostra)
                     LEFT JOIN pessoa p ON (p.idpessoa = a.idpessoa)
                          JOIN unidade u ON (u.idunidade = f.idunidade AND u.status = 'ATIVO')
                          JOIN unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) ON (o.tipoobjeto = 'modulo' AND f.idunidade = o.idunidade)
                          JOIN " . _DBCARBON . "._modulo m ON m.modulo = o.idobjeto AND m.modulotipo = 'lote'
                          JOIN prodserv ps ON (l.idprodserv = ps.idprodserv AND u.idempresa = ps.idempresa)
                         WHERE l.status NOT IN ('CANCELADO') AND l.idprodserv = ?idprodserv? ?condicaoWhere?) AS x
              GROUP BY x.idunidade
              ORDER BY qtdporlote DESC";
    }

    public static function buscarLoteELoteFracaoPorIdProdservEIdUnidade()
    {
        return "SELECT s.rotulo,
                       l.idlote,
                       l.idunidadegp,
                       l.partida,
                       l.converteest,
                       l.unpadrao,
                       l.unlote,
                       l.vunpadrao,
                       l.exercicio AS loteexercicio,
                       DMA(l.vencimento) AS dmavenci,                       
                       f.idempresa AS idempresaf,
                       f.idlotefracao,
                       f.qtd,
                       f.qtd_exp, 
                       f.idunidade AS idunidadepadrao,
                       r.idresultado,
                       a.idregistro,
                       a.exercicio,
                       p.nome,
                       p.idpessoa,
                       u.unidade,
                       u.convestoque,
                       o.idobjeto,
                       pf.idprodservformula,
                       CONCAT(pf.rotulo, ' ', IFNULL(pf.dose, ' '), '?conteudo?', ' (', pf.volumeformula, ' ', pf.un, ')') AS formula
                  FROM lote l JOIN lotefracao f ON (f.idlote = l.idlote AND f.status = 'DISPONIVEL')
             LEFT JOIN prodservformula pf ON (pf.idprodservformula = l.idprodservformula)
             LEFT JOIN resultado r ON (r.idresultado = l.idobjetosolipor AND tipoobjetosolipor = 'resultado')
             LEFT JOIN amostra a ON (a.idamostra = r.idamostra)
             LEFT JOIN pessoa p ON (p.idpessoa = a.idpessoa)
             LEFT JOIN unidade u ON (u.idunidade = f.idunidade AND u.status = 'ATIVO')
                  JOIN unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) ON (o.tipoobjeto = 'modulo' AND f.idunidade = o.idunidade)
                  JOIN " . _DBCARBON . "._modulo m ON m.modulo = o.idobjeto AND m.modulotipo = 'lote'
                  JOIN fluxostatus fs ON l.idfluxostatus = fs.idfluxostatus
                  JOIN " . _DBCARBON . "._status s ON fs.idstatus = s.idstatus
                 WHERE l.status NOT IN ('ESGOTADO' , 'CANCELADO') ?condicaoWhere? AND l.idprodserv = ?idprodserv? 
                   AND f.idunidade = ?idunidade?
                   AND f.qtd > 0
              GROUP BY l.idlote
              ORDER BY l.exercicio DESC , l.npartida DESC
              LIMIT 80";
    }

    public static function buscarGrafico()
    {
        return "SELECT DMA(u.criadoem) AS datagraf,
                       SUM(qtdc) AS qtdc,
                       SUM(qtdd) AS qtdd,
                       ROUND(SUM(estoque)) AS estoque
                  FROM (SELECT f.criadoem, 
                               f.qtdini AS qtdc, 
                               0 AS qtdd, 
                               0 AS estoque
                          FROM lote l JOIN lotefracao f ON (f.idlote = l.idlote AND idlotefracaoorigem IS NOT NULL)
                          JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                         WHERE l.status IN ('APROVADO' , 'QUARENTENA')
                           AND l.idprodserv = ?idprodserv? 
                           AND f.idunidade = ?idunidade?
                           ?condicaoWhere?
                           AND f.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiasgraf? DAY) 
                    UNION 
                        SELECT l.fabricacao AS criadoem,
                               f.qtdini AS qtdc,
                               0 AS qtdd,
                               0 AS estoque
                          FROM lote l JOIN lotefracao f ON (f.idlote = l.idlote AND idlotefracaoorigem IS NULL)
                          JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                         WHERE l.status IN ('APROVADO' , 'QUARENTENA')
                           AND l.idnfitem IS NULL
                           AND l.idprodserv = ?idprodserv? 
                           AND f.idunidade = ?idunidade?
                           ?condicaoWhere?
                           AND l.fabricacao > DATE_SUB(NOW(), INTERVAL ?consumodiasgraf? DAY) 
                    UNION 
                        SELECT c.criadoem, 
                               c.qtdc, 
                               0 AS qtdd, 
                               0 AS estoque
                          FROM lote l JOIN lotefracao f ON (f.idlote = l.idlote)
                          JOIN lotecons c ON (c.idlotefracao = f.idlotefracao AND c.qtdc > 0 AND c.status = 'ABERTO')
                          JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                         WHERE l.status IN ('APROVADO' , 'QUARENTENA')
                           AND l.idprodserv = ?idprodserv?
                           AND f.idunidade = ?idunidade?
                           ?condicaoWhere?
                           AND c.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiasgraf? DAY) 
                    UNION 
                        SELECT n.prazo AS criadoem,
                               f.qtdini AS qtdc,
                               0 AS qtdd,
                               0 AS estoque
                          FROM lote l JOIN lotefracao f ON (f.idlote = l.idlote AND idlotefracaoorigem IS NULL)
                          JOIN nfitem i ON (i.idnfitem = l.idnfitem)
                          JOIN nf n ON (n.idnf = i.idnf)
                          JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                         WHERE l.status IN ('APROVADO', 'QUARENTENA')
                           AND l.idprodserv = ?idprodserv?
                           AND f.idunidade = ?idunidade?
                           ?condicaoWhere?
                           AND n.prazo > DATE_SUB(NOW(), INTERVAL ?consumodiasgraf? DAY) 
                    UNION 
                        SELECT c.criadoem, 
                               0 AS qtdc, 
                               c.qtdd, 
                               0 AS estoque
                          FROM lote l JOIN lotefracao f ON (f.idlote = l.idlote)
                          JOIN lotecons c ON (c.idlotefracao = f.idlotefracao AND c.idlote = f.idlote AND c.qtdd > 0 AND c.status = 'ABERTO')
                         WHERE l.status IN ('APROVADO' , 'QUARENTENA')
                           AND l.idprodserv = ?idprodserv?
                           AND f.idunidade = ?idunidade?
                           ?condicaoWhere?
                           AND c.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiasgraf? DAY)
                    UNION
                        SELECT e.criadoem, 0 AS qtdc, 0 AS qtdd, i.valor AS estoque
                          FROM etl e JOIN etlitem i ON (i.idetl = e.idetl AND i.idobjeto = ?idobjeto1? AND i.objeto = '?objeto?')
                         WHERE e.idetlconf = ?idetlconf?
                           AND e.idobjeto = ?idobjeto2?
                           AND e.objeto = '?objeto?'
                           AND e.criadoem > DATE_SUB(NOW(), INTERVAL ?consumodiasgraf? DAY)
                           ) AS u
                GROUP BY datagraf
                ORDER BY u.criadoem;";
    }

    public static function buscarEnderecoPessoaLote()
    {
        return "SELECT ps.descr,
                       RIGHT(DMA(l.vencimento), 7) AS vencimento,
                       (SELECT CONCAT(IFNULL(en.logradouro, ''), ' ', IFNULL(en.endereco, ''), ', ', IFNULL(en.numero, ''), ', ', IF((IFNULL(en.complemento, '') <> ''),
                               CONCAT(IFNULL(en.complemento, ''), ', '), ''), IFNULL(en.bairro, ''), ' - ', CONCAT(SUBSTR(en.cep, 1, 5), '-', SUBSTR(en.cep, 6, 3)), ' - ',
                               IFNULL(cs.cidade, ''), '/', IFNULL(en.uf, '')) FROM endereco en LEFT JOIN nfscidadesiaf cs ON cs.codcidade = en.codcidade
                               WHERE en.status = 'ATIVO' AND en.idpessoa = l.idpessoa AND en.idtipoendereco = ?idtipoendereco?) AS enderecosacado,
                        IFNULL(enc.nomepropriedade, '') AS nome,
                        IFNULL(enc.cnpjend, '') AS cpfcnpj,
                        IFNULL(enc.inscest, '') AS inscrest
                   FROM prodserv ps JOIN lote l ON ps.idprodserv = l.idprodserv
              LEFT JOIN pessoa p ON (l.idpessoa = p.idpessoa)
              LEFT JOIN endereco enc ON (enc.status = 'ATIVO' AND enc.idpessoa = l.idpessoa AND enc.idtipoendereco = ?idtipoendereco?)
                  WHERE l.idlote = ?idlote?";
    }

    public static function buscarSolfabPorIdLote()
    {
        return "SELECT p.nome,
                       n.nucleo AS bioensaio,
                       f.finalidade,
                       f.especie,
                       b.idregistro,
                       b.exercicio,
                       b.qtd,
                       b.coranilha,
                       b.nascimento,
                       b.alojamento,
                       b.produto,
                       b.partida,
                       b.volume,
                       b.doses,
                       b.via
                  FROM lote l JOIN bioensaio b ON (b.idbioensaio = l.idobjetoprodpara)
                  JOIN nucleo n ON (n.idnucleo = b.idnucleo)
             LEFT JOIN especiefinalidade f ON (f.idespeciefinalidade = b.idespeciefinalidade)
                  JOIN pessoa p ON (b.idpessoa = p.idpessoa)
                 WHERE l.tipoobjetoprodpara = 'bioensaio'
                   AND l.idlote = ?idlote?";
    }

    public static function buscarEtapaLote()
    {
        return "SELECT e.idetapa
				  FROM loteativ a JOIN lote l ON (l.idlote = a.idlote)
				  JOIN prodservprproc s ON (s.idprodserv = l.idprodserv)
			 LEFT JOIN prprocprativ pa ON (pa.idprproc = s.idprproc AND pa.idprativ = a.idprativ)
			 LEFT JOIN etapa e ON (e.idetapa = pa.idetapa)
				 WHERE a.idlote = ?idlote?
				   AND a.status != 'CONCLUIDO'
			  ORDER BY a.ord
				 LIMIT 1";
    }

    public static function excluirResultadosVinculadosFormalizacao()
    {
        return "DELETE r 
                  FROM lote l JOIN loteativ la ON l.idlote = la.idlote
                  JOIN amostra a ON a.idobjetosolipor = la.idloteativ
                  JOIN resultado r ON r.idamostra = a.idamostra 
                 WHERE l.idlote = ?idlote?";
    }

    public static function atualizarLoteRotuloForm()
    {
        return "UPDATE lote SET rotuloform = '?rotuloform?' WHERE idlote = ?idlote?";
    }

    public static function buscarIdProdservFormulaPorIdLote()
    {
        return "SELECT idprodservformula FROM lote WHERE idlote = ?idlote?";
    }

    public static function buscarTestesSelecionadosFormalizacao()
    {
        return "SELECT la.idloteativ,
                       lo.idobjeto,
                       p.idsubtipoamostra,
                       e.idpessoaform,
                       l.idpessoa,
                       l.tipo,
                       IFNULL(ps.descrcurta, ps.descr) AS descr,
                       l.idempresa
                  FROM lote l JOIN loteativ la ON la.idlote = l.idlote
                  JOIN loteobj lo ON lo.idloteativ = la.idloteativ AND lo.tipoobjeto = 'prodserv'
                  JOIN prativ p ON la.idprativ = p.idprativ
                  JOIN empresa e ON e.idempresa = l.idempresa
                  JOIN prodserv ps ON ps.idprodserv = l.idprodserv
                 WHERE l.idlote = ?idlote?
                   AND NOT EXISTS(SELECT 1 FROM objetovinculo WHERE idobjetovinc = la.idloteativ AND tipoobjetovinc = 'loteativ')";
    }

    public static function buscarCertificadoDeAnaliseDosLotesParaEnvioDeEmail()
    {
        return "SELECT 
				l.idlote,l.partidaext,dma(l.vencimento) as vencimento,ni.cert,REPLACE(concat(convert(lpad(replace(l.partida,p.codprodserv,''),'3', '0')using latin1),'-',l.exercicio), '/', '.') as npart,p.codprodserv
				from lotecons i,nfitem ni,lote l,prodserv p 
				where l.idlote = i.idlote
				and p.assinatura ='S'
				and p.idprodserv = l.idprodserv
				and ni.cert ='Y'
				and i.tipoobjeto='nfitem'                
				and i.qtdd>0
				and i.idobjeto = ni.idnfitem
				and ni.idnf= ?idnf?
				and (l.idassinadopor is not null 
						or 	
						exists (select 1 from lote l2 join carrimbo c on(c.idobjeto = l2.idlote and c.tipoobjeto like('lote%') and c.status in ('ATIVO','ASSINADO'))
						join pessoacrmv pc on (c.idpessoa = pc.idpessoa)
									where l2.partida=l.partida and l2.exercicio=l.exercicio
								)
					)";
    }

    public static function buscarLotesVinculadosPorTipoObjetoConsumoEspecComUnion()
    {
        return "SELECT * FROM (SELECT l.idlote,
                                        l.partida,
                                        l.npartida,
                                        l.exercicio,
                                        l.status,
                                        p.un,
                                        f.qtd,
                                        NULL AS qtdd,
                                        f.idlotefracao,
                                        '' AS idlotecons,
                                        o.idobjeto,
                                        l.vencimento
                                FROM lote l JOIN lotefracao f ON (f.idlote = l.idlote AND f.status = 'DISPONIVEL')
                                JOIN unidade uu ON (f.idunidade = uu.idunidade AND uu.idunidade = '?idunidade?')
                                JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                                JOIN unidadeobjeto o ON (o.tipoobjeto = 'modulo' AND o.idobjeto LIKE ('lote%') AND o.idunidade = f.idunidade)
                                JOIN " . _DBCARBON . "._modulo m ON (m.modulo = o.idobjeto AND m.modulotipo = 'lote' AND m.status = 'ATIVO')
                                WHERE l.status IN ('APROVADO')
                                    AND l.idprodserv = ?idprodserv? 
                                    ?andWhere?
                            UNION ALL 
                                SELECT l.idlote,
                                        l.partida,
                                        l.npartida,
                                        l.exercicio,
                                        l.status,
                                        p.un,
                                        f.qtd,
                                        c.qtdd,
                                        f.idlotefracao,
                                        c.idlotecons,
                                        o.idobjeto,
                                        l.vencimento
                                FROM lote l JOIN lotefracao f ON (f.idlote = l.idlote)
                                JOIN lotecons c ON (c.idlote = l.idlote AND c.idlotefracao = f.idlotefracao AND c.idobjetoconsumoespec = ?idobjetoconsumoespec?
                                    AND c.tipoobjetoconsumoespec = '?tipoobjetoconsumoespec?' AND c.qtdd > 0 AND c.status != 'INATIVO')
                                JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                                JOIN unidadeobjeto o ON (o.tipoobjeto = 'modulo' AND o.idobjeto LIKE ('lote%') AND o.idunidade = f.idunidade)
                                JOIN " . _DBCARBON . "._modulo m ON (m.modulo = o.idobjeto AND m.modulotipo = 'lote' AND m.status = 'ATIVO') 
                                WHERE l.status IN ('APROVADO')
                                  AND l.idprodserv = ?idprodserv?
                                  ?andWhere?) AS u
                            GROUP BY idlote
                            ORDER BY partida";
    }

    public static function buscarLocalizacaoDeLotesVinculados()
    {
        return "SELECT * 
                FROM (
                    SELECT 
                        l.idlote,
                        l.partida,
                        l.npartida,
                        l.exercicio,
                        l.status,
                        p.un,
                        f.qtd,
                        NULL AS qtdd,
                        f.idlotefracao,
                        '' AS idlotecons,
                        o.idobjeto,
                        l.vencimento,
                        t.descricao,
                        td.coluna,
                        td.linha
                    FROM lote l 
                    JOIN lotefracao f ON (f.idlote = l.idlote AND f.status = 'DISPONIVEL')
                    LEFT JOIN lotelocalizacao ll ON(ll.idlote = l.idlote)
                    LEFT JOIN tagdim td ON (td.idtagdim = ll.idobjeto AND ll.tipoobjeto = 'tagdim')
                    LEFT JOIN tag t ON (t.idtag = td.idtag)
                    JOIN unidade uu ON (f.idunidade = uu.idunidade AND uu.idtipounidade = '?idtipounidade?')
                    JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                    JOIN unidadeobjeto o ON (o.tipoobjeto = 'modulo' AND o.idobjeto LIKE ('lote%') AND o.idunidade = f.idunidade)
                    JOIN " . _DBCARBON . "._modulo m ON (m.modulo = o.idobjeto AND m.modulotipo = 'lote' AND m.status = 'ATIVO')
                    WHERE l.status IN ('APROVADO')
                    AND l.idprodserv = ?idprodserv? 
                    ?andWhere?
                    UNION ALL 
                    SELECT 
                        l.idlote,
                        l.partida,
                        l.npartida,
                        l.exercicio,
                        l.status,
                        p.un,
                        f.qtd,
                        c.qtdd,
                        f.idlotefracao,
                        c.idlotecons,
                        o.idobjeto,
                        l.vencimento,
                        t.descricao,
                        td.coluna,
                        td.linha
                    FROM lote l JOIN lotefracao f ON (f.idlote = l.idlote)
                    LEFT JOIN lotelocalizacao ll ON(ll.idlote = l.idlote)
                    LEFT JOIN tagdim td ON (td.idtagdim = ll.idobjeto AND ll.tipoobjeto = 'tagdim')
                    LEFT JOIN tag t ON (t.idtag = td.idtag)
                    JOIN lotecons c ON (c.idlote = l.idlote AND c.idlotefracao = f.idlotefracao AND c.idobjetoconsumoespec = ?idobjetoconsumoespec?
                        AND c.tipoobjetoconsumoespec = '?tipoobjetoconsumoespec?' AND c.qtdd >= 0 AND c.status != 'INATIVO')
                    JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                    JOIN unidadeobjeto o ON (o.tipoobjeto = 'modulo' AND o.idobjeto LIKE ('lote%') AND o.idunidade = f.idunidade)
                    JOIN " . _DBCARBON . "._modulo m ON (m.modulo = o.idobjeto AND m.modulotipo = 'lote' AND m.status = 'ATIVO') 
                    WHERE l.status IN ('APROVADO')
                        AND l.idprodserv = ?idprodserv?
                        ?andWhere?
                    ) AS u
                WHERE EXISTS (
                    SELECT 1
                    FROM lotecons c
                    WHERE c.idlote = u.idlote
                    AND c.idobjetoconsumoespec = ?idsolmatitem?
                    AND c.tipoobjetoconsumoespec = 'solmatitem'
                )
                GROUP BY idlote
                ORDER BY partida";
    }

    public static function buscarLocalizacaoDeLotesVinculado()
    {
        return "SELECT l.idlote,
                        l.partida,
                        l.npartida,
                        l.exercicio,
                        l.status,
                        p.un,
                        f.qtd,
                        c.qtdd,
                        f.idlotefracao,
                        c.idlotecons,
                        o.idobjeto,
                        l.vencimento,
                        t.descricao,
                        td.coluna,
                        td.linha
                   FROM lote l JOIN lotefracao f ON (f.idlote = l.idlote)
                   LEFT JOIN lotelocalizacao ll ON(ll.idlote = l.idlote)
                    LEFT JOIN tagdim td ON (td.idtagdim = ll.idobjeto AND ll.tipoobjeto = 'tagdim')
                    LEFT JOIN tag t ON (t.idtag = td.idtag)
                   JOIN lotecons c ON (c.idlote = l.idlote AND c.idlotefracao = f.idlotefracao AND c.idobjetoconsumoespec = ?idobjetoconsumoespec?
                    AND c.tipoobjetoconsumoespec = 'solmatitem' AND c.qtdd > 0 AND c.status != 'INATIVO')
                   JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                   JOIN unidadeobjeto o ON (o.tipoobjeto = 'modulo' AND o.idobjeto LIKE ('lote%') AND o.idunidade = f.idunidade)
                   JOIN " . _DBCARBON . "._modulo m ON (m.modulo = o.idobjeto AND m.modulotipo = 'lote' AND m.status = 'ATIVO')
                  WHERE l.status IN ('APROVADO')
                    AND l.idprodserv = ?idprodserv?
                    ?andWhere?
               ORDER BY exercicio DESC, npartida DESC";
    }

    public static function buscarLotesVinculadosPorTipoObjetoConsumoEspec()
    {
        return "SELECT l.idlote,
                        l.partida,
                        l.npartida,
                        l.exercicio,
                        l.status,
                        p.un,
                        f.qtd,
                        c.qtdd,
                        f.idlotefracao,
                        c.idlotecons,
                        o.idobjeto,
                        l.vencimento
                   FROM lote l JOIN lotefracao f ON (f.idlote = l.idlote)
                   JOIN lotecons c ON (c.idlote = l.idlote AND c.idlotefracao = f.idlotefracao AND c.idobjetoconsumoespec = ?idobjetoconsumoespec?
                    AND c.tipoobjetoconsumoespec = 'solmatitem' AND c.qtdd > 0 AND c.status != 'INATIVO')
                   JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                   JOIN unidadeobjeto o ON (o.tipoobjeto = 'modulo' AND o.idobjeto LIKE ('lote%') AND o.idunidade = f.idunidade)
                   JOIN " . _DBCARBON . "._modulo m ON (m.modulo = o.idobjeto AND m.modulotipo = 'lote' AND m.status = 'ATIVO')
                  WHERE l.status IN ('APROVADO')
                    AND l.idprodserv = ?idprodserv?
                    ?andWhere?
               ORDER BY exercicio DESC, npartida DESC";
    }

    public static function buscarReservaLotePorNfEUnidade()
    {
        return "SELECT r.qtd,
                       r.qtd_exp,
                       r.criadopor,
                       r.criadoem,
                       u.unidade,
                       n.nnfe,
                       n.idnf,
                       p.nome
                  FROM lotereserva r JOIN lote l ON (r.idlote = l.idlote)
                  JOIN unidade u ON (l.idunidade = u.idunidade)
                  JOIN nfitem i ON (r.idobjeto = i.idnfitem)
                  JOIN nf n ON (i.idnf = n.idnf)
                  JOIN pessoa p ON (n.idpessoa = p.idpessoa)
                 WHERE r.status = 'PENDENTE' AND r.qtd > 0
                   AND r.tipoobjeto = '?tipoobjeto?' 
                   AND r.idlote = ?idlote?";
    }

    public static function buscarFormulaPorFornecedor()
    {
        return "SELECT l.idprodserv,
                       p.nome,
                       p.idpessoa,
                       l.idprodservformula,
                       CONCAT(f.rotulo, '-', IFNULL(f.dose, '--'), ' Doses ', ' (', f.volumeformula, ' ', f.un, ')') AS rotulo,
                       f.dose,
                       IF(pf.validadoem < DATE_SUB(NOW(), INTERVAL 12 MONTH), 'V', 'O') AS validade,
                       pf.idprodservforn,
                       pf.validadopor,
                       pf.validadoem,
                       pf.qtd,
                       pf.valido
                  FROM lote l JOIN pessoa p ON (l.idpessoa = p.idpessoa ?clausulalote?)
                  JOIN prodservformula f ON (l.idprodservformula = f.idprodservformula AND f.status = 'ATIVO' ?strplantel?)
             LEFT JOIN prodservforn pf ON (pf.status = 'ATIVO' AND p.idpessoa = pf.idpessoa AND pf.idprodservformula = f.idprodservformula AND pf.idprodserv = l.idprodserv)
                 WHERE l.idprodserv = ?idprodserv? 
                 ?strvalidacao?
              GROUP BY l.idprodserv, p.idpessoa, l.idprodservformula
              ORDER BY p.nome";
    }

    public static function buscarFormulaEAmostraPorIdProdserv()
    {
        return "SELECT ps.idprodserv,
                       ps.descr,
                       ls.partida,
                       ls.exercicio,
                       ls.vencimento,
                       ls.idlote,
                       ls.status,
                       fr.status AS statusfr,
                       ls.situacao,
                       lp.idpool,
                       lp.idlotepool,
                       ls.observacao,
                       CASE WHEN ls.vencimento < (DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 3 MONTH), '%Y-%m-%d')) THEN 'Y' ELSE 'N' END AS vencido,
                       a.idregistro,
                       a.exercicio,
                       st.subtipoamostra,
                       ls.tipificacao,
                       ls.orgao,
                       ls.idprodserv AS idprodservsemente
                  FROM prodservformula f JOIN prodservformulains i ON (i.idprodservformula = f.idprodservformula)
                  JOIN prodserv c ON (i.idprodserv = c.idprodserv AND c.especial = 'Y')
                  JOIN prodservformula fc ON (fc.idprodserv = i.idprodserv)
                  JOIN prodservformulains ic ON (ic.idprodservformula = fc.idprodservformula)
                  JOIN prodserv ps ON (ps.idprodserv = ic.idprodserv AND ps.especial = 'Y')
                  JOIN lote ls ON (ls.idprodserv = ic.idprodserv AND ls.tipoobjetosolipor = 'resultado' ?clausulals?)
                  JOIN lotefracao fr ON (fr.idlote = ls.idlote ?clausulastatusfracao?)
                  JOIN resultado r ON (r.idresultado = ls.idobjetosolipor)
                  JOIN amostra a ON (a.idamostra = r.idamostra AND a.idpessoa = ?idpessoa?)
             LEFT JOIN lotepool lp ON (lp.idlote = ls.idlote AND lp.status = 'ATIVO')
             LEFT JOIN subtipoamostra st ON (st.idsubtipoamostra = a.idsubtipoamostra)
                 WHERE f.idprodserv = ?idprodserv? 
                 ?strplantel?
              GROUP BY ls.idlote
              ORDER BY ps.idprodserv, lp.idpool, ls.status, ls.npartida ASC";
    }


    public static function buscarProdservFormulaEAmostraPorIdProdserv()
    {
        return "SELECT p.idprodserv,
                       p.descr,
                       l.idlote,
                       l.partida,
                       l.exercicio,
                       l.status,
                       l.vencimento,
                       l.qtddisp,
                       l.qtddisp_exp,
                       GROUP_CONCAT(DISTINCT (CONCAT(ls.partida, '/', ls.exercicio)) SEPARATOR ' ') AS sementes,
                       i.qtdi,
                       i.qtdi_exp,
                       IFNULL(pf.qtdpadrao, 1) AS qtdpadrao
                  FROM prodservformula f JOIN prodserv pf ON (pf.idprodserv = f.idprodserv)
                  JOIN prodservformulains i ON (i.idprodservformula = f.idprodservformula)
                  JOIN prodserv p ON (i.idprodserv = p.idprodserv AND p.especial = 'Y')
                  JOIN lote l ON (l.idprodserv = p.idprodserv AND l.status NOT IN ('ESGOTADO', 'CANCELADO', 'REPROVADO') AND l.idpessoa = ?idpessoa?)
                  JOIN lotefracao fr ON (fr.idlote = l.idlote AND fr.status = 'DISPONIVEL')
                  JOIN lotecons c ON (c.idobjeto = l.idlote AND c.tipoobjeto = 'lote' AND c.qtdd > 0)
                  JOIN lote ls ON (ls.idlote = c.idlote AND ls.idprodserv = ?idprodservant?)
                  JOIN prodserv ps ON (ps.idprodserv = ls.idprodserv AND ps.especial = 'Y')
                 WHERE f.idprodserv = ?idprodserv?
                   AND f.idprodservformula = ?idprodservformula?
                   AND f.status = 'ATIVO'
                   ?strplantel?
              GROUP BY l.idlote
              ORDER BY p.descr, l.partida";
    }

    public static function buscarLotesComPessoasEPartidasNaoNulos()
    {
        return "SELECT l.idlote, CONCAT(l.partida, '/', l.exercicio) AS partida
                  FROM lote l
                 WHERE l.idpessoa IS NOT NULL 
                   AND partida IS NOT NULL
              ORDER BY partida";
    }

    public static function buscarLotePorResultado()
    {
        return "SELECT p.descr, l.partida, l.exercicio, r.idresultado
                  FROM lote l JOIN prodserv p ON p.idprodserv = l.idprodserv
                  JOIN resultado r ON r.idresultado = l.idobjetosolipor
                 WHERE l.idobjetosolipor = ?idresultado?
                   AND l.tipoobjetosolipor = 'resultado'
                   AND l.status NOT IN ('REPROVADO', 'CANCELADO')
                   AND r.status NOT IN ('CANCELADO', 'OFFLINE') 
            UNION 
                SELECT '' AS descr, '' AS partida, '' AS exercicio, idresultado
                  FROM resultado r JOIN prodserv p ON p.idprodserv = r.idtipoteste
                  JOIN prodservtiporelatorio pr ON pr.idprodserv = p.idprodserv
                 WHERE r.idresultado = ?idresultado? AND (LENGTH(JSON_SEARCH(jsonresultado, 'all', '%POSITIVO%')) > 0 OR LENGTH(JSON_SEARCH(jsonresultado, 'all', '%NEGATIVO%')) > 0)
                   AND NOT EXISTS(SELECT 1 FROM lote l2 WHERE l2.idobjetosolipor = r.idresultado AND l2.tipoobjetosolipor = 'resultado' AND l2.status NOT IN ('REPROVADO' , 'CANCELADO'))
                   AND r.status NOT IN ('CANCELADO', 'OFFLINE') 
            UNION 
                SELECT '' AS descr, '' AS partida, '' AS exercicio, idresultado
                  FROM resultado r JOIN prodserv p ON p.idprodserv = r.idtipoteste
                  JOIN prodservtiporelatorio pr ON pr.idprodserv = p.idprodserv
                 WHERE r.idresultado = ?idresultado?
                   AND r.alerta = 'Y' 
                   AND r.status NOT IN ('CANCELADO', 'OFFLINE')
                   AND NOT EXISTS(SELECT 1 FROM lote l3 WHERE l3.idobjetosolipor = r.idresultado AND l3.tipoobjetosolipor = 'resultado' AND l3.status NOT IN ('REPROVADO', 'CANCELADO'))";
    }

    public static function buscarLotePorIdObjetoSoliPor()
    {
        return "SELECT CONCAT(partida, '/', exercicio) AS partida
                  FROM lote
                 WHERE idobjetosolipor = ?idobjetosolipor?                   
                   AND tipoobjetosolipor = '?tipoobjetosolipor?'
                   AND status NOT IN ('REPROVADO' , 'CANCELADO')";
    }

    public static function buscarStatusLotePorPartidaEExercicio()
    {
        return "SELECT status FROM lote WHERE idunidade = ?idunidade? AND partida = ?partida? AND exercicio = ?exercicio?";
    }

    public static function atualizarPprodservFormulaLote()
    {
        return "UPDATE lote SET idprodservformula = '?idprodservformula?' WHERE idlote = ?idlote?";
    }

    public static function buscarQuantidadeLotePorProduto()
    {
        return "SELECT count(*) as qtd FROM lote WHERE idprodserv = ?idprodserv?";
    }

    public static function buscarInfosEtiquetaRotulagem15x60()
    {
        return "SELECT 
                    l.idlote, l.fabricacao, l.vencimento, l.partidaext, pf.volumeformula, pf.un
                FROM
                    lote l
                    LEFT JOIN
                        prodservformula pf on (pf.idprodservformula = l.idprodservformula)
                WHERE
                    l.idlote = ?idlote?";
    }


    public static function buscarAmostraProdservlote()
    {
        return "SELECT p.idprodservloteservico,l.idlote,                    
                        p.idsubtipoamostra,
                        e.idpessoaform as idpessoa,
                        l.partida,
                        l.exercicio,                      
                        l.tipo,
                        IFNULL(ps.descrcurta, ps.descr) AS descr,
                        l.idempresa
                FROM lote l 
                JOIN prodserv ps ON ps.idprodserv = l.idprodserv
                JOIN empresa e ON e.idempresa = l.idempresa
                JOIN prodservloteservico p ON(p.idprodserv=l.idprodserv  AND p.status!='INATIVO') 
                WHERE l.idlote= ?idlote?";
    }

    public static function buscarTestesProdservlote()
    {
        return "SELECT 
                    i.idprodserv
                FROM
                    lote l
                        JOIN
                    prodservloteservico p ON (p.idprodserv = l.idprodserv
                        AND p.status != 'INATIVO')
                        JOIN
                    prodservloteservicoins i ON (i.idprodservloteservico = p.idprodservloteservico
                        AND i.status = 'ATIVO')
                WHERE
                    l.idlote = ?idlote?";
    }

    public static function buscarFormalizacaoPorLote()
    {
        return "SELECT idformalizacao
                  FROM formalizacao
                 WHERE idlote = ?idlote?";
    }

    public static function buscarLotePorIdProdServFormular()
    {
        return "SELECT idlote
                FROM lote
                WHERE idprodservformula = ?idprodservformula?";
    }

    public static function burscarLotePorProdserv()
    {
        return "SELECT idlote, CONCAT(partida, '/', exercicio) as lote
                  FROM lote
                 WHERE idprodserv in  (?idprodserv?)";
    }

    public static function buscarLotesDisponivesPorIdProdserv()
    {
        return "SELECT 
                    l.idlote, lf.idlotefracao, CONCAT(l.partida, '/', l.exercicio) as lote, recuperaexpoente(lf.qtd,lf.qtd_exp) as estoque, l.vencimento,
                    l.status, lf.idunidade, uo.idobjeto as modulo, l.idempresa, l.converteest, l.unpadrao, l.unlote, CONCAT(l.partida, '/', l.exercicio) as partida
                from lote l
                join lotefracao lf on lf.idlote = l.idlote and lf.status = 'DISPONIVEL'
                join unidadeobjeto uo on uo.idunidade = lf.idunidade and uo.tipoobjeto = 'modulo'
                join carbonnovo._modulo m on m.modulo = uo.idobjeto and m.modulotipo = 'lote'
                where l.idprodserv = ?idprodserv?
                and l.status in(?status?)
                and lf.idunidade = ?idunidade?
                group by l.idlote, lf.idlotefracao";
    }

    public static function buscarRazaoSocial()
    {
        return "SELECT empresa.razaosocial
        FROM empresa
        INNER JOIN lote ON empresa.idempresa = lote.idempresa
        WHERE lote.idlote = ?idlote?";
    }

    public static function buscarConsumoLotes()
    {
        return "SELECT ROUND(IFNULL(l.vlrlote, 0), 2) AS vlritem,
                        l.idlote,
                        lc.qtdc,
                        lc.qtdd,
                        lc.tipoobjeto,
                        lc.tipoobjetoconsumoespec,
                        lc.idobjetoconsumoespec,
                        si.idsolmat,
                        ni.idnf,
                        l.criadoem
                  FROM lote l JOIN nfitem ni ON ni.idnfitem = l.idnfitem
                  JOIN lotecons lc ON lc.idlote = l.idlote
                  JOIN solmatitem si ON si.idsolmatitem = lc.idobjetoconsumoespec AND lc.tipoobjetoconsumoespec = 'solmatitem'
                 WHERE l.idlote IN(?arr_idlote?)
                   AND lc.status = 'ABERTO'";
    }

    public static function buscarSeLoteConsomeTransferencia()
    {
        return "SELECT u.convestoque,
                        l.valconvori AS valconv,
                        l.converteest AS convertido,
                        l.vunpadrao,
                        p.consometransf,
                        p.imobilizado,
                        u.idtipounidade
                    FROM lotefracao f JOIN lote l ON (l.idlote = f.idlote)
                    JOIN unidade u ON (u.idunidade = f.idunidade)
                    JOIN prodserv p ON (p.idprodserv = l.idprodserv)
                    WHERE f.idlotefracao = '?idlotefracao?'";
    }

    public static function atualizarValorLote()
    {
        return "UPDATE lote SET vlrlote = '?vlrlote?' WHERE idlote = ?idlote?";
    }

    public static function atualizarQtdLote()
    {
        return "UPDATE lote SET qtdprod = '?qtdprod?', qtdpedida = '?qtdpedida?' WHERE idnfitem = ?idnfitem?";
    }

    public static function buscaSeloLote()
    {
        return "SELECT 
                    IFNULL(p.descrcurta, p.descr) AS descr
                FROM
                    loteformulains c
                        JOIN
                  
                    prodserv p ON (p.idprodserv = c.idprodserv
                        AND p.descr LIKE ('%SELO%'))
                WHERE
                    c.idlote= ?idlote? limit 1";
    }
    public static function BuscarQtdinilote()
    {
        return "SELECT 
                    IFNULL(SUM(lc.qtdc), 0) + IFNULL(lf.qtdini, 0) AS qtdini 
                FROM
                    lote l
                        JOIN
                    lotefracao lf ON (lf.idlote = l.idlote AND lf.idunidade = l.idunidade)
                        LEFT JOIN
                    lotecons lc ON (lc.idlotefracao = lf.idlotefracao and lc.status!='INATIVO' and lc.qtdc>0  and lc.tipoobjeto!='lotefracao')
                WHERE
                    lf.idlote = '?idlote?'
                GROUP BY l.idlote;";
    }

    public static function BuscarVolumeFormula()
    {
        return "SELECT 
                    volumeformula,
                    un
                FROM
                    prodservformula
                WHERE
                    idprodservformula =  '?idprodservformula?'";
    }

    public static function buscarProdservPorIdLote()
    {
        return "SELECT distinct t.tipoprodserv
                from lote l
                join prodserv p on l.idprodserv = p.idprodserv 
                join tipoprodserv t on t.idtipoprodserv = p.idtipoprodserv 
                where l.idlote = ?idlote?";
    }

    public static function buscarLote()
    {
        return "SELECT distinct t.tipoprodserv
                from lote l
                join prodserv p on l.idprodserv = p.idprodserv 
                join tipoprodserv t on t.idtipoprodserv = p.idtipoprodserv 
                where l.idlote = ?idlote?";
    }

    public static function buscarQtdLote(){
        return "SELECT count(idlote) FROM lote WHERE idnfitem = ?idnfitem?";
    }

    public static function buscarLotePorIdNfitem(){
        return "SELECT idlote FROM lote WHERE idnfitem = ?idnfitem?";
    }

    public static function atualizarLotesVencidos() {
        return "UPDATE lote SET status = 'VENCIDO', idfluxostatus = ?idfluxostatus? WHERE idlote = ?idlote?";
    }

    public static function buscarLotesAVencer() {
        return "SELECT l.idlote, l.substatus, m.modulo, l.idempresa
                FROM lote l
                JOIN unidadeobjeto uo ON uo.idunidade = l.idunidade AND uo.idempresa = l.idempresa
                JOIN carbonnovo._modulo m ON uo.idobjeto = m.modulo AND uo.tipoobjeto = 'modulo' AND m.modulotipo = 'lote'
                WHERE l.vencimento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND m.modulo = 'lotealmoxarifado'
                AND l.substatus != 'REVALIDAR'
                AND l.status != 'VENCIDO'";
    }

    public static function revalidarLotesAVencer() {
        return "UPDATE lote
                SET substatus = 'REVALIDAR'
                WHERE idlote = ?idlote?";
    }

    public static function revalidarLote() {
        return "UPDATE lote SET substatus = 'REVALIDADO' WHERE idlote = ?idlote?";
    }

    public static function buscarLotesVencidos() {
        return "SELECT l.idlote, l.substatus, m.modulo, l.idempresa, l.idfluxostatus, l.status
                FROM lote l
                JOIN unidadeobjeto uo ON uo.idunidade = l.idunidade AND uo.idempresa = l.idempresa
                JOIN carbonnovo._modulo m ON uo.idobjeto = m.modulo AND uo.tipoobjeto = 'modulo' AND m.modulotipo = 'lote'
                WHERE l.vencimento < NOW() 
                AND m.modulo = 'lotealmoxarifado'
                AND l.status NOT IN('VENCIDO', 'CANCELADO', 'CONCLUIDO')";
    }

    public static function atualizarStatusLote() {
        return "UPDATE lote set status = '?status?', idfluxostatus = ?idfluxostatus? WHERE idlote = ?idlote?";
    }

    public static function buscaVinculoConsumoPdi(){
        return "SELECT
                        lo.idlote AS idloteorigem,
                        ld.idlote AS idlotedestino,
                        lo.partida AS loteorigem,
                        ld.partida AS lotedestino,
                        ov.criadoem,
                        ov.criadopor
                    FROM objetovinculo ov
                            JOIN
                        lote lo ON (lo.idlote = ov.idobjeto AND ov.tipoobjeto = 'lotepdiorigem')
                            JOIN
                        lote ld ON (ld.idlote = ov.idobjetovinc AND ov.tipoobjetovinc = 'lotepdidestino')
                    WHERE lo.idlote = ?idlote?;";
    }
}
