<?
require_once("../inc/php/validaacesso.php");

baseToGet($_GET["_filtros"]);

if (!empty($_GET['_fds'])) {
    $_fds = explode('-', $_GET['_fds']);
    $vencimento_1     = $_fds[0];
    $vencimento_2     = $_fds[1];
} else {
    $vencimento_1     = $_GET["vencimento_1"];
    $vencimento_2     = $_GET["vencimento_2"];
}

		
$idtipoprodserv = $_GET["idtipoprodserv"];
$idcontaitem = $_GET["idcontaitem"];
$idprodserv = $_GET["idprodserv"];
$pesquisa = $_GET["pesquisa"];
$idsgdepartamento = $_GET["idsgdepartamento"];
//produto transferido a partir do almoxarifado e produtos manuais
if(empty($_GET["idempresa"])){
	$idempresa = ' = '. cb::idempresa();
} else {
	
    $getIds = $_GET["idempresa"];
    $_val=explode(',', $getIds);
    if(count($_val)>=1){
        $arrlenght=count($_val)-1;
        foreach ($_val as $key => $value) {
            if($key==$arrlenght){
                $virg='';
            } else {
                $virg=',';
            }
            $_value.="'".$value."'".$virg;
        }
    }

    $idempresa = " in (" . $_value . ")" ;

}


if (!empty($idsgdepartamento) and $idsgdepartamento != 'undefined') {
$clausulaw.= " and s.idsgdepartamento = ".$idsgdepartamento;
}


if (!empty($idcontaitem) and $idcontaitem != 'undefined') {
$clausulaw.= " and ci.idcontaitem = ".$idcontaitem;
}


?>
<html>

<head>
    <title>Rateio</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <link href="../inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />
    <style type="text/css" class='normal'>
        table {
            page-break-inside: auto
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto
        }

        thead {
            display: table-header-group
        }

        tfoot {
            display: table-footer-group
        }
    </style>
</head>

