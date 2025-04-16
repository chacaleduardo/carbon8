<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("controllers/endereco_controller.php");

$idcliente = $_GET["idpessoa"];

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "endereco";
$pagvalcampos = array(
    "idendereco" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from endereco where idendereco = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php")
?>
<div class="row ">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <table>
                    <tr>
                        <td></td>
                        <td>
                            <?
                            if (empty($_1_u_endereco_idpessoa)) {
                                $_1_u_endereco_idpessoa = $idcliente;
                            }
                            ?>
                            <input name="_1_<?= $_acao ?>_endereco_idpessoa" id="idpessoa" type="hidden" readonly value="<?= $_1_u_endereco_idpessoa ?>">
                            <input name="_1_<?= $_acao ?>_endereco_idendereco" id="idendereco" type="hidden" readonly value="<?= $_1_u_endereco_idendereco ?>">
                        </td>
                    </tr>

                    <tr>
                        <td align="right">Tipo Endere&ccedil;o:</td>
                        <? if ($_1_u_endereco_idtipoendereco == 6) {
                            $disabled = "disabled='disabled' ";
                        } 
                        ?>
                        <td>
                            <select <?= $disabled ?> name="_1_<?= $_acao ?>_endereco_idtipoendereco" onchange="validacmp(this)">
                                <?
                                echo $idcliente . " idcliente";                        
                                fillselect(EnderecoController::buscarTipoEndereco(), $_1_u_endereco_idtipoendereco);
                                ?>
                            </select>
                        </td>

                    </tr>
                    <tr id="trpropriedade">
                        <td class="nowrap" align="right">Nome Propriedade:</td>
                        <td colspan="3">
                            <input name="_1_<?= $_acao ?>_endereco_nomepropriedade" type="text" value="<?= $_1_u_endereco_nomepropriedade ?>" vnulo>
                        </td>
                    </tr>
                    <tr id="trpropriedade2">
                        <td align="right">CPF/CNPJ Propriedade:</td>
                        <td>
                            <input name="_1_<?= $_acao ?>_endereco_cnpjend" type="text" value="<?= $_1_u_endereco_cnpjend ?>" vnulo>
                        </td>
                        <td class="nowrap" align="right">I.E./P. Rural:</td>
                        <td>
                            <input name="_1_<?= $_acao ?>_endereco_inscest" type="text" value="<?= $_1_u_endereco_inscest ?>" vnulo>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Logradouro:</td>
                        <td>
                            <input name="_1_<?= $_acao ?>_endereco_logradouro" id="logradouro" type="text" value="<?= $_1_u_endereco_logradouro ?>" size="9">
                        </td>
                        <td align="right">Endere&ccedil;o:</td>
                        <td colspan='3'><input name="_1_<?= $_acao ?>_endereco_endereco" type="text" id="nome" value="<?= $_1_u_endereco_endereco ?>" size="45"></td>
                    </tr>
                    <tr>
                        <td align="right">Numero:</td>
                        <td>
                            <input name="_1_<?= $_acao ?>_endereco_numero" id="numero" type="text" value="<?= $_1_u_endereco_numero ?>" size="9">
                        </td>
                        <td align="right">Complemento:</td>
                        <td colspan='3'><input name="_1_<?= $_acao ?>_endereco_complemento" id="complemento" type="text" value="<?= $_1_u_endereco_complemento ?>" size="30"></td>
                    </tr>
                    <tr>
                        <td align="right">Bairro:</td>
                        <td><input name="_1_<?= $_acao ?>_endereco_bairro" type="text" id="bairroini" value="<?= $_1_u_endereco_bairro ?>" size="20" vnulo></td>
                        <td align="right">CEP:</td>
                        <td>
                            <input name="_1_<?= $_acao ?>_endereco_cep" id="cep" type="text" value="<?= $_1_u_endereco_cep ?>" size="9">
                        </td>
                        <!-- maf250311: comentado para bater com tmaq
            <td>Cidade </td>
            <td><input name="_1_<?= $_acao ?>_endereco_cidade" id="localidade" type="text" value="<?= $_1_u_endereco_cidade ?>" size="30"></td>
      -->
                    </tr>
                    <tr>

                        <td align="right">UF / Cidade:</td>
                        <td><select name="_1_<?= $_acao ?>_endereco_uf" id="iduf" vnulo>
                                <?
                                

                                fillselect(EnderecoController::$ufBr, $_1_u_endereco_uf);

                                ?>
                            </select>
                        </td>
                        <td align="right">Cidade:</td>
                        <td>
                            <?
                            if (empty($_1_u_endereco_codcidade)) {
                            ?>
                                <select name="_1_<?= $_acao ?>_endereco_codcidade" id="idcidade" vnulo>
                                    <option value=""></option>
                                </select>
                            <?
                            } elseif (!empty($_1_u_endereco_codcidade)) {
                            ?>
                                <select name="_1_<?= $_acao ?>_endereco_codcidade" id="idcidade" vnulo>
                                    <? fillselect( $CodcidadeCidade=EnderecoController::buscarCodcidadeCidade($_1_u_endereco_uf),
                                        $_1_u_endereco_codcidade
                                    ); ?>
                                </select>
                            <?
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Localização Maps.:</td>
                        <td colspan="3">
                            <input name="_1_<?= $_acao ?>_endereco_localizacao" type="text" value="<?= $_1_u_endereco_localizacao ?>" size="20" placeholder="https://">
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Obs Entrega:</td>
                        <td colspan="3">
                            <textarea <?= $readonly2 ?> name="_1_<?= $_acao ?>_endereco_obsentrega" style="width: 640px; height: 40px;"><?= $_1_u_endereco_obsentrega ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Status:</td>
                        <td><select name="_1_<?= $_acao ?>_endereco_status" id="status" vnulo>
                                <?
                                fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'", $_1_u_endereco_status);
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?
if (!empty($_1_u_endereco_idendereco)) { // trocar p/ cada tela a tabela e o id da tabela
    $_idModuloParaAssinatura = $_1_u_endereco_idendereco; // trocar p/ cada tela o id da tabela
    require 'viewAssinaturas.php';
}
$tabaud = "endereco"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
require_once('../form/js/endereco_js.php');
?>