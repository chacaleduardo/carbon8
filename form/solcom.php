<?
require_once("../inc/php/validaacesso.php");
require_once("controllers/solcom_controller.php");

if($_POST){
    include_once("../inc/php/cbpost.php");
}

$pagvaltabela = "solcom";
$pagvalcampos = array(
    "idsolcom" => "pk"
);

$pagsql = "SELECT * from solcom where idsolcom = '#pkid'";
include_once("../inc/php/controlevariaveisgetpost.php");

//Recuperar a unidade padrão conforme módulo pré-configurado
$_idempresa = !empty($_GET['_idempresa']) ? $_GET['_idempresa'] : $_SESSION['SESSAO']['IDEMPRESA'];
$unidadepadrao = getUnidadePadraoModulo($_GET["_modulo"], $_idempresa);
if($_acao == 'i'){
    $_1_u_solcom_idunidade = $unidadepadrao; 
}

if (empty($_1_u_solcom_status)){
	$_1_u_solcom_status = 'ABERTO';
}

$comprasMaster = array_key_exists("comprasmaster", getModsUsr("MODULOS"));

$arrayReadOnly = array('CONCLUIDO', 'CANCELADO', 'CADASTRO');
if(in_array($_1_u_solcom_status, $arrayReadOnly) /*&& $comprasMaster == 0*/)//@882441 - DESABILITAR CAMPOS NOS STATUS DO FLUXO FINAL
{
    $readonly2 = 'readonly'; 
}

$arrayReadOnly2 = array('CONCLUIDO', 'CANCELADO', 'CADASTRO', 'APROVADO', 'SOLICITADO');
if(in_array($_1_u_solcom_status, $arrayReadOnly2)/* && $comprasMaster == 0*/)//@882441 - DESABILITAR CAMPOS NOS STATUS DO FLUXO FINAL
{
    $readonly = 'readonly';
    $disable ='disabled';
}

$i = 99;

if(!empty($_1_u_solcom_idsolcom)) 
{ 
    $jprodserv = SolcomController::listarProdutos($_1_u_solcom_idsolcom);
    $jprodservFabricado= SolcomController::listarProdutosFabricados($_1_u_solcom_idsolcom); 
}

$userPref = json_decode(SolcomController::buscarPreferenciaPessoa('solcom', $_SESSION["SESSAO"]["IDPESSOA"]), true);

//Verifica se tem algum item Cancelado na Solcom
$qtdItensSolcomCancelados = SolcomController::buscarItensSolcomCancelados($_1_u_solcom_idsolcom);    

