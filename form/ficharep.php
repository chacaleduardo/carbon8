<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__ . "/controllers/ficharep_controller.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetros chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "ficharep";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idficharep" => "pk"
);

$idunidadepadrao = getUnidadePadraoModulo($_GET["_modulo"]);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
// GVT - 28/01/2020 - comentado a parte de idempresa para não parar as atividades no bioensaio
// voltar idempresa posteriormente.
$pagsql = "select * from ficharep where idficharep = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
$idplantel = traduzid("especiefinalidade", "idespeciefinalidade", "idplantel", $_1_u_ficharep_idespeciefinalidade);


$arrCli = FicharepController::buscarClientesParaEstudo(getidempresa('idempresa', 'pessoa'));
$jCli = $JSON->encode($arrCli);

if (!empty($idunidadepadrao) && !empty($idplantel)) {
    $arrProd = FicharepController::buscarLotesParaUsoNaFicha($idunidadepadrao, $idplantel, getidempresa('p.idempresa', 'prodserv'));
    $jProd = $JSON->encode($arrProd);
} else {
    $arrProd = [];
    $jProd = $JSON->encode($arrProd);
}
//Recupera os produtos a serem selecionados para uma nova Formalização

?>
<style>
    .servico {
        border: none;
        width: 145px;
        min-height: 40px;
        *background-color: #FF6347;
        float: left;
        border-radius: 10px;
        margin: 2px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .conteudo {
        align-items: center;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
    }

    .conteudo1 {
        /* align-items: center;*/
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        /* justify-content: center; */
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

    .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .dropdown-menu.inner {
        max-height: 450px !important;
    }

    .form-group .select-picker button {
        border-radius: .5rem 0 0 .5rem !important; 
        height: 30px;
    }

    .form-group .btn:not(.dropdown-toggle) {
        border-radius: 0 .5rem .5rem 0 !important;
        height: 30px;
    }
</style>
<div class="col-md-6">
    <div class="panel panel-default">
            <div class="panel-heading">
                <table style="width: 100%">
                    <tr>
                        <td>
                            <strong>ID.:</strong>
                            <? if (!empty($_1_u_ficharep_idficharep)) { ?>
                                <label class="alert-warning"><?= $_1_u_ficharep_idficharep ?></label>
                            <? } ?>
                            <input name="_1_<?= $_acao ?>_ficharep_idficharep" type="hidden" value="<?= $_1_u_ficharep_idficharep ?>">
                        </td>
                        <td>
                            <input name="_1_<?= $_acao ?>_ficharep_idunidade" type="hidden" value="<?= $idunidadepadrao ?>">
                        </td>
                        <td></td>
                        <td></td>
                        </td>
                        <td align="right">Status:</td>
                        <td>
                            <select class="size10" name="_1_<?= $_acao ?>_ficharep_status">
                                <? fillselect("select 'EM ANDAMENTO','Em Andamento' union select 'CONCLUIDO','Concluido' union select 'CANCELADO','Cancelado'", $_1_u_ficharep_status); ?>
                            </select>
                        </td>
                        <td><i title="Imprimir" class="fa fa-print pull-right fa-lg cinza hoverazul" onclick="janelamodal('report/ficharep.php?idficharep=<?= $_1_u_ficharep_idficharep . '&_idempresa=' . cb::idempresa() ?>')"></i>
            </div>
            </td>
            </tr>
            <tr>
                <td colspan="4">
                    <table style="width: 100%">
                        <tr>
                            <td style="width: 5%;">Espécie:</td>
                            <td align="left">
                                <? if (empty($_1_u_ficharep_idespeciefinalidade)) { ?>
                                    <select class="size10" id="idespeciefinalidade" name="_1_<?= $_acao ?>_ficharep_idespeciefinalidade" vnulo>
                                        <option></option>
                                        <? fillselect(FicharepController::toFillSelect(FicharepController::listarEspecieFinalidadePorUnidade($idunidadepadrao)), $_1_u_ficharep_idespeciefinalidade); ?>
                                    </select>
                                <? } else {
                                    $especie = traduzid("especiefinalidade", "idespeciefinalidade", "concat(tipoespecie,'-',finalidade)", $_1_u_ficharep_idespeciefinalidade);
                                    echo ($especie);
                                ?>
                                    <input name="_1_<?= $_acao ?>_ficharep_idespeciefinalidade" type="hidden" id="idespeciefinalidade" value="<?= $_1_u_ficharep_idespeciefinalidade ?>">
                                <? } ?>
                            </td>


                        </tr>
                    </table>
                </td>
                <?
                if (!empty($_1_u_ficharep_idficharep)) {
                ?>
                    <td align="right">Lote:</td>
                    <? if (empty($_1_u_ficharep_idlote)) { ?>
                        <td>
                            <input id="idlote" class="size10" name="_1_<?= $_acao ?>_ficharep_idlote" cbvalue="<?= $_1_u_ficharep_idlote ?>" value="<?= $arrProd[$_1_u_ficharep_idlote]["descr"] ?>" <? if ($_acao == 'u') echo "vnulo"; ?>>
                        </td>
                    <? } else { ?>
                        <? $rl = FicharepController::buscarDescrDoLoteFicharep($_1_u_ficharep_idlote); ?>
                        <td>
                            <label class="alert-warning"><?= $rl['descr'] ?></label>
                            &nbsp;<i class="fa fa-trash hoververmelho pointer" onclick="deletalote(<?= $_1_u_ficharep_idficharep ?>,'_x_u_ficharep_idlote')"></i>
                            &nbsp;<a class="fa fa-bars pointer" onclick="janelamodal('?_modulo=<?= FicharepController::buscarModuloPorUnidade($idunidadepadrao, 'lote') ?>&_acao=u&idlote=<?= traduzid('lotefracao', 'idlotefracao', 'idlote', $_1_u_ficharep_idlote) ?>')"></a>
                            <input type="hidden" class="size10" name="_1_<?= $_acao ?>_ficharep_idlote" value="<?= $_1_u_ficharep_idlote ?>">
                        </td>
                    <? } ?>
                    <?
                    $especie = traduzid("vwespeciefinalidade", "idespeciefinalidade", "especie", $_1_u_ficharep_idespeciefinalidade);
                    if (!preg_match('/Aves/', $especie)) { ?>
            <tr>
                <td colspan="3"></td>
                <td colspan="2" align="right">2º Lote:</td>
                <td nowrap>
                    <?
                        $idplantel = traduzid("especiefinalidade", "idespeciefinalidade", "idplantel", $_1_u_ficharep_idespeciefinalidade);
                        if (!empty($_1_u_ficharep_idlote)) {
                            $status = traduzid("lote", "idlote", "status", $_1_u_ficharep_idlote2);
                        }
                        //LTM - 22-09-2020: Alterado para pegar os lotes dos produtos de Ovos(46) e Animais (187)
                    ?>
                    <? if (empty($_1_u_ficharep_idlote2)) { ?>
                        <input id="idlote2" class="size10" name="_1_<?= $_acao ?>_ficharep_idlote2" cbvalue="<?= $_1_u_ficharep_idlote2 ?>" value="<?= $arrProd[$_1_u_ficharep_idlote2]["descr"] ?>" <? if ($_acao == 'u') echo "vnulo"; ?>>
                    <? } else { ?>
                        <? $rl = FicharepController::buscarDescrDoLoteFicharep($_1_u_ficharep_idlote2); ?>
                        <label class="alert-warning"><?= $rl['descr'] ?></label>
                        &nbsp;<i class="fa fa-trash hoververmelho pointer" onclick="deletalote(<?= $_1_u_ficharep_idficharep ?>,'_x_u_ficharep_idlote2')"></i>
                        &nbsp;<a class="fa fa-bars pointer" onclick="janelamodal('?_modulo=<?= FicharepController::buscarModuloPorUnidade($idunidadepadrao, 'lote') ?>&_acao=u&idlote=<?= traduzid('lotefracao', 'idlotefracao', 'idlote', $_1_u_ficharep_idlote2) ?>')"></a>
                        <input type="hidden" class="size10" name="_1_<?= $_acao ?>_ficharep_idlote2" value="<?= $_1_u_ficharep_idlote2 ?>">
                    <? } ?>
                </td>
            </tr>
            <?
                        $rowl = FicharepController::buscarLotePorIdObjetosoliporTipoobjetosolipor($_1_u_ficharep_idficharep, 'ficharep');
                        if (!empty($rowl)) { ?>
                <tr>
                    <td colspan="4"></td>
                    <td align="right">
                        Lote Criado:
                    </td>
                    <td>
                        <label class="alert-warning"><?= $rowl['partida'] . "/" . $rowl['exercicio'] ?></label>
                        <a class="fa fa-bars" onclick="janelamodal('?_modulo=<?= FicharepController::buscarModuloPorUnidade($idunidadepadrao, 'lote') ?>&_acao=u&idlote=<?= $rowl['idlote'] ?>')"></a>
                        <input type="hidden" readonly value="<?= $rowl['idlote'] ?>">
                    </td>
                </tr>
            <? } else { ?>
                <tr>
                    <td colspan="5"></td>
                    <td>
                        Criar Lote:<i class="fa fa-plus-circle fa-1x verde btn-lg pointer" title="Criar um agente" onclick="inovolote()"></i>
                    </td>
                </tr>
            <? } ?>
        <? } ?>
    <? } ?>
    </tr>
    </table>
    </div>
        <div class="panel-body">
        <? if (!empty($_1_u_ficharep_idficharep)) { ?>
            <table>
                <?
                if (!empty($_1_u_ficharep_idespeciefinalidade)) {
                    $especie = traduzid("vwespeciefinalidade", "idespeciefinalidade", "especie", $_1_u_ficharep_idespeciefinalidade);
                }
                if (!empty($_1_u_ficharep_inicio) and !empty($_1_u_ficharep_fim)) {
                    $inicio = (implode("-", array_reverse(explode("/", substr($_1_u_ficharep_inicio,0,10)))));
                    $fim = (implode("-", array_reverse(explode("/", $_1_u_ficharep_fim))));

                    $rowd = date_diff(new DateTime($fim),new DateTime($inicio));
                    $dias = $rowd->days." Dia(s)";
                }
                ?>
                <tr>
                    <td align="right">Início:</td>
                    <td>
                        <input name="_1_<?= $_acao ?>_ficharep_inicio" id="inicio" class="calendario" size="10" value="<?= $_1_u_ficharep_inicio ?>" vnulo autocomplete="off">
                        <input name="_ficharep_oldinicio" type="hidden" value="<?= substr($_1_u_ficharep_inicio, 0, -9); ?>">
                        <script>
                            $('#inicio').on('apply.daterangepicker', function(ev, picker) {
                                // console.log(picker.startDate.format('YYYY-MM-DD'));

                                setdt(picker.startDate.format('YYYY-MM-DD'));
                            });
                        </script>
                    </td>
                    <? if (preg_match('/Aves/', $especie)) { ?>
                        <td style=" color:red">Preencher com a hora da incubação</td>
                    <? } else { ?>
                        <td></td>
                    <? } ?>
                </tr>
                <tr>
                    <td align="right">Fim:</td>
                    <td>
                        <input name="_1_<?= $_acao ?>_ficharep_fim" id="fim" class="calendario" size="10" value="<?= $_1_u_ficharep_fim ?>" vnulo autocomplete="off">
                    </td>
                    <td id="obsdias" style="color: red;"><?= $dias ?></td>
                </tr>
                <? if (preg_match('/Aves/', $especie)) { ?>
                    <tr>
                        <td align="right">Quant. Ovos:</td>
                        <td><input name="_1_<?= $_acao ?>_ficharep_qtd" size="5" type="text" value="<?= $_1_u_ficharep_qtd ?>" vnulo></td>
                    </tr>
                    <tr>
                        <td align="right">Quebrados:</td>
                        <td><input name="_1_<?= $_acao ?>_ficharep_quebrados" size="5" type="text" value="<?= $_1_u_ficharep_quebrados ?>"></td>
                    </tr>
                    <tr>
                        <td align="right">Claros:</td>
                        <td><input name="_1_<?= $_acao ?>_ficharep_claros" size="5" type="text" value="<?= $_1_u_ficharep_claros ?>"></td>
                    </tr>
                    <tr>
                        <td align="right">Escuros:</td>
                        <td><input name="_1_<?= $_acao ?>_ficharep_escuros" size="5" type="text" value="<?= $_1_u_ficharep_escuros ?>"></td>
                    </tr>
                    <tr>
                        <td align="right">Mortos (1-5) dias:</td>
                        <td><input name="_1_<?= $_acao ?>_ficharep_mortos1a5" size="5" type="text" value="<?= $_1_u_ficharep_mortos1a5 ?>"></td>
                    </tr>
                    <tr>
                        <td align="right">Mortos (6-10) dias:</td>
                        <td><input name="_1_<?= $_acao ?>_ficharep_mortos10" size="5" type="text" value="<?= $_1_u_ficharep_mortos10 ?>"></td>
                    </tr>
                    <tr>
                        <td align="right">Mortos (10-19) dias:</td>
                        <td><input name="_1_<?= $_acao ?>_ficharep_mortosac10" size="5" type="text" value="<?= $_1_u_ficharep_mortosac10 ?>"></td>
                    </tr>
                    <tr>
                        <td align="right">Mortos acima 19 dias:</td>
                        <td><input name="_1_<?= $_acao ?>_ficharep_mortosac19" size="5" type="text" value="<?= $_1_u_ficharep_mortosac19 ?>"></td>
                    </tr>
                    <?
                    $menos = 0;
                    if ($_1_u_ficharep_quebrados > 0) {
                        $menos = $menos + $_1_u_ficharep_quebrados;
                    }
                    if ($_1_u_ficharep_claros > 0) {
                        $menos = $menos + $_1_u_ficharep_claros;
                    }
                    if ($_1_u_ficharep_escuros > 0) {
                        $menos = $menos + $_1_u_ficharep_escuros;
                    }
                    if ($_1_u_ficharep_mortos1a5 > 0) {
                        $menos = $menos + $_1_u_ficharep_mortos1a5;
                    }
                    if ($_1_u_ficharep_mortos10 > 0) {
                        $menos = $menos + $_1_u_ficharep_mortos10;
                    }
                    if ($_1_u_ficharep_mortosac10 > 0) {
                        $menos = $menos + $_1_u_ficharep_mortosac10;
                    }
                    if ($_1_u_ficharep_mortosac19 > 0) {
                        $menos = $menos + $_1_u_ficharep_mortosac19;
                    }
                    if ($menos < 1) {
                        $menos = 0;
                    }
                    $_1_u_ficharep_utilizados = $_1_u_ficharep_qtd - $menos;
                    ?>
                    <tr>
                        <td align="right">Utilizados:</td>
                        <td> <label class="alert-warning"><?= $_1_u_ficharep_utilizados ?></label>
                            <input name="_1_<?= $_acao ?>_ficharep_utilizados" size="5" type="hidden" type="text" value="<?= $_1_u_ficharep_utilizados ?>">
                        </td>
                    </tr>
                <?

                } else { ?>
                    <tr>
                        <td align="right">Quant. Fêmeas:</td>
                        <td><input name="_1_<?= $_acao ?>_ficharep_qtd" size="5" type="text" value="<?= $_1_u_ficharep_qtd ?>"></td>
                    </tr>

                    <tr>
                        <td align="right">Utilizados:</td>
                        <td><input name="_1_<?= $_acao ?>_ficharep_utilizados" size="5" type="text" value="<?= $_1_u_ficharep_utilizados ?>"></td>
                    </tr>
                <?
                } //if($especie=="Aves"){	    

                if (!empty($_1_u_ficharep_utilizados) and !empty($_1_u_ficharep_qtd) and preg_match('/Aves/', $especie)) {
                    $pnascidos = ($_1_u_ficharep_utilizados * 100) / $_1_u_ficharep_qtd;
                ?>
                    <tr>
                        <td align="right">Rendimento:</td>
                        <td>
                            <font color="red"><?= $pnascidos ?></font>
                        </td>
                    </tr>
                <? }
                $tgest = traduzid("especiefinalidade", "idespeciefinalidade", "gestacao", $_1_u_ficharep_idespeciefinalidade);

                if (!empty($_1_u_ficharep_inicio) and !empty($tgest) and empty($_1_u_ficharep_fim)) {
                    $inicio = implode("-", array_reverse(explode("/", $_1_u_ficharep_inicio)));
                    $_1_u_ficharep_fim = date('Y-m-d', strtotime($inicio. ' + '.$tgest.' days'));
                }
                ?>
                <tr>
                    <td align="right">Obs:</td>
                    <td colspan="3">
                        <textarea name="_1_<?= $_acao ?>_ficharep_obs"><?= $_1_u_ficharep_obs ?></textarea>
                    </td>
                </tr>
            </table>
        <? } ?>
        </div>
    </div>
</div>

<?
if (!empty($_1_u_ficharep_idficharep) and !empty($_1_u_ficharep_inicio)) {
?>

    <div class="col-md-6">
        <?
        $res =FicharepController::buscarBioensaiosDaFichadeRep($_1_u_ficharep_idficharep);
        $i = 1;
        foreach ($res as $id => $items) {
            $i = $i + 1;
        ?>
            <div class="panel panel-default">
                <div style="cursor: pointer;" class="panel-heading" data-toggle="collapse" href="#localInfo<?= $id ?>">
                    <div class="row d-flex align-items-center">
                        <span class="mr-2">Bioensaios (<?= count($items['bioensaios']) ?>)</span>
                        <div>
                            <label class="alert-warning mr-2">B<?= $items["idregistro"] ?>-<?= $items["exercicio"] ?></label>
                            <a class="fa fa-bars pointer hoverazul" title="Estudo" onclick="janelamodal('?_modulo=<?= FicharepController::buscarModuloPorUnidade($idunidadepadrao,'bioensaio') ?>&_acao=u&idbioensaio=<?= $items["idbioensaio"] ?>')"></a>
                        </div>
                    </div>
                </div>
                <div class="panel-body collapse" id="localInfo<?= $id ?>">
                    <? foreach($items['bioensaios'] as $item) { ?>
                        <div class="agrupamento">
                            <div class="d-flex flex-wrap w-100 mb-3">
                                <div class="form-group col-xs-5">
                                    <label for="">Protocolo:</label>
                                    <input id="idanalise<?= $i ?>" name="analise_idanalise" type="hidden" value="<?= $item["idanalise"] ?>">
                                    <div class="d-flex w-100">
                                        <select class="col-xs-10 select-picker p-0" id="idbioterioanalise<?= $i ?>" name="analise_idbioterioanalise" onchange="setanalise(<?= $i ?>,<?= $item['bqtd'] ?>);" vnulo data-live-search="true">
                                            <option value=""></option>
                                            <? fillselect(BioterioAnaliseQuery::buscarTipoAnalises(), $item["idbioterioanalise"]); ?>
                                        </select>
                                        <? if (!empty($item["idbioterioanalise"])) { ?>
                                            <div class="inline-block btn btn-primary col-xs-2 d-flex align-items-center justify-content-center" onclick="janelamodal('?_modulo=bioterioanalise&_acao=u&idbioterioanalise=<?= $item['idbioterioanalise'] ?>')">
                                                <span class="fa fa-bars text-white pointer" title="Protocolo"></span>
                                            </div>
                                        <? } ?>
                                    </div>
                                </div>
                                <div class="form-group col-xs-2">
                                    <label for="">Qtd:</label>
                                    <input id="analiseqtd<?= $i ?>" name="analise_qtd" type="hidden" value="<?= $item["qtd"] ?>">
                                    <input class="form-control" id="bioensaioqtd<?= $i ?>" name="bioensaio_qtd<?= $i ?>" onchange="qtdanalise(this,<?= $item['idanalise'] ?>,<?= $item['bqtd'] ?>,<?= $item['idbioensaio'] ?>)" value="<?= $item["qtd"] ?>">
                                </div>
                            </div>
                            <?$ress = BioensaioController::buscarTestesDoEnsaio( $item["idanalise"]);?>

                            <div class="conteudo">
                                <?
                                foreach ($ress as $k => $rows) {
                                    if ($rows['status'] == "CONCLUIDO") {
                                        $cor = "#00FF00";
                                    } elseif ($rows['status'] == "INATIVO") {
                                        $cor = "#DCDCDC";
                                    } else {
                                        $cor = "#FF6347";
                                    }
                                ?>
                                    <div tservico="<?= $rows['rotulo'] ?>" dmadata="<?= dma($rows['data']) ?>" dia="<?= $rows['dia'] ?>" difdias="<?= $rows['difdias'] ?> dias" status="<?= $rows['status'] ?>" observ="<?= $rows['obs'] ?>" style="background-color:<?= $cor ?>; cursor: pointer;" class="servico" title="<?= $rows['obs'] ?>" style="text-align: left;" onClick="inovoservico(this,<?= $rows['idservicoensaio'] ?>);">
                                        <table>
                                            <tr>
                                                <td align="center">
                                                    <font color="black">
                                                        <? echo ($rows['rotulo']); ?>
                                                    </font>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" nowrap>
                                                    <font color="black">
                                                        <? echo (dma($rows['data'])); ?> <?= $rows['difdias'] ?> dias
                                                    </font>
                                                </td>

                                            </tr>
                                        </table>
                                    </div>
                                    <? if ($rows['servico'] != 'ABATE' and $rows['servico'] != 'ALOJAMENTO' and $rows['servico'] != 'TRANSFERENCIA') { ?>
                                        <table>
                                            <tr>
                                                <td align="center">
                                                    <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="altservico(<?= $rows['idservicoensaio'] ?>,'CANCELADO')" title="Excluir <?= $rows['servico'] ?>"></i>
                                                </td>
                                            </tr>

                                        </table>
                                <?
                                    }
                                } // while($rows=mysqli_fetch_assoc($ress))
                                ?>
                            </div>
                        </div>
                    <?}?>
                </div>
            </div>
        <? } //while($row=mysqli_fetch_assoc($res))

        if (!empty($_1_u_ficharep_inicio) and !empty($_1_u_ficharep_fim)) { ?>

            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="agrupamento novo">
                        <?
                        if (!preg_match('/Aves/', $especie)) {
                            $rowl = FicharepController::buscarLotePorIdObjetosoliporTipoobjetosolipor($_1_u_ficharep_idficharep,'ficharep');
                            if (!empty($rowl)) {
                                if ($rowl['status'] == "ABERTO" or empty($_1_u_ficharep_idlote2)) {
                                    echo "Não é possivel criar grupos com o lote aberto.";
                                } else { ?>
                                    <i class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer" onclick="novogrupo();" title="Novo Grupo"></i>
                                <? }
                            }
                        } else {
                            if (empty($_1_u_ficharep_idlote)) {
                                echo "Não é possível criar grupos com lote vazio.";
                            } else {?>
                                <i class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer" onclick="novogrupo();" title="Novo Grupo"></i>
                            <?}
                        } ?>
                    </div>
                </div>
            </div>
        <?
        } //if(!empty($_1_u_ficharep_inicio) and !empty($_1_u_ficharep_fim))
        ?>
    </div>
<?
} //if(!empty($_1_u_ficharep_idficharep))
?>



<?
if (!empty($_1_u_ficharep_idficharep)) { // trocar p/ cada tela a tabela e o id da tabela
    $_idModuloParaAssinatura = $_1_u_ficharep_idficharep; // trocar p/ cada tela o id da tabela
    require 'viewAssinaturas.php';
}
$tabaud = "ficharep"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
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
                            <td align="right">Inà­cio:</td>
                            <td nowrap>
                                <input name="" id="fdata" class="calendario" type="text" size="6" value="" onchange="calculDiff();">
                                <input id="fdata2" type="hidden" type="text" size="6" value="">
                            </td>
                            <td align="right"></td>
                            <td nowrap>
                                <input name="" id="dia" type="hidden" size="6" value="">
                            </td>
                            <td align="right">Status:</td>
                            <td>
                                <select name="" id="status" vnulo>
                                    <? fillselect("SELECT 'PENDENTE','Pendente' union select 'CONCLUIDO','Concluido' union select 'INATIVO','Inativo'", $Â»1Â»uÂ»servicoensaioÂ»status); ?>
                                </select>
                            </td>
                            <td>
                                <font color="red">
                                    <div id="obsdias"></div>
                                </font>
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
<div id="bioensaio" style="display: none">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?
                    if (preg_match('/Aves/', $especie) and !empty($_1_u_ficharep_idlote)) {
                        $rowl = FicharepController::buscarLoteUsadoNaFicharep($_1_u_ficharep_idlote);
                    } else {
                        $rowl = FicharepController::buscarLotePorIdObjetosoliporTipoobjetosolipor($_1_u_ficharep_idficharep,'ficharep');
                    }
                    ?>
                    <table>
                        <tr>
                            <td class="hide" align="right">Espécie/Finalidade:</td>
                            <td nowrap>
                                <? $especie = traduzid("especiefinalidade", "idespeciefinalidade", "concat(tipoespecie,'-',finalidade)", $_1_u_ficharep_idespeciefinalidade);
                                ?>
                                <input type="hidden" class="size20" readonly value="<?= $especie ?>">
                                <input type="hidden" id="b_lotepd" value="<?= $rowl['idlote'] ?>">
                            </td>
                            <td align="right">Quantidade de Estudos a Criar:</td>
                            <td>
                                <input id="b_estudos" name="b_i_estudos" size="10" type="text">
                            </td>
                            <td align="right">Quantidade de Animais por Estudo:</td>
                            <td nowrap>
                                <input id="b_qtd" name="b_i_qtd" size="10" type="text">
                                <input type="hidden" id="b_idficharep" name="b_i_idficharep" value="<?= $_1_u_ficharep_idficharep ?>">
                                <input type="hidden" id="b_nascimento" name="b_i_nascimento" value="<?= $_1_u_ficharep_fim ?>">
                                <input type="hidden" id="b_idespeciefinalidade" name="b_i_idespeciefinalidade" value="<?= $_1_u_ficharep_idespeciefinalidade ?>">
                                <input type="hidden" id="b_idunidade" name="b_i_idunidade" value="<?= $_1_u_ficharep_idunidade ?>">
                                <input type="hidden" id="b_idlote" name="b_i_idlote" value="<?= $_1_u_ficharep_idlote ?>">
                            </td>
                            <td align="right">Status:</td>
                            <td>
                                <select id="bioensaio_status" name="b_i_status">
                                    <? fillselect(" select 'DISPONIVEL','Disponível' union select 'RESERVADO','Reservado'"); ?>
                                </select>
                            </td>
                            <td>

                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="novolote" style="display: none">
    <div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <table>
                            <tr>
                                <td align="right"><strong>Lote:</strong></td>
                                <td>
                                    <select class="size30" id="idprodservlote" name="">
                                        <option></option>
                                        <? fillselect(FicharepController::toFillSelect(FicharepController::buscarProdutoParaFicharep($idunidadepadrao,$idplantel))); ?>
                                    </select>
                                    <input id="idlotelote" name="" type="hidden" value="">
                                    <input id="statuslote" name="" type="hidden" value="ABERTO">
                                    <input id="idunidade" name="" type="hidden" value="<?= $idunidadepadrao ?>">
                                    <input id="exerciciolote" name="" type="hidden" value="<?= date("Y") ?>">
                                    <input id="tipoobjetolote" name="" type="hidden" value="ficharep">
                                    <input id="idobjetolote" name="" type="hidden" value="<?= $_1_u_ficharep_idficharep ?>">
                                </td>
                                <td>Qtd:</td>
                                <td>
                                    <input class="size5" id="qtdprod" name="" type="number" value="" vnulo>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?
function listalocal($_1_u_bioensaio_idbioensaio, $idanalise1, $datazero)
{
    global $_1_u_ficharep_idespeciefinalidade, $idunidadepadrao;

    $idespeciefinalidade = traduzid('especiefinalidade', 'idespeciefinalidade', 'idplantel', $_1_u_ficharep_idespeciefinalidade);

    if (!empty($datazero)) {
        $rowp =  BioensaioController::buscarLocaisEnsaio($idanalise1);
        if (!empty($rowp)) {
            $rowper = BioensaioController::buscarDatasDosServicos($idanalise1,$_1_u_bioensaio_idbioensaio);
            if (!empty($rowper)) { ?>
                <div align="Center">
                    <br>
                    <?
                    if (!empty($rowp['idtag'])) {
                        $rowpb = BioensaioController::buscarGaiolasBioensaio($rowp['idtag']);
                        ?>
                        <a onclick="mostrarmodal(<?= $idanalise1 ?>)" style="cursor: pointer; font-size: 12px;">
                            <i class="fa fa-home fa-lg" aria-hidden="true">&nbsp;&nbsp;<?= $rowpb['descricao'] ?></i>
                        </a>
                    <?} else {?>
                        <a onclick="mostrarmodal(<?= $idanalise1 ?>)" style="cursor: pointer; font-size: 14px;">
                            <i class="fa fa-home vermelho blink fa-lg" aria-hidden="true">&nbsp;&nbsp;Selecionar Local</i>
                        </a>
                    <?}?>
                </div>
                <?
                $b = 0;
                $share = '';
                $share = share::odie(false)::otipo("cb::usr")::bioensaioTagPorIdempresa('t.idtag');
                $share = $share?$share:" t.idempresa = ".cb::idempresa();
                $res1x = BioensaioController::buscarExamesDeUmaGaiola($idespeciefinalidade,$rowper['r3data'],$rowper['fdata'],$idunidadepadrao,$share);
                $qtd1x = count($res1x);
                if ($qtd1x > 0) { ?>
                    <div class="linhacab3" id="modallocalbioensaio_<?= $idanalise1 ?>" style="display: none; background-color: transparent;">
                        <div class="conteudo1">
                            <?
                            $i = 0;
                            $idbiobox = '';
                            foreach ($res1x as $k => $row1x) {
                                //echo("ocup=".$row1x['ocup']."+".$_1_u_bioensaio_qtd." <= ".$row1x['lotacao']);
                                if ((($row1x['ocup'] + $rowper['qtd']) <= ($row1x['lotacao'])) or ($rowp['idtag'] == $row1x['idtag'])) {
                                    $disp = $row1x['lotacao'] - $row1x['ocup'];
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

                                        <? if ($i > 0) { //fecha div
                                        ?>
                        </div>
                    <? } ?>

                    <div style="cursor: pointer;" class="linhacab">
                        <div class="colunainv" style="width:100%"><?= $row1x['descrpai'] ?></div>
                    <? } //desenha o biobox (abre div)
                    ?>
                    <?
                        $rest = BioensaioController::verificaSeHaVagasNaGaiola($row1x['idtag']);
                        $qtdt = count($rest);
                    ?>
                    <table align="center" style="margin: 0;margin-right:0;width:100%">
                        <tr>
                            <td style="background-color:<?= $cor ?>;">
                                <? if ($disp >= 0) { ?>
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
                                <? if ($qtdt > 0) {
                                        foreach ($rest as $k => $rowt) {
                                            echo ($rowt['qtd'] . " " . $rowt['especie'] . " " . $rowt['fimbio'] . "<br>");
                                        }
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
                                }
                                //if((($row1x['ocup']+$_1_u_bioensaio_qtd)<=($row1x['lotacao'])) or($rowp['idlocal']==$row1x['idlocal']))

                            } //while($row1x=mysqli_fetch_assoc($res1x))
            ?>
                    </div>
                    </div>
                    </div>
                <? } else { ?>
                    <div align='Center' class="linhacab3" id="modallocalbioensaio_<?= $idanalise1 ?>" style="display: none; background-color: transparent;">
                        <font style="font-size: 20px;font-weight: bold;">NENHUM LOCAL DISPONÍVEL</font>
                    </div>
                <? } ?>
<?
            }
        } // if($qtdp>0)

    } //if(!empty($datazero))

} //function listalocal($_1_u_bioensaio_idbioensaio)
include_once(__DIR__."/js/ficharep_js.php");