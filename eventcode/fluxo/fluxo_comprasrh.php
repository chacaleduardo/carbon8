<?
if(!empty($_idobjeto))
{
    $problema = [];
    $i = 0;
    // Não permitir concluir uma nota dos tipos do select sem rateio
    $sqls="select CONCAT(prodservdescr) AS prodservdescr
                    from nf n 
                    join  nfitem i  on(i.idnf=n.idnf and  i.idpessoa is  null)	
                    join contaitem c on(c.idcontaitem= i.idcontaitem and c.somarelatorio = 'Y')	
                where n.tiponf in ('T','S','M','E','R','D','B') 
                and n.tipocontapagar='D'
                and not exists (select 1 from lote l where l.idnfitem=i.idnfitem) 
                and not exists(select 1 from  rateioitem ri  
                                            join rateioitemdest rd on(rd.idrateioitem = ri.idrateioitem)
                                            where( ri.idobjeto = i.idnfitem 
                                            and ri.tipoobjeto = 'nfitem' )
                                    )
                and n.idnf=".$_idobjeto;    
  
    $ress=d::b()->query($sqls) or die("Erro ao buscar rateios da nota sql=".$sqls);
    $row = mysqli_fetch_assoc($ress);
    $qtd = mysqli_num_rows($ress);
    if($qtd>0){
        $statuspendente = 'Y';
        $problema[$i] = 'RATEIORH';
    }else{
        $statuspendente = 'N';
        $prodservdescr = '';
    } 

    $sqls="SELECT n.total, sum(ifnull(i.valor,0)) AS valor, n.geracontapagar, SUM(IFNULL(c.valor, 0)) AS valorcontapagar, i.ajuste
            FROM nf n LEFT JOIN contapagaritem i ON i.idobjetoorigem = n.idnf AND i.tipoobjetoorigem = 'nf' AND i.status != 'INATIVO' AND i.idpessoa != 0
            LEFT JOIN contapagar c ON c.idobjeto = n.idnf AND c.tipoobjeto = 'nf' AND c.status != 'INATIVO' 
            WHERE n.idnf = $_idobjeto  AND (i.obs NOT LIKE '%JUROS%' AND i.obs NOT LIKE '%MULTA%');";

    $res = d::b()->query($sqls) or die("erro ao buscar valores do pedido e faturas " . mysqli_error(d::b()) . "<p>SQL: ".$sqls);

    $_row = mysqli_fetch_assoc($res);

    if(($_row['total'] != $_row['valor'] && $_row['geracontapagar'] == 'Y' && $_row['geracontapagar'] == 'N')
        || ($_row['total'] != $_row['valorcontapagar'] && $_row['geracontapagar'] == 'Y' && $_row['geracontapagar'] == 'Y')){    
        $i++;
        $statuspendente = 'Y';
        $problema[$i] = 'TOTALNFVALORFATCOMPRA';
    }
                             
}

$status['permissao']['modulo'] = 'comprasrh';
$status['permissao']['esconderbotao'] = $statuspendente;
$status['permissao']['status'] = 'CONCLUIDO';
$status['permissao']['problema'] = $problema;
$status['permissao']['nome'] = $row['prodservdescr'];
?>
