<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

################################################## Atribuindo o resultado do metodo GET
$vencimento_1       = $_GET["vencimento_1"];
$vencimento_2       = $_GET["vencimento_2"];
$idtipoprodserv     = $_GET["idtipoprodserv"];
$idcontaitem        = $_GET["idcontaitem"];
$pesquisa           = $_GET["pesquisa"];
$tiponf             = $_GET["tiponf"];
$status             = $_GET["status"];
$modo               = $_GET["modo"];
$idagencia          = $_GET["idagencia"];
$idunidade          = $_GET["idunidade"];

if (empty($_GET["idempresa"])) {
    $idempresa = $_SESSION['SESSAO']['IDEMPRESA'];
} else {
    $idempresa = $_GET["idempresa"];
}

$sql = " select * from pessoa where flgsocio='Y' and idpessoa=" . $_SESSION["SESSAO"]["IDPESSOA"];
$res = d::b()->query($sql) or die("Erro ao buscar usuário: " . mysqli_error(d::b()));
$flgdiretor = mysqli_num_rows($res);

?>
<style>
   button.btn.dropdown-toggle.btn-default{
        width:350px;
    } 
    @media print {
		.hideprint{
			display: none !important;
		}
	}
</style>
<?
/*
 * colocar condição para executar select
 */
