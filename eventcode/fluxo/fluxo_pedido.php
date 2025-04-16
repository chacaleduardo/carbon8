<?
if(!empty($_idobjeto))
{
    require_once("../form/controllers/pedido_controller.php");

    $statuspendente = 'N';
    $qr = "SELECT tpnf, idformapagamento FROM nf WHERE idnf = ".$_idobjeto;
    $rs = d::b()->query($qr);
    $rw = mysqli_fetch_assoc($rs);
    $problema = array();   
    $i=0;
    
    if($rw["tpnf"] != "0"){
        $sqls="select 
                    idprodserv,idnfitem,idlote,sum(qtd) as qtd, sum(qtds) as qtds,
                    if(sum(qtd)> sum(qtds),'F','O') as sit
                from (
                         select p.idprodserv,n.idnfitem,0 as qtd,r.idlote,(ifnull(r.qtd,0)) as qtds
                            from nfitem n 
                                join prodserv p
                                join lotereserva r on(r.tipoobjeto = 'nfitem' 
                                and r.qtd > 0 
                                and r.idobjeto  = n.idnfitem)
                            where p.idprodserv = n.idprodserv
                                and p.venda='Y'
                                and n.manual <> 'Y'
                                and n.idnf=".$_idobjeto."
                    union 
                          select p.idprodserv,n.idnfitem,0 as qtd,c.idlote,(c.qtdd+c.qtdc) as qtds
                                from nfitem n 
                                    join prodserv p
                                    join lotecons c on(c.tipoobjeto = 'nfitem' and (c.qtdd > 0 or c.qtdc > 0) and c.idobjeto  = n.idnfitem)                                  
                                where p.idprodserv = n.idprodserv
                                    and p.venda='Y'
                                    and n.manual <> 'Y'
                                    and n.idnf=".$_idobjeto."  
                        union  
                            select p.idprodserv,n.idnfitem,0 as qtd,l.idlote,f.qtd as qtds
                                from nfitem n 
                                    join prodserv p	
                                    left join lote l on(l.idprodservformula=n.idprodservformula and l.idprodserv = p.idprodserv and l.status!='ESGOTADO' and l.status!='CANCELADO' )
                                    left join lotefracao f on(f.idlote = l.idlote and f.qtd>0)
                                where p.idprodserv = n.idprodserv
                                    and not exists  ( select 1 from lotecons c where c.tipoobjeto = 'nfitem' and (c.qtdd > 0 or c.qtdc > 0) and c.idobjeto  = n.idnfitem)
                                    and p.venda='Y'
                                    and n.manual <> 'Y'
                                    and n.idnf=".$_idobjeto."  
                        union
                            select  p.idprodserv,n.idnfitem,n.qtd,0 as idlote,0 as qtds
                                from nfitem n,prodserv p 
                                where p.idprodserv = n.idprodserv
                                    and p.venda='Y'
                                    and n.manual <> 'Y'
                                    and n.idnf=".$_idobjeto."                 
                    ) u  group by u.idnfitem,u.idprodserv";
        $ress=d::b()->query($sqls) or die("Erro consumos da nota sql=".$sqls);
        while($rws=mysqli_fetch_assoc($ress)){                            
            if($rws['sit'] == 'F'){
                $statuspendente = 'Y';
            }                            
        }
        $sqlx="select n.idnfitem,c.idlote,l.status,r.idlote as idloter,l2.status as statusr,n.manual
            from nfitem n join prodserv p
            left join lotecons c on(c.tipoobjeto = 'nfitem' 
                                    and (c.qtdd > 0 or c.qtdc > 0)
                                    and c.idobjeto  = n.idnfitem)
            left join lote l on(l.idlote=c.idlote)
            left join lotereserva r on(r.tipoobjeto = 'nfitem' 
                                    and r.qtd > 0 
                                    and r.idobjeto  = n.idnfitem)
                                    left join lote l2 on(r.idlote=l2.idlote)
            where p.idprodserv = n.idprodserv
        -- and p.venda='Y'
            and n.idnf=".$_idobjeto;
        $resx=d::b()->query($sqlx) or die("Erro consumos dos itens da nota sql=".$sqlx);
        $qtdl= mysqli_num_rows($resx);
        
        $sql2x="select * from nfitem 
                where idnf=".$_idobjeto."
                and (idprodserv is null or idprodserv = '')";
        $res2x=d::b()->query($sql2x) or die("Erro consumos dos itens da nota sql=".$sql2x);
        $qtd2= mysqli_num_rows($res2x);
        
        if($qtdl<1 and $qtd2<1){ $statuspendente = 'Y'; }

        while($rwx=mysqli_fetch_assoc($resx)){                            
            if(empty($rwx['idlote']) and empty($rwx['idloter']) and $rwx['manual'] != 'Y'){
                $statuspendente = "Y";
                $problema[$i]="SEM LOTE";
            }elseif(($rwx['status']!="APROVADO" 
                        AND $rwx['status']!="ESGOTADO"  AND $rwx['status']!="QUARENTENA" AND $rwx['status']!="LIBERADO" AND $rwx['status']!="CANCELADO" 
                        AND $rwx['statusr']!="APROVADO"
                        AND $rwx['statusr']!="LIBERADO" 
                        AND $rwx['statusr']!="QUARENTENA"
                        AND $rwx['statusr']!="ESGOTADO" 
                        AND $rwx['statusr']!="CANCELADO") AND $rwx['manual'] != 'Y'){
                $statuspendente = 'Y';
                $problema[$i]="LOTE SEM APROVAR";
            }                            
        }
    }

    if($statuspendente!='Y'){

        $sqlc = "select i.idcontapagaritem,i.datapagto,i.valor,i.status as status_item,p.nome,p.idpessoa,c.idcontapagar,c.datareceb,c.status, c.parcelas,c.parcela
        from contapagar ci
            join contapagaritem i on(ci.idcontapagar = i.idobjetoorigem and i.tipoobjetoorigem='contapagar' )    
            left join contapagar c on (c.idcontapagar = i.idcontapagar)
            join pessoa  p on(p.idpessoa = i.idpessoa	)
        where ci.idobjeto =" . $_idobjeto . "
            and not exists(select 1 from contapagaritem ii where ii.idcontapagar=ci.idcontapagar)
            and ci.tipoobjeto like ('nf%')  
        union
        select i.idcontapagaritem,i.datapagto,i.valor,i.status as status_item,p.nome,p.idpessoa,c.idcontapagar,c.datareceb,c.status, c.parcelas,c.parcela
            from contapagaritem ci  
                join contapagaritem i on(ci.idcontapagar = i.idobjetoorigem and i.tipoobjetoorigem='contapagar' )    
                join contapagar c on (c.idcontapagar = i.idcontapagar and c.tipoespecifico!='IMPOSTO')
                join pessoa  p on(p.idpessoa = i.idpessoa	)
        where ci.idobjetoorigem=" . $_idobjeto . " and ci.tipoobjetoorigem like 'nf%' order by parcela";
        $rescom = d::b()->query($sqlc) or die("Falha ao buscar comissões da nota:" . mysqli_error(d::b()));
        $qrcom = mysqli_num_rows($rescom);
        if ($qrcom > 0) { 
            $sqlcom=" select c.idpessoa,round(sum(n.total*(c.pcomissao/100)),2) as comissao
                        from nfitem n join nfitemcomissao c on(c.idnfitem = n.idnfitem) JOIN pessoa p ON p.idpessoa = c.idpessoa AND p.status IN ('PENDENTE', 'ATIVO')
                        where  n.idnf=" . $_idobjeto . " and n.nfe='Y'";    
            $restcom = d::b()->query($sqlcom) or die("Falha ao buscar total comissão:" . mysqli_error(d::b()));
            $rowtotalcom=mysqli_fetch_assoc($restcom);
            while ($rowp2 = mysqli_fetch_array($rescom)) {
                $totalcom=$totalcom+$rowp2['valor'];   
            }

            $diferencacom= number_format($rowtotalcom['comissao'],2) - number_format($totalcom,2);
            if((number_format($rowtotalcom['comissao'],2)!=number_format($totalcom,2)) and abs($diferencacom) > 1 ){
                $i=0;
                $problema[$i] = 'COMISSAO';
                $statuspendente = 'Y';
                $status['permissao']['modulo'] = 'pedido';
                $status['permissao']['esconderbotao'] = $statuspendente;
                $status['permissao']['status'] = 'CONCLUIDO';
                $status['permissao']['problema'] = $problema;
            }else{

                $status['permissao']['modulo'] = 'pedido';
                $status['permissao']['esconderbotao'] = 'N';
                $status['permissao']['status'] = 'FATURAR';
                $status['permissao']['problema'] = $problema;
            }
        }else{
            $status['permissao']['modulo'] = 'pedido';
            $status['permissao']['esconderbotao'] = 'N';
            $status['permissao']['status'] = 'FATURAR';
            $status['permissao']['problema'] = $problema;
        }

    }else{
        $i=0;
        $status['permissao']['modulo'] = 'pedido';
        $status['permissao']['esconderbotao'] = $statuspendente;
        $status['permissao']['status'] = 'FATURAR';
        $status['permissao']['problema'] = $problema;
    }

    if($statuspendente!='Y'){
        
        $sqls="select
                n.total,sum(ifnull(i.valor,0)) as valor ,n.geracontapagar
            from nf n 
                left join contapagaritem i on(i.idobjetoorigem = n.idnf and i.tipoobjetoorigem = 'nf' and i.status!='INATIVO')
            where n.idnf=".$_idobjeto;

        $res = d::b()->query($sqls) or die("erro ao buscar valores do pedido e faturas " . mysqli_error(d::b()) . "<p>SQL: ".$sqls);

        $_row = mysqli_fetch_assoc($res);

        if($_row['total'] != $_row['valor'] and  $_row['geracontapagar'] == 'Y' ){
        
            $i=0;
            $problema[$i] = 'TOTALNFVALORFAT';
            $statuspendente = 'Y';
            $status['permissao']['modulo'] = 'pedido';
            $status['permissao']['esconderbotao'] = $statuspendente;
            $status['permissao']['status'] = 'CONCLUIDO';
            $status['permissao']['problema'] = $problema;
        }else{
            $i=0;
            $status['permissao']['modulo'] = 'pedido';
            $status['permissao']['esconderbotao'] = $statuspendente;
            $status['permissao']['status'] = 'FATURAR';
            $status['permissao']['problema'] = $problema;
            }

    
    
    }else{

        $i=0;
        $status['permissao']['modulo'] = 'pedido';
        $status['permissao']['esconderbotao'] = $statuspendente;
        $status['permissao']['status'] = 'FATURAR';
        $status['permissao']['problema'] = $problema;
    }



    $qrp = PedidoController::buscarFaturaBoletoPorNf($_idobjeto);
    $formapagamento = traduzid("formapagamento", "idformapagamento", "formapagamento", $rw["idformapagamento"]);
    if(count($qrp) == 0  && $formapagamento == 'BOLETO'){
        $problema[$i+1] = "FLEGARBOLETO";
        $status['permissao']['modulo'] = 'pedido';
        $status['permissao']['esconderbotao'] = "Y";
        $status['permissao']['status'] = 'ENVIAR';
        $status['permissao']['problema'] = $problema;
    }

    $sqlclause = "SELECT geracontapagar FROM nf WHERE idnf = ".$_idobjeto.";";

    $ressqlclause = d::b()->query($sqlclause) or die("erro ao buscar valores do pedido e faturas " . mysqli_error(d::b()) . "<p>SQL: ".$sqlclause);

    $_rowsqlclause = mysqli_fetch_assoc($ressqlclause);

    if($_rowsqlclause['geracontapagar'] == 'Y'){
        
        $selectClause = "
                    IF(dtemissao IS NULL, 1, 0) +
                    IF(idformapagamento IS NULL, 1, 0) +
                    IF(validade IS NULL, 1, 0) +
                    IF(diasentrada IS NULL, 1, 0) +
                    IF(parcelas IS NULL, 1, 0)";
    }else{
        $selectClause = "IF(dtemissao IS NULL, 1, 0)";
    }
        
    $sqln = "SELECT SUM(
                ".$selectClause."
            ) AS totalnulos
            FROM nf
            WHERE idnf = ".$_idobjeto.";";

   $sqlnl =  d::b()->query($sqln) or die("erro ao buscar valores do pedido e faturas " . mysqli_error(d::b()) . "<p>SQL: ".$sqln);
    $_rown = mysqli_fetch_assoc($sqlnl);

    if($_rown['totalnulos'] > 0){
        $i=0;
        $problema[$i] = 'SOLICITADONULL';
        $statuspendente = 'Y';
        $status['permissao']['modulo'] = 'pedido';
        $status['permissao']['esconderbotao'] = $statuspendente;
        $status['permissao']['status'] = 'SOLICITADO';
        $status['permissao']['problema'] = $problema;
    }
} 
?>