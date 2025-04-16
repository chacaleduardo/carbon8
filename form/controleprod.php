﻿<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

$sqlx=" select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 7 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 7 DAY) as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 6 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 6 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 5 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 5 DAY) as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 4 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 4 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 3 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 3 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 2 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 2 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 1 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 1 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(now() , '%Y-%m-%d')) as vdata,'' as vlote,now() as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 1 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 1 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 2 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 2 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 3 DAY)  as odata        
        ";
$resx= d::b()->query($sqlx) or die("Falha ao buscar informações das datas : " . mysql_error() . "<p>SQL:".$sqlx);

$sql="SELECT * FROM(
            select 
                count(*) as qtd, dma(ifnull(la.execucao,now())) as vdata,GROUP_CONCAT( l.idlote SEPARATOR ',') as vlote,ifnull(la.execucao,now()) as odata
                from lote l 
                join prodserv p on(p.idprodserv=l.idprodserv and p.fabricado='Y' and p.venda='N' and p.especial ='Y') 
                join loteativ la on(la.idlote = l.idlote and la.status in ('PENDENTE','PROCESSANDO'))
                join loteformula f on(f.idlote =la.idlote and f.idprativ =la.idprativ)
                join prodservformulains fi on(fi.idprodservformulains=f.idprodservformulains)
                join prodservformula pf on(fi.idprodservformula=pf.idprodservformula and l.idprodservformula=pf.idprodservformula)
                join prodserv ps on(ps.idprodserv=f.idprodserv and p.status='ATIVO' AND f.idprodserv = ps.idprodserv and ps.especial='Y')
                join solfabitem sf on(sf.idsolfab=l.idsolfab and sf.tipoobjeto='lote')
                join lote s on(sf.idobjeto=s.idlote and s.status='APROVADO' and s.tipoobjetosolipor='resultado' and s.idprodserv=f.idprodserv)
                where l.status not in('APROVADO','REPROVADO','CANCELADO') and fi.status='ATIVO'
                ".getidempresa('l.idempresa','prodserv')."
                group by vdata
                UNION ALL        
                select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 7 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 7 DAY) as odata
                UNION ALL
                select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 6 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 6 DAY)  as odata
                UNION ALL
                select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 5 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 5 DAY) as odata
                UNION ALL
                select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 4 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 4 DAY)  as odata
                UNION ALL
                select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 3 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 3 DAY)  as odata
                UNION ALL
                select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 2 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 2 DAY)  as odata
                UNION ALL
                select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 1 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 1 DAY)  as odata
                UNION ALL
                select 0,dma(DATE_FORMAT(now() , '%Y-%m-%d')) as vdata,'' as vlote,now() as odata
                UNION ALL
                select 0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 1 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 1 DAY)  as odata
                 UNION ALL
                select 0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 2 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 2 DAY)  as odata
                UNION ALL
                select 0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 3 DAY)  as odata   
              ) AS u
              group by vdata order by odata";
		
echo '<!--'.$sql.'-->';
$res= d::b()->query($sql) or die("Falha ao buscar informações sementes pendentes : " . mysql_error() . "<p>SQL:".$sql);
$qtd=mysqli_num_rows($res);

$sqla="SELECT * FROM(
    select 
        count(*) as qtd, dma(ifnull(la.execucao,now())) as vdata,GROUP_CONCAT( l.idlote SEPARATOR ',') as vlote,ifnull(la.execucao,now()) as odata
        from lote l 
        join prodserv p on(p.idprodserv=l.idprodserv and p.fabricado='Y' and p.venda='N' and p.especial ='Y') 
        join loteativ la on(la.idlote = l.idlote and la.status = 'CONCLUIDO')
        join loteformula f on(f.idlote =la.idlote and f.idprativ =la.idprativ)
        join prodservformulains fi on(fi.idprodservformulains=f.idprodservformulains)
        join prodservformula pf on(fi.idprodservformula=pf.idprodservformula and l.idprodservformula=pf.idprodservformula)
        join prodserv ps on(ps.idprodserv=f.idprodserv and p.status='ATIVO' AND f.idprodserv = ps.idprodserv and ps.especial='Y')
        join solfabitem sf on(sf.idsolfab=l.idsolfab and sf.tipoobjeto='lote')
        join lote s on(sf.idobjeto=s.idlote and s.status='APROVADO' and s.tipoobjetosolipor='resultado' and s.idprodserv=f.idprodserv)
        where l.status not in('CANCELADO') and fi.status='ATIVO'
        ".getidempresa('l.idempresa','prodserv')."
        and  la.execucao between DATE_FORMAT(DATE_SUB(now(), INTERVAL 7 DAY) , '%Y-%m-%d')  and    DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 DAY) , '%Y-%m-%d')
        group by vdata
        UNION ALL        
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 7 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 7 DAY) as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 6 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 6 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 5 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 5 DAY) as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 4 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 4 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 3 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 3 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 2 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 2 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 1 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 1 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(now() , '%Y-%m-%d')) as vdata,'' as vlote,now() as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 1 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 1 DAY)  as odata
         UNION ALL
        select 0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 2 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 2 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 3 DAY)  as odata   
          ) AS u
          group by vdata order by odata;";
