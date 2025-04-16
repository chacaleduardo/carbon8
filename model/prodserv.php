<?
require_once(__DIR__."/../inc/php/validaacesso.php");
require_once(__DIR__."/../form/controllers/nfentrada_controller.php");

class PRODSERV
{
    public $valoritem = 0;
    //Retorna Tipo ProdServ - Utilizado no Evento
    function getListaProdServ()
    {
        $sqlm = "SELECT idprodserv, descr
                   FROM prodserv
                  WHERE status = 'ATIVO'
                    ".getidempresa('idempresa', 'prodserv')."
                    AND comprado = 'Y'
               ORDER BY descr;";
        $resm =  d::b()->query($sqlm)  or die("Erro sgdoctipo campo Prompt Drop sql:".$sqlm);
        return $resm;
    }
    function getProdServ($tipoobj = null)
    {
        global $JSON;
        $str = '';
        if ($tipoobj) {
            $tipoobj = explode(',', $tipoobj);
            $str = 'AND (';
            $or = '';
            foreach ($tipoobj as $key => $value) {
                $str .= $or.' '.$value.'="Y"';
                $or = ' OR';
            }
            $str .= ')';
        }
        $sqlm = "SELECT idprodserv, descr
                   FROM prodserv
                  WHERE status = 'ATIVO'
                    ".getidempresa('idempresa', 'prodserv')."
                   ".$str."
               ORDER BY descr;";
        $resm =  d::b()->query($sqlm)  or die("Erro sgdoctipo campo Prompt Drop sql:".$sqlm);
        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($resm)) {
            $arrtmp[$i]["value"] = $r["idprodserv"];
            $arrtmp[$i]["label"] = $r["descr"];
            $i++;
        }
        return $JSON->encode($arrtmp);
    }

    function getUnEstoque($idprodserv, $idunidade, $converteest, $unpadrao, $unlote)
    {
        $arrunori = getObjeto('unidade', $idunidade);
        $arrProdserv = getObjeto('prodserv', $idprodserv);
        if ($converteest == 'Y') {
            if ($arrunori['convestoque'] == 'N') {
                $un = $unlote;
            } else {
                $un = $unpadrao;
            }
        } else {
            $un = $unpadrao;
        }
        $vun = traduzid('unidadevolume', 'un', 'descr', $un);
        return $vun;
    }

    function getEstoqueLote($idlotefracao)
    {
        $arrlotefracao = getObjeto('lotefracao', $idlotefracao);
        $arrlote = getObjeto('lote', $arrlotefracao['idlote']);
        $arrunori = getObjeto('unidade', $arrlotefracao['idunidade']);
        $arrProdserv = getObjeto('prodserv', $arrlote['idprodserv']);

        if (/*$arrProdserv['uncptransf']=='Y' and */$arrunori['convestoque'] == 'N') {
            $qtdfr = $arrlotefracao["qtd"] / $arrlote['valconvori'];
        } else {
            $qtdfr = $arrlotefracao["qtd"];
        }

        return $qtdfr;
    }

    function getEstoqueLoteReal($idlotefracao)
    {
        $arrlotefracao = getObjeto('lotefracao', $idlotefracao);
        $arrlote = getObjeto('lote', $arrlotefracao['idlote']);

        $qtdfr = number_format(tratanumero($arrlotefracao["qtd"]), 2, ',', '.').' - '.traduzid('unidadevolume', 'un', 'descr', $arrlote['unpadrao']);

        return $qtdfr;
    }

    // retorna o historico de um consumo especifico
    function listalotecons($idlotecons)
    {
        $s = "select l.idlote,l.partida,l.exercicio from lotecons c join lote l on(l.idlote=c.idlote) where c.idlotecons=".$idlotecons;
        $r = d::b()->query($s) or die(" listalotecons - A consulta do lote falhou!!! : ".mysqli_error()."<p>SQL: $s");
        $rw = mysqli_fetch_assoc($r);
        ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading"> Partida:
                        <a class="hoverazul pointer" onclick="janelamodal('?_modulo=lotealmoxarifado&_acao=u&idlote=<?=$rw['idlote'] ?>')" title="Lote">
                            <?=$rw['partida'] ?>/<?=$rw['exercicio'] ?>
                        </a>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped planilha">
                            <?
                            $sql = " select c.idlotecons,c.tipoobjetoconsumoespec,c.tipoobjeto,c.qtdsol,c.qtdsol_exp,c.qtdd,c.qtdd_exp,c.qtdc as qtdc,c.qtdc_exp as qtdc_exp ,c.obs,c.criadoem,c.criadopor,a.partida,a.idlote,a.exercicio,o.idobjeto,u.unidade as destino ,uori.unidade as origem,u.idunidade
		from lotecons c join lotefracao f
                        join lotefracao lf on (lf.idlotefracao=c.idobjeto  and c.tipoobjeto ='lotefracao' )
                        left join lote a on(a.idlote=lf.idlote)
						left join unidadeobjeto o on(o.tipoobjeto='modulo' 	and o.idunidade = lf.idunidade)	
                        left join "._DBCARBON."._modulo m on (m.modulo = o.idobjeto and m.ready='FILTROS' and m.modulotipo = 'lote')	
                            join unidade u on(u.idunidade=lf.idunidade   )
                            join unidade uori on(uori.idunidade=f.idunidade )
		where c.idlotefracao=f.idlotefracao 
		and (c.qtdd > 0) 
        and c.status!='INATIVO'
        and c.idlotecons = ".$idlotecons."
		and  c.tipoobjeto = 'lotefracao'
        group by c.idlotecons";
                            //echo($sql);  
                            $res = d::b()->query($sql) or die(" listalotecons - A consulta dos consumos falhou!!! : ".mysqli_error()."<p>SQL: $sql");
                            $qtdv2 = mysqli_num_rows($res);
                            if ($qtdv2 > 0) {
                                $mostroucab = 'Y';
                            ?>

                                <tr>
                                    <th>Origem</th>
                                    <th>Destino</th>
                                    <th style="text-align: right !important;">Crédito</th>
                                    <th style="text-align: right !important;">Débito</th>
                                    <? if (!empty($unpdrado)) { ?>
                                        <th>Un</th>
                                    <? } ?>
                                    <th>Obs</th>
                                    <th>Por</th>
                                    <th>Em</th>
                                    <th></th>
                                </tr>
                                <?
                                echo ($tr);
                                while ($row = mysqli_fetch_assoc($res)) {
                                    $qtdd = $row["qtdd"] + $qtdd;
                                    $qtdc = $row["qtdc"] + $qtdc;
                                    if (empty($row['obs'])) {
                                        $row['obs'] == "Correção";
                                    }
                                    if ($row['tipoobjeto'] == 'lote') {
                                        $destino = $row['partida'].'/'.$row['exercicio'];
                                    } else {
                                        $destino = $row['destino'];
                                    }
                                    //se o a unidade do modulo não tiver lote
                                    if (empty($row['idobjeto'])) {
                                        $sqlmd = "select o.idobjeto from unidadeobjeto o
                        join "._DBCARBON."._modulo m 
                        on (m.modulo = o.idobjeto 
                            and m.ready='FILTROS' 
                            and m.modulotipo = 'lote')
                        where (o.tipoobjeto='modulo' 	and o.idunidade =8)";

                                        $rmd = d::b()->query($sqlmd) or die("Falha ao link lote da unidade:".mysqli_error(d::b()));
                                        $rowmd = mysqli_fetch_assoc($rmd);
                                        $row_idobjeto = $rowmd['idobjeto'];
                                    } else {
                                        $row_idobjeto = $row['idobjeto'];
                                    }


                                ?>
                                    <tr>
                                        <? //DEBITO E O CONTRARIO DE CREDITO ORIGEM/DESTINO
                                        if ($row["qtdd"] > 0) { ?>
                                            <td><?=$row['origem'] ?></td>
                                            <? if (!empty($row['idobjeto']) and $row['tipoobjeto'] == 'lote') { ?>
                                                <td onclick="janelamodal('?_modulo=<?=$row_idobjeto ?>&_acao=u&idlote=<?=$row['idlote'] ?>');" style="cursor: pointer;">
                                                    <font color="blue">
                                                    <? } else { ?>
                                                <td onclick="janelamodal('?_modulo=unidade&_acao=u&idunidade=<?=$row['idunidade'] ?>');" style="cursor: pointer;">
                                                    <font color="blue">
                                                    <? } ?>
                                                    <?=$destino ?> </font>
                                                </td>
                                            <? } else { ?>

                                                <? if (!empty($row['idobjeto']) and $row['tipoobjeto'] == 'lote') { ?>
                                                    <td onclick="janelamodal('?_modulo=<?=$row['idobjeto'] ?>&_acao=u&idlote=<?=$row['idlote'] ?>');" style="cursor: pointer;">
                                                        <font color="blue">
                                                        <? } else { ?>
                                                    <td>
                                                        <font>
                                                        <? } ?>
                                                        <?=$destino ?> </font>
                                                    </td>
                                                    <td><?=$row['origem'] ?></td>
                                                <? } ?>
                                                <td align="right">
                                                    <?
                                                    if ($row["qtdc"] > 0) {
                                                        if (
                                                            strpos(strtolower($row['qtdc_exp']), "d")
                                                            or strpos(strtolower($row['qtdc_exp']), "e")
                                                        ) {
                                                            echo recuperaExpoente(tratanumero($row["qtdc"]), $row['qtdc_exp']);
                                                        } else {
                                                            echo number_format(tratanumero($row["qtdc"]), 2, ',', '.');
                                                        }
                                                    } elseif ($row["qtdsol"] > 0) {
                                                        if (
                                                            strpos(strtolower($row['qtdsol_exp']), "d")
                                                            or strpos(strtolower($row['qtdsol_exp']), "e")
                                                        ) {
                                                            echo recuperaExpoente(tratanumero($row["qtdsol"]), $row['qtdsol_exp']);
                                                        } else {
                                                            echo number_format(tratanumero($row["qtdsol"]), 2, ',', '.');
                                                        }
                                                    } else {
                                                        echo "";
                                                    }
                                                    ?>
                                                </td>
                                                <td align="right">
                                                    <?
                                                    if ($row["qtdd"] > 0) {
                                                        if (
                                                            strpos(strtolower($row['qtdd_exp']), "d")
                                                            or strpos(strtolower($row['qtdd_exp']), "e")
                                                        ) {
                                                            echo recuperaExpoente(tratanumero($row["qtdd"]), $row['qtdd_exp']);
                                                        } else {
                                                            echo number_format(tratanumero($row["qtdd"]), 2, ',', '.');
                                                        }
                                                    } else {
                                                        echo "";
                                                    }
                                                    ?>
                                                </td>
                                                <? if (!empty($unpdrado)) { ?>
                                                    <td><?=$unpdrado ?></td>
                                                <? } ?>
                                                <td><?=$row['obs'] ?></td>
                                                <td><?=$row['criadopor'] ?></td>
                                                <td><?= dmahms($row['criadoem']) ?></td>
                                                <td>
                                                    <?/*if($_SESSION["SESSAO"]["USUARIO"]==$row['criadopor'] and empty($row['tipoobjetoconsumoespec'])){?>
                            <i class="fa fa-trash cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="(<?=$row['idlotecons']?>)" title="Excluir"></i>
                        <?}*/ ?>
                                                </td>
                                    </tr>
                            <?
                                }
                            } //if($qtdv2>0){
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?
    }

    function listaitem($listarItens, $tipoLista)
    {
        global $vnaocobrar,$vsubtotal, $vtotalicms, $valtotaldesconto, $vtotalipi, $i, $_1_u_nf_status, $_1_u_nf_tiponf, $readonly, $_1_u_nf_idnf, $qtdConversaoMoeda, 
               $_1_u_nf_idobjetosolipor, $internacional, $totalimpostoImportacao, $totalvalipi, $totalpis, $totalcofins, $_1_u_nf_siscomex, $_1_u_nf_icms,
               $_1_u_nf_moedainternacional, $totalimpostoImportacao;

        $ncmOld = '';
        $qtdConversaoMoedaInternacional = NfEntradaController::buscarSeExisteConversaoMoedaInternacional($_1_u_nf_idnf);
        foreach ($listarItens as $_itens) 
        {
            if($ncmOld != $_itens["ncm"] && $internacional == true && !empty($ncmOld)) {
                ?>
                <tr> 
                    <td class="estoque" colspan="50"><div><hr style="border-top: 4px solid #ddd;"></div></td>
                </tr>
                <?
            }

            $i = $i + 1;
            if (!empty($_itens['idlote']) || !empty($_itens['idlote2'])) 
            {
                $disableQtd = true;
            } else {
                $disableQtd = false;
            }

            if (empty($_itens["basecalc"]) or $_itens["basecalc"] == 0) {
                $_itens["basecalc"] = $_itens["total"];
            }

            if ($_itens['cobrar'] == 'Y') {
                if ($_itens['moeda'] == "BRL") {
                    $vsubtotal = $vsubtotal + $_itens['total'] + (($qtdConversaoMoedaInternacional > 0) ? 0 : $_itens['valipi']);
                    $moeda = $_itens['moeda'];
                } else {
                    $vsubtotal = $vsubtotal + $_itens['totalext'];
                    $moeda = $_itens['moeda'];
                }
            }else{
                if ($_itens['moeda'] == "BRL") {
                    $vnaocobrar = $vnaocobrar + $_itens['total'] + (($qtdConversaoMoedaInternacional > 0) ? 0 : $_itens['valipi']);
                  
                } else {
                    $vnaocobrar = $vnaocobrar + $_itens['totalext'];
                }
            }    

            $valtotaldesconto = $valtotaldesconto + ($_itens['des'] * $_itens["qtd"]);
            $vtotalicms = $vtotalicms + $_itens["valicms"];
            $vtotalipi = $vtotalipi + $_itens["valipi"];
            $tag = NfEntradaController::buscarTagPorIdNfItem($_itens['idnfitem']);
            ?>
            <tr idnfitem="<?=$_itens["idnfitem"] ?>">
                <td align="center" class="cobrar">
                    <? if ($_itens['cobrar'] == 'Y') {
                        $checked = 'checked';
                        $vchecked = 'N';
                    } else {
                        $checked = '';
                        $vchecked = 'Y';
                    }

                   if (!empty($_1_u_nf_status == 'CONCLUIDO')) {
                        $disablednamedfec = 'disabled';
                    } ?>
                    <input title="Ao desmarcar este, o valor do mesmo, não é contabilizado na fatura." type="checkbox" <?= $checked ?> <?= $disablednamedfec ?> name="namenfec" onclick="altcheck('nfitem','cobrar',<?= $_itens['idnfitem'] ?>,'<?= $vchecked ?>')">
                </td>
                <?
                if($tipoLista == 'cadastrado')
                {
                    ?>
                    <td class="cp_qtdsol alinhar-direita">
                        <?=$_itens["qtdsol"]; ?>
                        <?
                        if ($_itens['tipoobjetoitem'] == 'nfitem' and !empty($_itens['idobjetoitem'])) 
                        {
                            $nfOrigem = NfEntradaController::buscarDadosNfPorIdNfItem($_itens['idobjetoitem']);
                            if(!empty($nfOrigem)) 
                            {
                                ?>
                                <a title="Compra Origem - <?=$nfOrigem['idnf'] ?>" class="fa fa-bars fade pointer hoverazul" href="?_modulo=nfentrada&_acao=u&idnf=<?=$nfOrigem['idnf'] ?>" target="_blank"></a>
                                <?
                            }
                        }
                        ?>
                    </td>
                    <?
                } elseif($_1_u_nf_tiponf != 'R') { ?>
                    <td class="cp_qtdsol"></td>
                    <?
                }
                ?>                
                <td class="cp_qtd">                    
                    <div class="col-md-12 padding-zero" >
                        <div class="col-md-9 padding-zero">
                            <input <?=$readonly ?> name="_<?=$i ?>_u_nfitem_idnfitem" size="8" type="hidden" class="idnfitem" value="<?=$_itens["idnfitem"]; ?>">
                            <!-- Se a quantidade solicitada for diferente da quantidade, o texto será exibido em vermelho -->
                            <? 
                            $_itens["qtdsol"] = $_itens["qtdsol"] == NULL ? '0.00' : $_itens["qtdsol"];
                            if (($_itens["qtd"] < $_itens["qtdsol"]) && !empty($_itens["qtdsol"])) { 
                                $classColorirVermelho = 'colorirVermelho';
                            } else {
                                $classColorirVermelho = "";
                            }
                            ?>
                            <input <?=$disableQtd ? "readonly='readonly'" : ""; ?> name="_<?=$i ?>_u_nfitem_qtd" class="size5 <?=$classColorirVermelho?> alinhar-direita qtd-<?=$_itens["idnfitem"]; ?>" type="text" value="<?=$_itens["qtd"]; ?>" vdecimal>
                        </div>
                        <div class="col-md-2 alinhamento-topo">
                            <? 
                            if (($_itens["qtd"] < $_itens["qtdsol"]) && !empty($_itens["qtdsol"])) 
                            { 
                                $listarObjetoOrigem = NfEntradaController::buscarNfitemPorIdobjetoTipoobjetoEIdNfOrigem($_itens["idnfitem"], 'nfitem', $_itens['idnf']);
                                if ($listarObjetoOrigem['qtdLinhas'] < 1) 
                                {
                                    ?>
                                    <a class="fa fa-plus-circle verde hoververde  pointer " title="Duplicar Compra" onclick="duplicarcompra(<?=$_itens['idnf'] ?>)"></a>
                                    <?
                                }
                                if(!empty($listarObjetoOrigem['dados'])) {
                                    ?>
                                    <a title="Complemento Criado - <?=$listarObjetoOrigem['dados']['idnf'] ?>" class="fa fa-bars fade pointer hoverazul" href="?_modulo=nfentrada&_acao=u&idnf=<?=$listarObjetoOrigem['dados']['idnf'] ?>" target="_blank"></a>
                                    <?
                                }
                            }
                            ?>
                        </div>
                    </div>
                </td>
                
                <td title="<?=$_itens["unidade"] ?>" class="cp_un">
                    <? //corrigido para mostrar unidade do item 461046 hermesp 28042021
                    if (empty($_itens["idprodservforn"]))
                    {
                        //Valida se o item foi inserido manual. Caso tenha sido e o valor unidade estiver vazio insere na tabela nfitem esta informação.
                        // LTM (26/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=328326
                        if (empty($_itens['idprodserv']) && $_1_u_nf_status == 'INICIO') 
                        {
                            ?>
                            <select name="_<?=$i ?>_u_nfitem_un" style="width: 60px;">
                                <option value=""></option>
                                <? fillselect(NfEntradaController::listarUnidadeVolume(), $_itens["unidade"]); ?>
                            </select>
                            <?
                        } else {
                            echo ($_itens["unidade"]);
                        }
                    } else {
                        echo ($_itens["unidade"]);
                    }
                    ?>
                </td>
                <td class="cp_descricao">
                    <? if (!empty($_itens['idprodserv'])) { ?>
                        <a class="pointer" title="<?=$_itens["codprodserv"] ?>" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$_itens["idprodserv"] ?>')">
                            <small>
                                <?
                                if (empty($_itens["codforn"])) {
                                    echo ($_itens["descr"]." - ".$_itens["codprodserv"]);
                                } else {
                                    echo ($_itens["codforn"]);
                                }
                                ?>
                            </small>
                        </a>
                    <? } else {
                        ?>
                        <input <? if ($_itens["idpessoa"]) { ?>readonly='readonly' <? } ?> type="text" name="_<?=$i ?>_u_nfitem_prodservdescr" value="<?=$_itens["prodservdescr"] ?>">
                        <? 
                    }

                    //Atribui texto da descrição a uma variavel para ser exibida no titulo do modal 
                    if (!empty($_itens['idprodserv'])) {
                        if (empty($_itens["codforn"])) {
                            $titulomodal = $_itens["descr"]." - ".$_itens["codprodserv"];
                        } else {
                            $titulomodal = $_itens["codforn"];
                        }
                    } else {
                        $titulomodal = $_itens["prodservdescr"];
                    }


                    $dadosSolcom = NfEntradaController::buscarItensSolcom($_itens['idprodserv'], $_1_u_nf_idobjetosolipor);
                    foreach($dadosSolcom AS $_dadosSolcom){
                        ?>
                        <label class="idbox" style="margin-left: 5px; margin-right: 5px;">
                            <a title="Solicitação Compras" class="fade pointer hoverazul" style="padding: 3px;" idprodserv="<?=$_itens['idprodserv']?>" href="?_modulo=solcom&_acao=u&idsolcom=<?=$_dadosSolcom['idsolcom']?>" target="_blank">
                                <?=$_dadosSolcom['idsolcom']?>
                            </a>
                        </label>
                        <?
                    }
                    ?>
                </td>
                <td>
                    <div id="tb_<?=$_itens["idnfitem"] ?>" style="display: none;">
                        <table style="width: 100%">
                            <tr style="padding: 15px;">
                                <th>Categoria</th>
                                <th></th>
                                <th>Tipo</th>
                            </tr>
                            <tr>
                                <td style="width: 45% !important;" class="cp_grupoes">
                                    <? 
                                    $arrStatusNf = array('REPROVADO', 'CANCELADO');
                                    if(in_array($_1_u_nf_status, $arrStatusNf))
                                    {
                                        $disabled = 'disabled';
                                    }

                                    if (empty($_itens['idprodserv'])) 
                                    {
                                        ?>
                                        <input type="hidden" nomodal id="iidcontaitem<?=$_itens["idnfitem"] ?>" name="_<?=$i ?>_u_nfitem_idcontaitem" value="<?=$_itens['idcontaitem'] ?>">
                                        <select id="idcontaitem<?=$_itens["idnfitem"] ?>" name="" style="width: 100%" vnulo <?=$disabled?>>
                                            <option value=""></option>
                                            <? fillselect(NfEntradaController::buscarContaItemAtivoShare($_1_u_nf_tiponf), $_itens['idcontaitem']); ?>
                                        </select>
                                        <?
                                    } else {
                                        ?>
                                        <input type="hidden" nomodal id="iidcontaitem<?=$_itens["idnfitem"] ?>" name="_<?=$i ?>_u_nfitem_idcontaitem" value="<?=$_itens['idcontaitem'] ?>">
                                        <select id="idcontaitem<?=$_itens["idnfitem"] ?>" name="" style="width: 100%" vnulo <?=$disabled?>>
                                            <option value=""></option>
                                            <? fillselect(NfEntradaController::buscarContaItemProdservContaItem($_itens['idprodserv'], "AND c.status='ATIVO'", $_1_u_nf_tiponf, $_itens['idnfitem']), $_itens['idcontaitem']); ?>
                                        </select>
                                        <?
                                    }
                                    ?>
                                </td>
                                <td style="width: 10%;"></td>
                                <td style="width: 45%;" id="td<?=$_itens["idnfitem"] ?>" class="cp_tipo 1">                                    
                                    <input type="hidden" nomodal id="iidtipoprodserv<?=$_itens["idnfitem"] ?>" name="_<?=$i ?>_u_nfitem_idtipoprodserv" value="<?=$_itens['idtipoprodserv'] ?>">
                                    <select id="idtipoprodserv<?=$_itens["idnfitem"] ?>" name="" style="width: 100%" vnulo <?=$disabled?>>
                                        <option value=""></option>
                                        <? fillselect(NfEntradaController::listarTipoProdservCompra($_1_u_nf_status, $_itens['idcontaitem'], $_itens['idprodserv']), $_itens['idtipoprodserv']); ?>
                                    </select>                                       
                                </td>
                            </tr>
                        </table>
                    </div>
           
                    <? if (empty($_itens['idcontaitem']) || empty($_itens['idtipoprodserv'])) { ?>
                        <i class="btn fa fa-info-circle laranja" title="Categoria e/ou Subcategoria não Atribuídas" id="btn_<?=$_itens["idnfitem"] ?>" onclick="mostrarmodalgt(<?=$_itens['idnfitem'] ?>,'<?=addslashes($titulomodal)?>',<?=$i ?>)"></i>
                    <? } else { ?>
                        <i class="btn fa fa-info-circle" id="btn_<?=$_itens["idnfitem"] ?>" onclick="mostrarmodalgt(<?=$_itens['idnfitem'] ?>,'<?=addslashes($titulomodal) ?>',<?=$i ?>)"></i>
                    <? } ?>
                </td>
                <?
                if (!empty($_itens['moedaext']) and $_itens['moedaext'] != "BRL") 
                {
                    ?>
                    <td class="cp_taxaconsersao nowrap alinhar-direita">
                        <label class="alert-warning">
                            <? echo ($_itens['moedaext']); ?>
                        </label>
                        <?
                        if ($_itens['moeda'] == "BRL") 
                        {
                            ?>
                            <input vnulo <?=$readonly ?> class="alinhar-direita convmoeda-<?=$_itens['idnfitem'] ?>" style="width: 70px;" title="Câmbio BRL" placeholder="Câmbio" name="_<?=$i ?>_u_nfitem_convmoeda" type="text" value="<?=$_itens['convmoeda'] ?>">
                            <?
                        }
                        ?>
                    </td>
                    <td class="nowrap cp_valorun alinhar-direita">                
                        <input <?=$readonly ?> name="_<?=$i ?>_u_nfitem_moedaext" type="hidden" value="<?=$_itens['moedaext'] ?>">
                        <input <?=$readonly ?> <?=$disableQtd ? "readonly='readonly'" : ""; ?> class="alinhar-direita nfitem_vlritemext_<?=$_itens['idnfitem'] ?>" style="width: 70px;" name="_<?=$i ?>_u_nfitem_vlritemext" type="text" value="<?=$_itens['vlritemext'] ?>" onkeyup="setvalnfitem(this, <?=$_itens['idnfitem'] ?>)">
                    </td>
                    <?
                } elseif ($qtdConversaoMoeda > 0) {
                    ?>
                    <td></td>
                    <?
                }
                ?>                
                <td class="nowrap cp_valorun alinhar-direita">
                    <?
                    if ($_itens['vlritemext'] > 1) 
                    {
                        if ($_itens['vlritem'] < 1) {
                            $cbt = "btn-danger";
                        } else {
                            $cbt = "btn-primary ";
                        }
                    } else {
                        $cbt = "btn-success";
                    }
                    ?>
                    <button <?=$readonly ?> <?=$disableQtd ? "disabled='disabled'" : ""; ?> title="Moeda" moeda="<?=$_itens['moeda'] ?>" type="button" class="btn <?=$cbt ?>  btn-xs pointer" onclick="alteramoeda(this,<?=$_itens['idnfitem'] ?>,'<?=$_itens['moedaext'] ?>')">
                        <?=$_itens['moeda'] ?>
                    </button>
                    <? if ($_itens['moeda'] == "BRL") { ?>
                        <input <?=$readonly ?> <?=$disableQtd ? "readonly='readonly'" : ""; ?> class="alinhar-direita" style="width: 70px;" id="<?=$_itens['idnfitem'] ?>" onchange="saveRH(<?=$i?>,this)" onblur="analisaval(this)" name="_<?=$i ?>_u_nfitem_vlritem" oldvalue="<?=$_itens['vlritem'] ?>" type="text" value="<?=$_itens['vlritem'] ?>">
                    <?
                    } else {
                        ?>
                        <input <?=$readonly ?> name="_<?=$i ?>_u_nfitem_moedaext" type="hidden" value="<?=$_itens['moeda'] ?>">
                        <input <?=$readonly ?> <?=$disableQtd ? "readonly='readonly'" : ""; ?> class="alinhar-direita" style="width: 70px;" name="_<?=$i ?>_u_nfitem_vlritemext" type="text" value="<?=$_itens['vlritemext'] ?>">
                    <? } ?>
                </td>
                <td class="cp_desc">
                    <input <?=$readonly ?> <?=$disableQtd ? "readonly='readonly'" : ""; ?> class="alinhar-direita" style="width: 70px;" name="_<?=$i ?>_u_nfitem_des" class="size4" type="text" value="<?=$_itens["des"]; ?>" vdecimal>
                </td>
                <td class="cp_cfop">
                    <input <?=$readonly ?> <?=$disableQtd ? "readonly='readonly'" : ""; ?> class="alinhar-direita" style="width: 70px;" name="_<?=$i ?>_u_nfitem_cfop" size="8" type="text" value="<?=$_itens["cfop"] ?>">
                </td>
                <? if($internacional && $_1_u_nf_moedainternacional == 'Y'){ ?>
                    <td class="cp_total_imposto inserirImposto" style="text-align: center;" idnfitem="<?=$_itens["idnfitem"] ?>" indice="<?=$i ?>">
                        <a>                                                                    
                            <img src="../inc/img/lionicon.png" style="width: 15px; height: 15px;">
                        </a>
                    </td>
                <? } else { ?>
                    <td class="cp_bc alinhar-direita"><?=$_itens["basecalc"] ?></td>
                    <td class="cp_icms">
                        <input <?=$readonly ?> <?=$disableQtd ? "readonly='readonly'" : ""; ?> class="alinhar-direita" name="_<?=$i ?>_u_nfitem_aliqicms" size="5" type="text" value="<?=$_itens["aliqicms"] ?>">
                    </td>
                    <td class="cp_icmsr">
                        <input <?=$readonly ?> <?=$disableQtd ? "readonly='readonly'" : ""; ?> class="alinhar-direita" name="_<?=$i ?>_u_nfitem_valicms" size="5" type="text" value="<?=$_itens["valicms"] ?>">
                    </td>

                    <td class="cp_ipi">
                        <input <?=$readonly ?> <?=$disableQtd ? "readonly='readonly'" : ""; ?> class="alinhar-direita" name="_<?=$i ?>_u_nfitem_aliqipi" class="size5" type="text" value="<?=$_itens["aliqipi"]; ?>" vdecimal>
                    </td>
                    <td class="cp_vst">
                        <input <?=$readonly ?> <?=$disableQtd ? "readonly='readonly'" : ""; ?> class="alinhar-direita" name="_<?=$i ?>_u_nfitem_vst" class="size4" type="text" value="<?=$_itens["vst"]; ?>" vdecimal>
                    </td>
                <? } ?>

                <td class="cp_valor alinhar-direita">
                    <?
                    if ($_itens['moeda'] == "BRL") 
                    {
                        $valorTotalUnidade = ($internacional && $_1_u_nf_moedainternacional == 'Y') ? $_itens["total"] : $_itens["total"] + $_itens["valipi"];
                        ?>
                        <input <?=$readonly ?> <?=$disableQtd ? "readonly='readonly'" : ""; ?> class="alinhar-direita total-<?=$_itens["idnfitem"]?>" name="_<?=$i ?>_u_nfitem_total" class="size5" type="text" value="<?=number_format($valorTotalUnidade, 2, '.', ''); ?>" vdecimal>
                        <?
                    } else {
                        echo number_format($_itens['totalext'], 2, '.', '');
                    }
                    ?>
                </td>
                <td class="cp_lote" align="center">
                    <div id="lote_<?=$_itens["idnfitem"] ?>" style="display: none">
                        <?if($_1_u_nf_tiponf != 'R'){?>
                            <table class="table table-hover">
                                <? 
                                $figuranovo = 'fa-plus-circle verde';
                                $tqtdprod = 0;
                                $listarUnidadeObjetoItem = NfEntradaController::buscarUnidadeObjetoLoteModuloPorIdnfItem($_itens["idnfitem"], "");
                                foreach($listarUnidadeObjetoItem as $_unidadeObjetoItem) 
                                {
                                    $figuranovo = 'fa-bars cinza';

                                    if ($_unidadeObjetoItem['status'] != 'CANCELADO') {
                                        $tqtdprod = $tqtdprod + $_unidadeObjetoItem["qtdprod"];
                                        $cor = '';
                                    } else {
                                        $cor = 'background-color: #dcdcdc;opacity: 0.5;';
                                    }
                                    ?>
                                    <tr style="<?=$cor ?>" title="<?=$_unidadeObjetoItem['status'] ?>">
                                        <td>
                                            <?=number_format(tratanumero($_unidadeObjetoItem["qtdprod"]), 4, ',', '.'); ?>
                                        </td>
                                        <td><?=$_unidadeObjetoItem["unlote"] ?></td>
                                        <td>
                                            <a class=" hoverazul pointer" onclick="janelamodal('?_modulo=<?=$_unidadeObjetoItem['idobjeto'] ?>&_acao=u&idlote=<?=$_unidadeObjetoItem['idlote'] ?>');">
                                                <?=$_unidadeObjetoItem["partida"] ?>-<?=$_unidadeObjetoItem["exercicio"] ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <? 
                                }

                                if ($_itens["qtd"] > $tqtdprod) 
                                {
                                    ?>
                                    <tr class="tr_lote_<?=$_itens["idnfitem"] ?>">
                                        <td class="nowrap">
                                            <input name="#lote_idnfitem" value="<?=$_itens["idnfitem"] ?>" type="hidden">
                                            <input title="Qtd do lote a ser criado" class='quantidade<?=$_itens["idnfitem"] ?> size7' name="#lote_qtdprod" value="" type="text" class="size10">
                                            <input name="#lote_idprodserv" value="<?=$_itens["idprodserv"] ?>" type="hidden">
                                            <input name="#lote_exercicio" value="<?=date("Y") ?>" type="hidden">
                                        </td>
                                        <td> <?=$_itens["unidade"] ?></td>
                                        <td><i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer" onclick="excluitmp(this)" title="Excluir"></i></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a class="fa fa-plus-circle fa-x verde btn-lg pointer" onclick="novalinha(this,'<?=$_itens['idnfitem'] ?>')" title="Adicionar mais um Lote"></a>
                                        </td>
                                    </tr>
                                    <?
                                }
                                ?>
                            </table>
                        <?}?>
                    </div>
                    <?
                    if (($_itens["tipo"] = "PRODUTO")) // produto de venda de entrar no mesmo lote 
                    { 
                        if (!empty($_itens['idprodserv'])) 
                        {
                            //ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
                            if (!empty($_itens['idlote2'])) 
                            {
                                $listarUnidadeObjetoLote = NfEntradaController::buscarUnidadeObjetoLoteModuloPorIdnfItem("", $_itens['idlote2']);
                                $lote = $listarUnidadeObjetoLote[0];
                                ?>
                                <a class="fa fa-bars cinza fa-x  btn-lg pointer" onclick="janelamodal('?_modulo=<?=$lote['idobjeto'] ?>&_acao=u&idlote=<?=$_itens['idlote2'] ?>');" title="<?=$_itens['partida2'] ?>/<?=$_itens['exercicio2'] ?>"></a>
                                <?
                            } elseif($_itens['qtd'] > 0) {
                                if (empty($_itens["codforn"])) {
                                    $descricaolt = str_replace("'", '', $_itens["descr"]." - ".$_itens["codprodserv"]);
                                } else {
                                    $descricaolt = str_replace("'", '', $_itens["codforn"]);
                                }
                                ?>
                                <a class="fa <?=$figuranovo ?> fa-x  btn-lg pointer" onclick="gerarlotem(<?=$_itens['idnfitem'] ?>,'<?=$descricaolt ?>','<?=$_itens['qtd'] ?>','<?=$_itens['unidade'] ?>','<?=$tqtdprod ?>')" title="Novo Lote"></a>
                                <?
                            } else {
                                echo "-";
                            }
                        } else {
                            ?>
                            <a class="fa fa-pencil fa-1x btn-lg cinzaclaro hoverazul pointer" title="Relacionar Produto" onclick="relacionaprodserv(<?=$_itens['idnfitem'] ?>);"></a>
                        <?
                        }
                    } else {
                        ?>
                        <span>-</span>
                        <?
                    }
                    ?>

                    <div id="imposto_<?=$_itens["idnfitem"] ?>" style="display: none" class="panel-body">
                        <table class="table">                            
                            <tr>
                                <td><b>Imposto do Item:</b></td>
                                <td>
                                    <a href="/?_modulo=prodserv&_acao=u&_idempresa=<?=$_itens["idempresa"] ?>&idprodserv=<?=$_itens["idprodserv"] ?>" target="_blank"><?=$_itens["descr"]?></a>
                                    <input name="#imp_idnfitem" value="<?=$_itens["idnfitem"] ?>" type="hidden">
                                </td>                                                                
                            </tr>                         
                            <tr>
                                <td><b>NCM:</b></td>
                                <td><div class="div_ncm"><?=$_itens["ncm"]?></div></td>
                            </tr>     
                            <tr>
                                <td colspan="2">
                                    <table class="table planilha" style="border: 2px solid #ddd;">
                                        <thead>
                                            <tr>
                                                <th class="col-xs-2 alinhar-centro">Impostos</th>
                                                <th class="alinhar-centro">Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="alinhar-centro">II</td>
                                                <td class="alinhar-centro"><input name="#imp_impostoimportacao" class="size10 alinhar-direita" value="<?=$_itens["impostoimportacao"] ?>" type="text"></td>
                                            </tr>
                                            <tr class="bc-cinza">
                                                <td class="alinhar-centro">IPI</td>
                                                <td class="alinhar-centro"><input name="#imp_valipi" class="size10 alinhar-direita" value="<?=number_format($_itens["valipi"], 4);?>" type="text"></td>
                                            </tr>
                                            <tr>
                                                <td class="alinhar-centro">PIS</td>
                                                <td class="alinhar-centro"><input name="#imp_pis" class="size10 alinhar-direita" value="<?=number_format($_itens["pis"], 4);?>" type="text"></td>
                                            </tr>
                                            <tr class="bc-cinza">
                                                <td class="alinhar-centro">COFINS</td>
                                                <td class="alinhar-centro"><input name="#imp_cofins" class="size10 alinhar-direita" value="<?=number_format($_itens["cofins"], 4);?>" type="text"></td>
                                            </tr>
                                        </tbody>
                                        <?
                                        $totalimpostoImportacao += $_itens["impostoimportacao"];
                                        $totalvalipi += $_itens["valipi"];
                                        $totalpis += $_itens["pis"];
                                        $totalcofins += $_itens["cofins"];
                                        ?>
                                    </table>
                                </td>
                            </tr>                      
                        </table>
                    </div>
                </td>
                <td class="cp_tag">
                    <?if($_1_u_nf_tiponf != 'R')
                    {    
                        // @513502 - TAGS COM NOMENCLATURA INCORRETA NA NF
                        // adicionado join com empresa para obtenção da sigla       
                        $listaTagEmpresa = NfEntradaController::buscarTagEmpresa($_itens["idnfitem"], 'nfitem');
                        $listatag = "";

                        if ($listaTagEmpresa['qtdLinhas'] == 0 && $tag['idtagclass'] != 3) 
                        {
                            if (!empty($_itens['idprodservforn']) and !empty($_itens['valconv']) and $_itens['converteest'] == "Y") {
                                $qtdtag = round($_itens['qtd'] * $_itens['valconv']);
                            } else {
                                $qtdtag = round($_itens['qtd']);
                            }
                            ?>
                            <a class="fa fa-plus-circle fa-x verde btn-lg pointer" onclick="criaTags(<?=$qtdtag ?>,'<?=preg_replace("/'|\"/", "", $_itens['descr']); ?>',<?=$_itens['idnfitem'] ?>,'nfitem');" title="Nova Tag" id="maistag"></a>
                        <? 
                        } elseif($tag['idtagclass'] == 3){ 
                            echo '<div style="text-align: center;">-</div>';
                        } else {
                            ?>
                            <div class="cab" style="padding-top:10px;">
                                <?
                                foreach($listaTagEmpresa['dados'] AS $_tag) 
                                {
                                    // trocar para tag, pois no local não está buscando Tag, somente IDTag
                                    $listatag .= $_tag["tag"].",";

                                    // @513502 - TAGS COM NOMENCLATURA INCORRETA NA NF
                                    // adição da sigla e da mudança de layout da tag para atender aos padrões do projeto
                                    ?>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group input-group-sm">
                                            <label class="alert-warning"><?=$_tag["sigla"] ?>-<?=$_tag["tag"] ?></label>
                                            <span class="input-group-addon pointer hoverazul" onclick="janelamodal('?_modulo=tag&_acao=u&idtag=<?=$_tag['idtag']; ?>')" title="Abrir Tag">
                                                <i class="fa fa-bars pointer"></i>
                                            </span>
                                        </div>
                                    </div>
                                <? } ?>
                            </div>
                        <? }  
                    }      
                    ?>
                </td>
                <td class="cp_vincularTag">
                    <?
                    $corbotao = empty($tag['idobjeto']) ? 'fa-plus-circle verde' : 'fa-bars cinza';
                    ?>
                    <a class="fa fa-x <?=$corbotao?> btn-lg pointer" onclick="vincularTags(<?=$_itens['idnfitem']?>);" title="Vincular Tag" id="vincularTag"></a>                    
                    <div id="vinculotag_<?=$_itens["idnfitem"] ?>" style="display: none">
                        <div class="col-xs-12">
                            <div class="row m-0 d-flex align-items-right date-options">                            
                                <div class="w-100"> 
                                    <label for="" class="mb-1">Tipo Tag</label>
                                    <div class="col-xs-12 d-flex px-0">
                                        <select name="#tag_idtagclass" onchange="mostrarTagsPorTagClass('<?=$_itens['idnfitem'] ?>', this)">
                                            <option value=""></option>
                                            <? fillselect(NfEntradaController::buscarTagClass($_1_u_nf_status), $tag['idobjetoext']); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12">
                            <div class="row m-0 d-flex align-items-right date-options">                            
                                <div class="w-100"> 
                                    <label for="" class="mb-1">Selectionar Tag</label>
                                    <div class="col-xs-12 d-flex px-0">
                                        <input name="#tag_idnfitem" type="hidden" value="<?=$_itens["idnfitem"]?>">
                                        <input id="idtag_<?=$_itens["idnfitem"]?>_old" type="hidden" value="<?=$tag['idobjeto']?>">
                                        <input name="#tag_idnfitemacao" id="#tag_idnfitemacao_<?=$_itens["idnfitem"]?>" type="hidden" value="<?=$tag['idnfitemacao']?>">
                                        <select name="#tag_idtag" id="#idtag_idtag<?=$_itens["idnfitem"] ?>" style="width: 100%" vnulo <?=$disabled?>>
                                            <option value=""></option>
                                            <? 
                                            foreach(NfEntradaController::buscarTagsDisponiveisParaVinculo(cb::idempresa()) as $tags){
                                                if($tags['idtag'] == $tag['idobjeto']){
                                                    echo "<option value='{$tags['idtag']}' selected idtagclass='{$tags['idtagclass']}'>{$tags['descricao']}</option>\n";
                                                }else{
                                                    echo "<option value='{$tags['idtag']}' idtagclass='{$tags['idtagclass']}'>{$tags['descricao']}</option>\n";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 #div_categoria">
                            <div class="row m-0 d-flex align-items-right date-options">                            
                                <div class="w-100"> 
                                    <label for="" class="mb-1">Categoria</label>
                                    <div class="col-xs-12 d-flex px-0">
                                        <select name="#tag_categoria" id="categoria<?=$_itens["idnfitem"] ?>" style="width: 100%" vnulo <?=$disabled?>>
                                            <option value=""></option>
                                            <? fillselect(NfEntradaController::$_manutencao, $tag['categoria']); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 #div_km">
                            <div class="row m-0 d-flex align-items-right date-options" style="padding-bottom: 25px;">                            
                                <div class="w-100"> 
                                    <label for="" class="mb-1">KM</label>
                                    <div class="col-xs-12 d-flex px-0">
                                        <input name="#tag_km" value="<?=$tag["kmrodados"] ?>" type="text">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
                <td colspan="2" class='nowrap'>
                    <?

                    if ($_itens['tipoobjetoitem'] == 'nf' and !empty($_itens['idobjetoitem'])) 
                    {
                        $tiponf = traduzid('nf', 'idnf', 'tiponf', $_itens['idobjetoitem']);
                        switch($tiponf)
                        {
                            case 'V':
                                $link = "pedido";
                            break;
                            case in_array($tiponf, array('C', 'S', 'T', 'E', 'M', 'B', 'F')):
                                $link = "nfentrada";
                            break;
                            case 'R':
                                $link = "comprasrh";
                            break;
                            case 'D':
                                $link = "comprassocios";
                            break;
                        }
                        ?>
                        <a class="fa fa-bars pointer hoverazul" title="Nf" onclick="janelamodal('?_modulo=<?=$link ?>&_acao=u&idnf=<?=$_itens['idobjetoitem'] ?>')"></a>
                        <?
                    } elseif ($_itens['tipoobjetoitem'] == 'notafiscal' and !empty($_itens['idobjetoitem'])) {
                        ?>
                        <a class="fa fa-bars pointer hoverazul" title="NFs" onclick="janelamodal('?_modulo=nfs&_acao=u&idnotafiscal=<?=$_itens['idobjetoitem'] ?>')"></a>
                        <?
                    } else {
                        if (($_1_u_nf_status == "INICIO" or $_1_u_nf_status == "PREVISAO") && (empty($_itens['idlote']) and empty($_itens['idlote2']))) { ?>
                            <i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" onclick="excluir(<?=$_itens['idnfitem'] ?>)" Title="Excluir !"></i>
                            <?if($_GET['_modulo'] == "comprasrh" && empty($_itens['idprodserv'])){?>
                                <button class="btn btn-xs btn-success" onclick="salvarNovoItem(this)">Salvar</button>
                            <?}?>
                        <? 
                        }
                    }
                    ?>
                </td>
            </tr>
            <?
            if ($_itens['venda'] == 'Y') // devolução de um item que saiu pelo pedido
            { 
                $listarLoteNfItem = NfEntradaController::buscarLoteNfItem($_itens['idnfitem']);
                echo '<!-- lotes: '.$listarLoteNfItem['sql'].'-->';
                $ii = $i;
                if ($listarLoteNfItem['qtdLinhas'] > 0) 
                {
                ?>
                    <tr idnfitem="<?=$_itens["idnfitem"] ?>">
                        <td class="estoque" colspan="50">
                            <div>
                                <ul class="ulitens">
                                    <li class="liitens">
                                        <div>
                                            <div class="row">
                                                <div class="col-md-1 bold">QTD</div>
                                                <div class="col-md-1 bold">Estoque</div>
                                                <div class="col-md-2 bold">Lote (Unidade)</div>
                                                <div class="col-md-1 bold">Status</div>
                                            </div>
                                            <? 
                                            foreach($listarLoteNfItem['dados'] as $nfItem) 
                                            {
                                                $i = $i + 1;
                                                $loteconsarr = NfEntradaController::buscarConsumoLoteconsPorIdLoteEIdLoteFracao($_itens["idnfitem"], 'nfitem', $nfItem["idlotefracao"], $nfItem["idlote"]);
                                                $lotecons= $loteconsarr['dados'];
                                                if (empty($lotecons['id'])) {
                                                    $lcacao = "i";
                                                } else {
                                                    $lcacao = "u";
                                                }

                                                if ($lotecons["qtdc"] == $_itens["qtd"]) {
                                                    $bgcolor = "#bef69c";
                                                } elseif ($lotecons["qtdc"] > $_itens["qtd"]) {
                                                    $bgcolor = "#fff57a";
                                                } else {
                                                    $bgcolor = "";
                                                }
                                                ?>
                                                <div class="row" style="background-color: <?=$bgcolor ?>">
                                                    <div class="col-md-1">
                                                        <input class="size5" style="height: 22px;" valor="<?=$lotecons["qtdc"] ?>" type="text" name="_<?=$i ?>_<?=$lcacao ?>_lotecons_qtdc" value="<?=$lotecons["qtdc"] ?>" onchange="verificavalor(this)">
                                                        <input type="hidden" name="_<?=$i ?>_<?=$lcacao ?>_lotecons_idlotecons" value="<?=$lotecons["id"] ?>">
                                                        <input type="hidden" name="_<?=$i ?>_<?=$lcacao ?>_lotecons_tipoobjeto" value="nfitem">
                                                        <input type="hidden" name="_<?=$i ?>_<?=$lcacao ?>_lotecons_idobjeto" value="<?=$nfItem["idnfitem"] ?>">
                                                        <input type="hidden" name="_<?=$i ?>_<?=$lcacao ?>_lotecons_idlote" value="<?=$nfItem["idlote"] ?>">
                                                        <input type="hidden" name="_<?=$i ?>_<?=$lcacao ?>_lotecons_idlotefracao" value="<?=$nfItem["idlotefracao"] ?>">
                                                    </div>

                                                    <div class="col-md-1" title="Estoque= Estoque + QTD">
                                                        <?
                                                        echo $nfItem["qtddisp"];

                                                        ?>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <?
                                                        $_formalizacao = NfEntradaController::buscarFormalizacaoPorIdLote($nfItem["idlote"]);
                                                        $qtdresFromalizacao = count($_formalizacao);
                                                        
                                                        if ($nfItem["status"] != "APROVADO" and $nfItem["status"] != "ESGOTADO" and $qtdresFromalizacao > 0) { ?>
                                                            <a title="Ordem de produção" class="pointer" onclick="janelamodal('?_modulo=formalizacao&_acao=u&idformalizacao=<?=$_formalizacao['idformalizacao'] ?>')"><?=$nfItem["partida"] ?></a>
                                                        <? } else { ?>
                                                            <a title="Lote" class="pointer" onclick="janelamodal('?_modulo=<?=$nfItem["idobjeto"] ?>&_acao=u&idlote=<?=$nfItem["idlote"] ?>')"><?=$nfItem["partida"] ?></a>
                                                        <? }

                                                        echo (" (".$nfItem["unidade"].")"); ?>

                                                        <a class="fa fa-search azul pointer hoverazul" title="Histórico" onclick="consumo(<?=$nfItem['idlote'] ?>);"></a>
                                                    </div>
                                                    <div class="col-md-1 nowrap">
                                                        <a href="?_modulo=ao&_acao=u&idlote=<?=$nfItem["idlote"] ?>" target="_blank" style="color: inherit;">
                                                            <?
                                                            //Caso não tenha sido criado na formalização pegará o stauts do Lote
                                                            if ($nfItem["status"] != "APROVADO" and $nfItem["status"] != "ESGOTADO" and $qtdresFromalizacao > 0) {
                                                                $nfItem["status"] = $_formalizacao['status'];
                                                            } else {
                                                                $nfItem["status"] == 'AGUARDANDO' ? 'AGUARDANDO AUT.' : $nfItem["status"];
                                                            }

                                                            echo $nfItem["status"];
                                                            ?>
                                                        </a>
                                                    </div>

                                                </div>
                                                <div id="consumo<?=$nfItem["idlote"] ?>" style="display: none">
                                                    <?=$this->historicolotecons($nfItem["idlote"]); ?>
                                                </div>
                                            <?
                                            } 
                                            ?>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?
                }
            } //if($_itens['venda']==Y)

            $ncmOld = $_itens["ncm"];
        }
        ?>
        <tr>
            <td>
                <div id="imposto_total" style="display: none" class="panel-body">                                                   
                    <table class="table planilha" style="border: 2px solid #ddd;">
                        <thead>
                            <tr>
                                <th class="col-xs-3 alinhar-centro">Impostos</th>
                                <th class="alinhar-centro">Recolhido</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="alinhar-centro">SISCOMEX</td>
                                <td class="alinhar-direita"><?=number_format(str_replace(',', '.', str_replace('.', '', $_1_u_nf_siscomex)), 4, ',', '.')?></td>
                            </tr>
                            <tr class="bc-cinza">
                                <td class="alinhar-centro">IPI</td>
                                <td class="alinhar-direita"><?=number_format($totalvalipi, 4, ',', '.')?></td>
                            </tr>
                            <tr>
                                <td class="alinhar-centro">PIS</td>
                                <td class="alinhar-direita"><?=number_format($totalpis, 4, ',', '.')?></td>
                            </tr>
                            <tr class="bc-cinza">
                                <td class="alinhar-centro">COFINS</td>
                                <td class="alinhar-direita"><?=number_format($totalcofins, 4, ',', '.')?></td>
                            </tr>
                            <tr>
                                <td class="alinhar-centro">ICMS</td>
                                <td class="alinhar-direita"><?=number_format(str_replace(',', '.', str_replace('.', '', $_1_u_nf_icms)), 2, ',', '.')?></td>
                            </tr>
                            <tr>
                                <td class="alinhar-centro">II</td>
                                <td class="alinhar-direita"><?=number_format($totalimpostoImportacao, 4, ',', '.')?></td> 
                            </tr>
                            <tr style="border-top: 2px solid #ddd;">
                                <td class="alinhar-centro"><b>Total Impostos</b></td>
                                <td class="alinhar-direita"><b><?=number_format($totalimpostoImportacao + $_1_u_nf_siscomex + $totalvalipi + $totalpis + $totalcofins + $_1_u_nf_icms, 4, ',', '.')?></b></td> 
                            </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
        <?
    }

    // retorna o historico dos consumos do lote total ou por lotefracao
    //lote.php - prodserv.php - pedido.php
    function historicolotecons($idlote, $idunidade = null)
    {
        $qtdd = 0;
        $qtdc = 0;
        

        $consumoLote = NfEntradaController::buscarLotePorIdLote($idlote);
        $unpdrado = $consumoLote['campo'];
        ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <table class="table table-striped planilha">
                            <?
                            $strun = "";
                            if (!empty($idunidade)) 
                            {
                                $strun = " AND f.idunidade=".$idunidade." ";
                                $idtipounidade = traduzid('unidade', 'idunidade', 'idtipounidade', $idunidade);

                                // producao tipo 5 almf tipo 3
                                if ($idtipounidade == 3) 
                                { 
                                    $loteFracao = NfEntradaController::buscarLoteFracaoPorIdloteEIdUnidade($idlote, $idunidade);
                                    $qtdLoteFracao = count($loteFracao);
                                    $qtdv3=$qtdLoteFracao;
                                    if ($qtdLoteFracao > 0) 
                                    {
                                        $unidade = traduzid('unidade', 'idunidade', 'unidade', $idunidade);
                                        if (!empty($loteFracao['idlotefracaoorigem'])) 
                                        {
                                            if (!empty($loteFracao['idformalizacao'])) {
                                                $icone = '<i class="fa fa-bars pointer" style="color: #337ab7"  onclick="janelamodal(\'?_modulo=formalizacao&_acao=u&idformalizacao='.$loteFracao['idformalizacao'].'\');">';
                                            } else {
                                                $icone = '';
                                            }
                                            $origem = $loteFracao['unidade'] . $icone;
                                        } elseif (!empty($loteFracao['idnfitem'])) {
                                            $origem = 'Compras <i class="fa fa-bars pointer" style="color: #337ab7"  onclick="janelamodal(\'?_modulo=nfentrada&_acao=u&idnf='.$loteFracao['idnf'].'\');"></i>';
                                        } else {
                                            $modulo = NfEntradaController::buscarUnidadeObjetoPorTipoObjetoEIdUnidade($loteFracao['idunidadelote'], 'modulo', 'lote');
                                            $origem = $loteFracao['partida']."/".$loteFracao['exercicio'].' <i class="fa fa-bars pointer" style="color: #337ab7"  onclick="janelamodal(\'?_modulo='.$modulo['idobjeto'].'&_acao=u&idlote='.$idlote.'\');"></i>';
                                        }
                                        if(strpos(strtolower($loteFracao['qtdini_exp']), "d") || strpos(strtolower($loteFracao['qtdini_exp']), "e")) 
                                        {
                                            $valor = recuperaExpoente(tratanumero($loteFracao["qtdini"]), $loteFracao['qtdini_exp']);
                                            $qtdc = $qtdc + $valor;
                                        } else {
                                            $valor = number_format(tratanumero($loteFracao["qtdini"]), 2, ',', '.');
                                            $qtdc = $qtdc + $valor;
                                        }

                                        $tr = "<tr>
                                                <td nowrap>".$origem."</td>
                                                <td>".$unidade."</td>	
                                                <td align='right'>".$valor."</td>
                                                <td></td>";
                                        if (!empty($unpdrado)) {
                                            $tr .= "<td>".$unpdrado."</td>";
                                        }
                                        $tr .= "<td></td>
                                                <td>".$loteFracao['criadopor']."</td>
                                                <td>".dmahms($loteFracao['criadoem'])."</td>
                                                <td></td>
                                             </tr>";
                                            
                                    }
                                } else {
                                    $listarFracao = NfEntradaController::buscarFracaoPorLoteEUnidade($idlote, $idunidade);
                                    echo ('<!--'.$listarFracao['sql'].'-->');
                                    $qtdv3 = $listarFracao['qtdLinhas'];
                                    $o_idunidade = $idunidade;
                                    if ($qtdv3 > 0) 
                                    {
                                        $fracao = $listarFracao['dados'];
                                        if (!empty($fracao['idunidadelt'])) {
                                            $_idunidade = $fracao['idunidadelt'];
                                            $o_idunidade = $fracao['idunidade'];
                                        } else {
                                            $_idunidade = $o_idunidade;
                                        }
                                        $_idtipounidade = traduzid('unidade', 'idunidade', 'idtipounidade', $_idunidade);
                                        $unidade = traduzid('unidade', 'idunidade', 'unidade', $o_idunidade);
                                        $listarUnidadeObjeto = NfEntradaController::buscarUnidadeObjetoPorTipoObjetoEIdUnidade($o_idunidade, 'lote', 'modulo');    

                                        if ($_idtipounidade == 8 and !empty($fracao['idformalizacao'])) {
                                            $origem = 'Meios';
                                            if (!empty($fracao['idformalizacao'])) {
                                                $icone = ' <i class="fa fa-bars pointer" style="color: #337ab7"  onclick="janelamodal(\'?_modulo=formalizacao&_acao=u&idformalizacao='.$fracao['idformalizacao'].'\');">';
                                            } else {
                                                $icone = '';
                                            }
                                            $origem .= $icone;
                                        } elseif ($_idtipounidade == 5) {
                                            if (empty($fracao['idlotefracaoorigem'])) {
                                                $origem = 'Produção';
                                                if (!empty($fracao['idformalizacao'])) {
                                                    $icone = ' <i class="fa fa-bars pointer" style="color: #337ab7"  onclick="janelamodal(\'?_modulo=formalizacao&_acao=u&idformalizacao='.$fracao['idformalizacao'].'\');">';
                                                } else {
                                                    $icone = '';
                                                }
                                            } else {
                                                $rowo = NfEntradaController::buscarFracaoPorIdLoteFracao($fracao['idlotefracaoorigem']);
                                                $o_idunidade = $rowo['dados']['idunidade'];
                                                $origem = traduzid('unidade', 'idunidade', 'unidade', $o_idunidade);
                                                $listarUnidadeObjeto = NfEntradaController::buscarUnidadeObjetoPorTipoObjetoEIdUnidade($o_idunidade, 'lote', 'modulo');
                                                if (!empty($listarUnidadeObjeto['idobjeto'])) {
                                                    $icone = ' <i class="fa fa-bars pointer" style="color: #337ab7"  onclick="janelamodal(\'?_modulo='.$listarUnidadeObjeto[0]['idobjeto'].'&_acao=u&idlote='.$idlote.'\');"></i>';
                                                } else {
                                                    $icone = '';
                                                }
                                            }
                                            $origem .= $icone;
                                        } else {
                                            $origem = 'Deslocamento';
                                        }
                                        if (strpos(strtolower($fracao['qtdini_exp']), "d") || strpos(strtolower($fracao['qtdini_exp']), "e")) {
                                            $valor = recuperaExpoente(tratanumero($fracao["qtdini"]), $fracao['qtdini_exp']);
                                            $qtdc = $qtdc + $valor;
                                        } else {
                                            $valor = number_format(tratanumero($fracao["qtdini"]), 2, ',', '.');
                                            $qtdc = $qtdc + $valor;
                                        }

                                        $tr = "<tr>
                                                <td nowrap>".$origem."</td>
                                                <td>".$unidade."</td>		
                                                <td align='right'>".$valor."</td>
                                                <td></td>";
                                        if (!empty($unpdrado)) {
                                            $tr .= "<td>".$unpdrado."</td>";
                                        }
                                        $tr .= "<td></td>
                                                <td>".$fracao['criadopor']."</td>
                                                <td>".dmahms($fracao['criadoem'])."</td>
                                                <td></td>
                                            </tr>";
                                    }
                                }
                            }
  
                            $listarConsumo = NfEntradaController::buscarConsumoEUnidade($idlote, $strun);
                            echo ('<!--'.$listarConsumo['sql'].'-->');
                            $qtdv2 = $listarConsumo['qtdLinhas'];
                            if ($qtdv2 > 0 || $qtdv3 > 0) 
                            {
                                $mostroucab = 'Y';
                                ?>
                                <tr>
                                    <th>Origem</th>
                                    <th>Destino</th>
                                    <th style="text-align: right !important;">Crédito</th>
                                    <th style="text-align: right !important;">Débito</th>
                                    <? if (!empty($unpdrado)) { ?>
                                        <th>Un</th>
                                    <? } ?>
                                    <th>Obs</th>
                                    <th>Por</th>
                                    <th>Em</th>
                                    <th></th>
                                </tr>
                                <?
                                echo ($tr);
                                foreach($listarConsumo['dados'] as $consumo)
                                {
                                    $qtdd = $consumo["qtdd"] + $qtdd;
                                    $qtdc = $consumo["qtdc"] + $qtdc;
                                    if (empty($consumo['obs'])) {
                                        $consumo['obs'] == "Correção";
                                    }

                                    $searchTrans = 'Transferência na solicitacão de materiais';
                                    if (preg_match("/{$searchTrans}/i", $consumo['obs']) || $consumo['obs'] == "Lote Fracionado.") 
                                    {
                                        $ress = NfEntradaController::buscarSolMatItemPorIdSomatItem($consumo['idobjetoconsumoespec']);
                                        if ($ress['qtdLinhas'] > 0) 
                                        {
                                            $rows = $ress['dados'][0];
                                            if (!empty($rows['idsolmat']) and $consumo['obs'] != null) 
                                            {
                                                $consumo['obs'] = 'solmat='.$rows['idsolmat'];
                                                $obs1 = $consumo['obs'];
                                            }
                                        }
                                    }
                                    if ($consumo['tipoobjeto'] == 'lote') {
                                        $destino = $consumo['partida'].'/'.$consumo['exercicio'];
                                    } else {
                                        $destino = $consumo['destino'];
                                    }

                                    //se o a unidade do modulo não tiver lote
                                    if (empty($consumo['idobjeto'])) 
                                    {
                                        $rowmd = NfEntradaController::buscarUnidadeObjetoPorModuloTipoEIdUnidadeEReady('lote', 'modulo', 8);
                                        $row_idobjeto = $rowmd['idobjeto'];
                                    } else {
                                        $row_idobjeto = $consumo['idobjeto'];
                                    }

                                    if (!in_array($consumo["status"], ['ABERTO', 'PENDENTE','ALIQUOTA'])) {
                                        $cor = 'background-color: #dcdcdc;opacity: 0.5;';
                                        $title = 'INATIVO';
                                    } else {
                                        $title = 'ATIVO';
                                        $cor = 'background-color:';
                                    }
                                    ?>
                                    <tr style='<?=$cor ?>' title="<?=$title ?>">
                                        <? //DEBITO E O CONTRARIO DE CREDITO ORIGEM/DESTINO
                                        if ($consumo["qtdd"] > 0) 
                                        {
                                            $rlink = NfEntradaController::buscarUnidadeObjetoPorTipoObjetoEIdUnidade($consumo['idunidade'], 'modulo', 'lote');
                                            if ($consumo['tipoobjeto'] == 'lote') {
                                                $icone = '';
                                            } else {
                                                $icone = ' <i class="fa fa-bars pointer" style="color: #337ab7"  onclick="janelamodal(\'?_modulo='.$rlink['idobjeto'].'&_acao=u&idlote='.$consumo['idlote'].'\');"></i>';
                                            }
                                            ?>
                                            <td nowrap><?=$consumo['origem'] . $icone ?></td>
                                            <? if (!empty($consumo['idobjeto']) and $consumo['tipoobjeto'] == 'lote') { ?>
                                                <td onclick="janelamodal('?_modulo=<?=$row_idobjeto ?>&_acao=u&idlote=<?=$consumo['idlote'] ?>');" style="cursor: pointer;">
                                                    <font color="blue">
                                            <? } else { ?>
                                                <td>
                                                    <font>
                                            <? } ?>
                                                    <?=$destino ?> </font>
                                                </td>
                                        <? } else {
                                            if (!empty($consumo['idobjeto']) and $consumo['tipoobjeto'] == 'lote') 
                                            { 
                                                ?>
                                                <td onclick="janelamodal('?_modulo=<?=$consumo['idobjeto'] ?>&_acao=u&idlote=<?=$consumo['idlote'] ?>');" style="cursor: pointer;">
                                                    <font color="blue">
                                            <? } else { ?>
                                                <td>
                                                    <font>
                                            <? } ?>
                                            <?=$destino?> </font>
                                            </td>
                                            <td><?=$consumo['origem'] ?></td>
                                        <? } ?>
                                        <td align="right">
                                            <?
                                            if ($consumo["qtdc"] > 0) 
                                            {
                                                if (strpos(strtolower($consumo['qtdc_exp']), "d") || strpos(strtolower($consumo['qtdc_exp']), "e")) {
                                                    echo recuperaExpoente(tratanumero($consumo["qtdc"]), $consumo['qtdc_exp']);
                                                } else {
                                                    echo number_format(tratanumero($consumo["qtdc"]), 2, ',', '.');
                                                }
                                            } elseif ($consumo["qtdsol"] > 0) {
                                                if (strpos(strtolower($consumo['qtdsol_exp']), "d") || strpos(strtolower($consumo['qtdsol_exp']), "e")) {
                                                    echo recuperaExpoente(tratanumero($consumo["qtdsol"]), $consumo['qtdsol_exp']);
                                                } else {
                                                    echo number_format(tratanumero($consumo["qtdsol"]), 2, ',', '.');
                                                }
                                            } else {
                                                echo "";
                                            }
                                            ?>
                                        </td>
                                        <td align="right">
                                            <?
                                            if ($consumo["qtdd"] > 0) {
                                                if (strpos(strtolower($consumo['qtdd_exp']), "d") || strpos(strtolower($consumo['qtdd_exp']), "e")) {
                                                    echo recuperaExpoente(tratanumero($consumo["qtdd"]), $consumo['qtdd_exp']);
                                                } else {
                                                    echo number_format(tratanumero($consumo["qtdd"]), 2, ',', '.');
                                                }
                                            } else {
                                                echo "";
                                            }
                                            ?>
                                        </td>
                                        <? if (!empty($unpdrado)) { ?>
                                            <td><?=$unpdrado ?></td>
                                        <? } ?>
                                        <td <? if (!empty($rows['idsolmat']) && $consumo['obs'] == $obs1) { ?> style="color: #337ab7;text-decoration: none;cursor: pointer;" onclick="janelamodal('?_modulo=solmat&_acao=u&idsolmat=<?=$rows['idsolmat'] ?>')" <? } ?>>
                                            <?=$consumo['obs'] ?>
                                        </td>
                                        <td><?=$consumo['criadopor'] ?></td>
                                        <td><?= dmahms($consumo['criadoem']) ?></td>
                                        <td>
                                            <?
                                            if (!empty($consumo['idtransacao']) and $consumo["status"] == 'ABERTO') 
                                            {

                                                $obj = array();
                                                $verifica = NfEntradaController::verficarSePodeExcluirConsumo($consumo['idtransacao']);
                                                if(!$verifica){
                                                    $func = "alert('Não é possivel excluir este consumo, o lote possui consumos após a transferência')";
                                                    if(array_key_exists("retornaestoque", getModsUsr("MODULOS")) && ($consumo['consumetransfdestino'] == "Y" || $consumo['consometransf'] == "Y")){
                                                        $rowgs = NfEntradaController::buscarGrupoLoteConsPorIdTransacao($consumo['idtransacao']);
                                                        $obj['ids'] = explode(',',$rowgs['ids']);
                                                        if (!empty($rowgs['idlotefracao'])) {
                                                            $obj['idlotefracao'] = $rowgs['idlotefracao'];
                                                        }
                                                        $obj = json_encode($obj);
                                                        $func = 'excluirloteconstransacao('.$consumo["idtransacao"].')';
                                                    }
                                                }else{
                                                    $rowgs = NfEntradaController::buscarGrupoLoteConsPorIdTransacao($consumo['idtransacao']);
                                                    $obj['ids'] = explode(',',$rowgs['ids']);
                                                    if (!empty($rowgs['idlotefracao'])) {
                                                        $obj['idlotefracao'] = $rowgs['idlotefracao'];
                                                    }
                                                    $obj = json_encode($obj);
                                                    $func = 'excluirloteconstransacao('.$consumo["idtransacao"].')';
                                                }
                                                ?>
                                                <textarea id="excluirLoteconsTransacao<?=$consumo['idtransacao']?>" class="hidden" rows="10"><?=$obj?></textarea>
                                                <i class="fa fa-trash cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="<?=$func?>" title="Excluir"></i>
                                            <? } elseif (array_key_exists("ajustelote", getModsUsr("MODULOS"))) { ?>
                                                <i class="fa fa-trash cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="excluirlotecons(<?=$consumo['idlotecons'] ?>)" title="Excluir consumo"></i>
                                            <? } ?>
                                        </td>                                    
                                    </tr>
                                    <?
                                }
                            } //if($qtdv2>0){
                            ?>                            
                            <tr>
                                <td colspan="9">
                                    <hr>
                                </td>
                            </tr>

                            <?
                            $listarConsumoPorLoteFracao = NfEntradaController::buscarConsumoPorLoteFracaoETipoObjeto($idlote, $strun);
                            $qtdv = $listarConsumoPorLoteFracao['qtdLinhas'];
                            if ($qtdv > 0) 
                            {
                                if ($mostroucab != 'Y') 
                                {
                                    $mostroucab = 'Y';
                                    ?>
                                    <tr>
                                        <th>Origem</th>
                                        <th>Destino</th>
                                        <th>Crédito</th>
                                        <th>Débito</th>
                                        <? if (!empty($unpdrado)) { ?>
                                            <th>Un</th>
                                        <? } ?>
                                        <th>Obs</th>
                                        <th>Por</th>
                                        <th>Em</th>
                                        <th></th>
                                    </tr>
                                    <?
                                    echo ($tr);
                                }
                                
                                foreach($listarConsumoPorLoteFracao['dados'] as $consumoLote)
                                {
                                    $qtdd = $consumoLote["qtdd"] + $qtdd;
                                    $qtdc = $consumoLote["qtdc"] + $qtdc;
                                    if ($consumoLote['tipoobjeto'] == 'nfitem') 
                                    {
                                        $_res = NfEntradaController::buscarNnfePorIdNfItem($consumoLote['idobjeto']);
                                        $_numrows = $_res['qtdLinhas'];

                                        $_row = $_res['dados'];
                                        if (!empty($_row["nnfe"])) 
                                        {
                                            $destino = $_row['nome']." NFe=".$_row['nnfe'];
                                            $cortr = "";
                                        } else {
                                            $destino = $_row['nome']." Pedido=".$_row['idnf'];
                                            $cortr = '#ffff7b';
                                        }

                                        $id = $_row['idnf'];
                                        $tab = 'pedido';
                                        $title = "NFe";
                                        $obs = "Venda";
                                    } elseif ($consumoLote['tipoobjeto'] == 'resultado') {
                                        /** Acrescentar Mostrar o Consumo do Lote Tarefa nº 294246 em 08/01/2020 - Lidiane */
                                        $listarAmostra = NfEntradaController::buscarAmostraPorIdResultado($consumoLote['idobjeto']);
                                        $_numrows = $listarAmostra['qtdLinhas'];
                                        $_row = $listarAmostra['dados'];
                                        $destino = $_row['descr'];
                                        $id = $_row['idlote'];
                                        $tab = 'lote';
                                        $title = "Lote";
                                        $obs = "";
                                    }

                                    if ($_numrows > 0) 
                                    { //vendas
                                        ?>
                                        <tr style="background: <?=$cortr ?>;">
                                            <td><?=$consumoLote['unidade'] ?></td>
                                            <td class="">
                                                <? /* Valida se o Tipo Objeto é Resultado, caso seja, insere o link no Resultado */
                                                if ($consumoLote['tipoobjeto'] == 'resultado') 
                                                {
                                                    //Recuperar o modulo de resultados associado conforme a unidade
                                                    $modResultadosPadrao = getModuloResultadoPadrao($_row['idunidade']);
                                                    ?>
                                                    <a href="#" onclick="janelamodal('?_modulo=<?php echo $modResultadosPadrao ?>&_acao=u&idresultado=<?php echo $consumoLote['idobjeto']; ?>&_idempresa=<?=$consumoLote['idempresa']?>')"><?=$destino ?></a>

                                                <? } elseif ($consumoLote['tipoobjeto'] == 'nfitem') { ?>

                                                    <a href="#" onclick="janelamodal('?_modulo=pedido&_acao=u&idnf=<? echo $id; ?>')"><?=$destino ?></a>

                                                <?
                                                } else { ?>
                                                    <?=$destino ?>
                                                <? } ?>
                                            </td>

                                            <td align="right">
                                                <?
                                                if ($consumoLote['qtdc'] > 0) {
                                                    if (strpos(strtolower($consumoLote['qtdc_exp']), "d") || strpos(strtolower($consumoLote['qtdc_exp']), "e")) {
                                                        echo recuperaExpoente(tratanumero($consumoLote["qtdc"]), $consumoLote['qtdc_exp']);
                                                    } else {
                                                        echo number_format(tratanumero($consumoLote["qtdc"]), 2, ',', '.');
                                                    }
                                                } else {
                                                    echo "";
                                                }
                                                ?>
                                            </td>
                                            <td align="right">
                                                <?
                                                if ($consumoLote['qtdd'] > 0) {
                                                    if (strpos(strtolower($consumoLote['qtdd_exp']), "d") || strpos(strtolower($consumoLote['qtdd_exp']), "e")) 
                                                    {
                                                        echo recuperaExpoente(tratanumero($consumoLote["qtdd"]), $consumoLote['qtdd_exp']);
                                                    } else {
                                                        echo number_format(tratanumero($consumoLote["qtdd"]), 2, ',', '.');
                                                    }
                                                } else {
                                                    echo "";
                                                }
                                                ?>
                                            </td>
                                            <? if (!empty($unpdrado)) { ?>
                                                <td><?=$unpdrado ?></td>
                                            <? } ?>
                                            <td><?=$obs ?></td>
                                            <td><?=$consumoLote['criadopor'] ?></td>
                                            <td><?= dmahms($consumoLote['criadoem']) ?></td>
                                        </tr>
                                    <?
                                    }
                                } //while($row=mysqli_fetch_assoc($res)){
                            } //if($qtdv>0){

                            $listarReservaLote = NfEntradaController::buscarReservaLotePorNfEUnidade('nfitem', $idlote);
                            $qtdr = $listarReservaLote['qtdLinhas'];
                            if ($qtdr > 0) 
                            {
                                if ($mostroucab != 'Y') { ?>
                                    <tr>
                                        <th>Origem</th>
                                        <th>Destino</th>
                                        <th>Crédito</th>
                                        <th>Débito</th>
                                        <? if (!empty($unpdrado)) { ?>
                                            <th>Un</th>
                                        <? } ?>
                                        <th>Obs</th>
                                        <th>Por</th>
                                        <th>Em</th>
                                        <th></th>
                                    </tr>
                                <? }

                                foreach ($listarReservaLote['dados'] as $reserva) 
                                {
                                    $qtdd = $reserva["qtdd"] + $qtdd;
                                    $qtdc = $reserva["qtdc"] + $qtdc;
                                    ?>

                                    <tr style="background:#ffff7b;">
                                        <td><?=$reserva["unidade"] ?></td>
                                        <td>
                                            <a href="#" onclick="janelamodal('?_modulo=pedido&_acao=u&idnf=<?=$reserva['idnf'] ?>')">
                                                <?
                                                if (!empty($reserva["nnfe"])) {
                                                    echo $reserva['nome']." NFe=".$reserva['nnfe'];
                                                } else {
                                                    echo $reserva['nome']." Pedido=".$reserva['idnf'];
                                                }
                                                ?>
                                            </a>
                                        </td>
                                        <td align="right"></td>
                                        <td align="right">
                                            <?
                                            if ($reserva['qtd'] > 0) {
                                                if (strpos(strtolower($reserva['qtd_exp']), "d") || strpos(strtolower($reserva['qtd_exp']), "e")) {
                                                    echo recuperaExpoente(tratanumero($reserva["qtd"]), $reserva['qtd_exp']);
                                                } else {
                                                    echo number_format(tratanumero($reserva["qtd"]), 2, ',', '.');
                                                }
                                            } else {
                                                echo "";
                                            }
                                            ?>
                                        </td>
                                        <? if (!empty($unpdrado)) { ?>
                                            <td><?=$unpdrado ?></td>
                                        <? } ?>
                                        <td>Reserva</td>
                                        <td><?=$reserva["criadopor"] ?></td>
                                        <td><?= dmahms($reserva["criadoem"]) ?></td>
                                    </tr>

                                <? }
                            }

                            if (!empty($idlote) and !empty($idunidade)) 
                            {
                                $listarSomaLoteFracao = NfEntradaController::buscarSomasLoteFracao($idlote, $idunidade);
                                $qtddeb = $listarSomaLoteFracao['qtdLinhas'];
                                if ($qtddeb > 0) 
                                {
                                    $rowd = $listarSomaLoteFracao['dados'][0];
                                    ?>
                                    <tr>
                                        <td><? echo ("<!--".$listarSomaLoteFracao['sql']." -->"); ?></td>
                                        <td></td>
                                        <td style="background-color: #3fff0052;" title="Crédito" align="right">
                                            <?
                                            if (strpos(strtolower($rowd['qtdprod_exp']), "d") || strpos(strtolower($rowd['qtdprod_exp']), "e")) {
                                                echo recuperaExpoente(tratanumero($rowd["qtdc"]), $rowd['qtdprod_exp']);
                                            } else {
                                                echo number_format(tratanumero($rowd["qtdc"]), 2, ',', '.');
                                            }
                                            ?>
                                        </td>
                                        <td style="background-color: #ff000052;" title="Débito" align="right">
                                            <?
                                            if (strpos(strtolower($rowd['qtdprod_exp']), "d") || strpos(strtolower($rowd['qtdprod_exp']), "e")) {
                                                echo recuperaExpoente(tratanumero($rowd["qtdd"]), $rowd['qtdprod_exp']);
                                            } else {
                                                echo number_format(tratanumero($rowd["qtdd"]), 2, ',', '.');
                                            }
                                            ?>
                                        </td>
                                        <td colspan="4">
                                            <? if ($rowd['qtdd'] > $rowd['qtdc']) 
                                            {
                                                if (strpos(strtolower($rowd['qtdprod_exp']), "d") || strpos(strtolower($rowd['qtdprod_exp']), "e")) {
                                                    $dif = recuperaExpoente(tratanumero($rowd["qtddif"]), $rowd['qtdprod_exp']);
                                                } else {
                                                    $dif = number_format(tratanumero($rowd["qtddif"]), 2, ',', '.');
                                                }
                                                ?>
                                                <i title="Valor de <?=$dif ?> maior que o crédito " class="fa fa-exclamation-triangle laranja btn-lg pointer"></i>
                                                <spam style="color:red"> Valor de <?=$dif ?> maior que o crédito.</spam>
                                            <? } ?>
                                        </td>
                                        <td></td>
                                    </tr>
                                <?
                                }
                            }
                            ?>

                            <?
                            if ($qtdv2 == 0 && $qtdv == 0 && $qtdr == 0) 
                            {
                                ?>
                                <tr>
                                    <td>Este Lote não possui consumo.</td>
                                </tr>
                                <?
                            }
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?
    } //function historicolotecons($idlote,$idunidade=null){

    function getMediadiaria($idprodserv, $idunidadeest, $idprodservformula = null, $consumodias = 60)
    {

        //trazer o valor de conversão
        $valconv = traduzid('prodserv', 'idprodserv', 'valconv', $idprodserv);

        ($valconv == 0) ? $auxconv = 1 : $auxconv = $valconv;
        //iniciar variaveis de total de calculo
        $tqtdd = 0;
        $tqtdc = 0;
        if (!empty($idprodservformula)) {
            $in_str = " and l.idprodservformula=".$idprodservformula;
        }
        //pegar os o consumo
        $_sqlaux0 = "select 
                            c.idlotefracao, c.qtdd, c.qtdc
                        from 
                            lotefracao lf 
                        join
                            lote l on (lf.idlote = l.idlote)
                        join 
                            lotecons c on (lf.idlote = c.idlote and (c.qtdd>0 or c.qtdc>0) and c.idlotefracao=lf.idlotefracao and c.status='ABERTO')
                        where lf.idunidade = ".$idunidadeest."
                            ".$in_str."
                        and l.idprodserv = ".$idprodserv." 
                        and l.status not in ('CANCELADO','CANCELADA')
                        and c.criadoem > DATE_SUB(now(), INTERVAL ".$consumodias." DAY)";
        $_resaux0 = d::b()->query($_sqlaux0) or die("Erro ao consultar histórico de consumo do produto:".mysqli_error(d::b()));
        if ($_resaux0->num_rows > 0) {
            while ($_rowaux0 = mysqli_fetch_assoc($_resaux0)) {
                /*$sqlu = "SELECT u.convestoque 
                            from lotefracao l 
                            join unidade u on (l.idunidade = u.idunidade) 
                            where l.idlotefracao = ".$_rowaux0["idlotefracao"];
                    $resu = d::b()->query($sqlu) or die("Erro ao consultar idlotefracao:".mysqli_error(d::b()));
                    $rowu = mysqli_fetch_assoc($resu);
                    if($rowu["convestoque"] == "Y"){
                        $aqtdd = $_rowaux0["qtdd"] / $auxconv;
                        $aqtdc = $_rowaux0["qtdc"] / $auxconv;
                    }else{*/
                $aqtdd = $_rowaux0["qtdd"];
                $aqtdc = $_rowaux0["qtdc"];
                //}
                $tqtdd += $aqtdd;
                $tqtdc += $aqtdc;
            }
            //removido o credito do calculo de media diaria
            //$mediadiaria = ($tqtdd - $tqtdc)/60;
            $mediadiaria = ($tqtdd) / $consumodias;
            if ($mediadiaria < 0) {
                $mediadiaria *= -1;
            }
        } else
            $mediadiaria = 0;
        return $mediadiaria;
    }

    //retorna o sql do produtos em alerta ou pedidos em alerta conforme o tipo
    // pedidoemalerta.php -produtoemalertar.php
    function getProdservAlerta($idunidadepadrao, $tipo, $idcotacao = NULL, $idempresa = NULL)
    {

        if ($tipo == 'PEDIDO') {
            // $wherexx=" xx.statusorc ='CONCLUIDO' ";
            $wherexx = " xx.status in('DIVERGENCIA','APROVADO')";
        } else {
            $wherexx = " ((xx.statusorc !='CONCLUIDO') or (xx.statusorc ='CONCLUIDO' and  pedido_automatico > pedidoautomatico)) ";
        }

        if (!empty($idcotacao)) {
            $joincontaitem = " AND EXISTS (SELECT 1 FROM prodservcontaitem pi JOIN objetovinculo ov ON ov.idobjetovinc = pi.idcontaitem AND ov.idobjeto = '$idcotacao' AND ov.tipoobjeto = 'cotacao' AND ov.tipoobjetovinc = 'contaitem'
                                            WHERE pi.idprodserv = p.idprodserv)";
        }

        if (!empty($idempresa)) {
            $empresanf = ' and n.idempresa = '.$idempresa;
            $empresaps = ' and p.idempresa = '.$idempresa;
        }

        $vendas = "AND p.comprado = 'Y'";
        $joinun = "join unidadeobjeto o on (o.idobjeto='produtoemalerta' and o.tipoobjeto = 'modulo' and o.idunidade=p.idunidadealerta) join unidade u2 on(u2.idunidade = o.idunidade ".share::otipo('cb::usr')::produtoEmAlertaIdempresa("u2.idempresa")." )";
        //$joinun= join unidadeobjeto o on( o.idobjeto = p.idprodserv and o.tipoobjeto = 'prodserv') "; 

        $sql = "SELECT 
        idprodserv,
        codprodserv,
        tempocompra,
        descr,
        estmin,
        estmin_exp,
        pedidoautomatico,
        pedido_automatico,
        pedido,
        total,
        quar,	
        entrada,
        atrasado,
        atrasadocot,
	    statusorc,
        status,
        idnf,
        idcotacao,
	    tipo,
        CASE
            WHEN xx.pedido_automatico > xx.pedidoautomatico AND xx.idcotacao > 1 THEN 2
            WHEN  xx.status ='DIVERGENCIA'  and xx.atrasado ='V' THEN 5
            WHEN  xx.status ='APROVADO'  and xx.atrasado ='V' THEN 5
            WHEN  xx.status ='DIVERGENCIA'  and xx.atrasado ='O' THEN 9
            WHEN  xx.status ='APROVADO'  and xx.atrasado ='O' THEN 9
        	WHEN  xx.statusorc ='INICIO' THEN 1
            WHEN  xx.statusorc ='PREVISAO' THEN 1
            WHEN  xx.statusorc ='SEMORCAMENTO' THEN 7          
            WHEN  xx.statusorc = 'ENVIADO' and xx.atrasadocot ='V' THEN 3
            WHEN  xx.statusorc = 'ENVIADO' and xx.atrasadocot ='O' THEN 4
			WHEN  xx.statusorc = 'PENDENTE' and xx.atrasadocot ='V' THEN 3
            WHEN  xx.statusorc = 'PENDENTE' and xx.atrasadocot ='O' THEN 4
            
        ELSE 10
	END AS ordem,
        CASE xx.entrada
		WHEN 'NORMAL' THEN 1
		ELSE 2
	END AS ordem2,
	ultimoconsumo
        FROM (
	SELECT c.idcotacao AS idprodserv,
				'' AS codprodserv,
                0 as tempocompra,
				prodservdescr AS descr,
				0 AS estmin,
                '' AS estmin_exp,
                0 as pedidoautomatico,
                0 as pedido_automatico,
				0 AS pedido,
				0 AS total,
				0 AS quar,
				'MANUAL' as entrada,
				c.status AS statusorc,
                n.status as status,
                n.idnf,
                c.idcotacao,
               (if(DATE_FORMAT(n.previsaoentrega,'%Y-%m-%d')>=DATE_FORMAT(now(),'%Y-%m-%d'),'O','V'))  as atrasado,
               (if(DATE_FORMAT(c.prazo,'%Y-%m-%d')>=DATE_FORMAT(now(),'%Y-%m-%d'),'O','V'))  as atrasadocot,
				'MANUAL' AS tipo,
				c.alteradoem AS ultimoconsumo
			FROM nfitem p JOIN nf n ON p.idnf = n.idnf JOIN cotacao c ON n.idobjetosolipor = c.idcotacao
			WHERE p.idprodserv IS NULL AND n.status NOT IN ('CONCLUIDO', 'CANCELADO', 'REPROVADO')
			".share::otipo('cb::usr')::produtoEmAlertaIdempresa("c.idempresa")."
            and p.nfe='Y'
			AND n.tipoobjetosolipor = 'cotacao' 
            $empresanf
            $joincontaitem
		UNION 
		SELECT 
			u2.*,
            CASE
				WHEN u3.statusorc is null THEN 'SEMORCAMENTO'
				ELSE u3.statusorc
			END as statusorc,  
            (select n.status
				from nfitem i, nf n,cotacao c
				where i.idprodserv =u2.idprodserv
				and i.idnf = n.idnf
				and i.nfe='Y'
				and n.idobjetosolipor = u3.idcotacao 
				and n.tipoobjetosolipor = 'cotacao' 
				and n.status in ('APROVADO','DIVERGENCIA') LIMIT 1 ) as status, 
                (select n.idnf
				from nfitem i, nf n,cotacao c
				where i.idprodserv =u2.idprodserv
				and i.idnf = n.idnf
				and i.nfe='Y'
				and n.idobjetosolipor = u3.idcotacao 
				and n.tipoobjetosolipor = 'cotacao' 
				and n.status in ('APROVADO','DIVERGENCIA') LIMIT 1 ) as idnf,
                		u3.idcotacao,
				(select if(DATE_FORMAT(n.previsaoentrega,'%Y-%m-%d')>=DATE_FORMAT(now(),'%Y-%m-%d'),'O','V')  
				from nfitem i, nf n,cotacao c
				where i.idprodserv =u2.idprodserv
				and i.idnf = n.idnf
				and i.nfe='Y'
				and n.idobjetosolipor = u3.idcotacao 
				and n.tipoobjetosolipor = 'cotacao' 
				and n.status in ('APROVADO','DIVERGENCIA') LIMIT 1 ) as atrasado,
            (if(DATE_FORMAT(u3.prazo,'%Y-%m-%d')>=DATE_FORMAT(now(),'%Y-%m-%d'),'O','V'))  as atrasadocot,
			'NORMAL' AS tipo,
			ifnull((SELECT 
					criadoem
				FROM
					prodcomprar
				WHERE
					status = 'ATIVO'
						AND idprodserv = u2.idprodserv),sysdate()) AS ultimoconsumo
		FROM
			(SELECT 
				idprodserv,
					codprodserv,
                    tempocompra,
					descr,
                    estmin,
                    estmin_exp,
                                        pedidoautomatico,
                                        pedido_automatico,
					pedido,
					SUM(total) AS total,
					SUM(quar) AS quar,				
					entrada
			FROM
				(SELECT 
				p.idprodserv,
					p.codprodserv,
                    p.tempocompra,
					concat(e.sigla,' - ',p.descr) as descr,
                    p.estmin,
                    p.estmin_exp,
                    p.pedidoautomatico,
                    p.pedido_automatico,
					if(p.pedidoautomatico>p.pedido_automatico,p.pedidoautomatico,p.pedido_automatico) as pedido,
					IFNULL(f.qtd, 0) AS total,
					(SELECT 
							IFNULL(SUM(q.qtdprod), 0)
						FROM
							lote q
						WHERE
							q.idprodserv = p.idprodserv
								AND q.status = 'QUARENTENA') AS quar,					
					'NORMAL' AS entrada
			FROM
				prodserv p  
                                ".$joinun."                                    
			LEFT JOIN lote l ON (l.idprodserv = p.idprodserv 
                        AND l.status IN ('APROVADO' , 'QUARENTENA'))
                        LEFT JOIN lotefracao f on(f.idlote=l.idlote
                        and f.idunidade = p.idunidadeest and f.status='DISPONIVEL'
                            )
            left join empresa e on (e.idempresa = p.idempresa)
			WHERE p.tipo = 'PRODUTO'
                ".share::otipo('cb::usr')::produtoEmAlertaIdempresa("p.idempresa")."
					AND p.status = 'ATIVO'
					AND p.estmin IS NOT NULL
					AND p.estmin != 0.00
                 -- AND p.estideal != 0.00
                    $empresaps
                    $joincontaitem
					".$vendas.") AS u
			GROUP BY u.idprodserv) u2
				LEFT JOIN
			(SELECT 
				MAX(c.prazo) AS prazo, c.status AS statusorc, i.idprodserv,c.idcotacao
			FROM
				cotacao c
			JOIN nf n -- FORCE INDEX (NOVOPRAZO) 
			ON n.idobjetosolipor = c.idcotacao
			JOIN nfitem i  ON n.tipoobjetosolipor = 'cotacao'
				AND i.idnf = n.idnf
                and i.nfe='Y'
				AND n.status NOT IN ('CONCLUIDO', 'CANCELADO', 'REPROVADO')
                $empresanf
			GROUP BY i.idprodserv) u3 ON u3.idprodserv = u2.idprodserv
		WHERE
			((u2.estmin > u2.total) or (u2.pedido_automatico > u2.pedidoautomatico))
            ) AS xx  where ".$wherexx." group by idprodserv,idcotacao  order by
       
       ";
        //echo '<pre>'.$sql.'</pre>';
        return $sql;
    }

    function busca_valor_formula($inidprodservformula, $percentagem = 1)
    {
        // funcao para buscar o valor da formula e o valor de cada item da fórmula.

        //$valoritem=0;
        /*  $sql="select  i.qtdi,i.idprodserv,p.fabricado,p.descr,p.un,fi.idprodservformula,ifnull((i.qtdi/fi.qtdpadraof),1) as perc
                from prodservformula f 
                join  prodservformulains i on(f.idprodservformula=i.idprodservformula and  i.qtdi >0) 
                join prodserv p on(p.idprodserv = i.idprodserv) 
                left join prodservformula fi on(fi.status='ATIVO' 
                                                and fi.idprodserv=i.idprodserv
                                                and( fi.idplantel=f.idplantel or fi.idplantel is null or fi.idplantel='') )
                where f.idprodservformula= ".$inidprodservformula." order by p.descr";
                */
        $sql = "select * from (
                    select  i.qtdi,i.idprodserv,p.fabricado,p.descr,concat(fi.rotulo,' ',ifnull(fi.dose,' '),' ',p.conteudo,' ',' (',fi.volumeformula,' ',fi.un,')') as rotulo,p.un,fi.idprodservformula,ifnull(((i.qtdi/fi.qtdpadraof)*".$percentagem."),1) as perc
                             from prodservformula f 
                             join  prodservformulains i on(f.idprodservformula=i.idprodservformula and  i.qtdi >0) 
                             join prodserv p on(p.idprodserv = i.idprodserv) 
                             join prodservformula fi on(fi.status='ATIVO' 
                                                             and fi.idprodserv=i.idprodserv
                                                             and( fi.idplantel=f.idplantel ) )
                                                             
                             where f.idprodservformula= ".$inidprodservformula."
                             union 
                             select  i.qtdi,i.idprodserv,p.fabricado,p.descr,concat(fi.rotulo,' ',ifnull(fi.dose,' '),' ',p.conteudo,' ',' (',fi.volumeformula,' ',fi.un,')') as rotulo,p.un,fi.idprodservformula,ifnull(((i.qtdi/fi.qtdpadraof)*".$percentagem."),1) as perc
                             from prodservformula f 
                             join  prodservformulains i on(f.idprodservformula=i.idprodservformula and  i.qtdi >0) 
                             join prodserv p on(p.idprodserv = i.idprodserv) 
                              join prodservformula fi on(fi.status='ATIVO' 
                                                             and fi.idprodserv=i.idprodserv
                                                             and(  fi.idplantel is null or fi.idplantel='') )
                             where f.idprodservformula=  ".$inidprodservformula."
                             and not exists (select 1 from   prodservformula fi2 where fi2.status='ATIVO' 
                                                             and fi2.idprodserv=i.idprodserv
                                                             and(  fi2.idplantel is not null) )
                            union 
                            select  i.qtdi,i.idprodserv,p.fabricado,p.descr,'' as rotulo,p.un,null,ifnull(((i.qtdi/1)*".$percentagem."),1) as perc
                             from prodservformula f 
                             join  prodservformulains i on(f.idprodservformula=i.idprodservformula and  i.qtdi >0) 
                             join prodserv p on(p.idprodserv = i.idprodserv) 
                             where f.idprodservformula= ".$inidprodservformula." 
                             and not exists (select 1 from  prodservformula fi where fi.status='ATIVO' 
                                                             and fi.idprodserv=i.idprodserv)
                         
                              ) as u					 
                        group by u.idprodserv";
        $res = d::b()->query($sql);

        while ($row = mysqli_fetch_assoc($res)) {
            if ($row['fabricado'] == 'Y' and !empty($row['idprodservformula'])) {
                $this->busca_valor_formula($row['idprodservformula'], $row['perc']);
            } elseif ($row['fabricado'] == 'N') {
                $valor = $this->busca_valor_item($row['idprodserv'], $row['qtdi']);
                $valor = $valor * $percentagem;

                $this->valoritem = $this->valoritem + $valor;
            }
        } //while($row=mysqli_fetch_assoc($res)){
        return round($this->valoritem, 2);
    }

    function busca_valor_item($inidprodserv, $qtdi = 1)
    {
        $sql = "select ifnull(l.vlrlote,0) as valoritem,l.idlote 
      from lote l
      where l.idprodserv = ".$inidprodserv." order by idlote desc limit 1";
        $res = d::b()->query($sql);
        $row = mysqli_fetch_assoc($res);
        $valor = round(($qtdi * $row['valoritem']), 2);
        return $valor;
    }
}
?>