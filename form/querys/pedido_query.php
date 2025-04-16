<?
class PedidoQuery{    


    public static function listarPedidoVinculado(){
        return "SELECT 
                    n.idnf, 
                    CASE
                        WHEN n.nnfe is null THEN n.idnf
                        WHEN n.nnfe = '' THEN n.idnf
                        ELSE n.nnfe
                    END AS nnfe,
                    n.tiponf,t.natoptipo
                FROM
                    nf n left join natop t on(t.idnatop = n.idnatop)
                WHERE
                n.idobjetosolipor = ?idnf?
                        AND n.tipoobjetosolipor = 'nf'
                ORDER BY n.idnf";

    }

    public static function buscarNfitemGnre()
    {
        return "SELECT 
                    c.idcontapagar,
                    DMA(c.datareceb) AS datarecebimento,
                    c.status,
                    c.valor,
                    c.obs AS obscontapagar,
                    i.*
                FROM
                    nfitem i
                        JOIN
                    contapagar c ON (c.idobjeto = i.idnf
                        AND c.tipoobjeto = 'nf')
                WHERE
                    i.idobjetoitem = ?idnf?
                        AND i.tipoobjetoitem = 'nf'";
    }

    public static function buscarNfitemPedido()
    {
       return "SELECT 
                    CONCAT(IFNULL(CONCAT(e.sigla, ' - '), ''),
                            i.prodservdescr) AS descr,
                    p.descr AS desclog,
                    p.tipo,
                    p.descr AS descroriginal,
                    p.comissionado,
                    i.cprod AS codprodserv,
                    i.un,
                    p.local,
                    IFNULL(p.vlrvenda, '0.00') AS vlrvenda,
                    p.especial,
                    p.venda,
                    p.fabricado,
                    p.certificado,
                    i.*,
                    ii.vii
                FROM
                    nfitem i
                        LEFT JOIN
                    prodserv p ON (p.idprodserv = i.idprodserv)
                        LEFT JOIN
                    nfitemimport ii ON ii.idnfitem = i.idnfitem
                        LEFT JOIN
                    empresa e ON e.idempresa = p.idempresa
                WHERE
                    i.idnf = ?idnf? AND i.tiponf = 'V'
                ORDER BY i.nfe desc,p.descr" ;
    }

    public static function buscarListaCFOPporNatop()
    {
        return "SELECT 
                    GROUP_CONCAT(cfop) AS lcfop
                FROM
                    cfop
                WHERE
                    idnatop = ?idnatop? AND status ='ATIVO'";
    }


    public static function buscarTpnf()
    {
        return "SELECT 0 AS id, '[0] NF-e Entrada' AS valor
                UNION SELECT 1 AS id, '[1] NF-e Saída' AS valor
                UNION SELECT 3 AS id, '[3] NF-e Complementar' AS valor";
    }

    public static function buscarMoeda()
    {
        return "SELECT 'REAL' AS id ,'BRL' AS valor UNION SELECT 'USD' AS id,'USD' AS valor UNION SELECT 'EUR' AS id,'EUR' AS valor";
    }

    public static function buscarNfentradaPorIdnfe()
    {
        return "SELECT idnf FROM nf WHERE idnfe LIKE '%?refnfe?%';";
    }

    public static function buscarNfdevolucaoPoridnfe()
    {
        return "SELECT 
                    idnf, nnfe, total
                FROM
                    nf
                WHERE
                    nfref LIKE '%?idnfe?%'";
    }

    public static function buscarNfitemMoedaEstrangeira()
    {
        return "SELECT 
                    i.idnfitem
                FROM
                    nfitem i
                WHERE
                    i.moedaext IN ('USD' , 'EUR')
                        AND i.vlritemext IS NOT NULL
                        AND i.idnf = ?idnf? ";
    }

    public static function buscarLoteNfitemPorIdnf()
    {
        return "SELECT 
                        i.idnfitem,
                        p.tipo,
                        l2.exercicio AS exercicio2,
                        l2.partida AS partida2,
                        l2.idlote AS idlote2,
                        w.exercicio,
                        w.partida,
                        w.idlote,
                        i.idprodserv,
                        w.unpadrao,
                        w.qtdprod,
                        w.status,
                        p.comprado
                    FROM
                        nf n
                            JOIN
                        nfitem i
                            JOIN
                        prodserv p ON (p.idprodserv = i.idprodserv)
                            LEFT JOIN
                        lote w ON (w.idnfitem = i.idnfitem)
                            LEFT JOIN
                        prodservforn pf ON (pf.idprodserv = i.idprodserv
                            AND pf.idprodservforn = i.idprodservforn
                            AND pf.status = 'ATIVO'
                            AND pf.idpessoa = n.idpessoa)
                            LEFT JOIN
                        tipoprodserv t ON (t.idtipoprodserv = p.idtipoprodserv)
                            LEFT JOIN
                        empresa e ON (e.idempresa = p.idempresa)
                            LEFT JOIN
                        lotecons lc ON (lc.idobjeto = i.idnfitem
                            AND lc.tipoobjeto = 'nfitem'
                            AND lc.qtdc > 0)
                            LEFT JOIN
                        lote l2 ON (l2.idlote = lc.idlote)
                    WHERE
                        i.nfe = 'Y' AND i.idnf = n.idnf
                            AND n.idnf = ?idnf?
                    GROUP BY i.idnfitem
                    ORDER BY p.descr";
    }

