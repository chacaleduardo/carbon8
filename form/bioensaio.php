<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__ . "/controllers/bioensaio_controller.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetros chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "bioensaio";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idbioensaio" => "pk"
);

//Recuperar a unidade padrão conforme módulo pré-configurado
$idunidadepadrao = getUnidadePadraoModulo($_GET["_modulo"]);


/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
// GVT - 28/01/2020 - comentado a parte de idempresa para não parar as atividades no bioensaio
// voltar idempresa posteriormente.
$pagsql = "select * from bioensaio where idbioensaio = '#pkid' ";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

if (!empty($_1_u_bioensaio_idespeciefinalidade)) {
    $_1_u_bioensaio_idplantel = traduzid('especiefinalidade', 'idespeciefinalidade', 'idplantel', $_1_u_bioensaio_idespeciefinalidade);
}


function getClientesEst()
{

    return BioensaioController::buscarClientesParaEstudo(getidempresa('idempresa', 'pessoa'));
}
$arrCli = getClientesEst();


function getProdEst()
{

    global $idunidadepadrao;

    return BioensaioController::buscarProdutosParaEstudo(getidempresa('p.idempresa', 'prodserv'), $idunidadepadrao);
}


function getLoteAnimalEst()
{
    global $idunidadepadrao, $_1_u_bioensaio_idplantel, $_1_u_bioensaio_qtd;
    if (empty($_1_u_bioensaio_qtd)) {
        $_1_u_bioensaio_qtd = 0;
    }
    if (empty($_1_u_bioensaio_idplantel)) {
        $_1_u_bioensaio_idplantel = 0;
    }

    return BioensaioController::buscarLoteAnimalParaEstudo(getidempresa('p.idempresa', 'prodserv'), $idunidadepadrao, $_1_u_bioensaio_qtd, $_1_u_bioensaio_idplantel);
}


function listateste($inidservicoensaio)
{
    $rest = BioensaioController::buscarResultadosVinculadosAoEnsaio($inidservicoensaio);
    $qtdres = count($rest);
    if ($qtdres > 0) {

        foreach ($rest as $k => $r) {

            $lkmodulo = BioensaioController::buscarModuloPorUnidade($r['idunidade'], 'resultado');
            if ($r['status'] == 'FECHADO') {
                $cor = "#B0E2FF";
            } elseif ($r['status'] == 'ASSINADO') {
                $cor = "#00ff004d";
            } else {
                $cor = "#c4c5b47a";
            }

            $classDrag = ($r["status"] == "ABERTO"  or $r["status"] == "AGUARDANDO") ? "dragExcluir" : "";
            $disableteste = ($r["status"] == "ABERTO" or $r["status"] == "AGUARDANDO") ? "" : "readonly='readonly'";
?>
            <tr style=" background-color:<?= $cor ?>;" class="<?= $classDrag ?>" idresultado="<?= $r["idresultado"] ?>">
                <td>
                    <i title="Etiqueta de Atividade" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="imprimeEtiqueta(<?= $r['idservicoensaio'] ?>,'servicoensaio')"></i>
                </td>
                <td style="white-space: nowrap;">
                    <input type="hidden" name="_<?= $r["idresultado"] ?>_u_resultado_ord" value="<?= $r["ord"] ?>">
                    <input type="text" name="_<?= $r["idresultado"] ?>_u_resultado_quantidade" value="<?= $r["quantidade"] ?>" style="width:30px" placeholder="Quant." vnulo vnumero>
                </td>
                <td style="white-space: nowrap;">
                    <input type="hidden" name="_<?= $r["idresultado"] ?>_u_resultado_idresultado" value="<?= $r["idresultado"] ?>">
                    <input type="hidden" name="_<?= $r["idresultado"] ?>_u_resultado_idtipoteste" class="idprodserv" value="<?= $r["idtipoteste"] ?>">
                    <a href="?_modulo=<?= $lkmodulo ?>&_acao=u&idresultado=<?= $r["idresultado"] ?>" target="_blank"><?= $r["sigla"] ?>-<?= $r["rotulo"] ?> <?= $r["dataserv"] ?></a>
                </td>
                <td>
                    <a title="Etiqueta Com número dos animais" class="fa fa-print pull-right fa-lg azulclaro pointer hoverazul" onclick="imprimeEtiquetasoro(<?= $r['idservicoensaio'] ?>)"></a>
                </td>
                <td>
                    <?
                    $hidemove = "";
                    if ($r["status"] !== "ABERTO" and $r["status"] !== "AGUARDANDO") {
                        $hidemove = "hidden";
                    }
                    ?>
                    <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable <?= $hidemove ?>" onclick="delresultado(<?= $r["idresultado"] ?>)" title="Excluir Resultado"></i>
                </td>
            </tr>
    <?
            //$i++;
        } //while($r=  mysqli_fetch_assoc($rest))
    } //if($qtdres>0)
    ?>
    <tr>
        <td></td>
    </tr>
<?
} //function listateste()
function dprotocolo($idanalise)
{
    return (BioensaioController::verificarSeExisteResultadoNaAnalise($idanalise));
}
?>
<style>
    .linhacab1 {
        padding: 0;
        clear: both;
        display: inline-block;
        vertical-align: top;
        margin: 0px;
        background-color: white;
        min-height: 75px;
        width: 100%;
        font-size: 10px;
        color: gray;
        align-items: center;
        text-align: center;
    }

    .linhacab2 {
        padding: 0;
        clear: both;
        display: inline-block;
        vertical-align: top;
        margin: 0px;
        background-color: white;
        width: 100%;
        min-height: 20px;
        align-items: center;
        text-align: center;
    }

    .linhacab3 {
        padding: 0;
        clear: both;
        display: inline-block;
        vertical-align: top;
        margin: 0px;
        background-color: white;
        width: 100%;
        min-height: 150px;
        align-items: center;
        text-align: center;
    }

    .conteudo {
        /* align-items: center;*/
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        /* justify-content: center; */
    }

    .servico {
        border: none;
        /* min-width:170px;
        min-height: 40px; */
        *background-color: #FF7F50;
        float: left;
        border-radius: 10px;
        margin: 2px;
        display: flex;
        justify-content: center;
        /* align-items: center;*/
        vertical-align: top;
    }

    .trteste {
        background-color: white;
    }

    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        margin: 0;
    }

    .linhacab {
        padding: 0;
        clear: both;
        display: inline-block;
        vertical-align: top;
        margin: 0px;
        background-color: transparent;
        margin-right: 1px;
        border: 1px dashed #1f1f1f52;
        height: 100%;
        width: 200px;
    }

    .colunainv {
        height: 100%;
        border: 1px solid #A9A9A9;
        width: 95%;
        float: left;
        position: relative;
        text-align: center;
        background-color: #E0E0E0;
    }
