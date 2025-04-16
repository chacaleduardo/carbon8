<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$idempresa = $_GET["idempresa"];


if((!empty($vencimento_1) and !empty($vencimento_2)) and !empty($idempresa) ){
    $dataini = validadate($vencimento_1);
    $datafim = validadate($vencimento_2);
}else{
    die("Parâmetros necessário para comparação não enviados.");
}

?>

<html>
<head>
<title>Comparar</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
<script src="../inc/js/jquery/jquery-ui.js"></script>
<script src="../inc/js/jquery/jquery.autosize-min.js"></script>
<script src="../inc/css/bootstrap/js/bootstrap.min.js"></script>
<script src="../inc/js/functions.js"></script>



<link href="../inc/css/bootstrap/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css" />

<link href="../inc/css/bootstrap/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/css/fonts/laudo/laudofonts.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/css/fontawesome/font-awesome.min.css" media="all" rel="stylesheet" type="text/css" />

<link href="../inc/css/carbon.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/css/sislaudo.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/css/print.css" media="all" rel="stylesheet" type="text/css" />


<style>
	html {
    width: 760px;
}
.rotulo{
font-weight: bold;
font-size: 11px;
color:#848587;
}
.rotulob{
font-weight: bold;
font-size: 9px;
color:black;
}
.texto{
font-size: 11px;
}
.textoitem{
font-size: 9px;
}
.textoitem8{
font-size: 8px;
}

.box {
    display: table-cell;
    text-align: center;
    vertical-align: middle;
    width: 550px;
}
.box * {
    vertical-align: middle;
}

<?php if($_1_u_nf_idempresa == 2) { ?>
#_timbradocabecalho img
{
	height: 120px;
}

<?php } ?>

@media print{
	#rodapengeraarquivo{
		position: fixed;
		bottom: 0;
	}
}
</style>

