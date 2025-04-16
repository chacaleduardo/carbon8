<?
//ini_set("display_errors","1");
//error_reporting(E_ALL);
session_start();
$sessionid = session_id();//PEGA A SESSÃO 

if (defined('STDIN')){//se estiver sendo executao em linhade comando

	require_once("/var/www/carbon8/inc/php/functions.php");


}else{//se estiver seno executao via requisicao http
	require_once("../inc/php/functions.php");
	
}
$inspecionar=$_REQUEST['inspecionar'];


$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."', 'cron', 'atualizarateioproduto', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

/*atualiza lotes disponiveis
$sql="select idrateioitem,idunidade ,round(sum(qtdd/((qtdini+ifnull(qtdc,0)))*100)) as rateion, idunidadeest,round(((qtd)/((qtdini+ifnull(qtdc,0)))*100),4)  as rateioalm,idempresa
from (
 select r.idrateioitem,c.idlote,u.idunidade,c.qtdd,(select sum(qtdc)  from lotecons c where(c.idlotefracao=f.idlotefracao and c.qtdc>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')) as qtdc,l.qtdprod,l.valconvori,f.qtdini,f.qtd, l.idunidade as idunidadeest,i.qtd as qtdi,l.idempresa    
 -- ,sum(c.qtdd) as debito,(l.qtdprod*l.valconvori),f.qtd as disponivel,sum(c.qtdd)/(l.qtdprod*l.valconvori)*100 as rateion, p.idunidadeest,f.qtd/(l.qtdprod*l.valconvori)*100  as rateioalm
from nf n join nfitem i on(n.idnf = i.idnf)
join rateioitem r on(r.idobjeto=i.idnfitem and r.tipoobjeto='nfitem')
join prodserv p on(p.idprodserv =i.idprodserv and p.idunidadeest is not null and p.tipo='PRODUTO' )
join lote l on(l.idnfitem=i.idnfitem and l.status='APROVADO' ) 
join lotefracao f on(f.idlote=l.idlote and f.idunidade=l.idunidade  and f.status='DISPONIVEL')
join lotecons c on(c.idlotefracao=f.idlotefracao and c.qtdd>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO' -- and c.criadoem < '2023-06-01 01:00:00'
)
 -- join lotefracao fd on(fd.idlotefracao=c.idobjeto)
join  unidade u on  (u.idtipounidade = 19 and u.status ='ATIVO' and u.idempresa = l.idempresa)
where n.tiponf = 'C' 
--  and l.idlote= 205047 
 -- and n.idnf = 231693
 -- AND n.dtemissao >   '2023-06-01 01:00:00' 
 union all
  select  r.idrateioitem,c.idlote,fd.idunidade ,c.qtdd,(select sum(qtdc)  from lotecons c where(c.idlotefracao=f.idlotefracao and c.qtdc>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')) as qtdc,l.qtdprod,l.valconvori,f.qtdini,f.qtd, l.idunidade as idunidadeest,i.qtd as qtdi,l.idempresa    
  -- sum(c.qtdd) as debito,(l.qtdprod*l.valconvori),f.qtd as disponivel,sum(c.qtdd)/(l.qtdprod*l.valconvori)*100 as rateion, p.idunidadeest,f.qtd/(l.qtdprod*l.valconvori)*100  as rateioalm
from nf n join nfitem i on(n.idnf = i.idnf)
join rateioitem r on(r.idobjeto=i.idnfitem and r.tipoobjeto='nfitem')
join prodserv p on(p.idprodserv =i.idprodserv and p.idunidadeest is not null and p.tipo='PRODUTO' )
join lote l on(l.idnfitem=i.idnfitem and l.status='APROVADO' )
join lotefracao f on(f.idlote=l.idlote and f.idunidade=l.idunidade  and f.status='DISPONIVEL')
join lotecons c on(c.idlotefracao=f.idlotefracao and c.qtdd>0 and c.tipoobjeto = 'lotefracao' and c.status!='INATIVO' --  and c.criadoem < '2023-06-01 01:00:00'
)
left join lotefracao fd on(fd.idlotefracao=c.idobjeto)
where n.tiponf = 'C' 
-- and l.idlote= 205047 
 -- and n.idnf = 231693
--  AND n.dtemissao >   '2023-06-01 01:00:00'
 union all
   select  r.idrateioitem,c.idlote,pl.idunidade ,c.qtdd,(select sum(qtdc) from lotecons c where(c.idlotefracao=f.idlotefracao and c.qtdc>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')) as qtdc,l.qtdprod,l.valconvori,f.qtdini,f.qtd, l.idunidade as  idunidadeest,i.qtd as qtdi,l.idempresa   
-- ,sum(c.qtdd) as debito,(l.qtdprod*l.valconvori),f.qtd as disponivel,sum(c.qtdd)/(l.qtdprod*l.valconvori)*100 as rateion, p.idunidadeest,f.qtd/(l.qtdprod*l.valconvori)*100  as rateioalm
from nf n join nfitem i on(n.idnf = i.idnf)
join rateioitem r on(r.idobjeto=i.idnfitem and r.tipoobjeto='nfitem')
join prodserv p on(p.idprodserv =i.idprodserv and p.idunidadeest is not null and p.tipo='PRODUTO' )
join lote l on(l.idnfitem=i.idnfitem and l.status='APROVADO' )
join lotefracao f on(f.idlote=l.idlote and f.idunidade=l.idunidade and f.status='DISPONIVEL')
join lotecons c on(c.idlotefracao=f.idlotefracao and c.qtdd>0 and c.tipoobjeto = 'nfitem' and c.status!='INATIVO' -- and c.criadoem < '2023-06-01 01:00:00'
)
join nfitem ii on(ii.idnfitem=c.idobjeto)
join nf nn on(nn.idnf=ii.idnf)
  join plantelobjeto po on(po.idobjeto=nn.idpessoa and po.tipoobjeto = 'pessoa')
	join plantel pl on(pl.idplantel=po.idplantel)
where n.tiponf = 'C' 
-- and l.idlote= 205047 
 -- and n.idnf = 231693
--  AND n.dtemissao >   '2023-06-01 01:00:00'
 ) as u
 group by idrateioitem,idunidade  order by idrateioitem";
*/