</style>
<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <table style="width: 100%;">
                <tr>
                    <td><strong>Reg.:</strong></td>

                    <td>
                        <? if (!empty($_1_u_bioensaio_idregistro)) { ?>
                            <label class="alert-warning"> B<?= $_1_u_bioensaio_idregistro ?> - <?= $_1_u_bioensaio_exercicio ?></label>
                        <? } ?>
                        <input name="_1_<?= $_acao ?>_bioensaio_idregistro" type="hidden" value="<?= $_1_u_bioensaio_idregistro ?>" readonly='readonly'>
                        <input name="_1_<?= $_acao ?>_bioensaio_exercicio" type="hidden" value="<?= $_1_u_bioensaio_exercicio ?>" readonly='readonly'>
                        <input id="idbioensaio" name="_1_<?= $_acao ?>_bioensaio_idbioensaio" type="hidden" value="<?= $_1_u_bioensaio_idbioensaio ?>" readonly='readonly'>
                        <? if ($_acao == 'i') { ?>
                            <input size="8" name="_1_<?= $_acao ?>_bioensaio_idunidade" type="hidden" value="<?= $idunidadepadrao ?>">
                        <? 
                        } else { ?>
                            <input size="8" name="_1_<?= $_acao ?>_bioensaio_idunidade" type="hidden" value="<?= $_1_u_bioensaio_idunidade ?>">
                        <?
                    $idunidadepadrao= $_1_u_bioensaio_idunidade ;
                    } ?>
                    </td>
                    <td align="right">Espécie/Finalidade:</td>
                    <td>
                        <select name="_1_<?= $_acao ?>_bioensaio_idespeciefinalidade" style="width: 17em;" vnulo>
                            <option></option>
                            <? fillselect(BioensaioController::toFillSelect(BioensaioController::buscarEspecieFinalidade($idunidadepadrao)), $_1_u_bioensaio_idespeciefinalidade); ?>
                        </select>
                    </td>
                    <td align="right">Cliente:</td>
                    <td colspan="5">
                        <input id="idpessoa" type="text" name="_1_<?= $_acao ?>_bioensaio_idpessoa" vnulo cbvalue="<?= $_1_u_bioensaio_idpessoa ?>" value="<?= $arrCli[$_1_u_bioensaio_idpessoa]["nome"] ?>" style="width: 30em;" vnulo>
                        <input name="bioensaio_idpessoa" type="hidden" value="<?= $_1_u_bioensaio_idpessoa ?>" readonly='readonly'>
                    </td>
                    <? if (!empty($_1_u_bioensaio_idpessoa)) { ?>
                        <td align="right" norwrap>Estudo:</td>
                        <td nowrap="nowrap">
                            <input type="text" name="_1_<?= $_acao ?>_bioensaio_estudo" value="<?= $_1_u_bioensaio_estudo ?>" style="width: 14em;">
                        </td>
                    <? } ?>
                    <td align="right">Qtd.:</td>
                    <td><input name="_1_<?= $_acao ?>_bioensaio_qtd" size="10" type="text" value="<?= $_1_u_bioensaio_qtd ?>" vnulo></td>
                    <td>
                        <input name="_1_<?= $_acao ?>_bioensaio_status" type="hidden" value="<?= $_1_u_bioensaio_status ?>">
                        <span style="margin-left: 15%;">
                            <? $rotulo = getStatusFluxo($pagvaltabela, 'idbioensaio', $_1_u_bioensaio_idbioensaio) ?>
                            <label class="alert-warning" title="<?= $_1_u_bioensaio_status ?>" id="statusButton"><?= mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?> </label>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<?
