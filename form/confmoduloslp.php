<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("controllers/_lp_controller.php");

if ($_POST) {
    require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "_lp";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idlp" => "pk"
);

/*
* $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
*/
$pagsql = "SELECT 
            p.*, e.empresa, e.corsistema,o.idobjeto as idlpgrupo
            FROM
            carbonnovo._lp p
                JOIN
            empresa e ON (e.idempresa = p.idempresa)
                JOIN 
            carbonnovo._lpobjeto o on (o.tipoobjeto = 'lpgrupo' AND o.idlp = p.idlp)
            WHERE
            p.idlp = '#pkid'";

/*
* controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
*/
include_once("../inc/php/controlevariaveisgetpost.php");
$re = _LpController::buscarSiglaECorsistemaDaEmpresa($_1_u__lp_idempresa);

$_sigla = $re['sigla'];
$_corsistema = $re['corsistema'];

function retArrModSelecionados($inIdLp)
{
    global $_sigla;
    global $_1_u__lp_idempresa;
    return _LpController::buscarModulosSelecionados($_1_u__lp_idempresa, $inIdLp, $_sigla);
}

function retArrModFilho($inIdLp, $inmod, $inidempLP)
{

    global $_sigla;

    return _LpController::buscarModulosFilhos($inidempLP, $inIdLp, getidempresa('u.idempresa', 'unidade'), $inmod, $_sigla);
}

function retArrModFilho2($inIdLp, $inmod, $inidempLP)
{
    global $_sigla;

    if ($modulovinc = getModReal($inmod)) {
        $inmod = $modulovinc;
    }

    return _LpController::buscarModulosFilhosDosFilhos($inidempLP, $inIdLp, $inmod, $_sigla);
}


function retArrModRep($inIdLp, $inmod)
{

    return _LpController::buscarRepsDoModulo($inIdLp, $inmod);
}

?>
<style>
    .panel-default>.panel-heading{
        font-weight: normal;
    } 
