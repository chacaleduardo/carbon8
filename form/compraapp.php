<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/compraapp_controller.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "nf";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idnf" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT n.idnf, n.total, n.obs, n.idempresa, n.total, n.idobjetosolipor, n.dtemissao, n.status, n.idfluxostatus, n.criadopor, n.criadoem, 
                  n.alteradopor, n.alteradoem, ni.moeda, ni.idnfitem, ni.prodservdescr, ni.qtd
             FROM nf n LEFT JOIN nfitem ni ON n.idnf = ni.idnf 
            WHERE n.idnf = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$arrayStatus = array('CONCLUIDO', 'CANCELADO', 'REPROVADO');
if(in_array($_1_u_nf_status, $arrayStatus))
{
    $disabled = 'readonly';
} 

?>
<link href="../form/css/compraapp_css.css?_<?=date("dmYhms")?>" rel="stylesheet">
<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-default" style="font-size:12px">
            <!-- header -->
            <div class="panel-heading">
                <div class="row d-flex d-md-block flex-wrap align-items-end px-2 px-md-0">
                    <div class="px-0 col-xs-12 col-sm-1 sigla-empresa"></div>
                    <!-- Valor da despesa -->
                    <div class="d-flex flex-column col-xs-8">
                        <label for="" class="text-white">Valor da despesa</label>
                        <div class="d-flex align-items-center form-valor col-xs-10 col-sm-6 col-md-4 p-0">
                            <span class="h1">
                                R$ 
                            </span>
                            <input class="h1" name="valorapp" <?=$disabled?> inputmode="decimal" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode <= 44" value="<?=$_1_u_nf_total?>" vnulo>
                        </div>
                    </div>
                    <!-- Conversao -->
                    <div class="col-xs-3 text-end d-md-block">
                        <?
                        if(empty($_1_u_nf_moeda))
                        {
                            $_1_u_nf_moeda = 'BRL';
                        }
                        ?>
                        <select name="moedaapp" <?=$disabled?> class="select-custom border-0 bg-transparent text-uppercase bs-none text-white" vnulo>
                            <?=fillselect(CompraAppController::$_moeda, $_1_u_nf_moeda);?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="col-xs-6">
                            <!-- Definir Data -->                        
                            <div class="row m-0 d-flex align-items-center date-options">                            
                                <div class="w-100">
                                    <label for="" class="mb-1 ">Data Emissão Cupom</label>
                                    <div class="col-xs-12 d-flex px-0">
                                        <input id="date-input" type="text" name="data" class="hidden" hidden />
                                        <button class="btn btn-xs bg-primary text-white btn-hoje" data-value="<?= date('Y-m-d') ?>">Hoje</button>
                                        <button class="btn btn-xs bg-secondary text-white mx-2 btn-ontem" data-value="<?= date('Y-m-d', time()-(3600*27)) ?>">Ontem</button>                                                                
                                        <div class="position-relative btn btn-xs bg-secondary text-white d-flex align-items-center btn-calendario">
                                            <i class="fa fa-calendar-check-o text-4xl"></i>
                                            <span type="button" class="btn-outros pl-10"></span>                                
                                        </div>
                                        <input type="hidden" class="hidden" name="_1_<?=$_acao?>_nf_idnf" value="<?=$_1_u_nf_idnf?>"/>
                                        <?
                                        if(empty($_1_u_nf_dtemissao))
                                        {
                                            $_1_u_nf_dtemissao = date('Y-m-d');
                                        }
                                        ?>
                                        <input id="date-input" type="hidden" class="dtemissaoapp"  name="dtemissaoapp" value="<?=$_1_u_nf_dtemissao?>"/>
                                    </div>                       
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="row m-0 d-flex align-items-right date-options">                            
                                <div class="w-100">
                                    <? if($_GET['_acao'] == 'u')
                                    {
                                        ?>  
                                        <label for="" class="mb-1">Status</label>
                                        <div class="col-xs-12 d-flex px-0">
                                            <span>
                                                <? $rotulo = getStatusFluxo($pagvaltabela, 'idnf', $_1_u_nf_idnf) ?>
                                                <label class="d-flex align-items-center form-control alert-warning" title="<?= $_1_u_nf_status ?>" id="statusButton"><?= mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?> </label>
                                            </span>
                                        </div>
                                        <?
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <!-- Descricao -->
                    <div class="col-xs-12 col-sm-6">
                        <label for="" class="mb-1">Descrição (Opcional)</label>
                        <input type="text" class="form-control" name="obsapp" <?=$disabled?> value="<?=$_1_u_nf_obs?>"/>
                        <hr />
                    </div>
                    <!-- Tipo -->
                    <div class="col-xs-12 col-sm-6">
                        <label for="" class="mb-1">Tipo</label>
                        <input type="hidden" class="hidden"  name="idnfitemapp" <?=$disabled?> value="<?=$_1_u_nf_idnfitem?>"/>
                        <?
                        if($_1_u_nf_prodservdescr)
                        {
                            $tipoprodserv = $_1_u_nf_prodservdescr;
                        } else {
                            $tipoprodserv = $arrTipo[$_1_u_nf_idobjetosolipor]['tipoprodserv'];
                        }

                        $disabled2 = empty($_1_u_nf_idnf) ? "" : 'readonly'; 
                        ?>
                        <select type="text" class="form-control cinza" <?=$disabled?> <?=$disabled2?> name="prodservdescr" vnulo onchange="CB.post()">
                            <option value=""></option>
                            <?=fillselect(CompraAppController::buscarTipoProdservPorAppFillSelect(), $_1_u_nf_idobjetosolipor);?>
                        </select>
                        <hr />
                    </div>  
                    <!-- Quantidade -->    
                    <?if(!empty($_1_u_nf_idnf))
                    {
                        ?>              
                        <div class="inserirValor <?if(empty($_1_u_nf_idobjetosolipor)) {?> d-md-none <? } ?> row">
                            <!-- Quantidade Itens -->
                            <div class="col-xs-12 col-sm-6">
                                <div class="row m-0 d-flex align-items-center date-options">
                                    <div class="w-100">
                                        <label for="" class="mb-1">Quantidade</label>
                                        <div class="col-xs-12 d-flex px-0">
                                            <input type="text" class="form-control size10" <?=$disabled?> name="qtdapp" onchange="atualizarValor(this)" value="<?=$_1_u_nf_qtd?>" vnulo inputmode="decimal"/>
                                            <div class="position-relative d-flex align-items-center pl-10">
                                                <? 
                                                $tipoprodserv = CompraAppController::buscarTipoProdservPorApp($_1_u_nf_idobjetosolipor, 'idtipoprodserv'); 
                                                switch ($tipoprodserv['tipoprodserv']) 
                                                {
                                                    case stripos($tipoprodserv['tipoprodserv'], 'ALIMENTAÇÃO'):
                                                    case stripos($tipoprodserv['tipoprodserv'], 'ESTACIONAMENTO'):
                                                    case stripos($tipoprodserv['tipoprodserv'], 'FORNECEDOR'):
                                                    case stripos($tipoprodserv['tipoprodserv'], 'LAVA'):
                                                        echo "Unidade";
                                                    break;
                                                    case stripos($tipoprodserv['tipoprodserv'], 'HOSPEDAGEM'):
                                                        echo "Diária";
                                                    break;
                                                    case stripos($tipoprodserv['tipoprodserv'], 'COMBUSTÍVEL'):
                                                        echo "Litro";
                                                    break;
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr />
                            </div>
                            <!-- Valor Unitário -->
                            <div class="col-xs-12 col-sm-6">                                                            
                                <label for="" class="mb-1">Valor Unitário</label>
                                <div class="valor_unitario">
                                    R$ <?=number_format(str_replace(",", ".", str_replace(".", "", $_1_u_nf_total)) / (empty($_1_u_nf_qtd) ? 1 : $_1_u_nf_qtd), 2, ",", ".")?>
                                </div>
                                <hr />                                
                            </div>
                        </div>
                        <? 
                        if($tipoprodserv['idpessoa'] == 6380){  //Id Pessoa Combusivel
                            $tag = CompraAppController::buscarNfItemAcao($_1_u_nf_idnfitem);
                            ?>
                            <!-- Selecionar Veículo -->
                            <div class="col-xs-12 col-sm-6">
                                <label for="" class="mb-1">Veículo</label>
                                <select type="text" class="form-control cinza" name="idtag">
                                    <option value=""></option>
                                    <?=fillselect(CompraAppController::buscarTagPorIdTagClassVeiculos(3), $tag['idobjeto']); //Tag para Veículos ?>
                                </select>
                                <hr />
                            </div>

                            <!-- KM -->
                            <div class="col-xs-12 col-sm-6">
                                <label for="" class="mb-1">KM Atual</label>
                                <input type="text" class="form-control" name="kmatual" <?=$disabled?> value="<?=$tag['kmrodados']?>"/>
                                <hr />
                            </div>
                        <? } ?>

                        <!-- Camera -->
                        <div style="text-align: center;" class="col-xs-12 div_camera">
                            <div style="text-align: center;" class="col-xs-12">
                                <button onclick="loadCamera(this)" class="btn btn-primary">Usar Câmera</button>
                            </div>
                            <div style="text-align: center;" class="col-xs-12">
                                <video class="hidden" autoplay="true" id="webCamera"></video>
                            </div>
                            <div style="text-align: center;" class="col-xs-12">
                                <img src="" class="imgtaken" id="img_taken">
                            </div>
                            <div style="text-align: center;" class="col-xs-12">
                                <button class="btn btn-success hidden" id="takeSnapShot" type="button" onclick="takeSnapShot()">Tirar foto</button>
                                <button class="btn btn-danger hidden"  id="deleteShot" type="button" onclick="loadCamera()"><i class="fa fa-remove text-4xl"></i>&nbsp;Tirar outra foto</button>
                                <button class="btn btn-success hidden" id="sendShot" type="button" onclick="enviaFoto()"><i class="fa fa-check text-4xl"></i>&nbsp;Enviar</button>
                            </div>
                        </div>
                        <!-- Anexo -->
                        <div class="col-xs-12">
                            <label for="" class="mb-1">Anexo</label>
                            <div class="cbupload w-100" id="tag" title="Clique ou arraste arquivos para cá">
                                <i class="fa fa-cloud-upload fonte18"></i>
                            </div>
                            <hr />
                        </div>                                                   
                        <div class="col-xs-12">
                            <?
                            if($_1_u_nf_status == 'INICIO' && CompraAppController::buscarArquivoPorTipoObjetoEIdObjeto('nf', $_1_u_nf_idnf) > 0)
                            {
                                ?> 
                                <!-- Salvar -->
                                <div class="col-xs-6 text-right">
                                    <button id="cbSalvar" type="button" class="btn btn-success btn-xs" onclick="alterarFluxo('CONCLUIDO')" title="Salvar">
                                        <i class="fa fa-circle"></i>Concluir
                                    </button>
                                </div>
                                <?
                                $configuracaoCancelar = 'col-xs-6 text-left';
                            } else {
                                $configuracaoCancelar = 'col-xs-12 text-center';
                            }

                            if($_1_u_nf_status != 'CANCELADO'){
                                ?>  
                                <!-- Cancelar -->
                                <div class="<?=$configuracaoCancelar?>">
                                    <button id="cbSalvar" type="button" class="btn btn-danger btn-xs" onclick="alterarFluxo('CANCELADO')" title="Salvar">
                                        <i class="fa fa-circle"></i>Cancelar
                                    </button>
                                </div>
                            <? } ?>
                        </div>
                        <?
                    }
                    ?>                    
                </div>
            </div>
        </div>
    </div>
</div>
<?
$tabaud = "nf"; 
$_disableDefaultDropzone = true; //Condição para não aparecer o dropzone
require 'viewCriadoAlterado.php';
require_once('../form/js/compraapp_js.php');
?>