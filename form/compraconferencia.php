<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/nfentrada_controller.php");
require_once(__DIR__."/controllers/fluxo_controller.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

/**********************************************************************************************************************
*												|							|										  *
*												|	 Modal Conferência     	|										  *
*												V							V										  *
***********************************************************************************************************************/

$pagvaltabela = "nf";
$pagvalmodulo = $_GET['_moduloPai'];
$pagvalcampos = array(
    "idnf" => "pk"
);
$pagsql = "SELECT * FROM nf WHERE idnf = '#pkid'";

include_once("../inc/php/controlevariaveisgetpost.php");

?>
<link href="../form/css/nfentrada_css.css?_<?=date("dmYhms")?>" rel="stylesheet">
<?
$arrTipoNf = array('C', 'S', 'T', 'R', 'D', 'M', 'B', 'O');
if(in_array($_1_u_nf_tiponf, $arrTipoNf)) 
{
    if(!empty($_1_u_nf_idnfe)) 
    {
        $listarNfPorServDesc = NfEntradaController::buscarNfporServDesc($_1_u_nf_idnfe);
        $listNfPorServDesc = $listarNfPorServDesc['dados'];
        if($listarNfPorServDesc['qtdLinhas'] > 0) 
        {   
            ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading cabecalho" style="font-size:12px">
                            <div class="row">
                                <div class="col-sm-1 sigla-empresa"></div>
                                Registros de CTe
                            </div>
                        </div>
                        <?
                        foreach($listNfPorServDesc as $nfPorServDesc) 
                        {
                            $listarNfPessoa = NfEntradaController::buscarNfPessoaPorIdNf($nfPorServDesc['idnf']);
                            $qtdnfe = empty($listarNfPessoa) ? 0 : count($listarNfPessoa);
                            if($qtdnfe > 0) 
                            {
                                ?>                    
                                <div class="panel-body">
                                    <table class="table table-striped planilha">
                                        <tr>
                                            <th>CTe</th>
                                            <th>Transporte</th>
                                            <th>Emissão</th>
                                            <th>Status</th>
                                            <th>Valor</th>
                                            <th></th>
                                        </tr>
                                        <?
                                        $totalnfe = 0;
                                        foreach($listarNfPessoa as $nfPessoa) 
                                        {
                                            ?>
                                            <tr>
                                                <td><?=$nfPessoa['nnfe'] ?></td>
                                                <td><?=$nfPessoa['nome'] ?></td>
                                                <td><?=$nfPessoa['emissao'] ?></td>
                                                <td><?=$nfPessoa['status'] ?></td>
                                                <td><?=$nfPessoa['total'] ?></td>
                                                <td>
                                                    <a class="fa fa-bars pointer hoverazul" title="CTe" onclick="janelamodal('?_modulo=pedido&_acao=u&idnf=<?=$nfPessoa['idnf'] ?>')"></a>
                                                </td>
                                            </tr>
                                            <?
                                            $totalnfe += $nfPessoa['total'];
                                        }
                                        ?>
                                        <tr>
                                            <td colspan="4" align="right">Total</td>
                                            <td colspan="2"><?=number_format(tratanumero($totalnfe), 2, ',', '.') ?></td>
                                        </tr>
                                    </table>
                                </div>                
                                <?
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?
        } // if(!empty(['idnf'])){
    } //$_1_u_nf_idnfe
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading" style="font-size:12px">
                    <div class="row">
                        <div class="col-sm-1 sigla-empresa"></div>
                        <div class="col-sm-11">
                            <strong>Conferência</strong>
                            <?
                            if (!($_1_u_nf_status == 'CONCLUIDO' or $_1_u_nf_status == 'CANCELADO')) { ?>
                                <a title="Sinalizar que houve pendência nesta compra." class="fa fa-exclamation-triangle laranja btn-lg pointer" onclick="conferencia('pendente')"></a>
                            <? }  ?>
                        </div> 
                    </div>                    
                </div>
                <div class="panel-body">
                    <input name="_1_<?=$_acao?>_nf_idnf" id="idnf" type="hidden" value="<?=$_1_u_nf_idnf ?>" readonly='readonly'>
                    <input id="prazo" name="_1_<?= $_acao ?>_nf_prazo" type="hidden" value="<?= $_1_u_nf_prazo ?>" cbvalue="<?= $_1_u_nf_prazo ?>">
                    <input name="_1_<?= $_acao ?>_nf_dtemissao" class="calendario form-control" id="fdata" type="hidden" value="<?= $_1_u_nf_dtemissao ?>">
                    <input type="hidden" name="_1_<?=$_acao ?>_nf_tiponf" value="<?=$_1_u_nf_tiponf ?>">
                    <?
                    $listarNfConferenciaItem = NfEntradaController::buscarNfConferenciaItem($_1_u_nf_idnf);
                    $qtdq = $listarNfConferenciaItem['qtdLinhas'];

                    switch ($_1_u_nf_tiponf) 
                    {
                        case "C":
                            $tiponf = "danfe";
                            break;
                        case "S":
                            $tiponf = "servico";
                            break;
                        case "T":
                            $tiponf = "cte";
                            break;
                        case "E":
                            $tiponf = "concessionaria";
                            break;
                        case "M":
                            $tiponf = "manualcupom";
                            break;
                        case "B":
                            $tiponf = "recibo";
                            break;
                        case "F":
                            $tiponf = "fatura";
                            break;
                        case "D":
                            $tiponf = "socios";
                            break;
                        case "R":
                            $tiponf = "rh";
                            break;
                        case "O":
                            $tiponf = "manualcupom";
                            break;
                        default:
                            die("Erro ao identificar tipo de NF");
                            break;
                    }

                    $conferenciaItem = NfEntradaController::buscarConferenciaItem($tiponf);
                    $qtdcq = $conferenciaItem['qtdLinhas'];

                    if ($qtdq == 0 && $qtdcq > 0 && $tiponf != "socios") 
                    {
                        NfEntradaController::inserirNfConferenciaItem($_1_u_nf_idempresa, $_1_u_nf_idnf, $tiponf);
                        $listarNfConferenciaItem = NfEntradaController::buscarNfConferenciaItem($_1_u_nf_idnf);
                    }
                    ?>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>
                                <div class="col-md-8 espaco-previsao">
                                    Previsão Entrega:
                                </div>
                                <div class="col-md-2">
                                    <?
                                    $ListarHistoricoModal = NfEntradaController::buscarHistoricoAlteracao($_1_u_nf_idnf, 'previsaoentrega');
                                    $qtdvh = empty($ListarHistoricoModal) ? 0 : count($ListarHistoricoModal);
                                    if ($qtdvh > 0) 
                                    {
                                        ?>
                                        <div class="historicoPrevisaoEntrega" idnfitem="<?=$_itens["idnfitem"]?>">
                                            <i title="Histórico da Previsão Entrega" class="fa btn-sm fa-info-circle preto pointer hoverazul tip" data-target="webuiPopover0"></i>
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
                                                                    if ($historicoModal['justificativa'] == 'ATRASO') echo 'Atraso na Entrega';
                                                                    elseif ($historicoModal['justificativa'] == 'PEDIDO FORNECEDOR') echo 'A Pedido do Fornecedor';
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
                            </label>
                            <div class="input-group col-md-12">                                
                                <? if ($_1_u_nf_status == 'APROVADO' or $_1_u_nf_status == 'RECEBIDO' or $_1_u_nf_status == 'CONCLUIDO') {
                                    $vnulo = "vnulo";
                                } else {
                                    $vnulo = "";
                                }

                                if (empty($_1_u_nf_previsaoentrega)) {
                                ?>
                                    <div class="col-md-3">
                                        <input name="_1_<?=$_acao ?>_nf_previsaoentrega" onkeydown="return false" class="calendario" type="text" <?=$vnulo ?> value="<?=dma($_1_u_nf_previsaoentrega) ?>">
                                    </div>
                                <?
                                } else {
                                ?>
                                    <div class="col-md-3">
                                        <input name="_1_<?=$_acao ?>_nf_previsaoentrega" readonly="readonly" style="background-color: #f2f2f2;" type="text" <?=$vnulo ?> value="<?=dma($_1_u_nf_previsaoentrega) ?>">
                                     </div>
                                    <div class="col-md-2">
                                        <i class="fa fa-pencil btn-lg pointer" title='Editar Previsão Entrega' onclick="alteravalor('previsaoentrega','<?=dma($_1_u_nf_previsaoentrega) ?>','modulohistorico',<?=$_1_u_nf_idnf ?>,'Previsão de Entrega')"></i>
                                     </div>
                                <?
                                }
                                ?>                            
                            </div>
                        </div>                        
                    </div>
                    <div class="col-md-12">
                        <div class="form-group col-md-4">
                            <label>Data Recebimento:</label>
                            <div class="input-group col-md-12">
                                <input name="_1_<?=$_acao ?>_nf_dataconf" class="calendario" id="fdata4" type="text" value="<?=$_1_u_nf_dataconf ?>" onchange="atualizaconf(this,'dataconf');">
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Responsável Recebimento:</label>
                            <div class="input-group col-md-12">
                                <select name="_1_<?=$_acao ?>_nf_respinspecao" onchange="atualizaconf(this,'respinspecao');">
                                    <option value=""></option>
                                    <? fillselect(NfEntradaController::listarFuncionarioPessoaPorIdtipoPessoa(1, 'funcionarioCb', 'ATIVO'), $_1_u_nf_respinspecao); ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Transportador:</label>
                            <div class="input-group col-md-12">
                                <select name="_1_<?=$_acao ?>_nf_idtransportadora" onchange="atualizaconf(this,'idtransportadora');">
                                    <option value=""></option>
                                    <? fillselect(NfEntradaController::listarFuncionarioPessoaPorIdtipoPessoa(11, 'pessoasPorSession', 'ATIVO'), $_1_u_nf_idtransportadora); ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <?
                    if ($tiponf != "socios" and $tiponf != "rh") 
                    {
                        foreach($listarNfConferenciaItem['dados'] as $conferencia) 
                        {
                            ?>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="col-md-1">
                                        <select name="nfconferenciaitem_resultado" onchange="atualizanfconferenciaitem(this,<?=$conferencia['idnfconferenciaitem'] ?>);">
                                            <option value=""></option>
                                            <? fillselect(NfEntradaController::$_simNaoNenhum, $conferencia['resultado']); ?>
                                        </select>
                                    </div>
                                    <div class="col-md-10 top-align-sete">
                                        <?=$conferencia['qst'] ?>
                                    </div>
                                </div>
                            </div>
                        <?
                        }
                    }
                    ?>
                    <br />
                    <table>
                        <?
                        $listarPendencia = NfEntradaController::buscarNfPendencia($_1_u_nf_idnf);
                        $qtdq = $listarPendencia['qtdLinhas'];
                        if ($qtdq > 0) 
                        {
                            $l = 0;
                            foreach($listarPendencia['dados'] as $pendencia) 
                            {
                                $l = $l + 1;
                                if ($pendencia['status'] == 'PENDENTE') {
                                    $corresolv = "red";
                                    $corobspend = "#FF6A6A";
                                    $strreadonly = "";
                                } else {
                                    $corresolv = "red";
                                    $corobspend = "#32CD32";
                                    $strreadonly = "readonly='readonly'";
                                }
                                if (!empty($pendencia['descr'])) {
                                    $strreadonlyd = "readonly='readonly'";
                                } else {
                                    $strreadonlyd = "";
                                }
                                ?>
                                <tr>
                                    <td align="right">
                                        <font style="color:<?=$corresolv ?> "><?=$l?>-Pendência:</font>
                                    </td>
                                    <td></td>
                                    <td style="background-color: <?=$corobspend ?>">
                                        <textarea <?=$strreadonly ?> <?=$strreadonlyd ?> style="width: 550px; height: 30px;" style=font-size:medium; name="nfpendencia_descr" onchange="atualizapendencia(<?=$pendencia['idnfpendencia'] ?>,this,'descr')"><?=$pendencia['descr'] ?></textarea>
                                    </td>
                                    <td style="background-color: <?=$corobspend ?>">
                                        <? if ($pendencia['status'] == 'RESOLVIDO') { ?>

                                            <a title="Esta pendàªncia ja está resolvida" class="fas fa fa-thumbs-up verde btn-lg pointer"></a>
                                            <label class="alert-success"> RESOLVIDO</label>
                                        <? } else { ?>
                                            <a title="Clicar neste icone irá alterar pedência para resolvida" class="fas fa fa-thumbs-down vermelho btn-lg pointer" onclick="conferenciaok(<?=$pendencia['idnfpendencia'] ?>)"></a>
                                            <label class="alert-warning"> PENDENTE</label>
                                        <? } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <font style="color:<?=$corresolv ?> "><?=$l ?>-Tratativa:</font>
                                    </td>
                                    <td>
                                        <div class="oEmailorc">
                                            <a class="fa fa-search azul pointer hoverazul" title=" Ver Log Email" data-target="webuiPopover0"></a>
                                        </div>
                                        <div class="webui-popover-content">
                                            <li class="nowrap"><?=$pendencia["criadopor"] ?> <?=dmahms($pendencia["criadoem"]) ?></li>
                                            <li class="nowrap"><?=$pendencia["alteradopor"] ?> <?=dmahms($pendencia["alteradoem"]) ?></li>
                                        </div>
                                    </td>
                                    <td style="background-color: <?=$corobspend ?>">
                                        <textarea <?=$strreadonly ?> style="width: 550px; height: 30px;" style=font-size:medium; name="nfpendencia_tratativa" onchange="atualizapendencia(<?=$pendencia['idnfpendencia'] ?>,this,'tratativa')"><?=$pendencia['tratativa'] ?></textarea>

                                    </td>
                                    <td style="background-color: <?=$corobspend ?>"></td>
                                </tr>
                                <tr>
                                    <td style="height:20px;"></td>
                                </tr>
                                <?
                            } 
                        } //if($qtdq>0){

                        ?>
                    </table>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Observação:</label>
                            <div class="input-group col-md-12">
                                <textarea rows="2" style=font-size:medium; name="_1_<?=$_acao ?>_nf_obs" onchange="atualizaconf(this,'obs');"><?=$_1_u_nf_obs ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Observação Interna:</label>
                            <div class="input-group col-md-12">
                                <textarea rows="2" style=font-size:medium; name="_1_<?=$_acao ?>_nf_obsinterna"><?=$_1_u_nf_obsinterna ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?
}

require_once('../form/js/compraconferencia_js.php');
?>