    public static function buscarLotePorNfitem()
    {
        return "SELECT 
                    l.idlote,
                    CONVERT( LPAD(REPLACE(l.partida, l.spartida, ''),
                            '3',
                            '0') USING LATIN1) AS partida,
                    l.partida AS partidacompleta,
                    p.assinatura,
                    l.exercicio,
                    DMA(l.vencimento) AS vencimento,
                    l.idloteorigem,
                    i.qtdd,
                    IFNULL((SELECT 
                                    alteradopor
                                FROM
                                    carrimbo c
                                WHERE
                                    c.tipoobjeto IN ('lotealmoxarifado' , 'lotecq',
                                        'lotediagnostico',
                                        'lotediagnosticoautogenas','lotesproducaobacterias','lotesproducaofungos',
                                        'loteproducao',
                                        'loteretem',
                                        'lote')
                                        AND status = 'ASSINADO'
                                        AND c.idobjeto = l.idlote
                                LIMIT 1),
                            (SELECT 
                                    alteradopor
                                FROM
                                    carrimbo c
                                WHERE
                                    c.tipoobjeto IN ('lotealmoxarifado' , 'lotecq',
                                        'lotediagnostico',
                                        'lotediagnosticoautogenas','lotesproducaobacterias','lotesproducaofungos',
                                        'loteproducao',
                                        'loteretem',
                                        'lote')
                                        AND status = 'ASSINADO'
                                        AND c.idobjeto = l2.idlote
                                LIMIT 1)) AS idassinadopor
                FROM
                    lotecons i
                        JOIN
                    lote l ON l.idlote = i.idlote
                        JOIN
                    prodserv p ON p.idprodserv = l.idprodserv
                        JOIN
                    nfitem ni ON ni.idnfitem = i.idobjeto
                        LEFT JOIN
                    lote l2 ON (l2.idlote = l.idloteorigem)
                WHERE
                    1 AND i.tipoobjeto = 'nfitem'
                        AND (i.qtdd > 0 OR i.qtdc > 0)
                        AND i.idobjeto = ?idnfitem?
                LIMIT 1";
    }

    public static function BuscarCfopPorOrigem()
    {
        return "SELECT 
                    cfop as id, cfop
                FROM
                    cfop c
                WHERE
                    c.status = 'ATIVO'
                        AND origem = '?origem?'";
    }

    public static function BuscarNfitemImportacao()
    {
        return "SELECT 
                        *
                    FROM
                        nfitemimport
                    WHERE
                        idnfitem=?idnfitem?";
    }

    public static function BuscarComissaoPorIdnfitem()
    {
        return "SELECT 
                    c.*
                FROM
                    nfitemcomissao c
                WHERE
                    pcomissao > 0 
                    AND c.idnfitem=?idnfitem?";
    }

    public static function BuscarReservaConsumoLotePorIdnfitem(){
        return "SELECT 
                        1
                    FROM
                        lotecons
                    WHERE
                        qtdd > 0 AND idobjeto = ?idnfitem?
                            AND tipoobjeto = 'nfitem' 
                    UNION SELECT 
                        1
                    FROM
                        lotereserva
                    WHERE
                        qtd > 0 AND idobjeto = ?idnfitem?
                            AND tipoobjeto = 'nfitem'
                            AND status = 'PENDENTE'";
    }

