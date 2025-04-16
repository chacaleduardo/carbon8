<?
require_once("../inc/php/validaacesso.php");
require_once("controllers/pedido_controller.php");
require_once("controllers/rateioitemdest_controller.php");
require_once("../form/controllers/nfentrada_controller.php");

require_once("../model/prodserv.php");
require_once("../inc/php/permissao.php");
require_once(__DIR__."/controllers/fluxo_controller.php");

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


$arrayFormaPagamento = NfEntradaController::listarFormaPagamentoAtivoPorLP();

if (empty($_1_u_nf_intervalo)) {
    $_1_u_nf_intervalo = 28;
}

?>
<link href="../form/css/nfentrada_css.css?_<?=date("dmYhms")?>" rel="stylesheet">

    <div class="panel panel-default" style="font-size:12px">
        <div class="panel-heading">
        <div class="sigla-empresa"></div>

            <div class="row d-flex flex-between flex-wrap">
              
                <div class="form-group col-xs-6 col-md-2">
                    <label class="text-white">ID</label>
                    <div class="alert-warning form-control d-flex align-items-center">
                            <label for=""><?= $_1_u_nf_idnf ?></label>
                    </div>
               
            
                    <input name="_1_<?= $_acao ?>_nf_idnf" id="idnf" type="hidden" value="<?= $_1_u_nf_idnf ?>" readonly='readonly'>   
                </div>
                <div class="form-group col-xs-6 col-md-2">
                    <label class="text-white">Nome</label>
                    <div class="alert-warning form-control d-flex align-items-center">
                        <? echo traduzid("pessoa", "idpessoa", "nome", $_1_u_nf_idpessoa); ?>
                    </div>
               
            
                    <input name="_1_<?= $_acao ?>_nf_idnf" id="idnf" type="hidden" value="<?= $_1_u_nf_idnf ?>" readonly='readonly'>   
                </div>

                <div class="form-group col-xs-6 col-md-2">
                    <label class="text-white">Número NF</label>
                    <div class="alert-warning form-control d-flex align-items-center">
                        <input name="_1_<?= $_acao ?>_nf_nnfe" id="nnfe" type="text" value="<?= $_1_u_nf_nnfe ?>" > 
                    </div>
                     
                </div> 
                
                <div class="form-group col-xs-6 col-md-2">
                    <label class="text-white">Tipo NF  </label>
                    <div class="d-flex align-items-center">
                    <?    
                        $fillTipoNf = NfEntradaController::$_cobranca;                   
                    ?>
                    <input type="hidden" name="_1_<?= $_acao ?>_nf_tiponf" value="<?= $_1_u_nf_tiponf ?>">
                    <select name="_1_<?= $_acao ?>_nf_tiponf" id="tiponf" vnulo onchange="CB.post()" class="form-control">
                            <? fillselect($fillTipoNf, $_1_u_nf_tiponf); ?>
                    </select>
                    </div>
                </div> 
                
                <div class="form-group col-xs-6 col-md-2">
                    <label class="text-white"></label>  
                    <div class="d-flex align-items-center">
                        <?
                        $nfItens=RateioItemDestController::verificaritemIdnf($_1_u_nf_idnf);
                        $qtditens=sizeof($nfItens);
                        
                        if(($_1_u_nf_status=='ABERTO' or $_1_u_nf_status=='INICIO')  and $qtditens<1){
                        ?>
                        <button id="cobrar" type="button" class="btn btn-success btn-xs" style="background-color:#4878df !important;" title="Gerar Itens da Cobrança" onclick="gerarCobranca(<?=$_1_u_nf_idnf?>);" idrateioitem="">
                            <i class="fa fa-handshake-o fa-1x"></i>Gerar Itens
                        </button>
                        <?}?>
                </div>
            
            </div> 
            </div>          

        </div>
        <div class="panel-body">   
    <div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading cabecalho" style="font-size:12px">
                <div class="row">
                    <div class="col-sm-1 sigla-empresa"></div>
                    <div class="alinhar-texto-cabecalho">
                        <strong>Pagamento</strong>
                    </div>
                </div>
            </div>
            <?
            if (!empty($_1_u_nf_vnf)) {
                $vsubtotal = $_1_u_nf_vnf;
            } elseif (!empty($valorx)) {
                $valorx = $valorx - $desconto;
                $vsubtotal = $valorx;
            }

            if (!empty($fretex)) {
                $_1_u_nf_frete = $fretex;
            }

            if ($_1_u_nf_tiponf == "S" or $_1_u_nf_tiponf == 'R' or $_1_u_nf_tiponf == 'D') {
                $imp_serv = (tratanumero($_1_u_nf_pis) + tratanumero($_1_u_nf_cofins) + tratanumero($_1_u_nf_csll) + tratanumero($_1_u_nf_inss) + tratanumero($_1_u_nf_ir) + tratanumero($_1_u_nf_issret));
                $imp_serv = ($imp_serv < 0) ? 0 : $imp_serv;
                $vtotal = $vtotal - $imp_serv;
            }
            ?>
            <input name="_1_<?=$_acao ?>_nf_subtotal" id="vlrsubtotal" size="8" type="hidden" value="<?=number_format(tratanumero($vsubtotal), 2, ',', '.') ?>" <?=$readonly ?> <?=$vreadonly ?> vdecimal>
            <input <?=$readonly ?> <?=$vreadonly ?> name="_1_<?=$_acao ?>_nf_total" id="vlrtotal" class="size6" type="hidden" value="<?=number_format(tratanumero($vtotal), 2, ',', '.') ?>" vdecimal>
            <input name="_1_<?=$_acao?>_nf_idnf" id="idnf" type="hidden" value="<?=$_1_u_nf_idnf ?>" readonly='readonly'>
            <div class="panel-body">
                <? if ($_1_u_nf_tiponf == "E") { ?>
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="form-group">
                                <div class="col-sm-1">
                                    <?
                                    if ($_1_u_nf_sped == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    }
                                    ?>
                                    <input title="sped" type="checkbox" <?=$checked ?> name="namesped" onclick="altcheck('nf','sped',<?=$_1_u_nf_idnf ?>,'<?=$vchecked ?>')">
                                </div>
                                <div class="col-sm-10 top-align-sete">
                                    Sped?
                                </div>
                            </div>
                        </div>
                    </div>
                <? } ?>

                <div class="col-md-12">
                    <div class="form-group col-md-2">
                        <label>Emissão:</label>
                        <div class="input-group col-md-2">
                        <input  name="_1_<?= $_acao ?>_nf_dtemissao" class="calendario size8" <? if ($_1_u_nf_status == 'CONCLUIDO') { ?> disabled="disabled" <? } ?> id="fdata" type="text" value="<?= $_1_u_nf_dtemissao ?>">
                        </div>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Gera Parcela:</label>
                        <div class="input-group col-md-12">
                            <select class="size6" name="_1_<?=$_acao ?>_nf_geracontapagar" vnulo <? if ($_1_u_nf_status == 'CONCLUIDO' or $_1_u_nf_status == 'CANCELADO') { ?>readonly='readonly' <? } ?>>
                                <? fillselect(NfEntradaController::$_simNao, $_1_u_nf_geracontapagar); ?>
                            </select>
                        </div>
                    </div>
                    <? if ($_1_u_nf_geracontapagar == "Y") { ?>
                        <div class="form-group col-md-2">
                            <label>Tipo:</label>
                            <div class="input-group col-md-12">
                                <select class="size8"  disabled="disabled" name="_1_<?=$_acao ?>_nf_tipocontapagar" id="tipo">
                                    <? fillselect(NfEntradaController::$_debitoCredito, $_1_u_nf_tipocontapagar); ?>
                                </select>
                            </div>
                        </div>
                    <? } 
                    
                    if ($_1_u_nf_geracontapagar == "Y") 
                    { 
                        ?>
                         <div class="form-group col-md-2">
                            <label>Parcelas:</label>
                            <div class="input-group col-md-12">
                                <?
                                for ($selparcelas = 1; $selparcelas <= 120; $selparcelas++) {
                                    if ($selparcelas == 1) {
                                        $select = "select " . $selparcelas . ",'" . $selparcelas . "x' ";
                                    } else {
                                        $select .= "union select " . $selparcelas . ",'" . $selparcelas . "x' ";
                                    }
                                }
                                ?>
                                <select class="size6" name="_1_<?=$_acao ?>_nf_parcelas" id$_1_u_nf_dtemissao="parcelas" onchange="atualizaparc(this)">
                                    <? fillselect($select, $_1_u_nf_parcelas);?>
                                </select>
                            </div>
                        </div>
                        <? if (!empty($_1_u_nf_idformapagamento)) 
                        {
                            $rowdias = NfEntradaController::buscarConfiguracoesFormaPagamento($_1_u_nf_idformapagamento);
                            if (empty($_1_u_nf_diasentrada) and $_1_u_nf_diasentrada != 0) 
                            {
                                $_1_u_nf_diasentrada = $rowdias['campo'];
                                if (empty($_1_u_nf_diasentrada)) {
                                    $_1_u_nf_diasentrada = 0;
                                }
                            }
                            ?>
                            <div class="form-group col-md-2">
                                <label>1º Vencimento:</label>
                                <div class="input-group col-md-12">
                                    <input class="size3" name="_1_<?=$_acao ?>_nf_diasentrada" type="text" value="<?=$_1_u_nf_diasentrada ?>" onchange="atualizadiasentrada(this)">&nbsp
                                    <select class="size6" name="_1_<?=$_acao ?>_nf_tipointervalo">
                                        <? fillselect(NfEntradaController::$_periodo, $_1_u_nf_tipointervalo); ?>
                                    </select>
                                </div>
                            </div>
                            <? if ($_1_u_nf_parcelas > 1) {
                                $strdivtab = "style='display:block;'";
                            } else {
                                $strdivtab = "style='display:none;'";
                            } ?>
                            <div class="form-group col-md-2">
                                <label>Intervalo:</label>
                                <div class="input-group col-md-12">
                                    <input class="size3" name="_1_<?=$_acao ?>_nf_intervalo" type="text" value="<?=$_1_u_nf_intervalo ?>" onchange="atualizaintervalo(this)">
                                </div>
                            </div>
                        <? 
                        } 
                    }
                    ?>
                </div>

                <?
                if ($_1_u_nf_geracontapagar == "Y" and !empty($_1_u_nf_dtemissao)) 
                {
                    ?>
                    <div class="col-md-12">
                        <div class="form-group col-md-12">
                            <div class="col-md-2">
                                <label>Editar Proporção:</label>
                            </div>
                            <div class="col-md-10">
                                <div class="input-group col-md-12">
                                    <? if ($_1_u_nf_proporcional == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    } ?>
                                    <input title="Editar proporções" type="checkbox" <?=$checked ?> name="nameproporcional" onclick="altcheck('nf','proporcional',<?=$_1_u_nf_idnf ?>,'<?=$vchecked ?>')">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <? 
                        // Calcula a data daqui 3 dias
                        if (empty($_1_u_nf_diasentrada)) 
                        {
                            $_1_u_nf_diasentrada = 0;
                        }

                        $q = 10;
                        if (!empty($_1_u_nf_idnf)) 
                        {
                            $rescx = NfEntradaController::buscarIdNfConfPagar($_1_u_nf_idnf);
                            $qtdpx = count($rescx);
                            $v = 0;
                            $tproporcao = 0;
                            $count = 1;
                            foreach($rescx as $rowcx) 
                            {
                                $q++;
                                $i++;
                                if ($_1_u_nf_tipointervalo == "M") {
                                    $strintervalo = 'MONTH';
                                } elseif ($_1_u_nf_tipointervalo == "Y") {
                                    $strintervalo = 'YEAR';
                                } else {
                                    $strintervalo = 'days';
                                }
                                $q++;
                                if ($v == 0) {
                                    $dias = $_1_u_nf_diasentrada;
                                } else {

                                    $dias = $_1_u_nf_diasentrada + ($_1_u_nf_intervalo * $v);
                                }
                                $pvdate = $_1_u_nf_dtemissao;
                                $pvdate = str_replace('/', '-', $pvdate);
                                $timestamp = strtotime(date('Y-m-d', strtotime($pvdate)) . "+" . $dias . " " . $strintervalo . "");

                                //verificar se a data e sabado ou domingo
                                /*
                                $sqldia = "SELECT DAYOFWEEK('" . date('Y-m-d', $timestamp) . "') as diasemana;";
                                $resdia = d::b()->query($sqldia) or die("Erro ao buscar dia da semana");
                                $rowdia = mysqli_fetch_assoc($resdia);

                                if ($rowdia['diasemana'] == 1) { //Se for domingo aumenta 1 dia
                                    $timestamp = strtotime(date('Y-m-d', $timestamp) . "+1 days");
                                } elseif ($rowdia['diasemana'] == 7) { //Se for sabado aumenta 2 dias
                                    $timestamp = strtotime(date('Y-m-d', $timestamp) . "+2 days");
                                }
                                */

                                $eFeriado = 1;

                                WHILE ($eFeriado >= 1) {

                                    /*
                                    $sqldia = " SELECT verificaFeriadoFds('" . date('Y-m-d', $timestamp) . "' ) as eFeriado;";
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
                                }
                                $tproporcao = $tproporcao + $rowcx['proporcao'];
                                // Exibe o resultado
                                ?>
                                <div class="col-md-4">
                                    <div class="col-md-12">
                                        <div class="col-md-5">
                                            <font color="red"><? echo (($v + 1) . "º");?>:</font>
                                            <input style="width: 100px;" name="_<?=$q?>_u_nfconfpagar_idnfconfpagar" type="hidden" value="<?=$rowcx['idnfconfpagar'] ?>">
                                            <input class="size7 calendario dataconfdate" id="dataconf<?=$rowcx['idnfconfpagar'] ?>" idnfconfpagar="<?=$rowcx['idnfconfpagar'] ?>" name="_<?=$q?>_u_nfconfpagar_datareceb" type="text" value="<?=$rowcx['dmadatareceb'] ?>">
                                            <?
                                            if ($rowcx['dmadatareceb'] != date('d/m/Y', $timestamp)) 
                                            {
                                                ?>
                                                &nbsp;<?=date('d/m/Y ', $timestamp) ?>&nbsp;<i class="fa fa-exclamation-triangle laranja" title="Valor sugerido pelo Sistema"></i>
                                                <?
                                            }
                                            ?>
                                        </div>
                                        <? if ($_1_u_nf_proporcional == 'Y') { ?>
                                            <div class="col-md-2 alinhar-texto-cabecalho">
                                                Proporção:
                                            </div>                                        
                                            <div class="col-md-2">
                                                <input class="size4" name="_<?=$q?>_u_nfconfpagar_proporcao" type="text" value="<?=round($rowcx['proporcao'], 2) ?>" onchange="atualizaproporcao(this,<?=$rowcx['idnfconfpagar'] ?>)">
                                            </div>
                                        <? } ?>
                                        <div class="col-md-1">
                                            <? if (empty($rowcx['obs'])) { ?>
                                                <i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde bnt-lg-sete pointer" onclick="nfconfpagar(<?=$rowcx['idnfconfpagar'] ?>,<?=$q ?>)" title="Inserir observação."></i>
                                            <? } else { ?>
                                                <div class="observacao">
                                                    <i data-target="webuiPopover0" class="fa fa-info-circle fa-1x azul pointer hoverpreto bnt-lg-sete tip" onclick="nfconfpagar(<?=$rowcx['idnfconfpagar'] ?>,<?=$q ?>)"></i>
                                                </div>
                                                <div class="webui-popover-content">
                                                    <b>Obs:</b> <?=$rowcx['obs'] ?> <br />
                                                    <b>Alterado em:</b> <?=dmahms($rowcx['alteradoem']) ?><br />
                                                    <b>Alterado por:</b> <?=$rowcx['alteradopor'] ?><br />
                                                </div>                                                
                                            <? } ?>
                                        </div>
                                        <div class="col-md-2 alinhar-texto-cabecalho">
                                            <font color="red"><?=round($tproporcao, 2) ?></font>
                                        </div>
                                        
                                        <div id="<?=$q?>_editarnfconfpagar" class="hide">
                                            <table>
                                                <tr>
                                                    <td>
                                                        <textarea name="<?=$q ?>_nfconfpagar_obs" id="<?=$q?>_nfconfpagar_obs" style="width: 570px; height: 41px; margin: 0px;"><?=$rowcx['obs'] ?></textarea>
                                                        <input id="<?=$q?>_nfconfpagar_idnfconfpagar" name="<?=$q?>_nfconfpagar_idnfconfpagar" type="hidden" value="<?=$rowcx['idnfconfpagar'] ?>">
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
                                                                            <div class="col-md-4" style="text-align:left"><?=$rowcx['alteradopor'] ?></div>
                                                                            <div class="col-md-2" style="text-align:right">Alterado Em:</div>
                                                                            <div class="col-md-4" style="text-align:left"><?=dmahms($rowcx['alteradoem']); ?></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?
                                $v++;
                                if($count % 3 == 0)
                                {
                                    echo '</div><div class="col-md-12">';
                                }
                                $count++;
                            } //for ($v = 0; $v < $_1_u_nf_parcelas; $v++) {                            
                        }
                        ?>
                    </div>
                <?       
                } //if($_1_u_nf_geracontapagar=="Y"){
                ?>
            </div>
        </div>
    </div>
</div>



<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading cabecalho" style="font-size:12px">
                <div class="row">
                    <div class="col-sm-1 sigla-empresa"></div>
                    <div class="alinhar-texto-cabecalho">
                        <strong>Iten(s) da Cobrança</strong>
                    </div>
                </div>
            </div>
            <div class="panel-body">


            <?
            $listarItens=RateioItemDestController::listarRateioitemdestnfPorIdnf($_1_u_nf_idnf);
            ?>
            <div class="col-md-12">
            <table id="tbItens" class="table table-striped planilha">
                <tr class="cabitem">                       
                    <th class="col-md-5">Descrição</th>  
                    <th class="col-md-3">Unidade</th>     
                    <th class="col-md-3">Cobrança %</th>                                
                    <th class=" col-md-2 alinhar-direita nowrap">Valor R$</th>                                       
                </tr>
                <?
                $total=0;
                foreach ($listarItens as $_itens) 
                {
                    $total= $total+$_itens["valor"];    
                ?>
                <tr>                       
                    <td class="col-md-7">
                        <?
                            if(empty($_itens['descr'])){
                                echo $_itens['prodservdescr'];
                            }else{
                                echo $_itens['descr'];
                            }
                        ?>
                    </td> 
                    <td class=" col-md-2 alinhar-direita"><?=$_itens["unidade"]?></td>
                    <td class=" col-md-1 alinhar-direita"><?=number_format($_itens["rateio"], 2, '.', '');?></td> 
                    <td class=" col-md-2 alinhar-direita"><?=number_format($_itens["valor"], 2, '.', '');?></td>                   
                </tr>
                <?}?>
                <tr>
                    <td  class="col-md-7"></td>
                    <td  class="col-md-2"></td>
                    <td  class="col-md-1"></td>
                    <td class=" col-md-2 alinhar-direita">
                    <?=number_format( $total, 2, '.', '');?>
                    </td>
                </tr>
            </table>
            </div>
        </div>
    </div>
    </div>
</div>
<?
require_once('../form/js/comprapagamento_js.php');
require_once '../inc/php/readonly.php';
require_once('../form/js/rateioitemdestnf_js.php');
?>