if (!empty($_1_u_bioensaio_idespeciefinalidade)) { ?>

    <div iv class="col-md-5">
        <div class="panel panel-default">
            <div class="panel-heading">Informações do Produto</div>
            <div class="panel-body">
                <table style="width: 32vw;">
                    <tr>
                        <td align="right">Produto:</td>
                        <td colspan="3">
                            <? if (!empty($_1_u_bioensaio_idlotepd)) {
                                $rowl = BioensaioController::buscarDescrProduto($_1_u_bioensaio_idlotepd);
                            ?>
                                <label class="alert-warning">
                                    <font style='font-size: 11px;'><?= $rowl['descr'] ?></font>
                                </label>
                                <?
                                $modulo = BioensaioController::buscarModuloPorUnidade($rowl['idunidade'], 'lote');
                                ?>
                                <i onclick="janelamodal('?_modulo=<?= $modulo ?>&_acao=u&idlote=<?= $rowl['idlote'] ?>')" class="fa fa-bars cinzaclaro hoverazul btn-lg pointer"></i>
                                <i onclick="deletaltpd(<?= $_1_u_bioensaio_idbioensaio ?>)" class="fa fa-trash cinzaclaro hoververmelho btn-lg pointer"></i>
                                <input name="_1_<?= $_acao ?>_bioensaio_idnucleo" type="hidden" value="<?= $_1_u_bioensaio_idnucleo ?>" style="width: 27vw;">
                            <? } else { ?>
                                <input name="_1_<?= $_acao ?>_bioensaio_idlotepd" cbvalue="<?= $_1_u_bioensaio_idlotepd ?>" value="<?= $arrProd[$_1_u_bioensaio_idlotepd]["descr"] ?>" style="width: 27vw;">
                                <input name="_1_<?= $_acao ?>_bioensaio_idnucleo" type="hidden" value="<?= $_1_u_bioensaio_idnucleo ?>" style="width: 27vw;">
                            <? } ?>
                        </td>
                    </tr>

                    <tr>
                        <td align="right" nowrap>Enviado por:</td>
                        <td><input <? //=$readonly2
                                    ?> name="_1_<?= $_acao ?>_bioensaio_respenvio" class="" type="text" value="<?= $_1_u_bioensaio_respenvio ?>"></td>
                        <td align="right" nowrap> Envio:</td>
                        <td>
                            <input class="calendario" name="_1_<?= $_acao ?>_bioensaio_dataenvio" id="fdata2" type="text" size="6" value="<?= $_1_u_bioensaio_dataenvio ?>">
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>Responsável:</td>
                        <td>
                            <input <? //=$readonly2
                                    ?> name="_1_<?= $_acao ?>_bioensaio_recebidopor" size="15" type="text" value="<?= $_1_u_bioensaio_recebidopor ?>">
                        </td>
                        <td align="right" style="vertical-align: top;">Recebimento:</td>
                        <td style="vertical-align: top;">
                            <input class="calendario" name="_1_<?= $_acao ?>_bioensaio_datareceb" id="fdata3" type="text" size="6" value="<?= $_1_u_bioensaio_datareceb ?>">
                        </td>
                    </tr>
                </table>
                <table style="width: 32vw;">
                    <tr>
                        <td align="right" style="vertical-align: top;">Outras infos:</td>
                        <td colspan="3">
                            <textarea name="_1_<?= $_acao ?>_bioensaio_antigeno" style="width: 413px; height: 148px;"><?= $_1_u_bioensaio_antigeno ?></textarea>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="panel panel-default">
            <div class="panel-heading">Informações Específicas</div>
            <div class="panel-body">
                <table style="width: 32vw;">
                    <tr>
                        <? $_1_u_bioensaio_idplantel = traduzid('especiefinalidade', 'idespeciefinalidade', 'idplantel', $_1_u_bioensaio_idespeciefinalidade); ?>
                        <? $rotulo = traduzid('plantelrotulo', 'idplantel', 'rotulo', $_1_u_bioensaio_idplantel); ?>
                        <td align="Right"><?= $rotulo ?>:</td>
                        <td colspan="1"><input name="_1_<?= $_acao ?>_bioensaio_nascimento" class="calendario" class='size6' type="text" value="<?= $_1_u_bioensaio_nascimento ?>" vnulo></td>
                    </tr>
                    <tr>
                        <td align="right">Lote:</td>
                        <td colspan="6">
                            <? if (!empty($_1_u_bioensaio_idlote)) {
                                $rowl = BioensaioController::buscarDescrLote($_1_u_bioensaio_idlote);
                                $modulo = BioensaioController::buscarModuloPorUnidade($rowl['idunidade'], 'lote');
                            ?>
                                <label class="alert-warning"><?= $rowl['descr'] ?></label>
                                <i onclick="janelamodal('?_modulo=<?= $modulo ?>&_acao=u&idlote=<?= $rowl['idlote'] ?>')" class="fa fa-bars cinzaclaro hoverazul btn-lg pointer"></i>
                                <i onclick="deletalt(<?= $_1_u_bioensaio_idlote ?>,<?= $_1_u_bioensaio_idbioensaio ?>)" class="fa fa-trash cinzaclaro hoververmelho btn-lg pointer"></i>

                            <?
                            } else { ?>
                                <input name="_bioensaio_idlote" cbvalue="<?= $_1_u_bioensaio_idlote ?>" value="<?= $arrLoteAnimal[$_1_u_bioensaio_idlote]["descr"] ?>">
                            <? } ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Identificação do Grupo:</td>
                        <td><input <?= $readonly2 ?> name="_1_<?= $_acao ?>_bioensaio_coranilha" class='size6' type="text" value="<?= $_1_u_bioensaio_coranilha ?>"></td>
                        <td align="right">Vol. Aplic.:</td>
                        <td><input name="_1_<?= $_acao ?>_bioensaio_volume" class='size6' type="text" Value="<?= $_1_u_bioensaio_volume ?>"></td>
                    </tr>
                    <tr>
                        <td align="right">Via:</td>
                        <td>
                            <select name="_1_<?= $_acao ?>_bioensaio_via">
                                <option value=""></option>
                                <? fillselect("select 'INTRA-MUSCULAR-COXA','Int.-Musc. Coxa' 
                                    union select 'INTRA-MUSCULAR-PEITO','Int. Musc. Peito' 
                                    union select 'SUBCUTANEA','Subcutânea' 
                                    union select 'INTRA-PERITONEAL','Int. Peritoneal'
                                    union select 'INTRA-MUSCULAR','Int. Muscular'
                                    union select 'INTRA-OCULAR','Int. Ocular'
                                    union select 'INTRA-OVO','Int. Ovo'
                                    union select 'INTRA-VENOSA','Int. Venosa'
                                    union select 'ORAL','Oral'
                                    union select 'NASAL','Nasal'
                            ", $_1_u_bioensaio_via); ?>
                            </select>
                        </td>
                        <td align="right" class='nowrap'>Nº Doses:</td>
                        <td><input name="_1_<?= $_acao ?>_bioensaio_doses" class='size6' type="text" Value="<?= $_1_u_bioensaio_doses ?>"></td>
                    </tr>

                </table>
                <table style="width: 32vw;">
                    <tr>
                        <td align="right" style="vertical-align: top;">Obs:</td>
                        <td colspan="5">
                            <textarea name="_1_<?= $_acao ?>_bioensaio_obs" style="width: 100%; height: 114px;"><?= $_1_u_bioensaio_obs ?></textarea>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <?
        if (!empty($_1_u_bioensaio_idpessoa) and !empty($_1_u_bioensaio_idbioensaio)) {
        ?>
            <div class="panel panel-default">
                <div class="panel-heading">Inter-relações</div>
                <div class="panel-body">
                    <table>
                        <?
                        if (!empty($_1_u_bioensaio_idficharep)) {
                            $rowf = BioensaioController::buscarFichaRepBioensaio($_1_u_bioensaio_idficharep);
                        ?>
                            <tr>
                                <td>Ficha Rep.:</td>
                                <td>
                                    <? $modulo = BioensaioController::buscarModuloPorUnidade($idunidadepadrao, 'fichaprod'); ?>
                                    <a title="Ver Ficha de Reproducao" href="javascript:janelamodal('?_modulo=<?= $modulo ?>&_acao=u&idficharep=<?= $rowf["idficharep"] ?>')">
                                        <?= $rowf["ficharep"] ?>
                                    </a>
                                </td>
                            </tr>
                        <?
                        } //if(!empty($_1_u_bioensaio_idficharep))
                        ?>
                        <tr>
                            <td align="right">Formalização:</td>
                            <td>
                                <select <?= $disabled2 ?> name="_1_<?= $_acao ?>_bioensaio_idloteativ">
                                    <option value="0" selected></option>
                                    <?
                                    fillselect(BioensaioController::toFillSelect(BioensaioController::buscarFormalizacaoParaBioensaio($_1_u_bioensaio_idbioensaio)), $_1_u_bioensaio_idloteativ);
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <!-- <tr>
                            <td align="right">Documento:</td>
                            <td>
                                <select id="idsgdoc" <?= $disabled2 ?> name="_1_<?= $_acao ?>_bioensaio_idsgdoc">
                                    <option value="0" selected></option>
                                    <? fillselect(BioensaioController::toFillSelect(BioensaioController::buscarDocumentoParaBioensaio(58, $_1_u_bioensaio_idempresa)), $_1_u_bioensaio_idsgdoc); ?>
                                </select>
                                <? if ($_1_u_bioensaio_idsgdoc) { ?>
                                    <a class="fa fa-bars pointer hoverazul" title="Documento" onclick="janelamodal('report/sgdocprint.php?acao=u&idsgdoc=<?= $_1_u_bioensaio_idsgdoc ?>')"></a>
                                <? } ?>
                            </td>
                        </tr> -->
                        <tr>
                            <? if ($_1_u_bioensaio_idbioensaio) {
                                $resd = BioensaioController::buscarDocumentoDoBioensaio($_1_u_bioensaio_idbioensaio);
                                $qtdd = count($resd);
                                if ($qtdd < 1) { ?>
                                    <td align="right" style="vertical-align: top;">Certificado:</td>
                                    <td style="vertical-align: top;" colspan="3">
                                        <select name="bioensaiosgdoc_idsgdoc" class="" colspan="3" onchange="bioensaiosgdoc(this);">
                                            <option value=""></option>
                                            <? fillselect("select idsgdoc,concat('ID ',idregistro,' - ',titulo) as titulo from sgdoc
                                                    where idsgdoctipodocumento=32  
                                                    and status = 'APROVADO'
                                                    " . getidempresa('idempresa', 'documento') . " ORDER BY titulo"); ?>
                                        </select>
                                    </td>
                                    <? } else {
                                    $d = 77;
                                    foreach ($resd as $k => $rowd) {
                                        $d = $d + 1; ?>
                                        <td align="right" style="vertical-align: top;"> Certificado:</td>
                                        <td style="vertical-align: top;" colspan="3" class="size30">
                                            <a class="pointer" onclick="janelamodal('/form/sgdocupd.php?_acao=u&idsgdocupd=<?= $rowd['idsgdocupd'] ?>')">
                                                <?= $rowd['titulo'] ?>-<?= $rowd['versao'] ?>.<?= $rowd['revisao'] ?>
                                            </a>
                                            <i class="fa fa-trash cinzaclaro hoververmelho btn-lg pointer" idcontapagaritem="<?= $rowp['idcontapagaritem'] ?>" onclick="dbioensaiosgdoc(<?= $rowd["idbioensaiosgdoc"] ?>)" title="Retirar"></i>
                                        </td>
                            <? } //while($rowd=mysqli_fetch_assoc($resd))
                                } //if($qtdd <1)
                            } //if($_1_u_bioensaio_idbioensaio)
                            ?>
                        </tr>
                    </table>
                </div>
            </div>
            <?
            $resdes = BioensaioController::buscarPaisEFilhosDoBioensaios($_1_u_bioensaio_idbioensaio);
            $nrowdes = count($resdes);
            ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <table>
                        <tr>
                            <Td>Desenho Experimental</Td>
                            <td align="left" nowrap>
                                <? if ($_1_u_bioensaio_agrupar == 'N') {
                                    $checked = '';
                                    $agrupar = 'Y';
                                } else {
                                    $checked = 'checked';
                                    $agrupar = 'N';
                                }
                                ?>
                                <input title="Agrupar" type="checkbox" <?= $checked ?> name="nameagrupar" onclick="flgagrupar(<?= $_1_u_bioensaio_idbioensaio ?>,'<?= $agrupar ?>');">
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="panel-body">
                    <? if ($nrowdes > 0) { ?>
                        <table class="table table-striped planilha">
                            <tr>
                                <th>Reg.</th>
                                <th>Nº Animais</th>
                                <th></th>
                            </tr>
                            <? foreach ($resdes as $k => $rowdes) {
                                if (empty($rowdes['idbioensaiodes'])) {
                                    $colorir = "style='background-color :orange;'";
                                } else {
                                    $colorir = "";
                                } ?>
                                <tr <?= $colorir ?>>
                                    <td>
                                        <?
                                        $modulo = BioensaioController::buscarModuloPorUnidade($idunidadepadrao, 'bioensaio');
                                        ?>
                                        <a title="Editar Produto" href="javascript:janelamodal('?_modulo=<?= $modulo ?>&_acao=u&idbioensaio=<?= $rowdes['idbioensaioc'] ?>')">
                                            <?= $rowdes['registro'] ?>
                                        </a>
                                    </td>
                                    <td><?= $rowdes['qtd'] ?></td>
                                    <td align="center">
                                        <?
                                        $rowint = BioensaioController::buscarDesenhoExperimental($rowdes['idbioensaio'], $_1_u_bioensaio_idbioensaio);
                                        if (!empty($rowint)) { ?>
                                            <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="iubioensaiodes('d',<?= $rowint['idbioensaiodes'] ?>)" alt="Desvincular Experimento"></i>
                                        <? } ?>
                                    </td>
                                </tr>
                            <? } //while($rowdes=mysqli_fetch_assoc($resdes))
                            ?>
                        </table>
                    <? } //if($nrowdes>0)
                    ?>
                    <table>
                        <tr>
                            <td nowrap>Vincular Estudo:</td>
                            <td>
                                <select name="" onchange="iubioensaiodes('i',this);">
                                    <option value="0" selected></option>
                                    <?
                                    fillselect(BioensaioController::toFillSelect(BioensaioController::buscarEstudosParaControle($_1_u_bioensaio_idpessoa, getidempresa('b.idempresa', 'bioensaio'))));
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
    </div>
    <? if (!empty($_1_u_bioensaio_idbioensaio)) {
        $res = BioensaioController::buscarAnalisesDoEnsaio($_1_u_bioensaio_idbioensaio);
        $i = 1;
        foreach ($res as $k => $row) {
            $i = $i + 1;
            //se o controle ja possui teste para analise não se muda
            $desabledct = BioensaioController::verificaSeHacontroleNaAnalise($row["idanalise"]);
            ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <table>
                            <tr>
                                <? if (!empty($row['idanalise'])) { ?>
                                    <td>
                                    <th>ID:</th>
                                    </td>
                                    <td>
                                        <label class="alert-warning"><?= $row['idanalise'] ?></label>
                                    </td>
                                <? } ?>
                                <td>
                                <th>Protocolo:</th>
                                <td>
                                    <input id="idanalise<?= $row['idanalise'] ?>" name="analise_idanalise" type="hidden" value="<?= $row["idanalise"] ?>">
                                    <select <?= $desabledct ?> class="size15" id="idbioterioanalise<?= $row['idanalise'] ?>" name="analise_idbioterioanalise" onchange="setanalise(<?= $row['idanalise'] ?>);" vnulo>
                                        <option value=""></option>
                                        <? fillselect(BioensaioController::toFillSelect(BioensaioController::buscarTipoAnalises()), $row["idbioterioanalise"]); ?>
                                    </select>
                                </td>

                                <td>
                                    <? if (!empty($row["idbioterioanalise"])) { ?>
                                        <a class="fa fa-bars pointer hoverazul " title="Protocolo" onclick="janelamodal('?_modulo=bioterioanalise&_acao=u&idbioterioanalise=<?= $row["idbioterioanalise"] ?>')"></a>
                                    <? } else { ?>
                                        <a class="fa fa-plus-circle fa-2x verde btn-lg pointer" title="Novo Protocolo" onclick="janelamodal('?_modulo=bioterioanalise&_acao=i')"></a>
                                    <? } ?>
                                </td>
                                <th>Início:</th>
                                <td>
                                    <input idanalise="<?= $row['idanalise'] ?>" id="datadzero<?= $row['idanalise'] ?>" class="calendario datadzero" name="datadzero<?= $row['idanalise'] ?>" type="text" value="<?= dma($row["datadzero"]) ?>">
                                    <input id="datadzeroold<?= $row['idanalise'] ?>" name="datadzeroold<?= $row['idanalise'] ?>" type="hidden" value="<?= dma($row["datadzero"]) ?>">
                                </td>
                                <td></td>
                                <td>Quantidade:</td>
                                <td>
                                    <input id="qtd<?= $row['idanalise'] ?>" class="size4" name="analise_qtd" type="number" onchange="qtdanalise(this,<?= $row['idanalise'] ?>,<?= $_1_u_bioensaio_qtd ?>)" value="<?= $row['qtd'] ?>">
                                </td>
                                <?
                                if (!empty($row["idbioterioanalise"]) and $row["cria"] == 'N') { ?>
                                    <td>
                                        <button type="button" class="btn btn-success btn-xs" onclick="gerartestes(<?= $row['idanalise'] ?>)" title="Gerar Testes">
                                            <i class="fa fa-plus"></i>Testes
                                        </button>
                                    </td>
                                <? } ?>
                                <td align="right" colspan="2" class="nowrap">
                                    
                                        <?
                                        if (empty($row["idanalisepai"])) { ?>
                                            <div class="input-group input-group-sm">
                                                Controle:&nbsp;
                                                <? if (empty($row["idbioensaioctr"])) { ?>
                                                    <select <?= $desabledct ?> class="size10" name="idbioensaioctr" idbioensaioctr="<?= $row["idbioensaioctr"] ?>" onchange="setcontroleanalise(this,<?= $row["idanalise"] ?>)">
                                                        <option value="0" selected></option>
                                                        <?
                                                        fillselect(BioensaioController::toFillSelect(BioensaioController::buscarBioensaiosParaControle($_1_u_bioensaio_idpessoa,$_1_u_bioensaio_idbioensaio)), $row["idbioensaioctr"]);
                                                        ?>
                                                    </select>
                                                <? } else {

                                                    echo traduzid('bioensaio','idbioensaio','CONCAT("B",idregistro,"/",exercicio," - ",estudo)',$row['idbioensaioctr'])
                                                ?>
                                                    <span class="input-group-addon" title="Retirar Controle">
                                                        <i class="fa fa-eraser pointer" idbioensaioctr="<?= $row["idbioensaioctr"] ?>" onclick="resetcontroleanalise(this,<?= $row["idanalise"] ?>)"></i>
                                                    </span>
                                                    <span class="input-group-addon" title="Editar Controle">
                                                        <?
                                                            $modulo = BioensaioController::buscarModuloPorUnidade($idunidadepadrao,'bioensaio')
                                                        ?>
                                                        <a class="fa fa-bars pointer hoverazul" title="Bioensaio Ctr" onclick="janelamodal('?_modulo=<?= $modulo ?>&_acao=u&idbioensaio=<?= $row["idbioensaioctr"] ?>')"></a>
                                                    </span>
                                                <? } ?>
                                            </div>
                                        <?
                                        } else { //if(!empty($row["idanalisepai"]))
                                            $rwk = BioensaioController::buscarBioensaioDaAnalise($row["idanalisepai"]);
                                        ?>
                                            <label class="alert-warning">
                                                <? echo ("Controle:&nbsp;".$rwk["bioensaio"]);  ?>
                                            </label>
                                            <?
                                                $modulo = BioensaioController::buscarModuloPorUnidade($idunidadepadrao,'bioensaio');
                                            ?>
                                            <a class="fa fa-bars pointer hoverazul" title="Bioensaio Ctr" onclick="janelamodal('?_modulo=<?= $modulo ?>&_acao=u&idbioensaio=<?= $rwk["idbioensaio"] ?>')"></a>
                                        <?
                                        }
                                        ?>
                                </td>

                                <td>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <i class="fa fa-plus-circle  pull-right fa-lg cinza hoververde pointer" onclick="iservico(<?= $row["idanalise"] ?>)" title="Inserir nova Atividade"> &nbsp;&nbsp;Atividade</i>
                                </td>
                                <td rowspan="3">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <i title="Etiqueta de Atividade do Protocolo" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="imprimeEtiqueta(<?= $row["idanalise"] ?>,'analise')"> &nbsp;&nbsp;Etiqueta</i>
                                </td>
                                <? if (dprotocolo($row['idanalise']) == false) { ?>
                                    <td rowspan="3">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <i title="Excluir Protocolo" class="fa fa-trash pull-right fa-lg cinza pointer hoververmelho" onclick="deleteprotocolo(<?= $row['idanalise'] ?>)"> &nbsp;&nbsp;Excluir</i>
                                    </td>
                                <? } ?>
                            </tr>
                        </table>
                    </div>

                    <div class="panel-body">
                        <? if (!$row["idbioterioanalise"]) {
                            $row["idbioterioanalise"] = 1;
                        }
                        $ress = BioensaioController::buscarTestesDoEnsaio($row["idanalise"]);
                        ?>

                        <div class="conteudo">
                            <?
                            foreach ($ress as $k => $rows) {
                                if ($rows['status'] == "CONCLUIDO") {
                                    $cor = "#00ff004d";
                                } elseif ($rows['status'] == "INATIVO") {
                                    $cor = "#DCDCDC";
                                } else {
                                    $cor = "#ff634747";
                                }
                            ?>
                                <div style="background-color:<?= $cor ?>; " class="servico" title="<?= $rows['obs'] ?>" style="text-align: left;">
                                    <table>
                                        <tr>
                                            <td align="center" colspan="5" class="nowrap">
                                                <a class="pointer" tanalise="<?= $rows['idanalise'] ?>" tservico="<?= $rows['idservicobioterio'] ?>" dmadata="<?= $rows['dmadata'] ?>" dia="<?= $rows['dia'] ?>" difdias="<?= $rows['difdias'] ?> dias" status="<?= $rows['status'] ?>" observ="<?= $rows['obs'] ?>" onClick="uservico(this,<?= $rows['idservicoensaio'] ?>);">
                                                    <? echo ($rows['rotulo']); ?>
                                                </a>
                                                <?
                                                    $modulo = BioensaioController::buscarModuloPorUnidade($idunidadepadrao,'bioensaio');
                                                ?>
                                                <a title="Bioensaio Ctr" onclick="janelamodal('?_modulo=<?= $modulo ?>&_acao=u&idbioensaio=<?= $rows['idbioensaio'] ?>')"><?= $rows['registro'] ?></a>
                                                <?
                                                if ($rows['cria'] == 'N') {

                                                    echo (" (D " . $rows["diabioensaio"] . ")");
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" nowrap colspan="4">
                                                <font color="black">
                                                    <? echo ($rows['dmadata']); ?> <?= $rows['idade'] ?> dias
                                                </font>
                                            </td>
                                            <td align="center">
                                                <? if (empty($rows["idamostra"]) or $rows['status'] == "INATIVO") { ?>
                                                    <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="altservico(<?= $rows['idservicoensaio'] ?>)" title="Excluir servico"></i>
                                                <? } ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5">
                                                <hr style="border-color: #83887c; ">
                                            </td>
                                        </tr>
                                        <? listateste($rows['idservicoensaio']); ?>
                                        <tr>
                                            <td style="padding-bottom: 10px">
                                                <i id="novoteste" class="fa fa-plus-circle pull-right fa-lg cinzaclaro hoververde pointer" onclick="atnvteste(<?= $rows['idservicoensaio'] ?>)" title="Inserir novo teste"></i>
                                            </td>
                                            <td class="nowrap">
                                                <input name="qtd" id="qtdteste<?= $rows['idservicoensaio'] ?>" type="text" placeholder="qtd" value='' class='size2 hidden'>
                                            </td>
                                            <td>
                                                <select id="idtipoteste<?= $rows['idservicoensaio'] ?>" class="idprodserv size15 hidden" style="font-size: 9px;" name="#nameidtipoteste" onchange="novoTeste(<?= $rows["idanalise"] ?>,<?= $rows["idservicoensaio"] ?>)">
                                                    <option value="0" selected></option>
                                                    <?
                                                        fillselect(BioensaioController::toFillSelect(BioensaioController::buscarServicosParaEnsaio(getidempresa('t.idempresa', 'prodserv'),$idunidadepadrao)));
                                                    ?>
                                                </select>
                                                <input id="idservico<?= $rows['idservicoensaio'] ?>" value="<?= $rows["idservicoensaio"] ?>" type="hidden" style="font-size: 9px;" name="#nameidservicoensaio">
                                            </td>
                                        </tr>
                                    </table>

                                </div>
                            <?
                            } //while($rows=mysqli_fetch_assoc($ress))
                            ?>

                        </div>
                        <hr>
                        <table>
                            <tr>
                                <td></td>
                            </tr>
                        </table>
                        <? listalocal($_1_u_bioensaio_idbioensaio, $row['idanalise'], $row['datadzero'], $row['qtd']); ?>
                        <hr>
                        <div class="conteudo">
                            <table>
                                <td></td>
                                <th style=" padding-left: 8px">IDENTIFICADORES:</th>
                                <?
                                $sqlind = "select * from  identificador where idobjeto=" . $row['idanalise'] . " and tipoobjeto='analise' order by ididentificador";
                                $resind = d::b()->query($sqlind) or die("Erro ao buscar animais sql=" . $sqlind);
                                $qtdind = mysqli_num_rows($resind);
                                $collapse = "collapse";
                                $i = 555;
                                $lin = 0;
                                while ($rowind = mysqli_fetch_assoc($resind)) {
                                    $i = $i + 1;
                                    if ($lin == 10) {
                                        echo ('</tr><tr>');
                                        $lin = 0;
                                    }
                                    $lin = $lin + 1;
                                ?>

                                    <td>
                                        <input <?= $readonly2 ?> id="<?= $row['idanalise'] ?>" name="_<?= $i ?>_u_identificador_ididentificador" size="10" type="hidden" value="<?= $rowind['ididentificador'] ?>">
                                        <input <?= $readonly2 ?> name="_<?= $i ?>_u_identificador_identificacao" size="5" type="text" value="<?= $rowind['identificacao'] ?>">
                                    </td>
                                    <td>
                                        <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="dbioterioind(<?= $rowind['ididentificador'] ?>)" title="Excluir animal"></i>
                                    </td>
                                <?
                                }
                                if ($lin > 9) {
                                    echo ('</tr>');
                                }
                                ?>
                                <td>
                                    <i class="fa fa-plus-circle fa-1x  verde pointer" onclick="ibioterioind(<?= $row['idanalise'] ?>)" title="Inserir Animal"></i>
                                </td>
                            </table>
                        </div>
                        <hr>
                    </div>
                </div>
            </div>
        <?
                } //while($row=mysqli_fetch_assoc($res))
                $sqla = "select * from analise a where a.idobjeto = " . $_1_u_bioensaio_idbioensaio . " and a.objeto ='bioensaio'";
                $resa = d::b()->query($sqla) or die("Erro ao buscar analises do bioensaio");
                $qtda = mysqli_num_rows($resa);
                if ($qtda < 6) {
        ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="agrupamento novo">
                            Protocolo: <i class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer" onclick="novaanalise();" title="Inserir novo Protocolo"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?
                }
                if ($_1_u_bioensaio_idbioensaio) {
        ?>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <table>
                            <tr>
                                <td>
                                    Considerações Gerais:
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="panel-body">
                        <table style="width: 100%;">
                            <tr>
                                <td>
                                    <textarea style="height: 140px;" name="_1_<?= $_acao ?>_bioensaio_consideracoes"><?= $_1_u_bioensaio_consideracoes ?></textarea>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <table>
                            <tr>
                                <td>
                                    Conclusão do Teste:
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="panel-body">
                        <table style="width: 100%;">
                            <tr>
                                <td>
                                    <textarea style="height: 140px;" name="_1_<?= $_acao ?>_bioensaio_conclusao"><?= $_1_u_bioensaio_conclusao ?></textarea>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">

                        <table class="audit">
                            <tr>
                                <td rowspan="3">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <i title="Registro Operacional" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/relbioensaio.php?idbioensaio=<?= $_1_u_bioensaio_idbioensaio . '&_idempresa=' . cb::idempresa() ?>')"> &nbsp;&nbsp;Operacional</i>
                                </td>
                                <td rowspan="3">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <i title="Relatório do Biensaio" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/relbioensaiocbkp.php?incresult=Y&idbioensaio=<?= $_1_u_bioensaio_idbioensaio ?>&_modulo=<?= $_GET['_modulo'] . '&_idempresa=' . cb::idempresa() ?>')"> &nbsp;&nbsp;Bioensaio</i>
                                </td>
                                <td rowspan="3">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <i title="Impressão dos resultados" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/emissaoresultado.php?exerciciobiot=<?= $_1_u_bioensaio_exercicio ?>&idregistrob=<?= $_1_u_bioensaio_idregistro ?>')"> &nbsp;&nbsp;Resultados</i>
                                </td>
                            </tr>

                        </table>
                    </div>
                </div>
            </div>
            <?
                    if (!empty($_1_u_bioensaio_idbioensaio)) { // trocar p/ cada tela a tabela e o id da tabela
                        $_idModuloParaAssinatura = $_1_u_bioensaio_idbioensaio; // trocar p/ cada tela o id da tabela
                        require 'viewAssinaturas.php';
                    }
                    $tabaud = "bioensaio"; //pegar a tabela do criado/alterado em antigo
                    require 'viewCriadoAlterado.php';
            ?>
        <?
                }
        ?>
        <div id="servico" style="display: none">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <table>
                                <tr>
                                    <td align="right">
                                        <input id="idservicoensaio" name="" type="hidden" value="">
                                    </td>
                                    <td align="right">Dia:</td>
                                    <td nowrap>
                                        <input name="" id="dia" type="text" size="6" value="">
                                        <!--    <input  name="" id ="fdata" class="calendario"  type="text" size ="6" value="" onchange="calculDiff();">
                        <input  id ="fdata2" type="hidden"  type="text" size ="6" value="" >
                    -->
                                    </td>
                                    <td align="right"></td>
                                    <td nowrap>
                                        <select name="" id="ndropservico" value="">
                                            <? fillselect("select idservicobioterio,rotulo from  servicobioterio where status = 'ATIVO'  order by ordem"); ?>
                                        </select>
                                    </td>
                                    <td align="right">Status:</td>
                                    <td>
                                        <select name="" id="status" vnulo>
                                            <? fillselect("SELECT 'PENDENTE','Pendente' union select 'CONCLUIDO','Concluido' union select 'INATIVO','Inativo'"); ?>
                                        </select>
                                    </td>
                                    <td>
                                        <font color="red">
                                            <div id="obsdias"></div>
                                        </font>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td align="right" id="rotanalise">Análise:</td>
                                    <td colspan="3">
                                        <select name="" id="ndropanalise" value="">
                                            <? fillselect("select a.idanalise,b.tipoanalise 
                                            from analise a,bioterioanalise b
                                            where a.idobjeto = " . $_1_u_bioensaio_idbioensaio . " and a.objeto='bioensaio'
                                            and a.idbioterioanalise=b.idbioterioanalise order by b.tipoanalise"); ?>
                                        </select>
                                        <input name="" id="tipoobjeto" type="hidden" size="6" value="analise">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">Descr.:</td>
                                    <td colspan="5"><textarea name="" id="observ" style="width: 300px; height: 30px;"></textarea></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<? }
        }
    } //condição idplantel
?>
<?
function listalocal($_1_u_bioensaio_idbioensaio, $idanalise1, $datazero, $aqtd)
{
    global $_1_u_bioensaio_qtd, $_1_u_bioensaio_idplantel, $_1_u_bioensaio_idunidade;

    $rowp = BioensaioController::buscarLocaisEnsaio($idanalise1);
    if (!empty($datazero)) {
        if (!empty($rowp)) {
            $rowper = BioensaioController::buscarDatasDosServicos($idanalise1,$_1_u_bioensaio_idbioensaio);
            if (!empty($rowper)) { ?>
                <div align="center">
                    Período - <?= $rowper['r3inicio'] ?> à  <?= $rowper['fdmadata'] ?> - <?= $rowper['diasper'] ?> Dias
                </div>
                <div align="Center">
                    <br>
                    <?
                    if (!empty($rowp['idtag'])) {
                        $rowpb = BioensaioController::buscarGaiolasBioensaio($rowp['idtag']);
                        ?>
                            <a onclick="mostrarmodal(<?= $idanalise1 ?>)" style="cursor: pointer; font-size: 12px;"><i class="fa fa-home fa-lg" aria-hidden="true">&nbsp;&nbsp;<?= $rowpb['descricao'] ?></i></a>
                        <?} else {?>
                            <a onclick="mostrarmodal(<?= $idanalise1 ?>)" style="cursor: pointer; font-size: 14px;"><i class="fa fa-home vermelho blink fa-lg" aria-hidden="true">&nbsp;&nbsp;Selecionar Local</i></a>
                        <?}?>
                </div>
                <?
                $b = 0;
                $share = '';
                $share = share::odie(false)::otipo("cb::usr")::bioensaioTagPorIdempresa('t.idtag');
                $share = $share?$share:" t.idempresa = ".cb::idempresa();
                echo "<!-- $share -->";
                $res1x = BioensaioController::buscarExamesDeUmaGaiola($_1_u_bioensaio_idplantel,$rowper['r3data'],$rowper['fdata'],$_1_u_bioensaio_idunidade,$share);
                ?>
                <div class="linhacab3" id="modallocalbioensaio_<?= $idanalise1 ?>" style="background-color: transparent; display: none;">
                    <div class="conteudo">
                        <?
                        $i = 0;
                        $idbiobox = "";
                        foreach ($res1x as $k => $row1x) {
                            $disp = $row1x['lotacao'] - $row1x['ocup'];
                            $vagas = $disp - $aqtd;
                            //echo $disp;
                            if ($b == 8) {
                                echo ("</tr>");
                                echo ("<tr>");
                                $b = 0;
                            }

                            if ($rowp['idtag'] == $row1x['idtag'] and $rowp['idanalise'] == $idanalise1) {
                                $cor = "#90EE91";
                                $icone = "fa fa-times-circle-o fa-2x cinza hoververmelho btn-lg pointer";
                                $funcao = "dellocalensaio";
                            } else {
                                $cor = " ";
                                $icone = "fa fa-check-circle-o fa-2x verde btn-lg pointer";
                                $funcao = "iulocalensaio";
                            } ?>

                            <? if ($idbiobox != $row1x['idtagpai']) { ?>

                                <? if ($i > 0) { //fecha div?>
                                    </div>
                                <? } ?>

                <div style="cursor: pointer;" class="linhacab">
                    <div class="colunainv" style="width:100%"><?= $row1x['descrpai'] ?></div>
                <? } //desenha o biobox (abre div)
                ?>
                <?
                    // $rest = BioensaioController::verificaSeHaVagasNaGaiola($row1x['idtag']);
                    // $qtdt = count($rest);
                ?>
                <table align="center" style="margin: 0;margin-right:0;width:100%">
                    <tr>
                        <td style="background-color:<?= $cor ?>;">
                            <? if ($vagas >= 0) { ?>
                                <i class="<?= $icone ?>" onclick="<?= $funcao ?>(<?= $row1x['idtag'] ?>,<?= $rowp['idlocalensaio'] ?>,<?= $idanalise1 ?>,'<?= $row1x['rot'] ?>')"></i>
                            <? } else { ?>
                                <i class="fa fa-ban fa-2x hoververmelho" title="INDISPONIVEL"></i>
                            <? } ?>
                        </td>
                        <td style="color: red;background-color:<?= $cor ?>; font-size: 12px;" align="center">
                            <font style="color: black"><?= $row1x['rot'] ?></font>
                        </td>
                        <td style="background-color:<?= $cor ?>;width:63px"></td>
                    </tr>
                    <tr>
                        <td style="background-color:<?= $cor ?>;"></td>
                        <td style="color: red;background-color:<?= $cor ?>; font-size: 12px;" align="center">
                            Disp.:<?= $disp ?>
                        </td>
                        <td style="background-color:<?= $cor ?>;"></td>
                    </tr>
                    <tr>
                        <td style="background-color:<?= $cor ?>;"></td>
                        <td style="color: red;background-color:<?= $cor ?>; font-size: 10px; vertical-align: top;" align="center" nowrap>
                            <? if ($row1x['qtd'] > 0) {
                                // foreach ($rest as $k1 => $rowt) {
                                    echo ('<a target="_blank" href="?_modulo=bioensaio&_acao=u&idbioensaio=' . $row1x['idbioensaio'] . '">' . $row1x['qtd'] . " " . $row1x['especie'] . " " . $row1x['fimbio'] . '</a>' . "<br>");
                                // }
                            }
                            ?>
                        </td>
                        <td style="background-color:<?= $cor ?>;"></td>
                    </tr>
                </table>
            <?
                $b = $b + 1;
                $idbiobox = $row1x['idtagpai'];
                $i++;
                    } //while($row1x=mysqli_fetch_assoc($res1x))
            ?>
                </div>
                </div>
                </div>
<? }
        } // if($qtdp>0)

    } //if(!empty($datazero))

} //function listalocal($_1_u_bioensaio_idbioensaio)
?>
<input type="hidden" id="idPessoaLogada" value="<?=$_SESSION["SESSAO"]["IDPESSOA"]?>">
<?
require_once(__DIR__."/js/bioensaio_js.php");
?>