</style>
<div class="">
    <div class="col-md-12">
        <div class="panel-default">
            <div class="panel-heading hidden" onclick="abreModalMod(<?= $_1_u__lp_idlp ?>,<?= $_1_u__lp_idlpgrupo ?>,<?= $_1_u__lp_idempresa ?>)">Módulos</div>
            <div class="panel-body col-md-12" id="modsel_lp_<?= $_1_u__lp_idlp ?>" style="padding-top: 0px !important;">
                <br>
                <div id="cbpost_mods_<?= $_1_u__lp_idlp ?>" class="col-md-12" style="display: none;position: sticky;right: 80px;z-index: 900;top: 0px;background-color: white;">
                    <div class="col-md-4">Selecione a a permissão</div>
                    <div class="col-md-4"></div>
                    <div class="col-md-4 d-flex justify-content-end">
                        <button style="font-size: 0.875rem;" class="ml-2 btn btn-xs btn-primary" onclick="relacionaLpModuloBT('d',<?= $_1_u__lp_idlp ?>);"><i class="fa fa-trash"></i> Limpar</button>
                        <button style="font-size: 0.875rem;" class="ml-2 btn btn-xs btn-danger" onclick="relacionaLpModuloBT('w',<?= $_1_u__lp_idlp ?>);"><i class="fa fa-pencil"></i> Gravação</button>
                        <button style="font-size: 0.875rem;" class="ml-2 btn btn-xs btn-primary" onclick="relacionaLpModuloBT('r',<?= $_1_u__lp_idlp ?>);"><i class="fa fa-pencil"></i> Leitura</button>
                        <button style="font-size: 0.875rem;" class="ml-2 btn btn-xs btn-primary btn-rel" onclick="relacionaLpRepIdContaItem(<?= $_1_u__lp_idlp ?>);" title="Restringir a Categoria"><i class="fa fa-credit-card-alt"></i> Categoria</button>
                        <button style="font-size: 0.875rem;" class="ml-2 btn btn-xs btn-primary btn-rel" onclick="relacionaLpRepUBT(<?= $_1_u__lp_idlp ?>);" title="Restringir UNIDADE"><i class="fa fa-building"></i> Unidade</button>
                        <button style="font-size: 0.875rem;" class="ml-2 btn btn-xs btn-primary btn-rel" onclick="relacionaLpRepIdPessoa(<?= $_1_u__lp_idlp ?>);"><i class="fa fa-sitemap" title="Restringir a Organograma"></i> Organograma</button>
                    </div>
                </div>
                <div target-idlp='<?= $_1_u__lp_idlp ?>' class="targetSelecionados">
                    <?
                    $j = 0;
                    foreach (retArrModSelecionados($_1_u__lp_idlp) as $mod => $val) {

                        if ($val["status"] == "INATIVO") {
                            $icon = "<i class='fa fa-ban fonte08' style='display: block;' title='Inativo'></i>";
                            $attr = ($j > 0) ? "moduloinativo" : "moduloinicial";
                        } elseif ($val["tipo"] == "BTINV") {
                            $icon = "<i class='fa fa-eye-slash fonte08' style='display: block;' title='Funcionalidade'></i>";
                            $attr = ($j > 0) ? "modulooculto" : "moduloinicial";
                        } elseif ($val["tipo"] == "SNIPPET") {
                            $icon = "<i class='fa fa-eye-slash fonte08' style='display: block;' title='Snippet'></i>";
                            $attr = ($j > 0) ? "modulosnippet" : "moduloinicial";
                        } else {
                            $icon = "";
                            $attr = ($j > 0) ? "modulomenu" : "moduloinicial";
                        }

                        $arrmodfilho = retArrModFilho($_1_u__lp_idlp, $mod, $_1_u__lp_idempresa);
                        if (empty($tipomod)) {
                            $tipomod = $val['divisao'];
                            echo '<div class="panel panel-default" style="margin-top: 0px !important">';
                            echo '<div class="panel-heading col-md-12 d-flex justify-content-center align-items-center"  style="background-color: #6b6b6b;color: white;">
                                <div class="col-md-1"></div>
                                <div class="col-md-7" style="font-size: 1.5rem;">Snippets</div>
                                <div class="col-md-3 text-right" style="font-size: 1.25rem">
                                    <button class="btn btn-xs btn-default" onclick="collapseAll(this,\'in\')">
                                        <div class="d-flex justify-content-center align-items-center">
                                            Expandir todos &nbsp;
                                            <i class="fa fa-chevron-down" ></i>
                                        </div>
                                    </button>
                                </div>
                                <div class="col-md-1" style="writing-mode: vertical-rl;inline-size: min-content;">
                                    <i class="fa fa-2x fa-arrows-v" href="#' . $_1_u__lp_idlp . $mod . '_collapse_modtipo_' . $val['tipo'] . '" data-toggle="collapse"></i>
                                </div>
                            </div>';
                            echo '<div class="panel-body" id="' . $_1_u__lp_idlp . $mod . '_collapse_modtipo_' . $val['tipo'] . '" style="background: #ddd;">';
                            // echo '<table class="table table-striped planilha" style="width:100%;padding: 0px 5px !important">';
                        }
                        if ($val['divisao'] != $tipomod and !empty($tipomod)) {
                            // echo '</table>';
                            echo '</div>';
                            echo '</div>';
                            echo '<div class="panel panel-default" style="margin-top: 0px !important">';
                            echo '<div class="panel-heading col-md-12 d-flex justify-content-center align-items-center"  style="background-color: #6b6b6b;color: white;">
                                <div class="col-md-1"></div>
                                <div class="col-md-7" style="font-size: 1.5rem;">Módulos</div>
                                <div class="col-md-3 text-right" style="font-size: 1.25rem" >
                                    <button class="btn btn-xs btn-default" onclick="collapseAll(this,\'in\')">
                                        <div class="d-flex justify-content-center align-items-center">
                                            Expandir todos &nbsp;
                                            <i class="fa fa-chevron-down" ></i>
                                        </div>
                                    </button>
                                </div>
                                <div class="col-md-1" style="writing-mode: vertical-rl;inline-size: min-content;">
                                    <i class="fa fa-2x fa-arrows-v" href="#' . $_1_u__lp_idlp . $mod . '_collapse_modtipo_' . $val['tipo'] . '" data-toggle="collapse"></i>
                                </div>
                            </div>';
                            echo '<div class="panel-body" id="' . $_1_u__lp_idlp . $mod . '_collapse_modtipo_' . $val['tipo'] . '" style="background: #ddd;">';
                            // echo '<table class="table table-striped planilha" style="width:100%;padding: 0px 5px !important">';
                        }
                    ?>

                        <div class="visivel panel-default col-md-12" cbmodulo="<?= $mod ?>" cbidlpmodulo="<?= $val["idlpmodulo"] ?>" modulopai modulotipo="<?= $attr ?>" style="">
                            <div style="white-space:normal;text-divansform:uppercase;" class="marginzero panel-default " title="<?= $mod ?>">
                                <div style="border-left:8px solid <?= $val['corPerm'] ?>;background-color: #aaa;" class="panel-heading preto col-md-12">
                                    <div class="col-md-1">
                                        <input type="checkbox" modulopai="Y" modulo="<?=$mod?>" permissao="<?= $val["permissao"] ?>" idlpmodulo='<?= $val["idlpmodulo"] ?>' onchange="checkfilhos(this,'modulofilho');" title="<?= $val["rotPermissao"] ?>,<?= $_1_u__lp_idlp ?>">
                                    </div>
                                    <div class="col-md-9" style="font-size: 1.1rem;text-transform:uppercase;margin-top:3px;font-weight:bold;">
                                        <span><?= $val["rotulomenu"] ?> </span>
                                            <i class="fa fa-navicon azul pointer" onclick="janelamodal('?_modulo=_modulo&_acao=u&idmodulo=<?= $mod ?>')"></i>
                                        <? //$icon 
                                        ?>
                                    </div>
                                    <div class="col-md-1">
                                        <button permissao="<?= $val["permissao"] ?>" modulo='<?= $mod; ?>' idlpmodulo='<?= $val["idlpmodulo"] ?>' class="btn btn-xs <?= $val['classeBt'] ?>" onclick="perrmissaoModuloPai('<?=$val['permissao'] ?>',<?= $_1_u__lp_idlp ?>,this)">
                                            <i class="<?= $val["icoPermissao"] ?> branco pointer pull-left " title="<?= $val["rotPermissao"] ?>,<?= $_1_u__lp_idlp ?>"></i>
                                        </button>
                                    </div>
                                    <div class="col-md-1" style="writing-mode: vertical-rl;inline-size: min-content;">
                                        <? if (count($arrmodfilho) > 0) { ?>
                                            <i class="fa fa-2x fa-arrows-v pointer" href="#<?= $_1_u__lp_idlp . $mod ?>_collapse" data-toggle="collapse"></i>
                                        <? } ?>
                                    </div>
                                </div>
                                <? if (count($arrmodfilho) > 0) { ?>
                                    <div id="<?= $_1_u__lp_idlp . $mod ?>_collapse" class="filhocollapse panel-body" style="padding: 0px 0px !important;background-color: #eee;" mod="<?= $mod ?>">
                                        <? foreach ($arrmodfilho as $modf => $valf) { ?>
                                            <?
                                            $arrmodrep = retArrModRep($_1_u__lp_idlp, $modf, $_1_u__lp_idempresa);
                                            $arrmodfilho2 = retArrModFilho2($_1_u__lp_idlp, $modf, $_1_u__lp_idempresa);
                                            ?>
                                            <div class="<?= $modf; ?> panel-default visivel col-md-12" cbmodulo="<?= $modf ?>" cbidlpmodulo="<?= $valf["idlpmodulo"] ?>" modulofilho style="background-color: #eee;">
                                                <div style="height:30px; white-space:normal;text-align:left;border-left:8px solid <?= $valf['corPerm'] ?>;background-color: #ccc;" class="panel-heading col-md-12 marginzero" title="<?= $modf ?>">
                                                    <div class="col-md-1">
                                                        <input type="checkbox" onclick="checkfilhos(this,'modulofilho')" modulofilho modulo='<?= $modf; ?>' permissao="<?= $valf["permissao"] ?>" idlpmodulo='<?= $valf["idlpmodulo"] ?>' title="<?= $valf["rotPermissao"] ?>,<?= $_1_u__lp_idlp ?>">
                                                    </div>
                                                    <div class="col-md-7" style="font-size: 1.25rem;" >
                                                        <i class="<?= $valf["cssicone"] ?>"></i>
                                                        <span><?= $valf["rotulomenu"] ?> </span>
                                                        <i class="fa fa-navicon azul pointer" onclick="janelamodal('?_modulo=_modulo&_acao=u&idmodulo=<?= $modf ?>')"></i>
                                                        <? if ($valf["status"] == "INATIVO") {
                                                            echo "<i class='fa fa-ban fonte08' style='display: block;' title='Inativo'></i>";
                                                        } ?>
                                                    </div>
                                                    <div class="col-md-4" style="writing-mode: vertical-rl;inline-size: min-content;">
                                                        <? if (count($arrmodrep) > 0 || count($arrmodfilho2) > 0) { ?>
                                                            <i class="fa fa-2x fa-arrows-v pointer" href="#<?= $_1_u__lp_idlp . $modf ?>_collapse" data-toggle="collapse"></i>
                                                        <? } ?>
                                                    </div>
                                                </div>
                                                <? if (count($arrmodrep) > 0 || count($arrmodfilho2) > 0) { ?>
                                                    <div class="panel-body" id="<?= $_1_u__lp_idlp . $modf ?>_collapse" style="margin-top: 3px;background-color: #fff;">
                                                        <?

                                                        if (count($arrmodfilho2) > 0) { ?>
                                                            <div class="panel-default">
                                                                <div class="panel-heading col-md-12" style="background-color: #ddd;">
                                                                    <div class="col-md-1">
                                                                        <input type="checkbox" modulofuncpai onclick="checkfilhos(this,'modulofilho')">
                                                                    </div>
                                                                    <div class="col-md-8" style="font-size: 1.17rem;">
                                                                        <i class="fa fa-gears "></i>&nbsp;&nbsp;
                                                                        Funcionalidades
                                                                    </div>
                                                                    <div class="col-md-3" style="writing-mode: vertical-rl;inline-size: min-content;">
                                                                        <i class="fa fa-2x fa-arrows-v pointer" href="#<?= $_1_u__lp_idlp . $modf ?>_collapse_func" data-toggle="collapse"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="panel-body" id="<?= $_1_u__lp_idlp . $modf ?>_collapse_func" style="padding: 0px 0px !important;">
                                                                    <table class="panel-body table-striped" style="padding: 0px 0px !important;width: 100%;">
                                                                        <? foreach ($arrmodfilho2 as $modf2 => $valf2) { ?>
                                                                            <tr class="" cbmodulo="<?= $modf2 ?>" cbidlpmodulo="<?= $valf2["idlpmodulo"] ?>" modulofilho style="padding: 3px 10px !important; margin-top: 0px !important;border-left:8px solid <?= $valf2['corPerm'] ?>;">
                                                                                <td class="col-md-12">
                                                                                    <div class="col-md-1">
                                                                                        <input type="checkbox" modulofunc modulo='<?= $modf2 ?>' permissao="<?= $valf2["permissao"] ?>" idlpmodulo='<?= $valf2["idlpmodulo"] ?>' title="<?= $valf2["rotPermissao"] ?>,<?= $_1_u__lp_idlp ?>">
                                                                                    </div>
                                                                                    <div class="col-md-7">
                                                                                        <span><?= $valf2["rotulomenu"] ?></span>
                                                                                        <i class="fa fa-navicon azul pointer" onclick="janelamodal('?_modulo=_modulo&_acao=u&idmodulo=<?= $modf2 ?>')"></i>
                                                                                    </div>
                                                                                    <div class="col-md-4" style="writing-mode: vertical-rl;inline-size: min-content;">
                                                                                        <!-- <button class="btn btn-xs <?= $valf2['classeBt'] ?>">
                                                                                            <i class="<?= $valf2["icoPermissao"] ?> branco pointer pull-left " permissao="<?= $valf2["permissao"] ?>" idlpmodulo='<?= $valf2["idlpmodulo"] ?>' title="<?= $valf2["rotPermissao"] ?>,<?= $_1_u__lp_idlp ?>"></i>
                                                                                        </button> -->
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        <? } ?>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        <? }
                                                        if (count($arrmodrep)) { ?>
                                                            <div class="panel-default">
                                                                <div class="panel-heading col-md-12" style="background-color: #ddd;">
                                                                    <div class="col-md-1">
                                                                        <input type="checkbox" moduloreppai onclick="checkfilhos(this,'modulofilho')">
                                                                    </div>
                                                                    <div class="col-md-8" style="font-size: 1.17rem;">
                                                                        <i class="fa fa-bar-chart-o"></i>
                                                                        Relatórios
                                                                    </div>
                                                                    <div class="col-md-3" style="writing-mode: vertical-rl;inline-size: min-content;">
                                                                        <i class="fa fa-2x fa-arrows-v pointer" href="#<?= $_1_u__lp_idlp . $modf ?>_collapse_rep" data-toggle="collapse"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="panel-body col-md-12" id="<?= $_1_u__lp_idlp . $modf ?>_collapse_rep" style="padding: 0px 0px !important;background-color: #dddddd;width: 100%;">
                                                                    <table class="table-striped" style="width:100%;padding: 0px 5px !important">
                                                                    <? foreach ($arrmodrep as $modf2 => $valf2) { ?>
                                                                        <tr class="" cbmodulo="<?= $modf2 ?>" cbidlpmodulo="<?= $valf2["idlpmodulo"] ?>" modulofilho style="padding: 3px 10px !important; margin-top: 0px !important;">
                                                                            <td style="white-space:normal;text-align:left;color: black;border-left:8px solid <?= $valf2['corhex'] ?>;" class="col-md-12" title="<?= $modf2 ?>">
                                                                                <div class="col-md-1">
                                                                                    <input type="checkbox" flgcontaitem="<?= $valf2["flgcontaitem"] ?>" flgidpessoa="<?= $valf2["flgidpessoa"] ?>" flgunidade="<?= $valf2["flgunidade"] ?>" modulo=<?= $modf ?> idrep="<?= $valf2['idrep'] ?>" modulorep permissao="<?= $valf2["permissao"] ?>" idlprep='<?= $valf2["idlprep"] ?>' title="<?= $valf2["rotPermissao"] ?>,<?= $_1_u__lp_idlp ?>">
                                                                                </div>
                                                                                <div class="col-md-7" >
                                                                                    <span><?= $valf2["rep"] ?></span>&nbsp;<span style="background: rgb(102, 102, 102);font-size: 10px;color: #fff;padding: 0px 6px;border-radius: 3px;"><?=$valf2['reptipo']?></span>
                                                                                    <i class="fa fa-navicon azul pointer" onclick="janelamodal('?_modulo=_rep&_acao=u&idrep=<?= $modf2 ?>')"></i>
                                                                                </div>
                                                                                <div class="col-md-4 d-flex justify-content-end" >
                                                                                    <? if ($valf2["status"] == "INATIVO") {
                                                                                        echo "<i class='fa fa-ban fonte08' style='display: block;' title='Inativo'></i>";
                                                                                    } ?>
                                                                                    <button style=";<?= $valf2["displayBtnContaItem"] ?>;" class="ml-2 btn btn-xs <?= $valf2["classeBtCT"] ?> btn-contaitem" flgcontaitem="<?= $valf2["flgcontaitem"] ?>" idlprep='<?= $valf2["idlprep"] ?>' idrep="<?=$valf2['idrep']?>" onclick="perrmissaoRepFLG(this,'flgcontaitem',<?=$_1_u__lp_idlp?>)">
                                                                                        <i style="margin:2px;font-size:12px;width:12px" class="contaitem <?= $valf2["icoPermissaoCT"] ?> <?= $valf2["corIcoCT"] ?> pointer pull-left " flgcontaitem="<?= $valf2["flgcontaitem"] ?>" idlprep='<?= $valf2["idlprep"] ?>'></i>
                                                                                    </button>
                                                                                    <button style="<?= $valf2["displayBtnOrganograma"] ?>;" class="ml-2 btn btn-xs <?= $valf2["classeBtO"] ?> btn-idpessoa" flgidpessoa="<?= $valf2["flgidpessoa"] ?>" idlprep='<?= $valf2["idlprep"] ?>' idrep="<?=$valf2['idrep']?>" onclick="perrmissaoRepFLG(this,'flgidpessoa',<?=$_1_u__lp_idlp?>)">
                                                                                        <i style="margin:2px;font-size:12px;width:12px;" class="organograma <?= $valf2["icoPermissaoO"] ?> <?= $valf2["corIcoO"] ?> pointer pull-left " flgidpessoa="<?= $valf2["flgidpessoa"] ?>" idlprep='<?= $valf2["idlprep"] ?>'></i>
                                                                                    </button>
                                                                                    <button style="<?= $valf2["displayBtnUnidade"] ?>;" class="ml-2 btn btn-xs <?= $valf2["classeBtU"] ?> btn-un" flgunidade="<?= $valf2["flgunidade"] ?>" idlprep='<?= $valf2["idlprep"] ?>' idrep="<?=$valf2['idrep']?>" onclick="perrmissaoRepFLG(this,'flgunidade',<?=$_1_u__lp_idlp?>)">
                                                                                        <i style="margin:2px;font-size:12px;width:12px;" class="unidade <?= $valf2["icoPermissaoU"] ?> <?= $valf2["corIcoU"] ?> pointer pull-left " flgunidade="<?= $valf2["flgunidade"] ?>" idlprep='<?= $valf2["idlprep"] ?>'></i>
                                                                                    </button>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    <? } ?>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        <? } ?>
                                                    </div>
                                                <? } ?>
                                            </div>
                                        <? } ?>
                                    </div>
                                <? } ?>
                            </div>
                        </div>
                    <?  
                $tipomod = $val['divisao'];    
                } 
                    $tipomod= '';
                    // echo '</table>';
                    echo '</div>';
                    echo '</div>';
                    ?>
                </div>
            </div>

        </div><!-- Modulos -->
    </div>
