<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
include_once(__DIR__."/controllers/bioterioanalise_controller.php");
require_once(__DIR__ . "/controllers/bioensaio_controller.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "bioterioanalise";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idbioterioanalise" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from bioterioanalise where idbioterioanalise = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
?>
<style>
    .tdservico input {
        width: 40px !important;
        height: 20px !important;
        display: inline;
    }
</style>
<div class="row">
    <div class="col-md-5">
        <div class="panel panel-default">
            <div class="panel-heading">
                <table>
                    <tr>

                        <td></td>
                        <td><input name="_1_<?= $_acao ?>_bioterioanalise_idbioterioanalise" type="hidden" value="<?= $_1_u_bioterioanalise_idbioterioanalise ?>" readonly='readonly'></td>
                    </tr>
                    <tr>
                        <td align="right">Análise:</td>
                        <td><input name="_1_<?= $_acao ?>_bioterioanalise_tipoanalise" size="80" type="text" Value="<?= $_1_u_bioterioanalise_tipoanalise ?>" vnulo></td>
                        <td align="right">Status:</td>
                        <td>
                            <select name="_1_<?= $_acao ?>_bioterioanalise_status">
                                <? fillselect("SELECT 'ATIVO','Ativo' union SELECT 'INATIVO','Inativo' ", $_1_u_bioterioanalise_status); ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="panel-body">
                <? if (!empty($_1_u_bioterioanalise_idbioterioanalise)) { ?>

                    <table>
                        <tr>
                            <td align="right">Via:</td>
                            <td>
                                <select class='size20' name="_1_<?= $_acao ?>_bioterioanalise_pdvia">
                                    <option value=""></option>
                                    <? fillselect(BioterioAnaliseController::$ArrayVias, $_1_u_bioterioanalise_pdvia); ?>
                                </select>
                            </td>
                            <td align="right" class='nowrap'>Nº Doses:</td>
                            <td><input class='size4' name="_1_<?= $_acao ?>_bioterioanalise_pddose" size="2" type="text" Value="<?= $_1_u_bioterioanalise_pddose ?>"></td>
                            
                            <td align="right">Vol. Aplic.:</td>
                            <td><input name="_1_<?= $_acao ?>_bioterioanalise_pdvolume" size="2" type="text" Value="<?= $_1_u_bioterioanalise_pdvolume ?>"></td>
                        </tr>
                        <tr>
                            <td align="right">Espécie/Finalidade:</td>
                            <td>
                                <select name="_1_<?= $_acao ?>_bioterioanalise_idespeciefinalidade" class='size20' vnulo>
                                    <option></option>
                                    <? fillselect(BioensaioController::toFillSelect(BioensaioController::listarEspecieFinalidadePorEmpresa($_1_u_bioterioanalise_idempresa)), $_1_u_bioterioanalise_idespeciefinalidade); ?>
                                </select>
                            </td>
                            <td align="right">Idade entre:</td>
                            <td class='nowrap'><input class='size4' name="_1_<?= $_acao ?>_bioterioanalise_idadeinicial" size="2" type="text" Value="<?= $_1_u_bioterioanalise_idadeinicial ?>">
                            e
                            <input class='size4' name="_1_<?= $_acao ?>_bioterioanalise_idadefinal" size="2" type="text" Value="<?= $_1_u_bioterioanalise_idadefinal ?>"> dias</td>
                        </tr>
                        </tr>
                        <tr>
                            <td align="right">
                                Subcategoria:
                            </td>
                            <td>
                                <?									
                                    $sqlit = BioterioAnaliseController::listarTipoProdservTipoProdServ($_1_u_bioterioanalise_idempresa);								
                                ?>
                                <select  class='size20' name="_1_<?= $_acao ?>_bioterioanalise_idtipoprodserv">
                                    <option></option>
                                    <? fillselect($sqlit, $_1_u_bioterioanalise_idtipoprodserv) ?>
                                </select>
                            </td>
							
                            <td align="right">Analise de Cria?</td>
                            <td align="left">
                                <? if ($_1_u_bioterioanalise_cria == 'Y') {
                                    $checked = 'checked';
                                    $vchecked = 'N';
                                } else {
                                    $checked = '';
                                    $vchecked = 'Y';
                                }
                                ?>
                                <input title="Cria" type="checkbox" <?= $checked ?> name="namecria" onclick="altcheck('bioterioanalise','cria',<?= $_1_u_bioterioanalise_idbioterioanalise ?>,'<?= $vchecked ?>')">
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Texto:</td>
                            <td colspan="5"><textarea name="_1_<?= $_acao ?>_bioterioanalise_texto" cols="100" rows="20"><?= $_1_u_bioterioanalise_texto ?></textarea></td>
                        </tr>
                    </table>

                <?
                }
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="panel panel-default">
            <div class="panel-heading">Serviços</div>
            <div class="panel-body">
                <?
                if (!empty($_1_u_bioterioanalise_idbioterioanalise)) {
                ?>
                    <table id="tbTestes" class="table table-striped planilha">
                        <thead>
                            <tr>
                                <th>Dia-0</th>
                                <th>Serviço</th>
                                <th>Dia</th>
                                <th>Retirar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <? listaservico() ?>
                        </tbody>
                    </table>
                <?
                } //if(!empty($_1_u_bioterioanalise_idbioterioanalise)){
                ?>
            </div>
        </div>
    </div>
</div>
<?
if (!empty($_1_u_bioterioanalise_idbioterioanalise)) { // trocar p/ cada tela a tabela e o id da tabela
    $_idModuloParaAssinatura = $_1_u_bioterioanalise_idbioterioanalise; // trocar p/ cada tela o id da tabela
    require 'viewAssinaturas.php';
}
$tabaud = "bioterioanalise"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
?>
<?
function listaservico()
{
    global $_acao, $_1_u_bioterioanalise_idbioterioanalise;

    $resd = BioterioAnaliseController::buscarConfiguracaoDoServico($_1_u_bioterioanalise_idbioterioanalise);
    $i = 10;
    $d = 1000;
    foreach ($resd as $k => $rowd) {
        $i = $i + 1;
?>
        <tr>
            <td align="center">
                <? if ($rowd['diazero'] == 'N') {
                    $div = "divvermelho";
                    $cor = 'cinza';
                    $diazero = 'Y';
                    $checked = '';
                } else {
                    $div = "divverde";
                    $cor = 'verde';
                    $diazero = 'N';
                    $checked = 'checked';
                }
                ?>
                <input title="Dia zero?" type="checkbox" <?= $checked ?> name="namediazero" onclick="flgdiazero(<?= $rowd['idservicobioterioconf'] ?>,'<?= $diazero ?>');">

            </td>
            <td> <? if (empty($rowd['idservicobioterio'])) { ?>
                    <select name="_<?= $i ?>_u_servicobioterioconf_idservicobioterio" onchange="CB.post();" style="font-size: 10px" vnulo>
                        <option value=""></option>
                        <? fillselect(ServicoBioterioQuery::buscarServicosAtivos()); ?>
                    </select>
                <? } else { ?>
                    <a title="editar serviço" href="javascript:janelamodal('?_modulo=servicobioterio&_acao=u&idservicobioterio=<?= $rowd['idservicobioterio'] ?>')">
                        <? echo ("<h5 class='bold'>" . $rowd['rotulo'] . "</h5>"); ?>
                    </a>
                <?      } ?>
                <ul>
                    <? if ($rowd['geraamostra'] == 'S' and !empty($rowd['idservicobioterio'])) {
                        $rest = BioterioAnaliseController::buscarTesteDaConfiguracao($rowd['idservicobioterioconf']);
                        foreach ($rest as $k1 => $rowt) {
                            $d = $d + 1;
                    ?>
                            <li>
                                <? if (empty($rowt["idprodserv"])) { ?>
                                    <input name="_<?= $d ?>_u_bioterioanaliseteste_idbioterioanaliseteste" type="hidden" value="<?= $rowt["idbioterioanaliseteste"] ?>">
                                    <select name="_<?= $d ?>_u_bioterioanaliseteste_idprodserv" onchange="CB.post();" style="font-size: 10px" vnulo>
                                        <option value=""></option>
                                        <? fillselect(BioterioAnaliseController::toFillSelect(BioterioAnaliseController::buscarServicosParaConf(getidempresa("t.idempresa",'prodserv')))); ?>
                                    </select>
                                <? } else { ?>
                                    <a title="editar teste" href="javascript:janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?= $rowt["idprodserv"] ?>')">
                                        <?= $rowt['descr'] ?>
                                    </a>
                                <? } ?>
                                <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluirteste(<?= $rowt["idbioterioanaliseteste"] ?>)" title="Excluir teste"></i>

                            </li>

                        <?
                        }
                        ?>
                        <li>
                            <i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde pointer" onclick="novoteste(<?= $rowd['idservicobioterioconf'] ?>)" title="Inserir teste"></i>
                        </li>

                    <? } ?>
                </ul>
            </td>
            <td class="tdservico">
                <input name="_<?= $i ?>_u_servicobioterioconf_idservicobioterioconf" type="hidden" value="<?= $rowd["idservicobioterioconf"] ?>">
                <input name="_<?= $i ?>_u_servicobioterioconf_dia" type="text" size="2" value="<?= $rowd["dia"] ?>" onchange="CB.post();">
            </td>
            <td align="center">

                <i class="fa fa-trash fa-2x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluirservico(<?= $rowd["idservicobioterioconf"] ?>)" alt="Excluir"></i>

            </td>
        </tr>

    <?
    }
    ?>
    <tr>

        <td align="center">
            <i class="fa fa-plus-circle fa-2x  cinzaclaro hoververde pointer" onclick="novoservico()" title="Inserir serviço"></i>
        </td>
    </tr>
<?
}
require_once(__DIR__."/js/bioterioanalise_js.php");
?>