if ($_GET and !empty($vencimento_1) and !empty($vencimento_2)) {

    //die("Valores não escontrados nesta configuração");

    if ($modo == 'FT') {

        if (!empty($vencimento_1) or !empty($vencimento_2)) {
            $dataini = validadate($vencimento_1);
            $datafim = validadate($vencimento_2);

            if ($dataini and $datafim) {
                $clausulac .= " and (n.dtemissao  BETWEEN '" . $dataini . " 00:00:00' and '" . $datafim . " 23:59:59')" . "  ";
            } else {
                die("Datas n&atilde;o V&aacute;lidas!");
            }
        }

        if(!empty($idunidade)){
            $clausulaun .= " and n.idunidade=".$idunidade." ";
        }

        if (!empty($idcontaitem)) {
            $stridcontaitem = " and c.idcontaitem in (" . $idcontaitem . ") ";
        } else {
            $stridcontaitem = "";
        }
        
        if (!empty($tiponf)) {
            $strtiponf = " and tiponf = '" . $tiponf . "' ";
        } else {
            $strtiponf = "";
        }
      
/*
        if (!empty($idtipoprodserv)) {
            $stridtipoprodserv = " and p.idtipoprodserv =" . $idtipoprodserv . " ";
        } else {
            $stridtipoprodserv = "";
        }
        */
        /*Controlado pela LP se liberar o Grupo pode ser visto
        if ($flgdiretor < 1) {
            $viscontaitem = " and c.visualizarext='Y' ";
        } else {
            $viscontaitem = "";
        }
        */

        // nfitem descritivo e com idprodserv preenchido
        $sqlgrupo = "select u.contaitem,u.idcontaitem,u.cor,u.somarelatorio,u.previsao,sum(u.total) as somatotal,u.ordem,u.faturamento
                    from (
                            SELECT c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,i.total,c.ordem,c.faturamento
                                            FROM nf n 
                                            join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0) 
                                            join prodserv p on(p.idprodserv=i.idprodserv)
                                            join tipoprodserv tp on(tp.idtipoprodserv=p.idtipoprodserv)
                                            join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . "  )
                                            where n.status !='CANCELADO'
                                            and n.tiponf not in ('V')
                                            ".$clausulaun."
                                            " . $clausulac . " 
                                             
                                             and n.idempresa IN (" . $idempresa . ") 
                                            " . $stridcontaitem . "
                                            " . $stridtipoprodserv . "
                           union all
                           SELECT c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,i.total,c.ordem,c.faturamento
                                            FROM nf n 
                                            join nfitem i on(i.idnf=n.idnf  and i.nfe!='C' and i.total>0) 
                                            join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv)
                                            join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . "  )
                                            where n.status !='CANCELADO'
                                            and (i.idprodserv is null or i.idprodserv ='') 
                                            ".$clausulaun." 
                                            " . $clausulac . "
                                         
                                            and n.idempresa IN (" . $idempresa . ")
                                            " . $stridcontaitem . "
                                            " . $stridtipoprodserv . "
                        ) as u 
                        where 1   " . $strtiponf . " 
                        group by u.idcontaitem order by u.ordem";
        echo "<!--";
        echo $sqlgrupo;
        echo "-->";
        if (!empty($sqlgrupo)) {

            $resgrupo =  d::b()->query($sqlgrupo) or die("Falha ao pesquisar grupo de contas: " . mysqli_error() . "<p>SQL: $sqlgrupo");
            $ires = mysqli_num_rows($resgrupo);
            $saldototal = 0;
        }
    } else { //MODO FATURAMENTO
        if (!empty($vencimento_1) or !empty($vencimento_2)) {
            $dataini = validadate($vencimento_1);
            $datafim = validadate($vencimento_2);

            if ($dataini and $datafim) {
                $clausulac .= " and (cp.datareceb  BETWEEN '" . $dataini . "' and '" . $datafim . "')" . "  ";
            } else {
                die("Datas n&atilde;o V&aacute;lidas!");
            }
        }

        if (!empty($idcontaitem)) {
            $stridcontaitem = " and c.idcontaitem in (" . $idcontaitem . ") ";
        } else {
            $stridcontaitem = "";
        }

        if (!empty($idagencia)) {
            $clausulac .= " and cp.idagencia IN (" . $idagencia . ")  ";
        }
        
        
        if (!empty($tiponf)) {
            $strtiponf = " and tiponf = '" . $tiponf . "' ";
        } else {
            $strtiponf = "";
        }
      
        

        if(!empty($idunidade)){
            $clausulaun .= " and n.idunidade=".$idunidade." ";
        }
/*
        if (!empty($idtipoprodserv)) {
            $stridtipoprodserv = " and p.idtipoprodserv =" . $idtipoprodserv . " ";
        } else {
            $stridtipoprodserv = "";
        }
*/
        if ($flgdiretor < 1) {
            $viscontaitem = " and c.visualizarext='Y' ";
        } else {
            $viscontaitem = "";
        }

        $sqlgrupo = "select u.tiponf,u.contaitem,u.idcontaitem,u.cor,u.somarelatorio,u.previsao,sum(u.total) as somatotal,u.ordem,u.status,u.tipo,u.faturamento
                    from (
                        SELECT n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,                           
                        
                        round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor)*-1,2) as total
                        ,cp.status,cp.tipo,c.faturamento
                        ,c.ordem
                                FROM nf n 
                                join nfitem i on(i.idnf=n.idnf and i.nfe='Y' ) 
                                join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . "  )
                                join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                where cp.tipoespecifico!= 'AGRUPAMENTO'
                             
                                and cp.idempresa IN (" . $idempresa . ")  
                                ".$clausulaun."                               
                                " . $clausulac . " 
                                " . $stridcontaitem . "
                                " . $stridtipoprodserv . "  
                                
                                and cp.status !='INATIVO'
                                and cp.tipo = 'D'
                                and cp.valor>0
                                -- and i.total>0
                                and n.tiponf not in('S','R','O')
                       union all
                         SELECT   n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                               
                                round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*ci.valor)*-1,2) as total
                                 ,cp.status,cp.tipo,c.faturamento
                                ,c.ordem
                                FROM contapagar cp
                                join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                            and ci.tipoobjetoorigem ='nf')
                                join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                join nfitem i on(i.idnf=n.idnf and i.nfe='Y')          
                                join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )               
                                where cp.tipoespecifico = 'AGRUPAMENTO'  
                                
                                and cp.idempresa  IN (" . $idempresa . ") 
                                ".$clausulaun."
                                " . $clausulac . " 
                                " . $stridcontaitem . "
                                " . $stridtipoprodserv . "
                              
                                and cp.status !='INATIVO'
                                and ci.status!='INATIVO'
                                and cp.tipo = 'D'
                                and cp.valor>0
                                -- and i.total>0
                                and n.tiponf not in('S','R','O')                                
                        union all
                            SELECT n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,                           
                            round(((((ifnull(i.total,0))*(n.total/n.subtotal))/n.total)*cp.valor)*-1,2) as total  
                            ,cp.status,cp.tipo,c.faturamento
                            ,c.ordem
                                    FROM nf n 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y') 
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . "  )
                                    join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO'
                                   
                                    and cp.idempresa IN (" . $idempresa . ")   
                                    ".$clausulaun."                             
                                    " . $clausulac . " 
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "  
                                   
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'D'
                                    and cp.valor>0
                                    -- and i.total>0
                                    and n.tiponf  in('S','R')
                           union all
                             SELECT   n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                                    round(((((ifnull(i.total,0))*(n.total/n.subtotal))/n.total)*ci.valor)*-1,2) as total
                                     ,cp.status,cp.tipo,c.faturamento
                                    ,c.ordem
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='nf')
                                    join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y' )          
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )               
                                    where cp.tipoespecifico = 'AGRUPAMENTO'  
                                   
                                    and cp.idempresa IN (" . $idempresa . ")
                                    ".$clausulaun."
                                    " . $clausulac . " 
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "
                                 
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'D'
                                    and cp.valor>0
                                    -- and i.total>0
                                    and n.tiponf in('S','R')
                                    union all
                            SELECT n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,                           
                                    CASE
                                        WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0))/n.total)*cp.valor),2)  
                                        ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor),2)
                                    END as total
                            ,cp.status,cp.tipo,c.faturamento
                            ,c.ordem
                                    FROM nf n 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0)          
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )  
                                    join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO'    
                                    and i.idprodserv is null
                                    and cp.idempresa IN (" . $idempresa . ")  
                                    ".$clausulaun."                              
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "
                                   
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'C'
                           union all
                             SELECT   n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                                    CASE
                                        WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0))/n.total)*ci.valor),2)  
                                        ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*ci.valor),2)
                                    END as total
                                     ,cp.status,cp.tipo,c.faturamento
                                    ,c.ordem
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='nf')
                                    join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0)          
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )              
                                    where cp.tipoespecifico = 'AGRUPAMENTO' 
                                    and i.idprodserv is null
                                    and cp.idempresa IN (" . $idempresa . ") 
                                    ".$clausulaun." 
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "
                                 
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'C'
                            union all
                            SELECT n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,                           
                            CASE
                                WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0))/n.total)*cp.valor),2)  
                                ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor),2)
                            END as total
                            ,cp.status,cp.tipo,c.faturamento
                            ,c.ordem
                                    FROM nf n 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0 ) 
                                   join prodserv ps on(ps.idprodserv =i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem  " . $viscontaitem . " )   
                                    join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO'    
                                    
                                    and cp.idempresa IN (" . $idempresa . ")  
                                    ".$clausulaun."                             
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "  
                                   
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'C'
                           union all
                             SELECT   n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                                    CASE
                                        WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0))/n.total)*ci.valor),2)  
                                        ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*ci.valor),2)
                                    END as total
                                    ,cp.status,cp.tipo,c.faturamento
                                    ,c.ordem
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='nf')
                                    join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0) 
                                    join prodserv ps on(ps.idprodserv =i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem " . $viscontaitem . " )               
                                    where cp.tipoespecifico = 'AGRUPAMENTO' 
                                    
                                    and cp.idempresa IN (" . $idempresa . ")  
                                    ".$clausulaun."
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "
                                  
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'C'
                                union all
                                    SELECT                                    
                                        'SV' as tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                                       
                                        round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(cp.valor/n.total),2) as total
                                        ,cp.status,cp.tipo,c.faturamento
                                        ,c.ordem									
                                    FROM notafiscal n 
                                      join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal  ) 
                                       join prodserv ps on(ps.idprodserv=i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem  " . $viscontaitem . ")   
                                    join contapagar cp on(cp.idobjeto = n.idnotafiscal and cp.tipoobjeto ='notafiscal')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO' 
                                                                     
                                    and cp.idempresa IN (" . $idempresa . ")  
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . " 
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,cp.idcontapagar,i.idprodserv,i.valor
                           union all
                             SELECT   
                                    'SV' as tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                                    
                                    round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(ci.valor/n.total),2) as total
                                    ,cp.status,cp.tipo,c.faturamento
                                    ,c.ordem
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='notafiscal')
                                    join notafiscal n on(ci.idobjetoorigem =n.idnotafiscal  ) 
                                    join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal  ) 
                                    join prodserv ps on(ps.idprodserv=i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem " . $viscontaitem . " )               
                                    where cp.tipoespecifico = 'AGRUPAMENTO' 
                                   
                                    and cp.idempresa IN (" . $idempresa . ") 
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,ci.idcontapagaritem,i.idprodserv,i.valor

                                    union all
                                    SELECT 'SV' as tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                                            round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(cp.valor/n.total),2) as total,
                                            cp.status,cp.tipo,c.faturamento
                                            ,c.ordem									
                                    FROM notafiscal n 
                                    join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal and i.idprodserv is null ) 
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . " )   
                                    join contapagar cp on(cp.idobjeto = n.idnotafiscal and cp.tipoobjeto ='notafiscal')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO' 
                                                           
                                    and cp .idempresa IN (" . $idempresa . ")   
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . " 
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,cp.idcontapagar,i.idtipoprodserv,i.valor
                           union all
                             SELECT   
                                    'SV' as tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                                    
                                    round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(ci.valor/n.total),2) as total
                                    ,cp.status,cp.tipo,c.faturamento
                                    ,c.ordem
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='notafiscal')
                                    join notafiscal n on(ci.idobjetoorigem =n.idnotafiscal  ) 
                                    join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal and i.idprodserv is null ) 
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . " )                
                                    where cp.tipoespecifico = 'AGRUPAMENTO' 
                                    
                                    and cp .idempresa IN (" . $idempresa . ")   
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,ci.idcontapagaritem,i.idtipoprodserv,i.valor
                        ) as u             
                        where 1 " . $strtiponf . "
                        group by u.idcontaitem order by u.ordem";
        echo "<!-- grupo";
        echo $sqlgrupo;
        echo "-->";
        if (!empty($sqlgrupo)) {

            $resgrupo =  d::b()->query($sqlgrupo) or die("Falha ao pesquisar grupo de contas: " . mysqli_error() . "<p>SQL: $sqlgrupo");
            $ires = mysqli_num_rows($resgrupo);
            $saldototal = 0;
        }
    } //FIM DO MODO 
}
?>

