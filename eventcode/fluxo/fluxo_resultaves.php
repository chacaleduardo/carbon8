<?
 $problema = array();

if(!empty($_idobjeto))
{// Não permitir concluir uma nota dos tipos do select sem rateio
    $sqls="SELECT * from resultado r
                join fluxostatushist fh on (fh.idfluxostatus =r.idfluxostatus and fh.alteradopor = '".$_SESSION['SESSAO']['USUARIO']."' and fh.idmodulo = r.idresultado and fh.modulo = 'resultaves')
            Where r.status = 'FECHADO' and r.idresultado = ".$_idobjeto;    
  
    $ress=d::b()->query($sqls);
    $qtd=mysqli_num_rows($ress);
    $i=0;
    if($qtd>0){
        $escondebotao = 'Y';
        $problema[$i] = 'RESULTADO';
        $i++;
    }else{
        $escondebotao = 'N';
         $i++;
    } 
    $sqls="SELECT * from resultado r
            Where r.status = 'ASSINADO' and r.idresultado = ".$_idobjeto;    
  
    $ress=d::b()->query($sqls);
    $qtd=mysqli_num_rows($ress);
    $i=0;
    if($qtd>0){
        $status['esconderRestaurar'] = 'Y';
    }
                             
}

$status['permissao']['modulo'] = 'resultaves';
$status['permissao']['esconderbotao'] = $escondebotao;
$status['permissao']['status'] = 'CONFERIDO';
$status['permissao']['problema'] = $problema;




?>