echo '<!-- a:'.$sqla.'-->';
$resa= d::b()->query($sqla) or die("Falha ao buscar informações sementes concluidas : " . mysql_error() . "<p>SQL:".$sqla);
$qtda=mysqli_num_rows($rea);

    $sqlb="SELECT * FROM(
        select 
        sum(l.qtdajust) as qtd,  dma(ifnull(la.execucao,now())) as vdata,GROUP_CONCAT( l.idlote SEPARATOR ',') as vlote,ifnull(la.execucao,now()) as odata
        from lote l 
        join prodserv p on(p.idprodserv=l.idprodserv and p.fabricado='Y' and p.venda='N' and p.especial ='Y') 
        join loteativ la  on(la.idlote = l.idlote)
        where l.status not in('APROVADO','REPROVADO','CANCELADO') 
        and exists (select 1 from  loteobj lo, tag t 
        where (  lo.idloteativ = la.idloteativ and la.status in ('PENDENTE','PROCESSANDO') and lo.tipoobjeto ='tag-EQUIPAMENTO' and t.idtag=lo.idobjeto  and t.idtagtipo=16 ))
        ".getidempresa('l.idempresa','prodserv')."
        group by vdata
        UNION ALL        
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 7 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 7 DAY) as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 6 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 6 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 5 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 5 DAY) as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 4 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 4 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 3 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 3 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 2 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 2 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 1 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 1 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(now() , '%Y-%m-%d')) as vdata,'' as vlote,now() as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 1 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 1 DAY)  as odata
         UNION ALL
        select 0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 2 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 2 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 3 DAY)  as odata   
          ) AS u
          group by vdata order by odata;";
		
echo '<!-- b: '.$sqlb .'-->';
$resb= d::b()->query($sqlb) or die("Falha ao buscar informações suspensoes pendentes : " . mysql_error() . "<p>SQL:".$sqlb);
$qtdb=mysqli_num_rows($resb);

$sqlb1="SELECT * FROM(
        select 
        sum(l.qtdajust) as qtd,  dma(ifnull(la.execucao,now())) as vdata,GROUP_CONCAT( l.idlote SEPARATOR ',') as vlote,ifnull(la.execucao,now()) as odata
        from lote l 
        join prodserv p on(p.idprodserv=l.idprodserv and p.fabricado='Y' and p.venda='N' and p.especial ='Y') 
        join loteativ la  on(la.idlote = l.idlote)
        where l.status not in('CANCELADO') 
        and exists (select 1 from  loteobj lo, tag t 
        where (  lo.idloteativ = la.idloteativ and la.status = 'CONCLUIDO' and lo.tipoobjeto ='tag-EQUIPAMENTO' and t.idtag=lo.idobjeto  and t.idtagtipo=16 ))
        and  la.execucao between DATE_FORMAT(DATE_SUB(now(), INTERVAL 7 DAY) , '%Y-%m-%d')  and    DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 DAY) , '%Y-%m-%d')
        ".getidempresa('l.idempresa','prodserv')."
        group by vdata
        UNION ALL        
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 7 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 7 DAY) as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 6 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 6 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 5 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 5 DAY) as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 4 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 4 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 3 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 3 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 2 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 2 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 1 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 1 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(now() , '%Y-%m-%d')) as vdata,'' as vlote,now() as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 1 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 1 DAY)  as odata
         UNION ALL
        select 0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 2 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 2 DAY)  as odata
        UNION ALL
        select 0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 3 DAY)  as odata   
          ) AS u
          group by vdata order by odata;";
