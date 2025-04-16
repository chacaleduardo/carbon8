<?
require_once("../inc/php/validaacesso.php");
if($_POST){
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "devicesensor";
$pagvalcampos = array(
	"iddevicesensor" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from devicesensor where iddevicesensor = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

// CONTROLLERS
include_once(__DIR__."/controllers/devicesensor_controller.php");

$deviceSensoresBloco = DeviceSensorController::buscarDeviceSensoresBlocoPorIdDeviceSensor($_1_u_devicesensor_iddevicesensor);
$cont = 100;
$contp = 1000;
$blocoanterior="";

?>
<style>
.panel-body{
    padding-top: 5px !important;
}
.fa-2x {
    font-size: 1.5em;
}
.fa-2x.addCampo {
    font-size: 2em;
}
</style>
<div class="col-md-12">
    <div class="panel panel-default" >
        <div class="panel-heading">
            <table>
                <tr> 		    
                    <td>
                        <input 
                            name="_1_<?=$_acao?>_devicesensor_iddevicesensor" 
                            type="hidden" 			   
                            value="<?=$_1_u_devicesensor_iddevicesensor?>" 
                            readonly='readonly'					>
                    </td> 
                    <td>Sensor</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_devicesensor_nomesensor">
                        <?fillselect(DeviceSensorController::$nomeSensores, $_1_u_devicesensor_nomesensor);?>	 	
                        </select> 
                    </td> 
                    <td>Localização</td> 
                    <td>
                        <input 
                            name="_1_<?=$_acao?>_devicesensor_localizacao" 
                            type="text" 
                            value="<?=$_1_u_devicesensor_localizacao?>" 
                                                >
                    </td>
                    <td>Pino</td> 
                    <td>
                        <input 
                            name="_1_<?=$_acao?>_devicesensor_pino" 
                            type="text" 
                            value="<?=$_1_u_devicesensor_pino?>" 
                                                >
                    </td>
                    <td>M5</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_devicesensor_iddevice" class="select-picker" data-live-search="true">
                            <?fillselect(DeviceSensorController::buscarTags(), $_1_u_devicesensor_iddevice);?>		
                        </select>
                    </td> 
                    <td>Status</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_devicesensor_status">
                        <option value=""></option>
                        <?fillselect(DeviceSensorController::$status, $_1_u_devicesensor_status);?>		
                        </select>
                    </td> 
                </tr>	    
            </table>
	    </div>
        <div class="panel-body"> 
            <?if(!empty($_1_u_devicesensor_iddevicesensor))
            {
                foreach($deviceSensoresBloco as $sensorBloco) {?>
                    <div class="panel panel-default" statusbloco="<?=$sensorBloco['status']?>">
                        <div class="panel-heading">
                            <table>
                                <tr>
                                    <td class="idbox"><?=$sensorBloco["iddevicesensorbloco"]?></td>
                                    <td>Bloco</td>
                                    <td><input value="<?=$sensorBloco['rotulo']?>" readonly='readonly'></td> 
                                    <td>Unidade</td> 
                                    <td>
                                        <input type="hidden" name="_<?=$cont?>_<?=$_acao?>_devicesensorbloco_iddevicesensorbloco" value="<?=$sensorBloco["iddevicesensorbloco"]?>">
                                        <select name="_<?=$cont?>_<?=$_acao?>_devicesensorbloco_unidade">
                                            <option value=""></option>
                                            <?fillselect(DeviceSensorController::$unidades,$sensorBloco["unidade"]);?>		
                                        </select>
                                    </td>
                                    <td>Offset</td> 
                                    <td style="width:60px">
                                        <input 
                                            name="_<?=$cont?>_<?=$_acao?>_devicesensorbloco_offset" 
                                            type="text" 
                                            value="<?=$sensorBloco["offset"]?>" 
                                                                >
                                    </td>
                                    <td>Tipo Calibração</td> 
                                    <td>
                                        <input type="hidden" name="_<?=$cont?>_<?=$_acao?>_devicesensorbloco_iddevicesensorbloco" value="<?=$sensorBloco["iddevicesensorbloco"]?>">
                                        <select name="_<?=$cont?>_<?=$_acao?>_devicesensorbloco_tipocalibracao">
                                            <option value=""></option>
                                            <?fillselect(DeviceSensorController::$tiposCalibracao, $sensorBloco["tipocalibracao"]);?>		
                                        </select>
                                    </td>
                                    <td>Prioridade</td> 
                                    <td>
                                        <input type="hidden" name="_<?=$cont?>_<?=$_acao?>_devicesensorbloco_iddevicesensorbloco" value="<?=$sensorBloco["iddevicesensorbloco"]?>">
                                        <select name="_<?=$cont?>_<?=$_acao?>_devicesensorbloco_prioridade">
                                        <?fillselect(DeviceSensorController::$prioridades, $sensorBloco["prioridade"]);?>		
                                        </select>
                                    </td>
                                    <td>Status</td> 
                                    <td>
                                        <input type="hidden" name="_<?=$cont?>_<?=$_acao?>_devicesensorbloco_iddevicesensorbloco" value="<?=$sensorBloco["iddevicesensorbloco"]?>">
                                        <select name="_<?=$cont?>_<?=$_acao?>_devicesensorbloco_status">
                                        <?fillselect(DeviceSensorController::$status, $sensorBloco["status"]);?>		
                                        </select>
                                    </td>
                                    <td>
                                        <i class="fa fa-trash pointer hoververmelho cinzaclaro" onclick="delBloco(<?=$sensorBloco['iddevicesensorbloco']?>)"></i>
                                    </td> 
                                </tr>	    
                            </table>
                        </div>
                        <div class="panel-body">
                            <?
                            $linhas = 0;

                            $deviceSensoresCalib = DeviceSensorController::buscarDeviceSensoresCalibPorIdDeviceSensorBloco($sensorBloco["iddevicesensorbloco"]);

                            foreach($deviceSensoresCalib as $sensorCalib) {?>
                                    <table style="width:100%;">
                                    <tr>
                                        <td style="display: flex;justify-content: flex-end;padding-top: 9px;margin-left: -20px; "> Ref. Subida </td> 
                                        <td>
                                            <input type="hidden" name="_<?=$contp?>_<?=$_acao?>_devicesensorcalib_iddevicesensorcalib" value="<?=$sensorCalib["iddevicesensorcalib"]?>">
                                            <input 
                                                name="_<?=$contp?>_<?=$_acao?>_devicesensorcalib_refsubida" 
                                                value="<?=$sensorCalib["refsubida"]?>" 
                                                vnulo
                                            >
                                        </td>
                                        <td style="display: flex;justify-content: flex-end;padding-top: 9px;margin-left: -20px;"> Sensor Subida </td> 
                                        <td>
                                            <input 
                                                name="_<?=$contp?>_<?=$_acao?>_devicesensorcalib_sensorsubida" 
                                                value="<?=$sensorCalib["sensorsubida"]?>" 
                                                vnulo
                                            >
                                        </td>
                                        <td style="display: flex;justify-content: flex-end;padding-top: 9px;margin-left: -20px;"> Ref. Descida </td> 
                                        <td>
                                            <input 
                                                name="_<?=$contp?>_<?=$_acao?>_devicesensorcalib_refdescida" 
                                                value="<?=$sensorCalib["refdescida"]?>" 
                                                vnulo
                                            >
                                        </td>
                                        <td style="display: flex;justify-content: flex-end;padding-top: 9px;margin-left: -20px;"> Sensor Descida </td> 
                                        <td>
                                            <input 
                                                name="_<?=$contp?>_<?=$_acao?>_devicesensorcalib_sensordescida" 
                                                value="<?=$sensorCalib["sensordescida"]?>" 
                                                vnulo
                                            >
                                        </td>
                                        <td>
                                            <i class="fa fa-trash pointer hoververmelho cinzaclaro" onclick="delPonto(<?=$sensorCalib['iddevicesensorcalib']?>)"></i>
                                        </td>
                                    </tr>
                                </table>
                                
                            <?
                            $contp++;
                            $linhas++;
                            }?>
                            <?if($linhas<10){?>
                                <i contador="<?=$contp?>" class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer addPonto " onclick="addPonto(<?=$sensorBloco['iddevicesensorbloco']?>)"></i>
                            <?}?>
                        </div>
                    </div>
                    <?
                    $cont++;    
                } ?>
                <div id="varfisica" class="panel-body" style="float:left;">
                    <table style="width:100%;">
                        <tr>
                            <td>
                                <i class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer addCampo" onclick="addCampo();"></i>
                            </td>
                            <td>
                                <select id="tipounidademed" style="display: none;" onchange="addBloco(this)">
                                    <option value=""></option>
                                    <?fillselect(DeviceSensorController::buscarTodosDeviceSensorTipos(), $_1_u_devicesensortipo_rotulo);?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            <?}?>
        </div>
    </div>
</div>
<?if(!empty($_1_u_devicesensor_iddevicesensor))
{ ?>
    <div class="col-md-12">
        <?$tabaud = "devicesensor";?>
        <div class="panel panel-default">		
            <div class="panel-body">
                <div class="row col-md-12">		
                    <div class="col-md-1 nowrap">Criado Por:</div>     
                    <div class="col-md-5"><?=${"_1_u_".$tabaud."_criadopor"}?></div>
                    <div class="col-md-1 nowrap">Criado Em:</div>     
                    <div class="col-md-5"><?=${"_1_u_".$tabaud."_criadoem"}?></div>   
                </div>
                <div class="row col-md-12">            
                    <div class="col-md-1 nowrap">Alterado Por:</div>     
                    <div class="col-md-5"><?=${"_1_u_".$tabaud."_alteradopor"}?></div>
                    <div class="col-md-1 nowrap">Alterado Em:</div>     
                    <div class="col-md-5"><?=${"_1_u_".$tabaud."_alteradoem"}?></div>       
                </div>
            </div>
        </div>
    </div>
<?}?>

<? require_once(__DIR__."/js/devicesensor_js.php"); ?>