</head>
<body >

    <div class="row">
		
	<?
		
		$sql = "select x.idcontapagar,sum(x.rateio) as rateio
                    from (
                            select  idcontapagar,ifnull(sum(rateio),0) as rateio
                                 from ( 
                                        select  
                                              cp.idcontapagar, round(((ifnull(l.vlrlote,0)*(c.qtdd))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                                            from   
                                                lotecons c
                                                join rateioitem ra  force index(idobj_tipoobj) on(ra.idobjeto =c.idlotecons  and ra.tipoobjeto='lotecons' )
                                                join rateioitemdest dt on(dt.idrateioitem=ra.idrateioitem and dt.tipoobjeto = 'unidade')
                                                join lote l on(l.idlote=c.idlote)  
                                                join prodserv p on(l.idprodserv=p.idprodserv)
                                                join nfitem ni on(l.idnfitem=ni.idnfitem  )
                                                join nf n  on(ni.idnf=n.idnf )
                                                join tipoprodserv i on(i.idtipoprodserv = p.idtipoprodserv  )
                                                join unidade  e on(e.idunidade=dt.idobjeto)
                                                join contaitem ci on(ci.idcontaitem=ni.idcontaitem)
                                                join contapagaritem cpi on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf')
                                                join contapagar cp on(cp.idcontapagar=cpi.idcontapagar and cp.tipo='D' and cp.valor>0 and cp.status!='INATIVO')
                                            where p.tipo='PRODUTO'
                                                  and cp.datareceb  between '".$dataini ."' and '".$datafim ."'    
                                                    and cp.idempresa=".$idempresa." 
                                                    and e.idempresa=".$idempresa."                                     
                                                and c.qtdd>0 
                                                and c.status!='INATIVO'
                                                and p.comprado='Y'  
                                    ) as u group by u.idcontapagar
                                        union
                                        select  idcontapagar,ifnull(sum(rateio),0) as valor
                                        from ( 
                                            select cp.idcontapagar,round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                                            from nf n
                                                    join nfitem i on(i.idnf=n.idnf)
                                                    join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                                    join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'unidade')
                                                    join unidade e on(e.idunidade=dt.idobjeto  )
                                                    join contaitem ci on(ci.idcontaitem=i.idcontaitem)                     
                                                    left join prodserv pr on(pr.idprodserv=i.idprodserv )
                                                    left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                                                    join contapagaritem cpi on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf')
                                                    join contapagar cp on(cp.idcontapagar=cpi.idcontapagar and cp.tipo='D' and cp.valor>0 and cp.status!='INATIVO')
                                                where n.tiponf in ('T','S','M','E','R','D','B','C')
                                                and cp.datareceb  between '".$dataini ."' and '".$datafim ."'      
                                                and cp.idempresa=".$idempresa." 
                                                and e.idempresa=".$idempresa."                                   
                                            ) as u group by u.idcontapagar       
                                            union
                                            select  idcontapagar,ifnull(sum(rateio),0) as valor
                                            from ( 
                                                select cp.idcontapagar,round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                                                    from nf n
                                                        join nfitem i on(i.idnf=n.idnf  )
                                                        join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                                        join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'unidade')
                                                        join unidade e on(e.idunidade=dt.idobjeto  )
                                                        join contaitem ci on(ci.idcontaitem=i.idcontaitem)                     
                                                        left join prodserv pr on(pr.idprodserv=i.idprodserv )
                                                        left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                                                        join contapagar cp on(cp.idobjeto=n.idnf and cp.tipo='D' and cp.tipoobjeto='nf' and cp.tipoespecifico in('NORMAL','REPRESENTACAO') and cp.valor>0 and cp.status!='INATIVO')
                                                where n.tiponf in ('T','S','M','E','R','D','B','C')
                                                    and cp.datareceb  between '".$dataini ."' and '".$datafim ."'     
                                                    and cp.idempresa=".$idempresa." 
                                                    and e.idempresa=".$idempresa." 
                                            )as u group by u.idcontapagar    
                                                                 
                    -- empresa rateio empresa
                                union all
                                select  idcontapagar,ifnull(sum(rateio),0) as valor
                                from ( 
                                    select  
                                            cp.idcontapagar,round(((ifnull(l.vlrlote,0)*(c.qtdd))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                                    from   
                                        lotecons c
                                        join rateioitem ra force index(idobj_tipoobj) on(ra.tipoobjeto='lotecons' and ra.idobjeto =c.idlotecons )
                                        join rateioitemdest dt on(dt.idrateioitem=ra.idrateioitem and dt.tipoobjeto = 'empresa')
                                        join lote l on(l.idlote=c.idlote)  
                                        join prodserv p on(l.idprodserv=p.idprodserv)
                                        join nfitem ni on(l.idnfitem=ni.idnfitem  )
                                        join nf n  on(ni.idnf=n.idnf )
                                        join tipoprodserv i on(i.idtipoprodserv = p.idtipoprodserv )
                                        join empresa  d on(d.idempresa=dt.idobjeto)
                                        join contaitem ci on(ci.idcontaitem=ni.idcontaitem)
                                        join contapagaritem cpi on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf')
                                        join contapagar cp on(cp.idcontapagar=cpi.idcontapagar and cp.tipo='D' and cp.valor>0 and cp.status!='INATIVO')
                                    where p.tipo='PRODUTO'
                                       and cp.datareceb  between '".$dataini ."' and '".$datafim ."'      
                                        and cp.idempresa=".$idempresa." 
                                        and d.idempresa=".$idempresa."                                 
                                        and c.qtdd>0
                                        and c.status!='INATIVO'
                                        and p.comprado='Y'
                                    )as u group by u.idcontapagar 
                                        union 
                                        select  idcontapagar,ifnull(sum(rateio),0) as valor
                                        from ( 
                                    select cp.idcontapagar,round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                                        from nf n 
                                            join nfitem i on(i.idnf=n.idnf )
                                            join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                            join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'empresa')
                                            join empresa e on(e.idempresa=dt.idobjeto) 
                                            join contaitem ci on(ci.idcontaitem=i.idcontaitem)                       
                                            left join prodserv pr on(pr.idprodserv=i.idprodserv )
                                            left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                                            join contapagaritem cpi on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf')
                                            join contapagar cp on(cp.idcontapagar=cpi.idcontapagar and cp.tipo='D' and cp.valor>0 and cp.status!='INATIVO')
                                        where n.tiponf in ('T','S','M','E','R','D','B','C')
                                            and cp.datareceb  between '".$dataini ."' and '".$datafim ."'       
                                            and cp.idempresa=".$idempresa." 
                                            and e.idempresa=".$idempresa."                                    
                                        )as u group by u.idcontapagar 
                                    union 
                                    select  idcontapagar,ifnull(sum(rateio),0) as valor
                                    from ( 
                                        select cp.idcontapagar,round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                                            from nf n 
                                                join nfitem i on(i.idnf=n.idnf   )
                                                join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                                join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'empresa')
                                                join empresa e on(e.idempresa=dt.idobjeto) 
                                                join contaitem ci on(ci.idcontaitem=i.idcontaitem)                       
                                                left join prodserv pr on(pr.idprodserv=i.idprodserv )
                                                left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                                                join contapagar cp on(cp.idobjeto=n.idnf and cp.tipoobjeto='nf' and cp.tipo='D' and cp.tipoespecifico in('NORMAL','REPRESENTACAO') and cp.valor>0 and cp.status!='INATIVO')
                                            where n.tiponf in ('T','S','M','E','R','D','B','C')
                                                and cp.datareceb  between '".$dataini ."' and '".$datafim ."'  
                                                and cp.idempresa=".$idempresa." 
                                                and e.idempresa=".$idempresa." 
                                        )as u group by u.idcontapagar       
                                        union all
            -- rateado outras empresas
            select  idcontapagar,ifnull(sum(rateio),0) as valor
                from (
                        select  
                             cp.idcontapagar ,sum(round(((ifnull(l.vlrlote,0)*(c.qtdd))*(dt.valor/100))/ifnull(n.parcelas,1),2)) as rateio
                        from   
                            lotecons c
                            join rateioitem ra  force index(idobj_tipoobj) on(ra.idobjeto =c.idlotecons  and ra.tipoobjeto='lotecons' )
                            join rateioitemdest dt on(dt.idrateioitem=ra.idrateioitem and dt.tipoobjeto = 'unidade')
                            join lote l on(l.idlote=c.idlote)  
                            join prodserv p on(l.idprodserv=p.idprodserv)
                            join nfitem ni on(l.idnfitem=ni.idnfitem  )
                            join nf n  on(ni.idnf=n.idnf )
                            join tipoprodserv i on(i.idtipoprodserv = p.idtipoprodserv  )
                            join unidade  e on(e.idunidade=dt.idobjeto and e.idempresa!=".$idempresa." )
                            join contaitem ci on(ci.idcontaitem=ni.idcontaitem)
                            join contapagaritem cpi on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf')
                            join contapagar cp on(cp.idcontapagar=cpi.idcontapagar and cp.valor>0 and cp.status!='INATIVO')
                        where p.tipo='PRODUTO'
                                and cp.datareceb between '".$dataini ."' and '".$datafim ."'    
                                and cp.idempresa=".$idempresa."                                
                                and c.qtdd>0 
                                and c.status!='INATIVO'
                                and cp.tipo='D'
                                
                                and p.comprado='Y' group by  cp.idcontapagar         
                        union all
                        select  cp.idcontapagar  ,sum(round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2)) as rateio
                            from nf n
                                join nfitem i on(i.idnf=n.idnf   )
                                join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'unidade')
                                join unidade e on(e.idunidade=dt.idobjeto  and e.idempresa!=".$idempresa." )
                                join contaitem ci on(ci.idcontaitem=i.idcontaitem)                     
                                left join prodserv pr on(pr.idprodserv=i.idprodserv )
                                left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                                join contapagaritem cpi on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf')
                                join contapagar cp on(cp.idcontapagar=cpi.idcontapagar and cp.valor>0 and cp.status!='INATIVO')
                            where n.tiponf in ('T','S','M','E','R','D','B','C')
                                and cp.datareceb between '".$dataini ."' and '".$datafim ."'      
                                and cp.idempresa=".$idempresa." 
                                and cp.tipo='D'                                  
                                 group by  cp.idcontapagar  
                        union all
                                select  cp.idcontapagar  ,sum(round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2)) as rateio
                                from nf n
                                    join nfitem i on(i.idnf=n.idnf   )
                                    join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                    join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'unidade')
                                    join unidade e on(e.idunidade=dt.idobjeto  and e.idempresa!=".$idempresa."  )
                                    join contaitem ci on(ci.idcontaitem=i.idcontaitem)                     
                                    left join prodserv pr on(pr.idprodserv=i.idprodserv )
                                    left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                                    join contapagar cp on(cp.idobjeto=n.idnf and cp.tipoobjeto='nf' and cp.tipoespecifico in('NORMAL','REPRESENTACAO') and cp.valor>0 and cp.status!='INATIVO')
                                where n.tiponf in ('T','S','M','E','R','D','B','C')
                                    and cp.datareceb between '".$dataini ."' and '".$datafim ."'   
                                    and cp.idempresa=".$idempresa." 
                                    and cp.tipo='D'    
                                   
                                    group by  cp.idcontapagar
                        union all
                        select  
                             cp.idcontapagar ,sum(round(((ifnull(l.vlrlote,0)*(c.qtdd))*(dt.valor/100))/ifnull(n.parcelas,1),2)) as rateio
                            from   
                                lotecons c
                                join rateioitem ra force index(idobj_tipoobj) on(ra.tipoobjeto='lotecons' and ra.idobjeto =c.idlotecons )
                                join rateioitemdest dt on(dt.idrateioitem=ra.idrateioitem and dt.tipoobjeto = 'empresa')
                                join lote l on(l.idlote=c.idlote)  
                                join prodserv p on(l.idprodserv=p.idprodserv)
                                join nfitem ni on(l.idnfitem=ni.idnfitem  )
                                join nf n  on(ni.idnf=n.idnf )
                                join tipoprodserv i on(i.idtipoprodserv = p.idtipoprodserv  )
                                join empresa  d on(d.idempresa=dt.idobjeto  and d.idempresa!=".$idempresa." )
                                join contaitem ci on(ci.idcontaitem=ni.idcontaitem)
                                join contapagaritem cpi on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf')
                                join contapagar cp on(cp.idcontapagar=cpi.idcontapagar and cp.valor>0 and cp.status!='INATIVO')
                            where p.tipo='PRODUTO'
                                    and cp.datareceb between '".$dataini ."' and '".$datafim ."'    
                                    and cp.idempresa=".$idempresa." 
                                    and c.qtdd>0
                                    and c.status!='INATIVO'
                                    and cp.tipo='D'                            
                                    and p.comprado='Y'  group by  cp.idcontapagar
                    union all
                    select cp.idcontapagar, sum(round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2)) as rateio
                                from nf n 
                                    join nfitem i on(i.idnf=n.idnf  )
                                    join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                    join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'empresa')
                                    join empresa e on(e.idempresa=dt.idobjeto  and e.idempresa!=".$idempresa." ) 
                                    join contaitem ci on(ci.idcontaitem=i.idcontaitem)                       
                                    left join prodserv pr on(pr.idprodserv=i.idprodserv )
                                    left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                                    join contapagaritem cpi on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf')
                                    join contapagar cp on(cp.idcontapagar=cpi.idcontapagar and cp.valor>0 and cp.status!='INATIVO')
                                where n.tiponf in ('T','S','M','E','R','D','B','C')
                                    and cp.datareceb between '".$dataini ."' and '".$datafim ."'    
                                   and cp.idempresa=".$idempresa." 
                                   and cp.tipo='D'
                                    
                                   group by cp.idcontapagar
                    union all
                            select cp.idcontapagar, sum(round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2)) as rateio
                                from nf n 
                                    join nfitem i on(i.idnf=n.idnf  )
                                    join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                    join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'empresa')
                                    join empresa e on(e.idempresa=dt.idobjeto  and e.idempresa!=".$idempresa." ) 
                                    join contaitem ci on(ci.idcontaitem=i.idcontaitem)                       
                                    left join prodserv pr on(pr.idprodserv=i.idprodserv )
                                    left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                                    join contapagar cp on(cp.idobjeto=n.idnf and cp.tipoobjeto='nf' and cp.tipoespecifico in('NORMAL','REPRESENTACAO') and cp.valor>0 and cp.status!='INATIVO')
                                where n.tiponf in ('T','S','M','E','R','D','B','C')
                                    and cp.datareceb between '".$dataini ."' and '".$datafim ."'    
                                    and cp.idempresa=".$idempresa."  
                                    and cp.tipo='D'       
                                    
                                    group by  cp.idcontapagar
                                        ) as u group by u.idcontapagar
                -- amoxarifado
                                    union all
                                    select  
                            idcontapagar,ifnull(sum(rateio),0) as valor
                            from (
                                    select  
                                       cp.idcontapagar,round(((ifnull(l.vlrlote,0)*(l.qtdprod))*(f.qtd/l.qtdprod))/ifnull(n.parcelas,1),2) as rateio
                                    from contapagar cp   
                                        join contapagaritem cpi on(cp.idcontapagar=cpi.idcontapagar)
                                        join nf n  on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf'and n.tiponf in ('T','S','M','E','R','D','B','C')) 
                                        join nfitem ni  on(ni.idnf=n.idnf )
                                        join lote l  on(l.idnfitem=ni.idnfitem and l.status!='CANCELADO')
                                        join prodserv p on(l.idprodserv=p.idprodserv)
                                        join lotefracao f on(f.idlote=l.idlote and f.status='DISPONIVEL' and f.qtd>0)
                                        join unidade u on(u.idunidade=f.idunidade and u.idtipounidade=3)			
                                        
                                    where p.tipo='PRODUTO'
                                    and cp.valor>0 and cp.status!='INATIVO'
                                    and cp.tipo='D'
                                            and cp.datareceb between '".$dataini ."' and '".$datafim ."'  
                                            and cp.idempresa=".$idempresa." 
                                
                            ) as u group by idcontapagar
                    -- descartados
                            union all
                            select  
                        idcontapagar,ifnull(sum(rateio),0) as valor
                        from (
                                select  
                                    cp.idcontapagar,round(((ifnull(l.vlrlote,0)*(l.qtdprod))*(c.qtdd/l.qtdprod))/ifnull(n.parcelas,1),2) as rateio
                                from contapagar cp   
                                    join contapagaritem cpi on(cp.idcontapagar=cpi.idcontapagar)
                                    join nf n  on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf'and n.tiponf in ('T','S','M','E','R','D','B','C')) 
                                    join nfitem ni  on(ni.idnf=n.idnf )
                                    join lote l  on(l.idnfitem=ni.idnfitem and l.status!='CANCELADO')
                                    join prodserv p on(l.idprodserv=p.idprodserv)
                                    join lotefracao f on(f.idlote=l.idlote )
                                    join unidade u on(u.idunidade=f.idunidade and u.idtipounidade=3)
                                    join lotecons c on(c.idlote=l.idlote and c.status!='INATIVO' and c.idlotefracao = f.idlotefracao and c.tipoobjeto is null and c.idobjeto is null and c.qtdd>0)
                                    
                                where p.tipo='PRODUTO'
                                and cp.valor>0 and cp.status!='INATIVO'
                                and cp.tipo='D'
                                and cp.datareceb between '".$dataini ."' and '".$datafim ."'     
                                and cp.idempresa=".$idempresa." 
                            
                        ) as u group by idcontapagar
                -- pedido
                        union all
                        select  
                idcontapagar,ifnull(sum(rateio),0) as valor
                from (
                        select  
                            cp.idcontapagar,round(((ifnull(l.vlrlote,0)*(l.qtdprod))*(c.qtdd/l.qtdprod))/ifnull(n.parcelas,1),2) as rateio
                        from contapagar cp   
                            join contapagaritem cpi on(cp.idcontapagar=cpi.idcontapagar)
                            join nf n  on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf'and n.tiponf in ('T','S','M','E','R','D','B','C')) 
                            join nfitem ni  on(ni.idnf=n.idnf )
                            join lote l  on(l.idnfitem=ni.idnfitem and l.status!='CANCELADO')
                            join prodserv p on(l.idprodserv=p.idprodserv)
                            join lotefracao f on(f.idlote=l.idlote )
                            join unidade u on(u.idunidade=f.idunidade and u.idtipounidade=3)
                            join lotecons c on(c.idlote=l.idlote and c.status!='INATIVO' and c.idlotefracao = f.idlotefracao and c.tipoobjeto = 'nfitem'
                            and c.idobjeto is not null and c.qtdd>0)
                            
                        where p.tipo='PRODUTO'
                        and cp.tipo='D'
                        and cp.valor>0 and cp.status!='INATIVO'
                        and cp.datareceb between '".$dataini ."' and '".$datafim ."'  
                        and cp.idempresa=".$idempresa." 
                    
                ) as u group by idcontapagar
                
                -- saida producao
                union all
                select  
                idcontapagar,ifnull(sum(rateio),0) as valor
                from (
                        select  
                           cp.idcontapagar,round(((ifnull(l.vlrlote,0)*(l.qtdprod))*(c.qtdd/l.qtdprod))/ifnull(n.parcelas,1),2) as rateio
                        from contapagar cp   
                            join contapagaritem cpi on(cp.idcontapagar=cpi.idcontapagar)
                            join nf n  on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf'and n.tiponf in ('T','S','M','E','R','D','B','C')) 
                            join nfitem ni  on(ni.idnf=n.idnf )
                            join lote l  on(l.idnfitem=ni.idnfitem and l.status!='CANCELADO')
                            join prodserv p on(l.idprodserv=p.idprodserv)
                            join lotefracao f on(f.idlote=l.idlote )
                            join unidade u on(u.idunidade=f.idunidade and u.idtipounidade=3)
                            join lotecons c on(c.idlote=l.idlote and c.status!='INATIVO' and c.idlotefracao = f.idlotefracao and c.tipoobjetoconsumoespec ='loteativ' and c.idobjetoconsumoespec is not null and c.qtdd>0)
                            
                        where p.tipo='PRODUTO'
                        and cp.tipo='D'
                        and cp.valor>0 and cp.status!='INATIVO'
                        and cp.datareceb between '".$dataini ."' and '".$datafim ."'  
                        and cp.idempresa=".$idempresa."               
                ) as u group by idcontapagar
                )as x   group by x.idcontapagar order by x.idcontapagar";	

                echo("<!--".$sql."-->");
		$qr = d::b()->query($sql) or die("Erro ao buscar itens da rateio:".mysqli_error());

     
		$qtdrows= mysqli_num_rows($qr);
		if($qtdrows>0)
		{					
			?>
    <div class="col-md-12">
            <div class="panel panel-default" style="margin-left: 1%;width: 98%;">
                <div class="panel-body">
                <table class="table table-striped planilha">

					<tr>
                        <th style="text-align: center;">ID Fatura</th>
                        <th style="text-align: center;">NF</th>
							
						<th style="text-align: center;">Rateio R$</th>						
						<th style="text-align: center;">Extrato R$</th>							
					</tr>

					<?	
					$i=1;
					$rateio=0.00;
					$extrato=0.00;
					$troca="S";
					while ($row = mysqli_fetch_array($qr)){
						$i = $i+1;                                
                        //mudar a cor da linha
                       

                        $sqlx="select idcontapagar,sum(valor) as valor,status,tipoobjeto,idobjeto from (
                            select c.idcontapagar,round((ni.total+ifnull(ni.valipi,0)+ifnull(n.frete,0))/ifnull(n.parcelas,1),2) as valor,c.status,c.tipoobjeto,c.idobjeto 
                                        from contapagar c 
                                        join agencia a on(a.idagencia=c.idagencia )
                                          join contapagaritem cpi on(cpi.idcontapagar=c.idcontapagar)				
                                        join nf n  on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf' and n.tiponf in ('T','S','M','E','R','D','B','C')) 
                                        join nfitem ni  on(ni.idnf=n.idnf )
                                        where c.idempresa=".$idempresa." 
                                        and c.tipo='D'
                                        and exists(
                                            select 1 from  prodserv p  
                                                join lote l on(l.idprodserv=p.idprodserv)  
                                                    where (l.idnfitem=ni.idnfitem and l.status!='CANCELADO')
                                            union
                                            select 1 from  rateioitem r force index(idobj_tipoobj) 
                                                where(r.tipoobjeto='nfitem' and r.idobjeto =ni.idnfitem )
                                        )
                                        and c.datareceb between   '".$dataini ."' and '".$datafim ."'           
                                        and c.status!='INATIVO'
                                        and c.tipoespecifico!='NORMAL' 
                                        and c.idcontapagar=".$row['idcontapagar']."
                                        and c.valor>0 
                                union all
                                       select c.idcontapagar,round((ni.total+ifnull(ni.valipi,0)+ifnull(n.frete,0))/ifnull(n.parcelas,1),2) as valor,c.status,c.tipoobjeto,c.idobjeto    
                                       from contapagar c 
                                       join agencia a on(a.idagencia=c.idagencia )                             
                                       join nf n  on(c.idobjeto=n.idnf and c.tipoobjeto='nf' and n.tiponf in ('T','S','M','E','R','D','B','C')) 
                                       join nfitem ni  on(ni.idnf=n.idnf )
                                       where c.idempresa=".$idempresa." 
                                       and c.tipo='D'
                                       and exists(
                                           select 1 from  prodserv p  
                                               join lote l on(l.idprodserv=p.idprodserv)  
                                                   where (l.idnfitem=ni.idnfitem and l.status!='CANCELADO')
                                           union
                                           select 1 from  rateioitem r force index(idobj_tipoobj) 
                                               where(r.tipoobjeto='nfitem' and r.idobjeto =ni.idnfitem )
                                       )
                                       and c.datareceb  between   '".$dataini ."' and '".$datafim ."'   
                                       and c.tipoespecifico='NORMAL'
                                       and c.status!='INATIVO' 
                                       and c.idcontapagar=".$row['idcontapagar']."
                                       and c.valor>0 
                                        and c.status!='INATIVO'                            
                                        union all
                                          select 
                                        c.idcontapagar,round((ni.total+ifnull(ni.valipi,0)+ifnull(n.frete,0))/ifnull(n.parcelas,1),2) as valor,c.status,c.tipoobjeto,c.idobjeto 
                                        from contapagar c 
                                         join agencia a on(a.idagencia=c.idagencia )
                                        join nf n on(n.idnf=c.idobjeto and c.tipoobjeto='nf')
                                          join nfitem ni  on(ni.idnf=n.idnf )
                                        where c.tipoespecifico='REPRESENTACAO' 
                                        and c.status!='INATIVO'
                                        and c.idempresa=".$idempresa." 
                                        and c.tipo='D'
                                        and c.idcontapagar=".$row['idcontapagar']."
                                        and exists(
                                            select 1 from  prodserv p  
                                                join lote l on(l.idprodserv=p.idprodserv)  
                                                    where (l.idnfitem=ni.idnfitem and l.status!='CANCELADO')
                                            union
                                            select 1 from  rateioitem r force index(idobj_tipoobj) 
                                                where(r.tipoobjeto='nfitem' and r.idobjeto =ni.idnfitem )
                                        )
                                        and c.datareceb between  '".$dataini ."' and '".$datafim ."'  
                                    ) as u where u.idcontapagar=".$row['idcontapagar']." group by u.idcontapagar order by u.idcontapagar";
                                    $qrx = d::b()->query($sqlx) or die("Erro ao buscar itens do extrato:".mysqli_error());
                                    echo("<!--".$sqlx."-->");
                                    $rowx = mysqli_fetch_array($qrx);
                   
                                   
                                   
                                    if($row["rateio"]!=$rowx["valor"]){                                      
                                        $rateio=$rateio+$row["rateio"];
                                        $extrato= $extrato+$rowx["valor"];
                                    
                                        if(!empty($rowx['idobjeto']) and ($rowx['tipoobjeto'] == "nf" 
                                        or $rowx['tipoobjeto'] == "gnre" or $rowx['tipoobjeto'] =='nf_darf'  
                                        or $rowx['tipoobjeto'] =='nf_ir'  or $rowx['tipoobjeto'] =='nf_inss'  
                                        or $rowx['tipoobjeto'] =='nf_issret')){
                        
                                    $sqlex = "select *
                                            from nf 
                                            where idnf =".$rowx['idobjeto'];
                                    
                                    $qrex = d::b()->query($sqlex) or die("Erro ao buscar dados da nota:".mysql_error());
                                    $rowr = mysqli_fetch_assoc($qrex);
                                    if($rowr["tiponf"]=='V'){ $vtiponf = "Venda";  $link="pedido";}
                                    if($rowr["tiponf"]=='C'){ $vtiponf = "Compra"; $link="nfentrada";}	
                                    if($rowr["tiponf"]=='O'){ $vtiponf = "Compra"; $link="nfentrada";}		
                                    if($rowr["tiponf"]=='S'){ $vtiponf = "Servi&ccedil;o";  $link="nfentrada";}
                                    if($rowr["tiponf"]=='T'){ $vtiponf = "Cte";  $link="nfentrada";}
                                    if($rowr["tiponf"]=='E'){ $vtiponf = "Consession&aacute;ria"; $link="nfentrada";}
                                    if($rowr["tiponf"]=='M'){ $vtiponf = "Manual/Cupom"; $link="nfentrada";}
                                    if($rowr["tiponf"]=='B'){ $vtiponf = "Recibo"; $link="nfentrada";}
                                    if($rowr["tiponf"]=='R'){ $vtiponf = "PJ"; $link="comprasrh";}
                                    if($rowr["tiponf"]=='F'){ $vtiponf = "Fatura"; $link="nfentrada"; $tipo='F';}
                                    if($rowr["tiponf"]=='D'){ $vtiponf = "Sócios"; $link="comprassocios"; $tipo='D';}                  
                                    }
                ?>
 
						<tr >
                            <td align="center" >
                                <div class="input-group input-group-sm">
                                    <div class="input-group input-group-sm">
                                        <label class="alert-warning"> <?=$row["idcontapagar"]?> - <?=$rowx['status']?></label>	                                                        
                                        <span class="input-group-addon pointer hoverazul" onclick="janelamodal('../?_modulo=contapagar&_acao=u&idcontapagar=<?=$row['idcontapagar']?>')" title="Ver Pedido">
                                            <i class="fa fa-bars pointer" ></i>
                                        </span>							
                                    </div>
                                </div>                               
							<td align="center" >
                                <div class="input-group input-group-sm">
                                    <div class="input-group input-group-sm">
                                        <label class="alert-warning"> <?=$vtiponf?> - <?=$rowr['status']?></label>	                                                        
                                        <span class="input-group-addon pointer hoverazul" onclick="janelamodal('../?_modulo=<?=$link?>&_acao=u&idnf=<?=$rowx['idobjeto']?>')" title="Ver Pedido">
                                            <i class="fa fa-bars pointer" ></i>
                                        </span>							
                                    </div>
                                </div> 
                            </td>
                            <td align="right" ><font style="font-weight: bold; "><?=number_format(tratanumero($row["rateio"]), 2, ',', '.');?></font></td>	
							<td align="right" ><font style="font-weight: bold; "><?=number_format(tratanumero($rowx["valor"]), 2, ',', '.');?></font></td>
						</tr> 	
					<? 	
                                    }
					}
				
					?>

                        <tr>
                            <td align="center" ></td>
                            <td align="center" >Total</td>
							<td align="right" ><font style="font-weight: bold;"><?=number_format(tratanumero($rateio), 2, ',', '.');?></font></td>	
						    <td align="right" ><font style="font-weight: bold;"><?=number_format(tratanumero($extrato), 2, ',', '.');?></font></td>
						</tr>
				
				</table>
			</div>
            </div>
            </div>
		<?}?>			
	
	
</body>
</html>