echo '<!-- b1: '.$sqlb1 .'-->';
$resb1= d::b()->query($sqlb1) or die("Falha ao buscar informações suspensoes concluidas: " . mysql_error() . "<p>SQL:".$sqlb1);
$qtdb1=mysqli_num_rows($resb1);

$sqlc="SELECT * FROM(
        select 
           sum((if( l.qtdajust > 0,l.qtdajust,l.qtdpedida ) *pf.volumeformula)/pf.qtdpadraof) as qtda, sum(if(l.qtdprod > 0, l.qtdprod,l.qtdpedida )) as qtd,  dma(ifnull(la.execucao,now())) as vdata,GROUP_CONCAT( l.idlote SEPARATOR ',') as vlote,ifnull(la.execucao,now()) as odata
        from lote l 
        join prodservformula pf on(pf.idprodservformula=l.idprodservformula)
        join prodserv p on(p.idprodserv=l.idprodserv and p.fabricado='Y' and p.venda='Y' and p.especial ='Y') 
        join loteativ la  on(la.idlote = l.idlote  and (la.execucao > DATE_FORMAT(DATE_SUB(now(), INTERVAL 7 DAY) , '%Y-%m-%d')  or    la.execucao is null )  )
        where l.status not in('APROVADO','REPROVADO','CANCELADO') 
        and exists (select 1 from  loteobj lo, tag t 
        where (  lo.idloteativ = la.idloteativ and la.status in ('PENDENTE','PROCESSANDO') and lo.tipoobjeto ='tag-EQUIPAMENTO' and t.idtag=lo.idobjeto  and t.idtagtipo=186 ))
           ".getidempresa('l.idempresa','prodserv')."
        group by vdata
        UNION ALL        
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 7 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 7 DAY) as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 6 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 6 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 5 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 5 DAY) as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 4 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 4 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 3 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 3 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 2 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 2 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 1 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 1 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(now() , '%Y-%m-%d')) as vdata,'' as vlote,now() as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 1 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 1 DAY)  as odata
         UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 2 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 2 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 3 DAY)  as odata   
          ) AS u
          group by vdata order by odata;";
		
echo '<!-- c: '.$sqlc .'-->';
$resc= d::b()->query($sqlc) or die("Falha ao buscar informações vacinas pendentes: " . mysql_error() . "<p>SQL:".$sqlc);
$qtdc=mysqli_num_rows($resc);
		
$sqlc1="SELECT * FROM(
        select 
        sum((l.qtdajust*pf.volumeformula)/pf.qtdpadraof)  as qtda,sum(l.qtdprod) as qtd,  dma(ifnull(la.execucao,now())) as vdata,GROUP_CONCAT( l.idlote SEPARATOR ',') as vlote,ifnull(la.execucao,now()) as odata
        from lote l 
        join prodservformula pf on(pf.idprodservformula=l.idprodservformula)
        join prodserv p on(p.idprodserv=l.idprodserv and p.fabricado='Y' and p.venda='Y' and p.especial ='Y') 
        join loteativ la  on(la.idlote = l.idlote)
        where l.status not in('CANCELADO') 
        and exists (select 1 from  loteobj lo, tag t 
        where (  lo.idloteativ = la.idloteativ and la.status = 'CONCLUIDO' and lo.tipoobjeto ='tag-EQUIPAMENTO' and t.idtag=lo.idobjeto  and t.idtagtipo=186 ))
        and  la.execucao between DATE_FORMAT(DATE_SUB(now(), INTERVAL 7 DAY) , '%Y-%m-%d')  and    DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 DAY) , '%Y-%m-%d')
        ".getidempresa('l.idempresa','prodserv')."
        group by vdata
        UNION ALL        
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 7 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 7 DAY) as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 6 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 6 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 5 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 5 DAY) as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 4 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 4 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 3 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 3 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 2 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 2 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_SUB(now(), INTERVAL 1 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_SUB(now(), INTERVAL 1 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(now() , '%Y-%m-%d')) as vdata,'' as vlote,now() as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 1 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 1 DAY)  as odata
         UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 2 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 2 DAY)  as odata
        UNION ALL
        select 0,0,dma(DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 DAY) , '%Y-%m-%d')) as vdata,'' as vlote,DATE_ADD(now(), INTERVAL 3 DAY)  as odata   
          ) AS u
          group by vdata order by odata;";