<!-- Mostrar mensagem de Aguarde e bloquear tela  -->

<style>
    .backbranco {
        background-color: white !important;
    }
</style>
<div class="divbody">
    <?
    if ($_GET and $ires > 0) {

        $vlrcredito = 0;
        $vlrdebito = 0;
        $vlrpendcredito = 0;
        $vlrpenddebito = 0;
        $vlrquitadocredito = 0;
        $vlrquitadodebito = 0;
        $vlrprogramado = 0;
        $vlrprogramadopagar = 0;
        $vlrndescriminados = 0;
        $arrprogramado = array();

        if ($pesquisa == 'detalhe') {
            $collapse = "";
            $collapseitem = "collapse";
        } elseif ($pesquisa == 'detalheitem') {
            $collapse = "";
            $collapseitem = "";
        } else {
            $collapse = "collapse";
            $collapseitem = "collapse";
        }
        //die('pesq='.$pesquisa.' col='.$collapse);
        $vtotal = 0;
        $id = 0;
        $id2 = 0;
        if (!empty($_REQUEST['csv'])) {
            $conteudoexport = "CONTA ITEM;TIPO PRODUTO;NFe;QTD;ITEM;PARCELA;VALOR UN;TOTAL;\n";
        }
        $arrcontaitem = array();
        while ($row = mysqli_fetch_assoc($resgrupo)) {
            $id = $id + 1;
            if (!empty($row["idcontaitem"])  and $row["tiponf"] != "O") {

                if (!array_key_exists($row['idcontaitem'], $arrcontaitem)) {

                    $arrcontaitem[$row['idcontaitem']]['total'] =abs($row["somatotal"]);  ; 
                    $arrcontaitem[$row['idcontaitem']]['faturamento'] = $row["faturamento"]; 
                        

    ?>

        <div class="panel-default">
            <div class="panel-body">
                <table style="width:100%;font-size: 16px;" class="tbItem" idcontaitem="<?= $row["idcontaitem"] ?>">
                    <tr>
                     
                        <td  class="nowrap" style="width: 50%;">
                        <input name="_1_u_contaitem_cor" type="hidden" value="<?= $row["cor"] ?>" size="6" class="color" style="cursor:pointer;" onchange="cor(this,<?= $row["idcontaitem"] ?>);">
                        <b><?= $row["contaitem"] ?></b>
                        </td>
                        <td align="right" class="col-md-1"> 
                            <span class="idcontaitem_<?=$row["idcontaitem"]?>">
                            </span>
                        </td>
                      
                        <td align="right" class="col-md-1">
                        <?
                        if($row["somatotal"] < 0){
                            $row["somatotal"] = $row["somatotal"] * -1;
                        }
                        ?>
                            <b>R$<label class="alert-warning"><?= number_format(tratanumero($row["somatotal"]), 2, ',', '.'); ?></label></b>
                        </td>
                        
                        <td  align="right"  class="col-md-1"><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar" data-toggle="collapse" href="#prodInfo<?= $id ?>" aria-expanded="false"></i></td>
                      
                      
                    </tr>
                </table>

            </div>
            <div class="panel-body">

                <div class="<?= $collapse ?>" id="prodInfo<?= $id ?>">
                    <table class="table table-striped planilha" style="width:100%;font-size: 12px;" >
                        <?
                        //if($pesquisa=="detalhe"){
                        if ($modo == 'FT') {
                            //itens descritivos ou com idprodserv           
                            $sql = "select u.tipoprodserv,u.idtipoprodserv,sum(u.total) as somatotal  
                        from(
                            SELECT tp.tipoprodserv,tp.idtipoprodserv,i.total, n.tiponf
                            FROM nf n 
                            join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0) 
                            join prodserv p on(p.idprodserv=i.idprodserv)
                            join tipoprodserv tp on(tp.idtipoprodserv=p.idtipoprodserv)
                            where n.status !='CANCELADO'
                            and n.tiponf not in ('V')
                            and i.total>0
                            " . $clausulac . "         
                            " . $stridtipoprodserv . "
                            and n.idempresa  IN (" . $idempresa . ") 
                            and i.idcontaitem = " . $row['idcontaitem'] . "
                            union all
                            SELECT p.tipoprodserv,p.idtipoprodserv,i.total, n.tiponf
                            FROM nf n 
                            join nfitem i on(i.idnf=n.idnf and i.nfe!='C' and i.total>0)                        
                            join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv)
                            where n.status !='CANCELADO'     
                                                   
                            and (i.idprodserv is null or i.idprodserv ='')  
                            ".$clausulaun."  
                            " . $clausulac . "         
                            " . $stridtipoprodserv . "
                            and n.idempresa  IN (" . $idempresa . ") 
                            and i.idcontaitem = " . $row['idcontaitem'] . "
                        ) as u        
                        where 1  " . $strtiponf . "
                        group by u.idtipoprodserv order by somatotal desc";

                                        echo "<!--";
                                        echo $sql;
                                        echo "-->";
                                    } else { //Faturamento
                                        //contapagar agrupado e normal
                                        $sql = "select u.tipoprodserv,u.idtipoprodserv,sum(u.total) as somatotal,u.somarelatorio,u.status,u.tipo  
                        from (
                            SELECT p.tipoprodserv,p.idtipoprodserv,
                            
                            round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor)*-1,2) as total
                            ,c.somarelatorio,cp.status,cp.tipo
                            ,n.tiponf
                            FROM nf n 
                            join nfitem i on(i.idnf=n.idnf and i.nfe='Y') 
                            join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                            join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )
                            join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                            where cp.tipoespecifico!= 'AGRUPAMENTO' 
                            
                            and cp .idempresa IN (" . $idempresa . ") 
                            ".$clausulaun."                                   
                            " . $clausulac . "                    
                            " . $stridtipoprodserv . "  
                            and i.idcontaitem = " . $row['idcontaitem'] . "
                            and cp.status !='INATIVO'
                            and cp.tipo = 'D'
                            and cp.valor>0
                            -- and i.total>0
                            and n.tiponf not in('S','R','O')
                   union all
                    SELECT  p.tipoprodserv,p.idtipoprodserv,
                            
                            round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*sum(ci.valor))*-1,2) as total
                            ,c.somarelatorio,cp.status,cp.tipo
                            ,n.tiponf
                            FROM contapagar cp
                            join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                        and ci.tipoobjetoorigem ='nf')
                            join nf n on(ci.idobjetoorigem =n.idnf  ) 
                            join nfitem i on(i.idnf=n.idnf  and i.nfe='Y' )               
                            join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv " . $stridtipoprodserv . ")
                            join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . " )               
                            where cp.tipoespecifico = 'AGRUPAMENTO'  
                            
                            and cp .idempresa IN (" . $idempresa . ")  
                            ".$clausulaun."
                            " . $clausulac . "                     
                            " . $stridtipoprodserv . "
                            and i.idcontaitem = " . $row['idcontaitem'] . "
                            and cp.status !='INATIVO'
                            and ci.status!='INATIVO'
                            and cp.tipo = 'D'
                            and cp.valor>0
                           --  and i.total>0
                            and n.tiponf not in('S','R','O')
                            group by cp.idcontapagar,i.idnfitem
                    union all
                            SELECT p.tipoprodserv,p.idtipoprodserv,
                                    round(((((ifnull(i.total,0))*(n.total/ifnull(n.subtotal,n.total)))/n.total)*cp.valor)*-1,2) as total
                                    ,c.somarelatorio,cp.status,cp.tipo
                                    ,n.tiponf
                                    FROM nf n 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y') 
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )
                                    join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO' 
                                  
                                    and cp .idempresa IN (" . $idempresa . ")    
                                    ".$clausulaun."                                
                                    " . $clausulac . "                    
                                    " . $stridtipoprodserv . "  
                                    and i.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'D'
                                    and cp.valor>0
                                    -- and i.total>0
                                    and n.tiponf in('S','R')
                           union all
                            SELECT  p.tipoprodserv,p.idtipoprodserv,
                                    round(((((ifnull(i.total,0))*(n.total/ifnull(n.subtotal,n.total)))/n.total)*sum(`ci`.`valor`))*-1,2) as total
                                    ,c.somarelatorio,cp.status,cp.tipo
                                    ,n.tiponf
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='nf')
                                    join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                    join nfitem i on(i.idnf=n.idnf  and i.nfe='Y' )               
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv " . $stridtipoprodserv . ")
                                    join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . " )               
                                    where cp.tipoespecifico = 'AGRUPAMENTO'  
                                   
                                    and cp .idempresa IN (" . $idempresa . ")  
                                    ".$clausulaun."
                                    " . $clausulac . "                     
                                    " . $stridtipoprodserv . "
                                    and i.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'D'
                                    and cp.valor>0
                                   -- and i.total>0
                                    and n.tiponf in('S','R')
                                    group by cp.idcontapagar,i.idnfitem
                                union all
                                    SELECT p.tipoprodserv,p.idtipoprodserv,
                                            CASE
                                                WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0))/n.total)*cp.valor),2)  
                                                ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor),2)
                                            END as total                                            
                                            ,c.somarelatorio,cp.status,cp.tipo
                                            ,n.tiponf
                                            FROM nf n 
                                            join nfitem i on(i.idnf=n.idnf  and i.nfe='Y' and i.total>0 )               
                                            join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv " . $stridtipoprodserv . ")
                                            join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . " )    
                                            join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                            where cp.tipoespecifico!= 'AGRUPAMENTO'    
                                            and i.idprodserv is null
                                            and cp .idempresa IN (" . $idempresa . ") 
                                            ".$clausulaun."                                
                                            " . $clausulac . "                    
                                            " . $stridtipoprodserv . "  
                                            and c.idcontaitem = " . $row['idcontaitem'] . "
                                            and cp.status !='INATIVO'
                                            and cp.tipo = 'C'
                                   union all
                                    SELECT  p.tipoprodserv,p.idtipoprodserv,
                                            CASE
                                                WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0))/n.total)*sum(ci.valor)),2)  
                                                ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*ci.valor),2)
                                            END as total
                                            ,c.somarelatorio,cp.status,cp.tipo
                                            ,n.tiponf
                                            FROM contapagar cp
                                            join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                        and ci.tipoobjetoorigem ='nf')
                                            join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                            join nfitem i on(i.idnf=n.idnf  and i.nfe='Y' and i.total>0 )               
                                            join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv " . $stridtipoprodserv . ")
                                            join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . " )              
                                            where cp.tipoespecifico = 'AGRUPAMENTO' 
                                            and i.idprodserv is null
                                            and cp .idempresa IN (" . $idempresa . ") 
                                            ".$clausulaun."  
                                            " . $clausulac . "                     
                                            " . $stridtipoprodserv . "
                                            and c.idcontaitem = " . $row['idcontaitem'] . "
                                            and cp.status !='INATIVO'
                                            and ci.status!='INATIVO'
                                            and cp.tipo = 'C'
                                            group by cp.idcontapagar,i.idnfitem
                            union all
                            SELECT p.tipoprodserv,p.idtipoprodserv,
                                    CASE
                                        WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0))/n.total)*cp.valor),2)  
                                        ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor),2)
                                    END as total
                                    ,c.somarelatorio,cp.status,cp.tipo
                                    ,n.tiponf
                                    FROM nf n 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0 ) 
                                   join prodserv ps on(ps.idprodserv =i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem " . $viscontaitem . "  )   
                                    join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO'    
                                    
                                    and cp .idempresa IN (" . $idempresa . ")  
                                    ".$clausulaun."                               
                                    " . $clausulac . "                    
                                    " . $stridtipoprodserv . "  
                                    and c.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'C'
                           union all
                            SELECT  p.tipoprodserv,p.idtipoprodserv,
                                    CASE
                                        WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0))/n.total)*sum(ci.valor)),2)  
                                        ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*sum(ci.valor)),2)
                                    END as total 
                                    ,c.somarelatorio,cp.status,cp.tipo
                                    ,n.tiponf
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='nf')
                                    join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0) 
                                    join prodserv ps on(ps.idprodserv =i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv " . $stridtipoprodserv . " )
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem " . $viscontaitem . "  )               
                                    where cp.tipoespecifico = 'AGRUPAMENTO' 
                                    
                                    and cp .idempresa IN (" . $idempresa . ") 
                                    ".$clausulaun."  
                                    " . $clausulac . "                     
                                    " . $stridtipoprodserv . "
                                    and c.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'C'
                                    group by cp.idcontapagar,i.idnfitem
                                union all
                                    SELECT 
                                    p.tipoprodserv,p.idtipoprodserv,
                                   
                                    round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(cp.valor/n.total),2) as total
                                    ,c.somarelatorio
                                    ,cp.status,cp.tipo	
                                    ,'S' as tiponf
                                    FROM notafiscal n 
                                      join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal  ) 
                                      join prodserv ps on(ps.idprodserv=i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv " . $stridtipoprodserv . " )
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem  " . $viscontaitem . ")   
                                    join contapagar cp on(cp.idobjeto = n.idnotafiscal and cp.tipoobjeto ='notafiscal')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO'    
                                                                 
                                    and cp .idempresa IN (" . $idempresa . ")   
                                    " . $clausulac . "                     
                                    " . $stridtipoprodserv . "
                                    and c.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,cp.idcontapagar,i.idprodserv,i.valor
                           union all
                             SELECT   
                                    p.tipoprodserv,p.idtipoprodserv,
                                    
                                    round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(ci.valor/n.total),2) as total
                                    ,c.somarelatorio
                                    ,cp.status,cp.tipo	     
                                    ,'S' as tiponf
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='notafiscal')
                                    join notafiscal n on(ci.idobjetoorigem =n.idnotafiscal  ) 
                                    join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal  ) 
                                    join prodserv ps on(ps.idprodserv=i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv  " . $stridtipoprodserv . ")
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem " . $viscontaitem . " )               
                                    where cp.tipoespecifico = 'AGRUPAMENTO'  
                                    
                                    and cp .idempresa IN (" . $idempresa . ")   
                                    " . $clausulac . "                     
                                    " . $stridtipoprodserv . "
                                    and c.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,ci.idcontapagaritem,i.idprodserv,i.valor

                                    union all
                                    SELECT 
                                    p.tipoprodserv,p.idtipoprodserv,
                                   
                                    round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(cp.valor/n.total),2) as total
                                    ,c.somarelatorio
                                    ,cp.status,cp.tipo	
                                    ,'S' as tiponf
                                    FROM notafiscal n 
                                    join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal and i.idprodserv is null ) 
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv " . $stridtipoprodserv . " )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . ") 
                                    join contapagar cp on(cp.idobjeto = n.idnotafiscal and cp.tipoobjeto ='notafiscal')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO'    
                                                                
                                    and cp .idempresa IN (" . $idempresa . ")   
                                    " . $clausulac . "                     
                                    " . $stridtipoprodserv . "
                                    and c.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,cp.idcontapagar,i.idtipoprodserv,i.valor                                    
                           union all
                             SELECT   
                                    p.tipoprodserv,p.idtipoprodserv,
                                    
                                    round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(ci.valor/n.total),2) as total
                                    ,c.somarelatorio
                                    ,cp.status,cp.tipo
                                    ,'S' as tiponf
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='notafiscal')
                                    join notafiscal n on(ci.idobjetoorigem =n.idnotafiscal  ) 
                                    join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal and i.idprodserv is null ) 
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv " . $stridtipoprodserv . " )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . ")              
                                    where cp.tipoespecifico = 'AGRUPAMENTO'  
                                    
                                    and cp .idempresa IN (" . $idempresa . ")   
                                    " . $clausulac . "                     
                                    " . $stridtipoprodserv . "
                                    and c.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,ci.idcontapagaritem,i.idtipoprodserv,i.valor

                        ) as u
                        where 1  " . $strtiponf . "
                        group by u.idtipoprodserv order by somatotal desc";

                                        echo "<!--";
                                        echo $sql;
                                        echo "-->";
                                    }

                                    $res =  d::b()->query($sql) or die("Falha ao pesquisar tipoprodserv: " . mysqli_error() . "<p>SQL: $sql");

                                    while ($row2 = mysqli_fetch_assoc($res)) {
                                        $id2 = $id2 + 1;
                                        $percent =((abs($row2["somatotal"]) * 100)/ abs($row["somatotal"]));
                                        $arrprogramado[] =  $row["somatotal"];
                                    ?>

                                        <tr>
                                            <td class="nowrap" style="width: 50%;">
                                                <?
                                                echo "<!-- soma total";
                                                echo $sql;
                                                echo "-->";
                                                ?><?= $row2["tipoprodserv"] ?>
                                            </td>
                                            <td align="right" style="width: 12.5%;"></td>
                                            <td align="right" style="width: 12.5%;">
                                                <!-- <span>                                              
                                                <?echo(number_format(tratanumero($percent), 2, ',', '.').'%');?>
                                                </span>                                             -->
                                            </td>
                                            <td align="right" >
                                                <?
                                                if($row2["somatotal"] < 0){
                                                    $row2["somatotal"] = $row2["somatotal"] * -1;
                                                }
                                                ?>
                                                R$<label class="alert-warning"><?= number_format(tratanumero($row2["somatotal"]), 2, ',', '.'); ?><label>
                                            </td>
                                           
                                            <td align="right" ><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar" data-toggle="collapse" href="#nfInfo<?= $id2 ?>" aria-expanded="false"></i></td>
                                        </tr>

                                       

                                    <?} //while ($row2 = mysqli_fetch_assoc($res))
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>

        <?

                } //$arrcontaitem
            } else {
                //if($row["tipo"]=="D"){
                //    $vlrndescriminados= $vlrndescriminados-$row["somatotal"];
                // }else{
                $vlrndescriminados = $vlrndescriminados + $row["somatotal"];
                // }

            }
        } //while ($row = mysqli_fetch_assoc($res)){
        //$vlrprogramado=0;
        foreach ($arrprogramado as $idcontapagar => $valor) {
            $vlrprogramadopagar = $vlrprogramadopagar + $valor;
        }

        $vlrcredito = $vlrquitadocredito + $vlrpendcredito;

        $vlrdebito = $vlrquitadodebito + $vlrpenddebito;

        $somatotais = $vlrcredito - $vlrdebito; //a soma do total

        $vlrprogramadox = $vlrprogramado - $vlrprogramadopagar;

        $somatotais2=$somatotais-$vlrprogramadopagar-$vlrprogramadox;

        $vlrtotal = $somatotais - $vlrprogramadox - $vlrprogramadopagar + $vlrndescriminados;

        /*
		credito recebido= todas as quitadas

		credito a receber = todas a receber

		credito total =credito recebido+credito a receber

		despesa paga= todas as pagas  quitada
		despesa  a pagar= todas despepesas não quitada status diferente aberto

		total despesa=despesa paga+despesa  a pagar

		somavalores=credito total - despesa total

		total= somavalores - despesa programada -despesa programada sem item + valoresnão descri
	*/
        ?>
