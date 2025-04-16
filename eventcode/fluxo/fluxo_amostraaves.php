<?
 $problema = array();

if(!empty($_idobjeto))
{// Não permitir concluir uma nota dos tipos do select sem rateio
    $sqls="SELECT * from amostra a
                 where a.criadopor = '".$_SESSION['SESSAO']['USUARIO']."' and a.idamostra = ".$_idobjeto;    
  
    $ress=d::b()->query($sqls);
    $qtd=mysqli_num_rows($ress);
    $i=0;
    if($qtd>0){
        $escondebotao = 'Y';
        $problema[$i] = 'AMOSTRA';
        $i++;
    }else{
        $escondebotao = 'N';
         $i++;
    }                   
}

$status['permissao']['modulo'] = 'amostraaves';
$status['permissao']['esconderbotao'] = $escondebotao;
$status['permissao']['status'] = 'CONFERIDO';
$status['permissao']['problema'] = $problema;




?>