echo '<!-- c1: '.$sqlc1 .'-->';
$resc1= d::b()->query($sqlc1) or die("Falha ao buscar informações vacinas concluidas : " . mysql_error() . "<p>SQL:".$sqlc1);
$qtdc1=mysqli_num_rows($resc1);

?>

<style>
@media print { 
  * {
    -webkit-transition: none !important;
    transition: none !important;
  }
}
@media screen{	
    .quebrapagina{
        page-break-before:always;
        border: 2px solid #c0c0c0;
        width: 200%;
        margin: 1.5cm -100px;
    }
    .rot{
        color: gray;
    }
}

[class*='5']{width: 5%;}
[class*='10']{width: 9%;}
[class*='15']{width: 15%;}
[class*='20']{width: 20%;}
[class*='25']{width: 25%;}
[class*='30']{width: 30%;}
[class*='35']{width: 35%;}
[class*='40']{width: 39.99%;}
[class*='45']{width: 45%;}
[class*='50']{width: 50%;}
[class*='55']{width: 55%;}
[class*='60']{width: 60%;}
[class*='65']{width: 65%;}
[class*='70']{width: 70%;}
[class*='75']{width: 75%;}
[class*='80']{width: 80%;}
[class*='85']{width: 85%;}
[class*='90']{width: 90%;}
[class*='95']{width: 95%;}
[class*='100']{width: 100%;}
header{
	 background-color: white;
	 top: 0;
	 height: 1cm;
	 line-height: 1cm;
	 display: table;
}
header + hr{
	margin: 0;
}
.logosup{
	height: inherit;
	line-height: inherit;
	display: table-cell;
}
.logosup img{
	height: 0.5cm;
	vertical-align: middle;
}
.titulodoc{
	height: inherit;
	line-height: inherit;
	display: table-cell;
	text-align: center;
	font-size: 0.3cm;
	font-weight: bold;
}
.row{
    display: flex !important;
	table-layout: fixed;
	width: 99%;
	margin: 0mm 0mm;
}
.linhainferior{
	border-bottom: 1px solid #f8f8f8;
}
.col{
	display: table-cell;
	white-space: nowrap;
	padding: 1.5mm 1mm;
        text-align-last: center;
    border-right: 1px #80808073 dotted;
}
.row.grid .col{
	border: 1px solid #777777;
	
}
.row.grid .col:first-child{
	border-top: 1px solid silver;
}
.col.grupo .titulogrupo{
	margin: 0px;
	
	color: #777777;
	font-weight: bold;
	margin-bottom: 2mm;
    
}
.rot{
	color: #777777;
	overflow: hidden;
	font-size: 12px;
}
.quebralinha{
	white-space: normal;
        height: 65px;
}
.quebralinhacab{
	white-space: normal;
        height: 20px;
}
[class*='margem0.0']{
	margin: 0 0;
}
.sublinhado{
	border-bottom: 1px dashed gray;
}
</style>

	<div class="row margem 0.0">		
            <div class="titulodoc">Controle da Produção</div>		
	</div>

<div class="panel panel-default" style="margin-top:30px;">
    <div class="row " style="width: 100%;">
<?
$cortr='white';
while ($rowx=mysqli_fetch_assoc($resx)){
   
?> 
        <div class="col grupo 10 ">
            <div class="quebralinhacab">
            <div class="titulogrupo" ><?echo $rowx['vdata'];?></div>
            
            </div>
        </div>

<?
}
?>
        <hr>
    </div>
    <!--<?=$sql?>-->
    <div class="panel-heading">Semente(s) Pedente(s)</div>
    <div class="row " style="background-color: <?=$cortr?>;width: 100%;">
<?

while ($row=mysqli_fetch_assoc($res)){
    $arrlote = explode(",",$row['vlote']);    
?> 
        <div class="col grupo 10 oVisita">
            <div class=" quebralinha">
            <div class="titulogrupo" data-target="webuiPopover0"><br><?//echo $row['vdata'];?></div>
            <?if($row["qtd"]>0){?>
            <?=number_format(tratanumero($row["qtd"]), 2, ',', '.');?>
            <?}?>
            </div>
        </div>
        <div class="webui-popover-content">
        <?
        if($row["qtd"]>0){
        foreach ($arrlote as $idlote) {
            $partida= traduzid('lote', 'idlote', 'partida', $idlote);
            $exercicio= traduzid('lote', 'idlote', 'exercicio', $idlote);
            $idformalizacao= traduzid('formalizacao', 'idlote', 'idformalizacao', $idlote);
?> 
            <i class="pointer hoverazul" title="Lote" onclick="janelamodal('?_modulo=formalizacao&_acao=u&idformalizacao=<?=$idformalizacao?>')"><?=$partida?>/<?=$exercicio?></i>
             &nbsp; &nbsp;
<?        
        }
    }
        ?>
	</div> 
<?
}
?>
        <hr>
    </div>