    public static function BuscarItensPedido()
    {
        return "SELECT * FROM (        
            SELECT fr.idempresa as idempresafr,l.npartida , 
                    l.exercicio,  
                    CONCAT(l.partida, '/', l.exercicio) AS partida,
                    DMA(l.fabricacao) AS dataf,
                    DMA(l.vencimento) AS datav,
                    l.criadoem as lcriadoem,
                    l.vencimento,
                    l.qtdpedida,
                    fr.qtd as qtddisp,		
                    l.status,
                    l.idlote,
                    fr.idlotefracao,
                    (select alteradopor from carrimbo c where  c.tipoobjeto in ('lotealmoxarifado','lotecq','lotediagnostico','lotediagnosticoautogenas','loteproducao','loteretem') and status = 'ATIVO' and c.idobjeto = l.idlote limit 1) as idassinadopor,
                    l.idsolfab,
                    l.tipoobjetoprodpara,
                    l.idobjetoprodpara,
                    l.unpadrao,
                    p.assinatura,
                    u.unidade,
                    u.idunidade,
                    u.producao,
                    u.idtipounidade,
                    o.idobjeto,
                    p.descr,
                    p.codprodserv,
                    p.local,
                    p.vlrvenda,
                    'N' as lfprod,
                    f.idformalizacao,
                    f.envio as lenvio,
                    i.* 
                FROM nfitem i JOIN lotecons c ON c.idobjeto  = i.idnfitem
                JOIN lotefracao fr ON fr.idlotefracao = c.idlotefracao 
                JOIN lote l ON l.idlote = fr.idlote and l.piloto !='Y'
                JOIN prodserv p ON l.idprodserv = p.idprodserv
                LEFT JOIN formalizacao f ON l.idlote = f.idlote
                JOIN unidadeobjeto o on(o.tipoobjeto='modulo'                                                                             
                AND o.idunidade = fr.idunidade)	
                JOIN " . _DBCARBON . "._modulo m on (m.modulo = o.idobjeto AND m.ready='FILTROS' AND m.modulotipo = 'lote')
                JOIN unidade u on(u.idunidade=fr.idunidade)
                WHERE  c.tipoobjeto = 'nfitem'
                ?strform?
                ?idempresa?
                AND i.idnfitem =?idnfitem?
            UNION 
            SELECT fr.idempresa as idempresafr,l.npartida, 
                    l.exercicio, 
                    CONCAT(l.partida, '/', l.exercicio) AS partida,
                    DMA(l.fabricacao) AS dataf,
                    DMA(l.vencimento) AS datav,
                    l.criadoem as lcriadoem,
                    l.vencimento,
                    l.qtdpedida,
                    fr.qtd as qtddisp,
                    l.status,
                    l.idlote,
                    fr.idlotefracao,
                    (select alteradopor from carrimbo c where  c.tipoobjeto in ('lotealmoxarifado','lotecq','lotediagnostico','lotediagnosticoautogenas','loteproducao','loteretem') and status = 'ATIVO' and c.idobjeto = l.idlote limit 1) as idassinadopor,
                    l.idsolfab,
                    l.tipoobjetoprodpara,
                    l.idobjetoprodpara,
                    l.unpadrao,
                    p.assinatura,
                    u.unidade,
                    u.idunidade,
                    u.producao,
                    u.idtipounidade,
                    o.idobjeto,
                    p.descr,
                    p.codprodserv,
                    p.local,
                    p.vlrvenda,
                    if((select count(*) from lotefracao llf, unidade uu  where llf.idlote=l.idlote  and llf.status='DISPONIVEL' and uu.idunidade=llf.idunidade and uu.idtipounidade in (21))>=1,'Y','N') as lfprod,
                    f.idformalizacao,
                    f.envio as lenvio,
                    i.*  
                FROM nfitem i JOIN prodserv p ON p.idprodserv = i.idprodserv
                JOIN lote l ON l.idprodserv = p.idprodserv and l.piloto !='Y'
                LEFT JOIN formalizacao f ON l.idlote = f.idlote
                JOIN lotefracao fr ON l.idlote = fr.idlote
                JOIN unidadeobjeto o on(o.tipoobjeto='modulo' -- and o.idobjeto like ('lote%') 
                AND o.idunidade = fr.idunidade)
                JOIN " . _DBCARBON . "._modulo m on (m.modulo = o.idobjeto and m.modulotipo = 'lote' and m.status = 'ATIVO')	
                JOIN unidade u on(u.idunidade=fr.idunidade and ?struni?)
                WHERE l.idloteorigem  is null
                AND p.material!='Y'
                ?strest?
                ?idempresa?
                ?strform?
                ?strlp?
                ?idempresa?
                AND i.idnfitem =?idnfitem?
            UNION 
            SELECT fr.idempresa as idempresafr,l.npartida, 
                    l.exercicio, 
                    CONCAT(l.partida, '/', l.exercicio) AS partida,
                    DMA(l.fabricacao) AS dataf,
                    DMA(l.vencimento) AS datav,
                    l.criadoem as lcriadoem,
                    l.vencimento,
                    l.qtdpedida,
                    fr.qtd as qtddisp,
                    l.status,
                    l.idlote,
                    fr.idlotefracao,
                    (select alteradopor from carrimbo c where  c.tipoobjeto in ('lotealmoxarifado','lotecq','lotediagnostico','lotediagnosticoautogenas','loteproducao','loteretem') and status = 'ATIVO' and c.idobjeto = l.idlote limit 1) as idassinadopor,
                    l.idsolfab,
                    l.tipoobjetoprodpara,
                    l.idobjetoprodpara,
                    l.unpadrao,
                    p.assinatura,
                    u.unidade,
                    u.idunidade,
                    u.producao,
                    u.idtipounidade,
                    o.idobjeto,
                    p.descr,
                    p.codprodserv,
                    p.local,
                    p.vlrvenda,
                    if((SELECT count(*) from lotefracao llf, unidade uu  where llf.idlote=l.idlote  and llf.status='DISPONIVEL' and uu.idunidade=llf.idunidade and uu.idtipounidade in (21))>=1,'Y','N') as lfprod,
                    f.idformalizacao,
                    f.envio as lenvio,
                    i.*  
                FROM nfitem i JOIN prodserv p ON p.idprodserv = i.idprodserv
                JOIN lote l ON l.idprodserv = p.idprodserv and l.piloto !='Y'
                LEFT JOIN formalizacao f ON l.idlote = f.idlote
                JOIN lotefracao fr ON l.idlote = fr.idlote 
                JOIN unidadeobjeto o on(o.tipoobjeto='modulo' -- AND o.idobjeto like ('lote%')  
                AND o.idunidade = fr.idunidade)
                JOIN " . _DBCARBON . "._modulo m on (m.modulo = o.idobjeto and m.modulotipo = 'lote' and m.status = 'ATIVO')
                JOIN unidade u on(u.idunidade=fr.idunidade and ?struni?)
                WHERE l.idloteorigem  is null
                AND p.material='Y'
                AND l.status in ('APROVADO','LIBERADO') and fr.status='DISPONIVEL'
                ?idempresa?
                ?strform?
                ?strlp?
                ?idempresa? 
                AND i.idnfitem =?idnfitem?)as su 
            WHERE ((su.idtipounidade !=5 and su.lfprod='Y') or (su.lfprod='N')) and su.status !='CANCELADO'
            group by idlotefracao
            ORDER BY exercicio ASC, npartida ASC";
    }

    public static function buscarLotepedido()
    {
        return "SELECT  fr.idempresa as idempresafr,l.npartida, 
                        l.exercicio,
                        CONCAT(l.partida, '/', l.exercicio) AS partida,
                        DMA(l.fabricacao) AS dataf,
                        DMA(l.vencimento) AS datav,
                        l.criadoem as lcriadoem,
                        l.vencimento,
                        l.qtdpedida,
                        fr.qtd as qtddisp,
                        i.qtd,
                        l.status,
                        l.idlote,
                        fr.idlotefracao,
                        (select alteradopor from carrimbo c WHERE  c.tipoobjeto in ('lotealmoxarifado','lotecq','lotediagnostico','lotediagnosticoautogenas','loteproducao','loteretem') and status = 'ATIVO' and c.idobjeto = l.idlote limit 1) as idassinadopor,
                        l.idprodservformula,
                        l.idsolfab,
                        l.tipoobjetoprodpara,
                        l.idobjetoprodpara,
                        l.unpadrao,
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
                    FROM nfitem i, prodserv p, lotecons c, lote l, lotefracao fr
                    JOIN unidadeobjeto o on(o.tipoobjeto='modulo' 
                    AND o.idunidade = fr.idunidade)
                    JOIN " . _DBCARBON . "._modulo m on (m.modulo = o.idobjeto and m.modulotipo = 'lote' and m.status = 'ATIVO')	
                    JOIN unidade u on(u.idunidade=fr.idunidade )
                WHERE l.idlote = fr.idlote
                    AND fr.idlotefracao=c.idlotefracao
                    AND p.idprodserv = l.idprodserv
                    AND c.tipoobjeto='nfitem' 
                    AND (c.qtdd>0 or c.qtdc>0)
                    AND c.idobjeto =i.idnfitem
                    AND i.idnfitem = ?idnfitem?		 
                ORDER BY  exercicio ASC ,npartida ASC";
    }


    public static function buscarConsumoLotePedido (){
        return "SELECT 
                    idlotecons AS id, qtdd, qtdc, 'lotecons' AS tipo
                FROM
                    lotecons
                WHERE
                    idlote = ?idlote? AND idlotefracao = ?idlotefracao? and (qtdd > 0 or qtdc>0)
                        AND idobjeto = ?idnfitem?
                        AND tipoobjeto = 'nfitem'";
    }

    public static function buscarLoteReservaPedido()
    {
        return "SELECT 
                    c.qtd, i.idnf, i.idnfitem
                FROM
                    lotereserva c
                        JOIN
                    nfitem i ON (i.idnfitem = c.idobjeto)
                WHERE
                    c.idlote = ?idlote?
                        AND c.tipoobjeto = 'nfitem'
                        AND c.qtd > 0
                        AND c.status = 'PENDENTE'";
    }

    public static function buscarLoteReservaPorIdnfitem(){
        return "SELECT 
                    idlotereserva AS id,
                    qtd AS qtdd,
                    0 AS qtdc,
                    'lotereserva' AS tipo
                FROM
                    lotereserva
                WHERE
                    idlote = ?idlote? AND idobjeto = ?idnfitem?
                        AND tipoobjeto = 'nfitem'
                        AND status = 'PENDENTE'
                        AND idlotereserva NOT IN (?inidlotereserva?)";
    }

    public static function buscarRotuloFormula()
    {
        return "SELECT 
                    *
                FROM
                    prodservformularotulo
                WHERE
                    idprodservformula = ?idprodservformula? ";
    }

    public static function buscarCofinsPorNfitem()
    {
        return "SELECT 
                    *
                FROM
                    vwnfcofins
                WHERE
                    idnfitem =?idnfitem?";
    }

    public static function buscarPisPorNfitem()
    {
        return "SELECT 
                    *
                FROM
                    vwnfpis
                WHERE
                    idnfitem = ?idnfitem?";
    }

    public static function buscarFinnfePorNF()
    {
        return "SELECT 
                    p.finnfe
                FROM
                    nf n
                        JOIN
                    natop p ON (n.idnatop = p.idnatop)
                WHERE
                    n.idnf =?idnf?";
    }
    
    public static function buscarMoedaNF()
    {
        return "SELECT 
                    moeda
                FROM
                    nf
                WHERE
                    idnf = ?idnf? ";
    }
    public static function buscarInfEmpresaNF()
    {
        return "SELECT e.pedidoobs, e.uf, ci.descricaoicms FROM empresa e JOIN classificacaoicms ci ON ci.idempresa = e.idempresa AND ci.uf = e.uf WHERE e.idempresa = ?idempresa?";
    }

    public static function buscarFormapagamentoPorEmpresa()
    {
        return "SELECT 
                    idformapagamento, descricao
                FROM
                    formapagamento
                WHERE
                    status = 'ATIVO' AND credito = 'Y'
                        AND idempresa = ?idempresa?
                ORDER BY ord , descricao DESC";
    }
    
    public static function buscarConfpagarPorNF()
    {
        return "SELECT 
                        DMA(c.datareceb) AS dmadatareceb,
                        c.proporcao,
                        c.valorparcela,
                        c.idnfconfpagar,
                        c.obs,
                        c.alteradoem,
                        c.alteradopor
                    FROM
                        nfconfpagar c
                    WHERE
                        c.idnf = ?idnf?";
    }

    public static function retornaDiaSemanaPorData()
    {
        return "SELECT DAYOFWEEK('?vdata?') as diasemana";
    }

    public static function buscarStPorNF()
    {
        return "SELECT 
                        cst, piscst, confinscst
                    FROM
                        nfitem
                    WHERE
                        nfe = 'Y' AND idnf = ?idnf?";
    }

    public static function buscarNfcorrecaoPorIdnf()
    {
        return "SELECT 
                    *
                FROM
                    nfcorrecao
                WHERE
                    idnf = ?idnf?";
    }

    public static function buscarRotaUfPorTransportadora()
    {
        return "SELECT 
                    rp.prazoentrega, rp.obs
                FROM
                    rotaorigem ro
                        JOIN
                    empresa e ON e.codcidade = ro.codcidade
                        JOIN
                    rotapara rp ON rp.idrotaorigem = ro.idrotaorigem
                        JOIN
                    nfscidadesiaf c ON c.codcidade = rp.codcidade
                WHERE
                    rp.uf = '?uf?' AND rp.codcidade = '?codcidade?'
                        AND idpessoa = ?idpessoa?";
    }
    
    public static function buscarCtePedidoPorIdOBS()
    {
        return "select * from (
        SELECT 
                0 as idobjetovinculo,
                    i.cfop,
                    i.total,
                    n.idnf,
                    n.nnfe,
                    p.nome,
                    DMA(n.dtemissao) AS emissao,
                    n.status
                FROM
                    nfitem i,
                    nf n,
                    pessoa p
                WHERE
                    i.obs LIKE ('%?idnfe?%')
                        AND p.idpessoa = n.idpessoa
                        AND n.idnf = i.idnf 
                UNION SELECT 
                0 as idobjetovinculo,
                    i.cfop,
                    i.total,
                    n.idnf,
                    n.nnfe,
                    p.nome,
                    DMA(n.dtemissao) AS emissao,
                    n.status
                FROM
                    nfitem i,
                    nf n,
                    pessoa p
                WHERE
                    n.idobjetosolipor = ?idnf?
                        AND n.tipoobjetosolipor = 'nf'
                        AND n.tiponf = 'T'
                        AND p.idpessoa = n.idpessoa
                        AND n.idnf = i.idnf
                UNION
                SELECT 
                o.idobjetovinculo,
                        i.cfop,
                        i.total,
                        n.idnf,
                        n.nnfe,
                        p.nome,
                        DMA(n.dtemissao) AS emissao,
                        n.status
                    FROM
                        objetovinculo o
                            JOIN
                        nfitem i ON (o.idobjetovinc = i.idnf)
                            JOIN
                        nf n ON n.idnf = i.idnf
                            JOIN
                        pessoa p ON p.idpessoa = n.idpessoa
                    WHERE
                        o.tipoobjeto = 'nf'
                            AND o.tipoobjetovinc = 'cte'
                            AND o.idobjeto =  ?idnf? ) as u group by u.idnf";
    }

    public static function buscarCtePedidoPorId()
    {
            return "SELECT o.idobjetovinculo, 
                        i.cfop,
                        i.total,
                        n.idnf,
                        n.nnfe,
                        p.nome,
                        DMA(n.dtemissao) AS emissao,
                        n.status
                    FROM
                        objetovinculo o
                            JOIN
                        nfitem i ON (o.idobjetovinc = i.idnf)
                            JOIN
                        nf n ON n.idnf = i.idnf
                            JOIN
                        pessoa p ON p.idpessoa = n.idpessoa
                    WHERE
                        o.tipoobjeto = 'nf'
                            AND o.tipoobjetovinc = 'cte'
                            AND o.idobjeto =  ?idnf?";
    }

    public static function buscarNfVinculadaPorId()
    {
            return "SELECT o.idobjetovinculo, 
                        n.total,
                        n.idnf,
                        n.tiponf,
                        n.nnfe,
                        p.nome,
                        DMA(n.dtemissao) AS emissao,
                        n.status
                    FROM
                        objetovinculo o
                        JOIN
                        nf n ON n.idnf = o.idobjeto
                            JOIN
                        pessoa p ON p.idpessoa = n.idpessoa
                    WHERE
                        o.tipoobjeto = 'nf'
                            AND o.tipoobjeto= 'nf'
                             AND o.tipoobjetovinc in ('nf','cte')
                            AND o.idobjetovinc =  ?idnf?";
    }

   public static function buscarTotalCofinsNF()
   {
    return "SELECT 
                SUM(vCOFINS) AS cofins
            FROM
                vwnfcofins w
                    JOIN
                nfitem i ON (i.idnfitem = w.idnfitem AND i.nfe = 'Y')
            WHERE
                w.idnf = ?idnf?";
    }

    public static function buscarTotalPisNF()
    {
     return "SELECT 
                SUM(vPIS) AS vpis
            FROM
                vwnfpis w
                    JOIN
                nfitem i ON (i.idnfitem = w.idnfitem AND i.nfe = 'Y')
            WHERE
                w.idnf =?idnf?";
     }

     public static function buscarImpostosGNENf()
     {
        return "SELECT 
                    c.idcontapagar,
                    n.tiponf AS tiponota,
                    DMA(c.datareceb) AS datarecebimento,
                    c.status,
                    c.valor,
                    c.obs AS obscontapagar,
                    i.*
                FROM
                    nfitem i
                        JOIN
                    contapagar c ON (c.idobjeto = i.idnf
                        AND c.tipoobjeto = 'nf' and c.tipoespecifico!='REPRESENTACAO')
                        JOIN
                    nf n ON (n.idnf = i.idnf)
                WHERE
                    i.idobjetoitem = ?idnf?
                    AND i.tipoobjetoitem = 'nf'";
     }
     

     public static function buscarImpostosNf()
    {
        return "SELECT 
                    i.idcontapagaritem,
                    i.datapagto,
                    i.valor,
                    i.status AS status_item,
                    p.nome,
                    p.idpessoa,
                    c.idcontapagar,
                    c.datareceb,
                    c.status,
                    c.parcelas,
                    c.parcela,
                    f.descricao
                FROM
                    contapagar ci
                        JOIN
                    contapagaritem i ON (ci.idcontapagar = i.idobjetoorigem and i.ajuste='N'
                        AND i.tipoobjetoorigem = 'contapagar')
                         JOIN
                    contapagar c ON (c.idcontapagar = i.idcontapagar and c.tipoespecifico='IMPOSTO')
                    JOIN 
                    formapagamento f ON(c.idformapagamento=f.idformapagamento)
                        JOIN
                    pessoa p ON (p.idpessoa = i.idpessoa)
                WHERE
                    ci.idobjeto = ?idnf?
                        AND NOT EXISTS( SELECT 
                            1
                        FROM
                            contapagaritem ii
                        WHERE
                            ii.idcontapagar = ci.idcontapagar)
                        AND ci.tipoobjeto LIKE ('nf%') 
                UNION 
                    SELECT 
                        i.idcontapagaritem,
                        i.datapagto,
                        i.valor,
                        i.status AS status_item,
                        p.nome,
                        p.idpessoa,
                        c.idcontapagar,
                        c.datareceb,
                        c.status,
                        c.parcelas,
                        c.parcela,
                        f.descricao
                    FROM
                    
                        contapagaritem i 
                            JOIN
                        contapagar c ON (c.idcontapagar = i.idcontapagar and c.tipoespecifico='IMPOSTO')
                        JOIN 
                            formapagamento f ON(c.idformapagamento=f.idformapagamento)
                            JOIN
                        pessoa p ON (p.idpessoa = i.idpessoa)
                    WHERE
                        i.idobjetoorigem =?idnf?
                            AND i.tipoobjetoorigem LIKE 'nf%'
                   
                UNION
                SELECT 
                    i.idcontapagaritem,
                    i.datapagto,
                    i.valor,
                    i.status AS status_item,
                    p.nome,
                    p.idpessoa,
                    c.idcontapagar,
                    c.datareceb,
                    c.status,
                    c.parcelas,
                    c.parcela,
                    f.descricao
                FROM
                contapagaritem ci                       
                    JOIN contapagaritem i ON (ci.idcontapagar = i.idobjetoorigem  AND i.tipoobjetoorigem = 'contapagar' and i.ajuste='N')   
                    JOIN  contapagar cp ON (ci.idcontapagar = cp.idcontapagar and cp.tipoespecifico='AGRUPAMENTO')
					JOIN  contapagar c ON (c.idcontapagar = i.idcontapagar and c.tipoespecifico='IMPOSTO')
                    JOIN formapagamento f ON(c.idformapagamento=f.idformapagamento)
                    JOIN pessoa p ON (p.idpessoa = i.idpessoa)
                WHERE
                    ci.idobjetoorigem =?idnf?
                        AND ci.tipoobjetoorigem LIKE 'nf%'
                ORDER BY parcela";
    }


     public static function buscarContapagaritemPorNf()
     {
        return "SELECT 
                    a.boleto,
                    f.agrupado,
                    f.descricao,
                    f.agruppessoa,
                    f.agrupnota,
                    i.idcontapagar,
                    i.parcela,
                    i.parcelas,
                    i.idformapagamento,
                    i.valor,
                    'contapagaritem' AS tobj,
                    i.idcontapagaritem,
                    i.status
                FROM
                    contapagaritem i
                        JOIN
                    formapagamento f ON (f.idformapagamento = i.idformapagamento AND f.tipoespecifico !='IMPOSTO')
                        JOIN
                    agencia a ON (a.idagencia = f.idagencia)
                WHERE
                    i.idobjetoorigem = ?idnf?
                    AND i.status !='INATIVO'
                        -- AND i.ajuste = 'N'
                        AND i.datapagto != '0000-00-00'
                        AND i.tipoobjetoorigem LIKE 'nf%' 
                UNION SELECT 
                    a.boleto,
                    f.agrupado,
                    f.descricao,
                    f.agruppessoa,
                    f.agrupnota,
                    c.idcontapagar,
                    c.parcela,
                    c.parcelas,
                    c.idformapagamento,
                    c.valor,
                    'contapagar' AS tobj,
                    c.idcontapagar AS idcontapagaritem,
                    c.status
                FROM
                    contapagar c
                        JOIN
                    formapagamento f ON (f.idformapagamento = c.idformapagamento AND f.tipoespecifico !='IMPOSTO')
                        JOIN
                    agencia a ON (a.idagencia = c.idagencia)
                WHERE
                    c.idobjeto = ?idnf?
                        AND tipo = 'C'
                        AND c.status !='INATIVO'
                        AND NOT EXISTS( SELECT 
                            1
                        FROM
                            contapagaritem i
                        WHERE
                            i.idcontapagar = c.idcontapagar
                                -- AND i.ajuste = 'N'
                                AND i.tipoobjetoorigem = 'nf')
                        AND (c.tipoobjeto LIKE ('nf%')
                        OR c.tipoobjeto LIKE ('gnre'))
                ORDER BY parcela";
     }

     public static function buscarHistoricoStatusPedidoPorIdcontapagar()
     {
        return "SELECT 
                    n.idnf,
                    n.status,
                    cp.idcontapagar,
                    cp.idobjeto,
                    
                    cp.criadopor,
                    cp.criadoem AS criadoemcp,
                    fsh.*
                FROM
                    nf n
                        JOIN
                    contapagar cp ON cp.idobjeto = n.idnf
                        LEFT JOIN
                    (SELECT 
                        fsh.criadoem as criadoemfs ,fsh.idfluxostatus, fsh.idmodulo, fs.idfluxo, s.statustipo
                        FROM
                            fluxostatushist fsh
                        LEFT JOIN fluxostatus fs ON fs.idfluxostatus = fsh.idfluxostatus
                        JOIN " . _DBCARBON . "._status s ON s.idstatus = fs.idstatus AND s.statustipo = 'CONCLUIDO'
                        WHERE 
                            modulo = '?pagvalmodulo?' ) AS fsh ON fsh.idmodulo = n.idnf
                    WHERE
                        fsh.idmodulo = ?idnf?
                        AND cp.idcontapagar =?idcontapagar?
                            AND cp.criadoem > fsh.criadoemfs
                        limit 1";
     }

     public static function buscarConfPedidoFatPorIdnf()
     {
        return "SELECT 
                        e.uf,
                        p.inscrest,
                        indiedest,
                        p.idpessoa,
                        f.tpnf,
                        p.vendadireta,
                        f.idvendedor,
                        (SELECT 
                                1
                            FROM
                                natop nt
                            WHERE
                                nt.natop LIKE ('%DEVOLU%')
                                    AND nt.idnatop = f.idnatop) AS natopdev,
                        (SELECT 
                                1
                            FROM
                                natop nt
                            WHERE
                                nt.natop LIKE ('%OUTRA%')
                                    AND nt.natop LIKE ('%SAIDA%')
                                    AND nt.idnatop = f.idnatop) AS natoprem
                    FROM
                        nf f,
                        pessoa p,
                        endereco e
                    WHERE
                        p.idpessoa = f.idpessoafat
                            AND e.idendereco = f.idenderecofat
                            AND f.idnf = ?idnf?";
     }

     public static function atualizaCollapseNfitem()
     {
        return "UPDATE nfitem 
                    SET 
                        collapse = '?tipo?'
                    WHERE
                        idnf =?idnf?";
     }

     public static function buscarLotesNaoConsumidosPedido()
     {
        return "SELECT 
                    l.status, c.idlotecons
                FROM
                    nfitem i
                        JOIN
                    lotecons c ON (i.idnfitem = c.idobjeto
                        AND (c.qtdd = 0 OR c.qtdd IS NULL)
                        AND (c.qtdc = 0 OR c.qtdc IS NULL)
                        AND c.tipoobjeto = 'nfitem')
                        JOIN
                    lote l ON (c.idlote = l.idlote
                        AND l.status = 'ESGOTADO')
                WHERE
                    i.idnf = ?idnf?";
     }

     public static function atualizaInfCorrecaoNF()
     {
        return "UPDATE nf 
                    SET 
                        infcorrecao = CONCAT(IFNULL(infcorrecao, ''),'?correcao?')
                    WHERE
                        idnf =?idnf?";
     }

     public static function buscarInfCteNF()
     {
        return "SELECT 
                    n.frete, p.nome, n.dtemissao, total, idnfe, tiponf,n.idpessoa
                FROM
                    nf n
                        JOIN
                    pessoa p ON (p.idpessoa = n.idpessoa)
                WHERE
                    n.idnf = ?idnf?";
     }

     public static function buscarConfpagarComissao()
     {
        /*
       return "SELECT 
                    *
                FROM
                    confcontapagar
                WHERE
                    status = 'ATIVO' AND tipo = 'COMISSAO'
                        AND idformapagamento = ?idformapagamento?";
        */
        return "SELECT 
                        *
                    FROM
                        formapagamento
                    WHERE
                        status = 'ATIVO' 
                        AND tipoespecifico in('REPRESENTACAO','IMPOSTO')
                        AND idformapagamento = ?idformapagamento?";
     }

     public static function atualizaDataEntregaCte()
     {
        return "UPDATE nf n
                        JOIN
                    nfitem ni ON n.idnf = ni.idnf 
                SET 
                    entrega = '?entrega?'
                WHERE
                    ni.obs LIKE ('%?idnfepedido?%')";
     }

     public static function atualizaDataEntregaCtePorIdnf()
     {
        return "UPDATE nf n 
                    SET 
                        entrega = '?entrega?'
                    WHERE
                        n.idnfe LIKE ('%?idnfepedido?%')";
     }


     public static function buscarDataVencimentoNoMes()
     {
        return "SELECT ('?dtemissao?' + INTERVAL ?diavenc? DAY) AS dataitem";
     }

     public static function buscarDataVencimentoNoMesSequinte()
     {
        return "SELECT (LAST_DAY('?dtemissao?') + INTERVAL ?diavenc? DAY) AS dataitem";
     }

     public static function verificarSeExisteBoletoPorIdnf ()
     {
         return "SELECT 
                     COUNT(*) AS quant
                 FROM
                     remessaitem i,
                     remessa r,
                     contapagar c
                 WHERE
                     i.idremessa = r.idremessa
                         AND i.idcontapagar = c.idcontapagar
                         AND c.tipoobjeto = 'nf'
                         AND c.idobjeto = ?idnf?";
     }

     public static function buscarComissaoPorIdpessaoIdnf()
     {
        return "SELECT 
                    c.idpessoa,
                    ROUND(SUM(n.total * (c.pcomissao / 100)), 2) AS comissao
                FROM
                    nfitem n
                        JOIN
                    nfitemcomissao c ON (c.idnfitem = n.idnfitem)
                        JOIN
                    pessoa p ON p.idpessoa = c.idpessoa
                        AND (p.status IN ('PENDENTE', 'ATIVO') OR (p.status='INATIVO' && p.comissaoinativo = 'Y'))
                WHERE
                    n.idnf = ?idnf? AND n.nfe = 'Y'
                GROUP BY c.idpessoa";
     }

     public static function deletaLoteconsPorIdNF()
     {
         return "DELETE l.* FROM nfitem i,
                        lotecons l 
                    WHERE
                        l.idobjeto = i.idnfitem
                        AND l.tipoobjeto = 'nfitem'
                        AND i.idnf = ?idnf?";
     }

     public static function deletaLotereservaPorIdNF()
     {
         return "DELETE l.* FROM nfitem i,
                        lotereserva l 
                    WHERE
                        l.idobjeto = i.idnfitem
                        AND l.tipoobjeto = 'nfitem'
                        AND i.idnf =?idnf? ";
     }

     public static function verificarnftransf()
     {
         return "SELECT 
                    natoptipo
                FROM
                    nf n
                        JOIN
                    natop o ON (o.idnatop = n.idnatop
                        AND natoptipo = 'transferencia')
                WHERE
                    n.idnf = ?idnf? ";
     }

     public static function buscarParcelasSemComissao(){
        return "SELECT * FROM (SELECT i.idcontapagar,
                                      CONCAT(i.parcela, ' de ', i.parcelas) AS parcelas,
                                      i.idformapagamento,
                                      i.valor,
                                      i.datapagto
                                 FROM contapagaritem i 
                                WHERE i.idobjetoorigem = ?idnf?
                                  AND i.status != 'INATIVO'
                                  AND i.datapagto != '0000-00-00'
                                  AND i.tipoobjetoorigem LIKE 'nf%' 
                             UNION 
                               SELECT c.idcontapagar,
                                      CONCAT(c.parcela, ' de ', c.parcelas) AS parcelas,
                                      c.idformapagamento,
                                      c.valor,
                                      c.datapagto
                                 FROM contapagar c
                                WHERE c.idobjeto = ?idnf?
                                  AND tipo = 'C'
                                  AND c.status !='INATIVO'
                                  AND NOT EXISTS( SELECT 1 FROM contapagaritem i 
                                                   WHERE i.idcontapagar = c.idcontapagar 
                                                     AND i.tipoobjetoorigem = 'nf') 
                                                     AND (c.tipoobjeto LIKE ('nf%') OR c.tipoobjeto LIKE ('gnre'))) as u
                         WHERE NOT EXISTS (SELECT 1 FROM contapagaritem ci JOIN contapagaritem i ON ci.idcontapagar = i.idobjetoorigem AND i.tipoobjetoorigem = 'contapagar'
                                             JOIN contapagar c ON c.idcontapagar = i.idcontapagar and c.tipoespecifico = 'REPRESENTACAO'
                                            WHERE ci.idobjetoorigem = ?idnf?
                                              AND ci.tipoobjetoorigem LIKE 'nf%'
                                              AND u.idcontapagar = ci.idcontapagar)
                           AND NOT EXISTS (SELECT 1 FROM contapagar ci JOIN contapagaritem i ON ci.idcontapagar = i.idobjetoorigem AND i.tipoobjetoorigem = 'contapagar'
                                             JOIN contapagar c ON c.idcontapagar = i.idcontapagar and c.tipoespecifico = 'REPRESENTACAO'
                                             WHERE ci.idcontapagar = u.idcontapagar)
                        ORDER BY parcelas";
     }

     public static function buscarFinalidadeNatop(){
         return "SELECT 
                    op.finnfe
                FROM
                    nf n
                        JOIN
                    natop op ON op.idnatop = n.idnatop
                WHERE
                    n.idnf = ?idnf?";

     }

     public static function buscarvalorCtePedido(){
        return "SELECT 
                        ROUND(((total / totalpedido) * cte), 2) AS valorcalc,
                        ROUND((total / totalpedido) * 100, 2) AS percentual,
                        cte AS totalcte,
                        total,
                        totalpedido
                    FROM
                        (SELECT 
                            c.total AS cte,
                                p.idnf,
                                p.total,
                                (SELECT 
                                        SUM(pp.total)
                                    FROM
                                        objetovinculo oo
                                    JOIN nf pp ON (pp.idnf = oo.idobjeto)
                                    WHERE
                                        oo.tipoobjeto = 'nf'
                                            AND oo.tipoobjetovinc = 'cte'
                                            AND oo.idobjetovinc = c.idnf) totalpedido
                        FROM
                            objetovinculo o
                           JOIN nf c ON (c.idnf = o.idobjetovinc and c.status!='CANCELADO')
                            JOIN nf p ON (p.idnf = o.idobjeto and p.status!='CANCELADO')
                        WHERE
                            o.tipoobjeto = 'nf'
                                AND o.tipoobjetovinc = 'cte'
                                AND o.idobjetovinc = ?idnfcte?
                                AND o.idobjeto = ?idnf?) AS u;";
     }

     public static function BuscarConsumoNfitemSelecionadosPorIdNf(){
        return"SELECT idobjeto
                    FROM lotecons
                    WHERE qtdd > 0
                    AND idobjeto IN (SELECT idnfitem
                                    FROM nfitem 
                                    WHERE idnf = ?idnf?)
                    AND tipoobjeto = 'nfitem'
                    UNION
                    SELECT 1
                    FROM lotereserva
                    WHERE qtd > 0
                    AND idobjeto IN ((SELECT idnfitem
                                        FROM nfitem
                                        WHERE idnf = ?idnf?))
                    AND tipoobjeto = 'nfitem'
                    AND status = 'PENDENTE';";
    }
}
?>