?>
<link href="../form/css/solcom_css.css?_<?=date("dmYhms")?>" rel="stylesheet">
<div class="row">
    <div class="col-md-12" >
        <div class="panel panel-default" style="padding-bottom: 11px;">
            <div class="panel-heading">
                <div class="sigla-empresa"></div>
                <div class="row d-flex flex-wrap <?= ($_acao == 'i' ? '' : 'flex-between') ?>">
                    <!-- ID -->
                    <?if(!empty($_1_u_solcom_idsolcom)){
                            $idpessoa = $_1_u_solcom_idpessoa;
                        ?>
                        <div class="form-group col-xs-4 col-md-2">
                            <input name="_1_<?=$_acao?>_solcom_idsolcom" id="idsolcom" type="hidden" value="<?=$_1_u_solcom_idsolcom?>" readonly='readonly'>
                            <input name="_1_<?=$_acao?>_solcom_status" id="status" type="hidden" value="<?=$_1_u_solcom_status?>">
                            <input name="statusant" type="hidden" value="<?=$_1_u_solcom_status?>">
                            <label for="" class="text-white">
                                ID
                            </label>
                            <label class="alert-warning d-flex align-items-center form-control"><?=$_1_u_solcom_idsolcom?></label>
                        </div>
                    <?} else {
                        $idpessoa = $_SESSION["SESSAO"]["IDPESSOA"];
                    }?>

                    <input name="_1_<?=$_acao?>_solcom_idpessoa" id="idpessoa" type="hidden" value="<?=$idpessoa?>">

                    <!-- Titulo -->
                    <div class="form-group col-xs-8 col-md-4">
                        <label for="" class="text-white">Título</label>
                        <input name="_1_<?=$_acao?>_solcom_titulo" class="form-control" type="text" value="<?=mb_strtoupper($_1_u_solcom_titulo)?>" vnulo="" style="text-transform:uppercase">
                    </div>

                    <!-- Unidade -->
                    <div class="form-group col-xs-5 col-md-1">
                        <label for="" class="text-white">Unidade</label>
                        <? 
                        if(!empty($_1_u_solcom_idsolcom)){?>
                            <?if (empty($_1_u_solcom_idunidade)) {
                                $rotuloUnidade = traduzid('unidade', 'idunidade', 'unidade', traduzid("pessoa",'usuario','idunidade',$_1_u_solcom_criadopor));
                            }else {
                                $rotuloUnidade = traduzid('unidade', 'idunidade', 'unidade', $_1_u_solcom_idunidade);
                            }?>
                            <label class="alert-warning d-flex align-items-center form-control"><?=$rotuloUnidade?></label>
                            <input type="hidden" name="_1_<?=$_acao?>_solcom_idunidade" value="<?=$_1_u_solcom_idunidade?>" readonly>
                        <? } else { 
                            ?>
                            <select name="_1_<?=$_acao?>_solcom_idunidade" <?if(!empty($_1_u_solcom_idsolcom)){?>readonly<?}?> vnulo class="form-control">
                                <option value=""></option>
                                <? fillselect(SolcomController::listarFillSelectUnidadeAtivo('unidade', '_lp', getModsUsr("LPS"), $_SESSION["SESSAO"]["IDPESSOA"], $comprasMaster), $_1_u_solcom_idunidade);?>
                            </select>
                        <? } ?>
                    </div>

                    <!-- Importacao -->
                    <div class="form-group col-xs-1 col-md-1">
                        <label for="" class="text-white">Importação</label><br />
                        <input type="checkbox" onclick="alterarImportacao(this, <?=$_1_u_solcom_idsolcom?>)" <?=$_1_u_solcom_importacao == 'S' ? 'checked' : '' ?>>
                    </div>

                    <!-- Status -->
                    <? $rotulo = getStatusFluxo('solcom', 'idsolcom', $_1_u_solcom_idsolcom); ?>
                    <? if($rotulo) { ?>
                        <div class="form-group col-xs-6 col-md-2">
                            <label for="" class="text-white">Status</label>
                            <label class="alert-warning d-flex align-items-center form-control" title="<?=$_1_u_solcom_status?>" id="statusButton"><?=mb_strtoupper($rotulo['rotulo'],'UTF-8')?> </label></td>
                        </div>
                    <?}?>
                    <? if($_1_u_solcom_idsolcom) { ?>
                        <div class="form-group col-xs-12 col-md-auto d-flex align-items-center justify-content-end">
                            <a title="Imprimir Solicitação de Compras." class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/relsolcom.php?_acao=u&idsolcom=<?=$_1_u_solcom_idsolcom?>')"></a>
                        </div>
                    <? } ?>
                </div>
            </div>
            <div class="panel-body">
                <? if(!empty($_1_u_solcom_idsolcom)){ ?>
                    <div class="row">
                        <div class="col-xs-12 px-0">

                            <div class="panel panel-default" style="margin-left: 1%;width: 98%;">
                                <div class="panel-heading bg-none">
                                    Item(ns) Cadastrado(s)
                                    <i title="Itens comprados cadastrados no sistema." class="fa btn-sm fa-info-circle azul pointer hoverazul"></i>
                                    <i class="fa fa-arrows-v fa-1x cinzaclaro pointer" id="esconderMostrarTodos" state="<?=$userPref['collapse']['solcomitemtotal']?>" title="Esconder Todos" onclick="esconderMostrarTodos('cadastrado')" style="float: right; padding: 0 10px 0 10px;"></i>
                                </div>
                                <div id="tbItens" class="panel-body">
                                    <?listarItensSolcom(1, 'cadastrado', true);?>
                                    <? if( $_acao == "u" && $_1_u_solcom_status == 'ABERTO'){ ?>
                                        <div id="modeloNovoItem" class='hidden'>
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <div class="row d-flex">
                                                        <div class="col-xs-6 py-0 px-0">
                                                            Descrição
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="panel-body bg-white" style="padding: 0 !important;">
                                                    <div class="row planilha">
                                                        <div class="col-xs-6">
                                                            <input name="#idprodservItemSolcom" style="text-transform: uppercase;" type="text" class="idprodserv" id="produtocadastrado" placeholder="Selecione o produto / serviço" <?=$readonly?> <?=$disable?>>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <? if($_1_u_solcom_status == 'SOLICITADO' || $_1_u_solcom_status == 'ABERTO') {?>
                                            <i id="novoitem" class="fa fa-plus-circle fa-2x verde btn-lg pointer" onclick="novoItem('')" title="Inserir novo Item"></i>
                                        <? } ?>
                                    <?}?>   
                                </div>
                            </div>
                            
                            <div class="panel panel-default" style="margin-left: 1%;width: 98%;">
                                <div class="panel-heading bg-none">
                                    Item(ns) Não Cadastrado(s) <i title="Itens descritivos não cadastrados no sistema." class="fa btn-sm fa-info-circle azul pointer hoverazul"></i>
                                </div>
                                <div id="tbItensnaoCadastrado" class="panel-body">
                                    <?listarItensSolcom(10000, 'naocadastrado');?>
                                    <?if( $_acao == "u" && $_1_u_solcom_status == 'ABERTO'){?>
                                        <div id="modeloNovoItemnaoCadastrado" class='hidden'>
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <div class="row cabecalho-item-nao-cadastrado">
                                                        <div>Qtd</div>
                                                        <div>Un</div>
                                                        <div>Descrição</div>
                                                    </div>  
                                                </div>
                                                <div class="panel-body bg-white" style="padding: 0 !important;">
                                                    <div class="row dragExcluir align-items-center planilha">
                                                        <input style=" border: 1px solid silver;" name="#qtdItemSolcom" onkeypress="return event.charCode >= 48" min="1" title="Qtd" placeholder="Qtd" type="number" class="size6" <?=$readonly?>>
                                                        <select name="#unItemSolcom" class="un"  placeholder="Un" <?=$readonly?> vnulo> 
                                                            <option value=""></option>
                                                            <? fillselect(SolcomController::listarUnidadeVolume(), $_1_u_prodserv_un);?>
                                                        </select>
                                                        <input type="text" name="#idprodservItemSolcom" style="text-transform: uppercase;"  class="idprodserv"  placeholder="Informe o produto" <?=$readonly?>>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <? if($_1_u_solcom_status == 'SOLICITADO' || $_1_u_solcom_status == 'ABERTO') {?>
                                            <i id="novoitem" class="fa fa-plus-circle fa-2x verde btn-lg pointer" onclick="novoItem('naoCadastrado')" title="Inserir novo Item" <?=$readonly?>></i>
                                        <? } ?>
                                    <?}?>
                                </div>
                            </div>

                            <div class="panel panel-default" style="margin-left: 1%;width: 98%;">
                                <div class="panel-heading bg-none">
                                    Item(ns) Fabricado(s)
                                    <i title="Itens fabricados: Ao selecionar um item fabricado, será listado os produtos da formula para inclusão na Solicitação de Compra." class="fa btn-sm fa-info-circle azul pointer hoverazul"></i>
                                    <i class="fa fa-arrows-v fa-1x cinzaclaro pointer" id="esconderMostrarTodos" state="<?=$userPref['collapse']['solcomitemtotal']?>" title="Esconder Todos" onclick="esconderMostrarTodos('cadastrado')" style="float: right; padding: 0 10px 0 10px;"></i>
                                </div>
                                <div id="tbItensfabricado" class="panel-body">
                                    <?listarItensSolcom(99, 'fabricado', true);?>
                                    <? if( $_acao == "u" && $_1_u_solcom_status == 'ABERTO'){ ?>
                                        <div id="modeloNovoItemfabricado" class='hidden'>
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <div class="row d-flex">
                                                        <div class="col-xs-6 py-0 px-0">
                                                            Descrição
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="panel-body bg-white" style="padding: 0 !important;">
                                                    <div class="row planilha">
                                                        <div class="col-xs-6">
                                                            <input name="#idprodservItemSolcom" style="text-transform: uppercase;" type="text" class="produtofabricado" id="produtofabricado" placeholder="Selecione o produto" <?=$readonly?> <?=$disable?>>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <? if($_1_u_solcom_status == 'SOLICITADO' || $_1_u_solcom_status == 'ABERTO') {?>
                                            <i id="novoitem" class="fa fa-plus-circle fa-2x verde btn-lg pointer" onclick="novoItem('fabricado')" title="Inserir novo Item"></i>
                                        <? } ?>
                                    <?}?>   
                                </div>
                            </div>

                        </div>
                    </div>
                <? } ?>
            </div>
        </div>
    </div>
