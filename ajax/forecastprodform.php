<?
require_once("../inc/php/functions.php");
require_once(__DIR__."/../form/controllers/planejamentoprodserv_controller.php");


$jwt = validaToken();

// if($jwt["sucesso"] !== true){
//     echo JSON_ENCODE([
//         'error' => "Erro: Não autorizado."
//     ]);
//     die;
// }

if(!empty($_GET['idprodserv'])){
    $vari=1;
    $idprodserv = $_GET['idprodserv'];
    $idempresa = traduzid('prodserv','idprodserv','idempresa',$idprodserv);
    $idprodservformula = $_GET['idprodservformula'];

    $meses = array( 1 => 'JAN','FEV','MAR','ABR','MAI','JUN','JUL','AGO','SET','OUT','NOV','DEZ');

    $listarUnidadesEmpresa = PlanejamentoProdServController::buscarUnidadesPorUnidadeObjeto($idprodserv, 'prodserv', "  AND u.requisicao = 'Y' and u.idtipounidade !=3 AND u.idempresa = ".$idempresa);?>
    <div class="panel-body collapse in" id="dados<?=$idprodserv.$idprodservformula?>">
        <?
        foreach($listarUnidadesEmpresa as $unidadeEmpresa) {?>
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading " style="display: inline-flex; align-items: center; width: 100%; justify-content: space-between;">
                        <div>
                            <?=$unidadeEmpresa['unidade']?>                         
                        </div>
                    </div>
                    <div class="panel-body" style="padding-top: 2px !important;">	
                    <?

                        $lExercicio = PlanejamentoProdServController::buscarPlanejamentoprodservExercicio($idprodserv, $unidadeEmpresa['idunidade'],$idprodservformula);
                        sort($lExercicio);
                        foreach ($lExercicio as $exercicio) 
                        {

                            $lMes = PlanejamentoProdServController::buscarPlanejamentoprodservMes($idprodserv, $unidadeEmpresa['idunidade'],$exercicio['exercicio'],$idprodservformula);
                    ?>
                    
                    <table style="width: 100%;">                
                    <tr>
                        <td style="width: 7%;"></td>
                        <td style="width: 7%;"><b>MÊS</b></td>
                        <?
                            $m=0;
                            foreach ($lMes as $mes) 
                            {
                                $m++;
                                $vari++;
                            ?>
                            <td style="width: 7%;" class="nowrap">
                            <div class="col-md-4" style="padding-left: 2px;"> 
                                <?=$meses[$m]?>
                            </div>
                            <div class="col-md-4"> 
                                <?if(!empty($mes['planejado'])){?> 
                                <i class="fa fa-pencil pointer" title='Editar Planejamento' onclick="alteravalor('planejado','<?=$mes['planejado']?>','modulohistorico',<?=$mes['idplanejamentoprodserv']?>,'Planejamento:')"></i>
                            <?}?>
                            </div>
                            <div class="col-md-4"> 
                                    <?
                                    $ListarHistoricoModal = PlanejamentoProdServController::buscarHistoricoModuloAlteracao($mes['idplanejamentoprodserv'], 'planejamentoprodserv','planejado');
                                    $qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
                                    if ($qtdvh > 0) 
                                    {
                                        ?>
                                        
                                        <div class="historicoEnvio" idplanejamentoprodserv="<?=$mes['idplanejamentoprodserv']?>">
                                            <i title="Histórico do Planejamento" class="fa  fa-info-circle preto pointer hoverazul tip" data-target="webuiPopover0"></i>
                                        </div>
                                        <div class="webui-popover-content">
                                            <br/>
                                            <table class="table table-striped planilha">
                                                <?
                                                if($qtdvh > 0) 
                                                {
                                                    ?>
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">De</th>
                                                            <th scope="col">Para</th>
                                                            <th scope="col">Justificativa</th>
                                                            <th scope="col">Por</th>
                                                            <th scope="col">Em</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?
                                                        foreach($ListarHistoricoModal as $historicoModal) 
                                                        {
                                                            ?>
                                                            <tr>
                                                                <td><?=$historicoModal['valor_old'] ?></td>
                                                                <td><?=$historicoModal['valor'] ?></td>
                                                                <td>
                                                                    <?echo $historicoModal['justificativa'];?>
                                                                </td>
                                                                <td><?=$historicoModal['nomecurto'] ?></td>
                                                                <td><?=dmahms($historicoModal['criadoem']) ?></td>
                                                            </tr>
                                                            <?
                                                        }
                                                        ?>
                                                    </tbody>
                                                <?
                                                }
                                                ?>
                                            </table>
                                        </div>
                                    <?
                                    } 
                                    ?>
                                </div>
                                </td>
                                <?
                                }
                                reset($lMes);
                                ?>                        
                    </tr>
                    <tr>
                        <td style="width: 7%;">
                            <label class="alert-warning"><b><?=$exercicio['exercicio']?></b></label>
                        </td>
                        <td style="width: 7%;"><b>Planejamento</b></td>
                        <? 
                            
                            foreach ($lMes as $mes) 
                            {
                                $vari++;
                            ?>
                        <td style="width: 7%;" class="nowrap">
                            <input name="_<?=$vari.$idprodserv.$idprodservformula?>_u_planejamentoprodserv_idplanejamentoprodserv" class="hide" type="text" value="<?=$mes['idplanejamentoprodserv']?>"> 
                            <input name="_<?=$vari.$idprodserv.$idprodservformula?>_u_planejamentoprodserv_planejado" <?if(!empty($mes['planejado'])){?> readonly="readonly" style="background-color: #ece5e5 !important;"<?}?> class="size6" type="text" value="<?=$mes['planejado']?>"> 

                        </td>
                        <?
                            }
                            reset($lMes);
                        ?>
                        </tr>
                                            
                        <tr>
                            <td style="width: 7%;" class="nowrap"></td>
                            <td style="width: 7%;" class="nowrap"><b>Adicional %</b></td>
                            <?
                            $ad=0;
                            foreach ($lMes as $mes) 
                                {
                                    $ad++;
                                    $vari++;
                            ?>
                            <td style="width: 7%;" class="nowrap">
                                <input name="_<?=$vari.$idprodserv.$idprodservformula?>_u_planejamentoprodserv_idplanejamentoprodserv" class="hide" type="text" value="<?=$mes['idplanejamentoprodserv']?>"> 
                                <?if($ad==1){
                                    $_idplanejamentoprodserv=$mes['idplanejamentoprodserv'];
                                    $_adicional=$mes['adicional'];
                                ?>
                                    <input name="_<?=$vari.$idprodserv.$idprodservformula?>_u_planejamentoprodserv_adicional" <?if(!empty($mes['adicional'])){?> readonly="readonly" style="background-color: #ece5e5 !important;"<?}?> class="size6  adicional<?=$idprodserv.$unidadeEmpresa['idunidade'].$exercicio['exercicio']?>" type="text" value="<?=$mes['adicional']?>" onchange="atualizaad(this,'adicional<?=$idprodserv.$unidadeEmpresa['idunidade'].$exercicio['exercicio']?>')"> 
                                    <?}elseif($ad==2){?>
                                    <div class="col-md-4">
                                    <?if(!empty($_adicional)){?>
                                    <i class="fa fa-pencil pointer" title='Editar Adicional' onclick="alteravalor('adicional','<?=$_adicional?>','modulohistorico',<?=$_idplanejamentoprodserv?>,'Adicional:')"></i>
                                    <?}?>
                                    <input name="_<?=$vari.$idprodserv.$idprodservformula?>_u_planejamentoprodserv_adicional" class="hide  adicional<?=$idprodserv.$unidadeEmpresa['idunidade'].$exercicio['exercicio']?>" type="text" value="<?=$mes['adicional']?>" onchange="atualizaad(this,'adicional<?=$idprodserv.$unidadeEmpresa['idunidade'].$exercicio['exercicio']?>')"> 
                                    </div>
                                    <div class="col-md-6">
                                    <?
                                    $ListarHistoricoModal = PlanejamentoProdServController::buscarHistoricoModuloAlteracao($_idplanejamentoprodserv, 'planejamentoprodserv','adicional');
                                    $qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
                                    if ($qtdvh > 0) 
                                    {
                                        ?>
                                        
                                        <div class="historicoEnvio" idplanejamentoprodserv_adicional="<?=$_idplanejamentoprodserv?>">
                                            <i title="Histórico do Adicional" class="fa fa-info-circle preto pointer hoverazul tip" data-target="webuiPopover0"></i>
                                        </div>
                                        <div class="webui-popover-content">
                                            <br/>
                                            <table class="table table-striped planilha">
                                                <?
                                                if($qtdvh > 0) 
                                                {
                                                    ?>
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">De</th>
                                                            <th scope="col">Para</th>
                                                            <th scope="col">Justificativa</th>
                                                            <th scope="col">Por</th>
                                                            <th scope="col">Em</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?
                                                        foreach($ListarHistoricoModal as $historicoModal) 
                                                        {
                                                            ?>
                                                            <tr>
                                                                <td style="width: 7%;"><?=$historicoModal['valor_old'] ?></td>
                                                                <td style="width: 7%;"><?=$historicoModal['valor'] ?></td>
                                                                <td style="width: 7%;">
                                                                    <?echo $historicoModal['justificativa'];?>
                                                                </td>
                                                                <td style="width: 7%;"><?=$historicoModal['nomecurto'] ?></td>
                                                                <td style="width: 7%;"><?=dmahms($historicoModal['criadoem']) ?></td>
                                                            </tr>
                                                            <?
                                                        }
                                                        ?>
                                                    </tbody>
                                                <?
                                                }
                                                ?>
                                            </table>
                                        </div>
                                    <?
                                    } 
                                    ?>
                                    </div>
                                    <?}elseif($ad == 12){?>
                                        <button class="btn btn-success" idprodserv="<?=$idprodserv?>" idprodservformula="<?=$idprodservformula?>" onclick="salvaBloco(this)">Salvar</button>
                                    <?}else{?>                                    
                                    <input name="_<?=$vari.$idprodserv.$idprodservformula?>_u_planejamentoprodserv_adicional" class="hide  adicional<?=$idprodserv.$unidadeEmpresa['idunidade'].$exercicio['exercicio']?>" type="text" value="<?=$mes['adicional']?>" onchange="atualizaad(this,'adicional<?=$idprodserv.$unidadeEmpresa['idunidade'].$exercicio['exercicio']?>')"> 
                                    <?}?>
                                </td>
                            <?
                                }
                                reset($lMes);
                            ?>

                        

                        </tr>
                    <tr>
                        <td style="width: 7%;"></td>
                        <td style="width: 7%;"></td>
                        <td style="width: 7%;" colspan="11"></td>
                        <td style="width: 7%;" >
                            
                        </td>
                    </tr>
                    </table>
                    <hr>
                    <?
                    }//foreach ($lExercicio as $exercicio)
                ?>
                    <div>
                        <i id="mais_<?=$idprodserv.$idprodservformula.$unidadeEmpresa['idunidade']?>" class="fa fa-plus-circle fa-2x verde btn-lg pointer" onclick="novoPlanejamento(<?=$idprodserv.$idprodservformula.$unidadeEmpresa['idunidade']?>)" title="Novo Planejamento"></i>
                        <select id="select_<?=$idprodserv.$idprodservformula.$unidadeEmpresa['idunidade']?>" class="size7 hide" idprodservformula="<?=$idprodservformula?>" onchange="inserirPlanejamento(this,<?=$idprodserv?>,<?=$unidadeEmpresa['idunidade']?>)">
                        <option value=""> Selecione para adicionar</option>
                            <? fillselect(PlanejamentoProdServController::buscarExercicioPorId($idprodserv,$unidadeEmpresa['idunidade'],$idprodservformula)); ?>
                        </select>
                    </div>																												
                    </div>
                </div>
            </div>
            <script>
                $(".historicoEnvio").webuiPopover({
                    trigger: "click",
                    placement: "right",
                    width: 500,
                    delay: {
                        show: 300,
                        hide: 0
                    }
                });
            </script>
            <?
        }?>
    </div>
    <?
}

?>