</div> <!-- divbody -->
<? if ($modo != 'FT') { ?>
    <!-- <div class="panel-default">
        <div class="panel-heading">Despesas de <label class="alert-warning"> <?= dma($dataini) ?></label> á <label class="alert-warning"> <?= dma($datafim) ?></label></div>
        <div class="panel-body">
            <br>
            <table class="table table-striped planilha">
               
                <tr>
                    <? if (array_key_exists("quitarcredito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                        <td bgcolor="#c8d0ff">Crédido Recebido</td>
                        <td style="text-align: right;" bgcolor="#c8d0ff"><?= number_format(tratanumero($vlrquitadocredito), 2, ',', '.'); ?></td>
                    <? } ?>
                    <? if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                        <td bgcolor="#f0bfbf"> Despesa Paga</td>
                        <td style="text-align: right;" bgcolor="#f0bfbf"><?= number_format(tratanumero($vlrquitadodebito), 2, ',', '.'); ?></td>
                    <? } ?>
                    <?
                    if ($flgdiretor > 0) {              

                        if ($somatotais > 0) {
                            $corvalor = '#c8d0ff';
                        } else {
                            $corvalor = '#f0bfbf';
                        }
                        ?>
                        <td bgcolor="<?= $corvalor ?>">Credito Total  - Despesa Total:</td>
                        <td style="text-align: right;" bgcolor="<?= $corvalor ?>"><?= number_format(tratanumero($somatotais), 2, ',', '.'); ?> </td>       
                    <? } ?>        
                </tr>
               

                <tr>
                <? if (array_key_exists("quitarcredito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                        <td bgcolor="#98FB98">Crédito a Receber</td>
                        <td style="text-align: right;" bgcolor="#98FB98"><?= number_format(tratanumero($vlrpendcredito), 2, ',', '.'); ?></td>
                    <? } ?>
                    <? if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                        <td bgcolor="#FFFF00">Despesa a Pagar</td>
                        <td style="text-align: right;" bgcolor="#FFFF00"><?= number_format(tratanumero($vlrpenddebito), 2, ',', '.'); ?></td>
                        <? } ?>
                    <? if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                        <td bgcolor="#FF8C00" title="Débito aberto com itens na fatura">Despesa Programada</td>
                        <td style="text-align: right;" bgcolor="#FF8C00" title="Débito aberto com itens na fatura"><?= number_format(tratanumero($vlrprogramadopagar), 2, ',', '.'); ?> </td>
                    <? } ?>
                </tr>
                <tr>
                    <? if (array_key_exists("quitarcredito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                        <td bgcolor="">Crédito Total</td>
                        <td style="text-align: right;" bgcolor=""><b><?= number_format(tratanumero($vlrcredito), 2, ',', '.'); ?></b></td>
                    <? } ?>
                    <? if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                        <td bgcolor="">Despesa Total</td>
                        <td style="text-align: right;" bgcolor=""><b><?= number_format(tratanumero($vlrdebito), 2, ',', '.'); ?></b></td>
                    <? }
                  
                     if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                         <td bgcolor="#FF8C00" style="border-bottom: 1px solid #666;" title="Diferença entre valor provisionado e item lançado, ex fat cartão credito">Despesa Provisionada</td>
                        <td style="text-align: right; border-bottom: 1px solid #666;" bgcolor="#FF8C00" title="Diferença entre valor provisionado e item lançado, ex fat cartão credito"><?= number_format(tratanumero($vlrprogramadox), 2, ',', '.'); ?> </td>
                    <? } ?>
                </tr>
                <tr>
                <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    <?
                    if ($flgdiretor > 0) {

                        if ($somatotais > 0) {
                            $corvalor = '#c8d0ff';
                        } else {
                            $corvalor = '#f0bfbf';
                        }                        
                    ?>
                        <td bgcolor="<?= $corvalor ?>"><b>Soma dos valores</b></td>
                        <td style="text-align: right;" bgcolor="<?= $corvalor ?>"><b><?= number_format(tratanumero($somatotais2), 2, ',', '.'); ?></b> </td>
                    <? } ?>
                </tr>
                <? if ($flgdiretor > 0) { ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td bgcolor="" style="border-bottom: 1px solid #666;" title="Transação de valores entre contas">Valores não Discriminados</td>
                        <td style="text-align: right; border-bottom: 1px solid #666;" bgcolor="" title="Transação de valores entre contas"><b><?= number_format(tratanumero($vlrndescriminados), 2, ',', '.'); ?></b></td>
                    </tr>
                    <?
                    if ($vlrtotal > 0) {
                        $corvalor = '#c8d0ff';
                    } else {
                        $corvalor = '#f0bfbf';
                    }
                    ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td bgcolor="<?= $corvalor ?>" title="Soma dos valores - Despesa Programada + Valores não Descriminados"><b>Total</b></td>
                        <td style="text-align: right;" bgcolor="<?= $corvalor ?>" title="Soma dos valores-Despesa Programada+Valores não Descriminados">
                            <b><?= number_format(tratanumero($vlrtotal), 2, ',', '.'); ?></b>
                        </td>
                    </tr>
                <? } ?>
            </table>

            <hr>
            <?           