<body>

    <?

    if ($_GET and (!empty($vencimento_1) and !empty($vencimento_2)) or (!empty($idprodserv))) {

        if (!empty($vencimento_1) or !empty($vencimento_2)) {
            $dataini = validadate($vencimento_1);
            $datafim = validadate($vencimento_2);

            if ($dataini and $datafim) {
                // $clausula .= " and ( n.dtemissao  BETWEEN '" . $dataini ." 00:00:00' and '" .$datafim ." 23:59:59')"."  ";
               // $clausulac.=" and (c.criadoem  BETWEEN  '" . $dataini ." 00:00:00' and '" .$datafim ." 23:59:59')  ";
               $clausula.=" and cp.datareceb between '".$dataini ."' and '".$datafim ."' ";
            } else {
                die("Datas n&atilde;o V&aacute;lidas!");
            }
        }
/*
        if (!empty($idtipoprodserv)) {
            $stridtipoprodserv = " and i.idtipoprodserv =" . $idtipoprodserv . " ";
        } else {
            $stridtipoprodserv = "";
        }
        */
          
                $sql="select  
                tipo,idempresa,qtd,un,contaitem,idcontaitem,idtipo,idrateio,idrateioitem,idrateioitemdest,idnf,nnfe,idobjeto,tipoobjeto,idtipoprodserv,tipoprodserv,descr,vlrlote,rateio,valor,empresa,dtemissao
            from (
                    select 'nfitem' as tipo,cp.idempresa,i.qtd,ifnull(i.un,pr.un) as un,ci.contaitem,ci.idcontaitem,i.idnfitem as idtipo,r.idrateio,r.idrateioitem,dt.idrateioitemdest,n.idnf,n.nnfe,dt.idobjeto,'aratiar' as tipoobjeto,i.idtipoprodserv,tp.tipoprodserv,ifnull(pr.descr,i.prodservdescr) as descr,i.vlritem as vlrlote,
                   -- round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                    round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor)*(dt.valor/100),2) as rateio

                    ,dt.valor,e.empresa,cp.datareceb as dtemissao
                        from nf n 
                            join nfitem i on(i.idnf=n.idnf ".$stridtipoprodserv." ".$stridprodserv.")
                            join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                            join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'empresa')
                            join empresa e on(e.idempresa=dt.idobjeto) 
                            join contaitem ci on(ci.idcontaitem=i.idcontaitem)                       
                            left join prodserv pr on(pr.idprodserv=i.idprodserv )
                            left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                            join contapagar cp on(cp.idobjeto=n.idnf and cp.tipoobjeto='nf' and cp.tipo='D' and cp.tipoespecifico !='AGRUPAMENTO' and cp.valor>0 and cp.status!='INATIVO')
                        where  n.tiponf not in('S','R','O')
                            ".$clausula."
                            and e.idempresa ".$idempresa." 
                    UNION ALL
                    select 'nfitem' as tipo,cp.idempresa,i.qtd,ifnull(i.un,pr.un) as un,ci.contaitem,ci.idcontaitem,i.idnfitem as idtipo,r.idrateio,r.idrateioitem,dt.idrateioitemdest,n.idnf,n.nnfe,dt.idobjeto,'aratiar' as tipoobjeto,i.idtipoprodserv,tp.tipoprodserv,ifnull(pr.descr,i.prodservdescr) as descr,i.vlritem as vlrlote,
                    -- round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                    round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cpi.valor)*(dt.valor/100),2) as rateio
                    ,dt.valor,e.empresa,cp.datareceb as dtemissao
                    from nf n 
                        join nfitem i on(i.idnf=n.idnf ".$stridtipoprodserv." ".$stridprodserv.")
                        join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                        join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'empresa')
                        join empresa e on(e.idempresa=dt.idobjeto) 
                        join contaitem ci on(ci.idcontaitem=i.idcontaitem)                       
                        left join prodserv pr on(pr.idprodserv=i.idprodserv )
                        left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                        join contapagaritem cpi on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf')
                        join contapagar cp on(cp.idcontapagar=cpi.idcontapagar and cp.tipoespecifico ='AGRUPAMENTO' and cp.tipo='D' and cp.valor>0 and cp.status!='INATIVO')
                    where n.tiponf not in('S','R','O')
                        ".$clausula."
                        and e.idempresa ".$idempresa."    
                    UNION ALL
                        select 'nfitem' as tipo,cp.idempresa,i.qtd,ifnull(i.un,pr.un) as un,ci.contaitem,ci.idcontaitem,i.idnfitem as idtipo,r.idrateio,r.idrateioitem,dt.idrateioitemdest,n.idnf,n.nnfe,dt.idobjeto,'aratiar' as tipoobjeto,i.idtipoprodserv,tp.tipoprodserv,ifnull(pr.descr,i.prodservdescr) as descr,i.vlritem as vlrlote,
                        -- round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                        round(((((ifnull(i.total,0))*(n.total/n.subtotal))/n.total)*cp.valor)*(dt.valor/100),2) as rateio
                        ,dt.valor,e.empresa,cp.datareceb as dtemissao
                        from nf n 
                            join nfitem i on(i.idnf=n.idnf ".$stridtipoprodserv." ".$stridprodserv.")
                            join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                            join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'empresa')
                            join empresa e on(e.idempresa=dt.idobjeto) 
                            join contaitem ci on(ci.idcontaitem=i.idcontaitem)                       
                            left join prodserv pr on(pr.idprodserv=i.idprodserv )
                            left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                            join contapagar cp on(cp.idobjeto=n.idnf and cp.tipoobjeto='nf' and cp.tipo='D' and cp.tipoespecifico !='AGRUPAMENTO' and cp.valor>0 and cp.status!='INATIVO')
                        where n.tiponf in('S','R')
                            ".$clausula."
                            and e.idempresa ".$idempresa." 
                    UNION ALL
                    select 'nfitem' as tipo,cp.idempresa,i.qtd,ifnull(i.un,pr.un) as un,ci.contaitem,ci.idcontaitem,i.idnfitem as idtipo,r.idrateio,r.idrateioitem,dt.idrateioitemdest,n.idnf,n.nnfe,dt.idobjeto,'aratiar' as tipoobjeto,i.idtipoprodserv,tp.tipoprodserv,ifnull(pr.descr,i.prodservdescr) as descr,i.vlritem as vlrlote,
                   -- round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                   round(((((ifnull(i.total,0))*(n.total/n.subtotal))/n.total)*cpi.valor)*(dt.valor/100),2) as rateio
                    ,dt.valor,e.empresa,cp.datareceb as dtemissao
                    from nf n 
                        join nfitem i on(i.idnf=n.idnf ".$stridtipoprodserv." ".$stridprodserv.")
                        join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                        join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'empresa')
                        join empresa e on(e.idempresa=dt.idobjeto) 
                        join contaitem ci on(ci.idcontaitem=i.idcontaitem)                       
                        left join prodserv pr on(pr.idprodserv=i.idprodserv )
                        left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                        join contapagaritem cpi on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf')
                        join contapagar cp on(cp.idcontapagar=cpi.idcontapagar and cp.tipoespecifico ='AGRUPAMENTO' and cp.tipo='D' and cp.valor>0 and cp.status!='INATIVO')
                    where n.tiponf in('S','R')
                        ".$clausula."
                        and e.idempresa ".$idempresa." 
                
                             
                ) as u
                order by tipoobjeto,empresa,idobjeto,contaitem,tipoprodserv,descr,dtemissao";

        echo "<!-- aratiar ";
        echo $sql;
        echo "-->";
        if (!empty($sql)) {
          //  $res =  d::b()->query($sql) or die("Falha ao pesquisar consumos sem rateio: " . mysqli_error() . "<p>SQL: $sql");
         //   corpo($res, 'aratiar');
        }

        if ($pesquisa == 'UNIDADE') {

            $sql="select  
            tipo,idempresa,qtd,un,contaitem,idcontaitem,idtipo,idrateio,idrateioitem,idrateioitemdest,idnf,nnfe,idobjeto,tipoobjeto,idtipoprodserv,tipoprodserv,descr,vlrlote,rateio,valor,empresa,dtemissao
        from (
                   
           
            select 'nfitem' as tipo,cp.idempresa,i.qtd,ifnull(i.un,pr.un) as un,ci.contaitem,ci.idcontaitem,i.idnfitem as idtipo,r.idrateio,r.idrateioitem,dt.idrateioitemdest,n.idnf,n.nnfe,dt.idobjeto,dt.tipoobjeto,i.idtipoprodserv,tp.tipoprodserv,ifnull(pr.descr,i.prodservdescr) as descr,i.vlritem as vlrlote,
            -- round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
            round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor)*(dt.valor/100),2) as rateio
            ,dt.valor,e.unidade as empresa,cp.datareceb as dtemissao
                from nf n
                    join nfitem i on(i.idnf=n.idnf ".$stridtipoprodserv." ".$stridprodserv." )
                    join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                    join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'unidade')
                    join unidade e on(e.idunidade=dt.idobjeto  )
                    join contaitem ci on(ci.idcontaitem=i.idcontaitem)                     
                    left join prodserv pr on(pr.idprodserv=i.idprodserv )
                    left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                    join contapagar cp on(cp.idobjeto=n.idnf and cp.tipo='D' and cp.tipoobjeto='nf' and cp.tipoespecifico != 'AGRUPAMENTO' and cp.valor>0 and cp.status!='INATIVO')
                where n.tiponf not in('S','R','O')
                ".$clausula."
                and e.idempresa ".$idempresa." 
                UNION  ALL 
                select 'nfitem' as tipo,cp.idempresa,i.qtd,ifnull(i.un,pr.un) as un,ci.contaitem,ci.idcontaitem,i.idnfitem as idtipo,r.idrateio,r.idrateioitem,dt.idrateioitemdest,n.idnf,n.nnfe,dt.idobjeto,dt.tipoobjeto,i.idtipoprodserv,tp.tipoprodserv,ifnull(pr.descr,i.prodservdescr) as descr,i.vlritem as vlrlote,
                -- round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cpi.valor)*(dt.valor/100),2) as rateio
               ,dt.valor,e.unidade as empresa,cp.datareceb as dtemissao
                    from nf n
                        join nfitem i on(i.idnf=n.idnf ".$stridtipoprodserv." ".$stridprodserv." )
                        join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                        join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'unidade')
                        join unidade e on(e.idunidade=dt.idobjeto  )
                        join contaitem ci on(ci.idcontaitem=i.idcontaitem)                     
                        left join prodserv pr on(pr.idprodserv=i.idprodserv )
                        left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                        join contapagaritem cpi on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf')
                        join contapagar cp on(cp.idcontapagar=cpi.idcontapagar and cp.tipoespecifico='AGRUPAMENTO' and cp.tipo='D' and cp.valor>0 and cp.status!='INATIVO')
                    where n.tiponf not in('S','R','O')
                    ".$clausula."
                    and e.idempresa ".$idempresa." 
                UNION  ALL
                    select 'nfitem' as tipo,cp.idempresa,i.qtd,ifnull(i.un,pr.un) as un,ci.contaitem,ci.idcontaitem,i.idnfitem as idtipo,r.idrateio,r.idrateioitem,dt.idrateioitemdest,n.idnf,n.nnfe,dt.idobjeto,dt.tipoobjeto,i.idtipoprodserv,tp.tipoprodserv,ifnull(pr.descr,i.prodservdescr) as descr,i.vlritem as vlrlote,
                    -- round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                    round(((((ifnull(i.total,0))*(n.total/n.subtotal))/n.total)*cp.valor)*(dt.valor/100),2) as rateio
                    ,dt.valor,e.unidade as empresa,cp.datareceb as dtemissao
                    from nf n
                        join nfitem i on(i.idnf=n.idnf ".$stridtipoprodserv." ".$stridprodserv." )
                        join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                        join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'unidade')
                        join unidade e on(e.idunidade=dt.idobjeto  )
                        join contaitem ci on(ci.idcontaitem=i.idcontaitem)                     
                        left join prodserv pr on(pr.idprodserv=i.idprodserv )
                        left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                        join contapagar cp on(cp.idobjeto=n.idnf and cp.tipo='D' and cp.tipoobjeto='nf' and cp.tipoespecifico != 'AGRUPAMENTO' and cp.valor>0 and cp.status!='INATIVO')
                where  n.tiponf in('S','R')
                    ".$clausula."
                    and e.idempresa ".$idempresa." 
                 UNION  ALL
                    select 'nfitem' as tipo,cp.idempresa,i.qtd,ifnull(i.un,pr.un) as un,ci.contaitem,ci.idcontaitem,i.idnfitem as idtipo,r.idrateio,r.idrateioitem,dt.idrateioitemdest,n.idnf,n.nnfe,dt.idobjeto,dt.tipoobjeto,i.idtipoprodserv,tp.tipoprodserv,ifnull(pr.descr,i.prodservdescr) as descr,i.vlritem as vlrlote,
                    -- round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                    round(((((ifnull(i.total,0))*(n.total/n.subtotal))/n.total)*cpi.valor)*(dt.valor/100),2) as rateio
                    ,dt.valor,e.unidade as empresa,cp.datareceb as dtemissao
                        from nf n
                            join nfitem i on(i.idnf=n.idnf ".$stridtipoprodserv." ".$stridprodserv." )
                            join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                            join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'unidade')
                            join unidade e on(e.idunidade=dt.idobjeto  )
                            join contaitem ci on(ci.idcontaitem=i.idcontaitem)                     
                            left join prodserv pr on(pr.idprodserv=i.idprodserv )
                            left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                            join contapagaritem cpi on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf')
                            join contapagar cp on(cp.idcontapagar=cpi.idcontapagar and cp.tipoespecifico='AGRUPAMENTO' and cp.tipo='D' and cp.valor>0 and cp.status!='INATIVO')
                        where  n.tiponf in('S','R')
                        ".$clausula."
                        and e.idempresa ".$idempresa." 
                                     
        ) as u
        order by tipoobjeto,empresa,idobjeto,contaitem,tipoprodserv,descr,dtemissao";

        } else { //listar por deparatamento
          
            $sql="select  
                        tipo,idempresa,qtd,un,contaitem,idcontaitem,idtipo,idrateio,idrateioitem,idrateioitemdest,idnf,nnfe,idobjeto,tipoobjeto,idtipoprodserv,tipoprodserv,descr,vlrlote,rateio,valor,empresa,dtemissao
                    from (
                        
                            select 'nfitem' as tipo,cp.idempresa,i.qtd,ifnull(i.un,pr.un) as un,ci.contaitem,ci.idcontaitem,i.idnfitem as idtipo,r.idrateio,r.idrateioitem,dt.idrateioitemdest,n.idnf,n.nnfe,s.idsgdepartamento as idobjeto, 'sgdepartamento'  as tipoobjeto,i.idtipoprodserv,tp.tipoprodserv,ifnull(pr.descr,i.prodservdescr) as descr,i.vlritem as vlrlote,
                            -- round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                            round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cpi.valor)*(dt.valor/100),2) as rateio
                            ,dt.valor,s.departamento as empresa,cp.datareceb as dtemissao
                                from nf n
                                    join nfitem i on(i.idnf=n.idnf ".$stridtipoprodserv." ".$stridprodserv." )
                                    join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                    join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'unidade')
                                    join unidade e on(e.idunidade=dt.idobjeto  )  
                                    join sgdepartamento s on(s.idsgdepartamento=e.idobjeto and e.tipoobjeto='sgdepartamento')
                                    join contaitem ci on(ci.idcontaitem=i.idcontaitem)                    
                                    left join prodserv pr on(pr.idprodserv=i.idprodserv )
                                    left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                                    join contapagaritem cpi on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf')
                                    join contapagar cp on(cp.idcontapagar=cpi.idcontapagar and cp.tipoespecifico='AGRUPAMENTO' and cp.tipo='D' and cp.valor>0 and cp.status!='INATIVO')
                                where n.tiponf not in('S','R','O')
                                ".$clausula."
                            and e.idempresa ".$idempresa." 
                            
                                
                            union all
                                select 'nfitem' as tipo,cp.idempresa,i.qtd,ifnull(i.un,pr.un) as un,ci.contaitem,ci.idcontaitem,i.idnfitem as idtipo,r.idrateio,r.idrateioitem,dt.idrateioitemdest,n.idnf,n.nnfe,s.idsgdepartamento as idobjeto, 'sgdepartamento'  as tipoobjeto,i.idtipoprodserv,tp.tipoprodserv,ifnull(pr.descr,i.prodservdescr) as descr,i.vlritem as vlrlote,
                            --  round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                            round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor)*(dt.valor/100),2) as rateio
                                ,dt.valor,s.departamento as empresa,cp.datareceb as dtemissao
                                    from nf n
                                        join nfitem i on(i.idnf=n.idnf ".$stridtipoprodserv." ".$stridprodserv." )
                                        join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                        join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'unidade')
                                        join unidade e on(e.idunidade=dt.idobjeto  )
                                        join sgdepartamento s on(s.idsgdepartamento=e.idobjeto and e.tipoobjeto='sgdepartamento')
                                        join contaitem ci on(ci.idcontaitem=i.idcontaitem)                     
                                        left join prodserv pr on(pr.idprodserv=i.idprodserv )
                                        left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                                        join contapagar cp on(cp.idobjeto=n.idnf and cp.tipo='D' and cp.tipoobjeto='nf' and cp.tipoespecifico!='AGRUPAMENTO' and cp.valor>0 and cp.status!='INATIVO')
                                where n.tiponf not in('S','R','O')
                                    ".$clausula."
                                    and e.idempresa ".$idempresa." 
                            UNION ALL
                            select 'nfitem' as tipo,cp.idempresa,i.qtd,ifnull(i.un,pr.un) as un,ci.contaitem,ci.idcontaitem,i.idnfitem as idtipo,r.idrateio,r.idrateioitem,dt.idrateioitemdest,n.idnf,n.nnfe,s.idsgdepartamento as idobjeto, 'sgdepartamento'  as tipoobjeto,i.idtipoprodserv,tp.tipoprodserv,ifnull(pr.descr,i.prodservdescr) as descr,i.vlritem as vlrlote,
                            -- round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                            round(((((ifnull(i.total,0))*(n.total/n.subtotal))/n.total)*cpi.valor)*(dt.valor/100),2) as rateio
                            ,dt.valor,s.departamento as empresa,cp.datareceb as dtemissao
                            from nf n
                                join nfitem i on(i.idnf=n.idnf ".$stridtipoprodserv." ".$stridprodserv." )
                                join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'unidade')
                                join unidade e on(e.idunidade=dt.idobjeto  )  
                                join sgdepartamento s on(s.idsgdepartamento=e.idobjeto and e.tipoobjeto='sgdepartamento')
                                join contaitem ci on(ci.idcontaitem=i.idcontaitem)                    
                                left join prodserv pr on(pr.idprodserv=i.idprodserv )
                                left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                                join contapagaritem cpi on(cpi.idobjetoorigem=n.idnf and cpi.tipoobjetoorigem='nf')
                                join contapagar cp on(cp.idcontapagar=cpi.idcontapagar and cp.tipoespecifico='AGRUPAMENTO' and cp.tipo='D' and cp.valor>0 and cp.status!='INATIVO')
                            where  n.tiponf in('S','R')
                            ".$clausula."
                        and e.idempresa ".$idempresa." 
                        
                            
                        union all
                            select 'nfitem' as tipo,cp.idempresa,i.qtd,ifnull(i.un,pr.un) as un,ci.contaitem,ci.idcontaitem,i.idnfitem as idtipo,r.idrateio,r.idrateioitem,dt.idrateioitemdest,n.idnf,n.nnfe,s.idsgdepartamento as idobjeto, 'sgdepartamento'  as tipoobjeto,i.idtipoprodserv,tp.tipoprodserv,ifnull(pr.descr,i.prodservdescr) as descr,i.vlritem as vlrlote,
                            -- round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio
                            round(((((ifnull(i.total,0))*(n.total/n.subtotal))/n.total)*cp.valor)*(dt.valor/100),2) as rateio
                            ,dt.valor,s.departamento as empresa,cp.datareceb as dtemissao
                                from nf n
                                    join nfitem i on(i.idnf=n.idnf ".$stridtipoprodserv." ".$stridprodserv." )
                                    join  rateioitem r force index(idobj_tipoobj) on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem )
                                    join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'unidade')
                                    join unidade e on(e.idunidade=dt.idobjeto  )
                                    join sgdepartamento s on(s.idsgdepartamento=e.idobjeto and e.tipoobjeto='sgdepartamento')
                                    join contaitem ci on(ci.idcontaitem=i.idcontaitem)                     
                                    left join prodserv pr on(pr.idprodserv=i.idprodserv )
                                    left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
                                    join contapagar cp on(cp.idobjeto=n.idnf and cp.tipo='D' and cp.tipoobjeto='nf' and cp.tipoespecifico!='AGRUPAMENTO' and cp.valor>0 and cp.status!='INATIVO')
                            where  n.tiponf in('S','R')
                                ".$clausula."
                                and e.idempresa ".$idempresa." 
                                                
                    ) as u
                    order by tipoobjeto,empresa,idobjeto,contaitem,tipoprodserv,descr,dtemissao";
                    
        }


        echo "<!--";
        echo $sql;
        echo "-->";
        if (!empty($sql)) {
            $res =  d::b()->query($sql) or die("Falha ao pesquisar consumos: " . mysqli_error() . "<p>SQL: $sql");
            corpo($res, 'rateio');
        }

        // }



    }

    function corpo($res, $tiporel)
    {
        global $totalgeral, $arrvtipo, $arrvemp, $nfclass, $prodservclass;
        $ires = mysqli_num_rows($res);

    ?>



        <? if ($ires > 0) {
            $vtipo = 0;
            $vempresa = 0;
            //$rempresa=traduzid('empresa','idempresa','empresa',$_SESSION["SESSAO"]["IDEMPRESA"]);
            //$arrvemp=array();
            //$arrvtipo=array();
            while ($row = mysqli_fetch_assoc($res)) {
                $i = $i + 1;

                $total = $total + $row['rateio'];

                if (empty($idobjeto) or ($row['idobjeto'] != $idobjeto or   $row['tipoobjeto'] != $tipoobjeto)) {
                    if (!empty($idobjeto)) {
                        $arrvemp[$tipoobjeto][$idobjeto] = $arrvemp[$tipoobjeto][$idobjeto] + $vempresa;
                        $arrvtipo[$tipoobjeto][$idobjeto][$idtipoprodserv] = $arrvtipo[$tipoobjeto][$idobjeto][$idtipoprodserv] + $vtipo;
        ?>
                        <tr class='res'>
                            <td colspan="7" style="text-align: right;"><b><?= traduzid('tipoprodserv', 'idtipoprodserv', 'tipoprodserv', $idtipoprodserv); ?></td>
                            <td colspan="1" style="text-align: right;" class="nowrap">
                                <b>R$ <?= number_format(tratanumero($vtipo), 2, ',', '.'); ?>
                            </td>
                        </tr>
                        <tr class='res'>
                            <td colspan="7" style="text-align: right;"><b>Total <?= $empresa ?></td>
                            <td colspan="1" style="text-align: right;" class="nowrap">
                                <b> R$ <?= number_format(tratanumero($vempresa), 2, ',', '.'); ?>
                            </td>
                        </tr>
                        </table>
                        <p>
                        <div style="page-break-after: always;"></div>
                    <? $vtipo = 0;
                    }
                    $idtipoprodserv = '';

                    ?>

                    <table class="normal" style="width:100%">
                        <tr class="header">

                            <th colspan="9" style="text-transform: uppercase;">
                                <?= $row['empresa'] ?>
                            </th>
                        </tr>



                        <?
                        $empresa = $row['empresa'];
                        $tipoobjeto = $row['tipoobjeto'];
                        $idobjeto = $row['idobjeto'];
                        $vempresa = 0;
                        $vtipo = 0;
                    }
                    if ($row['idtipoprodserv'] != $idtipoprodserv) {
                        if (!empty($idtipoprodserv)) {
                            $arrvtipo[$tipoobjeto][$idobjeto][$idtipoprodserv] = $arrvtipo[$tipoobjeto][$idobjeto][$idtipoprodserv] + $vtipo;
                        ?>

                            <tr class='res'>

                                <td colspan="7" style="text-align: right;"><b> <?= traduzid('tipoprodserv', 'idtipoprodserv', 'tipoprodserv', $idtipoprodserv); ?></td>
                                <td colspan="1" style="text-align: right;" class="nowrap"><b>
                                        <div class="tipoprodserv" id='tipo<?= $idtipoprodserv ?>_<?= $row['idobjeto'] ?>_<?= $row['tipoobjeto'] ?>'>
                                            R$ <?= number_format(tratanumero($vtipo), 2, ',', '.'); ?>
                                        </div>
                                </td>
                            </tr>
                            <tr class='res'>
                                <td colspan="8"><br></td>
                            <tr>


                            <? $vtipo = 0;
                        }
                            ?>


                            <!-- tr class="header">             
            <th  colspan="9"> 
                <?= $row['tipoprodserv'] ?>
            </th>               
        </tr -->

                            <tr class="header">
                                <th>Qtd</th>
                                <th>Un</th>
                                <th>Item</th>
                                <th class="nowrap">Tipo</th>
                                <th class="nowrap">Data</th>
                                <th class="nowrap" title='Valor unitário sem rateio'>Valor Un</th>
                                <th>Rateio</th>
                                <th title='Valor total com rateio'>Valor</th>


                            </tr>
                        <?
                        $idtipoprodserv = $row['idtipoprodserv'];
                        $vtipo = 0;
                    }
                        ?>

                        <tr class="res">
                            <td class="col-md-1" title="Item" style="text-align: right;">
                                <?= number_format(tratanumero($row['qtd']), 2, ',', '.'); ?>
                            </td>
                            <td class="col-md-1" style="text-align: center;"><?= $row['un'] ?></td>
                            <td class="col-md-3" style="text-align: left;"><?= $row['descr'] ?></td>
                            <td class="col-md-2 nowrap" style="text-align: center;">
                                <? if ($row['tipo'] == 'lotecons') {
                                    $sl = "select * from lotecons where idlotecons=" . $row['idtipo'];
                                    $rl =  d::b()->query($sl) or die('Erro ao buscar tipo do consumo');
                                    $rwl = mysqli_fetch_assoc($rl);
                                    if ($rwl['tipoobjetoconsumoespec'] == 'solmatitem') {
                                        echo ("REQUISIÇÃO");
                                        $tipocons = 'REQUISICAO';
                                    } else {
                                        echo ("TRANSFERÊNCIA");
                                        $tipocons = 'TRANSFERENCIA';
                                    }
                                } else {
                                    echo ("COMPRA");
                                    $tipocons = 'COMPRA';
                                } ?>
                            </td>
                            <td class="col-md-2 nowrap" style=" text-align: center;">
                                <?= dmahms($row['dtemissao']) ?>
                            </td>
                            <td class="col-md-1 nowrap" style="text-align: right;">
                                R$ <?= number_format(tratanumero($row['vlrlote']), 2, ',', '.'); ?>
                            </td>
                            <td class="col-md-1" style="text-align: right;">
                                <?= number_format(tratanumero($row['valor']), 2, ',', '.'); ?>%
                            </td>
                            <td class="col-md-1 nowrap" style="text-align: right;">

                                <? if (!empty($row['idnf'])) { ?>

                                    R$ <?= number_format(tratanumero($row['rateio']), 2, ',', '.'); ?>

                                <? } else { ?>
                                    R$ <?= number_format(tratanumero($row['rateio']), 2, ',', '.'); ?>
                                <? } ?>

                            </td>

                        </tr>

                    <?
                    $vtipo = $vtipo + $row['rateio'];
                    $vempresa = $vempresa + $row['rateio'];
                } //while($row=mysqli_fetch_assoc($res)){
                $arrvemp[$tipoobjeto][$idobjeto] = $arrvemp[$tipoobjeto][$idobjeto] + $vempresa;
                $arrvtipo[$tipoobjeto][$idobjeto][$idtipoprodserv] = $arrvtipo[$tipoobjeto][$idobjeto][$idtipoprodserv] + $vtipo;
                    ?>
                    <tr class='res'>
                        <td colspan='7' style="text-align: right;"><b><?= traduzid('tipoprodserv', 'idtipoprodserv', 'tipoprodserv', $idtipoprodserv) ?></td>
                        <td colspan='1' style="text-align: right;">
                            <b> R$ <?= number_format(tratanumero($vtipo), 2, ',', '.'); ?>
                        </td>
                    </tr>
                    <tr class='res'>
                        <td colspan='7' style="text-align: right;"><b>Total <?= $empresa ?></td>
                        <td colspan='1' style="text-align: right;" class="nowrap">
                            <b> R$ <?= number_format(tratanumero($vempresa), 2, ',', '.'); ?>
                        </td>
                    </tr>

                    </table>

                    <br>
                    <div style="page-break-after: always;"></div>
                    <?

                    $totalgeral = $totalgeral + $total;
                    ?>

                <?
            }
                ?>

            <?
        } // fim corpo

            ?>

</body>

</html>
