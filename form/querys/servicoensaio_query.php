<?
class ServicoEnsaioQuery {

    public static function buscarIdentificacaoResultadoBioterio(){
        return "SELECT 
            i.identificacao
        FROM
            identificador i,
            servicoensaio s
        WHERE
            i.tipoobjeto = 'bioensaio'
                AND i.idobjeto = s.idobjeto
                AND s.tipoobjeto = 'bioensaio'
                AND s.idservicoensaio = ?idservicoensaio?
        ORDER BY identificacao";
    }

    public static function buscarIdentificacaoResultado(){
        return "SELECT 
            i.identificacao
        FROM
            identificador i
        WHERE
            i.idobjeto = ?idamostra?
                AND i.tipoobjeto = 'amostra'
        ORDER BY ididentificador";
    }

    public static function apagarServicosPendentesDaAnalise(){
        return "DELETE s.*  from servicoensaio s
                where s.idobjeto = ?idanalise?
                and s.status = 'PENDENTE'
                and  s.tipoobjeto='analise'";
    }

    public static function inserirServicosDaConfiguracao(){
        return "INSERT INTO servicoensaio (idempresa,idobjeto,tipoobjeto,idservicobioterio,dia,diazero,data,status,criadopor,criadoem,alteradopor,alteradoem)
                (select ?idempresa?,?idanalise?,'analise',c.idservicobioterio,c.dia,c.diazero,DATE_ADD('?dtinicio?', INTERVAL c.dia DAY) as datafim,'PENDENTE'
                ,'?usuario?',sysdate(),'?usuario?',sysdate()
                from servicobioterioconf c
                where c.idobjeto = ?idbioterioanalise?
                and c.tipoobjeto='bioterioanalise')";
    }

    public static function copiarConfServicoBioterio(){
        return "INSERT INTO servicoensaio
        (idempresa,idobjeto,tipoobjeto,idservicobioterio,servico,
        dia,data,diazero,obs,status,idservicoensaioctr,criadopor,criadoem,alteradopor,alteradoem)
        (select s.idempresa,?idanlisectr?,s.tipoobjeto,s.idservicobioterio,s.servico,
            s.dia,s.data,s.diazero,s.obs,'PENDENTE',s.idservicoensaio,'?usuario?',now(),'?usuario?',now()  
        from  servicoensaio s,servicobioterio sb  
            where s.idobjeto= ?idanalise?
            and s.tipoobjeto ='analise'
            and s.idservicobioterio=sb.idservicobioterio
            and sb.controle ='Y'
        )";
    }

    public static function atualizarAmostraDoServico(){
        return "UPDATE servicoensaio set idamostra =?idamostra?
                where idservicoensaio = ?idservicoensaio?";
    }

    public static function buscarConfiguracoesDoExame(){
        return "SELECT
                    ba.idprodserv,
                    c.idservicobioterio,
                    c.dia,s.idservicoensaio,
                    e.qtd,
                    e.produto,
                    e.partida,
                    e.idnucleo,
                    e.nascimento,
                    s.data,
                    DATEDIFF(s.data,e.nascimento) AS idade,
                    e.idpessoa,
                    e.idespeciefinalidade,
                    n.nucleo,
                    sb.idsubtipoamostra,
                    s.idamostra
                from analise a
                    join servicobioterioconf c on (c.idobjeto=a.idbioterioanalise and c.tipoobjeto='bioterioanalise')
                    join bioterioanaliseteste ba on (ba.idservicobioterioconf = c.idservicobioterioconf)
                    JOIN servicoensaio s on (s.idservicobioterio=c.idservicobioterio and s.dia=c.dia and s.idobjeto=a.idanalise and s.tipoobjeto = 'analise')
                    left JOIN bioensaio e on (e.idbioensaio = a.idobjeto)
                    left JOIN nucleo n on (e.idnucleo= n.idnucleo)
                    JOIN servicobioterio sb on (s.idservicobioterio = sb.idservicobioterio)
                where a.objeto = 'bioensaio'
                    and a.idanalise= ?idanalise?
                order by s.dia, idservicoensaio";
    }

    public static function buscarConfiguracaoDoEnsaio(){
        return "SELECT     
                    s.idservicobioterio,
                    s.dia,
                    s.idservicoensaio,
                    e.qtd,
                    e.produto,
                    e.partida,
                    e.idnucleo,
                    e.nascimento,
                    s.data,
                    DATEDIFF(s.data, e.nascimento) AS idade,
                    e.idpessoa,
                    e.idespeciefinalidade,
                    n.nucleo,
                    sb.idsubtipoamostra,
                    s.idamostra
                FROM
                    analise a
                    JOIN servicoensaio s ON (s.idobjeto = a.idanalise AND s.tipoobjeto = 'analise' AND s.idservicoensaio =?idservicoensaio?)
                    JOIN servicobioterio sb ON (s.idservicobioterio = sb.idservicobioterio)
                    JOIN bioensaio e ON (e.idbioensaio = a.idobjeto)
                    LEFT JOIN nucleo n ON (e.idnucleo = n.idnucleo)
                WHERE a.objeto = 'bioensaio'";
    }

    public static function buscarServicosPendentesPorUnidade(){
        return "SELECT     
                    b.idregistro,
                    UPPER(b.estudo) AS bioensaio,            
                    b.idbioensaio,
                    s.idservicoensaio,
                    sb.servico,
                    s.data AS dataserv,
                    IF(CURDATE() <= s.data, 'black', 'red') AS cor,
                    DMA(s.data) AS dmadata,
                    s.dia,
                    s.obs,
                    s.status,
                    l.gaiola,
                    'bioensaio' AS origem,
                    tg.descricao as local,
                    lo.descricao as gaiola
                from servicoensaio s
                    join servicobioterio sb
                    join analise a
                    join bioensaio b
                    LEFT JOIN localensaio l ON (a.idanalise = l.idanalise)
                    LEFT JOIN tag lo ON (lo.idtag = l.idtag)
                    left join tagsala ts on(ts.idtag=lo.idtag)
                    left join tag tg on(tg.idtag=ts.idtagpai)
                where b.idbioensaio=a.idobjeto
                    and sb.idservicobioterio = s.idservicobioterio
                    and a.objeto ='bioensaio'
                    and a.idanalise = s.idobjeto
                    and s.tipoobjeto = 'analise'
                    -- and b.idunidade = ?idunidadepadrao?
                    AND s.data BETWEEN CURDATE() - INTERVAL 100 DAY AND CURDATE() + INTERVAL 7 DAY
                    AND s.status = 'PENDENTE'
                ORDER BY dataserv , servico, local";
    }

    public static function apagarServicoEnsaio(){
        return "DELETE from servicoensaio where idobjeto= ?idanalise? and tipoobjeto ='analise' and status!='CONCLUIDO'";
    }

}
?>