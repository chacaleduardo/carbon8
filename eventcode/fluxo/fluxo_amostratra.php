<?
$problema = array();

include_once (__DIR__.'/../../form/controllers/inclusaoresultado_controller.php');
if(!empty($_idobjeto))
{// Não permitir concluir uma nota dos tipos do select sem rateio
    if(AmostraController::exigeConferenciaAmostra($_idobjeto)){
        if(array_key_exists("conferetra", getModsUsr("MODULOS")) != 1){
            $escondebotao = 'Y';
            $problema[$i] = 'AMOSTRATRA';
            $i++;
        }else{
            $escondebotao = 'N';
            $i++;
        }                   
    }                   
}

$status['permissao']['modulo'] = 'amostratra';
$status['permissao']['esconderbotao'] = $escondebotao;
$status['permissao']['status'] = 'CONFERIDO';
$status['permissao']['problema'] = $problema;




?>