</div>
<script>
    var idlp = <?= $_1_u__lp_idlp?>;

function collapseAll(vthis,inout){debugger

    if(inout == 'in'){
        oposite = 'out'
        text = 'Recolher';
        angle = 'up'
    }else{
        oposite = 'in';
        text = 'Expandir'
        angle = 'down'
    }

    $(vthis).parent().parent().siblings(".panel-body").children().find("[data-toggle='collapse']").each((i,e)=>{
        href = $(e).attr('href');
        if(!$(href).hasClass(inout)){
            $(e).click();
        }
    })

    $(vthis).html(`
        ${text} todos &nbsp;
        <i class="fa fa-chevron-${angle}" ></i>
    `)
    $(vthis).attr('onclick','collapseAll(this,"'+oposite+'")')
}

$("[type='checkbox']").change(function() {
    lenCheck = $(`#modsel_lp_${idlp}`).find('[type="checkbox"]:checked')
    if(lenCheck.length > 0) {
        mostrarbtnsSalvar(idlp,'show')
    }else{
        mostrarbtnsSalvar(idlp,'hide')
    }
});
function mostrarbtnsSalvar(idlp,fun){
	if($(`#modsel_lp_${idlp}`).find('[type="checkbox"][modulofilho]:checked').length > 0 || $(`#modsel_lp_${idlp}`).find('[type="checkbox"][modulopai]:checked').length > 0 || $(`#modsel_lp_${idlp}`).find('[type="checkbox"][modulofunc]:checked').length > 0){
		$("#cbpost_mods_"+idlp).find('.btn-rel').hide()
	}else{
		$("#cbpost_mods_"+idlp).find('.btn-rel').show()
	}
	(fun == 'hide')?
	$("#cbpost_mods_"+idlp).hide():
	$("#cbpost_mods_"+idlp).show()
}

