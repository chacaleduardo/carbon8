<?


if(!empty($_idobjeto))
{// Não permitir concluir uma nota dos tipos do select sem rateio
    $problema = array();

    $sqls="select n.*
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
    $qtd=mysqli_num_rows($ress);
    $i=0;
    if($qtd>0){
        $statusfluxo = 'CONCLUIDO';
        $statuspendente = 'Y';
        $problema[$i] = 'RATEIO';
        $i++;
    }else{
        $statusfluxo = 'CONCLUIDO';
        $statuspendente = 'N';
         $i++;
    } 
    
    $j = 0;
    $sqlValidaDataEntrada = "SELECT 1 FROM nf WHERE idnf = $_idobjeto AND prazo IS NULL";
    $resValidaDataEntrada = d::b()->query($sqlValidaDataEntrada) or die("Erro ao buscar prazo sql = ".$sqlValidaDataEntrada);
    $qtdValidaDataEntrada = mysqli_num_rows($resValidaDataEntrada);
    if($qtdValidaDataEntrada > 0){
        $statusfluxo = 'CONCLUIDO';
        $statuspendente = 'Y';
        $problema[$i] = 'DATAENTRADACONTROLENF';
        $j++;
    }else{
        $statusfluxo = 'CONCLUIDO';
        $statuspendente = 'N';
        $j++;
    }        

    $sqls="SELECT 
        idnfitem, idcontaitem, idtipoprodserv
    FROM
        nfitem
    WHERE
        idnf = $_idobjeto
            AND (idcontaitem IS NULL
            OR idtipoprodserv IS NULL)";

    $ress=d::b()->query($sqls) or die("Erro ao buscar categoria e tipo da nota sql=".$sqls);
    $qtd=mysqli_num_rows($ress);
    if($qtd>0){
        $statusfluxo = 'COBRANCA';
        $problema[$i] = 'CONTAITEM';
        $statuspendente = 'Y';
        $i++;
    } else{
        $statusfluxo = 'COBRANCA';
        $statuspendente = 'N';
        $i++;
    } 
    
    $sqls="select n.* from nf n where n.idnf=".$_idobjeto;    
    $ress=d::b()->query($sqls) or die("Erro ao buscar rateios da nota sql=".$sqls);
    $row = mysqli_fetch_assoc($ress);
    if( $row['tiponf']=='T' and empty($row['xmlret'])){
        $i++;
        $statuspendente = 'Y';
        $problema[$i] = 'XMLASSOCIADOCTE';
        $statusfluxo = 'CONCLUIDO';

    }
}

$status['permissao']['modulo'] = 'nfcte';
$status['permissao']['esconderbotao'] = $statuspendente;
$status['permissao']['status'] = $statusfluxo;
$status['permissao']['problema'] = $problema;
?>
