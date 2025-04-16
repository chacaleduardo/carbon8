<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/nfentrada_controller.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

/**********************************************************************************************************************
*												|							|										  *
*												|	Modal Controles Nf  	|										  *
*												V							V										  *
***********************************************************************************************************************/

$pagvaltabela = "nf";
$pagvalmodulo = $_GET['_modulo'];
$idnf = $_GET['idnf'];
$pagvalcampos = array(
    "idnf" => "pk"
);
$pagsql = "SELECT * FROM nf WHERE idnf = '#pkid'";

include_once("../inc/php/controlevariaveisgetpost.php");

?>
<link href="../form/css/nfentrada_css.css?_<?=date("dmYhms")?>" rel="stylesheet">
<?

if ($_1_u_nf_tiponf == 'C' || $_1_u_nf_tiponf == 'T' || $_1_u_nf_tiponf == 'O') 
{
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
             <div class="panel-heading cabecalho" style="font-size:12px">
                <div class="row">
                    <div class="col-sm-1 sigla-empresa"></div>
                    Controles NF
                </div>
            </div>
            <input name="_1_<?=$_acao?>_nf_idnf" id="idnf" type="hidden" value="<?=$_1_u_nf_idnf ?>" readonly='readonly'>
            <input name="_1_<?=$_acao?>_nf_dtemissao" id="tiponf" type="hidden" value="<?=$_1_u_nf_dtemissao ?>">
            <input name="_1_<?=$_acao?>_nf_tiponf" id="idnf" type="hidden" value="<?=$_1_u_nf_tiponf ?>">
            <div class="panel-body">
                <div class="form-group">
                    <label>Data Entrada:</label>
                    <div>
                        <? if(cb::idempresa() == 4) { 
                            ?>
                            <input name="_1_<?=$_acao ?>_nf_prazo" disabled="disabled" <? if(!empty($_1_u_nf_xmlret)) {echo "vnulo"; }?> class="calendario size8" id="fdata1" type="text" value="<?=$_1_u_nf_prazo ?>" autocomplete="off">
                            <? if ($_1_u_nf_status == 'CONCLUIDO' ) { ?>
                                <input name="_1_<?=$_acao ?>_nf_prazo" type="hidden" value="<?=$_1_u_nf_prazo ?>">
                            <? } elseif((array_key_exists("dataentradacompras", getModsUsr("MODULOS"))) && !empty($_1_u_nf_prazo)) { ?>
                                <i class="fa fa-pencil btn-lg pointer" title='Editar Envio' onclick="alteravalor('prazo', '<?=dma($_1_u_nf_prazo) ?>', 'modulohistorico', <?=$_1_u_nf_idnf ?>,'Data Entrada:')"></i>
                                <?     
                            }                       
                        } else { ?>
                            <input name="_1_<?=$_acao ?>_nf_prazo" <? if(!empty($_1_u_nf_xmlret)) {echo "vnulo"; }?> class="calendario size8" <? if ($_1_u_nf_status == 'CONCLUIDO' ) { ?> disabled="disabled" <? } ?> id="fdata1" type="text" value="<?=$_1_u_nf_prazo ?>" autocomplete="off">
                            <? if ($_1_u_nf_status == 'CONCLUIDO' ) { ?>
                                <input name="_1_<?=$_acao ?>_nf_prazo" type="hidden" value="<?=$_1_u_nf_prazo ?>">
                            <? } 
                        } 
                        ?>
                    </div>
                </div>
                
                <? if ($_1_u_nf_tiponf == 'C' || $_1_u_nf_tiponf == 'O' || $_1_u_nf_tiponf == 'T') { 

                    if($_1_u_nf_status=="INCICIO" or $_1_u_nf_status=="ENVIADO" or $_1_u_nf_status=='RESPONDIDO' or $_1_u_nf_status=='AUTORIZADA' or $_1_u_nf_status=='AUTORIZADO' or $_1_u_nf_status=='PREVISAO' or empty($_1_u_nf_idfinalidadeprodserv)){
                        $disfilinalidade='';
                    }else if(array_key_exists("alterafinalidade", getModsUsr("MODULOS"))){
                         $disfilinalidade='';
                    }else{
                        $disfilinalidade="disabled='disabled'";
                    }
                    
                    ?>
                    <div class="form-group">
                        <label>Finalidade:</label>
                        <div class="input-group">
                            <select <?=$disfilinalidade?> id="idfinalidadeprodserv" name="_1_<?=$_acao ?>_nf_idfinalidadeprodserv" class='size30' vnulo <? if ($_1_u_nf_envionfe == 'CONCLUIDA') { ?> onchange="atualizafinalidade(this)" <? } ?>>
                                <option value=""></option>
                                <? fillselect(NfEntradaController::buscarFinalidadeProdserv(), $_1_u_nf_idfinalidadeprodserv); ?>
                            </select>
                        </div>
                    </div>
                <? } ?> 
            
                <div class="form-group">
                    <label>Upload XML?</label>
                    <div class="input-group">
                        <i class="fa fa-cloud-upload dz-clickable pointer azul" id="xmlnfe" title="Clique para carregar um XML"></i>
                        <?
                        if (!empty($_1_u_nf_xmlret)) { ?>
                            <font color="red"><? echo ("Esta NF possui XML Carregado"); ?></font><?
                        }
                        ?>
                    </div>
                </div>

                <? if (($_1_u_nf_tiponf == 'O' || $_1_u_nf_tiponf == 'C') and empty($_1_u_nf_xmlret)) { ?>
                    <div class="form-group">
                        <label>Chave NFe:</label>
                        <div class="input-group">
                            <input id="idnfe" <?=$readonly ?> name="_1_<?=$_acao ?>_nf_idnfe" size="40" type="text" value="<?=$_1_u_nf_idnfe ?>" onchange="manifestanf(this)" pattern="[0-9]+$">
                        </div>
                    </div>
                <?
                }elseif ($_1_u_nf_tiponf == 'T' && empty($_1_u_nf_xmlret)) { 
                ?>
                 <div class="form-group">
                        <label>Chave CTe:</label>
                        <div class="input-group">
                            <input id="idnfe" <?=$readonly ?> name="_1_<?=$_acao ?>_nf_idnfe" size="40" type="text" value="<?=$_1_u_nf_idnfe ?>" onchange="salvachavecte(this)" pattern="[0-9]+$">
                        </div>
                    </div>
                <?
                }                
                ?>

                <div class="form-group">
                    <label>Ações NF:</label>
                    <div class="input-group col-md-12 alinhamento-esquerda-20">
                        <div class="col-md-4">
                            <div class="col-md-1">
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
                            <div class="col-md-10 top-align-sete">
                                Sped?
                                <?
                                $idSpedC100 = "";
                                $spedC100 = NfEntradaController::buscarSpedC100($_1_u_nf_idnf, "'ATIVO','CORRIGIDO'");
                                $qtdc100 = count($spedC100);
                                if ($qtdc100 > 0) 
                                {
                                    ?>
                                    <a class="fa fa-bars pointer hoverazul" title="Editar" onclick="janelamodal('?_modulo=spedc100&_acao=u&idspedc100=<?=$spedC100['idspedc100'] ?>')"></a>
                                    <?
                                    $idSpedC100 = $spedC100['idspedc100'];
                                }

                                $spedD100 = NfEntradaController::buscarSpedD100($_1_u_nf_idnf, "'ATIVO','CORRIGIDO'");
                                $qtdd100 = mysqli_num_rows($resd100);
                                if ($qtdd100 > 0) {
                                    $rowd100 = mysqli_fetch_assoc($resd100);
                                    ?>
                                    <a class="fa fa-bars pointer hoverazul" title="Editar" onclick="janelamodal('?_modulo=spedc100&_acao=u&idspedd100=<?=$spedD100['idspedd100'] ?>')"></a>
                                    <?
                                }
                                ?>
                            </div>
                        </div>
                        <? if ($qtdc100 > 0 and !empty($spedC100['idspedc100'])) 
                        {
                            ?>
                            <div class="col-md-4">
                                <div class="col-md-1 alinhamento-esquerda">
                                    <?
                                    if ($spedC100['status'] == 'CORRIGIDO') {
                                        $checked = 'checked';
                                        $vchecked = 'ATIVO';				    
                                    } else {
                                        $checked = '';
                                        $vchecked = 'CORRIGIDO';	
                                    }
                                    ?>                                
                                    <input class="btn-lg pointer" title="Conferido pela contabilidade não atualiza as informações do sped ao mexer nas configurações da nota." type="checkbox" <?=$checked ?> name="namesped" onclick="altcheck('spedc100','status',<?=$spedC100['idspedc100'] ?>,'<?=$vchecked ?>')">
                                </div>
                                <div class="col-md-10 top-align-sete">
                                    Conferido (Contabilidade)
                                    <?
                                    $listarHistoricoSped = NfEntradaController::buscarHistoricoSped($spedC100['idspedc100'], 'spedc100', 'status');
                                    $qtdhi = count($listarHistoricoSped);
                                    if ($qtdhi > 0) 
                                    { 
                                        ?>
                                        <i class="fa fa-info-circle pointer hoverazul tip">
                                            <span>
                                                <? foreach($listarHistoricoSped as $_historico) {
                                                    $validadopor = strtoupper($_historico['criadopor']);
                                                    $validadoem = dmahms($_historico['criadoem']);
                                                    ?>
                                                    <ul>
                                                        <li>Conferido por: <?=$validadopor ?> em: <?=$validadoem ?>
                                                    </ul>
                                                <? } ?>
                                            </span>
                                        </i>
                                    <? } ?>
                                </div>
                            </div>
                        <? } ?>
                    </div>
                </div>
                <div class="form-group alinhamento-esquerda-20">
                    <div class="input-group col-md-12">
                        <?
                        if ($_1_u_nf_tiponf == "C") 
                        {
                            if ($_1_u_nf_envionfe == "PENDENTE" and !empty($_1_u_nf_idnfe )){
                            ?>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-secondary btn-sm custom-sidebar" onclick="enviomanifestacao();" title="Confirmar Recebimento">
                                    <i class="fa fa-sign-out"></i>Confirmação
                                </button>
                            </div>
                            <?
                            }
                            if ($_1_u_nf_envionfe == "MANIFESTADA" and !empty($_1_u_nf_idnfe )){
                            ?>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-secondary btn-sm custom-sidebar" onclick="getnfe();" title="Baixar XML">
                                    <i class="fa fa-cloud-download"></i>Baixar XML
                                </button>
                            </div>
                          <?
                            }
                        }

                        if (!empty($_1_u_nf_xmlret)) 
                        {
                            ?>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-secondary btn-sm custom-sidebar" title="Baixar xml NFe" onclick="janelamodal('../inc/nfe/sefaz3/func/geraarquivo.php?idnotafiscal=<?=$_1_u_nf_idnf ?>')">
                                    <i class="fa fa-download"></i>XML
                                </button>
                            </div>
                            <?
                        }
                        
                        if ($_1_u_nf_envionfe == "CONCLUIDA" and ($_1_u_nf_tiponf == 'O' || $_1_u_nf_tiponf == 'C') and (!empty($_1_u_nf_idfinalidadeprodserv) || $_1_u_nf_faticms == 'Y' || $_1_u_nf_consumo == 'Y' || $_1_u_nf_imobilizado == 'Y' ||  $_1_u_nf_outro == 'Y' ||  $_1_u_nf_comercio == 'Y')) 
                        {
                            $tipoconsumo = traduzid('finalidadeprodserv', 'idfinalidadeprodserv', 'tipoconsumo', $_1_u_nf_idfinalidadeprodserv);
                            ?>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-secondary btn-sm custom-sidebar" title="Danfe" onclick="janelamodal('../inc/nfe/sefaz4/func/printDANFE.php?idnotafiscal=<?=$_1_u_nf_idnf ?>')">
                                    <i class="fa fa-print"></i>Danfe
                                </button>
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-secondary btn-sm custom-sidebar" onclick="altfimnfe('<?=$tipoconsumo ?>');" title="Confirmar Recebimento">
                                    <i class="fa fa-refresh"></i>Atualizar Itens via XML
                                </button>
                            </div>
                            <?
                            if ($_1_u_nf_envionfe == "CONCLUIDA") 
                            {
                                ?>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-secondary btn-sm custom-sidebar" onclick="devolvernf();" title="Devolver NF">
                                        <i class="fa fa-mail-reply"></i>Devolver NF
                                    </button>
                                </div>
                                <?
                            }
                        } elseif ($_1_u_nf_envionfe == "CONCLUIDA" and $_1_u_nf_tiponf == 'T') {
                            ?>
                            <div class="div col-lg-2">
                                <button type="button" class="btn btn-secondary btn-sm custom-sidebar" title="Danfe" onclick="janelamodal('../inc/cte/vendor/nfephp-org/sped-da/functions/printcte.php?idnf=<?=$_1_u_nf_idnf ?>')">
                                    <i class="fa fa-print"></i>CTe
                                </button>
                            </div>
                            <div class="div col-lg-2">
                                <button type="button" class="btn btn-secondary btn-sm custom-sidebar" onclick="gerainfcte();" title="Atualizar CTE via XML">
                                    <i class="fa fa-refresh"></i>Atualizar CTe via XML
                                </button>
                            </div>
                            <?
                        }
                        
                        if (!empty($_1_u_nf_xmlret) && $_1_u_nf_status != 'CONCLUIDO') 
                        {
                            ?>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-secondary btn-sm custom-sidebar" onclick="excluirxml(<?=$_1_u_nf_idnf ?>)" title="Excluir XML">
                                    <i class="fa fa-trash"></i>Excluir XML
                                </button>
                            </div>
                            <?
                        }
                        ?>
                    </div>
                </div>

                <?
                if ($_1_u_nf_tiponf == 'C' || $_1_u_nf_tiponf == 'S' || $_1_u_nf_tiponf == 'O')
                { 
                    $listarXmlItem = NfEntradaController::buscarXmlNfItem($_1_u_nf_idnf);
                    $qtdx = count($listarXmlItem);
                    if ($qtdx > 0) 
                    {
                        ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Itens - XML</div>
                                    <div class="panel-body">
                                        <table class="table table-striped planilha">
                                            <tr>
                                                <th>Cód</th>
                                                <th>Produto</th>
                                                <th style="text-align: right !important;">Qtd</th>
                                                <th style="text-align: right !important;">Un</th>
                                                <th style="text-align: right !important;">Valor Un</th>
                                                <th style="text-align: right !important;">Desconto</th>
                                                <th style="text-align: right !important;">CFOP</th>
                                                <th style="text-align: right !important;">BC</th>
                                                <th style="text-align: right !important;">ICMS ST</th>
                                                <th style="text-align: right !important;">ICMS %</th>
                                                <th style="text-align: right !important;">ICMS R$</th>
                                                <th style="text-align: right !important;">IPI</th>
                                                <th style="text-align: right !important;">Frete</th>
                                                <th style="text-align: right !important;">Outras Desp</th>
                                                <th style="text-align: right !important;">Valor</th>
                                            </tr>
                                            <?
                                            $valorx = 0.00;
                                            $fretex = 0.00;
                                            $stx = 0.00;
                                            $vipi = 0.00;
                                            $desconto = 0.00;
                                            $i = 1;
                                            foreach($listarXmlItem as $_itemXml) 
                                            {
                                                $i = $i + 1;
                                                $valorx = $valorx + $_itemXml['valor'];
                                                $valorx = $valorx + $_itemXml['vst'];
                                                $valorx = $valorx + $_itemXml['vipi'];
                                                $valorx = $valorx + $_itemXml['outro'];

                                                $stx = $stx + $_itemXml['vicms'];
                                                $fretex = $fretex + $_itemXml['frete'];
                                                $vipi = $vipi + $_itemXml['vipi'];
                                                $desconto = $desconto + ($_itemXml['desconto']);
                                                $voutro = $voutro + $_itemXml['outro'];
                                                $valoritem = $_itemXml['valor'] / $_itemXml['qtd'];
                                                ?>
                                                <tr class="respreto" style="background-color: #FFFFFF;">
                                                    <td class="respreto" align="center" style="font-size: 10px;">
                                                        <select title="<?=$_itemXml['descricaoprod'] ?>" class="nfitemxml_idprodserv" name="_<?=$i?>_u_nfitemxml_idprodserv" cprodforn="<?=$_itemXml['cprod']?>" fnidpessoa="<?=$_1_u_nf_idpessoa?>" onchange="atnfitemxml(this,<?=$_itemXml['idnfitemxml']; ?>,'idprodserv','<?=$_1_u_nf_consumo?>')">
                                                            <option value=""></option>
                                                            <? fillselect(NfEntradaController::buscarProdutoItemProdservQueNaoExisteXml($_1_u_nf_idnf, $_itemXml['idnfitemxml'], $_itemXml['idprodserv'],$_1_u_nf_consumo),$_itemXml['idprodserv']); ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input <?=$readonly ?> name="_<?=$i ?>_u_nfitemxml_idnfitemxml" size="8" type="hidden" value="<?=$_itemXml["idnfitemxml"]; ?>">
                                                        <input id="descr<?=$_itemXml["idnfitemxml"]; ?>" name="_<?=$i ?>_u_nfitemxml_prodservdescr" size="8" type="hidden" value="<?=$_itemXml["descr"]; ?>">
                                                        <?=$_itemXml['descr'] ?>
                                                    </td>
                                                    <td align="right">
                                                        <?=number_format(tratanumero($_itemXml['qtd']), 2, ',', '.'); ?>
                                                        <input id="qtd<?=$_itemXml["idnfitemxml"]; ?>" name="_<?=$i ?>_u_nfitemxml_qtd" size="8" type="hidden" value="<?=$_itemXml["qtd"]; ?>">
                                                    </td>
                                                    <td align="right">
                                                        <?=$_itemXml['un'] ?>
                                                        <input id="un<?=$_itemXml["idnfitemxml"]; ?>" name="_<?=$i ?>_u_nfitemxml_un" size="8" type="hidden" value="<?=$_itemXml["un"]; ?>">
                                                    </td>
                                                    <td align="right">
                                                        <?=number_format(tratanumero($_itemXml['valor'] / $_itemXml['qtd']), 2, ',', '.'); ?>
                                                    </td>
                                                    <td align="right">
                                                        <input name="_<?=$i ?>_u_nfitemxml_des" class="size7" style="text-align: right;" type="text" value="<?=$_itemXml['desconto']; ?>" onchange="atnfitemxml(this,<?=$_itemXml['idnfitemxml']; ?>,'des')">
                                                        <? if (!empty($_itemXml['descontopor'])) 
                                                        {
                                                            $descontoajuste = 'Y';
                                                            ?>
                                                            <i title="Ajuste: <?=$_itemXml['descontopor'] ?> <?=dmahms($_itemXml['descontoem']) ?>" class="fa fa-info-circle preto pointer hoverazul tip"></i>
                                                        <? } ?>
                                                    </td>
                                                    <td align="right">
                                                        <?
                                                        if($_itemXml['cfop']< 4000){
                                                            echo($_itemXml['cfop'] );
                                                        }else{
                                                            ?>
                                                            <font color="red" title="CFOP sem conversão,favor cadastrar CFOP de entrada e carregar o xml novamente.">
                                                            <?=$_itemXml['cfop']?>
                                                            </font>
                                                            <?
                                                        }                                                        
                                                         ?>
                                                    
                                                    </td>
                                                    <td align="right"><?=number_format(tratanumero($_itemXml['basecalc']), 2, ',', '.'); ?></td>
                                                    <td align="right"><?=number_format(tratanumero($_itemXml['vst']), 2, ',', '.'); ?></td>
                                                    <td align="right"><?=number_format(tratanumero($_itemXml['aliq_icms']), 2, ',', '.'); ?></td>
                                                    <td align="right"><?=number_format(tratanumero($_itemXml['vicms']), 2, ',', '.'); ?></td>
                                                    <td align="right"><?=number_format(tratanumero($_itemXml['vipi']), 2, ',', '.'); ?></td>
                                                    <td align="right">
                                                        <input name="_<?=$i ?>_u_nfitemxml_frete" class="size7" style="text-align: right;" type="text" value="<?=$_itemXml["frete"]; ?>" onchange="atnfitemxml(this,<?=$_itemXml['idnfitemxml']; ?>,'frete')">
                                                        <? if (!empty($_itemXml['fretepor'])) {
                                                            $freteajuste = 'Y';
                                                        ?>
                                                            <i title="Ajuste: <?=$_itemXml['fretepor'] ?> <?=dmahms($_itemXml['freteem']) ?>" class="fa fa-info-circle preto pointer hoverazul tip"></i>
                                                        <? } ?>
                                                    </td>
                                                    <td align="right">
                                                        <input name="_<?=$i ?>_u_nfitemxml_outro" class="size7" style="text-align: right;" type="text" value="<?=$_itemXml["outro"]; ?>" onchange="atnfitemxml(this,<?=$_itemXml['idnfitemxml']; ?>,'outro')">
                                                    </td>
                                                    <td align="right">
                                                        <?=number_format(tratanumero($_itemXml['valor']), 2, ',', '.'); ?>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                            <tr class="header">
                                                <td colspan="5" align="right">Total</td>
                                                <td align="right"><?=number_format(tratanumero($desconto), 2, ',', '.'); ?></td>
                                                <td colspan="2" align="right"></td>
                                                <td></td>
                                                <td></td>
                                                <td align="right"><?=number_format(tratanumero($stx), 2, ',', '.'); ?></td>
                                                <td align="right"><?=number_format(tratanumero($vipi), 2, ',', '.'); ?></td>
                                                <td align="right"><?=number_format(tratanumero($fretex), 2, ',', '.'); ?></td>
                                                <td align="right"><?=number_format(tratanumero($voutro), 2, ',', '.'); ?></td>
                                                <td align="right"><?=number_format(tratanumero($valorx), 2, ',', '.'); ?></td>
                                                <td></td>
                                            </tr>
                                            <?
                                            if ($desconto > 0) 
                                            {
                                                if ($descontoajuste == 'Y') 
                                                {
                                                    $strdajuste = "Ajuste";
                                                } else {
                                                    $strdajuste = "";
                                                }
                                                ?>
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td></td>
                                                    <td colspan="2"></td>
                                                    <td colspan=5 align="right">Desconto <?=$strdajuste ?></td>
                                                    <td align="right">
                                                        <font color="red">
                                                            <?=number_format(tratanumero($desconto), 2, ',', '.'); ?>
                                                        </font>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            <?
                                            }

                                            if ($fretex > 0) 
                                            {
                                                if ($freteajuste == 'Y') {
                                                    $strfajuste = "Ajuste";
                                                } else {
                                                    $strfajuste = "";
                                                }
                                                ?>
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td></td>
                                                    <td colspan="2"></td>
                                                    <td colspan=5 align="right">Frete <?=$strfajuste ?></td>
                                                    <td align="right">
                                                        <font color="red">
                                                            <?=number_format(tratanumero($fretex), 2, ',', '.'); ?>
                                                        </font>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            <?
                                            }
                                            ?>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td></td>
                                                <td colspan="2"></td>
                                                <td colspan=5 align="right">Total</td>
                                                <td align="right">
                                                    <font color="red">
                                                        <?
                                                        $valorf = ($valorx + $fretex) - $desconto;
                                                        ?>
                                                        <?=number_format(tratanumero($valorf), 2, ',', '.'); ?>
                                                    </font>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </table>
                                        <table>
                                            <tr>
                                                <td colspan="" align="right">Alq. CPL. ICMS %</td>
                                                <td><input <?=$readonly ?> name="_1_<?=$_acao ?>_nf_icmscpl" size="3" type="text" value="<?=$_1_u_nf_icmscpl ?>"></td>
                                                <td colspan="2" align="right">Vlr. CPL. ICMS R$</td>
                                                <td><input <?=$readonly ?> name="_1_<?=$_acao ?>_nf_vlricmscpl" size="8" type="text" value="<?=$_1_u_nf_vlricmscpl ?>"></td>
                                                <td>
                                                    <font color="red">Observar se existe na observação da NF aproveitamento de ICMS.</font>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?
                    } //if($qtdx>0){
                } //tipo c e s
                ?>
            </div>
        </div>
    </div>
    <?
}

require_once('../form/js/compracontrolenf_js.php');
?>