</div>
<div class="panel panel-default" hidden>
    <div id="comentarioopoup" class="panel-body">
        <table>
            <tr>
                <td>
                    Comentário:
                </td>
            </tr>
            <tr>
                <td>
                    <textarea rows="3" style="width: 400px; height: 80px;" onkeyup="atualizaCampo(this)" name="_99_i_modulocom_descricao" id="_99_i_modulocom_descricao"></textarea>  
                    <input type="hidden" id="_99_i_modulocom_idmodulo" name="_99_i_modulocom_idmodulo" value="<?=$_1_u_solcom_idsolcom?>">
                    <input type="hidden" id="_99_i_modulocom_modulo" name="_99_i_modulocom_modulo" value="solcom">
                </td>
            </tr>
            <tr>
                <td>
                    <table  id="tbItens" class="table table-striped planilha display ">
                        <?
                        $listaComentario = SolcomController::buscarComentarioSolcom($_1_u_solcom_idsolcom);
                        $qtdComentario = $listaComentario['qtdLinhas'];
                        foreach($listaComentario['dados'] as $comentario){?>
                            <tr >
                                <td style="line-height: 14px; padding: 8px; color:#666;">
                                    <div  >
                                        <div style="margin-left: 1px; word-break: break-word;line-height: 14px; padding: 8px; font-size: 11px;color:#666;">
                                            <?=dmahms($comentario['criadoem'])?> - <?=$comentario['criadopor']?>: <?=nl2br($comentario['descricao'])?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?}
                        ?>
                    </table>
                </td>
            </tr>                                
        </table>
    </div>

    <div id="modalsolmat" class="panel-body">  
        <div class="panel-body">  
            <div class="col-md-12" style="margin-top: -15px; color: gray; background-color: #f0f0f0; font-weight: bold; font-size: 11px; border-bottom: 1px solid #ddd;"> 
                <div class="col-md-1" style="text-align: right;">
                    <input checked name="marcartodos" title="Marcar/Desmarcar Todos" type="checkbox" onclick="checkallSolcom(this)">
                </div>    
                <div class="col-md-11" style="margin-top: 5px;">
                    <label>Descrição</label> 
                </div>
            </div>                     
            <?
            $solcomItensCheck = SolcomController::buscarItensSolcomGerarSolmat($_1_u_solcom_idsolcom);
            $i = 0;
            foreach($solcomItensCheck as $_itens)
            {   
                $i++;
                ?>
                <div class="col-md-12" style="width: 100%; border-top: #ddd 1px solid; <?if($i%2 == 0){?> background-color: #f0f0f0; <? } ?>">
                    <div class="col-md-1" style="color:#333; text-align: right;">
                        <input type="checkbox" checked="checked" name="solmatitem" class="idsolcomitem" id="solmatitem<?=$_itens['idsolcomitem']?>" value="<?=$_itens['idsolcomitem']?>" idsolcomitem="<?=$_itens['idsolcomitem']?>" qtd="<?=$_itens['qtdc']?>" onclick="addRemovCheck(this)">
                    </div>
                    <div class="col-md-11">
                        <label><?=mb_strtoupper($_itens['descr'], 'UTF-8')?></label>
                    </div>
                </div>
            <? } ?>
        </div>              
    </div>

    <div id="modalsoltag" class="panel-body">  
        <div class="panel-body">  
            <div class="col-md-12" style="margin-top: -15px; color: gray; background-color: #f0f0f0; font-weight: bold; font-size: 11px; border-bottom: 1px solid #ddd;"> 
                <div class="col-md-1" style="text-align: right;">
                    <input checked name="marcartodos" title="Marcar/Desmarcar Todos" type="checkbox" onclick="checkallSolcom(this)">
                </div>    
                <div class="col-md-11" style="margin-top: 5px;">
                    <label>Descrição</label> 
                </div>
            </div>                     
            <?
            $solcomItensCheck = SolcomController::buscarItensSolcomGerarSoltag($_1_u_solcom_idsolcom);
            $i = 0;
            foreach($solcomItensCheck as $_itens)
            {   
                $i++;
                ?>
                <div class="col-md-12" style="width: 100%; border-top: #ddd 1px solid; <?if($i%2 == 0){?> background-color: #f0f0f0; <? } ?>">
                    <div class="col-md-1" style="color:#333; text-align: right;">
                        <input type="checkbox" checked="checked" name="solmatitem" class="idsolcomitem" id="solmatitem<?=$_itens['idsolcomitem']?>" value="<?=$_itens['idsolcomitem']?>" idsolcomitem="<?=$_itens['idsolcomitem']?>" qtd="<?=$_itens['qtdc']?>" onclick="addRemovCheck(this)">
                    </div>
                    <div class="col-md-11">
                        <label><?=mb_strtoupper($_itens['descr'], 'UTF-8')?></label>
                    </div>
                </div>
            <? } ?>
        </div>              
    </div>
</div>
<?
$tabaud = "solcom";
require 'viewCriadoAlterado.php';
require_once('../form/js/solcom_js.php'); 
?>