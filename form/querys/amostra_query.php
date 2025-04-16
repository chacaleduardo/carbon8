<?
require_once(__DIR__."/_iquery.php");

class AmostraQuery implements DefaultQuery{

	public static $table = "amostra";
	public static $pk = "idamostra";

	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ["table" => self::$table,"pk"=>self::$pk]) ;
	}

    public static function buscarInfoEtiquetaAmostra(){
        return "SELECT
                    IF(a.status = 'PROVISORIO',CONCAT('P-',LPAD(a.idregistro, 5, '0')),a.idregistro) AS idregistro,
                    r.quantidade,
                    a.estexterno,
                    CONCAT('P-',LPAD(a.idregistroprovisorio, 5, '0')) AS idregistroprovisorio,
                    IF(LENGTH(ps.codprodserv)>22,CONCAT(LEFT(ps.codprodserv,19),'...'),ps.codprodserv) AS codprodserv,
                    IF(LENGTH(a.localcoleta)>30,CONCAT(LEFT(a.localcoleta,27),'...'),a.localcoleta) AS localcoleta,
                    IF(LENGTH(a.lacre)>30,CONCAT(LEFT(a.lacre,27),'...'),a.lacre) AS lacre,
                    r.idsecretaria,
                    IFNULL(a.nucleoamostra,n.nucleo) AS nucleo,
                    a.lote AS lote,	
                    LEFT(a.galpao,7) AS galpao,	
                    IF(LENGTH(s.subtipoamostra) >26,
                    CONCAT(LEFT(s.subtipoamostra,23),'...') ,
                    s.subtipoamostra) AS tipoamostra,
                    (SELECT COUNT(*) FROM impetiqueta ee WHERE ee.idresultado=r.idresultado) + 1 AS versao		
                FROM (amostra a,resultado r,prodserv ps,subtipoamostra s)
                    LEFT JOIN nucleo n ON n.idnucleo = a.idnucleo
                WHERE s.idsubtipoamostra = a.idsubtipoamostra			
                    AND ps.idprodserv=r.idtipoteste
                    AND r.impetiqueta='Y'
                    AND r.idamostra = a.idamostra
                    AND r.status not in ('CANCELADO','OFFLINE')
                    AND a.idamostra = ?idamostra?
                    AND IFNULL(r.loteetiqueta,999999) = ?idloteetiqueta?";
    }

    
    public static function buscarDadosResultadosAmostra(){
        return "SELECT 
                    a.idregistro,
                    a.exercicio,
                    r.idresultado,
                    p.descr,
                    r.alerta,
                    s.subtipoamostra,
                    r.status,
                    p.alertarotuloy,
                    p.alertarotulon,
                    p.alertarotulo,
                    rj.jresultado,
                    CASE
                        WHEN t.tiporelatorio LIKE '%antibiograma%' THEN 'Y'
                        ELSE 'N'
                    END AS 'relatorioantibiograma'
                FROM
                    amostra a
                        JOIN
                    subtipoamostra s ON (a.idsubtipoamostra = s.idsubtipoamostra)
                        JOIN
                    resultado r ON (a.idamostra = r.idamostra)
                        JOIN
                    prodserv p ON (p.idprodserv = r.idtipoteste)
                        LEFT JOIN
                    resultadojson rj ON (rj.idresultado = r.idresultado)
                        LEFT JOIN
                    prodservtiporelatorio pt ON pt.idprodserv = r.idtipoteste
                        LEFT JOIN
                    tiporelatorio t ON (pt.idtiporelatorio = t.idtiporelatorio)
                WHERE
                    a.?colIdAmostra? = ?idamostra?
                    AND EXISTS( SELECT 
                        *
                    FROM
                        prodservtiporelatorio pt
                    WHERE
                        pt.idprodserv = p.idprodserv
                            AND pt.idtiporelatorio = 65)
                AND r.status <> 'CANCELADO'
                GROUP BY r.idresultado
                ORDER BY a.idregistro , p.descr";
    }


    public static function buscarDadosAmostra(){
        return "SELECT 
                a.*,
                concat(a.dataamostra,' ',time(a.criadoem)) as dataamostrah,                    
                sta.subtipoamostra AS subtipoamostra,
                p.centrocusto,
                ifnull(e.nomepropriedade,p.nome) as nome,
                n.idnucleo,
                n.nucleo,
                p.razaosocial,
                (SELECT CONCAT(IFNULL(en.logradouro, ''),
                        ' ',IFNULL(en.endereco, ''),
                        ', ',IFNULL(en.numero, ''),
                        ', ',IF((IFNULL(en.complemento, '') <> ''),CONCAT(IFNULL(en.complemento, ''), ', '),''),
                        IFNULL(en.bairro, ''),
                        ' - ',CONCAT(SUBSTR(en.cep, 1, 5),'-',SUBSTR(en.cep, 6, 3)),
                        ' - ',IFNULL(cs.cidade, ''),
                        '/',IFNULL(en.uf, ''))
                    FROM endereco en
                        LEFT JOIN nfscidadesiaf cs ON cs.codcidade = en.codcidade
                    WHERE en.status = 'ATIVO'
                            AND en.idpessoa = a.idpessoa
                            AND en.idtipoendereco = 6
                ) AS enderecosacado,
                e.cnpjend AS cpfcnpj,
                e.inscest AS inscrest,
                ef.especietipofinalidade AS especietipofinalidade,
                ef.especiefinalidade AS especiefinalidade,
                ef.tipoespeciefinalidade AS tipoespeciefinalidade,
                da.valorobjeto AS valorobjeto
                
            FROM
                amostra a
                JOIN pessoa p on p.idpessoa = a.idpessoa
                LEFT JOIN dadosamostra da on da.idamostra=a.idamostra				
                JOIN subtipoamostra sta on sta.idsubtipoamostra = a.idsubtipoamostra
                LEFT JOIN nucleo n ON a.idnucleo = n.idnucleo
                LEFT JOIN vwespeciefinalidade ef ON ef.idespeciefinalidade = a.idespeciefinalidade
                left join endereco e on 
                    (e.status = 'ATIVO'
                    AND e.idpessoa = a.idpessoa
                    AND e.idtipoendereco = 6)
            WHERE
                a.idamostra = ?idamostra?";
    }


    public static function buscarDadosCabecalhoReportAmostra(){
        return "SELECT 
                    a.idregistro,
                    a.exercicio,
                    a.lote,
                    a.dataamostra,
                    a.descricao,
                    a.observacao,
                    a.idunidade,
                    ov.idobjetovinc AS idobjetosolipor,
                    ov.tipoobjetovinc AS tipoobjetosolipor,
                    ps.nome,
                    p.codprodserv,
                    p.descr,
                    r.quantidade,
                    p.textoinclusaores,
                    p.textopadrao,
                    r.status,
                    r.idamostra,
                    r.idresultado,
                    sta.subtipoamostra,
                    l.idlote,
                    a.partida as partidaamostra,
                    l.partida,
                    l.spartida,
                    l.npartida,
                    l.piloto, 
                    l.partidaext,
                    CONVERT(LPAD(REPLACE(l.partida,l.spartida,''),'3', '0') USING latin1) AS num_partida
                FROM
                    amostra a
                        JOIN
                    pessoa ps ON a.idpessoa = ps.idpessoa
                        JOIN
                    resultado r ON r.idamostra = a.idamostra
                        LEFT JOIN
                    objetovinculo ov ON ov.idobjeto = r.idresultado
                        AND ov.tipoobjeto = 'resultado'
                        JOIN
                    prodserv p ON p.idprodserv = r.idtipoteste
                        JOIN
                    subtipoamostra sta ON a.idsubtipoamostra = sta.idsubtipoamostra
                        left join
                    loteativ la on la.idloteativ = ov.idobjetovinc and ov.tipoobjetovinc = 'loteativ'
                        left join
                    lote l on l.idlote = la.idlote
                
                WHERE
                    a.idamostra = ?idamostra?
                    AND r.status not in ('CANCELADO','OFFLINE')";
    }

    public static function buscarEmpresaAmostra (){
        return "SELECT 
                    idempresa
                FROM
                    amostra
                WHERE
                    idamostra = ?idamostra?";
    }

    public static function buscarDatasAmostra ()
    {
        return "SELECT DATE_FORMAT(dataamostra, '%d/%m/%Y') AS dataamostra,
                       idregistro,
                       exercicio,
                       CONCAT(dataamostra, ' ', IFNULL(horaamostra, TIME(criadoem))) AS dataamostrah
                  FROM amostra 
                 WHERE idamostra = ?idamostra?";
    }

    public static function buscarDadosAmostraCabecalhoModuloResultados(){
        return"SELECT 
                a.idamostra,
                a.idempresa,
                a.idregistro,
                a.idunidade,
                a.dataamostra,
                IFNULL(p.nomecurto, p.nome) AS nome,
                p.idpessoa,
                sta.subtipoamostra,
                a.idade,
                a.tipoidade,
                a.exercicio,
                a.sexo,
                a.idespeciefinalidade
            FROM
                amostra a
                    LEFT JOIN
                pessoa p ON a.idpessoa = p.idpessoa
                    LEFT JOIN
                subtipoamostra sta ON a.idsubtipoamostra = sta.idsubtipoamostra
            WHERE
                a.idamostra = ?idamostra?";
    }
    
    public static function contarResultadosAssinados (){
        return "SELECT count(r.status) AS assinado
                FROM laudo.amostra as a
                    JOIN resultado as r on (r.idamostra=a.idamostra)
                WHERE a.idamostra=?idamostra? and r.status = 'ASSINADO'";
    }
    
    public static function buscarTestesDaAmostra (){
        return "SELECT
                    r.idresultado,
                    if(r.versao > 0, concat('[v',r.versao,']'),'') as versao,
                    p.idprodserv,
                    p.descr,
                    p.codprodserv,
                    r.quantidade,
                    r.npedido,
                    r.status,
                    p.logoinmetro,
                    r.criadopor,
                    dmahms(r.criadoem) criadoem,
                    r.alteradopor,
                    dmahms(r.alteradoem) alteradoem,
                    r.idlp,
                    r.idsecretaria,
                    r.impetiqueta,
                    r.ord,
                    r.loteetiqueta,
                    r.cobrar,
                    r.cobrancaobrig,
                    (
                        select count(*) as iagentes
                        from notafiscalitens ni
                        where ni.idresultado = r.idresultado
                    ) apagavel,
                    (
                        select count(*) as iagentes
                        from lote l
                        where l.tipoobjetosolipor='resultado' and l.idobjetosolipor=r.idresultado
                    ) iagentes
                FROM
                    resultado r 
                    LEFT JOIN prodserv p on (r.idtipoteste = p.idprodserv)
                WHERE  r.idamostra = ?idamostra?
                    and r.status not in ('OFFLINE','CANCELADO')
                order by r.ord";
    }
    
    public static function buscarLogDeReabertura (){
        return "SELECT 
                    motivo,motivoobs, DATE_FORMAT(criadoem, '%d/%m/%Y %H:%m:%s') as criadoem, criadopor 
                FROM 
                    fluxostatushistobs
                WHERE 
                    idmodulo = ?idamostra?  and modulo = '?modulo?'
                UNION
                SELECT 
                    a.valor as motivo, b.valor as motivoobs, DATE_FORMAT(a.criadoem, '%d/%m/%Y %H:%m:%s') as criadoem, a.criadopor 
                FROM 
                    _auditoria a 
                join
                    _auditoria b on a.objeto = b.objeto and a.idobjeto = b.idobjeto and a.criadoem = b.criadoem
                WHERE 
                    a.objeto = 'amostra'  and a.idobjeto =  ?idamostra?
                    and a.coluna = 'edicaomotivo' and b.coluna = 'edicaoobs'
                order by criadoem asc";
    }

    public static function buscarUltimaAmostra (){
        return "SELECT a.idamostra,
                        a.dataamostra,
                        dma(a.dataamostra) as dataamostrabr,
                        a.idregistro
                FROM amostra a
                WHERE a.status in ('ABERTO','FECHADO','PROVISORIO')
                    AND a.idunidade=?idunidade?
                ORDER BY a.idamostra desc
                LIMIT 1";
    }

    public static function buscarSolfab (){
        return "SELECT s.idsolfab,
                        sf.idlote 
                FROM amostra a
                    JOIN resultado r on(a.idamostra=  r.idamostra and r.status not in ('CANCELADO','OFFLINE'))
                    JOIN lote l on(r.idresultado = l.idobjetosolipor)
                    JOIN solfabitem s on(l.idlote = s.idobjeto)
                    JOIN solfab sf on(s.idsolfab = sf.idsolfab)
                WHERE a.idamostra= ?idamostra? GROUP BY s.idsolfab";
    }

    public static function buscarInformacoesTRAAssocioado (){
        return "SELECT  l.partida,
                        l.exercicio,
                        l.idlote,
                        l.status,
                        o.idobjeto,
                        fr.status as statusfr
                FROM amostra a,resultado r,prodserv p,lote l,lotefracao fr
                        left join unidadeobjeto o on(o.tipoobjeto='modulo' and o.idunidade = fr.idunidade)	
                        join carbonnovo._modulo m on (m.modulo = o.idobjeto and m.ready='FILTROS' and m.modulotipo = 'lote')
                WHERE r.idamostra = a.idamostra
                    and fr.idlote = l.idlote
                    and p.idprodserv = r.idtipoteste
                    and  l.tipoobjetosolipor='resultado' 
                    and l.idobjetosolipor=r.idresultado	
                    and r.status not in ('CANCELADO','OFFLINE')
                    and a.idamostra = ?idamostra?
                GROUP BY l.idlote
                ORDER BY l.partida,l.exercicio";
    }

    public static function buscarAmostraComSubtipo (){
        return "SELECT a.idamostra,
                        a.idregistro,
                        a.exercicio,
                        s.subtipoamostra
                from amostra a left join subtipoamostra s on (a.idsubtipoamostra =s.idsubtipoamostra)
                where a.idamostratra = ?idamostra?
                order by a.idregistro,a.exercicio";
    }

    public static function buscarTRAAmostra (){
        return "SELECT r.idresultado,
                        r.status,
                        p.codprodserv 
                FROM resultado r,prodserv p,resultadoamostralad ra
                WHERE r.idresultado=ra.idresultado
                    and ra.idamostra = ?idamostra?
                    and p.idprodserv = r.idtipoteste
                    and r.status not in ('CANCELADO','OFFLINE')
                order by p.codprodserv";
    }

    public static function buscarLoteAtiv (){
        return "SELECT DISTINCT(idobjetovinc)
                FROM amostra a
                    JOIN resultado r ON a.idamostra = r.idamostra
                    JOIN objetovinculo ov ON ov.idobjeto = r.idresultado AND ov.tipoobjeto = 'resultado' AND tipoobjetovinc = 'loteativ'
                WHERE r.idamostra = '?idamostra?' and r.status not in ('CANCELADO','OFFLINE')";
    }

    public static function buscarTRAVinculado (){
        return "SELECT a.idamostra
                FROM amostra a 
                WHERE a.idamostratra = ?idamostra?";
    }

    public static function buscarArquivosAmostra (){
        return "SELECT a.*,
                        dmahms(criadoem) as datacriacao 
                FROM arquivo a 
                WHERE 
                    a.tipoobjeto = 'amostra' 
                    AND a.idobjeto = ?idamostra?
                    AND tipoarquivo = 'ANEXO' 
                ORDER BY idarquivo ASC";
    }

    public static function buscarCorpoConferenciaAmostra(){
        return "SELECT a.idamostra
                FROM amostra a
                    JOIN pessoa p ON (p.idpessoa = a.idpessoa)
                WHERE a.status <> 'PROVISORIO'
                    and a.idunidade = 1
                    ?clausula?
                        AND EXISTS(SELECT 1
                                    FROM resultado r JOIN prodserv pp ON pp.idprodserv = r.idtipoteste
                                    WHERE r.idamostra = a.idamostra
                                    AND r.status NOT IN ('ASSINADO', 'OFFLINE', 'CANCELADO')
                                    AND pp.conferencia = 'Y' ?clausular?)
                        AND ?exists? (SELECT 1
                                            FROM carrimbo c
                                        WHERE c.idobjeto = a.idamostra
                                            AND c.tipoobjeto = 'amostraaves'
                                            AND c.status = 'CONFERIDO')";
    }

    public static function buscarConfResultadoPorIdamostra(){
        return "SELECT 
                    r.idresultado,
                    r.*,
                    a.*,
                    r.status as statusresult,
                    concat(a.dataamostra,' ',time(a.criadoem)) as dataamostrah,
                    sta.subtipoamostra AS subtipoamostra,
                    p.centrocusto,
                    p.nome,
                    n.idnucleo,
                    n.nucleo,
                    p.razaosocial,
                    (SELECT CONCAT(IFNULL(en.logradouro, ''),
                            ' ',IFNULL(en.endereco, ''),
                            ', ',IFNULL(en.numero, ''),
                            ', ',IF((IFNULL(en.complemento, '') <> ''),CONCAT(IFNULL(en.complemento, ''), ', '),''),
                            IFNULL(en.bairro, ''),
                            ' - ',CONCAT(SUBSTR(en.cep, 1, 5),'-',SUBSTR(en.cep, 6, 3)),
                            ' - ',IFNULL(cs.cidade, ''),
                            '/',IFNULL(en.uf, ''))
                        FROM endereco en
                            LEFT JOIN nfscidadesiaf cs force index (codcidade) ON cs.codcidade = en.codcidade
                    WHERE  en.status = 'ATIVO'
                                AND en.idpessoa = a.idpessoa
                                AND en.idtipoendereco = 2	
                        ) AS enderecosacado,
                    p.cpfcnpj AS cpfcnpj,
                    p.inscrest AS inscrest,
                    ef.especietipofinalidade AS especietipofinalidade,
                    ef.especiefinalidade AS especiefinalidade,
                    ef.tipoespeciefinalidade AS tipoespeciefinalidade,
                    ps.codprodserv,
                    ps.descr,
                    ps.tipoespecial,
                    ra.idpessoa as idassinadopor,
                    ra.criadoem as ascriadoem,
                    s.nome as secretaria,
                    r.npedido
                FROM amostra a
                        JOIN resultado r on ( r.idamostra = a.idamostra)
                        JOIN pessoa p on p.idpessoa = a.idpessoa				
                            JOIN subtipoamostra sta on sta.idsubtipoamostra = a.idsubtipoamostra
                            LEFT JOIN nucleo n ON a.idnucleo = n.idnucleo
                            LEFT JOIN vwespeciefinalidade ef ON ef.idespeciefinalidade = a.idespeciefinalidade
                    JOIN prodserv ps on (ps.idprodserv=r.idtipoteste 
                    )
                    LEFT JOIN resultadoassinatura ra on(ra.idresultado = r.idresultado)
                    LEFT JOIN pessoa s on(s.idpessoa=r.idsecretaria)
                WHERE
                    a.idamostra=  ?idamostra?
                    and r.status not in ('CANCELADO','OFFLINE')
                ORDER BY a.idregistro";
    }

    public static function solicitarAssinaturaAmostra (){
        return "UPDATE amostra set status='ASSINAR' where idamostra = ?idamostra?";
    }

    public static function atualizarCampoDaAmostra (){
        return "UPDATE amostra set ?campo? = '?valorcampo?' WHERE idamostra = ?idamostra?";
    }

    public static function atualizarStatuseFluxoAmostra (){
        return "UPDATE amostra 
            SET status = 'CANCELADO', 
                idfluxostatus = '?idfluxostatus?' 
            WHERE idamostra = '?idamostra?'";
    }

    public static function buscarAmostraEPreferencia(){
        return "SELECT a.idregistro,
                        a.idpessoa,
                        p.idpreferencia,
                        pref.observacaore
                FROM amostra a
                    JOIN pessoa p ON (a.idpessoa = p.idpessoa)
                    LEFT JOIN preferencia pref ON (p.idpreferencia = pref.idpreferencia)
                WHERE a.idamostra = ?idamostra? ";
    }

    public static function buscarCorpoTransfereciaAmostra(){
        return "SELECT a.exercicio,
                        a.idamostra,
                        a.idregistro,
                        a.idpessoa,
                        a.idunidade,
                        pe.nome,
                        GROUP_CONCAT(p.descr SEPARATOR ' - ') as teste
                FROM amostra a
                    LEFT JOIN resultado r on (a.idamostra = r.idamostra)
                    LEFT JOIN prodserv p on (r.idtipoteste = p.idprodserv)
                    LEFT JOIN pessoa pe on (a.idpessoa = pe.idpessoa)
                WHERE a.status = 'PROVISORIO'
                    ?clausula?
                    and a.idunidade = ?idunidadepadrao?
                    and a.idempresa = ?idempresa?
                    and r.status not in ('CANCELADO','OFFLINE')
                GROUP BY  a.idamostra					
                ORDER BY a.exercicio, a.idregistro";
    }

    public static function buscarAmostrasPorNucleo(){
        return "SELECT
                    idamostra,
                    exercicio,
                    idregistro,
                    lote,
                    idunidade 
                from amostra 
                where  idnucleo = ?idnucleo?
                order by exercicio, idamostra";
    }

    public static function buscarNucleosComparativo(){
        return "SELECT * from (SELECT n.idnucleo
                    ,a.idpessoa
                    ,n.nucleo
                    ,n.lote
                    ,YEAR(n.alojamento) as ano
                    ,n.situacao
                    ,n.tipoaves
                    ,a.idunidade
                    ,CAST(a.idade as UNSIGNED) as idade
                    ,r.idservicoensaio
                    ,ps.idprodserv
                    ,ps.tipoespecial
                    ,ps.descr
                    ,r.idresultado
                    ,CASE 
                        WHEN tipoespecial = 'ELISA' THEN (select re.titer from resultadoelisa re where re.idresultado=r.idresultado and re.nome = 'GMN' and re.status='A')
                        ELSE r.gmt
                    END as gmt
                    ,(select group_concat(nv.vacina SEPARATOR ' / ') from nucleovacina nv where n.idnucleo = nv.idnucleo AND nv.datavacina = CAST(a.idade as UNSIGNED)) as vacina
                from amostra a FORCE INDEX(pessoa_nucleo)
                    join nucleo n FORCE INDEX(PRIMARY) on (n.idnucleo = a.idnucleo)
                    join resultado r FORCE INDEX(idamostra) on (r.idamostra = a.idamostra and r.status not in ('CANCELADO','OFFLINE'))
                    join prodserv ps FORCE INDEX(PRIMARY) on (
                        ps.idprodserv = r.idtipoteste 
                        AND ps.tipo='SERVICO' 
                        AND ps.tipoespecial in ('PESAGEM','GUMBORO','BRONQUITE','NEWCASTLE','PNEUMOVIRUS','REOVIRUS','ELISA','GUMBORO IND','BRONQUITE IND','NEWCASTLE IND','PNEUMOVIRUS IND','REOVIRUS IND')
                    )
                where a.idempresa = ?idempresa?
                    and a.idpessoa in (?idpessoa?)
                    and CAST(a.idade as UNSIGNED) > ''
                    UNION 
                SELECT n.idnucleo
                    ,a.idpessoa
                    ,n.nucleo
                    ,n.lote
                    ,YEAR(n.alojamento) as ano
                    ,n.situacao
                    ,n.tipoaves
                    ,a.idunidade
                    ,CAST(nv.datavacina as UNSIGNED) as idade
                    ,r.idservicoensaio
                    ,ps.idprodserv
                    ,ps.tipoespecial
                    ,ps.descr
                    ,r.idresultado
                    ,'0' as gmt
                    ,(nv.vacina) as vacina
                from 
                nucleovacina nv
               -- amostra a FORCE INDEX(pessoa_nucleo)
                    join nucleo n FORCE INDEX(PRIMARY) on (n.idnucleo = nv.idnucleo)
                    left join amostra a on (a.idnucleo = n.idnucleo)
                    join resultado r FORCE INDEX(idamostra) on (r.idamostra = a.idamostra and r.status not in ('CANCELADO','OFFLINE'))
                    join prodserv ps FORCE INDEX(PRIMARY) on (
                        ps.idprodserv = r.idtipoteste 
                        AND ps.tipo='SERVICO' 
                        AND ps.tipoespecial in ('PESAGEM','GUMBORO','BRONQUITE','NEWCASTLE','PNEUMOVIRUS','REOVIRUS','ELISA','GUMBORO IND','BRONQUITE IND','NEWCASTLE IND','PNEUMOVIRUS IND','REOVIRUS IND')
                    )
                where n.idempresa = ?idempresa?
                    and n.idpessoa in (?idpessoa?)
                    and CAST(nv.datavacina as UNSIGNED) > ''
                    ) a
                    order by ano desc,nucleo, descr asc,idade, gmt asc;";
    }

    public static function buscarConsultaLanagro(){
        return "SELECT a.idamostra,
                        a.idpessoa,
                        r.idresultado,
                        r.descritivo,
                        r.idsecretaria,
                        concat(a.nucleoamostra,' ',a.lote) as nucleo,
                        a.exercicio,
                        a.idregistro,
                        a.tc,
                        dma(r.alteradoem) as alteradoem,
                        MONTHNAME(r.alteradoem) as mes,
                        pos.*,
                        if(pos.tipoave>'',pos.tipoave,ef.tipoavelanagro)as tipoavenovo,
                        if(pos.tipoexploracao>'',pos.tipoexploracao,
                            if(ef.finalidade='Corte','CORTE',
                                if(ef.finalidade='Postura','POSTURA',ef.tipoespecie)
                            )
                        ) as tipoexploracao,
                        sta.subtipoamostra as tiposubtipoamostra,
                        concat(a.idade,'-',a.tipoidade) as idadetipo,
                        concat(ef.tipoespecie,' ',ef.finalidade) as espfin,
                        ef.tipoespecie,
                        ef.finalidade
                from prodserv p
                        join resultado r
                        join amostra a
                        join plpositivo pos
                        left join especiefinalidade ef on ef.idespeciefinalidade=a.idespeciefinalidade
                        left join subtipoamostra sta on sta.idsubtipoamostra=a.idsubtipoamostra
                where  pos.idresultado = r.idresultado
                    and r.idtipoteste =p.idprodserv
                    and p.relatoriopositivo = 'Y'
                    and r.alerta ='Y'
                    and r.status = 'ASSINADO'
                    and r.idsecretaria is not null 
                    and r.idamostra = a.idamostra
                    ?clausula?
                    and exists (Select 1 from amostra a1 join resultado r1 on a1.idamostra = r1.idamostra where a.idamostra = a1.idamostra and r1.idsecretaria is not null and not r1.idsecretaria = 0)
                    and exists (Select 1 from amostra a1 join resultado r1 on a1.idamostra = r1.idamostra where a.idamostra = a1.idamostra and r1.idtipoteste = 678)
                GROUP BY  a.idamostra
                order by a.idregistro";
    }

    public static function inserirAmostra(){
        return "INSERT INTO amostra (idempresa,idunidade,status,idfluxostatus,idregistro,exercicio,idespeciefinalidade,idsubtipoamostra,idpessoa,idnucleo,tipoidade,idade,dataamostra,nucleoamostra,partida,tipoobjetosolipor,idobjetosolipor)
                VALUES    ('?idempresa?','?idunidade?','?status?','?idfluxostatus?','?idregistro?','?exercicio?','?idespeciefinalidade?','?idsubtipoamostra?','?idpessoa?','?idnucleo?','?tipoidade?','?idade?','?dataamostra?','?nucleoamostra?','?partida?','?tipoobjetosolipor?','?idobjetosolipor?')";
    }

    public static function buscarAmostrasPorDataClausulaEClausulaIdEmpresa()
    {
        return "SELECT  sum(r.quantidade) as quantidade,
                    ps.descr,
                    pl.plantel,
                    ifnull(f.vlrcusto,0.00) as vlrun, 
                    (sum(r.quantidade)*ifnull(f.vlrcusto,0.00)) as valor,
                    ps.vlrvenda,
                    (sum(r.quantidade)*ifnull(ps.vlrvenda,0.00)) as vlrvendatotal,
                    count(distinct a.idamostra) as qtdtra,                            
                    a.exercicio,
                    a.idunidade,
                    a.dataamostra,
                    r.idtipoteste,
                    a.idempresa,
                    a.idpessoa,
                    p.nome,
                    pl.idplantel                            
                from amostra a 
                join pessoa p on(p.idpessoa = a.idpessoa) 
                join plantelobjeto po on(po.idobjeto = a.idpessoa and po.tipoobjeto='pessoa')
                join plantel pl on(po.idplantel = pl.idplantel)
                join resultado r on(r.idamostra=a.idamostra)
                join prodserv ps on(ps.idprodserv=r.idtipoteste)
                left join prodservformula f on(f.idprodserv=r.idtipoteste)
                where 1 
                and r.status not in ('CANCELADO','OFFLINE')
                ?data?
                ?clausula?
                ?clausulaidempresa?
                group by pl.idplantel,r.idtipoteste
                order by pl.plantel,ps.descr";
    }

    public static function buscarAmostraPorIdPlantelGetIdEmpresaDataEClausula()
    {
        return "SELECT count(distinct a.idamostra) as qtdtra                                                     
                from amostra a 
                join pessoa p on(p.idpessoa = a.idpessoa) 
                join plantelobjeto po on(po.idobjeto = a.idpessoa and po.tipoobjeto='pessoa')
                join plantel pl on(po.idplantel = pl.idplantel and pl.idplantel=?idplantel?)
                where 1
                ?getidempresa?
                ?data?
                ?clausula?";
    }

    public static function buscarAmostraAssinatura()
	{
		return "SELECT a.idregistro,
					   a.exercicio,
					   r.idresultado,
					   p.descr,
					   r.alerta,
					   s.subtipoamostra,
					   DMA(a.dataamostra) AS dataamostra,
					   r.status,
					   (SELECT DMA(ra.criadoem) FROM resultadoassinatura ra WHERE ra.idresultado = r.idresultado ORDER BY criadoem DESC LIMIT 1) AS dataass
                  FROM amostra a JOIN subtipoamostra s ON (s.idsubtipoamostra = a.idsubtipoamostra)
                  JOIN resultado r ON a.idamostra = r.idamostra AND r.status != 'OFFLINE'
                  JOIN prodserv p ON (p.idprodserv = r.idtipoteste AND p.especial = 'Y')
                 WHERE a.idamostratra = ?idamostratra?
                   AND r.status NOT IN ('CANCELADO', 'OFFLINE')
              ORDER BY r.idresultado";
	}

    public static function buscarAmostraAssinaturaComRelatorio()
	{
        return "SELECT a.idregistro,
                       a.exercicio,
                       r.idresultado,
                       p.descr,
                       r.alerta,
                       s.subtipoamostra,
                       DMA(a.dataamostra) AS dataamostra,
                       r.status,
                       (SELECT DMA(ra.criadoem) FROM resultadoassinatura ra WHERE ra.idresultado = r.idresultado ORDER BY criadoem DESC LIMIT 1) AS dataass
                  FROM amostra a JOIN subtipoamostra s ON (s.idsubtipoamostra = a.idsubtipoamostra)
                  JOIN resultado r ON a.idamostra = r.idamostra AND r.status != 'OFFLINE'
                  JOIN prodserv p ON (p.idprodserv = r.idtipoteste AND p.especial = 'Y')
                 WHERE a.idamostra = ?idamostra?
                   AND EXISTS(SELECT 1 FROM prodservtiporelatorio pt WHERE pt.idprodserv = p.idprodserv AND pt.idtiporelatorio = 65)
                   AND r.status NOT IN ('CANCELADO', 'OFFLINE')
              ORDER BY r.idresultado";
    }

    public static function buscarAgentesAmostraPorIdAmostraTra()
	{
        return "SELECT r.idresultado,
                       l.partida,
                       l.exercicio,
                       l.idlote,
                       l.status,
                       p.descr
                  FROM lote l JOIN resultado r ON r.idresultado = l.idobjetosolipor AND l.tipoobjetosolipor = 'resultado'
                  JOIN prodserv p ON p.idprodserv = l.idprodserv
                  JOIN amostra a ON a.idamostra = r.idamostra
                 WHERE r.status NOT IN ('CANCELADO', 'OFFLINE')
                   AND a.idamostratra = ?idamostratra?
              ORDER BY r.ord";
    }

    public static function buscarAgentesAmostraPorIdAmostra()
	{
        return "SELECT r.idresultado,
                       l.partida,
                       l.exercicio,
                       l.idlote,
                       l.status,
                       p.descr
                  FROM lote l JOIN resultado r ON r.idresultado = l.idobjetosolipor AND l.tipoobjetosolipor = 'resultado'
                  JOIN prodserv p ON p.idprodserv = l.idprodserv
                  JOIN amostra a ON a.idamostra = r.idamostra
                 WHERE r.status NOT IN ('CANCELADO', 'OFFLINE')
                   AND a.idamostra = ?idamostra?
              ORDER BY r.ord";
    }

    public static function buscarAmostraPorEnderecoEFinalidade()
	{
        return "SELECT a.*,
                       CONCAT(a.dataamostra, ' ', TIME(a.criadoem)) AS dataamostrah,
                       sta.subtipoamostra AS subtipoamostra,
                       p.centrocusto,
                       IFNULL(e.nomepropriedade, p.nome) AS nome,
                       n.idnucleo,
                       n.nucleo,
                       p.razaosocial,
                       (SELECT CONCAT(IFNULL(en.logradouro, ''), ' ', IFNULL(en.endereco, ''), ', ', IFNULL(en.numero, ''), ', ', IF((IFNULL(en.complemento, '') <> ''),  CONCAT(IFNULL(en.complemento, ''), ', '), ''), IFNULL(en.bairro, ''), ' - ', CONCAT(SUBSTR(en.cep, 1, 5), '-', SUBSTR(en.cep, 6, 3)), ' - ', IFNULL(cs.cidade, ''), '/', IFNULL(en.uf, ''))
                          FROM endereco en LEFT JOIN nfscidadesiaf cs ON cs.codcidade = en.codcidade
                         WHERE en.status = 'ATIVO'
                           AND en.idpessoa = a.idpessoa
                           AND en.idtipoendereco = 6) AS enderecosacado,
                       e.cnpjend AS cpfcnpj,
                       e.inscest AS inscrest,
                       ef.especietipofinalidade AS especietipofinalidade,
                       ef.especiefinalidade AS especiefinalidade,
                       ef.tipoespeciefinalidade AS tipoespeciefinalidade,
                       da.valorobjeto AS valorobjeto
                  FROM amostra a JOIN pessoa p ON p.idpessoa = a.idpessoa 
             LEFT JOIN dadosamostra da ON da.idamostra = a.idamostra
                  JOIN subtipoamostra sta ON sta.idsubtipoamostra = a.idsubtipoamostra
             LEFT JOIN nucleo n ON a.idnucleo = n.idnucleo
             LEFT JOIN vwespeciefinalidade ef ON ef.idespeciefinalidade = a.idespeciefinalidade
             LEFT JOIN endereco e ON (e.status = 'ATIVO' AND e.idpessoa = a.idpessoa AND e.idtipoendereco = 6)
                 WHERE a.idamostra = ?idamostra?";
    }

    public static function buscarResultadoEAmostraPorIdAmostraTra()
	{
        return "SELECT r.*,
                       a.*,
                       r.status AS statusresult,
                       CONCAT(a.dataamostra, ' ', TIME(a.criadoem)) AS dataamostrah,
                       sta.subtipoamostra AS subtipoamostra,
                       p.centrocusto,
                       p.nome,
                       n.idnucleo,
                       n.nucleo,
                       p.razaosocial,
                       (SELECT CONCAT(IFNULL(en.logradouro, ''), ' ', IFNULL(en.endereco, ''), ', ', IFNULL(en.numero, ''), ', ', IF((IFNULL(en.complemento, '') <> ''), CONCAT(IFNULL(en.complemento, ''), ', '), ''), IFNULL(en.bairro, ''), ' - ', CONCAT(SUBSTR(en.cep, 1, 5), '-', SUBSTR(en.cep, 6, 3)), ' - ', IFNULL(cs.cidade, ''), '/', IFNULL(en.uf, ''))
                          FROM endereco en LEFT JOIN nfscidadesiaf cs FORCE INDEX (CODCIDADE) ON cs.codcidade = en.codcidade
                         WHERE en.status = 'ATIVO' AND en.idpessoa = a.idpessoa AND en.idtipoendereco = 6) AS enderecosacado,
                       p.cpfcnpj AS cpfcnpj,
                       p.inscrest AS inscrest,
                       ef.especietipofinalidade AS especietipofinalidade,
                       ef.especiefinalidade AS especiefinalidade,
                       ef.tipoespeciefinalidade AS tipoespeciefinalidade,
                       ps.codprodserv,
                       ps.descr,
                       ps.modelo,
                       ps.modo,
                       ps.tipoespecial,
                       ra.idpessoa AS idassinadopor,
                       MAX(ra.criadoem) AS ascriadoem,
                       rj.jresultado,
                       sbaal.subtipoamostra
                  FROM amostra a JOIN resultado r ON (r.idamostra = a.idamostra)
                  JOIN pessoa p ON p.idpessoa = a.idpessoa JOIN subtipoamostra sta ON sta.idsubtipoamostra = a.idsubtipoamostra
             LEFT JOIN nucleo n ON a.idnucleo = n.idnucleo
             LEFT JOIN vwespeciefinalidade ef ON ef.idespeciefinalidade = a.idespeciefinalidade
                  JOIN prodserv ps ON (ps.idprodserv = r.idtipoteste AND ps.especial = 'Y')
             LEFT JOIN resultadoassinatura ra ON (ra.idresultado = r.idresultado)
             LEFT JOIN resultadojson rj ON rj.idresultado = r.idresultado
             LEFT JOIN resultadoamostralad ral ON (ral.idresultado = r.idresultado)
             LEFT JOIN amostra aal ON (ral.idamostra = aal.idamostra)
             LEFT JOIN subtipoamostra sbaal ON (sbaal.idsubtipoamostra = aal.idsubtipoamostra)
                 WHERE a.idamostratra = ?idamostra?
              GROUP BY r.idresultado
              ORDER BY a.idregistro";
    }

    public static function buscarResultadoEAmostraPorIdAmostra()
	{
        return "SELECT r.*,
                       a.*,
                       r.status AS statusresult,
                       CONCAT(a.dataamostra, ' ', TIME(a.criadoem)) AS dataamostrah,
                       sta.subtipoamostra AS subtipoamostra,
                       p.centrocusto,
                       p.nome,
                       n.idnucleo,
                       n.nucleo,
                       p.razaosocial,
                       (SELECT CONCAT(IFNULL(en.logradouro, ''), ' ', IFNULL(en.endereco, ''), ', ', IFNULL(en.numero, ''), ', ', IF((IFNULL(en.complemento, '') <> ''), CONCAT(IFNULL(en.complemento, ''), ', '), ''), IFNULL(en.bairro, ''), ' - ', CONCAT(SUBSTR(en.cep, 1, 5), '-', SUBSTR(en.cep, 6, 3)), ' - ', IFNULL(cs.cidade, ''), '/', IFNULL(en.uf, ''))
                          FROM endereco en LEFT JOIN nfscidadesiaf cs FORCE INDEX (CODCIDADE) ON cs.codcidade = en.codcidade
                         WHERE en.status = 'ATIVO' AND en.idpessoa = a.idpessoa AND en.idtipoendereco = 6) AS enderecosacado,
                       p.inscrest AS inscrest,
                       ef.especietipofinalidade AS especietipofinalidade,
                       ef.especiefinalidade AS especiefinalidade,
                       ef.tipoespeciefinalidade AS tipoespeciefinalidade,
                       ps.codprodserv,
                       ps.descr,
                       ps.modelo,
                       ps.modo,
                       ps.tipoespecial,
                       ra.idpessoa AS idassinadopor,
                       MAX(ra.criadoem) AS ascriadoem,
                       rj.jresultado
                  FROM amostra a JOIN resultado r ON (r.idamostra = a.idamostra)
                  JOIN pessoa p ON p.idpessoa = a.idpessoa
                  JOIN subtipoamostra sta ON sta.idsubtipoamostra = a.idsubtipoamostra
             LEFT JOIN nucleo n ON a.idnucleo = n.idnucleo
             LEFT JOIN vwespeciefinalidade ef ON ef.idespeciefinalidade = a.idespeciefinalidade
                  JOIN prodserv ps ON (ps.idprodserv = r.idtipoteste AND ps.especial = 'Y')
             LEFT JOIN resultadoassinatura ra ON (ra.idresultado = r.idresultado)
             LEFT JOIN resultadojson rj ON rj.idresultado = r.idresultado
                 WHERE a.idamostra = ?idamostra?
              GROUP BY r.idresultado
              ORDER BY a.idregistro";
    }

    public static function inserirAmostraFormalizacao()
    {
        return "INSERT INTO amostra (idpessoa,                                     
                                     idunidade,
                                     idsubtipoamostra,
                                     idempresa,
                                     idregistro,
                                     idfluxostatus,
                                     descricao,
                                     lote,
                                     exercicio, 
                                     status,                                     
                                     dataamostra,
                                     criadopor,
                                     criadoem,
                                     alteradopor,
                                     alteradoem)
                             VALUES (?idpessoa?,                                     
                                     ?idunidade?,
                                     ?idsubtipoamostra?,
                                     ?idempresa?,
                                     ?idregistro?,
                                     ?idfluxostatus?,
                                     '?descricao?',
                                     '?lote?',
                                     '?exercicio?', 
                                     '?status?',                                     
                                     '?dataamostra?',
                                     '?usuario?',
                                     SYSDATE(),
                                     '?usuario?',
                                     SYSDATE())";
    }

    public static function buscarAgentesTeaTra() {
        return "SELECT
                    distinct ifnull(antibiotico.value, 'Sem antibiotico') as agente
                FROM `amostra` `a`
                JOIN `resultado` `r` ON `r`.`idamostra` = `a`.`idamostra`
                -- LEFT JOIN lote l on r.idresultado = l.idobjetosolipor and l.tipoobjetosolipor = 'resultado'
                -- JOIN `prodserv` `ps` ON `ps`.`idprodserv` = `r`.`idtipoteste`
                JOIN `prodserv` `ps` ON `ps`.`idprodserv` = `r`.`idtipoteste`
                LEFT JOIN JSON_TABLE(r.jsonresultado, '$' COLUMNS (
                    nested path '$.INDIVIDUAL[*]' COLUMNS (
                        name varchar(190) PATH '$.name',
                        value varchar(190) PATH '$.value',
                        titulo varchar(190) PATH '$.titulo'
                    )
                )) as antibiotico on antibiotico.titulo = 'ANTIBIÓTICO'
                WHERE (`a`.`idunidade` = 9)
                and antibiotico.value > ''
                and exists (
                    select 1
                    from prodserv
                    where descr like '%antibiograma%'
                    and idprodserv = r.idtipoteste
                )";
    }

    public static function buscarIndicadoresApagar() {
        return "SELECT ididentificador
                  FROM identificador 
                 WHERE idobjeto = ?idobjeto?
                   AND tipoobjeto = 'amostra'
                   AND identificacao = '?identificacao?'
              ORDER BY ididentificador DESC
                 LIMIT ?limitselect?";
    }

    public static function apagarIndicadores() {
        return "DELETE FROM identificador WHERE ididentificador = '?ididentificador?';";
    }

    public static function buscarResultadosCliente() {
        return "SELECT 
                    r.idresultado,
                    a.idamostra, 
                    a.dataamostra, 
                    p.nome, 
                    a.tutor, 
                    a.paciente, 
                    CONCAT(pl.plantel, '-',ef.finalidade) as especie,
                    '' as raca, 
                    IF(a.sexo > '', a.sexo, '-') as sexo,
                    IF(a.idade > '', a.idade, '-') as idade,
                    IF(a.tipoidade > '', a.tipoidade, '-') as tipoidade,
                    IF(ta.tipoamostra > '', ta.tipoamostra, '-') as tipoamostra,
                    r.status,
                    ps.modelo
                from amostra a
                join pessoacontato pc on pc.idpessoa = a.idpessoa
                join pessoa p on p.idpessoa = pc.idcontato
                left join tipoamostra ta on ta.idtipoamostra = a.idtipoamostra
                left join resultado r on r.idamostra = a.idamostra
                left join especiefinalidade ef on ef.idespeciefinalidade = a.idespeciefinalidade
                left join plantel pl on pl.idplantel = ef.idplantel
                left join prodserv ps on ps.idprodserv = r.idtipoteste
                where a.idempresa = ?idempresa?
                and pc.idcontato = ?idpessoa?
                and a.idunidade = ?idunidade?
                and r.status != 'CANCELADO'";
    }

    public static function alterarStatus() {
        return "UPDATE amostra
                SET idfluxostatus = ?idfluxostatus?,
                status = '?status?'
                WHERE idamostra = ?idamostra?";
    }
}

?>