function perrmissaoModuloPai(inPermissao,inidlp,e){
    strPost = '';
    inIdLpModulo=$(e).attr("idlpmodulo");
    inMod=$(e).attr("modulo");

    if(inMod==""){
        console.log("Erro: MODULO vazio!");

    }else{

        var sacao;
        var ord = "";

        //se nao houver registro na tabela, inserir permissao de [L]eitura
        if(inIdLpModulo=="" && inPermissao ==''){
            sacao="i";
            ord += "&_ajax_"+sacao+"__lpmodulo_ord=9999";
            strPost +=   "&_ajax_"+sacao+"__lpmodulo_idlp="+inidlp
                        +"&_ajax_"+sacao+"__lpmodulo_modulo="+inMod
                        +"&_ajax_"+sacao+"__lpmodulo_permissao=r"+ord;

        //caso exista leitura, alterar para Escrita (w)
        }else if(inIdLpModulo!="" && inPermissao =='r'){
            sacao="u";
            strPost += "&_ajax_"+sacao+"__lpmodulo_idlp="+inidlp
                        +"&_ajax_"+sacao+"__lpmodulo_modulo="+inMod
                        +"&_ajax_"+sacao+"__lpmodulo_permissao=w"
                        +"&_ajax_"+sacao+"__lpmodulo_idlpmodulo="+inIdLpModulo+ord;

        //caso exista escrita, alterar para Leitura (r)
        }/*else if(inIdLpModulo!="" && inPermissao=="w"){
            sacao="u";
            spermissao="r";
        }*/else if(inIdLpModulo!="" && inPermissao =='w'){
            //caso exista na tabela algum registro fora do padrão
            sacao = "d";
            strPost += "&_ajax_"+sacao+"__lpmodulo_idlpmodulo="+inIdLpModulo+ord;
        }

        
	CB.post({
            objetos: strPost,
            parcial:true,
        });
    
    }
}
function perrmissaoRepFLG(e,flg,idlp){debugger
    strPost = '';
    inIdLpRep=$(e).attr("idlprep");
    inRep=$(e).attr("idrep");
    inflg=$(e).attr(flg);

    if(inIdLpRep==""){
        sacao="i";
        strPost += "&_ajaxrep_"+sacao+"__lprep_idrep="+inRep+"&_ajaxrep_"+sacao+"__lprep_idlp="+idlp
                    +"&_ajaxrep_"+sacao+"__lprep_"+flg+"=Y";
    }else{
        
        if(inflg=="Y"){
            sacao="u";
            Nbtn="btn-primary";
            Rbtn = "";
            

            strPost += "&_ajaxrep_"+sacao+"__lprep_idlprep="+inIdLpRep
                    +"&_ajaxrep_"+sacao+"__lprep_"+flg+"=N";

        //caso exista leitura, alterar para Escrita (w)
        }else{
            //caso exista na tabela algum registro fora do padrão
            sacao = "u";
            Rbtn="btn-danger";
            Nbtn = "";
            
            
            strPost += "&_ajaxrep_"+sacao+"__lprep_idlprep="+inIdLpRep
                        +"&_ajaxrep_"+sacao+"__lprep_"+flg+"=Y";
        }

        
        
    }
    CB.post({
            objetos: strPost,
            parcial:true,
        });
}