//             foreach ($arrcontaitem as $idcontaitem => $v) {
//                // echo [$idcontaitem]."=>".$v['faturamento']."= >".$v['total']."\n";
//                 if($v['faturamento']=='C'){
//                     $percentual=(($v['total']*100)/$vlrcredito);
//                 }else{
//                     $percentual=(($v['total']*100)/$vlrdebito);
//                 }
                
// ?>
<!-- //                 <span class="pcontaitem hidden" id='idcontaitem_<?=$idcontaitem?>'>
//                      <b><?= number_format(tratanumero($percentual), 2, ',', '.'); ?>%</b>
//                 </span> -->
 <?

//             }
            ?>


          
            <!-- table class="table table-striped planilha" >
            <tr>
                <? if (array_key_exists("quitarcredito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
            <td bgcolor="#c8d0ff">CRÉDITO</td>
            <td bgcolor="#c8d0ff"><?= number_format(tratanumero($vlrcredito), 2, ',', '.'); ?> </td>
                <? } ?>
                <? if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
            <td bgcolor="#f0bfbf">DÉBITO</td>
            <td bgcolor="#f0bfbf"><?= number_format(tratanumero($vlrdebito), 2, ',', '.'); ?></td>
                <?
                }
                if ($flgdiretor > 0) {
                ?>
            <td bgcolor="<?= $cortrfim ?>">SOMA VALORES</td>
            <td bgcolor="<?= $cortrfim ?>"><?= number_format(tratanumero($somatotais), 2, ',', '.'); ?></td>
                <? } ?>
            </tr>
            <tr>
                <? if (array_key_exists("quitarcredito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
            <td bgcolor="#98FB98">CRÉDITO PENDENTE</td>
            <td bgcolor="#98FB98"><?= number_format(tratanumero($vlrpendcredito), 2, ',', '.'); ?></td>
                <? } ?>
                <? if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) {
                    $debpend = $vlrpenddebito - $vprogramado;
                ?>
            <td bgcolor="#FFFF00">DÉBITO PENDENTE</td>
            <td bgcolor="#FFFF00"><?= number_format(tratanumero($debpend), 2, ',', '.'); ?></td>
                <?
                }
                if ($flgdiretor > 0) { ?>
            <td bgcolor="#FFFACD">SOMA PENDENTES</td>
            <td bgcolor="#FFFACD"><?= number_format(tratanumero($vlrpendentefim), 2, ',', '.'); ?></td>
                <? } ?>
            </tr>

            <tr>
                <? if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
            <td bgcolor="#FF8C00">PROGRAMADO</td>
            <td bgcolor="#FF8C00" title="Debito Programado/Aberto"><?= number_format(tratanumero($vlrprogramado), 2, ',', '.'); ?></td>
                <? } ?>
            </tr>
        </table>
            <br>
	    <table class="table table-striped planilha">	
		    <tr style="height: 5px;"></tr>
		    <tr>
			    <td colspan='2'><font size="2">Previsão</font></td>
			    <td align="right"><font size="2"><?= number_format(tratanumero($previsao), 2, ',', '.'); ?></font></td>
		    </tr>	
		    <tr>
			    <td colspan='2'><font size="2">Total</font></td>
			    <td align="right"><font size="2"><?= number_format(tratanumero($vtotal), 2, ',', '.'); ?></font></td>
		    </tr>
	    </table 
        </div>
    </div> -->
<? } else {
?>
    <br>
    <p></p>
    <br>
<? } 

    } elseif ($ires < 1 and !empty($vencimento_1) and !empty($vencimento_2)) {
        echo '
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-center" style="color:red;">Não foram encontrados valores com estas configurações.</h2>
            </div>
        </div>
        ';
    }
?>
