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
$pagvaltabela = "deviceciclo";
$pagvalcampos = array(
	"iddeviceciclo" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from deviceciclo where iddeviceciclo = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
 
// CONTROLLERS
require_once(__DIR__."/../form/controllers/deviceciclo_controller.php");

if($_1_u_deviceciclo_iddeviceciclo)
{
    $deviceCicloAtiv = DeviceCicloController::buscarDeviceCicloAtivPorIdDeviceCiclo($_1_u_deviceciclo_iddeviceciclo);
    $devicesVinculadoAoCiclo = DeviceCicloController::buscarDevicesPorIdDeviceCiclo($_1_u_deviceciclo_iddeviceciclo);
}

?>
<style>
    .select-picker .dropdown-menu
	{
		max-width: 150%;
	}

    .panel-body{
        padding-top: 5px !important;
    }
    .config-status
    {
        display: flex;
    }

    .config-status > div
    {
        width: auto;
        display: flex;
        align-items: center;
    }

    .devicevinculo thead{
        font-weight: bold;
    }
</style>

<div class="col-md-9">
    <div class="panel panel-default" >
        <div class="panel-heading config-status">
            <table id="cicloatividades">
                <tr> 		    
                    <td>
                        <input 
                            name="_1_<?=$_acao?>_deviceciclo_iddeviceciclo" 
                            type="hidden" 			   
                            value="<?=$_1_u_deviceciclo_iddeviceciclo?>" 
                            readonly='readonly'					
						>
                    </td> 
                    <td>Nome Ciclo</td> 
                    <td>
                        <input 
                            name="_1_<?=$_acao?>_deviceciclo_nomeciclo" 
                            type="text" 
                            value="<?=$_1_u_deviceciclo_nomeciclo?>" 
                        >
                    </td>
                    <td>Modo de Execução</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_deviceciclo_modelo">
                        <?fillselect("select '1','Contínuo' union select '2','Sequencial'",$_1_u_deviceciclo_modelo);?>		
                        </select>
                    </td>
                    <td>Status</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_deviceciclo_status">
                        <?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_deviceciclo_status);?>		
                        </select>
                    </td> 
                </tr>	    
            </table>
	    </div>
		<div class="panel-body divbody"> 
			<?if(!empty($_1_u_deviceciclo_iddeviceciclo)){
                $ifi=-1;
                $cont = 100;
                foreach($deviceCicloAtiv as $cicloAtiv) {
                    $ifi = $cicloAtiv["ordem"];?>
                    <div class = "panel panel-default divbodyitem">
                            <div class="panel-heading">
                                <table>
                                    <tr>
                                        <td>
                                            <i class="fa fa-arrows cinzaclaro hover move" title="Ordenar Atividade>"></i>
                                            <input type="hidden" name="_<?=$cont?>_<?=$_acao?>_devicecicloativ_iddevicecicloativ" value="<?=$cicloAtiv["iddevicecicloativ"]?>">
                                            <input type="hidden" name="_<?=$cont?>_<?=$_acao?>_devicecicloativ_ordem" value="<?=$cicloAtiv["ordem"]?>">
                                        </td>
                                        <td>Atividade</td> 
                                        <td>
                                            <input type="hidden" name="_<?=$cont?>_<?=$_acao?>_devicecicloativ_iddevicecicloativ" value="<?=$cicloAtiv["iddevicecicloativ"]?>">
                                            <input 
                                                name="_<?=$cont?>_<?=$_acao?>_devicecicloativ_nomeativ" 
                                                type="text" 
                                                value="<?=$cicloAtiv["nomeativ"]?>"  
                                            >
                                        </td>
                                        <td>Executar</td> 
                                        <td>
                                        <input 
                                            style="width:60px"
                                            name="_<?=$cont?>_<?=$_acao?>_devicecicloativ_qtd" 
                                            value="<?=$cicloAtiv["qtd"]?>" 
                                        >
                                        </td>
                                        <td>
                                            <select name="_<?=$cont?>_<?=$_acao?>_devicecicloativ_tipo">
                                            <option value=""></option>
                                            <?fillselect(DeviceCicloController::$tipos, $cicloAtiv["tipo"]);?>		
                                            </select>
                                        </td>
                                        <td>Parâmetro</td> 
                                        <td>
                                        <select name="_<?=$cont?>_<?=$_acao?>_devicecicloativ_var">
                                            <option value=""></option>
                                            <?fillselect(DeviceCicloController::$var, $cicloAtiv["var"]);?>		
                                            </select>
                                        </td>
                                        <td>Status</td> 
                                        <td>
                                            <select name="_<?=$cont?>_<?=$_acao?>_devicecicloativ_status">
                                            <option value=""></option>
                                            <?fillselect(DeviceCicloController::$status, $cicloAtiv["status"]);?>		
                                            </select>
                                        </td>
                                        <td>
                                            <i class="fa fa-trash pointer hoververmelho cinzaclaro" onclick="delAtiv(<?=$cicloAtiv['iddevicecicloativ']?>)"></i>
                                        </td> 
                                    </tr>	    
                                </table>
                            </div>
                        <div class="panel-body">
                            <table style="width:100%">
                                <tr>
                                    <td style="width: 20%;">(Início) - Quando Leitura menor ou igual a</td>
                                    <td>
                                        <input 
                                            style="width:70px"
                                            name="_<?=$cont?>_<?=$_acao?>_devicecicloativ_min" 
                                            value="<?=$cicloAtiv["min"]?>" 
                                        >
                                    </td>
                                    <td>Ação</td> 
                                    <td style="width: 10%;">
                                        <?$acao=1;?>
                                        <select onchange="inserirAcao(this,<?=$acao?>,<?=$cont?>);" class="select-picker" data-live-search="true">
                                            <option value=""></option>
                                            <?fillselect(DeviceCicloController::$acoes);?>
                                        </select>
                                        
                                    </td>
                                    <td style="width: 40%;">
                                        <?
                                        $deviceCicloAtivacao = DeviceCicloController::buscarDeviceCicloAtivacaoPorIdDeviceCicloAtivEAcao($cicloAtiv["iddevicecicloativ"]);

                                        if(count($deviceCicloAtivacao))
                                        {
                                            foreach($deviceCicloAtivacao as $cicloAtivacao) 
                                            {?>
                                                <div style="float:left; border:1px solid #ccc; padding:2px 4px; margin:2px; text-transform:uppercase; font-size:10px;">
                                                    <?=$cicloAtivacao["rotulo"]?>
                                                    <span onclick="deleteAcao(<?=$cicloAtivacao['iddevicecicloativacao']?>);" class="pointer" style="color:red;font-weight: bold;">x</span>
                                                </div>
                                            <?}
                                        }?> 
                                    </td>
                                    <td>Alerta</td>
                                    <td>
                                        <input 
                                            style="width:70px"
                                            name="_<?=$cont?>_<?=$_acao?>_devicecicloativ_alertamin" 
                                            value="<?=$cicloAtiv["alertamin"]?>" 
                                        >
                                    </td>
                                    <td>Pânico</td>
                                    <td>
                                        <input 
                                            style="width:70px"
                                            name="_<?=$cont?>_<?=$_acao?>_devicecicloativ_panicomin" 
                                            value="<?=$cicloAtiv["panicomin"]?>" 
                                        >
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="9"><hr></td>
                                </tr>
                                <tr>
                                    <td style="width: 20%;">(Fim) - Quando Leitura maior ou igual a</td>
                                    <td>
                                        <input 
                                            style="width:70px"
                                            name="_<?=$cont?>_<?=$_acao?>_devicecicloativ_max" 
                                            value="<?=$cicloAtiv["max"]?>" 
                                        >
                                    </td>
                                    <td>Ação</td> 
                                    <td>
                                        <?$acao=2;?>
                                        <select onchange="inserirAcao(this,<?=$acao?>,<?=$cont?>);" class="select-picker" data-live-search="true">
                                            <option value=""></option>
                                            <?fillselect(DeviceCicloController::$acoes);?>		
                                        </select>
                                    </td>
                                    <td style="width: 40%;">
                                        <?
                                        $deviceCicloAtivacao = DeviceCicloController::buscarDeviceCicloAtivacaoPorIdDeviceCicloAtivEAcao($cicloAtiv["iddevicecicloativ"], 'max');

                                        if(count($deviceCicloAtivacao))
                                        {
                                            foreach($deviceCicloAtivacao as $cicloAtivacao) 
                                            {?>
                                                <div style="float:left; border:1px solid #ccc; padding:2px 4px; margin:2px; text-transform:uppercase; font-size:10px;">
                                                    <?=$cicloAtivacao["rotulo"]?>
                                                    <span onclick="deleteAcao(<?=$cicloAtivacao['iddevicecicloativacao']?>);" class="pointer" style="color:red;font-weight: bold;">x</span>
                                                </div>
                                            <?}
                                        }?>
                                    </td>
                                    
                                    <td>Alerta</td>
                                    <td>
                                        <input 
                                            style="width:70px"
                                            name="_<?=$cont?>_<?=$_acao?>_devicecicloativ_alertamax" 
                                            value="<?=$cicloAtiv["alertamax"]?>" 
                                        >
                                    </td>
                                    <td>Pânico</td>
                                    <td>
                                        <input 
                                            style="width:70px"
                                            name="_<?=$cont?>_<?=$_acao?>_devicecicloativ_panicomax" 
                                            value="<?=$cicloAtiv["panicomax"]?>" 
                                        >
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
			<?$cont++; }}?>
            <? if ($_1_u_deviceciclo_iddeviceciclo) { ?>
                <div id="varfisica" class="panel-body" style="float:left;">
                    <table style="width:100%;">
                        <tr>
                            <td>
                                <i class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer" onclick="addAtiv(<?=$ifi+1?>);"></i>
                            </td>
                        </tr>
                    </table>
                </div>
            <? } ?>
		</div>
    </div>