<!--<?=$sqla?>-->
    <div class="panel-heading">Semente(s) Concluida(s)</div>
    <div class="row" style="background-color: <?=$cortr?>;width: 100%;">
<?
while ($rowa=mysqli_fetch_assoc($resa)){ 
    $arrlotea = explode(",",$rowa['vlote']);
?>     
	
           <div class="col grupo 10 oVisita">
            <div class=" quebralinha">
            <div class="titulogrupo"><br><?//echo $rowa['vdata'];?></div>
            <?if($rowa["qtd"]>0){?>
            <?=number_format(tratanumero($rowa["qtd"]), 2, ',', '.');?>
            <?}?>
            </div>
           </div>
                <div class="webui-popover-content">
        <?
        if($rowa["qtd"]>0){
        foreach ($arrlotea as $idlote) {
            $partida= traduzid('lote', 'idlote', 'partida', $idlote);
            $exercicio= traduzid('lote', 'idlote', 'exercicio', $idlote);
            $idformalizacao= traduzid('formalizacao', 'idlote', 'idformalizacao', $idlote);
?> 
            <i class="pointer hoverazul" title="Lote" onclick="janelamodal('?_modulo=formalizacao&_acao=u&idformalizacao=<?=$idformalizacao?>')"><?=$partida?>/<?=$exercicio?></i>
             &nbsp; &nbsp;
<?        
        }
    }
        ?>
	</div> 
	
<?
}
?>
        <hr>
        </div> 
    
     <!--<?=$sqlb?>-->
    <div class="panel-heading">Suspensão(ões) Pedente(s)</div>
    <div class="row" style="background-color: <?=$cortr?>;width: 100%;">
<?
while ($rowb=mysqli_fetch_assoc($resb)){ 
    $arrloteb = explode(",",$rowb['vlote']);
?>
    
        <div class="col grupo 10 oVisita">
        <div class=" quebralinha">
        <div class="titulogrupo"><br><?//echo $rowb['vdata'];?></div>
        <?if($rowb["qtd"]>0){?>
        <?=number_format(tratanumero($rowb["qtd"]), 2, ',', '.');?>
        <?}?>
        </div>  
        </div>
        <div class="webui-popover-content">
        <?
        if($rowb["qtd"]>0){
        foreach ($arrloteb as $idlote) {
            $partida= traduzid('lote', 'idlote', 'partida', $idlote);
            $exercicio= traduzid('lote', 'idlote', 'exercicio', $idlote);
            $idformalizacao= traduzid('formalizacao', 'idlote', 'idformalizacao', $idlote);
?> 
            <i class="pointer hoverazul" title="Lote" onclick="janelamodal('?_modulo=formalizacao&_acao=u&idformalizacao=<?=$idformalizacao?>')"><?=$partida?>/<?=$exercicio?></i>
             &nbsp; &nbsp;
<?        
        }
    }
        ?>
	</div> 
      
    
<?
}
?>
        <hr>
        </div>
         <!--<?=$sql1?>-->
    <div class="panel-heading">Suspensão(ões) Concluida(s)</div>
    <div class="row" style="background-color: <?=$cortr?>;width: 100%;">
