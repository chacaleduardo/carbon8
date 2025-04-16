<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/cotacao_controller.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}

if ($_GET['tipo'] == 'cotacao') {

    $infoNf = CotacaoController::buscarNfPorTipoObjetoSoliPor($_GET['idcotacao'], $_GET['idempresa'], true);
    // Carrega os fillselects apenas uma vez para otimizar desempenho
    $fillSelectUnidadeVolume = CotacaoController::listarUnidadeVolume();
    $fillSelectContaItem = CotacaoController::listarContaItemAtivoShare();
    $fillSelectTipoProdserv = CotacaoController::listarProdservTipoProdServPorEmpresa($_1_u_cotacao_idempresa);
    $fillSelectTransportadora = CotacaoController::listarFornecedorPessoaPorIdTipoPessoa(11);
    $dominio = CotacaoController::buscarDominio($_1_u_cotacao_idempresa);

    $controleCabecarioReprovadosCancelados = true; // Controle do header para colapsar CANCELADO e REPROVADO
    $i = 1;
    $l = 0;
    $m = 0;

    foreach ($infoNf['nf'] as $_nf) {
        $i++;
        $l++;

        // Determina atributos com base no status
        $vnulo = in_array($_nf['status'], ['APROVADO', 'PREVISAO', 'RESPONDIDO', 'AUTORIZADO', 'AUTORIZADA']) ? 'vnulo' : '';
        $nfreadonly = in_array($_nf['status'], ['INICIO', 'RESPONDIDO', 'AUTORIZADO', 'ENVIADO', 'AUTORIZADA']) ? '' : "readonly='readonly'";
        $nfdisabled = in_array($_nf['status'], ['INICIO', 'RESPONDIDO', 'AUTORIZADO', 'ENVIADO', 'AUTORIZADA']) ? '' : "disabled='disabled'";
        $statusent = ($_nf['pedidoentrega'] === 'atrasado') ? 'ABERTO' : $_nf['status'];

        // Concatena descrição dos itens
        $descrItensNf = '';
        $taxaConversao = false;
        foreach ($infoNf['nfitens'][$_nf['idnf']] as $_itens) {
            $descrProduto = empty($_itens['codforn']) ? "{$_itens['descr']} - {$_itens['codprodserv']}" : $_itens['codforn'];
            $descrItensNf .= " {$descrProduto}";
            if (in_array($_itens['moedaext'], ['USD', 'EUR']) && !empty($_itens['vlritemext'])) {
                $taxaConversao = true;
            }
        }
?>

        <div class="row cotacao<?= $_nf['status'] ?>" style="display: block;">
            <div class="col-md-12 cotacaoabas cotacaoItensNota">
                <div class="panel panel-default" <?= $stylePanelDefault ?> id="nftable<?= $_nf['idnf'] ?>" data-text="<?= $descrItensNf ?>">
                    <div class="panel-heading <?= $statusent ?>" <?= $stylePanelHeading ?> id="divcor<?= $_nf['idnf'] ?>" data-text="<?= $descrItensNf ?>">
                        <!-- Linha de conteúdo -->
                        <div style="display: flex; flex-wrap: wrap; align-items: flex-start; gap: 1px;" data-text="<?= $descrItensNf ?>">

                            <!-- Primeira coluna (Chevron) -->
                            <div style="flex: 0 0 1%; min-width: 20px;">
                                <span style="display: block; height: 20px;"></span>
                                <i class="fa fa-chevron-down azul pointer"
                                    style="padding-right: 10px;"
                                    onclick="mostraitenscot(this,'cotacao<?= $_nf['idnf'] ?>')"></i>
                            </div>

                            <!-- Segunda coluna (ID/Duplicar) -->
                            <div style="flex: 0 0 1%; min-width: 45px;">
                                <span style="display: block; height: 20px;"></span>
                                <input type="hidden"
                                    id="cotacao<?= $_nf['idnf'] ?>iddiv"
                                    class="cotacao<?= $_nf['idnf'] ?>iddiv"
                                    value="<?= $statusent ?>">
                                <label class="idbox" style="padding: 5px; display: inline-block;"><?= $l ?></label>-
                                <a class="fa fa-clone pointer azul hoverpreto"
                                    style="font-size: medium;"
                                    title="Duplicar Cotação"
                                    onclick="duplicarcompra(<?= $_nf['idnf'] ?>)"></a>
                            </div>

                            <!-- Migrar Cotação -->
                            <div style="flex: 0 0 1%; min-width: 25px;">
                                <? if (count($infoNf['nfitens']['migrarCotacao'][$_nf['idnf']]) > 0) { ?>
                                    <span style="display: block; height: 20px;"></span>
                                    <a id="altorcamento"
                                        class="fa fa-unlink fa-2x pointer modalMigrarCotacaoClique azul hoverpreto"
                                        idnf="<?= $_nf['idnf'] ?>"
                                        title="Migrar Cotação"></a>
                                    <div class="panel panel-default" hidden>
                                        <div class="modalmigrarcotacao<?= $_nf['idnf'] ?>">
                                            Selecionar Cotação:
                                            <select id="val_idobjetosolipor<?= $_nf['idnf'] ?>" class="size25" onchange="alterarCotacao(<?= $_nf['idnf'] ?>, this)">
                                                <option value=""></option>
                                                <? fillselect($infoNf['nfitens']['migrarCotacao'][$_nf['idnf']]); ?>
                                            </select>
                                        </div>
                                    </div>
                                <? } else { ?>
                                    <span style="display: block; height: 20px;"></span>
                                    <i class="fa fa-unlink fa-2x cinzaclaro">
                                    </i>
                                <? } ?>
                            </div>

                            <!-- Cotação -->
                            <div style="flex: 0 0 4%; min-width: 60px;">
                                <span style="display: block; height: 20px;">Cotação:</span>
                                <label class="idbox" style="padding: 5px; display: inline-block;">
                                    <a title="Cotação Fornecedor"
                                        href="?_modulo=cotacaoforn&_acao=u&idnf=<?= $_nf['idnf'] ?>"
                                        target="_blank"><?= $_nf['idnf'] ?>
                                    </a>
                                </label>
                                <? if ($_nf['idnforigem']) { ?>
                                    <i class="fa fa-info-circle azul pointer hoverpreto tip">
                                        <span class="infoNfDuplicada">
                                            <p>Cotação duplicada REF: <b><?= $_nf['idnforigem'] ?></b></p>
                                            <p>Criado em: <b><?= dmahms($_nf['criadoem']) ?></b></p>
                                            <p>Criado por: <b><?= $_nf['criadopor'] ?></b></p>
                                        </span>
                                    </i>
                                <? } ?>
                            </div>

                            <!-- Fornecedor -->
                            <div style="flex: 0 0 25%; min-width: 300px;">
                                <span style="display: block; height: 20px;">Fornecedor:</span>
                                <label class="idbox" style="padding: 5px; width: 100%; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <a title="Fornecedor"
                                        href="?_modulo=pessoa&_acao=u&idpessoa=<?= $_nf['idpessoa'] ?>"
                                        target="_blank"><?= $_nf['nome'] ?>
                                    </a>
                                </label>
                            </div>
                            <div style="flex: 0 0 2%; min-width: 5px; text-align: center;">
                                <?
                                $resultado = $infoNf['resultadoavaliacaofornecedor'][$_nf['idpessoa']]['resultado']; ?>
                                <span style="display: block; height: 20px;"></span>
                                <? if (COUNT($resultado) > 0) {
                                    if ($row["resultado"] == 'REPROVADO') { ?>
                                        <a class="pull-left"
                                            title="Fornecedor <?= $resultado ?>">
                                            <i class="fa fa-exclamation-triangle fa-1x vermelho btn-lg pointer"></i>
                                        </a>
                                    <? }
                                } else { ?>
                                    <a class="pull-left"
                                        title="Avaliação do fornecedor PENDENTE">
                                        <i class="fa fa-exclamation-triangle fa-1x laranja btn-lg pointer"></i>
                                    </a>
                                <? } ?>
                            </div>

                            <!-- Tipo NF -->
                            <div style="flex: 0 0 8%; min-width: 100px;">
                                <span style="display: block; height: 20px;">Tipo NF:</span>
                                <?
                                if (empty($rownf['tiponf'])) {
                                    $_tiponf = 'C';
                                } else {
                                    $_tiponf = $rownf['tiponf'];
                                }
                                ?>
                                <select id="tiponf"
                                    name="_<?= $i ?>_<?= $_acao ?>_nf_tiponf"
                                    vnulo
                                    style="width: 100%; max-width: 100px;"
                                    <?= $_nf['status'] == "CONCLUIDO" ? 'disabled="disabled"' : '' ?>>
                                    <? fillselect(CotacaoController::$tipoNf, $_tiponf); ?>
                                </select>
                            </div>

                            <!-- Finalidade -->
                            <div style="flex: 0 0 15%; min-width: 80px;">
                                <span style="display: block; height: 20px;">Finalidade:</span>
                                <?
                                if (count($infoNf['fillSelectFinalidadeProdserv'][$_nf['idpessoa']]) == 0) {
                                    $option = "<option value=''>ALERTA: Configurar Finalidade no Fornecedor</option>";
                                } elseif (count($infoNf['fillSelectFinalidadeProdserv'][$_nf['idpessoa']]) > 1) {
                                    $option = "<option value=''></option>";
                                } else {
                                    $option = "";
                                }

                                if (count($infoNf['fillSelectFinalidadeProdserv'][$_nf['idpessoa']]) == 1) {
                                    $idfinalidadeprodserv = array_keys($infoNf['fillSelectFinalidadeProdserv'][$_nf['idpessoa']]);
                                    $idfinalidadeprodserv = $idfinalidadeprodserv[0];
                                } elseif (!empty($_nf['idfinalidadeprodserv'])) {
                                    $idfinalidadeprodserv = $_nf['idfinalidadeprodserv'];
                                } else {
                                    $idfinalidadeprodserv = "";
                                }
                                ?>
                                <select id="idfinalidadeprodserv"
                                    name="_<?= $i ?>_<?= $_acao ?>_nf_idfinalidadeprodserv"
                                    class="fillCheck"
                                    <? if ($_nf['status'] != "CANCELADO") { ?> vnulo <? } ?>
                                    <? if ($_nf['status'] == "CONCLUIDO" or $_nf['status'] == "INICIO RECEBIMENTO" or $_nf['status'] == "DIVERGENCIA" or $_nf['status'] == "CORRIGIDO" or $_nf['status'] == "CONFERIDO" or $_nf['status'] == "APROVADO") { ?>disabled='disabled' <? } ?>
                                    style="width: 95%;">
                                    <?= $option ?>
                                    <? fillselect($infoNf['fillSelectFinalidadeProdserv'][$_nf['idpessoa']], $idfinalidadeprodserv); ?>
                                </select>
                                <input type="hidden" name="cnpj_forn" value="<?= $_nf['cpfcnpj'] ?>">
                            </div>

                            <!-- Emissão NF -->
                            <div style="flex: 0 0 5%; min-width: 100px;">
                                <span style="display: block; height: 20px;">Emissão NF:</span>
                                <input name="_<?= $i ?>_<?= $_acao ?>_nf_dtemissao"
                                    class="calendario size10 dtemissao<?= $_nf['idnf'] ?> alterarDtEmissao"
                                    idnf="<?= $_nf['idnf'] ?>"
                                    id="fdata<?= $i ?>"
                                    type="text"
                                    value="<?= dma($_nf["dtemissao"]) ?>"
                                    <?= $nfdesabled ?>
                                    style="width: 100%; max-width: 100px;">
                            </div>


                            <!-- NF -->
                            <div style="flex: 0 0 2%; min-width: 20px; text-align: center;">
                                <span style="display: block; height: 20px;"></span>
                                <? if ($_nf['status'] != "INICIO" && $_nf['status'] != "RESPONDIDO" && $_nf['status'] != "AUTORIZADO" && $_nf['status'] != 'AUTORIZADA' && $_nf['status'] != "ENVIADO" && $_nf['status'] != "REPROVADO") {
                                    $modulo = 'nfentrada'; ?>
                                    <a class="fa fa-file fa-2x pointer cinza hoverazul"
                                        title="NF"
                                        onclick="janelamodal('?_modulo=<?= $modulo ?>&_acao=u&idnf=<?= $_nf['idnf'] ?>')"></a>
                                <? } ?>
                            </div>

                            <?
                            //usado para enviar o e-mail
                            if ($_nf['status'] == "DIVERGENCIA" || $_nf['status'] == "APROVADO" || $_nf['status'] == "PREVISAO" || $_nf['status'] == "REPROVADO" || $_nf['status'] == "CANCELADO") {
                                $tipoemail = 'emailaprovacao';
                                $tipoenvio = 'COTACAOAPROVADA';
                                $rotemail = 'Aprovação';
                                if ($_nf["emailaprovacao"] == 'Y') {
                                    $classtdemail = "amarelo";
                                    $varalt = 'N';
                                } elseif ($_nf["emailaprovacao"] == 'O' || $_nf["emailaprovacao"] == 'R') {
                                    $classtdemail = "verde";
                                    $varalt = 'N';
                                } elseif ($_nf["emailaprovacao"] == 'E') {
                                    $classtdemail = "vermelho";
                                    $varalt = 'Y';
                                } else {
                                    $classtdemail = "cinza";
                                    $varalt = 'Y';
                                }
                            } else {
                                $tipoemail = 'envioemailorc';
                                $tipoenvio = 'COTACAO';
                                $rotemail = 'Cotação';
                                if ($_nf["envioemailorc"] == 'Y') {
                                    $classtdemail = "amarelo";
                                    $varalt = 'N';
                                } elseif ($_nf["envioemailorc"] == 'O' or $_nf["envioemailorc"] == 'R') {
                                    $classtdemail = "verde";
                                    $varalt = 'N';
                                } elseif ($_nf["envioemailorc"] == 'E') {
                                    $classtdemail = "vermelho";
                                    $varalt = 'Y';
                                } else {
                                    $classtdemail = "cinza";
                                    $varalt = 'Y';
                                }
                            }

                            if ($qtdempresaemail[$tipoenvio] == 1) {
                                $nemails = 1;
                            } else {
                                if ($qtdempresaemail[$tipoenvio] > 1) {
                                    $nemails = 2;
                                } else {
                                    $nemails = 0;
                                }
                            }

                            if (count($infoNf['empresaemailobjeto'][$_nf['idnf']][$tipoenvio]) < 1) {
                                $setemail = 1;
                            } else {
                                $setemail = 0;
                            }

                            if ($nemails == 1) { ?>
                                <input id="emailunico" type="hidden" value="<?= $dominio["idemailvirtualconf"] ?>">
                                <input id="idempresaemail" type="hidden" value="<?= $dominio["idempresa"] ?>">
                            <? }

                            $formatadata = explode('/', $_1_u_cotacao_prazo);
                            $date = $formatadata[2] . '-' . $formatadata[1] . '-' . $formatadata[0] . ' 23:59:59';
                            $timeStampPrazo = strtotime($date);
                            $timeStampNow = strtotime(date('Y-m-d'));

                            if ($timeStampPrazo < $timeStampNow) {
                                $fdel = "alert('O prazo para envio do e-mail venceu no dia " . $_1_u_cotacao_prazo . ".')";
                            } else {
                                $fdel = "altflagemail(" . $_nf["idnf"] . ",'nf','" . $tipoemail . "','" . $varalt . "'," . $nemails . ");";
                            }
                            ?>

                            <!-- Email -->
                            <div style="flex: 0 0 2%; min-width: 20px; text-align: center;">
                                <span style="display: block; height: 20px;"></span>
                                <input id="setemail" type="hidden" value="<?= $setemail ?>">
                                <i class="fa fa-envelope fa-2x pointer <?= $classtdemail ?> hoverazul"
                                    title="Enviar email <?= $rotemail ?>"
                                    onclick="<?= $fdel ?>"></i>
                            </div>

                            <!-- Emails enviados -->
                            <div style="flex: 0 0 2%; min-width: 20px; text-align: center;">
                                <span style="display: block; height: 20px;"></span>
                                <? $idmailfila = $infoNf['mailfila'][$_nf['idnf']]['idmailfila'];
                                if (count($idmailfila) > 0) { ?>
                                    <a title="Ver emails enviados"
                                        onclick="janelamodal('?_modulo=envioemail&_acao=u&idmailfila=<?= $idmailfila ?>')">
                                        <i class="fa fa-envelope-o fa-2x cinza pointer"></i><i style="z-index: 2300;margin-left:-5px;margin-top:-7px;" class="fa fa-search cinza"></i>
                                    </a>
                                <? } else { ?>
                                    <i title="Sem emails enviado" class="fa fa-envelope-o fa-2x cinzaclaro"></i><i style="z-index: 2300;margin-left:-5px;margin-top:-7px;" class="fa fa-search cinzaclaro"></i>

                                <? } ?>

                            </div>
                            <!-- Propostas anexas -->
                            <div style="flex: 0 0 2%; min-width: 20px; text-align: center;">
                                <span style="display: block; height: 20px;"></span>
                                <i
                                    <? $nomeAnexoProposta = $infoNf['anexocotacao'][$_nf['idnf']];
                                    if (count($nomeAnexoProposta) > 0) {
                                        $arrprop .= $arrvirg . $_nf["idnf"];
                                        $arrvirg = ","; ?>
                                    id="propostaanexa_<?= $_nf['idnf'] ?>"
                                    title="Propostas Anexas"
                                    class="fa fa-paperclip fa-2x pointer cinza hoverazul"></i>
                                <div class="webui-popover-content" id="content_<?= $_nf['idnf'] ?>">
                                    <table>
                                        <?
                                        foreach ($nomeAnexoProposta  as $_nomeAnexoProposta) {
                                        ?>
                                            <tr>
                                                <td>
                                                    <a class="pointer" onclick="janelamodal('upload/<?= $_nomeAnexoProposta['caminho'] ?>');"><?= $_nomeAnexoProposta['nome'] ?></a>
                                                </td>
                                            </tr>
                                        <? } ?>
                                    </table>
                                </div>
                            <? } else { ?>
                                <i class="fa fa-paperclip fa-2x cinzaclaro"
                                    title="Sem propostas Anexas"></i>
                            <? } ?>
                            </div>
                            <!-- Status -->
                            <div style="flex: 0 0 10%; min-width: 100px;">
                                <span style="display: block; height: 20px;">Status:</span>
                                <input name="_<?= $i ?>_<?= $_acao ?>_nf_idnf"
                                    type="hidden"
                                    value="<?= $_nf['idnf'] ?>">
                                <? if ($_nf['status'] == "INICIO" || $_nf['status'] == "RESPONDIDO" || $_nf['status'] == "AUTORIZADO" || $_nf['status'] == 'AUTORIZADA' || $_nf['status'] == "ENVIADO") { ?>
                                    <input type="hidden"
                                        id="nfstatus<?= $_nf['idnf'] ?>"
                                        value="<?= $_nf['status'] ?>">
                                    <select class="size10 nfstatus<?= $_nf['idnf'] ?>"
                                        id="nfstatus"
                                        onchange="validarCamposPreenchidos(this, <?= $_nf['idnf'] ?>, '<?= $_nf['status'] ?>', '<?= $_itens['idtipoprodserv'] ?>', '<?= $_itens['qtd'] * $_itens['vlritem'] ?>');"
                                        style="width: 100%; max-width: 120px;">
                                        <? fillselect(CotacaoController::$statusCotacao, $_nf['status']); ?>
                                    </select>
                                <? } else { ?>
                                    <label class="idbox" style="padding: 5px; display: inline-block; width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <input type="hidden" id="nfstatus" value="<?= $_nf['status'] ?>">
                                        <?= $_nf['status'] ?>
                                    </label>
                                <? } ?>
                            </div>
                            <!-- Botão Salvar -->
                            <div style="flex: 0 0 10%; min-width: 100px; text-align: center;">
                                <span style="display: block; height: 20px;"></span>
                                <button type="button"
                                    class="btn btn-success btn-xs"
                                    onclick="salvarNf(<?= $_nf['idnf'] ?>,<?= $i ?>)"
                                    title="Salvar Este"
                                    style="font-size: medium;">
                                    <i class="fa fa-circle"></i> Salvar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body cotacaocancelado" id="cotacao<?= $_nf['idnf'] ?>" style="display: none;">
                        <div class=" row">
                            <div class="col-md-7">
                                <table>
                                    <tr>
                                        <td align="left">Nº Orçamento:</td>
                                        <td align="left">Vendedor(a):</td>
                                        <td align="left">Telefone:</td>
                                        <? if (!empty($_nf['observacaore'])) { ?>
                                            <td align="left">Observação Fronecedor:</td>
                                        <? } ?>
                                    </tr>
                                    <tr>
                                        <?
                                        if ($_nf['status'] == 'ENVIADO') {
                                            $infoStatus = "disabled title='Não é possivel editar enquanto o status for ENVIADO'";
                                        } else {
                                            $infoStatus = "";
                                        } ?>
                                        <!-- orçamento -->
                                        <td>
                                            <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> class="size25" name="_<?= $i ?>_<?= $_acao ?>_nf_pedidoext" type="text" value="<?= $_nf["pedidoext"] ?>" <?= $nfdesabled ?>>
                                        </td>

                                        <!-- vendedor -->
                                        <td>
                                            <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> class="size25" name="_<?= $i ?>_<?= $_acao ?>_nf_aoscuidados" type="text" value="<?= $_nf["aoscuidados"] ?>" <?= $nfdesabled ?>>
                                        </td>

                                        <!-- telefone -->
                                        <td>
                                            <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> class="size15" name="_<?= $i ?>_<?= $_acao ?>_nf_telefone" type="text" value="<?= $_nf["telefone"] ?>" <?= $nfdesabled ?>>
                                        </td>

                                        <!--obervaçao fornecedor -->
                                        <td>
                                            <? if (!empty($_nf['observacaore'])) { ?>
                                                <textarea class="alert-warning size40" disabled> <?= $_nf['observacaore'] ?> </textarea>
                                            <? } ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <?
                        if ($_nf['marcartodosnfitem'] == 'Y') {
                            $checked = 'checked';
                        } else {
                            $checked = '';
                        }
                        ?>
                        <table class="table table-striped planilha">
                            <thead>
                                <tr>
                                    <th>NF</th>
                                    <th><input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> name="marcartodos" title="Marcar/Desmarcar Todos" type="checkbox" id="<?= $_nf['idnf'] ?>" <?= $checked ?> onclick="checkall(<?= $_nf['idnf'] ?>,this)" <?= $nfdesabled ?>></th>
                                    <th>Qtd Sol</th>
                                    <th style="width: 65px;">Sug Com</th>
                                    <th>Un</th>
                                    <th>Descrição</th>
                                    <th class="nowrap">Categoria</th>
                                    <? if ($taxaConversao == TRUE) { ?>
                                        <th class="nowrap" title="Taxa de Conversão">Tx Cv</th>
                                    <? } ?>
                                    <th style="text-align: -webkit-center;">Valor Un</th>
                                    <th>Desc Un</th>
                                    <th>ICMS ST</th>
                                    <th>IPI %</th>
                                    <th>Total</th>
                                    <th>Validade</th>
                                    <th>Prev Entrega</th>
                                    <th>Obs</th>
                                    <th colspan="4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?
                                $total = 0;
                                $desconto = 0;
                                $totalsemdesc = 0;
                                $moeda = "";
                                $iobs = $i;
                                $first = '';
                                $infe = 0;
                                $qtdItens = 1;
                                $qtdnfitem = COUNT($infoNf['nfitens'][$_nf['idnf']]);
                                $itemfor = 0;
                                foreach ($infoNf['nfitens'][$_nf['idnf']] as $_itens) {
                                    $i = $i + 1;
                                    $itemfor++;
                                    if (!empty($first) and !empty($_itens['idprodserv']) and empty($second)) {
                                        $second = 1; ?>
                                        <tr>
                                            <td colspan="20">
                                                <hr>
                                            </td>
                                        </tr>
                                    <?
                                    }

                                    if (empty($_itens['idprodserv'])) {
                                        $first = 1;
                                    }

                                    if (in_array('Y', $infoNf['nfitens']['itenssolcom'][$_itens['idprodserv']]['urgencia'])) {
                                        $corSolcom  = "style='background-color: mistyrose'";
                                    } else {
                                        $corSolcom = "";
                                    }
                                    ?>
                                    <tr <?= $corSolcom ?>>
                                        <?
                                        //Esconde os itens que não estão checados                                
                                        if (in_array($_nf['status'], array('APROVADO', 'PREVISAO', 'DIVERGENCIA', 'CONCLUIDO', 'CONFERIDO')) && $_itens['nfe'] == 'N') {
                                            if ($infe == 0) {
                                        ?>
                                    <tr>
                                        <td colspan="20" style="height:40px;" data-toggle="collapse" href="#itemnf<?= $_itens['idnf'] ?>" aria-expanded="false" class="collapsed">
                                            Itens Não Selecionados
                                            <i class="fa fa-arrows-v cinzaclaro pointer cotacao_todos_item" title="Produto"></i>
                                        </td>
                                    </tr>
                                    <tr class="collapse" id="itemnf<?= $_itens['idnf'] ?>">
                                        <td colspan="20">
                                            <table style="width: 100%;">
                                        <?
                                            }
                                            $infe++;
                                        }
                                        ?>
                                        <td><?= $itemfor ?></td>
                                        <td>
                                            <?
                                            if ($_itens["nfe"] == 'Y') {
                                                $checked = 'checked';
                                                $vchecked = 'N';
                                                if ($_itens['moeda'] == "BRL") {
                                                    $totalsemdesc += $_itens['total'] + $_itens['valipi'] + ($_itens['des'] * $_itens['qtd']);
                                                    $total = $total + $_itens['total'] + $_itens['valipi'];
                                                    $desconto += $_itens['des'] * $_itens['qtd'];
                                                    $moeda = $_itens['moeda'];
                                                } else {
                                                    $total = $total + $_itens['totalext'];
                                                    $moeda = $_itens['moeda'];
                                                }
                                            } else {
                                                $checked = '';
                                                $vchecked = 'Y';
                                                if ($_itens['moeda'] == "BRL") {

                                                    $moeda = $_itens['moeda'];
                                                } else {

                                                    $moeda = $_itens['moeda'];
                                                }
                                            }
                                            ?>
                                            <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> title="Nfe" type="checkbox" <?= $nfdesabled ?> <?= $checked ?> name="namenfec" class="<?= $_nf['idnf'] ?>" id="<?= $_itens["idnfitem"] ?>" onclick="alterarCamposNf(<?= $_itens['idnfitem'] ?>, 'nfitem', 'nfe', '<?= $vchecked ?>',this, <?= $_nf['idnf'] ?>)">
                                        </td>
                                        <!-- Qtd Sol -->
                                        <td>
                                            <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> name="_<?= $i ?>_<?= $_acao ?>_nfitem_idnfitem" type="hidden" value="<?= $_itens['idnfitem'] ?>">
                                            <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> name="_<?= $i ?>_<?= $_acao ?>_nfitem_tiponf" type="hidden" value="C">
                                            <input style="text-align: right;" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> class="size5" name="_<?= $i ?>_<?= $_acao ?>_nfitem_qtdsol" onchange="atualizarQtd(this, <?= $_itens['idnfitem'] ?>)" id="qtdsol<?= $_itens['idnfitem'] ?>" type="text" value="<?= $_itens['qtdsol'] ?>">
                                        </td>

                                        <!-- Sug Com -->
                                        <td align="right">
                                            <?
                                            if ($_itens['converteest'] == "Y") {
                                                $sugestaocompra2 = $_itens['sugestaocompra2'] / $_itens['valconv'];
                                            } else {
                                                $sugestaocompra2 = $_itens['sugestaocompra2'];
                                            }
                                            ?>
                                            <label class="idbox" style="padding: 5px;"><?= number_format(tratanumero($sugestaocompra2), 2, ',', '.') ?></label>
                                        </td>

                                        <!-- Un -->
                                        <td>
                                            <?
                                            if (empty($_itens["unidade"])) {
                                                if (empty($_itens['idprodserv']) && $_nf['status'] == 'INICIO') {
                                            ?>
                                                    <select setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> name="_<?= $i ?>_<?= $_acao ?>_nfitem_un">
                                                        <option value=""></option>
                                                        <? fillselect($fillSelectUnidadeVolume, $_itens["unidade"]); ?>
                                                    </select>
                                            <?
                                                } else {
                                                    $unidade = $_itens["unidade"];
                                                }
                                            } else {
                                                $unidade = $_itens["unidade"];
                                            }
                                            ?>
                                            <a class="cinza hoverazul pointer modalProdServ" idprodserv="<?= $_itens['idprodserv'] ?>" modulo="prodservfornecedor">
                                                <?= $unidade ?>
                                            </a>
                                        </td>

                                        <!-- Descrição -->
                                        <td>
                                            <? if (!empty($_itens['idprodserv'])) {
                                                if (empty($_itens["codforn"])) {
                                                    $descrProduto = $_itens["descr"];
                                                } else {
                                                    $descrProduto = $_itens["codforn"];
                                                }
                                            ?>
                                                <a class="hoverazul pointer modalProdServ" title="ID: <?= $_itens['idprodserv'] ?>-<?= $_itens['tipoprodserv'] ?>" idprodserv="<?= $_itens['idprodserv'] ?>" modulo="calculosestoque">
                                                    <?= $descrProduto ?>
                                                </a>
                                            <? } else {
                                                $descrProduto = $_itens["prodservdescr"];
                                            ?>
                                                <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> class="size20" name="_<?= $i ?>_<?= $_acao ?>_nfitem_prodservdescr" type="text" value="<?= $_itens['prodservdescr'] ?>" onchange="salvarDescr(this,<?= $_itens['idnfitem'] ?>)">
                                                <?
                                            }
                                            //Mostra o Vínculo com Solcom
                                            if (!empty($infoNf['nfitens']['itenssolcom'][$_itens['idprodserv']])) {
                                                foreach ($infoNf['nfitens']['itenssolcom'][$_itens['idprodserv']] as $chave => $_idsolcom) {
                                                    if ($chave != 'urgencia') {
                                                ?>
                                                        <label class="idbox" style="margin-right: 5px; padding: 5px;">
                                                            <a title="Solicitação Compras" class="fade pointer hoverazul solcomvalida" idprodserv="<?= $_itens['idprodserv'] ?>" idnf="<?= $_itens['idnf'] ?>" idnfitem="<?= $_itens['idnfitem'] ?>" descr="<?= $descrProduto ?>" idsolcom="<?= $_idsolcom ?>" nfe="<?= $_itens["nfe"] ?>" href="?_modulo=solcom&_acao=u&idsolcom=<?= $_idsolcom ?>" target="_blank">
                                                                <?= $_idsolcom ?>
                                                            </a>
                                                        </label>
                                            <?
                                                    }
                                                }
                                            }
                                            ?>
                                        </td>

                                        <!-- Categoria -->
                                        <td align="center">
                                            <?
                                            if (!empty($_itens['idcontaitem'])) {
                                                $idcontaitem = $_itens['idcontaitem'];
                                            } elseif (!empty($_itens['idprodserv'])) {

                                                if (count($infoNf['nfitens']['fillSelectContaItemProdserv'][$_itens['idprodserv']]) == 1) {
                                                    foreach ($infoNf['nfitens']['fillSelectContaItemProdserv'][$_itens['idprodserv']] as $key => $_idcontaitem) {
                                                        $idcontaitem = $key;
                                                    }
                                                }
                                            } else {
                                                $idcontaitem = "";
                                            }
                                            ?>
                                            <div id="tb_<?= $_itens["idnfitem"] ?>" class="grupo_es_oculto" style="display: none;">
                                                <table style="width: 100%">
                                                    <tr style="padding: 15px;">
                                                        <th>Categoria</th>
                                                        <th></th>
                                                        <th>Tipo</th>
                                                    </tr>
                                                    <tr>
                                                        <td style="width: 45%;" class="cp_grupoes">
                                                            <? if (empty($_itens['idprodserv'])) {
                                                            ?>
                                                                <input type="hidden" nomodal id="iidcontaitem<?= $_itens["idnfitem"] ?>" name="_<?= $i ?>_u_nfitem_idcontaitem" value="<?= $_itens['idcontaitem'] ?>" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?>>
                                                                <select id="idcontaitem<?= $_itens["idnfitem"] ?>" name="" class='size25' setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?> <?= $vnulo ?> onchange="alterarContaItem(this, <?= $_itens['idnfitem'] ?>)">
                                                                    <option value=""></option>
                                                                    <? fillselect($fillSelectContaItem, $idcontaitem) ?>
                                                                </select>
                                                            <?
                                                            } elseif (!empty($_itens['idcontaitem'])) {
                                                                echo $infoNf['nfitens']['traduzirContaItem'][$_itens['idcontaitem']];
                                                            ?>
                                                                <input type="hidden" nomodal id="iidcontaitem<?= $_itens["idnfitem"] ?>" name="_<?= $i ?>_u_nfitem_idcontaitem" value="<?= $idcontaitem ?>" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?>>
                                                            <?
                                                            } else {
                                                            ?>
                                                                <input type="hidden" nomodal id="iidcontaitem<?= $_itens["idnfitem"] ?>" name="_<?= $i ?>_u_nfitem_idcontaitem" value="<?= $_itens['idcontaitem'] ?>" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?>>
                                                                <select id="idcontaitem<?= $_itens["idnfitem"] ?>" name="" class="size20" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?> <?= $vnulo ?>>
                                                                    <option value=""></option>
                                                                    <? fillselect($infoNf['nfitens']['fillSelectContaItemProdserv'][$_itens['idprodserv']], $idcontaitem); ?>
                                                                </select>
                                                            <?
                                                            }
                                                            ?>
                                                        </td>
                                                        <td style="width: 10%;"></td>
                                                        <td style="width: 45%;" id="td<?= $row["idnfitem"] ?>" class="cp_tipo 1">
                                                            <?
                                                            if (!empty($_itens['idprodserv']) && !empty($_itens['idtipoprodserv'])) {
                                                            ?>
                                                                <input type="hidden" nomodal name="_<?= $i ?>_u_nfitem_idtipoprodserv" value="<?= $_itens['idtipoprodserv'] ?>" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?>>
                                                            <?
                                                                echo $_itens['tipoprodserv'];
                                                            } else {
                                                                if ($_nf['status'] == 'CONCLUIDO') {
                                                                    $arrIdTipoProdserv = $fillSelectTipoProdserv;
                                                                } elseif ($_itens['idcontaitem']) {
                                                                    $arrIdTipoProdserv = $infoNf['nfitens']['fillSelectTipoProdservIdContaItem'][$_itens['idcontaitem']];
                                                                } else {
                                                                    if (!empty($_itens['idprodserv'])) {
                                                                        $arrIdTipoProdserv = $infoNf['nfitens']['fillSelectContaItemProdserv'][$_itens['idprodserv']];
                                                                    } else {
                                                                        $arrIdTipoProdserv = array('' => '');
                                                                    }
                                                                }
                                                            ?>
                                                                <input type="hidden" nomodal id="iidtipoprodserv<?= $row["idnfitem"] ?>" name="_<?= $i ?>_u_nfitem_idtipoprodserv" value="<?= $_itens['idtipoprodserv'] ?>" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?>>
                                                                <select id="idtipoprodserv<?= $_itens["idnfitem"] ?>" name="" style="width: 100%" vnulo setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?>>
                                                                    <option value=""></option>
                                                                    <? fillselect($arrIdTipoProdserv, $_itens['idtipoprodserv']); ?>
                                                                </select>
                                                            <?
                                                            } ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>

                                            <? if (empty($_itens['idcontaitem'])) { ?>
                                                <i class="btn fa fa-info-circle laranja" title="Categoria e/ou Subcategoria não Atribuídas" id="btn_<?= $_itens["idnfitem"] ?>" onclick="mostrarModalGrupoES(<?= $_itens['idnfitem'] ?>,'<?= addslashes($descrProduto) ?>',<?= $i ?>)"></i>
                                            <? } else { ?>
                                                <i class="btn fa fa-info-circle" id="btn_<?= $_itens["idnfitem"] ?>" onclick="mostrarModalGrupoES(<?= $_itens['idnfitem'] ?>,'<?= addslashes($descrProduto) ?>',<?= $i ?>)"></i>
                                            <? } ?>
                                        </td>

                                        <!-- Tx Cv -->
                                        <?
                                        if (!empty($_itens['moedaext']) && $_itens['moedaext'] != "BRL") {
                                        ?>
                                            <td class="nowrap">
                                                <label class="alert-warning">
                                                    <? echo ($_itens['moedaext']); ?>
                                                </label>
                                                <?
                                                if ($_itens['moeda'] == "BRL") {
                                                ?>
                                                    <input setdisable<?= $_itens['idnf'] ?> <?= $infoStatus ?> vnulo <?= $nfreadonly ?> style="width: 60px;" title="Câmbio BRL" placeholder="Câmbio" name="_<?= $i ?>_<?= $_acao ?>_nfitem_convmoeda" type="text" value="<?= $_itens['convmoeda'] ?>" onkeyup="setvalnfitem(this, <?= $_itens['idnfitem'] ?>, <?= $_itens['vlritemext'] ?>)">
                                                <?
                                                }
                                                ?>
                                            </td>
                                        <?
                                        } elseif ($taxaConversao == TRUE) { ?>
                                            <td></td> <?
                                                    } ?>

                                        <!-- Valor Un -->
                                        <td class="nowrap">
                                            <?
                                            if ($_itens['vlritemext'] > 1) {
                                                if ($_itens['vlritem'] < 1 || empty($_itens['convmoeda'])) {
                                                    $cbt = "btn-danger";
                                                    $_vlritem = 0;
                                                } else {
                                                    $cbt = "btn-primary ";
                                                    $_vlritem = $_itens['vlritem'];
                                                }
                                            } else {
                                                $cbt = "btn-success";
                                                $_vlritem = $_itens['vlritem'];
                                            }
                                            ?>
                                            <button setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?> title="Moeda" moeda="<?= $_itens['moeda'] ?>" type="button" class="btn <?= $cbt ?>  btn-xs pointer" onclick="alterarMoeda(this,<?= $_itens['idnfitem'] ?>,'<?= $_itens['moedaext'] ?>')">
                                                <?= $_itens['moeda'] ?>
                                            </button>
                                            <? if ($_itens['moeda'] == "BRL") { ?>
                                                <input style="text-align: right; width: 80px;" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> onchange="alterarCamposNf(<?= $_itens['idnfitem'] ?>,'nfitem','vlritem','<?= $vchecked ?>',this, '<?= $_nf['idnf'] ?>')" id="nfitem<?= $_itens['idnfitem'] ?>" name="_<?= $i ?>_<?= $_acao ?>_nfitem_vlritem" type="text" value="<?= $_vlritem ?>">
                                            <?
                                            } else {
                                            ?>
                                                <input style="text-align: right;" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> name="_<?= $i ?>_<?= $_acao ?>_nfitem_moedaext" type="hidden" value="<?= $_itens['moeda'] ?>">
                                                <input style="text-align: right; width: 80px;" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> name="_<?= $i ?>_<?= $_acao ?>_nfitem_vlritemext" type="text" value="<?= $_itens['vlritemext'] ?>">
                                            <? } ?>
                                        </td>

                                        <!-- Desc Un -->
                                        <td><input style="text-align: right;" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> class="size6" name="_<?= $i ?>_<?= $_acao ?>_nfitem_des" id="des<?= $_itens['idnfitem'] ?>" onchange="alterarCamposNf(<?= $_itens['idnfitem'] ?>, 'nfitem', 'des', '<?= $vchecked ?>', this, '<?= $_nf['idnf'] ?>')" type="text" value="<?= $_itens['des'] ?>"></td>

                                        <!-- ICMS ST -->
                                        <td><input style="text-align: right;" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> class="size5" name="_<?= $i ?>_<?= $_acao ?>_nfitem_vst" onchange="alterarCamposNf(<?= $_itens['idnfitem'] ?>, 'nfitem', 'vst', '<?= $vchecked ?>', this, '<?= $_nf['idnf'] ?>')" type="text" value="<?= $_itens['vst'] ?>"></td>

                                        <!-- IPI % -->
                                        <td><input style="text-align: right;" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> class="size5" id="ipi<?= $_itens['idnfitem'] ?>" name="_<?= $i ?>_<?= $_acao ?>_nfitem_aliqipi" onchange="alterarCamposNf(<?= $_itens['idnfitem'] ?>, 'nfitem', 'aliqipi', '<?= $vchecked ?>', this, '<?= $_nf['idnf'] ?>')" type="text" value="<?= $_itens['aliqipi'] ?>" title="<?= number_format(tratanumero($_itens['valipi']), 2, ',', '.') ?>"></td>

                                        <!-- Total -->
                                        <td align="right">
                                            <span id="totalext<?= $_itens['idnfitem'] ?>">
                                                <?
                                                if ($_itens['moeda'] == "BRL") {
                                                    echo number_format(tratanumero($_itens['total'] + $_itens['valipi']), 2, ',', '.');
                                                } else {
                                                    echo $_itens['totalext'] + $_itens['valipi'];
                                                }
                                                ?>
                                            </span>
                                        </td>

                                        <!-- Validade -->
                                        <td><input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfreadonly ?> class="calendario size7" style="width: 100px;" name="_<?= $i ?>_<?= $_acao ?>_nfitem_validade" _idnfitem="<?= $_itens["idnfitem"] ?>" _idnf="<?= $_nf["idnf"] ?>" type="text" value="<?= dma($_itens['validade']) ?>"></td>

                                        <!-- Prev Entrega -->
                                        <td>
                                            <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> id="nfitem_previsaoentrega<?= $_itens["idnfitem"] ?>" <?= $nfdesabled ?> name="_<?= $i ?>_<?= $_acao ?>_nfitem_previsaoentrega" _idnfitem="<?= $_itens["idnfitem"] ?>" _idnf="<?= $_nf["idnf"] ?>" class="calendario size7" type="text" value="<?= dma($_itens['previsaoentrega']) ?>">
                                        </td>

                                        <!-- Obs -->
                                        <td title="<?= $_itens['obs'] ?>">
                                            <?
                                            if ($_itens['obs']) {
                                                $corIObsv = "azul";
                                            } else {
                                                $corIObsv = "cinza";
                                            }
                                            ?>

                                            <i title="Observação" class="fa btn-sm fa-info-circle <?= $corIObsv ?> pointer hoverazul tip modalObservacaoClique" idnfitem="<?= $_itens['idnfitem'] ?>"></i>

                                            <div class="panel panel-default" hidden>
                                                <div id="modalObservacao<?= $_itens['idnfitem'] ?>" class="panel-body">
                                                    <div class="row" style="width: 100%;">
                                                        <div class="col-md-2 head" style="color:#333; text-align: right;">Observação:</div>
                                                        <div class="col-md-10">
                                                            <textarea setdisable<?= $_nf['idnf'] ?> onkeyup="atualizarCampo(this, '<?= $_itens['idnfitem'] ?>')" <?= $infoStatus ?> <?= $nfreadonly ?> style="height: 80px;" name="_<?= $i ?>_u_nfitem_obs" id="_<?= $_itens['idnfitem'] ?>_nfitem_obs" type="text"><?= $_itens['obs'] ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td title="Compra Atual">
                                            <?
                                            if ($_itens["vlr"] > 0) { ?>
                                                <a class="pointer hoverazul" title="Compra Atual" style="float: right;"><?= number_format(tratanumero($_itens['vlr']), 2, ',', '.') ?> </a>
                                            <? }
                                            ?>
                                        </td>
                                        <td title="Última Compra" align="right">
                                            <?
                                            if (!empty($_itens['ultimacompra'])) {
                                                $dadosUltimaCompra = explode('#', $_itens['ultimacompra']);
                                            ?>
                                                <a class="pointer hoverazul" title="Última Compra" style="color:green;" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?= $dadosUltimaCompra[0] ?>')"><?= number_format(tratanumero($dadosUltimaCompra[1]), 2, ',', '.') ?> </a>
                                            <? }


                                            ?>
                                        </td>
                                        <td>
                                            <div class="row nowrap">
                                                <div class="col-md-6">
                                                    <?
                                                    if (!empty($_itens['idprodserv'])) { ?>
                                                        <div class="historicocompras" idnfitem="<?= $_itens["idnfitem"] ?>" idprodserv="<?= $_itens['idprodserv'] ?>" id="historicocompras<?= $_itens["idnfitem"] ?>">
                                                            <a class="fa fa-1x fa-info-circle btn-lg  azul pointer hoverazul" title="Histórico de compras em todas as empresas" data-target="webuiPopover0"></a>
                                                        </div>
                                                        <div class="webui-popover-content" id="target-<?= $_itens["idnfitem"] ?>"></div>
                                                    <?
                                                    } //if(!empty($_itens['idprodserv'])){
                                                    ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?
                                                    if (!empty($_itens['idprodserv'])) {
                                                    ?>
                                                        <div class="itensSemelhantes" idnfitem="<?= $_itens["idnfitem"] ?>" id="semelhantes<?= $_itens["idnfitem"] ?>">
                                                            <a class="fa fa-1x fa-search btn-lg azul pointer hoverazul" title="Ver Cotações" data-target="webuiPopover0"></a>
                                                        </div>
                                                        <div class="webui-popover-content">
                                                            <br />
                                                            <table class="table table-striped planilha">
                                                                <tr>
                                                                    <th></th>
                                                                    <th>Cotação</th>
                                                                    <th>Valor Item</th>
                                                                    <th>Fornecedor</th>
                                                                    <th>Status</th>
                                                                </tr>
                                                                <?
                                                                foreach ($infoNf['nfitens']['semelhantes'][$_itens['idprodserv']] as $_semelhantes) {
                                                                    if ($_semelhantes["nfe"] == 'Y') {
                                                                        $checked = 'checked';
                                                                        $vchecked = 'N';
                                                                    } else {
                                                                        $checked = '';
                                                                        $vchecked = 'Y';
                                                                    }
                                                                ?>
                                                                    <tr>
                                                                        <td><input setdisable<?= $_semelhantes['idnf'] ?> title="Nfe" type="checkbox" <?= $checked ?> class="<?= $_semelhantes['idnf'] ?> inputsemelhante" id="<?= $_semelhantes["idnfitem"] ?>" onclick="alterarCamposNf(<?= $_semelhantes['idnfitem'] ?>, 'nfitem', 'nfe', '<?= $vchecked ?>', this, <?= $_nf['idnf'] ?>)"></td>
                                                                        <td><a href="#nftable<?= $_semelhantes["idnf"] ?>"><?= $_semelhantes['idnf'] ?></a></td>
                                                                        <td><b>R$ <?= number_format(tratanumero($_semelhantes['vlritem']), 2, ',', '.') ?></b></td>
                                                                        <td><?= $_semelhantes['nome'] ?></td>
                                                                        <td><?= strtoupper($_semelhantes['rotulo']) ?></td>
                                                                    </tr>
                                                                <?
                                                                }
                                                                ?>
                                                            </table>
                                                        </div>


                                                    <? } ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <? if (empty($nfreadonly)) { ?>
                                                <a class="fa fa-download verde pointer btn-lg hoverazul" id="btdelocaitem<?= $_itens["idnfitem"] ?>" title="Deslocar item" onclick="deslocar(<?= $_itens['idnfitem'] ?>,this)"></a>
                                            <? } ?>
                                        </td>
                                        <td>
                                            <? if (empty($nfreadonly) && empty($_itens['idlote'])) { ?>
                                                <a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluirItem(<?= $_itens['idnfitem'] ?>)" title="Excluir Item!"></a>
                                            <? } ?>
                                        </td>
                                    </tr>
                                    <?
                                    if ($qtdnfitem == $qtdItens && in_array($_nf['status'], array('APROVADO', 'PREVISAO', 'DIVERGENCIA', 'CONCLUIDO', 'CONFERIDO')) && $_itens['nfe'] == 'N') {
                                    ?>
                                        </tr>
                                        </td>
                        </table>
                <? }
                                    $qtdItens++;
                                }
                ?>
                <tr>
                    <td colspan="19">
                        <table class="adicionarNovoItem<?= $_nf['idnf'] ?>">
                            <tr class="trNovoItem"></tr>
                        </table>
                    </td>
                </tr>
                <?
                if (empty($nfreadonly)) {
                ?>
                    <tr class="hidden" id="modeloNovoItem<?= $_nf['idnf'] ?>">
                        <td></td>
                        <td colspan="2">
                            <input type="text" size="60" class="ui-autocomplete-input autocompletenovoitem" idnf="<?= $_nf['idnf'] ?>" idpessoa="<?= $_nf['idpessoa'] ?>" placeholder="**Novo item**" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?>>
                        </td>
                        <td colspan="13"></td>
                    </tr>

                    <tr class="esconderdiv">
                        <td colspan="8">
                            <i id="novoitem" class="fa fa-plus-circle fa-1x verde btn-lg pointer" onclick="inserirNovoItem(<?= $_nf['idnf'] ?>)" title="Inserir novo Item"></i>
                        </td>
                        <td colspan="8"></td>
                    </tr>
                <? } ?>
                <tr class="esconderdiv">
                    <?
                    if ($taxaConversao == TRUE) {
                        $colspanmodfrete = 9;
                        $colspanfrete = 2;
                    } else {
                        $colspanmodfrete = 8;
                        $colspanfrete = 2;
                    }
                    ?>

                    <td colspan="<?= $colspanmodfrete ?>"></td>
                    <td title="<?= CotacaoController::$tituloFrete ?>" align="right" colspan="<?= $colspanfrete ?>">
                        Frete:
                        <select setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> class="size6" name="_<?= $iobs ?>_<?= $_acao ?>_nf_modfrete" <?= $nfdesabled ?>>
                            <? fillselect(CotacaoController::$tipoFrete, $_nf['modfrete']); ?>
                        </select>

                        <?
                        if ($_nf['modfrete'] == '1') {
                            if (!empty($_nf['idnfe'])) {
                                $cte = CotacaoController::buscarCtePorIdNfe($_nf['idnfe'], $_nf['idnf']);
                            } else {
                                $cte = CotacaoController::buscarCte($_nf['idnf']);
                            }

                            if (count($cte) > 0) {
                        ?>
                                <a title="CTE" class="fa fa-bars preto pointer hoverazul" href="?_modulo=nfcte&_acao=u&idnf=<?= $cte[0]['idnf'] ?>" target="_blank"></a>
                            <?
                            } elseif ($_nf['status'] == "APROVADO" || $_nf['status'] == "PREVISAO") {
                            ?>
                                <i class="fa fa-plus-circle fa-1x verde  pointer" onclick="inserirNovoCte(<?= $_nf['idnf'] ?>)" title="Gerar Programação de CTe "></i>
                        <?
                            }
                        }
                        ?>
                    </td>
                    <?
                    if (empty($_nf['frete'])) {
                        $frete = 0.00;
                    } else {
                        $frete = $_nf['frete'];
                    }
                    ?>
                    <td>
                        <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> style="text-align-last: end;" name="_<?= $iobs ?>_<?= $_acao ?>_nf_frete" size="8" value="<?= number_format(tratanumero($frete), 2, ',', '.'); ?>" vdecimal onchange="atualizarFrete(this,<?= $_nf['idnf'] ?>)">
                    </td>
                    <td colspan="7"></td>
                </tr>
                <tr class="esconderdiv" style="background-color: #FFFFFF;">
                    <? if ($taxaConversao == TRUE) { ?>
                        <td align="right" colspan="11">Subtotal: <b><?= $moeda ?> </b></td>
                    <? } else { ?>
                        <td align="right" colspan="10">Subtotal: <b><?= $moeda ?> </b></td>
                    <? } ?>
                    <td align="right">
                        <b id="totalsemdesc<?= $_itens['idnf'] ?>">
                            <?= number_format(tratanumero($totalsemdesc), 2, ',', '.'); ?>
                        </b>
                    </td>
                    <td colspan="10"></td>
                </tr>
                <tr class="esconderdiv" style="background-color: #FFFFFF;">
                    <? if ($taxaConversao == TRUE) { ?>
                        <td align="right" colspan="11">Desconto: <b><?= $moeda ?> </b></td>
                    <? } else { ?>
                        <td align="right" colspan="10">Desconto: <b><?= $moeda ?> </b></td>
                    <? } ?>
                    <td align="right">
                        <b id="desconto<?= $_itens['idnf'] ?>">
                            <?= number_format(tratanumero($desconto), 2, ',', '.'); ?>
                        </b>
                    </td>
                    <td colspan="10"></td>
                </tr>

                <tr class="esconderdiv" style="background-color: #FFFFFF;">
                    <? if ($taxaConversao == TRUE) { ?>
                        <td align="right" colspan="11">Total: <b><?= $moeda ?> </b></td>
                    <? } else { ?>
                        <td align="right" colspan="10">Total: <b><?= $moeda ?> </b></td>
                    <? } ?>
                    <td align="right">
                        <b id="totalcomdesc<?= $_itens['idnf'] ?>">
                            <? $vtotal =  $total + tratanumero($_nf['frete']); ?>
                            <?= number_format(tratanumero($vtotal), 2, ',', '.'); ?>
                        </b>
                        <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> id="totalNf<?= $_nf['idnf'] ?>" name="_<?= $iobs ?>_<?= $_acao ?>_nf_total" type="hidden" value="<?= $vtotal ?>" vdecimal>
                    </td>
                    <td colspan="10"></td>
                </tr>
                </tbody>
                </table>
                <div class="row esconderdiv">
                    <div class="col-md-5">
                        <div class="">
                            <div class="" href="#transporte<?= $iobs ?>"></div>
                            <div>
                                <table id="Pagamento<?= $iobs ?>">
                                    <? if ($_nf['formapgto']) { ?>
                                        <tr>
                                            <td class="nowrap">Pag. Fornecedor:<span style="color: red;"><?= $_nf['formapgto'] ?></span></td>
                                        </tr>
                                    <? } ?>
                                    <? if ($_nf['parcelas'] > 1) {
                                        $strdivtab = "style='display:block;'";
                                    } else {
                                        $strdivtab = "style='display:none;'";
                                    } ?>
                                    <tr>
                                        <td>Pagamento:</td>
                                        <td>1º Venc. em dias:</td>
                                        <td>Parcelas:</td>
                                        <td>
                                            <div class="divtab intervaloClass<?= $_nf['idnf'] ?>" <?= $strdivtab ?> id="divtab1">Intervalo Par. em dias:</div>
                                        </td>
                                        <td>Observação Cotação:</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> cbvalue="<?= $_nf['idformapagamento'] ?>" class="size30 forma_pagamento" id="formapagamento<?= $_nf['idnf'] ?>" name="_<?= $iobs ?>_<?= $_acao ?>_nf_idformapagamento" <?= $nfdesabled ?> value="<?= $jPag[$_nf['idformapagamento']]['descricao'] ?>">
                                        </td>

                                        <td><input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> class="size10 diasentrada<?= $_nf['idnf'] ?>" name="_<?= $iobs ?>_<?= $_acao ?>_nf_diasentrada" size="2" type="number" value="<?= $_nf['diasentrada'] ?>" vdecimal <?= $nfreadonly ?> onchange="atualizarParcelas(this, <?= $_nf['idnf'] ?>)"></td>

                                        <td>
                                            <select setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> class="size10 parcelas<?= $_nf['idnf'] ?>" id="parcelas" name="_<?= $iobs ?>_<?= $_acao ?>_nf_parcelas" <?= $nfdesabled ?> onchange="atualizarParcelas(this, <?= $_nf['idnf'] ?>)">
                                                <option value=""></option>
                                                <?
                                                for ($isel = 1; $isel <= 60; $isel++) {
                                                    if ($isel == 1) {
                                                        $arrayParcelas[$isel] = $isel . "x";
                                                    } else {
                                                        $arrayParcelas[$isel] = $isel . "x";
                                                    }
                                                }
                                                fillselect($arrayParcelas, $_nf['parcelas']);
                                                ?>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="divtab nowrap intervaloClass<?= $_nf['idnf'] ?>" <?= $strdivtab ?> id="divtab2">
                                                <input class="intervaloant<?= $_nf['idnf'] ?>" type="hidden" value="<?= $_nf['intervalo'] ?>">
                                                <input setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?> class="size10 intervalo<?= $_nf['idnf'] ?>" name="_<?= $iobs ?>_<?= $_acao ?>_nf_intervalo" type="text" value="<?= $_nf['intervalo'] ?>" vdecimal onchange="atualizarParcelas(this, <?= $_nf['idnf'] ?>)">
                                            </div>
                                        </td>
                                        <td><textarea class="size50" setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> <?= $nfdesabled ?> name="_<?= $iobs ?>_<?= $_acao ?>_nf_obs"><?= $_nf['obs'] ?></textarea></td>
                                    </tr>
                                    <tr id="transporte<?= $iobs ?>">
                                        <td colspan="4">Transportadora:</td>
                                        <td>Observação Interna:</td>
                                    </tr>
                                    <tr>
                                        <td class="nowrap" colspan="4">
                                            <select setdisable<?= $_nf['idnf'] ?> <?= $infoStatus ?> class="size55" name="_<?= $iobs ?>_<?= $_acao ?>_nf_idtransportadora" <?= $nfdesabled ?>>
                                                <option value=""></option>
                                                <? fillselect($fillSelectTransportadora, $_nf['idtransportadora']); ?>
                                            </select>
                                            <? if (!empty($_nf['idtransportadora'])) { ?>
                                                <a title="Transportadora" class="fa fa-bars preto pointer hoverazul" href="?_modulo=pessoa&_acao=u&idpessoa=<?= $_nf['idtransportadora'] ?>" target="_blank"></a>
                                            <? } ?>
                                        </td>
                                        <td><textarea class="size50" name="_<?= $iobs ?>_<?= $_acao ?>_nf_obsinterna"><?= $_nf['obsinterna'] ?></textarea></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="compra<?= $_nf['idnf'] ?>" style="display: none">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <table>
                                <tr id="editforn">
                                    <td align="right">Fornecedor:</td>
                                    <td title="Pesquisar e inserir item exclusivo por fornecedor.">
                                        <div class="input-group">
                                            <input id="_f_nome<?= $_nf['idnf'] ?>" type="text" value="<?= $_nf['nome'] ?>" cbvalue="<?= $_nf['idpessoa'] ?>" name="x_f_nome" class="size12" disabled style="background-color: #e6e6e6; width:180px; ">
                                            <a id="editar_fornecedor" class="fa fa-pencil hoverazul btn-lg pointer" onclick="editarFornecedor();" title="Editar fornecedor"></a>
                                        </div>
                                    </td>
                                </tr>
                                <?
                                $b = 0;
                                foreach ($infoNf['nfitens'][$_nf['idnf']] as $_duplicarCompra) {
                                    $b = $b + 1;
                                ?>
                                    <tr id="tr<?= $_duplicarCompra['idnfitem'] ?>">
                                        <td><?= $_duplicarCompra['qtdsol'] ?>
                                            <input id="quantidade<?= $_nf['idnf'] ?>" name="_<?= $b ?>__quantidade" type="hidden" style="width: 80px;" value="<?= $_duplicarCompra['qtdsol'] ?>">
                                            <input id="idnfitem<?= $_nf['idnf'] ?>" name="_<?= $b ?>__idnfitem" type="hidden" value="<?= $_duplicarCompra['idnfitem'] ?>">
                                        </td>
                                        <td colspan="2">
                                            <? if (!empty($rowiy['prodservdescr'])) { ?>
                                                <?= $_duplicarCompra['prodservdescr'] ?>
                                            <? } else { ?>
                                                <?= $_duplicarCompra['descr'] ?>
                                            <? } ?>
                                        </td>
                                        <td><i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluirItemDuplicarCompra(this)" alt="Excluir item!"></i></td>
                                    </tr>
                                <? } ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <? }
}
if ($_GET['tipo'] == 'orcamento') {
    if (!empty($_GET['idcotacao'])) {
        $solcomAssociadaCotacao = CotacaoController::buscarSolicitacaoComprasAssociadoCotacao($_GET['idcotacao']);
        $qtdSolcom = count($solcomAssociadaCotacao);
    }

    if ($qtdSolcom > 0) {
    ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="panel-heading" data-toggle="collapse" href="#gpOrigemTag">Origem Solicitação de Orçamento</div>
                            <div class="panel-body collapse" id="gpOrigemTag" style="padding-top: 8px !important;">
                                <table class="table table-striped planilha">
                                    <tr>
                                        <td>Sol. Compras</td>
                                        <td>Produto</td>
                                        <td>Cotação</td>
                                        <td>Unidade</td>
                                        <td>Status</td>
                                        <td>Criado Em</td>
                                        <td>Solicitado Por</td>
                                    </tr>
                                    <?
                                    foreach ($solcomAssociadaCotacao as $rowSolcom) { ?>
                                        <tr>
                                            <td>
                                                <label class="idbox">
                                                    <?= $rowSolcom['idsolcom'] ?>
                                                    <a class="fa fa-bars pointer fade" target="_blank" href="?_modulo=solcom&_acao=u&idsolcom=<?= $rowSolcom['idsolcom'] ?>"></a>
                                                </label>
                                            </td>
                                            <td><?= $rowSolcom["descrcurta"] ?></td>
                                            <td><?= $rowSolcom["idnf"] ?></td>
                                            <td><?= $rowSolcom["unidade"] ?></td>
                                            <td><?= $rowSolcom["status"] ?></td>
                                            <td><?= dma($rowSolcom["criadoem"]) ?></td>
                                            <td><?= $rowSolcom["nomecurto"] ?></td>
                                        </tr>
                                    <? } ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<? }
}