</div>
<? if ($_1_u_deviceciclo_iddeviceciclo) { ?>
    <div class="col-md-3">
        <div class="panel panel-default">
            <div class="panel-heading">Devices Vinculados</div>
            <div class="panel-body">
                <select name="idm5" class="select-picker" onchange="inserirCiclo(this);" id="_m5" data-live-search="true">
                    <option value=""></option>
                    <?fillselect(DeviceCicloController::buscarDevicesDisponiveisParaVinculoPorIdDeviceCiclo($_1_u_deviceciclo_iddeviceciclo));?>			
                </select>
                <table class="table table-striped devicevinculo">
                    <thead>
                        <tr>
                            <td>Device</td>
                            <td>Tag</td>
                            <td>Sala</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?
                        foreach($devicesVinculadoAoCiclo as $device)
                        {?>
                            <tr>
                                <td align="center">
                                    <a href="?_modulo=device&_acao=u&iddevice=<?=$device["iddevice"]?>" target="_blank"><?=$device["iddevice"]?></a>
                                </td>
                                <td>
                                    <a href="?_modulo=tag&_acao=u&idtag=<?=$device["idtagdevice"]?>" target="_blank">TAG - <?=$device["tagdevice"]?></a>
                                </td>
                                <td>
                                    <?if(!empty($device["idtag"])){?>
                                        <a href="?_modulo=tag&_acao=u&idtag=<?=$device["idtag"]?>" target="_blank">TAG - <?=$device["tagsala"]?> - <?=$device["sala"]?></a>
                                    <?}?>
                                </td>
                                <td>
                                    <a href="?_modulo=device&_acao=u&iddevice=<?=$device["iddevice"]?>" target="_blank"><i class="fa fa-bars cinza hoverazul pointer"></i></a>
                                </td>
                                <td>
                                    <i class="fa fa-trash cinza hoververmelho pointer" onclick="removerCiclo(<?=$device['iddeviceobj']?>)"></i>
                                </td>
                            </tr>
                        <?}?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <?$tabaud = "deviceciclo";?>
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
<? } ?>
<? require(__DIR__."/js/deviceciclo_js.php") ?>