<?
while ($rowb1=mysqli_fetch_assoc($resb1)){ 
    $arrloteb1 = explode(",",$rowb1['vlote']);
?> 
	<div class="col grupo 10 oVisita">
            <div class=" quebralinha">
            <div class="titulogrupo"><br><?//echo $rowb1['vdata'];?></div>
            <?if($rowb1["qtd"]>0){?>
            <?=number_format(tratanumero($rowb1["qtd"]), 2, ',', '.');?>
            <?}?>
            </div>
        </div>
        <div class="webui-popover-content">
        <?
        if($rowb1["qtd"]>0){
        foreach ($arrloteb1 as $idlote) {
            $partida= traduzid('lote', 'idlote', 'partida', $idlote);
            $exercicio= traduzid('lote', 'idlote', 'exercicio', $idlote);
            $idformalizacao= traduzid('formalizacao', 'idlote', 'idformalizacao', $idlote);
?> 
            <i class="pointer hoverazul" title="Lote" onclick="janelamodal('?_modulo=formalizacao&_acao=u&idformalizacao=<?=$idformalizacao?>')"><?=$partida?>/<?=$exercicio?></i>
             &nbsp; &nbsp;
<?        
        }
    }
        ?>
	</div> 
<?
}
?>
        <hr>
    </div>
             <!--<?=$sqlc?>-->
    <div class="panel-heading">Vacina(s) Pendente(s)</div>
    <div class="row" style="background-color: <?=$cortr?>;width: 100%;">
<?
while ($rowc=mysqli_fetch_assoc($resc)){ 
    $arrlotec = explode(",",$rowc['vlote']);
?> 
	<div class="col grupo 10 oVisita">
            <div class=" quebralinha">
            <div class="titulogrupo"><br><?//echo $rowc['vdata'];?></div>
            <?if($rowc["qtda"]>0){?>
            <?=number_format(tratanumero($rowc["qtda"]), 2, ',', '.');?>
            <?}?><br>
            <?if($rowc["qtd"]>0){?>
            <?=number_format(tratanumero($rowc["qtd"]), 2, ',', '.');?>-FR
            <?}?>

            </div>
        </div>
        <div class="webui-popover-content">
            
        <?
    if($rowc["qtd"]>0){
        foreach ($arrlotec as $idlote) {
            $partida= traduzid('lote', 'idlote', 'partida', $idlote);
            $exercicio= traduzid('lote', 'idlote', 'exercicio', $idlote);
            $idformalizacao= traduzid('formalizacao', 'idlote', 'idformalizacao', $idlote);
?> 
            <i class="pointer hoverazul" title="Lote" onclick="janelamodal('?_modulo=formalizacao&_acao=u&idformalizacao=<?=$idformalizacao?>')"><?=$partida?>/<?=$exercicio?></i>
            &nbsp; &nbsp;
<?        
        }
    }
        ?>
	</div>         
	
<?
}
?>
        <hr>
    </div>
        <!--<?=$sqlc1?>-->
    <div class="panel-heading">Vacina(s) Concluida(s)</div>
    <div class="row" style="background-color: <?=$cortr?>;width: 100%;">
<?
while ($rowc1=mysqli_fetch_assoc($resc1)){ 
    $arrlotec1 = explode(",",$rowc1['vlote']);
?> 
	<div class="col grupo 10 oVisita">
            <div class=" quebralinha">
            <div class="titulogrupo"><br><?//echo $rowc1['vdata'];?></div>
             <?if($rowc1["qtda"]>0){?>
            <?=number_format(tratanumero($rowc1["qtda"]), 2, ',', '.');?>
            <?}?><br>
            <?if($rowc1["qtd"]>0){?>
            <?=number_format(tratanumero($rowc1["qtd"]), 2, ',', '.');?>-FR
            <?}?>
           
            </div>
        </div>
        <div class="webui-popover-content">
        <?
        if($rowc1["qtd"]>0){
            foreach ($arrlotec1 as $idlote) {
                $partida= traduzid('lote', 'idlote', 'partida', $idlote);
                $exercicio= traduzid('lote', 'idlote', 'exercicio', $idlote);
                $idformalizacao= traduzid('formalizacao', 'idlote', 'idformalizacao', $idlote);
    ?> 
                <i class="pointer hoverazul" title="Lote" onclick="janelamodal('?_modulo=formalizacao&_acao=u&idformalizacao=<?=$idformalizacao?>')"><?=$partida?>/<?=$exercicio?></i>
                &nbsp; &nbsp;
    <?        
            }
        }
        ?>
	</div> 
<?
}
?>
        <hr>
    </div>
</div>
	<br>
        <br>
    <script>
        CB.preLoadUrl = function(){
	//Como o carregamento é via ajax, os popups ficavam aparecendo apà³s o load
	$(".webui-popover").remove();
}

$(".oVisita").webuiPopover({
	trigger: "hover"
	,placement: "bottom"
        ,width:300
	,delay: {
        show: 300,
        hide: 0
    }
});
    </script>