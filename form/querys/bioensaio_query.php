<?
require_once(__DIR__."/_iquery.php");

class BioensaioQuery implements DefaultQuery{

    public static $table = "bioensaio";
	public static $pk = "idbioensaio";

	public static function buscarPorChavePrimaria(){
		return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, ['table'=>self::$table,'pk'=>self::$pk]) ;
	}

	public static function buscarPaisEFilhosDoBioensaios(){
		return "SELECT * from  (
            select 'pai' as idbioensaiodes,
                    b.idbioensaio,
                    b.qtd,
                    concat('B',b.idregistro,'/',b.exercicio) as registro,
                    b.idbioensaio as idbioensaioc
            from bioensaio b 
            where b.idbioensaio =  ?idbioensaio? 
            union all
            select 'controle',
                    bb.idbioensaio,
                    bb.qtd,
                    concat('B',bb.idregistro,'/',bb.exercicio) as registro,
                    bb.idbioensaio as idbioensaioc
            from analise a 
                join bioensaio bb on(a.idbioensaioctr = bb.idbioensaio) 
                where a.objeto = 'bioensaio' and a.idobjeto = ?idbioensaio?
            union all 
            select d.idbioensaiodes,
                    b.idbioensaio,
                    b.qtd,
                    concat('B',b.idregistro,'/',b.exercicio) as registro,
                    d.idbioensaio as idbioensaioc
            from bioensaiodes d
                join bioensaio b on(b.idbioensaio=d.idbioensaioc)
            where d.idbioensaio = ?idbioensaio? 
            union all
            select 'controle',
                bb.idbioensaio,
                bb.qtd,
                concat('B',bb.idregistro,'/',bb.exercicio) as registro,
                d.idbioensaio as idbioensaioc
            from  bioensaiodes d
                join bioensaio b on(b.idbioensaio=d.idbioensaioc)
                join analise a on(a.objeto = 'bioensaio' and a.idobjeto = b.idbioensaio)
                join bioensaio bb on(a.idbioensaioctr = bb.idbioensaio) 
            where d.idbioensaio = ?idbioensaio?
            ) as u 
            group by u.idbioensaio";
	}

	public static function buscarDesenhoExperimental(){
		return "SELECT * from bioensaiodes where idbioensaioc =?idbioensaioexperimental? and idbioensaio = ?idbioensaio?";
	}

	public static function buscarEstudosParaControle(){
		return "SELECT b.idbioensaio,
                        concat('B',b.idregistro,'/',b.exercicio) as registro
                from bioensaio b
                where  b.idpessoa = ?idpessoa?
                and b.agrupar ='N'
                ?getidempresa?
                and not exists(select 1 from bioensaiodes d where d.idbioensaioc = b.idbioensaio)
                and not exists(select 1 from bioensaiodes dd where dd.idbioensaio = b.idbioensaio) order by b.idregistro";
	}

	public static function buscarBioensaiosParaControle(){
		return "SELECT e.idbioensaio,
                        LEFT(concat('B',e.idregistro,' - ',e.estudo),40) as bioensaio
                from bioensaio e 
                where e.idpessoa = ?idpessoa?
                    and e.status not in ('CANCELADO','FINALIZADO')
                    and e.idbioensaio != ?idbioensaio?
                ORDER BY e.idregistro";
	}

	public static function buscarServicosDeUmaGaiola(){
		return "SELECT ts.idtagpai,
                        t.descricao as descrpai,
                        IFNULL(SUM(r.qtd), 0) AS ocup,
                        IFNULL(t2.lotacao, 0) AS lotacao,
                        t2.idtag,
                        t2.tempo,
                        t2.descricao as rot,
                        sum(r.qtd) as qtd,
                        dma(fimbio) fimbio,
                        r.especie,
                        e2.idtag as idlocal,
                        r.idbioensaio
                from tag t
                    join tagtipo tt on(tt.idtagtipo=t.idtagtipo and tt.bioensaio='Y' and tt.idplantel=?idplantel?)
                    join tagsala ts on(ts.idtagpai = t.idtag)
                    join tag t2 on( t2.idtag=ts.idtag and t2.idtagclass = 2 ) join tagtipo tt2 on(tt2.idtagtipo=t2.idtagtipo and tt2.bioensaio='Y' and tt2.idplantel=?idplantel?)
                    LEFT JOIN localensaio e ON (e.status IN ('AGENDADO' , 'ATIVO')
                        AND e.idtag = t2.idtag)
                    LEFT JOIN vw_reservabioensaio r ON (e.idanalise = r.idanalise
                        AND e.status not in  ('FINALIZADO','CANCELADO')
                            AND (
                                (
                                    if(r.iniciobio<='?r3data?','?r3data?',r.iniciobio) = '?r3data?'
                                    and 
                                    if(r.fimbio>='?fdata?','?fdata?',r.fimbio )= '?fdata?'
                                )
                                or
                                (
                                    (r.iniciobio between '?r3data?' and '?fdata?' or  r.fimbio  between '?r3data?' and '?fdata?')
                                )
                                ))
                    left join localensaio e2 on (e.idtag = t2.idtag
                        and e.status not in  ('FINALIZADO','CANCELADO')
                        and r.fimbio >= now()
                        and e.idanalise = e2.idanalise)
                where t.status='ATIVO' 
                    and t.idtagclass = 2 
                    and t.idunidade = ?idunidade?
                    ?share?
                GROUP BY t2.idtag
                order by ts.idtagpai,idtag,t.ordem,t2.ordem";
	}

	public static function verificaSeHaVagasNaGaiola(){
		return "SELECT sum(r.qtd) as qtd,
                        dma(fimbio) fimbio,
                        r.especie, e.idtag,
                        r.idbioensaio
                from localensaio e,vw_reservabioensaio r 
                where  e.idtag = ?idtag?
                    and e.status not in  ('FINALIZADO','CANCELADO')
                    and r.fimbio >= now()
                    and r.idanalise = e.idanalise
                group by fimbio,especie";
	}

	public static function atualizarDosesVolumeViaDoBioensaioPelaConfiguracao(){
		return "UPDATE analise a,bioensaio e,bioterioanalise b
                set e.doses=b.pddose,e.volume=b.pdvolume,e.via=b.pdvia
                where a.idanalise= ?idanalise?
                and e.idbioensaio = a.idobjeto
                and a.objeto ='bioensaio'
                and b.cria='N'
                and b.idbioterioanalise = ?idbioterioanalise?";
	}

	public static function buscarEstudosDeUmaGaiola(){
		return "SELECT
                    b.idregistro,
                    e.idlocalensaio,
                    b.idbioensaio,
                    UPPER(LEFT(b.estudo,20)) as bioensaio,
                    b.cor,
                    a.qtd,		
                    l.local,
                    e.idtag,
                    dma(r.iniciobio) as inicio1,
                    dma(r.fimbio) as fim1,
                    if(CURDATE()>=r.iniciobio,'S','N') as mmin,
                    if(CURDATE()<=r.fimbio,'S','N') as mmax,
                    (DATEDIFF(curdate(),r.fiminc)) AS diasvida,
                    e.obs,
                    e.gaiola,
                    b.status,
                    a.idanalise				
                from bioensaio b 
                    join analise a on (a.idobjeto=b.idbioensaio and a.objeto='bioensaio')
                    join localensaio e on (e.idanalise=a.idanalise)
                    join tag l on (e.idtag=l.idtag)
                    join vw_reservabioensaio r on(r.idanalise = a.idanalise AND  r.fimbio >= CURDATE())
                where b.status  not in('CANCELADO','FINALIZADO') 
                    and e.idtag = ?idtag?
                group by idanalise
                order by b.status asc, bioensaio, idanalise, gaiola, diasvida";
	}

    public static function buscarBioensaioPorAtividade()
    {
		return "SELECT r.idresultado,
                       b.idregistro,
                       b.exercicio,
                       b.idbioensaio,
                       p.descr,
                       r.status
                  FROM bioensaio b JOIN analise a ON a.idobjeto = b.idbioensaio AND a.objeto = 'bioensaio'
                  JOIN servicoensaio s ON s.idobjeto = a.idanalise AND s.tipoobjeto = 'analise'
                  JOIN resultado r ON r.idservicoensaio = s.idservicoensaio
                  JOIN prodserv p ON p.idprodserv = r.idtipoteste
                 WHERE b.idloteativ = ?idloteativ?";
    }
}?>