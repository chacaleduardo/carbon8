<?
require_once("../inc/php/validaacesso.php");
require_once("controllers/pedido_controller.php");


require_once("../model/prodserv.php");
require_once("../inc/php/permissao.php");
require_once(__DIR__."/controllers/fluxo_controller.php");
require_once(__DIR__."/controllers/nfentrada_controller.php");

//Chama a Classe prodserv
$prodservclass = new PRODSERV();


if (!empty($_GET["idnfcp"]) and empty($_GET["idnf"])) {
    $_GET["idnf"] = $_GET["idnfcp"];
    $_GET["_acao"] = 'u';
    $idnfcp = 'Y';
}
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
$pagsql = "select * from nf where idnf = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

//Recuperar a unidade padrão conforme módulo pré-configurado

$idunidadelote = getUnidadePadraoModulo('lotealmoxarifado', $_GET['_idempresa']);

if(!empty($_1_u_nf_idnatop)){
    $transferencia=PedidoController::verificarnftransf($_1_u_nf_idnf);
}

$ctesVinculadas = [];
$comprasVinculadas = [];
$ctesVinculadasNF = [];

if($_1_u_nf_idnf) {
    $ctesVinculadas = NfController::buscarCteVinculadasPorIdNf($_1_u_nf_idnf);
    $comprasVinculadas = NfController::buscarComprasVinculadasPorIdNf($_1_u_nf_idnf);
    $ctesVinculadasNF=PedidoController::buscarCtePedidoPorId($_1_u_nf_idnf);

    if (!empty($_1_u_nf_idnfe)) $ctesVinculadasNF=PedidoController::buscarCtePedidoPorIdOBS($_1_u_nf_idnfe,$_1_u_nf_idnf);
};

if(!empty($_1_u_nf_idempresafat) and count($transferencia)==0){
    $nf_idempresa=$_1_u_nf_idempresafat;
}else{
    $nf_idempresa=$_1_u_nf_idempresa;
}

if ($idnfcp == 'Y') {
    $_1_u_nf_nnfe = '';
    $_acao = 'i';
    $_1_u_nf_status = 'INICIO';
}

if (empty($_1_u_nf_tiponf)) {
    $_1_u_nf_tiponf = 'V';
}

global $comissionado;

if (!empty($_1_u_nf_idpessoa)) { //PREENCHER CONDICOES DE PAGAMENTO CONFORME PREFERENCIAS DO CLIENTE

    $rowpgto = PedidoController::buscarPreferenciaCliente($_1_u_nf_idpessoa,$_1_u_nf_idempresa);

    if (empty($_1_u_nf_idformapagamento)) {
        $_1_u_nf_idformapagamento = $rowpgto['idformapagamento'];
    }
    
    if (empty($_1_u_nf_parcelas)) {
        $_1_u_nf_parcelas = $rowpgto['parcelavenda'];
    }
    if (empty($_1_u_nf_diasentrada)) {
        $_1_u_nf_diasentrada = $rowpgto['prazopagtovenda'];
    }
    if (empty($_1_u_nf_intervalo)) {
        $_1_u_nf_intervalo = $rowpgto['intervalovenda'];
    }
}

if (empty($_1_u_nf_parcelas)) {
    $_1_u_nf_parcelas = 1;
}
if (empty($_1_u_nf_intervalo)) {
    $_1_u_nf_intervalo = 28;
}
if (empty($_1_u_nf_diasentrada)) {
    $_1_u_nf_diasentrada = 28;
}
if (empty($_1_u_nf_validade)) {
    $_1_u_nf_validade = 28;
}

  
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrCli = PedidoController::listarClietenPedidoPorIdTipoPessoa('1,2,7,12,116');

if($arrCli[$_1_u_nf_idpessoa] == NULL && !empty($_1_u_nf_idpessoa))
{
    $cliente = PedidoController::buscarClientePedidoPorIdPessoa($_1_u_nf_idpessoa);
    $arrCli[$_1_u_nf_idpessoa]['nome'] = $cliente['nome'];
    $arrCli[$_1_u_nf_idpessoa]['tipo'] = $cliente['tipo'];
}

//print_r($arrCli); die;
$jCli = $JSON->encode($arrCli);

//Recupera os produtos a serem selecionados para uma nova Formalização
$arrnatop = PedidoController::listarNatopPorEmpresa();
//print_r($arrnatop[$_1_u_nf_idnatop]["natop"]); die;
$jnatop = $JSON->encode($arrnatop);


//Recupera os produtos a serem selecionados para uma nova Formalização
$arrProd =  PedidoController::buscarProdutoSaida($_1_u_nf_idpessoa);
//print_r($arrCli); die;
$jsonProd = $JSON->encode($arrProd);


if($_1_u_nf_status == 'CONCLUIDO' || $_1_u_nf_status == 'CANCELADO') { 
    $disablednfStatus = "disabled='disabled'";
    $readonlynfStatus = "readonly='readonly'";
} else {
    $disablednfStatus = "";
    $readonlynfStatus = "";
}

?>
<script>
    $(function() {
        $('.caixa').autosize();
    });

    CB.preLoadUrl = function() {
        //Como o carregamento é via ajax, os popups ficavam aparecendo após o load
        $(".webui-popover").remove();
    }

    $(".oEmailorc").webuiPopover({
        trigger: "hover",
        placement: "right",
        delay: {
            show: 300,
            hide: 0
        }
    });

    <?
    if ($_1_u_nf_envionfe == 'CONCLUIDA') {
        $disablednf = "disabled='disabled'";
        $readonlynf = "readonly='readonly'";
    } else {
        $disablednf = "";
        $readonlynf = "";
    } ?>
    
