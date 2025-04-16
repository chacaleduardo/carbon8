<?
if(!empty($_idobjeto))
{// Não permitir concluir uma nota dos tipos do select sem rateio
    $sqls="select *
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
    $qtd=mysqli_num_rows($ress);
    if($qtd>0){
        $statuspendente = 'Y';
    }else{
        $statuspendente = 'N';
    } 
                             
}

$status['permissao']['modulo'] = 'comprassocios';
$status['permissao']['esconderbotao'] = $statuspendente;
$status['permissao']['status'] = 'CONCLUIDO';
?>