/*
$sql="select   idrateioitem,idunidade ,round(sum(qtdd/((qtdini+ifnull(qtdc,0)))*100),4) as rateion, idunidadeest,round(((qtd)/((qtdini+ifnull(qtdc,0)))*100),4)  as rateioalm,idempresa
-- idrateioitem,idunidade ,sum(qtdd/((qtdi+ifnull(qtdc,0))*valconvori)*100) as rateion, idunidadeest,(qtd)/((qtdi+ifnull(qtdc,0))*valconvori)*100  as rateioalm,idempresa
from (
 select r.idrateioitem,c.idlote,u.idunidade,c.qtdd,(select sum(qtdc)  from lotecons c where(c.idlotefracao=f.idlotefracao and c.qtdc>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')) as qtdc,l.qtdprod,l.valconvori,f.qtdini,f.qtd, l.idunidade as idunidadeest,i.qtd as qtdi,l.idempresa    
 -- ,sum(c.qtdd) as debito,(l.qtdprod*l.valconvori),f.qtd as disponivel,sum(c.qtdd)/(l.qtdprod*l.valconvori)*100 as rateion, p.idunidadeest,f.qtd/(l.qtdprod*l.valconvori)*100  as rateioalm
from nf n join nfitem i on(n.idnf = i.idnf)
join rateioitem r on(r.idobjeto=i.idnfitem and r.tipoobjeto='nfitem')
join prodserv p on(p.idprodserv =i.idprodserv and p.idunidadeest is not null and p.tipo='PRODUTO' )
join lote l on(l.idnfitem=i.idnfitem and l.status!='CANCELADO') 
join lotefracao f on(f.idlote=l.idlote and f.idunidade=l.idunidade)
join lotecons c on(c.idlotefracao=f.idlotefracao and c.qtdd>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')
 -- join lotefracao fd on(fd.idlotefracao=c.idobjeto)
join  unidade u on  (u.idtipounidade = 19 and u.status ='ATIVO' and u.idempresa = l.idempresa)
where n.tiponf = 'C' 
-- and l.idlote= 203556 
 AND n.dtemissao >   '2023-06-01 01:00:00' 
 union all
  select  r.idrateioitem,c.idlote,fd.idunidade ,c.qtdd,(select sum(qtdc)  from lotecons c where(c.idlotefracao=f.idlotefracao and c.qtdc>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')) as qtdc,l.qtdprod,l.valconvori,f.qtdini,f.qtd, l.idunidade as idunidadeest,i.qtd as qtdi,l.idempresa    
  -- sum(c.qtdd) as debito,(l.qtdprod*l.valconvori),f.qtd as disponivel,sum(c.qtdd)/(l.qtdprod*l.valconvori)*100 as rateion, p.idunidadeest,f.qtd/(l.qtdprod*l.valconvori)*100  as rateioalm
from nf n join nfitem i on(n.idnf = i.idnf)
join rateioitem r on(r.idobjeto=i.idnfitem and r.tipoobjeto='nfitem')
join prodserv p on(p.idprodserv =i.idprodserv and p.idunidadeest is not null and p.tipo='PRODUTO' )
join lote l on(l.idnfitem=i.idnfitem and l.status!='CANCELADO')
join lotefracao f on(f.idlote=l.idlote and f.idunidade=l.idunidade)
join lotecons c on(c.idlotefracao=f.idlotefracao and c.qtdd>0 and c.tipoobjeto = 'lotefracao' and c.status!='INATIVO')
left join lotefracao fd on(fd.idlotefracao=c.idobjeto)
where n.tiponf = 'C' 
 -- and l.idlote= 203556 
 AND n.dtemissao >   '2023-06-01 01:00:00'
 union all
   select  r.idrateioitem,c.idlote,pl.idunidade ,c.qtdd,(select sum(qtdc) from lotecons c where(c.idlotefracao=f.idlotefracao and c.qtdc>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')) as qtdc,l.qtdprod,l.valconvori,f.qtdini,f.qtd, l.idunidade as  idunidadeest,i.qtd as qtdi,l.idempresa   
-- ,sum(c.qtdd) as debito,(l.qtdprod*l.valconvori),f.qtd as disponivel,sum(c.qtdd)/(l.qtdprod*l.valconvori)*100 as rateion, p.idunidadeest,f.qtd/(l.qtdprod*l.valconvori)*100  as rateioalm
from nf n join nfitem i on(n.idnf = i.idnf)
join rateioitem r on(r.idobjeto=i.idnfitem and r.tipoobjeto='nfitem')
join prodserv p on(p.idprodserv =i.idprodserv and p.idunidadeest is not null and p.tipo='PRODUTO' )
join lote l on(l.idnfitem=i.idnfitem and l.status!='CANCELADO')
join lotefracao f on(f.idlote=l.idlote and f.idunidade=l.idunidade)
join lotecons c on(c.idlotefracao=f.idlotefracao and c.qtdd>0 and c.tipoobjeto = 'nfitem' and c.status!='INATIVO')
join nfitem ii on(ii.idnfitem=c.idobjeto)
join nf nn on(nn.idnf=ii.idnf)
  join plantelobjeto po on(po.idobjeto=nn.idpessoa and po.tipoobjeto = 'pessoa')
	join plantel pl on(pl.idplantel=po.idplantel)
where n.tiponf = 'C' 
 -- and l.idlote= 203556 
 AND n.dtemissao >   '2023-06-01 01:00:00'
 ) as u
 group by idrateioitem,idunidade  order by idrateioitem";
 */



 $sql="select idrateioitem,idunidade ,round(sum(qtdd/(((qtdi*valconvori)+ifnull(qtdc,0)))*100),4) as rateion, idunidadeest,round(((qtd)/(((qtdi*valconvori)+ifnull(qtdc,0)))*100),4)  as rateioalm,idempresa,
 -- idrateioitem,idunidade ,sum(qtdd/((qtdi+ifnull(qtdc,0))*valconvori)*100) as rateion, idunidadeest,
 (
  qtd
  +
  -- itens que estão no meios como fabricado ou nao
  ifnull(
    (select   
    (select sum(qtd) as qtdmeios from (
      select  fd2.qtd    
      from rateioitem r2
    join lote l2 on(l2.idnfitem=r2.idobjeto and l2.status!='CANCELADO'  )
    join lotefracao f2 on(f2.idlote=l2.idlote and f2.idunidade=l2.idunidade)
    join lotecons c2 on(c2.idlotefracao=f2.idlotefracao and c2.qtdd>0 and c2.tipoobjeto = 'lotefracao' and c2.status!='INATIVO')
     join lotefracao fd2 on(fd2.idlotefracao=c2.idobjeto and fd2.qtd > 0 )
     join unidade u2 on(u2.idunidade=fd2.idunidade  and u2.cd='Y')                         
    where r2.idrateioitem = u.idrateioitem
      group by  f2.idlotefracao
      union
   select  round(((cm2.qtdd*lfp2.qtd)/lp2.qtdprod),4) as qtd
      from  rateioitem r2
    join lote l2 on(l2.idnfitem=r2.idobjeto  and l2.status!='CANCELADO'  )
    join lotefracao f2 on(f2.idlote=l2.idlote and f2.idunidade=l2.idunidade)
    join lotecons c2 on(c2.idlotefracao=f2.idlotefracao and c2.qtdd>0 and c2.tipoobjeto = 'lotefracao' and c2.status!='INATIVO')
     join lotefracao fd2 on(fd2.idlotefracao=c2.idobjeto)
     join unidade u2 on(u2.idunidade=fd2.idunidade  and u2.cd='Y')    
      join lotecons cm2 on(cm2.idlotefracao=fd2.idlotefracao and cm2.qtdd>0 and cm2.tipoobjeto='lote'  and cm2.status='ABERTO')
                           join lote lp2 on(lp2.idlote=cm2.idobjeto)
                           join lotefracao lfp2 on(lfp2.idlote=lp2.idlote and lfp2.idunidade=lp2.idunidade and lfp2.qtd>0)                      
    where r2.idrateioitem = u.idrateioitem
      group by  lfp2.idlotefracao) as xx) ),0)
   
   )/((qtdi+ifnull(qtdc,0))*valconvori)*100  as rateioalm,idempresa
 from (
  -- fabricar e transferir
 select  r.idrateioitem,c.idlote,lfc.idunidade ,round(((cm.qtdd*sm.qtdd)/lp.qtdprod),2)  as qtdd,(select sum(qtdc)  from lotecons c where(c.idlotefracao=f.idlotefracao and c.qtdc>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')) as qtdc,l.qtdprod,l.valconvori,f.qtdini,f.qtd, l.idunidade as idunidadeest,i.qtd as qtdi,l.idempresa    
   from lotecons cc join lote ll on(ll.idlote=cc.idlote  and ll.idempresa!=8)
 join nfitem i on(ll.idnfitem = i.idnfitem)
 join rateioitem r on(r.idobjeto=i.idnfitem and r.tipoobjeto='nfitem')
 join prodserv p on(p.idprodserv =i.idprodserv and p.idunidadeest is not null and p.tipo='PRODUTO'  and p.insumo ='N' )
 join lote l on(l.idnfitem=i.idnfitem and l.status!='CANCELADO'  )
 join lotefracao f on(f.idlote=l.idlote and f.idunidade=l.idunidade)
 join lotecons c on(c.idlotefracao=f.idlotefracao and c.qtdd>0 and c.tipoobjeto = 'lotefracao' and c.status!='INATIVO')
  join lotefracao fd on(fd.idlotefracao=c.idobjeto)
  join unidade u on(u.idunidade=fd.idunidade  and u.cd='Y')    
	join lotecons cm on(cm.idlotefracao=fd.idlotefracao and cm.qtdd>0 and cm.tipoobjeto='lote'  and cm.status='ABERTO')
	join lote lp on(lp.idlote=cm.idobjeto)
	join lotecons sm on(sm.idlote = cm.idobjeto and sm.qtdd>0 and sm.tipoobjeto ='lotefracao'  and sm.status='ABERTO' )
	join lotefracao lfc on(lfc.idlotefracao = sm.idobjeto)																			
	join unidade ufc on(ufc.idunidade = lfc.idunidade  and ufc.cd ='N')     
 where cc.alteradoem > DATE_SUB(now(), INTERVAL 30 DAY) 
  and  (cc.qtdd>0 or cc.qtdc>0) and (cc.tipoobjeto = 'nfitem' or cc.tipoobjeto = 'lotefracao' or   cc.tipoobjeto is null )
  and cc.alteradoem BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND CURDATE()

   group by sm.idlotecons, cm.idlotecons
   union 
   -- fabricar e vender
   select  r.idrateioitem,c.idlote,ufc.idunidade ,round(((cm.qtdd*sm.qtdd)/lp.qtdprod),2)  as qtdd,(select sum(qtdc)  from lotecons c where(c.idlotefracao=f.idlotefracao and c.qtdc>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')) as qtdc,l.qtdprod,l.valconvori,f.qtdini,f.qtd, l.idunidade as idunidadeest,i.qtd as qtdi,l.idempresa    
   from lotecons cc join lote ll on(ll.idlote=cc.idlote  and ll.idempresa!=8)
 join nfitem i on(ll.idnfitem = i.idnfitem)
 join rateioitem r on(r.idobjeto=i.idnfitem and r.tipoobjeto='nfitem')
 join prodserv p on(p.idprodserv =i.idprodserv and p.idunidadeest is not null and p.tipo='PRODUTO'  and p.insumo ='N')
 join lote l on(l.idnfitem=i.idnfitem and l.status!='CANCELADO'  )
 join lotefracao f on(f.idlote=l.idlote and f.idunidade=l.idunidade)
 join lotecons c on(c.idlotefracao=f.idlotefracao and c.qtdd>0 and c.tipoobjeto = 'lotefracao' and c.status!='INATIVO')
  join lotefracao fd on(fd.idlotefracao=c.idobjeto)
  join unidade u on(u.idunidade=fd.idunidade  and u.cd='Y')    
	join lotecons cm on(cm.idlotefracao=fd.idlotefracao and cm.qtdd>0 and cm.tipoobjeto='lote'  and cm.status='ABERTO')
	join lote lp on(lp.idlote=cm.idobjeto)
	join lotecons sm on(sm.idlote = cm.idobjeto and sm.qtdd>0 and sm.tipoobjeto ='nfitem'  and sm.status='ABERTO' )
	join nfitem ni on(ni.idnfitem = sm.idobjeto)
	join nf n on(n.idnf = ni.idnf)
	join unidade ufc on( ufc.idtipounidade=21 and n.idempresa=ufc.idempresa and ufc.status='ATIVO')	
 where cc.alteradoem > DATE_SUB(now(), INTERVAL 30 DAY) 
  and  (cc.qtdd>0 or cc.qtdc>0) and (cc.tipoobjeto = 'nfitem' or cc.tipoobjeto = 'lotefracao' or   cc.tipoobjeto is null )
  and cc.alteradoem BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND CURDATE()

   group by  sm.idlotecons, cm.idlotecons
   union
    -- receber e transferiu como estava
    select  r.idrateioitem,c.idlote,ufc.idunidade ,round(cm.qtdd,2)  as qtdd,(select sum(qtdc)  from lotecons c where(c.idlotefracao=f.idlotefracao and c.qtdc>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')) as qtdc,l.qtdprod,l.valconvori,f.qtdini,f.qtd, l.idunidade as idunidadeest,i.qtd as qtdi,l.idempresa    
   from lotecons cc join lote ll on(ll.idlote=cc.idlote  and ll.idempresa!=8)
 join nfitem i on(ll.idnfitem = i.idnfitem)
 join rateioitem r on(r.idobjeto=i.idnfitem and r.tipoobjeto='nfitem')
 join prodserv p on(p.idprodserv =i.idprodserv and p.idunidadeest is not null and p.tipo='PRODUTO'  and p.insumo ='N' )
 join lote l on(l.idnfitem=i.idnfitem and l.status!='CANCELADO'  )
 join lotefracao f on(f.idlote=l.idlote and f.idunidade=l.idunidade)
 join lotecons c on(c.idlotefracao=f.idlotefracao and c.qtdd>0 and c.tipoobjeto = 'lotefracao' and c.status!='INATIVO')
  join lotefracao fd on(fd.idlotefracao=c.idobjeto)
  join unidade u on(u.idunidade=fd.idunidade  and u.cd='Y')    
		 join lotecons cm on(cm.idlotefracao=fd.idlotefracao and cm.qtdd>0 and cm.tipoobjeto='lotefracao'  and cm.status='ABERTO')
		join lotefracao lfc on(lfc.idlotefracao = cm.idobjeto)																			
		join unidade ufc on(ufc.idunidade = lfc.idunidade  and ufc.cd = 'N')
 where cc.alteradoem > DATE_SUB(now(), INTERVAL 30 DAY) 
  and  (cc.qtdd>0 or cc.qtdc>0) and (cc.tipoobjeto = 'nfitem' or cc.tipoobjeto = 'lotefracao' or   cc.tipoobjeto is null )
  and cc.alteradoem BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND CURDATE()

   group by cm.idlotecons
   union
         -- receber e jogar fora
    select  r.idrateioitem,c.idlote,fd.idunidade ,round(cm.qtdd,2)  as qtdd,(select sum(qtdc)  from lotecons c where(c.idlotefracao=f.idlotefracao and c.qtdc>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')) as qtdc,l.qtdprod,l.valconvori,f.qtdini,f.qtd, l.idunidade as idunidadeest,i.qtd as qtdi,l.idempresa    
   from lotecons cc join lote ll on(ll.idlote=cc.idlote  and ll.idempresa!=8)
 join nfitem i on(ll.idnfitem = i.idnfitem)
 join rateioitem r on(r.idobjeto=i.idnfitem and r.tipoobjeto='nfitem')
 join prodserv p on(p.idprodserv =i.idprodserv and p.idunidadeest is not null and p.tipo='PRODUTO' )
 join lote l on(l.idnfitem=i.idnfitem and l.status!='CANCELADO'  )
 join lotefracao f on(f.idlote=l.idlote and f.idunidade=l.idunidade)
 join lotecons c on(c.idlotefracao=f.idlotefracao and c.qtdd>0 and c.tipoobjeto = 'lotefracao' and c.status!='INATIVO')
  join lotefracao fd on(fd.idlotefracao=c.idobjeto)
  join unidade u on(u.idunidade=fd.idunidade  and u.cd='Y')    
		  join lotecons cm on(cm.idlotefracao=fd.idlotefracao and cm.qtdd>0 and cm.idobjeto is null and cm.status='ABERTO') 
 where cc.alteradoem > DATE_SUB(now(), INTERVAL 30 DAY) 
  and  (cc.qtdd>0 or cc.qtdc>0) and (cc.tipoobjeto = 'nfitem' or cc.tipoobjeto = 'lotefracao' or   cc.tipoobjeto is null )
  and cc.alteradoem BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND CURDATE()

   group by cm.idlotecons
   union
  -- fabricar e jogar fora   
    select  r.idrateioitem,c.idlote,fd.idunidade ,round(((cm.qtdd*sm.qtdd)/lp.qtdprod),2)  as qtdd,(select sum(qtdc)  from lotecons c where(c.idlotefracao=f.idlotefracao and c.qtdc>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')) as qtdc,l.qtdprod,l.valconvori,f.qtdini,f.qtd, l.idunidade as idunidadeest,i.qtd as qtdi,l.idempresa    
   from lotecons cc join lote ll on(ll.idlote=cc.idlote  and ll.idempresa!=8)
 join nfitem i on(ll.idnfitem = i.idnfitem)
 join rateioitem r on(r.idobjeto=i.idnfitem and r.tipoobjeto='nfitem')
 join prodserv p on(p.idprodserv =i.idprodserv and p.idunidadeest is not null and p.tipo='PRODUTO' )
 join lote l on(l.idnfitem=i.idnfitem and l.status!='CANCELADO'  )
 join lotefracao f on(f.idlote=l.idlote and f.idunidade=l.idunidade)
 join lotecons c on(c.idlotefracao=f.idlotefracao and c.qtdd>0 and c.tipoobjeto = 'lotefracao' and c.status!='INATIVO')
  join lotefracao fd on(fd.idlotefracao=c.idobjeto)
  join unidade u on(u.idunidade=fd.idunidade  and u.cd='Y')    
	 join lotecons cm on(cm.idlotefracao=fd.idlotefracao and cm.qtdd>0 and cm.tipoobjeto='lote'  and cm.status='ABERTO')
                        join lote lp on(lp.idlote=cm.idobjeto)
                        join lotecons sm on(sm.idlote = cm.idobjeto and sm.qtdd>0 and sm.idobjeto is null  and sm.status='ABERTO' )  
                      
 where cc.alteradoem > DATE_SUB(now(), INTERVAL 30 DAY) 
  and  (cc.qtdd>0 or cc.qtdc>0) and (cc.tipoobjeto = 'nfitem' or cc.tipoobjeto = 'lotefracao' or   cc.tipoobjeto is null )
  and cc.alteradoem BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND CURDATE()

   group by  sm.idlotecons, cm.idlotecons 
   
  union 
  select r.idrateioitem,c.idlote,u.idunidade,c.qtdd,(select sum(qtdc) from lotecons c where(c.idlotefracao=f.idlotefracao and c.qtdc>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')) as qtdc,l.qtdprod,l.valconvori,f.qtdini,f.qtd, l.idunidade as idunidadeest,i.qtd as qtdi,l.idempresa   
  -- ,sum(c.qtdd) as debito,(l.qtdprod*l.valconvori),f.qtd as disponivel,sum(c.qtdd)/(l.qtdprod*l.valconvori)*100 as rateion, p.idunidadeest,f.qtd/(l.qtdprod*l.valconvori)*100  as rateioalm
 from lotecons cc join lote ll on(ll.idlote=cc.idlote  and ll.idempresa!=8)
 join nfitem i on(ll.idnfitem = i.idnfitem)
 join rateioitem r on(r.idobjeto=i.idnfitem and r.tipoobjeto='nfitem')
 join prodserv p on(p.idprodserv =i.idprodserv and p.idunidadeest is not null and p.tipo='PRODUTO'  )
 join lote l on(l.idnfitem=i.idnfitem and l.status!='CANCELADO' )
 join lotefracao f on(f.idlote=l.idlote and f.idunidade=l.idunidade)
 join lotecons c on(c.idlotefracao=f.idlotefracao and c.qtdd>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')
  -- join lotefracao fd on(fd.idlotefracao=c.idobjeto)
 join  unidade u on  (u.idtipounidade = 28 and u.status ='ATIVO' and u.idempresa = l.idempresa)
 where cc.alteradoem > DATE_SUB(now(), INTERVAL 32 DAY) 
  and (cc.qtdd>0 or cc.qtdc>0) and (cc.tipoobjeto = 'nfitem' or cc.tipoobjeto = 'lotefracao' or   cc.tipoobjeto is null )
  and cc.alteradoem BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND CURDATE()

   group by c.idlotecons
  union all
   select  r.idrateioitem,c.idlote,fd.idunidade ,c.qtdd,(select sum(qtdc)  from lotecons c where(c.idlotefracao=f.idlotefracao and c.qtdc>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')) as qtdc,l.qtdprod,l.valconvori,f.qtdini,f.qtd, l.idunidade as idunidadeest,i.qtd as qtdi,l.idempresa    
   -- sum(c.qtdd) as debito,(l.qtdprod*l.valconvori),f.qtd as disponivel,sum(c.qtdd)/(l.qtdprod*l.valconvori)*100 as rateion, p.idunidadeest,f.qtd/(l.qtdprod*l.valconvori)*100  as rateioalm
 from lotecons cc join lote ll on(ll.idlote=cc.idlote  and ll.idempresa!=8)
 join nfitem i on(ll.idnfitem = i.idnfitem)
 join rateioitem r on(r.idobjeto=i.idnfitem and r.tipoobjeto='nfitem')
 join prodserv p on(p.idprodserv =i.idprodserv and p.idunidadeest is not null and p.tipo='PRODUTO'  and p.insumo ='N')
 join lote l on(l.idnfitem=i.idnfitem and l.status!='CANCELADO'  )
 join lotefracao f on(f.idlote=l.idlote and f.idunidade=l.idunidade)
 join lotecons c on(c.idlotefracao=f.idlotefracao and c.qtdd>0 and c.tipoobjeto = 'lotefracao' and c.status!='INATIVO')
 join lotefracao fd on(fd.idlotefracao=c.idobjeto)
 join unidade u on(u.idunidade=fd.idunidade  and u.cd='N')  
 where cc.alteradoem > DATE_SUB(now(), INTERVAL 32 DAY) 
  and  (cc.qtdd>0 or cc.qtdc>0) and (cc.tipoobjeto = 'nfitem' or cc.tipoobjeto = 'lotefracao' or   cc.tipoobjeto is null )
  and cc.alteradoem BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND CURDATE()

   group by c.idlotecons
 union all
   select  r.idrateioitem,c.idlote,pl.idunidade ,c.qtdd,(select sum(qtdc)  from lotecons c where(c.idlotefracao=f.idlotefracao and c.qtdc>0 and c.tipoobjeto is null and c.idobjeto is null  and c.status!='INATIVO')) as qtdc,l.qtdprod,l.valconvori,f.qtdini,f.qtd, l.idunidade as idunidadeest,i.qtd as qtdi,l.idempresa   
 -- ,sum(c.qtdd) as debito,(l.qtdprod*l.valconvori),f.qtd as disponivel,sum(c.qtdd)/(l.qtdprod*l.valconvori)*100 as rateion, p.idunidadeest,f.qtd/(l.qtdprod*l.valconvori)*100  as rateioalm
 from lotecons cc join lote ll on(ll.idlote=cc.idlote  and ll.idempresa!=8)
 join nfitem i on(ll.idnfitem = i.idnfitem)
 join rateioitem r on(r.idobjeto=i.idnfitem and r.tipoobjeto='nfitem')
 join prodserv p on(p.idprodserv =i.idprodserv and p.idunidadeest is not null and p.tipo='PRODUTO' and p.insumo ='N')
 join lote l on(l.idnfitem=i.idnfitem and l.status!='CANCELADO' )
 join lotefracao f on(f.idlote=l.idlote and f.idunidade=l.idunidade)
 join lotecons c on(c.idlotefracao=f.idlotefracao and c.qtdd>0 and c.tipoobjeto = 'nfitem' and c.status!='INATIVO')
  join nfitem ii on(ii.idnfitem=c.idobjeto)
   join nf nn on(nn.idnf=ii.idnf )
   join plantelobjeto po on(po.idobjeto=nn.idpessoa and po.tipoobjeto = 'pessoa')
  join plantel pl on(pl.idplantel=po.idplantel)
 where  cc.alteradoem > DATE_SUB(now(), INTERVAL 32 DAY) 
  and (cc.qtdd>0 or cc.qtdc>0)  and (cc.tipoobjeto = 'nfitem' or cc.tipoobjeto = 'lotefracao' or   cc.tipoobjeto is null )
  and cc.alteradoem BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND CURDATE()
   group by c.idlotecons
 ) as u -- where u.idlote = 135313
   group by idrateioitem,idunidade  order by idrateioitem";


$res=d::b()->query($sql) or die("Erro ao buscar consumos".mysqli_error(d::b())."sql=".$sql);

$idrateioitem=0;
$arridrateioitem = array();
$arrdest = array();

while($row=mysqli_fetch_assoc($res)){

  $arridrateioitem[$row['idrateioitem']]=$row['idrateioitem'];

  if(!empty($row['idunidade']) and !empty($row['idunidade'])){

    // o primeiro idrateiitem atuliza o almoxarifado
    if($row['idunidadeest']==$row['idunidade']){
      $rateio=$row['rateioalm']+$row['rateion'];
      if($rateio>100){ $rateio=100;}

      atualizaRateio($row['idrateioitem'],$row['idunidade'], $rateio,$row['idempresa']);

    }else{
      if($row['idrateioitem']!=$idrateioitem){
        // atualizar rateio almoxarifado
        atualizaRateio($row['idrateioitem'],$row['idunidadeest'],$row['rateioalm'],$row['idempresa']);
      }
    
      atualizaRateio($row['idrateioitem'],$row['idunidade'],$row['rateion'],$row['idempresa']);
    }

    $idrateioitem=$row['idrateioitem'];
  }

}
 
limparateio($arridrateioitem);// limpar rateios não consumidos

echo("OK");

function limparateio($arridrateioitem){

  global $arrdest,$inspecionar;
  $arrrepetidos='';
  $virg='';
  foreach($arridrateioitem as $chave => $val) {
    $sql="select * from rateioitemdest where idrateioitem=".$val;
    $res=d::b()->query($sql) or die("Erro ao buscar rateioitemdest existente".mysqli_error(d::b())."sql=".$sql);
    while($row=mysqli_fetch_assoc($res)){
      if($arrdest[$val][$row['idrateioitemdest']]>0){
        echo('');
      }else{
          $arrrepetidos.=$virg.$row['idrateioitemdest'];
          $virg=',';
      }
    }
    
  }

  if($inspecionar=='Y'){
    echo($arrrepetidos);
  }elseif(!empty($arrrepetidos)){
    $sqld="delete from rateioitemdest 
            where idrateioitemdestorigem is null
            and status ='PENDENTE'
            and  idrateioitemdest in (".$arrrepetidos.")";
    $resd=d::b()->query($sqld) or die("Erro ao excluir invalidos".mysqli_error(d::b())."sql=".$sqld);
  }


 /*
 select * from rateioitemdest where idrateioitemdestorigem is null
and status ='PENDENTE'
and  idrateioitemdest in (1919130,1830548,1919120,1830540,1830542,1830544,1898943,1845958,1907369,1918373,1907372,1890813,1907383,1918362,1904029,1907395,1918381,1890645,1890666,1890663,1905764,1918365,1905822,1907459,1918380,1905765,1907460);

 */
}

function inseriRateioitemdest($idobjeto,$tipoobjeto,$valor,$idrateioitem,$idempresa){
  global $arrdest;

  $sqli="insert into rateioitemdest (idempresa,idrateioitem,idobjeto,tipoobjeto,valor,criadopor,criadoem,alteradopor,alteradoem) 
        value(".$idempresa.",".$idrateioitem.",".$idobjeto.",'".$tipoobjeto."','".$valor."','cron-atualizarateio',now(),'cron-atualizarateio',now())";
 
    $resi=d::b()->query($sqli) or die("Erro ao atualizar rateio".mysqli_error(d::b())."sql=".$sqli);
    $idrateioitemdest = mysqli_insert_id(d::b());

    $arrdest[$idrateioitem][$idrateioitemdest]=$idrateioitemdest;
                         

}

function atualizaRateio($idrateioitem,$idunidade,$rateio,$idempresa){
  global $arrdest;
  if($rateio>100){$rateio=100;}
  // Buscar rateio do alm
    $sqlalm="SELECT 
            *
            FROM
            rateioitemdest d
            WHERE
            d.idrateioitem = ".$idrateioitem."
                AND d.idobjeto = ".$idunidade."
                AND d.tipoobjeto = 'unidade'";

  $resalm=d::b()->query($sqlalm) or die("Erro ao buscar rateio destino".mysqli_error(d::b())."sql=".$sqlalm);
  $qtdalm=mysqli_num_rows($resalm);
  if($qtdalm<1 and $rateio > 0){
    inseriRateioitemdest($idunidade,'unidade',$rateio,$idrateioitem,$idempresa);
  }else{
    $atualizado=0;
    while($rowalm=mysqli_fetch_assoc($resalm)){      

            //buscar se ja foi cobrado
            if($rowalm['status']=="COBRADO" or $rowalm['status']=="EDITADO" or $rowalm['custeado']=='Y'){
              if($rowalm['valor']>100){$rowalm['valor']=100;}
              $rateio =   $rateio - $rowalm['valor'];
              $arrdest[$idrateioitem][$rowalm['idrateioitemdest']]=$rowalm['idrateioitemdest'];
            }elseif($atualizado==0 and  $rateio > 0){
              $sqlup="update rateioitemdest nf set valor='".$rateio."',alteradoem=now() where nf.idrateioitemdest =".$rowalm['idrateioitemdest'];
              $resup=d::b()->query($sqlup) or die("Erro ao atualizar rateio destino".mysqli_error(d::b())."sql=".$sqlup);
              $arrdest[$idrateioitem][$rowalm['idrateioitemdest']]=$rowalm['idrateioitemdest'];
              $atualizado=1;
            }else{
              $sqlup="delete from rateioitemdest nf where nf.idrateioitemdest =".$rowalm['idrateioitemdest'];
              $resup=d::b()->query($sqlup) or die("Erro ao deletar rateio destino".mysqli_error(d::b())."sql=".$sqlup);
              $atualizado=1;
            }
         
    }

    if($atualizado==0 and $rateio > 0 ){
      inseriRateioitemdest($idunidade,'unidade',$rateio,$idrateioitem,$idempresa);
    }
   
  }
}
?>