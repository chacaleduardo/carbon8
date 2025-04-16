<?
require_once("../inc/php/functions.php");
require_once(__DIR__."/../form/controllers/rebioensaio_controller.php");

$idtagtipo = $_POST['idtagtipo'];
$idtagpai = $_POST['idtagpai'];
$idunidadepadrao = $_POST['idunidadepadrao'];

if(!empty($idtagtipo) and !empty($idtagpai) && !empty($idunidadepadrao)){

    $res5=RebioensaioController::buscarFilhosDeUmaSala($idunidadepadrao,$idtagpai);
    $qtd5=count($res5);
    if($qtd5>0){
        foreach($res5 as $k => $r5){?>
            <div>
	        <?
            $resb=RebioensaioController::buscarEstudosDeUmaGaiola($r5['idtag']);
            $qtdb=count($resb);
            if($qtdb>0){
                $i = 0;
                foreach($resb as $k1 => $rowb){
                    if($rowb['mmin']=='S' and $rowb['mmax']=='S'){
                        $stropacity="";
                    }else{
                        $stropacity="opacity: 0.4; ";
                    }
                    if($rowb['diasvida']>0){
                        $diasvida=$rowb['diasvida']." dias";
                    }else{
                        $diasvida="";
                    }
                    if($rowb['status']=='ATIVO'){
                        $cor="black";
                        $corf="#78b5eb";//azul
                    }elseif($rowb['status']=='DISPONIVEL'){
                        $cor="red";
                        $corf="#f8d2b76b;";//vermelho
                    }elseif($rowb['status']=='RESERVADO'){
                        $cor="black";
                        $corf="#78b5eb";//azul
                    }?>		
                    <div  class="linhacab">
                        <div  class="linha">
                            <div class="coluna" style="<?=$stropacity?> background-color:<?=$corf?>;" title="<?=$rowb['obs']?>">
                                <table align="Center">
                                    <tr >
                                        <td style="font-size: 11px;" align="center">
                                            <a title="Ver Bioensaio" style=" color: <?=$cor?>;" href="javascript:janelamodal('?_modulo=<?=RebioensaioController::buscarModuloPadrao('bioensaio',$idunidadepadrao)?>&_acao=u&idbioensaio=<?=$rowb['idbioensaio']?>')">
                                                B<?=$rowb['idregistro']?>-<?=$rowb['bioensaio']?>
                                            </a>
                                        </td>
                                    </tr>
                                    
                                    <tr style="color:<?=$cor?>;" >
                                        <td style="font-size: 11px;" align="center" >
                                            <i title="<?=$rowb['status']?>" class="fa fa-bookmark <?=$cor?>"></i>
                                        <?if(!empty($rowb['gaiola'])){?>
                                            <?=$rowb['gaiola']?>	
                                            <?}?>
                                        </td>
                                    </tr>

                                    <tr style="color:<?=$cor?>" >
                                        <td style="font-size: 11px;" align="center" >
                                            <i title="<?=$rowb['status']?>" class="fa fa-bookmark <?=$cor?>"></i>
                                        <?if(!empty($rowb['idanalise'])){?>
                                            <a style="color: <?=$cor?>;" title="Ver Bioensaio" href="javascript:janelamodal('?_modulo=<?=RebioensaioController::buscarModuloPadrao('bioensaio',$idunidadepadrao)?>&_acao=u&idbioensaio=<?=$rowb['idbioensaio']?>')">
                                                Protocolo - <?=$rowb['idanalise']?>
                                            </a>
                                            <?}?>
                                        </td>
                                    </tr>
                                    <tr  style="color:<?=$cor?>">
                                        <td style="font-size: 11px;" align="center">
                                        <?echo($rowb['qtd']." Animais ")?> <?echo($rowb['tipo'])?> <font color="red"><?=$diasvida?></font> 
                                        </td>
                                    </tr>		
                                    <tr  style="color:<?=$cor?>" >
                                        <td style="font-size: 10px;" align="center" >
                                        <?echo($rowb['inicio1']." - ".$rowb['fim1']);?>					
                                        </td>
                                    </tr>
                                </table>
                            </div>		
                        </div>
                    </div>
                <?}//while($rowb=mysqli_fetch_assoc($resb))
            }?>	  	
            </div>
        <?}// while($r1 = mysqli_fetch_assoc($res5))
    }
}


?>