</script>
<style>
    .popover {
        width: 223px !important;
    }

    .ulitens {
        padding-left: 0px;
        border: none;
        background-color: white;
        border: 1px solid;
        border-color: silver;
    }

    .panel-heading[data-toggle][href].collapsed {
        background-color: #e6e6e6;
    }

    .liitens {
        list-style: none;
        border-bottom: 5px solid whitesmoke;
        padding: 1px 10px;
    }

    .liitens div {
        xbackground-color: #E4E2E2;
    }

    .liitens ul {
        padding-left: 10px;
    }

    .nfitem {
        background-color: #ececec !important;
    }

    .nfitemok {
        background-color: #bef69c !important;
    }

    .nfitemalerta {
        background-color: #f6a49c !important;
    }

    .cabitem {
        background-color: #e6e6e6 !important;
    }

    .tdnegrito {
        font-weight: bold;
    }

    .desabilitado {
        background-color: #ece5e5 !important;
    }

    i.tip:hover {
        cursor: hand;
        position: relative
    }

    i.tip span {
        display: none
    }

    i.tip:hover span {
        border: #c0c0c0 1px dotted;
        padding: 5px 20px 5px 5px;
        display: block;
        z-index: 100;
        background: #f0f0f0 no-repeat 100% 5%;
        left: 0px;
        margin: 10px;
        width: 580px;
        position: absolute;
        top: 10px;
        text-decoration: none
    }

    .panel-body {
        padding-top: 10px !important;
    }

    .fa.pull-right {
        margin-left: 0.8em;
    }

    .loadercert {
        border: 2px solid #f3f3f3;
        border-radius: 50%;
        border-top: 2px solid #3498db;
        width: 15px;
        height: 15px;
        animation: spin 2s linear infinite;
        margin-left: 10px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
    #cbModal .modal-content {
        overflow: visible !important;
    }

    .excedendo-estoque-lote, .excedendo-pedido, .faltando-lote {
        background-color: #ffc7c7 !important;
    }

    .not-allowed{
        cursor: not-allowed;
    }

    .loading {
        display: inline-block;
        width: 15px;
        height: 15px;
        border: 3px solid #ccc;
        border-top: 3px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        padding-left: 10px;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .pdlf10{
        padding-left: 10px;
    }
</style>
<? echo "<!--" . getidempresa('p.idempresa', 'cfop') . "-->"; ?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <table>
                    <tr>
                        <td><strong>ID:</strong></td>
                        <td>
                            <? if (!empty($_1_u_nf_idnf)) { ?>
                                <label class="alert-warning"><?= $_1_u_nf_idnf ?></label>
                                <!-- Parcelas configuradas na preferencia do cliente -->
                                 <? if($rowpgto) { ?>
                                    <input type="text" class="hidden" hidden name="_parc_<?= $_acao ?>_nf_idnf" value="<?= $_1_u_nf_idnf ?>" />
                                    <input type="text" class="hidden" hidden name="_parc_<?= $_acao ?>_nf_parcelas" value="<?= $_1_u_nf_parcelas ?>" />
                                 <? } ?>
                            <? }
                            if ($idnfcp == 'Y') { ?>
                                <input name="_1_<?= $_acao ?>_nf_idnf" id="idnf" type="hidden" value="" readonly='readonly'>

                                <input name="_1_<?= $_acao ?>_nf_idobjetosolipor" type="hidden" value="<?= $_GET["idnfcp"] ?>" readonly='readonly'>
                                <input name="_1_<?= $_acao ?>_nf_tipoobjetosolipor" type="hidden" value="nf" readonly='readonly'>
                                <input name="_1_<?= $_acao ?>_nf_idnatop" type="hidden" value="<?= $_1_u_nf_idnatop ?>">
                                <input name="_1_<?= $_acao ?>_nf_tiponf" type="hidden" value="<?= $_1_u_nf_tiponf ?>">
                            <? } else { ?>
                                <input id="idnf" name="_1_<?= $_acao ?>_nf_idnf" type="hidden" value="<?= $_1_u_nf_idnf ?>">
                                <input name="_1_<?= $_acao ?>_nf_tiponf" type="hidden" value="<?= $_1_u_nf_tiponf ?>">
                            <? } ?>
                        </td>
                        <td align="right">Cliente:</td>
                        <td class='nowrap'>
                            <? if ($_1_u_nf_status == 'CONCLUIDO' or $_1_u_nf_status == 'CANCELADO' or $_1_u_nf_envionfe == 'CONCLUIDA') { ?>
                                <input name="_1_<?= $_acao ?>_nf_idpessoa" type="hidden" value="<?= $_1_u_nf_idpessoa ?>">
                            <? echo $arrCli[$_1_u_nf_idpessoa]["nome"];
                            } else { ?>
                                <input type="text" name="_1_<?= $_acao ?>_nf_idpessoa" vnulo cbvalue="<?= $_1_u_nf_idpessoa ?>" value="<?= $arrCli[$_1_u_nf_idpessoa]["nome"] ?>" style="width: 45em;" vnulo>
                            <? } ?>
                        </td>
                        <td class="nowrap">
                            <? if ($_1_u_nf_idpessoa) { ?>
                                <a id="cadcliente" class="fa fa-bars pointer hoverazul" title="Cadastro de  Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $_1_u_nf_idpessoa ?>')"></a>
                                <?
                                $arrcontapagar=PedidoController::buscarCreditoVencidoPorPessoa($_1_u_nf_idpessoa);
                                if(count($arrcontapagar)){
                                    $stcontapagar=implode(",", $arrcontapagar);

                                    $url = "_modulo=contareceber&_acao=u&idcontapagar=[" .$stcontapagar. "]";
                                    $titulo = "Faturas em aberto";
                                    $cor = "primary";
                                    $urlmodal = "_modulo=contareceber&_acao=u";
                                    ?>
                                    &nbsp; &nbsp;
                                    <i id='inadimplencia' inadimplencia='1' class="fa fa-exclamation-triangle laranja pointer hoverazul" onclick="popLink('<?= $url ?>','<?= $titulo ?>','<?= $cor ?>','<?= $urlmodal ?>','modal')" title="Cliente possui fatura em aberto"></i>
                                    &nbsp; &nbsp;
                                    <?
                                } else {
                                    ?>
                                    <i id='inadimplencia' inadimplencia='0' class="hide"></i>
                                    <?
                                }
                            }
                            ?>
                        </td>
                        <? if ($_1_u_nf_idpessoa) { ?>
                            <td align="right">Contrato:</td>
                            <td>
                                <? 
                                $rowb = PedidoController::listarContratoPorPessoa($_1_u_nf_idpessoa);
                                $existecontrato = count($rowb);                               
                                if (!empty($rowb['idcontrato']) and $existecontrato > 0) {
                                ?>
                                    <a class="fa fa-wpforms pointer hoverazul" title="<?= $rowb['titulo'] ?> <?= $rowb['vigenciafim'] ?>" onclick="janelamodal('?_modulo=contrato&_acao=u&idcontrato=<?= $rowb['idcontrato'] ?>')"></a>
                                <? } else { ?>
                                    <a class="fa fa-plus-circle verde pointer hoverazul" title="Novo Contrato" onclick="janelamodal('?_modulo=contrato&_acao=i')"></a>
                                <? } ?>
                            </td>
                        <? } ?>
                        <td style="width:100%"> </td>
                        <td align="right" nowrap> Solicitação:</td>
                        <td><? //if (empty($_1_u_nf_data)){$_1_u_nf_data= date("d/m/Y");}	
                            ?>
                            <input style="width:8em;" <?= $disablednf ?> name="_1_<?= $_acao ?>_nf_data" class="calendario" size="10" value="<?= $_1_u_nf_data ?>" vnulo autocomplete="off">
                        </td>
                        <td align="right" nowrap>Envio:</td>
                        <td style="min-width:180px" >
                        <div class="input-group input-group-sm ">                       
                        <?
                        
                        if (empty($_1_u_nf_envio)) {
                            ?>
                            <div class="col-md-6 ">                              
                                <input style="width:8em;" name="_1_<?=$_acao ?>_nf_envio" id="dataenvio" onkeydown="return false" class="calendario" type="text" <?=$vnulo ?> value="<?=dma($_1_u_nf_envio) ?>">
                            </div>
                            <?
                            } else {
                            ?>
                                 <div class="col-md-6">    
                                    <input style="width:8em;" name="_1_<?=$_acao ?>_nf_envio" id="dataenvio" readonly="readonly" style="background-color: #f2f2f2;" type="text" <?=$vnulo ?> value="<?=dma($_1_u_nf_envio) ?>">
                                </div>
                                <div class="col-md-2">    
                                    <i class="fa fa-pencil btn-lg pointer" title='Editar Envio' onclick="alteravalor('envio','<?=dma($_1_u_nf_envio) ?>','modulohistorico',<?=$_1_u_nf_idnf ?>,'Envio:')"></i>
                                </div>
                            <?
                            }
                            ?>
                            <div class="col-md-1"> 
                                <?
                                $ListarHistoricoModal = NfEntradaController::buscarHistoricoAlteracao($_1_u_nf_idnf, 'envio');
                                $qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
                                if ($qtdvh > 0) 
                                {
                                    ?>                                    
                                    <div class="historicoEnvio" idnfitem="<?=$_itens["idnfitem"]?>">
                                        <i title="Histórico do Envio" class="fa btn-lg fa-info-circle preto pointer hoverazul tip" data-target="webuiPopover0"></i>
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
                                                                <?
                                                                if ($historicoModal['justificativa'] == 'ATRASO') echo 'Atraso no Envio';
                                                                elseif ($historicoModal['justificativa'] == 'PEDIDO CLIENTE') echo 'A Pedido do Cliente';
                                                                elseif ($historicoModal['justificativa'] == 'PRAZO INCORRETO') echo 'Prazo Incorreto';
                                                                elseif ($historicoModal['justificativa'] == 'LOGISTICA') echo 'Alterado Pela Logistíca';
                                                                else echo $historicoModal['justificativa'];
                                                                ?>
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
                                } else {
                                    echo '&nbsp;';
                                }
                                ?>
                            </div>
                        </td>

                        <input name="statusant" type="hidden" style="width: 10px;" value="<?= $_1_u_nf_status ?>">
                        <? if ($_acao == 'u') {
                            $_1_u_nf_status = $_1_u_nf_status;
                        } else {
                            $_1_u_nf_status = 'INICIO';
                        } ?>
                        <input name="_1_<?= $_acao ?>_nf_status" type="hidden" style="width: 10px;" value="<?= $_1_u_nf_status ?>">
                        <td>
                            <span>
                                <? $rotulo = getStatusFluxo($pagvaltabela, 'idnf', $_1_u_nf_idnf) ?>
                                <label class="alert-warning" title="<?= $_1_u_nf_status ?>" id="statusButton"><?= mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?> </label>
                            </span>
                        </td>
                        <td><? if (!empty($_1_u_nf_idnf)) { ?><a title="Imprimir Orçamento" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/nf.php?_acao=u&idnf=<?= $_1_u_nf_idnf ?>')"></a><? } ?></td>
                    </tr>
                </table>
            </div>
            <div class="panel-body">
                <? if (!empty($_1_u_nf_idnf)) {
                    if (!empty($_1_u_nf_idobjetosolipor) and $_1_u_nf_tipoobjetosolipor == 'nf') {
                        $tiponfc = traduzid('nf', 'idnf', 'tiponf', $_1_u_nf_idobjetosolipor);
                        $nnfec = traduzid('nf', 'idnf', 'nnfe', $_1_u_nf_idobjetosolipor);
                        if ($tiponfc == 'V') {
                            $modc = "pedido";
                        } else {
                            $modc = "nfentrada";
                        }
                    ?>
                        <tr>
                            <td align="right">Origem:</td>
                            <td><a class="hoverazul pointer" title="Pedido" onclick="janelamodal('?_modulo=<?= $modc ?>&_acao=u&idnf=<?= $_1_u_nf_idobjetosolipor ?>')"><?= $_1_u_nf_idobjetosolipor ?></a></td>
                        </tr>
                    <? }

                    $arrnfvinc = PedidoController::listarPedidoVinculado($_1_u_nf_idnf);
                    $gerouentrada='N';
                    if (count($arrnfvinc) > 0) {
                    ?>
                        <tr>
                            <td align="right">Notas Relacionadas:</td>
                            <td colspan="10">
                                <? foreach ($arrnfvinc as $idnf => $rowm) {
                                    if ($rowm['tiponf'] == 'V' and $rowm['natoptipo']=='devolucao') {
                                        $modc = "pedidodevolucao";
                                    }elseif ($rowm['tiponf'] == 'V' ) {
                                        $modc = "pedido";
                                    }  else {
                                        $modc = "nfentrada";
                                        $gerouentrada='Y';
                                    }
                                ?>
                                    <a class="hoverazul pointer" title="Pedido" onclick="janelamodal('?_modulo=<?= $modc ?>&_acao=u&idnf=<?=$idnf?>')"><?= $rowm["nnfe"] ?></a>
                                <? } ?>
                            </td>
                        </tr>
                    <? } //if($_1_u_nf_status!='INICIO'){
                    ?>
                        <table><?
                            $idtipopessoa = traduzid("pessoa", "idpessoa", "idtipopessoa", $_1_u_nf_idpessoa);
                            if ($idtipopessoa != 1) {
                            ?>
                            
                            <tr>
                                <td align="right">A/C:</td>
                                <td class="nowrap">
                                    <select <?= $disablednf ?> name="_1_<?= $_acao ?>_nf_idcontato" id="idcontato">
                                        <option value=""></option>
                                        <? fillselect(PedidoController::buscarContatoPessoa($_1_u_nf_idpessoa), $_1_u_nf_idcontato); ?>
                                    </select>
                                </td>
                                <td>
                                    <? if ($_1_u_nf_idcontato) { ?>
                                        <a id="cadcontatos" class="fa fa-bars pointer hoverazul" title="Cadastro de  Contatos" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $_1_u_nf_idcontato ?>')"></a>
                                    <? } elseif($_1_u_nf_status != 'CONCLUIDO' && $_1_u_nf_status != 'CANCELADO') { ?>
                                        <a class="fa fa-plus-circle verde pointer hoverazul" title="Cadastro de  Pessoas" onclick="janelamodal('?_modulo=pessoa&_acao=i')"></a>
                                    <? } ?>
                                    <?
                                    //representante
                                    if ($_1_u_nf_idpessoa) {
                                        if (!empty($_1_u_nf_idvendedor) and $_1_u_nf_status == 'CONCLUIDO') {
                                             $nomevendedor=traduzid('pessoa', 'idpessoa', 'nome', $_1_u_nf_idvendedor);
                                    ?>
                                <td align="right">Responsável:</td>
                                <td colspan="6">
                                    <input name="_1_<?= $_acao ?>_nf_idvendedor" type="hidden" value="<?= $_1_u_nf_idvendedor ?>">

                                    <a class="pointer hoverazul" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $_1_u_nf_idvendedor ?>')" style="padding-left: 10px;">
                                        <label class="alert-warning">
                                            <font color="Blue" style="font-weight: bold;"><?= $nomevendedor?></font>
                                        </label>
                                    </a>
                                </td>
                            <?

                                        } else {
                            ?>

                                <td align="right">Responsável:</td>
                                <td colspan="6">
                                    <select class="size20" name="_1_<?= $_acao ?>_nf_idvendedor">
                                        <option value=""></option>
                                        <? fillselect(PedidoController::buscarResponavelCliente($_1_u_nf_idpessoa), $_1_u_nf_idvendedor); ?>
                                    </select>
                                    <?
                                            if (!empty($_1_u_nf_idvendedor)) {  
                                    ?>
                                        <a class="fa fa-bars pointer hoverazul" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $_1_u_nf_idvendedor ?>')"></a>
                                    <?
                                            }
                                    ?>
                                </td>
                        <?
                                        }
                                    } // if($_1_u_nf_idpessoa){
                        ?>
                        </td>
                            </tr>
                            <tr>
                                <td align="right">Email:</td>
                                <td nowrap>
                                    <textarea <?= $disablednf ?> class="caixa" style="width: 300px; height: 25px; font-size:medium" name="_1_<?= $_acao ?>_nf_emailorc"><?= $_1_u_nf_emailorc ?></textarea>
                                    <? 
                                    $resempresaemail =PedidoController::buscarEmailOrcamentoProduto($_1_u_nf_idempresa);
                                    $qtdempresaemail = count($resempresaemail);
                                    if ($qtdempresaemail == 1) {
                                        $nemails = 1;
                                    } else {
                                        if ($qtdempresaemail > 1) {
                                            $nemails = 2;
                                        } else {
                                            $nemails = 0;
                                        }
                                    }

                                   
                                    $rowemailobj =PedidoController::buscarEmailOrcamentoProdutoPorNf($_1_u_nf_idempresa,$_1_u_nf_idnf);
                                    $qtdemailobj = count($rowemailobj);

                                    if ($qtdemailobj < 1) {
                                        $setemail = 1;
                                    } else {
                                        $setemail = 0;
                                    }

                                    $existepv = strpos($_1_u_nf_emailorc, ";");
                                    if ($existepv === false) {
                                        null;
                                    } else {
                                        echo "<br><font color='red'>Atenção: Utilizar Vírgula para separar Emails!</font></br>";
                                    }

                                    if ($_1_u_nf_envioemailorc == 'Y') {
                                        $classtdemail = "amarelo";
                                        $emailval = 'N';
                                    } elseif ($_1_u_nf_envioemailorc == 'O') {
                                        $classtdemail = "verde";
                                        $emailval = 'N';
                                    } elseif ($_1_u_nf_envioemailorc == 'E') {
                                        $classtdemail = "vermelho";
                                        $emailval = 'N';
                                    } else {
                                        $classtdemail = "cinza";
                                        $emailval = 'Y';
                                    }

                                 
                                    $rowemail =PedidoController::buscarEmailfilaPorNf($_1_u_nf_idempresa,$_1_u_nf_idnf);                                                                       
                                    $numemail = count($rowemail);
                                    if ($numemail > 0) { ?>
                                <td>
                                    <a class="pull-right" title="Ver emails enviados" onclick="janelamodal('?_modulo=envioemail&_acao=u&idmailfila=<?= $rowemail['idmailfila'] ?>')"><i class="fa fa-envelope-o cinza pointer"></i><i style="z-index: 2300;margin-left:-5px;margin-top:-7px;" class="fa fa-search cinza cinza pointer"></i></a>
                                </td>
                            <? } ?>
                            </td>
                            <td>
                                <input id="setemail" type="hidden" value="<?= $setemail ?>">
                                <a id="envemail" class="fa fa-envelope pointer <?= $classtdemail ?>" title="Enviar email orçamento" onclick="envioemailorc(<?= $_1_u_nf_idnf ?>,'<?= $emailval ?>',<?= $nemails ?>);"></a>
                            </td>
                            <td>
                                <? if (!empty($_1_u_nf_idnf)) {
                                  
                                    $reseo=PedidoController::buscarlog($_1_u_nf_idnf, 'nf','EMAILORCPROD');

                                    $qtdeo = count($reseo);
                                    if ($qtdeo > 0) { ?>
                                        <div class="oEmailorc">
                                            <a class="fa fa-search azul pointer hoverazul" title=" Ver Log Email" data-target="webuiPopover0"></a>
                                        </div>
                                        <div class="webui-popover-content">
                                            <? foreach($reseo as $roweo){
                                                ?>
                                                <li><?= $roweo["log"] ?> <?= $roweo["status"] ?> <?= dmahms($roweo["criadoem"]) ?></li>
                                            <? } ?>
                                        </div>
                                <? }
                                } ?>
                            </td>
                            <td>
                                <table>
                                    <? if ($nemails == 1) {
                                        $resdominio =PedidoController::buscarDominio($_1_u_nf_idempresa, 'ORCPROD');
                                        $qtddominio = count($resdominio);
                                        if ($qtddominio > 0) {
                                            foreach($resdominio as $rowdominio){?>
                                                <tr>
                                                    <td>
                                                        <input id="emailunico" type="hidden" value="<?= $rowdominio["idemailvirtualconf"] ?>">
                                                        <input id="idempresaemail" type="hidden" value="<?= $rowdominio["idempresa"] ?>">

                                                    </td>
                                                </tr>
                                                <? }
                                        }
                                    } else {
                                        if ($nemails > 1) {
                                            $resdominio =PedidoController::buscarDominio($_1_u_nf_idempresa, 'NFP');
                                            $qtddominio = count($resdominio);
                                            if ($qtddominio > 0) {
                                                foreach($resdominio as $rowdominio){
                                                    if ($rowdominio["idemailvirtualconf"] == $rowemailobj["idemailvirtualconf"]) {
                                                        $chk = 'checked';
                                                    } else {
                                                        $chk = '';
                                                    } ?>
                                                    <tr>
                                                        <td>
                                                            <input id="emailorcamento" title="Email Remetente" type="radio" <?= $chk ?> <?=$disablednfStatus?> <?=$readonlynfStatus?> onclick="altremetenteemail(<?= $_1_u_nf_idnf ?>,<?= $rowdominio["idemailvirtualconf"] ?>,'ORCPROD',<?= $_1_u_nf_idempresa ?>)">
                                                            <label class="alert-warning"><?= $rowdominio["dominio"] ?> </label>
                                                        </td>
                                                    </tr>
                                            <? }
                                            }
                                        } else { ?>
                                            <tr>
                                                <td>
                                                    <label class="alert-danger">Não há email configurado para essa empresa</label>
                                                </td>
                                            </tr>
                                    <? }
                                    } ?>
                                </table>
                            </td>
                            </tr>

                        </table>
                    <? } //if($idtipopessoa!=1){
                 } //if(!empty($_1_u_nf_idnf)){
                ?>
            </div>
        </div>
    </div>
</div>

<? if (!empty($_1_u_nf_idnf)) { ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Dados Faturamento/Entrega</div>
                <div class="panel-body ">
                    <div class="col-md-6">
                        <table>

                            <tr>
                                <td align="right"> <b>O.C. Cliente:</b></td>
                                <td>
                                    <input <?= $disablednf ?> name="_1_<?= $_acao ?>_nf_nitemped" style="width: 100px;" id="pedidoext" type="text" value="<?= $_1_u_nf_nitemped ?>" vnulo style="display: <?= $strdados ?>">
                                </td>
                            </tr>
                            <?                         
                            if ((empty($_1_u_nf_nnfe) || !empty($_1_u_nf_idempresafat)) && !in_array($_1_u_nf_status, ['INICIO','ORCAMENTO', 'SOLICITADO', 'PENDENTE'])) { ?>
                                <tr>
                                    <td align="right"><b>Filial:</b></td>
                                    <td>
                                        <? if (!empty($_1_u_nf_nnfe)) { ?>
                                            <input name="_1_<?= $_acao ?>_nf_idempresafat" type="hidden" value="<?= $_1_u_nf_idempresafat ?>">
                                            <label class="alert-warning"><?= traduzid('empresa', 'idempresa', 'nomefantasia', $_1_u_nf_idempresafat); ?></label>
                                        <? } else {
                                            if(count($transferencia)>0){
                                            ?>
                                             <select name="_1_<?= $_acao ?>_nf_idempresafat" vnulo onchange="setafilial(this)">
                                                <option value=""></option>
                                                <? fillselect(PedidoController::buscarFilial(), $_1_u_nf_idempresafat); ?>
                                            </select>
                                            <?
                                            }else{
                                                ?>
                                                <select name="_1_<?= $_acao ?>_nf_idempresafat" onchange="setafilial(this)">
                                                    <option value=""></option>
                                                    <? fillselect(PedidoController::buscarEmpresaFilial($_1_u_nf_idpessoafat), $_1_u_nf_idempresafat); ?>
                                                </select>
                                            <?
                                            }
                                            
                                           } ?>
                                    </td>
                                    <td>
                                        <?
                                        if($gerouentrada == 'N'){
                                            if($_1_u_nf_envionfe == "CONCLUIDA" || $_1_u_nf_envionfe == "CANCELADA"){
                                                $travacursor = 'pointer';
                                                $onclick = 'novacompra()';
                                            } else {
                                                $travacursor = 'not-allowed';
                                                $onclick = '';
                                            }

                                            if (!empty($_1_u_nf_idempresafat) && count($transferencia) > 0) { ?>
                                                <i id="Copiaritens" class="fa fa-chain-broken fa-1x azul pointer <?=$travacursor?> pdlf10" onclick="<?=$onclick?>"  title="Gerar Entrada Empresa/Filial"></i>
                                                <?
                                             }elseif(!empty($_1_u_nf_idempresafat)){?>
                                                <i id="Copiaritens" class="fa fa-clone fa-1x azul" onclick="novopedido()" title="Gerar Nota de Transferência Empresa/Filial" style="padding-left: 10px;"></i>
                                                <? 
                                            } 
                                        }
                                        if(!empty($_1_u_nf_idempresafat) && $_1_u_nf_envionfe != 'CONCLUIDA'){
                                            ?>
                                            <i id="limparfilial" class="fa fa-eraser fa-1x azul pointer pdlf10" onclick="limparFilial()" title="Limpar Filial"></i>
                                            <?
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <? } ?>
                            <tr>
                                <td align="right"><b>Cliente:</b></td>
                                <? if ($_1_u_nf_status == 'CONCLUIDO' or $_1_u_nf_status == 'CANCELADO') { ?>
                                    <td class="nowrap">
                                        <input name="_1_<?= $_acao ?>_nf_idpessoafat" type="hidden" value="<?= $_1_u_nf_idpessoafat ?>">
                                        <? echo traduzid("pessoa", "idpessoa", "nome", $_1_u_nf_idpessoafat); ?>
                                        <a class="fa fa-bars pointer hoverazul" title="Cadastro de  Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $_1_u_nf_idpessoafat ?>')" style="padding-left: 5px;"></a>
                                    </td>
                                <? } else { ?>
                                    <td class="nowrap">
                                        <? if (empty($_1_u_nf_idpessoafat)) {
                                            $dfat = "";
                                        } else {
                                            $dfat = "disabled='disabled'";
                                        } ?>
                                        <input class="desabilitado" <?= $dfat ?> type="text" name="_1_<?= $_acao ?>_nf_idpessoafat" vnulo cbvalue="<?= $_1_u_nf_idpessoafat ?>" value="<?= $arrCli[$_1_u_nf_idpessoafat]["nome"] ?>" style="width: 35em; font-size: 11px; padding-left: 5px;" onchange="atualizaclientefat(this,<?= $_1_u_nf_idnf ?>);" vnulo>
                                        <? if (!empty($_1_u_nf_idpessoafat)) { ?>
                                            <a class="fa fa-bars pointer hoverazul" title="Cadastro de  Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $_1_u_nf_idpessoafat ?>')" style="padding-left: 5px;"></a>
                                        <? } ?>
                                    </td>
                                    <td class="nowrap">
                                        <a id="altcliente" class="fa fa-pencil hoverazul btn-lg pointer" onclick="editarclientefat(this);" title="Alterar cliente de faturamento."></a>
                                    </td>
                                <? } ?>
                            </tr>
                            <tr>
                                <td align="right"><b>Endereço:</b></td>
                                <td nowrap>
                                    <? if (!empty($_1_u_nf_idenderecofat)) { ?>
                                        <select class="desabilitado" disabled="disabled" style="font-size:  11px;" name="_1_<?= $_acao ?>_nf_idenderecofat" id="idenderecofat" onchange="CB.post()" vnulo>
                                            <? fillselect(PedidoController::buscarEnderecoFaturamentoPorPessoa($_1_u_nf_idpessoafat),$_1_u_nf_idenderecofat); ?>
                                        </select>
                                    <? } elseif (empty($_1_u_nf_idenderecofat) and !empty($_1_u_nf_idpessoafat)) { ?>
                                        <select style="font-size:  11px;" name="_1_<?= $_acao ?>_nf_idenderecofat" id="idenderecofat" vnulo>
                                            <? fillselect(PedidoController::buscarEnderecoFaturamentoPorPessoa($_1_u_nf_idpessoafat), $_1_u_nf_idenderecofat); ?>
                                        </select>
                                    <? } ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="nowrap" align="right"><b>Razão Social:</b></td>
                                <td><?= traduzid("pessoa", "idpessoa", "razaosocial", $_1_u_nf_idpessoafat) ?></td>
                            </tr>
                            <tr>
                                <td><b>CPF/CNPJ:</b></td>
                                <td>
                                    <? if (!empty($_1_u_nf_idpessoafat)) {                                       
                                        $rowic = PedidoController::buscarPessoa($_1_u_nf_idpessoafat);
                                        $cnpj = formatarCPF_CNPJ($rowic['cpfcnpj'], true);
                                        if (!empty($cnpj)) { ?>
                                            <span style="color: red;"><b><?= $cnpj ?></b></span>
                                            <? if (!empty($rowic['inscrest'])) { ?> / IE:<span style="color: red;"><b><? } ?><? if (!empty($rowic['inscrest'])) { ?> <?= $rowic['inscrest'] ?><? } ?></b></span>
                                        <? }
                                    } ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top" align="right"><b>Endereço:</b></td>
                                <td>
                                    <? if (!empty($_1_u_nf_idpessoafat)) {
                                      
                                        $resf = PedidoController::listarEnderecoFaturamentoPorPessoa($_1_u_nf_idpessoafat);
                                        foreach($resf as $rowf){
                                            $cep = formatarCEP($rowf["cep"], true); ?>
                                            <li style="display:inline-block;">
                                                <div class=""><?= $rowf["logradouro"] ?> <?= $rowf["endereco"] ?> N.: <?= $rowf["numero"] ?> <?= $rowf["complemento"] ?></div>
                                                <div class="nowrap">Bairro: <?= $rowf["bairro"] ?> CEP: <?= $cep ?> </div>
                                                <div class="nowrap">Cidade: <?= $rowf["cidade"] ?> UF: <?= $rowf["uf"] ?></div>
                                            </li>
                                    <? }
                                    } ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table>
                            <tr>
                                <td align="right"><b>Endereço Entrega:</b></td>
                                <td nowrap>
                                    <?
                                    $dfaEntrega = (empty($_1_u_nf_idendrotulo)) ? "" : "disabled='disabled'";

                                    if(!empty($_1_u_nf_idpessoafat)){
                                        $stridpessoa = $_1_u_nf_idpessoa.",".$_1_u_nf_idpessoafat;
                                    }else{
                                        $stridpessoa = $_1_u_nf_idpessoa;
                                    }                                       

                                    if (!empty($_1_u_nf_idendrotulo)) { ?>
                                        <select <?= $disabled ?> <?= $disablednf ?> <?=$dfaEntrega?> class="size40" name="_1_<?= $_acao ?>_nf_idendrotulo" class="idendrotulo" vnulo>
                                            <option value=""></option>
                                            <? fillselect(PedidoController::listarEnderecoPessoaPorTipo($stridpessoa,'2,3'), $_1_u_nf_idendrotulo); ?>
                                        </select>
                                    <? } elseif (empty($_1_u_nf_idendrotulo) and !empty($_1_u_nf_idpessoa)) { ?>
                                        <select <?= $disabled ?> <?= $disablednf ?> <?=$dfaEntrega?> class="size25" name="_1_<?= $_acao ?>_nf_idendrotulo" class="idendrotulo" vnulo>
                                            <option value=""></option>
                                            <? fillselect(PedidoController::listarEnderecoPessoaPorTipo($stridpessoa,'2,3'), $_1_u_nf_idendrotulo); ?>
                                        </select>
                                    <? } ?>
                                </td>
                                <td>
                                    <? if (!empty($_1_u_nf_idendrotulo)) { ?>                                        
                                        <a id="endereco" class="fa fa-bars pointer hoverazul" title="Endereço" onclick="janelamodal('?_modulo=endereco&_acao=u&idendereco=<?= $_1_u_nf_idendrotulo ?>')"></a>
                                        <? if($_1_u_nf_status != 'CONCLUIDO' && $_1_u_nf_status != 'CANCELADO') {  ?>
                                            <a id="altcliente" class="fa fa-pencil hoverazul btn-lg pointer" onclick="editarclienteEntrega('idendrotulo', '<?=$_1_u_nf_idendrotulo?>', 'modulohistorico', <?=$_1_u_nf_idnf ?>, 'Endereço Entrega:', 'idendrotulo');" title="Alterar cliente de faturamento."></a>
                                            <? 
                                        }

                                        $listaHistoricoDescr = ProdServController::buscarHistoricoDeAlteração($_1_u_nf_idnf, 'pedido', 'idendrotulo');
                                        $qtdhist = count($listaHistoricoDescr);

                                        if ($qtdhist > 0) { ?>
                                            <div id="hist_end_entrega" style="display: none">
                                                <table class="table table-hover">
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
                                                        <? foreach ($listaHistoricoDescr as $historico) { ?>
                                                            <tr>
                                                                <td style="padding: 3px !important;"><?=PedidoController::buscarEnderecoPorIdEndereco($historico['valor_old']); ?></td>
                                                                <td style="padding: 3px	!important;"><?=PedidoController::buscarEnderecoPorIdEndereco($historico['valor']) ?></td>
                                                                <td style="padding: 3px !important;"><?=$historico['justificativa']; ?></td>
                                                                <td style="padding: 3px !important;"><?=$historico['nomecurto'] ?></td>
                                                                <td style="padding: 3px !important;"><?= dmahms($historico['criadoem']) ?></td>
                                                            </tr>
                                                        <?
                                                        } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <img src="/form/img/icon-hist.svg" class="pointer hoverazul" alt="Icone histórico" onclick="modalhist('hist_end_entrega')">
                                            <?
                                        }
                                    } 
                                    ?>
                                </td>
                                <td><a style="display:none" id="cadendereco" class="fa fa-plus-circle verde pointer hoverazul" title="Cadastro de  Endereço" onclick="janelamodal('?_modulo=endereco&_acao=i&idpessoa=<?= $_1_u_nf_idpessoa ?>')"></a></td>
                            </tr>
                            <? if (!empty($_1_u_nf_idendrotulo)) { ?>
                                <tr>
                                    <td style="vertical-align: top" align="right"><b>Endereço:</b></td>
                                    <td>
                                        <?
                                        $resf = PedidoController::listarEnderecoFaturamentoPorId($_1_u_nf_idendrotulo);
                                        foreach($resf as $rowf){
                                            $localizacao = $rowf["localizacao"];
                                            $cep = formatarCEP($rowf["cep"], true); ?>
                                            <li style="display:inline-block;">
                                                <div class=""><?= $rowf["logradouro"] ?> <?= $rowf["endereco"] ?> N.: <?= $rowf["numero"] ?> <?= $rowf["complemento"] ?></div>
                                                <div class="nowrap">Bairro: <?= $rowf["bairro"] ?> CEP: <?= $cep ?> </div>
                                                <div class="nowrap">Cidade: <?= $rowf["cidade"] ?> UF: <?= $rowf["uf"] ?></div>
                                            </li>
                                        <? } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><b>Rota:</b></td>
                                    <td nowrap>
                                        <? if (empty($localizacao)) { ?>
                                            <i>Não informada</i>
                                        <? } else {
                                            if (filter_var($localizacao, FILTER_VALIDATE_URL)) { ?>
                                            <a href="<?= $localizacao ?>" target="_blank">Como chegar</a>
                                        <? } else {
                                            echo  $localizacao;
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <? } ?>
                        </table>
                    <? } ?>
                    <table>
                        <? if (!empty($_1_u_nf_idpessoa)) {
                    
                            $rowo = PedidoController::buscarPreferenciaCliente($_1_u_nf_idpessoa);                            
                            $observacaonfp = $rowo["observacaonfp"]; //traduzid("pessoa","idpessoa","observacaonfp",$_1_u_nf_idpessoa);
                            if (!empty($observacaonfp)) { ?>
                                <tr>
                                    <td><b>Observação:</b></td>
                                </tr>
                                <tr>
                                    <td><span style="color: red;"><b><?= str_replace(chr(13), "<br>", $observacaonfp) ?></b></span></td>
                                </tr>
                        <?
                            } else {
                                echo ("<tr><td></td><td></td></tr>");
                            }
                        } else {
                            echo ("<tr><td></td><td></td></tr>");
                        } ?>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <? if ($_acao == 'u' and !empty($_1_u_nf_idenderecofat)) {
            listaitens();

            if ($icmsufdest_ufremet == "Y" and $_1_u_nf_status != 'INICIO' and $_1_u_nf_status != 'SOLICITADO' and $_1_u_nf_status != 'PENDENTE' and $_1_u_nf_status != 'PEDIDO' and $_1_u_nf_status != 'EXPEDICAO' and $_1_u_nf_status != 'PRODUCAO' and !empty($vicmsufdest)) {
                 
                $rowg = PedidoController::buscarNfitemGnre($_1_u_nf_idnf);                  
                $_qtdgnre = count($rowg);
             
                if ($_qtdgnre > 0) {
                    $_acaognre = 'u';
                } else {
                    $_acaognre = 'i';
                }
                $i = $i + 1;
                $rowg['vlritem'] = $vicmsufdest;
                $rowg['total'] = $vicmsufdest;
                $rowg['qtd'] = 1;
                if (empty($rowg["obs"])) {
                    $rowg["obs"] = "GNRE";
                }
                if (empty($rowg["idtipoprodserv"]) or empty($rowg["idcontaitem"])) {
                    $arrconfCP = getDadosConfContapagar('GNRE');
                }
                if (empty($rowg["idtipoprodserv"])) {
                    $rowg["idtipoprodserv"] = $arrconfCP['idtipoprodserv'];
                }
                if (empty($rowg["idcontaitem"])) {
                    $rowg["idcontaitem"] = $arrconfCP['idcontaitem'];
                } ?>
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12 ">
                                    <table>
                                        <tr>
                                            <td></td>
                                            <td>
                                                <? echo ("*ICMS-DIFAL EC 87/2015: Partilha Destinatário (<font color='red'>R$" . number_format($vicmsufdest, 2, '.', '') . "</font>).");
                                                $_1_u_nf_obspartilha = "*ICMS-DIFAL EC 87/2015:  Partilha Destinatário (R$" . number_format($vicmsufdest, 2, '.', '') . ")."; ?>
                                                <input name="gnreval" size="20" type="hidden" value="<?= number_format($vicmsufdest, 2, '.', '') ?>">
                                                <input name="_1_<?= $_acao ?>_nf_obspartilha" size="20" type="hidden" value="<?= $_1_u_nf_obspartilha ?>">
                                            </td>
                                        </tr>
                                    </table>

                                    <table class="table table-striped planilha">
                                        <tr>
                                            <td style="text-align: center;">Código GNRE</td>

                                            <td colspan="5">
                                                <input name="nf_gnre" class="size40" type="text" value="<?= $_1_u_nf_gnre ?>" onchange="alteragnre(this)">
                                            </td>
                                        </tr>                                       
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <? } elseif ($icmsufdest_ufremet == "Y") {
                $_1_u_nf_obspartilha = "*ICMS-DIFAL EC 87/2015: Partilha Destinatário (R$" . number_format($vicmsufdest, 2, '.', '') . ").";
            ?>
                <input name="_1_<?= $_acao ?>_nf_obspartilha" size="20" type="hidden" value="<?= $_1_u_nf_obspartilha ?>">
        <?
            }
        } ?>
    </div>
<? } ?>



<? if (!empty($_1_u_nf_idnf)) {
    //desenhar informações de notafiscal envio e rotulo
    rodape();
    ###################### 
?>
<div class="row w-100">
    <div class="panel panel-default">
        <div class="panel-heading" data-toggle="collapse" href="#comprovante">Comprovante de Entrega</div>
        <div class="panel-body" id="comprovante" style="padding-top: 8px !important;">
            <div class="cbupload" id="cbupload-custom" title="Clique ou arraste arquivos para cá" style="width:100%;">
                <i class="fa fa-cloud-upload fonte18"></i>
            </div>
        </div>
    </div>
</div>
    <p>     

        <? if (!empty($_1_u_nf_idnf)) { // trocar p/ cada tela a tabela e o id da tabela
            $_idModuloParaAssinatura = $_1_u_nf_idnf; // trocar p/ cada tela o id da tabela
            require 'viewAssinaturas.php';
        }
        $tabaud = "nf"; //pegar a tabela do criado/alterado em antigo
        $idRefDefaultDropzone = "cbupload"; //pegar o id do dropzone do criado/alterado em antigo
        require 'viewCriadoAlterado.php'; ?>
    <? } ?>


    <?
    //listar os item da NF-Pedido
    function listaitens()
    {
        global $_1_u_nf_idnf, $prodservclass,$nf_idempresa, $_1_u_nf_nnfe,$_1_u_nf_idempresa, $_1_u_nf_envionfe, $_1_u_nf_idendereco, $_1_u_nf_idenderecofat, $_1_u_nf_idpessoa, $_1_u_nf_idnfe, $i, $arrnatop, $_acao, $icmsufdest_ufremet, $vsubtotal, $vdesconto, $vtotal, $vtotalicms, $vtotaldeson, $vtotalbc, $vtotalipi, $vtotaldes, $vtotalii, $vicmsufdest, $vicmsufremet, $frdesoneracao, $_1_u_nf_geracontapagar, $_1_u_nf_refnfe, $_1_u_nf_criadopor, $_1_u_nf_frete, $_1_u_nf_modfrete, $_1_u_nf_moeda, $_1_u_nf_totalext, $_1_u_nf_taxacv, $_1_u_nf_envionfe, $pedidopend, $_1_u_nf_comissao, $uf, $comissionado, $_1_u_nf_criadopor, $_1_u_nf_status, $_1_u_nf_finnfe, $_1_u_nf_tpnf, $_1_u_nf_idnatop, $_1_u_nf_idenderecofat, $prodservclass, $_1_u_nf_obspedido, $nfclass, $mensagem,$_1_u_nf_idempresafat;

        if ($_1_u_nf_envionfe == 'CONCLUIDA') {
            $disablednf = "disabled='disabled'";
            $readonlynf = "readonly='readonly'";
        } else {
            $disablednf = "";
            $readonlynf = "";
        }

        //buscar uf para caso de divisao de ICMS
        $uf = traduzid("endereco", "idendereco", "uf", $_1_u_nf_idenderecofat);

        $qr = PedidoController::buscarNfitemPedido($_1_u_nf_idnf);   
        $i = 10;

        $qtdrows = count($qr); 
        $lcfop = PedidoController::buscarListaCFOPporNatop($_1_u_nf_idnatop);
        ?>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <table>
                        <tr>
                            <td>Tipo:</td>
                            <td>
                                <select <?= $disablednf ?> name="_1_<?= $_acao ?>_nf_tpnf">
                                    <? fillselect(PedidoController::buscarTpnf(), $_1_u_nf_tpnf); ?>
                                </select>
                            </td>
                            <td nowrap align="right">Nat:</td>
                            <td>
                                <input <? if ($_1_u_nf_status != 'INICIO' and $_1_u_nf_status != 'SOLICITADO') { ?>readonly='readonly' <? } ?> id="idnatop" type="text" name="_1_<?= $_acao ?>_nf_idnatop" cbvalue="<?= $_1_u_nf_idnatop ?>" value="<?= $arrnatop[$_1_u_nf_idnatop]["natop"] ?>" style="width: 45em;" vnulo>
                            </td><?
                                    if ($_1_u_nf_status == 'INICIO' or $_1_u_nf_status == 'SOLICITADO' or $_1_u_nf_status == 'PENDENTE' or $_1_u_nf_status == 'PEDIDO' or $_1_u_nf_status == 'PRODUCAO' or $_1_u_nf_status == 'EXPEDICAO' or $_1_u_nf_status == 'FATURAR') { ?>
                                <td>
                                    <a id="divenda" class="fa fa-clone  pointer hoverazul" title="Dividir Venda" onclick="duplicarvenda(<?= $_1_u_nf_idnf ?>)"></a>
                                </td>
                            <? } ?>
                            <td></td>
                            <td id="_certificadodeanalise_" style="width:100%"></td>
                            <td align="right" nowrap>Moeda:</td>
                            <td>
                                <select name="_1_<?= $_acao ?>_nf_moeda">
                                    <? fillselect(PedidoController::buscarMoeda(), $_1_u_nf_moeda); ?>
                                </select>
                            </td>

                            <? //if ($_1_u_nf_status != 'INICIO'){
                            if ($_1_u_nf_moeda != 'REAL') {
                                $taxacv = $_1_u_nf_taxacv; ?>
                                <td class="nowrap" title="Taxa de Conversão">Tx Cv:</td>
                                <td>
                                    <input vnulo style="width: 50px;" title="Câmbio BRL" placeholder="Câmbio" name="_1_u_nf_taxacv" onchange="convertemoeda(this,<?= $_1_u_nf_idnf ?>)" type="text" step="0.05" min="0.01" value="<?= $taxacv = str_replace(",", ".", $taxacv); ?>">
                                </td>
                            <? } //}
                            ?>
                        </tr>
                        <? if (!empty($_1_u_nf_idnatop)) {
                            $finnfe = traduzid('natop', 'idnatop', 'finnfe', $_1_u_nf_idnatop);
                            //se for devolucao referencia nota de devolucao
                            if ($finnfe == 4 or $finnfe == 2) { ?>
                                <td colspan="3" align="right" nowrap>Chave de acesso NF Devolução/Complementar:</td>
                                <td colspan="4">
                                    <input name="_1_<?= $_acao ?>_nf_refnfe" size="45" type="text" value="<?= $_1_u_nf_refnfe ?>"><br>
                                </td>
                                <? if (!empty($_1_u_nf_refnfe)) {
                                
                                    $rowdev=PedidoController::buscarNfentradaPorIdnfe($_1_u_nf_refnfe);
                                    $qtddev1=count($rowdev);
                                    if (!empty($qtddev1)) {
                                    ?>
                                        <td>
                                            <a class="fa fa-bars pointer hoverazul" title="Compra" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?= $rowdev['idnf'] ?>')"></a>
                                        </td>

                        <? }
                                }
                            }
                        } ?>
                        <tr>
                            <? if (!empty($_1_u_nf_idnfe)) {
                            
                            
                                $rowdev=PedidoController::buscarNfdevolucaoPoridnfe(substr($_1_u_nf_idnfe, 3));
                                $qtddev = count($rowdev);
                                if ($qtddev > 0) {
                                
                                    $valordevolucao = $rowdev['total']; ?>
                                    <td colspan="10">
                                        <label class="alert-warning">NF possui devolução:<?= $rowdev['nnfe'] ?></label>
                                        <a class="fa fa-bars pointer hoverazul" title="Nota de Devolução" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?= $rowdev['idnf'] ?>')"></a>
                                    </td>

                            <? }
                            }
                            ?>
                        </tr>
                    </table>
                </div>
                <div class="panel-body">
                    <table id="tbItens" class="table table-striped">
                        <tbody>
                            <?
                            $resix = PedidoController::buscarNfitemMoedaEstrangeira($_1_u_nf_idnf);
                            $qtdvlext = count($resix); ?>
                            <tr class="cabitem">
                                <th></th>
                                <th style="text-align: center;"><small>NFe</small></th>
                                <th style="text-align: center;">                                    
                                    <div style="display: flex;align-items: center;height: 17px;gap: 2px;">
                                        <small style="margin-right:5px;">Cert</small>                                                                            
                                        <input title="Certificado" class="m-0 pointer chekTodos" type="checkbox" onclick="marcarTodosCertificados('Y')" style="height: 10px;">
                                        <i class="fa fa-warning laranja pointer certificado_todos" onclick="gerarCertificadoTodosLotes()" style="margin-left:5px; height: 10px; display:none;" title="Gerar Certificado de Análise de Todos Lotes?"></i>
                                    </div>
                                </th>
                                <th colspan="2" style="text-align: center;"><small title=" Quantidade">Qtd</small></th>
                                <th style="text-align: center;"><small>Descrição</small></th>
                                <th style="text-align: center;"><small>Fórmula</small></th>
                                <? if ($_1_u_nf_moeda == 'REAL') { ?>
                                    <th style="text-align: center;"><small>CFOP</small></th><? } ?>
                                <? if ($qtdvlext > 0 and $_1_u_nf_moeda != 'REAL') { ?>
                                    <th style="text-align: center;"><small> <?= $_1_u_nf_moeda ?></small></th><? } ?>
                                <? if ($_1_u_nf_moeda == 'REAL') { ?>
                                    <th style="text-align: center;"><small title=" Valor Liquido">Vl Liq</small></th>
                                <? } else { ?>
                                    <th style="text-align: center;"><small title=" valor unitario <?= $_1_u_nf_moeda ?>"><? echo "Vl Unit " . $_1_u_nf_moeda ?></small></th>
                                    <th style="text-align: center;"><small><? echo "Total " . $_1_u_nf_moeda ?></small></th>
                                    <th style="text-align: center;"><small title=" Valor Liquido BRL">Vl Liq BRL</small></th>
                                <? } ?>
                                <th style="text-align: center;"><small title=" Desconto">Desc</small></th>
                                <? if ($_1_u_nf_modfrete != 9) { ?>
                                    <th style="text-align: center;"><small>Frete</small></th>
                                <? } ?>
                                <? if ($_1_u_nf_moeda == 'REAL') { ?>
                                    <th style="text-align: center;"><small>Total BRL</small></th>
                                <? } else { ?>
                                    <th style="text-align: center;"><small>Total BRL</small></th>
                                <? } ?>
                                <th></th>
                                <th>
                                    <? if ($_1_u_nf_comissao == 'Y') { ?>
                                        <button type="button" class="btn btn-link btn-xs " onclick="abrecomissaonf(<?= $_1_u_nf_idnf ?>);">
                                            Comissão
                                        </button>
                                    <? } ?>
                                </th>
                                <? 
								if ($_1_u_nf_envionfe == 'CONCLUIDA') {
									$fdel = "alert('Nota fiscal já emitida não é possivel excluir.')";
								} else {
									$fdel = "removerVariasNfitem()";
								}
								
								if(empty($_1_u_nf_nnfe)){
									?>
									<th style="text-align: left;"><small><a class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer pr-3 ui-droppable excluir" title="Excluir" onclick="<?= $fdel?>"></a></small></th>
								<? } ?>
                                <th style="text-align: left;">
                                    <small><a class="fa fa-folder cinzaclaro pointer collapseFechar" title="Fechar todos" onclick="collapseiten('N')" style="display: none;"></a></small>
                                    <small><a class="fa fa-folder-open cinzaclaro pointer collapseAbrir" title="Abrir todos" onclick="collapseiten('Y')"></a></small>
                                </th>
                            </tr>
                            <? if ($qtdrows > 0) {
                                global $arrayaux;
                                $arrayaux = array();
                                $ni = 0;
                                $vtotalext = 0;


                                if ($_1_u_nf_tpnf == "0") {
                                

                                    $rs =PedidoController::buscarLoteNfitemPorIdnf($_1_u_nf_idnf);
                                    foreach($rs as $rw){                                
                                        $rows[$rw["idnfitem"]] = $rw;
                                    }
                                }
                                $voutro = 0;
                                $vseg = 0;

                                foreach($qr as $row){
                                    $ni = $ni + 1;
                                    $i = $i + 1;
                                    $cortd = "";
                                    $vdesconto = $vdesconto + ($row["des"] * $row["qtd"]);
                                    if ($row['nfe'] == "Y" and $_1_u_nf_tpnf > 0) {
                                        $vsubtotal = $vsubtotal /*+ $row["frete"] */ + (($row["vlritem"]/*+$row["des"]*/) * $row["qtd"]);
                                        $vtotal = $vtotal + $row["total"] + $row["vseg"] + $row["voutro"];
                                    } elseif ($_1_u_nf_tpnf == 0) {
                                        $vsubtotal = $vsubtotal /*+ $row["frete"] */ + (($row["vlritem"]) * $row["qtd"])/*+$row['vii']*/;
                                        $vtotal = $vtotal + $row["total"] + $row["vseg"] + $row["voutro"] + $row['vii'];
                                    }
                                    $vtotalext = $vtotalext + $row['totalext'];
                                    if ($row['nfe'] == "Y") {
                                        $vseg = $vseg + $row["vseg"];
                                        $voutro = $voutro + $row["voutro"];
                                        $vtotalii = $vtotalii + $row['vii'];
                                        $vtotalicms = $vtotalicms + $row["valicms"];
                                        $vtotaldeson = $vtotaldeson + $row["vicmsdeson"];
                                        $vtotalbc = $vtotalbc +  $row["basecalc"];
                                        $vtotalipi = $vtotalipi + $row["valipi"];
                                        $vtotaldes = $vtotaldes + ($row["des"] * $row['qtd']);
                                        $vicmsufdest = $vicmsufdest + $row['icmsufdest'];
                                        $vicmsufremet = $vicmsufremet + $row['icmsufremet'];
                                        if ($row['ipint'] == 50) {
                                            $vtotal = $vtotal + $row["valipi"];
                                        }
                                        if ($row['vIPIDevol'] > 0) {
                                            $vtotal = $vtotal + $row['vIPIDevol'];
                                        }

                                        if ($row['indiedest'] == 9 and $uf != "MG" and $uf != "EX") {
                                            $icmsufdest_ufremet = "Y";
                                        }

                                        if ($row['comissionado'] == 'Y') {
                                            $comissionado = 'Y';
                                        }
                                    }
                                    //busca o valor de produto no sistema para não aceitar um valor menor
                                    if (!empty($row['idprodserv'])) {
                                        if ($row['fabricado'] == 'Y') {
                                            if (!empty($row['idprodservformula'])) {

                                                $rowvalc =PedidoController::buscarValorContatoProdutoFomulado($_1_u_nf_idpessoa,$row['idprodserv'],$row['idprodservformula']);
                                                $qtdvalc = count($rowvalc);
                                                if ($qtdvalc > 0) {                                             
                                                    $valitemcont = $rowvalc["valor"];
                                                } else {

                                                    $rowvalc = PedidoController::buscarValorVendaFormula($row['idprodservformula']);
                                                    $valitemcont = $rowvalc["vlrvenda"];
                                                }
                                            } else {
                                                $valitemcont = '0.00';
                                            }
                                        } else {
                                        
                                            $rowvalc = PedidoController::buscarDescontoContratoPorProduto($_1_u_nf_idpessoa,$row['idprodserv']);
                                            $qtdvalc = count($rowvalc);
                                            if ($qtdvalc > 0) {
                                                $valitemcont = $rowvalc["valor"];
                                            } else {
                                                $valitemcont = $row["vlrvenda"];
                                            }
                                        }
                                    } else {
                                        $valitemcont = '0.00';
                                    }

                                    // $classDrag = ($r["status"]=="INICIO"  or $r["status"]=="AGUARDANDO")?"dragExcluir":"";
                                    $classDrag = "dragExcluir";
                                    // $disableteste = ($r["status"]=="INICIO" or $r["status"]=="AGUARDANDO")?"":"readonly='readonly'";

                                    if ($row["vicmsdeson"] > 0) {
                                        $frdesoneracao = "Y";
                                    }

                                    if ($row['criadopor'] != $_1_u_nf_criadopor) {
                                        $tdnegrito = "tdnegrito";
                                    } else {
                                        $tdnegrito = "";
                                    }

                                
                                    $rowadic = PedidoController::buscarLotePorNfitem($row['idnfitem']);
                                    
                                    $strtitle = "";
                                    $qtdd = 0;
                                
                                    if (!empty($rowadic['partida']) and !empty($rowadic['vencimento'])) {
                                        //adiciona a linha com as informações adicionais
                                        $vencimento = substr($rowadic['vencimento'], 3);
                                        $strtitle .= " Part: " . $rowadic['partida'] . "/" . $rowadic['exercicio'] . "  Venc: " . $vencimento;
                                        $qtdd = $qtdd + $rowadic["qtdd"];
                                    } else {
                                        $strtitle = " ";
                                        if ($row["nfe"] == 'Y') {
                                            $pedidopend = "1";
                                        }
                                        $qtdd = $qtdd + $rowadic["qtdd"];
                                    }

                            
                                    $rowre = PedidoController::buscarReservaLotePorNfitem($row['idnfitem']);
                            
                                    if (!empty($row["idprodservforn"])) {
                                        $rowreqtdd = $rowre['qtddconv'];
                                    } else {
                                        $rowreqtdd = $rowre['qtd'];
                                    }


                                    if ($row["qtd"]  > ($rowreqtdd)) {
                                        $classitem = 'nfitem';
                                    } elseif ($row["qtd"]  < ($rowreqtdd)) {
                                        $classitem = 'nfitemalerta';
                                    } else {
                                        $classitem = 'nfitemok';
                                    }
                                    $strtitle = $row["desclog"] . " " . $strtitle; ?>
                                    <tr class="<?= $classDrag ?> <?= $classitem ?>" idnfitem="<?= $row["idnfitem"] ?>">
                                        <!-- Ordem -->
                                        <td title="<?= $row['criadopor'] ?>" class="<?= $tdnegrito ?>">
                                            <strong>
                                                <a href="?_modulo=gerenciaprodcorponovo&_acao=u&validacao=T&idpessoa=<?= $_1_u_nf_idpessoa ?>&status=ATIVO&_idempresa=<?=$_GET['_idempresa']?>" target="_blank" style="color: inherit;">
                                                    <?= $ni ?>
                                                </a>
                                            </strong>
                                            <input type="hidden" name="_<?= $i ?>_u_nfitem_ord" value="<?= $row["ord"] ?>">
                                            <input type="hidden" name="_<?= $i ?>_u_nfitem_manual" value="<?= $row["manual"] ?>">
                                            <input name="_<?= $i ?>_u_nfitem_idnfitem" type="hidden" value="<?= $row["idnfitem"]; ?>" readonly='readonly'>
                                            <input name="_<?= $i ?>_u_nfitem_tiponf" type="hidden" value="V">
                                        </td>
                                        <!-- NFE -->
                                        <?
                                        //if($_1_u_nf_status == 'FATURAR' or $_1_u_nf_status== 'ENVIAR' or $_1_u_nf_status=='ENVIADO' or $_1_u_nf_status== 'CONCLUIDO' ){
                                        if ($row['venda'] == "Y" || $row['venda'] == "" || $row['nfe'] == 'Y') { ?>
                                            <td align="center">
                                                <? if ($row["nfe"] == 'Y') {
                                                    $checked = 'checked';
                                                    $vchecked = 'N';
                                                } else {
                                                    $checked = '';
                                                    $vchecked = 'Y';
                                                }

                                                //Verifica se foi gerada a NF, caso tenha sido, desabilita o campo 	namenfec (LTM - 11/08/2020 - 366888)
                                                if (!empty($_1_u_nf_envionfe == 'CONCLUIDA')) {
                                                    $disablednamedfec = 'disabled';
                                                } ?>
                                                <input title="Nfe" type="checkbox" <?= $checked ?> <?= $disablednamedfec ?> name="namenfec" onclick="altcheck('nfitem','nfe',<?= $row['idnfitem'] ?>,'<?= $vchecked ?>')">
                                            </td>
                                            
                                            <!-- CERT -->
                                            <? 
                                            if ($row['certificado'] == "Y")
                                            {
                                                ?>
                                                <td align="center">
                                                    <div style="display:inline-flex">
                                                        <? if (!empty($rowadic['idlote']) and $rowadic['assinatura'] == "S" and !empty($rowadic['idassinadopor'])) {
                                                            if ($row["cert"] == 'Y') {
                                                                $checked = 'checked';
                                                                $vchecked = 'N';
                                                            } else {
                                                                $checked = '';
                                                                $vchecked = 'Y';
                                                            }

                                                            $rowx1 = PedidoController::buscarLoteLoteativ($rowadic['partidacompleta'],$rowadic['exercicio']);

                                                            if (!empty($rowx1['idlote'])) {
                                                                $idlote = $rowx1['idlote'];
                                                            } else {
                                                            
                                                                $rowx1 =  PedidoController::buscarLoteAnaliseLote($rowadic['partidacompleta'],$rowadic['exercicio']);
                                                                if (!empty($rowx1['idlote'])) {
                                                                    $idlote = $rowx1['idlote'];
                                                                } else {
                                                                    $idlote = $rowadic['idlote'];
                                                                }
                                                            }
                                                
                                                            $_rowem =  PedidoController::buscarPartidaLote($idlote);

                                                            $filename = "/var/www/carbon8/upload/nfe/Certificado_" . $_rowem['codprodserv'] . "-part" . $_rowem['npart'] . ".pdf";

                                                            ?>
                                                            
                                                            <input title="Certificado" type="checkbox" <?= $checked ?> class="checked_item" idnfitem="<?=$row['idnfitem']?>" name="namecert" onclick="certnfitem('nfitem','cert',<?= $row["idnfitem"] ?>,'<?= $vchecked ?>',<?= $idlote ?>)">
                                                            <? if (!file_exists($filename)) {
                                                                $arrayaux[$row['idnfitem']]["idlote"] = $idlote;
                                                                $arrayaux[$row['idnfitem']]["descr"] = $row["descr"];
                                                                ?> 
                                                                <i class="fa fa-warning laranja pointer lote_<?=$row['idnfitem']?>" idlote="<?=$idlote?>" onclick="janelamodal('form/certanalise.php?_acao=u&idlote=<?=$idlote?>&geraarquivo=Y&gravaarquivo=Y&gerarautomatico=Y')" style="margin-left:5px;margin-top:5px" title="O PDF do Certificado de Análise não foi gerado, Clicar irá gerar o arquivo"></i>
                                                            <? }else{ ?>
                                                                <i class="fa fa-rotate-right azul pointer" idlote="<?=$idlote?>" onclick="janelamodal('form/certanalise.php?_acao=u&idlote=<?=$idlote?>&geraarquivo=Y&gravaarquivo=Y&gerarautomatico=Y')" style="margin-left:5px;margin-top:5px" title="Gerar Certificado de Análise novamente?"></i>
                                                            <? } ?>
                                                        <? } ?>
                                                    </div>
                                                </td>
                                                <? 
                                            } else {
                                                ?>
                                                <td></td>
                                                <?
                                            }
                                        } else { ?>
                                            <td></td>
                                            <td></td>
                                        <? } ?>

                                        <!-- QTD -->
                                        <td align="center" class="nowrap">
                                            <input class="size5 qtditem" <?= $readonlynf ?> item="<?=$row['idnfitem']?>" name="_<?= $i ?>_u_nfitem_qtd" autocomplete="off" id="vlr_qtd_<?= $row['idnfitem'] ?>" type="text" value="<?= $row["qtd"] ?>" vdecimal>
                                            <? if ($row["qtddev"] > 0) { ?>
                                                <br>
                                                <label class="alert-warning">Devolvido:<?= $row["qtddev"]; ?></label>
                                            <? } ?>
                                            <div class="totalItens<?=$row['idnfitem']?>" style="margin-top: 10px;">
                                            
                                            </div>
                                        </td>

                                        <!-- UN -->
                                        <td><?= $row["un"] ?></td>

                                        <!-- Descrição -->
                                        <td align="left" style="max-width: 500px;">
                                            <? if (!empty($row["idprodserv"])) { ?>
                                                <a class="pointer" title="<?= $strtitle ?>" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?= $row["idprodserv"] ?>')"><small><?= $row["descr"] ?></small></a><br>
                                            <? } else { ?>
                                                <small><?= $row["descr"] ?></small>
                                            <? } ?>
                                            <?
                                            //Verifica se o produto é vacina ou antigeno ou meio de cultura para aparecer a mensagem "Material Perecivel"
                                            if (preg_match("/VACINA|ANTIGENOS|MEIO DE CULTURA/", $row["descroriginal"]) && $row['tipo'] == 'PRODUTO') {
                                                $mensagem = TRUE;
                                            }
                                            ?>
                                        </td>

                                        <!-- Fórmula -->
                                        <td style="width: 182px;">
                                            <? if ($row['fabricado'] == 'Y') {
                                                if (!empty($row["idprodservformula"])) {
                                                    $escondeSelFormula = "display: none;";
                                                    $escondeSelLabelFormula = "display: block;";
                                                } else {
                                                    $escondeSelFormula = "display: block;";
                                                    $escondeSelLabelFormula = "display: none;";
                                                }
                                                ?>
                                                <div style="float: left; width: 95%; padding-right: 10px;">
                                                    <select <?= $disablednf ?> style="<?= $escondeSelFormula ?>" class="size20 selectFormula<?= $row['idprodservformula'] ?>" name="nfitem_idprodservformula" <? if ($row['fabricado'] == 'Y') {
                                                                                                                                                                                                            echo ('vnulo');
                                                                                                                                                                                                        } ?> onchange="atualizaf(this,<?= $row["idnfitem"] ?>)">
                                                        <option value="" hidden>Selecionar Fórmula</option>
                                                        <? if ($_1_u_nf_status == 'CONCLUIDO' or $_1_u_nf_status == 'CANCELADO') {
                                                            fillselect(PedidoController::buscarFormulaPorProdserv($row["idprodserv"]), $row["idprodservformula"]); 
                                                        } else {
                                                            fillselect(PedidoController::buscarFormulaAtivaPorProdserv($row["idprodserv"]), $row["idprodservformula"]); 
                                                        } ?>
                                                        <? ?>
                                                    </select>

                                                    <? Proporção:
                                                    if (!empty($row["idprodservformula"])) {
                                                        $_rowProdservForm = PedidoController::buscarRotuloFormulaPorId($row["idprodservformula"]);
                                                        ?>
                                                        <label style="<?= $escondeSelLabelFormula ?>" class="labelForumla<?= $row['idprodservformula'] ?>"><?= $_rowProdservForm['rotulo'] ?></label>
                                                    <? } ?>
                                                </div>
                                                <? if($_1_u_nf_status != 'CONCLUIDO' && $_1_u_nf_status != 'CANCELADO') {  ?>
                                                    <div style="float: right; width: 5%;">
                                                        <a id="editarFormula" style="padding: 16px 7px;" class="fa fa-pencil hoverazul pointer" onclick="editarFormula('<?= $row['idprodservformula'] ?>');" title="Editar Fórmula."></a>
                                                    </div>
                                                <? } ?>
                                            <?
                                            } ?>
                                            <input <?= $readonlynf ?> name="_<?= $i ?>_u_nfitem_cst" type="hidden" size="2" value="<?= $row["cst"]; ?>">
                                            
                                            <?
                                            if ($row["manual"] != 'Y') { ?>
                                                <input <?= $readonlynf ?> name="_<?= $i ?>_u_nfitem_vlritem" size="10" type="hidden" value="<?= $row["vlritem"]; ?>" vdecimal>
                                            <? } ?>
                                        </td>

                                        <!-- CFOP -->
                                        <? 
                                        $rowe = PedidoController::bucarEnderecoPorId($_1_u_nf_idenderecofat);
                                        $ufemit=traduzid('empresa','idempresa','uf',$nf_idempresa);
                                        if ($rowe["uf"] ==  $ufemit) {
                                            $strlocal = 'DENTRO';
                                        } else {
                                            $strlocal = 'FORA';
                                        }

                                        if ($_1_u_nf_moeda == 'REAL') {
                                        
                                            if (strpos($lcfop['lcfop'],$row["cfop"]) !== false and !empty($row["cfop"])) {
                                                $bccfop="";
                                                $alertcfop="";
                                            } else {
                                                $bccfop="background-color: #ff05056e;";
                                                $alertcfop="O CFOP selecionado não está vinculado a natureza da operação do pedido.";
                                            }
                                            
                                            ?>
                                            <td  title='<?=$alertcfop?>' class="respreto" align="center">
                                                <select  title='<?=$alertcfop?>' <?= $disablednf ?> name="_<?= $i ?>_u_nfitem_cfop" style="<?=$bccfop?>">
                                                    <option value=""></option>
                                                    <? fillselect(PedidoController::BuscarCfopPorOrigem($strlocal), $row["cfop"]); ?>
                                                </select>
                                            </td>
                                        
                                        <!-- Valor Líquido -->
                                        <? } 

                                        if ($_1_u_nf_moeda == 'REAL') { ?>
                                            
                                            <td class="nowrap">
                                                <input class="size7" <?= $readonlynf ?> id="vlr_original<?= $row['idnf']; ?>" name="_<?= $i ?>_u_nfitem_vlrliq" type="text" min="<?= $valitemcont ?>" value="<?= $row['vlrliq']; ?>" vdecimal onchange="validavalor(this)">
                                            </td>
                                        <? } else { ?>
                                            
                                            <td align='Center' class="nowrap">
                                                <input style="width: 50px;" <?= $readonlynf ?> id="vlr_ext<?= $row['idnfitem']; ?>" name="_<?= $i ?>_u_nfitem_vlritemext" type="text" value="<?= $row['vlritemext'] ?>">
                                            </td>
                                            <td align='Center' class="nowrap">
                                                <input readonly style="width:50px;" id="vlr_totalext<?= $row['idnfitem']; ?>" name="_<?= $i ?>_u_nfitem_totalext" size="8" type="text" value="<?= $row["totalext"]; ?>" vdecimal>
                                            </td>
                                            <td align='Center' class="nowrap">
                                                <input readonly style="width: 50px;" id="vlr_original<?= $row['idnfitem']; ?>" name="_<?= $i ?>_u_nfitem_vlrliq" size="7" type="text" value="<?= $row['vlritemext'] * $_1_u_nf_taxacv; ?>" vdecimal>
                                            </td>
                                        <? } ?>

                                        <!-- Desconto -->
                                        <td class="respreto" align="center"><input <?= $readonlynf ?> class="size4" name="_<?= $i ?>_u_nfitem_des" size="5" type="text" value="<?= $row['des']; ?>" onchange="setarAlterarValor('Y')" vdecimal></td>

                                        <!-- Frete -->
                                        <? if ($_1_u_nf_modfrete != 9) { ?>
                                            <td align="center">
                                                <input <?= $readonlynf ?> name="_<?= $i ?>_u_nfitem_frete" class="size4" type="text" value="<?= $row["frete"]; ?>" vdecimal vnulo>
                                            </td>
                                        <? } ?>
                                        
                                        <!-- Total Unitário -->
                                        <td align="center">
                                            <? if ($row["manual"] == 'Y') { ?>
                                                <input readonly style="width:70px;" name="_<?= $i ?>_u_nfitem_total" size="8" type="text" value="<?= $row['vlrliq'] * $row['qtd']; ?>" vdecimal>
                                                <input <?= $readonlynf ?>  id="nfitem_total<?=$row["idnfitem"]?>"  name="_<?= $i ?>_u_nfitem_total" idnfitemtotalhide="<?= $row['idnfitem'] ?>" size="8" type="hidden" value="<?= $row['vlrliq'] * $row['qtd']; ?>" vdecimal readonly='readonly'>
                                            <? } else { ?>
                                                <input readonly style="width:70px;" name="_<?= $i ?>_u_nfitem_total" size="8" type="text" value="<?= $row['vlrliq'] * $row['qtd']; ?>" vdecimal>
                                                <input <?= $readonlynf ?>  id="nfitem_total<?=$row["idnfitem"]?>"  name="_<?= $i ?>_u_nfitem_total" idnfitemtotalhide="<?= $row['idnfitem'] ?>" size="8" type="hidden" value="<?= $row['vlrliq'] * $row['qtd']; ?>" vdecimal readonly='readonly'>
                                                <? //echo number_format(tratanumero($row["total"]), 2, ',', '.');
                                                ?>
                                            <? } ?>
                                        </td>
                                            
                                        <!-- Fiscal -->
                                        <td align="center">
                                            <? if ($_SESSION["SESSAO"]["OBRIGATORIOCONTATO"] != 'Y' && $_1_u_nf_status != 'INICIO' && $_1_u_nf_status != 'SOLICITADO') { ?>
                                                <button type="button" class="btn btn-link btn-xs " onclick="financeiro(<?= $row["idnfitem"]; ?>,<?= $i ?>)">Fiscal</button>
                                                <? $ufEX = traduzid('endereco', 'idendereco', 'uf', $_1_u_nf_idenderecofat);
                                                if ($ufEX == 'EX') {
                                                    $rowimp = PedidoController::BuscarNfitemImportacao($row["idnfitem"]);
                                                    if (!empty($rowimp['idnfitemimport'])) {
                                                        $strimp = "&_acao=u&idnfitemimport=" . $rowimp['idnfitemimport'];
                                                    } else {
                                                        $strimp = "&_acao=i&idnfitem=" . $row["idnfitem"];
                                                    }
                                                ?>
                                                    <button type="button" class="btn btn-link btn-xs " onclick="importacao('<?= $strimp ?>');">Importação</button>
                                            <? }
                                            } ?>
                                        </td>

                                        <!-- Comissão -->
                                        <td>
                                            <? if ($row['comissionado'] == 'Y' and ($_1_u_nf_comissao == 'Y' or empty($_1_u_nf_comissao))) {
                                            
                                                $resco = PedidoController::BuscarComissaoPorIdnfitem($row['idnfitem']);
                                                $qtdcom = count($resco);
                                                if ($qtdcom < 1) {
                                                    $alertcom = "vermelho";
                                                    $fig = "<i title='Verificar Comissão' class='fa fa-exclamation-triangle laranja pointer'></i>";
                                                } else {
                                                    $alertcom = "";
                                                    $fig = "";
                                                }
                                            ?>
                                                <button type="button" class="btn btn-link btn-xs " onclick="abrecomissao(<?= $row['idnfitem'] ?>);" <?= $alertcom ?>>Comissão</button> <?= $fig ?>
                                            <? } ?>
                                        </td>

                                        <!-- Checkbox Excluir -->
                                        <?if(empty($_1_u_nf_nnfe)){?>
											<td style="text-align: left;">
												<input class="checkbox-nfitem" type="checkbox" data-idnfitem="<?=$row['idnfitem'];?>" value="<?=$row['idnfitem'];?>">
											</td> 
										<?}?>
                                            
                                        <!-- Expandir -->
                                        <td colspan="2" align="right">
                                            <? //if (($_SESSION["SESSAO"]["OBRIGATORIOCONTATO"] == 'Y' && $_1_u_nf_status != 'INICIO' /*&& $_1_u_nf_status != 'SOLICITADO'*/) || ($_SESSION["SESSAO"]["OBRIGATORIOCONTATO"] != 'Y' && $_1_u_nf_status != 'INICIO' /*&& $_1_u_nf_status != 'SOLICITADO'*/)) { 
                                            if($_SESSION["SESSAO"]["OBRIGATORIOCONTATO"] != 'Y'){
                                            ?>
                                                <i class="fa fa-arrows-v cinzaclaro pr-3 pointer estbotao" title="Estoque" data-toggle="collapse" idnfitem="<?= $row["idnfitem"] ?>" href="#nfiteminfo<?= $row["idnfitem"] ?>"></i>
                                            <? } ?>
                                        </td>
                                    </tr>
                                    <? if ($ufEX == 'EX' and $row['nfe'] == "Y"){?>
                                    <tr>
                                        <td  style=" vertical-align: middle;"class="nowrap" colspan='2' class="bold">Tributação <i title="Utilizar no campo UNIDADE a abreviatura KG (Quilograma) e preencher o campo QUANTIDADE com o valor em KG" class="fa btn-sm fa-info-circle 2x azul pointer hoverazul tip"></i></td>
                                        <td></td>
                                        <td align="center" class="nowrap"><input id="qtrib<?=$row["idnfitem"]?>" class="size5" title="Quantidade Tributada" placeholder="Qtd"  name="_<?=$i?>_u_nfitem_qtrib"  type="text" value="<?= $row['qtrib']?>" vdecimal onchange="calculaTrib(this,<?=$row['idnfitem']?>)"></td>
                                        <td align="left" class="nowrap"><input class="size3" title="Unidade Tributada" placeholder="Un"  name="_<?=$i?>_u_nfitem_utrib"  type="text" value="<?= $row['utrib']?>"></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td class="nowrap"><input id="vuntrib<?=$row["idnfitem"]?>"  class="size7" title="Valor Unidade Tributada" placeholder="Valor Un"  name="_<?=$i?>_u_nfitem_vuntrib"  type="text" value="<?= $row['vuntrib']?>" vdecimal></td>
                                        <td></td>
                                        <td></td>
                                        <td align="center" class="nowrap"></td>
                                    </tr>
                                    <?}?>
                                    <tr class="<?= $row["collapse"] ?> collapseall" idnfitem="<?= $row["idnfitem"] ?>" id="nfiteminfo<?= $row["idnfitem"] ?>">
                                        <td class="estoque" colspan="50">
                                            <div>
                                                <ul class="ulitens">
                                                    <li class="liitens">
                                                        <? 
                                                        if ($_1_u_nf_tpnf == "0" and $rows[$_idnfitem]['comprado'] == 'Y') { // se for comprado da entra em novo lote
                                                            $_idnfitem = $row["idnfitem"];
                                                            if (($rows[$_idnfitem]["tipo"] = "PRODUTO")) { // produto de venda de entrar no mesmo lote 
                                                                if (!empty($rows[$_idnfitem]['idprodserv'])) {
                                                                    if (empty($rows[$_idnfitem]['idlote']) and empty($rows[$_idnfitem]['idlote2'])) { ?>
                                                                        <div>
                                                                            <div class="panel-body">
                                                                                <div class="col-md-6">
                                                                                    Criar Lote: <i class="fa fa-plus-circle verde pointer" onclick="novolote(<?= $row["idnfitem"] ?>,'<?= $row["qtd"] ?>',<?= $row["idprodserv"] ?>,'<?= date("Y") ?>')"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <?
                                                                    } else {
                                                                        //ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
                                                                        if (empty($rows[$_idnfitem]['idlote2'])) {                                                                       
                                                                            $roa = PedidoController::buscarModuloPorIdlote($rows[$_idnfitem]['idlote']);
                                                                            ?>
                                                                            <div>
                                                                                <div class="row">
                                                                                    <div class="col-md-1 bold">QTD</div>
                                                                                    <div class="col-md-1 bold">Unidade</div>
                                                                                    <div class="col-md-2 bold">Lote</div>
                                                                                    <div class="col-md-1 bold">Status</div>
                                                                                </div>
                                                                                <div class="row">
                                                                                    <div class="col-md-1 bold">
                                                                                        <?= $rows[$_idnfitem]['qtdprod'] ?>
                                                                                    </div>
                                                                                    <div class="col-md-1 bold">
                                                                                        <?= $rows[$_idnfitem]['unpadrao'] ?>
                                                                                    </div>
                                                                                    <div class="col-md-2 bold">
                                                                                        <a class=" hoverazul pointer" onclick="janelamodal('?_modulo=<?= $roa['idobjeto'] ?>&_acao=u&idlote=<?= $rows[$_idnfitem]['idlote'] ?>');"><?= $rows[$_idnfitem]['partida'] ?>/<?= $rows[$_idnfitem]['exercicio'] ?></a>
                                                                                    </div>
                                                                                    <div class="col-md-1 bold">
                                                                                        <?= $rows[$_idnfitem]['status'] ?>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        <?
                                                                        } else {
                                                                        $roa = PedidoController::buscarModuloPorIdlote($rows[$_idnfitem]['idlote2']);
                                                                            ?>
                                                                            <div>
                                                                                <div class="row">
                                                                                    <div class="col-md-1 bold">QTD</div>
                                                                                    <div class="col-md-1 bold">Unidade</div>
                                                                                    <div class="col-md-2 bold">Lote</div>
                                                                                </div>
                                                                                <div class="row">
                                                                                    <div class="col-md-1 bold">
                                                                                        <?= $rows[$_idnfitem]['qtdprod'] ?>
                                                                                    </div>
                                                                                    <div class="col-md-1 bold">
                                                                                        <?= $rows[$_idnfitem]['unpadrao'] ?>
                                                                                    </div>
                                                                                    <div class="col-md-2 bold">
                                                                                        <a class=" hoverazul pointer" onclick="janelamodal('?_modulo=<?= $roa['idobjeto'] ?>&_acao=u&idlote=<?= $rows[$_idnfitem]['idlote2'] ?>');"><?= $rows[$_idnfitem]['partida2'] ?>/<?= $rows[$_idnfitem]['exercicio2'] ?></a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                    <? }
                                                                    }
                                                                } else { ?>
                                                                    <span>-</span>
                                                                <? }
                                                            } else { ?>
                                                                <span>-</span>
                                                            <? } ?>

                                                            <? } else {

                                                            if ($_1_u_nf_status == 'INICIO' or $_1_u_nf_status == 'ORCAMENTO' or $_1_u_nf_status == 'SOLICITADO' or $_1_u_nf_status == 'PENDENTE' or $_1_u_nf_status == 'PEDIDO' or  $_1_u_nf_status == 'PRODUCAO'  or  $_1_u_nf_status == 'FATURAR' or $_1_u_nf_status == 'EXPEDICAO') {
                                                            
                                                                $ront =PedidoController::buscarFinnfePorNF($_1_u_nf_idnf);
                                                            
                                                                $loteproducao =traduzid('empresa', 'idempresa', 'loteproducao', $_1_u_nf_idempresa); 
                                                                                                                    
                                                                $qr1=PedidoController::BuscarItensPedido($row['idnfitem'],$row['especial'] ,$_1_u_nf_idpessoa,$_1_u_nf_tpnf,$row['idprodservformula'],$_1_u_nf_status,$_1_u_nf_idempresafat,$loteproducao, $ront['finnfe']);

                                                            } else { //if($_1_u_nf_status=='INICIO' OR $_1_u_nf_status=='PEDIDO'){
                                                                $qr1=PedidoController::buscarLotepedido($row['idnfitem']);
                                                            }
                                                            $qtdrows1 = count($qr1);
                                                            $ii = $i;
                                                            if ($qtdrows1 > 0) { ?>
                                                                <div>
                                                                    <div class="row">
                                                                        <div class="col-md-1 bold">QTD</div>
                                                                        <div class="col-md-1 bold">Estoque</div>
                                                                        <div class="col-md-2 bold">Lote (Unidade)</div>
                                                                        <? if ($row['especial'] == 'Y') { ?>
                                                                            <div class="col-md-1 bold" style="text-align: center;">Sol. Fab.</div>
                                                                        <? } ?>
                                                                        <div class="col-md-1 bold">Status OP</div>
                                                                        <div class="col-md-1 bold">Criação</div>
                                                                        <div class="col-md-1 bold">Prev. Produção</div>
                                                                        <div class="col-md-1 bold">Fabricação</div>
                                                                        <div class="col-md-1 bold">Vencimento</div>
                                                                        <? if ($_1_u_nf_status != 'INICIO' and $_1_u_nf_status != 'SOLICITADO') { ?>
                                                                            <div class="col-md-1 bold">Localização</div>
                                                                            <div class="col-md-1 bold">Esgotar</div>
                                                                        <? } ?>
                                                                    </div>
                                                                    <? 
                                                                    $inidlotereserva = 0;
                                                                    foreach($qr1 as $row1){                                                                
                                                                        $i = $i + 1;
                                                                                                                                    
                                                                        if ($_1_u_nf_envionfe == "CONCLUIDA") {
                                                                            $disabledl = "disabled='disabled'";
                                                                        } else {
                                                                            $disabledl = "";
                                                                        }
                                                            
                                                                        $rowlc = PedidoController::buscarConsumoLotePedido($row["idnfitem"],$row1["idlote"],$row1["idlotefracao"] );
                                                                        if (empty($rowlc['id'])) {
                                                                            $lcacao = "i";
                                                                        } else {
                                                                            $lcacao = "u";
                                                                        }
                                                                    
                                                                        $ressol =PedidoController::buscarLoteReservaPedido($row1["idlote"]);
                                                                        $strresv = "";
                                                                        $existereserva = 'N';
                                                                        $existereservalote = 'N';
                                                                        $qtdresv = 0;

                                                                        foreach($ressol as $rowsol){                                                                     
                                                                            $qtdresv = $qtdresv + $rowsol['qtd'];
                                                                            if ($rowsol['idnfitem'] == $row["idnfitem"]) {
                                                                                $existereserva = 'Y';
                                                                                $qtdresof = $rowsol['qtd'];
                                                                            }
                                                                            $existereservalote = 'Y';
                                                                            $qtdresoflote = $rowsol['qtd'];
                                                                            $strresv .= "Orçamento:" . $rowsol['idnf'] . " - Qtd.: " . $rowsol['qtd'] . "<br>";
                                                                        }
                                                                        if ($row1["qtdpedida"] < $qtdresv) {
                                                                            $bgcolor = "#ff9c7a";
                                                                        } else {
                                                                            if ($rowlc["qtdd"] > 0) {
                                                                                $bgcolor = "#fff57a";
                                                                            } else {
                                                                                $bgcolor = "";
                                                                            }
                                                                        } ?>
                                                                        <div class="row" style="background-color: <?= $bgcolor ?>">
                                                                            <!-- <div class="col-md-1">                                                 
                                                                                <? $msg = "";
                                                                                if ($row1["tipoobjetoprodpara"] == 'nf' and $row1["idobjetoprodpara"] == $_1_u_nf_idnf) {
                                                                                    //$disabledlx="";
                                                                                } elseif ($row1['assinatura'] == "S" and empty($row1['idassinadopor'])) {
                                                                                    // $disabledlx="disabled='disabled'";
                                                                                    $msg = "Lote não assinado ainda."
                                                                                ?>
                                                                                    <a class="fa fa-exclamation-triangle vermelho hoverazul btn-lg pointer" onclick="janelamodal('?_modulo=lote&_acao=u&idlote=<?= $row1['idlote'] ?>')" title="Solicitar Assinatura do lote!"></a>                                           
                                                                                <? } else { ?>
                                                                                    <a class="fa fa-list-ol hoverazul btn-lg pointer" onclick="janelamodal('form/certanalise.php?_acao=u&idlote=<?= $row1["idlote"] ?>')" title="Cert. Análise"></a>                                           
                                                                                <? } ?>                                              
                                                                            </div>-->
                                                                            <div class="col-md-1">
                                                                                <? 
                                                                                    $qtdDisponivel = $row1["qtddisp"];
                                                                                    if ($existereservalote == 'Y' and !empty($strresv)) $qtdDisponivel = $row1["qtddisp"] - $qtdresv;
                                                                                
                                                                                    if ((($row1["status"] != "APROVADO" and $row1["status"] != "LIBERADO" and $row1["status"] != "QUARENTENA") or $existereserva == 'Y') and ($_1_u_nf_tpnf == 1)) {
                                                                                
                                                                                    $rowlre =PedidoController::buscarLoteReservaPorIdnfitem($row1["idlote"],$row["idnfitem"],$inidlotereserva);
                                                                                    if (empty($rowlre['id'])) {
                                                                                        $lcacao = "i";
                                                                                    } else {
                                                                                        $lcacao = "u";
                                                                                        $inidlotereserva .= ',' . $rowlre['id'];
                                                                                    }
                                                                                    //if($rowlc["qtdsol"]>0){$disabledl="";}

                                                                                    if (!empty($rowlre["qtdd"])) {
                                                                                        $nametag = "input";
                                                                                        $closetag = "";
                                                                                        $action = 'onkeyup="alterarTagNameFilhos(this)" onblur="alterarTagNameFilhos(this)"';
                                                                                    } else {
                                                                                        $nametag = "button";
                                                                                        $closetag = "</button>";
                                                                                        $action = 'onclick="alterarTagName(this)"';
                                                                                    }

                                                                                    ?>
                                                                                    <div class="changetag_<?= $i ?> hidden">
                                                                                        <<?= $nametag ?> type="hidden" <?= $disabledl ?> name="_<?= $i ?>_<?= $lcacao ?>_lotereserva_idlotereserva" value="<?= $rowlre["id"] ?>"><?= $closetag ?>
                                                                                        <<?= $nametag ?> type="hidden" <?= $disabledl ?> name="_<?= $i ?>_<?= $lcacao ?>_lotereserva_idlote" value="<?= $row1["idlote"] ?>"><?= $closetag ?>
                                                                                        <<?= $nametag ?> type="hidden" <?= $disabledl ?> name="_<?= $i ?>_<?= $lcacao ?>_lotereserva_tipoobjeto" value="nfitem"><?= $closetag ?>
                                                                                        <<?= $nametag ?> type="hidden" <?= $disabledl ?> name="_<?= $i ?>_<?= $lcacao ?>_lotereserva_idobjeto" value="<?= $row["idnfitem"] ?>"><?= $closetag ?>
                                                                                    </div>

                                                                                    <<?= $nametag ?> data-qtddisponivel="<?= $qtdDisponivel ?>" class="size5 changetag somarQtd<?=$row["idnfitem"]?>" identificador="<?= $i ?>" item="<?=$row["idnfitem"]?>" <?= $action ?> style="height: 22px;" title="<?= $msg ?>" <?= $disabledl ?> type="text" name="_<?= $i ?>_<?= $lcacao ?>_lotereserva_qtd" value="<?= $rowlre["qtdd"] ?>"><?= $closetag ?>
                                                                                    <?
                                                                                    if(!empty($strresv)){
                                                                                        ?>
                                                                                        <i class="fa fa-info-circle laranja pointer hoverazul tip" title="Reserva">
                                                                                            <span>
                                                                                                <?=$strresv ?>
                                                                                            </span>
                                                                                        </i>
                                                                                        <?
                                                                                    }
                                                                                } else {

                                                                                    if ($_1_u_nf_tpnf == 1) {
                                                                                        if (!empty($rowlc["qtdd"])) {
                                                                                            $nametag = "input";
                                                                                            $closetag = "";
                                                                                            $action = 'onkeyup="alterarTagNameFilhos(this)" onblur="alterarTagNameFilhos(this)"';
                                                                                        } else {
                                                                                            $nametag = "button";
                                                                                            $closetag = "</button>";
                                                                                            $action = 'onclick="alterarTagName(this)"';
                                                                                        }
                                                                                        // if($_1_u_nf_envionfe=="CONCLUIDA"){$disabledl="disabled='disabled'";}                                                                                
                                                                                        ?>
                                                                                        <<?= $nametag ?> data-qtddisponivel="<?= $qtdDisponivel ?>" class="size5 changetag somarQtd<?=$row["idnfitem"]?>" item="<?=$row["idnfitem"]?>" identificador="<?= $i ?>" style="height: 22px;" <?= $action ?> title="<?= $msg ?>" <?= $disabledl ?> type="text" name="_<?= $i ?>_<?= $lcacao ?>_lotecons_qtdd" data-qtdd="<?= $rowlc["qtdd"] ?>"  value="<?= $rowlc["qtdd"] ?>"><?= $closetag ?>
                                                                                        <?
                                                                                    } else {
                                                                                        if (!empty($rowlc["qtdc"])) {
                                                                                            $nametag = "input";
                                                                                            $closetag = "";
                                                                                            $action = 'onkeyup="alterarTagNameFilhos(this)" onblur="alterarTagNameFilhos(this)"';
                                                                                        } else {
                                                                                            $nametag = "button";
                                                                                            $closetag = "</button>";
                                                                                            $action = 'onclick="alterarTagName(this)"';
                                                                                        }
                                                                                        //if($_1_u_nf_envionfe=="CONCLUIDA"){$disabledl="disabled='disabled'";}
                                                                                        ?>
                                                                                        <<?= $nametag ?> data-qtddisponivel="<?= $qtdDisponivel ?>" class="size5 changetag somarQtd<?=$row["idnfitem"]?>" item="<?=$row["idnfitem"]?>" identificador="<?= $i ?>" style="height: 22px;" <?= $action ?> title="<?= $msg ?>" <?= $disabledl ?> type="text" name="_<?= $i ?>_<?= $lcacao ?>_lotecons_qtdc" value="<?= $rowlc["qtdc"] ?>"><?= $closetag ?>
                                                                                    <? } ?>
                                                                                    <div class="changetag_<?= $i ?> hidden">
                                                                                        <<?= $nametag ?> type="hidden" name="_<?= $i ?>_<?= $lcacao ?>_lotecons_idlotecons" value="<?= $rowlc["id"] ?>"><?= $closetag ?>
                                                                                        <<?= $nametag ?> type="hidden" name="_<?= $i ?>_<?= $lcacao ?>_lotecons_tipoobjeto" value="nfitem"><?= $closetag ?>
                                                                                        <? $finnfe = traduzid('natop', 'idnatop', 'finnfe', $_1_u_nf_idnatop);
                                                                                        if ($finnfe == 4) { ?>
                                                                                            <<?= $nametag ?> type="hidden" name="_<?= $i ?>_<?= $lcacao ?>_lotecons_status" value="DEVOLUCAO"><?= $closetag ?>
                                                                                        <? } ?>
                                                                                        <<?= $nametag ?> type="hidden" name="_<?= $i ?>_<?= $lcacao ?>_lotecons_idobjeto" value="<?= $row["idnfitem"] ?>"><?= $closetag ?>
                                                                                        <<?= $nametag ?> type="hidden" name="_<?= $i ?>_<?= $lcacao ?>_lotecons_idlote" value="<?= $row1["idlote"] ?>"><?= $closetag ?>
                                                                                        <<?= $nametag ?> type="hidden" name="_<?= $i ?>_<?= $lcacao ?>_lotecons_idlotefracao" value="<?= $row1["idlotefracao"] ?>"><?= $closetag ?>
                                                                                    </div>
                                                                                    <?
                                                                                }
                                                                                ?>
                                                                            </div>
                                                                            
                                                                            <div class="col-md-1">
                                                                                <? if ($existereservalote == 'Y' and !empty($strresv)) { ?>
                                                                                    <?= ($row1["qtddisp"] - $qtdresv) ?>
                                                                                    
                                                                                    <i class="fa fa-info-circle laranja pointer hoverazul tip">
                                                                                        <span><?= $strresv ?></span>
                                                                                    </i>
                                                                                <? 
                                                                                } else {
                                                                                    echo $row1["qtddisp"];
                                                                                }
                                                                                if (!empty($row["idprodservforn"])) {
                                                                                    echo (" " . $row1["unpadrao"]);
                                                                                }

                                                                                ?>
                                                                            </div>
                                                                            <div class="col-md-2">
                                                                                <?
                                                                            
                                                                                $rcv = PedidoController::buscaFormalizacaoLote( $row1["idlote"]);
                                                                                $qtdresFromalizacao = count($rcv);
                                                                                ?>
                                                                                <?/* if ($row1["status"] != "APROVADO" and $row1["status"] != "ESGOTADO" and $row1["status"] != "LIBERADO" and $qtdresFromalizacao > 0) { ?>
                                                                                    <a title="Ordem de produção" class="pointer" onclick="janelamodal('?_modulo=formalizacao&_acao=u&idformalizacao=<?= $rcv['idformalizacao'] ?>')"><?= $row1["partida"] ?></a>
                                                                                <? } else { */?>
                                                                                    <a title="Lote" class="pointer" onclick="janelamodal('?_modulo=<?= $row1["idobjeto"] ?>&_acao=u&idlote=<?= $row1["idlote"] ?>')"><?= $row1["partida"] ?></a>
                                                                                <?// }

                                                                                if($row1["idempresafr"]==$_1_u_nf_idempresafat ){
                                                                                    echo ("<span style='color: #3c763d;font-weight: bold;'> (" . $row1["unidade"] . ") </span>"); 

                                                                                }else{
                                                                                    echo (" (" . $row1["unidade"] . ")"); 
                                                                                }

                                                                            ?>

                                                                                <a class="fa fa-search azul pointer hoverazul" title="Histórico" onclick="consumo(<?= $row1["idlote"] ?>);"></a>
                                                                                <? $rselo = PedidoController::buscaSeloLote( $row1["idlote"]);
                                                                                if(!empty($rselo['descr'])){?>
                                                                                    <br>
                                                                                <?
                                                                                echo("(".$rselo['descr'].")");
                                                                                }
                                                                                ?>
                                                                                
                                                                            
                                                                                
                                                                            </div>
                                                                            <? if ($row['especial'] == 'Y') { ?>
                                                                                <div class="col-md-1 nowrap" style="text-align: center;">
                                                                                    <? if (!empty($row1['idsolfab'])) { ?>
                                                                                        <a title="Solicitação de Vacinas Autógenas" class="pointer" onclick="janelamodal('?_modulo=solfab&_acao=u&idsolfab=<?= $row1["idsolfab"] ?>')"><?= $row1["idsolfab"] ?></a>
                                                                                    <? } ?>
                                                                                </div>
                                                                            <? } ?>
                                                                            <div class="col-md-1 nowrap">                                                                           
                                                                                    <?
                                                                                    //Caso não tenha sido criado na formalização pegará o stauts do Lote
                                                                                    if (/*$row1["status"] != "APROVADO" and $row1["status"] != "ESGOTADO" and */ $qtdresFromalizacao > 0) {

                                                                                        ?>
                                                                                        <a title="Ordem de produção" class="pointer" onclick="janelamodal('?_modulo=<?=$rcv['modulo']?>&_acao=u&idformalizacao=<?=$rcv['idformalizacao']?>')">
                                                                                        <?

                                                                                        $row1["status"] = $rcv['status'];
                                                                                    } else {
                                                                                        ?>
                                                                                        <a  title="Lote" href="?_modulo=<?=$row1["idobjeto"]?>&_acao=u&idlote=<?= $row1["idlote"] ?>" target="_blank" style="color: inherit;">
                                                                                        <?                                                                                
                                                                                        $row1["status"] == 'AGUARDANDO' ? 'AGUARDANDO AUT.' : $row1["status"];
                                                                                    }

                                                                                    echo $row1["status"];
                                                                                    ?>
                                                                                </a>
                                                                            </div>
                                                                            <div class="col-md-1"><?= dma($row1["lcriadoem"]) ?></div>
                                                                            <div class="col-md-1"><? if ($row1['idformalizacao']) { ?><input class='calendario' idform='<?= $row1['idformalizacao'] ?>' name='<?= $row1['idformalizacao'] ?>data_formalizacao' value='<?= dma($row1["lenvio"]) ?>'><? } else {
                                                                                                                                                                                                                                                                                        } ?></div>
                                                                            <div class="col-md-1">
                                                                            <?if(empty($row1["dataf"])){?>                                                                            
                                                                                <font color="RED" title="Lote com data de fabricação vazia!" style="font-weight: bold;">VAZIO</font>
                                                                            <?
                                                                            }else{                                                                        
                                                                                echo($row1["dataf"]);                                                                      
                                                                            }                                                                        
                                                                            ?>
                                                                            </div>
                                                                            <div class="col-md-1">
                                                                            <?if(empty($row1["datav"])){?>                                                                            
                                                                                <font color="RED" title="Se emitida, a nota ficará sem a informação do lote!" style="font-weight: bold;">VAZIO</font>
                                                                            <?
                                                                            }else{                                                                        
                                                                                echo($row1["datav"]);                                                                      
                                                                            }                                                                        
                                                                            ?>
                                                                            </div>
                                                                            <? if ($_1_u_nf_status != 'INICIO' && $_1_u_nf_status != 'SOLICITADO') { ?>
                                                                                <div class="col-md-1">
                                                                                    <div class="oEmailorc">
                                                                                        <a class="fa fa-search azul pointer hoverazul" title=" Ver localizações" data-target="webuiPopover0"></a>
                                                                                    </div>
                                                                                    <div class="webui-popover-content">
                                                                                        <?
                                                                                    
                                                                                        $reseo =  PedidoController::buscarLotelocalizacao($row1["idlote"]);
                                                                                        foreach($reseo as $roweo){ 
                                                                                        ?>(<?
                                                                                                if ($roweo['tipoobjeto'] == "pessoa" and !empty($roweo['idobjeto'])) {
                                                                                                    echo (traduzid("pessoa", "idpessoa", "nome", $roweo['idobjeto']));
                                                                                                } elseif ($roweo['tipoobjeto'] == 'tagdim' and !empty($roweo['idobjeto'])) {                                                                                           
                                                                                                    $rloc = PedidoController::buscarTagTagdim($roweo['idobjeto']);
                                                                                                    echo ($rloc['campo']);
                                                                                                } else {
                                                                                                    echo ("Sem localização especifica.");
                                                                                                } ?>
                                                                                        )<?
                                                                                        } ?>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="col-md-1">
                                                                                    <? if ($row1["status"] != "ESGOTADO") { ?>
                                                                                        <a class="fa fa-minus-square  pointer vermelho" title="Esgotar Lote!!!" onClick="esgotarlote(<?= $row1["idlote"] ?>);">
                                                                                        </a>
                                                                                    <? } ?>
                                                                                    <? if ($row1["idprodservformula"]) {
                                                                                    
                                                                                        $rowk = PedidoController::buscarRotuloFormula($row1["idprodservformula"]);
                                                                                        if (empty($rowk["idprodservformularotulo"])) {                                 ?>
                                                                                            <a title="Imprimir rotulo." class="fa fa-exclamation-triangle laranja pointer" onclick="alert('Favor solicitar o cadastro do rotulo no cadastro de produtos!!!')"></a>
                                                                                        <? } else { ?>
                                                                                            <a title="Imprimir rotulo." class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('?_modulo=rotulolote&idlote=<?= $row1["idlote"] ?>&idnf=<?= $_1_u_nf_idnf ?>')"></a>
                                                                                    <?
                                                                                        } // if(empty($rowk["idprodservformularotulo"]))
                                                                                    } //if($row1["idprodservformula"])
                                                                                    ?>
                                                                                </div>
                                                                            <? } //if($_1_u_nf_status!='INICIO' and $_1_u_nf_status!='PEDIDO'){
                                                                            ?>
                                                                        </div>
                                                                        <div id="consumo<?= $row1["idlote"] ?>" style="display: none">
                                                                            <?$prodservclass->historicolotecons($row1["idlote"]);
                                                                            ?>
                                                                        </div>
                                                                    <?
                                                                    } //while ($row1 = mysqli_fetch_array($qr1)){
                                                                    ?>
                                                                </div>
                                                            <? } else { //if($qtdrows1>0){ naum tem lote
                                                            ?>
                                                                <div>
                                                                    <div class="panel-body">
                                                                        <div class="col-md-6">Não foi encontrado lote disponivel!!!</div>
                                                                    </div>
                                                                </div>
                                                            <?
                                                            } //if($qtdrows1>0){

                                                        }
                                                        ?>
                                                    </li>
                                                    <? if (($_1_u_nf_status == "PEDIDO" or $_1_u_nf_status == "INICIO" or $_1_u_nf_status == 'SOLICITADO' or $_1_u_nf_status == 'PENDENTE') and (!empty($row["idprodservformula"]))) { ?>
                                                        <li class="liitens">
                                                            <div class="agrupamento novo">
                                                                <div class="row">
                                                                    <div class="col-md-1">
                                                                        <input class="size5" placeholder="Qtd." title="" type="text" name="<?= $row['idnfitem'] ?>_loteqtdd" id="<?= $row['idnfitem'] ?>_loteqtdd" value="<?=round($row["qtd"])?>">
                                                                    </div>
                                                                    <? 
                                                                    if($row["especial"]=='Y'){
                                                                    $stridsf = listaSolfabCliente($row["idprodservformula"], $_1_u_nf_idpessoa, $row["idprodserv"]); ?>
                                                                    <div class="col-md-3">
                                                                        <? if (empty($stridsf)) { ?>
                                                                            <select name="<?= $row['idnfitem'] ?>_loteidsolfab" id="<?= $row['idnfitem'] ?>_loteidsolfab" onchange="listaagente(this,<?= $row['idnfitem'] ?>,<?= $_1_u_nf_idpessoa ?>,<?= $row["idprodservformula"] ?>)">
                                                                                <option value="" disabled selected hidden>Sol. Fab.</option>
                                                                                <option value="novo">Nova</option>
                                                                            </select>
                                                                        <? } else { ?>
                                                                            <select name="<?= $row['idnfitem'] ?>_loteidsolfab" id="<?= $row['idnfitem'] ?>_loteidsolfab" onchange="listaagente(this,<?= $row['idnfitem'] ?>,<?= $_1_u_nf_idpessoa ?>,<?= $row["idprodservformula"] ?>)">
                                                                                <option value="" disabled selected hidden>Sol. Fab.</option>
                                                                                <option value="novo">Nova</option>
                                                                                <?fillselect(PedidoController::buscarSolfabPorIds($stridsf))?>
                                                                            </select>
                                                                        <? } ?>
                                                                    </div>
                                                                    <?}?>
                                                                    <div class="col-md-1">
                                                                        <i class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer" onclick="novaformalizacao(<?= $row['idnfitem'] ?>,<?= $row['idprodserv'] ?>,<?= $_1_u_nf_idpessoa ?>,<?= $row["idprodservformula"] ?>,'<?=$row["especial"]?>')" title="Gerar OP"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row" id="listaagentes<?= $row['idnfitem'] ?>"></div>
                                                        </li>
                                                    <? } ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Modulo Fiscal-->
                                    <div id="Modfiscal<?= $row["idnfitem"]; ?>" style="display: none">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <div class="container-fluid">
                                                            <div class="row">
                                                                <div class="col-md-1">Valores Manuais?</div>
                                                                <div class="col-md-3">
                                                                    <? if ($row["manual"] == 'Y') {
                                                                        $checked = 'checked';
                                                                        $vchecked = 'N';
                                                                        $hidden = "text";
                                                                        $desabmanual = "";
                                                                    } else {
                                                                        $checked = '';
                                                                        $vchecked = 'Y';
                                                                        $hidden = 'hidden';
                                                                        $desabmanual = "disabled='disabled'";
                                                                    } ?>
                                                                    <input title="Considerar valores Manuais." type="checkbox" <?= $checked ?> name="namemanualc" onclick="altcheckmanual('nfitem','manual',<?= $row["idnfitem"] ?>,'<?= $vchecked ?>')">
                                                                </div>
                                                                <div class="col-md-1 nowrap">CFOP:</div>
                                                                <div class="col-md-3">
                                                                    <? if ($row["manual"] == 'Y') { ?>
                                                                        <input <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_cfop" class="size6" type="text" value="<?= $row["cfop"]; ?>">
                                                                    <? } else { ?>
                                                                        <?= $row["cfop"]; ?>
                                                                        <input name="_<?= $ii ?>_u_nfitem_cfop" name="" class="size6" type="hidden" value="<?= $row["cfop"]; ?>">
                                                                    <? } ?>
                                                                </div>
                                                                <div class="col-md-1 nowrap">Cod Item Ped:</div>
                                                                <div class="col-md-3">
                                                                    <input <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_nitemped" class="size6" type="text" value="<?= $row["nitemped"]; ?>">
                                                                </div>
                                                            </div>
                                                            <? if (empty($row["idprodserv"])) { ?>
                                                                <div class="row">
                                                                    <div class="col-md-1">Produto:</div>
                                                                    <div class="col-md-9">
                                                                        <select class="size40" name="_<?= $ii ?>_u_nfitem_idprodserv">
                                                                            <option value=""></option>
                                                                            <?fillselect(PedidoController::buscarProservVendaMaterial(), $row["idprodserv"]); ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            <? } ?>
                                                            <div class="row">
                                                                <div class="col-md-1 nowrap">Cod Prod:</div>
                                                                <div class="col-md-3">
                                                                    <? if ($row["manual"] == 'Y') { ?>
                                                                        <input <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_cprod" class="size6" type="text" value="<?= $row["cprod"]; ?>">
                                                                    <? } else { ?>
                                                                        <input disabled='disabled' name="_<?= $ii ?>_u_nfitem_cprod" class="size6" type="text" value="<?= $row["cprod"]; ?>">
                                                                    <? } ?>
                                                                </div>
                                                                <div class="col-md-1">Vlr UN:</div>
                                                                <div class="col-md-3">
                                                                    <? if ($row["manual"] == 'Y') { ?>
                                                                        <input <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_vlritem" class="size8" type="text" value="<?= $row["vlritem"]; ?>">
                                                                    <? } else { ?>
                                                                        <?= number_format($row["vlritem"], 2, '.', ''); ?>
                                                                    <? } ?>
                                                                </div>
                                                                <div class="col-md-1">UN:</div>
                                                                <div class="col-md-3">
                                                                    <select <?= $desabmanual ?> <?= $disablednf ?> name="_<?= $ii ?>_u_nfitem_un">
                                                                        <option value=""></option>
                                                                        <? fillselect(PedidoController::buscarUnidadeVolume(), $row["un"]); ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-1 nowrap">NCM:</div>
                                                                <div class="col-md-3">
                                                                    <? if ($row["manual"] == 'Y') { ?>
                                                                        <input <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_ncm" class="size6" type="text" value="<?= $row["ncm"]; ?>">
                                                                    <? } else { ?>
                                                                        <input disabled='disabled' name="_<?= $ii ?>_u_nfitem_ncm" class="size6" type="text" value="<?= $row["ncm"]; ?>">
                                                                    <? } ?>
                                                                </div>
                                                                <div class="col-md-1">CEST:</div>
                                                                <div class="col-md-3">
                                                                    <input <?= $desabmanual ?> <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_cest" class="size6" type="text" value="<?= $row["cest"]; ?>">
                                                                </div>
                                                                <div class="col-md-1">ISS:</div>
                                                                <div class="col-md-3">
                                                                    <input <?= $desabmanual ?> <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_iss" class="size6" type="text" value="<?= $row["iss"]; ?>">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-1 nowrap">Aliq. ICMS:</div>
                                                                <div class="col-md-3">
                                                                    <? if ($row["manual"] == 'Y') { ?>
                                                                        <input <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_aliqicms" class="size6" type="text" value="<?= $row["aliqicms"]; ?>">
                                                                    <? } else { ?>
                                                                        <?= $row["aliqicms"]; ?>
                                                                        <input <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_aliqicms" name="" class="size6" type="hidden" value="<?= $row["aliqicms"]; ?>">
                                                                    <? } ?>
                                                                </div>
                                                                <div class="col-md-1">Finalidade:</div>
                                                                <div class="col-md-3">
                                                                    <select <?= $desabmanual ?> <?= $disablednf ?> class="size15" name="_<?= $ii ?>_u_nfitem_finalidade">
                                                                        <option value=""></option>
                                                                        <? fillselect(PedidoController::buscarFinalidadeProdserv(), $row["finalidade"]); ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4"></div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-1">ICMS:</div>
                                                                <div class="col-md-3">
                                                                    <? if ($row["manual"] == 'Y') { ?>
                                                                        <input <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_valicms" class="size6" type="text" value="<?= $row["valicms"]; ?>">
                                                                    <? } else { ?>
                                                                        <?= $row["valicms"]; ?>
                                                                        <input <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_valicms" class="size6" type="hidden" value="<?= $row["valicms"]; ?>" vdecimal>
                                                                    <? } ?>
                                                                </div>
                                                                <div class="col-md-1">Origem:</div>
                                                                <div class="col-md-3">
                                                                    <select <?= $desabmanual ?> <?= $disablednf ?> class="size15" name="_<?= $ii ?>_u_nfitem_origem">
                                                                        <? fillselect(PedidoController::buscarOrigemProdserv(), $row["origem"]); ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4"></div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-1 nowrap">ICMS-Dest:</div>
                                                                <div class="col-md-3">
                                                                    <input type="hidden" class="size6" id="descr<?= $row["idnfitem"]; ?>" value="<?= $row["descr"]; ?>">
                                                                    <? if ($row['indiedest'] == 9) { ?><?= number_format($row['icmsufdest'], 2, '.', ''); ?><? } else {
                                                                                                                                                    echo ("0.00");
                                                                                                                                                } ?>
                                                                </div>
                                                                <div class="col-md-1"> CST:</div>
                                                                <div class="col-md-3">
                                                                    <select <?= $desabmanual ?> <?= $disablednf ?> class="size15" name="_<?= $ii ?>_u_nfitem_cst">
                                                                        <option></option>
                                                                        <? fillselect(PedidoController::buscarSTProdserv(), $row["cst"]); ?>
                                                                    </select>
                                                                </div>
                                                                
                                                            </div>
                                                            <div class="row">                                      
                                                                <div class="col-md-1">BC.%:	</div>
                                                                <div class="col-md-3">
                                                                    <? if ($row["manual"] == 'Y') { ?>
                                                                        <input <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_aliqbasecal" class="size6" type="text" value="<?= $row["aliqbasecal"]; ?>">
                                                                    <? } else { ?>
                                                                        <?= $row["aliqbasecal"]; ?>
                                                                        <input <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_aliqbasecal" name="" class="size6" type="hidden" value="<?= $row["aliqbasecal"]; ?>">
                                                                    <? } ?>

                                                                </div>

                                                                <div class="col-md-1 nowrap">Mod-BC: </div>
                                                                <div class="col-md-3">
                                                                    <select <?= $desabmanual ?> <?= $disablednf ?> class="size15" name="_<?= $ii ?>_u_nfitem_modbc">
                                                                        <? fillselect(PedidoController::buscarModbcProdserv(), $row['modbc']); ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4"></div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-1">IPI:</div>
                                                                <div class="col-md-3">
                                                                    <? if ($row["manual"] == 'Y') { ?>
                                                                        <input <?= $readonlynf ?> id="valipi<?= $row["idnfitem"]; ?>" name="" class="size6" type="text" value="<?= $row["valipi"]; ?>">
                                                                    <? } else { ?>
                                                                        <?= $row["valipi"]; ?>
                                                                        <input id="valipi<?= $row["idnfitem"]; ?>" name="" class="size6" type="hidden" value="<?= $row["valipi"]; ?>" readonly='readonly'>
                                                                    <? } ?>
                                                                </div>
                                                                <div class="col-md-1">Aliq IPI:</div>
                                                                <div class="col-md-3 ">
                                                                    <input <?= $desabmanual ?> <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_aliqipi" class="size6" type="text" value="<?= $row["aliqipi"]; ?>">
                                                                </div>
                                                                <div class="col-md-1 nowrap">CST PIS:</div>
                                                                <div class="col-md-3">
                                                                    <select <?= $desabmanual ?> <?= $disablednf ?> class="size15" name="_<?= $ii ?>_u_nfitem_piscst">
                                                                        <? fillselect(PedidoController::buscarCstPisProdserv(), $row['piscst']); ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        
                                                            <div class="row">
                                                                <div class="col-md-1">PIS:</div>
                                                                <? if ($row["manual"] == 'Y') {
                                                                    if (trim($row["pis"]) == "" or  $row['piscst']=='06') {
                                                                        //$pisaux = ($row["aliqpis"] / 100) * $row["total"];
                                                                        $pisaux = 0.00;
                                                                    } else {
                                                                        $pisaux = $row["pis"];
                                                                    }
                                                                    if (trim($row["bcpis"]) == "") {
                                                                        $bcpisaux = $row["total"];
                                                                    } else {
                                                                        $bcpisaux = $row["bcpis"];
                                                                    } ?>
                                                                    <div class="col-md-3 ">
                                                                        <input <?= $desabmanual ?> <?= $readonlynf ?> id="valpis<?= $row["idnfitem"]; ?>" name="" placeholder="PIS" class="size6" type="text" value="<?= $pisaux; ?>">
                                                                    </div>

                                                                <? } else {

                                                                    if ($row["piscst"] == '04'or $row["piscst"] == '05' or $row["piscst"] == '06' or  $row["piscst"] == '07' or $row["piscst"] == '08' or $row["piscst"] == '09') {
                                                                        $vpis = '0.00';
                                                                        $bcpisaux = '0.00';
                                                                    }else{
                                                                        $rowpis = PedidoController::buscarPisPorNfitem($row["idnfitem"]);
                                                                        $vpis = $rowpis['vPIS'];
                                                                        $bcpisaux = $rowpis['vBC'];
                                                                    }                                                                  
                                                                    ?>
                                                                    <div class="col-md-3 ">
                                                                        <?= $vpis ?>
                                                                        <input <?= $desabmanual ?> <?= $readonlynf ?> id="valpis<?= $row["idnfitem"]; ?>" name="" class="size6" type="hidden" value="<?= $vpis ?>" readonly='readonly'>
                                                                    </div>
                                                                <? } ?>
                                                                <div class="col-md-1 nowrap"> BC PIS: </div>
                                                                <div class="col-md-3">
                                                                    <input <?= $desabmanual ?> <?= $readonlynf ?> id="valbcpis<?= $row["idnfitem"]; ?>" name="" placeholder="BC.PIS" class="size15" type="text" value="<?= $bcpisaux; ?>">
                                                                </div>
                                                                <div class="col-md-1">Aliq PIS:</div>
                                                                <div class="col-md-3 ">
                                                                    <input <?= $desabmanual ?> <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_aliqpis" class="size6" type="text" value="<?= $row["aliqpis"]; ?>">
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-1">COFINS:</div>
                                                                <? if ($row["manual"] == 'Y') {
                                                                    if (trim($row["cofins"]) == ""  or  $row['piscst']=='06') {
                                                                    // $cofinsaux = ($row["aliqcofins"] / 100) * $row["total"];
                                                                    $cofinsaux = 0.00;
                                                                    } else {
                                                                        $cofinsaux = $row["cofins"];
                                                                    }

                                                                    if (trim($row["bccofins"]) == "") {
                                                                        $bccofinsaux = $row["total"];
                                                                    } else {
                                                                        $bccofinsaux = $row["bccofins"];
                                                                    } ?>
                                                                    <div class="col-md-3">
                                                                        <input <?= $readonlynf ?> id="valcofins<?= $row["idnfitem"]; ?>" name="" placeholder="COFINS" class="size6" type="text" value="<?= $cofinsaux; ?>">
                                                                    </div>

                                                                <? } else {

                                                                        if ($row["confinscst"] == '04'or $row["confinscst"] == '05' or $row["confinscst"] == '06' or  $row["confinscst"] == '07' or $row["confinscst"] == '08' or $row["confinscst"] == '09') {
                                                                            $vCOFINS = '0.00';
                                                                            $bccofinsaux = '0.00';

                                                                        }else{
                                                                                                                                
                                                                            $rowcof = PedidoController::buscarCofinsPorNfitem($row["idnfitem"]);
                                                                            $vCOFINS = $rowcof['vCOFINS'];
                                                                            $bccofinsaux=$rowcof['vBC'];
                                                                        }                                                         
                                                                    ?>

                                                                    <div class="col-md-3">
                                                                        <?= $vCOFINS ?>
                                                                        <input <?= $readonlynf ?> id="valcofins<?= $row["idnfitem"]; ?>" name="" class="size6" type="hidden" value="<?= $vCOFINS ?>" readonly='readonly'>
                                                                    </div>
                                                                <? } ?>
                                                                <div class="col-md-1 nowrap">BC Cofins:</div>
                                                                <div class="col-md-3">
                                                                    <input <?= $readonlynf ?> id="valbccofins<?= $row["idnfitem"]; ?>" name="" placeholder="BC.COFINS" class="size15" type="text" value="<?= $bccofinsaux; ?>">
                                                                </div>
                                                                <div class="col-md-1">Aliq Cofins:</div>
                                                                <div class="col-md-3 ">
                                                                    <input <?= $desabmanual ?> <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_aliqcofins" class="size6" type="text" value="<?= $row["aliqcofins"]; ?>">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-1 nowrap">Deson.(R$):</div>
                                                                <div class="col-md-3">
                                                                    <? if ($row["manual"] == 'Y') { ?>
                                                                        <input <?= $readonlynf ?> id="vicmsdeson<?= $row["idnfitem"]; ?>" name="" class="size6" type="text" value="<?= $row["vicmsdeson"]; ?>">
                                                                    <? } else { ?>
                                                                        <?= number_format($row["vicmsdeson"], 2, '.', ''); ?>
                                                                    <? } ?>
                                                                </div>
                                                                <div class="col-md-1 nowrap">CST IPI:</div>
                                                                <div class="col-md-3">
                                                                    <select <?= $desabmanual ?> <?= $disablednf ?> class="size15" name="_<?= $ii ?>_u_nfitem_ipint">
                                                                        <? fillselect(PedidoController::buscarCstIpiProdserv(), $row['ipint']); ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4"></div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-1 nowrap">BC ICMS:</div>
                                                                <div class="col-md-3">
                                                                    <? if ($row["manual"] == 'Y') { ?>
                                                                        <input <?= $readonlynf ?> id="basecalc<?= $row["idnfitem"]; ?>" name="" class="size6" type="text" value="<?= $row["basecalc"]; ?>">
                                                                    <? } else {
                                                                        echo ($row["basecalc"]); ?>
                                                                        <input id="basecalc<?= $row["idnfitem"]; ?>" name="" class="size6" type="hidden" value="<?= $row["basecalc"]; ?>">
                                                                    <? } ?>
                                                                </div>
                                                                <div class="col-md-1 nowrap">CST Cofins:</div>
                                                                <div class="col-md-3">
                                                                    <select <?= $desabmanual ?> <?= $disablednf ?> class="size15" name="_<?= $ii ?>_u_nfitem_confinscst">
                                                                        <? fillselect(PedidoController::buscarCstCofinsProdserv(), $row['confinscst']); ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4"></div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-1 nowrap">Valor Seguro:</div>
                                                                <div class="col-md-3">
                                                                    <? if ($row["manual"] == 'Y') { ?>
                                                                        <input <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_vseg" class="size6" type="text" value="<?= $row["vseg"]; ?>">
                                                                    <? } else { ?>
                                                                        <input disabled='disabled' name="_<?= $ii ?>_u_nfitem_vseg" class="size6" type="text" value="<?= $row["vseg"]; ?>">
                                                                    <? } ?>
                                                                </div>
                                                                <div class="col-md-1 nowrap">Outras Despesas:</div>
                                                                <div class="col-md-3">
                                                                    <? if ($row["manual"] == 'Y') { ?>
                                                                        <input <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_voutro" class="size6" type="text" value="<?= $row["voutro"]; ?>">
                                                                    <? } else { ?>
                                                                        <input disabled='disabled' name="_<?= $ii ?>_u_nfitem_voutro" class="size6" type="text" value="<?= $row["voutro"]; ?>">
                                                                    <? } ?>
                                                                </div>
                                                                <div class="col-md-1 nowrap">Valor Total:</div>
                                                                <div class="col-md-3">
                                                                    <? if ($row["manual"] == 'Y') { ?>
                                                                        <input <?= $readonlynf ?> name="_<?= $ii ?>_u_nfitem_total" class="size6" type="text" value="<?= $row["total"]; ?>">
                                                                    <? } else {
                                                                        echo number_format($row["total"], 2, '.', '');
                                                                    } ?>
                                                                </div>
                                                            </div>
                                                            <?
                                                            $desobs = 'Obs:';                                                       
                                                            $ront =PedidoController::buscarFinnfePorNF($_1_u_nf_idnf);
                                                            if ($ront['finnfe']) {
                                                                $desobs = 'Mot. Devolução:'; ?>
                                                                <div class="row">
                                                                    <div class="col-md-4 nowrap">% Mercadoria Devolvida:</div>
                                                                    <div class="col-md-2">
                                                                        <? if ($row["manual"] == 'Y') { ?>
                                                                            <input <?= $readonlynf ?> id="pDevol<?= $row["idnfitem"]; ?>" name="" class="size6" type="text" value="<?= $row["pDevol"]; ?>">
                                                                        <? } else {
                                                                            echo ($row["pDevol"]); ?>
                                                                            <input id="pDevol<?= $row["idnfitem"]; ?>" name="" class="size6" type="hidden" value="<?= $row["pDevol"]; ?>">
                                                                        <? } ?>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-4 nowrap">IPI devolvido:</div>
                                                                    <div class="col-md-2">
                                                                        <? if ($row["manual"] == 'Y') { ?>
                                                                            <input <?= $readonlynf ?> id="vIPIDevol<?= $row["idnfitem"]; ?>" name="" class="size6" type="text" value="<?= $row["vIPIDevol"]; ?>">
                                                                        <? } else {
                                                                            echo ($row["vIPIDevol"]); ?>
                                                                            <input id="vIPIDevol<?= $row["idnfitem"]; ?>" name="" class="size6" type="hidden" value="<?= $row["vIPIDevol"]; ?>">
                                                                        <? } ?>
                                                                    </div>
                                                                </div>
                                                            <? } ?>
                                                            <div class="row">
                                                                <div class="col-md-2"><?= $desobs ?></div>
                                                                <div class="col-md-8 ">
                                                                    <? if ($row["manual"] == 'Y') { ?>
                                                                        <textarea <?= $readonlynf ?> class="caixa" id="obs<?= $row["idnfitem"]; ?>" name="" style="width: 340px; height: 80px;"><?= $row["obs"] ?></textarea>
                                                                    <? } else { ?>
                                                                        <textarea disabled='disabled' class="caixa" id="obs<?= $row["idnfitem"]; ?>" name="" style="width: 340px; height: 80px;"><?= $row["obs"] ?></textarea>
                                                                    <? } ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modulo Fiscal fim-->
                                <?
                                } //while($r=  mysqli_fetch_assoc($rest)){

                                $vtotal = $vtotal + tratanumero($_1_u_nf_frete);
                                $_1_u_nf_total = number_format($vtotal, 2, '.', '');

                                if ($_1_u_nf_verdesc == "Y") {
                                    $checked = 'checked';
                                    $vchecked = 'N';
                                } else {
                                    $checked = '';
                                    $vchecked = 'Y';
                                } ?>

                        </tbody>
                    </table>
                    <table style='float: right;'>
                        <tbody>
                            <? if ($_1_u_nf_moeda == 'REAL') { ?>
                                <tr>
                                    <td colspan="8"></td>
                                    <th align="right">Sub-Total:</th>
                                    <td align="right"><?= number_format(tratanumero($vsubtotal), 2, ',', '.'); ?></td>
                                    <td style="width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                </tr>
                            <? } ?>
                            <? if ($_1_u_nf_moeda == 'REAL') { ?>
                                <tr>
                                    <td colspan="8"></td>
                                    <th align="right">Desoneração:</th>
                                    <td align="right">
                                        <font color="RED" style="font-weight: bold;"><?= number_format(tratanumero($vtotaldeson), 2, ',', '.'); ?> </font>
                                    </td>
                                    <td style="width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan="8"></td>
                                    <th align="right">Desconto:</th>
                                    <td align="right"><?= number_format(tratanumero($vdesconto), 2, ',', '.'); ?></td>
                                    <td style="width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                </tr>
                            <? } ?>

                            <tr>
                                <td colspan="8"></td>
                                <th title="<?=PedidoController::$strObsFrete?>" align="right" class="nowrap">Frete

                                    <select <?= $disablednf ?> class="size8" name="_1_<?= $_acao ?>_nf_modfrete">
                                        <? fillselect(PedidoController::$tipoFrete,$_1_u_nf_modfrete); ?>
                                    </select>:

                                </th>
                                <td align="right">
                                    <font><?= number_format(tratanumero($_1_u_nf_frete), 2, ',', '.'); ?></font>
                                </td>
                                <td style="width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            </tr>
                            <? if (!empty($valordevolucao)) { ?>
                                <tr>
                                    <td colspan="8"></td>
                                    <th align="right" class="nowrap">NF. Devolução:
                                    </th>
                                    <td align="right">
                                        <font><?= number_format(tratanumero($valordevolucao), 2, ',', '.'); ?></font>
                                    </td>
                                    <td style="width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                </tr>
                                <? }
                                if ($_1_u_nf_moeda == 'REAL') {
                                    if ($vtotalicms > 0 and $_1_u_nf_tpnf == 0) { ?>
                                    <tr>
                                        <td colspan="8"></td>
                                        <th align="right">ICMS:</th>
                                        <td align="right">
                                            <?= number_format(tratanumero($vtotalicms), 2, ',', '.'); ?>
                                        </td>
                                        <td style="width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    </tr>
                                <? }
                                }
                                if ($vtotalii > 0 and $_1_u_nf_tpnf == 0) { ?>
                                <tr>
                                    <td colspan="8"></td>
                                    <th align="right">II:</th>
                                    <td align="right">
                                        <?= number_format(tratanumero($vtotalii), 2, ',', '.'); ?>
                                    </td>
                                    <td style="width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                </tr>

                                <? }
                                if ($_1_u_nf_moeda == 'REAL') {
                                    if ($vtotalipi > 0) { ?>
                                    <tr>
                                        <td colspan="8"></td>
                                        <th align="right">IPI:</th>
                                        <td align="right">
                                            <?= number_format(tratanumero($vtotalipi), 2, ',', '.'); ?>
                                        </td>
                                        <td style="width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    </tr>
                                <? }
                                }
                                if (!empty($voutro)) { ?>
                                <tr>
                                    <td colspan="8"></td>
                                    <th align="right">Outras Despesas:</th>
                                    <td align="right">
                                        <?= number_format(tratanumero($voutro), 2, ',', '.'); ?>
                                    </td>
                                    <td style="width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                </tr>
                            <? }
                                if (!empty($voutro)) { ?>
                                <tr>
                                    <td colspan="8"></td>
                                    <th align="right">Seguro:</th>
                                    <td align="right">
                                        <?= number_format(tratanumero($vseg), 2, ',', '.'); ?>
                                    </td>
                                    <td style="width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                </tr>
                            <? } ?>
                            <?
                            
                                $moedanf = PedidoController::buscarMoedaNF($_1_u_nf_idnf);
                                $moedanf1 = $moedanf['moeda'];
                            ?>
                            <tr>
                                <td colspan="8"></td>
                                <th align="right">Total (<? if ($moedanf1 == 'USD') {
                                                                echo "USD";
                                                            } elseif ($moedanf1 == 'EUR') {
                                                                echo "EUR";
                                                            } else {
                                                                echo "BRL";
                                                            } ?>):</th>
                                <td class="idbox" align="right">
                                    <? if ($_1_u_nf_tpnf == 0 and $uf == 'EX') {
                                        echo "<!-- vtotal = vsubtotal  vtotalicms + vtotalipi + voutro + vtotalii -->";
                                        echo "<!-- vtotal = " . $vsubtotal . " + " . $vtotalicms . " + " . $vtotalipi . " + " . $voutro . " +" . tratanumero($_1_u_nf_frete) . " -->";
                                        $vtotal = $vsubtotal + $vtotalicms + $vtotalipi + $voutro + tratanumero($_1_u_nf_frete);
                                    } ?>
                                    <input name="_1_<?= $_acao ?>_nf_icms" size="8" type="hidden" value="<?= $vtotalicms ?>">
                                    <input name="_1_<?= $_acao ?>_nf_ipi" size="8" type="hidden" value="<?= $vtotalipi ?>">
                                    <input name="_1_<?= $_acao ?>_nf_bc" size="8" type="hidden" value="<?= $vtotalbc ?>">
                                    <input name="_1_<?= $_acao ?>_nf_subtotal" id="vlrsubtotal" size="8" type="hidden" value="<?= $vsubtotal ?>" vdecimal>
                                    <input name="_1_<?= $_acao ?>_nf_total" id="vlrtotal" size="8" type="hidden" value="<?= $vtotal ?>" vdecimal>
                                    <input name="_1_<?= $_acao ?>_nf_totalext" id="vlrtotalext" size="8" type="hidden" value="<?= $vtotalext ?>" vdecimal>
                                    <? if ($_1_u_nf_moeda == 'REAL') { ?>
                                        <?= number_format(tratanumero($vtotal), 2, ',', '.'); ?>
                                    <? } else { ?>
                                        <?= number_format(tratanumero($vtotalext), 2, ',', '.'); ?>
                                    <? } ?>
                                </td>
                                <td style="width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            </tr>
                            <? if ($_1_u_nf_moeda != 'REAL') { ?>
                                <tr>
                                    <td colspan="8"></td>
                                    <th align="right">Total (BRL):</th>
                                    <td class="idbox" align="right">
                                        <? if ($_1_u_nf_tpnf == 0 and $uf == 'EX') {
                                            echo "<!-- vtotal = vsubtotal  vtotalicms + vtotalipi + voutro + vtotalii -->";
                                            echo "<!-- vtotal = " . $vsubtotal . " + " . $vtotalicms . " + " . $vtotalipi . " + " . $voutro . " -->";
                                            $vtotal = $vsubtotal + $vtotalicms + $vtotalipi + $voutro;
                                        } ?>
                                        <input name="_1_<?= $_acao ?>_nf_icms" size="8" type="hidden" value="<?= $vtotalicms ?>">
                                        <input name="_1_<?= $_acao ?>_nf_ipi" size="8" type="hidden" value="<?= $vtotalipi ?>">
                                        <input name="_1_<?= $_acao ?>_nf_bc" size="8" type="hidden" value="<?= $vtotalbc ?>">
                                        <input name="_1_<?= $_acao ?>_nf_subtotal" id="vlrsubtotal" size="8" type="hidden" value="<?= $vsubtotal ?>" vdecimal>
                                        <input name="_1_<?= $_acao ?>_nf_total" id="vlrtotal" size="8" type="hidden" value="<?= $vtotal ?>" vdecimal>
                                        <input name="_1_<?= $_acao ?>_nf_totalext" id="vlrtotalext" size="8" type="hidden" value="<?= $vtotalext ?>" vdecimal>
                                        <?= number_format(tratanumero($vtotal), 2, ',', '.'); ?>
                                    </td>
                                    <td style="width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                </tr>
                            <? } ?>                        
                        <?

                                if (count($arrayaux) == 0) {
                                    $arrayaux = 0;
                                } else {
                                    $arrayaux = json_encode($arrayaux);
                                }
                            } else {
                                $arrayaux = 0;
                            } //if($qtdres>0){  
                        ?>
                        </tbody>
                    </table>
                    <table class="hidden" id="modeloNovoIten">
                        <tr class='dragExcluir nfitem'>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                <input type="hidden" name="#nameidnfitem">
                                <input type="hidden" name="#nameord" value="">
                                <input style=" border: 1px solid silver;" autocomplete="off" name="#namequantidade" title="Qtd" placeholder="Qtd" type="text" size="2">
                            </td>
                            <td class="nowrap" colspan="4">
                                <input type="text" name="#nameidprodserv" style="width: 50em;" class="idprodserv" cbvalue placeholder="Informe o produto">
                                <select name="#nameidprodservformula" class="idprodservformula hidden size15 ">
                                    <option value=""></option>
                                </select>
                                <input type="text" name="#nameprodservdescr" style="width: 50em;" class="prodservdescr hidden" placeholder="Digite o nome do produto">
                                <button name="#botaodescritivo" type="button" class="btn btn-default btn-xs" onclick="alterainput(this,'#botaoidprodserv','#nameprodservdescr','#nameidprodserv')" title="Mudar a inserção para item descritivo">
                                    <i class="fa fa-plus"></i>Descritivo
                                </button>
                                <button name="#botaoidprodserv" type="button" class="btn btn-default btn-xs hidden" onclick="alterainput(this,'#botaodescritivo','#nameidprodserv','#nameprodservdescr')" title="Mudar a inserção para item cadastrado">
                                    <i class="fa fa-plus"></i>Item Cadastrado
                                </button>
                            </td>

                            <td colspan='6'></td>
                        
                        </tr>
                    </table>

                    <div class="row">
                        <? if (($_1_u_nf_status == "INICIO" or $_1_u_nf_status == "SOLICITADO"  or $_1_u_nf_status == "PENDENTE" or $_1_u_nf_status == "PEDIDO" or $_1_u_nf_status == "EXPEDICAO") and (!empty($_1_u_nf_idnatop) and !empty($_1_u_nf_idenderecofat))) { ?>
                            <div class="col-md-1 col-md-offset-0">
                                <i id="novoitem" class="fa fa-plus-circle fa-1x verde btn-lg pointer" onclick="novoItem()" title="Inserir novo Item"></i>


                            
                            </div>
                        <? } ?>
                    </div>
                </div>
            </div>
        </div>
    <? } //function listaitens(){

    function rodape()
    {
        global $vtotalicms, $vtotalipi, $_1_u_nf_idpessoafat, $_1_u_nf_idnf,$nf_idempresa,$_1_u_nf_idempresa, $_1_u_nf_idnfe, $_1_u_nf_controle, $_1_u_nf_modfrete, $_1_u_nf_idpessoa, $_1_u_nf_inffrete, $_1_u_nf_idtransportadora, $_1_u_nf_prazo, $_1_u_nf_obsenvio, $_1_u_nf_idendereco, $_1_u_nf_idenderecofat, $_1_u_nf_ufsaidapais, $_1_u_nf_xlocexporta, $_1_u_nf_xlocdespacho, $_1_u_nf_obs, $_1_u_nf_nitemped, $_1_u_nf_diasentrada, $_1_u_nf_dtemissao, $_1_u_nf_dhsaient, $_1_u_nf_parcelas, $_1_u_nf_intervalo, $_1_u_nf_envionfe, $_1_u_nf_infcpl, $_1_u_nf_obsint, $_1_u_nf_obsinterna, $_1_u_nf_formapgto, $_1_u_nf_idformapagamento, $_1_u_nf_idagencia, $vicmsufremet, $vicmsufdest, $icmsufdest_ufremet, $_1_u_nf_tiponf, $_1_u_nf_protocolonfe, $pedidopend, $_1_u_nf_sped, $_1_u_nf_emaildanfe, $_1_u_nf_emailxml, $_1_u_nf_rastreador, $_1_u_nf_enviarastreador, $_1_u_nf_emaildadosnfe, $_1_u_nf_envioemail, $_1_u_nf_enviaemailped, $_1_u_nf_logemail, $_1_u_nf_emailorc, $_1_u_nf_envioemailorc, $_1_u_nf_status, $_1_u_nf_respenvio, $_1_u_nf_nnfe, $_1_u_nf_geracontapagar, $_1_u_nf_indpag, $_1_u_nf_validade, $_1_u_nf_dtemissao, $_1_u_nf_dhsaient, $_1_u_nf_recibo, $rowpgto, $_1_u_nf_implocal, $_1_u_nf_impitem, $_1_u_nf_idendrotulo, $_1_u_nf_impendereco, $_1_u_nf_idcontato, $_1_u_nf_envio, $_acao, $_1_u_nf_comissao, $comissionado, $_1_u_nf_entrega, $_1_u_nf_peso, $_1_u_nf_custoenvio, $_1_u_nf_total, $_1_u_nf_emailboleto, $_1_u_nf_tipoenvioemail, $_1_u_nf_emaildadosnfemat, $_1_u_nf_infcorrecao, $_1_u_nf_idnatop, $_1_u_nf_qvol, $_1_u_nf_esp, $_1_u_nf_marca, $_1_u_nf_nvol, $_1_u_nf_pesob, $_1_u_nf_pesol,
            $_1_u_nf_statustransf, $frdesoneracao, $vtotaldeson, $vtotal, $_1_u_nf_frete, $_1_u_nf_proporcional, $i, $_1_u_nf_obspedido, $arrnatop, $mensagem, $pagvalmodulo, $ctesVinculadasNF,
            $disabled, $disablednfStatus, $readonlynfStatus;
        if ($_1_u_nf_envionfe == 'CONCLUIDA') {
            $disablednf = "disabled='disabled'";
            $readonlynf = "readonly='readonly'";
        } else {
            $disablednf = "";
            $readonlynf = "";
        } ?>

    <div class="row">
        <? if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1  or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 15) { ?>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <table>
                            <tr>
                                <td>Observação Pedido</td>
                                <td>   
                                    <? if($_1_u_nf_status != 'CONCLUIDO' && $_1_u_nf_status != 'CANCELADO') { ?>    
                                        <i class="fa fa-pencil btn-lg pointer" title='Editar Observação Pedido' onclick="alteraValorObs('obspedido','<?= preg_replace( "/\r|\n/", "", $_1_u_nf_obspedido ); ?>','modulohistorico',<?=$_1_u_nf_idnf ?>,'Observação Pedido:')"></i>
                                    <? } ?>
                                </td>
                                <td>
                                <?
                                    $ListarHistoricoModal = NfEntradaController::buscarHistoricoAlteracao($_1_u_nf_idnf, 'obspedido');
                                    $qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
                                    if ($qtdvh > 0) 
                                    {
                                        ?>
                                        
                                        <div class="historicoObs" >
                                            <i title="Histórico do Envio" class="fa btn-lg fa-info-circle preto pointer hoverazul tip" data-target="webuiPopover0"></i>
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
                                                           <th scope="col">Histórico</th>
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
                                                                <td><?=$historicoModal['valor'] ?></td>
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
                                    } else {
                                        echo '&nbsp;';
                                    }
                                    ?>
                                    
                                </td>
                            </tr>
                        </table>
                    </div>
                    <? if (!empty($_1_u_nf_obspedido)) {
                        $textareaped = "hidden";
                        $obslistped = "";
                    } else {
                        $obslistped = "hidden";
                        $textareaped = "";
                        $collpaseobs = "collapse";
                    } ?>
                    <div class="panel-body <?= $collpaseobs ?>" id="obspedido">
                        <table style="width: 100%;">
                            <tr class="<?= $obslistped ?>" id='obstxtped'>
                                <td>
                                    <font color="red">
                                    <?= nl2br($_1_u_nf_obspedido) ?>
                                    </font>
                                </td>
                            </tr>
                            <tr class="<?= $textareaped ?>" id='obsinputped'>
                                <td><textarea class="caixa" style="width: 100; height: 85px;" name="_1_<?= $_acao ?>_nf_obspedido"><?= $_1_u_nf_obspedido ?></textarea></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        <? } ?>
        <? if ($_1_u_nf_status == 'INICIO' || $_1_u_nf_status == 'SOLICITADO' || $_1_u_nf_status == 'PENDENTE') { ?>
            <div <? if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1 or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 15) { ?> class="col-md-6" <? } else { ?>class="col-md-12" <? } ?>>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <table>
                            <tr>
                                <td>Observação Orçamento</td>
                                <td><a class="fa fa-pencil hoverazul btn-lg pointer" onclick="editarobs();" title="Editar observação"></a></td>
                            </tr>
                        </table>
                    </div>
                    <div class="panel-body ">
                        <? //colocar o texto da observação de acordo com o estado do cliente
                        if (!empty($_1_u_nf_idenderecofat)) {

                            
                           
                            $rowempresa = PedidoController::buscarInfEmpresaNF($nf_idempresa);
                           
                            $ufcliente = traduzid("endereco", "idendereco", "uf", $_1_u_nf_idenderecofat);

                            $natoptipo = traduzid("natop", "idnatop", "natoptipo", $_1_u_nf_idnatop);

                            $textoobs='';
                            if($natoptipo=='transferencia'){

                                //$textoobs .= "Operação entre estabelecimentos de mesma titularidade não sujeita a incidência de ICMS, conforme decisão judicial exarada nos autos do MS 5029461-54.2022.8.13.0702, em trâmite perante a 3ª Vara da Fazenda Pública e Autarquias da Comarca de Uberlândia/MG.";
                                $textoobs .=" Operação entre estabelecimentos de mesma titularidade não sujeita a incidência de ICMS, conforme decisão judicial exarada nos autos do MS 5052149-39.2024.8.13.0702, em trâmite perante a 1ª Vara da Fazenda Pública e Autarquias da Comarca de Uberlândia/MG";
                            }elseif ($ufcliente == $rowempresa['uf']) {

                                $textoobs .= $rowempresa["descricaoicms"];    

                            }elseif($icmsufdest_ufremet == "Y") {
                                $textoobs .= "*DESONERAÇÃO APLICADA: referente à redução de 60% da base de cálculo do ICMS previsto neste(s) item(s), conforme convênio ICMS 100/97. Esta desoneração será aplicada no valor bruto do produto no corpo da nota fiscal e informada no rodapé da mesma.";
                            }else{
                                $textoobs = $rowempresa["pedidoobs"];
                            }

                        }

                        //$_1_u_nf_obs = (!empty($_1_u_nf_obs))? $_1_u_nf_obs : "*DESCONTO aplicado: Referente à  redução de 60% da base de cálculo do ICMS previsto neste(s) item(s), conforme convàªnio ICMS 100/97. Este desconto será aplicado no valor bruto do produto no corpo da Nota Fiscal.\n\nFRETE CIF: Faturamento acima de R$ 2.000,00.\n\nFRETE FOB: O valor referente ao frete poderá ser cobrado junto a nota fiscal, ou ser enviado via Sedex a cobrar (neste último, o cliente deverá retirar na agência dos Correios).";
                        $_1_u_nf_obs = (!empty($_1_u_nf_obs)) ? $_1_u_nf_obs : $textoobs;

                        if (!empty($_1_u_nf_obs)) {
                            $textareaobs = "hidden";
                            $obslistobs = "";
                        } else {
                            $obslistobs = "hidden";
                            $textareaobs = "";
                        } ?>
                        <table>
                            <tr class="<?= $obslistobs ?>" id='obstxtobs'>
                                <td>
                                    <?= nl2br($_1_u_nf_obs) ?>
                                </td>
                            </tr>
                            <tr class="<?= $textareaobs ?>" id='obsinputobs'>
                                <td><textarea class="caixa" style="width: 555px;  height: 85px;" name="_1_<?= $_acao ?>_nf_obs"><?= $_1_u_nf_obs ?></textarea></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        <? } ?>
        <? if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1  or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 15) { ?>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <table>
                            <tr>
                                <td>Observação Interna</td>
                                <td>
                                    <? if($_1_u_nf_status != 'CONCLUIDO' && $_1_u_nf_status != 'CANCELADO') { ?>    
                                        <i class="fa fa-pencil btn-lg pointer" title='Editar Observação Interna' onclick="alteraValorObs('obsinterna','<?= preg_replace( "/\r|\n/", "", $_1_u_nf_obsinterna ); ?>','modulohistorico',<?=$_1_u_nf_idnf ?>,'Observação Interna:')"></i>
                                    <? } ?>
                                </td>
                                <td>
                                    <?
                                    $ListarHistoricoModal = NfEntradaController::buscarHistoricoAlteracao($_1_u_nf_idnf, 'obsinterna');
                                    $qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
                                    if ($qtdvh > 0) 
                                    {
                                        ?>
                                        
                                        <div class="historicoObs" >
                                            <i title="Histórico do Envio" class="fa btn-lg fa-info-circle preto pointer hoverazul tip" data-target="webuiPopover0"></i>
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
                                                           <th scope="col">Histórico</th>
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
                                                                <td><?=$historicoModal['valor'] ?></td>
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
                                    } else {
                                        echo '&nbsp;';
                                    }
                                    ?>
                                    
                                </td>
                            </tr>
                        </table>
                    </div>
                    <? if (!empty($_1_u_nf_obsinterna)) {
                        $textareaint = "hidden";
                        $obslistint = "";
                    } else {
                        $obslistint = "hidden";
                        $textareaint = "";
                        $collpaseint = "collapse";
                    } ?>
                    <div class="panel-body <?= $collpaseint ?>" id="obsinterna">
                        <table style="width: 100%;">
                            <tr class="<?= $obslistint ?>" id='obstxt'>
                                <td> 
                                    <font color="red">
                                    <?= nl2br($_1_u_nf_obsinterna) ?>
                                    </font>
                                </td>
                            </tr>
                            <tr class="<?= $textareaint ?>" id='obsinput'>
                                <td><textarea class="caixa" style="width: 100; height: 85px;" name="_1_<?= $_acao ?>_nf_obsinterna"><?= $_1_u_nf_obsinterna ?></textarea></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        <? } ?>
    </div>
    <div class="row">
        <? if ($_1_u_nf_status == 'ENVIAR' or $_1_u_nf_status == 'ENVIADO' and !empty($_1_u_nf_idnatop)) {
            $edita = 'disabled';
            $controlacollapse = '';
        } else {
            $controlacollapse = '';
        } ?>
        <div class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" href="#collapsedados">
                Dados financeiros
            </div>
            <div class="panel-body " id="collapsedados">
                <div class="col-md-5">
                    <div class="panel panel-default">
                        <div class="panel-heading" data-toggle="collapse" href="#collapsepag">Pagamento</div>
                        <div class="panel-body " id="collapsepag">
                            <table>
                                <? if (!empty($_1_u_nf_nnfe)) { ?>
                                    <tr>
                                        <td align="right">Nº NFe:</td>
                                        <td><input readonly='readonly' style="width: 100px;" name="_1_<?= $_acao ?>_nf_nnfe" type="text" value="<?= $_1_u_nf_nnfe ?>"></td>
                                    </tr>
                                <? } ?>
                                <tr>
                                    <? //if($_1_u_nf_status != 'INICIO' and $_1_u_nf_status != 'SOLICITADO' and $_1_u_nf_status != 'ORCAMENTO' and $_1_u_nf_status != 'PEDIDO' and $_1_u_nf_status != 'PRODUCAO' and $_1_u_nf_status != 'EXPEDICAO'){ 
                                    ?>
                                    <td align="right">Emissão:</td>
                                    <td colspan="3" style="font-weight: 600;">
                                        <? if (!empty($disablednf)) {
                                            echo ($_1_u_nf_dtemissao); ?>
                                            <input  class='size15' autocomplete="off" name="_1_<?= $_acao ?>_nf_dtemissao" id="fdata" type="hidden" size="15" value="<?= $_1_u_nf_dtemissao ?>">
                                        <? } else { ?>
                                           
                                            <input  name="_1_<?= $_acao ?>_nf_dtemissao" class="calendariodatahora size15" value="<?= $_1_u_nf_dtemissao ?>" autocomplete="off">
                                        <? } ?>
                                    </td>
                                    <? //} 
                                    //if($_1_u_nf_status != 'PEDIDO' and $_1_u_nf_status != 'PRODUCAO'){
                                    ?>
                                    <td align="right" nowrap>Gera Parcela:</td>
                                    <td>
                                        <select <?= $edita ?> style="width:60px;" name="_1_<?= $_acao ?>_nf_geracontapagar">
                                            <? fillselect(PedidoController::$varSimNao, $_1_u_nf_geracontapagar); ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <? if ($_1_u_nf_status != 'INICIO' and $_1_u_nf_status != 'SOLICITADO' and $_1_u_nf_status != 'PENDENTE' and $_1_u_nf_status != 'ORCAMENTO' and $_1_u_nf_status != 'PEDIDO' and $_1_u_nf_status != 'PRODUCAO') { ?>
                                        <td align="right">Saída/Entrada:</td>
                                        <td colspan="3">
                                            <? if (!empty($disablednf)) {
                                                echo ($_1_u_nf_dhsaient);
                                            ?>
                                                <input <?= $edita ?> class='size15' autocomplete="off" name="_1_<?= $_acao ?>_nf_dhsaient" id="fdata" type="hidden" size="15" value="<?= $_1_u_nf_dhsaient ?>">
                                            <? } else { ?>
                                                <input <?= $edita ?> name="_1_<?= $_acao ?>_nf_dhsaient" class="calendariodatahora size15" value="<?= $_1_u_nf_dhsaient ?>" autocomplete="off">
                                            <? } ?>
                                        </td>
                                    <? }
                                    
                                    if (($comissionado == "Y") or $_1_u_nf_comissao == "Y") {
                                ?>
                                        <td align="right" nowrap>Comissão:</td>
                                        <td>
                                            <select <?= $edita ?> class='size5' name="_1_<?= $_acao ?>_nf_comissao">
                                                <? fillselect(PedidoController::$varSimNao, $_1_u_nf_comissao); ?>
                                            </select>
                                        </td>
                                    <? } elseif ($comissionado == "N") { ?>
                                        <td>Comissão:</td>
                                        <td>
                                            <b>Não</b>
                                            <input name="_1_<?= $_acao ?>_nf_comissao" type="hidden" value="N">
                                        </td>
                                    <? }
                                    //}
                                    ?>
                                </tr>
                                <? //} //if($_1_u_nf_status!='INICIO' and $_1_u_nf_status!='PRODUCAO'){
                                ?>
                            </table>
                            <? //}
                            if ($_1_u_nf_geracontapagar != 'N') { ?>
                                <table>
                                    <tr>
                                        <td align="right">Fatura Automática:</td>
                                        <td colspan='3'>                                          
                                            <select <?= $edita ?> id="sect" name="_1_<?= $_acao ?>_nf_idformapagamento">
                                                <option></option>
                                                <? fillselect(PedidoController::buscarFormapagamentoPorEmpresa($_1_u_nf_idempresa), $_1_u_nf_idformapagamento); ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="right" nowrap>Condição de Pagto:</td>
                                        <td>
                                            <select <?= $edita ?> <?= $disablednf ?> style="width: 100px;" name="_1_<?= $_acao ?>_nf_indpag">
                                                <? fillselect("select '0','à Vista' union select '1','à Prazo'", $_1_u_nf_indpag); ?>
                                            </select>
                                        </td>
                                        <td align="right">Validade(D):</td>
                                        <td><input class="size3" <?= $edita ?> <?= $disablednf ?> name="_1_<?= $_acao ?>_nf_validade" type="text" value="<?= $_1_u_nf_validade ?>"></td>
                                    </tr>
                                </table>
                            <? } //if($_1_u_nf_geracontapagar!='N'){
                            if ($_1_u_nf_geracontapagar != 'N') { ?>
                                <table>
                                    <tr>
                                        <td align="right " style="width: 23px;"></td>
                                        <td align="right">Vencimento(D):</td>
                                        <td><input <?= $edita ?> class="size3" style="width: 100px;" <?= $readonlynf ?> name="_1_<?= $_acao ?>_nf_diasentrada" size="1" type="text" value="<?= $_1_u_nf_diasentrada ?>" onchange="atualizadiasentrada(this)"></td>
                                        <td align="right" style="margin-left:-30px;">Parcelas:</td>
                                        <td>
                                            <select <?= $edita ?> class="size5" name="nf_parcelas" id="parcelas" <?=$disabled?> onchange="atualizaparc(this)">
                                                <? fillselect(PedidoController::$parcelasNF, $_1_u_nf_parcelas); ?>
                                            </select>
                                        </td>
                                        <? if ($_1_u_nf_parcelas > 1) {
                                            $strdivtab = "style='display:block;'";
                                        } else {
                                            $strdivtab = "style='display:none;'";
                                        } ?>
                                        <td align="right">
                                            <div class="divtab" <?= $strdivtab ?> id="divtab1">Intervalo(D):</div>
                                        </td>
                                        <td>
                                            <div class=" divtab" <?= $strdivtab ?> id="divtab2"><input <?= $readonlynf ?> <?= $edita ?> class="size3" style="width: 100px;" name="_1_<?= $_acao ?>_nf_intervalo" type="text" value="<?= $_1_u_nf_intervalo ?>" onchange="atualizaintervalo(this)"> </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="10">
                                            <hr>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <? if ($_1_u_nf_status != 'CONCLUIDO') { ?>
                                                Atualizar Data(s):&nbsp;<a class="fa fa-download btn-lg verde pointer hoverazul" title="Alterar valores" onclick="atualizaconfpagar();"></a>
                                            <? } ?>
                                        </td>
                                        <td class="nowrap">Editar Proporção:

                                            <? if ($_1_u_nf_proporcional == 'Y') {
                                                $checked = 'checked';
                                                $vchecked = 'N';
                                            } else {
                                                $checked = '';
                                                $vchecked = 'Y';
                                            } ?>
                                        </td>
                                        <td>
                                            <input <?= $edita ?> title="Editar proporções" type="checkbox" <?= $checked ?> name="nameproporcional" onclick="altcheck('nf','proporcional',<?= $_1_u_nf_idnf ?>,'<?= $vchecked ?>')">
                                        </td>
                                    </tr>
                                    <? // Calcula a data daqui 3 dias
                                    //if($_1_u_nf_status!='INICIO' and $_1_u_nf_status!='SOLICITADO' and $_1_u_nf_status!='PEDIDO'){
                                    if (!empty($_1_u_nf_idnf) and !empty($_1_u_nf_diasentrada) and !empty($_1_u_nf_intervalo)) {
                                        $q = 999;
                                        $rescx = PedidoController::buscarConfpagarPorNF($_1_u_nf_idnf);
                                                                               
                                        $v = 0;
                                        $tproporcao = 0;
                                        foreach($rescx as $rowcx){ 
                                            $q++;
                                            $i++;
                                            if ($v == 0) {
                                                $dias = $_1_u_nf_diasentrada - 1;
                                            } else {
                                                $dias = $_1_u_nf_diasentrada + ($_1_u_nf_intervalo * $v) - 1;
                                            }
                                            if (empty($_1_u_nf_dtemissao)) {
                                                $pvdate = date("d/m/Y H:i:s");
                                            } else {
                                                $pvdate = $_1_u_nf_dtemissao;
                                            }
                                            $pvdate = str_replace('/', '-', $pvdate);
                                            //echo date('Y-m-d', strtotime($pvdate));
                                            $timestamp = strtotime(date('Y-m-d', strtotime($pvdate)) . "+" . $dias . " days");

                                            //verificar se a data e sabado ou domingo
                                             /*
                                            $rowdia = PedidoController::retornaDiaSemanaPorData(date('Y-m-d', $timestamp));

                                            if ($rowdia['diasemana'] == 1) { //Se for domingo aumenta 1 dia
                                                $timestamp = strtotime(date('Y-m-d', $timestamp) . "+1 days");
                                            } elseif ($rowdia['diasemana'] == 7) { //Se for sabado aumenta 2 dias
                                                $timestamp = strtotime(date('Y-m-d', $timestamp) . "+2 days");
                                            }
                                             */
                                            $eFeriado = 1;

                                            WHILE ($eFeriado >= 1) {
            
                                                /*$sqldia = " SELECT verificaFeriadoFds('" . date('Y-m-d', $timestamp) . "' ) as eFeriado;";
                                                $resdia = d::b()->query($sqldia) or die("Erro ao buscar dia da semana");
                                                */
                                                $rowdia =  NFController::verificaFeriadoFds(date('Y-m-d', $timestamp));
                                              
                                                                                  
                                                IF($rowdia['eFeriado'] == 1) {
                                                    $timestamp = strtotime(date('Y-m-d', $timestamp) . "+1 days");
                                                    $eFeriado = 1;
                                                }else{
                                                    $eFeriado = 0;
                                                }
                                                    
                                            }

                                            if (empty($rowcx['dmadatareceb'])) {
                                                $rowcx['dmadatareceb'] = date('d/m/Y', $timestamp);
                                            }

                                            $proporcao = 100 / $_1_u_nf_parcelas;
                                            if (empty($rowcx['proporcao'])) {
                                                $rowcx['proporcao'] = $proporcao;
                                                $valorparcela = ($vtotal * $proporcao) / 100;
                                            } elseif (!empty($rowcx['valorparcela'])) {
                                                $valorparcela = $rowcx['valorparcela'];
                                            } else {
                                                $valorparcela = ($vtotal * $rowcx['proporcao']) / 100;
                                            }

                                            $tproporcao = $tproporcao + $rowcx['proporcao'];

                                            // Exibe o resultado
                                            ?>
                                            <tr>
                                                <td align="right">
                                                    <font color="red"><? echo (($v + 1) . "º"); // 
                                                                        ?>:</font>
                                                </td>
                                                <td>
                                                    <input <?= $edita ?> style="width: 100px;" name="_<?= $q ?>_u_nfconfpagar_idnfconfpagar" type="hidden" value="<?= $rowcx['idnfconfpagar'] ?>">
                                                    <input <?= $edita ?> class="size7 calendario confcontapagar" idnfconfpagar="<?= $rowcx['idnfconfpagar'] ?>" datagerada="<?= date('d/m/Y', $timestamp) ?>" name="_<?= $q ?>_u_nfconfpagar_datareceb" type="text" value="<?= $rowcx['dmadatareceb'] ?>" autocomplete="off">
                                                    <? if ($rowcx['dmadatareceb'] != date('d/m/Y', $timestamp)) { ?>
                                                        &nbsp;<?= date('d/m/Y ', $timestamp) ?>&nbsp;<i class="fa fa-exclamation-triangle laranja" title="Valor sugerido pelo Sistema"></i>
                                                    <? } ?>
                                                </td>
                                                <? if ($_1_u_nf_proporcional == 'Y') { ?>
                                                    <td align="right">Proporção:</td>
                                                <? } ?>
                                                <td class='nowrap'>
                                                    <? if ($_1_u_nf_proporcional == 'Y') { ?>
                                                        <input <?= $edita ?> class="size4" name="_<?= $q ?>_u_nfconfpagar_proporcao" type="text" value="<?= round($rowcx['proporcao'], 2) ?>" onchange="atualizaproporcao(this,<?= $rowcx['idnfconfpagar'] ?>)">
                                                        <span style="padding-left: 10%;">Valor Parcela: <b>R$ <?= number_format(tratanumero($valorparcela), 2, ',', '.') ?></b><span>
                                                            <? } ?>
                                                            <? if (empty($rowcx['obs'])) { ?>
                                                                <i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer insobspgt" onclick="nfconfpagar(<?= $rowcx['idnfconfpagar'] ?>,<?= $q ?>)" title="Inserir observação."></i>
                                                            <? } else { ?>
                                                                <i class="fa fa-info-circle fa-1x azul pointer hoverpreto btn-lg tip" onclick="nfconfpagar(<?= $rowcx['idnfconfpagar'] ?>,<?= $q ?>)">
                                                                    <span>
                                                                        <ul>
                                                                            <li> Obs: <?= $rowcx['obs'] ?>
                                                                                <p>
                                                                            <li> Alterado em: <?= dmahms($rowcx['alteradoem']) ?>
                                                                                <p>
                                                                            <li> Alterado por: <?= $rowcx['alteradopor'] ?>
                                                                        </ul>
                                                                    </span>
                                                                </i>
                                                            <? } ?>
                                                            <div id='<?= $q ?>_editarnfconfpagar' class='hide'>
                                                                <table>
                                                                    <tr>
                                                                        <td>
                                                                            <textarea name="<?= $q ?>_nfconfpagar_obs" id="<?= $q ?>_nfconfpagar_obs" style="width: 760px; height: 41px; margin: 0px;"><?= $rowcx['obs'] ?></textarea>
                                                                            <input id="<?= $q ?>_nfconfpagar_idnfconfpagar" name="<?= $q ?>_nfconfpagar_idnfconfpagar" type="hidden" value="<?= $rowcx['idnfconfpagar'] ?>">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="row">
                                                                                <div class="col-md-12">
                                                                                    <div class="panel panel-default">
                                                                                        <div class="panel-body">
                                                                                            <div class="row col-md-12">
                                                                                                <div class="col-md-2" style="text-align:right">Alterado Por:</div>
                                                                                                <div class="col-md-4" style="text-align:left"><?= $rowcx['alteradopor'] ?></div>
                                                                                                <div class="col-md-2" style="text-align:right">Alterado Em:</div>
                                                                                                <div class="col-md-4" style="text-align:left"><?= dmahms($rowcx['alteradoem']); ?></div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                </td>
                                            </tr>
                                        <? $v++;
                                        } ?>
                                        <tr>
                                            <td colspan="3"></td>
                                            <td>
                                                <font color="red"><?= round($tproporcao, 2) ?></font>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="10">
                                                <hr>
                                            </td>
                                        </tr>
                                    <? } ?>
                                </table>
                            <? } //if($_1_u_nf_geracontapagar!='N'){
                            ?>
                            <table>
                                <tr>
                                    <td>
                                        <font color="red"><?= nl2br($rowpgto['obsvenda']); ?></font>
                                    </td>
                                </tr>
                            </table>
                            <div class="papel hover" id="formSF" style="width: 100%;">
                                <?
                                //06/08/2020 MCC COMENTADO A PARTE QUE VERIFICA SE GERA NFE A PEDIDO DO FABIO.
                                 if ($_1_u_nf_status != 'INICIO' and $_1_u_nf_status != 'SOLICITADO' and $_1_u_nf_status != 'PENDENTE' and $_1_u_nf_status != 'PEDIDO' and $_1_u_nf_status != 'PRODUCAO') { ?>
                                    <table style="width:100%">
                                        <?
                                        $contigencia = traduzid('empresa', 'idempresa', 'contingencia', $_1_u_nf_idempresa);
                                        if ($contigencia == 'Y') {

                                            $datacontingencia = traduzid('empresa', 'idempresa', 'datacontingencia', $_1_u_nf_idempresa);
                                        ?>
                                            <tr>
                                                <td style="color: red;" colspan='6' class='nowrap'>Emissão de nota fiscal em contigência SVCAN (<?= dmahms($datacontingencia) ?>) <i class='fa fa-exclamation-triangle laranja pointer' title="Emissão de nota fiscal em contigência SVCAN (<?= dmahms($datacontingencia) ?>), notas serão processadas automaticamente após retorno do servidor. Nessa forma de emissão, não é permitido cancelar e nem fazer a carta de correção. Recomenda-se aplicar o modo de contingência em casos extremos, que realmente causem comprometimento à empresa. Em situações mais amenas, é melhor aguardar que o sistema se normalize para emitir a nota fiscal em tempo real."></i></td>
                                            </tr>
                                        <?
                                        }
                                        if ($_1_u_nf_envionfe != "CONCLUIDA" && $_1_u_nf_envionfe != "CANCELADA") { ?>
                                            <tr>
                                                <td align="right" nowrap>Gerar Danfe:</td>
                                                <td class="tdbr" align="left">
                                                    <?
                                                    $permiteGerarDanfe = PedidoController::permiteGerarDanfe($_1_u_nf_idpessoa);
                                                    if (tratanumero($vtotal) != tratanumero($_1_u_nf_total)) {?>
                                                        <a class="fa fa-exclamation-triangle laranja pointer" title="Favor salvar a nota novamente antes de gerar a Danfe!"></a>
                                                    <?}elseif($permiteGerarDanfe){?>
                                                        <a class="fa fa-cloud-upload pointer hoverazul" title="Gerar Danfe" onClick="envionfe(<?= $pedidopend ?>);"></a>
                                                    <?}else{?>
                                                        <a class="fa fa-exclamation-triangle laranja pointer" title="Não é possível gerar danfe, divergência na divisão do cliente!"></a>
                                                    <?}?>
                                                </td>
                                                <td align="right" nowrap>Consultar Protocolo:</td>
                                                <td class="tdbr" align="left">
                                                    <a class="fa fa-cloud-download pointer hoverazul" title="Consulta Protocolo" onClick="consultanfe();"></a>
                                                </td>
                                                <td align="right" nowrap>Alterar Recibo:</td>
                                                <td class="tdbr" align="left">
                                                    <div id="rotrecibo" style="display: inline-block">
                                                        <a class="fa fa-pencil-square-o pointer hoverazul" title="Editar Recibo" onClick="showdivrecibo();"></a>
                                                    </div>
                                                    <div id="inputrecibo" style="display: none">
                                                        <input name="_1_<?= $_acao ?>_nf_recibo" type="text" size="15" value="<?= $_1_u_nf_recibo ?>">
                                                    </div>
                                                    <?
                                                    $recibos = PedidoController::buscarNfpLote($_1_u_nf_idnf);
                                                    if($recibos){
                                                        ?>
                                                        <div id="recibosemitidos" style="display: inline-block">
                                                            <a class="fa fa-info-circle pointer hoverazul showdivrecibo" title="Recibos Gerados" style="padding-left: 10px;"></a>
                                                        </div>
                                                        <div class="panel panel-default" hidden>
                                                            <div id="modalrecibosgerados" class="panel-heading">
                                                                <table class="table table-striped">
                                                                    <tr>
                                                                        <th>N° Recibo</th>
                                                                        <th>Criado Em</th>
                                                                    </tr>
                                                                    <?
                                                                    foreach($recibos as $_recibo){
                                                                        ?>
                                                                        <tr>
                                                                            <!-- Recibo -->
                                                                            <td><?=$_recibo['recibo']?></td>
                                                                            <!-- Un -->
                                                                            <td><?=dmahms($_recibo['criadoem'])?></td>
                                                                        </tr>                                                                        
                                                                    <? 
                                                                    }
                                                                    ?>
                                                                </table>                                                  
                                                            </div>
                                                        </div>
                                                    <?
                                                    }
                                                    ?>  
                                                </td>
                                            </tr>
                                        <?
                                        } //if(empty($_1_u_nf_protocolonfe) and $_1_u_nf_status!='Orçamento'  /*and $faltaestoque=='N'*/){
                                        if ($_1_u_nf_envionfe == "CONCLUIDA" || $_1_u_nf_envionfe == "CANCELADA") { ?>
                                            <tr>
                                                <td align="right">Danfe:</td>
                                                <td align="left"><a class="fa fa-print pointer hoverazul" title="Danfe" onclick="janelamodal('../inc/nfe/sefaz4/func/printDANFE.php?idnotafiscal=<?= $_1_u_nf_idnf ?>')"></a></td>
                                                <td align="right">XML:</td>
                                                <td align="left"><a class="fa fa-download pointer hoverazul" title="XML NFe" onclick="janelamodal('../inc/nfe/sefaz3/func/geraarquivo.php?idnotafiscal=<?= $_1_u_nf_idnf ?>')"></a></td>
                                                <? if ($_1_u_nf_envionfe == "CONCLUIDA") {
                                                   ?>
                                                    <td align="right" nowrap>
                                                        Carta Cor.:
                                                    </td>
                                                    <td><i class="fa fa-envelope-square azul pointer hoverazul" title="Carta de Correção" id="toggle_cartacorrecao"></i></td>
                                                    <td align="right">Sped?</td>
                                                    <td class="tdbr" align="center">
                                                        <? if ($_1_u_nf_sped == 'Y') {
                                                            $checked = 'checked';
                                                            $vchecked = 'N';
                                                        } else {
                                                            $checked = '';
                                                            $vchecked = 'Y';
                                                        } ?>
                                                        <input title="sped" type="checkbox" <?= $checked ?> name="namesped" onclick="altcheck('nf','sped',<?= $_1_u_nf_idnf ?>,'<?= $vchecked ?>')">
                                                    </td>
                                                <? } else { ?>
                                                    <td><span style="color: red;"> NFe foi Cancelada no Sistema</span></td>
                                                <? } ?>
                                            </tr>
                                            <? if ($_1_u_nf_envionfe == "CONCLUIDA") { ?>
                                                <tr>
                                                    <td colspan="5" align="right" nowrap>Cancelar NFe:</td>
                                                    <td><i class="fa fa-minus-square vermelho pointer hoverazul" id="toggle_cancelanfe" title="Cancelar NFe"></i></td>
                                                    <? if ($_1_u_nf_status == 'CONCLUIDO') { ?>
                                                        <td align="right" nowrap>NF Retorno:</td>
                                                        <td align="center">
                                                            <a class="fa fa-mail-reply-all btn-lg pointer azul" title="Gerar NF de retorno" onclick="transferirnf();"></a>
                                                        </td>
                                                    <? } ?>
                                                </tr>
                                        <? }
                                        } ?>
                                    </table>
                                    <table style="min-width: 100%;">
                                        <? if ($_1_u_nf_status != 'INICIO' and $_1_u_nf_status != 'SOLICITADO' and $_1_u_nf_status != 'PEDIDO') {
                                            $q = 0;
                                            
                                            $rescx = PedidoController::buscarConfpagarPorNF($_1_u_nf_idnf);
                                                                                      
                                            $v = 0;
                                            $tproporcao = 0;
                                            foreach($rescx as $rowcx){                                             
                                                $q++;
                                                $i++;
                                                if ($v == 0) {
                                                    $dias = $_1_u_nf_diasentrada - 1;
                                                } else {
                                                    $dias = $_1_u_nf_diasentrada + ($_1_u_nf_intervalo * $v) - 1;
                                                }

                                               
                                                if (empty($_1_u_nf_dtemissao)) {
                                                    $pvdate = date("d/m/Y H:i:s");
                                                } else {
                                                    $pvdate = $_1_u_nf_dtemissao;
                                                }

                                                $pvdate = str_replace('/', '-', $pvdate);
                                                //echo date('Y-m-d', strtotime($pvdate));
                                                $timestamp = strtotime(date('Y-m-d', strtotime($pvdate)) . "+" . $dias . " days");

                                                //verificar se a data e sabado ou domingo                                                                                         
                                                $rowdia = PedidoController::retornaDiaSemanaPorData(date('Y-m-d', $timestamp));

                                                if ($rowdia['diasemana'] == 1) { //Se for domingo aumenta 1 dia
                                                    $timestamp = strtotime(date('Y-m-d', $timestamp) . "+1 days");
                                                } elseif ($rowdia['diasemana'] == 7) { //Se for sabado aumenta 2 dias
                                                    $timestamp = strtotime(date('Y-m-d', $timestamp) . "+2 days");
                                                }
                                                if (empty($rowcx['dmadatareceb'])) {
                                                    $rowcx['dmadatareceb'] = date('d/m/Y', $timestamp);
                                                }

                                                $strinfcpl = $strinfcpl . $virgula . ($v + 1) . "º-" . $rowcx['dmadatareceb'];
                                                $vdias = $dias + 1;
                                                // Exibe o resultado
                                                $diasparc = $diasparc . $barra . $vdias;
                                                $barra = "/";
                                                $virgula = ", ";

                                                $htmlvenc .= $htmlvenc . "<font color='red'>" . ($v + 1) . 'º -' . $rowcx['dmadatareceb'] . "</font>";
                                                if ($q == 4) {
                                                    $q = 0;
                                                    $htmlvenc .= $htmlvenc . "<br>";
                                                } else {
                                                    $htmlvenc .= $htmlvenc . "&nbsp;&nbsp;&nbsp;&nbsp;";
                                                }
                                                $v++;
                                            } //for ($v = 0; $v < $_1_u_nf_parcelas; $v++) {    
                                        } ?>
                                        <tr>
                                            <td align="left">Inf. NFe:</td>
                                        </tr>
                                        <? if ($_1_u_nf_envionfe == 'CONCLUIDA') { ?>
                                            <tr>
                                                <td colspan="3"><textarea class="caixa" readonly="readonly" style="min-width: 100%; height: 220px; font-size: 11px;" name="_1_<?= $_acao ?>_nf_obsint"><?= $_1_u_nf_obsint ?></textarea></td>
                                            </tr>
                                        <? } else {
                                            if (!empty($_1_u_nf_idformapagamento)) {
                                               
                                                $rowta2 =PedidoController::buscarFormapagamentoAgenciaPorFormapagamento($_1_u_nf_idformapagamento);
                                                if ($rowta2['formapagamento'] == "DEPOSITO") {
                                                    $stragencia = " - Crédito em conta: Banco " . $rowta2['agencia'] . ", Ag.: " . $rowta2['nagencia'] . ", C.c: " . $rowta2['nconta'];
                                                } else {
                                                    $stragencia = "";
                                                }
                                            }
                                            
                                            $resimp =PedidoController::buscarStPorNF($_1_u_nf_idnf);
                                            $frpiscofins = "";
                                            foreach($resimp as $rowimp){  
                                                if ($rowimp['piscst'] == '06' or $rowimp['confinscst'] == '06') {
                                                    $frpiscofins = " Alíq. zero PIS/COFINS p/ receita da venda no merc. interno, conf. inciso VII, art. 1, Lei 10.925/2004. ";
                                                } 
                                            }


                                            $rowempresa = PedidoController::buscarInfEmpresaNF($nf_idempresa);

                                            //texto para inf nfe de acordo com estado de origem do cliente
                                            $ufcliente = traduzid("endereco", "idendereco", "uf", $_1_u_nf_idenderecofat);
                                            $arrnatopex = explode(" -", $arrnatop[$_1_u_nf_idnatop]["natop"]);
                                            $textoinf  = $arrnatopex[0];
                                            $natoptipo = traduzid("natop", "idnatop", "natoptipo", $_1_u_nf_idnatop);

                                            if($natoptipo=='transferencia'){
                
                                                // $textoinf .= "Operação entre estabelecimentos de mesma titularidade não sujeita a incidência de ICMS, conforme decisão judicial exarada nos autos do MS 5029461-54.2022.8.13.0702, em trâmite perante a 3ª Vara da Fazenda Pública e Autarquias da Comarca de Uberlândia/MG.";
                                                $textoinf .=" Operação entre estabelecimentos de mesma titularidade não sujeita a incidência de ICMS, conforme decisão judicial exarada nos autos do MS 5052149-39.2024.8.13.0702, em trâmite perante a 1ª Vara da Fazenda Pública e Autarquias da Comarca de Uberlândia/MG";
                                            }elseif ($ufcliente == $rowempresa['uf']) {

                                                
                                                if($rowempresa['uf']=='MG'){
                                                    $textois = " ICMS isento conforme artigo 6º anexo I, Parte 1, item 4 decreto 43.080/2002 MG.";
                                                }elseif($rowempresa['uf']=='MT'){                                
                                                    $textois = " ICMS Isento conforme Artigo 115 Inciso I do Anexo IV do RICMS/MT.";
                                                }elseif($rowempresa['uf']=='SP'){
                                                    $textois = " ICMS Isento conforme Artigo 41 Inciso I do Anexo I do RICMS/SP.";
                                                }elseif($rowempresa['uf']=='PR'){
                                                    $textois = " ICMS diferido conforme Artigo 44, inciso IV, do Anexo VIII do RICMS/PR.";
                                                }elseif($rowempresa['uf']=='SC'){
                                                    $textois = " ICMS isento conforme Artigo 29 Inciso I do Anexo 2 do RICMS/SC.";
                                                }elseif($rowempresa['uf']=='RS'){
                                                    $textois = " ICMS isento conforme Artigo 9º Inciso VIII alínea a do Livro I do RICMS/RS.";
                                                }

                                                $textoinf .= " conforme pedido de compra realizado por Nº " . $_1_u_nf_nitemped . " - Vencimento(s) em " . $_1_u_nf_parcelas . " parcela(s): (" . $diasparc . " DD): " . $strinfcpl . "" . $stragencia . " -". $textois." IPI reduzido à  alíquota zero, TIPI aprovada pelo Decreto 4544/02.";
                                                if ($icmsufdest_ufremet == "Y" and $_1_u_nf_tiponf == "V" and !empty($vicmsufdest)) {
                                                    $textoinf .= " *ICMS-DIFAL EC 87/2015: Partilha Destinatário (R$" . number_format(tratanumero($vicmsufdest), 2, ',', '.') . ").";
                                                }
                                                $textoinf .= $frpiscofins;
                                            } else {
                                                $textoinf .= " conforme pedido de compra realizado por Nº " . $_1_u_nf_nitemped . " - Vencimento(s) em " . $_1_u_nf_parcelas . " parcela(s): (" . $diasparc . " DD): " . $strinfcpl . "" . $stragencia;
                                                $textoinf .= ' ICMS: Base de cálc. ICMS red. em 60% conf. Item 1, alínea "a", da Parte 1 do Anexo II do RICMS/MG,';
                                                if ($frdesoneracao == 'Y') {
                                                    $textoinf .= " com desoneração de R$ " . number_format(tratanumero($vtotaldeson), 2, ',', '.') . ". Valor final com a desoneração: R$ " . number_format(tratanumero($vtotal), 2, ',', '.') . ".";
                                                }
                                                $textoinf .= " IPI reduzido à alíquota zero, TIPI aprovada pelo Decreto 4544/02.";
                                                if ($icmsufdest_ufremet == "Y" and $_1_u_nf_tiponf == "V" and !empty($vicmsufdest) ) {
                                                    $textoinf .= " *ICMS-DIFAL EC 87/2015: Partilha Destinatário (R$" . number_format(tratanumero($vicmsufdest), 2, ',', '.') . ").";
                                                }
                                                $textoinf .= $frpiscofins;
                                            } //if($ufcliente=="MG"){
                                            if (!empty($_1_u_nf_idendrotulo)) {
                                                $rowen =PedidoController::buscarEnderecoFaturamentoPorId($_1_u_nf_idendrotulo);
                                            } else {
                                            
                                                $rowen =PedidoController::buscarEnderecoPessoaPorTipo("$_1_u_nf_idpessoa",'3');                                                    
                                            }
                                           
                                            $qtdent = count($rowen);
                                            if ($qtdent > 0) {
                                               
                                                $textoinf .= " ENDEREÇO DE ENTREGA: " . $rowen['logradouro'] . " " . $rowen['endereco'] . " " . $rowen['numero'];
                                                if (!empty($rowen['complemento'])) {
                                                    $textoinf .= ", " . $rowen['complemento'];
                                                }
                                                if (!empty($rowen['bairro'])) {
                                                    $textoinf .= ", " . $rowen['bairro'];
                                                }
                                                if (!empty($rowen['cep'])) {
                                                    $textoinf .= ", " . $rowen['cep'];
                                                }
                                                $textoinf .= ", " . $rowen['cidade'] . "-" . $rowen['uf'];
                                            }
                                           
                                            $rowta3 = PedidoController::buscarPreferenciaCliente($_1_u_nf_idpessoa);   
                                            if (!empty($rowta3['obsinfnfe'])) {
                                                $textoinf .= $rowta3['obsinfnfe'];
                                            }

                                            if ($mensagem == TRUE) {
                                                $textoinf .= '. MATERIAL PERECÍVEL';
                                            }

                                            $_1_u_nf_infcpl = (!empty($_1_u_nf_infcpl)) ? $_1_u_nf_infcpl : $textoinf; ?>
                                            <tr>
                                                <td colspan="3"><textarea <?= $edita ?> class="caixa" style="width: 461px; height: 160px; font-size: 12px;" name="_1_<?= $_acao ?>_nf_infcpl"><?= $_1_u_nf_infcpl ?></textarea></td>
                                            </tr>
                                        <? } ////if($_1_u_nf_envionfe=='CONCLUIDA'){
                                        ?>
                                    </table>
                                    <? if ($_1_u_nf_envionfe == "CANCELADA" or $_1_u_nf_envionfe == 'CONCLUIDA') { ?>
                                        <div id="cancelanf" idnf=<?= $_1_u_nf_idnf ?>>
                                            <table>
                                                <tr>
                                                    <td align="right"><span style="color: red;">Inf. Cancel.:</span></td>
                                                    <td>
                                                        <textarea class="caixa" style="width: 460px; height: 70px; font-size:12px;" name="_1_<?= $_acao ?>_nf_infcpl" onchange="atualizainfcpl(this)"><?= $_1_u_nf_infcpl ?></textarea>
                                                    </td>
                                                </tr>
                                                <? if ($_1_u_nf_envionfe == 'CONCLUIDA') { ?>
                                                    <tr>
                                                        <td></td>
                                                        <td>
                                                            <? if (!empty($_1_u_nf_infcpl)) { ?>
                                                                <button id="cbSalvar" type="button" class="btn btn-danger btn-xs" onclick="cancelanfe()">
                                                                    <i class="fa fa-circle"></i>Cancelar
                                                                </button>
                                                            <? } ?>
                                                        </td>
                                                    </tr>
                                                <? } ?>
                                            </table>
                                        </div>
                                    <? } ?>
                                    <? if ($_1_u_nf_envionfe == 'CONCLUIDA') { ?>
                                        <div id="cartacorrecao" idnf=<?= $_1_u_nf_idnf ?>>
                                            <table>
                                                <tr>
                                                    <td align="right">Carta de Correção.:</td>
                                                    <td>
                                                        <div class="oEmailorc">
                                                            <a class="fa fa-exclamation-triangle laranja pointer" title="" data-target="webuiPopover0"></a>
                                                        </div>
                                                        <div class="webui-popover-content">

                                                            Atenção! A carta de correção eletrônica (CC-e) poderá ser emitida desde que o erro NÃO esteja relacionado com:
                                                            <br>
                                                            1 - As variáveis que determinam o valor do imposto tais como:<br>
                                                            base de cálculo, alíquota, diferença de preço, quantidade, <br>
                                                            valor da operação (para estes casos deverá ser utilizada NF-e Complementar);<br>
                                                            2 - A correção de dados cadastrais que implique mudança do remetente ou do destinatário;<br>
                                                            3 - A data de emissão da NF-e ou a data de Saída da mercadoria.

                                                        </div>
                                                    </td>
                                                    <td>
                                                        <textarea class="caixa" style="width: 253px; height: 60px;  font-size:12px;" name="_1_<?= $_acao ?>_nf_infcorrecao" onchange="atualizact(this)"><?= $_1_u_nf_infcorrecao ?></textarea>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td>
                                                        <button id="cbSalvar" type="button" class="btn btn-danger btn-xs" onclick="enviarcartacor()">
                                                            <i class="fa fa-circle"></i>Enviar Carta
                                                        </button>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <? 
                                        
                                          $rescor = PedidoController::buscarNfcorrecaoPorIdnf($_1_u_nf_idnf);
                                          $qtdcor = count($rescor);
                                        if ($qtdcor > 0) {
                                            $lc = 0;
                                            foreach($rescor as $rowcor){ 
                                                $lc = $lc + 1; ?>
                                                <div>
                                                    <table>
                                                        <tr>
                                                            <td>Carta Correção - [<?= $lc ?>]</td>
                                                            <td><a title="Imprimir Cartas de Correção." class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/pedidocartacor.php?_acao=u&idnf=<?= $rowcor["idnf"] ?>&idnfcorrecao=<?= $rowcor["idnfcorrecao"] ?>')"></a></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                <?
                                            }
                                        }
                                    }
                                } //if($_1_u_nf_status!='INICIO'){
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <? if ($_1_u_nf_status != 'INICIO' and $_1_u_nf_status != 'SOLICITADO' /*and $_1_u_nf_status!='PRODUCAO' and $_1_u_nf_status!='PEDIDO'*/) { ?>
                        <? if (!empty($_1_u_nf_idenderecofat)) {
                            $uffat = traduzid('endereco', 'idendereco', 'uf', $_1_u_nf_idenderecofat);
                            if ($uffat == 'EX') { ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading" title="Favor preencher em casos de exportação">Exportação <a title="Favor preencher em casos de exportação." class="fa fa-exclamation-triangle laranja pointer" onclick="alert('Favor preencher em casos de exportação!!!')"></a></div>
                                    <div class="panel-body">
                                        <table>
                                            <td align="right">UF de Saída:</td>
                                            <td> <select <?= $disablednf ?> name="_1_<?= $_acao ?>_nf_ufsaidapais">
                                                    <option value=""></option>
                                                    <?
                                                        fillselect(PedidoController::buscarUfBr(), $_1_u_nf_ufsaidapais);
                                                    ?>
                                                </select>
                                            </td>
                                            <td align="right">Local de Exportação:</td>
                                            <td><input <?= $readonlynf ?> class="size15" name="_1_<?= $_acao ?>_nf_xlocexporta" type="text" value="<?= $_1_u_nf_xlocexporta ?>"></td>
                                            <td align="right">Local de Despacho:</td>
                                            <td><input <?= $readonlynf ?> class="size15" name="_1_<?= $_acao ?>_nf_xlocdespacho" type="text" value="<?= $_1_u_nf_xlocdespacho ?>"></td>
                                        </table>
                                    </div>
                                </div>
                        <? }
                        } ?>
                        
                        <div id="transporte" class="panel panel-default">
                            <div class="panel-heading">
                                <table>
                                    <tr>
                                        <td data-toggle="collapse" href="#collapsetransp">Transporte</td>
                                        <td style="width: 60%;" data-toggle="collapse" href="#collapsetransp"></td>
                                        <td>
                                            <? if ($_1_u_nf_impendereco == "Y") {
                                                $checked = 'checked';
                                                $vchecked = 'N';
                                            } else {
                                                $checked = '';
                                                $vchecked = 'Y';
                                            } ?>
                                            <input title="Imprimir Endereço?" type="checkbox" <?= $checked ?> name="nameimpend" onclick="altcheck('nf','impendereco',<?= $_1_u_nf_idnf ?>,'<?= $vchecked ?>')">
                                        </td>

                                        <td>
                                            Endereço
                                        </td>
                                        <td>
                                            <? if ($_1_u_nf_impitem == "Y") {
                                                $checked = 'checked';
                                                $vchecked = 'N';
                                            } else {
                                                $checked = '';
                                                $vchecked = 'Y';
                                            } ?>
                                            <input title="Imprimir Itens?" type="checkbox" <?= $checked ?> name="nameimpiten" onclick="altcheck('nf','impitem',<?= $_1_u_nf_idnf ?>,'<?= $vchecked ?>')" </td>
                                        <td>Itens</td>
                                        <td>
                                            <? if ($_1_u_nf_implocal == "Y") {
                                                $checked = 'checked';
                                                $vchecked = 'N';
                                            } else {
                                                $checked = '';
                                                $vchecked = 'Y';
                                            } ?>
                                            <input title="Imprimir Local?" type="checkbox" <?= $checked ?> name="nameimplocal" onclick="altcheck('nf','implocal',<?= $_1_u_nf_idnf ?>,'<?= $vchecked ?>')" </td>
                                        <td>Local</td>
                                        <td style="width: 10%;"></td>
                                        <td><a id="imprimircupom" title="Imprimir Cupom" class="fa fa-print  fa-lg cinza pointer hoverazul" onclick="imprimeEtiqueta(<?= $_1_u_nf_idnf ?>)"></a></td>
                                        <td><a id="html" class="fa fa-file-text btn-lg pointer" title="Html" onclick="janelamodal('report/impetiquetaped.php?idnf=<?= $_1_u_nf_idnf ?>')"></a></td>
                                    </tr>

                                </table>

                            </div>
                            <div class="panel-body " id="collapsetransp">
                                <table>
                                    <? //LTM (16-09-2020) - 373161: Atlerado conforme solicitação do Fábio 
                                    ?>
                                    <tr>
                                        <td colspan="6">
                                            <table width="100%">
                                                <tr>
                                                    <td align="right" nowrap>Envio:</td>
                                                    <td nowrap>
                                                        <?= dma($_1_u_nf_envio) ?>
                                                    </td>
                                                    <td align="right">Previsão Entrega:</td>
                                                    <td>
                                                        <?
                                                        //colocar previsão de entrega 
                                                        if (empty($_1_u_nf_obsenvio)) {
                                                            if (!empty($_1_u_nf_idtransportadora)) {
                                                               
                                                                $rowt2=PedidoController::buscarPreferenciaCliente($_1_u_nf_idtransportadora); 
                                                                
                                                            }
                                                            if (!empty($rowt2['observacaonfp'])) {
                                                                $_1_u_nf_obsenvio = $rowt2['observacaonfp'];
                                                            } else {
                                                                $_1_u_nf_obsenvio = "De 2 à  3 dias úteis";
                                                            }
                                                        } ?>
                                                        <input <?= $readonlynf ?> style="color: red;" name="_1_<?= $_acao ?>_nf_obsenvio" size="20" type="text" value="<?= $_1_u_nf_obsenvio ?>">
                                                    </td>
                                                    <td align="right" nowrap>Entrega:</td>
                                                    <td>
                                                        <input size="10" class="calendario" class="size7" name="_1_<?= $_acao ?>_nf_entrega" value="<?= $_1_u_nf_entrega ?>" autocomplete="off">
                                                        <input type="hidden" name="idnfepedido" value="<?= $_1_u_nf_idnfe ?>">
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>

                                    </tr>
                                    <tr>
                                        <td align="right" nowrap>Inf. Frete:</td>
                                        <td><input <?= $readonlynf ?> name="_1_<?= $_acao ?>_nf_inffrete" size="20" type="text" value="<?= $_1_u_nf_inffrete ?>"></td>
                                        <td align="right">Frete:</td>
                                        <td>
                                            <?
                                            if ($_1_u_nf_modfrete == 9) {
                                                echo ("9-Sem Frete");
                                            } elseif ($_1_u_nf_modfrete == 2) {
                                                echo ("2-Por conta de Terceiro");
                                            } elseif ($_1_u_nf_modfrete == 3) {
                                                echo ("0-Por conta do Emitente Incluso na nota");
                                            } elseif ($_1_u_nf_modfrete == 1) {
                                                echo ("1-Por conta do Destinatário/Remetente");
                                            } else {
                                                echo ("0-Por conta do Emitente");
                                            }
                                            ?>
                                            <input name="_1_<?= $_acao ?>_nf_icms" id="vlricms" size="8" type="hidden" value="<?= $vtotalicms ?>" vdecimal>
                                            <input name="_1_<?= $_acao ?>_nf_ipi" id="vlripi" size="8" type="hidden" value="<?= $vtotalipi ?>" vdecimal>
                                        </td>
                                    </tr>
                                    <?
                                    if (!empty($_1_u_nf_idpessoa)) {
                                      
                                       
                                        if (empty($_1_u_nf_idtransportadora)) {
                                            $rowt = PedidoController:: buscarTransportadorPorIdpessoa($_1_u_nf_idpessoa);
                                            $_1_u_nf_idtransportadora = $rowt['idtransportadora'];
                                        } else {
                                            $idtransportadorapr = $_1_u_nf_idtransportadora;
                                        }

                                        if (!empty($idtransportadorapr)) {
                                            $fverificactransp = "verificatransp(this," . $idtransportadorapr . ",'" . $rowt['nome'] . "');";
                                        }
                                    }
                                    $alteratransp = "alterartransportadora(this," . $_1_u_nf_idnf . ");";
                                    if (empty($_1_u_nf_infcorrecao) and $_1_u_nf_envionfe == 'CONCLUIDA') {
                                        $disablednftr = "disabled='disabled'";
                                    } ?>
                                    <tr>
                                        <td align="right">Transportadora:</td>
                                        <td><input name="idtransportadora" value="<?= $_1_u_nf_idtransportadora ?>" type="hidden">
                                            <select <?= $disablednftr ?> name="_1_<?= $_acao ?>_nf_idtransportadora" onchange="<? if (!empty($_1_u_nf_nnfe) and $_1_u_nf_status == 'CONCLUIDO') {
                                                                                                                                echo $alteratransp;
                                                                                                                            } else {
                                                                                                                                echo $fverificactransp;
                                                                                                                            } ?>">
                                                <option value=""></option>
                                                <? fillselect(PedidoController::listarTransportadora(), $_1_u_nf_idtransportadora); ?>
                                            </select>
                                        </td>
                                        <?
                                        if (!empty($_1_u_nf_idendrotulo)) {
                                            
                                          
                                            $rowufcid =PedidoController::buscarEnderecoFaturamentoPorId($_1_u_nf_idendrotulo);
                                        }
                                        if (!empty($_1_u_nf_idtransportadora) && !empty($rowufcid["codcidade"])) {
                                           

                                            $rowrota =PedidoController::buscarRotaUfPorTransportadora($_1_u_nf_idtransportadora,$rowufcid["uf"],$rowufcid["codcidade"]);
                                            $qtdrota = count($rowrota);
                                            if ($qtdrota > 0) {
                                                           ?>
                                                <td>Sugestão Entrega: <?= $rowrota["prazoentrega"] ?> dias úteis
                                                    <i class="fa fa-info-circle fa-1x  pointer hoverpreto btn-lg tip">
                                                        <span style="width:max-content;">OBS: <?= $rowrota["obs"] ?></span>
                                                    </i>
                                                </td>
                                        <?
                                            }
                                        }
                                        ?>
                                        
                                    </tr>
                                </table>
                                <? if ($_1_u_nf_status != 'PRODUCAO' and $_1_u_nf_status != 'PEDIDO') { ?>

                                    <? if (empty($_1_u_nf_pesob)) {
                                        $_1_u_nf_pesob = "";
                                    } ?>
                                    <? if (empty($_1_u_nf_pesol)) {
                                        $_1_u_nf_pesol = "";
                                    } ?>

                                    <table>
                                        <tr>
                                            <td align="right">Quantidade:</td>
                                            <td><input class="size7" name="_1_<?= $_acao ?>_nf_qvol" size="20" type="text" value="<?= $_1_u_nf_qvol ?>" vdecimal></td>
                                            <td align="right">Espécie:</td>
                                            <td><input <?= $readonlynf ?> class="size7" name="_1_<?= $_acao ?>_nf_esp" size="20" type="text" value="<?= $_1_u_nf_esp ?>"></td>

                                            <td align="right">Marca:</td>
                                            <td><input <?= $readonlynf ?> class="size10" name="_1_<?= $_acao ?>_nf_marca" size="20" type="text" value="<?= $_1_u_nf_marca ?>"></td>
                                            <td align="right">Numeração:</td>
                                            <td><input <?= $readonlynf ?> class="size7" name="_1_<?= $_acao ?>_nf_nvol" size="20" type="text" value="<?= $_1_u_nf_nvol ?>"></td>
                                        </tr>
                                        <tr>

                                            <td align="right">Peso B:</td>
                                            <td><input class="size7" name="_1_<?= $_acao ?>_nf_pesob" size="20" type="text" value="<?= $_1_u_nf_pesob ?>" vdecimal></td>
                                            <td align="right">Peso L:</td>
                                            <td><input <?= $readonlynf ?> class="size7" name="_1_<?= $_acao ?>_nf_pesol" size="20" type="text" value="<?= $_1_u_nf_pesol ?>" vdecimal></td>
                                            <td align="right">Custo (R$):</td>
                                            <td><input <?= $readonlynf ?> class="size7" name="_1_<?= $_acao ?>_nf_custoenvio" size="20" type="text" value="<?= $_1_u_nf_custoenvio ?>" vdecimal></td>
                                        </tr>
                                    </table>
                                    <table>
                                        <tr>
                                            <th>Lista de Postagem</th>
                                            <? if (!empty($_1_u_nf_idnf)) { ?>
                                                <td>
                                                    <a title="Imprimir Rótulo" class="fa fa-print fa-lg cinza pointer hoverazul hidden" onclick="janelamodal('report/rotulo.php?_modulo=<?= $_GET['_modulo'] ?>&idnf=<?= $_1_u_nf_idnf ?>')"></a>
                                                    <a title="Imprimir Rótulo" class="fa fa-print fa-lg cinza pointer hoverazul" onclick="showModalEtiqueta()"></a>
                                                </td>
                                            <? } ?>
                                        </tr>
                                    </table>
                                    <?
                                } // if( $_1_u_nf_status!='PRODUCAO' and $_1_u_nf_status!='PEDIDO'){
                                if (!empty($_1_u_nf_idtransportadora) and !empty($_1_u_nf_idendrotulo)) {
                                    $rowob = PedidoController::buscarRotaPorEndereco($_1_u_nf_idendrotulo,$_1_u_nf_idtransportadora);
                                    if (!empty($rowob['obs'])) { ?>
                                        <table>
                                            <tr>
                                                <td colspan="2">
                                                    <font size="2" style="color: red;"><b><?= str_replace(chr(13), "<br>", $rowob['obs']) ?></b></font>
                                                </td>
                                            </tr>
                                        </table>
                                <?
                                    }
                                } ?>

                                <? if (!empty($_1_u_nf_idnf) && ($_1_u_nf_status != 'CONCLUIDO' && $_1_u_nf_status != 'CANCELADO')) {
                                    $qtdcte = count($ctesVinculadasNF);
                                    ?>
                                    <p>
                                        <hr>
                                        <table class="table table-striped planilha">
                                            <tr class="cabitem">
                                                <th colspan="9" align="center">Cte Vinculado</th>
                                            </tr>
                                            <?
                                            if ($qtdcte > 0) {
                                            ?>
                                                <tr>
                                                    <th>Cte</th>
                                                    <th>Transportador</th>
                                                    <th>CFOP</th>
                                                    <th>Emissão</th>
                                                    <th>Status</th>
                                                    <th>Total Cte</th>
                                                    <th>%</th>
                                                    <th>Valor Pedido</th>
                                                    <th></th>
                                                </tr>
                                                <?
                                                $totalcte = 0;
                                                foreach($ctesVinculadasNF as $item) {


                                                    $cte = PedidoController::buscarvalorCtePedido($item['idnf'] ,$_1_u_nf_idnf);

                                                    if(empty($cte['percentual'])){
                                                        $cte['percentual']='100,00';
                                                    }

                                                    
                                                    if(empty($cte['valorcalc'])){
                                                        $cte['valorcalc']=$item['total'];
                                                    }

                                                    if ($item['status'] != 'CANCELADO') {
                                                        $totalcte = $totalcte +$cte['valorcalc'];
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td><?= $item['nnfe'] ?></td>
                                                        <td><?= $item['nome'] ?></td>
                                                        <td><?= $item['cfop'] ?></td>
                                                        <td><?= $item['emissao'] ?></td>
                                                        <td><?= $item['status'] ?></td>
                                                        <td>
                                                            <?= number_format(tratanumero($item['total']), 2, ',', '.'); ?>
                                                        </td>
                                                        <td>
                                                            <?= number_format(tratanumero($cte['percentual']), 2, ',', '.'); ?>
                                                        </td>
                                                        <td>
                                                            <?= number_format(tratanumero($cte['valorcalc']), 2, ',', '.'); ?>
                                                        </td>
                                                        <td>
                                                            <a class="fa fa-bars pointer hoverazul" title="CTe" onclick="janelamodal('?_modulo=nfcte&_acao=u&idnf=<?= $item['idnf'] ?>')"></a>
                                                        </td>                                                  
                                                    </tr>
                                                <?
                                                }
                                                ?>
                                                <tr>
                                                    <td colspan="7">Total</td>
                                                    <td colspan="2">
                                                        <?= number_format(tratanumero($totalcte), 2, ',', '.'); ?>
                                                        <? if ($_1_u_nf_total > 10) { ?> (<font color="red"><?= number_format(tratanumero((($totalcte * 100) / $_1_u_nf_total)), 2, ',', '.') ?>%</font>)<? } ?>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                            <tr>
                                                <td colspan="9">
                                                    <i id="btn-modal-frete" class="fa fa-plus-circle fa-2x verde btn-lg pointer" title="Gerar CTe"></i>
                                                </td>
                                            </tr>
                                        </table>
                                    </p>
                                <?
                                } //if(!empty($_1_u_nf_idnfe)){
                                ?>
                            </div>
                        </div>
                        <? if ($_1_u_nf_status != 'PEDIDO' && $_1_u_nf_status != 'PRODUCAO' && $_1_u_nf_status != 'EXPEDICAO') { ?>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-md-1" data-toggle="collapse" href='#collapseemail'>
                                            Email
                                        </div>
                                        <div class="col-md-1">
                                            <? if (!empty($_1_u_nf_idnf)) {
                                              
                                                $reseo=PedidoController::buscarlog($_1_u_nf_idnf,'nf','EMAILNFE');
                                                $qtdeo = count($reseo);

                                                if ($qtdeo > 0) {
                                            ?>
                                                    <div class="oEmailorc">
                                                        <a class="fa fa-search azul pointer hoverazul" title=" Ver Log Email" data-target="webuiPopover0"></a>
                                                    </div>
                                                    <div class="webui-popover-content">
                                                        <?
                                                        foreach($reseo as $roweo ) { ?>
                                                            <li><?= $roweo["log"] ?> <?= $roweo["status"] ?> <?= dmahms($roweo["criadoem"]) ?></li>
                                                        <? } ?>
                                                    </div>
                                            <?
                                                }
                                            } ?>
                                        </div>
                                        <div class="col-md-7"></div>
                                        <? if ($_1_u_nf_envioemail == 'Y') {
                                            $classtdemail = "amarelo";
                                            $emailval = 'N';
                                        } elseif ($_1_u_nf_envioemail == 'O') {
                                            $classtdemail = "verde";
                                            $emailval = 'N';
                                        } elseif ($_1_u_nf_envioemail == 'E') {
                                            $classtdemail = "vermelho";
                                            $emailval = 'N';
                                        } else {
                                            $classtdemail = "cinza";
                                            $emailval = 'Y';
                                        } ?>
                                        <div class="col-md-1" id="warningcert"></div>
                                        <?
                                        // GVT - 24/04/2020 - Busca domínios da empresa relacionada a NF.
                                        //					- Só é possível ter um domínio do tipo PRODUTO e um do tipo SERVIÇO, pois está configurado no banco de dados.
                                        $dominioproduto = 0;
                                        $dominioservico = 0;
                                        $disabledominio = 0;
                                       
                                        $resdominio = PedidoController::buscarEmailOrcamentoProdutoServicoPorEmpresa();
                                        $qtdrowdominio = count($resdominio);
                                        if ($qtdrowdominio == 0) {
                                            $disabledominio = 1;
                                        } else {
                                            foreach($resdominio as $rowdominio ) {
                                                switch ($rowdominio["tipoenvio"]) {
                                                    case 'NFP':
                                                        $dominioproduto = 1;
                                                        break;
                                                    case 'NFPS':
                                                        $dominioservico = 1;
                                                        break;
                                                    default:
                                                        $disabledominio = 1;
                                                        break;
                                                }
                                            }
                                        }

                                        if (!$disabledominio) { ?>
                                            <div class="col-md-1">
                                                <a id="enviarnf" class="fa fa-envelope pointer <?= $classtdemail ?>" valor="<?= $emailval ?>" title="Enviar email NFe" onclick="envioemail(this,<?= $_1_u_nf_idnf ?>);"></a>
                                            </div>
                                            <?
                                            $rowemail = PedidoController:: buscarEmailFilaNfPorId($_1_u_nf_idnf);
                                            $numemail = count($rowemail);
                                            if ($numemail > 0) { ?>
                                                <div class="col-md-1">
                                                    <a class="pull-right" title="Ver emails enviados" onclick="janelamodal('?_modulo=envioemail&_acao=u&idmailfila=<?= $rowemail['idmailfila'] ?>')"><i class="fa fa-envelope-o cinza pointer"></i><i style="z-index: 2300;margin-left:-5px;margin-top:-7px;" class="fa fa-search cinza cinza pointer"></i></a>
                                                </div>
                                        <? }
                                        } ?>
                                    </div>
                                </div>
                                <div class="panel-body " id="collapseemail">

                                    <? if ($_1_u_nf_envionfe == 'CONCLUIDA') { ?>
                                        <table>
                                            <tr>
                                                <td align="right" nowrap>Email Danfe?
                                                    <? if ($_1_u_nf_emaildanfe == 'Y') {
                                                        $checked = 'checked';
                                                        $vchecked = 'N';
                                                    } else {
                                                        $checked = '';
                                                        $vchecked = 'Y';
                                                    } ?>
                                                    <input title="Email Danfe" type="checkbox" <?= $checked ?> name="nameemaildanfe" onclick="altcheck('nf','emaildanfe',<?= $_1_u_nf_idnf ?>,'<?= $vchecked ?>')">
                                                </td>
                                                <td align="right" nowrap>Email XML?
                                                    <? if ($_1_u_nf_emailxml == 'Y') {
                                                        $checked = 'checked';
                                                        $vchecked = 'N';
                                                    } else {
                                                        $checked = '';
                                                        $vchecked = 'Y';
                                                    } ?>
                                                    <input title="Email xml" type="checkbox" <?= $checked ?> name="nameemailxml" onclick="altcheck('nf','emailxml',<?= $_1_u_nf_idnf ?>,'<?= $vchecked ?>')">
                                                </td>
                                                <td>
                                                    <?                                              
                                                    $qrp=PedidoController::buscarFaturaBoletoPorNf($_1_u_nf_idnf);
                                                    
                                                    $qtdrowsp = count($qrp);
                                                    if ($qtdrowsp > 0) {
                                                        if ($_1_u_nf_emailboleto == 'Y') {
                                                            $checked = 'checked';
                                                            $vchecked = 'N';
                                                        } else {
                                                            $checked = '';
                                                            $vchecked = 'Y';
                                                        } ?> Email Boleto?
                                                        <input title="Email Boleto" type="checkbox" <?= $checked ?> name="nameemailboleto" onclick="altcheck('nf','emailboleto',<?= $_1_u_nf_idnf ?>,'<?= $vchecked ?>')">
                                                        <input type="hidden" name="_nf_emailboleto" value="<?=$_1_u_nf_emailboleto?>">
                                                        <input type="hidden" name="qtd_boleto_faturar" value="<?=$qtdrowsp?>">
                                                    <? } ?>
                                                </td>
                                            </tr>
                                        </table>
                                    <? } ?>
                                    <table>
                                        <tr>
                                            <td align="right">Cod. Rastreador:</td>
                                            <td>
                                                <input name="rastreador" type="hidden" value="<?= $_1_u_nf_rastreador ?>">
                                                <input name="_1_<?= $_acao ?>_nf_rastreador" size="" type="text" value="<?= $_1_u_nf_rastreador ?>">
                                            </td>
                                            <td class="tdbr" align="right">
                                                <? if ($_1_u_nf_enviarastreador == 'Y') {
                                                    $checked = 'checked';
                                                    $vchecked = 'N';
                                                } else {
                                                    $checked = '';
                                                    $vchecked = 'Y';
                                                } ?>
                                                <input title="Enviar rastreador" type="checkbox" <?= $checked ?> name="namerastreador" onclick="altcheck('nf','enviarastreador',<?= $_1_u_nf_idnf ?>,'<?= $vchecked ?>')">
                                            </td>
                                        </tr>
                                        <? if ($_1_u_nf_tipoenvioemail == 'VENDA') {
                                            $checked = 'checked';
                                        } else {
                                            $checked = '';
                                        }
                                        if ($_1_u_nf_tipoenvioemail == 'MATERIAL') {
                                            $checkedN = 'checked';
                                        } else {
                                            $checkedN = '';
                                        }

                                        if ($_1_u_nf_envioemail == 'Y') {
                                            $classtdemail = "amarelo";
                                            $emailval = 'N';
                                        } elseif ($_1_u_nf_envioemail == 'O') {
                                            $classtdemail = "verde";
                                            $emailval = 'N';
                                        } elseif ($_1_u_nf_envioemail == 'E') {
                                            $classtdemail = "vermelho";
                                            $emailval = 'N';
                                        } else {
                                            $classtdemail = "cinza";
                                            $emailval = 'Y';
                                        }

                                        if (empty($_1_u_nf_emaildadosnfe) or empty($_1_u_nf_emaildadosnfemat) and !empty($_1_u_nf_idpessoafat)) {

                                            if (empty($_1_u_nf_emaildadosnfe)) {
                                               
                                                $resem = PedidoController::buscarPessoaEmailNfePorId($_1_u_nf_idpessoafat);
                                                if (count($resem) > 0) {
                                                    $virg = "";
                                                    foreach ($resem as $rowem) {
                                                        if (!empty($rowem['emailxmlnfe'])) {
                                                            $_1_u_nf_emaildadosnfe .= $virg . $rowem['emailxmlnfe'];
                                                            $virg = ",";
                                                        }
                                                    }
                                                }

                                              
                                                $resem = PedidoController::buscarPessoaEmailNfeCc($_1_u_nf_idpessoafat);
                                                if (count($resem) > 0) {
                                                    foreach ($resem as $rowem) {
                                                        if (!empty($rowem['emailxmlnfecc'])) {
                                                            $_1_u_nf_emaildadosnfe .= $virg . $rowem['emailxmlnfecc'];
                                                            $virg = ",";
                                                        }
                                                    }
                                                }
                                            }


                                            if (empty($_1_u_nf_emaildadosnfemat)) {

                                                $resem = PedidoController::buscarPessoaEmailMaterialNfe($_1_u_nf_idpessoafat);
                                              
                                                if (count($resem) > 0) {
                                                    $virg = "";
                                                    foreach ($resem as $rowem) {
                                                        if (!empty($rowem['emailmaterial'])) {
                                                            $_1_u_nf_emaildadosnfemat .= $virg . $rowem['emailmaterial'];
                                                            $virg = ",";
                                                        }
                                                    }
                                                }

                                               
                                                $resem = PedidoController::buscarPessoaEmailNfePorId($_1_u_nf_idpessoafat);
                                                if (count($resem) > 0) {
                                                    $virg = "";
                                                    foreach ($resem as $rowem) {
                                                        if (!empty($rowem['emailmat'])) {
                                                            $_1_u_nf_emaildadosnfemat .= $virg . $rowem['emailmat'];
                                                            $virg = ",";
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        if ($disabledominio) { ?>
                                            <tr>
                                                <td colspan="5">
                                                    <label class="alert-danger">Empresa sem domínio configurado</label>
                                                </td>
                                            </tr>
                                            <? } else {
                                            if ($dominioproduto) {
                                                
                                                $rowemailobj1 =PedidoController::buscarEmpresaemailobjPorTipoId( $_1_u_nf_idnf,'NFP');
                                                                                           
                                                $resdominio1 = PedidoController::buscarEmpresaemailobjPorTipoIdempresa($_1_u_nf_idempresa,'NFP');
                                                $qtddominio1 = count($resdominio1);
                                                $_cont = 0;
                                                if ($qtddominio1 > 0) {
                                                    foreach( $resdominio1  as $rowdominio1) {
                                                        if (($rowdominio1["idemailvirtualconf"] == $rowemailobj1["idemailvirtualconf"]) and ($checked == 'checked')) {
                                                            $chk1 = 'checked';
                                                        } else {
                                                            $chk1 = '';
                                                        } ?>
                                                        <tr>
                                                            <td colspan="5">
                                                                <input title="Email Vendas" type="radio" <?= $chk1 ?> value="<?=$rowdominio1["idemailvirtualconf"]?>" <?=$disablednfStatus?> <?=$readonlynfStatus?> name="nameemailnfe" onclick="alttipoemail(<?=$_1_u_nf_idnf ?>, 'VENDA', <?=$rowdominio1["idemailvirtualconf"] ?>, <?=$rowdominio1["idempresa"] ?>, '<?=$rowdominio1["dominio"]?>', '<?=$rowemailobj1["idempresaemailobjeto"]?>')">
                                                                <input type="hidden" name="idemailvirtualconf-<?=$rowdominio1["idemailvirtualconf"]?>" value="<?=$rowdominio1["idemailvirtualconf"]?>">
                                                                <input type="hidden" name="tipoenvioemail-<?=$rowdominio1["idemailvirtualconf"]?>" value="VENDA">
                                                                <input type="hidden" name="idempresa_dominio-<?=$rowdominio1["idemailvirtualconf"]?>" value="<?=$rowdominio1["idempresa"]?>">
                                                                <input type="hidden" name="emaildadosnfemat-<?=$rowdominio1["idemailvirtualconf"]?>" value="<?=$rowdominio1["dominio"]?>">
                                                                <label class="alert-warning"><?= $rowdominio1["dominio"] ?> </label>
                                                                <? if ($_cont == 0) { ?>- Produtos
                                                                    <a id="editaremail" class="fa fa-pencil hoverazul btn-lg pointer" onclick="editaremail('emaildadosnfe','emaildadosnfeinput');" title="Editar email material"></a>
                                                                <? } ?>
                                                        </tr>
                                                        <?
                                                        $_cont++;
                                                    }
                                                }
                                                if (!empty($_1_u_nf_emaildadosnfe)) {
                                                    $textarea = "hidden";
                                                } else {
                                                    $obslist = "hidden";
                                                } ?>
                                                <tr class="<?= $obslist ?>" id='emaildadosnfe'>
                                                    <td align="right">Email(s):</td>
                                                    <td colspan="8" style="word-break:break-word">
                                                        <? //maf: consultar diretamente dos logs de SMTP //nl2br($_1_u_nf_emaildadosnfe)
                                                        echo consultaLogsSmtp('nfp', $_1_u_nf_idnf, "table");
                                                        ?>
                                                    </td>
                                                    </td>
                                                </tr>
                                                <tr class="<?= $textarea ?>" id='emaildadosnfeinput'>
                                                    <td align="right">Email(s):</td>
                                                    <td colspan="8">
                                                        <textarea name="_1_<?= $_acao ?>_nf_emaildadosnfe" style="width: 550px; height: 35px;" onchange="emaildadosnfe(this);"><?= $_1_u_nf_emaildadosnfe ?></textarea>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                            <? if ($dominioservico) {
                                              
                                                $rowemailobj2=PedidoController::buscarEmpresaemailobjPorTipoId( $_1_u_nf_idnf,'NFPS');                                                

                                                $resdominio2 =  PedidoController::buscarEmpresaemailobjPorTipoIdempresa($_1_u_nf_idempresa,'NFPS');
                                                $qtddominio2 = count($resdominio2);
                                                $_cont = 0;
                                                if ($qtddominio2 > 0) {
                                                    foreach( $resdominio2  as $rowdominio2) {                                                    
                                                        if (($rowdominio2["idemailvirtualconf"] == $rowemailobj2["idemailvirtualconf"]) and ($checkedN == 'checked')) {
                                                            $chk2 = 'checked';
                                                        } else {
                                                            $chk2 = '';
                                                        } ?>
                                                        <tr>
                                                            <td colspan="5">
                                                                <input title="Email Material" type="radio" <?= $chk2 ?> value="<?=$rowdominio2["idemailvirtualconf"]?>" <?=$disablednfStatus?> <?=$readonlynfStatus?> name="nameemailnfe" onclick="alttipoemail(<?= $_1_u_nf_idnf ?>,'MATERIAL', <?=$rowdominio2['idemailvirtualconf'] ?>, <?=$rowdominio2['idempresa'] ?>, '<?=$rowdominio2['dominio']?>', '<?=$rowemailobj2['idempresaemailobjeto']?>')">
                                                                <input type="hidden" name="emaildadosnfe-<?=$rowdominio2["idemailvirtualconf"]?>" value="<?=$rowdominio2["idemailvirtualconf"]?>">
                                                                <input type="hidden" name="tipoenvioemail-<?=$rowdominio2["idemailvirtualconf"]?>" value="MATERIAL">
                                                                <input type="hidden" name="idempresa_dominio-<?=$rowdominio2["idemailvirtualconf"]?>" value="<?=$rowdominio2["idempresa"]?>">
                                                                <input type="hidden" name="emaildadosnfemat-<?=$rowdominio2["idemailvirtualconf"]?>" value="<?=$_1_u_nf_emaildadosnfemat?>">
                                                                <label class="alert-warning"><?= $rowdominio2["dominio"] ?></label>
                                                                <? if ($_cont == 0) { ?> - Serviços e Diversos
                                                                    <a id="editar_email" class="fa fa-pencil hoverazul btn-lg pointer" onclick="editaremail('emailmat','emailmatinput');" title="Editar email material"></a>
                                                                <? } ?>
                                                            </td>
                                                        </tr>
                                                <?
                                                        $_cont++;
                                                    }
                                                }
                                                if (!empty($_1_u_nf_emaildadosnfemat)) {
                                                    $textarea = "hidden";
                                                } else {
                                                    $obslist = "hidden";
                                                } ?>
                                                <tr class="<?= $obslist ?>" id='emailmat'>
                                                    <td align="right">Email(s):</td>
                                                    <td colspan="8">
                                                        <?= nl2br($_1_u_nf_emaildadosnfemat) ?>
                                                    </td>
                                                </tr>
                                                <tr class="<?= $textarea ?>" id='emailmatinput'>
                                                    <td align="right">Email(s):</td>
                                                    <td colspan="8">
                                                        <textarea name="_1_<?= $_acao ?>_nf_emaildadosnfemat" id="emaildadosnfemat" style="width: 550px; height: 35px;" onchange="emaildadosnfemat(this);"><?= $_1_u_nf_emaildadosnfemat ?></textarea>
                                                    </td>
                                                </tr>
                                        <? }
                                        } ?>
                                        <tr>
                                            <td>
                                                <? $existepv = strpos($_1_u_nf_emaildadosnfe, ";");
                                                if ($existepv === false) {
                                                    null;
                                                } else {
                                                    echo "<br><font color='red'>Atenção: Utilizar Vírgula para separar Emails!</font></br>";
                                                } ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                    <? }
                    } // if($_1_u_nf_status!='EXPEDICAO' and $_1_u_nf_status!='PRODUCAO')
                    ?>
                </div>
                <?

                if ($_acao == 'u' and !empty($_1_u_nf_idenderecofat)
                     and ($_1_u_nf_status == "FATURAR" or $_1_u_nf_status == "EXPEDICAO" or
                            $_1_u_nf_status == "CONCLUIDO" or $_1_u_nf_status=="ENVIAR" or
                            $_1_u_nf_status == "ENVIADO" or $_1_u_nf_status == "CANCELADO" or 
                            $_1_u_nf_status == "DEVOLVIDO" or $_1_u_nf_status =="RECUSADO")
                     ) { 

                    $rowconf = PedidoController::buscarTotalCofinsNF($_1_u_nf_idnf);

                    
                    $rowpis =PedidoController::buscarTotalPisNF($_1_u_nf_idnf);

                    if ($rowconf['cofins'] > 0 or   $rowpis['vpis'] > 0 or $icmsufdest_ufremet == "Y") {
                        $_1_u_nf_cofins = $rowconf['cofins'];
                        $_1_u_nf_pis = $rowpis['vpis'];
                ?>
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading" data-toggle="collapse" href="#collapseimpostos">Impostos</div>
                                <div class="panel-body" id="collapseimpostos">
                                    <table>
                                        <tr>
                                            <td align="right">&nbsp;&nbsp;&nbsp;&nbsp;PIS (0,65%):</td>
                                            <td>
                                                <input name="_1_<?= $_acao ?>_nf_pis" class="size6" type="text" value="<?= $_1_u_nf_pis ?>">
                                            </td>
                                            <td align="right">&nbsp;&nbsp;&nbsp;&nbsp;Cofins (3,00%):</td>
                                            <td>
                                                <input name="_1_<?= $_acao ?>_nf_cofins" class="size6" type="text" value="<?= $_1_u_nf_cofins ?>">
                                            </td>
                                        </tr>
                                    </table>
                                    <?
                                    
                                    $resg = PedidoController::buscarImpostosGNENf($_1_u_nf_idnf);

                                    $_qtdimp = count($resg);

                                    if ($_qtdimp > 0) {
                                    ?>
                                        <br>
                                        <table class="table table-striped planilha">
                                            <tr>
                                                <th>Descrição</th>
                                                <th>Categoria</th>
                                                <th>Tipo</th>
                                                <th>Vencimento</th>
                                                <th>Valor</th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                            <? $vlritemp = 0;
                                            foreach($resg as $rowg){
                                                $i = $i + 1;
                                                $vlritemp = $vlritemp + $rowg['vlritem'];

                                                if ($rowg["tiponota"] == 'R') {
                                                    $link = "comprasrh";
                                                } else {
                                                    $link = "nfentrada";
                                                }

                                            ?>
                                                <tr>
                                                    <td <? if (empty($rowg["prodservdescr"])) { ?>style="background-color: red" <? } ?>>
                                                        <input name="_<?= $i ?>_u_nfitem_idnfitem" size="8" type="hidden" value="<?= $rowg["idnfitem"]; ?>">
                                                        <input name="_<?= $i ?>_u_nfitem_qtd" class="size5" type="hidden" value="<?= $rowg["qtd"]; ?>" vdecimal>

                                                        <input name="_<?= $i ?>_u_nfitem_prodservdescr" size="20" type="text" value="<?= $rowg["prodservdescr"] ?>">
                                                    </td>
                                                    <td>
                                                        <select id="idcontaitem<?= $rowg["idnfitem"] ?>" class='size15' name="_<?= $i ?>_u_nfitem_idcontaitem" vnulo onchange="preencheti(<?= $rowg["idnfitem"] ?>)">
                                                            <option value=""></option>
                                                            <? fillselect(getContaItemSelect(), $rowg['idcontaitem']); ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <?
                                                        if ($_1_u_nf_status == 'CONCLUIDO') {
                                                      ?>
                                                        <select id="idtipoprodserv<?= $rowg["idnfitem"] ?>" class='size15' name="_<?= $i ?>_u_nfitem_idtipoprodserv" vnulo>
                                                            <option value=""></option>
                                                            <? fillselect(PedidoController::buscarProdservTipoProdServ(), $rowg['idtipoprodserv']); ?>
                                                        </select>
                                                        <?
                                                        } elseif ($rowg['idcontaitem']) {
                                                            $sqlit = "select e.idtipoprodserv,t.tipoprodserv
                                                                        from contaitemtipoprodserv e 
                                                                                join tipoprodserv t on(t.idtipoprodserv=e.idtipoprodserv )
                                                                        where e.idcontaitem=" . $rowg['idcontaitem'] . " order by t.tipoprodserv";
                                                        ?>
                                                        <select id="idtipoprodserv<?= $rowg["idnfitem"] ?>" class='size15' name="_<?= $i ?>_u_nfitem_idtipoprodserv" vnulo>
                                                            <option value=""></option>
                                                            <? fillselect(PedidoController::buscarContaItemTipoProdservTipoProdServ($rowg['idcontaitem']), $rowg['idtipoprodserv']); ?>
                                                        </select>
                                                        <?
                                                        } else {
                                                            
                                                            ?>
                                                            <select id="idtipoprodserv<?= $rowg["idnfitem"] ?>" class='size15' name="_<?= $i ?>_u_nfitem_idtipoprodserv" vnulo>
                                                                <option value=""></option>
                                                                <? fillselect(PedidoController::$ArrayVazio, $rowg['idtipoprodserv']); ?>
                                                            </select>
                                                            <?
                                                        }
                                                        ?>
                                                      
                                                    </td>
                                                    <td><?= $rowg['datarecebimento'] ?></td>
                                                    <td <? if (empty($rowg["vlritem"])) { ?>style="background-color: red" <? } ?>>
                                                        <?= number_format(tratanumero($rowg["total"]), 2, ',', '.'); ?>
                                                    </td>

                                                    <? //Acrescentado para excluir impostos - LTM (03/08/2020 - 363864) 
                                                    ?>
                                                    <td>
                                                        <a class="fa fa-bars pointer hoverazul" title="Nf" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?= $rowg["idcontapagar"] ?>')"></a>
                                                    </td>
                                                    <td>
                                                        <?if($rowg['status']!="QUITADO"){?>
                                                        <i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" onclick="excluiritem(<?= $rowg["idnfitem"] ?>)" alt="Excluir !"></i>
                                                        <?}?>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                            <tr>
                                                <td colspan="4" align="right"></td>
                                                <td><?= number_format(tratanumero($vlritemp), 2, ',', '.'); ?></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        </table>
                                    <? } 
                                    
                                    
                                    
                                    $rescom = PedidoController::buscarImpostosNf($_1_u_nf_idnf);
                                    $qrcom = count($rescom);
                                    if ($qrcom > 0) {
                                    ?>
                                     <br>
                                        <table class="table table-striped planilha">
                                            <? 
                                            $vlritemp=0;
                                            foreach($rescom as $rowp2){
                                                $vlritemp=$vlritemp+$rowp2['valor'];    
                                            ?>
                                                <tr>
                                                    <th></th>
                                                    <td><? echo ($rowp2["descricao"]); ?></td>
                                                    <th>Parcela:</th>
                                                    <td><? echo ($rowp2["parcela"] . " de " . $rowp2["parcelas"]); ?></td>
                                                    <td colspan="2"><?= $rowp2["nome"] ?></td>
                                                    <th>Recebimento:</th>
                                                    <td>
                                                        <input type="hidden" class="datarecebparc" value="<?=$rowp2["datareceb"]?>" statusCI="<?=$rowp2["status"]?>" parcela="<?=$rowp2['parcela']?>">
                                                        <?= dma($rowp2["datareceb"]) ?>
                                                    </td>
                                                    <th>Status:</th>
                                                    <td><?= $rowp2["status_item"] ?></td>
                                                    <th>Valor:</th>
                                                    <td>
                                                        <?  
                                                        $resao =PedidoController:: buscarRestaurarPorIdlp(getModsUsr("LPS"));
                                                        $qtdao = count($resao);
                                                        if ($qtdao > 0 and $rowp2["status_item"] != "QUITADO") { ?>
                                                            <input name="contapagaritem_valor" class="size10" type="text" value="<?= $rowp2["valor"] ?>" onchange="atualizavlitem(this,<?= $rowp2["idcontapagaritem"] ?>)">
                                                        <? } else { ?>
                                                            <?= number_format(tratanumero($rowp2["valor"]), 2, ',', '.'); ?>
                                                        <? } ?>
                                                    </td>
                                                    <td>
                                                        <a class="fa fa-bars fa-1x cinzaclaro hoverazul btn-lg pointer" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?= $rowp2["idcontapagar"] ?>');"></a>
                                                    </td>
                                                </tr>
                                            <? } //while ($rowp2 = mysqli_fetch_array($qrp2))?>
                                            <tr>
                                                <td colspan="11" align="right"></td>
                                                <td><?= number_format(tratanumero($vlritemp), 2, ',', '.'); ?></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        </table>
                                    <?}?>
                                </div>
                            </div>
                        </div>
                    <?

                    }
                }


                $qrpx = PedidoController::buscarContapagaritemPorNf($_1_u_nf_idnf);
                $qtdrx = count($qrpx);
                //die($sqlx);
                if ($qtdrx > 0) { //tem contapagaritem
                    ?>
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading primeira" style="padding: 8px;">
                                <a data-toggle="collapse" href='#collapsefatp'>Faturamento</a>
                                <?if(!empty($_1_u_nf_controle) ){
                                        echo(' - Nosso Número: ');
                                        $rwrem = PedidoController::buscarRemssaEnvioPorIdnf($_1_u_nf_idnf);
                                        $qtdremessa = count($rwrem);
                                        if( $qtdremessa < 1 && $_1_u_nf_status != 'CONCLUIDO' && $_1_u_nf_status != 'CANCELADO'){
                                            ?>
                                            <button type="button" class="btn btn-primary  btn-xs" onclick="nossonum(<?=$_1_u_nf_idnf?>)" title="Atualizar nosso número, você deve atualizar este número em caso de alteração de valores para geração de novos boletos.">
                                                <?=$_1_u_nf_controle?> <i class="fa fa-refresh"></i>
                                            </button>
                                          
                                        <?
                                        }else{
                                            echo($_1_u_nf_controle);
                                        }
                                       }?>
                                
                           
                            </div>
                            <div class="panel-body " id="collapsefatp">
                                <table class="table table-striped planilha">
                                    <?
                                    $idcontaspagar = [];
                                    $mostrarAutomatica = false;
                                    $mostrarHistorico = false;
                                    foreach($qrpx as $fatura)
                                    {
                                        if($fatura['agruppessoa'] == 'N' && $fatura['agrupado'] == 'Y' && $fatura['agrupnota'] == 'N')
                                        {
                                            $mostrarAutomatica = true ;
                                        } 
                                        
                                        $historico = PedidoController::buscarHistoricoStatusPedidoPorIdcontapagar($pagvalmodulo, $fatura['idcontapagar'], $_1_u_nf_idnf);
                                        if(count($historico) > 1){
                                            $mostrarHistorico = true ;
                                        }
                                        array_push($idcontaspagar, $fatura['idcontapagar']); 
                                    }
                                    $idcontaspagar = implode(",", $idcontaspagar);
                                    $row = PedidoController::buscarRemessaPorIdcontapagar($idcontaspagar);
                                    $boletoTodos = count($row);
                                    ?>
                                    <tr>
                                        <th>Fatura</th>
                                        <th>Parcela</th>
                                        <th>Obs</th>
                                        <th>Recebimento</th>
                                        <th>Status</th>
                                        <? if($mostrarAutomatica == true) { ?>
                                            <th>Fatura Automática</th>
                                        <? 
                                            $somarFaturaColspan = 1;
                                        } 
                                        ?>
                                        <th>Valor</th>
                                        <? 
                                        $remessaFormaPagamento = PedidoController::buscarIdRemessaParaFormaPagamento($_1_u_nf_idformapagamento);
                                        if (($_1_u_nf_status == 'FATURAR' && !empty($_1_u_nf_nnfe)) && $remessaFormaPagamento['qtdLinhas'] > 0 && $boletoTodos == 0 && !empty($_1_u_nf_protocolonfe)) { ?>
                                            <th colspan="3">
                                                Remessa
                                                &nbsp;&nbsp;
                                                <input type="checkbox" onclick="marcarTodosRemessa(this)">&nbsp;&nbsp;
                                                <span class="enviarRemessa" style="display: none;">
                                                    <a class="fa fa-forward fa-1x azul hoverazul pointer" title="enviar Todos" onclick="gerarRemessaTodos(<?=$_1_u_nf_idnf?>, <?=$_1_u_nf_idformapagamento?>, this);"></a>
                                                </span>
                                            </th>
                                        <? 
                                        } elseif($boletoTodos > 0) {
                                            ?>
                                            <th colspan="3">
                                                Boleto
                                            </th>
                                            <?
                                        } ?>
                                        <th> 
                                            <? 
                                            if($mostrarHistorico) { 
                                                ?>
                                                Histórico 
                                                <? 
                                                $somarHistoricoColspan = 1;
                                            } ?></th>                                        
                                        <th></th>
                                    </tr>
                                    <? $totalcpg = 0;
                                    foreach($qrpx as $rowx )
                                    {
                                        if (!empty($rowx['idcontapagar'])) 
                                        {
                                           
                                            $rowp = PedidoController::buscarFaturaPorId($rowx['idcontapagar']);
                                            $totalcpg = $totalcpg + $rowx["valor"];
                                           
                                            $row = PedidoController::buscarRemessaPorIdcontapagar($rowx['idcontapagar']);
                                            $boleto = count($row);
                                            if ($rowp["boletopdf"] == 'Y') {
                                                $checked = 'checked';
                                                $vchecked = 'N';
                                            } else {
                                                $checked = '';
                                                $vchecked = 'Y';
                                            }
                                        } ?>
                                    
                                        <tr>    
                                            <!-- Fatura -->                                        
                                            <td><? echo ($rowp["parcela"]); ?></td>   
                                            <!-- Parcela -->                                           
                                            <td class="nowrap"><?= $rowx['parcela'] ?> de <?= $rowx['parcelas'] ?> </td> 
                                            <!-- Obs -->                                            
                                            <td>
                                                <input name="<?= $i ?>idcontapagarobs" id="idcontapagarobs" style="width: 250px; font-size: 11px" type="text" onchange="atualizaobscp(this,<?= $rowp["idcontapagar"] ?>)" value="<?= $rowp["obs"] ?>">
                                            </td>  
                                            <!-- Vencimento -->                                                   
                                            <td> 
                                                <input type="hidden" class="datarecebparc" value="<?=$rowp["datareceb"]?>" statusCI="<?=$rowp["status"]?>" parcela="<?=$rowx['parcela']?>">
                                                <?= $rowp["datareceb"] ?>
                                            </td>      
                                            <!-- Status -->                                                
                                            <td><label class="alert-warning"><?= $rowp["status"] ?></label></td>
                                            <!-- Fatura Automática -->           
                                            <? if ($rowx['agruppessoa'] == 'N' and $rowx['agrupado'] == 'Y' and $rowx['agrupnota'] == 'N') { ?>                                                
                                                <td>
                                                    <select <? if ($rowx['status'] == 'QUITADO') { ?> disabled="disabled" <? } ?> class="size15" name="contapagaritem_idformapagamento" onchange="atualizacontaitem(this,<?= $rowx["idcontapagaritem"] ?>,'idformapagamento')">
                                                        <option value=""></option>
                                                        <? fillselect(PedidoController::buscarFormapagamentoAgrupadoPorEmpresa(), $rowx['idformapagamento']); ?>
                                                    </select>
                                                </td>
                                            <? } ?>
                                            <!-- Valor -->    
                                            <td style="width: 98px;">
                                                <? if ($rowx["status"] != "QUITADO") {
                                                    if ($rowx['tobj'] == 'contapagar') {
                                                ?>
                                                        <input name="contapagaritem_valor" class="size10" type="text" value="<?= $rowx["valor"] ?>" onchange="atualizavlcp(this,<?= $rowx["idcontapagaritem"] ?>)">
                                                    <?
                                                    } else {
                                                    ?>
                                                        <input name="contapagaritem_valor" class="size10" type="text" value="<?= $rowx["valor"] ?>" onchange="atualizavlitem(this,<?= $rowx["idcontapagaritem"] ?>)">
                                                    <?
                                                    }
                                                } else { ?>
                                                    <?= number_format(tratanumero($rowx["valor"]), 2, ',', '.'); ?>
                                                <? } ?>
                                            </td>

                                            <!-- Boleto/Remessa -->
                                            <? if ($_1_u_nf_status == 'FATURAR' && !empty($_1_u_nf_nnfe) && $boleto == 0 && !empty($_1_u_nf_protocolonfe) && $remessaFormaPagamento['qtdLinhas'] > 0) { ?>
                                                <td colspan="3">
                                                    Remessa &nbsp;&nbsp; <?=$rowp['idcontapagar'] ?> &nbsp;&nbsp;
                                                    <input type="checkbox" class="gerarremessa" idcontapagar="<?=$rowp['idcontapagar'] ?>" name="gerarremessa" onclick="gerarRemessa(<?=$_1_u_nf_idnf?>, <?=$rowp['idcontapagar'] ?>, <?=$_1_u_nf_idformapagamento?>, this)">
                                                    <input type="text" class="hidden" value="<?=$_1_u_nf_protocolonfe?>" name="_protocol_nfe">
                                                </td>
                                            <? 
                                            } 
                                            
                                            if ($boleto > 0) { ?>
                                                <td>
                                                    <a class="fa fa-wpforms fa-1x btn-lg  cinzaclaro pointer hoverazul pointer" title="Boleto" onclick="janelamodal('inc/boletophp/<?= $rowx['boleto'] ?>.php?idcontapagar=<?= $rowp['idcontapagar'] ?>')"></a>
                                                </td>
                                            <? } ?>
                                            <? if ($boleto > 0) { ?>
                                                <td>
                                                    <input title="Boleto PDF" type="checkbox" <?=$checked?> boleto="<?=$rowx['boleto']?>" class="boleto_<?=$rowp['idcontapagar'] ?>" name="namecert" onclick="boletopdf(<?= $rowp['idcontapagar'] ?>,this,'<?= $rowx['boleto'] ?>')">
                                                </td>
                                            <? } ?>


                                            <? if ($boleto > 0) { ?>
                                                <td>
                                                    <a class="fa btn-lg pointer hoverazul pointer" title="Remessa" onclick="janelamodal('?_modulo=remessa&_acao=u&idremessa=<?= $row['idremessa'] ?>')"><?= $row['idremessa'] ?></a>
                                                </td>
                                            <? } 
                                            
                                            echo '<!-- Histórico -->';
                                            if (!empty($rowp['idcontapagar'])) {           
                                                ?>
                                                <td>
                                                    <?                                       
                                                    $qrfs = PedidoController::buscarHistoricoStatusPedidoPorIdcontapagar($pagvalmodulo,$rowp['idcontapagar'],$_1_u_nf_idnf);
                                                    foreach($qrfs as $rowfs){
                                                        ?>
                                                        <a class="fa fa-info-circle tip" title="Informações de Criação" data-toggle="popover" href="#<?= $rowp['idcontapagar'] ?>" style="margin-left:20px" data-trigger="hover"></a>
                                                        <div id="modalpopover_<?= $rowp['idcontapagar'] ?>" class="modal-popover hidden">
                                                            <table>
                                                                <tr>
                                                                    <td nowrap><b>Criado por:</b></td>
                                                                    <td><?= dmahms($rowfs['criadopor']) ?></td>

                                                                </tr>
                                                                <tr style="margin-top: 10px;">
                                                                    <td nowrap><b>Criado em:</b> </td>
                                                                    <td><?= dmahms($rowfs['criadoemcp']) ?></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    <?
                                                    }
                                                    ?>
                                                </td>
                                                <?
                                            } ?>                                            
                                            <td>
                                                <a class="fa fa-bars fa-1x btn-lg cinzaclaro hoverazul pointer" style="padding-left:95px" title="Conta Pagar" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?= $rowp["idcontapagar"] ?>');"></a>
                                            </td>
                                        </tr>
                                        <?
                                    } //while($rowx=mysqli_fetch_assoc($qrpx))
                                    if ((tratanumero($vtotal) - tratanumero($_1_u_nf_frete)) > tratanumero($totalcpg) or (tratanumero($vtotal) - tratanumero($_1_u_nf_frete)) < tratanumero($totalcpg)) {
                                        $figalert = "<i title='Valor " . tratanumero($totalcpg) . " diferente com o da nota " . tratanumero($vtotal) . ".' class='fa fa-exclamation-triangle laranja btn-lg pointer'></i>";
                                    } ?>
                                    <tr>
                                        <td colspan="<?=(5 + $somarFaturaColspan + $somarHistoricoColspan)?>" style="text-align: right;"><b>Total:</b></td>
                                        <td colspan="1"><b><?= number_format(tratanumero($totalcpg), 2, ',', '.'); ?> <?= $figalert ?></b></td>
                                        <td colspan="5"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="8">
                                            <? if ($_1_u_nf_status != "CONCLUIDO" or  $_1_u_nf_status != "CANCELADO") { ?>
                                                <input value="<?= $qtdrx + 1 ?>" id="parcela_parcelas" type='hidden' name="parcela_parcelas">
                                                <a class="fa fa-plus-circle verde pointer hoverazul" title="Nova Parcela" onclick="showModal()"></a>
                                            <? } ?>
                                        </td>
                                    </tr>
                                </table>

                            </div>
                        </div>
                    </div>

                <? }
            

                $qrp = PedidoController::buscarFaturaPorPedido($_1_u_nf_idnf);
                $qtdrowsp = count($qrp);
                //Alteração do Layout alinhando os campos (Lidiane - 06/05/2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315903)
                if ($qtdrowsp > 0) { ?>
                    <div class=" row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading segunda" data-toggle="collapse" href='#collapsefats'>Faturamento</div>
                                <div class="panel-body " id="collapsefats">
                                    <table class="table table-striped planilha">
                                        <? $totalcp = 0;
                                        foreach($qrp as $rowp ) {
                                            $i = $i + 1;
                                            $totalcp = $totalcp + $rowp["valor"];
                                                                                    
                                            $row = PedidoController::buscarRemessaPorIdcontapagar($rowp['idcontapagar']);
                                            $boleto = count($row);
                                            if ($rowp["boletopdf"] == 'Y') {
                                                $checked = 'checked';
                                                $vchecked = 'N';
                                            } else {
                                                $checked = '';
                                                $vchecked = 'Y';
                                            } ?>
                                            <tr>
                                                <th>Fatura:</th>
                                                <td class="nowrap"><a class=" pointer" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?= $rowp["idcontapagar"] ?>');"><? echo ($rowp["parcela"] . " de " . $rowp["parcelas"]); ?></a></td>
                                                <th>Obs:</th>
                                                <td style="max-width: 200px">
                                                    <input name="<?= $i ?>idcontapagarobs" id="idcontapagarobs" style="width: 350px; font-size: 11px" type="text" onchange="atualizaobscp(this,<?= $rowp["idcontapagar"] ?>)" value="<?= $rowp["obs"] ?>">
                                                </td>
                                                <th>Recebimento:</th>
                                                <td> 
                                                    <input type="hidden" class="datarecebparc" value="<?=$rowp["datareceb"]?>" statusCI="<?=$rowp["status"]?>" parcela="<?=$rowp['parcela']?>">
                                                    <?= $rowp["datareceb"] ?>
                                                </td>
                                                <th>Status:</th>
                                                <td><label class="alert-warning"><?= $rowp["status"] ?></label></td>
                                                <th>Valor:</th>
                                                <td style="max-width: 85px">
                                                    <?
                                                    
                                                    $resao =PedidoController:: buscarRestaurarPorIdlp(getModsUsr("LPS"));
                                                    $qtdao = count($resao);
                                                    if ($qtdao > 0 and $rowp["status"] != "QUITADO") {
                                                    ?>
                                                        <input name="contapagar_valor" class="size8" type="text" value="<?= $rowp["valor"] ?>" onchange="atualizavl(this,<?= $rowp["idcontapagar"] ?>)">
                                                    <?
                                                    } else {
                                                    ?>
                                                        <?= number_format(tratanumero($rowp["valor"]), 2, ',', '.'); ?>
                                                    <?
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?
                                                    $qrfs1 = PedidoController:: buscarHistoricoStatusPedidoPorIdcontapagar($pagvalmodulo,$rowp['idcontapagar'],$_1_u_nf_idnf);
                                                    foreach($qrfs1 as $rowfs1){
                                                    ?>
                                                        <a class="fa fa-info-circle tip" title="Informações de Criação" data-toggle="popover" href="#<?= $rowp['idcontapagar'] ?>" style="margin-left:20px" data-trigger="hover"></a>
                                                        <div id="modalpopover_<?= $rowp['idcontapagar'] ?>" class="modal-popover hidden">
                                                            <table>
                                                                <tr>
                                                                    <td nowrap><b>Criado por:</b></td>
                                                                    <td><?= dmahms($rowfs1['criadopor']) ?></td>

                                                                </tr>
                                                                <tr style="margin-top: 10px;">
                                                                    <td nowrap><b>Criado em:</b> </td>
                                                                    <td><?= dmahms($rowfs1['criadoemcp']) ?></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    <?
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <a title="Fatura" class="fa fa-bars fa-1x cinzaclaro hoverazul btn-lg pointer" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?= $rowp["idcontapagar"] ?>');"></a>
                                                </td>
                                                <td>
                                                    <? if ($boleto > 0) { ?>
                                                        <a class="fa fa-wpforms fa-1x btn-lg  cinzaclaro pointer hoverazul pointer" title="Boleto" onclick="janelamodal('inc/boletophp/<?= $rowp['boleto'] ?>.php?idcontapagar=<?= $rowp['idcontapagar'] ?>')"></a>
                                                    <? } ?>
                                                </td>
                                                <td>
                                                    <? if ($boleto > 0) { ?>
                                                        <input title="Boleto PDF" type="checkbox" <?= $checked ?> name="namecert" onclick="boletopdf(<?= $rowp['idcontapagar'] ?>,this,'<?= $rowp['boleto'] ?>')">
                                                    <? } ?>
                                                </td>
                                                <td>
                                                    <? if ($boleto > 0) { ?>
                                                        <a class="fa btn-lg pointer hoverazul pointer" title="Remessa" onclick="janelamodal('?_modulo=remessa&_acao=u&idremessa=<?= $row['idremessa'] ?>')"><?= $row['idremessa'] ?></a>
                                                    <? } ?>
                                                </td>

                                            </tr>
                                        <?
                                            
                                        } //WHILE
                                        if (tratanumero($vtotal) > tratanumero($totalcp) or tratanumero($vtotal) < tratanumero($totalcp)) {
                                            $figalert = "<i title='Valor " . tratanumero($totalcp) . " diferente com o da nota " . tratanumero($vtotal) . ".' class='fa fa-exclamation-triangle laranja btn-lg pointer'></i>";
                                        } ?>
                                        <tr>
                                            <td colspan="9"></td>
                                            <td><b><?= number_format(tratanumero($totalcp), 2, ',', '.'); ?> <?= $figalert ?></b></td>
                                            <td colspan="5"></td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?
                }

                if($_SESSION["SESSAO"]["OBRIGATORIOCONTATO"]=="Y"){
                    $inidpessoa=$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].",".$_SESSION["SESSAO"]["IDPESSOA"];
                }else{
                    $inidpessoa=null;
                }

                $rescom = PedidoController::buscarComissaoPorIdnf($_1_u_nf_idnf, $inidpessoa);
                $qrcom = count($rescom);
                if ($qrcom > 0) {
                   
                    $rowtotalcom = PedidoController::buscarTotalComissaoPorNf($_1_u_nf_idnf,$inidpessoa);
                    ?>
                    <div class=" row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">Comissões</div>
                                <div class="panel-body">
                                    <table class="table table-striped planilha">
                                        <th>Fatura:</th>
                                        <th>Parcela:</th>
                                        <th>Comssionado:</th>
                                        <th>Recebimento:</th>
                                        <th>Status:</th>
                                        <th>Valor:</th>
                                        <th></th>
                                        <? 
                                        $totalcom=0;
                                        foreach($rescom as $rowp2){
                                            $totalcom = $totalcom+$rowp2['valor'];    
                                            ?>
                                            <tr>    
                                                <!-- Fatura -->                                            
                                                <td><? echo ($rowp2["parcela"]); ?></td> 

                                                <!-- Parcela -->                                               
                                                <td><? echo ($rowp2["parcela"] . " de " . $rowp2["parcelas"]); ?></td>

                                                <!-- Comssionado -->
                                                <td><?= $rowp2["nome"] ?></td>    
                                                
                                                <!-- Vencimento -->
                                                <td>
                                                    <input type="hidden" class="datarecebparc" value="<?=dma($rowp2["datareceb"])?>" statusCI="<?=$rowp2["status"]?>" parcela="<?=$rowp2['parcela']?> - <?=$rowp2["nome"]?>">
                                                    <?= dma($rowp2["datareceb"]) ?>
                                                </td>                                                
                                                <td><?= $rowp2["status_item"] ?></td>                                                
                                                <td>
                                                    <?  
                                                    $resao =PedidoController:: buscarRestaurarPorIdlp(getModsUsr("LPS"));
                                                    $qtdao = count($resao);
                                                    if ($qtdao > 0 and $rowp2["status_item"] != "QUITADO") { ?>
                                                        <input name="contapagaritem_valor" class="size10" type="text" value="<?= $rowp2["valor"] ?>" onchange="atualizavlitem(this,<?= $rowp2["idcontapagaritem"] ?>)">
                                                    <? } else { ?>
                                                        <?= number_format(tratanumero($rowp2["valor"]), 2, ',', '.'); ?>
                                                    <? } ?>
                                                </td>
                                                <td>
                                                    <a class="fa fa-bars fa-1x cinzaclaro hoverazul btn-lg pointer" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?= $rowp2["idcontapagar"] ?>');"></a>
                                                </td>
                                            </tr>
                                        <? } //while ($rowp2 = mysqli_fetch_array($qrp2))

                                            $diferencacom= number_format($rowtotalcom['comissao'],2) - number_format($totalcom,2);

                                            if( (number_format($rowtotalcom['comissao'],2) != number_format($totalcom,2)) and abs($diferencacom) > 1){
                                                $alertacom="Total da comissão deve ser ".number_format(tratanumero($rowtotalcom['comissao']), 2, ',', '.')." verificar comissões ";
                                                $coralerta="red";
                                            }else{
                                                $alertacom="Total";
                                                $coralerta="";
                                            }
                                            
                                        ?>
                                        <tr style="color: <?=$coralerta?>;">
                                            <td colspan="5" title=<?=$rowtotalcom['comissao']?> style="text-align: right;"><b><?=$alertacom?></b></td>
                                            <td><b><?=number_format(tratanumero($totalcom), 2, ',', '.')?></b></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="8">
                                                <? 
                                                $rescomArray = PedidoController::buscarParcelasSemComissao($_1_u_nf_idnf);
                                                $contadorParcelasSemComissao = count($rescomArray);
                                                if (($_1_u_nf_status != "CONCLUIDO" or  $_1_u_nf_status != "CANCELADO") && $contadorParcelasSemComissao > 0 ) { ?>
                                                    <input value="<?= $qtdrx + 1 ?>" id="parcela_parcelas" type='hidden' name="parcela_parcelas">
                                                    <a class="fa fa-plus-circle verde pointer hoverazul" title="Nova Parcela" onclick="showModalComissao()"></a>
                                                <? } ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <? } elseif ($_1_u_nf_comissao == 'Y' and ($qtdrowsp == 0 or $qtdrx == 0) and $_1_u_nf_geracontapagar == "Y") { // if($qtdp2>0)
                ?>
                    <div class=" row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">Comissões</div>
                                <div class="panel-body">
                                    <table class="table table-striped planilha">
                                        <tr>
                                            <td colspan="10">
                                                <font color="red">Não gerou comissão!!!</font>
                                            </td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <? } //if($qtdp2>0)
                if ($qtdrowsp == 0 and $qtdrx == 0 and $_1_u_nf_geracontapagar == "Y" and $_1_u_nf_status == "CONCLUIDO") { ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading" data-toggle="collapse" href='#collapsefat'>Faturamento</div>
                                <div class="panel-body " id="collapsefat">
                                    <table>
                                        <tr>
                                            <td align="center">
                                                <font style="font-size: 25px;" color="red">NF não gerou parcela(s)!!!</font>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <? } //if($qtdrowsp>0){
                ?>
            </div>
        </div>
    </div>
<? } //rodape()
?>

<div id="novaparcela" style="display: none;">
    <div class="row">
        <div class="col-md-12">
            <table style="margin-left: 26%;margin-bottom: 10px;">
                <tr>
                    <td style="width: 100px !important; "><input type="radio" id="checkcredito" name="_modalnovaparcelacontapagar_tipo_" value="C" checked="yes" style="margin-right: 5px;"> Crédito </td>
                    <td style="width: 100px !important; "><input type="radio" id="checkdebito" name="_modalnovaparcelacontapagar_tipo_" value="D" style="margin-right: 5px;"> Débito </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td align="right">Fatura Automática:</td>
                    <td>
                        <select id="formapagnovaparc" name="formapagnovaparc">
                            <option></option>
                            <? fillselect(PedidoController::buscarFormapagamentoCreditoPornota(), $_1_u_nf_idformapagamento); ?>
                        </select>
                    </td>
                    <td align="right">Valor:</td>
                    <td><input type="text" id="valornovaparc" name="valornovaparc"></td>
                </tr>
                <tr>
                    <td align="right">Recebimento:</td>
                    <td><input type="date" id="vencnovaparc" name="vencnovaparc" placeholder="Ex: 00/00/0000"></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div id="novacomissao" style="display: none;">
    <div class="row">
        <div class="col-md-12">
            <table style="margin-bottom: 10px;">                
                <tr>
                    <td>Forma Pagamento:</td>
                    <td>
                        <?  
                        $disablednf = "";
                        $arrayContaPagar = [];
        
                        $rescomArray = PedidoController::buscarParcelasSemComissao($_1_u_nf_idnf);
                        foreach($rescomArray as $_comArray){ 
                            $arrayContaPagar[$_comArray['idcontapagar']] = $_comArray["parcelas"];
                            $datapagto = explode(" ", $_comArray['datapagto']);
                            ?>
                            <input type="hidden" class="contaPagar<?=$_comArray['idcontapagar']?>" contadatapagto="<?=$datapagto[0]?>" formapagamento="<?=$_comArray['idformapagamento']?>" valorparcelacomissao="<?=$_comArray['valor']?>">
                            <?
                        }
                        ?>
                        <select id="formapagnovaparc" name="formapagnovaparc" onchange="calculaComissao(this)">
                            <option></option>
                            <? fillselect($arrayContaPagar); ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td style="width: 25% !important;">Recebimento:</td>
                    <td style="width: 75% !important;">
                        <input type="date" id="vencnovacomissao" class="calendario vencnovacomissao size15" name="vencnovacomissao" autocomplete="off">
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <th align="right">Comissionado:</th>
                    <th>Valor:</th>
                </tr>
                <?
                $pessoas = PedidoController::buscarPessoasComissaoNf($_1_u_nf_idnf);
                foreach($pessoas as $_pessoas){
                    ?>
                    <tr>
                        <td align="right"><?=$_pessoas['nome']?></td>
                        <td><input type="text" class="gerarNovaComissao" id="valornovacomissao<?=$_pessoas['idpessoa']?>" name="valornovacomissao<?=$_pessoas['idpessoa']?>" comissao="<?=$_pessoas['pcomissaototal']?>" idpessoa="<?=$_pessoas['idpessoa']?>"></td>
                    </tr>
                <?
                }

                $formaPagamentoComissao = PedidoController::buscarFormaPagamentoPorIdEmpresaETipo(cb::idempresa(), 'COMISSAO');
                ?>
                <input type="hidden" class="idformapagamentoComissao" value="<?=$formaPagamentoComissao['idformapagamento']?>">
                <input type="hidden" class="idcontaPagarComissao" value="">
            </table>
            <br />
            <br />
            <br />
        </div>
    </div>
</div>
<?
require_once '../inc/php/readonly.php';
require_once('../form/js/pedido_js.php');
?>
