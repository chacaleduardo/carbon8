<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    include_once("../inc/php/cbpost.php");
}

// CONTROLLERS
require_once(__DIR__."/controllers/etlconf_controller.php");
require_once(__DIR__."/controllers/_mtotabcol_controller.php");

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetros chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "etlconf";
$pagvalcampos = array(
	"idetlconf" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from etlconf where idetlconf = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

/* *********************************** Tabelas dos databases configurados ****************************** */
$tabelasDoBancoCarbonELaudo = EtlConfController::buscarTabelasDoBancoCarbonELaudo();

if($_acao == 'u')
{
    $etlConf = EtlConfController::buscarPorChavePrimaria($_1_u_etlconf_idetlconf);
    $tabECol = null;

    if($etlConf['tabela'])
    {
        $tabECol = _MtoTabColController::buscarPorTabECol($etlConf['tabela'], 'alteradoem');
    }
    
    $checked='';
    $vchecked='Y';

    if($_1_u_etlconf_repetereg=='Y'){
        $checked='checked';
        $vchecked='N';					
    }
}
?>

<link rel="stylesheet" href="<?= "/form/css/etlconf_css.css?_".date('dmYhms') ?>" />

<div class="row w-full">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row w-full d-flex flex-wrap">
                <!-- Titulo -->
                <div class="col-xs-12 col-md-4 form-group">
                    <label for="" class="text-white">
                        Título:
                    </label>
                    <input id="idetlconf" name="_1_<?=$_acao?>_etlconf_idetlconf" type="hidden" value="<?=$_1_u_etlconf_idetlconf?>" readonly='readonly' />
                    <input name="_1_<?=$_acao?>_etlconf_titulo" type="text" class="form-control" value="<?=$_1_u_etlconf_titulo?>" />
                </div>
                <!-- Reenviar a cada: -->
                <div class="col-xs-6 col-md-2 form-group">
                    <label for="" class="text-white">
                        Reenviar a cada:
                    </label>
                    <select name="_1_<?=$_acao?>_etlconf_multiplo" vnulo class="form-control">
                        <?fillselect(EtlConfController::$horaDiaSemanaMesAno, $_1_u_etlconf_multiplo);?>
                    </select>                             
                </div>
                <!-- A partir De -->
                <div class="col-xs-6 col-md-2 form-group">
                    <label for="" class="text-white">
                        A partir De:
                    </label>
                    <input name="_1_<?=$_acao?>_etlconf_apartirde" class="form-control calendario" type="text"  value="<?=$_1_u_etlconf_apartirde?>" vnulo>
                </div>
                <!-- Status: -->
                <div class="col-xs-6 col-md-2 form-group">
                    <label for="" class="text-white">
                        Status
                    </label>
                    <select name="_1_<?=$_acao?>_etlconf_status" class="form-control">
                        <?fillselect(EtlConfController::$status, $_1_u_etlconf_status);?>
                    </select>
                </div>
                <!-- Executado em -->
                <div class="col-xs-6 col-md-2 form-group" disabled>
                    <label for="" class="text-white">
                        Executado em
                    </label>
                    <div class="form-control d-flex align-items-center">
                        <?= $_1_u_etlconf_executadoem ?>
                    </div>
                </div>
            </div>        
        </div>
        <div class="panel-body">
            <? if(!empty($_1_u_etlconf_idetlconf))
            {?>
                <div class="row w-full">
                    <!-- Computar o mesmo registro -->
                    <div class="col-xs-12 form-group d-flex align-items-center justify-content-end">
                        <label for="computa-mesmo" class="mr-2">
                            Computar o mesmo registro mais de uma vez
                        </label>
                        <input id="computa-mesmo" title="Computar o mesmo registro mais de uma vez" type="checkbox" <?=$checked?> name="namerepete" onclick="repetereg('etlconf','repetereg',<?=$_1_u_etlconf_idetlconf?>,'<?=$vchecked?>')">
                    </div>
                </div>
                <div class="d-flex flex-wrap col-xs-12 col-md-6 px-0">
                    <!-- Tabela -->
                    <div class="col-xs-12 form-group">
                        <label for="">
                            Tabela
                        </label>
                        <div class="w-100 d-flex align-items-center">
                            <input <?=$readonly?> type="text" name="_1_<?=$_acao?>_etlconf_tabela" cbvalue="<?=$_1_u_etlconf_tabela?>" value="<?=$_1_u_etlconf_tabela?>" class="form-control mr-3">
                            <?if($_1_u_etlconf_tabela){?>
                                <a class="fa fa-bars pointer hoverazul" onclick="janelamodal('?_modulo=_mtotabcol&_acao=u&PK=<?=_DBAPP.'.'.$_1_u_etlconf_tabela?>')" ></a>
                            <?}?>
                        </div>
                    </div>
                    <div class="col-xs-12 form-group">
                        <label for="">Descrição</label>
                        <textarea name="_1_<?=$_acao?>_etlconf_descr" class="form-control" rows="20"><?=$_1_u_etlconf_descr?></textarea>
                    </div>
                </div>
            <?}?>
        </div>
    </div>
</div>
<?if($_1_u_etlconf_tabela)
{ ?>
    <?if(!$tabECol)
    { ?>
        <div class="col-md-7">
            <div class="panel panel-default">
                <div class="panel-heading"> <span class="alert-error">ETL não possui tabela vinculada.</span></div>
            </div>
        </div>
    <?} else 
    {
        if(!count($tabECol))
        { ?>
            <div class="col-md-7">
                <div class="panel panel-default">
                    <div class="panel-heading"> <span class="alert-error">Modulo [<a class="pointer" onclick="janelamodal('?_modulo=_mtotabcol&_acao=u&PK=<?=_DBAPP?>.<?=$etlConf['tabela']?>')"><?=$etlConf['tabela']?></a>] não possui campo alteradopor. <br> Favor informar ao setor de TI para criação do mesmo.</span></div>
                </div>
            </div>
        <? } else
        {
            EtlConfController::deletarFiltrosRemovidosDaTabela($etlConf['tabela'], $_1_u_etlconf_idetlconf);
            EtlConfController::inserirFiltrosAdicionadosNaTabela($_1_u_etlconf_idetlconf, $etlConf['tabela'], $_SESSION["SESSAO"]["IDEMPRESA"], $_SESSION["SESSAO"]["USUARIO"]);
            
            $where = "tc.tab='".$_1_u_etlconf_tabela."'";
           
            $filtros = _MtoTabColController::buscarFiltrosPorIdEtlConfEClausula($_1_u_etlconf_idetlconf, $where);
        ?>
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading">Filtros Tabela            
                    <a class="pointer" onclick="janelamodal('?_modulo=_mtotabcol&_acao=u&PK=<?=_DBAPP?>.<?=$etlConf['tabela']?>')"><?=$etlConf['tabela']?></a>
            </div>
                <div class="panel-body">
                    <?if(count($filtros))
                    {?>
                        <table class="planilha grade compacto w-100 overflow-auto">
                            <tr>
                                <th>Rótulo</th>
                                <th>Select</th>
                                <th>Tipo</th>
                                <th>Where</th>
                                <th>Valor</th>
                                <th>Group</th>
                                <th>Sum</th>
                                <th>Separador</th>	
                                <th>Group_Concat</th>
                                <th>Soma Final</th>				        
                            </tr>
                            <?
                            $l=99;
                        
                            foreach($filtros as $row)
                            {
                                $l=$l+1;
                                $sepchecked=($row['separador']=='Y')?"checked='checked'":"";
                            
                                if($row["valor"]!='null' and $row["valor"]!=' ' and $row["valor"]!=''){
                                    $bcolor="#99cc99";
                                }else{
                                    $bcolor="white";  
                                }
                                if($row["somaf"]<> "") {$bcolor = "#fdd236";}
                                if($rf["grp"]=="Y"){$bcolor = "#fdd236";}
                                if(empty($row["idetlconffiltros"])){
                                    $ac ='i';
                                }else{
                                    $ac ='u';
                                }
                            
                                
                            ?>
                            <tr style="background-color:<?=$bcolor?>;">
                            
                                <td><?=$row["rotcurto"]?>
                                <?if(!empty($row["idetlconffiltros"])){?>
                                <input id="idetlconf" name="_<?=$l?>_<?=$ac?>_etlconffiltros_idetlconffiltros" type="hidden" value="<?=$row["idetlconffiltros"]?>">
                                <?}?>
                                <input id="idetlconf" name="_<?=$l?>_<?=$ac?>_etlconffiltros_idetlconf" type="hidden" value="<?=$_1_u_etlconf_idetlconf?>">
                                <input id="col" name="_<?=$l?>_<?=$ac?>_etlconffiltros_col" type="hidden" value="<?=$row["colf"]?>">
                                </td>     
                                <td>
                                <select name="_<?=$l?>_<?=$ac?>_etlconffiltros_addselect">
                                    <?fillselect(EtlConfController::$simNao, $row["addselect"])?>
                                </select>
                                    
                                </td>     
                                <td><?=$row["datatype"]?></td>                
                                                    
                                <?if(!empty($row['dropsql'])){
                                    $empresa = explode("getidempresa('", $row["dropsql"]);
                                    $empresa = explode("').", $empresa[1]);
                                    $empresa = explode("','", $empresa[0]);
                                    $arrvalor= explode(',', $row["valor"]);     
                                    $sqlm = str_replace( "sessao_idempresa"," idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"],$row['dropsql']);   							
                                    $sqlm = str_replace( '".getidempresa(\''.$empresa[0].'\',\''.$empresa[1].'\')."',getidempresa($empresa[0], $empresa[1]),$row['dropsql']);   
                            ?>
                                <td align="center"> 
                                    <input name="_<?=$l?>_<?=$ac?>_etlconffiltros_sinal" type="hidden" value="in"> in
                                </td>
                                <td>
                                    <select class="selectpicker valoresselect" multiple="multiple" data-live-search="true" onchange="atualizavalor(this,<?=$row['idetlconffiltros']?>);">
                                    <?
                                    $resm =  SQL::ini($sqlm)::exec()->data;
                                    foreach($resm as $item)
                                    {
                                        $selected= '';
                                        if (in_array($item['id'],$arrvalor)){
                                            $selected= 'selected';
                                        }

                                        echo '<option data-tokens="'.retira_acentos($item['valor']).'" value="'.$item['id'].'" '.$selected.' >'.$item['valor'].'</option>'; 
                                    }		
                                    ?>
                                </select> 
                                </td> 
                                <?      
                                }elseif($row["datatype"]=='date' or $row["datatype"]=='datetime'){?> 
                                <td>                    
                                    <select name="_<?=$l?>_<?=$ac?>_etlconffiltros_sinal" vnulo>
                                    <option value=""></option>
                                        <?fillselect(EtlConfController::$sinais, $row["sinal"]);?>		
                                    </select>
                                </td>
                                <td class="nowrap"> 
                                    <select class="size6"  name="_<?=$l?>_<?=$ac?>_etlconffiltros_valor">
                                        <option value=""></option>
                                            <?fillselect(EtlConfController::$valores, $row["valor"]);?>		
                                        </select>
                                    <input class="size5" placeholder="Dia" name="_<?=$l?>_<?=$ac?>_etlconffiltros_nowdias" type="text" value="<?=$row["nowdias"]?>"> Dias
                                </td>
                                <?                             
                                }else
                                {?> 
                                    <td>                    
                                        <select name="_<?=$l?>_<?=$ac?>_etlconffiltros_sinal" vnulo>
                                            <?fillselect(EtlConfController::$sinais2, $row["sinal"]);?>		
                                        </select>
                                    </td>
                                    <td>
                                        <input name="_<?=$l?>_<?=$ac?>_etlconffiltros_valor" type="text" value="<?=$row["valor"]?>">  
                                    </td>
                                <?}?>
                                <td>
                                    <select name="_<?=$l?>_<?=$ac?>_etlconffiltros_grp" style="width:58px;">
                                        <?fillselect(EtlConfController::$simNao, $row["grp"]);?>
                                    </select>
                                </td>                    
                                <td>
                                    <select name="_<?=$l?>_<?=$ac?>_etlconffiltros_acsum" style="width:58px;">
                                        <?fillselect(EtlConfController::$simNao, $row["acsum"]);?>
                                    </select>
                                </td>
                                <td align="center">
                                    <input type="radio" name="separador" <?=$sepchecked?> onclick="toggle(<?=$row['idetlconffiltros']?>,'separador',this)" >
                                </td>
                                <td align="center">
                                    <select name="_<?=$l?>_<?=$ac?>_etlconffiltros_grpconcat" style="width:58px;">
                                        <option></option>
                                        <?fillselect(EtlConfController::$grupoConcat, $row["grpconcat"]);?>
                                    </select>
                                </td>
                                <td align="center">
                                    <select name="_<?=$l?>_<?=$ac?>_etlconffiltros_somaf" style="width:58px;">
                                        <option></option>
                                        <?fillselect(EtlConfController::$somaF, $row["somaf"]);?>
                                    </select>
                                </td>
                            </tr>
                            <?}?>
                        </table>
                    <?}?>
                </div>
            </div>
        </div>
            <?}
        }
    }

$and = '';
if(!empty($_1_u_etlconf_idetlconf)) // trocar p/ cada tela a tabela e o id da tabela
{
	$_idModuloParaAssinatura = $_1_u_etlconf_idetlconf; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "etlconf"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
require_once(__DIR__."/js/etlconf_js.php");
?>
