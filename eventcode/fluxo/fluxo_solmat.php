<?
$problema = array();

include_once (__DIR__.'/../../form/controllers/solmat_controller.php');

$escondebotao='N';

if(!empty($_idobjeto))
{// Não permitir concluir uma nota dos tipos do select sem rateio
   

    $itemPendenteAprovacao=SolmatController::buscarSolmatitemPendente($_idobjeto);	

    if($itemPendenteAprovacao['pendente']=='Y'){
      
        $escondebotao = 'Y';
        $problema[$i] = 'SOLMATITEMPENDENTE';
        $i++;
        $mensagem='Item pendente para aprovação na solicitação de materiais.';
    }else{
        $escondebotao = 'N';
        $i++;

        $itemEstoque=SolmatController::buscarSolmatEstoque($_idobjeto);	

        foreach ($itemEstoque as $Estoque) 
        {
            if($Estoque['qtd'] < $Estoque['qtdc']){

                $escondebotao = 'Y';
                $problema[$i] = 'SOLMATITEMPENDENTE';
                $mensagem .=" ITEM ".$Estoque['descr']." SEM ESTOQUE DISPONÍVEL (".$Estoque['qtd'].") "; 
                $i++;
                
            }
        }   

    }                   
        
    

}


    $status['permissao']['modulo'] = 'solmat';
    $status['permissao']['esconderbotao'] = $escondebotao;
    $status['permissao']['status'] = 'SOLICITADO';
    $status['permissao']['problema'] = $problema;
    $status['permissao']['mensagem'] =  $mensagem;


?>