function checkfilhos(vthis,modulotipo){
	$(vthis).parent().parent().siblings().find('[type="checkbox"]').each((i,e)=>{
			$(e).prop('checked',$(vthis).prop('checked'));
	});
}
function relacionaLpModuloBT(inPermissao,inidlp){debugger
	var strPost = "";
    var mods = [];
	$(`#modsel_lp_${idlp}`).find('[type="checkbox"]:checked').not('[modulorep]').not('[moduloreppai]').not('[modulofuncpai]').each((i,e)=>{
		inIdLpModulo=$(e).attr("idlpmodulo");
		inMod=$(e).attr("modulo");

		if(inMod==""){
			console.log("Erro: MODULO vazio!");

		}else{
            if(!mods.includes(inMod)){
                mods.push(inMod)
                

                var sacao;
                var ord = "";

                //se nao houver registro na tabela, inserir permissao de [L]eitura
                if(inIdLpModulo=="" && inPermissao !='d'){
                    sacao="i";
                    ord += "&_ajax"+i+"_"+sacao+"__lpmodulo_ord=9999";
                    strPost +=   "&_ajax"+i+"_"+sacao+"__lpmodulo_idlp="+inidlp
                                +"&_ajax"+i+"_"+sacao+"__lpmodulo_modulo="+inMod
                                +"&_ajax"+i+"_"+sacao+"__lpmodulo_permissao="+inPermissao+ord;

                //caso exista leitura, alterar para Escrita (w)
                }else if(inIdLpModulo!="" && inPermissao !='d'){
                    sacao="u";
                    strPost += "&_ajax"+i+"_"+sacao+"__lpmodulo_idlp="+inidlp
                                +"&_ajax"+i+"_"+sacao+"__lpmodulo_modulo="+inMod
                                +"&_ajax"+i+"_"+sacao+"__lpmodulo_permissao="+inPermissao
                                +"&_ajax"+i+"_"+sacao+"__lpmodulo_idlpmodulo="+inIdLpModulo+ord;

                //caso exista escrita, alterar para Leitura (r)
                }/*else if(inIdLpModulo!="" && inPermissao=="w"){
                    sacao="u";
                    spermissao="r";
                }*/else if(inIdLpModulo != ""){
                    //caso exista na tabela algum registro fora do padrão
                    sacao = "d";
                    strPost += "&_ajax"+i+"_"+sacao+"__lpmodulo_idlpmodulo="+inIdLpModulo+ord;
                }

		    }
        }
	});

	$(`#modsel_lp_${idlp}`).find('[type="checkbox"][modulorep]:checked').each((i,e)=>{
		inIdLpRep=$(e).attr("idlprep");
		inRep=$(e).attr("idrep");

		if(inRep==""){
			console.log("Erro: idrep vazio!");
		}else{

			var sacao;

			//se nao houver registro na tabela, inserir permissao de [L]eitura
			if(inIdLpRep=="" && inPermissao != 'd'){
				sacao ="i";
				strPost += "&_ajaxrep"+i+"_"+sacao+"__lprep_idlp="+inidlp
							+"&_ajaxrep"+i+"_"+sacao+"__lprep_idrep="+inRep;
				

			//caso exista leitura, alterar para Escrita (w)
			}else if(inPermissao == 'd' && inIdLpRep != ''){
				//caso exista na tabela algum registro fora do padrão
				sacao = "d";
				strPost += "&_ajaxrep"+i+"_"+sacao+"__lprep_idlprep="+inIdLpRep;
			}
		}
	})

	CB.post({
            objetos: strPost,
            parcial:true,
			// refresh:false
            // posPost: function(data, textStatus, jqXHR){
                
            //     $(vthis).attr("permissao", spermissao || "");
            //     if(CB.lastInsertId>0){
            //         $(vthis).attr("idlpmodulo", CB.lastInsertId);
            //     }
            //     $(vthis).removeClass(Ricon).addClass(Nicon);
            //     $(vthis).parent().parent().parent().find('button').first().removeClass(Rbtn).addClass(Nbtn);
                
            // }
        });

}
function relacionaLpRepUBT(idlp){debugger

    var strPost = '';
    $(`#modsel_lp_${idlp}`).find('[type="checkbox"][modulorep]:checked').each((i,e)=>{
		inIdLpRep=$(e).attr("idlprep");
		inRep=$(e).attr("idrep");
		inflgunidade=$(e).attr("flgunidade");

		if(inIdLpRep==""){
			sacao="i";
            strPost += "&_ajaxrep"+i+"_"+sacao+"__lprep_idrep="+inRep+"&_ajaxrep"+i+"_"+sacao+"__lprep_idlp="+idlp
                    +"&_ajaxrep"+i+"_"+sacao+"__lprep_flgunidade=Y";
		}else{
            if($(e).parent().parent().find('.btn-un').is(':visible')){
                if(inflgunidade=="Y"){
                }else{
                    //caso exista na tabela algum registro fora do padrão
                    sacao = "u";
                    Rbtn="btn-danger";
                    Nbtn = "";
                    
                    
                    strPost += "&_ajaxrep"+i+"_"+sacao+"__lprep_idlprep="+inIdLpRep
                                +"&_ajaxrep"+i+"_"+sacao+"__lprep_flgunidade=Y";
                }
            }
		}
	})
    CB.post({
        objetos: strPost,
        parcial:true,
    });
}
function relacionaLpRepIdPessoa(idlp){
    var strPost = '';
    $(`#modsel_lp_${idlp}`).find('[type="checkbox"][modulorep]:checked').each((i,e)=>{
		inIdLpRep=$(e).attr("idlprep");
		inRep=$(e).attr("idrep");
		inflgidpessoa=$(e).attr("flgidpessoa");

		if(inIdLpRep==""){
			sacao="i";
            strPost += "&_ajaxrep"+i+"_"+sacao+"__lprep_idrep="+inRep+"&_ajaxrep"+i+"_"+sacao+"__lprep_idlp="+idlp
                    +"&_ajaxrep"+i+"_"+sacao+"__lprep_flgidpessoa=Y";
		}else{
            if($(e).parent().parent().find('.btn-idpessoa').is(':visible')){
                if(inflgidpessoa=="Y" && inIdLpRep !=''){
                }else{
                //caso exista na tabela algum registro fora do padrão
                sacao = "u";
                Rbtn="btn-danger";


                strPost += "&_ajaxrep"+i+"_"+sacao+"__lprep_idlprep="+inIdLpRep
                            +"&_ajaxrep"+i+"_"+sacao+"__lprep_flgidpessoa=Y";
                }
            }
		}
	});
    
    CB.post({
        objetos: strPost,
        parcial:true,
    });
}

