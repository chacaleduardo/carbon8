<?
require_once("../inc/php/validaacesso.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "rhtipoevento";
$pagvalcampos = array(
    "idrhtipoevento" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from rhtipoevento where idrhtipoevento = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
?>
<script>
    <?
    if ($_1_u_rhtipoevento_flgeditavel == "N") { ?>
        //$("#cbModuloForm:input").not('[name*="nf_idnf"],[name*="statusant"],[id*="cbTextoPesquisa"]').prop( "disabled", true );
        $("#cbModuloForm").find('input').not('[name*="rhtipoevento_idrhtipoevento"],[name*="rheventopessoa_idrheventopessoa"],[name*="rheventopessoa_valor"],[name*="_rhtipoevento_idpessoa"],[name*="rhtipoevento_evento"],[name*="rhtipoevento_eventocurto"],[name*="flgmanual"],[name*="rhtipoevento_ord"]').prop("disabled", true);
        $("#cbModuloForm").find("select").prop("disabled", true);
        $("#cbModuloForm").find("textarea").prop("disabled", true);
    <?
    }
    ?>
</script>
<?
function getRhevento()
{
    global $JSON;
    $s = "select p.idrhtipoevento,p.evento
            from rhtipoevento p
            where p.status='ATIVO'                               
            order by p.evento";

    $rts = d::b()->query($s) or die("getRhevento: " . mysqli_error(d::b()));

    $arrtmp = array();
    $i = 0;
    while ($r = mysqli_fetch_assoc($rts)) {
        $arrtmp[$i]["value"] = $r["idrhtipoevento"];
        $arrtmp[$i]["label"] = $r["evento"];
        $i++;
    }

    return $JSON->encode($arrtmp);
}

$jrhtipoevento = getRhevento();

function getFuncionario()
{
    global $_1_u_rhtipoevento_idrhtipoevento;

    $sql = "SELECT
                p.idpessoa,
                p.nomecurto
        FROM pessoa p			
        WHERE p.status = 'ATIVO'
        and not exists(select 1 from rheventopessoa e where e.idpessoa = p.idpessoa and e.idrhtipoevento = " . $_1_u_rhtipoevento_idrhtipoevento . ")
        AND p.idtipopessoa  =1              
        ORDER BY p.nomecurto";

    $res = d::b()->query($sql) or die("getFuncionario: Erro: " . mysqli_error(d::b()) . "\n" . $sql);

    $arrret = array();
    while ($r = mysqli_fetch_assoc($res)) {
        $arrret[$r["idpessoa"]]["nomecurto"] = $r["nomecurto"];
    }
    return $arrret;
}
$jFunc = "null";
if (!empty($_1_u_rhtipoevento_idrhtipoevento)) {
    $arrFunc = getFuncionario();
    //print_r($arrFunc); die;
    $jFunc = $JSON->encode($arrFunc);
}

?>
<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row d-flex flex-between flex-wrap">
                <!-- ID.: -->
                <div class="form-group col-xs-1 col-md-1">
                    <label class="text-white">ID:</label><br />
                    <? if (!empty($_1_u_rhtipoevento_idrhtipoevento)) { ?>
                        <label class="alert-warning"><?= $_1_u_rhtipoevento_idrhtipoevento ?></label>
                    <? } ?>
                    <input name="_1_<?= $_acao ?>_rhtipoevento_idrhtipoevento" type="hidden" value="<?= $_1_u_rhtipoevento_idrhtipoevento ?>">
                </div>
                    
                <!-- Título.: -->
                <div class="form-group col-xs-6 col-md-3">
                    <label class="text-white">Título:</label>
                    <input class="size30" name="_1_<?= $_acao ?>_rhtipoevento_evento" type="text" value="<?= $_1_u_rhtipoevento_evento ?>">
                </div>

                <!-- Título curto.: -->
                <div class="form-group col-xs-6 col-md-2">
                    <label class="text-white">Título curto:</label>
                    <input class="size20" name="_1_<?= $_acao ?>_rhtipoevento_eventocurto" type="text" value="<?= $_1_u_rhtipoevento_eventocurto ?>">
                </div>

                <!-- Cod E-Social:.: -->
                <div class="form-group col-xs-6 col-md-1">
                    <label class="text-white">Cod E-Social:</label>
                    <input class="size8" name="_1_<?= $_acao ?>_rhtipoevento_codigo" type="text" value="<?= $_1_u_rhtipoevento_codigo ?>">
                </div>

                <!-- Código Histórico Dominio.: -->
                <div class="form-group col-xs-6 col-md-1">
                    <label class="text-white">Cód. Hist. Dominio:</label>
                    <input class="size8" name="_1_<?= $_acao ?>_rhtipoevento_historicodominio" type="text" value="<?= $_1_u_rhtipoevento_historicodominio?>">
                </div>
                
                <? if ($_1_u_rhtipoevento_flgfolha == 'Y' or $_1_u_rhtipoevento_flgfixo == 'Y' or $_1_u_rhtipoevento_flgdecimoterc == 'Y' or $_1_u_rhtipoevento_flgdecimoterc2 == 'Y') { ?>
                    <!-- Ordem na folha:.: -->
                    <div class="form-group col-xs-6 col-md-1">
                        <label class="text-white">Ordem na folha:</label>
                        <input class="size10" name="_1_<?= $_acao ?>_rhtipoevento_ord" type="text" value="<?= $_1_u_rhtipoevento_ord ?>">
                    </div>
                <? }  ?>

                <!-- Status.: -->
                <div class="form-group col-xs-6 col-md-2">
                    <label class="text-white">Status:</label> <br />
                    <select name="_1_<?= $_acao ?>_rhtipoevento_status">
                        <? fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'", $_1_u_rhtipoevento_status); ?>
                    </select>
                </div>                
            </div>
        </div>

        <?
        if (!empty($_1_u_rhtipoevento_idrhtipoevento)) {
            ?>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6" style=" border-right: 1px dotted silver;">

                        <? if ($_1_u_rhtipoevento_idrhtipoevento == 427) { ?>
                            <table>
                                <tr>
                                    <td class="nowrap">
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <input title="Folha Décimo Terceiro Primeira Parcela" type="radio" checked name="dterceiro">
                                    </td>
                                    <td align="right">Folha 13 (Parc. 1):</td>
                                    <td class="nowrap">
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <input title="Folha Décimo Terceiro Segunda Parcela" type="radio" checked name="dterceiro2">
                                    </td>
                                    <td align="right">Folha 13 (Parc. 2):</td>
                                </tr>
                            </table>

                        <? } else { ?>
                            <table>
                                <tr>

                                    <?
                                    if ($_1_u_rhtipoevento_flgponto == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    }
                                    ?>
                                    <td>
                                        <input title="Aparece na Tela de Ponto" type="radio" <?= $checked ?> name="radiofolha" onclick="altcheck2('rhtipoevento','flgponto',<?= $_1_u_rhtipoevento_idrhtipoevento ?>,'<?= $vchecked ?>')">
                                    </td>
                                    <td align="right">Ponto</td>


                                    <?
                                    if ($_1_u_rhtipoevento_flgfolha == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    }
                                    ?>
                                    <td class="nowrap">
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <input title="Aparece na Folha" type="radio" <?= $checked ?> name="radiofolha" onclick="altcheck2('rhtipoevento','flgfolha',<?= $_1_u_rhtipoevento_idrhtipoevento ?>,'<?= $vchecked ?>')">
                                    </td>
                                    <td align="right">Folha:</td>


                                    <?
                                    if ($_1_u_rhtipoevento_flgfixo == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    }
                                    ?>
                                    <td class="nowrap">
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <input title="Folha fixo" type="radio" <?= $checked ?> name="radiofolha" onclick="altcheck2('rhtipoevento','flgfixo',<?= $_1_u_rhtipoevento_idrhtipoevento ?>,'<?= $vchecked ?>')">
                                    </td>
                                    <td align="right">Folha fixo:</td>


                                    <?
                                    if ($_1_u_rhtipoevento_flgferias == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    }
                                    ?>
                                    <td class="nowrap">
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <input title="Folha Férias" type="radio" <?= $checked ?> name="radiofolha" onclick="altcheck('rhtipoevento','flgferias',<?= $_1_u_rhtipoevento_idrhtipoevento ?>,'<?= $vchecked ?>')">
                                    </td>
                                    <td align="right">Folha Ferias:</td>


                                    <?
                                    if ($_1_u_rhtipoevento_flgdecimoterc == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    }
                                    ?>
                                    <td class="nowrap">
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <input title="Folha Décimo Terceiro Primeira Parcela" type="radio" <?= $checked ?> name="radiofolha" onclick="altcheck2('rhtipoevento','flgdecimoterc',<?= $_1_u_rhtipoevento_idrhtipoevento ?>,'<?= $vchecked ?>')">
                                    </td>
                                    <td align="right">Folha 13 (Parc. 1):</td>

                                    <?
                                    if ($_1_u_rhtipoevento_flgdecimoterc2 == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    }
                                    ?>
                                    <td class="nowrap">
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <input title="Folha Décimo Terceiro Segunda Parcela" type="radio" <?= $checked ?> name="radiofolha" onclick="altcheck2('rhtipoevento','flgdecimoterc2',<?= $_1_u_rhtipoevento_idrhtipoevento ?>,'<?= $vchecked ?>')">
                                    </td>
                                    <td align="right">Folha 13 (Parc. 2):</td>
                                </tr>
                            </table>
                        <? } ?>
                        <table>
                            <tr>
                                <td colspan="12">
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td align="right">Tipo:</td>
                                <td>
                                    <select class="size8" name="_1_<?= $_acao ?>_rhtipoevento_tipo">
                                        <option value=""></option>
                                        <? fillselect("select 'D','Débito' union select 'C','Crédito' union select 'I','Informativo'", $_1_u_rhtipoevento_tipo); ?>
                                    </select>
                                </td>
   
                                <td align="right">Formato:</td>
                                <td>
                                    <select class="size12" name="_1_<?= $_acao ?>_rhtipoevento_formato" onchange="CB.post();">
                                        <option value=""></option>
                                        <? fillselect("select 'H','Horas'
                                    union select 'HI','Horas Inicio' 
                                    union select 'HIF','Horas Inicio Fim'
                                    union select 'D','Dinheiro' 
                                    union select 'DIA','Dia'", $_1_u_rhtipoevento_formato); ?>
                                    </select>
                                </td>
                            </tr>

                            <tr>

                                <?

                                if ($_1_u_rhtipoevento_formato == 'D' or $_1_u_rhtipoevento_formato == 'H') {
                                ?>
                                    <td align="right">Valor padrão:</td>
                                    <td>
                                        <input type="hidden" name="_old_rhtipoevento_valor" value="<?= $_1_u_rhtipoevento_valor ?>">
                                        <input class="size5" name="_1_<?= $_acao ?>_rhtipoevento_valor" type="text" value="<?= $_1_u_rhtipoevento_valor ?>" vdecimal>
                                    </td>
                                    <td align="right">Valor para conversão:</td>
                                    <td><input title="Valor para conversão entre eventos" class="size5" name="_1_<?= $_acao ?>_rhtipoevento_valorconv" type="text" value="<?= $_1_u_rhtipoevento_valorconv ?>" vdecimal></td>

                                <? } ?>
                                <?
                                if (!empty($_1_u_rhtipoevento_idrhtipoevento)) {
                                ?>
                                    <td align="right">Pode ser gerado manualmente:</td>
                                    <?
                                    if ($_1_u_rhtipoevento_flgmanual == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    }
                                    ?>
                                    <td>
                                        <input title="Gera manual" type="checkbox" <?= $checked ?> name="flgmanual" onclick="altcheck('rhtipoevento','flgmanual',<?= $_1_u_rhtipoevento_idrhtipoevento ?>,'<?= $vchecked ?>')">
                                    </td>
                                <?
                                }
                                ?>

                            </tr>
                            <tr>
                                <td align="right">Dependente:</td>
                                <?
                                if ($_1_u_rhtipoevento_flgdependente == 'Y') {
                                    $checked = 'checked';
                                    $vchecked = 'N';
                                } else {
                                    $checked = '';
                                    $vchecked = 'Y';
                                }
                                ?>
                                <td>
                                    <input title="Dependente" type="checkbox" <?= $checked ?> name="flgdependente" onclick="altcheck('rhtipoevento','flgdependente',<?= $_1_u_rhtipoevento_idrhtipoevento ?>,'<?= $vchecked ?>')">
                                </td>
                                <? if ($_1_u_rhtipoevento_formato == 'D' or $_1_u_rhtipoevento_formato == 'H' or $_1_u_rhtipoevento_formato == 'DIA') { ?>
                                    <td align="right">Soma outro evento:</td>
                                    <?
                                    if ($_1_u_rhtipoevento_flgsomatorio == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    }
                                    ?>
                                    <td>
                                        <input title="Soma Outro evento" type="checkbox" <?= $checked ?> name="flgsomatorio" onclick="altcheck('rhtipoevento','flgsomatorio',<?= $_1_u_rhtipoevento_idrhtipoevento ?>,'<?= $vchecked ?>')">
                                    </td>
                                <?
                                }

                                if ($_1_u_rhtipoevento_flgsomatorio == 'Y') {
                                    if ($_1_u_rhtipoevento_formato == 'DIA') {
                                        $fmto = 'DIA';
                                    } elseif ($_1_u_rhtipoevento_formato == 'D') {
                                        $fmto = 'D';
                                    } else {
                                        $fmto = 'H';
                                    }
                                ?>
                                    <td align="right">Somar:</td>
                                    <td>
                                        <select class="size15" name="_1_<?= $_acao ?>_rhtipoevento_idrhtipoeventosum">
                                            <option value=""></option>
                                            <? fillselect("select idrhtipoevento,evento from rhtipoevento where formato ='" . $fmto . "' and status='ATIVO' order by evento", $_1_u_rhtipoevento_idrhtipoeventosum); ?>
                                        </select>
                                    </td>
                                <?
                                }
                                ?>
                            </tr>
                        </table>

                    </div>
                    <div class="col-md-6">
                        <?
                        $inssirrffgts = array(37, 47, 448, 29, 48, 447, 430, 449, 467, 486); //486 = 486 em produção 
                        if (!in_array($_1_u_rhtipoevento_idrhtipoevento, $inssirrffgts)) { // nao for irrf fgts inss                   


                        ?>
                            <table class="table table-striped planilha">
                                <tr>
                                    <th colspan="3" class="header" style="text-align:center !important;">Tabela de Incidências</th>
                                </tr>
                                <tr>
                                    <th style="text-align:center !important;">INSS</th>
                                    <th style="text-align:center !important;">FGTS</th>
                                    <th style="text-align:center !important;">IRRF</th>
                                </tr>
                                <tr>
                                    <?
                                    if ($_1_u_rhtipoevento_inss == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    }
                                    ?>
                                    <td style="text-align:center !important;">
                                        <input title="INSS" type="checkbox" <?= $checked ?> name="inss" onclick="altcheck('rhtipoevento','inss',<?= $_1_u_rhtipoevento_idrhtipoevento ?>,'<?= $vchecked ?>')">
                                    </td>
                                    <?
                                    if ($_1_u_rhtipoevento_fgts == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    }
                                    ?>
                                    <td style="text-align:center !important;">
                                        <input title="FGTS" type="checkbox" <?= $checked ?> name="fgts" onclick="altcheck('rhtipoevento','fgts',<?= $_1_u_rhtipoevento_idrhtipoevento ?>,'<?= $vchecked ?>')">
                                    </td>
                                    <?
                                    if ($_1_u_rhtipoevento_irrf == 'Y') {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    }
                                    ?>
                                    <td style="text-align:center !important;">
                                        <input title="IRRF" type="checkbox" <?= $checked ?> name="irrf" onclick="altcheck('rhtipoevento','irrf',<?= $_1_u_rhtipoevento_idrhtipoevento ?>,'<?= $vchecked ?>')">
                                    </td>
                                </tr>
                            </table>
                        <?
                        } else {
                            //tabela de faixas
                        ?>
                            <table class="table table-striped planilha">
                                <tr>
                                    <th colspan="8" class="header" style="text-align:center !important;">
                                        Tabela de Base de Calculo <? if ($_1_u_rhtipoevento_idrhtipoevento == 486) {
                                                                        echo ('de Idade');
                                                                    } ?>
                                    </th>
                                </tr>
                                <tr>
                                    <?
                                    if ($_1_u_rhtipoevento_idrhtipoevento != 486) { //486 em produção
                                    ?>
                                        <th style="text-align:center !important;">Menor Aprendiz</th>
                                        <th style="text-align:center !important;">Todos</th>
                                        <th style="text-align:center !important;"> > que</th>
                                    <? } ?>
                                    <th style="text-align:center !important;">De</th>
                                    <th style="text-align:center !important;">Até</th>
                                    <?
                                    if ($_1_u_rhtipoevento_idrhtipoevento != 486) { //486 em produção
                                    ?>
                                        <th style="text-align:center !important;">Aliq %</th>
                                    <? } else { ?>
                                        <th style="text-align:center !important;">Cobrar</th>
                                    <? } ?>
                                    <?
                                    if ($_1_u_rhtipoevento_idrhtipoevento != 486) { //486 em produção
                                    ?>
                                        <th style="text-align:center !important;">Deduzir</th>
                                        <th style="text-align:center !important;">Dependente</th>
                                        <th style="text-align:center !important;"></th>
                                    <? } ?>
                                </tr>
                                <?
                                $sqlbc = "select * from rhtipoeventobc where idrhtipoevento=" . $_1_u_rhtipoevento_idrhtipoevento . " order by todos,valinicio,acimade";
                                $resbc = d::b()->query($sqlbc) or die("Erro ao buscar base de calculo de impostos");
                                $li = 99;
                                while ($rbc = mysqli_fetch_assoc($resbc)) {
                                    $li++;


                                ?>
                                    <tr>
                                        <?
                                        if ($_1_u_rhtipoevento_idrhtipoevento != 486) { //486 em produção
                                        ?>
                                            <th style="text-align:center !important;">
                                                <?
                                                if ($rbc['menoraprendiz'] == 'Y') {
                                                    $checked = 'checked';
                                                    $vchecked = 'N';
                                                } else {
                                                    $checked = '';
                                                    $vchecked = 'Y';
                                                }
                                                ?>
                                                <input title="Aplica Execlusivamente a Menor Aprendiz" type="checkbox" <?= $checked ?> name="menoraprendiz" onclick="altcheck('rhtipoeventobc','menoraprendiz',<?= $rbc['idrhtipoeventobc'] ?>,'<?= $vchecked ?>')">
                                            </th>
                                        <? } ?>
                                        <input class="size5" name="_bc<?= $li ?>_u_rhtipoeventobc_idrhtipoeventobc" type="hidden" value="<?= $rbc['idrhtipoeventobc'] ?>">
                                        <?
                                        if ($_1_u_rhtipoevento_idrhtipoevento != 486) { //486 em produção
                                        ?>
                                            <th style="text-align:center !important;">
                                                <?
                                                if ($rbc['todos'] == 'Y') {
                                                    $checked = 'checked';
                                                    $vchecked = 'N';
                                                } else {
                                                    $checked = '';
                                                    $vchecked = 'Y';
                                                }
                                                ?>
                                                <input title="Todos os valores" type="checkbox" <?= $checked ?> name="todos" onclick="altcheck('rhtipoeventobc','todos',<?= $rbc['idrhtipoeventobc'] ?>,'<?= $vchecked ?>')">
                                            </th>
                                            <th style="text-align:center !important;">
                                                <? if ($rbc['todos'] == 'Y') {
                                                    echo "---";
                                                } else { ?>
                                                    <input class="size5" name="_bc<?= $li ?>_u_rhtipoeventobc_acimade" type="text" value="<?= $rbc['acimade'] ?>" vdecimal>
                                                <? } ?>
                                            </th>
                                        <? } ?>
                                        <th style="text-align:center !important;">
                                            <? if ($rbc['todos'] == 'Y' or !empty($rbc['acimade'])) {
                                                echo "---";
                                            } else { ?>
                                                <input class="size5" name="_bc<?= $li ?>_u_rhtipoeventobc_valinicio" type="text" value="<?= $rbc['valinicio'] ?>" vdecimal>
                                            <? } ?>
                                        </th>
                                        <th style="text-align:center !important;">
                                            <? if ($rbc['todos'] == 'Y' or !empty($rbc['acimade'])) {
                                                echo "---";
                                            } else { ?>
                                                <input class="size7" name="_bc<?= $li ?>_u_rhtipoeventobc_valfim" type="text" value="<?= $rbc['valfim'] ?>" vdecimal>
                                            <? } ?>
                                        </th>
                                        <th style="text-align:center !important;" colspan="3">
                                            <input class="size5" name="_bc<?= $li ?>_u_rhtipoeventobc_percentual" type="text" value="<?= $rbc['percentual'] ?>" vdecimal>
                                        </th>
                                        <?
                                        if ($_1_u_rhtipoevento_idrhtipoevento != 486) { //486 em produção
                                        ?>
                                            <th style="text-align:center !important;">
                                                <input class="size5" name="_bc<?= $li ?>_u_rhtipoeventobc_deduzir" type="text" value="<?= $rbc['deduzir'] ?>" vdecimal>
                                            </th>
                                            <th style="text-align:center !important;">
                                                <input class="size5" name="_bc<?= $li ?>_u_rhtipoeventobc_dependente" type="text" value="<?= $rbc['dependente'] ?>" vdecimal>
                                            </th>
                                            <th style="text-align:center !important;">
                                                <i title="Excluir" class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluirbc(<?= $rbc['idrhtipoeventobc'] ?>)" alt="Excluir"></i>
                                            </th>
                                        <? } ?>
                                    </tr>
                                <? } ?>
                                <tr>
                                    <td colspan="8">
                                        <i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" onclick="novo()" title="Inserir nova base de calculo"></i>
                                    </td>
                                </tr>
                            </table>
                        <?
                        }
                        ?>
                    </div>
                </div>
            </div>
    </div>
</div>

<? if ($_1_u_rhtipoevento_flgfixo == 'Y') { ?>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">Funcionários com o Evento Fixo</div>
            <div class="panel-body">
                <table class="table table-striped planilha">
                    <tr>
                        <th colspan="3"></th>
                    </tr>
                    <?
                    $sqlh = "select e.idrheventopessoa,e.valor,t.idpessoa,t.nomecurto,e.status
                        from rheventopessoa e left join pessoa t on(t.idpessoa=e.idpessoa)
                        where e.idrhtipoevento=" . $_1_u_rhtipoevento_idrhtipoevento . " order by t.nomecurto";
                    $resh = d::b()->query($sqlh) or die("Erro ao buscar eventos fixos do funcionário : " . mysqli_error(d::b()) . "<p>SQL:" . $sqlh);
                    $qtdh = mysqli_num_rows($resh);
                    if ($qtdh > 0) {
                    ?>
                        <tr>
                            <td>Evento</td>
                            <td>Valor</td>
                            <td></td>
                        </tr>
                        <?
                        $i = 99;
                        while ($rowh = mysqli_fetch_assoc($resh)) {
                            $i = $i + 1;
                        ?>
                            <tr>
                                <td>
                                    <input name="_<?= $i ?>_u_rheventopessoa_idrheventopessoa" type="hidden" value="<?= $rowh['idrheventopessoa'] ?>">
                                    <?= $rowh['nomecurto'] ?>
                                </td>
                                <td>
                                    <input name="_<?= $i ?>_u_rheventopessoa_valor" type="text" class="size5" value="<?= $rowh['valor'] ?>" onchange="CB.post()">
                                </td>
                                <td>
                                    <? if ($rowh['status'] == 'ATIVO') { ?>
                                        <i class="fa fa-check-circle-o  fa-1x verde hoververde btn-lg pointer ui-droppable" onclick="retiraevfixo(<?= $rowh['idrheventopessoa'] ?>,'INATIVO')" title="ATIVO"></i>
                                    <? } else { ?>
                                        <i class="fa fa-check-circle-o  fa-1x vermelho hoververmelho btn-lg pointer ui-droppable" onclick="retiraevfixo(<?= $rowh['idrheventopessoa'] ?>,'ATIVO')" title="INATIVO"></i>
                                    <? } ?>
                                </td>
                            </tr>
                    <?
                        }
                    }
                    ?>
                    <tr>
                        <td colspan="3">
                            <input name="_rhtipoevento_idpessoa" class="compacto" type="text" cbvalue placeholder="Selecione um funcionário">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
<?
            } //if($_1_u_rhtipoevento_flgfixo=='Y'){

?>



<?
            if (!empty($_1_u_rhtipoevento_idrhtipoevento)) { // trocar p/ cada tela a tabela e o id da tabela
                $_idModuloParaAssinatura = $_1_u_rhtipoevento_idrhtipoevento; // trocar p/ cada tela o id da tabela
                require 'viewAssinaturas.php';
            }
            $tabaud = "rhtipoevento"; //pegar a tabela do criado/alterado em antigo
            require 'viewCriadoAlterado.php';
?>

<script>
    jFunc = <?= $jFunc ?>; // autocomplete 
    //mapear autocomplete de clientes
    jFunc = jQuery.map(jFunc, function(o, id) {
        return {
            "label": o.nomecurto,
            value: id + ""
        }
    });

    //autocomplete 
    $("[name*=_rhtipoevento_idpessoa]").autocomplete({
        source: jFunc,
        delay: 0,
        select: function(event, ui) {
            CB.post({
                objetos: "_x_i_rheventopessoa_idpessoa=" + ui.item.value + "&_x_i_rheventopessoa_idrhtipoevento=" + $("[name=_1_u_rhtipoevento_idrhtipoevento]").val()
            });
        },
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
            };
        }
    });

    function altcheck(vtab, vcampo, vid, vcheck) {
        CB.post({
            objetos: "_x_u_" + vtab + "_id" + vtab + "=" + vid + "&_x_u_" + vtab + "_" + vcampo + "=" + vcheck
        });
    }
    //altcheck2('rhtipoevento','flgponto',<?= $_1_u_rhtipoevento_idrhtipoevento ?>,'<?= $vchecked ?>')
    function altcheck2(vtab, vcampo, vid, vcheck) {

        flags = ['flgponto', 'flgfolha', 'flgfixo', 'flgferias', 'flgdecimoterc'];
        var posItem = flags.indexOf(vcampo); //localiza posicao
        var removedItem = flags.splice(posItem, 1); // remove um item da possição
        var stri = '';
        flags.forEach(function(item, index) {
            stri += "&_x_u_rhtipoevento_" + item + "=N";
        })


        CB.post({
            objetos: "_x_u_rhtipoevento_idrhtipoevento=" + vid + "&_x_u_" + vtab + "_" + vcampo + "=" + vcheck + stri
        });
    }


    function retiraevfixo(inid, status) {
        CB.post({
            objetos: "_x_u_rheventopessoa_idrheventopessoa=" + inid + "&_x_u_rheventopessoa_status=" + status,
            parcial: true
        });
    }

    function novo() {

        CB.post({
            objetos: "_x_i_rhtipoeventobc_idrhtipoevento=" + $("[name=_1_u_rhtipoevento_idrhtipoevento]").val(),
            parcial: true
        });
    }

    function excluirbc(inidrhtipoeventobc) {
        CB.post({
            objetos: "_x_d_rhtipoeventobc_idrhtipoeventobc=" + inidrhtipoeventobc,
            parcial: true
        });
    }


    function preenchetins() {

        $("#idtipoprodserv").html("<option value=''>Procurando....</option>");

        $.ajax({
            type: "get",
            url: "ajax/buscacontaitem.php",
            data: {
                idcontaitem: $("#idcontaitem").val()
            },

            success: function(data) {
                $("#idtipoprodserv").html(data);
            },

            error: function(objxmlreq) {
                alert('Erro:<br>' + objxmlreq.status);

            }
        }) //$.ajax

    }
</script>
<? } //}//if(!empty($_1_u_rhtipoevento_idrhtipoevento)){ ?>