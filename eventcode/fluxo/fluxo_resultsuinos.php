<?
 $problema = array();

 include_once (__DIR__.'/../../form/controllers/inclusaoresultado_controller.php');
if(!empty($_idobjeto))
{// Não permitir concluir uma nota dos tipos do select sem rateio
    $idamostra = traduzid('resultado','idresultado','idamostra',$_idobjeto,false);
    if(InclusaoResultadoController::exigeConferenciaAmostra($idamostra)){
        $escondebotao = 'Y';
        $problema[0] = 'RESULTADOTRA';
        $i++;
    }else{
        $escondebotao = 'N';
         $i++;
    } 
   
                             
}

$status['permissao']['modulo'] = 'resultsuinos';
$status['permissao']['esconderbotao'] = $escondebotao;
$status['permissao']['status'] = 'FECHADO';
$status['permissao']['problema'] = $problema;




?>