function relacionaLpRepIdContaItem(idlp){debugger
    var strPost = '';
    $(`#modsel_lp_${idlp}`).find('[type="checkbox"][modulorep]:checked').each((i,e)=>{
        inIdLpRep=$(e).attr("idlprep");
        inRep=$(e).attr("idrep");
        inflgcontaitem=$(e).attr("flgcontaitem");
        if(inIdLpRep==""){
			sacao="i";
            strPost += "&_ajaxrep"+i+"_"+sacao+"__lprep_idrep="+inRep+"&_ajaxrep"+i+"_"+sacao+"__lprep_idlp="+idlp
                    +"&_ajaxrep"+i+"_"+sacao+"__lprep_flgcontaitem=Y";
		}else{
            if($(e).parent().parent().find('.btn-contaitem').is(':visible')){
                if(inflgcontaitem=="Y" && inIdLpRep !=''){
                }else{
                //caso exista na tabela algum registro fora do padrão
                sacao = "u";
                Rbtn="btn-danger";


                strPost += "&_ajaxrep"+i+"_"+sacao+"__lprep_idlprep="+inIdLpRep
                            +"&_ajaxrep"+i+"_"+sacao+"__lprep_flgcontaitem=Y";
                }
            }
		}
    });
    
    CB.post({
        objetos: strPost,
        parcial:true,
    });
}

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>