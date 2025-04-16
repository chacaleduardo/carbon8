<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/nfentrada_controller.php");
require_once(__DIR__."/controllers/fluxo_controller.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

/**********************************************************************************************************************
*												|							|										  *
*												|	 Modal Recebimento     	|										  *
*												V							V										  *
***********************************************************************************************************************/

$pagvaltabela = "nf";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idnf" => "pk"
);
$pagsql = "SELECT * FROM nf WHERE idnf = '#pkid'";

include_once("../inc/php/controlevariaveisgetpost.php");

?>
<link href="../form/css/nfentrada_css.css?_<?=date("dmYhms")?>" rel="stylesheet">

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading cabecalho" style="font-size:12px">
                <div class="row">
                    <div class="col-sm-1 sigla-empresa"></div>
                   Recebimento
                </div>
            </div>
            <div class="panel-body">
                <div class="col-md-12">
                    <div class="form-group col-md-3">
                        <label>Envio:</label>
                        <div class="input-group col-md-12">
                            <input name="_1_<?=$_acao ?>_nf_envio" class="calendario" value="<?=$_1_u_nf_envio ?>" vnulo>
                            <input name="_1_<?=$_acao?>_nf_idnf" id="idnf" type="hidden" value="<?=$_1_u_nf_idnf ?>" readonly='readonly'>
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Entrega:</label>
                        <div class="input-group col-md-12">
                            <div class="col-md-5" style="margin-left: -10px;">
                                <input name="_1_<?=$_acao ?>_nf_entrega" class="calendario" value="<?=$_1_u_nf_entrega ?>" vnulo>
                                <?
                                //colocar previsão de entrega 
                                if (empty($_1_u_nf_obsenvio)) {
                                    if (!empty($_1_u_nf_idtransportadora)) 
                                    {
                                        $previsaoEntrega = NfEntradaController::buscarTransportadorPorIdpessoa($_1_u_nf_idtransportadora, true);
                                    }
                                    if (!empty($previsaoEntrega['observacaonfp'])) {
                                        $_1_u_nf_obsenvio = $previsaoEntrega['observacaonfp'];
                                    } else {
                                        $_1_u_nf_obsenvio = "De 2 à  3 dias úteis";
                                    }
                                }
                                ?>
                            </div>
                            <div class="col-md-7">
                                <input style="color: red;" name="_1_<?=$_acao ?>_nf_obsenvio" size="20" type="text" value="<?=$_1_u_nf_obsenvio ?>">
                            </div> 
                        </div>                        
                    </div>

                    <div class="form-group col-md-3">
                        <label>Frete:</label>
                        <div class="input-group col-md-12">
                            <select <?=$disablednf ?> name="_1_<?=$_acao ?>_nf_modfrete">
                                <? fillselect(NfEntradaController::$_modFrete, $_1_u_nf_modfrete); ?>
                            </select>
                            <input name="_1_<?=$_acao ?>_nf_icms" id="vlricms" size="8" type="hidden" value="<?=$vtotalicms ?>" vdecimal>
                            <input name="_1_<?=$_acao ?>_nf_ipi" id="vlripi" size="8" type="hidden" value="<?=$vtotalipi ?>" vdecimal>
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Inf. Frete:</label>
                        <div class="input-group col-md-12">
                            <input name="_1_<?=$_acao ?>_nf_inffrete" size="20" type="text" value="<?=$_1_u_nf_inffrete ?>">
                        </div>                        
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group col-md-12">
                        <label>Tipo de Frete:</label>
                        <div class="input-group col-md-12">
                            <select name="_1_<?=$_acao ?>_nf_tipofrete" <?=$disablednf ?>>
                                <option value=""></option>
                                <? fillselect(NfEntradaController::$_tipofrete, $_1_u_nf_tipofrete); ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group col-md-3">
                        <label>Transportadora:</label>
                        <div class="input-group col-md-12">
                            <?
                            if (!empty($_1_u_nf_idpessoa)) 
                            {
                                $transportadora = NfEntradaController::buscarTransportadorPorIdpessoa($_1_u_nf_idpessoa, true);
                                if (empty($_1_u_nf_idtransportadora)) {
                                    $_1_u_nf_idtransportadora = $transportadora['idtransportadora'];
                                } else {
                                    $idtransportadorapr = $_1_u_nf_idtransportadora;
                                }

                                if (!empty($idtransportadorapr)) {
                                    $fverificactransp = "verificatransp(this," . $idtransportadorapr . ",'" . $transportadora['nome'] . "');";
                                }
                            }
                            ?>
                            <input name="idtransportadora" value="<?=$_1_u_nf_idtransportadora ?>" type="hidden">
                            <select <?=$disablednf ?> name="_1_<?=$_acao ?>_nf_idtransportadora" onchange="<?=$fverificactransp ?>">
                                <option value=""></option>
                                <? fillselect(NfEntradaController::listarFuncionarioPessoaPorIdtipoPessoa(11, 'pessoasPorSession', 'ATIVO'), $_1_u_nf_idtransportadora); ?>
                            </select>
                        </div>
                    </div>                    
                    <div class="form-group col-md-3">
                        <label>Resp. Envio:</label>
                        <div class="input-group col-md-12">
                            <input name="respenvio" value="<?=$_1_u_nf_respenvio ?>" type="hidden">
                            <select name="_1_<?=$_acao ?>_nf_respenvio" id="respenvi" vnulo style="display: <?=$strdados ?>">
                                <option value=""></option>
                                <? fillselect(NfEntradaController::listarFuncionarioPessoaPorIdtipoPessoa(1, 'pessoasPorSession', 'ATIVO'), $_1_u_nf_respenvio); ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group col-md-3">
                        <label>Peso (KG):</label>
                        <div class="input-group col-md-12">
                            <input name="_1_<?=$_acao ?>_nf_peso" size="20" type="text" value="<?=$_1_u_nf_peso ?>" vdecimal>
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Custo (R$):</label>
                        <div class="input-group col-md-12">
                            <input name="_1_<?=$_acao ?>_nf_custoenvio" size="20" type="text" value="<?=$_1_u_nf_custoenvio ?>" vdecimal>
                        </div>
                    </div>
                </div>

                <div class="col-md-12" style="margin-left: 10px;">
                    <div class="form-group">
                        <label style="color: red !important;">Obs. Interna:</label>
                        <div class="input-group col-md-12">
                            <textarea class="caixa" style="width: 99%; height: 40px; " name="_1_<?=$_acao ?>_nf_obsinterna" onchange="atualizaobsint(this)"><?=$_1_u_nf_obsinterna ?></textarea>
                        </div>   
                    </div>                 
                </div>
            </div>
            <?
            $listarIdNfe = NfEntradaController::buscarIdNfeNfItemPorObsNotNULLEIdNf($_1_u_nf_idnf);
            $idNfe = $listarIdNfe['dados'];
            $qtdidnfe = $listarIdNfe['qtdLinhas'];
            if ($listarIdNfe['idnfe'] != "" && $qtdidnfe > 0) 
            {
                $listarNfPorIdNfe = NfEntradaController::buscarNfPessoaPorIdNfe($listarIdNfe['idnfe']);
                $qtdnfe = $listarNfPorIdNfe['qtdLinhas'];
                if ($qtdnfe > 0) 
                {
                    ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">Registros de NFe</div>
                        <div class="panel-body">
                            <table class="table table-striped planilha">
                                <tr>
                                    <th>NFe</th>
                                    <th>Cliente</th>
                                    <th>Emissão</th>
                                    <th>Status</th>
                                    <th>Valor</th>
                                    <th></th>
                                </tr>
                                <?
                                $totalcte = 0;
                                foreach($listarNfPorIdNfe as $nfe) 
                                {
                                    if ($rowcte['status'] != 'CANCELADO') {
                                        $totalnfe = $totalnfe + $nfe['total'];
                                    }
                                    if ($nfe['tiponf'] == 'V') {
                                        $modv = 'pedido';
                                    } else {
                                        $modv = 'nfentrada';
                                    }
                                    ?>
                                    <tr>
                                        <td><?=$nfe['nnfe'] ?></td>
                                        <td><?=$nfe['nome'] ?></td>
                                        <td><?=$nfe['emissao'] ?></td>
                                        <td><?=$nfe['status'] ?></td>
                                        <td><?=$nfe['total'] ?></td>
                                        <td>
                                            <a class="fa fa-bars pointer hoverazul" title="CTe" onclick="janelamodal('?_modulo=<?=$modv ?>&_acao=u&idnf=<?=$nfe['idnf'] ?>')"></a>
                                        </td>
                                    </tr>
                                    <?
                                }
                                ?>
                                <tr>
                                    <td colspan="4" align="right">Total</td>
                                    <td colspan="2"><?=number_format(tratanumero($totalnfe), 2, ',', '.') ?> <? if ($_1_u_nf_total > 10) { ?> (<font title="Custo Cte %." color="red"><?=round((($_1_u_nf_total * 100) / $totalnfe), 2) ?>%</font>)<? } ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                <?
                }
            }
        ?>
    </div>
</div>

<?
require_once('../form/js/comprarecebimento_js.php');
?>